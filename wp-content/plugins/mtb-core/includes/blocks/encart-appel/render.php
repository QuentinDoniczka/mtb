<?php
/**
 * Composant « Encart d’appel » — rendu public.
 *
 * Inclus par le cœur à chaque instance du bloc, par un « require » nu et non un « require_once »
 * (wp-includes/blocks.php, fabrique du « render_callback »). Ce fichier ne déclare donc AUCUNE
 * fonction : une déclaration ici ferait tomber le site entier dès qu’une page porterait deux
 * encarts — et le bloc en autorise plusieurs par page. Les fonctions d’aide sont dans « rendu.php »,
 * inclus une seule fois par « bootstrap.php ».
 *
 * Aucune détection de contexte n’est faite ici — ni « is_admin() », ni « REST_REQUEST » : un
 * « return » nu suffit à ne rien rendre, et l’encadré d’état vide de l’éditeur est porté par
 * « editeur.js ».
 *
 * @package MTB\Core
 *
 * Variables fournies par le cœur dans la portée de ce fichier :
 *
 * @var array     $attributes Attributs enregistrés du bloc.
 * @var string    $content    Contenu par défaut du bloc — inutilisé : ce bloc n’a pas d’enfants.
 * @var \WP_Block $block      Instance du bloc.
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$mtb_encart_attributs = is_array( $attributes ) ? $attributes : array();

/*
 * Assainissement en entrée : « trim » sur les chaînes, pour la seule décision « est-ce vide » et pour
 * retirer des espaces de bord, qui ne sont pas un fait de domaine ; « absint » sur l’identifiant, dans
 * rendu.php. Ni sanitize_text_field, ni wp_strip_all_tags, ni wp_kses : elles passent par strip_tags()
 * et videraient en silence une valeur commençant par « < ». La sortie, elle, est échappée sans
 * exception.
 */
$mtb_encart_accroche = isset( $mtb_encart_attributs['accroche'] ) && is_string( $mtb_encart_attributs['accroche'] )
	? trim( $mtb_encart_attributs['accroche'] )
	: '';

$mtb_encart_saisie = isset( $mtb_encart_attributs['telephone'] ) && is_string( $mtb_encart_attributs['telephone'] )
	? $mtb_encart_attributs['telephone']
	: '';

$mtb_encart_numero = \MTB\Core\Blocks\EncartAppel\telephone_retenu( $mtb_encart_saisie );
$mtb_encart_page   = \MTB\Core\Blocks\EncartAppel\page_retenue( $mtb_encart_attributs['page_id'] ?? 0 );

/*
 * L’unique garde du module : sans numéro ET sans page, l’encart serait un titre suivi de rien. Il
 * n’existe alors pas du tout côté public — aucun trou, aucune réserve. C’est dans l’éditeur seul que
 * l’éditrice voit l’encadré expliquant ce qui manque.
 */
if ( '' === $mtb_encart_numero && 0 === $mtb_encart_page ) {
	return;
}

// L’adresse est échappée avant d’être jugée : si le protocole « tel » venait à être refusé, le
// numéro s’affiche en texte simple plutôt que dans un lien vide.
$mtb_encart_href = esc_url( \MTB\Core\Blocks\EncartAppel\telephone_href( $mtb_encart_numero ) );

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() échappe lui-même ; l’entourer d’esc_attr() doublerait l’échappement.
echo '<div ' . get_block_wrapper_attributes( array( 'class' => 'mtb-encart-appel' ) ) . '>';

// Libellé figé par le vocabulaire d’interface : il ne se règle pas, il ne se compose pas.
echo '<h2 class="mtb-encart-appel__titre">Nous contacter</h2>';

// Jamais de paragraphe vide : sans accroche, l’élément n’existe pas et l’écart est porté par le titre.
if ( '' !== $mtb_encart_accroche ) {
	echo '<p class="mtb-encart-appel__accroche">' . esc_html( $mtb_encart_accroche ) . '</p>';
}

/*
 * Les deux actions sont des enfants DIRECTS de ce conteneur : c’est lui qui porte l’écart entre deux
 * cibles tactiles et le passage à la ligne sur téléphone. Un paragraphe intercalé les empilerait et
 * ferait tomber les deux. La garde ci-dessus assure qu’il n’est jamais vide.
 */
echo '<div class="mtb-encart-appel__actions">';

if ( '' !== $mtb_encart_numero ) {
	$mtb_encart_lu = esc_html( \MTB\Core\Blocks\EncartAppel\telephone_affiche( $mtb_encart_numero ) );

	/*
	 * Même crochet dans les deux cas : le numéro garde sa boîte et sa hauteur de cible qu’il soit
	 * composable ou non. L’absence de soulignement sur le « span » est correcte — ce n’est pas un lien.
	 */
	if ( '' !== $mtb_encart_href ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $mtb_encart_href sort d’esc_url(), $mtb_encart_lu d’esc_html().
		echo '<a class="mtb-encart-appel__telephone" href="' . $mtb_encart_href . '">' . $mtb_encart_lu . '</a>';
	} else {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $mtb_encart_lu sort d’esc_html().
		echo '<span class="mtb-encart-appel__telephone">' . $mtb_encart_lu . '</span>';
	}
}

/*
 * Le bouton ne porte que la classe de bouton du site, et aucune classe propre à ce composant : son
 * apparence est une primitive écrite une seule fois pour tout le site. Une classe que personne ne
 * style le ferait retomber sur les couleurs du cœur, hors de la palette.
 *
 * Son libellé est le titre de la page choisie, recopié : rien n’est inventé, et il suit tout seul si
 * l’éditrice renomme sa page.
 */
if ( 0 !== $mtb_encart_page ) {
	echo '<a class="wp-element-button" href="' . esc_url( (string) get_permalink( $mtb_encart_page ) ) . '">'
		. esc_html( trim( (string) get_the_title( $mtb_encart_page ) ) )
		. '</a>';
}

echo '</div>';
echo '</div>';
