<?php
/**
 * Lecture d'un fichier de données transcrites et contrôle de la forme de sa racine.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\PorteesChiens;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Les trois seuls échecs immédiats et totaux du module vivent ici : fichier illisible, JSON
 * invalide, racine qui n'est pas une liste. Ce ne sont pas des transcriptions fautives, c'est
 * l'ABSENCE de transcription — annoncer « 0 importée » sans erreur serait exactement le silence
 * que ce module existe pour interdire.
 *
 * Tout le reste — une entrée fautive, une référence manquante, une photo absente — se traite
 * entrée par entrée : rejeter le lot entier sur une ligne fautive laisserait la base vide derrière
 * un seul avertissement.
 */

/**
 * Lit un fichier de données et rend ses entrées.
 *
 * Interrompt la commande, avant toute écriture, si le fichier ne peut pas être lu ou ne contient
 * pas une liste. Le contrôle du contenu des entrées appartient à « controle.php ».
 *
 * @param string $chemin Chemin absolu du fichier.
 *
 * @return array<int, mixed> Entrées du fichier, dans l'ordre. Liste vide si le fichier est vide.
 */
function lire( string $chemin ): array {
	$contenu = lire_texte( $chemin );

	if ( null === $contenu ) {
		echec_fichier_illisible( $chemin );

		return array();
	}

	$entrees = json_decode( $contenu, true );

	if ( JSON_ERROR_NONE !== json_last_error() ) {
		echec_json_invalide( $chemin, json_last_error_msg() );

		return array();
	}

	if ( ! is_array( $entrees ) || ! est_une_liste( $entrees ) ) {
		echec_racine_invalide( $chemin );

		return array();
	}

	return $entrees;
}

/**
 * Lit un fichier texte local, sans jamais toucher au réseau.
 *
 * file_get_contents() et non WP_Filesystem : la lecture porte sur un chemin local fourni en ligne
 * de commande, jamais sur une URL, et WP_Filesystem exigerait une initialisation d'administration
 * qui n'existe pas en WP-CLI.
 *
 * @param string $chemin Chemin absolu.
 *
 * @return string|null Contenu du fichier, null s'il est illisible.
 */
function lire_texte( string $chemin ): ?string {
	if ( ! is_file( $chemin ) || ! is_readable( $chemin ) ) {
		return null;
	}

	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$contenu = file_get_contents( $chemin );

	return false === $contenu ? null : $contenu;
}

/**
 * Ramène les fins de ligne d'un texte à des sauts de ligne simples.
 *
 * PIÈGE MESURÉ, pas théorique : le dépôt est cloné avec « core.autocrlf = true » et ne porte aucun
 * « .gitattributes ». « html/portee-u2-2023.html » fait 38 887 octets sur disque, porte 525 CR, et
 * 38 887 − 525 = 38 362, soit exactement la taille que le relevé du source déclare. Comparer un
 * extrait à un fichier sans cette normalisation ferait échouer le contrôle des extraits sur des
 * différences qui n'existent que sur ce disque.
 *
 * @param string $texte Texte brut.
 *
 * @return string Texte aux fins de ligne normalisées.
 */
function normaliser_fins_de_ligne( string $texte ): string {
	return str_replace( array( "\r\n", "\r" ), "\n", $texte );
}

/**
 * Le tableau décodé est-il une liste — donc une suite d'entrées et non un objet ?
 *
 * @param array<mixed> $valeur Tableau décodé.
 *
 * @return bool Vrai pour une liste, y compris vide.
 */
function est_une_liste( array $valeur ): bool {
	return array_keys( $valeur ) === range( 0, count( $valeur ) - 1 ) || array() === $valeur;
}
