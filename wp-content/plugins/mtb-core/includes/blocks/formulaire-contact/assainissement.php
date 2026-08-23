<?php
/**
 * Composant « Formulaire de contact » — assainissement des valeurs reçues et de la mention.
 *
 * Fichier inclus UNE SEULE FOIS, par « bootstrap.php ».
 *
 * QUATRIÈME COPIE DE L'ASSAINISSEUR DE RECOPIE, ET LA VARIANTE EST NOMMÉE (dette T-#22-c).
 * La sémantique reprise est celle de « includes/query/coordonnees/option.php » — contrôle
 * d'encodage par « wp_check_invalid_utf8() » puis SUPPRESSION des caractères de contrôle — et non
 * celle de « includes/content/chien/assainissement.php », qui les REMPLACE par une espace et
 * n'appelle pas « wp_check_invalid_utf8() ». Trois définitions de « valeur propre » coexistent déjà
 * dans ce dépôt ; une quatrième qui DIVERGE serait pire qu'une quatrième identique.
 *
 * POURQUOI PAS « sanitize_text_field() » (décision 20). Cette fonction, « wp_strip_all_tags() »,
 * « wp_kses() » et « sanitize_email() » passent toutes par « strip_tags() », qui VIDE EN SILENCE
 * tout ce qui suit un « < ». Un nom de famille écrit « <Marie » ou un message qui commence par
 * « <3 » disparaîtrait sans un mot, et la décision 45 dit qu'un courriel perdu est perdu.
 * C'est sûr parce que rien n'est stocké, que la sortie est échappée SANS EXCEPTION et que le
 * courriel part en texte brut, où il n'y a rien à interpréter.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Blocks\FormulaireContact;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Recopie une valeur en la rendant sûre à manipuler, sans jamais en changer le sens.
 *
 * Les caractères de contrôle sont SUPPRIMÉS, jamais remplacés : une suppression ne fabrique pas de
 * caractère qui n'était pas là. Les fins de ligne sont normalisées en « \n », ou converties en
 * espace pour une valeur qui tient sur une ligne — c'est cette conversion, faite AVANT tout autre
 * traitement, qui rend l'injection d'en-tête de courriel impossible par construction.
 *
 * @param mixed $valeur     Valeur brute, telle qu'elle sort de la requête.
 * @param bool  $multiligne Vrai pour conserver les retours à la ligne.
 *
 * @return string Valeur recopiée, chaîne vide si la valeur reçue n'était pas un scalaire.
 */
function nettoyer_recopie( $valeur, bool $multiligne ): string {
	// Un champ posté deux fois arrive en tableau : ce n'est pas une saisie, cela vaut vide.
	if ( ! is_scalar( $valeur ) ) {
		return '';
	}

	// Rend la suite sûre : une entrée mal encodée ressort vide d'ici plutôt que tronquée au hasard.
	$texte = wp_check_invalid_utf8( (string) $valeur );

	if ( $multiligne ) {
		$texte = str_replace( array( "\r\n", "\r" ), "\n", $texte );
	} else {
		$texte = str_replace( array( "\r\n", "\r", "\n" ), ' ', $texte );
	}

	/*
	 * Comparaison octet par octet volontaire : en UTF-8, aucun octet de continuation ne descend
	 * sous 0x80, la classe ne peut donc pas mordre sur un caractère accentué. « \x0A » n'y figure
	 * pas — les retours à la ligne viennent d'être traités juste au-dessus, selon le cas.
	 */
	$nettoye = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $texte );

	if ( ! is_string( $nettoye ) ) {
		return '';
	}

	return trim( $nettoye );
}

/**
 * Assainit une valeur qui tient sur une seule ligne.
 *
 * @param mixed $valeur Valeur brute.
 *
 * @return string Valeur recopiée, sans CR ni LF.
 */
function assainir_ligne( $valeur ): string {
	return nettoyer_recopie( $valeur, false );
}

/**
 * Assainit une valeur qui peut tenir sur plusieurs lignes.
 *
 * @param mixed $valeur Valeur brute.
 *
 * @return string Valeur recopiée, retours à la ligne conservés et normalisés en « \n ».
 */
function assainir_multiligne( $valeur ): string {
	return nettoyer_recopie( $valeur, true );
}

/**
 * Lit une valeur de « $_POST » et la recopie proprement.
 *
 * « wp_unslash() » EST APPELÉ SUR CHAQUE LECTURE, AVANT TOUT AUTRE TRAITEMENT. PHP et WordPress
 * livrent « $_POST » échappé par des barres obliques inverses ; sans ce déséchappement, une
 * apostrophe dans un nom français — « L'Hermitage », « d'Entrecasteaux » — arriverait « \' » dans
 * le courriel et dans le champ réaffiché.
 *
 * @param string $cle        Nom du champ posté.
 * @param bool   $multiligne Vrai pour conserver les retours à la ligne.
 *
 * @return string Valeur recopiée, chaîne vide si le champ est absent.
 */
function lire_champ_poste( string $cle, bool $multiligne ): string {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Formulaire public anonyme : la vérification est un jeton HMAC horodaté, motivée au contrat #22 §5.2. La valeur lue ici est assainie immédiatement et n'est jamais écrite en base.
	if ( ! isset( $_POST[ $cle ] ) ) {
		return '';
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Assainissement fait par nettoyer_recopie() : les fonctions sanitize_* du cœur passent par strip_tags() et videraient une valeur commençant par « < » (décision 20).
	return nettoyer_recopie( wp_unslash( $_POST[ $cle ] ), $multiligne );
}

/**
 * Dit si la mention d'information ne porte aucun texte affichable.
 *
 * Recopie volontaire de « blocks/bandeau-alerte/rendu.php » : un champ de texte enrichi paraît
 * rempli sans l'être — un « <br> » seul, une entité « &nbsp; », une espace insécable U+00A0, ou
 * l'espace fine insécable U+202F que le clavier français pose devant un « ! ». Un « trim() » n'en
 * verrait aucun, et le composant s'afficherait avec une mention vide alors que le contrat exige
 * qu'il disparaisse.
 *
 * « wp_strip_all_tags() » est proscrit sur une valeur recopiée (décision 20), mais ici il n'opère
 * que sur une copie jetée dans l'expression même : la valeur émise en sortie reste l'originale.
 *
 * Le drapeau « u » est obligatoire — sans lui, « \p{Z} » et « \p{C} » ne sont pas interprétés comme
 * des propriétés Unicode et les espaces insécables survivraient au nettoyage.
 *
 * @param string $valeur Mention telle qu'elle est enregistrée dans le contenu de la page.
 *
 * @return bool Vrai s'il n'y a rien à afficher.
 */
function mention_est_vide( string $valeur ): bool {
	$resultat = preg_replace(
		'/[\p{Z}\p{C}\s]+/u',
		'',
		html_entity_decode( wp_strip_all_tags( $valeur ), ENT_QUOTES | ENT_HTML5, 'UTF-8' )
	);

	// preg_replace rend null sur de l'UTF-8 cassé : une mention illisible n'est pas une mention.
	if ( null === $resultat ) {
		return true;
	}

	return '' === $resultat;
}

/**
 * Liste blanche des balises admises dans la mention, en sortie.
 *
 * Recopiée de « blocks/bandeau-alerte/rendu.php », et volontairement plus étroite que
 * « wp_kses_post() » : cette dernière admet « img », « table », « h2 », « ul » ou « figure ». Un
 * collage ferait donc survivre une image ou un tableau au milieu d'un formulaire, c'est-à-dire une
 * décision visuelle prise par un collage. Et « h2 » porterait un second filet vertical à
 * l'intérieur du bloc, que le système de design interdit.
 *
 * Sur « a », les cinq attributs sont exactement ceux que le format de lien du cœur écrit :
 * « rel="noreferrer noopener" » quand elle coche « ouvrir dans un nouvel onglet », « data-type » et
 * « data-id » quand elle choisit une page du site. « id » est exclu : un identifiant collé créerait
 * un doublon dans un document qui en porte déjà neuf, contractuels.
 *
 * @return array<string, array<string, bool>> Balises et attributs admis, au format wp_kses().
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
 * Liste blanche des protocoles admis dans un lien de la mention.
 *
 * Explicite plutôt que « wp_allowed_protocols() », qui en compte une vingtaine dont « ftp »,
 * « irc », « svn » et « feed ». Une mention pointe vers une page du site — adresse relative, aucun
 * protocole à valider —, vers un courriel ou vers un téléphone.
 *
 * @return array<int, string> Protocoles admis, au format wp_kses().
 */
function protocoles_admis(): array {
	return array( 'http', 'https', 'mailto', 'tel' );
}
