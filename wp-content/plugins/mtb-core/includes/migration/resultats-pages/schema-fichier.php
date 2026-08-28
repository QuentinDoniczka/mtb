<?php
/**
 * Règle de correspondance entre une clé de fichier de reprise et le modèle vivant.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\ResultatsPages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * LA RÈGLE, ET SES TROIS ÉCARTS DÉCLARÉS (contrat #21 §3)
 *
 * Une clé de fichier est la clé de méta du modèle privée de son préfixe « _mtb_ ». L'ensemble des
 * clés acceptées n'est écrit nulle part : il est CALCULÉ à chaque exécution depuis le modèle
 * vivant. Le jour où une issue ajoute un champ au résultat de travail, un fichier de données peut
 * l'employer sans qu'une ligne de ce module ne bouge.
 *
 * Écart 1 — « reference » n'est PAS une clé acceptée, et « chien_id » non plus. Aucune clé du
 * fichier ne peut désigner une fiche chien. C'est la propriété de sûreté de toute la stratégie de
 * rattachement : un transcripteur ne peut pas poser un lien faux, parce que le fichier qu'il écrit
 * n'a aucun champ pour l'exprimer. Le rattachement passe par « correspondances-chiens.json », qui
 * exige une justification écrite.
 *
 * Écart 2 — « source » est une clé réservée, sœur de « commentaire » : jamais lue par l'importeur,
 * jamais écrite en base, lue uniquement par le comparateur hors ligne. Elle est OBLIGATOIRE : une
 * entrée sans provenance est une affirmation sans preuve.
 *
 * Écart 3 — « chien_nom » est obligatoire, corollaire de l'écart 1 : seul porteur du chien, donc
 * quatrième membre du tuple d'identité.
 */

/**
 * Clé réservée, jamais lue ni écrite : elle explique au lecteur pourquoi une entrée existe.
 */
const CLE_COMMENTAIRE = 'commentaire';

/**
 * Clé réservée et obligatoire : la provenance, lue par le seul comparateur hors ligne.
 */
const CLE_SOURCE = 'source';

/**
 * Sous-clés obligatoires de la provenance d'une page.
 */
const SOUS_CLES_SOURCE_PAGE = array( 'capture', 'html', 'sha256', 'zone' );

/**
 * Sous-clés obligatoires de la provenance d'un résultat.
 */
const SOUS_CLES_SOURCE_RESULTAT = array( 'famille', 'discipline_source', 'ligne' );

/**
 * Les deux seuls statuts qu'une page reprise peut porter (contrat §3.3).
 */
const STATUTS_ACCEPTES = array( 'publish', 'draft' );

/**
 * Clés d'une fiche de page : clé de fichier => champ de « wp_posts ».
 *
 * Ce ne sont pas des clés de méta : ce sont les champs de contenu de WordPress, et il n'existe
 * aucun modèle vivant d'où les dériver. Pas de clé « parent », et c'est un refus motivé (A6) :
 * poser un parent déciderait d'une URL, or cette décision appartient à l'issue de référencement.
 *
 * @return array<string, string> Clé de fichier => nom du champ de contenu.
 */
function cles_de_contenu_page(): array {
	return array(
		'reference' => 'post_name',
		'titre'     => 'post_title',
		'statut'    => 'post_status',
	);
}

/**
 * Clé de fichier correspondant à une clé de méta du modèle.
 *
 * @param string $meta Clé de méta, préfixe « _mtb_ » compris.
 *
 * @return string Clé de fichier.
 */
function cle_json_de_meta( string $meta ): string {
	return 0 === strpos( $meta, '_mtb_' ) ? substr( $meta, strlen( '_mtb_' ) ) : $meta;
}

/**
 * Clés de méta que le fichier ne peut pas porter, parce qu'elles désignent un contenu.
 *
 * Dérivé du modèle et non recopié : toute clé de méta déclarée « integer » et dont le nom se
 * termine par « _id » désigne un identifiant de contenu, qu'un fichier de données ne peut pas
 * connaître.
 *
 * @return string[] Clés de méta exclues.
 */
function metas_relationnelles(): array {
	$exclues = array();

	foreach ( champs_resultat() as $meta => $definition ) {
		$type = isset( $definition['type'] ) ? (string) $definition['type'] : 'string';

		if ( 'integer' === $type && '_id' === substr( (string) $meta, -3 ) ) {
			$exclues[] = (string) $meta;
		}
	}

	return $exclues;
}

/**
 * Liste close des clés acceptées dans une entrée de « resultats.json ».
 *
 * @return string[] Clés acceptées, dans l'ordre du modèle.
 */
function cles_acceptees_resultat(): array {
	$relationnelles = metas_relationnelles();
	$acceptees      = array();

	foreach ( array_keys( champs_resultat() ) as $meta ) {
		if ( in_array( (string) $meta, $relationnelles, true ) ) {
			continue;
		}

		$acceptees[] = cle_json_de_meta( (string) $meta );
	}

	$acceptees[] = CLE_SOURCE;
	$acceptees[] = CLE_COMMENTAIRE;

	return array_values( array_unique( $acceptees ) );
}

/**
 * Clés du tuple d'identité d'un résultat : sans elles, l'entrée serait recréée à chaque exécution.
 *
 * @return string[] Clés de fichier obligatoires.
 */
function cles_didentite_resultat(): array {
	return array( 'discipline', 'chien_nom', 'annee', 'niveau' );
}

/**
 * Clés de méta du tuple d'identité, retrouvées dans le modèle vivant.
 *
 * Le module n'écrit nulle part le préfixe d'une clé de méta : la table part du modèle et ne retient
 * que les champs dont la clé de fichier appartient au tuple. Deuxième effet, voulu : une clé que le
 * modèle ne déclarerait plus disparaît de la recherche d'identité au lieu de provoquer un accès à
 * une case absente.
 *
 * @return string[] Clés de méta, dans l'ordre du modèle.
 */
function metas_didentite_resultat(): array {
	$identite = cles_didentite_resultat();
	$metas    = array();

	foreach ( array_keys( champs_resultat() ) as $meta ) {
		if ( in_array( cle_json_de_meta( (string) $meta ), $identite, true ) ) {
			$metas[] = (string) $meta;
		}
	}

	return $metas;
}

/**
 * Clés acceptées dans une fiche de page.
 *
 * @return string[] Clés acceptées.
 */
function cles_acceptees_page(): array {
	$acceptees   = array_keys( cles_de_contenu_page() );
	$acceptees[] = CLE_SOURCE;
	$acceptees[] = 'composition';
	$acceptees[] = CLE_COMMENTAIRE;

	return $acceptees;
}

/**
 * Clés acceptées dans une entrée de correspondance chien.
 *
 * @return string[] Clés acceptées.
 */
function cles_acceptees_correspondance(): array {
	return array( 'chien_nom', 'reference', 'justification' );
}

/**
 * Clés acceptées dans une entrée de composition qui produit un bloc.
 *
 * @return string[] Clés acceptées.
 */
function cles_acceptees_bloc(): array {
	return array( 'bloc', 'attributs', 'paragraphes', 'photo', CLE_SOURCE, CLE_COMMENTAIRE );
}

/**
 * Clés acceptées dans une entrée de composition qui déclare un écart.
 *
 * Un écart n'est jamais une exemption : il réclame des lignes exactement comme un bloc, sans rien
 * produire, et porte sa raison à côté des lignes qu'il couvre.
 *
 * @return string[] Clés acceptées.
 */
function cles_acceptees_ecart(): array {
	return array( 'ecart', CLE_SOURCE, CLE_COMMENTAIRE );
}

/**
 * Nom du seul bloc du catalogue capable de porter des enfants.
 *
 * Sa liste blanche d'insertion est « core/paragraph » et « core/list » ; ce module n'émet que le
 * premier (contrat §4).
 */
const BLOC_A_PROSE = 'mtb/fiche-information';

/**
 * Attributs acceptés pour un bloc, lus vivants dans le registre des types de blocs.
 *
 * @param string $bloc Nom du bloc.
 *
 * @return string[] Noms d'attributs acceptés, liste vide si le bloc n'est pas enregistré.
 */
function attributs_acceptes( string $bloc ): array {
	$declares = attributs_declares( $bloc );

	if ( array() === $declares ) {
		return array();
	}

	$interdits = attributs_interdits( $bloc );

	return array_values( array_diff( array_keys( $declares ), $interdits ) );
}

/**
 * Attributs qu'un fichier de données ne peut pas porter, dérivés du schéma vivant du bloc.
 *
 * Un fichier ne peut pas connaître un identifiant de contenu : il n'existe pas avant l'import, et
 * il change d'une base à l'autre. Sont donc refusés tous les attributs numériques dont le nom se
 * termine par « _id », ainsi que « photo » et « photos ». Le nom de fichier d'une photo vit À CÔTÉ
 * des attributs, dans la clé « photo » de l'entrée, et l'importeur le résout.
 *
 * @param string $bloc Nom du bloc.
 *
 * @return string[] Noms d'attributs interdits.
 */
function attributs_interdits( string $bloc ): array {
	$interdits = array();

	foreach ( attributs_declares( $bloc ) as $nom => $schema ) {
		$nom  = (string) $nom;
		$type = isset( $schema['type'] ) ? (string) $schema['type'] : '';

		if ( in_array( $nom, array( 'photo', 'photos' ), true ) ) {
			$interdits[] = $nom;

			continue;
		}

		if ( in_array( $type, array( 'integer', 'number' ), true ) && '_id' === substr( $nom, -3 ) ) {
			$interdits[] = $nom;
		}
	}

	return $interdits;
}

/**
 * Attribut de photo d'un bloc : son unique attribut numérique dont le nom commence par « photo ».
 *
 * C'est là que l'importeur écrit l'identifiant de la pièce jointe qu'il a résolu depuis le nom de
 * fichier. Le nom n'est pas recopié d'une liste : il est retrouvé dans le schéma vivant du bloc.
 *
 * @param string $bloc Nom du bloc.
 *
 * @return string Nom de l'attribut, chaîne vide si le bloc n'en porte pas exactement un.
 */
function attribut_de_photo( string $bloc ): string {
	$candidats = array();

	foreach ( attributs_declares( $bloc ) as $nom => $schema ) {
		$nom  = (string) $nom;
		$type = isset( $schema['type'] ) ? (string) $schema['type'] : '';

		if ( 0 === strpos( $nom, 'photo' ) && in_array( $type, array( 'integer', 'number' ), true ) ) {
			$candidats[] = $nom;
		}
	}

	return 1 === count( $candidats ) ? $candidats[0] : '';
}

/**
 * Une valeur de fichier est-elle absente ?
 *
 * « null », la clé absente, la chaîne vide et la liste vide signifient tous la même chose : écrire
 * le défaut du modèle. Jamais « empty() » : empty( '0' ) vaut vrai, et une année ou un effectif de
 * zéro est une valeur légitime.
 *
 * @param mixed $brut Valeur brute.
 *
 * @return bool Vrai si la valeur ne dit rien.
 */
function valeur_absente( $brut ): bool {
	return null === $brut || '' === $brut || array() === $brut;
}

/**
 * Un texte est-il blanc au sens de la reprise — vide, ou fait de blancs seuls, U+00A0 comprise ?
 *
 * Une ligne d'espacement de l'éditeur IONOS n'est pas un paragraphe : le contrôle amont la refuse,
 * et la composition ne l'émet pas. Une seule définition de la règle, pour que les deux endroits qui
 * l'appliquent ne puissent pas diverger.
 *
 * @param string $texte Texte du fichier.
 *
 * @return bool Vrai si le texte ne porte aucun caractère visible.
 */
function texte_blanc( string $texte ): bool {
	return '' === trim( str_replace( "\u{00a0}", ' ', $texte ) );
}

/**
 * Valeur brute d'une clé de méta dans une entrée, ou le défaut du modèle si elle est absente.
 *
 * @param array<string, mixed> $entree Entrée du fichier.
 * @param string               $meta   Clé de méta.
 *
 * @return mixed Valeur brute telle qu'elle figure au fichier, ou le défaut du modèle.
 */
function valeur_brute( array $entree, string $meta ) {
	$cle  = cle_json_de_meta( $meta );
	$brut = isset( $entree[ $cle ] ) ? $entree[ $cle ] : null;

	return valeur_absente( $brut ) ? defaut_de( $meta ) : $brut;
}

/**
 * Valeurs brutes de toutes les clés de méta non relationnelles d'un résultat.
 *
 * Le parcours part du modèle et non du fichier : aucune clé du modèle ne peut donc rester non
 * écrite, ce qui rend le stockage identique à celui d'un enregistrement depuis l'écran de saisie.
 *
 * @param array<string, mixed> $entree Entrée du fichier.
 *
 * @return array<string, mixed> Clé de méta => valeur brute.
 */
function valeurs_brutes_resultat( array $entree ): array {
	$relationnelles = metas_relationnelles();
	$valeurs        = array();

	foreach ( array_keys( champs_resultat() ) as $meta ) {
		$meta = (string) $meta;

		if ( in_array( $meta, $relationnelles, true ) ) {
			continue;
		}

		$valeurs[ $meta ] = valeur_brute( $entree, $meta );
	}

	return $valeurs;
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
