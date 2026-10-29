<?php
/**
 * Tests des coordonnées centralisées, exécutés dans WordPress :
 *   php tools/wp-cli.phar --path=.local/wordpress eval-file tests/coordonnees.php
 * Les réglages enregistrés sont restaurés à la fin.
 *
 * @package SnexCore
 */

use Snex\Core\Coordonnees as C;

defined( 'ABSPATH' ) || exit;

$snex_saved   = get_option( C\OPTION );
$snex_results = array();
$snex_check   = static function ( string $label, bool $ok ) use ( &$snex_results ): void {
	$snex_results[ $label ] = $ok;
};

try {
	$snex_check( 'Numéro ivoirien à 8 chiffres refusé', false === C\is_valid_international( '22508565007' ) );
	$snex_check( 'Numéro ivoirien à 10 chiffres accepté', true === C\is_valid_international( '2250102030405' ) );

	update_option( C\OPTION, C\defaults() );
	$snex_check( 'Bouton Appeler masqué tant que le numéro n\'est pas confirmé', '' === C\value( 'tel_url' ) );

	$snex_clean = C\sanitize(
		array(
			'telephone_affiche'       => '+225 01 02 03 04 05',
			'telephone_international' => '+225 01 02 03 04 05',
			'telephone_confirme'      => '1',
			'email'                   => 'pas-un-email',
		)
	);
	$snex_check( 'Numéro international réduit aux chiffres', '2250102030405' === $snex_clean['telephone_international'] );
	$snex_check( 'Confirmation conservée pour un numéro valide', true === $snex_clean['telephone_confirme'] );
	$snex_check( 'E-mail invalide rejeté', '' === $snex_clean['email'] );

	$snex_bad = C\sanitize(
		array(
			'whatsapp'          => '22508565007',
			'whatsapp_confirme' => '1',
		)
	);
	$snex_check( 'WhatsApp non activé avec un numéro à 8 chiffres', false === $snex_bad['whatsapp_confirme'] );

	update_option( C\OPTION, $snex_clean );
	$snex_check( 'Lien tel: généré après confirmation', 'tel:+2250102030405' === C\value( 'tel_url' ) );

	$snex_html = do_blocks( '<!-- wp:button {"metadata":{"bindings":{"url":{"source":"snex/coordonnees","args":{"key":"whatsapp_url"}}}}} --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button">WhatsApp</a></div><!-- /wp:button -->' );
	$snex_check( 'Bouton WhatsApp absent du rendu sans compte confirmé', '' === trim( $snex_html ) );

	$snex_html = do_blocks( '<!-- wp:button {"metadata":{"bindings":{"url":{"source":"snex/coordonnees","args":{"key":"tel_url"}}}}} --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button">Appeler</a></div><!-- /wp:button -->' );
	$snex_check( 'Numéro affiché sous le bouton Appeler', str_contains( $snex_html, '<span class="btn-num">+225 01 02 03 04 05</span></a>' ) );

	$snex_html = do_blocks( '<!-- wp:button {"metadata":{"bindings":{"url":{"source":"snex/coordonnees","args":{"key":"tel_url"}},"text":{"source":"snex/coordonnees","args":{"key":"tel_label"}}}}} --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button">Appeler</a></div><!-- /wp:button -->' );
	$snex_check( 'Numéro non répété quand le libellé le contient déjà', ! str_contains( $snex_html, 'btn-num' ) );

	$snex_html = do_blocks( '<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"snex/coordonnees","args":{"key":"telephone2"}}}}} --><p>x</p><!-- /wp:paragraph -->' );
	$snex_check( 'Second téléphone masqué quand il est vide', '' === trim( $snex_html ) );

	$snex_html = do_blocks( '<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"snex/coordonnees","args":{"key":"telephone"}}}}} --><p>x</p><!-- /wp:paragraph -->' );
	$snex_check( 'Paragraphe lié affiche le téléphone enregistré', str_contains( $snex_html, '+225 01 02 03 04 05' ) );
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
