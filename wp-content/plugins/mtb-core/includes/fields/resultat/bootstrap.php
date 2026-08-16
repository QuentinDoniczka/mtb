<?php
/**
 * Écran de saisie des résultats de travail.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Fields\Resultat;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/controle-chien.php';
require_once __DIR__ . '/ecran.php';
require_once __DIR__ . '/sauvegarde.php';
require_once __DIR__ . '/titre.php';

add_action( 'add_meta_boxes', __NAMESPACE__ . '\\ajouter_boite' );
add_action( 'save_post_mtb_resultat', __NAMESPACE__ . '\\enregistrer_champs', 10, 3 );
add_filter( 'post_updated_messages', __NAMESPACE__ . '\\messages_enregistrement' );
add_filter( 'bulk_post_updated_messages', __NAMESPACE__ . '\\messages_par_lot', 10, 2 );

/**
 * Déclare la boîte de saisie sur l'écran d'un résultat de travail.
 *
 * @param string $type Type de contenu de l'écran en cours.
 */
function ajouter_boite( string $type ): void {
	if ( 'mtb_resultat' !== $type ) {
		return;
	}

	add_meta_box(
		'mtb-resultat-saisie',
		'Résultat de travail',
		__NAMESPACE__ . '\\rendre_ecran',
		'mtb_resultat',
		'normal',
		'high'
	);

	/*
	 * WordPress ajoute d'office une boîte « Slug » à tout écran classique, et la propose dans les
	 * options de l'écran. Elle n'a aucun objet ici — un résultat n'a pas d'adresse publique — et son
	 * titre est un mot que le vocabulaire du projet proscrit. Elle est retirée après son
	 * enregistrement par le cœur, qui a lieu juste avant ce rappel.
	 */
	remove_meta_box( 'slugdiv', 'mtb_resultat', 'normal' );
}

/**
 * Remplace les messages d'enregistrement.
 *
 * Sans eux, l'écran livré par cette issue annoncerait « Article publié. » — un mot que le
 * vocabulaire du projet proscrit, sur l'écran même que l'éleveuse utilise le plus.
 *
 * @param array<string, array<int, string>> $messages Messages de tous les types de contenu.
 *
 * @return array<string, array<int, string>> Messages complétés.
 */
function messages_enregistrement( array $messages ): array {
	$messages['mtb_resultat'] = array(
		0  => '',
		1  => 'Résultat de travail mis à jour.',
		2  => 'Champ mis à jour.',
		3  => 'Champ supprimé.',
		4  => 'Résultat de travail mis à jour.',
		5  => 'Résultat de travail restauré à sa version précédente.',
		6  => 'Résultat de travail publié.',
		7  => 'Résultat de travail enregistré.',
		8  => 'Résultat de travail soumis à relecture.',
		9  => 'Résultat de travail planifié.',
		10 => 'Brouillon du résultat de travail mis à jour.',
	);

	return $messages;
}

/**
 * Remplace les messages des actions groupées de la liste.
 *
 * @param array<string, array<string, string>> $messages Messages de tous les types de contenu.
 * @param array<string, int>                   $comptes  Nombre de contenus concernés par action.
 *
 * @return array<string, array<string, string>> Messages complétés.
 */
function messages_par_lot( array $messages, array $comptes ): array {
	$messages['mtb_resultat'] = array(
		'updated'   => phrase( $comptes, 'updated', 'Résultat de travail mis à jour.', 'résultats de travail mis à jour.' ),
		'locked'    => phrase( $comptes, 'locked', 'Un résultat de travail n’a pas été modifié : quelqu’un d’autre est en train de le modifier.', 'résultats de travail n’ont pas été modifiés : quelqu’un d’autre est en train de les modifier.' ),
		'deleted'   => phrase( $comptes, 'deleted', 'Résultat de travail supprimé définitivement.', 'résultats de travail supprimés définitivement.' ),
		'trashed'   => phrase( $comptes, 'trashed', 'Résultat de travail mis à la corbeille.', 'résultats de travail mis à la corbeille.' ),
		'untrashed' => phrase( $comptes, 'untrashed', 'Résultat de travail sorti de la corbeille.', 'résultats de travail sortis de la corbeille.' ),
	);

	return $messages;
}

/**
 * Compose un message d'action groupée en accordant le nombre.
 *
 * @param array<string, int> $comptes   Nombres fournis par WordPress.
 * @param string             $cle       Action concernée.
 * @param string             $singulier Phrase complète au singulier.
 * @param string             $pluriel   Fin de phrase au pluriel, le nombre étant placé devant.
 *
 * @return string Phrase à afficher.
 */
function phrase( array $comptes, string $cle, string $singulier, string $pluriel ): string {
	$nombre = isset( $comptes[ $cle ] ) ? (int) $comptes[ $cle ] : 0;

	if ( 1 === $nombre ) {
		return $singulier;
	}

	return (string) $nombre . ' ' . $pluriel;
}
