<?php
/**
 * Porte unique du module d'import vers le modèle gelé des contenus.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\ImportFixtures;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Ce fichier est le SEUL du module à nommer une fonction de « content/ » ou de « query/ ». Tout le
 * reste du module passe par lui. Deux conséquences voulues :
 *
 *   - aucune liste fermée du modèle — disciplines, statuts, variétés, disponibilités, sexes,
 *     cadrages — n'est recopiée nulle part dans l'import : elles sont lues vivantes, à l'exécution ;
 *   - le jour où une issue ajoute un champ à une portée, à un chien ou à un résultat, un fichier de
 *     fixture peut l'employer sans qu'une ligne de l'import ne bouge.
 *
 * Les délégations n'ont volontairement aucune garde « function_exists » : ces fonctions sont
 * livrées par les modules « content » de la même extension, chargés par le même chargeur. Leur
 * absence est une extension cassée, et l'erreur fatale est alors la panne la plus bruyante
 * possible — c'est l'esprit du contrat §7. Les deux seules fonctions globales consommées, elles,
 * sont gardées : elles appartiennent au groupe « query », dont le contrat prévoit qu'il puisse
 * manquer.
 */

/**
 * Espace de noms des assainisseurs de la fiche Chien.
 *
 * « content/chien/champs.php » stocke des noms de fonctions non qualifiés et les préfixe lui-même
 * de son espace de noms au moment d'enregistrer les champs ; l'import refait le même geste.
 */
const ESPACE_CHIEN = 'MTB\\Core\\Content\\Chien\\';

/**
 * Table des champs du modèle pour un jeu, indexée par clé de méta.
 *
 * @param string $jeu Jeu de fixtures.
 *
 * @return array<string, array<string, mixed>> Table du modèle, tableau vide si le jeu est inconnu.
 */
function champs( string $jeu ): array {
	switch ( $jeu ) {
		case 'chiens':
			return \MTB\Core\Content\Chien\schema_des_champs();
		case 'portees':
			return \MTB\Core\Content\Portee\catalogue();
		case 'resultats':
			return \MTB\Core\Content\Resultat\definitions();
	}

	return array();
}

/**
 * Les huit champs de santé de la fiche Chien, indexés par clé courte.
 *
 * @return array<string, array<string, string>> Clé courte => description du champ.
 */
function champs_sante(): array {
	return \MTB\Core\Content\Chien\champs_sante();
}

/**
 * Les cinq titres et brevets de la fiche Chien, indexés par clé courte.
 *
 * @return array<string, array<string, string>> Clé courte => description du champ.
 */
function champs_titres(): array {
	return \MTB\Core\Content\Chien\champs_titres();
}

/**
 * Liste close des disciplines, lue vivante.
 *
 * @return array<string, string> Clé stockée => libellé, tableau vide si la source est absente.
 */
function disciplines(): array {
	if ( ! function_exists( 'mtb_resultat_disciplines' ) ) {
		return array();
	}

	return mtb_resultat_disciplines();
}

/**
 * Liste close des sexes d'un résultat, lue vivante.
 *
 * @return array<string, string> Clé stockée => libellé, tableau vide si la source est absente.
 */
function sexes_resultat(): array {
	if ( ! function_exists( 'mtb_resultat_sexes' ) ) {
		return array();
	}

	return mtb_resultat_sexes();
}

/**
 * Assainisseur déclaré par le modèle pour une clé de méta.
 *
 * @param string $jeu Jeu de fixtures.
 * @param string $cle Clé de méta.
 *
 * @return string Nom de fonction appelable, chaîne vide si la clé est hors modèle.
 */
function assainisseur_de( string $jeu, string $cle ): string {
	$champs = champs( $jeu );

	if ( ! isset( $champs[ $cle ] ) ) {
		return '';
	}

	switch ( $jeu ) {
		case 'chiens':
			return ESPACE_CHIEN . (string) $champs[ $cle ]['assainissement'];
		case 'portees':
			return (string) $champs[ $cle ]['assainir'];
		case 'resultats':
			return (string) $champs[ $cle ]['assainissement'];
	}

	return '';
}

/**
 * Applique l'assainisseur du modèle à une valeur brute.
 *
 * @param string $jeu  Jeu de fixtures.
 * @param string $cle  Clé de méta.
 * @param mixed  $brut Valeur brute issue du fichier.
 *
 * @return mixed Valeur assainie, ou la valeur brute si la clé est hors modèle.
 */
function assainir( string $jeu, string $cle, $brut ) {
	$assainisseur = assainisseur_de( $jeu, $cle );

	if ( '' === $assainisseur || ! is_callable( $assainisseur ) ) {
		return $brut;
	}

	return call_user_func( $assainisseur, $brut );
}

/**
 * Nom court de l'assainisseur, pour les messages de rejet.
 *
 * @param string $jeu Jeu de fixtures.
 * @param string $cle Clé de méta.
 *
 * @return string Nom de fonction sans son espace de noms, chaîne vide si la clé est hors modèle.
 */
function nom_de_lassainisseur( string $jeu, string $cle ): string {
	$assainisseur = assainisseur_de( $jeu, $cle );

	if ( '' === $assainisseur ) {
		return '';
	}

	$position = strrpos( $assainisseur, '\\' );

	return false === $position ? $assainisseur : substr( $assainisseur, $position + 1 );
}

/**
 * Valeur par défaut du modèle pour une clé de méta.
 *
 * C'est la valeur qu'un enregistrement depuis l'écran de saisie produit quand la case est vide, et
 * celle que l'import écrit pour toute clé absente du fichier. Les deux tables qui ne la déclarent
 * pas explicitement — celle du chien et celle du résultat — la font dériver du type déclaré, très
 * exactement comme « content/chien/champs.php » le fait au moment d'enregistrer les champs.
 *
 * @param string $jeu Jeu de fixtures.
 * @param string $cle Clé de méta.
 *
 * @return mixed Valeur par défaut, chaîne vide si la clé est hors modèle.
 */
function defaut_de( string $jeu, string $cle ) {
	$champs = champs( $jeu );

	if ( ! isset( $champs[ $cle ] ) ) {
		return '';
	}

	if ( 'portees' === $jeu ) {
		return $champs[ $cle ]['defaut'];
	}

	$type = isset( $champs[ $cle ]['type'] ) ? (string) $champs[ $cle ]['type'] : 'string';

	return 'integer' === $type ? 0 : '';
}
