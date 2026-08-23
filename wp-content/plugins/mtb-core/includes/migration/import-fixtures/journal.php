<?php
/**
 * Point de sortie unique du module d'import : compteurs, accords et messages.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\ImportFixtures;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Les seuls \WP_CLI::log(), warning(), success() et error() du module vivent ici. Le contrat §7 —
 * « l'échec est bruyant », un avertissement par rejet, une ligne de synthèse par type — se vérifie
 * donc en lisant un seul fichier.
 *
 * Aucune fonction de traduction : l'extension n'en charge aucune (contrat #1 §7), et ces messages
 * s'adressent au développeur qui provisionne la pile, jamais à l'éleveuse.
 */

/**
 * Libellés et accords propres à chaque jeu.
 *
 * Une portée est un nom féminin, un chien et un résultat des noms masculins : sans cette table, la
 * synthèse écrirait « 4 portées créés », que personne dans cet élevage n'écrirait.
 *
 * @return array<string, array<string, mixed>> Jeu => libellés.
 */
function libelles_de_jeu(): array {
	return array(
		'chiens'    => array(
			'titre'   => 'Chiens',
			'cree'    => array( 'créé', 'créés' ),
			'present' => array( 'déjà présent', 'déjà présents' ),
			'rejete'  => array( 'rejeté', 'rejetés' ),
			'objet'   => 'fiche créée',
			'garde'   => "La fiche n'a pas été supprimée.",
		),
		'portees'   => array(
			'titre'   => 'Portées',
			'cree'    => array( 'créée', 'créées' ),
			'present' => array( 'déjà présente', 'déjà présentes' ),
			'rejete'  => array( 'rejetée', 'rejetées' ),
			'objet'   => 'portée créée',
			'garde'   => "La portée n'a pas été supprimée.",
		),
		'resultats' => array(
			'titre'   => 'Résultats de travail',
			'cree'    => array( 'créé', 'créés' ),
			'present' => array( 'déjà présent', 'déjà présents' ),
			'rejete'  => array( 'rejeté', 'rejetés' ),
			'objet'   => 'résultat créé',
			'garde'   => "Le résultat n'a pas été supprimé.",
		),
	);
}

/**
 * Libellé d'un rôle de parent tel qu'il s'écrit dans un message.
 *
 * Écrit ici, et une seule fois : « père » et « mère » sont deux mots du domaine, et deux fichiers
 * les composaient chacun de leur côté.
 *
 * @param string $role « pere » ou « mere ».
 *
 * @return string Libellé accentué.
 */
function libelle_de_role( string $role ): string {
	return 'pere' === $role ? 'père' : 'mère';
}

/**
 * État de la session d'import : compteurs et lignes de rapport en attente.
 *
 * Rendu par référence : c'est le seul état du module, et il ne survit pas à la commande.
 *
 * @return array<string, mixed> État courant.
 */
function &registre(): array {
	static $registre = array(
		'jeux'   => array(),
		'photos' => array(),
		'rejets' => 0,
	);

	return $registre;
}

/**
 * Ouvre la session de rapport pour les jeux réellement demandés.
 *
 * Un type non fourni n'apparaît dans aucune ligne du rapport : c'est la garantie qu'un appel
 * partiel ne raconte pas ce qu'il n'a pas fait.
 *
 * @param string[] $jeux Jeux demandés.
 */
function demarrer( array $jeux ): void {
	$registre = &registre();

	$registre['jeux']   = array();
	$registre['photos'] = array();
	$registre['rejets'] = 0;

	foreach ( $jeux as $jeu ) {
		$registre['jeux'][ $jeu ] = array(
			'cree'    => 0,
			'present' => 0,
			'rejete'  => 0,
		);
	}
}

/**
 * Incrémente un compteur d'entrées.
 *
 * @param string $jeu  Jeu concerné.
 * @param string $quoi « cree », « present » ou « rejete ».
 */
function compter( string $jeu, string $quoi ): void {
	$registre = &registre();

	if ( ! isset( $registre['jeux'][ $jeu ][ $quoi ] ) ) {
		return;
	}

	++$registre['jeux'][ $jeu ][ $quoi ];
}

/**
 * Nombre de rejets constatés, tous types confondus.
 *
 * Il pilote à lui seul le code de sortie : un avertissement qui n'est pas un rejet — une photo
 * absente, un appel sans option — laisse la commande sortir en 0.
 *
 * @return int Nombre de rejets.
 */
function rejets(): int {
	$registre = &registre();

	return (int) $registre['rejets'];
}

/**
 * Accorde un nombre et son libellé.
 *
 * Le singulier vaut jusqu'à un inclus : « 0 déjà présent », « 1 déjà présent », « 2 déjà présents ».
 *
 * @param int      $nombre   Nombre.
 * @param string[] $libelles Formes singulière puis plurielle.
 *
 * @return string Nombre suivi de sa forme accordée.
 */
function accorder( int $nombre, array $libelles ): string {
	$forme = $nombre >= 2 ? $libelles[1] : $libelles[0];

	return $nombre . ' ' . $forme;
}

/**
 * Signale un rejet et l'impute au code de sortie.
 *
 * @param string   $fichier     Nom du fichier de fixtures.
 * @param int      $index       Index de l'entrée, fondé sur zéro.
 * @param string   $identifiant Identifiant métier de l'entrée.
 * @param string[] $raisons     Raisons rédigées, chacune une phrase complète.
 * @param string   $jeu         Jeu concerné, chaîne vide si le rejet ne compte aucune entrée.
 */
function rejeter( string $fichier, int $index, string $identifiant, array $raisons, string $jeu = '' ): void {
	$registre = &registre();

	++$registre['rejets'];

	if ( '' !== $jeu ) {
		compter( $jeu, 'rejete' );
	}

	\WP_CLI::warning( phrase_dentree( $fichier, $index, $identifiant, implode( ' ', $raisons ) ) );
}

/**
 * Compose la ligne d'un avertissement portant sur une entrée précise.
 *
 * @param string $fichier     Nom du fichier de fixtures.
 * @param int    $index       Index de l'entrée, fondé sur zéro.
 * @param string $identifiant Identifiant métier de l'entrée.
 * @param string $raison      Raison rédigée.
 *
 * @return string Ligne composée.
 */
function phrase_dentree( string $fichier, int $index, string $identifiant, string $raison ): string {
	return sprintf( '%s [%d] « %s » — %s', $fichier, $index, $identifiant, $raison );
}

/**
 * Signale qu'un contenu créé ne porte pas la valeur demandée, sans jamais le supprimer.
 *
 * @param string   $jeu         Jeu concerné.
 * @param string   $fichier     Nom du fichier de fixtures.
 * @param int      $index       Index de l'entrée, fondé sur zéro.
 * @param string   $identifiant Identifiant métier de l'entrée.
 * @param string[] $divergences Divergences rédigées, une par champ.
 */
function signaler_divergences( string $jeu, string $fichier, int $index, string $identifiant, array $divergences ): void {
	$libelles = libelles_de_jeu();
	$registre = &registre();

	foreach ( $divergences as $divergence ) {
		++$registre['rejets'];

		\WP_CLI::warning(
			phrase_dentree(
				$fichier,
				$index,
				$identifiant,
				sprintf( '%s, mais %s %s', $libelles[ $jeu ]['objet'], $divergence, $libelles[ $jeu ]['garde'] )
			)
		);
	}
}

/**
 * Signale qu'une filiation n'a pas pu être écrite, la fiche restant créée.
 *
 * @param string $fichier     Nom du fichier de fixtures.
 * @param int    $index       Index de l'entrée, fondé sur zéro.
 * @param string $identifiant Identifiant métier de l'entrée.
 * @param string $role        « pere » ou « mere ».
 * @param string $reference   Référence introuvable.
 */
function rejeter_filiation( string $fichier, int $index, string $identifiant, string $role, string $reference ): void {
	$libelles = libelles_de_jeu();
	$registre = &registre();

	++$registre['rejets'];

	\WP_CLI::warning(
		phrase_dentree(
			$fichier,
			$index,
			$identifiant,
			sprintf(
				'aucune fiche ne porte la référence « %s » indiquée comme %s : la filiation n\'a pas été écrite. %s',
				$reference,
				libelle_de_role( $role ),
				$libelles['chiens']['garde']
			)
		)
	);
}

/**
 * Note qu'une photo a été versée à la médiathèque.
 *
 * @param string $slug Slug de la pièce jointe.
 */
function photo_importee( string $slug ): void {
	$registre = &registre();

	$registre['photos'][] = sprintf( 'Photo « %s » : importée dans la médiathèque.', $slug );
}

/**
 * Note qu'une photo était déjà dans la médiathèque et n'a pas été retouchée.
 *
 * @param string $slug Slug de la pièce jointe.
 */
function photo_presente( string $slug ): void {
	$registre = &registre();

	$registre['photos'][] = sprintf( 'Photo « %s » : déjà présente dans la médiathèque.', $slug );
}

/**
 * Signale une photo introuvable — un avertissement, jamais un rejet.
 *
 * Une photo absente est un état de rendu légitime : les entrées qui la citent restent créées, et le
 * code de sortie reste 0. Une généalogie absente, elle, serait un fait faux, et fait rejeter.
 *
 * @param string $slug    Slug demandé.
 * @param string $dossier Dossier où la photo a été cherchée.
 */
function photo_absente( string $slug, string $dossier ): void {
	\WP_CLI::warning(
		sprintf(
			'Photo « %s » : aucun fichier de ce nom dans %s. La photo n\'est pas importée ; les entrées qui la citent sont créées sans elle.',
			$slug,
			$dossier
		)
	);
}

/**
 * Signale un appel sans aucune option.
 */
function aucun_fichier(): void {
	\WP_CLI::warning( 'Aucun fichier indiqué : rien à importer. Options disponibles : --portees, --chiens, --resultats.' );
}

/**
 * Interrompt la commande avant toute écriture : le fichier est illisible.
 *
 * @param string $chemin Chemin demandé.
 */
function echec_fichier_illisible( string $chemin ): void {
	\WP_CLI::error( sprintf( 'Fichier de fixtures introuvable ou illisible : %s. Aucun contenu n\'a été importé.', $chemin ) );
}

/**
 * Interrompt la commande avant toute écriture : le JSON est invalide.
 *
 * @param string $chemin  Chemin du fichier.
 * @param string $message Message rendu par le décodeur.
 */
function echec_json_invalide( string $chemin, string $message ): void {
	\WP_CLI::error( sprintf( 'Fichier de fixtures illisible, JSON invalide : %s (%s). Aucun contenu n\'a été importé.', $chemin, $message ) );
}

/**
 * Interrompt la commande avant toute écriture : la racine n'est pas une liste.
 *
 * @param string $chemin Chemin du fichier.
 */
function echec_racine_invalide( string $chemin ): void {
	\WP_CLI::error( sprintf( 'Fichier de fixtures mal formé : %s ne contient pas une liste d\'entrées. Aucun contenu n\'a été importé.', $chemin ) );
}

/**
 * Imprime le rapport puis sort — code 1 dès un seul rejet, code 0 sinon.
 */
function conclure(): void {
	$registre = &registre();
	$libelles = libelles_de_jeu();
	$total    = array(
		'cree'    => 0,
		'present' => 0,
		'rejete'  => 0,
	);

	foreach ( array_keys( $libelles ) as $jeu ) {
		if ( isset( $registre['jeux'][ $jeu ] ) ) {
			$compteurs = $registre['jeux'][ $jeu ];

			\WP_CLI::log(
				sprintf(
					'%s : %s, %s, %s.',
					$libelles[ $jeu ]['titre'],
					accorder( $compteurs['cree'], $libelles[ $jeu ]['cree'] ),
					accorder( $compteurs['present'], $libelles[ $jeu ]['present'] ),
					accorder( $compteurs['rejete'], $libelles[ $jeu ]['rejete'] )
				)
			);

			foreach ( $total as $quoi => $valeur ) {
				$total[ $quoi ] = $valeur + $compteurs[ $quoi ];
			}
		}

		// Les photos se rangent entre les chiens et les portées, comme dans le déroulé de l'import.
		if ( 'chiens' === $jeu ) {
			foreach ( $registre['photos'] as $ligne ) {
				\WP_CLI::log( $ligne );
			}
		}
	}

	$contenus = accorder( $total['cree'], array( 'contenu créé', 'contenus créés' ) );
	$presents = accorder( $total['present'], array( 'déjà présent', 'déjà présents' ) );

	if ( 0 === rejets() ) {
		\WP_CLI::success(
			sprintf(
				'Import terminé : %s, %s, %s.',
				$contenus,
				$presents,
				accorder( $total['rejete'], array( 'rejeté', 'rejetés' ) )
			)
		);

		return;
	}

	/*
	 * Sortie standard vidée avant l'erreur : WP-CLI écrit les avertissements et les erreurs sur la
	 * sortie d'erreur, non tamponnée, et le rapport sur la sortie standard, tamponnée dès qu'elle
	 * n'est pas un terminal. Sans ce vidage, « docker compose logs » afficherait la conclusion
	 * AVANT les lignes qu'elle résume.
	 */
	fflush( STDOUT );

	\WP_CLI::error(
		sprintf(
			'Import terminé avec %s : %s, %s. Aucun contenu n\'a été supprimé.',
			accorder( rejets(), array( 'rejet', 'rejets' ) ),
			$contenus,
			$presents
		)
	);
}
