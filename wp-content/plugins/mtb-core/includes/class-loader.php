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
 * Aucune issue ne modifie jamais « mtb-core.php » ni ce fichier.
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
 * WordPress, et par eux seuls. La priorité init 99 est réservée au chargeur, et aucun module
 * n'appelle flush_rewrite_rules().
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
	 * Régénère les règles de réécriture quand la version ou la liste des contenus a changé.
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

		$nouvelle = array(
			'version'    => MTB_CORE_VERSION,
			'types'      => self::noms_mtb( array_keys( get_post_types() ) ),
			'taxonomies' => self::noms_mtb( array_keys( get_taxonomies() ) ),
		);

		$stockee  = get_option( 'mtb_core_empreinte' );
		$ancienne = is_array( $stockee ) ? $stockee : null;

		if ( $ancienne === $nouvelle ) {
			return;
		}

		/**
		 * Se déclenche une fois quand la version ou la liste des contenus enregistrés change.
		 *
		 * Seul point d'accroche pour une migration de données d'une issue future.
		 *
		 * @param array|null $ancienne Empreinte précédente, null à la toute première exécution.
		 * @param array      $nouvelle Empreinte qui vient d'être calculée.
		 */
		do_action( 'mtb_core_mise_a_jour', $ancienne, $nouvelle );

		/*
		 * false, jamais true : true réécrit .htaccess, ce qui suppose un système de fichiers
		 * accessible en écriture — fausse hypothèse sur un mutualisé durci.
		 */
		flush_rewrite_rules( false );

		update_option( 'mtb_core_empreinte', $nouvelle, true );
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
