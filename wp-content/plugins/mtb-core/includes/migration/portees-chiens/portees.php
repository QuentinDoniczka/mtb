<?php
/**
 * Reprise des portées.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\PorteesChiens;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Valeur du mode de saisie d'un parent qui exige une fiche.
 *
 * Seule valeur du modèle citée hors de « modele.php », et pour une seule raison : le modèle ne peut
 * pas exprimer qu'un parent déclaré par fiche doit porter une référence résolvable.
 */
const PARENT_PAR_FICHE = 'fiche';

/**
 * Importe les portées.
 *
 * @param array<int, mixed>                   $entrees Entrées du fichier.
 * @param string                              $chemin  Chemin du fichier.
 * @param array<string, int>                  $index   Index « référence de chien => identifiant ».
 * @param array<string, array<string, mixed>> $photos  Index des photographies.
 */
function importer_portees( array $entrees, string $chemin, array &$index, array $photos ): void {
	$fichier = nom_de_fichier( $chemin );

	foreach ( $entrees as $rang => $entree ) {
		$rang        = (int) $rang;
		$identifiant = is_array( $entree ) ? texte_souple( isset( $entree['identifiant'] ) ? $entree['identifiant'] : null ) : '';
		$verdict     = controler( 'portees', $entree );

		compter( 'portees', 'lues' );

		if ( array() !== $verdict['rejets'] ) {
			rejeter( $fichier, $rang, $identifiant, $verdict['rejets'], 'portees' );

			continue;
		}

		/*
		 * Les défauts de transcription sont dits AVANT le contrôle d'existence : ils portent sur le
		 * fichier, pas sur la base. Une portée déjà reprise dont la transcription est incomplète
		 * doit se signaler à chaque passage, sans quoi le premier import serait le seul moment de
		 * la vie du dépôt où l'oubli est visible.
		 */
		signaler_defauts( $fichier, $rang, $identifiant, $verdict['defauts'] );

		if ( 0 < portee_par_identifiant( $identifiant ) ) {
			compter( 'portees', 'present' );

			continue;
		}

		$parents = resoudre_les_parents( $entree, $index );

		if ( array() !== $parents['raisons'] ) {
			rejeter( $fichier, $rang, $identifiant, $parents['raisons'], 'portees' );

			continue;
		}

		$galerie = identifiants_de_photos( isset( $entree[ CLE_GALERIE ] ) ? $entree[ CLE_GALERIE ] : null );
		$champs  = champs_de_contenu( 'portees', $entree );
		$metas   = array_merge(
			valeurs_brutes( 'portees', $entree ),
			$parents['fiches'],
			array(
				'_mtb_chiots'  => chiots_bruts( $entree ),
				// Côté portée, la galerie est un TABLEAU d'entiers — côté chien, une chaîne.
				'_mtb_galerie' => pieces_rattachables( $galerie, $photos ),
			)
		);
		$conversion = convertir_les_marqueurs( valeur( isset( $entree['texte_libre'] ) ? $entree['texte_libre'] : null ) );
		$post_id    = inserer( 'mtb_portee', $champs );

		if ( 0 === $post_id ) {
			rejeter( $fichier, $rang, $identifiant, array( 'WordPress a refusé la création de la portée.' ), 'portees' );

			continue;
		}

		ecrire_metas( $post_id, $metas );

		foreach ( $parents['liaisons'] as $role => $reference ) {
			noter_liaison( $identifiant, (string) $role, (string) $reference, (int) $parents['fiches'][ '_mtb_' . $role . '_fiche' ] );
		}

		compter( 'portees', 'cree' );
		noter_conversion( $identifiant, $conversion );

		if ( 0 < (int) $conversion['residus'] ) {
			signaler_residus( $identifiant, (int) $conversion['residus'] );
		}

		signaler_divergences( 'portees', $fichier, $rang, $identifiant, controler_aval( 'portees', $post_id, $metas, $champs ) );
	}
}

/**
 * Résout les fiches du père et de la mère.
 *
 * Une référence qui ne désigne aucune fiche fait rejeter la portée ENTIÈRE, qui n'est pas écrite.
 * L'écrire malgré tout produirait une portée déclarée « parent par fiche » sans fiche : sur la
 * page, une généalogie muette, et en base un fait faux qui attendrait qu'on le remarque.
 *
 * @param array<string, mixed> $entree Entrée du fichier.
 * @param array<string, int>   $index  Index « référence de chien => identifiant ».
 *
 * @return array<string, array<string, mixed>> array{ fiches, liaisons, raisons }.
 */
function resoudre_les_parents( array $entree, array &$index ): array {
	$fiches   = array();
	$liaisons = array();
	$raisons  = array();

	foreach ( roles() as $role ) {
		$cle       = '_mtb_' . $role . '_fiche';
		$libelle   = libelle_de_role( $role );
		$type      = type_de_parent( 'portees', $entree, $role );
		$reference = texte_souple_de_groupe( $entree, $role, 'reference' );

		$fiches[ $cle ] = defaut_de( 'portees', $cle );

		if ( PARENT_PAR_FICHE !== $type ) {
			continue;
		}

		if ( '' === $reference ) {
			$raisons[] = sprintf(
				'le %s est saisi par fiche mais aucune référence n\'est donnée : la portée n\'est pas écrite.',
				$libelle
			);

			continue;
		}

		$fiche = chien_par_reference( $reference, $index );

		if ( 0 === $fiche ) {
			$raisons[] = message_de_filiation_manquante( 'portees', $role, $reference );

			continue;
		}

		$fiches[ $cle ]    = $fiche;
		$liaisons[ $role ] = $reference;
	}

	return array(
		'fiches'   => $fiches,
		'liaisons' => $liaisons,
		'raisons'  => $raisons,
	);
}
