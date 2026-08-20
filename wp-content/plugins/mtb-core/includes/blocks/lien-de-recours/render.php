<?php
/**
 * Composant « Lien de recours » — rendu public.
 *
 * Inclus par le cœur à chaque instance du bloc, par un « require » nu et non un « require_once »
 * (wp-includes/blocks.php). Ce fichier ne déclare donc AUCUNE fonction : une déclaration ici ferait
 * tomber le site entier dès le deuxième lien de la liste. La résolution de la destination vit dans
 * « rendu.php », inclus une seule fois par « bootstrap.php ».
 *
 * @package MTB\Core
 *
 * Variables fournies par le cœur dans la portée de ce fichier :
 *
 * @var array     $attributes Attributs enregistrés du bloc.
 * @var string    $content    Contenu par défaut du bloc — inutilisé : ce bloc n'a pas d'enfants.
 * @var \WP_Block $block      Instance du bloc.
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$mtb_recours_attributs = is_array( $attributes ) ? $attributes : array();

$mtb_recours_cible = isset( $mtb_recours_attributs['cible'] ) && is_string( $mtb_recours_attributs['cible'] )
	? $mtb_recours_attributs['cible']
	: 'accueil';

$mtb_recours_destination = \MTB\Core\Blocks\LienDeRecours\destination( $mtb_recours_cible );

/*
 * Destination inexistante : le composant ne rend RIEN — pas un « li » vide, pas un lien désactivé,
 * pas une mention. Le « return » rend la main sans rien imprimer ; le cœur met ce fichier en tampon
 * de sortie, et c'est le tampon vide qui devient le rendu du bloc. La liste des gabarits perd un
 * élément, sans trou ni puce orpheline.
 */
if ( null === $mtb_recours_destination ) {
	return;
}

/*
 * Le « li » est la racine du bloc : c'est lui qui porte les attributs d'enveloppe. Le composant
 * n'active aucun réglage d'apparence, la seule classe émise est donc le crochet du thème, et
 * l'extension n'écrit ici aucune règle visuelle.
 */
printf(
	'<li %1$s><a href="%2$s">%3$s</a></li>',
	get_block_wrapper_attributes( array( 'class' => 'mtb-lien-de-recours' ) ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() échappe lui-même ; l'entourer d'esc_attr() doublerait l'échappement.
	esc_url( $mtb_recours_destination['url'] ),
	esc_html( $mtb_recours_destination['libelle'] )
);
