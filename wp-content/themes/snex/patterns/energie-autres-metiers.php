<?php
/**
 * Title: Énergie – Autres métiers
 * Slug: snex/energie-autres-metiers
 * Categories: snex
 * Viewport Width: 1280
 * Description: Renvoi vers les pages Télécommunications et Photovoltaïque.
 *
 * @package Snex
 */

use function Snex\Theme\image;
?>
<!-- wp:group {"metadata":{"name":"Autres métiers"},"align":"full","className":"section","backgroundColor":"paper","templateLock":"contentOnly","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull section has-paper-background-color has-background"><!-- wp:group {"align":"wide","className":"section-head","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide section-head"><!-- wp:paragraph {"className":"eyebrow"} -->
<p class="eyebrow">Nos autres métiers</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Télécoms et solaire, sur les mêmes sites</h2>
<!-- /wp:heading --></div>
<!-- /wp:group -->

<!-- wp:columns {"align":"wide","className":"cards cards--two"} -->
<div class="wp-block-columns alignwide cards cards--two"><!-- wp:column {"className":"card"} -->
<div class="wp-block-column card"><!-- wp:image {"sizeSlug":"large"} -->
<figure class="wp-block-image size-large"><img src="<?php echo image( 'telecom-batteries.webp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" alt="Modules de batteries lithium dans une armoire de site télécom"/></figure>
<!-- /wp:image -->

<!-- wp:group {"className":"card__body","layout":{"type":"default"}} -->
<div class="wp-block-group card__body"><!-- wp:paragraph {"className":"domain domain--telecom"} -->
<p class="domain domain--telecom">Télécommunications</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Sites et infrastructures télécoms</h3>
<!-- /wp:heading -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-lien"} -->
<div class="wp-block-button is-style-lien"><a class="wp-block-button__link wp-element-button" href="/telecommunications/">Voir les prestations</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column {"className":"card"} -->
<div class="wp-block-column card"><!-- wp:image {"sizeSlug":"large"} -->
<figure class="wp-block-image size-large"><img src="<?php echo image( 'pv-technicien-onduleur.webp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" alt="Technicien en casque et gilet haute visibilité intervenant sur un coffret à côté d'un onduleur solaire"/></figure>
<!-- /wp:image -->

<!-- wp:group {"className":"card__body","layout":{"type":"default"}} -->
<div class="wp-block-group card__body"><!-- wp:paragraph {"className":"domain domain--pv"} -->
<p class="domain domain--pv">Photovoltaïque</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Solutions solaires</h3>
<!-- /wp:heading -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-lien"} -->
<div class="wp-block-button is-style-lien"><a class="wp-block-button__link wp-element-button" href="/photovoltaique/">Voir les prestations</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
