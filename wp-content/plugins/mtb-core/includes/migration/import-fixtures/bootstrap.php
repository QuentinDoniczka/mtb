<?php
/**
 * Commande « wp mtb import-fixtures » : jeu de contenus de démonstration.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\ImportFixtures;

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
require_once __DIR__ . '/references.php';
require_once __DIR__ . '/ecriture.php';
require_once __DIR__ . '/photo.php';
require_once __DIR__ . '/chiens.php';
require_once __DIR__ . '/portees.php';
require_once __DIR__ . '/resultats.php';
require_once __DIR__ . '/commande.php';

/*
 * Une fonction, et aucune classe de commande : « WP_CLI::add_command( 'mtb …' ) » crée l'espace de
 * noms parent « mtb » tout seul. Une future « wp mtb import-ancien-site » s'ajoutera donc dans son
 * propre dossier, sans toucher un fichier commun.
 *
 * « shortdesc » et « synopsis » sont ce qui fait sortir « wp mtb import-fixtures --help » en 0 :
 * c'est par cet appel que docker/provision/provision.sh sonde l'existence de la commande.
 */
\WP_CLI::add_command(
	'mtb import-fixtures',
	__NAMESPACE__ . '\\executer',
	array(
		'shortdesc' => 'Importe le jeu de contenus de démonstration (chiens, portées, résultats de travail).',
		'synopsis'  => array(
			array(
				'type'        => 'assoc',
				'name'        => 'chiens',
				'description' => 'Chemin du fichier des fiches Chien.',
				'optional'    => true,
			),
			array(
				'type'        => 'assoc',
				'name'        => 'portees',
				'description' => 'Chemin du fichier des portées.',
				'optional'    => true,
			),
			array(
				'type'        => 'assoc',
				'name'        => 'resultats',
				'description' => 'Chemin du fichier des résultats de travail.',
				'optional'    => true,
			),
		),
		'longdesc'  => "## NOTES\n\n"
			. "Le jeu importé est FICTIF et destiné à la pile de développement : rien de ce contenu ne doit atteindre une base de production. Ses marqueurs sont « LOF DEMO … », les identifiants de portée « DEMO<n> <année> » et l'affixe « de Démonstration ».\n\n"
			. "Les trois options sont facultatives : un appel partiel fonctionne, et un type non fourni n'apparaît dans aucune ligne du rapport.\n\n"
			. "L'import crée le contenu absent et laisse strictement intact le contenu présent. Il ne supprime jamais rien et ne pose aucun marqueur d'origine : un contenu importé est indiscernable d'un contenu saisi. Conséquence : modifier un fichier de fixtures ne met PAS à jour une base existante — il faut « docker compose down -v ».\n\n"
			. "Les photos sont lues dans le sous-dossier « photos/ » du dossier du fichier qui les cite. Aucun appel réseau n'est fait.\n\n"
			. "Chaque entrée refusée donne un avertissement nommé ; les entrées valides du même fichier sont importées, et la commande sort en 1 dès un seul rejet.\n\n"
			. "## EXAMPLES\n\n"
			. "    wp mtb import-fixtures --chiens=/fixtures/chiens.json --portees=/fixtures/portees.json --resultats=/fixtures/resultats.json\n",
	)
);
