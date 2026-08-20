<?php
/**
 * Squelette de document partagé par les deux gabarits de fiche.
 *
 * Ce fichier n'est jamais résolu par la hiérarchie des gabarits : son nom est délibérément hors de
 * toute hiérarchie — ni `header.php`, ni `footer.php`, ni `single.php`. Deux fichiers « header »
 * dans un thème qui porte déjà `parts/header.html` seraient une invitation à l'erreur.
 *
 * Pourquoi il existe : quand `locate_template()` trouve un `.php`, `locate_block_template()`
 * tronque la liste des gabarits de blocs candidats et rend le `.php`. `template-canvas.php` n'est
 * alors jamais chargé, et avec lui disparaissent deux choses que le canevas assurait seul —
 * la balise `viewport` et la balise `title`. Les deux sont écrites ici, à la main.
 *
 * @package MTB
 */

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

/**
 * Rend le document complet d'une fiche : en-tête du site, corps, pied de page.
 *
 * L'ordre des étapes est le livrable, pas un détail d'écriture :
 *
 * 1. Les feuilles du site sont mises en file **avant** le tampon. Le rendu des blocs qui suit
 *    enfile les feuilles de composants ; sans ce pré-appel, `mtb-base` sortirait après elles, la
 *    cascade s'inverserait et les composants perdraient contre `base.css` à spécificité égale.
 * 2. Le corps est rendu en mémoire, donc tout ce qu'il enfile est connu avant `wp_head()`.
 * 3. Le document est écrit, tampon compris.
 *
 * @param string   $classe_de_page Classes ajoutées à `<body>`, séparées par des espaces.
 * @param callable $corps          Fonction qui imprime le contenu de `<main>`.
 */
function mtb_fiche_rendre_le_document( string $classe_de_page, callable $corps ): void {
	if ( function_exists( 'mtb_feuilles_du_site' ) ) {
		mtb_feuilles_du_site();
	}

	if ( function_exists( 'mtb_mettre_feuille_en_file' ) ) {
		mtb_mettre_feuille_en_file( 'mtb-fiches', 'assets/css/fiches.css', array( 'mtb-jetons' ) );
	}

	ob_start();

	/*
	 * `do_blocks()` et jamais `block_template_part()` : cette dernière rend le contenu de la partie
	 * sans son enveloppe — ni `<header>`, ni `<footer>`, ni `class="wp-block-template-part"`. Les
	 * deux points de repère du site disparaîtraient et `entete-pied.css` ne s'accrocherait plus.
	 * Le passage par `do_blocks()` va jusqu'à `render_block()`, donc les filtres qui nomment les
	 * deux navigations s'appliquent comme sur les gabarits de blocs.
	 */
	echo do_blocks( '<!-- wp:template-part {"slug":"header","tagName":"header"} /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	/*
	 * `tabindex="-1"` est écrit littéralement : le filtre du thème qui le pose au rendu est gardé
	 * sur l'ancre d'un bloc, et un `<main>` écrit en PHP n'est pas un bloc. Sans cet attribut,
	 * suivre « Aller au contenu » déplace la vue et laisse le focus derrière.
	 */
	echo '<main id="contenu" tabindex="-1" class="mtb-canal">';

	$corps();

	echo '</main>';

	echo do_blocks( '<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	$document = (string) ob_get_clean();

	?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo esc_html( wp_get_document_title() ); ?></title>
<?php wp_head(); ?>
</head>
<body <?php body_class( $classe_de_page ); ?>>
<?php wp_body_open(); ?>
<?php
	// Assemblé pièce par pièce ci-dessus, chaque valeur du domaine déjà échappée à son point d'écriture.
	echo $document; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	wp_footer();
	?>
</body>
</html>
	<?php
}

/**
 * Rend l'encart d'une page protégée par mot de passe : étiquette, phrase, formulaire.
 *
 * Un seul formulaire sur la page : `the_content()` n'est jamais appelé sur un contenu protégé,
 * puisqu'il rendrait ce même formulaire une seconde fois.
 *
 * Les deux chaînes sont reprises mot pour mot de `design-system/MASTER.md` §9.5 et §10.3. Elles ne
 * disent rien du contenu protégé — c'est ce que §9.5 exige.
 */
function mtb_fiche_rendre_l_encart_protege(): void {
	?>
	<section class="mtb-encart-protege">
		<p class="mtb-encart-protege__etiquette">Page protégée</p>
		<p class="mtb-encart-protege__phrase">Cette page est réservée. Saisissez le mot de passe communiqué par l'élevage.</p>
		<?php echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</section>
	<?php
}

/**
 * Rend une galerie à partir d'identifiants de photos, par le composant du catalogue.
 *
 * `render_block()` est la seule voie conforme : la fonction de rendu du composant vit dans
 * l'espace de noms de l'extension, que le thème n'a pas le droit d'appeler. Ce chemin déclenche
 * en outre la mise en file de la feuille du composant.
 *
 * @param array<int,array<string,mixed>> $photos Photos telles que la fonction de lecture les rend.
 *
 * @return string Balisage de la galerie, ou chaîne vide s'il n'y a rien à montrer.
 */
function mtb_fiche_galerie_rendue( array $photos ): string {
	$identifiants = array();

	foreach ( $photos as $photo ) {
		$identifiant = is_array( $photo ) && isset( $photo['id'] ) ? (int) $photo['id'] : 0;

		if ( $identifiant > 0 ) {
			$identifiants[] = $identifiant;
		}
	}

	if ( array() === $identifiants ) {
		return '';
	}

	return trim(
		render_block(
			array(
				'blockName'    => 'mtb/galerie-photos',
				'attrs'        => array( 'photos' => $identifiants ),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			)
		)
	);
}

/**
 * Imprime la section d'une galerie : le titre, puis le composant.
 *
 * Écrite une seule fois pour les deux fiches. La garde est la chaîne rendue et rien d'autre : une
 * galerie vide fait rendre la chaîne vide au composant, et ni le titre ni la section n'existent
 * alors. Deux écritures de cette garde finiraient par diverger, et l'une des deux laisserait un
 * titre orphelin.
 *
 * @param array<int,array<string,mixed>> $photos Photos telles que la fonction de lecture les rend.
 * @param string                         $classe Attribut de classe de la section.
 * @param string                         $titre  Titre de section, repris de `MASTER.md`.
 */
function mtb_fiche_rendre_la_galerie( array $photos, string $classe, string $titre ): void {
	$galerie = mtb_fiche_galerie_rendue( $photos );

	if ( '' === $galerie ) {
		return;
	}
	?>
	<section class="<?php echo esc_attr( $classe ); ?>">
		<h2><?php echo esc_html( $titre ); ?></h2>
		<?php echo $galerie; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — balisage du composant, échappé pièce par pièce par l'extension. ?>
	</section>
	<?php
}

/**
 * Imprime le commentaire de l'éleveuse, sans titre de section.
 *
 * C'est du contenu WordPress natif, pas un champ du domaine : il passe par les filtres du cœur,
 * donc par les blocs et par les compositions. Les deux types de contenu le portent — `mtb_portee`
 * et `mtb_chien` déclarent tous deux `supports => editor` — et l'écran de saisie promet à
 * l'éleveuse qu'il « s'affiche sur sa fiche, sur le site ». Une seule écriture pour les deux, afin
 * que la garde du vide et l'échappement ne puissent pas diverger d'une fiche à l'autre.
 *
 * Jamais appelée sur un contenu protégé : l'appelant sort avant, sans quoi `the_content` rendrait
 * un second formulaire de mot de passe.
 *
 * @param string $classe Crochet de classe de la section.
 */
function mtb_fiche_rendre_le_commentaire( string $classe ): void {
	$saisi = (string) get_the_content();

	if ( '' === trim( $saisi ) ) {
		return;
	}

	$prose = trim( (string) apply_filters( 'the_content', $saisi ) );

	if ( '' === $prose ) {
		return;
	}
	?>
	<section class="<?php echo esc_attr( $classe ); ?>">
		<?php echo wp_kses_post( $prose ); ?>
	</section>
	<?php
}

/**
 * Imprime un champ enveloppé sous forme de ligne de liste de définition.
 *
 * Le libellé et l'affichage viennent tous deux du serveur : cette fonction n'en compose aucun et
 * n'accorde rien. Une valeur absente vaut déjà « Non renseigné » à l'arrivée.
 *
 * @param string               $libelle Libellé public du champ.
 * @param array<string,string> $champ   Champ enveloppé (`libelle`, `valeur`, `affichage`).
 */
function mtb_fiche_imprimer_la_ligne( string $libelle, array $champ ): void {
	$affichage = isset( $champ['affichage'] ) ? (string) $champ['affichage'] : '';
	$valeur    = isset( $champ['valeur'] ) ? (string) $champ['valeur'] : '';
	$doux      = '' === $valeur ? ' mtb-etat-doux' : '';

	?>
	<dt><?php echo esc_html( $libelle ); ?></dt>
	<dd class="mtb-fiche__valeur<?php echo esc_attr( $doux ); ?>"><?php echo esc_html( $affichage ); ?></dd>
	<?php
}
