<?php
/**
 * Retrait du fournisseur « users » du plan du site, et le 404 franc qui doit l'accompagner.
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
 * « wp_sitemaps_add_provider », lui — le seul crochet du plan du site que ce fichier serve encore —,
 * est à la maille du fournisseur entier.
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
 *
 * COMPLÉMENT DATÉ DU 2026-09-08 (#50) — RETIRER NE SUFFIT PAS, IL FAUT RÉPONDRE.
 *
 * Le retrait ci-dessus a laissé une adresse à moitié morte. Mesuré au lot 18 :
 * « /wp-sitemap-users-1.xml » rend 200 sur du HTML — un FAUX 404, que les moteurs classent moins
 * bien qu'un 404 franc, parce qu'ils le tiennent pour une page réelle et vide (dette T106, résidu
 * déjà nommé au point E de l'amendement du contrat #24). La cause est dans le cœur : quand un
 * fournisseur n'est PAS au registre, « render_sitemaps() » sort par un « return » NU, sans poser
 * aucun code de statut, et la requête poursuit son cours en page d'accueil. Le sous-plan d'un
 * fournisseur PRÉSENT mais à liste vide, lui, reçoit « set_404() » + « status_header( 404 ) » —
 * c'est le chemin de « mtb_resultat », et c'est pourquoi lui rend un 404 franc.
 *
 * NOUS AVONS CASSÉ CETTE RESSOURCE, NOUS LA RÉPARONS. Et rien de plus : pas de règle générale
 * « tout sous-plan sans fournisseur répond 404 », qui ferait plier la BORNE 3 une seconde fois,
 * pour du confort cette fois. Le 404 ne vaut QUE pour les noms de « FOURNISSEURS_RETIRES ».
 *
 * SI « users » EST RÉTABLI DEMAIN, LE 404 CESSE DE LUI-MÊME : la garde de désarmement sort dès que
 * le nom est au registre. Aucun état mort à nettoyer, aucune ligne à défaire.
 * ─────────────────────────────────────────────────────────────────────────────────────────────
 */

/**
 * Les fournisseurs de plan du site que ce module retire, et pour lesquels il répond donc 404.
 *
 * UNIQUE ÉCRITURE DU NOM D'UN FOURNISSEUR RETIRÉ DANS TOUT LE DÉPÔT. Les DEUX rappels ci-dessous la
 * lisent, et eux seuls : « ecarter_le_fournisseur_utilisateurs() » pour retirer, et
 * « repondre_404_au_sous_plan_retire() » pour répondre. C'est ce qui rend impossible un retrait sans
 * 404, ou un 404 sans retrait — deux mécanismes décrivant le même fait et pouvant diverger en
 * silence, c'est le mode de panne, pas la fonctionnalité (leçon de #52).
 *
 * AJOUTER UN NOM ICI SUFFIT : le retrait du registre et le 404 franc suivent tous les deux, sans une
 * ligne de plus ailleurs.
 *
 * CONDITION DE RENOMMAGE. Le jour où cette liste porte PLUS D'UN NOM,
 * « ecarter_le_fournisseur_utilisateurs() » DOIT être renommée : son nom est aujourd'hui
 * littéralement vrai, et il est cité par nom dans « docs/contracts/issue-52.md » §4 et §10 comme
 * témoin que ce crochet est intact, « pas d'un caractère ». Le renommer avant ce jour-là périmerait
 * deux citations d'un contrat gelé pour zéro gain.
 */
const FOURNISSEURS_RETIRES = array( 'users' );

/**
 * Retire du plan du site les fournisseurs de « FOURNISSEURS_RETIRES », seuls, les autres inchangés.
 *
 * @param mixed  $provider Fournisseur en cours d'enregistrement, ou déjà écarté par un filtre précédent.
 * @param string $name     Nom du fournisseur.
 *
 * @return mixed Le fournisseur reçu, inchangé, sauf pour un nom de « FOURNISSEURS_RETIRES » où
 *               « false » est renvoyé.
 */
function ecarter_le_fournisseur_utilisateurs( $provider, string $name ) {
	// Comparaison stricte : sans elle, « 0 » et consorts entreraient par une comparaison lâche.
	if ( ! in_array( $name, FOURNISSEURS_RETIRES, true ) ) {
		return $provider;
	}

	return false;
}

/*
 * QUATRE FAITS DU CŒUR, RELEVÉS DANS LE CONTENEUR LE 2026-09-08, WordPress 6.9. AUCUN N'EST DÉDUIT.
 *
 *   1. FOURNISSEUR ABSENT DU REGISTRE : « wp-includes/sitemaps/class-wp-sitemaps.php:200-202 » —
 *      « if ( ! $provider ) { return; } ». UN « return » NU : aucun code de statut n'est posé, la
 *      requête poursuit son cours en « index.php?sitemap=users&paged=1 », donc en page d'accueil.
 *      C'est le trou, et c'est celui que ce rappel bouche.
 *   2. FOURNISSEUR PRÉSENT MAIS LISTE VIDE : même fichier, l. 211-215 — « $wp_query->set_404();
 *      status_header( 404 ); return; ». C'est le chemin de « mtb_resultat », et c'est EXACTEMENT ce
 *      que la garde 7 reproduit : le même geste, dans le même ordre, sans un appel de plus.
 *   3. ACCROCHE DU CŒUR : même fichier, l. 69 — « add_action( 'template_redirect', array( $this,
 *      'render_sitemaps' ) ) », SANS argument de priorité, donc priorité 10.
 *   4. LE BAIL COMBINÉ, l. 172-174 : « if ( ! ( $sitemap || $stylesheet_type ) ) { return; } », posé
 *      AVANT la branche de feuille de style (l. 183-188). Les deux règles « .xsl » (l. 140-141)
 *      n'écrivent que « sitemap-stylesheet » et laissent « sitemap » VIDE : la garde 1 ci-dessous
 *      les protège donc bien, et la garde 2 les protège une seconde fois, à dessein.
 *
 * CINQUIÈME FAIT, QUE LA LECTURE A AJOUTÉ ET QU'IL FAUT ÉCRIRE. Le cœur porte une branche de plus,
 * l. 176-180 : quand « sitemaps_enabled() » est faux — site en privé, « blog_public » à 0 — il pose
 * lui-même « set_404() » + « status_header( 404 ) ». Les règles de réécriture, elles, sont posées
 * INCONDITIONNELLEMENT (l. 65-73 : « register_rewrites() » et l'accroche courent avant le test).
 * Sur un site en privé ce rappel n'est donc pas désarmé : il repose un 404 que le cœur vient de
 * poser. Même réponse, aucun effet supplémentaire, aucune divergence.
 */

/**
 * Répond le 404 franc que le cœur ne pose pas pour un fournisseur de plan du site que nous retirons.
 *
 * Les sept gardes ci-dessous sont dans l'ordre imposé par le contrat #50 §6. Aucune ne se réordonne.
 * Deux ne se négocient pas : le désarmement au registre (garde 5), sans lequel le 404 survivrait au
 * rétablissement du fournisseur, et la borne de périmètre (garde 4), sans laquelle ce module
 * deviendrait un correcteur de référencement à vocation ouverte.
 *
 * @global \WP_Query $wp_query Requête principale de WordPress.
 */
function repondre_404_au_sous_plan_retire(): void {
	/*
	 * 1. Le nom du sous-plan, dérivé AVEC LA FONCTION MÊME QUE LE CŒUR APPLIQUE À LA MÊME VALEUR
	 * (« class-wp-sitemaps.php:166 »), pour ne jamais répondre 404 sur un nom que le cœur aurait
	 * servi. Sortir sur un nom vide écarte la quasi-totalité du trafic en un test, protège les deux
	 * feuilles « .xsl », et neutralise « ?sitemap[]=users » SANS NOTICE PHP : « sanitize_text_field »
	 * ne déclare aucun type (« formatting.php:5590 ») — notre « strict_types » ne provoque donc
	 * aucune « TypeError » — et « _sanitize_text_fields » rend « '' » sur un tableau (l. 5643-5645).
	 */
	$sous_plan = sanitize_text_field( get_query_var( 'sitemap' ) );

	if ( '' === $sous_plan ) {
		return;
	}

	/*
	 * 2. Les deux feuilles « .xsl », une seconde fois et localement. REDONDANTE À DESSEIN avec la
	 * garde 1 tant que le bail combiné du cœur tient. Le mode de panne qu'elle ferme est totalement
	 * muet : les cinq sous-plans deviendraient illisibles pour un humain tout en restant
	 * parfaitement valides pour un moteur, et aucun autre contrôle ne le dirait.
	 */
	if ( '' !== sanitize_text_field( get_query_var( 'sitemap-stylesheet' ) ) ) {
		return;
	}

	/*
	 * 3. Contextes où poser un 404 de front n'a pas de sens. CEINTURE : « template_redirect » n'y
	 * court pas. Alignée mot pour mot sur « redirections-301/service.php », parce que deux services
	 * de front du même groupe « migration/ » ne divergent pas sur leur garde de contexte. Placée
	 * APRÈS la garde 1, contrairement au module voisin : « wp_doing_ajax() » et « wp_doing_cron() »
	 * appliquent chacun un filtre, la garde 1 est plus discriminante et moins chère.
	 */
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}

	/*
	 * 4. LA BORNE 3. On ne répond 404 que pour ce que CE MODULE retire lui-même. Un sous-plan
	 * inconnu — « /wp-sitemap-usersx-1.xml » — garde son 200 d'aujourd'hui : résidu nommé au §8 du
	 * contrat #50, jamais masqué. Comparaison stricte, et sensible à la casse comme le registre du
	 * cœur : normaliser la casse nous ferait répondre 404 pour un nom que le cœur n'a jamais routé.
	 */
	if ( ! in_array( $sous_plan, FOURNISSEURS_RETIRES, true ) ) {
		return;
	}

	/*
	 * 5. LA GARDE DE DÉSARMEMENT. Le 404 est conditionné au retrait EFFECTIF, constaté au registre
	 * (« sitemaps.php:52-56 », qui rend le tableau des fournisseurs indexé par nom) et non déduit de
	 * notre propre filtre. Si le fournisseur est rétabli — notre « add_filter » retiré, un
	 * « wp_register_sitemap_provider() » tiers, le dossier renommé « _indexation-heritee » — LE 404
	 * CESSE DE LUI-MÊME : aucun état mort à nettoyer. Placée après la garde 4, elle ne court que sur
	 * un nom déjà de la liste, donc jamais en pratique.
	 */
	$fournisseurs = wp_get_sitemap_providers();

	if ( isset( $fournisseurs[ $sous_plan ] ) ) {
		return;
	}

	/*
	 * 6. ASSURANCE CONTRE L'ERREUR FATALE. Ce rappel court sur chaque requête de front du site : un
	 * « Call to a member function set_404() on null » y serait le site entier par terre — le rayon
	 * d'explosion pour lequel le fournisseur vide hérité du cœur a été écarté. Le cœur ne fait pas ce
	 * test ; nous le faisons, parce que nous ne sommes pas le cœur et qu'un tiers peut avoir écrasé
	 * cette globale avant nous.
	 */
	global $wp_query;

	if ( ! $wp_query instanceof \WP_Query ) {
		return;
	}

	/*
	 * 7. Le même geste que le cœur, dans le même ordre, sans un appel de plus : ni
	 * « nocache_headers() », ni en-tête de type, ni corps. AUCUN « exit » : la fonction rend la main,
	 * « template-loader.php » évalue « is_404() » après « template_redirect » et charge
	 * « themes/mtb/templates/404.html ». C'est le chemin exact de « mtb_resultat », et c'est ce qui
	 * rend les deux réponses identiques à l'octet — deux comportements différents pour un même fait
	 * est précisément ce qu'on répare.
	 */
	$wp_query->set_404();
	status_header( 404 );
}
