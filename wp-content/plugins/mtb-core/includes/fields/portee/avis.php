<?php
/**
 * Avis affichés après l'enregistrement d'une portée.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Fields\Portee;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Porte les avis d'un enregistrement jusqu'à l'écran qui suit la redirection.
 *
 * Un transient et non un argument d'URL : une adresse ne peut pas transporter ce que l'éleveuse a
 * tapé, or citer sa saisie — « vous aviez saisi : … » — est ce qui rend vraie la promesse que rien
 * n'est perdu, au lieu de se contenter de l'affirmer. L'interdit de transient du contrat de
 * l'extension vise les fonctions de lecture ; un avis d'administration de soixante secondes n'en
 * est pas une.
 */
final class Avis {

	/**
	 * Durée de vie d'un avis, en secondes : le temps d'une redirection, pas davantage.
	 *
	 * @var int
	 */
	private const DUREE = 60;

	/**
	 * Vrai quand le dernier enregistrement a ramené la portée en brouillon.
	 *
	 * Propriété statique et non variable globale : la surface globale de l'extension reste close.
	 * Elle ne sert qu'à la redirection, dans la même requête que l'enregistrement.
	 *
	 * @var bool
	 */
	private static bool $retour_en_brouillon = false;

	/**
	 * Classe purement statique : aucune instance n'a de sens.
	 */
	private function __construct() {}

	/**
	 * Nom du transient d'un utilisateur pour une portée.
	 *
	 * @param int $post_id Identifiant de la portée.
	 *
	 * @return string Clé du transient.
	 */
	private static function cle( int $post_id ): string {
		return 'mtb_portee_avis_' . get_current_user_id() . '_' . $post_id;
	}

	/**
	 * Retient les avis d'un enregistrement.
	 *
	 * @param int   $post_id Identifiant de la portée.
	 * @param array $avis    Éléments « niveau » et « texte ».
	 */
	public static function definir( int $post_id, array $avis ): void {
		if ( array() === $avis ) {
			return;
		}

		set_transient( self::cle( $post_id ), $avis, self::DUREE );
	}

	/**
	 * Lit puis supprime les avis retenus pour une portée.
	 *
	 * @param int $post_id Identifiant de la portée.
	 *
	 * @return array<int,array<string,string>> Éléments retenus, tableau vide s'il n'y en a pas.
	 */
	public static function consommer( int $post_id ): array {
		$avis = get_transient( self::cle( $post_id ) );

		delete_transient( self::cle( $post_id ) );

		return is_array( $avis ) ? $avis : array();
	}

	/**
	 * Signale que la portée vient d'être ramenée en brouillon.
	 */
	public static function signaler_retour_en_brouillon(): void {
		self::$retour_en_brouillon = true;
	}

	/**
	 * Dit si la portée vient d'être ramenée en brouillon dans cette requête.
	 *
	 * @return bool Vrai après une rétrogradation.
	 */
	public static function retour_en_brouillon(): bool {
		return self::$retour_en_brouillon;
	}
}

/**
 * Compose un élément d'avis.
 *
 * @param string $niveau « error » ou « warning ».
 * @param string $texte  Phrase à afficher.
 *
 * @return array<string,string> Élément d'avis.
 */
function avis( string $niveau, string $texte ): array {
	return array(
		'niveau' => $niveau,
		'texte'  => $texte,
	);
}

/**
 * Retire le message du cœur quand il annoncerait une publication qui n'a pas eu lieu.
 *
 * Le cœur compose son message après l'enregistrement : ayant reçu « Publier », il annonce
 * « Portée publiée. » quand bien même la portée vient d'être ramenée en brouillon faute d'un champ
 * obligatoire. C'est le seul rôle qui reste à ce filtre — les avis, eux, passent par le transient.
 *
 * @param mixed $adresse Adresse de redirection proposée.
 * @param mixed $post_id Identifiant du contenu enregistré.
 *
 * @return string Adresse de redirection retenue.
 */
function taire_le_message_de_publication( $adresse, $post_id ): string {
	$adresse = (string) $adresse;

	if ( 'mtb_portee' !== get_post_type( (int) $post_id ) || ! Avis::retour_en_brouillon() ) {
		return $adresse;
	}

	return remove_query_arg( 'message', $adresse );
}

/**
 * Affiche puis efface les avis de l'enregistrement précédent, sur l'écran d'une portée.
 */
function afficher_avis(): void {
	$ecran = get_current_screen();

	if ( null === $ecran || 'post' !== $ecran->base || 'mtb_portee' !== $ecran->post_type ) {
		return;
	}

	$post = get_post();

	if ( ! $post instanceof \WP_Post ) {
		return;
	}

	foreach ( Avis::consommer( (int) $post->ID ) as $element ) {
		if ( ! is_array( $element ) || ! isset( $element['texte'] ) || ! is_string( $element['texte'] ) ) {
			continue;
		}

		$niveau = isset( $element['niveau'] ) && 'error' === $element['niveau'] ? 'error' : 'warning';

		echo '<div class="notice notice-' . esc_attr( $niveau ) . ' is-dismissible"><p>' . esc_html( $element['texte'] ) . '</p></div>';
	}
}

/**
 * Compose la phrase qui nomme les champs obligatoires restés vides.
 *
 * @param string[] $manquants Libellés exacts des champs manquants.
 * @param bool     $brouillon Vrai si la portée vient d'être enregistrée en brouillon.
 * @param string   $statut    Statut de la portée après enregistrement.
 *
 * @return string Phrase à afficher.
 */
function phrase_champs_manquants( array $manquants, bool $brouillon, string $statut ): string {
	$liste = implode( ', ', $manquants );

	/*
	 * Aucun préfixe « Erreur : » ici : l'enregistrement a réussi, et ce préfixe est réservé à
	 * l'erreur d'un champ. Annoncer une erreur quand le travail est sauvegardé alarmerait pour rien.
	 */
	if ( $brouillon ) {
		return 'La portée est enregistrée en brouillon, elle n’est pas encore en ligne. Champs à remplir : ' . $liste . '.';
	}

	if ( 'publish' === $statut ) {
		return 'La portée reste en ligne, mais tout n’est pas rempli. Champs à remplir : ' . $liste . '.';
	}

	return 'La portée est enregistrée. Champs à remplir avant de la publier : ' . $liste . '.';
}

/**
 * Compose la phrase d'une date refusée, en citant la saisie.
 *
 * @param string $saisie    Ce que l'éleveuse a tapé.
 * @param string $conservee Date précédemment enregistrée, ou chaîne vide.
 *
 * @return string Phrase à afficher.
 */
function phrase_date_refusee( string $saisie, string $conservee ): string {
	$phrase = 'La date de naissance n’a pas été modifiée : « ' . $saisie . ' » n’est pas une date. ';

	if ( '' !== $conservee ) {
		$phrase .= 'La date précédente a été conservée. ';
	}

	return $phrase . 'Vous pouvez l’écrire sous la forme 04/03/2024.';
}

/**
 * Compose la phrase d'avertissement d'un identifiant déjà employé.
 *
 * @param string $identifiant Identifiant saisi.
 *
 * @return string Phrase à afficher.
 */
function phrase_doublon( string $identifiant ): string {
	if ( '' === $identifiant ) {
		return 'Une autre portée porte déjà le même identifiant. Vérifiez qu’il ne s’agit pas d’un doublon.';
	}

	return 'Une autre portée porte déjà l’identifiant « ' . $identifiant . ' ». Vérifiez qu’il ne s’agit pas d’un doublon.';
}
