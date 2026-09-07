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
 * référentiel, que chaque cible se résout aujourd'hui, et — depuis l'étape 6 bis — que la règle de
 * mise en sommeil est CHARGÉE, ACCROCHÉE, et qu'elle PRODUIT SON EFFET sur les listes que le cœur
 * fabrique en processus. Elle ne dit RIEN de ce que le serveur RÉPOND.
 *
 * TROIS NATURES DE CONTRÔLE, ET AUCUNE NE SE PRÉSENTE JAMAIS POUR UNE AUTRE. La doctrine en portait
 * deux ; l'issue #53 en ajoute une troisième, au milieu :
 *
 *   1. COHÉRENCE DE TABLE — ce que le dépôt déclare (étapes 0 à 5 et 7). Elle ne dépend d'aucune
 *      règle chargée.
 *   2. RÈGLE CHARGÉE ET PRODUISANT SON EFFET — l'étape 6 bis, en processus, sur les listes du cœur.
 *      Elle mesure un EFFET, jamais la présence d'une clé : une clé présente dont l'effet est absent
 *      la fait ÉCHOUER. Elle déclare elle-même, à chaque exécution, ce qu'elle NE mesure pas.
 *   3. CE QUE LE SERVEUR RÉPOND — le « curl », et les groupes B et R du protocole du §13 du contrat
 *      #52. Hors de cette commande, à jamais.
 *
 * LA 2 A ÉTÉ AJOUTÉE PARCE QUE LA 1 RESTAIT VERTE SANS RIEN AVOIR OBSERVÉ. L'étape 6 comptait la clé
 * « _mtb_robots_source » en base ; sur une base restaurée par copie SQL, où « convertir() » n'a
 * jamais couru, elle imprimait « 5 (attendu 5) » sur cinq contenus redevenus indexables. Elle se
 * taisait exactement le jour où elle aurait compté (dette T109). Un contrôle qui peut répondre « tout
 * va bien » sans avoir rien observé n'est pas un contrôle, c'est un décor.
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
 * Exécute les huit étapes de vérification, dont deux sous-étapes — 3 bis et 6 bis.
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

	\WP_CLI::success( 'Carte des 52 adresses vérifiée : référentiel, correspondance, cibles, boucles, ancres, état des contenus repris et effet mesuré sur le plan du site.' );
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
 * LA CLÉ DE LA « meta_query » N'EST PLUS RECOPIÉE, ET SON DÉRÉFÉRENCEMENT EST GARDÉ.
 * « migration/indexation-heritee » se désactive par le renommage documenté du dossier en
 * « _indexation-heritee » ; sa constante disparaît alors avec lui, et la lire serait une erreur
 * fatale. Le prix de la garde est dit ouvertement : quand le module manque, CETTE ÉTAPE CESSE DE
 * COMPTER au lieu d'imprimer sereinement « 5 (attendu 5) » alors que plus rien ne convertit. C'est un
 * gain, pas un coût — un vert sans convertisseur est le mensonge exact que l'étape 6 bis existe pour
 * fermer —, et le silence n'en est pas un : L'ÉTAPE DIT ELLE-MÊME QU'ELLE N'A PAS MESURÉ, parce
 * qu'une étape qui disparaît d'une séquence numérotée de 0 à 7 se lit comme une étape qui a passé ;
 * la sonde de l'étape 6 bis, juste en dessous, NOMME ensuite le symbole manquant et échoue.
 *
 * @param array<int, string> $echecs Liste des échecs, complétée sur place.
 */
function etape_6_noindex( array &$echecs ): void {
	$porteurs = array();

	if ( defined( 'MTB\\Core\\Migration\\IndexationHeritee\\CLE' ) ) {
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
						'key'     => \MTB\Core\Migration\IndexationHeritee\CLE,
						'compare' => 'EXISTS',
					),
				),
			)
		);

		foreach ( $requete->posts as $identifiant ) {
			$porteurs[] = (int) $identifiant;
		}

		$nombre = count( $porteurs );

		\WP_CLI::log( sprintf( '── Étape 6 — contenus portant « _mtb_robots_source » : %d (attendu %d).', $nombre, CONTENUS_NOINDEX_ATTENDUS ) );

		foreach ( $porteurs as $identifiant ) {
			$contenu = get_post( $identifiant );

			if ( $contenu instanceof \WP_Post ) {
				\WP_CLI::log( sprintf( '   « %s » — %s', $contenu->post_title, (string) get_permalink( $contenu ) ) );
			}
		}

		if ( CONTENUS_NOINDEX_ATTENDUS !== $nombre ) {
			$echecs[] = 'noindex_compte:' . $nombre;

			\WP_CLI::warning( sprintf( 'Erreur (étape 6) : %d contenus portent la clé, %d sont attendus. Un écart signifie qu\'un contenu de l\'ancien site manque en base, ou qu\'un contenu de plus la porte.', $nombre, CONTENUS_NOINDEX_ATTENDUS ) );
		}
	} else {
		// Ni « warning », ni échec : l'échec est porté une fois, par la sonde de l'étape 6 bis. Le
		// dédoubler ici ferait compter deux fois le même défaut.
		\WP_CLI::log( '── Étape 6 — NON MESURÉE : « migration/indexation-heritee » est absent, sa clé n\'est donc pas déclarée. Rien n\'est compté ici ; l\'étape 6 bis nomme le symbole manquant et échoue.' );
	}

	etape_6_bis_effet( $porteurs, $echecs );
}

/**
 * Nom pleinement qualifié du rappel dont l'étape 6 bis mesure l'effet.
 *
 * Il est écrit ici UNE FOIS, et lu partout ailleurs. C'est un NOM DE SYMBOLE, pas une valeur de
 * domaine : l'idiome de sonde Z1 exige un nom littéral, puisqu'il sert précisément à répondre quand
 * le module qui le déclare n'est pas là pour le fournir.
 */
const RAPPEL_SOMMEIL_PLAN_DU_SITE = 'MTB\\Core\\Query\\MiseEnSommeil\\exclure_du_plan_du_site';

/**
 * Étape 6 bis — la conversion a-t-elle produit son effet ?
 *
 * C'est la NATURE 2 des trois qu'énumère l'en-tête de ce fichier : une règle chargée dont on mesure
 * l'EFFET, jamais la présence d'une clé. Le motif de son existence est écrit là-haut, une fois — il
 * ne se recopie pas ici, une doctrine recopiée étant une doctrine qui divergera.
 *
 * CE QU'ELLE MESURE, ET CE QU'ELLE NE MESURE PAS — ET ELLE LE DÉCLARE ELLE-MÊME, À CHAQUE EXÉCUTION,
 * dans les deux lignes qu'elle imprime en tête. L'ÉTAT en base et l'EFFET sur le plan du site que le
 * cœur fabrique en processus : oui. La balise « robots » du HTML servi et la recherche interne du
 * site : NON — celles-là sont de la nature 3, hors de cette commande. Un témoin qui déclare son
 * propre périmètre est la seule forme de témoin qui ne redevienne pas une dette.
 *
 * DEUX MOITIÉS INDÉPENDANTES. L'ÉTAT en base est toujours mesuré ; l'EFFET ne l'est que sur les
 * contenus éligibles — publiés, non protégés, d'un type servi par le fournisseur « posts ». Un contenu
 * hors mesure d'effet reçoit sa ligne et son motif : une absence qu'on ne sait pas attribuer n'est pas
 * une preuve, et taire le motif la ferait passer pour telle.
 *
 * @param array<int, int>    $porteurs Identifiants des contenus portant le fait hérité.
 * @param array<int, string> $echecs   Liste des échecs, complétée sur place.
 */
function etape_6_bis_effet( array $porteurs, array &$echecs ): void {
	\WP_CLI::log( '── Étape 6 bis — la conversion a-t-elle produit son effet ?' );
	\WP_CLI::log( '   Périmètre : cette étape mesure l\'ÉTAT en base et l\'EFFET sur le plan du site que le cœur fabrique en processus. Elle ne mesure NI la balise « robots » du HTML servi, NI la recherche interne du site : celles-là se mesurent au « curl » et par les groupes B et R du protocole du §13 du contrat #52.' );
	\WP_CLI::log( '   Ce que ceci prouve : la règle est chargée, accrochée, et elle retire bien du plan du site les contenus convertis. Ce que ceci ne prouve pas : ce que le serveur répond à un visiteur.' );

	if ( ! sonde_des_modules( $echecs ) ) {
		return;
	}

	$population = population_convertible( $porteurs );

	\WP_CLI::log( sprintf( '   Population mesurée : %d contenu(s) portant le fait hérité ET dont « demande_noindex() » est vrai, sur %d porteur(s) de la clé.', count( $population ), count( $porteurs ) ) );

	foreach ( $population as $contenu ) {
		if ( 'absente' === $contenu['etat'] ) {
			$echecs[] = 'conversion_jamais_jouee:' . $contenu['id'];

			\WP_CLI::warning( sprintf( 'Erreur (étape 6 bis) : « %s » (#%d) porte le fait hérité et demande « noindex », mais AUCUNE valeur « en sommeil » n\'a jamais été écrite : « convertir() » n\'a pas couru sur cette base. Remède : ouvrir une page de l\'administration une seule fois — le rattrapage court sur « admin_init » —, puis rejouer cette commande. C\'est l\'état normal d\'une base restaurée par copie SQL : ce n\'est pas le témoin qui est cassé.', $contenu['titre'], $contenu['id'] ) );

			continue;
		}

		if ( 'indistinct' === $contenu['etat'] ) {
			\WP_CLI::warning( sprintf( 'Avertissement (étape 6 bis) : « %s » (#%d) porte une valeur « en sommeil » qui n\'est ni absente ni « 1 » — soit un réveil explicite décidé par l\'éleveuse, soit une valeur abîmée. CE TÉMOIN NE SAIT PAS LES SÉPARER et ne tranche pas. Valeur stockée, pour qu\'un humain tranche : %s.', $contenu['titre'], $contenu['id'], $contenu['octet'] ) );

			continue;
		}

		\WP_CLI::log( sprintf( '   « %s » (#%d) — état : en sommeil. Converti.', $contenu['titre'], $contenu['id'] ) );
	}

	$posts = plan_du_site_mesurable( $echecs );

	if ( ! $posts instanceof \WP_Sitemaps_Provider ) {
		return;
	}

	$sous_types = array();

	foreach ( array_keys( $posts->get_object_subtypes() ) as $sous_type ) {
		$sous_types[] = (string) $sous_type;
	}

	$servis    = array_flip( $sous_types );
	$eligibles = array();

	foreach ( $population as $contenu ) {
		if ( 'publish' !== $contenu['statut'] ) {
			\WP_CLI::log( sprintf( '   « %s » (#%d) — hors mesure d\'effet : non publié. Le plan du site ne liste que le contenu publié.', $contenu['titre'], $contenu['id'] ) );

			continue;
		}

		if ( $contenu['protege'] ) {
			\WP_CLI::log( sprintf( '   « %s » (#%d) — hors mesure d\'effet : protégé par mot de passe. « query/page-protegee » le retire du plan du site dans LES DEUX passes ; son absence ne prouve rien de notre règle.', $contenu['titre'], $contenu['id'] ) );

			continue;
		}

		if ( ! isset( $servis[ $contenu['type'] ] ) ) {
			\WP_CLI::log( sprintf( '   « %s » (#%d) — hors mesure d\'effet : son type « %s » n\'est pas servi par le fournisseur « posts » du plan du site.', $contenu['titre'], $contenu['id'], $contenu['type'] ) );

			continue;
		}

		$eligibles[] = $contenu;
	}

	/*
	 * SONDE DE RECETTE, EN PROCESSUS, RÉTABLIE — ET INTERDITE PARTOUT AILLEURS.
	 *
	 * Ce bloc RETIRE UN RAPPEL puis le REMET. C'est le seul geste du dépôt qui touche la table des
	 * filtres, et il est borné par quatre gardes, chacune motivée :
	 *
	 *   1. UN SEUL CROCHET, « wp_sitemaps_posts_query_args ». JAMAIS « wp_robots » : les contrats #23
	 *      et #24, reconduits par le §12 du contrat #52, interdisent nommément d'en retirer un rappel.
	 *      « On ne le retire qu'une seconde, pour mesurer » est le premier pas exact de cette faute —
	 *      la tentation est nommée ici pour être fermée, pas pour être décrite.
	 *   2. RETRAIT PAR NOM PLEINEMENT QUALIFIÉ, JAMAIS PAR CROCHET. « query/page-protegee » accroche un
	 *      HOMONYME au même crochet et à la même priorité ; un retrait par crochet emporterait les deux,
	 *      et la mesure attribuerait à notre règle un effet qui n'est pas le sien.
	 *   3. RÉTABLISSEMENT EN « finally », PUIS RÉ-ASSERTION IMPRIMÉE. Si « has_filter() » ne rend plus
	 *      la priorité, la commande ÉCHOUE DUREMENT : un témoin qui laisse le processus amputé ment au
	 *      suivant.
	 *   4. AUCUNE ÉCRITURE. La table des filtres est une structure de mémoire, propre à ce processus de
	 *      recette, qui ne sert aucun visiteur et se termine à la ligne suivante. Ni option, ni contenu,
	 *      ni méta, ni fichier : la borne 1 de l'amendement au §2 du contrat #1 reste entière.
	 *
	 * COPIER CE GESTE HORS D'UN OUTIL DE RECETTE EST INTERDIT. Sur une requête de front, il produirait
	 * exactement la panne que « query/mise-en-sommeil » existe pour empêcher : un contenu endormi de
	 * retour dans un index, sur une page qui répond 200 et un journal qui reste vide.
	 *
	 * L'ORDRE DANS LE SEAU DE PRIORITÉ CHANGE, ET C'EST SANS EFFET : le rappel rétabli passe après
	 * l'homonyme. La convention de cohabitation gelée aux contrats #23 et #24, restatée en tête de
	 * « query/mise-en-sommeil/bootstrap.php », le garantit — l'un mute « has_password », l'autre
	 * « post__not_in », aucun ne lit la clé de l'autre, le résultat est identique dans les deux ordres.
	 *
	 * LA LISTE DES ENDORMIS EST LA MÊME DANS LES DEUX PASSES, ET C'EST VÉRIFIÉ SUR LE CODE :
	 * « identifiants_en_sommeil() » mémoïse sa liste par processus (« etat.php »), sans invalidation ni
	 * crochet de purge. Seule la PRÉSENCE DU RAPPEL change d'une passe à l'autre — c'est ce qui rend la
	 * différence ATTRIBUABLE. La passe nominale court la première, pour que la mémoïsation se remplisse
	 * en régime normal.
	 */
	$priorite = (int) has_filter( 'wp_sitemaps_posts_query_args', RAPPEL_SOMMEIL_PLAN_DU_SITE );
	$avec     = chemins_du_fournisseur_posts( $posts, 'avec le rappel' );

	try {
		remove_filter( 'wp_sitemaps_posts_query_args', RAPPEL_SOMMEIL_PLAN_DU_SITE, $priorite );

		$sans = chemins_du_fournisseur_posts( $posts, 'sans le rappel' );
	} finally {
		add_filter( 'wp_sitemaps_posts_query_args', RAPPEL_SOMMEIL_PLAN_DU_SITE, $priorite, 1 );
	}

	$retabli = has_filter( 'wp_sitemaps_posts_query_args', RAPPEL_SOMMEIL_PLAN_DU_SITE );

	if ( false === $retabli ) {
		\WP_CLI::error( sprintf( 'Le rappel « %s » n\'a PAS pu être rétabli après la sonde de contraste. Le processus est amputé : aucune autre mesure de cette exécution ne veut plus rien dire.', RAPPEL_SOMMEIL_PLAN_DU_SITE ) );
	}

	\WP_CLI::log( sprintf( '   Rappel rétabli : « has_filter() » rend la priorité %d.', (int) $retabli ) );

	$attendues  = adresses_attendues( $sous_types );
	$difference = array_diff_key( $sans, $avec );

	\WP_CLI::log( sprintf( '   Contraste : %d adresse(s) présentes sans le rappel et absentes avec ; %d attendue(s).', count( $difference ), count( $attendues ) ) );

	foreach ( $attendues as $chemin => $identifiant ) {
		if ( isset( $difference[ $chemin ] ) ) {
			continue;
		}

		$echecs[] = 'effet_absent:' . $identifiant;

		\WP_CLI::warning( sprintf( 'Erreur (étape 6 bis) : « %s » (#%d) est en sommeil, publié et non protégé, et pourtant son adresse « %s » ne disparaît PAS du plan du site quand on retire le rappel. La clé est posée, l\'effet est absent.', (string) get_post_field( 'post_title', $identifiant ), $identifiant, $chemin ) );
	}

	foreach ( array_keys( $difference ) as $chemin ) {
		if ( isset( $attendues[ $chemin ] ) ) {
			continue;
		}

		$echecs[] = 'effet_hors_perimetre:' . $chemin;

		\WP_CLI::warning( sprintf( 'Erreur (étape 6 bis) : l\'adresse « %s » disparaît du plan du site quand le rappel agit, alors qu\'elle n\'appartient à aucun contenu en sommeil. Le rappel retire plus que ce qu\'il annonce.', $chemin ) );
	}

	foreach ( $eligibles as $contenu ) {
		if ( 'indistinct' !== $contenu['etat'] ) {
			continue;
		}

		if ( '' !== $contenu['adresse'] && isset( $avec[ $contenu['adresse'] ] ) ) {
			continue;
		}

		\WP_CLI::warning( sprintf( 'Avertissement (étape 6 bis) : « %s » (#%d) n\'est pas en sommeil, il est publié et non protégé, et pourtant son adresse est absente du plan du site. Ce témoin ne dit pas pourquoi : un autre rappel du même crochet peut en être la cause.', $contenu['titre'], $contenu['id'] ) );
	}
}

/**
 * Sonde d'existence — les symboles sont-ils là, et le rappel est-il accroché ?
 *
 * AUCUN « require_once » N'EST AJOUTÉ, ET C'EST UN ARBITRAGE, PAS UN OUBLI. Cette étape s'exécute au
 * dispatch de la commande, donc APRÈS le chargement de tous les modules : un « require_once »
 * n'apporterait rien qu'une erreur fatale le jour du renommage documenté en « _indexation-heritee »,
 * et ferait de surcroît dépendre « redirections-301 » de l'ordre de parcours des groupes du chargeur.
 *
 * DEUX ÉCHECS DISTINCTS, ET LA DISTINCTION EST LE CŒUR DE L'ISSUE. « temoin_indisponible » dit que le
 * MODULE EST ABSENT — le témoin ne peut pas mesurer, et ce n'est pas un verdict sur les contenus.
 * « rappel_non_accroche » dit que LA RÈGLE EST CHARGÉE ET N'AGIT PLUS. Les confondre rendrait le
 * témoin inutile dans les deux cas ; c'est pourquoi le second test ne court QUE SI le premier a tout
 * trouvé — sans cette garde, un module absent imprimerait « la règle est chargée », c'est-à-dire une
 * contrevérité.
 *
 * @param array<int, string> $echecs Liste des échecs, complétée sur place.
 *
 * @return bool Vrai seulement si tout est mesurable.
 */
function sonde_des_modules( array &$echecs ): bool {
	$fonctions = array(
		'MTB\\Core\\Migration\\IndexationHeritee\\demande_noindex',
		'MTB\\Core\\Query\\MiseEnSommeil\\est_en_sommeil',
		'MTB\\Core\\Query\\MiseEnSommeil\\identifiants_en_sommeil',
	);

	$constantes = array(
		'MTB\\Core\\Migration\\IndexationHeritee\\CLE',
		'MTB\\Core\\Query\\MiseEnSommeil\\CLE',
	);

	$absents = array();

	foreach ( $fonctions as $symbole ) {
		if ( ! function_exists( $symbole ) ) {
			$absents[] = $symbole;
		}
	}

	foreach ( $constantes as $symbole ) {
		if ( ! defined( $symbole ) ) {
			$absents[] = $symbole;
		}
	}

	foreach ( $absents as $symbole ) {
		$echecs[] = 'temoin_indisponible:' . $symbole;

		\WP_CLI::warning( sprintf( 'Erreur (étape 6 bis) : « %s » est introuvable. Le témoin NE PEUT PAS MESURER — le module concerné est probablement désactivé (dossier renommé avec un souligné initial). Ce n\'est pas un verdict sur les contenus.', $symbole ) );
	}

	if ( array() !== $absents ) {
		return false;
	}

	if ( false === has_filter( 'wp_sitemaps_posts_query_args', RAPPEL_SOMMEIL_PLAN_DU_SITE ) ) {
		$echecs[] = 'rappel_non_accroche';

		\WP_CLI::warning( sprintf( 'Erreur (étape 6 bis) : le rappel « %s » n\'est PAS accroché à « wp_sitemaps_posts_query_args ». La règle est chargée et n\'agit plus : tout contenu endormi est de retour au plan du site, et rien d\'autre ne le dirait.', RAPPEL_SOMMEIL_PLAN_DU_SITE ) );

		return false;
	}

	return true;
}

/**
 * Réduit les porteurs du fait hérité à la population que la conversion visait, et relève leur état.
 *
 * LA POPULATION EST « PORTE LE FAIT HÉRITÉ » ET « demande_noindex() EST VRAI », et cette borne ne
 * s'élargit jamais : c'est EXACTEMENT celle de « convertir_un() ». Conséquence garantie, et c'est le
 * but — une page ordinaire du site, sans le fait hérité, sans état de sommeil, « jamais réglée »,
 * n'entre jamais dans la population et NE PEUT JAMAIS ROUGIR. La borne est la clé héritée, pas l'état
 * de sommeil.
 *
 * LES VERDICTS PASSENT PAR « metadata_exists() » ET « est_en_sommeil() », JAMAIS PAR UNE LECTURE
 * BRUTE. Séparer « jamais réglé » de « réveillé explicitement » est tout ce que le troisième état
 * achète ; « get_post_meta() » rend la chaîne vide dans les deux cas et ne saurait pas les distinguer.
 *
 * @param array<int, int> $porteurs Identifiants des contenus portant le fait hérité.
 *
 * @return array<int, array{id: int, titre: string, adresse: string, type: string, statut: string, protege: bool, etat: 'absente'|'endormi'|'indistinct', octet: string}>
 */
function population_convertible( array $porteurs ): array {
	$population = array();

	foreach ( $porteurs as $identifiant ) {
		$identifiant = (int) $identifiant;
		$contenu     = get_post( $identifiant );

		if ( ! $contenu instanceof \WP_Post ) {
			continue;
		}

		if ( ! \MTB\Core\Migration\IndexationHeritee\demande_noindex( $identifiant ) ) {
			\WP_CLI::log( sprintf( '   « %s » (#%d) — porte le fait hérité SANS directive « noindex » : hors population, la conversion ne l\'a jamais visé.', $contenu->post_title, $identifiant ) );

			continue;
		}

		if ( ! metadata_exists( 'post', $identifiant, \MTB\Core\Query\MiseEnSommeil\CLE ) ) {
			$etat = 'absente';
		} elseif ( \MTB\Core\Query\MiseEnSommeil\est_en_sommeil( $identifiant ) ) {
			$etat = 'endormi';
		} else {
			$etat = 'indistinct';
		}

		$population[] = array(
			'id'      => $identifiant,
			'titre'   => (string) $contenu->post_title,
			'adresse' => normaliser_chemin( (string) get_permalink( $contenu ) ),
			'type'    => (string) $contenu->post_type,
			'statut'  => (string) $contenu->post_status,
			'protege' => '' !== (string) $contenu->post_password,
			'etat'    => $etat,
			'octet'   => 'indistinct' === $etat ? octet_stocke( $identifiant ) : '',
		);
	}

	return $population;
}

/**
 * Rend, pour l'affichage seul, la valeur d'état « en sommeil » telle qu'elle est stockée.
 *
 * LECTURE BRUTE DE DIAGNOSTIC — JAMAIS UN VERDICT. Cette fonction n'est appelée que dans la branche
 * « état indistinct », où le témoin déclare qu'il ne sait pas séparer un réveil explicite « 0 »,
 * décision de l'éleveuse, d'une valeur abîmée. Elle n'existe que pour donner à un humain de quoi
 * trancher.
 *
 * POURQUOI CE N'EST PAS LA LECTURE QUE LE §12 DU CONTRAT #52 INTERDIT. Le mal que ce §12 ferme est
 * qu'un contenu « se lise endormi à un endroit et visible à un autre ». Ici RIEN N'EST DÉRIVÉ : aucune
 * branche, aucune conséquence, aucun verdict — on affiche. Le verdict reste « est_en_sommeil() » et
 * « metadata_exists() », sans exception. Le jour où quelqu'un tirera une branche de ce retour, il aura
 * rouvert la faute que ce §12 ferme.
 *
 * LA GARDE « is_scalar() » EST OBLIGATOIRE, ET ELLE EST MESURÉE AILLEURS. « etat.php » documente qu'un
 * « (string) » sur une méta sérialisée lève « Array to string conversion », donc UNE LIGNE DE JOURNAL
 * PAR AFFICHAGE — et cette commande a pour propriété déclarée de ne rien journaliser. Un scalaire non
 * imprimable bascule en hexadécimal borné plutôt que de vomir un binaire dans un terminal.
 *
 * @param int $identifiant Identifiant du contenu.
 *
 * @return string Description bornée de la valeur stockée, toujours imprimable.
 */
function octet_stocke( int $identifiant ): string {
	$valeur = get_post_meta( $identifiant, \MTB\Core\Query\MiseEnSommeil\CLE, true );

	if ( ! is_scalar( $valeur ) ) {
		return sprintf( 'valeur non scalaire (%s)', gettype( $valeur ) );
	}

	$brut   = (string) $valeur;
	$taille = strlen( $brut );

	if ( '' !== $brut && ! ctype_print( $brut ) ) {
		return sprintf( 'valeur non imprimable (hex : %s, %d octets)', substr( bin2hex( $brut ), 0, 80 ), $taille );
	}

	if ( $taille > 40 ) {
		return sprintf( '« %s… » (%d octets)', substr( $brut, 0, 40 ), $taille );
	}

	return sprintf( '« %s » (%d octets)', $brut, $taille );
}

/**
 * Rend le fournisseur « posts » du plan du site, ou rien quand il n'y a rien à mesurer.
 *
 * TROIS FAITS DU CŒUR, MESURÉS DANS LE CONTENEUR LE 2026-09-08 SUR WORDPRESS 6.9, JAMAIS SUPPOSÉS
 * (dette T97 — aucun nombre sans sa recette). Chacun décide d'une ligne de ce module :
 *
 *   1. « WP_Sitemaps::init() » N'APPELLE « register_sitemaps() » QUE SI « sitemaps_enabled() » EST VRAI
 *      (« wp-includes/sitemaps/class-wp-sitemaps.php »). Façade éteinte, il n'existe AUCUN
 *      fournisseur — pas même « posts ». C'est ce fait, et lui seul, qui impose L'ORDRE des tests
 *      ci-dessous : la façade D'ABORD, le fournisseur ENSUITE. Dans l'ordre inverse, une recette
 *      légitimement fermée aux moteurs rougirait en « témoin indisponible », et la première personne
 *      qui verrait ce rouge « réparerait » le témoin.
 *   2. « WP_Sitemaps_Posts::get_max_num_pages() » APPELLE « get_posts_query_args() », donc APPLIQUE
 *      « wp_sitemaps_posts_query_args » (« wp-includes/sitemaps/providers/class-wp-sitemaps-posts.php »).
 *      Le nombre de pages peut donc DIFFÉRER d'une passe à l'autre : il est relu DANS CHAQUE PASSE,
 *      jamais une seule fois pour les deux.
 *   3. L'ENTRÉE D'ACCUEIL du sous-type « page » n'est empilée par « get_url_list() » que si
 *      « show_on_front » vaut « posts », et sur la seule page 1. Elle ne dépend d'aucun rappel de ce
 *      crochet : présente ou absente, elle l'est DANS LES DEUX PASSES et s'annule dans la différence.
 *      (Sur cette base, au même relevé, « show_on_front » vaut « page » : elle n'est pas empilée.)
 *
 * EFFET DE BORD ASSUMÉ ET DÉCLARÉ. « wp_sitemaps_get_server() » INSTANCIE le serveur du plan du site
 * s'il ne l'était pas, ce qui déclenche « wp_sitemaps_add_provider » — donc les rappels voisins, LUS
 * ET JAMAIS MODIFIÉS. La ligne « Serveur du plan du site : … » dit à chaque exécution ce que cette
 * étape a provoqué, plutôt que de le laisser deviner.
 *
 * AUCUNE REQUÊTE RÉSEAU. Le plan du site est CONSTRUIT EN PROCESSUS, jamais récupéré en HTTP (D6).
 *
 * @param array<int, string> $echecs Liste des échecs, complétée sur place.
 *
 * @return \WP_Sitemaps_Provider|null Fournisseur « posts », ou « null » quand la moitié « effet » ne
 *                                    peut pas être mesurée.
 */
function plan_du_site_mesurable( array &$echecs ): ?\WP_Sitemaps_Provider {
	$deja_instancie = isset( $GLOBALS['wp_sitemaps'] );

	$serveur = wp_sitemaps_get_server();

	\WP_CLI::log( sprintf( '   Serveur du plan du site : %s.', $deja_instancie ? 'déjà instancié par le cœur' : 'instancié par cette étape' ) );

	if ( ! $serveur->sitemaps_enabled() ) {
		\WP_CLI::warning( 'Avertissement (étape 6 bis) : le plan du site est DÉSACTIVÉ sur ce site (« blog_public » à 0, ou le filtre « wp_sitemaps_enabled »). La moitié « effet » de cette étape N\'A PAS ÉTÉ MESURÉE : il n\'y a aucune façade à mesurer. Ne pas lire l\'absence d\'erreur comme une preuve.' );

		return null;
	}

	$fournisseurs = wp_get_sitemap_providers();
	$posts        = $fournisseurs['posts'] ?? null;

	if ( ! $posts instanceof \WP_Sitemaps_Provider ) {
		$echecs[] = 'temoin_indisponible:fournisseur_posts';

		\WP_CLI::warning( 'Erreur (étape 6 bis) : le fournisseur « posts » du plan du site est introuvable, alors que le plan du site est actif. Le témoin NE PEUT PAS MESURER l\'effet — un rappel sur « wp_sitemaps_add_provider » l\'a probablement retiré. Ce n\'est pas un verdict sur les contenus.' );

		return null;
	}

	return $posts;
}

/**
 * Relève l'ensemble des chemins que le fournisseur « posts » liste réellement, toutes pages comprises.
 *
 * TOUTES LES PAGES SONT BALAYÉES, JAMAIS LA SEULE PAGE 1. Le jour où le site dépassera
 * « wp_sitemaps_max_urls », une lecture partielle deviendrait fausse EN SILENCE. Le nombre de pages ET
 * le nombre d'adresses sont imprimés par sous-type et par passe — aucun nombre sans sa recette.
 *
 * LA COMPARAISON PORTE SUR DES ADRESSES, JAMAIS SUR DES IDENTIFIANTS : « get_url_list() » rend des
 * « loc ». Les deux côtés de la comparaison sont réduits par « normaliser_chemin() » de ce module même,
 * jamais par un second normalisateur qui divergerait un jour du premier. Un « loc » qui ne se normalise
 * pas est écarté ET COMPTÉ : une adresse silencieusement perdue fausserait la différence.
 *
 * @param \WP_Sitemaps_Provider $posts     Fournisseur « posts » du plan du site.
 * @param string                $etiquette Libellé de la passe, imprimé tel quel.
 *
 * @return array<string, true> Chemins normalisés, indexés par eux-mêmes.
 */
function chemins_du_fournisseur_posts( \WP_Sitemaps_Provider $posts, string $etiquette ): array {
	$chemins    = array();
	$illisibles = 0;

	foreach ( array_keys( $posts->get_object_subtypes() ) as $sous_type ) {
		$sous_type = (string) $sous_type;
		$pages     = (int) $posts->get_max_num_pages( $sous_type );
		$adresses  = 0;

		for ( $page = 1; $page <= $pages; $page++ ) {
			foreach ( (array) $posts->get_url_list( $page, $sous_type ) as $entree ) {
				++$adresses;

				$loc = is_array( $entree ) && isset( $entree['loc'] ) && is_string( $entree['loc'] ) ? $entree['loc'] : '';

				$chemin = '' === $loc ? '' : normaliser_chemin( $loc );

				if ( '' === $chemin ) {
					++$illisibles;

					continue;
				}

				$chemins[ $chemin ] = true;
			}
		}

		\WP_CLI::log( sprintf( '   Plan du site, fournisseur « posts », sous-type « %s » : %d page(s), %d adresse(s) — %s.', $sous_type, $pages, $adresses, $etiquette ) );
	}

	if ( $illisibles > 0 ) {
		\WP_CLI::log( sprintf( '   %d adresse(s) du plan du site n\'ont pas pu être normalisées et sont écartées de la comparaison.', $illisibles ) );
	}

	return $chemins;
}

/**
 * Compose l'ensemble des adresses que le rappel doit retirer du plan du site.
 *
 * ELLE COUVRE TOUS LES ENDORMIS, PAS SEULEMENT LES CINQ CONTENUS REPRIS. Le rappel les retire tous ;
 * l'assertion doit donc les attendre tous, sans quoi elle rougirait dès le premier sommeil décidé par
 * l'éleveuse — c'est-à-dire qu'elle punirait l'usage normal du produit.
 *
 * Trois retranchements, chacun parce que l'absence n'y prouverait rien : le contenu non publié n'est
 * dans aucune des deux passes, le contenu protégé par mot de passe est retiré des DEUX passes par
 * « query/page-protegee », et un type que le fournisseur ne sert pas n'apparaît nulle part.
 *
 * @param array<int, string> $sous_types Sous-types réellement servis par le fournisseur « posts ».
 *
 * @return array<string, int> Chemin normalisé => identifiant de l'endormi éligible.
 */
function adresses_attendues( array $sous_types ): array {
	$attendues = array();
	$servis    = array_flip( $sous_types );

	foreach ( \MTB\Core\Query\MiseEnSommeil\identifiants_en_sommeil() as $identifiant ) {
		$identifiant = (int) $identifiant;

		if ( 'publish' !== (string) get_post_status( $identifiant ) ) {
			continue;
		}

		if ( '' !== (string) get_post_field( 'post_password', $identifiant ) ) {
			continue;
		}

		if ( ! isset( $servis[ (string) get_post_type( $identifiant ) ] ) ) {
			continue;
		}

		$chemin = normaliser_chemin( (string) get_permalink( $identifiant ) );

		if ( '' === $chemin ) {
			continue;
		}

		$attendues[ $chemin ] = $identifiant;
	}

	return $attendues;
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
