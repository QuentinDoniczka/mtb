<?php
/**
 * Contrôle amont d'une entrée de reprise, avant toute écriture.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\ResultatsPages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * LA RÈGLE AMONT, EN UNE PHRASE
 *
 * Si la valeur du fichier diffère du défaut du modèle ET que l'assainisseur DÉCLARÉ du champ rend
 * ce défaut, l'entrée est rejetée. Cette règle ne recopie aucune liste fermée : elle interroge le
 * modèle vivant.
 *
 * Elle n'attrape pas « IGP » : sanitize_key() en fait « igp », qui n'est pas vide. Pour les DEUX
 * SEULS champs passés à sanitize_key — la discipline et le sexe — le contrôle ajoute donc une
 * appartenance aux listes lues vivantes. Une valeur fausse y serait sinon stockée sans une erreur,
 * et sortirait de tout tableau de résultats.
 *
 * Le contrôle aval (« ecriture.php ») ne remplace pas celui-ci : l'assainisseur est toujours
 * d'accord avec lui-même, et l'aval ne verrait donc ni une clé inconnue, ni « igp ».
 */

/**
 * Contrôle une entrée de « resultats.json » et rend les raisons de la rejeter.
 *
 * @param mixed $entree Entrée brute, telle qu'elle sort du décodeur.
 *
 * @return string[] Raisons rédigées ; liste vide si l'entrée est acceptable.
 */
function controler_resultat( $entree ): array {
	if ( ! is_array( $entree ) || est_une_liste( $entree ) ) {
		return array( "l'entrée n'est pas un objet JSON : elle ne peut porter aucun champ." );
	}

	return array_merge(
		controler_les_cles( $entree, cles_acceptees_resultat(), 'résultat' ),
		controler_lidentite_du_resultat( $entree ),
		controler_la_provenance_du_resultat( $entree ),
		controler_les_valeurs_du_resultat( $entree ),
		controler_les_listes_fermees( $entree )
	);
}

/**
 * Refuse les clés de premier niveau que le modèle ne connaît pas.
 *
 * Une clé inconnue n'est jamais ignorée : sa valeur ne serait stockée nulle part, en silence, et
 * elle enseignerait au lecteur un modèle qui n'existe pas.
 *
 * @param array<string, mixed> $entree    Entrée.
 * @param string[]             $acceptees Clés acceptées.
 * @param string               $quoi      Nom de l'objet contrôlé, pour le message.
 *
 * @return string[] Raisons rédigées.
 */
function controler_les_cles( array $entree, array $acceptees, string $quoi ): array {
	$inconnues = array();

	foreach ( array_keys( $entree ) as $cle ) {
		if ( ! in_array( (string) $cle, $acceptees, true ) ) {
			$inconnues[] = (string) $cle;
		}
	}

	if ( array() === $inconnues ) {
		return array();
	}

	$raisons = array(
		sprintf(
			'%s inconnue%s du modèle sur ce %s : %s. Clés attendues : %s.',
			count( $inconnues ) >= 2 ? 'clés' : 'clé',
			count( $inconnues ) >= 2 ? 's' : '',
			$quoi,
			citer( $inconnues ),
			implode( ', ', $acceptees )
		),
	);

	if ( in_array( 'reference', $inconnues, true ) && 'résultat' === $quoi ) {
		$raisons[] = 'Aucune clé de ce fichier ne peut désigner une fiche chien : c\'est ce qui rend '
			. 'impossible d\'y poser un lien faux. Un rattachement s\'écrit dans '
			. '« correspondances-chiens.json », avec sa justification.';
	}

	return $raisons;
}

/**
 * Exige les quatre clés qui donnent son identité à un résultat.
 *
 * Sans elles, l'entrée ne serait retrouvée par aucune relecture et serait RECRÉÉE à chaque
 * exécution, en silence.
 *
 * @param array<string, mixed> $entree Entrée.
 *
 * @return string[] Raisons rédigées.
 */
function controler_lidentite_du_resultat( array $entree ): array {
	$manquantes = array();

	foreach ( cles_didentite_resultat() as $cle ) {
		if ( '' === texte_de( $entree, $cle ) ) {
			$manquantes[] = $cle;
		}
	}

	if ( array() === $manquantes ) {
		return array();
	}

	return array(
		sprintf(
			"le tuple d'identité est incomplet — %s manque : la reprise recréerait ce résultat à "
			. 'chaque exécution.',
			citer( $manquantes )
		),
	);
}

/**
 * Exige une provenance complète : une entrée sans preuve n'est pas une reprise.
 *
 * @param array<string, mixed> $entree Entrée.
 *
 * @return string[] Raisons rédigées.
 */
function controler_la_provenance_du_resultat( array $entree ): array {
	$source = isset( $entree[ CLE_SOURCE ] ) ? $entree[ CLE_SOURCE ] : null;

	if ( ! is_array( $source ) || est_une_liste( $source ) ) {
		return array(
			'la clé « source » est absente ou n\'est pas un objet JSON : une entrée sans provenance '
			. 'est une affirmation sans preuve, et c\'est elle qui rend la reprise vérifiable.',
		);
	}

	$manquantes = array();

	foreach ( SOUS_CLES_SOURCE_RESULTAT as $cle ) {
		if ( '' === texte_de( $source, $cle ) ) {
			$manquantes[] = $cle;
		}
	}

	if ( array() === $manquantes ) {
		return array();
	}

	return array( sprintf( 'la provenance est incomplète — %s manque.', citer( $manquantes ) ) );
}

/**
 * Applique la règle amont à toutes les clés de méta d'un résultat.
 *
 * @param array<string, mixed> $entree Entrée.
 *
 * @return string[] Raisons rédigées.
 */
function controler_les_valeurs_du_resultat( array $entree ): array {
	$raisons = array();

	foreach ( valeurs_brutes_resultat( $entree ) as $meta => $brut ) {
		$defaut = defaut_de( (string) $meta );

		// Rien à dire d'une valeur absente ou déjà égale au défaut : elle sera écrite telle quelle.
		if ( $brut === $defaut ) {
			continue;
		}

		if ( assainir( (string) $meta, $brut ) !== $defaut ) {
			continue;
		}

		$raisons[] = sprintf(
			'la valeur %s de la clé « %s » est refusée par %s() du modèle, qui la ramène au défaut.',
			rendre_valeur( $brut ),
			cle_json_de_meta( (string) $meta ),
			nom_de_lassainisseur( (string) $meta )
		);
	}

	return $raisons;
}

/**
 * Exige l'appartenance aux deux listes fermées que l'assainissement ne protège pas.
 *
 * sanitize_key( 'RING' ) rend « ring » — juste par accident. sanitize_key( 'IGP' ) rend « igp »,
 * qui n'est la clé d'aucune discipline, et « content/resultat/champs.php » refuse délibérément
 * toute liste blanche pour ne jamais détruire une valeur devenue orpheline. Sans le contrôle
 * ci-dessous, la ligne serait stockée sans erreur et sortirait de tout tableau de résultats.
 *
 * @param array<string, mixed> $entree Entrée.
 *
 * @return string[] Raisons rédigées.
 */
function controler_les_listes_fermees( array $entree ): array {
	$raisons       = array();
	$appartenances = array(
		'discipline' => array(
			'liste'  => disciplines(),
			'source' => 'mtb_resultat_disciplines()',
			'accord' => 'inconnue',
		),
		'sexe'       => array(
			'liste'  => sexes(),
			'source' => 'mtb_resultat_sexes()',
			'accord' => 'inconnu',
		),
	);

	foreach ( $appartenances as $cle => $appartenance ) {
		$valeur = texte_de( $entree, $cle );

		if ( '' === $valeur || isset( $appartenance['liste'][ $valeur ] ) ) {
			continue;
		}

		$raisons[] = sprintf(
			'%s « %s » %s de %s. Valeurs attendues : %s.',
			$cle,
			$valeur,
			$appartenance['accord'],
			$appartenance['source'],
			implode( ', ', array_keys( $appartenance['liste'] ) )
		);
	}

	return $raisons;
}

/**
 * Contrôle une fiche de page et rend les raisons de la rejeter.
 *
 * @param mixed $page Objet décodé.
 *
 * @return string[] Raisons rédigées.
 */
function controler_page( $page ): array {
	if ( ! is_array( $page ) || est_une_liste( $page ) ) {
		return array( "la fiche de page n'est pas un objet JSON." );
	}

	$raisons = controler_les_cles( $page, cles_acceptees_page(), 'page' );

	foreach ( array( 'reference', 'titre' ) as $cle ) {
		if ( '' === texte_de( $page, $cle ) ) {
			$raisons[] = sprintf( 'la clé « %s » est vide : elle donne son identité à la page.', $cle );
		}
	}

	$statut = texte_de( $page, 'statut' );

	if ( ! in_array( $statut, STATUTS_ACCEPTES, true ) ) {
		$raisons[] = sprintf(
			'le statut « %s » n\'est pas repris : seuls %s sont acceptés.',
			$statut,
			citer( STATUTS_ACCEPTES )
		);
	}

	$source = isset( $page[ CLE_SOURCE ] ) ? $page[ CLE_SOURCE ] : null;

	if ( ! is_array( $source ) || est_une_liste( $source ) ) {
		$raisons[] = 'la clé « source » est absente ou n\'est pas un objet JSON.';
	} else {
		$manquantes = array();

		foreach ( SOUS_CLES_SOURCE_PAGE as $cle ) {
			if ( '' === texte_de( $source, $cle ) ) {
				$manquantes[] = $cle;
			}
		}

		if ( array() !== $manquantes ) {
			$raisons[] = sprintf( 'la provenance est incomplète — %s manque.', citer( $manquantes ) );
		}
	}

	$composition = isset( $page['composition'] ) ? $page['composition'] : array();

	if ( null === $composition ) {
		$composition = array();
	}

	if ( ! is_array( $composition ) || ! est_une_liste( $composition ) ) {
		$raisons[] = 'la clé « composition » doit être une liste ordonnée d\'entrées.';

		return $raisons;
	}

	foreach ( $composition as $rang => $entree ) {
		foreach ( controler_entree_de_composition( $entree ) as $raison ) {
			$raisons[] = sprintf( 'composition [%d] : %s', (int) $rang, $raison );
		}
	}

	return $raisons;
}

/**
 * Contrôle une entrée de composition : un bloc, ou un écart déclaré, et rien d'autre.
 *
 * @param mixed $entree Entrée brute.
 *
 * @return string[] Raisons rédigées.
 */
function controler_entree_de_composition( $entree ): array {
	if ( ! is_array( $entree ) || est_une_liste( $entree ) ) {
		return array( "l'entrée n'est pas un objet JSON." );
	}

	if ( ! isset( $entree[ CLE_SOURCE ] ) || ! is_string( $entree[ CLE_SOURCE ] ) ) {
		return array(
			'la clé « source » est absente : une entrée réclame toujours les lignes de la capture '
			. 'qu\'elle consomme, écart compris.',
		);
	}

	if ( isset( $entree['ecart'] ) ) {
		$raisons = controler_les_cles( $entree, cles_acceptees_ecart(), 'écart' );

		if ( '' === texte_de( $entree, 'ecart' ) ) {
			$raisons[] = 'la raison de l\'écart est vide : un écart porte toujours sa raison à côté '
				. 'des lignes qu\'il couvre.';
		}

		return $raisons;
	}

	$raisons = controler_les_cles( $entree, cles_acceptees_bloc(), 'bloc' );
	$bloc    = texte_de( $entree, 'bloc' );

	if ( '' === $bloc ) {
		return array_merge(
			$raisons,
			array( 'l\'entrée ne nomme ni un bloc, ni un écart.' )
		);
	}

	if ( ! bloc_enregistre( $bloc ) ) {
		$raisons[] = sprintf(
			'le bloc « %s » n\'est pas enregistré. Blocs disponibles : %s.',
			$bloc,
			implode( ', ', blocs_enregistres() )
		);

		return $raisons;
	}

	return array_merge(
		$raisons,
		controler_les_attributs( $bloc, $entree ),
		controler_les_paragraphes( $bloc, $entree )
	);
}

/**
 * Contrôle les attributs d'un bloc contre le schéma vivant de son type.
 *
 * @param string               $bloc   Nom du bloc.
 * @param array<string, mixed> $entree Entrée de composition.
 *
 * @return string[] Raisons rédigées.
 */
function controler_les_attributs( string $bloc, array $entree ): array {
	$attributs = isset( $entree['attributs'] ) ? $entree['attributs'] : array();

	if ( null === $attributs || '' === $attributs ) {
		return array();
	}

	if ( ! is_array( $attributs ) || est_une_liste( $attributs ) ) {
		return array( 'la clé « attributs » doit être un objet JSON.' );
	}

	$acceptes  = attributs_acceptes( $bloc );
	$interdits = attributs_interdits( $bloc );
	$declares  = attributs_declares( $bloc );
	$raisons   = array();

	foreach ( $attributs as $nom => $valeur ) {
		$nom = (string) $nom;

		if ( in_array( $nom, $interdits, true ) ) {
			$raisons[] = sprintf(
				'l\'attribut « %s » désigne un contenu de la base : un fichier de données ne peut pas '
				. 'le connaître. Le nom de fichier d\'une photo s\'écrit dans la clé « photo » de '
				. 'l\'entrée, et la reprise le résout.',
				$nom
			);

			continue;
		}

		if ( ! in_array( $nom, $acceptes, true ) ) {
			$raisons[] = sprintf(
				'l\'attribut « %s » n\'existe pas sur « %s ». Attributs acceptés : %s.',
				$nom,
				$bloc,
				array() === $acceptes ? 'aucun' : implode( ', ', $acceptes )
			);

			continue;
		}

		if ( valeur_absente( $valeur ) ) {
			continue;
		}

		$schema = isset( $declares[ $nom ] ) ? $declares[ $nom ] : array();

		if ( isset( $schema['enum'] ) && is_array( $schema['enum'] ) && ! in_array( $valeur, $schema['enum'], true ) ) {
			$raisons[] = sprintf(
				'la valeur %s de l\'attribut « %s » est hors de son énumération. Valeurs acceptées : %s.',
				rendre_valeur( $valeur ),
				$nom,
				implode( ', ', array_map( 'strval', $schema['enum'] ) )
			);

			continue;
		}

		if ( is_string( $valeur ) ) {
			$raisons = array_merge( $raisons, controler_un_attribut( $valeur, sprintf( 'l\'attribut « %s »', $nom ) ) );
		}
	}

	return $raisons;
}

/**
 * Contrôle la prose d'une entrée de composition.
 *
 * @param string               $bloc   Nom du bloc.
 * @param array<string, mixed> $entree Entrée de composition.
 *
 * @return string[] Raisons rédigées.
 */
function controler_les_paragraphes( string $bloc, array $entree ): array {
	$paragraphes = isset( $entree['paragraphes'] ) ? $entree['paragraphes'] : array();

	if ( null === $paragraphes || '' === $paragraphes || array() === $paragraphes ) {
		return array();
	}

	if ( ! is_array( $paragraphes ) || ! est_une_liste( $paragraphes ) ) {
		return array( 'la clé « paragraphes » doit être une liste ordonnée de textes.' );
	}

	if ( BLOC_A_PROSE !== $bloc ) {
		return array(
			sprintf(
				'le bloc « %s » ne porte pas d\'enfants : seul « %s » en accepte.',
				$bloc,
				BLOC_A_PROSE
			),
		);
	}

	$raisons = array();

	foreach ( $paragraphes as $rang => $texte ) {
		$rang = (int) $rang;

		if ( ! is_string( $texte ) ) {
			$raisons[] = sprintf( 'le paragraphe [%d] n\'est pas un texte.', $rang );

			continue;
		}

		if ( texte_blanc( $texte ) ) {
			$raisons[] = sprintf(
				'le paragraphe [%d] est vide ou ne contient qu\'une espace insécable : une ligne '
				. 'd\'espacement de l\'éditeur IONOS n\'est pas un paragraphe.',
				$rang
			);

			continue;
		}

		$raisons = array_merge(
			$raisons,
			controler_un_texte( $texte, sprintf( 'le paragraphe [%d]', $rang ) )
		);
	}

	return $raisons;
}

/**
 * Interdits de fond communs à toute chaîne reprise dans le contenu d'une page.
 *
 * @param string $texte Texte du fichier.
 * @param string $quoi  Désignation de la chaîne, pour le message.
 *
 * @return string[] Raisons rédigées.
 */
function controler_un_texte( string $texte, string $quoi ): array {
	$raisons = array();

	if ( false !== strpos( $texte, '[IMAGE' ) ) {
		$raisons[] = sprintf(
			'%s contient « [IMAGE » : une image de la capture ne se recopie pas en prose, elle se '
			. 'verse dans la médiathèque et se cite par la clé « photo » de l\'entrée.',
			$quoi
		);
	}

	if ( false !== strpos( $texte, '[IFRAME' ) ) {
		$raisons[] = sprintf(
			'%s contient « [IFRAME » : le nouveau site n\'appelle aucun domaine tiers.',
			$quoi
		);
	}

	if ( false !== stripos( $texte, 'mtbrabant.com' ) ) {
		$raisons[] = sprintf(
			'%s contient une adresse « mtbrabant.com » : aucune URL de l\'ancien site ne survit dans '
			. 'le contenu repris.',
			$quoi
		);
	}

	return $raisons;
}

/**
 * Interdits propres à une valeur d'attribut de bloc.
 *
 * Un attribut est stocké verbatim et échappé au rendu par le composant : la notation de lien de la
 * capture y resterait littérale, et le visiteur lirait « [LIEN href=… ] » à l'écran. Mieux vaut le
 * refuser que de le laisser paraître.
 *
 * @param string $texte Valeur du fichier.
 * @param string $quoi  Désignation de la valeur, pour le message.
 *
 * @return string[] Raisons rédigées.
 */
function controler_un_attribut( string $texte, string $quoi ): array {
	$raisons = controler_un_texte( $texte, $quoi );

	if ( false !== strpos( $texte, '[LIEN' ) ) {
		$raisons[] = sprintf(
			'%s contient « [LIEN » : un attribut de bloc est du texte, jamais du balisage. Un lien '
			. 'se pose dans un paragraphe.',
			$quoi
		);
	}

	return $raisons;
}

/**
 * Contrôle une entrée de « correspondances-chiens.json ».
 *
 * @param mixed    $entree Entrée brute.
 * @param string[] $noms   Noms de chien portés par les résultats transcrits.
 *
 * @return string[] Raisons rédigées.
 */
function controler_correspondance( $entree, array $noms ): array {
	if ( ! is_array( $entree ) || est_une_liste( $entree ) ) {
		return array( "l'entrée n'est pas un objet JSON." );
	}

	$raisons = controler_les_cles( $entree, cles_acceptees_correspondance(), 'correspondance' );

	$nom = isset( $entree['chien_nom'] ) && is_string( $entree['chien_nom'] ) ? $entree['chien_nom'] : '';

	if ( '' === $nom ) {
		$raisons[] = 'la clé « chien_nom » est vide.';
	} elseif ( ! in_array( $nom, $noms, true ) ) {
		/*
		 * Égalité stricte, caractère pour caractère, sans aucune normalisation : « Pegaz » et
		 * « Pégaz » sont deux chaînes, et « très probablement le même chien » est un jugement,
		 * jamais une fonction de normalisation.
		 */
		$raisons[] = sprintf(
			'aucun résultat ne porte le nom « %s », caractère pour caractère : une correspondance qui '
			. 'ne correspond à rien serait sans effet, et en silence.',
			$nom
		);
	}

	if ( '' === texte_de( $entree, 'reference' ) ) {
		$raisons[] = 'la clé « reference » est vide : elle porte le slug de la fiche chien.';
	}

	if ( '' === texte_de( $entree, 'justification' ) ) {
		$raisons[] = 'la clé « justification » est vide. Elle est obligatoire : c\'est elle qui rend '
			. 'impossible de faire entrer « très probablement le même chien » sans l\'écrire.';
	}

	return $raisons;
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
 * Cite une liste de clés dans un message.
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
