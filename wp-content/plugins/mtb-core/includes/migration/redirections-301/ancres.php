<?php
/**
 * Réparation des liens internes pointant encore vers l'ancien domaine, au rendu.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\Redirections301;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * CE N'EST PAS UN SECOND MÉCANISME : c'est le même, appliqué à un second point d'application. La
 * carte est la MÊME, la normalisation est la MÊME fonction, la résolution d'identité est la MÊME.
 *
 * POURQUOI IL EXISTE. Les douze « href » repris de l'ancien site sont ABSOLUS vers
 * « https://www.mtbrabant.com/… ». Une 301 posée sur notre WordPress ne les rattrape que si notre
 * serveur répond pour ce domaine — et la propriété du domaine est une question ouverte (BRIEF
 * §15.4). Le filtre les répare dans tous les cas, y compris si « mtbrabant.com » n'est jamais
 * pointé sur nous.
 *
 * POURQUOI PAS UNE RÉÉCRITURE DES DONNÉES À LA SOURCE. Hors empreinte, et surtout INEFFICACE :
 * « migration/resultats-pages/pages.php:17-25 » établit que l'import est en création seule
 * (« Trouvée : comptée "déjà présente", et AUCUNE écriture »). Une base déjà peuplée ne bougerait
 * pas d'un octet.
 *
 * ÉCART DÉCLARÉ — CONTENU STOCKÉ ≠ CONTENU SERVI. Le contenu stocké garde l'ancienne adresse ; le
 * contenu servi porte la nouvelle. D4 porte sur le texte, qui ne bouge pas — mais c'est un écart,
 * et un écart non écrit n'est imputable à personne. Conséquence concrète : si l'éleveuse ouvre la
 * fiche d'Etch dans l'éditeur, elle y verra encore
 * « https://www.mtbrabant.com/bhpl/portée-m-2016/ ». Rien ne l'invite à y toucher, et y toucher ne
 * casserait rien.
 *
 * LE TEXTE VISIBLE DE L'ANCRE N'EST JAMAIS TOUCHÉ : l'expression ne capture que la VALEUR de
 * l'attribut « href », jamais ce qui se trouve entre « <a …> » et « </a> ».
 */

/**
 * Remplace, au rendu, les liens vers l'ancien domaine par le permalien servi aujourd'hui.
 *
 * Le paramètre et le retour ne sont pas typés : un filtre tiers placé avant nous peut avoir rendu
 * autre chose qu'une chaîne, et « strict_types » en ferait une erreur fatale au milieu d'une fiche.
 *
 * @param mixed $contenu Contenu du champ principal, tel que les filtres précédents l'ont laissé.
 *
 * @return mixed Contenu, liens réparés ; la valeur reçue telle quelle si elle n'est pas une chaîne
 *               ou si elle ne porte pas l'ancien domaine.
 */
function reparer_les_ancres( $contenu ) {
	// Sortie immédiate : gratuite sur la centaine de contenus qui ne portent aucun lien ancien.
	if ( ! is_string( $contenu ) || false === strpos( $contenu, 'mtbrabant.com' ) ) {
		return $contenu;
	}

	$repare = preg_replace_callback(
		'#(<a\b[^>]*?\shref\s*=\s*)(["\'])(.*?)\2#is',
		__NAMESPACE__ . '\\reparer_une_ancre',
		$contenu
	);

	// Un dépassement de « pcre.backtrack_limit » rend null : on sert alors le contenu d'origine,
	// jamais une page vide.
	return is_string( $repare ) ? $repare : $contenu;
}

/**
 * Réécrit la valeur d'un attribut « href », ou laisse l'ancre intacte.
 *
 * @param array<int, string> $occurrence Groupes capturés : préfixe, guillemet, valeur du « href ».
 *
 * @return string Ancre réécrite, ou l'occurrence d'origine, octet pour octet.
 */
function reparer_une_ancre( array $occurrence ): string {
	$remplacement = adresse_de_remplacement( (string) ( $occurrence[3] ?? '' ) );

	if ( '' === $remplacement ) {
		return (string) $occurrence[0];
	}

	return (string) $occurrence[1] . (string) $occurrence[2] . esc_url( $remplacement ) . (string) $occurrence[2];
}

/**
 * Permalien à servir à la place d'une adresse de l'ancien site.
 *
 * Les deux schémas et les deux formes d'hôte sont couverts. La forme percent-encodée et la forme
 * UTF-8 brute retombent sur la même clé par « normaliser_chemin() », sans code dédié.
 *
 * CIBLE NON RÉSOLUE OU CHEMIN HORS CARTE ⇒ ON NE TOUCHE PAS AU LIEN. Un lien laissé tel quel reste
 * cliquable ; un lien réécrit vers un 404 ne l'est plus. Les entrées de verdict « identique » et
 * « identique_apres_creation » ne portent aucune identité de cible et sont donc, ici aussi,
 * laissées intactes.
 *
 * @param string $href Valeur de l'attribut « href », telle qu'elle est stockée.
 *
 * @return string Permalien de remplacement, chaîne vide quand le lien ne doit pas être touché.
 */
function adresse_de_remplacement( string $href ): string {
	if ( '' === $href || false === strpos( $href, 'mtbrabant.com' ) ) {
		return '';
	}

	$hote = hote_de( $href );

	if ( 'mtbrabant.com' !== $hote && 'www.mtbrabant.com' !== $hote ) {
		return '';
	}

	$chemin = normaliser_chemin( $href );

	if ( '' === $chemin ) {
		return '';
	}

	$carte = carte();

	if ( ! isset( $carte[ $chemin ] ) ) {
		$formes = formes_connues();

		if ( ! isset( $formes[ $chemin ] ) ) {
			return '';
		}

		$chemin = $formes[ $chemin ];
	}

	$entree = $carte[ $chemin ];

	if ( '301' !== $entree['verdict'] ) {
		return '';
	}

	$resolution = resoudre_cible( is_array( $entree['cible'] ) ? $entree['cible'] : array() );

	return is_string( $resolution['url'] ) ? $resolution['url'] : '';
}
