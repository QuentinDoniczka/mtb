<?php
/**
 * Écran de saisie d'un résultat de travail.
 *
 * Aucune règle visuelle, aucun style en ligne, aucun JavaScript : les classes du cœur suffisent, et
 * tous les contrôles sont visibles d'emblée.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Fields\Resultat;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Affiche la boîte de saisie. Tout échappement se fait ici, au rendu.
 *
 * @param \WP_Post $post Résultat en cours d'édition.
 */
function rendre_ecran( \WP_Post $post ): void {
	$discipline = (string) get_post_meta( $post->ID, '_mtb_discipline', true );
	$chien_id   = absint( get_post_meta( $post->ID, '_mtb_chien_id', true ) );
	$chien_nom  = (string) get_post_meta( $post->ID, '_mtb_chien_nom', true );
	$sexe       = (string) get_post_meta( $post->ID, '_mtb_sexe', true );
	$annee      = absint( get_post_meta( $post->ID, '_mtb_annee', true ) );
	$niveau     = (string) get_post_meta( $post->ID, '_mtb_niveau', true );
	$conducteur = (string) get_post_meta( $post->ID, '_mtb_conducteur', true );
	$pays       = (string) get_post_meta( $post->ID, '_mtb_pays', true );

	wp_nonce_field( 'mtb_resultat_saisie', 'mtb_resultat_nonce' );
	?>
	<table class="form-table" role="presentation">
		<tbody>
			<tr>
				<th scope="row"><label for="mtb-resultat-discipline">Discipline</label></th>
				<td>
					<select
						id="mtb-resultat-discipline"
						name="mtb_resultat_discipline"
						aria-describedby="mtb-resultat-discipline-aide">
						<?php foreach ( options_disciplines( $discipline ) as $valeur => $libelle ) : ?>
							<option value="<?php echo esc_attr( $valeur ); ?>"<?php selected( $valeur, $discipline ); ?>>
								<?php echo esc_html( $libelle ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<p class="description" id="mtb-resultat-discipline-aide">
						La discipline dans laquelle ce résultat a été obtenu.
					</p>
				</td>
			</tr>

			<?php rendre_controle_chien( $chien_id, $chien_nom ); ?>

			<tr>
				<th scope="row"><label for="mtb-resultat-sexe">Sexe</label></th>
				<td>
					<select
						id="mtb-resultat-sexe"
						name="mtb_resultat_sexe"
						aria-describedby="mtb-resultat-sexe-aide">
						<?php foreach ( options_sexes( $sexe ) as $valeur => $libelle ) : ?>
							<option value="<?php echo esc_attr( $valeur ); ?>"<?php selected( $valeur, $sexe ); ?>>
								<?php echo esc_html( $libelle ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<p class="description" id="mtb-resultat-sexe-aide">
						Utile pour un chien qui n’a pas de fiche. Si une fiche est choisie, c’est le
						sexe de la fiche qui est affiché.
					</p>
				</td>
			</tr>

			<tr>
				<th scope="row"><label for="mtb-resultat-annee">Année</label></th>
				<td>
					<input
						type="text"
						class="small-text"
						inputmode="numeric"
						id="mtb-resultat-annee"
						name="mtb_resultat_annee"
						value="<?php echo esc_attr( 0 === $annee ? '' : (string) $annee ); ?>"
						aria-describedby="mtb-resultat-annee-aide" />
					<p class="description" id="mtb-resultat-annee-aide">
						L’année où le résultat a été obtenu, en quatre chiffres. Laissez vide si vous
						ne la connaissez pas.
					</p>
				</td>
			</tr>

			<tr>
				<th scope="row"><label for="mtb-resultat-niveau">Niveau ou titre obtenu</label></th>
				<td>
					<input
						type="text"
						class="regular-text"
						id="mtb-resultat-niveau"
						name="mtb_resultat_niveau"
						value="<?php echo esc_attr( $niveau ); ?>"
						list="mtb-resultat-niveaux"
						aria-describedby="mtb-resultat-niveau-aide" />
					<?php rendre_suggestions( 'mtb-resultat-niveaux', '_mtb_niveau' ); ?>
					<p class="description" id="mtb-resultat-niveau-aide">
						Recopiez le niveau ou le titre tel qu’il est écrit sur le document officiel.
						Les valeurs déjà saisies sont proposées dès les premières lettres.
					</p>
				</td>
			</tr>

			<tr>
				<th scope="row"><label for="mtb-resultat-conducteur">Conducteur</label></th>
				<td>
					<input
						type="text"
						class="regular-text"
						id="mtb-resultat-conducteur"
						name="mtb_resultat_conducteur"
						value="<?php echo esc_attr( $conducteur ); ?>"
						list="mtb-resultat-conducteurs"
						aria-describedby="mtb-resultat-conducteur-aide" />
					<?php rendre_suggestions( 'mtb-resultat-conducteurs', '_mtb_conducteur' ); ?>
					<p class="description" id="mtb-resultat-conducteur-aide">
						La personne qui menait le chien. Laissez vide si vous ne la connaissez pas.
					</p>
				</td>
			</tr>

			<tr>
				<th scope="row"><label for="mtb-resultat-pays">Pays</label></th>
				<td>
					<input
						type="text"
						class="regular-text"
						id="mtb-resultat-pays"
						name="mtb_resultat_pays"
						value="<?php echo esc_attr( $pays ); ?>"
						aria-describedby="mtb-resultat-pays-aide" />
					<p class="description" id="mtb-resultat-pays-aide">
						À remplir seulement si le résultat a été obtenu à l’étranger. Un pays laissé
						vide n’affiche rien du tout sur le site.
					</p>
				</td>
			</tr>
		</tbody>
	</table>
	<?php
}

/**
 * Options du choix de discipline, la valeur enregistrée toujours présélectionnable.
 *
 * Une valeur devenue orpheline est ajoutée telle quelle en fin de liste : un ré-enregistrement ne
 * l'efface pas.
 *
 * @param string $selection Valeur enregistrée.
 *
 * @return array<string, string> Valeur vers libellé, l'option vide en premier.
 */
function options_disciplines( string $selection ): array {
	$options = array( '' => 'Non renseigné' );

	foreach ( liste_disciplines() as $cle => $libelle ) {
		$options[ $cle ] = $libelle;
	}

	if ( '' !== $selection && ! isset( $options[ $selection ] ) ) {
		$options[ $selection ] = $selection;
	}

	return $options;
}

/**
 * Options du choix de sexe, la valeur enregistrée toujours présélectionnable.
 *
 * @param string $selection Valeur enregistrée.
 *
 * @return array<string, string> Valeur vers libellé, l'option vide en premier.
 */
function options_sexes( string $selection ): array {
	$options = array( '' => 'Non renseigné' );

	foreach ( liste_sexes() as $cle => $libelle ) {
		$options[ $cle ] = $libelle;
	}

	if ( '' !== $selection && ! isset( $options[ $selection ] ) ) {
		$options[ $selection ] = $selection;
	}

	return $options;
}

/**
 * Liste close des disciplines, lue depuis son unique source.
 *
 * L'écran n'en refait jamais une : ajouter une discipline doit rester une seule ligne, à un seul
 * endroit.
 *
 * @return array<string, string> Clé vers libellé, tableau vide si la source est indisponible.
 */
function liste_disciplines(): array {
	if ( ! function_exists( 'mtb_resultat_disciplines' ) ) {
		return array();
	}

	return mtb_resultat_disciplines();
}

/**
 * Liste close des sexes, lue depuis son unique source.
 *
 * @return array<string, string> Clé vers libellé, tableau vide si la source est indisponible.
 */
function liste_sexes(): array {
	if ( ! function_exists( 'mtb_resultat_sexes' ) ) {
		return array();
	}

	return mtb_resultat_sexes();
}

/**
 * Affiche la liste de suggestions rattachée à un champ libre.
 *
 * Accélérateur de frappe sans une ligne de JavaScript : elle tape une lettre, elle choisit. Rien
 * n'est imposé — la liste ne contraint jamais la valeur saisie.
 *
 * @param string $identifiant Identifiant repris par l'attribut « list » du champ.
 * @param string $cle_champ   Clé du champ dont on relit les valeurs déjà saisies.
 */
function rendre_suggestions( string $identifiant, string $cle_champ ): void {
	$valeurs = valeurs_deja_saisies( $cle_champ );

	if ( array() === $valeurs ) {
		return;
	}
	?>
	<datalist id="<?php echo esc_attr( $identifiant ); ?>">
		<?php foreach ( $valeurs as $valeur ) : ?>
			<option value="<?php echo esc_attr( $valeur ); ?>"></option>
		<?php endforeach; ?>
	</datalist>
	<?php
}

/**
 * Valeurs distinctes déjà saisies pour un champ, tous statuts confondus.
 *
 * @param string $cle_champ Clé du champ.
 *
 * @return string[] Valeurs distinctes non vides, triées.
 */
function valeurs_deja_saisies( string $cle_champ ): array {
	static $memoire = array();

	if ( isset( $memoire[ $cle_champ ] ) ) {
		return $memoire[ $cle_champ ];
	}

	$valeurs = array();

	foreach ( resultats_existants() as $resultat ) {
		$valeur = (string) get_post_meta( $resultat->ID, $cle_champ, true );

		if ( '' !== $valeur ) {
			$valeurs[ $valeur ] = true;
		}
	}

	/*
	 * Les valeurs ont servi de clés pour dédoublonner, et PHP retransforme en entier toute clé
	 * purement numérique : un niveau saisi « 3 » ressortirait d'array_keys() en int, et le type
	 * annoncé ici serait faux. On rétablit la chaîne, pour que l'annotation reste vraie le jour où
	 * un appel typé se placera en aval.
	 */
	$valeurs = array_map( 'strval', array_keys( $valeurs ) );

	sort( $valeurs, SORT_NATURAL | SORT_FLAG_CASE );

	$memoire[ $cle_champ ] = $valeurs;

	return $valeurs;
}

/**
 * Résultats déjà enregistrés, chargés une seule fois pour alimenter les suggestions.
 *
 * Le nombre est plafonné : les suggestions sont un accélérateur de frappe, pas un index exhaustif,
 * et l'écran de saisie ne doit jamais dépendre du volume enregistré.
 *
 * @return \WP_Post[] Résultats les plus récents, tableau vide si aucun.
 */
function resultats_existants(): array {
	static $resultats = null;

	if ( null !== $resultats ) {
		return $resultats;
	}

	$resultats = get_posts(
		array(
			'post_type'              => 'mtb_resultat',
			'post_status'            => array( 'publish', 'future', 'draft', 'pending', 'private' ),
			'posts_per_page'         => 200,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'ignore_sticky_posts'    => true,
			'orderby'                => 'ID',
			'order'                  => 'DESC',
		)
	);

	return $resultats;
}
