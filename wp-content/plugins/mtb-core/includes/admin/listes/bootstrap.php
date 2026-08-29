<?php
/**
 * Colonnes, ordre, filtres et modification groupée des trois listes d'administration.
 *
 * Ce que l'éleveuse voit changer : deux ou trois colonnes de plus après le titre, une liste
 * déroulante de filtre à la place de celle des mois, un ordre qui a un sens, et un champ
 * « Disponibilité » dans la Modification groupée des portées. Rien ne change sur le site public.
 *
 * UN SEUL MODULE POUR LES TROIS LISTES, ET NON TROIS. Le moteur — portée d'écran, balayage,
 * sentinelle, application du filtre — est rigoureusement identique pour les trois types ; il ne
 * diffère que par la table de description de « types.php ». Trois modules imposeraient soit trois
 * copies de ce moteur, qui finiraient par diverger, soit une inclusion croisée entre modules du même
 * groupe, qui rendrait implicite un ordre de dépendance.
 *
 * ÉCART ASSUMÉ AU COMMENTAIRE DU CHARGEUR. Celui-ci annonce pour le groupe « admin » les crochets
 * « admin_menu » et « admin_init » ; les nôtres sont d'autres crochets d'administration, comme ceux
 * de « admin/medias ». Ce sont les crochets que le cœur offre pour ce travail, et il n'en existe pas
 * d'autres.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Admin\Listes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Le vocabulaire et les listes fermées appartiennent à d'autres modules. On les require_once plutôt
 * que de compter sur l'ordre de parcours du chargeur : un module ne doit jamais dépendre de cet
 * ordre, et une seconde inclusion est sans effet. Recopier ici une liste de valeurs ou un libellé
 * ferait diverger deux vocabulaires en silence, et l'écran finirait par dire autre chose que la base.
 *
 * « content/portee/champs.php » et non la liste jumelle du module de lecture : c'est celle-ci que
 * consulte l'assainisseur de la disponibilité, et la validation de la modification groupée doit lire
 * exactement la liste que l'assainissement fera respecter.
 *
 * Les neuf disciplines, elles, viennent d'une fonction globale que le module de lecture définit sous
 * garde : on ne require jamais le bootstrap.php d'un autre module, on teste au moment de l'appel
 * (voir « disciplines() » dans types.php).
 */
require_once MTB_CORE_DIR . 'includes/content/portee/champs.php';
require_once MTB_CORE_DIR . 'includes/content/chien/choix.php';
require_once MTB_CORE_DIR . 'includes/query/portee/hydratation.php';

require_once __DIR__ . '/types.php';
require_once __DIR__ . '/colonnes.php';
require_once __DIR__ . '/ordre.php';
require_once __DIR__ . '/filtres.php';
require_once __DIR__ . '/modification-groupee.php';

/*
 * Aucun crochet n'est posé hors de l'administration, et c'est la garde la plus forte possible : les
 * rappels ne sont pas seulement inertes sur le site public, ils n'y sont jamais attachés. Tous les
 * crochets ci-dessous sont propres à wp-admin, sauf « pre_get_posts », qui court sur chaque requête —
 * raison de plus pour ne pas l'y attacher. Le rappel garde malgré tout son propre contrôle, la
 * défense d'une seule ligne étant celle qui saute le jour où quelqu'un déplace le fichier.
 *
 * Contrairement au module « admin/medias », cette garde est ici parfaitement sûre : rien de ce que ce
 * module fait n'a de sens ni d'effet au téléversement d'une photo ou sous WP-CLI.
 */
if ( ! is_admin() ) {
	return;
}

add_filter( 'manage_mtb_portee_posts_columns', __NAMESPACE__ . '\\colonnes_portee' );
add_filter( 'manage_mtb_chien_posts_columns', __NAMESPACE__ . '\\colonnes_chien' );
add_filter( 'manage_mtb_resultat_posts_columns', __NAMESPACE__ . '\\colonnes_resultat' );

/*
 * « manage_edit-<type>_sortable_columns » N'EST ENREGISTRÉ QUE POUR RETRANCHER, JAMAIS POUR AJOUTER.
 * Aucune de nos sept colonnes n'y est ajoutée : une colonne de champ triable au clic ferait
 * construire à la requête une jointure sur la valeur du champ, et tout contenu dépourvu de ce champ
 * disparaîtrait de la liste sans un mot. À la place, un ordre par défaut imposé une fois, calculé en
 * PHP.
 *
 * L'unique objet du rappel est de retirer le cinquième élément de l'entrée « date » du cœur, celui
 * qui marque la colonne Date comme triée par défaut. Cet ordre par défaut n'existe plus depuis que
 * ce module en impose un autre : sans ce retrait, l'en-tête Date porterait un aria-sort="descending"
 * annonçant à un lecteur d'écran un ordre que la table n'a pas. Titre et Date restent cliquables,
 * c'est le comportement natif, on n'y touche pas.
 */

add_filter( 'manage_edit-mtb_portee_sortable_columns', __NAMESPACE__ . '\\retirer_ordre_initial_de_date' );
add_filter( 'manage_edit-mtb_chien_sortable_columns', __NAMESPACE__ . '\\retirer_ordre_initial_de_date' );
add_filter( 'manage_edit-mtb_resultat_sortable_columns', __NAMESPACE__ . '\\retirer_ordre_initial_de_date' );

add_action( 'manage_mtb_portee_posts_custom_column', __NAMESPACE__ . '\\rendre_cellule', 10, 2 );
add_action( 'manage_mtb_chien_posts_custom_column', __NAMESPACE__ . '\\rendre_cellule', 10, 2 );
add_action( 'manage_mtb_resultat_posts_custom_column', __NAMESPACE__ . '\\rendre_cellule', 10, 2 );

add_filter( 'disable_months_dropdown', __NAMESPACE__ . '\\desactiver_liste_des_mois', 10, 2 );
add_action( 'restrict_manage_posts', __NAMESPACE__ . '\\afficher_filtre', 10, 2 );

add_action( 'pre_get_posts', __NAMESPACE__ . '\\imposer_ordre' );

add_action( 'bulk_edit_custom_box', __NAMESPACE__ . '\\champ_groupe', 10, 2 );
add_action( 'bulk_edit_posts', __NAMESPACE__ . '\\ecrire_disponibilite', 10, 2 );
