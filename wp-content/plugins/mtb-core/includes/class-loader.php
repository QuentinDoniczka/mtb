<?php
/**
 * Chargeur à auto-découverte des modules de l'extension.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * CONVENTION D'AUTO-ENREGISTREMENT DES MODULES
 *
 * Un module = un dossier : includes/<groupe>/<module>/bootstrap.php, seul fichier inclus ici.
 * Dossier sans bootstrap.php → ignoré, avec une note journalisée si WP_DEBUG. Dossier préfixé
 * « _ » → ignoré volontairement, c'est la façon de désactiver un module sans le supprimer.
 * Aucune issue ne modifie jamais « mtb-core.php », sans exception ni amendement possible. Ce
 * fichier-ci ne s'ouvre que par une réouverture nominative, écrite et datée au §13 du contrat #1 ;
 * une seule a été accordée à ce jour, à l'issue #27, et elle est refermée.
 *
 * Les six groupes, dans l'ordre de parcours, et le hook imposé à leurs modules :
 *
 *   content   → init 10
 *   fields    → add_meta_boxes, save_post_<type> ; scripts d'éditeur sur init 20
 *   query     → aucun hook, simples déclarations de fonctions
 *   blocks    → init 20
 *   admin     → admin_menu, admin_init
 *   migration → déclaration à l'inclusion, sous garde WP_CLI
 *
 * Un module ne dépend jamais de cet ordre de parcours : le séquencement réel passe par les hooks
 * WordPress, et par eux seuls. Les priorités init 99 et wp_loaded 20 sont réservées au chargeur,
 * et aucun module n'appelle flush_rewrite_rules(). Ce qu'un module a en revanche le droit de faire
 * en matière de règles de réécriture est décrit au bloc suivant.
 *
 * Interdit dans un bootstrap.php À L'INCLUSION :
 *   - tout accès à la base : get_option, get_posts, WP_Query, get_terms ;
 *   - register_post_type / _taxonomy / _block_type / _post_meta hors d'un rappel de hook ;
 *   - toute fonction de traduction — __(), _e(), _x(), _n(), esc_html__() — avant « init » ;
 *   - toute fonction remplaçable : wp_get_current_user, is_user_logged_in, wp_mail, wp_redirect ;
 *   - toute sortie : echo, print, printf, HTML hors balises PHP ;
 *   - session_start, header(), ini_set, et tout appel HTTP sortant.
 *
 * Le chargeur isole un module qui lève une exception à l'inclusion. Il n'isole ni une faute de
 * frappe, ni un module qui plante à l'usage : un require d'un fichier non analysable reste un
 * E_COMPILE_ERROR brut, jamais un ParseError rattrapable (vérifié sur PHP 8.1 à l'issue #1).
 *
 * Le contrat complet — nommage, recettes de module par groupe, frontière thème/extension, états
 * spéciaux — est dans « docs/contracts/issue-1.md ». En cas de divergence, le contrat fait foi.
 */

/*
 * RÈGLES DE RÉÉCRITURE D'URL — CE QU'UN MODULE A LE DROIT DE FAIRE (issue #27)
 *
 * Un module PEUT appeler add_rewrite_rule(), add_permastruct(), add_rewrite_tag() et
 * add_rewrite_endpoint() depuis son rappel « init » 10, comme n'importe quelle extension
 * WordPress. Cela n'a jamais été interdit : les trois interdits du contrat #1 §13 portent sur
 * flush_rewrite_rules(), sur les priorités réservées au chargeur — « init » 99 et « wp_loaded »
 * 20 — et sur la dépendance à l'ordre de parcours des groupes. Ces trois-là restent entiers,
 * sans exception.
 *
 * ET LE MODULE N'A RIEN D'AUTRE À FAIRE. Il ne régénère pas les règles, ne touche pas aux
 * permaliens, ne prévient personne. L'empreinte comparée à « init » 99 embarque l'état complet
 * des entrées à partir desquelles WordPress fabrique l'option « rewrite_rules » :
 * permastructures, étiquettes de réécriture, règles supplémentaires (haut, bas, hors WordPress)
 * et terminaisons. Une règle ajoutée fait donc changer l'empreinte, ce qui déclenche la
 * régénération sur LA PREMIÈRE REQUÊTE VENUE — y compris celle d'un visiteur anonyme, sans
 * passer par Réglages → Permaliens, écran que le rôle Éditeur ne peut pas ouvrir faute de
 * « manage_options ». C'est tout l'objet de l'issue #27.
 *
 * À LA CHARGE DU MODULE
 *   - poser sa règle depuis un rappel de « init » 10, jamais à l'inclusion du bootstrap.php ;
 *   - l'enregistrer SANS CONDITION DE CONTEXTE : jamais derrière « if ( is_admin() ) », jamais
 *     derrière une condition qui distingue une requête publique d'une requête d'administration
 *     (voir angle mort 2 ci-dessous) ;
 *   - déclarer LUI-MÊME la variable de requête que sa règle introduit, par
 *     add_filter( 'query_vars', … ). Le chargeur n'en déclare aucune, ni ne devine laquelle ;
 *   - préfixer « mtb_ » ce qu'il introduit — variable de requête, nom de permastructure, nom de
 *     terminaison — au titre du nommage du contrat #1 §6, et non parce que l'empreinte
 *     l'exigerait : elle ne l'exige pas.
 *
 * À LA CHARGE DU CHARGEUR
 *   - calculer l'empreinte à « init » 99, la comparer, appeler flush_rewrite_rules( false ) une
 *     seule fois quand la moitié « réécriture » a changé, et journaliser ce changement sous
 *     WP_DEBUG ;
 *   - déclencher « mtb_core_mise_a_jour » quand, et seulement quand, la moitié « identité »
 *     (version, types, taxonomies) a changé.
 *   Rien d'autre. Le chargeur ne pose aucune règle et n'en corrige aucune.
 *
 * ANGLES MORTS ASSUMÉS — nommés ici pour qu'aucune chaîne future ne les redécouvre à ses frais.
 *
 *   1. UN FILTRE DE SORTIE N'EST PAS VU. Un module qui modifierait les règles par
 *      « rewrite_rules_array », « post_rewrite_rules » ou « <nom>_rewrite_rules » laisse toutes
 *      les entrées observées identiques : l'empreinte ne bouge pas, la modification ne prend
 *      jamais effet d'elle-même. Ce chemin est à éviter ; s'il devenait nécessaire, il demande
 *      un amendement écrit au contrat, pas un contournement.
 *
 *   2. UN ENREGISTREMENT CONDITIONNEL FAIT BATTRE L'EMPREINTE. Une règle posée sur les seules
 *      requêtes d'administration (ou l'inverse) donne deux empreintes qui alternent : une
 *      régénération à chaque requête, indéfiniment. Le coût est en lenteur, jamais en données —
 *      « mtb_core_mise_a_jour » ne dépend que de la moitié « identité » et ne peut pas être
 *      rejoué par un battement de règles. Sous WP_DEBUG, le battement se voit à l'œil nu : la
 *      ligne « règles de réécriture régénérées » se répète à chaque rechargement.
 *
 *   3. SANS STRUCTURE DE PERMALIENS, LA MOITIÉ « RÉÉCRITURE » N'EST PAS CALCULÉE. WordPress ne
 *      fabrique alors aucune règle, et normalise différemment les arguments de réécriture selon
 *      le contexte : la garde « is_admin() || get_option( 'permalink_structure' ) » de
 *      class-wp-post-type.php:641 et class-wp-taxonomy.php:385 (WordPress 6.9) ferait battre
 *      l'empreinte d'elle-même, sans qu'aucun module ait rien fait de travers. La moitié
 *      « identité », elle, reste calculée en toutes circonstances.
 *
 *   4. L'EMPREINTE OBSERVE DES ENTRÉES, PAS UN RÉSULTAT. Elle n'affirme pas que les URL servies
 *      sont les bonnes : elle affirme que les règles ont été régénérées après le dernier
 *      changement connu des entrées. La vérification qu'une URL répond reste manuelle.
 *
 * CE QUE CETTE EMPREINTE A RÉPARÉ, ET QU'IL NE FAUT PAS DÉFAIRE : avant #27, elle n'observait
 * que les NOMS des types et taxonomies. Changer « 'slug' => 'portees' » en autre chose ne
 * changeait donc rien — le nom « mtb_portee » ne bougeant pas, la nouvelle adresse ne prenait
 * jamais effet et l'ancienne continuait de répondre. Pas un 404 visible : un site qui a l'air
 * de marcher avec les mauvaises URL.
 */

/**
 * Découvre et inclut les modules, puis maintient l'empreinte de version à jour.
 *
 * Classe entièrement statique et non instanciable : elle n'a aucun état à porter au-delà du
 * drapeau de chargement, et l'extension n'expose aucun objet dans l'espace global.
 */
final class Loader {

	/**
	 * Groupes parcourus, dans cet ordre.
	 *
	 * Liste close : aucun filtre ne permet d'y ajouter un groupe (contrat §2 et §5).
	 *
	 * Note de portabilité : la constante n'est volontairement pas typée
	 * ( « public const array » date de PHP 8.3, l'extension vise PHP 8.1 ).
	 *
	 * @var string[]
	 */
	public const GROUPES = array(
		'content',
		'fields',
		'query',
		'blocks',
		'admin',
		'migration',
	);

	/**
	 * Drapeau de chargement, contre un double appel de charger().
	 *
	 * @var bool
	 */
	private static bool $deja_charge = false;

	/**
	 * Classe purement statique : aucune instance n'a de sens.
	 */
	private function __construct() {}

	/**
	 * Point d'entrée unique de l'extension.
	 */
	public static function charger(): void {
		if ( true === self::$deja_charge ) {
			return;
		}

		self::$deja_charge = true;

		foreach ( self::GROUPES as $groupe ) {
			self::charger_groupe( $groupe );
		}

		/*
		 * Priorité 99 : après les types de contenu (init 10) et les blocs (init 20), pour que
		 * l'empreinte reflète l'état réel des enregistrements. C'est ce qui remplace
		 * register_activation_hook et fait fonctionner un dépôt FTP sans réactivation.
		 */
		add_action( 'init', array( self::class, 'synchroniser_version' ), 99 );
	}

	/**
	 * Parcourt un groupe et inclut le bootstrap.php de chacun de ses modules.
	 *
	 * @param string $groupe Nom du groupe, l'un de self::GROUPES.
	 */
	private static function charger_groupe( string $groupe ): void {
		$dossier_groupe = __DIR__ . '/' . $groupe;

		if ( ! is_dir( $dossier_groupe ) ) {
			return;
		}

		/*
		 * scandir() et non glob( '*'.'/bootstrap.php' ) : glob() ne distingue pas « aucun module »
		 * de « un module sans bootstrap.php », or la note en WP_DEBUG sur cette absence est une
		 * exigence du contrat. scandir() fait les deux en une passe, trie par ordre ascendant
		 * (donc reproductible) et n'est pas désactivé sur les hébergements mutualisés.
		 */
		$entrees = scandir( $dossier_groupe );

		if ( false === $entrees ) {
			return;
		}

		foreach ( $entrees as $entree ) {
			$initiale = substr( $entree, 0, 1 );

			// « . » couvre « . », « .. », « .gitkeep » et tout dossier masqué ; « _ » désactive un module.
			if ( '.' === $initiale || '_' === $initiale ) {
				continue;
			}

			$dossier_module = $dossier_groupe . '/' . $entree;

			if ( ! is_dir( $dossier_module ) ) {
				continue;
			}

			$identifiant = $groupe . '/' . $entree;
			$bootstrap   = $dossier_module . '/bootstrap.php';

			if ( is_readable( $bootstrap ) ) {
				self::inclure_module( $bootstrap, $identifiant );

				continue;
			}

			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				self::journaliser( sprintf( 'module « %s » ignoré : aucun bootstrap.php lisible dans ce dossier.', $identifiant ) );
			}
		}
	}

	/**
	 * Inclut le bootstrap.php d'un module en isolant les exceptions levées à l'inclusion.
	 *
	 * Une erreur de syntaxe dans le module n'est pas rattrapable ici : voir la note du haut de
	 * fichier sur la portée réelle de ce try/catch.
	 *
	 * @param string $chemin      Chemin absolu du bootstrap.php.
	 * @param string $identifiant Identifiant lisible du module, « groupe/module ».
	 */
	private static function inclure_module( string $chemin, string $identifiant ): void {
		try {
			require_once $chemin;
		} catch ( \Throwable $e ) {
			/*
			 * En développement, relancer plutôt que journaliser : le développeur doit voir la
			 * trace réelle et le fichier fautif, pas une ligne résumée dans un journal.
			 * En production, un module défaillant ne doit pas emporter le site avec lui.
			 */
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				throw $e;
			}

			self::journaliser( sprintf( 'module « %s » non chargé : %s', $identifiant, $e->getMessage() ) );
		}
	}

	/**
	 * Compare l'empreinte stockée à l'état réel, et déclenche ce que chaque moitié commande.
	 *
	 * Deux comparaisons indépendantes, une seule option. La moitié « identité » (version, types,
	 * taxonomies) déclenche « mtb_core_mise_a_jour » ; la moitié « réécriture » déclenche
	 * flush_rewrite_rules( false ). Les séparer rend structurellement impossible qu'un battement
	 * de règles rejoue une migration de données — panne bien pire qu'un flush en trop.
	 *
	 * Aucun contrôle de capacité, aucun nonce, volontairement : la routine doit tourner sur la
	 * première requête venue après un dépôt FTP, y compris celle d'un visiteur anonyme, sans quoi
	 * les URL des portées répondraient 404 jusqu'à la prochaine visite d'un administrateur. Elle
	 * n'écrit aucune donnée d'origine utilisateur : sa valeur dérive d'une constante du code et de
	 * l'état d'enregistrement en mémoire. Une capacité la rendrait inopérante ; un nonce n'a pas
	 * de sens hors formulaire. La règle « nonce sur toute écriture » de CLAUDE.md vise les
	 * écritures issues d'une requête utilisateur — ce n'en est pas une.
	 *
	 * Concurrence : deux requêtes simultanées après un déploiement peuvent l'exécuter en même
	 * temps. L'opération est idempotente et le coût se limite à un flush en double, une seule fois
	 * dans la vie d'une version. Un verrou par transient coûterait plus cher que le problème.
	 */
	public static function synchroniser_version(): void {
		// Pendant « wp core install », la base n'est pas prête : aucune écriture d'option parasite.
		if ( wp_installing() ) {
			return;
		}

		$stockee  = get_option( 'mtb_core_empreinte' );
		$ancienne = is_array( $stockee ) ? $stockee : null;

		$identite   = self::empreinte_identite();
		$reecriture = self::empreinte_reecriture();

		$nouvelle               = $identite;
		$nouvelle['reecriture'] = $reecriture;

		$identite_ancienne = self::identite_stockee( $ancienne );

		// L'empreinte d'avant #27 n'a pas de clé « reecriture » : le repli array() vaut « jamais décrite ».
		$reecriture_ancienne = is_array( $ancienne['reecriture'] ?? null ) ? $ancienne['reecriture'] : array();

		if ( $identite_ancienne !== $identite ) {
			/**
			 * Se déclenche une fois quand la version ou la liste des contenus enregistrés change.
			 *
			 * Seul point d'accroche pour une migration de données d'une issue future. Depuis #27,
			 * il ne se déclenche PAS pour un changement d'URL seul — slug, archive, règle.
			 *
			 * @param array|null $ancienne Empreinte précédente, null à la toute première exécution.
			 * @param array      $nouvelle Empreinte qui vient d'être calculée, ses deux moitiés.
			 */
			do_action( 'mtb_core_mise_a_jour', $ancienne, $nouvelle );

			/*
			 * La moitié « réécriture » stockée est reconduite telle quelle : elle décrit l'état
			 * pour lequel « rewrite_rules » a été régénérée la dernière fois, et la régénération
			 * de cette requête-ci n'a pas encore eu lieu. L'écrire ici affirmerait une
			 * synchronisation qui n'est pas faite.
			 */
			$identite['reecriture'] = $reecriture_ancienne;

			update_option( 'mtb_core_empreinte', $identite, true );
		}

		if ( $reecriture_ancienne === $reecriture ) {
			return;
		}

		self::journaliser_changement( $reecriture_ancienne, $reecriture );

		/*
		 * false, jamais true : true réécrit .htaccess, ce qui suppose un système de fichiers
		 * accessible en écriture — fausse hypothèse sur un mutualisé durci.
		 */
		flush_rewrite_rules( false );

		/*
		 * WP_Rewrite::flush_rules() se re-programme sur « wp_loaded » tant que
		 * did_action( 'wp_loaded' ) est faux (class-wp-rewrite.php:1873-1881, WordPress 6.9) : à
		 * « init » 99 il l'est, donc la régénération n'a pas encore eu lieu. La moitié
		 * « réécriture » n'est écrite qu'après, en priorité 20, une fois l'effet réellement
		 * produit. La fermeture porte la valeur comparée ci-dessus plutôt qu'une valeur recalculée
		 * plus tard : c'est bien l'état pour lequel le flush a été demandé qui est consigné.
		 */
		add_action(
			'wp_loaded',
			static function () use ( $nouvelle ): void {
				update_option( 'mtb_core_empreinte', $nouvelle, true );
			},
			20
		);
	}

	/**
	 * Moitié « identité » de l'empreinte : ce dont un changement peut commander une migration.
	 *
	 * Calculée en toutes circonstances, sans garde : une migration de données ne doit jamais
	 * dépendre d'un réglage de permaliens.
	 *
	 * @return array Version de l'extension, types et taxonomies « mtb_ » enregistrés, triés.
	 */
	private static function empreinte_identite(): array {
		return array(
			'version'    => MTB_CORE_VERSION,
			'types'      => self::noms_mtb( array_keys( get_post_types() ) ),
			'taxonomies' => self::noms_mtb( array_keys( get_taxonomies() ) ),
		);
	}

	/**
	 * Moitié « réécriture » de l'empreinte : les six collections d'entrée du cœur.
	 *
	 * Ce sont exactement les tableaux que WP_Rewrite::rewrite_rules() lit pour fabriquer l'option
	 * « rewrite_rules ». Les observer plutôt que de relire « slug », « with_front »,
	 * « has_archive », « feeds », « pages », « ep_mask », « query_var » et « hierarchical » évite
	 * de re-dériver ce que le cœur dérive : ces arguments y sont déjà, sous leur forme utile, et
	 * rien n'est à tenir en phase avec une future version de WordPress. Aucun filtrage sur
	 * « mtb_ » : une règle de cible « index.php?pagename=… » doit être vue comme les autres.
	 *
	 * Le tri de chaque sous-tableau n'est pas cosmétique : sans lui, l'ordre d'enregistrement
	 * suffirait à faire battre l'empreinte d'une requête à l'autre.
	 *
	 * @return array Les six collections triées, ou array() quand il n'y a rien à décrire.
	 */
	private static function empreinte_reecriture(): array {
		/*
		 * La garde du cœur (class-wp-post-type.php:641, class-wp-taxonomy.php:385) amputée de son
		 * « is_admin() || » : c'est ce terme-là qui fait que « rewrite » vaut le tableau brut sur
		 * une requête publique et le tableau normalisé à cinq clés en administration. Sans
		 * structure de permaliens, WordPress ne fabrique aucune règle : il n'y a rien à décrire,
		 * et le décrire ferait battre l'empreinte à chaque alternance de contexte.
		 */
		if ( '' === (string) get_option( 'permalink_structure', '' ) ) {
			return array();
		}

		global $wp_rewrite;

		// Cas d'absence explicite : hors d'une requête WordPress complète, il n'y a rien à décrire.
		if ( ! $wp_rewrite instanceof \WP_Rewrite ) {
			return array();
		}

		$permastructs = $wp_rewrite->extra_permastructs;
		$regles_haut  = $wp_rewrite->extra_rules_top;
		$regles_bas   = $wp_rewrite->extra_rules;
		$regles_hors  = $wp_rewrite->non_wp_rules;

		// Repli : sort() serait fatal si WP_Rewrite n'avait pas initialisé « endpoints », à init 99 et hors du try/catch.
		$terminaisons = is_array( $wp_rewrite->endpoints ) ? $wp_rewrite->endpoints : array();

		$etiquettes = array();

		foreach ( $wp_rewrite->rewritecode as $rang => $code ) {
			$etiquettes[ (string) $code ] = array(
				(string) ( $wp_rewrite->rewritereplace[ $rang ] ?? '' ),
				(string) ( $wp_rewrite->queryreplace[ $rang ] ?? '' ),
			);
		}

		ksort( $permastructs );
		ksort( $etiquettes );
		ksort( $regles_haut );
		ksort( $regles_bas );
		ksort( $regles_hors );
		sort( $terminaisons );

		return array(
			'permastructs' => $permastructs,
			'etiquettes'   => $etiquettes,
			'regles_haut'  => $regles_haut,
			'regles_bas'   => $regles_bas,
			'regles_hors'  => $regles_hors,
			'terminaisons' => $terminaisons,
		);
	}

	/**
	 * Relit la moitié « identité » de l'empreinte stockée, quelle qu'en soit la forme.
	 *
	 * Repli clé par clé, sans version de schéma ni code de migration : l'empreinte d'avant #27
	 * porte déjà les trois bonnes clés, et une option corrompue se compare simplement comme
	 * différente.
	 *
	 * @param array|null $stockee Option « mtb_core_empreinte » telle que lue, null si absente ou illisible.
	 *
	 * @return array|null Les trois clés d'identité, null quand rien n'est stocké.
	 */
	private static function identite_stockee( ?array $stockee ): ?array {
		if ( null === $stockee ) {
			return null;
		}

		return array(
			'version'    => is_string( $stockee['version'] ?? null ) ? $stockee['version'] : '',
			'types'      => is_array( $stockee['types'] ?? null ) ? $stockee['types'] : array(),
			'taxonomies' => is_array( $stockee['taxonomies'] ?? null ) ? $stockee['taxonomies'] : array(),
		);
	}

	/**
	 * Journalise, sous WP_DEBUG, le changement de la moitié « réécriture ».
	 *
	 * Une ligne positive plutôt qu'une alarme négative : en régime normal elle paraît une fois par
	 * déploiement, et elle sert d'accusé de réception à la chaîne qui vient de déposer un module
	 * — « j'ai rechargé, le chargeur me dit regles_haut +1 ». Sous battement, c'est-à-dire quand un
	 * module enregistre sa règle sous condition de contexte, elle se répète à chaque rechargement,
	 * ce qui se voit à l'œil nu sans rien stocker ni écrire de plus.
	 *
	 * Trois états par collection, et le troisième n'est pas un raffinement : « = » identique,
	 * « +n » / « -n » entrées gagnées ou perdues, « modifiée » même nombre d'entrées mais contenu
	 * différent. Sans ce dernier, un changement de slug — le défaut même que #27 répare — se
	 * journaliserait « tout = », c'est-à-dire une ligne annonçant une régénération sans rien qui
	 * l'explique.
	 *
	 * Aucun compteur, aucun horodatage : ils s'écriraient à chaque changement, donc une écriture
	 * d'option de plus par requête sous battement — le détecteur aggraverait ce qu'il mesure.
	 *
	 * Résidu assumé : en production WP_DEBUG est faux, un battement serait donc silencieux. Il
	 * coûte un update_option et un flush par requête — mesurable en lenteur, jamais en données.
	 *
	 * @param array $ancienne Moitié « réécriture » précédente, array() si jamais décrite.
	 * @param array $nouvelle Moitié « réécriture » qui vient d'être calculée.
	 */
	private static function journaliser_changement( array $ancienne, array $nouvelle ): void {
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return;
		}

		$collections = array( 'permastructs', 'etiquettes', 'regles_haut', 'regles_bas', 'regles_hors', 'terminaisons' );
		$deltas      = array();

		foreach ( $collections as $collection ) {
			$avant = is_array( $ancienne[ $collection ] ?? null ) ? $ancienne[ $collection ] : array();
			$apres = is_array( $nouvelle[ $collection ] ?? null ) ? $nouvelle[ $collection ] : array();
			$ecart = count( $apres ) - count( $avant );

			if ( 0 !== $ecart ) {
				$deltas[] = $collection . ' ' . sprintf( '%+d', $ecart );

				continue;
			}

			$deltas[] = $collection . ( $avant === $apres ? ' =' : ' modifiée' );
		}

		self::journaliser( 'règles de réécriture régénérées — ' . implode( ', ', $deltas ) );
	}

	/**
	 * Filtre une liste de noms enregistrés sur le préfixe « mtb_ » et la trie.
	 *
	 * Le tri est essentiel : sans lui, l'ordre d'enregistrement suffirait à faire varier
	 * l'empreinte d'une requête à l'autre et les règles de réécriture seraient régénérées en
	 * boucle.
	 *
	 * @param string[] $noms Noms de types de contenu ou de taxonomies enregistrés.
	 *
	 * @return string[] Noms préfixés « mtb_ », triés, réindexés.
	 */
	private static function noms_mtb( array $noms ): array {
		$retenus = array_filter(
			$noms,
			// Sans cast, un nom entièrement numérique arriverait en int : TypeError fatal, et init 99 est hors du try/catch.
			static function ( $nom ): bool {
				return 0 === strpos( (string) $nom, 'mtb_' );
			}
		);

		sort( $retenus );

		return $retenus;
	}

	/**
	 * Unique point d'appel à error_log() de l'extension (contrat §12).
	 *
	 * @param string $message Message déjà rédigé, sans préfixe.
	 */
	private static function journaliser( string $message ): void {
		/*
		 * Le journal du serveur est le seul canal disponible ici : le chargeur s'exécute avant
		 * toute interface d'administration, et écrire à l'écran romprait l'exigence « aucune
		 * sortie parasite » — WP-CLI traite la moindre sortie comme un échec d'activation.
		 */
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( '[mtb-core] ' . $message );
	}
}
