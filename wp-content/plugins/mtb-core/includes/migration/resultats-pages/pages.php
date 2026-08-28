<?php
/**
 * Reprise des sept pages libres de l'ancien site.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\ResultatsPages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * IDENTITÉ PAR « post_name », ET CRÉATION SEULEMENT
 *
 * Une page est cherchée par sa référence — son « post_name ». Trouvée : comptée « déjà présente »,
 * et AUCUNE écriture. Absente : créée. Il n'existe aucun chemin de mise à jour dans ce module.
 *
 * Ce n'est pas de la prudence de principe : les sept pages existent déjà en base de développement,
 * et l'éleveuse y corrigera des mots. Son travail n'est jamais écrasé par un outil qu'elle ne voit
 * pas. Conséquence à annoncer : sur une base déjà peuplée, la reprise n'écrira rien du tout, et
 * pour l'observer il faut détruire la base — « docker compose down -v ».
 *
 * PAS DE PARENT, ET C'EST UN REFUS MOTIVÉ
 *
 * Les pages sont créées à plat. Poser un parent déciderait d'une URL, or cette décision appartient
 * à l'issue de référencement et à personne d'autre. Vérifié : aucun gabarit, aucun fil d'Ariane,
 * aucun slug réservé du thème ne dépend d'une de ces sept pages.
 */

/**
 * Reprend les pages libres.
 *
 * @param array<string, array<string, mixed>> $pages   Fiches de page, indexées par référence.
 * @param array<string, string>               $chemins Référence => chemin du fichier lu.
 * @param array<string, int>                  $photos  Nom de fichier => identifiant de pièce jointe.
 * @param bool                                $simuler Vrai pour ne rien écrire.
 */
function importer_pages( array $pages, array $chemins, array $photos, bool $simuler ): void {
	foreach ( $pages as $reference => $page ) {
		$reference = (string) $reference;
		$fichier   = isset( $chemins[ $reference ] ) ? nom_de_fichier( $chemins[ $reference ] ) : $reference . '.json';
		$raisons   = controler_page( $page );

		if ( array() !== $raisons ) {
			rejeter( $fichier, 0, $reference, $raisons, 'pages' );

			continue;
		}

		if ( 0 < page_existante( texte_de( $page, 'reference' ) ) ) {
			compter( 'pages', 'present' );

			continue;
		}

		$composition = isset( $page['composition'] ) && is_array( $page['composition'] ) ? $page['composition'] : array();
		$ratees      = array();
		$contenu     = composer_le_balisage( $composition, $photos, $ratees );

		if ( array() !== $ratees ) {
			rejeter( $fichier, 0, $reference, $ratees, 'pages' );

			continue;
		}

		if ( '' !== $contenu && ! aller_retour_stable( $contenu ) ) {
			rejeter(
				$fichier,
				0,
				$reference,
				array(
					'le balisage composé ne se relit pas à l\'identique : il n\'est pas écrit. Ce '
					. 'contrôle précède l\'écriture précisément pour qu\'une page cassée n\'existe '
					. 'jamais en base.',
				),
				'pages'
			);

			continue;
		}

		/*
		 * Les champs de contenu se déduisent de la table des clés de fiche, jamais d'une seconde
		 * liste écrite ici : une clé de fichier renommée d'un seul côté ne peut pas passer inaperçue.
		 */
		$champs = array();

		foreach ( cles_de_contenu_page() as $cle => $champ ) {
			$champs[ $champ ] = texte_de( $page, $cle );
		}

		$champs['post_content'] = $contenu;

		if ( $simuler ) {
			$robots = fait_de_robots( $page );

			compter( 'pages', 'cree' );
			noter(
				sprintf(
					'Page « %s » : serait créée en %s, %s de balisage%s.',
					$champs['post_name'],
					$champs['post_status'],
					accorder( strlen( $contenu ), array( 'octet', 'octets' ) ),
					array() === $robots ? '' : sprintf( ', avec le fait de robots « %s »', $robots['valeur'] )
				)
			);

			continue;
		}

		$post_id = inserer( 'page', $champs );

		if ( 0 === $post_id ) {
			rejeter( $fichier, 0, $reference, array( 'WordPress a refusé la création de la page.' ), 'pages' );

			continue;
		}

		/*
		 * Le fait de robots est écrit APRÈS la création, comme une méta ordinaire : la clé n'est
		 * déclarée par aucun « content/** », donc aucun sanitize_callback ne la couvrirait à
		 * l'insertion. Elle est assainie sous-clé par sous-clé par le schéma, avec l'assainisseur du
		 * modèle, et le contrôle aval la relit comme les autres.
		 */
		$robots = fait_de_robots( $page );

		if ( array() !== $robots ) {
			ecrire_metas( $post_id, array( CLE_ROBOTS => $robots ) );
			noter(
				sprintf(
					'Page « %s » : fait de robots « %s » recopié de %s. Il est STOCKÉ, il n\'est pas '
					. 'encore rendu — la page reste indexable tant que le référencement ne l\'honore pas.',
					$champs['post_name'],
					$robots['valeur'],
					$robots['source']
				)
			);
		}

		compter( 'pages', 'cree' );
		signaler_divergences(
			'pages',
			$fichier,
			0,
			$reference,
			controler_aval_page( $post_id, $champs, $robots )
		);
	}
}

/**
 * Identifiant de la page portant cette référence, tous statuts confondus.
 *
 * @param string $reference Slug de la page.
 *
 * @return int Identifiant, 0 si aucune page ne la porte.
 */
function page_existante( string $reference ): int {
	if ( '' === $reference ) {
		return 0;
	}

	$page = get_page_by_path( $reference, OBJECT, 'page' );

	return $page instanceof \WP_Post ? (int) $page->ID : 0;
}

/**
 * Lit les fiches de page présentes dans un dossier, triées par nom de fichier.
 *
 * @param string $dossier Dossier des fiches de page.
 *
 * @return array<string, array<string, mixed>> Référence => fiche décodée.
 */
function lire_les_pages( string $dossier ): array {
	if ( ! is_dir( $dossier ) ) {
		annoncer( sprintf( 'Aucun dossier de pages (%s) : aucune page ne sera reprise.', $dossier ) );

		return array();
	}

	$entrees = scandir( $dossier );

	if ( false === $entrees ) {
		return array();
	}

	$pages = array();

	foreach ( $entrees as $entree ) {
		if ( 'json' !== pathinfo( $entree, PATHINFO_EXTENSION ) ) {
			continue;
		}

		$reference = pathinfo( $entree, PATHINFO_FILENAME );

		$pages[ $reference ] = lire_objet( $dossier . '/' . $entree );
	}

	return $pages;
}

/**
 * Chemins des fiches de page lues, pour les messages.
 *
 * @param string   $dossier     Dossier des fiches de page.
 * @param string[] $references  Références lues.
 *
 * @return array<string, string> Référence => chemin.
 */
function chemins_des_pages( string $dossier, array $references ): array {
	$chemins = array();

	foreach ( $references as $reference ) {
		$chemins[ $reference ] = $dossier . '/' . $reference . '.json';
	}

	return $chemins;
}
