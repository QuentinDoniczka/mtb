<?php
/**
 * Construction du balisage du composant « Tableau de résultats ».
 *
 * Le module rend une structure et les seuls crochets que la feuille de style vise, jamais une
 * décision visuelle : aucun style en ligne, aucune classe de mise en page, aucune dimension.
 *
 * Ce fichier ne lit jamais la base et n'appelle aucune fonction de lecture : il reçoit des tableaux
 * déjà construits et les imprime.
 *
 * DEUX RÈGLES D'ÉCRITURE À NE JAMAIS ASSOUPLIR POUR LA LISIBILITÉ.
 *
 * 1. Aucune chaîne de format ci-dessous ne contient de retour à la ligne, de tabulation, de
 *    PHP_EOL ni d'indentation, et chacune tient sur une seule ligne source. Le repli mobile du
 *    tableau retire les cellules vides avec « td:empty », qui échoue sur un simple retour à la
 *    ligne : un format reformaté sur trois lignes casserait l'affichage téléphone de toute la page
 *    sans qu'aucun « php -l », aucune revue de code et aucune vérification au-dessus de 48 rem ne
 *    le voie. La cellule vide est pour la même raison une branche à part, et non le chemin général
 *    avec un contenu vide.
 *
 * 2. Une cellule non vide contient exactement un nœud enfant : soit un passage de texte, soit un
 *    seul élément. Sous 48 rem la cellule devient une grille à deux colonnes — étiquette, valeur —
 *    et un deuxième enfant direct produirait un troisième élément de grille, qui se placerait à la
 *    ligne suivante dans la colonne des étiquettes. Rien ne le signalerait au-dessus de 768 px.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Blocks\TableauResultats;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Balisage complet d'une instance du composant.
 *
 * Deux sorties possibles, et rien d'autre : le balisage, ou la chaîne vide. L'état vide destiné à
 * l'éleveuse vit dans l'écran d'édition, pas ici — côté visiteur, un composant sans contenu ne
 * s'affiche pas, pas même son conteneur ni son titre.
 *
 * @param array<string, mixed> $attributs     Attributs de l'instance.
 * @param bool                 $rendu_de_bloc Vrai quand WordPress rend une instance du bloc — le
 *                                            cas de render.php. Faux pour un appel de gabarit.
 * @param int|null             $chien_id      Mode « chien-courant » seulement : identifiant à
 *                                            afficher, ou null pour le déduire de la requête.
 */
function rendu( array $attributs, bool $rendu_de_bloc = true, ?int $chien_id = null ): string {
	if ( ! lecture_disponible() ) {
		return '';
	}

	if ( 'chien-courant' === source_demandee( $attributs ) ) {
		/*
		 * « Palmarès de travail » est recopié de la section du système de conception qui décrit cet
		 * écran ; le titre de section, lui, appartient au gabarit de la fiche et n'est pas rendu ici.
		 */
		$corps = balisage_tableau( palmares( identifiant_du_chien( $chien_id ) ), 'Palmarès de travail' );
	} else {
		$corps = '';

		foreach ( groupes( discipline_demandee( $attributs ) ) as $groupe ) {
			$corps .= balisage_groupe( $groupe );
		}
	}

	if ( '' === $corps ) {
		return '';
	}

	return sprintf( '<div %s>%s</div>', attributs_conteneur( $rendu_de_bloc ), $corps );
}

/**
 * Les attributs du conteneur du composant.
 *
 * get_block_wrapper_attributes() lit le bloc en cours de rendu dans une propriété statique du cœur :
 * hors du rendu d'un bloc — un appel de gabarit — cette propriété vaut null et la fonction émet un
 * avertissement PHP. Le conteneur est donc composé ici pour ce cas-là, avec exactement les mêmes
 * classes et dans le même ordre.
 *
 * MESURÉ, ET C'EST LA RAISON DE LA LIGNE SUIVANTE. Le composant ferme le support « className »,
 * comme tous ses réglages d'éditeur. Or c'est ce support-là, et non les classes de l'enveloppe, qui
 * commande l'ajout de la classe engendrée par le cœur : fermé, le cœur ne l'écrit plus, et le
 * conteneur sortait avec la seule classe maison alors que le balisage gelé en porte deux. Elle est
 * donc ajoutée ici, sur les deux chemins, pour que le rendu du bloc et l'appel de gabarit donnent
 * exactement le même conteneur — et son nom est demandé au cœur plutôt que recopié.
 *
 * @param bool $rendu_de_bloc Vrai quand WordPress rend une instance du bloc.
 */
function attributs_conteneur( bool $rendu_de_bloc ): string {
	$classes = 'mtb-tableau-resultats';

	if ( function_exists( 'wp_get_block_default_classname' ) ) {
		$generee = wp_get_block_default_classname( 'mtb/tableau-resultats' );

		if ( is_string( $generee ) && '' !== $generee ) {
			$classes .= ' ' . $generee;
		}
	}

	if ( $rendu_de_bloc ) {
		return get_block_wrapper_attributes( array( 'class' => $classes ) );
	}

	return sprintf( 'class="%s"', esc_attr( $classes ) );
}

/**
 * Un groupe : son titre de discipline, puis son tableau.
 *
 * Le titre n'est jamais rendu sans son tableau : pas de titre orphelin. La clé stockée de la
 * discipline est portée par un attribut de données parce qu'elle n'apparaît nulle part ailleurs
 * dans le balisage — seul le libellé s'imprime. Le thème n'en dérive jamais un libellé et ne
 * distingue jamais une discipline d'une autre.
 *
 * Une discipline sortie de la liste close se rend exactement comme les autres : son libellé est la
 * valeur brute, imprimée telle quelle. Le renseignement « orpheline » n'est jamais lu ici.
 *
 * C'est ici, et nulle part ailleurs dans le rendu, qu'une clé de la fonction de lecture est écrite
 * en toutes lettres : « discipline » désigne le groupe et son attribut de données, jamais une
 * colonne. Aucune colonne n'est nommée dans ce fichier — les en-têtes et les cellules parcourent la
 * liste des colonnes que la fonction de lecture fournit, et le composant ne sait pas laquelle est
 * laquelle.
 *
 * @param mixed $groupe Groupe tel que la fonction de lecture l'a construit.
 */
function balisage_groupe( $groupe ): string {
	if ( ! is_array( $groupe ) ) {
		return '';
	}

	$cle     = isset( $groupe['discipline'] ) && is_string( $groupe['discipline'] ) ? $groupe['discipline'] : '';
	$libelle = isset( $groupe['discipline_libelle'] ) && is_string( $groupe['discipline_libelle'] ) ? $groupe['discipline_libelle'] : '';
	$tableau = balisage_tableau( $groupe, $libelle );

	if ( '' === $tableau ) {
		return '';
	}

	return sprintf( '<section data-discipline="%s"><h2>%s</h2>%s</section>', esc_attr( $cle ), esc_html( $libelle ), $tableau );
}

/**
 * Un tableau : sa légende, sa ligne d'en-tête, ses lignes.
 *
 * La légende donne au tableau son nom accessible, sans exiger le moindre identifiant unique — neuf
 * tableaux sur une même page en demanderaient neuf. Le thème la retire de la vue et la conserve
 * pour les technologies d'assistance.
 *
 * Les colonnes viennent du paquet et de lui seul : le composant ne sait pas, et n'a pas à savoir,
 * quelles colonnes un mode affiche. Les en-têtes et les cellules parcourent la même liste, dans le
 * même ordre, ce qui rend structurellement impossible un décalage entre les deux.
 *
 * @param mixed  $paquet  Tableau à deux clés, « colonnes » et « lignes ».
 * @param string $legende Nom du tableau, imprimé tel quel.
 */
function balisage_tableau( $paquet, string $legende ): string {
	if ( ! is_array( $paquet ) ) {
		return '';
	}

	$colonnes = isset( $paquet['colonnes'] ) && is_array( $paquet['colonnes'] ) ? $paquet['colonnes'] : array();
	$lignes   = isset( $paquet['lignes'] ) && is_array( $paquet['lignes'] ) ? $paquet['lignes'] : array();

	if ( array() === $colonnes || array() === $lignes ) {
		return '';
	}

	$entetes = '';
	$corps   = '';

	foreach ( $colonnes as $colonne ) {
		$entetes .= sprintf( '<th scope="col">%s</th>', esc_html( colonne_normalisee( $colonne )['libelle'] ) );
	}

	foreach ( $lignes as $ligne ) {
		$corps .= balisage_ligne( $ligne, $colonnes );
	}

	if ( '' === $corps ) {
		return '';
	}

	return sprintf( '<table class="mtb-tableau"><caption>%s</caption><thead><tr>%s</tr></thead><tbody>%s</tbody></table>', esc_html( $legende ), $entetes, $corps );
}

/**
 * Une ligne : une cellule par colonne, dans l'ordre des colonnes.
 *
 * @param mixed             $ligne    Ligne telle que la fonction de lecture l'a construite.
 * @param array<int, mixed> $colonnes Colonnes du tableau.
 */
function balisage_ligne( $ligne, array $colonnes ): string {
	$cellules = is_array( $ligne ) && isset( $ligne['cellules'] ) && is_array( $ligne['cellules'] ) ? $ligne['cellules'] : array();
	$rendu    = '';

	foreach ( $colonnes as $colonne ) {
		$colonne = colonne_normalisee( $colonne );
		$cellule = isset( $cellules[ $colonne['cle'] ] ) && is_array( $cellules[ $colonne['cle'] ] ) ? $cellules[ $colonne['cle'] ] : array();

		$rendu .= balisage_cellule( $colonne['libelle'], $cellule );
	}

	if ( '' === $rendu ) {
		return '';
	}

	return '<tr>' . $rendu . '</tr>';
}

/**
 * Une cellule.
 *
 * L'étiquette portée par « data-libelle » est exactement le libellé de la colonne, à l'octet près :
 * c'est elle que la feuille de style réaffiche devant la valeur quand le tableau se déplie en lignes
 * sur un téléphone. Elle vient de la même variable que l'en-tête, dans la même boucle.
 *
 * Le lien existe si, et seulement si, l'adresse fournie est une chaîne non vide. Le renseignement
 * « etat » n'est jamais consulté : une fiche en brouillon, à la corbeille ou protégée par mot de
 * passe garde son nom et perd son lien, sans que rien ne signale au visiteur l'existence d'un
 * contenu réservé.
 *
 * @param string               $libelle Libellé de la colonne, imprimé tel quel en étiquette.
 * @param array<string, mixed> $cellule Cellule telle que la fonction de lecture l'a construite.
 */
function balisage_cellule( string $libelle, array $cellule ): string {
	$affichage = isset( $cellule['affichage'] ) && is_string( $cellule['affichage'] ) ? $cellule['affichage'] : '';

	/*
	 * Branche dédiée, et non le chemin général avec un contenu vide : ZÉRO caractère entre les deux
	 * balises. Un espace, une indentation ou un retour à la ligne suffiraient à faire échouer
	 * « td:empty », et l'étiquette réapparaîtrait seule sur un téléphone, suivie de rien.
	 */
	if ( '' === $affichage ) {
		return sprintf( '<td data-libelle="%s" class="mtb-tableau__cellule--vide"></td>', esc_attr( $libelle ) );
	}

	$url = isset( $cellule['url'] ) && is_string( $cellule['url'] ) ? $cellule['url'] : '';

	if ( '' === $url ) {
		return sprintf( '<td data-libelle="%s">%s</td>', esc_attr( $libelle ), esc_html( $affichage ) );
	}

	return sprintf( '<td data-libelle="%s"><a href="%s">%s</a></td>', esc_attr( $libelle ), esc_url( $url ), esc_html( $affichage ) );
}

/**
 * Une colonne ramenée à ses deux chaînes, quelle que soit la forme reçue.
 *
 * Rendre toujours deux chaînes garantit qu'un en-tête et une cellule sont émis pour chaque entrée
 * de la liste des colonnes, même si l'une d'elles était mal formée : l'alignement des colonnes est
 * préservé, et rien n'est inventé.
 *
 * @param mixed $colonne Colonne telle que la fonction de lecture l'a construite.
 *
 * @return array<string, string> Deux clés, « cle » et « libelle ».
 */
function colonne_normalisee( $colonne ): array {
	return array(
		'cle'     => is_array( $colonne ) && isset( $colonne['cle'] ) && is_string( $colonne['cle'] ) ? $colonne['cle'] : '',
		'libelle' => is_array( $colonne ) && isset( $colonne['libelle'] ) && is_string( $colonne['libelle'] ) ? $colonne['libelle'] : '',
	);
}
