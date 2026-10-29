<?php
/**
 * Plugin Name: S-NEX – e-mails en local
 * Description: En environnement local uniquement, écrit les e-mails dans wp-content/snex-mails au lieu de les envoyer.
 *
 * Copié dans wp-content/mu-plugins par bin/setup-local.sh. Jamais installé en production.
 *
 * @package Snex
 */

defined( 'ABSPATH' ) || exit;

if ( 'local' === wp_get_environment_type() ) {
	add_filter(
		'pre_wp_mail',
		static function ( $result, array $atts ) {
			$dir = WP_CONTENT_DIR . '/snex-mails';
			wp_mkdir_p( $dir );
			$files = array();
			foreach ( (array) $atts['attachments'] as $name => $path ) {
				$files[] = ( is_string( $name ) ? $name : basename( $path ) ) . ' (' . size_format( (int) filesize( $path ) ) . ')';
			}
			$text  = 'À : ' . implode( ', ', (array) $atts['to'] ) . "\n";
			$text .= 'Objet : ' . $atts['subject'] . "\n";
			$text .= 'En-têtes : ' . implode( ' | ', (array) $atts['headers'] ) . "\n";
			$text .= 'Pièces jointes : ' . ( $files ? implode( ', ', $files ) : 'aucune' ) . "\n\n";
			$text .= $atts['message'];
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- outil local.
			return false !== file_put_contents( $dir . '/' . gmdate( 'Ymd-His' ) . '-' . wp_generate_password( 4, false ) . '.txt', $text );
		},
		10,
		2
	);
}
