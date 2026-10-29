<?php
/**
 * Tests du référencement, de la sécurité et des images, exécutés dans WordPress (site local lancé) :
 *   php tools/wp-cli.phar --path=.local/wordpress eval-file tests/seo-securite.php
 * Les réglages modifiés sont restaurés à la fin.
 *
 * @package SnexCore
 */

use Snex\Core\Coordonnees as C;
use Snex\Core\Performance as P;
use Snex\Core\Securite as S;
use Snex\Core\Seo;

defined( 'ABSPATH' ) || exit;

$snex_saved   = get_option( C\OPTION );
$snex_results = array();
$snex_check   = static function ( string $label, bool $ok ) use ( &$snex_results ): void {
	$snex_results[ $label ] = $ok;
};

try {
	/* ---------- Référencement ---------- */
	$snex_desc = Seo\shorten( str_repeat( 'Installation et maintenance de sites télécoms. ', 10 ) );
	$snex_check( 'Description limitée à 160 caractères', mb_strlen( $snex_desc ) <= Seo\DESCRIPTION_MAX && str_ends_with( $snex_desc, '…' ) );
	$snex_check( 'Description courte conservée telle quelle', 'Énergie et solaire.' === Seo\shorten( '<p>Énergie   et solaire.</p>' ) );

	$_GET['devis'] = 'envoye';
	$snex_check( 'Confirmation d\'envoi du devis exclue des moteurs', ! empty( Seo\robots( array() )['noindex'] ) );
	unset( $_GET['devis'] );

	update_option( C\OPTION, C\defaults() );
	$snex_check( 'Téléphone absent des données structurées tant qu\'il n\'est pas confirmé', ! isset( Seo\organization()['telephone'] ) );
	update_option(
		C\OPTION,
		array_merge(
			C\defaults(),
			array(
				'telephone_international' => '2250102030405',
				'telephone_confirme'      => true,
			)
		)
	);
	$snex_check( 'Téléphone confirmé publié au format international', '+2250102030405' === ( Seo\organization()['telephone'] ?? '' ) );

	/* ---------- Sécurité ---------- */
	$snex_headers = S\headers( array( 'X-Pingback' => 'x' ) );
	$snex_check( 'En-têtes de sécurité ajoutés', 'nosniff' === ( $snex_headers['X-Content-Type-Options'] ?? '' ) && isset( $snex_headers['Referrer-Policy'], $snex_headers['X-Frame-Options'] ) );
	$snex_check( 'En-tête X-Pingback retiré', ! isset( $snex_headers['X-Pingback'] ) );

	wp_set_current_user( 0 );
	$snex_routes = S\hide_user_endpoints(
		array(
			'/wp/v2/users'               => 1,
			'/wp/v2/users/(?P<id>[\d]+)' => 1,
			'/wp/v2/pages'               => 1,
		)
	);
	$snex_check( 'Liste des comptes masquée aux visiteurs', array( '/wp/v2/pages' ) === array_keys( $snex_routes ) );

	$snex_wrong = S\limit_login( new WP_Error( 'invalid_username', 'Identifiant inconnu : admin2' ), 'admin2' );
	$snex_check( 'Même message pour un identifiant ou un mot de passe faux', is_wp_error( $snex_wrong ) && 'snex_login_failed' === $snex_wrong->get_error_code() );
	set_transient( S\login_key(), S\LOGIN_MAX, MINUTE_IN_SECONDS );
	$snex_locked = S\limit_login( new WP_User( 1 ), 'admin' );
	$snex_check( 'Connexion bloquée après trop d\'échecs, même avec le bon mot de passe', is_wp_error( $snex_locked ) && 'snex_login_locked' === $snex_locked->get_error_code() );
	delete_transient( S\login_key() );

	wp_set_current_user( 1 );
	$snex_check( 'Administrateur sans HTML libre (contenus filtrés)', ! current_user_can( 'unfiltered_html' ) );
	$snex_filtered = wp_kses_post( '<p onclick="alert(1)">Texte</p><script>alert(1)</script><img src="x" onerror="alert(1)">' );
	$snex_check( 'Script et attributs JavaScript retirés d\'un contenu', ! preg_match( '/<script|onclick|onerror/i', $snex_filtered ) && str_contains( $snex_filtered, 'Texte' ) );
	wp_set_current_user( 0 );
	$snex_check( 'Mots de passe d\'application désactivés', ! wp_is_application_passwords_available() );
	$snex_check( 'Commentaires fermés', ! comments_open( (int) get_option( 'page_on_front' ) ) );

	S\inline_hashes( 'console.log(1)' );
	$snex_csp = S\csp();
	$snex_check( 'CSP : scripts en ligne autorisés par empreinte, jamais en bloc', str_contains( $snex_csp, "script-src 'self' 'sha256-" ) && ! preg_match( "/script-src[^;]*'unsafe-inline'/", $snex_csp ) );
	$snex_check( 'CSP : ni objets ni intégration dans un site tiers', str_contains( $snex_csp, "object-src 'none'" ) && str_contains( $snex_csp, "frame-ancestors 'self'" ) );

	/* ---------- Images ---------- */
	$snex_svg = P\link_image( '<figure class="wp-block-image"><img src="' . esc_url( get_theme_file_uri( 'assets/images/schema-inverseur.svg' ) ) . '" alt=""/></figure>', array( 'attrs' => array() ) );
	$snex_check( 'Schéma SVG du thème dimensionné', str_contains( $snex_svg, 'width="' ) && str_contains( $snex_svg, 'height="' ) );
	$snex_media = (array) get_option( 'snex_seed_media', array() );
	if ( isset( $snex_media['hero-pv-sol.webp'] ) ) {
		$snex_img = P\link_image( '<figure class="wp-block-image"><img src="' . esc_url( wp_get_attachment_url( $snex_media['hero-pv-sol.webp'] ) ) . '" alt=""/></figure>', array( 'attrs' => array() ) );
		$snex_check( 'Image de la médiathèque reliée à son média', str_contains( $snex_img, 'wp-image-' . $snex_media['hero-pv-sol.webp'] ) );
	}

	/* ---------- Site local (bin/serve.sh) ---------- */
	$snex_get  = static fn( string $path, array $args = array() ) => wp_remote_request(
		home_url( $path ),
		$args + array(
			'redirection' => 0,
			'timeout'     => 15,
		)
	);
	$snex_home = $snex_get( '/' );
	if ( is_wp_error( $snex_home ) ) {
		WP_CLI::warning( 'Site local injoignable (' . $snex_home->get_error_message() . ') : tests HTTP ignorés. Lancez bin/serve.sh.' );
	} else {
		$snex_body = wp_remote_retrieve_body( $snex_home );
		$snex_check( 'Accueil : description, Open Graph et données structurées', str_contains( $snex_body, '<meta name="description"' ) && str_contains( $snex_body, 'og:title' ) && str_contains( $snex_body, 'application/ld+json' ) );
		$snex_check( 'Accueil : version de WordPress masquée', ! str_contains( $snex_body, 'name="generator"' ) );
		$snex_check( 'Accueil : en-tête nosniff envoyé', 'nosniff' === wp_remote_retrieve_header( $snex_home, 'x-content-type-options' ) );
		$snex_check( 'Accueil : politique de sécurité du contenu (CSP) envoyée', str_contains( (string) wp_remote_retrieve_header( $snex_home, 'content-security-policy' ), "script-src 'self' 'sha256-" ) );
		$snex_check( 'Accueil : PHP non annoncé', '' === wp_remote_retrieve_header( $snex_home, 'x-powered-by' ) );
		$snex_check( '« ?author=1 » ne révèle pas l\'identifiant', 404 === wp_remote_retrieve_response_code( $snex_get( '/?author=1' ) ) );
		$snex_check( 'API REST : liste des comptes introuvable', 404 === wp_remote_retrieve_response_code( $snex_get( '/wp-json/wp/v2/users' ) ) );
		$snex_check( 'XML-RPC refusé', 403 === wp_remote_retrieve_response_code( $snex_get( '/xmlrpc.php', array( 'method' => 'POST' ) ) ) );
		$snex_check( 'Plan du site sans les comptes', 404 === wp_remote_retrieve_response_code( $snex_get( '/wp-sitemap-users-1.xml' ) ) );
	}
} finally {
	update_option( C\OPTION, $snex_saved );
}

$snex_failed = 0;
foreach ( $snex_results as $snex_label => $snex_ok ) {
	WP_CLI::log( ( $snex_ok ? 'RÉUSSI  ' : 'ÉCHEC   ' ) . $snex_label );
	$snex_failed += $snex_ok ? 0 : 1;
}
if ( $snex_failed ) {
	WP_CLI::error( "$snex_failed test(s) en échec." );
}
WP_CLI::success( count( $snex_results ) . ' tests réussis.' );
