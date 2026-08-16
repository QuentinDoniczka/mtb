# Polices du thème `mtb`

Deux fichiers, deux familles, **zéro requête vers un domaine tiers**. Les polices sont servies depuis
`wp-content/themes/mtb/assets/fonts/`. Aucun appel à Google Fonts, Adobe Fonts ou bunny.fonts
(MASTER §4.1, BRIEF §12, contrainte D6).

La reproductibilité est **documentaire** : les commandes ci-dessous sont copiables-collables et
suffisent à refabriquer les deux fichiers à l'identique. Il n'y a **pas** de script de construction,
pas de `package.json`, pas de `node_modules` — le projet interdit toute étape de construction.

- **Date de récupération et de fabrication** : 15 août 2026
- **Outillage** : Python 3.13.7, `fontTools` 4.63.0, `brotli`
- `pyftsubset` n'étant pas exposé au `PATH` de cette machine, le module est appelé directement.
  `python -m fontTools.subset` **est** le point d'entrée de `pyftsubset` : mêmes options, même sortie.

---

## 1. Newsreader — famille « de caractère »

| | |
|---|---|
| Rôle (MASTER §4.1) | Titres, chapô, citations, nom complet d'un chien |
| Fonderie | Production Type |
| Licence | SIL Open Font License 1.1 — texte intégral dans `ofl-newsreader.txt` |
| Source amont | `https://raw.githubusercontent.com/google/fonts/main/ofl/newsreader/Newsreader%5Bopsz,wght%5D.ttf` |
| Taille du fichier source | **451 664 octets** |
| Fichier livré | `newsreader-var-latin.woff2` |
| **Taille mesurée** | **124 184 octets** |
| Axes conservés | `wght` 200 → 800, `opsz` 6 → 72 |
| Glyphes conservés | 255 |

```sh
python -m fontTools.subset "Newsreader[opsz,wght].ttf" \
  --output-file=newsreader-var-latin.woff2 \
  --flavor=woff2 \
  --unicodes="U+0000-00FF,U+0131,U+0152-0153,U+2000-206F,U+2116,U+20AC,U+2122,U+2212" \
  --layout-features="kern,liga,clig,ccmp,locl,mark,mkmk,tnum" \
  --name-IDs="*" --name-languages="0x0409" \
  --no-hinting --drop-tables+=DSIG
```

## 2. Public Sans — famille « de labeur »

| | |
|---|---|
| Rôle (MASTER §4.1) | Texte courant, navigation, tableaux, étiquettes, chiffres, boutons |
| Fonderie | U.S. Web Design System |
| Licence | SIL Open Font License 1.1 — texte intégral dans `ofl-public-sans.txt` |
| Source amont | `https://raw.githubusercontent.com/google/fonts/main/ofl/publicsans/PublicSans%5Bwght%5D.ttf` |
| Taille du fichier source | **103 316 octets** |
| Fichier livré | `public-sans-var-latin.woff2` |
| **Taille mesurée** | **23 364 octets** |
| Axes conservés | `wght` 100 → 900 |
| Glyphes conservés | 255 |

```sh
python -m fontTools.subset "PublicSans[wght].ttf" \
  --output-file=public-sans-var-latin.woff2 \
  --flavor=woff2 \
  --unicodes="U+0000-00FF,U+0131,U+0152-0153,U+2000-206F,U+2116,U+20AC,U+2122,U+2212" \
  --layout-features="kern,liga,clig,ccmp,locl,mark,mkmk,tnum" \
  --name-IDs="*" --name-languages="0x0409" \
  --no-hinting --drop-tables+=DSIG
```

Les fichiers `OFL.txt` proviennent des deux mêmes répertoires amont et sont recopiés **intégralement**,
ligne de copyright de la fonderie comprise. La SIL OFL 1.1 rend cette inclusion **obligatoire à la
redistribution** : ces deux fichiers ne se suppriment pas, ne se résument pas et ne se remplacent pas
par un lien. Les identifiants de nom `0` (copyright), `13` (licence) et `14` (adresse de la licence)
sont par ailleurs conservés **à l'intérieur** des `.woff2` — c'est le sens de `--name-IDs="*"`.

---

## 3. Jeu de caractères

`unicode-range` gelé par `docs/contracts/issue-2.md`, identique pour les deux familles :

```
U+0000-00FF, U+0131, U+0152-0153, U+2000-206F, U+2116, U+20AC, U+2122, U+2212
```

Il couvre le latin de base, le supplément latin-1 nécessaire au français
(`à â ä ç é è ê ë î ï ô ö ù û ü ÿ œ Œ Æ æ`), la ponctuation générale (`« » … – —`), ainsi que
`° × № ⁄ € ™ −`. **Aucun cyrillique, aucun grec, aucun vietnamien.**

`№` (U+2116) est **inclus** : MASTER §4.1 énumère délibérément le jeu de caractères et cette
énumération fait foi ; l'extrait de chargement du §4.2 ne le couvrait pas (arbitrage 5 du contrat).
Présence vérifiée dans les deux fichiers livrés.

Les seuls codets absents du `.woff2` par rapport à la plage demandée sont les caractères de contrôle
C0/C1 et les positions non attribuées, qui n'existent dans aucune des deux polices amont.

## 4. Fonctionnalités OpenType

Demandées : `kern, liga, clig, ccmp, locl, mark, mkmk, tnum`.

Réellement présentes après sous-ensemble — les autres n'ont **rien** à conserver une fois les glyphes
non latins retirés, ce n'est pas une perte :

| Fichier | Fonctionnalités conservées |
|---|---|
| `newsreader-var-latin.woff2` | `kern`, `liga`, `tnum` |
| `public-sans-var-latin.woff2` | `kern`, `liga`, `locl`, `tnum` |

**`tnum` est non négociable** et est présent dans les deux : MASTER §4.5 impose
`font-variant-numeric: tabular-nums` sur les cellules de tableau — n° LOF, pourcentages de diversité
génétique, années de résultats.

## 5. Vérification des axes — obligatoire

Commande imposée par le contrat, et sa sortie littérale sur les fichiers livrés :

```sh
python -c "from fontTools.ttLib import TTFont; f=TTFont('newsreader-var-latin.woff2'); print([a.axisTag for a in f['fvar'].axes])"
# ['wght', 'opsz']

python -c "from fontTools.ttLib import TTFont; f=TTFont('public-sans-var-latin.woff2'); print([a.axisTag for a in f['fvar'].axes])"
# ['wght']
```

**Les deux fichiers sont restés variables.** Aucun `--instancer`, aucun épinglage d'axe, aucune plage
restreinte. Les sept graisses de MASTER §4.1 sont donc bien gratuites, et l'axe `opsz` de Newsreader
reste entier (6 → 72) pour que le `font-optical-sizing: auto` du §4.2 ait quelque chose à piloter.

> **Note sur l'ordre.** Le contrat attendait littéralement `['opsz', 'wght']` pour Newsreader. La
> sortie donne `['wght', 'opsz']` : c'est l'ordre de la table `fvar` du **fichier amont**, que le
> sous-ensemble ne réordonne pas — et le nom de fichier amont (`Newsreader[opsz,wght].ttf`) suit la
> convention alphabétique de Google Fonts, pas l'ordre de la table. L'**ensemble** des axes est
> conforme ; seul leur ordre de déclaration diffère, ce qui n'a aucun effet en CSS.

## 6. Poids et cible — dépassement signalé

| Fichier | Octets |
|---|---|
| `newsreader-var-latin.woff2` | 124 184 |
| `public-sans-var-latin.woff2` | 23 364 |
| **Total** | **147 548** |

**La cible de MASTER §4.1 — « cible de poids : ≤ 100 Ko pour les deux fichiers réunis », et MASTER
écrit bien *cible*, pas plafond — est dépassée.** De 47 548 octets si l'on compte 1 Ko = 1 000 o,
de 45 148 o si l'on compte 1 Ko = 1 024 o ; c'est ce second chiffre que retient l'amendement 6 de
`docs/contracts/issue-2.md`.

**Les deux contraintes du BRIEF §12, elles, sont tenues** — et elles ne portent pas sur ce nombre :
« 2 fichiers de police maximum » (deux fichiers livrés) et « HTML + CSS + JS < 200 Ko » hors photos,
budget que le brief énonce séparément et dans lequel les polices n'entrent pas.

Ce n'est ni arrondi ni négocié en silence : le dépassement est remonté tel quel à `lead-issue-mtb`,
et les fichiers livrés sont la version **strictement conforme** à MASTER (axes entiers,
fonctionnalités minimales, jeu de caractères gelé).

Le poids vient presque entièrement de `gvar`, la table des deltas de variation de Newsreader :
114 860 octets avant compression, pour deux axes. Le reste se répartit entre `GPOS` (53 670) et
`GDEF` (22 103), c'est-à-dire le crénage variable.

Leviers mesurés, pour que l'arbitrage se fasse sur des chiffres et non sur des estimations :

| Piste | Newsreader | Total | Ce qu'elle coûte |
|---|---|---|---|
| **Livré — strictement conforme** | 124 184 | **147 548** | rien |
| Retirer `mark` + `mkmk` | 123 984 | 147 348 | quasi rien à gagner |
| Retirer `kern` | 86 336 | 109 700 | crénage perdu, **et toujours hors cible** |
| `wght` restreint à 400–600 (Newsreader) et 400–700 (Public Sans) | 86 168 | **107 704** | plus aucune graisse hors de celles listées en §4.1 — **toujours hors cible** |
| idem **plus** `opsz` restreint à 14–36 | 75 808 | **97 344** | **seule piste sous la cible** ; l'axe optique cesse de suivre au-delà de 36 px, donc les `h1` en `--t-2xl`/`--t-3xl` (jusqu'à 80 px) perdent leur affinement |
| `opsz` épinglé à 18 (axe supprimé) | 52 464 | 75 828 | **exclu** : contredit MASTER §4.2 |

Les deux dernières lignes **rouvrent MASTER** (§4.1 « les sept graisses sont gratuites », §4.2
`font-optical-sizing: auto`) : la décision appartient à `lead-design-mtb`, pas à `dev-ux-mtb`. Tant
qu'elle n'est pas rendue, les fichiers conformes restent en place.

Rappel : les deux fichiers sont préchargés et servis en même origine ; la cible du §4.1 porte sur le
poids, pas sur le nombre de requêtes.

## 7. Replis métriquement ajustés — valeurs mesurées

MASTER §4.2 et la question **D6** du §15 délèguent explicitement cette mesure à `dev-ux-mtb` :
« je ne les invente pas ici ». Les valeurs ci-dessous sont donc **mesurées sur les fichiers
sous-ensemblés livrés**, pas reprises d'un générateur en ligne ni estimées.

| | Newsreader → `local("Georgia")` | Public Sans → `local("Arial")` |
|---|---|---|
| `size-adjust` | **90.8 %** | **105.0 %** |
| `ascent-override` | **80.9 %** | **90.5 %** |
| `descent-override` | **29.2 %** | **21.4 %** |
| `line-gap-override` | **0 %** | **0 %** |

Déclarées dans `assets/css/base.css` sous les familles `"Newsreader repli"` et `"Public Sans repli"`,
placées en deuxième position des piles `--serif` et `--sans` de `tokens.css`.

### Méthode

1. **Instance mesurée** : `wght = 400`, c'est-à-dire le texte courant. La mesure à la graisse par
   défaut du fichier serait fausse pour Public Sans, dont l'instance par défaut est `wght = 100`
   (Thin). Pour Newsreader, `opsz` est laissé à son défaut, 18, qui est la taille du corps de texte.
2. **`size-adjust`** = largeur d'avance moyenne de la police web ÷ largeur d'avance moyenne de la
   police de repli, les deux normalisées par leur `unitsPerEm` (2000 pour les deux polices web,
   2048 pour Georgia et Arial). C'est la largeur d'avance qui décide du nombre de lignes, donc du
   décalage de mise en page au remplacement.
3. **Corpus** : un texte français ordinaire de 456 signes, reproduit ci-dessous. Le choix n'est pas
   neutre et il est donc écrit : une moyenne **non pondérée** sur `a-z A-Z 0-9` donnerait 96,4 %
   pour Newsreader au lieu de 90,8 %, parce qu'elle surreprésente capitales et chiffres, absents de
   la prose dans ces proportions. Contre-épreuve sur le pangramme « Portez ce vieux whisky au juge
   blond qui fume » : 92,1 % — cohérent avec la prose à 1,3 point près, ce qui confirme que c'est la
   moyenne non pondérée qui est l'exception.
4. **`ascent-override` / `descent-override` / `line-gap-override`** = métriques verticales de la
   police web, divisées par `size-adjust`. Les métriques retenues sont `sTypoAscender`,
   `sTypoDescender` et `sTypoLineGap` de la table `OS/2`, parce que **les deux polices web portent le
   drapeau `USE_TYPO_METRICS`** (bit 7 de `fsSelection`) : c'est bien ce jeu-là que le navigateur
   emploie. Valeurs relevées, en em — Newsreader : 0,735 / −0,265 / 0 ; Public Sans : 0,950 /
   −0,225 / 0.

Corpus employé, à recopier tel quel pour refaire la mesure :

> Les chiots naissent au printemps et quittent l'elevage a huit semaines. Chaque portee est presentee
> avec la date de naissance, le nombre de males et de femelles, puis la liste des chiots et leur
> devenir. Les parents ont une fiche complete : identite, sante, titres obtenus et resultats de
> travail. Une famille qui cherche un compagnon trouve d'abord les faits, ensuite les photographies.
> Le texte courant est compose pour etre lu longtemps, sur un telephone comme sur un grand ecran,
> sans fatigue et sans effort particulier de la part du lecteur.

### Limite honnête de cette mesure

Georgia et Arial sont mesurées **sur cette machine de développement** (Windows 11,
`C:\Windows\Fonts\georgia.ttf` et `arial.ttf`). Ce sont les mêmes fichiers Microsoft sur macOS, donc
les chiffres y valent aussi. Sur un poste Linux sans ces deux polices, `local("Georgia")` et
`local("Arial")` ne trouvent rien : la pile retombe alors sur les génériques `serif` / `sans-serif`,
**sans** ajustement métrique. Ce cas n'est pas rattrapable en CSS et n'est pas une régression : c'est
le comportement d'avant l'ajustement.

Aucune de ces valeurs n'a été vérifiée **au rendu dans un navigateur** : la mesure est géométrique.
Une contre-épreuve visuelle du « swap » reste à faire quand la stack sert des pages réelles.
