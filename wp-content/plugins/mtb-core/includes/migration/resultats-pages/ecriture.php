<?php
/**
 * Écriture d'un contenu repris, et contrôle aval de ce qui a réellement été stocké.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\ResultatsPages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * SEUL FICHIER DU MODULE À ÉCRIRE — contenus, champs et pièces jointes. La propriété se vérifie par
 * une recherche : hors d'ici, le module ne nomme ni wp_insert_post, ni wp_update_post, ni
 * update_post_meta, ni media_handle_sideload. Il n'appelle ni wp_delete_post, ni wp_trash_post, ni
 * delete_post_meta, ni $wpdb : la reprise ne supprime jamais rien et n'écrit aucune requête. Le seul
 * fichier qu'elle efface est la copie temporaire qu'elle vient elle-même de faire pour verser une
 * image.
 *
 * ELLE NE MET JAMAIS À JOUR UN CONTENU EXISTANT NON PLUS. Les sept pages existent déjà en base de
 * développement, et l'éleveuse y corrigera des mots : son travail n'est jamais écrasé par un outil
 * qu'elle ne voit pas. La seule écriture sur un contenu déjà là est celle de « --raccrocher », qui
 * ne touche qu'un champ dont la valeur actuelle est zéro.
 *
 * LES ANTISLASHS — la corruption silencieuse à éviter
 *
 * update_metadata() appelle wp_unslash() PUIS sanitize_meta() ; wp_insert_post() appelle
 * wp_unslash() sur tout le tableau. Les deux attendent des données échappées, parce qu'elles sont
 * écrites pour recevoir du $_POST — or une valeur venue de json_decode() ne l'est pas. Sans
 * wp_slash(), un niveau recopié « Ring 1\2 » perdrait son antislash sans une erreur ni un
 * avertissement.
 *
 * wp_slash() est donc appliqué à TOUT ce qui part vers l'écriture, tableaux et entiers compris.
 * Aucune exception « c'est un entier, donc inutile » : une exception est une règle qu'on oublie, et
 * wp_slash() traverse récursivement en laissant les entiers intacts.
 */

/**
 * Le compte courant peut-il écrire du balisage de blocs sans qu'il soit filtré ?
 *
 * En WP-CLI sans « --user », aucun utilisateur n'est connecté : « wp_filter_post_kses » s'accroche
 * à « content_save_pre », et son preg_replace( '/--+/', '-' ) s'applique au contenu des
 * commentaires de blocs. Le balisage des pages serait détruit à l'écriture, en silence.
 *
 * @return bool Vrai si l'écriture peut avoir lieu.
 */
function peut_ecrire(): bool {
	return current_user_can( 'unfiltered_html' );
}

/**
 * Insère un contenu.
 *
 * @param string               $type_de_contenu Type de contenu WordPress.
 * @param array<string, mixed> $champs          Champs de « wp_posts », valeurs brutes non échappées.
 *
 * @return int Identifiant créé, ou 0 si WordPress a refusé l'insertion.
 */
function inserer( string $type_de_contenu, array $champs ): int {
	$postarr = array_merge( $champs, array( 'post_type' => $type_de_contenu ) );

	if ( ! isset( $postarr['post_status'] ) ) {
		$postarr['post_status'] = 'publish';
	}

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
 * Remplace le titre provisoire d'un résultat par celui que compose le serveur.
 *
 * Le contenu vient d'être créé par la même exécution : ce n'est pas la mise à jour d'un contenu
 * existant, c'est l'achèvement d'une création. Le titre ne peut pas être écrit avant les champs,
 * puisqu'il les lit.
 *
 * @param int    $post_id    Identifiant du résultat.
 * @param string $provisoire Titre déjà écrit.
 *
 * @return string Titre effectivement demandé.
 */
function achever_le_titre( int $post_id, string $provisoire ): string {
	$titre = titre_de_resultat( $post_id, $provisoire );

	if ( $titre === $provisoire ) {
		return $provisoire;
	}

	wp_update_post(
		wp_slash(
			array(
				'ID'         => $post_id,
				'post_title' => $titre,
			)
		)
	);

	return $titre;
}

/**
 * Pose le lien vers une fiche chien sur un résultat qui n'en a pas.
 *
 * N'écrit que si la valeur actuelle vaut zéro : un lien posé à la main par l'éleveuse n'est jamais
 * écrasé.
 *
 * @param int $post_id  Identifiant du résultat.
 * @param int $chien_id Identifiant de la fiche chien.
 *
 * @return bool Vrai si le lien a été posé.
 */
function poser_le_lien_chien( int $post_id, int $chien_id ): bool {
	if ( 0 === $chien_id ) {
		return false;
	}

	if ( 0 !== (int) get_post_meta( $post_id, '_mtb_chien_id', true ) ) {
		return false;
	}

	update_post_meta( $post_id, '_mtb_chien_id', wp_slash( $chien_id ) );

	return true;
}

/**
 * Recopie une image hors de son dossier d'origine, puis la verse dans la médiathèque.
 *
 * media_handle_sideload() DÉPLACE le fichier qu'on lui donne : l'image est donc recopiée dans le
 * dossier temporaire avant d'être versée, pour qu'un dossier monté en lecture seule reste intact.
 * Le seul fichier que cette fonction efface est la copie temporaire qu'elle vient de faire.
 *
 * @param string $nom    Nom de fichier voulu.
 * @param string $source Chemin du fichier d'origine.
 *
 * @return int Identifiant de la pièce jointe, 0 en cas d'échec.
 */
function verser( string $nom, string $source ): int {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$temporaire = wp_tempnam( $nom );

	if ( '' === $temporaire || ! copy( $source, $temporaire ) ) {
		return 0;
	}

	$piece_jointe = media_handle_sideload(
		array(
			'name'     => $nom,
			'tmp_name' => $temporaire,
		),
		0
	);

	if ( is_wp_error( $piece_jointe ) ) {
		// media_handle_sideload() ne déplace pas le fichier quand il échoue : à la reprise de le retirer.
		if ( file_exists( $temporaire ) ) {
			wp_delete_file( $temporaire );
		}

		return 0;
	}

	return (int) $piece_jointe;
}

/**
 * Relit ce qui a été stocké sur un résultat et rend les divergences constatées.
 *
 * Il compare la relecture à la valeur BRUTE du fichier, non échappée : toute erreur d'échappement
 * devient donc un rejet nommé, sur un contenu déjà créé et jamais supprimé.
 *
 * @param int                  $post_id Contenu écrit.
 * @param array<string, mixed> $metas   Clé de méta => valeur brute demandée.
 * @param array<string, mixed> $champs  Champ de contenu => valeur brute demandée.
 *
 * @return string[] Divergences rédigées ; liste vide si tout est conforme.
 */
function controler_aval_resultat( int $post_id, array $metas, array $champs ): array {
	return array_merge(
		divergences_de_metas( $post_id, $metas ),
		controler_les_champs( $post_id, $champs )
	);
}

/**
 * Relit les champs stockés sur un contenu et rend les divergences avec les valeurs demandées.
 *
 * La comparaison porte sur la valeur BRUTE du fichier passée à l'assainisseur du modèle, jamais sur
 * une valeur déjà échappée : une erreur d'échappement devient donc une divergence nommée. La reprise
 * comme la vérification posent la même question, et la posent au même endroit.
 *
 * @param int                  $post_id Contenu relu.
 * @param array<string, mixed> $metas   Clé de méta => valeur brute demandée.
 *
 * @return string[] Divergences rédigées ; liste vide si tout est conforme.
 */
function divergences_de_metas( int $post_id, array $metas ): array {
	$divergences = array();

	foreach ( $metas as $cle => $brut ) {
		$attendu = assainir( (string) $cle, $brut );
		$stocke  = get_post_meta( $post_id, (string) $cle, true );

		if ( ! equivalent( $attendu, $stocke ) ) {
			$divergences[] = phrase_de_divergence( (string) $cle, $attendu, $stocke );
		}
	}

	return $divergences;
}

/**
 * Relit une page et rend les divergences constatées, balisage compris.
 *
 * Deuxième des trois contrôles du balisage : il porte sur le contenu RELU EN BASE, et attrape donc
 * ce qu'aucun contrôle amont ne peut voir — un wp_slash() manquant, un filtrage kses qui aurait
 * réduit les « -- » d'un commentaire de bloc.
 *
 * @param int                  $post_id Contenu écrit.
 * @param array<string, mixed> $champs  Champ de contenu => valeur brute demandée.
 * @param array<string, string> $robots Fait de robots demandé, tableau vide si la page n'en a pas.
 *
 * @return string[] Divergences rédigées.
 */
function controler_aval_page( int $post_id, array $champs, array $robots = array() ): array {
	$divergences = controler_les_champs( $post_id, $champs );

	if ( array() !== $robots ) {
		$stocke = get_post_meta( $post_id, CLE_ROBOTS, true );

		if ( ! equivalent( $robots, $stocke ) ) {
			$divergences[] = phrase_de_divergence( CLE_ROBOTS, $robots, $stocke );
		}
	}

	$contenu = get_post_field( 'post_content', $post_id, 'raw' );

	if ( ! is_string( $contenu ) || '' === $contenu ) {
		return $divergences;
	}

	if ( ! aller_retour_stable( $contenu ) ) {
		$divergences[] = 'le balisage relu en base ne se réécrit pas à l\'identique : un commentaire '
			. 'de bloc a été altéré à l\'écriture.';
	}

	return $divergences;
}

/**
 * Compare les champs de « wp_posts » demandés à ceux qui ont été stockés.
 *
 * @param int                  $post_id Contenu écrit.
 * @param array<string, mixed> $champs  Champ de contenu => valeur brute demandée.
 *
 * @return string[] Divergences rédigées.
 */
function controler_les_champs( int $post_id, array $champs ): array {
	$divergences = array();
	$contenu     = get_post( $post_id );

	if ( ! $contenu instanceof \WP_Post ) {
		return array( 'le contenu créé n\'a pas pu être relu.' );
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
 * toujours en chaîne de la base. Comparer sans ce cast ferait crier la commande sur chaque entier,
 * et le vrai signal se noierait.
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
