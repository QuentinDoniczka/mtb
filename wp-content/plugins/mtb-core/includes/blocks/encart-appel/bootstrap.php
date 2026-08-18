<?php
/**
 * Composant « Encart d’appel » — déclaration du script d’édition et enregistrement du bloc.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Blocks\EncartAppel;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Les fonctions d’aide du rendu vivent dans un fichier à part, inclus une seule fois ici.
 * « render.php », lui, est inclus par le cœur avec un « require » nu, donc une fois par encart
 * présent sur la page : une déclaration de fonction qui y figurerait ferait tomber le site entier
 * dès le deuxième encart — et le bloc en autorise plusieurs par page.
 */
require_once __DIR__ . '/rendu.php';

add_action( 'init', __NAMESPACE__ . '\\enregistrer', 20 );

/**
 * Déclare le script d’édition, puis enregistre le bloc — dans cet ordre.
 *
 * L’ordre compte : « block.json » ne porte que la poignée du script, et le cœur exige qu’elle soit
 * déjà connue au moment de l’enregistrement du bloc. Aucune étape de construction n’est employée ;
 * le fichier est du JavaScript ordinaire, servi tel quel — écrire « file:./editeur.js » ferait
 * chercher au cœur un fichier d’actifs que seule une étape de construction produit.
 *
 * Les sept dépendances sont toutes livrées par le cœur : l’aperçu et le sélecteur de page
 * n’ajoutent aucune bibliothèque, et rien de tout cela n’est servi au visiteur.
 */
function enregistrer(): void {
	wp_register_script(
		'mtb-encart-appel-editeur',
		MTB_CORE_URL . 'includes/blocks/encart-appel/editeur.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-data', 'wp-html-entities', 'wp-server-side-render' ),
		MTB_CORE_VERSION,
		true
	);

	register_block_type( __DIR__ );
}
