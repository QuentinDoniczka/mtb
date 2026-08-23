<?php
/**
 * Import des résultats de travail.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\ImportFixtures;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Importe les résultats de travail.
 *
 * @param array<int, mixed>  $entrees Entrées du fichier.
 * @param string             $chemin  Chemin du fichier.
 * @param array<string, int> $index   Index « référence de chien => identifiant ».
 */
function importer_resultats( array $entrees, string $chemin, array &$index ): void {
	$fichier = nom_de_fichier( $chemin );

	foreach ( $entrees as $rang => $entree ) {
		$rang        = (int) $rang;
		$identifiant = is_array( $entree ) ? identifiant_de_resultat( $entree ) : '';
		$raisons     = controler( 'resultats', $entree );

		if ( array() !== $raisons ) {
			rejeter( $fichier, $rang, $identifiant, $raisons, 'resultats' );

			continue;
		}

		$reference = texte_de( $entree, 'reference' );
		$chien_id  = chien_par_reference( $reference, $index );

		if ( '' !== $reference && 0 === $chien_id ) {
			rejeter(
				$fichier,
				$rang,
				$identifiant,
				array( sprintf( 'aucune fiche ne porte la référence « %s » : le résultat n\'est pas écrit.', $reference ) ),
				'resultats'
			);

			continue;
		}

		$metas  = array_merge( valeurs_brutes( 'resultats', $entree ), array( '_mtb_chien_id' => $chien_id ) );
		$champs = champs_de_contenu( 'resultats', $entree );

		if ( 0 < resultat_existant( $metas, $chien_id ) ) {
			compter( 'resultats', 'present' );

			continue;
		}

		/*
		 * Titre provisoire : le niveau, que le tuple d'identité rend obligatoire. Le titre définitif
		 * est composé par le serveur une fois les champs écrits — c'est lui qui lit les valeurs
		 * enregistrées, et il n'existe qu'une seule composition de ce titre dans le projet.
		 */
		$champs['post_title'] = (string) assainir( 'resultats', '_mtb_niveau', $metas['_mtb_niveau'] );

		$post_id = inserer( 'mtb_resultat', $champs );

		if ( 0 === $post_id ) {
			rejeter( $fichier, $rang, $identifiant, array( 'WordPress a refusé la création du résultat.' ), 'resultats' );

			continue;
		}

		ecrire_metas( $post_id, $metas );

		$champs['post_title'] = appliquer_le_titre( $post_id, $champs['post_title'] );

		compter( 'resultats', 'cree' );
		signaler_divergences( 'resultats', $fichier, $rang, $identifiant, controler_aval( 'resultats', $post_id, $metas, $champs ) );
	}
}

/**
 * Remplace le titre provisoire par le titre composé au serveur.
 *
 * @param int    $post_id     Identifiant du résultat.
 * @param string $provisoire  Titre provisoire déjà écrit.
 *
 * @return string Titre effectivement demandé.
 */
function appliquer_le_titre( int $post_id, string $provisoire ): string {
	if ( ! function_exists( '\\MTB\\Core\\Fields\\Resultat\\composer_titre' ) ) {
		return $provisoire;
	}

	$titre = \MTB\Core\Fields\Resultat\composer_titre( $post_id );

	if ( $titre === $provisoire ) {
		return $provisoire;
	}

	wp_update_post(
		wp_slash(
			array(
				'ID'         => $post_id,
				'post_title' => $titre,
			)
		)
	);

	return $titre;
}

/**
 * Identifiant métier d'un résultat, pour les messages.
 *
 * @param array<string, mixed> $entree Entrée du fichier.
 *
 * @return string Identifiant lisible, jamais vide.
 */
function identifiant_de_resultat( array $entree ): string {
	$parties = array();

	foreach ( array( 'discipline', 'reference', 'chien_nom', 'annee' ) as $cle ) {
		$valeur = texte_de( $entree, $cle );

		if ( '' !== $valeur ) {
			$parties[] = $valeur;
		}
	}

	return array() === $parties ? 'entrée sans identifiant' : implode( ' — ', $parties );
}

/**
 * Cherche un résultat déjà enregistré sur le tuple d'identité.
 *
 * Discipline, année, niveau, et le chien — par sa fiche quand elle existe, par son nom sinon. Les
 * quatre clés sont obligatoires au fichier : sans cette obligation, une clé jamais écrite ne serait
 * pas retrouvée par la relecture et l'entrée serait recréée à chaque exécution, en silence.
 *
 * @param array<string, mixed> $metas    Valeurs brutes des champs du résultat.
 * @param int                  $chien_id Identifiant de la fiche du chien, 0 s'il n'en a pas.
 *
 * @return int Identifiant du résultat trouvé, 0 sinon.
 */
function resultat_existant( array $metas, int $chien_id ): int {
	$conditions = array( 'relation' => 'AND' );

	foreach ( array( '_mtb_discipline', '_mtb_annee', '_mtb_niveau' ) as $cle ) {
		$conditions[] = array(
			'key'     => $cle,
			'value'   => (string) assainir( 'resultats', $cle, $metas[ $cle ] ),
			'compare' => '=',
		);
	}

	$conditions[] = 0 < $chien_id
		? array(
			'key'     => '_mtb_chien_id',
			'value'   => $chien_id,
			'compare' => '=',
		)
		: array(
			'key'     => '_mtb_chien_nom',
			'value'   => (string) assainir( 'resultats', '_mtb_chien_nom', $metas['_mtb_chien_nom'] ),
			'compare' => '=',
		);

	$trouves = get_posts(
		array(
			'post_type'              => 'mtb_resultat',
			'post_status'            => 'any',
			'fields'                 => 'ids',
			'posts_per_page'         => 1,
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- l'identité d'un résultat est un tuple de champs, et l'import n'écrit pas de requête à la main.
			'meta_query'             => $conditions,
		)
	);

	return array() === $trouves ? 0 : (int) $trouves[0];
}
