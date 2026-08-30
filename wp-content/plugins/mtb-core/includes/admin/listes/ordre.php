<?php
/**
 * Ordre imposé aux trois listes d'administration, et mécanisme partagé avec les filtres.
 *
 * L'ordre par défaut de ces listes était celui de la date de publication : la reprise de l'ancien
 * site n'ayant jamais écrit cette date, les cent cinq contenus portent l'horodatage de l'import à
 * la seconde près, et l'ordre affiché était donc l'ordre d'import inversé — sans aucun rapport avec
 * l'élevage. On impose ici un ordre qui a un sens, une fois pour toutes, plutôt qu'un tri cliquable
 * qui pourrait être mis dans un état surprenant.
 *
 * La liste ordonnée des identifiants se calcule en PHP, puis se pose en « post__in ». Trier en SQL
 * sur la valeur d'un champ imposerait une jointure, et ferait disparaître de la liste, sans un mot,
 * tout contenu dépourvu de ce champ — soit exactement ce que l'issue combat.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Admin\Listes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Statuts couverts par l'écran en cours.
 *
 * C'est cette fonction, et elle seule, qui neutralise le piège de la corbeille. Une liste
 * d'identifiants calculée sur « post_status => any » n'en contiendrait aucun contenu en corbeille,
 * puisque « any » écarte tout statut exclu de la recherche ; posée en « post__in » sur l'onglet
 * Corbeille, elle viderait l'onglet. « any » est donc proscrit dans tout ce module : la portée se
 * dérive de l'écran lui-même, et sur l'onglet Corbeille la liste calculée est celle des contenus en
 * corbeille.
 *
 * La validation reproduit celle du cœur, qui teste « post_status » contre les statuts enregistrés
 * avant de le retenir. Une valeur non enregistrée — « all » compris — ramène à l'onglet « Tous ».
 *
 * @return string[] Noms de statuts, jamais vide.
 */
function statuts_de_l_ecran(): array {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- lecture d'un paramètre de navigation en GET, posé par les onglets du cœur ; aucune écriture n'en découle.
	$demande = isset( $_GET['post_status'] ) ? sanitize_key( wp_unslash( $_GET['post_status'] ) ) : '';

	if ( '' !== $demande && in_array( $demande, array_values( get_post_stati() ), true ) ) {
		return array( $demande );
	}

	/*
	 * WP_Post_Status fixe « show_in_admin_all_list » à l'inverse de « internal » quand l'argument
	 * n'est pas passé : publish, future, draft, pending et private sont donc à vrai, trash et les
	 * statuts internes à faux. C'est exactement la portée de l'onglet « Tous ».
	 */
	$tous = array_values( get_post_stati( array( 'show_in_admin_all_list' => true ) ) );

	return array() === $tous ? array( 'publish' ) : $tous;
}

/**
 * Balaie tous les contenus de la portée d'écran et rend la liste ordonnée plus les valeurs de
 * filtre.
 *
 * Un seul balayage par requête HTTP, mémoïsé : l'ordre (qui court tôt, dans pre_get_posts) et la
 * barre de filtres (qui court plus tard, à l'affichage) le partagent. Deux requêtes SQL en tout —
 * « fields => ids » amorce quand même le cache des champs, si bien que les lectures de champs
 * ci-dessous ne coûtent aucune requête supplémentaire.
 *
 * @param string $type Nom du type de contenu, l'un des trois.
 *
 * @return array{ids: int[], valeurs: array<int, string>} Identifiants ordonnés, et pour chacun la
 *                                                        valeur sur laquelle son filtre porte.
 */
function balayage( string $type ): array {
	static $memoire  = array();
	static $en_cours = false;

	$statuts = statuts_de_l_ecran();
	$clef    = $type . '|' . implode( ',', $statuts );

	if ( isset( $memoire[ $clef ] ) ) {
		return $memoire[ $clef ];
	}

	$vide = array(
		'ids'     => array(),
		'valeurs' => array(),
	);

	/*
	 * Ce WP_Query déclenche pre_get_posts. La garde is_main_query() suffit à l'écarter, mais une
	 * récursion ici serait un épuisement de mémoire et non un affichage de travers : un drapeau de
	 * plus coûte une ligne.
	 */
	if ( true === $en_cours ) {
		return $vide;
	}

	$en_cours = true;

	$requete = new \WP_Query(
		array(
			'post_type'              => $type,
			'post_status'            => $statuts,
			'posts_per_page'         => -1,
			'fields'                 => 'ids',
			'orderby'                => 'ID',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => true,
		)
	);

	$en_cours = false;

	$identifiants = array();

	foreach ( $requete->posts as $brut ) {
		$identifiant = (int) $brut;

		if ( $identifiant > 0 ) {
			$identifiants[] = $identifiant;
		}
	}

	$resultat = array(
		'ids'     => ordonner( $type, $identifiants ),
		'valeurs' => valeurs_de_filtre( $type, $identifiants ),
	);

	$memoire[ $clef ] = $resultat;

	return $resultat;
}

/**
 * Ordonne une liste d'identifiants selon la règle du type.
 *
 * @param string $type         Nom du type de contenu.
 * @param int[]  $identifiants Identifiants, dans l'ordre du balayage.
 *
 * @return int[] Identifiants ordonnés.
 */
function ordonner( string $type, array $identifiants ): array {
	if ( 'mtb_portee' === $type ) {
		return ordonner_portees( $identifiants );
	}

	if ( 'mtb_resultat' === $type ) {
		return ordonner_resultats( $identifiants );
	}

	// Les chiens s'ordonnent nativement par titre : aucune liste d'identifiants n'y sert l'ordre.
	return $identifiants;
}

/**
 * Ordonne les portées : date de naissance décroissante, non datées en fin de liste.
 *
 * Règle de comparaison recopiée de la lecture publique, pour que l'administration montre exactement
 * la succession du site. Une portée sans date lisible n'a pas de place chronologique : elle se range
 * en fin de liste, jamais en tête, et n'est jamais escamotée. Les égalités se départagent par
 * l'identifiant, pour que l'ordre soit le même d'une requête à l'autre.
 *
 * « Sans date lisible » se décide par la fonction qui rend la colonne, et par elle seule : une date
 * corrompue affiche « Non renseigné » et doit donc se ranger avec les non datées. Refaire ici le test
 * de validité fabriquerait une seconde notion de date absente, qui divergerait de la première ; c'est
 * exactement ce qui rangeait une date illisible en tête de liste.
 *
 * @param int[] $identifiants Identifiants à ordonner.
 *
 * @return int[] Identifiants ordonnés.
 */
function ordonner_portees( array $identifiants ): array {
	$dates = array();

	foreach ( $identifiants as $identifiant ) {
		$brut = champ( $identifiant, '_mtb_date_naissance' );

		$lisible = \MTB\Core\Query\Portee\Hydratation::ABSENCE
			!== \MTB\Core\Query\Portee\Hydratation::date_en_toutes_lettres( $brut );

		$dates[ $identifiant ] = $lisible ? $brut : '';
	}

	usort(
		$identifiants,
		static function ( int $gauche, int $droite ) use ( $dates ): int {
			$une   = $dates[ $gauche ];
			$autre = $dates[ $droite ];

			if ( '' === $une && '' !== $autre ) {
				return 1;
			}

			if ( '' !== $une && '' === $autre ) {
				return -1;
			}

			$ecart = strcmp( $autre, $une );

			if ( 0 !== $ecart ) {
				return $ecart;
			}

			return $droite <=> $gauche;
		}
	);

	return array_values( $identifiants );
}

/**
 * Ordonne les résultats de travail : disciplines dans l'ordre gelé, orphelines ensuite,
 * sans-discipline en tout dernier ; dans un groupe, année décroissante et sans-année en fin.
 *
 * C'est la succession de la page publique « Travail », reprise à l'identique : un seul modèle
 * mental au lieu de deux. Elle rassemble aussi tous les résultats incomplets en un bloc unique, en
 * toute fin de liste — l'endroit qu'on regarde exprès pour trouver ce qui reste à compléter.
 *
 * Le départage se fait par identifiant croissant, surtout pas par niveau : il n'existe aucune
 * hiérarchie officielle des niveaux, et en inventer une fabriquerait un fait.
 *
 * @param int[] $identifiants Identifiants à ordonner.
 *
 * @return int[] Identifiants ordonnés.
 */
function ordonner_resultats( array $identifiants ): array {
	$connues   = disciplines();
	$paquets   = array();
	$orphelins = array();
	$annees    = array();

	foreach ( $identifiants as $identifiant ) {
		$cle                    = champ( $identifiant, '_mtb_discipline' );
		$annees[ $identifiant ] = (int) champ( $identifiant, '_mtb_annee' );

		if ( isset( $connues[ $cle ] ) ) {
			$paquets[ $cle ][] = $identifiant;

			continue;
		}

		$orphelins[ $cle ][] = $identifiant;
	}

	$ordonnes = array();

	foreach ( array_keys( $connues ) as $cle ) {
		if ( isset( $paquets[ $cle ] ) ) {
			$ordonnes = array_merge( $ordonnes, ordonner_par_annee( $paquets[ $cle ], $annees ) );
		}
	}

	foreach ( cles_orphelines( array_keys( $orphelins ) ) as $cle ) {
		$ordonnes = array_merge( $ordonnes, ordonner_par_annee( $orphelins[ $cle ], $annees ) );
	}

	return $ordonnes;
}

/**
 * Ordonne les clés orphelines : alphabétique, la discipline non renseignée en tout dernier.
 *
 * Un ordre explicite, jamais celui du hasard : deux affichages successifs de la même liste doivent
 * donner exactement la même succession.
 *
 * @param array<int, int|string> $cles Clés brutes issues d'un tableau indexé par discipline.
 *
 * @return string[] Clés ordonnées.
 */
function cles_orphelines( array $cles ): array {
	/*
	 * PHP retransforme en entier toute clé de tableau purement numérique : une discipline valant
	 * « 2019 » — une inversion de colonnes dans un import — ressortirait en int et ferait tomber
	 * l'écran sur un TypeError au premier paramètre typé. On rétablit la chaîne ici, à la sortie.
	 */
	$cles = array_map( 'strval', $cles );

	sort( $cles );

	$sans_discipline = in_array( '', $cles, true );

	$cles = array_values(
		array_filter(
			$cles,
			static function ( string $cle ): bool {
				return '' !== $cle;
			}
		)
	);

	if ( true === $sans_discipline ) {
		$cles[] = '';
	}

	return $cles;
}

/**
 * Ordonne un groupe de résultats par année décroissante, sans-année en fin de groupe.
 *
 * @param int[]           $identifiants Identifiants du groupe.
 * @param array<int, int> $annees       Année stockée de chaque identifiant, zéro si absente.
 *
 * @return int[] Identifiants ordonnés.
 */
function ordonner_par_annee( array $identifiants, array $annees ): array {
	usort(
		$identifiants,
		static function ( int $gauche, int $droite ) use ( $annees ): int {
			$une   = $annees[ $gauche ];
			$autre = $annees[ $droite ];

			if ( $une !== $autre ) {
				if ( 0 === $une ) {
					return 1;
				}

				if ( 0 === $autre ) {
					return -1;
				}

				return $autre <=> $une;
			}

			return $gauche <=> $droite;
		}
	);

	return array_values( $identifiants );
}

/**
 * Valeur sur laquelle porte le filtre de chaque contenu.
 *
 * Portées : l'année de la date de naissance, ou la chaîne vide quand cette date est absente ou
 * illisible. Chiens : la clé de statut. Résultats : la clé de discipline, orpheline comprise.
 *
 * « Lisible » se décide par la fonction qui rend la colonne, comme pour l'ordre : le filtre ne
 * propose que des années déduites des contenus, et les quatre premiers caractères d'une date
 * corrompue n'en sont pas une. Une date acceptée fait toujours dix caractères, si bien que la
 * découpe n'a plus besoin d'être gardée en longueur.
 *
 * @param string $type         Nom du type de contenu.
 * @param int[]  $identifiants Identifiants balayés.
 *
 * @return array<int, string> Identifiant vers valeur de filtre.
 */
function valeurs_de_filtre( string $type, array $identifiants ): array {
	$valeurs = array();

	foreach ( $identifiants as $identifiant ) {
		if ( 'mtb_portee' === $type ) {
			$date = champ( $identifiant, '_mtb_date_naissance' );

			$lisible = \MTB\Core\Query\Portee\Hydratation::ABSENCE
				!== \MTB\Core\Query\Portee\Hydratation::date_en_toutes_lettres( $date );

			$valeurs[ $identifiant ] = $lisible ? substr( $date, 0, 4 ) : '';

			continue;
		}

		if ( 'mtb_chien' === $type ) {
			$valeurs[ $identifiant ] = champ( $identifiant, '_mtb_statut' );

			continue;
		}

		$valeurs[ $identifiant ] = champ( $identifiant, '_mtb_discipline' );
	}

	return $valeurs;
}

/**
 * Impose l'ordre et applique le filtre sur la liste d'administration en cours.
 *
 * @param \WP_Query $query Requête en cours de préparation.
 */
function imposer_ordre( \WP_Query $query ): void {
	if ( ! is_admin() ) {
		return;
	}

	/*
	 * $pagenow plutôt que get_current_screen() : c'est le global que le cœur emploie lui-même, il
	 * est toujours posé en administration, alors que get_current_screen() peut rendre null selon le
	 * contexte — et lire une propriété sur null serait une erreur fatale au chargement de la liste.
	 */
	global $pagenow;

	if ( 'edit.php' !== $pagenow ) {
		return;
	}

	if ( ! $query->is_main_query() ) {
		return;
	}

	$type = $query->get( 'post_type' );

	if ( ! is_string( $type ) ) {
		return;
	}

	$description = description( $type );

	if ( null === $description ) {
		return;
	}

	$balayage = balayage( $type );
	$filtre   = filtre_actif( $type );
	$ids      = $balayage['ids'];

	if ( '' !== $filtre ) {
		$ids = array_values(
			array_filter(
				$ids,
				static function ( int $identifiant ) use ( $balayage, $filtre ): bool {
					return $filtre === ( $balayage['valeurs'][ $identifiant ] ?? '' );
				}
			)
		);

		/*
		 * Sentinelle, et jamais un tableau vide : WP_Query teste la valeur de « post__in » avant
		 * d'ajouter sa clause, si bien qu'un tableau vide est ignoré et que la liste afficherait
		 * TOUT. Un filtre « 2019 » sans résultat montrerait les vingt-sept portées — le contraire
		 * exact de la demande, et sans un mot. Zéro n'est l'identifiant d'aucun contenu.
		 */
		$query->set( 'post__in', array() === $ids ? array( 0 ) : $ids );
	} elseif ( array() !== $ids && 'post__in' === $description['ordre'] ) {
		/*
		 * RÈGLE DE SÛRETÉ : « post__in » n'est jamais posé sur un écran non filtré dont la liste
		 * calculée est vide. Conséquence — sans filtre, un défaut du balayage ne peut que rater
		 * l'ordre, jamais vider une liste. La sentinelle ci-dessus ne sert donc que dans le seul cas
		 * où un écran vide est la bonne réponse : elle a demandé 2019, il n'y a rien en 2019.
		 */
		$query->set( 'post__in', $ids );
	}

	/*
	 * Un tri explicitement demandé l'emporte sur l'ordre imposé, mais n'annule jamais le filtre en
	 * cours. On teste le paramètre d'URL et non l'état de la requête : le cœur pose lui-même
	 * « orderby => modified » sur les onglets Brouillons et En attente, sans que personne n'ait
	 * cliqué. Tester l'URL distingue « elle a cliqué » de « le cœur a mis un défaut », et impose
	 * donc le même ordre sur toute la liste, onglet par onglet.
	 */
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- lecture d'un paramètre de tri en GET, posé par les en-têtes du cœur ; aucune écriture n'en découle.
	if ( isset( $_GET['orderby'] ) ) {
		return;
	}

	if ( 'titre' === $description['ordre'] ) {
		$query->set( 'orderby', 'title' );
		$query->set( 'order', 'ASC' );

		return;
	}

	$pose = $query->get( 'post__in' );

	// « order » n'est pas posé : le cœur force ASC pour « post__in », c'est-à-dire l'ordre du tableau.
	if ( is_array( $pose ) && array() !== $pose && array( 0 ) !== $pose ) {
		$query->set( 'orderby', 'post__in' );
	}
}
