<?php
/**
 * Conversion du fait hérité en un interrupteur que l'éleveuse pilote.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\IndexationHeritee;

use const MTB\Core\Query\MiseEnSommeil\CLE as CLE_SOMMEIL;
use const MTB\Core\Query\MiseEnSommeil\ENDORMI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/fait.php';
require_once MTB_CORE_DIR . 'includes/query/mise-en-sommeil/etat.php';

/*
 * ─────────────────────────────────────────────────────────────────────────────────────────────
 * DETTE RELEVÉE, DATÉE, ET DÉCLARÉE PAYÉE PAR CE FICHIER
 *
 * Elle a été écrite le 2026-09-05 par l'issue #24, dans « robots.php », sous le titre « LE TROU DE
 * CONTRAINTE 1, ET COMMENT IL SE FERME », et elle disait ceci :
 *
 *     « Un "noindex" posé par filtre est invisible et irréversible depuis "wp-admin". Il ne se
 *       ferme pas par un réglage — hors périmètre — mais PAR LE GUIDE (D3) : "doc-client-mtb"
 *       livre une fiche nommant les cinq contenus. Un état invisible et documenté vaut infiniment
 *       mieux qu'un état invisible et tu. »
 *
 * « robots.php » a été SUPPRIMÉ le 2026-09-07 par l'issue #52, et son commentaire est relevé ici
 * plutôt que perdu, parce que ce fichier-ci est exactement ce qu'il annonçait comme hors de portée :
 * la transformation de cet état invisible en INTERRUPTEUR, dans un écran, réversible d'un clic. Le
 * palliatif — un état invisible mais documenté — n'a plus lieu d'être : l'état est visible.
 *
 * LA CONSIGNE QUI RESTE, ET QUI EST OPPOSABLE : toute règle d'indexation future doit se poser sur un
 * état VISIBLE DANS UN ÉCRAN. Jamais sur un fait que seul le code connaît, jamais sur une liste
 * d'identifiants en dur, jamais sur une condition qu'aucune case ne montre. Une règle qu'on ne peut
 * pas voir est une règle qu'on ne peut pas défaire, et « rien ne se recopie à la main » vaut aussi
 * pour ce qui retire une page des moteurs.
 * ─────────────────────────────────────────────────────────────────────────────────────────────
 */

/*
 * POURQUOI LA CONVERSION, ET JAMAIS LA COHABITATION DES DEUX MÉCANISMES.
 *
 * Les faire vivre côte à côte produit le pire mode de panne possible sur cette issue, et la
 * démonstration ne demande aucune hypothèse : l'éleveuse réveille Halan, « _mtb_en_sommeil » passe à
 * « 0 », le module de mise en sommeil se tait — MAIS l'ancien « marquer_noindex() » lirait toujours
 * « _mtb_robots_source » et rendrait « noindex », et l'ancien « ecarter_les_noindex() » retirerait
 * toujours Halan du plan du site. LE RÉVEIL NE RÉVEILLERAIT RIEN, sur un écran qui affirme le
 * contraire.
 *
 * LE RATTRAPAGE PARTIEL EST REFUSÉ, ET C'EST ÉCRIT ICI POUR QUE PERSONNE NE L'ESSAIE. On PEUT poser
 * un « wp_robots » en priorité 30 qui retire le « noindex » hérité ; on ne PEUT PAS retirer la clause
 * qu'un rappel voisin a posée dans les arguments du plan du site, la convention de cohabitation
 * interdisant de reconstruire « $args » et défaire la clause d'un voisin étant précisément le geste
 * que les contrats #23 et #24 interdisent. On obtiendrait un demi-réveil : balise rétablie, plan du
 * site toujours amputé.
 *
 * LA VALEUR ÉCRITE DÉRIVE DE LA MÉTA DÉJÀ EN BASE, JAMAIS D'UNE LISTE D'IDENTIFIANTS EN DUR. On
 * recopie l'état, on ne l'invente pas — et « _mtb_robots_source » n'est ni réécrite ni effacée
 * (décision 55) : le fait relevé sur l'ancien site reste en base, avec sa provenance. Seule la
 * DIRECTIVE SERVIE change de mécanisme.
 *
 * POURQUOI TROIS ACCROCHES SUR UNE MÊME FONCTION IDEMPOTENTE — et pourquoi deux n'auraient pas suffi.
 *
 *   1. « mtb_core_mise_a_jour » ne se déclenche que si l'empreinte d'IDENTITÉ change, c'est-à-dire
 *      la version de l'extension, les types ou les taxonomies « mtb_ ». #52 n'ajoute ni type ni
 *      taxonomie et ne touche pas « mtb-core.php » : CE CROCHET NE SE DÉCLENCHERA DONC PAS UNE SEULE
 *      FOIS DU FAIT DE #52 sur une base déjà pourvue de « mtb_core_empreinte ». Et là où il se
 *      déclenche — une base neuve —, il court AVANT que les commandes de reprise n'aient écrit
 *      « _mtb_robots_source ». Une conversion accrochée à lui SEUL ne convertirait rien et laisserait
 *      les contenus repris indexables, en silence. Il est gardé parce qu'il est le point d'accroche
 *      contractuel des migrations et qu'il coûte une ligne — jamais parce qu'il serait le
 *      déclencheur porteur.
 *   2. « added_post_meta » et « updated_post_meta », filtrées sur la seule clé du fait hérité,
 *      couvrent l'ordre « code déployé, PUIS import » : la conversion suit le fait à la milliseconde,
 *      sous WP-CLI comme ailleurs.
 *   3. « admin_init » EST LE DÉCLENCHEUR PORTEUR : il couvre l'ordre « import déjà joué, PUIS code
 *      déployé », donc la base d'aujourd'hui et toute base restaurée par copie. Il court AVANT que
 *      « post.php » ne rende un écran d'édition, ce qui garantit que l'éleveuse ne verra jamais une
 *      case décochée sur un contenu qui devait être endormi — sans quoi un simple « Mettre à jour »
 *      écrirait « 0 », et LE RÉVEIL SILENCIEUX AURAIT ÉTÉ FAIT PAR ELLE, sans qu'elle l'ait voulu.
 *
 * AUCUNE OPTION-DRAPEAU, AUCUNE COMMANDE, AUCUN ÉTAT DE MIGRATION À MAINTENIR. La borne 2 de
 * l'amendement au §2 du contrat #1 interdit à un module de « migration/ » de dépendre d'un état en
 * base pour se déclencher. Le garde-fou est LA REQUÊTE ELLE-MÊME : « fields => ids », une
 * « meta_query » sans aucun « OR », qui rend zéro ligne dès le lendemain de la conversion, et qui ne
 * court jamais sur une requête publique.
 *
 * AUCUNE RÉCURSION : « sur_arrivee_du_fait() » sort sur toute clé autre que celle du fait hérité,
 * donc l'écriture de « _mtb_en_sommeil » — qui déclenche « added_post_meta » à son tour — ne se
 * rappelle pas elle-même.
 *
 * AUCUN NONCE, AUCUNE CAPACITÉ, ET C'EST MOTIVÉ, PAS OUBLIÉ — même raisonnement qu'à
 * « Loader::synchroniser_version() » de « includes/class-loader.php », dont le docbloc écrit le même
 * raisonnement : cette écriture n'est PAS D'ORIGINE UTILISATEUR, sa valeur dérive
 * d'une méta déjà en base, elle doit tourner sur un import WP-CLI comme sur la première visite
 * d'administration venue, et une capacité la rendrait inopérante là où elle sert. La règle « nonce
 * sur toute écriture » de CLAUDE.md vise les écritures issues d'une requête utilisateur ; ce n'en est
 * pas une.
 */

/**
 * Convertit en état « en sommeil » tout contenu portant le fait hérité et jamais réglé.
 *
 * Idempotente, et c'est toute sa sûreté : rejouée mille fois, elle ne touche jamais un contenu qui
 * porte DÉJÀ une valeur — « 1 » comme « 0 ». Elle peut donc s'accrocher trois fois sans précaution.
 *
 * @return int Nombre de contenus effectivement convertis lors de cet appel.
 */
function convertir(): int {
	$convertis = 0;

	foreach ( a_convertir() as $identifiant ) {
		if ( convertir_un( (int) $identifiant ) ) {
			++$convertis;
		}
	}

	return $convertis;
}

/**
 * Convertit un seul contenu, si et seulement si les deux conditions sont réunies.
 *
 * LE TEST D'ABSENCE EST « metadata_exists() », JAMAIS « get_post_meta() », ET C'EST LE CŒUR DE
 * L'AFFAIRE. « get_post_meta() » rend la chaîne vide aussi bien pour une clé absente que pour une
 * valeur vide, et ne sait donc pas distinguer « jamais réglé » de « réveillé explicitement ». Or
 * « 0 » EST UNE DÉCISION DE L'ÉLEVEUSE, et elle n'est JAMAIS DÉFAITE : un contenu qu'elle a réveillé
 * ne se rendort pas au prochain passage de cette fonction. C'est exactement ce que le troisième état
 * achète, et c'est ce qui permet de se passer de toute option-drapeau.
 *
 * @param int $identifiant Identifiant du contenu.
 *
 * @return bool Vrai si la conversion a eu lieu pour ce contenu.
 */
function convertir_un( int $identifiant ): bool {
	if ( $identifiant <= 0 ) {
		return false;
	}

	if ( ! demande_noindex( $identifiant ) ) {
		return false;
	}

	if ( metadata_exists( 'post', $identifiant, CLE_SOMMEIL ) ) {
		return false;
	}

	update_post_meta( $identifiant, CLE_SOMMEIL, ENDORMI );

	return true;
}

/**
 * Liste les contenus qui portent le fait hérité et ne portent AUCUNE valeur d'état « en sommeil ».
 *
 * « meta_query » en « AND » STRICT, SANS AUCUN « OR ». Les deux clauses sont d'un seul tenant :
 * « _mtb_robots_source » EXISTS ET « _mtb_en_sommeil » NOT EXISTS. Un « OR » ici transformerait cette
 * requête bornée en un balayage de toute la base, et le « NOT EXISTS » d'un frère de « OR » rouvrirait
 * le piège d'alias décrit en tête de « query/mise-en-sommeil/bootstrap.php ». Ici, les deux clauses
 * étant conjointes, chacune reçoit son propre alias correctement qualifié.
 *
 * C'est cette requête, et rien d'autre, qui borne le module : elle rend ZÉRO LIGNE dès le lendemain
 * de la conversion. Aucun état en base n'a besoin d'être maintenu pour l'arrêter.
 *
 * « post_type => any » laisse dehors les types déclarés « exclude_from_search », dont
 * « mtb_resultat » : il n'a ni adresse, ni plan du site, ni recherche, et n'a jamais porté le fait
 * hérité. « post_status => any » couvre le brouillon comme le publié : un contenu repris qui serait
 * repassé en brouillon doit être converti lui aussi, sans quoi sa publication future le rendrait
 * indexable.
 *
 * @return array<int, int> Identifiants à convertir, éventuellement vide.
 */
function a_convertir(): array {
	$requete = new \WP_Query(
		array(
			'post_type'              => 'any',
			'post_status'            => 'any',
			'posts_per_page'         => -1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'meta_query'             => array(
				'relation' => 'AND',
				array(
					'key'     => CLE,
					'compare' => 'EXISTS',
				),
				array(
					'key'     => CLE_SOMMEIL,
					'compare' => 'NOT EXISTS',
				),
			),
		)
	);

	$identifiants = array();

	foreach ( $requete->posts as $identifiant ) {
		$identifiants[] = (int) $identifiant;
	}

	return $identifiants;
}

/**
 * Convertit un contenu à l'instant même où l'import lui pose le fait hérité.
 *
 * Couvre l'ordre « code déployé, puis import ». La première garde est ce qui empêche toute récursion :
 * l'écriture de l'état « en sommeil » déclenche à son tour « added_post_meta », avec une autre clé,
 * et sort immédiatement.
 *
 * @param mixed $id_meta  Identifiant de la ligne de métadonnée, non utilisé.
 * @param mixed $id_objet Identifiant du contenu concerné.
 * @param mixed $cle      Clé de la métadonnée qui vient d'être écrite.
 * @param mixed $valeur   Valeur écrite, non utilisée : la lecture passe par « demande_noindex() ».
 *
 * @return void
 */
function sur_arrivee_du_fait( $id_meta, $id_objet, $cle, $valeur ): void {
	unset( $id_meta, $valeur );

	if ( CLE !== (string) $cle ) {
		return;
	}

	convertir_un( (int) $id_objet );
}

/**
 * Rejoue la conversion à l'ouverture de l'administration.
 *
 * Le déclencheur porteur : il couvre l'ordre « import déjà joué, puis code déployé », donc la base
 * d'aujourd'hui et toute base restaurée par copie. Il court avant que « post.php » ne rende un écran
 * d'édition.
 *
 * La garde « wp_doing_ajax() » retranche les requêtes d'arrière-plan de l'administration — battement
 * de sauvegarde automatique, complétion, téléversement — qui passent toutes par « admin_init » et
 * rejoueraient la requête pour rien. Dès le lendemain de la conversion, cette requête rend zéro ligne
 * et ne coûte plus qu'un SELECT borné par deux clauses de méta.
 *
 * @return void
 */
function rattraper(): void {
	if ( wp_doing_ajax() ) {
		return;
	}

	convertir();
}
