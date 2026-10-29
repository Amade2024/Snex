<?php
/**
 * Title: Accueil – Réalisations récentes
 * Slug: snex/accueil-realisations
 * Categories: snex
 * Viewport Width: 1280
 * Description: Les trois dernières réalisations publiées, avec un état vide tant qu'aucune n'est en ligne.
 *
 * @package Snex
 */

?>
<!-- wp:group {"metadata":{"name":"Réalisations récentes"},"align":"full","className":"section","backgroundColor":"paper","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull section has-paper-background-color has-background"><!-- wp:group {"align":"wide","className":"section-head","templateLock":"contentOnly","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide section-head"><!-- wp:paragraph {"className":"eyebrow"} -->
<p class="eyebrow">Réalisations</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Sur le terrain</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"lead"} -->
<p class="lead">Une sélection d'interventions récentes, détaillées dans la page Réalisations.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:query {"queryId":11,"query":{"perPage":3,"pages":0,"offset":0,"postType":"realisation","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false},"align":"wide","className":"cards-query"} -->
<div class="wp-block-query alignwide cards-query"><!-- wp:post-template {"layout":{"type":"grid","minimumColumnWidth":"18rem"}} -->
<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"4/3"} /-->

<!-- wp:group {"className":"card__body","layout":{"type":"default"}} -->
<div class="wp-block-group card__body"><!-- wp:post-terms {"term":"domaine","className":"domain"} /-->

<!-- wp:post-title {"level":3,"isLink":true} /-->

<!-- wp:paragraph {"className":"meta","metadata":{"bindings":{"content":{"source":"core/post-meta","args":{"key":"snex_lieu"}}}}} -->
<p class="meta"></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
<!-- /wp:post-template -->

<!-- wp:query-no-results -->
<!-- wp:group {"className":"empty-state","layout":{"type":"default"}} -->
<div class="wp-block-group empty-state"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Les premières réalisations arrivent bientôt</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Nous publions nos chantiers au fur et à mesure, avec l'accord des clients concernés.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
<!-- /wp:query-no-results --></div>
<!-- /wp:query -->

<!-- wp:buttons {"align":"wide","className":"section-foot"} -->
<div class="wp-block-buttons alignwide section-foot"><!-- wp:button {"className":"is-style-lien"} -->
<div class="wp-block-button is-style-lien"><a class="wp-block-button__link wp-element-button" href="/realisations/">Voir toutes les réalisations</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->
