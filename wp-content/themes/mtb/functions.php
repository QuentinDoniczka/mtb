<?php
/**
 * Socle du thème MTB.
 *
 * Présentation uniquement : aucune règle métier, aucune interrogation de la base, aucune règle CSS.
 * Les feuilles de style vivent dans `assets/css/` et appartiennent à `dev-ux-mtb`.
 *
 * Ce fichier est conçu pour ne plus être rouvert (décision 9 de `docs/ETAT.md`) : la mise en file
 * d'attente des feuilles de bloc est générique, il n'y a donc aucune liste à rallonger quand un
 * composant nouveau arrive.
 *
 * @package MTB
 */

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

/**
 * Met une feuille du thème en file d'attente, si et seulement si elle existe.
 *
 * Le garde `file_exists()` permet aux deux moitiés du socle d'être commitées dans le désordre :
 * une feuille encore absente n'émet ni requête inutile ni avertissement, et la page tient debout.
 * La version vaut `filemtime()` : jamais un numéro tenu à la main, qu'on oublierait d'incrémenter.
 *
 * @param string   $poignee      Poignée gelée par le contrat d'interface.
 * @param string   $relatif      Chemin relatif à la racine du thème.
 * @param string[] $dependances  Poignées dont dépend la feuille.
 */
function mtb_mettre_feuille_en_file( string $poignee, string $relatif, array $dependances = array() ): void {
	$chemin = get_theme_file_path( $relatif );

	if ( ! file_exists( $chemin ) ) {
		return;
	}

	wp_enqueue_style( $poignee, get_theme_file_uri( $relatif ), $dependances, (string) filemtime( $chemin ) );
}

/**
 * Réglages de thème.
 */
function mtb_demarrer_le_theme(): void {
	/*
	 * `patterns/` est vide et le catalogue de compositions du cœur n'a pas sa place dans
	 * l'insérteur : l'éditrice n'y trouverait que des mises en page étrangères au système de design.
	 */
	remove_theme_support( 'core-block-patterns' );

	/*
	 * Les trois feuilles entrent dans l'iframe de l'éditeur. `tokens.css` en fait partie : sans lui
	 * `--l-texte` n'existe pas côté éditeur et la largeur de canal ne s'y affiche pas.
	 */
	$feuilles_editeur = array();

	foreach ( array( 'assets/css/tokens.css', 'assets/css/base.css', 'assets/css/editor.css' ) as $relatif ) {
		if ( file_exists( get_theme_file_path( $relatif ) ) ) {
			$feuilles_editeur[] = $relatif;
		}
	}

	if ( array() !== $feuilles_editeur ) {
		add_editor_style( $feuilles_editeur );
	}
}
add_action( 'after_setup_theme', 'mtb_demarrer_le_theme' );

/**
 * Vide les jeux de valeurs prédéfinies que WordPress fournit d'origine.
 *
 * `theme.json` déclare bien `defaultPalette`, `defaultFontSizes` et leurs voisines à `false`, mais
 * ces clés ne pilotent que `prevent_override` : elles décident si une valeur du thème écrase celle
 * du cœur, elles n'empêchent pas le cœur d'émettre les siennes. `wp_get_global_stylesheet()` fixe
 * en dur les origines `default, theme, custom`, si bien que les quarante-neuf `--wp--preset--*` du
 * cœur étaient écrites sur chaque page.
 *
 * L'enjeu est le verrouillage avant le poids : tant que ces variables existent et fonctionnent, une
 * classe `has-vivid-red-color` restée dans un contenu rendrait une couleur hors des quinze jetons de
 * `MASTER.md` §3.1, que le §13 interdit.
 *
 * Seuls les jeux de valeurs sont vidés. Le reste du schéma du cœur est laissé intact.
 *
 * @param WP_Theme_JSON_Data $donnees Données de l'origine « cœur ».
 * @return WP_Theme_JSON_Data
 */
function mtb_vider_les_valeurs_predefinies_du_coeur( $donnees ) {
	if ( ! $donnees instanceof WP_Theme_JSON_Data ) {
		return $donnees;
	}

	/*
	 * Les listes sont fournies sans clé d'origine : le constructeur de `WP_Theme_JSON` se charge
	 * de les ranger sous l'origine « default », qui est celle de ce filtre.
	 */
	return $donnees->update_with(
		array(
			'version'  => WP_Theme_JSON::LATEST_SCHEMA,
			'settings' => array(
				'color'      => array(
					'palette'   => array(),
					'gradients' => array(),
					'duotone'   => array(),
				),
				'typography' => array(
					'fontSizes' => array(),
				),
				'spacing'    => array(
					'spacingSizes' => array(),
					'spacingScale' => array( 'steps' => 0 ),
				),
				'shadow'     => array(
					'presets' => array(),
				),
				'dimensions' => array(
					'aspectRatios' => array(),
				),
			),
		)
	);
}
add_filter( 'wp_theme_json_data_default', 'mtb_vider_les_valeurs_predefinies_du_coeur' );

/**
 * Retire les variations de style du cœur qui contredisent le système de design.
 *
 * - Image et Logo du site, « Arrondis » : `MASTER.md` §13 interdit nommément `border-radius: 50%`
 *   sur une image, et §14 range l'arrondi parmi ce que l'éditrice ne peut pas atteindre.
 * - Séparateur, « Ligne large » et « Pointillés » : §2.1 remplace tout `<hr>` par le filet double et
 *   en donne une liste close. Ces deux variations laisseraient défaire la signature du site depuis
 *   un menu, sans que rien ne le laisse deviner.
 *
 * Ces variations sont déclarées dans le `block.json` du cœur et **jamais** dans
 * `WP_Block_Styles_Registry`, qui est vide : `unregister_block_style()` n'aurait aucun effet. C'est
 * donc la description du bloc qu'on filtre, avant son enregistrement.
 *
 * Les variations conservées sont un choix assumé : « Contour » (Bouton, Nuage d'étiquettes),
 * « Uni » (Citation) et « Rayures » (Tableau) ne contredisent aucun interdit du §13.
 *
 * @param array $metadonnees Description du bloc lue dans son `block.json`.
 * @return array
 */
function mtb_retirer_les_variations_interdites( array $metadonnees ): array {
	$interdites = array(
		'core/image'     => array( 'rounded' ),
		'core/site-logo' => array( 'rounded' ),
		'core/separator' => array( 'wide', 'dots' ),
	);

	if ( ! isset( $metadonnees['name'] ) || ! isset( $interdites[ $metadonnees['name'] ] ) ) {
		return $metadonnees;
	}

	if ( ! isset( $metadonnees['styles'] ) || ! is_array( $metadonnees['styles'] ) ) {
		return $metadonnees;
	}

	$a_retirer = $interdites[ $metadonnees['name'] ];

	$metadonnees['styles'] = array_values(
		array_filter(
			$metadonnees['styles'],
			static function ( $variation ) use ( $a_retirer ) {
				if ( ! is_array( $variation ) || ! isset( $variation['name'] ) ) {
					return true;
				}

				return ! in_array( $variation['name'], $a_retirer, true );
			}
		)
	);

	return $metadonnees;
}
add_filter( 'block_type_metadata', 'mtb_retirer_les_variations_interdites' );

/**
 * Les deux feuilles servies au visiteur.
 */
function mtb_feuilles_du_site(): void {
	mtb_mettre_feuille_en_file( 'mtb-jetons', 'assets/css/tokens.css' );
	mtb_mettre_feuille_en_file( 'mtb-base', 'assets/css/base.css', array( 'mtb-jetons' ) );
}
add_action( 'wp_enqueue_scripts', 'mtb_feuilles_du_site' );

/**
 * Feuilles propres à un bloc, servies uniquement quand ce bloc est rendu.
 *
 * Déposer `assets/css/blocs/core-image.css` suffit à l'habiller : rien à déclarer ici.
 * L'itération porte sur le registre des blocs, jamais sur le disque — `glob()`, `scandir()`,
 * `opendir()` et `DirectoryIterator` sont désactivés sur une partie des hébergements mutualisés
 * visés en production (décision 4 de `docs/ETAT.md`). Seul `file_exists()` touche le système de
 * fichiers. `wp_loaded` garantit que tous les blocs, cœur comme extension, sont enregistrés.
 */
function mtb_feuilles_de_blocs(): void {
	/*
	 * Pourquoi cet enregistrement : `mtb-jetons` n'est déclaré que par `mtb_feuilles_du_site()`, sur
	 * `wp_enqueue_scripts`, qui ne se déclenche jamais en administration. Chaque feuille ci-dessous
	 * en dépend, et `WP_Dependencies::all_deps()` abandonne l'élément entier — sans erreur ni
	 * avertissement — dès qu'une dépendance est introuvable : aucune feuille de bloc n'atteignait la
	 * toile de l'éditeur, pour les dix composants du catalogue.
	 *
	 * L'enregistrement est préféré au retrait de `deps`, qui marcherait aujourd'hui mais retirerait
	 * une garantie d'ordre : la première feuille sœur qui en aurait besoin casserait en silence.
	 * Contrepartie assumée et documentée : `tokens.css` entre deux fois dans la toile de l'éditeur,
	 * une fois par `add_editor_style()` et une fois par cette dépendance — le même fichier, octet
	 * pour octet, donc impossible à faire diverger, et jamais servi au visiteur.
	 *
	 * La garde `is_admin()` n'est pas de la coquetterie : `wp_loaded` se déclenche avant
	 * `wp_enqueue_scripts`, et `wp_enqueue_style()` sur une poignée déjà enregistrée ne met pas sa
	 * source à jour — une pré-inscription côté site figerait la source et la version servies au
	 * visiteur.
	 */
	if ( is_admin() && ! wp_style_is( 'mtb-jetons', 'registered' ) ) {
		$jetons = get_theme_file_path( 'assets/css/tokens.css' );

		if ( file_exists( $jetons ) ) {
			wp_register_style( 'mtb-jetons', get_theme_file_uri( 'assets/css/tokens.css' ), array(), (string) filemtime( $jetons ) );
		}
	}

	foreach ( array_keys( WP_Block_Type_Registry::get_instance()->get_all_registered() ) as $nom_de_bloc ) {
		// Un nom de bloc vaut `espace/nom` ; le contrôle interdit qu'une clé exotique du registre
		// serve à composer un chemin de fichier.
		if ( 1 !== preg_match( '#^[a-z0-9-]+/[a-z0-9-]+$#', $nom_de_bloc ) ) {
			continue;
		}

		$base    = str_replace( '/', '-', $nom_de_bloc );
		$relatif = 'assets/css/blocs/' . $base . '.css';
		$chemin  = get_theme_file_path( $relatif );

		if ( ! file_exists( $chemin ) ) {
			continue;
		}

		wp_enqueue_block_style(
			$nom_de_bloc,
			array(
				'handle' => 'mtb-bloc-' . $base,
				'src'    => get_theme_file_uri( $relatif ),
				// `path` autorise le cœur à écrire la feuille en ligne quand elle est courte,
				// ce qui économise une requête.
				'path'   => $chemin,
				'deps'   => array( 'mtb-jetons' ),
				'ver'    => (string) filemtime( $chemin ),
			)
		);
	}
}
add_action( 'wp_loaded', 'mtb_feuilles_de_blocs' );

/**
 * Préchargement des deux polices auto-hébergées.
 *
 * `crossorigin="anonymous"` est obligatoire même en même origine : sans lui le navigateur
 * télécharge la police deux fois, une pour le préchargement et une pour le `@font-face`.
 * Le préchargement est conditionné à la présence du fichier — annoncer une police absente
 * coûterait une requête en échec sur chaque page.
 */
function mtb_precharger_les_polices(): void {
	$fichiers = array( 'newsreader-var-latin.woff2', 'public-sans-var-latin.woff2' );

	foreach ( $fichiers as $fichier ) {
		$relatif = 'assets/fonts/' . $fichier;

		if ( ! file_exists( get_theme_file_path( $relatif ) ) ) {
			continue;
		}

		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin="anonymous">' . "\n",
			esc_url( get_theme_file_uri( $relatif ) )
		);
	}
}
add_action( 'wp_head', 'mtb_precharger_les_polices', 1 );

/**
 * Retire du cœur tout ce qui fait sortir une requête du navigateur vers un domaine tiers.
 *
 * Aucun test de version : `remove_action()` sur un crochet absent ne produit ni erreur ni
 * avertissement, la liste couvre donc les cœurs antérieurs et postérieurs à 6.4.
 */
function mtb_purger_les_origines_tierces(): void {
	// Emoji : le script de détection déclare `settings.baseUrl` sur s.w.org.
	// La priorité 7 est celle du cœur ; sans elle le retrait serait sans effet.
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'embed_head', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_emoji_styles' );
	remove_action( 'enqueue_embed_scripts', 'wp_enqueue_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

	// oEmbed : découverte, script hôte et iframes de contenus embarqués.
	// `wp_oembed_add_discovery_links` est accroché deux fois par le cœur, aux priorités 4 et 10.
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links', 4 );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );
	remove_filter( 'embed_oembed_html', 'wp_maybe_enqueue_oembed_host_js' );

	// Répertoire de blocs distant, dans l'insérteur de l'éditeur.
	remove_action( 'enqueue_block_editor_assets', 'wp_enqueue_editor_block_directory_assets' );
}
add_action( 'init', 'mtb_purger_les_origines_tierces' );

/**
 * Même purge, pour les crochets déclarés par `wp-admin/includes/admin-filters.php`.
 *
 * Ce fichier est chargé après `wp_loaded` : un retrait posé plus tôt serait sans effet.
 */
function mtb_purger_les_origines_tierces_en_administration(): void {
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_enqueue_scripts', 'wp_enqueue_emoji_styles' );
}
add_action( 'admin_init', 'mtb_purger_les_origines_tierces_en_administration' );

/**
 * Retire la collection Google Fonts de la bibliothèque de polices.
 *
 * Son manifeste est hébergé sur s.w.org. Le cœur l'enregistre sur `init` à la priorité 10 ;
 * ce retrait passe donc plus tard.
 */
function mtb_retirer_la_collection_google_fonts(): void {
	if ( ! function_exists( 'wp_unregister_font_collection' ) || ! class_exists( 'WP_Font_Library' ) ) {
		return;
	}

	// Le cœur signale un `_doing_it_wrong` si la collection n'est pas enregistrée.
	if ( ! array_key_exists( 'google-fonts', WP_Font_Library::get_instance()->get_font_collections() ) ) {
		return;
	}

	wp_unregister_font_collection( 'google-fonts' );
}
add_action( 'init', 'mtb_retirer_la_collection_google_fonts', 20 );

/**
 * Retire le script `wp-embed`, devenu sans objet une fois l'oEmbed hôte désactivé.
 */
function mtb_retirer_le_script_embed(): void {
	wp_deregister_script( 'wp-embed' );
}
add_action( 'wp_enqueue_scripts', 'mtb_retirer_le_script_embed', 100 );

/**
 * Retire le greffon emoji de l'éditeur classique.
 *
 * @param string[] $greffons Greffons TinyMCE.
 * @return string[]
 */
function mtb_retirer_le_greffon_emoji( array $greffons ): array {
	return array_values( array_diff( $greffons, array( 'wpemoji' ) ) );
}
add_filter( 'tiny_mce_plugins', 'mtb_retirer_le_greffon_emoji' );

/**
 * Retire s.w.org des indices de ressource, quelle qu'en soit la provenance.
 *
 * @param array $urls Indices de ressource, chaînes ou tableaux `href`.
 * @return array
 */
function mtb_purger_les_indices_de_ressource( array $urls ): array {
	$conserves = array();

	foreach ( $urls as $url ) {
		$href = is_array( $url ) && isset( $url['href'] ) ? $url['href'] : $url;

		if ( is_string( $href ) && false !== strpos( $href, 's.w.org' ) ) {
			continue;
		}

		$conserves[] = $url;
	}

	return $conserves;
}
add_filter( 'wp_resource_hints', 'mtb_purger_les_indices_de_ressource' );

/**
 * Coupe les avatars, servis par secure.gravatar.com.
 *
 * @return string Valeur d'option court-circuitée.
 */
function mtb_couper_les_avatars(): string {
	return '0';
}
add_filter( 'pre_option_show_avatars', 'mtb_couper_les_avatars' );

/**
 * Retire l'onglet Openverse de l'insérteur de médias.
 *
 * Le cœur installé ne pose aucune valeur par défaut côté PHP : la catégorie est enregistrée par son
 * JavaScript, qui interroge api.openverse.org tant que ce réglage n'est pas explicitement faux.
 *
 * @param array $reglages Réglages de l'éditeur de blocs.
 * @return array
 */
function mtb_couper_openverse( array $reglages ): array {
	$reglages['enableOpenverseMediaCategory'] = false;

	return $reglages;
}
add_filter( 'block_editor_settings_all', 'mtb_couper_openverse' );

// Découverte oEmbed distante et compositions distantes hébergées chez WordPress.org.
add_filter( 'embed_oembed_discover', '__return_false' );
add_filter( 'should_load_remote_block_patterns', '__return_false' );
