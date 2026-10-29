<?php
/**
 * Title: Énergie – En-tête
 * Slug: snex/energie-entete
 * Categories: snex
 * Viewport Width: 1280
 * Description: En-tête de la page Énergie : titre, présentation, boutons et photo.
 *
 * @package Snex
 */

use function Snex\Theme\image;
?>
<!-- wp:group {"metadata":{"name":"En-tête Énergie"},"align":"full","className":"page-hero","backgroundColor":"ink","textColor":"surface","templateLock":"contentOnly","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull page-hero has-surface-color has-ink-background-color has-text-color has-background"><!-- wp:columns {"verticalAlignment":"bottom","align":"wide","className":"page-hero__grid"} -->
<div class="wp-block-columns alignwide are-vertically-aligned-bottom page-hero__grid"><!-- wp:column {"verticalAlignment":"bottom"} -->
<div class="wp-block-column is-vertically-aligned-bottom"><!-- wp:paragraph {"className":"crumbs"} -->
<p class="crumbs"><a href="/">Accueil</a> / Énergie</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"eyebrow"} -->
<p class="eyebrow">Énergie</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1,"textColor":"surface"} -->
<h1 class="wp-block-heading has-surface-color has-text-color">Installations électriques, groupes électrogènes et alimentation sécurisée</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"lead"} -->
<p class="lead">Nous installons et entretenons les équipements qui alimentent vos bâtiments et vos sites techniques, y compris quand le réseau coupe.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/demande-de-devis/?domaine=energie">Demander un devis énergie</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-clair","metadata":{"bindings":{"url":{"source":"snex/coordonnees","args":{"key":"tel_url"}}}}} -->
<div class="wp-block-button is-style-clair"><a class="wp-block-button__link wp-element-button">Appeler</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"bottom"} -->
<div class="wp-block-column is-vertically-aligned-bottom"><!-- wp:image {"sizeSlug":"large"} -->
<figure class="wp-block-image size-large"><img src="<?php echo image( 'energie-inverseur-auto.webp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" alt="Coffret d'inverseur automatique réseau et groupe électrogène en cours de câblage"/><figcaption class="wp-element-caption">Coffret inverseur automatique réseau / groupe, 63 A</figcaption></figure>
<!-- /wp:image --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
