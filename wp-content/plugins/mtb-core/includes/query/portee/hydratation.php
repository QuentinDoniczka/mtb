<?php
/**
 * Mise en forme d'une portée pour le thème.
 *
 * Toute valeur du domaine sort enveloppée — « libelle », « valeur », « affichage » —, forme commune
 * aux trois types de contenu du lot. Le libellé public et la chaîne à imprimer viennent du serveur ;
 * le thème n'en compose aucune.
 *
 * Les libellés propres aux champs de ce module — « Née le », « Disponibilité », « Père », « Mère »,
 * les quatre colonnes de chiots — sont écrits en toutes lettres ici : ce ne sont pas des listes
 * fermées, aucune clé stockée en base n'en dépend et aucun assainisseur ne les consulte. Les listes
 * fermées, elles, ne le sont jamais : leurs clés sont stockées en base et un assainisseur de
 * « content/portee/champs.php » décide seul de ce qui y est admis. Ce module les appelle, il n'en
 * garde aucune copie — deux copies finiraient par dire deux choses différentes, l'une en
 * administration, l'autre sur le site.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Query\Portee;

use function MTB\Core\Content\Portee\disponibilites;
use function MTB\Core\Content\Portee\sexes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Le vocabulaire de la portée appartient au module « content/portee ». On le require_once
 * plutôt que de compter sur l'ordre de parcours du chargeur : un module ne doit jamais dépendre
 * de cet ordre, et une seconde inclusion est sans effet.
 */
require_once MTB_CORE_DIR . 'includes/content/portee/champs.php';

/**
 * Hydrate une portée et ses parties.
 *
 * Classe entièrement statique : elle ne porte aucun état, et l'extension n'expose aucun objet.
 */
final class Hydratation {

	/**
	 * Libellé unique de l'absence de donnée. Jamais un tiret, jamais « Aucun », jamais « Non testé ».
	 *
	 * @var string
	 */
	public const ABSENCE = 'Non renseigné';

	/**
	 * Classe purement statique : aucune instance n'a de sens.
	 */
	private function __construct() {}

	/**
	 * Colonnes du tableau des chiots, dans l'ordre.
	 *
	 * Ce sont exactement les libellés que porte chaque cellule d'un chiot : un composant tableau les
	 * lit ici pour ses attributs « data-libelle », il ne les réécrit jamais.
	 *
	 * @return array<int,string> Libellés de colonnes.
	 */
	public static function colonnes_chiots(): array {
		return array( 'Nom', 'Sexe', 'N° LOF', 'Devenir' );
	}

	/**
	 * Les trois états de disponibilité, clé stockée vers libellé affiché.
	 *
	 * Passe-plat vers « content/portee/champs.php », qui possède cette liste fermée : les trois
	 * libellés ne sont pas déclarés ici. C'est cette liste-là que consulte l'assainisseur au moment
	 * de l'écriture, et une seconde copie finirait par dire autre chose que la base. La méthode
	 * subsiste parce que « mtb_get_portees() » valide contre elle son argument « disponibilite ».
	 *
	 * @return array<string,string> Clés « disponible », « reserve », « passee ».
	 */
	public static function disponibilites(): array {
		return disponibilites();
	}

	/**
	 * Les deux sexes d'un chiot, clé stockée vers libellé affiché.
	 *
	 * Passe-plat vers « content/portee/champs.php », pour la raison exposée au-dessus : les deux
	 * libellés ne sont pas déclarés ici.
	 *
	 * @return array<string,string> Clés « male », « femelle ».
	 */
	private static function sexes(): array {
		return sexes();
	}

	/**
	 * Interroge les portées publiées et consultables, sans aucune clause de méta.
	 *
	 * Trier par « orderby => meta_value » imposerait une jointure sur « _mtb_date_naissance » et
	 * ferait donc disparaître de l'index, sans un mot, toute portée qui n'a pas cette clé. Le tri
	 * se fait en PHP : à l'échelle d'un élevage le coût est nul, et aucune portée ne peut être
	 * escamotée.
	 *
	 * Aucun transient, aucun cache maison : WP_Query met déjà ses résultats en cache dans le groupe
	 * « posts », invalidé à chaque enregistrement.
	 *
	 * @param array $arguments Arguments de WP_Query propres à l'appel.
	 *
	 * @return array<int,\WP_Post> Contenus trouvés, tableau vide si rien ne correspond.
	 */
	public static function contenus( array $arguments = array() ): array {
		$defauts = array(
			'post_type'              => 'mtb_portee',
			'post_status'            => 'publish',
			'has_password'           => false,
			'posts_per_page'         => -1,
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
		);

		$requete  = new \WP_Query( array_merge( $defauts, $arguments ) );
		$contenus = array();

		foreach ( $requete->posts as $post ) {
			if ( $post instanceof \WP_Post ) {
				$contenus[] = $post;
			}
		}

		return $contenus;
	}

	/**
	 * Exécute une liste de portées, hydrate chaque résultat et trie en PHP.
	 *
	 * @param array  $arguments Arguments de WP_Query propres à l'appel.
	 * @param string $ordre     « DESC » ou « ASC », sur la date de naissance.
	 *
	 * @return array<int,array<string,mixed>> Portées hydratées et triées.
	 */
	public static function liste( array $arguments = array(), string $ordre = 'DESC' ): array {
		$portees = array();

		foreach ( self::contenus( $arguments ) as $post ) {
			$portees[] = self::portee( $post );
		}

		return self::trier( $portees, $ordre );
	}

	/**
	 * Trie des portées sur la date de naissance, les non datées toujours en fin de liste.
	 *
	 * « Non datée » recouvre deux cas : la date absente, et la date que « date_en_toutes_lettres() »
	 * refuse. Cette décision de lisibilité est prise par cette fonction-là et par elle seule, en
	 * amont du comparateur ; une date illisible n'a pas plus de place chronologique qu'une date
	 * absente, elle se range donc en fin de liste, jamais en tête, et n'est jamais escamotée.
	 *
	 * Une égalité de date est départagée par l'identifiant de contenu, pour que l'ordre soit
	 * déterministe d'une requête à l'autre.
	 *
	 * @param array  $portees Portées hydratées, ou éléments portant « date_naissance » et « id ».
	 * @param string $ordre   « DESC » ou « ASC ».
	 *
	 * @return array<int,array<string,mixed>> Portées triées, réindexées.
	 */
	public static function trier( array $portees, string $ordre = 'DESC' ): array {
		$croissant = ( 'ASC' === strtoupper( $ordre ) );

		usort(
			$portees,
			static function ( array $premiere, array $seconde ) use ( $croissant ): int {
				$une   = self::date_de( $premiere );
				$autre = self::date_de( $seconde );

				// Sans date lisible, en fin de liste dans les deux sens : pas de place chronologique.
				if ( '' === $une && '' !== $autre ) {
					return 1;
				}

				if ( '' !== $une && '' === $autre ) {
					return -1;
				}

				$ecart = $croissant ? strcmp( $une, $autre ) : strcmp( $autre, $une );

				if ( 0 !== $ecart ) {
					return $ecart;
				}

				$id_une   = (int) ( $premiere['id'] ?? 0 );
				$id_autre = (int) ( $seconde['id'] ?? 0 );

				return $croissant ? $id_une <=> $id_autre : $id_autre <=> $id_une;
			}
		);

		return array_values( $portees );
	}

	/**
	 * Lit la date de naissance d'un élément enveloppé.
	 *
	 * @param array $element Portée hydratée ou élément de liste.
	 *
	 * @return string Date AAAA-MM-JJ lisible, ou chaîne vide.
	 */
	private static function date_de( array $element ): string {
		$champ = $element['date_naissance'] ?? array();

		return is_array( $champ ) ? self::date_lisible( $champ ) : '';
	}

	/**
	 * Date d'un champ enveloppé, ou chaîne vide quand cette date n'est pas lisible.
	 *
	 * « Lisible » se décide par « date_en_toutes_lettres() » et par elle seule : refaire ici le
	 * test de validité fabriquerait une seconde notion de date absente, qui divergerait de la
	 * première. On lit l'affichage déjà calculé par « champ_date() » plutôt que de rappeler la
	 * fonction : la décision n'est pas refaite, elle est relue, et aucun appel à wp_date() ne
	 * s'ajoute. Le repli, quand l'élément ne porte pas d'affichage, interroge la même fonction ;
	 * il n'est jamais un repli sur la chaîne vide, qui serait une troisième notion de plus.
	 *
	 * Cette fonction ne renvoie ni n'écrit jamais une « valeur » modifiée : la donnée brute ne se
	 * reformate pas. Le tri ignore une date illisible, il ne l'efface pas, et la portée sort de
	 * « mtb_get_portee() » avec sa « valeur » intacte, pour qu'une réparation future puisse encore
	 * la lire.
	 *
	 * @param array $champ Champ enveloppé « libelle » / « valeur » / « affichage ».
	 *
	 * @return string Date brute inchangée si elle est lisible, sinon chaîne vide.
	 */
	private static function date_lisible( array $champ ): string {
		$valeur = $champ['valeur'] ?? '';

		if ( ! is_string( $valeur ) || '' === $valeur ) {
			return '';
		}

		$affichage = $champ['affichage'] ?? null;

		if ( ! is_string( $affichage ) ) {
			$affichage = self::date_en_toutes_lettres( $valeur );
		}

		return self::ABSENCE === $affichage ? '' : $valeur;
	}

	/**
	 * Hydrate un élément de la liste des portées d'un chien, dans la forme que consomme sa fiche.
	 *
	 * Cette forme est gelée : « lien » et non « url », « role » et non « role_du_chien ». La fiche
	 * du chien la reçoit telle quelle et ne normalise rien.
	 *
	 * @param \WP_Post $post Portée.
	 * @param string   $role « pere » ou « mere ».
	 *
	 * @return array<string,mixed> Élément de liste.
	 */
	public static function element_du_chien( \WP_Post $post, string $role ): array {
		$id      = (int) $post->ID;
		$adresse = get_permalink( $post );

		$roles = array(
			'pere' => 'Père',
			'mere' => 'Mère',
		);

		return array(
			'id'             => $id,
			'identifiant'    => (string) $post->post_title,
			'lien'           => is_string( $adresse ) ? $adresse : '',
			'date_naissance' => self::champ_date( 'Née le', self::texte( get_post_meta( $id, '_mtb_date_naissance', true ) ) ),
			'disponibilite'  => self::champ_liste( 'Disponibilité', self::texte( get_post_meta( $id, '_mtb_disponibilite', true ) ), self::disponibilites() ),
			'role'           => array(
				'valeur'    => isset( $roles[ $role ] ) ? $role : '',
				'affichage' => $roles[ $role ] ?? '',
			),
		);
	}

	/**
	 * Hydrate une portée.
	 *
	 * @param \WP_Post $post Portée à hydrater.
	 *
	 * @return array<string,mixed> Portée hydratée.
	 */
	public static function portee( \WP_Post $post ): array {
		$id          = (int) $post->ID;
		$identifiant = (string) $post->post_title;
		$adresse     = get_permalink( $post );
		$lien        = is_string( $adresse ) ? $adresse : '';

		/*
		 * Charge minimale : pas un champ du domaine, pas même vide. Une clé présente à « » dirait
		 * déjà qu'elle existe. Les fonctions de liste, elles, écartent ces contenus en amont.
		 */
		if ( post_password_required( $post ) ) {
			return array(
				'id'      => $id,
				'lien'    => $lien,
				'protege' => true,
				'etat'    => 'page_protegee',
			);
		}

		$titre_public = '' === $identifiant ? 'Portée' : 'Portée ' . $identifiant;
		$date         = self::texte( get_post_meta( $id, '_mtb_date_naissance', true ) );
		$males        = self::texte( get_post_meta( $id, '_mtb_males', true ) );
		$femelles     = self::texte( get_post_meta( $id, '_mtb_femelles', true ) );
		$chiots       = self::chiots( $id );

		$date_naissance = self::champ_date( 'Née le', $date );
		$date_lisible   = self::date_lisible( $date_naissance );

		return array(
			'id'              => $id,
			'identifiant'     => $identifiant,
			'titre_public'    => $titre_public,
			'lien'            => $lien,
			'statut'          => (string) $post->post_status,
			'protege'         => false,
			'etat'            => 'ok',
			'annee'           => '' === $date_lisible ? '' : substr( $date_lisible, 0, 4 ),
			'date_naissance'  => $date_naissance,
			'disponibilite'   => self::champ_liste( 'Disponibilité', self::texte( get_post_meta( $id, '_mtb_disponibilite', true ) ), self::disponibilites() ),
			'males'           => self::champ_compteur( 'Nombre de mâles', $males, 'mâle', 'mâles' ),
			'femelles'        => self::champ_compteur( 'Nombre de femelles', $femelles, 'femelle', 'femelles' ),
			'effectif_texte'  => self::texte_effectif( $males, $femelles ),
			'pere'            => self::parent( $id, 'pere', 'Père' ),
			'mere'            => self::parent( $id, 'mere', 'Mère' ),
			'chiots_colonnes' => self::colonnes_chiots(),
			'chiots'          => $chiots,
			'chiots_message'  => array() === $chiots ? 'Liste des chiots non renseignée.' : '',
			'galerie'         => self::galerie( $id, $titre_public ),
			'photo'           => self::photo( (int) get_post_thumbnail_id( $id ), $titre_public ),
		);
	}

	/**
	 * Hydrate un parent.
	 *
	 * Le mode de saisie fait foi, jamais la présence d'un identifiant : une branche inactive
	 * conserve sa valeur, et la lire sans tester le mode afficherait une généalogie fausse.
	 *
	 * Les tests de santé ne sont renseignés que pour un parent sans fiche : quand « etat » vaut
	 * « fiche », la fiche du chien fait foi et c'est elle qu'un composant interroge.
	 *
	 * @param int    $id      Identifiant de la portée.
	 * @param string $branche « pere » ou « mere ».
	 * @param string $libelle « Père » ou « Mère ».
	 *
	 * @return array<string,mixed> Parent hydraté.
	 */
	private static function parent( int $id, string $branche, string $libelle ): array {
		$parent = self::parent_absent( $branche, $libelle );
		$type   = self::texte( get_post_meta( $id, '_mtb_' . $branche . '_type', true ) );

		if ( 'fiche' === $type ) {
			$fiche_id = (int) get_post_meta( $id, '_mtb_' . $branche . '_fiche', true );

			if ( $fiche_id <= 0 ) {
				return $parent;
			}

			$fiche = get_post( $fiche_id );

			if ( ! $fiche instanceof \WP_Post
				|| 'mtb_chien' !== $fiche->post_type
				|| 'publish' !== $fiche->post_status
				|| post_password_required( $fiche )
			) {
				// Une fiche qui ne résout plus donne une donnée absente, jamais un lien mort.
				return $parent;
			}

			$adresse = get_permalink( $fiche );

			$parent['etat']     = 'fiche';
			$parent['fiche_id'] = $fiche_id;
			$parent['lien']     = is_string( $adresse ) ? $adresse : '';
			$parent['nom']      = self::champ( 'Nom', (string) $fiche->post_title );

			/*
			 * Les tests de ce parent ne sont pas absents : ils sont ailleurs, sur sa fiche, et c'est
			 * elle qui fait foi. Rendre « Non renseigné » affirmerait qu'il n'a pas été testé — un
			 * fait d'élevage faux. Un affichage vide ne dit rien, ce qui est exact ; le composant qui
			 * veut ces résultats appelle la fonction de lecture de la fiche.
			 */
			$parent['sante'] = self::champ_muet( self::libelle_sante( $branche ), '' );

			return $parent;
		}

		if ( 'exterieur' !== $type ) {
			return $parent;
		}

		$nom     = self::texte( get_post_meta( $id, '_mtb_' . $branche . '_nom', true ) );
		$elevage = self::texte( get_post_meta( $id, '_mtb_' . $branche . '_elevage', true ) );
		$sante   = self::texte( get_post_meta( $id, '_mtb_' . $branche . '_sante', true ) );

		if ( '' === $nom && '' === $elevage && '' === $sante ) {
			return $parent;
		}

		$parent['etat']    = 'parent_hors_elevage';
		$parent['nom']     = self::champ( 'Nom', $nom );
		$parent['elevage'] = self::champ_muet( 'Élevage', $elevage );
		$parent['sante']   = self::champ( self::libelle_sante( $branche ), $sante );

		return $parent;
	}

	/**
	 * Forme d'un parent dont rien n'est connu.
	 *
	 * @param string $branche « pere » ou « mere ».
	 * @param string $libelle « Père » ou « Mère ».
	 *
	 * @return array<string,mixed> Parent absent.
	 */
	private static function parent_absent( string $branche, string $libelle ): array {
		return array(
			'etat'     => 'donnee_absente',
			'libelle'  => $libelle,
			'fiche_id' => 0,
			'lien'     => '',
			'nom'      => self::champ( 'Nom', '' ),
			'elevage'  => self::champ_muet( 'Élevage', '' ),
			'sante'    => self::champ( self::libelle_sante( $branche ), '' ),
		);
	}

	/**
	 * Libellé des tests de santé d'un parent.
	 *
	 * @param string $branche « pere » ou « mere ».
	 *
	 * @return string Libellé affiché.
	 */
	private static function libelle_sante( string $branche ): string {
		return 'pere' === $branche ? 'Tests de santé du père' : 'Tests de santé de la mère';
	}

	/**
	 * Hydrate la liste des chiots.
	 *
	 * Les libellés des quatre cellules sont exactement ceux de « colonnes_chiots() ».
	 *
	 * @param int $id Identifiant de la portée.
	 *
	 * @return array<int,array<string,array<string,string>>> Chiots, tableau vide si rien n'est saisi.
	 */
	private static function chiots( int $id ): array {
		$stockes = get_post_meta( $id, '_mtb_chiots', true );

		if ( ! is_array( $stockes ) ) {
			return array();
		}

		$chiots = array();

		foreach ( $stockes as $rangee ) {
			if ( ! is_array( $rangee ) ) {
				continue;
			}

			$chiots[] = array(
				'nom'     => self::champ( 'Nom', self::texte( $rangee['nom'] ?? '' ) ),
				'sexe'    => self::champ_liste( 'Sexe', self::texte( $rangee['sexe'] ?? '' ), self::sexes() ),
				'lof'     => self::champ( 'N° LOF', self::texte( $rangee['lof'] ?? '' ) ),
				'devenir' => self::champ( 'Devenir', self::texte( $rangee['devenir'] ?? '' ) ),
			);
		}

		return array_values( $chiots );
	}

	/**
	 * Hydrate la galerie, dans l'ordre choisi.
	 *
	 * @param int    $id    Identifiant de la portée.
	 * @param string $repli Nom de la portée, employé quand une photo n'a pas d'alternative.
	 *
	 * @return array<int,array<string,mixed>> Photos, tableau vide si la galerie est vide.
	 */
	private static function galerie( int $id, string $repli ): array {
		$stockees = get_post_meta( $id, '_mtb_galerie', true );

		if ( ! is_array( $stockees ) ) {
			return array();
		}

		$photos = array();

		foreach ( $stockees as $valeur ) {
			$photo = self::photo( is_scalar( $valeur ) ? (int) $valeur : 0, $repli );

			if ( null !== $photo ) {
				$photos[] = $photo;
			}
		}

		return $photos;
	}

	/**
	 * Hydrate une photo : un identifiant et une alternative textuelle, jamais une adresse ni du HTML.
	 *
	 * Le thème choisit lui-même la taille d'affichage : c'est une décision de présentation.
	 *
	 * @param int    $piece_jointe Identifiant du fichier joint.
	 * @param string $repli        Nom de la portée, employé quand le fichier n'a pas d'alternative.
	 *
	 * @return array<string,mixed>|null Photo, ou null si le fichier n'existe plus.
	 */
	private static function photo( int $piece_jointe, string $repli ): ?array {
		if ( $piece_jointe <= 0 ) {
			return null;
		}

		$fichier = get_post( $piece_jointe );

		if ( ! $fichier instanceof \WP_Post || 'attachment' !== $fichier->post_type ) {
			return null;
		}

		$alt = self::texte( get_post_meta( $piece_jointe, '_wp_attachment_image_alt', true ) );

		return array(
			'id'  => $piece_jointe,
			// Repli sur le nom de la portée : aucune photo ne part sans alternative textuelle.
			'alt' => '' === $alt ? $repli : $alt,
		);
	}

	/**
	 * Enveloppe une valeur recopiée.
	 *
	 * @param string $libelle Libellé public du champ.
	 * @param string $valeur  Valeur stockée, jamais reformatée.
	 *
	 * @return array<string,string> Champ enveloppé.
	 */
	private static function champ( string $libelle, string $valeur ): array {
		return array(
			'libelle'   => $libelle,
			'valeur'    => $valeur,
			'affichage' => '' === $valeur ? self::ABSENCE : $valeur,
		);
	}

	/**
	 * Enveloppe une valeur dont l'absence ne se dit pas.
	 *
	 * Deux cas, et deux seulement : l'élevage d'un parent extérieur, où écrire « Non renseigné »
	 * derrière un nom de chien laisserait croire à une information manquante là où il n'y a rien à
	 * dire ; et les tests de santé d'un parent qui a une fiche, qui ne sont pas absents mais portés
	 * par sa fiche. Partout ailleurs, une donnée absente se dit « Non renseigné ».
	 *
	 * @param string $libelle Libellé public du champ.
	 * @param string $valeur  Valeur stockée, jamais reformatée.
	 *
	 * @return array<string,string> Champ enveloppé.
	 */
	private static function champ_muet( string $libelle, string $valeur ): array {
		return array(
			'libelle'   => $libelle,
			'valeur'    => $valeur,
			'affichage' => $valeur,
		);
	}

	/**
	 * Enveloppe une date : valeur brute AAAA-MM-JJ, affichage selon les réglages du site.
	 *
	 * @param string $libelle Libellé public du champ.
	 * @param string $date    Date au format AAAA-MM-JJ, ou chaîne vide.
	 *
	 * @return array<string,string> Champ enveloppé.
	 */
	public static function champ_date( string $libelle, string $date ): array {
		return array(
			'libelle'   => $libelle,
			'valeur'    => $date,
			'affichage' => self::date_en_toutes_lettres( $date ),
		);
	}

	/**
	 * Enveloppe une valeur de liste fermée : clé canonique en valeur, libellé français en affichage.
	 *
	 * Une clé inconnue est traitée comme une absence : on n'affiche jamais une clé technique.
	 *
	 * @param string   $libelle  Libellé public du champ.
	 * @param string   $cle      Clé stockée.
	 * @param string[] $libelles Clés reconnues vers libellés affichés.
	 *
	 * @return array<string,string> Champ enveloppé.
	 */
	public static function champ_liste( string $libelle, string $cle, array $libelles ): array {
		$connue = isset( $libelles[ $cle ] );

		return array(
			'libelle'   => $libelle,
			'valeur'    => $connue ? $cle : '',
			'affichage' => $connue ? $libelles[ $cle ] : self::ABSENCE,
		);
	}

	/**
	 * Enveloppe un compteur de chiots.
	 *
	 * La valeur reste la chaîne stockée : c'est la seule façon de distinguer « 0 mâle », qui est un
	 * fait, de « non renseigné », qui n'en est pas un.
	 *
	 * @param string $libelle   Libellé public du champ.
	 * @param string $valeur    Valeur stockée, suite de chiffres ou chaîne vide.
	 * @param string $singulier Nom au singulier.
	 * @param string $pluriel   Nom au pluriel.
	 *
	 * @return array<string,string> Champ enveloppé.
	 */
	private static function champ_compteur( string $libelle, string $valeur, string $singulier, string $pluriel ): array {
		return array(
			'libelle'   => $libelle,
			'valeur'    => $valeur,
			'affichage' => '' === $valeur ? self::ABSENCE : self::texte_compteur( (int) $valeur, $singulier, $pluriel ),
		);
	}

	/**
	 * Met une date de naissance en toutes lettres, selon les réglages du site.
	 *
	 * L'horodatage est construit à midi, jamais à minuit : sur un fuseau négatif, minuit bascule la
	 * veille et une portée née le 31 s'afficherait le 30. date_i18n() est proscrite.
	 *
	 * @param string $date Date au format AAAA-MM-JJ.
	 *
	 * @return string Date en toutes lettres, ou « Non renseigné ».
	 */
	public static function date_en_toutes_lettres( string $date ): string {
		if ( '' === $date ) {
			return self::ABSENCE;
		}

		$jour = \DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', $date . ' 12:00:00', wp_timezone() );

		if ( ! $jour instanceof \DateTimeImmutable || $jour->format( 'Y-m-d' ) !== $date ) {
			return self::ABSENCE;
		}

		$format = get_option( 'date_format' );

		if ( ! is_string( $format ) || '' === $format ) {
			$format = 'j F Y';
		}

		$texte = wp_date( $format, $jour->getTimestamp() );

		return is_string( $texte ) ? $texte : self::ABSENCE;
	}

	/**
	 * Compose le texte d'un compteur, au singulier ou au pluriel.
	 *
	 * @param int    $nombre    Nombre lu.
	 * @param string $singulier Nom au singulier.
	 * @param string $pluriel   Nom au pluriel.
	 *
	 * @return string Texte fini.
	 */
	private static function texte_compteur( int $nombre, string $singulier, string $pluriel ): string {
		return $nombre . ' ' . ( $nombre > 1 ? $pluriel : $singulier );
	}

	/**
	 * Compose l'effectif de la portée.
	 *
	 * Vide quand les deux compteurs le sont : on n'écrit pas « 0 mâle » quand on ne sait pas.
	 *
	 * @param string $males    Nombre de mâles, tel qu'il est stocké.
	 * @param string $femelles Nombre de femelles, tel qu'il est stocké.
	 *
	 * @return string Texte fini, chaîne vide si rien n'est connu.
	 */
	private static function texte_effectif( string $males, string $femelles ): string {
		$parties = array();

		if ( '' !== $males ) {
			$parties[] = self::texte_compteur( (int) $males, 'mâle', 'mâles' );
		}

		if ( '' !== $femelles ) {
			$parties[] = self::texte_compteur( (int) $femelles, 'femelle', 'femelles' );
		}

		return implode( ', ', $parties );
	}

	/**
	 * Ramène une valeur stockée à une chaîne, sans rien reformater.
	 *
	 * @param mixed $valeur Valeur stockée.
	 *
	 * @return string Chaîne, vide si la donnée manque.
	 */
	private static function texte( $valeur ): string {
		return is_scalar( $valeur ) ? (string) $valeur : '';
	}
}
