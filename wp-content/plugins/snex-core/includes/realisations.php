<?php
/**
 * Réalisations : type de contenu, domaines d'activité, lieu et année.
 *
 * @package SnexCore
 */

namespace Snex\Core\Realisations;

defined( 'ABSPATH' ) || exit;

const POST_TYPE = 'realisation';
const TAXONOMY  = 'domaine';
const META_LIEU = 'snex_lieu';
const META_AN   = 'snex_annee';

/**
 * Domaines d'activité du cahier des charges (slug => nom).
 */
const DOMAINES = array(
	'telecommunications' => 'Télécommunications',
	'energie'            => 'Énergie',
	'photovoltaique'     => 'Photovoltaïque',
	'formation'          => 'Formation',
);

/**
 * Branche les crochets WordPress.
 */
function bootstrap(): void {
	add_action( 'init', __NAMESPACE__ . '\\register_content_types' );
	add_action( 'add_meta_boxes_' . POST_TYPE, __NAMESPACE__ . '\\add_details_box' );
	add_action( 'save_post_' . POST_TYPE, __NAMESPACE__ . '\\save_details', 10, 2 );
	add_filter( 'manage_' . POST_TYPE . '_posts_columns', __NAMESPACE__ . '\\admin_columns' );
	add_action( 'manage_' . POST_TYPE . '_posts_custom_column', __NAMESPACE__ . '\\admin_column_value', 10, 2 );
}

/**
 * Enregistre le type « Réalisation », la taxonomie « Domaine » et les métadonnées.
 */
function register_content_types(): void {
	register_post_type(
		POST_TYPE,
		array(
			'labels'        => array(
				'name'               => 'Réalisations',
				'singular_name'      => 'Réalisation',
				'menu_name'          => 'Réalisations',
				'add_new'            => 'Ajouter',
				'add_new_item'       => 'Ajouter une réalisation',
				'edit_item'          => 'Modifier la réalisation',
				'new_item'           => 'Nouvelle réalisation',
				'view_item'          => 'Voir la réalisation',
				'search_items'       => 'Rechercher une réalisation',
				'not_found'          => 'Aucune réalisation',
				'not_found_in_trash' => 'Aucune réalisation dans la corbeille',
				'all_items'          => 'Toutes les réalisations',
			),
			'public'        => true,
			'has_archive'   => 'realisations',
			'rewrite'       => array(
				'slug'       => 'realisations',
				'with_front' => false,
			),
			'menu_icon'     => 'dashicons-portfolio',
			'menu_position' => 20,
			'show_in_rest'  => true,
			'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
			'template'      => array(
				array(
					'core/paragraph',
					array( 'placeholder' => 'Contexte : besoin du client, type de site, contraintes…' ),
				),
				array(
					'core/heading',
					array(
						'level'   => 2,
						'content' => 'Travaux réalisés',
					),
				),
				array( 'core/list' ),
				array( 'core/gallery', array( 'linkTo' => 'none' ) ),
			),
		)
	);

	register_taxonomy(
		TAXONOMY,
		POST_TYPE,
		array(
			'labels'            => array(
				'name'          => 'Domaines',
				'singular_name' => 'Domaine',
				'menu_name'     => 'Domaines',
				'all_items'     => 'Tous les domaines',
				'edit_item'     => 'Modifier le domaine',
				'add_new_item'  => 'Ajouter un domaine',
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array(
				'slug'       => 'domaine',
				'with_front' => false,
			),
		)
	);

	$meta_args = array(
		'type'              => 'string',
		'single'            => true,
		'show_in_rest'      => true,
		'sanitize_callback' => 'sanitize_text_field',
		'auth_callback'     => static fn( $allowed, $meta_key, $post_id ) => current_user_can( 'edit_post', $post_id ),
	);
	register_post_meta( POST_TYPE, META_LIEU, $meta_args + array( 'label' => 'Lieu de réalisation' ) );
	register_post_meta(
		POST_TYPE,
		META_AN,
		array_merge(
			$meta_args,
			array(
				'label'             => 'Année',
				'sanitize_callback' => __NAMESPACE__ . '\\sanitize_year',
			)
		)
	);
}

/**
 * Crée les domaines s'ils n'existent pas encore.
 */
function seed_domains(): void {
	foreach ( DOMAINES as $slug => $name ) {
		if ( ! term_exists( $slug, TAXONOMY ) ) {
			wp_insert_term( $name, TAXONOMY, array( 'slug' => $slug ) );
		}
	}
}

/**
 * Garde une année sur 4 chiffres, ou une chaîne vide.
 *
 * @param mixed $value Valeur saisie.
 */
function sanitize_year( $value ): string {
	$value = preg_replace( '/\D/', '', (string) $value );
	return ( 4 === strlen( $value ) ) ? $value : '';
}

/**
 * Ajoute l'encadré « Lieu et année » dans l'éditeur.
 */
function add_details_box(): void {
	add_meta_box( 'snex-realisation-details', 'Lieu et année', __NAMESPACE__ . '\\render_details_box', POST_TYPE, 'side', 'high' );
}

/**
 * Affiche les champs Lieu et Année.
 *
 * @param \WP_Post $post Réalisation en cours.
 */
function render_details_box( \WP_Post $post ): void {
	wp_nonce_field( 'snex_realisation_details', 'snex_realisation_nonce' );
	$lieu  = get_post_meta( $post->ID, META_LIEU, true );
	$annee = get_post_meta( $post->ID, META_AN, true );
	?>
	<p>
		<label for="snex-lieu"><strong>Lieu de réalisation</strong></label><br>
		<input type="text" id="snex-lieu" name="snex_lieu" class="widefat" value="<?php echo esc_attr( $lieu ); ?>" placeholder="Ex. : Abidjan, Côte d'Ivoire">
	</p>
	<p>
		<label for="snex-annee"><strong>Année</strong></label><br>
		<input type="text" id="snex-annee" name="snex_annee" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" value="<?php echo esc_attr( $annee ); ?>" placeholder="2026">
	</p>
	<p class="description">Indiquez un lieu réel et vérifié. Le nom du client n'apparaît que s'il a donné son accord.</p>
	<?php
}

/**
 * Enregistre le lieu et l'année.
 *
 * @param int      $post_id Identifiant de la réalisation.
 * @param \WP_Post $post    Réalisation.
 */
function save_details( int $post_id, \WP_Post $post ): void {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	$nonce = isset( $_POST['snex_realisation_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['snex_realisation_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'snex_realisation_details' ) || ! current_user_can( 'edit_post', $post_id ) || POST_TYPE !== $post->post_type ) {
		return;
	}
	$lieu  = isset( $_POST['snex_lieu'] ) ? sanitize_text_field( wp_unslash( $_POST['snex_lieu'] ) ) : '';
	$annee = isset( $_POST['snex_annee'] ) ? sanitize_year( sanitize_text_field( wp_unslash( $_POST['snex_annee'] ) ) ) : '';
	update_post_meta( $post_id, META_LIEU, $lieu );
	update_post_meta( $post_id, META_AN, $annee );
}

/**
 * Ajoute les colonnes Lieu et Année à la liste des réalisations.
 *
 * @param array<string,string> $columns Colonnes existantes.
 * @return array<string,string>
 */
function admin_columns( array $columns ): array {
	$columns['snex_lieu']  = 'Lieu';
	$columns['snex_annee'] = 'Année';
	return $columns;
}

/**
 * Remplit les colonnes Lieu et Année.
 *
 * @param string $column  Colonne.
 * @param int    $post_id Réalisation.
 */
function admin_column_value( string $column, int $post_id ): void {
	if ( 'snex_lieu' === $column ) {
		echo esc_html( get_post_meta( $post_id, META_LIEU, true ) );
	} elseif ( 'snex_annee' === $column ) {
		echo esc_html( get_post_meta( $post_id, META_AN, true ) );
	}
}
