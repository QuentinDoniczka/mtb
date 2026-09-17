<?php
/**
 * La troisième moitié : « Slug » renommé dans la Modification rapide des listes Pages, Portées et Chiens.
 *
 * POURQUOI UNE TROISIÈME MOITIÉ, ET POURQUOI EN PHP. Dans les listes, l'étiquette du champ d'adresse du
 * panneau « Modification rapide » n'a rien à voir avec la rangée de l'écran d'une page : elle est émise
 * côté serveur par « WP_Posts_List_Table::inline_edit() », qui appelle « _e( 'Slug' ) », domaine
 * « default ». Le script de ce module n'y est pas chargé, et il n'y servirait à rien : le script du
 * cœur qui ouvre le panneau ne fait que cloner un gabarit déjà présent dans le HTML. Relevé sur
 * WORDPRESS 6.9, LE 2026-09-17. Le catalogue français traduit « Slug » par « Slug » : ce n'est pas une
 * traduction manquante.
 *
 * LE MÉCANISME. « translate() » applique le filtre « gettext », puis « gettext_{domaine} ». On se pose
 * sur « gettext_default » : le NOM DU CROCHET borne le domaine, sans aucune comparaison de domaine dans
 * le rappel. Le filtre n'est posé que sur trois listes, depuis « load-edit.php », seul crochet qui
 * précède à coup sûr la rangée : « quick_edit_show_taxonomy » n'existe que pour un type qui a des
 * taxonomies, et « quick_edit_custom_box » arrive après la rangée.
 *
 * LA GARDE D'ÉCRAN VIT DANS LE RAPPEL DE « load-edit.php », JAMAIS DANS LE RAPPEL DE TRADUCTION. Le
 * rappel de traduction est appelé pour chaque chaîne de la liste ; il ne fait qu'une recherche par clé.
 *
 * LA TABLE N'EST PAS ICI : elle vit dans « libelles.php », sous « table_modification_rapide() ».
 *
 * MODE DE PANNE. Si le cœur reformule « Slug », ou cesse de déclencher « load-edit.php », la liste redit
 * « Slug ». Rien n'est cassé, rien n'est perdu, et rien ne le signale.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Admin\VocabulairePage;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Les types de contenu dont la Modification rapide dit « Adresse de la page ».
 *
 * ÉCRITS EN DUR, ET AUCUN N'EST AJOUTÉ SANS MESURE.
 *
 * Les Articles (« post ») sont EXCLUS VOLONTAIREMENT : l'issue #59 vise trois types, l'éleveuse n'écrit
 * pas d'articles, et le « Slug » qui y reste est la preuve visible que la garde tient. Dette T-#59-a.
 * Ne l'ajoutez pas « en passant ».
 *
 * Les Résultats (« mtb_resultat ») sont ABSENTS parce que leur panneau n'a pas de rangée d'adresse :
 * le cœur ne l'émet que pour un type consultable qui gère un titre, et ce type n'est ni l'un ni
 * l'autre. Mesuré le 2026-09-17 : aucun champ d'adresse dans ce panneau.
 *
 * @return array<int, string> Noms des types visés.
 */
function types_de_la_modification_rapide(): array {
	return array( 'page', 'mtb_portee', 'mtb_chien' );
}

/**
 * Pose le renommage sur la seule liste d'un type visé.
 *
 * « get_current_screen() » absente ou sans écran signifie « CE N'EST PAS MON ÉCRAN », jamais une
 * erreur. « 'edit' !== base » écarte tout autre écran qui déclencherait ce crochet ; la comparaison des
 * types est stricte.
 *
 * AUCUNE GARDE « is_admin() », ICI NI EN TÊTE DU MODULE, ET IL NE FAUT PAS EN « AJOUTER » UNE. Ici elle
 * serait inutile : « load-edit.php » ne se déclenche que depuis « wp-admin/admin.php ». En tête du
 * module elle serait nuisible : elle tuerait le filtre des libellés photo sur la façade REST — la
 * mesure est recopiée dans l'en-tête de « bootstrap.php ».
 *
 * Sur la façade REST, WP-CLI, « admin-ajax » et le site public, ce crochet ne se déclenche pas : rien
 * n'est attaché. L'enregistrement d'une Modification rapide passe par « admin-ajax » et ne réémet pas
 * le panneau : il n'a rien à renommer.
 *
 * @return void
 */
function poser_le_renommage_de_la_modification_rapide(): void {
	if ( ! function_exists( 'get_current_screen' ) ) {
		return;
	}

	$ecran = get_current_screen();

	if ( ! $ecran instanceof \WP_Screen || 'edit' !== $ecran->base ) {
		return;
	}

	if ( ! in_array( $ecran->post_type, types_de_la_modification_rapide(), true ) ) {
		return;
	}

	add_filter( 'gettext_default', __NAMESPACE__ . '\\renommer_dans_la_modification_rapide', 10, 2 );
}

/**
 * Rend le libellé français pour les chaînes de la table, la traduction reçue partout ailleurs.
 *
 * QUATRE INTERDITS, CHACUN POUR UNE PANNE PRÉCISE.
 * AUCUNE FONCTION DE TRADUCTION : nous sommes dans le filtre de traduction, le moindre appel se
 * rappellerait lui-même.
 * AUCUN ÉCHAPPEMENT : ce rappel n'imprime rien. « _e() » imprime la valeur rendue TELLE QUELLE, sans
 * l'échapper : c'est la valeur elle-même, écrite dans « libelles.php » sans « < », « > » ni « & », qui
 * rend cette impression sûre. Échapper ici doublerait l'échappement partout où le cœur échappe
 * lui-même la chaîne.
 * AUCUN TEST D'ÉCRAN : il est fait une fois, avant de poser ce filtre.
 * COMPARAISON PAR CLÉ EXACTE, sans expression régulière ni remplacement partiel : c'est ce qui interdit
 * au renommage de déborder sur une phrase qui contiendrait le mot par hasard.
 *
 * LES PARAMÈTRES ET LE RETOUR NE SONT PAS TYPÉS, comme « remplacer_les_libelles() » : sous
 * strict_types, un filtre voisin qui rendrait autre chose qu'une chaîne ferait d'un libellé une erreur
 * fatale au milieu de la liste. Une valeur inattendue est rendue telle quelle.
 *
 * @param mixed $traduction Traduction que le cœur s'apprête à rendre.
 * @param mixed $texte      Chaîne source anglaise, telle qu'écrite dans le cœur.
 *
 * @return mixed Le libellé français pour nos chaînes, la traduction reçue partout ailleurs.
 */
function renommer_dans_la_modification_rapide( $traduction, $texte ) {
	if ( ! is_string( $texte ) ) {
		return $traduction;
	}

	$table = table_modification_rapide();

	if ( ! isset( $table[ $texte ] ) ) {
		return $traduction;
	}

	return $table[ $texte ];
}
