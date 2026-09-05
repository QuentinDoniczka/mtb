<?php
/**
 * Lecture du fait hérité : la directive « robots » relevée sur le site source.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\IndexationHeritee;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * LE FILTRE LIT LA MÉTA ; IL NE CODE JAMAIS EN DUR LA LISTE DES CINQ IDENTIFIANTS. C'est ce qui le
 * rend juste si un sixième contenu portait la méta un jour, et indépendant des identifiants de
 * contenu, qui diffèrent d'une base à l'autre.
 *
 * ASYMÉTRIE DÉLIBÉRÉE ENTRE LES DEUX LECTEURS DE CETTE CLÉ :
 *   - le filtre « wp_robots » lit la VALEUR — il faut y trouver « noindex » ;
 *   - le retrait du plan du site teste l'EXISTENCE de la clé, jamais sa valeur, parce que
 *     « meta_query » ne sait pas fouiller une valeur sérialisée sans devenir fausse.
 * Le contrôle qui les réconcilie est écrit au contrat #24 §6.2 : le nombre de contenus portant la
 * clé, le nombre rendus « noindex » et le nombre retirés du plan du site doivent être ÉGAUX.
 */

/**
 * Clé de la métadonnée posée par l'import du site source.
 *
 * Elle n'est pas déclarée ici : c'est l'import de « migration/resultats-pages » et
 * « migration/portees-chiens » qui l'écrit, à partir du « <head> » archivé. Ce module ne fait que
 * la lire.
 */
const CLE = '_mtb_robots_source';

/**
 * Dit si un contenu portait une directive « noindex » sur le site source.
 *
 * @param int $identifiant Identifiant du contenu.
 *
 * @return bool Vrai seulement si la métadonnée existe, est un tableau, et que sa clé « valeur » est
 *              une chaîne contenant « noindex ». Faux dans tous les autres cas, sans avertissement.
 */
function demande_noindex( int $identifiant ): bool {
	if ( $identifiant <= 0 ) {
		return false;
	}

	$fait = get_post_meta( $identifiant, CLE, true );

	if ( ! is_array( $fait ) || ! isset( $fait['valeur'] ) || ! is_string( $fait['valeur'] ) ) {
		return false;
	}

	return false !== stripos( $fait['valeur'], 'noindex' );
}
