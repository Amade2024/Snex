<?php
/**
 * Formulaire de demande de devis en trois étapes (bloc « snex/devis »).
 *
 * La demande est envoyée par e-mail à l'adresse saisie dans Coordonnées : rien n'est enregistré en base
 * et les pièces jointes sont supprimées du serveur dès la fin de l'envoi. Si le demandeur choisit WhatsApp,
 * il est ensuite conduit vers la conversation WhatsApp de l'entreprise, message de demande déjà rédigé. Sans JavaScript, les trois
 * étapes s'affichent l'une sous l'autre et le formulaire reste utilisable.
 *
 * @package SnexCore
 */

namespace Snex\Core\Devis;

use Snex\Core\Coordonnees;
use Snex\Core\Realisations;

defined( 'ABSPATH' ) || exit;

const BLOCK      = 'snex/devis';
const ACTION     = 'snex_devis';
const MAX_FILES  = 3;
const MAX_SIZE   = 5242880;  // 5 Mo par fichier (abaissé si l'hébergeur autorise moins).
const MAX_TOTAL  = 10485760; // 10 Mo pour l'ensemble des fichiers.
const MIN_DELAY  = 3;        // Secondes minimum entre l'affichage et l'envoi (en dessous : robot).
const RATE_LIMIT = 5;        // Demandes envoyées par heure depuis une même adresse IP.

/**
 * Villes suggérées pendant la saisie. Le champ reste libre : toute autre ville ou tout autre pays peut être écrit.
 */
const VILLES = array( 'Abidjan', 'Abengourou', 'Aboisso', 'Agboville', 'Bondoukou', 'Bouaflé', 'Bouaké', 'Dabou', 'Daloa', 'Divo', 'Ferkessédougou', 'Gagnoa', 'Grand-Bassam', 'Korhogo', 'Man', 'Odienné', 'San-Pédro', 'Séguéla', 'Soubré', 'Yamoussoukro' );

/**
 * Communes du district d'Abidjan, suggérées quand la ville choisie est Abidjan.
 */
const COMMUNES_ABIDJAN = array( 'Abobo', 'Adjamé', 'Anyama', 'Attécoubé', 'Bingerville', 'Cocody', 'Koumassi', 'Marcory', 'Plateau', 'Port-Bouët', 'Songon', 'Treichville', 'Yopougon' );

const CONTACTS = array(
	'telephone' => 'Appel',
	'whatsapp'  => 'WhatsApp',
	'email'     => 'E-mail',
);

/**
 * Étape de chaque champ, pour rouvrir la bonne étape quand le serveur signale une erreur.
 */
const STEPS = array(
	'nom'           => 1,
	'entreprise'    => 1,
	'telephone'     => 1,
	'email'         => 1,
	'contact'       => 1,
	'ville'         => 2,
	'commune'       => 2,
	'service'       => 2,
	'service_autre' => 2,
	'budget'        => 2,
	'description'   => 2,
	'pieces'        => 3,
	'consentement'  => 3,
);

/**
 * Branche les crochets WordPress.
 */
function bootstrap(): void {
	add_action( 'init', __NAMESPACE__ . '\\register' );
	add_action( 'template_redirect', __NAMESPACE__ . '\\handle' );
}

/**
 * Services proposés : les domaines d'activité, plus un besoin libre.
 *
 * @return array<string,string>
 */
function services(): array {
	return Realisations\DOMAINES + array( 'autre' => 'Autre besoin' );
}

/**
 * Taille maximale d'un fichier, dans la limite fixée par l'hébergeur.
 */
function max_size(): int {
	return (int) min( MAX_SIZE, wp_max_upload_size() );
}

/**
 * Taille maximale de l'ensemble des fichiers, en laissant de la marge pour les champs texte.
 */
function max_total(): int {
	$post_max = wp_convert_hr_to_bytes( (string) ini_get( 'post_max_size' ) );
	return (int) ( $post_max > 0 ? min( MAX_TOTAL, $post_max - 262144 ) : MAX_TOTAL );
}

/**
 * Formats acceptés pour les pièces jointes (extension => type MIME).
 *
 * @return array<string,string>
 */
function allowed_mimes(): array {
	return array(
		'pdf'      => 'application/pdf',
		'jpg|jpeg' => 'image/jpeg',
		'png'      => 'image/png',
		'webp'     => 'image/webp',
		'doc'      => 'application/msword',
		'docx'     => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		'xls'      => 'application/vnd.ms-excel',
		'xlsx'     => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
	);
}

/**
 * Déclare le bloc et ses scripts.
 */
function register(): void {
	$url = static fn( string $file ) => plugins_url( 'assets/' . $file, \Snex\Core\DIR . '/snex-core.php' );
	wp_register_script(
		'snex-devis',
		$url( 'devis.js' ),
		array(),
		\Snex\Core\VERSION,
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
	wp_register_script( 'snex-devis-editor', $url( 'devis-editor.js' ), array( 'wp-blocks', 'wp-element', 'wp-block-editor' ), \Snex\Core\VERSION, true );
	register_block_type(
		BLOCK,
		array(
			'api_version'           => 3,
			'title'                 => 'Formulaire de devis',
			'description'           => 'Demande de devis en trois étapes, envoyée par e-mail à l\'adresse des Coordonnées.',
			'category'              => 'widgets',
			'icon'                  => 'feedback',
			'supports'              => array(
				'html'     => false,
				'multiple' => false,
				'reusable' => false,
			),
			'editor_script_handles' => array( 'snex-devis-editor' ),
			'view_script_handles'   => array( 'snex-devis' ),
			'render_callback'       => __NAMESPACE__ . '\\render',
		)
	);
}

/**
 * État de la requête en cours : saisie, erreurs par champ, erreur générale.
 *
 * @param array<string,mixed>|null $set Nouvel état (facultatif).
 * @return array{data:array<string,mixed>,errors:array<string,string>,general:string}
 */
function state( ?array $set = null ): array {
	static $state = null;
	if ( null !== $set ) {
		$state = $set + array(
			'data'    => array(),
			'errors'  => array(),
			'general' => '',
		);
	}
	return $state ?? array(
		'data'    => array(),
		'errors'  => array(),
		'general' => '',
	);
}

/**
 * Jeton horodaté et signé : permet d'écarter les robots qui envoient le formulaire en moins de MIN_DELAY secondes.
 */
function token(): string {
	$time = (string) time();
	return $time . '.' . wp_hash( $time . '|' . ACTION, 'nonce' );
}

/**
 * Âge d'un jeton en secondes, ou null s'il a été falsifié.
 *
 * @param string $token Jeton reçu.
 */
function token_age( string $token ): ?int {
	[ $time, $hash ] = array_pad( explode( '.', $token, 2 ), 2, '' );
	if ( '' === $time || ! hash_equals( wp_hash( $time . '|' . ACTION, 'nonce' ), $hash ) ) {
		return null;
	}
	return time() - (int) $time;
}

/**
 * Vérifie un numéro de téléphone : 10 chiffres pour un numéro ivoirien, ou format international (+ ou 00).
 *
 * @param string $raw Saisie de l'internaute.
 */
function is_valid_phone( string $raw ): bool {
	$raw    = trim( $raw );
	$digits = preg_replace( '/\D/', '', $raw );
	if ( preg_match( '/^(\+|00)/', $raw ) ) {
		return Coordonnees\is_valid_international( str_starts_with( $raw, '00' ) ? substr( $digits, 2 ) : $digits );
	}
	return 10 === strlen( $digits );
}

/**
 * Champ texte envoyé, nettoyé et tronqué.
 *
 * @param string $key       Nom du champ.
 * @param int    $max       Longueur maximale.
 * @param bool   $multiline Conserver les retours à la ligne.
 */
function input( string $key, int $max = 120, bool $multiline = false ): string {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- nonce vérifié dans handle() avant toute lecture.
	if ( ! isset( $_POST[ $key ] ) || ! is_string( $_POST[ $key ] ) ) {
		return '';
	}
	$value = $multiline ? sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) : sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
	// phpcs:enable
	return mb_substr( $value, 0, $max );
}

/**
 * Montant saisi par l'internaute, réduit aux chiffres et présenté par milliers (« 5 000 000 »).
 *
 * @param string $raw Saisie.
 */
function format_amount( string $raw ): string {
	$digits = ltrim( substr( preg_replace( '/\D/', '', $raw ), 0, 13 ), '0' );
	return '' === $digits ? '' : number_format( (float) $digits, 0, ',', ' ' );
}

/**
 * Saisie envoyée, nettoyée. Les choix hors liste sont ramenés à une valeur vide.
 *
 * @return array<string,mixed>
 */
function read_input(): array {
	$pick = static fn( string $value, array $allowed ) => in_array( $value, $allowed, true ) ? $value : '';
	return array(
		'nom'           => input( 'nom', 100 ),
		'entreprise'    => input( 'entreprise', 120 ),
		'telephone'     => input( 'telephone', 30 ),
		'email'         => input( 'email', 120 ),
		'contact'       => $pick( input( 'contact' ), array_keys( CONTACTS ) ),
		'ville'         => input( 'ville', 80 ),
		'commune'       => input( 'commune', 80 ),
		'service'       => $pick( input( 'service' ), array_keys( services() ) ),
		'service_autre' => input( 'service_autre', 120 ),
		'budget'        => format_amount( input( 'budget', 40 ) ),
		'description'   => input( 'description', 3000, true ),
		'consentement'  => '1' === input( 'consentement' ),
	);
}

/**
 * Vérifie la saisie. Les messages reprennent ceux affichés par le navigateur (assets/devis.js).
 *
 * @param array<string,mixed> $d Saisie nettoyée.
 * @return array<string,string> Erreurs par champ.
 */
function validate( array $d ): array {
	$e = array();
	if ( mb_strlen( trim( (string) $d['nom'] ) ) < 2 ) {
		$e['nom'] = 'Indiquez votre nom et votre prénom.';
	}
	if ( ! is_valid_phone( (string) $d['telephone'] ) ) {
		$e['telephone'] = 'Indiquez un numéro joignable : 10 chiffres (07 00 00 00 00) ou format international (+225…).';
	}
	if ( ! is_email( (string) $d['email'] ) ) {
		$e['email'] = 'Indiquez une adresse e-mail valide, par exemple nom@entreprise.ci.';
	}
	if ( '' === $d['ville'] ) {
		$e['ville'] = 'Indiquez la ville du projet.';
	}
	if ( '' === $d['commune'] ) {
		$e['commune'] = 'Indiquez la commune ou le quartier.';
	}
	if ( '' === $d['service'] ) {
		$e['service'] = 'Choisissez le service concerné.';
	} elseif ( 'autre' === $d['service'] && '' === $d['service_autre'] ) {
		$e['service_autre'] = 'Précisez votre besoin.';
	}
	if ( mb_strlen( trim( (string) $d['description'] ) ) < 20 ) {
		$e['description'] = 'Décrivez votre projet en quelques phrases (20 caractères minimum).';
	}
	if ( empty( $d['consentement'] ) ) {
		$e['consentement'] = 'Cochez cette case pour pouvoir envoyer votre demande.';
	}
	return $e;
}

/**
 * Pièces jointes envoyées, vérifiées (nombre, taille, format réel du fichier).
 *
 * @param array<string,string> $errors Erreurs, complétées en cas de problème.
 * @return array<int,array{tmp:string,name:string,size:int}>
 */
function collect_files( array &$errors ): array {
	// phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput -- nonce vérifié dans handle() ; chemins temporaires contrôlés par is_uploaded_file(), noms nettoyés ci-dessous.
	$f = $_FILES['pieces'] ?? null;
	// phpcs:enable
	if ( ! is_array( $f ) || ! is_array( $f['name'] ?? null ) ) {
		return array();
	}
	$files = array();
	$total = 0;
	foreach ( $f['name'] as $i => $name ) {
		$code = (int) ( $f['error'][ $i ] ?? UPLOAD_ERR_NO_FILE );
		if ( UPLOAD_ERR_NO_FILE === $code ) {
			continue;
		}
		$size = (int) ( $f['size'][ $i ] ?? 0 );
		$tmp  = (string) ( $f['tmp_name'][ $i ] ?? '' );
		$name = sanitize_file_name( (string) $name );
		if ( in_array( $code, array( UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE ), true ) || $size > max_size() ) {
			$errors['pieces'] = sprintf( '« %s » dépasse %s.', $name, size_format( max_size() ) );
		} elseif ( UPLOAD_ERR_OK !== $code || ! is_uploaded_file( $tmp ) ) {
			$errors['pieces'] = sprintf( 'L\'envoi de « %s » a été interrompu. Joignez-le à nouveau.', $name );
		} else {
			$check = wp_check_filetype_and_ext( $tmp, $name, allowed_mimes() );
			if ( ! $check['ext'] || ! $check['type'] ) {
				$errors['pieces'] = sprintf( 'Format non accepté pour « %s ». Formats acceptés : PDF, JPG, PNG, WebP, Word, Excel.', $name );
			}
		}
		if ( isset( $errors['pieces'] ) ) {
			return array();
		}
		$total  += $size;
		$files[] = array(
			'tmp'  => $tmp,
			'name' => $name,
			'size' => $size,
		);
	}
	if ( count( $files ) > MAX_FILES ) {
		$errors['pieces'] = sprintf( 'Joignez %d fichiers au maximum.', MAX_FILES );
	} elseif ( $total > max_total() ) {
		$errors['pieces'] = sprintf( 'L\'ensemble des fichiers dépasse %s. Retirez-en un ou réduisez leur taille.', size_format( max_total() ) );
	}
	return isset( $errors['pieces'] ) ? array() : $files;
}

/**
 * Compte les demandes envoyées depuis la même adresse IP (adresse hachée, conservée une heure).
 */
function rate_limited(): bool {
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key = 'snex_devis_' . md5( wp_hash( $ip ) );
	$n   = (int) get_transient( $key );
	if ( $n >= RATE_LIMIT ) {
		return true;
	}
	set_transient( $key, $n + 1, HOUR_IN_SECONDS );
	return false;
}

/**
 * Libellés lisibles de la demande, dans l'ordre du formulaire.
 *
 * @param array<string,mixed> $d Saisie validée.
 * @return array<string,string>
 */
function summary( array $d ): array {
	return array(
		'Nom et prénom'      => $d['nom'],
		'Entreprise'         => '' !== $d['entreprise'] ? $d['entreprise'] : '—',
		'Téléphone'          => $d['telephone'],
		'E-mail'             => $d['email'],
		'Contact préféré'    => CONTACTS[ $d['contact'] ] ?? '—',
		'Service'            => 'autre' === $d['service'] ? 'Autre : ' . $d['service_autre'] : services()[ $d['service'] ],
		'Ville'              => $d['ville'],
		'Commune / quartier' => $d['commune'],
		'Budget estimé'      => '' !== $d['budget'] ? $d['budget'] . ' FCFA' : 'Non précisé',
	);
}

/**
 * Envoie la demande par e-mail. Les fichiers temporaires sont supprimés quoi qu'il arrive.
 *
 * @param array<string,mixed>                               $d     Saisie validée.
 * @param array<int,array{tmp:string,name:string,size:int}> $files Pièces jointes.
 */
function send( array $d, array $files ): bool {
	$attachments = array();
	foreach ( $files as $i => $file ) {
		$attachments[ isset( $attachments[ $file['name'] ] ) ? ( $i + 1 ) . '-' . $file['name'] : $file['name'] ] = $file['tmp'];
	}
	try {
		$to = Coordonnees\value( 'email' );
		if ( ! is_email( $to ) ) {
			return false;
		}
		$rows = summary( $d );
		$body = "Nouvelle demande de devis envoyée depuis le site.\n\n";
		foreach ( $rows as $label => $value ) {
			$body .= $label . ' : ' . $value . "\n";
		}
		$body .= "\nDescription du projet :\n" . $d['description'] . "\n\n";
		$body .= 'Pièces jointes : ' . ( $attachments ? implode( ', ', array_keys( $attachments ) ) : 'aucune' ) . "\n\n";
		$body .= '— Envoyé le ' . wp_date( 'j F Y à H:i' ) . ". Répondez à cet e-mail pour écrire directement au demandeur.\n";

		$name    = trim( str_replace( array( '"', ',', '<', '>' ), '', (string) $d['nom'] ) );
		$subject = sprintf( 'Demande de devis – %s – %s', $rows['Service'], $d['nom'] . ( $d['entreprise'] ? ' (' . $d['entreprise'] . ')' : '' ) );
		$headers = array(
			'Content-Type: text/plain; charset=UTF-8',
			sprintf( 'Reply-To: %s <%s>', $name, sanitize_email( (string) $d['email'] ) ),
		);
		return wp_mail( $to, $subject, $body, $headers, $attachments );
	} finally {
		foreach ( $attachments as $tmp ) {
			if ( file_exists( $tmp ) ) {
				wp_delete_file( $tmp );
			}
		}
	}
}

/**
 * Lien WhatsApp vers l'entreprise, avec la demande déjà rédigée. Vide si le numéro WhatsApp n'est pas confirmé.
 *
 * @param array<string,mixed> $d       Saisie validée.
 * @param int                 $files   Nombre de pièces jointes.
 * @param bool                $by_mail La demande et ses pièces jointes sont aussi parties par e-mail.
 */
function whatsapp_url( array $d, int $files, bool $by_mail ): string {
	$base = Coordonnees\value( 'whatsapp_url' );
	if ( '' === $base ) {
		return '';
	}
	$text = "Bonjour, voici ma demande de devis envoyée depuis le site.\n\n";
	foreach ( summary( $d ) as $label => $value ) {
		if ( 'Contact préféré' !== $label ) {
			$text .= '*' . $label . '* : ' . $value . "\n";
		}
	}
	$text .= "\n*Description du projet* :\n" . $d['description'] . "\n";
	if ( $files ) {
		$text .= "\n" . ( $by_mail ? sprintf( 'Mes documents (%d) vous ont été envoyés par e-mail.', $files ) : sprintf( 'Je vous envoie mes documents (%d) dans cette conversation.', $files ) ) . "\n";
	}
	return $base . '?text=' . rawurlencode( $text );
}

/**
 * Message d'erreur général, avec les autres moyens de contact.
 *
 * @param string $reason Début du message.
 */
function fallback_message( string $reason ): string {
	$tel   = Coordonnees\value( 'telephone' );
	$email = Coordonnees\value( 'email' );
	return trim( $reason . ' ' . sprintf( 'Vous pouvez aussi nous joindre%s%s.', $tel ? ' au ' . $tel : '', $email ? ( $tel ? ' ou' : '' ) . ' par e-mail à ' . $email : '' ) );
}

/**
 * Traite l'envoi du formulaire, avant l'affichage de la page.
 */
function handle(): void {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- simple repère d'envoi, le nonce est vérifié ci-dessous.
	if ( ! isset( $_GET['devis'] ) || 'envoi' !== $_GET['devis'] || ! isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
		return;
	}
	$back = add_query_arg( 'devis', 'envoye', get_permalink( get_queried_object_id() ) ) . '#devis';

	// Envoi plus lourd que ce qu'accepte le serveur : PHP vide alors $_POST et $_FILES.
	if ( empty( $_POST ) ) {
		state( array( 'general' => sprintf( 'Les fichiers joints sont trop volumineux (%s au total au maximum). Réduisez-les et remplissez à nouveau le formulaire.', size_format( max_total() ) ) ) );
		return;
	}

	$nonce = isset( $_POST['_devis_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_devis_nonce'] ) ) : '';
	$data  = read_input();
	if ( ! wp_verify_nonce( $nonce, ACTION ) ) {
		state(
			array(
				'data'    => $data,
				'general' => 'La page est restée ouverte trop longtemps. Vérifiez vos informations et envoyez à nouveau la demande.',
			)
		);
		return;
	}

	// Robots : champ piège rempli ou envoi instantané. On affiche le message de succès sans rien envoyer.
	$age = token_age( isset( $_POST['_devis_t'] ) ? sanitize_text_field( wp_unslash( $_POST['_devis_t'] ) ) : '' );
	if ( '' !== input( 'site_web' ) || null === $age || $age < MIN_DELAY ) {
		wp_safe_redirect( $back, 303 );
		exit;
	}

	$errors = validate( $data );
	$files  = collect_files( $errors );
	if ( $errors ) {
		$general = isset( $errors['pieces'] ) && 1 === count( $errors ) ? '' : 'Certaines informations sont à compléter.';
		if ( $files || isset( $errors['pieces'] ) ) {
			$general = trim( $general . ' Joignez à nouveau vos fichiers avant l\'envoi.' );
		}
		state( compact( 'data', 'errors', 'general' ) );
		return;
	}
	if ( rate_limited() ) {
		state(
			array(
				'data'    => $data,
				'general' => fallback_message( 'Plusieurs demandes ont déjà été envoyées depuis votre connexion. Réessayez dans une heure.' ),
			)
		);
		return;
	}
	$count = count( $files );
	$sent  = send( $data, $files );
	$wa    = 'whatsapp' === $data['contact'] ? whatsapp_url( $data, $count, $sent ) : '';
	if ( $wa ) {
		// Demande choisie par WhatsApp : la conversation s'ouvre avec le message prêt à envoyer.
		wp_redirect( $wa, 303 ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- adresse wa.me construite ici.
		exit;
	}
	if ( ! $sent ) {
		state(
			array(
				'data'    => $data,
				'general' => fallback_message( 'Votre demande n\'a pas pu être envoyée, à cause d\'un problème technique de notre côté. Réessayez dans quelques minutes.' ),
			)
		);
		return;
	}
	wp_safe_redirect( $back, 303 );
	exit;
}

/**
 * Attributs communs d'un champ : identifiant, nom, description et état d'erreur.
 *
 * @param string               $name   Nom du champ.
 * @param array<string,string> $errors Erreurs.
 * @param bool                 $hint   Le champ a une aide.
 */
function attrs( string $name, array $errors, bool $hint = false ): string {
	$described = trim( ( $hint ? 'devis-' . $name . '-aide ' : '' ) . 'devis-err-' . $name );
	return sprintf(
		'id="devis-%1$s" name="%1$s" aria-describedby="%2$s"%3$s',
		esc_attr( $name ),
		esc_attr( $described ),
		isset( $errors[ $name ] ) ? ' aria-invalid="true"' : ''
	);
}

/**
 * Zone d'erreur d'un champ (vide tant qu'il n'y a pas d'erreur).
 *
 * @param string               $name   Nom du champ.
 * @param array<string,string> $errors Erreurs.
 */
function error( string $name, array $errors ): string {
	return sprintf( '<p class="devis__error" id="devis-err-%s">%s</p>', esc_attr( $name ), esc_html( $errors[ $name ] ?? '' ) );
}

/**
 * Liste de boutons radio présentés en cartes.
 *
 * @param string               $name    Nom du champ.
 * @param array<string,string> $choices Valeur => libellé.
 * @param string               $current Valeur cochée.
 * @param array<string,string> $errors  Erreurs.
 * @param string               $recap   Libellé dans le récapitulatif.
 */
function radios( string $name, array $choices, string $current, array $errors, string $recap ): string {
	$html = '';
	foreach ( $choices as $value => $label ) {
		$html .= sprintf(
			'<label class="devis__choice devis__choice--%1$s"><input type="radio" id="devis-%2$s-%1$s" name="%2$s" value="%1$s" required data-recap="%3$s" aria-describedby="devis-err-%2$s"%4$s%5$s> <span>%6$s</span></label>',
			esc_attr( $value ),
			esc_attr( $name ),
			esc_attr( $recap ),
			checked( $current, $value, false ),
			isset( $errors[ $name ] ) ? ' aria-invalid="true"' : '',
			esc_html( $label )
		);
	}
	return $html;
}

/**
 * Message affiché après l'envoi.
 */
function render_done(): string {
	$tel = Coordonnees\value( 'telephone' );
	return sprintf(
		'<div %s><div class="devis-done" role="status" tabindex="-1"><h2>Demande envoyée</h2><p>Merci, votre demande de devis a bien été transmise à %s. Nous vous recontactons dès que possible, selon le moyen que vous avez indiqué.</p>%s<p><a href="%s">Envoyer une autre demande</a></p></div></div>',
		get_block_wrapper_attributes(
			array(
				'id'    => 'devis',
				'class' => 'devis-wrap',
			)
		),
		esc_html( Coordonnees\value( 'raison_sociale' ) ),
		$tel ? '<p>Pour une urgence, appelez le ' . esc_html( $tel ) . '.</p>' : '',
		esc_url( remove_query_arg( 'devis', get_permalink() ) . '#devis' )
	);
}

/**
 * Affiche le formulaire (ou le message de confirmation).
 */
function render(): string {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- paramètres d'affichage uniquement.
	if ( isset( $_GET['devis'] ) && 'envoye' === $_GET['devis'] ) {
		return render_done();
	}
	$preset = isset( $_GET['service'] ) ? sanitize_key( wp_unslash( $_GET['service'] ) ) : '';
	// phpcs:enable

	$s      = state();
	$errors = $s['errors'];
	$d      = $s['data'] + array(
		'nom'           => '',
		'entreprise'    => '',
		'telephone'     => '',
		'email'         => '',
		'contact'       => 'telephone',
		'ville'         => '',
		'commune'       => '',
		'service'       => isset( services()[ $preset ] ) ? $preset : '',
		'service_autre' => '',
		'budget'        => '',
		'description'   => '',
		'consentement'  => false,
	);
	$step   = $errors ? min( array_map( static fn( $k ) => STEPS[ $k ] ?? 1, array_keys( $errors ) ) ) : 1;
	$a      = static fn( string $name, bool $hint = false ) => attrs( $name, $errors, $hint );
	$err    = static fn( string $name ) => error( $name, $errors );
	$val    = static fn( string $name ) => esc_attr( (string) $d[ $name ] );
	$action = add_query_arg( 'devis', 'envoi', get_permalink() ) . '#devis';
	$legal  = get_page_by_path( 'mentions-legales' );
	$more   = $legal ? ' <a href="' . esc_url( get_permalink( $legal ) ) . '">En savoir plus</a>' : '';
	$wrap   = get_block_wrapper_attributes(
		array(
			'id'    => 'devis',
			'class' => 'devis-wrap',
		)
	);

	ob_start();
	// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- $a, $err et $val renvoient du contenu échappé.
	?>
	<div <?php echo $wrap; ?>>
	<form class="devis" method="post" enctype="multipart/form-data" action="<?php echo esc_url( $action ); ?>"
		data-step="<?php echo (int) $step; ?>" data-max-files="<?php echo (int) MAX_FILES; ?>"
		data-max-size="<?php echo (int) max_size(); ?>" data-max-total="<?php echo (int) max_total(); ?>">

		<ol class="devis__progress">
			<li>Vos coordonnées</li>
			<li>Votre projet</li>
			<li>Documents et envoi</li>
		</ol>

		<?php if ( $s['general'] || $errors ) : ?>
		<div class="devis__summary" role="alert" tabindex="-1">
			<p><strong><?php echo esc_html( $s['general'] ? $s['general'] : 'Certaines informations sont à compléter.' ); ?></strong></p>
			<?php if ( $errors ) : ?>
			<ul>
				<?php foreach ( $errors as $field => $message ) : ?>
					<?php $target = in_array( $field, array( 'service', 'contact' ), true ) ? $field . '-' . array_key_first( 'service' === $field ? services() : CONTACTS ) : $field; ?>
				<li><a href="#devis-<?php echo esc_attr( $target ); ?>"><?php echo esc_html( $message ); ?></a></li>
				<?php endforeach; ?>
			</ul>
			<?php endif; ?>
		</div>
		<?php endif; ?>

		<p class="devis__note">Les champs marqués d'un astérisque (<span class="devis__req">*</span>) sont obligatoires.</p>

		<fieldset class="devis__step" data-step="1" tabindex="-1">
			<legend><span class="devis__count">Étape 1 sur 3</span> Vos coordonnées</legend>
			<div class="devis__grid">
				<div class="devis__field">
					<label for="devis-nom">Nom et prénom <span class="devis__req">*</span></label>
					<input type="text" <?php echo $a( 'nom' ); ?> value="<?php echo $val( 'nom' ); ?>" autocomplete="name" required minlength="2" maxlength="100" data-recap="Nom et prénom" data-error="Indiquez votre nom et votre prénom.">
					<?php echo $err( 'nom' ); ?>
				</div>
				<div class="devis__field">
					<label for="devis-entreprise">Entreprise <span class="devis__opt">(facultatif)</span></label>
					<input type="text" <?php echo $a( 'entreprise' ); ?> value="<?php echo $val( 'entreprise' ); ?>" autocomplete="organization" maxlength="120" data-recap="Entreprise">
					<?php echo $err( 'entreprise' ); ?>
				</div>
				<div class="devis__field">
					<label for="devis-telephone">Numéro de téléphone <span class="devis__req">*</span></label>
					<input type="tel" <?php echo $a( 'telephone', true ); ?> value="<?php echo $val( 'telephone' ); ?>" autocomplete="tel" inputmode="tel" required maxlength="30" data-recap="Téléphone" data-error="Indiquez un numéro joignable : 10 chiffres (07 00 00 00 00) ou format international (+225…).">
					<p class="devis__hint" id="devis-telephone-aide">Par exemple 07 00 00 00 00 ou +225 07 00 00 00 00.</p>
					<?php echo $err( 'telephone' ); ?>
				</div>
				<div class="devis__field">
					<label for="devis-email">E-mail <span class="devis__req">*</span></label>
					<input type="email" <?php echo $a( 'email' ); ?> value="<?php echo $val( 'email' ); ?>" autocomplete="email" required maxlength="120" data-recap="E-mail" data-error="Indiquez une adresse e-mail valide, par exemple nom@entreprise.ci.">
					<?php echo $err( 'email' ); ?>
				</div>
				<fieldset class="devis__field devis__field--full">
					<legend class="devis__label">Comment préférez-vous échanger avec nous ?</legend>
					<div class="devis__choices devis__choices--small"><?php echo radios( 'contact', CONTACTS, (string) $d['contact'], $errors, 'Contact préféré' ); ?></div>
					<?php if ( Coordonnees\value( 'whatsapp_url' ) ) : ?>
						<p class="devis__hint" id="devis-contact-aide">Avec WhatsApp, votre demande s'ouvre dans WhatsApp, déjà rédigée : il ne reste qu'à l'envoyer. Dans tous les cas, nous la recevons aussi par e-mail.</p>
					<?php endif; ?>
					<?php echo $err( 'contact' ); ?>
				</fieldset>
			</div>
			<div class="devis__nav">
				<button type="button" class="wp-element-button devis__next" data-nav="next" hidden>Continuer</button>
			</div>
		</fieldset>

		<fieldset class="devis__step" data-step="2" tabindex="-1">
			<legend><span class="devis__count">Étape 2 sur 3</span> Votre projet</legend>
			<div class="devis__grid">
				<div class="devis__field">
					<label for="devis-ville">Ville <span class="devis__req">*</span></label>
					<input type="text" <?php echo $a( 'ville' ); ?> value="<?php echo $val( 'ville' ); ?>" list="devis-villes" autocomplete="address-level2" required maxlength="80" placeholder="Par exemple Abidjan" data-recap="Ville" data-error="Indiquez la ville du projet.">
					<datalist id="devis-villes">
						<?php foreach ( VILLES as $ville ) : ?>
						<option value="<?php echo esc_attr( $ville ); ?>"></option>
						<?php endforeach; ?>
					</datalist>
					<?php echo $err( 'ville' ); ?>
				</div>
				<div class="devis__field">
					<label for="devis-commune">Commune ou quartier <span class="devis__req">*</span></label>
					<input type="text" <?php echo $a( 'commune' ); ?> value="<?php echo $val( 'commune' ); ?>" list="devis-communes" autocomplete="address-level3" required maxlength="80" data-recap="Commune / quartier" data-error="Indiquez la commune ou le quartier.">
					<datalist id="devis-communes">
						<?php foreach ( COMMUNES_ABIDJAN as $commune ) : ?>
						<option value="<?php echo esc_attr( $commune ); ?>"></option>
						<?php endforeach; ?>
					</datalist>
					<?php echo $err( 'commune' ); ?>
				</div>
				<fieldset class="devis__field devis__field--full">
					<legend class="devis__label">Service concerné <span class="devis__req">*</span></legend>
					<div class="devis__choices"><?php echo radios( 'service', services(), (string) $d['service'], $errors, 'Service' ); ?></div>
					<?php echo $err( 'service' ); ?>
				</fieldset>
				<div class="devis__field devis__field--full" data-show-if="service=autre">
					<label for="devis-service_autre">Précisez votre besoin <span class="devis__req">*</span></label>
					<input type="text" <?php echo $a( 'service_autre' ); ?> value="<?php echo $val( 'service_autre' ); ?>" maxlength="120" data-recap="Besoin" data-error="Précisez votre besoin.">
					<?php echo $err( 'service_autre' ); ?>
				</div>
				<div class="devis__field devis__field--full">
					<label for="devis-budget">Budget estimé <span class="devis__opt">(facultatif)</span></label>
					<div class="devis__money">
						<input type="text" <?php echo $a( 'budget', true ); ?> value="<?php echo $val( 'budget' ); ?>" inputmode="numeric" maxlength="20" placeholder="Par exemple 5 000 000" data-recap="Budget estimé">
						<span aria-hidden="true">FCFA</span>
					</div>
					<p class="devis__hint" id="devis-budget-aide">Montant en francs CFA. Un ordre de grandeur nous aide à proposer une solution adaptée.</p>
					<?php echo $err( 'budget' ); ?>
				</div>
				<div class="devis__field devis__field--full">
					<label for="devis-description">Description du projet <span class="devis__req">*</span></label>
					<textarea <?php echo $a( 'description', true ); ?> rows="7" required minlength="20" maxlength="3000" data-recap="Description" data-error="Décrivez votre projet en quelques phrases (20 caractères minimum)."><?php echo esc_textarea( (string) $d['description'] ); ?></textarea>
					<p class="devis__hint" id="devis-description-aide">Nature des travaux, type de site ou de bâtiment, puissance ou équipements concernés, délai souhaité… <span class="devis__counter" data-counter="description"></span></p>
					<?php echo $err( 'description' ); ?>
				</div>
			</div>
			<div class="devis__nav">
				<button type="button" class="wp-element-button devis__back" data-nav="back" hidden>Retour</button>
				<button type="button" class="wp-element-button devis__next" data-nav="next" hidden>Continuer</button>
			</div>
		</fieldset>

		<fieldset class="devis__step" data-step="3" tabindex="-1">
			<legend><span class="devis__count">Étape 3 sur 3</span> Documents et envoi</legend>
			<div class="devis__grid">
				<div class="devis__field devis__field--full">
					<label for="devis-pieces">Photos, plans ou documents <span class="devis__opt">(facultatif)</span></label>
					<input type="file" id="devis-pieces" name="pieces[]" multiple accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx" aria-describedby="devis-pieces-aide devis-err-pieces"<?php echo isset( $errors['pieces'] ) ? ' aria-invalid="true"' : ''; ?>>
					<p class="devis__hint" id="devis-pieces-aide">
						<?php echo esc_html( sprintf( '%d fichiers maximum, %s chacun : PDF, JPG, PNG, WebP, Word ou Excel. Ils sont transmis avec votre demande puis supprimés du serveur.', MAX_FILES, size_format( max_size() ) ) ); ?>
					</p>
					<ul class="devis__files" aria-live="polite"></ul>
					<?php echo $err( 'pieces' ); ?>
				</div>
				<div class="devis__recap devis__field--full" hidden>
					<h3>Récapitulatif de votre demande</h3>
					<dl></dl>
					<p class="devis__recap-edit">
						<button type="button" class="devis__link" data-goto="1">Modifier vos coordonnées</button>
						<button type="button" class="devis__link" data-goto="2">Modifier le projet</button>
					</p>
				</div>
				<div class="devis__field devis__field--full">
					<label class="devis__consent">
						<input type="checkbox" <?php echo $a( 'consentement' ); ?> value="1" required<?php checked( $d['consentement'] ); ?> data-error="Cochez cette case pour pouvoir envoyer votre demande.">
						<span>J'accepte que ces informations soient transmises par e-mail à <?php echo esc_html( Coordonnees\value( 'raison_sociale' ) ); ?> pour répondre à ma demande. Elles ne sont pas enregistrées sur le site.<?php echo $more; ?> <span class="devis__req">*</span></span>
					</label>
					<?php echo $err( 'consentement' ); ?>
				</div>
			</div>
			<div class="devis__hp" aria-hidden="true">
				<label for="devis-site_web">Ne pas remplir ce champ</label>
				<input type="text" id="devis-site_web" name="site_web" value="" tabindex="-1" autocomplete="off">
			</div>
			<?php wp_nonce_field( ACTION, '_devis_nonce', false ); ?>
			<input type="hidden" name="_devis_t" value="<?php echo esc_attr( token() ); ?>">
			<div class="devis__nav">
				<button type="button" class="wp-element-button devis__back" data-nav="back" hidden>Retour</button>
				<button type="submit" class="wp-element-button devis__submit">Envoyer la demande</button>
			</div>
		</fieldset>
	</form>
	</div>
	<?php
	// phpcs:enable
	return (string) ob_get_clean();
}
