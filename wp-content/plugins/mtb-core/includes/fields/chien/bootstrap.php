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
add_filter( 'wp_editor_settings', __NAMESPACE__ . '\\reglages_de_l_editeur' );
add_filter( 'mce_buttons_2', __NAMESPACE__ . '\\boutons_de_la_seconde_barre' );
add_filter( 'post_updated_messages', __NAMESPACE__ . '\\messages_enregistrement' );
add_filter( 'bulk_post_updated_messages', __NAMESPACE__ . '\\messages_par_lot', 10, 2 );
add_filter( 'get_sample_permalink_html', __NAMESPACE__ . '\\masquer_adresse_du_coeur', 10, 2 );

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
 * Messages affichés après l'enregistrement d'une fiche.
 *
 * Les libellés déclarés au type de contenu — « Fiche publiée. », « Fiche mise à jour. » — ne
 * servent que l'éditeur de blocs, dont l'écran de la fiche se passe. Sans ce filtre, le cœur
 * retombe sur les messages de l'article et annonce « Article publié. » après chaque
 * enregistrement : un mot que l'éleveuse ne doit jamais lire.
 *
 * @param array<string, array<int, string>> $messages Messages par type de contenu.
 *
 * @return array<string, array<int, string>>
 */
function messages_enregistrement( array $messages ): array {
	$messages['mtb_chien'] = array(
		0  => '',
		1  => 'Fiche mise à jour.',
		2  => 'Champ mis à jour.',
		3  => 'Champ supprimé.',
		4  => 'Fiche mise à jour.',
		5  => 'Fiche restaurée à sa version précédente.',
		6  => 'Fiche publiée.',
		7  => 'Fiche enregistrée.',
		8  => 'Fiche soumise à relecture.',
		9  => 'Fiche planifiée.',
		10 => 'Brouillon de la fiche mis à jour.',
	);

	return $messages;
}

/**
 * Messages affichés après une action sur plusieurs fiches à la fois.
 *
 * @param array<string, array<string, string>> $messages Messages par type de contenu.
 * @param array<string, int>                   $comptes  Nombre de fiches par action.
 *
 * @return array<string, array<string, string>>
 */
function messages_par_lot( array $messages, array $comptes ): array {
	$messages['mtb_chien'] = array(
		'updated'   => phrase( $comptes, 'updated', 'Fiche mise à jour.', 'fiches mises à jour.' ),
		'locked'    => phrase( $comptes, 'locked', "Une fiche n'a pas été modifiée : quelqu'un d'autre est en train de la modifier.", "fiches n'ont pas été modifiées : quelqu'un d'autre est en train de les modifier." ),
		'deleted'   => phrase( $comptes, 'deleted', 'Fiche supprimée définitivement.', 'fiches supprimées définitivement.' ),
		'trashed'   => phrase( $comptes, 'trashed', 'Fiche mise à la corbeille.', 'fiches mises à la corbeille.' ),
		'untrashed' => phrase( $comptes, 'untrashed', 'Fiche sortie de la corbeille.', 'fiches sorties de la corbeille.' ),
	);

	return $messages;
}

/**
 * Accorde une phrase de lot au nombre de fiches concernées.
 *
 * @param array<string, int> $comptes    Nombre de fiches par action.
 * @param string             $cle        Action concernée.
 * @param string             $singulier  Phrase pour une seule fiche.
 * @param string             $pluriel    Fin de phrase pour plusieurs fiches.
 */
function phrase( array $comptes, string $cle, string $singulier, string $pluriel ): string {
	$nombre = isset( $comptes[ $cle ] ) ? (int) $comptes[ $cle ] : 0;

	if ( 1 === $nombre ) {
		return $singulier;
	}

	return (string) $nombre . ' ' . $pluriel;
}

/**
 * Retire le bloc d'adresse que le cœur rend sous le titre.
 *
 * Deux raisons. Son intitulé emploie un mot interdit à l'écran. Et son bouton de modification est
 * un cul-de-sac depuis que la boîte d'origine est remplacée par la nôtre : le script du cœur
 * reporte l'adresse modifiée dans un champ qui n'existe plus, l'éleveuse croirait avoir changé
 * l'adresse et notre champ renverrait l'ancienne. Un seul endroit pour changer l'adresse, le
 * nôtre.
 *
 * Le filtre est global : la borne sur le type de contenu est ce qui l'empêche de vider ce bloc
 * sur les Pages et les articles.
 *
 * @param mixed $html    Balisage proposé par le cœur.
 * @param mixed $post_id Identifiant du contenu concerné.
 */
function masquer_adresse_du_coeur( $html, $post_id ): string {
	if ( 'mtb_chien' === get_post_type( (int) $post_id ) ) {
		return '';
	}

	return (string) $html;
}

/**
 * Vrai uniquement sur l'écran d'édition d'une fiche Chien.
 *
 * Les deux filtres ci-dessous sont **globaux** : sans cette borne, ils s'appliqueraient aussi à
 * l'éditeur des Pages, qui appartient à une autre issue. Ce débordement-là ne se verrait dans
 * aucun « git status », puisque aucun fichier étranger n'aurait été touché — il faut donc le
 * fermer ici, à l'exécution.
 *
 * get_current_screen() n'est pas définie partout où ces filtres passent : son absence signifie
 * « ce n'est pas mon écran », jamais une erreur.
 */
function ecran_de_fiche_chien(): bool {
	if ( ! function_exists( 'get_current_screen' ) ) {
		return false;
	}

	$ecran = get_current_screen();

	if ( ! $ecran instanceof \WP_Screen ) {
		return false;
	}

	return 'mtb_chien' === $ecran->post_type && 'post' === $ecran->base;
}

/**
 * Retire le bouton d'insertion de photo au-dessus de la zone de commentaire.
 *
 * Deux raisons, dans cet ordre d'importance. Une photo insérée au fil de la prose échapperait au
 * traitement des photos du système de design — cadrage, ratios, tailles — alors que la Galerie
 * photos, elle, le respecte : les photos ont un chemin, c'est celui-là. Et le libellé du bouton
 * emploie un mot interdit à l'écran, là où le vocabulaire dit « photo ».
 *
 * Conséquence assumée : plus d'image au milieu du texte.
 *
 * @param array<string, mixed> $reglages Réglages de la zone d'édition.
 *
 * @return array<string, mixed>
 */
function reglages_de_l_editeur( array $reglages ): array {
	if ( ! ecran_de_fiche_chien() ) {
		return $reglages;
	}

	$reglages['media_buttons'] = false;

	return $reglages;
}

/**
 * Retire les sélecteurs de couleur de la seconde barre d'outils.
 *
 * Ils mettent l'éleveuse à deux clics d'une couleur qui n'est pas dans la palette, et la valeur
 * part en style en ligne : aucune feuille de style ne peut la rattraper ensuite.
 *
 * @param array<int, string> $boutons Boutons de la seconde barre.
 *
 * @return array<int, string>
 */
function boutons_de_la_seconde_barre( array $boutons ): array {
	if ( ! ecran_de_fiche_chien() ) {
		return $boutons;
	}

	return array_values( array_diff( $boutons, array( 'forecolor', 'backcolor' ) ) );
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

	/*
	 * Le cœur ajoute lui-même une boîte pour l'adresse dès qu'un type de contenu a une page
	 * publique, et son titre emploie un mot interdit à l'écran. On ne se contente pas de la
	 * retirer : une fiche chien a bien une adresse — /chien/<nom-dusage>/ — et la retirer sans
	 * rien mettre à la place priverait l'éleveuse de la maîtrise de ses adresses. On la remplace
	 * donc par la même boîte, nommée dans son vocabulaire.
	 */
	remove_meta_box( 'slugdiv', 'mtb_chien', 'normal' );

	// Priorité basse, donc en dernier : c'est une boîte qu'on n'ouvre qu'en cas d'erreur.
	add_meta_box( 'mtb-chien-adresse', 'Adresse de la page', __NAMESPACE__ . '\\rendre_adresse', 'mtb_chien', 'normal', 'low' );
}
