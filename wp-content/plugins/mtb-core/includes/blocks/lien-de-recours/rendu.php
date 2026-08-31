<?php
/**
 * Composant « Lien de recours » — fonctions d'aide du rendu public.
 *
 * Ce fichier est inclus UNE SEULE FOIS, par « bootstrap.php ». Il est le seul du module à déclarer
 * une fonction : « render.php » est inclus par le cœur avec un « require » nu, donc une fois par
 * instance du bloc — et les gabarits en portent trois chacun.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Blocks\LienDeRecours;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * POURQUOI CE BLOC EXISTE, ALORS QUE LE CŒUR SEMBLE COUVRIR LE BESOIN.
 *
 * « 404.html » et « search.html » sont des gabarits de thème de blocs : des fichiers « .html » qui
 * n'exécutent aucun PHP. Ils ne peuvent donc ni appeler home_url(), ni demander l'adresse d'une
 * archive, ni vérifier qu'une page existe — leurs liens de recours étaient des « href » écrits en
 * dur, faux dès que le site n'est pas à la racine d'un domaine (dette T28 du contrat #16).
 *
 * Le cœur ne comble pas ce trou : « core/home-link » est son SEUL bloc qui calcule une adresse de
 * site au rendu, et il ne couvre que l'accueil ; « core/navigation-link » — le seul qui connaisse
 * « post-type-archive » — imprime l'attribut « url » ENREGISTRÉ, calculé dans l'éditeur et gelé dans
 * le balisage.
 *
 * Ce module ne lit aucune donnée d'élevage : home_url(), get_post_type_archive_link() et
 * get_page_by_path() sont des API de navigation du cœur (décision 41).
 */

/**
 * Résout une cible en adresse et en libellé, ou constate qu'il n'y a pas de destination.
 *
 * L'absence de destination n'est pas une erreur : c'est un cas normal, et le seul rendu correct est
 * alors le vide (décision 26 — un composant sans contenu ne s'affiche pas au visiteur). Un lien de
 * recours mort sur une page d'erreur serait une seconde impasse offerte à un visiteur déjà perdu.
 *
 * LIMITE ASSUMÉE SUR « meute » : le segment « la-meute » est une CONVENTION, pas une garantie. Rien
 * n'oblige l'éleveuse à nommer ainsi sa page ; si elle l'appelle « Nos chiens », son adresse change
 * et le lien ne s'affiche pas. Silencieux, mais honnête : un lien absent vaut mieux qu'un lien mort.
 * La vraie réponse serait un réglage d'administration désignant la page ; elle est hors de cette
 * issue et devra être demandée.
 *
 * @param string $cible Cible demandée : « accueil », « portees » ou « meute ».
 *
 * @return array{url: string, libelle: string}|null L'adresse et le libellé, ou null si la
 *                                                  destination n'existe pas.
 */
function destination( string $cible ): ?array {
	if ( 'accueil' === $cible ) {
		return array(
			'url'     => home_url( '/' ),
			'libelle' => 'Accueil',
		);
	}

	if ( 'portees' === $cible ) {
		// Rend « false » si le type de contenu n'existe pas ou n'a pas d'archive — par exemple si
		// l'extension est désactivée alors que le gabarit du thème, lui, reste en place.
		$adresse = get_post_type_archive_link( 'mtb_portee' );

		return is_string( $adresse ) && '' !== $adresse
			? array(
				'url'     => $adresse,
				'libelle' => 'Les portées',
			)
			: null;
	}

	if ( 'meute' === $cible ) {
		$page = get_page_by_path( 'la-meute' );

		/*
		 * get_page_by_path() ne filtre PAS sur l'état : elle rend aussi bien un brouillon qu'une
		 * page à la corbeille. Les deux vérifications sont donc à faire ici, et pas seulement
		 * l'existence :
		 *
		 * - hors « publish », le visiteur recevrait une page introuvable — l'impasse même que ce
		 *   lien est censé éviter ;
		 * - protégée par mot de passe, il recevrait un mur de mot de passe, qui n'est pas un
		 *   recours (et le contenu protégé ne doit fuiter par aucun composant).
		 */
		if ( ! $page instanceof \WP_Post || 'publish' !== $page->post_status || '' !== $page->post_password ) {
			return null;
		}

		$adresse = get_permalink( $page );

		return is_string( $adresse ) && '' !== $adresse
			? array(
				'url'     => $adresse,
				'libelle' => 'La meute',
			)
			: null;
	}

	/*
	 * Cible hors de l'énumération. Le cœur la ramène normalement au défaut « accueil » en validant
	 * les attributs contre le schéma de block.json, mais do_blocks() peut aussi tourner sur du
	 * balisage forgé à la main : rien plutôt qu'un lien deviné.
	 */
	return null;
}

/*
 * POURQUOI CETTE GARDE EXISTE, ET POURQUOI ELLE VIT DANS UN FILTRE.
 *
 * Le bloc rend un « li ». Posé au premier niveau d'une page plutôt que dans une liste, il produit un
 * « li » orphelin en plein flux — un balisage invalide (dette T51). Or « render.php » ne peut pas
 * constater ce cas lui-même : le cœur ne lui transmet ni son bloc parent ni son rang.
 * « $parent_block » n'est PAS une globale et n'est lisible d'aucune façon depuis le fichier de
 * rendu ; les filtres « render_block_* » sont le seul canal par lequel cette information circule.
 *
 * DEUX CHEMINS, MESURÉS SUR WORDPRESS 6.9, ET ILS NE SE COMPORTENT PAS PAREIL :
 *
 * - Premier niveau — « wp-includes/blocks.php:2283 » pose « $parent_block = null », « :2379 » filtre
 *   le contexte, « :2381 » le passe au CONSTRUCTEUR de WP_Block. Celui-ci le range dans
 *   « available_context » (« wp-includes/class-wp-block.php:139 »), puis
 *   « refresh_context_dependents() » ne recopie dans « ->context » que les noms déclarés en
 *   « uses_context » (« class-wp-block.php:163-169 »). C'est pourquoi « usesContext » est
 *   INDISPENSABLE dans block.json : sans la déclaration, la clé posée ici n'atteindrait jamais
 *   « $block->context ». Ce n'est pas une précaution, c'est le tamis mesuré du cœur.
 * - Imbriqué — « class-wp-block.php:552 » pose « $parent_block = $this » et « :567 » affecte le
 *   résultat du filtre DIRECTEMENT à « ->context », sans passer par ce tamis.
 *
 * TOUS LES NUMÉROS DE LIGNE DU CŒUR CITÉS ICI SONT DES REPÈRES DE LECTURE, PAS UN CONTRAT. Rien ne
 * fige la version relevée : « docker/wordpress/Dockerfile:5 » tire « wordpress:php8.1-apache », une
 * étiquette FLOTTANTE, et le dépôt ne contient pas le cœur. Un « build --pull » peut donc les
 * décaler sans que rien ne le signale. Ce qui est le livrable, c'est le MÉCANISME décrit — les deux
 * chemins et le tamis « uses_context » —, et il se revérifie en relisant les deux fichiers nommés.
 *
 * POLARITÉ DU REPLI, ET POURQUOI DANS CE SENS. On n'enveloppe que si la clé est présente et vaut
 * exactement « true » ; toute autre situation — clé absente, filtre jamais appelé, valeur d'un autre
 * type — laisse le rendu d'aujourd'hui, à l'octet près. Les trois écrans livrés (« 404.html »,
 * « search.html », état vide d'« index.html ») sont CORRECTS aujourd'hui : toute panne du canal doit
 * les laisser intacts. La polarité inverse — envelopper par défaut, se taire sur signal — ferait
 * d'une panne silencieuse un « ul » dans un « ul » sur ces trois gabarits, c'est-à-dire un balisage
 * invalide à l'endroit précis qu'on prétend protéger.
 *
 * LE TEST PORTE SUR LE PARENT IMMÉDIAT, PAS SUR UN ANCÊTRE. L'énoncé de l'issue — « si aucun ancêtre
 * ul/ol n'est détecté » — est plus lâche que HTML : le modèle de contenu impose que le PARENT d'un
 * « li » soit « ul », « ol » ou « menu ». Un bloc posé dans un « core/list-item » a bien un ancêtre
 * « ul » et produit pourtant un « li » dans un « li », invalide. Seul « $bloc_parent->name » est lu.
 *
 * ENSEMBLE ACCEPTÉ, LISTE CLOSE : « core/list », et lui seul. Il rend « ul » ou « ol » selon son
 * attribut « ordered », SOUS UN SEUL ET MÊME NOM DE BLOC — « attrs.ordered » n'est donc jamais lu. En
 * 6.9 c'est un bloc STATIQUE (« wp-includes/blocks/list/ » ne contient que block.json et des feuilles
 * de style : aucun render.php, aucun rappel de rendu). Sans effet sur l'arbitrage : un bloc statique
 * À BLOCS INTERNES passe lui aussi par la boucle interne de « WP_Block::render() », et son
 * « allowedBlocks » est une contrainte d'insérteur, pas de rendu.
 *
 * Les quatre refus, avec leur motif, pour qu'aucune passe future ne les ajoute « pour bien faire » :
 *
 * - « core/list-item » — c'est le cas même que cette garde répare ;
 * - « core/navigation » — rend un « ul », mais ce bloc n'y est jamais posé et le rendu de la
 *   navigation a ses propres attentes, invérifiables d'ici ;
 * - « core/post-template » et « core/comment-template » — rendent un « ul » mais INTERPOSENT un
 *   « li » généré par élément : les accepter réintroduirait le « li » dans « li ».
 *
 * La liste est close plutôt qu'heuristique parce qu'UN FAUX POSITIF N'EST PAS GRATUIT : envelopper à
 * tort dans un vrai « ul » produit un « ul » enfant direct de « ul », tout aussi invalide. Aucune des
 * deux directions n'est sûre ; seule une liste vérifiée l'est.
 *
 * LE « ul » EST NU — quatre caractères, cinq pour sa fermeture, aucun attribut, aucune classe, aucun
 * espace. Les attributs d'enveloppe restent sur le « li », qui demeure la racine du bloc, et la
 * sous-chaîne « li … /li » est rigoureusement identique dans les deux branches. Deux raisons : la
 * feuille du thème documente que ce composant « émet <li class="mtb-lien-de-recours"><a href> »
 * (base.css:943) — cette phrase doit rester vraie dans les DEUX branches ; et une classe sur le
 * « ul » ferait émettre à « mtb-core » un crochet de style, ce que la contrainte 2 du brief interdit.
 *
 * L'APERÇU DE L'ÉDITEUR DIVERGE, ET C'EST ASSUMÉ. « editeur.js » rend un « li » nu quel que soit son
 * parent et n'est PAS modifié : lui faire connaître son parent exigerait « wp-data », ce qui défait
 * la justification écrite des trois dépendances (« bootstrap.php:53-58 »). Aucun « parent » ni
 * « ancestor » n'est ajouté à block.json non plus : ces clés changeraient le comportement de
 * l'éditeur de site sur trois gabarits livrés sans qu'on puisse en prouver l'identité au pixel. C'est
 * le rendu SERVEUR qui est réparé, et lui seul.
 *
 * LES 44 px, DITS SANS LES SURVENDRE. Le crochet du thème est « .mtb-liens-de-secours > li > a »
 * (base.css:954-962, MASTER.md §9.5 et §12.10) ; le « ul » émis ici étant nu, l'orphelin enveloppé
 * n'obtient PAS cette cible tactile. Ce n'est ni une régression ni un défaut neuf : l'orphelin
 * d'aujourd'hui ne l'obtient pas davantage, pour exactement la même raison. La garde ne crée aucun
 * échec AA ; elle laisse intact un manque préexistant, sur un chemin qu'aucun écran d'édition
 * n'atteint.
 *
 * MÉMOIRE DE L'ARBITRAGE — trois options écartées, une ligne chacune :
 *
 * - rendre le vide hors d'une liste : ferait disparaître en silence un contenu bien formé qu'un
 *   développeur a délibérément posé. La doctrine de silence de « render.php:33-38 » couvre « rien de
 *   valide à montrer », PAS « contenu correct au mauvais endroit » ;
 * - la garde éditoriale « parent »/« ancestor » seule : ne satisfait pas l'issue, et fait courir à
 *   l'éditeur de site un risque invérifiable ;
 * - le bloc unique auto-contenu, qui rendrait la liste entière : exigerait de réécrire les trois
 *   gabarits, hors empreinte.
 *
 * T51 est payée par ce module seul. T55 — l'écart entre le libellé « Les portées » et MASTER.md
 * §10.3 — est l'objet de l'issue #43, hors de ce lot : cette garde est structurelle, pas lexicale, et
 * ne touche aucun libellé.
 */

/**
 * Pose sur le contexte du bloc le constat qu'il n'a pas de conteneur de liste pour parent.
 *
 * AUCUN PARAMÈTRE N'EST TYPÉ, LE RETOUR NON PLUS, et c'est délibéré. Sous « strict_types=1 »,
 * déclarer « array $contexte » transformerait le mauvais retour d'un rappel tiers en TypeError SUR
 * CHAQUE BLOC DE CHAQUE PAGE ; les gardes internes, elles, ne peuvent que se taire. C'est la forme
 * des deux filtres de même nature du thème (« functions.php:504 » et « :869 »).
 *
 * La fonction rend TOUJOURS ce qu'elle a reçu, éventuellement augmenté ou amputé d'une seule clé :
 * jamais null, jamais un tableau neuf.
 *
 * LE « unset » DE LA BRANCHE « LISTE » EST UNE DÉFENSE EN PROFONDEUR, ET RIEN DE PLUS. Il ne rend pas
 * structurellement impossible la résurrection de la clé : « refresh_context_dependents() » fusionne
 * « ->context » DANS « ->available_context » (« class-wp-block.php:161 ») avant de recopier les noms
 * déclarés (« :163-169 »), et « :573-575 » l'appelle justement quand un filtre a modifié le contexte
 * — une clé homonyme héritée d'un ancêtre serait donc réécrite APRÈS notre « unset ». Ce qui rend la
 * résurrection impossible EN PRATIQUE est ailleurs, et c'est cela qu'il faut retenir : aucun bloc du
 * site ne déclare « mtb/hors-liste » en « providesContext », ce filtre est le seul à l'écrire, il ne
 * l'écrit que sur « mtb/lien-de-recours », et ce bloc n'a aucun enfant — la clé ne peut pas
 * descendre.
 *
 * CE QUE LA GARDE NE FAIT PAS : elle ne compte pas les frères, ne regroupe rien, ne réordonne rien,
 * ne lit aucune donnée d'élevage et ne touche à aucun libellé. Trois orphelins côte à côte donnent
 * TROIS « ul » d'un seul élément — valide, et c'est la conséquence honnête de trois blocs posés hors
 * d'une liste.
 *
 * LIMITE D'ACCESSIBILITÉ SIGNALÉE, NON COMBLÉE : un « ul » nu n'a aucun nom accessible. Hors liste,
 * un lecteur d'écran annonce « liste, 1 élément » sans dire de quoi. Y remédier supposerait une
 * chaîne de domaine fabriquée par « mtb-core » hors de tout arbitrage de MASTER.md.
 *
 * @param mixed $contexte     Contexte du bloc, tel que le cœur le transmet.
 * @param mixed $bloc_analyse Bloc analysé, tel que le cœur le transmet.
 * @param mixed $bloc_parent  Bloc parent, ou null au premier niveau d'une page.
 *
 * @return mixed Le contexte reçu, augmenté ou amputé de « mtb/hors-liste ».
 */
function signaler_l_absence_de_conteneur_de_liste( $contexte, $bloc_analyse, $bloc_parent = null ) {
	/*
	 * Le nom du bloc en tout premier : ce filtre passe sur CHAQUE bloc de CHAQUE page, et son coût
	 * partout ailleurs doit se limiter à deux lectures de tableau.
	 */
	if ( ! is_array( $bloc_analyse ) || ! isset( $bloc_analyse['blockName'] ) ) {
		return $contexte;
	}

	if ( 'mtb/lien-de-recours' !== $bloc_analyse['blockName'] ) {
		return $contexte;
	}

	if ( ! is_array( $contexte ) ) {
		return $contexte;
	}

	if ( $bloc_parent instanceof \WP_Block && in_array( $bloc_parent->name, array( 'core/list' ), true ) ) {
		unset( $contexte['mtb/hors-liste'] );

		return $contexte;
	}

	$contexte['mtb/hors-liste'] = true;

	return $contexte;
}
