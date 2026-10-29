<?php
/**
 * Thème S-NEX : chargement des styles, styles de boutons et catégorie de motifs.
 *
 * @package Snex
 */

namespace Snex\Theme;

defined( 'ABSPATH' ) || exit;

/**
 * Feuille de style du thème, sur le site et dans l'éditeur.
 */
function enqueue_styles(): void {
	$version = wp_get_theme()->get( 'Version' );
	wp_enqueue_style( 'snex', get_stylesheet_uri(), array(), $version );
	wp_enqueue_script(
		'snex-protection-contenu',
		get_theme_file_uri( 'assets/protection-contenu.js' ),
		array(),
		$version,
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
	wp_enqueue_script(
		'snex-retour-haut',
		get_theme_file_uri( 'assets/retour-haut.js' ),
		array(),
		$version,
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_styles' );

/**
 * Réglages du thème.
 */
function setup(): void {
	add_editor_style( 'style.css' );
	remove_theme_support( 'core-block-patterns' );
}
add_action( 'after_setup_theme', __NAMESPACE__ . '\\setup' );

/**
 * Variantes de boutons utilisées par la charte.
 */
function register_block_styles(): void {
	$styles = array(
		'clair'   => 'Contour clair (sur fond nuit)',
		'nuit'    => 'Plein nuit',
		'contour' => 'Contour nuit',
		'lien'    => 'Lien fléché',
	);
	foreach ( $styles as $name => $label ) {
		register_block_style(
			'core/button',
			array(
				'name'  => $name,
				'label' => $label,
			)
		);
	}
}
add_action( 'init', __NAMESPACE__ . '\\register_block_styles' );

/**
 * Catégorie de motifs « S-NEX » dans l'outil d'insertion.
 */
function register_pattern_category(): void {
	register_block_pattern_category( 'snex', array( 'label' => 'S-NEX' ) );
}
add_action( 'init', __NAMESPACE__ . '\\register_pattern_category' );

/**
 * Retire le script des émojis, inutile ici.
 */
function disable_emojis(): void {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
}
add_action( 'init', __NAMESPACE__ . '\\disable_emojis' );

/**
 * Précharge les deux polices visibles dès l'affichage (texte courant et titres) pour éviter le changement de police.
 */
function preload_fonts(): void {
	foreach ( array( 'inter-latin-400-normal.woff2', 'barlow-semi-condensed-latin-700-normal.woff2' ) as $font ) {
		printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin />' . "\n", esc_url( get_theme_file_uri( 'assets/fonts/' . $font ) ) );
	}
}
add_action( 'wp_head', __NAMESPACE__ . '\\preload_fonts', 1 );

/**
 * URL d'une image fournie avec le thème (images par défaut des motifs).
 *
 * @param string $file Nom du fichier dans assets/images.
 */
function image( string $file ): string {
	return esc_url( get_theme_file_uri( 'assets/images/' . $file ) );
}
