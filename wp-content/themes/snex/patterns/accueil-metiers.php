<?php
/**
 * Title: Accueil – Nos métiers
 * Slug: snex/accueil-metiers
 * Categories: snex
 * Viewport Width: 1280
 * Description: Les quatre domaines d'activité, une carte par métier.
 *
 * @package Snex
 */

use function Snex\Theme\image;
?>
<!-- wp:group {"metadata":{"name":"Nos métiers"},"anchor":"metiers","align":"full","className":"section","backgroundColor":"paper","templateLock":"contentOnly","layout":{"type":"constrained"}} -->
<div id="metiers" class="wp-block-group alignfull section has-paper-background-color has-background"><!-- wp:group {"align":"wide","className":"section-head","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide section-head"><!-- wp:paragraph {"className":"eyebrow"} -->
<p class="eyebrow">Nos métiers</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Quatre domaines complémentaires</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"lead"} -->
<p class="lead">Installation, exploitation et maintenance sur le terrain, et formation des équipes techniques.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:columns {"align":"wide","className":"cards"} -->
<div class="wp-block-columns alignwide cards"><!-- wp:column {"className":"card"} -->
<div class="wp-block-column card"><!-- wp:image {"sizeSlug":"large"} -->
<figure class="wp-block-image size-large"><img src="<?php echo image( 'telecom-batteries.webp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" alt="Modules de batteries lithium installés dans une armoire extérieure de site télécom"/></figure>
<!-- /wp:image -->

<!-- wp:group {"className":"card__body","layout":{"type":"default"}} -->
<div class="wp-block-group card__body"><!-- wp:paragraph {"className":"domain domain--telecom"} -->
<p class="domain domain--telecom">Télécommunications</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Sites et infrastructures télécoms</h3>
<!-- /wp:heading -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li>Installation de sites et d'équipements</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Exploitation des installations</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Maintenance préventive et corrective</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-lien"} -->
<div class="wp-block-button is-style-lien"><a class="wp-block-button__link wp-element-button" href="/telecommunications/">Voir les prestations</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column {"className":"card"} -->
<div class="wp-block-column card"><!-- wp:image {"sizeSlug":"large"} -->
<figure class="wp-block-image size-large"><img src="<?php echo image( 'energie-inverseur-auto.webp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" alt="Coffret d'inverseur automatique réseau et groupe électrogène, avec disjoncteurs et parafoudre"/></figure>
<!-- /wp:image -->

<!-- wp:group {"className":"card__body","layout":{"type":"default"}} -->
<div class="wp-block-group card__body"><!-- wp:paragraph {"className":"domain domain--energie"} -->
<p class="domain domain--energie">Énergie</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Installations électriques et groupes électrogènes</h3>
<!-- /wp:heading -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li>Installations électriques</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Groupes électrogènes</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Alimentation sécurisée et maintenance</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-lien"} -->
<div class="wp-block-button is-style-lien"><a class="wp-block-button__link wp-element-button" href="/energie/">Voir les prestations</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column {"className":"card"} -->
<div class="wp-block-column card"><!-- wp:image {"sizeSlug":"large"} -->
<figure class="wp-block-image size-large"><img src="<?php echo image( 'pv-onduleurs.webp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" alt="Deux onduleurs solaires hybrides numérotés 01 et 02, fixés au mur d'un local technique"/></figure>
<!-- /wp:image -->

<!-- wp:group {"className":"card__body","layout":{"type":"default"}} -->
<div class="wp-block-group card__body"><!-- wp:paragraph {"className":"domain domain--pv"} -->
<p class="domain domain--pv">Photovoltaïque</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Solutions solaires</h3>
<!-- /wp:heading -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li>Études de projets solaires</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Installation et maintenance</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Fourniture de matériel solaire</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-lien"} -->
<div class="wp-block-button is-style-lien"><a class="wp-block-button__link wp-element-button" href="/photovoltaique/">Voir les prestations</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column {"className":"card"} -->
<div class="wp-block-column card"><!-- wp:image {"sizeSlug":"large"} -->
<figure class="wp-block-image size-large"><img src="<?php echo image( 'formation-harnais.webp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" alt="Technicien en tenue haute visibilité portant un harnais antichute et sa longe, prêt pour un travail en hauteur"/></figure>
<!-- /wp:image -->

<!-- wp:group {"className":"card__body","layout":{"type":"default"}} -->
<div class="wp-block-group card__body"><!-- wp:paragraph {"className":"domain domain--formation"} -->
<p class="domain domain--formation">Formation</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Formations techniques</h3>
<!-- /wp:heading -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li>Sécurité et travail en hauteur</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Électricité et solaire</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Exploitation de sites télécoms</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-lien"} -->
<div class="wp-block-button is-style-lien"><a class="wp-block-button__link wp-element-button" href="/formation/">Voir les formations</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
