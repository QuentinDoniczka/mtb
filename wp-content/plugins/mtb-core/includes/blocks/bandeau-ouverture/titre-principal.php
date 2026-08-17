<?php
/**
 * Décision unique : le bandeau de ce contenu porte-t-il le titre principal de la page ?
 *
 * Deux consommateurs — l'effacement du titre rendu par le cœur et le rendu du bandeau — appellent
 * cette décision et n'en réimplémentent jamais un morceau. C'est ce qui garantit exactement un
 * « h1 » par page, dans tous les cas de figure.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

/*
 * DEUX BLOCS D'ESPACE DE NOMS DANS UN SEUL FICHIER, ET C'EST VOULU.
 *
 * La décision est interne au module, donc dans l'espace de noms du module. La fonction exposée au
 * thème est publique, donc dans l'espace de noms GLOBAL : un thème conforme n'écrit jamais « MTB\ »,
 * c'est la frontière vérifiable par recherche entre le thème et l'extension. PHP n'admet les deux
 * dans un même fichier qu'avec la syntaxe à accolades, et interdit alors le moindre code hors des
 * accolades — y compris la garde ABSPATH, qui vit donc dans le premier bloc.
 */

namespace MTB\Core\Blocks\BandeauOuverture {

	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	/**
	 * Le bandeau de ce contenu porte-t-il le titre principal de la page ?
	 *
	 * Mémoïsée pour la durée de la requête : la fonction est appelée au moins deux fois par page — une
	 * fois par le filtre du titre du cœur, une fois par le rendu du bandeau — et l'analyse du contenu
	 * ne doit avoir lieu qu'une fois. Ni transient, ni cache persistant : la réponse dépend du contenu
	 * de la page, qui change à chaque enregistrement, et un cache périmé après modification est
	 * exactement l'échec de « saisi une fois, affiché partout ».
	 *
	 * Effet de bord assumé : le premier appel fixe la réponse pour toute la requête. C'est correct
	 * parce que le premier appel a lieu pendant le rendu du gabarit principal, donc dans le bon
	 * contexte.
	 *
	 * @param int $post_id Identifiant du contenu jugé.
	 *
	 * @return bool true seulement si le bandeau émet le « h1 » de la page.
	 */
	function doit_porter_le_titre_principal( int $post_id ): bool {
		static $memo = array();

		// Garde 1 — aucun contexte, aucune décision. Non mémoïsée : rien à retenir d'une clé absurde.
		if ( $post_id <= 0 ) {
			return false;
		}

		if ( isset( $memo[ $post_id ] ) ) {
			return $memo[ $post_id ];
		}

		$memo[ $post_id ] = evaluer( $post_id );

		return $memo[ $post_id ];
	}

	/**
	 * Applique les gardes, sans mémoire. Toute sortie anticipée vaut « le bandeau ne porte rien ».
	 *
	 * @param int $post_id Identifiant du contenu jugé, déjà connu strictement positif.
	 *
	 * @return bool
	 */
	function evaluer( int $post_id ): bool {
		$post = get_post( $post_id );

		// Garde 1 (suite) — un identifiant qui ne désigne aucun contenu.
		if ( ! $post instanceof \WP_Post ) {
			return false;
		}

		/*
		 * Garde 2 — hors d'une vue singulière, ou sur une vue singulière portant sur un AUTRE contenu.
		 * Sans elle, sur une archive ou une page de recherche listant une page dont le contenu
		 * commence par un bandeau, le filtre effacerait le titre-lien de cette entrée dans la liste,
		 * alors que le bandeau, lui, n'y est pas rendu du tout (les listes rendent l'extrait). On
		 * perdrait des liens de navigation, en silence, sur un site qui répond 200.
		 *
		 * Conséquence dans l'éditeur : la vue d'édition n'est pas une vue singulière, la décision y
		 * vaut donc toujours false et le titre du bandeau y est toujours un « p ». Aucun second « h1 »
		 * n'est injecté dans le DOM de l'éditeur, et l'aperçu reste fidèle puisque le thème stylise la
		 * classe du titre et jamais sa balise.
		 */
		if ( ! is_singular() || get_queried_object_id() !== $post_id ) {
			return false;
		}

		/*
		 * Garde 3 — page protégée dont le mot de passe n'a pas été fourni. « core/post-content » rend
		 * alors le formulaire de mot de passe et le rendu du bandeau ne s'exécute jamais : effacer
		 * quand même le titre du cœur laisserait la page SANS AUCUN « h1 ».
		 *
		 * post_password_required() et non « has_password » : une fois le mot de passe saisi, le
		 * contenu se rend, le bandeau avec, et le titre du cœur doit repartir.
		 */
		if ( post_password_required( $post ) ) {
			return false;
		}

		// Garde 4 — pré-test bon marché : une simple recherche de chaîne avant toute analyse.
		if ( ! has_block( 'mtb/bandeau-ouverture', $post ) ) {
			return false;
		}

		$premier = premier_bloc_nomme( $post->post_content );

		if ( null === $premier || 'mtb/bandeau-ouverture' !== ( $premier['blockName'] ?? null ) ) {
			return false;
		}

		/*
		 * Garde 5 — un bandeau sans aucun texte ne peut pas porter le titre principal. Le titre du
		 * cœur repart alors ; sur une page sans titre il ne rend rien de lui-même, et la page finit
		 * sans « h1 ». C'est un défaut de la page — un titre manquant — jamais un titre inventé.
		 */
		$attributs   = isset( $premier['attrs'] ) && is_array( $premier['attrs'] ) ? $premier['attrs'] : array();
		$titre_saisi = isset( $attributs['titre'] ) ? (string) $attributs['titre'] : '';

		return '' !== titre_effectif( $titre_saisi, $post_id );
	}

	/**
	 * Titre que le bandeau affiche réellement : celui qu'elle a tapé, sinon celui de la page.
	 *
	 * Écrite UNE SEULE FOIS parce que la règle de repli a deux consommateurs — la garde 5
	 * ci-dessus, qui a besoin de savoir s'il y a du texte, et le rendu, qui a besoin du texte
	 * lui-même. Recopiée de part et d'autre, une correction faite d'un seul côté ferait décider le
	 * « h1 » sur un titre et en imprimer un autre : le mécanisme deviendrait visible, sans erreur,
	 * sur un site qui répond 200.
	 *
	 * post_title BRUT, jamais get_the_title() : ce dernier applique le filtre « the_title », qui
	 * préfixe « Privé : » ou « Protégé : » — un mot du cœur inséré dans le titre du bandeau sans
	 * qu'on l'ait décidé — et wptexturize, qui convertit « 1x2 » en « 1×2 ». Un seul régime pour ce
	 * qu'elle a tapé comme pour le titre de la page : brut, échappé au rendu, rien d'autre.
	 *
	 * @param string $titre_saisi Valeur de l'attribut « titre », telle qu'enregistrée.
	 * @param int    $post_id     Contenu dont le titre sert de repli. Le contenu n'est relu que si
	 *                            le repli est nécessaire, et get_post() sert alors le cache.
	 *
	 * @return string Titre effectif, chaîne vide si le bandeau n'a aucun titre à afficher.
	 */
	function titre_effectif( string $titre_saisi, int $post_id ): string {
		$titre = trim( $titre_saisi );

		if ( '' !== $titre ) {
			return $titre;
		}

		$post = get_post( $post_id );

		return $post instanceof \WP_Post ? trim( (string) $post->post_title ) : '';
	}

	/**
	 * Premier bloc réellement nommé du premier niveau du contenu.
	 *
	 * Les blocs de premier niveau seulement : un bandeau imbriqué dans un Groupe ou une Colonne n'est
	 * jamais « premier », le titre du cœur est conservé et le bandeau rend un « p ». C'est délibéré et
	 * c'est le comportement le plus sûr — exactement un « h1 », y compris dans ce cas.
	 *
	 * @param string $contenu Contenu enregistré de la page.
	 *
	 * @return array<string, mixed>|null Bloc analysé, ou null si le contenu ne porte aucun bloc nommé.
	 */
	function premier_bloc_nomme( string $contenu ): ?array {
		foreach ( parse_blocks( $contenu ) as $bloc ) {
			/*
			 * L'analyseur intercale des blocs sans nom pour les espaces et les retours à la ligne
			 * entre deux blocs : les sauter est indispensable, sinon le premier bloc trouvé serait
			 * presque toujours ce blanc, et la décision serait toujours fausse.
			 */
			if ( null === ( $bloc['blockName'] ?? null ) && '' === trim( (string) ( $bloc['innerHTML'] ?? '' ) ) ) {
				continue;
			}

			return $bloc;
		}

		return null;
	}
}

namespace {

	if ( ! function_exists( 'mtb_bandeau_ouverture_porte_le_titre' ) ) {
		/**
		 * Le bandeau d'ouverture de ce contenu émet-il le « h1 » de la page ?
		 *
		 * Unique juge de la question, et seule voie sanctionnée pour un gabarit : tout gabarit qui
		 * émet son propre « h1 » ailleurs que par « core/post-title » doit interroger cette fonction,
		 * sinon deux « h1 » cohabiteront sans que rien ne le signale. Le titre d'une page se rend par
		 * « core/post-title », et « core/post-title » ne se retire jamais d'un gabarit : son
		 * effacement est conditionnel et appartient à l'extension.
		 *
		 * N'imprime rien, ne rend aucun HTML, et rend false dès qu'elle ne peut pas décider.
		 *
		 * @param int $post_id Identifiant du contenu. 0 — le défaut — désigne le contenu actuellement
		 *                     interrogé.
		 *
		 * @return bool true : le bandeau porte le titre principal, le gabarit ne doit pas émettre le
		 *              sien. false : il ne le porte pas (bandeau absent, pas premier bloc, page
		 *              protégée non déverrouillée, bandeau sans texte, hors vue singulière), le
		 *              gabarit est libre.
		 */
		function mtb_bandeau_ouverture_porte_le_titre( int $post_id = 0 ): bool {
			if ( $post_id <= 0 ) {
				$post_id = get_queried_object_id();
			}

			return \MTB\Core\Blocks\BandeauOuverture\doit_porter_le_titre_principal( $post_id );
		}
	}
}
