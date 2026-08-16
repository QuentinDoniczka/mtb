<?php
/**
 * Assainissement des valeurs recopiées d'un résultat de travail.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Content\Resultat;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Fichier partagé par les modules « content » et « fields » du résultat : la même fonction sert de
 * sanitize_callback à register_post_meta et de nettoyage explicite dans la routine de sauvegarde,
 * pour qu'il n'existe qu'une seule définition de « valeur propre ». Le garde rend une seconde
 * inclusion sans effet, quelle que soit la forme du chemin employé.
 */
if ( function_exists( __NAMESPACE__ . '\\assainir_texte_recopie' ) ) {
	return;
}

/**
 * Nettoie une valeur recopiée sans jamais en altérer le contenu.
 *
 * sanitize_text_field() est volontairement écartée : elle passe par wp_strip_all_tags(), donc par
 * strip_tags(), qui supprime tout ce qui suit un « < » jusqu'à un « > » — absent d'une valeur
 * ordinaire. Un niveau recopié d'un palmarès officiel commençant par « < » serait donc vidé sans
 * erreur ni avertissement, et un nom de chien recopié ne se réécrit jamais. Ce serait effacer par
 * outillage une donnée d'élevage réelle, exactement ce que D11 interdit. On retire donc les seuls
 * caractères de contrôle, on aplatit la valeur sur une ligne, on coupe les espaces de bord, et rien
 * d'autre : ni strip_tags, ni wp_kses, ni échappement. C'est sûr parce que l'échappement est
 * systématique en sortie et que seul un compte disposant d'« edit_post » écrit ici.
 *
 * @param mixed $valeur Valeur brute, telle qu'elle sort du formulaire ou d'une écriture programmée.
 *
 * @return string Valeur nettoyée, vide si la valeur reçue n'était pas un scalaire.
 */
function assainir_texte_recopie( $valeur ): string {
	if ( ! is_scalar( $valeur ) ) {
		return '';
	}

	// Rend la suite sûre pour le modificateur « u » : une entrée mal encodée ressort vide d'ici.
	$texte = wp_check_invalid_utf8( (string) $valeur );

	$nettoye = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $texte );

	if ( is_string( $nettoye ) ) {
		$texte = $nettoye;
	}

	$aplati = preg_replace( '/[\r\n\t]+/', ' ', $texte );

	if ( is_string( $aplati ) ) {
		$texte = $aplati;
	}

	return trim( $texte );
}
