<?php
/**
 * La moitié PHP : le rappel qui pose les libellés de la famille « photo » sur le type « page ».
 *
 * LA TABLE N'EST PAS ICI. Les quatre valeurs vivent dans « libelles.php », rendues par la fonction
 * « libelles_du_type() » ; ce fichier ne contient que le rappel de filtre qui les affecte au type de
 * contenu. Ce partage est écrit parce qu'il se cherche : la fonction porte le nom de la table, pas
 * celui du fichier qui l'emploie.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Admin\VocabulairePage;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rend les quatre libellés français à la place de ceux du cœur, pour le seul type « page ».
 *
 * SA PORTÉE EST LE NOM DU FILTRE, PAS UNE COMPARAISON DE CHAÎNE, et c'est la supériorité structurelle
 * de cette moitié sur l'autre. « post_type_labels_page » ne s'applique qu'au type « page » : le
 * débordement sur les Articles, les Médias, les Portées, les Chiens, les Résultats, les gabarits de
 * l'éditeur de site ou les écrans de taxonomie est IMPOSSIBLE PAR CONSTRUCTION, pas évité par
 * précaution. Corollaire : ce rappel ne peut pas tomber en panne parce que le cœur aurait reformulé
 * une chaîne — il n'en lit aucune. Les clés qu'il écrit sont l'API publique de register_post_type().
 *
 * AUCUNE GARDE « is_admin() », NI ICI NI EN TÊTE DU MODULE — voir l'en-tête de « bootstrap.php »,
 * où la mesure qui l'interdit est recopiée. En deux mots : le bouton de la photo lit ce libellé par
 * la façade REST, où is_admin() vaut faux ; sous cette garde le libellé serait INTERMITTENT.
 *
 * AUCUN « get_current_screen() », AUCUNE CONDITION DE CONTEXTE. Un libellé qui dépendrait de la
 * façade par laquelle on le lit est un libellé qui bat, et un libellé qui bat est pire qu'un libellé
 * resté en anglais.
 *
 * AUCUNE FONCTION DE TRADUCTION. Les quatre valeurs sont des chaînes littérales du dépôt : ni __(),
 * ni _x(), ni translate(). Le chargeur interdit d'ailleurs toute fonction de traduction avant « init »,
 * et ce filtre court précisément avant.
 *
 * ON N'ÉCHAPPE PAS, et c'est un écart délibéré à la règle générale du projet. Ce rappel N'IMPRIME
 * RIEN : il rend des chaînes que le cœur, ou React, échappe et imprime lui-même. Échapper ici
 * doublerait l'échappement et une apostrophe ressortirait en « &#039; » au milieu d'une étiquette.
 * Aucune des quatre valeurs n'en porte, ce qui rend le point théorique aujourd'hui et opposable demain.
 *
 * LE PARAMÈTRE ET LE RETOUR NE SONT PAS TYPÉS, comme « Medias\format_de_sortie() »,
 * « Corbeille\completer_messages() » et « DescriptionPhoto\remplacer_libelle() », et le motif est ici
 * PLUS GRAVE QU'AILLEURS : ce filtre court sur TOUTE requête, publique comprise, et il court à
 * l'amorçage. Sous strict_types, un filtre voisin ayant rendu autre chose qu'un objet transformerait
 * le calcul des libellés en erreur fatale au démarrage de WordPress — un site entièrement blanc, front
 * compris. Le contrôle défensif qui suit préfère rendre la valeur reçue telle quelle : un libellé
 * resté en anglais vaut infiniment mieux qu'un site blanc.
 *
 * @param mixed $libelles Objet des libellés que le cœur vient de calculer pour le type « page ».
 *
 * @return mixed Les libellés, la famille « photo » remplacée ; ou la valeur reçue si elle n'est pas
 *               exploitable.
 */
function remplacer_les_libelles( $libelles ) {
	if ( ! is_object( $libelles ) ) {
		return $libelles;
	}

	foreach ( libelles_du_type() as $cle => $libelle ) {
		$libelles->{$cle} = $libelle;
	}

	return $libelles;
}
