<?php
/**
 * Versement des photographies archivées dans la médiathèque.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\PorteesChiens;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * LA RÈGLE QUI COMMANDE TOUT : 192 FICHIERS, 150 IMAGES
 *
 * L'archive porte 192 fichiers pour 150 images distinctes. Trois groupes d'octets rigoureusement
 * identiques — 16, 16 et 12 fichiers — sont les bandeaux de rubrique que le site répétait en tête
 * de chaque fiche de chien. La conséquence est dure et contre-intuitive :
 *
 *   JAMAIS « première image de la page = image mise en avant ».
 *
 * La première image d'une fiche de chien EST un de ces bandeaux. La règle naïve donnerait le même
 * faux portrait à seize chiens. Une image byte-identique sur seize pages ne peut représenter aucun
 * individu : les trois condensés ci-dessous entrent en médiathèque — rien n'est supprimé — mais ne
 * sont rattachés à aucune galerie et ne deviennent jamais un portrait.
 *
 * DEUX GARDES, PAS UNE. La liste des trois condensés est recopiée du relevé, donc datée. Elle est
 * doublée d'une règle MESURÉE à l'exécution : un condensé cité par plus d'une entité ne peut pas
 * porter de portrait. La liste attrape les bandeaux connus, la mesure attrape ceux qu'on ne
 * connaît pas encore.
 *
 * UNE PIÈCE JOINTE PAR CONDENSÉ, PAS PAR IDENTIFIANT. Deux identifiants IONOS au même condensé sont
 * le même octet : ils partagent une seule pièce jointe, réutilisée. Le nom de fichier reste
 * l'identifiant IONOS, casse comprise, et le slug de la pièce jointe aussi — c'est par lui que le
 * rejeu la retrouve sans la recréer.
 *
 * AUCUN APPEL RÉSEAU. Les images sont lues sur le disque, dans l'archive du dépôt.
 */

/**
 * Condensés SHA-256 des trois bandeaux de rubrique.
 *
 * Recopiés du relevé de « docs/migration/source/photos/MANIFESTE.md », mesurés sur les octets
 * déposés : 7 758 octets sur 16 fichiers, 7 674 sur 16, 15 208 sur 12.
 *
 * @return string[] Condensés.
 */
function condenses_de_bandeau(): array {
	return array(
		'f920725d8563a666e3d3b8f0c6845dae05730855e35c808c799027208bc53ddd',
		'ce63e3c1c67d0fb958436f51cc0573cb9461f3ba6186c9fae19ad8ed5ba77708',
		'68914df7a4107796b5621f0779e87dce2bdf324ecbbbd35eeabeb12e0c774811',
	);
}

/**
 * Les deux seuls textes alternatifs que le site source écrit.
 *
 * Recopiés VERBATIM du §7 de « docs/migration/source/photos/MANIFESTE.md », qui les relève lui-même
 * verbatim de l'attribut « alt » du HTML archivé. Ce sont les deux seuls endroits où le site
 * attache un nom à une image ; rien n'en est déduit ici, ils sont reportés, pas interprétés.
 *
 * Ils vivent dans le code et non dans les fichiers de données parce que le format de ces fichiers,
 * gelé par le contrat, ne prévoit pour une photographie qu'un identifiant IONOS nu.
 *
 * @return array<string, string> Identifiant IONOS => texte alternatif du source.
 */
function textes_alternatifs_du_source(): array {
	return array(
		'14079090.JPG' => 'Nyx du Mont Brabant',
		'16717136.jpg' => 'Pluton',
	);
}

/**
 * Recense les photographies citées par les entités, dans l'ordre des fichiers.
 *
 * La citation retenue est la PREMIÈRE : c'est elle qui nommera la pièce jointe. Le titre dit où la
 * photo est publiée, jamais ce qu'elle montre — décrire une image supposerait de savoir qui est
 * dessus, et c'est précisément ce que le projet refuse d'inventer.
 *
 * @param array<string, array<int, mixed>> $entrees Entrées par jeu.
 *
 * @return array<string, array<string, mixed>> Identifiant IONOS => citation.
 */
function citations( array $entrees ): array {
	$citations = array();

	foreach ( array( 'chiens', 'portees' ) as $jeu ) {
		if ( ! isset( $entrees[ $jeu ] ) ) {
			continue;
		}

		foreach ( $entrees[ $jeu ] as $entree ) {
			if ( ! is_array( $entree ) ) {
				continue;
			}

			$libelles     = libelles_dentite( $jeu, $entree );
			$identifiants = identifiants_cites( $jeu, $entree );
			$total        = count( $identifiants );
			$rang         = 0;

			foreach ( $identifiants as $identifiant ) {
				++$rang;

				if ( isset( $citations[ $identifiant ] ) ) {
					++$citations[ $identifiant ]['entites'];

					continue;
				}

				$citations[ $identifiant ] = array(
					'titre'   => $libelles['titre'],
					'alt'     => $libelles['alt'],
					'rang'    => $rang,
					'total'   => $total,
					'entites' => 1,
				);
			}
		}
	}

	return $citations;
}

/**
 * Identifiants IONOS cités par une entité, dans l'ordre de la page.
 *
 * @param string               $jeu    « chiens » ou « portees ».
 * @param array<string, mixed> $entree Entrée du fichier.
 *
 * @return string[] Identifiants, sans doublon.
 */
function identifiants_cites( string $jeu, array $entree ): array {
	$cites = array();

	if ( 'chiens' === $jeu ) {
		$cites = identifiants_de_photos( isset( $entree[ CLE_PHOTO ] ) ? $entree[ CLE_PHOTO ] : null );
	}

	foreach ( identifiants_de_photos( isset( $entree[ CLE_GALERIE ] ) ? $entree[ CLE_GALERIE ] : null ) as $identifiant ) {
		if ( ! in_array( $identifiant, $cites, true ) ) {
			$cites[] = $identifiant;
		}
	}

	return $cites;
}

/**
 * Libellés d'une entité, tels qu'ils s'écrivent dans un titre de pièce jointe et dans un alt.
 *
 * @param string               $jeu    « chiens » ou « portees ».
 * @param array<string, mixed> $entree Entrée du fichier.
 *
 * @return array<string, string> array{ titre, alt }.
 */
function libelles_dentite( string $jeu, array $entree ): array {
	if ( 'chiens' === $jeu ) {
		$nom = texte_souple( isset( $entree['nom_usage'] ) ? $entree['nom_usage'] : null );

		return array(
			'titre' => sprintf( 'la fiche de %s', $nom ),
			'alt'   => sprintf( 'la fiche de %s', $nom ),
		);
	}

	$identifiant = texte_souple( isset( $entree['identifiant'] ) ? $entree['identifiant'] : null );

	return array(
		'titre' => sprintf( 'la portée %s', $identifiant ),
		'alt'   => sprintf( 'la page de la portée %s', $identifiant ),
	);
}

/**
 * S'assure que toutes les photographies citées sont en médiathèque.
 *
 * @param array<string, array<int, mixed>> $entrees Entrées par jeu.
 * @param array<string, mixed>             $options Options de la commande.
 *
 * @return array<string, array<string, mixed>> Identifiant IONOS => array{ piece, bandeau, partage }.
 */
function garantir_les_photos( array $entrees, array $options ): array {
	$dossier   = dossier_des_photos( $options );
	$citations = citations( $entrees );

	/*
	 * Un dossier absent est dit UNE FOIS. Sans cette garde, un dossier non monté produirait cent
	 * cinquante avertissements identiques, et la seule information utile — « le dossier n'est pas
	 * là » — se noierait dans le bruit qu'elle provoque.
	 */
	if ( ! is_dir( $dossier ) ) {
		dossier_de_photos_absent( $dossier, count( $citations ) );

		return array();
	}

	$groupes = grouper_par_condense( array_keys( $citations ), $dossier, $citations );
	$index   = array();

	foreach ( $groupes as $condense => $groupe ) {
		$piece = piece_jointe_du_groupe( (string) $condense, $groupe, $citations );

		foreach ( $groupe['identifiants'] as $identifiant ) {
			$index[ $identifiant ] = array(
				'piece'   => $piece,
				'bandeau' => in_array( (string) $condense, condenses_de_bandeau(), true ),
				'partage' => $groupe['entites'] > 1,
			);
		}
	}

	foreach ( array_keys( $citations ) as $identifiant ) {
		if ( isset( $index[ $identifiant ] ) ) {
			continue;
		}

		photo_absente( (string) $identifiant, $dossier );

		$index[ $identifiant ] = array(
			'piece'   => 0,
			'bandeau' => false,
			'partage' => false,
		);
	}

	return $index;
}

/**
 * Dresse l'index des photographies SANS RIEN VERSER, pour la commande de vérification.
 *
 * Même groupement par condensé, même sondage par slug, aucune écriture et aucune ligne de journal :
 * une commande qui vérifie ne doit rien changer à ce qu'elle observe.
 *
 * @param array<string, array<int, mixed>> $entrees Entrées par jeu.
 * @param array<string, mixed>             $options Options de la commande.
 *
 * @return array<string, array<string, mixed>> Identifiant IONOS => array{ piece, bandeau, partage }.
 */
function photos_existantes( array $entrees, array $options ): array {
	$citations = citations( $entrees );
	$groupes   = grouper_par_condense( array_keys( $citations ), dossier_des_photos( $options ), $citations );
	$index     = array();

	foreach ( $groupes as $condense => $groupe ) {
		$piece = 0;

		foreach ( $groupe['identifiants'] as $identifiant ) {
			$piece = piece_jointe_par_identifiant( (string) $identifiant );

			if ( 0 < $piece ) {
				break;
			}
		}

		foreach ( $groupe['identifiants'] as $identifiant ) {
			$index[ $identifiant ] = array(
				'piece'   => $piece,
				'bandeau' => in_array( (string) $condense, condenses_de_bandeau(), true ),
				'partage' => $groupe['entites'] > 1,
			);
		}
	}

	return $index;
}

/**
 * Groupe les identifiants cités par condensé de leurs octets.
 *
 * Un identifiant dont le fichier manque n'entre dans aucun groupe : il est signalé par l'appelant.
 *
 * @param string[]                             $identifiants Identifiants cités, dans l'ordre.
 * @param string                               $dossier      Dossier des photographies.
 * @param array<string, array<string, mixed>>  $citations    Citations, pour compter les entités.
 *
 * @return array<string, array<string, mixed>> Condensé => array{ identifiants, chemin, entites }.
 */
function grouper_par_condense( array $identifiants, string $dossier, array $citations ): array {
	$groupes = array();

	foreach ( $identifiants as $identifiant ) {
		$chemin = fichier_de_photo( (string) $identifiant, $dossier );

		if ( '' === $chemin ) {
			continue;
		}

		$condense = hash_file( 'sha256', $chemin );

		if ( ! is_string( $condense ) || '' === $condense ) {
			continue;
		}

		if ( ! isset( $groupes[ $condense ] ) ) {
			$groupes[ $condense ] = array(
				'identifiants' => array(),
				'chemin'       => $chemin,
				'entites'      => 0,
			);
		}

		$groupes[ $condense ]['identifiants'][] = (string) $identifiant;
		$groupes[ $condense ]['entites']       += isset( $citations[ $identifiant ]['entites'] )
			? (int) $citations[ $identifiant ]['entites']
			: 1;
	}

	return $groupes;
}

/**
 * Pièce jointe d'un groupe de condensé : celle qui existe, ou celle qu'on verse.
 *
 * Le rejeu passe ici sans rien écrire : chaque identifiant du groupe est sondé par son slug, et le
 * premier trouvé rend la pièce jointe. Une pièce jointe présente n'est JAMAIS retouchée — ni son
 * titre, ni son texte alternatif, ni son fichier. C'est le seul moyen de ne pas écraser une
 * correction que l'éleveuse aurait faite depuis la médiathèque.
 *
 * @param string                              $condense  Condensé du groupe.
 * @param array<string, mixed>                $groupe    Groupe.
 * @param array<string, array<string, mixed>> $citations Citations.
 *
 * @return int Identifiant de la pièce jointe, 0 si le versement a échoué.
 */
function piece_jointe_du_groupe( string $condense, array $groupe, array $citations ): int {
	foreach ( $groupe['identifiants'] as $identifiant ) {
		$existante = piece_jointe_par_identifiant( (string) $identifiant );

		if ( 0 < $existante ) {
			photo_presente( (string) $identifiant, $existante );

			return $existante;
		}
	}

	$portant    = (string) $groupe['identifiants'][0];
	$citation   = isset( $citations[ $portant ] ) ? $citations[ $portant ] : array();
	$bandeau    = in_array( $condense, condenses_de_bandeau(), true );
	$entites    = (int) $groupe['entites'];
	$titre      = $bandeau
		? sprintf( 'Image reprise du site d\'origine, identique sur %d pages (identifiant %s)', $entites, $portant )
		: titre_de_piece_jointe( $citation, $portant );
	$alternatif = texte_alternatif( $portant, $citation, $bandeau, $entites );

	$piece = verser( $portant, (string) $groupe['chemin'], $titre, $alternatif );

	if ( 0 === $piece ) {
		return 0;
	}

	if ( $bandeau ) {
		photo_bandeau( $portant, $entites );

		return $piece;
	}

	photo_importee( $portant, $piece, array_slice( $groupe['identifiants'], 1 ) );

	return $piece;
}

/**
 * Titre lisible d'une pièce jointe.
 *
 * C'est lui que l'éleveuse lit dans la médiathèque, jamais le nom de fichier. Il dit OÙ la photo
 * est publiée, et rien d'autre.
 *
 * @param array<string, mixed> $citation    Citation retenue.
 * @param string               $identifiant Identifiant IONOS.
 *
 * @return string Titre.
 */
function titre_de_piece_jointe( array $citation, string $identifiant ): string {
	if ( ! isset( $citation['titre'] ) || '' === (string) $citation['titre'] ) {
		return sprintf( 'Photo reprise du site d\'origine (identifiant %s)', $identifiant );
	}

	return sprintf(
		'Photo de %s (%d sur %d)',
		(string) $citation['titre'],
		(int) $citation['rang'],
		(int) $citation['total']
	);
}

/**
 * Texte alternatif d'une pièce jointe.
 *
 * DETTE D'ACCESSIBILITÉ DÉCLARÉE, PAS UNE CASE COCHÉE. Le site source n'écrit que deux « alt » ;
 * ils sont repris verbatim. Pour les autres, un alt factuel de CONTEXTE : pour un lecteur d'écran,
 * huit alternatives voisines sur une même page sont du bruit. « alt="" » serait pire — il
 * affirmerait que ces photographies sont décoratives, ce qui est faux. Entre un bruit vrai et un
 * silence faux, on prend le bruit vrai et on l'écrit. Seule l'éleveuse peut payer cette dette,
 * depuis la médiathèque, photo par photo.
 *
 * @param string               $identifiant Identifiant IONOS.
 * @param array<string, mixed> $citation    Citation retenue.
 * @param bool                 $bandeau     L'image est-elle un bandeau de rubrique ?
 * @param int                  $entites     Nombre d'entités qui citent ces octets.
 *
 * @return string Texte alternatif.
 */
function texte_alternatif( string $identifiant, array $citation, bool $bandeau, int $entites ): string {
	$du_source = textes_alternatifs_du_source();

	if ( isset( $du_source[ $identifiant ] ) ) {
		return $du_source[ $identifiant ];
	}

	if ( $bandeau ) {
		return sprintf( 'Image reprise du site d\'origine, identique sur %d pages.', $entites );
	}

	if ( ! isset( $citation['alt'] ) || '' === (string) $citation['alt'] ) {
		return 'Photographie reprise du site de l\'élevage.';
	}

	return sprintf( 'Photographie publiée sur %s.', (string) $citation['alt'] );
}

/**
 * Cherche le fichier d'une photographie, quelle que soit son extension.
 *
 * Comparaison de nom exacte, casse comprise, plutôt que motif de recherche : « .JPG » et « .jpg »
 * coexistent dans l'archive et ne sont pas harmonisés. Un motif donnerait à l'identifiant un sens
 * qu'il n'a pas.
 *
 * @param string $identifiant Identifiant IONOS.
 * @param string $dossier     Dossier des photographies.
 *
 * @return string Chemin du fichier, chaîne vide s'il n'existe pas.
 */
function fichier_de_photo( string $identifiant, string $dossier ): string {
	if ( '' === $identifiant || ! is_dir( $dossier ) ) {
		return '';
	}

	$entrees = scandir( $dossier );

	if ( false === $entrees ) {
		return '';
	}

	foreach ( $entrees as $entree ) {
		$chemin = $dossier . '/' . $entree;

		if ( ! is_file( $chemin ) ) {
			continue;
		}

		if ( $entree === $identifiant || pathinfo( $entree, PATHINFO_FILENAME ) === $identifiant ) {
			return $chemin;
		}
	}

	return '';
}

/**
 * Recopie l'image hors de l'archive, puis la verse dans la médiathèque.
 *
 * media_handle_sideload() DÉPLACE le fichier qu'on lui donne, et « docs/migration/source/photos/ »
 * est une archive de sauvegarde, pas un stock de travail : l'image est donc recopiée dans un
 * temporaire d'abord. Cette voie est aussi celle qui enchaîne wp_generate_attachment_metadata(),
 * l'appel qui écrit réellement les sous-tailles et les formats modernes.
 *
 * @param string $identifiant Identifiant IONOS, qui devient le slug de la pièce jointe.
 * @param string $source      Chemin du fichier dans l'archive.
 * @param string $titre       Titre lisible.
 * @param string $alternatif  Texte alternatif.
 *
 * @return int Identifiant de la pièce jointe, 0 en cas d'échec.
 */
function verser( string $identifiant, string $source, string $titre, string $alternatif ): int {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$nom        = basename( $source );
	$temporaire = wp_tempnam( $nom );

	if ( '' === $temporaire || ! copy( $source, $temporaire ) ) {
		return 0;
	}

	$piece = media_handle_sideload(
		array(
			'name'     => $nom,
			'tmp_name' => $temporaire,
		),
		0,
		null,
		array(
			'post_title' => wp_slash( $titre ),
			'post_name'  => wp_slash( $identifiant ),
		)
	);

	if ( is_wp_error( $piece ) ) {
		// media_handle_sideload() ne déplace pas le fichier quand il échoue : à nous de le retirer.
		if ( file_exists( $temporaire ) ) {
			wp_delete_file( $temporaire );
		}

		return 0;
	}

	update_post_meta( (int) $piece, '_wp_attachment_image_alt', wp_slash( $alternatif ) );

	return (int) $piece;
}

/**
 * Traduit une liste d'identifiants IONOS en identifiants de pièces jointes rattachables.
 *
 * Un bandeau de rubrique en est écarté : il n'appartient à la galerie d'aucun chien. Un
 * identifiant introuvable l'est aussi, l'avertissement ayant déjà été émis au versement.
 *
 * @param string[]                            $identifiants Identifiants cités, dans l'ordre.
 * @param array<string, array<string, mixed>> $photos       Index des photographies.
 *
 * @return array<int, int> Identifiants de pièces jointes, dans l'ordre, sans doublon.
 */
function pieces_rattachables( array $identifiants, array $photos ): array {
	$pieces = array();

	foreach ( $identifiants as $identifiant ) {
		if ( ! isset( $photos[ $identifiant ] ) || $photos[ $identifiant ]['bandeau'] ) {
			continue;
		}

		$piece = (int) $photos[ $identifiant ]['piece'];

		if ( 0 < $piece && ! in_array( $piece, $pieces, true ) ) {
			$pieces[] = $piece;
		}
	}

	return $pieces;
}

/**
 * Identifiant de l'image mise en avant d'une entité, s'il y en a une.
 *
 * DEUX REFUS, ET ILS SONT DIFFÉRENTS. Un bandeau connu ne peut pas être un portrait, parce que ses
 * octets sont ceux de seize autres pages. Une image dont les octets sont cités par plus d'une
 * entité ne le peut pas davantage, même si elle n'est pas au relevé : c'est la même erreur, et la
 * mesure la voit sans qu'on ait à la connaître d'avance. Une fiche sans portrait est un état de
 * rendu prévu ; un faux portrait, non.
 *
 * @param string[]                            $identifiants Identifiants cités, dans l'ordre.
 * @param array<string, array<string, mixed>> $photos       Index des photographies.
 *
 * @return int Identifiant de la pièce jointe, 0 s'il n'y a pas de portrait possible.
 */
function portrait_possible( array $identifiants, array $photos ): int {
	foreach ( $identifiants as $identifiant ) {
		if ( ! isset( $photos[ $identifiant ] ) ) {
			continue;
		}

		$photo = $photos[ $identifiant ];

		if ( $photo['bandeau'] || $photo['partage'] || 0 === (int) $photo['piece'] ) {
			continue;
		}

		return (int) $photo['piece'];
	}

	return 0;
}
