<?php
/**
 * Les cinq contrôles qui prouvent la reprise, sans jamais rien écrire.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\PorteesChiens;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * UNE VÉRIFICATION QUI SE DÉCLARE FAITE NE VAUT RIEN.
 *
 * Cinq passes, toutes mesurées, aucune déclarative :
 *
 *   1. complétude par soustraction — 17 − 17 = 0, 27 − 27 = 0, recomptée deux fois de deux façons
 *      indépendantes : par résolution entrée par entrée, puis par dénombrement direct en base ;
 *   2. chaque valeur non vide est un EXTRAIT LITTÉRAL de sa source, comparé caractère pour
 *      caractère après normalisation des fins de ligne ;
 *   3. contrôle aval en base sur les 44, jamais sur un échantillon : c'est lui, et lui seul, qui
 *      attrape « N/N » devenu « N\N » ou une insécable perdue ;
 *   4. les clés absentes, nommées comme des oublis et distinguées des vides à motif ;
 *   5. rejeu : sur base peuplée, 0 création — chaque entrée est déjà retrouvée par sa clé.
 *
 * Un échec est NOMMÉ, jamais un avertissement diffus : « 3 valeurs douteuses » n'appelle aucune
 * correction, « chien-tesla, titres.lof : l'extrait n'apparaît pas dans chiens/chien-tesla.md »
 * en appelle une.
 */

/**
 * Effectifs attendus, mesurés sur l'archive du source par le contrat #19.
 *
 * @return array<string, int> Jeu => nombre d'entités.
 */
function effectifs_attendus(): array {
	return array(
		'chiens'  => 17,
		'portees' => 27,
	);
}

/**
 * Valeurs de listes fermées exemptées du contrôle des extraits.
 *
 * Liste LIMITATIVE et codée en dur, et c'est voulu : une liste d'exceptions qui grossit est le
 * signal que l'approche dérape. Ces douze clés sont des clés de PROJET — elles n'ont jamais été
 * écrites par le site source, et exiger qu'elles y figurent verbatim n'aurait aucun sens. Elles ne
 * dispensent d'ailleurs de rien : le report d'un « DCD » du site vers la clé « disparu » est un
 * arbitrage écrit au contrat, pas une recopie, et c'est là qu'il se ratifie.
 *
 * @return string[] Valeurs exemptées.
 */
function valeurs_de_liste_fermee(): array {
	return array(
		'disparu',
		'reproducteur',
		'retraite',
		'en_cours_de_confirmation',
		'male',
		'femelle',
		'poil_long',
		'poil_court',
		'fiche',
		'exterieur',
		'oui',
		'non',
	);
}

/**
 * Exécute les cinq contrôles.
 *
 * @param array<string, array<int, mixed>> $entrees Entrées par jeu.
 * @param array<string, string>            $chemins Chemin du fichier par jeu.
 * @param array<string, mixed>             $options Options de la commande.
 */
function verifier( array $entrees, array $chemins, array $options ): void {
	$index = array();

	controle_completude( $entrees, $chemins, $index );
	controle_des_extraits( $entrees, $chemins, $options );
	controle_aval_en_base( $entrees, $chemins, $options, $index );
	controle_des_oublis( $entrees, $chemins );
	controle_de_rejeu( $entrees, $chemins, $index );
}

/**
 * Passe 1 — complétude par soustraction, recomptée deux fois de façon indépendante.
 *
 * @param array<string, array<int, mixed>> $entrees Entrées par jeu.
 * @param array<string, string>            $chemins Chemin du fichier par jeu.
 * @param array<string, int>               $index   Index des fiches Chien, complété ici.
 */
function controle_completude( array $entrees, array $chemins, array &$index ): void {
	ouvrir_controle( 1, 'complétude par soustraction, entrées transcrites contre contenus en base' );

	$libelles = libelles_de_jeu();
	$echecs   = array();
	$preuves  = array();
	$total    = array(
		'attendu'  => 0,
		'lues'     => 0,
		'resolues' => 0,
	);

	foreach ( effectifs_attendus() as $jeu => $attendu ) {
		$lues     = isset( $entrees[ $jeu ] ) ? count( $entrees[ $jeu ] ) : 0;
		$resolues = 0;

		foreach ( isset( $entrees[ $jeu ] ) ? $entrees[ $jeu ] : array() as $entree ) {
			if ( is_array( $entree ) && 0 < entite_existante( $jeu, cle_didentite( $jeu, $entree ), $index ) ) {
				++$resolues;
			}
		}

		$total['attendu']  += $attendu;
		$total['lues']     += $lues;
		$total['resolues'] += $resolues;

		$preuves[] = sprintf(
			'%s : attendues %d − transcrites %d = %d ; transcrites %d − retrouvées en base %d = %d ; %s de type « %s » en base au total.',
			$libelles[ $jeu ]['titre'],
			$attendu,
			$lues,
			$attendu - $lues,
			$lues,
			$resolues,
			$lues - $resolues,
			accorder( denombrer_en_base( $jeu ), array( 'contenu', 'contenus' ) ),
			type_de_contenu( $jeu )
		);

		$echecs = array_merge( $echecs, verdict_de_completude( $jeu, $attendu, $lues, $resolues ) );
	}

	// Le second recomptage du contrat : les deux jeux ensemble, 44 − 44 = 0.
	$preuves[] = sprintf(
		'Les deux jeux : attendues %d − transcrites %d = %d ; transcrites %d − retrouvées en base %d = %d.',
		$total['attendu'],
		$total['lues'],
		$total['attendu'] - $total['lues'],
		$total['lues'],
		$total['resolues'],
		$total['lues'] - $total['resolues']
	);

	verdict_de_controle( 'Complétude', $echecs, $preuves );
}

/**
 * Écarts constatés sur la complétude d'un jeu.
 *
 * @param string $jeu      « chiens » ou « portees ».
 * @param int    $attendu  Effectif attendu.
 * @param int    $lues     Entrées transcrites.
 * @param int    $resolues Entrées retrouvées en base.
 *
 * @return string[] Échecs nommés.
 */
function verdict_de_completude( string $jeu, int $attendu, int $lues, int $resolues ): array {
	$libelles = libelles_de_jeu();
	$echecs   = array();

	if ( $lues !== $attendu ) {
		$echecs[] = sprintf(
			'%s : %s pour %d attendues, il en %s %d.',
			$libelles[ $jeu ]['titre'],
			accorder( $lues, array( 'transcrite', 'transcrites' ) ),
			$attendu,
			$lues < $attendu ? 'manque' : 'reste',
			abs( $attendu - $lues )
		);
	}

	if ( $resolues !== $lues ) {
		$echecs[] = sprintf(
			'%s : %s, %s en base — %s ne %s pas.',
			$libelles[ $jeu ]['titre'],
			accorder( $lues, array( 'transcrite', 'transcrites' ) ),
			accorder( $resolues, array( 'retrouvée', 'retrouvées' ) ),
			accorder( $lues - $resolues, $libelles[ $jeu ]['entite'] ),
			$lues - $resolues >= 2 ? 's\'y retrouvent' : 's\'y retrouve'
		);
	}

	return $echecs;
}

/**
 * Passe 2 — chaque valeur non vide est un extrait littéral de sa source.
 *
 * @param array<string, array<int, mixed>> $entrees Entrées par jeu.
 * @param array<string, string>            $chemins Chemin du fichier par jeu.
 * @param array<string, mixed>             $options Options de la commande.
 */
function controle_des_extraits( array $entrees, array $chemins, array $options ): void {
	ouvrir_controle( 2, 'chaque valeur non vide figure littéralement dans le fichier source qu\'elle cite' );

	$echecs    = array();
	$verifiees = 0;
	$exemptees = 0;
	$racine    = racine_du_source( $options );

	/*
	 * Sans l'archive, la passe ne peut RIEN prouver, et c'est en soi l'échec le plus grave qu'elle
	 * puisse rendre : elle le dit en une ligne au lieu de sept cents. Elle ne se déclare surtout
	 * pas concluante — une vérification qui ne s'exécute pas ne vaut pas mieux qu'une absence de
	 * vérification, elle est pire, parce qu'elle rassure.
	 */
	if ( ! is_dir( $racine ) ) {
		verdict_de_controle(
			'Extraits',
			array(
				sprintf(
					'Archive du source introuvable : %s. Aucun extrait n\'est confrontable, le contrôle ne s\'est pas exécuté. La pile monte l\'archive en « /var/www/html/docs/migration/source » : vérifiez que ce dossier est bien là, ou indiquez « --source=<dossier> ».',
					$racine
				),
			),
			array( '0 valeur confrontée : le contrôle n\'a pas pu s\'exécuter.' )
		);

		return;
	}

	foreach ( $entrees as $jeu => $liste ) {
		foreach ( $liste as $rang => $entree ) {
			if ( ! is_array( $entree ) ) {
				continue;
			}

			$prefixe = sprintf( '%s [%d] « %s »', nom_de_fichier( $chemins[ $jeu ] ), (int) $rang, cle_didentite( (string) $jeu, $entree ) );

			foreach ( valeurs_a_verifier( (string) $jeu, $entree ) as $valeur ) {
				if ( ! est_renseignee( $valeur['brut'] ) ) {
					continue;
				}

				if ( $valeur['ferme'] && in_array( valeur( $valeur['brut'] ), valeurs_de_liste_fermee(), true ) ) {
					++$exemptees;

					continue;
				}

				++$verifiees;

				$echec = echec_dextrait( $valeur['brut'], $prefixe, $valeur['chemin'], $options );

				if ( '' !== $echec ) {
					$echecs[] = $echec;
				}
			}
		}
	}

	verdict_de_controle(
		'Extraits',
		$echecs,
		array(
			sprintf(
				'%s à leur source, %s au titre des listes fermées, %s.',
				accorder( $verifiees, array( 'valeur confrontée', 'valeurs confrontées' ) ),
				accorder( $exemptees, array( 'valeur exemptée', 'valeurs exemptées' ) ),
				accorder( count( $echecs ), array( 'échec nommé', 'échecs nommés' ) )
			),
		)
	);
}

/**
 * Confronte un extrait à son fichier source.
 *
 * UN SEUL CHEMIN échappe à la confrontation contiguë, et il est nommé ici :
 * « robots_source.extrait », pour la raison mesurée qu'écrit echec_des_balises_de_robots(). Il
 * n'échappe pas au contrôle : il en reçoit un autre, balise par balise.
 *
 * @param mixed                $brut    Valeur transcrite.
 * @param string               $prefixe Préfixe de message identifiant l'entrée.
 * @param string               $chemin  Chemin de la clé dans le fichier.
 * @param array<string, mixed> $options Options de la commande.
 *
 * @return string Échec rédigé, chaîne vide si la confrontation réussit.
 */
function echec_dextrait( $brut, string $prefixe, string $chemin, array $options ): string {
	if ( CLE_FICHIER_ROBOTS === $chemin ) {
		return echec_des_balises_de_robots( $brut, $prefixe, $options );
	}

	$declare = source( $brut );
	$fichier = fichier_de_source( $declare, $options );

	if ( '' === $fichier['chemin'] ) {
		return sprintf(
			'%s, clé « %s » : le fichier source déclaré « %s » est introuvable. L\'extrait n\'est confrontable à rien.',
			$prefixe,
			$chemin,
			$declare
		);
	}

	$texte = texte_de_source( (string) $fichier['chemin'] );

	if ( null === $texte ) {
		return sprintf(
			'%s, clé « %s » : le fichier source « %s » est illisible.',
			$prefixe,
			$chemin,
			$declare
		);
	}

	if ( false !== strpos( $texte, normaliser_fins_de_ligne( extrait( $brut ) ) ) ) {
		return '';
	}

	return sprintf(
		'%s, clé « %s » : l\'extrait déclaré n\'apparaît pas dans « %s ». Extrait cherché : %s. La valeur %s n\'est donc justifiée par rien.',
		$prefixe,
		$chemin,
		$declare,
		rendre_valeur( extrait( $brut ) ),
		rendre_valeur( valeur( $brut ) )
	);
}

/**
 * Confronte l'extrait du fait de non-indexation à sa source, BALISE PAR BALISE.
 *
 * EXEMPTION DE CONTIGUÏTÉ, ET ELLE NE PORTE QUE SUR « robots_source.extrait ».
 *
 * Le fait à recopier n'est pas une balise, c'est la COEXISTENCE CONTRADICTOIRE de deux balises
 * « robots » dans un même « <head> » : un « noindex, nofollow » en tête, un « index,follow » bien
 * plus loin. Mesuré sur les fichiers archivés : les deux balises sont distantes d'environ
 * 1 700 octets. Aucune sous-chaîne contiguë du fichier ne peut donc les porter toutes les deux, et
 * n'en citer qu'une ferait lire une page franchement non indexée — ce que la source ne dit pas.
 *
 * Le contrôle n'est pas remplacé par rien : on perd la contiguïté, jamais la vérifiabilité. Chaque
 * ligne de l'extrait doit figurer LITTÉRALEMENT dans le fichier source, fins de ligne normalisées.
 * Une balise inventée reste donc impossible à faire passer.
 *
 * @param mixed                $brut    Valeur transcrite.
 * @param string               $prefixe Préfixe de message identifiant l'entrée.
 * @param array<string, mixed> $options Options de la commande.
 *
 * @return string Échec rédigé, chaîne vide si chaque balise citée figure dans la source.
 */
function echec_des_balises_de_robots( $brut, string $prefixe, array $options ): string {
	$chemin  = CLE_FICHIER_ROBOTS . '.' . CLE_EXTRAIT;
	$declare = source( $brut );
	$fichier = fichier_de_source( $declare, $options );

	if ( '' === $fichier['chemin'] ) {
		return sprintf(
			'%s, clé « %s » : le fichier source déclaré « %s » est introuvable. L\'extrait n\'est confrontable à rien.',
			$prefixe,
			$chemin,
			$declare
		);
	}

	$texte = texte_de_source( (string) $fichier['chemin'] );

	if ( null === $texte ) {
		return sprintf(
			'%s, clé « %s » : le fichier source « %s » est illisible.',
			$prefixe,
			$chemin,
			$declare
		);
	}

	$balises = balises_de_lextrait( $brut );

	if ( array() === $balises ) {
		return sprintf(
			'%s, clé « %s » : aucun extrait n\'est cité. Le fait de non-indexation n\'est justifié par rien.',
			$prefixe,
			$chemin
		);
	}

	$absentes = array();

	foreach ( $balises as $balise ) {
		if ( false === strpos( $texte, $balise ) ) {
			$absentes[] = $balise;
		}
	}

	if ( array() === $absentes ) {
		return '';
	}

	return sprintf(
		'%s, clé « %s » : %s ne figure pas dans « %s » : %s. La coexistence des deux balises n\'est donc justifiée par rien.',
		$prefixe,
		$chemin,
		accorder( count( $absentes ), array( 'balise citée', 'balises citées' ) ),
		$declare,
		citer( $absentes )
	);
}

/**
 * Balises citées par l'extrait du fait de non-indexation, une par ligne.
 *
 * @param mixed $brut Valeur transcrite.
 *
 * @return string[] Lignes non blanches de l'extrait, fins de ligne normalisées.
 */
function balises_de_lextrait( $brut ): array {
	$lignes  = explode( "\n", normaliser_fins_de_ligne( extrait( $brut ) ) );
	$balises = array();

	foreach ( $lignes as $ligne ) {
		if ( '' !== trim( $ligne ) ) {
			$balises[] = $ligne;
		}
	}

	return $balises;
}

/**
 * Contenu d'un fichier source, fins de ligne normalisées, lu une seule fois.
 *
 * @param string $chemin Chemin absolu.
 *
 * @return string|null Contenu normalisé, null si le fichier est illisible.
 */
function texte_de_source( string $chemin ): ?string {
	static $lus = array();

	if ( array_key_exists( $chemin, $lus ) ) {
		return $lus[ $chemin ];
	}

	$brut = lire_texte( $chemin );

	$lus[ $chemin ] = null === $brut ? null : normaliser_fins_de_ligne( $brut );

	return $lus[ $chemin ];
}

/**
 * Valeurs d'une entrée soumises au contrôle des extraits.
 *
 * Les clés d'identité — référence, identifiant, slug — en sont exclues : ce sont des clés de
 * projet, pas des chaînes du site. Les listes de photographies aussi : un identifiant IONOS est un
 * nom de fichier. Les rangées de chiots, elles, y entrent une sous-clé à la fois.
 *
 * @param string               $jeu    « chiens » ou « portees ».
 * @param array<string, mixed> $entree Entrée du fichier.
 *
 * @return array<int, array<string, mixed>> array{ chemin, brut, ferme }.
 */
function valeurs_a_verifier( string $jeu, array $entree ): array {
	$valeurs = array();
	$fermees = listes_fermees( $jeu );
	$exclues = chemins_sans_controle_de_forme();

	foreach ( cles_de_contenu( $jeu ) as $cle => $champ ) {
		if ( in_array( $cle, $exclues, true ) || ! isset( $entree[ $cle ] ) ) {
			continue;
		}

		$valeurs[] = array(
			'chemin' => $cle,
			'brut'   => $entree[ $cle ],
			'ferme'  => false,
		);
	}

	foreach ( array_keys( sources_json( $jeu ) ) as $meta ) {
		$chemin = chemin_json( $jeu, (string) $meta );

		if ( in_array( $chemin, $exclues, true ) ) {
			continue;
		}

		$transcription = transcription( $jeu, $entree, (string) $meta );

		if ( ! $transcription['presente'] ) {
			continue;
		}

		$valeurs[] = array(
			'chemin' => $chemin,
			'brut'   => $transcription['brut'],
			'ferme'  => isset( $fermees[ $meta ] ),
		);
	}

	foreach ( chiots_transcrits( $jeu, $entree ) as $chiot ) {
		$valeurs[] = $chiot;
	}

	return $valeurs;
}

/**
 * Sous-valeurs des rangées de chiots d'une portée.
 *
 * @param string               $jeu    « chiens » ou « portees ».
 * @param array<string, mixed> $entree Entrée du fichier.
 *
 * @return array<int, array<string, mixed>> array{ chemin, brut, ferme }.
 */
function chiots_transcrits( string $jeu, array $entree ): array {
	if ( 'portees' !== $jeu || ! isset( $entree[ CLE_CHIOTS ] ) || ! is_array( $entree[ CLE_CHIOTS ] ) ) {
		return array();
	}

	$valeurs = array();

	foreach ( $entree[ CLE_CHIOTS ] as $rang => $rangee ) {
		if ( ! is_array( $rangee ) ) {
			continue;
		}

		foreach ( sous_cles_de_chiot() as $sous_cle ) {
			if ( ! isset( $rangee[ $sous_cle ] ) ) {
				continue;
			}

			$valeurs[] = array(
				'chemin' => sprintf( '%s[%d].%s', CLE_CHIOTS, (int) $rang, $sous_cle ),
				'brut'   => $rangee[ $sous_cle ],
				'ferme'  => 'sexe' === $sous_cle,
			);
		}
	}

	return $valeurs;
}

/**
 * Passe 3 — contrôle aval en base, sur les 44 et jamais sur un échantillon.
 *
 * @param array<string, array<int, mixed>> $entrees Entrées par jeu.
 * @param array<string, string>            $chemins Chemin du fichier par jeu.
 * @param array<string, mixed>             $options Options de la commande.
 * @param array<string, int>               $index   Index des fiches Chien.
 */
function controle_aval_en_base( array $entrees, array $chemins, array $options, array &$index ): void {
	ouvrir_controle( 3, 'ce que la base porte vraiment, relu champ par champ et comparé au fichier' );

	$photos    = photos_existantes( $entrees, $options );
	$echecs    = array();
	$controles = 0;

	foreach ( array( 'chiens', 'portees' ) as $jeu ) {
		foreach ( isset( $entrees[ $jeu ] ) ? $entrees[ $jeu ] : array() as $rang => $entree ) {
			if ( ! is_array( $entree ) ) {
				continue;
			}

			$identifiant = cle_didentite( $jeu, $entree );
			$post_id     = entite_existante( $jeu, $identifiant, $index );
			$prefixe     = sprintf( '%s [%d] « %s »', nom_de_fichier( $chemins[ $jeu ] ), (int) $rang, $identifiant );

			if ( 0 === $post_id ) {
				// L'absence est déjà nommée par les passes 1 et 5 ; ne pas la compter deux fois.
				continue;
			}

			++$controles;

			$divergences = controler_aval(
				$jeu,
				$post_id,
				metas_attendues( $jeu, $entree, $index, $photos ),
				champs_de_contenu( $jeu, $entree ),
				fait_de_robots( $entree )
			);

			foreach ( $divergences as $divergence ) {
				$echecs[] = $prefixe . ' — ' . $divergence;
			}
		}
	}

	verdict_de_controle(
		'Contrôle aval',
		$echecs,
		array(
			sprintf(
				'%s en base, champ par champ, sans échantillonnage.',
				accorder( $controles, array( 'entité relue', 'entités relues' ) )
			),
		)
	);
}

/**
 * Métas qu'une entrée doit porter en base, exactement comme l'import les écrit.
 *
 * @param string                              $jeu    « chiens » ou « portees ».
 * @param array<string, mixed>                $entree Entrée du fichier.
 * @param array<string, int>                  $index  Index des fiches Chien.
 * @param array<string, array<string, mixed>> $photos Index des photographies.
 *
 * @return array<string, mixed> Clé de méta => valeur brute attendue.
 */
function metas_attendues( string $jeu, array $entree, array &$index, array $photos ): array {
	$metas   = valeurs_brutes( $jeu, $entree );
	$galerie = pieces_rattachables(
		identifiants_de_photos( isset( $entree[ CLE_GALERIE ] ) ? $entree[ CLE_GALERIE ] : null ),
		$photos
	);

	foreach ( roles() as $role ) {
		$cle       = '_mtb_' . $role . '_fiche';
		$reference = texte_souple_de_groupe( $entree, $role, 'reference' );
		$attendue  = 'portees' === $jeu && PARENT_PAR_FICHE !== type_de_parent( $jeu, $entree, $role )
			? ''
			: $reference;

		$fiche = chien_par_reference( $attendue, $index );

		$metas[ $cle ] = 0 < $fiche ? $fiche : defaut_de( $jeu, $cle );
	}

	if ( 'portees' === $jeu ) {
		$metas['_mtb_chiots']  = chiots_bruts( $entree );
		$metas['_mtb_galerie'] = $galerie;

		return $metas;
	}

	$metas['_mtb_galerie'] = implode( ',', $galerie );

	return $metas;
}

/**
 * Passe 4 — les clés absentes, nommées comme des oublis.
 *
 * Distinguer un vide voulu d'un oubli est le seul point du format à provenance : sans cette passe,
 * une clé oubliée par le transcripteur et une donnée que le site n'énonce pas se ressemblent
 * exactement, et « le site ne le disait pas » devient invérifiable.
 *
 * @param array<string, array<int, mixed>> $entrees Entrées par jeu.
 * @param array<string, string>            $chemins Chemin du fichier par jeu.
 */
function controle_des_oublis( array $entrees, array $chemins ): void {
	ouvrir_controle( 4, 'les clés absentes, distinguées des vides à motif' );

	$echecs = array();
	$vides  = 0;

	foreach ( $entrees as $jeu => $liste ) {
		foreach ( $liste as $rang => $entree ) {
			if ( ! is_array( $entree ) ) {
				continue;
			}

			$prefixe = sprintf( '%s [%d] « %s »', nom_de_fichier( $chemins[ $jeu ] ), (int) $rang, cle_didentite( (string) $jeu, $entree ) );
			$verdict = controler( (string) $jeu, $entree );

			foreach ( $verdict['rejets'] as $rejet ) {
				$echecs[] = $prefixe . ' — ' . $rejet;
			}

			foreach ( $verdict['defauts'] as $defaut ) {
				$echecs[] = $prefixe . ' — ' . $defaut;
			}

			$vides += compter_les_vides_a_motif( (string) $jeu, $entree );
		}
	}

	verdict_de_controle(
		'Oublis',
		$echecs,
		array(
			sprintf(
				'%s, motif écrit en regard.',
				accorder( $vides, array( 'vide voulu recensé', 'vides voulus recensés' ) )
			),
		)
	);
}

/**
 * Compte les vides voulus correctement motivés d'une entrée.
 *
 * @param string               $jeu    « chiens » ou « portees ».
 * @param array<string, mixed> $entree Entrée du fichier.
 *
 * @return int Nombre de vides motivés.
 */
function compter_les_vides_a_motif( string $jeu, array $entree ): int {
	$vides = 0;

	foreach ( valeurs_a_verifier( $jeu, $entree ) as $valeur ) {
		if ( ! est_renseignee( $valeur['brut'] ) && '' !== motif( $valeur['brut'] ) ) {
			++$vides;
		}
	}

	return $vides;
}

/**
 * Passe 5 — rejeu : sur base peuplée, l'import ne créerait rien.
 *
 * @param array<string, array<int, mixed>> $entrees Entrées par jeu.
 * @param array<string, string>            $chemins Chemin du fichier par jeu.
 * @param array<string, int>               $index   Index des fiches Chien.
 */
function controle_de_rejeu( array $entrees, array $chemins, array &$index ): void {
	ouvrir_controle( 5, 'un nouveau passage de l\'import ne créerait rien' );

	$echecs = array();
	$deja   = 0;

	foreach ( array( 'chiens', 'portees' ) as $jeu ) {
		foreach ( isset( $entrees[ $jeu ] ) ? $entrees[ $jeu ] : array() as $rang => $entree ) {
			if ( ! is_array( $entree ) ) {
				continue;
			}

			$identifiant = cle_didentite( $jeu, $entree );

			if ( 0 < entite_existante( $jeu, $identifiant, $index ) ) {
				++$deja;

				continue;
			}

			$echecs[] = sprintf(
				'%s [%d] « %s » : aucune entité ne porte cette clé d\'identité en base. Un nouveau passage la CRÉERAIT — l\'idempotence n\'est pas démontrée.',
				nom_de_fichier( $chemins[ $jeu ] ),
				(int) $rang,
				$identifiant
			);
		}
	}

	verdict_de_controle(
		'Rejeu',
		$echecs,
		array(
			sprintf(
				'%s, 0 création à prévoir, 0 modification : la reprise ne touche jamais ce qui existe.',
				accorder( $deja, array( 'entité déjà reprise', 'entités déjà reprises' ) )
			),
		)
	);
}

/**
 * Clé d'identité d'une entrée selon son jeu.
 *
 * @param string               $jeu    « chiens » ou « portees ».
 * @param array<string, mixed> $entree Entrée du fichier.
 *
 * @return string Référence d'un chien, ou identifiant d'une portée.
 */
function cle_didentite( string $jeu, array $entree ): string {
	$cle = 'chiens' === $jeu ? 'reference' : 'identifiant';

	return texte_souple( isset( $entree[ $cle ] ) ? $entree[ $cle ] : null );
}

/**
 * Dénombre les contenus d'un type en base, tous statuts confondus.
 *
 * Deuxième comptage, volontairement indépendant du premier : il ne part pas des entrées mais de la
 * base. Deux méthodes qui tombent d'accord valent mieux qu'une méthode répétée deux fois.
 *
 * @param string $jeu « chiens » ou « portees ».
 *
 * @return int Nombre de contenus.
 */
function denombrer_en_base( string $jeu ): int {
	$trouves = get_posts(
		array(
			'post_type'              => type_de_contenu( $jeu ),
			'post_status'            => 'any',
			'fields'                 => 'ids',
			'posts_per_page'         => -1,
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	return count( $trouves );
}
