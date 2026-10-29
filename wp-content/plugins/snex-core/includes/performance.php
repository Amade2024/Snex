<?php
/**
 * Performance : images dimensionnées et chargées au bon moment, en-tête de page allégé.
 *
 * Les blocs Image insérés depuis les motifs pointent vers un fichier sans identifiant de média : WordPress ne peut
 * alors ni indiquer leurs dimensions (la page « saute » au chargement), ni proposer des tailles réduites, ni différer
 * leur chargement. Le média correspondant est retrouvé à l'affichage, sans modifier le contenu enregistré.
 *
 * @package SnexCore
 */

namespace Snex\Core\Performance;

defined( 'ABSPATH' ) || exit;

/**
 * Branche les crochets WordPress.
 */
function bootstrap(): void {
	// Avant wp_filter_content_tags (priorité 12 de the_content), qui ajoute dimensions, srcset et chargement différé.
	add_filter( 'render_block_core/image', __NAMESPACE__ . '\\link_image', 10, 2 );

	// Pas de blog : flux RSS et liens d'intégration inutiles dans l'en-tête.
	remove_action( 'wp_head', 'feed_links', 2 );
	remove_action( 'wp_head', 'feed_links_extra', 3 );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
}

/**
 * Dimensions d'une image fournie avec le thème (SVG compris, d'après son viewBox).
 *
 * @param string $file Chemin du fichier.
 * @return array{0:int,1:int}|null
 */
function theme_image_size( string $file ): ?array {
	if ( ! is_readable( $file ) ) {
		return null;
	}
	if ( str_ends_with( $file, '.svg' ) ) {
		$svg = (string) file_get_contents( $file, false, null, 0, 2048 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- fichier local du thème.
		if ( preg_match( '/viewBox="[\d.\s-]*?\s([\d.]+)\s+([\d.]+)"/', $svg, $m ) ) {
			return array( (int) round( (float) $m[1] ), (int) round( (float) $m[2] ) );
		}
		return null;
	}
	$size = wp_getimagesize( $file );
	return $size ? array( (int) $size[0], (int) $size[1] ) : null;
}

/**
 * Relie l'image d'un bloc à son média (classe wp-image-ID), ou lui donne ses dimensions si elle vient du thème.
 *
 * @param string              $html  Rendu du bloc.
 * @param array<string,mixed> $block Bloc analysé.
 */
function link_image( string $html, array $block ): string {
	if ( ! empty( $block['attrs']['id'] ) || str_contains( $html, 'wp-image-' ) ) {
		return $html;
	}
	$p = new \WP_HTML_Tag_Processor( $html );
	if ( ! $p->next_tag( 'img' ) || null !== $p->get_attribute( 'width' ) ) {
		return $html;
	}
	$src = (string) $p->get_attribute( 'src' );
	$id  = attachment_url_to_postid( $src );
	if ( $id ) {
		$p->add_class( 'wp-image-' . $id );
		return $p->get_updated_html();
	}
	$base = get_theme_file_uri( 'assets/images/' );
	if ( str_starts_with( $src, $base ) ) {
		$size = theme_image_size( get_theme_file_path( 'assets/images/' . basename( $src ) ) );
		if ( $size ) {
			$p->set_attribute( 'width', (string) $size[0] );
			$p->set_attribute( 'height', (string) $size[1] );
			return $p->get_updated_html();
		}
	}
	return $html;
}
