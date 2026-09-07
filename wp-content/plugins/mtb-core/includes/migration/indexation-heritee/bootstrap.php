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
 *      étant déjà en base et servi par « query/mise-en-sommeil ». IL N'EST PAS NEUTRE POUR AUTANT,
 *      et il faut le dire ici : il rendrait au plan du site le fournisseur écarté le 2026-09-05, et
 *      ferait tomber avec lui le 404 franc posé depuis le 2026-09-08 (#50) — les deux effets
 *      tombent ensemble, puisqu'ils lisent la même constante. Le module reste néanmoins séparé pour
 *      que ce renommage, s'il devient nécessaire, n'emporte pas une ligne de la carte des 52
 *      adresses reprises.
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
 * AMENDEMENT DÉCLARÉ À LA BORNE 1 (contrat #24 §15) : ce module ÉCRIT désormais, ce qu'il ne faisait
 * pas. La borne 1 porte sur ses hooks de FRONT ; les trois accroches de la conversion sont
 * « mtb_core_mise_a_jour », « added_post_meta » / « updated_post_meta » (administration ou WP-CLI) et
 * « admin_init » (administration seule) : AUCUNE ÉCRITURE SUR UNE REQUÊTE PUBLIQUE.
 * Les DEUX hooks de front de ce module — « wp_sitemaps_add_provider » 10 et, depuis le 2026-09-08
 * (#50), « template_redirect » 20 — n'écrivent RIEN. Le premier rend « false » au cœur ; le second
 * pose un code de statut sur la réponse en cours. La borne 1 dit « il lit, il RÉPOND » : poser un
 * 404 est répondre, pas écrire. Aucun « update_option », « wp_insert_post », « update_post_meta »,
 * « wp_set_object_terms » ni « wp_delete_post » sur une requête publique. Les bornes 2 et 3
 * sont intactes : aucun état en base ne déclenche la conversion — c'est la requête elle-même qui la
 * borne — et le périmètre reste clos aux seuls faits « _mtb_robots_source » relevés sur l'ancien
 * site.
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
