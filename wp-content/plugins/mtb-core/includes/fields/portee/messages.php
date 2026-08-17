<?php
/**
 * Messages du cœur après l'enregistrement d'une portée, et après une action groupée.
 *
 * Les libellés « item_published » et « item_updated » déclarés au type de contenu ne servent que
 * l'éditeur de blocs, qui est écarté sur cet écran. Sans les deux filtres de ce fichier, le cœur
 * retombe sur ses messages d'article et annonce « Article publié. » à chaque enregistrement — un
 * mot proscrit, sur l'écran que l'éleveuse voit le plus souvent.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Fields\Portee;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Remplace les messages d'enregistrement d'une portée.
 *
 * Paramètre non typé : sous strict_types, un filtre du cœur qui passerait autre chose qu'un tableau
 * provoquerait une erreur fatale.
 *
 * @param mixed $messages Messages de tous les types de contenu.
 *
 * @return array Messages complétés.
 */
function messages_enregistrement( $messages ): array {
	if ( ! is_array( $messages ) ) {
		return array();
	}

	$post = get_post();

	if ( ! $post instanceof \WP_Post || 'mtb_portee' !== $post->post_type ) {
		return $messages;
	}

	$publiee = ( 'private' === $post->post_status ) ? 'Portée publiée en privé.' : 'Portée publiée.';

	/*
	 * Les indices 2 et 3 nomment un champ sans dire « champ personnalisé », mot proscrit. Le panneau
	 * qui les déclenche n'est pas activé sur cet écran ; la phrase est là pour qu'aucune voie
	 * détournée ne fasse remonter le vocabulaire du cœur.
	 */
	$messages['mtb_portee'] = array(
		0  => '',
		1  => 'Portée mise à jour.' . lien_voir( $post ),
		2  => 'Champ mis à jour.',
		3  => 'Champ supprimé.',
		4  => 'Portée mise à jour.',
		5  => phrase_revision(),
		6  => $publiee . lien_voir( $post ),
		7  => 'Portée enregistrée.',
		8  => 'Portée soumise à relecture.' . lien_apercu( $post ),
		9  => phrase_planifiee( $post ) . lien_apercu( $post ),
		10 => 'Brouillon de la portée mis à jour.' . lien_apercu( $post ),
	);

	return $messages;
}

/**
 * Remplace les messages des actions groupées de la liste des portées.
 *
 * @param mixed $messages Messages de tous les types de contenu.
 * @param mixed $comptes  Nombre de contenus concernés, par action.
 *
 * @return array Messages complétés.
 */
function messages_par_lot( $messages, $comptes ): array {
	if ( ! is_array( $messages ) ) {
		return array();
	}

	if ( ! is_array( $comptes ) ) {
		return $messages;
	}

	$messages['mtb_portee'] = array(
		'updated'   => accorder( $comptes, 'updated', 'Portée mise à jour.', 'portées mises à jour.' ),
		'locked'    => accorder( $comptes, 'locked', 'Une portée n’a pas été modifiée : quelqu’un d’autre est en train de la modifier.', 'portées n’ont pas été modifiées : quelqu’un d’autre est en train de les modifier.' ),
		'deleted'   => accorder( $comptes, 'deleted', 'Portée supprimée définitivement.', 'portées supprimées définitivement.' ),
		'trashed'   => accorder( $comptes, 'trashed', 'Portée déplacée dans la corbeille.', 'portées déplacées dans la corbeille.' ),
		'untrashed' => accorder( $comptes, 'untrashed', 'Portée sortie de la corbeille.', 'portées sorties de la corbeille.' ),
	);

	return $messages;
}

/**
 * Compose un message d'action groupée en accordant le nombre.
 *
 * @param array  $comptes   Nombres fournis par le cœur.
 * @param string $cle       Action concernée.
 * @param string $singulier Phrase complète au singulier.
 * @param string $pluriel   Fin de phrase au pluriel, le nombre étant placé devant.
 *
 * @return string Phrase à afficher.
 */
function accorder( array $comptes, string $cle, string $singulier, string $pluriel ): string {
	$nombre = isset( $comptes[ $cle ] ) && is_scalar( $comptes[ $cle ] ) ? (int) $comptes[ $cle ] : 0;

	if ( 1 === $nombre ) {
		return $singulier;
	}

	return (string) $nombre . ' ' . $pluriel;
}

/**
 * Lien « Voir la portée » ajouté aux messages qui suivent une mise en ligne.
 *
 * @param \WP_Post $post Portée enregistrée.
 *
 * @return string Balisage échappé, chaîne vide si la portée n'a pas d'adresse.
 */
function lien_voir( \WP_Post $post ): string {
	$adresse = get_permalink( $post );

	if ( ! is_string( $adresse ) || '' === $adresse ) {
		return '';
	}

	return ' <a href="' . esc_url( $adresse ) . '">Voir la portée</a>';
}

/**
 * Lien « Aperçu de la portée » ajouté aux messages qui suivent un enregistrement hors ligne.
 *
 * @param \WP_Post $post Portée enregistrée.
 *
 * @return string Balisage échappé, chaîne vide si l'aperçu n'est pas disponible.
 */
function lien_apercu( \WP_Post $post ): string {
	$adresse = get_preview_post_link( $post );

	if ( ! is_string( $adresse ) || '' === $adresse ) {
		return '';
	}

	return ' <a target="_blank" href="' . esc_url( $adresse ) . '">Aperçu de la portée</a>';
}

/**
 * Phrase de la portée rétablie depuis une version antérieure.
 *
 * @return string|false Phrase à afficher, ou faux quand aucune version n'est désignée.
 */
function phrase_revision() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- lecture d'affichage, aucune écriture.
	$revision = isset( $_GET['revision'] ) && is_scalar( $_GET['revision'] ) ? absint( wp_unslash( $_GET['revision'] ) ) : 0;

	if ( 0 === $revision ) {
		return false;
	}

	$version = wp_post_revision_title( $revision, false );

	if ( ! is_string( $version ) || '' === $version ) {
		return 'Portée rétablie à sa version précédente.';
	}

	return 'Portée rétablie à la version du ' . $version . '.';
}

/**
 * Phrase de la portée planifiée, datée selon les réglages du site.
 *
 * La date et l'heure sont formatées par deux appels séparés : glisser un « à » dans un format de
 * date reviendrait à faire passer des lettres pour des jetons de format. date_i18n() est proscrite,
 * strtotime() aussi — l'horodatage vient du cœur, déjà calculé.
 *
 * @param \WP_Post $post Portée enregistrée.
 *
 * @return string Phrase à afficher.
 */
function phrase_planifiee( \WP_Post $post ): string {
	$horodatage = get_post_time( 'U', true, $post );

	if ( ! is_numeric( $horodatage ) ) {
		return 'Portée planifiée.';
	}

	$format_date = get_option( 'date_format' );
	$format_heure = get_option( 'time_format' );

	if ( ! is_string( $format_date ) || '' === $format_date ) {
		$format_date = 'j F Y';
	}

	if ( ! is_string( $format_heure ) || '' === $format_heure ) {
		$format_heure = 'H:i';
	}

	$jour = wp_date( $format_date, (int) $horodatage );
	$heure = wp_date( $format_heure, (int) $horodatage );

	if ( ! is_string( $jour ) || ! is_string( $heure ) ) {
		return 'Portée planifiée.';
	}

	return 'Portée planifiée pour le ' . $jour . ' à ' . $heure . '.';
}
