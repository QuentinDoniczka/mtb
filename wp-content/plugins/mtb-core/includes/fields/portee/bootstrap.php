<?php
/**
 * Écran de saisie d'une portée : boîtes, script, enregistrement et avis.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Fields\Portee;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/ecran.php';
require_once __DIR__ . '/sauvegarde.php';
require_once __DIR__ . '/avis.php';

add_action( 'add_meta_boxes', __NAMESPACE__ . '\\declarer_boites', 10, 1 );
add_action( 'edit_form_after_title', __NAMESPACE__ . '\\intituler_le_commentaire', 10, 1 );
add_action( 'save_post_mtb_portee', __NAMESPACE__ . '\\enregistrer_champs', 10, 3 );
add_action( 'init', __NAMESPACE__ . '\\declarer_script', 20 );
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\mettre_le_script_en_file', 10, 1 );
add_action( 'admin_notices', __NAMESPACE__ . '\\afficher_avis', 10 );

add_filter( 'use_block_editor_for_post_type', __NAMESPACE__ . '\\ecarter_editeur_de_blocs', 10, 2 );
add_filter( 'enter_title_here', __NAMESPACE__ . '\\texte_fantome_du_titre', 10, 2 );
add_filter( 'get_sample_permalink_html', __NAMESPACE__ . '\\masquer_adresse_de_la_page', 10, 2 );
add_filter( 'redirect_post_location', __NAMESPACE__ . '\\taire_le_message_de_publication', 10, 2 );
add_filter( 'mce_buttons_2', __NAMESPACE__ . '\\retirer_la_couleur_du_texte', 10, 2 );
add_filter( 'wp_editor_settings', __NAMESPACE__ . '\\reglages_de_l_editeur', 10, 2 );

/**
 * Dit si l'écran d'administration en cours est celui d'une portée.
 *
 * get_current_screen() n'existe pas hors administration et peut valoir null même dedans, avant que
 * l'écran ne soit posé : les deux cas se testent.
 *
 * @return bool Vrai sur l'écran de saisie d'une portée.
 */
function sommes_nous_sur_une_portee(): bool {
	if ( ! function_exists( 'get_current_screen' ) ) {
		return false;
	}

	$ecran = get_current_screen();

	return null !== $ecran && 'mtb_portee' === $ecran->post_type;
}

/**
 * Retire les deux choix de couleur de la barre d'outils, sur le seul écran d'une portée.
 *
 * Deux clics y suffisent à poser une couleur hors des quinze jetons du système de design, en style
 * en ligne, dans une prose qu'aucune feuille de style ne pourra plus rattraper. « backcolor », la
 * couleur de fond, produit exactement le même effet que « forecolor » : les deux partent ensemble.
 *
 * Le filtre est global : sans la garde d'écran, il toucherait aussi les Pages, qui ne sont pas de
 * cette issue. Chaque module borne sur son propre écran, jamais sur ceux des autres.
 *
 * @param mixed $boutons Boutons de la deuxième rangée.
 * @param mixed $editeur Identifiant de l'éditeur concerné.
 *
 * @return array Boutons retenus.
 */
function retirer_la_couleur_du_texte( $boutons, $editeur ): array {
	unset( $editeur );

	if ( ! is_array( $boutons ) || ! sommes_nous_sur_une_portee() ) {
		return is_array( $boutons ) ? $boutons : array();
	}

	return array_values( array_diff( $boutons, array( 'forecolor', 'backcolor' ) ) );
}

/**
 * Ajuste l'éditeur natif sur le seul écran d'une portée.
 *
 * Le bouton d'insertion du cœur s'intitule « Ajouter un média », mot interdit à l'écran. Les photos
 * d'une portée passent par « Photo principale » et par « Galerie photos ».
 *
 * @param mixed $reglages Réglages proposés.
 * @param mixed $editeur  Identifiant de l'éditeur concerné.
 *
 * @return array Réglages retenus.
 */
function reglages_de_l_editeur( $reglages, $editeur ): array {
	if ( ! is_array( $reglages ) ) {
		return array();
	}

	if ( 'content' !== (string) $editeur || ! sommes_nous_sur_une_portee() ) {
		return $reglages;
	}

	$reglages['media_buttons'] = false;

	return $reglages;
}

/**
 * Une portée se remplit, elle ne se compose pas : l'éditeur de blocs est écarté pour ce contenu.
 *
 * Paramètres non typés : sous strict_types, un filtre du cœur qui passerait autre chose qu'une
 * chaîne provoquerait une erreur fatale.
 *
 * @param mixed $utiliser Décision proposée.
 * @param mixed $type     Type de contenu examiné.
 *
 * @return bool Faux pour une portée, la décision reçue sinon.
 */
function ecarter_editeur_de_blocs( $utiliser, $type ): bool {
	if ( 'mtb_portee' === (string) $type ) {
		return false;
	}

	return (bool) $utiliser;
}

/**
 * Remplace le texte fantôme du champ titre par le nom métier de ce qu'on y saisit.
 *
 * @param mixed $texte Texte fantôme proposé.
 * @param mixed $post  Contenu en cours de modification.
 *
 * @return string Texte fantôme retenu.
 */
function texte_fantome_du_titre( $texte, $post ): string {
	if ( $post instanceof \WP_Post && 'mtb_portee' === $post->post_type ) {
		return 'Identifiant de la portée — exemple : A3 2025';
	}

	return (string) $texte;
}

/**
 * Retire l'encart d'adresse que le cœur pose sous le titre d'une portée.
 *
 * Cet encart emploie le mot « permalien », interdit à l'écran. La capacité, elle, n'est pas retirée :
 * la boîte « Adresse de la page » de l'écran de saisie porte le même champ sous son libellé français.
 *
 * @param mixed $html    Balisage proposé par le cœur.
 * @param mixed $post_id Identifiant du contenu concerné.
 *
 * @return string Balisage retenu.
 */
function masquer_adresse_de_la_page( $html, $post_id ): string {
	if ( 'mtb_portee' === get_post_type( (int) $post_id ) ) {
		return '';
	}

	return (string) $html;
}

/**
 * Déclare le script de l'écran de saisie, sur « init » 20.
 *
 * Aucune étape de construction : le fichier est du JavaScript ordinaire, servi tel quel.
 */
function declarer_script(): void {
	wp_register_script(
		'mtb-portee-ecran',
		MTB_CORE_URL . 'includes/fields/portee/ecran.js',
		array( 'media-editor' ),
		MTB_CORE_VERSION,
		true
	);
}

/**
 * Met le script et la fenêtre de choix des photos en file, sur le seul écran d'une portée.
 *
 * @param mixed $suffixe Suffixe de la page d'administration en cours.
 */
function mettre_le_script_en_file( $suffixe ): void {
	if ( 'post.php' !== (string) $suffixe && 'post-new.php' !== (string) $suffixe ) {
		return;
	}

	if ( ! sommes_nous_sur_une_portee() ) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_script( 'mtb-portee-ecran' );
}
