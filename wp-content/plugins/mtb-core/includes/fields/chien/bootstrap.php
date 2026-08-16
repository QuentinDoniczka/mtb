<?php
/**
 * Écran de saisie de la fiche Chien : sections, script d'écran et enregistrement.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Fields\Chien;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Rien de ce module n'a de sens hors de l'administration : aucun de ses fichiers n'est chargé côté public.
if ( ! is_admin() ) {
	return;
}

/*
 * Le vocabulaire et l'assainissement appartiennent au module « content/chien » : ils sont partagés,
 * jamais recopiés, pour que l'accord au sexe et le nettoyage des valeurs recopiées n'existent qu'à
 * un seul endroit. Une seconde inclusion est sans effet.
 */
require_once MTB_CORE_DIR . 'includes/content/chien/choix.php';
require_once MTB_CORE_DIR . 'includes/content/chien/assainissement.php';
require_once __DIR__ . '/class-avis.php';
require_once __DIR__ . '/ecran.php';
require_once __DIR__ . '/sauvegarde.php';

add_action( 'init', __NAMESPACE__ . '\\enregistrer_scripts', 20 );
add_action( 'add_meta_boxes', __NAMESPACE__ . '\\enregistrer_sections', 10, 2 );
add_action( 'edit_form_after_title', __NAMESPACE__ . '\\rendre_titre_du_commentaire' );
add_action( 'save_post_mtb_chien', __NAMESPACE__ . '\\enregistrer_champs', 10, 3 );
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\charger_scripts' );
add_filter( 'redirect_post_location', __NAMESPACE__ . '\\ajouter_avis_a_la_redirection', 10, 2 );
add_action( 'admin_notices', __NAMESPACE__ . '\\afficher_avis' );

/**
 * Déclare les scripts d'écran. Appelée sur « init », priorité 20.
 */
function enregistrer_scripts(): void {
	wp_register_script(
		'mtb-chien-statut',
		MTB_CORE_URL . 'includes/fields/chien/statut.js',
		array(),
		MTB_CORE_VERSION,
		true
	);

	wp_register_script(
		'mtb-chien-galerie',
		MTB_CORE_URL . 'includes/fields/chien/galerie.js',
		array( 'media-editor' ),
		MTB_CORE_VERSION,
		true
	);
}

/**
 * Charge les scripts sur le seul écran d'édition d'une fiche Chien.
 *
 * @param string $page Page d'administration en cours.
 */
function charger_scripts( string $page ): void {
	if ( 'post.php' !== $page && 'post-new.php' !== $page ) {
		return;
	}

	$ecran = get_current_screen();

	if ( null === $ecran || 'mtb_chien' !== $ecran->post_type ) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_script( 'mtb-chien-statut' );
	wp_enqueue_script( 'mtb-chien-galerie' );
}

/**
 * Déclare les sections de l'écran, dans l'ordre où l'on remplit une fiche.
 *
 * @param string $type_post Type de contenu de l'écran.
 * @param mixed  $post      Fiche en cours d'édition.
 */
function enregistrer_sections( string $type_post, $post ): void {
	if ( 'mtb_chien' !== $type_post ) {
		return;
	}

	$sections = array(
		'mtb-chien-identite'    => array( 'Identité', __NAMESPACE__ . '\\rendre_identite' ),
		'mtb-chien-parents'     => array( 'Parents', __NAMESPACE__ . '\\rendre_parents' ),
		'mtb-chien-taille-robe' => array( 'Taille et robe', __NAMESPACE__ . '\\rendre_taille_robe' ),
		'mtb-chien-sante'       => array( 'Santé', __NAMESPACE__ . '\\rendre_sante' ),
		'mtb-chien-titres'      => array( 'Titres et brevets', __NAMESPACE__ . '\\rendre_titres' ),
		'mtb-chien-photos'      => array( 'Photos et pedigree', __NAMESPACE__ . '\\rendre_photos' ),
	);

	foreach ( $sections as $identifiant => $section ) {
		add_meta_box( $identifiant, $section[0], $section[1], 'mtb_chien', 'normal', 'high' );
	}
}
