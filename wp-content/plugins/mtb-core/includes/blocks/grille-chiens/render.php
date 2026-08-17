<?php
/**
 * Rendu d'une instance du composant « Grille de chiens ».
 *
 * Ce fichier est inclus par WordPress, une fois par instance du bloc, jamais par le chargeur de
 * l'extension. Il ne déclare donc aucune fonction : deux grilles sur la même page donneraient un
 * « Cannot redeclare function », une erreur de compilation hors de portée de tout try/catch, et le
 * site entier tomberait. Il ne contient qu'une garde et une seule instruction de sortie.
 *
 * @package MTB\Core
 *
 * @var array<string, mixed> $attributes Attributs de l'instance, fournis par WordPress.
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Le balisage est échappé morceau par morceau au moment où il est construit, dans balisage.php.
echo \MTB\Core\Blocks\GrilleChiens\rendu( isset( $attributes ) && is_array( $attributes ) ? $attributes : array() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
