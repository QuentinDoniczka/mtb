<?php
/**
 * Fonctions de lecture des portées, seule façon pour le thème d'obtenir une donnée.
 *
 * Espace de noms global, volontairement : le thème n'écrit jamais « MTB\ », c'est ce qui rend la
 * frontière thème / extension vérifiable d'un simple grep. Aucun hook n'est posé ici.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/hydratation.php';

if ( ! function_exists( 'mtb_get_portee' ) ) {
	/**
	 * Lit une portée par son identifiant de contenu.
	 *
	 * @param int $id Identifiant du contenu.
	 *
	 * @return array<string,mixed>|null Portée hydratée, ou null si elle n'existe pas.
	 */
	function mtb_get_portee( int $id ): ?array {
		if ( $id <= 0 ) {
			return null;
		}

		$post = get_post( $id );

		if ( ! $post instanceof \WP_Post || 'mtb_portee' !== $post->post_type ) {
			return null;
		}

		return \MTB\Core\Query\Portee\Hydratation::portee( $post );
	}
}

if ( ! function_exists( 'mtb_get_derniere_portee' ) ) {
	/**
	 * Lit la portée née le plus récemment.
	 *
	 * Le tri se fait sur la date de naissance, jamais sur la date de saisie. Une portée sans date
	 * de naissance ne peut donc pas devenir « la dernière ».
	 *
	 * @return array<string,mixed>|null Portée hydratée, ou null quand aucune portée n'est publiée.
	 */
	function mtb_get_derniere_portee(): ?array {
		foreach ( \MTB\Core\Query\Portee\Hydratation::liste() as $portee ) {
			// La liste est triée, les non datées en fin : la première datée est la plus récente.
			if ( '' !== (string) ( $portee['date_naissance']['valeur'] ?? '' ) ) {
				return $portee;
			}
		}

		return null;
	}
}

if ( ! function_exists( 'mtb_get_portee_par_identifiant' ) ) {
	/**
	 * Lit une portée par l'identifiant que l'éleveuse a saisi, par exemple « A3 2025 ».
	 *
	 * Un doublon d'identifiant est toléré à la saisie : il est départagé ici vers la portée née le
	 * plus récemment, puis par identifiant de contenu, de façon déterministe.
	 *
	 * @param string $identifiant Identifiant saisi.
	 *
	 * @return array<string,mixed>|null Portée hydratée, ou null si aucune ne porte cet identifiant.
	 */
	function mtb_get_portee_par_identifiant( string $identifiant ): ?array {
		$identifiant = trim( $identifiant );

		if ( '' === $identifiant ) {
			return null;
		}

		$portees = \MTB\Core\Query\Portee\Hydratation::liste( array( 'title' => $identifiant ) );

		return $portees[0] ?? null;
	}
}

if ( ! function_exists( 'mtb_get_portees' ) ) {
	/**
	 * Lit une liste de portées, de la plus récemment née à la plus ancienne.
	 *
	 * Arguments reconnus : « nombre » (-1 pour toutes), « page », « ordre » (« desc » ou « asc »),
	 * « annee » (quatre chiffres), « disponibilite » (« disponible », « reserve », « passee ») et
	 * « exclure » (identifiants de contenus). Tout autre argument est ignoré.
	 *
	 * @param array $args Arguments de lecture.
	 *
	 * @return array<int,array<string,mixed>> Portées hydratées, tableau vide si aucune ne correspond.
	 */
	function mtb_get_portees( array $args = array() ): array {
		$nombre = isset( $args['nombre'] ) && is_scalar( $args['nombre'] ) ? (int) $args['nombre'] : -1;

		if ( 0 === $nombre ) {
			$nombre = -1;
		}

		$page = isset( $args['page'] ) && is_scalar( $args['page'] ) ? max( 1, (int) $args['page'] ) : 1;

		$ordre = 'DESC';

		if ( isset( $args['ordre'] ) && is_scalar( $args['ordre'] ) && 'asc' === strtolower( (string) $args['ordre'] ) ) {
			$ordre = 'ASC';
		}

		$annee = isset( $args['annee'] ) && is_scalar( $args['annee'] ) ? trim( (string) $args['annee'] ) : '';

		if ( 1 !== preg_match( '/^\d{4}$/', $annee ) ) {
			$annee = '';
		}

		$disponibilite = isset( $args['disponibilite'] ) && is_scalar( $args['disponibilite'] ) ? (string) $args['disponibilite'] : '';

		// La liste close vient du module d'hydratation, jamais d'une copie de plus.
		$connues = \MTB\Core\Query\Portee\Hydratation::disponibilites();

		if ( ! isset( $connues[ $disponibilite ] ) ) {
			$disponibilite = '';
		}

		$exclure = array();

		if ( isset( $args['exclure'] ) && is_array( $args['exclure'] ) ) {
			foreach ( $args['exclure'] as $valeur ) {
				if ( is_scalar( $valeur ) && absint( $valeur ) > 0 ) {
					$exclure[] = absint( $valeur );
				}
			}
		}

		$portees = \MTB\Core\Query\Portee\Hydratation::liste(
			array( 'post__not_in' => $exclure ),
			$ordre
		);

		/*
		 * Filtres appliqués après hydratation, jamais par une clause de méta : une clause écarterait
		 * aussi les portées qui n'ont pas la clé, alors qu'elles doivent rester visibles.
		 */
		$retenues = array();

		foreach ( $portees as $portee ) {
			if ( '' !== $annee && $annee !== (string) $portee['annee'] ) {
				continue;
			}

			if ( '' !== $disponibilite && $disponibilite !== (string) ( $portee['disponibilite']['valeur'] ?? '' ) ) {
				continue;
			}

			$retenues[] = $portee;
		}

		if ( -1 === $nombre ) {
			return $retenues;
		}

		return array_slice( $retenues, ( $page - 1 ) * $nombre, $nombre );
	}
}

if ( ! function_exists( 'mtb_get_portees_du_chien' ) ) {
	/**
	 * Lit les portées dont ce chien est le père ou la mère.
	 *
	 * Le mode de saisie du parent est testé en plus de l'identifiant : une branche inactive
	 * conserve sa valeur, et la lire seule attribuerait au chien une portée qui n'est pas la
	 * sienne — une généalogie fausse.
	 *
	 * Le rapprochement se fait en PHP et non par une clause de méta : aucune portée ne peut ainsi
	 * être écartée par une clé absente.
	 *
	 * @param int $chien_id Identifiant de la fiche du chien.
	 *
	 * @return array<int,array<string,mixed>> Éléments de liste triés, tableau vide s'il n'y en a pas.
	 */
	function mtb_get_portees_du_chien( int $chien_id ): array {
		if ( $chien_id <= 0 ) {
			return array();
		}

		$elements = array();

		foreach ( \MTB\Core\Query\Portee\Hydratation::contenus() as $post ) {
			$id   = (int) $post->ID;
			$role = '';

			if ( 'fiche' === (string) get_post_meta( $id, '_mtb_pere_type', true )
				&& $chien_id === (int) get_post_meta( $id, '_mtb_pere_fiche', true )
			) {
				$role = 'pere';
			} elseif ( 'fiche' === (string) get_post_meta( $id, '_mtb_mere_type', true )
				&& $chien_id === (int) get_post_meta( $id, '_mtb_mere_fiche', true )
			) {
				$role = 'mere';
			}

			if ( '' === $role ) {
				continue;
			}

			$elements[] = \MTB\Core\Query\Portee\Hydratation::element_du_chien( $post, $role );
		}

		return \MTB\Core\Query\Portee\Hydratation::trier( $elements );
	}
}

if ( ! function_exists( 'mtb_get_portee_voisine' ) ) {
	/**
	 * Lit la portée qui précède ou qui suit celle-ci, par date de naissance.
	 *
	 * Une égalité de date est départagée par l'identifiant de contenu. Les extrémités de la liste
	 * renvoient null.
	 *
	 * @param int    $id   Identifiant de la portée de départ.
	 * @param string $sens « precedente » ou « suivante ».
	 *
	 * @return array<string,mixed>|null Portée hydratée, ou null s'il n'y en a pas.
	 */
	function mtb_get_portee_voisine( int $id, string $sens ): ?array {
		if ( $id <= 0 || ! in_array( $sens, array( 'precedente', 'suivante' ), true ) ) {
			return null;
		}

		/*
		 * L'ordre complet est le seul moyen exact de départager une égalité de date, qu'une
		 * comparaison « inférieur à » ne saurait pas faire. La liste est décroissante : la portée
		 * précédente, c'est-à-dire née avant, est donc l'élément suivant.
		 *
		 * Seules les portées datées entrent dans cette chaîne : elle est chronologique, et y insérer
		 * une portée sans date relierait deux fiches que rien n'ordonne l'une par rapport à l'autre.
		 * Elles restent entières dans mtb_get_portees() et dans l'index — c'est la navigation seule
		 * qui les ignore. Une portée sans date n'a donc ni précédente ni suivante.
		 */
		$portees      = array();
		$identifiants = array();

		foreach ( \MTB\Core\Query\Portee\Hydratation::liste() as $portee ) {
			if ( '' === (string) ( $portee['date_naissance']['valeur'] ?? '' ) ) {
				continue;
			}

			$portees[]      = $portee;
			$identifiants[] = (int) $portee['id'];
		}

		$position = array_search( $id, $identifiants, true );

		if ( false === $position ) {
			return null;
		}

		$cible = 'precedente' === $sens ? (int) $position + 1 : (int) $position - 1;

		if ( $cible < 0 || $cible >= count( $portees ) ) {
			return null;
		}

		return $portees[ $cible ];
	}
}
