<?php
/**
 * Déclaration des champs de la fiche Chien auprès de WordPress.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Content\Chien;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Schéma des champs stockés : clé => type et assainissement.
 *
 * Toutes les clés portent le tiret bas initial, qui rend la donnée protégée : elle n'est donc
 * jamais listée par WordPress dans le panneau « Champs personnalisés ». C'est la garantie
 * mécanique qu'aucune clé technique n'atteint l'écran de l'éleveuse.
 *
 * Les clés de santé et de titres sont lues depuis le vocabulaire (choix.php) plutôt que recopiées
 * ici : une seule liste, donc aucune dérive possible entre l'écran de saisie et le stockage.
 *
 * @return array<string, array<string, string>> Clé de champ => array{ type, assainissement }.
 */
function schema_des_champs(): array {
	$schema = array(
		'_mtb_nom_complet'    => array( 'assainissement' => 'assainir_texte_recopie' ),
		'_mtb_sexe'           => array( 'assainissement' => 'assainir_sexe' ),
		'_mtb_variete'        => array( 'assainissement' => 'assainir_variete' ),
		'_mtb_date_naissance' => array( 'assainissement' => 'assainir_date' ),
		'_mtb_date_deces'     => array( 'assainissement' => 'assainir_date' ),
		'_mtb_statut'         => array( 'assainissement' => 'assainir_statut' ),
		'_mtb_taille'         => array( 'assainissement' => 'assainir_texte_recopie' ),
		'_mtb_couleur'        => array( 'assainissement' => 'assainir_texte_recopie' ),
		'_mtb_masque'         => array( 'assainissement' => 'assainir_texte_recopie' ),
		'_mtb_genetique_robe' => array( 'assainissement' => 'assainir_texte_recopie' ),
	);

	foreach ( array_merge( champs_sante(), champs_titres() ) as $champ ) {
		// Seul « ADN identifié » est une liste fermée ; tous les autres résultats sont recopiés.
		$schema[ $champ['cle'] ] = array(
			'assainissement' => isset( $champ['liste'] ) ? 'assainir_oui_non' : 'assainir_texte_recopie',
		);
	}

	$schema['_mtb_autres_tests']  = array( 'assainissement' => 'assainir_texte_multiligne' );
	$schema['_mtb_autres_titres'] = array( 'assainissement' => 'assainir_texte_multiligne' );

	foreach ( array( 'pere', 'mere' ) as $role ) {
		$schema[ '_mtb_' . $role . '_fiche' ]   = array(
			'type'           => 'integer',
			'assainissement' => 'assainir_identifiant',
		);
		$schema[ '_mtb_' . $role . '_nom' ]     = array( 'assainissement' => 'assainir_texte_recopie' );
		$schema[ '_mtb_' . $role . '_elevage' ] = array( 'assainissement' => 'assainir_texte_recopie' );
	}

	$schema['_mtb_cadrage']  = array( 'assainissement' => 'assainir_cadrage' );
	$schema['_mtb_galerie']  = array( 'assainissement' => 'assainir_liste_identifiants' );
	$schema['_mtb_pedigree'] = array( 'assainissement' => 'assainir_url' );

	return $schema;
}

/**
 * Enregistre tous les champs de la fiche Chien. Appelée sur « init », priorité 10.
 */
function enregistrer_champs(): void {
	foreach ( schema_des_champs() as $cle => $champ ) {
		$type = isset( $champ['type'] ) ? $champ['type'] : 'string';

		register_post_meta(
			'mtb_chien',
			$cle,
			array(
				'type'              => $type,
				'single'            => true,
				'default'           => 'integer' === $type ? 0 : '',
				'show_in_rest'      => true,
				'sanitize_callback' => __NAMESPACE__ . '\\' . $champ['assainissement'],
				'auth_callback'     => __NAMESPACE__ . '\\peut_modifier_la_fiche',
			)
		);
	}
}

/**
 * Autorisation d'écriture d'un champ, exigée explicitement plutôt que laissée au défaut.
 *
 * Les paramètres ne sont pas typés à dessein : WordPress appelle ce rappel depuis plusieurs
 * contextes, dont certains passent un identifiant nul.
 *
 * @param mixed $autorise Décision précédente.
 * @param mixed $cle      Clé du champ.
 * @param mixed $post_id  Identifiant de la fiche visée.
 */
function peut_modifier_la_fiche( $autorise, $cle, $post_id ): bool {
	return current_user_can( 'edit_post', (int) $post_id );
}
