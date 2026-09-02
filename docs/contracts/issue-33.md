# Contrat d'interface — Issue #33 — Hisser `.mtb-dispo`, `.mtb-photo` et l'`object-position` en variable dans une feuille partagée

Gelé au lot 16. Base : `origin/main` à `92f3202` (`MASTER.md` rév. 1.5).
Dette servie : **T18**. Ligne de DoD : **§14 — D2**.

**Cette issue ne touche que le thème.** Aucune ligne de `wp-content/plugins/` ne change ; il n'y a donc
pas de plan `leaddev-back-mtb` à réconcilier. Ce document n'est pas un contrat front↔back : c'est le
**gel des décisions** qui gouvernent l'écriture, et la liste des vérifications qui conditionnent son
acceptation.

---

## 1. Empreinte — exhaustive et close

**Commit 1** — `base.css`, `fiches.css`, `blocs/mtb-derniere-portee.css`, `blocs/mtb-liste-portees.css`,
plus leurs quatre `*.min.css`.
**Commit 2** — `base.css`, `blocs/mtb-bandeau-ouverture.css`, `blocs/mtb-grille-chiens.css`,
`blocs/mtb-liste-portees.css`, `blocs/mtb-coordonnees-plan.css`, plus leurs cinq `*.min.css`.

**Rien d'autre.** Ni `functions.php`, ni `tokens.css`, ni `editor.css`, ni un gabarit PHP, ni un fichier
de l'extension, ni `design-system/MASTER.md`.

**Point à porter à la revue, pour qu'elle ne le lise pas comme un débordement** : l'empreinte de
l'**issue** ne nomme que `blocs/*.css` et `base.css`. `fiches.css` est ajouté **délibérément** par le
lead, parce qu'il porte le **troisième exemplaire** des deux primitives et la **septième**
`object-position`. Sans lui, l'issue laisserait derrière elle la copie qu'elle prétend supprimer.

---

## 2. Le recensement — l'énoncé était périmé, et la tâche 1 était du travail réel

L'issue et `docs/ETAT.md:1349` annoncent **cinq** `object-position`. Il y en a **sept**. `fiches.css:206`
se déclarait déjà lui-même « SIXIÈME ÉCRITURE » : le dépôt savait que le chiffre était faux.

| Motif | Exemplaires | Ancres |
|---|---|---|
| `.mtb-dispo` nue | **3** | `blocs/mtb-derniere-portee.css:91` · `blocs/mtb-liste-portees.css:286` · `fiches.css:120` |
| `.mtb-photo` nue | **3** | `blocs/mtb-derniere-portee.css:165` · `blocs/mtb-liste-portees.css:178` · `fiches.css:183` |
| `object-position: var(--point-interet, 50% 38%)` | **7** | `bandeau-ouverture.css:411` · `derniere-portee.css:202` · `fiche-information.css:97` · `galerie-photos.css:241` · `grille-chiens.css:319` · `liste-portees.css:221` · `fiches.css:217` |

**Neuf porteurs** émettent `mtb-photo` ou `mtb-dispo`, tous vérifiés dans le code :

| # | Ancre | Classes émises |
|---|---|---|
| 1 | `blocks/bandeau-ouverture/render.php:150` | `mtb-bandeau-ouverture__photo mtb-photo` |
| 2 | `blocks/derniere-portee/render.php:159` | `mtb-photo mtb-derniere-portee__photo` |
| 3 | `blocks/liste-portees/rendu.php:273` | `mtb-liste-portees__vignette mtb-photo` |
| 4 | `single-mtb_chien.php:173` | `mtb-fiche-chien__portrait --vide mtb-photo` |
| 5 | `single-mtb_chien.php:182` | `mtb-fiche-chien__portrait mtb-photo` |
| 6 | `blocks/derniere-portee/render.php:105` | `mtb-dispo mtb-dispo--<etat>` — **aucune classe d'élément** |
| 7 | `blocks/liste-portees/rendu.php:393` | `mtb-liste-portees__dispo mtb-dispo mtb-dispo--<etat>` |
| 8 | `single-mtb_portee.php:100` | `mtb-fiche-portee__dispo mtb-dispo mtb-dispo--<etat>` |
| 9 | `single-mtb_chien.php:383` | `mtb-carte-portee__dispo mtb-dispo mtb-dispo--<etat>` |

Le porteur **n° 6 n'a aucune classe d'élément** : l'ancêtre est le seul crochet possible pour ce badge.
C'est ce qui rend obligatoire la forme du §5 ci-dessous.

---

## 3. Le domicile : `base.css` §13

**§13, en toute fin de `base.css`, après le §12.** Le fichier porte déjà un §11 (« Les liens de
recours », l. 940) et un §12 (« La prose reprise de l'ancien site », l. 965) : la section neuve est la
**treizième**, pas la onzième. Le fichier prescrit lui-même l'ajout en fin de fichier trois fois
(`:546`, `:692`, `:733`).

**Pourquoi `base.css` et pas une feuille neuve** — quatre raisons, aucune n'est une préférence :
1. **Décision 30, gelée à `docs/contracts/issue-16.md:519-520`** : « une primitive nommée par
   `MASTER.md` s'écrit en classe nue, une seule fois → **dans `base.css`** ». `.mtb-dispo::before` et
   `.mtb-photo > img` sont **littéralement écrites** par MASTER (§3.3 l. 211, §6.2 l. 496).
2. **Le précédent du §10** (`.mtb-tableau`) est le même cas à la virgule près : un consommateur qui
   n'est le rendu d'aucun bloc, donc qu'aucune feuille de composant ne couvre.
3. **La toile de l'éditeur** : `base.css` est déjà dans `add_editor_style()`. Une feuille neuve n'y
   serait pas.
4. Une feuille neuve exigerait de rouvrir `functions.php` (**hors empreinte**, décision 9) et ferait
   passer `make css-check` de **15 à 16 paires** — l'attendu documenté sauterait.

---

## 4. Les dix règles hissées — sélecteurs et déclarations finales

Ordre : badge, puis photo.

| # | Sélecteur | Déclarations finales |
|---|---|---|
| 13.1 | `.mtb-dispo` | `display: inline-flex` · `align-items: center` · **`gap: var(--e-1)`** · **`padding: var(--e-1) var(--e-2)`** · `border-radius: var(--r-1)` · **`font-family: var(--sans)`** · `font-weight: 700` · `font-size: var(--t-sm)` · `line-height: 1.2` · `text-transform: uppercase` · `letter-spacing: .12em` · **`white-space: normal`** |
| 13.2 | `.mtb-dispo::before` | `content: ""` · `flex: none` · `inline-size: .6em` · `block-size: .6em` · `border: 2px solid currentColor` · `border-radius: 50%` |
| 13.3 | `.mtb-dispo--disponible` | `background-color: var(--sauge)` · `color: var(--calcaire)` |
| 13.4 | `.mtb-dispo--disponible::before` | `background-color: currentColor` |
| 13.5 | `.mtb-dispo--reserve` | `background-color: var(--calcaire-creux)` · `color: var(--texte)` · `border: 1px solid var(--laiton)` |
| 13.6 | `.mtb-dispo--reserve::before` | `background-image: linear-gradient(to right, currentColor 50%, transparent 50%)` |
| 13.7 | `.mtb-dispo--passee` | `background-color: var(--calcaire)` · `color: var(--texte-doux)` · `border: var(--bord-actif)` |
| 13.8 | `.mtb-photo` | `position: relative` · `background-color: var(--calcaire-creux)` · `color: var(--texte-doux)` · `font-size: var(--t-sm)` |
| 13.9 | `.mtb-photo::after` | `content: ""` · `position: absolute` · `inset: 0` · **`border-radius: inherit`** · `box-shadow: var(--cerne-photo)` · `pointer-events: none` |
| 13.10 | `.mtb-photo.mtb-photo > img` | `display: block` · `inline-size: 100%` · `block-size: 100%` · `object-fit: cover` · `object-position: var(--point-interet, 50% 38%)` |

### 4.1 Les cinq points de forme, avec leur raison — à écrire dans le commentaire, jamais nus

**(a) `gap: var(--e-1)` et `padding: var(--e-1) var(--e-2)` — `MASTER.md` §5.1.1, révision 1.5.**
Ce n'est pas la valeur majoritaire : **c'est la minoritaire**, celle qu'écrivait déjà
`mtb-liste-portees.css`. Les deux autres feuilles s'y alignent. Le vote par fréquence était un
**artefact** : `fiches.css:117-119` déclare avoir recopié `mtb-derniere-portee.css:96` pour rester
littéralement identique — il n'y avait donc pas trois écritures indépendantes mais **une proposition
écrite deux fois contre une autre**, et trancher par la majorité aurait reconduit exactement le défaut
que #33 répare.

Citer **§5.1.1 et la révision 1.5**, pas seulement les jetons. Les quatre appuis du document :
- l'écart pastille ↔ libellé est un « **ajustement de pastille** » (`--e-1`) et **non** un « écart entre
  deux cibles tactiles » (`--e-2`), le badge n'étant cliquable nulle part (§3.3) ;
- la pastille est un `::before` de l'élément **qui porte le texte**, héritant de `currentColor` pour que
  son contraste **soit** celui du libellé : elle se lit comme un signe **du** mot, pas comme un objet
  **à côté du** mot ;
- à `--t-sm`, l'interlettre de `.12em` valant ≈ 1,8 px, `--e-1` place la pastille à **2,2 interlettres**
  du premier caractère — attachée et distincte — quand `--e-2` l'en écarte de **4,4** et la détache ;
- le badge partage déjà la **typographie** du bouton (§4.5) et son **rayon** `--r-1` (§5.2) : s'il en
  prenait aussi la **boîte**, la seule distinction restante serait le **curseur**, signal de survol que
  le §12.10 refuse comme unique. D'où une boîte de **26 px** face aux **≥ 48 px** du bouton (§8.4).

**Deux points sont non compensés à dessein — ne pas les « réparer », ce ne sont pas des oublis** :
l'interlettre de `.12em` court **après la dernière capitale**, donc le rembourrage droit vaut
optiquement ≈ 9,9 px contre 8 px à gauche (**≈ 1,9 px d'asymétrie**) ; le document décide de ne rien
compenser, précisément pour qu'aucune chaîne n'invente une marge négative. Et **`--e-2` est retenu ici
hors de la règle des cibles tactiles** : c'est un rembourrage, pas une application de cette règle. Les
44 px du §12.10 ne concernent pas le badge, et sa hauteur de 26 px **n'est pas une non-conformité**.

**(b) Le doublage de `.mtb-photo.mtb-photo > img` est CONSERVÉ (13.10).** Une fois la règle dans
`base.css`, elle et le `img` de `base.css:124` sont préfixées **ensemble** par
`.editor-styles-wrapper` : (0,3,1) contre (0,1,1) dans la toile, (0,2,1) contre (0,0,1) sur le site.
Dé-doubler en `.mtb-photo > img` donnerait (0,1,1) contre (0,1,1) dans la toile — **égalité tranchée par
l'ordre**. Le doublage coûte 11 octets et ferme le sujet.

**(c) `border-radius: inherit` est RETENU (13.9).** Présent seulement dans `liste-portees.css:199`.
Ce n'est **pas un arbitrage de valeur** : superset **inerte** sur un cadre droit (`--r-0` vaut `0`),
justifié par `liste-portees.css:191-194` et corroboré indépendamment par `bandeau-ouverture.css:384-388`,
qui déclare l'avoir écrit « pour que le jour de la mise en commun la déduplication soit une
**suppression et jamais un arbitrage** ».

**(d) `white-space: normal` est écrit dans la primitive, et la preuve d'accessibilité voyage avec.**
`normal` est la valeur initiale du CSS : la déclarer est une **garde d'héritage**, `white-space` étant
héritée. Deux feuilles prescrivent par écrit de retenir `normal` (`derniere-portee.css:111`,
`liste-portees.css:303`). **La mesure D7 de `liste-portees.css:298-303` doit être transportée mot pour
mot dans le commentaire du §13** — « `nowrap` mesuré à 360 px, `scrollWidth` 400 pour `clientWidth` 360,
défilement horizontal, échec AA ». Elle ne doit pas disparaître avec la règle qui la portait.

**(e) `font-family: var(--sans)` est dans la primitive.** MASTER §4.5 l. 334 impose Public Sans au
badge, et `--sans` vaut bien Public Sans (`tokens.css:26`, vérifié). Une primitive hissée **ne doit pas
dépendre d'un ancêtre `body`** (`base.css:104`) qui n'est pas garanti dans toutes les toiles.

### 4.2 L'en-tête du §13 — quatre paragraphes obligatoires

1. **Pourquoi cette primitive vit ici** : décision 30, `docs/contracts/issue-16.md:519-520`, et le
   précédent du §10.
2. **Le paragraphe déplacé de `fiches.css:96-114`**, requalifié en « pourquoi cette primitive vit ici ».
   Voir §7 pour son traitement exact.
3. **La preuve D7** du `white-space` (point d).
4. **La table de cascade** : les trois contextes, les neuf porteurs, le crochet survivant, et l'énoncé
   *aucune règle du §13 ne dépend de l'ordre d'enfilage*.

Plus la **liste close des littéraux** (`.6em`, `2px`, `50%`, le dégradé, `50% 38%`, `1px`, `700`, `1.2`,
`.12em`), sur le modèle du §10 : aucune valeur brute nouvelle n'est introduite.

---

## 5. Le seul crochet survivant — et pourquoi il doit peser (0,3,0)

**`gap` et `padding` ne sont plus scopés nulle part** : MASTER les porte désormais, ils vivent dans la
primitive. Les deux règles que le plan prévoyait pour `liste-portees` et `fiches` **ne sont pas écrites**.

| Feuille | Sélecteur final | Poids | Déclarations |
|---|---|---|---|
| `blocs/mtb-derniere-portee.css` (règle l. 115, **sélecteur doublé**) | `.mtb-derniere-portee.mtb-derniere-portee .mtb-dispo` | **(0,3,0)** | `white-space: nowrap` |

**Le piège que le hissage crée, et que ce doublage ferme.** Après hissage, l'adversaire de ce crochet
n'est plus rien : c'est `.mtb-dispo` **dans `base.css`**, qui porte `white-space: normal`.

| Contexte | §13 `.mtb-dispo` | crochet non doublé | Verdict |
|---|---|---|---|
| Site | `.mtb-dispo` → (0,1,0) | `.mtb-derniere-portee .mtb-dispo` → (0,2,0) | crochet gagne |
| **Toile** | `.editor-styles-wrapper .mtb-dispo` → **(0,2,0)** | `.mtb-derniere-portee .mtb-dispo` → **(0,2,0)** | **ÉGALITÉ → l'ordre tranche** |

Le préfixage ajoute **une classe** à `base.css` et **pas** aux feuilles de blocs. La convention « deux
classes minimum » de `derniere-portee.css:11-15` a été écrite contre un `h2` — (0,0,1) préfixé en
(0,1,1) — et **cesse d'être suffisante dès que `base.css` porte un sélecteur de CLASSE sur le même
élément**. C'est le défaut même que l'issue existe pour fermer, qui serait **réintroduit par le
hissage**. Le dépôt documente déjà le remède pour cette raison exacte : `base.css:761-763`, et
`base.css:900-903` qui écrit « aucun sélecteur doublé nécessaire, **contrairement à une feuille de
bloc** ».

**`fiches.css:299-303` (`.mtb-fiche-portee__dispo, .mtb-carte-portee__dispo`) n'est pas touché** : ses
deux déclarations (`inline-size`, `max-inline-size`) sont **disjointes** du §13, donc aucun ordre ne
peut mordre. Ne pas le préfixer par cosmétique.

---

## 6. `object-position` : sept écritures deviennent quatre, et la justification est en DEUX temps

**Ne pas fusionner les quatre restantes.** La justification doit être écrite en deux temps, sinon la
revue la re-litige :
- **trois par disjonction d'élément** — `fiche-information.css:97`, `galerie-photos.css:241`,
  `grille-chiens.css:319` : aucun de ces éléments ne porte `mtb-photo` (grep exhaustif sur
  `wp-content/**/*.php`) ;
- **une par spécificité et valeurs identiques** — `bandeau-ouverture.css:411` **est** atteinte par
  13.10, puisque `render.php:150` pose `mtb-photo` sur le cadre parent. Elle perd la cascade et **c'est
  sans effet, les cinq valeurs étant littéralement identiques**.

`bandeau-ouverture.css:187-197` refuse explicitement de dépendre de `.mtb-photo` ; cette autonomie est
**maintenue**. Sa raison écrite (« `.mtb-photo` ne vit que dans les feuilles #12 et #13, servies
seulement si l'un de ces blocs rend ») **devient périmée le jour du hissage** — `base.css` est servie
partout. À rendre en dette, **pas à corriger ici** : ce serait une refonte de trois feuilles de blocs.

**Aucun jeton `--cadrage-photo`.** Une propriété personnalisée est substituée **sur l'élément où elle
est déclarée**. Déclarée sur `:root`, où `--point-interet` n'existe pas, elle se **figerait** à
`50% 38%`, et cette valeur figée serait héritée par tous les descendants : les **cinq cadrages de la
liste fermée du §6.2** (`fiches.css:500-503`, `grille-chiens.css:345-348`,
`fiche-information.css:117-133`) cesseraient de fonctionner **en silence**, sur le seul réglage
photographique laissé à l'éleveuse. `tokens.css` est de toute façon hors empreinte.

---

## 7. Les dettes de texte

### 7.1 T99 — six phrases, sept lignes, huit numéraux

**« composants du catalogue » = 10, toujours vrai. « feuilles de blocs » = 11 depuis le lot 15**, la
onzième habillant le formulaire de contact, qui n'est pas un composant du catalogue.

| Ancre | Texte | Correction |
|---|---|---|
| `base.css:792` | « des **dix** feuilles de composants » | onze |
| `bandeau-ouverture.css:149` | « **neuf** feuilles sœurs » | dix |
| `bandeau-ouverture.css:159` | « Les **neuf** sœurs héritent » | dix |
| `bandeau-ouverture.css:178` | « pas **dix** » (fragments) | onze |
| `bandeau-ouverture.css:179` | « recopiés dans **dix** feuilles de blocs » | onze |
| `bandeau-ouverture.css:186` | « ni ses **neuf** sœurs » | dix |
| `grille-chiens.css:83` | « pas **dix** règles recopiées dans **dix** feuilles de blocs » | onze / onze |

**INTERDICTION ABSOLUE de toucher ces phrases, qui comptent des COMPOSANTS et sont vraies** :
`bandeau-ouverture.css:9`, `:26`, `:33`, `:139`, `:185` · `base.css:756`, `:772` ·
`grille-chiens.css:28`, `:86`, `:87` · `galerie-photos.css:29` · `fiche-information.css:145` ·
`coordonnees-plan.css:7` (« ces onze règles » = règles de la feuille, sans rapport).
**Aucun `sed` sur « dix » ni sur « neuf ».** `editor.css` est hors empreinte, et sa l. 79 cite MASTER
§9.1 mot pour mot.

### 7.2 T18 — deux chiffres périmés, à écrire au chiffre FINAL (quatre)

- `coordonnees-plan.css:131` : « la déclaration existe déjà **cinq** fois (T18) » → **quatre**, avec
  mention de #33.
- `grille-chiens.css:290-293` : « en **cinq** exemplaires », plus une énumération de quatre feuilles
  **elle-même fausse** — il manquait `galerie-photos.css` et `fiches.css`. Réécrire : **sept avant #33,
  quatre après**, et énumérer les quatre survivantes (`bandeau-ouverture`, `fiche-information`,
  `galerie-photos`, `grille-chiens`).

### 7.3 Trois renvois périmés — trois traitements DIFFÉRENTS, à ne pas uniformiser

Une **poignée renommée** n'est pas une **cible supprimée**, et ni l'un ni l'autre n'est une **dérive de
numéros de ligne**.

- **`fiches.css:104` — poignée renommée. → COMMIT 1.** `mtb-galerie-photos-style` est devenue
  `mtb-bloc-mtb-galerie-photos` au lot 15. **Ne pas réécrire le nom** : c'est un **relevé de mesure**
  (« RELEVÉ SUR UNE FICHE CHIEN RENDUE »), et le réécrire **antidaterait une observation que personne
  n'a faite**. **Dater le relevé** et **noter le renommage**. Le fait porteur à protéger est
  l'**absence** de `mtb-derniere-portee` et `mtb-liste-portees` dans le `head` d'une fiche : c'est
  l'argument le plus fort en faveur du hissage. Ce paragraphe part dans l'en-tête du §13 avec les règles
  qu'il explique — il appartient au **commit 1**, et non au 2 : l'y recopier en le sachant périmé
  rendrait le commit 1 sciemment faux.
- **`liste-portees.css:89` — cible supprimée, renvoi vivant à côté.** « Forme reprise de
  `galerie.css:73`, **et désormais partagée avec `mtb-grille-chiens.css §1`, dont l'en-tête porte
  l'argumentation complète** » → supprimer le renvoi mort, **garder le vivant**.
- **`grille-chiens.css:129` — cible supprimée, renvoi d'origine.** « Forme reprise telle quelle de
  `galerie.css:73`. » → **dater l'origine** sans pointer un fichier mort.

**INTERDIT : ne jamais repointer ces deux-là vers `mtb-galerie-photos.css:73`.** Le lot 15 a déplacé le
fichier ; rien ne garantit que la l. 73 y porte la même règle — ce serait **fabriquer une dérive de
numéros (T88)**.

### 7.4 Deux paragraphes périmés que le §13 contredirait — correction bornée, sous condition

`bandeau-ouverture.css:32-44` (« CETTE FEUILLE N'ATTEINT PAS L'IFRAME DE L'ÉDITEUR », dette T11) et
`liste-portees.css:39-45` (T-#13-c) affirment au **présent** que les feuilles de blocs n'atteignent pas
la toile. C'était vrai ; **c'est périmé depuis #6**. `bandeau-ouverture.css:41-44` prescrit même un
« correctif d'UNE LIGNE » **qui a été appliqué**.

Toute l'analyse de cascade du §13 repose sur la prémisse inverse : la laisser contredite **dans sa
propre empreinte** garantit que la revue la re-litige.

**Autorisé, borné à une phrase par paragraphe, et sous condition stricte** : la correction est une
**note de solde datée et attribuée** — jamais une affirmation fraîche. Elle ne s'écrit que si le dépôt
la porte : `functions.php:230-236` (l'enregistrement en administration), `grille-chiens.css:29` (« #6
enregistre désormais `mtb-jetons` en administration ») et `liste-portees.css:45` (qui écrit déjà
« Solde : #6 »). **Si ces trois appuis ne sont pas retrouvés, ne rien écrire et le rendre en dette.**
**Ne pas toucher « commune aux dix composants » de `bandeau-ouverture.css:33`** — phrase vraie, §7.1.

### 7.5 `liste-portees.css:8` — à corriger avec précision, pas à supprimer

« Chaque octet d'ici, **commentaires compris**, est payé à chaque vue. » Périmé depuis #40 : c'est
l'artefact minifié qui est servi, et `mtb_min_depouiller()` en retire les commentaires. **Mais la phrase
redevient vraie dès que l'artefact est périmé**, le thème retombant alors sur la source. Écrire cette
précision exacte — c'est la formulation la plus utile, puisqu'elle dit **pourquoi `make css` est un
verrou et non une formalité**.

### 7.6 À ne PAS « réparer »

`fiches.css:206-207` (« SIXIÈME ÉCRITURE… dette T18 AGGRAVÉE ») est un compte périmé **supprimé par le
commit 1**, porté par la règle déplacée. Personne ne doit le corriger ensuite.

---

## 8. Ce qui change d'apparence — nommé, jamais tu

**La tâche 5 de l'énoncé (« vérifier visuellement qu'aucun bloc ne change d'apparence ») est fausse
telle qu'écrite.** Trois choses changent, toutes voulues.

**(1) Deux badges sur trois changent de boîte — c'est le but, et c'est MASTER 1.5 qui le décide.**

| Porteur | `gap` | `padding` | Effet |
|---|---|---|---|
| `derniere-portee` (accueil) | `--e-2` → **`--e-1`** | `--e-2 --e-3` → **`--e-1 --e-2`** | **change** : 8→4 px d'écart, 8/12→4/8 px |
| `fiches` (fiche portée, carte portée d'une fiche chien) | `--e-2` → **`--e-1`** | `--e-2 --e-3` → **`--e-1 --e-2`** | **change**, idem |
| `liste-portees` (index) | `--e-1` | `-inline: --e-2` / `-block: --e-1` ≡ `--e-1 --e-2` | **inchangé**, au pixel |

C'est très exactement la promesse de l'issue — « la disponibilité d'un chiot aura la même apparence
partout » — et elle ne pouvait pas être tenue en laissant les valeurs scopées.

**Corollaire à noter : la mesure `nowrap` de `derniere-portee.css:117-121` reste valide et devient
conservatrice.** Elle relevait un badge de 238 px dans un canal de 324 px à 360 px, **avec l'ancien
rembourrage** ; le nouveau retire 8 px horizontaux. Le badge ne peut que rétrécir : le `nowrap` scopé
reste sûr, avec plus de marge qu'à la mesure. Ne pas rejouer la mesure, **écrire qu'elle est un
majorant**.

**(2) `.mtb-photo` ne change nulle part.** Les cinq déclarations de 13.10 battent désormais
`.mtb-bandeau-ouverture__image` (0,1,0) sur le site — **les cinq valeurs sont identiques, zéro pixel**.
`color` et `font-size` de `__image` ne sont pas dans le §13 et survivent.

**(3) Un changement est attendu DANS LA TOILE, sur le seul bandeau d'ouverture, et il corrige un
défaut.** `bandeau-ouverture.css:404-405` justifie `__image` par « (0,1,0) contre (0,0,1) à `img` » **en
oubliant le préfixage** : le `img` de `base.css:124-129` y pèse (0,1,1) et bat (0,1,0). Le bandeau perd
donc **aujourd'hui** son `block-size: 100%` dans la toile, et sa photo dicte la hauteur du cadre. Le §13
apporte du (0,3,1) et ferme ce défaut latent. L'aperçu de la toile **est** le rendu du serveur
(`bandeau-ouverture/editeur.js:14, 18, 33`, `wp.serverSideRender`), donc `mtb-photo` y est bien présente.
**Déduit du modèle de préfixage, NON MESURÉ** — à écrire comme tel.

---

## 9. Interdits

1. **Ne jamais émettre `mtb-photo` sur `.mtb-coordonnees-plan__cadre`.** Le `cover` hissé (0,2,1) et le
   `contain` délibéré de `coordonnees-plan.css:138` (0,2,1) seraient à **égalité** ; l'ordre déciderait,
   le plan serait rogné et **le point d'arrivée coupé**. Vérifié : le rendu ne l'émet pas aujourd'hui.
2. Ne pas fusionner les quatre `object-position` restantes (§6).
3. Ne pas repointer un renvoi vers `mtb-galerie-photos.css:73` (§7.3).
4. Ne pas réécrire la poignée relevée à `fiches.css:104` (§7.3).
5. Aucun `!important`, aucune valeur brute nouvelle.
6. Aucun `sed` sur « dix » / « neuf » (§7.1).
7. Aucun fichier hors empreinte (§1). **`MASTER.md` appartient au lead et vient d'être commité : ne pas
   l'ouvrir.**
8. Ne pas égaliser la boîte des trois états de disponibilité (§11, dette rendue).

---

## 10. Vérifications — ce qui conditionne l'acceptation

**Personne dans ce projet n'a jamais rendu ce site dans un navigateur. Aucune étape n'en demande un, et
aucune vérification non tenue ne sera déclarée tenue.**

- **V1 — Identité d'ENSEMBLE de déclarations, jamais de textes.** Pour chacune des dix règles, extraire
  l'ensemble `{propriété: valeur}` des trois exemplaires, normaliser (fins de ligne, espaces, ordre des
  déclarations, ordre des sélecteurs), comparer les **ensembles**. Motif : `liste-portees.css:334-338`
  et `derniere-portee.css:142-146` écrivent les mêmes trois déclarations dans un **ordre différent** —
  un `diff` textuel produirait un faux positif. Attendu : ensembles identiques pour huit règles ; pour
  `.mtb-photo::after`, `liste-portees` = superset d'exactement `border-radius: inherit` ; pour
  `.mtb-dispo`, écart exactement `{gap, padding*, font-family, white-space}` et **rien d'autre**.
- **V2 — BLOQUANTE AVANT TOUTE ÉCRITURE. Le bandeau, déclaration par déclaration.**
  `.mtb-bandeau-ouverture__photo` (règle ouverte l. 301) contre `.mtb-photo` : quatre propriétés partagées
  (`position`, `background-color`, `color`, `font-size`), quatre valeurs. Son `::after` (l. 392-399)
  contre 13.9 : six propriétés. `__image` contre 13.10 : cinq propriétés partagées. **Si une seule
  valeur diverge, arrêt et remontée au lead** — le bandeau changerait d'apparence sur **toutes** les
  pages du site.
- **V3 — Table de cascade, neuf porteurs × trois contextes** : (a) gabarit de blocs (`base.css` **avant**
  les feuilles de blocs), (b) gabarit de fiche (`base.css` **après**), (c) toile préfixée (`base.css` +1
  classe, feuilles de blocs non préfixées). Gagnant identique dans les trois, **sauf** la ligne du
  bandeau nommée au §8(3). Le crochet du §5 se vérifie par (0,3,0) > (0,2,0).
- **V4 — Disjonction des jeux de propriétés** de `.mtb-liste-portees__vignette`,
  `.mtb-derniere-portee__photo`, `.mtb-fiche-chien__portrait`, `.mtb-fiche-chien__portrait--vide`,
  `.mtb-fiche-portee__dispo, .mtb-carte-portee__dispo`, rejouée sur le code livré.
- **V5 — `git diff --stat` VIDE** sur `wp-content/plugins/` et sur `wp-content/themes/mtb/*.php`. Aucune
  classe émise ne change : c'est ce qui rend V3 **suffisante**.
- **V6 — Bilan d'octets par composition de page**, mesuré sur les `*.min.css` réellement servis, pour
  cinq compositions : accueil, `/portees/`, fiche chien, fiche portée, **page sans photo ni badge**.
- **V7 — `make css-check` = 0 et 15 paires « à jour », APRÈS CHACUN des deux commits.** Le compte reste
  **15** : aucune feuille créée ni supprimée.
- **V8 — Aucun `!important` ni valeur brute introduits.**
- **V9 — LE TEST QUI PROUVE QUE LA DETTE EST PAYÉE.** `grep -n '^\.mtb-dispo\|^\.mtb-photo'` sur les
  treize feuilles → **exactement une occurrence de chaque, dans `base.css`**. Et la seule déclaration
  résiduelle est le `white-space: nowrap` du §5, sur un crochet qui ne matche qu'un composant et qui
  gagne **sans dépendre de l'ordre**.

### Ce qui n'est PAS démontrable — à déclarer, jamais à cocher

- Que le badge **ressemble** à ce que §3.3 décrit, et que sa nouvelle boîte est juste à l'œil.
- Que le cerne est **visible**. La mesure pixel est d'une chaîne antérieure
  (`derniere-portee.css:172-182`) ; elle est **déplacée sans être rejouée**.
- Que la correction du bandeau **dans la toile** rend correctement — **déduite, non mesurée**.
- Que le préfixage `.editor-styles-wrapper` s'applique à `base.css` et non aux feuilles de blocs :
  prémisse du dépôt, confirmée cinq fois (`derniere-portee:194`, `liste-portees:210`,
  `coordonnees-plan:135`, `galerie-photos:237`, `base.css:900-903`). **Reprise, non re-mesurée.**

---

## 11. Arbitrages

| Question | Décision | Raison |
|---|---|---|
| `gap` du badge | **`var(--e-1)`** | `MASTER.md` §5.1.1, rév. 1.5. La minoritaire l'emporte : le vote par fréquence était un artefact de recopie (`fiches.css:117-119`). |
| `padding` du badge | **`var(--e-1) var(--e-2)`** | Idem. **Remonté au lead plutôt que tranché en chaîne** : MASTER était muet, et graver une valeur non portée aurait reconduit le défaut réparé. Le lead a ouvert une révision bornée. |
| `font-family` | **`var(--sans)` dans la primitive** | §4.5 l. 334 impose Public Sans ; `--sans` = Public Sans (`tokens.css:26`). Tranché en chaîne : **il découle du document**. Une primitive hissée ne dépend pas d'un `body` non garanti. |
| `white-space` | **`normal` dans la primitive, `nowrap` scopé à (0,3,0)** | Garde d'héritage + preuve D7. Le doublage ferme un piège de cascade **créé par le hissage lui-même**. |
| `border-radius: inherit` | **retenu** | Superset inerte, pas un arbitrage. Deux feuilles le prescrivent. |
| Domicile | **`base.css` §13** | Décision 30 gelée, précédent du §10, toile gratuite, `functions.php` intact, 15 paires préservées. |
| Feuille neuve | **rejetée** | Rouvrirait `functions.php` (décision 9), 16 paires, deux domiciles concurrents. |
| Jeton `--cadrage-photo` | **rejeté** | Piège de substitution : casserait les cinq cadrages **en silence** (§6). |
| Les 4 `object-position` restantes | **conservées** | Trois par disjonction d'élément, une par spécificité. Autonomie assumée et argumentée. |
| Découpage en deux commits | **retenu** | Un commit mêlant hissage et vingt commentaires rend le diff illisible pour la revue. |
| `.mtb-liste-portees__dispo` crochet mort | **laissé tel quel** | Aucune règle ne le cible ; le corriger est une décision de balisage hors de cette issue. **Rendu en dette.** |

---

## 12. Ce que l'éleveuse gagne ou perd

**Rien qu'elle ait à apprendre** — aucun champ, aucun réglage, aucun bloc, aucune composition, aucun
gabarit. **`doc-client-mtb` n'a rien à livrer** ; le guide reste à sa bijection 122/122.

Deux choses changent sous ses yeux sans qu'elle ait rien à faire : le badge de disponibilité prend
**partout** la même boîte, un peu plus compacte sur l'accueil et sur les fiches ; et dans l'éditeur, la
photo du **bandeau d'ouverture** cessera d'imposer sa hauteur au cadre — l'aperçu, aujourd'hui faux dans
la toile, devient juste.
