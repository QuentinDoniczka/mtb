<?php
/**
 * Règle de correspondance entre une clé de fichier de fixtures et une clé du modèle.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\ImportFixtures;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * LA RÈGLE, ET SES SEULES EXCEPTIONS (contrat #29 §4)
 *
 * Une clé de fichier est la clé de méta du modèle privée de son préfixe « _mtb_ ». Exceptions,
 * limitativement : les clés de contenu WordPress, les quatre groupes — « tests_sante », « titres »,
 * « pere », « mere » — dont les sous-clés sont les clés courtes du modèle, et « commentaire », qui
 * n'est ni lue ni écrite.
 *
 * L'ensemble des clés acceptées n'est écrit nulle part : il est CALCULÉ depuis le modèle, à chaque
 * exécution. Le jour où une issue ajoute un champ, un fichier de fixture peut l'employer sans
 * qu'une ligne de ce module ne bouge.
 *
 * Une clé relationnelle — « reference » d'un parent ou d'un résultat, « galerie », « photo » —
 * porte un slug là où le modèle stocke un identifiant de contenu, qui n'existe pas avant l'import.
 * Elle est donc reconnue ici, mais résolue et écrite par le fichier de son type.
 */

/**
 * Clé réservée, jamais lue ni écrite : elle explique au lecteur pourquoi une entrée existe.
 */
const CLE_COMMENTAIRE = 'commentaire';

/**
 * Clé de fixture portant le slug de la photo principale, hors modèle de méta.
 *
 * L'image mise en avant est une donnée de contenu WordPress (« _thumbnail_id »), au même titre que
 * le titre ou le contenu : elle n'a ni entrée de catalogue, ni assainisseur de modèle, et c'est le
 * contrôle aval qui la surveille.
 */
const CLE_PHOTO = 'photo';

/**
 * Clés de contenu WordPress d'un jeu : clé de fichier => champ de « wp_posts ».
 *
 * @param string $jeu Jeu de fixtures.
 *
 * @return array<string, string> Clé de fichier => nom du champ de contenu.
 */
function cles_de_contenu( string $jeu ): array {
	$cles = array(
		'chiens'    => array(
			'nom_usage'   => 'post_title',
			'reference'   => 'post_name',
			'texte_libre' => 'post_content',
		),
		'portees'   => array(
			'identifiant' => 'post_title',
			'texte_libre' => 'post_content',
		),
		'resultats' => array(
			'texte_libre' => 'post_content',
		),
	);

	return isset( $cles[ $jeu ] ) ? $cles[ $jeu ] : array();
}

/**
 * Clés de méta résolues par un slug, donc écrites hors de la passe ordinaire.
 *
 * @param string $jeu Jeu de fixtures.
 *
 * @return string[] Clés de méta relationnelles.
 */
function cles_relationnelles( string $jeu ): array {
	$cles = array(
		'chiens'    => array( '_mtb_pere_fiche', '_mtb_mere_fiche', '_mtb_galerie' ),
		'portees'   => array( '_mtb_pere_fiche', '_mtb_mere_fiche', '_mtb_galerie' ),
		'resultats' => array( '_mtb_chien_id' ),
	);

	return isset( $cles[ $jeu ] ) ? $cles[ $jeu ] : array();
}

/**
 * Les deux rôles de parent, dans l'ordre de saisie.
 *
 * @return string[] Rôles.
 */
function roles(): array {
	return array( 'pere', 'mere' );
}

/**
 * Origine de chaque clé de méta dans le fichier : clé de méta => groupe et clé de fichier.
 *
 * C'est la table pivot du module : elle sert aussi bien à dresser la liste des clés acceptées qu'à
 * relire les valeurs d'une entrée. Un groupe vide signifie « clé de premier niveau ».
 *
 * @param string $jeu Jeu de fixtures.
 *
 * @return array<string, array<string, string>> Clé de méta => array{ groupe, cle }.
 */
function sources_json( string $jeu ): array {
	$sources = array();

	if ( 'chiens' === $jeu ) {
		foreach ( champs_sante() as $court => $champ ) {
			$sources[ $champ['cle'] ] = array(
				'groupe' => 'tests_sante',
				'cle'    => (string) $court,
			);
		}

		foreach ( champs_titres() as $court => $champ ) {
			$sources[ $champ['cle'] ] = array(
				'groupe' => 'titres',
				'cle'    => (string) $court,
			);
		}
	}

	foreach ( array_keys( champs( $jeu ) ) as $meta ) {
		if ( isset( $sources[ $meta ] ) ) {
			continue;
		}

		$sources[ $meta ] = source_dune_meta( (string) $meta );
	}

	return $sources;
}

/**
 * Origine d'une clé de méta qui n'appartient ni à la santé ni aux titres.
 *
 * @param string $meta Clé de méta, préfixe « _mtb_ » compris.
 *
 * @return array<string, string> array{ groupe, cle }.
 */
function source_dune_meta( string $meta ): array {
	$court = substr( $meta, strlen( '_mtb_' ) );

	foreach ( roles() as $role ) {
		$prefixe = $role . '_';

		if ( 0 !== strpos( $court, $prefixe ) ) {
			continue;
		}

		$suffixe = substr( $court, strlen( $prefixe ) );

		return array(
			'groupe' => $role,
			// Le fichier ne peut pas connaître un identifiant de contenu : il désigne la fiche par son slug.
			'cle'    => 'fiche' === $suffixe ? 'reference' : $suffixe,
		);
	}

	// Même raison, sur le chien d'un résultat de travail.
	if ( 'chien_id' === $court ) {
		return array(
			'groupe' => '',
			'cle'    => 'reference',
		);
	}

	return array(
		'groupe' => '',
		'cle'    => $court,
	);
}

/**
 * Les groupes d'un jeu et leurs sous-clés acceptées.
 *
 * @param string $jeu Jeu de fixtures.
 *
 * @return array<string, string[]> Nom du groupe => sous-clés acceptées.
 */
function groupes( string $jeu ): array {
	$groupes = array();

	foreach ( sources_json( $jeu ) as $source ) {
		if ( '' === $source['groupe'] ) {
			continue;
		}

		if ( ! isset( $groupes[ $source['groupe'] ] ) ) {
			$groupes[ $source['groupe'] ] = array();
		}

		$groupes[ $source['groupe'] ][] = $source['cle'];
	}

	return $groupes;
}

/**
 * Liste close des clés de premier niveau acceptées, calculée depuis le modèle.
 *
 * @param string $jeu Jeu de fixtures.
 *
 * @return string[] Clés acceptées, dans l'ordre du modèle.
 */
function cles_acceptees( string $jeu ): array {
	$acceptees = array_keys( cles_de_contenu( $jeu ) );

	if ( 'chiens' === $jeu ) {
		$acceptees[] = CLE_PHOTO;
	}

	foreach ( sources_json( $jeu ) as $source ) {
		$acceptees[] = '' === $source['groupe'] ? $source['cle'] : $source['groupe'];
	}

	$acceptees[] = CLE_COMMENTAIRE;

	return array_values( array_unique( $acceptees ) );
}

/**
 * Une valeur de fichier est-elle absente ?
 *
 * « null », la clé absente, la chaîne vide et la liste vide signifient tous la même chose : la clé
 * de méta est écrite avec le défaut du modèle. Jamais « empty() » : empty( '0' ) vaut vrai, et un
 * effectif de zéro mâle est un fait d'élevage légitime.
 *
 * @param mixed $brut Valeur brute.
 *
 * @return bool Vrai si la valeur ne dit rien.
 */
function valeur_absente( $brut ): bool {
	return null === $brut || '' === $brut || array() === $brut;
}

/**
 * Valeur brute d'une clé de méta dans une entrée, ou le défaut du modèle si elle est absente.
 *
 * @param string               $jeu    Jeu de fixtures.
 * @param array<string, mixed> $entree Entrée du fichier.
 * @param string               $meta   Clé de méta.
 *
 * @return mixed Valeur brute telle qu'elle figure au fichier, ou le défaut du modèle.
 */
function valeur_brute( string $jeu, array $entree, string $meta ) {
	$sources = sources_json( $jeu );

	if ( ! isset( $sources[ $meta ] ) ) {
		return defaut_de( $jeu, $meta );
	}

	$source = $sources[ $meta ];
	$portee = $entree;

	if ( '' !== $source['groupe'] ) {
		$groupe = isset( $entree[ $source['groupe'] ] ) ? $entree[ $source['groupe'] ] : null;
		$portee = is_array( $groupe ) ? $groupe : array();
	}

	$brut = isset( $portee[ $source['cle'] ] ) ? $portee[ $source['cle'] ] : null;

	return valeur_absente( $brut ) ? defaut_de( $jeu, $meta ) : $brut;
}

/**
 * Valeurs brutes de toutes les clés de méta non relationnelles d'une entrée.
 *
 * Le parcours part du modèle et non du fichier : aucune clé du modèle ne peut donc rester non
 * écrite, ce qui rend le stockage identique à celui d'un enregistrement depuis l'écran de saisie.
 *
 * @param string               $jeu    Jeu de fixtures.
 * @param array<string, mixed> $entree Entrée du fichier.
 *
 * @return array<string, mixed> Clé de méta => valeur brute.
 */
function valeurs_brutes( string $jeu, array $entree ): array {
	$relationnelles = cles_relationnelles( $jeu );
	$valeurs        = array();

	foreach ( array_keys( champs( $jeu ) ) as $meta ) {
		$meta = (string) $meta;

		if ( in_array( $meta, $relationnelles, true ) ) {
			continue;
		}

		$valeurs[ $meta ] = valeur_brute( $jeu, $entree, $meta );
	}

	return $valeurs;
}

/**
 * Champs de contenu WordPress d'une entrée.
 *
 * @param string               $jeu    Jeu de fixtures.
 * @param array<string, mixed> $entree Entrée du fichier.
 *
 * @return array<string, string> Champ de « wp_posts » => valeur brute.
 */
function champs_de_contenu( string $jeu, array $entree ): array {
	$champs = array();

	foreach ( cles_de_contenu( $jeu ) as $cle => $champ ) {
		$brut = isset( $entree[ $cle ] ) ? $entree[ $cle ] : null;

		$champs[ $champ ] = is_scalar( $brut ) ? (string) $brut : '';
	}

	return $champs;
}

/**
 * Valeur de premier niveau d'une entrée, sans interprétation.
 *
 * @param array<string, mixed> $entree Entrée du fichier.
 * @param string               $cle    Clé de fichier.
 *
 * @return string Valeur en chaîne, chaîne vide si absente ou non scalaire.
 */
function texte_de( array $entree, string $cle ): string {
	$brut = isset( $entree[ $cle ] ) ? $entree[ $cle ] : null;

	return is_scalar( $brut ) ? trim( (string) $brut ) : '';
}

/**
 * Sous-valeur d'un groupe, sans interprétation.
 *
 * @param array<string, mixed> $entree Entrée du fichier.
 * @param string               $groupe Nom du groupe.
 * @param string               $cle    Sous-clé.
 *
 * @return string Valeur en chaîne, chaîne vide si absente ou non scalaire.
 */
function texte_de_groupe( array $entree, string $groupe, string $cle ): string {
	$contenu = isset( $entree[ $groupe ] ) ? $entree[ $groupe ] : null;

	if ( ! is_array( $contenu ) ) {
		return '';
	}

	return texte_de( $contenu, $cle );
}

/**
 * Liste de slugs de photos portée par une clé, dans l'ordre du fichier.
 *
 * @param mixed $brut Valeur brute.
 *
 * @return string[] Slugs non vides, dans l'ordre, sans doublon.
 */
function slugs_de_photos( $brut ): array {
	if ( is_scalar( $brut ) ) {
		$brut = array( $brut );
	}

	if ( ! is_array( $brut ) ) {
		return array();
	}

	$slugs = array();

	foreach ( $brut as $element ) {
		if ( ! is_scalar( $element ) ) {
			continue;
		}

		$slug = trim( (string) $element );

		if ( '' !== $slug && ! in_array( $slug, $slugs, true ) ) {
			$slugs[] = $slug;
		}
	}

	return $slugs;
}
