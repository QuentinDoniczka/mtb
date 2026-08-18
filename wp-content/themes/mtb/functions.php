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
	mtb_mettre_feuille_en_file( 'mtb-entete-pied', 'assets/css/entete-pied.css', array( 'mtb-jetons' ) );
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

/**
 * Écarte le lien d'évitement que le cœur injecte par JavaScript.
 *
 * `wp_enqueue_block_template_skip_link()` (`wp-includes/theme-templates.php:109`) fait deux choses
 * d'un seul geste : elle imprime la feuille en ligne qui masque `.skip-link.screen-reader-text`, et
 * elle enfile un script qui crée un second lien d'évitement **avant** `.wp-site-blocks`, donc avant
 * celui de `parts/header.html`. Le retirer entièrement — par
 * `remove_action( 'wp_footer', 'the_block_template_skip_link' )` — emporterait aussi la feuille, et
 * le lien écrit à la main resterait visible en permanence : `base.css` section 6 n'habille que son
 * état de focus et s'appuie sur cette feuille du cœur pour l'état masqué.
 *
 * Seul le script est donc retiré. Poignées de script et de style sont deux registres distincts,
 * `wp_dequeue_script()` ne touche pas à la feuille du même nom.
 */
function mtb_retirer_le_lien_d_evitement_du_coeur(): void {
	wp_dequeue_script( 'wp-block-template-skip-link' );
}
add_action( 'wp_enqueue_scripts', 'mtb_retirer_le_lien_d_evitement_du_coeur', 100 );

/**
 * Les deux emplacements de menu du site.
 *
 * `register_nav_menus()` appelle `add_theme_support( 'menus' )`, ce qui suffit à faire apparaître
 * `Apparence > Menus` (`wp-admin/menu.php:247-249`, sans garde `wp_is_block_theme()`). C'est
 * l'écran depuis lequel l'éleveuse compose ses menus, sans passer par l'éditeur de site.
 *
 * Deux emplacements et non un : le pied de page demande un **plan du site**, pas une copie du menu
 * d'en-tête. Les libellés sont ceux qu'elle lit à l'écran, dans la colonne des emplacements.
 */
function mtb_declarer_les_emplacements_de_menu(): void {
	register_nav_menus(
		array(
			'principal' => 'Menu principal',
			'pied'      => 'Plan du site',
		)
	);
}
add_action( 'after_setup_theme', 'mtb_declarer_les_emplacements_de_menu' );

/**
 * Mémorise l'emplacement que sert le bloc Navigation en cours de rendu.
 *
 * Le filtre de repli (`block_core_navigation_render_fallback`) ne reçoit **que** des blocs : rien
 * ne lui dit quel bloc il sert. Sans cette mémoire, les deux navigations de la page rendraient le
 * même menu, et le plan du site serait une copie de l'en-tête.
 *
 * **Couplage assumé, et c'est pour cela qu'il est écrit ici** : la reconnaissance se fait sur la
 * classe `mtb-plan-du-site` de `parts/footer.html`. Renommer cette classe sans toucher à cette
 * fonction ferait retomber le pied de page sur le menu principal — en silence, sans erreur. Le
 * défaut est volontairement `principal` et jamais « aucun » : un bloc Navigation posé ailleurs
 * qu'en pied de page rend le menu principal plutôt que rien.
 *
 * @param string|null $emplacement Emplacement à mémoriser ; `null` pour lire.
 * @return string
 */
function mtb_emplacement_de_navigation( ?string $emplacement = null ): string {
	static $courant = 'principal';

	if ( null !== $emplacement ) {
		$courant = $emplacement;
	}

	return $courant;
}

/**
 * Reconnaît, avant son rendu, l'emplacement que sert un bloc Navigation.
 *
 * `render_block_data` se déclenche pour chaque bloc, juste avant que son rendu ne commence : la
 * valeur est donc posée quand le repli la relit, et chaque bloc Navigation la repose pour lui-même.
 *
 * @param array $bloc Bloc analysé.
 * @return array
 */
function mtb_reperer_l_emplacement_de_navigation( $bloc ) {
	if ( ! is_array( $bloc ) || ! isset( $bloc['blockName'] ) || 'core/navigation' !== $bloc['blockName'] ) {
		return $bloc;
	}

	$classes = isset( $bloc['attrs']['className'] ) && is_string( $bloc['attrs']['className'] )
		? preg_split( '/\s+/', trim( $bloc['attrs']['className'] ) )
		: array();

	if ( ! is_array( $classes ) ) {
		$classes = array();
	}

	mtb_emplacement_de_navigation( in_array( 'mtb-plan-du-site', $classes, true ) ? 'pied' : 'principal' );

	return $bloc;
}
add_filter( 'render_block_data', 'mtb_reperer_l_emplacement_de_navigation' );

/*
 * Empêche le cœur de convertir le menu classique en contenu enregistré.
 *
 * Sans ce filtre, `WP_Navigation_Fallback::create_classic_menu_fallback()`
 * (`wp-includes/class-wp-navigation-fallback.php:139-171`) recopie le menu **une seule fois** dans
 * un contenu `wp_navigation`, puis ne relit plus jamais le menu classique : l'éleveuse modifierait
 * son menu, la page publique ne changerait pas, et rien à l'écran ne le lui dirait.
 */
add_filter( 'wp_navigation_should_create_fallback', '__return_false' );

/**
 * Mémorise qu'un bloc Navigation vient d'être rendu sans aucune entrée.
 *
 * Deux crochets distincts ont besoin de la même information et le cœur n'en transporte aucune :
 * le repli calcule les blocs, le rendu décide de ne rien afficher. La valeur par défaut est
 * `false`, si bien qu'un bloc qui n'est jamais passé par le repli — parce qu'il porte un `ref` ou
 * des blocs internes — n'est jamais concerné.
 *
 * @param bool|null $constat Vrai quand le repli n'a produit aucune entrée ; `null` pour lire.
 * @return bool
 */
function mtb_navigation_sans_entree( ?bool $constat = null ): bool {
	static $sans_entree = false;

	if ( null !== $constat ) {
		$sans_entree = $constat;
	}

	return $sans_entree;
}

/**
 * Une entrée de menu que le cœur rendra réellement.
 *
 * **Pourquoi ce tri, et pourquoi il n'est pas cosmétique.** `get_inner_blocks_html()`
 * (`wp-includes/blocks/navigation.php:186-209`) ferme le `<ul>` dès qu'une entrée ne rend pas de
 * `<li>`, puis en rouvre un pour la suivante. Une seule entrée muette au milieu du menu coupe donc
 * la liste **en deux `<ul>` frères** — deux listes annoncées au lecteur d'écran là où l'éleveuse
 * n'en a composé qu'une. Constaté : une entrée pointant vers une page en brouillon.
 *
 * Le critère est recopié de **`render_block_core_navigation_link()`**
 * (`wp-includes/blocks/navigation-link.php:172-203`) et de
 * **`render_block_core_navigation_submenu()`** (`navigation-submenu.php:67-80`), qui rendent tous
 * deux une chaîne vide sur ces deux conditions. Écarter ici ce que le cœur écarterait une étape
 * plus loin ne cache donc **rien qui aurait été visible**.
 *
 * **`is_post_publicly_viewable()` a été lue et écartée**, alors qu'elle semblait faite pour ça :
 * `wp-includes/post.php:2508-2519` la définit comme `is_post_type_viewable() &&
 * is_post_status_viewable()`, qui n'est **pas** le critère des deux blocs ci-dessus. Sur un type de
 * contenu non visible publiquement mais dont le contenu est publié, elle rend faux alors que le
 * cœur, lui, rendrait l'entrée : le thème masquerait une entrée que l'éleveuse verrait autrement.
 * Le brouillon et le contenu privé sont déjà écartés par le statut, donc rien n'est perdu.
 *
 * **Couplage à surveiller** : le cœur expose depuis 6.8 le filtre
 * `render_block_core_navigation_link_allowed_post_status`, qui peut élargir les statuts rendus. Il
 * n'est pas appliqué ici — il attend un `WP_Block`, que ce tri n'a pas — donc si quelqu'un s'en
 * sert un jour, cette liste doit suivre. `navigation-submenu.php` ne l'expose pas et fige `publish`.
 *
 * Les autres genres (`custom`, `taxonomy`, `post-type-archive`) sont conservés tels quels : le
 * thème ne juge pas ce qu'il ne sait pas juger. Une entrée déroulante écartée **emporte ses
 * enfants**, exactement comme le ferait le cœur, qui ne rend pas son sous-arbre.
 *
 * Une entrée écartée n'est jamais remplacée ni signalée au visiteur : le thème n'invente rien.
 *
 * @param mixed $entree Bloc analysé.
 * @return bool
 */
function mtb_entree_de_menu_rendue( $entree ): bool {
	if ( ! is_array( $entree ) || ! isset( $entree['blockName'] ) ) {
		return false;
	}

	if ( ! in_array( $entree['blockName'], array( 'core/navigation-link', 'core/navigation-submenu' ), true ) ) {
		return true;
	}

	$attributs = isset( $entree['attrs'] ) && is_array( $entree['attrs'] ) ? $entree['attrs'] : array();

	if ( empty( $attributs['label'] ) ) {
		return false;
	}

	$vers_un_contenu = ( isset( $attributs['kind'] ) && 'post-type' === $attributs['kind'] )
		|| ( isset( $attributs['type'] ) && in_array( $attributs['type'], array( 'post', 'page' ), true ) );

	if ( ! $vers_un_contenu || ! isset( $attributs['id'] ) || ! is_numeric( $attributs['id'] ) ) {
		return true;
	}

	$contenu = get_post( (int) $attributs['id'] );

	return $contenu instanceof WP_Post && 'publish' === $contenu->post_status;
}

/**
 * Reconstruit les entrées du menu à chaque requête, depuis le menu classique de l'emplacement.
 *
 * Le repli du cœur (`wp-includes/blocks/navigation.php:1055-1066`) propose un `core/page-list`
 * quand il ne trouve rien : le thème inventerait alors une liste de pages que personne n'a
 * composée. Ce filtre remplace ce repli par la seule source d'autorité du projet — le menu que
 * l'éleveuse tient dans `Apparence > Menus` — relue à chaque rendu, jamais recopiée.
 *
 * `WP_Classic_To_Block_Menu_Converter::convert()` est l'API publique et non dépréciée de cette
 * conversion (`wp-includes/class-wp-classic-to-block-menu-converter.php:26`) ; les fonctions
 * `block_core_navigation_get_classic_menu_fallback*()` le sont depuis 6.3.
 *
 * L'emplacement servi vient de `mtb_emplacement_de_navigation()`, posée juste avant le rendu :
 * l'en-tête rend `principal`, le pied de page rend `pied`, et les deux coexistent sur la page.
 *
 * Aucun emplacement assigné, menu supprimé, menu vide, conversion en échec : dans tous ces cas le
 * repli est vide et le bloc ne s'affiche pas au visiteur (décision 26). Le thème ne comble rien.
 *
 * @param array $blocs_de_repli Repli proposé par le cœur, ignoré.
 * @return array
 */
function mtb_entrees_du_menu_classique( $blocs_de_repli ): array {
	unset( $blocs_de_repli );

	$aucune      = array();
	$emplacement = mtb_emplacement_de_navigation();

	if ( ! class_exists( 'WP_Classic_To_Block_Menu_Converter' ) ) {
		mtb_navigation_sans_entree( true );

		return $aucune;
	}

	$emplacements = get_nav_menu_locations();

	if ( ! is_array( $emplacements ) || empty( $emplacements[ $emplacement ] ) ) {
		mtb_navigation_sans_entree( true );

		return $aucune;
	}

	// Un emplacement peut pointer vers un menu supprimé entre-temps : l'objet est alors faux.
	$menu = wp_get_nav_menu_object( $emplacements[ $emplacement ] );

	if ( ! $menu ) {
		mtb_navigation_sans_entree( true );

		return $aucune;
	}

	$balisage = WP_Classic_To_Block_Menu_Converter::convert( $menu );

	if ( is_wp_error( $balisage ) || ! is_string( $balisage ) || '' === trim( $balisage ) ) {
		mtb_navigation_sans_entree( true );

		return $aucune;
	}

	$entrees = parse_blocks( $balisage );

	if ( function_exists( 'block_core_navigation_filter_out_empty_blocks' ) ) {
		$entrees = block_core_navigation_filter_out_empty_blocks( $entrees );
	}

	$entrees = array_values( array_filter( $entrees, 'mtb_entree_de_menu_rendue' ) );

	mtb_navigation_sans_entree( array() === $entrees );

	return $entrees;
}
add_filter( 'block_core_navigation_render_fallback', 'mtb_entrees_du_menu_classique' );

/**
 * Un menu sans entrée ne laisse pas un point de repère vide au lecteur d'écran.
 *
 * Le cœur rend `<nav></nav>` même quand il n'a aucune entrée à y mettre (`navigation.php:708-712`) :
 * un point de repère de navigation annoncé et vide. Décision 26 : côté public, un composant sans
 * contenu ne s'affiche pas.
 *
 * @param string $contenu Balisage rendu du bloc.
 * @return string
 */
function mtb_retirer_la_navigation_sans_entree( $contenu ) {
	if ( ! mtb_navigation_sans_entree() ) {
		return $contenu;
	}

	mtb_navigation_sans_entree( false );

	return '';
}
add_filter( 'render_block_core/navigation', 'mtb_retirer_la_navigation_sans_entree' );

/**
 * Reconnaît les requêtes de l'écran des menus, et elles seules.
 *
 * Deux formes existent : l'écran lui-même (`nav-menus.php`, qui reçoit aussi ses propres envois de
 * formulaire) et les quatre actions `admin-ajax` dont son JavaScript a besoin. Les quatre sont
 * déclarées dans `wp-admin/admin-ajax.php:77,83,85,86` et chacune vérifie `edit_theme_options` de
 * son côté (`wp-admin/includes/ajax-actions.php:1528,1876,1963,2019`) : sans elles, la colonne de
 * gauche, la recherche rapide, l'ajout d'entrée et l'enregistrement de l'emplacement échouent en
 * silence.
 *
 * `$_REQUEST['action']` n'est ici qu'un aiguillage : chaque action du cœur revérifie son propre
 * nonce et sa propre capacité avant d'écrire quoi que ce soit.
 *
 * @return bool
 */
function mtb_requete_de_l_ecran_des_menus(): bool {
	if ( ! is_admin() ) {
		return false;
	}

	$page = isset( $GLOBALS['pagenow'] ) ? (string) $GLOBALS['pagenow'] : '';

	if ( 'nav-menus.php' === $page ) {
		return true;
	}

	if ( 'admin-ajax.php' !== $page ) {
		return false;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- aiguillage seul, voir plus haut.
	$action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : '';

	return in_array(
		$action,
		array( 'add-menu-item', 'menu-get-metabox', 'menu-locations-save', 'menu-quick-search' ),
		true
	);
}

/**
 * Ouvre l'écran des menus à qui tient déjà les pages du site — et rien d'autre.
 *
 * Le rôle Éditeur n'a ni `switch_themes` ni `edit_theme_options` : `Apparence` lui est entièrement
 * invisible (`wp-admin/menu.php:207`) et `nav-menus.php:23` le refuse. Un
 * `add_cap( 'edit_theme_options' )` réglerait le problème et en créerait deux : il s'écrit dans
 * l'option `wp_user_roles`, donc survit au changement de thème sans que personne ne le relie à sa
 * cause ; et il ouvrirait `Apparence > Éditeur` — l'éditeur de site entier — en permanence, qu'un
 * `remove_submenu_page()` masquerait sans jamais refuser une URL ni un appel REST.
 *
 * La capacité est donc accordée **à la requête**, jamais en base : elle n'existe que le temps de
 * l'écran des menus, et disparaît avec le thème. `edit_pages` est la capacité retenue par
 * `docs/contracts/issue-38.md` §7.1 pour désigner la personne qui tient les pages du site.
 *
 * @param array $capacites Capacités effectives de l'utilisateur.
 * @param array $demandees Capacités primitives exigées par l'appel en cours.
 * @return array
 */
function mtb_ouvrir_l_ecran_des_menus( $capacites, $demandees ) {
	if ( ! is_array( $capacites ) || ! is_array( $demandees ) ) {
		return $capacites;
	}

	if ( ! in_array( 'edit_theme_options', $demandees, true ) ) {
		return $capacites;
	}

	if ( empty( $capacites['edit_pages'] ) ) {
		return $capacites;
	}

	if ( ! mtb_requete_de_l_ecran_des_menus() ) {
		return $capacites;
	}

	$capacites['edit_theme_options'] = true;

	return $capacites;
}
add_filter( 'user_has_cap', 'mtb_ouvrir_l_ecran_des_menus', 10, 2 );

/**
 * Le chemin par lequel l'éleveuse arrive à son menu.
 *
 * Corollaire du filtre ci-dessus : sur toute autre requête la capacité n'est pas accordée, donc
 * `Apparence` reste invisible et l'écran des menus n'a aucun point d'entrée. Cette entrée de
 * premier niveau en est un, atteignable avec `edit_pages` — les capacités qu'elle a déjà. Elle est
 * posée juste sous « Pages », d'où l'on vient quand on veut ajouter une page au menu.
 *
 * Le libellé est **Menus**, celui de l'écran lui-même : aucun mot de `MASTER.md` §10.4.
 *
 * Position **25** : les trois types de contenu de l'extension occupent 21 à 23 (Portée, Chien,
 * Résultat de travail) et l'écran des coordonnées occupe 24. Les menus se rangent juste après. Deux
 * entrées à la même position se départagent de façon non déterministe dans `$menu`.
 */
function mtb_entree_de_menu_vers_les_menus(): void {
	add_menu_page( 'Menus', 'Menus', 'edit_pages', 'nav-menus.php', '', 'dashicons-menu-alt', 25 );
}
add_action( 'admin_menu', 'mtb_entree_de_menu_vers_les_menus' );

/**
 * Sur l'écran des menus, ne montre pas des portes que la capacité n'ouvre pas.
 *
 * Le temps de cette requête, `edit_theme_options` est accordée : `Apparence` redevient visible et
 * affiche « Thèmes » et « Éditeur ». Les deux mènent à un refus, puisque la capacité n'est pas
 * accordée sur leurs requêtes à elles. Les masquer ici ne remplace pas ce refus — il tient tout
 * seul, y compris sur une URL tapée — il évite seulement de proposer une impasse.
 *
 * `switch_themes` distingue le compte d'administration, qui garde son menu `Apparence` entier.
 */
function mtb_refermer_l_apparence_sur_l_ecran_des_menus(): void {
	if ( ! mtb_requete_de_l_ecran_des_menus() ) {
		return;
	}

	if ( current_user_can( 'switch_themes' ) ) {
		return;
	}

	remove_submenu_page( 'themes.php', 'themes.php' );
	remove_submenu_page( 'themes.php', 'site-editor.php' );
}
add_action( 'admin_menu', 'mtb_refermer_l_apparence_sur_l_ecran_des_menus', 999 );

/**
 * Rend focalisable la cible du lien d'évitement, sur tous les gabarits.
 *
 * Sans `tabindex="-1"`, suivre « Aller au contenu » déplace la vue mais **laisse le focus
 * derrière** : la navigation au clavier repart du début et le lecteur d'écran n'annonce rien. Le
 * mesurer sur les deux gabarits d'erreur seuls donnerait un test qui passe et ne prouve rien.
 *
 * L'attribut est posé **au rendu**, jamais dans le balisage enregistré, pour trois raisons :
 *
 * 1. Un attribut non prévu par le `block.json` d'un `core/group` fait échouer la validation du bloc
 *    dans l'éditeur de site — le balisage enregistré reste donc valide.
 * 2. La garantie devient uniforme sur tout le site au lieu d'être vraie sur deux gabarits.
 * 3. Les gabarits à venir en héritent sans avoir à y penser : `index.html` et `singular.html`
 *    n'appartiennent à aucune issue, une convention écrite dans un contrat n'aurait été payée par
 *    personne.
 *
 * `WP_HTML_Tag_Processor` est l'outil prévu pour modifier un attribut sur du balisage rendu ;
 * aucune expression régulière n'est appliquée à du HTML.
 *
 * @param string $contenu Balisage rendu du bloc.
 * @param array  $bloc    Bloc analysé.
 * @return string
 */
function mtb_rendre_la_cible_focalisable( $contenu, $bloc ) {
	if ( ! is_string( $contenu ) || ! is_array( $bloc ) ) {
		return $contenu;
	}

	// Le contrôle est volontairement le plus étroit possible : il se lit sur deux clés de tableau,
	// et n'ouvre le lecteur de balisage que pour le seul bloc ancré sur la cible.
	if ( ! isset( $bloc['attrs']['anchor'] ) || 'contenu' !== $bloc['attrs']['anchor'] ) {
		return $contenu;
	}

	if ( ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
		return $contenu;
	}

	$processeur = new WP_HTML_Tag_Processor( $contenu );

	if ( ! $processeur->next_tag() ) {
		return $contenu;
	}

	if ( 'contenu' !== $processeur->get_attribute( 'id' ) || null !== $processeur->get_attribute( 'tabindex' ) ) {
		return $contenu;
	}

	$processeur->set_attribute( 'tabindex', '-1' );

	return $processeur->get_updated_html();
}
add_filter( 'render_block', 'mtb_rendre_la_cible_focalisable', 10, 2 );

/**
 * Fait entrer la feuille de l'en-tête et du pied de page dans la toile de l'éditeur.
 *
 * Sans elle, l'en-tête et le pied de page s'affichent sans fond, sans filet double et sans
 * traitement de navigation dans l'éditeur de site : ce qu'on y voit ne ressemble pas au site.
 *
 * L'appel est séparé de `mtb_demarrer_le_theme()` et non ajouté à sa liste : `add_editor_style()`
 * accumule les feuilles au lieu de les remplacer, et `functions.php` ne reçoit que des ajouts —
 * aucune ligne existante n'est rouverte. Le garde `file_exists()` est celui du reste du fichier :
 * une feuille absente n'annonce rien.
 */
function mtb_feuille_entete_pied_dans_l_editeur(): void {
	if ( file_exists( get_theme_file_path( 'assets/css/entete-pied.css' ) ) ) {
		add_editor_style( 'assets/css/entete-pied.css' );
	}
}
add_action( 'after_setup_theme', 'mtb_feuille_entete_pied_dans_l_editeur', 11 );

/**
 * Donne son nom au point de repère de navigation, sur le balisage rendu.
 *
 * Deux `<nav>` sans nom accessible sont annoncés « navigation » deux fois : rien ne les distingue.
 * Le cœur tire ce nom du titre du contenu `wp_navigation`, et la conception de cette issue fait
 * qu'il n'en existe aucun — c'est même tout son objet.
 *
 * **Pourquoi pas l'attribut `ariaLabel` du bloc, qui serait la voie évidente.** Sur WordPress 6.9,
 * il est rendu **deux fois de suite dans la même valeur**. Deux mécanismes du cœur émettent
 * `aria-label` pour ce bloc : `get_nav_wrapper_attributes()` (`wp-includes/blocks/navigation.php`,
 * qui lit `ariaLabel` via `get_unique_navigation_name()`) et le support de bloc
 * `wp-includes/block-supports/aria-label.php`, ajouté en 6.8. Or
 * `class-wp-block-supports.php:184` range `aria-label` dans la liste des attributs **concaténés**,
 * aux côtés de `class` et `style` : la valeur est donc collée à elle-même. Le défaut est dans le
 * cœur et ne dépend d'aucun filtre du thème.
 *
 * **Ne pas remettre `ariaLabel` dans `parts/header.html` ni `parts/footer.html` en croyant
 * simplifier** : sans cet attribut, aucun des deux mécanismes ne se déclenche, et le nom est posé
 * ici, une seule fois. L'attribut n'est écrit que s'il est absent, de sorte qu'un cœur qui
 * corrigerait la concaténation reprendrait la main sans que rien ne soit à défaire.
 *
 * Le nom vient du **libellé de l'emplacement enregistré**, celui que l'éleveuse lit dans son écran
 * des menus : une seule source de vérité, qui suit si les libellés changent. Un libellé introuvable
 * ou vide ne pose rien — un `aria-label=""` vaut moins que pas d'attribut.
 *
 * Priorité 20 : `mtb_retirer_la_navigation_sans_entree()` passe avant et rend une chaîne vide quand
 * le menu n'a aucune entrée. Nommer un balisage vide le ressusciterait.
 *
 * @param string $contenu Balisage rendu du bloc.
 * @return string
 */
function mtb_nommer_la_navigation( $contenu ) {
	if ( ! is_string( $contenu ) || '' === trim( $contenu ) ) {
		return $contenu;
	}

	$emplacements = get_registered_nav_menus();
	$emplacement  = mtb_emplacement_de_navigation();

	if ( ! is_array( $emplacements ) || ! isset( $emplacements[ $emplacement ] ) ) {
		return $contenu;
	}

	$nom = trim( (string) $emplacements[ $emplacement ] );

	if ( '' === $nom || ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
		return $contenu;
	}

	$processeur = new WP_HTML_Tag_Processor( $contenu );

	if ( ! $processeur->next_tag( array( 'tag_name' => 'NAV' ) ) ) {
		return $contenu;
	}

	if ( null !== $processeur->get_attribute( 'aria-label' ) ) {
		return $contenu;
	}

	$processeur->set_attribute( 'aria-label', $nom );

	return $processeur->get_updated_html();
}
add_filter( 'render_block_core/navigation', 'mtb_nommer_la_navigation', 20 );
