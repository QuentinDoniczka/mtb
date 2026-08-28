<?php
/**
 * Lecture d'un fichier de reprise et contrôle de la forme de sa racine.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\ResultatsPages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Les trois seuls échecs immédiats et totaux du module vivent ici : fichier illisible, JSON
 * invalide, racine de forme inattendue. Ce ne sont pas des données fautives, c'est l'ABSENCE de
 * données — annoncer « 0 repris » sans erreur serait exactement le silence qu'on cherche à éviter.
 *
 * Tout le reste — une entrée fautive, une correspondance qui ne correspond à rien, une photo
 * absente — se traite entrée par entrée : rejeter le lot entier sur une ligne fautive priverait le
 * site de soixante résultats derrière un seul avertissement.
 */

/**
 * Lit un fichier de reprise et rend ses entrées, la racine devant être une liste.
 *
 * @param string $chemin Chemin absolu du fichier.
 *
 * @return array<int, mixed> Entrées du fichier, dans l'ordre. Liste vide si le fichier est vide.
 */
function lire_liste( string $chemin ): array {
	$decode = decoder( $chemin );

	if ( ! is_array( $decode ) || ! est_une_liste( $decode ) ) {
		echec_racine_invalide( $chemin, 'une liste d\'entrées' );

		return array();
	}

	return $decode;
}

/**
 * Lit un fichier de reprise et rend son objet racine.
 *
 * @param string $chemin Chemin absolu du fichier.
 *
 * @return array<string, mixed> Objet décodé, tableau vide en cas d'échec.
 */
function lire_objet( string $chemin ): array {
	$decode = decoder( $chemin );

	if ( ! is_array( $decode ) || est_une_liste( $decode ) ) {
		echec_racine_invalide( $chemin, 'un objet JSON' );

		return array();
	}

	return $decode;
}

/**
 * Lit et décode un fichier, en interrompant la commande sur les deux échecs totaux.
 *
 * @param string $chemin Chemin absolu du fichier.
 *
 * @return mixed Valeur décodée, ou null si la commande a été interrompue.
 */
function decoder( string $chemin ) {
	if ( ! is_file( $chemin ) || ! is_readable( $chemin ) ) {
		echec_fichier_illisible( $chemin );

		return null;
	}

	/*
	 * file_get_contents() et non WP_Filesystem : la lecture porte sur un chemin local livré avec
	 * l'extension, jamais sur une URL, et WP_Filesystem exigerait une initialisation
	 * d'administration qui n'existe pas en WP-CLI.
	 */
	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$contenu = file_get_contents( $chemin );

	if ( false === $contenu ) {
		echec_fichier_illisible( $chemin );

		return null;
	}

	$decode = json_decode( $contenu, true );

	if ( JSON_ERROR_NONE !== json_last_error() ) {
		echec_json_invalide( $chemin, json_last_error_msg() );

		return null;
	}

	return $decode;
}

/*
 * LE TABLEAU VIDE EST À LA FOIS UNE LISTE VALIDE ET UN OBJET VALIDE, ET CE N'EST PAS UNE ERREUR
 *
 * json_decode( '[]', true ) et json_decode( '{}', true ) rendent tous deux, exactement, array().
 * PHP ne peut donc PAS distinguer une liste vide d'un objet vide après décodage : l'ambiguïté est
 * irréductible, et elle se résout au site d'appel, jamais dans ces deux fonctions.
 *
 * Les deux rendent donc vrai sur array(), et c'est délibéré :
 *
 *   - « paragraphes » : [] est légitime — une fiche de charpente n'a aucune prose ;
 *   - « composition » : [] est légitime — une page sans aucun bloc ;
 *   - « attributs »   : {} est légitime — un bloc dont tous les attributs valent leur défaut.
 *
 * Le prochain passant sera tenté de « corriger » l'une des deux en refusant array(). Il casserait
 * l'autre. La règle est ailleurs : une clé absente, « null », la chaîne vide et le tableau vide
 * disent tous « écris le défaut », et c'est valeur_absente() qui porte cette règle.
 */

/**
 * Le tableau décodé est-il une liste — donc une suite d'entrées et non un objet ?
 *
 * @param array<mixed> $valeur Tableau décodé.
 *
 * @return bool Vrai pour une liste, y compris vide.
 */
function est_une_liste( array $valeur ): bool {
	return array() === $valeur || array_keys( $valeur ) === range( 0, count( $valeur ) - 1 );
}

/**
 * Le tableau décodé est-il un objet — donc un jeu de clés nommées et non une suite d'entrées ?
 *
 * @param array<mixed> $valeur Tableau décodé.
 *
 * @return bool Vrai pour un objet, y compris vide.
 */
function est_un_objet( array $valeur ): bool {
	return array() === $valeur || array_keys( $valeur ) !== range( 0, count( $valeur ) - 1 );
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

/**
 * Dossier des données livrées avec le module.
 *
 * @return string Chemin absolu, sans barre oblique finale.
 */
function dossier_des_donnees(): string {
	return __DIR__ . '/donnees';
}

/**
 * Dossier des photos livrées avec le module.
 *
 * Il existe et il est vide : c'est ce qui rend exact le message d'absence des trois photos.
 *
 * @return string Chemin absolu, sans barre oblique finale.
 */
function dossier_des_photos(): string {
	return __DIR__ . '/photos';
}
