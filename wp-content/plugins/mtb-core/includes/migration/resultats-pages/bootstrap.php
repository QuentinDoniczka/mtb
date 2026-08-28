<?php
/**
 * Commande « wp mtb reprise-resultats-pages » : reprise des résultats de travail et des pages libres.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\ResultatsPages;

/*
 * Garde WP-CLI en première ligne exécutable, avant même le garde ABSPATH : sur une requête web,
 * rien de ce module n'est exécuté au-delà d'ici — aucun hook posé, aucun fichier inclus, aucun
 * accès à la base, aucune sortie. WP_CLI n'est défini que par WP-CLI, une fois WordPress chargé,
 * si bien que ce garde couvre déjà l'accès direct au fichier ; celui d'ABSPATH le suit malgré tout,
 * parce qu'aucun fichier de l'extension ne s'en dispense.
 */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/journal.php';
require_once __DIR__ . '/modele.php';
require_once __DIR__ . '/schema-fichier.php';
require_once __DIR__ . '/fichier.php';
require_once __DIR__ . '/controle.php';
require_once __DIR__ . '/composition.php';
require_once __DIR__ . '/ecriture.php';
require_once __DIR__ . '/photo.php';
require_once __DIR__ . '/correspondances.php';
require_once __DIR__ . '/resultats.php';
require_once __DIR__ . '/pages.php';
require_once __DIR__ . '/verification.php';
require_once __DIR__ . '/commande.php';

/*
 * Une fonction, et aucune classe de commande : « WP_CLI::add_command( 'mtb …' ) » crée l'espace de
 * noms parent « mtb » tout seul, et « wp mtb import-fixtures » vit déjà dans son propre dossier.
 *
 * Le nom de sous-commande est le nom du dossier d'empreinte : deux reprises aux empreintes
 * disjointes ont des noms disjoints PAR CONSTRUCTION, sans coordination entre elles.
 *
 * « shortdesc » et « synopsis » sont ce qui fait sortir « --help » en 0.
 */
\WP_CLI::add_command(
	'mtb reprise-resultats-pages',
	__NAMESPACE__ . '\\executer',
	array(
		'shortdesc' => 'Reprend les résultats de travail et les pages libres de l’ancien site.',
		'synopsis'  => array(
			array(
				'type'        => 'assoc',
				'name'        => 'resultats',
				'description' => 'Chemin du fichier des résultats de travail. Par défaut, celui livré avec l’extension.',
				'optional'    => true,
			),
			array(
				'type'        => 'assoc',
				'name'        => 'pages',
				'description' => 'Dossier des fiches de page. Par défaut, celui livré avec l’extension.',
				'optional'    => true,
			),
			array(
				'type'        => 'assoc',
				'name'        => 'correspondances',
				'description' => 'Chemin du fichier des correspondances chien. Par défaut, celui livré avec l’extension.',
				'optional'    => true,
			),
			array(
				'type'        => 'assoc',
				'name'        => 'photos',
				'description' => 'Dossier où chercher les photos citées. Aucune valeur par défaut : le téléversement est différé.',
				'optional'    => true,
			),
			array(
				'type'        => 'flag',
				'name'        => 'simuler',
				'description' => 'Déroule la reprise sans rien écrire.',
				'optional'    => true,
			),
			array(
				'type'        => 'flag',
				'name'        => 'verifier',
				'description' => 'Compare la base aux fichiers et signale les écarts, sans jamais écrire.',
				'optional'    => true,
			),
			array(
				'type'        => 'flag',
				'name'        => 'raccrocher',
				'description' => 'Pose les liens chien manquants sur les résultats déjà repris.',
				'optional'    => true,
			),
		),
		'longdesc'  => "## NOTES\n\n"
			. "La commande CRÉE le contenu absent et laisse strictement intact le contenu présent. Elle ne met jamais à jour une page ni un résultat existants, et ne supprime jamais rien : les sept pages existent déjà en base de développement, et les corrections de l’éleveuse ne sont écrasées par aucun outil qu’elle ne voit pas.\n\n"
			. "**Conséquence à connaître avant de lancer** : sur une base déjà peuplée, la reprise comptera « déjà présente » et n’écrira RIEN. Pour l’observer, il faut détruire la base de développement — « docker compose down -v » — puis reprovisionner. C’est un cycle destructeur.\n\n"
			. "Le compte courant doit disposer de « unfiltered_html ». Sans utilisateur, WP-CLI laisse « wp_filter_post_kses » réduire les « -- » du contenu des commentaires de blocs, et le balisage des pages serait détruit en silence : la commande refuse alors d’écrire.\n\n"
			. "Les photos ne sont pas téléversées aujourd’hui, et ce n’est plus le dossier qui manque : l’archive du site source est montée en lecture seule sur le conteneur, et « --photos=/var/www/html/docs/migration/source/photos » y trouverait les images. Ce qui manque est le TEXTE ALTERNATIF : aucune de ces photos n’en porte dans la capture, et en inventer un serait inventer un fait. C’est une question à l’éleveuse. Les pages sont donc créées sans photo, l’emplacement n’existe pas dans le rendu, et le code de sortie reste 0.\n\n"
			. "Un résultat dont le chien n’a pas de fiche n’est jamais rejeté : le nom est écrit tel quel, aucun lien n’est posé, et un avertissement le dit. « --raccrocher » repasse plus tard et ne touche qu’un lien dont la valeur est actuellement zéro.\n\n"
			. "La fidélité des données à la capture de l’ancien site ne se vérifie pas ici : elle se vérifie hors ligne, avec « python concordance/concordance.py », qui lit le HTML archivé de « docs/ ». Ce contrôle se lance depuis l’arbre de travail et jamais depuis un conteneur — non pas que l’archive y manque, elle y est montée, mais parce que le conteneur n’a ni « python » ni « python3 ».\n\n"
			. "## EXAMPLES\n\n"
			. "    wp mtb reprise-resultats-pages --simuler\n"
			. "    wp mtb reprise-resultats-pages --user=admin\n"
			. "    wp mtb reprise-resultats-pages --verifier\n"
			. "    wp mtb reprise-resultats-pages --raccrocher --user=admin\n",
	)
);
