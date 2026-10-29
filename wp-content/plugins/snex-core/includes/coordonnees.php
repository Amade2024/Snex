<?php
/**
 * Coordonnées centralisées : réglages, source de liaison de blocs « snex/coordonnees ».
 *
 * Une seule saisie alimente l'en-tête, le pied de page, la page Contact et les boutons Appeler / WhatsApp.
 * Les liens d'appel et WhatsApp ne s'affichent que lorsque le numéro a été confirmé.
 *
 * @package SnexCore
 */

namespace Snex\Core\Coordonnees;

defined( 'ABSPATH' ) || exit;

const OPTION = 'snex_coordonnees';
const SOURCE = 'snex/coordonnees';
const SLUG   = 'snex-coordonnees';
const CAP    = 'edit_pages';

/**
 * Branche les crochets WordPress.
 */
function bootstrap(): void {
	add_action( 'init', __NAMESPACE__ . '\\register_source' );
	add_action( 'admin_menu', __NAMESPACE__ . '\\add_page' );
	add_action( 'admin_init', __NAMESPACE__ . '\\register_settings' );
	add_filter( 'option_page_capability_' . OPTION, static fn() => CAP );
	add_filter( 'render_block', __NAMESPACE__ . '\\hide_empty_bound_blocks', 10, 2 );
	add_filter( 'render_block_core/button', __NAMESPACE__ . '\\add_phone_number', 10, 2 );
	add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\\enqueue_editor_values' );
}

/**
 * Transmet les coordonnées à l'éditeur pour les afficher dans les blocs liés.
 */
function enqueue_editor_values(): void {
	$handle = 'snex-coordonnees-editor';
	wp_enqueue_script(
		$handle,
		plugins_url( 'assets/coordonnees-editor.js', \Snex\Core\DIR . '/snex-core.php' ),
		array( 'wp-blocks' ),
		\Snex\Core\VERSION,
		true
	);
	$keys   = array( 'raison_sociale', 'telephone', 'telephone2', 'tel_label', 'tel_url', 'whatsapp_url', 'email', 'mailto_url', 'adresse', 'carte_url', 'copyright', 'rccm' );
	$values = array_combine( $keys, array_map( __NAMESPACE__ . '\\value', $keys ) );
	wp_add_inline_script( $handle, 'window.snexCoordonnees = ' . wp_json_encode( $values ) . ';', 'before' );
}

/**
 * Valeurs par défaut, reprises du cahier des charges. Le numéro international reste vide tant qu'il n'est pas confirmé.
 *
 * @return array<string,string|bool>
 */
function defaults(): array {
	return array(
		'raison_sociale'          => 'S-NEX SARL',
		'telephone_affiche'       => '+225 07 13 27 87 59',
		'telephone_international' => '',
		'telephone_confirme'      => false,
		'telephone2_affiche'      => '+225 05 66 19 27 57 (WhatsApp)',
		'whatsapp'                => '',
		'whatsapp_confirme'       => false,
		'email'                   => 'direction@snexsarl.com',
		'adresse'                 => "Port-Bouët, Gonzagueville, Abidjan, Côte d'Ivoire",
		'carte_url'               => '',
		'rccm'                    => 'CI-ABJ-03-2026-B12-03677',
	);
}

/**
 * Enregistre les valeurs par défaut à l'activation, sans écraser une saisie existante.
 */
function seed_defaults(): void {
	add_option( OPTION, defaults() );
}

/**
 * Coordonnées enregistrées, complétées par les valeurs par défaut.
 *
 * @return array<string,string|bool>
 */
function get_all(): array {
	$saved = get_option( OPTION, array() );
	return wp_parse_args( is_array( $saved ) ? $saved : array(), defaults() );
}

/**
 * Valeur prête à afficher pour une clé de liaison.
 *
 * @param string $key Clé demandée par le bloc.
 */
function value( string $key ): string {
	$c = get_all();
	switch ( $key ) {
		case 'raison_sociale':
			return (string) $c['raison_sociale'];
		case 'telephone':
			return (string) $c['telephone_affiche'];
		case 'telephone2':
			return (string) $c['telephone2_affiche'];
		case 'tel_label':
			return $c['telephone_affiche'] ? 'Appeler le ' . $c['telephone_affiche'] : '';
		case 'tel_url':
			return ( $c['telephone_confirme'] && $c['telephone_international'] ) ? 'tel:+' . $c['telephone_international'] : '';
		case 'whatsapp_url':
			return ( $c['whatsapp_confirme'] && $c['whatsapp'] ) ? 'https://wa.me/' . $c['whatsapp'] : '';
		case 'email':
			return (string) $c['email'];
		case 'mailto_url':
			return $c['email'] ? 'mailto:' . $c['email'] : '';
		case 'adresse':
			return (string) $c['adresse'];
		case 'carte_url':
			return (string) $c['carte_url'];
		case 'copyright':
			return '© ' . wp_date( 'Y' ) . ' ' . $c['raison_sociale'];
		case 'rccm':
			return $c['rccm'] ? $c['raison_sociale'] . ' – RCCM n° ' . $c['rccm'] : '';
	}
	return '';
}

/**
 * Déclare la source de liaison utilisée par les blocs du thème.
 */
function register_source(): void {
	if ( ! function_exists( 'register_block_bindings_source' ) ) {
		return;
	}
	register_block_bindings_source(
		SOURCE,
		array(
			'label'              => 'Coordonnées S-NEX',
			'get_value_callback' => static function ( array $args ): string {
				$val = value( isset( $args['key'] ) ? (string) $args['key'] : '' );
				return str_ends_with( (string) ( $args['key'] ?? '' ), '_url' ) ? esc_url( $val ) : esc_html( $val );
			},
		)
	);
}

/**
 * Masque un bloc lié à une coordonnée vide (ex. : bouton WhatsApp tant que le numéro n'est pas confirmé).
 *
 * @param string              $html  Rendu du bloc.
 * @param array<string,mixed> $block Bloc analysé.
 */
function hide_empty_bound_blocks( string $html, array $block ): string {
	$bindings = $block['attrs']['metadata']['bindings'] ?? null;
	if ( ! is_array( $bindings ) ) {
		return $html;
	}
	foreach ( array( 'url', 'content', 'text' ) as $attr ) {
		$binding = $bindings[ $attr ] ?? null;
		if ( is_array( $binding ) && SOURCE === ( $binding['source'] ?? '' ) && '' === value( (string) ( $binding['args']['key'] ?? '' ) ) ) {
			return '';
		}
	}
	return $html;
}

/**
 * Affiche le numéro sous le libellé des boutons d'appel (liés à « tel_url »), une fois le numéro confirmé.
 *
 * @param string              $html  Rendu du bouton (vide s'il a été masqué).
 * @param array<string,mixed> $block Bloc analysé.
 */
function add_phone_number( string $html, array $block ): string {
	$bindings = $block['attrs']['metadata']['bindings'] ?? array();
	$binding  = $bindings['url'] ?? null;
	// Un libellé lié (« Appeler le +225… ») contient déjà le numéro.
	if ( '' === $html || isset( $bindings['text'] ) || ! is_array( $binding ) || SOURCE !== ( $binding['source'] ?? '' ) || 'tel_url' !== ( $binding['args']['key'] ?? '' ) ) {
		return $html;
	}
	$tel = value( 'telephone' );
	return $tel ? (string) preg_replace( '#</a>#', '<span class="btn-num">' . esc_html( $tel ) . '</span></a>', $html, 1 ) : $html;
}

/**
 * Ajoute la page « Coordonnées » au menu d'administration (accessible aux éditeurs).
 */
function add_page(): void {
	add_menu_page( 'Coordonnées de l\'entreprise', 'Coordonnées', CAP, SLUG, __NAMESPACE__ . '\\render_page', 'dashicons-id', 21 );
}

/**
 * Déclare le réglage et sa validation.
 */
function register_settings(): void {
	register_setting(
		OPTION,
		OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => __NAMESPACE__ . '\\sanitize',
			'default'           => defaults(),
		)
	);
}

/**
 * Nettoie et vérifie la saisie.
 *
 * @param mixed $input Données envoyées par le formulaire.
 * @return array<string,string|bool>
 */
function sanitize( $input ): array {
	$input  = is_array( $input ) ? $input : array();
	$digits = static fn( $v ) => preg_replace( '/\D/', '', (string) $v );
	$out    = array(
		'raison_sociale'          => sanitize_text_field( $input['raison_sociale'] ?? '' ),
		'telephone_affiche'       => sanitize_text_field( $input['telephone_affiche'] ?? '' ),
		'telephone_international' => $digits( $input['telephone_international'] ?? '' ),
		'telephone_confirme'      => ! empty( $input['telephone_confirme'] ),
		'telephone2_affiche'      => sanitize_text_field( $input['telephone2_affiche'] ?? '' ),
		'whatsapp'                => $digits( $input['whatsapp'] ?? '' ),
		'whatsapp_confirme'       => ! empty( $input['whatsapp_confirme'] ),
		'email'                   => sanitize_email( $input['email'] ?? '' ),
		'adresse'                 => sanitize_text_field( $input['adresse'] ?? '' ),
		'carte_url'               => esc_url_raw( $input['carte_url'] ?? '', array( 'https' ) ),
		'rccm'                    => sanitize_text_field( $input['rccm'] ?? '' ),
	);

	foreach ( array(
		'telephone_international' => 'telephone_confirme',
		'whatsapp'                => 'whatsapp_confirme',
	) as $field => $flag ) {
		if ( ! $out[ $flag ] ) {
			continue;
		}
		if ( ! is_valid_international( $out[ $field ] ) ) {
			$out[ $flag ] = false;
			add_settings_error( OPTION, $field, 'Numéro international invalide : saisissez l\'indicatif sans « + » suivi du numéro (en Côte d\'Ivoire : 225 puis les 10 chiffres). Le bouton reste masqué.' );
		}
	}
	if ( ! empty( $input['email'] ) && '' === $out['email'] ) {
		add_settings_error( OPTION, 'email', 'Adresse e-mail invalide : l\'ancienne valeur est remplacée par un champ vide.' );
	}
	return $out;
}

/**
 * Vérifie un numéro au format international sans « + ». Les numéros ivoiriens comptent 10 chiffres depuis le 31/01/2021.
 *
 * @param string $digits Chiffres seuls.
 */
function is_valid_international( string $digits ): bool {
	if ( str_starts_with( $digits, '225' ) ) {
		return 13 === strlen( $digits );
	}
	return (bool) preg_match( '/^\d{8,15}$/', $digits );
}

/**
 * Affiche la page de réglages.
 */
function render_page(): void {
	if ( ! current_user_can( CAP ) ) {
		return;
	}
	$c    = get_all();
	$name = static fn( string $k ) => esc_attr( OPTION . '[' . $k . ']' );
	?>
	<div class="wrap">
		<h1>Coordonnées de l'entreprise</h1>
		<p>Ces informations s'affichent dans l'en-tête, le pied de page, la page Contact et les boutons d'appel. Modifiez-les ici une seule fois.</p>
		<?php settings_errors( OPTION ); ?>
		<form method="post" action="options.php">
			<?php settings_fields( OPTION ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="snex-raison">Raison sociale</label></th>
					<td><input id="snex-raison" class="regular-text" name="<?php echo $name( 'raison_sociale' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- échappé par $name. ?>" value="<?php echo esc_attr( $c['raison_sociale'] ); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="snex-tel">Téléphone affiché</label></th>
					<td><input id="snex-tel" class="regular-text" name="<?php echo $name( 'telephone_affiche' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" value="<?php echo esc_attr( $c['telephone_affiche'] ); ?>">
					<p class="description">Tel qu'il apparaît sur le site, par exemple « +225 07 00 00 00 00 ».</p></td>
				</tr>
				<tr>
					<th scope="row"><label for="snex-tel-int">Téléphone au format international</label></th>
					<td><input id="snex-tel-int" class="regular-text" inputmode="numeric" name="<?php echo $name( 'telephone_international' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" value="<?php echo esc_attr( $c['telephone_international'] ); ?>" placeholder="225XXXXXXXXXX">
					<p class="description">Chiffres seuls, sans « + » ni espace : 225 puis les 10 chiffres.</p>
					<label><input type="checkbox" name="<?php echo $name( 'telephone_confirme' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" value="1" <?php checked( $c['telephone_confirme'] ); ?>> Numéro vérifié : afficher les boutons « Appeler »</label></td>
				</tr>
				<tr>
					<th scope="row"><label for="snex-tel2">Second téléphone affiché</label></th>
					<td><input id="snex-tel2" class="regular-text" name="<?php echo $name( 'telephone2_affiche' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" value="<?php echo esc_attr( $c['telephone2_affiche'] ); ?>">
					<p class="description">Affiché sous le premier dans le pied de page et sur la page Contact. Laisser vide pour le masquer.</p></td>
				</tr>
				<tr>
					<th scope="row"><label for="snex-wa">WhatsApp</label></th>
					<td><input id="snex-wa" class="regular-text" inputmode="numeric" name="<?php echo $name( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" value="<?php echo esc_attr( $c['whatsapp'] ); ?>" placeholder="225XXXXXXXXXX">
					<label><input type="checkbox" name="<?php echo $name( 'whatsapp_confirme' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" value="1" <?php checked( $c['whatsapp_confirme'] ); ?>> Compte WhatsApp vérifié : afficher le bouton WhatsApp</label></td>
				</tr>
				<tr>
					<th scope="row"><label for="snex-email">E-mail</label></th>
					<td><input id="snex-email" type="email" class="regular-text" name="<?php echo $name( 'email' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" value="<?php echo esc_attr( $c['email'] ); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="snex-adresse">Adresse</label></th>
					<td><input id="snex-adresse" class="large-text" name="<?php echo $name( 'adresse' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" value="<?php echo esc_attr( $c['adresse'] ); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="snex-carte">Lien Google Maps du siège</label></th>
					<td><input id="snex-carte" type="url" class="large-text" name="<?php echo $name( 'carte_url' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" value="<?php echo esc_attr( $c['carte_url'] ); ?>" placeholder="https://maps.app.goo.gl/…">
					<p class="description">À renseigner une fois l'adresse précise confirmée.</p></td>
				</tr>
				<tr>
					<th scope="row"><label for="snex-rccm">Numéro RCCM</label></th>
					<td><input id="snex-rccm" class="regular-text" name="<?php echo $name( 'rccm' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" value="<?php echo esc_attr( $c['rccm'] ); ?>" placeholder="CI-ABJ-…">
					<p class="description">Affiché dans la bannière d'accueil et dans le pied de page. Laisser vide pour le masquer.</p></td>
				</tr>
			</table>
			<?php submit_button( 'Enregistrer les coordonnées' ); ?>
		</form>
	</div>
	<?php
}
