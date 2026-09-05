<?php
/**
 * Résolution d'une identité de la carte en permalien servi aujourd'hui.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\Redirections301;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * POURQUOI UNE IDENTITÉ ET NON UNE URL FIGÉE. Le jour où le schéma de permalien change, où
 * l'éleveuse renomme une fiche, où l'issue #27 rejoue son empreinte, une table d'URL figées
 * deviendrait fausse EN SILENCE. Une identité, elle, se re-résout à chaque requête : elle rend le
 * bon permalien, ou elle ne rend rien — et « ne rien rendre » est un état nommé, que la commande
 * de vérification signale en échec.
 *
 * AUCUNE DE CES FONCTIONS N'EST APPELÉE À L'INCLUSION. « get_permalink() »,
 * « get_page_by_path() » et les fonctions de lecture « mtb_* » supposent WordPress chargé ; le
 * seul appelant légitime est un rappel de hook, bien après « plugins_loaded ».
 */

/**
 * Résout une identité de carte en permalien.
 *
 * @param array<int, mixed> $identite array( type, référence ) ; type parmi « portee », « chien », « page ».
 *
 * @return array<string, mixed> array{ url: string, etat: string } ; « url » vide quand rien n'est
 *                              résolu. « etat » vaut « ok », « cible_par_repli » ou
 *                              « cible_non_resolue ».
 */
function resoudre_cible( array $identite ): array {
	$absente = array(
		'url'  => '',
		'etat' => 'cible_non_resolue',
	);

	$type      = isset( $identite[0] ) && is_string( $identite[0] ) ? $identite[0] : '';
	$reference = isset( $identite[1] ) && is_string( $identite[1] ) ? trim( $identite[1] ) : '';

	if ( '' === $type || '' === $reference ) {
		return $absente;
	}

	if ( 'portee' === $type ) {
		return resoudre_portee( $reference );
	}

	if ( 'chien' === $type ) {
		return resoudre_contenu_par_slug( $reference, 'mtb_chien' );
	}

	if ( 'page' === $type ) {
		return resoudre_contenu_par_slug( $reference, 'page' );
	}

	return $absente;
}

/**
 * Résout une portée par son identifiant saisi, avec un repli déclaré.
 *
 * LE REPLI EST DÉCLARÉ, PAS DISCRET, ET SON MOTIF EST GELÉ. « mtb_get_portee_par_identifiant() »
 * passe par « Hydratation::contenus() », qui impose « has_password => false »
 * (« includes/query/portee/hydratation.php:114 »). C'est JUSTE pour une lecture publique — une
 * portée protégée n'a rien à faire dans un index — mais FAUX pour une 301 : si l'éleveuse protège
 * une portée par mot de passe, son ancienne adresse cesserait de rediriger SANS UN MOT. Le repli
 * conserve la 301 vers la page de mot de passe, qui répond 200, et la commande nomme chaque cible
 * qui a eu besoin de lui.
 *
 * @param string $identifiant Identifiant de portée, transcrit du site source.
 *
 * @return array<string, mixed> array{ url, etat }.
 */
function resoudre_portee( string $identifiant ): array {
	if ( function_exists( 'mtb_get_portee_par_identifiant' ) ) {
		$portee = mtb_get_portee_par_identifiant( $identifiant );

		if ( is_array( $portee ) && isset( $portee['lien'] ) && is_string( $portee['lien'] ) && '' !== $portee['lien'] ) {
			return array(
				'url'  => $portee['lien'],
				'etat' => 'ok',
			);
		}
	}

	$contenus = get_posts(
		array(
			'post_type'              => 'mtb_portee',
			'post_status'            => 'publish',
			'title'                  => $identifiant,
			'posts_per_page'         => 1,
			'orderby'                => 'ID',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
		)
	);

	$contenu = $contenus[0] ?? null;

	if ( ! $contenu instanceof \WP_Post ) {
		return array(
			'url'  => '',
			'etat' => 'cible_non_resolue',
		);
	}

	$adresse = get_permalink( $contenu );

	if ( ! is_string( $adresse ) || '' === $adresse ) {
		return array(
			'url'  => '',
			'etat' => 'cible_non_resolue',
		);
	}

	return array(
		'url'  => $adresse,
		'etat' => 'cible_par_repli',
	);
}

/**
 * Résout une fiche de chien ou une page libre par son slug.
 *
 * « mtb_get_chien() » n'est pas employée : sa signature prend un identifiant de contenu, pas un
 * slug (« includes/query/chien/bootstrap.php:40 »). Aucune fonction de lecture ne résout un slug
 * de fiche, et en créer une serait hors empreinte — « includes/query/** » est fermé à cette issue.
 * « get_page_by_path() » est une fonction du cœur appelée DEPUIS L'EXTENSION : la frontière du
 * contrat #1 §8 vise le thème, jamais l'extension.
 *
 * @param string $slug            Slug du contenu.
 * @param string $type_de_contenu « mtb_chien » ou « page ».
 *
 * @return array<string, mixed> array{ url, etat }.
 */
function resoudre_contenu_par_slug( string $slug, string $type_de_contenu ): array {
	$absente = array(
		'url'  => '',
		'etat' => 'cible_non_resolue',
	);

	$contenu = get_page_by_path( $slug, OBJECT, $type_de_contenu );

	if ( ! $contenu instanceof \WP_Post ) {
		return $absente;
	}

	// Un brouillon ou une corbeille n'est pas une cible : rediriger vers elle servirait un 404.
	if ( 'publish' !== $contenu->post_status ) {
		return $absente;
	}

	$adresse = get_permalink( $contenu );

	if ( ! is_string( $adresse ) || '' === $adresse ) {
		return $absente;
	}

	return array(
		'url'  => $adresse,
		'etat' => 'ok',
	);
}
