<?php
/**
 * Gardes posées avant la première écriture.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\PorteesChiens;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * GARDE DE NON-MÉLANGE — POURQUOI ELLE EST BLOQUANTE ET NON UN AVERTISSEMENT
 *
 * « wp mtb import-fixtures » sème un jeu FICTIF : des chiens à l'affixe « de Démonstration », des
 * numéros « LOF DEMO … ». Ce jeu ne pose aucun marqueur d'origine — un contenu importé y est
 * indiscernable d'un contenu saisi. Une fois le réel versé par-dessus le fictif, plus rien ne les
 * sépare à l'œil nu, et la seule sortie est de repartir d'une base vide, en perdant tout ce que
 * l'éleveuse aurait déjà saisi entre-temps. Ce qu'on ne peut pas défaire, on l'interdit avant.
 */

/**
 * Le mot qui trahit une base semée de contenus de démonstration.
 */
const MARQUEUR_DE_DEMONSTRATION = 'Démonstration';

/**
 * Types de contenu que la reprise vise, et sur lesquels porte la garde.
 *
 * @return string[] Noms de types de contenu.
 */
function types_repris(): array {
	return array( 'mtb_chien', 'mtb_portee', 'mtb_resultat' );
}

/**
 * Interrompt la commande si la base porte déjà du contenu de démonstration.
 *
 * @return bool Vrai si la base est saine ; la commande sort en erreur dans le cas contraire.
 */
function refuser_une_base_de_demonstration(): bool {
	/*
	 * « search_columns » restreint la recherche au titre : sans lui, WordPress fouillerait aussi le
	 * contenu, et un texte de portée qui citerait le mot bloquerait une base parfaitement saine.
	 * « post_status => any » et les trois types explicites, parce que « any » sur le type écarte
	 * mtb_resultat, qui est déclaré hors recherche.
	 */
	$trouves = get_posts(
		array(
			'post_type'              => types_repris(),
			'post_status'            => 'any',
			's'                      => MARQUEUR_DE_DEMONSTRATION,
			'search_columns'         => array( 'post_title' ),
			'posts_per_page'         => 5,
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	if ( array() === $trouves ) {
		return true;
	}

	$titres = array();

	foreach ( $trouves as $trouve ) {
		if ( $trouve instanceof \WP_Post ) {
			$titres[] = $trouve->post_title;
		}
	}

	echec_base_de_demonstration( $titres );

	return false;
}

/**
 * Interrompt la commande si le module d'images n'est pas en place.
 *
 * PRÉREQUIS D'ORDRE, PAS UNE RECOMMANDATION. WordPress ne découpe et ne convertit une image qu'AU
 * TÉLÉVERSEMENT : une photographie versée avant que la sous-taille de galerie ne soit déclarée
 * n'aura jamais ses formats modernes ni ses sous-tailles, et les cent cinquante images seraient à
 * régénérer. La sous-taille est déclarée sur « init », donc présente au moment où une commande
 * WP-CLI s'exécute — son absence signale une extension amputée, pas un ordre d'exécution.
 *
 * @return bool Vrai si le module d'images est en place.
 */
function exiger_le_module_dimages(): bool {
	if ( has_image_size( 'mtb-vignette-galerie' ) ) {
		return true;
	}

	sortir_en_echec(
		'La sous-taille « mtb-vignette-galerie » n\'est pas déclarée : le module d\'images de l\'extension est absent ou désactivé. Les photographies versées maintenant n\'auraient ni sous-tailles ni formats modernes, et tout le stock serait à régénérer. Aucun contenu n\'a été importé.'
	);

	return false;
}

/**
 * Prévient si le compte qui exécute la commande ne peut pas écrire du texte non filtré.
 *
 * Sans « --user », WP-CLI n'ouvre aucune session : les filtres kses sont posés, et « post_content »
 * passe par wp_kses(). Rien n'est perdu, mais « <60% » devient « &lt;60% » et la valeur stockée
 * cesse d'être celle du fichier. Le contrôle aval le nommera de toute façon ; l'annoncer avant
 * évite de découvrir quarante-quatre défauts après coup.
 *
 * Un avertissement et non un refus : sur un texte sans caractère sensible, l'import est correct, et
 * bloquer une reprise valide serait un échec faux.
 */
function avertir_du_compte_dexecution(): void {
	if ( current_user_can( 'unfiltered_html' ) ) {
		return;
	}

	informer(
		'Note : le compte courant n\'a pas « unfiltered_html ». Les textes libres traverseront wp_kses(), qui échappe les chevrons — « <60% » deviendrait « &lt;60% ». Le contrôle aval nommera chaque écart. Relancez avec « --user=<administrateur> » pour écrire les textes verbatim.'
	);
}
