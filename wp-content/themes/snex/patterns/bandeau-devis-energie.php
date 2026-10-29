<?php
/**
 * Title: Bandeau de demande de devis – Énergie
 * Slug: snex/bandeau-devis-energie
 * Categories: snex
 * Viewport Width: 1280
 * Description: Bandeau orange avec les boutons Devis et Appeler (le bouton Appeler n'apparaît qu'une fois le numéro confirmé).
 *
 * @package Snex
 */

?>
<!-- wp:group {"metadata":{"name":"Bandeau devis"},"align":"full","className":"cta-band","backgroundColor":"orange","textColor":"ink","templateLock":"contentOnly","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull cta-band has-ink-color has-orange-background-color has-text-color has-background"><!-- wp:columns {"verticalAlignment":"center","align":"wide","className":"cta-band__inner"} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center cta-band__inner"><!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:heading -->
<h2 class="wp-block-heading">Un groupe à entretenir, un inverseur à installer ?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Décrivez votre installation et vos contraintes : nous préparons une proposition adaptée.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","className":"cta-band__actions"} -->
<div class="wp-block-column is-vertically-aligned-center cta-band__actions"><!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-nuit"} -->
<div class="wp-block-button is-style-nuit"><a class="wp-block-button__link wp-element-button" href="/demande-de-devis/?domaine=energie">Demander un devis énergie</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-contour","metadata":{"bindings":{"text":{"source":"snex/coordonnees","args":{"key":"tel_label"}},"url":{"source":"snex/coordonnees","args":{"key":"tel_url"}}}}} -->
<div class="wp-block-button is-style-contour"><a class="wp-block-button__link wp-element-button">Appeler</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
