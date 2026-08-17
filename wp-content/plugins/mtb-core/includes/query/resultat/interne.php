<?php
/**
 * Lecture, hydratation, tri et calcul des colonnes des résultats de travail.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Query\Resultat;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Libellé d'absence, figé par le système de design.
 */
const ABSENCE = 'Non renseigné';

/**
 * État « le champ existe mais n'a pas été rempli ».
 */
const ETAT_ABSENT = 'donnee_absente';

/**
 * État « le chien n'a pas de fiche sur le site ».
 *
 * Le mot est impropre ici — le chien d'un résultat n'est le parent de personne — mais il est figé
 * par le contrat de l'issue #1 et partagé avec les autres modules. Il n'a aucun effet à
 * l'exécution : le consommateur regarde « url », jamais « etat », pour décider d'un lien.
 */
const ETAT_SANS_FICHE = 'parent_hors_elevage';

/**
 * Charge une fois par requête HTTP toutes les lignes publiées, déjà hydratées.
 *
 * Une seule lecture des résultats, une seule lecture groupée des fiches chiens liées : le coût ne
 * dépend ni du nombre de disciplines, ni du nombre de chiens. Mémorisation en statique et non par
 * transient : un transient périmé après modification d'un résultat serait exactement l'échec de
 * « saisi une fois, affiché partout ».
 *
 * @return array<int, array<string, mixed>> Lignes indexées de zéro, triées par identifiant croissant.
 */
function toutes_les_lignes(): array {
	static $lignes = null;

	if ( null !== $lignes ) {
		return $lignes;
	}

	$resultats = get_posts(
		array(
			'post_type'              => 'mtb_resultat',
			'post_status'            => 'publish',
			'has_password'           => false,
			'posts_per_page'         => -1,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'ignore_sticky_posts'    => true,
			'orderby'                => 'ID',
			'order'                  => 'ASC',
		)
	);

	$identifiants_chiens = array();

	foreach ( $resultats as $resultat ) {
		$identifiant = absint( get_post_meta( $resultat->ID, '_mtb_chien_id', true ) );

		if ( 0 < $identifiant ) {
			$identifiants_chiens[] = $identifiant;
		}
	}

	$fiches = fiches_chiens( $identifiants_chiens );
	$lignes = array();

	foreach ( $resultats as $resultat ) {
		$lignes[] = construire_ligne( $resultat, $fiches );
	}

	return $lignes;
}

/**
 * Charge en un seul lot les fiches chiens liées, quel que soit leur statut.
 *
 * Les statuts non publiés sont demandés volontairement : le contrat exige que le nom d'une fiche
 * passée en brouillon ou à la corbeille reste affiché, sans lien.
 *
 * @param int[] $identifiants Identifiants de fiches, avec doublons éventuels.
 *
 * @return array<int, \WP_Post> Fiches indexées par identifiant ; tableau vide si aucune.
 */
function fiches_chiens( array $identifiants ): array {
	$identifiants = array_values( array_unique( array_filter( array_map( 'absint', $identifiants ) ) ) );

	if ( array() === $identifiants ) {
		return array();
	}

	$fiches = get_posts(
		array(
			'post_type'              => 'mtb_chien',
			'post__in'               => $identifiants,
			'post_status'            => array( 'publish', 'future', 'draft', 'pending', 'private', 'trash' ),
			'posts_per_page'         => -1,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'ignore_sticky_posts'    => true,
			'orderby'                => 'ID',
			'order'                  => 'ASC',
		)
	);

	$par_identifiant = array();

	foreach ( $fiches as $fiche ) {
		$par_identifiant[ $fiche->ID ] = $fiche;
	}

	return $par_identifiant;
}

/**
 * Compose une ligne complète : un identifiant, puis six cellules à valeur brute et affichage fini.
 *
 * Les six cellules sont toujours présentes, y compris celles qu'aucune colonne n'affichera : le
 * consommateur parcourt « colonnes » et n'a jamais à tester l'existence d'une clé.
 *
 * @param \WP_Post             $resultat Contenu du résultat.
 * @param array<int, \WP_Post> $fiches   Fiches chiens déjà chargées, indexées par identifiant.
 *
 * @return array<string, mixed> Ligne au format gelé par le contrat.
 */
function construire_ligne( \WP_Post $resultat, array $fiches ): array {
	$discipline = (string) get_post_meta( $resultat->ID, '_mtb_discipline', true );
	$annee      = absint( get_post_meta( $resultat->ID, '_mtb_annee', true ) );
	$chien_id   = absint( get_post_meta( $resultat->ID, '_mtb_chien_id', true ) );

	$fiche = isset( $fiches[ $chien_id ] ) ? $fiches[ $chien_id ] : null;

	return array(
		'id'       => (int) $resultat->ID,
		'cellules' => array(
			'annee'      => cellule_annee( $annee ),
			'chien'      => cellule_chien( $resultat, $chien_id, $fiche ),
			'niveau'     => cellule_texte( (string) get_post_meta( $resultat->ID, '_mtb_niveau', true ) ),
			'discipline' => cellule_discipline( $discipline ),
			'conducteur' => cellule_texte( (string) get_post_meta( $resultat->ID, '_mtb_conducteur', true ) ),
			'pays'       => cellule_pays( (string) get_post_meta( $resultat->ID, '_mtb_pays', true ) ),
		),
	);
}

/**
 * Cellule d'une valeur textuelle ordinaire.
 *
 * « valeur » porte la donnée recopiée telle quelle, « affichage » la chaîne à imprimer : le
 * consommateur n'a qu'à regarder « affichage », et une cellule sans étiquette à imprimer est
 * exactement celle dont « affichage » est vide.
 *
 * @param string $valeur Valeur stockée, recopiée telle quelle.
 *
 * @return array<string, mixed> Cellule au format gelé.
 */
function cellule_texte( string $valeur ): array {
	if ( '' === $valeur ) {
		return array(
			'valeur'    => '',
			'affichage' => ABSENCE,
			'url'       => '',
			'etat'      => ETAT_ABSENT,
		);
	}

	return array(
		'valeur'    => $valeur,
		'affichage' => $valeur,
		'url'       => '',
		'etat'      => '',
	);
}

/**
 * Cellule de l'année : entier pour trier, chaîne décimale pour imprimer.
 *
 * Aucun formatage de nombre n'est appliqué à l'affichage : « 2 021 » serait produit.
 *
 * @param int $annee Année stockée, zéro si absente.
 *
 * @return array<string, mixed> Cellule au format gelé.
 */
function cellule_annee( int $annee ): array {
	if ( 0 === $annee ) {
		return array(
			'valeur'    => 0,
			'affichage' => ABSENCE,
			'url'       => '',
			'etat'      => ETAT_ABSENT,
		);
	}

	return array(
		'valeur'    => $annee,
		'affichage' => (string) $annee,
		'url'       => '',
		'etat'      => '',
	);
}

/**
 * Cellule de la discipline : clé canonique en valeur, libellé fini en affichage.
 *
 * Une valeur orpheline garde sa clé brute des deux côtés : rien ne disparaît, ni de la page, ni en
 * silence.
 *
 * @param string $cle Clé stockée, chaîne vide si la discipline n'a jamais été renseignée.
 *
 * @return array<string, mixed> Cellule au format gelé.
 */
function cellule_discipline( string $cle ): array {
	if ( '' === $cle ) {
		return array(
			'valeur'    => '',
			'affichage' => ABSENCE,
			'url'       => '',
			'etat'      => ETAT_ABSENT,
		);
	}

	return array(
		'valeur'    => $cle,
		'affichage' => libelle_discipline( $cle ),
		'url'       => '',
		'etat'      => '',
	);
}

/**
 * Cellule du pays — seule exception à « Non renseigné ».
 *
 * Un pays vide ne signifie pas « inconnu » mais « le résultat n'a pas été obtenu à l'étranger ».
 * Écrire « Non renseigné » y serait faux : l'affichage reste vide, ce qui supprime l'étiquette du
 * repli mobile, et la colonne disparaît quand aucune ligne ne la remplit.
 *
 * @param string $valeur Valeur stockée, recopiée telle quelle.
 *
 * @return array<string, mixed> Cellule au format gelé.
 */
function cellule_pays( string $valeur ): array {
	return array(
		'valeur'    => $valeur,
		'affichage' => $valeur,
		'url'       => '',
		'etat'      => '',
	);
}

/**
 * Cellule du chien : nom, lien éventuel, sexe.
 *
 * Le lien existe si et seulement si « url » est une chaîne non vide. Une fiche en brouillon, à la
 * corbeille ou protégée par mot de passe garde son nom et perd son lien — sans état distinct, qui
 * signalerait au visiteur l'existence d'un contenu réservé.
 *
 * @param \WP_Post      $resultat Contenu du résultat.
 * @param int           $chien_id Identifiant de fiche saisi, zéro si aucune fiche n'est choisie.
 * @param \WP_Post|null $fiche    Fiche chien liée, ou null.
 *
 * @return array<string, mixed> Cellule au format gelé.
 */
function cellule_chien( \WP_Post $resultat, int $chien_id, ?\WP_Post $fiche ): array {
	$sexe = (string) get_post_meta( $resultat->ID, '_mtb_sexe', true );
	$nom  = '';
	$url  = '';
	$etat = '';

	if ( $fiche instanceof \WP_Post ) {
		$nom = (string) $fiche->post_title;

		// La fiche fait autorité sur le sexe ; le champ du résultat ne sert qu'à défaut.
		$sexe_fiche = (string) get_post_meta( $fiche->ID, '_mtb_sexe', true );

		if ( '' !== $sexe_fiche ) {
			$sexe = $sexe_fiche;
		}

		if ( '' !== $nom && 'publish' === $fiche->post_status && '' === (string) $fiche->post_password ) {
			$url = url_fiche( $fiche );
		}
	}

	if ( '' === $nom ) {
		// Une fiche choisie l'emporte ; le nom recopié ne sert que si aucune fiche n'est utilisable.
		$nom  = (string) get_post_meta( $resultat->ID, '_mtb_chien_nom', true );
		$url  = '';
		$etat = '' === $nom ? '' : ETAT_SANS_FICHE;
	}

	if ( '' === $nom ) {
		return array(
			'valeur'       => $chien_id,
			'affichage'    => ABSENCE,
			'url'          => '',
			'etat'         => ETAT_ABSENT,
			'sexe'         => $sexe,
			'sexe_libelle' => libelle_sexe( $sexe ),
		);
	}

	return array(
		'valeur'       => $chien_id,
		'affichage'    => $nom,
		'url'          => $url,
		'etat'         => $etat,
		'sexe'         => $sexe,
		'sexe_libelle' => libelle_sexe( $sexe ),
	);
}

/**
 * Adresse publique d'une fiche chien, chaîne vide si le type n'offre aucune page publique.
 *
 * get_post_type_object() renvoie null tant que le type n'est pas enregistré : c'est l'état normal
 * du site avant la livraison des fiches chiens, et il ne doit produire ni erreur ni lien faux.
 *
 * @param \WP_Post $fiche Fiche chien publiée.
 *
 * @return string Adresse absolue, ou chaîne vide.
 */
function url_fiche( \WP_Post $fiche ): string {
	$type = get_post_type_object( 'mtb_chien' );

	if ( null === $type || true !== $type->public ) {
		return '';
	}

	$url = get_permalink( $fiche );

	return false === $url ? '' : (string) $url;
}

/**
 * Libellé d'une discipline : celui de la liste close, ou la valeur brute si elle est orpheline.
 *
 * @param string $cle Clé stockée, jamais vide : l'absence de discipline est traitée en amont.
 *
 * @return string Libellé à imprimer.
 */
function libelle_discipline( string $cle ): string {
	$disciplines = disciplines();

	return isset( $disciplines[ $cle ] ) ? $disciplines[ $cle ] : $cle;
}

/**
 * Liste close des disciplines, lue depuis son unique source.
 *
 * @return array<string, string> Clé stockée vers libellé, ordre gelé ; tableau vide si le module de
 *                               lecture publique a été désactivé.
 */
function disciplines(): array {
	if ( ! function_exists( 'mtb_resultat_disciplines' ) ) {
		return array();
	}

	return mtb_resultat_disciplines();
}

/**
 * Libellé d'un sexe : celui de la liste close, ou la valeur stockée telle quelle.
 *
 * Vaut la chaîne vide quand le sexe est inconnu, et non « Non renseigné » : le sexe n'est pas une
 * colonne du tableau, un texte de remplissage à côté de chaque nom serait du bruit.
 *
 * @param string $valeur Valeur stockée.
 *
 * @return string Libellé à imprimer, chaîne vide si inconnu.
 */
function libelle_sexe( string $valeur ): string {
	if ( '' === $valeur ) {
		return '';
	}

	if ( ! function_exists( 'mtb_resultat_sexes' ) ) {
		return $valeur;
	}

	$sexes = mtb_resultat_sexes();

	return isset( $sexes[ $valeur ] ) ? $sexes[ $valeur ] : $valeur;
}

/**
 * Libellés de colonne, dans l'ordre gelé par le contrat.
 *
 * Ce sont exactement les chaînes que le composant recopie dans « data-libelle » sur chaque cellule,
 * ce qui permet au tableau de se déplier en lignes libellées sur un téléphone.
 *
 * @return array<string, string> Clé de colonne vers libellé.
 */
function libelles_colonnes(): array {
	return array(
		'annee'      => 'Année',
		'chien'      => 'Chien',
		'niveau'     => 'Niveau',
		'discipline' => 'Discipline',
		'conducteur' => 'Conducteur',
		'pays'       => 'Pays',
	);
}

/**
 * Calcule les colonnes d'un jeu de lignes.
 *
 * Conducteur et Pays ne sont présents que si au moins une ligne les remplit : les rendre
 * inconditionnels afficherait des colonnes entièrement vides, et sur un téléphone une étiquette
 * suivie de rien à chaque ligne.
 *
 * @param array<int, array<string, mixed>> $lignes     Lignes du groupe.
 * @param string[]                         $cles_fixes Colonnes toujours présentes pour ce consommateur.
 *
 * @return array<int, array<string, string>> Liste ordonnée de colonnes.
 */
function colonnes( array $lignes, array $cles_fixes ): array {
	$colonnes = array();

	foreach ( libelles_colonnes() as $cle => $libelle ) {
		$retenue = in_array( $cle, $cles_fixes, true );

		if ( false === $retenue && ( 'conducteur' === $cle || 'pays' === $cle ) ) {
			$retenue = au_moins_une_valeur( $lignes, $cle );
		}

		if ( true === $retenue ) {
			$colonnes[] = array(
				'cle'     => $cle,
				'libelle' => $libelle,
			);
		}
	}

	return $colonnes;
}

/**
 * Indique si au moins une ligne remplit une cellule donnée.
 *
 * Le test porte sur la valeur brute et non sur l'affichage : un conducteur non renseigné affiche
 * « Non renseigné », et compter cet affichage comme une valeur ferait apparaître la colonne
 * Conducteur sur tous les tableaux. Les deux seules colonnes calculées — Conducteur et Pays — sont
 * des chaînes recopiées, le test juste pour leur type est donc celui de la chaîne vide.
 *
 * @param array<int, array<string, mixed>> $lignes Lignes à examiner.
 * @param string                           $cle    Clé de cellule.
 *
 * @return bool Vrai dès qu'une ligne porte une valeur.
 */
function au_moins_une_valeur( array $lignes, string $cle ): bool {
	foreach ( $lignes as $ligne ) {
		if ( isset( $ligne['cellules'][ $cle ]['valeur'] ) && '' !== $ligne['cellules'][ $cle ]['valeur'] ) {
			return true;
		}
	}

	return false;
}

/**
 * Trie des lignes par année, en PHP et non en SQL.
 *
 * Un tri SQL sur la valeur du champ exclurait les lignes sans année, que le contrat exige de
 * renvoyer. Une année absente se range en dernier dans les deux sens : une ligne incomplète ne
 * remonte jamais en tête. Départage par identifiant croissant — surtout pas par niveau, il
 * n'existe aucune hiérarchie officielle des niveaux et en inventer une fabriquerait un fait.
 *
 * @param array<int, array<string, mixed>> $lignes Lignes à trier.
 * @param string                           $ordre  « annee_desc » ou « annee_asc ».
 *
 * @return array<int, array<string, mixed>> Lignes triées, réindexées.
 */
function trier( array $lignes, string $ordre ): array {
	$decroissant = 'annee_desc' === $ordre;

	usort(
		$lignes,
		static function ( array $gauche, array $droite ) use ( $decroissant ): int {
			$annee_gauche = $gauche['cellules']['annee']['valeur'];
			$annee_droite = $droite['cellules']['annee']['valeur'];

			if ( $annee_gauche !== $annee_droite ) {
				if ( 0 === $annee_gauche ) {
					return 1;
				}

				if ( 0 === $annee_droite ) {
					return -1;
				}

				return true === $decroissant ? $annee_droite <=> $annee_gauche : $annee_gauche <=> $annee_droite;
			}

			return $gauche['id'] <=> $droite['id'];
		}
	);

	return array_values( $lignes );
}

/**
 * Normalise les deux seuls arguments acceptés ; toute autre clé est ignorée.
 *
 * @param array<string, mixed> $args         Arguments reçus du consommateur.
 * @param string               $ordre_defaut Ordre à retenir en l'absence de demande explicite.
 *
 * @return array<string, mixed> Tableau à deux clés : « ordre » et « disciplines ».
 */
function normaliser_args( array $args, string $ordre_defaut ): array {
	$ordre = isset( $args['ordre'] ) ? (string) $args['ordre'] : '';

	if ( 'annee_desc' !== $ordre && 'annee_asc' !== $ordre ) {
		$ordre = $ordre_defaut;
	}

	$disciplines = array();

	if ( isset( $args['disciplines'] ) && is_array( $args['disciplines'] ) ) {
		foreach ( $args['disciplines'] as $cle ) {
			if ( is_string( $cle ) && '' !== $cle ) {
				$disciplines[] = $cle;
			}
		}
	}

	return array(
		'ordre'       => $ordre,
		'disciplines' => $disciplines,
	);
}

/**
 * Groupe toutes les lignes publiées par discipline.
 *
 * Les disciplines connues sortent dans l'ordre gelé de la liste close ; les valeurs orphelines
 * suivent, en fin de retour, avec leur valeur brute pour libellé. Rien ne disparaît.
 *
 * @param array<string, mixed> $args Arguments déjà normalisés.
 *
 * @return array<int, array<string, mixed>> Liste ordonnée de groupes ; tableau vide si aucune ligne.
 */
function par_discipline( array $args ): array {
	$connues   = disciplines();
	$paquets   = array();
	$orphelins = array();

	foreach ( toutes_les_lignes() as $ligne ) {
		$cle = $ligne['cellules']['discipline']['valeur'];

		if ( array() !== $args['disciplines'] && ! in_array( $cle, $args['disciplines'], true ) ) {
			continue;
		}

		if ( isset( $connues[ $cle ] ) ) {
			$paquets[ $cle ][] = $ligne;

			continue;
		}

		$orphelins[ $cle ][] = $ligne;
	}

	$groupes = array();

	foreach ( $connues as $cle => $libelle ) {
		if ( ! isset( $paquets[ $cle ] ) ) {
			continue;
		}

		$groupes[] = groupe( $cle, $libelle, false, trier( $paquets[ $cle ], $args['ordre'] ) );
	}

	foreach ( cles_orphelines( $orphelins ) as $cle ) {
		$libelle = '' === $cle ? ABSENCE : $cle;

		$groupes[] = groupe( $cle, $libelle, true, trier( $orphelins[ $cle ], $args['ordre'] ) );
	}

	return $groupes;
}

/**
 * Ordonne les clés orphelines : alphabétique, la discipline non renseignée en tout dernier.
 *
 * Un ordre explicite, jamais celui du hasard : deux affichages successifs de la même page doivent
 * donner exactement la même succession de tableaux.
 *
 * @param array<string, array<int, array<string, mixed>>> $orphelins Lignes indexées par clé orpheline.
 *
 * @return string[] Clés ordonnées.
 */
function cles_orphelines( array $orphelins ): array {
	/*
	 * PHP retransforme en entier toute clé de tableau purement numérique : une discipline valant
	 * « 2019 » — une inversion de colonnes dans un import — ressortirait d'array_keys() en int, et
	 * ferait tomber toute la page sur un TypeError au premier paramètre typé rencontré. On rétablit
	 * la chaîne d'origine ici, à la sortie du tableau, avant tout appel typé.
	 */
	$cles = array_map( 'strval', array_keys( $orphelins ) );

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
 * Assemble un groupe.
 *
 * @param string                           $cle       Clé stockée de la discipline.
 * @param string                           $libelle   Libellé à imprimer tel quel.
 * @param bool                             $orpheline Vrai si la valeur est hors de la liste close.
 * @param array<int, array<string, mixed>> $lignes    Lignes déjà triées.
 *
 * @return array<string, mixed> Groupe au format gelé.
 */
function groupe( string $cle, string $libelle, bool $orpheline, array $lignes ): array {
	return array(
		'discipline'         => $cle,
		'discipline_libelle' => $libelle,
		'orpheline'          => $orpheline,
		'colonnes'           => colonnes( $lignes, array( 'annee', 'chien', 'niveau' ) ),
		'lignes'             => $lignes,
	);
}

/**
 * Palmarès d'une fiche chien : ses lignes, sans colonne Chien.
 *
 * @param int                  $chien_id Identifiant de la fiche.
 * @param array<string, mixed> $args     Arguments déjà normalisés.
 *
 * @return array<string, mixed> Tableau à deux clés : « colonnes » et « lignes ».
 */
function du_chien( int $chien_id, array $args ): array {
	$lignes = array();

	if ( 0 < $chien_id ) {
		foreach ( toutes_les_lignes() as $ligne ) {
			if ( $chien_id !== $ligne['cellules']['chien']['valeur'] ) {
				continue;
			}

			if ( array() !== $args['disciplines'] && ! in_array( $ligne['cellules']['discipline']['valeur'], $args['disciplines'], true ) ) {
				continue;
			}

			$lignes[] = $ligne;
		}
	}

	$lignes = trier( $lignes, $args['ordre'] );

	return array(
		'colonnes' => array() === $lignes ? array() : colonnes( $lignes, array( 'annee', 'niveau', 'discipline' ) ),
		'lignes'   => $lignes,
	);
}
