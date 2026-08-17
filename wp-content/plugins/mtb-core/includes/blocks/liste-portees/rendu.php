<?php
/**
 * Balisage du composant « Liste de portées ».
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Blocks\ListePortees;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rend la liste des portées : structure et crochets de classes, aucune décision visuelle.
 *
 * Le bloc n'écrit jamais rien en base : aucun nonce n'est donc posé ici, il n'y aurait pas de sujet à
 * protéger. Le seul chemin privilégié qui atteint ce code est la route de rendu de blocs du cœur, dont
 * le cœur porte lui-même la capacité et le jeton.
 */
final class Rendu {

	/**
	 * Rend le bloc entier.
	 *
	 * @param array $attributs Attributs du bloc, tels que l'éditeur les a enregistrés.
	 *
	 * @return string Balisage complet, chaîne vide quand rien n'est à afficher.
	 */
	public static function bloc( array $attributs ): string {
		/*
		 * Le module des portées peut manquer : dossier désactivé, dépôt partiel, ou exception isolée
		 * par le chargeur. La page perd alors sa liste, rien de plus, et aucun message n'est inventé
		 * au visiteur.
		 */
		if ( ! function_exists( 'mtb_get_portees' ) ) {
			return '';
		}

		$reglages  = self::normaliser( $attributs );
		$arguments = array( 'nombre' => -1 );

		if ( '' !== $reglages['annee'] ) {
			$arguments['annee'] = $reglages['annee'];
		}

		/*
		 * Toutes les portées sont demandées, puis coupées ici : sans le total retenu, le bloc ne
		 * saurait pas qu'il tronque, donc ne saurait pas s'il doit offrir le lien vers l'index. La
		 * sélection et l'ordre restent entièrement produits par le module propriétaire.
		 */
		$portees = mtb_get_portees( $arguments );

		if ( array() === $portees ) {
			return self::liste_vide( $reglages['annee'] );
		}

		$total    = count( $portees );
		$tronquee = $reglages['nombre'] > 0 && $reglages['nombre'] < $total;

		if ( $tronquee ) {
			$portees = array_slice( $portees, 0, $reglages['nombre'] );
		}

		$entrees = '';

		foreach ( $portees as $portee ) {
			$entrees .= self::entree( is_array( $portee ) ? $portee : array() );
		}

		$interieur = '<ul class="mtb-liste-portees__liste" role="list">' . "\n" . $entrees . '</ul>' . "\n";

		if ( $tronquee ) {
			$interieur .= self::sortie();
		}

		return self::enveloppe( $interieur, false );
	}

	/**
	 * Ramène les deux attributs à des valeurs sûres.
	 *
	 * Un attribut vient de l'éditeur, donc d'une soumission : la route de rendu de blocs du cœur
	 * accepte n'importe quel corps. Aucune valeur n'est présumée propre.
	 *
	 * @param array $attributs Attributs bruts.
	 *
	 * @return array{nombre:int,annee:string} « nombre » à -1 pour toutes les portées, « annee » vide
	 *                                        pour aucun filtre.
	 */
	public static function normaliser( array $attributs ): array {
		return array(
			'nombre' => self::nombre( $attributs['nombre'] ?? null ),
			'annee'  => self::annee( $attributs['annee'] ?? null ),
		);
	}

	/**
	 * Lit le nombre de portées demandé.
	 *
	 * Il n'existe aucun moyen de demander zéro portée : pour n'en afficher aucune, on retire le bloc.
	 * Toute valeur qui n'est pas une suite de chiffres valant au moins un signifie donc « toutes ».
	 *
	 * @param mixed $brut Valeur de l'attribut.
	 *
	 * @return int Nombre demandé, ou -1 pour toutes les portées.
	 */
	private static function nombre( $brut ): int {
		$valeur = self::texte( $brut );

		if ( 1 !== preg_match( '/^\d+$/', $valeur ) ) {
			return -1;
		}

		$nombre = (int) $valeur;

		return $nombre < 1 ? -1 : $nombre;
	}

	/**
	 * Lit l'année demandée.
	 *
	 * L'année est normalisée avant l'appel de lecture, et non après : le message d'état vide dépend de
	 * la validité du filtre. Une année mal formée est déjà ignorée par la fonction de lecture, et un
	 * rendu qui croirait le filtre actif annoncerait « Aucune portée pour cette année. » au-dessus de
	 * la liste complète.
	 *
	 * @param mixed $brut Valeur de l'attribut.
	 *
	 * @return string Année à quatre chiffres, ou chaîne vide pour aucun filtre.
	 */
	private static function annee( $brut ): string {
		$valeur = trim( self::texte( $brut ) );

		return 1 === preg_match( '/^\d{4}$/', $valeur ) ? $valeur : '';
	}

	/**
	 * Rend le cas où aucune portée ne correspond.
	 *
	 * Distinguer « aucune portée pour cette année » de « rien n'est encore publié » appartient au
	 * serveur : le thème n'a jamais à trancher, sous peine de porter une règle métier. Quand rien
	 * n'est publié, le composant ne s'affiche pas du tout et la page ne réserve aucune place.
	 *
	 * @param string $annee Année normalisée, chaîne vide si aucun filtre.
	 *
	 * @return string Balisage de l'état vide, ou chaîne vide.
	 */
	private static function liste_vide( string $annee ): string {
		if ( '' === $annee ) {
			return '';
		}

		// Sonde : une seule portée suffit à savoir que le site en publie, et que le filtre est en cause.
		if ( array() === mtb_get_portees( array( 'nombre' => 1 ) ) ) {
			return '';
		}

		$interieur = '<p class="mtb-liste-portees__vide">' . esc_html( 'Aucune portée pour cette année.' ) . '</p>' . "\n"
			. self::sortie();

		return self::enveloppe( $interieur, true );
	}

	/**
	 * Enveloppe le contenu du bloc.
	 *
	 * « alignwide » est écrit en dur, et c'est la seule classe de mise en page que l'extension émette :
	 * le système de design range les listes de portées dans le canal large, et le thème ne peut pas
	 * obtenir ce placement depuis sa feuille de bloc. L'extension transmet une affectation du système
	 * de design, elle n'invente pas une règle visuelle — l'éleveuse ne peut pas la défaire, le bloc
	 * n'offrant aucun réglage d'alignement.
	 *
	 * @param string $interieur Balisage intérieur.
	 * @param bool   $vide      Vrai pour l'état « année sans résultat ».
	 *
	 * @return string Balisage enveloppé.
	 */
	private static function enveloppe( string $interieur, bool $vide ): string {
		$classes = $vide
			? 'mtb-liste-portees mtb-liste-portees--vide alignwide'
			: 'mtb-liste-portees alignwide';

		return '<div ' . get_block_wrapper_attributes( array( 'class' => $classes ) ) . '>' . "\n"
			. $interieur
			. '</div>' . "\n";
	}

	/**
	 * Rend une entrée de la liste.
	 *
	 * @param array $portee Portée hydratée.
	 *
	 * @return string Balisage de l'entrée.
	 */
	private static function entree( array $portee ): string {
		return '<li class="mtb-liste-portees__entree">' . "\n"
			. self::vignette( $portee )
			. '<div class="mtb-liste-portees__corps">' . "\n"
			. self::nom( $portee ) . "\n"
			. self::meta( $portee )
			. '</div>' . "\n"
			. '</li>' . "\n";
	}

	/**
	 * Rend la vignette, si et seulement si une image exploitable existe.
	 *
	 * Une photo présente garantit que le contenu du fichier joint existe, pas que le fichier soit là :
	 * l'image est donc construite d'abord, et le cadre n'est émis que si elle a produit du balisage.
	 * Sans cette précaution, une photo manquante laisserait un emplacement vide.
	 *
	 * @param array $portee Portée hydratée.
	 *
	 * @return string Balisage du cadre, ou chaîne vide.
	 */
	private static function vignette( array $portee ): string {
		$photo = $portee['photo'] ?? null;

		// La photo vaut null quand il n'y en a pas ; la galerie, elle, vaut un tableau vide.
		if ( ! is_array( $photo ) ) {
			return '';
		}

		$piece_jointe = (int) self::texte( $photo['id'] ?? '' );

		if ( $piece_jointe <= 0 ) {
			return '';
		}

		$image = wp_get_attachment_image(
			$piece_jointe,
			'medium',
			false,
			array(
				'class' => 'mtb-liste-portees__image',
				// Alternative passée brute : wp_get_attachment_image() échappe ses attributs, et la
				// pré-échapper ferait lire « &#039; » à un lecteur d'écran.
				'alt'   => self::texte( $photo['alt'] ?? '' ),
				/*
				 * « 144px » et la largeur de vignette « 9rem » de
				 * assets/css/blocs/mtb-liste-portees.css sont UNE valeur écrite dans DEUX fichiers.
				 * Changer l'une sans l'autre livre un srcset qui mentira au navigateur, sans le
				 * moindre symptôme visible.
				 */
				'sizes' => '144px',
			)
		);

		if ( ! is_string( $image ) || '' === $image ) {
			return '';
		}

		return '<figure class="mtb-liste-portees__vignette mtb-photo">' . $image . '</figure>' . "\n";
	}

	/**
	 * Rend le nom de la portée : un lien quand la fiche est consultable, un texte sinon.
	 *
	 * Le texte est le titre public fourni fini par le serveur, jamais « Voir la portée » : dans une
	 * liste de vingt-sept entrées, vingt-sept liens homonymes n'ont aucun nom accessible distinctif.
	 *
	 * @param array $portee Portée hydratée.
	 *
	 * @return string Balisage du nom.
	 */
	private static function nom( array $portee ): string {
		$titre = esc_html( self::texte( $portee['titre_public'] ?? '' ) );
		$lien  = esc_url( self::texte( $portee['lien'] ?? '' ) );

		// Jamais un lien mort : sans adresse consultable, le nom reste du texte.
		if ( '' === $lien ) {
			return '<span class="mtb-liste-portees__nom">' . $titre . '</span>';
		}

		return '<a class="mtb-liste-portees__lien" href="' . $lien . '">' . $titre . '</a>';
	}

	/**
	 * Rend la ligne secondaire : date, effectif, disponibilité.
	 *
	 * Les éléments sont séparés par une fin de ligne, qui vaut une espace typographique : sans elle,
	 * un rendu sans feuille de style collerait la date et l'effectif.
	 *
	 * @param array $portee Portée hydratée.
	 *
	 * @return string Balisage de la ligne.
	 */
	private static function meta( array $portee ): string {
		$parties  = array( self::date( $portee ) );
		$effectif = self::texte( $portee['effectif_texte'] ?? '' );

		/*
		 * L'effectif est une chaîne nue, et il est vide quand les deux compteurs le sont : on n'écrit
		 * jamais « 0 mâle » quand on ne sait pas.
		 */
		if ( '' !== $effectif ) {
			$parties[] = '<span class="mtb-liste-portees__effectif">' . esc_html( $effectif ) . '</span>';
		}

		$badge = self::badge( $portee );

		if ( '' !== $badge ) {
			$parties[] = $badge;
		}

		return '<p class="mtb-liste-portees__meta">' . "\n"
			. implode( "\n", $parties ) . "\n"
			. '</p>' . "\n";
	}

	/**
	 * Rend la date de naissance.
	 *
	 * Une date absente s'imprime « Non renseigné », tel que le serveur le fournit : l'omettre rendrait
	 * une portée sans date indiscernable d'un défaut d'affichage. L'étiquette, elle, disparaît alors —
	 * « Née le Non renseigné » n'est pas du français.
	 *
	 * @param array $portee Portée hydratée.
	 *
	 * @return string Balisage de la date.
	 */
	private static function date( array $portee ): string {
		$champ = isset( $portee['date_naissance'] ) && is_array( $portee['date_naissance'] )
			? $portee['date_naissance']
			: array();

		$valeur    = self::texte( $champ['valeur'] ?? '' );
		$libelle   = self::texte( $champ['libelle'] ?? '' );
		$affichage = esc_html( self::texte( $champ['affichage'] ?? '' ) );

		if ( '' === $valeur ) {
			return '<span class="mtb-liste-portees__date">'
				. '<span class="mtb-liste-portees__date-valeur">' . $affichage . '</span>'
				. '</span>';
		}

		$etiquette = '' === $libelle
			? ''
			: '<span class="mtb-liste-portees__etiquette">' . esc_html( $libelle ) . '</span> ';

		// La valeur brute AAAA-MM-JJ part telle quelle dans « datetime » : aucun reformatage.
		return '<span class="mtb-liste-portees__date">'
			. $etiquette
			. '<time class="mtb-liste-portees__date-valeur" datetime="' . esc_attr( $valeur ) . '">'
			. $affichage
			. '</time>'
			. '</span>';
	}

	/**
	 * Rend le badge de disponibilité.
	 *
	 * La décision se prend sur la valeur stockée, jamais sur l'affichage : l'affichage vaut « Non
	 * renseigné » quand rien n'est choisi, et l'imprimer créerait un quatrième état de disponibilité,
	 * qui n'a ni forme, ni couleur, ni preuve de contraste.
	 *
	 * @param array $portee Portée hydratée.
	 *
	 * @return string Balisage du badge, ou chaîne vide.
	 */
	private static function badge( array $portee ): string {
		$champ = isset( $portee['disponibilite'] ) && is_array( $portee['disponibilite'] )
			? $portee['disponibilite']
			: array();

		$valeur = self::texte( $champ['valeur'] ?? '' );

		if ( '' === $valeur ) {
			return '';
		}

		// La valeur appartient à une liste close ; elle est tout de même échappée.
		return '<span class="mtb-liste-portees__dispo mtb-dispo mtb-dispo--' . esc_attr( $valeur ) . '">'
			. esc_html( self::texte( $champ['affichage'] ?? '' ) )
			. '</span>';
	}

	/**
	 * Rend le lien vers l'index des portées.
	 *
	 * @return string Balisage du lien, ou chaîne vide si l'index n'est pas atteignable.
	 */
	private static function sortie(): string {
		$archive = get_post_type_archive_link( 'mtb_portee' );
		$adresse = is_string( $archive ) ? esc_url( $archive ) : '';

		if ( '' === $adresse ) {
			return '';
		}

		return '<p class="mtb-liste-portees__sortie">'
			. '<a class="mtb-liste-portees__lien-index" href="' . $adresse . '">'
			. esc_html( 'Toutes les portées' )
			. '</a>'
			. '</p>' . "\n";
	}

	/**
	 * Ramène une valeur lue à une chaîne, sans rien reformater.
	 *
	 * @param mixed $valeur Valeur lue, d'un attribut ou d'une fonction de lecture.
	 *
	 * @return string Chaîne, vide si la donnée n'en est pas une.
	 */
	private static function texte( $valeur ): string {
		return is_scalar( $valeur ) ? (string) $valeur : '';
	}
}
