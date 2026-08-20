<?php
/**
 * Composant « Lien de recours » — fonctions d'aide du rendu public.
 *
 * Ce fichier est inclus UNE SEULE FOIS, par « bootstrap.php ». Il est le seul du module à déclarer
 * une fonction : « render.php » est inclus par le cœur avec un « require » nu, donc une fois par
 * instance du bloc — et les gabarits en portent trois chacun.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Blocks\LienDeRecours;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * POURQUOI CE BLOC EXISTE, ALORS QUE LE CŒUR SEMBLE COUVRIR LE BESOIN.
 *
 * « 404.html » et « search.html » sont des gabarits de thème de blocs : des fichiers « .html » qui
 * n'exécutent aucun PHP. Ils ne peuvent donc ni appeler home_url(), ni demander l'adresse d'une
 * archive, ni vérifier qu'une page existe — leurs liens de recours étaient des « href » écrits en
 * dur, faux dès que le site n'est pas à la racine d'un domaine (dette T28 du contrat #16).
 *
 * Le cœur ne comble pas ce trou : « core/home-link » est son SEUL bloc qui calcule une adresse de
 * site au rendu, et il ne couvre que l'accueil ; « core/navigation-link » — le seul qui connaisse
 * « post-type-archive » — imprime l'attribut « url » ENREGISTRÉ, calculé dans l'éditeur et gelé dans
 * le balisage.
 *
 * Ce module ne lit aucune donnée d'élevage : home_url(), get_post_type_archive_link() et
 * get_page_by_path() sont des API de navigation du cœur (décision 41).
 */

/**
 * Résout une cible en adresse et en libellé, ou constate qu'il n'y a pas de destination.
 *
 * L'absence de destination n'est pas une erreur : c'est un cas normal, et le seul rendu correct est
 * alors le vide (décision 26 — un composant sans contenu ne s'affiche pas au visiteur). Un lien de
 * recours mort sur une page d'erreur serait une seconde impasse offerte à un visiteur déjà perdu.
 *
 * LIMITE ASSUMÉE SUR « meute » : le segment « la-meute » est une CONVENTION, pas une garantie. Rien
 * n'oblige l'éleveuse à nommer ainsi sa page ; si elle l'appelle « Nos chiens », son adresse change
 * et le lien ne s'affiche pas. Silencieux, mais honnête : un lien absent vaut mieux qu'un lien mort.
 * La vraie réponse serait un réglage d'administration désignant la page ; elle est hors de cette
 * issue et devra être demandée.
 *
 * @param string $cible Cible demandée : « accueil », « portees » ou « meute ».
 *
 * @return array{url: string, libelle: string}|null L'adresse et le libellé, ou null si la
 *                                                  destination n'existe pas.
 */
function destination( string $cible ): ?array {
	if ( 'accueil' === $cible ) {
		return array(
			'url'     => home_url( '/' ),
			'libelle' => 'Accueil',
		);
	}

	if ( 'portees' === $cible ) {
		// Rend « false » si le type de contenu n'existe pas ou n'a pas d'archive — par exemple si
		// l'extension est désactivée alors que le gabarit du thème, lui, reste en place.
		$adresse = get_post_type_archive_link( 'mtb_portee' );

		return is_string( $adresse ) && '' !== $adresse
			? array(
				'url'     => $adresse,
				'libelle' => 'Les portées',
			)
			: null;
	}

	if ( 'meute' === $cible ) {
		$page = get_page_by_path( 'la-meute' );

		/*
		 * get_page_by_path() ne filtre PAS sur l'état : elle rend aussi bien un brouillon qu'une
		 * page à la corbeille. Les deux vérifications sont donc à faire ici, et pas seulement
		 * l'existence :
		 *
		 * - hors « publish », le visiteur recevrait une page introuvable — l'impasse même que ce
		 *   lien est censé éviter ;
		 * - protégée par mot de passe, il recevrait un mur de mot de passe, qui n'est pas un
		 *   recours (et le contenu protégé ne doit fuiter par aucun composant).
		 */
		if ( ! $page instanceof \WP_Post || 'publish' !== $page->post_status || '' !== $page->post_password ) {
			return null;
		}

		$adresse = get_permalink( $page );

		return is_string( $adresse ) && '' !== $adresse
			? array(
				'url'     => $adresse,
				'libelle' => 'La meute',
			)
			: null;
	}

	/*
	 * Cible hors de l'énumération. Le cœur la ramène normalement au défaut « accueil » en validant
	 * les attributs contre le schéma de block.json, mais do_blocks() peut aussi tourner sur du
	 * balisage forgé à la main : rien plutôt qu'un lien deviné.
	 */
	return null;
}
