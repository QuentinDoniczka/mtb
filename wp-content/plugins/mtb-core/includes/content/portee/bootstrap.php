<?php
/**
 * Type de contenu Portée : enregistrement du contenu et de ses seize champs.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Content\Portee;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/champs.php';

add_action( 'init', __NAMESPACE__ . '\\enregistrer', 10 );

/**
 * Enregistre le type de contenu Portée puis ses champs, sur « init » 10.
 *
 * Aucun appel à flush_rewrite_rules() : l'apparition de « mtb_portee » modifie l'empreinte du
 * chargeur, qui régénère les règles de réécriture tout seul sur « init » 99.
 */
function enregistrer(): void {
	register_post_type(
		'mtb_portee',
		array(
			'labels'            => libelles(),
			'description'       => 'Les portées de l’élevage : identifiant, date de naissance, père et mère, chiots, photos.',
			'public'            => true,
			'show_in_rest'      => true,
			'show_in_nav_menus' => true,
			'menu_position'     => 20,
			'menu_icon'         => 'dashicons-pets',
			'capability_type'   => 'post',
			'map_meta_cap'      => true,
			'hierarchical'      => false,
			/*
			 * « editor » est présent : le commentaire de l'éleveuse reste l'éditeur natif, sous le
			 * titre. Le déplacer dans une boîte reposerait sur trois détails internes du cœur —
			 * _wp_translate_postdata(), l'identifiant codé en dur dans autosave.js, l'ordre
			 * d'exécution face à edit-form-advanced.php — dont la moindre évolution ferait cesser
			 * l'enregistrement de la prose en silence. Une fiche de chien place son commentaire au
			 * même endroit : deux écrans jumeaux, une seule disposition. Contrepartie assumée : les
			 * champs viennent après la prose.
			 *
			 * « custom-fields » reste absent, son panneau emploie des mots interdits à l'écran.
			 */
			'supports'          => array( 'title', 'editor', 'revisions', 'thumbnail' ),
			'has_archive'       => true,
			'rewrite'           => array(
				'slug'       => 'portees',
				'with_front' => false,
			),
			'query_var'         => true,
			'delete_with_user'  => false,
		)
	);

	enregistrer_champs();
}

/**
 * Libellés du type de contenu, écrits en toutes lettres, jamais composés par concaténation.
 *
 * @return array<string,string> Libellés attendus par register_post_type().
 */
function libelles(): array {
	return array(
		'name'                     => 'Portées',
		'singular_name'            => 'Portée',
		'menu_name'                => 'Portées',
		'name_admin_bar'           => 'Portée',
		// « add_new » est l'entrée du menu de gauche, sous « Portées » : le mot seul y suffit, et il
		// aligne les trois types du lot. « add_new_item » est le titre de la page, où le mot complet sert.
		'add_new'                  => 'Ajouter',
		'add_new_item'             => 'Ajouter une portée',
		'edit_item'                => 'Modifier la portée',
		'new_item'                 => 'Nouvelle portée',
		'view_item'                => 'Voir la portée',
		'view_items'               => 'Voir les portées',
		'all_items'                => 'Toutes les portées',
		'archives'                 => 'Toutes les portées',
		'search_items'             => 'Rechercher une portée',
		'not_found'                => 'Aucune portée.',
		'not_found_in_trash'       => 'Aucune portée dans la corbeille.',
		'insert_into_item'         => 'Insérer dans la portée',
		'uploaded_to_this_item'    => 'Photos de cette portée',
		'featured_image'           => 'Photo principale',
		'set_featured_image'       => 'Choisir la photo principale',
		'remove_featured_image'    => 'Retirer la photo principale',
		'use_featured_image'       => 'Utiliser comme photo principale',
		'filter_items_list'        => 'Filtrer la liste des portées',
		'items_list_navigation'    => 'Navigation dans la liste des portées',
		'items_list'               => 'Liste des portées',
		'item_published'           => 'Portée publiée.',
		'item_published_privately' => 'Portée publiée en privé.',
		'item_reverted_to_draft'   => 'Portée repassée en brouillon.',
		'item_scheduled'           => 'Portée planifiée.',
		'item_updated'             => 'Portée mise à jour.',
		'item_link'                => 'Lien vers une portée',
		'item_link_description'    => 'Un lien vers une portée.',
	);
}
