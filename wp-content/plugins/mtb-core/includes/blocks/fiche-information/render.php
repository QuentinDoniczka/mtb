<?php
/**
 * Composant « Fiche d'information » — rendu public.
 *
 * Inclus par le cœur à chaque instance du bloc, par un « require » nu et non un « require_once »
 * (wp-includes/blocks.php:572). Ce fichier ne déclare donc AUCUNE fonction : une déclaration ici
 * ferait tomber le site entier dès qu'une page porterait deux fiches. Les fonctions d'aide sont
 * dans « rendu.php », inclus une seule fois par « bootstrap.php ».
 *
 * @package MTB\Core
 *
 * Variables fournies par le cœur dans la portée de ce fichier :
 *
 * @var array     $attributes Attributs enregistrés du bloc.
 * @var string    $content    Prose déjà rendue par le cœur, blocs enfants compris.
 * @var \WP_Block $block      Instance du bloc.
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$mtb_fiche_attributs = is_array( $attributes ) ? $attributes : array();
$mtb_fiche_prose     = is_string( $content ) ? $content : '';

$mtb_fiche_titre = \MTB\Core\Blocks\FicheInformation\titre(
	isset( $mtb_fiche_attributs['titre'] ) ? (string) $mtb_fiche_attributs['titre'] : '',
	isset( $mtb_fiche_attributs['niveau_titre'] ) ? (string) $mtb_fiche_attributs['niveau_titre'] : ''
);

$mtb_fiche_figure = \MTB\Core\Blocks\FicheInformation\figure(
	\MTB\Core\Blocks\FicheInformation\identifiant_photo(
		isset( $mtb_fiche_attributs['photo_id'] ) ? $mtb_fiche_attributs['photo_id'] : 0
	),
	isset( $mtb_fiche_attributs['photo_description'] ) ? (string) $mtb_fiche_attributs['photo_description'] : '',
	isset( $mtb_fiche_attributs['photo_legende'] ) ? (string) $mtb_fiche_attributs['photo_legende'] : '',
	isset( $mtb_fiche_attributs['cadrage'] ) ? (string) $mtb_fiche_attributs['cadrage'] : ''
);

$mtb_fiche_position = \MTB\Core\Blocks\FicheInformation\position_retenue(
	isset( $mtb_fiche_attributs['position_photo'] ) ? (string) $mtb_fiche_attributs['position_photo'] : ''
);

/*
 * La composition n'existe qu'une fois, dans « rendu.php » : ce fichier et la fonction d'interface du
 * module l'appellent tous deux, puis enveloppent son retour à leur façon. La prose est passée telle
 * quelle, sans kses : le cœur l'a déjà rendue, chaque bloc enfant ayant échappé son propre contenu.
 */
$mtb_fiche_corps = \MTB\Core\Blocks\FicheInformation\contenu(
	$mtb_fiche_titre,
	$mtb_fiche_figure,
	$mtb_fiche_prose,
	$mtb_fiche_position
);

// Rien de saisi : l'élément racine n'existe pas du tout. Aucun trou, aucune réserve.
if ( '' === $mtb_fiche_corps ) {
	return;
}

$mtb_fiche_racine = array( 'class' => 'mtb-fiche-information' );

// Émis pour la seule testabilité : l'ordre visuel est porté par l'ordre du DOM, jamais par le CSS.
if ( '' !== $mtb_fiche_figure ) {
	$mtb_fiche_racine['data-position'] = $mtb_fiche_position;
}

printf(
	'<div %1$s>%2$s</div>',
	get_block_wrapper_attributes( $mtb_fiche_racine ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() échappe lui-même ; l'entourer d'esc_attr() doublerait l'échappement.
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Titre, figure et légende échappés par rendu.php ; la prose est déjà rendue et échappée par le cœur.
	$mtb_fiche_corps
);
