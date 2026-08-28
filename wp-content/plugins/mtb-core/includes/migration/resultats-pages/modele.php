<?php
/**
 * Porte unique du module de reprise vers le modèle gelé des contenus.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\ResultatsPages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Ce fichier est le SEUL du module à nommer une fonction de « content/ » ou de « query/ », et le
 * seul à interroger le registre des types de blocs. Tout le reste du module passe par lui. Trois
 * conséquences voulues :
 *
 *   - aucune liste fermée du modèle — disciplines, sexes, attributs de bloc, valeurs d'énumération
 *     — n'est recopiée dans ce module : elles sont lues VIVANTES, à l'exécution ;
 *   - aucun assainisseur n'est écrit ici. « content/resultat/champs.php:21-23 » enregistre
 *     assainir_texte_recopie() en sanitize_callback de register_post_meta, et son commentaire dit
 *     que ce rappel couvre toute écriture par update_post_meta(), « y compris celle d'un futur
 *     import ». Ce module se contente d'appeler l'assainisseur DÉCLARÉ par le modèle, pour prédire
 *     ce que la base stockera ; il n'en définit aucun ;
 *   - le jour où une issue ajoute un champ au résultat de travail, un fichier de données peut
 *     l'employer sans qu'une ligne de ce module ne bouge.
 *
 * La délégation vers « content » n'a volontairement aucune garde « function_exists » : ces
 * fonctions sont livrées par la même extension et chargées par le même chargeur ; leur absence est
 * une extension cassée, et l'erreur fatale est alors la panne la plus bruyante possible. Les
 * fonctions globales du groupe « query » et de « fields », elles, sont gardées : le contrat du
 * chargeur prévoit qu'un module puisse manquer.
 */

/**
 * Table des huit champs du résultat de travail, indexée par clé de méta.
 *
 * @return array<string, array<string, string>> Table du modèle.
 */
function champs_resultat(): array {
	return \MTB\Core\Content\Resultat\definitions();
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
function sexes(): array {
	if ( ! function_exists( 'mtb_resultat_sexes' ) ) {
		return array();
	}

	return mtb_resultat_sexes();
}

/**
 * Assainisseur déclaré par le modèle pour une clé de méta.
 *
 * @param string $meta Clé de méta.
 *
 * @return string Nom de fonction appelable, chaîne vide si la clé est hors modèle.
 */
function assainisseur_de( string $meta ): string {
	$champs = champs_resultat();

	if ( ! isset( $champs[ $meta ]['assainissement'] ) ) {
		return '';
	}

	return (string) $champs[ $meta ]['assainissement'];
}

/**
 * Applique l'assainisseur du modèle à une valeur brute.
 *
 * @param string $meta Clé de méta.
 * @param mixed  $brut Valeur brute issue du fichier.
 *
 * @return mixed Valeur assainie, ou la valeur brute si la clé est hors modèle.
 */
function assainir( string $meta, $brut ) {
	$assainisseur = assainisseur_de( $meta );

	if ( '' === $assainisseur || ! is_callable( $assainisseur ) ) {
		return $brut;
	}

	return call_user_func( $assainisseur, $brut );
}

/**
 * Nom court de l'assainisseur, pour les messages de rejet.
 *
 * @param string $meta Clé de méta.
 *
 * @return string Nom de fonction sans son espace de noms, chaîne vide si la clé est hors modèle.
 */
function nom_de_lassainisseur( string $meta ): string {
	$assainisseur = assainisseur_de( $meta );

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
 * celle que la reprise écrit pour toute clé absente du fichier.
 *
 * @param string $meta Clé de méta.
 *
 * @return mixed Valeur par défaut, chaîne vide si la clé est hors modèle.
 */
function defaut_de( string $meta ) {
	$champs = champs_resultat();

	if ( ! isset( $champs[ $meta ] ) ) {
		return '';
	}

	$type = isset( $champs[ $meta ]['type'] ) ? (string) $champs[ $meta ]['type'] : 'string';

	return 'integer' === $type ? 0 : '';
}

/**
 * Titre d'administration composé par le serveur depuis les champs enregistrés.
 *
 * Il n'existe qu'une seule composition de ce titre dans le projet, et elle n'est pas ici.
 *
 * @param int    $post_id    Identifiant du résultat.
 * @param string $provisoire Titre déjà écrit, rendu tel quel si la composition n'est pas chargée.
 *
 * @return string Titre à écrire.
 */
function titre_de_resultat( int $post_id, string $provisoire ): string {
	if ( ! function_exists( '\\MTB\\Core\\Fields\\Resultat\\composer_titre' ) ) {
		return $provisoire;
	}

	return \MTB\Core\Fields\Resultat\composer_titre( $post_id );
}

/**
 * Schéma vivant des attributs d'un bloc du catalogue.
 *
 * Lu dans le registre des types de blocs, jamais dans un fichier « block.json » relu à la main :
 * c'est ce qui garantit qu'une valeur hors « enum » est refusée avec l'énumération réelle, celle
 * que l'éditeur applique.
 *
 * @param string $bloc Nom du bloc.
 *
 * @return array<string, array<string, mixed>> Attribut => schéma, tableau vide si le bloc est
 *                                             inconnu du registre.
 */
function attributs_declares( string $bloc ): array {
	$registre = \WP_Block_Type_Registry::get_instance();
	$type     = $registre->get_registered( $bloc );

	if ( ! $type instanceof \WP_Block_Type || ! is_array( $type->attributes ) ) {
		return array();
	}

	return $type->attributes;
}

/**
 * Le bloc est-il enregistré ?
 *
 * @param string $bloc Nom du bloc.
 *
 * @return bool Vrai si le registre le connaît.
 */
function bloc_enregistre( string $bloc ): bool {
	return \WP_Block_Type_Registry::get_instance()->is_registered( $bloc );
}

/**
 * Noms de tous les blocs enregistrés, pour les messages de rejet.
 *
 * @return string[] Noms de blocs, triés.
 */
function blocs_enregistres(): array {
	$noms = array_keys( \WP_Block_Type_Registry::get_instance()->get_all_registered() );

	sort( $noms );

	return $noms;
}
