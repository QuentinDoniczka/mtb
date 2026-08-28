<?php
/**
 * Point de sortie unique du module de reprise : compteurs, accords et messages.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\PorteesChiens;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Les seuls \WP_CLI::log(), warning(), success() et error() du module vivent ici. « Un rejet, une
 * ligne ; un jeu, une synthèse » se vérifie donc en lisant un seul fichier, et l'accord au genre
 * ne peut pas diverger d'un fichier à l'autre.
 *
 * Aucune fonction de traduction : l'extension n'en charge aucune, et ces messages s'adressent au
 * développeur qui provisionne la base, jamais à l'éleveuse.
 *
 * DEUX COMPTEURS, ET PAS UN SEUL, parce que les deux échecs n'ont pas la même gravité :
 *
 *   - « rejet »  : l'entité n'est PAS écrite. Une valeur sans provenance, un parent déclaré par
 *                  fiche dont la référence ne se résout pas — écrire produirait un fait faux.
 *   - « défaut » : l'entité EST écrite, et quelque chose cloche dans sa transcription. Une clé
 *                  absente, un vide sans motif, une divergence au contrôle aval. Rien de faux
 *                  n'entre en base, mais la transcription est contestable.
 *
 * Les deux font sortir la commande en 1 : un défaut silencieux serait un défaut qu'on n'apprend
 * jamais. Ils restent distincts dans la synthèse pour que le lecteur sache ce qu'il a en base.
 */

/**
 * Libellés et accords propres à chaque jeu.
 *
 * Une portée est un nom féminin, un chien un nom masculin : sans cette table, la synthèse
 * écrirait « 4 portées créés », que personne dans cet élevage n'écrirait.
 *
 * @return array<string, array<string, mixed>> Jeu => libellés.
 */
function libelles_de_jeu(): array {
	return array(
		'chiens'  => array(
			'titre'    => 'Chiens',
			'cree'     => array( 'créé', 'créés' ),
			'present'  => array( 'déjà importé', 'déjà importés' ),
			'rejete'   => array( 'rejeté', 'rejetés' ),
			'objet'    => 'fiche créée',
			'garde'    => "La fiche n'a pas été supprimée.",
			'entite'   => array( 'entrée de chien', 'entrées de chien' ),
			'lue'      => array( 'entrée de chien lue', 'entrées de chien lues' ),
		),
		'portees' => array(
			'titre'    => 'Portées',
			'cree'     => array( 'créée', 'créées' ),
			'present'  => array( 'déjà importée', 'déjà importées' ),
			'rejete'   => array( 'rejetée', 'rejetées' ),
			'objet'    => 'portée créée',
			'garde'    => "La portée n'a pas été supprimée.",
			'entite'   => array( 'entrée de portée', 'entrées de portée' ),
			'lue'      => array( 'entrée de portée lue', 'entrées de portée lues' ),
		),
	);
}

/**
 * Libellé d'un rôle de parent tel qu'il s'écrit dans un message.
 *
 * @param string $role « pere » ou « mere ».
 *
 * @return string Libellé accentué.
 */
function libelle_de_role( string $role ): string {
	return 'pere' === $role ? 'père' : 'mère';
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
		'jeux'        => array(),
		'photos'      => array(),
		'liaisons'    => array(),
		'conversions' => array(),
		'controles'   => array(),
		'rejets'      => 0,
		'defauts'     => 0,
	);

	return $registre;
}

/**
 * Ouvre la session de rapport pour les jeux réellement demandés.
 *
 * @param string[] $jeux Jeux demandés.
 */
function demarrer( array $jeux ): void {
	$registre = &registre();

	$registre['jeux']        = array();
	$registre['photos']      = array();
	$registre['liaisons']    = array();
	$registre['controles']   = array();
	$registre['conversions'] = array(
		'lignes'    => array(),
		'galerie'   => 0,
		'images'    => 0,
		'liens'     => 0,
		'nues'      => 0,
		'cadres'    => 0,
		'orphelins' => 0,
		'entites'   => 0,
	);
	$registre['rejets']      = 0;
	$registre['defauts']     = 0;

	foreach ( $jeux as $jeu ) {
		$registre['jeux'][ $jeu ] = array(
			'lues'    => 0,
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
 * @param string $quoi « lues », « cree », « present » ou « rejete ».
 */
function compter( string $jeu, string $quoi ): void {
	$registre = &registre();

	if ( ! isset( $registre['jeux'][ $jeu ][ $quoi ] ) ) {
		return;
	}

	++$registre['jeux'][ $jeu ][ $quoi ];
}

/**
 * Nombre d'entités refusées, tous jeux confondus.
 *
 * @return int Nombre de rejets.
 */
function rejets(): int {
	$registre = &registre();

	return (int) $registre['rejets'];
}

/**
 * Nombre de défauts de transcription constatés sur des entités pourtant écrites.
 *
 * @return int Nombre de défauts.
 */
function defauts(): int {
	$registre = &registre();

	return (int) $registre['defauts'];
}

/**
 * Accorde un nombre et son libellé.
 *
 * Le singulier vaut jusqu'à un inclus : « 0 déjà importée », « 1 déjà importée », « 2 déjà
 * importées ».
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
 * Signale une entité refusée : elle n'est pas écrite.
 *
 * @param string   $fichier     Nom du fichier de données.
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
 * Signale un défaut de transcription sur une entité qui est, elle, écrite.
 *
 * @param string   $fichier     Nom du fichier de données.
 * @param int      $index       Index de l'entrée, fondé sur zéro.
 * @param string   $identifiant Identifiant métier de l'entrée.
 * @param string[] $defauts     Défauts rédigés, un par clé.
 */
function signaler_defauts( string $fichier, int $index, string $identifiant, array $defauts ): void {
	$registre = &registre();

	foreach ( $defauts as $defaut ) {
		++$registre['defauts'];

		\WP_CLI::warning( phrase_dentree( $fichier, $index, $identifiant, $defaut ) );
	}
}

/**
 * Signale qu'un contenu créé ne porte pas la valeur demandée, sans jamais le supprimer.
 *
 * @param string   $jeu         Jeu concerné.
 * @param string   $fichier     Nom du fichier de données.
 * @param int      $index       Index de l'entrée, fondé sur zéro.
 * @param string   $identifiant Identifiant métier de l'entrée.
 * @param string[] $divergences Divergences rédigées, une par champ.
 */
function signaler_divergences( string $jeu, string $fichier, int $index, string $identifiant, array $divergences ): void {
	$libelles = libelles_de_jeu();

	if ( ! isset( $libelles[ $jeu ] ) ) {
		return;
	}

	$rediges = array();

	foreach ( $divergences as $divergence ) {
		$rediges[] = sprintf( '%s, mais %s %s', $libelles[ $jeu ]['objet'], $divergence, $libelles[ $jeu ]['garde'] );
	}

	signaler_defauts( $fichier, $index, $identifiant, $rediges );
}

/**
 * Compose le message d'une filiation qui n'a pas pu être écrite.
 *
 * Sur une fiche Chien, c'est un DÉFAUT et non un rejet : la fiche est déjà écrite, et la reprise ne
 * supprime jamais. Sur une portée, l'appelant en fait un rejet — une portée déclarée « parent par
 * fiche » sans fiche afficherait une généalogie muette, c'est-à-dire un fait faux qui attend qu'on
 * le remarque.
 *
 * @param string $jeu       « chiens » ou « portees ».
 * @param string $role      « pere » ou « mere ».
 * @param string $reference Référence introuvable.
 *
 * @return string Message rédigé.
 */
function message_de_filiation_manquante( string $jeu, string $role, string $reference ): string {
	$libelles = libelles_de_jeu();

	if ( 'portees' === $jeu ) {
		return sprintf(
			'aucune fiche ne porte la référence « %s » indiquée comme %s : la portée n\'est pas écrite.',
			$reference,
			libelle_de_role( $role )
		);
	}

	return sprintf(
		'aucune fiche ne porte la référence « %s » indiquée comme %s : la filiation n\'a pas été écrite. %s',
		$reference,
		libelle_de_role( $role ),
		$libelles['chiens']['garde']
	);
}

/**
 * Note la conversion des marqueurs de capture d'une entité, et l'ajoute à la synthèse.
 *
 * Une transformation qui ne se compte pas ne se relit pas : sans ces lignes, personne ne saurait
 * combien de liens ont été convertis ni combien d'images ont été retirées, et la conversion
 * deviendrait une boîte noire posée entre la transcription et la base.
 *
 * @param string               $titre      Libellé de l'entité, tel qu'il s'affichera.
 * @param array<string, mixed> $conversion Retour de convertir_les_marqueurs().
 */
function noter_conversion( string $titre, array $conversion ): void {
	$registre = &registre();
	$images   = (int) $conversion['galerie'] + (int) $conversion['images'];
	$total    = (int) $conversion['liens'] + $images + (int) $conversion['cadres'] + (int) $conversion['orphelins'];

	if ( 0 === $total ) {
		return;
	}

	++$registre['conversions']['entites'];

	foreach ( array( 'galerie', 'images', 'liens', 'nues', 'cadres', 'orphelins' ) as $quoi ) {
		$registre['conversions'][ $quoi ] += (int) $conversion[ $quoi ];
	}

	$registre['conversions']['lignes'][] = sprintf(
		'Texte de « %s » : %s, %s retirée%s, %s.%s',
		$titre,
		accorder( (int) $conversion['liens'], array( 'lien converti', 'liens convertis' ) ),
		accorder( $images, array( 'image', 'images' ) ),
		$images >= 2 ? 's' : '',
		accorder( (int) $conversion['cadres'], array( 'cadre converti', 'cadres convertis' ) ),
		0 === (int) $conversion['nues']
			? ''
			: sprintf( ' Dont %s rendue%s en URL visible, faute de texte d\'ancre.', accorder( (int) $conversion['nues'], array( 'ancre vide', 'ancres vides' ) ), (int) $conversion['nues'] >= 2 ? 's' : '' )
	);
}

/**
 * Signale qu'un texte converti porte encore une balise ou un marqueur qui ne devrait plus y être.
 *
 * @param string $titre   Libellé de l'entité.
 * @param int    $residus Nombre de résidus comptés.
 */
function signaler_residus( string $titre, int $residus ): void {
	$registre = &registre();

	++$registre['defauts'];

	\WP_CLI::warning(
		sprintf(
			'Texte de « %s » : %s après conversion — balise capable d\'appeler un domaine tiers, ou marqueur de capture qui s\'afficherait au visiteur. À corriger avant publication.',
			$titre,
			accorder( $residus, array( 'résidu', 'résidus' ) )
		)
	);
}

/**
 * Note une liaison de filiation réellement posée, pour qu'elle soit ratifiable une par une.
 *
 * Le contrat §5 lie sur une identité stricte de nom, jamais sur une ressemblance : chaque lien
 * est donc un arbitrage, et un arbitrage qui ne se lit nulle part n'est imputable à personne.
 *
 * @param string $titre     Libellé de l'entité porteuse, telle qu'elle s'affichera.
 * @param string $role      « pere » ou « mere ».
 * @param string $reference Référence de la fiche liée.
 * @param int    $post_id   Identifiant de la fiche liée.
 */
function noter_liaison( string $titre, string $role, string $reference, int $post_id ): void {
	$registre = &registre();

	$registre['liaisons'][] = sprintf(
		'Liaison posée : « %s » a pour %s la fiche « %s » (contenu %d).',
		$titre,
		libelle_de_role( $role ),
		$reference,
		$post_id
	);
}

/**
 * Note qu'une photo a été versée à la médiathèque.
 *
 * @param string   $identifiant  Identifiant IONOS retenu pour la pièce jointe.
 * @param int      $piece_jointe Identifiant de la pièce jointe.
 * @param string[] $partages     Autres identifiants IONOS de même condensé.
 */
function photo_importee( string $identifiant, int $piece_jointe, array $partages ): void {
	$registre = &registre();
	$ligne    = sprintf( 'Photo « %s » : versée à la médiathèque (contenu %d).', $identifiant, $piece_jointe );

	if ( array() !== $partages ) {
		$ligne .= sprintf(
			' Octets identiques à %s : une seule pièce jointe, réutilisée.',
			citer( $partages )
		);
	}

	$registre['photos'][] = $ligne;
}

/**
 * Note qu'une photo était déjà en médiathèque et n'a été retouchée en rien.
 *
 * @param string $identifiant  Identifiant IONOS sous lequel elle a été retrouvée.
 * @param int    $piece_jointe Identifiant de la pièce jointe.
 */
function photo_presente( string $identifiant, int $piece_jointe ): void {
	$registre = &registre();

	$registre['photos'][] = sprintf(
		'Photo « %s » : déjà en médiathèque (contenu %d), ni titre ni texte alternatif ni fichier retouchés.',
		$identifiant,
		$piece_jointe
	);
}

/**
 * Note qu'une photo est versée sans être rattachée à quoi que ce soit.
 *
 * @param string $identifiant Identifiant IONOS.
 * @param int    $partages    Nombre de fiches qui portent le même condensé.
 */
function photo_bandeau( string $identifiant, int $partages ): void {
	$registre = &registre();

	$registre['photos'][] = sprintf(
		'Photo « %s » : bandeau de rubrique, octets identiques sur %d pages. Versée à la médiathèque, rattachée à aucune galerie, portrait d\'aucun chien.',
		$identifiant,
		$partages
	);
}

/**
 * Signale une photo introuvable — un défaut, jamais un rejet.
 *
 * Une photo absente est un état de rendu prévu : les entités qui la citent restent créées sans
 * elle. Une généalogie absente, elle, serait un fait faux, et fait rejeter.
 *
 * @param string $identifiant Identifiant IONOS demandé.
 * @param string $dossier     Dossier où le fichier a été cherché.
 */
function photo_absente( string $identifiant, string $dossier ): void {
	$registre = &registre();

	++$registre['defauts'];

	\WP_CLI::warning(
		sprintf(
			'Photo « %s » : aucun fichier de ce nom dans %s. Les entités qui la citent sont créées sans elle.',
			$identifiant,
			$dossier
		)
	);
}

/**
 * Signale que le dossier des photographies n'existe pas — un défaut, et un seul.
 *
 * Cas réel et non théorique : « docs/ » n'est monté dans aucun conteneur de la pile, alors que
 * l'archive des photographies y vit. Sans montage, ou sans « --photos », rien n'est versé.
 *
 * @param string $dossier Dossier cherché.
 * @param int    $citees  Nombre de photographies citées par les fichiers de données.
 */
function dossier_de_photos_absent( string $dossier, int $citees ): void {
	$registre = &registre();

	++$registre['defauts'];

	\WP_CLI::warning(
		sprintf(
			'Dossier des photographies introuvable : %s. %s ne %s versée%s ; les entités sont créées sans galerie ni portrait. Montez « docs/migration/source » dans le conteneur, ou indiquez « --photos=<dossier> ».',
			$dossier,
			accorder( $citees, array( 'photographie citée', 'photographies citées' ) ),
			$citees >= 2 ? 'seront' : 'sera',
			$citees >= 2 ? 's' : ''
		)
	);
}

/**
 * Note une ligne d'information sans gravité.
 *
 * @param string $message Message rédigé.
 */
function informer( string $message ): void {
	\WP_CLI::log( $message );
}

/**
 * Interrompt la commande avant toute écriture : le fichier est illisible.
 *
 * @param string $chemin Chemin demandé.
 */
function echec_fichier_illisible( string $chemin ): void {
	\WP_CLI::error( sprintf( 'Fichier de données introuvable ou illisible : %s. Aucun contenu n\'a été importé.', $chemin ) );
}

/**
 * Interrompt la commande avant toute écriture : le JSON est invalide.
 *
 * @param string $chemin  Chemin du fichier.
 * @param string $message Message rendu par le décodeur.
 */
function echec_json_invalide( string $chemin, string $message ): void {
	\WP_CLI::error( sprintf( 'Fichier de données illisible, JSON invalide : %s (%s). Aucun contenu n\'a été importé.', $chemin, $message ) );
}

/**
 * Interrompt la commande avant toute écriture : la racine n'est pas une liste.
 *
 * @param string $chemin Chemin du fichier.
 */
function echec_racine_invalide( string $chemin ): void {
	\WP_CLI::error( sprintf( 'Fichier de données mal formé : %s ne contient pas une liste d\'entrées. Aucun contenu n\'a été importé.', $chemin ) );
}

/**
 * Interrompt la commande avant toute écriture : la base porte déjà un jeu de démonstration.
 *
 * @param string[] $titres Titres fautifs relevés, au plus quelques-uns.
 */
function echec_base_de_demonstration( array $titres ): void {
	\WP_CLI::error(
		sprintf(
			'La base porte du contenu de démonstration (%s). Une base semée par « wp mtb import-fixtures » n\'est pas une base d\'accueil pour la reprise : une fois les deux jeux mêlés, le fictif ne se distingue plus du réel à l\'œil nu. Repartez d\'une base vide (« docker compose down -v »). Aucun contenu n\'a été importé.',
			citer( $titres )
		)
	);
}

/**
 * Cite une liste d'éléments dans un message.
 *
 * @param string[] $elements Éléments à citer.
 *
 * @return string Liste citée, séparée par des virgules.
 */
function citer( array $elements ): string {
	$cites = array();

	foreach ( $elements as $element ) {
		$cites[] = '« ' . $element . ' »';
	}

	return implode( ', ', $cites );
}

/**
 * Rend une valeur lisible dans un message, sans jamais la transformer.
 *
 * @param mixed $valeur Valeur brute.
 *
 * @return string Valeur citée entre guillemets français.
 */
function rendre_valeur( $valeur ): string {
	if ( is_bool( $valeur ) ) {
		return $valeur ? '« true »' : '« false »';
	}

	if ( null === $valeur ) {
		return '« null »';
	}

	if ( is_scalar( $valeur ) ) {
		return '« ' . (string) $valeur . ' »';
	}

	$rendu = wp_json_encode( $valeur, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );

	return '« ' . ( is_string( $rendu ) ? $rendu : '?' ) . ' »';
}

/**
 * Ouvre une passe de vérification et en annonce l'objet.
 *
 * @param int    $rang  Numéro de la passe.
 * @param string $objet Ce que la passe prouve.
 */
function ouvrir_controle( int $rang, string $objet ): void {
	\WP_CLI::log( sprintf( '— Contrôle %d : %s', $rang, $objet ) );
}

/**
 * Enregistre le verdict d'une passe de vérification.
 *
 * @param string   $nom     Nom court de la passe.
 * @param string[] $echecs  Échecs nommés ; liste vide si la passe est concluante.
 * @param string[] $preuves Ce que la passe a réellement mesuré, chiffres compris, une ligne par
 *                          mesure. Une passe qui n'imprime aucune mesure ne prouve rien.
 */
function verdict_de_controle( string $nom, array $echecs, array $preuves ): void {
	$registre = &registre();

	$registre['controles'][] = array(
		'nom'    => $nom,
		'echecs' => count( $echecs ),
	);

	foreach ( $preuves as $preuve ) {
		\WP_CLI::log( '  ' . $preuve );
	}

	foreach ( $echecs as $echec ) {
		++$registre['defauts'];

		\WP_CLI::warning( $echec );
	}
}

/**
 * Imprime le rapport de l'import puis sort — code 1 dès un rejet ou un défaut, code 0 sinon.
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
					'%s : %s, %s, %s, %s.',
					$libelles[ $jeu ]['titre'],
					accorder( $compteurs['lues'], $libelles[ $jeu ]['lue'] ),
					accorder( $compteurs['cree'], $libelles[ $jeu ]['cree'] ),
					accorder( $compteurs['present'], $libelles[ $jeu ]['present'] ),
					accorder( $compteurs['rejete'], $libelles[ $jeu ]['rejete'] )
				)
			);

			foreach ( $total as $quoi => $valeur ) {
				$total[ $quoi ] = $valeur + $compteurs[ $quoi ];
			}
		}

		// Les photos et les liaisons se rangent entre les chiens et les portées, comme le déroulé.
		if ( 'chiens' === $jeu ) {
			foreach ( $registre['photos'] as $ligne ) {
				\WP_CLI::log( $ligne );
			}

			foreach ( $registre['liaisons'] as $ligne ) {
				\WP_CLI::log( $ligne );
			}

			foreach ( $registre['conversions']['lignes'] as $ligne ) {
				\WP_CLI::log( $ligne );
			}
		}
	}

	conclure_les_conversions();

	$contenus = accorder( $total['cree'], array( 'contenu créé', 'contenus créés' ) );
	$presents = accorder( $total['present'], array( 'déjà importé', 'déjà importés' ) );

	if ( 0 === rejets() && 0 === defauts() ) {
		\WP_CLI::success( sprintf( 'Reprise terminée : %s, %s, 0 rejet, 0 défaut.', $contenus, $presents ) );

		return;
	}

	sortir_en_echec(
		sprintf(
			'Reprise terminée avec %s et %s : %s, %s. Aucun contenu n\'a été supprimé.',
			accorder( rejets(), array( 'rejet', 'rejets' ) ),
			accorder( defauts(), array( 'défaut', 'défauts' ) ),
			$contenus,
			$presents
		)
	);
}

/**
 * Imprime la synthèse globale de la conversion des marqueurs de capture.
 */
function conclure_les_conversions(): void {
	$registre = &registre();
	$c        = $registre['conversions'];

	if ( 0 === (int) $c['entites'] ) {
		return;
	}

	\WP_CLI::log(
		sprintf(
			'Marqueurs de capture convertis sur %s : %s, %s retirée%s dont %s en lien d\'agrandissement de galerie, %s, %s supprimée%s. Aucune balise appelant un domaine tiers n\'est publiée.',
			accorder( (int) $c['entites'], array( 'entité', 'entités' ) ),
			accorder( (int) $c['liens'], array( 'lien converti', 'liens convertis' ) ),
			accorder( (int) $c['galerie'] + (int) $c['images'], array( 'image', 'images' ) ),
			(int) $c['galerie'] + (int) $c['images'] >= 2 ? 's' : '',
			(int) $c['galerie'],
			accorder( (int) $c['cadres'], array( 'cadre converti en lien', 'cadres convertis en liens' ) ),
			accorder( (int) $c['orphelins'], array( 'fermeture orpheline', 'fermetures orphelines' ) ),
			(int) $c['orphelins'] >= 2 ? 's' : ''
		)
	);
}

/**
 * Imprime le rapport de la vérification puis sort — code 1 dès un échec nommé.
 */
function conclure_verification(): void {
	$registre = &registre();
	$total    = 0;

	foreach ( $registre['controles'] as $controle ) {
		$total += (int) $controle['echecs'];

		\WP_CLI::log(
			sprintf(
				'%s : %s.',
				$controle['nom'],
				0 === (int) $controle['echecs'] ? 'concluant' : accorder( (int) $controle['echecs'], array( 'échec', 'échecs' ) )
			)
		);
	}

	if ( 0 === $total ) {
		\WP_CLI::success(
			sprintf(
				'Vérification terminée : %s, 0 échec. Rien n\'a été écrit.',
				accorder( count( $registre['controles'] ), array( 'contrôle passé', 'contrôles passés' ) )
			)
		);

		return;
	}

	sortir_en_echec(
		sprintf(
			'Vérification terminée avec %s sur %s. Rien n\'a été écrit.',
			accorder( $total, array( 'échec nommé', 'échecs nommés' ) ),
			accorder( count( $registre['controles'] ), array( 'contrôle', 'contrôles' ) )
		)
	);
}

/**
 * Sort en échec après avoir vidé la sortie standard.
 *
 * WP-CLI écrit avertissements et erreurs sur la sortie d'erreur, non tamponnée, et le rapport sur
 * la sortie standard, tamponnée dès qu'elle n'est pas un terminal. Sans ce vidage, un journal de
 * conteneur afficherait la conclusion AVANT les lignes qu'elle résume.
 *
 * @param string $message Message de conclusion.
 */
function sortir_en_echec( string $message ): void {
	fflush( STDOUT );

	\WP_CLI::error( $message );
}
