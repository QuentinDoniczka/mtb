<?php
/**
 * Lecture d'un fichier de fixtures et contrôle de la forme de sa racine.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\ImportFixtures;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Les trois seuls échecs immédiats et totaux du module vivent ici : fichier illisible, JSON
 * invalide, racine qui n'est pas une liste. Ce ne sont pas des fixtures fautives, c'est l'ABSENCE
 * de fixtures — annoncer « 0 importée » sans erreur serait exactement le silence de T39.
 *
 * Tout le reste — une entrée fautive, une référence manquante, une photo absente — se traite entrée
 * par entrée : rejeter le lot entier sur une ligne fautive livrerait un site vide derrière un seul
 * avertissement.
 */

/**
 * Lit un fichier de fixtures et rend ses entrées.
 *
 * Interrompt la commande, avant toute écriture, si le fichier ne peut pas être lu ou ne contient
 * pas une liste. Le contrôle du contenu des entrées, lui, appartient à « controle.php ».
 *
 * @param string $chemin Chemin absolu du fichier.
 *
 * @return array<int, mixed> Entrées du fichier, dans l'ordre. Liste vide si le fichier est vide.
 */
function lire( string $chemin ): array {
	if ( ! is_file( $chemin ) || ! is_readable( $chemin ) ) {
		echec_fichier_illisible( $chemin );

		return array();
	}

	/*
	 * file_get_contents() et non WP_Filesystem : la lecture porte sur un chemin local fourni en
	 * ligne de commande, jamais sur une URL, et WP_Filesystem exigerait une initialisation
	 * d'administration qui n'existe pas en WP-CLI.
	 */
	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$contenu = file_get_contents( $chemin );

	if ( false === $contenu ) {
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
 * Le tableau décodé est-il une liste — donc une suite d'entrées et non un objet ?
 *
 * @param array<mixed> $valeur Tableau décodé.
 *
 * @return bool Vrai pour une liste, y compris vide.
 */
function est_une_liste( array $valeur ): bool {
	return array_keys( $valeur ) === range( 0, count( $valeur ) - 1 ) || array() === $valeur;
}

/**
 * Nom du fichier tel qu'il apparaît dans les messages.
 *
 * @param string $chemin Chemin absolu.
 *
 * @return string Nom du fichier, sans son dossier.
 */
function nom_de_fichier( string $chemin ): string {
	return basename( $chemin );
}
