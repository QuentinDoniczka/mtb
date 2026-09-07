<?php
/**
 * Retrait des moteurs, du plan du site, de la recherche et des flux du contenu mis en sommeil.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Query\MiseEnSommeil;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/etat.php';

/*
 * CE MODULE EST LE FRÈRE DE « query/page-protegee », JAMAIS SON EXTENSION.
 *
 * Chaque module de ces crochets répond à UNE question, sur UN ensemble, avec UNE provenance :
 * « ce contenu porte-t-il un mot de passe ? » se relit dans la colonne « post_password » ; « ce
 * contenu était-il déjà noindex sur l'ancien site ? » est un fait recopié ; « l'ÉLEVEUSE a-t-elle
 * endormi ce contenu ? » est une TROISIÈME question, et c'est celle-ci. Les fondre obligerait
 * « page-protegee » à quitter « has_password » — une forme GELÉE au contrat #23 — pour un besoin qui
 * n'est pas le sien, et le témoin d'inclusion Z1 est PAR MODULE : deux modules, deux sondes. Un
 * module fondu n'aurait plus qu'une sonde pour deux promesses. L'arbitrage complet est au §3 du
 * contrat #52.
 *
 * AUCUNE GARDE DE CONTEXTE À L'INCLUSION, ET SURTOUT PAS « if ( ! is_admin() ) { return; } ».
 *
 * Les façades visées — moteurs de recherche, plan du site, recherche du site, flux — sont des
 * requêtes DE FAÇADE PUBLIQUE, où is_admin() VAUT FAUX, qu'un compte soit connecté ou non.
 * Recopier ici la garde qui est la norme du groupe « admin » — celle que
 * « admin/listes/bootstrap.php » pose à juste titre, et précisément à propos d'un « pre_get_posts » —
 * ne retrancherait pas un cas marginal : elle éteindrait ce module EN ENTIER, sur la seule façade où
 * il sert, et sans rien casser d'observable. Le test de contexte vit DANS LE RAPPEL, jamais au
 * chargement : c'est lui, et non une garde de fichier, qui laisse intacte la recherche de l'éleveuse
 * dans sa propre administration.
 *
 * LA PANNE DE CE MODULE EST INVISIBLE.
 *
 * Il ne rend rien, n'imprime rien, ne journalise rien, n'écrit rien en base et ne déclare aucune
 * fonction globale « mtb_ » — le thème ne l'appelle jamais et ne reçoit aucune clé nouvelle. S'il
 * cesse d'être chargé, ou si une garde est mal posée, un contenu endormi redevient indexable, revient
 * au plan du site, revient à la recherche du site et aux flux : LA PAGE RÉPOND 200 ET « debug.log »
 * RESTE VIDE. Rien à l'écran ne le dirait, et l'éleveuse verrait toujours sa case cochée. Le SEUL
 * témoin est le protocole de vérification du §13 du contrat #52, qui se rejoue EN ENTIER à chaque
 * livraison touchant ces fichiers ; son point Z1 — has_filter() et has_action() interrogés PAR NOM DE
 * RAPPEL, et non par crochet, trois modules du dépôt filtrant « wp_robots » — est la seule preuve
 * directe que ce fichier a été inclus.
 *
 * ─────────────────────────────────────────────────────────────────────────────────────────────
 * CONDITION DE NON-COLLISION SUR « wp_sitemaps_posts_query_args », À NE JAMAIS DÉFAIRE
 *
 * Bloc transplanté le 2026-09-07 de « migration/indexation-heritee/plan-du-site.php », dont le
 * rappel de plan du site a été supprimé par #52 ; augmenté de la mesure d'alias ci-dessous. Il porte
 * une convention GELÉE AUX CONTRATS #23 ET #24, et elle vaut pour tout rappel futur, d'où qu'il
 * vienne.
 *
 * « includes/query/page-protegee/bootstrap.php:71 » accroche le MÊME crochet, à la MÊME priorité,
 * pour retirer le contenu protégé par mot de passe. « add_filter() » EMPILE : les deux rappels
 * s'exécutent, la sortie du premier entrant dans le second. L'ordre leur est indifférent — à UNE
 * condition, et à elle seule :
 *
 *     CHAQUE RAPPEL MUTE DES CLÉS DE « $args ». AUCUN NE REMPLACE « $args ».
 *
 * Si l'un écrivait « $args = array( … ); » ou « return array( … ); », il EFFACERAIT l'autre — sans
 * erreur, sans avertissement, sans une ligne de journal. Le plan du site répondrait 200 en annonçant
 * une page qui devait en être retirée. Sont donc interdits ici : « return array( … ); »,
 * « $arguments = array( … ); » et tout « unset() » d'une clé voisine, « has_password » en tête.
 *
 * Aujourd'hui la disjonction est parfaite : « page-protegee » mute « has_password », ce module-ci
 * mute « post__not_in », et AUCUN DES DEUX NE LIT LA CLÉ DE L'AUTRE. C'est une propriété plus forte
 * que l'additivité : le résultat est identique dans les deux ordres d'exécution.
 *
 * POURQUOI « post__not_in » ET NON UNE « meta_query », ALORS QUE L'ÉTAT EST UNE MÉTA — MESURE, PAS
 * PRÉFÉRENCE. La forme qui vient d'abord à l'esprit est
 * OR( « _mtb_en_sommeil » NOT EXISTS , « _mtb_en_sommeil » != '1' ). Elle est FAUSSE, et sa fausseté
 * se lit dans « WP_Meta_Query » :
 *
 *   - « NOT EXISTS » produit un LEFT JOIN dont le « ON » porte « AND alias.meta_key = %s » ;
 *   - TOUTE AUTRE comparaison, « != » comprise, produit un INNER JOIN dont le « ON » ne porte que
 *     « wp_posts.ID = alias.post_id » ;
 *   - « WP_Meta_Query::find_compatible_table_alias() » ne partage un alias entre frères d'un « OR »
 *     que pour une liste blanche de comparaisons positives — « = », « IN », « BETWEEN », « LIKE »,
 *     « REGEXP », « RLIKE », « > », « >= », « < », « <= » — OÙ NE FIGURENT NI « != » NI
 *     « NOT EXISTS ».
 *
 * Deux alias, donc, dont un INNER JOIN non qualifié par la clé, que le « OR » du WHERE ne peut pas
 * rattraper : TOUT CONTENU NE PORTANT AUCUNE LIGNE DANS « wp_postmeta » DISPARAÎTRAIT DU PLAN DU
 * SITE. C'est-à-dire les pages créées sans passer par un écran d'édition — dont l'ACCUEIL —,
 * auxquelles WordPress n'attache ni « _edit_lock » ni « _edit_last ». Le plan du site répondrait 200,
 * XML valide, amputé, SANS UN MOT. Le contrôle S5 du protocole rend ce verdict mesurable : une page
 * témoin sans aucun champ DOIT figurer au plan du site.
 *
 * La forme retenue n'a aucune jointure « NOT EXISTS », donc aucun piège d'alias : la clause « = » de
 * « identifiants_en_sommeil() » sélectionne UNIQUEMENT les endormis, et « array() » est un no-op
 * strict de WP_Query — « aucun contenu endormi » ne change pas une ligne du SQL du cœur.
 *
 * Et le jour où l'un des rappels de ce crochet emploiera malgré tout une « meta_query », elle
 * s'ENVELOPPERA sous « 'relation' => 'AND' » plutôt que de se compléter par ajout : un ajout naïf
 * dans un « OR » préexistant rendrait l'exclusion sans effet, silencieusement lui aussi.
 * ─────────────────────────────────────────────────────────────────────────────────────────────
 */

// Priorité 20 : après « wp_robots_noindex_embeds », « wp_robots_max_image_preview » et les autres
// rappels du cœur, sans quoi on travaillerait sur un tableau incomplet. C'est la priorité des
// rappels voisins, alignée et non arbitraire.
add_filter( 'wp_robots', __NAMESPACE__ . '\\interdire_indexation_du_contenu_en_sommeil', 20, 1 );

add_filter( 'wp_sitemaps_posts_query_args', __NAMESPACE__ . '\\exclure_du_plan_du_site', 10, 1 );

add_action( 'pre_get_posts', __NAMESPACE__ . '\\exclure_de_la_recherche', 10, 1 );

/**
 * Interdit aux moteurs d'indexer le contenu que l'éleveuse a mis en sommeil.
 *
 * Les deux autres accroches retirent le contenu endormi des index DU SITE ; elles n'empêchent pas un
 * moteur d'atteindre une adresse qu'il connaît déjà — et il la connaît, puisque la promesse centrale
 * de l'issue est que cette adresse continue de répondre 200. Cette balise-ci est donc la seule qui
 * parle aux moteurs.
 *
 * LE RETOUR PASSE PAR « wp_robots_no_robots() », HELPER DU CŒUR ET NON REMPLAÇABLE, JAMAIS PAR UN
 * « noindex » POSÉ À LA MAIN : lui seul accorde aussi « follow » ou « nofollow » selon le réglage
 * « blog_public » du site. Un « noindex, nofollow » écrit à la main retiendrait au passage
 * l'exploration des fiches liées depuis la page endormie — des fiches qui, elles, doivent être
 * indexées. Écart déclaré à l'arbitrage A3 du contrat #52, et consigné pour qu'aucune chaîne future
 * ne le « répare » : sur « blog_public = 1 », un contenu converti depuis le fait hérité passe de
 * « noindex, nofollow » à « noindex, follow ». Le fait recopié reste intact en base, avec sa
 * provenance ; seule la directive servie s'aligne sur la règle, ce qui est le sens même de la
 * conversion.
 *
 * Aucune autre clé de « $robots » n'est touchée — « max-image-preview » comprise — et rien ne
 * s'applique hors des vues singulières : les archives, la recherche et les flux ne contiennent plus
 * le contenu endormi, les leur marquer serait marquer tout le site.
 *
 * LA CONDITION PORTE SUR LE CONTENU, JAMAIS SUR LE VISITEUR. Ni session, ni cookie, ni capacité : une
 * directive conditionnelle serait empoisonnée en cache dès la première mise en cache, et servie à
 * tous dans l'état où le premier visiteur l'a fabriquée. Arbitrage déjà rendu à « page-protegee », il
 * vaut ici sans changement.
 *
 * Le paramètre et le retour ne sont pas typés, et c'est le précédent d'« admin/corbeille » : un
 * filtre tiers peut avoir rendu autre chose qu'un tableau, et « strict_types » en ferait une erreur
 * fatale — ici, un « <head> » tronqué servi à un moteur.
 *
 * @param mixed $robots Directives d'indexation déjà composées.
 *
 * @return mixed Directives complétées du refus d'indexation, ou la valeur reçue telle quelle.
 */
function interdire_indexation_du_contenu_en_sommeil( $robots ) {
	if ( ! is_array( $robots ) ) {
		return $robots;
	}

	if ( ! is_singular() ) {
		return $robots;
	}

	$identifiant = (int) get_queried_object_id();

	if ( $identifiant <= 0 ) {
		return $robots;
	}

	if ( ! est_en_sommeil( $identifiant ) ) {
		return $robots;
	}

	return wp_robots_no_robots( $robots );
}

/**
 * Retire du plan du site les contenus en sommeil, sous-plan par sous-plan.
 *
 * La clé mutée est « post__not_in », et elle est FUSIONNÉE avec la valeur déjà présente, jamais
 * remplacée : un rappel voisin peut en avoir posé une. La convention de cohabitation qui l'exige est
 * écrite en tête de ce fichier ; le motif du choix de cette clé plutôt que d'une « meta_query » y est
 * démontré, mesuré sur le code du cœur.
 *
 * LE SECOND ARGUMENT DU CŒUR EST DÉLIBÉRÉMENT REFUSÉ. Le crochet passe le type de contenu en
 * deuxième position ; ne pas le demander rend STRUCTURELLEMENT IMPOSSIBLE d'écrire un jour une
 * branche par type — c'est-à-dire la porte par laquelle un type de contenu futur serait oublié du
 * plan du site, en silence. La règle est la même pour tous ; elle s'écrit donc une fois.
 *
 * Aucune autre clé n'est touchée — ni « post_status », ni « post_type », ni « orderby », ni
 * « posts_per_page », ni « no_found_rows » : la pagination et l'ordre du plan restent entièrement au
 * cœur.
 *
 * Le paramètre et le retour ne sont pas typés, même précédent que ci-dessus : ici, un XML tronqué
 * servi à un moteur de recherche.
 *
 * @param mixed $arguments Arguments de requête du sous-plan en cours de construction.
 *
 * @return mixed Arguments dont la seule clé « post__not_in » est mutée, ou la valeur reçue telle
 *               quelle.
 */
function exclure_du_plan_du_site( $arguments ) {
	if ( ! is_array( $arguments ) ) {
		return $arguments;
	}

	$existant = isset( $arguments['post__not_in'] ) && is_array( $arguments['post__not_in'] )
		? $arguments['post__not_in']
		: array();

	$arguments['post__not_in'] = array_values( array_unique( array_merge( $existant, identifiants_en_sommeil() ) ) );

	return $arguments;
}

/**
 * Retire les contenus en sommeil de la recherche du site et des flux.
 *
 * Les deux façades sont traitées ensemble parce qu'elles sont la même requête : celle que WordPress
 * bâtit à partir de l'adresse demandée. Un flux est un index public au sens littéral du BRIEF §8 —
 * arbitrage déjà rendu pour le contenu protégé (contrat #23), repris ici sans changement, pour qu'il
 * n'existe entre deux modules frères aucune asymétrie qu'une chaîne future « corrigerait » dans un
 * sens ou dans l'autre.
 *
 * CE CROCHET COURT SUR CHAQUE REQUÊTE DU SITE : les trois gardes ci-dessous ne sont pas des
 * précautions de style, elles sont le module. Leur ordre est porteur et chacune a son motif propre.
 *
 * 1. HORS ADMINISTRATION, ET C'EST LE MODE DE PANNE N° 1. En administration, le cœur peuple le
 *    WP_Query global : sur « edit.php?post_type=mtb_chien&s=halan », is_main_query() ET is_search()
 *    valent vrai tous les deux. Sans cette première garde, le contenu endormi disparaîtrait de la
 *    recherche de l'éleveuse DANS SON PROPRE ÉCRAN, alors qu'on lui promet précisément de l'y
 *    retrouver, marqué « En sommeil ». Un interrupteur dont l'objet devient introuvable n'est plus
 *    réversible.
 * 2. REQUÊTE PRINCIPALE SEULE, ET C'EST LA SEULE GARDE QUI PROTÈGE LA REST. is_admin() vaut FAUX sur
 *    « /wp-json/ » ; is_main_query() compare la requête au WP_Query global, qu'un contrôleur REST
 *    n'emploie jamais. C'est elle qui laisse l'éditeur de blocs proposer un contenu endormi dans son
 *    sélecteur de lien — le sommeil ne retire rien des pages ni des menus du site, il faut donc
 *    pouvoir continuer d'y pointer. Elle empêche par ailleurs la WP_Query interne de
 *    « identifiants_en_sommeil() » de se re-filtrer elle-même, en renfort du drapeau de ré-entrance.
 * 3. RECHERCHE OU FLUX SEULEMENT. is_search() seul serait trop étroit — il laisserait les flux
 *    ouverts ; sans la garde 1, il serait au contraire trop large côté administration.
 *
 * L'effet est une FUSION de « post__not_in » avec la valeur existante, jamais un remplacement : la
 * requête de recherche peut déjà en porter un, posé par le cœur ou par un rappel voisin.
 *
 * Aucun test de session, et c'est une décision : la promesse du BRIEF §8 est inconditionnelle, un
 * index rendu conditionnel serait empoisonné en cache, et is_user_logged_in() est une fonction
 * remplaçable, interdite à l'extension. Conséquence assumée, à écrire dans la fiche d'aide : la
 * recherche DU SITE ne remonte pas un contenu endormi, même pour l'éleveuse connectée. Elle le
 * retrouve dans « Portées », « Chiens » et « Pages », où rien n'est filtré.
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

	$existant = $requete->get( 'post__not_in' );
	$existant = is_array( $existant ) ? $existant : array();

	$requete->set( 'post__not_in', array_values( array_unique( array_merge( $existant, identifiants_en_sommeil() ) ) ) );
}
