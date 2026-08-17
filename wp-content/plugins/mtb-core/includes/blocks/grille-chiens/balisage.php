<?php
/**
 * Construction du balisage du composant « Grille de chiens ».
 *
 * Le module rend une structure et des crochets de classes, jamais une décision visuelle : aucun
 * style en ligne, aucune classe de mise en page, aucune dimension. Le thème habille.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Blocks\GrilleChiens;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Balisage complet d'une instance du bloc.
 *
 * Trois sorties possibles, et rien d'autre : la grille, l'état vide de l'éditeur, ou la chaîne
 * vide. Côté visiteur, un composant sans contenu ne s'affiche pas — pas même son conteneur.
 *
 * @param array<string, mixed> $attributs Attributs de l'instance.
 */
function rendu( array $attributs ): string {
	if ( ! lecture_disponible() ) {
		return '';
	}

	$statut   = statut_demande( $attributs );
	$sections = '';

	foreach ( groupes( $statut ) as $groupe ) {
		$sections .= balisage_groupe( $groupe );
	}

	if ( '' === $sections ) {
		return contexte_editeur() ? balisage_etat_vide( $statut ) : '';
	}

	return sprintf(
		'<div %s>%s</div>',
		get_block_wrapper_attributes( array( 'class' => 'mtb-grille-chiens' ) ),
		$sections
	);
}

/**
 * Une carte : la photo carrée et le nom d'usage. Rien d'autre.
 *
 * Ni statut, ni sexe, ni variété, ni date : ces champs valent « Non renseigné » quand ils sont
 * vides, et on écrirait alors « Né le Non renseigné » sous une vignette. Le statut, lui, est porté
 * par le titre du groupe. Toutes les cartes ont ainsi la même silhouette, remplies ou non.
 *
 * @param mixed $chien Fiche telle que la fonction de lecture l'a construite.
 */
function balisage_carte( $chien ): string {
	if ( ! is_array( $chien ) ) {
		return '';
	}

	$nom = isset( $chien['nom_usage'] ) && is_string( $chien['nom_usage'] ) ? $chien['nom_usage'] : '';
	$id  = isset( $chien['id'] ) ? (int) $chien['id'] : 0;

	return '<li class="mtb-grille-chiens__carte">'
		. balisage_cadre_photo( $chien )
		. balisage_nom( $nom, $id )
		. '</li>';
}

/**
 * Le nom d'usage, lié à la fiche quand un lien utilisable existe.
 *
 * Un seul lien par carte, et il porte le nom : la photo n'est jamais un lien, sans quoi chaque
 * chien coûterait deux arrêts de tabulation et deux annonces au lecteur d'écran pour la même fiche.
 * Le texte est toujours enveloppé, avec ou sans lien, pour que la feuille de style n'ait qu'une
 * seule forme à cibler.
 *
 * @param string $nom      Nom d'usage.
 * @param int    $chien_id Identifiant de la fiche.
 */
function balisage_nom( string $nom, int $chien_id ): string {
	$texte = '<span class="mtb-grille-chiens__nom-texte">' . esc_html( $nom ) . '</span>';
	$lien  = lien_de_la_fiche( $chien_id );

	if ( '' === $lien ) {
		return '<h3 class="mtb-grille-chiens__nom">' . $texte . '</h3>';
	}

	return sprintf(
		'<h3 class="mtb-grille-chiens__nom"><a class="mtb-grille-chiens__lien" href="%s">%s</a></h3>',
		esc_url( $lien ),
		$texte
	);
}

/**
 * Adresse de la fiche, ou chaîne vide s'il n'y en a pas d'utilisable.
 *
 * Jamais un lien vide, jamais un « # » : sans destination, le nom reste nu. La fiche de chien ne
 * porte pas son adresse dans le retour de lecture ; on la demande donc à WordPress pour un
 * identifiant que la fonction de lecture a elle-même fourni.
 *
 * @param int $chien_id Identifiant de la fiche.
 */
function lien_de_la_fiche( int $chien_id ): string {
	if ( $chien_id <= 0 ) {
		return '';
	}

	$lien = get_permalink( $chien_id );

	if ( ! is_string( $lien ) || '' === $lien ) {
		return '';
	}

	// esc_url() rend une chaîne vide pour une adresse qu'il refuse : c'est le même test qu'au rendu.
	return '' === esc_url( $lien ) ? '' : $lien;
}

/**
 * Le cadre de la photo. Il est rendu même sans photo : l'emplacement se réserve, il ne disparaît
 * jamais, sinon les cartes n'auraient pas la même silhouette.
 *
 * @param array<string, mixed> $chien Fiche du chien.
 */
function balisage_cadre_photo( array $chien ): string {
	$photo   = isset( $chien['photo_principale'] ) && is_array( $chien['photo_principale'] ) ? $chien['photo_principale'] : null;
	$image   = null === $photo ? '' : balisage_photo( $photo );
	$classes = array( 'mtb-grille-chiens__cadre-photo' );

	if ( '' === $image ) {
		// Aucun modificateur de cadrage sans photo : il n'y aurait rien à ancrer. Et le cadre reste
		// vide — ni pictogramme, ni silhouette, ni image de remplacement.
		$classes[] = 'mtb-grille-chiens__cadre-photo--absente';

		return sprintf( '<div class="%s"></div>', esc_attr( implode( ' ', $classes ) ) );
	}

	$cadrage = classe_de_cadrage( $chien );

	if ( '' !== $cadrage ) {
		$classes[] = $cadrage;
	}

	return sprintf( '<div class="%s">%s</div>', esc_attr( implode( ' ', $classes ) ), $image );
}

/**
 * La classe de cadrage correspondant au choix de l'éleveuse.
 *
 * Les clés stockées portent des tirets bas — « haut_gauche » — et les classes des tirets. Sans
 * cette conversion, deux cadrages sur cinq produiraient une classe que la feuille de style ne cible
 * pas : la photo retomberait sur le cadrage par défaut, en silence.
 *
 * @param array<string, mixed> $chien Fiche du chien.
 */
function classe_de_cadrage( array $chien ): string {
	$valeur = '';

	if ( isset( $chien['cadrage']['valeur'] ) && is_string( $chien['cadrage']['valeur'] ) ) {
		$valeur = $chien['cadrage']['valeur'];
	}

	$suffixe = sanitize_html_class( str_replace( '_', '-', $valeur ) );

	if ( '' === $suffixe ) {
		return '';
	}

	return 'mtb-grille-chiens__cadre-photo--cadrage-' . $suffixe;
}

/**
 * La photo, rendue par WordPress plutôt que composée à la main.
 *
 * La taille demandée est redimensionnée, jamais recadrée : un carré recadré par WordPress
 * annulerait en silence le choix de cadrage. Le rognage carré appartient à la feuille de style.
 *
 * « loading » et « decoding » sont passés explicitement. C'est ce qui empêche le cœur de désigner
 * la première vignette comme image principale de la page et de lui poser une priorité haute —
 * aucune photo de la grille ne doit en porter.
 *
 * @param array<string, mixed> $photo Photo principale telle que la lecture l'a fournie.
 */
function balisage_photo( array $photo ): string {
	$piece_id = isset( $photo['id'] ) ? (int) $photo['id'] : 0;

	if ( $piece_id <= 0 ) {
		return '';
	}

	/*
	 * La description est passée brute : wp_get_attachment_image() échappe déjà chaque attribut, et
	 * un échappement de notre côté afficherait « d&#039;Ulysse » à l'écran. Une description vide est
	 * transmise telle quelle — la photo est alors décorative, la carte reste nommée par son titre,
	 * et le cœur ne va pas en chercher une autre. Elle n'est jamais fabriquée ni complétée ici.
	 */
	$description = isset( $photo['alt'] ) && is_string( $photo['alt'] ) ? $photo['alt'] : '';

	$image = wp_get_attachment_image(
		$piece_id,
		'medium_large',
		false,
		array(
			'class'    => 'mtb-grille-chiens__photo',
			'alt'      => $description,
			'sizes'    => tailles_image(),
			'loading'  => 'lazy',
			'decoding' => 'async',
		)
	);

	return is_string( $image ) ? $image : '';
}

/**
 * Un groupe : son titre au pluriel, fourni par le serveur, et ses cartes.
 *
 * Le titre n'est jamais rendu sans ses cartes : pas de titre orphelin. La clé du statut est portée
 * par un attribut de données, à l'usage de la feuille de style et des tests — le thème n'en dérive
 * jamais un libellé.
 *
 * @param mixed $groupe Groupe tel que la fonction de lecture l'a construit.
 */
function balisage_groupe( $groupe ): string {
	if ( ! is_array( $groupe ) ) {
		return '';
	}

	$statut = isset( $groupe['statut'] ) && is_string( $groupe['statut'] ) ? $groupe['statut'] : '';
	$titre  = isset( $groupe['libelle'] ) && is_string( $groupe['libelle'] ) ? $groupe['libelle'] : '';
	$chiens = isset( $groupe['chiens'] ) && is_array( $groupe['chiens'] ) ? $groupe['chiens'] : array();
	$cartes = '';

	foreach ( $chiens as $chien ) {
		$cartes .= balisage_carte( $chien );
	}

	if ( '' === $cartes ) {
		return '';
	}

	return sprintf(
		'<section class="mtb-grille-chiens__groupe" data-statut="%s"><h2 class="mtb-grille-chiens__titre-groupe">%s</h2><ul class="mtb-grille-chiens__liste" role="list">%s</ul></section>',
		esc_attr( $statut ),
		esc_html( $titre ),
		$cartes
	);
}

/**
 * L'état vide, visible de l'éleveuse seule.
 *
 * Deux lignes : le nom du composant, puis ce qui manque. La phrase est vraie dans les deux cas
 * qu'on ne peut pas distinguer — aucune fiche publiée, ou des fiches dont aucune n'a de statut — et
 * elle nomme les deux gestes à faire. Aucun chien n'est compté : ce serait interroger un type de
 * contenu que ce module ne possède pas.
 *
 * L'étiquette est écrite en casse normale : la mettre en majuscules serait une décision visuelle, et
 * un lecteur d'écran épellerait.
 *
 * @param string $statut « tous », ou une clé de statut.
 */
function balisage_etat_vide( string $statut ): string {
	return sprintf(
		'<div %s><p class="mtb-etat-vide__composant">%s</p><p class="mtb-etat-vide__phrase">%s</p></div>',
		get_block_wrapper_attributes( array( 'class' => 'mtb-grille-chiens mtb-grille-chiens--vide mtb-etat-vide' ) ),
		esc_html( 'Grille de chiens' ),
		esc_html( phrase_etat_vide( $statut ) )
	);
}

/**
 * La phrase de l'état vide, selon le réglage de l'instance.
 *
 * Le nom du statut est demandé au singulier au module qui détient le vocabulaire, dans sa forme
 * masculine canonique : ni accord, ni pluriel détourné, ni invention. L'appel est sûr, car un
 * statut autre que « tous » n'a pu franchir statut_demande() que si ce vocabulaire est présent.
 *
 * @param string $statut « tous », ou une clé de statut.
 */
function phrase_etat_vide( string $statut ): string {
	if ( 'tous' === $statut ) {
		return "Ce bloc n'affiche rien tant qu'aucune fiche de chien publiée n'a de statut.";
	}

	$libelle = \MTB\Core\Content\Chien\libelle_statut( $statut, '' );

	return "Ce bloc n'affiche rien tant qu'aucune fiche de chien publiée n'a le statut « " . $libelle . ' ».';
}
