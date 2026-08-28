<?php
/**
 * Point de sortie unique du module de reprise : compteurs, accords et messages.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\ResultatsPages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Les seuls \WP_CLI::log(), warning(), success() et error() du module vivent ici. « L'échec est
 * bruyant », un avertissement par rejet, une ligne de synthèse par type : cela se vérifie donc en
 * lisant un seul fichier.
 *
 * Aucune fonction de traduction : l'extension n'en charge aucune, et ces messages s'adressent au
 * développeur qui lance la reprise, jamais à l'éleveuse.
 */

/**
 * Libellés et accords propres à chaque type repris.
 *
 * Une page est un nom féminin, un résultat un nom masculin : sans cette table, la synthèse
 * écrirait « 7 pages créés », que personne dans cet élevage n'écrirait.
 *
 * @return array<string, array<string, mixed>> Type => libellés.
 */
function libelles(): array {
	return array(
		'resultats' => array(
			'titre'   => 'Résultats de travail',
			'cree'    => array( 'créé', 'créés' ),
			'present' => array( 'déjà présent', 'déjà présents' ),
			'rejete'  => array( 'rejeté', 'rejetés' ),
			'objet'   => 'résultat créé',
			'garde'   => "Le résultat n'a pas été supprimé.",
		),
		'pages'     => array(
			'titre'   => 'Pages',
			'cree'    => array( 'créée', 'créées' ),
			'present' => array( 'déjà présente', 'déjà présentes' ),
			'rejete'  => array( 'rejetée', 'rejetées' ),
			'objet'   => 'page créée',
			'garde'   => "La page n'a pas été supprimée.",
		),
	);
}

/**
 * État de la session : compteurs et lignes de rapport en attente.
 *
 * Rendu par référence : c'est le seul état du module, et il ne survit pas à la commande.
 *
 * @return array<string, mixed> État courant.
 */
function &registre(): array {
	static $registre = array(
		'mode'    => '',
		'types'   => array(),
		'notes'   => array(),
		'liens'   => array(
			'raccroches'  => 0,
			'deja_lies'   => 0,
			'non_resolus' => 0,
		),
		'rejets'  => 0,
	);

	return $registre;
}

/**
 * Ouvre la session de rapport.
 *
 * @param string   $mode  Mode d'exécution, tel qu'il s'écrit dans le rapport.
 * @param string[] $types Types réellement traités.
 */
function demarrer( string $mode, array $types ): void {
	$registre = &registre();

	$registre['mode']   = $mode;
	$registre['types']  = array();
	$registre['notes']  = array();
	$registre['liens']  = array(
		'raccroches'  => 0,
		'deja_lies'   => 0,
		'non_resolus' => 0,
	);
	$registre['rejets'] = 0;

	foreach ( $types as $type ) {
		$registre['types'][ $type ] = array(
			'cree'    => 0,
			'present' => 0,
			'rejete'  => 0,
		);
	}

	\WP_CLI::log( sprintf( 'Reprise des résultats de travail et des pages libres — %s.', $mode ) );
}

/**
 * Incrémente un compteur d'entrées.
 *
 * @param string $type Type concerné.
 * @param string $quoi « cree », « present » ou « rejete ».
 */
function compter( string $type, string $quoi ): void {
	$registre = &registre();

	if ( ! isset( $registre['types'][ $type ][ $quoi ] ) ) {
		return;
	}

	++$registre['types'][ $type ][ $quoi ];
}

/**
 * Incrémente un compteur de rattachement chien.
 *
 * @param string $quoi « raccroches », « deja_lies » ou « non_resolus ».
 */
function compter_lien( string $quoi ): void {
	$registre = &registre();

	if ( ! isset( $registre['liens'][ $quoi ] ) ) {
		return;
	}

	++$registre['liens'][ $quoi ];
}

/**
 * Nombre de rejets constatés, tous types confondus.
 *
 * Il pilote à lui seul le code de sortie : un avertissement qui n'est pas un rejet — une photo
 * absente, une fiche chien introuvable, des fixtures de démonstration détectées — laisse la
 * commande sortir en 0.
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
 * Le singulier vaut jusqu'à un inclus : « 0 déjà présente », « 1 déjà présente », « 2 déjà
 * présentes ».
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
 * Compose la ligne d'un message portant sur une entrée précise.
 *
 * @param string $fichier     Nom du fichier de données.
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
 * Signale un rejet et l'impute au code de sortie.
 *
 * @param string   $fichier     Nom du fichier de données.
 * @param int      $index       Index de l'entrée, fondé sur zéro.
 * @param string   $identifiant Identifiant métier de l'entrée.
 * @param string[] $raisons     Raisons rédigées, chacune une phrase complète.
 * @param string   $type        Type concerné, chaîne vide si le rejet ne compte aucune entrée.
 */
function rejeter( string $fichier, int $index, string $identifiant, array $raisons, string $type = '' ): void {
	$registre = &registre();

	++$registre['rejets'];

	if ( '' !== $type ) {
		compter( $type, 'rejete' );
	}

	\WP_CLI::warning( phrase_dentree( $fichier, $index, $identifiant, implode( ' ', $raisons ) ) );
}

/**
 * Signale qu'un contenu créé ne porte pas la valeur demandée, sans jamais le supprimer.
 *
 * @param string   $type        Type concerné.
 * @param string   $fichier     Nom du fichier de données.
 * @param int      $index       Index de l'entrée, fondé sur zéro.
 * @param string   $identifiant Identifiant métier de l'entrée.
 * @param string[] $divergences Divergences rédigées, une par champ.
 */
function signaler_divergences( string $type, string $fichier, int $index, string $identifiant, array $divergences ): void {
	$libelles = libelles();
	$registre = &registre();

	foreach ( $divergences as $divergence ) {
		++$registre['rejets'];

		\WP_CLI::warning(
			phrase_dentree(
				$fichier,
				$index,
				$identifiant,
				sprintf( '%s, mais %s %s', $libelles[ $type ]['objet'], $divergence, $libelles[ $type ]['garde'] )
			)
		);
	}
}

/**
 * Note une ligne d'information à imprimer dans la synthèse.
 *
 * @param string $ligne Ligne rédigée.
 */
function noter( string $ligne ): void {
	$registre = &registre();

	$registre['notes'][] = $ligne;
}

/**
 * Journalise une ligne immédiatement, sans l'imputer au code de sortie.
 *
 * @param string $ligne Ligne rédigée.
 */
function annoncer( string $ligne ): void {
	\WP_CLI::log( $ligne );
}

/**
 * Signale qu'un nom de chien n'a pas de fiche, ce qui est l'état NORMAL de cette reprise.
 *
 * C'est l'inversion délibérée du piège de « import-fixtures/resultats.php:40-50 », qui rejette une
 * entrée dont la référence ne résout pas. Juste pour un jeu fictif clos ; faux ici — cela ferait
 * disparaître 60 résultats sur 61 si les fiches chien n'ont pas encore été importées. Le nom reste
 * verbatim, le lien n'est pas posé, et le code de sortie ne bouge pas.
 *
 * @param string $nom       Nom du chien, verbatim.
 * @param string $reference Slug cherché.
 */
function lien_non_resolu( string $nom, string $reference ): void {
	compter_lien( 'non_resolus' );

	\WP_CLI::warning(
		sprintf(
			'Chien « %s » : aucune fiche ne porte la référence « %s ». Deux causes possibles, et la '
			. 'commande ne peut pas les départager : soit l\'import des fiches chien n\'a pas encore '
			. 'tourné, soit la référence du fichier de correspondances est fautive. Le nom reste écrit '
			. 'tel quel, aucun lien n\'est posé, et « --raccrocher » repassera plus tard.',
			$nom,
			$reference
		)
	);
}

/**
 * Signale que les trois photos ne sont pas téléversées, et pourquoi.
 *
 * Manque déclaré, pas case cochée : le téléversement est DIFFÉRÉ (arbitrage A5). Les pages sont
 * créées sans photo, l'emplacement n'existe pas dans le rendu, et le code de sortie reste 0.
 *
 * LA CAUSE A CHANGÉ, ET CE MESSAGE AVEC ELLE. Il invoquait d'abord l'inaccessibilité de l'archive
 * depuis les conteneurs ; ce motif est **caduc** depuis « compose.yaml:109 », qui la monte en
 * lecture seule sur « wpcli », photos comprises. Un avertissement qui ment sur sa cause envoie
 * l'opérateur chercher au mauvais endroit, ce qui est pire qu'un commentaire périmé. Il ne reste
 * donc que la cause qui tient encore : personne ne sait quel texte alternatif écrire, et c'est une
 * question à l'éleveuse.
 *
 * @param string[] $noms    Noms de fichiers attendus.
 * @param string   $dossier Dossier réellement cherché.
 */
function photos_differees( array $noms, string $dossier ): void {
	\WP_CLI::warning(
		sprintf(
			'Photos non téléversées : %s. Cherchées dans %s, qui ne les contient pas. Le dossier des '
			. 'photos n\'est plus hors d\'atteinte : l\'archive du site source est montée en lecture '
			. 'seule sur le conteneur « wpcli », et « --photos=/var/www/html/docs/migration/source/photos » '
			. 'les trouverait. L\'extension, elle, ne duplique pas les octets d\'une archive : ce serait '
			. 'une seconde source de vérité pour une photographie. Ce qui manque est ailleurs, et '
			. 'personne ici ne peut l\'inventer — AUCUNE de ces images ne porte de texte alternatif '
			. 'dans la capture, et en écrire un serait écrire un fait. C\'est une question ouverte à '
			. 'l\'éleveuse, dette T-#21-a. Les pages sont créées SANS photo : l\'emplacement n\'existe '
			. 'pas, aucun trou n\'est rendu.',
			implode( ', ', $noms ),
			$dossier
		)
	);
}

/**
 * Signale qu'une photo citée n'a pas été trouvée dans le dossier fourni.
 *
 * @param string $nom     Nom de fichier cité.
 * @param string $dossier Dossier cherché.
 */
function photo_absente( string $nom, string $dossier ): void {
	\WP_CLI::warning(
		sprintf(
			'Photo « %s » : aucun fichier de ce nom dans %s. Le bloc est composé sans photo ; '
			. 'l\'emplacement n\'existe pas dans le rendu.',
			$nom,
			$dossier
		)
	);
}

/**
 * Note qu'une photo a été versée à la médiathèque.
 *
 * @param string $nom Nom de fichier versé.
 */
function photo_importee( string $nom ): void {
	noter( sprintf( 'Photo « %s » : importée dans la médiathèque.', $nom ) );
}

/**
 * Note qu'une photo était déjà dans la médiathèque et n'a pas été retouchée.
 *
 * @param string $nom Nom de fichier trouvé.
 */
function photo_presente( string $nom ): void {
	noter( sprintf( 'Photo « %s » : déjà présente dans la médiathèque, non retouchée.', $nom ) );
}

/**
 * Signale la présence des résultats de démonstration semés par le provisionnement.
 *
 * L'importeur DÉTECTE et AVERTIT ; il ne supprime jamais rien. Supprimer du contenu qui n'est pas
 * le sien est hors de tout mandat.
 *
 * @param int $combien Nombre de résultats de démonstration comptés.
 */
function fixtures_de_demonstration( int $combien ): void {
	if ( 0 === $combien ) {
		return;
	}

	\WP_CLI::warning(
		sprintf(
			'%s de démonstration %s dans la base, semé%s par le provisionnement '
			. '(docker/provision/provision.sh:229-238, affixe « de Démonstration »). La page '
			. '/travail/ mélangera les deux jeux, et l\'ordre à année égale en dépend. Rien n\'est '
			. 'supprimé : ce contenu n\'appartient pas à cette commande. Le geste correct est de '
			. 'poser MTB_FIXTURES=0 dans « .env », puis de redémarrer la pile à froid : le '
			. 'provisionnement saute alors le jeu de démonstration sans rien supprimer. Un '
			. '« docker compose down -v » seul NE SUFFIT PAS — le provisionnement suivant resème '
			. 'aussitôt.',
			accorder( $combien, array( 'résultat', 'résultats' ) ),
			$combien >= 2 ? 'sont présents' : 'est présent',
			$combien >= 2 ? 's' : ''
		)
	);
}

/**
 * Interrompt la commande avant toute écriture : l'utilisateur ne peut pas écrire de balisage brut.
 *
 * En WP-CLI sans utilisateur, « wp_filter_post_kses » s'accroche et son
 * preg_replace( '/--+/', '-' ) s'applique au contenu des commentaires de blocs : le balisage des
 * pages serait détruit en silence.
 */
function echec_capacite(): void {
	\WP_CLI::error(
		'Le compte courant ne dispose pas de la capacité « unfiltered_html » : le balisage des blocs '
		. 'serait filtré et détruit à l\'écriture. Aucun contenu n\'a été créé. Relancer la commande '
		. 'en nommant un administrateur : wp mtb reprise-resultats-pages --user=admin'
	);
}

/**
 * Interrompt la commande avant toute lecture : deux options du mode d'exécution se contredisent.
 */
function echec_options_incompatibles(): void {
	\WP_CLI::error( 'Les options « --verifier » et « --raccrocher » ne se combinent pas : la première ne fait que lire, la seconde écrit.' );
}

/**
 * Interrompt la commande avant toute écriture : le fichier est illisible.
 *
 * @param string $chemin Chemin demandé.
 */
function echec_fichier_illisible( string $chemin ): void {
	\WP_CLI::error( sprintf( 'Fichier de reprise introuvable ou illisible : %s. Aucun contenu n\'a été repris.', $chemin ) );
}

/**
 * Interrompt la commande avant toute écriture : le JSON est invalide.
 *
 * @param string $chemin  Chemin du fichier.
 * @param string $message Message rendu par le décodeur.
 */
function echec_json_invalide( string $chemin, string $message ): void {
	\WP_CLI::error( sprintf( 'Fichier de reprise illisible, JSON invalide : %s (%s). Aucun contenu n\'a été repris.', $chemin, $message ) );
}

/**
 * Interrompt la commande avant toute écriture : la racine n'a pas la forme attendue.
 *
 * @param string $chemin Chemin du fichier.
 * @param string $forme  Forme attendue, rédigée.
 */
function echec_racine_invalide( string $chemin, string $forme ): void {
	\WP_CLI::error( sprintf( 'Fichier de reprise mal formé : %s ne contient pas %s. Aucun contenu n\'a été repris.', $chemin, $forme ) );
}

/**
 * Imprime le rapport puis sort — code 1 dès un seul rejet, code 0 sinon.
 */
function conclure(): void {
	$registre = &registre();
	$libelles = libelles();
	$total    = array(
		'cree'    => 0,
		'present' => 0,
		'rejete'  => 0,
	);

	foreach ( array_keys( $libelles ) as $type ) {
		if ( ! isset( $registre['types'][ $type ] ) ) {
			continue;
		}

		$compteurs = $registre['types'][ $type ];

		\WP_CLI::log(
			sprintf(
				'%s : %s, %s, %s.',
				$libelles[ $type ]['titre'],
				accorder( $compteurs['cree'], $libelles[ $type ]['cree'] ),
				accorder( $compteurs['present'], $libelles[ $type ]['present'] ),
				accorder( $compteurs['rejete'], $libelles[ $type ]['rejete'] )
			)
		);

		foreach ( $total as $quoi => $valeur ) {
			$total[ $quoi ] = $valeur + $compteurs[ $quoi ];
		}
	}

	$liens = $registre['liens'];

	if ( 0 < $liens['raccroches'] + $liens['deja_lies'] + $liens['non_resolus'] ) {
		\WP_CLI::log(
			sprintf(
				'Rattachement chien : %s, %s, %s.',
				accorder( $liens['raccroches'], array( 'lien posé', 'liens posés' ) ),
				accorder( $liens['deja_lies'], array( 'déjà lié, laissé intact', 'déjà liés, laissés intacts' ) ),
				accorder( $liens['non_resolus'], array( 'fiche introuvable', 'fiches introuvables' ) )
			)
		);
	}

	foreach ( $registre['notes'] as $ligne ) {
		\WP_CLI::log( $ligne );
	}

	$contenus = accorder( $total['cree'], array( 'contenu créé', 'contenus créés' ) );
	$presents = accorder( $total['present'], array( 'déjà présent', 'déjà présents' ) );

	if ( 0 === rejets() ) {
		\WP_CLI::success(
			sprintf(
				'%s terminée : %s, %s, %s.',
				ucfirst( (string) $registre['mode'] ),
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
			'%s terminée avec %s : %s, %s. Aucun contenu n\'a été supprimé.',
			ucfirst( (string) $registre['mode'] ),
			accorder( rejets(), array( 'rejet', 'rejets' ) ),
			$contenus,
			$presents
		)
	);
}
