<?php
/**
 * Les sept colonnes ajoutées aux trois listes d'administration.
 *
 * Une colonne nomme l'absence que le titre cache : le titre composé d'un résultat de travail omet
 * ses parties manquantes en silence, si bien qu'un résultat sans discipline porte un titre qui
 * commence par le nom du chien et n'annonce rien. La colonne, elle, écrit « Non renseigné ».
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Admin\Listes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Insère les colonnes du type entre « Titre » et « Date ».
 *
 * Un seul paramètre de colonnes, jamais deux : le cœur ne passe qu'un argument à ce filtre, et un
 * rappel typé à deux paramètres obligatoires lèverait une ArgumentCountError qui emporterait toute
 * la liste. Le nom du type est donc figé par un rappel dédié, un par liste.
 *
 * Le premier paramètre n'est pas typé : un filtre tiers peut avoir rendu autre chose qu'un tableau,
 * et strict_types en ferait une erreur fatale au chargement de l'écran.
 *
 * @param mixed  $colonnes Colonnes déjà déclarées, clé interne vers en-tête.
 * @param string $type     Nom du type de contenu concerné.
 *
 * @return mixed Colonnes, les nôtres insérées, ou la valeur reçue si elle n'est pas exploitable.
 */
function inserer_colonnes( $colonnes, string $type ) {
	if ( ! is_array( $colonnes ) ) {
		return $colonnes;
	}

	$description = description( $type );

	if ( null === $description ) {
		return $colonnes;
	}

	$nouvelles = array();
	$posees    = false;

	/*
	 * Reconstruction dans l'ordre plutôt qu'une affectation en bout de tableau : celle-ci
	 * rejetterait les nouvelles colonnes après « Date », loin du titre qu'elles complètent.
	 * La colonne « Date » native est conservée : l'issue demande d'ajouter, pas de retirer.
	 */
	foreach ( $colonnes as $cle => $entete ) {
		if ( 'date' === $cle && false === $posees ) {
			$nouvelles = array_merge( $nouvelles, $description['colonnes'] );
			$posees    = true;
		}

		$nouvelles[ $cle ] = $entete;
	}

	// Aucune colonne « Date » — un filtre tiers a pu la retirer : nos colonnes ferment la liste.
	if ( false === $posees ) {
		$nouvelles = array_merge( $nouvelles, $description['colonnes'] );
	}

	return $nouvelles;
}

/**
 * Colonnes de la liste des portées.
 *
 * Un rappel par type, plutôt qu'un seul accroché au filtre générique : ce sont les trois crochets
 * nommés que le contrat de l'issue gèle, et un crochet nommé se retrouve à la lecture.
 *
 * @param mixed $colonnes Colonnes déjà déclarées.
 *
 * @return mixed Colonnes complétées.
 */
function colonnes_portee( $colonnes ) {
	return inserer_colonnes( $colonnes, 'mtb_portee' );
}

/**
 * Colonnes de la liste des chiens.
 *
 * @param mixed $colonnes Colonnes déjà déclarées.
 *
 * @return mixed Colonnes complétées.
 */
function colonnes_chien( $colonnes ) {
	return inserer_colonnes( $colonnes, 'mtb_chien' );
}

/**
 * Colonnes de la liste des résultats de travail.
 *
 * @param mixed $colonnes Colonnes déjà déclarées.
 *
 * @return mixed Colonnes complétées.
 */
function colonnes_resultat( $colonnes ) {
	return inserer_colonnes( $colonnes, 'mtb_resultat' );
}

/**
 * Retire de la colonne « Date » native sa marque « colonne triée par défaut ».
 *
 * POURQUOI. Le cœur déclare la colonne Date avec un cinquième élément, « $initial_order », qui vaut
 * « desc ». En l'absence de « orderby » dans l'URL, la table d'affichage s'en sert pour poser sur
 * l'en-tête Date la classe « sorted desc », le texte masqué « Table ordered by Date. » et surtout
 * aria-sort="descending". C'était exact tant que ces listes étaient rangées par date de
 * publication ; ce module leur impose désormais un autre ordre — date de naissance, nom d'usage,
 * ordre gelé des disciplines. Sans cette correction, WordPress annoncerait à un lecteur d'écran un
 * ordre que la table n'a pas, et ce serait notre ordre imposé qui rendrait l'affirmation fausse.
 *
 * ON RETRANCHE, ON N'AJOUTE JAMAIS. Aucune de nos sept colonnes n'est rendue triable au clic : une
 * colonne de champ triable ferait construire à la requête une jointure sur la valeur du champ, et
 * tout contenu dépourvu de ce champ disparaîtrait de la liste sans un mot. La colonne Date, elle,
 * trie sur une vraie colonne de table : elle reste triable au clic et cesse seulement de se
 * déclarer triée quand elle ne l'est pas. Une demande explicite dans l'URL l'emporte sur l'ordre
 * imposé, et l'en-tête retrouve alors son aria-sort — à juste titre, cette fois.
 *
 * Un seul index est retiré, aucune valeur n'est recomposée : les autres éléments sont rendus tels
 * quels, y compris les chaînes traduites du cœur, qui vivent dans sa propre traduction. Le tableau
 * vient d'un filtre, une extension tierce a pu le remanier : une forme inattendue est rendue
 * inchangée plutôt que réparée.
 *
 * @param mixed $colonnes Colonnes triables déjà déclarées, clé interne vers description.
 *
 * @return mixed Colonnes triables, l'ordre initial de « date » retiré, ou la valeur reçue telle
 *               quelle si sa forme n'est pas celle attendue.
 */
function retirer_ordre_initial_de_date( $colonnes ) {
	if ( ! is_array( $colonnes ) || ! isset( $colonnes['date'] ) ) {
		return $colonnes;
	}

	$date = $colonnes['date'];

	if ( ! is_array( $date ) || ! array_key_exists( 4, $date ) ) {
		return $colonnes;
	}

	unset( $date[4] );

	$colonnes['date'] = $date;

	return $colonnes;
}

/**
 * Imprime le contenu d'une de nos cellules.
 *
 * Aucune cellule n'est jamais vide : une donnée absente s'écrit « Non renseigné ».
 *
 * @param string $colonne Clé interne de la colonne rendue.
 * @param int    $post_id Identifiant du contenu de la ligne.
 */
function rendre_cellule( string $colonne, int $post_id ): void {
	$texte = texte_cellule( $colonne, $post_id );

	if ( '' === $texte ) {
		return;
	}

	echo esc_html( $texte );
}

/**
 * Compose le texte d'une cellule, avant échappement.
 *
 * @param string $colonne Clé interne de la colonne.
 * @param int    $post_id Identifiant du contenu.
 *
 * @return string Texte à imprimer, chaîne vide si la colonne n'est pas des nôtres.
 */
function texte_cellule( string $colonne, int $post_id ): string {
	switch ( $colonne ) {
		case 'mtb_date_naissance':
			return \MTB\Core\Query\Portee\Hydratation::date_en_toutes_lettres( champ( $post_id, '_mtb_date_naissance' ) );

		case 'mtb_disponibilite':
			return libelle_de_liste( champ( $post_id, '_mtb_disponibilite' ), \MTB\Core\Content\Portee\disponibilites() );

		case 'mtb_statut':
			// Point unique de l'accord : jamais de forme composée, elle se lit mal en synthèse vocale.
			return \MTB\Core\Content\Chien\libelle_statut( champ( $post_id, '_mtb_statut' ), champ( $post_id, '_mtb_sexe' ) );

		case 'mtb_variete':
			return libelle_de_liste( champ( $post_id, '_mtb_variete' ), \MTB\Core\Content\Chien\varietes() );

		case 'mtb_discipline':
			return libelle_discipline( champ( $post_id, '_mtb_discipline' ) );

		case 'mtb_annee':
			return libelle_annee( champ( $post_id, '_mtb_annee' ) );

		case 'mtb_chien':
			return libelle_chien( $post_id );
	}

	return '';
}

/**
 * Lit une valeur stockée et la rend en chaîne, sans jamais la reformater.
 *
 * Lecture directe assumée : une liste d'administration montre les brouillons, les planifiés, les
 * contenus protégés par mot de passe et la corbeille, que les fonctions de lecture publiques
 * écartent délibérément. La frontière de CLAUDE.md interdit au thème d'interroger la base, pas à
 * l'extension.
 *
 * @param int    $post_id Identifiant du contenu.
 * @param string $cle     Clé stockée.
 *
 * @return string Valeur telle qu'elle est stockée, chaîne vide si absente ou non scalaire.
 */
function champ( int $post_id, string $cle ): string {
	$valeur = get_post_meta( $post_id, $cle, true );

	return is_scalar( $valeur ) ? (string) $valeur : '';
}

/**
 * Libellé d'une valeur de liste fermée.
 *
 * Une clé inconnue est traitée comme une absence : on n'affiche jamais une clé technique. C'est la
 * doctrine déjà écrite pour le site public, appliquée ici sans variante.
 *
 * @param string                $cle      Clé stockée, éventuellement vide ou inconnue.
 * @param array<string, string> $libelles Liste fermée, lue vivante par l'appelant.
 *
 * @return string Libellé affiché, ou « Non renseigné ».
 */
function libelle_de_liste( string $cle, array $libelles ): string {
	return isset( $libelles[ $cle ] ) ? $libelles[ $cle ] : absence();
}

/**
 * Libellé d'une discipline.
 *
 * Une clé hors de la liste fermée mais non vide garde sa valeur brute : la discipline stockée n'est
 * pas filtrée sur une liste blanche à l'enregistrement, précisément pour qu'une valeur devenue
 * orpheline ne soit pas détruite au premier ré-enregistrement. La colonne ne la fait donc pas
 * disparaître non plus.
 *
 * @param string $cle Clé stockée.
 *
 * @return string Libellé, clé brute si orpheline, « Non renseigné » si vide.
 */
function libelle_discipline( string $cle ): string {
	if ( '' === $cle ) {
		return absence();
	}

	$disciplines = disciplines();

	return isset( $disciplines[ $cle ] ) ? $disciplines[ $cle ] : $cle;
}

/**
 * Libellé d'une année.
 *
 * Chiffres bruts, jamais de mise en forme des nombres : la locale française produirait « 2 021 »,
 * qui n'est pas une année.
 *
 * @param string $valeur Valeur stockée.
 *
 * @return string Année en chiffres, ou « Non renseigné ».
 */
function libelle_annee( string $valeur ): string {
	$annee = (int) $valeur;

	return 0 === $annee ? absence() : (string) $annee;
}

/**
 * Libellé de la colonne « Chien » d'un résultat de travail — quatre états.
 *
 * La précédence est celle que l'écran de saisie énonce à l'éleveuse : si une fiche est choisie,
 * elle l'emporte ; le nom recopié ne sert que si aucune fiche n'est choisie.
 *
 * Aucun lien vers la fiche, délibérément : la ligne porte déjà le lien d'édition du résultat sur
 * son titre, et get_edit_post_link() rend null sans la capacité — la colonne changerait alors
 * d'aspect selon le compte connecté.
 *
 * @param int $post_id Identifiant du résultat de travail.
 *
 * @return string Titre de la fiche, « Fiche n° … », « Fiche introuvable », nom recopié, ou absence.
 */
function libelle_chien( int $post_id ): string {
	$identifiant = (int) champ( $post_id, '_mtb_chien_id' );

	if ( $identifiant > 0 ) {
		$fiche = get_post( $identifiant );

		if ( ! $fiche instanceof \WP_Post || 'mtb_chien' !== $fiche->post_type ) {
			return 'Fiche introuvable';
		}

		$titre = (string) $fiche->post_title;

		return '' === $titre ? 'Fiche n° ' . (string) $identifiant : $titre;
	}

	$nom = champ( $post_id, '_mtb_chien_nom' );

	return '' === $nom ? absence() : $nom;
}
