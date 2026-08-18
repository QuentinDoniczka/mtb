# Contrat d'interface — Issue #10 — Composant Encart d'appel

**Gelé le 2026-08-18** par la chaîne `lead-issue-mtb` de l'issue #10, après réconciliation des plans
`leaddev-back-mtb` et `leaddev-front-mtb`, écrits en aveugle l'un de l'autre.

Contraignant à partir d'ici. Les deux devs implémentent **ce document**, pas leur plan d'origine
lorsque les deux divergent. Les divergences et leur arbitrage sont consignés en §9.

Bloc concerné : `mtb/encart-appel` — « **Encart d'appel** ».
Empreinte d'écriture de la chaîne, close :

- `wp-content/plugins/mtb-core/includes/blocks/encart-appel/**` (dossier neuf)
- `wp-content/themes/mtb/assets/css/blocs/mtb-encart-appel.css` (fichier neuf)
- `docs/guide/composant-encart-appel.md` (fichier neuf)
- `docs/contracts/issue-10.md` (ce fichier)

Rien d'autre. En particulier : ni `theme.json`, ni `functions.php`, ni `tokens.css`, ni `base.css`,
ni `editor.css`, ni `includes/blocks/categorie-mtb/`, ni aucun autre dossier de `includes/blocks/`.

---

## 1. Fonctions de lecture exposées par l'extension

**Aucune.** Ce module n'expose ni fonction `mtb_get_*`, ni `interface.php`, ni filtre. Il n'affiche
aucune donnée d'élevage — ni portée, ni chien, ni résultat — mais la saisie de l'éditrice. Son contrat
est le nom du bloc et son balisage, rien d'autre.

Le thème n'appelle donc **aucun** `mtb_get_*` pour cette issue, et n'interroge jamais la base.

### 1.1 Fonctions *consommées*, déclarées par personne — signatures gelées

Deux fonctions sont appelées sous `function_exists()`. **Ni l'extension ni le thème ne les déclarent
dans cette chaîne** (décision 19 : le chargeur parcourt par `scandir()`, deux homonymes s'ombrent en
silence sur un site qui répond 200 ; l'issue sœur #11 travaille sur la même donnée au même moment).
La garde est donc fausse aujourd'hui et l'on retombe sur le repli : **c'est le comportement voulu, pas
une panne.**

| Fonction attendue | Signature exigée | Effet le jour où elle existe |
|---|---|---|
| `mtb_get_telephone_elevage()` | **zéro paramètre requis** ; retourne `?string`, ou l'enveloppe de la décision 18 dont la clé `valeur` est une `string` | devient la source d'autorité du numéro ; la constante du module cesse d'être lue, sur **toutes** les instances à la fois, sans rouvrir une page |
| `mtb_get_page_contact()` | **zéro paramètre requis** ; retourne `?int` = **identifiant de page**, jamais une URL | devient la cible par défaut du bouton, sur toutes les instances non surchargées |

`mtb_get_page_contact()` rend un **identifiant** et non une adresse : le libellé du bouton est le titre
de la page (§5), donc le module a besoin du contenu, pas seulement de son URL.

**Trois exigences fermes, à faire respecter par l'issue qui les déclarera :**

1. **Zéro argument requis.** Un paramètre obligatoire produirait un `ArgumentCountError` fatal derrière
   la garde `function_exists()`, sur **toute page portant un encart**.
2. Le retour est traité **défensivement** par le module : jamais d'`Array to string conversion`, jamais
   de `TypeError`, jamais d'écran blanc. Toute forme inattendue est traitée comme « rien ».
3. `mtb_get_telephone_elevage()` peut rendre l'une **ou** l'autre des deux formes ; le module accepte
   les deux. **Cette ambiguïté ne doit pas survivre à l'issue « Coordonnées de l'élevage »**, qui doit
   trancher une forme unique et l'écrire à son contrat.

### 1.2 Les deux chaînes de résolution — ordre gelé

**Téléphone**, dans cet ordre, le premier cran non vide gagne :

1. attribut d'instance `telephone`, `trim()` non vide → **il gagne, tel quel** ;
2. `mtb_get_telephone_elevage()` sous `function_exists()` ;
3. constante d'espace de noms du module `TELEPHONE_ELEVAGE = '0680505619'` — **cas d'aujourd'hui**.

**Page cible**, même ordre : attribut `page_id` > 0 → `mtb_get_page_contact()` sous `function_exists()`
→ `0`.

**Le numéro par défaut n'est JAMAIS un `default` de `block.json`.** Un attribut laissé à sa valeur par
défaut n'est pas sérialisé dans le commentaire de bloc ; un défaut figé dans la définition serait donc
mort dès l'insertion et gelé sur chaque page déjà enregistrée. Résolu au rendu, il reste vivant et la
bascule vers la source centrale est gratuite et rétroactive. **C'est le cœur de l'approche retenue.**

### 1.3 Validation de la page cible — les quatre conditions

Un identifiant, d'où qu'il vienne, n'est retenu que si **les quatre** sont vraies :

| Condition | Pourquoi |
|---|---|
| `'page' === get_post_type( $id )` | une portée ou un chien n'est pas une cible d'encart d'appel |
| `'publish' === get_post_status( $id )` | brouillon, corbeille, privé → 404 pour le visiteur = **bouton mort**, D12 |
| `'' === get_post_field( 'post_password', $id )` | on n'envoie pas un appel à l'action vers un mur de mot de passe ; `get_the_title()` y préfixerait de surcroît « Protégé : » sur le libellé du bouton |
| `'' !== get_permalink( $id )` **et** `'' !== trim( get_the_title( $id ) )` | pas d'adresse ou pas de libellé → pas de bouton |

Une seule condition fausse → **pas de bouton**, et **rien d'autre ne bouge** (§9.4 de `MASTER.md`,
précédent littéral : « *celle sans fiche n'a simplement pas de bouton* »). **Ce n'est pas un état vide.**

---

## 2. Blocs enregistrés

`mtb/encart-appel` — titre « **Encart d'appel** », `"category": "mtb"` (décision 25 ; la catégorie est
livrée une seule fois par `includes/blocks/categorie-mtb/`, hors empreinte, **aucun index central à
éditer**, décision 9).

| | |
|---|---|
| `apiVersion` | `3` |
| `icon` | `phone` (dashicon du cœur, aucun fichier, aucune requête) |
| `description` | « Un encadré « Nous contacter » avec le téléphone de l'élevage et un bouton vers la page de votre choix. Le numéro s'affiche tout seul : il n'y a rien à recopier. » |
| `keywords` | `contact`, `contacter`, `téléphone`, `telephone`, `appel`, `bouton` |
| `render` | `file:./render.php` |
| `editorScript` | `mtb-encart-appel-editeur` |
| `save` | `null` — rendu serveur intégral ; extension désactivée = **aucun orphelin HTML** dans le `post_content` |
| `example` | **absent** — un `example` sur un bloc à rendu serveur déclenche un rendu REST à chaque survol dans l'insérteur |
| `style` / `editorStyle` | **absents** — le CSS vit dans le thème (décision 28, contrat #1 §8). **T15 n'est pas reproduite.** |

### 2.1 Attributs — liste close, tous plats, aucun visuel

| Attribut | Type | Défaut `block.json` | Résolu au rendu |
|---|---|---|---|
| `accroche` | `string` | `""` | non — vide = pas de phrase |
| `telephone` | `string` | `""` | **oui**, §1.2 |
| `page_id` | `number` | `0` | **oui**, §1.2 |

**Aucun attribut de niveau de titre.** Le titre est un `<h2>` figé (§9, arbitrage 5).

### 2.2 `supports` — recopie littérale de `fiche-information/block.json`

Tout à `false` : `color` (fond, texte, lien, dégradés, bouton, vérificateur de contraste),
`typography` (`fontSize`, `lineHeight`, `textAlign`), `spacing` (`margin`, `padding`, `blockGap`),
`dimensions`, `background`, `filter.duotone`, `align`, `alignWide`, `anchor`, `ariaLabel`,
`className`, `customClassName`, `html`, `lock`, `renaming`, `reusable`, `splitting`, `layout`,
`position`, `border`, `shadow`.

`"inserter": true` · **`"multiple": true`** — plusieurs encarts par page sont autorisés (§9, arbitrage 7).

`className: false` a une conséquence à connaître : **aucune classe `wp-block-mtb-encart-appel` n'est
émise**. Le CSS vise `.mtb-encart-appel`, jamais `wp-block-*`.

---

## 3. Balisage rendu — gelé, au caractère près

### 3.1 Cas complet

```html
<div class="mtb-encart-appel">
  <h2 class="mtb-encart-appel__titre">Nous contacter</h2>
  <p class="mtb-encart-appel__accroche">…</p>
  <div class="mtb-encart-appel__actions">
    <a class="mtb-encart-appel__telephone" href="tel:0680505619">06 80 50 56 19</a>
    <a class="wp-element-button" href="https://…/contact/">Contact</a>
  </div>
</div>
```

### 3.2 Les six crochets, et ce qui casse si l'un manque

| Crochet | Élément | Présent quand | Ce qui casse s'il manque |
|---|---|---|---|
| `mtb-encart-appel` | `<div>` racine | dès que le bloc rend | surface `--calcaire-creux`, **filet double vertical** (§2.1, liste close), rembourrage, rythme de section — tout disparaît. L'encart devient du texte nu dans le flux |
| `mtb-encart-appel__titre` | le `<h2>` | toujours | le `h2` garde le filet double de `base.css:201-216` → **deux filets dans le même bloc visuel**, interdit par §2.1 ; plus 48 px de marge haute et 22 px de rembourrage bas orphelins |
| `mtb-encart-appel__accroche` | `<p>` | accroche non vide | rien de visuel — **aucune règle ne le cible** (§4). Crochet demandé pour la lisibilité du balisage et pour `review-mtb` |
| `mtb-encart-appel__actions` | `<div>` | **dès que le bloc rend** (voir §3.4) | téléphone et bouton se collent, l'écart minimal entre deux cibles tactiles tombe, et **le repli à 360 px n'a plus de mécanisme** |
| `mtb-encart-appel__telephone` | `<a>` **ou** `<span>` (§3.3) | un numéro est résolu | **D7 tombe** : le lien retombe à ~28 px de haut. C'est le piège nommé de cette issue |
| `wp-element-button` | `<a>` du bouton | une cible est retenue | le bouton retombe sur les styles globaux du cœur — `#32373c` sur `#fff`, gélule de 9999 px, **deux couleurs hors des quinze jetons : T7 rouverte** |

### 3.3 Le téléphone — un seul crochet, deux éléments possibles

Le `href` n'est composé que si le numéro est composable : chiffres extraits par
`preg_replace( '/[^0-9]/', '', $valeur )`, longueur **entre 4 et 15** (borne E.164 — une saisie du
genre « 06 80 50 56 19 ou 04 94 … » donnerait vingt chiffres, donc un `href` faux ; mieux vaut pas de
lien qu'un mauvais numéro composé).

- **`href` composable** → `<a class="mtb-encart-appel__telephone" href="tel:…">`
- **`href` non composable** → `<span class="mtb-encart-appel__telephone">`, **même classe**

Le numéro reste alors du texte lisible, sélectionnable, jamais un `href` vide (D12). Le style de lien
de `base.css:248-260` (encre `--sauge-fonce`, soulignement `--laiton`) ne s'applique qu'à `a` : un
`span` n'est donc pas souligné, ce qui est correct — **ce n'est pas un lien**.

### 3.4 Présence de `__actions`

Le bloc ne rend quelque chose que s'il a **au moins une action** (§6). `__actions` porte donc toujours
**un ou deux** enfants et **n'est jamais vide**. Il est présent chaque fois que le bloc rend.

### 3.5 Les cinq variations, toutes à habiller

| Cas | Rendu public |
|---|---|
| tout rempli | titre + accroche + téléphone + bouton |
| **accroche absente** | titre puis actions — l'écart est porté par le `margin-block-end` du **titre**, jamais par une marge haute des actions : rien d'orphelin |
| **bouton absent** (§9.4) | titre + accroche + téléphone seul — `__actions` reste un conteneur flex à un enfant, `gap` ne s'exprime qu'**entre** deux enfants : aucun vide résiduel, aucune largeur réservée |
| **téléphone absent** | titre + accroche + bouton seul — symétrique |
| **ni téléphone ni cible** | **rien du tout** (décision 26) — voir §6 |

### 3.6 Garanties fermes du balisage

- **Racine : `<div>`**, sans `<section>`, sans `role`, sans `aria-labelledby`. Précédent gelé au
  contrat #7 §3 : un repère nommé par composant multiplie les *landmark regions* sur une page qui se
  navigue déjà par ses titres, et deux encarts produiraient deux repères homonymes.
- **L'ordre du DOM est l'ordre visuel et l'ordre de lecture** : téléphone d'abord, bouton ensuite.
  **Aucune propriété `order`, aucun `flex-direction: …-reverse` ne doit le contredire.**
- Le `<h2>` est **fixe, sans réglage de niveau**, et ne porte ni `id` ni `aria-labelledby`.
- **Aucun attribut `data-*`.** La présence ou l'absence d'un élément est le seul signal — la
  décision 10 (`data-libelle`) ne s'applique pas : aucun tableau ici.
- **Dans l'éditeur, le balisage est identique**, imbriqué d'un niveau supplémentaire dans le conteneur
  de `useBlockProps()` produit par `ServerSideRender`.
- Interdits côté serveur : aucun `style=`, aucune valeur de couleur / taille / espacement / ratio,
  aucun conteneur vide, aucun `<hr>`, aucun `<img>`, aucun SVG, **aucun pictogramme de téléphone**
  (§13 de `MASTER.md` : aucune police d'icônes, aucun sprite distant), **aucune MAJUSCULE littérale**
  (les capitales sont posées en CSS).
- Le bouton est un `<a>` simple rendu par `render.php`, **jamais un bloc Bouton du cœur en
  `InnerBlocks`** — ce dernier rouvrirait la variation « Contour », les réglages de largeur, et
  rendrait la présence du bouton non gouvernable par D12.

---

## 4. Répartition du CSS — décision 30 appliquée au bouton

> Test du contrat #12 §10.4a : *la déclaration décrit-elle **ce qu'est** l'élément, ou **la place dont
> il dispose ici** ?*

| Déclaration | Ce qu'elle décrit | Verdict |
|---|---|---|
| fond `--sauge`, libellé `--calcaire` 700 majuscules `.12em`, `--r-1`, ≥ 48 px, survol `--pin` + cerne `--laiton`, actif `translateY(1px)`, focus = anneau §8.1 | **ce qu'est** un bouton du site | **primitive** — déjà écrite une seule fois, `base.css:625-691`, en classe doublée `(0,2,0)` |
| position dans la rangée, écart au lien téléphone, comportement au repli | **la place dont il dispose ici** | **appartient au bloc** — portée par `.mtb-encart-appel__actions` |

**Conclusion gelée : le bouton est une primitive du site, pas l'affaire de ce bloc.** La feuille
`mtb-encart-appel.css` n'écrit **aucune** règle le concernant, et le serveur n'émet **aucune classe
propre au bloc sur son `<a>`** — un crochet que rien ne style est du poids mort dans le HTML. Si une
surcharge de placement devenait nécessaire, le sélecteur `.mtb-encart-appel__actions > .wp-element-button`
existe **sans aucune modification de balisage**.

### 4.1 La feuille — cinq règles, ordre de source porteur

Fichier : `wp-content/themes/mtb/assets/css/blocs/mtb-encart-appel.css`, **nom au caractère près** :
la boucle de `functions.php:194-249` dérive `mtb/encart-appel` → `assets/css/blocs/mtb-encart-appel.css`
et sert la feuille par `wp_enqueue_block_style()` si le fichier existe. **Rien à déclarer nulle part**
(décision 28) ; la poignée est `mtb-bloc-mtb-encart-appel`, la dépendance `mtb-jetons`.

1. **`.mtb-encart-appel`** — `margin-block: max(var(--rythme-section), var(--e-7))` ·
   `background-color: var(--calcaire-creux)` · `background-image: var(--filet-double-v)` ·
   `background-repeat: no-repeat` · `background-position: left top` ·
   `background-size: var(--filet-double-h) 100%` · `padding: var(--e-6)` ·
   `padding-inline-start: calc(var(--e-6) + var(--filet-double-h))`.
   Forme du filet vertical reprise **littéralement** de `base.css:266-277` (`blockquote`).
   N'écrit ni `border-radius` (`--r-0` est le défaut), ni `border-left` (le filet est un dégradé — le
   vide de 2 px **est** la signature, une bordure la détruirait), ni `display: flex/grid/flow-root`
   (la racine reste en flux, les marges viennent de `base.css`), ni `overflow: hidden` (§7.8 l'interdit
   sur un conteneur de texte, et il rognerait l'anneau de focus).
2. **`.mtb-encart-appel .mtb-encart-appel__titre`** — `background-image: none` ·
   `padding-block-end: 0` · `margin-block: 0 var(--e-4)`. Retire le **second** filet (§2.1 en interdit
   deux par bloc visuel). Précédent exact : `mtb-derniere-portee.css:38-42`.
3. **`.mtb-encart-appel__accroche`** — **aucune règle.** Typographie du corps, écart bas porté par le
   `margin-block-end` de `p`. Écrire ici serait une seconde source de vérité.
4. **`.mtb-encart-appel__actions`** — `display: flex` · `flex-wrap: wrap` · `align-items: center` ·
   `gap: var(--e-4)`. **Aucun `order`, aucun `row-reverse`, aucun `justify-content`.** `flex-wrap`
   est **tout le mécanisme du repli à 360 px**, sans une seule requête média.
5. **`.mtb-encart-appel .mtb-encart-appel__telephone`** — `display: inline-flex` ·
   `align-items: center` · `min-block-size: 48px` · `overflow-wrap: break-word`.
   Aucune couleur, aucun soulignement, aucun `font-*` : le lien `tel:` est un lien de contenu au sens
   du §8.2 et reçoit tout de `base.css:248-260`.
6. **`.mtb-encart-appel.mtb-encart-appel > :last-child { margin-block-end: 0 }`** — **écrite en
   dernier, à ne pas déplacer.** Classe **doublée** à dessein : à `(0,1,0)` elle perdrait dans la toile
   de l'éditeur contre le `p` de `base.css:240`, qui y pèse `(0,1,1)` une fois préfixé.

### 4.2 Table de spécificité — à recopier dans l'en-tête de la feuille

| Concurrent (`base.css`) | Poids site / toile | Sélecteur retenu | Poids |
|---|---|---|---|
| `h2` (l. 201) | (0,0,1) / (0,1,1) | `.mtb-encart-appel .mtb-encart-appel__titre` | (0,2,0) ✔ |
| `a` (l. 248) | (0,0,1) / (0,1,1) | `.mtb-encart-appel .mtb-encart-appel__telephone` | (0,2,0) ✔ |
| `p` (l. 240) | (0,0,1) / (0,1,1) | `.mtb-encart-appel.mtb-encart-appel > :last-child` | (0,2,0) ✔ |
| — | — | `.mtb-encart-appel`, `.mtb-encart-appel__actions` | (0,1,0), aucun concurrent ✔ |

**`48px` est la seule valeur brute autorisée dans la feuille.** Précédent et justification déjà écrits
dans `base.css:366-369` : c'est un minimum d'accessibilité écrit en pixels par `MASTER.md` (§8.4 pour
le bouton, §8.5 pour le champ), aucun jeton ne le porte, et il est interdit d'en créer un.

---

## 5. Chaînes fournies par le serveur

Le thème les **imprime**, il ne les compose jamais.

| Chaîne | Origine | Valeur |
|---|---|---|
| Titre de l'encart | **figé**, `MASTER.md` §10.3 | « Nous contacter » |
| Numéro affiché | donnée, BRIEF §7 | `06 80 50 56 19` par défaut — voir §5.1 |
| `href` du téléphone | dérivé, sans invention | `tel:0680505619` — **forme nationale, aucun `+33`** |
| Libellé du bouton | **titre de la page choisie**, recopié | ex. « Contact » |
| Accroche | saisie de l'éditrice | telle que tapée |
| Phrase d'état vide | figée, §6 | voir §6 |

**Apostrophe typographique `’` (U+2019)** dans toutes les chaînes destinées à un humain, comme
`derniere-portee`. (`fiche-information` emploie l'apostrophe droite : divergence existante, relevant
de T13, **non propagée ici**.)

### 5.1 Affichage du numéro — la seule mise en forme autorisée

Le numéro s'affiche **tel qu'il est stocké**, à une exception : si la valeur stockée est **exactement
dix chiffres et rien d'autre**, le serveur la groupe par paires — `implode( ' ', str_split( $valeur, 2 ) )`.
Les chiffres et leur ordre sont intacts : c'est un rendu, pas une réécriture. **Toute autre forme
stockée s'imprime verbatim.**

**Fait de domaine, recopié à la lettre** (BRIEF §7) : le numéro de l'élevage est **`0680505619`**. La
constante du module porte exactement cette chaîne, aucune autre forme n'existe dans le dépôt.

### 5.2 Interdits de composition — le thème ne fait JAMAIS cela

- Écrire `0680505619` — ni en HTML, ni en CSS, ni dans un `content:`.
- Reformater le numéro. `letter-spacing` sur les chiffres est permis ; réordonner, ponctuer ou ajouter
  un indicatif ne l'est pas.
- Composer « Nous contacter », le libellé du bouton, ou un préfixe « Téléphone : ». **Le serveur
  n'émet aujourd'hui aucun libellé devant le numéro** ; s'il en faut un visible un jour, il vient du
  serveur.
- Interroger la base, ou décider ce qu'est « la page Contact ».
- Masquer un élément par `display: none` : si un élément est là, c'est qu'il a du contenu ; s'il n'a
  pas de contenu, il n'est pas là.
- Habiller l'état vide (§6).

---

## 6. États spéciaux

| État | Émis par le serveur | Rendu par le thème |
|---|---|---|
| `complet` | titre + accroche + `__actions` à deux enfants | nominal, §4.1 |
| `sans_accroche` | le `<p class="__accroche">` **n'est pas émis** — jamais un `<p>` vide | l'écart est porté par le `margin-block-end` du titre : rien d'orphelin, même valeur avec ou sans accroche |
| `sans_bouton` — **§9.4, contenu à moitié rempli** | l'`<a class="wp-element-button">` **n'est pas émis**. Ni `href="#"`, ni `aria-disabled`, ni bouton grisé : **jamais de bouton mort** (D12) | `__actions` reste un flex à un enfant ; `gap` ne s'exprime qu'entre deux enfants → aucun vide résiduel. **Aucun chrome, aucune note, aucun cadre tireté** |
| `sans_telephone` | le `<a\|span class="__telephone">` n'est pas émis | symétrique du précédent |
| `telephone_non_composable` | `<span class="__telephone">` au lieu de `<a>`, §3.3 | même boîte, même cible ; pas de soulignement, car ce n'est pas un lien |
| `page_indisponible` | l'une des quatre conditions du §1.3 est fausse → traité exactement comme `sans_bouton` | idem `sans_bouton` |
| `aucune_action` — **le seul état vide réel** | **rien du tout au visiteur** (décision 26) | dans l'éditeur seulement : le cadre `.mtb-etat-vide`, §6.1 |

**`sans_bouton` n'est PAS un état vide.** C'est `MASTER.md` §9.4, dont le précédent est déjà écrit :
« *Deux parents dont un seul a une fiche : les deux cartes gardent la même taille ; celle sans fiche
n'a simplement pas de bouton.* » Le noter est nécessaire, sinon `review-mtb` le remontera comme
manquant et **T13 gagnera une cinquième convention**.

### 6.1 L'état vide — forme de référence, paiement de la dette T13

Atteint dans l'éditeur seul, par `EmptyResponsePlaceholder` de `ServerSideRender`, quand la réponse du
rendu serveur est vide. **Jamais rendu par le serveur.** Le PHP ne connaît **qu'une seule garde** :
les deux résolutions vides → `return;`. **Aucune détection de contexte** (`is_admin()`, `REST_REQUEST`,
`is_admin_bar_showing()`), exactement comme `derniere-portee`.

```html
<div class="mtb-etat-vide">
  <span class="mtb-etat-vide__nom">Encart d'appel</span>
  <p class="mtb-etat-vide__phrase">Ce bloc n'affiche rien tant qu'aucun numéro de téléphone ni aucune page de contact ne sont indiqués.</p>
</div>
```

Cinq exigences, toutes bloquantes :

1. **Un `span`, pas un `p`**, pour `__nom`. `editor.css:142-145` a été écrit **pour un `span`**
   (`display: block` posé comme mécanique) et **ne remet pas à zéro les marges d'un `p`**.
2. **Aucune classe modificatrice** — ni `mtb-etat-vide--encart-appel`, ni `mtb-encart-appel__vide`.
3. Le cadre est émis **seul, NON enveloppé dans `.mtb-encart-appel`**. Sinon on empile une surface
   `--calcaire-creux` dans une surface `--calcaire-creux`, avec un filet double par-dessus le cadre
   tireté — et §9.1 exige **une seule apparence pour les dix composants**.
4. Nom du composant en **casse naturelle** (« Encart d'appel ») : les capitales sont posées par
   `text-transform` dans `editor.css`, **jamais tapées**.
5. **La feuille `mtb-encart-appel.css` n'écrit aucune règle d'état vide**, ni sous ces crochets ni
   sous d'autres. L'apparence est écrite une fois pour toutes dans `editor.css:126-168`, hors empreinte.

**Trois divergences constatées chez les sœurs, que ce module ne reproduit pas** (état des lieux de
T13) : `fiche-information/editeur.js:107` émet `mtb-etat-vide mtb-fiche-information__vide` avec deux
`<p>` ; `derniere-portee/editeur.js:33` émet `mtb-etat-vide mtb-etat-vide--derniere-portee` avec deux
`<p>` ; `grille-chiens/balisage.php:310` émet l'état vide **depuis le PHP**. **Ce module écrit la forme
de référence.**

**Réserve honnête, à connaître avant la revue** (§9, arbitrage 11) : tant que
`mtb_get_telephone_elevage()` n'existe pas, un champ Téléphone vidé retombe sur la constante — il y a
**toujours** un numéro, donc `aucune_action` est **structurellement inatteignable dans la stack
actuelle**. La branche est écrite quand même : c'est le paiement de T13 et elle ne coûte qu'une
fonction de six lignes. **Elle n'est pas testable aujourd'hui, et le rapport de lot doit le dire.**

> **Amendement 4, ratifié le 2026-08-18 — ce sont DEUX états inatteignables, pas un.**
> `dev-integration-mtb` l'a établi : `sans_telephone` l'est pour exactement la même raison que
> `aucune_action`. `telephone_retenu()` retombe toujours sur `TELEPHONE_ELEVAGE`, donc
> `<a|span class="__telephone">` est **toujours** émis. Le code des deux branches est correct, écrit
> et relu ; il n'est simplement pas **exerçable** avant la livraison de `mtb_get_telephone_elevage()`
> (§12.1). Les cinq autres états sur sept ont été mesurés sur des pages réellement servies.

---

## 7. Écran d'édition

Aucun menu d'administration nouveau. Le composant s'insère dans une **Page**.

**Chemin de clic** : `Pages › (une page) › +` → panneau **Mont Brabant** → **Encart d'appel** →
l'encart apparaît **déjà rempli** (« Nous contacter » + `06 80 50 56 19`) → volet latéral **Réglages
de l'encart** → *(facultatif)* phrase d'accroche → *(facultatif)* téléphone → *(facultatif)* page du
bouton → **Mettre à jour**.

Un seul `PanelBody` « **Réglages de l'encart** », trois contrôles dans cet ordre :

| # | Libellé exact à l'écran | Type | Aide sous le champ |
|---|---|---|---|
| 1 | **Phrase d'accroche** | `TextControl` | « Facultatif. Une phrase sous le titre « Nous contacter ». Laissée vide, aucune phrase n'apparaît. » |
| 2 | **Téléphone affiché** | `TextControl` | « Laissez ce champ vide pour afficher le numéro de l'élevage. Ce que vous tapez ici s'affiche exactement tel quel, sur cette page seulement. » |
| 3 | **Page vers laquelle mène le bouton** | `SelectControl` | « Sans page choisie, l'encart s'affiche sans bouton. Le bouton porte le nom de la page. » |

- **Aucun texte fantôme (`placeholder`) dans le champ Téléphone** : y écrire le numéro par défaut le
  recopierait dans le JavaScript — **deuxième copie d'un fait de domaine, interdite** (D11).
  L'aperçu, à deux centimètres, montre déjà le numéro résolu. **C'est l'argument entier en faveur de
  `ServerSideRender`** : un aperçu dessiné en JavaScript devrait recopier `0680505619`.
- **Pas de réglage de titre** : « Nous contacter » est figé par §10.3.
- Liste des pages : `useSelect` → `getEntityRecords( 'postType', 'page', { per_page: 100, status: 'publish', orderby: 'title', order: 'asc', _fields: 'id,title' } )`,
  libellés passés par `wp.htmlEntities.decodeEntities()` — sans quoi une page « Élevage &#038; famille »
  afficherait son entité en clair. Première option : « **Aucune (pas de bouton)** », valeur `0`.

  > **Amendement 1, ratifié le 2026-08-18 — `per_page: 100` et non `-1`.** Le contrat gelé écrivait
  > `-1` ; c'est **mesurément inopérant** sur le cœur installé (6.9), et `dev-back-mtb` l'a établi
  > avant de livrer : `GET /wp/v2/pages?per_page=-1&…` répond **400 `rest_invalid_param`**
  > (« per_page doit être compris entre 1 et 100 »). Cause lue dans le cœur,
  > `class-wp-rest-controller.php:352-360` : `per_page` est borné à `[1,100]` et le `validate_callback`
  > s'exécute **avant** le `sanitize_callback` `absint`. Côté JS, `core-data` ne pagine à la main que
  > si la requête porte son symbole interne `RECEIVE_INTERMEDIATE_RESULTS` ; sans lui la valeur part
  > telle quelle. Appliqué à la lettre, le contrat laissait `getEntityRecords` **ne jamais résoudre**,
  > donc le sélecteur définitivement sur « Chargement… » et `disabled` : **le réglage principal du
  > composant inutilisable, et l'issue livrée cassée**. `100` est le maximum accepté, requête
  > revérifiée en 200. Le site source compte **52 adresses au total** : le plafond n'est pas
  > atteignable à court terme, et s'il l'était un jour, l'état « page choisie absente de la liste »
  > déjà exigé ci-dessous couvre le cas sans rien casser. C'est un défaut du cœur, pas du contrat —
  > le cœur se heurte au même 400 sur son propre `/wp/v2/wp_pattern_category?per_page=-1`.

  > **Amendement 2, ratifié le 2026-08-18 — la valeur affichée quand la page choisie a disparu.** Le
  > contrat ne prescrivait que le texte d'aide. `dev-back-mtb` affiche en outre « **Aucune (pas de
  > bouton)** » dans le champ — c'est ce que voit le visiteur — au lieu de laisser une valeur
  > orpheline qui rendrait le champ visuellement vide, exactement le défaut que le contrat nomme pour
  > l'état « Chargement… ». **L'identifiant enregistré n'est pas effacé** : si la page est republiée,
  > le bouton revient seul. Retenu.
- **Trois états du sélecteur, tous à prévoir** : liste non résolue → option « Chargement… » et contrôle
  `disabled` (sans quoi le champ paraît vide alors qu'une page est choisie) ; page choisie absente de
  la liste (supprimée, dépubliée, protégée) → l'aide devient « **La page choisie n'est plus disponible.
  Choisissez-en une autre.** » ; liste vide → seule l'option « Aucune ».
- Drapeaux `__next40pxDefaultSize` et `__nextHasNoMarginBottom` sur chaque contrôle, comme les six sœurs.
- **Mots interdits à l'écran** (§10.4) : `slug`, `permalien`, `champ personnalisé`, `template`,
  `call to action`, `hero`, `bloc réutilisable`, `média`… Tous les libellés sont en français métier.

### 7.1 Où le composant s'utilise, où il ne s'utilise pas

- **S'utilise** dans une **Page** : accueil, Placement, une page de contenu.
- **Ne s'utilise pas** dans une fiche Portée, Chien ou Résultat (décision 24) — ni dans l'en-tête ou
  le pied de page, qui exigent `edit_theme_options`, capacité que le rôle Éditeur n'a pas (T1).

  > **Amendement 3, ratifié le 2026-08-18 — la décision 24 n'est pas encore appliquée par le code.**
  > Ce paragraphe écrivait « aucun bloc n'y est insérable ». `dev-integration-mtb` a mesuré le
  > contraire : `content/portee/bootstrap.php:53` et `content/chien/bootstrap.php:70` déclarent
  > `supports => array( 'title', 'editor', … )`, et **aucun `allowed_block_types_all` n'existe dans le
  > dépôt** — `categorie-mtb/bootstrap.php:25` renvoie explicitement ce filtre à l'epic Gabarits. Les
  > blocs **sont** donc insérables dans une fiche aujourd'hui. Sans conséquence pour #10 : l'encart y
  > rendrait correctement. **Mais la fiche d'aide ne doit pas promettre un verrou qui n'existe pas** —
  > elle dit où le composant a sa place, jamais qu'il est mécaniquement empêché ailleurs. Hors
  > empreinte de cette chaîne ; remonté au §12.

---

## 8. Fichiers, ordre d'écriture, et les trois pièges attestés

`wp-content/plugins/mtb-core/includes/blocks/encart-appel/` — **cinq fichiers, rien d'autre** :

| # | Fichier | Rôle |
|---|---|---|
| 1 | `block.json` | déclaration §2 ; **aucun `default` de domaine**, aucun `style`, aucun `viewScript`, aucun `example` |
| 2 | `rendu.php` | **le seul fichier du module qui déclare des fonctions** : la constante et les six fonctions d'aide |
| 3 | `render.php` | **zéro déclaration de fonction** ; consomme 2 |
| 4 | `editeur.js` | JavaScript ordinaire, aucune étape de construction, **servi à l'administration seule** |
| 5 | `bootstrap.php` | **écrit en dernier** ; `require_once __DIR__ . '/rendu.php';` puis `add_action( 'init', …, 20 )` |

`enregistrer()` fait `wp_register_script( 'mtb-encart-appel-editeur', …, array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-data', 'wp-html-entities', 'wp-server-side-render' ), MTB_CORE_VERSION, true )` **puis** `register_block_type( __DIR__ )` — **dans cet ordre** : `block.json` ne porte que la poignée du script, et le cœur exige qu'elle soit déjà connue au moment de l'enregistrement.

**Aucun `interface.php`** : aucun gabarit de #16/#17 n'a besoin de cet encart ; l'ajouter serait de
l'abstraction spéculative.

### Les trois pièges

1. **`render.php` est inclus par le cœur avec un `require` NU** (`wp-includes/blocks.php`), une fois
   par occurrence du bloc sur la page. **Une déclaration de fonction qui y figurerait ferait tomber le
   site entier dès le deuxième encart** — et `"multiple": true` rend ce cas ordinaire. Les fonctions
   d'aide vont dans `rendu.php`, inclus **une seule fois** par `bootstrap.php` en `require_once`.
   Forme à recopier : `includes/blocks/fiche-information/bootstrap.php:16-22`.
2. **Décision 27** : ne jamais laisser, à aucun instant de l'arbre de travail, un `require` vers un
   fichier non écrit — **erreur fatale sur tout le site, `wp-admin` compris**. D'où `bootstrap.php`
   en dernier. Arrivé deux fois au lot 3, une chaîne sœur y a perdu 9 de ses 25 vérifications.
3. **Décision 23** : commit par `git commit -m "…" -- <chemins explicites>`, **jamais** `git add` suivi
   d'un `commit` nu — l'index git est partagé entre les trois chaînes du lot.

### 8.1 Les six fonctions de `rendu.php`

```
const TELEPHONE_ELEVAGE = '0680505619';   // BRIEF §7, recopié à la lettre.
                                          // Constante d'espace de noms, jamais define().

telephone_retenu( string $saisi ): string        // les 3 crans du §1.2 ; valeur STOCKÉE, jamais
                                                 // mise en forme, ou ''
telephone_affiche( string $valeur ): string      // §5.1 : dix chiffres exactement → groupage par
                                                 // paires ; toute autre forme → verbatim
telephone_chiffres( string $valeur ): string     // preg_replace( '/[^0-9]/', '', … ), interne au href
telephone_href( string $valeur ): string         // '' hors de [4,15] chiffres ; sinon 'tel:' . chiffres.
                                                 // AUCUN +33 (§9, arbitrage 4)
page_retenue( $attribut ): int                   // les 3 crans du §1.2 → 0 ou identifiant validé
page_utilisable( $identifiant ): int             // les 4 conditions du §1.3 → 0 ou l'identifiant
```

### 8.2 Sécurité

- **Aucun chemin d'écriture propre au module** : pas de méta-boîte, pas de `save_post`, pas
  d'`update_option`, pas de point d'entrée REST. L'unique écriture est celle de l'éditeur de blocs du
  cœur — capacité `edit_post` + nonce REST `X-WP-Nonce`, vérifiés par le cœur. **C'est pourquoi aucun
  nonce n'est écrit ici : il n'y a pas de formulaire à protéger.**
- **Assainissement en entrée** : `absint()` sur l'identifiant ; `(string)` + `trim()` sur les deux
  chaînes, pour la seule décision « est-ce vide » et pour retirer des espaces de bord, qui ne sont pas
  un fait de domaine. **Ni `sanitize_text_field`, ni `wp_strip_all_tags`, ni `wp_kses`** (décision 20 :
  elles passent par `strip_tags()` et vident en silence une valeur commençant par `<`).
  **Aucune quatrième copie de `assainir_texte_recopie()` : T9 n'est pas aggravée**, et n'a pas à
  l'être — la valeur ne transite par aucun chemin d'écriture du module et sort systématiquement
  échappée.
- **Échappement en sortie**, un par nature : `esc_html()` sur l'accroche, le numéro affiché et le
  libellé du bouton ; `esc_url()` sur le `tel:` et sur le permalien ;
  `get_block_wrapper_attributes()` s'échappe seule (`phpcs:ignore` documenté, comme les sœurs). Les
  classes sont des **littéraux écrits en clair**, jamais interpolées.
- **`esc_url()` et le protocole `tel`** : `tel` figure dans la liste par défaut de
  `wp_allowed_protocols()` depuis WP 4.3. **À confirmer par un `grep` dans le cœur installé (6.9) au
  moment de l'implémentation — c'est une vérification, pas une hypothèse.** Si le protocole était
  absent, `esc_url()` renverrait `''` : le numéro resterait affiché en texte simple, **jamais un `href`
  vide** (D12) — la branche `<span>` du §3.3 est déjà écrite et sert ce cas.
- **Ce que l'éleveuse ne peut pas casser** : aucun réglage n'atteint une couleur, une police, un
  espacement ou une largeur (`supports` tout à `false`). Un mauvais réglage produit au pire un encart
  sans bouton, **jamais une page cassée**.

---

## 9. Arbitrages — les désaccords entre les deux plans, tranchés

Les deux plans ont été écrits en aveugle. Onze points divergeaient ou restaient ouverts.

| # | Désaccord | Décision | Raison |
|---|---|---|---|
| **1** | **Racine** : `<section>` (back) vs `<div>` (front) | **`<div>`** | Précédent déjà gelé au contrat #7 §3. Le back reconnaissait lui-même n'émettre ni `id` ni `aria-labelledby` ; le `<div>` supprime toute ambiguïté sur les repères et aligne le composant sur ses cinq sœurs |
| **2** | **Structure des actions** : deux `<p>` séparés, `__telephone` / `__action`, avec un `<a class="__lien-telephone">` et un `<span class="__numero">` imbriqués (back) vs un `<div class="__actions">` portant les deux `<a>` en enfants directs (front) | **la structure du front** | C'est **le désaccord qui aurait cassé la livraison**. Toute la mise en page du front repose sur `__actions` en conteneur flex à deux enfants directs : `gap` pour l'écart entre cibles tactiles, `flex-wrap` pour **tout le mécanisme du repli à 360 px sans requête média**. Deux `<p>` séparés s'empilent et le `gap` ne s'exprime jamais — **D7 et le repli 360 px tombaient tous les deux** |
| **3** | **Double crochet `__lien-telephone` / `__numero`** (back), pour que le numéro garde son traitement typographique qu'il soit lien ou non | **un seul crochet, `__telephone`, porté par un `<a>` ou par un `<span>`** (§3.3) | Le besoin du back était réel — le cas « numéro non composable » existe — mais deux crochets pour un élément dont un seul est stylé est du poids mort. Même classe, deux types d'élément : le sélecteur du front fonctionne sur les deux, et l'absence de soulignement sur le `span` est correcte, ce n'est pas un lien |
| **4** | **Classe du bouton** : `mtb-encart-appel__bouton` (back) vs `wp-element-button` **sans aucune classe propre au bloc** (front) | **`wp-element-button`, aucune classe de bloc** | **Vérifié dans le code** : `base.css:625-691` écrit la primitive §8.4 complète sur `.wp-element-button.wp-element-button` (classe doublée, `(0,2,0)`, cinq états). Un `__bouton` que personne ne style laissait le bouton retomber sur les styles globaux du cœur — `#32373c` sur `#fff`, gélule 9999 px : **deux couleurs hors des quinze jetons, T7 rouverte**. Application directe de la décision 30 (§4) |
| **5** | **Niveau de titre réglable** `h2`/`h3` (front, question ouverte) vs `<h2>` figé (back) | **`<h2>` figé, aucun attribut** | Un réglage de moins pour l'éleveuse, aucun besoin attesté, et la feuille est agnostique dans les deux cas. Rouvrable sans coût si un gabarit de l'epic 7 en a besoin |
| **6** | **Libellé du bouton** — `MASTER.md` §10.3 fige « Nous contacter » pour l'encart et ne donne **aucun** libellé à son bouton | **le titre de la page choisie, recopié** | Rien d'inventé, rien de composé, et il suit tout seul si elle renomme sa page. L'alternative — une chaîne fixe (« Nous écrire ») — serait **inventée par une chaîne**, ce que §10 interdit. **Réserve assumée** : une page nommée « Contact » donne un bouton « Contact », un nom là où §10.1 préfère un verbe à l'infinitif. Remonté à `lead-design-mtb` pour un éventuel ajout à §10.3 |
| **7** | **Plusieurs encarts par page ?** | **oui, `"multiple": true`** | Aucune raison de donnée de l'interdire (contrairement à « dernière portée », dont il n'existe qu'une). Une page longue peut vouloir un rappel en fin de lecture. **Conséquence : le piège du `require` nu de `render.php` (§8) devient un cas ordinaire, pas un cas limite** |
| **8** | **Rythme de section** : `max(var(--rythme-section), var(--e-7))` (front) vs `--e-7` sec, comme `mtb-derniere-portee.css:27-34` | **`max(var(--rythme-section), var(--e-7))`** | **Vérifié dans `tokens.css:96,100`** : `--rythme-section` vaut `clamp(2.5rem, 6vw, 5.5rem)`, soit **40 px à 360 px** — 8 px de moins que les `--e-7` qu'exige §2.1 entre deux filets — et **88 px au maximum**. Le `--e-7` sec respecterait §2.1 mais donnerait à un encart pleine largeur un rythme de section deux fois trop serré sur grand écran. Le `max()` compose **deux jetons existants**, n'en invente aucun, et satisfait §5.1 et §2.1 **inconditionnellement**. Ce n'est pas une divergence avec la sœur : `derniere-portee` ne pose qu'un `margin-block-start`, sur une seule de ses variantes, pour un filet **horizontal**. **Extrapolation nommée, remontée à `lead-design-mtb`** |
| **9** | **Rédaction de la phrase d'état vide** — deux formulations proposées | **« Ce bloc n'affiche rien tant qu'aucun numéro de téléphone ni aucune page de contact ne sont indiqués. »** | Forme imposée par §9.1. Verbe au pluriel et participe au masculin pluriel : avec deux sujets coordonnés dont l'un est féminin, c'est la seule forme qui se lise sans accord bancal. Plus précis pour l'éleveuse que « aucun téléphone ni aucune page » |
| **10** | **L'accroche relève-t-elle du chapô de §4.5** (Newsreader 400, `--t-md`) **ou du corps ?** | **du corps — aucune règle écrite** | §4.5 décrit le chapô d'une **page** ; rien ne dit qu'une accroche d'encart en relève. C'est le choix qui n'invente rien. Coût d'un changement ultérieur : une règle de trois déclarations. Remonté à `lead-design-mtb` |
| **11** | **L'état vide est-il testable ?** (soulevé par le back) | **non, et on l'écrit quand même** | Tant que `mtb_get_telephone_elevage()` n'existe pas, le cran 3 rend toujours un numéro : `aucune_action` est **structurellement inatteignable**. La branche est écrite parce que c'est le paiement de T13 et qu'elle coûte six lignes. **La non-testabilité est portée au rapport de lot**, pas dissimulée |

### 9.1 Arbitrages hérités du `brainstorm-mtb`, appliqués sans être re-litigés

| Point | Décision |
|---|---|
| Approche générale | **Sélecteur de page livré (recours immédiat pour l'éleveuse), câblé d'avance pour une source centrale** (§1.2). Trois lignes aujourd'hui contre une reprise page par page demain |
| Défaut du téléphone | **jamais dans `block.json`** — résolu au rendu (§1.2) |
| Forme du `tel:` | **nationale, `tel:0680505619`, aucun `+33`** — ajouter un indicatif serait dériver un fait absent du brief. RFC 3966 recommande la forme nationale pour un numéro non international, et les composeurs mobiles français la traitent sans difficulté. **Consigné pour que personne ne le « corrige » plus tard** |
| Titre de l'encart | **figé « Nous contacter »** (§10.3) ; l'« accroche » de l'issue est une phrase **facultative en plus**, vide par défaut |
| `#10` vs `#11` | **deux composants justifiés**, pas une duplication : #11 « Coordonnées + plan » est une *information* (où c'est, comment joindre), #10 est un *appel* posé en fin de page de contenu. §2.1 leur donne même des traitements distincts — l'encart d'appel est le seul des deux à porter le filet double **vertical**. **Ce qui est dupliqué, c'est le numéro**, et c'est ce que §1.1 répare sans toucher à l'empreinte de #11 |

---

## 10. Interdits

- Le thème n'interroge **jamais** la base directement (`WP_Query`, `get_post_meta`, `get_posts`,
  `get_terms`, `MTB\`).
- Le thème ne compose **jamais** une chaîne métier, ne formate ni ne normalise un numéro de téléphone,
  ne déduit aucun libellé.
- Le thème n'écrit **aucune** règle d'état vide, ne redéclare **aucune** apparence de bouton,
  n'invente **aucun** jeton ni valeur brute (exception unique : `48px`, avec son précédent
  `base.css:366-369`).
- L'extension n'émet **aucune** règle visuelle, **aucun** octet de CSS, **aucune** mise en page
  décorative. **T15 n'est pas reproduite.**
- **Zéro octet de JavaScript servi au visiteur.** `editeur.js` (~5 Ko) n'est servi qu'à
  l'administration ; `assets/js/` du thème reste vide. Un lien `tel:` et un lien de page fonctionnent
  sans script — il n'y a rien à améliorer progressivement ici.
- **Zéro requête vers un domaine tiers** : aucune police, aucune image, aucun `@import`, aucune
  `url()` distante, aucune police d'icônes, aucun sprite SVG, aucun appel HTTP sortant.
- Aucune requête média hors des trois points de rupture de §7.2 — **ici : zéro**.
- Aucun `register_block_style` : ce serait un choix visuel offert à l'éleveuse, que §13 interdit.
- Aucune modification de `theme.json` : le verrouillage du §14 est déjà intégralement en place.

---

## 11. Accessibilité — ce qui est tenu, et par quoi

| Exigence | Mécanisme |
|---|---|
| **D7 — cible tactile ≥ 48 px** (`MASTER.md` §8.4 l'emporte sur les 44 px de BRIEF §11) | bouton : `min-block-size: 48px` de `base.css:630`. Téléphone : `min-block-size: 48px` + `inline-flex`, règle 5. **Le piège de cette issue est réglé par le balisage** : le lien `tel:` est enfant direct de `__actions`, jamais un lien en ligne dans une phrase |
| Écart entre deux cibles | `gap: var(--e-4)` = 16 px, au-dessus du minimum de `--e-2` (§5.1) |
| Hiérarchie de titres | le `h2` de l'encart s'insère sous le `h1` de la Page. **Aucun `h1` émis par ce composant** |
| Parcours clavier | deux liens, ordre du DOM = ordre visuel. Aucun `tabindex`, aucun piège, aucun `order` CSS |
| Focus visible | anneau unique du §8.1 (`base.css:465-470`) + anneau clair intérieur du bouton (`base.css:688-691`), ≥ 3,77:1 sur n'importe quel fond (§12.8). **Aucun `overflow: hidden` sur la racine** : l'anneau n'est jamais rogné |
| Aucune information par la couleur seule | le lien téléphone est **souligné en permanence** (§8.2) ; le bouton porte un libellé écrit ; son survol change le fond **et** ajoute un cerne — deux signaux |
| Rien ne dépend du survol | aucune règle `:hover` dans la feuille du bloc, aucun contenu révélé au survol |
| **360 px** | canal texte 324 px − (32 + 32 + 6) = **254 px** utiles. `flex-wrap` fait passer le bouton sous le téléphone dès que la somme dépasse 254 px. `overflow-wrap: break-word` sur le numéro. **Aucune largeur fixe, aucune requête média, aucun débordement** |
| **Zoom 200 %** | aucune taille en `vw` seul, **aucune hauteur fixe** (`min-block-size` uniquement), aucun `overflow: hidden` |

### 11.1 Contrastes employés — tous calculés, aucun estimé

| Paire | Ratio | Source |
|---|---|---|
| titre `--pin` sur `--calcaire-creux` | **12,74:1** AAA | **absent de la table §12.3** — autorisé par §3.2, calculé indépendamment par les deux côtés. **Amendement à faire ratifier par `lead-design-mtb`** (§12) |
| accroche `--texte` sur `--calcaire-creux` | 10,78:1 AAA | §12.3 |
| lien téléphone `--sauge-fonce` sur `--calcaire-creux` | 6,92:1 AAA | §12.3 |
| survol du lien `--pin` sur `--calcaire-creux` | 12,74:1 | idem première ligne |
| libellé du bouton `--calcaire` sur `--sauge` | 5,25:1 AA | §12.4 |
| **limite du bouton `--sauge` contre `--calcaire-creux`** — critère **1.4.11 (≥ 3:1)** | **4,70:1 ✓** | **absente de tout le §12.** Calculée par la chaîne (L(`--sauge`) = 0,1266, L(`--calcaire-creux`) = 0,7810 → 0,8310 / 0,1766 = 4,705), **confirmée par recalcul indépendant du plan front**. **Elle passe : aucun cerne permanent n'est nécessaire, aucun amendement de §8.4 n'est requis, et la feuille n'écrit aucune bordure sur le bouton** |

---

## 12. Ce qui sort de cette chaîne — à router par l'orchestrateur

Aucun de ces points n'est corrigeable dans l'empreinte de #10.

1. **Créer une issue « Coordonnées de l'élevage — écran de réglages unique »** (`contact` / `infra`),
   portant adresse, téléphone, courriel et page de contact, plus ses fonctions de lecture aux
   signatures du §1.1. Sans elle, le numéro de l'élevage existera en dur à **trois** endroits — cet
   encart, #11, et le pied de page de §7.3 — et « je change de numéro » redeviendra un travail de
   développeur, sur un projet dont la phrase fondatrice est l'inverse. **Le câblage du §1.2 rend la
   bascule gratuite et rétroactive le jour où elle est livrée.**
2. **Interdire à #10 et #11 de déclarer `mtb_get_telephone_elevage()` ou `mtb_get_page_contact()`.**
   Décision 19 : si les deux chaînes déclarent la même fonction, `scandir()` en fait gagner une par
   ordre alphabétique, **sans une erreur**, sur un site qui répond 200. Tant qu'aucune ne la déclare,
   `function_exists()` est faux et chacune retombe sur son repli.
3. **Renvoi croisé entre les deux fiches d'aide.** Côté #10 : « Pour donner l'adresse et le plan
   d'accès, employez plutôt *Coordonnées + plan*. » La réciproque appartient à #11.
4. **Amendements à `MASTER.md` §12, pour `lead-design-mtb`** : `--pin` sur `--calcaire-creux` =
   **12,74:1** (absent de §12.3, pourtant l'encre de titre de l'encart) et `--sauge` contre
   `--calcaire-creux` = **4,70:1** (limite non textuelle, absente de tout le §12). Même famille que
   l'amendement `--laiton-texte` / `--calcaire-creux` = 4,75:1 déjà consigné dans `editor.css:94-115`
   et toujours non ratifié.
5. **Trois questions de design remontées** (§9, arbitrages 6, 8, 10) : libellé du bouton à figer ou
   non en §10.3 · le rythme de section d'un encart · l'accroche d'encart relève-t-elle du chapô de
   §4.5.
6. **Observation hors empreinte, pour `review-mtb`** : `mtb-derniere-portee.css:230` annonce en
   commentaire que `.mtb-derniere-portee > :last-child` « pèse (0,2,0) » ; elle pèse **(0,1,0)**, et
   dans la toile de l'éditeur le `p` de `base.css:240` y pèse (0,1,1) — **la remise à zéro de marge y
   perd**. Sans effet visible aujourd'hui. **Ce module n'écrit pas la même forme** : voir la règle 6
   du §4.1, à classe doublée.
7. **Question pour l'éleveuse, non bloquante** : veut-elle qu'on l'appelle en premier, ou qu'on lui
   écrive ? C'est la hiérarchie visuelle entre le numéro et le bouton. **Repli appliqué en l'absence de
   réponse** : les deux au même niveau, numéro d'abord dans l'ordre du document — il sert le public de
   BRIEF §2 et §4 (personnes âgées sur mobile), pour qui `tel:` est l'action la plus directe, sans
   fermer le chemin vers le formulaire, seul point de collecte prévu (§9 du brief).
