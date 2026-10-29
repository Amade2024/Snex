<?php
/**
 * Contenus de départ du site (pages, menu, logo, médias), à partir des motifs du thème.
 * Exécuté par bin/setup-local.sh avec « wp eval-file ». Réexécutable : une page déjà présente
 * n'est pas modifiée, sauf avec SNEX_SEED_FORCE=1. Aucune réalisation n'est créée : seules
 * les réalisations réelles, fournies par S-NEX, seront publiées.
 *
 * @package Snex
 */

namespace Snex\Seed;

defined( 'ABSPATH' ) || exit;

wp_set_current_user( 1 );
// WordPress garde la liste des motifs du thème en cache : un motif ajouté serait sinon introuvable.
wp_get_theme()->delete_pattern_cache();
// SNEX_SEED_FORCE=1 régénère toutes les pages ; SNEX_SEED_FORCE=energie,contact seulement celles-ci.
$force = array_filter( explode( ',', (string) getenv( 'SNEX_SEED_FORCE' ) ) );

/* ---------- 1. Médias : photos du thème copiées dans la médiathèque ---------- */
require_once ABSPATH . 'wp-admin/includes/image.php';
$media     = get_option( 'snex_seed_media', array() );
$theme_url = get_stylesheet_directory_uri() . '/assets/images/';
foreach ( glob( get_stylesheet_directory() . '/assets/images/*.{webp,png}', GLOB_BRACE ) as $file ) {
	$name = basename( $file );
	if ( isset( $media[ $name ] ) && get_post( $media[ $name ] ) ) {
		continue;
	}
	$upload = wp_upload_bits( $name, null, file_get_contents( $file ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( $upload['error'] ) {
		\WP_CLI::warning( "$name : {$upload['error']}" );
		continue;
	}
	$id = wp_insert_attachment(
		array(
			'post_mime_type' => wp_check_filetype( $upload['file'] )['type'],
			'post_title'     => ucfirst( str_replace( '-', ' ', pathinfo( $name, PATHINFO_FILENAME ) ) ),
			'post_status'    => 'inherit',
		),
		$upload['file']
	);
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );
	$media[ $name ] = $id;
}
update_option( 'snex_seed_media', $media, false );
$GLOBALS['snex_seed_media']     = $media; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
$GLOBALS['snex_seed_theme_url'] = $theme_url; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

/**
 * Remplace les images du thème par leur copie dans la médiathèque (l'administrateur peut ensuite les changer).
 *
 * @param string $content Contenu de blocs.
 */
function use_media( string $content ): string {
	$media     = $GLOBALS['snex_seed_media'];
	$theme_url = $GLOBALS['snex_seed_theme_url'];
	foreach ( $media as $name => $id ) {
		$content = str_replace( $theme_url . $name, wp_get_attachment_url( $id ), $content );
	}
	return $content;
}

/**
 * Contenu d'un motif enregistré par le thème.
 *
 * @param string $slug Identifiant du motif.
 */
function pattern( string $slug ): string {
	$p = \WP_Block_Patterns_Registry::get_instance()->get_registered( $slug );
	if ( ! $p ) {
		\WP_CLI::error( "Motif introuvable : $slug" );
	}
	return $p['content'];
}

/**
 * En-tête de page intérieure (même structure que le motif de la page Énergie).
 *
 * @param string $eyebrow Surtitre.
 * @param string $title   Titre H1.
 * @param string $lead    Phrase d'introduction.
 * @param string $img     Image du thème (facultative).
 * @param string $alt     Texte alternatif.
 */
function hero( string $eyebrow, string $title, string $lead, string $img = '', string $alt = '' ): string {
	$theme_url = $GLOBALS['snex_seed_theme_url'];
	$text      = '<!-- wp:paragraph {"className":"crumbs"} --><p class="crumbs"><a href="/">Accueil</a> / ' . esc_html( $eyebrow ) . '</p><!-- /wp:paragraph -->';
	$text     .= '<!-- wp:paragraph {"className":"eyebrow"} --><p class="eyebrow">' . esc_html( $eyebrow ) . '</p><!-- /wp:paragraph -->';
	$text     .= '<!-- wp:heading {"level":1,"textColor":"surface"} --><h1 class="wp-block-heading has-surface-color has-text-color">' . esc_html( $title ) . '</h1><!-- /wp:heading -->';
	$text     .= '<!-- wp:paragraph {"className":"lead"} --><p class="lead">' . esc_html( $lead ) . '</p><!-- /wp:paragraph -->';
	$media_col = '';
	if ( $img ) {
		$media_col = '<!-- wp:column {"verticalAlignment":"bottom"} --><div class="wp-block-column is-vertically-aligned-bottom"><!-- wp:image {"sizeSlug":"large"} --><figure class="wp-block-image size-large"><img src="' . esc_url( $theme_url . $img ) . '" alt="' . esc_attr( $alt ) . '"/></figure><!-- /wp:image --></div><!-- /wp:column -->';
	}
	return '<!-- wp:group {"align":"full","className":"page-hero","backgroundColor":"ink","textColor":"surface","templateLock":"contentOnly","layout":{"type":"constrained"}} --><div class="wp-block-group alignfull page-hero has-surface-color has-ink-background-color has-text-color has-background">'
		. '<!-- wp:columns {"verticalAlignment":"bottom","align":"wide","className":"page-hero__grid"} --><div class="wp-block-columns alignwide are-vertically-aligned-bottom page-hero__grid">'
		. '<!-- wp:column {"verticalAlignment":"bottom"} --><div class="wp-block-column is-vertically-aligned-bottom">' . $text . '</div><!-- /wp:column -->'
		. $media_col
		. '</div><!-- /wp:columns --></div><!-- /wp:group -->';
}

/**
 * Section de texte courant : liste de [titre, paragraphe].
 *
 * @param array<int,array{0:string,1:string}> $items Blocs titre + texte.
 * @param string                              $bg    Couleur de fond (facultative).
 */
function prose( array $items, string $bg = '' ): string {
	$inner = '';
	foreach ( $items as $item ) {
		if ( '' !== $item[0] ) {
			$inner .= '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html( $item[0] ) . '</h2><!-- /wp:heading -->';
		}
		foreach ( (array) $item[1] as $p ) {
			$inner .= '<!-- wp:paragraph --><p>' . wp_kses_post( $p ) . '</p><!-- /wp:paragraph -->';
		}
	}
	$attrs = $bg ? ',"backgroundColor":"' . $bg . '"' : '';
	$class = $bg ? ' has-' . $bg . '-background-color has-background' : '';
	return '<!-- wp:group {"align":"full","className":"section prose"' . $attrs . ',"layout":{"type":"constrained"}} --><div class="wp-block-group alignfull section prose' . $class . '">' . $inner . '</div><!-- /wp:group -->';
}

/**
 * Prestations présentées en rangées texte + photo, comme sur la page Énergie (photo à gauche une fois sur deux).
 * Une prestation sans photo s'affiche en texte seul ; une image peut lui être ajoutée ensuite dans l'éditeur.
 *
 * @param array<int,array{0:string,1:string,2?:string,3?:string}> $items Titre, texte, image du thème, texte alternatif.
 */
function services( array $items ): string {
	$theme_url = $GLOBALS['snex_seed_theme_url'];
	$rows      = '';
	foreach ( $items as $i => $item ) {
		[ $title, $text, $img, $alt ] = $item + array( '', '', '', '' );
		$class                        = 'service' . ( $i % 2 ? ' service--inverse' : '' );
		$cols                         = '<!-- wp:column {"className":"service__text"} --><div class="wp-block-column service__text">'
			. '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html( $title ) . '</h2><!-- /wp:heading -->'
			. '<!-- wp:paragraph --><p>' . esc_html( $text ) . '</p><!-- /wp:paragraph -->'
			. '</div><!-- /wp:column -->';
		if ( $img ) {
			$cols .= '<!-- wp:column {"className":"service__media"} --><div class="wp-block-column service__media"><!-- wp:image {"sizeSlug":"large"} --><figure class="wp-block-image size-large"><img src="' . esc_url( $theme_url . $img ) . '" alt="' . esc_attr( $alt ) . '"/></figure><!-- /wp:image --></div><!-- /wp:column -->';
		}
		$rows .= '<!-- wp:group {"className":"' . $class . '","layout":{"type":"default"}} --><div class="wp-block-group ' . $class . '">'
			. '<!-- wp:columns {"className":"service__grid"} --><div class="wp-block-columns service__grid">' . $cols . '</div><!-- /wp:columns -->'
			. '</div><!-- /wp:group -->';
	}
	return '<!-- wp:group {"align":"full","className":"services","layout":{"type":"constrained"}} --><div class="wp-block-group alignfull services">'
		. '<!-- wp:group {"align":"wide","layout":{"type":"default"}} --><div class="wp-block-group alignwide">' . $rows . '</div><!-- /wp:group -->'
		. '</div><!-- /wp:group -->';
}

/**
 * Galerie de photos de chantier (images du thème), précédée d'un titre de section.
 *
 * @param string                              $title  Titre de la section.
 * @param array<int,array{0:string,1:string}> $photos Image du thème, texte alternatif.
 */
function gallery( string $title, array $photos ): string {
	$theme_url = $GLOBALS['snex_seed_theme_url'];
	$images    = '';
	foreach ( $photos as [ $img, $alt ] ) {
		$images .= '<!-- wp:image {"sizeSlug":"large"} --><figure class="wp-block-image size-large"><img src="' . esc_url( $theme_url . $img ) . '" alt="' . esc_attr( $alt ) . '"/></figure><!-- /wp:image -->';
	}
	return '<!-- wp:group {"align":"full","className":"section photos","backgroundColor":"paper","layout":{"type":"constrained"}} --><div class="wp-block-group alignfull section photos has-paper-background-color has-background">'
		. '<!-- wp:group {"align":"wide","className":"section-head","layout":{"type":"default"}} --><div class="wp-block-group alignwide section-head">'
		. '<!-- wp:paragraph {"className":"eyebrow"} --><p class="eyebrow">En images</p><!-- /wp:paragraph -->'
		. '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html( $title ) . '</h2><!-- /wp:heading -->'
		. '</div><!-- /wp:group -->'
		. '<!-- wp:gallery {"columns":3,"linkTo":"none","sizeSlug":"large","align":"wide"} --><figure class="wp-block-gallery alignwide has-nested-images columns-3 is-cropped">' . $images . '</figure><!-- /wp:gallery -->'
		. '</div><!-- /wp:group -->';
}

/**
 * Paragraphe lié à une coordonnée centralisée.
 *
 * @param string $key     Clé de coordonnée.
 * @param string $fallback Texte affiché dans l'éditeur.
 */
function bound( string $key, string $fallback ): string {
	return '<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"snex/coordonnees","args":{"key":"' . $key . '"}}}}} --><p>' . esc_html( $fallback ) . '</p><!-- /wp:paragraph -->';
}

/* ---------- 2. Pages ---------- */
$pages = array(
	'accueil'            => array(
		'Accueil',
		implode(
			"\n\n",
			array_map(
				__NAMESPACE__ . '\\pattern',
				array( 'snex/accueil-banniere', 'snex/accueil-presentation', 'snex/accueil-metiers', 'snex/accueil-atouts', 'snex/accueil-demarche', 'snex/accueil-vitrine' )
			)
		),
	),
	'energie'            => array(
		'Énergie',
		implode( "\n\n", array_map( __NAMESPACE__ . '\\pattern', array( 'snex/energie-entete', 'snex/energie-sommaire', 'snex/energie-prestations' ) ) )
		. gallery(
			'Entretien des groupes électrogènes',
			array(
				array( 'ge-fg-wilson-capot.webp', 'Groupe électrogène FG Wilson capoté, avec son tableau de commande et son arrêt d\'urgence' ),
				array( 'ge-sdmo-capot.webp', "Groupe électrogène SDMO capoté, tableau de commande et bouton d'arrêt d'urgence" ),
				array( 'ge-filtre-gasoil.webp', 'Filtre à gasoil remplacé, annoté de la date et du compteur horaire de l\'intervention' ),
				array( 'ge-appoint-huile.webp', "Appoint d'huile moteur neuve versée dans un groupe électrogène à l'aide d'un entonnoir" ),
				array( 'ge-vidange-huile.webp', 'Vidange : récupération de l\'huile usagée dans un bidon' ),
				array( 'ge-courroie-alternateur.webp', "Courroie d'alternateur contrôlée sur le moteur d'un groupe électrogène" ),
				array( 'ge-capteur-bloc-moteur.webp', 'Raccordement électrique vérifié sur le bloc moteur d\'un groupe électrogène' ),
				array( 'ge-fg-wilson-logo.webp', 'Plaque de marque FG Wilson sur le capot d\'un groupe électrogène' ),
			)
		)
		. gallery(
			'Pose de transformateurs',
			array(
				array( 'energie-transformateur-levage.webp', "Technicien guidant un transformateur de distribution soulevé par le bras de grue d'un camion" ),
				array( 'energie-transformateur-poteau.webp', "Technicien fixant un transformateur en haut d'un poteau électrique en béton, camion-grue au pied" ),
			)
		)
		. implode( "\n\n", array_map( __NAMESPACE__ . '\\pattern', array( 'snex/bandeau-devis-energie', 'snex/energie-autres-metiers' ) ) ),
	),
	'telecommunications' => array(
		'Télécommunications',
		hero( 'Télécommunications', 'Installation, exploitation et maintenance de sites télécoms', "Nous intervenons sur les sites et infrastructures télécoms, de l'installation des équipements à leur maintenance." )
		. services(
			array(
				array( "Installation d'infrastructures et de sites", 'Mise en place et raccordement des équipements de vos sites télécoms.', 'telecom-batteries.webp', "Modules de batteries lithium installés et raccordés dans l'armoire extérieure d'un site télécom" ),
				array( 'Exploitation des installations', 'Suivi du fonctionnement des installations et interventions courantes sur site.', 'telecom-armoire.webp', "Armoire d'énergie d'un site télécom ouverte : disjoncteurs, redresseurs et câblage" ),
				array( 'Maintenance préventive et corrective', 'Visites planifiées, contrôles, dépannages et remise en service.' ),
			)
		)
		. gallery(
			'Sites télécoms sur le terrain',
			array(
				array( 'telecom-armoire-smart-sys.webp', "Armoire d'énergie de site télécom ouverte : système Smart SYS 48 V, redresseurs, entrées solaires et compteurs" ),
				array( 'telecom-armoire-compteurs.webp', "Armoire d'énergie de site télécom avec compteurs AC et DC, disjoncteurs batteries et modules redresseurs" ),
				array( 'telecom-armoire-compteurs-dc.webp', "Armoire d'énergie d'un site télécom avec deux compteurs DC, bornier de répartition et protections" ),
				array( 'telecom-armoire-ouverte.webp', "Armoire télécom ouverte entre deux shelters, sous l'auvent solaire d'un site en toiture" ),
				array( 'telecom-site-pylone.webp', 'Site télécom en toiture : armoires sous un auvent de panneaux solaires, pylône et antennes en arrière-plan' ),
				array( 'telecom-site-toit.webp', "Site télécom en terrasse : armoires d'équipements sous un auvent solaire, à côté d'un pylône" ),
				array( 'telecom-site-epower.webp', "Armoires d'énergie et de télécommunications en toiture, sous une structure de panneaux solaires" ),
				array( 'telecom-technicien-pylone.webp', "Technicien équipé d'un harnais montant sur un pylône télécom, à côté des armoires du site" ),
			)
		)
		. pattern( 'snex/bandeau-devis' ),
	),
	'photovoltaique'     => array(
		'Photovoltaïque',
		hero( 'Photovoltaïque', 'Études, installation et maintenance de solutions solaires', "Nous accompagnons vos projets solaires de l'étude à la maintenance, et fournissons le matériel adapté.", 'pv-panneaux-ville.webp', 'Champ de panneaux solaires monocristallins posé en toiture, vue sur la ville' )
		. services(
			array(
				array( 'Études de projets solaires', 'Analyse du besoin et du site, dimensionnement et proposition chiffrée.' ),
				array( 'Installation', 'Pose des panneaux, des onduleurs et du stockage, en toiture ou au sol.', 'pv-structure-sol.webp', 'Panneaux solaires posés sur une structure au sol en acier galvanisé, fixée sur des plots en béton' ),
				array( 'Maintenance', 'Contrôles périodiques et interventions en cas de panne.', 'pv-technicien-onduleur.webp', "Technicien en gilet haute visibilité intervenant sur un coffret électrique à côté d'un onduleur solaire" ),
				array( 'Fourniture de matériel solaire', 'Fourniture du matériel nécessaire à votre installation.', 'pv-onduleurs.webp', "Deux onduleurs solaires hybrides numérotés 01 et 02, fixés au mur d'un local technique" ),
			)
		)
		. gallery(
			'Auvents solaires sur sites télécoms',
			array(
				array( 'pv-auvent-toiture.webp', "Auvent de panneaux solaires installé au-dessus des armoires d'un site télécom en toiture" ),
				array( 'pv-panneaux-ville.webp', 'Champ de panneaux solaires monocristallins posé en toiture, vue sur la ville' ),
				array( 'pv-panneaux-soleil.webp', 'Panneaux solaires sur structure en toiture, au soleil de fin de journée' ),
				array( 'pv-panneaux-pluie.webp', "Panneaux solaires couverts de gouttes de pluie, au-dessus d'armoires techniques" ),
				array( 'pv-auvent-pose.webp', 'Structure solaire en cours de raccordement, câbles en attente au-dessus des armoires' ),
				array( 'pv-auvent-portrait.webp', "Auvent solaire en acier galvanisé fixé sur muret béton, au-dessus d'une armoire télécom" ),
				array( 'pv-auvent-armoires.webp', 'Armoires techniques abritées sous un auvent de panneaux solaires, contreventement en acier' ),
				array( 'pv-auvent-sous-face.webp', "Sous-face d'un auvent solaire : panneaux et rails en acier galvanisé" ),
				array( 'pv-auvent-eclairage-2.webp', "Rails et câblage sous les panneaux d'un auvent solaire, réglette d'éclairage et projecteur" ),
				array( 'pv-mesure-tension-302v.webp', 'Mesure à la pince multimètre de la tension du champ solaire : 302,7 V' ),
			)
		)
		. pattern( 'snex/bandeau-devis' ),
	),
	'formation'          => array(
		'Formation',
		hero( 'Formation', 'Formations techniques et sécurité sur site', 'Nous transmettons notre expérience du terrain : sécurité, électricité, groupes électrogènes, solaire et sites télécoms.', 'formation-stagiaires-attestations.webp', 'Quatre stagiaires en tenue de travail présentant leur attestation de formation' )
		. services(
			array(
				array( 'Sécurité et travail en hauteur', 'Port du harnais, ascension de pylônes, équipements de protection et consignes sur site.', 'telecom-technicien-pylone.webp', "Technicien équipé d'un harnais montant sur un pylône télécom, à côté des armoires du site" ),
				array( 'Électricité et groupes électrogènes', 'Installations électriques, inverseurs, entretien courant des groupes électrogènes.', 'energie-inverseur-auto.webp', "Coffret d'inverseur automatique réseau et groupe électrogène, avec disjoncteurs et parafoudre" ),
				array( 'Systèmes photovoltaïques', 'Fonctionnement, mise en service, mesures et maintenance des installations solaires.', 'pv-mesure-tension-302v.webp', 'Mesure à la pince multimètre de la tension du champ solaire : 302,7 V' ),
				array( 'Exploitation de sites télécoms', "Armoires d'énergie, redresseurs, batteries et maintenance préventive.", 'telecom-armoire-smart-sys.webp', "Armoire d'énergie de site télécom ouverte : système Smart SYS 48 V, redresseurs, entrées solaires et compteurs" ),
			)
		)
		. gallery(
			"Entretien d'un générateur : théorie et pratique",
			array(
				array( 'ge-vidange-huile.webp', 'Vidange : récupération de l\'huile usagée dans un bidon' ),
				array( 'ge-appoint-huile-2.webp', "Huile moteur neuve versée à l'entonnoir dans un groupe électrogène" ),
				array( 'ge-appoint-huile.webp', "Appoint d'huile moteur neuve versée dans un groupe électrogène à l'aide d'un entonnoir" ),
				array( 'ge-filtre-gasoil.webp', 'Filtre à gasoil remplacé, annoté de la date et du compteur horaire de l\'intervention' ),
				array( 'ge-courroie-alternateur.webp', "Courroie d'alternateur contrôlée sur le moteur d'un groupe électrogène" ),
				array( 'ge-capteur-bloc-moteur.webp', 'Raccordement électrique vérifié sur le bloc moteur d\'un groupe électrogène' ),
			)
		)
		. gallery(
			'Électricité : théorie et pratique',
			array(
				array( 'energie-armoire-redresseurs-batteries.webp', "Armoire d'énergie ouverte : compteur, disjoncteurs et redresseurs en haut, batteries lithium ZTE en bas" ),
				array( 'energie-module-redresseur-ihs.webp', "Module redresseur Delta d'un système d'énergie de site télécom, sorti de son armoire" ),
				array( 'energie-coffret-auvent-solaire.webp', "Coffret électrique fixé au poteau d'un auvent de panneaux solaires, sur un site télécom" ),
				array( 'energie-baie-energie.webp', "Baie d'énergie extérieure d'un site télécom, extincteur accroché à côté" ),
				array( 'energie-armoires-bts.webp', 'Armoire ZTE et armoire BTS et transmission sur un site télécom, pylône en arrière-plan' ),
				array( 'energie-site-telecom-abri.webp', "Armoires techniques et caisson d'un site télécom sous abri" ),
			)
		)
		. gallery(
			'Télécommunications : théorie et pratique',
			array(
				array( 'telecom-coffret-distribution-48v.webp', 'Coffret de distribution 48 V ouvert : disjoncteurs des départs, barres de retour et câbles repérés' ),
				array( 'telecom-controleur-redresseurs.webp', "Contrôleur d'un système d'énergie télécom et disjoncteurs DC, câblage repéré, contrôle au tournevis" ),
				array( 'telecom-disjoncteur-tetrapolaire.webp', "Disjoncteur ABB triphasé 20 A et ses câbles d'alimentation, serrage contrôlé au tournevis" ),
				array( 'telecom-armoire-batteries-48v.webp', 'Armoire de batteries de secours : douze batteries 12 V 150 Ah au plomb étanche, en trois rangées de quatre' ),
			)
		)
		. pattern( 'snex/bandeau-devis' ),
	),
	'a-propos'           => array(
		'À propos',
		hero( 'À propos', 'Des solutions techniques fiables pour connecter, alimenter et accompagner votre croissance', 'Télécommunications, infrastructures énergétiques et solutions photovoltaïques, depuis Port-Bouët, Abidjan.', 'pv-auvent-toiture.webp', "Auvent de panneaux solaires installé au-dessus des armoires d'un site télécom en toiture" )
		. '<!-- wp:group {"align":"full","className":"section flyer-section","layout":{"type":"constrained"}} --><div class="wp-block-group alignfull section flyer-section">'
		. '<!-- wp:image {"sizeSlug":"large","align":"center","className":"flyer"} --><figure class="wp-block-image aligncenter size-large flyer"><img src="' . esc_url( $GLOBALS['snex_seed_theme_url'] . 'flyer-snex.webp' ) . '" alt="Flyer S-NEX SARL, Télécom et Photovoltaïque : Connecter, Innover, Énergiser. Nos services : installation télécom, électricité, panneaux solaires, formation. Votre partenaire de confiance pour l’énergie solaire et les solutions télécom."/></figure><!-- /wp:image -->'
		. '</div><!-- /wp:group -->'
		. prose(
			array(
				array(
					'',
					array(
						'Implantée à Port-Bouët, dans le quartier de Gonzagueville à Abidjan, <strong>S-NEX SARL est une entreprise spécialisée dans les télécommunications, les infrastructures énergétiques et les solutions photovoltaïques.</strong> Nous accompagnons les entreprises, les opérateurs et les institutions dans la réalisation, l’exploitation et la maintenance de leurs infrastructures techniques.',
						'Notre engagement : proposer des solutions adaptées aux besoins de chaque client, en privilégiant la fiabilité des installations, la qualité des interventions et la continuité des services.',
					),
				),
				array(
					'Notre mission',
					array(
						'<strong>Concevoir, déployer et maintenir des infrastructures performantes et durables.</strong>',
						'Nous mettons notre expertise au service des projets de télécommunications et d’énergie afin de contribuer à la disponibilité des réseaux, à la sécurisation de l’alimentation électrique et au développement de solutions énergétiques adaptées aux exigences du terrain.',
					),
				),
				array(
					'Notre approche',
					array(
						'<strong>Un partenaire de confiance, à chaque étape de votre projet.</strong>',
						'De l’étude du site à la mise en service, en passant par l’installation, l’exploitation et la maintenance, S-NEX SARL vous accompagne avec une approche intégrée et orientée résultats.',
						'Nous accordons une attention particulière à la qualité d’exécution, à la réactivité de nos équipes et à la planification des interventions, afin de limiter les interruptions de service et d’optimiser la performance des infrastructures.',
					),
				),
				array(
					'Notre implantation et nos ambitions',
					array(
						'<strong>Une présence locale, une ambition régionale.</strong>',
						'Basée à Port-Bouët, Gonzagueville, à Abidjan, S-NEX SARL intervient sur l’ensemble du territoire ivoirien selon les besoins de ses clients. Forte de son ancrage local et tournée vers l’avenir, l’entreprise ambitionne d’étendre progressivement ses activités à d’autres marchés d’Afrique de l’Ouest.',
						'Notre objectif est de bâtir des partenariats durables et de contribuer au développement d’infrastructures fiables, performantes et adaptées aux enjeux technologiques et énergétiques de la région.',
					),
				),
			)
		)
		. pattern( 'snex/bandeau-devis' ),
	),
	'demande-de-devis'   => array(
		'Demande de devis',
		hero( 'Demande de devis', 'Décrivez votre projet', 'Trois étapes : vos coordonnées, votre projet, vos documents. Votre demande est envoyée directement à notre direction.' )
		. '<!-- wp:group {"align":"full","className":"section","backgroundColor":"paper","layout":{"type":"constrained"}} --><div class="wp-block-group alignfull section has-paper-background-color has-background"><!-- wp:snex/devis /--></div><!-- /wp:group -->',
	),
	'contact'            => array(
		'Contact',
		hero( 'Contact', 'Contactez S-NEX SARL', 'Par téléphone, WhatsApp ou e-mail.' )
		. '<!-- wp:group {"align":"full","className":"section prose","layout":{"type":"constrained"}} --><div class="wp-block-group alignfull section prose">'
		. '<!-- wp:heading --><h2 class="wp-block-heading">Coordonnées</h2><!-- /wp:heading -->'
		. bound( 'raison_sociale', 'S-NEX SARL' ) . bound( 'adresse', "Port-Bouët, Gonzagueville, Abidjan, Côte d'Ivoire" ) . bound( 'telephone', '+225 07 13 27 87 59' ) . bound( 'telephone2', '+225 05 66 19 27 57 (WhatsApp)' ) . bound( 'email', 'direction@snexsarl.com' )
		. '<!-- wp:buttons --><div class="wp-block-buttons">'
		. '<!-- wp:button {"metadata":{"bindings":{"url":{"source":"snex/coordonnees","args":{"key":"tel_url"}}}}} --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button">Appeler</a></div><!-- /wp:button -->'
		. '<!-- wp:button {"className":"is-style-contour","metadata":{"bindings":{"url":{"source":"snex/coordonnees","args":{"key":"whatsapp_url"}}}}} --><div class="wp-block-button is-style-contour"><a class="wp-block-button__link wp-element-button">WhatsApp</a></div><!-- /wp:button -->'
		. '<!-- wp:button {"className":"is-style-contour","metadata":{"bindings":{"url":{"source":"snex/coordonnees","args":{"key":"mailto_url"}}}}} --><div class="wp-block-button is-style-contour"><a class="wp-block-button__link wp-element-button" href="mailto:direction@snexsarl.com">Écrire un e-mail</a></div><!-- /wp:button -->'
		. '</div><!-- /wp:buttons -->'
		. '</div><!-- /wp:group -->',
	),
	'mentions-legales'   => array(
		'Mentions légales',
		hero( 'Mentions légales', 'Mentions légales', 'Informations légales relatives au site et au traitement des données personnelles.' )
		. prose(
			array(
				array( 'Éditeur du site', array( '[À compléter par S-NEX SARL : dénomination sociale, forme juridique, capital social, numéro de compte contribuable, adresse du siège.]', 'RCCM : CI-ABJ-03-2026-B12-03677', 'E-mail : direction@snexsarl.com' ) ),
				array( 'Directeur de la publication', '[À compléter : nom et fonction.]' ),
				array( 'Hébergement', '[À compléter après le choix de l\'hébergeur : nom, adresse, contact.]' ),
				array( 'Propriété intellectuelle', "Les textes, photographies et le logo présents sur ce site appartiennent à S-NEX SARL ou sont utilisés avec l'accord de leurs auteurs. Toute reproduction sans autorisation écrite est interdite." ),
				array( 'Données personnelles', array( 'Les informations envoyées par les formulaires de contact et de devis servent uniquement à répondre à votre demande. Elles sont transmises par e-mail à S-NEX SARL et ne sont pas conservées dans une base de données du site. Les pièces jointes sont supprimées du serveur après l\'envoi.', '[À compléter et faire valider : durée de conservation des e-mails, droits des personnes et contact pour les exercer, conformément à la réglementation ivoirienne sur la protection des données personnelles.]' ) ),
			)
		),
	),
);

// Extraits : description des pages dans les moteurs de recherche et au partage (160 caractères au plus).
$excerpts = array(
	'accueil'            => "Installation, exploitation et maintenance de sites télécoms, d'installations électriques et de systèmes photovoltaïques en Côte d'Ivoire.",
	'energie'            => 'Installations électriques, groupes électrogènes et alimentation sécurisée : installation et entretien, y compris quand le réseau coupe.',
	'telecommunications' => "Installation, exploitation et maintenance de sites et d'infrastructures télécoms, de la pose des équipements aux dépannages.",
	'photovoltaique'     => 'Projets solaires de l\'étude à la maintenance : dimensionnement, installation en toiture ou au sol, fourniture de matériel.',
	'formation'          => 'Formations techniques et sécurité sur site : travail en hauteur, électricité, groupes électrogènes, solaire et sites télécoms.',
	'a-propos'           => 'S-NEX SARL, spécialiste des télécommunications, des infrastructures énergétiques et du photovoltaïque à Port-Bouët, Abidjan.',
	'demande-de-devis'   => 'Demandez un devis à S-NEX SARL en trois étapes : vos coordonnées, votre projet, vos documents.',
	'contact'            => 'Contactez S-NEX SARL par téléphone ou par e-mail. Siège à Port-Bouët, Gonzagueville, Abidjan.',
	'mentions-legales'   => 'Informations légales relatives au site de S-NEX SARL et au traitement des données personnelles.',
);

$ids = array();
foreach ( $pages as $slug => $page ) {
	$existing = get_page_by_path( $slug );
	$content  = use_media( $page[1] );
	if ( $existing && ! in_array( '1', $force, true ) && ! in_array( $slug, $force, true ) ) {
		$ids[ $slug ] = $existing->ID;
		// Extrait ajouté aux pages déjà créées, sans écraser celui saisi dans l'éditeur.
		if ( '' === $existing->post_excerpt && isset( $excerpts[ $slug ] ) ) {
			wp_update_post(
				array(
					'ID'           => $existing->ID,
					'post_excerpt' => $excerpts[ $slug ],
				)
			);
		}
		continue;
	}
	$postarr = array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_name'    => $slug,
		'post_title'   => $page[0],
		'post_content' => wp_slash( $content ),
		'post_excerpt' => $excerpts[ $slug ] ?? '',
	);
	if ( $existing ) {
		$postarr['ID'] = $existing->ID;
	}
	$ids[ $slug ] = wp_insert_post( $postarr, true );
	if ( is_wp_error( $ids[ $slug ] ) ) {
		\WP_CLI::error( $ids[ $slug ]->get_error_message() );
	}
}

// Domaines ajoutés après l'activation de l'extension (ex. Formation).
\Snex\Core\Realisations\seed_domains();

/* ---------- 3. Réglages de lecture, identité, logo ---------- */
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $ids['accueil'] );
update_option( 'blogname', 'S-NEX SARL' );
update_option( 'blogdescription', 'Connecter · Innover · Énergiser' );
if ( isset( $media['logo-snex.png'] ) ) {
	update_option( 'site_logo', $media['logo-snex.png'] );
	set_theme_mod( 'custom_logo', $media['logo-snex.png'] );
	update_post_meta( $media['logo-snex.png'], '_wp_attachment_image_alt', 'S-NEX SARL' );
}

/* ---------- 4. Menu principal ---------- */
$menu_items = array(
	array( 'Accueil', 'accueil' ),
	array( 'Télécommunications', 'telecommunications' ),
	array( 'Énergie', 'energie' ),
	array( 'Photovoltaïque', 'photovoltaique' ),
	array( 'Formation', 'formation' ),
	array( 'À propos', 'a-propos' ),
	array( 'Contact', 'contact' ),
	array( 'Demander un devis', 'demande-de-devis', 'menu-devis' ),
);
$links      = '';
foreach ( $menu_items as $item ) {
	$attrs = array(
		'label' => $item[0],
		'type'  => 'page',
		'id'    => $ids[ $item[1] ],
		'url'   => get_permalink( $ids[ $item[1] ] ),
		'kind'  => 'post-type',
	);
	if ( ! empty( $item[2] ) ) {
		$attrs['className'] = $item[2];
	}
	$links .= '<!-- wp:navigation-link ' . wp_json_encode( $attrs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . ' /-->';
}
$nav      = get_posts(
	array(
		'post_type'   => 'wp_navigation',
		'title'       => 'Menu principal',
		'post_status' => 'publish',
		'numberposts' => 1,
	)
);
$nav_post = array(
	'post_type'    => 'wp_navigation',
	'post_status'  => 'publish',
	'post_title'   => 'Menu principal',
	'post_content' => wp_slash( $links ),
);
if ( $nav ) {
	$nav_post['ID'] = $nav[0]->ID;
}
wp_insert_post( $nav_post );

/* ---------- 5. Contenus d'exemple de WordPress retirés ---------- */
foreach ( array(
	'hello-world' => 'post',
	'sample-page' => 'page',
) as $slug => $type ) {
	$p = get_page_by_path( $slug, OBJECT, $type );
	if ( $p ) {
		wp_delete_post( $p->ID, true );
	}
}

\WP_CLI::success( count( $ids ) . ' pages, ' . count( $media ) . ' médias, menu principal prêts.' );
