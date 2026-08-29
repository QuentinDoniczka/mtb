<?php
/**
 * Table de description des trois listes d'administration.
 *
 * Seul endroit du module qui sait « quoi pour quel type ». Le moteur — portée d'écran, balayage,
 * sentinelle, application du filtre — est rigoureusement identique pour les trois listes ; il ne
 * diffère que par ces quelques lignes. Trois modules imposeraient trois copies du moteur, et le
 * jour où elles divergent une liste escamote du contenu sans un mot.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Admin\Listes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Description des trois listes.
 *
 * « colonnes » : clé interne vers en-tête affiché, dans l'ordre d'insertion entre « title » et
 * « date ». Les clés internes ne sont jamais montrées à l'éleveuse : le cœur en fait une classe
 * CSS et un attribut « data-colname ».
 *
 * « ordre » : « post__in » quand l'ordre se calcule en PHP, « titre » quand l'ordre natif suffit.
 * L'ordre natif ne peut rien omettre, c'est pourquoi on le préfère dès qu'il dit la bonne chose.
 *
 * « filtre » : nom du paramètre d'URL réservé, libellé de sa première option, et nom accessible de
 * la liste déroulante. Ces trois noms de paramètre sont gelés par le contrat de l'issue #28 ;
 * aucun autre module ne doit les employer. Le nom accessible se range ici, avec les autres
 * libellés, et jamais en dur dans le fichier qui imprime la liste : c'est ce fichier-ci, et lui
 * seul, qui sait quel mot va à quelle liste. Il remplace celui de la liste des mois du cœur, que
 * ce module neutralise et qui, elle, était étiquetée — sans lui, un lecteur d'écran annoncerait
 * une liste déroulante sans nom là où il en annonçait une nommée.
 *
 * @return array<string, array<string, mixed>> Nom du type de contenu vers sa description.
 */
function descriptions(): array {
	return array(
		'mtb_portee'   => array(
			'colonnes' => array(
				'mtb_date_naissance' => 'Date de naissance',
				'mtb_disponibilite'  => 'Disponibilité',
			),
			'ordre'    => 'post__in',
			'filtre'   => array(
				'parametre'          => 'mtb_annee',
				'libelle_toutes'     => 'Toutes les années',
				'libelle_accessible' => 'Filtrer par année',
			),
		),
		'mtb_chien'    => array(
			'colonnes' => array(
				'mtb_statut'  => 'Statut',
				'mtb_variete' => 'Variété',
			),
			'ordre'    => 'titre',
			'filtre'   => array(
				'parametre'          => 'mtb_statut',
				'libelle_toutes'     => 'Tous les statuts',
				'libelle_accessible' => 'Filtrer par statut',
			),
		),
		'mtb_resultat' => array(
			'colonnes' => array(
				'mtb_discipline' => 'Discipline',
				'mtb_annee'      => 'Année',
				'mtb_chien'      => 'Chien',
			),
			'ordre'    => 'post__in',
			'filtre'   => array(
				'parametre'          => 'mtb_discipline',
				'libelle_toutes'     => 'Toutes les disciplines',
				'libelle_accessible' => 'Filtrer par discipline',
			),
		),
	);
}

/**
 * Description d'un type, ou null s'il n'est pas l'un des trois.
 *
 * @param string $type Nom du type de contenu.
 *
 * @return array<string, mixed>|null Description, ou null.
 */
function description( string $type ): ?array {
	$descriptions = descriptions();

	return isset( $descriptions[ $type ] ) ? $descriptions[ $type ] : null;
}

/**
 * Libellé unique de l'absence de donnée, lu vivant et jamais recopié.
 *
 * Aucune colonne ne compose sa propre chaîne d'absence. Celles qui doivent la produire elles-mêmes
 * passent par ici ; les autres la reçoivent déjà de la fonction de lecture qu'elles appellent —
 * « libelle_statut() » et « date_en_toutes_lettres() » rendent la leur, mot pour mot la même.
 * Jamais un tiret, jamais « Aucun », jamais une cellule vide (MASTER §9.3 et §10.3).
 *
 * @return string Libellé affiché à la place d'une donnée manquante.
 */
function absence(): string {
	return \MTB\Core\Content\Chien\non_renseigne();
}

/**
 * Liste close des disciplines, lue vivante.
 *
 * Le module de lecture publique définit cette fonction globale sous garde « function_exists » ; on
 * ne require jamais le bootstrap.php d'un autre module, on teste au moment de l'appel. Au moment
 * où nos rappels s'exécutent — rendu d'un écran d'administration — le groupe « query » est chargé
 * depuis longtemps. Le repli sur tableau vide est le patron déjà employé par « fields/resultat ».
 *
 * @return array<string, string> Clé stockée vers libellé, ordre gelé ; vide si le module manque.
 */
function disciplines(): array {
	if ( ! function_exists( 'mtb_resultat_disciplines' ) ) {
		return array();
	}

	return mtb_resultat_disciplines();
}
