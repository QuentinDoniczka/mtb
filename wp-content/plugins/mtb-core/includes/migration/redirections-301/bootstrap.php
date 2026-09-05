<?php
/**
 * Redirections permanentes des 46 adresses de l'ancien site, et réparation de ses liens internes.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\Redirections301;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * AMENDEMENT DÉCLARÉ AU CONTRAT #1 §2, qui assigne au groupe « migration/ » une « déclaration à
 * l'inclusion, sous garde WP_CLI ». Ce module dépose DEUX HOOKS DE FRONT PERMANENTS —
 * « template_redirect » 1 et « the_content » 20 — et déclare cet écart plutôt que de dévier en
 * douce (précédent : décision 46, le formulaire de contact). Le texte complet de l'amendement est
 * au §15 du contrat #24.
 *
 * POURQUOI « migration/ » ET PAS AILLEURS. L'empreinte annoncée à l'issue, « includes/seo/** », est
 * IRRÉALISABLE : « class-loader.php:145-152 » déclare six groupes en dur, liste close, et un
 * dossier hors des six n'est JAMAIS inclus — pas d'erreur, pas de ligne au journal, pas même la
 * note en WP_DEBUG. Module mort et silencieux. Et « migration/ » est le bon endroit sur le fond :
 * la table des 301 n'existe QUE PARCE QUE l'ancien site a existé. Elle est finie, datée, gelée à
 * jamais, et vit à côté des données dont elle décrit les adresses. Qui cherchera dans six ans
 * « pourquoi /bhpl/portée-m-2016/ marche encore ? » cherchera dans « migration/ ».
 *
 * LES TROIS BORNES DE L'AMENDEMENT, QUE CE MODULE RESPECTE :
 *   1. il est EN LECTURE SEULE — jamais « update_option », « wp_insert_post », « update_post_meta » ;
 *   2. il ne DÉPEND D'AUCUN ÉTAT EN BASE pour se déclencher — sa table est dans ses fichiers, il
 *      fonctionne à la seconde où le dossier arrive par FTP, sans réglage, sans visite de
 *      « wp-admin », sans régénération de règles de réécriture ;
 *   3. son PÉRIMÈTRE EST CLOS ET DATÉ — 52 adresses énumérées de l'ancien site, jamais un routeur
 *      général.
 *
 * AUCUNE GARDE « WP_CLI » EN TÊTE DE FICHIER, contrairement à « migration/import-fixtures » : le
 * service 301 et la réparation des ancres sont des services de FRONT. La garde ne couvre que la
 * commande de vérification, plus bas.
 */

require_once __DIR__ . '/carte.php';
require_once __DIR__ . '/chemin.php';
require_once __DIR__ . '/cible.php';
require_once __DIR__ . '/service.php';
require_once __DIR__ . '/ancres.php';

add_action( 'template_redirect', __NAMESPACE__ . '\\rediriger', 1 );
add_filter( 'the_content', __NAMESPACE__ . '\\reparer_les_ancres', 20 );

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once __DIR__ . '/commande.php';

	/*
	 * « shortdesc » et « synopsis » ne sont pas de la décoration : ce sont eux qui font sortir
	 * « wp mtb verifier-redirections --help » en code 0, et c'est par cet appel que le protocole de
	 * vérification sonde l'existence du module. Sans cette sonde, un module absent donnerait 46
	 * lignes « 404 » qu'on lirait comme « les redirections ne marchent pas » au lieu de « le module
	 * n'existe pas ». Le précédent est « docker/provision/provision.sh:239 ».
	 */
	\WP_CLI::add_command(
		'mtb verifier-redirections',
		__NAMESPACE__ . '\\verifier',
		array(
			'shortdesc' => 'Vérifie la carte des 52 adresses de l\'ancien site contre son référentiel et contre la base.',
			'synopsis'  => array(
				array(
					'type'        => 'assoc',
					'name'        => 'sitemap',
					'description' => 'Chemin du plan du site de l\'ancien site servant de référentiel.',
					'optional'    => true,
				),
			),
			'longdesc'  => "## NOTES\n\n"
				. "Cette commande N'ÉCRIT RIEN : ni option, ni contenu, ni métadonnée. Elle lit le disque et la base, jamais le réseau.\n\n"
				. "Elle prouve que la table est cohérente avec le référentiel et que chaque cible se résout aujourd'hui. Elle ne dit RIEN de ce que le serveur répond : cela se mesure au « curl », et les deux contrôles ne se présentent jamais l'un pour l'autre.\n\n"
				. "Huit étapes : 0 le référentiel n'a pas bougé — 1 chaque adresse du référentiel est une clé — 2 chaque clé est une adresse du référentiel — 3 chaque cible de verdict « 301 » se résout en contenu publié — 3 bis avertissement quand la cible n'a été obtenue que par le repli — 4 aucune boucle — 5 chaque lien vers l'ancien domaine stocké en base est couvert — 6 le nombre de contenus non indexés — 7 les repères d'installation.\n\n"
				. "Elle se joue en recette, depuis l'arbre du dépôt, où « docs/migration/source/sitemap.xml » existe. Le dossier « docs/ » n'est pas déployé en production, et aucun autre fichier de ce module ne le lit.\n\n"
				. "## EXAMPLES\n\n"
				. "    wp mtb verifier-redirections\n",
		)
	);
}
