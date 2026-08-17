<?php
/**
 * Réglages de traitement des photos téléversées : sous-taille de vignette et format moderne.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Admin\Medias;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * LIMITE COMMUNE AUX DEUX RÉGLAGES, ET RAISON D'ÊTRE DE CE MODULE.
 *
 * Ni la sous-taille ni la conversion de format n'agissent sur un fichier déjà présent dans la
 * bibliothèque : WordPress découpe et convertit une image UNE SEULE FOIS, au téléversement. Ce
 * module n'a donc d'effet que sur les photos envoyées APRÈS son arrivée, et rien ici ne régénère le
 * stock existant. C'est pourquoi il doit précéder la reprise des photos de l'ancien site : livré
 * après, il imposerait de régénérer les sous-tailles de tous les fichiers déjà importés.
 *
 * PAS DE GARDE « is_admin() », CONTRAIREMENT AUX AUTRES MODULES DU GROUPE « admin ».
 *
 * Une sous-taille n'existe que si elle est déclarée AU MOMENT OÙ LES DONNÉES DE LA PIÈCE JOINTE SONT
 * PRODUITES, c'est-à-dire au téléversement : c'est là, et nulle part ailleurs, que le cœur lit la
 * liste des tailles enregistrées pour décider quels fichiers écrire sur le disque. La conversion de
 * format s'applique au même instant. Or cet instant n'est pas toujours dans wp-admin : sous WP-CLI
 * comme sur la route REST des photos, « is_admin() » vaut faux — et la reprise des photos de l'ancien
 * site est précisément un import WP-CLI. Derrière une garde « is_admin() », « add_image_size() » ne
 * serait pas appelée sur ces requêtes et le fichier de 400 px NE SERAIT JAMAIS ÉCRIT : non pas absent
 * d'un srcset, absent du disque, et irrécupérable sans régénérer toutes les sous-tailles. Ne pas
 * « ranger » ce module derrière la garde habituelle du groupe.
 */

add_action( 'init', __NAMESPACE__ . '\\enregistrer_sous_tailles', 10 );
add_filter( 'image_editor_output_format', __NAMESPACE__ . '\\format_de_sortie', 10, 1 );

/**
 * Déclare la sous-taille manquante entre « medium » (300 px) et « medium_large » (768 px).
 *
 * Une vignette de galerie mesure 144 à 222 px de large. À densité d'écran doublée, le navigateur a
 * besoin d'environ 300 à 440 px : faute de candidat dans cet intervalle, il tire « medium_large »,
 * soit de l'ordre de six fois les pixels utiles sur un téléphone. Avec cette taille, la densité 1
 * choisit 300 px et la densité 2 choisit 400 px, sans qu'aucun attribut « sizes » ne change.
 *
 * Le troisième argument est « false », et il est porteur : la sous-taille n'est PAS rognée, donc le
 * rapport de la photo d'origine est conservé et le cadrage reste entièrement décidé par le CSS. Une
 * taille rognée appliquerait un second recadrage centré, qui ignorerait le point d'intérêt de la
 * photo — c'est le défaut pour lequel « thumbnail » est écartée des galeries. Le rapport conservé est
 * aussi ce qui permet au cœur de retenir ce fichier comme candidat de srcset : il n'y range que les
 * tailles de même rapport que l'original.
 */
function enregistrer_sous_tailles(): void {
	add_image_size( 'mtb-vignette-galerie', 400, 400, false );
}

/**
 * Fait produire au format WebP les images qu'un téléversement JPEG engendre : image principale et
 * sous-tailles.
 *
 * Le support du serveur est vérifié ici, et non au moment d'accrocher le filtre : ce rappel ne
 * s'exécute qu'au traitement d'une image, donc le contrôle ne coûte rien sur une page ordinaire, et
 * il porte sur l'éditeur réellement retenu par le cœur à cet instant. Sur un hébergement dont ni GD
 * ni Imagick ne savent encoder du WebP, la correspondance n'est pas ajoutée et WordPress conserve le
 * format d'origine : un JPEG reste un JPEG, aucune image n'est perdue et rien n'échoue.
 *
 * Seul le JPEG est converti, et le PNG est laissé intact délibérément : le gain mesurable porte sur
 * les photographies, qui sont en JPEG, tandis qu'un PNG sert à ce qui contient du trait ou du texte —
 * un pedigree numérisé, un document portant un numéro LOF. Le WebP que WordPress produit par défaut
 * est avec perte : sur du texte fin, il introduit des artéfacts qui peuvent rendre un chiffre
 * douteux. L'exactitude d'un numéro recopié pèse plus lourd que quelques kilo-octets.
 *
 * Portée réelle de la conversion, relevée sur un téléversement d'essai plutôt que supposée : le cœur
 * convertit les sous-tailles ET l'image principale, celle que « la photo en grand » d'une galerie
 * ouvre. Le fichier envoyé par l'éleveuse n'est pas perdu pour autant — WordPress le conserve intact
 * à côté et le désigne par « original_image » dans les données de la pièce jointe. Rien n'est donc
 * détruit, mais il faut savoir qu'un lien « voir la photo en grand » servira le WebP, pas le JPEG.
 *
 * Le paramètre n'est pas typé : un filtre tiers peut avoir rendu autre chose qu'un tableau, et
 * strict_types transformerait cela en erreur fatale au milieu d'un téléversement.
 *
 * @param mixed $formats Correspondances « type d'origine » => « type produit ».
 *
 * @return mixed Correspondances, celle du JPEG vers le WebP ajoutée si le serveur sait la tenir.
 */
function format_de_sortie( $formats ) {
	if ( ! is_array( $formats ) ) {
		return $formats;
	}

	if ( ! wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) ) ) {
		return $formats;
	}

	$formats['image/jpeg'] = 'image/webp';

	return $formats;
}
