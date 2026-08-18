<?php
/**
 * Coordonnées de l'élevage : la table de référence et ses deux dérivations.
 *
 * Espace de noms GLOBAL, volontairement : un thème conforme n'écrit jamais « MTB\ », c'est ce qui
 * rend la frontière thème / extension vérifiable d'un simple grep. Aucun hook n'est posé ici, aucune
 * requête n'est faite, aucune option n'est lue : ces trois valeurs sont des constantes du code,
 * recopiées du brief §7, et les fonctions sont appelables à tout moment, y compris avant « init ».
 *
 * Ce fichier est écrit pour être DÉPLACÉ TEL QUEL dans « includes/query/ » (dette T19 du contrat
 * #11) : il ne dépend de rien du module qui l'héberge aujourd'hui. Le pied de page de l'epic
 * Gabarits et l'encart d'appel consomment ces valeurs sans rien avoir à voir avec le bloc.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'mtb_coordonnees_elevage' ) ) {
	/**
	 * Rend les coordonnées de référence de l'élevage, recopiées du brief §7.
	 *
	 * Nom sans « get », comme mtb_resultat_disciplines() : c'est une table de constantes, pas une
	 * lecture de contenu (décision 16). Le cas « donnée absente » n'existe donc pas — les trois clés
	 * sont toujours présentes et toujours des chaînes non vides.
	 *
	 * Corollaire que le thème doit connaître : cette fonction NE REFLÈTE PAS ce qui a été saisi dans
	 * un bloc donné. Ce qui est tapé sur une page vit dans les attributs du bloc de cette page ;
	 * ici vivent les valeurs de référence de l'élevage.
	 *
	 * Le numéro est écrit d'un seul tenant, sans regroupement en paires, sans « +33 » et sans zéro
	 * ajouté ni retiré : c'est la graphie du brief et celle du site source. Aucun appelant ne le
	 * reformate.
	 *
	 * @return array{adresse: string, telephone: string, courriel: string} Les trois coordonnées.
	 */
	function mtb_coordonnees_elevage(): array {
		return array(
			'adresse'   => '3060 Route de Salernes, 83570 Entrecasteaux',
			'telephone' => '0680505619',
			'courriel'  => 'mtbrabant@gmail.com',
		);
	}
}

if ( ! function_exists( 'mtb_coordonnees_lien_telephone' ) ) {
	/**
	 * Dérive l'URI d'appel d'un numéro de téléphone, sans jamais modifier le numéro lui-même.
	 *
	 * Seules les espaces sont retirées — U+0020, U+00A0 (insécable) et U+202F (fine insécable, que
	 * produisent le clavier et le traitement de texte français) : un « tel: » n'admet pas d'espace.
	 * RIEN N'EST AJOUTÉ : ni indicatif, ni zéro de tête, ni reformulation. Ce qui s'affiche reste le
	 * numéro tel qu'il a été saisi ; seule la cible du lien est compactée.
	 *
	 * L'URI est rendue NON ÉCHAPPÉE : l'échappement appartient au rendu, qui seul sait dans quel
	 * contexte il l'imprime.
	 *
	 * @param string $telephone Numéro tel qu'il a été saisi.
	 *
	 * @return string URI « tel:… », ou chaîne vide s'il ne reste rien une fois les espaces retirées.
	 */
	function mtb_coordonnees_lien_telephone( string $telephone ): string {
		$compact = str_replace( array( ' ', "\u{00A0}", "\u{202F}" ), '', $telephone );

		// Rien à appeler : le rendu affichera le numéro en texte nu, jamais un lien vide.
		if ( '' === $compact ) {
			return '';
		}

		return 'tel:' . $compact;
	}
}

if ( ! function_exists( 'mtb_coordonnees_lien_courriel' ) ) {
	/**
	 * Dérive l'URI de courriel, sans jamais modifier l'adresse elle-même.
	 *
	 * is_email() décide s'il y a un lien, jamais ce qui s'affiche : une adresse que le cœur juge
	 * invalide reste affichée telle quelle, en texte nu. C'est la règle d'exactitude du domaine —
	 * une valeur recopiée ne se corrige pas.
	 *
	 * « mailto: » en clair, sans obfuscation : l'adresse est déjà publique sur le site source, et
	 * zéro octet de JavaScript servi au visiteur est une contrainte gelée — aucune parade n'existe
	 * sans JavaScript.
	 *
	 * @param string $courriel Adresse telle qu'elle a été saisie.
	 *
	 * @return string URI « mailto:… », ou chaîne vide si l'adresse n'est pas une adresse valide.
	 */
	function mtb_coordonnees_lien_courriel( string $courriel ): string {
		if ( ! is_email( $courriel ) ) {
			return '';
		}

		return 'mailto:' . $courriel;
	}
}
