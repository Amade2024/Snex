<?php
/**
 * Title: Énergie – Prestations
 * Slug: snex/energie-prestations
 * Categories: snex
 * Viewport Width: 1280
 * Description: Les quatre prestations du pôle Énergie, avec photos et schéma.
 *
 * @package Snex
 */

use function Snex\Theme\image;
?>
<!-- wp:group {"metadata":{"name":"Prestations Énergie"},"align":"full","className":"services","templateLock":"contentOnly","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull services"><!-- wp:group {"align":"wide","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide"><!-- wp:group {"anchor":"installations","className":"service","layout":{"type":"default"}} -->
<div id="installations" class="wp-block-group service"><!-- wp:columns {"className":"service__grid"} -->
<div class="wp-block-columns service__grid"><!-- wp:column {"className":"service__text"} -->
<div class="wp-block-column service__text"><!-- wp:paragraph {"className":"eyebrow"} -->
<p class="eyebrow">Installations électriques</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Du tableau jusqu'aux équipements</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Nous réalisons les installations électriques de bâtiments professionnels et de sites techniques : distribution, protections, câblage et raccordements.</p>
<!-- /wp:paragraph -->

<!-- wp:list {"className":"checks"} -->
<ul class="wp-block-list checks"><!-- wp:list-item -->
<li>Tableaux et coffrets électriques</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Protections : disjoncteurs, différentiels, parafoudres</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Câblage, repérage et raccordements</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Automatismes à contacteurs et temporisations</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:column -->

<!-- wp:column {"className":"service__media"} -->
<div class="wp-block-column service__media"><!-- wp:image {"sizeSlug":"large"} -->
<figure class="wp-block-image size-large"><img src="<?php echo image( 'energie-temporisation.webp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" alt="Contacteur avec bloc de temporisation et disjoncteurs de commande"/><figcaption class="wp-element-caption">Contacteur avec bloc de temporisation 0,1 à 30 s</figcaption></figure>
<!-- /wp:image --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"anchor":"groupes","className":"service service--inverse","layout":{"type":"default"}} -->
<div id="groupes" class="wp-block-group service service--inverse"><!-- wp:columns {"className":"service__grid"} -->
<div class="wp-block-columns service__grid"><!-- wp:column {"className":"service__text"} -->
<div class="wp-block-column service__text"><!-- wp:paragraph {"className":"eyebrow"} -->
<p class="eyebrow">Groupes électrogènes</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Un groupe prêt à prendre le relais</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Nous installons, raccordons et entretenons les groupes électrogènes qui secourent vos installations pendant les coupures du réseau.</p>
<!-- /wp:paragraph -->

<!-- wp:list {"className":"checks"} -->
<ul class="wp-block-list checks"><!-- wp:list-item -->
<li>Installation et raccordement au tableau</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Entretien périodique : vidange, filtres, courroies</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Diagnostic de pannes</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Remplacement de pièces, dont les régulateurs de tension (AVR)</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:column -->

<!-- wp:column {"className":"service__media"} -->
<div class="wp-block-column service__media"><!-- wp:image {"sizeSlug":"large"} -->
<figure class="wp-block-image size-large"><img src="<?php echo image( 'ge-regulateur-sx460.webp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" alt="Régulateur de tension d'alternateur SX460 posé dans son emballage"/><figcaption class="wp-element-caption">Régulateur de tension d'alternateur SX460</figcaption></figure>
<!-- /wp:image -->

<!-- wp:group {"className":"thumbs","layout":{"type":"grid","columnCount":3}} -->
<div class="wp-block-group thumbs"><!-- wp:image {"sizeSlug":"large"} -->
<figure class="wp-block-image size-large"><img src="<?php echo image( 'ge-filtre-huile.webp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" alt="Filtre à huile Fleetguard LF3828 monté sur un moteur"/><figcaption class="wp-element-caption">Filtre à huile</figcaption></figure>
<!-- /wp:image -->

<!-- wp:image {"sizeSlug":"large"} -->
<figure class="wp-block-image size-large"><img src="<?php echo image( 'ge-filtre-air.webp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" alt="Boîtier de filtre à air d'un groupe électrogène"/><figcaption class="wp-element-caption">Filtre à air</figcaption></figure>
<!-- /wp:image -->

<!-- wp:image {"sizeSlug":"large"} -->
<figure class="wp-block-image size-large"><img src="<?php echo image( 'ge-courroie.webp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" alt="Courroie trapézoïdale usée tenue en main"/><figcaption class="wp-element-caption">Courroie</figcaption></figure>
<!-- /wp:image --></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"anchor":"alimentation","className":"service","layout":{"type":"default"}} -->
<div id="alimentation" class="wp-block-group service"><!-- wp:columns {"className":"service__grid"} -->
<div class="wp-block-columns service__grid"><!-- wp:column {"className":"service__text"} -->
<div class="wp-block-column service__text"><!-- wp:paragraph {"className":"eyebrow"} -->
<p class="eyebrow">Alimentation sécurisée</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Le réseau coupe, vos équipements restent alimentés</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Un inverseur de source bascule automatiquement vos équipements du réseau CIE vers le groupe électrogène en cas de coupure, puis les ramène sur le réseau à son retour.</p>
<!-- /wp:paragraph -->

<!-- wp:list {"className":"checks"} -->
<ul class="wp-block-list checks"><!-- wp:list-item -->
<li>Coffrets inverseurs automatiques réseau / groupe</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Inverseurs à contacteurs avec temporisation</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Couplage avec onduleurs et batteries, en lien avec nos solutions photovoltaïques</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:column -->

<!-- wp:column {"className":"service__media"} -->
<div class="wp-block-column service__media"><!-- wp:image {"sizeSlug":"large"} -->
<figure class="wp-block-image size-large"><img src="<?php echo image( 'energie-contacteurs-cie-ge.webp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" alt="Deux contacteurs étiquetés CIE et GE, câblés dans un coffret d'inverseur"/><figcaption class="wp-element-caption">Contacteurs repérés CIE (réseau) et GE (groupe)</figcaption></figure>
<!-- /wp:image --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:image {"sizeSlug":"full","className":"diagram"} -->
<figure class="wp-block-image size-full diagram"><img src="<?php echo image( 'schema-inverseur.svg' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" alt="Schéma : le réseau CIE et le groupe électrogène alimentent un inverseur automatique, qui alimente vos équipements ; un onduleur avec batteries peut s'ajouter en option."/><figcaption class="wp-element-caption">Principe de l'alimentation sécurisée. L'onduleur et les batteries, en option, couvrent le délai de démarrage du groupe.</figcaption></figure>
<!-- /wp:image --></div>
<!-- /wp:group -->

<!-- wp:group {"anchor":"maintenance","className":"service service--inverse","layout":{"type":"default"}} -->
<div id="maintenance" class="wp-block-group service service--inverse"><!-- wp:columns {"className":"service__grid"} -->
<div class="wp-block-columns service__grid"><!-- wp:column {"className":"service__text"} -->
<div class="wp-block-column service__text"><!-- wp:paragraph {"className":"eyebrow"} -->
<p class="eyebrow">Maintenance</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Préventive et corrective</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Nous suivons vos installations dans la durée, avec des visites planifiées et des interventions en cas de panne.</p>
<!-- /wp:paragraph -->

<!-- wp:group {"className":"maint","layout":{"type":"grid","minimumColumnWidth":"14rem"}} -->
<div class="wp-block-group maint"><!-- wp:group {"className":"maint-col","layout":{"type":"default"}} -->
<div class="wp-block-group maint-col"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Préventive</h3>
<!-- /wp:heading -->

<!-- wp:list {"className":"checks"} -->
<ul class="wp-block-list checks"><!-- wp:list-item -->
<li>Visites planifiées</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Contrôles et mesures</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Remplacement des consommables</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"maint-col","layout":{"type":"default"}} -->
<div class="wp-block-group maint-col"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Corrective</h3>
<!-- /wp:heading -->

<!-- wp:list {"className":"checks"} -->
<ul class="wp-block-list checks"><!-- wp:list-item -->
<li>Diagnostic sur site</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Remplacement des pièces défectueuses</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Remise en service et essais</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column {"className":"service__media"} -->
<div class="wp-block-column service__media"><!-- wp:image {"sizeSlug":"large"} -->
<figure class="wp-block-image size-large"><img src="<?php echo image( 'logistique-levage.webp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" alt="Caisse d'équipement hissée par une grue sur une toiture-terrasse à Abidjan"/><figcaption class="wp-element-caption">Levage d'un équipement en toiture</figcaption></figure>
<!-- /wp:image --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
