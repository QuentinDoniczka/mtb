<?php
/**
 * Fonctions de lecture de la fiche Chien exposées au thème.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

/*
 * Pas d'espace de noms ici, volontairement : les fonctions de lecture publiques sont la seule
 * exception au nommage MTB\Core\<Groupe>\<Module>. Elles vivent dans l'espace global, préfixées
 * « mtb_ », pour qu'un thème conforme n'ait jamais à écrire « MTB\ ».
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Le vocabulaire du chien appartient au module « content/chien ». On le require_once plutôt que
 * de compter sur l'ordre de parcours du chargeur : un module ne doit jamais dépendre de cet
 * ordre, et une seconde inclusion est sans effet.
 */
require_once MTB_CORE_DIR . 'includes/content/chien/choix.php';
require_once __DIR__ . '/lecture.php';

if ( ! function_exists( 'mtb_get_chien' ) ) {
	/**
	 * Fiche complète d'un chien.
	 *
	 * Toutes les clés du retour sont toujours présentes, même quand la donnée manque : le thème
	 * n'a jamais à tester une clé à l'aveugle. Les chaînes sont brutes ; le thème les échappe au
	 * rendu, et ne reformate jamais une valeur recopiée ni une date.
	 *
	 * @param int $chien_id Identifiant de la fiche, 0 pour la fiche affichée.
	 *
	 * @return array<string, mixed>|null Null si l'identifiant ne désigne pas une fiche publiée.
	 */
	function mtb_get_chien( int $chien_id = 0 ): ?array {
		return \MTB\Core\Query\Chien\fiche( $chien_id );
	}
}

if ( ! function_exists( 'mtb_get_chiens_par_statut' ) ) {
	/**
	 * Chiens publiés, groupés par statut, dans l'ordre d'affichage gelé.
	 *
	 * Un groupe sans chien n'est pas renvoyé du tout : c'est le serveur qui décide qu'une section
	 * n'est pas rendue, jamais le thème. Un chien sans statut n'apparaît dans aucun groupe.
	 *
	 * @param array<string, mixed> $args « ordre » : naissance_desc (défaut), naissance_asc,
	 *                                   alphabetique. Le thème ne le passe jamais.
	 *
	 * @return array<int, array<string, mixed>> Liste ordonnée de groupes ; tableau vide si aucun
	 *                                          chien n'a de statut.
	 */
	function mtb_get_chiens_par_statut( array $args = array() ): array {
		return \MTB\Core\Query\Chien\groupes( $args );
	}
}
