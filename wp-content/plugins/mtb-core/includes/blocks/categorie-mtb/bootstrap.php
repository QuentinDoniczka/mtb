<?php
/**
 * Catégorie « Mont Brabant » de l'insérteur de blocs.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Blocks\CategorieMtb;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * CE MODULE A UN SEUL RÔLE ET NE GROSSIT JAMAIS.
 *
 * Il déclare la catégorie sous laquelle les composants du catalogue apparaissent dans l'insérteur.
 * Un composant sœur s'y raccroche en écrivant « "category": "mtb" » dans son block.json — rien
 * d'autre : aucun index à tenir, aucun fichier partagé, donc aucune collision d'empreinte entre les
 * chaînes qui écrivent les dix composants sans se voir.
 *
 * Ce dossier n'accueille ni second filtre, ni icône, ni restriction d'insérteur
 * (« allowed_block_types_all » appartient à l'epic Gabarits) : l'y mettre en ferait un point de
 * passage obligé pour dix issues, exactement l'index central que la décision 9 interdit.
 *
 * Pas de garde « is_admin() » : le filtre est inoffensif côté public, et l'aperçu de bloc passe par
 * REST, qui n'est ni l'un ni l'autre selon les versions de WordPress.
 */

// « block_categories_all » (WordPress ≥ 5.8) et jamais « block_categories », déprécié depuis.
add_filter( 'block_categories_all', __NAMESPACE__ . '\\ajouter', 10, 2 );

/**
 * Place la catégorie du catalogue en tête des catégories de l'insérteur.
 *
 * En tête, et non à la suite : les composants du Mont Brabant sont ce que l'éleveuse insère
 * réellement ; « Texte », « Média », « Widgets » et « Thème » sont le bruit qu'elle traverse. Elle
 * ouvre l'insérteur et « Mont Brabant » est la première chose qu'elle lit.
 *
 * La garde d'idempotence n'est pas décorative : plusieurs composants du catalogue portent leur propre
 * filet de sécurité sur ce même filtre, et une catégorie déclarée deux fois apparaîtrait deux fois
 * dans l'insérteur.
 *
 * @param mixed $categories Catégories déjà déclarées. Typé « mixed » et non « array » : un filtre
 *                          tiers peut avoir rendu autre chose, et strict_types transformerait cela
 *                          en erreur fatale sur l'écran d'édition.
 * @param mixed $contexte   Contexte d'édition fourni par le cœur, inutilisé ici : la catégorie est
 *                          la même pour tout contenu.
 *
 * @return mixed Les catégories, la nôtre en première position.
 */
function ajouter( $categories, $contexte = null ) {
	unset( $contexte );

	if ( ! is_array( $categories ) ) {
		return $categories;
	}

	foreach ( $categories as $categorie ) {
		if ( is_array( $categorie ) && isset( $categorie['slug'] ) && 'mtb' === $categorie['slug'] ) {
			return $categories;
		}
	}

	array_unshift(
		$categories,
		array(
			'slug'  => 'mtb',
			'title' => 'Mont Brabant',
			// null, comme le cœur l'écrit pour ses propres catégories : la clé est vestigiale.
			'icon'  => null,
		)
	);

	return $categories;
}
