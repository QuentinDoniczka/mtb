<?php
/**
 * Composant « Fiche d'information » — déclaration du script d'édition et enregistrement du bloc.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Blocks\FicheInformation;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Les fonctions d'aide du rendu vivent dans un fichier à part, inclus une seule fois ici.
 * « render.php », lui, est inclus par le cœur avec un « require » nu (wp-includes/blocks.php:572),
 * donc une fois par fiche présente sur la page : une déclaration de fonction qui y figurerait
 * ferait tomber le site entier dès la deuxième fiche.
 */
require_once __DIR__ . '/rendu.php';

/*
 * Point d'entrée du même balisage pour les gabarits du thème, hors de tout contexte de bloc : les
 * fiches Portée, Chien et Résultat n'acceptent aucun bloc. Simple déclaration de fonction, aucun
 * hook, aucune lecture de donnée.
 */
require_once __DIR__ . '/interface.php';

add_action( 'init', __NAMESPACE__ . '\\enregistrer', 20 );

/**
 * Déclare le script d'édition, puis enregistre le bloc — dans cet ordre.
 *
 * L'ordre compte : « block.json » ne porte que la poignée du script, et le cœur exige qu'elle soit
 * déjà connue au moment de l'enregistrement du bloc. Aucune étape de construction n'est employée ;
 * le fichier est du JavaScript ordinaire, servi tel quel.
 */
function enregistrer(): void {
	wp_register_script(
		'mtb-fiche-information-editeur',
		MTB_CORE_URL . 'includes/blocks/fiche-information/editeur.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-data' ),
		MTB_CORE_VERSION,
		true
	);

	register_block_type( __DIR__ );
}
