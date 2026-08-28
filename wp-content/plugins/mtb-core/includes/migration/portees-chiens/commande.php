<?php
/**
 * Orchestration des deux commandes de reprise.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\PorteesChiens;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Jeux repris, dans l'ordre où ils doivent être lus.
 */
const JEUX = array( 'chiens', 'portees' );

/**
 * Exécute la reprise.
 *
 * Le déroulé, et la raison de chaque étape :
 *
 *   0. refuser une base semée de démonstration, exiger le module d'images, prévenir du compte —
 *      trois gardes AVANT la moindre écriture, parce qu'aucune des trois ne se rattrape après ;
 *   1. lire et décoder les DEUX fichiers, pour qu'un fichier illisible ne laisse jamais une base à
 *      moitié peuplée ;
 *   2. créer les fiches Chien, sans leurs clés relationnelles ;
 *   3. verser les photographies, une pièce jointe par condensé ;
 *   4. écrire la filiation, la galerie et le portrait des fiches créées à l'étape 2 ;
 *   5. les portées, qui référencent des fiches et des photographies ;
 *   6. la synthèse, et le code de sortie.
 *
 * L'ordre 2-4 n'est pas une commodité : un chien référence un chien qui figure plus loin dans le
 * même fichier, et une passe unique donnerait à la moitié du jeu une filiation vide, sans un mot.
 *
 * @param array<int, string>    $args       Arguments positionnels, aucun n'est attendu.
 * @param array<string, string> $assoc_args Options de la commande.
 */
function executer_import( array $args, array $assoc_args ): void {
	$chemins = fichiers_de_donnees( $assoc_args );

	demarrer( JEUX );

	if ( ! refuser_une_base_de_demonstration() || ! exiger_le_module_dimages() ) {
		return;
	}

	avertir_du_compte_dexecution();

	$entrees = array();

	foreach ( JEUX as $jeu ) {
		$entrees[ $jeu ] = lire( $chemins[ $jeu ] );
	}

	$references = array();
	$creees     = creer_chiens( $entrees['chiens'], $chemins['chiens'], $references );
	$photos     = garantir_les_photos( $entrees, $assoc_args );

	completer_chiens( $entrees['chiens'], $chemins['chiens'], $creees, $references, $photos );
	importer_portees( $entrees['portees'], $chemins['portees'], $references, $photos );

	conclure();
}

/**
 * Exécute la vérification.
 *
 * Aucune écriture, aucune garde de non-mélange : une commande qui ne touche à rien peut s'exécuter
 * sur n'importe quelle base, et c'est justement sur une base douteuse qu'on veut pouvoir la lancer.
 *
 * @param array<int, string>    $args       Arguments positionnels, aucun n'est attendu.
 * @param array<string, string> $assoc_args Options de la commande.
 */
function executer_verification( array $args, array $assoc_args ): void {
	$chemins = fichiers_de_donnees( $assoc_args );

	demarrer( JEUX );

	$entrees = array();

	foreach ( JEUX as $jeu ) {
		$entrees[ $jeu ] = lire( $chemins[ $jeu ] );
	}

	verifier( $entrees, $chemins, $assoc_args );

	conclure_verification();
}
