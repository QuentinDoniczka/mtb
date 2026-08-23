<?php
/**
 * Composant « Formulaire de contact » — déclaration du script d'édition, enregistrement du bloc,
 * et accroche du traitement de la soumission.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Blocks\FormulaireContact;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * POURQUOI CE MODULE VIT DANS « includes/blocks/ » ET NON DANS « includes/contact/ ».
 *
 * « includes/class-loader.php » porte une liste CLOSE de six groupes — content, fields, query,
 * blocks, admin, migration — et ne parcourt que « includes/<groupe>/ ». Un dossier hors de cette
 * liste n'est NI LU, NI JOURNALISÉ, NI SIGNALÉ : le site répond 200 et le composant n'existe pas.
 * C'est la classe de panne de la décision 27, en pire — silencieuse et permanente.
 * « class-loader.php » n'est pas modifiable : fichier partagé, plusieurs chaînes tournent en
 * parallèle sur cet arbre sans isolation.
 */

/*
 * AUCUNE GARDE « is_admin() » AUTOUR DE L'ENREGISTREMENT, et c'est le piège le plus coûteux de ce
 * dossier. Les modules de « includes/fields/ » commencent par « if ( ! is_admin() ) { return; } »,
 * parce qu'un écran de saisie n'a aucun sens côté public. Un module de « includes/blocks/ » qui
 * recopierait cette ligne ne s'enregistrerait PAS côté public : le composant fonctionnerait dans
 * l'éditeur et disparaîtrait du site, sans erreur, sur une page qui répond 200. Un bloc doit
 * s'enregistrer sur les trois façades — public, administration et REST.
 */

/*
 * LES SEPT FICHIERS D'AIDE SONT REQUIS ICI, UNE SEULE FOIS, ET ILS EXISTENT TOUS.
 *
 * Décision 27 : un « require » vers un fichier absent dans un bootstrap.php met TOUT LE SITE en
 * erreur fatale, wp-admin compris — le chargeur n'attrape pas un E_COMPILE_ERROR. Aucun de ces sept
 * chemins ne doit donc être écrit avant que le fichier ne soit sur le disque.
 *
 * « render.php » n'est PAS requis ici : le cœur l'inclut lui-même, avec un « require » NU, une fois
 * par instance du bloc. C'est pourquoi il ne déclare aucune fonction — une déclaration y ferait
 * tomber le site entier dès la deuxième instance.
 *
 * L'ordre suit les dépendances de lecture, non une exigence technique : PHP résout les appels de
 * fonction à l'exécution, et aucun de ces fichiers ne fait quoi que ce soit à l'inclusion.
 */
require_once __DIR__ . '/messages.php';
require_once __DIR__ . '/assainissement.php';
require_once __DIR__ . '/destination.php';
require_once __DIR__ . '/jeton.php';
require_once __DIR__ . '/etat.php';
require_once __DIR__ . '/traitement.php';
require_once __DIR__ . '/rendu.php';

add_action( 'init', __NAMESPACE__ . '\\enregistrer', 20 );

/*
 * AMENDEMENT DÉCLARÉ AU CONTRAT #1 §2, qui impose « init 20 » aux modules du groupe « blocks » :
 * l'enregistrement du bloc reste sur « init 20 » ; ce second hook est le seul du groupe, et il est
 * écrit, non fait en douce (décision 46).
 *
 * PRIORITÉ 1 : aucun autre écouteur de « template_redirect » ne doit pouvoir transformer le POST en
 * redirection avant que le message ne parte.
 */
add_action( 'template_redirect', __NAMESPACE__ . '\\traiter', 1 );

/**
 * Déclare le script d'édition et son état de destination, puis enregistre le bloc — dans cet ordre.
 *
 * L'ordre compte : « block.json » ne porte que la POIGNÉE du script, et le cœur exige qu'elle soit
 * déjà connue au moment de l'enregistrement du bloc. Écrire « "editorScript": "file:./editeur.js" »
 * ferait chercher au cœur un « editeur.asset.php » produit par une étape de construction : il n'y en
 * a aucune dans ce projet, et l'absence déclencherait un « _doing_it_wrong ».
 */
function enregistrer(): void {
	/*
	 * Trois dépendances, pas davantage. Ni « wp-components » — le composant n'a aucun panneau
	 * latéral, et un panneau à zéro réglage est un panneau qu'elle ouvre pour rien —, ni « wp-data »
	 * (aucune donnée à lire côté navigateur), ni « wp-server-side-render », dont le contrat #22 §4.3
	 * écarte l'usage en quatre points.
	 *
	 * Enregistrer n'est pas mettre en file : ce script n'est jamais servi au visiteur, le cœur ne le
	 * met en file que dans l'éditeur, par la seule clé « editorScript ».
	 */
	wp_register_script(
		'mtb-formulaire-contact-editeur',
		MTB_CORE_URL . 'includes/blocks/formulaire-contact/editeur.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor' ),
		MTB_CORE_VERSION,
		true
	);

	/*
	 * CE QUI TRAVERSE VERS L'ÉDITEUR — amendement §19.2 du contrat #22, et l'interdit du §4.4 y est
	 * PRÉCISÉ, PAS LEVÉ :
	 *
	 * - « destination » : L'ÉTAT SEUL, en un mot — « presente », « invalide » ou « absente ». Il dit
	 *   laquelle des trois phrases d'état vide afficher, rien de plus.
	 * - « libelles » : les douze libellés d'interface de l'écran d'édition, lus de « messages.php ».
	 *   Ils y étaient recopiés en dur, dont huit MOT POUR MOT identiques à des constantes du
	 *   serveur : une retouche d'un libellé aurait laissé l'aperçu afficher l'ancien, SANS ERREUR NI
	 *   JOURNAL (décisions 43 et 46). Le §9 veut toutes les chaînes dans « messages.php » ; elles y
	 *   sont désormais toutes, et l'éditeur les reçoit d'une seule source.
	 *
	 * L'ADRESSE DE L'ÉLEVAGE NE TRANSITE JAMAIS. Aucune donnée de coordonnées ne figure dans cette
	 * charge — ni adresse, ni numéro : « etat_destination() » rend trois mots possibles et
	 * « libelles_editeur() » ne rend que des constantes de « messages.php ». Et TOUJOURS AUCUN
	 * APPEL REST supplémentaire : la charge est écrite dans le document de l'écran d'édition.
	 *
	 * VALEUR PÉRIMABLE, ASSUMÉE : l'état est calculé au chargement de l'écran. Si l'éleveuse change
	 * le réglage dans un autre onglet, elle recharge l'écran. À dire dans la fiche d'aide.
	 *
	 * La garde « is_admin() » ne porte QUE sur cette donnée, jamais sur l'enregistrement du bloc :
	 * le script d'édition n'est mis en file que dans l'administration, et lire le réglage sur chaque
	 * page publique serait une lecture pour personne.
	 */
	if ( is_admin() ) {
		wp_add_inline_script(
			'mtb-formulaire-contact-editeur',
			'window.mtbFormulaireContact = ' . wp_json_encode(
				array(
					'destination' => etat_destination(),
					'libelles'    => libelles_editeur(),
				)
			) . ';',
			'before'
		);
	}

	/*
	 * Clés absentes de « block.json », volontairement :
	 *
	 * - « style » et « editorStyle » : l'extension n'émet aucune règle visuelle et ne met aucune
	 *   feuille en file. La feuille du composant est servie par le thème, qui déduit son nom du nom
	 *   du bloc — « mtb-formulaire-contact.css ».
	 * - « textdomain » : aucune fonction i18n dans mtb-core, le français est littéral. Une clé sans
	 *   catalogue suggérerait le contraire.
	 * - « viewScript » et « script » : ce composant n'a AUCUN JavaScript public, zéro octet. C'est
	 *   ce qui explique le couple ancre + « tabindex="-1" » à la place d'une mise au focus.
	 * - « example » : l'aperçu de l'insérteur monterait la représentation statique hors de tout
	 *   contenu, avec sa mention vide, donc avec un encadré d'état vide dans une vignette où rien
	 *   ne peut être corrigé.
	 */
	register_block_type( __DIR__ );
}
