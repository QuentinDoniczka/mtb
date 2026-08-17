<?php
/**
 * Enregistrement de la fiche Chien et avis rendus à l'éleveuse.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Fields\Chien;

use function MTB\Core\Content\Chien\assainir_cadrage;
use function MTB\Core\Content\Chien\assainir_date;
use function MTB\Core\Content\Chien\assainir_identifiant;
use function MTB\Core\Content\Chien\assainir_liste_identifiants;
use function MTB\Core\Content\Chien\assainir_oui_non;
use function MTB\Core\Content\Chien\assainir_sexe;
use function MTB\Core\Content\Chien\assainir_statut;
use function MTB\Core\Content\Chien\assainir_texte_multiligne;
use function MTB\Core\Content\Chien\assainir_texte_recopie;
use function MTB\Core\Content\Chien\assainir_url;
use function MTB\Core\Content\Chien\assainir_variete;
use function MTB\Core\Content\Chien\cadrage_par_defaut;
use function MTB\Core\Content\Chien\champs_sante;
use function MTB\Core\Content\Chien\champs_titres;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Valeur brute d'un champ du formulaire.
 *
 * Un champ absent du formulaire vaut le vide, jamais « ne pas toucher » : une section vidée par
 * l'éleveuse doit s'enregistrer vide.
 *
 * Le wp_unslash() ci-dessous sert à inspecter et assainir la valeur telle qu'elle a été tapée. Il
 * a une contrepartie obligatoire : toute écriture passe ensuite par ecrire_meta(), qui la
 * ré-échappe. Ces deux fonctions vont par paire, ne jamais en employer une sans l'autre.
 *
 * @param string $cle Clé du champ.
 */
function valeur_postee( string $cle ): string {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- le nonce est vérifié en tête de enregistrer_champs(), seul appelant de cette fonction.
	if ( ! isset( $_POST[ $cle ] ) || ! is_scalar( $_POST[ $cle ] ) ) {
		return '';
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- assainissement fait par l'appelant, champ par champ, avec la fonction propre à ce champ : sanitize_text_field() y viderait une valeur commençant par « < ».
	return (string) wp_unslash( $_POST[ $cle ] );
}

/**
 * Écrit un champ de la fiche. Point de passage unique de toutes les écritures de ce module.
 *
 * Le wp_slash() n'est pas une redondance et il ne doit pas être retiré : update_metadata() applique
 * lui-même un wp_unslash() à la valeur reçue. Une valeur déjà dé-échappée par valeur_postee()
 * serait donc dé-échappée une seconde fois, et un antislash légitime disparaîtrait à chaque
 * enregistrement — « Rex\Test » deviendrait « RexTest ». C'est une perte de donnée recopiée, D11,
 * silencieuse et cumulative.
 *
 * Une seule fonction plutôt qu'un wp_slash() sur chaque appel : on ne peut plus en oublier un.
 *
 * @param int        $post_id Identifiant de la fiche.
 * @param string     $cle     Clé du champ.
 * @param int|string $valeur  Valeur déjà assainie, dé-échappée.
 */
function ecrire_meta( int $post_id, string $cle, $valeur ): void {
	update_post_meta( $post_id, $cle, wp_slash( $valeur ) );
}

/**
 * Retient un avis quand une valeur saisie a été refusée.
 *
 * @param string $brut   Valeur reçue.
 * @param string $propre Valeur retenue après assainissement.
 * @param string $code   Code d'avis à émettre.
 */
function signaler_refus( string $brut, string $propre, string $code ): void {
	if ( '' !== $brut && '' === $propre ) {
		Avis::ajouter( $code );
	}
}

/**
 * Enregistre la fiche Chien.
 *
 * @param int      $post_id      Identifiant de la fiche.
 * @param \WP_Post $post         Fiche enregistrée.
 * @param bool     $mise_a_jour  Vrai s'il s'agit d'une mise à jour ; non utilisé, la routine
 *                               traite les deux cas de la même façon.
 */
function enregistrer_champs( int $post_id, \WP_Post $post, bool $mise_a_jour ): void {
	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}

	$nonce = isset( $_POST['mtb_chien_nonce'] ) && is_scalar( $_POST['mtb_chien_nonce'] )
		? sanitize_text_field( wp_unslash( $_POST['mtb_chien_nonce'] ) )
		: '';

	/*
	 * Sans nonce valide, la routine ne touche à rien : c'est ce qui rend inoffensives la
	 * modification rapide depuis la liste, une écriture par l'API et toute requête forgée.
	 */
	if ( '' === $nonce || false === wp_verify_nonce( $nonce, 'mtb_chien_fiche' ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	enregistrer_textes( $post_id );
	enregistrer_listes( $post_id );
	enregistrer_dates( $post_id );
	enregistrer_parent( $post_id, 'pere' );
	enregistrer_parent( $post_id, 'mere' );
	enregistrer_photos( $post_id );
}

/**
 * Champs recopiés : la valeur est conservée telle quelle, seuls les caractères de contrôle
 * partent. Aucune valeur de santé, de cotation ou de numéro LOF n'est vérifiée ni normalisée.
 *
 * @param int $post_id Identifiant de la fiche.
 */
function enregistrer_textes( int $post_id ): void {
	$cles = array(
		'_mtb_nom_complet',
		'_mtb_taille',
		'_mtb_couleur',
		'_mtb_masque',
		'_mtb_genetique_robe',
	);

	foreach ( champs_sante() as $champ ) {
		if ( ! isset( $champ['liste'] ) ) {
			$cles[] = $champ['cle'];
		}
	}

	foreach ( champs_titres() as $champ ) {
		$cles[] = $champ['cle'];
	}

	foreach ( $cles as $cle ) {
		ecrire_meta( $post_id, $cle, assainir_texte_recopie( valeur_postee( $cle ) ) );
	}

	ecrire_meta( $post_id, '_mtb_autres_tests', assainir_texte_multiligne( valeur_postee( '_mtb_autres_tests' ) ) );
	ecrire_meta( $post_id, '_mtb_autres_titres', assainir_texte_multiligne( valeur_postee( '_mtb_autres_titres' ) ) );
}

/**
 * Champs à liste fermée : une valeur hors liste est refusée et signalée.
 *
 * @param int $post_id Identifiant de la fiche.
 */
function enregistrer_listes( int $post_id ): void {
	$sexe_brut = valeur_postee( '_mtb_sexe' );
	$sexe      = assainir_sexe( $sexe_brut );
	signaler_refus( $sexe_brut, $sexe, 'sexe_refuse' );
	ecrire_meta( $post_id, '_mtb_sexe', $sexe );

	$variete_brut = valeur_postee( '_mtb_variete' );
	$variete      = assainir_variete( $variete_brut );
	signaler_refus( $variete_brut, $variete, 'variete_refusee' );
	ecrire_meta( $post_id, '_mtb_variete', $variete );

	$statut_brut = valeur_postee( '_mtb_statut' );
	$statut      = assainir_statut( $statut_brut );
	signaler_refus( $statut_brut, $statut, 'statut_refuse' );
	ecrire_meta( $post_id, '_mtb_statut', $statut );

	// Sans statut, la fiche n'entre dans aucun groupe de « La meute » : elle est prévenue tout de suite.
	if ( '' === $statut ) {
		Avis::ajouter( 'statut_vide' );
	}

	$adn_brut = valeur_postee( '_mtb_adn_identifie' );
	$adn      = assainir_oui_non( $adn_brut );
	signaler_refus( $adn_brut, $adn, 'adn_refuse' );
	ecrire_meta( $post_id, '_mtb_adn_identifie', $adn );
}

/**
 * Dates : la seule cohérence vérifiée est le décès postérieur à la naissance, et seulement quand
 * les deux dates sont remplies. Une incohérence n'efface jamais la saisie.
 *
 * @param int $post_id Identifiant de la fiche.
 */
function enregistrer_dates( int $post_id ): void {
	$naissance = date_retenue( $post_id, '_mtb_date_naissance', 'naissance_refusee', 'mtb_chien_naissance' );
	$deces     = date_retenue( $post_id, '_mtb_date_deces', 'deces_refuse', 'mtb_chien_deces' );

	if ( '' !== $naissance && '' !== $deces && strcmp( $deces, $naissance ) < 0 ) {
		Avis::ajouter( 'deces_avant_naissance' );
	}

	ecrire_meta( $post_id, '_mtb_date_naissance', $naissance );
	ecrire_meta( $post_id, '_mtb_date_deces', $deces );
}

/**
 * Date à enregistrer : la saisie si elle est comprise, sinon celle déjà enregistrée.
 *
 * Effacer une date juste parce que la nouvelle saisie est incomprise ferait perdre une donnée que
 * personne n'a demandé à supprimer. Un champ vidé volontairement, lui, est bien enregistré vide :
 * c'est une intention, pas un accident.
 *
 * @param int    $post_id   Identifiant de la fiche.
 * @param string $cle       Clé du champ.
 * @param string $code      Code d'avis à émettre en cas de refus.
 * @param string $parametre Paramètre d'adresse qui transporte la saisie refusée.
 */
function date_retenue( int $post_id, string $cle, string $code, string $parametre ): string {
	$saisie = valeur_postee( $cle );
	$date   = assainir_date( $saisie );

	if ( '' === $saisie || '' !== $date ) {
		return $date;
	}

	Avis::ajouter( $code );
	Avis::preciser( $parametre, $saisie );

	return valeur_enregistree( $post_id, $cle );
}

/**
 * Filiation : une fiche, ou un nom libre, ou rien. L'auto-référence est refusée à la sauvegarde
 * comme elle l'est déjà à la source, où la fiche courante n'est pas proposée dans la liste.
 *
 * @param int    $post_id Identifiant de la fiche.
 * @param string $role    « pere » ou « mere ».
 */
function enregistrer_parent( int $post_id, string $role ): void {
	$prefixe = '_mtb_' . $role . '_';
	$fiche   = assainir_identifiant( valeur_postee( $prefixe . 'fiche' ) );

	if ( 0 !== $fiche && $fiche === $post_id ) {
		$fiche = 0;
		Avis::ajouter( 'pere' === $role ? 'pere_soi_meme' : 'mere_soi_meme' );
	}

	if ( 0 !== $fiche && 'mtb_chien' !== get_post_type( $fiche ) ) {
		$fiche = 0;
		Avis::ajouter( 'pere' === $role ? 'pere_introuvable' : 'mere_introuvable' );
	}

	ecrire_meta( $post_id, $prefixe . 'fiche', $fiche );
	ecrire_meta( $post_id, $prefixe . 'nom', assainir_texte_recopie( valeur_postee( $prefixe . 'nom' ) ) );
	ecrire_meta( $post_id, $prefixe . 'elevage', assainir_texte_recopie( valeur_postee( $prefixe . 'elevage' ) ) );
}

/**
 * Cadrage, galerie et lien pedigree.
 *
 * @param int $post_id Identifiant de la fiche.
 */
function enregistrer_photos( int $post_id ): void {
	$cadrage_brut = valeur_postee( '_mtb_cadrage' );
	$cadrage      = assainir_cadrage( $cadrage_brut );

	if ( '' !== $cadrage_brut && '' === $cadrage ) {
		Avis::ajouter( 'cadrage_refuse' );
	}

	if ( '' === $cadrage ) {
		$cadrage = cadrage_par_defaut();
	}

	ecrire_meta( $post_id, '_mtb_cadrage', $cadrage );
	ecrire_meta( $post_id, '_mtb_galerie', assainir_liste_identifiants( valeur_postee( '_mtb_galerie' ) ) );

	$pedigree_brut = valeur_postee( '_mtb_pedigree' );
	$pedigree      = assainir_url( $pedigree_brut );
	signaler_refus( $pedigree_brut, $pedigree, 'pedigree_refuse' );
	ecrire_meta( $post_id, '_mtb_pedigree', $pedigree );
}

/**
 * Transporte les avis collectés jusqu'à la page qui suit l'enregistrement.
 *
 * @param string $emplacement Adresse de redirection.
 * @param int    $post_id     Identifiant de la fiche.
 */
function ajouter_avis_a_la_redirection( string $emplacement, int $post_id ): string {
	if ( 'mtb_chien' !== get_post_type( $post_id ) ) {
		return $emplacement;
	}

	$codes = Avis::codes();

	if ( array() === $codes ) {
		return $emplacement;
	}

	$emplacement = add_query_arg( 'mtb_chien_avis', implode( ',', $codes ), $emplacement );

	foreach ( Avis::precisions() as $parametre => $saisie ) {
		// La saisie refusée voyage telle quelle ; elle est assainie à la relecture et échappée au rendu.
		$emplacement = add_query_arg( $parametre, $saisie, $emplacement );
	}

	return $emplacement;
}

/**
 * Saisie refusée telle qu'elle revient de l'adresse, prête à être citée.
 *
 * @param string $parametre Nom du paramètre d'adresse.
 */
function saisie_citee( string $parametre ): string {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- valeur d'affichage seulement, aucune écriture, échappée au rendu.
	if ( ! isset( $_GET[ $parametre ] ) || ! is_scalar( $_GET[ $parametre ] ) ) {
		return '';
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- idem.
	$saisie = sanitize_text_field( wp_unslash( $_GET[ $parametre ] ) );

	// Une saisie interminable ne doit pas déformer l'encart : on cite le début, cela suffit à la reconnaître.
	if ( mb_strlen( $saisie ) > 40 ) {
		$saisie = mb_substr( $saisie, 0, 40 ) . '…';
	}

	return $saisie;
}

/**
 * Affiche les avis de la fiche Chien.
 */
function afficher_avis(): void {
	$ecran = get_current_screen();

	if ( null === $ecran || 'mtb_chien' !== $ecran->post_type ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- simple drapeau d'affichage, aucune écriture, et les codes reçus sont filtrés sur une liste fermée.
	if ( ! isset( $_GET['mtb_chien_avis'] ) || ! is_scalar( $_GET['mtb_chien_avis'] ) ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- idem.
	$recus    = explode( ',', sanitize_text_field( wp_unslash( $_GET['mtb_chien_avis'] ) ) );
	$messages = Avis::messages();

	foreach ( $recus as $code ) {
		if ( ! isset( $messages[ $code ] ) ) {
			continue;
		}

		$texte = $messages[ $code ]['texte'];

		if ( isset( $messages[ $code ]['modele'], $messages[ $code ]['parametre'] ) ) {
			$saisie = saisie_citee( $messages[ $code ]['parametre'] );

			if ( '' !== $saisie ) {
				$texte = sprintf( $messages[ $code ]['modele'], $saisie );
			}
		}

		printf(
			'<div class="notice notice-%1$s"><p>%2$s</p></div>',
			esc_attr( $messages[ $code ]['type'] ),
			esc_html( $texte )
		);
	}
}
