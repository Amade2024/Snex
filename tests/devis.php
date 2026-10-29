<?php
/**
 * Tests du formulaire de devis, exécutés dans WordPress :
 *   php tools/wp-cli.phar --path=.local/wordpress eval-file tests/devis.php
 *
 * @package SnexCore
 */

use Snex\Core\Devis as D;

defined( 'ABSPATH' ) || exit;

$snex_results = array();
$snex_check   = static function ( string $label, bool $ok ) use ( &$snex_results ): void {
	$snex_results[ $label ] = $ok;
};

$snex_valid = array(
	'nom'           => 'Awa Koné',
	'entreprise'    => '',
	'telephone'     => '07 08 56 50 07',
	'email'         => 'awa@kone.ci',
	'contact'       => 'whatsapp',
	'ville'         => 'Abidjan',
	'commune'       => 'Cocody',
	'service'       => 'photovoltaique',
	'service_autre' => '',
	'budget'        => '',
	'description'   => 'Installation solaire en toiture pour un entrepôt.',
	'consentement'  => true,
);

$snex_check( 'Numéro ivoirien à 10 chiffres accepté', D\is_valid_phone( '07 08 56 50 07' ) );
$snex_check( 'Numéro ivoirien à 8 chiffres refusé', ! D\is_valid_phone( '08 56 50 07' ) );
$snex_check( 'Numéro +225 à 10 chiffres accepté', D\is_valid_phone( '+225 07 08 56 50 07' ) );
$snex_check( 'Numéro 00225 accepté', D\is_valid_phone( '00225 0708565007' ) );
$snex_check( 'Numéro +225 à 8 chiffres refusé', ! D\is_valid_phone( '+225 08 56 50 07' ) );
$snex_check( 'Numéro étranger accepté', D\is_valid_phone( '+33 6 12 34 56 78' ) );

$snex_check( 'Demande complète acceptée', array() === D\validate( $snex_valid ) );
$snex_check( 'Entreprise et budget facultatifs', array() === D\validate( $snex_valid ) );
$snex_check( 'Ville hors liste acceptée', array() === D\validate( array_merge( $snex_valid, array( 'ville' => 'Ouagadougou' ) ) ) );

$snex_check( 'Montant groupé par milliers', '5 000 000' === D\format_amount( '5000000' ) );
$snex_check( 'Montant nettoyé (lettres, points)', '2 500 000' === D\format_amount( '2.500.000 FCFA' ) );
$snex_check( 'Montant vide ou nul ignoré', '' === D\format_amount( 'à voir' ) && '' === D\format_amount( '000' ) );
$snex_check( 'Budget non précisé dans le récapitulatif', 'Non précisé' === D\summary( $snex_valid )['Budget estimé'] );
$snex_check( 'Budget saisi suivi de FCFA', '750 000 FCFA' === D\summary( array_merge( $snex_valid, array( 'budget' => '750 000' ) ) )['Budget estimé'] );

// Demande par WhatsApp : lien vers le numéro confirmé dans Coordonnées, message déjà rédigé.
$snex_wa_saved = get_option( Snex\Core\Coordonnees\OPTION );
$snex_wa_set   = static function ( bool $confirmed ) use ( $snex_wa_saved ): void {
	update_option(
		Snex\Core\Coordonnees\OPTION,
		array_merge(
			Snex\Core\Coordonnees\get_all(),
			array(
				'whatsapp'          => '2250566192757',
				'whatsapp_confirme' => $confirmed,
			)
		)
	);
};
$snex_wa_set( true );
$snex_wa      = D\whatsapp_url( $snex_valid, 2, true );
$snex_wa_text = rawurldecode( (string) substr( $snex_wa, (int) strpos( $snex_wa, 'text=' ) + 5 ) );
$snex_check( 'WhatsApp : lien vers le numéro de l\'entreprise', str_starts_with( $snex_wa, 'https://wa.me/2250566192757?text=' ) );
$snex_check( 'WhatsApp : message avec nom, service et description', str_contains( $snex_wa_text, 'Awa Koné' ) && str_contains( $snex_wa_text, 'Photovoltaïque' ) && str_contains( $snex_wa_text, 'Installation solaire en toiture' ) );
$snex_check( 'WhatsApp : documents signalés comme envoyés par e-mail', str_contains( $snex_wa_text, 'Mes documents (2) vous ont été envoyés par e-mail.' ) );
$snex_check( 'WhatsApp : documents à joindre si l\'e-mail a échoué', str_contains( rawurldecode( D\whatsapp_url( $snex_valid, 1, false ) ), 'Je vous envoie mes documents (1) dans cette conversation.' ) );
$snex_wa_set( false );
$snex_check( 'WhatsApp non confirmé : pas de redirection', '' === D\whatsapp_url( $snex_valid, 0, true ) );
update_option( Snex\Core\Coordonnees\OPTION, $snex_wa_saved );

$snex_errors = D\validate( array_merge( $snex_valid, array( 'service' => 'autre' ) ) );
$snex_check( '« Autre besoin » demande une précision', array( 'service_autre' ) === array_keys( $snex_errors ) );

$snex_errors = D\validate( array_merge( $snex_valid, array( 'consentement' => false ) ) );
$snex_check( 'Envoi refusé sans consentement', isset( $snex_errors['consentement'] ) );

$snex_errors = D\validate( array_fill_keys( array_keys( $snex_valid ), '' ) );
$snex_check(
	'Formulaire vide : tous les champs obligatoires signalés',
	array( 'nom', 'telephone', 'email', 'ville', 'commune', 'service', 'description', 'consentement' ) === array_keys( $snex_errors )
);

$snex_check( 'Jeton récent reconnu', 0 === D\token_age( D\token() ) );
$snex_check( 'Jeton falsifié rejeté', null === D\token_age( time() - 60 . '.abc' ) );

$snex_summary = D\summary(
	array_merge(
		$snex_valid,
		array(
			'service'       => 'autre',
			'service_autre' => 'Groupe électrogène',
		)
	)
);
$snex_check( 'Récapitulatif : besoin libre repris', 'Autre : Groupe électrogène' === $snex_summary['Service'] );

$snex_html = do_blocks( '<!-- wp:snex/devis /-->' );
$snex_check( 'Bloc affiché avec ses trois étapes', 3 === substr_count( $snex_html, 'class="devis__step"' ) );
$snex_check( 'Bloc protégé par un nonce', str_contains( $snex_html, 'name="_devis_nonce"' ) );

$snex_failed = 0;
foreach ( $snex_results as $snex_label => $snex_ok ) {
	WP_CLI::log( ( $snex_ok ? 'RÉUSSI  ' : 'ÉCHEC   ' ) . $snex_label );
	$snex_failed += $snex_ok ? 0 : 1;
}
if ( $snex_failed ) {
	WP_CLI::error( "$snex_failed test(s) en échec." );
}
WP_CLI::success( count( $snex_results ) . ' tests réussis.' );
