<?php
/**
 * Construction des fiches Chien renvoyées au thème.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Query\Chien;

use function MTB\Core\Content\Chien\cadrage_par_defaut;
use function MTB\Core\Content\Chien\cadrages;
use function MTB\Core\Content\Chien\champs_sante;
use function MTB\Core\Content\Chien\champs_titres;
use function MTB\Core\Content\Chien\libelle_date;
use function MTB\Core\Content\Chien\libelle_statut;
use function MTB\Core\Content\Chien\libelle_statut_pluriel;
use function MTB\Core\Content\Chien\non_renseigne;
use function MTB\Core\Content\Chien\oui_non;
use function MTB\Core\Content\Chien\sexes;
use function MTB\Core\Content\Chien\statuts;
use function MTB\Core\Content\Chien\varietes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fiche complète d'un chien, ou null si l'identifiant ne désigne pas une fiche publiée.
 *
 * @param int $chien_id Identifiant de la fiche, 0 pour la fiche affichée.
 *
 * @return array<string, mixed>|null
 */
function fiche( int $chien_id ): ?array {
	$post = 0 === $chien_id ? get_post() : get_post( $chien_id );

	if ( ! $post instanceof \WP_Post ) {
		return null;
	}

	if ( 'mtb_chien' !== $post->post_type || 'publish' !== $post->post_status ) {
		return null;
	}

	/*
	 * Contenu protégé : la fiche existe, mais aucun champ du domaine n'est renseigné tant que le
	 * mot de passe n'a pas été saisi. Rien ne fuit par la fonction de lecture.
	 */
	if ( '' !== $post->post_password && post_password_required( $post ) ) {
		return squelette( $post, 'page_protegee' );
	}

	return fiche_complete( $post );
}

/**
 * Fiche vide : toutes les clés du contrat, aucune donnée.
 *
 * Point unique de définition de la forme du retour — c'est ce qui garantit qu'aucune clé ne
 * manque jamais, y compris sur une fiche protégée ou entièrement vide.
 *
 * @param \WP_Post $post Fiche concernée.
 * @param string   $etat « normal » ou « page_protegee ».
 *
 * @return array<string, mixed>
 */
function squelette( \WP_Post $post, string $etat ): array {
	$vide  = champ_vide();
	$titre = nom_usage( $post );

	return array(
		'id'                => (int) $post->ID,
		'etat'              => $etat,
		'nom_usage'         => '' === $titre ? non_renseigne() : $titre,
		'nom_complet'       => $vide,
		'sexe'              => $vide,
		'variete'           => $vide,
		'date_naissance'    => array_merge( $vide, array( 'libelle' => libelle_date( 'naissance', '' ) ) ),
		'date_deces'        => array_merge( $vide, array( 'libelle' => libelle_date( 'deces', '' ) ) ),
		'statut'            => $vide,
		'taille'            => $vide,
		'couleur'           => $vide,
		'masque'            => $vide,
		'genetique_robe'    => $vide,
		'sante'             => section_vide( champs_sante() ),
		'sante_renseignee'  => false,
		'autres_tests'      => array(),
		'titres'            => section_vide( champs_titres() ),
		'titres_renseignes' => false,
		'autres_titres'     => array(),
		'pere'              => parent_vide( 'pere' ),
		'mere'              => parent_vide( 'mere' ),
		'photo_principale'  => null,
		'cadrage'           => champ_liste( cadrage_par_defaut(), cadrages() ),
		'galerie'           => array(),
		'pedigree'          => null,
	);
}

/**
 * Fiche renseignée d'un chien publié et lisible.
 *
 * @param \WP_Post $post Fiche concernée.
 *
 * @return array<string, mixed>
 */
function fiche_complete( \WP_Post $post ): array {
	$id    = (int) $post->ID;
	$fiche = squelette( $post, 'normal' );
	$sexe  = valeur( $id, '_mtb_sexe' );
	$titre = nom_usage( $post );

	$fiche['nom_complet']    = champ_texte( valeur( $id, '_mtb_nom_complet' ) );
	$fiche['sexe']           = champ_liste( $sexe, sexes() );
	$fiche['variete']        = champ_liste( valeur( $id, '_mtb_variete' ), varietes() );
	$fiche['date_naissance'] = champ_date( valeur( $id, '_mtb_date_naissance' ), libelle_date( 'naissance', $sexe ) );
	$fiche['date_deces']     = champ_date( valeur( $id, '_mtb_date_deces' ), libelle_date( 'deces', $sexe ) );
	$fiche['taille']         = champ_texte( valeur( $id, '_mtb_taille' ) );
	$fiche['couleur']        = champ_texte( valeur( $id, '_mtb_couleur' ) );
	$fiche['masque']         = champ_texte( valeur( $id, '_mtb_masque' ) );
	$fiche['genetique_robe'] = champ_texte( valeur( $id, '_mtb_genetique_robe' ) );

	$statut          = valeur( $id, '_mtb_statut' );
	$statuts         = statuts();
	$fiche['statut'] = array(
		'valeur'    => isset( $statuts[ $statut ] ) ? $statut : '',
		'affichage' => libelle_statut( $statut, $sexe ),
	);

	$fiche['sante']         = section( $id, champs_sante() );
	$fiche['autres_tests']  = lignes( valeur( $id, '_mtb_autres_tests' ) );
	$fiche['titres']        = section( $id, champs_titres() );
	$fiche['autres_titres'] = lignes( valeur( $id, '_mtb_autres_titres' ) );

	$fiche['sante_renseignee']  = section_renseignee( $fiche['sante'], $fiche['autres_tests'] );
	$fiche['titres_renseignes'] = section_renseignee( $fiche['titres'], $fiche['autres_titres'] );

	$fiche['pere'] = parent_de( $id, 'pere' );
	$fiche['mere'] = parent_de( $id, 'mere' );

	$fiche['photo_principale'] = photo( (int) get_post_thumbnail_id( $post ), $titre );
	$fiche['cadrage']          = cadrage( valeur( $id, '_mtb_cadrage' ) );
	$fiche['galerie']          = galerie( valeur( $id, '_mtb_galerie' ), $titre );
	$fiche['pedigree']         = pedigree( valeur( $id, '_mtb_pedigree' ) );

	return $fiche;
}

/**
 * Groupes de chiens par statut, dans l'ordre gelé, groupes vides exclus.
 *
 * @param array<string, mixed> $args Options de lecture ; « ordre » seulement.
 *
 * @return array<int, array<string, mixed>>
 */
function groupes( array $args ): array {
	$ordre = isset( $args['ordre'] ) && is_string( $args['ordre'] ) ? $args['ordre'] : 'naissance_desc';

	$requete = new \WP_Query(
		array(
			'post_type'              => 'mtb_chien',
			'post_status'            => 'publish',
			// Une fiche protégée par mot de passe ne figure dans aucun index public (BRIEF §8).
			'has_password'           => false,
			'posts_per_page'         => -1,
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_term_cache' => false,
		)
	);

	$par_statut = array();

	foreach ( $requete->posts as $post ) {
		if ( ! $post instanceof \WP_Post ) {
			continue;
		}

		$fiche  = fiche_complete( $post );
		$statut = $fiche['statut']['valeur'];

		// Sans statut, un chien n'appartient à aucun groupe : il n'y a pas de titre à lui donner.
		if ( '' === $statut ) {
			continue;
		}

		if ( ! isset( $par_statut[ $statut ] ) ) {
			$par_statut[ $statut ] = array();
		}

		$par_statut[ $statut ][] = $fiche;
	}

	$resultat = array();

	foreach ( array_keys( statuts() ) as $statut ) {
		if ( ! isset( $par_statut[ $statut ] ) ) {
			continue;
		}

		$chiens = $par_statut[ $statut ];
		usort(
			$chiens,
			static function ( array $a, array $b ) use ( $ordre ): int {
				return comparer( $a, $b, $ordre );
			}
		);

		$resultat[] = array(
			'statut'  => $statut,
			'libelle' => libelle_statut_pluriel( $statut ),
			'chiens'  => $chiens,
		);
	}

	return $resultat;
}

/**
 * Ordre interne d'un groupe : date de naissance décroissante, non datés en fin, départage
 * alphabétique sur le nom d'usage. Déterministe, donc explicable, malgré une date facultative.
 *
 * @param array<string, mixed> $a     Première fiche.
 * @param array<string, mixed> $b     Seconde fiche.
 * @param string               $ordre Ordre demandé.
 */
function comparer( array $a, array $b, string $ordre ): int {
	if ( 'alphabetique' === $ordre ) {
		return comparer_noms( $a, $b );
	}

	$date_a = (string) $a['date_naissance']['valeur'];
	$date_b = (string) $b['date_naissance']['valeur'];

	if ( '' === $date_a && '' === $date_b ) {
		return comparer_noms( $a, $b );
	}

	if ( '' === $date_a ) {
		return 1;
	}

	if ( '' === $date_b ) {
		return -1;
	}

	$comparaison = 'naissance_asc' === $ordre ? strcmp( $date_a, $date_b ) : strcmp( $date_b, $date_a );

	return 0 === $comparaison ? comparer_noms( $a, $b ) : $comparaison;
}

/**
 * Départage alphabétique, accents ignorés pour que « Élia » se range avec les « E ».
 *
 * @param array<string, mixed> $a Première fiche.
 * @param array<string, mixed> $b Seconde fiche.
 */
function comparer_noms( array $a, array $b ): int {
	return strnatcasecmp( remove_accents( (string) $a['nom_usage'] ), remove_accents( (string) $b['nom_usage'] ) );
}

/**
 * Nom d'usage tel qu'il a été saisi : c'est le titre WordPress, jamais un champ à part.
 *
 * @param \WP_Post $post Fiche concernée.
 */
function nom_usage( \WP_Post $post ): string {
	return trim( (string) $post->post_title );
}

/**
 * Valeur brute d'un champ, ramenée à une chaîne.
 *
 * @param int    $post_id Identifiant de la fiche.
 * @param string $cle     Clé du champ.
 */
function valeur( int $post_id, string $cle ): string {
	$valeur = get_post_meta( $post_id, $cle, true );

	return is_scalar( $valeur ) ? (string) $valeur : '';
}

/**
 * Forme commune d'un champ absent.
 *
 * @return array<string, string>
 */
function champ_vide(): array {
	return array(
		'valeur'    => '',
		'affichage' => non_renseigne(),
	);
}

/**
 * Champ de texte recopié : la valeur telle qu'elle a été saisie, jamais reformatée.
 *
 * @param string $valeur Valeur stockée.
 *
 * @return array<string, string>
 */
function champ_texte( string $valeur ): array {
	return array(
		'valeur'    => $valeur,
		'affichage' => '' === $valeur ? non_renseigne() : $valeur,
	);
}

/**
 * Champ de liste fermée : une valeur inconnue est traitée comme absente.
 *
 * @param string                $valeur  Valeur stockée.
 * @param array<string, string> $options Liste fermée.
 *
 * @return array<string, string>
 */
function champ_liste( string $valeur, array $options ): array {
	if ( ! isset( $options[ $valeur ] ) ) {
		return champ_vide();
	}

	return array(
		'valeur'    => $valeur,
		'affichage' => $options[ $valeur ],
	);
}

/**
 * Champ de date : valeur ISO pour la machine, valeur formatée pour l'œil, libellé accordé.
 *
 * @param string $valeur  Date stockée, au format AAAA-MM-JJ.
 * @param string $libelle Libellé accordé au sexe.
 *
 * @return array<string, string>
 */
function champ_date( string $valeur, string $libelle ): array {
	return array(
		'valeur'    => $valeur,
		'affichage' => '' === $valeur ? non_renseigne() : date_affichee( $valeur ),
		'libelle'   => $libelle,
	);
}

/**
 * Date formatée selon les réglages du site.
 *
 * @param string $iso Date au format AAAA-MM-JJ.
 */
function date_affichee( string $iso ): string {
	$format = (string) get_option( 'date_format' );

	if ( '' === $format ) {
		$format = 'j F Y';
	}

	$date = \DateTimeImmutable::createFromFormat( '!Y-m-d', $iso, wp_timezone() );

	if ( false === $date ) {
		return $iso;
	}

	$affichage = wp_date( $format, $date->getTimestamp() );

	return false === $affichage ? $iso : $affichage;
}

/**
 * Section Santé ou Titres, entièrement vide.
 *
 * @param array<string, array<string, string>> $champs Description des champs.
 *
 * @return array<string, array<string, string>>
 */
function section_vide( array $champs ): array {
	$section = array();

	foreach ( $champs as $court => $champ ) {
		$section[ $court ] = array(
			'libelle'   => $champ['public'],
			'valeur'    => '',
			'affichage' => non_renseigne(),
		);
	}

	return $section;
}

/**
 * Section Santé ou Titres renseignée depuis la fiche.
 *
 * @param int                                  $post_id Identifiant de la fiche.
 * @param array<string, array<string, string>> $champs  Description des champs.
 *
 * @return array<string, array<string, string>>
 */
function section( int $post_id, array $champs ): array {
	$section = array();

	foreach ( $champs as $court => $champ ) {
		$valeur = valeur( $post_id, $champ['cle'] );

		if ( isset( $champ['liste'] ) ) {
			$entree = champ_liste( $valeur, oui_non() );
		} else {
			$entree = champ_texte( $valeur );
		}

		$section[ $court ] = array(
			'libelle'   => $champ['public'],
			'valeur'    => $entree['valeur'],
			'affichage' => $entree['affichage'],
		);
	}

	return $section;
}

/**
 * Une section n'est renseignée que si au moins un de ses champs porte une valeur : c'est le
 * serveur qui décide si la section est rendue, jamais le thème.
 *
 * @param array<string, array<string, string>> $section Champs de la section.
 * @param array<int, string>                   $autres  Lignes du champ libre de la section.
 */
function section_renseignee( array $section, array $autres ): bool {
	if ( array() !== $autres ) {
		return true;
	}

	foreach ( $section as $entree ) {
		if ( '' !== $entree['valeur'] ) {
			return true;
		}
	}

	return false;
}

/**
 * Découpe un champ multiligne en lignes utiles.
 *
 * @param string $texte Valeur stockée.
 *
 * @return array<int, string> Une entrée par ligne non vide ; tableau vide, jamais null.
 */
function lignes( string $texte ): array {
	if ( '' === $texte ) {
		return array();
	}

	$lignes = array();

	foreach ( explode( "\n", $texte ) as $ligne ) {
		$ligne = trim( $ligne );

		if ( '' !== $ligne ) {
			$lignes[] = $ligne;
		}
	}

	return $lignes;
}

/**
 * Parent non renseigné : la forme est la même que celle d'un parent connu, seul « type » change.
 *
 * @param string $role « pere » ou « mere ».
 *
 * @return array<string, mixed>
 */
function parent_vide( string $role ): array {
	return array(
		'type'        => 'non_renseigne',
		'libelle'     => 'pere' === $role ? 'Père' : 'Mère',
		'id'          => 0,
		'nom'         => '',
		'nom_complet' => '',
		'elevage'     => '',
		'lien'        => '',
		'affichage'   => non_renseigne(),
		'photo'       => null,
	);
}

/**
 * Filiation à profondeur 1 : la fiche choisie si elle existe encore, sinon le nom libre, sinon
 * rien. Aucune remontée d'ascendance, donc aucune récursion possible.
 *
 * @param int    $post_id Identifiant de la fiche courante.
 * @param string $role    « pere » ou « mere ».
 *
 * @return array<string, mixed>
 */
function parent_de( int $post_id, string $role ): array {
	$parent   = parent_vide( $role );
	$fiche_id = (int) valeur( $post_id, '_mtb_' . $role . '_fiche' );
	$post_lie = $fiche_id > 0 ? get_post( $fiche_id ) : null;

	/*
	 * Une fiche est choisie et elle existe : elle fait autorité, quel que soit son état de
	 * publication. Basculer sur le nom libre parce qu'elle est en brouillon afficherait le nom d'un
	 * autre chien comme parent — une généalogie inventée par le code. Le nom libre ne reprend la
	 * main que si la fiche a réellement disparu, cas traité plus bas.
	 */
	if ( $post_lie instanceof \WP_Post && 'mtb_chien' === $post_lie->post_type ) {
		return parent_depuis_fiche( $parent, $post_lie );
	}

	$nom     = valeur( $post_id, '_mtb_' . $role . '_nom' );
	$elevage = valeur( $post_id, '_mtb_' . $role . '_elevage' );

	if ( '' === $nom && '' === $elevage ) {
		return $parent;
	}

	$parent['type']      = 'hors_elevage';
	$parent['nom']       = $nom;
	$parent['elevage']   = $elevage;
	$parent['affichage'] = '' === $nom ? non_renseigne() : $nom;

	return $parent;
}

/**
 * Parent renseigné par une fiche existante.
 *
 * Le lien n'existe que si la fiche est publiée et lisible. Une fiche en brouillon, en attente,
 * privée, protégée ou à la corbeille garde son nom — c'est un fait de généalogie, il reste vrai —
 * et perd tout le reste : ni lien, ni nom complet, ni photo. Rien du contenu réservé ne sort par
 * cette fonction, et aucun état nouveau n'est créé : « lien » vide est le seul signal, exactement
 * comme pour le chien d'un résultat de travail.
 *
 * @param array<string, mixed> $parent Parent vide, déjà porteur de son libellé.
 * @param \WP_Post             $fiche  Fiche du parent.
 *
 * @return array<string, mixed>
 */
function parent_depuis_fiche( array $parent, \WP_Post $fiche ): array {
	$nom = nom_usage( $fiche );

	$parent['type']      = 'fiche';
	$parent['id']        = (int) $fiche->ID;
	$parent['nom']       = $nom;
	$parent['affichage'] = '' === $nom ? non_renseigne() : $nom;

	if ( 'publish' !== $fiche->post_status || post_password_required( $fiche ) ) {
		return $parent;
	}

	$parent['nom_complet'] = valeur( (int) $fiche->ID, '_mtb_nom_complet' );
	$parent['lien']        = (string) get_permalink( $fiche );
	$parent['photo']       = photo( (int) get_post_thumbnail_id( $fiche ), $nom );

	return $parent;
}

/**
 * Photo utilisable, ou null si elle n'existe plus.
 *
 * @param int    $piece_id  Identifiant de la photo.
 * @param string $nom_usage Nom d'usage du chien, pour la description de repli.
 *
 * @return array<string, mixed>|null
 */
function photo( int $piece_id, string $nom_usage ): ?array {
	if ( $piece_id <= 0 || 'attachment' !== get_post_type( $piece_id ) ) {
		return null;
	}

	return array(
		'id'  => $piece_id,
		'alt' => texte_alternatif( $piece_id, $nom_usage ),
	);
}

/**
 * Description de la photo pour les personnes aveugles.
 *
 * Le repli n'invente aucun fait : il énonce ce que la photo est par construction, la photo de ce
 * chien. Une description vide laisserait le thème sans alternative, et le thème n'a pas le droit
 * de composer cette chaîne lui-même.
 *
 * @param int    $piece_id  Identifiant de la photo.
 * @param string $nom_usage Nom d'usage du chien.
 */
function texte_alternatif( int $piece_id, string $nom_usage ): string {
	$description = get_post_meta( $piece_id, '_wp_attachment_image_alt', true );
	$description = is_scalar( $description ) ? trim( (string) $description ) : '';

	if ( '' !== $description ) {
		return $description;
	}

	return '' === $nom_usage ? '' : 'Photo de ' . $nom_usage;
}

/**
 * Galerie : les photos encore présentes, dans l'ordre saisi.
 *
 * @param string $liste     Identifiants séparés par des virgules.
 * @param string $nom_usage Nom d'usage du chien.
 *
 * @return array<int, array<string, mixed>>
 */
function galerie( string $liste, string $nom_usage ): array {
	if ( '' === $liste ) {
		return array();
	}

	$photos = array();

	foreach ( explode( ',', $liste ) as $morceau ) {
		$photo = photo( (int) trim( $morceau ), $nom_usage );

		if ( null !== $photo ) {
			$photos[] = $photo;
		}
	}

	return $photos;
}

/**
 * Cadrage de la photo, avec son défaut.
 *
 * @param string $valeur Valeur stockée.
 *
 * @return array<string, string>
 */
function cadrage( string $valeur ): array {
	$cadrages = cadrages();

	if ( ! isset( $cadrages[ $valeur ] ) ) {
		$valeur = cadrage_par_defaut();
	}

	return array(
		'valeur'    => $valeur,
		'affichage' => $cadrages[ $valeur ],
	);
}

/**
 * Lien pedigree, ou null s'il n'y en a pas.
 *
 * @param string $url URL stockée.
 *
 * @return array<string, string>|null
 */
function pedigree( string $url ): ?array {
	if ( '' === $url ) {
		return null;
	}

	return array(
		'url'     => $url,
		'libelle' => 'Voir le pedigree',
	);
}
