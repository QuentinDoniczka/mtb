<?php
/**
 * Directive « robots » servie sur les contenus que l'ancien site tenait hors des moteurs.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\IndexationHeritee;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * POURQUOI ON HONORE LE « noindex », alors que le site source se contredit.
 *
 * Le fait, vérifié dans « docs/migration/source/html/ » : les cinq pages portent DEUX balises
 * « robots » contradictoires dans le même « <head> » — « noindex, nofollow » en tête, puis
 * « index,follow ». Trois arguments tranchent :
 *
 *   1. « index,follow » est une CONSTANTE DE GABARIT, présente sur les 54 fichiers archivés.
 *      « noindex, nofollow » est le SIGNAL DISCRIMINANT, présent sur 5 seulement, à une position
 *      que les 48 autres pages n'utilisent pas. Ce n'est pas une contradiction symétrique : c'est
 *      un ajout délibéré dans l'interface IONOS.
 *   2. Quand deux directives « robots » se contredisent, LA PLUS RESTRICTIVE S'APPLIQUE. Le
 *      comportement effectivement observable de l'ancien site était donc « noindex ». La contrainte
 *      4 — « rien de l'ancien site ne se perd » — porte sur ce comportement-là : ne pas l'honorer,
 *      ce serait CHANGER l'ancien site, pas le préserver.
 *   3. D11 ne couvre pas ce cas. Le BRIEF §14 énumère ce qui est une donnée de domaine : « aucun
 *      nom de chien, date, affixe, numéro LOF, résultat de test ou de concours ». Une directive
 *      « robots » n'en est aucune.
 *
 * LE TROU DE CONTRAINTE 1, ET COMMENT IL SE FERME. Un « noindex » posé par filtre est invisible et
 * irréversible depuis « wp-admin ». Il ne se ferme pas par un réglage — hors périmètre — mais PAR
 * LE GUIDE (D3) : « doc-client-mtb » livre une fiche nommant les cinq contenus. Un état invisible et
 * documenté vaut infiniment mieux qu'un état invisible et tu. Le retour en arrière coûte une ligne :
 * renommer le dossier de ce module en « _indexation-heritee », ce qui laisse les 46 redirections
 * intactes.
 */

/**
 * Rend « noindex, nofollow » sur les contenus portant le fait hérité.
 *
 * Priorité 20, après les rappels du cœur, pour que le retrait des clés contraires porte sur ce que
 * le cœur a réellement posé.
 *
 * « max-image-preview » est délibérément CONSERVÉ : ce n'est pas une directive contraire, et le
 * budget D8 de cette issue est chiffré sur une balise qui la garde.
 *
 * Le paramètre et le retour ne sont pas typés : un filtre tiers peut avoir rendu autre chose qu'un
 * tableau, et « strict_types » en ferait une erreur fatale dans le « <head> » d'une fiche publiée.
 *
 * @param mixed $robots Directives assemblées par le cœur et les filtres précédents.
 *
 * @return mixed Directives complétées, ou la valeur reçue si elle n'est pas un tableau.
 */
function marquer_noindex( $robots ) {
	if ( ! is_array( $robots ) ) {
		return $robots;
	}

	if ( ! is_singular() ) {
		return $robots;
	}

	if ( ! demande_noindex( (int) get_queried_object_id() ) ) {
		return $robots;
	}

	// Les deux seules clés contraires. Les laisser produirait « index, noindex » dans la même balise.
	unset( $robots['index'], $robots['follow'] );

	$robots['noindex']  = true;
	$robots['nofollow'] = true;

	return $robots;
}
