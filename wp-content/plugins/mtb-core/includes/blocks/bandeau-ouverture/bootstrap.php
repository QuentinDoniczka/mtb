<?php
/**
 * Composant « Bandeau d'ouverture » : enregistrement du bloc, de son script d'éditeur, et
 * effacement conditionnel du titre rendu par le cœur.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Blocks\BandeauOuverture;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * AUCUNE GARDE « is_admin() » ICI, ET C'EST LE PIÈGE LE PLUS COÛTEUX DE CE DOSSIER.
 *
 * Les trois modules de « includes/fields/ » commencent par « if ( ! is_admin() ) { return; } », parce
 * qu'un écran de saisie n'a aucun sens côté public. Un module de « includes/blocks/ » qui recopierait
 * cette ligne ne s'enregistrerait PAS côté public : le composant fonctionnerait parfaitement dans
 * l'éditeur et disparaîtrait du site, sans erreur, sans avertissement, sur une page qui répond 200.
 * Un bloc doit s'enregistrer sur les trois façades — public (rendu), administration (insérteur) et
 * REST (aperçu de l'éditeur) — donc sans aucune garde de contexte.
 */

/*
 * render.php n'est jamais inclus ici : WordPress l'inclut lui-même, une fois par instance du bloc,
 * dans une portée de variables précise ( $attributes, $content, $block ). L'inclure depuis le
 * chargeur l'exécuterait hors de toute portée utile.
 */
require_once __DIR__ . '/titre-principal.php';

add_action( 'init', __NAMESPACE__ . '\\enregistrer', 20 );

/*
 * « render_block_core/post-title » et non « render_block » : le filtre dynamique par nom de bloc
 * (cœur ≥ 5.7) ne s'exécute que pour le bloc visé, au lieu d'être appelé pour chaque bloc de chaque
 * page, et son troisième paramètre donne l'instance — donc le contexte du contenu rendu.
 */
add_filter( 'render_block_core/post-title', __NAMESPACE__ . '\\effacer_le_titre_du_coeur', 10, 3 );

/**
 * Enregistre le script d'éditeur puis le bloc. Appelée sur « init », priorité 20.
 */
function enregistrer(): void {
	/*
	 * Le script est enregistré ici et block.json n'en porte que la POIGNÉE. Un
	 * « "editorScript": "file:./editeur.js" » ferait chercher à WordPress un « editeur.asset.php »
	 * produit par une étape de construction : il n'y en a aucune dans ce projet, et l'absence
	 * déclencherait un « _doing_it_wrong ». La poignée enregistrée à la main est officiellement
	 * prise en charge et strictement équivalente.
	 */
	wp_register_script(
		'mtb-bandeau-ouverture-editeur',
		MTB_CORE_URL . 'includes/blocks/bandeau-ouverture/editeur.js',
		array(
			'wp-blocks',
			'wp-element',
			'wp-block-editor',
			'wp-components',
			'wp-data',
			'wp-server-side-render',
		),
		MTB_CORE_VERSION,
		true
	);

	/*
	 * Trois clés absentes de block.json, volontairement, et qu'aucun composant sœur ne doit y ajouter :
	 *
	 * - « style » et « editorStyle » : l'extension n'émet aucune CSS. La feuille du composant est
	 *   servie par le thème, qui déduit son nom du nom du bloc.
	 * - « textdomain » : aucune fonction i18n dans mtb-core, le français est littéral. Une clé sans
	 *   catalogue suggérerait le contraire.
	 * - « viewScript » : ce composant n'a aucun JavaScript public, zéro octet.
	 *
	 * Et surtout : AUCUNE clé de « supports » à forme d'objet — color, typography, spacing,
	 * dimensions, border, shadow, background, filter, position, layout. Elles ne s'éteignent PAS en
	 * écrivant « false » : ce n'est pas dans leur schéma, la clé serait ignorée en silence et on
	 * croirait avoir posé un verrou. Elles s'éteignent en n'étant pas déclarées du tout, ce qui est
	 * le cas. theme.json verrouille les VALEURS offertes, block.json décide de l'EXISTENCE du
	 * contrôle : les deux sont nécessaires.
	 */
	register_block_type( __DIR__ );
}

/**
 * Efface le titre rendu par « core/post-title » quand le bandeau porte le titre principal de la page.
 *
 * C'est ce qui garantit exactement un « h1 » par page : le bandeau émet le sien uniquement dans les
 * cas où celui du cœur est retiré, et la décision est prise par une seule fonction, jamais
 * réimplémentée ici.
 *
 * @param string        $contenu       HTML rendu par le bloc du cœur.
 * @param array<mixed>  $bloc_analyse  Bloc tel que l'analyseur l'a produit, inutilisé.
 * @param \WP_Block     $instance      Instance rendue, seule source du contexte de contenu.
 *
 * @return string Chaîne vide quand le bandeau porte le titre, le contenu inchangé sinon.
 */
function effacer_le_titre_du_coeur( string $contenu, array $bloc_analyse, \WP_Block $instance ): string {
	unset( $bloc_analyse );

	/*
	 * Le contexte est la source de vérité quand il est peuplé — il l'est parce que block.json
	 * déclare « usesContext ». Le repli sur le contenu interrogé couvre les gabarits où le cœur
	 * n'injecte pas la clé : le mécanisme d'injection est un détail interne, on ne s'y fie pas seul.
	 */
	$post_id = isset( $instance->context['postId'] ) ? (int) $instance->context['postId'] : 0;

	if ( 0 === $post_id ) {
		$post_id = get_queried_object_id();
	}

	if ( doit_porter_le_titre_principal( $post_id ) ) {
		return '';
	}

	return $contenu;
}
