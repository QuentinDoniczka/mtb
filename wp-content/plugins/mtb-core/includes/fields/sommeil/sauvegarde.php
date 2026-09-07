<?php
/**
 * Enregistrement de l'état « en sommeil » depuis le formulaire de l'éditeur classique.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Fields\Sommeil;

use const MTB\Core\Query\MiseEnSommeil\CLE;
use const MTB\Core\Query\MiseEnSommeil\ENDORMI;
use const MTB\Core\Query\MiseEnSommeil\REVEILLE;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Écrit l'état « en sommeil » du contenu enregistré.
 *
 * CINQ GARDES, DANS CET ORDRE, ET LA DEUXIÈME EST PORTEUSE, PAS DÉCORATIVE.
 *
 * Une case à cocher non cochée N'EST PAS POSTÉE. Une case absente du POST est donc rigoureusement
 * indistinguable de « décochée » : le formulaire ne dit pas la différence entre « elle a décoché » et
 * « ce formulaire ne contenait pas la case ». Sans la sentinelle du nonce, l'édition rapide,
 * l'édition en lot, wp_publish_post() et l'enregistrement automatique — qui postent TOUS un
 * formulaire partiel, sans notre case — écriraient « 0 » et RÉVEILLERAIENT UN CONTENU ENDORMI EN
 * SILENCE. Aucune erreur, aucun journal, un écran qui répond 200 et un contenu qui revient dans
 * Google sans que personne l'ait demandé. C'est exactement le motif écrit à
 * « fields/portee/sauvegarde.php:36-43 », et c'est le même garde-fou.
 *
 * La présence du champ de nonce vaut donc « ce POST vient bien de l'écran d'édition, la case y était,
 * et son absence signifie décochée ». La vérification cryptographique qui suit vaut « et ce POST
 * vient bien de cette personne, sur ce contenu-là ».
 *
 * L'écriture est inconditionnelle : « 1 » ou « 0 », jamais la suppression de la clé. Le troisième
 * état — clé absente — est l'état de DÉPART, il ne se rétablit pas depuis un écran, et le rétablir
 * rendrait le contenu à nouveau convertible par la reprise du fait hérité.
 *
 * @param int      $post_id     Identifiant du contenu enregistré.
 * @param \WP_Post $post        Contenu enregistré.
 * @param bool     $mise_a_jour Vrai s'il s'agit d'une modification.
 *
 * @return void
 */
function enregistrer( int $post_id, \WP_Post $post, bool $mise_a_jour ): void {
	unset( $post, $mise_a_jour );

	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}

	if ( ! isset( $_POST['mtb_sommeil_nonce'] ) ) {
		return;
	}

	$nonce = sanitize_text_field( wp_unslash( $_POST['mtb_sommeil_nonce'] ) );

	if ( ! wp_verify_nonce( $nonce, 'mtb_sommeil' ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce vérifié ci-dessus.
	$coche = isset( $_POST[ CHAMP ] )
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce vérifié ci-dessus.
		&& ENDORMI === sanitize_text_field( wp_unslash( $_POST[ CHAMP ] ) );

	update_post_meta( $post_id, CLE, $coche ? ENDORMI : REVEILLE );
}
