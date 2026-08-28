<?php
/**
 * Écriture d'un contenu repris, et contrôle aval de ce qui a réellement été stocké.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\PorteesChiens;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * SEUL FICHIER DU MODULE À ÉCRIRE DANS LA BASE, hors du versement des photographies. Il n'appelle
 * ni wp_delete_post, ni wp_trash_post, ni delete_post_meta : la reprise ne supprime jamais rien.
 *
 * LES ANTISLASHS — LA CORRUPTION SILENCIEUSE À ÉVITER
 *
 * update_metadata() appelle wp_unslash() PUIS sanitize_meta() ; wp_insert_post() appelle
 * wp_unslash() sur tout le tableau. Les deux attendent des données échappées, parce qu'elles sont
 * écrites pour recevoir du $_POST — or une valeur venue de json_decode() ne l'est pas. Sans
 * wp_slash(), un résultat de test « N/N » écrit « N\N » perdrait son antislash sans une erreur ni
 * un avertissement, et le site afficherait un résultat de santé faux.
 *
 * wp_slash() est donc appliqué à TOUT ce qui part vers l'écriture, tableaux et entiers compris.
 * Aucune exception « c'est un entier, donc inutile » : une exception est une règle qu'on oublie, et
 * wp_slash() traverse récursivement en laissant les entiers intacts.
 *
 * LE COMPTE QUI EXÉCUTE, ET POURQUOI ÇA COMPTE
 *
 * Sans « --user », WP-CLI n'ouvre aucune session : current_user_can( 'unfiltered_html' ) est faux,
 * les filtres kses sont posés, et « post_content » traverse wp_kses() — qui transforme « <60% » en
 * « &lt;60% ». Rien n'est perdu, mais la valeur stockée cesse d'être celle du fichier. Le contrôle
 * ci-dessous le NOMME au lieu de le laisser passer ; la commande, elle, le dit avant de commencer.
 */

/**
 * Insère un contenu.
 *
 * @param string               $type_de_contenu Type de contenu WordPress.
 * @param array<string, mixed> $champs          Champs de « wp_posts », valeurs brutes non échappées.
 *
 * @return int Identifiant créé, ou 0 si WordPress a refusé l'insertion.
 */
function inserer( string $type_de_contenu, array $champs ): int {
	$postarr = array_merge(
		$champs,
		array(
			'post_type'   => $type_de_contenu,
			'post_status' => 'publish',
		)
	);

	$post_id = wp_insert_post( wp_slash( $postarr ), true );

	if ( is_wp_error( $post_id ) ) {
		return 0;
	}

	return (int) $post_id;
}

/**
 * Écrit une série de clés de méta.
 *
 * @param int                  $post_id Contenu porteur.
 * @param array<string, mixed> $metas   Clé de méta => valeur brute non échappée.
 */
function ecrire_metas( int $post_id, array $metas ): void {
	foreach ( $metas as $cle => $valeur ) {
		update_post_meta( $post_id, (string) $cle, wp_slash( $valeur ) );
	}
}

/**
 * Rattache l'image mise en avant d'un contenu.
 *
 * @param int $post_id      Contenu porteur.
 * @param int $piece_jointe Identifiant de la pièce jointe.
 */
function definir_photo( int $post_id, int $piece_jointe ): void {
	set_post_thumbnail( $post_id, $piece_jointe );
}

/**
 * Relit ce qui a été stocké et rend les divergences constatées.
 *
 * Deuxième raison d'être de ce contrôle, et la plus importante : il compare la relecture à la
 * valeur BRUTE du fichier RÉ-ASSAINIE, jamais à ce qui a été envoyé. Toute perte d'échappement
 * devient donc un défaut nommé, sur un contenu déjà créé et jamais supprimé. Il voit aussi ce que
 * l'amont ne peut pas voir — le titre, le slug, le contenu, l'image mise en avant, qui n'ont aucun
 * assainisseur de modèle.
 *
 * Il porte sur les 44 entités, jamais sur un échantillon : c'est lui, et lui seul, qui attrape
 * « N/N » devenu « N\N », une valeur vidée, une espace insécable perdue.
 *
 * @param string               $jeu     « chiens » ou « portees ».
 * @param int                  $post_id Contenu écrit.
 * @param array<string, mixed> $metas   Clé de méta => valeur brute demandée.
 * @param array<string, mixed> $champs  Champ de contenu => valeur brute demandée.
 *
 * @return string[] Divergences rédigées ; liste vide si tout est conforme.
 */
function controler_aval( string $jeu, int $post_id, array $metas, array $champs ): array {
	$divergences = array();

	foreach ( $metas as $cle => $brut ) {
		$attendu = assainir( $jeu, (string) $cle, $brut );
		$stocke  = get_post_meta( $post_id, (string) $cle, true );

		if ( ! equivalent( $attendu, $stocke ) ) {
			$divergences[] = phrase_de_divergence( (string) $cle, $attendu, $stocke );
		}
	}

	$contenu = get_post( $post_id );

	if ( ! $contenu instanceof \WP_Post ) {
		return $divergences;
	}

	foreach ( $champs as $champ => $attendu ) {
		$stocke = isset( $contenu->{$champ} ) ? $contenu->{$champ} : '';

		if ( ! equivalent( $attendu, $stocke ) ) {
			$divergences[] = phrase_de_divergence( (string) $champ, $attendu, $stocke );
		}
	}

	return $divergences;
}

/**
 * Compose la phrase d'une divergence.
 *
 * @param string $cle     Clé concernée.
 * @param mixed  $attendu Valeur demandée.
 * @param mixed  $stocke  Valeur relue.
 *
 * @return string Phrase rédigée.
 */
function phrase_de_divergence( string $cle, $attendu, $stocke ): string {
	return sprintf(
		'le champ « %s » n\'a pas été stocké tel que demandé (demandé %s, stocké %s).',
		$cle,
		rendre_valeur( $attendu ),
		rendre_valeur( $stocke )
	);
}

/**
 * Deux valeurs disent-elles la même chose, une fois passées par la base ?
 *
 * La comparaison se fait sur un cast chaîne récursif : une méta déclarée « integer » revient
 * toujours en chaîne de la base, et un tableau revient avec ses valeurs en chaîne. Comparer sans ce
 * cast ferait crier la commande sur chaque entier, et le vrai signal se noierait.
 *
 * @param mixed $attendu Valeur demandée.
 * @param mixed $stocke  Valeur relue.
 *
 * @return bool Vrai si les deux valeurs se valent.
 */
function equivalent( $attendu, $stocke ): bool {
	return cast_chaine( $attendu ) === cast_chaine( $stocke );
}

/**
 * Ramène récursivement une valeur à des chaînes.
 *
 * @param mixed $valeur Valeur.
 *
 * @return mixed Chaîne, ou tableau de chaînes.
 */
function cast_chaine( $valeur ) {
	if ( is_array( $valeur ) ) {
		return array_map( __NAMESPACE__ . '\\cast_chaine', $valeur );
	}

	if ( is_bool( $valeur ) ) {
		return $valeur ? '1' : '';
	}

	return is_scalar( $valeur ) ? (string) $valeur : '';
}
