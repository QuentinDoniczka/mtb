<?php
/**
 * Conversion des marqueurs de capture en HTML publiable.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\PorteesChiens;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * POURQUOI CE FICHIER EXISTE — ET POURQUOI CE N'EST PAS UNE DÉCISION DE CONTENU
 *
 * « [LIEN href=…] », « [IMAGE src=…] » et « [IFRAME src=…] » sont la NOTATION de la convention de
 * capture, pas le texte du site source. Le site affichait un lien et une image ; il n'a jamais
 * affiché la chaîne « [IMAGE src=… ] ». Publier la notation reviendrait à reproduire l'outil au
 * lieu de la source — la même faute que publier un CRLF ou une ancre coupée en deux. Reproduire le
 * site, c'est convertir ces marqueurs ; les recopier serait les trahir.
 *
 * La transformation vit ICI, dans le code, et non dans les fichiers de données : ceux-ci restent
 * fidèles à la capture, donc confrontables verbatim à la source par le contrôle des extraits. Une
 * règle écrite une fois dans du code se relit et se conteste ; la même règle appliquée à la main
 * dans trente-quatre entités ne se relit plus.
 *
 * ELLE NE S'APPLIQUE QU'À « texte_libre », JAMAIS À UNE VALEUR DE CHAMP. Un résultat de santé, un
 * numéro LOF, un nom d'élevage ne portent aucun marqueur et ne traversent aucune transformation :
 * la recopie littérale y reste la règle absolue.
 *
 * POURQUOI L'IMAGE EST RETIRÉE ET NON RESTAURÉE — deux raisons cumulées, et la seconde est
 * bloquante :
 *
 *   1. la photographie est DÉJÀ rattachée à l'entité. Les identifiants IONOS cités par « galerie »
 *      et « photo » sont versés en médiathèque puis rattachés par « _mtb_galerie » et
 *      « _thumbnail_id ». Restaurer la balise afficherait la même image deux fois.
 *   2. surtout, un « <img src="https://www.mtbrabant.com/…"> » ferait partir une requête du
 *      NAVIGATEUR DU VISITEUR vers un domaine tiers. C'est D6 — zéro requête vers un domaine
 *      tiers, zéro traceur, donc aucun bandeau de consentement — et D6 est bloquant. Le domaine
 *      sera de surcroît résilié : l'image serait cassée à brève échéance.
 *
 * UN ANCRAGE N'EST PAS UNE REQUÊTE. Un « <a href> » n'appelle rien tant que le visiteur ne le
 * clique pas : les liens sortants du texte sont donc conservés. C'est la ligne exacte qui sépare la
 * règle 2 des règles 3 et 4.
 *
 * AUCUN MOT DE L'ÉLEVEUSE N'EST TOUCHÉ. Seuls disparaissent des marqueurs et les URL d'images
 * qu'ils portent. Le texte rédactionnel traverse intact.
 */

/**
 * Espaces acceptées entre deux marqueurs, insécable comprise.
 *
 * L'insécable est écrite en octets (« \xC2\xA0 ») et non en caractère : les expressions de ce
 * fichier travaillent SANS le modificateur « u », faute de quoi un octet mal encodé ferait rendre
 * null à preg_replace() et VIDERAIT le texte — précisément la perte que tout ce module existe pour
 * empêcher. En UTF-8, aucun octet de continuation ne descend sous 0x80 : ces classes ne peuvent
 * donc pas mordre sur un caractère accentué.
 */
const ESPACES = '(?:[ \t\r\n]|\xC2\xA0)';

/**
 * Convertit les marqueurs de capture d'un texte libre en HTML publiable.
 *
 * L'ordre des règles porte le sens et n'est pas permutable : le motif du lien d'agrandissement doit
 * être reconnu AVANT que la règle 2 ne retire les images, sans quoi il ne resterait qu'une ancre
 * vide pointant une image de l'ancien domaine.
 *
 * @param string $texte Texte libre, tel qu'il est transcrit.
 *
 * @return array<string, mixed> array{ texte, galerie, images, liens, nues, cadres, orphelins, residus }.
 */
function convertir_les_marqueurs( string $texte ): array {
	$compte = array(
		'galerie'   => 0,
		'images'    => 0,
		'liens'     => 0,
		'nues'      => 0,
		'cadres'    => 0,
		'orphelins' => 0,
		'residus'   => 0,
	);

	if ( '' === $texte ) {
		return array_merge( array( 'texte' => '' ), $compte );
	}

	$texte = retirer_les_liens_dagrandissement( $texte, $compte );
	$texte = retirer_les_images( $texte, $compte );
	$texte = convertir_les_liens( $texte, $compte );
	$texte = convertir_les_cadres( $texte, $compte );
	$texte = retirer_les_fermetures_orphelines( $texte, $compte );
	$texte = resserrer_les_lignes_vides( $texte );

	$compte['residus'] = compter_les_residus( $texte );

	return array_merge( array( 'texte' => trim( $texte ) ), $compte );
}

/**
 * Règle 1 — supprime le lien d'agrandissement de galerie, ancre comprise.
 *
 * Motif du gabarit IONOS : une ancre qui ne contient QU'UNE image, éventuellement entourée
 * d'espaces. Elle n'apporte rien ici — la photographie est déjà dans la galerie de l'entité, et
 * l'ancre pointait une seconde copie de la même image sur l'ancien domaine.
 *
 * Cette règle passe la première : appliquée après la règle 2, elle ne trouverait plus qu'une ancre
 * vide et laisserait un lien invisible vers un domaine tiers.
 *
 * @param string             $texte  Texte.
 * @param array<string, int> $compte Compteurs, complétés.
 *
 * @return string Texte.
 */
function retirer_les_liens_dagrandissement( string $texte, array &$compte ): string {
	$motif = '/\[LIEN' . ESPACES . '+href=[^\]]*\]' . ESPACES . '*(?:\[IMAGE' . ESPACES . '[^\]]*\]' . ESPACES . '*)+\[\/LIEN\]/';

	return remplacer( $texte, $motif, '', 'galerie', $compte );
}

/**
 * Règle 2 — supprime toute image restante.
 *
 * @param string             $texte  Texte.
 * @param array<string, int> $compte Compteurs, complétés.
 *
 * @return string Texte.
 */
function retirer_les_images( string $texte, array &$compte ): string {
	return remplacer( $texte, '/\[IMAGE' . ESPACES . '[^\]]*\]/', '', 'images', $compte );
}

/**
 * Règle 3 — convertit un lien en ancre HTML.
 *
 * Ancre vide : le site source en porte au moins une, réduite à une seule espace. Rendue telle
 * quelle, elle donnerait un lien invisible et incliquable — un contenu perdu pour le visiteur. On
 * rend alors l'URL en texte visible : le lien reste utilisable, et rien n'est inventé.
 *
 * @param string             $texte  Texte.
 * @param array<string, int> $compte Compteurs, complétés.
 *
 * @return string Texte.
 */
function convertir_les_liens( string $texte, array &$compte ): string {
	$converti = preg_replace_callback(
		'/\[LIEN' . ESPACES . '+href=([^\]]*)\](.*?)\[\/LIEN\]/s',
		static function ( array $trouve ) use ( &$compte ): string {
			$url     = trim( $trouve[1] );
			$visible = trim( str_replace( "\xC2\xA0", ' ', $trouve[2] ) );

			++$compte['liens'];

			if ( '' === $visible ) {
				++$compte['nues'];

				$visible = $url;
			}

			return '<a href="' . esc_url( $url ) . '">' . esc_html( $visible ) . '</a>';
		},
		$texte
	);

	return is_string( $converti ) ? $converti : $texte;
}

/**
 * Règle 4 — convertit un cadre incorporé en lien cliquable.
 *
 * Les deux vidéos du site deviennent des liens, jamais des « iframe ». Un « iframe » YouTube ferait
 * partir une requête du navigateur du visiteur vers un domaine tiers, ce que D6 interdit ; un lien
 * ne part que si le visiteur le décide. La vidéo cesse d'être perdue sans que D6 tombe.
 *
 * @param string             $texte  Texte.
 * @param array<string, int> $compte Compteurs, complétés.
 *
 * @return string Texte.
 */
function convertir_les_cadres( string $texte, array &$compte ): string {
	$converti = preg_replace_callback(
		'/\[IFRAME' . ESPACES . '+src=([^\]]*)\]/',
		static function ( array $trouve ) use ( &$compte ): string {
			$url = trim( $trouve[1] );

			++$compte['cadres'];

			return '<a href="' . esc_url( $url ) . '">' . esc_html( $url ) . '</a>';
		},
		$texte
	);

	return is_string( $converti ) ? $converti : $texte;
}

/**
 * Règle 5a — supprime une fermeture de lien restée seule.
 *
 * @param string             $texte  Texte.
 * @param array<string, int> $compte Compteurs, complétés.
 *
 * @return string Texte.
 */
function retirer_les_fermetures_orphelines( string $texte, array &$compte ): string {
	return remplacer( $texte, '/\[\/LIEN\]/', '', 'orphelins', $compte );
}

/**
 * Règle 5b — ramène à une seule les lignes vides laissées par les suppressions.
 *
 * Une ligne « vide » du site source contient souvent une espace ou une insécable : elle est traitée
 * comme vide, sinon les trous laissés par les images retirées resteraient béants.
 *
 * @param string $texte Texte.
 *
 * @return string Texte.
 */
function resserrer_les_lignes_vides( string $texte ): string {
	$blancs   = '(?:[ \t\r]|\xC2\xA0)*';
	$resserre = preg_replace( '/\n' . $blancs . '(?:\n' . $blancs . '){2,}/', "\n\n", $texte );

	return is_string( $resserre ) ? $resserre : $texte;
}

/**
 * Compte ce qui, après conversion, déclencherait encore une requête ou trahirait la notation.
 *
 * Filet de sûreté, et non contrôle décoratif : si ce compte n'est pas nul, une balise capable
 * d'appeler un domaine tiers a survécu, ou un marqueur de capture s'apprête à s'afficher en toutes
 * lettres au visiteur. Les deux sont des défauts nommés, jamais des silences.
 *
 * @param string $texte Texte converti.
 *
 * @return int Nombre de résidus.
 */
function compter_les_residus( string $texte ): int {
	$traces    = array( '<img', '<iframe', '<script', '<embed', '<object', '<video', '<audio', '<source', '<link', '[LIEN', '[IMAGE', '[IFRAME', '[/LIEN' );
	$minuscule = strtolower( $texte );
	$residus   = 0;

	foreach ( $traces as $trace ) {
		$residus += substr_count( $minuscule, strtolower( $trace ) );
	}

	return $residus;
}

/**
 * Applique un motif et compte les remplacements réellement faits.
 *
 * @param string             $texte  Texte.
 * @param string             $motif  Expression rationnelle.
 * @param string             $par    Remplacement.
 * @param string             $cle    Compteur à incrémenter.
 * @param array<string, int> $compte Compteurs, complétés.
 *
 * @return string Texte, inchangé si l'expression a échoué.
 */
function remplacer( string $texte, string $motif, string $par, string $cle, array &$compte ): string {
	$faits    = 0;
	$remplace = preg_replace( $motif, $par, $texte, -1, $faits );

	if ( ! is_string( $remplace ) ) {
		return $texte;
	}

	$compte[ $cle ] += (int) $faits;

	return $remplace;
}
