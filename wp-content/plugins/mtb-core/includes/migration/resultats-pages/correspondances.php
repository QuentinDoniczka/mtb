<?php
/**
 * Rattachement d'un résultat de travail à une fiche chien.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\ResultatsPages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * POURQUOI LE RATTACHEMENT VIT DANS UN FICHIER À PART
 *
 * « Chien lié quand identifiable » est une formule creuse : en l'état, c'est une invitation à
 * fabriquer des liens. L'affixe « du Mont Brabant » n'est pas un critère — dix-neuf noms le portent
 * et n'ont aucune des dix-sept fiches.
 *
 * Le fichier des résultats n'a donc AUCUNE clé pour désigner une fiche. Le rattachement s'écrit
 * ailleurs, une paire à la fois, chacune avec sa justification écrite — c'est ce qui rend impossible
 * de faire entrer « très probablement le même chien » sans que quelqu'un ait à l'écrire et à le
 * signer.
 *
 * Le slug est résolu À L'EXÉCUTION, jamais gelé dans un fichier. Fiche absente : pas de lien, nom
 * verbatim, avertissement, code de sortie INCHANGÉ. C'est ce qui rend les deux reprises — celle des
 * fiches chien et celle-ci — indépendantes de l'ordre dans lequel elles tournent.
 */

/**
 * Lit le fichier des correspondances et rend les paires acceptables.
 *
 * @param string   $chemin Chemin du fichier.
 * @param string[] $noms   Noms de chien portés par les résultats transcrits.
 *
 * @return array<string, string> Nom de chien => slug de la fiche.
 */
function lire_les_correspondances( string $chemin, array $noms ): array {
	if ( ! is_file( $chemin ) ) {
		annoncer( sprintf( 'Aucun fichier de correspondances (%s) : aucun résultat ne sera lié à une fiche.', $chemin ) );

		return array();
	}

	$fichier = nom_de_fichier( $chemin );
	$paires  = array();

	foreach ( lire_liste( $chemin ) as $rang => $entree ) {
		$rang        = (int) $rang;
		$identifiant = is_array( $entree ) ? texte_de( $entree, 'chien_nom' ) : 'entrée sans nom';
		$raisons     = controler_correspondance( $entree, $noms );

		if ( array() !== $raisons ) {
			rejeter( $fichier, $rang, '' === $identifiant ? 'entrée sans nom' : $identifiant, $raisons );

			continue;
		}

		$paires[ (string) $entree['chien_nom'] ] = texte_de( $entree, 'reference' );
	}

	return $paires;
}

/**
 * Identifiant d'une fiche chien depuis sa référence, résolu à l'exécution.
 *
 * Jamais $wpdb, jamais une requête écrite à la main : get_page_by_path() interroge le cache
 * d'objets, voit tous les statuts — donc une fiche en brouillon aussi — et reste une API du cœur.
 *
 * @param string             $reference Slug de la fiche.
 * @param array<string, int> $index     Index de session, complété par référence.
 *
 * @return int Identifiant, 0 si aucune fiche ne porte cette référence.
 */
function chien_par_reference( string $reference, array &$index ): int {
	if ( '' === $reference ) {
		return 0;
	}

	if ( isset( $index[ $reference ] ) ) {
		return (int) $index[ $reference ];
	}

	$fiche = get_page_by_path( $reference, OBJECT, 'mtb_chien' );
	$id    = $fiche instanceof \WP_Post ? (int) $fiche->ID : 0;

	$index[ $reference ] = $id;

	return $id;
}

/**
 * Identifiant de la fiche du chien nommé, ou 0 avec un avertissement.
 *
 * @param string                $nom    Nom du chien, verbatim.
 * @param array<string, string> $paires Nom de chien => slug de la fiche.
 * @param array<string, int>    $index  Index de session.
 *
 * @return int Identifiant de la fiche, 0 si aucune ne convient.
 */
function fiche_du_chien( string $nom, array $paires, array &$index ): int {
	if ( ! isset( $paires[ $nom ] ) ) {
		return 0;
	}

	$reference = $paires[ $nom ];
	$chien_id  = chien_par_reference( $reference, $index );

	if ( 0 === $chien_id ) {
		lien_non_resolu( $nom, $reference );
	}

	return $chien_id;
}

/**
 * Mode « --raccrocher » : pose les liens manquants sur les résultats déjà importés.
 *
 * Idempotent, et lançable autant de fois qu'on veut. Il n'écrit que sur les résultats dont le champ
 * vaut ACTUELLEMENT zéro : un lien posé à la main par l'éleveuse n'est jamais écrasé.
 *
 * @param array<string, string> $paires  Nom de chien => slug de la fiche.
 * @param bool                  $simuler Vrai pour ne rien écrire.
 */
function raccrocher( array $paires, bool $simuler ): void {
	$index = array();

	foreach ( $paires as $nom => $reference ) {
		$chien_id = chien_par_reference( (string) $reference, $index );

		if ( 0 === $chien_id ) {
			lien_non_resolu( (string) $nom, (string) $reference );

			continue;
		}

		foreach ( resultats_du_nom( (string) $nom ) as $post_id ) {
			if ( 0 !== (int) get_post_meta( $post_id, '_mtb_chien_id', true ) ) {
				compter_lien( 'deja_lies' );

				continue;
			}

			if ( $simuler ) {
				compter_lien( 'raccroches' );
				noter( sprintf( 'Résultat %d : le lien vers la fiche « %s » serait posé.', $post_id, $reference ) );

				continue;
			}

			if ( poser_le_lien_chien( $post_id, $chien_id ) ) {
				compter_lien( 'raccroches' );
			}
		}
	}
}

/**
 * Résultats de travail portant ce nom de chien, tous statuts confondus.
 *
 * @param string $nom Nom du chien, verbatim.
 *
 * @return int[] Identifiants de résultats.
 */
function resultats_du_nom( string $nom ): array {
	$trouves = get_posts(
		array(
			'post_type'              => 'mtb_resultat',
			'post_status'            => 'any',
			'fields'                 => 'ids',
			'posts_per_page'         => -1,
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- le chien d'un résultat est un champ, et la reprise n'écrit aucune requête à la main.
			'meta_query'             => array(
				array(
					'key'     => '_mtb_chien_nom',
					'value'   => (string) assainir( '_mtb_chien_nom', $nom ),
					'compare' => '=',
				),
			),
		)
	);

	return array_map( 'intval', $trouves );
}
