<?php
/**
 * Composant « Bandeau d'alerte » — déclaration du script d'édition et enregistrement du bloc.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Blocks\BandeauAlerte;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * AUCUNE GARDE « is_admin() » ICI, ET C'EST LE PIÈGE LE PLUS COÛTEUX DE CE DOSSIER.
 *
 * Les modules de « includes/fields/ » commencent par « if ( ! is_admin() ) { return; } », parce
 * qu'un écran de saisie n'a aucun sens côté public. Un module de « includes/blocks/ » qui recopierait
 * cette ligne ne s'enregistrerait PAS côté public : le composant fonctionnerait parfaitement dans
 * l'éditeur et disparaîtrait du site, sans erreur, sans avertissement, sur une page qui répond 200.
 * Un bloc doit s'enregistrer sur les trois façades — public (rendu), administration (insérteur) et
 * REST (aperçu de l'éditeur) — donc sans aucune garde de contexte.
 */

/*
 * Les fonctions d'aide du rendu vivent dans un fichier à part, inclus une seule fois ici.
 * « render.php », lui, est inclus par le cœur avec un « require » nu, donc une fois par bandeau
 * présent sur la page : une déclaration de fonction qui y figurerait ferait tomber le site entier
 * dès le deuxième bandeau.
 */
require_once __DIR__ . '/rendu.php';

add_action( 'init', __NAMESPACE__ . '\\enregistrer', 20 );

/**
 * Déclare le script d'édition, puis enregistre le bloc — dans cet ordre.
 *
 * L'ordre compte : « block.json » ne porte que la POIGNÉE du script, et le cœur exige qu'elle soit
 * déjà connue au moment de l'enregistrement du bloc. Un
 * « "editorScript": "file:./editeur.js" » ferait chercher à WordPress un « editeur.asset.php »
 * produit par une étape de construction : il n'y en a aucune dans ce projet, et l'absence
 * déclencherait un « _doing_it_wrong ».
 */
function enregistrer(): void {
	/*
	 * Trois dépendances, pas davantage. Ni « wp-components » (le composant n'a aucun panneau
	 * latéral : un panneau à zéro réglage est un panneau qu'elle ouvre pour rien), ni « wp-data »
	 * (aucune donnée à lire), ni « wp-server-side-render » (l'aperçu EST le champ éditable ; un rendu
	 * serveur coûterait un aller-retour réseau à chaque frappe pour afficher un texte que le
	 * navigateur possède déjà).
	 *
	 * Enregistrer n'est pas mettre en file : ce script n'est jamais servi au visiteur, le cœur ne le
	 * met en file que dans l'éditeur, par la seule clé « editorScript ».
	 */
	wp_register_script(
		'mtb-bandeau-alerte-editeur',
		MTB_CORE_URL . 'includes/blocks/bandeau-alerte/editeur.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor' ),
		MTB_CORE_VERSION,
		true
	);

	/*
	 * Clés absentes de block.json, volontairement :
	 *
	 * - « style » et « editorStyle » : l'extension n'émet aucune règle visuelle et ne met aucune
	 *   feuille en file. La feuille du composant est servie par le thème, qui déduit son nom du nom
	 *   du bloc.
	 * - « textdomain » : aucune fonction i18n dans mtb-core, le français est littéral. Une clé sans
	 *   catalogue suggérerait le contraire.
	 * - « viewScript » et « script » : ce composant n'a aucun JavaScript public, zéro octet.
	 */
	register_block_type( __DIR__ );
}
