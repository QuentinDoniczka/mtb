<?php
/**
 * Composant « Grille de chiens » : enregistrement du bloc, de son script d'éditeur, et interface de
 * rendu offerte aux gabarits.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

/*
 * DEUX BLOCS D'ESPACE DE NOMS DANS UN SEUL FICHIER, ET C'EST VOULU.
 *
 * L'enregistrement du bloc est interne au module, donc dans l'espace de noms du module. La fonction
 * de rendu offerte aux gabarits est publique, donc dans l'espace de noms GLOBAL : un thème conforme
 * n'écrit jamais « MTB\ », c'est la frontière vérifiable par recherche entre le thème et
 * l'extension. PHP n'admet les deux dans un même fichier qu'avec la syntaxe à accolades, et interdit
 * alors le moindre code hors des accolades — y compris la garde ABSPATH, qui vit donc dans le
 * premier bloc.
 */

namespace MTB\Core\Blocks\GrilleChiens {

	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	/*
	 * render.php n'est jamais inclus ici : WordPress l'inclut lui-même, une fois par instance du bloc.
	 * L'inclure depuis le chargeur exécuterait son rendu hors de toute portée utile, et le fichier ne
	 * doit surtout pas déclarer de fonction — deux grilles sur la même page provoqueraient une erreur
	 * de compilation qu'aucun try/catch n'attrape.
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
			'mtb-grille-chiens-editeur',
			MTB_CORE_URL . 'includes/blocks/grille-chiens/editeur.js',
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render' ),
			MTB_CORE_VERSION,
			true
		);

		// Tout le texte affiché par l'éditeur vient d'ici : editeur.js n'en contient aucun.
		wp_localize_script( 'mtb-grille-chiens-editeur', 'mtbGrilleChiens', donnees_editeur() );

		register_block_type( __DIR__ );
	}
}

namespace {

	if ( ! function_exists( 'mtb_grille_chiens_rendu' ) ) {
		/**
		 * La grille de chiens, telle que le visiteur la voit, rendue pour un gabarit.
		 *
		 * Interface destinée aux gabarits des issues #16 et #17 — « La meute » et l'archive des
		 * chiens — pour que la grille ne soit jamais réécrite côté thème : une saisie, un rendu, un
		 * seul endroit où le balisage existe. À appeler sous « function_exists() », l'extension
		 * pouvant être désactivée.
		 *
		 * Le nom ne commence PAS par « mtb_get_ », et c'est délibéré : une fonction « mtb_get_* » rend
		 * des données brutes et jamais du HTML. Celle-ci rend du HTML, donc elle porte le préfixe du
		 * composant. « mtb_grille_chiens_* » est réservé à ce module : aucun autre ne peut l'ombrer.
		 *
		 * Précision honnête : dans un thème de blocs, un gabarit est un fichier HTML et ne peut pas
		 * appeler de PHP. Le chemin de réutilisation le plus probable pour #16 et #17 est donc
		 * d'insérer le bloc lui-même dans le gabarit — « <!-- wp:mtb/grille-chiens
		 * {"statut":"reproducteur"} /--> ». Cette fonction sert le cas où un « render.php » de
		 * gabarit, un motif dynamique ou un futur bloc de niveau gabarit en a besoin. Les deux
		 * chemins mènent au même code.
		 *
		 * N'imprime rien, et ne rend jamais l'état vide de l'éditeur : côté visiteur, un composant
		 * sans contenu ne s'affiche pas.
		 *
		 * @param string $statut « tous » — le défaut, tous les statuts groupés — ou une clé de statut.
		 *                       Toute valeur inconnue retombe sur « tous » : jamais un statut inventé.
		 *
		 * @return string Le balisage de la grille, ou une chaîne vide s'il n'y a rien à afficher.
		 */
		function mtb_grille_chiens_rendu( string $statut = 'tous' ): string {
			/*
			 * Un seul chemin de rendu, celui du bloc : l'assainissement du statut a lieu dans rendu(),
			 * par statut_demande(), et aucune ligne de balisage n'est recopiée ici. Le second argument
			 * dit seulement que WordPress ne rend pas une instance du bloc.
			 */
			return \MTB\Core\Blocks\GrilleChiens\rendu( array( 'statut' => $statut ), false );
		}
	}
}
