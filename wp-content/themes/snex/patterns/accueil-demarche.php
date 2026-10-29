<?php
/**
 * Title: Accueil – Démarche
 * Slug: snex/accueil-demarche
 * Categories: snex
 * Viewport Width: 1280
 * Description: Les cinq étapes de prise en charge d'un projet, tracées comme un schéma unifilaire.
 *
 * @package Snex
 */

$snex_steps = array(
	array( 'Échange et visite', 'Nous recueillons votre besoin et visitons le site si nécessaire.' ),
	array( 'Étude et proposition', 'Dimensionnement, choix du matériel et devis détaillé.' ),
	array( 'Réalisation', 'Installation par nos équipes, dans le respect des règles de sécurité.' ),
	array( 'Mise en service', "Essais, réglages et remise des informations d'exploitation." ),
	array( 'Suivi et maintenance', 'Visites préventives et interventions correctives.' ),
);
?>
<!-- wp:group {"metadata":{"name":"Démarche"},"align":"full","className":"section","templateLock":"contentOnly","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull section"><!-- wp:group {"align":"wide","className":"section-head","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide section-head"><!-- wp:paragraph {"className":"eyebrow"} -->
<p class="eyebrow">Notre démarche</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">De la première visite à la maintenance</h2>
<!-- /wp:heading --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"wide","className":"steps","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide steps">
<?php foreach ( $snex_steps as $snex_step ) : ?>
<!-- wp:group {"className":"step","layout":{"type":"default"}} -->
<div class="wp-block-group step"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php echo esc_html( $snex_step[0] ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php echo esc_html( $snex_step[1] ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
<?php endforeach; ?>
</div>
<!-- /wp:group --></div>
<!-- /wp:group -->
