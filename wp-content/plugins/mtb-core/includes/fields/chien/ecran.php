<?php
/**
 * Écran de saisie de la fiche Chien : rendu des sections et des champs.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Fields\Chien;

use function MTB\Core\Content\Chien\cadrage_par_defaut;
use function MTB\Core\Content\Chien\cadrages;
use function MTB\Core\Content\Chien\champs_sante;
use function MTB\Core\Content\Chien\champs_titres;
use function MTB\Core\Content\Chien\non_renseigne;
use function MTB\Core\Content\Chien\oui_non;
use function MTB\Core\Content\Chien\sexes;
use function MTB\Core\Content\Chien\statuts;
use function MTB\Core\Content\Chien\varietes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Valeur enregistrée d'un champ, ramenée à une chaîne.
 *
 * @param int    $post_id Identifiant de la fiche.
 * @param string $cle     Clé du champ.
 */
function valeur_enregistree( int $post_id, string $cle ): string {
	$valeur = get_post_meta( $post_id, $cle, true );

	return is_scalar( $valeur ) ? (string) $valeur : '';
}

/**
 * Identifiant HTML dérivé d'une clé de champ.
 *
 * @param string $cle Clé du champ.
 */
function identifiant_html( string $cle ): string {
	return 'mtb-champ-' . str_replace( '_', '-', ltrim( $cle, '_' ) );
}

/**
 * Ligne d'aide sous un champ.
 *
 * @param string $identifiant Identifiant HTML du champ.
 * @param string $aide        Phrase d'aide, vide pour ne rien afficher.
 */
function rendre_aide( string $identifiant, string $aide ): void {
	if ( '' === $aide ) {
		return;
	}

	printf(
		'<p class="description mtb-champ__aide" id="%1$s">%2$s</p>',
		esc_attr( $identifiant . '-aide' ),
		esc_html( $aide )
	);
}

/**
 * Champ de saisie sur une ligne.
 *
 * @param string $cle     Clé du champ.
 * @param string $libelle Libellé affiché.
 * @param string $valeur  Valeur enregistrée.
 * @param string $aide    Phrase d'aide.
 * @param string $type    Type de contrôle HTML.
 */
function rendre_champ_texte( string $cle, string $libelle, string $valeur, string $aide = '', string $type = 'text' ): void {
	$identifiant = identifiant_html( $cle );

	echo '<div class="mtb-champ">';

	printf(
		'<label class="mtb-champ__etiquette" for="%1$s"><strong>%2$s</strong></label>',
		esc_attr( $identifiant ),
		esc_html( $libelle )
	);

	printf(
		'<input type="%1$s" class="widefat" id="%2$s" name="%3$s" value="%4$s"%5$s />',
		esc_attr( $type ),
		esc_attr( $identifiant ),
		esc_attr( $cle ),
		esc_attr( $valeur ),
		'' === $aide ? '' : ' aria-describedby="' . esc_attr( $identifiant . '-aide' ) . '"'
	);

	rendre_aide( $identifiant, $aide );

	echo '</div>';
}

/**
 * Zone de texte, une ligne par entrée.
 *
 * @param string $cle     Clé du champ.
 * @param string $libelle Libellé affiché.
 * @param string $valeur  Valeur enregistrée.
 * @param string $aide    Phrase d'aide.
 */
function rendre_zone_texte( string $cle, string $libelle, string $valeur, string $aide = '' ): void {
	$identifiant = identifiant_html( $cle );

	echo '<div class="mtb-champ">';

	printf(
		'<label class="mtb-champ__etiquette" for="%1$s"><strong>%2$s</strong></label>',
		esc_attr( $identifiant ),
		esc_html( $libelle )
	);

	printf(
		'<textarea class="widefat" rows="4" id="%1$s" name="%2$s"%3$s>%4$s</textarea>',
		esc_attr( $identifiant ),
		esc_attr( $cle ),
		'' === $aide ? '' : ' aria-describedby="' . esc_attr( $identifiant . '-aide' ) . '"',
		esc_textarea( $valeur )
	);

	rendre_aide( $identifiant, $aide );

	echo '</div>';
}

/**
 * Groupe de boutons radio, précédé de l'option « Non renseigné ».
 *
 * @param string                $cle     Clé du champ.
 * @param string                $libelle Libellé affiché.
 * @param array<string, string> $options Liste fermée.
 * @param string                $valeur  Valeur enregistrée.
 * @param string                $aide    Phrase d'aide.
 */
function rendre_radios( string $cle, string $libelle, array $options, string $valeur, string $aide = '' ): void {
	$identifiant = identifiant_html( $cle );
	$choix       = array_merge( array( '' => non_renseigne() ), $options );

	printf( '<fieldset class="mtb-champ" id="%s">', esc_attr( $identifiant ) );
	printf( '<legend class="mtb-champ__etiquette"><strong>%s</strong></legend>', esc_html( $libelle ) );

	foreach ( $choix as $option => $etiquette ) {
		printf(
			'<label class="mtb-champ__choix"><input type="radio" name="%1$s" value="%2$s"%3$s /> %4$s</label>',
			esc_attr( $cle ),
			esc_attr( (string) $option ),
			checked( $valeur, (string) $option, false ),
			esc_html( $etiquette )
		);
	}

	rendre_aide( $identifiant, $aide );

	echo '</fieldset>';
}

/**
 * Groupe de boutons radio du statut : les libellés portent leurs deux formes, et le script
 * d'écran échange le texte quand le sexe change.
 *
 * Sans JavaScript, la forme masculine canonique reste affichée et la ligne d'aide dit exactement
 * ce qui se passera sur le site : rien ne casse et rien ne ment.
 *
 * @param string $valeur Valeur enregistrée.
 * @param string $sexe   Sexe enregistré.
 */
function rendre_statut( string $valeur, string $sexe ): void {
	$identifiant = identifiant_html( '_mtb_statut' );
	$feminin     = 'femelle' === $sexe;

	printf( '<fieldset class="mtb-champ" id="%s">', esc_attr( $identifiant ) );
	echo '<legend class="mtb-champ__etiquette"><strong>Statut</strong></legend>';

	printf(
		'<label class="mtb-champ__choix"><input type="radio" name="_mtb_statut" value=""%1$s /> %2$s</label>',
		checked( $valeur, '', false ),
		esc_html( non_renseigne() )
	);

	foreach ( statuts() as $cle => $formes ) {
		printf(
			'<label class="mtb-champ__choix"><input type="radio" name="_mtb_statut" value="%1$s"%2$s /> <span class="mtb-champ__libelle-accorde" data-mtb-masculin="%3$s" data-mtb-feminin="%4$s">%5$s</span></label>',
			esc_attr( (string) $cle ),
			checked( $valeur, (string) $cle, false ),
			esc_attr( $formes['masculin'] ),
			esc_attr( $formes['feminin'] ),
			esc_html( $feminin ? $formes['feminin'] : $formes['masculin'] )
		);
	}

	rendre_aide(
		$identifiant,
		"Sur le site, ce libellé s'accorde au sexe du chien : une femelle reproductrice s'affiche « Reproductrice ». Sans statut, le chien n'apparaît pas sur la page « La meute »."
	);

	echo '</fieldset>';
}

/**
 * Liste déroulante d'une liste fermée.
 *
 * @param string                $cle          Clé du champ.
 * @param string                $libelle      Libellé affiché.
 * @param array<string, string> $options      Liste fermée.
 * @param string                $valeur       Valeur enregistrée.
 * @param string                $aide         Phrase d'aide.
 * @param string                $premier_vide Libellé de la première option, vide pour ne pas en
 *                                            proposer.
 */
function rendre_liste( string $cle, string $libelle, array $options, string $valeur, string $aide = '', string $premier_vide = '' ): void {
	$identifiant = identifiant_html( $cle );

	echo '<div class="mtb-champ">';

	printf(
		'<label class="mtb-champ__etiquette" for="%1$s"><strong>%2$s</strong></label>',
		esc_attr( $identifiant ),
		esc_html( $libelle )
	);

	printf(
		'<select class="widefat" id="%1$s" name="%2$s"%3$s>',
		esc_attr( $identifiant ),
		esc_attr( $cle ),
		'' === $aide ? '' : ' aria-describedby="' . esc_attr( $identifiant . '-aide' ) . '"'
	);

	if ( '' !== $premier_vide ) {
		printf(
			'<option value=""%1$s>%2$s</option>',
			selected( $valeur, '', false ),
			esc_html( $premier_vide )
		);
	}

	foreach ( $options as $option => $etiquette ) {
		printf(
			'<option value="%1$s"%2$s>%3$s</option>',
			esc_attr( (string) $option ),
			selected( $valeur, (string) $option, false ),
			esc_html( $etiquette )
		);
	}

	echo '</select>';

	rendre_aide( $identifiant, $aide );

	echo '</div>';
}

/**
 * Intitulé de la zone de contenu.
 *
 * L'éditeur classique ne pose aucun intitulé au-dessus de la zone de contenu : sans cette ligne,
 * l'éleveuse voit une grande boîte de texte anonyme et ne peut pas deviner ce qu'on attend
 * dedans. Le libellé « Commentaire de l'éleveuse » est gelé, encore faut-il qu'il atteigne
 * l'écran. Le niveau de titre est celui des sections, pour que la navigation par titres reste
 * cohérente d'un bout à l'autre de la page.
 *
 * @param mixed $post Fiche en cours d'édition.
 */
function rendre_titre_du_commentaire( $post ): void {
	if ( ! $post instanceof \WP_Post || 'mtb_chien' !== $post->post_type ) {
		return;
	}

	echo '<div class="mtb-champ mtb-champ--commentaire">';
	echo '<h2 class="mtb-champ__etiquette">Commentaire de l\'éleveuse</h2>';
	echo '<p class="description mtb-champ__aide">Écrivez ici le texte libre de la fiche : ce que vous souhaitez dire de ce chien. Il s\'affiche sur sa fiche, sur le site.</p>';
	echo '</div>';
}

/**
 * Boîte « Adresse de la page », en remplacement de celle du cœur.
 *
 * Le champ porte le nom attendu par WordPress, « post_name » : c'est le cœur qui applique
 * sanitize_title(), garantit l'unicité de l'adresse et la régénère depuis le nom d'usage quand le
 * champ est laissé vide. Refaire ce travail nous-mêmes le referait moins bien — et c'est pourquoi
 * ce champ ne passe surtout pas par notre routine de sauvegarde : l'adresse appartient au contenu,
 * pas aux champs de la fiche.
 *
 * @param mixed $post Fiche en cours d'édition.
 */
function rendre_adresse( $post ): void {
	// Sans urldecode(), une adresse accentuée s'afficherait sous sa forme encodée, illisible.
	$adresse = $post instanceof \WP_Post ? urldecode( (string) $post->post_name ) : '';

	rendre_champ_texte(
		'post_name',
		'Adresse de la page',
		$adresse,
		"L'adresse de cette fiche sur le site. Elle se remplit toute seule à partir du nom d'usage ; ne la modifiez que si l'adresse est fausse."
	);
}

/**
 * Section « Identité ».
 *
 * @param \WP_Post $post Fiche en cours d'édition.
 */
function rendre_identite( \WP_Post $post ): void {
	$id = (int) $post->ID;

	/*
	 * Nonce posé ici, dans la première section : une section repliée par l'éleveuse reste présente
	 * dans la page, donc le champ est toujours envoyé. Sans lui, la sauvegarde ne touche à rien —
	 * c'est ce qui protège la fiche d'une modification rapide depuis la liste ou d'un appel d'API.
	 */
	wp_nonce_field( 'mtb_chien_fiche', 'mtb_chien_nonce' );

	rendre_champ_texte(
		'_mtb_nom_complet',
		'Nom complet (avec affixe)',
		valeur_enregistree( $id, '_mtb_nom_complet' ),
		"Le nom complet inscrit au LOF, affixe compris. Le nom d'usage, lui, se saisit tout en haut de la page."
	);

	rendre_radios(
		'_mtb_sexe',
		'Sexe',
		sexes(),
		valeur_enregistree( $id, '_mtb_sexe' ),
		"Le sexe sert aussi à accorder les libellés affichés sur le site : « Né le » ou « Née le », « Reproducteur » ou « Reproductrice »."
	);

	rendre_radios(
		'_mtb_variete',
		'Variété',
		varietes(),
		valeur_enregistree( $id, '_mtb_variete' )
	);

	rendre_champ_texte(
		'_mtb_date_naissance',
		'Date de naissance',
		valeur_enregistree( $id, '_mtb_date_naissance' ),
		"Si vous ne connaissez que l'année, laissez le champ vide : le site affichera « Non renseigné » plutôt qu'une date approximative.",
		'date'
	);

	rendre_champ_texte(
		'_mtb_date_deces',
		'Date de décès',
		valeur_enregistree( $id, '_mtb_date_deces' ),
		"À remplir seulement si le chien est décédé. Laissé vide, rien n'apparaît sur le site.",
		'date'
	);

	rendre_statut( valeur_enregistree( $id, '_mtb_statut' ), valeur_enregistree( $id, '_mtb_sexe' ) );
}

/**
 * Section « Taille et robe ».
 *
 * @param \WP_Post $post Fiche en cours d'édition.
 */
function rendre_taille_robe( \WP_Post $post ): void {
	$id = (int) $post->ID;

	$champs = array(
		'_mtb_taille'         => array( 'Taille', 'Recopiez la taille telle quelle, avec son unité.' ),
		'_mtb_couleur'        => array( 'Couleur', 'Recopiez la couleur telle quelle.' ),
		'_mtb_masque'         => array( 'Masque', 'Recopiez la mention telle quelle.' ),
		'_mtb_genetique_robe' => array( 'Génétique de robe', 'Recopiez la formule telle quelle : rien ne sera corrigé ni mis en majuscules.' ),
	);

	foreach ( $champs as $cle => $champ ) {
		rendre_champ_texte( $cle, $champ[0], valeur_enregistree( $id, $cle ), $champ[1] );
	}
}

/**
 * Section « Santé ».
 *
 * @param \WP_Post $post Fiche en cours d'édition.
 */
function rendre_sante( \WP_Post $post ): void {
	$id = (int) $post->ID;

	foreach ( champs_sante() as $champ ) {
		$valeur = valeur_enregistree( $id, $champ['cle'] );
		$aide   = isset( $champ['aide'] ) ? $champ['aide'] : '';

		if ( isset( $champ['liste'] ) ) {
			rendre_radios( $champ['cle'], $champ['saisie'], oui_non(), $valeur, $aide );

			continue;
		}

		rendre_champ_texte( $champ['cle'], $champ['saisie'], $valeur, $aide );
	}

	rendre_zone_texte(
		'_mtb_autres_tests',
		'Autres tests de santé',
		valeur_enregistree( $id, '_mtb_autres_tests' ),
		'Une ligne par test : son nom, puis son résultat. À utiliser pour tout test qui n\'a pas son champ ci-dessus.'
	);
}

/**
 * Section « Titres et brevets ».
 *
 * @param \WP_Post $post Fiche en cours d'édition.
 */
function rendre_titres( \WP_Post $post ): void {
	$id = (int) $post->ID;

	foreach ( champs_titres() as $champ ) {
		rendre_champ_texte(
			$champ['cle'],
			$champ['saisie'],
			valeur_enregistree( $id, $champ['cle'] ),
			isset( $champ['aide'] ) ? $champ['aide'] : ''
		);
	}

	rendre_zone_texte(
		'_mtb_autres_titres',
		'Autres titres et brevets',
		valeur_enregistree( $id, '_mtb_autres_titres' ),
		"Une ligne par titre. Les résultats d'exposition se notent ici ; les résultats de travail, eux, se saisissent dans « Résultats de travail »."
	);
}

/**
 * Fiches Chien proposées en père ou en mère, la fiche courante exclue.
 *
 * @param int $exclu Identifiant de la fiche courante.
 *
 * @return array<int, string> Identifiant => libellé de l'option.
 */
function fiches_proposees( int $exclu ): array {
	$fiches = get_posts(
		array(
			'post_type'        => 'mtb_chien',
			'post_status'      => array( 'publish', 'draft', 'pending', 'private', 'future' ),
			'numberposts'      => -1,
			'orderby'          => 'title',
			'order'            => 'ASC',
			'exclude'          => array( $exclu ),
			'suppress_filters' => false,
		)
	);

	$options = array();

	foreach ( $fiches as $fiche ) {
		$nom_complet = valeur_enregistree( (int) $fiche->ID, '_mtb_nom_complet' );
		$etiquette   = (string) $fiche->post_title;

		if ( '' !== $nom_complet && $nom_complet !== $etiquette ) {
			$etiquette .= ' (' . $nom_complet . ')';
		}

		$mention = mention_de_statut( (string) $fiche->post_status );

		if ( '' !== $mention ) {
			$etiquette .= ' — ' . $mention;
		}

		$options[ (int) $fiche->ID ] = $etiquette;
	}

	return $options;
}

/**
 * Mention accolée au libellé d'une fiche qui n'est pas encore publiée.
 *
 * Une fiche en préparation reste proposée — préparer le père en brouillon puis le publier est un
 * enchaînement normal — mais la liste doit dire ce qu'elle propose : sur le site, le lien vers un
 * parent ne s'affiche que si sa fiche est publiée. Sans cette mention, le choix serait fait
 * correctement et il ne se passerait rien, sans un mot d'explication.
 *
 * Les mentions sont écrites en français littéral plutôt que reprises des libellés de WordPress :
 * elles restent françaises même si le site tourne dans une autre langue, et elles s'accordent avec
 * « fiche ».
 *
 * @param string $statut État de publication de la fiche.
 *
 * @return string Mention à accoler, vide pour une fiche publiée.
 */
function mention_de_statut( string $statut ): string {
	$mentions = array(
		'draft'   => 'brouillon',
		'pending' => 'en attente de relecture',
		'private' => 'privée',
		'future'  => 'planifiée',
	);

	return isset( $mentions[ $statut ] ) ? $mentions[ $statut ] : '';
}

/**
 * Un parent : la fiche quand elle existe, sinon le nom libre et l'élevage.
 *
 * @param \WP_Post $post      Fiche en cours d'édition.
 * @param string   $role      « pere » ou « mere ».
 * @param string   $libelle   Libellé du parent.
 * @param string   $exterieur Libellé du repli hors élevage.
 */
function rendre_parent( \WP_Post $post, string $role, string $libelle, string $exterieur ): void {
	$id      = (int) $post->ID;
	$choisi  = valeur_enregistree( $id, '_mtb_' . $role . '_fiche' );
	$options = array();

	foreach ( fiches_proposees( $id ) as $fiche_id => $etiquette ) {
		$options[ (string) $fiche_id ] = $etiquette;
	}

	rendre_liste(
		'_mtb_' . $role . '_fiche',
		$libelle,
		$options,
		'0' === $choisi ? '' : $choisi,
		"Choisissez la fiche si ce chien a la sienne sur le site : le lien entre les deux fiches se fera tout seul. Tant que la fiche du parent n'est pas publiée, le lien ne s'affiche pas sur le site. Quand une fiche est choisie, c'est elle qui s'affiche : les champs Nom et Élevage ci-dessous ne servent que si aucune fiche n'est sélectionnée.",
		non_renseigne()
	);

	printf( '<fieldset class="mtb-champ"><legend class="mtb-champ__etiquette"><strong>%s</strong></legend>', esc_html( $exterieur ) );

	rendre_champ_texte(
		'_mtb_' . $role . '_nom',
		'Nom',
		valeur_enregistree( $id, '_mtb_' . $role . '_nom' ),
		"À remplir seulement quand ce parent n'a pas de fiche sur le site."
	);

	rendre_champ_texte(
		'_mtb_' . $role . '_elevage',
		'Élevage',
		valeur_enregistree( $id, '_mtb_' . $role . '_elevage' )
	);

	echo '</fieldset>';
}

/**
 * Section « Parents ».
 *
 * @param \WP_Post $post Fiche en cours d'édition.
 */
function rendre_parents( \WP_Post $post ): void {
	rendre_parent( $post, 'pere', 'Père', 'Père — étalon extérieur' );
	rendre_parent( $post, 'mere', 'Mère', 'Mère — hors élevage' );
}

/**
 * Section « Photos et pedigree ».
 *
 * @param \WP_Post $post Fiche en cours d'édition.
 */
function rendre_photos( \WP_Post $post ): void {
	$id      = (int) $post->ID;
	$cadrage = valeur_enregistree( $id, '_mtb_cadrage' );

	echo '<p class="description mtb-champ__aide">La photo principale se choisit dans la colonne de droite, dans l\'encadré « Photo principale ».</p>';

	rendre_liste(
		'_mtb_cadrage',
		'Cadrage de la photo',
		cadrages(),
		'' === $cadrage ? cadrage_par_defaut() : $cadrage,
		"Indiquez la zone de la photo à garder visible quand elle est recadrée. « Centre » est le cadrage par défaut : il garde la tête du chien, un peu au-dessus du milieu de la photo."
	);

	rendre_galerie( $id );

	rendre_champ_texte(
		'_mtb_pedigree',
		'Lien pedigree (LOF Select)',
		valeur_enregistree( $id, '_mtb_pedigree' ),
		"Collez l'adresse de la page du chien sur LOF Select, copiée depuis la barre du navigateur.",
		'url'
	);
}

/**
 * Galerie photos : la liste enregistrée est rendue par le serveur, la modale média du cœur sert à
 * la modifier.
 *
 * @param int $post_id Identifiant de la fiche.
 */
function rendre_galerie( int $post_id ): void {
	$liste  = valeur_enregistree( $post_id, '_mtb_galerie' );
	$photos = '' === $liste ? array() : explode( ',', $liste );

	echo '<div class="mtb-champ mtb-galerie" id="mtb-chien-galerie">';
	echo '<p class="mtb-champ__etiquette"><strong>Galerie photos</strong></p>';
	echo '<ul class="mtb-galerie__liste">';

	/*
	 * Les photos encore présentes sont retenues avant d'être rendues : il faut connaître leur
	 * nombre pour savoir laquelle est la dernière. Le rang reste compté sur les photos réellement
	 * rendues, jamais sur les identifiants stockés — une photo supprimée de la bibliothèque ne doit
	 * pas laisser de trou dans la numérotation annoncée à l'écran.
	 */
	$retenues = array();

	foreach ( $photos as $morceau ) {
		$photo_id = (int) trim( $morceau );

		if ( $photo_id <= 0 || 'attachment' !== get_post_type( $photo_id ) ) {
			continue;
		}

		$retenues[] = $photo_id;
	}

	$total = count( $retenues );

	foreach ( $retenues as $index => $photo_id ) {
		$rang = $index + 1;

		printf( '<li class="mtb-galerie__photo" data-mtb-photo="%d">', $photo_id );

		echo wp_kses_post( wp_get_attachment_image( $photo_id, 'thumbnail' ) );

		/*
		 * Chaque bouton porte le rang de sa photo : trois boutons par photo tous nommés « Retirer »
		 * sont indistinguables à la tabulation comme au lecteur d'écran. Le libellé visible est
		 * complet, donc le nom accessible et le texte lu à voix haute sont le même.
		 *
		 * Aux bornes de la liste, le bouton sans effet est désactivé plutôt que retiré : le nombre
		 * de boutons ne varie pas d'une ligne à l'autre, le parcours au clavier reste régulier, le
		 * contrôle sort tout seul de l'ordre de tabulation et le lecteur d'écran l'annonce comme
		 * indisponible. Le bouton existe, il n'est simplement pas utilisable ici.
		 */
		printf(
			'<button type="button" class="button-link mtb-galerie__retirer">%s</button>',
			esc_html( sprintf( 'Retirer la photo %d', $rang ) )
		);
		printf(
			'<button type="button" class="button-link mtb-galerie__avant"%1$s>%2$s</button>',
			1 === $rang ? ' disabled="disabled"' : '',
			esc_html( sprintf( 'Monter la photo %d', $rang ) )
		);
		printf(
			'<button type="button" class="button-link mtb-galerie__apres"%1$s>%2$s</button>',
			$rang === $total ? ' disabled="disabled"' : '',
			esc_html( sprintf( 'Descendre la photo %d', $rang ) )
		);

		echo '</li>';
	}

	echo '</ul>';

	printf(
		'<input type="hidden" id="%1$s" name="_mtb_galerie" value="%2$s" />',
		esc_attr( identifiant_html( '_mtb_galerie' ) ),
		esc_attr( $liste )
	);

	echo '<button type="button" class="button mtb-galerie__ajouter">Ajouter des photos</button>';
	echo '<p class="description mtb-champ__aide">Les photos s\'affichent sur la fiche dans l\'ordre de cette liste. Pensez à décrire chaque photo dans la fenêtre des photos, pour les personnes aveugles.</p>';
	echo '</div>';
}
