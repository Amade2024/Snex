<?php
/**
 * Title: Accueil – Vitrine (façon flyer)
 * Slug: snex/accueil-vitrine
 * Categories: snex
 * Viewport Width: 1280
 * Description: Quatre services avec icônes, mosaïque de photos de chantier et phrase d'accroche.
 *
 * @package Snex
 */

use function Snex\Theme\image;
?>
<!-- wp:group {"metadata":{"name":"Vitrine"},"align":"full","className":"section vitrine","templateLock":"contentOnly","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull section vitrine"><!-- wp:group {"align":"wide","className":"vitrine__head","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide vitrine__head"><!-- wp:heading {"className":"vitrine__titre"} -->
<h2 class="wp-block-heading vitrine__titre">Nos services</h2>
<!-- /wp:heading --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"wide","className":"vitrine__services","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide vitrine__services"><!-- wp:group {"className":"vitrine__service ico-pylone","layout":{"type":"default"}} -->
<div class="wp-block-group vitrine__service ico-pylone"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><a href="/telecommunications/">Installation télécom</a></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Réseaux et infrastructures</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"vitrine__service ico-eclair","layout":{"type":"default"}} -->
<div class="wp-block-group vitrine__service ico-eclair"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><a href="/energie/">Électricité</a></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Installations électriques et groupes électrogènes</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"vitrine__service ico-panneau","layout":{"type":"default"}} -->
<div class="wp-block-group vitrine__service ico-panneau"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><a href="/photovoltaique/">Panneaux solaires</a></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Solutions photovoltaïques</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"vitrine__service ico-formation","layout":{"type":"default"}} -->
<div class="wp-block-group vitrine__service ico-formation"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><a href="/formation/">Formation</a></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Développement de compétences</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"wide","className":"vitrine__photos","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide vitrine__photos"><!-- wp:image {"sizeSlug":"large"} -->
<figure class="wp-block-image size-large"><img src="<?php echo image( 'pv-panneaux-ville.webp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" alt="Champ de panneaux solaires monocristallins posé en toiture, vue sur la ville"/></figure>
<!-- /wp:image -->

<!-- wp:image {"sizeSlug":"large"} -->
<figure class="wp-block-image size-large"><img src="<?php echo image( 'telecom-armoire-batteries-48v.webp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" alt="Armoire de batteries de secours : douze batteries 12 V 150 Ah au plomb étanche"/></figure>
<!-- /wp:image -->

<!-- wp:image {"sizeSlug":"large"} -->
<figure class="wp-block-image size-large"><img src="<?php echo image( 'telecom-armoire-smart-sys.webp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" alt="Armoire d'énergie de site télécom ouverte : redresseurs, entrées solaires et compteurs"/></figure>
<!-- /wp:image -->

<!-- wp:image {"sizeSlug":"large"} -->
<figure class="wp-block-image size-large"><img src="<?php echo image( 'telecom-coffret-distribution-48v.webp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" alt="Coffret de distribution 48 V ouvert, câbles repérés"/></figure>
<!-- /wp:image -->

<!-- wp:image {"sizeSlug":"large"} -->
<figure class="wp-block-image size-large"><img src="<?php echo image( 'ge-sdmo-capot.webp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" alt="Groupe électrogène capoté avec son tableau de commande"/></figure>
<!-- /wp:image -->

<!-- wp:image {"sizeSlug":"large"} -->
<figure class="wp-block-image size-large"><img src="<?php echo image( 'energie-transformateur-poteau.webp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" alt="Technicien fixant un transformateur en haut d'un poteau électrique"/></figure>
<!-- /wp:image -->

<!-- wp:image {"sizeSlug":"large"} -->
<figure class="wp-block-image size-large"><img src="<?php echo image( 'pv-auvent-toiture.webp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" alt="Auvent de panneaux solaires au-dessus des armoires d'un site télécom"/></figure>
<!-- /wp:image --></div>
<!-- /wp:group -->

<!-- wp:heading {"className":"vitrine__accroche"} -->
<h2 class="wp-block-heading vitrine__accroche">Votre partenaire de confiance pour <strong>l'énergie solaire</strong> et les solutions télécom</h2>
<!-- /wp:heading --></div>
<!-- /wp:group -->
