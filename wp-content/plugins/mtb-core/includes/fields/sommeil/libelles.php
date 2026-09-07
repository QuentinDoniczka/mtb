<?php
/**
 * Les quatre phrases de l'interrupteur « en sommeil », et la reconnaissance de la page d'accueil.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Fields\Sommeil;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * SOURCE UNIQUE DES CHAÎNES, POUR QUE LES TROIS ÉCRANS NE PUISSENT PAS DIVERGER.
 *
 * L'interrupteur paraît à trois endroits : l'encadré « Publier » de l'éditeur classique (portée,
 * fiche de chien), la zone latérale de l'éditeur de blocs (page), et la mention dans les listes
 * d'administration. Les trois lisent CES fonctions ; aucun ne recopie une phrase. Le JavaScript de
 * l'éditeur de blocs les REÇOIT DU SERVEUR et les imprime — il n'en compose aucune, il n'en concatène
 * aucune. Une phrase recopiée dans un quatrième endroit divergerait au premier ajustement de
 * rédaction, et l'éleveuse lirait deux promesses différentes pour un seul interrupteur.
 *
 * Les textes sont GELÉS AU §7 DU CONTRAT #52, au caractère près, et vérifiés contre MASTER.md §10.4 :
 * aucun mot interdit, pas d'emoji, pas de pastille de couleur, et surtout NI « masqué » NI « caché »
 * — deux mots qui promettraient que la page ne s'ouvre plus, alors que la promesse centrale est
 * l'inverse : son adresse continue de l'ouvrir en 200. « En sommeil » est LE MOT DE L'ÉLEVEUSE
 * elle-même ; il se garde. MASTER.md §10.1 impose le verbe à l'infinitif pour une action, d'où le
 * partage entre l'ACTION — « Mettre ce contenu en sommeil » — et l'ÉTAT — « En sommeil ».
 */

/**
 * Libellé de la case à cocher. C'est une action, donc un verbe à l'infinitif.
 *
 * @return string
 */
function libelle_case(): string {
	return 'Mettre ce contenu en sommeil';
}

/**
 * Phrase d'aide affichée sous la case, sur les deux écrans.
 *
 * Elle dit les quatre effets et le non-effet, dans cet ordre, parce que c'est l'ordre des questions
 * qu'on se pose : est-ce que ça reste en ligne, qu'est-ce que ça retire, et qu'est-ce que ça ne
 * retire pas. La dernière phrase est la réponse au doute le plus coûteux — le sommeil ne retire rien
 * des listes du site.
 *
 * @return string
 */
function aide(): string {
	return 'Le contenu reste en ligne : son adresse continue de l’ouvrir normalement. Il n’est plus proposé par les moteurs de recherche, ne figure plus dans le plan du site et ne remonte plus dans la recherche de votre site. Il reste affiché dans vos pages et dans vos menus.';
}

/**
 * Mention affichée à côté du titre dans les listes d'administration. C'est un état, pas une action.
 *
 * @return string
 */
function libelle_etat(): string {
	return 'En sommeil';
}

/**
 * Avertissement affiché sur la seule page réglée comme page d'accueil du site.
 *
 * L'interrupteur n'est PAS retiré de la page d'accueil : l'éleveuse seule décide de ce qu'elle
 * archive, et le lui retirer serait décider à sa place. Mais rien n'est silencieux. Cette phrase est
 * factuelle et sans dramatisation : elle dit ce que le geste fait, elle ne dit pas de ne pas le
 * faire.
 *
 * @return string
 */
function avertissement_accueil(): string {
	return 'Cette page est la page d’accueil de votre site : la mettre en sommeil retire l’adresse principale du site des moteurs de recherche.';
}

/**
 * Dit si un contenu est la page réglée comme page d'accueil du site.
 *
 * DÉTECTION PAR LE SEUL COUPLE D'OPTIONS QUI DÉCIDE DE LA PAGE D'ACCUEIL, JAMAIS PAR LECTURE DE
 * PERMALIEN NI PAR COMPARAISON D'URL. Comparer « get_permalink( $id ) » à « home_url( '/' ) » se
 * trompe dès qu'un site est servi dans un sous-dossier, dès qu'un module de langue préfixe les
 * adresses, et dès que la barre finale diffère d'un côté ou de l'autre. Le couple ci-dessous EST la
 * définition : « show_on_front » dit si l'accueil est une page, « page_on_front » dit laquelle. Quand
 * l'accueil affiche les derniers articles, « show_on_front » vaut « posts » et aucune page n'est
 * l'accueil — l'avertissement ne paraît alors nulle part, ce qui est juste.
 *
 * @param int $id Identifiant du contenu.
 *
 * @return bool Vrai seulement si ce contenu est la page d'accueil réglée du site.
 */
function est_la_page_d_accueil( int $id ): bool {
	if ( 'page' !== (string) get_option( 'show_on_front' ) ) {
		return false;
	}

	if ( $id <= 0 ) {
		return false;
	}

	return $id === (int) get_option( 'page_on_front' );
}
