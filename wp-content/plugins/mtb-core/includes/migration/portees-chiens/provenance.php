<?php
/**
 * Lecture du format à provenance : toute valeur transcrite dit d'où elle vient.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\PorteesChiens;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * LE FORMAT, ET LA RAISON POUR LAQUELLE IL EST LOURD
 *
 * Toute valeur non vide s'écrit :
 *
 *     { "valeur": "…", "source": "portees/portee-a3-2025.md", "extrait": "…verbatim…" }
 *
 * « extrait » est une sous-chaîne LITTÉRALE du fichier « source ». C'est ce qui rend la
 * transcription contestable par un tiers : sans elle, un chiffre transcrit et un chiffre inventé
 * ont exactement la même tête. Le contrôle §9.2 la vérifie caractère pour caractère.
 *
 * TROIS ÉTATS, ET ILS NE SE CONFONDENT JAMAIS — c'est toute la raison d'être du format :
 *
 *   { "valeur": "A3 2025", "source": "…", "extrait": "…" }   la source l'énonce
 *   { "valeur": "", "motif": "le site ne l'énonce jamais" }  vide VOULU, et le motif le dit
 *   clé absente                                              OUBLI, et il est nommé
 *
 * Sans le motif obligatoire, un oubli de transcription est indiscernable d'une absence dans le
 * site source, et « le site ne le disait pas » devient invérifiable.
 */

/**
 * Clé portant la valeur transcrite.
 */
const CLE_VALEUR = 'valeur';

/**
 * Clé portant le fichier source, relatif à la racine de l'archive.
 */
const CLE_SOURCE = 'source';

/**
 * Clé portant l'extrait verbatim qui justifie la valeur.
 */
const CLE_EXTRAIT = 'extrait';

/**
 * Clé portant le motif d'un vide voulu.
 */
const CLE_MOTIF = 'motif';

/**
 * La valeur brute a-t-elle la forme d'un objet à provenance ?
 *
 * @param mixed $brut Valeur brute issue du fichier.
 *
 * @return bool Vrai si c'est un objet JSON portant au moins la clé « valeur ».
 */
function est_un_objet_de_valeur( $brut ): bool {
	return is_array( $brut ) && ! est_une_liste( $brut ) && array_key_exists( CLE_VALEUR, $brut );
}

/**
 * Valeur transcrite, telle qu'elle figure au fichier.
 *
 * Aucune normalisation, aucun rognage : « LOF_ 13462 » porte une espace parasite que le site
 * source affiche, et la retirer inventerait un numéro d'inscription.
 *
 * @param mixed $brut Valeur brute.
 *
 * @return string Valeur transcrite, chaîne vide si la forme n'est pas celle attendue.
 */
function valeur( $brut ): string {
	if ( ! est_un_objet_de_valeur( $brut ) || ! is_scalar( $brut[ CLE_VALEUR ] ) ) {
		return '';
	}

	return (string) $brut[ CLE_VALEUR ];
}

/**
 * Sous-clé textuelle d'un objet à provenance.
 *
 * @param mixed  $brut Valeur brute.
 * @param string $cle  Sous-clé lue.
 *
 * @return string Valeur en chaîne, chaîne vide si absente ou non scalaire.
 */
function sous_cle( $brut, string $cle ): string {
	if ( ! is_array( $brut ) || ! isset( $brut[ $cle ] ) || ! is_scalar( $brut[ $cle ] ) ) {
		return '';
	}

	return (string) $brut[ $cle ];
}

/**
 * Fichier source déclaré par une valeur transcrite.
 *
 * @param mixed $brut Valeur brute.
 *
 * @return string Chemin relatif, chaîne vide si absent.
 */
function source( $brut ): string {
	return trim( sous_cle( $brut, CLE_SOURCE ) );
}

/**
 * Extrait verbatim déclaré par une valeur transcrite.
 *
 * Non rogné : un extrait dont les espaces de bord portent le sens reste comparable au fichier.
 *
 * @param mixed $brut Valeur brute.
 *
 * @return string Extrait, chaîne vide si absent.
 */
function extrait( $brut ): string {
	return sous_cle( $brut, CLE_EXTRAIT );
}

/**
 * Motif déclaré par un vide voulu.
 *
 * @param mixed $brut Valeur brute.
 *
 * @return string Motif, chaîne vide si absent.
 */
function motif( $brut ): string {
	return trim( sous_cle( $brut, CLE_MOTIF ) );
}

/**
 * La valeur transcrite énonce-t-elle quelque chose ?
 *
 * Jamais empty() : empty( '0' ) vaut vrai, et un effectif de zéro mâle est un fait d'élevage
 * légitime que le site peut énoncer.
 *
 * @param mixed $brut Valeur brute.
 *
 * @return bool Vrai si la valeur est non vide.
 */
function est_renseignee( $brut ): bool {
	return '' !== valeur( $brut );
}

/**
 * Raisons de refuser d'écrire à cause de la forme d'une valeur transcrite.
 *
 * Une valeur non vide sans sa provenance est exactement ce que le format existe pour interdire :
 * elle entrerait en base comme un fait, sans que rien ne permette de la contester. On ne l'écrit
 * pas.
 *
 * @param mixed  $brut   Valeur brute.
 * @param string $chemin Chemin de la clé dans le fichier, pour le message.
 *
 * @return string[] Raisons rédigées ; liste vide si la forme est acceptable.
 */
function raisons_de_rejet( $brut, string $chemin ): array {
	if ( ! est_un_objet_de_valeur( $brut ) ) {
		return array(
			sprintf(
				'la clé « %s » n\'est pas un objet à provenance : attendu { "valeur", "source", "extrait" }, ou { "valeur": "", "motif" } pour un vide voulu. Reçu %s.',
				$chemin,
				rendre_valeur( $brut )
			),
		);
	}

	if ( ! est_renseignee( $brut ) ) {
		return array();
	}

	$manquantes = array();

	if ( '' === source( $brut ) ) {
		$manquantes[] = CLE_SOURCE;
	}

	if ( '' === extrait( $brut ) ) {
		$manquantes[] = CLE_EXTRAIT;
	}

	if ( array() === $manquantes ) {
		return array();
	}

	return array(
		sprintf(
			'la clé « %s » porte la valeur %s sans %s : une valeur sans provenance n\'est pas contestable, elle n\'est pas écrite.',
			$chemin,
			rendre_valeur( valeur( $brut ) ),
			citer( $manquantes )
		),
	);
}

/**
 * Défauts de transcription d'une valeur transcrite, qui n'empêchent pas d'écrire.
 *
 * Un vide sans motif ne met rien de faux en base — la clé recevra le défaut du modèle. Mais il
 * rend l'oubli indiscernable de l'absence, ce qui est le seul point du format. Il est nommé.
 *
 * @param mixed  $brut   Valeur brute.
 * @param string $chemin Chemin de la clé dans le fichier, pour le message.
 *
 * @return string[] Défauts rédigés ; liste vide si rien à dire.
 */
function raisons_de_defaut( $brut, string $chemin ): array {
	if ( ! est_un_objet_de_valeur( $brut ) || est_renseignee( $brut ) ) {
		return array();
	}

	if ( '' !== motif( $brut ) ) {
		return array();
	}

	return array(
		sprintf(
			'la clé « %s » est vide sans « motif » : un vide voulu et un oubli de transcription ne se distinguent plus. La clé est écrite vide.',
			$chemin
		),
	);
}

/**
 * Défaut signalant une clé absente du fichier.
 *
 * @param string $chemin Chemin de la clé dans le fichier.
 *
 * @return string Défaut rédigé.
 */
function raison_doubli( string $chemin ): string {
	return sprintf(
		'la clé « %s » est absente : une clé absente est un oubli, pas un vide. Écrivez { "valeur": "", "motif": "…" } si la source ne l\'énonce pas. La clé est écrite vide.',
		$chemin
	);
}
