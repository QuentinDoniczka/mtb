<?php
/**
 * Indexation héritée : les contenus que l'ancien site tenait hors des moteurs le restent.
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
 *   1. HONNÊTETÉ DU NOM — un module nommé « redirections-301 » qui poserait « wp_robots »
 *      mentirait sur son contenu.
 *   2. RÉVERSIBILITÉ. Si l'éleveuse demande demain « remettez Placement dans Google », la réponse
 *      est de renommer ce dossier en « _indexation-heritee » — la façon documentée de désactiver un
 *      module (« class-loader.php », initiale « _ ») — SANS TOUCHER UNE LIGNE des 46 redirections.
 *      Avec un module unique, on désactiverait les deux en même temps.
 *   3. TÉMOINS D'ÉCHEC DISJOINTS. « redirections-301 » se prouve vivant par un code de sortie
 *      WP-CLI ; ce module-ci n'a PAS de commande, et sa seule sonde est la balise « robots »
 *      mesurée dans le HTML servi sur « /chien/halan/ ». Deux sondes de nature différente : les
 *      réunir dans un dossier ferait croire qu'une seule suffit.
 *
 * SA PANNE EST INVISIBLE. Il ne rend rien, n'imprime rien, ne journalise rien, ne déclare aucune
 * fonction globale et n'écrit rien en base. S'il cesse d'être chargé, les cinq contenus
 * réapparaissent simplement dans le plan du site et leur balise « robots » redevient
 * « max-image-preview:large » — la page répond 200, le journal reste vide. Le seul témoin est le
 * protocole de vérification du contrat #24 §10.
 *
 * Même amendement que le module voisin : ce module de « migration/ » accroche des hooks de FRONT
 * en permanence, sans garde « WP_CLI », et respecte les trois bornes — lecture seule, aucun état en
 * base pour se déclencher, périmètre clos et daté aux seuls faits relevés sur l'ancien site.
 */

require_once __DIR__ . '/fait.php';
require_once __DIR__ . '/robots.php';
require_once __DIR__ . '/plan-du-site.php';

// Priorité 20 : après « wp_robots_noindex_embeds », « wp_robots_max_image_preview » et les autres
// rappels du cœur, sans quoi le retrait des clés contraires porterait sur un tableau incomplet.
add_filter( 'wp_robots', __NAMESPACE__ . '\\marquer_noindex', 20 );

// Deux arguments demandés : la forme du rappel est imposée au contrat #24 §6.3, et le second
// argument y figure. Il n'est pas utilisé — aucune branche par type de contenu n'est souhaitable —
// mais la signature reste celle qui a été gelée, pour que la revue puisse la comparer au texte.
add_filter( 'wp_sitemaps_posts_query_args', __NAMESPACE__ . '\\ecarter_les_noindex', 10, 2 );

// Exception motivée au contrat #24 §6.4, datée du 2026-09-05 : voir le commentaire de bloc au-dessus
// de « ecarter_le_fournisseur_utilisateurs() » dans « plan-du-site.php ». Priorité par défaut,
// aucun autre rappel connu sur ce crochet dans ce dépôt.
add_filter( 'wp_sitemaps_add_provider', __NAMESPACE__ . '\\ecarter_le_fournisseur_utilisateurs', 10, 2 );
