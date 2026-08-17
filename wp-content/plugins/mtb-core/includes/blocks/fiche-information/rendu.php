<?php
/**
 * Composant « Fiche d'information » — fonctions d'aide du rendu public.
 *
 * Ce fichier est inclus UNE SEULE FOIS, par « bootstrap.php ». Il est le seul du module à déclarer
 * des fonctions : « render.php » est inclus une fois par fiche présente sur la page.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Blocks\FicheInformation;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dit si une valeur saisie est vide, une fois son balisage et ses espaces ignorés.
 *
 * « wp_strip_all_tags » est proscrit sur une valeur recopiée (décision 20 de docs/ETAT.md) parce
 * qu'il viderait en silence une valeur commençant par « < ». Ici il n'opère que sur une copie
 * jetée dans la même expression : la valeur émise en sortie reste l'originale, intacte.
 *
 * Cas couvert au passage : le paragraphe vide du gabarit produit « <p></p> », donc une prose
 * considérée vide, donc une fiche fraîchement insérée qui ne rend rien côté public.
 *
 * @param string $valeur Valeur telle qu'elle est enregistrée.
 *
 * @return bool Vrai si elle ne porte aucun texte.
 */
function est_vide( string $valeur ): bool {
	return '' === trim( wp_strip_all_tags( $valeur ) );
}

/**
 * Retient la balise du titre dans une liste blanche littérale.
 *
 * Toute autre valeur retombe sur « h2 » : le titre de niveau 1 appartient au titre de la page.
 *
 * @param string $niveau Valeur enregistrée du réglage « Niveau du titre ».
 *
 * @return string « h2 » ou « h3 ».
 */
function balise_titre( string $niveau ): string {
	return in_array( $niveau, array( 'h2', 'h3' ), true ) ? $niveau : 'h2';
}

/**
 * Retient la valeur de cadrage dans la liste blanche gelée du modèle de contenu.
 *
 * Les cinq clés sont celles de includes/content/chien/choix.php : un seul vocabulaire pour un seul
 * réglage. Elles sont émises verbatim, sans transformation.
 *
 * @param string $cadrage Valeur enregistrée du réglage « Cadrage de la photo ».
 *
 * @return string L'une des cinq clés, « centre » à défaut.
 */
function cadrage_retenu( string $cadrage ): string {
	$connus = array( 'haut_gauche', 'haut', 'centre', 'haut_droite', 'bas' );

	return in_array( $cadrage, $connus, true ) ? $cadrage : 'centre';
}

/**
 * Retient la position de la photo dans une liste blanche littérale.
 *
 * @param string $position Valeur enregistrée du réglage « Position de la photo ».
 *
 * @return string « dessus » ou « dessous ».
 */
function position_retenue( string $position ): string {
	return in_array( $position, array( 'dessus', 'dessous' ), true ) ? $position : 'dessus';
}

/**
 * Vérifie qu'une photo choisie existe encore et renvoie son identifiant.
 *
 * Une photo retirée de la médiathèque laisse l'identifiant enregistré : l'emplacement doit alors
 * ne pas exister du tout, plutôt que de produire un trou ou un avertissement.
 *
 * @param mixed $valeur Valeur enregistrée de l'attribut de photo.
 *
 * @return int Identifiant de la pièce jointe, ou 0 si elle n'existe plus.
 */
function identifiant_photo( $valeur ): int {
	$identifiant = absint( is_scalar( $valeur ) ? $valeur : 0 );

	if ( 0 === $identifiant ) {
		return 0;
	}

	return 'attachment' === get_post_type( $identifiant ) ? $identifiant : 0;
}

/**
 * Compose l'élément de titre, ou rien du tout.
 *
 * @param string $titre  Titre saisi, balisage en ligne compris.
 * @param string $niveau Valeur enregistrée du réglage « Niveau du titre ».
 *
 * @return string Balisage du titre, chaîne vide si le titre n'a pas été saisi.
 */
function titre( string $titre, string $niveau ): string {
	if ( est_vide( $titre ) ) {
		return '';
	}

	$balise = balise_titre( $niveau );

	return sprintf(
		'<%1$s class="%2$s">%3$s</%1$s>',
		$balise,
		esc_attr( 'mtb-fiche-information__titre' ),
		// RichText autorise « strong », « em » et « a » : esc_html afficherait les balises en clair.
		wp_kses_post( $titre )
	);
}

/**
 * Compose la figure — emplacement de la photo, puis légende — ou rien du tout.
 *
 * @param int    $photo_id    Identifiant déjà vérifié de la pièce jointe.
 * @param string $description Description de la photo, telle que saisie.
 * @param string $legende     Légende de la photo, telle que saisie.
 * @param string $cadrage     Valeur enregistrée du réglage « Cadrage de la photo ».
 *
 * @return string Balisage de la figure, chaîne vide s'il n'y a pas de photo à rendre.
 */
function figure( int $photo_id, string $description, string $legende, string $cadrage ): string {
	if ( 0 === $photo_id ) {
		return '';
	}

	$image = wp_get_attachment_image(
		$photo_id,
		'large',
		false,
		array(
			'class' => 'mtb-fiche-information__image',
			// Passée brute : wp_get_attachment_image applique esc_attr, l'échapper ici doublerait
			// l'encodage et ferait apparaître « &amp; » dans une description contenant « & ».
			'alt'   => $description,
		)
	);

	if ( '' === $image ) {
		return '';
	}

	/*
	 * Le balisage de l'image est repris tel que le cœur l'a produit, sans passer par wp_kses_post.
	 * Vérification V6 du contrat, faite dans la version installée : la liste blanche de kses
	 * (wp-includes/kses.php:212-224) n'admet sur « img » ni « srcset », ni « sizes », ni
	 * « decoding » — les trois disparaissaient, alors que le contrat §5 et MASTER §6.9 les exigent.
	 * Aucune valeur saisie n'entre ici sans passer par le cœur : la taille et la classe sont des
	 * littéraux, l'identifiant est vérifié, et « alt » est échappé par wp_get_attachment_image.
	 */
	$emplacement = sprintf(
		'<div class="%1$s" data-cadrage="%2$s">%3$s</div>',
		esc_attr( 'mtb-fiche-information__photo' ),
		esc_attr( cadrage_retenu( $cadrage ) ),
		$image
	);

	$balisage_legende = '';

	if ( ! est_vide( $legende ) ) {
		$balisage_legende = sprintf(
			'<figcaption class="%1$s">%2$s</figcaption>',
			esc_attr( 'mtb-fiche-information__legende' ),
			esc_html( $legende )
		);
	}

	return sprintf(
		'<figure class="%1$s">%2$s%3$s</figure>',
		esc_attr( 'mtb-fiche-information__figure' ),
		$emplacement,
		$balisage_legende
	);
}

/**
 * Compose le contenu de la fiche — titre, figure et prose — dans l'ordre du balisage gelé.
 *
 * Seule et unique source de la composition : « render.php » l'enveloppe dans
 * get_block_wrapper_attributes(), mtb_fiche_information_balisage() l'enveloppe dans le div qu'elle
 * construit. Deux entrées, un seul balisage — sans quoi l'une des deux dériverait au premier
 * ajustement.
 *
 * Le titre est toujours en premier : « photo au-dessus du texte » veut dire au-dessus des
 * paragraphes, jamais au-dessus du titre. La position de la photo se joue dans l'ordre du DOM, qui
 * est l'ordre visuel et l'ordre de lecture : aucune propriété « order » n'est nécessaire ni voulue.
 *
 * La prose arrive prête à être émise : chaque entrée décide de sa propre barrière, parce que les
 * deux provenances n'offrent pas les mêmes garanties.
 *
 * @param string $titre    Balisage du titre, chaîne vide s'il n'y en a pas.
 * @param string $figure   Balisage de la figure, chaîne vide s'il n'y en a pas.
 * @param string $prose    Prose déjà prête, chaîne vide s'il n'y en a pas.
 * @param string $position Valeur du réglage « Position de la photo » ; ramenée ici même à la liste
 *                         blanche, pour qu'aucune entrée ne dépende de l'ordre des appels.
 *
 * @return string Balisage intérieur de la fiche, chaîne vide si rien n'a été saisi.
 */
function contenu( string $titre, string $figure, string $prose, string $position ): string {
	$bloc_prose = '';

	if ( ! est_vide( $prose ) ) {
		$bloc_prose = '<div class="' . esc_attr( 'mtb-fiche-information__prose' ) . '">'
			. $prose
			. '</div>';
	}

	// Rien de saisi : l'appelant n'émet aucun élément racine. Aucun trou, aucune réserve.
	if ( '' === $titre && '' === $figure && '' === $bloc_prose ) {
		return '';
	}

	if ( 'dessous' === position_retenue( $position ) ) {
		return $titre . $bloc_prose . $figure;
	}

	return $titre . $figure . $bloc_prose;
}
