<?php
/**
 * La garde d'écran et la mise en file de la moitié JavaScript.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Admin\VocabulairePage;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dit si la requête en cours rend l'écran d'édition d'une page.
 *
 * Garde recopiée telle quelle de « includes/fields/sommeil/editeur-de-blocs.php », qui borne le même
 * écran : les deux modules doivent mordre exactement au même endroit, et deux formulations
 * différentes finiraient par diverger.
 *
 * « get_current_screen() » n'est pas définie partout où « enqueue_block_editor_assets » passe : son
 * absence signifie « CE N'EST PAS MON ÉCRAN », jamais une erreur. Retour immédiat, aucun script mis
 * en file.
 *
 * « 'post' !== $ecran->base » est ce qui écarte l'ÉDITEUR DE SITE, l'éditeur de motifs et l'éditeur de
 * widgets, où ce même crochet court aussi. Ces trois écrans sont de surcroît fermés à l'éleveuse, qui
 * n'a ni « switch_themes » ni « edit_theme_options ».
 *
 * @return bool Vrai sur le seul écran d'édition d'une page.
 */
function sommes_nous_sur_une_page(): bool {
	if ( ! function_exists( 'get_current_screen' ) ) {
		return false;
	}

	$ecran = get_current_screen();

	if ( ! $ecran instanceof \WP_Screen || 'page' !== $ecran->post_type || 'post' !== $ecran->base ) {
		return false;
	}

	return true;
}

/**
 * Met le script du renommage en file, sur le seul écran d'édition d'une page.
 *
 * C'EST ICI, ET NULLE PART AILLEURS, QUE LE CONTEXTE SE TESTE. Le module ne porte aucune garde à son
 * chargement : la moitié PHP serait tuée par elle. Le partage est celui que
 * « includes/fields/sommeil/bootstrap.php » documente déjà — l'un des deux crochets a besoin de la
 * garde, l'autre serait tué par elle.
 *
 * LES DEUX SEULES DÉPENDANCES SONT « wp-hooks » ET « wp-i18n », ET LA SUITE EST CONTRE-INTUITIVE,
 * DONC ÉCRITE. « wp-hooks » est la seule dépendance fonctionnelle : c'est elle qui fournit
 * addFilter(). « wp-i18n » est déclarée pour que notre script soit imprimé après le paquet dont il
 * modifie le comportement. « wp-editor » ET « wp-edit-post » NE SONT JAMAIS DÉCLARÉES : le contrat
 * gelé de l'issue #54 l'interdit, au motif qu'elles inverseraient l'ordre d'impression et tueraient le
 * filtre. Le réflexe naturel — « mon filtre agit sur l'éditeur, donc je dépends de l'éditeur » — est
 * exactement le geste à ne pas faire, et il ne laisserait aucune trace : la rangée redirait « Slug »,
 * la page s'enregistrerait, le journal resterait vide.
 *
 * CE QUE CETTE FORME EXIGE, ET QUI SE VÉRIFIE AU NAVIGATEUR : le filtre doit être posé AVANT le
 * premier rendu de l'éditeur. Il l'est parce que le fichier s'exécute à l'analyse du script, alors que
 * le cœur monte l'éditeur au « domReady » qui suit. Aucune sonde posée à la main dans une console ne
 * prouve ce point — elle arrive forcément après le rendu. Le contrôle honnête est de charger l'écran
 * et de lire la rangée telle qu'elle s'affiche, SANS aucune interaction.
 *
 * LES CHAÎNES VIENNENT DU SERVEUR, LE JAVASCRIPT NE FAIT QUE LES RENDRE. « wp_json_encode() » échappe
 * le non-ASCII en « \uXXXX » : la charge part en ASCII pur et le mojibake est structurellement nul.
 * Propriété gelée par le contrat #52, et interdit opposable de la contourner en composant une chaîne
 * côté JavaScript.
 *
 * « before » et non « after » : l'objet doit exister au moment où le fichier s'exécute.
 *
 * Le script n'est ni enregistré ailleurs, ni déclaré sur « init » : personne d'autre n'a besoin de sa
 * poignée, et l'enregistrer plus tôt n'avancerait rien.
 *
 * @return void
 */
function mettre_le_script_en_file(): void {
	if ( ! sommes_nous_sur_une_page() ) {
		return;
	}

	wp_enqueue_script(
		'mtb-vocabulaire-page',
		MTB_CORE_URL . 'includes/admin/vocabulaire-page/editeur.js',
		array( 'wp-hooks', 'wp-i18n' ),
		MTB_CORE_VERSION,
		true
	);

	wp_add_inline_script(
		'mtb-vocabulaire-page',
		'window.mtbVocabulairePage = ' . wp_json_encode( array( 'table' => table_javascript() ) ) . ';',
		'before'
	);
}
