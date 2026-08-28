<?php
/**
 * Commandes « wp mtb importer-portees-chiens » et « wp mtb verifier-portees-chiens ».
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\PorteesChiens;

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
require_once __DIR__ . '/chemins.php';
require_once __DIR__ . '/fichier.php';
require_once __DIR__ . '/provenance.php';
require_once __DIR__ . '/texte.php';
require_once __DIR__ . '/schema-fichier.php';
require_once __DIR__ . '/controle.php';
require_once __DIR__ . '/references.php';
require_once __DIR__ . '/ecriture.php';
require_once __DIR__ . '/photos.php';
require_once __DIR__ . '/garde.php';
require_once __DIR__ . '/chiens.php';
require_once __DIR__ . '/portees.php';
require_once __DIR__ . '/verification.php';
require_once __DIR__ . '/commande.php';

/**
 * Options communes aux deux commandes.
 *
 * @return array<int, array<string, mixed>> Synopsis WP-CLI.
 */
function synopsis(): array {
	return array(
		array(
			'type'        => 'assoc',
			'name'        => 'chiens',
			'description' => 'Chemin du fichier des fiches Chien transcrites. Par défaut : donnees/chiens.json du module.',
			'optional'    => true,
		),
		array(
			'type'        => 'assoc',
			'name'        => 'portees',
			'description' => 'Chemin du fichier des portées transcrites. Par défaut : donnees/portees.json du module.',
			'optional'    => true,
		),
		array(
			'type'        => 'assoc',
			'name'        => 'source',
			'description' => "Racine de l'archive du site source, à laquelle les clés « source » sont relatives. Par défaut : docs/migration/source du dépôt.",
			'optional'    => true,
		),
		array(
			'type'        => 'assoc',
			'name'        => 'photos',
			'description' => "Dossier des photographies archivées. Par défaut : le sous-dossier « photos » de la racine du source.",
			'optional'    => true,
		),
	);
}

/*
 * Deux fonctions, et aucune classe de commande : « WP_CLI::add_command( 'mtb …' ) » crée l'espace
 * de noms parent « mtb » tout seul, si bien qu'aucun fichier commun n'est touché — c'est la
 * condition pour que trois chaînes écrivent en parallèle sans se recouvrir.
 *
 * Le nommage est délibérément SANS AMBIGUÏTÉ avec « wp mtb import-fixtures », qui sème du fictif.
 * Deux commandes voisines dont l'une détruit la valeur de l'autre doivent se lire à un caractère
 * près, pas se deviner.
 */
\WP_CLI::add_command(
	'mtb importer-portees-chiens',
	__NAMESPACE__ . '\\executer_import',
	array(
		'shortdesc' => "Importe les 17 fiches Chien et les 27 portées transcrites depuis l'ancien site.",
		'synopsis'  => synopsis(),
		'longdesc'  => "## NOTES\n\n"
			. "Le contenu importé est RÉEL et destiné à la base de production. La commande REFUSE de s'exécuter si la base porte déjà un contenu de démonstration : une fois le fictif et le réel mêlés, ils ne se distinguent plus à l'œil nu.\n\n"
			. "L'import crée le contenu absent et laisse strictement intact le contenu présent. Il ne supprime jamais rien. Conséquence : corriger un fichier de données ne met PAS à jour une base existante.\n\n"
			. "Les photographies sont lues sur le disque, dans l'archive du dépôt. Aucun appel réseau n'est fait. Une seule pièce jointe est créée par condensé SHA-256 : les 192 fichiers de l'archive donnent 150 images distinctes.\n\n"
			. "Lancez la commande avec « --user=<administrateur> » : sans session, les textes libres traversent wp_kses(), qui échappe les chevrons.\n\n"
			. "Chaque entrée refusée donne un avertissement nommé ; les entrées valides du même fichier sont importées, et la commande sort en 1 dès un seul rejet ou un seul défaut.\n\n"
			. "## EXAMPLES\n\n"
			. "    wp mtb importer-portees-chiens --user=1\n"
			. "    wp mtb importer-portees-chiens --chiens=/chemin/chiens.json --portees=/chemin/portees.json --user=1\n",
	)
);

\WP_CLI::add_command(
	'mtb verifier-portees-chiens',
	__NAMESPACE__ . '\\executer_verification',
	array(
		'shortdesc' => 'Vérifie la reprise des portées et des chiens en cinq passes, sans rien écrire.',
		'synopsis'  => synopsis(),
		'longdesc'  => "## NOTES\n\n"
			. "Aucune écriture, jamais : la commande se lance sur n'importe quelle base, y compris une base douteuse.\n\n"
			. "Les cinq passes : complétude par soustraction ; confrontation de chaque valeur non vide à l'extrait littéral de sa source ; contrôle aval en base sur les 44 entités ; clés absentes signalées comme oublis ; démonstration du rejeu.\n\n"
			. "Un échec est NOMMÉ — entité, clé, valeur — jamais résumé en un décompte. La commande sort en 1 dès un seul échec.\n\n"
			. "## EXAMPLES\n\n"
			. "    wp mtb verifier-portees-chiens\n",
	)
);
