<?php
/**
 * Contrôle « fiche de chien ou nom recopié » de l'écran des résultats de travail.
 *
 * Copie locale, assumée : le contrôle équivalent des portées porte deux sous-champs (Nom et
 * Élevage) là où celui-ci n'en porte qu'un — le site repris imprime une chaîne unique où l'affixe
 * fait partie du nom. Fusionner les deux imposerait ici un champ « Élevage » que le domaine ne
 * demande pas. Le rapprochement est une dette identifiée, pas un oubli.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Fields\Resultat;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Affiche les deux champs du chien, toujours visibles tous les deux.
 *
 * Aucun des deux n'est masqué en attendant une interaction : un contrôle qui dépend du JavaScript
 * casse au clavier, et le chemin « sans fiche » est ici le cas courant, pas la soupape rare.
 *
 * @param int    $chien_id  Identifiant de fiche enregistré, zéro si aucun.
 * @param string $chien_nom Nom recopié enregistré.
 */
function rendre_controle_chien( int $chien_id, string $chien_nom ): void {
	?>
	<tr>
		<th scope="row"><label for="mtb-resultat-chien-id">Chien concerné</label></th>
		<td>
			<fieldset>
				<legend class="screen-reader-text">Chien concerné</legend>
				<select
					id="mtb-resultat-chien-id"
					name="mtb_resultat_chien_id"
					aria-describedby="mtb-resultat-chien-id-aide">
					<?php foreach ( options_fiches( $chien_id ) as $valeur => $libelle ) : ?>
						<option value="<?php echo esc_attr( (string) $valeur ); ?>"<?php selected( (int) $valeur, $chien_id ); ?>>
							<?php echo esc_html( $libelle ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<p class="description" id="mtb-resultat-chien-id-aide">
					Choisissez la fiche du chien si elle existe déjà sur le site.
				</p>

				<p>
					<label for="mtb-resultat-chien-nom">Nom du chien (si le chien n’a pas de fiche)</label><br />
					<input
						type="text"
						class="regular-text"
						id="mtb-resultat-chien-nom"
						name="mtb_resultat_chien_nom"
						value="<?php echo esc_attr( $chien_nom ); ?>"
						aria-describedby="mtb-resultat-chien-nom-aide" />
				</p>
				<p class="description" id="mtb-resultat-chien-nom-aide">
					À remplir seulement si le chien n’a pas de fiche sur le site. Recopiez son nom tel
					qu’il s’écrit, avec son affixe.
				</p>

				<p class="description">
					Si une fiche est choisie, elle l’emporte. Le nom recopié ne sert que si aucune
					fiche n’est choisie.
				</p>
			</fieldset>
		</td>
	</tr>
	<?php
}

/**
 * Construit la liste des options du sélecteur de fiche.
 *
 * La valeur enregistrée reste toujours présélectionnée, même si la fiche est passée à la corbeille
 * ou a disparu : un ré-enregistrement ne doit jamais perdre le lien.
 *
 * @param int $chien_id Identifiant enregistré, zéro si aucun.
 *
 * @return array<int, string> Identifiant vers libellé, l'option vide en premier.
 */
function options_fiches( int $chien_id ): array {
	$options = array( 0 => '— Aucune fiche —' );

	foreach ( fiches_disponibles() as $fiche ) {
		$options[ (int) $fiche->ID ] = '' === (string) $fiche->post_title
			? 'Fiche n° ' . (string) $fiche->ID
			: (string) $fiche->post_title;
	}

	if ( 0 < $chien_id && ! isset( $options[ $chien_id ] ) ) {
		$options[ $chien_id ] = libelle_fiche_hors_liste( $chien_id );
	}

	return $options;
}

/**
 * Libellé d'une fiche qui n'apparaît pas dans la liste courante.
 *
 * @param int $chien_id Identifiant enregistré.
 *
 * @return string Libellé lisible, jamais une clé technique.
 */
function libelle_fiche_hors_liste( int $chien_id ): string {
	$fiche = get_post( $chien_id );

	if ( ! $fiche instanceof \WP_Post ) {
		return 'Fiche n° ' . (string) $chien_id . ' (introuvable)';
	}

	if ( '' === (string) $fiche->post_title ) {
		return 'Fiche n° ' . (string) $chien_id;
	}

	return (string) $fiche->post_title;
}

/**
 * Fiches de chiens proposées au choix.
 *
 * Tant que les fiches de chiens n'existent pas sur le site, la liste est vide et le champ « Nom du
 * chien » reste pleinement utilisable : aucune erreur, aucun avertissement.
 *
 * @return \WP_Post[] Fiches triées par titre, tableau vide si aucune.
 */
function fiches_disponibles(): array {
	static $fiches = null;

	if ( null !== $fiches ) {
		return $fiches;
	}

	if ( false === post_type_exists( 'mtb_chien' ) ) {
		$fiches = array();

		return $fiches;
	}

	$fiches = get_posts(
		array(
			'post_type'              => 'mtb_chien',
			'post_status'            => array( 'publish', 'future', 'draft', 'pending', 'private' ),
			'posts_per_page'         => -1,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'ignore_sticky_posts'    => true,
			'orderby'                => 'title',
			'order'                  => 'ASC',
		)
	);

	return $fiches;
}
