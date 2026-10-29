<?php
/**
 * Référencement : description, partage sur les réseaux (Open Graph), données structurées, indexation, plan du site.
 *
 * La description de chaque page vient de son extrait (panneau « Extrait » de l'éditeur). Sans extrait, la phrase
 * d'introduction de la page est reprise. Les données structurées n'utilisent que les Coordonnées saisies.
 *
 * @package SnexCore
 */

namespace Snex\Core\Seo;

use Snex\Core\Coordonnees;
use Snex\Core\Realisations;

defined( 'ABSPATH' ) || exit;

const DESCRIPTION_MAX = 160;

/**
 * Branche les crochets WordPress.
 */
function bootstrap(): void {
	add_action( 'init', static fn() => add_post_type_support( 'page', 'excerpt' ) );
	add_action( 'wp_head', __NAMESPACE__ . '\\print_meta', 2 );
	add_filter( 'wp_robots', __NAMESPACE__ . '\\robots' );
	add_filter( 'wp_sitemaps_add_provider', static fn( $provider, string $name ) => 'users' === $name ? false : $provider, 10, 2 );
}

/**
 * Raccourcit un texte à la longueur d'une description, sans couper un mot.
 *
 * @param string $text Texte brut ou HTML.
 */
function shorten( string $text ): string {
	$text = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $text ) ) );
	if ( mb_strlen( $text ) <= DESCRIPTION_MAX ) {
		return $text;
	}
	$cut = mb_substr( $text, 0, DESCRIPTION_MAX - 1 );
	$cut = preg_replace( '/\s+\S*$/u', '', $cut );
	return rtrim( $cut, ' ,;:.–-' ) . '…';
}

/**
 * Description de la page affichée : extrait, sinon phrase d'introduction (paragraphe « lead »), sinon début du texte.
 */
function description(): string {
	if ( is_singular() ) {
		$post = get_queried_object();
		if ( ! $post instanceof \WP_Post || post_password_required( $post ) ) {
			return '';
		}
		if ( '' !== trim( $post->post_excerpt ) ) {
			return shorten( $post->post_excerpt );
		}
		if ( preg_match( '#<p class="lead">(.*?)</p>#s', $post->post_content, $m ) ) {
			return shorten( $m[1] );
		}
		return shorten( excerpt_remove_blocks( strip_shortcodes( $post->post_content ) ) );
	}
	if ( is_tax() || is_category() || is_tag() ) {
		return shorten( term_description() );
	}
	if ( is_post_type_archive() ) {
		$type = get_post_type_object( (string) get_query_var( 'post_type' ) );
		return $type ? shorten( (string) $type->description ) : '';
	}
	return '';
}

/**
 * Image de partage : image mise en avant, sinon première image du contenu, sinon logo.
 */
function share_image(): string {
	if ( is_singular() ) {
		$post = get_queried_object();
		if ( $post instanceof \WP_Post ) {
			$featured = get_the_post_thumbnail_url( $post, 'large' );
			if ( $featured ) {
				return $featured;
			}
			if ( preg_match( '#<img[^>]+src="([^"]+)"#', $post->post_content, $m ) && ! str_ends_with( strtolower( $m[1] ), '.svg' ) ) {
				return html_entity_decode( $m[1] );
			}
		}
	}
	$logo = (int) get_option( 'site_logo' );
	return $logo ? (string) wp_get_attachment_image_url( $logo, 'full' ) : '';
}

/**
 * Données structurées de l'entreprise (schema.org), publiées sur l'accueil.
 *
 * @return array<string,mixed>
 */
function organization(): array {
	$data = array(
		'@context' => 'https://schema.org',
		'@type'    => 'Organization',
		'name'     => Coordonnees\value( 'raison_sociale' ),
		'url'      => home_url( '/' ),
	);
	$logo = (int) get_option( 'site_logo' );
	if ( $logo && wp_get_attachment_image_url( $logo, 'full' ) ) {
		$data['logo'] = wp_get_attachment_image_url( $logo, 'full' );
	}
	if ( Coordonnees\value( 'email' ) ) {
		$data['email'] = Coordonnees\value( 'email' );
	}
	// Numéro publié seulement après confirmation, comme les boutons Appeler.
	if ( Coordonnees\value( 'tel_url' ) ) {
		$data['telephone'] = substr( Coordonnees\value( 'tel_url' ), 4 );
	}
	if ( Coordonnees\value( 'adresse' ) ) {
		$data['address'] = Coordonnees\value( 'adresse' );
	}
	if ( Coordonnees\value( 'carte_url' ) ) {
		$data['hasMap'] = Coordonnees\value( 'carte_url' );
	}
	return $data;
}

/**
 * Balises de description, de partage et données structurées.
 */
function print_meta(): void {
	if ( is_404() || is_search() ) {
		return;
	}
	$description = description();
	$title       = wp_get_document_title();
	$url         = is_singular() ? wp_get_canonical_url() : '';
	$image       = share_image();
	$tags        = array(
		'og:type'      => is_front_page() ? 'website' : 'article',
		'og:site_name' => get_bloginfo( 'name' ),
		'og:locale'    => get_locale(),
		'og:title'     => is_front_page() ? get_bloginfo( 'name' ) : single_post_title( '', false ),
		'og:url'       => $url ? $url : home_url( add_query_arg( array() ) ),
		'og:image'     => $image,
	);
	if ( '' === $tags['og:title'] ) {
		$tags['og:title'] = $title;
	}

	if ( $description ) {
		printf( '<meta name="description" content="%s" />' . "\n", esc_attr( $description ) );
		$tags['og:description'] = $description;
	}
	foreach ( array_filter( $tags ) as $property => $content ) {
		printf( '<meta property="%s" content="%s" />' . "\n", esc_attr( $property ), 'og:url' === $property || 'og:image' === $property ? esc_url( $content ) : esc_attr( $content ) );
	}
	printf( '<meta name="twitter:card" content="%s" />' . "\n", $image ? 'summary_large_image' : 'summary' );

	if ( is_front_page() ) {
		wp_print_inline_script_tag(
			wp_json_encode( organization(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ),
			array( 'type' => 'application/ld+json' )
		);
	}
}

/**
 * Pages exclues des moteurs de recherche : recherche, confirmation d'envoi, archives sans contenu ou héritées du blog.
 *
 * @param array<string,bool|string> $robots Directives.
 * @return array<string,bool|string>
 */
function robots( array $robots ): array {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- simple lecture du paramètre d'affichage.
	$noindex = is_search() || is_attachment() || is_date() || is_author() || is_category() || is_tag() || isset( $_GET['devis'] );
	if ( ( is_post_type_archive( Realisations\POST_TYPE ) || is_tax( Realisations\TAXONOMY ) ) && ! have_posts() ) {
		$noindex = true;
	}
	if ( $noindex ) {
		unset( $robots['max-image-preview'] );
		$robots['noindex'] = true;
		$robots['follow']  = true;
	}
	return $robots;
}
