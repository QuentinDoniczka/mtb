<?php
/**
 * Composant « Liste de portées » : enregistrement du bloc et de son script d'éditeur.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Blocks\ListePortees;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Les deux classes du module sont chargées ici, une seule fois. render.php, lui, est inclus par
 * WordPress à chaque instance du bloc : il ne peut donc rien déclarer.
 */
require_once __DIR__ . '/rendu.php';
require_once __DIR__ . '/annees.php';

add_action( 'init', __NAMESPACE__ . '\\enregistrer', 20 );
add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\\transmettre_annees' );

/**
 * Enregistre le script de l'éditeur puis le bloc, sur « init » 20.
 *
 * Le script est enregistré ici et block.json n'en porte que la poignée : « file:./editeur.js » ferait
 * chercher à WordPress un editeur.asset.php produit par une étape de construction, qui n'existe pas,
 * et l'absence de ce fichier provoque un avertissement du cœur.
 */
function enregistrer(): void {
	wp_register_script(
		'mtb-liste-portees-editeur',
		MTB_CORE_URL . 'includes/blocks/liste-portees/editeur.js',
		array(
			'wp-blocks',
			'wp-element',
			'wp-block-editor',
			'wp-components',
			'wp-server-side-render',
			'wp-data',
			'wp-core-data',
		),
		MTB_CORE_VERSION,
		true
	);

	register_block_type( __DIR__ );
}

/**
 * Transmet à l'éditeur la liste des années qui ont au moins une portée.
 *
 * Sur « enqueue_block_editor_assets » et nulle part ailleurs : c'est le seul hook qui ne s'exécute
 * que dans l'éditeur. Dérivée sur « init », la liste hydraterait toutes les portées à chaque requête
 * publique, pour un réglage que seule l'éleveuse consulte.
 */
function transmettre_annees(): void {
	wp_localize_script( 'mtb-liste-portees-editeur', 'mtbListePorteesAnnees', Annees::disponibles() );
}
