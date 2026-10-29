<?php
/**
 * Sécurité : en-têtes HTTP et politique de sécurité du contenu (CSP) contre les attaques XSS, contenus filtrés,
 * XML-RPC fermé, comptes non énumérables, tentatives de connexion limitées.
 *
 * Ce qui relève du serveur (HTTPS, exécution de PHP dans uploads, sauvegardes) est décrit dans le README.
 *
 * @package SnexCore
 */

namespace Snex\Core\Securite;

defined( 'ABSPATH' ) || exit;

const LOGIN_MAX    = 5;  // Échecs de connexion tolérés par adresse IP...
const LOGIN_WINDOW = 15; // ... sur cette durée, en minutes.

/**
 * Branche les crochets WordPress.
 */
function bootstrap(): void {
	// Pas d'éditeur de code des thèmes et extensions dans l'administration.
	if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
		define( 'DISALLOW_FILE_EDIT', true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- constante de WordPress.
	}

	// Version de WordPress et liens inutiles retirés de l'en-tête.
	add_filter( 'the_generator', '__return_empty_string' );
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'template_redirect', 'wp_shortlink_header', 11 );

	// XML-RPC : inutile au site, souvent visé par les attaques par force brute.
	add_filter( 'xmlrpc_enabled', '__return_false' );
	add_filter( 'xmlrpc_methods', '__return_empty_array' );
	add_action( 'init', __NAMESPACE__ . '\\block_xmlrpc', 0 );

	// Ni HTML ni script libres dans les contenus, même pour les administrateurs : tout passe par le filtre de WordPress.
	if ( ! defined( 'DISALLOW_UNFILTERED_HTML' ) ) {
		define( 'DISALLOW_UNFILTERED_HTML', true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- constante de WordPress.
	}

	// Mots de passe d'application (accès à l'API par identifiant) : inutiles au site.
	add_filter( 'wp_is_application_passwords_available', '__return_false' );

	// Commentaires et rétroliens fermés partout : le site n'en publie pas.
	add_filter( 'comments_open', '__return_false' );
	add_filter( 'pings_open', '__return_false' );

	add_filter( 'wp_headers', __NAMESPACE__ . '\\headers' );
	add_action( 'send_headers', static fn() => header_remove( 'X-Powered-By' ) );

	// Politique de sécurité du contenu (CSP) contre l'injection de scripts.
	add_filter( 'wp_inline_script_attributes', __NAMESPACE__ . '\\register_inline_script', 10, 2 );
	add_action( 'template_redirect', __NAMESPACE__ . '\\start_csp', PHP_INT_MAX );

	// Identifiants des comptes : ni liste publique, ni archives d'auteur, ni nom dans les intégrations.
	add_filter( 'rest_endpoints', __NAMESPACE__ . '\\hide_user_endpoints' );
	add_action( 'template_redirect', __NAMESPACE__ . '\\disable_author_archives', 1 );
	add_filter( 'oembed_response_data', __NAMESPACE__ . '\\strip_oembed_author' );

	add_filter( 'authenticate', __NAMESPACE__ . '\\limit_login', 100, 2 );
	add_action( 'wp_login_failed', __NAMESPACE__ . '\\count_failure' );
	add_action( 'wp_login', static fn() => delete_transient( login_key() ) );
}

/**
 * Refuse les requêtes adressées à xmlrpc.php.
 */
function block_xmlrpc(): void {
	if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
		status_header( 403 );
		exit;
	}
}

/**
 * En-têtes de sécurité des pages du site.
 *
 * @param array<string,string> $headers En-têtes prévus par WordPress.
 * @return array<string,string>
 */
function headers( array $headers ): array {
	unset( $headers['X-Pingback'] );
	$headers['X-Content-Type-Options']     = 'nosniff';
	$headers['X-Frame-Options']            = 'SAMEORIGIN';
	$headers['Referrer-Policy']            = 'strict-origin-when-cross-origin';
	$headers['Permissions-Policy']         = 'camera=(), microphone=(), geolocation=(), payment=(), usb=()';
	$headers['Cross-Origin-Opener-Policy'] = 'same-origin';
	if ( is_ssl() && 'production' === wp_get_environment_type() ) {
		$headers['Strict-Transport-Security'] = 'max-age=31536000';
	}
	return $headers;
}

/**
 * Empreintes SHA-256 des scripts en ligne écrits par WordPress dans la page en cours.
 *
 * @param string|null $data Script à ajouter (facultatif).
 * @return array<int,string>
 */
function inline_hashes( ?string $data = null ): array {
	static $hashes = array();
	if ( null !== $data ) {
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- format exigé par la CSP.
		$hashes[] = "'sha256-" . base64_encode( hash( 'sha256', $data, true ) ) . "'";
	}
	return array_values( array_unique( $hashes ) );
}

/**
 * Retient chaque script en ligne passé par wp_get_inline_script_tag() : lui seul sera autorisé par la CSP.
 * Un script injecté dans la page (faille XSS) n'a pas d'empreinte autorisée et n'est pas exécuté.
 *
 * @param array<string,string|bool> $attributes Attributs de la balise.
 * @param string                    $data       Contenu exact du script.
 * @return array<string,string|bool>
 */
function register_inline_script( array $attributes, string $data ): array {
	inline_hashes( $data );
	return $attributes;
}

/**
 * Politique de sécurité du contenu : seuls les fichiers du site et les scripts en ligne de WordPress s'exécutent.
 */
function csp(): string {
	$directives = array(
		'default-src'     => "'self'",
		'script-src'      => trim( "'self' " . implode( ' ', inline_hashes() ) ),
		'style-src'       => "'self' 'unsafe-inline'",
		'img-src'         => "'self' data:",
		'font-src'        => "'self'",
		'connect-src'     => "'self'",
		'frame-src'       => "'none'",
		'object-src'      => "'none'",
		'base-uri'        => "'self'",
		'form-action'     => "'self' https://wa.me https://api.whatsapp.com", // Devis choisi par WhatsApp : redirection vers la conversation.
		'frame-ancestors' => "'self'",
	);
	// Barre d'administration : avatar Gravatar et police d'icônes intégrée, seulement pour les comptes connectés.
	if ( is_user_logged_in() ) {
		$directives['img-src']  .= ' https://secure.gravatar.com';
		$directives['font-src'] .= ' data:';
	}
	$policy = array();
	foreach ( $directives as $name => $value ) {
		$policy[] = $name . ' ' . $value;
	}
	if ( is_ssl() ) {
		$policy[] = 'upgrade-insecure-requests';
	}
	return implode( '; ', $policy );
}

/**
 * Met la page en mémoire tampon pour envoyer la CSP une fois tous les scripts en ligne connus (pied de page compris).
 * Les empreintes ne dépendent que du contenu : la politique reste valable si la page est mise en cache.
 */
function start_csp(): void {
	if ( is_customize_preview() || is_feed() || is_robots() ) {
		return;
	}
	ob_start(
		static function ( string $html ): string {
			if ( ! headers_sent() ) {
				header( 'Content-Security-Policy: ' . csp() );
			}
			return $html;
		}
	);
}

/**
 * Retire la liste des comptes de l'API REST pour les visiteurs non connectés (l'éditeur en a besoin une fois connecté).
 *
 * @param array<string,mixed> $endpoints Routes de l'API.
 * @return array<string,mixed>
 */
function hide_user_endpoints( array $endpoints ): array {
	if ( is_user_logged_in() ) {
		return $endpoints;
	}
	foreach ( array_keys( $endpoints ) as $route ) {
		if ( str_starts_with( $route, '/wp/v2/users' ) ) {
			unset( $endpoints[ $route ] );
		}
	}
	return $endpoints;
}

/**
 * Archives d'auteur (et « ?author=1 », qui redirige vers l'identifiant du compte) : page introuvable.
 */
function disable_author_archives(): void {
	global $wp_query;
	if ( is_author() ) {
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}
}

/**
 * Retire le nom et le lien de l'auteur des données d'intégration (oEmbed).
 *
 * @param array<string,mixed> $data Données renvoyées.
 * @return array<string,mixed>
 */
function strip_oembed_author( array $data ): array {
	unset( $data['author_name'], $data['author_url'] );
	return $data;
}

/**
 * Clé de comptage des échecs pour l'adresse IP en cours (adresse hachée).
 */
function login_key(): string {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	return 'snex_login_' . md5( wp_hash( $ip ) );
}

/**
 * Compte un échec de connexion.
 */
function count_failure(): void {
	$key = login_key();
	set_transient( $key, (int) get_transient( $key ) + 1, LOGIN_WINDOW * MINUTE_IN_SECONDS );
}

/**
 * Bloque la connexion après trop d'échecs, et donne le même message que l'identifiant ou le mot de passe soit faux.
 *
 * @param \WP_User|\WP_Error|null $user     Résultat de l'authentification.
 * @param string                  $username Identifiant saisi.
 * @return \WP_User|\WP_Error|null
 */
function limit_login( $user, $username ) {
	if ( '' === (string) $username ) {
		return $user;
	}
	if ( (int) get_transient( login_key() ) >= LOGIN_MAX ) {
		return new \WP_Error( 'snex_login_locked', sprintf( 'Trop de tentatives de connexion. Réessayez dans %d minutes.', LOGIN_WINDOW ) );
	}
	if ( is_wp_error( $user ) && array_intersect( $user->get_error_codes(), array( 'invalid_username', 'invalid_email', 'incorrect_password' ) ) ) {
		return new \WP_Error( 'snex_login_failed', 'Identifiant ou mot de passe incorrect.' );
	}
	return $user;
}
