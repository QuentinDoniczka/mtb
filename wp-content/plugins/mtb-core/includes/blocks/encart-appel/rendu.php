<?php
/**
 * Composant « Encart d’appel » — fonctions d’aide du rendu public.
 *
 * Ce fichier est inclus UNE SEULE FOIS, par « bootstrap.php ». Il est le seul du module à déclarer
 * des fonctions : « render.php », lui, est inclus par le cœur avec un « require » nu, donc une fois
 * par encart présent sur la page, et le bloc autorise plusieurs encarts par page.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Blocks\EncartAppel;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retient le numéro à afficher, dans l’ordre : saisie de la page, puis source centrale.
 *
 * La valeur rendue est celle qui est STOCKÉE, jamais une mise en forme : la mise en forme est le
 * travail de « telephone_affiche() », et le « href » celui de « telephone_href() ».
 *
 * Il n’y a que deux crans, et plus de constante de repli : l’écran « Coordonnées de l’élevage »
 * l’a remplacée. « mtb_get_telephone_elevage() » est la source d’autorité Y COMPRIS QUAND ELLE REND
 * VIDE — sans quoi un numéro que l’éleveuse a délibérément effacé reviendrait sur le site, et
 * l’encart ne saurait jamais se rendre sans téléphone. La garde « function_exists() » reste, elle,
 * indispensable : elle couvre le module de lecture absent ou désactivé.
 *
 * Son retour est traité défensivement — toute forme inattendue vaut « rien » — pour qu’aucune page
 * portant un encart ne puisse tomber sur un TypeError.
 *
 * @param string $saisi Valeur enregistrée du réglage « Téléphone affiché ».
 *
 * @return string Numéro tel qu’il est stocké, ou chaîne vide s’il n’y en a aucun.
 */
function telephone_retenu( string $saisi ): string {
	$saisi = trim( $saisi );

	if ( '' !== $saisi ) {
		return $saisi;
	}

	if ( function_exists( 'mtb_get_telephone_elevage' ) ) {
		$lu = mtb_get_telephone_elevage();

		// Les deux formes admises par le contrat : la chaîne nue, ou l’enveloppe de champ.
		if ( is_array( $lu ) ) {
			$lu = $lu['valeur'] ?? '';
		}

		if ( is_string( $lu ) ) {
			$lu = trim( $lu );

			if ( '' !== $lu ) {
				return $lu;
			}
		}
	}

	return '';
}

/**
 * Met le numéro en forme pour la lecture — la seule mise en forme autorisée.
 *
 * Dix chiffres et rien d’autre : groupage par paires, comme un numéro français se lit. Toute autre
 * forme s’imprime verbatim, parce qu’une saisie que l’on ne comprend pas est une saisie que l’on
 * n’a pas le droit de réécrire. Les chiffres et leur ordre sont intacts dans les deux cas : c’est un
 * rendu, pas une correction.
 *
 * @param string $valeur Numéro tel qu’il est stocké.
 *
 * @return string Numéro tel qu’il s’affiche.
 */
function telephone_affiche( string $valeur ): string {
	if ( 1 !== preg_match( '/^[0-9]{10}$/', $valeur ) ) {
		return $valeur;
	}

	return implode( ' ', str_split( $valeur, 2 ) );
}

/**
 * Extrait les seuls chiffres d’un numéro. Usage interne à la composition du « href ».
 *
 * @param string $valeur Numéro tel qu’il est stocké.
 *
 * @return string Les chiffres, dans leur ordre d’origine.
 */
function telephone_chiffres( string $valeur ): string {
	return (string) preg_replace( '/[^0-9]/', '', $valeur );
}

/**
 * Compose l’adresse « tel: », ou rien du tout.
 *
 * Deux bornes, et une abstention. Moins de quatre chiffres : il n’y a pas de numéro. Plus de quinze :
 * c’est la borne haute d’un numéro composable, et une saisie du genre « 06 … ou 04 … » en donnerait
 * une vingtaine — le lien composerait alors les deux numéros bout à bout. Mieux vaut pas de lien
 * qu’un mauvais numéro composé : le numéro reste lisible et sélectionnable, dans un « span ».
 *
 * Aucun indicatif n’est ajouté : la forme nationale est celle que le brief donne, et en ajouter un
 * serait dériver un fait absent. À ne pas « corriger » plus tard.
 *
 * @param string $valeur Numéro tel qu’il est stocké.
 *
 * @return string Adresse « tel: », ou chaîne vide si le numéro n’est pas composable.
 */
function telephone_href( string $valeur ): string {
	$chiffres = telephone_chiffres( $valeur );
	$longueur = strlen( $chiffres );

	if ( $longueur < 4 || $longueur > 15 ) {
		return '';
	}

	return 'tel:' . $chiffres;
}

/**
 * Retient la page du bouton, dans l’ordre : choix de la page, source centrale, aucune.
 *
 * L’identifiant retenu passe ensuite la validation de « page_utilisable() ». Une page choisie puis
 * supprimée ne bascule PAS en silence vers la page de contact centrale : le choix de l’éditrice
 * n’est jamais remplacé par un autre à son insu — il ne produit simplement plus de bouton, et
 * l’éditeur le lui dit.
 *
 * « mtb_get_page_contact() » rend un identifiant, jamais une adresse : le libellé du bouton est le
 * titre de la page, donc ce module a besoin du contenu et pas seulement de son URL.
 *
 * @param mixed $attribut Valeur enregistrée du réglage « Page vers laquelle mène le bouton ».
 *
 * @return int Identifiant validé, ou 0 s’il n’y a pas de bouton à rendre.
 */
function page_retenue( $attribut ): int {
	$identifiant = absint( is_numeric( $attribut ) ? $attribut : 0 );

	if ( 0 === $identifiant && function_exists( 'mtb_get_page_contact' ) ) {
		$lu = mtb_get_page_contact();

		$identifiant = absint( is_numeric( $lu ) ? $lu : 0 );
	}

	if ( 0 === $identifiant ) {
		return 0;
	}

	return page_utilisable( $identifiant );
}

/**
 * Vérifie qu’une page peut réellement porter le bouton — quatre conditions, toutes nécessaires.
 *
 * Une seule fausse et le bouton n’existe pas du tout : jamais de bouton mort, jamais de « href="#" »,
 * jamais de bouton grisé. Rien d’autre ne bouge dans l’encart pour autant.
 *
 * Le mot de passe est écarté pour deux raisons : on n’envoie pas un appel à l’action vers un mur, et
 * « get_the_title() » préfixerait de surcroît le libellé du bouton par la mention de protection.
 *
 * @param mixed $identifiant Identifiant de page, d’où qu’il vienne.
 *
 * @return int L’identifiant s’il est utilisable, 0 sinon.
 */
function page_utilisable( $identifiant ): int {
	$page = absint( is_numeric( $identifiant ) ? $identifiant : 0 );

	if ( 0 === $page ) {
		return 0;
	}

	if ( 'page' !== get_post_type( $page ) ) {
		return 0;
	}

	if ( 'publish' !== get_post_status( $page ) ) {
		return 0;
	}

	if ( '' !== (string) get_post_field( 'post_password', $page ) ) {
		return 0;
	}

	// get_permalink() rend « false » sur une page introuvable ; le transtypage ramène au même cas.
	if ( '' === (string) get_permalink( $page ) ) {
		return 0;
	}

	if ( '' === trim( (string) get_the_title( $page ) ) ) {
		return 0;
	}

	return $page;
}
