<?php
/**
 * Renomme, dans l'administration, le champ que WordPress appelle « Texte alternatif ».
 *
 * CE QUE L'ÉLEVEUSE VOIT CHANGER. Sous chaque photo, dans la fenêtre des photos comme sur l'écran de
 * modification d'une photo, l'étiquette « Texte alternatif » devient « Description de la photo (pour
 * les personnes aveugles) ». Rien d'autre ne bouge : ni la valeur qu'elle a saisie, ni l'endroit où
 * le champ se trouve, ni le site public. Le libellé est celui que « design-system/MASTER.md » §10.2
 * fige, et §10.4 range « alt » parmi les mots interdits à l'écran.
 *
 * CE QUI CONTINUE DE DIRE « TEXTE ALTERNATIF », ET QUE CE MODULE NE PEUT PAS ATTEINDRE : les blocs
 * Image et Galerie du cœur, dans l'éditeur. Leurs étiquettes sont servies par « wp.i18n » depuis un
 * catalogue JavaScript, et aucun filtre PHP ne les atteint. Ces blocs sont insérables par
 * l'éleveuse aujourd'hui, elle y lira donc encore l'ancien mot : c'est la dette T-#35-a.
 *
 * DE MÊME, LE MENU S'APPELLE TOUJOURS « MÉDIAS », alors que §10.4 range « média » parmi les mots
 * interdits et dit « photo ». Celui-là est à la portée de ce module, mais pas sans sa propre carte
 * d'émission : la chaîne source « Media » compte six émissions « __( 'Media' ) » et douze au total,
 * et la renommer à l'aveugle déborderait. C'est la dette T-#35-b.
 *
 * MODULE DISTINCT DE « admin/medias », À NE PAS Y RANGER. Trois raisons, toutes vérifiables :
 * « admin/medias » assume de n'avoir AUCUNE garde « is_admin() », parce que ses réglages doivent agir
 * au téléversement, y compris sous WP-CLI ; le nôtre est exclusivement wp-admin et doit être gardé.
 * Préfixer « admin/medias » d'un « _ » pour désactiver le renommage emporterait la conversion WebP,
 * irrattrapable une fois des photos importées. Enfin « admin/medias » agit sur le fichier, ce module
 * sur le mot : ce ne sont pas les mêmes sujets.
 *
 * POURQUOI « gettext » ET NON « attachment_fields_to_edit ». Mesuré, non supposé, sur WordPress 6.9,
 * par une sonde jetable qui journalisait les clés reçues et posait une sentinelle sur le libellé de
 * « image_alt ». Résultat : la sentinelle n'apparaît sur AUCUN parcours (écran d'une portée, écran
 * d'un chien, « upload.php », « upload.php?mode=grid », écran d'une photo, réponse AJAX
 * « query-attachments »). Le crochet ne s'exécute que sur deux de ces six parcours (l'écran d'une
 * photo et la réponse AJAX, une ligne de journal chacun), et toujours depuis
 * « get_compat_media_markup() » (wp-admin/includes/media.php:1935), avec un tableau de champs VIDE —
 * cette fonction retirant de toute façon « image_alt » de son résultat trois lignes plus bas. Le seul
 * appel qui porte « image_alt » est celui de « get_attachment_fields_to_edit() » (media.php:1509),
 * atteint par le seul iframe hérité de téléversement, qu'aucun écran d'aujourd'hui n'ouvre. Le
 * crochet prescrit ne pouvait donc pas mordre. C'est ce que la dette T16 du contrat #6 annonçait.
 *
 * LES HUIT ENDROITS OÙ LE CŒUR ÉMET CES DEUX CHAÎNES, relevés sur WORDPRESS 6.9 — recopiés depuis la
 * source PHP du conteneur, jamais depuis un catalogue de traduction. Toute chaîne future qui doit
 * remesurer commence par cette liste :
 *
 *   « Alternative Text » — sept émissions, toutes des étiquettes de description d'image :
 *     wp-admin/includes/media.php:1485     get_attachment_fields_to_edit()  (chemin hérité)
 *     wp-admin/includes/media.php:2982     wp_media_insert_url_form()       (image par adresse)
 *     wp-admin/includes/media.php:3233     edit_form_image_editor()         (écran d'une photo)
 *     wp-includes/media-template.php:516   wp_print_media_templates()       (fiche deux colonnes)
 *     wp-includes/media-template.php:1074  wp_print_media_templates()       (réglages d'insertion)
 *     wp-includes/media-template.php:1137  wp_print_media_templates()       (détails d'une image)
 *     wp-includes/widgets/class-wp-widget-media-image.php:98
 *                                          WP_Widget_Media_Image::get_instance_schema()
 *
 *   « Alt Text » — une seule émission, le volet de droite de la fenêtre des photos :
 *     wp-includes/media-template.php:768   wp_print_media_templates()       (fiche une colonne)
 *
 * Aucune de ces huit émissions ne passe par « _x() » : le filtre « gettext_with_context » n'est donc
 * pas posé. Un crochet inutile est un coût non payé.
 *
 * LE TEXTE D'AIDE DU CŒUR N'EST PAS REMPLACÉ, ET C'EST UNE DÉCISION. La phrase qui suit le champ
 * (media-template.php:161 et wp-admin/includes/media.php:3241) a pour source
 * « <a href="%1$s" %2$s>Learn how to describe the purpose of the image%3$s</a>. Leave empty if the
 * image is purely decorative. ». Elle porte un lien sortant vers l'arbre de décision du W3C, c'est-
 * à-dire une information factuelle que notre propre phrase d'aide ne dit pas. On ne remplace jamais
 * un texte par un autre qui en dit moins. Elle porte de surcroît « % », « < » et « > », que le
 * troisième interdit ci-dessous proscrit. Seule l'étiquette est renommée.
 *
 * ON COMPARE SUR L'ANGLAIS, JAMAIS SUR LE FRANÇAIS. « gettext » reçoit la chaîne source en deuxième
 * paramètre et sa traduction en premier. Comparer la source rend le module indifférent à l'état de la
 * langue du site : il mord aussi bien sur une installation en français que sur une installation
 * restée en anglais, cas que le provisionnement documente comme possible.
 *
 * MODE DE PANNE, À CONNAÎTRE. Le jour où WordPress reformule l'une de ces deux chaînes sources, la
 * comparaison ne trouve plus rien et l'écran redit « Texte alternatif ». La panne est BÉNIGNE — rien
 * n'est cassé, aucune donnée n'est perdue, aucune erreur n'est levée, on revient exactement à l'état
 * d'avant ce module — mais elle est SILENCIEUSE. Le contrôle est de relire les huit lignes ci-dessus
 * dans le cœur installé et de comparer leurs chaînes sources à la table ci-dessous.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Admin\DescriptionPhoto;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Aucun crochet n'est posé hors de l'administration, et c'est la garde la plus forte possible : le
 * rappel n'est pas seulement inerte sur le site public, il n'y est jamais attaché. Elle compte
 * doublement ici, « gettext » étant appelé pour CHAQUE chaîne traduite de CHAQUE requête : non gardé,
 * ce module ferait payer une comparaison à chaque visiteur anonyme, pour un libellé qu'aucun visiteur
 * ne voit jamais.
 *
 * LIMITE ASSUMÉE : sur une requête REST, « is_admin() » vaut faux et le libellé n'est pas remplacé.
 * C'est correct et voulu — une route REST rend des données, jamais des libellés d'écran, et sa
 * description de schéma emploie d'ailleurs une tout autre chaîne (« Alternative text to display when
 * attachment is not displayed. »), qui n'est pas de notre ressort.
 */
if ( ! is_admin() ) {
	return;
}

add_filter( 'gettext', __NAMESPACE__ . '\\remplacer_libelle', 10, 3 );

/**
 * Les deux chaînes sources du cœur et le libellé français qui les remplace.
 *
 * Table GELÉE : elle ne se déduit de rien, ne se calcule pas, ne lit rien en base et n'admet aucun
 * filtre. Les clés sont les chaînes sources anglaises recopiées octet pour octet depuis le cœur
 * (WordPress 6.9, huit lignes citées dans l'en-tête). La valeur est le libellé de
 * « design-system/MASTER.md » §10.2, recopié verbatim.
 *
 * LES DEUX CLÉS RENDENT LE MÊME LIBELLÉ, ET ELLES RESTENT DEUX ENTRÉES. C'est le même champ vu de
 * deux écrans, et le français du cœur les traduit d'ailleurs identiquement, par « Texte alternatif » ;
 * les fondre ferait qu'une reformulation de l'une entraînerait l'autre en silence.
 *
 * Mémoïsée par un « static » local : la table est construite une fois par requête, alors que le
 * rappel est appelé des milliers de fois sur un écran d'administration.
 *
 * @return array<string, string> Chaîne source anglaise du cœur => libellé affiché.
 */
function table(): array {
	static $table = null;

	if ( null === $table ) {
		$table = array(
			'Alternative Text' => 'Description de la photo (pour les personnes aveugles)',
			'Alt Text'         => 'Description de la photo (pour les personnes aveugles)',
		);
	}

	return $table;
}

/**
 * Rend le libellé français à la place de la traduction du cœur, pour ces deux chaînes et rien d'autre.
 *
 * QUATRE INTERDITS DANS CE RAPPEL, chacun pour une panne précise :
 *
 * 1. AUCUNE FONCTION DE TRADUCTION — ni « __() », ni « _e() », ni « translate() », ni
 *    « esc_html__() ». Nous SOMMES dans « gettext » : le moindre appel se rappellerait lui-même, en
 *    récursion infinie, dès la première chaîne de la première page.
 * 2. ON N'ÉCHAPPE PAS, et c'est délibéré malgré la règle générale du projet. Ce module N'IMPRIME
 *    RIEN : il rend une chaîne que le cœur échappe et imprime lui-même. Échapper ici doublerait
 *    l'échappement, et une apostrophe ressortirait affichée en « &#039; » au milieu de l'étiquette.
 * 3. AUCUNE CHAÎNE DE REMPLACEMENT NE CONTIENT « % », « < », « > » NI « & ». Le « % » d'abord :
 *    plusieurs chaînes du cœur sont passées à « sprintf() », et un « % » de trop y lève une
 *    ArgumentCountError, c'est-à-dire un écran blanc — le piège que
 *    « includes/admin/corbeille/bootstrap.php » documente déjà. Le libellé retenu n'en porte aucun.
 * 4. AUCUN « get_current_screen() », AUCUNE EXPRESSION RÉGULIÈRE, AUCUN « stripos », AUCUN
 *    « str_replace ». Comparaison stricte et exacte de la chaîne entière, ou rien : c'est ce qui
 *    borne le module aux huit émissions relevées et interdit qu'il déborde sur un écran qui parle
 *    d'autre chose. « get_current_screen() » n'est de surcroît pas défini à toute heure de la requête.
 *
 * Les paramètres et le retour ne sont pas typés, comme « Medias\format_de_sortie() » et
 * « Corbeille\completer_messages() », et pour le même motif : sous strict_types, un tiers passant
 * autre chose qu'une chaîne transformerait un rendu d'étiquette en erreur fatale au milieu d'un
 * écran. Un libellé resté en anglais vaut infiniment mieux qu'un écran blanc.
 *
 * Les gardes vont de la moins chère à la plus chère, le domaine d'abord : l'immense majorité des
 * appels vient d'un autre domaine ou d'une chaîne absente de la table, et repart en deux comparaisons.
 *
 * @param mixed $traduction Traduction que le cœur s'apprête à rendre.
 * @param mixed $texte      Chaîne source anglaise, telle qu'écrite dans le cœur.
 * @param mixed $domaine    Domaine de traduction de l'appel.
 *
 * @return mixed Le libellé français pour nos deux chaînes, la traduction reçue partout ailleurs.
 */
function remplacer_libelle( $traduction, $texte, $domaine ) {
	if ( 'default' !== $domaine ) {
		return $traduction;
	}

	if ( ! is_string( $texte ) ) {
		return $traduction;
	}

	$table = table();

	if ( ! isset( $table[ $texte ] ) ) {
		return $traduction;
	}

	return $table[ $texte ];
}
