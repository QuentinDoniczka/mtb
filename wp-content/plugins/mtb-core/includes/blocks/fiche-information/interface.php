<?php
/**
 * Composant « Fiche d'information » — balisage réutilisable hors d'un rendu de bloc.
 *
 * Espace de noms global, volontairement : le thème n'écrit jamais « MTB\ », c'est ce qui rend la
 * frontière thème / extension vérifiable d'un simple grep. Aucun hook n'est posé ici.
 *
 * Cette fonction ne lit AUCUNE donnée — ni portée, ni chien, ni résultat. C'est l'appelant qui lit,
 * par les fonctions « mtb_get_* », et qui décide quoi rendre : un composant de mise en page expose
 * son balisage, jamais une requête.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'mtb_fiche_information_balisage' ) ) {
	/**
	 * Rend une fiche d'information à partir de valeurs fournies par l'appelant.
	 *
	 * Les fiches Portée, Chien et Résultat se remplissent sur l'écran classique : aucun bloc n'y est
	 * insérable. Un gabarit qui doit rendre le même motif — un titre, de la prose, une photo cadrée
	 * et cernée — passe par ici plutôt que de réécrire un second balisage côté thème, qui dériverait
	 * de celui-ci au premier ajustement.
	 *
	 * Toute clé inconnue de $arguments est écartée, jamais rendue.
	 *
	 * @param array<string,mixed> $arguments {
	 *     Valeurs à rendre, toutes facultatives.
	 *
	 *     @type string   $titre             Titre, balisage en ligne admis.
	 *     @type string   $niveau_titre      « h2 » ou « h3 » ; toute autre valeur retombe sur « h2 ».
	 *     @type string   $prose             Balisage de paragraphes, passé à wp_kses_post().
	 *     @type int      $photo_id          Identifiant de la pièce jointe, 0 pour aucune photo.
	 *     @type string   $photo_description Texte alternatif de la photo, tel que saisi.
	 *     @type string   $photo_legende     Légende de la photo, telle que saisie.
	 *     @type string   $cadrage           L'une des cinq clés de cadrage, « centre » à défaut.
	 *     @type string   $position_photo    « dessus » ou « dessous », « dessus » à défaut.
	 *     @type string[] $classes           Classes ajoutées à la racine, après « mtb-fiche-information ».
	 * }
	 *
	 * @return string Balisage complet, racine comprise, ou chaîne vide si rien n'est à afficher.
	 */
	function mtb_fiche_information_balisage( array $arguments = array() ): string {
		$defauts = array(
			'titre'             => '',
			'niveau_titre'      => 'h2',
			'prose'             => '',
			'photo_id'          => 0,
			'photo_description' => '',
			'photo_legende'     => '',
			'cadrage'           => 'centre',
			'position_photo'    => 'dessus',
			'classes'           => array(),
		);

		/*
		 * array_intersect_key et non wp_parse_args : une clé inconnue est écartée ici même, donc
		 * elle ne peut atteindre aucune sortie. Chaque valeur reçue est ensuite ramenée au type
		 * attendu sans avertissement, un gabarit ne devant jamais faire tomber une page.
		 */
		$valeurs = array_merge( $defauts, array_intersect_key( $arguments, $defauts ) );

		$titre = \MTB\Core\Blocks\FicheInformation\titre(
			is_scalar( $valeurs['titre'] ) ? (string) $valeurs['titre'] : '',
			is_scalar( $valeurs['niveau_titre'] ) ? (string) $valeurs['niveau_titre'] : ''
		);

		$figure = \MTB\Core\Blocks\FicheInformation\figure(
			\MTB\Core\Blocks\FicheInformation\identifiant_photo( $valeurs['photo_id'] ),
			is_scalar( $valeurs['photo_description'] ) ? (string) $valeurs['photo_description'] : '',
			is_scalar( $valeurs['photo_legende'] ) ? (string) $valeurs['photo_legende'] : '',
			is_scalar( $valeurs['cadrage'] ) ? (string) $valeurs['cadrage'] : ''
		);

		$position = \MTB\Core\Blocks\FicheInformation\position_retenue(
			is_scalar( $valeurs['position_photo'] ) ? (string) $valeurs['position_photo'] : ''
		);

		/*
		 * wp_kses_post sur la prose, et c'est le seul écart de traitement avec le rendu du bloc :
		 * là, « $content » est déjà rendu et échappé par le cœur, bloc enfant par bloc enfant, et
		 * kses amputerait un balisage légitime. Ici la prose vient d'un champ de saisie de fiche,
		 * dont rien ne garantit le passage par le cœur : la barrière est nécessaire, et son domicile
		 * est l'entrée. Deux provenances, deux garanties, pas un double standard caché.
		 */
		$prose = wp_kses_post( is_scalar( $valeurs['prose'] ) ? (string) $valeurs['prose'] : '' );

		$corps = \MTB\Core\Blocks\FicheInformation\contenu( $titre, $figure, $prose, $position );

		// Rien à afficher : aucun élément racine, pas même un conteneur vide.
		if ( '' === $corps ) {
			return '';
		}

		$classes = array( 'mtb-fiche-information' );

		if ( is_array( $valeurs['classes'] ) ) {
			foreach ( $valeurs['classes'] as $classe ) {
				if ( is_string( $classe ) && '' !== $classe ) {
					$classes[] = $classe;
				}
			}
		}

		// Émis pour la seule testabilité, et seulement si une photo est rendue : l'ordre visuel est
		// porté par l'ordre du DOM, jamais par le CSS.
		$attribut_position = '' === $figure
			? ''
			: sprintf( ' data-position="%s"', esc_attr( $position ) );

		/*
		 * La racine est construite littéralement : get_block_wrapper_attributes() ne rendrait pas
		 * ici ce qu'on croit, un gabarit de thème n'étant pas dans un contexte de rendu de bloc.
		 */
		return sprintf(
			'<div class="%1$s"%2$s>%3$s</div>',
			esc_attr( implode( ' ', $classes ) ),
			$attribut_position,
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Titre, figure et légende échappés par rendu.php ; la prose est passée à wp_kses_post ci-dessus.
			$corps
		);
	}
}
