<?php
/**
 * Retrait du contenu protégé par mot de passe du plan du site, de la recherche et des flux.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Query\PageProtegee;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * POURQUOI CE MODULE VIT DANS « query », ALORS QU'IL NE LIT RIEN ET N'EXPOSE AUCUNE FONCTION.
 *
 * L'issue annonçait « includes/privacy/ ». Ce dossier n'existe pas pour le chargeur :
 * « includes/class-loader.php » ne balaie que six groupes écrits en dur — content, fields, query,
 * blocks, admin, migration —, liste close par contrat. Un bootstrap.php posé sous « privacy »
 * n'aurait jamais été inclus : aucune erreur, aucune ligne au journal, la page en 200, et le module
 * MORT. Le groupe « admin » a été écarté pour la raison inverse, plus dangereuse encore : la garde
 * d'ouverture y est la norme du groupe, et « admin/listes/bootstrap.php » la pose PRÉCISÉMENT à
 * propos d'un pre_get_posts, en écrivant qu'un crochet courant sur chaque requête est « une raison de
 * plus pour ne pas l'y attacher ». Recopier ce réflexe ici éteindrait les deux filtres sur la seule
 * façade où ils servent.
 *
 * « query » est donc retenu, à son prix, écrit ici pour que la revue n'ait pas à le découvrir : ce
 * module n'expose aucune fonction « mtb_ » et il pose trois crochets, là où deux autres modules du
 * groupe écrivent dans leur en-tête qu'« aucun hook n'est posé ». L'objection est légitime et
 * s'assume telle quelle : une impureté de forme s'objecte à voix haute en revue, une mort silencieuse
 * ne se dit nulle part. L'arbitrage complet est au §2 de « docs/contracts/issue-23.md ».
 *
 * AUCUNE GARDE DE CONTEXTE À L'INCLUSION, ET SURTOUT PAS « if ( ! is_admin() ) { return; } ».
 *
 * Les trois façades visées — plan du site, recherche du site, flux — sont des requêtes DE FAÇADE
 * PUBLIQUE, hors administration, où is_admin() VAUT FAUX — qu'un compte soit connecté ou non, la
 * recherche du site en est justement l'exemple. La garde habituelle du groupe « admin » ne
 * retrancherait donc pas un cas marginal : elle éteindrait ce module en entier, et sans rien casser
 * d'observable. Le test de contexte vit DANS LE RAPPEL, jamais au chargement — c'est lui, et non une
 * garde de fichier, qui laisse intacte la recherche de l'éleveuse dans sa propre administration. Ne
 * pas « ranger » ce module derrière la garde du groupe voisin.
 *
 * LA PANNE DE CE MODULE EST INVISIBLE.
 *
 * Il ne rend rien, n'imprime rien, ne journalise rien, ne déclare aucune fonction globale, n'écrit
 * rien en base et n'émet aucune chaîne de caractères. S'il cesse d'être chargé, ou si une garde est
 * mal posée, la page répond 200 et debug.log reste vide, pendant qu'un contenu réservé réapparaît
 * en QUATRE endroits que personne ne relit : le plan du site, les flux, la recherche du site pour
 * toute personne connectée, et la page redevient indexable par les moteurs. Le SEUL témoin est le
 * protocole de vérification du §5 de « docs/contracts/issue-23.md », qui se rejoue en entier à chaque
 * livraison touchant ce fichier ; son point Z1 — has_filter() et has_action() non faux — est la seule
 * preuve directe que ce fichier a été inclus.
 *
 * « wp_robots » EST FILTRÉ PAR DEUX MODULES DU LOT, DÉLIBÉRÉMENT. CE N'EST PAS UN DOUBLON.
 *
 * Celui-ci et « migration/indexation-heritee » posent chacun leur rappel sur « wp_robots », en
 * priorité 20 tous les deux. Ils répondent à DEUX QUESTIONS DIFFÉRENTES, sur DEUX ENSEMBLES
 * DISJOINTS, avec DEUX PROVENANCES DE VÉRITÉ : ici, « ce contenu porte-t-il un mot de passe ? » —
 * une RÈGLE, qui se relit dans la colonne « post_password » ; là-bas, « ce contenu était-il déjà
 * noindex sur l'ancien site ? » — un FAIT RECOPIÉ, conservé avec sa provenance dans
 * « _mtb_robots_source » (décision 55), que ce module ne lit ni n'écrit jamais. Verser notre règle
 * dans leur table corromprait ce que la décision 55 protège ; verser leurs faits ici obligerait la
 * reprise à connaître le mot de passe d'un contenu. Les filtres « wp_robots » sont additifs et se
 * combinent dans n'importe quel ordre. QU'AUCUNE CHAÎNE FUTURE N'EN RETIRE UN EN CROYANT
 * DÉDOUBLONNER : elle rendrait indexable soit tout le contenu protégé, soit les cinq contenus repris,
 * et — encore une fois — rien à l'écran ne le dirait.
 */

add_filter( 'wp_sitemaps_posts_query_args', __NAMESPACE__ . '\\exclure_du_plan_du_site', 10, 1 );

/**
 * Retire le contenu protégé par mot de passe de chaque sous-plan du plan du site.
 *
 * Le cœur bâtit chaque sous-plan avec « post_status => publish » pour seule restriction : une page ou
 * une portée protégée y entre comme les autres, et son adresse part au moteur de recherche. La clause
 * ajoutée ici vaut pour TOUS les types de contenu du plan — page, article, portée, fiche de chien —,
 * sans distinction et sans exception.
 *
 * LE SECOND ARGUMENT DU CŒUR EST DÉLIBÉRÉMENT REFUSÉ. Le crochet passe le type de contenu en
 * deuxième position ; ne pas le demander rend STRUCTURELLEMENT IMPOSSIBLE d'écrire un jour une
 * branche par type — c'est-à-dire la porte par laquelle un type de contenu futur serait oublié dans
 * le plan du site, silencieusement. La règle est la même pour tous ; elle s'écrit donc une fois.
 *
 * Aucune autre clé n'est touchée — ni post_status, ni post_type, ni orderby, ni posts_per_page, ni
 * no_found_rows : la pagination et l'ordre du plan restent entièrement au cœur. Les deux autres
 * crochets du plan du site sont écartés à dessein : « wp_sitemaps_post_types » retirerait un TYPE
 * entier au lieu d'une entrée, et « wp_sitemaps_posts_pre_url_list » court-circuiterait la requête du
 * cœur, donc aussi son comptage.
 *
 * CONVENTION DE COHABITATION, GELÉE À L'IDENTIQUE DANS LES CONTRATS #23 ET #24 — NE PAS « SIMPLIFIER »
 * CE CORPS EN RECONSTRUISANT LE TABLEAU. Ce hook porte DEUX rappels : celui-ci et
 * « migration/indexation-heritee/plan-du-site.php », qui écarte du plan les cinq contenus repris que
 * l'ancien site tenait en noindex. Chaque rappel MUTE des clés de $args ; AUCUN ne remplace $args.
 * Sont donc interdits ici : « return array( … ); », « $arguments = array( … ); » et
 * « unset( $arguments['has_password'] ); ». Sans cette convention, les deux rappels se détruiraient
 * l'un l'autre EN SILENCE — pas d'erreur, pas de journal, un plan du site en 200 annonçant une page
 * qui devait en sortir. Et le jour où l'un des deux emploiera une meta_query, elle s'ENVELOPPERA sous
 * « 'relation' => 'AND' » plutôt que de se compléter par ajout : un ajout naïf dans un « OR »
 * préexistant rendrait l'exclusion sans effet, silencieusement elle aussi.
 *
 * Le paramètre et le retour ne sont pas typés, et c'est le précédent d'« admin/corbeille » : un
 * filtre tiers peut avoir rendu autre chose qu'un tableau, et strict_types en ferait une erreur
 * fatale — ici, un XML tronqué servi à un moteur de recherche.
 *
 * @param mixed $arguments Arguments de requête du sous-plan en cours de construction.
 *
 * @return mixed Arguments complétés de la clause d'exclusion, ou la valeur reçue si elle n'est pas
 *               un tableau.
 */
function exclure_du_plan_du_site( $arguments ) {
	if ( ! is_array( $arguments ) ) {
		return $arguments;
	}

	$arguments['has_password'] = false;

	return $arguments;
}

add_action( 'pre_get_posts', __NAMESPACE__ . '\\exclure_de_la_recherche', 10, 1 );

/**
 * Retire le contenu protégé par mot de passe de la recherche du site et des flux.
 *
 * Les deux façades sont traitées ensemble parce qu'elles sont la même requête : celle que WordPress
 * bâtit à partir de l'adresse demandée. Un flux est un index public au sens littéral du BRIEF §8, et
 * « /portees/feed/ » existe du seul fait que le type de contenu déclare une archive.
 *
 * CE QUE LE CŒUR FAISAIT DÉJÀ, ET QUE CE RAPPEL COMPLÈTE — relevé dans le code du cœur plutôt que
 * supposé. WP_Query::parse_search() ajoute de lui-même « AND post_password = '' » à toute recherche,
 * MAIS SEULEMENT quand personne n'est connecté (wp-includes/class-wp-query.php, dans parse_search()).
 * La recherche anonyme était donc déjà tenue ; ce qui ne l'était pas, et que ce rappel ferme, c'est
 * la recherche vue par une personne CONNECTÉE — l'éleveuse elle-même — et les FLUX, que le cœur ne
 * filtre à aucun moment. La condition du cœur portant sur la session, elle ne pouvait pas tenir la
 * promesse inconditionnelle du BRIEF §8 ; celle-ci ne dépend de rien.
 *
 * CE CROCHET COURT SUR CHAQUE REQUÊTE DU SITE : les trois gardes ci-dessous ne sont pas des
 * précautions de style, elles sont le module. Leur ordre est porteur et chacune a son motif propre.
 *
 * 1. HORS ADMINISTRATION, ET C'EST LE CŒUR DE L'ISSUE. En administration, le cœur peuple le
 *    WP_Query global : sur « edit.php?post_type=page&s=espace », is_main_query() ET is_search()
 *    valent vrai tous les deux. Sans cette première garde, la page protégée disparaîtrait de la
 *    recherche de l'éleveuse DANS SON PROPRE ÉCRAN « Pages » — le mode de panne n° 1 de ce module,
 *    et le seul endroit où elle doit toujours retrouver ce qu'elle a protégé.
 * 2. REQUÊTE PRINCIPALE SEULE, ET C'EST LA SEULE GARDE QUI PROTÈGE LA REST. is_admin() vaut FAUX
 *    sur « /wp-json/ » ; is_main_query() compare la requête au WP_Query global, qu'un contrôleur
 *    REST n'emploie jamais. C'est elle qui laisse l'éditeur de blocs proposer la page protégée dans
 *    son sélecteur de lien — le lien étant précisément ce que l'éleveuse doit transmettre de la main
 *    à la main. Elle écarte aussi le plan du site, d'où la nécessité d'un filtre séparé pour lui :
 *    pre_get_posts ne peut PAS filtrer le plan du site, ce n'est pas une ceinture de plus.
 * 3. RECHERCHE OU FLUX SEULEMENT. is_search() seul serait trop étroit — il laisserait les flux
 *    ouverts ; sans la garde 1, il serait au contraire trop large côté administration.
 *
 * Aucun test de session, et c'est une décision, pas un oubli : le BRIEF §8 est inconditionnel, un
 * index rendu conditionnel serait empoisonné en cache dès la première mise en cache, et
 * is_user_logged_in() est une fonction remplaçable, interdite à l'extension. Conséquence assumée,
 * écrite dans la fiche d'aide : la recherche DU SITE ne remonte pas une page protégée, même pour
 * l'éleveuse connectée, même après saisie du mot de passe. Elle la retrouve dans « Pages » et
 * « Portées », où rien n'est filtré.
 *
 * Aucune autre variable de requête n'est touchée, aucun type de contenu n'est exclu de la recherche,
 * et ni posts_search, ni posts_where, ni the_posts ne sont approchés : la clause est posée par le
 * cœur lui-même, à partir de la seule variable renseignée ici.
 *
 * @param \WP_Query $requete Requête en cours de préparation.
 *
 * @return void
 */
function exclure_de_la_recherche( \WP_Query $requete ): void {
	if ( is_admin() ) {
		return;
	}

	if ( ! $requete->is_main_query() ) {
		return;
	}

	if ( ! $requete->is_search() && ! $requete->is_feed() ) {
		return;
	}

	$requete->set( 'has_password', false );
}

add_filter( 'wp_robots', __NAMESPACE__ . '\\interdire_indexation_du_contenu_protege', 20 );

/**
 * Interdit aux moteurs d'indexer la page ou la fiche dont l'objet porte un mot de passe.
 *
 * Les deux premières accroches retirent le contenu protégé des index DU SITE ; elles n'empêchent pas
 * un moteur d'atteindre une adresse transmise de la main à la main, puis publiée ailleurs. Il n'y
 * verrait que le mur de mot de passe, mais le titre — que le cœur préfixe de « Protégé : » — et
 * l'adresse entreraient dans un index tiers. C'est la complétion minimale d'une promesse déjà écrite
 * au BRIEF §8, pas un périmètre élargi.
 *
 * PRIORITÉ 20, ET ELLE EST ALIGNÉE, PAS ARBITRAIRE : c'est celle de
 * « migration/indexation-heritee/bootstrap.php:47 », et pour le motif qui y est écrit — passer après
 * « wp_robots_noindex_embeds », « wp_robots_max_image_preview » et les autres rappels du cœur, pour
 * ne pas travailler sur un tableau incomplet.
 *
 * LA CONDITION PORTE SUR LE CONTENU, JAMAIS SUR LE VISITEUR, ET C'EST LE POINT QUI DÉCIDE DE LA
 * FORME. post_password_required() répondrait à une tout autre question — « CE visiteur-ci est-il
 * enfermé dehors ? » —, donc la directive dépendrait du cookie « wp-postpass_ » de celui qui demande
 * la page. Une page mise en cache pour quelqu'un qui vient de saisir le mot de passe serait alors
 * resservie À TOUS SANS noindex : l'empoisonnement de cache que le §3.5 refuse déjà pour les index,
 * et un défaut qu'aucune page à l'écran ne montrerait. On lit donc la colonne, avec la forme du
 * dépôt : « '' !== (string) get_post_field( 'post_password', … ) ».
 *
 * Le retour passe par wp_robots_no_robots(), helper du cœur et NON remplaçable, plutôt que par un
 * « noindex » posé à la main : lui seul accorde aussi « follow » ou « nofollow » selon le réglage
 * « blog_public » du site. Aucune autre clé de $robots n'est touchée, et rien n'est appliqué hors des
 * vues singulières — les archives, la recherche et les flux ne contiennent plus le contenu protégé,
 * les leur marquer serait marquer tout le site.
 *
 * @param array<string, mixed> $robots Directives d'indexation déjà composées.
 *
 * @return array<string, mixed> Directives, complétées du refus d'indexation si le contenu est
 *                              protégé.
 */
function interdire_indexation_du_contenu_protege( array $robots ): array {
	if ( ! is_singular() ) {
		return $robots;
	}

	$identifiant = get_queried_object_id();

	if ( $identifiant <= 0 ) {
		return $robots;
	}

	if ( '' === (string) get_post_field( 'post_password', $identifiant ) ) {
		return $robots;
	}

	return wp_robots_no_robots( $robots );
}
