<?php
/**
 * Normalisation d'un chemin demandé en clé de la carte des 52.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\Redirections301;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ramène une adresse ou un chemin demandé à la forme des clés de la carte.
 *
 * AUCUNE FONCTION D'ASSAINISSEMENT DE TEXTE N'EST APPLIQUÉE ICI, et c'est délibéré :
 * « sanitize_text_field() » ou « sanitize_title() » détruiraient le chemin — le premier mange les
 * balises et les octets de contrôle, le second remplacerait « é » par « e » et ferait échouer la
 * recherche de clé précisément sur les 30 adresses accentuées. L'assainissement, ici, c'est le
 * décodage, le rejet dur de l'octet nul, et surtout le fait que la valeur produite ne serve QU'À
 * une lecture de clé dans un tableau constant : jamais à une requête, jamais à une sortie.
 *
 * LA COMPARAISON PORTE SUR LE CHEMIN SEUL, JAMAIS SUR L'HÔTE. C'est ce qui rend le module
 * indifférent au domaine servi, donc utile même si « mtbrabant.com » n'est jamais pointé sur nous
 * (BRIEF §15.4, question ouverte).
 *
 * « rawurldecode() » couvre d'un seul geste les deux formes qu'un navigateur peut envoyer : la
 * forme percent-encodée « /bhpl/port%C3%A9e-m-2016/ » et la forme UTF-8 brute
 * « /bhpl/portée-m-2016/ ». Aucune des deux n'a besoin de code dédié.
 *
 * @param string $adresse Adresse complète ou chemin brut, tel que reçu.
 *
 * @return string Chemin normalisé, barres initiale et finale garanties ; chaîne vide si le chemin
 *                est inexploitable — rejet dur, aucune redirection ne peut en découler.
 */
function normaliser_chemin( string $adresse ): string {
	if ( '' === $adresse ) {
		return '';
	}

	// Coupe la chaîne de requête et le fragment d'un seul geste, sans expression régulière maison.
	$chemin = wp_parse_url( $adresse, PHP_URL_PATH );

	if ( ! is_string( $chemin ) || '' === $chemin ) {
		return '';
	}

	$chemin = rawurldecode( $chemin );

	/*
	 * Rejet dur, jamais un nettoyage : un octet nul dans un chemin n'est pas une faute de frappe
	 * d'un visiteur, c'est une tentative de troncature. On ne devine pas ce qu'elle voulait dire.
	 */
	if ( false !== strpos( $chemin, "\0" ) ) {
		return '';
	}

	$prefixe = prefixe_du_site();

	if ( '' !== $prefixe ) {
		if ( $chemin === $prefixe ) {
			$chemin = '/';
		} elseif ( 0 === strpos( $chemin, $prefixe . '/' ) ) {
			$chemin = substr( $chemin, strlen( $prefixe ) );
		}
	}

	$chemin = '/' . ltrim( $chemin, '/' );

	if ( '/' !== substr( $chemin, -1 ) ) {
		$chemin .= '/';
	}

	return $chemin;
}

/**
 * Préfixe de chemin du site, pour une installation en sous-dossier.
 *
 * Inerte en Docker, où le site est servi à la racine et où cette fonction rend la chaîne vide.
 * Indispensable sur un mutualisé où WordPress vivrait dans « /elevage/ » : sans ce retrait, la
 * requête « /elevage/bhpl/portée-m-2016/ » ne retrouverait jamais sa clé, et les 46 redirections
 * seraient inertes SANS QU'AUCUN TÉMOIN NE SE DÉCLENCHE. La commande de vérification imprime cette
 * valeur pour cette raison précise.
 *
 * @return string Préfixe sans barre finale, chaîne vide quand le site est à la racine.
 */
function prefixe_du_site(): string {
	$prefixe = wp_parse_url( home_url( '/' ), PHP_URL_PATH );

	if ( ! is_string( $prefixe ) ) {
		return '';
	}

	return rtrim( rawurldecode( $prefixe ), '/' );
}

/**
 * Hôte d'une adresse, en minuscules.
 *
 * @param string $adresse Adresse à lire.
 *
 * @return string Hôte, chaîne vide si l'adresse n'en porte pas.
 */
function hote_de( string $adresse ): string {
	$hote = wp_parse_url( $adresse, PHP_URL_HOST );

	return is_string( $hote ) ? strtolower( $hote ) : '';
}
