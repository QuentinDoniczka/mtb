<?php
/**
 * Années disponibles pour le réglage « Année » du composant « Liste de portées ».
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Blocks\ListePortees;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dérive la liste des années qui portent au moins une portée.
 *
 * Méthode statique dans l'espace de noms du bloc, et jamais une fonction globale « mtb_get_… » : les
 * modules sont chargés par ordre alphabétique et une garde « function_exists » ferait gagner cette
 * implémentation en silence le jour où le module propriétaire des portées exposera ses années.
 */
final class Annees {

	/**
	 * Années présentes dans les portées publiées, de la plus récente à la plus ancienne.
	 *
	 * Aucun tri n'est appliqué : la liste arrive déjà ordonnée du module qui possède les portées, et
	 * la refaire ici serait s'approprier une garantie qui ne nous appartient pas.
	 *
	 * @return string[] Années à quatre chiffres, tableau vide si aucune portée n'est datée.
	 */
	public static function disponibles(): array {
		if ( ! function_exists( 'mtb_get_portees' ) ) {
			return array();
		}

		$annees = array();

		foreach ( mtb_get_portees( array( 'nombre' => -1 ) ) as $portee ) {
			$annee = isset( $portee['annee'] ) && is_scalar( $portee['annee'] ) ? (string) $portee['annee'] : '';

			// Une portée sans date n'appartient à aucune année ; elle reste dans la liste des portées.
			if ( '' === $annee ) {
				continue;
			}

			$annees[] = $annee;
		}

		return array_values( array_unique( $annees ) );
	}
}
