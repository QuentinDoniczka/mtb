<?php
/**
 * Rendu du bloc « Encart dernière portée ».
 *
 * Ce fichier est inclus par WordPress au moment du rendu, jamais par le chargeur de l'extension :
 * « $attributes », « $content » et « $block » sont en portée locale. Aucune détection de contexte
 * n'y est faite — pas de « is_admin() », pas de « REST_REQUEST », pas de « is_front_page() » : un
 * « return » nu suffit à ne rien rendre, et l'état vide de l'éditeur est porté par « editeur.js ».
 *
 * Tout échappement se fait ici, au rendu : les fonctions de lecture renvoient des valeurs brutes.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Extension partiellement chargée : jamais d'erreur fatale, jamais d'écran blanc.
if ( ! function_exists( 'mtb_get_derniere_portee' ) ) {
	return;
}

$mtb_portee = mtb_get_derniere_portee();

/*
 * « is_array() » plutôt que « null !== » : une seule garde couvre l'absence de portée publiée et
 * tout retour dégénéré. Aucune portée publiée, donc rien du tout côté public.
 */
if ( ! is_array( $mtb_portee ) ) {
	return;
}

/*
 * Test exact de « la charge utile est complète ». La charge réduite d'une portée protégée par mot
 * de passe ne contient aucun champ du domaine : cette garde est ce qui rend les lectures suivantes
 * sûres. Elle est aujourd'hui inatteignable, la liste des portées écartant déjà les contenus
 * protégés ; elle immunise le bloc si ce filtre était un jour assoupli.
 */
if ( 'ok' !== ( $mtb_portee['etat'] ?? '' ) ) {
	return;
}

$mtb_accroche = trim( (string) ( $attributes['accroche'] ?? '' ) );

// Un titre vide ne produit aucun élément : ni « h2 » vide, ni « aria-labelledby » qui pointe dans le vide.
$mtb_identifiant_accroche = '' === $mtb_accroche ? '' : wp_unique_id( 'mtb-derniere-portee-' );

/*
 * Le suffixe de classe d'état vient d'une liste blanche : la clé stockée ne s'interpole jamais dans
 * un nom de classe. Une valeur inconnue ne donne ni badge ni modificateur, jamais un quatrième état
 * de disponibilité.
 */
$mtb_disponibilite = (string) ( $mtb_portee['disponibilite']['valeur'] ?? '' );
$mtb_etat          = in_array( $mtb_disponibilite, array( 'disponible', 'reserve', 'passee' ), true ) ? $mtb_disponibilite : '';

$mtb_classes = array( 'mtb-derniere-portee' );

if ( '' !== $mtb_etat ) {
	$mtb_classes[] = 'mtb-derniere-portee--' . $mtb_etat;
}

// get_block_wrapper_attributes() échappe lui-même : l'entourer d'esc_attr() doublerait l'échappement.
echo '<section ' . get_block_wrapper_attributes( array( 'class' => implode( ' ', $mtb_classes ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

if ( '' !== $mtb_identifiant_accroche ) {
	echo ' aria-labelledby="' . esc_attr( $mtb_identifiant_accroche ) . '"';
}

echo '>';

if ( '' !== $mtb_accroche ) {
	echo '<h2 class="mtb-derniere-portee__accroche" id="' . esc_attr( $mtb_identifiant_accroche ) . '">' . esc_html( $mtb_accroche ) . '</h2>';
}

/*
 * Le nom de la portée est une valeur de donnée, pas l'intitulé d'une section : en titre HTML, il
 * sauterait un niveau dès que l'accroche est vide.
 */
echo '<p class="mtb-derniere-portee__titre">' . esc_html( (string) ( $mtb_portee['titre_public'] ?? '' ) ) . '</p>';

/*
 * La date se rend sans aucune garde : la portée renvoyée est, par construction, celle dont la date
 * de naissance est renseignée. Une garde ici serait du code mort laissant croire que le cas existe.
 *
 * Le libellé et la date restent dans deux éléments distincts : les joindre reviendrait à composer
 * une chaîne que le serveur livre délibérément en deux morceaux.
 */
echo '<p class="mtb-derniere-portee__identite">';
echo '<span class="mtb-derniere-portee__date">';
echo '<span class="mtb-derniere-portee__etiquette">' . esc_html( (string) ( $mtb_portee['date_naissance']['libelle'] ?? '' ) ) . '</span>';
echo '<span class="mtb-derniere-portee__valeur">' . esc_html( (string) ( $mtb_portee['date_naissance']['affichage'] ?? '' ) ) . '</span>';
echo '</span>';

/*
 * Le badge est imbriqué dans le paragraphe de l'identité, jamais frère : c'est ce qui garantit
 * structurellement qu'il est toujours accompagné de la date, en lecture d'écran comme à l'œil.
 *
 * On teste la valeur stockée, jamais l'affichage : imprimer l'affichage sans ce test produirait un
 * badge « Non renseigné », un état de disponibilité qui n'existe pas.
 */
if ( '' !== $mtb_etat ) {
	echo '<span class="mtb-dispo mtb-dispo--' . esc_attr( $mtb_etat ) . '">' . esc_html( (string) ( $mtb_portee['disponibilite']['affichage'] ?? '' ) ) . '</span>';
}

echo '</p>';

/*
 * L'effectif est imprimé tel quel, jamais recomposé depuis les deux compteurs : le serveur l'accorde
 * lui-même et ne le fournit pas du tout quand les deux compteurs sont vides. Absent, l'élément
 * n'existe pas — jamais « 0 mâle », jamais « Non renseigné ».
 */
$mtb_effectif = (string) ( $mtb_portee['effectif_texte'] ?? '' );

if ( '' !== $mtb_effectif ) {
	echo '<p class="mtb-derniere-portee__effectif">' . esc_html( $mtb_effectif ) . '</p>';
}

/*
 * La photo peut valoir null : le test porte sur « is_array », jamais sur la présence d'un
 * identifiant. Sans photo, l'emplacement n'existe pas — aucun trou, aucune réserve.
 */
$mtb_photo = $mtb_portee['photo'] ?? null;

if ( is_array( $mtb_photo ) ) {
	$mtb_piece_jointe = (int) ( $mtb_photo['id'] ?? 0 );

	/*
	 * L'alternative textuelle est passée explicitement : sans elle, le cœur relit celle du fichier
	 * et rendrait « alt » vide quand la photo n'en a pas, alors que le serveur a construit un repli
	 * sur le nom de la portée pour qu'aucune photo ne part sans alternative. Elle n'est pas
	 * pré-échappée, wp_get_attachment_image() échappe tous ses attributs.
	 */
	$mtb_image = (string) wp_get_attachment_image(
		$mtb_piece_jointe,
		'large',
		false,
		array(
			'alt'      => (string) ( $mtb_photo['alt'] ?? '' ),
			'loading'  => 'lazy',
			'decoding' => 'async',
			'sizes'    => '(min-width: 40rem) 576px, 100vw',
		)
	);

	if ( '' !== $mtb_image ) {
		/*
		 * Largeur réelle du fichier : un fait mesuré, pas une règle visuelle. Le plafonnement d'une
		 * photo de définition insuffisante est impossible en CSS seul, aucune requête de conteneur
		 * ne connaissant la largeur du fichier. Le facteur et le centrage restent dans la feuille du
		 * thème ; si la largeur n'est pas résoluble, l'attribut est omis et le CSS garde son repli.
		 */
		$mtb_source  = wp_get_attachment_image_src( $mtb_piece_jointe, 'full' );
		$mtb_largeur = is_array( $mtb_source ) ? (int) ( $mtb_source[1] ?? 0 ) : 0;
		$mtb_style   = $mtb_largeur > 0 ? ' style="' . esc_attr( '--photo-largeur-naturelle:' . $mtb_largeur . 'px' ) . '"' : '';

		echo '<div class="mtb-photo mtb-derniere-portee__photo"' . $mtb_style . '>' . $mtb_image . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

/*
 * Le lien porte son seul libellé, sans nom accessible composé : le nom de la portée est dans la même
 * section, deux éléments au-dessus. Sans adresse consultable, le paragraphe n'existe pas : jamais un
 * lien mort.
 */
$mtb_lien = (string) ( $mtb_portee['lien'] ?? '' );

if ( '' !== $mtb_lien ) {
	echo '<p class="mtb-derniere-portee__action"><a class="mtb-derniere-portee__lien" href="' . esc_url( $mtb_lien ) . '">Voir la portée</a></p>';
}

echo '</section>';
