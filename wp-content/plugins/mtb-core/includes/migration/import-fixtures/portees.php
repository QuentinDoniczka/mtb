<?php
/**
 * Import des portées.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\ImportFixtures;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Valeur du mode de saisie d'un parent qui exige une fiche.
 *
 * Seule valeur du modèle citée par l'import, et pour une seule raison : le modèle ne peut pas
 * exprimer qu'un parent déclaré par fiche doit porter une référence résolvable. Une portée dont le
 * père serait déclaré par fiche avec une fiche à zéro afficherait une généalogie muette — un fait
 * faux en attente — alors qu'elle a été saisie comme complète.
 */
const PARENT_PAR_FICHE = 'fiche';

/**
 * Importe les portées.
 *
 * @param array<int, mixed>  $entrees Entrées du fichier.
 * @param string             $chemin  Chemin du fichier.
 * @param array<string, int> $index   Index « référence de chien => identifiant ».
 * @param array<string, int> $photos  Index « slug de photo => identifiant ».
 */
function importer_portees( array $entrees, string $chemin, array &$index, array $photos ): void {
	$fichier = nom_de_fichier( $chemin );

	foreach ( $entrees as $rang => $entree ) {
		$rang        = (int) $rang;
		$identifiant = is_array( $entree ) ? texte_de( $entree, 'identifiant' ) : '';
		$raisons     = controler( 'portees', $entree );

		if ( array() !== $raisons ) {
			rejeter( $fichier, $rang, $identifiant, $raisons, 'portees' );

			continue;
		}

		if ( 0 < portee_par_identifiant( $identifiant ) ) {
			compter( 'portees', 'present' );

			continue;
		}

		$parents = resoudre_les_parents( $entree, $index );

		if ( array() !== $parents['raisons'] ) {
			rejeter( $fichier, $rang, $identifiant, $parents['raisons'], 'portees' );

			continue;
		}

		$champs  = champs_de_contenu( 'portees', $entree );
		$metas   = array_merge(
			valeurs_brutes( 'portees', $entree ),
			$parents['fiches'],
			array( '_mtb_galerie' => identifiants_de_photos( isset( $entree['galerie'] ) ? $entree['galerie'] : null, $photos ) )
		);
		$post_id = inserer( 'mtb_portee', $champs );

		if ( 0 === $post_id ) {
			rejeter( $fichier, $rang, $identifiant, array( 'WordPress a refusé la création de la portée.' ), 'portees' );

			continue;
		}

		ecrire_metas( $post_id, $metas );

		compter( 'portees', 'cree' );
		signaler_divergences( 'portees', $fichier, $rang, $identifiant, controler_aval( 'portees', $post_id, $metas, $champs ) );
	}
}

/**
 * Résout les fiches du père et de la mère.
 *
 * Une référence qui ne désigne aucune fiche fait rejeter la portée ENTIÈRE, qui n'est pas écrite.
 * L'écrire malgré tout produirait une portée déclarée « parent par fiche » sans fiche : sur la page,
 * une généalogie muette, et en base un fait faux qui attendrait qu'on le remarque.
 *
 * @param array<string, mixed> $entree Entrée du fichier.
 * @param array<string, int>   $index  Index « référence de chien => identifiant ».
 *
 * @return array<string, array<string, mixed>> array{ fiches, raisons }.
 */
function resoudre_les_parents( array $entree, array &$index ): array {
	$fiches  = array();
	$raisons = array();

	foreach ( roles() as $role ) {
		$cle       = '_mtb_' . $role . '_fiche';
		$libelle   = libelle_de_role( $role );
		$type      = assainir( 'portees', '_mtb_' . $role . '_type', valeur_brute( 'portees', $entree, '_mtb_' . $role . '_type' ) );
		$reference = texte_de_groupe( $entree, $role, 'reference' );

		$fiches[ $cle ] = defaut_de( 'portees', $cle );

		if ( PARENT_PAR_FICHE !== $type ) {
			continue;
		}

		if ( '' === $reference ) {
			$raisons[] = sprintf( 'le %s est saisi par fiche mais aucune référence n\'est donnée : la portée n\'est pas écrite.', $libelle );

			continue;
		}

		$fiche = chien_par_reference( $reference, $index );

		if ( 0 === $fiche ) {
			$raisons[] = sprintf(
				'aucune fiche ne porte la référence « %s » indiquée comme %s : la portée n\'est pas écrite.',
				$reference,
				$libelle
			);

			continue;
		}

		$fiches[ $cle ] = $fiche;
	}

	return array(
		'fiches'  => $fiches,
		'raisons' => $raisons,
	);
}

/**
 * Identifiant d'une portée depuis son identifiant métier, quel que soit son statut.
 *
 * get_posts() et non mtb_get_portee_par_identifiant() : la fonction de lecture fige
 * « post_status => publish » et « has_password => false » (query/portee/hydratation.php), si bien
 * qu'une portée passée en brouillon serait recréée en double à la prochaine exécution. Ce que le
 * contrat gèle est la CLÉ d'identité — l'identifiant — pas la fonction qui la lit.
 *
 * @param string $identifiant Identifiant métier, qui est le titre de la portée.
 *
 * @return int Identifiant de contenu, 0 si aucune portée ne porte ce titre.
 */
function portee_par_identifiant( string $identifiant ): int {
	if ( '' === $identifiant ) {
		return 0;
	}

	$trouvees = get_posts(
		array(
			'post_type'              => 'mtb_portee',
			'title'                  => $identifiant,
			'post_status'            => 'any',
			'fields'                 => 'ids',
			'posts_per_page'         => 1,
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	return array() === $trouvees ? 0 : (int) $trouvees[0];
}
