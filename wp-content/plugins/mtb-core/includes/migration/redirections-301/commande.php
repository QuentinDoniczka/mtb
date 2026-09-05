<?php
/**
 * Commande « wp mtb verifier-redirections » : contrôle de la carte contre son référentiel.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\Redirections301;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * CETTE COMMANDE N'ÉCRIT RIEN — ni option, ni contenu, ni méta. C'est la borne 1 de l'amendement au
 * §2 du contrat #1 : un module de « migration/ » qui accroche un hook de front est en lecture
 * seule. Elle lit le disque et la base, jamais le réseau (D6).
 *
 * CE QU'ELLE PROUVE, ET CE QU'ELLE NE PROUVE PAS. Elle dit que la table est cohérente avec le
 * référentiel et que chaque cible se résout aujourd'hui. Elle ne dit RIEN de ce que le serveur
 * répond : cela se mesure au « curl », et les deux contrôles ne se présentent jamais l'un pour
 * l'autre.
 *
 * ELLE LIT « docs/migration/source/sitemap.xml », ET C'EST LA SEULE LECTURE DE « docs/ » DE TOUT CE
 * MODULE. L'interdit du contrat #24 §13 vise l'EXÉCUTION — le service 301, le filtre d'ancres, le
 * rendu d'une page —, jamais un outil de recette joué à la main depuis l'arbre du dépôt. Le
 * précédent est « migration/portees-chiens », dont la commande lit la même archive par le même
 * calcul de chemin. Le service, lui, ne lit aucun fichier : sa table est en PHP.
 */

/**
 * Condensé SHA-256 attendu du référentiel, calculé sur son contenu à fins de ligne « \n ».
 *
 * POURQUOI LES DEUX FORMES SONT ACCEPTÉES. Le dépôt tourne en « core.autocrlf=true » sans
 * « .gitattributes » (dette T-#21-m, documentée dans « compose.yaml ») : un rendu Windows écrit
 * « sitemap.xml » en CRLF sur le disque, alors que l'objet versionné est en LF. Le même fichier,
 * inchangé, a donc deux condensés selon la machine. Comparer la seule forme brute ferait échouer
 * l'étape 0 sur tout poste Windows et réussir sur Linux — un contrôle qui dépend du système de
 * fichiers ne mesure plus rien. On compare donc la forme brute PUIS la forme normalisée en « \n »,
 * et la commande dit laquelle a répondu.
 */
const CONDENSE_ATTENDU = 'bb78eebcd0fa3d8f3b739b6fad9df1ddf49b6abcd49da033d3f78f76cc09cd1e';

/**
 * Nombre de contenus attendus porteurs de la clé « _mtb_robots_source ».
 */
const CONTENUS_NOINDEX_ATTENDUS = 5;

/**
 * Exécute les huit étapes de vérification.
 *
 * @param array<int, string>   $arguments Arguments positionnels ; aucun n'est attendu.
 * @param array<string, mixed> $options   Options nommées de la commande.
 */
function verifier( array $arguments, array $options ): void {
	unset( $arguments );

	$echecs = array();

	$locs = etape_0_referentiel( $options, $echecs );

	etape_1_et_2_correspondance( $locs, $echecs );
	etape_3_et_4_cibles( $echecs );
	etape_5_ancres( $echecs );
	etape_6_noindex( $echecs );
	etape_7_prefixe();

	if ( array() !== $echecs ) {
		\WP_CLI::error( sprintf( '%d contrôle(s) en échec — voir les lignes « Erreur » ci-dessus.', count( $echecs ) ) );
	}

	\WP_CLI::success( 'Carte des 52 adresses vérifiée : référentiel, correspondance, cibles, boucles, ancres et contenus non indexés.' );
}

/**
 * Étape 0 — le référentiel n'a pas bougé, et on en extrait les 52 adresses.
 *
 * @param array<string, mixed> $options Options de la commande.
 * @param array<int, string>   $echecs  Liste des échecs, complétée sur place.
 *
 * @return array<int, string> Chemins normalisés des « <loc> », tableau vide en cas d'échec.
 */
function etape_0_referentiel( array $options, array &$echecs ): array {
	$fichier = isset( $options['sitemap'] ) && is_string( $options['sitemap'] ) && '' !== $options['sitemap']
		? $options['sitemap']
		: dirname( __DIR__, 6 ) . '/docs/migration/source/sitemap.xml';

	\WP_CLI::log( '── Étape 0 — référentiel : ' . $fichier );

	if ( ! is_file( $fichier ) || ! is_readable( $fichier ) ) {
		$echecs[] = 'referentiel_absent';

		\WP_CLI::warning( 'Erreur : référentiel introuvable ou illisible. Cette commande se joue depuis l\'arbre du dépôt, où « docs/ » existe ; « docs/ » n\'est pas déployé en production.' );

		return array();
	}

	// Lecture d'un fichier local : aucune requête sortante, aucun flux distant (D6).
	$brut = file_get_contents( $fichier ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Fichier local du dépôt, jamais une adresse réseau.

	if ( ! is_string( $brut ) ) {
		$echecs[] = 'referentiel_illisible';

		\WP_CLI::warning( 'Erreur : le référentiel n\'a pas pu être lu.' );

		return array();
	}

	$condense_brut = hash( 'sha256', $brut );
	$condense_lf   = hash( 'sha256', str_replace( "\r\n", "\n", $brut ) );

	if ( CONDENSE_ATTENDU === $condense_brut ) {
		\WP_CLI::log( '   condensé SHA-256 conforme, sur le fichier tel quel : ' . $condense_brut );
	} elseif ( CONDENSE_ATTENDU === $condense_lf ) {
		\WP_CLI::log( '   condensé SHA-256 conforme après normalisation des fins de ligne en « \n » : ' . $condense_lf );
		\WP_CLI::log( '   (le fichier sur disque est en CRLF ; brut = ' . $condense_brut . ')' );
	} else {
		$echecs[] = 'referentiel_modifie';

		\WP_CLI::warning( sprintf( 'Erreur : le référentiel a bougé. Attendu %s, obtenu %s (brut) et %s (fins de ligne normalisées). La mesure ne veut plus rien dire tant que ce point n\'est pas tranché.', CONDENSE_ATTENDU, $condense_brut, $condense_lf ) );
	}

	$adresses = array();

	if ( preg_match_all( '#<loc>\s*(.*?)\s*</loc>#is', $brut, $trouvees ) ) {
		foreach ( $trouvees[1] as $adresse ) {
			$adresses[] = normaliser_chemin( html_entity_decode( (string) $adresse, ENT_QUOTES, 'UTF-8' ) );
		}
	}

	\WP_CLI::log( sprintf( '   %d adresses lues au référentiel.', count( $adresses ) ) );

	return $adresses;
}

/**
 * Étapes 1 et 2 — chaque adresse du référentiel est une clé, chaque clé est une adresse.
 *
 * @param array<int, string> $locs   Chemins normalisés du référentiel.
 * @param array<int, string> $echecs Liste des échecs, complétée sur place.
 */
function etape_1_et_2_correspondance( array $locs, array &$echecs ): void {
	$carte = carte();

	\WP_CLI::log( sprintf( '── Étapes 1 et 2 — correspondance : %d adresses au référentiel, %d clés en carte.', count( $locs ), count( $carte ) ) );

	foreach ( $locs as $chemin ) {
		if ( '' === $chemin ) {
			$echecs[] = 'loc_illisible';

			\WP_CLI::warning( 'Erreur (étape 1) : une adresse du référentiel n\'a pas pu être normalisée.' );

			continue;
		}

		if ( ! isset( $carte[ $chemin ] ) ) {
			$echecs[] = 'loc_hors_carte:' . $chemin;

			\WP_CLI::warning( sprintf( 'Erreur (étape 1) : « %s » est au référentiel et absente de la carte.', $chemin ) );
		}
	}

	$connues = array_flip( $locs );

	foreach ( array_keys( $carte ) as $chemin ) {
		if ( ! isset( $connues[ $chemin ] ) ) {
			$echecs[] = 'cle_hors_referentiel:' . $chemin;

			\WP_CLI::warning( sprintf( 'Erreur (étape 2) : « %s » est en carte et absente du référentiel.', $chemin ) );
		}
	}
}

/**
 * Étapes 3, 3 bis et 4 — chaque cible de verdict « 301 » se résout, sans boucle.
 *
 * @param array<int, string> $echecs Liste des échecs, complétée sur place.
 */
function etape_3_et_4_cibles( array &$echecs ): void {
	\WP_CLI::log( '── Étapes 3 et 4 — résolution des cibles et anti-boucle.' );

	$resolues = 0;
	$replis   = 0;

	foreach ( carte() as $chemin => $entree ) {
		if ( '301' !== $entree['verdict'] ) {
			continue;
		}

		$identite = is_array( $entree['cible'] ) ? $entree['cible'] : array();
		$nom      = sprintf( '%s « %s »', (string) ( $identite[0] ?? '?' ), (string) ( $identite[1] ?? '?' ) );

		$resolution = resoudre_cible( $identite );
		$cible      = is_string( $resolution['url'] ) ? $resolution['url'] : '';

		if ( '' === $cible ) {
			$echecs[] = 'cible_non_resolue:' . $chemin;

			\WP_CLI::warning( sprintf( 'Erreur (étape 3) : « %s » → %s ne se résout en aucun contenu publié. Aucune redirection ne sera servie.', $chemin, $nom ) );

			continue;
		}

		++$resolues;

		if ( 'cible_par_repli' === $resolution['etat'] ) {
			++$replis;

			\WP_CLI::warning( sprintf( 'Avertissement (étape 3 bis) : « %s » → %s n\'a été obtenue que par le repli — la portée est probablement protégée par mot de passe. La 301 est servie.', $chemin, $nom ) );
		}

		if ( normaliser_chemin( $cible ) === $chemin ) {
			$echecs[] = 'boucle:' . $chemin;

			\WP_CLI::warning( sprintf( 'Erreur (étape 4) : « %s » se résout vers lui-même — boucle de redirection.', $chemin ) );
		}
	}

	\WP_CLI::log( sprintf( '   %d cibles résolues, dont %d par le repli.', $resolues, $replis ) );
}

/**
 * Étape 5 — chaque lien vers l'ancien domaine stocké en base est couvert par la carte.
 *
 * Requête directe et préparée plutôt que « WP_Query » : le cœur n'offre aucun prédicat « le champ
 * principal contient cette chaîne ». « s => … » chercherait aussi dans le titre et l'extrait, avec
 * ses propres filtres, et rendrait un décompte qu'on ne pourrait pas défendre.
 *
 * Les révisions sont écartées : elles ne sont jamais rendues par « the_content » au visiteur. Le
 * relevé du 2026-09-05 en compte deux, sur la fiche « jango ».
 *
 * @param array<int, string> $echecs Liste des échecs, complétée sur place.
 */
function etape_5_ancres( array &$echecs ): void {
	global $wpdb;

	\WP_CLI::log( '── Étape 5 — liens internes vers l\'ancien domaine, stockés en base.' );

	$lignes = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Recherche dans le champ principal, sans équivalent WP_Query ; commande de recette jouée à la main.
		$wpdb->prepare(
			"SELECT ID, post_type, post_name, post_content FROM {$wpdb->posts} WHERE post_status = %s AND post_type <> %s AND post_content LIKE %s",
			'publish',
			'revision',
			'%' . $wpdb->esc_like( 'mtbrabant.com' ) . '%'
		)
	);

	$total   = 0;
	$cibles  = array();
	$formes  = formes_connues();
	$carte   = carte();
	$contenu = 0;

	foreach ( (array) $lignes as $ligne ) {
		++$contenu;

		if ( ! preg_match_all( '#href\s*=\s*(["\'])(.*?)\1#is', (string) $ligne->post_content, $trouvees ) ) {
			continue;
		}

		foreach ( $trouvees[2] as $href ) {
			$href = (string) $href;

			if ( false === strpos( $href, 'mtbrabant.com' ) ) {
				continue;
			}

			++$total;

			$chemin = normaliser_chemin( $href );
			$clef   = '';

			if ( isset( $carte[ $chemin ] ) ) {
				$clef = $chemin;
			} elseif ( isset( $formes[ $chemin ] ) ) {
				$clef = $formes[ $chemin ];
			}

			if ( '' === $clef || '301' !== $carte[ $clef ]['verdict'] ) {
				$echecs[] = 'ancre_non_couverte:' . $href;

				\WP_CLI::warning( sprintf( 'Erreur (étape 5) : le lien « %s » du contenu #%d (%s) n\'est couvert ni par une clé ni par une forme déclarée. Il ne sera pas réparé au rendu.', $href, (int) $ligne->ID, (string) $ligne->post_name ) );

				continue;
			}

			$cibles[ $clef ] = true;
		}
	}

	\WP_CLI::log( sprintf( '   %d liens dans %d contenus publiés, vers %d cibles distinctes.', $total, $contenu, count( $cibles ) ) );
}

/**
 * Étape 6 — le nombre de contenus porteurs de la clé « _mtb_robots_source ».
 *
 * @param array<int, string> $echecs Liste des échecs, complétée sur place.
 */
function etape_6_noindex( array &$echecs ): void {
	$requete = new \WP_Query(
		array(
			'post_type'              => 'any',
			'post_status'            => 'any',
			'posts_per_page'         => -1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'meta_query'             => array( // phpcs:ignore WordPress.DB.SlowMetaQuery.SlowDbQuery -- Commande de recette, jouée à la main, jamais sur une requête de front.
				array(
					'key'     => '_mtb_robots_source',
					'compare' => 'EXISTS',
				),
			),
		)
	);

	$nombre = count( $requete->posts );

	\WP_CLI::log( sprintf( '── Étape 6 — contenus portant « _mtb_robots_source » : %d (attendu %d).', $nombre, CONTENUS_NOINDEX_ATTENDUS ) );

	foreach ( $requete->posts as $identifiant ) {
		$contenu = get_post( (int) $identifiant );

		if ( $contenu instanceof \WP_Post ) {
			\WP_CLI::log( sprintf( '   « %s » — %s', $contenu->post_title, (string) get_permalink( $contenu ) ) );
		}
	}

	if ( CONTENUS_NOINDEX_ATTENDUS !== $nombre ) {
		$echecs[] = 'noindex_compte:' . $nombre;

		\WP_CLI::warning( sprintf( 'Erreur (étape 6) : %d contenus portent la clé, %d sont attendus. Un écart signifie qu\'un contenu de l\'ancien site manque en base, ou qu\'un contenu de plus la porte.', $nombre, CONTENUS_NOINDEX_ATTENDUS ) );
	}
}

/**
 * Étape 7 — le préfixe de site calculé et le premier chemin normalisé, imprimés sans jugement.
 *
 * Elle n'échoue jamais. Son seul rôle est qu'une installation en sous-dossier ne se plante pas en
 * silence : si le préfixe n'est pas celui qu'on croit, les 46 clés ne seront jamais atteintes, et
 * rien d'autre ne le dirait.
 */
function etape_7_prefixe(): void {
	$prefixe = prefixe_du_site();
	$clefs   = array_keys( carte() );

	\WP_CLI::log( '── Étape 7 — repères d\'installation.' );
	\WP_CLI::log( sprintf( '   adresse du site : %s', home_url( '/' ) ) );
	\WP_CLI::log( sprintf( '   préfixe de chemin calculé : « %s »%s', $prefixe, '' === $prefixe ? ' (site servi à la racine)' : '' ) );
	\WP_CLI::log( sprintf( '   première clé de la carte : « %s »', (string) ( $clefs[0] ?? '' ) ) );
	\WP_CLI::log( sprintf( '   normalisation d\'un exemple accentué : « %s »', normaliser_chemin( 'https://www.mtbrabant.com/bhpl/port%C3%A9e-m-2016/?utm=1' ) ) );
}
