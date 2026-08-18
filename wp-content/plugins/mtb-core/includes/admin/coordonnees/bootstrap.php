<?php
/**
 * Écran unique des coordonnées de l'élevage : adresse, téléphone, courriel, page de contact.
 *
 * L'écran ÉCRIT l'option ; il ne la lit pour personne d'autre. Les fonctions de lecture publiques
 * — « mtb_get_telephone_elevage() », « mtb_get_page_contact() »,
 * « mtb_get_coordonnees_elevage() » — vivent dans le groupe « query », et c'est délibéré : elles
 * sont consommées sur la façade publique, par les composants, à chaque rendu. Les domicilier ici
 * exposerait ce module au piège que « admin/medias/bootstrap.php » documente sur dix lignes — le
 * jour où quelqu'un « rangerait » le groupe derrière une garde « is_admin() », le numéro
 * s'éteindrait sur tout le site public, en silence.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Admin\Coordonnees;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Le nom de l'option, ses valeurs de départ et son assainisseur appartiennent au module
 * « query/coordonnees ». On les require_once plutôt que de compter sur l'ordre de parcours du
 * chargeur : un module ne doit jamais dépendre de cet ordre, et une seconde inclusion est sans
 * effet. Réécrire le nom de l'option en dur ici ferait écrire l'écran dans une option que personne
 * ne lit, sans la moindre erreur.
 */
require_once MTB_CORE_DIR . 'includes/query/coordonnees/option.php';
require_once __DIR__ . '/ecran.php';

add_action( 'admin_menu', __NAMESPACE__ . '\\ajouter_menu' );
add_action( 'admin_post_' . ACTION, __NAMESPACE__ . '\\enregistrer' );
