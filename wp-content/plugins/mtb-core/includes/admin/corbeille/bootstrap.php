<?php
/**
 * Avertissement ajouté au bandeau de sortie de corbeille des trois types de contenu.
 *
 * WordPress rend un contenu sorti de la corbeille en brouillon, c'est-à-dire hors du site, et son
 * bandeau ne le dit pas. L'éleveuse rétablit une portée, lit « Portée sortie de la corbeille. », et
 * ne comprend pas pourquoi le site ne la remontre pas. La phrase ajoutée ici est celle que les
 * fiches d'aide écrivent déjà, accordée aux trois types.
 *
 * ON COMPLÈTE, ON N'ÉCRASE JAMAIS. Les trois types renseignent déjà leurs cinq messages groupés
 * depuis « includes/fields/** », en français d'élevage, et deux fiches d'aide en citent le libellé
 * mot pour mot. Ce module s'enregistre donc en priorité 20 — après eux —, lit la phrase qu'ils ont
 * posée et lui ajoute son avertissement derrière. Il ne recompose pas cette phrase et ne la recopie
 * pas en dur : c'est tout l'intérêt de la priorité. Les quatre autres clés ne sont pas touchées.
 *
 * Module séparé de « admin/listes » à dessein : un message sur un parcours n'est ni une colonne, ni
 * un filtre, ni un ordre, et il se désactive d'un « _ » sans emporter les colonnes.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Admin\Corbeille;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'bulk_post_updated_messages', __NAMESPACE__ . '\\completer_messages', 20, 2 );

/**
 * Ajoute l'avertissement de retour en brouillon à la seule clé « untrashed » des trois types.
 *
 * DEUX RÈGLES À NE JAMAIS ENFREINDRE DANS LES CHAÎNES CI-DESSOUS. L'écran des listes passe le
 * message final à sprintf() avec un seul argument : le total ne doit donc porter AU PLUS un « %s »
 * — un second lèverait une ArgumentCountError, c'est-à-dire un écran blanc au retour d'un
 * rétablissement — et tout « % » littéral se doublerait en « %% ». Les phrases ajoutées ici n'en
 * portent aucun, et celles de « includes/fields/** » composent le nombre elles-mêmes.
 *
 * Défensif de bout en bout : si un type n'a pas de messages, si la clé « untrashed » manque ou
 * n'est pas une chaîne, le tableau est rendu inchangé. On préfère un bandeau incomplet à un
 * bandeau fabriqué sur une phrase absente.
 *
 * Le cœur ajoute de lui-même un lien « Modifier la portée » quand un seul contenu a été rétabli :
 * la phrase et le lien se complètent, et on n'empile jamais un second bandeau — ce serait deux
 * annonces au lecteur d'écran.
 *
 * Les paramètres ne sont pas typés, et ils sont contrôlés : un filtre tiers peut avoir rendu autre
 * chose qu'un tableau, et strict_types en ferait une erreur fatale sur la liste.
 *
 * @param mixed $bulk_messages Messages déjà déclarés, indexés par type de contenu.
 * @param mixed $bulk_counts   Nombre de contenus concernés, indexé par clé de message.
 *
 * @return mixed Messages complétés, ou la valeur reçue si elle n'est pas exploitable.
 */
function completer_messages( $bulk_messages, $bulk_counts ) {
	/*
	 * Première garde, comme dans tout ce groupe : rien ne s'exécute hors administration. Le cœur ne
	 * déclenche ce filtre que depuis wp-admin/edit.php, la garde ne retranche donc rien aujourd'hui ;
	 * elle est là pour le jour où un tiers l'appellerait de sa propre initiative.
	 */
	if ( ! is_admin() ) {
		return $bulk_messages;
	}

	if ( ! is_array( $bulk_messages ) ) {
		return $bulk_messages;
	}

	$comptes = is_array( $bulk_counts ) ? $bulk_counts : array();
	$pluriel = au_pluriel( $comptes );

	foreach ( avertissements() as $type => $paire ) {
		if ( ! isset( $bulk_messages[ $type ] ) || ! is_array( $bulk_messages[ $type ] ) ) {
			continue;
		}

		if ( ! isset( $bulk_messages[ $type ]['untrashed'] ) || ! is_string( $bulk_messages[ $type ]['untrashed'] ) ) {
			continue;
		}

		$existante = $bulk_messages[ $type ]['untrashed'];

		if ( '' === trim( $existante ) ) {
			continue;
		}

		$bulk_messages[ $type ]['untrashed'] = $existante . ' ' . ( $pluriel ? $paire['pluriel'] : $paire['singulier'] );
	}

	return $bulk_messages;
}

/**
 * Les six formes de l'avertissement, recopiées telles qu'elles s'affichent.
 *
 * La formulation ne s'invente pas : elle est reprise de « docs/guide/chien-ajouter-un-chien.md »,
 * « Une fiche rétablie revient en brouillon, c'est-à-dire hors du site », et accordée aux trois
 * types. Les six formes sont écrites en toutes lettres et surtout PAS composées d'un radical et
 * d'une terminaison : une règle d'accord fabriquée écrirait tôt ou tard une phrase fausse. L'accord
 * se recopie, il ne se calcule pas. Que deux formes coïncident — une portée et une fiche sont
 * toutes deux du féminin — ne les autorise pas à se partager une entrée : le jour où l'une change,
 * l'autre ne doit pas suivre en silence.
 *
 * La phrase s'enchaîne derrière celle du type, qui la précède toujours : elle commence donc par un
 * pronom et jamais par le nom du contenu.
 *
 * @return array<string, array<string, string>> Type de contenu, puis nombre.
 */
function avertissements(): array {
	return array(
		'mtb_portee'   => array(
			'singulier' => 'Elle revient en brouillon, c’est-à-dire hors du site : ouvrez-la et cliquez sur « Publier » pour qu’elle réapparaisse.',
			'pluriel'   => 'Elles reviennent en brouillon, c’est-à-dire hors du site : ouvrez-les et cliquez sur « Publier » pour qu’elles réapparaissent.',
		),
		'mtb_chien'    => array(
			'singulier' => 'Elle revient en brouillon, c’est-à-dire hors du site : ouvrez-la et cliquez sur « Publier » pour qu’elle réapparaisse.',
			'pluriel'   => 'Elles reviennent en brouillon, c’est-à-dire hors du site : ouvrez-les et cliquez sur « Publier » pour qu’elles réapparaissent.',
		),
		'mtb_resultat' => array(
			'singulier' => 'Il revient en brouillon, c’est-à-dire hors du site : ouvrez-le et cliquez sur « Publier » pour qu’il réapparaisse.',
			'pluriel'   => 'Ils reviennent en brouillon, c’est-à-dire hors du site : ouvrez-les et cliquez sur « Publier » pour qu’ils réapparaissent.',
		),
	);
}

/**
 * Dit si l'avertissement doit s'accorder au pluriel, selon le nombre de contenus rétablis.
 *
 * La règle est exactement celle des trois « messages_par_lot() » qui composent la phrase de tête :
 * le singulier au seul compte de un, le pluriel partout ailleurs. Un compte de zéro donne donc le
 * pluriel — comme « 0 portées sorties de la corbeille. » —, ce qui n'est jamais imprimé mais garde
 * les deux moitiés de la phrase accordées entre elles. L'accord au nombre se fait ici, à la main :
 * l'extension n'emploie aucune fonction de traduction.
 *
 * @param array<string, mixed> $comptes Nombre de contenus par clé de message.
 *
 * @return bool Vrai quand la phrase se met au pluriel.
 */
function au_pluriel( array $comptes ): bool {
	$compte = isset( $comptes['untrashed'] ) && is_scalar( $comptes['untrashed'] ) ? (int) $comptes['untrashed'] : 0;

	return 1 !== $compte;
}
