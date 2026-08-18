<?php
/**
 * Composant « Bandeau d'alerte » — rendu public.
 *
 * Inclus par le cœur à chaque instance du bloc, par un « require » nu et non un « require_once »
 * (wp-includes/blocks.php). Ce fichier ne déclare donc AUCUNE fonction : une déclaration ici ferait
 * tomber le site entier dès qu'une page porterait deux bandeaux. Les fonctions d'aide vivent dans
 * « rendu.php », inclus une seule fois par « bootstrap.php ».
 *
 * @package MTB\Core
 *
 * Variables fournies par le cœur dans la portée de ce fichier :
 *
 * @var array     $attributes Attributs enregistrés du bloc.
 * @var string    $content    Contenu rendu par le cœur — ce bloc n'en a aucun.
 * @var \WP_Block $block      Instance du bloc.
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$mtb_bandeau_alerte_attributs = is_array( $attributes ) ? $attributes : array();

/*
 * Recasté sans faire confiance au schéma de block.json : do_blocks() peut aussi tourner sur du
 * contenu importé par la reprise de l'ancien site, ou forgé à la main.
 */
$mtb_bandeau_alerte_message = isset( $mtb_bandeau_alerte_attributs['message'] )
	? (string) $mtb_bandeau_alerte_attributs['message']
	: '';

/*
 * Message vide : le composant ne s'affiche pas du tout au visiteur. Ni enveloppe, ni commentaire
 * HTML, ni marge fantôme — il n'y a donc rien à masquer en CSS, et aucune règle de la feuille ne
 * suppose la présence de l'encart sur une page.
 *
 * Le « return » rend la main sans rien imprimer : le cœur met ce fichier en tampon de sortie, la
 * valeur rendue ici est ignorée, et c'est le tampon vide qui devient le rendu du bloc.
 */
if ( \MTB\Core\Blocks\BandeauAlerte\est_vide( $mtb_bandeau_alerte_message ) ) {
	return;
}

/*
 * Le message est passé à wp_kses() TEL QU'IL EST ENREGISTRÉ : la détection du vide travaille sur une
 * copie, jamais sur la valeur émise. wp_kses() est la seule et unique barrière du module —
 * esc_html() afficherait « <a href="…"> » en clair dans la phrase de l'éleveuse.
 */
printf(
	'<div %1$s><p class="%2$s">%3$s</p></div>',
	get_block_wrapper_attributes( array( 'class' => 'mtb-bandeau-alerte' ) ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() échappe lui-même ; l'entourer d'esc_attr() doublerait l'échappement.
	esc_attr( 'mtb-bandeau-alerte__message' ),
	wp_kses(
		$mtb_bandeau_alerte_message,
		\MTB\Core\Blocks\BandeauAlerte\balises_admises(),
		\MTB\Core\Blocks\BandeauAlerte\protocoles_admis()
	)
);
