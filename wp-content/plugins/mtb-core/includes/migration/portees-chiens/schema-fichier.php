<?php
/**
 * Correspondance entre une clé de fichier de données et une clé du modèle gelé.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\PorteesChiens;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * LA RÈGLE, ET SES SEULES EXCEPTIONS
 *
 * Une clé de fichier est la clé de méta du modèle privée de son préfixe « _mtb_ ». Exceptions,
 * limitativement : les clés de contenu WordPress ; les quatre groupes — « tests_sante »,
 * « titres », « pere », « mere » — dont les sous-clés sont les clés courtes du modèle ; la clé
 * « photo », qui porte l'image mise en avant ; et « robots_source », hors modèle (voir modele.php).
 *
 * L'ensemble des clés acceptées n'est écrit nulle part : il est CALCULÉ depuis le modèle, à chaque
 * exécution. Le jour où une issue ajoute un champ, un fichier de données peut l'employer sans
 * qu'une ligne de ce module ne bouge.
 *
 * LES CLÉS TECHNIQUES SONT TOLÉRANTES DE FORME, ET C'EST DÉLIBÉRÉ
 *
 * « reference », « identifiant », « slug_source », la « reference » d'un parent, un identifiant de
 * photographie : ces cinq-là s'acceptent aussi bien en chaîne nue qu'en objet à provenance. Elles
 * ne portent pas un fait d'élevage mais une clé d'identité, et le contrôle des extraits les exempte
 * de toute façon. Deux transcripteurs travaillant en parallèle peuvent trancher différemment sur
 * une clé technique : refuser 44 entrées pour ce motif serait un échec faux. Toute autre clé exige
 * la forme complète, sans tolérance.
 */

/**
 * Clé de fichier portant l'identifiant IONOS de l'image mise en avant.
 *
 * L'image mise en avant est une donnée de contenu WordPress (« _thumbnail_id »), au même titre que
 * le titre : elle n'a ni entrée de catalogue, ni assainisseur de modèle, et c'est le contrôle aval
 * qui la surveille.
 */
const CLE_PHOTO = 'photo';

/**
 * Clé de fichier portant la galerie, liste d'identifiants IONOS.
 */
const CLE_GALERIE = 'galerie';

/**
 * Clé de fichier portant le fait de non-indexation relevé dans le « <head> » archivé.
 */
const CLE_FICHIER_ROBOTS = 'robots_source';

/**
 * Sous-clés du fait de non-indexation, dans l'ordre où elles se lisent.
 *
 * Il se stocke comme il s'écrit au fichier : « valeur » dit ce que la balise énonce, « source » le
 * fichier archivé où elle a été relevée, « extrait » les octets mêmes de la capture. Stocker la
 * seule chaîne « noindex, nofollow » ferait entrer en base une affirmation sans provenance, sur la
 * clé même que le référencement devra honorer — et la reprise des pages, qui écrit la même clé,
 * l'écrit déjà sous cette forme complète.
 */
const SOUS_CLES_ROBOTS = array( 'valeur', 'source', 'extrait' );

/**
 * Clé de fichier portant la liste nominative des chiots d'une portée.
 */
const CLE_CHIOTS = 'chiots';

/**
 * Sous-clés d'une rangée de chiot, dans l'ordre du modèle.
 *
 * @return string[] Sous-clés.
 */
function sous_cles_de_chiot(): array {
	return array( 'nom', 'sexe', 'lof', 'devenir' );
}

/**
 * Clés de contenu dont la forme nue est tolérée.
 *
 * @return string[] Clés tolérantes.
 */
function cles_techniques(): array {
	return array( 'reference', 'identifiant', 'slug_source' );
}

/**
 * Chemins de fichier portant une CLÉ DE PROJET et non un fait recopié.
 *
 * Ces sept chemins ont ceci en commun que le site source ne les énonce jamais : ni la référence
 * d'une fiche, ni le mode de saisie d'un parent n'existent sur mtbrabant.com. Ce sont des décisions
 * de modélisation, et leur demander un extrait verbatim est impossible par construction — en
 * fabriquer un serait inventer une provenance, ce que ce format existe pour empêcher.
 *
 * Conséquence unique et limitée : ils s'écrivent en chaîne nue comme en objet à provenance, et se
 * lisent par texte_souple(). Leur VALEUR reste contrôlée comme toutes les autres.
 *
 * @return string[] Chemins de fichier.
 */
function chemins_de_projet(): array {
	return array(
		'reference',
		'identifiant',
		'slug_source',
		'pere.reference',
		'mere.reference',
		'pere.type',
		'mere.type',
	);
}

/**
 * Mode de saisie d'un parent, lu quelle que soit la forme employée par la transcription.
 *
 * Point de lecture UNIQUE : trois fichiers lisaient cette valeur chacun de leur côté, et deux
 * d'entre eux la lisaient par valeur(), qui rend une chaîne vide sur une chaîne nue. Le mode
 * revenait donc vide, le type était stocké vide, et aucune filiation n'était posée — sans un mot.
 *
 * @param string               $jeu    « chiens » ou « portees ».
 * @param array<string, mixed> $entree Entrée du fichier.
 * @param string               $role   « pere » ou « mere ».
 *
 * @return string Mode de saisie, chaîne vide s'il n'est pas déclaré.
 */
function type_de_parent( string $jeu, array $entree, string $role ): string {
	return texte_souple( transcription( $jeu, $entree, '_mtb_' . $role . '_type' )['brut'] );
}

/**
 * Clés de contenu WordPress d'un jeu : clé de fichier => champ de « wp_posts ».
 *
 * Le slug d'une portée vient de l'URL du site source et son titre de son intitulé : sur
 * « portee-n-2017 », le site écrit « N_2 » en titre et sert « /portée-n-2017/ ». Les deux faits
 * divergent, et les deux sont conservés — c'est le slug qui fait atterrir la redirection 301.
 *
 * @param string $jeu « chiens » ou « portees ».
 *
 * @return array<string, string> Clé de fichier => nom du champ de contenu.
 */
function cles_de_contenu( string $jeu ): array {
	$cles = array(
		'chiens'  => array(
			'nom_usage'   => 'post_title',
			'reference'   => 'post_name',
			'texte_libre' => 'post_content',
		),
		'portees' => array(
			'identifiant' => 'post_title',
			'slug_source' => 'post_name',
			'texte_libre' => 'post_content',
		),
	);

	return isset( $cles[ $jeu ] ) ? $cles[ $jeu ] : array();
}

/**
 * Clés de méta résolues autrement que par une simple recopie de valeur.
 *
 * Une clé relationnelle porte un slug ou un identifiant IONOS là où le modèle stocke un
 * identifiant de contenu, qui n'existe pas avant l'import. La liste des chiots, elle, n'est pas
 * une valeur mais une suite de rangées. Le fait de non-indexation, enfin, n'est pas davantage une
 * valeur : il se stocke avec sa provenance entière, là où valeur() le réduirait à sa seule
 * « valeur ». Toutes sont écrites par le fichier de leur type, hors de la passe ordinaire.
 *
 * @param string $jeu « chiens » ou « portees ».
 *
 * @return string[] Clés de méta.
 */
function cles_hors_passe_ordinaire( string $jeu ): array {
	$cles = array(
		'chiens'  => array( '_mtb_pere_fiche', '_mtb_mere_fiche', '_mtb_galerie', CLE_ROBOTS ),
		'portees' => array( '_mtb_pere_fiche', '_mtb_mere_fiche', '_mtb_galerie', '_mtb_chiots' ),
	);

	return isset( $cles[ $jeu ] ) ? $cles[ $jeu ] : array();
}

/**
 * Les deux rôles de parent, dans l'ordre de saisie.
 *
 * @return string[] Rôles.
 */
function roles(): array {
	return array( 'pere', 'mere' );
}

/**
 * Origine de chaque clé de méta dans le fichier : clé de méta => groupe et clé de fichier.
 *
 * Table pivot du module : elle sert aussi bien à dresser la liste des clés acceptées qu'à relire
 * les valeurs d'une entrée. Un groupe vide signifie « clé de premier niveau ».
 *
 * @param string $jeu « chiens » ou « portees ».
 *
 * @return array<string, array<string, string>> Clé de méta => array{ groupe, cle }.
 */
function sources_json( string $jeu ): array {
	$sources = array();

	if ( 'chiens' === $jeu ) {
		foreach ( champs_sante() as $court => $champ ) {
			$sources[ $champ['cle'] ] = array(
				'groupe' => 'tests_sante',
				'cle'    => (string) $court,
			);
		}

		foreach ( champs_titres() as $court => $champ ) {
			$sources[ $champ['cle'] ] = array(
				'groupe' => 'titres',
				'cle'    => (string) $court,
			);
		}

		$sources[ CLE_ROBOTS ] = array(
			'groupe' => '',
			'cle'    => CLE_FICHIER_ROBOTS,
		);
	}

	foreach ( array_keys( champs( $jeu ) ) as $meta ) {
		if ( isset( $sources[ $meta ] ) ) {
			continue;
		}

		$sources[ $meta ] = source_dune_meta( (string) $meta );
	}

	return $sources;
}

/**
 * Origine d'une clé de méta qui n'appartient ni à la santé ni aux titres.
 *
 * @param string $meta Clé de méta, préfixe « _mtb_ » compris.
 *
 * @return array<string, string> array{ groupe, cle }.
 */
function source_dune_meta( string $meta ): array {
	$court = substr( $meta, strlen( '_mtb_' ) );

	foreach ( roles() as $role ) {
		$prefixe = $role . '_';

		if ( 0 !== strpos( $court, $prefixe ) ) {
			continue;
		}

		$suffixe = substr( $court, strlen( $prefixe ) );

		return array(
			'groupe' => $role,
			// Le fichier ne peut pas connaître un identifiant de contenu : il désigne la fiche par son slug.
			'cle'    => 'fiche' === $suffixe ? 'reference' : $suffixe,
		);
	}

	return array(
		'groupe' => '',
		'cle'    => $court,
	);
}

/**
 * Les groupes d'un jeu et leurs sous-clés acceptées.
 *
 * @param string $jeu « chiens » ou « portees ».
 *
 * @return array<string, string[]> Nom du groupe => sous-clés acceptées.
 */
function groupes( string $jeu ): array {
	$groupes = array();

	foreach ( sources_json( $jeu ) as $source ) {
		if ( '' === $source['groupe'] ) {
			continue;
		}

		if ( ! isset( $groupes[ $source['groupe'] ] ) ) {
			$groupes[ $source['groupe'] ] = array();
		}

		$groupes[ $source['groupe'] ][] = $source['cle'];
	}

	return $groupes;
}

/**
 * Liste close des clés de premier niveau acceptées, calculée depuis le modèle.
 *
 * @param string $jeu « chiens » ou « portees ».
 *
 * @return string[] Clés acceptées.
 */
function cles_acceptees( string $jeu ): array {
	$acceptees = array_keys( cles_de_contenu( $jeu ) );

	if ( 'chiens' === $jeu ) {
		$acceptees[] = CLE_PHOTO;
	}

	foreach ( sources_json( $jeu ) as $source ) {
		$acceptees[] = '' === $source['groupe'] ? $source['cle'] : $source['groupe'];
	}

	return array_values( array_unique( $acceptees ) );
}

/**
 * Chemin d'une clé de méta tel qu'il s'écrit dans le fichier.
 *
 * @param string $jeu  « chiens » ou « portees ».
 * @param string $meta Clé de méta.
 *
 * @return string Chemin de fichier, « groupe.cle » ou « cle ».
 */
function chemin_json( string $jeu, string $meta ): string {
	$sources = sources_json( $jeu );

	if ( ! isset( $sources[ $meta ] ) ) {
		return $meta;
	}

	$source = $sources[ $meta ];

	return '' === $source['groupe'] ? $source['cle'] : $source['groupe'] . '.' . $source['cle'];
}

/**
 * Valeur transcrite d'une clé de méta dans une entrée, et son état de présence.
 *
 * @param string               $jeu    « chiens » ou « portees ».
 * @param array<string, mixed> $entree Entrée du fichier.
 * @param string               $meta   Clé de méta.
 *
 * @return array<string, mixed> array{ presente: bool, brut: mixed }.
 */
function transcription( string $jeu, array $entree, string $meta ): array {
	$sources = sources_json( $jeu );

	if ( ! isset( $sources[ $meta ] ) ) {
		return array(
			'presente' => false,
			'brut'     => null,
		);
	}

	$source  = $sources[ $meta ];
	$porteur = $entree;

	if ( '' !== $source['groupe'] ) {
		$groupe  = isset( $entree[ $source['groupe'] ] ) ? $entree[ $source['groupe'] ] : null;
		$porteur = is_array( $groupe ) ? $groupe : array();
	}

	return array(
		'presente' => array_key_exists( $source['cle'], $porteur ),
		'brut'     => isset( $porteur[ $source['cle'] ] ) ? $porteur[ $source['cle'] ] : null,
	);
}

/**
 * Valeurs transcrites de toutes les clés de méta de la passe ordinaire.
 *
 * Le parcours part du modèle et non du fichier : aucune clé du modèle ne peut donc rester non
 * écrite, ce qui rend le stockage identique à celui d'un enregistrement depuis l'écran de saisie.
 *
 * @param string               $jeu    « chiens » ou « portees ».
 * @param array<string, mixed> $entree Entrée du fichier.
 *
 * @return array<string, string> Clé de méta => valeur transcrite.
 */
function valeurs_brutes( string $jeu, array $entree ): array {
	$hors    = cles_hors_passe_ordinaire( $jeu );
	$valeurs = array();

	foreach ( array_keys( sources_json( $jeu ) ) as $meta ) {
		$meta = (string) $meta;

		if ( in_array( $meta, $hors, true ) ) {
			continue;
		}

		$transcription = transcription( $jeu, $entree, $meta );

		/*
		 * Une clé de projet se lit souplement : lue par valeur(), une chaîne nue rendrait une chaîne
		 * vide, et la clé serait stockée VIDE sans qu'aucun contrôle ne s'en aperçoive — l'assainisseur
		 * étant toujours d'accord avec lui-même sur une valeur vide.
		 */
		$valeurs[ $meta ] = in_array( chemin_json( $jeu, $meta ), chemins_de_projet(), true )
			? texte_souple( $transcription['brut'] )
			: valeur( $transcription['brut'] );
	}

	return $valeurs;
}

/**
 * Fait de non-indexation déclaré par une entrée, assaini sous-clé par sous-clé.
 *
 * Aucun assainisseur n'est écrit ici : chaque sous-clé passe par celui du module, la copie la plus
 * stricte du dépôt. Jamais sanitize_text_field() ni wp_kses() — toutes deux passent par
 * strip_tags(), qui viderait EN SILENCE un extrait fait de balises « <meta … /> », c'est-à-dire
 * très exactement ce que cet extrait est là pour prouver.
 *
 * Une sous-clé absente devient une chaîne vide, jamais une clé manquante : la relecture doit
 * pouvoir comparer les trois.
 *
 * @param array<string, mixed> $entree Entrée du fichier.
 *
 * @return array<string, string> Fait de non-indexation, tableau vide si l'entrée n'en déclare aucun.
 */
function fait_de_robots( array $entree ): array {
	$brut = isset( $entree[ CLE_FICHIER_ROBOTS ] ) ? $entree[ CLE_FICHIER_ROBOTS ] : null;

	if ( ! is_array( $brut ) || est_une_liste( $brut ) ) {
		return array();
	}

	$fait = array();

	foreach ( SOUS_CLES_ROBOTS as $cle ) {
		$fait[ $cle ] = assainir_recopie( isset( $brut[ $cle ] ) ? $brut[ $cle ] : '', false );
	}

	return $fait;
}

/**
 * Rangées de chiots d'une portée, valeurs dégagées de leur provenance.
 *
 * @param array<string, mixed> $entree Entrée du fichier.
 *
 * @return array<int, array<string, string>> Rangées, dans l'ordre du fichier.
 */
function chiots_bruts( array $entree ): array {
	$brut = isset( $entree[ CLE_CHIOTS ] ) ? $entree[ CLE_CHIOTS ] : null;

	if ( ! is_array( $brut ) ) {
		return array();
	}

	$rangees = array();

	foreach ( $brut as $rangee ) {
		if ( ! is_array( $rangee ) ) {
			continue;
		}

		$propre = array();

		foreach ( sous_cles_de_chiot() as $sous_cle ) {
			$propre[ $sous_cle ] = valeur( isset( $rangee[ $sous_cle ] ) ? $rangee[ $sous_cle ] : null );
		}

		$rangees[] = $propre;
	}

	return $rangees;
}

/**
 * Champs de contenu WordPress d'une entrée.
 *
 * @param string               $jeu    « chiens » ou « portees ».
 * @param array<string, mixed> $entree Entrée du fichier.
 *
 * @return array<string, string> Champ de « wp_posts » => valeur transcrite.
 */
function champs_de_contenu( string $jeu, array $entree ): array {
	$champs = array();

	foreach ( cles_de_contenu( $jeu ) as $cle => $champ ) {
		$brut = isset( $entree[ $cle ] ) ? $entree[ $cle ] : null;

		$champs[ $champ ] = in_array( $cle, cles_techniques(), true ) ? texte_souple( $brut ) : valeur( $brut );
	}

	/*
	 * Le contenu publié est le texte CONVERTI, jamais la notation de capture. La conversion vit
	 * ici, dans le seul endroit qui compose « post_content », pour que l'import et la vérification
	 * comparent rigoureusement la même valeur : si la vérification relisait le texte non converti,
	 * elle nommerait quarante-quatre divergences imaginaires. La fonction appelée est pure — même
	 * entrée, même sortie — ce qui rend les deux chemins équivalents par construction.
	 */
	if ( isset( $champs['post_content'] ) ) {
		$conversion = convertir_les_marqueurs( (string) $champs['post_content'] );

		$champs['post_content'] = (string) $conversion['texte'];
	}

	return $champs;
}

/**
 * Valeur d'une clé technique, en chaîne nue comme en objet à provenance.
 *
 * @param mixed $brut Valeur brute.
 *
 * @return string Valeur, chaîne vide si absente ou d'une forme inattendue.
 */
function texte_souple( $brut ): string {
	if ( is_scalar( $brut ) ) {
		return trim( (string) $brut );
	}

	return trim( valeur( $brut ) );
}

/**
 * Sous-valeur technique d'un groupe, en chaîne nue comme en objet à provenance.
 *
 * @param array<string, mixed> $entree Entrée du fichier.
 * @param string               $groupe Nom du groupe.
 * @param string               $cle    Sous-clé.
 *
 * @return string Valeur, chaîne vide si absente.
 */
function texte_souple_de_groupe( array $entree, string $groupe, string $cle ): string {
	$contenu = isset( $entree[ $groupe ] ) ? $entree[ $groupe ] : null;

	if ( ! is_array( $contenu ) ) {
		return '';
	}

	return texte_souple( isset( $contenu[ $cle ] ) ? $contenu[ $cle ] : null );
}

/**
 * Identifiants IONOS cités par une clé de photographie, dans l'ordre du fichier.
 *
 * L'ordre porte le sens d'une galerie : il est conservé tel quel.
 *
 * @param mixed $brut Valeur brute de « photo » ou de « galerie ».
 *
 * @return string[] Identifiants non vides, dans l'ordre, sans doublon.
 */
function identifiants_de_photos( $brut ): array {
	if ( is_scalar( $brut ) || est_un_objet_de_valeur( $brut ) ) {
		$brut = array( $brut );
	}

	if ( ! is_array( $brut ) ) {
		return array();
	}

	$identifiants = array();

	foreach ( $brut as $element ) {
		$identifiant = texte_souple( $element );

		if ( '' !== $identifiant && ! in_array( $identifiant, $identifiants, true ) ) {
			$identifiants[] = $identifiant;
		}
	}

	return $identifiants;
}
