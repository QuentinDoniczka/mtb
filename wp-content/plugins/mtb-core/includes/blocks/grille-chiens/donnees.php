<?php
/**
 * Données du composant « Grille de chiens » : liste fermée des statuts, lecture, textes d'éditeur.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Blocks\GrilleChiens;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rend disponible le vocabulaire du chien, et dit s'il l'est.
 *
 * Le module ne possède ni le type de contenu ni son vocabulaire : il emprunte les libellés de
 * statut au module qui les détient, plutôt que d'en garder une copie qui finirait par diverger.
 *
 * L'inclusion est précédée d'une sonde : un require_once d'un fichier absent est une erreur de
 * compilation que le try/catch du chargeur n'attrape pas — le site entier tomberait pour un module
 * manquant. Faute de vocabulaire, le sélecteur ne propose que son premier choix, tout attribut
 * retombe sur « tous », et aucun statut n'est inventé.
 */
function vocabulaire_disponible(): bool {
	static $disponible = null;

	if ( null !== $disponible ) {
		return $disponible;
	}

	$chemin = MTB_CORE_DIR . 'includes/content/chien/choix.php';

	if ( is_readable( $chemin ) ) {
		require_once $chemin;
	}

	$disponible = function_exists( 'MTB\\Core\\Content\\Chien\\statuts' )
		&& function_exists( 'MTB\\Core\\Content\\Chien\\libelle_statut' )
		&& function_exists( 'MTB\\Core\\Content\\Chien\\libelle_statut_pluriel' );

	return $disponible;
}

/**
 * Les statuts connus, dans l'ordre gelé des groupes, avec leur titre au pluriel.
 *
 * C'est la seule autorité du module au rendu : la liste écrite dans block.json ne sert qu'à
 * l'éditeur, et une divergence entre les deux ne peut produire qu'un repli en mode groupé.
 *
 * @return array<string, string> Clé stockée => titre de groupe au pluriel.
 */
function statuts_connus(): array {
	if ( ! vocabulaire_disponible() ) {
		return array();
	}

	$libelles = array();

	foreach ( array_keys( \MTB\Core\Content\Chien\statuts() ) as $cle ) {
		$cle              = (string) $cle;
		$libelles[ $cle ] = \MTB\Core\Content\Chien\libelle_statut_pluriel( $cle );
	}

	return $libelles;
}

/**
 * Les choix du réglage « Statut à afficher », dans l'ordre où ils s'affichent.
 *
 * Le premier choix est le défaut, et il est complet par construction : le jour où un chien reçoit
 * un statut, il apparaît sans qu'on ait à insérer un composant de plus.
 *
 * @return array<int, array<string, string>> Liste de couples valeur / libellé.
 */
function choix_du_reglage(): array {
	$choix = array(
		array(
			'value' => 'tous',
			'label' => 'Tous les statuts, groupés',
		),
	);

	foreach ( statuts_connus() as $cle => $libelle ) {
		$choix[] = array(
			'value' => $cle,
			'label' => $libelle,
		);
	}

	return $choix;
}

/**
 * Le statut demandé par l'instance, ramené à la liste fermée.
 *
 * Toute valeur inconnue retombe sur le mode groupé : jamais un statut inventé, jamais une grille
 * vide sans raison.
 *
 * @param array<string, mixed> $attributs Attributs de l'instance.
 */
function statut_demande( array $attributs ): string {
	$demande = isset( $attributs['statut'] ) && is_string( $attributs['statut'] ) ? $attributs['statut'] : 'tous';
	$connus  = statuts_connus();

	return isset( $connus[ $demande ] ) ? $demande : 'tous';
}

/**
 * Vrai si la fonction de lecture des chiens est présente.
 *
 * Son absence n'est pas une grille vide : c'est une grille qui ne rend rien du tout, sans erreur ni
 * avertissement. Une phrase d'état vide mentirait, le problème n'étant pas l'absence de statut.
 */
function lecture_disponible(): bool {
	return function_exists( 'mtb_get_chiens_par_statut' );
}

/**
 * Tous les groupes rendus par la fonction de lecture, avant le moindre filtrage.
 *
 * Deux besoins s'en servent, et un seul appel les sert : les groupes à rendre, et la question « des
 * fiches publiées portent-elles un statut ? » que la phrase d'état vide doit trancher. Aucun chien
 * n'est compté et aucune requête n'est refaite : le module ne possède ni le type de contenu ni ses
 * requêtes.
 *
 * La mémorisation est une statique de fonction, jamais un transient ni le cache d'objets : sur une
 * installation dotée d'un cache persistant, la grille resterait périmée après la modification d'une
 * fiche. Une statique ne franchit pas la limite de la requête.
 *
 * @return array<int, mixed> Liste de groupes ; tableau vide si aucune fiche publiée n'a de statut,
 *                           ou si la fonction de lecture est absente.
 */
function tous_les_groupes(): array {
	static $memo = null;

	if ( ! lecture_disponible() ) {
		return array();
	}

	if ( null === $memo ) {
		$lus  = mtb_get_chiens_par_statut();
		$memo = is_array( $lus ) ? $lus : array();
	}

	return $memo;
}

/**
 * Les groupes à rendre, tels que la fonction de lecture les a construits.
 *
 * Le filtrage porte sur les groupes déjà rendus : le module n'interroge jamais la base lui-même, ni
 * ne redécide de l'ordre, ni de ce qu'est un groupe vide.
 *
 * @param string $statut « tous », ou une clé de statut.
 *
 * @return array<int, mixed> Liste de groupes ; tableau vide si aucun ne correspond.
 */
function groupes( string $statut ): array {
	$tous = tous_les_groupes();

	if ( 'tous' === $statut ) {
		return $tous;
	}

	$retenus = array();

	foreach ( $tous as $groupe ) {
		if ( is_array( $groupe ) && isset( $groupe['statut'] ) && $statut === $groupe['statut'] ) {
			$retenus[] = $groupe;
		}
	}

	return $retenus;
}

/**
 * Vrai pendant l'aperçu d'un bloc dans l'éditeur, et seulement là.
 *
 * L'aperçu d'un bloc à rendu serveur passe par la route REST du moteur de rendu, pendant laquelle
 * REST_REQUEST vaut true ; la capacité garantit qu'un visiteur anonyme ne l'atteint jamais.
 * is_admin() ne conviendrait pas : il vaut false pendant une requête REST.
 *
 * Couplage à connaître avant de toucher à editeur.js : c'est l'aperçu qui produit cette requête.
 * Retirer l'aperçu ferait disparaître l'état vide de l'éditeur, en silence et sans erreur.
 */
function contexte_editeur(): bool {
	return defined( 'REST_REQUEST' ) && REST_REQUEST && current_user_can( 'edit_posts' );
}

/**
 * Valeur de l'attribut « sizes » des photos de la grille.
 *
 * Le défaut est dérivé de la piste réelle de la grille livrée. Sans lui, le cœur écrirait
 * « (max-width: 768px) 100vw, 768px » et la page téléchargerait des photos de 768 px pour des
 * vignettes trois fois plus petites.
 */
function tailles_image(): string {
	$defaut = '(min-width: 37.5em) 14rem, 45vw';

	/**
	 * Filtre la place que chaque photo de la grille occupe dans la page.
	 *
	 * C'est une valeur de mise en page : elle appartient au thème, et c'est le seul point
	 * d'extension du composant.
	 *
	 * @param string $tailles Valeur de l'attribut « sizes ».
	 */
	$tailles = apply_filters( 'mtb_grille_chiens_tailles_image', $defaut );

	return is_string( $tailles ) && '' !== $tailles ? $tailles : $defaut;
}

/**
 * Textes et choix transmis à l'éditeur.
 *
 * Tout ce qui s'affiche dans l'éditeur passe par ici : editeur.js ne contient aucun texte, donc
 * aucun libellé de statut ne peut y diverger de celui du site.
 *
 * @return array<string, mixed>
 */
function donnees_editeur(): array {
	return array(
		'nom'     => 'mtb/grille-chiens',
		'reglage' => array(
			'etiquette' => 'Statut à afficher',
			'aide'      => "« Tous les statuts, groupés » affiche toutes les fiches de chien publiées qui ont un statut, chaque groupe sous son titre. Un statut choisi n'affiche que les chiens de ce statut.",
			'choix'     => choix_du_reglage(),
			'defaut'    => 'tous',
		),
	);
}
