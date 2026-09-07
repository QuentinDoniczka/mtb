<?php
/**
 * Mention « En sommeil » à côté du titre, dans les listes d'administration.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Admin\Sommeil;

use function MTB\Core\Query\MiseEnSommeil\est_en_sommeil;
use function MTB\Core\Fields\Sommeil\libelle_etat;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * POURQUOI CE MODULE EST SÉPARÉ DE « fields/sommeil », QUI TRAITE POURTANT DU MÊME INTERRUPTEUR.
 *
 * L'un a besoin de la garde d'ouverture, l'autre serait TUÉ par elle. « fields/sommeil » doit courir
 * sur la façade REST, où is_admin() vaut faux, sans quoi la métadonnée ne serait pas déclarée et le
 * case de la zone latérale ne pourrait rien écrire. Ce module-ci, à l'inverse, ne pose qu'un seul crochet,
 * « display_post_states », qui N'EXISTE QU'EN ADMINISTRATION : la garde y est donc parfaitement sûre,
 * et c'est la norme du groupe. Les réunir dans un seul module obligerait à choisir, et fabriquerait
 * exactement le piège que décrit le §2.2 du contrat #23.
 *
 * La garde est la plus forte possible : le rappel n'est pas seulement inerte hors de wp-admin, il n'y
 * est jamais attaché.
 */

if ( ! is_admin() ) {
	return;
}

/*
 * On require les FICHIERS, jamais le bootstrap.php d'un autre module, et on ne recopie ni la clé, ni
 * sa lecture, ni le libellé. La lecture porte le repli ouvert — seule la chaîne « 1 » endort — et le
 * libellé est celui, unique, que lisent aussi les deux écrans de saisie : c'est ce qui empêche la
 * liste de dire autre chose que la case.
 */
require_once MTB_CORE_DIR . 'includes/query/mise-en-sommeil/etat.php';
require_once MTB_CORE_DIR . 'includes/fields/sommeil/libelles.php';

add_filter( 'display_post_states', __NAMESPACE__ . '\\ajouter_la_mention', 10, 2 );

/**
 * Ajoute « En sommeil » aux mentions que le cœur affiche à côté du titre.
 *
 * « display_post_states » est la primitive NATIVE du cœur, celle qui écrit déjà « Brouillon »,
 * « Protégé par un mot de passe » et « Page d'accueil » : la mention se range donc à côté d'états que
 * l'éleveuse connaît déjà, à la place qu'elle connaît déjà, sans une colonne de plus et sans un octet
 * de CSS. DU TEXTE, ET JAMAIS UNE COULEUR SEULE — une pastille sans mot ne se lit ni au lecteur
 * d'écran, ni en vision des couleurs déficiente, ni sur une impression en noir et blanc.
 *
 * LA MENTION DÉRIVE DE L'ÉTAT, SANS AUCUNE LISTE DE TYPES. Elle paraît donc dans Portées, dans Chiens
 * ET dans Pages sans que ces trois écrans soient nommés nulle part ici, et elle resterait juste si un
 * type futur portait la clé. « admin/listes/types.php » n'est pas ouvert par cette issue, et n'a pas
 * à l'être.
 *
 * Le rappel n'est pas typé : le cœur passe un tableau et un WP_Post, mais un filtre voisin peut avoir
 * rendu autre chose, et « strict_types » en ferait une erreur fatale AU MILIEU DE LA LISTE — donc un
 * écran « Portées » blanc, sur lequel l'éleveuse ne peut plus rien ouvrir.
 *
 * @param mixed $etats Mentions déjà composées, indexées par clé.
 * @param mixed $post  Contenu de la ligne en cours.
 *
 * @return mixed Mentions complétées, ou la valeur reçue telle quelle.
 */
function ajouter_la_mention( $etats, $post ) {
	if ( ! is_array( $etats ) ) {
		return $etats;
	}

	if ( ! $post instanceof \WP_Post ) {
		return $etats;
	}

	if ( ! est_en_sommeil( (int) $post->ID ) ) {
		return $etats;
	}

	/*
	 * Le cœur imprime ces mentions sans les échapper lui-même (wp-admin/includes/class-wp-posts-
	 * list-table.php) : l'échappement se fait donc ici, à la composition.
	 */
	$etats['mtb_en_sommeil'] = esc_html( libelle_etat() );

	return $etats;
}
