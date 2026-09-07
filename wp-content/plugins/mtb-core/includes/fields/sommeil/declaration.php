<?php
/**
 * Déclaration de l'état « en sommeil » auprès du cœur, pour les trois types concernés.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Fields\Sommeil;

use const MTB\Core\Query\MiseEnSommeil\CLE;
use const MTB\Core\Query\MiseEnSommeil\ENDORMI;
use const MTB\Core\Query\MiseEnSommeil\REVEILLE;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Les types de contenu qui portent l'état « en sommeil ». Liste close, source unique.
 *
 * LES TROIS TYPES, ET PAS UN DE PLUS. Pas « post » : le site n'en publie aucun de l'élevage. Pas
 * « mtb_resultat » : il est déclaré « public => false », il n'a donc ni adresse propre, ni entrée au
 * plan du site, ni présence dans la recherche — un sommeil y serait sans objet, et le déclarer
 * laisserait croire qu'il agit.
 *
 * CETTE LISTE NE SE RECOPIE NULLE PART, ET C'EST LE MOTIF DE LA CONSTANTE. Elle décide de trois
 * choses qui doivent toujours s'accorder : les types auprès desquels la métadonnée est déclarée, les
 * écrans où la case se rend, et les crochets d'enregistrement qui l'écrivent. Recopiée, elle
 * divergerait SANS QU'UNE LIGNE DE JOURNAL NE LE DISE — un quatrième type déclaré ici mais absent de
 * l'écran classique donnerait une métadonnée qui existe et une case qui n'est nulle part ; absent des
 * crochets d'enregistrement, une case qui se coche et ne s'enregistre jamais. Les deux pannes sont
 * muettes et rendent 200.
 */
const TYPES = array( 'page', 'mtb_portee', 'mtb_chien' );

/**
 * Nom du champ de formulaire de la case, sur l'écran classique.
 *
 * Il est lu à DEUX endroits — « ecran-classique.php » l'écrit dans l'attribut « name », et
 * « sauvegarde.php » le cherche dans « $_POST » — et ces deux-là doivent s'accorder au caractère près.
 * Changé d'un seul côté, l'enregistrement ne trouverait plus la case ; or une case absente du POST est
 * indistinguable d'une case décochée : CHAQUE enregistrement écrirait « réveillé », et tout contenu
 * endormi que l'éleveuse rouvrirait se réveillerait en silence.
 */
const CHAMP = 'mtb_en_sommeil';

/**
 * Déclare l'état « en sommeil » sur les trois types qui le portent. Appelée sur « init », priorité 20.
 *
 * AUCUN « default », JAMAIS, ET C'EST STRUCTUREL. Un défaut rendrait get_post_meta() incapable de
 * séparer « clé absente » de « '0' », donc détruirait le troisième état — donc l'idempotence de la
 * conversion du fait hérité, qui repose entièrement sur metadata_exists(). Le jour où quelqu'un
 * ajoute « 'default' => '0' » pour « faire propre », la conversion se remet à rendormir ce que
 * l'éleveuse a réveillé, en silence.
 *
 * « show_in_rest » VRAI POUR « page » SEULE. Seules les pages ont l'éditeur de blocs, et c'est lui
 * qui a besoin de lire et d'écrire cet état par la façade REST. Les deux types « mtb_ » emploient
 * l'éditeur classique et passent par un formulaire : leur ouvrir la REST serait une surface sans
 * usage. Surface déclarée, non fermée, et écrite ici pour qu'elle ne soit pas découverte plus tard :
 * « show_in_rest » ouvre la LECTURE ANONYME de cet état sur les pages ; l'auth_callback ne garde que
 * l'écriture. Ce n'est ni une donnée de domaine ni une donnée personnelle, et l'information est déjà
 * publiquement observable dans le « <head> » de la page endormie elle-même : la REST n'ajoute aucune
 * connaissance.
 *
 * L'« auth_callback » EST OBLIGATOIRE, PAS DÉFENSIF. La clé commence par un souligné, elle est donc
 * PROTÉGÉE au sens du cœur, et register_meta() lui affecterait « __return_false » par défaut : TOUTE
 * écriture par la REST serait refusée, la case du panneau « Résumé » se cocherait, la page
 * s'enregistrerait, et la case reviendrait décochée — sans erreur, sans journal, sur un écran qui
 * répond 200. Il teste « current_user_can( 'edit_post', $object_id ) », LA CAPACITÉ SUR L'OBJET, et
 * jamais « edit_posts » : la capacité générale autoriserait à endormir une page qu'on n'a pas le
 * droit de modifier.
 */
function declarer_le_champ(): void {
	foreach ( TYPES as $type ) {
		register_post_meta(
			$type,
			CLE,
			array(
				'single'            => true,
				'type'              => 'string',
				'show_in_rest'      => 'page' === $type,
				'sanitize_callback' => __NAMESPACE__ . '\\assainir',
				'auth_callback'     => __NAMESPACE__ . '\\peut_modifier',
			)
		);
	}
}

/**
 * Ramène toute valeur reçue à l'un des deux seuls états écrits.
 *
 * Miroir exact du repli ouvert de « est_en_sommeil() » : seule la chaîne « 1 » endort, tout le reste
 * vaut visible et s'écrit « 0 ». Cet assainisseur ne rend JAMAIS la chaîne vide, ce qui garantit
 * qu'une valeur écrite est toujours l'un des deux états connus — le troisième état, « jamais réglé »,
 * étant l'ABSENCE de la clé et non une valeur.
 *
 * La garde « is_scalar() » est là pour le même motif mesuré qu'à « est_en_sommeil() » : « (string) »
 * sur un tableau lève un avertissement PHP, donc une ligne au journal, sur une écriture qui doit
 * simplement se ramener à « visible ».
 *
 * @param mixed $valeur Valeur soumise, par le formulaire ou par la façade REST.
 *
 * @return string « 1 » ou « 0 ».
 */
function assainir( $valeur ): string {
	if ( ! is_scalar( $valeur ) ) {
		return REVEILLE;
	}

	return ENDORMI === (string) $valeur ? ENDORMI : REVEILLE;
}

/**
 * Dit si la personne connectée a le droit d'endormir ou de réveiller CE contenu-là.
 *
 * La signature est celle que le cœur impose à un « auth_callback » ; seuls le droit et l'objet nous
 * intéressent ici.
 *
 * @param bool   $autorise    Verdict proposé par le cœur, ignoré : il vaut faux sur une clé protégée.
 * @param string $cle         Clé de la métadonnée.
 * @param int    $object_id   Identifiant du contenu visé.
 *
 * @return bool Vrai si la personne connectée peut modifier ce contenu.
 */
function peut_modifier( $autorise, $cle, $object_id ): bool {
	unset( $autorise, $cle );

	return current_user_can( 'edit_post', (int) $object_id );
}
