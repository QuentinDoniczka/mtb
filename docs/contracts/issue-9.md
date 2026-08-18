# Contrat d'interface — Issue #9 — Composant Bandeau d'alerte

**Gelé le 2026-08-18** par la chaîne `lead-issue-mtb` de l'issue #9, après réconciliation des plans de
`leaddev-back-mtb` et `leaddev-front-mtb`, écrits en aveugle l'un de l'autre.

Ce document est contraignant. Il prime sur les deux plans partout où ils divergent ; les six arbitrages
rendus sont consignés au §8 avec leur raison.

**Empreinte d'écriture de cette issue — stricte, arbre de travail partagé, aucune isolation :**

| Chemin | Nature |
|---|---|
| `wp-content/plugins/mtb-core/includes/blocks/bandeau-alerte/**` | dossier neuf |
| `wp-content/themes/mtb/assets/css/blocs/mtb-bandeau-alerte.css` | fichier neuf |
| `docs/guide/composant-bandeau-alerte.md` | fichier neuf |
| `docs/contracts/issue-9.md` | ce fichier |

Tout le reste appartient à une chaîne sœur (#10 encart d'appel, #11 coordonnées + plan) ou au lot 3.
En particulier : `theme.json`, `functions.php`, `tokens.css`, `base.css`, `editor.css`,
`includes/blocks/categorie-mtb/` et tout autre dossier de `includes/blocks/` sont **interdits**.

---

## 1. L'approche retenue, et ce qu'elle refuse

Un bloc `mtb/bandeau-alerte`, **rendu serveur**, **un seul réglage** : le texte du message. Rendu dans le
**canal texte** comme un encart `--calcaire-creux` portant le **filet double vertical**, sur le patron
littéral de `MASTER.md` §9.5 (« encart de confirmation `--calcaire-creux`, filet double vertical
`--sauge` »).

Refusé explicitement, et à ne pas réintroduire en cours de développement :

- **aucun réglage de type ou de niveau d'alerte**, aucune liste fermée *information / attention*.
  `MASTER.md` §3.2 ferme la liste des fonds à cinq et §3.1 réserve `--oxyde` à l'erreur de formulaire :
  aucune seconde couleur légitime n'existe. Un réglage sans effet visible est pire que pas de réglage ;
- **aucune icône, aucun pictogramme, aucune forme dessinée nouvelle** (§13) ;
- **aucun `role`, aucun `aria-live`, aucun `<aside>`, aucun titre, aucune étiquette, aucun préfixe** ;
- **aucun `InnerBlocks`** — ce serait une seconde *Fiche d'information* ;
- **aucune fonction de lecture, aucun hook, aucune option, aucune méta, aucun type de contenu.**

### 1.1 D7 — pourquoi elle est tenue par construction

`MASTER.md` §3.3 pose la règle de façon asymétrique : « Le mot seul suffit ; **la couleur seule ne
suffit jamais**. » §8 : « deux signaux au minimum, jamais la couleur seule. » §9.5 en donne le modèle
appliqué — la confirmation d'envoi est un message important, écrit « Message envoyé. », **sans couleur
sémantique et sans coche verte** (« la couleur seule ne dirait rien »).

Ce composant **n'a aucune couleur sémantique** : son fond `--calcaire-creux` est la surface en creux
générique du §3.2, employée aussi par l'encart d'appel et par l'état vide. Il ne signale aucun état,
aucune gravité. **Il n'y a donc aucune information portée par la couleur, donc rien à doubler.** Le sens
est intégralement porté par la phrase française que l'éleveuse tape.

Ligne correspondante pour le tableau §12.9 de `MASTER.md`, à inscrire lors de la prochaine révision de
`lead-design-mtb` : « Message temporaire — signal 1 : le texte lui-même · signal 2 : l'encart en creux
(forme et position) · **aucun signal de couleur**. »

---

## 2. Fonctions de lecture exposées par l'extension

**Aucune.**

Ce module n'ajoute **rien** à la surface globale de `mtb-core` : ni fonction `mtb_get_*`, ni fonction de
rendu de composant, ni constante, ni option, ni filtre, ni hook, ni route REST. Le thème n'a rien à
appeler ; il n'a que du CSS à écrire. Le grep de frontière du contrat #2 doit rester **à zéro
occurrence** dans le thème après cette issue.

**Aucune fonction de rendu réutilisable n'est exposée aux gabarits de #16-#18**, bien que l'amendement 1
du contrat #1 l'autoriserait formellement. Motif : le seul appel imaginable serait
`mtb_bandeau_alerte_rendu( 'Pas de chiots actuellement' )` **écrit dans un fichier de thème** — un
message temporaire gravé dans un fichier que Fabienne ne peut pas ouvrir. C'est la règle d'or enfreinte,
pas une commodité. **Interdit de contrat.**

---

## 3. Bloc enregistré

| Clé | Valeur, littéralement |
|---|---|
| `$schema` | `https://schemas.wp.org/trunk/block.json` |
| `apiVersion` | `3` |
| `name` | **`mtb/bandeau-alerte`** |
| `title` | **`Bandeau d'alerte`** — seul nom attesté (BRIEF §6, titre de l'issue) |
| `description` | `Message temporaire (« portée à venir », « pas de chiots actuellement »).` — recopiée de BRIEF §6 ligne 146 |
| `category` | **`mtb`** (« Mont Brabant ») — décision 25, aucun index central à éditer |
| `icon` | `megaphone` (dashicon du cœur, administration uniquement, même origine). Convention des cinq sœurs : `cover-image`, `media-text`, `pets`, `list-view`, `grid-view`. Ni `warning` ni `info` : tous deux suggéreraient un niveau de gravité que ce composant refuse d'avoir |
| `keywords` | `[ "alerte", "message", "annonce", "temporaire", "bandeau" ]` |
| `attributes` | **`{ "message": { "type": "string", "default": "" } }` — le seul.** Ni `source` ni `selector` |
| `example` | `{ "attributes": { "message": "Le texte du message s'affiche ici." }, "viewportWidth": 600 }` — aucune image, aucun fait d'élevage |
| `render` | `file:./render.php` |
| `editorScript` | **une poignée** (`mtb-bandeau-alerte-editeur`), **jamais `file:`** — contrat #1 §10, piège 1 |

**Clés volontairement absentes, à ne pas ajouter** : `style` et `editorStyle` (le CSS vit dans le thème,
décision 28 — ne pas rejouer T15), `viewScript` et `script` (zéro octet de JS public), `textdomain`
(aucune i18n dans `mtb-core`, contrat #1 §7), `usesContext`.

### 3.1 `supports` — ce que §14 impose d'éteindre

```
align: false          alignWide: false      anchor: false        ariaLabel: false
className: false      customClassName: false  html: false        lock: false
renaming: false       reusable: false       splitting: false     inserter: true
multiple: false       layout: false         position: false      border: false   shadow: false
color:      { background:false, text:false, link:false, gradients:false, button:false, enableContrastChecker:false }
typography: { fontSize:false, lineHeight:false, textAlign:false }
spacing:    { margin:false, padding:false, blockGap:false }
dimensions: { aspectRatio:false, minHeight:false }
background: { backgroundImage:false, backgroundSize:false }
filter:     { duotone:false }
```

Trois points que le dev **ne doit pas « simplifier »** :

- **`className: false`** retire la classe `wp-block-mtb-bandeau-alerte` du rendu. C'est le seul `support`
  de cette liste qui change la sortie, et le thème ne s'y accroche jamais. **Le crochet racine est donc
  unique : `mtb-bandeau-alerte`.**
- **`splitting: false`, et aucun `onSplit` / `onReplace` / `identifier` passé au `RichText`.** Sans cela,
  `Entrée` demande au cœur de scinder le bloc, c'est-à-dire d'en créer un **second**, que `multiple:false`
  ne bloque pas à ce stade. Avec ces propriétés absentes, `Entrée` insère un saut de ligne — comportement
  voulu, et c'est pourquoi `<br>` figure dans la liste blanche du §6. `disableLineBreaks: true` est
  **écarté** : il ferait de `Entrée` une touche morte, ce qui se lit comme un bug.
- Les clés à forme d'objet sont écrites **en objet à sous-clés fausses** (comme
  `fiche-information/block.json:60-88`), jamais sous la forme `"color": false` : les deux fonctionnent,
  la première documente ce qui est fermé. `theme.json` verrouille les **valeurs**, `block.json` décide de
  l'**existence** du contrôle — les deux sont nécessaires.

### 3.2 Le champ

`RichText`, `tagName: 'p'`, `className: 'mtb-bandeau-alerte__message'`,
**`allowedFormats: [ 'core/link' ]`** et rien d'autre, `multiline` absent,
`placeholder: 'Le message à afficher…'`.

Le lien seul est justifié par la règle d'or : pointer vers la page *Placement* sans recopier une mise en
forme. Il est **mesuré** : `--sauge-fonce` sur `--calcaire-creux` = **6,92:1, AAA**, paire tabulée au
§12.3. C'est ce qui distingue ce composant de `bandeau-ouverture`, qui a refusé le `RichText` faute d'une
paire suffisante sur voile photo (4,09:1) — la divergence est fondée, pas arbitraire.

**Aucun `InspectorControls`, aucun panneau latéral** : un panneau à zéro réglage est un panneau qu'elle
ouvre pour rien.

**`allowedFormats` n'est pas une barrière de sécurité** — c'est un réglage d'interface. La barrière est
en PHP, en sortie (§6).

---

## 4. Balisage émis — table close, à recopier littéralement

### 4.1 Public, message renseigné — le seul cas où quoi que ce soit est émis

```html
<div class="mtb-bandeau-alerte"><p class="mtb-bandeau-alerte__message">…texte de l'éleveuse, lien éventuel…</p></div>
```

| # | Élément | Classes, littéralement | Contenu |
|---|---|---|---|
| 1 | `<div>` | `mtb-bandeau-alerte`, émise par `get_block_wrapper_attributes( array( 'class' => 'mtb-bandeau-alerte' ) )` | l'élément 2 |
| 2 | `<p>` | `mtb-bandeau-alerte__message` | `$message` passé à `wp_kses()` — voir §6 |

Racine **`<div>`**, jamais `<section>`, `<aside>`, `<header>` ni `<p>` : un repère de page sans nom
accessible est un repère générique inutile, et le nommer exigerait un libellé absent du §10.3 (précédent
gelé, contrat #6 §5).

Deux éléments et pas un seul (`<p>` racine portant les attributs d'enveloppe) : la séparation
« l'encart » / « le message » est exactement la coupure de la décision 30 ; le crochet `__message` donne
la **spécificité à deux classes** nécessaire contre le `p` de `base.css:241` ; et l'éditeur a besoin d'un
conteneur. Coût : ≈ 40 octets.

### 4.2 Public, message vide

**Chaîne vide.** Aucun élément, aucune enveloppe, aucun commentaire HTML, aucune marge fantôme.
`return` avant la moindre impression — décision 26 et D12.

### 4.3 Éditeur

```html
<div [useBlockProps() — AUCUNE classe du thème ajoutée ici]>
  <div class="mtb-bandeau-alerte">
    <p class="mtb-bandeau-alerte__message"><!-- RichText, toujours monté --></p>
  </div>
  <!-- si et seulement si le message est vide, en FRÈRE de l'encart : -->
  <div class="mtb-etat-vide"><span class="mtb-etat-vide__nom">Bandeau d'alerte</span><p class="mtb-etat-vide__phrase">Ce bloc n'affiche rien tant qu'aucun texte n'est renseigné.</p></div>
</div>
```

**L'encart reste rendu même vide** : le `RichText` y affiche son texte d'invite, donc l'éleveuse voit la
forme qu'elle remplit et peut cliquer dedans. Elle voit deux choses cohérentes : la forme, et la phrase
qui dit que rien ne s'affichera tant qu'elle n'aura pas tapé.

### 4.4 Crochets de classes — liste close

| Crochet | Où | Qui l'habille |
|---|---|---|
| `.mtb-bandeau-alerte` | racine de l'encart, public **et** éditeur | `mtb-bandeau-alerte.css` (thème) |
| `.mtb-bandeau-alerte__message` | le `<p>`, public **et** éditeur | idem |
| `.mtb-etat-vide` · `.mtb-etat-vide__nom` · `.mtb-etat-vide__phrase` | **éditeur uniquement** | `editor.css:126-168`, déjà écrit, **hors empreinte** |

**Modificateurs refusés par contrat**, aucune règle ne les cible et une classe morte est un nom qu'une
chaîne sœur orthographiera mal un jour : `mtb-bandeau-alerte--vide`, `--editeur`, `--info`, `--alerte`,
`--important`, `mtb-etat-vide--bandeau-alerte`. **Aucun attribut `data-*` non plus** — ni côté public, ni
côté éditeur (voir arbitrage A3).

---

## 5. États spéciaux

| État | Émis par le serveur | Rendu par le thème |
|---|---|---|
| **message renseigné** | les deux éléments du §4.1 | encart `--calcaire-creux`, filet double vertical, encre `--texte` |
| **message vide — public** | **rien du tout**, pas même l'enveloppe | **rien.** Aucune règle de la feuille ne suppose la présence de `.mtb-bandeau-alerte` sur une page ; aucun `:empty`, aucun masquage CSS |
| **message vide — éditeur** | le balisage du §4.3 | apparence §9.1, portée par `editor.css` — **la feuille du bloc n'écrit rien** |
| **balisage refusé (collage)** | le balisage est retiré, **le texte est conservé** ; s'il ne reste rien, cas « message vide » | — |
| `page_protegee` | inatteignable : le cœur remplace `the_content` par le formulaire avant tout `render_callback` | rien à faire, rien à défaire |
| `aucune_portee` · `donnee_absente` · `parent_hors_elevage` | **sans objet** — ce bloc ne lit aucune donnée d'élevage | — |
| **message très long** (dix lignes) | — | l'encart grandit ; aucune hauteur, aucun plafond, aucun « lire la suite » (§9.4) |
| **URL collée dans le message** | — | `overflow-wrap: break-word` (§7.7) |

Ce bloc n'imprime **jamais** « Non renseigné » : ce n'est pas une fiche.

### 5.1 Détection du « vide » — le test exact, autorité du serveur

Un `RichText` paraît rempli sans l'être : `<br>`, `&nbsp;`, une espace insécable U+00A0, une espace fine
insécable U+202F posée par le clavier français devant un « ! », un `<a href="…"></a>` dont le libellé a
été effacé.

`rendu.php` → `est_vide( string $valeur ): bool`, sur une **copie jetée dans l'expression même** (aucune
valeur émise n'est altérée) :

1. `wp_strip_all_tags()` — fait tomber `<br>` et vide `<a></a>` ;
2. `html_entity_decode( …, ENT_QUOTES | ENT_HTML5, 'UTF-8' )` — `&nbsp;` devient U+00A0 ;
3. `preg_replace( '/[\p{Z}\p{C}\s]+/u', '', … )` — `\p{Z}` couvre U+00A0 et U+202F, `\p{C}` les
   caractères de contrôle et U+200B. **Le drapeau `u` est obligatoire** ;
4. `'' === $resultat`. **`null` de `preg_replace` (UTF-8 invalide) vaut vide** : une chaîne dont
   l'encodage est cassé n'est pas un message affichable.

Le miroir JavaScript de `editeur.js` ne décide **que** de l'affichage de l'encadré :
`valeur.replace(/<[^>]*>/g,'').replace(/&nbsp;/g,' ').replace(/[\s  ​]+/g,'') === ''`

**Divergence bornée et assumée** : le miroir ne décode pas toutes les entités. Conséquence maximale, un
encadré d'éditeur légèrement en retard sur la vérité. **Le serveur est l'autorité, l'éditeur est un
conseil** — même arbitrage que `bandeau-ouverture/editeur.js:104-109`. **Ne pas « corriger » ce point**
en déportant le test côté serveur : cela coûterait un aller-retour REST à chaque frappe.

---

## 6. Assainissement et échappement

| Moment | Ce qui s'y passe |
|---|---|
| **À l'écriture** | **rien de notre côté, délibérément.** L'écriture est celle du cœur sur la Page : nonce et `edit_page` déjà vérifiés par `wp-admin/post.php`. Le module n'ouvre aucun chemin d'écriture ⇒ **aucun nonce ni `current_user_can` à écrire** ; en poser un serait du théâtre |
| **Barrière déclarative** | `block.json` : `"type": "string"`, `"default": ""`. `WP_Block_Type::prepare_attributes_for_render()` remplace toute valeur non conforme par le défaut, **avant** `render.php` |
| **À la sortie** | `wp_kses()` dans `rendu.php`, appelée par `render.php`. **Seule et unique barrière** |

### 6.1 La liste blanche, plus étroite que `wp_kses_post()`

```
balises_admises() → array(
    'a'  => array( 'href' => true, 'target' => true, 'rel' => true, 'data-type' => true, 'data-id' => true ),
    'br' => array(),
)
protocoles_admis() → array( 'http', 'https', 'mailto', 'tel' )

wp_kses( $message, balises_admises(), protocoles_admis() )
```

- **`a`** est le seul format autorisé. `href` / `target` / `rel` sont ce que `core/link` écrit réellement
  (`rel="noreferrer noopener"` quand elle coche « ouvrir dans un nouvel onglet »). `data-type` /
  `data-id` sont écrits par le cœur quand elle choisit une page du site : inertes, mais **c'est ce
  qu'elle a produit** — les retirer serait normaliser sans raison. `id`, `class`, `title` sont **exclus** :
  le cœur ne les produit pas ici et un `id` collé créerait un doublon d'identifiant dans le document.
- **`br`** : conséquence directe du comportement de `Entrée` (§3.1). Sans lui, un message sur deux lignes
  perdrait sa coupure en silence.
- **Protocoles explicites** plutôt que `wp_allowed_protocols()` (une vingtaine, dont `ftp`, `irc`, `svn`,
  `feed`). Une alerte pointe vers une page du site (URL relative, aucun protocole à valider), un courriel
  ou un téléphone.

**Divergence assumée avec `fiche-information`**, qui applique `wp_kses_post()` sur son titre
(`rendu.php:117`). Ce n'est pas une divergence de principe — les deux assainissent en sortie, par liste
blanche `kses`, sur une valeur de `RichText`. C'est un **rétrécissement dicté par la portée déclarée du
champ** : `wp_kses_post()` admet une centaine de balises dont `img`, `table`, `h2`, `ul`, `figure`. Un
collage ferait donc survivre une image ou un tableau **dans un encart d'alerte** — une décision visuelle
prise par un collage, exactement ce que §14 retire à l'éditrice. Et `h2` / `hr` / `blockquote` porteraient
un **second filet double** dans le bloc, interdit du §2.1. Le titre de `fiche-information` est un autre
champ, dont le gras et l'italique sont autorisés par conception : sa liste large n'est pas fausse pour
lui, elle serait fausse ici.

**La décision 20 n'est pas enfreinte, et il faut savoir pourquoi.** Elle protège les **données d'élevage
recopiées** — un `<60%` de dysplasie, un numéro LOF — qu'un `strip_tags` viderait en silence. Le message
d'alerte n'est pas une donnée recopiée : c'est une phrase composée. Et le cas résiduel est fermé par
construction : `supports.html: false` retire « Modifier en HTML », et le `RichText` échappe en `&lt;` tout
`<` tapé. **`wp_kses` ne voit jamais autre chose que ce que le `RichText` a produit.** Le seul motif qui
resterait ambigu est un `<` collé immédiatement suivi d'une lettre ; il n'apparaît dans aucune phrase
française plausible, et il est nommé ici plutôt que découvert plus tard.

### 6.2 Tableau d'échappement

| Valeur émise | Fonction | Pourquoi celle-là |
|---|---|---|
| `message` | **`wp_kses( …, balises_admises(), protocoles_admis() )`** | `esc_html()` afficherait `<a href="…">` en clair ; `wp_kses_post()` laisserait passer image et tableau |
| Attributs de la racine | **aucune** — `get_block_wrapper_attributes()` échappe lui-même | l'entourer d'`esc_attr()` **double** l'échappement |
| Toutes les classes | **`esc_attr()`**, y compris littérales | la garde ne coûte rien et survit à un refactor |
| Chaînes de l'état vide | **`esc_html()`** si émises en PHP ; **texte nu** en JS | littéraux du code |

Chaque `echo` / `printf` de balisage non échappé porte un
`phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped` **suivi de sa justification en une
ligne**, jamais nu.

---

## 7. Feuille de style du thème

**Fichier unique, neuf** : `wp-content/themes/mtb/assets/css/blocs/mtb-bandeau-alerte.css`.

### 7.1 Le nom est mesuré, pas supposé

Lu ligne à ligne dans `wp-content/themes/mtb/functions.php:194-250` : la boucle itère sur le **registre
des blocs** (`WP_Block_Type_Registry`, l. 221), filtre sur `#^[a-z0-9-]+/[a-z0-9-]+$#` (l. 224), dérive la
base par `str_replace( '/', '-', $nom )` (l. 228) puis le chemin `assets/css/blocs/<base>.css` (l. 229).
`mtb/bandeau-alerte` → **`assets/css/blocs/mtb-bandeau-alerte.css`, confirmé**. `file_exists()` (l. 232)
échoue **en silence** : un caractère d'écart donne un encart nu sur un site qui répond 200.

Le bloc doit être enregistré **avant `wp_loaded`** : le chargeur de `mtb-core` enregistre sur `init`, qui
précède. La dépendance `mtb-jetons` en administration est traitée en amont (`functions.php:213-219`,
correctif du lot 3 : sans elle, `WP_Dependencies::all_deps()` abandonnerait la feuille **entière, en
silence**, et l'encart serait nu dans la toile). **Rien à déclarer nulle part — déposer le fichier
suffit.** À vérifier, pas à croire : V2 du §9.

### 7.2 Le canal — aucune règle `grid-column`

`templates/singular.html:4` rend `core/post-content` avec `mtb-canal` : les blocs insérés par l'éleveuse
en sont les enfants directs. `base.css:515-520` leur affecte déjà le canal texte
(`grid-column: texte-debut / texte-fin; min-inline-size: 0`), et `base.css:507-510` neutralise le
`max-inline-size` et les marges automatiques de `is-layout-constrained`.

**La feuille n'écrit donc ni `grid-column`, ni `inline-size`, ni `max-inline-size`, ni `margin-inline`.**
`MASTER.md` §7.1 réserve le canal texte à « prose, titres, listes, formulaires, tableaux courts » ; le
canal pleine est réservé au bandeau d'ouverture, à la bande photo et au pied de page, « **rien
d'autre** ». Un bandeau d'alerte pleine largeur serait un bandeau photo sans photo — un dispositif qui
n'existe pas au catalogue. Le bloc n'émet **jamais** `alignwide` ni `alignfull` (`supports.align: false`).

Corollaire : aucun cadre à ratio, aucun plafond de hauteur, donc **hors d'atteinte de la famille de
défauts de la décision 32**.

### 7.3 Règle 1 — l'encart, seul filet du bloc

| Propriété | Valeur | Autorité |
|---|---|---|
| `margin-block` | `var(--e-7)` | §2.1 : jamais deux filets « de suite sans au moins `--e-7` d'écart ». **Les deux côtés** — voir l'amendement 1 |
| `background-color` | `var(--calcaire-creux)` | §9.5 littéral ; §3.2 |
| `color` | `var(--texte)` | §3.2, encre autorisée sur ce fond |
| `background-image` | `var(--filet-double-v)` | §2.1 |
| `background-repeat` | `no-repeat` | §2.1, application type |
| `background-position` | `left top` | recopié de `base.css:274`, même filet vertical sur `blockquote` |
| `background-size` | `var(--filet-double-h) 100%` | §2.1 : 6 px de large, pleine hauteur |
| `padding-block` | `var(--e-6)` | §5.1 : « `--e-6` : padding d'encart » |
| `padding-inline-end` | `var(--e-6)` | idem |
| `padding-inline-start` | `calc(var(--e-6) + var(--filet-double-h))` | §2.1 : la largeur du filet est « **à réserver en padding** » |

**Propriétés logiques obligatoires** pour les rembourrages (`padding-block`, `padding-inline-*`), jamais
`padding-left` / `padding-top`. `background-position` **n'a aucune forme logique en CSS** : `left top` est
écrit en littéral, ce sont des mots-clés et non des longueurs — pas une valeur brute au sens du §13.

`color: var(--texte)` **est écrit** bien qu'égal à la valeur héritée : un fond déclaré sans son encre est
une paire du §3.2 non tenue. Une déclaration, 24 octets, la paire devient une garantie. **À ne pas
« simplifier ».**

**Ce que la règle n'écrit pas, à dessein** : `font-family`, `font-size`, `line-height` (`base.css:105-116`
pose déjà la ligne « Corps » du §4.5 ; monter le message à `--t-md` serait une décision visuelle que
`MASTER.md` ne prend nulle part) · `border-radius` (`--r-0` vaut `0`, déclaration inerte) · `border`,
`box-shadow` (§5.4 ; le filet **est** la délimitation).

> **Amendement 1 — 2026-08-18, après mesure de `dev-integration-mtb`. Le §7.3 disait faux.**
> Il interdisait toute marge basse au motif que « l'élément suivant apporte la sienne ». **C'est faux pour
> le voisin le plus courant** : `base.css:241` ne donne à `p` qu'un `margin-block-end`, jamais de
> `margin-block-start`, et `.mtb-canal` est une grille en `row-gap: normal` — les marges ne se collapsent
> donc pas et rien ne comble le bas. **Mesuré au navigateur : bas de l'encart 1182,02 → haut du `<p>`
> suivant 1182,02, soit 0,00 px.** Le texte suivant est collé au bord de l'encart, visiblement, à 1440 px
> comme à 360 px. C'est exactement la famille de défauts de la décision 33 — une mesure juste sur le
> mauvais objet.
> **Correctif gelé : `margin-block: var(--e-7)`**, un mot. Les 48 px du haut sont conservés (V10 reste
> vrai, et deux bandeaux consécutifs passent à 96 px, toujours ≥ 48), le bas est fermé, et la recette se
> rapproche de celle de sa sœur #10. Le motif « cela ferait dériver le `--rythme-section` » ne tient pas :
> `--e-7` n'est pas `--rythme-section`, et une marge symétrique sur un encart est ce que `base.css`
> applique déjà à `hr`.

### 7.4 Règle 2 — le message

Sélecteur `.mtb-bandeau-alerte .mtb-bandeau-alerte__message`, **spécificité (0,2,0) obligatoire**.
`editor.css:11-28` établit que la toile préfixe les sélecteurs de `base.css` par
`.editor-styles-wrapper` : `p` y pèse **(0,1,1)**. Une feuille de bloc n'est pas préfixée. À une seule
classe (0,1,0), la règle gagnerait sur le site et **perdrait dans l'éditeur** — exactement le mensonge que
`editor.css` existe pour empêcher.

| Propriété | Valeur | Pourquoi |
|---|---|---|
| `margin-block-end` | `0` | `base.css:241` pose `--e-4` sur tout `p` : sans neutralisation, le rembourrage bas vaudrait 48 px contre 32 en haut, encart visiblement déséquilibré |
| `overflow-wrap` | `break-word` | §7.7 ; l'éleveuse peut coller un lien dont le libellé **est** une URL — seul débordement horizontal concevable à 360 px |

### 7.5 Le filet double — les deux vérifications exigées

**(a) Une seule fois dans le bloc.** Porteurs de filet de `base.css` : `h1` (194), `h2` (211), `hr` (279),
`blockquote` (272). **Aucun ne peut apparaître dans ce bloc** : la racine est un `<div>`, l'enfant unique
un `<p>`, et `allowedFormats` est réduit à `core/link`. **Interdit de contrat : le bloc n'émet jamais de
titre, de `<hr>`, de `<blockquote>`, ni un second `background-image`.**

**(b) Jamais à moins de `--e-7` d'un autre filet.** Les marges ne se collapsent pas entre enfants d'une
grille : l'écart réel est la somme.

| Élément précédent | Sa marge basse | + `--e-7` | Écart entre filets | §2.1 |
|---|---|---|---|---|
| `h1` | `--e-5` = 24 px | 48 px | **72 px** | ✓ |
| `h2` | `--e-4` = 16 px | 48 px | **64 px** | ✓ |
| `hr` | `--e-7` = 48 px | 48 px | **96 px** | ✓ |
| `blockquote` | ≈ `--e-4` | 48 px | **≥ 64 px** | ✓ |
| un second bandeau d'alerte | 0 | 48 px | **48 px** | ✓ au plancher exact |

Sans la marge, un bandeau posé sous un `h2` mettrait son filet vertical à **16 px** du filet horizontal du
titre. La marge `--e-7` **n'est pas un choix de rythme : c'est la règle du §2.1 rendue exécutable.**

### 7.6 Contraste — calculé

**`--texte` `#22312A` sur `--calcaire-creux` `#E7E5DA` = 10,78:1, AAA**, recalculé indépendamment par la
formule du §3 et **tabulé au §12.3**, première ligne. Paires secondaires du même encart, toutes tabulées :
`--sauge-fonce` (lien) **6,92:1 AAA**, `--laiton` (soulignement) **3,15:1** non textuel — et le
soulignement n'est jamais porteur du sens, il est le second signal du §12.9.

### 7.7 Le lien — aucune surcharge

`base.css:248-259` habille déjà `a` conformément au §8.2 (encre `--sauge-fonce`, soulignement permanent,
décoration `--laiton` 1 px, `text-underline-offset: .2em`, survol vers `--pin` à 2 px), et
`base.css` porte l'anneau de focus du §8.1.

Test de la décision 30 : tout ce dont ce lien a besoin décrit **ce qu'est** un lien, pas la place dont il
dispose ici. C'est la primitive, déjà écrite, nue, juste sur ce fond. **Zéro règle, zéro surcharge.**

Deux tentations écartées nommément, pour qu'aucun dev ne les rejoue :

- **une cible tactile de 44 px** (`padding-block`, comme `mtb-derniere-portee.css:219-222`) : **non**.
  Là-bas le lien est une action isolée ; ici c'est un lien **en ligne dans une phrase**. §12.10 rattache
  les 44 px aux §5.1, §6.7, §8.3 et §8.4 — **jamais au §8.2**. Un rembourrage vertical casserait
  l'interligne 1.65 et ferait chevaucher les lignes ;
- **un traitement de lien externe** (chevron du §8.6) : **non**, le §8.6 vise le lien pedigree LOF Select.
  Une sixième variante d'un dispositif transverse rejouerait T9.

### 7.8 360 px, zoom 200 %, éditeur

**Aucune `@media` dans la feuille.** Aucune largeur déclarée, aucune largeur fixe (a fortiori aucune
> 300 px), aucune taille en `vw` seul, aucune hauteur, aucun `overflow: hidden`. À 360 px :
`--marge-page` = 18 px → canal texte 324 px, l'encart consomme 32 + 32 + 6 = 70 px, **254 px de mesure**,
soit ≈ 30 signes à 17 px — étroit mais lisible, et **c'est le rembourrage que §5.1 prescrit** ; le réduire
par une requête média serait inventer une valeur absente de `MASTER.md`.

**L'éditeur obtient le même rendu sans une règle dupliquée** : la feuille arrive dans l'iframe par
`wp_enqueue_block_style()`, et `edit()` émet les **deux mêmes crochets**. **Zéro règle bornée à
l'éditeur, zéro modificateur `--editeur`.**

**T13 — confirmation explicite** : la feuille du bloc ne contient **aucune** règle d'état vide, aucun
`.mtb-etat-vide*`, aucune classe modificatrice d'état. Les trois crochets sont **nus** et vivent dans
`editor.css:126-168`, hors empreinte.

---

## 8. Comportement de l'éditeur

**Le nœud, et il ne se contourne pas.** Le champ est un `RichText` : il doit rester **monté en
permanence, à la même place dans l'arbre**, sinon React le remonte au premier caractère tapé et **le
curseur saute hors du champ**. Toute conception qui remplacerait le champ par l'encadré d'état vide
(branche `if/else`, bascule sur `isSelected`) produit exactement ce défaut. C'est ce qui tranche la
question, pas une préférence esthétique.

```
racine = useBlockProps()                         ← AUCUNE classe du thème
return el( 'div', racine,
    el( 'div', { className: 'mtb-bandeau-alerte' },
        el( RichText, { tagName: 'p', className: 'mtb-bandeau-alerte__message', … } )   ← TOUJOURS monté, TOUJOURS à cette place
    ),
    estVide ? el( EtatVide ) : null              ← frère POSTÉRIEUR de l'encart
)
save: function () { return null; }               ← une FONCTION ; « save: null » fait échouer l'enregistrement du bloc
```

`EtatVide` est déclaré **une seule fois, hors de `edit`** (sinon il est remonté à chaque frappe).
Ajouter ou retirer un nœud **après** un nœud stable ne le remonte pas : la garantie du `RichText` est
tenue par cette structure comme par l'autre.

**Zéro octet servi au visiteur — trois garanties cumulées** :

1. `editeur.js` n'est atteignable que par la poignée `mtb-bandeau-alerte-editeur`, référencée
   **uniquement** par `editorScript` ; le cœur ne la met en file que sur `enqueue_block_editor_assets` ;
2. aucune clé `viewScript` ni `script`, **aucun `wp_enqueue_script` nulle part** dans le module ;
3. `bootstrap.php` **n'a pas de garde `is_admin()`** — il ne fait qu'`wp_register_script` (enregistrer
   ≠ mettre en file, coût nul côté public) et `register_block_type`. **Le piège inverse est le vrai
   danger** : une garde `is_admin()` ferait disparaître le bloc du site tout en le laissant parfait dans
   l'éditeur, sur une page qui répond 200 (`bandeau-ouverture/bootstrap.php:17-26`).

**Dépendances du script : `wp-blocks`, `wp-element`, `wp-block-editor`** — trois, pas davantage. Ni
`wp-components` (aucun panneau), ni `wp-data` (aucune lecture d'entité), ni `wp-server-side-render`
(l'aperçu **est** le champ éditable ; un `ServerSideRender` coûterait un aller-retour REST à chaque
frappe pour afficher un texte que le navigateur possède déjà). Garde d'ouverture identique aux sœurs :
`if ( ! window.wp || ! wp.blocks || ! wp.element || ! wp.blockEditor ) { return; }`.

### 8.1 L'état vide — forme exacte, paiement de T13

```html
<div class="mtb-etat-vide"><span class="mtb-etat-vide__nom">Bandeau d'alerte</span><p class="mtb-etat-vide__phrase">Ce bloc n'affiche rien tant qu'aucun texte n'est renseigné.</p></div>
```

- racine `.mtb-etat-vide`, **aucune classe modificatrice, aucune classe locale** ;
- le nom dans un **`<span>`**, jamais un `<p>` : `editor.css:145` lui pose `display:block` et ne remet pas
  à zéro la marge basse qu'un `<p>` hérite de `base.css` — c'est précisément l'écart qui fait que les
  quatre conventions du lot 3 **ne sont pas identiques au pixel**, contrairement à ce que le journal
  affirme ;
- le nom en **casse naturelle** « Bandeau d'alerte » : les capitales sont posées par `text-transform`
  (`editor.css:157`), et des capitales littérales seraient **épelées lettre à lettre** par un lecteur
  d'écran ;
- la phrase **exactement** : « Ce bloc n'affiche rien tant qu'aucun texte n'est renseigné. » — forme
  imposée par §9.1 et libellé donné verbatim par le corps de l'issue ;
- **apostrophes droites**, comme `bandeau-ouverture` et `fiche-information`. Une seule forme dans le
  module.

**Ce module est le premier écrit après que la convention existe : il est la référence, pas la cinquième
variante.** Il ne corrige aucune sœur (empreinte).

---

## 9. Arborescence et ordre d'implémentation

```
wp-content/plugins/mtb-core/includes/blocks/bandeau-alerte/
├── block.json     déclaration (§3)
├── bootstrap.php  namespace MTB\Core\Blocks\BandeauAlerte ; UN SEUL require_once → rendu.php
│                  add_action( 'init', …, 20 ) ; AUCUNE garde is_admin()
├── rendu.php      LE SEUL fichier qui déclare des fonctions :
│                  est_vide(): bool · balises_admises(): array · protocoles_admis(): array
├── render.php     inclus par le cœur une fois par instance, par un « require » NU
│                  AUCUNE déclaration de fonction (« Cannot redeclare » ; la règle ne se négocie pas)
└── editeur.js     ES5, aucun JSX, aucune étape de construction
```

**Les trois fichiers PHP** portent `if ( ! defined( 'ABSPATH' ) ) { exit; }` **et**
`declare(strict_types=1)`. *(Rédaction corrigée le 2026-08-18 : la version gelée écrivait « les cinq
fichiers », ce qu'un `.js` et un `.json` ne peuvent évidemment pas porter. Sans conséquence sur le code
livré, qui était juste.)* Aucun fichier CSS dans l'extension (décision 28). Aucun `interface.php`.

**Ordre imposé — décision 27 : ne jamais commiter un `bootstrap.php` dont un `require` pointe un fichier
non écrit, sous peine d'erreur fatale sur tout le site, `wp-admin` compris.**

1. `rendu.php` · 2. `render.php` · 3. `editeur.js` · 4. `block.json` · 5. **`bootstrap.php` en dernier**
(`require_once rendu.php`, puis `wp_register_script`, **puis** `register_block_type( __DIR__ )` — la
poignée doit être connue à l'enregistrement) · 6. `mtb-bandeau-alerte.css` (thème) · 7. vérifications dans
la stack · 8. fiche d'aide.

### 9.1 Vérifications exigées avant clôture — à constater, jamais à supposer

| # | Ce qui est vérifié | Comment |
|---|---|---|
| V1 | Le bloc est **réellement trouvable** dans l'insérteur, panneau « Mont Brabant » | insérteur ouvert et **cliqué**, pas `is_registered` |
| V2 | La feuille est découverte : `mtb-bloc-mtb-bandeau-alerte` présent dans le HTML public **et** la feuille arrive dans la **toile** de l'éditeur (fond creusé + filet visibles) | `file_exists()` échoue en silence ; c'est le piège `all_deps()` du lot 3, jamais revérifié pour un bloc neuf |
| V3 | Message vide ⇒ **zéro octet** de balisage public | `curl` puis `grep -c "mtb-bandeau-alerte"` → **0** |
| V4 | Message renseigné ⇒ exactement les deux éléments du §4.1 | source de la page publique |
| V5 | **Zéro octet de JS public** | `grep -c "bandeau-alerte/editeur.js"` → **0** ; et le bandeau s'affiche à l'identique **JavaScript désactivé** |
| V6 | `className: false` retire bien `wp-block-mtb-bandeau-alerte` | source de la page |
| V7 | `Entrée` dans le champ **ne crée pas** un second bandeau ; le curseur **ne saute pas** au premier caractère tapé | frappe réelle, enregistrer, recharger |
| V8 | Un collage riche (titre + image + tableau) ne laisse que le texte et les liens | coller, enregistrer, recharger, **noter le résultat**, ne pas contourner |
| V9 | `<br>`, `&nbsp;`, espaces seuls ⇒ vides des **deux** côtés | saisir chaque cas, comparer encadré et page publique |
| V10 | **Un seul filet double**, et écart mesuré au navigateur sous un `h1`, un `h2`, un `hr` et un second bandeau : **≥ 48 px** dans les quatre cas | §2.1 est la seule audace du système ; la mesure est la seule preuve |
| V11 | Rembourrage haut et bas **égaux** (32 px), gauche = 32 px **après** le filet | prouve la neutralisation de `p` et la réserve du filet |
| V12 | 360 px **en iframe de même origine**, zoom 200 % à 1280 px ; message d'une ligne / de six lignes / avec une URL collée | Chrome sans interface ne descend pas sous ≈ 500 px : une capture directe mentirait |
| V13 | Lien dans le message : encre, soulignement, survol, **anneau de focus** — `getComputedStyle()` **identique** à un lien de prose ordinaire | preuve qu'aucune surcharge n'a été introduite en douce |
| V14 | L'encadré d'état vide est **identique au pixel** à celui de `bandeau-ouverture` | capture des deux, superposition |
| V15 | Aucune notice, aucun `_doing_it_wrong`, aucun avertissement de console | `debug.log` **sur une page rendue** — décision 29 : `WP_DEBUG` est faux en WP-CLI |
| V16 | D6 : zéro origine tierce ajoutée, public **et** administration | onglet Réseau, filtre « domaine ≠ localhost » |
| V17 | **Octets réels** de la feuille et du HTML (D8, « chiffres à l'appui ») | mesurer, **ne pas recopier l'estimation du §10** |

---

## 10. Poids

| Élément | Disque (estimé) | Servi au **visiteur** | Servi à l'**éleveuse** |
|---|---|---|---|
| `block.json` · `bootstrap.php` · `rendu.php` · `render.php` | ≈ 8,9 ko | 0 | 0 |
| `editeur.js` | ≈ 5,5 ko (≈ 1,9 ko compressé) | **0** | ≈ 5,5 ko, éditeur seulement, mis en cache |
| `mtb-bandeau-alerte.css` | ≈ 2,2 ko (dont ≈ 480 o utiles) | servie **uniquement sur les pages portant le bloc**, probablement **en ligne** (`path` fourni) | idem |
| HTML ajouté | — | **0 o** message vide ; ≈ **120 o** + le message sinon | — |
| Police, image, icône, origine tierce | — | **0** | **0** |

Page à six composants **mesurée** au lot 3 : 167 627 o → **≈ 169 950 o**, soit **≈ 30 Ko sous les
200 000 o** du BRIEF §12. Deux fichiers de police, inchangé.

---

## 11. Chaînes fournies par le serveur

**Le thème n'en compose aucune.**

| Chaîne, verbatim | Où | Visible par |
|---|---|---|
| `Bandeau d'alerte` | `title` du bloc **et** `mtb-etat-vide__nom` (casse naturelle) | l'éleveuse |
| `Message temporaire (« portée à venir », « pas de chiots actuellement »).` | `description`, insérteur et onglet « Bloc » | l'éleveuse |
| `Le message à afficher…` | texte d'invite du `RichText` | l'éleveuse |
| `Ce bloc n'affiche rien tant qu'aucun texte n'est renseigné.` | `mtb-etat-vide__phrase` | l'éleveuse |
| `Le texte du message s'affiche ici.` | `example` du `block.json`, aperçu d'insérteur | l'éleveuse |
| **aucune chaîne publique** | — | **le texte public est intégralement celui de l'éleveuse : ni préfixe, ni étiquette, ni ponctuation ajoutée** |

---

## 12. Interdits

**Le thème ne fera jamais** :

- interroger la base (`WP_Query`, `get_posts`, `get_post_meta`, `get_terms`, `$wpdb`) ni appeler
  `MTB\Core\*` — le grep de frontière du contrat #2 reste à zéro ;
- **ajouter un mot au bandeau** : aucun préfixe « Information : », « Attention : », « Alerte : » en
  `::before` ni ailleurs. Le préfixe du §9.5 appartient à la **confirmation d'envoi** ; un `content:`
  porteur de mot serait une chaîne du domaine composée par le thème ;
- ajouter une icône, un pictogramme, une forme dessinée (§13) ;
- introduire une seconde couleur, une bordure `--oxyde`, un fond hors des cinq du §3.2, une paire
  encre/fond absente du §12 ;
- poser `role`, `aria-live`, `<aside>` ou un titre par du CSS ou un script ;
- écrire une règle nouvelle pour `.mtb-etat-vide*` ou lui inventer un modificateur ;
- masquer le bloc en CSS quand il est vide — **il n'est pas rendu du tout**, il n'y a rien à masquer ;
- décider de ce qu'est « un message vide » : le serveur tranche ;
- s'accrocher à `wp-block-mtb-bandeau-alerte` (absente, `className: false`) ;
- poser une ombre, un arrondi > 2 px, un dégradé autre que `--filet-double-v`, une largeur fixe, une
  taille en `vw` seul, une valeur brute hors de la liste close de la feuille (mots-clés seuls : `left`,
  `top`, `no-repeat`, `100%`, `0`) ;
- toucher `theme.json`, `functions.php`, `tokens.css`, `base.css`, `editor.css`, `parts/`, `templates/`,
  `patterns/`.

**L'extension ne fera jamais** :

- émettre une règle visuelle, un `style=""`, une propriété personnalisée en ligne, ou mettre une feuille
  en file (décision 28 — ne pas rejouer T15) ;
- émettre un titre (`h1`–`h6`), un `<hr>`, un `<blockquote>`, un `<figure>`, une classe `alignwide` /
  `alignfull` / `has-*-color` ;
- émettre un chevron de lien externe ni un texte caché « (nouvelle fenêtre) » : elle ne peut pas savoir à
  l'octet près ce qui est « externe » sans composer une chaîne ;
- toucher `includes/blocks/categorie-mtb/` ni aucun autre dossier de `includes/blocks/` ;
- ajouter une capacité, un rôle, une route REST, un `register_rest_field`.

**Personne ne fera jamais** : écrire `<!-- wp:mtb/bandeau-alerte {"message":"…"} /-->` dans un fichier de
`templates/` ou de `patterns/`. Cela graverait un message **temporaire** dans un fichier que Fabienne ne
peut pas éditer : la règle d'or enfreinte, pas un conseil.

---

## 13. Ce que l'éleveuse gagne, ce qu'elle n'obtient pas

**Chemin de clic, à recopier dans la fiche d'aide (D3) :**

1. **Pages** → ouvrir la page concernée (l'accueil, le plus souvent).
2. Bouton **+** en haut à gauche, ou taper `/` sur une ligne vide.
3. Panneau **« Mont Brabant »**, premier de la liste → **« Bandeau d'alerte »**. Elle peut aussi taper
   « alerte », « message » ou « annonce ».
4. Le bloc s'insère **avec le curseur déjà dedans** : elle tape sa phrase.
5. Facultatif : sélectionner un ou plusieurs mots → bouton **lien** → choisir une page du site. C'est le
   **seul** enrichissement disponible — ni gras, ni italique, ni couleur, ni taille.
6. **Publier** / **Mettre à jour**.
7. **Pour le retirer** : cliquer le bloc → menu `⋮` → **Supprimer**. Ou effacer tout le texte : le bloc
   reste dans la page, l'encadré d'éditeur explique que rien ne s'affiche, et **le visiteur ne voit
   rien**.

**Elle n'obtient pas, délibérément** (§14) : aucune couleur, taille, graisse, espacement, alignement,
niveau de gravité ni icône. Elle ne peut pas poser ce bloc dans une **fiche** de portée, de chien ou de
résultat — ces écrans sont des écrans de saisie classiques (décision 24) : « les pages se composent, les
fiches se remplissent ». **À porter dans la fiche d'aide.**

**Un point à écrire honnêtement dans la fiche** : le bloc est **par page**. Pour afficher le même message
sur l'accueil **et** sur Placement, elle le tape deux fois. C'est une tension réelle avec la contrainte 3,
assumée : un message temporaire se pose là où il a du sens (voir Q-#9-1 au §14).

---

## 14. Arbitrages rendus — chaque désaccord, la décision, sa raison

| # | Désaccord entre les deux plans | Décision | Raison |
|---|---|---|---|
| **A1** | **Phrase de l'état vide.** Back : « …tant qu'aucun **texte n'est renseigné**. » Front : « …tant qu'aucun **message n'est saisi**. » | **Back** | Le corps de l'issue donne le libellé **verbatim**. Un libellé destiné à l'éleveuse ne se reformule pas en chaîne de dev. Deux formulations pour la même phrase, c'est T13 rejouée au premier composant censé la payer. |
| **A2** | **Élément portant le nom.** Back : `<span class="mtb-etat-vide__nom">`. Front : `<p>`. | **Back — `<span>`** | `editor.css:145` pose `display:block` sur ce crochet et **ne remet pas à zéro** la marge basse qu'un `<p>` hérite de `base.css`. C'est exactement l'écart qui rend les quatre conventions du lot 3 non identiques au pixel. Le `<span>` est la forme canonique. |
| **A3** | **Place de l'état vide dans l'éditeur.** Back : à l'intérieur de la racine `.mtb-bandeau-alerte`, avec un crochet `data-vide="oui"` pour neutraliser l'habillage. Front : **frère** de l'encart, sous une enveloppe `useBlockProps()` sans classe de thème, sans `data-*`. | **Front**, `data-vide` **supprimé** | `.mtb-etat-vide` porte lui-même un fond `--calcaire-creux` et un contour tireté `--laiton` : imbriqué dans l'encart, il pose **une surface creuse dans une surface creuse identique**, et son tireté se lit comme la bordure de l'encart — ce que §5.3 et §5.4 excluent. La garantie de non-remontage du `RichText` est tenue par les deux structures (le nœud conditionnel reste postérieur à un nœud stable), donc l'argument de back n'est pas perdu. `data-vide` disparaît avec le besoin qu'il servait : un attribut que personne ne consomme est du balisage mort. |
| **A4** | **Échappement du message.** Back : `wp_kses()` à liste blanche étroite (`a` + `br`, 4 protocoles). Front (C3) : `wp_kses_post()`. | **Back** | `wp_kses_post()` admet `img`, `table`, `h2`, `ul`, `figure` : un collage ferait **survivre une image ou un tableau dans un encart d'alerte** — une décision visuelle prise par un collage, ce que §14 retire à l'éditrice. Pire, `h2` / `hr` / `blockquote` porteraient un **second filet double** dans le bloc, que le front lui-même déclare interdit à son §3.3(a). Les deux moitiés voulaient la même chose ; seule la liste étroite l'obtient. |
| **A5** | **Détection du vide.** Back : `wp_strip_all_tags` + `html_entity_decode` + `\p{Z}\p{C}`. Front (C3) : `trim( wp_strip_all_tags( … ) )`. | **Back** | `trim()` ne voit ni `&nbsp;`, ni U+00A0, ni U+202F — l'espace fine insécable que le clavier français pose devant un « ! ». Un bandeau vide s'afficherait alors au visiteur : D12 manquée. |
| **A6** | **Texte d'invite du champ.** Back : « Le message à afficher… ». Front : proposition « Votre message ». | **Back** | Le serveur possède les chaînes ; le thème n'en compose ni n'en propose aucune. Aucun des deux n'est au §10.3 : on retient celui du côté qui l'émet, et on n'en invente pas un troisième. |
| **A7** | **Où vit l'appel `wp_kses()`.** Relevé par `dev-back-mtb` pendant l'implémentation : le §6 écrivait « `wp_kses()` **dans `rendu.php`**, appelée par `render.php` », alors que le §9 ferme la liste des fonctions de `rendu.php` à **trois**. Les deux ne sont satisfaisables ensemble qu'en ajoutant une quatrième fonction. | **§9 l'emporte** : les deux listes blanches vivent dans `rendu.php`, **l'appel `wp_kses()` est nu dans `render.php`** | C'est la forme littérale du code donné au §6.1, et elle évite une enveloppe nommée qui n'ajouterait rien. `wp_kses` est reconnue comme fonction d'échappement par le sniff : aucun `phpcs:ignore` n'est requis sur cet argument. **Écart correctement remonté plutôt que décidé en silence.** |

**Points sur lesquels les deux plans étaient déjà d'accord et qui sont gelés sans arbitrage** : nom du
bloc, rendu serveur, attribut unique `message`, `allowedFormats: ['core/link']`, `multiple: false`,
`className: false`, canal texte sans `grid-column`, aucun `role` / `aria-live` / titre / `<aside>`, aucune
fonction de lecture, aucune feuille servie par l'extension, zéro octet de JS public, état vide public =
rien du tout.

---

## 15. Dettes et points remontés — ne pas les redécouvrir dans trois lots

| # | Point | Pour qui |
|---|---|---|
| **T19** | **`MASTER.md` §2.1 déclare close une liste d'emplacements du filet double qui ne contient ni cet encart, ni les deux encarts du §9.5 (page protégée, confirmation d'envoi) qui l'emploient déjà.** `MASTER.md` se contredit **avant** cette issue. Le patron du §9.5 a été appliqué ; l'inscription formelle des trois emplacements dans la liste du §2.1 reste à faire. Aucune ligne de code n'en dépend. | **révision `lead-design-mtb`** |
| **T20** | **`--pin` sur `--calcaire-creux` = 12,75:1 (calculé), absente du tableau §12.3.** Elle est sûre — largement AAA — mais le préambule du §12 est catégorique : « une paire absente de ce tableau est une paire interdite ». Elle est atteinte **par héritage de `base.css:257`** sur tout lien survolé posé dans une surface creusée : elle est donc partagée avec l'issue sœur #10. Aucune règle n'a été écrite à ce sujet et rien ne s'en autorise. | **révision `lead-design-mtb`** |
| **T21** | **La recette de l'encart creusé à filet vertical s'écrit deux fois en parallèle et en aveugle** — ici (§7.3) et dans l'encart d'appel (#10), à qui §2.1 accorde le même filet et §3.2 le même fond. Si les deux feuilles divergent d'un jeton, le site portera **deux encarts qui devraient être frères et ne le seront pas** : T9 rejouée sur une apparence que `MASTER.md` décrit une seule fois. **Aucune primitive `.mtb-encart` n'est inventée ici** : `MASTER.md` ne la nomme pas et la décision 30 réserve la classe nue aux primitives qu'il nomme. Un hissage ultérieur ne coûtera rien **si les deux feuilles sont littéralement identiques** — la recette du §7.3 est écrite pour être comparée ligne à ligne. | **`/lead-mtb`**, au niveau du lot |
| **T13** | **Payée par cette issue, pour ce composant seulement.** Les quatre conventions d'état vide du lot 3 subsistent : `bandeau-ouverture/editeur.js:74` émet bien le `<span>` canonique, mais `fiche-information/editeur.js:107-111` émet un `<p>` **et** une classe locale, `liste-portees/editeur.js:67-75` un `<p>` **et deux** classes locales, `derniere-portee/editeur.js:33-34` un `<p>` **et** un modificateur `mtb-etat-vide--derniere-portee`. Les trois qui emploient `<p>` reçoivent en plus la marge basse de `base.css` que `editor.css:167` ne neutralise que sur `__phrase` : **elles ne sont donc pas identiques au pixel**, contrairement à ce qu'affirme le journal du lot 3. **Ce module n'en corrige aucune** (empreinte). | une passe d'alignement, hors lot parallèle |
| **T15** | Non rouverte, non imitée : l'extension ne sert aucune feuille. | inchangé |
| **Q-#9-1** | **Le message d'alerte doit-il apparaître sur toutes les pages en une seule saisie, ou page par page ?** Fait d'usage, non de code : rien n'attend la réponse. Si « toutes les pages », la réponse technique n'est **pas** ce bloc mais une insertion dans l'en-tête du site — donc **#18** et la dette **T1** (elle n'a pas `edit_theme_options`). | **l'éleveuse** |
| **Q-#9-2** | **« Portée à venir » est-il un état de portée plutôt qu'un message tapé ?** Une quatrième valeur de disponibilité toucherait §3.3, §10.2, la décision 21, le badge et la fiche de guide de la portée — et supposerait un fait d'élevage non vérifié (annonce-t-elle une portée avant la naissance, et sous quels mots ?). Lié à **Q13** (date de saillie), toujours ouverte. **Non comblé, non déduit.** | **l'éleveuse** |
| **Q-#9-3** | **Collision de vocabulaire : « Bandeau d'alerte » (BRIEF §6) contre « Bandeau d'ouverture » (§6.5, livré et documenté)**, qui désigne l'exact contraire — une bande photo pleine fenêtre. Le nom attesté est **conservé** : aucun remplaçant n'est inventé. Si un jour il est arbitré, la réponse se répercute dans `MASTER.md` §10.3, dans le `block.json` et dans la fiche d'aide. | **l'utilisateur** |
| **Q-#9-4** | **RÉPONDUE par la mesure de `dev-integration-mtb`, 2026-08-18.** `allowedFormats: ['core/link']` **ne retire ni `<strong>`, ni `<em>`, ni `<img>`** d'un collage : ils restent dans l'attribut enregistré. Deux conséquences, mesurées : **(a) le public est propre** — `wp_kses` les retire tous, zéro origine tierce sur la page publique, le contrat tient intégralement ; **(b) la toile de l'éditeur charge réellement une image externe collée** (console : `ERR_NAME_NOT_RESOLVED` sur `https://exemple.invalid`). **Décision : on ne corrige pas.** Aucune propriété de `RichText` ne retire `<img>` sans retirer aussi le lien (`disableFormats`), et un assainissement dans `onChange` se battrait contre le cœur au prix du curseur. Surtout, **c'est le périmètre exact de la décision 15** : D6 est tenue pour le visiteur et bornée explicitement à lui ; l'administration charge déjà 15 images de `s.w.org`, assumées. Une requête tierce déclenchée par un collage volontaire de l'éleveuse relève de la même borne. **À regarder en revue.** | consigné — **`review-mtb`** |
| **F-#9-1** | **Fait mesuré à l'implémentation, consigné plutôt que découvert plus tard : un protocole refusé n'est pas retiré, il est *dégradé*.** `<a href="ftp://x.fr">…</a>` collé ressort en `<a href="//x.fr">…</a>` — une adresse **relative au protocole**, donc un lien externe vivant. C'est le comportement de `wp_kses_bad_protocol` du cœur, **pas une faiblesse de la liste blanche du §6.1** : `javascript:` est bien neutralisé dans les deux cas. **Décision : on ne corrige pas.** L'éleveuse peut déjà poser un lien externe légitime en `https`, D6 porte sur les requêtes émises par la page et non sur les liens qu'un visiteur choisit de suivre, et lutter contre le cœur ici coûterait plus que le défaut. Ce qui est réellement perdu est l'intention exacte d'un collage exotique. **À regarder en revue**, pas à combler en silence. | consigné — **`review-mtb`** |
