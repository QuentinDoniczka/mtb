<?php
/**
 * Composant « Formulaire de contact » — le jeton horodaté qui remplace le nonce.
 *
 * Fichier inclus UNE SEULE FOIS, par « bootstrap.php ».
 *
 * ÉCART ASSUMÉ À « nonces sur toute écriture » DE CLAUDE.md, écrit au contrat #22 §5.2 et non fait
 * en douce. Quatre raisons, dans l'ordre de poids :
 *
 * 1. Un formulaire public anonyme n'a AUCUNE session à protéger et N'ÉCRIT RIEN EN BASE : il n'y a
 *    pas de CSRF à empêcher — un attaquant peut de toute façon poster directement.
 * 2. Le nonce ne sait pas exprimer un ÂGE MINIMAL, exigence explicite du brief §9 (« délai minimal
 *    de soumission »).
 * 3. Derrière un cache de page, un nonce périmé rend « Êtes-vous sûr de vouloir faire cela ? » —
 *    un message de sécurité incompréhensible pour le public du brief §2.
 * 4. Le jeton, lui, SE RÉCUPÈRE : un jeton invalide ou expiré ne vide JAMAIS les champs, le
 *    formulaire se réaffiche complet avec un jeton neuf et une phrase française.
 *
 * DEUX LIMITES, ÉCRITES ET NON CACHÉES :
 *
 * - Le jeton est REJOUABLE DANS SON HEURE. Il prouve « un formulaire a été servi à l'instant t »,
 *   pas « ceci est une première soumission ». Le rendre à usage unique exigerait de mémoriser les
 *   jetons consommés, donc d'écrire en base — refusé par la décision 45.
 * - AUCUNE LIMITATION DE DÉBIT n'est livrée, même motif.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Blocks\FormulaireContact;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Âge minimal admis, en secondes. En deçà, personne n'a lu trois champs et tapé un message. */
const JETON_AGE_MINIMAL = 3;

/** Âge maximal admis, en secondes. Au-delà, l'onglet est resté ouvert plus d'une heure. */
const JETON_AGE_MAXIMAL = 3600;

/**
 * Secret de signature, dérivé des sels du site.
 *
 * « wp_salt() » avec un schéma qui ne fait pas partie des quatre du cœur retombe sur l'option de
 * site « secret_key », qu'il génère à la première demande. La valeur est donc stable dans la vie
 * de l'installation et propre à elle : un jeton forgé sur un autre site ne vaut rien ici.
 *
 * AUCUNE GARDE « function_exists() » ICI, et c'est délibéré : « wp_salt() » vit dans
 * « pluggable.php », que le cœur charge APRÈS les extensions et AVANT le premier hook. Elle est
 * donc toujours définie aux deux seuls moments où ce module l'appelle — « template_redirect » et le
 * rendu du bloc. Une garde qui ne garde rien serait du code mort qui ment (décision 46). Le repli
 * qui compte, lui, est plus bas : un secret non-chaîne ou vide fait ÉCHOUER FERMÉE la vérification.
 *
 * @return string Secret, ou chaîne vide si le cœur n'a rien à donner.
 */
function jeton_secret(): string {
	$secret = wp_salt( 'mtb-contact' );

	return is_string( $secret ) ? $secret : '';
}

/**
 * Frappe un jeton neuf, à l'instant où le formulaire est rendu.
 *
 * Forme : « <horodatage>.<signature HMAC de l'horodatage> ». L'horodatage est en clair — il n'a
 * rien de secret — et la signature interdit de le reculer pour contourner le délai minimal.
 *
 * Un jeton neuf est émis à CHAQUE réaffichage du formulaire : une visiteuse qui corrige une faute
 * de frappe repart d'une horloge fraîche et ne peut pas se faire refuser deux fois pour un âge.
 *
 * @return string Jeton, ou chaîne vide si le secret est indisponible.
 */
function jeton_creer(): string {
	$secret = jeton_secret();

	if ( '' === $secret ) {
		return '';
	}

	$horodatage = (string) time();

	return $horodatage . '.' . hash_hmac( 'sha256', $horodatage, $secret );
}

/**
 * Vérifie un jeton reçu, dans l'ordre : forme, signature, puis âge.
 *
 * LA SIGNATURE EST COMPARÉE PAR « hash_equals() », à temps constant : une comparaison « === »
 * s'arrête au premier octet différent et laisse mesurer, requête après requête, combien d'octets
 * de tête sont justes. C'est la seule façon connue de reconstruire une signature sans le secret.
 *
 * ÉCHOUE FERMÉE : secret indisponible, forme inattendue, horodatage non numérique — tout ce qui
 * n'est pas explicitement bon est refusé. Jamais d'acceptation par défaut. Le refus étant
 * récupérable en un clic, une fausse alerte ne coûte pas un message.
 *
 * @param string $jeton Valeur du champ caché, déjà assainie.
 *
 * @return string Chaîne vide si le jeton est bon ; sinon « invalide », « rapide » ou « vieux ».
 */
function jeton_verifier( string $jeton ): string {
	$secret = jeton_secret();

	if ( '' === $secret ) {
		return 'invalide';
	}

	$segments = explode( '.', $jeton );

	if ( 2 !== count( $segments ) ) {
		return 'invalide';
	}

	$horodatage = $segments[0];
	$signature  = $segments[1];

	/*
	 * « ctype_digit() » avant tout calcul : il exclut le signe, la notation exponentielle et la
	 * chaîne vide, qu'un « (int) » avalerait en rendant 0. Douze chiffres couvrent l'horloge Unix
	 * jusqu'en l'an 33 658 et bornent la chaîne signée.
	 */
	if ( ! ctype_digit( $horodatage ) || strlen( $horodatage ) > 12 ) {
		return 'invalide';
	}

	if ( ! hash_equals( hash_hmac( 'sha256', $horodatage, $secret ), $signature ) ) {
		return 'invalide';
	}

	$age = time() - (int) $horodatage;

	/*
	 * Un âge négatif — horloge du serveur reculée entre deux requêtes — tombe ici aussi, et c'est
	 * le bon refus : il est récupérable en un clic, tandis qu'accepter un jeton venu du futur
	 * ouvrirait le délai minimal.
	 */
	if ( $age < JETON_AGE_MINIMAL ) {
		return 'rapide';
	}

	if ( $age > JETON_AGE_MAXIMAL ) {
		return 'vieux';
	}

	return '';
}
