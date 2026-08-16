<?php
/**
 * Catalogue des champs d'une portée : clés stockées, libellés et assainisseurs.
 *
 * Ce fichier est le seul endroit du projet qui décrit les seize clés d'une portée. L'écran de
 * saisie et la sauvegarde s'y réfèrent ; ils n'en redéclarent aucune.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Content\Portee;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Les trois états de disponibilité, clé stockée vers libellé affiché.
 *
 * La clé est courte et sans accent pour qu'un ajustement de typographie ne réécrive jamais la base.
 *
 * @return array<string,string> Clés « disponible », « reserve », « passee ».
 */
function disponibilites(): array {
	return array(
		'disponible' => 'Chiots disponibles',
		'reserve'    => 'Tous réservés',
		'passee'     => 'Portée passée',
	);
}

/**
 * Les deux sexes d'un chiot, clé stockée vers libellé affiché.
 *
 * @return array<string,string> Clés « male », « femelle ».
 */
function sexes(): array {
	return array(
		'male'    => 'Mâle',
		'femelle' => 'Femelle',
	);
}

/**
 * Catalogue des seize champs d'une portée.
 *
 * L'identifiant de la portée est son titre et son commentaire est le contenu du contenu : ni l'un
 * ni l'autre n'est un champ, ils ne figurent donc pas ici.
 *
 * @return array<string,array<string,mixed>> Clé stockée vers description du champ.
 */
function catalogue(): array {
	return array(
		'_mtb_date_naissance' => array(
			'libelle'  => 'Date de naissance',
			'type'     => 'string',
			'defaut'   => '',
			'assainir' => __NAMESPACE__ . '\\assainir_date',
		),
		'_mtb_disponibilite'  => array(
			'libelle'  => 'Disponibilité',
			'type'     => 'string',
			'defaut'   => '',
			'assainir' => __NAMESPACE__ . '\\assainir_disponibilite',
		),
		'_mtb_males'          => array(
			'libelle'  => 'Nombre de mâles',
			'type'     => 'string',
			'defaut'   => '',
			'assainir' => __NAMESPACE__ . '\\assainir_compteur',
		),
		'_mtb_femelles'       => array(
			'libelle'  => 'Nombre de femelles',
			'type'     => 'string',
			'defaut'   => '',
			'assainir' => __NAMESPACE__ . '\\assainir_compteur',
		),
		'_mtb_chiots'         => array(
			'libelle'  => 'Chiot',
			'type'     => 'array',
			'defaut'   => array(),
			'assainir' => __NAMESPACE__ . '\\assainir_chiots',
		),
		'_mtb_galerie'        => array(
			'libelle'  => 'Galerie photos',
			'type'     => 'array',
			'defaut'   => array(),
			'assainir' => __NAMESPACE__ . '\\assainir_galerie',
		),
		'_mtb_pere_type'      => array(
			'libelle'  => 'Père',
			'type'     => 'string',
			'defaut'   => '',
			'assainir' => __NAMESPACE__ . '\\assainir_type_parent',
		),
		'_mtb_pere_fiche'     => array(
			'libelle'  => 'Fiche du père',
			'type'     => 'integer',
			'defaut'   => 0,
			'assainir' => __NAMESPACE__ . '\\assainir_fiche',
		),
		'_mtb_pere_nom'       => array(
			'libelle'  => 'Nom',
			'type'     => 'string',
			'defaut'   => '',
			'assainir' => __NAMESPACE__ . '\\assainir_texte',
		),
		'_mtb_pere_elevage'   => array(
			'libelle'  => 'Élevage',
			'type'     => 'string',
			'defaut'   => '',
			'assainir' => __NAMESPACE__ . '\\assainir_texte',
		),
		'_mtb_pere_sante'     => array(
			'libelle'  => 'Tests de santé du père',
			'type'     => 'string',
			'defaut'   => '',
			'assainir' => __NAMESPACE__ . '\\assainir_multiligne',
		),
		'_mtb_mere_type'      => array(
			'libelle'  => 'Mère',
			'type'     => 'string',
			'defaut'   => '',
			'assainir' => __NAMESPACE__ . '\\assainir_type_parent',
		),
		'_mtb_mere_fiche'     => array(
			'libelle'  => 'Fiche de la mère',
			'type'     => 'integer',
			'defaut'   => 0,
			'assainir' => __NAMESPACE__ . '\\assainir_fiche',
		),
		'_mtb_mere_nom'       => array(
			'libelle'  => 'Nom',
			'type'     => 'string',
			'defaut'   => '',
			'assainir' => __NAMESPACE__ . '\\assainir_texte',
		),
		'_mtb_mere_elevage'   => array(
			'libelle'  => 'Élevage',
			'type'     => 'string',
			'defaut'   => '',
			'assainir' => __NAMESPACE__ . '\\assainir_texte',
		),
		'_mtb_mere_sante'     => array(
			'libelle'  => 'Tests de santé de la mère',
			'type'     => 'string',
			'defaut'   => '',
			'assainir' => __NAMESPACE__ . '\\assainir_multiligne',
		),
	);
}

/**
 * Déclare les seize clés d'une portée. Appelé depuis le rappel « init » 10 du module.
 *
 * Aucune clé n'est exposée en REST. WP_REST_Post_Meta_Fields ne teste pas le mot de passe d'un
 * contenu : les chiots, les noms des parents et leurs tests de santé d'une portée protégée y
 * seraient lisibles en anonyme, alors que les fonctions de lecture les taisent. Le type de contenu,
 * lui, reste exposé — c'est de lui, et non de ses champs, qu'un sélecteur a besoin pour lister les
 * portées. Une issue qui aurait besoin d'un champ en REST l'ouvrira en traitant le mot de passe.
 */
function enregistrer_champs(): void {
	foreach ( catalogue() as $cle => $champ ) {
		register_post_meta(
			'mtb_portee',
			$cle,
			array(
				'type'              => $champ['type'],
				'description'       => $champ['libelle'],
				'single'            => true,
				'default'           => $champ['defaut'],
				'show_in_rest'      => false,
				'sanitize_callback' => $champ['assainir'],
				'auth_callback'     => __NAMESPACE__ . '\\autoriser_ecriture',
			)
		);
	}
}

/**
 * Autorise l'écriture d'un champ de portée à qui peut modifier cette portée.
 *
 * Aucune capacité n'est ajoutée nulle part : le rôle Éditeur natif obtient « edit_post » sur une
 * portée par « capability_type => post » et « map_meta_cap => true ».
 *
 * Paramètres volontairement non typés : sous strict_types, un type déclaré ferait de tout appel
 * inattendu du cœur une erreur fatale.
 *
 * @param mixed $permis            Décision proposée par WordPress, ignorée.
 * @param mixed $cle               Clé du champ, ignorée : la règle est la même pour les seize.
 * @param mixed $identifiant_objet Identifiant de la portée visée.
 *
 * @return bool Vrai si l'utilisateur courant peut modifier cette portée.
 */
function autoriser_ecriture( $permis, $cle, $identifiant_objet ): bool {
	return current_user_can( 'edit_post', (int) $identifiant_objet );
}

/**
 * Nettoie une valeur recopiée sans jamais en changer le sens.
 *
 * Ni sanitize_text_field(), ni sanitize_textarea_field(), ni strip_tags(), ni wp_kses() : toutes
 * passent par strip_tags(), qui sur une valeur commençant par « < » supprime tout jusqu'à un « > »
 * inexistant. La valeur devient vide, sans erreur ni avertissement. Or « <60% » est un résultat de
 * dysplasie parfaitement réel, et l'effacer inventerait une donnée d'élevage.
 *
 * Ne subsistent donc que le contrôle d'encodage et le retrait des caractères de contrôle. C'est sûr
 * parce que l'échappement est systématique au rendu et que seul un compte pouvant modifier la
 * portée écrit ici.
 *
 * @param mixed $valeur     Valeur brute.
 * @param bool  $multiligne Vrai pour conserver les retours à la ligne.
 *
 * @return string Valeur recopiée, chaîne vide si la donnée manque.
 */
function nettoyer_recopie( $valeur, bool $multiligne ): string {
	if ( ! is_scalar( $valeur ) ) {
		return '';
	}

	$texte = wp_check_invalid_utf8( (string) $valeur );

	if ( $multiligne ) {
		$texte = str_replace( array( "\r\n", "\r" ), "\n", $texte );
	} else {
		$texte = str_replace( array( "\r\n", "\r", "\n" ), ' ', $texte );
	}

	/*
	 * Retrait des seuls caractères de contrôle. Comparaison octet par octet volontaire : en UTF-8,
	 * aucun octet de continuation ne descend sous 0x80, la classe ne peut donc pas mordre sur un
	 * caractère accentué.
	 */
	$texte = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $texte );

	if ( ! is_string( $texte ) ) {
		return '';
	}

	return trim( $texte );
}

/**
 * Assainit une valeur de texte sur une seule ligne, sans rien reformater.
 *
 * @param mixed $valeur Valeur brute.
 *
 * @return string Texte recopié, chaîne vide si la donnée manque.
 */
function assainir_texte( $valeur ): string {
	return nettoyer_recopie( $valeur, false );
}

/**
 * Assainit un texte sur plusieurs lignes en conservant les retours à la ligne.
 *
 * @param mixed $valeur Valeur brute.
 *
 * @return string Texte recopié, chaîne vide si la donnée manque.
 */
function assainir_multiligne( $valeur ): string {
	return nettoyer_recopie( $valeur, true );
}

/**
 * Assainit une date de naissance et la ramène au format AAAA-MM-JJ.
 *
 * Deux notations acceptées, par expression rationnelle explicite puis checkdate() : AAAA-MM-JJ, que
 * poste le champ de date natif, et JJ/MM/AAAA, que poste un navigateur sans champ de date natif.
 * Une date française sur quatre chiffres d'année n'est pas ambiguë, elle ne change pas de sens en
 * changeant de notation. Jamais strtotime(), qui devine.
 *
 * Une valeur qui n'est pas une date réelle est refusée : la stocker rendrait le tri chronologique
 * et le formatage faux sans que rien ne le signale. La sauvegarde, elle, conserve alors la date
 * précédente et le dit à l'éleveuse en citant sa saisie.
 *
 * @param mixed $valeur Valeur brute.
 *
 * @return string Date AAAA-MM-JJ, ou chaîne vide.
 */
function assainir_date( $valeur ): string {
	if ( ! is_scalar( $valeur ) ) {
		return '';
	}

	$brut = trim( (string) $valeur );

	if ( '' === $brut ) {
		return '';
	}

	if ( 1 === preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $brut, $parties ) ) {
		$annee = $parties[1];
		$mois  = $parties[2];
		$jour  = $parties[3];
	} elseif ( 1 === preg_match( '#^(\d{2})/(\d{2})/(\d{4})$#', $brut, $parties ) ) {
		$annee = $parties[3];
		$mois  = $parties[2];
		$jour  = $parties[1];
	} else {
		return '';
	}

	if ( ! checkdate( (int) $mois, (int) $jour, (int) $annee ) ) {
		return '';
	}

	return $annee . '-' . $mois . '-' . $jour;
}

/**
 * Assainit une disponibilité sur la liste close des trois états.
 *
 * @param mixed $valeur Valeur brute.
 *
 * @return string « disponible », « reserve », « passee », ou chaîne vide.
 */
function assainir_disponibilite( $valeur ): string {
	if ( ! is_scalar( $valeur ) ) {
		return '';
	}

	$brut = (string) $valeur;

	return isset( disponibilites()[ $brut ] ) ? $brut : '';
}

/**
 * Assainit un compteur de chiots en conservant la distinction entre « 0 » et « non renseigné ».
 *
 * Le retour est une chaîne, jamais un entier : absint( '' ) vaudrait 0, ce qui affirmerait qu'il
 * n'y a aucun mâle alors que l'éleveuse n'a rien saisi.
 *
 * @param mixed $valeur Valeur brute.
 *
 * @return string Suite de chiffres, ou chaîne vide.
 */
function assainir_compteur( $valeur ): string {
	if ( ! is_scalar( $valeur ) ) {
		return '';
	}

	$brut = trim( (string) $valeur );

	if ( '' === $brut ) {
		return '';
	}

	$chiffres = preg_replace( '/[^0-9]/', '', $brut );

	return is_string( $chiffres ) ? $chiffres : '';
}

/**
 * Assainit le mode de saisie d'un parent.
 *
 * @param mixed $valeur Valeur brute.
 *
 * @return string « fiche », « exterieur », ou chaîne vide.
 */
function assainir_type_parent( $valeur ): string {
	if ( ! is_scalar( $valeur ) ) {
		return '';
	}

	$brut = (string) $valeur;

	return in_array( $brut, array( 'fiche', 'exterieur' ), true ) ? $brut : '';
}

/**
 * Assainit le sexe d'un chiot sur la liste close des deux valeurs.
 *
 * @param mixed $valeur Valeur brute.
 *
 * @return string « male », « femelle », ou chaîne vide.
 */
function assainir_sexe( $valeur ): string {
	if ( ! is_scalar( $valeur ) ) {
		return '';
	}

	$brut = (string) $valeur;

	return isset( sexes()[ $brut ] ) ? $brut : '';
}

/**
 * Assainit l'identifiant de la fiche d'un parent.
 *
 * @param mixed $valeur Valeur brute.
 *
 * @return int Identifiant positif, ou 0.
 */
function assainir_fiche( $valeur ): int {
	if ( ! is_scalar( $valeur ) ) {
		return 0;
	}

	return absint( $valeur );
}

/**
 * Assainit la liste des chiots et la réindexe.
 *
 * Une rangée dont les quatre sous-champs sont vides est écartée. Le n° LOF est recopié tel quel :
 * aucune normalisation, jamais.
 *
 * @param mixed $valeur Valeur brute, tableau de rangées.
 *
 * @return array<int,array<string,string>> Liste réindexée, tableau vide si rien n'est saisi.
 */
function assainir_chiots( $valeur ): array {
	if ( ! is_array( $valeur ) ) {
		return array();
	}

	$propres = array();

	foreach ( $valeur as $rangee ) {
		if ( ! is_array( $rangee ) ) {
			continue;
		}

		$chiot = array(
			'nom'     => assainir_texte( $rangee['nom'] ?? '' ),
			'sexe'    => assainir_sexe( $rangee['sexe'] ?? '' ),
			'lof'     => assainir_texte( $rangee['lof'] ?? '' ),
			'devenir' => assainir_texte( $rangee['devenir'] ?? '' ),
		);

		if ( '' === $chiot['nom'] && '' === $chiot['sexe'] && '' === $chiot['lof'] && '' === $chiot['devenir'] ) {
			continue;
		}

		$propres[] = $chiot;
	}

	/*
	 * Garde contre une soumission forgée, et rien d'autre : ce n'est pas une règle d'élevage, aucune
	 * portée réelle n'approche ce nombre. Sans plafond, un formulaire fabriqué à la main ferait
	 * enfler la valeur stockée sans limite.
	 */
	return array_slice( array_values( $propres ), 0, 100 );
}

/**
 * Assainit la galerie : identifiants de photos, sans doublon, dans l'ordre choisi.
 *
 * @param mixed $valeur Valeur brute, tableau d'identifiants.
 *
 * @return array<int,int> Identifiants positifs réindexés, tableau vide si la galerie est vide.
 */
function assainir_galerie( $valeur ): array {
	if ( ! is_array( $valeur ) ) {
		return array();
	}

	$identifiants = array();

	foreach ( $valeur as $element ) {
		if ( ! is_scalar( $element ) ) {
			continue;
		}

		$identifiant = absint( $element );

		if ( $identifiant > 0 && ! in_array( $identifiant, $identifiants, true ) ) {
			$identifiants[] = $identifiant;
		}
	}

	// Garde contre une soumission forgée, et rien d'autre : ce n'est pas une règle d'élevage.
	return array_slice( array_values( $identifiants ), 0, 200 );
}
