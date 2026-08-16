<?php
/**
 * Type de contenu « Chien » : enregistrement et réglages d'écran.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Content\Chien;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/choix.php';
require_once __DIR__ . '/assainissement.php';
require_once __DIR__ . '/champs.php';

add_action( 'init', __NAMESPACE__ . '\\enregistrer', 10 );
add_action( 'init', __NAMESPACE__ . '\\enregistrer_champs', 10 );
add_filter( 'use_block_editor_for_post_type', __NAMESPACE__ . '\\ecran_de_saisie_classique', 10, 2 );
add_filter( 'enter_title_here', __NAMESPACE__ . '\\invite_du_titre', 10, 2 );

/**
 * Enregistre le type de contenu. Appelée sur « init », priorité 10.
 */
function enregistrer(): void {
	register_post_type(
		'mtb_chien',
		array(
			'labels'                => array(
				'name'                   => 'Chiens',
				'singular_name'          => 'Chien',
				'menu_name'              => 'Chiens',
				'name_admin_bar'         => 'Chien',
				'add_new'                => 'Ajouter',
				'add_new_item'           => 'Ajouter un chien',
				'edit_item'              => 'Modifier la fiche du chien',
				'new_item'               => 'Nouveau chien',
				'view_item'              => 'Voir la fiche',
				'view_items'             => 'Voir les chiens',
				'search_items'           => 'Rechercher un chien',
				'not_found'              => 'Aucun chien pour le moment.',
				'not_found_in_trash'     => 'Aucun chien dans la corbeille.',
				'all_items'              => 'Tous les chiens',
				'archives'               => 'Fiches des chiens',
				'insert_into_item'       => 'Insérer dans la fiche',
				'uploaded_to_this_item'  => 'Photos de cette fiche',
				'featured_image'         => 'Photo principale',
				'set_featured_image'     => 'Choisir la photo principale',
				'remove_featured_image'  => 'Retirer la photo principale',
				'use_featured_image'     => 'Utiliser comme photo principale',
				'item_published'         => 'Fiche publiée.',
				'item_updated'           => 'Fiche mise à jour.',
				'item_reverted_to_draft' => 'Fiche repassée en brouillon.',
			),
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'show_in_rest'          => true,
			'menu_position'         => 22,
			'menu_icon'             => 'dashicons-pets',
			/*
			 * « revisions » n'est pas décoratif ici : c'est la contrepartie du choix de loger le
			 * commentaire de l'éleveuse dans le contenu de la fiche. Sans lui, une prose écrasée
			 * puis enregistrée serait définitivement perdue. À savoir en revanche : WordPress ne
			 * versionne que le titre et le contenu, jamais les champs de la fiche.
			 */
			'supports'              => array( 'title', 'editor', 'thumbnail', 'revisions' ),
			'hierarchical'          => false,
			/*
			 * Aucune archive : « La meute » est une page libre composée de composants. Une archive
			 * native créerait un second index concurrent, que personne ne pourrait éditer.
			 */
			'has_archive'           => false,
			'rewrite'               => array(
				'slug'       => 'chien',
				'with_front' => false,
			),
			'capability_type'       => 'post',
			'map_meta_cap'          => true,
			'delete_with_user'      => false,
		)
	);
}

/**
 * Impose le formulaire classique sur la fiche Chien, et sur elle seule.
 *
 * Une fiche ne se compose pas, elle se remplit : en éditeur de blocs, les champs atterrissent en
 * boîtes sous le canevas, et il faudrait faire défiler la page à chaque chien pour atteindre ce
 * pour quoi on l'a ouverte. L'API REST du type reste disponible : les deux réglages sont
 * indépendants.
 *
 * @param bool   $utiliser  Décision précédente.
 * @param string $type_post Type de contenu concerné.
 */
function ecran_de_saisie_classique( bool $utiliser, string $type_post ): bool {
	if ( 'mtb_chien' === $type_post ) {
		return false;
	}

	return $utiliser;
}

/**
 * Nomme le champ titre « Nom d'usage » : le titre WordPress est le nom d'usage, jamais recopié
 * dans un champ à part.
 *
 * @param string    $texte Invite par défaut.
 * @param \WP_Post  $post  Fiche en cours d'édition.
 */
function invite_du_titre( string $texte, $post ): string {
	if ( $post instanceof \WP_Post && 'mtb_chien' === $post->post_type ) {
		return "Nom d'usage";
	}

	return $texte;
}
