<?php
/**
 * Composant « Coordonnées et plan d'accès » — déclaration du script d'édition, injection des valeurs
 * par défaut et enregistrement du bloc.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Blocks\CoordonneesPlan;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * AUCUNE GARDE « is_admin() » ICI : un bloc doit s'enregistrer sur les trois façades — public
 * (rendu), administration (insérteur) et REST (aperçu de l'éditeur). Une garde de contexte le ferait
 * fonctionner dans l'éditeur et disparaître du site, sans erreur, sur une page qui répond 200.
 *
 * Les trois fichiers requis ci-dessous existent : un « require » vers un fichier absent met tout le
 * site en erreur fatale, wp-admin compris, le chargeur relançant l'exception quand WP_DEBUG est vrai.
 *
 * « render.php » n'est jamais inclus ici : le cœur l'inclut lui-même, une fois par instance du bloc,
 * dans une portée de variables précise ( $attributes, $content, $block ).
 */
require_once __DIR__ . '/coordonnees.php';
require_once __DIR__ . '/rendu.php';
require_once __DIR__ . '/interface.php';

add_action( 'init', __NAMESPACE__ . '\\enregistrer', 20 );

/*
 * Accroché à l'inclusion et non sur un hook : le filtre doit être en place AVANT
 * register_block_type_from_metadata(), qui l'exécute au moment de l'enregistrement, donc dès
 * « init » priorité 20. add_filter() n'accède ni à la base, ni aux traductions : c'est l'une des
 * rares choses qu'un bootstrap.php a le droit de faire à l'inclusion.
 */
add_filter( 'block_type_metadata', __NAMESPACE__ . '\\defauts_du_bloc' );

/**
 * Déclare le script d'édition, puis enregistre le bloc — dans cet ordre.
 *
 * L'ordre compte : « block.json » ne porte que la POIGNÉE du script, et le cœur exige qu'elle soit
 * déjà connue au moment de l'enregistrement. Un « "editorScript": "file:./editeur.js" » ferait
 * chercher au cœur un « editeur.asset.php » produit par une étape de construction : il n'y en a
 * aucune dans ce projet, et l'absence déclencherait un « _doing_it_wrong ».
 */
function enregistrer(): void {
	wp_register_script(
		'mtb-coordonnees-plan-editeur',
		MTB_CORE_URL . 'includes/blocks/coordonnees-plan/editeur.js',
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
	 * Clés volontairement absentes de block.json, et qu'aucun composant sœur ne doit y ajouter :
	 *
	 * - « style », « viewStyle » et « editorStyle » : l'extension n'émet aucune règle visuelle. La
	 *   feuille du composant vit dans le thème, qui déduit son nom du nom du bloc.
	 * - « viewScript » et « script » : zéro octet de JavaScript servi au visiteur.
	 * - « textdomain » : aucune fonction d'internationalisation dans mtb-core, le français est
	 *   littéral. Une clé sans catalogue suggérerait le contraire.
	 *
	 * Et aucun add_filter( 'block_categories_all' ) : la catégorie « Mont Brabant » est livrée une
	 * seule fois, par includes/blocks/categorie-mtb/.
	 */
	register_block_type( __DIR__ );
}

/**
 * Injecte les coordonnées de référence de l'élevage dans les valeurs par défaut du bloc.
 *
 * « block.json » est du JSON statique : aucun appel de fonction n'y est évaluable, et y recopier
 * l'adresse, le numéro et le courriel en ferait une deuxième source de vérité, qui dériverait de
 * mtb_coordonnees_elevage() au premier changement. Le filtre du cœur s'exécute DANS
 * register_block_type_from_metadata(), donc avant l'enregistrement : les valeurs atteignent le rendu
 * comme l'inspecteur de l'éditeur.
 *
 * Mode de panne, et il est bénin : si le filtre ne s'appliquait pas, un bloc fraîchement inséré
 * aurait trois attributs vides — donc rien côté public et l'état vide côté éditeur, immédiatement
 * visible. Jamais une valeur fausse, jamais un silence.
 *
 * @param array<string,mixed> $metadata Métadonnées lues dans le block.json en cours d'enregistrement.
 *
 * @return array<string,mixed> Les mêmes métadonnées, valeurs par défaut renseignées.
 */
function defauts_du_bloc( array $metadata ): array {
	// Garde sur le nom : le filtre est appelé pour CHAQUE bloc enregistré, cœur compris.
	if ( ! isset( $metadata['name'] ) || 'mtb/coordonnees-plan' !== $metadata['name'] ) {
		return $metadata;
	}

	$reference = mtb_coordonnees_elevage();

	foreach ( array( 'adresse', 'telephone', 'courriel' ) as $champ ) {
		if ( isset( $metadata['attributes'][ $champ ] ) && is_array( $metadata['attributes'][ $champ ] ) ) {
			$metadata['attributes'][ $champ ]['default'] = $reference[ $champ ];
		}

		// L'aperçu de l'insérteur montre le composant rempli, sans recopier une deuxième fois les
		// valeurs : elles viennent de la même table de constantes que les défauts.
		if ( isset( $metadata['example']['attributes'] ) && is_array( $metadata['example']['attributes'] ) ) {
			$metadata['example']['attributes'][ $champ ] = $reference[ $champ ];
		}
	}

	return $metadata;
}
