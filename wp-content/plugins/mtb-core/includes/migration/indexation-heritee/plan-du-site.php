<?php
/**
 * Retrait du plan du site de l'archive d'auteur, que l'ancien site ne publiait pas.
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
 * « wp_sitemaps_add_provider », lui — le seul crochet que ce fichier accroche encore —, est à la
 * maille du fournisseur entier.
 *
 * CE FICHIER NE RETIRE PLUS AUCUNE ENTRÉE DU PLAN DU SITE DEPUIS LE 2026-09-07 (#52) :
 * « ecarter_les_noindex() » est supprimée, le fait hérité ayant été converti en l'état « en
 * sommeil » — voir « conversion.php ». La CONDITION DE NON-COLLISION sur
 * « wp_sitemaps_posts_query_args », qui vivait ici, est transplantée en tête de
 * « includes/query/mise-en-sommeil/bootstrap.php », augmentée de la mesure d'alias qui écarte la
 * forme en « OR » ; elle reste opposable à tout rappel futur de ce crochet.
 */

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
