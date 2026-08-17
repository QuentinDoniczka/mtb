<?php
/**
 * Composant « Galerie photos » : enregistrement du bloc, de ses poignées et garde de catégorie.
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

add_action( 'init', __NAMESPACE__ . '\\enregistrer', 20 );
add_filter( 'block_categories_all', __NAMESPACE__ . '\\garantir_la_categorie', 10, 1 );

/**
 * Déclare les poignées puis le bloc. Appelée sur « init », priorité 20.
 *
 * Les trois poignées sont enregistrées ici plutôt que déclarées en « file: » dans block.json :
 * WordPress chercherait alors un « editeur.asset.php » voisin, produit par une étape de
 * construction que le projet n'a pas, et émettrait un avertissement. Les enregistrer nous-mêmes
 * garde aussi les noms conformes à la convention « mtb-<module>-<usage> ».
 */
function enregistrer(): void {
	wp_register_script(
		'mtb-galerie-photos-editeur',
		MTB_CORE_URL . 'includes/blocks/galerie-photos/editeur.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render' ),
		MTB_CORE_VERSION,
		true
	);

	/*
	 * Une feuille déclarée en « style » passe par « enqueue_block_assets », donc atteint la page
	 * publique ET la toile de l'éditeur. C'est ce qui évite de recopier la grille dans la feuille
	 * d'éditeur : une seule feuille habille les deux contextes.
	 */
	wp_register_style(
		'mtb-galerie-photos-style',
		MTB_CORE_URL . 'includes/blocks/galerie-photos/galerie.css',
		array(),
		MTB_CORE_VERSION
	);

	wp_register_style(
		'mtb-galerie-photos-editeur-style',
		MTB_CORE_URL . 'includes/blocks/galerie-photos/editeur.css',
		array(),
		MTB_CORE_VERSION
	);

	register_block_type( __DIR__ );
}

/**
 * Garantit la présence de la catégorie de blocs « Mont Brabant », sans jamais la dupliquer.
 *
 * Le dossier « includes/blocks/categorie-mtb/ » qui doit un jour la porter seul n'existe pas
 * encore, et plusieurs composants sont livrés en parallèle : chacun porte donc cette garde. Elle
 * est idempotente — le premier module exécuté ajoute la catégorie, les suivants constatent sa
 * présence et rendent la main — et devient inerte le jour où le module dédié arrive.
 *
 * L'ajout se fait en fin de liste, jamais en tête : aucun onglet du cœur n'est déplacé.
 *
 * Le paramètre n'est pas typé : un autre module mal élevé pourrait renvoyer autre chose qu'un
 * tableau, et une erreur de type sur un filtre du cœur emporterait l'éditeur entier.
 *
 * @param mixed $categories Catégories de blocs proposées.
 *
 * @return mixed Catégories, la nôtre garantie présente une seule fois.
 */
function garantir_la_categorie( $categories ) {
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
		'icon'  => null,
	);

	return $categories;
}
