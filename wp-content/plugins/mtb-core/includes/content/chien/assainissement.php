<?php
/**
 * Assainissement des champs de la fiche Chien, à la frontière d'entrée.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Content\Chien;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Fichier partagé par les modules « content » et « fields » du chien : la même fonction sert de
 * sanitize_callback à register_post_meta et de nettoyage explicite dans la routine de sauvegarde.
 * Le garde rend une seconde inclusion sans effet, quelle que soit la forme du chemin employé.
 */
if ( function_exists( __NAMESPACE__ . '\\assainir_texte_recopie' ) ) {
	return;
}

/**
 * Nettoie une valeur recopiée sans jamais en altérer le contenu.
 *
 * sanitize_text_field() est volontairement écartée ici : elle passe par wp_strip_all_tags(), donc
 * une valeur de santé commençant par « < » (une diversité génétique inférieure à un seuil, par
 * exemple) serait silencieusement vidée. Ce serait inventer — par outillage — un fait d'élevage,
 * exactement ce que D11 interdit. On retire donc les caractères de contrôle et les sauts de ligne,
 * on coupe les espaces de bord, et rien d'autre. L'échappement a lieu au rendu, par esc_html().
 *
 * @param mixed $valeur Valeur brute, telle qu'elle sort du formulaire ou de l'API.
 *
 * @return string Valeur nettoyée, vide si la valeur reçue n'était pas un scalaire.
 */
function assainir_texte_recopie( $valeur ): string {
	if ( ! is_scalar( $valeur ) ) {
		return '';
	}

	$texte = (string) $valeur;

	/*
	 * Classe d'octets et non de caractères, sans le modificateur « u » : dans une séquence UTF-8
	 * multi-octets, tous les octets valent 0x80 ou plus, donc aucun caractère accentué n'est
	 * touché. Le modificateur « u » ferait renvoyer null sur une entrée mal encodée, ce qui
	 * viderait la valeur — précisément la perte que cette fonction existe pour éviter.
	 */
	$nettoye = preg_replace( '/[\x00-\x1F\x7F]+/', ' ', $texte );

	if ( is_string( $nettoye ) ) {
		$texte = $nettoye;
	}

	return trim( $texte );
}

/**
 * Nettoie une zone de texte en conservant les retours à la ligne, qui portent le sens ici :
 * une ligne = un test ou un titre.
 *
 * @param mixed $valeur Valeur brute.
 *
 * @return string Valeur nettoyée, lignes séparées par un saut de ligne simple.
 */
function assainir_texte_multiligne( $valeur ): string {
	if ( ! is_scalar( $valeur ) ) {
		return '';
	}

	$texte = str_replace( array( "\r\n", "\r" ), "\n", (string) $valeur );

	// Tous les caractères de contrôle sauf le saut de ligne (0x0A), qui est la structure du champ.
	$nettoye = preg_replace( '/[\x00-\x09\x0B-\x1F\x7F]+/', ' ', $texte );

	if ( is_string( $nettoye ) ) {
		$texte = $nettoye;
	}

	$lignes = array_map( 'trim', explode( "\n", $texte ) );

	return trim( implode( "\n", $lignes ) );
}

/**
 * Retient une valeur seulement si elle appartient à la liste fermée qui la gouverne.
 *
 * Seules les clés du tableau sont regardées : la forme des valeurs est indifférente, ce qui permet
 * d'y passer aussi bien sexes() que statuts(), dont chaque entrée est elle-même un tableau de formes.
 *
 * @param mixed                $valeur  Valeur brute.
 * @param array<string, mixed> $options Liste fermée, clé stockée => libellé ou formes du libellé.
 *
 * @return string Clé retenue, ou chaîne vide.
 */
function cle_de_liste( $valeur, array $options ): string {
	if ( ! is_scalar( $valeur ) ) {
		return '';
	}

	$cle = trim( (string) $valeur );

	return isset( $options[ $cle ] ) ? $cle : '';
}

/**
 * Assainit le champ Sexe.
 *
 * @param mixed $valeur Valeur brute.
 */
function assainir_sexe( $valeur ): string {
	return cle_de_liste( $valeur, sexes() );
}

/**
 * Assainit le champ Variété.
 *
 * @param mixed $valeur Valeur brute.
 */
function assainir_variete( $valeur ): string {
	return cle_de_liste( $valeur, varietes() );
}

/**
 * Assainit le champ Statut.
 *
 * @param mixed $valeur Valeur brute.
 */
function assainir_statut( $valeur ): string {
	return cle_de_liste( $valeur, statuts() );
}

/**
 * Assainit le champ ADN identifié.
 *
 * @param mixed $valeur Valeur brute.
 */
function assainir_oui_non( $valeur ): string {
	return cle_de_liste( $valeur, oui_non() );
}

/**
 * Assainit le champ Cadrage de la photo.
 *
 * @param mixed $valeur Valeur brute.
 */
function assainir_cadrage( $valeur ): string {
	return cle_de_liste( $valeur, cadrages() );
}

/**
 * Retient une date seulement si elle est complète et réelle.
 *
 * Aucune date n'est jamais devinée ni complétée : une saisie incompréhensible donne une valeur
 * vide, et la lecture émettra « Non renseigné ».
 *
 * @param mixed $valeur Valeur brute, attendue au format AAAA-MM-JJ.
 *
 * @return string Date au format AAAA-MM-JJ, ou chaîne vide.
 */
function assainir_date( $valeur ): string {
	if ( ! is_scalar( $valeur ) ) {
		return '';
	}

	$date = trim( (string) $valeur );

	if ( 1 !== preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $date, $morceaux ) ) {
		return '';
	}

	if ( ! checkdate( (int) $morceaux[2], (int) $morceaux[3], (int) $morceaux[1] ) ) {
		return '';
	}

	return $date;
}

/**
 * Assainit un lien externe.
 *
 * @param mixed $valeur Valeur brute.
 *
 * @return string URL retenue, ou chaîne vide.
 */
function assainir_url( $valeur ): string {
	if ( ! is_scalar( $valeur ) ) {
		return '';
	}

	return esc_url_raw( trim( (string) $valeur ) );
}

/**
 * Assainit un identifiant de fiche.
 *
 * @param mixed $valeur Valeur brute.
 */
function assainir_identifiant( $valeur ): int {
	if ( ! is_scalar( $valeur ) ) {
		return 0;
	}

	return absint( $valeur );
}

/**
 * Assainit une liste ordonnée d'identifiants de photos.
 *
 * Stockée en une seule ligne, séparateurs virgule : l'ordre est celui de la saisie, il porte le
 * sens de la galerie et doit être conservé.
 *
 * @param mixed $valeur Valeur brute.
 *
 * @return string Identifiants positifs, sans doublon, séparés par des virgules.
 */
function assainir_liste_identifiants( $valeur ): string {
	if ( ! is_array( $valeur ) && ! is_scalar( $valeur ) ) {
		return '';
	}

	$morceaux = is_array( $valeur ) ? $valeur : explode( ',', (string) $valeur );
	$retenus  = array();

	foreach ( $morceaux as $morceau ) {
		if ( ! is_scalar( $morceau ) ) {
			continue;
		}

		$identifiant = absint( $morceau );

		if ( 0 === $identifiant || in_array( $identifiant, $retenus, true ) ) {
			continue;
		}

		$retenus[] = $identifiant;
	}

	return implode( ',', $retenus );
}
