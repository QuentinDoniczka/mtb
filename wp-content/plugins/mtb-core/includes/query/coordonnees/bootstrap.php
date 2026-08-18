<?php
/**
 * Fonctions de lecture des coordonnées de l'élevage, exposées au thème et aux composants.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

/*
 * Pas d'espace de noms ici, volontairement : les fonctions de lecture publiques sont la seule
 * exception au nommage MTB\Core\<Groupe>\<Module>. Elles vivent dans l'espace global, préfixées
 * « mtb_ », pour qu'un thème conforme n'ait jamais à écrire « MTB\ ».
 *
 * Aucun hook n'est posé, et aucune option n'est lue à l'inclusion : c'est un module du groupe
 * « query », de simples déclarations de fonctions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/option.php';

if ( ! function_exists( 'mtb_get_telephone_elevage' ) ) {
	/**
	 * Numéro de téléphone de l'élevage, tel qu'il est enregistré dans l'écran « Coordonnées ».
	 *
	 * Zéro paramètre requis, et c'est gelé : les deux composants qui l'appellent le font derrière
	 * une garde « function_exists() » posée avant que cette fonction n'existe. Un paramètre
	 * obligatoire lèverait un ArgumentCountError FATAL sur toute page portant un encart d'appel.
	 *
	 * La valeur rendue est celle qui est STOCKÉE, jamais une mise en forme : le groupage par paires
	 * appartient à l'affichage d'« encart-appel », et « coordonnees-plan » a délibérément choisi de
	 * ne pas grouper.
	 *
	 * @return string|null Numéro tel qu'il est stocké, ou null quand le réglage est vide.
	 */
	function mtb_get_telephone_elevage(): ?string {
		$telephone = \MTB\Core\Query\Coordonnees\lire()['telephone'];

		// Vidé délibérément par l'éleveuse : le numéro disparaît du site, ce n'est pas une panne.
		if ( '' === trim( $telephone ) ) {
			return null;
		}

		return $telephone;
	}
}

if ( ! function_exists( 'mtb_get_page_contact' ) ) {
	/**
	 * Identifiant de la Page désignée comme page de contact.
	 *
	 * Rend un identifiant, jamais une adresse ni un titre : l'appelant a besoin du contenu pour en
	 * tirer un libellé de bouton, et une URL ne le lui donnerait pas.
	 *
	 * NE VALIDE PAS que la page existe encore, est publiée et n'est pas protégée par mot de passe.
	 * « encart-appel/rendu.php » le fait déjà, à quatre conditions, au moment du rendu — et la
	 * validité d'une page dépend de ce moment, pas du réglage. Un réglage qui « corrigerait »
	 * l'identifiant remplacerait en silence le choix de l'éleveuse.
	 *
	 * @return int|null Identifiant enregistré, ou null quand aucune page n'est choisie.
	 */
	function mtb_get_page_contact(): ?int {
		$identifiant = \MTB\Core\Query\Coordonnees\lire()['page_contact'];

		if ( $identifiant <= 0 ) {
			return null;
		}

		return $identifiant;
	}
}

if ( ! function_exists( 'mtb_get_coordonnees_elevage' ) ) {
	/**
	 * Les trois coordonnées affichables de l'élevage, sous la forme enveloppe de la décision 18.
	 *
	 * Les trois clés sont TOUJOURS présentes ; « valeur » et « affichage » sont TOUJOURS des
	 * chaînes, éventuellement vides. Un appelant n'a donc jamais à tester l'existence d'une clé.
	 *
	 * Deux règles fermes :
	 *
	 * 1. « affichage » est identique à « valeur » sur les trois champs. Il ne porte JAMAIS le
	 *    groupage par paires d'« encart-appel » : la décision 38 autorise un composant à fixer la
	 *    typographie d'une donnée recopiée, elle interdit de la remonter dans la source. L'imposer
	 *    ici l'imposerait aussi à « coordonnees-plan », qui a choisi de ne pas grouper.
	 * 2. Quand la valeur est vide, « affichage » vaut la chaîne vide, JAMAIS « Non renseigné ».
	 *    « Non renseigné » est réservé à un champ de FICHE non rempli ; une coordonnée absente fait
	 *    disparaître sa ligne. L'écrire à côté de « Téléphone » dans un pied de page affirmerait un
	 *    non-fait sur l'éleveuse (D11).
	 *
	 * « page_contact » n'y figure pas : un identifiant n'est pas une valeur affichée.
	 *
	 * @return array<string,array{libelle: string, valeur: string, affichage: string}>
	 */
	function mtb_get_coordonnees_elevage(): array {
		$valeurs = \MTB\Core\Query\Coordonnees\lire();

		$libelles = array(
			'adresse'   => 'Adresse',
			'telephone' => 'Téléphone',
			'courriel'  => 'Courriel',
		);

		$enveloppe = array();

		foreach ( $libelles as $cle => $libelle ) {
			$enveloppe[ $cle ] = array(
				'libelle'   => $libelle,
				'valeur'    => $valeurs[ $cle ],
				'affichage' => $valeurs[ $cle ],
			);
		}

		return $enveloppe;
	}
}
