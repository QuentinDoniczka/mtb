<?php
/**
 * Composant « Formulaire de contact » — rendu public.
 *
 * Inclus par le cœur à chaque instance du bloc, par un « require » NU et non un « require_once »
 * (wp-includes/blocks.php, fabrique du « render_callback »). CE FICHIER NE DÉCLARE DONC AUCUNE
 * FONCTION : une déclaration ici ferait tomber le site ENTIER dès qu'une page porterait deux
 * formulaires. Les fonctions d'aide vivent dans « rendu.php », inclus une seule fois par
 * « bootstrap.php ».
 *
 * Aucune détection de contexte n'est faite ici — ni « is_admin() », ni « REST_REQUEST » : un
 * « return » nu suffit à ne rien rendre, et l'encadré d'état vide de l'éditeur est porté par
 * « editeur.js », côté navigateur.
 *
 * @package MTB\Core
 *
 * Variables fournies par le cœur dans la portée de ce fichier :
 *
 * @var array     $attributes Attributs enregistrés du bloc.
 * @var string    $content    Contenu rendu par le cœur — ce bloc n'a pas d'enfants.
 * @var \WP_Block $block      Instance du bloc.
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * PREMIÈRE LIGNE, ET C'EST VOULU : le rang d'instance se prend avant tout autre travail.
 *
 * Tout rang différent de 1 ne rend RIEN — pas même une enveloppe. Deux formulaires postant sur la
 * même adresse sont indiscernables du serveur, qui ne saurait pas lequel a été soumis, et les neuf
 * identifiants du balisage seraient dupliqués dans le document : les « for » des étiquettes et les
 * liens du résumé d'erreurs pointeraient alors vers le mauvais champ.
 */
if ( 1 !== \MTB\Core\Blocks\FormulaireContact\Etat::prochaine_instance() ) {
	return;
}

/*
 * Le contenu en cours de rendu, par le contexte de bloc d'abord : c'est la seule valeur juste dans
 * une boucle secondaire ou un rendu différé. « get_queried_object_id() » ne sert que de repli, pour
 * un bloc rendu hors d'une boucle.
 */
$mtb_formulaire_contact_page = isset( $block->context['postId'] )
	? (int) $block->context['postId']
	: (int) get_queried_object_id();

$mtb_formulaire_contact_sortie = \MTB\Core\Blocks\FormulaireContact\composer(
	is_array( $attributes ) ? $attributes : array(),
	$mtb_formulaire_contact_page
);

/*
 * Le « return » rend la main sans rien imprimer : le cœur met ce fichier en tampon de sortie, la
 * valeur rendue ici est ignorée, et c'est le tampon vide qui devient le rendu du bloc. Ni enveloppe,
 * ni commentaire HTML, ni marge fantôme — aucune règle de la feuille ne suppose la présence du bloc.
 */
if ( '' === $mtb_formulaire_contact_sortie ) {
	return;
}

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Balisage entièrement composé et échappé par « rendu.php » : esc_html, esc_attr, esc_url, esc_textarea et wp_kses y sont appliqués valeur par valeur. Un échappement de plus ici sortirait le balisage en entités.
echo $mtb_formulaire_contact_sortie;
