<?php
/**
 * Title: Accueil – Bannière
 * Slug: snex/accueil-banniere
 * Categories: snex
 * Viewport Width: 1280
 * Description: Bannière d'accueil : slogan, titre, présentation, deux boutons et photo.
 *
 * @package Snex
 */

use function Snex\Theme\image;
?>
<!-- wp:group {"metadata":{"name":"Bannière"},"align":"full","className":"hero","backgroundColor":"ink","textColor":"surface","templateLock":"contentOnly","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull hero has-surface-color has-ink-background-color has-text-color has-background"><!-- wp:columns {"verticalAlignment":"center","align":"wide","className":"hero__grid"} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center hero__grid"><!-- wp:column {"verticalAlignment":"center","className":"hero__text"} -->
<div class="wp-block-column is-vertically-aligned-center hero__text"><!-- wp:heading {"level":1,"textColor":"surface"} -->
<h1 class="wp-block-heading has-surface-color has-text-color">Infrastructures télécoms, énergie et solaire en Côte d'Ivoire</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"lead"} -->
<p class="lead">S-NEX SARL installe, exploite et entretient les sites télécoms, les installations électriques et les systèmes photovoltaïques des entreprises, opérateurs et institutions.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"rccm","metadata":{"bindings":{"content":{"source":"snex/coordonnees","args":{"key":"rccm"}}}}} -->
<p class="rccm">S-NEX SARL – RCCM n° CI-ABJ-03-2026-B12-03677</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/demande-de-devis/">Demander un devis</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-clair"} -->
<div class="wp-block-button is-style-clair"><a class="wp-block-button__link wp-element-button" href="#metiers">Découvrir nos services</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

<!-- wp:list {"className":"sources"} -->
<ul class="wp-block-list sources"><!-- wp:list-item {"className":"source--telecom"} -->
<li class="source--telecom">Télécommunications</li>
<!-- /wp:list-item -->

<!-- wp:list-item {"className":"source--energie"} -->
<li class="source--energie">Énergie</li>
<!-- /wp:list-item -->

<!-- wp:list-item {"className":"source--pv"} -->
<li class="source--pv">Photovoltaïque</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","className":"hero__media"} -->
<div class="wp-block-column is-vertically-aligned-center hero__media"><!-- wp:columns {"isStackedOnMobile":false,"className":"hero__pair"} -->
<div class="wp-block-columns is-not-stacked-on-mobile hero__pair"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:image {"sizeSlug":"large"} -->
<figure class="wp-block-image size-large"><img src="<?php echo image( 'energie-transformateur-levage.webp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" alt="Technicien guidant un transformateur de distribution soulevé par le bras de grue d'un camion"/></figure>
<!-- /wp:image --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:image {"sizeSlug":"large"} -->
<figure class="wp-block-image size-large"><img src="<?php echo image( 'pv-mesure-tension-302v.webp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" alt="Mesure à la pince multimètre de la tension du champ solaire : 302,7 V"/></figure>
<!-- /wp:image --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:image {"sizeSlug":"large","className":"hero__image"} -->
<figure class="wp-block-image size-large hero__image"><img src="<?php echo image( 'pv-panneaux-ville.webp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- échappé par image(). ?>" alt="Champ de panneaux solaires monocristallins posé en toiture, vue sur la ville"/><figcaption class="wp-element-caption">Panneaux solaires installés en toiture à Abidjan</figcaption></figure>
<!-- /wp:image --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
