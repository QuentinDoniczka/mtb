<?php
/**
 * Composition du balisage de blocs d'une page reprise.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\ResultatsPages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * LA RÈGLE QUI FERME LE RISQUE D1
 *
 * Ce module ne compose JAMAIS de chaîne de balisage à la main. Il construit un tableau à la forme
 * exacte de ce que rend parse_blocks(), et appelle serialize_blocks().
 *
 * Ce n'est pas du confort. serialize_block_attributes() échappe « -- » en « -- », et un
 * « -- » dans une valeur d'attribut FERME le commentaire HTML : la page serait détruite, et le
 * ferait silencieusement, sur un site qui répond 200.
 *
 * QUATRE FORMES ÉMISES, ET RIEN D'AUTRE
 *
 *   1. le bloc auto-fermant, « <!-- wp:mtb/x {…} /--> » ;
 *   2. l'enveloppe « mtb/fiche-information », seul bloc du catalogue à porter des enfants ;
 *   3. « core/paragraph », dont le balisage enregistré est un « <p> » NU ;
 *   4. les fermetures, posées par serialize_blocks() lui-même.
 *
 * « core/list » n'est jamais émis : son save() pose class="wp-block-list" et exige des
 * « core/list-item » imbriqués — le seul vecteur d'invalidation réel de cette reprise. Et la source
 * n'en a pas besoin : la réduction rend le contenu en lignes séparées, donc en paragraphes.
 * « core/heading » non plus : l'inséreur le refuse dans une fiche, et l'éleveuse pourrait donc
 * supprimer ce qu'elle ne peut pas recréer.
 *
 * Onze des douze blocs du catalogue ont « save: () => null » : leur balisage enregistré est le
 * commentaire auto-fermant, le validateur compare du vide à du vide, ils ne peuvent
 * structurellement pas basculer en « contenu inattendu ». Seul « mtb/fiche-information » a un save
 * réel, qui ne sérialise que ses enfants. D'où l'unique piège, tenu par la construction ci-dessous :
 * rien d'autre que des commentaires de blocs et des blancs entre l'ouverture et la fermeture d'une
 * fiche d'information.
 */

/**
 * Blanc qui sépare deux blocs voisins dans un contenu enregistré par l'éditeur, qu'ils soient de
 * premier niveau ou enfants d'une même fiche.
 */
const SEPARATEUR_DE_BLOCS = "\n\n";

/**
 * Compose le contenu d'une page depuis sa liste d'entrées.
 *
 * @param array<int, mixed>  $composition Entrées de composition, déjà contrôlées.
 * @param array<string, int> $photos      Nom de fichier => identifiant de pièce jointe.
 * @param string[]           $raisons     Raisons de rejet, complétées par référence.
 *
 * @return string Balisage de blocs, chaîne vide si la composition ne produit aucun bloc.
 */
function composer_le_balisage( array $composition, array $photos, array &$raisons ): string {
	$noeuds = array();

	foreach ( $composition as $entree ) {
		if ( ! is_array( $entree ) || isset( $entree['ecart'] ) ) {
			continue;
		}

		$noeud = noeud_de_bloc( $entree, $photos, $raisons );

		if ( null === $noeud ) {
			continue;
		}

		if ( array() !== $noeuds ) {
			$noeuds[] = noeud_libre( SEPARATEUR_DE_BLOCS );
		}

		$noeuds[] = $noeud;
	}

	if ( array() === $noeuds ) {
		return '';
	}

	return serialize_blocks( $noeuds );
}

/**
 * Nœud de texte libre, tel que le parseur en produit entre deux blocs.
 *
 * @param string $texte Texte brut.
 *
 * @return array<string, mixed> Nœud à la forme de parse_blocks().
 */
function noeud_libre( string $texte ): array {
	return array(
		'blockName'    => null,
		'attrs'        => array(),
		'innerBlocks'  => array(),
		'innerHTML'    => $texte,
		'innerContent' => array( $texte ),
	);
}

/**
 * Nœud d'un bloc du catalogue, avec ses attributs et sa prose éventuelle.
 *
 * @param array<string, mixed> $entree  Entrée de composition.
 * @param array<string, int>   $photos  Nom de fichier => identifiant de pièce jointe.
 * @param string[]             $raisons Raisons de rejet, complétées par référence.
 *
 * @return array<string, mixed>|null Nœud, ou null si l'entrée ne peut pas être composée.
 */
function noeud_de_bloc( array $entree, array $photos, array &$raisons ) {
	$bloc = texte_de( $entree, 'bloc' );

	if ( '' === $bloc ) {
		return null;
	}

	$attributs = attributs_a_ecrire( $bloc, $entree, $photos, $raisons );
	$enfants   = array();

	foreach ( paragraphes_de( $entree ) as $texte ) {
		$enrichi = texte_enrichi( $texte, $raisons );

		if ( null === $enrichi ) {
			continue;
		}

		$enfants[] = noeud_de_paragraphe( $enrichi );
	}

	/*
	 * LE PARAGRAPHE VIDE EST UNE PROPRIÉTÉ DE LA SÉRIALISATION, JAMAIS DE LA DONNÉE.
	 *
	 * Une fiche d'information sans prose reçoit un « core/paragraph » vide, exactement comme le
	 * gabarit d'insertion de l'éditeur en pose un : c'est le curseur où l'éleveuse tape. Aucun
	 * fichier de données ne porte pour autant un paragraphe vide — la donnée reste vide, et le
	 * contrôle amont continue de refuser une ligne d'espacement de l'éditeur IONOS comme
	 * paragraphe, ce qui est une contrainte structurelle et non une commodité d'édition.
	 *
	 * Rien n'en paraît au public : « est_vide() » de « blocks/fiche-information/rendu.php » réduit
	 * « <p></p> » à une prose vide, « contenu() » rend alors la chaîne vide, et « render.php » sort
	 * avant tout affichage. Zéro octet, pas de div vide, pas de réserve.
	 *
	 * Les autres blocs du catalogue ne portent pas d'enfants : eux restent auto-fermants.
	 */
	if ( array() === $enfants && BLOC_A_PROSE === $bloc ) {
		$enfants[] = noeud_de_paragraphe( '' );
	}

	if ( array() === $enfants ) {
		return array(
			'blockName'    => $bloc,
			'attrs'        => $attributs,
			'innerBlocks'  => array(),
			'innerHTML'    => '',
			'innerContent' => array(),
		);
	}

	/*
	 * Les fragments reproduisent au caractère près ce que l'éditeur enregistre : un saut de ligne
	 * après l'ouverture, une ligne vide entre deux enfants, un saut de ligne avant la fermeture. Rien
	 * d'autre que des commentaires de blocs et des blancs ne se glisse entre les deux délimiteurs.
	 */
	$fragments = array( "\n" );

	foreach ( array_keys( $enfants ) as $rang ) {
		if ( 0 < $rang ) {
			$fragments[] = SEPARATEUR_DE_BLOCS;
		}

		$fragments[] = null;
	}

	$fragments[] = "\n";

	return array(
		'blockName'    => $bloc,
		'attrs'        => $attributs,
		'innerBlocks'  => $enfants,
		'innerHTML'    => "\n\n",
		'innerContent' => $fragments,
	);
}

/**
 * Nœud d'un paragraphe du cœur : un « <p> » nu, sans classe ni attribut.
 *
 * @param string $contenu Contenu déjà échappé.
 *
 * @return array<string, mixed> Nœud à la forme de parse_blocks().
 */
function noeud_de_paragraphe( string $contenu ): array {
	$balisage = "\n<p>" . $contenu . "</p>\n";

	return array(
		'blockName'    => 'core/paragraph',
		'attrs'        => array(),
		'innerBlocks'  => array(),
		'innerHTML'    => $balisage,
		'innerContent' => array( $balisage ),
	);
}

/**
 * Liste des paragraphes d'une entrée, dans l'ordre.
 *
 * @param array<string, mixed> $entree Entrée de composition.
 *
 * @return string[] Textes, liste vide si l'entrée n'en porte pas.
 */
function paragraphes_de( array $entree ): array {
	$paragraphes = isset( $entree['paragraphes'] ) ? $entree['paragraphes'] : array();

	if ( ! is_array( $paragraphes ) ) {
		return array();
	}

	$textes = array();

	foreach ( $paragraphes as $texte ) {
		if ( is_string( $texte ) && ! texte_blanc( $texte ) ) {
			$textes[] = $texte;
		}
	}

	return $textes;
}

/**
 * Attributs à écrire sur un bloc, défauts omis et photo résolue.
 *
 * Une valeur absente n'est pas écrite : l'attribut retombe alors sur le défaut de son
 * « block.json », ce qui est très exactement ce que l'éditeur enregistre.
 *
 * @param string               $bloc    Nom du bloc.
 * @param array<string, mixed> $entree  Entrée de composition.
 * @param array<string, int>   $photos  Nom de fichier => identifiant de pièce jointe.
 * @param string[]             $raisons Raisons de rejet, complétées par référence.
 *
 * @return array<string, mixed> Attributs à sérialiser.
 */
function attributs_a_ecrire( string $bloc, array $entree, array $photos, array &$raisons ): array {
	$declares  = isset( $entree['attributs'] ) && is_array( $entree['attributs'] ) ? $entree['attributs'] : array();
	$attributs = array();

	/*
	 * Un attribut est stocké VERBATIM, sans le moindre échappement HTML. Un attribut de bloc n'est
	 * pas du balisage : c'est une valeur JSON, que serialize_block_attributes() échappe pour le
	 * commentaire et que le composant échappe à son tour au rendu. L'échapper ici l'échapperait
	 * deux fois, et le visiteur lirait « &amp; » là où la source écrit « & ».
	 */
	foreach ( $declares as $nom => $valeur ) {
		if ( valeur_absente( $valeur ) ) {
			continue;
		}

		$attributs[ (string) $nom ] = $valeur;
	}

	$photo = texte_de( $entree, 'photo' );

	if ( '' === $photo ) {
		return $attributs;
	}

	$piece_jointe = isset( $photos[ $photo ] ) ? (int) $photos[ $photo ] : 0;

	if ( 0 === $piece_jointe ) {
		// État « photo_absente » : l'emplacement n'existe pas, aucune réserve n'est rendue.
		return $attributs;
	}

	$cle = attribut_de_photo( $bloc );

	if ( '' === $cle ) {
		$raisons[] = sprintf(
			'le bloc « %s » ne porte aucun emplacement de photo : la clé « photo » de l\'entrée est '
			. 'sans destination.',
			$bloc
		);

		return $attributs;
	}

	$attributs[ $cle ] = $piece_jointe;

	return $attributs;
}

/**
 * Convertit la notation de lien de la capture en balisage, et échappe le reste.
 *
 * Seul balisage inline autorisé : « [LIEN href=…]texte[/LIEN] ». Le lien produit ne porte QUE son
 * attribut « href » — ni cible, ni relation, ni classe, ni titre.
 *
 * @param string   $texte   Texte du fichier de données.
 * @param string[] $raisons Raisons de rejet, complétées par référence.
 *
 * @return string|null Contenu échappé, ou null si le texte ne peut pas être repris.
 */
function texte_enrichi( string $texte, array &$raisons ) {
	$morceaux = preg_split(
		'/(\[LIEN href=[^\]]*\].*?\[\/LIEN\])/su',
		$texte,
		-1,
		PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
	);

	if ( ! is_array( $morceaux ) ) {
		$raisons[] = 'un texte n\'a pas pu être analysé : il n\'est pas encodé en UTF-8 valide.';

		return null;
	}

	$rendu = '';

	foreach ( $morceaux as $morceau ) {
		$lien = array();

		if ( 1 !== preg_match( '/^\[LIEN href=([^\]]*)\](.*?)\[\/LIEN\]$/su', $morceau, $lien ) ) {
			$rendu .= echapper_texte( $morceau );

			continue;
		}

		$adresse = esc_url_raw( trim( $lien[1] ) );

		if ( '' === $adresse ) {
			$raisons[] = sprintf( 'l\'adresse « %s » n\'est pas un lien recevable.', $lien[1] );

			return null;
		}

		$rendu .= '<a href="' . echapper_attribut( $adresse ) . '">' . echapper_texte( $lien[2] ) . '</a>';
	}

	return $rendu;
}

/**
 * Échappe un nœud de texte comme l'éditeur de blocs le fait.
 *
 * esc_html() est volontairement écartée : elle échappe aussi l'apostrophe et le chevron fermant,
 * alors que l'éditeur de blocs n'échappe que l'esperluette et le chevron ouvrant. Un « => » de la
 * source deviendrait « =&gt; », ce que le sérialiseur du texte enrichi ne réécrirait jamais ainsi —
 * et le bloc basculerait en « contenu inattendu » à la première ouverture dans l'éditeur.
 *
 * @param string $texte Texte brut.
 *
 * @return string Texte échappé.
 */
function echapper_texte( string $texte ): string {
	return str_replace( '<', '&lt;', echapper_esperluette( $texte ) );
}

/**
 * Échappe les esperluettes qui n'ouvrent pas déjà une entité.
 *
 * Une esperluette déjà écrite en entité n'est pas ré-échappée : le sérialiseur du texte enrichi ne
 * réécrirait jamais « &amp;amp; » comme la source l'écrit, et le bloc basculerait en « contenu
 * inattendu » à la première ouverture dans l'éditeur.
 *
 * @param string $texte Texte brut.
 *
 * @return string Texte dont les esperluettes nues sont échappées.
 */
function echapper_esperluette( string $texte ): string {
	$echappe = preg_replace( '/&(?!([a-z0-9]+|#[0-9]+|#x[a-f0-9]+);)/i', '&amp;', $texte );

	return is_string( $echappe ) ? $echappe : $texte;
}

/**
 * Échappe une valeur d'attribut HTML comme l'éditeur de blocs le fait.
 *
 * @param string $valeur Valeur brute.
 *
 * @return string Valeur échappée.
 */
function echapper_attribut( string $valeur ): string {
	return str_replace( '"', '&quot;', echapper_esperluette( $valeur ) );
}

/**
 * Le balisage se relit-il et se réécrit-il à l'identique ?
 *
 * Premier des trois contrôles du balisage : il attrape un nœud mal formé AVANT l'écriture. Le
 * deuxième relit le contenu réellement stocké — il attrape un wp_slash() manquant et un filtrage
 * kses. Le troisième est à l'écran, dans l'éditeur : le validateur de blocs est CLIENT, aucun
 * contrôle serveur ne le remplace, et on le dit plutôt que de faire semblant.
 *
 * @param string $contenu Balisage composé.
 *
 * @return bool Vrai si l'aller-retour est stable.
 */
function aller_retour_stable( string $contenu ): bool {
	return serialize_blocks( parse_blocks( $contenu ) ) === $contenu;
}
