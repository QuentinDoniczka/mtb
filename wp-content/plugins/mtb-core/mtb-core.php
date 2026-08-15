<?php
/**
 * Plugin Name:        MTB Core
 * Description:        Contenu et logique métier de l'élevage du Mont Brabant : portées, chiens, résultats de travail, composants d'édition et fonctions de lecture du thème. Extension sur mesure, sans dépendance externe.
 * Version:            0.1.0
 * Requires at least:  6.5
 * Requires PHP:       8.1
 * Author:             Mont Brabant
 * License:            GPL v2 or later
 * License URI:        https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:        mtb-core
 *
 * Ce fichier en entier doit rester analysable par PHP 7.0 : PHP compile un fichier complet avant
 * d'en exécuter la première ligne, donc le garde-fou de version ci-dessous ne protège que les
 * fichiers inclus APRÈS lui, jamais ce fichier. Toute syntaxe postérieure à PHP 7.0 ici
 * (types d'union, match, ?->, énumérations, attributs, type de retour « void »…) remplacerait le
 * message d'erreur propre par une erreur fatale d'analyse. C'est aussi pourquoi ce fichier ne
 * déclare ni fonction ni classe : il ne contient qu'une fermeture anonyme pour la notice.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Garde de double chargement : une extension présente à la fois dans plugins/ et copiée dans
 * mu-plugins/ serait incluse deux fois et produirait un « Constant already defined ».
 */
if ( defined( 'MTB_CORE_VERSION' ) ) {
	return;
}

if ( version_compare( PHP_VERSION, '8.1', '<' ) ) {
	add_action(
		'admin_notices',
		function () {
			$message = 'L’extension « MTB Core » a besoin de PHP 8.1 ou d’une version plus récente pour fonctionner. '
				. 'Ce serveur exécute actuellement PHP ' . PHP_VERSION . '. '
				. 'Le site continue de fonctionner, mais les portées, les chiens et les résultats de travail '
				. 'ne sont pas disponibles tant que la version de PHP n’a pas été relevée chez l’hébergeur.';

			echo '<div class="notice notice-error"><p>' . esc_html( $message ) . '</p></div>';
		}
	);

	return;
}

/*
 * define() et non const : dans un fichier sous « namespace MTB\Core; », const MTB_CORE_VERSION
 * créerait la constante MTB\Core\MTB_CORE_VERSION, invisible d'un defined( 'MTB_CORE_VERSION' )
 * et donc inutilisable par les modules comme par le thème.
 */
define( 'MTB_CORE_VERSION', '0.1.0' );
define( 'MTB_CORE_FILE', __FILE__ );
define( 'MTB_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'MTB_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once __DIR__ . '/includes/class-loader.php';

/*
 * Chargement immédiat, et non sur « plugins_loaded » : WordPress inclut les extensions avant de
 * déclencher plugins_loaded. Charger tout de suite laisse aux modules la possibilité d'accrocher
 * plugins_loaded eux-mêmes, ce qu'un chargement différé leur interdirait.
 */
Loader::charger();
