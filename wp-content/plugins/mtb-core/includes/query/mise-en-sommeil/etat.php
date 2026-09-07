<?php
/**
 * L'état « en sommeil » : sa clé, sa lecture, et la liste des contenus qui le portent.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Query\MiseEnSommeil;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * CE FICHIER EST LA SEULE SOURCE DE VÉRITÉ DE LA CLÉ ET DE SA LECTURE.
 *
 * Les écrans de saisie, la mention dans les listes et la conversion du fait hérité le require_once
 * plutôt que de recopier la chaîne « _mtb_en_sommeil » ou de refaire un get_post_meta(). Le motif
 * n'est pas l'élégance : une lecture recopiée ailleurs perdrait le repli ci-dessous, et un contenu
 * dont la méta serait abîmée se lirait « endormi » à un endroit et « visible » à un autre, sans que
 * rien ne le dise. Interdit opposable, §12 du contrat #52.
 *
 * TROIS ÉTATS, ET LE TROISIÈME N'EST PAS UN RAFFINEMENT :
 *
 *   '1'         → en sommeil ;
 *   '0'         → réveillé explicitement, c'est-à-dire UNE DÉCISION DE L'ÉLEVEUSE ;
 *   clé absente → jamais réglé, état de départ.
 *
 * Séparer « absente » de « '0' » est ce qui rend la conversion des contenus repris ré-exécutable
 * sans jamais rendormir ce que l'éleveuse a réveillé — sans option-drapeau, sans commande, sans état
 * de migration à maintenir. C'est aussi pourquoi AUCUN « default » n'est posé à
 * register_post_meta() : un défaut rendrait get_post_meta() incapable de faire cette différence.
 * Le test d'absence se fait par metadata_exists(), jamais par get_post_meta().
 */

/**
 * Clé de la métadonnée qui porte l'état « en sommeil ».
 *
 * Préfixée d'un souligné, donc hors du panneau des champs du cœur : l'éleveuse ne voit jamais cette
 * clé sous son nom, elle voit une case à cocher et une mention en français.
 */
const CLE = '_mtb_en_sommeil';

/**
 * Valeur qui endort un contenu. Seule cette chaîne exacte a cet effet.
 */
const ENDORMI = '1';

/**
 * Valeur écrite quand l'éleveuse décoche la case : réveillé, explicitement.
 */
const REVEILLE = '0';

/**
 * Dit si un contenu est en sommeil.
 *
 * REPLI OUVERT, DÉLIBÉRÉ : SEULE LA CHAÎNE EXACTE « 1 » ENDORT. Toute autre valeur — vide, « oui »,
 * un tableau sérialisé par erreur, une méta tronquée par un import raté — vaut VISIBLE. Le motif
 * s'écrit dans un sens et un seul : une méta abîmée ne doit pas retirer un contenu des moteurs à
 * jamais sans que rien ne le dise. Le repli inverse — « tout ce qui n'est pas explicitement '0'
 * endort » — ferait disparaître un contenu des index sur une corruption, c'est-à-dire exactement la
 * panne que personne ne voit passer, puisque la page continue de répondre 200.
 *
 * LA GARDE « is_scalar() » N'EST PAS UNE PRÉCAUTION DE STYLE, ELLE EST MESURÉE. Sans elle,
 * « (string) $valeur » sur une méta sérialisée lève « PHP Warning: Array to string conversion », donc
 * UNE LIGNE PAR AFFICHAGE dans « debug.log » — et ce module a pour propriété déclarée de ne rien
 * journaliser. Un contenu dont la méta serait abîmée noierait le journal du site au lieu de
 * simplement redevenir visible. La garde rend le repli SILENCIEUX, ce qui est la moitié de sa raison
 * d'être ; le verdict, lui, est identique pour toute valeur scalaire.
 *
 * @param int $identifiant Identifiant du contenu.
 *
 * @return bool Vrai seulement si la métadonnée vaut exactement « 1 ».
 */
function est_en_sommeil( int $identifiant ): bool {
	if ( $identifiant <= 0 ) {
		return false;
	}

	$valeur = get_post_meta( $identifiant, CLE, true );

	if ( ! is_scalar( $valeur ) ) {
		return false;
	}

	return ENDORMI === (string) $valeur;
}

/**
 * Liste les identifiants des contenus publiés qui sont en sommeil.
 *
 * MÉMOÏSATION PAR REQUÊTE ET DRAPEAU DE RÉ-ENTRANCE : CE N'EST PAS UN ORNEMENT, C'EST LE MODULE.
 * Cette fonction exécute une WP_Query depuis l'intérieur de « pre_get_posts » et depuis la
 * construction de chaque sous-plan du plan du site. Sans mémoïsation, le plan du site la rejouerait
 * une fois par type de contenu ; sans drapeau de ré-entrance, la WP_Query interne déclencherait à
 * son tour « pre_get_posts », qui rappellerait cette fonction, qui lancerait une WP_Query, et ainsi
 * de suite. Le drapeau rend array() pendant que la requête est en vol : une liste vide est un no-op
 * strict de WP_Query, donc la requête interne s'exécute non filtrée, ce qui est exactement ce qu'on
 * veut d'elle — elle cherche les endormis, elle ne doit pas s'en exclure elle-même. La garde
 * « ! is_main_query() » de exclure_de_la_recherche() ferme le même chemin par l'autre bout.
 *
 * UNE SEULE CLAUSE « = », JAMAIS DE « NOT EXISTS » NI DE COMPARAISON NÉGATIVE. La forme envisagée au
 * brainstorm — OR( clé NOT EXISTS , clé != '1' ) — est écartée sur mesure du cœur, pas sur
 * préférence : voir le bloc de « bootstrap.php » qui en porte la démonstration. Ici, la clause
 * sélectionne UNIQUEMENT les endormis, par un INNER JOIN qualifié par la clé, et un contenu qui ne
 * porte aucune ligne dans « wp_postmeta » n'est jamais touché.
 *
 * « post_type => any » couvre page, portée et fiche de chien sans les nommer, et laisse dehors les
 * types déclarés « exclude_from_search » — dont « mtb_resultat », qui n'a ni adresse, ni plan du
 * site, ni recherche : un sommeil y serait sans objet. « post_status => publish » suffit : un
 * brouillon n'est ni au plan du site, ni dans la recherche du site.
 *
 * @return array<int, int> Identifiants des contenus en sommeil, éventuellement vide.
 */
function identifiants_en_sommeil(): array {
	static $memoire  = null;
	static $en_cours = false;

	if ( is_array( $memoire ) ) {
		return $memoire;
	}

	if ( true === $en_cours ) {
		return array();
	}

	$en_cours = true;

	$requete = new \WP_Query(
		array(
			'post_type'              => 'any',
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'meta_query'             => array(
				array(
					'key'     => CLE,
					'value'   => ENDORMI,
					'compare' => '=',
				),
			),
		)
	);

	$en_cours = false;

	$identifiants = array();

	foreach ( $requete->posts as $identifiant ) {
		$identifiants[] = (int) $identifiant;
	}

	$memoire = $identifiants;

	return $memoire;
}
