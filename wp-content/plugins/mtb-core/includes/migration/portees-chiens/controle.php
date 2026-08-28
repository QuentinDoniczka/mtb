<?php
/**
 * Contrôle amont d'une entrée transcrite, avant toute écriture.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\PorteesChiens;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * DEUX VERDICTS, ET LA LIGNE QUI LES SÉPARE
 *
 * REJET — l'entité n'est pas écrite. Réservé à ce qui mettrait un FAIT FAUX ou INCONTESTABLE en
 * base : une clé que le modèle ne connaît pas, une valeur non vide sans sa provenance, une valeur
 * que l'assainisseur du modèle ramène au défaut (donc détruite en silence), un sexe de chiot hors
 * de la liste fermée.
 *
 * DÉFAUT — l'entité est écrite, et la transcription est contestée. Réservé à ce qui ne met rien de
 * faux en base mais rend la transcription invérifiable : une clé absente, un vide sans motif. La
 * base est juste ; c'est le fichier qui doit être complété. Rejeter ici viderait la reprise entière
 * pour un défaut de documentation.
 *
 * LA RÈGLE DE VALEUR, EN UNE PHRASE : si la valeur du fichier diffère du défaut du modèle ET que
 * l'assainisseur déclaré du champ rend ce défaut, l'entrée est rejetée. Elle ne recopie aucune
 * liste fermée : elle interroge le modèle vivant, et attrape donc « DCD », « M », « true » aussi
 * bien qu'un statut inventé.
 */

/**
 * Chemins de clés qui échappent au contrôle de forme, sans échapper au contrôle de présence.
 *
 * Deux familles, et aucune ne porte un fait d'élevage transcrit :
 *
 *   - les clés d'IDENTITÉ — référence d'une fiche, identifiant et slug d'une portée, référence
 *     d'un parent : ce sont des clés de projet, pas des chaînes du site ;
 *   - le MODE DE SAISIE d'un parent — « pere.type », « mere.type ». « fiche » et « exterieur » sont
 *     notre décision de modélisation, pas un énoncé du site : le site source n'écrit JAMAIS
 *     « exterieur ». Exiger un extrait verbatim pour cette valeur est impossible par construction,
 *     et en fabriquer un serait inventer une provenance — exactement ce que ce format existe pour
 *     empêcher. Le contrôle de FORME est donc levé ; celui de VALEUR ne l'est pas, et un type qui
 *     ne vaut ni « fiche » ni « exterieur » fait toujours rejeter l'entrée par la règle de valeur.
 *     Mesuré sur les données : 54 occurrences en chaîne nue, dont 21 « fiche » ;
 *   - les clés de LISTE — galerie, chiots : une liste ne peut pas porter un « motif », son vide
 *     s'exprime en étant vide. Chaque rangée de chiots, elle, porte bien sa provenance et est
 *     contrôlée par controler_les_chiots().
 *
 * La clé « photo » n'y figure pas parce qu'elle n'entre jamais dans ce contrôle : elle n'a aucune
 * clé de méta, et son absence est légitime — l'image de tête d'une fiche peut être un bandeau de
 * rubrique partagé par seize pages, auquel cas il n'y a pas de portrait à citer.
 *
 * @return string[] Chemins de fichier.
 */
function chemins_sans_controle_de_forme(): array {
	return array_merge( chemins_de_projet(), array( CLE_GALERIE, CLE_CHIOTS ) );
}

/**
 * Chemins de clés dont l'absence n'est pas un oubli.
 *
 * Quatre fiches sur dix-sept portent la balise de non-indexation ; l'absence est la règle, et la
 * signaler treize fois noierait le signal qu'elle est censée porter.
 *
 * @return string[] Chemins de fichier.
 */
function chemins_facultatifs(): array {
	return array( CLE_FICHIER_ROBOTS );
}

/**
 * Contrôle une entrée et rend ses rejets et ses défauts.
 *
 * @param string $jeu    « chiens » ou « portees ».
 * @param mixed  $entree Entrée brute, telle qu'elle sort du décodeur.
 *
 * @return array<string, string[]> array{ rejets, defauts }.
 */
function controler( string $jeu, $entree ): array {
	if ( ! is_array( $entree ) || est_une_liste( $entree ) ) {
		return array(
			'rejets'  => array( "l'entrée n'est pas un objet JSON : elle ne peut porter aucune clé." ),
			'defauts' => array(),
		);
	}

	$rejets = array_merge(
		controler_les_cles( $jeu, $entree ),
		controler_les_groupes( $jeu, $entree ),
		controler_lidentite( $jeu, $entree )
	);

	if ( array() !== $rejets ) {
		// Sur une entrée dont la structure est fautive, tout contrôle de valeur dirait n'importe quoi.
		return array(
			'rejets'  => $rejets,
			'defauts' => array(),
		);
	}

	$provenances = controler_les_provenances( $jeu, $entree );

	return array(
		'rejets'  => array_merge(
			$provenances['rejets'],
			controler_les_valeurs( $jeu, $entree ),
			controler_le_mode_de_saisie( $jeu, $entree ),
			controler_les_chiots( $jeu, $entree )
		),
		'defauts' => $provenances['defauts'],
	);
}

/**
 * Refuse les clés de premier niveau que le modèle ne connaît pas.
 *
 * Une clé inconnue n'est jamais ignorée : elle enseignerait au lecteur un modèle qui n'existe pas,
 * et sa valeur ne serait stockée nulle part, en silence.
 *
 * @param string               $jeu    « chiens » ou « portees ».
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
 * @param string               $jeu    « chiens » ou « portees ».
 * @param array<string, mixed> $entree Entrée.
 *
 * @return string[] Raisons rédigées.
 */
function controler_les_groupes( string $jeu, array $entree ): array {
	$raisons = array();

	foreach ( groupes( $jeu ) as $groupe => $acceptees ) {
		if ( ! isset( $entree[ $groupe ] ) ) {
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
 * Sans elle, l'entité ne serait retrouvée par aucune relecture et serait RECRÉÉE à chaque
 * exécution, en silence. C'est le seul mode de panne qui casse l'idempotence.
 *
 * @param string               $jeu    « chiens » ou « portees ».
 * @param array<string, mixed> $entree Entrée.
 *
 * @return string[] Raisons rédigées.
 */
function controler_lidentite( string $jeu, array $entree ): array {
	$cle = 'chiens' === $jeu ? 'reference' : 'identifiant';

	if ( '' !== texte_souple( isset( $entree[ $cle ] ) ? $entree[ $cle ] : null ) ) {
		return array();
	}

	return array(
		sprintf(
			'la clé « %s » est vide : sans elle, la reprise recréerait cette entité à chaque exécution.',
			$cle
		),
	);
}

/**
 * Contrôle la forme à provenance de toutes les valeurs transcrites d'une entrée.
 *
 * @param string               $jeu    « chiens » ou « portees ».
 * @param array<string, mixed> $entree Entrée.
 *
 * @return array<string, string[]> array{ rejets, defauts }.
 */
function controler_les_provenances( string $jeu, array $entree ): array {
	$rejets  = array();
	$defauts = array();

	$sans_forme = chemins_sans_controle_de_forme();

	foreach ( cles_a_provenance( $jeu, $entree ) as $chemin => $etat ) {
		if ( ! $etat['presente'] ) {
			$defauts[] = raison_doubli( (string) $chemin );

			continue;
		}

		if ( in_array( (string) $chemin, $sans_forme, true ) ) {
			continue;
		}

		$rejets  = array_merge( $rejets, raisons_de_rejet( $etat['brut'], (string) $chemin ) );
		$defauts = array_merge( $defauts, raisons_de_defaut( $etat['brut'], (string) $chemin ) );
	}

	return array(
		'rejets'  => $rejets,
		'defauts' => $defauts,
	);
}

/**
 * Clés d'une entrée attendues au fichier, et leur état de présence.
 *
 * Les sous-clés de parent qui n'ont pas lieu d'être — le nom libre d'un parent déclaré par fiche,
 * la référence d'un parent extérieur — n'y figurent pas : leur absence n'est pas un oubli, c'est
 * la conséquence du mode de saisie choisi.
 *
 * @param string               $jeu    « chiens » ou « portees ».
 * @param array<string, mixed> $entree Entrée.
 *
 * @return array<string, array<string, mixed>> Chemin de fichier => array{ presente, brut }.
 */
function cles_a_provenance( string $jeu, array $entree ): array {
	$attendues = array();

	foreach ( cles_de_contenu( $jeu ) as $cle => $champ ) {
		$attendues[ $cle ] = array(
			'presente' => array_key_exists( $cle, $entree ),
			'brut'     => isset( $entree[ $cle ] ) ? $entree[ $cle ] : null,
		);
	}

	foreach ( array_keys( sources_json( $jeu ) ) as $meta ) {
		$chemin = chemin_json( $jeu, (string) $meta );

		$attendues[ $chemin ] = transcription( $jeu, $entree, (string) $meta );
	}

	foreach ( roles() as $role ) {
		foreach ( sous_cles_de_parent_hors_sujet( $jeu, $entree, $role ) as $chemin ) {
			unset( $attendues[ $chemin ] );
		}
	}

	foreach ( chemins_facultatifs() as $chemin ) {
		unset( $attendues[ $chemin ] );
	}

	return $attendues;
}

/**
 * Sous-clés d'un parent que son mode de saisie rend sans objet.
 *
 * Sur une portée, le mode est explicite : « fiche » ou « exterieur ». Sur un chien, il est implicite
 * — une référence désigne une fiche, un nom libre désigne un chien sans fiche — et le modèle ne
 * porte pas de clé de type. On lit donc le fichier tel qu'il est.
 *
 * « sante » n'est JAMAIS écarté, même sur un parent lié à une fiche. La chaîne de tests que le site
 * affiche sur la ligne de parents date de la saillie et peut différer de celle de la fiche : elle
 * doit être transcrite, ou son absence motivée. Elle cesse d'être affichée dès qu'on lie — c'est
 * une dette déclarée — mais elle reste stockée, et une valeur non transcrite serait perdue.
 *
 * @param string               $jeu    « chiens » ou « portees ».
 * @param array<string, mixed> $entree Entrée.
 * @param string               $role   « pere » ou « mere ».
 *
 * @return string[] Chemins de fichier sans objet.
 */
function sous_cles_de_parent_hors_sujet( string $jeu, array $entree, string $role ): array {
	if ( 'portees' === $jeu ) {
		$par_fiche = PARENT_PAR_FICHE === type_de_parent( $jeu, $entree, $role );
	} else {
		$par_fiche = '' !== texte_souple_de_groupe( $entree, $role, 'reference' );
	}

	return $par_fiche
		? array( $role . '.nom', $role . '.elevage' )
		: array( $role . '.reference' );
}

/**
 * Applique la règle de valeur à toutes les clés de méta de la passe ordinaire.
 *
 * @param string               $jeu    « chiens » ou « portees ».
 * @param array<string, mixed> $entree Entrée.
 *
 * @return string[] Raisons rédigées.
 */
function controler_les_valeurs( string $jeu, array $entree ): array {
	$raisons = array();

	foreach ( valeurs_brutes( $jeu, $entree ) as $meta => $brut ) {
		$defaut = defaut_de( $jeu, (string) $meta );

		// Rien à dire d'une valeur vide ou déjà égale au défaut : elle sera écrite telle quelle.
		if ( $brut === $defaut || '' === $brut ) {
			continue;
		}

		if ( assainir( $jeu, (string) $meta, $brut ) !== $defaut ) {
			continue;
		}

		$raisons[] = sprintf(
			'la valeur %s de la clé « %s » est refusée par %s() du modèle, qui la ramène au défaut : elle serait perdue en silence.',
			rendre_valeur( $brut ),
			chemin_json( $jeu, (string) $meta ),
			nom_de_lassainisseur( $jeu, (string) $meta )
		);
	}

	return $raisons;
}

/**
 * Exige un mode de saisie déclaré et connu pour chacun des deux parents d'une portée.
 *
 * CONTREPARTIE EXACTE DE L'EXEMPTION DE FORME accordée à « pere.type » et « mere.type ». La forme
 * n'est plus contrôlée sur ces deux clés ; leur valeur, elle, l'est deux fois plutôt qu'une.
 *
 * La règle de valeur ordinaire se tait sur un vide, et elle a raison partout ailleurs : sur presque
 * toutes les clés, un vide est un fait — le site ne l'énonce pas. Ici, non. « fiche » et
 * « exterieur » ne sont pas deux nuances d'un même champ, ce sont les deux seules façons dont une
 * portée peut désigner un parent, et le vide n'en est pas une troisième. Une portée écrite sans
 * mode de saisie n'aurait ni parent lié ni parent nommé : une généalogie muette, obtenue en
 * silence, sur une clé que le fichier a simplement oublié d'écrire.
 *
 * La liste acceptée est lue vivante dans le modèle, jamais recopiée ici.
 *
 * @param string               $jeu    « chiens » ou « portees ».
 * @param array<string, mixed> $entree Entrée.
 *
 * @return string[] Raisons rédigées.
 */
function controler_le_mode_de_saisie( string $jeu, array $entree ): array {
	if ( 'portees' !== $jeu ) {
		return array();
	}

	$raisons = array();
	$fermees = listes_fermees( $jeu );

	foreach ( roles() as $role ) {
		$meta = '_mtb_' . $role . '_type';

		// Un mode non vide mais inconnu est déjà rejeté par la règle de valeur : ne pas le dire deux fois.
		if ( '' !== type_de_parent( $jeu, $entree, $role ) ) {
			continue;
		}

		$raisons[] = sprintf(
			'le mode de saisie du %s n\'est pas déclaré : la clé « %s » est absente ou vide, et la portée n\'aurait ni parent lié ni parent nommé. Valeurs attendues : %s.',
			libelle_de_role( $role ),
			chemin_json( $jeu, $meta ),
			implode( ', ', $fermees[ $meta ] )
		);
	}

	return $raisons;
}

/**
 * Contrôle la liste nominative des chiots d'une portée.
 *
 * Le sexe est vérifié contre la liste fermée lue vivante : assainir_chiots() écarte silencieusement
 * un sexe inconnu, et la rangée serait stockée sans lui — un chiot sans sexe alors que le site en
 * énonce un.
 *
 * @param string               $jeu    « chiens » ou « portees ».
 * @param array<string, mixed> $entree Entrée.
 *
 * @return string[] Raisons rédigées.
 */
function controler_les_chiots( string $jeu, array $entree ): array {
	if ( 'portees' !== $jeu || ! isset( $entree[ CLE_CHIOTS ] ) ) {
		return array();
	}

	if ( ! is_array( $entree[ CLE_CHIOTS ] ) || ! est_une_liste( $entree[ CLE_CHIOTS ] ) ) {
		return array( sprintf( 'la clé « %s » doit être une liste de rangées.', CLE_CHIOTS ) );
	}

	$raisons = array();
	$sexes   = sexes_de_chiot();

	foreach ( $entree[ CLE_CHIOTS ] as $rang => $rangee ) {
		$prefixe = sprintf( '%s[%d]', CLE_CHIOTS, (int) $rang );

		if ( ! is_array( $rangee ) || est_une_liste( $rangee ) ) {
			$raisons[] = sprintf( 'la rangée « %s » n\'est pas un objet JSON.', $prefixe );

			continue;
		}

		foreach ( array_keys( $rangee ) as $cle ) {
			if ( ! in_array( (string) $cle, sous_cles_de_chiot(), true ) ) {
				$raisons[] = sprintf(
					'sous-clé « %s » inconnue dans « %s ». Sous-clés attendues : %s.',
					(string) $cle,
					$prefixe,
					implode( ', ', sous_cles_de_chiot() )
				);
			}
		}

		foreach ( sous_cles_de_chiot() as $sous_cle ) {
			// Une sous-clé absente est un oubli, jamais un rejet : elle se traite ailleurs.
			if ( ! array_key_exists( $sous_cle, $rangee ) ) {
				continue;
			}

			$brut = isset( $rangee[ $sous_cle ] ) ? $rangee[ $sous_cle ] : null;

			$raisons = array_merge( $raisons, raisons_de_rejet( $brut, $prefixe . '.' . $sous_cle ) );
		}

		$sexe = valeur( isset( $rangee['sexe'] ) ? $rangee['sexe'] : null );

		if ( '' !== $sexe && ! in_array( $sexe, $sexes, true ) ) {
			$raisons[] = sprintf(
				'le sexe %s de « %s.sexe » n\'appartient pas à la liste fermée du modèle. Valeurs attendues : %s.',
				rendre_valeur( $sexe ),
				$prefixe,
				implode( ', ', $sexes )
			);
		}
	}

	return $raisons;
}
