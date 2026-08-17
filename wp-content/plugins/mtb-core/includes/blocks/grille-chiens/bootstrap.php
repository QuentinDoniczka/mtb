<?php
/**
 * Composant « Grille de chiens » : enregistrement du bloc et de son script d'éditeur.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Blocks\GrilleChiens;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * render.php n'est jamais inclus ici : WordPress l'inclut lui-même, une fois par instance du bloc.
 * L'inclure depuis le chargeur exécuterait son rendu hors de toute portée utile, et le fichier ne
 * doit surtout pas déclarer de fonction — deux grilles sur la même page provoqueraient une erreur
 * de compilation qu'aucun try/catch n'attrape.
 */
require_once __DIR__ . '/donnees.php';
require_once __DIR__ . '/balisage.php';

add_action( 'init', __NAMESPACE__ . '\\enregistrer', 20 );
add_filter( 'block_categories_all', __NAMESPACE__ . '\\categorie_du_catalogue' );

/**
 * Enregistre le script d'éditeur puis le bloc. Appelée sur « init », priorité 20.
 */
function enregistrer(): void {
	/*
	 * Le script est enregistré ici et « block.json » n'en porte que la poignée. Un
	 * « file:./editeur.js » ferait chercher à WordPress un fichier de dépendances produit par une
	 * étape de construction : il n'y en a pas, et l'absence déclencherait un avertissement.
	 */
	wp_register_script(
		'mtb-grille-chiens-editeur',
		MTB_CORE_URL . 'includes/blocks/grille-chiens/editeur.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render' ),
		MTB_CORE_VERSION,
		true
	);

	// Tout le texte affiché par l'éditeur vient d'ici : editeur.js n'en contient aucun.
	wp_localize_script( 'mtb-grille-chiens-editeur', 'mtbGrilleChiens', donnees_editeur() );

	register_block_type( __DIR__ );
}

/**
 * Ajoute la catégorie du catalogue à l'insérteur, si aucun autre module ne l'a déjà fait.
 *
 * register_block_type() ne valide pas la catégorie d'un bloc : elle n'émet ni erreur ni
 * avertissement, mais l'insérteur ne construit ses sections que depuis les catégories
 * enregistrées. Un bloc dont la catégorie n'y figure pas n'apparaît nulle part, tout en restant
 * trouvable par la recherche — une panne muette et à moitié vraie. Le test d'existence rend le
 * filtre idempotent : plusieurs composants peuvent le porter sans se gêner.
 *
 * @param mixed $categories Catégories déjà déclarées.
 *
 * @return mixed
 */
function categorie_du_catalogue( $categories ) {
	if ( ! is_array( $categories ) ) {
		return $categories;
	}

	foreach ( $categories as $categorie ) {
		if ( is_array( $categorie ) && isset( $categorie['slug'] ) && 'mtb' === $categorie['slug'] ) {
			return $categories;
		}
	}

	$categories[] = array(
		'slug'  => 'mtb',
		'title' => 'Mont Brabant',
	);

	return $categories;
}
