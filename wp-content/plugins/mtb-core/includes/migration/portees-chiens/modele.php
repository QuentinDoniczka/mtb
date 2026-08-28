<?php
/**
 * Porte unique du module de reprise vers le modèle gelé des contenus.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\PorteesChiens;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Ce fichier est le SEUL du module à nommer une fonction de « content/ ». Tout le reste passe par
 * lui. Deux conséquences voulues :
 *
 *   - aucune liste fermée du modèle — statuts, variétés, sexes, disponibilités, cadrages — n'est
 *     recopiée dans l'import : elles sont lues vivantes, à l'exécution ;
 *   - le jour où une issue ajoute un champ à une portée ou à un chien, les fichiers de données
 *     peuvent l'employer sans qu'une ligne de ce module ne bouge.
 *
 * DETTE T9 — LA CINQUIÈME SÉMANTIQUE QU'ON N'ÉCRIT PAS
 *
 * Le dépôt porte déjà plusieurs copies de « nettoyer une valeur recopiée sans en changer le
 * sens ». Ce module n'en crée pas une de plus : pour les clés stockées qui n'ont AUCUN assainisseur
 * de modèle — le fait de robots du §11, l'image mise en avant — il appelle la plus stricte déjà en
 * place, « Content\Portee\nettoyer_recopie() », qui contrôle l'encodage par wp_check_invalid_utf8()
 * et SUPPRIME les caractères de contrôle. Le titre, le slug et le contenu, eux, ne passent pas par
 * ici : ils partent tels quels à wp_insert_post(), et c'est le contrôle aval qui les surveille.
 *
 * À signaler, parce que c'est contre-intuitif et que ce module ne peut pas le corriger :
 * « content/chien/assainissement.php » est la copie la PLUS LAXISTE — elle REMPLACE les caractères
 * de contrôle par une espace au lieu de les supprimer, et n'appelle jamais
 * wp_check_invalid_utf8(). Et c'est ELLE, pas celle employée ici, que sanitize_meta() invoquera
 * sur tous les champs de la fiche Chien, à l'import comme à la saisie. Le hissage des quatre
 * copies vers une seule est la dette T9 ; il n'appartient pas à cette issue, dont l'empreinte
 * s'arrête à ce dossier.
 *
 * Les délégations n'ont volontairement aucune garde « function_exists » : ces fonctions sont
 * livrées par les modules « content » de la même extension, chargés par le même chargeur. Leur
 * absence est une extension cassée, et l'erreur fatale est alors la panne la plus bruyante
 * possible.
 */

/**
 * Espace de noms des assainisseurs de la fiche Chien.
 *
 * « content/chien/champs.php » stocke des noms de fonctions non qualifiés et les préfixe lui-même
 * de son espace de noms au moment d'enregistrer les champs ; la reprise refait le même geste.
 */
const ESPACE_CHIEN = 'MTB\\Core\\Content\\Chien\\';

/**
 * Clé de méta hors modèle gelé, portant le fait de non-indexation relevé dans le source.
 *
 * ÉCART DÉCLARÉ. Le contrat §11 exige que le fait « noindex, nofollow », relevé verbatim dans le
 * « <head> » archivé des quatre fiches concernées, soit conservé. Or « content/chien/champs.php »
 * ne déclare aucune clé pour lui, et ce fichier est hors de l'empreinte d'écriture de cette issue.
 * Le fait est donc stocké sous cette clé, protégée par son tiret bas initial — donc invisible du
 * panneau « Champs personnalisés » — mais NON enregistrée par register_post_meta() : elle n'a ni
 * assainisseur de modèle, ni rappel d'autorisation. Ce module l'assainit lui-même avant écriture.
 *
 * Deux suites nécessaires, aucune n'appartient à cette issue :
 *   - déclarer la clé dans « content/chien/champs.php » ;
 *   - la rendre (wp_robots, exclusion du plan de site), sans quoi les quatre fiches sont
 *     indexables alors que le dépôt affirme le contraire (Q-20-9).
 */
const CLE_ROBOTS = '_mtb_robots_source';

/**
 * Table des champs du modèle pour un jeu, indexée par clé de méta.
 *
 * @param string $jeu « chiens » ou « portees ».
 *
 * @return array<string, array<string, mixed>> Table du modèle, tableau vide si le jeu est inconnu.
 */
function champs( string $jeu ): array {
	switch ( $jeu ) {
		case 'chiens':
			return \MTB\Core\Content\Chien\schema_des_champs();
		case 'portees':
			return \MTB\Core\Content\Portee\catalogue();
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
 * Clés de méta gouvernées par une liste fermée, et les clés que cette liste accepte.
 *
 * Lues vivantes, jamais recopiées. C'est la seule table qui autorise l'exception du contrôle
 * §9.2 : la valeur stockée y est une clé de projet, pas une chaîne du site source, et exiger
 * qu'elle figure verbatim dans une capture n'aurait aucun sens.
 *
 * @param string $jeu « chiens » ou « portees ».
 *
 * @return array<string, string[]> Clé de méta => clés acceptées par sa liste.
 */
function listes_fermees( string $jeu ): array {
	if ( 'chiens' === $jeu ) {
		return array(
			'_mtb_sexe'          => array_keys( \MTB\Core\Content\Chien\sexes() ),
			'_mtb_variete'       => array_keys( \MTB\Core\Content\Chien\varietes() ),
			'_mtb_statut'        => array_keys( \MTB\Core\Content\Chien\statuts() ),
			'_mtb_adn_identifie' => array_keys( \MTB\Core\Content\Chien\oui_non() ),
			'_mtb_cadrage'       => array_keys( \MTB\Core\Content\Chien\cadrages() ),
		);
	}

	if ( 'portees' === $jeu ) {
		return array(
			'_mtb_disponibilite' => array_keys( \MTB\Core\Content\Portee\disponibilites() ),
			'_mtb_pere_type'     => array( 'fiche', 'exterieur' ),
			'_mtb_mere_type'     => array( 'fiche', 'exterieur' ),
		);
	}

	return array();
}

/**
 * Clés acceptées pour le sexe d'un chiot d'une portée, lues vivantes.
 *
 * @return string[] Clés stockées.
 */
function sexes_de_chiot(): array {
	return array_keys( \MTB\Core\Content\Portee\sexes() );
}

/**
 * Assainisseur déclaré par le modèle pour une clé de méta.
 *
 * @param string $jeu « chiens » ou « portees ».
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
	}

	return '';
}

/**
 * Applique l'assainisseur du modèle à une valeur brute.
 *
 * Une clé hors modèle — la clé de robots — n'a pas d'assainisseur déclaré : elle reçoit celui de
 * ce module, qui est la copie la plus stricte du dépôt.
 *
 * @param string $jeu  « chiens » ou « portees ».
 * @param string $cle  Clé de méta.
 * @param mixed  $brut Valeur brute issue du fichier.
 *
 * @return mixed Valeur assainie.
 */
function assainir( string $jeu, string $cle, $brut ) {
	$assainisseur = assainisseur_de( $jeu, $cle );

	if ( '' === $assainisseur || ! is_callable( $assainisseur ) ) {
		return assainir_recopie( $brut, false );
	}

	return call_user_func( $assainisseur, $brut );
}

/**
 * Nettoie une valeur recopiée qu'aucun assainisseur de modèle ne gouverne.
 *
 * Délégation stricte, sans une ligne de logique propre : voir la note T9 en tête de fichier.
 * Jamais sanitize_text_field(), wp_strip_all_tags() ni wp_kses() — toutes passent par
 * strip_tags(), qui viderait EN SILENCE une valeur commençant par « < », comme le « <60% » d'une
 * dysplasie.
 *
 * @param mixed $valeur     Valeur brute.
 * @param bool  $multiligne Vrai pour conserver les retours à la ligne.
 *
 * @return string Valeur recopiée, chaîne vide si la donnée manque.
 */
function assainir_recopie( $valeur, bool $multiligne ): string {
	return \MTB\Core\Content\Portee\nettoyer_recopie( $valeur, $multiligne );
}

/**
 * Nom court de l'assainisseur, pour les messages de rejet.
 *
 * @param string $jeu « chiens » ou « portees ».
 * @param string $cle Clé de méta.
 *
 * @return string Nom de fonction sans son espace de noms.
 */
function nom_de_lassainisseur( string $jeu, string $cle ): string {
	$assainisseur = assainisseur_de( $jeu, $cle );

	if ( '' === $assainisseur ) {
		return 'nettoyer_recopie';
	}

	$position = strrpos( $assainisseur, '\\' );

	return false === $position ? $assainisseur : substr( $assainisseur, $position + 1 );
}

/**
 * Valeur par défaut du modèle pour une clé de méta.
 *
 * C'est la valeur qu'un enregistrement depuis l'écran de saisie produit quand la case est vide, et
 * celle que la reprise écrit pour toute clé vide du fichier. La table du chien ne la déclare pas
 * explicitement : elle la fait dériver du type déclaré, exactement comme
 * « content/chien/champs.php » au moment d'enregistrer les champs.
 *
 * @param string $jeu « chiens » ou « portees ».
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

/**
 * Type de contenu WordPress d'un jeu.
 *
 * @param string $jeu « chiens » ou « portees ».
 *
 * @return string Nom du type de contenu, chaîne vide si le jeu est inconnu.
 */
function type_de_contenu( string $jeu ): string {
	$types = array(
		'chiens'  => 'mtb_chien',
		'portees' => 'mtb_portee',
	);

	return isset( $types[ $jeu ] ) ? $types[ $jeu ] : '';
}
