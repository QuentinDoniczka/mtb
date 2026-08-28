# MTB — Système de design

**Élevage de bergers hollandais du Mont Brabant · Entrecasteaux (Var)**

Source de vérité visuelle du projet. `dev-ux-mtb` implémente **à la lettre** ce document ;
`review-mtb` audite le code livré **contre** ce document. Une décision visuelle qui n'est pas ici
n'existe pas : c'est une question bloquante, pas une invention.

- **Version** : 1.1 — 28 août 2026 (1.0 du 15 août 2026 ; seul le §7.7 est amendé, cf. §16)
- **Décision amont** : `docs/ETAT.md` décision 8 — direction `styles/style5` « Sauge et calcaire »
  conservée, structure entièrement refaite.
- **Fichier de jetons attendu** : `wp-content/themes/mtb/assets/css/tokens.css`, plus le miroir
  `wp-content/themes/mtb/theme.json`. Aucune valeur brute (couleur, taille, durée) ailleurs.
- **Historique** : ce document s'amende par ajout. Une révision ultérieure ajoute une entrée datée au
  §15 et modifie la section concernée ; elle n'efface pas la justification d'origine.

---

## 1. Concept et direction

**L'idée en trois phrases.** Le site se compose comme une **planche d'herbier d'apothicaire** : un fond
de pierre calcaire, une encre vert pin, un vert sauge froid pour la structure, un seul métal chaud — le
laiton — employé au trait et jamais en aplat. Sur cette planche, les photographies de chiens sont posées
grandes, à bords francs, sans arrondi ni ombre, et la matière des pages — les portées, les tests de
santé, les palmarès — est composée avec la densité d'un **livre des origines** : chiffres à chasse fixe,
étiquettes en petites capitales laiton, filets qui séparent au lieu d'encadrer.

**Pourquoi cela convient à un élevage de chiens de travail.** Le registre est celui du document
sérieux, pas de la vitrine : ce que cherchent une famille, un conducteur ou un éleveur, ce sont des
faits — une date, une cotation, un numéro LOF, un résultat de RING — et une photographie qui montre le
chien. La sévérité botanique donne au fait sa place et laisse à l'image toute la lumière. La chaleur ne
vient ni d'un beige ni d'une terre cuite : elle vient du laiton, du pelage sur les photos, et de la
prose de l'éleveuse, reprise telle quelle.

**Ce que « moderne » veut dire ici.** Échelle typographique fluide, mise en page en grille CSS à trois
canaux, espacement généreux et délibéré, aucune requête tierce, aucun cadre de composants. Ce que
« moderne » ne veut **pas** dire : cartes arrondies flottant sur un fond gris, dégradés, verre dépoli,
ombres portées, violet, illustrations vectorielles génériques. Voir §13.

### 1.1 Ce qui est repris de `styles/style5`, ce qui est jeté

| Repris | Pourquoi |
|---|---|
| La palette et ses **ratios mesurés** | Calculés à la formule WCAG 2.1 (luminance relative). Recalculés indépendamment ici : les onze valeurs reproduisent celles de style5 au centième près. Rien n'a dérivé. |
| Le registre **botanique sévère** | Sauge froid, calcaire pâle, un seul métal chaud. Aucun fond jaune, aucune terre cuite, aucun beige chaud. |
| Le **filet double** | Conservé et promu **seul élément signature**, avec une réinterprétation (§2). |
| La hiérarchie portée par le **contraste serif/sans**, pas par la couleur | Trois encres seulement ; l'œil lit la taille et la famille. |
| **Aucun état signalé par la couleur seule** | Règle transverse, §8 et §12. |

| Jeté | Pourquoi |
|---|---|
| Conteneur fixe 940 px, colonne 239 px, corps 700 px | Contournement de la grille IONOS. Remplacés par une grille à trois canaux fluides (§7). |
| `display: contents` sur le menu, `order:1/2`, `pointer-events: none`, `padding-right: calc(940px - 239px)`, débordement volontaire hors de `#sidebar` | Il n'existait qu'un marqueur `<var>navigation</var>` à contourner. En thème sur mesure, nous écrivons le HTML : il n'y a plus rien à contourner. |
| Les **piles de polices système** | Le CMS n'autorisait aucun fichier. Nous auto-hébergeons deux fichiers variables (§4). |
| Le rattrapage du contenu hérité (`<font>`, Comic Sans, 10–12 px, lavande `#9288C4`) | Le nouveau modèle de contenu produit un balisage propre. Il n'y a plus rien à rattraper. La reprise de contenu (`contenu-mtb`) nettoie à la source, pas en CSS. |
| Les risques connus liés à `overflow: hidden` et au 8ᵉ item de menu | Sans objet. |
| Les **médaillons ronds** | Abandonnés. Justification écrite au §2.2 — c'est une perte assumée, pas un oubli. |

---

## 2. Signature

### 2.1 Le filet double — la seule audace, tenue partout

> **Un trait de 3 px sauge, un vide de 2 px, un trait de 1 px laiton.** Six pixels de haut, un seul
> jeton, jamais deux fois dans le même bloc visuel.

```css
--filet-double: linear-gradient(
  to bottom,
  var(--sauge)  0    3px,
  transparent   3px  5px,
  var(--laiton) 5px  6px
);
--filet-double-v: linear-gradient(
  to right,
  var(--sauge)  0    3px,
  transparent   3px  5px,
  var(--laiton) 5px  6px
);
--filet-double-h: 6px;   /* hauteur / largeur du filet, à réserver en padding */
```

Application type :

```css
.mtb-filet {
  background-image: var(--filet-double);
  background-repeat: no-repeat;
  background-position: left bottom;
  background-size: 100% var(--filet-double-h);
  padding-bottom: calc(var(--e-4) + var(--filet-double-h));
}
```

**Réinterprétation par rapport à style5** : les deux traits ne se touchent plus, un vide de 2 px les
sépare. C'est ce vide qui fait lire un *double filet gravé* et non une grosse bordure. Au-dessous de
`--e-3` d'espace disponible, on n'utilise pas le filet double : on utilise `--bord-fin`.

**Où il apparaît — liste close.**

| Emplacement | Forme | Largeur |
|---|---|---|
| Bord bas de l'en-tête du site | horizontale | pleine largeur |
| Sous le `<h1>` de page | horizontale | pleine largeur du canal texte |
| Sous chaque `<h2>` | horizontale | segment de `6rem` |
| À la place de chaque `<hr>` | horizontale | pleine largeur du canal |
| Bord haut du pied de page | horizontale | pleine largeur de la fenêtre |
| Bord gauche d'une citation (`blockquote`) et de l'**encart d'appel** | verticale | pleine hauteur |
| Bord bas d'un **bandeau photo pleine largeur** | horizontale | pleine largeur |
| Au-dessus de l'**encart « dernière portée »** quand la disponibilité est *Chiots disponibles* | horizontale | pleine largeur de l'encart |

**Où il n'apparaît jamais.** Boutons · champs de formulaire · cellules de tableau · cartes (parent,
chien, chiot) · vignettes de galerie · éléments de menu · à l'intérieur d'un bloc qui en porte déjà un ·
deux fois de suite sans au moins `--e-7` d'écart. Un `<h2>` immédiatement suivi d'un `<hr>` : le `<hr>`
n'est pas rendu.

**Pourquoi c'est une signature et pas une décoration.** Elle est portée par **un seul jeton** : changer
`--filet-double` change tous les séparateurs du site en une ligne. Elle est reproductible en CSS pur,
sans image, sans coût de poids. Et elle est la seule chose qui se répète : le reste de la page est
composé sans ornement.

### 2.2 Les médaillons ronds — abandonnés, et pourquoi

style5 découpait les photos de reproducteurs et les vignettes de galerie en **pastilles circulaires
cerclées de laiton**. C'était une bonne réponse à un mauvais problème : le module galerie IONOS servait
des vignettes de 25 px, et un cercle rattrapait des cadrages hétérogènes.

Ici, la photographie est la matière première du site (§10 du brief). Trois raisons de l'abandonner :

1. **Un cercle est le recadrage le plus destructeur pour ces photos.** Les images sont prises dehors,
   en cadrage amateur, chien debout ou en action, souvent de plein pied. Un masque circulaire ne garde
   qu'une tête, mal centrée une fois sur deux, oreilles coupées.
2. **La pastille ronde cerclée est un tic de vitrine animalière.** Elle tire le registre vers le
   « pet-shop » que le brief interdit explicitement.
3. **Une audace unique.** Deux signatures concurrentes, c'est zéro signature. Le filet double est plus
   fort, moins cher, et applicable à tout — y compris là où il n'y a pas de photo.

**Perte assumée** : le site perd un dispositif mémorable et « chaud ». Elle est compensée par les
bandeaux photo pleine largeur (§6.5) et par le pied de page sur fond `--pin`, qui donnent au site son
rythme clair/sombre. Le laiton, lui, survit : il tient le trait bas du filet, les étiquettes, les
chiffres d'index et les liens sur fond sombre.

**Conséquence pour `dev-ux-mtb`** : `border-radius: 50%` est interdit sur toute image. Voir §13.

---

## 3. Palette

**Base de calcul.** Luminance relative WCAG 2.1 : pour chaque canal `c` normalisé sur `[0,1]`,
`c_lin = c/12.92` si `c ≤ 0.03928`, sinon `((c+0.055)/1.055)^2.4` ; puis
`L = 0.2126·R + 0.7152·G + 0.0722·B` ; ratio `= (L_clair + 0.05) / (L_sombre + 0.05)`.
Tous les ratios de ce document sont **calculés**, jamais estimés. Les onze jetons hérités de style5 ont
été recalculés ici de façon indépendante et reproduisent ses valeurs.

### 3.1 Les jetons

| Jeton | Nom | Valeur | Rôle | Texte autorisé ? |
|---|---|---|---|---|
| `--calcaire` | calcaire | `#F2F1EA` | **fond de page** | fond |
| `--calcaire-creux` | calcaire creusé | `#E7E5DA` | surfaces en creux : encart d'appel, état vide, ligne survolée, bouton désactivé | fond |
| `--blanc` | blanc | `#FBFAF6` | surfaces posées : champ de formulaire, carte parent, carte chien | fond |
| `--pin` | pin | `#16241C` | encre des titres ; **et fond** du pied de page, du voile photo, de la visionneuse | oui |
| `--pin-creux` | pin creusé | `#24382C` | survol d'une ligne sur fond `--pin` | fond |
| `--texte` | texte | `#22312A` | texte courant | oui |
| `--texte-doux` | texte doux | `#4A5A50` | texte secondaire, légendes, bordure de champ | oui (fonds clairs seulement) |
| `--calcaire-ombre` | calcaire d'ombre | `#BDBCB0` | texte secondaire **sur fond `--pin` uniquement** | oui (fond `--pin` seulement) |
| `--sauge` | sauge | `#4A6B57` | structure : filet haut, fond de bouton, rail de page courante, soulignement de `<th>` | oui (fonds clairs) |
| `--sauge-fonce` | sauge foncé | `#35513F` | liens de contenu | oui (fonds clairs) |
| `--laiton` | laiton | `#9A7B3F` | filet bas, cerne, bordure d'étiquette — **jamais de texte** | **non** |
| `--laiton-texte` | laiton lisible | `#7A5F2C` | étiquettes en petites capitales, chiffres d'index | oui (fonds clairs) |
| `--laiton-clair` | laiton clair | `#E3CB9C` | liens et traits **sur fond `--pin` / `--sauge`** | oui (fonds sombres) |
| `--filet` | filet | `#CFCDBF` | hachure purement décorative — **ne porte jamais d'information** | **non** |
| `--oxyde` | oxyde | `#8C3A28` | **erreur de formulaire uniquement** — jamais un aplat, jamais décoratif | oui (fonds clairs) |

**Trois jetons ajoutés par rapport à style5**, chacun avec son ratio calculé au §12 :
`--pin-creux` (le survol sur fond sombre était impossible sans lui), `--calcaire-ombre` (le pied de page
sombre exigeait un texte secondaire lisible : `--texte-doux` sur `--pin` ne fait que **2,20:1**, échec),
`--oxyde` (validation de formulaire). L'oxyde est une exception sémantique assumée à la règle « aucune
terre cuite » : il n'apparaît qu'en texte et en bordure de champ invalide, jamais en surface, et il est
toujours doublé du mot « Erreur : ».

### 3.2 Les cinq fonds, et rien d'autre

| Fond | Usage | Encres autorisées |
|---|---|---|
| `--calcaire` | fond de page, canal texte, canal large | `--pin`, `--texte`, `--texte-doux`, `--sauge`, `--sauge-fonce`, `--laiton-texte` |
| `--blanc` | champ de formulaire, carte parent, carte chien | idem |
| `--calcaire-creux` | encart d'appel, état vide, survol de ligne, bouton désactivé | `--pin`, `--texte`, `--texte-doux`, `--sauge-fonce` |
| `--sauge` | bouton, badge « Chiots disponibles » | `--calcaire` uniquement |
| `--pin` | pied de page, bandeau photo, visionneuse, lien d'évitement | `--calcaire`, `--blanc`, `--calcaire-ombre`, `--laiton-clair` |

Aucun autre fond n'existe. Pas de dégradé, sauf le voile photo du §6.4 et le filet double du §2.

### 3.3 États de disponibilité d'une portée

Trois états, trois libellés **fixes**, jamais reformulés. Chaque état porte **trois signaux** : le mot,
la forme de la pastille, la couleur. Le mot seul suffit ; la couleur seule ne suffit jamais.

| État (valeur) | Libellé exact affiché | Pastille (forme) | Fond / encre | Bordure | Ratio |
|---|---|---|---|---|---|
| `disponible` | **Chiots disponibles** | disque **plein** ● | `--sauge` / `--calcaire` | aucune | 5,25:1 ✓ AA |
| `reserve` | **Tous réservés** | disque **à moitié plein** ◐ | `--calcaire-creux` / `--texte` | 1 px `--laiton` | 10,78:1 ✓ AAA |
| `passee` | **Portée passée** | anneau **vide** ○ | `--calcaire` / `--texte-doux` | 1 px `--texte-doux` | 6,47:1 ✓ AA |

La pastille est dessinée **en CSS**, sans police d'icônes ni fichier image, et hérite de `currentColor`
— son contraste est donc celui du libellé :

```css
.mtb-dispo::before {
  content: "";
  inline-size: .6em; block-size: .6em;
  border: 2px solid currentColor; border-radius: 50%;
  /* plein  */ background: currentColor;
  /* demi   */ background: linear-gradient(to right, currentColor 50%, transparent 50%);
  /* vide   */ background: transparent;
}
```

Quatrième signal, sur l'accueil et la page Placement uniquement : l'état *Chiots disponibles* fait
apparaître le filet double au-dessus de l'encart. Les deux autres états n'ont pas de filet.

**Le badge est un `<p>` ou un `<span>` lisible, pas une image et pas une couleur de fond seule.** Il est
toujours accompagné, en lecture d'écran comme à l'œil, de la date de naissance de la portée.

---

## 4. Typographie

### 4.1 Les deux familles — deux fichiers, zéro requête tierce

| Rôle | Famille | Licence | Fichier livré | Axes disponibles | Poids réellement utilisés |
|---|---|---|---|---|---|
| **De caractère** — titres, chapô, citations, nom complet d'un chien | **Newsreader** (Production Type) | SIL Open Font License 1.1 | `newsreader-var-latin.woff2` | `opsz 6→72`, `wght 200→800` | 400, 500, 600 |
| **De labeur** — texte courant, navigation, tableaux, étiquettes, chiffres, boutons | **Public Sans** (U.S. Web Design System) | SIL Open Font License 1.1 | `public-sans-var-latin.woff2` | `wght 100→900` | 400, 500, 600, 700 |

**Total : 2 fichiers.** Budget §12 tenu exactement. Une police variable compte pour un fichier et donne
**toute** sa plage de graisses sans coût supplémentaire : les sept graisses ci-dessus sont gratuites.

**Cible de poids** : ≤ 100 Ko pour les deux fichiers réunis après sous-ensemble. Sous-ensemble à
produire : latin de base + supplément latin-1 nécessaire au français (`à â ä ç é è ê ë î ï ô ö ù û ü ÿ œ
Œ Æ æ`) + `« » … – — ° × № ⁄` + chiffres tabulaires. Aucun cyrillique, aucun grec, aucun vietnamien.

**Pourquoi ces deux-là.**

- **Newsreader** est une serif de presse de facture transitionnelle : empattements en coin, contraste
  franc, hauteur d'x élevée. Son axe `opsz` fait le travail qu'aucune pile système ne peut faire — à
  60 px les déliés s'affinent et le dessin devient une affiche, à 19 px il s'épaissit et redevient
  lisible. C'est un seul fichier qui se comporte comme deux polices. Elle est nette et froide, ce qui
  s'accorde au sauge ; la chaleur reste au laiton, conformément à la direction.
- **Public Sans** est une grotesque neutre d'origine institutionnelle (dérivée de Libre Franklin),
  faite pour les formulaires et les tableaux d'administration. Elle évite les deux tics du moment :
  la neutralité « SaaS » d'Inter et l'humanisme aimable d'Open Sans. Ses chiffres alignés et son
  `tnum` sont exactement ce qu'il faut pour des numéros LOF, des pourcentages de diversité génétique
  et des tableaux de résultats.

**Interdiction absolue** : aucun appel à Google Fonts, Adobe Fonts, bunny.fonts ou tout autre domaine.
Les deux fichiers sont servis depuis `wp-content/themes/mtb/assets/fonts/`.

### 4.2 Chargement

```css
@font-face {
  font-family: "Newsreader";
  src: url("../fonts/newsreader-var-latin.woff2") format("woff2-variations");
  font-weight: 200 800;
  font-style: normal;
  font-display: swap;
  unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+2000-206F, U+20AC, U+2122, U+2212;
}
```
(idem pour Public Sans, `font-weight: 100 900`.)

- `font-optical-sizing: auto` sur `html` — l'axe `opsz` de Newsreader suit alors la taille rendue.
- Les **deux** fichiers sont préchargés (`<link rel="preload" as="font" type="font/woff2" crossorigin>`).
- Une police de repli **métriquement ajustée** est déclarée pour chacune (`local("Georgia")` pour la
  serif, `local("Arial")` pour la sans, avec `size-adjust` / `ascent-override`). **Les valeurs exactes
  de `size-adjust` doivent être mesurées par `dev-ux-mtb` sur les fichiers sous-ensemblés** : je ne les
  invente pas ici. Objectif : décalage de mise en page nul au remplacement.

### 4.3 Absence d'italique — décision explicite

Le budget de deux fichiers est consommé par les deux romains. **Il n'y a pas de fichier italique.**

- `font-synthesis-style` reste à `auto` : `<em>`, `<cite>`, `<i>` sont donc rendus en **oblique
  synthétique**. C'est visuellement moins bon qu'une vraie italique, mais l'information d'emphase est
  conservée — la supprimer (`font-synthesis: none`) serait une régression d'accessibilité.
- Le design **ne se sert jamais de l'italique** comme dispositif : pas de chapô en italique, pas de
  légende en italique, pas de citation en italique. L'italique n'apparaît que là où le contenu repris
  de l'ancien site l'impose (titres d'ouvrages de la page Littérature, noms latins).
- Point rouvrable, consigné au §11 : si l'oblique synthétique se révèle inacceptable sur la page
  Littérature, l'arbitrage honnête est de troquer un des deux romains contre une italique, pas de
  cacher le problème.

### 4.4 Échelle fluide

Chaque palier combine un terme en `rem` et un terme en `vw`. **Aucune taille n'est exprimée en `vw`
seul** : c'est ce qui casse le zoom à 200 % (§11 du brief).

| Jeton | Valeur | Rendu 360 px → 1440 px | Usage |
|---|---|---|---|
| `--t-xs` | `clamp(.8125rem, .78rem + .16vw, .875rem)` | 13 → 14 px | étiquettes petites capitales, légendes, mentions |
| `--t-sm` | `clamp(.9375rem, .91rem + .14vw, 1rem)` | 15 → 16 px | tableaux, notes, pied de page |
| `--t-base` | `clamp(1.0625rem, 1.02rem + .2vw, 1.1875rem)` | 17 → 19 px | **texte courant** |
| `--t-md` | `clamp(1.1875rem, 1.12rem + .3vw, 1.375rem)` | 19 → 22 px | chapô, citation, accroche |
| `--t-lg` | `clamp(1.375rem, 1.24rem + .6vw, 1.75rem)` | 22 → 28 px | `h3` |
| `--t-xl` | `clamp(1.75rem, 1.5rem + 1.1vw, 2.5rem)` | 28 → 40 px | `h2` |
| `--t-2xl` | `clamp(2.25rem, 1.75rem + 2.2vw, 3.75rem)` | 36 → 60 px | `h1` de page courante |
| `--t-3xl` | `clamp(2.75rem, 1.9rem + 3.6vw, 5rem)` | 44 → 80 px | `h1` dans un bandeau d'ouverture uniquement |

Le texte courant démarre à **17 px** et non 16 : le public inclut des personnes âgées lisant sur
téléphone (brief §4).

### 4.5 Comment la hiérarchie se construit

**Par la famille et la taille, jamais par la couleur.** Trois encres seulement sur fond clair
(`--pin` pour les titres, `--texte` pour le corps, `--texte-doux` pour le secondaire) et elles
appartiennent à la même teinte : l'œil ne lit pas une couleur, il lit une graisse et un dessin.

| Élément | Famille | Poids | Taille | Interligne | Autres |
|---|---|---|---|---|---|
| `h1` | Newsreader | 600 | `--t-2xl` | 1.05 | `letter-spacing: -.015em` · `text-wrap: balance` · filet double dessous |
| `h1` de bandeau | Newsreader | 600 | `--t-3xl` | 1.02 | `max-width: 18ch` · encre `--calcaire` |
| `h2` | Newsreader | 500 | `--t-xl` | 1.12 | `-.01em` · filet double de 6rem dessous |
| `h3` | Newsreader | 500 | `--t-lg` | 1.25 | sans filet |
| `h4` | Public Sans | 600 | `--t-xs` | 1.4 | **étiquette** : majuscules, `letter-spacing: .16em`, encre `--laiton-texte` |
| Chapô / accroche | Newsreader | 400 | `--t-md` | 1.45 | `--texte` |
| Corps | Public Sans | 400 | `--t-base` | 1.65 | `--texte` |
| Citation | Newsreader | 400 | `--t-md` | 1.5 | `--pin`, filet double vertical à gauche |
| Nom complet d'un chien (avec affixe) | Newsreader | 500 | `--t-md` | 1.3 | seul endroit où la serif descend dans une fiche |
| Tableau, cellule | Public Sans | 400 | `--t-sm` | 1.4 | `font-variant-numeric: tabular-nums` |
| Tableau, en-tête | Public Sans | 600 | `--t-xs` | 1.35 | majuscules, `.14em`, `--pin`, soulignement 3 px `--sauge` |
| Bouton, badge | Public Sans | 700 | `--t-sm` | 1.2 | majuscules, `.12em` |
| Navigation | Public Sans | 500 | `--t-sm` | 1.3 | majuscules, `.06em` |
| Légende de photo | Public Sans | 400 | `--t-xs` | 1.45 | `--texte-doux` |

**Contrainte sur l'étiquette** : majuscules + `.16em` d'interlettre à 13 px n'est lisible qu'en
**libellés courts, trois mots maximum**. `dev-front-mtb` ne place jamais une phrase dans un `h4`.

**Règle de coupe** : `hyphens: auto` sur le corps et les titres, `lang="fr"` obligatoire, plus
`overflow-wrap: break-word` sur toute cellule de tableau et tout nom complet — un affixe long à 360 px
ne doit jamais provoquer de défilement horizontal.

---

## 5. Espacement, rayons, bordures, élévation

### 5.1 Espacement

Base 4 px. Échelle discrète pour les composants, échelle fluide pour le rythme des pages.

| Jeton | Valeur | Usage |
|---|---|---|
| `--e-1` | `.25rem` (4) | interlettre visuel, ajustement de pastille |
| `--e-2` | `.5rem` (8) | **écart minimal entre deux cibles tactiles** |
| `--e-3` | `.75rem` (12) | gouttière de galerie, padding de cellule |
| `--e-4` | `1rem` (16) | padding standard, espace après un paragraphe |
| `--e-5` | `1.5rem` (24) | espace avant un `h3`, padding de carte |
| `--e-6` | `2rem` (32) | padding d'encart, gouttière de grille |
| `--e-7` | `3rem` (48) | espace avant un `h2`, gouttière portrait/texte |
| `--e-8` | `4.5rem` (72) | séparation de deux sections courtes |
| `--e-9` | `7rem` (112) | avant/après un bandeau photo |
| `--rythme-section` | `clamp(2.5rem, 6vw, 5.5rem)` | **espace vertical entre deux sections de page** |
| `--marge-page` | `clamp(1rem, 5vw, 3rem)` | marge latérale de la fenêtre (16 px à 360 px) |

**Règle** : l'espace *au-dessus* d'un titre est toujours plus grand que l'espace *au-dessous* — un titre
appartient à ce qui le suit. Rapport appliqué : `--e-7` dessus / `--e-4` dessous pour `h2`,
`--e-5` / `--e-3` pour `h3`.

### 5.2 Rayons

| Jeton | Valeur | Usage |
|---|---|---|
| `--r-0` | `0` | **tout par défaut** : photos, cartes, encarts, tableaux, bandeaux |
| `--r-1` | `2px` | commandes uniquement : bouton, champ, badge, pastille de galerie |

Il n'existe pas de troisième rayon. `border-radius: 50%` est interdit sur les images (§2.2) ; il n'est
autorisé que sur la pastille de disponibilité, qui fait 0,6 em.

### 5.3 Bordures

| Jeton | Valeur | Usage | Contraste requis |
|---|---|---|---|
| `--bord-fin` | `1px solid var(--filet)` | séparation **décorative** entre lignes d'une liste | aucun — ne porte jamais d'information |
| `--bord-actif` | `1px solid var(--texte-doux)` | **bord d'un champ de formulaire**, bord d'un badge « Portée passée » | 7,01:1 sur `--blanc` ✓ |
| `--bord-fort` | `3px solid var(--sauge)` | soulignement de `<th>`, rail de page courante, bord bas d'un bandeau | 5,25:1 sur `--calcaire` ✓ |
| `--cerne-photo` | `inset 0 0 0 1px rgb(22 36 28 / .22)` | cerne intérieur de **toute** photo (§6.6) | — |

`--filet` (1,41:1 sur calcaire) ne peut **jamais** délimiter un contrôle ni un champ : c'est un jeton
décoratif. `dev-ux-mtb` qui l'emploierait sur un `input` produit une non-conformité 1.4.11.

### 5.4 Élévation

**Aucune ombre portée sur le site.** La profondeur se fait par le fond (`--blanc` posé / `--calcaire`
neutre / `--calcaire-creux` creusé) et par le filet. C'est une position tenue, pas un oubli : les ombres
douces sur cartes arrondies sont le marqueur visuel exact que le brief interdit.

Une seule exception, justifiée par la fonction : le **panneau de navigation mobile**, qui recouvre du
contenu, porte `--ombre-panneau: 0 8px 24px rgb(22 36 28 / .18)`. Nulle part ailleurs.

---

## 6. Photographies

La décision la plus importante du site. Les photographies existantes sont prises **dehors, en lumière
variable, en cadrage amateur, portrait et paysage mêlés** — chien debout, en action, ou tenu. Le système
est écrit pour ces images-là.

> **Principe** : le **cadre** porte l'image, jamais l'inverse. Chaque emplacement a un ratio déclaré ;
> l'image le remplit. Aucune image n'impose sa taille à la page, aucune page n'attend une image
> parfaite.

### 6.1 Les cinq ratios, et rien d'autre

| Jeton | Ratio | Emplacement |
|---|---|---|
| `--r-portrait` | `4 / 5` | photo principale d'une fiche chien, photo de chiot |
| `--r-paysage` | `3 / 2` | vignette de galerie, image d'une fiche d'information, carte de portée |
| `--r-carre` | `1 / 1` | vignette d'index (grille de chiens, liste de portées) |
| `--r-bandeau` | `4/3` → `16/9` → `21/9` | bandeau d'ouverture, voir §6.5 |
| `--r-libre` | `auto` | **visionneuse uniquement** (§6.7) |

### 6.2 Cadrage — la règle unique

```css
.mtb-photo > img {
  inline-size: 100%; block-size: 100%;
  object-fit: cover;
  object-position: var(--point-interet, 50% 38%);
}
```

- Défaut `50% 38%` : sur une photo de chien en pied, la tête est au-dessus du centre géométrique. Ce
  défaut sauve la majorité des cadrages sans aucune intervention de l'éditrice.
- **Point d'intérêt** : seul réglage photographique laissé à l'éditrice, sous forme de **liste fermée**
  de cinq valeurs — *Haut gauche · Haut · Centre · Haut droite · Bas*. Elle ne saisit jamais de
  pourcentage. C'est un choix dans une liste, donc conforme au brief §6.

### 6.3 Une photo portrait dans un emplacement paysage (et l'inverse)

**Le cadre gagne toujours.** L'image recouvre (`cover`) et est ancrée sur le point d'intérêt. Il n'y a
ni bande noire, ni fond flouté, ni image déformée, ni hauteur variable selon la source.

Deux conséquences assumées, écrites pour que personne ne les « corrige » plus tard :

1. Une photo paysage placée dans le portrait `4/5` d'une fiche chien est **rognée sur les côtés**. C'est
   voulu : la fiche chien doit avoir la même silhouette pour les 17 chiens.
2. Une photo portrait placée dans un `3/2` de galerie est **rognée en haut et en bas**. Le point
   d'intérêt permet de choisir laquelle des deux extrémités on garde.

**La seule échappatoire** est la visionneuse (§6.7), où l'image est vue entière. C'est là, et nulle part
ailleurs, que le cadrage d'origine est respecté.

### 6.4 Texte sur une photo — la règle du voile, avec sa preuve

Du texte n'est superposé à une image **qu'au bandeau d'ouverture** (§6.5). Nulle part ailleurs : pas de
titre sur une vignette, pas de légende incrustée, pas de nom de chien posé sur la galerie.

```css
--voile-photo: linear-gradient(
  to top,
  rgb(22 36 28 / .86)  0%,
  rgb(22 36 28 / .72) 34%,
  rgb(22 36 28 / .28) 66%,
  rgb(22 36 28 / .06) 100%
);
```

**Règle du voile** : le texte n'occupe que la zone où l'opacité du voile est **≥ 0,72**, c'est-à-dire le
tiers bas du bandeau (`0 → 34 %` de la hauteur en partant du bas). Le bloc de texte porte
`padding-block-end: --e-6` et ne dépasse pas cette zone.

**Preuve.** Cas le plus défavorable : un pixel **blanc pur** sous le voile.

| Position | Opacité | Fond composé | `--calcaire` (#F2F1EA) dessus |
|---|---|---|---|
| Bas du bandeau | 0,86 | `#37433C` | **9,18:1** ✓ AAA |
| Limite haute de la zone de texte | 0,72 | `#576155` | **5,65:1** ✓ AA (toutes tailles) |
| Image absente (fond `--pin`) | — | `#16241C` | **14,23:1** ✓ AAA |

Le contraste est donc garanti **quelle que soit la photographie**, y compris une photo surexposée ou un
ciel blanc. C'est ce qui rend le composant sûr entre les mains d'une non-designer.

### 6.5 Le bandeau d'ouverture

Pleine largeur de la fenêtre, sous l'en-tête. Porte le `<h1>` de la page.

| Largeur de fenêtre | Ratio | Note |
|---|---|---|
| < 40 rem | `4 / 3` | un `21/9` sur téléphone ne montrerait qu'une tranche d'herbe |
| 40 – 64 rem | `16 / 9` | |
| ≥ 64 rem | `21 / 9`, plafonné à `max-block-size: 32rem` | |

- Voile `--voile-photo`, `h1` en `--t-3xl` Newsreader 600 `--calcaire`, accroche en `--t-md` `--calcaire`
  sous le titre, largeur maximale `24ch`.
- **Bord bas** : filet double, pleine largeur.
- Image : `fetchpriority="high"`, `loading="eager"`, `decoding="async"`, `width`/`height` explicites.
- **Sans image** : le bandeau ne disparaît pas et ne garde pas son ratio — il devient une **bande de
  texte** sur fond `--pin`, `padding-block: --e-8`, mêmes styles de titre. Contraste 14,23:1. La page
  reste juste, l'éditrice n'a rien cassé.
- `alt` : rempli par l'éditrice. Si elle le laisse vide, l'image est rendue `alt=""` (décorative) — le
  `h1` porte le sens. Elle ne peut pas produire d'image sans alternative accessible.

### 6.6 Comportement avec une source de mauvaise qualité

| Cas | Comportement — obligatoire |
|---|---|
| Photo pâle, délavée, ciel blanc | **Cerne intérieur** `--cerne-photo` sur **toute** photo : `box-shadow: inset 0 0 0 1px rgb(22 36 28 / .22)`. Le bord de l'image existe toujours contre le fond calcaire. |
| Photo sombre, sous-exposée | Aucune correction. Le cerne suffit ; le fond calcaire fait la séparation. |
| Photo de trop basse définition | L'emplacement ne s'agrandit pas au-delà de la largeur naturelle × 1,5. Au-delà, elle est servie à sa largeur et centrée dans son canal, cerne compris. |
| Photo à transparence (PNG détouré) | Fond de l'emplacement `--calcaire-creux` : jamais de damier, jamais de trou. |
| Photo qui ne charge pas | L'emplacement conserve son ratio et son fond `--calcaire-creux` ; le texte `alt` s'affiche à l'intérieur en `--texte-doux`, `--t-sm`. Pas de pictogramme cassé. |
| Photo absente (champ vide) | §9.2. |

**Interdit** : `filter: saturate()`, `contrast()`, `brightness()`, `sepia()`, `grayscale()` et tout
duotone sur une photographie de chien. Un filtre qui « sauve » une mauvaise photo abîme les neuf autres,
et il falsifie la robe — donnée d'élevage (brief §4, exactitude du domaine).

### 6.7 Galerie et visionneuse

- **Galerie** : `grid`, `repeat(auto-fill, minmax(min(100%, 9rem), 1fr))`, gouttière `--e-3`, vignettes
  en `--r-paysage`. À 360 px : **deux colonnes**, sans défilement horizontal.
- Chaque vignette est un `<button>` ou un `<a>` de **44 px minimum** dans les deux dimensions (garanti
  par le ratio et la largeur de colonne).
- **Visionneuse** : fond `--pin` opaque, image en `object-fit: contain`, `--r-libre` — **c'est le seul
  endroit où la photo est vue entière**. Légende et compteur (« 3 / 12 ») en `--calcaire-ombre`. Ferme
  au clavier (`Échap`), piège de focus, bouton « Fermer » visible, jamais dépendante du survol.
- La visionneuse est le seul composant JavaScript visuel du site. Sans JS, la vignette est un lien
  direct vers le fichier image : rien n'est perdu.

### 6.8 Une photo à côté d'un corps de texte

Cas de la fiche chien et de la carte parent.

- ≥ 64 rem : grille à deux colonnes `minmax(16rem, 22rem) 1fr`, gouttière `--e-7`. Le portrait `4/5`
  occupe la colonne de gauche et devient `position: sticky; top: --e-6` **uniquement si la colonne de
  droite dépasse 1,5 fois sa hauteur** — sinon il reste statique. Pas de sticky sous 64 rem.
- < 64 rem : le portrait passe au-dessus du texte, pleine largeur du canal texte, ratio inchangé.
- Une image **jamais** flottée dans le texte (`float`), jamais habillée. Le brief impose 360 px sans
  défilement horizontal ; un habillage à 328 px ne laisse pas une mesure lisible.
- Légende sous l'image, alignée à gauche, `--t-xs` `--texte-doux`, jamais centrée.

### 6.9 Performance des images

Toute image hors bandeau : `loading="lazy"`, `decoding="async"`, `width`/`height` ou `aspect-ratio`
déclarés (décalage cumulé nul). Formats modernes servis par WordPress, `srcset` sur toutes les tailles
d'emplacement définies ci-dessus. Les photographies sont hors du budget de 200 Ko, mais aucune image
**décorative** n'est introduite par le design : le système ne consomme pas un seul octet d'image.

---

## 7. Mise en page

### 7.1 La grille à trois canaux

Une seule grille, appliquée à tout conteneur de contenu. Elle remplace le conteneur fixe de style5.

```css
.mtb-canal {
  display: grid;
  grid-template-columns:
    [pleine-debut] var(--marge-page)
    [large-debut]  minmax(0, 1fr)
    [texte-debut]  min(100% - 2 * var(--marge-page), var(--l-texte)) [texte-fin]
                   minmax(0, 1fr) [large-fin]
                   var(--marge-page) [pleine-fin];
}
.mtb-canal > *              { grid-column: texte; }
.mtb-canal > .alignwide     { grid-column: large; }
.mtb-canal > .alignfull     { grid-column: pleine; }
```

| Jeton | Valeur | Correspondance `theme.json` |
|---|---|---|
| `--l-texte` | `36rem` (576 px, ≈ 63 signes à 19 px) | `settings.layout.contentSize` |
| `--l-large` | `68rem` (1088 px) | `settings.layout.wideSize` |
| pleine | 100 % de la fenêtre | `alignfull` |

- **Canal texte** : prose, titres, listes, formulaires, tableaux courts.
- **Canal large** : galeries, grilles de chiens, listes de portées, tableaux de résultats, fiche chien
  à deux colonnes.
- **Canal pleine** : bandeau d'ouverture, bande photo, pied de page. Rien d'autre.

`--l-large` est plafonné à 68 rem, pas à la largeur de l'écran : sur un moniteur 27 pouces la page reste
un document, pas un tableau de bord.

### 7.2 Points de rupture — trois, pas davantage

| Jeton | Valeur | Ce qui change |
|---|---|---|
| `--bp-tableau` | `48rem` (768 px) | les tableaux passent en lignes empilées (§7.6) |
| `--bp-nav` | `60rem` (960 px) | la navigation passe en panneau déclenché par un bouton |
| `--bp-fiche` | `64rem` (1024 px) | la fiche chien et la carte parent passent à deux colonnes |

Tout le reste est fluide (`clamp`, `minmax`, `auto-fill`). Aucune requête média décorative.

### 7.3 Composition d'une page

```
lien d'évitement (« Aller au contenu », visible au focus)
en-tête du site        — canal pleine, fond --calcaire, filet double en bord bas
[bandeau d'ouverture]  — canal pleine, facultatif (§6.5)
<main id="contenu">    — canal, sections séparées de --rythme-section
pied de page           — canal pleine, fond --pin, filet double en bord haut
```

**En-tête** : nom de l'élevage à gauche (Newsreader 500, `--t-md`, encre `--pin`, sans logo image
obligatoire), navigation à droite ≥ `--bp-nav`. **Non collant** — un en-tête collant mange l'écran d'un
téléphone et complique le parcours au clavier. Décision assumée.

**Navigation < `--bp-nav`** : bouton `<button aria-expanded>` libellé « Menu », panneau déroulant plein
canal, fond `--calcaire`, `--ombre-panneau`. **Le panneau est visible par défaut dans le HTML** ; c'est
le JavaScript qui le replie au chargement. Si le JS échoue, le menu reste entièrement accessible.
C'est la leçon de style5, reprise telle quelle : on ne cache jamais un menu en attendant une interaction.

**Pied de page** sur fond `--pin` : coordonnées de l'élevage (3060 Route de Salernes, 83570
Entrecasteaux · 0680505619 · mtbrabant@gmail.com), plan du site en trois colonnes, mentions légales,
« © Fabienne Guéneau ». Liens en `--laiton-clair` (10,20:1), texte secondaire en `--calcaire-ombre`
(8,43:1). Cibles ≥ 44 px.

### 7.4 Comment se lit une portée

Ordre imposé — c'est l'ordre dans lequel une famille lit la page.

1. **Identité** — `h1` = identifiant (« Portée A3 2025 »), date de naissance, **badge de disponibilité**
   (§3.3), nombre de mâles et de femelles. Canal texte. Filet double sous le `h1`.
2. **Les parents** — deux cartes côte à côte ≥ `--bp-fiche`, empilées en dessous. Chaque carte : portrait
   `4/5`, nom d'usage en Newsreader 500, nom complet avec affixe en dessous, tests de santé en liste de
   définition, et un lien « Voir la fiche » **quand le parent a une fiche**. Un étalon extérieur sans
   fiche affiche son nom et son élevage, sans lien, sans carte vide.
3. **Les chiots** — tableau (nom, sexe, n° LOF, devenir), canal large, chiffres tabulaires. Empilé sous
   `--bp-tableau` (§7.6).
4. **Le commentaire de l'éleveuse** — canal texte, prose reprise intégralement.
5. **La galerie** — canal large.
6. **Navigation entre portées** — « Portée précédente » / « Portée suivante », par date de naissance.

### 7.5 Comment se lit une fiche chien

1. **`h1` = nom d'usage.** Juste dessous, le nom complet avec affixe en Newsreader 500 `--t-md`.
2. **Deux colonnes ≥ `--bp-fiche`** : portrait `4/5` à gauche (§6.8), identité à droite — sexe, variété
   (*Poil long* / *Poil court*), naissance, décès éventuel, statut, taille, couleur, masque, génétique de
   robe. Composée en **liste de définition** (`<dl>`), libellé en étiquette laiton, valeur en corps.
3. **Santé** — `h2` « Santé », liste de définition : HD, ED, LTV, DM, SDCA 1, SDCA 2, ADN identifié,
   diversité génétique. Chaque valeur est **recopiée**, jamais interprétée. Une donnée absente affiche
   « Non renseigné » (§9.3).
4. **Titres et brevets** — TC, CSAU, cotation LOF, expositions.
5. **Palmarès de travail** — tableau, calculé depuis les résultats saisis.
6. **Portées** — calculé : les portées où ce chien est père ou mère, en cartes.
7. **Galerie**, puis **lien pedigree** (LOF Select), rendu comme lien externe (§8.6).

Aucune de ces sections n'est saisie deux fois : 5 et 6 sont des calculs.

### 7.6 Un long tableau de résultats sur un téléphone

**Aucun défilement horizontal, aucun conteneur à barre de défilement.** Sous `--bp-tableau`, chaque
`<tr>` devient un bloc et chaque cellule affiche son libellé de colonne :

```css
@media screen and (max-width: 47.999rem) {
  .mtb-tableau thead { position: absolute; clip-path: inset(50%); }   /* retiré à l'œil, gardé au lecteur d'écran */
  .mtb-tableau tr    { display: block; padding-block: var(--e-4); border-block-end: var(--bord-fin); }
  .mtb-tableau td    { display: grid; grid-template-columns: 8rem 1fr; gap: var(--e-3); padding: var(--e-1) 0; }
  .mtb-tableau td::before {
    content: attr(data-libelle);
    font: 600 var(--t-xs)/1.35 var(--sans);
    text-transform: uppercase; letter-spacing: .14em; color: var(--laiton-texte);
  }
}
```

> **Dépendance dure envers `dev-back-mtb` / `dev-front-mtb`** : le rendu serveur de tout composant
> tableau (résultats de travail, chiots) doit émettre `data-libelle="…"` sur **chaque** `<td>`, avec
> exactement le libellé de l'en-tête de colonne du §10. Sans cet attribut, les tableaux sont illisibles
> sur téléphone. C'est un point de contrat, pas une suggestion.

La requête est en `@media screen` : **en impression, le tableau reste un tableau** (§9.6).

Groupement : la page Travail affiche un `h2` par discipline, un tableau par discipline, trié par année
décroissante. Une discipline sans aucune ligne n'affiche **ni titre ni tableau** (§9.3).

### 7.7 Jusqu'à 360 px

À 360 px : `--marge-page` = 18 px, canal texte = 324 px, corps à 17 px, galerie à deux colonnes,
tableaux empilés, navigation en panneau, `h1` à 44 px avec `text-wrap: balance` et `hyphens: auto`.
**Aucun élément à largeur fixe supérieure à 300 px** dans tout le système.

#### 7.7.1 Renvoi à la ligne des jetons insécables — règle amendée le 28 août 2026

**Ce que prescrivait la version 1.0** : « `overflow-wrap: break-word` sur les noms complets, les
numéros LOF et les URL de pedigree ». Cette phrase est **remplacée** par la présente sous-section ;
elle est conservée ici pour l'historique.

**Pourquoi elle est amendée.** Trois emplacements livrés ont employé `anywhere` là où la règle disait
`break-word` — `assets/css/entete-pied.css:216` (et sa reprise l. 227),
`assets/css/blocs/mtb-coordonnees-plan.css:75`, `assets/css/base.css` §12 — chacun avec une mesure
écrite en commentaire, et les trois mesures concordent. Trois contournements motivés par le même fait
mesuré ne sont pas trois écarts à exempter : c'est la règle qui était fausse. **`break-word` cesse
d'être la valeur par défaut.**

**Le fait, normatif et vérifiable sans le projet.** CSS Text Level 3, définition d'`overflow-wrap` :
les occasions de coupure introduites par `break-word` **ne sont pas prises en compte dans le calcul
des tailles intrinsèques min-content** ; celles introduites par `anywhere` le sont. Autrement dit
`break-word` ne change que le rendu d'une boîte **déjà dimensionnée** ; `anywhere` change en plus **la
largeur que la boîte réclame**. Conséquence directe : dès qu'une boîte se dimensionne sur son contenu
— élément flex, `inline-flex`, `inline-block`, flottant, `fit-content`, `position: absolute`, piste de
grille en `auto` / `min-content` / `max-content` — `break-word` ne fait strictement **rien** contre un
débordement horizontal. C'est le seul et même fait dans les trois cas ci-dessous.

**Les mesures, à reproduire à l'identique.** Protocole : fenêtre de 360 px puis de 320 px,
`document.documentElement.scrollWidth` pour le débordement ; largeur min-content obtenue en appliquant
`inline-size: min-content` à la boîte dans l'inspecteur.

| # | Emplacement | Contenu mesuré | `normal` | `break-word` | `anywhere` |
|---|---|---|---|---|---|
| 1 | `base.css` §12 — prose reprise, `/portees/c-2007/` | ancre de lien dont le libellé est une URL YouTube verbatim, **67 signes sans aucune occasion de coupure** | min-content **347,25 px** | min-content **347,25 px** — *inchangé* | min-content **15,5 px** |
| 1bis | idem, `/portees/o-2018/` | même défaut, autre URL | 343,44 px | 343,44 px | — |
| 2 | `blocs/mtb-coordonnees-plan.css:75` | courriel dans une boîte `inline-flex` (cible tactile de 44 px), fenêtre 360 px, zoom du texte seul à 200 % | — | document rendu à **387 px** pour 360 | document rendu à **360 px** |
| 3 | `entete-pied.css:227` | libellé de navigation dans un `<span>` que le cœur met lui-même en `break-word` à (0,2,0), boîte flex dimensionnée sur son contenu, fenêtre de 180 px (téléphone de 360 px zoomé à 200 %) | — | débordement de **13 px** | pas de débordement |

Rappel du contexte du cas 1 : le canal texte vaut **324 px** à 360 px de fenêtre ; avant correction le
document rendait **365 px dans une fenêtre de 360** et **362 px dans une fenêtre de 320**. Échec du
présent §7.7 et du critère AA 1.4.10, donc **bloquant (D7)**. `hyphens: auto` n'entame pas le jeton :
une URL n'a pas de syllabe. Et le libellé **ne se réécrit pas** — c'est de la prose reprise de l'ancien
site, où le texte du lien *est* l'URL ; lui inventer un intitulé lisible inventerait des mots que le
site source n'a jamais écrits (exactitude du domaine, `CLAUDE.md`). La correction est donc
typographique, et elle vise la **classe** du problème : toute URL longue reprise plus tard produit le
même défaut.

**La règle.**

1. **`overflow-wrap: anywhere` est la valeur par défaut du site**, portée une seule fois par un
   **sélecteur racine** dans `base.css` (voir §7.7.2 pour la raison décisive), donc héritée par tout
   texte : prose reprise, prose saisie, libellés de lien, URL nues, courriels, numéros LOF, noms
   complets avec affixe, titres, légendes.
2. **Sur une boîte dimensionnée par son conteneur** — un paragraphe ou un titre dans le canal texte —
   `anywhere` et `break-word` **rendent exactement la même chose** : la différence ne porte que sur la
   taille intrinsèque, qui n'est pas consultée. Il n'y a donc **rien à déclarer localement** dans ce
   cas, et rien à arbitrer : l'héritage suffit.
3. **Seule exception dure : `th` et `td` restent en `break-word`** (`base.css:323`). Raison : avec
   `table-layout: auto`, la largeur des colonnes dérive des contributions min-content ; `anywhere` les
   effondrerait à un signe par ligne et rendrait le tableau illisible **sans rien gagner**, puisque le
   §7.6 empile déjà les tableaux sous `--bp-tableau`. Aucun tableau n'a besoin d'`anywhere` pour tenir
   les 360 px. La déclaration en propre bat l'héritage : c'est acquis et déjà exploité.
4. **Toute autre déclaration locale de `break-word` doit porter sa mesure en commentaire**, sinon elle
   est retirée au profit de l'héritage. Une déclaration de `break-word` sans mesure est présumée
   redondante (boîte dimensionnée par son conteneur) ou fausse (boîte dimensionnée sur son contenu).
   `review-mtb` audite sur ce critère.
5. **Une déclaration locale d'`anywhere` ne se retire que sur mesure**, jamais par déduction :
   l'héritage peut être intercepté dans la page comme dans la toile par une feuille du cœur. Le cas est
   **avéré** pour `span.wp-block-navigation-item__label` — `entete-pied.css:227` reste nécessaire.

**Statut des trois emplacements cités** : **conformes**, plus en écart. La règle rejoint le code, pas
l'inverse.

#### 7.7.2 Ce que la règle vaut côté éditeur

**Le constat.** La règle corrective du lot 8 est posée sur des sélecteurs de classe
(`.entry-content`, `.mtb-fiche-portee__commentaire`, `.mtb-fiche-chien__commentaire`). Or le cœur
réécrit les feuilles passées à `add_editor_style()` : les sélecteurs racine reconnus (`html`, `body`,
`:root`, et leurs formes `:where()`) sont **remplacés** par `.editor-styles-wrapper`, **tous les autres
sont préfixés** par lui (vérifié dans WordPress 6.9, cf. l'en-tête d'`assets/css/editor.css`). Une
règle de classe ne s'applique donc dans la toile que s'il existe, **à l'intérieur** de la toile, un
élément portant cette classe. L'agent du lot 8 rapporte que la règle y est **inerte** : l'éleveuse voit
la prose déborder pendant qu'elle compose, sur un contenu que le site public rend correctement.

**Le verdict.** La règle doit valoir dans la toile **exactement ce qu'elle vaut sur le site** — sans
exception ni atténuation. Un écran d'édition qui ment sur le renvoi à la ligne est un défaut, pas un
détail : il pousse l'éleveuse à corriger un problème qui n'existe pas, c'est-à-dire à réécrire une
prose qu'on a précisément recopiée pour ne pas la réécrire.

**Le moyen, et pourquoi c'est celui-là.** La règle est portée par un **sélecteur racine**. C'est la
seule construction dont le cœur garantit lui-même la transposition dans la toile : le `body` du site
et l'enveloppe de la toile reçoivent la même déclaration, **sans nommer aucun sélecteur d'éditeur**,
donc sans rien deviner. C'est déjà par ce mécanisme que les jetons, les fonds, les familles et les
interlignes sont justes dans la toile. Deux conséquences à tenir : les `th`/`td` conservent leur
`break-word` en propre, qui bat l'héritage (point 3 du §7.7.1) ; les déclarations locales d'`anywhere`
restent jusqu'à mesure contraire (point 5).

**Le résidu, nommé et non comblé.** L'héritage peut être intercepté dans la toile par une feuille de
blocs du cœur — le cas est avéré à (0,2,0) pour le libellé de navigation. Si, **après** la pose de la
règle racine, une mesure dans la toile montre que la prose y déborde encore, alors un crochet nommé
dans `editor.css` devient nécessaire — et **nommer ce sélecteur suppose d'avoir inspecté le DOM de la
toile**. C'est un **préalable à lever par la mesure**, pas une valeur à deviner : cf. §15, question D8.
L'agent du lot 8 a eu raison de laisser ce crochet non écrit ; nommer un sélecteur qu'il n'avait pas
vu aurait été une invention, que ce document interdit.

**Ordre d'exécution imposé à la chaîne de correction**, sans étape sautée :

1. déplacer la règle sur un sélecteur racine dans `base.css` et supprimer la règle de classe du §12,
   devenue redondante ;
2. mesurer dans la toile, sur une page libre contenant l'URL de 67 signes du cas 1, en fenêtre de
   360 px puis de 320 px ;
3. **seulement si** la mesure 2 échoue : inspecter le DOM de la toile, nommer le conteneur réel, écrire
   le crochet dans `editor.css` avec la mesure en commentaire.

#### 7.7.3 Ce que cet amendement ne change pas

La règle de coupe du §4.5 (`hyphens: auto` sur le corps et les titres, `lang="fr"` obligatoire,
`break-word` sur les cellules de tableau et les noms complets) **reste vraie telle qu'elle est écrite**
et n'est pas rouverte : les cellules gardent `break-word` (point 3), et pour un nom complet posé dans
le canal texte les deux valeurs rendent la même chose (point 2). Idem pour le §9.4 (titre très long) et
le §10.2 (n° LOF) : aucun de leurs rendus ne bouge. Palette, typographie, espacement, photographie et
vocabulaire sont inchangés.

### 7.8 Zoom 200 %

Aucune taille en `vw` seul (§4.4), aucune hauteur fixe sur un conteneur de texte (`min-block-size`
seulement), aucun `overflow: hidden` sur un conteneur susceptible de contenir du texte. À 200 %, la mise
en page d'un écran 1280 px devient celle d'un 640 px : tout est déjà prévu par les points de rupture.

---

## 8. États

**Règle générale : deux signaux au minimum, jamais la couleur seule.** Et le focus est toujours
**visuellement distinct** du survol.

### 8.1 L'anneau de focus — un seul, partout, y compris sur une photo

```css
:focus-visible {
  outline: 2px solid var(--pin);
  outline-offset: 2px;
  box-shadow: 0 0 0 2px var(--calcaire);
  border-radius: var(--r-1);
}
```

Anneau **clair collé à l'élément**, anneau **sombre juste dehors**. Comme `--calcaire` (L = 0,8773) et
`--pin` (L = 0,0152) encadrent toute la plage de luminance, **au moins un des deux anneaux contraste
toujours** avec le fond, quel qu'il soit.

**Preuve.** Le pire fond possible est celui qui minimise les deux ratios à la fois, atteint quand ils
s'égalisent : `(L+0,05)² = (0,8773+0,05)·(0,0152+0,05)` d'où `L+0,05 = 0,2458` et ratio commun
**3,77:1**. Le meilleur des deux anneaux est donc **toujours ≥ 3,77:1**, au-dessus des 3:1 exigés par
le critère 1.4.11 — y compris posé sur une photographie, un fond sauge ou un fond pin.

Le survol n'utilise **jamais** d'anneau : la confusion focus/survol est impossible.

### 8.2 Lien dans le contenu

| État | Signaux |
|---|---|
| normal | `--sauge-fonce` (7,73:1) **+ souligné**, décoration `--laiton` 1 px, `text-underline-offset: .2em` |
| survol | encre `--pin` **+** décoration `--pin` **portée à 2 px** |
| focus | anneau §8.1 (le soulignement ne bouge pas) |
| visité | inchangé — pas de couleur de lien visité (elle serait le seul signal, et elle porte une information privée à l'écran) |

Sur fond `--pin` : encre `--laiton-clair` (10,20:1), même soulignement, survol vers `--calcaire`.

### 8.3 Lien de navigation

| État | Signaux |
|---|---|
| normal | `--texte`, pas de soulignement, cible ≥ 44 px |
| survol | fond `--calcaire-creux` **+** trait bas `--laiton` de 2 px |
| **page courante** | trait bas `--sauge` de **3 px** **+** graisse **600** **+** `aria-current="page"` |
| focus | anneau §8.1 |

Survol et page courante diffèrent par **l'épaisseur du trait, sa couleur et la graisse** : trois écarts.

### 8.4 Bouton

| État | Signaux |
|---|---|
| normal | fond `--sauge`, libellé `--calcaire` 700 majuscules (5,25:1), `--r-1`, hauteur ≥ 48 px |
| survol | fond `--pin` (14,23:1) **+** cerne intérieur 1 px `--laiton` |
| actif | `translateY(1px)` |
| focus | anneau §8.1 |
| **désactivé** | fond `--calcaire-creux`, libellé `--texte-doux` (5,79:1, **reste lisible**), `cursor: not-allowed`, `aria-disabled="true"`, **et le libellé change** (« Envoi en cours… ») |

Le bouton désactivé conserve un contraste AA : un bouton grisé illisible est une non-conformité
courante que ce système refuse.

### 8.5 Champ de formulaire

| État | Signaux |
|---|---|
| normal | fond `--blanc`, `--bord-actif` (7,01:1), hauteur ≥ 48 px, **étiquette visible au-dessus** (jamais de `placeholder` en guise d'étiquette) |
| focus | anneau §8.1 **+** bord porté à `--sauge` (l'épaisseur ne change pas : aucun décalage) |
| **erreur** | bord 2 px `--oxyde` **+** message sous le champ préfixé « **Erreur :** » en `--oxyde` (6,74:1 sur `--calcaire`) **+** `aria-invalid="true"` **+** `aria-describedby` vers le message |
| requis | mention « (obligatoire) » **écrite dans l'étiquette**, pas un astérisque coloré |

### 8.6 Lien externe

Un lien qui sort du site (pedigree LOF Select) porte, après le libellé, un **chevron dessiné en CSS**
(deux bordures de 1 px `--laiton`, 7 × 7 px) et le texte caché « (nouvelle fenêtre) » si `target="_blank"`.
Aucune police d'icônes, aucun fichier SVG externe.

---

## 9. États vides et erreurs

**Règle transverse (D12)** :

> **Côté public, un composant sans contenu ne s'affiche pas. Côté éditeur, il s'affiche toujours, avec
> la phrase française qui dit ce qui manque.**

C'est ce qui permet à l'éditrice de comprendre son erreur sans qu'un visiteur voie une page cassée.

### 9.1 L'état vide côté éditeur — une seule apparence, pour tous les composants

```
Contour tireté 1 px --laiton, rayon --r-0, fond --calcaire-creux,
padding --e-6, texte --t-sm --texte-doux, aligné à gauche.
Ligne 1 : le nom du composant en étiquette laiton.
Ligne 2 : une phrase, toujours de la forme
          « Ce bloc n'affiche rien tant que <ce qui manque>. »
```

Exemple : « **ENCART DERNIÈRE PORTÉE** — Ce bloc n'affiche rien tant qu'aucune portée n'est publiée. »
Cette apparence est identique pour les dix composants du catalogue. Elle n'existe **que** dans l'éditeur
(feuille `editor.css`).

### 9.2 Photo manquante

- Emplacement **facultatif** (galerie, image d'une fiche d'information) : l'emplacement n'existe pas.
  Aucun trou, aucune réserve.
- Emplacement **structurant** (portrait d'une fiche chien, carte parent) : l'emplacement garde son ratio
  `4/5`, fond `--calcaire-creux`, cerne `--cerne-photo`, filet double en bord bas, et au centre le nom
  d'usage du chien en Newsreader 400 `--t-md` `--texte-doux` (6,47:1). **Aucun pictogramme d'appareil
  photo, aucune silhouette de chien, aucune image de remplacement.**
- Bandeau d'ouverture sans image : §6.5, bande de texte sur `--pin`.

### 9.3 Donnée absente — le vocabulaire de l'absence

| Situation | Ce qui s'affiche |
|---|---|
| Un champ de fiche non rempli | **« Non renseigné »** en `--texte-doux`, jamais un tiret seul, jamais un blanc |
| Une section entière non remplie (Santé, Titres) | La section n'est pas rendue |
| Un chien sans aucun résultat de travail | La section « Palmarès » n'est pas rendue |
| Une discipline sans aucune ligne | Ni titre ni tableau |
| Une portée sans liste de chiots | « Liste des chiots non renseignée. » en `--texte-doux`, canal texte |
| Une galerie vide | Le bloc n'est pas rendu |
| Une liste de portées filtrée sans résultat | « Aucune portée pour cette année. » **+** un lien « Voir toutes les portées » |
| Un parent sans fiche (étalon extérieur) | Nom et élevage en clair, sans lien, sans carte grisée |

**« Non renseigné » n'est jamais remplacé par « Aucun », « Non testé » ou « Néant ».** Une donnée absente
n'est pas une donnée négative : écrire « Aucun » à la place d'un champ vide inventerait un fait
d'élevage (brief §4, D11).

### 9.4 Contenu à moitié rempli

| Cas | Comportement |
|---|---|
| Titre très long (nom complet avec affixe) | `text-wrap: balance`, `hyphens: auto`, `overflow-wrap: break-word`. Jamais de troncature à `…` sur un nom de chien. |
| Titre très court (« A3 ») | Aucune hauteur minimale imposée au titre : le filet double suit le texte. |
| Texte libre très long | Canal texte à 36 rem, il se lit. Aucune limite de hauteur, aucun « lire la suite ». |
| Un seul chien dans une grille de 4 colonnes | `repeat(auto-fill, …)` : la carte garde sa largeur de colonne et reste à gauche. Elle ne s'étire pas sur toute la ligne. |
| Deux parents dont un seul a une fiche | Les deux cartes gardent la même taille ; celle sans fiche n'a simplement pas de bouton. |

### 9.5 Pages d'erreur

- **404** : `h1` « Page introuvable », un paragraphe, un champ de recherche, et trois liens — Accueil,
  Les portées, La meute. Fond `--calcaire`, pas d'illustration, pas d'humour.
- **Page protégée par mot de passe** : encart `--calcaire-creux` avec filet double vertical, étiquette
  « Page protégée », phrase « Cette page est réservée. Saisissez le mot de passe communiqué par
  l'élevage. », champ + bouton. Aucun indice sur le contenu.
- **Recherche sans résultat** : « Aucun résultat pour « … ». » + les mêmes trois liens.
- **Formulaire de contact envoyé** : encart de confirmation `--calcaire-creux`, filet double vertical
  `--sauge`, préfixe « **Message envoyé.** », pas de coche verte (la couleur seule ne dirait rien).

---

## 10. Micro-rédaction et vocabulaire de référence

> **Cette section est l'arbitre.** Quand deux chaînes nomment différemment la même chose, c'est ce
> tableau qui tranche — `/lead-mtb` et `doc-client-mtb` y renvoient. Un mot d'ici est employé
> **à l'identique** dans le site public, dans les écrans d'administration, dans les fiches d'aide et
> dans le guide de l'éleveuse.

### 10.1 La voix

- **Française, simple, active.** « Ajouter une portée », pas « Création d'une nouvelle entrée ».
- **Le vocabulaire de l'éleveuse**, jamais celui du développeur. Portée, saillie, cotation,
  confirmation, conducteur, affixe.
- **On ne vend pas.** Pas de superlatif, pas d'exclamation, pas de « nos merveilleux compagnons », pas
  d'emoji. Le brief interdit le registre mièvre et le registre startup.
- **On recopie les faits.** Un nom, une date, un résultat, une généalogie ne sont jamais reformulés.
- **Phrases courtes.** Une idée par phrase, pas de jargon anglais (« slider », « hero », « call to
  action » n'apparaissent nulle part, ni à l'écran ni dans l'aide).
- **Les libellés d'action commencent par un verbe à l'infinitif** : « Ajouter une photo »,
  « Protéger cette page », « Envoyer le message ».

### 10.2 Vocabulaire figé — modèle de contenu

| Notion | Libellé en administration | Libellé côté public | Valeurs / note |
|---|---|---|---|
| Portée | **Portée** | Portée | Type de contenu. Jamais « article », jamais « post ». |
| Identifiant de portée | **Identifiant de la portée** | (dans le titre) | Lettre(s) + chiffre facultatif + année. Ex. `A3 2025`, `L 1995`. |
| Date de naissance | **Date de naissance** | Née le | |
| Saillie | **Saillie** | Saillie | Champ de date. Jamais « accouplement », jamais « mating ». |
| Père | **Père** | Père | Lien vers une fiche Chien **ou** nom libre. |
| Père extérieur | **Père — étalon extérieur** | Père | Deux sous-champs : *Nom* et *Élevage*. |
| Mère | **Mère** | Mère | Idem. |
| Nombre de mâles | **Nombre de mâles** | mâles | |
| Nombre de femelles | **Nombre de femelles** | femelles | |
| Chiot | **Chiot** | Chiot | Sous-champs : *Nom*, *Sexe*, *N° LOF*, *Devenir*. |
| Devenir (d'un chiot) | **Devenir** | Devenir | Texte libre recopié. |
| Disponibilité | **Disponibilité** | (badge) | Liste fermée de trois : **Chiots disponibles** · **Tous réservés** · **Portée passée**. |
| Galerie photos | **Galerie photos** | (pas de titre) | |
| Commentaire | **Commentaire de l'éleveuse** | (pas de titre) | Le « texte libre » du brief §5.1. |
| Chien | **Chien** | Chien | Type de contenu. |
| Nom d'usage | **Nom d'usage** | (le `h1` de la fiche) | |
| Nom complet | **Nom complet (avec affixe)** | Nom complet | |
| Affixe | **Affixe** | (dans le nom complet) | « du Mont Brabant ». |
| Sexe | **Sexe** | Sexe | Liste fermée : **Mâle** · **Femelle**. |
| Variété | **Variété** | Variété | Liste fermée : **Poil long** · **Poil court**. |
| Date de décès | **Date de décès** | Décédé le / Décédée le | Accord selon le sexe. |
| Taille | **Taille** | Taille | |
| Couleur | **Couleur** | Couleur | Recopiée. |
| Masque | **Masque** | Masque | |
| Génétique de robe | **Génétique de robe** | Génétique de robe | Recopiée à l'identique. |
| Statut | **Statut** | Statut | Liste fermée : **Reproducteur** · **Retraité** · **Disparu** · **En cours de confirmation**. **L'affichage accorde au féminin** selon le champ Sexe : *Reproductrice*, *Retraitée*, *Disparue*. |
| Dysplasie des hanches | **Dysplasie des hanches (HD)** | Hanches (HD) | Valeur **recopiée**, jamais interprétée. |
| Dysplasie des coudes | **Dysplasie des coudes (ED)** | Coudes (ED) | Idem. |
| LTV | **LTV** | LTV | |
| DM | **DM** | DM | |
| SDCA 1 / SDCA 2 | **SDCA 1** / **SDCA 2** | SDCA 1 / SDCA 2 | |
| ADN identifié | **ADN identifié** | ADN identifié | |
| Diversité génétique | **Diversité génétique** | Diversité génétique | Exprimée en %. |
| Cotation | **Cotation LOF** | Cotation LOF | Valeur **recopiée telle quelle**. Voir §15, question ouverte. |
| Confirmation | **Confirmation** | Confirmation | |
| TC / CSAU | **TC** / **CSAU** | TC / CSAU | |
| N° LOF | **N° LOF** | N° LOF | Chiffres tabulaires, `overflow-wrap: break-word`. |
| Pedigree | **Lien pedigree (LOF Select)** | Voir le pedigree | Lien externe (§8.6). |
| Résultat de travail | **Résultat de travail** | Résultat | Type de contenu. |
| Discipline | **Discipline** | Discipline | Liste fermée : **RING** · **IGP / RCI** · **Mondioring** · **Obéissance** · **Pistage** · **Recherche utilitaire** · **Sauvetage** · **Truffe**. |
| Année | **Année** | Année | |
| Niveau | **Niveau ou titre obtenu** | Niveau | Recopié. |
| Conducteur | **Conducteur** | Conducteur | Personne qui mène le chien. Jamais « handler ». |
| Pays | **Pays** | Pays | Rempli seulement si le résultat est étranger. |
| Photo principale | **Photo principale** | — | Jamais « image mise en avant ». |
| Point d'intérêt | **Cadrage de la photo** | — | Liste fermée : *Haut gauche* · *Haut* · *Centre* · *Haut droite* · *Bas*. |
| Texte alternatif | **Description de la photo (pour les personnes aveugles)** | — | Jamais « alt », jamais « attribut alt ». |
| Adresse de la page | **Adresse de la page** | — | Jamais « slug », jamais « permalien ». |

### 10.3 Vocabulaire figé — interface

| Contexte | Libellé exact |
|---|---|
| Lien d'évitement | **Aller au contenu** |
| Bouton de menu mobile | **Menu** / **Fermer** |
| Vers une fiche portée | **Voir la portée** |
| Vers une fiche chien | **Voir la fiche** |
| Vers l'index des portées | **Toutes les portées** |
| Vers l'index des chiens | **La meute** |
| Navigation entre portées | **Portée précédente** / **Portée suivante** |
| Encart d'appel | **Nous contacter** |
| Bouton d'envoi du formulaire | **Envoyer le message** |
| Confirmation d'envoi | **Message envoyé.** |
| Erreur de champ | **Erreur : …** |
| Champ obligatoire | *(obligatoire)* dans l'étiquette |
| Donnée absente | **Non renseigné** |
| Galerie, compteur | **3 / 12** |
| Page protégée | **Page protégée** |
| 404 | **Page introuvable** |

### 10.4 Mots interdits à l'écran (site et administration)

`custom post type` · `CPT` · `métadonnée` · `meta` · `champ personnalisé` · `taxonomie` · `terme` ·
`slug` · `permalien` · `extrait` · `shortcode` · `bloc réutilisable` (dire « composant ») ·
`média` (dire « photo ») · `image mise en avant` (dire « photo principale ») · `alt` ·
`hero` · `slider` · `call to action` · `template` · `responsive` · `breakpoint`.

---

## 11. Autocritique

Passe obligatoire du brief §10. Menée après la rédaction du §1 au §10 ; les corrections décidées ici
sont **déjà intégrées** ci-dessus, et sont signalées comme telles.

### 11.1 « Aurais-je produit ceci pour n'importe quel site de campagne ou d'animaux ? »

**En partie oui, et c'est le point faible.** Sauge + calcaire + serif + un accent laiton est devenu, en
2020-2026, une famille en soi : vin nature, cosmétique végétale, épicerie fine. La palette seule ne
distingue pas ce site.

**Ce qui a été changé en conséquence** — la différenciation a été déplacée de la couleur vers **la
densité et la donnée** :

- Les fiches chien et portée sont composées comme des **pages de livre des origines** : listes de
  définition, étiquettes en petites capitales laiton, chiffres à chasse fixe, tableaux à filets et non
  à cartes (§7.5, §4.5). Aucun modèle « artisanal » ne fait cela — il montre des images et trois lignes
  de texte.
- L'échelle typographique a été durcie vers le haut (`--t-3xl` à 80 px) pour que les bandeaux photo
  soient de vraies pages de titre, pas des vignettes.
- Le `--rythme-section` a été rendu fluide plutôt que fixe, pour que les pages longues et reprises
  intégralement de l'ancien site respirent sans devenir des tunnels.

**Ce qui reste spécifique à cet élevage** et à aucun autre site : la triade de disponibilité à trois
signaux, la règle du voile prouvée sur photo blanche, le bandeau qui existe **sans** image, le tableau
qui se déplie en lignes libellées, le filet double.

### 11.2 Tics de « design d'IA » — passage en revue

| Tic | Verdict |
|---|---|
| Crème + serif + terre cuite | **Évité en partie.** Le calcaire et la serif y sont, la terre cuite non — le vert est *froid* et le seul métal est un laiton employé au trait. `--oxyde` est la seule couleur chaude vive, et elle est réservée aux erreurs de formulaire. **Choix délibéré, assumé par écrit.** |
| Noir + accent acidulé | Absent. |
| Look éditorial à filets fins | **Présent, et revendiqué.** C'est *la* signature, elle est unique, portée par un jeton, et sa liste d'emplacements est **close** (§2.1) — c'est ce qui la sépare d'un tic décoratif appliqué partout. |
| Cartes arrondies sur fond gris | **Éliminé.** Rayon maximal 2 px, réservé aux commandes ; aucune ombre portée sauf le panneau de menu ; pas de fond gris — cinq fonds nommés, tous chauds-neutres. |
| Dégradés, verre dépoli, violet | Absents. Le seul dégradé du système est le voile photo, qui est fonctionnel et prouvé. |
| Illustrations vectorielles génériques | Interdites (§13). Les seules formes dessinées sont la pastille, le chevron et le filet, toutes en CSS pur. |

### 11.3 L'audace est-elle unique et tenue partout ?

Oui, **après correction**. La première version gardait le filet double **et** les médaillons ronds : deux
dispositifs concurrents. Les médaillons ont été supprimés (§2.2) et la justification écrite. Il reste
une seule idée, appliquée à huit emplacements listés et interdite ailleurs.

### 11.4 Cela tient-il avec les vraies photographies de cet élevage ?

**C'est la faiblesse la plus honnête de ce document : `docs/migration/source/` est vide, je n'ai pas vu
les fichiers.** Tout ce qui est écrit au §6 est déduit du brief et de l'observation du site en ligne —
photos extérieures, lumière variable, cadrage amateur, portrait et paysage mêlés — et rédigé pour
survivre au pire cas : cerne obligatoire sur toute image, ratio imposé, point d'intérêt en liste fermée,
voile prouvé sur pixel blanc, aucun filtre correctif.

**Deux points à revérifier dès que les images sont importées** (`contenu-mtb`) :
1. Le ratio `21/9` du bandeau ≥ 64 rem est le plus risqué du système : si les photos disponibles sont
   majoritairement des portraits serrés, il faudra le ramener à `16/9`. **Cela ne change qu'un jeton.**
2. Le défaut `object-position: 50% 38%` est un pari sur des chiens en pied. À vérifier sur une vingtaine
   d'images ; s'il tombe mal, la valeur à corriger est unique.

### 11.5 Une non-designer peut-elle remplir ce système sans le casser ?

**Oui**, et c'est le point le plus solide du document. Ce qu'elle peut choisir : un texte, une photo, sa
description, un cadrage dans une liste de cinq, une valeur dans une liste fermée, la présence et l'ordre
des composants. Ce qu'elle ne peut pas atteindre : couleur, police, taille, espacement, alignement, CSS.
Chaque composant a un état vide défini (§9) et chaque état vide est **silencieux côté public**.

Le risque résiduel est ailleurs : **elle peut publier une photo mal cadrée**. Le système la rogne au lieu
de la déformer, ce qui est le moindre mal, mais aucun CSS ne rattrape une photo floue. La réponse est
dans le guide (`doc-client-mtb`), pas dans le design.

### 11.6 Ce qui sera difficile, et ce que je reverrais

1. **L'absence d'italique** (§4.3). C'est le compromis le plus visible du budget de deux fichiers.
   Impact concret sur la page Littérature. À rouvrir si le rendu est jugé mauvais — l'arbitrage
   alternatif est explicite, pas caché.
2. **La dépendance `data-libelle`** (§7.6). Un seul oubli côté rendu serveur rend un tableau illisible
   sur téléphone. `review-mtb` doit le vérifier ligne par ligne sur les deux composants concernés.
3. **Le risque de monotonie.** Trois encres, cinq fonds, aucune ombre, aucun arrondi : sur une page
   longue et sans photo (BHPL, Littérature, Mentions légales), la page peut paraître plate. Le rythme
   repose entièrement sur l'échelle typographique et le `--rythme-section`. Si cela ne suffit pas, la
   réponse **ne sera pas** d'ajouter une couleur : ce sera d'ajouter un bandeau photo ou une citation.
4. **Les valeurs de `size-adjust`** des polices de repli ne sont pas dans ce document parce que je ne
   peux pas les mesurer. Elles sont déléguées, pas oubliées (§4.2).
5. **Le pied de page sombre** est une prise de risque : c'est la seule grande surface `--pin` du site et
   elle a exigé deux jetons nouveaux. Si elle pèse trop dans la page, l'alternative est un pied de page
   `--calcaire-creux` — mais on perd le rythme clair/sombre qui compense l'abandon des médaillons.
6. **Le `21/9`** — voir §11.4.

---

## 12. Preuve d'accessibilité

Tous les ratios sont calculés selon la formule du §3. **Chaque paire que le système autorise figure
ici.** Une paire absente de ce tableau est une paire interdite : `dev-ux-mtb` qui en a besoin pose une
question bloquante.

### 12.1 Sur `--calcaire` `#F2F1EA` — fond de page

| Encre | Ratio | Verdict |
|---|---|---|
| `--pin` | **14,23:1** | AAA |
| `--texte` | **12,03:1** | AAA |
| `--sauge-fonce` | **7,73:1** | AAA |
| `--oxyde` | **6,74:1** | AA (AAA en grand) |
| `--texte-doux` | **6,47:1** | AA (AAA en grand) |
| `--laiton-texte` | **5,30:1** | AA |
| `--sauge` | **5,25:1** | AA — et ≥ 3:1 pour le rail de 3 px |
| `--laiton` | **3,51:1** | **non textuel** : filet bas, cerne, bordure d'étiquette |
| `--filet` | **1,41:1** | **décoratif seulement** — ne délimite jamais un contrôle |

### 12.2 Sur `--blanc` `#FBFAF6` — champ, carte

| Encre | Ratio | Verdict |
|---|---|---|
| `--pin` | **15,43:1** | AAA |
| `--texte` | **13,05:1** | AAA |
| `--sauge-fonce` | **8,38:1** | AAA |
| `--oxyde` | **7,31:1** | AAA |
| `--texte-doux` | **7,01:1** | AAA — c'est le bord de champ, ≥ 3:1 largement tenu |
| `--laiton-texte` | **5,74:1** | AA |
| `--laiton` | **3,81:1** | non textuel |

### 12.3 Sur `--calcaire-creux` `#E7E5DA` — encart, état vide, ligne survolée, bouton désactivé

| Encre | Ratio | Verdict |
|---|---|---|
| `--texte` | **10,78:1** | AAA |
| `--sauge-fonce` | **6,92:1** | AAA |
| `--oxyde` | **6,04:1** | AA |
| `--texte-doux` | **5,79:1** | AA — **libellé du bouton désactivé** |
| `--laiton` | **3,15:1** | non textuel (bordure du badge « Tous réservés ») |

### 12.4 Sur `--sauge` `#4A6B57` — bouton, badge « Chiots disponibles »

| Encre | Ratio | Verdict |
|---|---|---|
| `--calcaire` | **5,25:1** | AA |
| `--laiton-clair` | **3,76:1** | ≥ 3:1 — indicateur non textuel uniquement |

### 12.5 Sur `--pin` `#16241C` — pied de page, visionneuse, bande de texte sans photo

| Encre | Ratio | Verdict |
|---|---|---|
| `--blanc` | **15,43:1** | AAA |
| `--calcaire` | **14,23:1** | AAA |
| `--laiton-clair` | **10,20:1** | AAA — liens du pied de page |
| `--calcaire-ombre` | **8,43:1** | AAA — texte secondaire |
| `--texte-doux` | **2,20:1** | ❌ **INTERDIT** sur ce fond. Consigné pour qu'il ne soit jamais tenté. |

### 12.6 Sur `--pin-creux` `#24382C` — ligne survolée sur fond sombre

| Encre | Ratio | Verdict |
|---|---|---|
| `--calcaire` | **11,06:1** | AAA |
| `--laiton-clair` | **7,92:1** | AAA |
| `--calcaire-ombre` | **6,55:1** | AA |

`--pin-creux` contre `--pin` : **1,29:1**. Le survol sur fond sombre **doit donc porter un second
signal** (trait `--laiton-clair` de 2 px). C'est déjà la règle du §8, cette mesure l'impose.

### 12.7 Texte sur photographie

Voir §6.4 : **5,65:1** au pire point de la zone de texte et **9,18:1** en bas, calculés sur un pixel
blanc pur. Aucune photographie ne peut faire échouer le bandeau.

### 12.8 Anneau de focus

Voir §8.1 : **≥ 3,77:1** garantis sur n'importe quel fond, y compris une photographie — démonstration
par la borne inférieure du couple `--calcaire` / `--pin`.

### 12.9 Indépendance à la couleur

| Information | Signal 1 | Signal 2 | Signal 3 |
|---|---|---|---|
| Disponibilité d'une portée | libellé écrit | forme de la pastille (plein / demi / vide) | couleur |
| Page courante du menu | trait 3 px sauge | graisse 600 | `aria-current` |
| Survol du menu | trait 2 px laiton | fond creusé | — |
| Focus | anneau double | (jamais un simple changement de couleur) | — |
| Lien dans le texte | **soulignement permanent** | couleur | — |
| Erreur de formulaire | mot « Erreur : » | bordure 2 px | `aria-invalid` |
| Bouton désactivé | libellé changé | `aria-disabled` | fond creusé |
| Ligne survolée d'un tableau | fond creusé | — (une ligne survolée ne porte aucune information) | — |

### 12.10 Le reste de la liste bloquante (brief §11)

| Exigence | Où elle est traitée |
|---|---|
| Un seul `<h1>` par page, hiérarchie logique | §7.3, §7.4, §7.5 |
| Parcours clavier complet | §8.1, visionneuse §6.7 |
| Lien d'évitement | §7.3, §10.3 |
| `alt` utile sur chaque photo | §6.5, §10.2 (libellé du champ) |
| Cibles ≥ 44 px, écart ≥ `--e-2` | §5.1, §6.7, §8.3, §8.4 |
| Aucune information par la couleur seule | §12.9 |
| Zoom 200 % sans perte | §7.8, §4.4 |
| 360 px sans défilement horizontal | §7.7 |
| `lang="fr"` | prérequis de `hyphens: auto`, §4.5 |
| Rien ne dépend du survol | §6.7, §8 — le survol n'est jamais le seul accès à une fonction |

---

## 13. Interdits

Un point de cette liste dans le code livré est une non-conformité que `review-mtb` doit remonter.

**Structure et outillage**
- Aucun page builder, aucun thème du dépôt, aucun cadre CSS (Bootstrap, Tailwind, Bulma…).
- Aucune requête navigateur vers un domaine tiers : polices, icônes, scripts, cartes, images.
- Aucune police d'icônes, aucun sprite SVG distant. Les trois formes du site (pastille, chevron,
  filet) sont en CSS pur.
- Aucune valeur brute hors de `tokens.css` : pas de `#`, pas de `px` de couleur ou d'espacement écrit
  en dur dans une feuille de composant.

**Visuel**
- `border-radius` > 2 px, où que ce soit. `border-radius: 50%` sur une image.
- Toute ombre portée, sauf `--ombre-panneau` sur le menu mobile.
- Tout dégradé, sauf `--filet-double` et `--voile-photo`.
- Verre dépoli, flou d'arrière-plan, duotone, `mix-blend-mode`.
- `filter:` sur une photographie de chien.
- Une couleur hors des quinze jetons du §3.1.
- Un fond hors des cinq du §3.2.
- Une paire encre/fond absente du §12.
- Un état signalé par la couleur seule.
- Une taille exprimée en `vw` seul.
- Une largeur fixe supérieure à 300 px.
- Du texte superposé à une image ailleurs qu'au bandeau d'ouverture.
- Une majuscule + interlettre sur plus de trois mots.
- Un pictogramme de remplacement, une silhouette de chien, une illustration générique.
- L'italique comme dispositif de design (§4.3).
- Le filet double deux fois dans le même bloc, ou hors de la liste close du §2.1.

**Interaction**
- Une fonction accessible **uniquement** au survol.
- Un menu caché par défaut dans le HTML et révélé par CSS ou JS (§7.3).
- Un en-tête collant.
- Un `outline: none` sans remplacement conforme au §8.1.
- Une animation d'apparition au défilement, un parallaxe, un carrousel automatique.
- Un `placeholder` tenant lieu d'étiquette de champ.

**Éditrice**
- Toute couleur, police, taille, espacement ou alignement réglable depuis l'éditeur (§14).
- Un composant qui rend une page cassée quand il est mal rempli.
- Un terme technique à l'écran (§10.4).

---

## 14. Ce qui est verrouillé, ce qui appartient à l'éditrice

| L'éditrice décide | L'éditrice ne peut pas atteindre |
|---|---|
| Le texte d'un titre, d'un paragraphe, d'une accroche | Une couleur, quelle qu'elle soit |
| Le choix d'une photo et sa description | Une famille, une taille, une graisse de police |
| Le cadrage d'une photo, dans une liste de cinq valeurs | Un espacement, une marge, un remplissage |
| Une valeur dans une liste fermée (disponibilité, statut, variété, discipline, sexe) | Un alignement (gauche / centre / droite) |
| La présence, l'absence et l'ordre des composants d'une page | La largeur d'une colonne, une grille personnalisée |
| Combien d'éléments affiche une liste | Un CSS personnalisé, une classe personnalisée |
| Le mot de passe d'une page protégée | Un dégradé, une ombre, un arrondi, un duotone |
| Le menu (entrées et ordre) | Le rendu d'un composant |

**Conséquence pour `theme.json`**, à appliquer par `dev-front-mtb` :
`settings.color.custom: false`, `customGradient: false`, `customDuotone: false`, `defaultPalette: false`,
`defaultGradients: false`, `link: false`, `background: false`, `text: false` au niveau des blocs ;
`settings.typography.customFontSize: false`, `fontStyle: false`, `fontWeight: false`,
`letterSpacing: false`, `textDecoration: false`, `dropCap: false` ;
`settings.spacing.customSpacingSize: false`, `padding: false`, `margin: false` ;
`settings.layout` verrouillé sur `--l-texte` / `--l-large` ;
`appearanceTools` **non** activé globalement. La palette exposée à l'éditrice est **vide** : elle ne
choisit aucune couleur.

---

## 15. Questions remontées

Aucune n'est inventée, aucune n'est comblée en silence (D11).

| # | Question | Bloque | Qui tranche |
|---|---|---|---|
| D1 | **Cotation LOF** — le brief demande le champ mais ne donne pas la grille officielle des valeurs. Je **ne l'invente pas**. Le champ est donc livré en **texte recopié**. Faut-il une liste fermée, et avec quels libellés exacts ? | l'écran de saisie d'une fiche chien | l'éleveuse |
| D2 | **Dysplasie (HD / ED)** — même situation : valeur recopiée telle quelle, pas de liste fermée A/B/C/D/E tant que la grille n'est pas confirmée. | idem | l'éleveuse |
| D3 | **Disciplines** — le brief §5.3 annonce « ~7 disciplines » puis en énumère **huit**. J'ai retenu les huit énumérées (§10.2). À confirmer. | la page Travail | l'éleveuse |
| D4 | **Statut au féminin** — j'ai décidé que l'affichage accorde le statut au sexe du chien (*Reproductrice*, *Retraitée*, *Disparue*). Décision de rédaction prise faute d'indication ; à confirmer, elle a un coût de développement. | la fiche chien | l'utilisateur |
| D5 | **Les photographies n'ont pas encore été importées** (`docs/migration/source/` est vide). Les ratios du §6.1 et le point d'intérêt par défaut du §6.2 sont à revérifier après reprise. | rien — les jetons sont uniques et modifiables | `contenu-mtb`, puis révision de ce document |
| D6 | **Valeurs de `size-adjust`** des polices de repli — à mesurer sur les fichiers sous-ensemblés, pas à inventer ici. | rien | `dev-ux-mtb` |
| D7 | **Le logo actuel du site** doit-il être repris comme image dans l'en-tête, ou l'en-tête se compose-t-il en typographie seule ? Le §7.3 prévoit le second cas par défaut, qui est plus robuste et plus léger. | l'en-tête | l'éleveuse |
| D8 | **Le renvoi à la ligne de la prose dans la toile de l'éditeur** — le §7.7.2 impose de porter la règle par un sélecteur racine, ce qui ne demande aucune inspection. Reste le cas où le cœur intercepterait l'héritage dans la toile, comme il le fait déjà pour le libellé de navigation à (0,2,0). Il faudrait alors un crochet nommé dans `editor.css` : **le sélecteur ne se devine pas, il s'inspecte**. Mesure à faire dans la toile, sur une page libre contenant l'URL de 67 signes, en 360 px puis 320 px. | la fidélité de l'écran d'édition, pas le site public | `dev-ux-mtb`, par la mesure |

---

## 16. Journal des révisions

| Date | Version | Ce qui change | Motif |
|---|---|---|---|
| 2026-08-28 | 1.1 | **§7.7 seul.** `overflow-wrap: anywhere` devient la valeur par défaut, portée par un sélecteur racine ; `break-word` n'est conservé que sur `th`/`td`, avec sa raison. Les trois mesures qui l'imposent sont écrites dans le document (§7.7.1), ainsi que le fait normatif qui les explique. Le §7.7.2 tranche la valeur de la règle côté éditeur et nomme le préalable d'inspection (D8). Rien d'autre n'est rouvert : palette, typographie, espacement, photographie et vocabulaire inchangés ; le §4.5 reste vrai tel quel. | Trois emplacements livrés contournaient la règle 1.0 avec la même mesure ; débordement à 360 px et 320 px sur de la prose reprise, D7 bloquante |
| 2026-08-15 | 1.0 | Document initial. Palette et ratios repris de `styles/style5` et recalculés indépendamment ; trois jetons ajoutés (`--pin-creux`, `--calcaire-ombre`, `--oxyde`) ; filet double conservé et réinterprété (vide de 2 px) ; **médaillons ronds abandonnés** ; structure IONOS entièrement remplacée par une grille à trois canaux ; deux polices variables auto-hébergées (Newsreader, Public Sans). | `docs/ETAT.md` décision 8 |
