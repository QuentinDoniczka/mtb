<?php
/**
 * Composant « Galerie photos » : enregistrement du bloc et de ses poignées.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Blocks\GaleriePhotos;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * « rendu.php » est chargé ici, à l'inclusion du module. « render.php », lui, est inclus par
 * WordPress au moment du rendu — jamais par le chargeur — et ne fait qu'appeler « rendre() » : la
 * fonction doit donc déjà être déclarée quand il s'exécute.
 */
require_once __DIR__ . '/rendu.php';

/*
 * La catégorie d'insérteur « Mont Brabant » appartient au module « includes/blocks/categorie-mtb/ »,
 * qui la déclare une seule fois pour tout le catalogue. Ce composant s'y raccroche par le seul
 * « "category": "mtb" » de son block.json : aucun filtre « block_categories_all » ici, sans quoi le
 * même onglet serait déclaré par autant de modules qu'il y a de composants.
 */
add_action( 'init', __NAMESPACE__ . '\\enregistrer', 20 );

/**
 * Déclare les poignées puis le bloc. Appelée sur « init », priorité 20.
 *
 * Les deux poignées sont enregistrées ici plutôt que déclarées en « file: » dans block.json. Pour
 * le script, c'est une obligation : WordPress chercherait un « editeur.asset.php » voisin, produit
 * par une étape de construction que le projet n'a pas, et émettrait un avertissement. Pour la
 * feuille d'éditeur, un « file: » serait sans danger — « register_block_style_handle() » résout
 * l'URI lui-même et ne cherche jamais d'« asset.php » — mais la déclarer ici garde son nom conforme
 * à la convention « mtb-<module>-<usage> » et lisible dans la file d'attente.
 */
function enregistrer(): void {
	wp_register_script(
		'mtb-galerie-photos-editeur',
		MTB_CORE_URL . 'includes/blocks/galerie-photos/editeur.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render' ),
		MTB_CORE_VERSION,
		true
	);

	wp_register_style(
		'mtb-galerie-photos-editeur-style',
		MTB_CORE_URL . 'includes/blocks/galerie-photos/editeur.css',
		array(),
		MTB_CORE_VERSION
	);

	register_block_type( __DIR__ );
}
