<?php
/**
 * Plugin Name:       S-NEX Core
 * Description:       Fonctions métier du site S-NEX SARL : réalisations, domaines d'activité, coordonnées centralisées et formulaire de devis.
 * Version:           0.1.0
 * Requires at least: 6.7
 * Requires PHP:      8.1
 * Author:            S-NEX SARL
 * License:           GPL-2.0-or-later
 * Text Domain:       snex-core
 *
 * @package SnexCore
 */

namespace Snex\Core;

defined( 'ABSPATH' ) || exit;

const VERSION = '0.1.0';
const DIR     = __DIR__;

require_once DIR . '/includes/realisations.php';
require_once DIR . '/includes/coordonnees.php';
require_once DIR . '/includes/devis.php';
require_once DIR . '/includes/courriel.php';
require_once DIR . '/includes/seo.php';
require_once DIR . '/includes/securite.php';
require_once DIR . '/includes/performance.php';

Realisations\bootstrap();
Coordonnees\bootstrap();
Devis\bootstrap();
Seo\bootstrap();
Securite\bootstrap();
Performance\bootstrap();

register_activation_hook( __FILE__, __NAMESPACE__ . '\\activate' );

/**
 * Activation : enregistre les types de contenu, crée les domaines et les coordonnées par défaut.
 */
function activate(): void {
	Realisations\register_content_types();
	Realisations\seed_domains();
	Coordonnees\seed_defaults();
	flush_rewrite_rules();
}
