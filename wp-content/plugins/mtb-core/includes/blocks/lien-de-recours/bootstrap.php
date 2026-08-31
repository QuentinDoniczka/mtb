<?php
/**
 * Composant « Lien de recours » — déclaration du script d'édition et enregistrement du bloc.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Blocks\LienDeRecours;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * PREMIER COMPOSANT NON INSÉRABLE DU CATALOGUE, et c'est délibéré.
 *
 * « supports.inserter » vaut false dans block.json : le bloc rend un « li », qui n'a aucun sens hors
 * d'une liste. Offert dans l'insérteur, il permettrait de poser un élément de liste orphelin au
 * milieu d'une page, sans que rien n'explique pourquoi il s'affiche de travers. Il est posé une fois
 * pour toutes dans « 404.html » et « search.html » par le thème.
 *
 * Conséquences à assumer : les dix autres modules de ce dossier sont des composants OFFERTS à
 * l'éleveuse ; celui-ci ne l'est pas. Il n'a donc pas de fiche d'aide et n'en aura pas — D3 vise ce
 * qu'elle manipule, et elle ne verra jamais ce bloc. « category » reste « mtb » par cohérence, sans
 * effet visible : l'insérteur ne l'affiche nulle part.
 *
 * Aucune garde « is_admin() » : un bloc doit s'enregistrer sur les trois façades — public (rendu),
 * administration et REST. La recopier ferait disparaître le composant du site sans erreur ni
 * avertissement, sur une page qui répond 200.
 */

/*
 * La résolution de la destination vit dans un fichier à part, inclus une seule fois ici.
 * « render.php », lui, est inclus par le cœur avec un « require » nu, donc une fois par lien présent
 * sur la page : une déclaration de fonction qui y figurerait ferait tomber le site entier dès le
 * deuxième lien.
 */
require_once __DIR__ . '/rendu.php';

add_action( 'init', __NAMESPACE__ . '\\enregistrer', 20 );

/**
 * Déclare le script d'édition, puis enregistre le bloc — dans cet ordre.
 *
 * L'ordre compte : « block.json » ne porte que la poignée du script, et le cœur exige qu'elle soit
 * déjà connue au moment de l'enregistrement du bloc. Aucune étape de construction n'est employée ;
 * écrire « file:./editeur.js » ferait chercher au cœur un fichier d'actifs que seule une étape de
 * construction produit, et déclencherait un « _doing_it_wrong ».
 */
function enregistrer(): void {
	/*
	 * Trois dépendances : le bloc n'a aucun réglage à offrir, donc ni « wp-components », ni
	 * « wp-data », ni « wp-server-side-render ». Le script n'existe que pour éviter, dans l'éditeur
	 * de site, le cadre « ce bloc n'est pas pris en charge » sur un gabarit où le bloc est légitime ;
	 * le visiteur n'en reçoit pas un octet.
	 */
	wp_register_script(
		'mtb-lien-de-recours-editeur',
		MTB_CORE_URL . 'includes/blocks/lien-de-recours/editeur.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor' ),
		MTB_CORE_VERSION,
		true
	);

	register_block_type( __DIR__ );
}

/*
 * Le constat « ce bloc n'a pas de conteneur de liste pour parent » ne peut se faire qu'ici : le cœur
 * ne transmet le bloc parent qu'aux filtres de rendu, jamais au fichier de rendu lui-même. Le
 * troisième argument est indispensable — « add_filter() » n'en passe QU'UN par défaut, et c'est
 * précisément le parent, arrivé en troisième, qui est lu. Motif complet en tête de la fonction, dans
 * « rendu.php ».
 */
add_filter( 'render_block_context', __NAMESPACE__ . '\\signaler_l_absence_de_conteneur_de_liste', 10, 3 );
