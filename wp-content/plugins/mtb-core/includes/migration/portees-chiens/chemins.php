<?php
/**
 * Résolution des chemins : fichiers de données, archive du source, dossier des photographies.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\PorteesChiens;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Trois racines, et une seule est codée en dur : celle du module lui-même, par __DIR__.
 *
 * Les fichiers de données vivent dans « donnees/ », à côté de ce code, parce qu'ils sont la
 * transcription versionnée qui fait tenir tout le dispositif : un tiers, dans six mois, base
 * détruite, ouvre le dossier et relance l'import.
 *
 * L'archive du source, elle, vit hors de l'extension — « docs/migration/source/ » — parce qu'elle
 * documente le projet et non le code. Sa position relative se déduit de la disposition du dépôt,
 * qui est une constante du projet ; l'option « --source » existe pour le jour où elle ne l'est
 * plus, et pour qu'un déplacement de dossier ne devienne jamais un échec silencieux.
 */

/**
 * Dossier des fichiers de données transcrits.
 *
 * @return string Chemin absolu, sans barre oblique finale.
 */
function dossier_des_donnees(): string {
	return __DIR__ . '/donnees';
}

/**
 * Chemin par défaut d'un fichier de données.
 *
 * @param string $jeu « chiens » ou « portees ».
 *
 * @return string Chemin absolu.
 */
function fichier_par_defaut( string $jeu ): string {
	return dossier_des_donnees() . '/' . $jeu . '.json';
}

/**
 * Racine de l'archive du site source, telle que les clés « source » la référencent.
 *
 * @param array<string, mixed> $options Options de la commande.
 *
 * @return string Chemin absolu, sans barre oblique finale.
 */
function racine_du_source( array $options ): string {
	$fournie = option_texte( $options, 'source' );

	if ( '' !== $fournie ) {
		return rtrim( $fournie, '/\\' );
	}

	/*
	 * Six niveaux : portees-chiens → migration → includes → mtb-core → plugins → wp-content →
	 * racine du dépôt. La disposition est celle que CLAUDE.md gèle ; un déplacement la casserait,
	 * et le contrôle des extraits le dirait aussitôt au lieu de passer en silence.
	 */
	return dirname( __DIR__, 6 ) . '/docs/migration/source';
}

/**
 * Dossier des photographies archivées.
 *
 * @param array<string, mixed> $options Options de la commande.
 *
 * @return string Chemin absolu, sans barre oblique finale.
 */
function dossier_des_photos( array $options ): string {
	$fourni = option_texte( $options, 'photos' );

	if ( '' !== $fourni ) {
		return rtrim( $fourni, '/\\' );
	}

	return racine_du_source( $options ) . '/photos';
}

/**
 * Chemin absolu d'un fichier cité par une clé « source », et le dossier qui l'a fourni.
 *
 * Deux racines essayées, dans cet ordre, et la seconde est nommée quand elle sert : l'archive du
 * source d'abord, puis « donnees/ », qui accueille les re-dérivations. Une capture re-dérivée ne
 * peut pas vivre dans « source/ » — le contrat #19 §9 le gèle — et un extrait vérifié contre une
 * re-dérivation doit se lire dans le journal, jamais se deviner.
 *
 * @param string $relatif Chemin relatif déclaré par la clé « source ».
 * @param array<string, mixed> $options Options de la commande.
 *
 * @return array<string, string> array{ chemin, racine } ; « chemin » vide si le fichier est introuvable.
 */
function fichier_de_source( string $relatif, array $options ): array {
	$relatif = ltrim( str_replace( '\\', '/', $relatif ), '/' );

	if ( '' === $relatif || false !== strpos( $relatif, '..' ) ) {
		return array(
			'chemin' => '',
			'racine' => '',
		);
	}

	$racines = array(
		'archive du source' => racine_du_source( $options ),
		'recapture'         => dossier_des_donnees(),
	);

	foreach ( $racines as $nom => $racine ) {
		$chemin = $racine . '/' . $relatif;

		if ( is_file( $chemin ) && is_readable( $chemin ) ) {
			return array(
				'chemin' => $chemin,
				'racine' => (string) $nom,
			);
		}
	}

	return array(
		'chemin' => '',
		'racine' => '',
	);
}

/**
 * Valeur textuelle d'une option de commande.
 *
 * @param array<string, mixed> $options Options.
 * @param string               $nom     Nom de l'option.
 *
 * @return string Valeur nettoyée, chaîne vide si l'option est absente ou non scalaire.
 */
function option_texte( array $options, string $nom ): string {
	if ( ! isset( $options[ $nom ] ) || ! is_scalar( $options[ $nom ] ) ) {
		return '';
	}

	return trim( (string) $options[ $nom ] );
}

/**
 * Chemins des deux fichiers de données, options ou défauts.
 *
 * @param array<string, mixed> $options Options de la commande.
 *
 * @return array<string, string> Jeu => chemin absolu.
 */
function fichiers_de_donnees( array $options ): array {
	$chemins = array();

	foreach ( array( 'chiens', 'portees' ) as $jeu ) {
		$fourni = option_texte( $options, $jeu );

		$chemins[ $jeu ] = '' === $fourni ? fichier_par_defaut( $jeu ) : $fourni;
	}

	return $chemins;
}

/**
 * Nom du fichier tel qu'il apparaît dans les messages.
 *
 * @param string $chemin Chemin absolu.
 *
 * @return string Nom du fichier, sans son dossier.
 */
function nom_de_fichier( string $chemin ): string {
	return basename( $chemin );
}
