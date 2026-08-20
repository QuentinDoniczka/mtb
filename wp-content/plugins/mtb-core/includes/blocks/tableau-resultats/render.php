<?php
/**
 * Rendu d'une instance du composant « Tableau de résultats ».
 *
 * Ce fichier est inclus par WordPress, une fois par instance du bloc, jamais par le chargeur de
 * l'extension. Il ne déclare donc AUCUNE FONCTION : deux tableaux sur la même page donneraient un
 * « Cannot redeclare function », erreur de compilation hors de portée de tout try/catch, et le site
 * entier tomberait.
 *
 * @package MTB\Core
 *
 * @var array<string, mixed> $attributes Attributs de l'instance, fournis par WordPress.
 * @var WP_Block             $block      Instance du bloc, fournie par WordPress.
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Le contexte de contenu est lu ici, et transmis à la fonction de rendu. Null — et non zéro — quand
 * il n'y en a pas : c'est cette valeur, et elle seule, qui autorise le repli sur le contenu
 * interrogé par la requête. Un zéro explicite signifierait « cette fiche-là », et ne doit jamais
 * faire afficher le palmarès d'un autre chien.
 *
 * Les attributs sont recastés dans donnees.php sans faire confiance au schéma de block.json :
 * do_blocks() tourne aussi sur du contenu importé par la reprise de l'ancien site.
 */
$mtb_contexte = null;

if ( isset( $block ) && $block instanceof WP_Block && isset( $block->context['postId'] ) && 0 < (int) $block->context['postId'] ) {
	$mtb_contexte = (int) $block->context['postId'];
}

// Le balisage est échappé morceau par morceau au moment où il est construit, dans balisage.php.
echo \MTB\Core\Blocks\TableauResultats\rendu( isset( $attributes ) && is_array( $attributes ) ? $attributes : array(), true, $mtb_contexte ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
