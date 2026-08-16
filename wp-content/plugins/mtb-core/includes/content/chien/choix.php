<?php
/**
 * Vocabulaire fermé de la fiche Chien : clés canoniques stockées, libellés affichés, accord au sexe.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Content\Chien;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Fichier partagé par les trois modules « chien » (content, fields, query), et non recopié dans
 * chacun : le contrat §4 impose que l'accord au sexe soit écrit « une fois, au même endroit ».
 * Trois copies finiraient par diverger, et le jour où elles divergent une fiche affiche deux
 * libellés différents pour la même donnée. Chaque bootstrap fait un require_once de ce fichier ;
 * le garde ci-dessous rend une seconde inclusion sans effet, quelle que soit la forme du chemin
 * employé par l'appelant.
 */
if ( function_exists( __NAMESPACE__ . '\\statuts' ) ) {
	return;
}

/**
 * Libellé unique de l'absence de donnée (MASTER §9.3 et §10.3).
 *
 * Jamais « Aucun », jamais « Non testé », jamais un tiret : une donnée absente n'est pas une
 * donnée négative.
 */
function non_renseigne(): string {
	return 'Non renseigné';
}

/**
 * Liste fermée « Sexe ».
 *
 * @return array<string, string> Clé stockée => libellé affiché.
 */
function sexes(): array {
	return array(
		'male'    => 'Mâle',
		'femelle' => 'Femelle',
	);
}

/**
 * Liste fermée « Variété ».
 *
 * @return array<string, string> Clé stockée => libellé affiché.
 */
function varietes(): array {
	return array(
		'poil_long'  => 'Poil long',
		'poil_court' => 'Poil court',
	);
}

/**
 * Liste fermée « ADN identifié » — trois états : oui, non, et le vide (« Non renseigné »).
 *
 * @return array<string, string> Clé stockée => libellé affiché.
 */
function oui_non(): array {
	return array(
		'oui' => 'Oui',
		'non' => 'Non',
	);
}

/**
 * Liste fermée « Cadrage de la photo » (MASTER §6.2).
 *
 * @return array<string, string> Clé stockée => libellé affiché.
 */
function cadrages(): array {
	return array(
		'haut_gauche' => 'Haut gauche',
		'haut'        => 'Haut',
		'centre'      => 'Centre',
		'haut_droite' => 'Haut droite',
		'bas'         => 'Bas',
	);
}

/**
 * Cadrage retenu quand rien n'a été choisi (contrat §2, clé « cadrage »).
 */
function cadrage_par_defaut(): string {
	return 'centre';
}

/**
 * Liste fermée « Statut », dans l'ordre d'affichage gelé des groupes de « La meute ».
 *
 * La valeur stockée est neutre et immobile : elle ne porte aucun accord. Changer le sexe d'un
 * chien ne réécrit donc jamais sa donnée, il change seulement ce qui s'affiche.
 *
 * @return array<string, array<string, string>> Clé stockée => formes masculine, féminine, plurielle.
 */
function statuts(): array {
	return array(
		'reproducteur'             => array(
			'masculin' => 'Reproducteur',
			'feminin'  => 'Reproductrice',
			'pluriel'  => 'Reproducteurs',
		),
		'en_cours_de_confirmation' => array(
			'masculin' => 'En cours de confirmation',
			'feminin'  => 'En cours de confirmation',
			'pluriel'  => 'En cours de confirmation',
		),
		'retraite'                 => array(
			'masculin' => 'Retraité',
			'feminin'  => 'Retraitée',
			'pluriel'  => 'Retraités',
		),
		'disparu'                  => array(
			'masculin' => 'Disparu',
			'feminin'  => 'Disparue',
			'pluriel'  => 'Disparus',
		),
	);
}

/**
 * Forme grammaticale à employer pour un sexe donné.
 *
 * Point unique de l'accord, pour le statut comme pour les dates : un sexe vide donne la forme
 * masculine canonique de MASTER §10.2. Jamais de point médian, jamais de parenthèse, jamais de
 * tiret — un « Retraité(e) » se lit mal en synthèse vocale.
 *
 * @param string $sexe Clé stockée du sexe, éventuellement vide.
 *
 * @return string « feminin » ou « masculin ».
 */
function accord( string $sexe ): string {
	return 'femelle' === $sexe ? 'feminin' : 'masculin';
}

/**
 * Libellé du statut, accordé au sexe.
 *
 * @param string $cle  Clé stockée du statut, éventuellement vide ou inconnue.
 * @param string $sexe Clé stockée du sexe, éventuellement vide.
 *
 * @return string Libellé accordé, ou « Non renseigné » si le statut est absent.
 */
function libelle_statut( string $cle, string $sexe ): string {
	$statuts = statuts();

	if ( ! isset( $statuts[ $cle ] ) ) {
		return non_renseigne();
	}

	return $statuts[ $cle ][ accord( $sexe ) ];
}

/**
 * Titre de groupe au pluriel, pour la page « La meute ».
 *
 * @param string $cle Clé stockée du statut.
 *
 * @return string Titre du groupe, ou « Non renseigné » si le statut est inconnu.
 */
function libelle_statut_pluriel( string $cle ): string {
	$statuts = statuts();

	if ( ! isset( $statuts[ $cle ] ) ) {
		return non_renseigne();
	}

	return $statuts[ $cle ]['pluriel'];
}

/**
 * Libellé public d'une date de la fiche, accordé au sexe.
 *
 * Même mécanisme et même endroit que l'accord du statut : « Né le » / « Née le »,
 * « Décédé le » / « Décédée le ».
 *
 * @param string $champ « naissance » ou « deces ».
 * @param string $sexe  Clé stockée du sexe, éventuellement vide.
 *
 * @return string Libellé accordé.
 */
function libelle_date( string $champ, string $sexe ): string {
	$libelles = array(
		'naissance' => array(
			'masculin' => 'Né le',
			'feminin'  => 'Née le',
		),
		'deces'     => array(
			'masculin' => 'Décédé le',
			'feminin'  => 'Décédée le',
		),
	);

	if ( ! isset( $libelles[ $champ ] ) ) {
		return '';
	}

	return $libelles[ $champ ][ accord( $sexe ) ];
}

/**
 * Les huit champs de santé à clés gelées.
 *
 * Le neuvième champ de santé du contrat, « Autres tests de santé », ne figure pas ici : c'est une
 * zone de texte libre, rendue au premier niveau du retour de lecture et non dans « sante », pour
 * que toutes les entrées de « sante » gardent exactement la même forme.
 *
 * « saisie » est le libellé de l'écran d'administration, « public » celui du site (MASTER §10.2).
 * Les clés courtes du tableau sont celles du retour de mtb_get_chien().
 *
 * @return array<string, array<string, string>> Clé courte => description du champ.
 */
function champs_sante(): array {
	return array(
		'hd'                  => array(
			'cle'    => '_mtb_sante_hd',
			'saisie' => 'Dysplasie des hanches (HD)',
			'public' => 'Hanches (HD)',
			'aide'   => 'Recopiez le résultat tel quel, comme il figure sur le document officiel.',
		),
		'ed'                  => array(
			'cle'    => '_mtb_sante_ed',
			'saisie' => 'Dysplasie des coudes (ED)',
			'public' => 'Coudes (ED)',
			'aide'   => 'Recopiez le résultat tel quel, comme il figure sur le document officiel.',
		),
		'ltv'                 => array(
			'cle'    => '_mtb_sante_ltv',
			'saisie' => 'LTV',
			'public' => 'LTV',
			'aide'   => 'Recopiez le résultat tel quel.',
		),
		'dm'                  => array(
			'cle'    => '_mtb_sante_dm',
			'saisie' => 'DM',
			'public' => 'DM',
			'aide'   => 'Recopiez le résultat tel quel.',
		),
		'sdca1'               => array(
			'cle'    => '_mtb_sante_sdca1',
			'saisie' => 'SDCA 1',
			'public' => 'SDCA 1',
			'aide'   => 'Recopiez le résultat tel quel.',
		),
		'sdca2'               => array(
			'cle'    => '_mtb_sante_sdca2',
			'saisie' => 'SDCA 2',
			'public' => 'SDCA 2',
			'aide'   => 'Recopiez le résultat tel quel.',
		),
		'adn_identifie'       => array(
			'cle'    => '_mtb_adn_identifie',
			'saisie' => 'ADN identifié',
			'public' => 'ADN identifié',
			'liste'  => 'oui_non',
			'aide'   => "Laissez sur « Non renseigné » tant que vous n'avez pas l'information : le site n'affichera rien.",
		),
		'diversite_genetique' => array(
			'cle'    => '_mtb_diversite_genetique',
			'saisie' => 'Diversité génétique',
			'public' => 'Diversité génétique',
			'aide'   => 'Recopiez la valeur telle quelle, avec le signe et le pourcentage. Rien ne sera corrigé ni reformaté.',
		),
	);
}

/**
 * Les cinq titres et brevets à clés gelées.
 *
 * @return array<string, array<string, string>> Clé courte => description du champ.
 */
function champs_titres(): array {
	return array(
		'tc'           => array(
			'cle'    => '_mtb_tc',
			'saisie' => 'TC',
			'public' => 'TC',
			'aide'   => 'Recopiez la mention telle quelle, y compris la date et le lieu si le document les porte.',
		),
		'csau'         => array(
			'cle'    => '_mtb_csau',
			'saisie' => 'CSAU',
			'public' => 'CSAU',
			'aide'   => 'Recopiez la mention telle quelle, y compris la date et le lieu si le document les porte.',
		),
		'cotation_lof' => array(
			'cle'    => '_mtb_cotation_lof',
			'saisie' => 'Cotation LOF',
			'public' => 'Cotation LOF',
			'aide'   => 'Recopiez la cotation telle quelle. Aucune vérification, aucune correction automatique.',
		),
		'confirmation' => array(
			'cle'    => '_mtb_confirmation',
			'saisie' => 'Confirmation',
			'public' => 'Confirmation',
			'aide'   => 'Recopiez la mention telle quelle.',
		),
		'lof'          => array(
			'cle'    => '_mtb_lof',
			'saisie' => 'N° LOF',
			'public' => 'N° LOF',
			'aide'   => 'Recopiez le numéro tel quel, avec ses espaces et sa ponctuation.',
		),
	);
}
