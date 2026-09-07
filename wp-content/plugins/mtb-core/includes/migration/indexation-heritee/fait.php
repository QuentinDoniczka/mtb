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
 * LA LECTURE PASSE PAR LA MÉTA ; ELLE NE CODE JAMAIS EN DUR LA LISTE DES CINQ IDENTIFIANTS. C'est ce
 * qui rend « demande_noindex() » juste si un sixième contenu portait la méta un jour, et indépendante
 * des identifiants de contenu, qui diffèrent d'une base à l'autre.
 *
 * CETTE CLÉ EST UN FAIT RECOPIÉ, CONSERVÉ AVEC SA PROVENANCE (décision 55). Ce qu'elle déclare, c'est
 * CE QUE L'ANCIEN SITE DÉCLARAIT — la directive relevée dans le « <head> » archivé, datée et sourcée.
 * Elle n'est ni réécrite ni effacée, et c'est aujourd'hui sa seule raison d'être.
 *
 * ELLE N'AGIT PLUS. Depuis l'issue #52, le 2026-09-07, ce fait ne produit AUCUNE DIRECTIVE : il a été
 * CONVERTI UNE FOIS, par « conversion.php » de ce même dossier, en un état que l'éleveuse pilote —
 * « _mtb_en_sommeil ». Les deux lecteurs que ce bloc décrivait auparavant ONT ÉTÉ SUPPRIMÉS : le
 * filtre « wp_robots » « marquer_noindex() », avec son fichier « robots.php », et le retrait du plan
 * du site « ecarter_les_noindex() », retirée de « plan-du-site.php ». L'asymétrie entre lecture de la
 * VALEUR et test d'EXISTENCE qu'ils imposaient N'EXISTE PLUS, et la mesure d'égalité du §6.2 du
 * contrat #24 qui les réconciliait est RELEVÉE au §11.3 du contrat #52 : ne plus s'y appuyer.
 *
 * INTERDIT OPPOSABLE — §12 DU CONTRAT #52. Ne JAMAIS réintroduire un filtre « wp_robots » ni un filtre
 * de plan du site qui lise cette clé. Le rétablir rendrait LE RÉVEIL INOPÉRANT EN SILENCE : l'éleveuse
 * décocherait la case, l'écran lui dirait le contenu réveillé, et le contenu resterait hors des
 * moteurs.
 *
 * CE QUI RESTE LISIBLE ICI, ET POURQUOI C'EST UTILE. Cette clé répond encore à « QU'EST-CE QUE
 * L'ANCIEN SITE DÉCLARAIT ? », quand « _mtb_en_sommeil » répond à « QU'EST-CE QUE L'ÉLEVEUSE A DÉCIDÉ
 * DEPUIS ? ». Deux questions distinctes, qui ne doivent JAMAIS FUSIONNER : l'une est un fait de
 * migration, figé ; l'autre est une décision vivante.
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
