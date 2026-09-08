<?php
/**
 * Indexation héritée : conversion du fait relevé sur l'ancien site en un état que l'éleveuse pilote.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\IndexationHeritee;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * MODULE DISTINCT DE « redirections-301 », POUR TROIS MOTIFS GELÉS :
 *
 *   1. HONNÊTETÉ DU NOM — un module nommé « redirections-301 » qui convertirait une directive
 *      d'indexation mentirait sur son contenu.
 *   2. RÉVERSIBILITÉ. Elle ne passe plus par le code depuis le 2026-09-07 (#52), ET C'EST TOUT
 *      L'OBJET DE L'ISSUE : à « remettez Placement dans Google », la réponse est désormais
 *      « ouvrez Placement et décochez "Mettre ce contenu en sommeil" ». Le renommage du dossier en
 *      « _indexation-heritee » — la façon documentée de désactiver un module
 *      (« class-loader.php », initiale « _ ») — NE RÉVEILLE PLUS AUCUN CONTENU MIS EN SOMMEIL : il
 *      empêcherait seulement la conversion des contenus PAS ENCORE convertis, l'état des autres
 *      étant déjà en base et servi par « query/mise-en-sommeil ». IL N'EST PAS NEUTRE POUR AUTANT, et
 *      il faut le dire ici parce que c'est UN PIÈGE MATÉRIEL pour qui l'appliquerait de bonne foi.
 *      TROIS EFFETS, ET ILS TOMBENT ENSEMBLE : il rendrait au plan du site le fournisseur écarté le
 *      2026-09-05 ; il ferait tomber avec lui le 404 franc posé depuis le 2026-09-08 (#50), les deux
 *      lisant la même constante ; et, depuis le 2026-09-08 (#49), IL ROUVRIRAIT TOUTES LES ARCHIVES
 *      D'AUTEUR DU SITE — « /author/<slug>/ » de retour à 200 et la forme en requête de retour à 301
 *      vers elle — DONC L'ÉNUMÉRATION DES COMPTES, CELUI DE L'ÉLEVEUSE COMPRIS, DE NOUVEAU LISIBLE.
 *      Le module reste néanmoins séparé pour que ce renommage, s'il devient nécessaire, n'emporte pas
 *      une ligne de la carte des 52 adresses reprises.
 *   3. TÉMOINS D'ÉCHEC DISJOINTS. « redirections-301 » se prouve vivant par un code de sortie
 *      WP-CLI ; ce module-ci n'a PAS de commande, et sa seule sonde est l'état écrit en base sur les
 *      contenus repris. Deux sondes de nature différente : les réunir dans un dossier ferait croire
 *      qu'une seule suffit.
 *
 * CE QUE CE MODULE FAIT DEPUIS LE 2026-09-07 (#52), ET CE QU'IL NE FAIT PLUS.
 *
 * Il ne SERT PLUS AUCUNE DIRECTIVE. Il ne pose plus « wp_robots », il ne retire plus rien du plan du
 * site à partir du fait hérité : « robots.php » est supprimé, « ecarter_les_noindex() » aussi, et
 * leurs deux « add_filter » avec eux. Il CONVERTIT, une fois et pour toutes, le fait relevé sur
 * l'ancien site en l'état « en sommeil » — une case à cocher dans un écran, que l'éleveuse peut
 * décocher. C'est « query/mise-en-sommeil » qui sert la directive, à partir de CET état-là.
 *
 * CE QU'IL FAIT EN PLUS DEPUIS LE 2026-09-08 (#50) : IL RÉPOND POUR LE FOURNISSEUR QU'IL RETIRE.
 * Ce n'est pas une directive d'indexation de plus — il n'en sert toujours aucune. C'est la
 * réparation de ce que le retrait avait cassé : « /wp-sitemap-users-1.xml » rendait 200 sur du
 * HTML, un faux 404. Le détail, la mesure et la borne sont écrits en tête de « plan-du-site.php ».
 *
 * POURQUOI LA CONVERSION ET NON LA COHABITATION DES DEUX MÉCANISMES. Les faire vivre côte à côte
 * produit le pire mode de panne de l'issue : l'éleveuse réveille Halan, l'ancien filtre continue de
 * rendre « noindex » et de le retirer du plan du site, LE RÉVEIL NE RÉVEILLE RIEN — sur un écran qui
 * affirme le contraire. Et le RATTRAPAGE PARTIEL EST REFUSÉ : on peut retirer un « noindex » posé par
 * un voisin, on ne peut pas défaire la clause qu'il a posée dans les arguments du plan du site sans
 * violer la convention de cohabitation gelée aux contrats #23 et #24. On obtiendrait un demi-réveil :
 * balise rétablie, plan du site toujours amputé. La démonstration complète est en tête de
 * « conversion.php ».
 *
 * POURQUOI LE RATTRAPAGE PARTIEL EST AUSSI REFUSÉ CÔTÉ DÉCLENCHEUR. « mtb_core_mise_a_jour » ne se
 * déclenche que si l'empreinte d'identité change ; #52 n'ajoute ni type ni taxonomie et ne touche pas
 * « mtb-core.php ». CE CROCHET NE SE DÉCLENCHERA DONC PAS UNE SEULE FOIS du fait de #52 sur une base
 * déjà pourvue de « mtb_core_empreinte », et là où il se déclenche il court AVANT l'import. Une
 * conversion accrochée à lui seul ne convertirait rien, en silence. D'où trois accroches sur la même
 * fonction idempotente, chacune avec son rôle nommé, « admin_init » étant le déclencheur porteur.
 *
 * PREMIÈRE SURFACE SILENCIEUSE : LA PANNE DE LA CONVERSION EST INVISIBLE. Elle ne rend rien,
 * n'imprime rien, ne journalise rien et ne déclare aucune fonction globale. Si ce module cesse
 * d'être chargé APRÈS la conversion, l'état converti ne bouge pas : il est en base, et c'est
 * « query/mise-en-sommeil » qui le lit. S'il cesse d'être chargé AVANT, les contenus repris ne sont
 * jamais convertis et restent indexables — la page répond 200, le journal reste vide. Le seul
 * témoin est le protocole du §13 du contrat #52, points C1 à C6.
 *
 * DEUXIÈME SURFACE SILENCIEUSE, DEPUIS #50, ET DE NATURE DIFFÉRENTE. Si le rappel de
 * « template_redirect » cesse de mordre — ligne « add_action » perdue dans une reprise, priorité
 * changée, « FOURNISSEURS_RETIRES » vidée, dossier renommé en « _indexation-heritee » —
 * « /wp-sitemap-users-1.xml » revient à 200 sur du HTML, silencieusement. RIEN NE LE DIRAIT : pas
 * une ligne au journal, pas un écran, et l'éleveuse ne visite jamais cette adresse. Sa sonde n'est
 * pas en base, c'est un code de statut HTTP, et elle se joue en recette — protocole du §9 du
 * contrat #50. LUI DONNER UNE COMMANDE WP-CLI CONTREDIRAIT LE MOTIF 3 CI-DESSUS, qui tient ce
 * module séparé de « redirections-301 » précisément parce que leurs témoins d'échec sont de
 * natures différentes ; en ajouter un troisième de la nature du voisin brouillerait la frontière
 * que ce motif protège. Résidu nommé, non masqué.
 *
 * TROISIÈME SURFACE SILENCIEUSE, DEPUIS #49, DE MÊME NATURE QUE LA PRÉCÉDENTE ET D'UN TOUT AUTRE ENJEU.
 * Si le rappel du filtre « request » cesse de mordre — ligne « add_filter » perdue dans une reprise,
 * « CLES_D_AUTEUR » vidée, dossier renommé en « _indexation-heritee », le cœur renommant une de ces
 * clés ou cessant d'honorer « error », un tiers filtrant « request » après nous et les remettant —
 * « /author/<slug>/ » revient à 200 et la forme en requête à 301, silencieusement. RIEN NE LE DIRAIT :
 * pas une ligne au journal, pas un écran, et l'éleveuse ne visite jamais ces adresses. LA NUANCE QUI SE
 * DIT PLUTÔT QU'ELLE NE SE LISSE : ce que #50 protège est une adresse machine que personne ne demande ;
 * CE QUE CELUI-CI PROTÈGE EST L'IDENTIFIANT DE CONNEXION DE L'ÉLEVEUSE. Le mode de panne est muet dans
 * les deux cas, l'enjeu ne l'est pas. Sa sonde est de même nature que celle de #50 — un code de statut
 * HTTP, joué en recette, protocole du §11 du contrat #49 — et AUCUNE SONDE SUPPLÉMENTAIRE N'EST
 * PROPOSÉE POUR AUTANT : la seule qui aurait du sens serait une commande WP-CLI, et LUI EN DONNER UNE
 * CONTREDIRAIT LE MOTIF 3 CI-DESSUS. Résidu nommé, non masqué, et son poids réel écrit plutôt que lissé.
 *
 * AMENDEMENT DÉCLARÉ À LA BORNE 1 (contrat #24 §15) : ce module ÉCRIT désormais, ce qu'il ne faisait
 * pas. La borne 1 porte sur ses hooks de FRONT ; les trois accroches de la conversion sont
 * « mtb_core_mise_a_jour », « added_post_meta » / « updated_post_meta » (administration ou WP-CLI) et
 * « admin_init » (administration seule) : AUCUNE ÉCRITURE SUR UNE REQUÊTE PUBLIQUE.
 * Les TROIS hooks de front de ce module — « wp_sitemaps_add_provider » 10, « template_redirect » 20
 * depuis le 2026-09-08 (#50) et « request » 10 depuis le 2026-09-08 (#49) — n'écrivent RIEN. Le
 * premier rend « false » au cœur ; le deuxième pose un code de statut sur la réponse en cours ; le
 * troisième AMENDE LA REQUÊTE EN MÉMOIRE, pour le seul processus en cours — et « de front » se dit ici
 * de son EFFET, non de son contexte d'exécution : le cœur applique aussi « request » sur les écrans de
 * liste de l'administration, où ce rappel sort par sa première garde sans rien amender (voir le renvoi
 * de son « add_filter », plus bas). La borne 1 disait « il lit », #50 l'a resserrée en « il lit, il
 * RÉPOND » — poser un 404 est répondre, pas écrire — et #49 l'étend une seconde fois, par écrit et non
 * en silence (contrat #49 §13.1) : « IL LIT, IL RÉPOND, ET IL PEUT AMENDER LA REQUÊTE EN MÉMOIRE —
 * JAMAIS L'ÉTAT PERSISTANT. » Aucun « update_option », « wp_insert_post », « update_post_meta »,
 * « wp_set_object_terms » ni « wp_delete_post » sur une requête publique, et aucune règle de
 * réécriture touchée. Les bornes 2 et 3 sont intactes : aucun état en base ne déclenche la conversion
 * — c'est la requête elle-même qui la borne — le périmètre reste clos aux seuls faits
 * « _mtb_robots_source » relevés sur l'ancien site, et celui de #49 est clos et daté aux archives
 * d'auteur de ce site.
 *
 * MESURE D'ÉGALITÉ DU CONTRAT #24 §6.2, RELEVÉE LE 2026-09-07 : « le nombre de contenus portant
 * _mtb_robots_source, le nombre rendus noindex et le nombre retirés du plan du site sont ÉGAUX » cesse
 * d'être vrai, et ce n'est pas une régression. Le fait hérité ne PRODUIT plus la directive, il a été
 * CONVERTI. Deux nombres distincts le remplacent : 5, constante historique des contenus portant
 * « _mtb_robots_source », que l'étape 6 de « wp mtb verifier-redirections » compte EN BASE et
 * continue de vérifier ; et n, nombre vivant de contenus portant l'état « en sommeil », piloté par
 * l'éleveuse et destiné à changer.
 */

require_once __DIR__ . '/fait.php';
require_once __DIR__ . '/plan-du-site.php';
require_once __DIR__ . '/conversion.php';
require_once __DIR__ . '/archives-d-auteur.php';

/*
 * Trois accroches sur une seule fonction idempotente, parce qu'aucune ne couvre à elle seule les
 * ordres possibles entre le déploiement du code et l'import. Leur partage exact, et le motif de
 * chacune, sont écrits en tête de « conversion.php » ; ne pas en retirer une en croyant dédoublonner.
 */
add_action( 'mtb_core_mise_a_jour', __NAMESPACE__ . '\\convertir', 10, 0 );
add_action( 'added_post_meta', __NAMESPACE__ . '\\sur_arrivee_du_fait', 10, 4 );
add_action( 'updated_post_meta', __NAMESPACE__ . '\\sur_arrivee_du_fait', 10, 4 );
add_action( 'admin_init', __NAMESPACE__ . '\\rattraper', 10 );

// Exception motivée au contrat #24 §6.4, datée du 2026-09-05 : voir le bloc d'exception en tête de
// « plan-du-site.php », au-dessus de « FOURNISSEURS_RETIRES ». Priorité par défaut, aucun autre
// rappel connu sur ce crochet dans ce dépôt.
add_filter( 'wp_sitemaps_add_provider', __NAMESPACE__ . '\\ecarter_le_fournisseur_utilisateurs', 10, 2 );

// Réparation du faux 404 laissé par le retrait ci-dessus (dette T106, issue #50, 2026-09-08).
// PRIORITÉ 20, ET CE N'EST PAS QU'UNE QUESTION D'ORDRE AVEC LE CŒUR :
//   1. « WP_Sitemaps::render_sitemaps() » court en priorité 10 (relevé le 2026-09-08 dans le
//      conteneur, « wp-includes/sitemaps/class-wp-sitemaps.php:69 », accroche sans argument de
//      priorité) ; à 20 il a déjà rendu la main par son « return » nu, et nous répondons pour le
//      fournisseur que nous avons retiré. Si une version future du cœur se met à répondre
//      correctement, elle « exit » avant nous et ce rappel ne parle jamais ;
//   2. « redirect_canonical » court AUSSI en priorité 10 (« wp-includes/default-filters.php:666 »),
//      et appelle « redirect_guess_404_permalink() » quand la requête EST un 404. Poser
//      « set_404() » AVANT lui — en priorité 1, comme le module voisin — lui donnerait un 404 à
//      deviner, et « /wp-sitemap-users-1.xml » partirait en 301 vers un contenu au hasard, ce qui
//      serait PIRE que la panne d'origine. La priorité 20 laisse le devineur endormi, parce qu'il a
//      déjà statué sur une requête qui n'était pas un 404.
// Ne jamais descendre ce rappel sous la priorité de « redirect_canonical ». Pas d'« accepted_args » :
// « template_redirect » ne passe aucun argument.
add_action( 'template_redirect', __NAMESPACE__ . '\\repondre_404_au_sous_plan_retire', 20 );

// Neutralisation des archives d'auteur (dette T105, issue #49, 2026-09-08). Le détail et les neuf faits
// du cœur sont écrits en tête de « archives-d-auteur.php » ; l'ordre imposé des cinq gardes est écrit
// au-dessus du rappel lui-même, dans le même fichier.
// LE FILTRE « request », ET NON « template_redirect » 20 COMME LE RAPPEL CI-DESSUS. Le motif est
// mesuré, pas déduit : #50 a relevé que « redirect_canonical » « exit » en priorité 10
// (« wp-includes/default-filters.php:666 »), si bien qu'un rappel à 20 ne mordrait JAMAIS sur la forme
// en requête et laisserait l'oracle d'énumération des comptes — la moitié la plus dangereuse —
// entièrement ouvert, EN SILENCE. « request » court dans « WP::parse_request() »
// (« class-wp.php:409 »), donc AVANT l'action « parse_request » (l. 418), donc avant
// « rest_api_loaded() » et avant que « REST_REQUEST » ne soit défini : la garde REST de ce rappel est
// « isset( $variables['rest_route'] ) », et « defined( 'REST_REQUEST' ) » y est interdit comme
// trompeur. Le tableau n'est jamais remplacé : seules des clés nommées en sont retirées.
// ET « request » COURT AUSSI EN ADMINISTRATION. Ce renvoi affirmait le contraire ; c'était FAUX, et
// personne ne l'avait mesuré. FAIT RELEVÉ le 2026-09-08 dans le conteneur : « wp() » a EXACTEMENT DEUX
// sites d'appel dans tout « wp-admin/ » — « includes/post.php:1319 » dans « wp_edit_posts_query() » et
// « includes/post.php:1407 » dans « wp_edit_attachments_query() » — et « wp() » applique « request » par
// « WP::parse_request() ». Toute liste « edit.php » et la Médiathèque en mode liste passent donc par ce
// rappel, et l'onglet « Le mien » met une clé d'auteur dans leur adresse : sans garde, l'écran Portées
// rendait LES 33 PORTÉES SOUS UN ONGLET QUI EN ANNONCE UNE, en 404, sans un mot et sans une ligne au
// journal. LA GARDE 1 PORTE DONC « is_admin() » À CÔTÉ DE « rest_route ». Elle ne rouvre rien sur le
// front, et c'est mesuré et non déduit : « wp-admin/admin.php:104 » appelle « auth_redirect() » AVANT
// que « edit.php » n'atteigne « wp_edit_posts_query() », si bien qu'un anonyme part en 302 vers
// « wp-login.php » avec un corps de zéro octet, avant tout appel à « wp() » ; et « admin-ajax.php »
// comme « admin-post.php », les deux seuls autres contextes où « is_admin() » vaut vrai, n'appellent
// jamais « wp() ». Le détail est écrit au fait 9 en tête de « archives-d-auteur.php ».
// PRIORITÉ 10, UN ARGUMENT. Aucun autre rappel de ce crochet n'existe dans ce dépôt — vérifié par
// recherche sur « wp-content/ » le 2026-09-08 — donc aucune concurrence de priorité.
add_filter( 'request', __NAMESPACE__ . '\\neutraliser_la_requete_d_auteur', 10, 1 );
