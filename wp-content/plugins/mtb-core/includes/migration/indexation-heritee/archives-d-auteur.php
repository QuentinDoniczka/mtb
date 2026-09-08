<?php
/**
 * Neutralisation des archives d'auteur : la requête est privée de son objet, jamais répondue après coup.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\IndexationHeritee;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * ─────────────────────────────────────────────────────────────────────────────────────────────
 * POURQUOI CE MÉCANISME VIT DANS « indexation-heritee », ET POURQUOI IL A SON PROPRE FICHIER
 * DÉCIDÉ LE 2026-09-08 (#49), APRÈS MESURE — dette T105
 *
 * L'OBJECTION EST RÉELLE, ET ELLE EST ÉCRITE PLUTÔT QUE TUE : « /author/ » n'est PAS un héritage de
 * l'ancien site, c'est une fonctionnalité de WordPress nu. Un module nommé « indexation héritée » qui
 * neutralise une fonctionnalité du cœur que l'ancien site n'a jamais eue frôle le motif 1 gelé en tête
 * de son propre « bootstrap.php ».
 *
 * CE QUI TRANCHE POUR RESTER ICI :
 *   1. Le bloc d'exception motivée du 2026-09-05, en tête de « plan-du-site.php », a déjà pris
 *      « /author/admin/ » EN CHARGE NOMMÉMENT — la même adresse, la même fuite, le même motif
 *      (BRIEF §4, « zéro donnée personnelle inutile »). #49 finit ce que cette exception a commencé ;
 *      il n'ouvre pas un troisième sujet.
 *   2. Séparer les mécanismes recréerait exactement le mode de panne de #52 : si l'un tombait, le plan
 *      du site republierait l'archive d'auteur pendant que l'autre la ferait répondre 404. Sous un
 *      seul « bootstrap.php », LES TROIS EFFETS TOMBENT ET SE RELÈVENT ENSEMBLE.
 *
 * LE FICHIER EST NEUF, ET C'EST LA MÊME RÈGLE À L'ÉCHELLE DU FICHIER. Loger une neutralisation
 * d'archives d'auteur dans « plan-du-site.php » donnerait un fichier qui ment sur son contenu — le
 * motif 1 appliqué au fichier. MODULE N'EST PAS FICHIER : la cohésion du point 2 tient au
 * « bootstrap.php » commun, pas au partage d'un même fichier.
 *
 * BORNE 3 TENUE, PÉRIMÈTRE CLOS ET DATÉ : les archives d'auteur de CE site, fonctionnalité du cœur que
 * ce site n'emploie pas — l'ancien site n'en publiait aucune, le thème n'a aucun gabarit d'auteur, et
 * une archive d'auteur y tombe sur l'index du blog. La borne 3 dit « clos et daté », pas « minuscule ».
 * La prochaine demande de ce genre exige son propre amendement écrit et daté.
 *
 * AMENDEMENT DÉCLARÉ À LA BORNE 1 (contrat #49 §13.1). Elle se lisait « lecture seule », puis « il lit,
 * il RÉPOND » depuis #50. Ce rappel-ci va un cran plus loin, et l'étendre en silence aurait été la
 * faute que la borne existe pour empêcher : IL MODIFIE LA REQUÊTE. Il retire des clés du tableau des
 * variables de requête et y pose « error ». RIEN N'EST PERSISTÉ — aucun « update_option », aucun
 * « wp_insert_post », aucun « update_post_meta », aucun « wp_set_object_terms », aucune règle de
 * réécriture, aucune écriture en base d'aucune sorte, et aucun « flush » requis. La borne 1 est étendue,
 * par écrit, à : « il lit, il RÉPOND, et il peut AMENDER LA REQUÊTE EN MÉMOIRE — jamais l'état
 * persistant. » Bornes 2 et 3 intactes : ce rappel ne dépend d'aucun état en base pour se déclencher et
 * fonctionne à la seconde où le dossier arrive par FTP.
 * ─────────────────────────────────────────────────────────────────────────────────────────────
 */

/*
 * NEUF FAITS DU CŒUR, RELEVÉS DANS LE CONTENEUR LE 2026-09-08, WordPress 6.9. AUCUN N'EST DÉDUIT.
 *
 *   1. « wp-includes/class-wp.php:409 » — « $this->query_vars = apply_filters( 'request',
 *      $this->query_vars ); », et l. 418 « do_action_ref_array( 'parse_request', array( &$this ) ); ».
 *      LE FILTRE COURT AVANT L'ACTION.
 *   2. « wp-includes/rest-api.php:436-438 » — « rest_api_loaded() » lit la variable de route REST sur
 *      l'action « parse_request », DONC APRÈS NOUS, et c'est ELLE qui définit « REST_REQUEST ».
 *   3. « class-wp.php:455-463 » — « send_headers() » lit « $this->query_vars['error'] » et pose
 *      lui-même le statut, les en-têtes anti-cache et le type HTML.
 *   4. « class-wp-query.php:1138 » — « if ( '404' == $query_vars['error'] ) ». La valeur 404 EN CHAÎNE
 *      est celle que le cœur porte lui-même (l. 824, 917, 928) : on n'invente pas une valeur, on
 *      emprunte la sienne.
 *   5. « class-wp.php:743-744 » — « handle_404() » ouvre sur « if ( is_404() ) { return; } ». UN 404
 *      DÉJÀ POSÉ SURVIT, le statut ne peut pas revenir à 200. C'est ce fait, et lui seul, qui rend SANS
 *      OBJET la garde de ceinture « template_redirect » envisagée au plan. Elle n'est pas écrite, et
 *      il ne faut pas l'écrire « au cas où » : une ceinture contre un cas mesuré comme impossible est
 *      une garde qui rassure sans couvrir.
 *   6. « class-wp-query.php:1833-1839 » — « set_404() » PRÉSERVE délibérément « is_feed »
 *      (« $is_feed = $this->is_feed; » … « $this->is_feed = $is_feed; »), et
 *      « wp-includes/template-loader.php:57 » teste « is_feed() » AVANT « is_404 » (l. 69). C'est ce
 *      fait, LU ET NON DÉDUIT, qui rend la garde 4 ci-dessous obligatoire.
 *   7. « wp-includes/canonical.php:319 » — la branche auteur de « redirect_canonical » exige
 *      « is_author() », et l. 327 elle fabrique l'adresse de l'archive à partir du « user_nicename ».
 *      LUI RETIRER SON OBJET LA NEUTRALISE ENTIÈREMENT : elle n'est pas contournée, elle est privée
 *      d'objet. Et l. 960, le devineur de 404 ne travaille que « if ( get_query_var( 'name' ) ) » — une
 *      requête d'auteur vidée n'a pas de nom de contenu, il ne mord donc pas, et le « redirect_url »
 *      attendu au protocole est VIDE.
 *   8. « class-wp.php:319-336 » — la collecte porte « elseif ( isset( $perma_query_vars[ $wpvar ] ) )
 *      { $this->query_vars[ $wpvar ] = $perma_query_vars[ $wpvar ]; } » : les variables issues d'une
 *      règle de réécriture passent bien par « public_query_vars ». Relevé pour documenter le rejet du
 *      filtre « query_vars » — écarté parce qu'il ne pose AUCUN 404 et rendrait donc 200 sur l'index du
 *      blog — et non pour le rouvrir.
 *   9. « wp-admin/includes/post.php:1319 » — « wp( $query ); », dans « wp_edit_posts_query() » ; et
 *      l. 1407 « wp( wp_edit_attachments_query_vars( $q ) ); », dans « wp_edit_attachments_query() ».
 *      CE FILTRE COURT DONC EN ADMINISTRATION : « wp() » appelle « WP::main() », qui appelle
 *      « WP::parse_request() », qui applique « request » (fait 1). CE SONT LES DEUX SEULS APPELS DE
 *      « wp() » DE TOUT « wp-admin/ » — relevé par recherche le 2026-09-08 ; la seule autre ligne que
 *      la recherche rend, « class-wp-posts-list-table.php:164 », est un commentaire. Les deux servent
 *      des écrans de LISTE : toute liste « edit.php » (Articles, Pages, Portées, Chiens, Résultats) et
 *      la Médiathèque EN MODE LISTE (« class-wp-media-list-table.php:102 » appelle
 *      « wp_edit_attachments_query() »). LE MODE GRILLE N'ATTEINT PAS « wp() », et le détail vaut
 *      d'être écrit parce qu'il se lit de travers : « wp-admin/upload.php:140 » branche sur
 *      « if ( 'grid' === $mode ) », et cette branche-là appelle bien « wp_edit_attachments_query_vars() »
 *      (l. 158) — mais c'est L'AUTRE FONCTION, celle qui se contente de fabriquer des variables de
 *      requête et les passe à « wp_localize_script() » (l. 166) pour que le navigateur interroge
 *      ensuite. Deux fonctions de noms voisins et de rôles disjoints : « post.php:1333 »
 *      « wp_edit_attachments_query_vars() », qui n'appelle jamais « wp() », et « post.php:1406 »
 *      « wp_edit_attachments_query() », qui l'appelle l. 1407. La grille va donc chercher ses données
 *      par « admin-ajax.php:103 » (l'action « query-attachments », traitée par
 *      « wp-admin/includes/ajax-actions.php:3021 », « wp_ajax_query_attachments() »), et
 *      « admin-ajax.php » n'appelle jamais « wp() ». CONSÉQUENCE À DIRE PLUTÔT QU'À TAIRE :
 *      « upload.php:137 » fait de « grid » LE MODE PAR DÉFAUT, si bien que le défaut du 2026-09-08 ne
 *      touchait la Médiathèque QUE hors de son mode par défaut — une raison de plus pour qu'il soit
 *      passé inaperçu. « post.php » — modifier, enregistrer — n'appelle jamais « wp() » non plus.
 *      Enfin « author » EST une variable publique (« class-wp.php:18 »), donc elle entre
 *      bien dans « query_vars » en administration : ce n'est pas une possibilité théorique, c'est le
 *      chemin des onglets « Le mien » des écrans de liste. Et « wp-admin/admin.php:104 » appelle
 *      « auth_redirect() » AVANT que « edit.php » n'atteigne « wp_edit_posts_query() ».
 *
 * CES NUMÉROS DE LIGNE SONT ÉPINGLÉS À WordPress 6.9. « wp-includes/ » n'étant pas versionné dans ce
 * dépôt, ILS SE PÉRIMERONT EN SILENCE à la prochaine montée de version. Résidu nommé, non masqué.
 */

/**
 * Les clés de requête qui font d'une requête une requête d'auteur.
 *
 * DÉTECTENT ET SONT RETIRÉES — les deux rôles à la fois, et c'est voulu.
 *
 * UNIQUE ÉCRITURE DE CES DEUX NOMS DANS TOUT « wp-content/plugins/ », même discipline que
 * « FOURNISSEURS_RETIRES » : le contrôle P11 du protocole le vérifie par mesure, pas à l'œil.
 *
 * AUCUN IDENTIFIANT EN DUR, AUCUN NUMÉRO DE COMPTE, AUCUN « user_nicename » N'ENTRE DANS LE CODE DE CE
 * MODULE, ni ici ni ailleurs — les seules occurrences du dépôt sont des relevés datés, en commentaire —
 * et c'est la propriété livrée : CE RAPPEL NE LIT AUCUN COMPTE, donc il vaut pour les comptes présents
 * ET FUTURS, et sa justesse ne dépend d'aucun état de la base. La mesure du 2026-09-08 a trouvé DEUX
 * comptes là où l'énoncé de l'issue en annonçait un, et le second est celui de l'éleveuse : une liste
 * bornée au premier aurait laissé SON identifiant de connexion publié, et aurait menti dès la création
 * d'un troisième compte — le mécanisme qui diverge en silence, condamné par #50 et #52.
 *
 * PAS DE FILTRE D'EXTENSION sur cette liste : un point d'extension ferait de ce module un module de
 * référencement à vocation ouverte — borne 3. Le périmètre se modifie en éditant la constante, dans un
 * commit, avec son motif.
 */
const CLES_D_AUTEUR = array( 'author', 'author_name' );

/**
 * Les clés emportées avec la requête d'auteur : retirées SEULEMENT, jamais détectantes.
 *
 * L'ASYMÉTRIE AVEC « CLES_D_AUTEUR » EST LE FAIT À VOIR D'UN COUP D'ŒIL : un flux seul n'est JAMAIS une
 * requête d'auteur, et une requête d'auteur n'a JAMAIS de flux légitime sur ce site. Les retirer sans
 * jamais les faire détecter est précisément ce qui empêche ce rappel de mordre sur le flux du site.
 *
 * CE N'EST PAS UNE PRÉCAUTION, C'EST LE FAIT 6 CI-DESSUS. Sans ce retrait,
 * « /author/<slug>/feed/ » rendrait LE FLUX DU SITE ENTIER à une adresse d'auteur — statut 404, corps
 * RSS. Un index servi à une URL étrangère : le défaut même que #50 vient de réparer.
 */
const CLES_EMPORTEES = array( 'feed' );

/**
 * Prive la requête d'auteur de son objet, et laisse le cœur en tirer lui-même un 404.
 *
 * DEUX GESTES, DEUX RÔLES DISJOINTS, et c'est ce qui rend la forme lisible : on retire les clés
 * d'auteur, donc « is_author() » ne peut plus être vrai ; on pose « error », donc ce sont
 * « WP::send_headers() » et « WP_Query::parse_query() » qui répondent, par leur propre chemin. NOUS NE
 * POSONS AUCUN STATUT DE NOTRE MAIN, et il n'y a AUCUN « exit ». Nous ne répondons pas après le cœur :
 * nous lui donnons une requête dont il tire lui-même la bonne réponse.
 *
 * POURQUOI LE FILTRE « request » ET NON « template_redirect » 20 COMME LE MODULE VOISIN. #50 a MESURÉ
 * que « redirect_canonical » « exit » en priorité 10. Un rappel à 20 fermerait l'archive en jolie
 * adresse et laisserait la forme en requête ENTIÈREMENT OUVERTE, EN SILENCE — c'est-à-dire la moitié la
 * moins dangereuse livrée comme un tout, alors que l'oracle d'énumération est justement dans la forme
 * en requête. Descendre sous la priorité 10 pour l'attraper est interdit par #50 §11 et réveillerait le
 * devineur de 404. Ici, l'oracle meurt AVANT la priorité 10, donc sans jamais entrer en concurrence
 * avec elle.
 *
 * INTERDIT GELÉ, ET C'EST L'AUTRE BOUT DE LA GARDE 1 : ON RETIRE DES CLÉS NOMMÉES, ON NE REMPLACE
 * JAMAIS LE TABLEAU. Le « return » porte toujours le tableau reçu, amputé. Un « return array( … ) »
 * détruirait la route REST et tout ce qu'un voisin y aurait mis — l'éditeur de blocs par terre, et
 * invisible en recette si l'on ne teste que le front.
 *
 * L'ORDRE DES CINQ GARDES EST IMPOSÉ par le contrat #49 §6, et aucune ne se réordonne : contexte avant
 * tout — administration et REST — parce que ce sont les seules sorties dont l'oubli casse autre chose
 * que cette issue, et l'oubli de la première a bel et bien cassé un écran ; détection avant
 * modification, pour que le coût sur les requêtes ordinaires soit un « array_key_exists() » et rien de
 * plus ; retraits avant la pose de « error », pour que l'état intermédiaire « requête vidée sans 404 »
 * — le faux 200 — n'existe à aucun instant du filtre.
 *
 * @param array $variables Variables de la requête en cours, telles que le cœur vient de les collecter.
 *
 * @return array Le tableau reçu, rendu identique en administration, sur une requête REST et hors d'une
 *               requête d'auteur ; amputé des clés d'auteur et du flux et portant « error » sur une
 *               requête d'auteur du front.
 */
function neutraliser_la_requete_d_auteur( array $variables ): array {
	/*
	 * 1. DEUX SORTIES DE CONTEXTE, ET ELLES SONT LES PREMIÈRES PARCE QUE CHACUNE CASSE AUTRE CHOSE QUE
	 * CETTE ISSUE.
	 *
	 * « is_admin() » — CE FILTRE COURT EN ADMINISTRATION. C'est le fait 9, relevé le 2026-09-08 dans
	 * le conteneur, et il CONTREDIT la prémisse écrite ici jusque-là : « wp_edit_posts_query() »
	 * (« wp-admin/includes/post.php:1319 ») et « wp_edit_attachments_query() » (l. 1407) appellent
	 * « wp() », donc « WP::parse_request() », donc « request ». Sans cette sortie, dès que l'adresse
	 * d'un écran de liste porte une clé d'auteur — ce que fait l'onglet « Le mien » — les clés étaient
	 * retirées et « error » posé : L'ÉCRAN PORTÉES RENDAIT LES 33 PORTÉES SOUS UN ONGLET QUI EN ANNONCE
	 * UNE, en statut 404, sans un mot, sans un terme technique, SANS UNE LIGNE AU JOURNAL. Un écran qui
	 * a l'air de marcher et qui ment sur ce qu'il montre est pire qu'un écran cassé : l'éleveuse n'a
	 * aucune raison de le signaler. Mesuré avant correctif, en session : « Tous (33) | Le mien (1) »,
	 * 20 lignes affichées, « 33 éléments », statut 404.
	 *
	 * ET ELLE NE ROUVRE RIEN SUR LE FRONT — mesuré, pas déduit, parce que c'est une prémisse non
	 * vérifiée qui a produit le défaut ci-dessus. « wp-admin/admin.php:104 » appelle
	 * « auth_redirect() » AVANT que « edit.php » n'atteigne « wp_edit_posts_query() » : un visiteur
	 * anonyme demandant « /wp-admin/edit.php?post_type=… &author=2 » est renvoyé à la connexion en 302
	 * avec un CORPS DE ZÉRO OCTET, avant que « wp() » ne coure — aucune donnée d'auteur ne sort. Les
	 * deux seuls autres contextes où « is_admin() » vaut vrai, « admin-ajax.php » et
	 * « admin-post.php », N'APPELLENT JAMAIS « wp() » (fait 9), donc ce rappel n'y court pas du tout.
	 * La fermeture de l'énumération sur le front est entière.
	 *
	 * « rest_route » — LA GARDE LA PLUS GRAVE DU FICHIER. « rest_api_loaded() » lit la route REST sur
	 * « parse_request », donc APRÈS ce filtre (faits 1 et 2) — et la clé d'auteur EST une variable
	 * publique : « /wp-json/wp/v2/posts?author=1 » la porte. Sans cette sortie, une requête REST
	 * légitime partirait en 404 : L'ÉDITEUR DE BLOCS PAR TERRE, rayon d'explosion « site entier » côté
	 * administration, pour la fermeture d'une archive publique. Doctrine reprise de
	 * « query/page-protegee/bootstrap.php » (« is_admin() vaut FAUX sur /wp-json/ ») ; rien n'est
	 * réinventé ici, et c'est précisément pourquoi « is_admin() » NE SUFFIT PAS et ne remplace pas ce
	 * test.
	 *
	 * « defined( 'REST_REQUEST' ) » EST INTERDIT DANS CE FICHIER, et le dire vaut mieux que de laisser
	 * un successeur le rajouter de bonne foi : à l'instant où ce filtre court, LA CONSTANTE N'EST PAS
	 * ENCORE DÉFINIE, puisque c'est « rest_api_loaded() » qui la définit, après nous (fait 2). La
	 * recopier donnerait l'illusion de la protection REST tout en ne protégeant rien. C'est pourquoi
	 * la garde de contexte des deux services de front voisins n'est toujours pas recopiée telle
	 * quelle : on en reprend « is_admin() », qui couvre aussi « admin-ajax.php », et rien d'autre.
	 * « wp_doing_cron() » n'est pas repris non plus, ET CE TIERS DE PHRASE EST MAINTENANT ANCRÉ COMME
	 * LES AUTRES : c'est le dernier morceau de l'affirmation dont #49 a trouvé le premier morceau faux,
	 * et il ne s'appuyait sur rien, le fait 9 ayant borné sa recherche à « wp-admin/ » alors que
	 * « wp-cron.php » est à la racine. Relevé le 2026-09-08 dans le conteneur, WordPress 6.9 :
	 * « wp-cron.php » ne porte AUCUNE occurrence d'un appel à « wp() » ; il pose
	 * « define( 'DOING_CRON', true ); » (l. 42) puis charge « wp-load.php » (l. 46), et rien de plus.
	 * « WP::parse_request() » n'y court donc jamais, et ce rappel n'y est jamais appliqué : reprendre
	 * « wp_doing_cron() » serait une garde qui rassure sans couvrir.
	 */
	if ( is_admin() || isset( $variables['rest_route'] ) ) {
		return $variables;
	}

	/*
	 * 2. LA QUASI-TOTALITÉ DU TRAFIC ÉCARTÉE EN UN TEST, avant tout autre travail — c'est le contrôle
	 * P7 du protocole qui le prouve, les cinq pages mesurées du site rendant la même taille à l'octet
	 * avant et après. « array_key_exists() » et JAMAIS une comparaison de valeur : il est sûr sur une
	 * valeur tableau, donc une forme en tableau est neutralisée sans « TypeError » malgré
	 * « strict_types » et sans une notice au journal. Une comparaison de valeur à sa place rouvrirait
	 * en miniature la dette de « plan-du-site.php » : un test dont la sémantique dépend de la version
	 * de PHP.
	 *
	 * CONSÉQUENCE ASSUMÉE : la règle est LA PRÉSENCE DE LA CLÉ, sans aucune arithmétique. Elle
	 * sur-couvre légèrement le cœur — une valeur vide, nulle ou non numérique sert aujourd'hui
	 * l'accueil et rendra désormais 404 — et c'est le bon côté de l'erreur, parce qu'une règle sans
	 * comparaison de valeur ne peut pas dériver avec une version de PHP.
	 */
	$est_une_requete_d_auteur = false;

	foreach ( CLES_D_AUTEUR as $cle ) {
		if ( array_key_exists( $cle, $variables ) ) {
			$est_une_requete_d_auteur = true;
			break;
		}
	}

	if ( false === $est_une_requete_d_auteur ) {
		return $variables;
	}

	/*
	 * 3. « is_author() » ne peut plus être vrai : la branche auteur de « redirect_canonical » (fait 7)
	 * est PRIVÉE D'OBJET, l'oracle d'énumération meurt à la racine, et le SQL de la requête principale
	 * ne nomme plus jamais l'auteur. Poser « error » seul laisserait la clé en place et rouvrirait
	 * l'oracle en entier.
	 */
	foreach ( CLES_D_AUTEUR as $cle ) {
		unset( $variables[ $cle ] );
	}

	/*
	 * 4. LE FLUX, ET SEULEMENT PARCE QUE LA GARDE 2 A MORDU (fait 6). Second effet, moins visible et
	 * qu'il faut écrire quand même : un « is_feed() » resté vrai ferait courir pour rien, à chaque
	 * requête d'auteur, les exclusions de recherche de « query/mise-en-sommeil » et
	 * « query/page-protegee ».
	 */
	foreach ( CLES_EMPORTEES as $cle ) {
		unset( $variables[ $cle ] );
	}

	/*
	 * 5. LE 404 EST RENDU PAR LE CŒUR, PAS PAR NOUS (faits 3 et 4). La CHAÎNE, jamais l'entier : c'est
	 * la valeur exacte que le cœur porte lui-même. Sans cette ligne, la requête vidée deviendrait la
	 * page d'accueil et rendrait 200 sur l'index du blog — le faux 200 que #50 vient de réparer,
	 * reproduit à une autre adresse : le remède deviendrait le défaut voisin.
	 */
	$variables['error'] = '404';

	return $variables;
}
