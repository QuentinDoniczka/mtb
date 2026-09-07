<?php
/**
 * L'interrupteur « en sommeil » dans le panneau « Résumé » de l'éditeur de blocs.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Fields\Sommeil;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Déclare le script de l'éditeur de blocs. Appelée sur « init », priorité 20.
 *
 * Les six dépendances sont exactement celles que le fichier consomme, et rien de plus : « wp-plugins »
 * pour registerPlugin, « wp-editor » et « wp-edit-post » pour les deux emplacements possibles du
 * panneau de statut, « wp-element » pour createElement, « wp-components » pour la case et l'encart
 * d'avertissement, « wp-data » pour la lecture et l'écriture de l'état. Une dépendance manquante
 * laisserait le panneau absent SANS ERREUR : la page s'enregistrerait, la case ne serait nulle part,
 * et rien au journal. C'est le contrôle E4 du protocole, joué au navigateur réel, qui prouve le
 * contraire — E3 seul ne prouve rien.
 *
 * @return void
 */
function declarer_le_script(): void {
	wp_register_script(
		'mtb-sommeil-editeur',
		MTB_CORE_URL . 'includes/fields/sommeil/editeur.js',
		array( 'wp-plugins', 'wp-editor', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data' ),
		MTB_CORE_VERSION,
		true
	);
}

/**
 * Met le script en file sur le seul écran d'édition d'une page, et lui passe ses chaînes.
 *
 * « enqueue_block_editor_assets » court sur TOUT écran qui monte un éditeur de blocs — l'éditeur de
 * site, l'éditeur de motifs, l'éditeur d'un widget. La garde d'écran est donc le module : sans elle,
 * le panneau tenterait de se monter là où « core/editor » ne décrit aucun contenu.
 *
 * « get_current_screen() » n'est pas définie partout où ce crochet passe : son absence signifie
 * « ce n'est pas mon écran », jamais une erreur.
 *
 * LES CHAÎNES VIENNENT DU SERVEUR, LE JAVASCRIPT LES IMPRIME. Il n'en compose aucune, n'en concatène
 * aucune et n'en traduit aucune : c'est ce qui garantit que le panneau « Résumé » et l'encadré
 * « Publier » de l'éditeur classique disent MOT POUR MOT la même chose, aujourd'hui et après le
 * prochain ajustement de rédaction. Le calcul de la page d'accueil se fait ici aussi, côté serveur,
 * par le couple d'options qui en décide — le JavaScript reçoit un booléen déjà tranché.
 *
 * « before » et non « after » : l'objet doit exister au moment où le fichier s'exécute.
 *
 * @return void
 */
function mettre_le_script_en_file(): void {
	if ( ! function_exists( 'get_current_screen' ) ) {
		return;
	}

	$ecran = get_current_screen();

	if ( ! $ecran instanceof \WP_Screen || 'page' !== $ecran->post_type || 'post' !== $ecran->base ) {
		return;
	}

	$contenu     = get_post();
	$identifiant = $contenu instanceof \WP_Post ? (int) $contenu->ID : 0;

	wp_enqueue_script( 'mtb-sommeil-editeur' );

	wp_add_inline_script(
		'mtb-sommeil-editeur',
		'window.mtbSommeil = ' . wp_json_encode(
			array(
				/*
				 * LA CLÉ AUSSI VIENT DU SERVEUR, pour le même motif que les phrases et pour un enjeu
				 * plus grave. Écrite en dur côté JavaScript, elle divergerait le jour où la constante
				 * PHP changerait : le panneau « Résumé » continuerait d'écrire sous l'ancien nom, la
				 * case cesserait de persister, la page répondrait 200 et le journal resterait vide.
				 * C'est la signature de panne exacte que toute cette issue combat. « CLE » est ici la
				 * seule et même constante que lisent la requête, l'écran classique et la conversion.
				 */
				'cle'           => \MTB\Core\Query\MiseEnSommeil\CLE,
				'libelle'       => libelle_case(),
				'aide'          => aide(),
				'accueil'       => est_la_page_d_accueil( $identifiant ),
				'avertissement' => avertissement_accueil(),
			)
		) . ';',
		'before'
	);
}
