<?php
/**
 * Données du composant « Tableau de résultats » : lecture, choix du réglage, textes d'éditeur.
 *
 * Ce fichier ne produit aucun HTML. Il appelle les fonctions de lecture des résultats de travail,
 * qu'il ne réimplémente jamais — le type qui possède la donnée possède sa lecture.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Blocks\TableauResultats;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Vrai si les fonctions de lecture des résultats de travail sont présentes.
 *
 * Leur absence n'est pas un tableau vide : c'est un composant qui ne rend rien du tout, sans erreur
 * ni avertissement. Le module ne lit jamais « mtb_resultat » lui-même et ne refait aucune requête.
 */
function lecture_disponible(): bool {
	return function_exists( 'mtb_get_resultats_travail_par_discipline' )
		&& function_exists( 'mtb_get_resultats_travail_du_chien' );
}

/**
 * La liste close des disciplines, empruntée à son unique source.
 *
 * Jamais recopiée ici : une copie finirait par ne plus dire la même chose que les écrans de saisie.
 * Absente, le réglage ne propose que son premier choix et aucune discipline n'est inventée.
 *
 * @return array<string, string> Clé stockée vers libellé à imprimer, ou tableau vide.
 */
function disciplines_connues(): array {
	if ( ! function_exists( 'mtb_resultat_disciplines' ) ) {
		return array();
	}

	$lues = mtb_resultat_disciplines();

	return is_array( $lues ) ? $lues : array();
}

/**
 * Le mode de l'instance : « chien-courant » ou « discipline ».
 *
 * Comparaison stricte à une seule valeur : toute autre écriture retombe sur le mode par défaut.
 * Ce réglage n'est jamais écrit par l'éditeur — seul un gabarit le pose, dans son commentaire de
 * bloc.
 *
 * @param array<string, mixed> $attributs Attributs de l'instance.
 */
function source_demandee( array $attributs ): string {
	$demande = isset( $attributs['source'] ) && is_string( $attributs['source'] ) ? $attributs['source'] : '';

	return 'chien-courant' === $demande ? 'chien-courant' : 'discipline';
}

/**
 * La discipline demandée par l'instance, chaîne vide pour « toutes les disciplines ».
 *
 * Assainie, jamais ramenée à une liste blanche : une instance réglée sur une discipline devenue
 * orpheline doit continuer d'afficher ce groupe-là, et surtout pas basculer en silence sur les neuf.
 *
 * @param array<string, mixed> $attributs Attributs de l'instance.
 */
function discipline_demandee( array $attributs ): string {
	$demande = isset( $attributs['discipline'] ) && is_string( $attributs['discipline'] ) ? $attributs['discipline'] : '';

	return sanitize_key( $demande );
}

/**
 * L'identifiant de la fiche chien affichée, zéro si la page n'en affiche aucune.
 *
 * Trois crans, dans cet ordre : le contexte que le cœur fournit au bloc, sinon le contenu interrogé
 * par la requête, puis dans les deux cas un garde-fou de type.
 *
 * Le garde-fou n'est pas de la prudence gratuite : sur une archive de terme, la requête interroge un
 * terme, dont l'identifiant vit dans une autre table et peut numériquement coïncider avec celui
 * d'une fiche chien. Sans parade, une page sans rapport afficherait le palmarès d'un chien, en
 * répondant 200.
 *
 * MESURÉ, ET C'EST POURQUOI LE DEUXIÈME CRAN NE PREND PAS L'IDENTIFIANT SEUL. Le garde-fou de type
 * ne suffit pas à lui seul : sur cette installation, la catégorie n° 1 et l'article n° 1 coexistent
 * déjà, et une catégorie qui porterait le numéro d'une fiche chien passerait le contrôle sans être
 * remarquée — get_post_type() ne dirait alors que le type du contenu qui porte ce numéro, pas celui
 * de l'objet réellement interrogé. On demande donc l'objet interrogé lui-même, et on ne retient son
 * identifiant que s'il s'agit d'un contenu. Sur une fiche, le résultat est identique ; sur un terme,
 * une archive d'auteur ou un type de contenu, il n'y a plus de numéro à confondre.
 *
 * @param int|null $contexte Identifiant reçu du contexte du bloc, ou null quand il n'y en a aucun —
 *                           seule valeur qui autorise le repli sur la requête courante.
 */
function identifiant_du_chien( ?int $contexte ): int {
	$identifiant = null === $contexte ? absint( identifiant_interroge() ) : absint( $contexte );

	if ( 0 === $identifiant ) {
		return 0;
	}

	return 'mtb_chien' === get_post_type( $identifiant ) ? $identifiant : 0;
}

/**
 * L'identifiant du contenu interrogé par la requête, zéro si elle n'en interroge aucun.
 *
 * Un terme, un auteur ou un type de contenu ne rendent jamais d'identifiant ici : leurs numéros
 * vivent dans d'autres tables et n'ont aucun sens comme identifiant de fiche.
 */
function identifiant_interroge(): int {
	$interroge = get_queried_object();

	return $interroge instanceof \WP_Post ? (int) $interroge->ID : 0;
}

/**
 * Les groupes à rendre, tels que la fonction de lecture les a construits.
 *
 * Le module ne trie ni ne regroupe : il passe l'ordre voulu et imprime ce qu'il reçoit, groupes
 * orphelins compris et dans le même ordre.
 *
 * La mémorisation est une statique de fonction, jamais un transient ni le cache d'objets : sur une
 * installation dotée d'un cache persistant, le tableau resterait périmé après la saisie d'un
 * résultat. Une statique ne franchit pas la limite de la requête.
 *
 * @param string $discipline Clé de discipline, ou chaîne vide pour toutes.
 *
 * @return array<int, mixed> Liste de groupes ; tableau vide s'il n'y a rien à afficher.
 */
function groupes( string $discipline ): array {
	static $memo = array();

	if ( ! lecture_disponible() ) {
		return array();
	}

	if ( ! isset( $memo[ $discipline ] ) ) {
		$lus = mtb_get_resultats_travail_par_discipline(
			array(
				'ordre'       => 'annee_desc',
				'disciplines' => '' === $discipline ? array() : array( $discipline ),
			)
		);

		$memo[ $discipline ] = is_array( $lus ) ? $lus : array();
	}

	return $memo[ $discipline ];
}

/**
 * Le palmarès d'une fiche chien : ses colonnes et ses lignes.
 *
 * Aucun argument d'ordre n'est passé : le défaut de la fonction de lecture est le bon, une carrière
 * se lisant dans son sens.
 *
 * @param int $chien_id Identifiant de la fiche, déjà vérifié.
 *
 * @return array<string, mixed> Deux clés, « colonnes » et « lignes », vides quand il n'y a rien.
 */
function palmares( int $chien_id ): array {
	static $memo = array();

	$vide = array(
		'colonnes' => array(),
		'lignes'   => array(),
	);

	if ( 0 === $chien_id || ! lecture_disponible() ) {
		return $vide;
	}

	if ( ! isset( $memo[ $chien_id ] ) ) {
		$lu = mtb_get_resultats_travail_du_chien( $chien_id );

		$memo[ $chien_id ] = is_array( $lu ) ? $lu : $vide;
	}

	return $memo[ $chien_id ];
}

/**
 * Les choix du réglage « Discipline à afficher », dans l'ordre où ils s'affichent.
 *
 * Le premier choix est le défaut, et il est complet par construction : il affiche un tableau par
 * discipline, y compris une discipline sortie de la liste, que le réglage ne pourrait plus proposer.
 * C'est la seule façon d'atteindre un résultat dont la discipline a été renommée.
 *
 * @return array<int, array<string, string>> Liste de couples valeur / libellé.
 */
function choix_du_reglage(): array {
	$choix = array(
		array(
			'value' => '',
			'label' => 'Toutes les disciplines',
		),
	);

	foreach ( disciplines_connues() as $cle => $libelle ) {
		$choix[] = array(
			'value' => (string) $cle,
			'label' => (string) $libelle,
		);
	}

	return $choix;
}

/**
 * Les phrases d'état vide, une par choix possible du réglage.
 *
 * Elles sont composées ici, en PHP, et transmises finies à l'éditeur : le script en choisit une, il
 * n'en compose aucune. C'est la règle que le serveur impose au thème, appliquée à notre propre
 * JavaScript.
 *
 * La discipline est nommée et citée entre guillemets : « cette discipline » obligerait l'éleveuse à
 * rouvrir le panneau latéral pour savoir de quoi on parle, et donnerait la même phrase aux neuf.
 * Le libellé étant cité, il n'y a ni accord ni préposition à composer.
 *
 * @return array<string, string> Clé de discipline — la chaîne vide pour « toutes » — vers phrase.
 */
function phrases_etat_vide(): array {
	$phrases = array(
		'' => "Ce bloc n'affiche rien tant qu'aucun résultat de travail n'est publié.",
	);

	foreach ( disciplines_connues() as $cle => $libelle ) {
		$phrases[ (string) $cle ] = "Ce bloc n'affiche rien tant qu'aucun résultat de travail n'est publié dans la discipline « " . (string) $libelle . ' ».';
	}

	return $phrases;
}

/**
 * La phrase d'état vide du mode « palmarès d'une fiche chien ».
 *
 * Ce cas se produit : une instance posée dans un gabarit et ouverte dans l'éditeur de site n'a
 * aucun contexte de fiche pendant l'aperçu, et afficherait sans elle une phrase fausse.
 */
function phrase_etat_vide_palmares(): string {
	return "Ce bloc n'affiche rien tant que la fiche de chien affichée n'a aucun résultat de travail.";
}

/**
 * Textes et choix transmis à l'éditeur.
 *
 * Tout ce qui s'affiche dans l'éditeur passe par ici : editeur.js ne contient aucun texte, donc
 * aucun libellé de discipline ne peut y diverger de celui du site.
 *
 * @return array<string, mixed>
 */
function donnees_editeur(): array {
	return array(
		'nom'        => 'mtb/tableau-resultats',
		'nomAffiche' => 'Tableau de résultats',
		'reglage'    => array(
			'etiquette' => 'Discipline à afficher',
			'aide'      => "« Toutes les disciplines » affiche un tableau par discipline, chacun sous son titre — y compris une discipline qui ne serait plus dans la liste. Une discipline choisie n'affiche que son tableau.",
			'choix'     => choix_du_reglage(),
			'defaut'    => '',
		),
		'etatVide'   => array(
			'phrases'  => phrases_etat_vide(),
			'palmares' => phrase_etat_vide_palmares(),
		),
	);
}
