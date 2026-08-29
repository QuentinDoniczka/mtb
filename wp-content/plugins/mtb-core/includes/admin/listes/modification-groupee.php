<?php
/**
 * Champ « Disponibilité » dans la Modification groupée des portées, et son écriture.
 *
 * Le geste que l'issue veut rendre possible est massif : les vingt-sept portées reprises de
 * l'ancien site ont toutes une disponibilité vide. La Modification groupée le tient en un seul
 * passage, et surtout elle ne préremplit rien PAR CONSTRUCTION — c'est ce qui la rend sûre sans une
 * ligne de JavaScript.
 *
 * La Modification rapide est écartée pour cette raison exactement : WordPress n'y préremplit pas un
 * champ de ce genre, si bien que le panneau s'ouvrirait sur la première option et qu'un « Mettre à
 * jour » distrait écrirait « Chiots disponibles » sur une portée de 1995. Un fait d'élevage faux,
 * écrit en silence, par l'outil censé le protéger. Elle demande son propre lot, avec son script et
 * son accessibilité vérifiée au lecteur d'écran.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Admin\Listes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Valeur qui signifie « ne touche à rien » — la sentinelle du cœur, la même que pour le format.
 */
const SANS_CHANGEMENT = '-1';

/**
 * Imprime le champ « Disponibilité » du panneau de modification groupée des portées.
 *
 * Le cœur déclenche ce crochet une fois par colonne ajoutée : sur la liste des portées, deux fois.
 * On n'imprime que pour la colonne « Disponibilité », et on rend la main sans rien écrire partout
 * ailleurs — y compris sur les chiens et les résultats, dont le panneau ne porte aucun champ ajouté.
 *
 * Les classes employées sont celles du cœur, reprises du champ « Format » natif : c'est ainsi que
 * la contrainte « aucun octet de CSS » est tenue.
 *
 * @param string $colonne Clé interne de la colonne en cours.
 * @param string $type    Nom du type de contenu de l'écran.
 */
function champ_groupe( string $colonne, string $type ): void {
	if ( 'mtb_disponibilite' !== $colonne || 'mtb_portee' !== $type ) {
		return;
	}

	?>
	<fieldset class="inline-edit-col-right">
		<div class="inline-edit-col">
			<label class="inline-edit-group">
				<span class="title">Disponibilité</span>
				<select name="mtb_disponibilite">
					<option value="<?php echo esc_attr( SANS_CHANGEMENT ); ?>">&mdash; Pas de changement &mdash;</option>
					<?php foreach ( \MTB\Core\Content\Portee\disponibilites() as $cle => $libelle ) : ?>
						<option value="<?php echo esc_attr( (string) $cle ); ?>"><?php echo esc_html( $libelle ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
		</div>
	</fieldset>
	<?php

	/*
	 * Aucune option « Non renseigné » : vider une disponibilité en masse est un geste destructeur
	 * que l'issue ne demande pas. Il reste possible fiche par fiche, où il est délibéré.
	 */
}

/**
 * Écrit la disponibilité choisie sur les portées de la sélection.
 *
 * Le crochet employé n'est atteint que par l'écran des listes, APRÈS la vérification du jeton de la
 * modification groupée et APRÈS toute la boucle de mise à jour du cœur. Il ne peut donc
 * structurellement pas se déclencher sur un enregistrement ordinaire, un enregistrement
 * automatique, une révision, ni sur la Modification rapide, qui passe par une autre route. La garde
 * la plus dangereuse du lot est structurelle, pas déclarative.
 *
 * L'écran de saisie d'une portée, de son côté, refuse tout formulaire ne portant pas son propre
 * jeton : la mise à jour déclenchée par le cœur lui rend la main immédiatement, et les seize champs
 * d'une portée restent intacts. Cette garde vit dans le module de saisie ; elle ne doit jamais être
 * assouplie.
 *
 * Les deux paramètres ne sont pas typés : ce crochet reste appelable par un tiers avec n'importe
 * quoi, et strict_types en ferait une erreur fatale au milieu d'une mise à jour groupée.
 *
 * @param mixed $updated          Identifiants mis à jour par le cœur.
 * @param mixed $shared_post_data Données communes soumises par le panneau.
 */
function ecrire_disponibilite( $updated, $shared_post_data ): void {
	if ( ! is_admin() ) {
		return;
	}

	if ( ! is_array( $updated ) || array() === $updated ) {
		return;
	}

	if ( ! is_array( $shared_post_data ) ) {
		return;
	}

	$type = isset( $shared_post_data['post_type'] ) && is_scalar( $shared_post_data['post_type'] )
		? sanitize_key( (string) $shared_post_data['post_type'] )
		: '';

	if ( 'mtb_portee' !== $type ) {
		return;
	}

	/*
	 * Vérification du jeton, et « return » plutôt que wp_die() : la fonction qui déclenche ce
	 * crochet est publique, un tiers peut l'appeler avec des données explicites hors de toute
	 * requête de formulaire. On refuse alors d'écrire ; on ne tue pas la requête d'autrui.
	 */
	$jeton = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';

	if ( ! wp_verify_nonce( $jeton, 'bulk-posts' ) ) {
		return;
	}

	// Second filet : prouve que la soumission est bien celle du panneau de modification groupée.
	if ( ! isset( $_REQUEST['bulk_edit'] ) ) {
		return;
	}

	if ( ! isset( $shared_post_data['mtb_disponibilite'] ) || ! is_scalar( $shared_post_data['mtb_disponibilite'] ) ) {
		return;
	}

	$choix = (string) $shared_post_data['mtb_disponibilite'];

	/*
	 * Sentinelle : c'est le cas le plus fréquent. Elle a modifié autre chose — le statut, l'auteur —
	 * et n'a pas touché à la disponibilité. Rien n'est écrit, aucune clé n'est touchée.
	 */
	if ( SANS_CHANGEMENT === $choix || '' === $choix ) {
		return;
	}

	// Liste fermée lue vivante, jamais recopiée : c'est exactement celle que l'assainisseur applique.
	if ( ! isset( \MTB\Core\Content\Portee\disponibilites()[ $choix ] ) ) {
		return;
	}

	foreach ( $updated as $brut ) {
		$identifiant = (int) $brut;

		// Le cœur range un zéro dans cette liste quand une mise à jour a échoué.
		if ( $identifiant <= 0 ) {
			continue;
		}

		if ( 'mtb_portee' !== get_post_type( $identifiant ) ) {
			continue;
		}

		if ( wp_is_post_revision( $identifiant ) || wp_is_post_autosave( $identifiant ) ) {
			continue;
		}

		// Contrôle sur chaque contenu, jamais une seule fois pour toute la sélection.
		if ( ! current_user_can( 'edit_post', $identifiant ) ) {
			continue;
		}

		/*
		 * Une seule clé écrite, et aucune des quinze autres d'une portée : ni titre recalculé, ni
		 * changement de statut, ni mise à jour du contenu. wp_slash() parce que l'écriture
		 * déséchappe avant d'assainir.
		 */
		update_post_meta( $identifiant, '_mtb_disponibilite', wp_slash( $choix ) );
	}
}
