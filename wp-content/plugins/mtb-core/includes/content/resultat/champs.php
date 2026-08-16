<?php
/**
 * Champs du type de contenu « Résultat de travail ».
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Content\Resultat;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enregistre les huit champs, depuis le rappel init 10 de bootstrap.php.
 *
 * Second filet d'assainissement, délibéré : la frontière du formulaire assainit déjà, mais ce
 * rappel couvre toute écriture par update_post_meta(), y compris celle d'un futur import.
 */
function enregistrer_champs(): void {
	foreach ( definitions() as $cle => $definition ) {
		register_post_meta(
			'mtb_resultat',
			$cle,
			array(
				'type'              => $definition['type'],
				'description'       => $definition['description'],
				'single'            => true,

				/*
				 * Aucun consommateur ne lit ces champs en REST : le type de contenu lui-même en est
				 * absent, et le thème passe par les fonctions de lecture.
				 */
				'show_in_rest'      => false,
				'sanitize_callback' => $definition['assainissement'],
				'auth_callback'     => __NAMESPACE__ . '\\autoriser_ecriture',
			)
		);
	}
}

/**
 * Les huit champs : clé protégée, type, assainissement.
 *
 * La discipline est assainie par sanitize_key et jamais par une liste blanche : une liste blanche
 * détruirait au premier ré-enregistrement une valeur devenue orpheline, et rien ne doit disparaître
 * en silence. La liste close des disciplines vit à l'écran de saisie et au rendu.
 *
 * @return array<string, array<string, string>> Définition indexée par clé de champ.
 */
function definitions(): array {
	return array(
		'_mtb_discipline' => array(
			'type'           => 'string',
			'assainissement' => 'sanitize_key',
			'description'    => 'Discipline',
		),
		'_mtb_chien_id'   => array(
			'type'           => 'integer',
			'assainissement' => 'absint',
			'description'    => 'Chien concerné',
		),
		'_mtb_chien_nom'  => array(
			'type'           => 'string',
			'assainissement' => 'sanitize_text_field',
			'description'    => 'Nom du chien (si le chien n’a pas de fiche)',
		),
		'_mtb_sexe'       => array(
			'type'           => 'string',
			'assainissement' => 'sanitize_key',
			'description'    => 'Sexe',
		),
		'_mtb_annee'      => array(
			'type'           => 'integer',
			'assainissement' => 'absint',
			'description'    => 'Année',
		),
		'_mtb_niveau'     => array(
			'type'           => 'string',
			'assainissement' => 'sanitize_text_field',
			'description'    => 'Niveau ou titre obtenu',
		),
		'_mtb_conducteur' => array(
			'type'           => 'string',
			'assainissement' => 'sanitize_text_field',
			'description'    => 'Conducteur',
		),
		'_mtb_pays'       => array(
			'type'           => 'string',
			'assainissement' => 'sanitize_text_field',
			'description'    => 'Pays',
		),
	);
}

/**
 * Autorise la lecture et l'écriture d'un champ selon la capacité sur le contenu porteur.
 *
 * Paramètres volontairement non typés : WordPress passe cette valeur depuis un filtre exécuté en
 * mode de typage souple, et un identifiant nul y provoquerait une erreur fatale avec un type déclaré.
 *
 * @param bool   $autorise Valeur proposée par WordPress.
 * @param string $cle_meta Clé du champ concerné.
 * @param int    $post_id  Identifiant du contenu porteur.
 *
 * @return bool Vrai si l'utilisateur courant peut modifier ce contenu.
 */
function autoriser_ecriture( $autorise, $cle_meta, $post_id ): bool {
	return current_user_can( 'edit_post', (int) $post_id );
}
