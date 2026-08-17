<?php
/**
 * Type de contenu « Résultat de travail ».
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Content\Resultat;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/champs.php';

add_action( 'init', __NAMESPACE__ . '\\enregistrer', 10 );

/**
 * Enregistre le type de contenu, puis ses champs, dans le même rappel init 10.
 *
 * Les champs sont enregistrés depuis ce rappel et non depuis un second add_action : cela garantit
 * qu'aucun register_post_meta ne s'exécute avant que le type existe, sans dépendre d'un ordre de
 * priorité entre deux rappels.
 */
function enregistrer(): void {
	register_post_type( 'mtb_resultat', arguments() );

	enregistrer_champs();
}

/**
 * Réglages du type de contenu.
 *
 * @return array<string, mixed> Arguments passés à register_post_type().
 */
function arguments(): array {
	return array(
		'label'               => 'Résultats de travail',
		'labels'              => libelles(),
		'description'         => 'Un résultat obtenu en concours ou en épreuve de travail.',

		/*
		 * Un résultat n'a aucune page publique : il ne s'affiche que dans le tableau de la page
		 * Travail et dans le palmarès d'une fiche chien. Aucune URL, donc aucune règle de
		 * réécriture, aucun gabarit isolé, aucune entrée de plan de site.
		 */
		'public'              => false,
		'publicly_queryable'  => false,
		'has_archive'         => false,
		'rewrite'             => false,
		'query_var'           => false,
		'exclude_from_search' => true,
		'show_in_nav_menus'   => false,

		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 23,

		/*
		 * false fait retomber WordPress sur l'écran d'édition classique : la saisie est visible
		 * d'emblée, sans chargement de l'éditeur de blocs ni panneau latéral à dérouler. Aucun
		 * consommateur n'a besoin de ce type en REST.
		 */
		'show_in_rest'        => false,

		/*
		 * false, et surtout pas array() : WP_Post_Type::set_props() traite un tableau vide comme
		 * « rien de demandé » et ajoute d'office title, editor et autosave — vérifié sur cette
		 * installation, la boîte de titre réapparaissait. Seul false supprime réellement toute
		 * prise en charge, donc toute boîte de titre. Le titre est composé au serveur à
		 * l'enregistrement : un champ de titre qu'elle remplit et que le serveur écrase serait un
		 * mensonge fait à l'éditrice.
		 */
		'supports'            => false,

		/*
		 * Capacités de l'article : le rôle Éditeur natif possède déjà tout ce qu'il faut pour
		 * créer, modifier et supprimer un résultat. Aucune capacité n'est ajoutée nulle part.
		 */
		'capability_type'     => 'post',
		'map_meta_cap'        => true,

		'hierarchical'        => false,
		'can_export'          => true,
		'delete_with_user'    => false,
	);
}

/**
 * Libellés d'administration, écrits en toutes lettres, jamais construits par concaténation.
 *
 * @return array<string, string> Libellés passés à register_post_type().
 */
function libelles(): array {
	return array(
		'name'                     => 'Résultats de travail',
		'singular_name'            => 'Résultat de travail',
		'menu_name'                => 'Résultats de travail',
		'name_admin_bar'           => 'Résultat de travail',
		'add_new'                  => 'Ajouter',
		'add_new_item'             => 'Ajouter un résultat de travail',
		'edit_item'                => 'Modifier le résultat de travail',
		'new_item'                 => 'Nouveau résultat de travail',
		'view_item'                => 'Voir le résultat de travail',
		'view_items'               => 'Voir les résultats de travail',
		'search_items'             => 'Rechercher un résultat de travail',
		'not_found'                => 'Aucun résultat de travail pour le moment.',
		'not_found_in_trash'       => 'Aucun résultat de travail dans la corbeille.',
		'all_items'                => 'Tous les résultats de travail',
		'archives'                 => 'Résultats de travail',
		'attributes'               => 'Réglages du résultat de travail',
		'insert_into_item'         => 'Insérer dans le résultat de travail',
		'uploaded_to_this_item'    => 'Photos de ce résultat de travail',
		'filter_items_list'        => 'Filtrer la liste des résultats de travail',
		'items_list_navigation'    => 'Navigation dans la liste des résultats de travail',
		'items_list'               => 'Liste des résultats de travail',
		'item_published'           => 'Résultat de travail publié.',
		'item_published_privately' => 'Résultat de travail publié en privé.',
		'item_reverted_to_draft'   => 'Résultat de travail repassé en brouillon.',
		'item_scheduled'           => 'Résultat de travail planifié.',
		'item_updated'             => 'Résultat de travail mis à jour.',
		'item_link'                => 'Lien vers un résultat de travail',
		'item_link_description'    => 'Un lien vers un résultat de travail.',
	);
}
