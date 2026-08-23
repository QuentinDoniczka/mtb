<?php
/**
 * Contrôle amont d'une entrée de fixture, avant toute écriture.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\ImportFixtures;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * LA RÈGLE AMONT, EN UNE PHRASE (contrat #29 §7.1)
 *
 * Si la valeur du fichier diffère du défaut du modèle ET que l'assainisseur déclaré du champ rend
 * ce défaut, l'entrée est rejetée. Cette règle ne recopie aucune liste fermée : elle interroge le
 * modèle vivant. Elle attrape « fiche_chien », « HD », « true ».
 *
 * Elle n'attrape pas « IGP » : sanitize_key() en fait « igp », qui n'est pas vide. Pour les DEUX
 * SEULS champs passés à sanitize_key — la discipline et le sexe d'un résultat — le contrôle ajoute
 * donc une appartenance aux listes lues vivantes. Une valeur fausse y serait sinon stockée sans une
 * erreur, et sortirait de tout tableau de résultats.
 *
 * Le contrôle aval (« ecriture.php ») ne remplace pas celui-ci : l'assainisseur est toujours
 * d'accord avec lui-même, et l'aval ne verrait donc ni « fiche_chien » ni « igp ».
 */

/**
 * Contrôle une entrée et rend les raisons de la rejeter.
 *
 * @param string $jeu    Jeu de fixtures.
 * @param mixed  $entree Entrée brute, telle qu'elle sort du décodeur.
 *
 * @return string[] Raisons rédigées ; liste vide si l'entrée est acceptable.
 */
function controler( string $jeu, $entree ): array {
	if ( ! is_array( $entree ) || est_une_liste( $entree ) ) {
		return array( "l'entrée n'est pas un objet JSON : elle ne peut porter aucun champ." );
	}

	return array_merge(
		controler_les_cles( $jeu, $entree ),
		controler_les_groupes( $jeu, $entree ),
		controler_lidentite( $jeu, $entree ),
		controler_les_valeurs( $jeu, $entree ),
		controler_les_listes_fermees( $jeu, $entree )
	);
}

/**
 * Refuse les clés de premier niveau que le modèle ne connaît pas.
 *
 * Une clé inconnue n'est jamais ignorée : « nb_males » ou « niveau_ou_titre » enseigneraient au
 * lecteur un modèle qui n'existe pas, et sa valeur ne serait stockée nulle part, en silence.
 *
 * @param string               $jeu    Jeu de fixtures.
 * @param array<string, mixed> $entree Entrée.
 *
 * @return string[] Raisons rédigées.
 */
function controler_les_cles( string $jeu, array $entree ): array {
	$acceptees = cles_acceptees( $jeu );
	$inconnues = array();

	foreach ( array_keys( $entree ) as $cle ) {
		if ( ! in_array( (string) $cle, $acceptees, true ) ) {
			$inconnues[] = (string) $cle;
		}
	}

	if ( array() === $inconnues ) {
		return array();
	}

	return array(
		sprintf(
			'%s inconnue%s du modèle : %s. Clés attendues : %s.',
			count( $inconnues ) >= 2 ? 'clés' : 'clé',
			count( $inconnues ) >= 2 ? 's' : '',
			citer( $inconnues ),
			implode( ', ', $acceptees )
		),
	);
}

/**
 * Refuse un groupe mal formé et les sous-clés que le modèle ne connaît pas.
 *
 * @param string               $jeu    Jeu de fixtures.
 * @param array<string, mixed> $entree Entrée.
 *
 * @return string[] Raisons rédigées.
 */
function controler_les_groupes( string $jeu, array $entree ): array {
	$raisons = array();

	foreach ( groupes( $jeu ) as $groupe => $acceptees ) {
		if ( ! isset( $entree[ $groupe ] ) || valeur_absente( $entree[ $groupe ] ) ) {
			continue;
		}

		if ( ! is_array( $entree[ $groupe ] ) || est_une_liste( $entree[ $groupe ] ) ) {
			$raisons[] = sprintf( 'la clé « %s » doit être un objet JSON.', $groupe );

			continue;
		}

		$inconnues = array();

		foreach ( array_keys( $entree[ $groupe ] ) as $cle ) {
			if ( ! in_array( (string) $cle, $acceptees, true ) ) {
				$inconnues[] = (string) $cle;
			}
		}

		if ( array() !== $inconnues ) {
			$raisons[] = sprintf(
				'sous-clé%s inconnue%s du groupe « %s » : %s. Sous-clés attendues : %s.',
				count( $inconnues ) >= 2 ? 's' : '',
				count( $inconnues ) >= 2 ? 's' : '',
				$groupe,
				citer( $inconnues ),
				implode( ', ', $acceptees )
			);
		}
	}

	return $raisons;
}

/**
 * Exige la clé qui donne son identité à l'entrée.
 *
 * Sans elle, l'entrée ne serait retrouvée par aucune relecture et serait RECRÉÉE à chaque
 * exécution, en silence — mode de panne réel, pas théorique (contrat §6).
 *
 * @param string               $jeu    Jeu de fixtures.
 * @param array<string, mixed> $entree Entrée.
 *
 * @return string[] Raisons rédigées.
 */
function controler_lidentite( string $jeu, array $entree ): array {
	if ( 'chiens' === $jeu ) {
		return '' === texte_de( $entree, 'reference' )
			? array( "la clé « reference » est vide : sans elle, l'import recréerait cette fiche à chaque exécution." )
			: array();
	}

	if ( 'portees' === $jeu ) {
		return '' === texte_de( $entree, 'identifiant' )
			? array( "la clé « identifiant » est vide : sans elle, l'import recréerait cette portée à chaque exécution." )
			: array();
	}

	$manquantes = array();

	foreach ( array( 'discipline', 'annee', 'niveau' ) as $cle ) {
		if ( '' === texte_de( $entree, $cle ) ) {
			$manquantes[] = $cle;
		}
	}

	if ( '' === texte_de( $entree, 'reference' ) && '' === texte_de( $entree, 'chien_nom' ) ) {
		$manquantes[] = 'reference ou chien_nom';
	}

	if ( array() === $manquantes ) {
		return array();
	}

	return array(
		sprintf(
			"le tuple d'identité est incomplet — %s manque : l'import recréerait ce résultat à chaque exécution.",
			citer( $manquantes )
		),
	);
}

/**
 * Applique la règle amont à toutes les clés de méta non relationnelles.
 *
 * @param string               $jeu    Jeu de fixtures.
 * @param array<string, mixed> $entree Entrée.
 *
 * @return string[] Raisons rédigées.
 */
function controler_les_valeurs( string $jeu, array $entree ): array {
	$raisons = array();

	foreach ( valeurs_brutes( $jeu, $entree ) as $meta => $brut ) {
		$defaut = defaut_de( $jeu, $meta );

		// Rien à dire d'une valeur absente ou déjà égale au défaut : elle sera écrite telle quelle.
		if ( $brut === $defaut ) {
			continue;
		}

		if ( assainir( $jeu, $meta, $brut ) !== $defaut ) {
			continue;
		}

		$raisons[] = sprintf(
			'la valeur %s de la clé « %s » est refusée par %s() du modèle, qui la ramène au défaut.',
			rendre_valeur( $brut ),
			chemin_json( $jeu, $meta ),
			nom_de_lassainisseur( $jeu, $meta )
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
 * @param string               $jeu    Jeu de fixtures.
 * @param array<string, mixed> $entree Entrée.
 *
 * @return string[] Raisons rédigées.
 */
function controler_les_listes_fermees( string $jeu, array $entree ): array {
	if ( 'resultats' !== $jeu ) {
		return array();
	}

	$raisons       = array();
	$appartenances = array(
		'discipline' => array(
			'liste'  => disciplines(),
			'source' => 'mtb_resultat_disciplines()',
			'accord' => 'inconnue',
		),
		'sexe'       => array(
			'liste'  => sexes_resultat(),
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
 * Chemin d'une clé de méta tel qu'il s'écrit dans le fichier.
 *
 * @param string $jeu  Jeu de fixtures.
 * @param string $meta Clé de méta.
 *
 * @return string Chemin de fichier, « groupe.cle » ou « cle ».
 */
function chemin_json( string $jeu, string $meta ): string {
	$sources = sources_json( $jeu );

	if ( ! isset( $sources[ $meta ] ) ) {
		return $meta;
	}

	$source = $sources[ $meta ];

	return '' === $source['groupe'] ? $source['cle'] : $source['groupe'] . '.' . $source['cle'];
}

/**
 * Rend une valeur lisible dans un message, sans jamais la transformer.
 *
 * Les booléens et les listes sont rendus dans leur notation JSON : « true » doit se lire « true »
 * dans l'avertissement, et non « 1 », sans quoi la correction à apporter au fichier resterait
 * illisible.
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
