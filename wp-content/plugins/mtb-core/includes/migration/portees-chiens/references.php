<?php
/**
 * Résolution des clés d'identité vers des identifiants de contenu.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\PorteesChiens;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * POURQUOI PAS LES FONCTIONS DE LECTURE PUBLIQUES
 *
 * mtb_get_chien() et mtb_get_portee_par_identifiant() figent « post_status => publish » et
 * « has_password => false » : c'est ce qu'il faut pour une page, et c'est exactement ce qu'il ne
 * faut pas ici. Une fiche que l'éleveuse aurait repassée en brouillon serait vue comme absente, et
 * la reprise la recréerait EN DOUBLE au rejeu, sans un mot. Ce qui est gelé par le contrat, c'est
 * la CLÉ d'identité — le slug d'un chien, le titre d'une portée — pas la fonction qui la lit.
 *
 * Jamais $wpdb, jamais une requête écrite à la main : get_page_by_path() interroge le cache
 * d'objets, voit tous les statuts, et reste une API du cœur.
 */

/**
 * Identifiant d'une fiche Chien depuis sa référence, quel que soit son statut.
 *
 * L'index de session évite de réinterroger la base pour une fiche déjà rencontrée, et retient
 * aussi bien les fiches créées par la reprise que celles qui étaient déjà là.
 *
 * @param string             $reference Slug de la fiche, qui est son « post_name ».
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
 * Identifiant d'une portée depuis son identifiant métier, quel que soit son statut.
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

/**
 * Identifiant d'une pièce jointe depuis l'identifiant IONOS qui lui sert de slug.
 *
 * @param string $identifiant Identifiant IONOS.
 *
 * @return int Identifiant de contenu, 0 si la médiathèque n'en contient aucune sous ce slug.
 */
function piece_jointe_par_identifiant( string $identifiant ): int {
	if ( '' === $identifiant ) {
		return 0;
	}

	$piece = get_page_by_path( $identifiant, OBJECT, 'attachment' );

	return $piece instanceof \WP_Post ? (int) $piece->ID : 0;
}

/**
 * Identifiant d'une entité déjà reprise, selon son jeu.
 *
 * @param string             $jeu   « chiens » ou « portees ».
 * @param string             $cle   Référence d'un chien, ou identifiant d'une portée.
 * @param array<string, int> $index Index de session des fiches Chien.
 *
 * @return int Identifiant de contenu, 0 si l'entité n'existe pas.
 */
function entite_existante( string $jeu, string $cle, array &$index ): int {
	return 'chiens' === $jeu ? chien_par_reference( $cle, $index ) : portee_par_identifiant( $cle );
}
