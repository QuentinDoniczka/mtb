<?php
/**
 * Import des fiches Chien, en deux passes.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\ImportFixtures;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * POURQUOI DEUX PASSES
 *
 * Un chien référence un chien : « demo-rex » a « demo-cesar » pour père, et « demo-cesar » figure
 * plus loin dans le même fichier. Une passe unique écrirait donc une filiation vide pour la moitié
 * du jeu, sans un mot. La première passe crée toutes les fiches et mémorise « référence =>
 * identifiant » ; la seconde écrit les quatre clés qui pointent un autre contenu.
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
		$identifiant = is_array( $entree ) ? texte_de( $entree, 'reference' ) : '';
		$raisons     = controler( 'chiens', $entree );

		if ( array() !== $raisons ) {
			rejeter( $fichier, $rang, $identifiant, $raisons, 'chiens' );

			continue;
		}

		if ( 0 < chien_par_reference( $identifiant, $index ) ) {
			compter( 'chiens', 'present' );

			continue;
		}

		$champs  = champs_de_contenu( 'chiens', $entree );
		$metas   = valeurs_brutes( 'chiens', $entree );
		$post_id = inserer( 'mtb_chien', $champs );

		if ( 0 === $post_id ) {
			rejeter( $fichier, $rang, $identifiant, array( 'WordPress a refusé la création de la fiche.' ), 'chiens' );

			continue;
		}

		ecrire_metas( $post_id, $metas );

		$index[ $identifiant ] = $post_id;
		$creees[ $rang ]       = $post_id;

		compter( 'chiens', 'cree' );
		signaler_divergences( 'chiens', $fichier, $rang, $identifiant, controler_aval( 'chiens', $post_id, $metas, $champs ) );
	}

	return $creees;
}

/**
 * Seconde passe : filiation, photo principale et galerie des fiches créées.
 *
 * @param array<int, mixed>  $entrees Entrées du fichier.
 * @param string             $chemin  Chemin du fichier.
 * @param array<int, int>    $creees  Index d'entrée => identifiant des fiches créées.
 * @param array<string, int> $index   Index « référence => identifiant ».
 * @param array<string, int> $photos  Index « slug de photo => identifiant ».
 */
function completer_chiens( array $entrees, string $chemin, array $creees, array &$index, array $photos ): void {
	$fichier = nom_de_fichier( $chemin );

	foreach ( $creees as $rang => $post_id ) {
		$entree = $entrees[ $rang ];

		if ( ! is_array( $entree ) ) {
			continue;
		}

		$identifiant = texte_de( $entree, 'reference' );
		$metas       = array();

		foreach ( roles() as $role ) {
			$cle       = '_mtb_' . $role . '_fiche';
			$reference = texte_de_groupe( $entree, $role, 'reference' );
			$fiche     = chien_par_reference( $reference, $index );

			if ( '' !== $reference && 0 === $fiche ) {
				/*
				 * La fiche, elle, reste créée : elle est déjà écrite, et l'import ne supprime
				 * jamais. Seule la filiation est rejetée, et elle est nommée.
				 */
				rejeter_filiation( $fichier, $rang, $identifiant, $role, $reference );
			}

			$metas[ $cle ] = 0 < $fiche ? $fiche : defaut_de( 'chiens', $cle );
		}

		$metas['_mtb_galerie'] = identifiants_de_photos( isset( $entree['galerie'] ) ? $entree['galerie'] : null, $photos );

		ecrire_metas( $post_id, $metas );

		$portrait = photo_principale( $entree, $photos );

		if ( 0 < $portrait ) {
			definir_photo( $post_id, $portrait );

			$metas['_thumbnail_id'] = $portrait;
		}

		signaler_divergences( 'chiens', $fichier, $rang, $identifiant, controler_aval( 'chiens', $post_id, $metas, array() ) );
	}
}

/**
 * Identifiant de la photo principale d'une entrée.
 *
 * Une photo introuvable ne fait jamais rejeter l'entrée : une fiche sans portrait est un état de
 * rendu prévu, une généalogie muette ne l'est pas. La clé « _thumbnail_id » n'est alors pas écrite.
 *
 * @param array<string, mixed> $entree Entrée du fichier.
 * @param array<string, int>   $photos Index des photos.
 *
 * @return int Identifiant de la pièce jointe, 0 s'il n'y en a pas.
 */
function photo_principale( array $entree, array $photos ): int {
	$slugs = slugs_de_photos( isset( $entree[ CLE_PHOTO ] ) ? $entree[ CLE_PHOTO ] : null );

	foreach ( $slugs as $slug ) {
		if ( isset( $photos[ $slug ] ) && 0 < $photos[ $slug ] ) {
			return (int) $photos[ $slug ];
		}
	}

	return 0;
}

/**
 * Traduit une liste de slugs de photos en identifiants, dans l'ordre du fichier.
 *
 * L'ordre porte le sens d'une galerie : il est conservé tel quel. Un slug introuvable est écarté,
 * l'avertissement ayant déjà été émis au moment du versement.
 *
 * @param mixed              $brut   Valeur brute de la clé « galerie ».
 * @param array<string, int> $photos Index des photos.
 *
 * @return array<int, int> Identifiants, liste vide si aucune photo n'est disponible.
 */
function identifiants_de_photos( $brut, array $photos ): array {
	$identifiants = array();

	foreach ( slugs_de_photos( $brut ) as $slug ) {
		if ( isset( $photos[ $slug ] ) && 0 < $photos[ $slug ] ) {
			$identifiants[] = (int) $photos[ $slug ];
		}
	}

	return $identifiants;
}
