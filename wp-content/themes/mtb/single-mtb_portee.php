<?php
/**
 * Fiche d'une portée — `design-system/MASTER.md` §7.4.
 *
 * Ce gabarit l'emporte sur `templates/singular.html` : quand `locate_template()` trouve un `.php`,
 * `locate_block_template()` tronque la liste des gabarits de blocs candidats à ceux qui sont au
 * moins aussi spécifiques, et aucun `wp_template` ne porte ces slugs.
 *
 * Aucune règle du domaine n'est écrite ici : les libellés, les dates formatées, l'effectif, les
 * trois états de disponibilité et la phrase de la liste des chiots absente viennent tous de la
 * fonction de lecture de l'extension. Ce fichier choisit seulement **quand** chaque chose
 * s'imprime.
 *
 * @package MTB
 */

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

require_once get_theme_file_path( 'enveloppe-fiche.php' );

/**
 * Imprime le corps de la fiche, dans l'ordre imposé par §7.4.
 *
 * @param array<string,mixed>|null $portee Portée hydratée, ou null.
 */
function mtb_portee_rendre_le_corps( ?array $portee ): void {
	/*
	 * Extension absente, ou contenu que la fonction de lecture refuse : le titre, et rien d'autre.
	 * Aucun champ n'est cherché ailleurs — une fiche à moitié devinée serait pire qu'une fiche nue.
	 */
	if ( null === $portee ) {
		?>
		<header class="mtb-fiche-portee__identite">
			<h1><?php echo esc_html( get_the_title() ); ?></h1>
		</header>
		<?php
		return;
	}

	$etat = isset( $portee['etat'] ) ? (string) $portee['etat'] : '';

	/*
	 * Contenu protégé : la charge utile ne porte aucun champ du domaine, pas même vide. Le titre
	 * vient donc du cœur, avec son préfixe « Protégé : » — constaté, jamais contourné : le
	 * recomposer serait fabriquer une chaîne du domaine.
	 */
	if ( 'page_protegee' === $etat ) {
		?>
		<header class="mtb-fiche-portee__identite">
			<h1><?php echo esc_html( get_the_title() ); ?></h1>
		</header>
		<?php
		mtb_fiche_rendre_l_encart_protege();

		return;
	}

	mtb_portee_rendre_l_identite( $portee );
	mtb_portee_rendre_les_parents( $portee );
	mtb_portee_rendre_les_chiots( $portee );
	// §7.4-4 — le commentaire de l'éleveuse, sans titre de section.
	mtb_fiche_rendre_le_commentaire( 'mtb-fiche-portee__commentaire' );
	mtb_portee_rendre_la_galerie( $portee );
	mtb_portee_rendre_les_voisines( isset( $portee['id'] ) ? (int) $portee['id'] : 0 );
}

/**
 * §7.4-1 — identité : titre, date de naissance, badge de disponibilité, effectif.
 *
 * @param array<string,mixed> $portee Portée hydratée.
 */
function mtb_portee_rendre_l_identite( array $portee ): void {
	$date      = isset( $portee['date_naissance'] ) && is_array( $portee['date_naissance'] ) ? $portee['date_naissance'] : array();
	$dispo     = isset( $portee['disponibilite'] ) && is_array( $portee['disponibilite'] ) ? $portee['disponibilite'] : array();
	$dispo_cle = isset( $dispo['valeur'] ) ? (string) $dispo['valeur'] : '';
	$effectif  = isset( $portee['effectif_texte'] ) ? (string) $portee['effectif_texte'] : '';

	// Le repli en tableau vide ci-dessus n'a de sens que si chaque clé est ensuite lue de même.
	$date_valeur    = isset( $date['valeur'] ) ? (string) $date['valeur'] : '';
	$date_libelle   = isset( $date['libelle'] ) ? (string) $date['libelle'] : '';
	$date_affichage = isset( $date['affichage'] ) ? (string) $date['affichage'] : '';
	?>
	<header class="mtb-fiche-portee__identite">
		<h1><?php echo esc_html( isset( $portee['titre_public'] ) ? (string) $portee['titre_public'] : '' ); ?></h1>

		<p class="mtb-fiche-portee__date">
			<?php if ( '' !== $date_valeur ) : ?>
				<?php /* L'étiquette est un fragment de phrase : « Née le Non renseigné » n'est pas du français, et elle ne s'imprime donc qu'avec sa date. La valeur, elle, s'imprime toujours — une date absente doit rester discernable d'un défaut de rendu. L'attribut reçoit la valeur brute AAAA-MM-JJ, sans aucun reformatage. */ ?>
				<span class="mtb-fiche-portee__etiquette"><?php echo esc_html( $date_libelle ); ?></span>
				<time class="mtb-fiche-portee__date-valeur" datetime="<?php echo esc_attr( $date_valeur ); ?>"><?php echo esc_html( $date_affichage ); ?></time>
			<?php else : ?>
				<span class="mtb-fiche-portee__date-valeur mtb-etat-doux"><?php echo esc_html( $date_affichage ); ?></span>
			<?php endif; ?>
		</p>

		<?php if ( '' !== $dispo_cle ) : ?>
			<?php // Aucune disponibilité choisie : aucun badge, en silence. Un quatrième état n'existe pas. ?>
			<p class="mtb-fiche-portee__dispo mtb-dispo mtb-dispo--<?php echo esc_attr( $dispo_cle ); ?>"><?php echo esc_html( isset( $dispo['affichage'] ) ? (string) $dispo['affichage'] : '' ); ?></p>
		<?php endif; ?>

		<?php if ( '' !== $effectif ) : ?>
			<p class="mtb-fiche-portee__effectif"><?php echo esc_html( $effectif ); ?></p>
		<?php endif; ?>
	</header>
	<?php
}

/**
 * §7.4-2 — les parents. Un parent dont rien n'est connu n'a pas de carte ; les deux absents
 * emportent la section entière.
 *
 * @param array<string,mixed> $portee Portée hydratée.
 */
function mtb_portee_rendre_les_parents( array $portee ): void {
	$parents = array();

	foreach ( array( 'pere', 'mere' ) as $branche ) {
		$parent = isset( $portee[ $branche ] ) && is_array( $portee[ $branche ] ) ? $portee[ $branche ] : array();

		if ( isset( $parent['etat'] ) && 'donnee_absente' !== $parent['etat'] ) {
			$parents[] = $parent;
		}
	}

	if ( array() === $parents ) {
		return;
	}
	?>
	<section class="mtb-fiche-portee__parents">
		<h2>Les parents</h2>

		<?php // role="list" : la feuille retire les puces, et Safari retire alors la sémantique de liste. ?>
		<ul class="mtb-cartes-parents" role="list">
			<?php foreach ( $parents as $parent ) : ?>
				<?php mtb_portee_rendre_une_carte_parent( $parent ); ?>
			<?php endforeach; ?>
		</ul>
	</section>
	<?php
}

/**
 * Une carte parent. Un étalon extérieur affiche son nom et son élevage, sans lien.
 *
 * @param array<string,mixed> $parent Parent hydraté.
 */
function mtb_portee_rendre_une_carte_parent( array $parent ): void {
	$nom     = isset( $parent['nom'] ) && is_array( $parent['nom'] ) ? $parent['nom'] : array();
	$elevage = isset( $parent['elevage'] ) && is_array( $parent['elevage'] ) ? $parent['elevage'] : array();
	$sante   = isset( $parent['sante'] ) && is_array( $parent['sante'] ) ? $parent['sante'] : array();
	$lien    = isset( $parent['lien'] ) ? (string) $parent['lien'] : '';

	// Le repli en tableau vide ci-dessus n'a de sens que si chaque clé est ensuite lue de même.
	$nom_affichage     = isset( $nom['affichage'] ) ? (string) $nom['affichage'] : '';
	$elevage_affichage = isset( $elevage['affichage'] ) ? (string) $elevage['affichage'] : '';
	$sante_affichage   = isset( $sante['affichage'] ) ? (string) $sante['affichage'] : '';
	$sante_libelle     = isset( $sante['libelle'] ) ? (string) $sante['libelle'] : '';
	?>
	<li class="mtb-carte-parent">
		<p class="mtb-carte-parent__role"><?php echo esc_html( isset( $parent['libelle'] ) ? (string) $parent['libelle'] : '' ); ?></p>
		<h3 class="mtb-carte-parent__nom"><?php echo esc_html( $nom_affichage ); ?></h3>

		<?php if ( '' !== $elevage_affichage ) : ?>
			<p class="mtb-carte-parent__elevage"><?php echo esc_html( $elevage_affichage ); ?></p>
		<?php endif; ?>

		<?php if ( '' !== $sante_affichage ) : ?>
			<dl class="mtb-carte-parent__sante">
				<dt><?php echo esc_html( $sante_libelle ); ?></dt>
				<dd><?php echo esc_html( $sante_affichage ); ?></dd>
			</dl>
		<?php endif; ?>

		<?php if ( 'fiche' === ( isset( $parent['etat'] ) ? (string) $parent['etat'] : '' ) && '' !== $lien ) : ?>
			<p class="mtb-carte-parent__acces">
				<a class="mtb-carte-parent__lien" href="<?php echo esc_url( $lien ); ?>">Voir la fiche</a>
			</p>
		<?php endif; ?>
	</li>
	<?php
}

/**
 * §7.4-3 — les chiots. Le titre reste quand la liste n'est pas saisie ; la phrase qui le dit vient
 * du serveur.
 *
 * @param array<string,mixed> $portee Portée hydratée.
 */
function mtb_portee_rendre_les_chiots( array $portee ): void {
	$chiots   = isset( $portee['chiots'] ) && is_array( $portee['chiots'] ) ? $portee['chiots'] : array();
	$colonnes = isset( $portee['chiots_colonnes'] ) && is_array( $portee['chiots_colonnes'] ) ? $portee['chiots_colonnes'] : array();
	$message  = isset( $portee['chiots_message'] ) ? (string) $portee['chiots_message'] : '';
	$a_chiots = array() !== $chiots;
	$classes  = 'mtb-fiche-portee__chiots' . ( $a_chiots ? ' alignwide' : '' );
	?>
	<section class="<?php echo esc_attr( $classes ); ?>">
		<h2>Les chiots</h2>

		<?php if ( ! $a_chiots ) : ?>
			<p class="mtb-etat-doux"><?php echo esc_html( $message ); ?></p>
		<?php else : ?>
			<?php /* `mtb-tableau` est la primitive nommée par §7.6 : sans elle, le dépliage en lignes libellées ne l'atteint pas et le tableau déborde à 360 px. Le canal large de §7.4-3 est porté par la <section> et non par le `<table>` : `base.css` ne vise que `.mtb-canal > .alignwide`, et un petit-fils n'est jamais atteint. Il n'est porté que lorsque le tableau existe, car la même `<section>` porte l'état vide, que §9.3 range en canal texte. */ ?>
			<table class="mtb-tableau mtb-tableau--chiots">
				<thead>
					<tr>
						<?php foreach ( $colonnes as $colonne ) : ?>
							<th scope="col"><?php echo esc_html( (string) $colonne ); ?></th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $chiots as $chiot ) : ?>
						<tr>
							<?php foreach ( (array) $chiot as $cellule ) : ?>
								<?php
								/*
								 * Le libellé est lu sur la cellule elle-même, jamais sur l'indice de
								 * la colonne : un décalage d'ordre resterait alors sans effet.
								 */
								$libelle   = isset( $cellule['libelle'] ) ? (string) $cellule['libelle'] : '';
								$affichage = isset( $cellule['affichage'] ) ? (string) $cellule['affichage'] : '';
								?>
								<td data-libelle="<?php echo esc_attr( $libelle ); ?>"><?php echo esc_html( $affichage ); ?></td>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</section>
	<?php
}

/**
 * §7.4-5 — la galerie. Vide, le composant rend la chaîne vide : ni titre, ni section.
 *
 * @param array<string,mixed> $portee Portée hydratée.
 */
function mtb_portee_rendre_la_galerie( array $portee ): void {
	$photos = isset( $portee['galerie'] ) && is_array( $portee['galerie'] ) ? $portee['galerie'] : array();

	mtb_fiche_rendre_la_galerie( $photos, 'mtb-fiche-portee__galerie alignwide', 'La galerie' );
}

/**
 * §7.4-6 — navigation entre portées, par date de naissance.
 *
 * Une portée sans date n'a ni précédente ni suivante : la fonction de lecture le décide, et le
 * lien correspondant n'existe alors simplement pas. Ni lien désactivé, ni mention.
 *
 * @param int $id Identifiant de la portée courante.
 */
function mtb_portee_rendre_les_voisines( int $id ): void {
	if ( ! function_exists( 'mtb_get_portee_voisine' ) || $id <= 0 ) {
		return;
	}

	$precedente = mtb_get_portee_voisine( $id, 'precedente' );
	$suivante   = mtb_get_portee_voisine( $id, 'suivante' );

	$lien_precedent = is_array( $precedente ) && isset( $precedente['lien'] ) ? (string) $precedente['lien'] : '';
	$lien_suivant   = is_array( $suivante ) && isset( $suivante['lien'] ) ? (string) $suivante['lien'] : '';

	if ( '' === $lien_precedent && '' === $lien_suivant ) {
		return;
	}
	?>
	<nav class="mtb-fiche-portee__voisines" aria-label="Portées">
		<?php if ( '' !== $lien_precedent ) : ?>
			<a class="mtb-fiche-portee__voisine mtb-fiche-portee__voisine--precedente" rel="prev" href="<?php echo esc_url( $lien_precedent ); ?>">Portée précédente</a>
		<?php endif; ?>

		<?php if ( '' !== $lien_suivant ) : ?>
			<a class="mtb-fiche-portee__voisine mtb-fiche-portee__voisine--suivante" rel="next" href="<?php echo esc_url( $lien_suivant ); ?>">Portée suivante</a>
		<?php endif; ?>
	</nav>
	<?php
}

if ( have_posts() ) {
	the_post();
}

$mtb_portee_courante = function_exists( 'mtb_get_portee' ) ? mtb_get_portee( (int) get_the_ID() ) : null;

mtb_fiche_rendre_le_document(
	'mtb-fiche mtb-fiche--portee',
	static function () use ( $mtb_portee_courante ): void {
		mtb_portee_rendre_le_corps( $mtb_portee_courante );
	}
);
