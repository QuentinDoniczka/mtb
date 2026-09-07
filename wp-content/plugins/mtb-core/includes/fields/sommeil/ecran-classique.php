<?php
/**
 * L'interrupteur « en sommeil » dans l'encadré « Publier » de l'éditeur classique.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Fields\Sommeil;

use function MTB\Core\Query\MiseEnSommeil\est_en_sommeil;
use const MTB\Core\Query\MiseEnSommeil\ENDORMI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rend la case à cocher, son aide, et l'avertissement de page d'accueil le cas échéant.
 *
 * POSITION RÉELLE À L'ÉCRAN, ÉCRITE ICI PARCE QU'ELLE SE REPREND TELLE QUELLE DANS LA FICHE D'AIDE :
 * « post_submitbox_misc_actions » court À LA FIN de « #misc-publishing-actions », donc APRÈS la ligne
 * « Visibilité » ET APRÈS la ligne de date. Il n'existe aucun crochet entre les deux.
 *
 * LA LIGNE DE DATE N'A PAS LE MÊME LIBELLÉ SELON L'ÉTAT DU CONTENU, et la fiche d'aide doit dire les
 * deux. Relevé au navigateur, WordPress 6.9 : sur un contenu DÉJÀ PUBLIÉ, la ligne s'intitule
 * « Publié le : » et le bouton « Mettre à jour » ; sur un contenu NEUF, la ligne s'intitule
 * « Publier tout de suite » et le bouton « Publier ». La case se rend dans les deux cas. « Publier
 * le » n'existe sur aucun des deux écrans : ne pas le réintroduire ici.
 *
 * La fiche d'aide décrit ce que l'éleveuse VOIT, et jamais ce qu'on aurait voulu.
 *
 * CLASSES DU CŒUR UNIQUEMENT : « misc-pub-section » pour la section, « howto » pour l'aide. Zéro
 * octet de CSS n'est produit par l'extension pour cet écran, et aucune classe locale n'est posée —
 * une classe qu'aucune règle ne cible est un nom qu'une chaîne future orthographiera mal.
 *
 * Le paramètre n'est pas typé : le cœur passe un WP_Post, mais la garde est écrite plutôt que
 * supposée — « strict_types » ferait de la moindre surprise une erreur fatale au milieu de l'encadré
 * « Publier », c'est-à-dire un écran d'édition tronqué sur lequel plus rien ne s'enregistre.
 *
 * @param mixed $post Contenu en cours d'édition.
 *
 * @return void
 */
function rendre_la_case( $post ): void {
	if ( ! $post instanceof \WP_Post ) {
		return;
	}

	if ( ! in_array( (string) $post->post_type, TYPES, true ) ) {
		return;
	}

	$identifiant = (int) $post->ID;

	if ( ! current_user_can( 'edit_post', $identifiant ) ) {
		return;
	}

	/*
	 * Le nonce est posé ICI, dans le formulaire de l'écran d'édition, et il est la SENTINELLE que
	 * « enregistrer() » exige : sans lui, une case absente du POST serait indistinguable d'une case
	 * décochée. Troisième argument « false » : pas de champ de renvoi, il ferait doublon avec celui
	 * du cœur.
	 */
	wp_nonce_field( 'mtb_sommeil', 'mtb_sommeil_nonce', false, true );

	$accueil = est_la_page_d_accueil( $identifiant );

	/*
	 * Les trois identifiants dérivent du NOM DU CHAMP, et la valeur postée est celle-là même que
	 * « enregistrer() » compare. Rien de tout cela n'est écrit deux fois : l'attribut « for » du
	 * libellé doit valoir l'attribut « id » de la case, et « value » doit valoir ce que la sauvegarde
	 * reconnaît comme « cochée ». Une divergence sur l'un ou l'autre est muette — la case se coche à
	 * l'écran, et l'état s'enregistre à l'envers.
	 */
	$identifiant_aide    = CHAMP . '_aide';
	$identifiant_accueil = CHAMP . '_accueil';
	$descripteur         = $identifiant_aide . ( $accueil ? ' ' . $identifiant_accueil : '' );

	echo '<div class="misc-pub-section">';

	printf(
		'<input type="checkbox" id="%1$s" name="%1$s" value="%2$s" aria-describedby="%3$s"%4$s> ',
		esc_attr( CHAMP ),
		esc_attr( ENDORMI ),
		esc_attr( $descripteur ),
		checked( est_en_sommeil( $identifiant ), true, false )
	);

	printf(
		'<label for="%s">%s</label>',
		esc_attr( CHAMP ),
		esc_html( libelle_case() )
	);

	printf(
		'<p class="howto" id="%s">%s</p>',
		esc_attr( $identifiant_aide ),
		esc_html( aide() )
	);

	if ( $accueil ) {
		printf(
			'<p class="howto" id="%s">%s</p>',
			esc_attr( $identifiant_accueil ),
			esc_html( avertissement_accueil() )
		);
	}

	echo '</div>';
}
