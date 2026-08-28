<?php
/**
 * Versement des photos citées par les pages reprises.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\ResultatsPages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * LE TÉLÉVERSEMENT EST DIFFÉRÉ, ET C'EST UN MANQUE DÉCLARÉ, PAS UNE CASE COCHÉE (arbitrage A5).
 *
 * Les trois photos des pages Travail et BHPL vivent dans « docs/migration/source/photos/ », que
 * « compose.yaml:109 » monte désormais en lecture seule sur le conteneur « wpcli » : elles y sont
 * ATTEIGNABLES. Ce n'est donc plus le montage qui les retient — cet argument-là, invoqué à
 * l'écriture de ce module, est tombé dans le même lot, et il est retiré plutôt qu'entretenu.
 *
 * Deux raisons tiennent encore, et elles suffisent. La première : l'extension ne recopie pas les
 * 1 125 604 octets de ces images chez elle — dupliquer une archive crée une seconde source de
 * vérité pour une photographie, et l'engage dans git pour toujours. La seconde, et c'est elle qui
 * bloque vraiment : AUCUNE des trois ne porte de texte alternatif dans la capture, et personne ici
 * ne peut en inventer un. Aucune ne peut donc être reprise CORRECTEMENT aujourd'hui, montage ou
 * pas — c'est une question ouverte à l'éleveuse, dette T-#21-a.
 *
 * Conséquence tenue : les pages sont créées sans photo, l'état « photo_absente » est déjà géré par
 * les composants — l'emplacement n'existe pas, aucun trou ni réserve n'est rendu — et le code de
 * sortie reste 0.
 *
 * Aucun appel réseau : les images seraient lues sur le disque local, dans le dossier fourni par
 * « --photos », qui n'a volontairement aucune valeur par défaut. À défaut d'option, le module
 * regarde son propre dossier « photos/ », qui existe et ne contient que son « .gitkeep » : c'est ce
 * qui rend le message d'absence exact plutôt qu'approximatif.
 *
 * Ce fichier décide QUELLES photos sont nécessaires et lesquelles manquent ; il n'écrit rien. Le
 * versement lui-même — « verser() » — vit dans « ecriture.php », avec toutes les autres écritures du
 * module : c'est ce qui laisse vraie la propriété « un seul fichier écrit », vérifiable par une
 * recherche et non sur parole.
 */

/**
 * Les trois fichiers de photo que la reprise attend, nommés pour que le message soit vérifiable.
 *
 * Ce sont les noms tels qu'ils figurent dans l'archive de capture. La reprise ne les invente pas :
 * elle ne les trouve pas.
 */
const PHOTOS_ATTENDUES = array( '16497476.png', '6412830.jpg', '7128435.png' );

/**
 * Dossier où chercher les photos.
 *
 * @param string $option Valeur de « --photos », chaîne vide si l'option est absente.
 *
 * @return string Chemin du dossier, sans barre oblique finale.
 */
function dossier_a_explorer( string $option ): string {
	if ( '' !== $option ) {
		return rtrim( $option, '/\\' );
	}

	return dossier_des_photos();
}

/**
 * Noms de fichiers de photo cités par les compositions, dans l'ordre de rencontre.
 *
 * @param array<string, array<string, mixed>> $pages Fiches de page, indexées par référence.
 *
 * @return string[] Noms de fichiers, sans doublon.
 */
function photos_citees( array $pages ): array {
	$noms = array();

	foreach ( $pages as $page ) {
		$composition = isset( $page['composition'] ) && is_array( $page['composition'] ) ? $page['composition'] : array();

		foreach ( $composition as $entree ) {
			if ( ! is_array( $entree ) ) {
				continue;
			}

			$nom = texte_de( $entree, 'photo' );

			if ( '' !== $nom && ! in_array( $nom, $noms, true ) ) {
				$noms[] = $nom;
			}
		}
	}

	return $noms;
}

/**
 * S'assure que toutes les photos citées sont dans la médiathèque.
 *
 * @param string[] $noms     Noms de fichiers cités.
 * @param string   $dossier  Dossier où chercher.
 * @param bool     $simuler  Vrai pour ne rien verser.
 *
 * @return array<string, int> Nom de fichier => identifiant de pièce jointe, 0 si la photo manque.
 */
function garantir_les_photos( array $noms, string $dossier, bool $simuler ): array {
	$index = array();

	if ( array() === $noms ) {
		return $index;
	}

	$absentes = array();

	foreach ( $noms as $nom ) {
		$index[ $nom ] = garantir( $nom, $dossier, $simuler );

		if ( 0 === $index[ $nom ] ) {
			$absentes[] = $nom;
		}
	}

	if ( array() === $absentes ) {
		return $index;
	}

	/*
	 * Un seul avertissement, bruyant, quand ce sont les trois photos attendues qui manquent : c'est
	 * le manque déclaré de l'arbitrage A5, et il mérite sa cause écrite plutôt que trois lignes
	 * anonymes.
	 */
	if ( array() === array_diff( $absentes, PHOTOS_ATTENDUES ) && count( $absentes ) === count( PHOTOS_ATTENDUES ) ) {
		photos_differees( PHOTOS_ATTENDUES, $dossier );

		return $index;
	}

	foreach ( $absentes as $nom ) {
		photo_absente( $nom, $dossier );
	}

	return $index;
}

/**
 * Verse une photo si la médiathèque ne la contient pas déjà.
 *
 * Une photo trouvée n'est jamais retouchée : ni son titre, ni son texte alternatif, ni son fichier.
 * Aucun texte alternatif n'est écrit : il est une question ouverte à l'éleveuse, et en inventer un
 * serait inventer un fait.
 *
 * @param string $nom     Nom de fichier cherché.
 * @param string $dossier Dossier où chercher.
 * @param bool   $simuler Vrai pour ne rien verser.
 *
 * @return int Identifiant de la pièce jointe, 0 si la photo n'a pas pu être versée.
 */
function garantir( string $nom, string $dossier, bool $simuler ): int {
	$existante = piece_jointe_par_nom( $nom );

	if ( 0 < $existante ) {
		photo_presente( $nom );

		return $existante;
	}

	$source = $dossier . '/' . $nom;

	if ( '' === $dossier || ! is_file( $source ) || ! is_readable( $source ) ) {
		return 0;
	}

	if ( $simuler ) {
		noter( sprintf( 'Photo « %s » : trouvée dans %s, elle serait versée.', $nom, $dossier ) );

		return 0;
	}

	$piece_jointe = verser( $nom, $source );

	if ( 0 === $piece_jointe ) {
		return 0;
	}

	photo_importee( $nom );

	return $piece_jointe;
}

/**
 * Identifiant d'une pièce jointe déjà versée sous ce nom de fichier.
 *
 * Le slug d'une pièce jointe est dérivé du nom de fichier privé de son extension : c'est ce qui
 * rend le versement idempotent d'une exécution à l'autre, sans marquer quoi que ce soit.
 *
 * @param string $nom Nom de fichier.
 *
 * @return int Identifiant, 0 si la médiathèque n'en contient aucune.
 */
function piece_jointe_par_nom( string $nom ): int {
	$slug = sanitize_title( pathinfo( $nom, PATHINFO_FILENAME ) );

	if ( '' === $slug ) {
		return 0;
	}

	$piece = get_page_by_path( $slug, OBJECT, 'attachment' );

	return $piece instanceof \WP_Post ? (int) $piece->ID : 0;
}
