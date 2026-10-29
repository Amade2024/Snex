<?php
/**
 * Envoi des e-mails du site par la messagerie de l'entreprise (SMTP), pour qu'ils arrivent sans passer en spam.
 *
 * Actif seulement si le mot de passe est déclaré dans wp-config.php (jamais dans le dépôt) :
 *
 *   define( 'SNEX_SMTP_PASSWORD', '…' );
 *   // Facultatif, valeurs par défaut :
 *   define( 'SNEX_SMTP_HOST', 'mail.snexsarl.com' );
 *   define( 'SNEX_SMTP_PORT', 465 );            // 587 avec 'tls'.
 *   define( 'SNEX_SMTP_SECURE', 'ssl' );        // 'ssl' ou 'tls'.
 *   define( 'SNEX_SMTP_USER', 'direction@snexsarl.com' );
 *
 * L'expéditeur est l'adresse de connexion ; l'adresse du demandeur est placée en « Répondre à » par le formulaire.
 *
 * @package SnexCore
 */

namespace Snex\Core\Courriel;

use Snex\Core\Coordonnees;

defined( 'ABSPATH' ) || exit;

/**
 * Branche les crochets WordPress.
 */
function bootstrap(): void {
	if ( ! is_configured() ) {
		return;
	}
	add_action( 'phpmailer_init', __NAMESPACE__ . '\configure' );
	add_filter( 'wp_mail_from', __NAMESPACE__ . '\from' );
	add_filter( 'wp_mail_from_name', __NAMESPACE__ . '\from_name' );
}

/**
 * La messagerie SMTP est-elle configurée ?
 */
function is_configured(): bool {
	return defined( 'SNEX_SMTP_PASSWORD' ) && '' !== (string) constant( 'SNEX_SMTP_PASSWORD' );
}

/**
 * Réglage d'une constante, ou sa valeur par défaut.
 *
 * @param string     $name    Nom de la constante.
 * @param int|string $fallback Valeur par défaut.
 * @return int|string
 */
function setting( string $name, $fallback ) {
	return defined( $name ) ? constant( $name ) : $fallback;
}

/**
 * Adresse de connexion, utilisée aussi comme expéditeur.
 */
function user(): string {
	return (string) setting( 'SNEX_SMTP_USER', 'direction@snexsarl.com' );
}

/**
 * Configure PHPMailer pour la messagerie de l'entreprise.
 *
 * @param \PHPMailer\PHPMailer\PHPMailer $mailer Instance utilisée par wp_mail().
 */
function configure( $mailer ): void {
	$mailer->isSMTP();
	$mailer->Host       = (string) setting( 'SNEX_SMTP_HOST', 'mail.snexsarl.com' ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	$mailer->Port       = (int) setting( 'SNEX_SMTP_PORT', 465 ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	$mailer->SMTPSecure = (string) setting( 'SNEX_SMTP_SECURE', 'ssl' ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	$mailer->SMTPAuth   = true; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	$mailer->Username   = user(); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	$mailer->Password   = (string) constant( 'SNEX_SMTP_PASSWORD' ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	$mailer->Timeout    = 15; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
}

/**
 * Expéditeur : l'adresse de connexion (un autre expéditeur serait refusé ou classé en spam).
 */
function from(): string {
	return user();
}

/**
 * Nom de l'expéditeur : la raison sociale.
 */
function from_name(): string {
	$name = Coordonnees\value( 'raison_sociale' );
	return '' !== $name ? $name : 'Site S-NEX';
}
