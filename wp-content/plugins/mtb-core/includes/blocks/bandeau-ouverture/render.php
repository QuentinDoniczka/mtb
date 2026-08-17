<?php
/**
 * Rendu du composant « Bandeau d'ouverture ».
 *
 * Inclus par WordPress, une fois par instance du bloc, jamais par le chargeur de l'extension.
 * Reçoit $attributes, $content et $block de la portée d'appel du cœur.
 *
 * Aucun espace de noms et AUCUNE DÉCLARATION DE FONCTION dans ce fichier : deux instances du même
 * bloc sur une page provoqueraient un « Cannot redeclare function », erreur de compilation qu'aucun
 * try/catch n'attrape. La décision du titre principal vit dans titre-principal.php et est appelée
 * ici en nom pleinement qualifié.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Les trois attributs sont recastés sans faire confiance au schéma de block.json : do_blocks() peut
 * aussi tourner sur du contenu importé par la reprise de l'ancien site, ou forgé à la main.
 */
$mtb_post_id = 0;

if ( isset( $block ) && $block instanceof WP_Block && isset( $block->context['postId'] ) ) {
	$mtb_post_id = (int) $block->context['postId'];
}

if ( 0 === $mtb_post_id ) {
	$mtb_post_id = (int) get_the_ID();
}

$mtb_photo_saisie = isset( $attributes['photo'] ) ? (int) $attributes['photo'] : 0;

/*
 * Un seul contrôle couvre les deux pannes possibles : la pièce jointe a été supprimée depuis
 * (get_post() rend null) ou le fichier choisi n'est pas une image (un PDF). Dans les deux cas le
 * bandeau devient une bande de texte, la page reste juste, et aucun cadre vide n'apparaît.
 */
$mtb_id_photo = ( $mtb_photo_saisie > 0 && wp_attachment_is_image( $mtb_photo_saisie ) ) ? $mtb_photo_saisie : 0;

$mtb_balise_photo = '';

if ( 0 !== $mtb_id_photo ) {
	/*
	 * La description de la photo est lue sur la photo elle-même et passée en dur, plutôt que laissée
	 * au repli historique du cœur sur le titre, la légende ou le nom de fichier : l'extension
	 * n'invente jamais de texte alternatif. Vide, la photo est rendue décorative et le titre porte le
	 * sens.
	 */
	$mtb_description = trim( (string) get_post_meta( $mtb_id_photo, '_wp_attachment_image_alt', true ) );

	$mtb_attributs_image = array(
		'class' => 'mtb-bandeau-ouverture__image',
		'alt'   => $mtb_description,
		// Le composant occupe la largeur du canal où il est posé ; le thème décide de cette largeur.
		'sizes' => '100vw',
		/*
		 * Indications de chargement, pas de style : le cœur pose « lazy » et « async » par défaut sur
		 * une image de contenu, il faut donc écraser explicitement. Cette photo est le plus grand
		 * élément visible à l'ouverture de la page.
		 */
		'fetchpriority' => 'high',
		'loading'       => 'eager',
		'decoding'      => 'async',
	);

	/**
	 * Filtre les attributs de l'image du bandeau.
	 *
	 * Unique échappatoire du composant : elle permet d'ajuster « sizes », « loading »,
	 * « fetchpriority » ou « class » sans modifier un fichier de l'extension. Elle ne peut ni retirer
	 * la description de la photo — réappliquée après le filtre — ni changer la photo affichée.
	 *
	 * @param array<string, string> $mtb_attributs_image Attributs passés à wp_get_attachment_image().
	 * @param int                   $mtb_id_photo        Identifiant de la photo rendue.
	 * @param int                   $mtb_post_id         Identifiant du contenu qui porte le bandeau.
	 */
	$mtb_attributs_image = apply_filters( 'mtb_bandeau_ouverture_attributs_image', $mtb_attributs_image, $mtb_id_photo, $mtb_post_id );

	if ( ! is_array( $mtb_attributs_image ) ) {
		$mtb_attributs_image = array();
	}

	$mtb_attributs_image['alt'] = $mtb_description;

	/*
	 * Taille « full » : wp_calculate_image_srcset() propose alors toutes les tailles enregistrées et
	 * laisse le navigateur choisir. wp_get_attachment_image() échappe src, srcset, sizes, alt et
	 * class, et fournit width et height explicites — décalage cumulé nul.
	 */
	$mtb_balise_photo = (string) wp_get_attachment_image( $mtb_id_photo, 'full', false, $mtb_attributs_image );

	// Le cœur peut refuser de rendre là où nous avions dit oui : le modificateur décrit la sortie.
	if ( '' === $mtb_balise_photo ) {
		$mtb_id_photo = 0;
	}
}

/*
 * Le repli du titre vide sur le titre de la page est exactement la règle sur laquelle la garde 5 de la
 * décision du « h1 » se prononce : elle vit donc dans titre-principal.php, en un seul endroit.
 * Recopiée ici, une correction faite d'un seul côté ferait décider le « h1 » sur un titre et en
 * imprimer un autre. C'est aussi là qu'est écrit pourquoi le titre de la page est lu brut plutôt que
 * par get_the_title().
 */
$mtb_titre = \MTB\Core\Blocks\BandeauOuverture\titre_effectif(
	isset( $attributes['titre'] ) ? (string) $attributes['titre'] : '',
	$mtb_post_id
);

$mtb_accroche = isset( $attributes['accroche'] ) ? trim( (string) $attributes['accroche'] ) : '';

/*
 * Ni photo, ni titre, ni titre de page, ni accroche : côté public, un composant sans contenu ne
 * s'affiche pas. Sans cette sortie, une page sans titre rendrait une bande sombre vide, c'est-à-dire
 * la page cassée qu'un contenu mal rempli ne doit jamais produire. Côté éditeur, l'écran du bloc
 * affiche son état vide AVANT d'appeler le serveur : l'éditrice n'a jamais un trou muet.
 *
 * Le « return » rend la main sans rien imprimer : le cœur met ce fichier en tampon de sortie, la
 * valeur rendue ici est ignorée, et c'est le tampon vide qui devient le rendu du bloc.
 */
if ( 0 === $mtb_id_photo && '' === $mtb_titre && '' === $mtb_accroche ) {
	return;
}

$mtb_classes = 'mtb-bandeau-ouverture';

/*
 * Un seul modificateur, et il décrit la SORTIE RENDUE, jamais le champ enregistré. Il n'existe pas de
 * « --sans-texte » ni de « --avec-photo » : une classe qu'aucune règle ne cible est une classe morte,
 * donc un nom que l'un des dix composants du catalogue orthographiera mal un jour.
 */
if ( 0 === $mtb_id_photo ) {
	$mtb_classes .= ' mtb-bandeau-ouverture--sans-photo';
}

$mtb_porte_le_titre = \MTB\Core\Blocks\BandeauOuverture\doit_porter_le_titre_principal( $mtb_post_id );

// « h1 » ou « p » selon la décision, mais TOUJOURS la même classe : le thème stylise la classe.
$mtb_balise_titre = $mtb_porte_le_titre ? 'h1' : 'p';

?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => $mtb_classes ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() échappe lui-même ; l'entourer d'esc_attr() doublerait l'échappement. ?>>
	<?php if ( 0 !== $mtb_id_photo ) : ?>
		<?php // « mtb-photo » est le crochet générique commun aux composants qui affichent une photo. ?>
		<div class="mtb-bandeau-ouverture__photo mtb-photo">
			<?php echo $mtb_balise_photo; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- balisage complet produit et échappé par wp_get_attachment_image(). ?>
		</div>
	<?php endif; ?>
	<?php if ( '' !== $mtb_titre || '' !== $mtb_accroche ) : ?>
		<div class="mtb-bandeau-ouverture__texte">
			<?php if ( '' !== $mtb_titre ) : ?>
				<?php
				printf(
					'<%1$s class="mtb-bandeau-ouverture__titre">%2$s</%1$s>',
					tag_escape( $mtb_balise_titre ),
					esc_html( $mtb_titre )
				);
				?>
			<?php endif; ?>
			<?php if ( '' !== $mtb_accroche ) : ?>
				<p class="mtb-bandeau-ouverture__accroche"><?php echo esc_html( $mtb_accroche ); ?></p>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>
