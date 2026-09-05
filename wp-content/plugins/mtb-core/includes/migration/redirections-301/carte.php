<?php
/**
 * Table gelée des 52 adresses publiées par l'ancien site mtbrabant.com.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\Redirections301;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * CE FICHIER EST LA SOURCE DE VÉRITÉ DES REDIRECTIONS. « docs/migration/redirections.md » est un
 * relevé daté, reproductible par « wp mtb verifier-redirections » ; il n'est JAMAIS lu à
 * l'exécution (contrat #24 §3.4).
 *
 * LES 52, PAS LES 46. Les six adresses qui ne bougent pas y figurent avec leur verdict : l'absence
 * de 301 devient une donnée écrite, structurelle, et non un oubli que personne ne peut voir. La
 * commande de vérification y gagne un contrôle en une ligne : « 52 <loc> − 52 clés = 0 ».
 *
 * LA VALEUR D'UNE ENTRÉE N'EST JAMAIS UNE URL FIGÉE, C'EST UNE IDENTITÉ, résolue en permalien au
 * moment de la requête (voir « cible.php »). Une table de cibles figées deviendrait fausse EN
 * SILENCE au premier changement de schéma de permalien ou au premier renommage par l'éleveuse —
 * exactement la classe de défaut que l'issue #27 vient de réparer.
 *
 * PROVENANCE DE CHAQUE COLONNE, ET RIEN N'EST COMPOSÉ DE TÊTE (D11) :
 *   - les chemins anciens sont transcrits des 52 « <loc> » de « docs/migration/source/sitemap.xml »,
 *     dans leur ordre d'apparition, décodés (le sitemap source les écrit percent-encodés) ;
 *   - les 27 identifiants de portée viennent de la clé « identifiant.valeur » de
 *     « migration/portees-chiens/donnees/portees.json », appariée par « slug_source » ;
 *   - les 17 slugs de fiche viennent de la clé « reference » de « donnees/chiens.json » ;
 *   - les 2 slugs de page viennent de « migration/resultats-pages/donnees/pages/*.json ».
 *
 * PIÈGE VÉRIFIÉ, À NE PAS « CORRIGER » : la portée servie à « /bhpl/portée-n-2017/ » porte
 * l'identifiant « N_2 2017 », et non « N 2017 ». C'est la valeur transcrite du site source et c'est
 * celle qui est en base (post_title de « /portees/n-2017/ »). Le tiret bas n'est pas une faute de
 * frappe : le remplacer casserait la redirection sans un mot.
 *
 * LA CLÉ « formes ». Elle ne sert QU'À la réparation des ancres internes (« ancres.php »), JAMAIS
 * au service 301 : le périmètre des 301 reste exactement les 52 adresses qui ont existé. Elle
 * déclarerait une forme d'adresse ALTÉRÉE, c'est-à-dire n'ayant jamais existé sur l'ancien site,
 * qu'un import aurait écrite en base. Mesure du 2026-09-05, en base de développement : les douze
 * « href » stockés portent leurs accents intacts, en UTF-8 brut (octets C3 A9 pour « é ») —
 * l'hypothèse d'une altération par « esc_url() » est INFIRMÉE. Toutes les « formes » sont donc
 * vides, et le mécanisme reste en place pour qu'une altération future se déclare comme une donnée
 * plutôt que comme un correctif dispersé dans le code.
 */

/**
 * Table des 52 adresses de l'ancien site, indexée par chemin canonique décodé, barre finale comprise.
 *
 * Chaque entrée porte quatre clés :
 *   « verdict » — « 301 », « identique » ou « identique_apres_creation », et rien d'autre ;
 *   « cible »   — identité à résoudre, array( type, référence ) ; tableau vide hors verdict « 301 » ;
 *   « formes »  — formes d'adresse altérées connues, pour la seule réparation des ancres ;
 *   « note »    — motif écrit, non vide seulement quand le verdict en demande un.
 *
 * @return array<string, array<string, mixed>> Les 52 entrées, dans l'ordre du sitemap source.
 */
function carte(): array {
	static $carte = null;

	if ( null !== $carte ) {
		return $carte;
	}

	$carte = array(

		// ── Les six adresses qui ne bougent pas ─────────────────────────────────────────────

		'/'                          => array(
			'verdict' => 'identique',
			'cible'   => array(),
			'formes'  => array(),
			'note'    => '',
		),
		'/bhpl/'                     => array(
			'verdict' => 'identique',
			'cible'   => array(),
			'formes'  => array(),
			'note'    => '',
		),
		'/travail/'                  => array(
			'verdict' => 'identique',
			'cible'   => array(),
			'formes'  => array(),
			'note'    => '',
		),
		'/placement/'                => array(
			'verdict' => 'identique',
			'cible'   => array(),
			'formes'  => array(),
			'note'    => '',
		),
		'/contact/'                  => array(
			'verdict' => 'identique',
			'cible'   => array(),
			'formes'  => array(),
			'note'    => '',
		),
		'/la-meute/'                 => array(
			'verdict' => 'identique_apres_creation',
			'cible'   => array(),
			'formes'  => array(),
			'note'    => 'Aucune redirection, et c\'est un arbitrage. « docs/guide/page-creer-la-page-la-meute.md » apprend à l\'éleveuse à créer une page titrée « La meute », donc de slug « la-meute », donc servie ici : poser une 301 casserait la page que le guide lui demande de créer. Il n\'existe par ailleurs aucune cible de repli, « mtb_chien » étant déclaré « has_archive => false ». Répond 404 avant l\'acte d\'édition documenté, 200 après — par conception, pas par oubli. Écart à D5 assumé et daté, levé le jour où la page est publiée.',
		),

		// ── Les 27 portées ──────────────────────────────────────────────────────────────────

		'/bhpl/portée-a3-2025/'      => array(
			'verdict' => '301',
			'cible'   => array( 'portee', 'A3 2025' ),
			'formes'  => array(),
			'note'    => '',
		),
		'/bhpl/portée-a2-2025/'      => array(
			'verdict' => '301',
			'cible'   => array( 'portee', 'A2 2025' ),
			'formes'  => array(),
			'note'    => '',
		),
		'/bhpl/portée-a1-2025/'      => array(
			'verdict' => '301',
			'cible'   => array( 'portee', 'A1 2025' ),
			'formes'  => array(),
			'note'    => '',
		),
		'/bhpl/portée-v2-2024/'      => array(
			'verdict' => '301',
			'cible'   => array( 'portee', 'V2 2024' ),
			'formes'  => array(),
			'note'    => '',
		),
		'/bhpl/portée-v1-2024/'      => array(
			'verdict' => '301',
			'cible'   => array( 'portee', 'V1 2024' ),
			'formes'  => array(),
			'note'    => '',
		),
		'/bhpl/portée-u3-2023/'      => array(
			'verdict' => '301',
			'cible'   => array( 'portee', 'U3 2023' ),
			'formes'  => array(),
			'note'    => '',
		),
		'/bhpl/portée-u2-2023/'      => array(
			'verdict' => '301',
			'cible'   => array( 'portee', 'U2 2023' ),
			'formes'  => array(),
			'note'    => '',
		),
		'/bhpl/portée-u1-2023/'      => array(
			'verdict' => '301',
			'cible'   => array( 'portee', 'U1 2023' ),
			'formes'  => array(),
			'note'    => '',
		),
		'/bhpl/portée-t-2022/'       => array(
			'verdict' => '301',
			'cible'   => array( 'portee', 'T 2022' ),
			'formes'  => array(),
			'note'    => '',
		),
		'/bhpl/portée-s2-2021/'      => array(
			'verdict' => '301',
			'cible'   => array( 'portee', 'S2 2021' ),
			'formes'  => array(),
			'note'    => '',
		),
		'/bhpl/portée-s1-2021/'      => array(
			'verdict' => '301',
			'cible'   => array( 'portee', 'S1 2021' ),
			'formes'  => array(),
			'note'    => '',
		),
		'/bhpl/portée-r-2020/'       => array(
			'verdict' => '301',
			'cible'   => array( 'portee', 'R 2020' ),
			'formes'  => array(),
			'note'    => '',
		),
		'/bhpl/portée-p2-2019/'      => array(
			'verdict' => '301',
			'cible'   => array( 'portee', 'P2 2019' ),
			'formes'  => array(),
			'note'    => '',
		),
		'/bhpl/portée-p-2019/'       => array(
			'verdict' => '301',
			'cible'   => array( 'portee', 'P 2019' ),
			'formes'  => array(),
			'note'    => '',
		),
		'/bhpl/portée-o-2018/'       => array(
			'verdict' => '301',
			'cible'   => array( 'portee', 'O 2018' ),
			'formes'  => array(),
			'note'    => '',
		),
		'/bhpl/portée-n-2017/'       => array(
			'verdict' => '301',
			'cible'   => array( 'portee', 'N_2 2017' ),
			'formes'  => array(),
			'note'    => 'Identifiant transcrit tel quel : « N_2 2017 », tiret bas compris. C\'est la valeur du site source et celle qui est en base.',
		),
		'/bhpl/portée-m-2016/'       => array(
			'verdict' => '301',
			'cible'   => array( 'portee', 'M 2016' ),
			'formes'  => array(),
			'note'    => '',
		),
		'/bhpl/portée-j-2014/'       => array(
			'verdict' => '301',
			'cible'   => array( 'portee', 'J 2014' ),
			'formes'  => array(),
			'note'    => '',
		),
		'/bhpl/portée-h-2012/'       => array(
			'verdict' => '301',
			'cible'   => array( 'portee', 'H 2012' ),
			'formes'  => array(),
			'note'    => '',
		),
		'/bhpl/portée-g-2011/'       => array(
			'verdict' => '301',
			'cible'   => array( 'portee', 'G 2011' ),
			'formes'  => array(),
			'note'    => '',
		),
		'/bhpl/portée-f-2010/'       => array(
			'verdict' => '301',
			'cible'   => array( 'portee', 'F 2010' ),
			'formes'  => array(),
			'note'    => '',
		),
		'/bhpl/portée-e-2009/'       => array(
			'verdict' => '301',
			'cible'   => array( 'portee', 'E 2009' ),
			'formes'  => array(),
			'note'    => '',
		),
		'/bhpl/portée-d-2008/'       => array(
			'verdict' => '301',
			'cible'   => array( 'portee', 'D 2008' ),
			'formes'  => array(),
			'note'    => '',
		),
		'/bhpl/portée-c-2007/'       => array(
			'verdict' => '301',
			'cible'   => array( 'portee', 'C 2007' ),
			'formes'  => array(),
			'note'    => '',
		),
		'/bhpl/portée-s-2001/'       => array(
			'verdict' => '301',
			'cible'   => array( 'portee', 'S 2001' ),
			'formes'  => array(),
			'note'    => '',
		),
		'/bhpl/portée-m-1996/'       => array(
			'verdict' => '301',
			'cible'   => array( 'portee', 'M 1996' ),
			'formes'  => array(),
			'note'    => '',
		),
		'/bhpl/portée-l-1995/'       => array(
			'verdict' => '301',
			'cible'   => array( 'portee', 'L 1995' ),
			'formes'  => array(),
			'note'    => '',
		),

		// ── Les deux pages libres déplacées ─────────────────────────────────────────────────

		'/bhpl/littérature/'         => array(
			'verdict' => '301',
			'cible'   => array( 'page', 'litterature' ),
			'formes'  => array(),
			'note'    => '',
		),
		'/mentions-légales/'         => array(
			'verdict' => '301',
			'cible'   => array( 'page', 'mentions-legales' ),
			'formes'  => array(),
			'note'    => '',
		),

		// ── Les 17 fiches de chien ──────────────────────────────────────────────────────────

		'/la-meute/very-best/'       => array(
			'verdict' => '301',
			'cible'   => array( 'chien', 'very-best' ),
			'formes'  => array(),
			'note'    => '',
		),
		'/la-meute/you/'             => array(
			'verdict' => '301',
			'cible'   => array( 'chien', 'you' ),
			'formes'  => array(),
			'note'    => '',
		),
		'/la-meute/tesla/'           => array(
			'verdict' => '301',
			'cible'   => array( 'chien', 'tesla' ),
			'formes'  => array(),
			'note'    => '',
		),
		'/la-meute/roxane/'          => array(
			'verdict' => '301',
			'cible'   => array( 'chien', 'roxane' ),
			'formes'  => array(),
			'note'    => '',
		),
		'/la-meute/ray-ban/'         => array(
			'verdict' => '301',
			'cible'   => array( 'chien', 'ray-ban' ),
			'formes'  => array(),
			'note'    => '',
		),
		'/la-meute/rolex/'           => array(
			'verdict' => '301',
			'cible'   => array( 'chien', 'rolex' ),
			'formes'  => array(),
			'note'    => '',
		),
		'/la-meute/youry/'           => array(
			'verdict' => '301',
			'cible'   => array( 'chien', 'youry' ),
			'formes'  => array(),
			'note'    => '',
		),
		'/la-meute/pégaz/'           => array(
			'verdict' => '301',
			'cible'   => array( 'chien', 'pegaz' ),
			'formes'  => array(),
			'note'    => 'Seule adresse accentuée des 17 fiches. Le slug servi aujourd\'hui est « pegaz », sans accent : c\'est la « reference » transcrite dans donnees/chiens.json, et « sanitize_title_with_dashes() » aurait de toute façon produit la même chose.',
		),
		'/la-meute/jango/'           => array(
			'verdict' => '301',
			'cible'   => array( 'chien', 'jango' ),
			'formes'  => array(),
			'note'    => '',
		),
		'/la-meute/opium/'           => array(
			'verdict' => '301',
			'cible'   => array( 'chien', 'opium' ),
			'formes'  => array(),
			'note'    => '',
		),
		'/la-meute/grocky/'          => array(
			'verdict' => '301',
			'cible'   => array( 'chien', 'grocky' ),
			'formes'  => array(),
			'note'    => '',
		),
		'/la-meute/etch/'            => array(
			'verdict' => '301',
			'cible'   => array( 'chien', 'etch' ),
			'formes'  => array(),
			'note'    => '',
		),
		'/la-meute/happy/'           => array(
			'verdict' => '301',
			'cible'   => array( 'chien', 'happy' ),
			'formes'  => array(),
			'note'    => '',
		),
		'/la-meute/halan/'           => array(
			'verdict' => '301',
			'cible'   => array( 'chien', 'halan' ),
			'formes'  => array(),
			'note'    => '',
		),
		'/la-meute/maya/'            => array(
			'verdict' => '301',
			'cible'   => array( 'chien', 'maya' ),
			'formes'  => array(),
			'note'    => '',
		),
		'/la-meute/tara/'            => array(
			'verdict' => '301',
			'cible'   => array( 'chien', 'tara' ),
			'formes'  => array(),
			'note'    => '',
		),
		'/la-meute/gribouille/'      => array(
			'verdict' => '301',
			'cible'   => array( 'chien', 'gribouille' ),
			'formes'  => array(),
			'note'    => '',
		),
	);

	return $carte;
}

/**
 * Index inverse des formes altérées : forme normalisée => clé canonique de la carte.
 *
 * Lu par la seule réparation des ancres internes. Vide aujourd'hui, par mesure et non par oubli
 * (voir l'en-tête de ce fichier). La commande de vérification compte ces formes à part, pour
 * qu'aucune n'entre jamais dans le décompte des 52.
 *
 * @return array<string, string> Forme altérée => clé de la carte ; tableau vide si aucune.
 */
function formes_connues(): array {
	static $index = null;

	if ( null !== $index ) {
		return $index;
	}

	$index = array();

	foreach ( carte() as $clef => $entree ) {
		foreach ( $entree['formes'] as $forme ) {
			$index[ (string) $forme ] = (string) $clef;
		}
	}

	return $index;
}
