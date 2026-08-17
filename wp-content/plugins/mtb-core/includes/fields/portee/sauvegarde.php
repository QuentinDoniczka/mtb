<?php
/**
 * Enregistrement des champs d'une portée.
 *
 * Le catalogue et les assainisseurs appartiennent au module « content/portee ». Ils sont appelés
 * ici au moment de l'enregistrement, jamais à l'inclusion : ce fichier ne dépend donc pas de
 * l'ordre de parcours du chargeur.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Fields\Portee;

use MTB\Core\Content\Portee as Champs;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enregistre les champs d'une portée depuis l'écran de saisie.
 *
 * @param int      $post_id      Identifiant de la portée.
 * @param \WP_Post $post         Portée enregistrée.
 * @param bool     $mise_a_jour  Vrai s'il s'agit d'une modification.
 */
function enregistrer_champs( int $post_id, \WP_Post $post, bool $mise_a_jour ): void {
	unset( $mise_a_jour );

	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}

	/*
	 * Garde-fou : sans ce champ, le formulaire soumis n'est pas l'écran de saisie. L'édition
	 * rapide, l'édition en lot, wp_publish_post() et l'enregistrement automatique postent tous un
	 * formulaire partiel ; les traiter effacerait tous les champs de la portée.
	 */
	if ( ! isset( $_POST['mtb_portee_ecran'] ) ) {
		return;
	}

	$nonce = sanitize_text_field( wp_unslash( $_POST['mtb_portee_ecran'] ) );

	if ( ! wp_verify_nonce( $nonce, 'mtb_portee_ecran' ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce vérifié ci-dessus.
	$brut = wp_unslash( $_POST );

	if ( ! is_array( $brut ) ) {
		return;
	}

	$catalogue = Champs\catalogue();
	$avis      = array();

	enregistrer_date( $post_id, $brut, $catalogue, $avis );

	ecrire( $post_id, '_mtb_disponibilite', $brut['_mtb_disponibilite'] ?? '', $catalogue );

	enregistrer_compteur( $post_id, $brut, $catalogue, $avis, '_mtb_males', 'Nombre de mâles' );
	enregistrer_compteur( $post_id, $brut, $catalogue, $avis, '_mtb_femelles', 'Nombre de femelles' );

	ecrire( $post_id, '_mtb_chiots', chiots_soumis( $brut ), $catalogue );
	ecrire( $post_id, '_mtb_galerie', galerie_soumise( $brut ), $catalogue );

	enregistrer_parents( $post_id, $brut, $catalogue );

	controler( $post_id, $post, $brut, $avis );

	Avis::definir( $post_id, $avis );
}

/**
 * Écrit la date de naissance, ou conserve la précédente et le dit.
 *
 * Un champ vidé est une intention : on écrit le vide. Une saisie non vide qu'on ne sait pas lire
 * n'en est pas une : l'écraser par du vide perdrait une date déjà connue sans rien dire. On garde
 * donc l'ancienne et on cite ce qui a été tapé.
 *
 * @param int   $post_id   Identifiant de la portée.
 * @param array $brut      Données soumises, déjà déséchappées.
 * @param array $catalogue Catalogue des champs.
 * @param array $avis      Avis en construction, complété par référence.
 */
function enregistrer_date( int $post_id, array $brut, array $catalogue, array &$avis ): void {
	$saisie = isset( $brut['_mtb_date_naissance'] ) && is_scalar( $brut['_mtb_date_naissance'] )
		? trim( (string) $brut['_mtb_date_naissance'] )
		: '';

	$lue = Champs\assainir_date( $saisie );

	if ( '' !== $saisie && '' === $lue ) {
		$conservee = (string) get_post_meta( $post_id, '_mtb_date_naissance', true );

		$avis[] = avis( 'warning', phrase_date_refusee( $saisie, $conservee ) );

		return;
	}

	ecrire( $post_id, '_mtb_date_naissance', $lue, $catalogue );
}

/**
 * Écrit un compteur de chiots, ou conserve le précédent et le dit.
 *
 * Même règle que pour la date : un champ vidé est une intention, on écrit le vide ; une saisie non
 * vide qu'on ne sait pas lire n'en est pas une. « 0 » reste « 0 » — c'est un fait d'élevage — et le
 * vide reste le vide.
 *
 * @param int    $post_id   Identifiant de la portée.
 * @param array  $brut      Données soumises, déjà déséchappées.
 * @param array  $catalogue Catalogue des champs.
 * @param array  $avis      Avis en construction, complété par référence.
 * @param string $cle       Clé du compteur.
 * @param string $libelle   Libellé exact du champ, tel qu'il est à l'écran.
 */
function enregistrer_compteur( int $post_id, array $brut, array $catalogue, array &$avis, string $cle, string $libelle ): void {
	$saisie = isset( $brut[ $cle ] ) && is_scalar( $brut[ $cle ] ) ? trim( (string) $brut[ $cle ] ) : '';

	$lue = Champs\assainir_compteur( $saisie );

	if ( '' !== $saisie && '' === $lue ) {
		$conservee = (string) get_post_meta( $post_id, $cle, true );

		$avis[] = avis( 'warning', phrase_compteur_refuse( $libelle, $saisie, $conservee ) );

		return;
	}

	ecrire( $post_id, $cle, $lue, $catalogue );
}

/**
 * Assainit puis écrit un champ de la portée.
 *
 * wp_slash() avant l'écriture : update_post_meta() déséchappe la valeur reçue avant de l'assainir,
 * une valeur déjà déséchappée y perdrait ses contre-obliques.
 *
 * @param int    $post_id   Identifiant de la portée.
 * @param string $cle       Clé du champ.
 * @param mixed  $valeur    Valeur soumise, déjà déséchappée.
 * @param array  $catalogue Catalogue des champs.
 */
function ecrire( int $post_id, string $cle, $valeur, array $catalogue ): void {
	if ( ! isset( $catalogue[ $cle ]['assainir'] ) || ! is_callable( $catalogue[ $cle ]['assainir'] ) ) {
		return;
	}

	$assainie = call_user_func( $catalogue[ $cle ]['assainir'], $valeur );

	update_post_meta( $post_id, $cle, wp_slash( $assainie ) );
}

/**
 * Écrit le mode de saisie de chaque parent, puis la seule branche retenue.
 *
 * La branche non retenue conserve sa valeur : l'éleveuse peut basculer d'un chemin à l'autre sans
 * rien reperdre. C'est aussi pourquoi « _mtb_pere_fiche » peut encore porter un identifiant alors
 * que la portée déclare un étalon extérieur — le type fait foi, jamais l'identifiant.
 *
 * @param int   $post_id   Identifiant de la portée.
 * @param array $brut      Données soumises, déjà déséchappées.
 * @param array $catalogue Catalogue des champs.
 */
function enregistrer_parents( int $post_id, array $brut, array $catalogue ): void {
	foreach ( array( 'pere', 'mere' ) as $branche ) {
		$cle_type = '_mtb_' . $branche . '_type';
		$type     = Champs\assainir_type_parent( $brut[ $cle_type ] ?? '' );

		ecrire( $post_id, $cle_type, $type, $catalogue );

		if ( 'fiche' === $type ) {
			$cle = '_mtb_' . $branche . '_fiche';
			ecrire( $post_id, $cle, $brut[ $cle ] ?? 0, $catalogue );

			continue;
		}

		if ( 'exterieur' === $type ) {
			foreach ( array( '_nom', '_elevage', '_sante' ) as $suffixe ) {
				$cle = '_mtb_' . $branche . $suffixe;
				ecrire( $post_id, $cle, $brut[ $cle ] ?? '', $catalogue );
			}
		}
	}
}

/**
 * Extrait les rangées de chiots soumises, en écartant celles cochées « Retirer ce chiot ».
 *
 * La réindexation et l'écart des rangées entièrement vides appartiennent à l'assainisseur.
 *
 * @param array $brut Données soumises, déjà déséchappées.
 *
 * @return array<int,array<string,mixed>> Rangées conservées.
 */
function chiots_soumis( array $brut ): array {
	if ( ! isset( $brut['chiots'] ) || ! is_array( $brut['chiots'] ) ) {
		return array();
	}

	$rangees = array();

	foreach ( $brut['chiots'] as $rangee ) {
		if ( ! is_array( $rangee ) || isset( $rangee['retirer'] ) ) {
			continue;
		}

		$rangees[] = $rangee;
	}

	return $rangees;
}

/**
 * Extrait la galerie soumise, en écartant les photos dont la case « Retirer la photo 1 » est cochée.
 *
 * Retirer une photo retire un identifiant de la liste ; le fichier reste sur le site.
 *
 * @param array $brut Données soumises, déjà déséchappées.
 *
 * @return array<int,int> Identifiants conservés, dans l'ordre soumis.
 */
function galerie_soumise( array $brut ): array {
	if ( ! isset( $brut['_mtb_galerie'] ) || ! is_array( $brut['_mtb_galerie'] ) ) {
		return array();
	}

	$retirees = array();

	if ( isset( $brut['_mtb_galerie_retirer'] ) && is_array( $brut['_mtb_galerie_retirer'] ) ) {
		foreach ( $brut['_mtb_galerie_retirer'] as $valeur ) {
			if ( is_scalar( $valeur ) ) {
				$retirees[] = absint( $valeur );
			}
		}
	}

	$conservees = array();

	foreach ( $brut['_mtb_galerie'] as $valeur ) {
		if ( ! is_scalar( $valeur ) ) {
			continue;
		}

		$identifiant = absint( $valeur );

		if ( $identifiant > 0 && ! in_array( $identifiant, $retirees, true ) ) {
			$conservees[] = $identifiant;
		}
	}

	return $conservees;
}

/**
 * Contrôle les deux champs obligatoires et le doublon d'identifiant.
 *
 * Jamais de wp_die(), jamais de refus d'enregistrement : une portée déjà publiée à qui il manque
 * un champ reste publiée, parce que la rétrograder ferait tomber en 404 une adresse que des
 * familles ont en favori.
 *
 * @param int      $post_id Identifiant de la portée.
 * @param \WP_Post $post    Portée enregistrée.
 * @param array    $brut    Données soumises, déjà déséchappées.
 * @param array    $avis    Avis en construction, complété par référence.
 */
function controler( int $post_id, \WP_Post $post, array $brut, array &$avis ): void {
	$manquants   = array();
	$identifiant = trim( (string) $post->post_title );

	if ( '' === $identifiant ) {
		$manquants[] = 'Identifiant de la portée';
	}

	if ( '' === (string) get_post_meta( $post_id, '_mtb_date_naissance', true ) ) {
		$manquants[] = 'Date de naissance';
	}

	$brouillon = false;
	$statut    = (string) $post->post_status;

	if ( array() !== $manquants ) {
		$statut_initial = isset( $brut['original_post_status'] ) && is_scalar( $brut['original_post_status'] )
			? sanitize_key( (string) $brut['original_post_status'] )
			: '';

		if ( 'publish' === $statut && 'publish' !== $statut_initial ) {
			remove_action( 'save_post_mtb_portee', __NAMESPACE__ . '\\enregistrer_champs', 10 );
			wp_update_post(
				array(
					'ID'          => $post_id,
					'post_status' => 'draft',
				)
			);
			add_action( 'save_post_mtb_portee', __NAMESPACE__ . '\\enregistrer_champs', 10, 3 );

			$brouillon = true;
			$statut    = 'draft';

			Avis::signaler_retour_en_brouillon();
		}

		$avis[] = avis( 'warning', phrase_champs_manquants( $manquants, $brouillon, $statut ) );
	}

	if ( '' !== $identifiant && porte_un_doublon( $post_id, $identifiant ) ) {
		$avis[] = avis( 'warning', phrase_doublon( $identifiant ) );
	}
}

/**
 * Dit si une autre portée porte déjà cet identifiant.
 *
 * Un doublon est un avertissement, jamais un refus d'enregistrer.
 *
 * @param int    $post_id     Identifiant de la portée en cours.
 * @param string $identifiant Identifiant saisi.
 *
 * @return bool Vrai si une autre portée porte le même identifiant.
 */
function porte_un_doublon( int $post_id, string $identifiant ): bool {
	$requete = new \WP_Query(
		array(
			'post_type'              => 'mtb_portee',
			'title'                  => $identifiant,
			'post_status'            => array( 'publish', 'future', 'draft', 'pending', 'private' ),
			'post__not_in'           => array( $post_id ),
			'posts_per_page'         => 1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	return array() !== $requete->posts;
}
