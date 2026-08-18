<?php
/**
 * Coordonnées de l'élevage : l'option, ses valeurs de départ, sa lecture et son assainisseur.
 *
 * Source unique de l'adresse, du numéro et du courriel pour tout le dépôt. Les composants ne les
 * recopient plus : ils lisent les fonctions publiques déclarées par « bootstrap.php ».
 *
 * Ce fichier ne pose aucun hook et ne lit rien à l'inclusion : toute lecture d'option se fait dans
 * le corps d'une fonction, comme le chargeur l'exige (« includes/class-loader.php »).
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Query\Coordonnees;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Nom de l'option unique. Seule convention de nom existante : « mtb_core_empreinte ».
 *
 * Le module d'administration l'obtient d'ici et ne le réécrit jamais en dur : deux littéraux
 * divergents feraient écrire l'écran dans une option que personne ne lit, sans la moindre erreur.
 */
const NOM_OPTION = 'mtb_core_coordonnees';

/**
 * Valeurs de départ, recopiées à la lettre de « docs/BRIEF.md » §7.
 *
 * Le numéro est écrit d'un seul tenant, sans regroupement en paires, sans « +33 » et sans zéro
 * ajouté ni retiré : le groupage par paires de « encart-appel » est une typographie d'affichage
 * (décision 38) et ne remonte jamais dans le réglage.
 *
 * « page_contact » vaut 0 : aucune Page de contact n'existe encore, et en désigner une serait
 * inventer un fait d'élevage (D11).
 *
 * Elles ne servent qu'aux clés que personne n'a jamais écrites — voir « lire() ».
 *
 * @return array{adresse: string, telephone: string, courriel: string, page_contact: int}
 */
function valeurs_de_depart(): array {
	return array(
		'adresse'      => '3060 Route de Salernes, 83570 Entrecasteaux',
		'telephone'    => '0680505619',
		'courriel'     => 'mtbrabant@gmail.com',
		'page_contact' => 0,
	);
}

/**
 * Lit les quatre coordonnées enregistrées, complétées des seules valeurs de départ manquantes.
 *
 * PRÉSENCE, PAS VACUITÉ. Une clé PRÉSENTE dans l'option stockée gagne, MÊME VIDE ; une clé ABSENTE
 * retombe sur sa valeur de départ. D'où « array_key_exists() », et jamais « isset() » ni
 * « empty() » : c'est la seule façon de distinguer « l'éleveuse a délibérément vidé son numéro » de
 * « cette clé n'a jamais été écrite ». Même raisonnement que la décision 21 sur les compteurs de
 * mâles et de femelles d'une portée.
 *
 * Le second argument de « get_option() » ne suffirait pas : il n'est rendu que si la LIGNE d'option
 * est absente, et il ne comble aucune clé manquante d'un tableau existant. La fusion est donc faite
 * ici, clé par clé, avec retypage de chacune.
 *
 * Aucun état dégradé ne lève d'erreur, et la lecture ne réécrit jamais ce qu'elle lit : sans quoi
 * une option incomplète deviendrait complète au premier affichage venu, et le choix de vider un
 * champ serait perdu par le simple fait de regarder la page.
 *
 * @return array{adresse: string, telephone: string, courriel: string, page_contact: int}
 */
function lire(): array {
	$depart  = valeurs_de_depart();
	$stockee = get_option( NOM_OPTION );

	// Ligne absente, chaîne, entier, null, objet : rien d'exploitable, tout part du départ.
	if ( ! is_array( $stockee ) ) {
		return $depart;
	}

	$valeurs = $depart;

	foreach ( array( 'adresse', 'telephone', 'courriel' ) as $cle ) {
		if ( ! array_key_exists( $cle, $stockee ) ) {
			continue;
		}

		$brut = $stockee[ $cle ];

		// Un tableau ou un objet n'est pas une coordonnée : la valeur de départ tient.
		if ( is_array( $brut ) || is_object( $brut ) ) {
			continue;
		}

		$valeurs[ $cle ] = (string) $brut;
	}

	if ( array_key_exists( 'page_contact', $stockee ) ) {
		$brut = $stockee['page_contact'];

		if ( ! is_array( $brut ) && ! is_object( $brut ) ) {
			$valeurs['page_contact'] = absint( $brut );
		}
	}

	return $valeurs;
}

/**
 * Nettoie une valeur recopiée sans jamais en altérer le contenu.
 *
 * « sanitize_text_field() », « wp_strip_all_tags() », « wp_kses() » et « sanitize_email() » sont
 * volontairement écartées : toutes passent par « strip_tags() », qui supprime ce qui suit un « < »
 * jusqu'à un « > ». Une adresse ou un numéro commençant par « < » serait vidé SANS erreur ni
 * avertissement — D11 enfreinte par l'outillage (décision 20). On retire donc les seuls caractères
 * de contrôle, on contrôle l'encodage, on coupe les espaces de bord, et rien d'autre. C'est sûr
 * parce que l'échappement est systématique en sortie et que seul un compte disposant
 * d'« edit_pages » écrit ici.
 *
 * Sémantique reprise de « content/portee/champs.php » et de « content/resultat/assainissement.php » :
 * les caractères de contrôle sont SUPPRIMÉS, jamais remplacés par une espace, et l'encodage est
 * contrôlé.
 *
 * @param mixed $valeur     Valeur brute, telle qu'elle sort du formulaire.
 * @param bool  $multiligne Vrai pour conserver les retours à la ligne.
 *
 * @return string Valeur recopiée, chaîne vide si la valeur reçue n'était pas un scalaire.
 */
function nettoyer_recopie( $valeur, bool $multiligne ): string {
	if ( ! is_scalar( $valeur ) ) {
		return '';
	}

	// Rend la suite sûre : une entrée mal encodée ressort vide d'ici plutôt que tronquée au hasard.
	$texte = wp_check_invalid_utf8( (string) $valeur );

	if ( $multiligne ) {
		$texte = str_replace( array( "\r\n", "\r" ), "\n", $texte );
	} else {
		$texte = str_replace( array( "\r\n", "\r", "\n" ), ' ', $texte );
	}

	/*
	 * Comparaison octet par octet volontaire : en UTF-8, aucun octet de continuation ne descend
	 * sous 0x80, la classe ne peut donc pas mordre sur un caractère accentué.
	 */
	$nettoye = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $texte );

	if ( ! is_string( $nettoye ) ) {
		return '';
	}

	return trim( $nettoye );
}

/**
 * Assainit une valeur qui tient sur une seule ligne.
 *
 * @param mixed $valeur Valeur brute.
 *
 * @return string Valeur recopiée.
 */
function assainir_ligne( $valeur ): string {
	return nettoyer_recopie( $valeur, false );
}

/**
 * Assainit une valeur qui peut tenir sur plusieurs lignes.
 *
 * @param mixed $valeur Valeur brute.
 *
 * @return string Valeur recopiée, retours à la ligne conservés et normalisés en « \n ».
 */
function assainir_multiligne( $valeur ): string {
	return nettoyer_recopie( $valeur, true );
}

/**
 * Assainit les quatre coordonnées reçues d'un formulaire, prêtes à être enregistrées.
 *
 * Les quatre clés sont toujours produites : une clé absente du formulaire vaut vide, et le vide est
 * un choix légitime de l'éleveuse que la lecture retiendra tel quel — jamais un défaut à recombler.
 *
 * L'adresse admet les retours à la ligne ; le téléphone et le courriel tiennent sur une ligne.
 * Aucune des trois n'est reformatée ni validée : une coordonnée recopiée ne se corrige pas.
 *
 * L'identifiant de page n'est pas vérifié ici : la validité d'une page dépend du moment et du
 * contexte du rendu, et « encart-appel » la contrôle déjà à quatre conditions au moment d'émettre
 * son bouton.
 *
 * @param array<string,mixed> $brut Valeurs telles qu'elles sortent du formulaire, déjà déséchappées.
 *
 * @return array{adresse: string, telephone: string, courriel: string, page_contact: int}
 */
function assainir( array $brut ): array {
	$page = $brut['page_contact'] ?? 0;

	return array(
		'adresse'      => assainir_multiligne( $brut['adresse'] ?? '' ),
		'telephone'    => assainir_ligne( $brut['telephone'] ?? '' ),
		'courriel'     => assainir_ligne( $brut['courriel'] ?? '' ),
		'page_contact' => absint( is_numeric( $page ) ? $page : 0 ),
	);
}
