<?php
/**
 * Rendu des boîtes de l'écran de saisie d'une portée.
 *
 * Tout échappement se fait ici, au rendu, jamais en amont.
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
 * Déclare les boîtes de l'écran, dans l'ordre où l'éleveuse pense une portée.
 *
 * @param mixed $type Type de contenu de l'écran en cours.
 */
function declarer_boites( $type ): void {
	if ( 'mtb_portee' !== (string) $type ) {
		return;
	}

	/*
	 * Le commentaire de l'éleveuse n'a pas de boîte : c'est l'éditeur natif, sous le titre. Les
	 * champs viennent donc après la prose.
	 */
	$boites = array(
		'mtb-portee-identite' => array( 'La portée', __NAMESPACE__ . '\\boite_identite' ),
		'mtb-portee-parents'  => array( 'Père et mère', __NAMESPACE__ . '\\boite_parents' ),
		'mtb-portee-chiots'   => array( 'Les chiots', __NAMESPACE__ . '\\boite_chiots' ),
		'mtb-portee-galerie'  => array( 'Galerie photos', __NAMESPACE__ . '\\boite_galerie' ),
	);

	foreach ( $boites as $identifiant => $boite ) {
		add_meta_box( $identifiant, $boite[0], $boite[1], 'mtb_portee', 'normal', 'high' );
	}

	/*
	 * Le cœur ajoute sa propre boîte d'adresse à certains contenus, sous un intitulé interdit à
	 * l'écran. On ne retire pas la capacité, on la renomme : la boîte ci-dessous porte le même
	 * champ sous le libellé français, en dernier, parce qu'on ne la touche qu'en cas d'erreur.
	 */
	remove_meta_box( 'slugdiv', 'mtb_portee', 'normal' );

	add_meta_box( 'mtb-portee-adresse', 'Adresse de la page', __NAMESPACE__ . '\\boite_adresse', 'mtb_portee', 'normal', 'low' );
}

/**
 * Nomme la zone de prose qui suit le titre.
 *
 * L'éditeur natif du cœur n'a aucun intitulé : sans cette ligne, l'éleveuse voit une grande zone de
 * texte anonyme et doit deviner à quoi elle sert. Le libellé est celui du système de design, jamais
 * « Texte libre », qui est le mot du brief et non celui de l'écran.
 *
 * L'étiquette désigne « content », l'identifiant que le cœur donne à la zone de texte de l'éditeur
 * classique : un clic sur le libellé y place donc le curseur.
 *
 * @param mixed $post Contenu en cours de modification.
 */
function intituler_le_commentaire( $post ): void {
	if ( ! $post instanceof \WP_Post || 'mtb_portee' !== $post->post_type ) {
		return;
	}

	echo '<h2 class="mtb-portee-intitule"><label for="content">Commentaire de l’éleveuse</label></h2>';
	echo '<p class="description">Votre texte, tel que vous souhaitez le lire sur la page de la portée.</p>';
}

/**
 * Identifiant de la portée en cours de modification, 0 pour un écran sans contenu.
 *
 * @param mixed $post Contenu passé au rappel de la boîte.
 *
 * @return int Identifiant du contenu.
 */
function identifiant_du_contenu( $post ): int {
	return $post instanceof \WP_Post ? (int) $post->ID : 0;
}

/**
 * Lit un champ de la portée sous forme de chaîne.
 *
 * @param int    $post_id Identifiant de la portée.
 * @param string $cle     Clé du champ.
 *
 * @return string Valeur stockée, chaîne vide si la donnée manque.
 */
function lire( int $post_id, string $cle ): string {
	if ( $post_id <= 0 ) {
		return '';
	}

	$valeur = get_post_meta( $post_id, $cle, true );

	return is_scalar( $valeur ) ? (string) $valeur : '';
}

/**
 * Boîte « La portée » : date de naissance, disponibilité, compteurs.
 *
 * @param mixed $post Contenu en cours de modification.
 */
function boite_identite( $post ): void {
	$post_id = identifiant_du_contenu( $post );

	wp_nonce_field( 'mtb_portee_ecran', 'mtb_portee_ecran' );

	$date     = lire( $post_id, '_mtb_date_naissance' );
	$dispo    = lire( $post_id, '_mtb_disponibilite' );
	$males    = lire( $post_id, '_mtb_males' );
	$femelles = lire( $post_id, '_mtb_femelles' );

	echo '<table class="form-table" role="presentation"><tbody>';

	echo '<tr>';
	echo '<th scope="row"><label for="mtb-portee-date-naissance">Date de naissance (obligatoire)</label></th>';
	echo '<td>';
	echo '<input type="date" id="mtb-portee-date-naissance" name="_mtb_date_naissance" value="' . esc_attr( $date ) . '" class="regular-text">';
	echo '<p class="description">La date de naissance des chiots. C’est elle qui classe les portées sur le site et qui désigne la dernière.</p>';
	echo '</td></tr>';

	echo '<tr>';
	echo '<th scope="row"><label for="mtb-portee-disponibilite">Disponibilité</label></th>';
	echo '<td>';
	echo '<select id="mtb-portee-disponibilite" name="_mtb_disponibilite">';
	echo '<option value="">Non renseigné</option>';
	foreach ( Champs\disponibilites() as $cle => $libelle ) {
		echo '<option value="' . esc_attr( $cle ) . '"' . selected( $dispo, $cle, false ) . '>' . esc_html( $libelle ) . '</option>';
	}
	echo '</select>';
	echo '<p class="description">Tant que rien n’est choisi, aucune mention de disponibilité n’apparaît sur le site.</p>';
	echo '</td></tr>';

	echo '<tr>';
	echo '<th scope="row"><label for="mtb-portee-males">Nombre de mâles</label></th>';
	echo '<td>';
	echo '<input type="number" min="0" step="1" inputmode="numeric" id="mtb-portee-males" name="_mtb_males" value="' . esc_attr( $males ) . '" class="small-text">';
	echo '<p class="description">Laissez vide tant que vous ne le savez pas. Zéro est une réponse, vide n’en est pas une.</p>';
	echo '</td></tr>';

	echo '<tr>';
	echo '<th scope="row"><label for="mtb-portee-femelles">Nombre de femelles</label></th>';
	echo '<td>';
	echo '<input type="number" min="0" step="1" inputmode="numeric" id="mtb-portee-femelles" name="_mtb_femelles" value="' . esc_attr( $femelles ) . '" class="small-text">';
	echo '<p class="description">Laissez vide tant que vous ne le savez pas. Zéro est une réponse, vide n’en est pas une.</p>';
	echo '</td></tr>';

	echo '</tbody></table>';
}

/**
 * Boîte « Père et mère » : deux chemins de saisie, à égalité.
 *
 * @param mixed $post Contenu en cours de modification.
 */
function boite_parents( $post ): void {
	$post_id = identifiant_du_contenu( $post );

	echo '<p class="description">Deux façons de renseigner un parent : choisir une fiche déjà présente sur le site, ou saisir son nom et son élevage. Les deux se valent.</p>';

	champ_parent( $post_id, 'pere' );
	champ_parent( $post_id, 'mere' );
}

/**
 * Rend le groupe de saisie d'un parent.
 *
 * Le libellé du second chemin diffère volontairement entre le père et la mère : « étalon » désigne
 * un reproducteur mâle, et « lice extérieure » inventerait un terme d'élevage.
 *
 * @param int    $post_id Identifiant de la portée.
 * @param string $branche « pere » ou « mere ».
 */
function champ_parent( int $post_id, string $branche ): void {
	$est_pere = ( 'pere' === $branche );

	$legende       = $est_pere ? 'Père' : 'Mère';
	$libelle_fiche = $est_pere ? 'Il a une fiche sur le site' : 'Elle a une fiche sur le site';
	$libelle_libre = $est_pere ? 'Étalon extérieur' : 'Elle n’a pas de fiche sur le site';
	$libelle_liste = $est_pere ? 'Fiche du père' : 'Fiche de la mère';
	$libelle_sante = $est_pere ? 'Tests de santé du père' : 'Tests de santé de la mère';
	$phrase_perdue = $est_pere
		? 'La fiche de chien liée n’existe plus. Choisissez une autre fiche, ou saisissez le père comme étalon extérieur.'
		: 'La fiche de chien liée n’existe plus. Choisissez une autre fiche, ou saisissez la mère sans fiche sur le site.';

	$type    = lire( $post_id, '_mtb_' . $branche . '_type' );
	$fiche   = (int) lire( $post_id, '_mtb_' . $branche . '_fiche' );
	$nom     = lire( $post_id, '_mtb_' . $branche . '_nom' );
	$elevage = lire( $post_id, '_mtb_' . $branche . '_elevage' );
	$sante   = lire( $post_id, '_mtb_' . $branche . '_sante' );

	$options     = options_de_fiches();
	$introuvable = false;

	if ( $fiche > 0 && ! isset( $options[ $fiche ] ) ) {
		$fiche_stockee = get_post( $fiche );

		if ( $fiche_stockee instanceof \WP_Post && 'mtb_chien' === $fiche_stockee->post_type ) {
			$options[ $fiche ] = (string) $fiche_stockee->post_title;
		} else {
			// La valeur est conservée en option : un select qui ne la porte pas l'effacerait au premier enregistrement.
			$options[ $fiche ] = 'Fiche introuvable';
			$introuvable       = true;
		}
	}

	$aucune_fiche = ( 0 === count( $options ) );

	/*
	 * On ne désactive jamais le bouton radio « fiche » quand la valeur stockée vaut déjà « fiche » :
	 * un champ désactivé n'est pas soumis, et le premier enregistrement effacerait la relation.
	 */
	$desactiver = ( $aucune_fiche && 'fiche' !== $type );

	$prefixe = 'mtb-portee-' . $branche;

	echo '<fieldset class="mtb-portee-parent" data-parent="' . esc_attr( $branche ) . '">';
	echo '<legend><strong>' . esc_html( $legende ) . '</strong></legend>';

	echo '<p><label>';
	echo '<input type="radio" name="_mtb_' . esc_attr( $branche ) . '_type" value="fiche" data-branche="fiche"';
	echo checked( $type, 'fiche', false );
	echo $desactiver ? ' disabled' : '';
	echo '> ' . esc_html( $libelle_fiche );
	echo '</label></p>';

	echo '<div class="mtb-portee-branche" data-branche="fiche">';

	if ( $introuvable ) {
		echo '<div class="notice notice-warning inline"><p>' . esc_html( $phrase_perdue ) . '</p></div>';
	}

	echo '<p><label for="' . esc_attr( $prefixe ) . '-fiche">' . esc_html( $libelle_liste ) . '</label><br>';
	echo '<select id="' . esc_attr( $prefixe ) . '-fiche" name="_mtb_' . esc_attr( $branche ) . '_fiche"' . ( $desactiver ? ' disabled' : '' ) . '>';
	echo '<option value="">— Aucune fiche —</option>';

	foreach ( $options as $identifiant => $titre ) {
		echo '<option value="' . esc_attr( (string) $identifiant ) . '"' . selected( $fiche, (int) $identifiant, false ) . '>' . esc_html( $titre ) . '</option>';
	}

	echo '</select></p>';

	if ( $aucune_fiche ) {
		echo '<p class="description">Aucune fiche de chien n’est encore enregistrée.</p>';
	}

	echo '</div>';

	echo '<p><label>';
	echo '<input type="radio" name="_mtb_' . esc_attr( $branche ) . '_type" value="exterieur" data-branche="exterieur"';
	echo checked( $type, 'exterieur', false );
	echo '> ' . esc_html( $libelle_libre );
	echo '</label></p>';

	echo '<div class="mtb-portee-branche" data-branche="exterieur">';

	echo '<p><label for="' . esc_attr( $prefixe ) . '-nom">Nom</label><br>';
	echo '<input type="text" id="' . esc_attr( $prefixe ) . '-nom" name="_mtb_' . esc_attr( $branche ) . '_nom" value="' . esc_attr( $nom ) . '" class="regular-text"></p>';

	echo '<p><label for="' . esc_attr( $prefixe ) . '-elevage">Élevage</label><br>';
	echo '<input type="text" id="' . esc_attr( $prefixe ) . '-elevage" name="_mtb_' . esc_attr( $branche ) . '_elevage" value="' . esc_attr( $elevage ) . '" class="regular-text"></p>';

	echo '<p><label for="' . esc_attr( $prefixe ) . '-sante">' . esc_html( $libelle_sante ) . '</label><br>';
	echo '<textarea id="' . esc_attr( $prefixe ) . '-sante" name="_mtb_' . esc_attr( $branche ) . '_sante" rows="4" class="large-text">' . esc_textarea( $sante ) . '</textarea>';
	echo '<span class="description">Recopiez les résultats tels qu’ils sont écrits sur les documents, sans les reformuler.</span></p>';

	echo '</div>';
	echo '</fieldset>';
}

/**
 * Fiches de chien publiées, par ordre alphabétique de titre.
 *
 * « Type non enregistré » et « aucune fiche publiée » se traitent identiquement : la liste est
 * vide dans les deux cas, et l'écran le dit d'une phrase non technique.
 *
 * @return array<int,string> Identifiant de fiche vers titre.
 */
function options_de_fiches(): array {
	static $options = null;

	if ( is_array( $options ) ) {
		return $options;
	}

	$options = array();

	if ( ! post_type_exists( 'mtb_chien' ) ) {
		return $options;
	}

	$requete = new \WP_Query(
		array(
			'post_type'              => 'mtb_chien',
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	foreach ( $requete->posts as $fiche ) {
		if ( $fiche instanceof \WP_Post ) {
			$options[ (int) $fiche->ID ] = (string) $fiche->post_title;
		}
	}

	return $options;
}

/**
 * Boîte « Les chiots » : un tableau de rangées explicitement indexées.
 *
 * @param mixed $post Contenu en cours de modification.
 */
function boite_chiots( $post ): void {
	$post_id = identifiant_du_contenu( $post );

	$chiots = $post_id > 0 ? get_post_meta( $post_id, '_mtb_chiots', true ) : array();

	if ( ! is_array( $chiots ) ) {
		$chiots = array();
	}

	$chiots = array_values( $chiots );

	// Trois rangées vierges de secours : l'écran reste utilisable quand le JavaScript ne s'exécute pas.
	for ( $secours = 0; $secours < 3; $secours++ ) {
		$chiots[] = array();
	}

	echo '<p class="description">Les noms et les numéros arrivent souvent plusieurs semaines après la naissance : ce tableau se remplit en plusieurs fois. Les compteurs de la boîte « La portée » ne s’en déduisent jamais.</p>';

	echo '<table class="widefat" id="mtb-portee-chiots-tableau"><thead><tr>';
	echo '<th scope="col">Nom</th>';
	echo '<th scope="col">Sexe</th>';
	echo '<th scope="col">N° LOF</th>';
	echo '<th scope="col">Devenir</th>';
	echo '<th scope="col">Retirer ce chiot</th>';
	echo '</tr></thead><tbody id="mtb-portee-chiots-rangees">';

	foreach ( $chiots as $rang => $chiot ) {
		$index = (int) $rang;
		// Balisage déjà échappé par rangee_chiot().
		echo rangee_chiot( (string) $index, (string) ( $index + 1 ), is_array( $chiot ) ? $chiot : array() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	echo '</tbody></table>';

	// Balisage déjà échappé par rangee_chiot().
	echo '<template id="mtb-portee-chiot-modele">' . rangee_chiot( '__INDEX__', '__NUMERO__', array() ) . '</template>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	/*
	 * L'enveloppe « span hidden » et non l'attribut « hidden » sur le bouton lui-même : la feuille
	 * d'administration du cœur impose « display:inline-block » à .button, ce qui l'emporterait sur
	 * la règle [hidden] du navigateur et laisserait à l'écran un bouton sans effet.
	 */
	echo '<p><span class="mtb-portee-outil" hidden><button type="button" class="button" id="mtb-portee-chiot-ajouter">Ajouter un chiot</button></span></p>';
	echo '<span id="mtb-portee-chiots-annonce" class="screen-reader-text" role="status" data-annonce="Chiot ajouté."></span>';
}

/**
 * Rend une rangée de chiot.
 *
 * Les indices sont explicites — « chiots[0][nom] » et jamais « chiots[][nom] », dont les crochets
 * vides désolidariseraient les quatre sous-champs.
 *
 * @param string $index  Indice de la rangée, ou le gabarit « __INDEX__ ».
 * @param string $numero Numéro lisible de la rangée, ou le gabarit « __NUMERO__ ».
 * @param array  $chiot  Valeurs stockées de la rangée.
 *
 * @return string Balisage échappé de la rangée.
 */
function rangee_chiot( string $index, string $numero, array $chiot ): string {
	$nom     = isset( $chiot['nom'] ) && is_scalar( $chiot['nom'] ) ? (string) $chiot['nom'] : '';
	$sexe    = isset( $chiot['sexe'] ) && is_scalar( $chiot['sexe'] ) ? (string) $chiot['sexe'] : '';
	$lof     = isset( $chiot['lof'] ) && is_scalar( $chiot['lof'] ) ? (string) $chiot['lof'] : '';
	$devenir = isset( $chiot['devenir'] ) && is_scalar( $chiot['devenir'] ) ? (string) $chiot['devenir'] : '';

	$champ   = 'chiots[' . $index . ']';
	$prefixe = 'mtb-portee-chiot-' . $index;

	$html = '<tr class="mtb-portee-chiot">';

	$html .= '<td><label for="' . esc_attr( $prefixe ) . '-nom" class="screen-reader-text">Nom du chiot ' . esc_html( $numero ) . '</label>';
	$html .= '<input type="text" id="' . esc_attr( $prefixe ) . '-nom" name="' . esc_attr( $champ ) . '[nom]" value="' . esc_attr( $nom ) . '" class="regular-text"></td>';

	$html .= '<td><label for="' . esc_attr( $prefixe ) . '-sexe" class="screen-reader-text">Sexe du chiot ' . esc_html( $numero ) . '</label>';
	$html .= '<select id="' . esc_attr( $prefixe ) . '-sexe" name="' . esc_attr( $champ ) . '[sexe]">';
	$html .= '<option value="">Non renseigné</option>';

	foreach ( Champs\sexes() as $cle => $libelle ) {
		$html .= '<option value="' . esc_attr( $cle ) . '"' . selected( $sexe, $cle, false ) . '>' . esc_html( $libelle ) . '</option>';
	}

	$html .= '</select></td>';

	$html .= '<td><label for="' . esc_attr( $prefixe ) . '-lof" class="screen-reader-text">N° LOF du chiot ' . esc_html( $numero ) . '</label>';
	$html .= '<input type="text" id="' . esc_attr( $prefixe ) . '-lof" name="' . esc_attr( $champ ) . '[lof]" value="' . esc_attr( $lof ) . '" class="regular-text"></td>';

	$html .= '<td><label for="' . esc_attr( $prefixe ) . '-devenir" class="screen-reader-text">Devenir du chiot ' . esc_html( $numero ) . '</label>';
	$html .= '<input type="text" id="' . esc_attr( $prefixe ) . '-devenir" name="' . esc_attr( $champ ) . '[devenir]" value="' . esc_attr( $devenir ) . '" class="regular-text"></td>';

	$html .= '<td><label for="' . esc_attr( $prefixe ) . '-retirer" class="screen-reader-text">Retirer le chiot ' . esc_html( $numero ) . '</label>';
	$html .= '<input type="checkbox" id="' . esc_attr( $prefixe ) . '-retirer" name="' . esc_attr( $champ ) . '[retirer]" value="1"></td>';

	$html .= '</tr>';

	return $html;
}

/**
 * Boîte « Galerie photos » : une liste ordonnée d'identifiants de photos.
 *
 * @param mixed $post Contenu en cours de modification.
 */
function boite_galerie( $post ): void {
	$post_id = identifiant_du_contenu( $post );

	$photos = $post_id > 0 ? get_post_meta( $post_id, '_mtb_galerie', true ) : array();

	if ( ! is_array( $photos ) ) {
		$photos = array();
	}

	echo '<p class="description">Les photos s’affichent sur le site dans l’ordre de cette liste. Retirer une photo d’ici ne la supprime jamais du site.</p>';

	echo '<ul id="mtb-portee-galerie-liste" class="mtb-portee-galerie">';

	$rang = 0;

	foreach ( $photos as $valeur ) {
		$identifiant = is_scalar( $valeur ) ? absint( $valeur ) : 0;

		if ( 0 === $identifiant ) {
			continue;
		}

		++$rang;

		// Balisage déjà échappé par element_galerie().
		echo element_galerie( (string) $identifiant, (string) $rang, apercu_photo( $identifiant ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	echo '</ul>';

	// Balisage déjà échappé par element_galerie().
	echo '<template id="mtb-portee-galerie-modele">' . element_galerie( '__ID__', '__NUMERO__', '<img src="" alt="">' ) . '</template>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	echo '<p><span class="mtb-portee-outil" hidden><button type="button" class="button" id="mtb-portee-galerie-ajouter">Ajouter des photos</button></span></p>';
}

/**
 * Boîte « Adresse de la page ».
 *
 * Le champ porte le nom que le cœur attend, « post_name » : c'est lui qui applique sanitize_title(),
 * garantit l'unicité de l'adresse et la régénère depuis l'identifiant quand le champ est laissé
 * vide. Écrire cette valeur nous-mêmes doublerait un travail déjà fait, et mal.
 *
 * @param mixed $post Contenu en cours de modification.
 */
function boite_adresse( $post ): void {
	/*
	 * urldecode() est un filet pour un cas de bord, pas un rattrapage courant : une saisie faite
	 * depuis cet écran ne produit jamais d'adresse encodée, sanitize_title() appliquant
	 * remove_accents() en contexte « save » — « Élia » y devient « elia », jamais « %C3%89lia ».
	 * L'appel ne sert qu'à une adresse déjà encodée arrivée par une autre voie que l'écran de
	 * saisie, où il affiche la forme lisible plutôt que la forme encodée. Comportement mesuré dans
	 * la stack sur une adresse cyrillique posée directement en base.
	 */
	$adresse = $post instanceof \WP_Post ? urldecode( (string) $post->post_name ) : '';

	echo '<p><label for="mtb-portee-adresse">Adresse de la page</label><br>';
	echo '<input type="text" id="mtb-portee-adresse" name="post_name" value="' . esc_attr( $adresse ) . '" class="regular-text"></p>';
	echo '<p class="description">L’adresse de cette portée sur le site. Elle se remplit toute seule à partir de l’identifiant ; ne la modifiez que si l’adresse est fausse.</p>';
}

/**
 * Rend l'aperçu d'une photo de la galerie.
 *
 * @param int $identifiant Identifiant du fichier joint.
 *
 * @return string Balisage échappé de l'aperçu.
 */
function apercu_photo( int $identifiant ): string {
	$fichier = get_post( $identifiant );

	if ( $fichier instanceof \WP_Post && 'attachment' === $fichier->post_type ) {
		$image = wp_get_attachment_image( $identifiant, 'thumbnail' );

		if ( '' !== $image ) {
			return $image;
		}
	}

	return '<span class="mtb-portee-photo-absente">Photo introuvable</span>';
}

/**
 * Rend un élément de la galerie.
 *
 * @param string $identifiant Identifiant du fichier joint, ou le gabarit « __ID__ ».
 * @param string $numero      Rang lisible de la photo, ou le gabarit « __NUMERO__ ».
 * @param string $apercu      Balisage déjà échappé de l'aperçu.
 *
 * @return string Balisage échappé de l'élément.
 */
function element_galerie( string $identifiant, string $numero, string $apercu ): string {
	$prefixe = 'mtb-portee-photo-' . $identifiant;

	$html  = '<li class="mtb-portee-photo">';
	// Le gabarit accompagne la mention : le script y réécrit le rang sans jamais composer la phrase.
	$html .= '<span class="mtb-portee-photo-rang" data-libelle-rang="Photo __NUMERO__">Photo ' . esc_html( $numero ) . '</span> ';
	$html .= $apercu;
	$html .= '<input type="hidden" name="_mtb_galerie[]" value="' . esc_attr( $identifiant ) . '">';
	$html .= ' <label for="' . esc_attr( $prefixe ) . '-retirer">';
	$html .= '<input type="checkbox" id="' . esc_attr( $prefixe ) . '-retirer" name="_mtb_galerie_retirer[]" value="' . esc_attr( $identifiant ) . '"> ';
	$html .= action_galerie( 'span', 'Retirer la photo', $numero, '' );
	$html .= '</label> ';
	$html .= '<span class="mtb-portee-outil" hidden>';
	$html .= action_galerie( 'button', 'Monter la photo', $numero, 'monter' ) . ' ';
	$html .= action_galerie( 'button', 'Descendre la photo', $numero, 'descendre' );
	$html .= '</span>';
	$html .= '</li>';

	return $html;
}

/**
 * Rend une action de la galerie, libellée par le rang de la photo.
 *
 * Trois boutons « Monter » identiques ne disent pas à un lecteur d'écran *quoi* monter : chaque
 * action porte donc le rang de sa photo. Le texte visible et le nom accessible sont la même chaîne,
 * il n'y a rien à tenir en double.
 *
 * Le rang voyage aussi en gabarit dans « data-libelle-rang » : la liste étant renumérotée à chaque
 * ajout, déplacement ou retrait, le script y réécrit le libellé depuis ce gabarit et le rang ne peut
 * pas dériver. La phrase entière reste écrite ici, en français, jamais composée par le script.
 *
 * @param string $balise  « button » ou « span ».
 * @param string $verbe   Début du libellé, sans le rang.
 * @param string $numero  Rang lisible de la photo, ou le gabarit « __NUMERO__ ».
 * @param string $sens    « monter », « descendre », ou chaîne vide pour une action sans déplacement.
 *
 * @return string Balisage échappé de l'action.
 */
function action_galerie( string $balise, string $verbe, string $numero, string $sens ): string {
	$gabarit = $verbe . ' __NUMERO__';
	$libelle = $verbe . ' ' . $numero;

	$attributs = ' data-libelle-rang="' . esc_attr( $gabarit ) . '"';

	if ( 'button' === $balise ) {
		return '<button type="button" class="button" data-deplacer="' . esc_attr( $sens ) . '"' . $attributs . '>' . esc_html( $libelle ) . '</button>';
	}

	return '<span class="mtb-portee-photo-action"' . $attributs . '>' . esc_html( $libelle ) . '</span>';
}
