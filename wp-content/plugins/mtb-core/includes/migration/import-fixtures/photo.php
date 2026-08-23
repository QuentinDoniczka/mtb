<?php
/**
 * Versement des photos de démonstration dans la médiathèque.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\ImportFixtures;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Aucun appel réseau : les images sont lues sur le disque local, dans un dossier DÉRIVÉ de celui du
 * fichier de fixtures qui les cite — « /fixtures/portees.json » donne « /fixtures/photos/ ». C'est
 * ce qui laisse la signature de la commande intacte, donc « docker/provision/provision.sh » hors
 * d'atteinte, sans jamais coder un chemin en dur.
 *
 * Le dossier des fixtures est monté en lecture seule et media_handle_sideload() DÉPLACE le fichier
 * qu'on lui donne : l'image est donc recopiée dans le dossier temporaire avant d'être versée.
 */

/**
 * Texte alternatif des photos de démonstration.
 *
 * Recopié au caractère près depuis « docker/provision/provision.sh », qui verse la même image tant
 * que son étape « photo de test portrait » subsiste. Cette duplication est portée en dette T-#29-c :
 * elle disparaîtra avec cette étape, et jusque-là les deux littéraux ne doivent pas diverger.
 */
const TEXTE_ALTERNATIF = "Image de test synthétique, cadrage portrait — jamais une vraie photo d'élevage";

/**
 * Dossier des photos d'un jeu, dérivé du chemin de son fichier.
 *
 * @param string $chemin_json Chemin du fichier de fixtures.
 *
 * @return string Chemin du dossier des photos.
 */
function dossier_des_photos( string $chemin_json ): string {
	return rtrim( dirname( $chemin_json ), '/\\' ) . '/photos';
}

/**
 * Slugs de photos cités par un jeu, dans l'ordre du fichier.
 *
 * @param string            $jeu     Jeu de fixtures.
 * @param array<int, mixed> $entrees Entrées du fichier.
 *
 * @return string[] Slugs, sans doublon.
 */
function slugs_references( string $jeu, array $entrees ): array {
	$cles = array(
		'chiens'  => array( CLE_PHOTO, 'galerie' ),
		'portees' => array( 'galerie' ),
	);

	if ( ! isset( $cles[ $jeu ] ) ) {
		return array();
	}

	$slugs = array();

	foreach ( $entrees as $entree ) {
		if ( ! is_array( $entree ) ) {
			continue;
		}

		foreach ( $cles[ $jeu ] as $cle ) {
			if ( ! isset( $entree[ $cle ] ) ) {
				continue;
			}

			foreach ( slugs_de_photos( $entree[ $cle ] ) as $slug ) {
				if ( ! in_array( $slug, $slugs, true ) ) {
					$slugs[] = $slug;
				}
			}
		}
	}

	return $slugs;
}

/**
 * S'assure que toutes les photos citées sont dans la médiathèque.
 *
 * @param array<string, array<int, mixed>> $entrees Entrées par jeu.
 * @param array<string, string>            $chemins Chemin du fichier par jeu.
 *
 * @return array<string, int> Slug => identifiant de pièce jointe, 0 si la photo manque.
 */
function garantir_les_photos( array $entrees, array $chemins ): array {
	$index = array();

	foreach ( $entrees as $jeu => $liste ) {
		$dossier = dossier_des_photos( $chemins[ $jeu ] );

		foreach ( slugs_references( (string) $jeu, $liste ) as $slug ) {
			if ( isset( $index[ $slug ] ) ) {
				continue;
			}

			$index[ $slug ] = garantir( $slug, $dossier );
		}
	}

	return $index;
}

/**
 * Verse une photo si la médiathèque ne la contient pas déjà.
 *
 * Une photo trouvée n'est jamais retouchée : ni son titre, ni son texte alternatif, ni son fichier.
 *
 * @param string $slug    Slug de la pièce jointe.
 * @param string $dossier Dossier où chercher le fichier.
 *
 * @return int Identifiant de la pièce jointe, 0 si la photo n'a pas pu être versée.
 */
function garantir( string $slug, string $dossier ): int {
	$existante = piece_jointe_par_slug( $slug );

	if ( 0 < $existante ) {
		photo_presente( $slug );

		return $existante;
	}

	$source = fichier_de_photo( $slug, $dossier );

	if ( '' === $source ) {
		photo_absente( $slug, $dossier );

		return 0;
	}

	$piece_jointe = verser( $slug, $source );

	if ( 0 === $piece_jointe ) {
		photo_absente( $slug, $dossier );

		return 0;
	}

	photo_importee( $slug );

	return $piece_jointe;
}

/**
 * Cherche le fichier d'une photo dans le dossier, quelle que soit son extension.
 *
 * Comparaison de nom exacte plutôt que motif de recherche : un slug est une donnée de fichier, et
 * un motif lui donnerait un sens qu'il n'a pas.
 *
 * @param string $slug    Slug cherché.
 * @param string $dossier Dossier des photos.
 *
 * @return string Chemin du fichier, chaîne vide s'il n'existe pas.
 */
function fichier_de_photo( string $slug, string $dossier ): string {
	if ( ! is_dir( $dossier ) ) {
		return '';
	}

	$entrees = scandir( $dossier );

	if ( false === $entrees ) {
		return '';
	}

	foreach ( $entrees as $entree ) {
		$chemin = $dossier . '/' . $entree;

		if ( ! is_file( $chemin ) ) {
			continue;
		}

		if ( pathinfo( $entree, PATHINFO_FILENAME ) === $slug ) {
			return $chemin;
		}
	}

	return '';
}

/**
 * Recopie l'image hors du montage en lecture seule, puis la verse dans la médiathèque.
 *
 * @param string $slug   Slug voulu pour la pièce jointe.
 * @param string $source Chemin du fichier d'origine.
 *
 * @return int Identifiant de la pièce jointe, 0 en cas d'échec.
 */
function verser( string $slug, string $source ): int {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$nom        = basename( $source );
	$temporaire = wp_tempnam( $nom );

	if ( '' === $temporaire || ! copy( $source, $temporaire ) ) {
		return 0;
	}

	$piece_jointe = media_handle_sideload(
		array(
			'name'     => $nom,
			'tmp_name' => $temporaire,
		),
		0,
		null,
		array(
			'post_title' => wp_slash( $slug ),
			'post_name'  => wp_slash( $slug ),
		)
	);

	if ( is_wp_error( $piece_jointe ) ) {
		// media_handle_sideload() ne déplace pas le fichier quand il échoue : à l'import de le retirer.
		if ( file_exists( $temporaire ) ) {
			wp_delete_file( $temporaire );
		}

		return 0;
	}

	update_post_meta( (int) $piece_jointe, '_wp_attachment_image_alt', wp_slash( TEXTE_ALTERNATIF ) );

	return (int) $piece_jointe;
}
