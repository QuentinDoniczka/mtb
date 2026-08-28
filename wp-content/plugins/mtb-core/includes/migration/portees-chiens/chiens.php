<?php
/**
 * Reprise des fiches Chien, en deux passes.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\PorteesChiens;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * POURQUOI DEUX PASSES, ET POURQUOI CE N'EST PAS NÉGOCIABLE
 *
 * Un chien référence un chien : une fiche a pour père une fiche qui figure PLUS LOIN dans le même
 * fichier. Une passe unique écrirait donc une filiation vide pour une partie du jeu, sans un mot —
 * et une généalogie muette en base est un fait faux qui attend qu'on le remarque. La première passe
 * crée toutes les fiches et mémorise « référence => identifiant » ; la seconde écrit les seules
 * clés qui pointent un autre contenu.
 *
 * Une fiche déjà présente traverse la seconde passe SANS RIEN ÉCRIRE : « créer si absent, laisser
 * strictement intact si présent » ne souffre aucune exception, pas même pour compléter.
 */

/**
 * Première passe : crée les fiches, sans aucune de leurs clés relationnelles.
 *
 * @param array<int, mixed>  $entrees Entrées du fichier.
 * @param string             $chemin  Chemin du fichier.
 * @param array<string, int> $index   Index « référence => identifiant », complété par référence.
 *
 * @return array<int, int> Index d'entrée => identifiant, pour les seules fiches créées.
 */
function creer_chiens( array $entrees, string $chemin, array &$index ): array {
	$fichier = nom_de_fichier( $chemin );
	$creees  = array();

	foreach ( $entrees as $rang => $entree ) {
		$rang        = (int) $rang;
		$identifiant = is_array( $entree ) ? texte_souple( isset( $entree['reference'] ) ? $entree['reference'] : null ) : '';
		$verdict     = controler( 'chiens', $entree );

		compter( 'chiens', 'lues' );

		if ( array() !== $verdict['rejets'] ) {
			rejeter( $fichier, $rang, $identifiant, $verdict['rejets'], 'chiens' );

			continue;
		}

		signaler_defauts( $fichier, $rang, $identifiant, $verdict['defauts'] );

		if ( 0 < chien_par_reference( $identifiant, $index ) ) {
			compter( 'chiens', 'present' );

			continue;
		}

		$champs  = champs_de_contenu( 'chiens', $entree );
		$metas   = valeurs_brutes( 'chiens', $entree );

		/*
		 * La conversion des marqueurs est recomptée ici pour être journalisée.
		 * convertir_les_marqueurs() est pure : cet appel et celui de champs_de_contenu() rendent
		 * rigoureusement le même texte, et c'est ce qui rend le compte fidèle à ce qui est écrit.
		 */
		$conversion = convertir_les_marqueurs( valeur( isset( $entree['texte_libre'] ) ? $entree['texte_libre'] : null ) );
		$post_id    = inserer( 'mtb_chien', $champs );

		if ( 0 === $post_id ) {
			rejeter( $fichier, $rang, $identifiant, array( 'WordPress a refusé la création de la fiche.' ), 'chiens' );

			continue;
		}

		ecrire_metas( $post_id, $metas );

		$index[ $identifiant ] = $post_id;
		$creees[ $rang ]       = $post_id;

		compter( 'chiens', 'cree' );
		noter_conversion( '' === $champs['post_title'] ? $identifiant : (string) $champs['post_title'], $conversion );

		if ( 0 < (int) $conversion['residus'] ) {
			signaler_residus( $identifiant, (int) $conversion['residus'] );
		}

		signaler_divergences( 'chiens', $fichier, $rang, $identifiant, controler_aval( 'chiens', $post_id, $metas, $champs ) );
	}

	return $creees;
}

/**
 * Seconde passe : filiation, galerie et image mise en avant des fiches créées.
 *
 * @param array<int, mixed>                   $entrees Entrées du fichier.
 * @param string                              $chemin  Chemin du fichier.
 * @param array<int, int>                     $creees  Index d'entrée => identifiant des fiches créées.
 * @param array<string, int>                  $index   Index « référence => identifiant ».
 * @param array<string, array<string, mixed>> $photos  Index des photographies.
 */
function completer_chiens( array $entrees, string $chemin, array $creees, array &$index, array $photos ): void {
	$fichier = nom_de_fichier( $chemin );

	foreach ( $creees as $rang => $post_id ) {
		$entree = $entrees[ $rang ];

		if ( ! is_array( $entree ) ) {
			continue;
		}

		$identifiant = texte_souple( isset( $entree['reference'] ) ? $entree['reference'] : null );
		$titre       = valeur( isset( $entree['nom_usage'] ) ? $entree['nom_usage'] : null );
		$metas       = array();
		$defauts     = array();

		foreach ( roles() as $role ) {
			$cle       = '_mtb_' . $role . '_fiche';
			$reference = texte_souple_de_groupe( $entree, $role, 'reference' );
			$fiche     = chien_par_reference( $reference, $index );

			if ( '' !== $reference && 0 === $fiche ) {
				// La fiche, elle, reste créée : elle est déjà écrite, et la reprise ne supprime jamais.
				$defauts[] = message_de_filiation_manquante( 'chiens', $role, $reference );
			}

			if ( 0 < $fiche ) {
				noter_liaison( '' === $titre ? $identifiant : $titre, $role, $reference, $fiche );
			}

			$metas[ $cle ] = 0 < $fiche ? $fiche : defaut_de( 'chiens', $cle );
		}

		/*
		 * Côté chien, la galerie est une CHAÎNE « 12,45,78 » — côté portée, c'est un tableau. Les
		 * deux modèles ont été gelés séparément et divergent sur ce point ; on écrit ce que chacun
		 * attend, sans chercher à les rapprocher depuis un module d'import.
		 */
		$galerie               = identifiants_de_photos( isset( $entree[ CLE_GALERIE ] ) ? $entree[ CLE_GALERIE ] : null );
		$metas['_mtb_galerie'] = implode( ',', pieces_rattachables( $galerie, $photos ) );

		ecrire_metas( $post_id, $metas );

		$portrait = portrait_possible(
			identifiants_de_photos( isset( $entree[ CLE_PHOTO ] ) ? $entree[ CLE_PHOTO ] : null ),
			$photos
		);

		if ( 0 < $portrait ) {
			definir_photo( $post_id, $portrait );

			/*
			 * Ajouté APRÈS l'écriture, et volontairement : set_post_thumbnail() a déjà posé la clé.
			 * Elle n'entre ici que pour que le contrôle aval relise le portrait comme il relit les
			 * autres clés — un portrait posé et non stocké serait sinon le seul écart muet.
			 */
			$metas['_thumbnail_id'] = $portrait;
		}

		signaler_defauts( $fichier, $rang, $identifiant, $defauts );
		signaler_divergences( 'chiens', $fichier, $rang, $identifiant, controler_aval( 'chiens', $post_id, $metas, array() ) );
	}
}
