<?php
/**
 * Composant « Bandeau d'alerte » — fonctions d'aide du rendu public.
 *
 * Ce fichier est inclus UNE SEULE FOIS, par « bootstrap.php ». Il est le seul du module à déclarer
 * des fonctions : « render.php » est inclus par le cœur avec un « require » nu, donc une fois par
 * instance du bloc présente sur la page.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Blocks\BandeauAlerte;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dit si le message ne porte aucun texte affichable.
 *
 * Un champ de texte enrichi paraît rempli sans l'être : un « <br> » seul, une entité « &nbsp; »,
 * une espace insécable U+00A0, l'espace fine insécable U+202F que le clavier français pose devant
 * un « ! », ou un lien dont le libellé a été effacé. Un « trim() » n'en verrait aucun, et le
 * visiteur recevrait un encart vide.
 *
 * « wp_strip_all_tags » est proscrit sur une valeur recopiée (décision 20 de docs/ETAT.md) parce
 * qu'il viderait en silence une valeur commençant par « < ». Ici il n'opère que sur une copie jetée
 * dans l'expression même : la valeur émise en sortie reste l'originale, intacte.
 *
 * Le drapeau « u » est obligatoire — sans lui, « \p{Z} » et « \p{C} » ne sont pas interprétés comme
 * des propriétés Unicode et les espaces insécables survivraient au nettoyage.
 *
 * @param string $valeur Message tel qu'il est enregistré.
 *
 * @return bool Vrai s'il n'y a rien à afficher.
 */
function est_vide( string $valeur ): bool {
	$resultat = preg_replace(
		'/[\p{Z}\p{C}\s]+/u',
		'',
		html_entity_decode( wp_strip_all_tags( $valeur ), ENT_QUOTES | ENT_HTML5, 'UTF-8' )
	);

	/*
	 * preg_replace rend null quand la chaîne n'est pas de l'UTF-8 valide. Une chaîne dont
	 * l'encodage est cassé n'est pas un message affichable : elle vaut vide.
	 */
	if ( null === $resultat ) {
		return true;
	}

	return '' === $resultat;
}

/**
 * Liste blanche des balises admises dans le message, en sortie.
 *
 * Plus étroite que « wp_kses_post() », et c'est le point : cette dernière admet « img », « table »,
 * « h2 », « ul » ou « figure ». Un collage ferait donc survivre une image ou un tableau dans un
 * encart d'alerte — une décision visuelle prise par un collage, alors que le composant ne propose
 * aucun réglage d'apparence. Et « h2 », « hr » ou « blockquote » porteraient un second filet
 * vertical à l'intérieur du bloc, que le système de design interdit.
 *
 * Sur « a », les cinq attributs sont exactement ceux que le format de lien du cœur écrit :
 * « rel="noreferrer noopener" » quand elle coche « ouvrir dans un nouvel onglet », « data-type » et
 * « data-id » quand elle choisit une page du site. Inertes, mais c'est ce qu'elle a produit : les
 * retirer serait normaliser sans raison. « id », « class » et « title » sont exclus — le cœur ne
 * les produit pas ici, et un « id » collé créerait un doublon d'identifiant dans le document.
 *
 * « br » est la conséquence directe du comportement de la touche Entrée dans le champ : sans lui,
 * un message écrit sur deux lignes perdrait sa coupure en silence.
 *
 * @return array<string, array<string, bool>> Balises et attributs admis, au format attendu par
 *                                            wp_kses().
 */
function balises_admises(): array {
	return array(
		'a'  => array(
			'href'      => true,
			'target'    => true,
			'rel'       => true,
			'data-type' => true,
			'data-id'   => true,
		),
		'br' => array(),
	);
}

/**
 * Liste blanche des protocoles admis dans un lien du message.
 *
 * Explicite plutôt que « wp_allowed_protocols() », qui en compte une vingtaine dont « ftp », « irc »,
 * « svn » et « feed ». Une alerte pointe vers une page du site — adresse relative, aucun protocole à
 * valider —, vers un courriel ou vers un téléphone.
 *
 * @return array<int, string> Protocoles admis, au format attendu par wp_kses().
 */
function protocoles_admis(): array {
	return array( 'http', 'https', 'mailto', 'tel' );
}
