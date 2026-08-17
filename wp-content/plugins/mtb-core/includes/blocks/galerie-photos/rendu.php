<?php
/**
 * Composition du balisage de la galerie photos.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Blocks\GaleriePhotos;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sous-taille demandée à WordPress.
 *
 * « medium » et jamais « thumbnail » : la vignette du cœur est rognée par défaut, ce qui
 * appliquerait un second recadrage centré, ignorant le point d'intérêt. Le recadrage appartient au
 * CSS, jamais à WordPress.
 *
 * @var string
 */
const TAILLE_PAR_DEFAUT = 'medium';

/**
 * Attribut de largeurs d'affichage, dérivé de la grille et non choisi.
 *
 * Chaque nombre est la borne supérieure exacte de sa bande de fenêtre : 196 px pour trois colonnes,
 * 222 px pour deux, et 90 vw en dessous — 100 vw surestimerait de 10 % et ferait monter d'un cran.
 * Sans cet attribut, le cœur écrirait « (max-width: 300px) 100vw, 300px », faux dès la deuxième
 * colonne et sur tout écran large.
 *
 * @var string
 */
const LARGEURS_PAR_DEFAUT = '(min-width: 32rem) 196px, (min-width: 21rem) 222px, 90vw';

/**
 * Compose le balisage complet de la galerie, déjà échappé.
 *
 * @param int[]                $identifiants Identifiants de photos, dans l'ordre voulu.
 * @param array<string, mixed> $options      Clés reconnues : « taille », « sizes ». Toute autre clé
 *                                           est ignorée sans erreur.
 *
 * @return string Balisage complet, ou chaîne vide quand aucune photo n'est affichable.
 */
function rendre( array $identifiants, array $options = array() ): string {
	$retenues = retenir( $identifiants );
	$total    = count( $retenues );

	/*
	 * Point unique de sortie pour deux situations : aucune photo n'a été choisie, et toutes les
	 * photos choisies ont disparu de la bibliothèque. Le bloc n'est alors pas rendu du tout —
	 * ni enrobage, ni liste vide, zéro octet dans la page.
	 */
	if ( 0 === $total ) {
		return '';
	}

	$taille   = option_texte( $options, 'taille', TAILLE_PAR_DEFAUT );
	$largeurs = option_texte( $options, 'sizes', LARGEURS_PAR_DEFAUT );

	$balisage = sprintf(
		'<div class="mtb-galerie-photos" data-mtb-total="%s">',
		esc_attr( (string) $total )
	);

	/*
	 * role="list" n'est pas décoratif : la feuille du composant retire « list-style », et Safari
	 * retire alors la sémantique de liste. L'attribut est ce qui fait annoncer « liste de N
	 * éléments » à la synthèse vocale.
	 */
	$balisage .= '<ul class="mtb-galerie-photos__grille" role="list">';

	foreach ( $retenues as $index => $photo_id ) {
		$balisage .= rendre_une_photo( $photo_id, $index + 1, $total, $taille, $largeurs );
	}

	$balisage .= '</ul></div>';

	return $balisage;
}

/**
 * Retient les identifiants qui désignent réellement une image affichable.
 *
 * Aucun dédoublonnage : si un identifiant apparaît deux fois, la photo apparaît deux fois.
 * L'écarter en silence serait décider à la place de l'éleveuse.
 *
 * @param int[] $identifiants Identifiants tels qu'enregistrés.
 *
 * @return int[] Identifiants retenus, réindexés, dans l'ordre d'origine.
 */
function retenir( array $identifiants ): array {
	$retenues = array();

	foreach ( $identifiants as $brut ) {
		$photo_id = is_scalar( $brut ) ? (int) $brut : 0;

		if ( $photo_id <= 0 ) {
			continue;
		}

		if ( 'attachment' !== get_post_type( $photo_id ) ) {
			continue;
		}

		// Plus strict que le seul type « attachment » : ferme le cas d'un document choisi par erreur.
		if ( ! wp_attachment_is_image( $photo_id ) ) {
			continue;
		}

		// Sans URL de fichier, le lien serait vide : écarter ici supprime toute branche au rendu.
		if ( '' === url_du_fichier( $photo_id ) ) {
			continue;
		}

		$retenues[] = $photo_id;
	}

	return $retenues;
}

/**
 * Rend une vignette : le lien vers le fichier, l'image, et le nom accessible du lien.
 *
 * @param int    $photo_id Identifiant de la photo.
 * @param int    $rang     Rang de la photo parmi celles réellement rendues, à partir de 1.
 * @param int    $total    Nombre de photos réellement rendues.
 * @param string $taille   Nom de la sous-taille demandée.
 * @param string $largeurs Attribut de largeurs d'affichage.
 */
function rendre_une_photo( int $photo_id, int $rang, int $total, string $taille, string $largeurs ): string {
	$balisage = '<li class="mtb-galerie-photos__element">';

	$balisage .= sprintf(
		'<a class="mtb-galerie-photos__lien" href="%1$s" data-mtb-photo="%2$s" data-mtb-rang="%3$s">',
		esc_url( url_du_fichier( $photo_id ) ),
		esc_attr( (string) $photo_id ),
		esc_attr( (string) $rang )
	);

	/*
	 * « loading » et « decoding » sont écrits explicitement : depuis WordPress 6.3, le cœur peut
	 * poser « eager » et « fetchpriority=high » sur la première grande image de la page. Une
	 * galerie n'est jamais le bandeau d'ouverture, et le cœur ne peut pas le savoir. La valeur
	 * explicite gagne, et « fetchpriority » n'est alors jamais émis.
	 *
	 * La classe remplace les « attachment-medium size-medium » du cœur : un seul crochet.
	 *
	 * Le texte alternatif n'est pas transmis : c'est celui de la bibliothèque, tel quel, vide
	 * compris. Aucun repli n'est fabriqué ici — le nom accessible du lien est assuré par le
	 * libellé de rang ci-dessous.
	 */
	$balisage .= wp_get_attachment_image(
		$photo_id,
		$taille,
		false,
		array(
			'class'    => 'mtb-galerie-photos__image',
			'sizes'    => $largeurs,
			'loading'  => 'lazy',
			'decoding' => 'async',
		)
	);

	/*
	 * Ce libellé est le nom accessible du lien, et c'est sa raison d'être : un lien dont le seul
	 * contenu est une image sans description n'a aucun nom accessible. Il complète la description
	 * de la bibliothèque quand elle existe, au lieu de l'écraser comme le ferait un aria-label.
	 * Le rang et le total sont des faits produits par le rendu, jamais une description inventée.
	 */
	$balisage .= sprintf(
		'<span class="mtb-galerie-photos__rang">%s</span>',
		esc_html( sprintf( 'Photo %1$d sur %2$d', $rang, $total ) )
	);

	$balisage .= '</a></li>';

	return $balisage;
}

/**
 * URL du fichier pleine taille, ou chaîne vide s'il n'en a pas.
 *
 * @param int $photo_id Identifiant de la photo.
 */
function url_du_fichier( int $photo_id ): string {
	$url = wp_get_attachment_image_url( $photo_id, 'full' );

	return is_string( $url ) ? $url : '';
}

/**
 * Lit une option de texte, en retombant sur son défaut dès qu'elle est absente ou inutilisable.
 *
 * @param array<string, mixed> $options Options reçues de l'appelant.
 * @param string               $cle     Clé recherchée.
 * @param string               $defaut  Valeur employée à défaut.
 */
function option_texte( array $options, string $cle, string $defaut ): string {
	if ( ! isset( $options[ $cle ] ) || ! is_string( $options[ $cle ] ) || '' === $options[ $cle ] ) {
		return $defaut;
	}

	return $options[ $cle ];
}
