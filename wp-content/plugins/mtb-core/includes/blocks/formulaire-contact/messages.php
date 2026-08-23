<?php
/**
 * Composant « Formulaire de contact » — toutes les chaînes françaises du module.
 *
 * Fichier inclus UNE SEULE FOIS, par « bootstrap.php ».
 *
 * POURQUOI TOUT EST ICI, ET NULLE PART AILLEURS. Le contrat #22 §9 confie au serveur la totalité
 * des mots affichés : le thème n'en compose aucun, n'en accorde aucun, n'en préfixe aucun en CSS.
 * Une phrase écrite deux fois — ici et dans une pseudo-classe « ::before » — diverge au premier
 * ajustement, et personne ne sait plus laquelle fait foi. Un texte affiché est du contenu ; il a
 * une seule adresse.
 *
 * Le préfixe « Erreur : » est ÉCRIT DANS LA CHAÎNE, jamais posé par le style : un « content: »
 * disparaît d'une copie de texte et de certains modes de restitution, et le contrat §12.9 exige un
 * second signal qui ne soit pas la couleur.
 *
 * Aucune fonction de traduction — __(), _e(), esc_html__() — dans mtb-core : le français est
 * littéral, il n'y a aucun catalogue de traduction, et le chargeur interdit un appel i18n avant
 * « init ».
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Blocks\FormulaireContact;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Interface — les étiquettes et les libellés du formulaire lui-même. */

const ETIQUETTE_NOM      = 'Votre nom';
const ETIQUETTE_COURRIEL = 'Votre adresse de courriel';
const ETIQUETTE_MESSAGE  = 'Votre message';

/** Mention d'obligation écrite DANS l'étiquette, jamais un astérisque coloré (MASTER.md §8.5). */
const MENTION_OBLIGATOIRE = '(obligatoire)';

/** Note d'aide du seul champ dont la raison d'être n'est pas évidente. */
const AIDE_COURRIEL = "Pour que l'élevage puisse vous répondre.";

/** Libellé du bouton d'envoi — figé par MASTER.md §10.3, il ne se règle pas. */
const LIBELLE_ENVOI = 'Envoyer le message';

/**
 * Étiquette du piège à robots.
 *
 * Elle existe pour le cas où la feuille de style ne serait pas chargée : le champ devient alors
 * visible, et une visiteuse doit lire ce qu'il attend d'elle plutôt qu'un champ anonyme.
 */
const ETIQUETTE_PIEGE = 'Ne remplissez pas ce champ.';

/** Nom accessible du repère « form ». Reprend le titre du composant. */
const NOM_FORMULAIRE = 'Formulaire de contact';

/* Résumé d'erreurs, en tête du formulaire. */

const TITRE_RESUME = "Votre message n'a pas été envoyé.";

/* Erreurs de champ. */

const ERREUR_NOM_VIDE          = 'Erreur : indiquez votre nom.';
const ERREUR_NOM_TROP_LONG     = 'Erreur : votre nom dépasse 200 caractères.';
const ERREUR_COURRIEL_VIDE     = 'Erreur : indiquez votre adresse de courriel.';
const ERREUR_COURRIEL_INVALIDE = "Erreur : cette adresse de courriel n'est pas valide. Vérifiez qu'elle est de la forme nom@exemple.fr.";
const ERREUR_COURRIEL_LONG     = 'Erreur : cette adresse de courriel dépasse 254 caractères.';
const ERREUR_MESSAGE_VIDE      = 'Erreur : écrivez votre message.';

/* Erreurs globales — celles qui ne désignent aucun champ, donc sans lien dans le résumé. */

const ERREUR_JETON_INVALIDE = "Erreur : le formulaire n'a pas pu être vérifié. Cliquez de nouveau sur le bouton Envoyer le message : vos réponses sont conservées.";
const ERREUR_JETON_VIEUX    = "Erreur : cette page est restée ouverte plus d'une heure. Cliquez de nouveau sur le bouton Envoyer le message : vos réponses sont conservées.";
const ERREUR_JETON_RAPIDE   = "Erreur : le message est parti moins de trois secondes après l'ouverture de la page. Cliquez de nouveau sur le bouton Envoyer le message : vos réponses sont conservées.";

/* Envoi impossible — le titre commun et les quatre causes du contrat §6.6. */

const TITRE_INFORMATION = "Votre message n'a pas pu être envoyé.";

const CAUSE_PIEGE               = "Erreur : ce message n'a pas pu être envoyé.";
const CAUSE_ENVOI_ECHOUE        = "Erreur : le courriel n'a pas pu partir du site.";
const CAUSE_DESTINATION_ABSENTE = "Erreur : le site n'a plus d'adresse de destination enregistrée.";
const CAUSE_CORPS_PERDU         = "Erreur : ce message n'a pas pu être reçu — il dépasse la taille acceptée par le serveur. Votre texte n'est pas arrivé jusqu'au site.";

/* Confirmation. */

const CONFIRMATION_PREFIXE = 'Message envoyé.';
const CONFIRMATION_TEXTE   = "Votre message a été envoyé par courriel à l'élevage.";
const CONFIRMATION_REPRISE = 'Écrire un autre message';

/*
 * Mention d'information — LE TEXTE PAR DÉFAUT NE VIT PAS ICI, ET NE DOIT PAS Y REVENIR.
 *
 * Il vit dans la clé « attributes.mention.default » de « block.json », seul endroit qui puisse le
 * porter : le cœur lit ce fichier AVANT tout PHP du module, et remplit lui-même l'attribut manquant
 * par « WP_Block::prepare_attributes_for_render() ». C'est la même frontière JSON/PHP que le
 * « title » du bloc, déjà consignée en dette T-#22-h. Une constante ici serait un SECOND exemplaire
 * du même littéral français, que personne ne lirait et qui divergerait en silence à la première
 * retouche — la classe de défaut des décisions 43 et 46. Ne pas « corriger » cette absence.
 *
 * Copie de lecture, NON NORMATIVE — « block.json » fait foi en cas d'écart :
 *
 *   Votre message est envoyé par courriel à l'élevage. Votre nom et votre adresse de courriel
 *   l'accompagnent, pour que l'élevage puisse vous répondre. Le site n'en garde aucune copie : ni
 *   votre message, ni votre nom, ni votre adresse ne sont enregistrés ici.
 *
 * Chaque phrase est adossée à du code, et n'affirme rien de plus que lui :
 *
 * 1. « envoyé par courriel à l'élevage » — « traitement.php » lignes 405-410, « wp_mail( $adresse,
 *    … ) », où « $adresse » vient de « destination() » (« destination.php » lignes 101-107), tirée
 *    de « mtb_get_coordonnees_elevage()['courriel']['valeur'] ».
 * 2. « Votre nom et votre adresse […] l'accompagnent, pour que l'élevage puisse vous répondre » —
 *    « corps_courriel() » ci-dessous, lignes « Nom : » et « Courriel : » ; et « traitement.php »
 *    lignes 399-401, l'en-tête « Reply-To » de la visiteuse, qui existe pour cela seul. Le membre de
 *    phrase reprend MOT POUR MOT « AIDE_COURRIEL » ci-dessus, et cette reprise est voulue.
 * 3. « Le site n'en garde aucune copie » — décision 45 : le module ne compte aucun « update_option »,
 *    « add_option », « set_transient », « $wpdb », « setcookie », « session_start » ni
 *    « wp_insert_post ».
 *
 * CE QUE LE TEXTE TAIT, ET C'EST DÉLIBÉRÉ : aucune durée de conservation (il n'y en a pas), aucun
 * droit d'accès ou de suppression (ni le code ni personne ne garantit le vidage d'une boîte de
 * courriels), aucun responsable de traitement nommé (la raison sociale manque encore aux mentions
 * légales, et l'inventer serait un fait faux), aucune promesse de réponse — « PUISSE vous
 * répondre » énonce une finalité, pas un engagement (contrat §6.5).
 *
 * La mention reste un réglage libre : l'éleveuse la remplace ou la vide depuis son écran d'édition.
 */

/*
 * Éditeur — les mots que l'éleveuse lit dans son écran d'édition, et nulle part ailleurs.
 *
 * ILS VIVENT ICI ET NON DANS « editeur.js » (amendement §19.2 du contrat #22). Le §9 confie au
 * serveur la totalité des mots affichés, et douze d'entre eux étaient recopiés en dur dans le
 * script d'édition — dont huit MOT POUR MOT identiques aux constantes ci-dessus. Une retouche
 * d'un libellé ici aurait laissé l'aperçu d'éditeur afficher l'ancien, SANS ERREUR NI JOURNAL :
 * l'éleveuse aurait vu dans son écran un mot que le site n'emploie plus (décisions 43 et 46).
 * Ils partent vers le navigateur par le script en ligne du rappel « init 20 », en même temps que
 * l'état de la destination.
 */

/** Texte de substitution du seul champ éditable du composant. */
const EDITEUR_INVITE_MENTION = "Écrivez ici la mention d'information.";

/*
 * Les trois phrases d'état vide (§4.4), dans leur ordre de priorité.
 *
 * « DANS LE MENU COORDONNÉES », ET NON « Réglages → Coordonnées » : « includes/admin/coordonnees/
 * ecran.php » appelle « add_menu_page() » — un menu de PREMIER NIVEAU. La formulation gelée était
 * fausse et aurait envoyé l'éleveuse dans un menu qu'elle n'a pas (arbitrage A10, décision 43).
 */
const EDITEUR_ETAT_DESTINATION_ABSENTE  = "Ce bloc n'affiche rien tant qu'aucun courriel n'est enregistré dans le menu Coordonnées.";
const EDITEUR_ETAT_DESTINATION_INVALIDE = "Ce bloc n'affiche rien tant que l'adresse enregistrée dans le menu Coordonnées n'est pas une adresse de courriel valide.";
const EDITEUR_ETAT_MENTION_VIDE         = "Ce bloc n'affiche rien tant que la mention d'information n'est pas écrite.";

/**
 * Les douze libellés que l'écran d'édition affiche, prêts à traverser vers le navigateur.
 *
 * SEULS DES LIBELLÉS D'INTERFACE PASSENT ICI. L'interdit du §4.4 est précisé, pas levé : aucune
 * donnée de coordonnées ne transite vers l'éditeur — ni adresse, ni numéro. Ce tableau ne lit
 * aucune option et n'appelle aucune fonction de lecture ; il ne rend que des constantes de ce
 * fichier. L'état de la destination voyage à côté, en trois mots.
 *
 * Les clés sont celles que « editeur.js » demande. Elles reprennent le nom des constantes en
 * minuscules, pour qu'une lecture du script mène directement à la ligne qui fait foi.
 *
 * @return array<string, string> Libellés d'interface, clé de lecture => texte affiché.
 */
function libelles_editeur(): array {
	return array(
		'nom_formulaire'            => NOM_FORMULAIRE,
		'etiquette_nom'             => ETIQUETTE_NOM,
		'etiquette_courriel'        => ETIQUETTE_COURRIEL,
		'etiquette_message'         => ETIQUETTE_MESSAGE,
		'mention_obligatoire'       => MENTION_OBLIGATOIRE,
		'aide_courriel'             => AIDE_COURRIEL,
		'etiquette_piege'           => ETIQUETTE_PIEGE,
		'libelle_envoi'             => LIBELLE_ENVOI,
		'invite_mention'            => EDITEUR_INVITE_MENTION,
		'etat_destination_absente'  => EDITEUR_ETAT_DESTINATION_ABSENTE,
		'etat_destination_invalide' => EDITEUR_ETAT_DESTINATION_INVALIDE,
		'etat_mention_vide'         => EDITEUR_ETAT_MENTION_VIDE,
	);
}

/**
 * Texte de l'erreur « message trop long », qui porte l'adresse de recours.
 *
 * L'adresse arrive en paramètre et n'est jamais lue ici : aucun littéral d'adresse dans ce module
 * (contrat §3). Elle vient de l'écran « Coordonnées », par les fonctions de lecture publiques.
 *
 * @param string $adresse Adresse de destination, telle qu'elle est enregistrée.
 *
 * @return string Phrase complète, non échappée — l'échappement appartient au rendu.
 */
function erreur_message_trop_long( string $adresse ): string {
	return 'Erreur : votre message dépasse 20 000 caractères. Raccourcissez-le, ou écrivez directement à ' . $adresse . '.';
}

/**
 * Sujet du courriel envoyé à l'élevage.
 *
 * Le nom y figure parce que l'éleveuse trie son courrier au téléphone : vingt lignes de sujet
 * identique ne se distinguent pas. Il a déjà traversé « assainir_ligne() », donc il ne peut porter
 * ni CR ni LF — c'est cette garantie, et non un filtrage ici, qui interdit l'injection d'en-tête.
 *
 * @param string $nom Nom saisi, déjà assaini.
 *
 * @return string Sujet, la part variable plafonnée à 200 caractères.
 */
function sujet_courriel( string $nom ): string {
	// Ceinture : la validation refuse déjà au-delà de 200, ce plafond ne mord donc jamais.
	$nom = mb_substr( $nom, 0, 200 );

	return 'Message de ' . $nom . " — site de l'élevage";
}

/**
 * Corps du courriel, en texte brut.
 *
 * Aucun HTML, donc aucun « < » ne disparaît et rien n'est interprété : c'est ce qui rend sûr le
 * choix de ne PAS passer les valeurs par « sanitize_text_field() » (décision 20).
 *
 * L'adresse de la visiteuse est recopiée dans le corps EN PLUS de l'en-tête « Reply-To » : un
 * relais de messagerie peut retirer cet en-tête, et l'éleveuse doit pouvoir répondre quand même.
 *
 * @param array{nom: string, courriel: string, message: string} $valeurs    Valeurs assainies.
 * @param string                                                $origine    Adresse de la page d'où le message part.
 * @param string                                                $horodatage Date et heure déjà mises en forme.
 *
 * @return string Corps du courriel.
 */
function corps_courriel( array $valeurs, string $origine, string $horodatage ): string {
	$lignes = array(
		'Message reçu depuis le formulaire de contact du site.',
		'',
		'Nom : ' . $valeurs['nom'],
		'Courriel : ' . $valeurs['courriel'],
		'',
		'Message :',
		$valeurs['message'],
		'',
		'---',
		'Envoyé depuis ' . $origine . ' le ' . $horodatage . '.',
	);

	return implode( "\n", $lignes );
}

/**
 * Date et heure de l'envoi, dans le fuseau du site — dernière ligne du corps ci-dessus.
 *
 * Composée ICI et non dans « traitement.php » : le « à » qui relie la date à l'heure est un mot
 * français affiché, et le contrat §9 ne laisse aucune chaîne française vivre ailleurs que dans ce
 * fichier.
 *
 * DEUX APPELS CONCATÉNÉS, ET C'EST OBLIGATOIRE. Écrire « 'j F Y \à H\hi' » en un seul appel serait
 * FAUX : l'échappement de « date() » porte sur UN OCTET, et « à » en occupe deux en UTF-8. La barre
 * oblique inverse n'échapperait donc que le premier — le second octet resterait interprété comme
 * un caractère de format, et la date sortirait corrompue.
 *
 * @return string Par exemple « 23 août 2026 à 17h52 ».
 */
function horodatage_lisible(): string {
	$date  = wp_date( 'j F Y' );
	$heure = wp_date( 'H\hi' );

	// wp_date() rend false quand le fuseau du site est illisible : mieux vaut pas d'horodatage
	// qu'un « 1 janvier 1970 » qui affirmerait une date fausse.
	if ( ! is_string( $date ) || ! is_string( $heure ) ) {
		return '';
	}

	return $date . ' à ' . $heure;
}

/**
 * Ligne de recours par courriel de l'encart « envoi impossible ».
 *
 * Le fragment « en recopiant votre message ci-dessous » est RETIRÉ quand le corps de la requête a
 * été perdu : dans ce cas il n'y a rien ci-dessous, et l'écrire serait faux.
 *
 * L'adresse est rendue en substitution « %s » pour que le rendu puisse l'entourer d'un lien sans
 * découper la phrase à la main.
 *
 * @param bool $avec_recopie Vrai quand le message saisi est réaffiché sous l'encart.
 *
 * @return string Gabarit de phrase, un seul « %s » pour l'adresse.
 */
function gabarit_recours_courriel( bool $avec_recopie ): string {
	if ( $avec_recopie ) {
		return 'Vous pouvez écrire directement à %s, en recopiant votre message ci-dessous.';
	}

	return 'Vous pouvez écrire directement à %s.';
}

/**
 * Ligne de recours par téléphone de l'encart « envoi impossible ».
 *
 * @return string Gabarit de phrase, un seul « %s » pour le numéro.
 */
function gabarit_recours_telephone(): string {
	return "Vous pouvez aussi appeler l'élevage au %s.";
}
