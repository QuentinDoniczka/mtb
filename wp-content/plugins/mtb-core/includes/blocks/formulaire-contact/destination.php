<?php
/**
 * Composant « Formulaire de contact » — d'où vient l'adresse de destination, et les recours.
 *
 * Fichier inclus UNE SEULE FOIS, par « bootstrap.php ».
 *
 * INTERDIT ABSOLU, VÉRIFIABLE PAR RECHERCHE : aucun littéral d'adresse de courriel dans ce module.
 * « includes/query/coordonnees/option.php » détient le SEUL littéral du dépôt. Un second
 * exemplaire ferait tomber la moitié « destination modifiable sans toucher un fichier » de
 * l'exigence D1 : l'éleveuse changerait son adresse dans l'écran « Coordonnées » et le formulaire
 * continuerait d'écrire à l'ancienne, sans la moindre erreur.
 *
 * Les quatre fonctions de lecture consommées le sont toutes SOUS GARDE « function_exists() », et
 * leur retour est traité défensivement — toute forme inattendue vaut « rien ». Sans quoi une page
 * portant ce bloc tomberait sur un TypeError le jour où le module de lecture serait désactivé.
 * Ce module n'appelle JAMAIS « get_option() » lui-même (décision 19).
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Blocks\FormulaireContact;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adresse enregistrée dans l'écran « Coordonnées », telle qu'elle est stockée.
 *
 * Recopiée, jamais corrigée : « assainir_ligne() » ne retire que des caractères de contrôle et des
 * espaces de bord, il ne reformate pas une adresse. La validité est jugée ailleurs, et ne change
 * jamais ce qui est affiché.
 *
 * @return string Adresse brute, ou chaîne vide.
 */
function destination_brute(): string {
	if ( ! function_exists( 'mtb_get_coordonnees_elevage' ) ) {
		return '';
	}

	$coordonnees = mtb_get_coordonnees_elevage();

	if ( ! is_array( $coordonnees ) || ! isset( $coordonnees['courriel'] ) ) {
		return '';
	}

	$courriel = $coordonnees['courriel'];

	/*
	 * L'enveloppe de la décision 18 est la forme que le contrat #38 gèle pour CETTE fonction. La
	 * branche « chaîne nue » est une ceinture, et elle en est une pour une raison précise : le type
	 * de retour ne garantit que le tableau de tête, jamais la forme de ses éléments.
	 */
	if ( is_array( $courriel ) ) {
		$courriel = $courriel['valeur'] ?? '';
	}

	return assainir_ligne( $courriel );
}

/**
 * État de la destination, en trois mots — et rien d'autre ne sort d'ici vers l'éditeur.
 *
 * L'adresse elle-même ne transite JAMAIS vers le script d'édition : celui-ci n'a besoin que de
 * savoir laquelle des trois phrases d'état vide afficher.
 *
 * « invalide » et « absente » sont distingués parce que les deux phrases diffèrent : l'une dit
 * qu'il n'y a pas de courriel, l'autre que celui qui est enregistré n'en est pas un. Les confondre
 * enverrait l'éleveuse chercher un champ vide qui ne l'est pas.
 *
 * @return string « presente », « invalide » ou « absente ».
 */
function etat_destination(): string {
	$adresse = destination_brute();

	if ( '' === $adresse ) {
		return 'absente';
	}

	/*
	 * Écart déclaré (contrat §13.7) : une adresse enregistrée mais refusée par « is_email() » est
	 * traitée comme absente pour l'envoi. On n'écrit pas à une adresse dont le cœur dit qu'elle
	 * n'en est pas une, et la corriger serait modifier une valeur recopiée.
	 * « is_email() » refuse aussi les adresses internationalisées (partie locale non ASCII) —
	 * limite du cœur, écrite au contrat §13.14, non contournée ici.
	 */
	if ( ! is_email( $adresse ) ) {
		return 'invalide';
	}

	return 'presente';
}

/**
 * Adresse à laquelle le message part réellement.
 *
 * @return string Adresse utilisable, ou chaîne vide s'il n'y en a aucune.
 */
function destination(): string {
	if ( 'presente' !== etat_destination() ) {
		return '';
	}

	return destination_brute();
}

/**
 * Dit si le composant a une destination à qui écrire.
 *
 * Hors soumission, une destination inutilisable fait DISPARAÎTRE le bloc côté public (décision 26) :
 * un formulaire qui n'écrit nulle part est une promesse fausse. L'éleveuse, elle, voit dans
 * l'éditeur la phrase qui dit ce qui manque.
 *
 * @return bool Vrai quand un message peut partir.
 */
function destination_utilisable(): bool {
	return '' !== destination();
}

/**
 * Adresse « mailto: » de la ligne de recours, dérivée par la fonction de lecture publique.
 *
 * Jamais recomposée ici : « mtb_coordonnees_lien_courriel() » est la seule autorité, et elle rend
 * une chaîne vide quand l'adresse n'est pas valide. Le rendu affiche alors l'adresse en texte nu.
 *
 * @param string $adresse Adresse telle qu'elle est enregistrée.
 *
 * @return string URI « mailto:… », non échappée, ou chaîne vide.
 */
function lien_de_recours_courriel( string $adresse ): string {
	if ( '' === $adresse || ! function_exists( 'mtb_coordonnees_lien_courriel' ) ) {
		return '';
	}

	$lien = mtb_coordonnees_lien_courriel( $adresse );

	return is_string( $lien ) ? $lien : '';
}

/**
 * Numéro de téléphone de la seconde ligne de recours.
 *
 * Rendu tel qu'il est stocké : le groupage par paires appartient à l'affichage d'« encart-appel »
 * (décision 38) et ne remonte jamais dans la source. Sans numéro, la ligne de recours n'existe
 * pas — on n'écrit pas « Non renseigné » à côté de « appeler l'élevage ».
 *
 * @return string|null Numéro tel qu'il est stocké, ou null quand le réglage est vide.
 */
function telephone_de_recours(): ?string {
	if ( ! function_exists( 'mtb_get_telephone_elevage' ) ) {
		return null;
	}

	$numero = mtb_get_telephone_elevage();

	/*
	 * Le contrat #38 gèle « ?string » sur CETTE fonction, et son arbitrage 1 dit pourquoi :
	 * l'enveloppe de la décision 18 n'appartient qu'à « mtb_get_coordonnees_elevage() ». Un tableau
	 * ne peut donc pas arriver ici, et le déballer serait un garde-fou qui ne garde rien
	 * (décision 46). Reste « null », qui est le réglage vidé par l'éleveuse, jamais une panne.
	 */
	if ( ! is_string( $numero ) ) {
		return null;
	}

	$numero = assainir_ligne( $numero );

	return '' === $numero ? null : $numero;
}

/**
 * Adresse « tel: » de la ligne de recours, dérivée par la fonction de lecture publique.
 *
 * Jamais recomposée ici : « mtb_coordonnees_lien_telephone() » compacte les espaces sans rien
 * ajouter — ni indicatif, ni zéro de tête. Sans elle, le numéro s'affiche en texte nu plutôt que
 * dans un lien qui composerait un mauvais numéro.
 *
 * @param string $numero Numéro tel qu'il est stocké.
 *
 * @return string URI « tel:… », non échappée, ou chaîne vide.
 */
function lien_de_recours_telephone( string $numero ): string {
	if ( '' === $numero || ! function_exists( 'mtb_coordonnees_lien_telephone' ) ) {
		return '';
	}

	$lien = mtb_coordonnees_lien_telephone( $numero );

	return is_string( $lien ) ? $lien : '';
}
