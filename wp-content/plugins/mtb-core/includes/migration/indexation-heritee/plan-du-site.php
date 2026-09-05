<?php
/**
 * Retrait du plan du site des contenus que l'ancien site tenait hors des moteurs.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\IndexationHeritee;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * « wp_sitemaps_posts_query_args » EST LE SEUL CROCHET DU CŒUR QUI RETIRE RÉELLEMENT UNE ENTRÉE.
 *
 * Vérifié par lecture de « WP_Sitemaps_Posts::get_url_list() » dans le conteneur, WordPress 6.9 :
 * la boucle finale écrit « $url_list[] = $sitemap_entry; » — le retour du filtre
 * « wp_sitemaps_posts_entry » est EMPILÉ SANS ÊTRE TESTÉ. Y renvoyer un tableau vide ne retire donc
 * pas l'entrée : cela produit un « <url/> » vide, c'est-à-dire un plan de site sciemment abîmé.
 * « wp_sitemaps_add_provider », lui, est à la maille du fournisseur entier.
 *
 * CRITÈRE DE RETRAIT : L'EXISTENCE DE LA CLÉ, PAS SA VALEUR. « meta_query » ne sait pas fouiller
 * une valeur sérialisée sans devenir fausse ; le filtre « wp_robots », lui, lit la valeur.
 * L'asymétrie est délibérée, et le contrôle qui la rend sûre est écrit au contrat #24 §6.2 : le
 * nombre de contenus portant la clé, le nombre rendus « noindex » et le nombre retirés du plan du
 * site doivent être ÉGAUX.
 *
 * ─────────────────────────────────────────────────────────────────────────────────────────────
 * CONDITION DE NON-COLLISION AVEC LE FILTRE DE #23, À NE JAMAIS DÉFAIRE
 *
 * « includes/query/page-protegee/bootstrap.php:71 » accroche le MÊME crochet, à la MÊME priorité,
 * pour retirer le contenu protégé par mot de passe. « add_filter() » EMPILE : les deux rappels
 * s'exécutent, la sortie du premier entrant dans le second. L'ordre leur est indifférent — à UNE
 * condition, et à elle seule :
 *
 *     CHAQUE RAPPEL MUTE DES CLÉS DE « $args ». AUCUN NE REMPLACE « $args ».
 *
 * Si l'un écrivait « $args = array( … ); » ou « return array( … ); », il EFFACERAIT l'autre — sans
 * erreur, sans avertissement, sans une ligne de journal. Le plan du site répondrait 200 en
 * annonçant une page qui devait en être retirée.
 *
 * POURQUOI L'ENVELOPPE ET NON L'AJOUT. « $args['meta_query'][] = $clause; » paraît suffire, et ne
 * l'est pas : si un autre filtre avait posé « 'relation' => 'OR' », l'ajout naïf ferait passer nos
 * contenus PAR UN OU, et l'exclusion serait sans effet, SILENCIEUSEMENT. L'enveloppe sous
 * « 'relation' => 'AND' » est immunisée. En l'état, #23 emploie « has_password » et non
 * « meta_query » : le cas est théorique aujourd'hui, et c'est précisément pourquoi il se gèle
 * maintenant.
 * ─────────────────────────────────────────────────────────────────────────────────────────────
 */

/**
 * Retire du plan du site tout contenu portant le fait hérité.
 *
 * ASYMÉTRIE DÉCLARÉE AVEC SON JUMEAU, ET ELLE EST VOULUE. « page-protegee/bootstrap.php:112 »
 * accroche le même crochet avec une signature NON TYPÉE (« exclure_du_plan_du_site( $arguments ) »),
 * par prudence contre un filtre tiers qui rendrait autre chose qu'un tableau. Ce rappel-ci est
 * TYPÉ, et ne s'aligne pas sur lui, pour deux motifs :
 *
 *   1. LA SIGNATURE EST GELÉE AU CONTRAT #24 §6.3, qui l'écrit en toutes lettres sous le titre
 *      « forme conforme, imposée », en regard d'une liste de « formes interdites, à refuser en
 *      revue ». La revue compare le code au texte : casser cette correspondance coûterait plus
 *      qu'elle ne rapporte.
 *   2. LE RISQUE QUE LE JUMEAU PARE N'EXISTE PAS ICI. Le cœur passe toujours un tableau à
 *      « wp_sitemaps_posts_query_args », et la contrainte D10 interdit toute extension tierce sur
 *      ce site : aucun filtre étranger ne peut s'intercaler. Le typage rend donc visible, dès
 *      l'appel, la seule forme que ce rappel accepte.
 *
 * Écrit ici plutôt que tu, pour qu'une revue future ne lise pas cette divergence comme un oubli et
 * n'« aligne » pas les deux signatures en croyant bien faire.
 *
 * @param array  $args            Arguments de requête du sous-plan en cours de construction.
 * @param string $type_de_contenu Type de contenu du sous-plan.
 *
 * @return array Arguments, dont la seule clé « meta_query » est mutée.
 */
function ecarter_les_noindex( array $args, string $type_de_contenu ): array {
	unset( $type_de_contenu );

	$ancienne = isset( $args['meta_query'] ) && is_array( $args['meta_query'] ) ? $args['meta_query'] : array();
	$clause   = array(
		'key'     => CLE,
		'compare' => 'NOT EXISTS',
	);

	$args['meta_query'] = array() === $ancienne
		? array( $clause )
		: array(
			'relation' => 'AND',
			$ancienne,
			$clause,
		);

	return $args;
}

/*
 * ─────────────────────────────────────────────────────────────────────────────────────────────
 * EXCEPTION MOTIVÉE AU CONTRAT #24 §6.4, DATÉE DU 2026-09-05, DÉCIDÉE APRÈS MESURE
 *
 * Le contrat #24 avait écarté ce correctif du périmètre de l'issue, au motif que le remède
 * « n'a rien d'un héritage de l'ancien site » et affaiblirait la BORNE 3 de l'amendement au §2 du
 * contrat #1 (« périmètre clos et daté, jamais un module de référencement à vocation ouverte »).
 * Cette décision a été rouverte et inversée par le lead sur la foi de la mesure suivante.
 *
 * LA MESURE, chiffrée, base de développement, WordPress 6.9, relevée le 2026-09-05 : « /wp-sitemap.xml »
 * liste bien « /wp-sitemap-users-1.xml » ; ce sous-plan contient EXACTEMENT une entrée,
 * « /author/admin/ » ; en base, « user_login » = « admin » ET « user_nicename » = « admin » — les deux
 * sont identiques. Le plan du site publie donc littéralement l'identifiant de connexion de
 * l'administrateur.
 *
 * LE MOTIF : BRIEF §4, « zéro donnée personnelle inutile ». Le plan du site publiait l'identifiant de
 * connexion de l'administrateur, ce qui n'est vrai d'aucune autre archive de ce site.
 *
 * POURQUOI CE CROCHET EST LE BON : « wp_sitemaps_add_provider » est à la maille du FOURNISSEUR ENTIER,
 * ce qui est précisément la maille voulue ici — on ne retire pas une entrée, on retire une archive qui
 * n'aurait jamais dû exister sur ce site : l'ancien site ne publiait aucune archive d'auteur.
 *
 * CE MODULE NE DEVIENT PAS POUR AUTANT UN MODULE DE RÉFÉRENCEMENT À VOCATION OUVERTE. La borne 3
 * tient : ce rappel ne retire que le fournisseur « users », nommément, et rien d'autre. La prochaine
 * demande de ce genre exige son propre amendement écrit et daté.
 * ─────────────────────────────────────────────────────────────────────────────────────────────
 */

/**
 * Retire le fournisseur « users » du plan du site, seul, tous les autres inchangés.
 *
 * @param mixed  $provider Fournisseur en cours d'enregistrement, ou déjà écarté par un filtre précédent.
 * @param string $name     Nom du fournisseur.
 *
 * @return mixed Le fournisseur reçu, inchangé, sauf pour « users » où « false » est renvoyé.
 */
function ecarter_le_fournisseur_utilisateurs( $provider, string $name ) {
	if ( 'users' !== $name ) {
		return $provider;
	}

	return false;
}
