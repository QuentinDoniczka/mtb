<?php
/**
 * Composant « Coordonnées et plan d'accès » — rendu public.
 *
 * Inclus par le cœur à chaque instance du bloc, par un « require » nu et non un « require_once »
 * (wp-includes/blocks.php). Ce fichier ne déclare donc AUCUNE fonction : une déclaration ici ferait
 * tomber le site entier dès qu'une page porterait deux fois le composant — coordonnées en tête et en
 * pied d'une page longue est un usage prévu. Les fonctions d'aide sont dans « rendu.php », inclus
 * une seule fois par « bootstrap.php ».
 *
 * @package MTB\Core
 *
 * Variables fournies par le cœur dans la portée de ce fichier :
 *
 * @var array     $attributes Attributs enregistrés du bloc.
 * @var string    $content    Contenu interne, inutilisé : ce composant n'a aucun bloc enfant.
 * @var \WP_Block $block      Instance du bloc.
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$mtb_coordonnees_attributs = is_array( $attributes ) ? $attributes : array();

/*
 * Les attributs sont recastés sans faire confiance au schéma de block.json : do_blocks() tourne
 * aussi sur du contenu importé par la reprise de l'ancien site, ou forgé à la main. L'assainissement
 * a lieu ici, au rendu, parce que ce module n'a aucun chemin d'écriture propre — les attributs
 * arrivent dans « post_content » par le chemin du cœur, dont la capacité et le nonce REST sont déjà
 * vérifiés par wp_insert_post() et rest_cookie_check_errors().
 */
$mtb_coordonnees_corps = \MTB\Core\Blocks\CoordonneesPlan\contenu(
	\MTB\Core\Blocks\CoordonneesPlan\texte_multiligne(
		isset( $mtb_coordonnees_attributs['adresse'] ) ? $mtb_coordonnees_attributs['adresse'] : ''
	),
	\MTB\Core\Blocks\CoordonneesPlan\texte_ligne(
		isset( $mtb_coordonnees_attributs['telephone'] ) ? $mtb_coordonnees_attributs['telephone'] : ''
	),
	\MTB\Core\Blocks\CoordonneesPlan\texte_ligne(
		isset( $mtb_coordonnees_attributs['courriel'] ) ? $mtb_coordonnees_attributs['courriel'] : ''
	),
	absint(
		isset( $mtb_coordonnees_attributs['plan_id'] ) && is_scalar( $mtb_coordonnees_attributs['plan_id'] )
			? $mtb_coordonnees_attributs['plan_id']
			: 0
	),
	\MTB\Core\Blocks\CoordonneesPlan\texte_ligne(
		isset( $mtb_coordonnees_attributs['plan_description'] ) ? $mtb_coordonnees_attributs['plan_description'] : ''
	),
	(int) get_the_ID()
);

/*
 * Aucune coordonnée saisie : l'élément racine n'existe pas du tout, même si un plan est choisi.
 * Aucun trou, aucune réserve, zéro octet. Le « return » rend la main sans rien imprimer — le cœur
 * met ce fichier en tampon de sortie, la valeur rendue ici est ignorée, et c'est le tampon vide qui
 * devient le rendu du bloc. Côté éditeur, l'écran du composant affiche son état vide.
 */
if ( '' === $mtb_coordonnees_corps ) {
	return;
}

printf(
	'<div %1$s>%2$s</div>',
	get_block_wrapper_attributes( array( 'class' => 'mtb-coordonnees-plan' ) ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() échappe lui-même ; l'entourer d'esc_attr() doublerait l'échappement.
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Étiquettes, valeurs, URI et légende échappées par rendu.php ; l'image est échappée par wp_get_attachment_image().
	$mtb_coordonnees_corps
);
