<?php
/**
 * Enregistrement d'un résultat de travail.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Fields\Resultat;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enregistre les huit champs, puis recompose le titre.
 *
 * Ordre imposé, sans exception : sortie sur enregistrement automatique ou révision, vérification du
 * jeton, vérification de la capacité, retrait des échappements puis assainissement champ par champ,
 * écriture, et seulement ensuite composition du titre.
 *
 * @param int      $post_id     Identifiant du résultat.
 * @param \WP_Post $post        Résultat tel qu'il vient d'être enregistré.
 * @param bool     $mise_a_jour Vrai s'il s'agit d'une modification.
 */
function enregistrer_champs( int $post_id, \WP_Post $post, bool $mise_a_jour ): void {
	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}

	$jeton = isset( $_POST['mtb_resultat_nonce'] )
		? sanitize_text_field( wp_unslash( $_POST['mtb_resultat_nonce'] ) )
		: '';

	if ( '' === $jeton || false === (bool) wp_verify_nonce( $jeton, 'mtb_resultat_saisie' ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$valeurs = array(
		'_mtb_discipline' => sanitize_key( champ_envoye( 'mtb_resultat_discipline' ) ),
		'_mtb_chien_id'   => absint( champ_envoye( 'mtb_resultat_chien_id' ) ),
		'_mtb_chien_nom'  => sanitize_text_field( champ_envoye( 'mtb_resultat_chien_nom' ) ),
		'_mtb_sexe'       => sanitize_key( champ_envoye( 'mtb_resultat_sexe' ) ),
		'_mtb_annee'      => absint( champ_envoye( 'mtb_resultat_annee' ) ),
		'_mtb_niveau'     => sanitize_text_field( champ_envoye( 'mtb_resultat_niveau' ) ),
		'_mtb_conducteur' => sanitize_text_field( champ_envoye( 'mtb_resultat_conducteur' ) ),
		'_mtb_pays'       => sanitize_text_field( champ_envoye( 'mtb_resultat_pays' ) ),
	);

	/*
	 * wp_slash() est réappliqué juste avant l'écriture : update_post_meta() retire lui-même un
	 * niveau d'échappement. Sans ce rappel, une valeur contenant une barre oblique inverse la
	 * perdrait ; avec lui, l'aller-retour est neutre et rien ne s'accumule d'un enregistrement à
	 * l'autre.
	 */
	foreach ( $valeurs as $cle => $valeur ) {
		update_post_meta( $post_id, $cle, wp_slash( $valeur ) );
	}

	appliquer_titre( $post_id, (string) $post->post_title );
}

/**
 * Lit une valeur envoyée par le formulaire, échappements retirés.
 *
 * Un champ absent de l'envoi est traité comme vide : l'écran présente les huit champs à chaque
 * enregistrement, une absence signale donc un effacement, jamais un « ne pas toucher ».
 *
 * Le jeton et la capacité sont vérifiés par l'appelant, avant tout appel à cette fonction.
 *
 * @param string $nom Nom du contrôle dans le formulaire.
 *
 * @return string Valeur brute désessorée, chaîne vide si absente.
 */
function champ_envoye( string $nom ): string {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	if ( ! isset( $_POST[ $nom ] ) || is_array( $_POST[ $nom ] ) ) {
		return '';
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	return (string) wp_unslash( $_POST[ $nom ] );
}

/**
 * Recompose le titre et ne l'écrit que s'il a changé.
 *
 * Deux mécanismes étaient possibles. Le filtre « wp_insert_post_data » évite la récursion, mais
 * s'exécute avant l'écriture des champs : il faudrait y recomposer le titre depuis l'envoi brut,
 * donc dupliquer tout l'assainissement et se retrouver avec deux sources de vérité pour la même
 * chaîne. Le retrait temporaire du rappel garde une source unique — les valeurs enregistrées — au
 * prix d'un seul appel encadré, et cet appel est entièrement évité quand le titre est inchangé :
 * un ré-enregistrement sans modification ne produit aucune écriture de titre.
 *
 * @param int    $post_id       Identifiant du résultat.
 * @param string $titre_courant Titre tel qu'il est actuellement enregistré.
 */
function appliquer_titre( int $post_id, string $titre_courant ): void {
	$titre = composer_titre( $post_id );

	if ( $titre === $titre_courant ) {
		return;
	}

	remove_action( 'save_post_mtb_resultat', __NAMESPACE__ . '\\enregistrer_champs', 10 );

	// Même raison que pour les champs : wp_update_post() retire un niveau d'échappement.
	wp_update_post(
		array(
			'ID'         => $post_id,
			'post_title' => wp_slash( $titre ),
		)
	);

	add_action( 'save_post_mtb_resultat', __NAMESPACE__ . '\\enregistrer_champs', 10, 3 );
}
