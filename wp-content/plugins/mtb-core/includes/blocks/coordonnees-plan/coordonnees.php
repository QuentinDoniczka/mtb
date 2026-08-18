<?php
/**
 * Coordonnées de l'élevage : le repli local du module, et les deux dérivations d'URI.
 *
 * REPLI LOCAL, PAS SOURCE DE VÉRITÉ. L'adresse, le numéro et le courriel écrits plus bas sont les
 * valeurs du brief §7 RECOPIÉES dans ce module, exactement comme « encart-appel/rendu.php » recopie
 * le numéro dans sa propre constante. Le doublon est assumé et tracé : l'issue « Coordonnées de
 * l'élevage — écran de réglages unique » livrera la source unique et fera basculer les deux
 * composants d'un coup.
 *
 * Conséquence, et c'est la raison de l'espace de noms : AUCUN AUTRE MODULE NE LIT CES VALEURS.
 * La table est interne à « MTB\Core\Blocks\CoordonneesPlan\ » et n'est plus appelable de l'extérieur.
 * Deux fonctions homonymes déclarées en espace de noms global par deux modules que l'auto-découverte
 * charge dans l'ordre alphabétique des dossiers s'ombrent SANS LEVER D'ERREUR, sur un site qui
 * répond 200 : la panne serait silencieuse, et imputée au mauvais module. Le pied de page (#18) et
 * l'encart d'appel (#10) ne consomment donc rien d'ici ; ils tiennent leur numéro de leur propre
 * repli, jusqu'à l'écran de réglages.
 *
 * Les deux dérivations, elles, restent globales sous « function_exists() » : elles ne possèdent
 * aucune donnée d'élevage, elles transforment en URI une valeur qu'on leur passe.
 *
 * Ce fichier ne dépend de rien du reste du module — ni de « rendu.php », ni de « interface.php » —,
 * ne pose aucun hook, ne fait aucune requête et ne lit aucune option : tout y est appelable à tout
 * moment, y compris avant « init » (dette T19 du contrat #11).
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Blocks\CoordonneesPlan {

	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	/**
	 * Numéro de l'élevage, recopié à la lettre du brief §7 — repli du module, rien de plus.
	 *
	 * Constante d'espace de noms et non « define() », comme dans le module sœur « encart-appel » :
	 * elle n'a pas à exister hors de ce module.
	 *
	 * Le numéro est écrit d'un seul tenant, sans regroupement en paires, sans « +33 » et sans zéro
	 * ajouté ni retiré : c'est la graphie du brief et celle du site source.
	 */
	const TELEPHONE_ELEVAGE = '0680505619';

	/**
	 * Rend les coordonnées de référence du composant, pour ses valeurs par défaut et ses gabarits.
	 *
	 * INTERNE AU MODULE. Nom sans « get », comme mtb_resultat_disciplines() : c'est un repli de
	 * constantes, pas une lecture de contenu (décision 16). Le cas « donnée absente » n'existe donc
	 * pas — les trois clés sont toujours présentes et toujours des chaînes non vides.
	 *
	 * Corollaire : cette fonction NE REFLÈTE PAS ce qui a été saisi dans un bloc donné. Ce qui est
	 * tapé sur une page vit dans les attributs du bloc de cette page ; ici vivent les valeurs de
	 * repli du module.
	 *
	 * @return array{adresse: string, telephone: string, courriel: string} Les trois coordonnées.
	 */
	function coordonnees_elevage(): array {
		return array(
			'adresse'   => '3060 Route de Salernes, 83570 Entrecasteaux',
			'telephone' => telephone_elevage(),
			'courriel'  => 'mtbrabant@gmail.com',
		);
	}

	/**
	 * Retient le numéro à afficher, dans l'ordre : source centrale, repli du module.
	 *
	 * Forme reprise telle quelle de « encart-appel/rendu.php » (fonction telephone_retenu()), garde
	 * et traitement défensif compris : les deux composants doivent se comporter à l'identique face à
	 * cette fonction, pas parler deux dialectes.
	 *
	 * « mtb_get_telephone_elevage() » n'est déclarée par personne aujourd'hui : la garde est donc
	 * fausse et l'on retombe sur la constante. C'est le comportement voulu, pas une panne — le rendu
	 * public est, à l'octet près, celui d'avant cette consommation.
	 *
	 * La FORME DE SON RETOUR N'EST PAS TRANCHÉE : chaîne nue, ou enveloppe de champ
	 * array( 'libelle', 'valeur', 'affichage' ) de la décision 18. C'est l'issue qui déclarera la
	 * fonction qui décidera ; les deux formes sont acceptées ici, et toute autre vaut « rien », pour
	 * qu'aucune page portant le composant ne puisse tomber le jour où elle apparaîtra.
	 *
	 * L'adresse et le courriel ne sont, eux, tirés d'aucune fonction centrale : aucune n'existe ni
	 * n'est gelée pour eux. Et « mtb_get_page_contact() » n'est délibérément PAS consommée par ce
	 * composant — il n'a ni bouton ni lien vers la page Contact, l'appeler serait un couplage gratuit.
	 * Ce n'est pas un oubli.
	 *
	 * @return string Numéro tel qu'il est stocké, jamais mis en forme.
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

		return TELEPHONE_ELEVAGE;
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
