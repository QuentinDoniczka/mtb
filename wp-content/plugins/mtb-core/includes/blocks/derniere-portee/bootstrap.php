<?php
/**
 * Bloc « Encart dernière portée » : script de l'éditeur et enregistrement du bloc.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Blocks\DernierePortee;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', __NAMESPACE__ . '\\enregistrer', 20 );

/**
 * Déclare le script de l'éditeur, puis le bloc lui-même.
 *
 * Le script est enregistré ici et « block.json » n'en porte que la poignée : écrire
 * « file:./editeur.js » ferait chercher au cœur un « editeur.asset.php » que seule une étape de
 * construction produit, et il émettrait un « _doing_it_wrong ». Le projet n'a aucune étape de
 * construction, ce fichier est servi tel quel.
 *
 * « wp-server-side-render » est livré par le cœur : l'aperçu de l'éditeur n'ajoute aucune
 * dépendance externe.
 */
function enregistrer(): void {
	wp_register_script(
		'mtb-derniere-portee-editeur',
		MTB_CORE_URL . 'includes/blocks/derniere-portee/editeur.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render' ),
		MTB_CORE_VERSION,
		true
	);

	register_block_type( __DIR__ );
}
