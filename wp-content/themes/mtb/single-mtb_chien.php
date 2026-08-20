<?php
/**
 * Fiche d'un chien — `design-system/MASTER.md` §7.5.
 *
 * Comme la fiche de portée, ce gabarit l'emporte sur `templates/singular.html` par la troncature
 * que `locate_block_template()` applique dès que `locate_template()` trouve un `.php`.
 *
 * Deux sections — Santé, Titres et brevets — sont rendues ou non sur la seule foi d'un signal du
 * serveur (`sante_renseignee`, `titres_renseignes`). Le gabarit n'inspecte jamais les champs pour
 * décider : décider qu'une section est vide est une affaire de serveur.
 *
 * Les libellés d'identité sont écrits ici faute d'être fournis par la fonction de lecture, et sont
 * repris mot pour mot de la colonne « Libellé côté public » de `MASTER.md` §10.2 — dette T29 : un
 * libellé qui changerait là-bas devrait changer ici aussi.
 *
 * @package MTB
 */

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

require_once get_theme_file_path( 'enveloppe-fiche.php' );

/**
 * Imprime le corps de la fiche, dans l'ordre imposé par §7.5.
 *
 * @param array<string,mixed>|null $chien Fiche hydratée, ou null.
 */
function mtb_chien_rendre_le_corps( ?array $chien ): void {
	/*
	 * La fonction de lecture rend null sur une fiche qui n'est pas publiée — l'aperçu d'un
	 * brouillon n'a donc aucune donnée. Le titre s'affiche, et rien d'autre : aucun champ n'est
	 * relu ailleurs, une fiche à moitié devinée serait pire qu'une fiche nue.
	 */
	if ( null === $chien ) {
		?>
		<header class="mtb-fiche-chien__identite">
			<h1><?php echo esc_html( get_the_title() ); ?></h1>
		</header>
		<?php
		return;
	}

	$nom_usage = isset( $chien['nom_usage'] ) ? (string) $chien['nom_usage'] : '';
	?>
	<header class="mtb-fiche-chien__identite">
		<h1><?php echo esc_html( $nom_usage ); ?></h1>

		<?php if ( '' !== (string) $chien['nom_complet']['valeur'] ) : ?>
			<?php // Sous-titre et non ligne de liste : « Non renseigné » sous le nom d'un chien serait du bruit. ?>
			<p class="mtb-fiche-chien__nom-complet"><?php echo esc_html( (string) $chien['nom_complet']['affichage'] ); ?></p>
		<?php endif; ?>
	</header>
	<?php

	if ( 'page_protegee' === (string) $chien['etat'] ) {
		mtb_fiche_rendre_l_encart_protege();

		return;
	}

	mtb_chien_rendre_la_presentation( $chien, $nom_usage );
	mtb_chien_rendre_la_sante( $chien );
	mtb_chien_rendre_les_titres( $chien );
	mtb_chien_rendre_le_palmares();
	mtb_chien_rendre_les_portees( isset( $chien['id'] ) ? (int) $chien['id'] : 0 );

	/*
	 * Le commentaire de l'éleveuse. `MASTER.md` §7.5 ne l'énumère pas, là où §7.4-4 le prévoit sur
	 * une portée ; le type de contenu, lui, déclare `supports => editor` et l'écran de saisie
	 * promet à l'éleveuse que ce texte « s'affiche sur sa fiche, sur le site ». Sans cette section,
	 * un texte saisi et enregistré ne se voit nulle part — la perte silencieuse que la contrainte 4
	 * interdit. Placé comme sur la portée : après les données, avant la galerie.
	 */
	mtb_fiche_rendre_le_commentaire( 'mtb-fiche-chien__commentaire' );

	mtb_chien_rendre_la_galerie( $chien );
	mtb_chien_rendre_le_pedigree( $chien );
}

/**
 * §7.5-2 — portrait et liste d'identité, deux colonnes au-delà de `--bp-fiche`.
 *
 * @param array<string,mixed> $chien     Fiche hydratée.
 * @param string              $nom_usage Nom d'usage, employé au centre du cadre sans photo.
 */
function mtb_chien_rendre_la_presentation( array $chien, string $nom_usage ): void {
	?>
	<div class="mtb-fiche-chien__presentation alignwide">
		<?php mtb_chien_rendre_le_portrait( $chien, $nom_usage ); ?>

		<dl class="mtb-fiche-chien__champs">
			<?php
			mtb_fiche_imprimer_la_ligne( 'Sexe', (array) $chien['sexe'] );
			mtb_fiche_imprimer_la_ligne( 'Variété', (array) $chien['variete'] );
			mtb_fiche_imprimer_la_ligne( (string) $chien['date_naissance']['libelle'], (array) $chien['date_naissance'] );

			/*
			 * Le vide de la date de décès EST l'information : « Décédée le Non renseigné » sur un
			 * chien vivant serait un fait d'élevage faux, pas une donnée absente.
			 */
			if ( '' !== (string) $chien['date_deces']['valeur'] ) {
				mtb_fiche_imprimer_la_ligne( (string) $chien['date_deces']['libelle'], (array) $chien['date_deces'] );
			}

			mtb_fiche_imprimer_la_ligne( 'Statut', (array) $chien['statut'] );
			mtb_fiche_imprimer_la_ligne( 'Taille', (array) $chien['taille'] );
			mtb_fiche_imprimer_la_ligne( 'Couleur', (array) $chien['couleur'] );
			mtb_fiche_imprimer_la_ligne( 'Masque', (array) $chien['masque'] );
			mtb_fiche_imprimer_la_ligne( 'Génétique de robe', (array) $chien['genetique_robe'] );

			/*
			 * La filiation : `BRIEF.md` §5.2 inscrit « Père × Mère » parmi les champs d'un Chien.
			 * Deux lignes de la liste d'identité, sans titre de section nouveau. Le libellé vient du
			 * serveur (`libelle`), donc rien n'est recopié ici.
			 *
			 * Sur une fiche chien, un parent se lit sur `type` — jamais sur `etat`, qui est le
			 * vocabulaire de la portée. Les deux formes décrivent la même notion et ne se
			 * ressemblent pas.
			 */
			mtb_chien_rendre_le_parent( (array) $chien['pere'] );
			mtb_chien_rendre_le_parent( (array) $chien['mere'] );
			?>
		</dl>
	</div>
	<?php
}

/**
 * Une ligne de filiation : le parent en lien quand il a une fiche consultable, sinon en clair.
 *
 * Un parent dont la fiche existe mais n'est pas publiée conserve son nom sans lien — c'est le
 * serveur qui le décide, en ne rendant pas de lien : un fait de généalogie reste vrai, et rien du
 * contenu réservé ne sort. Le gabarit ne teste jamais l'état de publication lui-même.
 *
 * @param array<string,mixed> $parent Parent tel que la fonction de lecture le rend.
 */
function mtb_chien_rendre_le_parent( array $parent ): void {
	$libelle   = isset( $parent['libelle'] ) ? (string) $parent['libelle'] : '';
	$type      = isset( $parent['type'] ) ? (string) $parent['type'] : '';
	$lien      = isset( $parent['lien'] ) ? (string) $parent['lien'] : '';
	$affichage = isset( $parent['affichage'] ) ? (string) $parent['affichage'] : '';
	$elevage   = isset( $parent['elevage'] ) ? (string) $parent['elevage'] : '';
	$doux      = 'non_renseigne' === $type ? ' mtb-etat-doux' : '';
	?>
	<dt><?php echo esc_html( $libelle ); ?></dt>
	<dd class="mtb-fiche__valeur<?php echo esc_attr( $doux ); ?>">
		<?php if ( '' !== $lien ) : ?>
			<a class="mtb-fiche-chien__parent-lien" href="<?php echo esc_url( $lien ); ?>"><?php echo esc_html( $affichage ); ?></a>
		<?php else : ?>
			<span class="mtb-fiche-chien__parent-nom"><?php echo esc_html( $affichage ); ?></span>
		<?php endif; ?>

		<?php if ( '' !== $elevage ) : ?>
			<span class="mtb-fiche-chien__parent-elevage"><?php echo esc_html( $elevage ); ?></span>
		<?php endif; ?>
	</dd>
	<?php
}

/**
 * Le portrait, ou l'emplacement structurant qui garde sa place quand la photo manque.
 *
 * @param array<string,mixed> $chien     Fiche hydratée.
 * @param string              $nom_usage Nom d'usage du chien.
 */
function mtb_chien_rendre_le_portrait( array $chien, string $nom_usage ): void {
	$photo = isset( $chien['photo_principale'] ) && is_array( $chien['photo_principale'] ) ? $chien['photo_principale'] : null;

	if ( null === $photo ) {
		?>
		<div class="mtb-fiche-chien__portrait mtb-fiche-chien__portrait--vide mtb-photo">
			<p class="mtb-fiche-chien__portrait-nom"><?php echo esc_html( $nom_usage ); ?></p>
		</div>
		<?php
		return;
	}

	$cadrage = isset( $chien['cadrage']['valeur'] ) ? (string) $chien['cadrage']['valeur'] : '';
	?>
	<figure class="mtb-fiche-chien__portrait mtb-photo" data-cadrage="<?php echo esc_attr( $cadrage ); ?>">
		<?php
		echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — la fonction du cœur échappe chacun de ses attributs.
			(int) $photo['id'],
			'medium_large',
			false,
			array(
				'class'    => 'mtb-fiche-chien__photo',
				/*
				 * Alternative passée brute : `wp_get_attachment_image()` échappe elle-même, et la
				 * pré-échapper ferait lire « &#039; » à un lecteur d'écran.
				 */
				'alt'      => isset( $photo['alt'] ) ? (string) $photo['alt'] : '',
				/*
				 * « 22rem » est la borne haute de la colonne de portrait de §6.8, et « 36rem » la
				 * largeur du canal texte : deux valeurs qui vivent aussi dans `fiches.css`.
				 */
				'sizes'    => '(min-width: 64rem) 352px, (min-width: 40rem) 576px, 90vw',
				/*
				 * Explicites : sans eux, le cœur peut désigner cette image comme image principale
				 * et lui poser « fetchpriority=high », que §6.5 réserve au bandeau d'ouverture.
				 */
				'loading'  => 'lazy',
				'decoding' => 'async',
			)
		);
		?>
	</figure>
	<?php
}

/**
 * §7.5-3 — Santé. La section entière disparaît sur décision du serveur.
 *
 * @param array<string,mixed> $chien Fiche hydratée.
 */
function mtb_chien_rendre_la_sante( array $chien ): void {
	if ( true !== $chien['sante_renseignee'] ) {
		return;
	}
	?>
	<section class="mtb-fiche-chien__sante">
		<h2>Santé</h2>

		<dl class="mtb-fiche-chien__champs">
			<?php foreach ( (array) $chien['sante'] as $champ ) : ?>
				<?php mtb_fiche_imprimer_la_ligne( isset( $champ['libelle'] ) ? (string) $champ['libelle'] : '', (array) $champ ); ?>
			<?php endforeach; ?>
		</dl>

		<?php mtb_chien_rendre_les_lignes_libres( (array) $chien['autres_tests'], 'mtb-fiche-chien__autres-tests' ); ?>
	</section>
	<?php
}

/**
 * §7.5-4 — Titres et brevets. Même mécanique que Santé.
 *
 * @param array<string,mixed> $chien Fiche hydratée.
 */
function mtb_chien_rendre_les_titres( array $chien ): void {
	if ( true !== $chien['titres_renseignes'] ) {
		return;
	}
	?>
	<section class="mtb-fiche-chien__titres">
		<h2>Titres et brevets</h2>

		<dl class="mtb-fiche-chien__champs">
			<?php foreach ( (array) $chien['titres'] as $champ ) : ?>
				<?php mtb_fiche_imprimer_la_ligne( isset( $champ['libelle'] ) ? (string) $champ['libelle'] : '', (array) $champ ); ?>
			<?php endforeach; ?>
		</dl>

		<?php mtb_chien_rendre_les_lignes_libres( (array) $chien['autres_titres'], 'mtb-fiche-chien__autres-titres' ); ?>
	</section>
	<?php
}

/**
 * Les lignes d'un champ libre, recopiées une à une.
 *
 * Aucun libellé n'est imprimé au-dessus : la fonction de lecture n'en fournit pas, et le vocabulaire
 * de §10.2 n'en atteste aucun côté public.
 *
 * @param array<int,string> $lignes  Lignes utiles, déjà découpées par le serveur.
 * @param string            $classe  Crochet de classe de la liste.
 */
function mtb_chien_rendre_les_lignes_libres( array $lignes, string $classe ): void {
	if ( array() === $lignes ) {
		return;
	}
	?>
	<ul class="<?php echo esc_attr( $classe ); ?>" role="list">
		<?php foreach ( $lignes as $ligne ) : ?>
			<li><?php echo esc_html( (string) $ligne ); ?></li>
		<?php endforeach; ?>
	</ul>
	<?php
}

/**
 * §7.5-5 — Palmarès de travail.
 *
 * La chaîne rendue est la garde unique : deux gardes indépendantes — une sur les données, une sur
 * le balisage — produiraient un titre orphelin le jour où le composant manque.
 */
function mtb_chien_rendre_le_palmares(): void {
	/*
	 * Le commentaire de bloc, et non l'appel de fonction : c'est le passage par `render_block()`
	 * qui déclenche le filtre sur lequel `wp_enqueue_block_style()` accroche la feuille du
	 * composant. Un appel direct la laisserait absente de la page. Le corps de la fiche étant rendu
	 * en mémoire avant `wp_head()`, l'enfilage arrive à temps.
	 */
	// ▼ DÉPENDANCE #15 — seule ligne à changer si le composant fige une autre interface.
	$palmares = trim( do_blocks( '<!-- wp:mtb/tableau-resultats {"source":"chien-courant"} /-->' ) );
	// ▲

	if ( '' === $palmares ) {
		return;
	}
	?>
	<section class="mtb-fiche-chien__palmares alignwide">
		<h2>Palmarès de travail</h2>
		<?php echo $palmares; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — balisage du composant, échappé pièce par pièce par l'extension. ?>
	</section>
	<?php
}

/**
 * §7.5-6 — les portées où ce chien est père ou mère, en cartes.
 *
 * Aucune photo : la fonction de lecture n'en rend pas, et aucune lecture supplémentaire n'est faite
 * pour en obtenir une.
 *
 * @param int $id Identifiant de la fiche.
 */
function mtb_chien_rendre_les_portees( int $id ): void {
	$portees = function_exists( 'mtb_get_portees_du_chien' ) && $id > 0 ? mtb_get_portees_du_chien( $id ) : array();

	if ( array() === $portees ) {
		return;
	}
	?>
	<section class="mtb-fiche-chien__portees alignwide">
		<h2>Portées</h2>

		<ul class="mtb-cartes-portees" role="list">
			<?php foreach ( $portees as $portee ) : ?>
				<?php mtb_chien_rendre_une_carte_portee( (array) $portee ); ?>
			<?php endforeach; ?>
		</ul>
	</section>
	<?php
}

/**
 * Une carte de portée.
 *
 * Le texte du lien est l'identifiant de la portée : c'est un nom accessible distinctif, fourni fini
 * par le serveur, là où vingt liens « Voir la portée » n'en seraient pas un.
 *
 * @param array<string,mixed> $portee Élément de liste hydraté.
 */
function mtb_chien_rendre_une_carte_portee( array $portee ): void {
	$identifiant = isset( $portee['identifiant'] ) ? (string) $portee['identifiant'] : '';
	$lien        = isset( $portee['lien'] ) ? (string) $portee['lien'] : '';
	$date        = isset( $portee['date_naissance'] ) && is_array( $portee['date_naissance'] ) ? $portee['date_naissance'] : array();
	$dispo       = isset( $portee['disponibilite'] ) && is_array( $portee['disponibilite'] ) ? $portee['disponibilite'] : array();
	$role        = isset( $portee['role'] ) && is_array( $portee['role'] ) ? $portee['role'] : array();
	$dispo_cle   = isset( $dispo['valeur'] ) ? (string) $dispo['valeur'] : '';

	// Le repli en tableau vide ci-dessus n'a de sens que si chaque clé est ensuite lue de même.
	$date_valeur    = isset( $date['valeur'] ) ? (string) $date['valeur'] : '';
	$date_libelle   = isset( $date['libelle'] ) ? (string) $date['libelle'] : '';
	$date_affichage = isset( $date['affichage'] ) ? (string) $date['affichage'] : '';
	?>
	<li class="mtb-carte-portee">
		<h3 class="mtb-carte-portee__titre">
			<?php if ( '' !== $lien ) : ?>
				<a class="mtb-carte-portee__lien" href="<?php echo esc_url( $lien ); ?>"><?php echo esc_html( $identifiant ); ?></a>
			<?php else : ?>
				<?php echo esc_html( $identifiant ); ?>
			<?php endif; ?>
		</h3>

		<?php if ( isset( $role['valeur'] ) && '' !== (string) $role['valeur'] ) : ?>
			<p class="mtb-carte-portee__role"><?php echo esc_html( isset( $role['affichage'] ) ? (string) $role['affichage'] : '' ); ?></p>
		<?php endif; ?>

		<p class="mtb-carte-portee__date">
			<?php if ( '' !== $date_valeur ) : ?>
				<?php /* Même règle que sur la fiche de portée : l'étiquette ne s'imprime qu'avec sa date. */ ?>
				<span class="mtb-carte-portee__etiquette"><?php echo esc_html( $date_libelle ); ?></span>
				<time datetime="<?php echo esc_attr( $date_valeur ); ?>"><?php echo esc_html( $date_affichage ); ?></time>
			<?php else : ?>
				<span class="mtb-etat-doux"><?php echo esc_html( $date_affichage ); ?></span>
			<?php endif; ?>
		</p>

		<?php if ( '' !== $dispo_cle ) : ?>
			<p class="mtb-carte-portee__dispo mtb-dispo mtb-dispo--<?php echo esc_attr( $dispo_cle ); ?>"><?php echo esc_html( isset( $dispo['affichage'] ) ? (string) $dispo['affichage'] : '' ); ?></p>
		<?php endif; ?>
	</li>
	<?php
}

/**
 * §7.5-7 — la galerie.
 *
 * @param array<string,mixed> $chien Fiche hydratée.
 */
function mtb_chien_rendre_la_galerie( array $chien ): void {
	mtb_fiche_rendre_la_galerie( (array) $chien['galerie'], 'mtb-fiche-chien__galerie alignwide', 'Galerie' );
}

/**
 * §7.5-7 — le lien pedigree, rendu en lien externe (§8.6).
 *
 * @param array<string,mixed> $chien Fiche hydratée.
 */
function mtb_chien_rendre_le_pedigree( array $chien ): void {
	$pedigree = isset( $chien['pedigree'] ) && is_array( $chien['pedigree'] ) ? $chien['pedigree'] : null;

	if ( null === $pedigree ) {
		return;
	}
	?>
	<p class="mtb-fiche-chien__pedigree">
		<?php // Le chevron du §8.6 est dessiné en CSS : aucune police d'icônes, aucun fichier externe. ?>
		<a class="mtb-lien-externe" href="<?php echo esc_url( (string) $pedigree['url'] ); ?>" target="_blank" rel="noopener">
			<?php echo esc_html( (string) $pedigree['libelle'] ); ?><span class="screen-reader-text"> (nouvelle fenêtre)</span>
		</a>
	</p>
	<?php
}

if ( have_posts() ) {
	the_post();
}

$mtb_chien_courant = function_exists( 'mtb_get_chien' ) ? mtb_get_chien( (int) get_the_ID() ) : null;

mtb_fiche_rendre_le_document(
	'mtb-fiche mtb-fiche--chien',
	static function () use ( $mtb_chien_courant ): void {
		mtb_chien_rendre_le_corps( $mtb_chien_courant );
	}
);
