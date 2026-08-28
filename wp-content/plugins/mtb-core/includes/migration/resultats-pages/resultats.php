<?php
/**
 * Reprise des soixante et un résultats de travail de l'ancien site.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\ResultatsPages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * UN PIÈGE INVERSÉ DÉLIBÉRÉMENT
 *
 * « import-fixtures/resultats.php:40-50 » REJETTE une entrée dont la référence de chien ne résout
 * pas. C'est juste pour un jeu de démonstration fictif et clos : chaque fiche y est créée par la
 * même commande, une référence qui ne résout pas y est forcément une faute de frappe.
 *
 * C'est FAUX ici, et le contraire est fait. Sur les soixante et un résultats de l'ancien site,
 * SOIXANTE nomment un chien qui n'a aucune fiche sur le nouveau site — ce n'est pas une faute, c'est
 * l'état du domaine. Appliquer la règle des fixtures ferait disparaître soixante résultats sur
 * soixante et un. Ici : nom écrit verbatim, « _mtb_chien_id » à zéro, avertissement, et CODE DE
 * SORTIE INCHANGÉ. L'état « chien_sans_fiche » est un état de rendu prévu, pas une panne.
 *
 * L'ORDRE DU FICHIER EST L'ORDRE DE LA SOURCE, ET CE N'EST PAS COSMÉTIQUE
 *
 * « query/resultat/interne.php:505 » départage deux lignes de même année par identifiant de
 * contenu, donc par ordre de création. Créer dans l'ordre du fichier reproduit l'ordre du site
 * source SANS qu'aucun champ de tri ne soit inventé.
 */

/**
 * Reprend les résultats de travail.
 *
 * @param array<int, mixed>     $entrees Entrées du fichier.
 * @param string                $chemin  Chemin du fichier.
 * @param array<string, string> $paires  Nom de chien => slug de la fiche.
 * @param bool                  $simuler Vrai pour ne rien écrire.
 */
function importer_resultats( array $entrees, string $chemin, array $paires, bool $simuler ): void {
	$fichier = nom_de_fichier( $chemin );
	$index   = array();

	foreach ( $entrees as $rang => $entree ) {
		$rang        = (int) $rang;
		$identifiant = is_array( $entree ) ? identifiant_de_resultat( $entree ) : 'entrée sans identifiant';
		$raisons     = controler_resultat( $entree );

		if ( array() !== $raisons ) {
			rejeter( $fichier, $rang, $identifiant, $raisons, 'resultats' );

			continue;
		}

		$nom      = texte_de( $entree, 'chien_nom' );
		$chien_id = fiche_du_chien( $nom, $paires, $index );
		$metas    = array_merge( valeurs_brutes_resultat( $entree ), array( '_mtb_chien_id' => $chien_id ) );

		if ( 0 < resultat_existant( $metas ) ) {
			compter( 'resultats', 'present' );

			continue;
		}

		if ( $simuler ) {
			compter( 'resultats', 'cree' );

			continue;
		}

		/*
		 * Titre provisoire : le niveau, que le tuple d'identité rend obligatoire. Le titre définitif
		 * est composé par le serveur une fois les champs écrits — c'est lui qui lit les valeurs
		 * enregistrées, et il n'existe qu'une seule composition de ce titre dans le projet.
		 */
		$champs = array( 'post_title' => (string) assainir( '_mtb_niveau', $metas['_mtb_niveau'] ) );

		$post_id = inserer( 'mtb_resultat', $champs );

		if ( 0 === $post_id ) {
			rejeter( $fichier, $rang, $identifiant, array( 'WordPress a refusé la création du résultat.' ), 'resultats' );

			continue;
		}

		ecrire_metas( $post_id, $metas );

		$champs['post_title'] = achever_le_titre( $post_id, $champs['post_title'] );

		compter( 'resultats', 'cree' );

		if ( 0 < $chien_id ) {
			compter_lien( 'raccroches' );
		}

		signaler_divergences(
			'resultats',
			$fichier,
			$rang,
			$identifiant,
			controler_aval_resultat( $post_id, $metas, $champs )
		);
	}
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

	foreach ( cles_didentite_resultat() as $cle ) {
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
 * Discipline, nom du chien, année, niveau : les quatre clés que le fichier rend obligatoires. Le
 * chien est cherché par son NOM et non par sa fiche, puisque soixante des soixante et un n'en ont
 * pas — chercher par fiche ferait recréer les mêmes lignes à chaque exécution, en silence.
 *
 * @param array<string, mixed> $metas Valeurs brutes des champs du résultat.
 *
 * @return int Identifiant du résultat trouvé, 0 sinon.
 */
function resultat_existant( array $metas ): int {
	$conditions = array( 'relation' => 'AND' );

	foreach ( metas_didentite_resultat() as $cle ) {
		$conditions[] = array(
			'key'     => $cle,
			'value'   => (string) assainir( $cle, $metas[ $cle ] ),
			'compare' => '=',
		);
	}

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
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- l'identité d'un résultat est un tuple de champs, et la reprise n'écrit pas de requête à la main.
			'meta_query'             => $conditions,
		)
	);

	return array() === $trouves ? 0 : (int) $trouves[0];
}

/**
 * Compte les résultats de démonstration semés par le provisionnement.
 *
 * L'importeur DÉTECTE et AVERTIT ; il ne supprime jamais rien. Supprimer du contenu qui n'est pas
 * le sien est hors de tout mandat, et ces cinq lignes appartiennent à la pile de développement.
 *
 * @return int Nombre de résultats de démonstration présents.
 */
function compter_les_fixtures(): int {
	$trouves = get_posts(
		array(
			'post_type'              => 'mtb_resultat',
			'post_status'            => 'any',
			'fields'                 => 'ids',
			'posts_per_page'         => -1,
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_term_cache' => false,
		)
	);

	$combien = 0;

	foreach ( $trouves as $post_id ) {
		if ( est_une_fixture( (int) $post_id ) ) {
			++$combien;
		}
	}

	return $combien;
}

/**
 * Un résultat porte-t-il l'un des deux marqueurs du jeu de démonstration ?
 *
 * Les cinq lignes semées par « docker/provision/provision.sh » se reconnaissent à l'affixe
 * « de Démonstration » de leur chien, ou à la référence « demo-… » de la fiche qu'elles citent.
 *
 * @param int $post_id Identifiant du résultat.
 *
 * @return bool Vrai si le résultat vient du jeu de démonstration.
 */
function est_une_fixture( int $post_id ): bool {
	$nom = (string) get_post_meta( $post_id, '_mtb_chien_nom', true );

	if ( false !== strpos( $nom, 'de Démonstration' ) ) {
		return true;
	}

	$chien_id = (int) get_post_meta( $post_id, '_mtb_chien_id', true );

	if ( 0 === $chien_id ) {
		return false;
	}

	$fiche = get_post( $chien_id );

	return $fiche instanceof \WP_Post && 0 === strpos( (string) $fiche->post_name, 'demo-' );
}
