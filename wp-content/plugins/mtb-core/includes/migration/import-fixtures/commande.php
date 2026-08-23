<?php
/**
 * Orchestration de « wp mtb import-fixtures ».
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\ImportFixtures;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Jeux importables, dans l'ordre où ils doivent être écrits.
 *
 * Les chiens d'abord : une portée et un résultat les référencent, jamais l'inverse.
 */
const JEUX = array( 'chiens', 'portees', 'resultats' );

/**
 * Exécute l'import.
 *
 * Le déroulé, et la raison de chaque étape :
 *
 *   0. lire, décoder et vérifier la racine de TOUS les fichiers fournis — avant la moindre
 *      écriture, pour qu'un fichier illisible ne laisse jamais une base à moitié peuplée ;
 *   1. créer les fiches Chien, sans leurs clés relationnelles ;
 *   2. verser les photos citées dans la médiathèque ;
 *   3. écrire la filiation, le portrait et la galerie des fiches créées à l'étape 1 ;
 *   4. les portées, qui référencent des fiches et des photos ;
 *   5. les résultats de travail, qui référencent des fiches ;
 *   6. la synthèse, et le code de sortie.
 *
 * @param array<int, string>    $args       Arguments positionnels, aucun n'est attendu.
 * @param array<string, string> $assoc_args Options « --portees », « --chiens », « --resultats ».
 */
function executer( array $args, array $assoc_args ): void {
	$chemins = array();

	foreach ( JEUX as $jeu ) {
		$chemin = isset( $assoc_args[ $jeu ] ) && is_scalar( $assoc_args[ $jeu ] ) ? trim( (string) $assoc_args[ $jeu ] ) : '';

		if ( '' !== $chemin ) {
			$chemins[ $jeu ] = $chemin;
		}
	}

	demarrer( array_keys( $chemins ) );

	if ( array() === $chemins ) {
		aucun_fichier();
		conclure();

		return;
	}

	$entrees = array();

	foreach ( $chemins as $jeu => $chemin ) {
		$entrees[ $jeu ] = lire( $chemin );
	}

	$references = array();
	$creees     = array();

	if ( isset( $entrees['chiens'] ) ) {
		$creees = creer_chiens( $entrees['chiens'], $chemins['chiens'], $references );
	}

	$photos = garantir_les_photos( $entrees, $chemins );

	if ( isset( $entrees['chiens'] ) ) {
		completer_chiens( $entrees['chiens'], $chemins['chiens'], $creees, $references, $photos );
	}

	if ( isset( $entrees['portees'] ) ) {
		importer_portees( $entrees['portees'], $chemins['portees'], $references, $photos );
	}

	if ( isset( $entrees['resultats'] ) ) {
		importer_resultats( $entrees['resultats'], $chemins['resultats'], $references );
	}

	conclure();
}
