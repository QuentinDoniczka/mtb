<?php
/**
 * Mode « --verifier » : confronte la base aux fichiers de reprise, sans jamais écrire.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\ResultatsPages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Ce fichier N'ÉCRIT RIEN, et c'est sa raison d'être. Il se lance juste après une reprise pour
 * répondre à une seule question : ce que la base contient dit-il la même chose que ce que les
 * fichiers déclarent ?
 *
 * Il signale, il ne corrige pas. Une divergence sur une page peut parfaitement être une correction
 * de l'éleveuse — c'est à elle qu'appartient le dernier mot sur les mots de son site, et un outil
 * qui « remettrait en conformité » effacerait son travail.
 */

/**
 * Vérifie que les résultats et les pages déclarés existent et disent la même chose.
 *
 * @param array<int, mixed>                   $resultats Entrées de « resultats.json ».
 * @param string                              $chemin    Chemin du fichier des résultats.
 * @param array<string, array<string, mixed>> $pages     Fiches de page, indexées par référence.
 * @param array<string, string>               $chemins   Référence => chemin du fichier lu.
 * @param array<string, int>                  $photos    Nom de fichier => identifiant de pièce jointe.
 */
function verifier( array $resultats, string $chemin, array $pages, array $chemins, array $photos ): void {
	verifier_les_resultats( $resultats, $chemin );
	verifier_les_pages( $pages, $chemins, $photos );
}

/**
 * Vérifie que chaque résultat déclaré existe, et que ses champs sont ceux du fichier.
 *
 * @param array<int, mixed> $entrees Entrées du fichier.
 * @param string            $chemin  Chemin du fichier.
 */
function verifier_les_resultats( array $entrees, string $chemin ): void {
	$fichier = nom_de_fichier( $chemin );

	foreach ( $entrees as $rang => $entree ) {
		$rang        = (int) $rang;
		$identifiant = is_array( $entree ) ? identifiant_de_resultat( $entree ) : 'entrée sans identifiant';
		$raisons     = controler_resultat( $entree );

		if ( array() !== $raisons ) {
			rejeter( $fichier, $rang, $identifiant, $raisons, 'resultats' );

			continue;
		}

		$metas   = valeurs_brutes_resultat( $entree );
		$post_id = resultat_existant( $metas );

		if ( 0 === $post_id ) {
			rejeter(
				$fichier,
				$rang,
				$identifiant,
				array( 'aucun résultat de la base ne porte ce tuple d\'identité : il n\'a pas été repris.' ),
				'resultats'
			);

			continue;
		}

		$divergences = divergences_de_metas( $post_id, $metas );

		if ( array() === $divergences ) {
			compter( 'resultats', 'present' );

			continue;
		}

		signaler_divergences( 'resultats', $fichier, $rang, $identifiant, $divergences );
	}
}

/**
 * Vérifie que chaque page déclarée existe, et que son balisage est celui que composerait la reprise.
 *
 * @param array<string, array<string, mixed>> $pages   Fiches de page, indexées par référence.
 * @param array<string, string>               $chemins Référence => chemin du fichier lu.
 * @param array<string, int>                  $photos  Nom de fichier => identifiant de pièce jointe.
 */
function verifier_les_pages( array $pages, array $chemins, array $photos ): void {
	foreach ( $pages as $reference => $page ) {
		$reference = (string) $reference;
		$fichier   = isset( $chemins[ $reference ] ) ? nom_de_fichier( $chemins[ $reference ] ) : $reference . '.json';
		$raisons   = controler_page( $page );

		if ( array() !== $raisons ) {
			rejeter( $fichier, 0, $reference, $raisons, 'pages' );

			continue;
		}

		$post_id = page_existante( texte_de( $page, 'reference' ) );

		if ( 0 === $post_id ) {
			rejeter(
				$fichier,
				0,
				$reference,
				array( 'aucune page de la base ne porte cette référence : elle n\'a pas été reprise.' ),
				'pages'
			);

			continue;
		}

		$composition = isset( $page['composition'] ) && is_array( $page['composition'] ) ? $page['composition'] : array();
		$ratees      = array();
		$attendu     = composer_le_balisage( $composition, $photos, $ratees );

		if ( array() !== $ratees ) {
			rejeter( $fichier, 0, $reference, $ratees, 'pages' );

			continue;
		}

		$divergences = array();
		$contenu     = get_post( $post_id );

		if ( ! $contenu instanceof \WP_Post ) {
			rejeter( $fichier, 0, $reference, array( 'la page n\'a pas pu être relue.' ), 'pages' );

			continue;
		}

		foreach ( array( 'post_title' => 'titre', 'post_status' => 'statut' ) as $champ => $cle ) {
			if ( ! equivalent( texte_de( $page, $cle ), $contenu->{$champ} ) ) {
				$divergences[] = phrase_de_divergence( $champ, texte_de( $page, $cle ), $contenu->{$champ} );
			}
		}

		if ( $attendu !== $contenu->post_content ) {
			$divergences[] = sprintf(
				'le balisage de la page diffère de celui que composerait la reprise (%s en base, %s '
				. 'au fichier). Une divergence n\'est pas forcément une faute : l\'éleveuse a pu '
				. 'corriger la page, et rien n\'est réécrit.',
				accorder( strlen( (string) $contenu->post_content ), array( 'octet', 'octets' ) ),
				accorder( strlen( $attendu ), array( 'octet', 'octets' ) )
			);
		}

		if ( array() === $divergences ) {
			compter( 'pages', 'present' );

			continue;
		}

		signaler_divergences( 'pages', $fichier, 0, $reference, $divergences );
	}
}
