<?php
/**
 * Les trois filtres des listes d'administration, et la neutralisation de la liste des mois.
 *
 * Le filtre s'applique par liste d'identifiants, jamais par une clause sur un champ : un seul
 * mécanisme sert l'ordre et le filtre, donc une seule chose à vérifier, et aucun contenu dépourvu
 * du champ ne peut être escamoté par une jointure.
 *
 * Une valeur de filtre invalide est ignorée, jamais réparée : l'écran revient à « toutes », et la
 * liste déroulante retombe sur sa première option, ce qui rend l'état lisible d'un coup d'œil.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Admin\Listes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Neutralise la liste déroulante des mois sur les trois listes.
 *
 * Elle filtre sur la date de publication, qui vaut l'horodatage de la reprise de l'ancien site sur
 * cent cinq contenus : elle n'y dit donc rien de vrai sur l'élevage. Sur la liste des portées, elle
 * entrerait de surcroît en collision avec le filtre « Année » — deux listes voisines qui semblent
 * toutes deux filtrer par année, l'une sur la naissance, l'autre sur la publication. C'est un
 * filtre qu'elle peut déclencher et qui cache du contenu sur un critère qu'elle ne comprend pas.
 *
 * Le premier paramètre n'est pas typé : un filtre tiers peut avoir rendu autre chose qu'un booléen,
 * et strict_types en ferait une erreur fatale.
 *
 * @param mixed  $desactiver Décision déjà prise en amont.
 * @param string $type       Nom du type de contenu de l'écran.
 *
 * @return mixed Vrai pour nos trois listes, la décision reçue partout ailleurs.
 */
function desactiver_liste_des_mois( $desactiver, string $type ) {
	return null === description( $type ) ? $desactiver : true;
}

/**
 * Imprime la liste déroulante de filtre de l'écran en cours.
 *
 * Le cœur tamponne la sortie de ce crochet et n'imprime son bouton « Filtrer » que si le tampon
 * n'est pas vide : la liste des mois étant neutralisée, c'est notre liste déroulante qui le fait
 * apparaître. Le libellé du bouton vient du cœur, on ne l'écrit pas.
 *
 * Le balisage est exactement celui de la liste des mois qu'il remplace — une étiquette masquée liée
 * par « for » et « id » —, parce qu'un module qui remplace un contrôle du cœur hérite de ses
 * obligations d'accessibilité. Sans elle, un lecteur d'écran annoncerait une liste déroulante sans
 * nom là où il en annonçait une nommée : une régression que ce module aurait lui-même introduite.
 * « screen-reader-text » est une classe du cœur ; aucune feuille de style n'est en jeu.
 *
 * @param string $type  Nom du type de contenu de l'écran.
 * @param string $which Emplacement de la barre : « top », « bottom » ou « bar ».
 */
function afficher_filtre( string $type, string $which ): void {
	// Le cœur ne déclenche ce crochet qu'en haut de la liste des contenus ; « bar » vient des photos.
	if ( 'top' !== $which ) {
		return;
	}

	$description = description( $type );

	if ( null === $description ) {
		return;
	}

	$parametre = $description['filtre']['parametre'];
	$options   = options_de_filtre( $type );
	$courante  = filtre_actif( $type );

	/*
	 * Un seul de ces contrôles par écran, donc un identifiant fixe suffit ; il est préfixé pour ne
	 * pouvoir entrer en collision avec aucun de ceux du cœur (« filter-by-date »,
	 * « bulk-action-selector-top »…).
	 */
	$identifiant = 'mtb-filtre-' . $parametre;

	echo '<label class="screen-reader-text" for="' . esc_attr( $identifiant ) . '">';
	echo esc_html( $description['filtre']['libelle_accessible'] );
	echo '</label>';
	echo '<select name="' . esc_attr( $parametre ) . '" id="' . esc_attr( $identifiant ) . '">';
	echo '<option value="">' . esc_html( $description['filtre']['libelle_toutes'] ) . '</option>';

	foreach ( $options as $valeur => $libelle ) {
		echo '<option value="' . esc_attr( (string) $valeur ) . '"' . selected( $courante, (string) $valeur, false ) . '>';
		echo esc_html( $libelle );
		echo '</option>';
	}

	echo '</select>';
}

/**
 * Options proposées par le filtre d'un type, dans leur ordre d'affichage.
 *
 * Les années et les disciplines orphelines se déduisent des contenus existants, jamais d'une plage
 * codée en dur, et elles viennent du même balayage que l'ordre — donc de la même portée d'écran. On
 * ne propose ainsi jamais un filtre qui ne mène nulle part.
 *
 * @param string $type Nom du type de contenu.
 *
 * @return array<string, string> Valeur à poser dans l'URL vers libellé affiché.
 */
function options_de_filtre( string $type ): array {
	if ( 'mtb_portee' === $type ) {
		return annees_presentes( $type );
	}

	if ( 'mtb_chien' === $type ) {
		return statuts_au_pluriel();
	}

	return disciplines_proposees( $type );
}

/**
 * Années de naissance réellement présentes, de la plus récente à la plus ancienne.
 *
 * @param string $type Nom du type de contenu.
 *
 * @return array<string, string> Année vers elle-même : une année s'imprime en chiffres bruts.
 */
function annees_presentes( string $type ): array {
	$balayage = balayage( $type );
	$annees   = array();

	foreach ( $balayage['valeurs'] as $valeur ) {
		if ( 1 === preg_match( '/^\d{4}$/', $valeur ) ) {
			$annees[ $valeur ] = $valeur;
		}
	}

	krsort( $annees, SORT_STRING );

	return $annees;
}

/**
 * Les quatre statuts, dans l'ordre gelé de la liste fermée, au pluriel.
 *
 * Un filtre nomme un groupe : c'est exactement le couple déjà en place côté public, titre de groupe
 * au pluriel et fiche au singulier accordé. Elle choisira donc « Retraités » dans le filtre et lira
 * « Retraitée » dans la colonne — les deux formes sont gelées à la même source, ce n'est pas une
 * divergence. L'ordre est celui du fichier de vocabulaire, jamais l'alphabétique : un tri sur les
 * clés stockées détruirait l'ordre d'affichage que la page « La meute » emploie déjà.
 *
 * @return array<string, string> Clé de statut vers titre de groupe.
 */
function statuts_au_pluriel(): array {
	$options = array();

	foreach ( array_keys( \MTB\Core\Content\Chien\statuts() ) as $cle ) {
		$options[ $cle ] = \MTB\Core\Content\Chien\libelle_statut_pluriel( (string) $cle );
	}

	return $options;
}

/**
 * Les neuf disciplines dans l'ordre gelé, puis les clés orphelines réellement présentes.
 *
 * Une valeur orpheline garde sa clé brute pour libellé : elle n'est pas destinée à durer, mais tant
 * qu'elle existe elle doit être atteignable. Rien ne disparaît, ni de la liste, ni du filtre.
 *
 * @param string $type Nom du type de contenu.
 *
 * @return array<string, string> Clé de discipline vers libellé.
 */
function disciplines_proposees( string $type ): array {
	$connues = disciplines();
	$options = array();

	foreach ( $connues as $cle => $libelle ) {
		$options[ (string) $cle ] = $libelle;
	}

	$balayage  = balayage( $type );
	$orphelins = array();

	foreach ( $balayage['valeurs'] as $valeur ) {
		if ( '' !== $valeur && ! isset( $connues[ $valeur ] ) ) {
			$orphelins[ $valeur ] = true;
		}
	}

	foreach ( cles_orphelines( array_keys( $orphelins ) ) as $cle ) {
		$options[ $cle ] = $cle;
	}

	return $options;
}

/**
 * Valeur de filtre demandée dans l'URL, une fois assainie et validée.
 *
 * Une valeur qui ne figure pas parmi les options proposées est ignorée, jamais réparée : mieux vaut
 * revenir à « toutes » qu'afficher un écran filtré sur un critère que personne n'a demandé.
 *
 * @param string $type Nom du type de contenu.
 *
 * @return string Valeur retenue, chaîne vide si aucun filtre n'est actif.
 */
function filtre_actif( string $type ): string {
	$description = description( $type );

	if ( null === $description ) {
		return '';
	}

	$parametre = $description['filtre']['parametre'];

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- lecture d'un paramètre de filtre en GET, soumis par le bouton « Filtrer » du cœur ; aucune écriture n'en découle. L'assainissement ne peut pas tenir sur cette ligne : il dépend du paramètre lu, et la valeur est ensuite validée contre les options réellement proposées.
	$brut = isset( $_GET[ $parametre ] ) ? wp_unslash( $_GET[ $parametre ] ) : '';

	if ( ! is_string( $brut ) || '' === $brut ) {
		return '';
	}

	/*
	 * Une année est une suite de quatre chiffres, une clé de liste fermée est une clé : deux
	 * assainissements différents, parce que sanitize_key() minusculerait une valeur d'année sans
	 * rien y gagner et que sanitize_text_field() laisserait passer une clé bien trop libre.
	 */
	$valeur = 'mtb_annee' === $parametre
		? sanitize_text_field( $brut )
		: sanitize_key( $brut );

	return isset( options_de_filtre( $type )[ $valeur ] ) ? $valeur : '';
}
