<?php
/**
 * Rendu serveur du bloc « mtb/galerie-photos ».
 *
 * Inclus par WordPress au moment du rendu, jamais par le chargeur de modules.
 *
 * @package MTB\Core
 *
 * @var array<string, mixed> $attributes Attributs du bloc, validés par le schéma de block.json.
 * @var string               $content    Contenu enregistré — aucun, le bloc n'a pas d'enfant.
 * @var \WP_Block            $block      Instance du bloc en cours de rendu.
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$mtb_galerie_html = \MTB\Core\Blocks\GaleriePhotos\rendre( (array) ( $attributes['photos'] ?? array() ) );

// Rien du tout dans la page : ni enrobage, ni liste vide. La sortie précède toute émission.
if ( '' === $mtb_galerie_html ) {
	return;
}

/*
 * Échappement fait pièce par pièce dans « rendu.php ». Un second filtrage par wp_kses_post()
 * retirerait les crochets « data-mtb-* » gelés pour la future visionneuse, et selon la version
 * l'attribut « decoding » : il détruirait le contrat au lieu de protéger quoi que ce soit.
 */
// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo $mtb_galerie_html;
