<?php
/**
 * Composant « Tableau de résultats » : enregistrement du bloc, de son script d'éditeur, et interface
 * de rendu offerte aux gabarits.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

/*
 * DEUX BLOCS D'ESPACE DE NOMS DANS UN SEUL FICHIER, ET C'EST VOULU.
 *
 * L'enregistrement du bloc est interne au module, donc dans l'espace de noms du module. Les deux
 * fonctions de rendu offertes aux gabarits sont publiques, donc dans l'espace de noms GLOBAL : un
 * thème conforme n'écrit jamais « MTB\ », c'est la frontière vérifiable par recherche entre le
 * thème et l'extension. PHP n'admet les deux dans un même fichier qu'avec la syntaxe à accolades,
 * et interdit alors le moindre code hors des accolades — y compris la garde ABSPATH, qui vit donc
 * dans le premier bloc.
 */

namespace MTB\Core\Blocks\TableauResultats {

	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	/*
	 * Seuls des fichiers écrits dans le même commit que celui-ci sont requis. Un require vers un
	 * fichier absent est une erreur de compilation qu'aucun try/catch n'attrape : tout le site
	 * tomberait, l'administration comprise.
	 *
	 * render.php n'est jamais inclus ici : WordPress l'inclut lui-même, une fois par instance du
	 * bloc. Il ne doit surtout déclarer aucune fonction — deux tableaux sur la même page
	 * provoqueraient un « Cannot redeclare function ».
	 */
	require_once __DIR__ . '/donnees.php';
	require_once __DIR__ . '/balisage.php';

	/*
	 * La catégorie d'insérteur « Mont Brabant » appartient au module « includes/blocks/categorie-mtb/ »,
	 * qui la déclare une seule fois pour tout le catalogue ; ce composant s'y raccroche par le seul
	 * « "category": "mtb" » de son block.json, et ne porte donc aucun filtre « block_categories_all ».
	 */
	add_action( 'init', __NAMESPACE__ . '\\enregistrer', 20 );

	/**
	 * Enregistre le script d'éditeur puis le bloc. Appelée sur « init », priorité 20.
	 */
	function enregistrer(): void {
		/*
		 * Le script est enregistré ici et « block.json » n'en porte que la poignée. Un
		 * « file:./editeur.js » ferait chercher à WordPress un fichier de dépendances produit par une
		 * étape de construction : il n'y en a pas, et l'absence déclencherait un avertissement.
		 */
		wp_register_script(
			'mtb-tableau-resultats-editeur',
			MTB_CORE_URL . 'includes/blocks/tableau-resultats/editeur.js',
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render' ),
			MTB_CORE_VERSION,
			true
		);

		/*
		 * Tout le texte affiché par l'éditeur vient d'ici : editeur.js n'en contient aucun. La liste
		 * des disciplines est un tableau littéral, sans la moindre requête — rien à différer.
		 */
		wp_localize_script( 'mtb-tableau-resultats-editeur', 'mtbTableauResultats', donnees_editeur() );

		register_block_type( __DIR__ );
	}
}

namespace {

	/*
	 * HABILLAGE DES DEUX FONCTIONS CI-DESSOUS : ÉCRIT ICI POUR NE PAS RESTER IMPLICITE.
	 *
	 * L'extension ne produit aucune règle visuelle, et ces deux fonctions n'en mettent aucune en
	 * file d'attente — c'est le thème qui habille, et lui seul. Le tableau qu'elles rendent porte
	 * « class="mtb-tableau" », primitive que le thème sert sur TOUTES ses pages depuis sa feuille
	 * de base : filets de ligne, légende masquée, en-tête découpé et repli en lignes libellées sous
	 * 48 rem en découlent sans que l'appelant ait quoi que ce soit à déclarer.
	 *
	 * CE QUE CELA IMPLIQUE POUR UN GABARIT, ET C'EST MESURÉ. Le cœur n'attache la feuille propre à
	 * un bloc qu'au filtre « render_block », c'est-à-dire au rendu d'une INSTANCE du bloc : un
	 * gabarit qui appelle l'une de ces fonctions ne le déclenche pas, et n'obtient donc pas cette
	 * feuille-là. Vérifié le 2026-08-20 sur une fiche chien rendue par un gabarit PHP, à 360 px et à
	 * 1440 px : le tableau est complet et correct, sans elle, et lui servir la feuille du bloc ne
	 * change aucune valeur calculée. Aucune parade n'est donc écrite ici.
	 *
	 * ET SURTOUT, AUCUNE NE PASSERAIT PAR « render_block() ». Router ces fonctions par le cœur pour
	 * déclencher le filtre coûterait le troisième paramètre de « rendu() » : un bloc ne transporte
	 * son identifiant que dans son contexte, et un contexte à zéro se lit « aucun contexte », pas
	 * « celui-ci et aucun autre ». Mesuré sur la pile : par cette route,
	 * « mtb_tableau_resultats_du_chien_rendu( 0 ) » appelée depuis la fiche d'un chien rendait le
	 * palmarès DE CE CHIEN au lieu de rien. L'appel direct rend une chaîne vide, comme promis.
	 */

	if ( ! function_exists( 'mtb_tableau_resultats_rendu' ) ) {
		/**
		 * Les résultats de travail en tableau, tels que le visiteur les voit, rendus pour un gabarit.
		 *
		 * Le nom ne commence PAS par « mtb_get_ », et c'est délibéré : une fonction « mtb_get_* »
		 * rend des données brutes et jamais du HTML. « mtb_tableau_resultats_* » est réservé à ce
		 * module. À appeler sous « function_exists() », l'extension pouvant être désactivée.
		 *
		 * L'ordre n'est pas réglable : les tableaux de discipline se lisent de l'année la plus
		 * récente à la plus ancienne, et c'est la seule lecture juste de cet écran.
		 *
		 * N'imprime rien, et ne rend jamais l'état vide de l'écran d'édition : côté visiteur, un
		 * composant sans contenu ne s'affiche pas.
		 *
		 * @param string $discipline Chaîne vide — le défaut — pour un tableau par discipline, dans
		 *                           l'ordre de la fonction de lecture. Une clé de discipline
		 *                           n'affiche que ce tableau-là.
		 *
		 * @return string Le balisage, ou une chaîne vide s'il n'y a rien à afficher.
		 */
		function mtb_tableau_resultats_rendu( string $discipline = '' ): string {
			return \MTB\Core\Blocks\TableauResultats\rendu(
				array(
					'source'     => 'discipline',
					'discipline' => $discipline,
				),
				false
			);
		}
	}

	if ( ! function_exists( 'mtb_tableau_resultats_du_chien_rendu' ) ) {
		/**
		 * Le palmarès de travail d'une fiche chien, rendu pour un gabarit.
		 *
		 * Un seul tableau, sans titre de section : celui-ci appartient au gabarit de la fiche.
		 * L'ordre n'est pas réglable non plus : une carrière se lit de la première année à la
		 * dernière.
		 *
		 * Le suffixe reprend celui de la fonction de lecture correspondante : le même mot désigne la
		 * même sélection, et qui connaît l'une devine l'autre.
		 *
		 * @param int $chien_id Identifiant de la fiche chien. Zéro, ou un identifiant qui n'est pas
		 *                      une fiche chien, rend une chaîne vide — jamais le palmarès d'un autre.
		 *
		 * @return string Le balisage, ou une chaîne vide s'il n'y a rien à afficher.
		 */
		function mtb_tableau_resultats_du_chien_rendu( int $chien_id ): string {
			return \MTB\Core\Blocks\TableauResultats\rendu(
				array( 'source' => 'chien-courant' ),
				false,
				$chien_id
			);
		}
	}
}
