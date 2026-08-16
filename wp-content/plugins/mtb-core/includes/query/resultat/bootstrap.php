<?php
/**
 * Fonctions de lecture des résultats de travail, exposées au thème.
 *
 * Aucun espace de noms, volontairement : le thème n'écrit jamais « MTB\ », c'est la frontière
 * vérifiable par recherche entre le thème et l'extension.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/interne.php';

if ( ! function_exists( 'mtb_resultat_disciplines' ) ) {
	/**
	 * Liste close des disciplines : l'unique énumération du projet.
	 *
	 * Ni l'écran de saisie, ni le tri, ni le groupement, ni aucun composant n'en refont une :
	 * ajouter ou retirer une discipline coûte une ligne, ici. On stocke une clé stable et jamais un
	 * libellé, pour qu'une révision de graphie n'oblige jamais à réécrire des lignes existantes.
	 *
	 * @return array<string, string> Clé stockée vers libellé à imprimer, dans l'ordre gelé.
	 */
	function mtb_resultat_disciplines(): array {
		return array(
			'ring'                 => 'RING',
			'igp_rci'              => 'IGP / RCI',
			'mondioring'           => 'Mondioring',
			'obeissance'           => 'Obéissance',
			'pistage'              => 'Pistage',
			'recherche_utilitaire' => 'Recherche utilitaire',
			'sauvetage'            => 'Sauvetage',
			'truffe'               => 'Truffe',
			'autres_disciplines'   => 'Autres disciplines',
		);
	}
}

if ( ! function_exists( 'mtb_resultat_sexes' ) ) {
	/**
	 * Liste close des sexes.
	 *
	 * Le serveur fournit un mot, jamais un pictogramme : un symbole sans équivalent textuel échoue
	 * à l'accessibilité, et le composer serait une décision de rendu.
	 *
	 * @return array<string, string> Clé stockée vers libellé à imprimer.
	 */
	function mtb_resultat_sexes(): array {
		return array(
			'male'    => 'Mâle',
			'femelle' => 'Femelle',
		);
	}
}

if ( ! function_exists( 'mtb_get_resultats_travail_par_discipline' ) ) {
	/**
	 * Résultats publiés, groupés par discipline.
	 *
	 * @param array<string, mixed> $args Deux clés reconnues, et aucune autre : « ordre »
	 *                                   (« annee_desc » ou « annee_asc », « annee_desc » par défaut)
	 *                                   et « disciplines » (liste de clés ; vide = toutes).
	 *
	 * @return array<int, array<string, mixed>> Liste ordonnée de groupes, tableau vide s'il n'y a
	 *                                          rien à afficher. Une discipline sans aucune ligne
	 *                                          n'apparaît pas.
	 */
	function mtb_get_resultats_travail_par_discipline( array $args = array() ): array {
		return \MTB\Core\Query\Resultat\par_discipline(
			\MTB\Core\Query\Resultat\normaliser_args( $args, 'annee_desc' )
		);
	}
}

if ( ! function_exists( 'mtb_get_resultats_travail_du_chien' ) ) {
	/**
	 * Palmarès d'une fiche chien : ses résultats publiés, sans colonne Chien.
	 *
	 * @param int                  $chien_id Identifiant de la fiche chien.
	 * @param array<string, mixed> $args     Deux clés reconnues, et aucune autre : « ordre »
	 *                                       (« annee_asc » par défaut, une carrière se lisant dans
	 *                                       son sens) et « disciplines ».
	 *
	 * @return array<string, mixed> Deux clés toujours présentes : « colonnes » et « lignes », toutes
	 *                              deux à un tableau vide quand le chien n'a aucun résultat.
	 */
	function mtb_get_resultats_travail_du_chien( int $chien_id, array $args = array() ): array {
		return \MTB\Core\Query\Resultat\du_chien(
			$chien_id,
			\MTB\Core\Query\Resultat\normaliser_args( $args, 'annee_asc' )
		);
	}
}
