<?php
/**
 * Minifieur hors ligne des feuilles de style du thème MTB (issue #40, tâche T52).
 *
 * Retire les commentaires CSS, et rien d'autre. Écrit à côté de chaque source `<nom>.css` un
 * artefact `<nom>.min.css` précédé d'un marqueur d'empreinte que le thème relit à l'exécution
 * pour décider s'il peut le servir.
 *
 * Ce script n'est JAMAIS chargé par WordPress et n'est déployé sur aucun chemin servi par le
 * web : il est appelé par `make css` et `make css-check`, dans le conteneur, sur un montage du
 * dépôt.
 *
 * JUMEAU À TENIR EN ACCORD — `wp-content/themes/mtb/functions.php`, fonction
 * `mtb_feuille_a_servir()`. La forme canonique, la révision `mtb-min/1` et le calcul de
 * l'empreinte y sont écrits une seconde fois, et les deux moitiés doivent s'accorder octet pour
 * octet. Toute divergence dégrade vers « la source est servie », jamais vers « un artefact
 * accepté à tort » — mais elle est silencieuse, et c'est pourquoi les deux moitiés sont écrites
 * d'une seule main (`docs/contracts/issue-40.md` §4 et §11).
 *
 * POURQUOI `glob()` EST LICITE ICI, ET NULLE PART AILLEURS. La proscription de `glob()`,
 * `scandir()`, `opendir()` et `DirectoryIterator` (décision 4 de `docs/ETAT.md`, rappelée dans le
 * docbloc de `mtb_feuilles_de_blocs()`, dans `functions.php`) vise le chemin de requête du
 * thème sur un hébergement mutualisé, où ces fonctions sont désactivées. Ce script ne tourne
 * jamais sous WordPress, jamais sur l'hébergement de production, jamais dans une requête web.
 * Ce renvoi nomme la fonction et ne cite aucun numéro de ligne : un nom survit aux décalages
 * de `functions.php`, un numéro non — ne pas « réparer » l'ancre en y remplaçant le nom par un.
 *
 * Usage :
 *   php mtb-minifier-css.php --racine=<dossier assets/css> [--verifier]
 *
 * Sans `--verifier` : régénère les artefacts, une ligne par feuille, sortie 0 sauf refus.
 * Avec `--verifier` : n'écrit rien ; rend `à jour`, `PÉRIMÉ`, `ABSENT`, `ORPHELIN` ou
 * `INVALIDE` par paire, et sort 1 dès qu'une seule paire n'est pas `à jour`.
 *
 * @package MTB
 */

declare(strict_types=1);

/**
 * Révision du dépouilleur, entrée du hachage.
 *
 * Elle périme TOUS les artefacts d'un coup le jour où la transformation change : sans elle, une
 * correction de défaut ne serait jamais distribuée, puisque les sources, elles, n'auraient pas
 * bougé. Le jumeau de cette constante vit dans `functions.php`.
 */
const MTB_MIN_REVISION = "mtb-min/1\n";

/** Motif du marqueur, ancré à l'octet 0 de l'artefact. Jumeau dans `functions.php`. */
const MTB_MIN_MOTIF = '#^/\*!mtb-src:([1-9][0-9]{0,9}):([0-9a-f]{16})\*/#';

/** États du balayeur. */
const MTB_MIN_CODE        = 0;
const MTB_MIN_GUILLEMET   = 1;
const MTB_MIN_APOSTROPHE  = 2;
const MTB_MIN_URL         = 3;
const MTB_MIN_COMMENTAIRE = 4;

/**
 * Forme canonique : les fins de ligne, et rien d'autre.
 *
 * Aucune autre normalisation — ni espaces, ni casse, ni BOM. Le dépôt tourne en
 * `core.autocrlf=true` sans motif `.gitattributes` sur `wp-content`, si bien qu'une même source
 * pèse 45 914 octets dans Git et 46 927 sur un disque Windows. Une empreinte calculée sur les
 * octets bruts serait donc rejetée partout en production, silencieusement.
 *
 * @param string $octets Octets du fichier.
 * @return string
 */
function mtb_min_canonique( string $octets ): string {
	return str_replace( array( "\r\n", "\r" ), "\n", $octets );
}

/**
 * Empreinte de la source, 16 hexadécimaux.
 *
 * `sha256` et jamais `xxh3` : le générateur tourne en PHP 8.1 dans le conteneur, le vérificateur
 * tourne sur l'hébergement, dont la version n'est pas tranchée. Choisir l'algorithme selon sa
 * disponibilité ferait dépendre l'empreinte du PHP qui l'a écrite. Seize hexadécimaux ne sont
 * pas une frontière de sécurité : c'est un détecteur de modification accidentelle.
 *
 * @param string $canonique Forme canonique de la source.
 * @return string
 */
function mtb_min_empreinte( string $canonique ): string {
	return substr( hash( 'sha256', MTB_MIN_REVISION . $canonique ), 0, 16 );
}

/**
 * Marqueur écrit en tête d'artefact, sans son saut de ligne.
 *
 * @param string $canonique Forme canonique de la source.
 * @return string
 */
function mtb_min_marqueur( string $canonique ): string {
	return '/*!mtb-src:' . strlen( $canonique ) . ':' . mtb_min_empreinte( $canonique ) . '*/';
}

/**
 * Dépouille une feuille de ses commentaires.
 *
 * Chaque commentaire est remplacé par EXACTEMENT une espace, jamais par rien : c'est la règle qui
 * rend structurellement impossible la soudure de deux jetons, et qui sauve les douze sites de
 * calcul où une espace autour d'un signe est signifiante. Les seuls autres octets à bouger sont
 * les espaces de FIN de ligne, rognés, et les lignes ainsi devenues vides, supprimées. Rien
 * d'autre n'est touché : aucun rognage à gauche, aucun écrasement d'espaces à l'intérieur d'une
 * ligne, aucun point-virgule retiré, aucune couleur réécrite, aucun réordonnancement.
 *
 * @param string $canonique Forme canonique de la source.
 * @return array{corps:string,commentaires:int,refus:string}
 */
function mtb_min_depouiller( string $canonique ): array {
	$n            = strlen( $canonique );
	$sortie       = '';
	$touchees     = array();
	$ligne        = 0;
	$etat         = MTB_MIN_CODE;
	$i            = 0;
	$commentaires = 0;
	$ouverture    = 0;
	$echec        = '';

	while ( $i < $n ) {
		$c = $canonique[ $i ];

		if ( MTB_MIN_COMMENTAIRE === $etat ) {
			// Seul une fermeture ferme un commentaire : CSS n'a pas de commentaires imbriqués.
			if ( '*' === $c && $i + 1 < $n && '/' === $canonique[ $i + 1 ] ) {
				$sortie            .= ' ';
				$touchees[ $ligne ] = true;
				++$commentaires;
				$i   += 2;
				$etat = MTB_MIN_CODE;
				continue;
			}

			++$i;
			continue;
		}

		if ( MTB_MIN_CODE !== $etat ) {
			if ( '\\' === $c && $i + 1 < $n ) {
				$sortie .= $c . $canonique[ $i + 1 ];

				if ( "\n" === $canonique[ $i + 1 ] ) {
					++$ligne;
				}

				$i += 2;
				continue;
			}

			if ( "\n" === $c && MTB_MIN_URL !== $etat ) {
				$echec = "saut de ligne brut dans une chaîne ouverte à l'octet " . $ouverture;
				break;
			}

			$sortie .= $c;

			if ( "\n" === $c ) {
				++$ligne;
			}

			if ( ( MTB_MIN_GUILLEMET === $etat && '"' === $c )
				|| ( MTB_MIN_APOSTROPHE === $etat && "'" === $c )
				|| ( MTB_MIN_URL === $etat && ')' === $c ) ) {
				$etat = MTB_MIN_CODE;
			}

			++$i;
			continue;
		}

		if ( '/' === $c && $i + 1 < $n && '*' === $canonique[ $i + 1 ] ) {
			$ouverture = $i;
			$etat      = MTB_MIN_COMMENTAIRE;
			$i        += 2;
			continue;
		}

		if ( '"' === $c || "'" === $c ) {
			$ouverture = $i;
			$etat      = '"' === $c ? MTB_MIN_GUILLEMET : MTB_MIN_APOSTROPHE;
			$sortie   .= $c;
			++$i;
			continue;
		}

		/*
		 * Une url non citée : `url(` insensible à la casse, suivi d'espaces optionnels puis d'un
		 * caractère autre qu'un guillemet, close par sa parenthèse. Une ouverture de commentaire
		 * y vit sans être un commentaire.
		 */
		if ( '(' === $c && $i >= 3 && 'url' === strtolower( substr( $canonique, $i - 3, 3 ) ) ) {
			$j = $i + 1;

			while ( $j < $n && false !== strpos( " \t\n\r\f", $canonique[ $j ] ) ) {
				++$j;
			}

			if ( $j < $n && '"' !== $canonique[ $j ] && "'" !== $canonique[ $j ] ) {
				$ouverture = $i;
				$etat      = MTB_MIN_URL;
			}

			$sortie .= $c;
			++$i;
			continue;
		}

		$sortie .= $c;

		if ( "\n" === $c ) {
			++$ligne;
		}

		++$i;
	}

	if ( '' === $echec && MTB_MIN_COMMENTAIRE === $etat ) {
		$echec = "commentaire non terminé, ouvert à l'octet " . $ouverture;
	}

	if ( '' === $echec && MTB_MIN_CODE !== $etat ) {
		$echec = "chaîne non terminée, ouverte à l'octet " . $ouverture;
	}

	if ( '' !== $echec ) {
		return array(
			'corps'        => '',
			'commentaires' => 0,
			'refus'        => $echec,
		);
	}

	/*
	 * Rognage à droite, puis suppression des seules lignes DEVENUES vides : une ligne vide
	 * préexistante est conservée telle quelle, sans quoi le dépouilleur reformaterait la feuille
	 * au lieu d'en retirer les commentaires.
	 */
	$gardees = array();

	foreach ( explode( "\n", $sortie ) as $index => $texte ) {
		$rogne = rtrim( $texte, " \t" );

		if ( '' === $rogne && isset( $touchees[ $index ] ) ) {
			continue;
		}

		$gardees[] = $rogne;
	}

	$corps = implode( "\n", $gardees );

	if ( '' !== $corps && "\n" !== substr( $corps, -1 ) ) {
		$corps .= "\n";
	}

	return array(
		'corps'        => $corps,
		'commentaires' => $commentaires,
		'refus'        => '',
	);
}

/**
 * Forme normalisée d'une feuille : commentaires remplacés par une espace, suites de blancs
 * écrasées en UNE espace. C'est l'oracle de P2.
 *
 * Écraser une suite de blancs en une espace, ce n'est pas la supprimer : un calcul écrit
 * `1px + 2px` et un calcul écrit `1px+2px` ont deux normalisées différentes. C'est très
 * exactement l'écueil des espaces signifiantes que P2 doit voir.
 *
 * Balayeur volontairement écrit à part de `mtb_min_depouiller()` : il n'a pas le même objectif,
 * ne connaît ni les lignes ni le rognage, et sert d'oracle. Réserve d'honnêteté : les deux
 * partagent la même idée d'automate, donc un défaut commun leur serait invisible — c'est
 * pourquoi la table de cas de P6 existe.
 *
 * @param string $canonique Forme canonique d'une feuille.
 * @return string
 */
function mtb_min_normaliser( string $canonique ): string {
	$n      = strlen( $canonique );
	$sortie = '';
	$etat   = MTB_MIN_CODE;
	$i      = 0;

	while ( $i < $n ) {
		$c = $canonique[ $i ];

		if ( MTB_MIN_COMMENTAIRE === $etat ) {
			if ( '*' === $c && $i + 1 < $n && '/' === $canonique[ $i + 1 ] ) {
				$sortie .= ' ';
				$i      += 2;
				$etat    = MTB_MIN_CODE;
				continue;
			}

			++$i;
			continue;
		}

		if ( MTB_MIN_CODE !== $etat ) {
			if ( '\\' === $c && $i + 1 < $n ) {
				$sortie .= $c . $canonique[ $i + 1 ];
				$i      += 2;
				continue;
			}

			$sortie .= $c;

			if ( ( MTB_MIN_GUILLEMET === $etat && '"' === $c )
				|| ( MTB_MIN_APOSTROPHE === $etat && "'" === $c )
				|| ( MTB_MIN_URL === $etat && ')' === $c )
				|| ( MTB_MIN_URL !== $etat && "\n" === $c ) ) {
				$etat = MTB_MIN_CODE;
			}

			++$i;
			continue;
		}

		if ( '/' === $c && $i + 1 < $n && '*' === $canonique[ $i + 1 ] ) {
			$etat = MTB_MIN_COMMENTAIRE;
			$i   += 2;
			continue;
		}

		if ( '"' === $c || "'" === $c ) {
			$etat    = '"' === $c ? MTB_MIN_GUILLEMET : MTB_MIN_APOSTROPHE;
			$sortie .= $c;
			++$i;
			continue;
		}

		if ( '(' === $c && $i >= 3 && 'url' === strtolower( substr( $canonique, $i - 3, 3 ) ) ) {
			$j = $i + 1;

			while ( $j < $n && false !== strpos( " \t\n\r\f", $canonique[ $j ] ) ) {
				++$j;
			}

			if ( $j < $n && '"' !== $canonique[ $j ] && "'" !== $canonique[ $j ] ) {
				$etat = MTB_MIN_URL;
			}
		}

		$sortie .= $c;
		++$i;
	}

	$ecrase = preg_replace( '/[ \t\n\r\f]+/', ' ', $sortie );

	return trim( is_string( $ecrase ) ? $ecrase : $sortie );
}

/**
 * Vrai si le texte porte encore une ouverture de commentaire hors chaîne. C'est l'oracle de P3 :
 * sans lui, un dépouilleur qui ne ferait rien passerait P1 et P2.
 *
 * @param string $canonique Corps de l'artefact, en forme canonique.
 * @return bool
 */
function mtb_min_porte_un_commentaire( string $canonique ): bool {
	$sonde = mtb_min_depouiller( $canonique );

	if ( '' !== $sonde['refus'] ) {
		return true;
	}

	return $sonde['commentaires'] > 0;
}

/**
 * Pré-vol d'une source : ce qui doit faire refuser, et ce qui doit être signalé.
 *
 * Le constat d'aujourd'hui est vert sur les quinze feuilles, et cela ne dispense de rien : on ne
 * fait pas confiance à un constat daté. Le pré-vol tourne à chaque exécution, dans les deux
 * modes.
 *
 * @param string $octets Octets bruts de la source, avant toute normalisation.
 * @return array{refus:string,signalements:string[]}
 */
function mtb_min_prevol( string $octets ): array {
	$signalements = array();

	if ( 0 === strncmp( $octets, "\xEF\xBB\xBF", 3 ) ) {
		return array(
			'refus'        => 'BOM UTF-8 en tête de fichier',
			'signalements' => $signalements,
		);
	}

	$canonique = mtb_min_canonique( $octets );

	if ( 0 === strncmp( $canonique, '/*!mtb-src:', 11 ) ) {
		return array(
			'refus'        => 'la source porte déjà un marqueur d\'artefact',
			'signalements' => $signalements,
		);
	}

	$resultat = mtb_min_depouiller( $canonique );

	if ( '' !== $resultat['refus'] ) {
		return array(
			'refus'        => $resultat['refus'],
			'signalements' => $signalements,
		);
	}

	/*
	 * Ce qui ne fait pas refuser mais doit se voir : une chaîne portant une séquence de
	 * commentaire, une règle importée, un jeu de caractères déclaré, un contournement de
	 * navigateur. Les compter sur le fichier entier serait le piège de comptage du lot 13 —
	 * les commentaires français en parlent abondamment — donc on les compte sur la NORMALISÉE,
	 * d'où les commentaires ont disparu.
	 */
	$normalisee = mtb_min_normaliser( $canonique );

	foreach ( array( '/*', '*/' ) as $sequence ) {
		if ( false !== strpos( $normalisee, $sequence ) ) {
			$signalements[] = 'une chaîne ou une url porte une séquence de commentaire';
			break;
		}
	}

	foreach ( array( '@import', '@charset' ) as $regle ) {
		if ( false !== stripos( $normalisee, $regle ) ) {
			$signalements[] = 'règle ' . $regle . ' présente';
		}
	}

	if ( false !== strpos( $normalisee, '\\9' ) ) {
		$signalements[] = 'contournement de navigateur présent';
	}

	return array(
		'refus'        => '',
		'signalements' => $signalements,
	);
}

/**
 * Les sources à traiter, dans l'ordre : `assets/css/*.css` puis `assets/css/blocs/*.css`.
 *
 * Exclusions, chacune avec son motif : `editor.css` n'atteint jamais un visiteur et `add_editor_style()`
 * résout ses chemins lui-même, donc son artefact ne serait servi par personne ; tout `*.min.css`
 * est un artefact, sans quoi on produirait `base.min.min.css` ; tout fichier non `.css` — le
 * `.gitkeep` du dossier des blocs — n'est pas une feuille.
 *
 * @param string $racine Dossier `assets/css` du thème.
 * @return string[] Chemins absolus des sources.
 */
function mtb_min_sources( string $racine ): array {
	$sources = array();

	foreach ( array( $racine . '/*.css', $racine . '/blocs/*.css' ) as $motif ) {
		$trouves = glob( $motif );

		if ( ! is_array( $trouves ) ) {
			continue;
		}

		sort( $trouves );

		foreach ( $trouves as $chemin ) {
			$nom = basename( $chemin );

			if ( 'editor.css' === $nom ) {
				continue;
			}

			if ( '.min.css' === substr( $nom, -8 ) ) {
				continue;
			}

			$sources[] = $chemin;
		}
	}

	return $sources;
}

/**
 * Les artefacts présents sur le disque, sources ou non.
 *
 * @param string $racine Dossier `assets/css` du thème.
 * @return string[] Chemins absolus.
 */
function mtb_min_artefacts_presents( string $racine ): array {
	$artefacts = array();

	foreach ( array( $racine . '/*.min.css', $racine . '/blocs/*.min.css' ) as $motif ) {
		$trouves = glob( $motif );

		if ( ! is_array( $trouves ) ) {
			continue;
		}

		sort( $trouves );

		foreach ( $trouves as $chemin ) {
			$artefacts[] = $chemin;
		}
	}

	return $artefacts;
}

/**
 * Chemin de l'artefact d'une source — même dossier, obligatoirement.
 *
 * Ce n'est pas une préférence esthétique : `base.css` porte des `url("../fonts/…")` relatifs à
 * l'emplacement de la feuille. Un artefact rangé ailleurs renverrait vers un dossier inexistant,
 * soit deux polices en 404 — et le repli métrique de `base.css` rend cette chute invisible à
 * l'œil.
 *
 * @param string $source Chemin absolu d'une source `.css`.
 * @return string
 */
function mtb_min_artefact_de( string $source ): string {
	return substr( $source, 0, -4 ) . '.min.css';
}

/**
 * Les artefacts sans source de même racine, et l'artefact interdit de l'éditeur.
 *
 * Signalés, jamais supprimés automatiquement : cet outil n'efface aucun fichier.
 *
 * @param string $racine Dossier `assets/css` du thème.
 * @return string[] Messages, un par anomalie.
 */
function mtb_min_orphelins( string $racine ): array {
	$attendus = array();

	foreach ( mtb_min_sources( $racine ) as $source ) {
		$attendus[ mtb_min_artefact_de( $source ) ] = true;
	}

	$anomalies = array();

	foreach ( mtb_min_artefacts_presents( $racine ) as $artefact ) {
		if ( isset( $attendus[ $artefact ] ) ) {
			continue;
		}

		if ( 'editor.min.css' === basename( $artefact ) ) {
			$anomalies[] = basename( $artefact ) . ' : l\'éditeur reste sur ses sources, cet artefact ne serait servi par personne';
			continue;
		}

		$anomalies[] = basename( $artefact ) . ' : ORPHELIN, aucune source de même racine';
	}

	return $anomalies;
}

/**
 * Table de cas à sortie attendue, écrite à la main. Seul oracle réellement indépendant.
 *
 * @return array<int,array{nom:string,entree:string,attendu:?string}> `attendu` à `null` : refus attendu.
 */
function mtb_min_table_de_cas(): array {
	return array(
		array(
			'nom'     => 'l\'espace de remplacement, la règle qui sauve les calculs',
			'entree'  => "a{b:c/*x*/d}\n",
			'attendu' => "a{b:c d}\n",
		),
		array(
			'nom'     => 'deux jetons collés autour d\'un commentaire ne se soudent pas',
			'entree'  => "margin:0/*x*/auto\n",
			'attendu' => "margin:0 auto\n",
		),
		array(
			'nom'     => 'espaces autour du signe, dans un calcul, intactes',
			'entree'  => "a{width:calc(var(--x) + 1px)/*n*/}\n",
			'attendu' => "a{width:calc(var(--x) + 1px) }\n",
		),
		array(
			'nom'     => 'ouverture de commentaire dans une chaîne à guillemets',
			'entree'  => "a{content:\"/*\"}\n",
			'attendu' => "a{content:\"/*\"}\n",
		),
		array(
			'nom'     => 'fermeture de commentaire dans une chaîne à apostrophes',
			'entree'  => "a{content:'*/'}\n",
			'attendu' => "a{content:'*/'}\n",
		),
		array(
			'nom'     => 'url non citée contenant une ouverture de commentaire',
			'entree'  => "a{background:url(/*.png)}\n",
			'attendu' => "a{background:url(/*.png)}\n",
		),
		array(
			'nom'     => 'url non citée, casse et espaces',
			'entree'  => "a{background:URL(  /*.png)}\n",
			'attendu' => "a{background:URL(  /*.png)}\n",
		),
		array(
			'nom'     => 'url citée : la chaîne protège, pas l\'url',
			'entree'  => "a{background:url(\"/*x*/\")}\n",
			'attendu' => "a{background:url(\"/*x*/\")}\n",
		),
		array(
			'nom'     => 'guillemet échappé : la chaîne ne se ferme pas',
			'entree'  => 'a{content:"x\"/*y*/"}' . "\n",
			'attendu' => 'a{content:"x\"/*y*/"}' . "\n",
		),
		array(
			'nom'     => 'commentaire seul sur sa ligne : ligne supprimée, saut de ligne compris',
			'entree'  => "/* seul sur sa ligne */\n",
			'attendu' => '',
		),
		array(
			'nom'     => 'lignes vides préexistantes conservées',
			'entree'  => "a{}\n\n\nb{}\n",
			'attendu' => "a{}\n\n\nb{}\n",
		),
		array(
			'nom'     => 'ligne vide préexistante voisine d\'une ligne supprimée',
			'entree'  => "a{}\n\n/*x*/\n\nb{}\n",
			'attendu' => "a{}\n\n\nb{}\n",
		),
		array(
			'nom'     => 'commentaire en fin de ligne : rognage à droite, ligne conservée',
			'entree'  => "a{b:c}/*x*/\n",
			'attendu' => "a{b:c}\n",
		),
		array(
			'nom'     => 'commentaire sur plusieurs lignes : une seule espace, lignes refermées',
			'entree'  => "a{\n/* x\n   y */\nb:c}\n",
			'attendu' => "a{\nb:c}\n",
		),
		array(
			'nom'     => 'indentation à gauche jamais rognée',
			'entree'  => "a{\n\tb:c; /* x */\n}\n",
			'attendu' => "a{\n\tb:c;\n}\n",
		),
		array(
			'nom'     => 'commentaire non terminé',
			'entree'  => 'a{b:c}/*fin',
			'attendu' => null,
		),
		array(
			'nom'     => 'chaîne non terminée',
			'entree'  => 'a{content:"x',
			'attendu' => null,
		),
		array(
			'nom'     => 'saut de ligne brut dans une chaîne',
			'entree'  => "a{content:\"x\ny\"}\n",
			'attendu' => null,
		),
	);
}

/**
 * Joue la table de cas de P6.
 *
 * @return string[] Messages d'échec ; tableau vide si tout est conforme.
 */
function mtb_min_jouer_la_table_de_cas(): array {
	$echecs = array();

	foreach ( mtb_min_table_de_cas() as $cas ) {
		$resultat = mtb_min_depouiller( $cas['entree'] );

		if ( null === $cas['attendu'] ) {
			if ( '' === $resultat['refus'] ) {
				$echecs[] = 'P6 « ' . $cas['nom'] . " » : refus attendu, aucun refus rendu";
			}

			continue;
		}

		if ( '' !== $resultat['refus'] ) {
			$echecs[] = 'P6 « ' . $cas['nom'] . ' » : refus inattendu — ' . $resultat['refus'];
			continue;
		}

		if ( $cas['attendu'] !== $resultat['corps'] ) {
			$echecs[] = 'P6 « ' . $cas['nom'] . ' » : attendu ' . var_export( $cas['attendu'], true )
				. ', obtenu ' . var_export( $resultat['corps'], true );
		}
	}

	/*
	 * La forme canonique et l'empreinte, elles aussi, ont leur table : une source convertie
	 * d'une fin de ligne à l'autre doit rendre la MÊME empreinte, sans quoi l'artefact serait
	 * rejeté partout en production.
	 */
	$lf   = "a{\n\tb:c;\n}\n";
	$crlf = "a{\r\n\tb:c;\r\n}\r\n";
	$cr   = "a{\r\tb:c;\r}\r";

	if ( mtb_min_canonique( $crlf ) !== $lf || mtb_min_canonique( $cr ) !== $lf ) {
		$echecs[] = 'P6 « forme canonique » : une fin de ligne n\'est pas ramenée au saut de ligne seul';
	}

	if ( mtb_min_empreinte( mtb_min_canonique( $crlf ) ) !== mtb_min_empreinte( $lf ) ) {
		$echecs[] = 'P6 « empreinte » : deux fins de ligne rendent deux empreintes';
	}

	if ( 1 !== preg_match( MTB_MIN_MOTIF, mtb_min_marqueur( $lf ) ) ) {
		$echecs[] = 'P6 « marqueur » : le marqueur produit ne vérifie pas son propre motif';
	}

	$longueur = strlen( mtb_min_marqueur( $lf ) );

	if ( $longueur < 31 || $longueur > 40 ) {
		$echecs[] = 'P6 « marqueur » : longueur ' . $longueur . ' hors des bornes 31 à 40';
	}

	return $echecs;
}

/**
 * Corps d'un artefact : ses octets après le marqueur et le saut de ligne qui le suit.
 *
 * @param string $artefact Octets de l'artefact, en forme canonique.
 * @return string|null `null` si le marqueur est absent ou mal formé.
 */
function mtb_min_corps_de( string $artefact ): ?string {
	if ( 1 !== preg_match( MTB_MIN_MOTIF, $artefact, $trouve ) ) {
		return null;
	}

	$apres = strlen( $trouve[0] );

	if ( $apres >= strlen( $artefact ) ) {
		return '';
	}

	if ( "\n" !== $artefact[ $apres ] ) {
		return null;
	}

	return substr( $artefact, $apres + 1 );
}

/**
 * Lit une source et rend ses formes.
 *
 * @param string $source Chemin absolu.
 * @return array{octets:string,canonique:string,refus:string}
 */
function mtb_min_lire_la_source( string $source ): array {
	$octets = is_readable( $source ) ? file_get_contents( $source ) : false;

	if ( ! is_string( $octets ) ) {
		return array(
			'octets'    => '',
			'canonique' => '',
			'refus'     => 'lecture impossible',
		);
	}

	return array(
		'octets'    => $octets,
		'canonique' => mtb_min_canonique( $octets ),
		'refus'     => '',
	);
}

/**
 * Régénère les artefacts.
 *
 * Deux temps, et c'est délibéré : tout est lu, pré-volé et dépouillé en mémoire ; un seul refus
 * arrête l'outil AVANT la moindre écriture. Un refus ne laisse donc jamais le dossier à
 * moitié régénéré.
 *
 * @param string $racine Dossier `assets/css` du thème.
 * @return int Code de sortie.
 */
function mtb_min_generer( string $racine ): int {
	$sources = mtb_min_sources( $racine );

	if ( array() === $sources ) {
		fwrite( STDERR, "REFUS : aucune source trouvée sous {$racine}\n" );

		return 1;
	}

	$echecs_p6 = mtb_min_jouer_la_table_de_cas();

	if ( array() !== $echecs_p6 ) {
		foreach ( $echecs_p6 as $echec ) {
			fwrite( STDERR, "REFUS : {$echec}\n" );
		}

		return 1;
	}

	$anomalies = mtb_min_orphelins( $racine );

	if ( array() !== $anomalies ) {
		foreach ( $anomalies as $anomalie ) {
			fwrite( STDERR, "REFUS : {$anomalie}\n" );
		}

		return 1;
	}

	$prets = array();

	foreach ( $sources as $source ) {
		$nom = basename( $source );
		$lu  = mtb_min_lire_la_source( $source );

		if ( '' !== $lu['refus'] ) {
			fwrite( STDERR, "REFUS : {$nom} — {$lu['refus']}\n" );

			return 1;
		}

		$prevol = mtb_min_prevol( $lu['octets'] );

		if ( '' !== $prevol['refus'] ) {
			fwrite( STDERR, "REFUS : {$nom} — {$prevol['refus']}\n" );

			return 1;
		}

		foreach ( $prevol['signalements'] as $signalement ) {
			echo "signalement : {$nom} — {$signalement}\n";
		}

		$resultat = mtb_min_depouiller( $lu['canonique'] );

		if ( '' !== $resultat['refus'] ) {
			fwrite( STDERR, "REFUS : {$nom} — {$resultat['refus']}\n" );

			return 1;
		}

		$prets[] = array(
			'source'   => $source,
			'artefact' => mtb_min_artefact_de( $source ),
			'avant'    => strlen( $lu['canonique'] ),
			'contenu'  => mtb_min_marqueur( $lu['canonique'] ) . "\n" . $resultat['corps'],
		);
	}

	$total_avant = 0;
	$total_apres = 0;

	echo "Octets comptés sur la forme canonique — un saut de ligne par ligne, comme en production.\n";

	foreach ( $prets as $pret ) {
		if ( false === file_put_contents( $pret['artefact'], $pret['contenu'] ) ) {
			fwrite( STDERR, 'REFUS : écriture impossible — ' . basename( $pret['artefact'] ) . "\n" );

			return 1;
		}

		$apres        = strlen( $pret['contenu'] );
		$total_avant += $pret['avant'];
		$total_apres += $apres;

		printf(
			"%s → %s : %d o → %d o (%s %%)\n",
			basename( $pret['source'] ),
			basename( $pret['artefact'] ),
			$pret['avant'],
			$apres,
			mtb_min_variation( $pret['avant'], $apres )
		);
	}

	printf(
		"total : %d feuilles, %d o → %d o (%s %%)\n",
		count( $prets ),
		$total_avant,
		$total_apres,
		mtb_min_variation( $total_avant, $total_apres )
	);

	return 0;
}

/**
 * Variation de poids, signe compris.
 *
 * Le signe est calculé et non écrit en dur dans le format : une feuille dont l'artefact serait
 * plus lourd que sa source — ce que P4 refuse, mais que rien n'empêche d'afficher — rendrait
 * autrement deux signes moins collés.
 *
 * @param int $avant Octets de la source, forme canonique.
 * @param int $apres Octets de l'artefact.
 * @return string
 */
function mtb_min_variation( int $avant, int $apres ): string {
	if ( 0 === $avant ) {
		return '0,0';
	}

	$variation = ( $apres - $avant ) * 100 / $avant;
	$signe     = $variation <= 0 ? "\xe2\x88\x92" : '+';

	return $signe . number_format( abs( $variation ), 1, ',', ' ' );
}

/**
 * Vérifie les paires sans rien écrire.
 *
 * @param string $racine Dossier `assets/css` du thème.
 * @return int Code de sortie.
 */
function mtb_min_verifier( string $racine ): int {
	$sortie = 0;

	$echecs_p6 = mtb_min_jouer_la_table_de_cas();

	foreach ( $echecs_p6 as $echec ) {
		echo "{$echec}\n";
	}

	printf(
		"P6 : table de cas — %d cas, %d échec(s)\n",
		count( mtb_min_table_de_cas() ),
		count( $echecs_p6 )
	);

	if ( array() !== $echecs_p6 ) {
		$sortie = 1;
	}

	$sources      = mtb_min_sources( $racine );
	$signalements = 0;

	foreach ( $sources as $source ) {
		$nom      = basename( $source );
		$artefact = mtb_min_artefact_de( $source );
		$lu       = mtb_min_lire_la_source( $source );

		if ( '' !== $lu['refus'] ) {
			printf( "%-32s INVALIDE   source %s\n", $nom, $lu['refus'] );
			$sortie = 1;
			continue;
		}

		$prevol = mtb_min_prevol( $lu['octets'] );

		if ( '' !== $prevol['refus'] ) {
			printf( "%-32s INVALIDE   pré-vol : %s\n", $nom, $prevol['refus'] );
			$sortie = 1;
			continue;
		}

		$signalements += count( $prevol['signalements'] );

		foreach ( $prevol['signalements'] as $signalement ) {
			printf( "%-32s signalement : %s\n", $nom, $signalement );
		}

		if ( ! file_exists( $artefact ) ) {
			printf( "%-32s ABSENT\n", $nom );
			$sortie = 1;
			continue;
		}

		$octets_artefact = is_readable( $artefact ) ? file_get_contents( $artefact ) : false;

		if ( ! is_string( $octets_artefact ) ) {
			printf( "%-32s INVALIDE   artefact illisible\n", $nom );
			$sortie = 1;
			continue;
		}

		$canonique_artefact = mtb_min_canonique( $octets_artefact );
		$corps              = mtb_min_corps_de( $canonique_artefact );

		if ( null === $corps ) {
			printf( "%-32s INVALIDE   marqueur absent ou mal formé\n", $nom );
			$sortie = 1;
			continue;
		}

		if ( 1 !== preg_match( MTB_MIN_MOTIF, $canonique_artefact, $trouve ) ) {
			printf( "%-32s INVALIDE   marqueur illisible\n", $nom );
			$sortie = 1;
			continue;
		}

		if ( $trouve[1] !== (string) strlen( $lu['canonique'] ) || $trouve[2] !== mtb_min_empreinte( $lu['canonique'] ) ) {
			printf( "%-32s PÉRIMÉ     le marqueur ne décrit plus la source\n", $nom );
			$sortie = 1;
			continue;
		}

		$attendu = mtb_min_depouiller( $lu['canonique'] );

		if ( '' !== $attendu['refus'] ) {
			printf( "%-32s INVALIDE   %s\n", $nom, $attendu['refus'] );
			$sortie = 1;
			continue;
		}

		$defauts = array();

		// P1 — les deux côtés en forme canonique : un dépôt fraîchement cloné rend l'artefact
		// avec les fins de ligne de sa plate-forme, jamais avec celles du générateur.
		if ( $attendu['corps'] !== $corps ) {
			$defauts[] = 'P1 le corps n\'est pas ce que le dépouilleur produit';
		}

		// P2 — l'oracle des espaces signifiantes.
		if ( mtb_min_normaliser( $lu['canonique'] ) !== mtb_min_normaliser( $corps ) ) {
			$defauts[] = 'P2 le flux de jetons diffère';
		}

		// P3 — sans quoi un dépouilleur qui ne ferait rien passerait P1 et P2.
		if ( mtb_min_porte_un_commentaire( $corps ) ) {
			$defauts[] = 'P3 le corps porte encore un commentaire';
		}

		// P4 — attrape un artefact vide ou deux fichiers intervertis.
		if ( strlen( $canonique_artefact ) >= strlen( $lu['canonique'] ) ) {
			$defauts[] = 'P4 l\'artefact n\'est pas plus court que sa source';
		}

		if ( array() !== $defauts ) {
			printf( "%-32s INVALIDE   %s\n", $nom, implode( ' ; ', $defauts ) );
			$sortie = 1;
			continue;
		}

		printf(
			"%-32s à jour     %d o → %d o\n",
			$nom,
			strlen( $lu['canonique'] ),
			strlen( $canonique_artefact )
		);
	}

	// P5 — le périmètre du contrat est celui du disque.
	$anomalies = mtb_min_orphelins( $racine );

	foreach ( $anomalies as $anomalie ) {
		echo "{$anomalie}\n";
		$sortie = 1;
	}

	printf(
		"P5 : %d artefact(s) hors périmètre — pré-vol : %d feuille(s), %d signalement(s)\n",
		count( $anomalies ),
		count( $sources ),
		$signalements
	);

	return $sortie;
}

/**
 * Point d'entrée.
 *
 * @param string[] $arguments Arguments de la ligne de commande, `$argv` compris.
 * @return int Code de sortie.
 */
function mtb_min_principal( array $arguments ): int {
	$racine    = '';
	$verifier  = false;

	foreach ( array_slice( $arguments, 1 ) as $argument ) {
		if ( '--verifier' === $argument ) {
			$verifier = true;
			continue;
		}

		if ( 0 === strncmp( $argument, '--racine=', 9 ) ) {
			$racine = rtrim( substr( $argument, 9 ), '/' );
			continue;
		}

		fwrite( STDERR, "REFUS : argument inconnu {$argument}\n" );

		return 2;
	}

	if ( '' === $racine || ! is_dir( $racine ) ) {
		fwrite( STDERR, "usage : php mtb-minifier-css.php --racine=<dossier assets/css> [--verifier]\n" );

		return 2;
	}

	return $verifier ? mtb_min_verifier( $racine ) : mtb_min_generer( $racine );
}

if ( 'cli' !== PHP_SAPI ) {
	exit( 2 );
}

exit( mtb_min_principal( $argv ) );
