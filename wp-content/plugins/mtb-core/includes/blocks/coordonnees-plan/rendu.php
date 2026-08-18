<?php
/**
 * Composant « Coordonnées et plan d'accès » — fonctions d'aide du rendu public.
 *
 * Ce fichier est inclus UNE SEULE FOIS, par « bootstrap.php ». Il est, avec « coordonnees.php » et
 * « interface.php », le seul du module à déclarer des fonctions : « render.php » est inclus par le
 * cœur avec un « require » nu, donc une fois par instance du bloc présente sur la page.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Blocks\CoordonneesPlan;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Nettoie une valeur recopiée tenant sur une ligne, sans jamais en altérer le contenu.
 *
 * Reprise à l'identique de includes/content/resultat/assainissement.php — et non de la variante du
 * chien, qui n'appelle pas wp_check_invalid_utf8() avant un preg_replace portant le modificateur
 * « u » (sur une entrée mal encodée, preg_replace rend null et la branche de repli renvoie la valeur
 * NON nettoyée) et qui remplace les caractères de contrôle par une espace au lieu de les supprimer,
 * donc injecte un caractère jamais tapé dans une valeur recopiée.
 *
 * sanitize_text_field(), wp_strip_all_tags() et wp_kses() sont proscrites ici (décision 20) : elles
 * passent par strip_tags(), qui viderait EN SILENCE une valeur commençant par « < ». On retire donc
 * les seuls caractères de contrôle, on aplatit la valeur sur une ligne, on coupe les espaces de
 * bord, et rien d'autre. C'est sûr parce que l'échappement est systématique en sortie.
 *
 * @param mixed $valeur Valeur brute, telle qu'elle sort des attributs du bloc ou d'un gabarit.
 *
 * @return string Valeur nettoyée, vide si la valeur reçue n'était pas un scalaire.
 */
function texte_ligne( $valeur ): string {
	if ( ! is_scalar( $valeur ) ) {
		return '';
	}

	// Rend la suite sûre pour le modificateur « u » : une entrée mal encodée ressort vide d'ici.
	$texte = wp_check_invalid_utf8( (string) $valeur );

	$nettoye = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $texte );

	if ( is_string( $nettoye ) ) {
		$texte = $nettoye;
	}

	$aplati = preg_replace( '/[\r\n\t]+/', ' ', $texte );

	if ( is_string( $aplati ) ) {
		$texte = $aplati;
	}

	return trim( $texte );
}

/**
 * Nettoie une valeur recopiée dont le domaine autorise le retour à la ligne.
 *
 * Même nettoyage que texte_ligne(), à une divergence près, déclarée au contrat §10 : les retours à
 * la ligne sont PRÉSERVÉS, « \r\n » et « \r » étant ramenés à « \n » pour n'avoir qu'une seule
 * graphie à rendre ensuite. Une adresse postale s'écrit sur plusieurs lignes ; les aplatir ferait
 * perdre une coupure que l'éleveuse a tapée. Ce n'est pas un autre avis sur « ce qu'est une valeur
 * propre », c'est le même nettoyage sur un champ dont le domaine autorise le retour à la ligne.
 *
 * La tabulation reste aplatie en espace : elle n'est jamais une coupure de ligne, et rien dans une
 * adresse postale ne la porte.
 *
 * @param mixed $valeur Valeur brute, telle qu'elle sort des attributs du bloc ou d'un gabarit.
 *
 * @return string Valeur nettoyée, vide si la valeur reçue n'était pas un scalaire.
 */
function texte_multiligne( $valeur ): string {
	if ( ! is_scalar( $valeur ) ) {
		return '';
	}

	$texte = wp_check_invalid_utf8( (string) $valeur );

	$nettoye = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $texte );

	if ( is_string( $nettoye ) ) {
		$texte = $nettoye;
	}

	$normalise = preg_replace( '/\r\n?/', "\n", $texte );

	if ( is_string( $normalise ) ) {
		$texte = $normalise;
	}

	$tabulations = preg_replace( '/\t+/', ' ', $texte );

	if ( is_string( $tabulations ) ) {
		$texte = $tabulations;
	}

	return trim( $texte );
}

/**
 * Compose une paire « étiquette + valeur » de la liste de définition, ou rien du tout.
 *
 * Un champ vide retire la PAIRE ENTIÈRE — jamais un « dt » orphelin, jamais un « dd » vide, jamais
 * « Non renseigné » : c'est le bénéfice structurel de la liste de définition, aucune règle de style
 * ne dépendant de la présence d'un champ.
 *
 * L'étiquette est émise SANS PONCTUATION : ni deux-points, ni tiret. Le libellé et la valeur sont
 * deux éléments distincts, et personne ne compose une chaîne à partir d'eux.
 *
 * @param string $etiquette    Libellé affiché, fourni par l'appelant.
 * @param string $modificateur Suffixe du crochet de classe du « dd » : adresse, telephone, courriel.
 * @param string $valeur       Valeur déjà assainie, telle qu'elle a été saisie.
 * @param string $uri          URI du lien, chaîne vide pour un rendu en texte nu.
 * @param bool   $multiligne   Vrai pour rendre les retours à la ligne par des « br » littéraux.
 *
 * @return string Balisage de la paire, chaîne vide si la valeur est vide.
 */
function paire( string $etiquette, string $modificateur, string $valeur, string $uri, bool $multiligne ): string {
	if ( '' === $valeur ) {
		return '';
	}

	$texte = esc_html( $valeur );

	/*
	 * La coupure de ligne est portée par le HTML, jamais par un « span » que le thème habillerait :
	 * le balisage reste juste feuille de style désactivée. nl2br() reçoit une valeur DÉJÀ échappée,
	 * donc il n'insère des « br » que dans du texte inoffensif ; son second argument le fait écrire
	 * « <br> » et non « <br /> ».
	 */
	if ( $multiligne ) {
		$texte = nl2br( $texte, false );
	}

	if ( '' !== $uri ) {
		$texte = sprintf(
			'<a class="mtb-coordonnees-plan__lien" href="%1$s">%2$s</a>',
			esc_url( $uri ),
			$texte
		);
	}

	return sprintf(
		'<dt class="mtb-coordonnees-plan__etiquette">%1$s</dt><dd class="mtb-coordonnees-plan__valeur mtb-coordonnees-plan__valeur--%2$s">%3$s</dd>',
		esc_html( $etiquette ),
		esc_attr( $modificateur ),
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Valeur échappée par esc_html ci-dessus, URI par esc_url.
		$texte
	);
}

/**
 * Compose le bloc des coordonnées — un seul « address », une seule liste de définition.
 *
 * « address » est l'élément des coordonnées du document : c'est sa définition HTML. Le plan reste en
 * dehors, une carte n'étant pas une coordonnée.
 *
 * @param string $adresse   Adresse déjà assainie, retours à la ligne compris.
 * @param string $telephone Numéro déjà assaini, tel qu'il a été saisi.
 * @param string $courriel  Adresse de courriel déjà assainie, telle qu'elle a été saisie.
 *
 * @return string Balisage des coordonnées, chaîne vide si les trois champs sont vides.
 */
function coordonnees( string $adresse, string $telephone, string $courriel ): string {
	/*
	 * Appels directs et non sous function_exists() : les deux dérivations sont déclarées par
	 * coordonnees.php, que bootstrap.php inclut avant ce fichier. Une garde ici ferait disparaître
	 * le lien en silence au lieu de signaler un chargement cassé. La garde du contrat §2 vise les
	 * consommateurs EXTÉRIEURS au module — le thème, les autres modules —, pas la chaîne
	 * propriétaire de la fonction.
	 */
	$paires = paire( 'Adresse', 'adresse', $adresse, '', true )
		. paire( 'Téléphone', 'telephone', $telephone, mtb_coordonnees_lien_telephone( $telephone ), false )
		. paire( 'Courriel', 'courriel', $courriel, mtb_coordonnees_lien_courriel( $courriel ), false );

	if ( '' === $paires ) {
		return '';
	}

	return sprintf(
		'<address class="mtb-coordonnees-plan__coordonnees"><dl class="mtb-coordonnees-plan__liste">%s</dl></address>',
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Étiquettes, valeurs et URI échappées par paire().
		$paires
	);
}

/**
 * Compose la figure du plan d'accès — cadre, image, puis légende — ou rien du tout.
 *
 * Quatre gardes, dans cet ordre : identifiant utile, pièce jointe existante, pièce jointe qui est
 * bien une image, et balisage réellement produit par le cœur. Un seul échec et l'emplacement
 * N'EXISTE PAS DU TOUT — ni cadre vide, ni image cassée, ni figure orpheline, ni avertissement. Le
 * reste du composant rend normalement.
 *
 * @param int    $plan_id     Identifiant de la pièce jointe choisie, 0 pour aucun plan.
 * @param string $description Description de l'image, telle qu'elle a été saisie.
 * @param int    $post_id     Identifiant du contenu qui porte le composant, pour le seul filtre.
 *
 * @return string Balisage de la figure, chaîne vide s'il n'y a pas de plan à rendre.
 */
function figure( int $plan_id, string $description, int $post_id ): string {
	if ( $plan_id <= 0 ) {
		return '';
	}

	// Pièce jointe supprimée de la médiathèque : l'identifiant survit en base, pas le fichier.
	if ( 'attachment' !== get_post_type( $plan_id ) ) {
		return '';
	}

	// Un document choisi par erreur — un PDF de plan — ne doit pas devenir une image cassée.
	if ( ! wp_attachment_is_image( $plan_id ) ) {
		return '';
	}

	$attributs = array(
		// Remplace les « attachment-large size-large » du cœur : un seul crochet sur l'image.
		'class'    => 'mtb-coordonnees-plan__image',
		/*
		 * Passée BRUTE : wp_get_attachment_image() applique esc_attr(), la pré-échapper ferait lire
		 * « &#039; » à une synthèse vocale. Vide par défaut, l'image est décorative — l'extension
		 * n'invente jamais l'alternative d'une image qu'elle n'a jamais vue.
		 */
		'alt'      => $description,
		/*
		 * Écrits explicitement : depuis la version 6.3, le cœur peut poser « eager » et
		 * « fetchpriority=high » sur la première grande image de la page, et un plan d'accès n'ouvre
		 * jamais une page.
		 */
		'loading'  => 'lazy',
		'decoding' => 'async',
		/*
		 * Dérivé du canal où le composant est posé, jamais choisi : canal texte, --l-texte = 36 rem
		 * = 576 px, atteint dès que la fenêtre dépasse 36 rem plus deux marges de page.
		 * « supports.align » valant false, aucune insertion ne peut élargir ce canal.
		 */
		'sizes'    => '(min-width: 42rem) 576px, 92vw',
	);

	/**
	 * Filtre les attributs de l'image du plan d'accès.
	 *
	 * Unique échappatoire du composant : elle permet au thème d'ajuster « sizes », « loading » ou
	 * « class » en une ligne, sans modifier un fichier de l'extension. Elle ne peut ni retirer la
	 * description de l'image — réappliquée juste après — ni changer l'image affichée.
	 *
	 * @param array<string, string> $attributs Attributs passés à wp_get_attachment_image().
	 * @param int                   $plan_id   Identifiant de la pièce jointe rendue.
	 * @param int                   $post_id   Identifiant du contenu qui porte le composant.
	 */
	$attributs = apply_filters( 'mtb_coordonnees_plan_attributs_image', $attributs, $plan_id, $post_id );

	if ( ! is_array( $attributs ) ) {
		$attributs = array();
	}

	$attributs['alt'] = $description;

	$image = (string) wp_get_attachment_image( $plan_id, 'large', false, $attributs );

	// Le cœur peut refuser de rendre là où les trois gardes précédentes avaient dit oui.
	if ( '' === $image ) {
		return '';
	}

	/*
	 * La mention d'attribution vient de la LÉGENDE DE LA PIÈCE JOINTE, jamais d'un attribut de bloc
	 * concurrent : deux sources pour une même ligne, dont l'une deviendrait fausse. La légende
	 * voyage avec l'image — si le plan est un jour remplacé, la mention part avec l'ancienne image
	 * au lieu de mentir sur la nouvelle.
	 */
	$legende = texte_ligne( wp_get_attachment_caption( $plan_id ) );

	$balisage_legende = '';

	if ( '' !== $legende ) {
		$balisage_legende = sprintf(
			'<figcaption class="mtb-coordonnees-plan__legende">%s</figcaption>',
			esc_html( $legende )
		);
	}

	return sprintf(
		'<figure class="mtb-coordonnees-plan__figure"><div class="mtb-coordonnees-plan__cadre">%1$s</div>%2$s</figure>',
		/*
		 * Émis SANS wp_kses_post() : la liste blanche de kses n'admet sur « img » ni « srcset », ni
		 * « sizes », ni « decoding », et les supprimerait en silence. wp_get_attachment_image()
		 * échappe elle-même tout ce qu'elle produit.
		 */
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Balisage produit et échappé par wp_get_attachment_image().
		$image,
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Légende échappée par esc_html ci-dessus.
		$balisage_legende
	);
}

/**
 * Compose le contenu du composant — les coordonnées d'abord, le plan ensuite.
 *
 * Seule et unique source de la composition : « render.php » l'enveloppe dans
 * get_block_wrapper_attributes(), mtb_coordonnees_plan_rendu() l'enveloppe dans le div qu'elle
 * construit. Deux entrées, un seul balisage — sans quoi l'une des deux dériverait au premier
 * ajustement.
 *
 * L'ordre du DOM est l'ordre de lecture : l'information de localisation est du texte réel, rendu
 * AVANT l'image, et intacte si le plan disparaît.
 *
 * Les trois coordonnées vides l'emportent sur un plan choisi : un composant réduit à une image de
 * carte, sans une ligne d'adresse en texte, n'informe personne qui ne voit pas l'image.
 *
 * @param string $adresse          Adresse, telle qu'elle a été saisie.
 * @param string $telephone        Numéro, tel qu'il a été saisi.
 * @param string $courriel         Adresse de courriel, telle qu'elle a été saisie.
 * @param int    $plan_id          Identifiant de la pièce jointe du plan, 0 pour aucun plan.
 * @param string $plan_description Description de l'image du plan, telle qu'elle a été saisie.
 * @param int    $post_id          Identifiant du contenu qui porte le composant.
 *
 * @return string Balisage intérieur, chaîne vide si les trois coordonnées sont vides.
 */
function contenu( string $adresse, string $telephone, string $courriel, int $plan_id, string $plan_description, int $post_id ): string {
	$coordonnees = coordonnees( $adresse, $telephone, $courriel );

	// Aucune coordonnée : aucun élément, aucune racine, zéro octet — même si un plan est choisi.
	if ( '' === $coordonnees ) {
		return '';
	}

	return $coordonnees . figure( $plan_id, $plan_description, $post_id );
}
