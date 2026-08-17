<?php
/**
 * Rendu public du bloc « Liste de portées ».
 *
 * WordPress inclut ce fichier à chaque instance du bloc, dans une portée locale : il ne déclare donc
 * ni fonction ni classe. Une deuxième liste sur la même page produirait sans cela une erreur fatale
 * que rien ne rattrape. Tout le balisage vit dans Rendu, chargé une seule fois par bootstrap.php.
 *
 * Ce fichier ne connaît que le public : il ne cherche jamais à savoir s'il rend pour l'éditeur, cette
 * question est tranchée dans editeur.js, où elle est exacte par nature.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Chaque valeur est échappée dans Rendu, au plus près de son balisage.
echo \MTB\Core\Blocks\ListePortees\Rendu::bloc( isset( $attributes ) && is_array( $attributes ) ? $attributes : array() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
