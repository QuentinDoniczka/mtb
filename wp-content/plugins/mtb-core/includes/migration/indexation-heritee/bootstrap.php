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
 *      (« class-loader.php », initiale « _ ») — NE REMET PLUS RIEN DANS GOOGLE : il empêcherait
 *      seulement la conversion des contenus PAS ENCORE convertis, l'état des autres étant déjà en
 *      base et servi par « query/mise-en-sommeil ». Le module reste néanmoins séparé pour que ce
 *      renommage, s'il devient nécessaire, n'emporte pas une ligne des 46 redirections.
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
 * SA PANNE EST INVISIBLE. Il ne rend rien, n'imprime rien, ne journalise rien et ne déclare aucune
 * fonction globale. S'il cesse d'être chargé APRÈS la conversion, rien ne change : l'état est en
 * base, et c'est « query/mise-en-sommeil » qui le lit. S'il cesse d'être chargé AVANT, les contenus
 * repris ne sont jamais convertis et restent indexables — la page répond 200, le journal reste vide.
 * Le seul témoin est le protocole du §13 du contrat #52, points C1 à C6.
 *
 * AMENDEMENT DÉCLARÉ À LA BORNE 1 (contrat #24 §15) : ce module ÉCRIT désormais, ce qu'il ne faisait
 * pas. La borne 1 porte sur ses hooks de FRONT ; les trois accroches de la conversion sont
 * « mtb_core_mise_a_jour », « added_post_meta » / « updated_post_meta » (administration ou WP-CLI) et
 * « admin_init » (administration seule) : AUCUNE ÉCRITURE SUR UNE REQUÊTE PUBLIQUE. Le seul hook de
 * front qui reste, « wp_sitemaps_add_provider », demeure strictement en lecture. Les bornes 2 et 3
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

// Exception motivée au contrat #24 §6.4, datée du 2026-09-05 : voir le commentaire de bloc au-dessus
// de « ecarter_le_fournisseur_utilisateurs() » dans « plan-du-site.php ». Priorité par défaut,
// aucun autre rappel connu sur ce crochet dans ce dépôt.
add_filter( 'wp_sitemaps_add_provider', __NAMESPACE__ . '\\ecarter_le_fournisseur_utilisateurs', 10, 2 );
