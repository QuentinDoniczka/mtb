<?php
/**
 * Composition du titre d'un résultat de travail.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Fields\Resultat;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Titre affiché quand aucune des quatre parties n'est renseignée.
 *
 * C'est une consigne d'administration, pas un fait d'élevage : elle rend la ligne incomplète
 * repérable dans la liste.
 */
const TITRE_INCOMPLET = 'Résultat de travail (à compléter)';

/**
 * Compose le titre à partir des seules valeurs enregistrées.
 *
 * Format : discipline — chien — niveau — année. Les parties absentes sont omises, jamais comblées.
 * Le titre est un objet d'administration : il n'apparaît sur aucune page publique et aucun
 * consommateur ne le lit.
 *
 * @param int $post_id Identifiant du résultat.
 *
 * @return string Titre composé, jamais vide.
 */
function composer_titre( int $post_id ): string {
	$parties = array(
		libelle_discipline( (string) get_post_meta( $post_id, '_mtb_discipline', true ) ),
		nom_du_chien( $post_id ),
		(string) get_post_meta( $post_id, '_mtb_niveau', true ),
		annee_en_texte( absint( get_post_meta( $post_id, '_mtb_annee', true ) ) ),
	);

	$retenues = array();

	foreach ( $parties as $partie ) {
		if ( '' !== $partie ) {
			$retenues[] = $partie;
		}
	}

	if ( array() === $retenues ) {
		return TITRE_INCOMPLET;
	}

	return implode( ' — ', $retenues );
}

/**
 * Libellé de la discipline, ou la valeur brute si elle est hors de la liste close.
 *
 * @param string $cle Clé stockée.
 *
 * @return string Libellé, chaîne vide si aucune discipline n'est renseignée.
 */
function libelle_discipline( string $cle ): string {
	if ( '' === $cle ) {
		return '';
	}

	$disciplines = liste_disciplines();

	return isset( $disciplines[ $cle ] ) ? $disciplines[ $cle ] : $cle;
}

/**
 * Nom du chien : celui de la fiche si elle est choisie, sinon le nom recopié.
 *
 * @param int $post_id Identifiant du résultat.
 *
 * @return string Nom, chaîne vide si aucun chien n'est renseigné.
 */
function nom_du_chien( int $post_id ): string {
	$chien_id = absint( get_post_meta( $post_id, '_mtb_chien_id', true ) );

	if ( 0 < $chien_id ) {
		$fiche = get_post( $chien_id );

		if ( $fiche instanceof \WP_Post && '' !== (string) $fiche->post_title ) {
			return (string) $fiche->post_title;
		}
	}

	return (string) get_post_meta( $post_id, '_mtb_chien_nom', true );
}

/**
 * Année en chaîne décimale brute.
 *
 * Aucun formatage de nombre : « 2 021 » serait produit.
 *
 * @param int $annee Année stockée, zéro si absente.
 *
 * @return string Année, chaîne vide si absente.
 */
function annee_en_texte( int $annee ): string {
	return 0 === $annee ? '' : (string) $annee;
}
