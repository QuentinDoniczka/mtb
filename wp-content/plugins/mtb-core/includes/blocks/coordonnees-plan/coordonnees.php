<?php
/**
 * Coordonnées de l'élevage : la lecture de la source centrale, et les deux dérivations d'URI.
 *
 * PLUS DE REPLI LOCAL, ET PLUS DE DOUBLON. L'adresse, le numéro et le courriel ne sont plus recopiés
 * dans ce module : ils viennent de l'écran « Coordonnées de l'élevage », par
 * « mtb_get_coordonnees_elevage() » et « mtb_get_telephone_elevage() ». Il ne subsiste dans tout le
 * dépôt qu'un seul littéral de chaque valeur, celui des valeurs de départ de l'option.
 *
 * L'espace de noms reste, et pour la raison qui l'a fait naître : les fonctions ci-dessous sont
 * INTERNES AU MODULE. Deux fonctions homonymes déclarées en espace de noms global par deux modules
 * que l'auto-découverte charge dans l'ordre alphabétique des dossiers s'ombrent SANS LEVER
 * D'ERREUR, sur un site qui répond 200 : la panne serait silencieuse, et imputée au mauvais module.
 *
 * Les deux dérivations, elles, restent globales sous « function_exists() » : elles ne possèdent
 * aucune donnée d'élevage, elles transforment en URI une valeur qu'on leur passe.
 *
 * Ce fichier ne dépend de rien du reste du module — ni de « rendu.php », ni de « interface.php » —,
 * ne pose aucun hook et ne fait aucune requête. Il LIT DÉSORMAIS UNE OPTION, par les fonctions de
 * lecture publiques : l'accès à la base est disponible dès « wp-settings.php », donc l'appel reste
 * sûr avant « init », mais la phrase « ne lit aucune option » qui figurait ici est devenue fausse.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Blocks\CoordonneesPlan {

	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	/**
	 * Rend les coordonnées centrales de l'élevage, pour les valeurs par défaut du composant.
	 *
	 * INTERNE AU MODULE. Nom sans « get », comme mtb_resultat_disciplines() (décision 16) : la
	 * lecture de contenu appartient à « mtb_get_coordonnees_elevage() », et cette fonction-ci n'en
	 * est que l'adaptation à la forme plate qu'attendent les attributs du bloc.
	 *
	 * Les trois clés sont TOUJOURS présentes et TOUJOURS des chaînes. Elles peuvent désormais être
	 * VIDES : l'éleveuse a le droit de vider un champ de l'écran de réglages, et vider un champ
	 * retire la ligne correspondante. Une chaîne vide n'est donc pas une panne.
	 *
	 * Corollaire : cette fonction NE REFLÈTE PAS ce qui a été saisi dans un bloc donné. Ce qui est
	 * tapé sur une page vit dans les attributs du bloc de cette page ; ici vivent les valeurs
	 * centrales, celles que reprend toute instance qui ne les a pas surchargées.
	 *
	 * @return array{adresse: string, telephone: string, courriel: string} Les trois coordonnées.
	 */
	function coordonnees_elevage(): array {
		$centrales = array();

		if ( function_exists( 'mtb_get_coordonnees_elevage' ) ) {
			$lu = mtb_get_coordonnees_elevage();

			if ( is_array( $lu ) ) {
				$centrales = $lu;
			}
		}

		return array(
			'adresse'   => valeur_centrale( $centrales, 'adresse' ),
			'telephone' => telephone_elevage(),
			'courriel'  => valeur_centrale( $centrales, 'courriel' ),
		);
	}

	/**
	 * Extrait une valeur de l'enveloppe centrale, sans jamais rendre autre chose qu'une chaîne.
	 *
	 * Traitement défensif volontairement identique à celui de « telephone_elevage() » : l'enveloppe
	 * de champ, ou la chaîne nue, et toute autre forme vaut « rien ». Ces défauts sont recalculés à
	 * « init » 20 sur CHAQUE requête, administration comprise : une forme inattendue y lèverait un
	 * TypeError qui emporterait wp-admin avec le site public.
	 *
	 * @param array<string,mixed> $centrales Enveloppe centrale, tableau vide si elle n'existe pas.
	 * @param string              $cle       Clé cherchée.
	 *
	 * @return string Valeur telle qu'elle est stockée, chaîne vide s'il n'y en a pas.
	 */
	function valeur_centrale( array $centrales, string $cle ): string {
		$lu = $centrales[ $cle ] ?? '';

		// Les deux formes admises par le contrat : l'enveloppe de champ, ou la chaîne nue.
		if ( is_array( $lu ) ) {
			$lu = $lu['valeur'] ?? '';
		}

		if ( ! is_string( $lu ) ) {
			return '';
		}

		return $lu;
	}

	/**
	 * Retient le numéro à afficher : la source centrale, et elle seule.
	 *
	 * Forme reprise telle quelle de « encart-appel/rendu.php » (fonction telephone_retenu()), garde
	 * et traitement défensif compris : les deux composants doivent se comporter à l'identique face à
	 * cette fonction, pas parler deux dialectes.
	 *
	 * Il n'y a plus de repli local. « mtb_get_telephone_elevage() » est la source d'autorité
	 * Y COMPRIS QUAND ELLE REND VIDE — sans quoi un numéro que l'éleveuse a délibérément effacé
	 * reviendrait sur le site. La garde « function_exists() » ne couvre plus qu'un seul cas, celui
	 * du module de lecture absent ou désactivé, et rend alors vide elle aussi.
	 *
	 * Les deux formes de retour admises par le contrat sont acceptées — chaîne nue, ou enveloppe de
	 * champ array( 'libelle', 'valeur', 'affichage' ) de la décision 18 —, et toute autre vaut
	 * « rien », pour qu'aucune page portant le composant ne puisse tomber.
	 *
	 * « mtb_get_page_contact() » n'est délibérément PAS consommée par ce composant — il n'a ni
	 * bouton ni lien vers la page Contact, l'appeler serait un couplage gratuit. Ce n'est pas un
	 * oubli.
	 *
	 * @return string Numéro tel qu'il est stocké, jamais mis en forme, vide s'il n'y en a pas.
	 */
	function telephone_elevage(): string {
		if ( function_exists( 'mtb_get_telephone_elevage' ) ) {
			$lu = mtb_get_telephone_elevage();

			// Les deux formes admises par le contrat : la chaîne nue, ou l'enveloppe de champ.
			if ( is_array( $lu ) ) {
				$lu = $lu['valeur'] ?? '';
			}

			if ( is_string( $lu ) ) {
				$lu = trim( $lu );

				if ( '' !== $lu ) {
					return $lu;
				}
			}
		}

		return '';
	}
}

namespace {

	if ( ! function_exists( 'mtb_coordonnees_lien_telephone' ) ) {
		/**
		 * Dérive l'URI d'appel d'un numéro de téléphone, sans jamais modifier le numéro lui-même.
		 *
		 * Seules les espaces sont retirées — U+0020, U+00A0 (insécable) et U+202F (fine insécable,
		 * que produisent le clavier et le traitement de texte français) : un « tel: » n'admet pas
		 * d'espace. RIEN N'EST AJOUTÉ : ni indicatif, ni zéro de tête, ni reformulation. Ce qui
		 * s'affiche reste le numéro tel qu'il a été saisi ; seule la cible du lien est compactée.
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
		 * zéro octet de JavaScript servi au visiteur est une contrainte gelée — aucune parade
		 * n'existe sans JavaScript.
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
}
