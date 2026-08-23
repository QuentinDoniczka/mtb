<?php
/**
 * Résolution des références d'un fichier de fixtures vers des identifiants de contenu.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\ImportFixtures;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Un fichier de fixtures ne peut pas connaître un identifiant de contenu : il n'existe pas avant
 * l'import. Il désigne donc une fiche ou une photo par son SLUG, et ce fichier est le seul endroit
 * qui traduit un slug en identifiant.
 *
 * Jamais $wpdb, jamais une requête écrite à la main : get_page_by_path() interroge le cache
 * d'objets, voit tous les statuts — donc une fiche en brouillon aussi — et reste une API du cœur.
 */

/**
 * Identifiant d'une fiche Chien depuis sa référence.
 *
 * L'index de session évite de réinterroger la base pour une fiche déjà rencontrée, et retient
 * aussi bien les fiches créées par l'import que celles qui étaient déjà là.
 *
 * @param string             $reference Slug de la fiche.
 * @param array<string, int> $index     Index de session, complété par référence.
 *
 * @return int Identifiant, ou 0 si aucune fiche ne porte cette référence.
 */
function chien_par_reference( string $reference, array &$index ): int {
	if ( '' === $reference ) {
		return 0;
	}

	if ( isset( $index[ $reference ] ) ) {
		return (int) $index[ $reference ];
	}

	$fiche = get_page_by_path( $reference, OBJECT, 'mtb_chien' );

	if ( ! $fiche instanceof \WP_Post ) {
		return 0;
	}

	$index[ $reference ] = (int) $fiche->ID;

	return (int) $fiche->ID;
}

/**
 * Identifiant d'une pièce jointe depuis son slug.
 *
 * @param string $slug Slug de la pièce jointe.
 *
 * @return int Identifiant, ou 0 si la médiathèque n'en contient aucune sous ce slug.
 */
function piece_jointe_par_slug( string $slug ): int {
	if ( '' === $slug ) {
		return 0;
	}

	$piece = get_page_by_path( $slug, OBJECT, 'attachment' );

	return $piece instanceof \WP_Post ? (int) $piece->ID : 0;
}
