<?php
/**
 * Écran « Coordonnées de l'élevage » : son menu, son formulaire et son traitement.
 *
 * Formulaire écrit à la main, et non déclaré par la Settings API : celle-ci poste vers
 * « wp-admin/options.php », qui exige « manage_options ». L'éleveuse est Éditeur — l'écran lui
 * répondrait 403 sur l'enregistrement, ou n'accepterait son envoi qu'au prix du filtre
 * « option_page_capability_{$groupe} », dont le suffixe est le groupe d'options et non le nom de
 * l'option : panne muette classique.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Admin\Coordonnees;

use function MTB\Core\Query\Coordonnees\assainir;
use function MTB\Core\Query\Coordonnees\lire;

use const MTB\Core\Query\Coordonnees\NOM_OPTION;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Capacité requise pour voir l'écran et pour enregistrer.
 *
 * « edit_pages », JAMAIS « manage_options ». Fabienne est Éditeur natif : le rôle possède
 * « edit_pages », il ne possède ni « manage_options » ni « edit_theme_options ». Un écran sous
 * « manage_options » lui serait tout simplement INVISIBLE, sur un site qui répond 200, et personne
 * ne s'en apercevrait tant qu'on ne testerait qu'en administrateur.
 *
 * Elle dit par ailleurs quelque chose de juste : la personne qui tient les pages du site tient les
 * coordonnées du site. Auteur et Contributeur ne l'ont pas.
 */
const CAPACITE = 'edit_pages';

/**
 * Identifiant de l'écran dans l'URL d'administration.
 */
const PAGE = 'mtb-coordonnees';

/**
 * Action postée vers « admin-post.php », d'où le hook « admin_post_mtb_enregistrer_coordonnees ».
 */
const ACTION = 'mtb_enregistrer_coordonnees';

/**
 * Nom du champ portant le nonce.
 */
const CHAMP_NONCE = 'mtb_coordonnees_nonce';

/**
 * Déclare l'entrée de menu de premier niveau.
 *
 * Menu de premier niveau et non « add_options_page() » : ce dernier ferait apparaître à l'éleveuse
 * un menu « Réglages » ne contenant qu'une seule entrée, dont le nom promet dix écrans.
 *
 * Position 24 : les trois types de contenu occupent 21, 22 et 23, l'écran se range juste après eux.
 */
function ajouter_menu(): void {
	add_menu_page(
		'Coordonnées de l\'élevage',
		'Coordonnées',
		CAPACITE,
		PAGE,
		__NAMESPACE__ . '\\afficher',
		'dashicons-location-alt',
		24
	);
}

/**
 * Affiche l'écran.
 *
 * @return void
 */
function afficher(): void {
	if ( ! current_user_can( CAPACITE ) ) {
		wp_die(
			esc_html( 'Vous n\'avez pas l\'autorisation de modifier les coordonnées de l\'élevage.' ),
			esc_html( 'Accès refusé' ),
			array( 'response' => 403 )
		);
	}

	$valeurs = lire();

	/*
	 * Simple drapeau d'affichage posé par la redirection : aucune écriture n'en dépend, d'où
	 * l'absence de nonce. sanitize_key() est ici sans danger — ce n'est pas une valeur recopiée
	 * mais un mot-clé technique que ce fichier écrit lui-même.
	 */
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$drapeau = isset( $_GET['coordonnees'] ) ? sanitize_key( wp_unslash( $_GET['coordonnees'] ) ) : '';

	$enregistre = 'enregistrees' === $drapeau;

	?>
	<div class="wrap">
		<h1>Coordonnées de l'élevage</h1>

		<?php if ( $enregistre ) : ?>
			<div class="notice notice-success" role="status">
				<p>Coordonnées enregistrées.</p>
			</div>
		<?php endif; ?>

		<p>
			Ce que vous écrivez ici s'affiche partout sur le site. Un champ laissé vide retire
			l'information de tout le site : rien ne s'affiche à sa place.
		</p>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="<?php echo esc_attr( ACTION ); ?>" />
			<?php wp_nonce_field( ACTION, CHAMP_NONCE ); ?>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">
						<label for="mtb-coordonnees-adresse">Adresse</label>
					</th>
					<td>
						<textarea
							id="mtb-coordonnees-adresse"
							name="mtb_adresse"
							rows="3"
							class="large-text"
							aria-describedby="mtb-coordonnees-adresse-aide"
						><?php echo esc_textarea( $valeurs['adresse'] ); ?></textarea>
						<p class="description" id="mtb-coordonnees-adresse-aide">
							L'adresse de l'élevage, telle que vous l'écrivez. Le champ « Adresse »
							d'un composant posé dans une page, lui, ne change que cette page.
							Laissez vide pour retirer l'adresse de tout le site.
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="mtb-coordonnees-telephone">Téléphone</label>
					</th>
					<td>
						<input
							type="text"
							id="mtb-coordonnees-telephone"
							name="mtb_telephone"
							class="regular-text"
							value="<?php echo esc_attr( $valeurs['telephone'] ); ?>"
							aria-describedby="mtb-coordonnees-telephone-aide"
						/>
						<p class="description" id="mtb-coordonnees-telephone-aide">
							Le numéro auquel les familles vous joignent. Le champ « Téléphone » d'un
							composant posé dans une page ne change que cette page.
							Laissez vide pour retirer le numéro de tout le site.
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="mtb-coordonnees-courriel">Courriel</label>
					</th>
					<td>
						<input
							type="text"
							id="mtb-coordonnees-courriel"
							name="mtb_courriel"
							class="regular-text"
							value="<?php echo esc_attr( $valeurs['courriel'] ); ?>"
							aria-describedby="mtb-coordonnees-courriel-aide"
						/>
						<p class="description" id="mtb-coordonnees-courriel-aide">
							L'adresse électronique à laquelle on vous écrit. Le champ « Courriel »
							d'un composant posé dans une page ne change que cette page.
							Laissez vide pour retirer le courriel de tout le site.
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="mtb-coordonnees-page">Page de contact</label>
					</th>
					<td>
						<select
							id="mtb-coordonnees-page"
							name="mtb_page_contact"
							aria-describedby="mtb-coordonnees-page-aide"
						>
							<?php foreach ( choix_de_pages( $valeurs['page_contact'] ) as $identifiant => $titre ) : ?>
								<option
									value="<?php echo esc_attr( (string) $identifiant ); ?>"
									<?php selected( $identifiant, $valeurs['page_contact'] ); ?>
								><?php echo esc_html( $titre ); ?></option>
							<?php endforeach; ?>
						</select>
						<p class="description" id="mtb-coordonnees-page-aide">
							La page vers laquelle mènent les boutons du site qui ne désignent aucune
							page eux-mêmes. Choisissez « Aucune » pour qu'aucun bouton ne s'affiche.
						</p>
					</td>
				</tr>
			</table>

			<?php submit_button( 'Enregistrer' ); ?>
		</form>
	</div>
	<?php
}

/**
 * Compose la liste déroulante des pages : « Aucune », puis les pages publiées.
 *
 * Le choix déjà enregistré est ajouté à la liste même si la page a été dépubliée depuis, et il y
 * paraît sous son vrai titre. Sans cela, l'écran afficherait « Aucune » et le premier
 * enregistrement effacerait EN SILENCE un choix que l'éleveuse n'a pas défait. Le choix ne
 * disparaît de la liste que si la page a réellement été supprimée : il ne reste alors plus rien à
 * désigner.
 *
 * @param int $choix Identifiant actuellement enregistré, 0 pour aucun.
 *
 * @return array<int,string> Titres indexés par identifiant, « Aucune » en tête.
 */
function choix_de_pages( int $choix ): array {
	$liste = array( 0 => 'Aucune' );

	$pages = get_pages(
		array(
			'post_status' => 'publish',
			'sort_column' => 'post_title',
			'sort_order'  => 'ASC',
		)
	);

	if ( is_array( $pages ) ) {
		foreach ( $pages as $page ) {
			$liste[ (int) $page->ID ] = titre_lisible( (int) $page->ID );
		}
	}

	if ( $choix > 0 && ! isset( $liste[ $choix ] ) && get_post( $choix ) instanceof \WP_Post ) {
		$liste[ $choix ] = titre_lisible( $choix );
	}

	return $liste;
}

/**
 * Titre d'une page pour la liste déroulante, jamais vide.
 *
 * @param int $identifiant Identifiant de la page.
 *
 * @return string Titre de la page, ou une mention de repli si elle n'en porte aucun.
 */
function titre_lisible( int $identifiant ): string {
	$titre = trim( (string) get_the_title( $identifiant ) );

	return '' === $titre ? '(sans titre)' : $titre;
}

/**
 * Enregistre le formulaire, puis redirige.
 *
 * Le nonce est vérifié AVANT toute lecture de « $_POST », et la capacité l'est une seconde fois :
 * un compte sans « edit_pages » qui poste directement sur « admin-post.php » reçoit un 403 et rien
 * n'est écrit.
 *
 * Aucun champ n'est refusé, pas même vide : vider est un acte légitime, et c'est la seule route
 * vers un site qui n'affiche plus le numéro. La règle d'or veut que l'éleveuse décide, pas l'écran.
 *
 * Redirection après enregistrement (motif POST/Redirect/GET) : un rechargement de page ne renvoie
 * pas le formulaire.
 *
 * @return void
 */
function enregistrer(): void {
	check_admin_referer( ACTION, CHAMP_NONCE );

	if ( ! current_user_can( CAPACITE ) ) {
		wp_die(
			esc_html( 'Vous n\'avez pas l\'autorisation de modifier les coordonnées de l\'élevage.' ),
			esc_html( 'Accès refusé' ),
			array( 'response' => 403 )
		);
	}

	/*
	 * Les quatre valeurs passent par l'assainisseur du module, jamais par sanitize_text_field() ni
	 * ses cousines : elles suppriment sans un mot une valeur commençant par « < » (décision 20).
	 */
	// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$brut = array(
		'adresse'      => isset( $_POST['mtb_adresse'] ) ? wp_unslash( $_POST['mtb_adresse'] ) : '',
		'telephone'    => isset( $_POST['mtb_telephone'] ) ? wp_unslash( $_POST['mtb_telephone'] ) : '',
		'courriel'     => isset( $_POST['mtb_courriel'] ) ? wp_unslash( $_POST['mtb_courriel'] ) : '',
		'page_contact' => isset( $_POST['mtb_page_contact'] ) ? wp_unslash( $_POST['mtb_page_contact'] ) : 0,
	);
	// phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	// Autochargée : la valeur est lue sur toute requête publique portant un composant.
	update_option( NOM_OPTION, assainir( $brut ), true );

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'        => PAGE,
				'coordonnees' => 'enregistrees',
			),
			admin_url( 'admin.php' )
		)
	);

	exit;
}
