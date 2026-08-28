<?php
/**
 * Orchestration de « wp mtb reprise-resultats-pages ».
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\ResultatsPages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Ce fichier n'écrit rien et n'imprime rien : il lit les options, décide du mode, appelle les
 * fichiers qui savent faire, et laisse « journal.php » parler. C'est ce qui rend le déroulé lisible
 * d'un seul coup d'œil, et la sortie vérifiable en lisant un seul autre fichier.
 */

/**
 * Exécute la reprise.
 *
 * Le déroulé, et la raison de chaque étape :
 *
 *   0. lire et vérifier la forme de TOUS les fichiers avant la moindre écriture, pour qu'un fichier
 *      illisible ne laisse jamais une base à moitié peuplée ;
 *   1. compter les résultats de démonstration déjà en base, et le dire ;
 *   2. résoudre les photos citées — aujourd'hui aucune, le téléversement est différé ;
 *   3. les résultats de travail, dans l'ordre du fichier, qui est l'ordre de la source ;
 *   4. les pages, cherchées par leur référence et créées seulement si elles manquent ;
 *   5. la synthèse, et le code de sortie.
 *
 * @param array<int, string>    $args       Arguments positionnels, aucun n'est attendu.
 * @param array<string, string> $assoc_args Options de la commande.
 */
function executer( array $args, array $assoc_args ): void {
	$verifier   = isset( $assoc_args['verifier'] );
	$raccrocher = isset( $assoc_args['raccrocher'] );
	$simuler    = isset( $assoc_args['simuler'] );

	if ( $verifier && $raccrocher ) {
		echec_options_incompatibles();
	}

	$chemin_resultats       = option_de_chemin( $assoc_args, 'resultats', dossier_des_donnees() . '/resultats.json' );
	$dossier_pages          = option_de_chemin( $assoc_args, 'pages', dossier_des_donnees() . '/pages' );
	$chemin_correspondances = option_de_chemin( $assoc_args, 'correspondances', dossier_des_donnees() . '/correspondances-chiens.json' );

	// « --photos » n'a délibérément aucune valeur par défaut : le téléversement est différé (A5).
	$dossier_photos = dossier_a_explorer( option_de_chemin( $assoc_args, 'photos', '' ) );

	$ecrit = ! $verifier && ! $simuler;

	demarrer( nom_du_mode( $verifier, $raccrocher, $simuler ), types_du_mode( $raccrocher ) );

	if ( $ecrit && ! peut_ecrire() ) {
		echec_capacite();

		return;
	}

	$resultats = lire_liste( $chemin_resultats );
	$noms      = noms_de_chiens( $resultats );
	$paires    = lire_les_correspondances( $chemin_correspondances, $noms );

	if ( $raccrocher ) {
		raccrocher( $paires, $simuler );
		conclure();

		return;
	}

	$pages   = lire_les_pages( $dossier_pages );
	$chemins = chemins_des_pages( $dossier_pages, array_keys( $pages ) );
	$photos  = garantir_les_photos( photos_citees( $pages ), $dossier_photos, ! $ecrit );

	fixtures_de_demonstration( compter_les_fixtures() );

	if ( $verifier ) {
		verifier( $resultats, $chemin_resultats, $pages, $chemins, $photos );
		conclure();

		return;
	}

	importer_resultats( $resultats, $chemin_resultats, $paires, $simuler );
	importer_pages( $pages, $chemins, $photos, $simuler );

	conclure();
}

/**
 * Valeur d'une option de chemin, ou son défaut.
 *
 * @param array<string, mixed> $assoc_args Options reçues.
 * @param string               $nom        Nom de l'option.
 * @param string               $defaut     Valeur par défaut.
 *
 * @return string Chemin, sans barre oblique finale.
 */
function option_de_chemin( array $assoc_args, string $nom, string $defaut ): string {
	$valeur = isset( $assoc_args[ $nom ] ) && is_scalar( $assoc_args[ $nom ] ) ? trim( (string) $assoc_args[ $nom ] ) : '';

	return '' === $valeur ? $defaut : rtrim( $valeur, '/\\' );
}

/**
 * Nom du mode tel qu'il s'écrit dans le rapport.
 *
 * @param bool $verifier   Mode vérification.
 * @param bool $raccrocher Mode rattachement.
 * @param bool $simuler    Simulation.
 *
 * @return string Nom du mode.
 */
function nom_du_mode( bool $verifier, bool $raccrocher, bool $simuler ): string {
	if ( $verifier ) {
		return 'vérification, aucune écriture';
	}

	if ( $raccrocher ) {
		return $simuler ? 'rattachement des chiens, simulation' : 'rattachement des chiens';
	}

	return $simuler ? 'simulation, aucune écriture' : 'reprise';
}

/**
 * Types comptés dans la synthèse selon le mode.
 *
 * @param bool $raccrocher Mode rattachement.
 *
 * @return string[] Types comptés.
 */
function types_du_mode( bool $raccrocher ): array {
	return $raccrocher ? array() : array( 'resultats', 'pages' );
}

/**
 * Noms de chien portés par les résultats transcrits, sans doublon.
 *
 * @param array<int, mixed> $entrees Entrées du fichier des résultats.
 *
 * @return string[] Noms verbatim.
 */
function noms_de_chiens( array $entrees ): array {
	$noms = array();

	foreach ( $entrees as $entree ) {
		if ( ! is_array( $entree ) || ! isset( $entree['chien_nom'] ) || ! is_string( $entree['chien_nom'] ) ) {
			continue;
		}

		if ( ! in_array( $entree['chien_nom'], $noms, true ) ) {
			$noms[] = $entree['chien_nom'];
		}
	}

	return $noms;
}
