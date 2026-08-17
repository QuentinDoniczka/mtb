# Contrat d'interface — Issue #7 — Composant « Fiche d'information »

**Gelé le 2026-08-17 par `lead-issue-mtb`. Contraignant à partir de maintenant.**

Premier composant du catalogue (BRIEF §6) et **premier bloc du projet** : `includes/blocks/` était vide.
Ce contrat sert donc deux fois — il gèle ce composant, et il établit les précédents que les neuf
composants suivants suivront. Les points marqués **⚑ précédent** sont écrits pour être recopiés.

Il réconcilie deux plans écrits **en aveugle** par `leaddev-back-mtb` et `leaddev-front-mtb`. Ils
divergeaient sur **cinq points de balisage**, dont deux auraient produit une **panne silencieuse** :
la page rend, tout a l'air normal, et le réglage de l'éleveuse n'a aucun effet. Les arbitrages sont en
fin de document.

---

## Périmètre d'écriture de l'issue — rien en dehors

```
wp-content/plugins/mtb-core/includes/blocks/fiche-information/**
wp-content/themes/mtb/assets/css/blocs/mtb-fiche-information.css     ← un seul fichier, neuf
docs/contracts/issue-7.md
docs/guide/composant-fiche-information.md
```

**Six chaînes tournent en parallèle** (#6, #7, #8, #12, #13, #14), arbre de travail unique, **aucune
branche**. Un fichier écrit hors périmètre est irrécupérable.

**Interdits nommément, et ce ne sont pas des précautions théoriques** :
`assets/css/base.css` (**la chaîne #6 le possède dans ce lot**) · `theme.json` · `functions.php` ·
`style.css` · `assets/css/tokens.css` · `assets/css/editor.css` · `templates/**` · `parts/**` ·
`patterns/**` · `assets/css/blocs/core-button.css` (dette T7, **non attribuée** — voir §11) ·
`includes/blocks/categorie-mtb/` (**non attribuée** — voir §10) · tout autre dossier de
`includes/blocks/` · `mtb-core.php` · `includes/class-loader.php`.

**Pourquoi le fichier CSS est dans le thème et non dans l'extension.** L'empreinte initiale de l'issue
plaçait les styles « dans le dossier du bloc ». C'est en contradiction avec deux contrats gelés :
`issue-1.md` §8 et §13 (« **l'extension n'émet aucune règle visuelle ni mise en page** … le thème
habille ») et `issue-2.md`, qui place le CSS d'un bloc dans `assets/css/blocs/<espace>-<nom>.css` servi
par la **boucle générique** de `functions.php:194-224`. Le fichier retenu est **neuf et au nom unique du
bloc** : aucune chaîne sœur ne peut le nommer, `assets/css/blocs/` ne contenait qu'un `.gitkeep`, et
**aucun fichier partagé n'est touché**. Extension d'empreinte d'exactement un fichier, à risque de
collision nul, contre une infraction que `review-mtb` aurait remontée.

---

## Approche retenue — non rouvrable

**Bloc conteneur : structure fermée, prose ouverte.**

| Élément | Mécanisme | Pourquoi pas autrement |
|---|---|---|
| Titre | attribut fermé, `RichText` en ligne dans le canevas | `core/heading` laisse choisir `h1`→`h6`. `templates/singular.html` rend déjà `wp:post-title {"level":1}` : un second `h1` casse BRIEF §11, **bloquant**. Et `h4` est une **étiquette** (MASTER §4.5), `h5`/`h6` sont sans style dans `base.css` — trois niveaux sur six sont des pièges. |
| Prose | `InnerBlocks`, `allowedBlocks` = **`core/paragraph`, `core/list`**, rien d'autre | Un `RichText multiline` fermé retirerait les **listes à puces**. `docs/migration/` **n'existe pas encore** : personne n'a lu les pages sources. Le prix de se tromper n'est pas symétrique — se tromper vers l'ouvert coûte du JavaScript, se tromper vers le fermé coûte une **perte de structure éditoriale à la reprise** (contrainte 4, D4). C'est aussi un mécanisme **déprécié** de Gutenberg. |
| Photo | attributs fermés | `core/image` offre l'alignement gauche/droite, donc le `float` que MASTER §6.8 interdit et que §14 met hors de portée de l'éleveuse. |
| Étape de build | **aucune, jamais** | Contrat #1, arbitrage 2. `editeur.js` en `wp.element.createElement`, aucun JSX, aucun `package.json`. |

**Options écartées, pour qu'on ne les re-litige pas.** *Pas de bloc du tout, on habille les blocs du
cœur* : défendable — `theme.json` verrouille déjà tout le visuel, la protection n'est pas ce qu'un bloc
achète — mais MASTER §9 et §9.1 décrivent une apparence d'état vide « identique pour les dix composants
du catalogue » qu'**aucun bloc du cœur ne peut porter**, et retirer une ligne du catalogue du brief est
une décision de produit, pas d'implémentation. *Une composition (`pattern`)* : trois clics et un aperçu,
mais **une composition ne survit pas à son insertion** — il ne reste que des blocs libres, rien ne tient
la structure, aucun état vide n'est possible, et le composant cesse de se retirer d'un geste (BRIEF §3).

---

## 1. Fonctions de lecture exposées par l'extension

**Aucune.** Ce composant n'affiche **que ce que l'éleveuse a saisi dans le bloc**. Il ne lit aucune
portée, aucun chien, aucun résultat ; il ne déclare aucune fonction `mtb_*` et ne touche pas
`includes/query/`.

Trois corollaires contraignants :

- La clause `function_exists()` de l'**amendement 3 du contrat #2** reste **vacante** pour cette issue.
- Aucune fonction homonyme n'est déclarée, donc **aucun risque d'ombrage silencieux** (décision 19).
- Le test de recette de #2 reste vrai : `plugin deactivate mtb-core` fait **disparaître le bloc**, pas
  casser la page. Voir §8.

**Aucun type de contenu, aucune taxonomie, aucune clé de méta, aucune option.** ⚑ **précédent** : un
composant de mise en page n'est pas une donnée d'élevage. Son contenu appartient à la Page qui le
porte et doit voyager avec elle — copie, révision, export, restauration. Le stockage est celui du
cœur : les attributs sérialisés dans le commentaire de bloc de `post_content`, plus l'`InnerBlocks` en
HTML de blocs. Une méta imposerait une clé indexée par occurrence, et une révision de page ne
restaurerait pas le texte.

---

## 2. Bloc enregistré

`mtb/fiche-information` · `apiVersion: 3` · titre affiché **« Fiche d'information »** ·
catégorie `mtb` (« Mont Brabant ») · icône `media-text` · **rendu côté serveur** (`render.php`).

> **⚠️ Le nom du bloc est l'item le plus porteur de ce contrat.** La boucle générique
> (`functions.php:202-203`) transforme `espace/nom` en `espace-nom.css`. Un écart — `mtb/fiche-info`,
> `mtb/fiche_information`, `mtb-core/fiche-information` — et **aucune feuille n'est servie, sans erreur
> ni avertissement** : la photo s'affiche brute, sans ratio ni cerne, les listes sont collées, la page
> répond 200. C'est la panne silencieuse la plus coûteuse du dispositif et elle ne se voit qu'à l'œil.
> Le nom est gelé au caractère près.

### Attributs — liste close

| Clé | Type | Défaut | Valeurs admises |
|---|---|---|---|
| `titre` | `string` | `""` | HTML ; formats autorisés **`core/bold`, `core/italic`, `core/link`** et rien d'autre |
| `niveau_titre` | `string` | `"h2"` | `enum: ["h2","h3"]` |
| `photo_id` | `number` | `0` | identifiant de pièce jointe ; `0` = aucune photo |
| `photo_description` | `string` | `""` | texte simple — le texte alternatif |
| `photo_legende` | `string` | `""` | texte simple |
| `cadrage` | `string` | `"centre"` | `enum: ["haut_gauche","haut","centre","haut_droite","bas"]` |
| `position_photo` | `string` | `"dessus"` | `enum: ["dessus","dessous"]` |

- **`snake_case` français.** Les clés de `cadrage` sont **recopiées de `includes/content/chien/choix.php:79-94`**, déjà gelées par l'issue #4 : deux vocabulaires pour le même réglage serait un défaut de conception.
- **Aucun attribut n'a de `source` ni de `selector`.** Structurant : les attributs vivent dans le JSON du commentaire de bloc, donc `save()` n'émet **que** l'`InnerBlocks`, donc `$content` ne contient **que** la prose. Un `source: "html"` sur le titre le ferait apparaître dans `$content` et il serait rendu deux fois.
- **Aucun attribut `photo_url`.** Ce serait une donnée recopiée qui périme au premier remplacement de fichier. Le canevas lit l'URL par `getMedia()`, le rendu public par `wp_get_attachment_image()`.
- **La liste blanche de formats est une garde dure** : elle rend inatteignable le format « couleur du texte », parent de la dette T7, quoi que fasse un futur `theme.json`.

### `supports` — tout ce qui est visuel est à `false`

```
align: false · alignWide: false · anchor: false · ariaLabel: false · className: false
customClassName: false · html: false · lock: false · renaming: false · reusable: false
splitting: false · layout: false · position: false · border: false · shadow: false
inserter: true · multiple: true
color:      { background:false, text:false, link:false, gradients:false, button:false,
              enableContrastChecker:false }
typography: { fontSize:false, lineHeight:false, textAlign:false }
spacing:    { margin:false, padding:false, blockGap:false }
dimensions: { aspectRatio:false, minHeight:false }
background: { backgroundImage:false, backgroundSize:false }
filter:     { duotone:false }
```

- **`className: false` → la classe `wp-block-mtb-fiche-information` n'existe pas.** Le thème ne doit pas la styler. La racine est émise par `get_block_wrapper_attributes( array( 'class' => 'mtb-fiche-information' ) )`.
- **`align: false`** : aujourd'hui `alignwide`/`alignfull` **ne font rien** dans une Page (§9). Offrir un bouton inopérant est un mensonge d'interface. Rouvrable d'une ligne quand les gabarits lèveront le plafond.
- **Les verrous font doublon avec `theme.json`, et c'est voulu.** ⚑ **précédent** : `theme.json` appartient au thème ; le bloc doit rester verrouillé s'il change.
- **Pas de `$schema`** dans `block.json` : la clé est inerte à l'exécution mais écrirait une URL `schemas.wp.org` qu'un IDE irait chercher. Zéro origine tierce.
- **Pas de `textdomain`.** Renseignée, elle fait appeler `translate_with_gettext_context()` par le cœur sur `title`, `description` et `keywords`. Contrat #1 §7 gèle « aucune fonction i18n nulle part ». L'en-tête `Text Domain:` de `mtb-core.php` reste — inoffensif et exigé par #1.
- **Pas de `style`, `editorStyle`, `viewStyle`, `viewScript`, `script`.** Le CSS vit dans le thème.
- **`editorScript` porte une POIGNÉE** — `mtb-fiche-information-editeur` — jamais `file:./editeur.js`, sinon WordPress cherche un `editeur.asset.php` et émet un `_doing_it_wrong` (contrat #1 §10, piège 1). Motif déjà éprouvé dans le dépôt : `includes/fields/portee/bootstrap.php:164-172`.
- **`example` : livré, et il ne référence aucune image.** `photo_id` reste à `0`. Les deux chaînes — « Titre de la fiche » et « Un paragraphe de présentation. » — ne contiennent **aucun fait d'élevage** : pas de nom de chien, pas de date, pas de résultat, pas de nom de race. **Zéro requête vers `s.w.org`** : la décision 15 de `ETAT.md` compte 15 images en administration, ce module en ajoute **zéro**.

### Arborescence du module

```
includes/blocks/fiche-information/
├── bootstrap.php   namespace MTB\Core\Blocks\FicheInformation
│                   À L'INCLUSION, uniquement : garde ABSPATH,
│                   require_once __DIR__ . '/rendu.php',
│                   add_action( 'init', …, 20 )
│                   Dans le rappel, DANS CET ORDRE :
│                     wp_register_script( 'mtb-fiche-information-editeur', … )
│                     PUIS register_block_type( __DIR__ )
├── block.json
├── editeur.js      wp.blocks.registerBlockType + wp.element.createElement
├── rendu.php       fonctions d'aide, déclarées UNE SEULE FOIS
└── render.php      inclus par WordPress à chaque instance — PUREMENT PROCÉDURAL
```

> **⚑ précédent, et c'est le piège le plus dangereux du module.**
> `register_block_type_from_metadata` construit un `render_callback` qui fait
> `ob_start(); require $template_path; return ob_get_clean();` — un **`require`**, pas un
> `require_once`, sans quoi une deuxième instance du bloc sur la même page ne rendrait rien.
> `render.php` est donc **inclus autant de fois qu'il y a de fiches sur la page**. Toute déclaration
> `function` qui y figurerait produirait un `Cannot redeclare` — un `E_COMPILE_ERROR` que le
> `try/catch` du chargeur **n'attrape pas** (contrat #1 §12) : **deux fiches dans une page = site par
> terre.** D'où `rendu.php` séparé. `render.php` ne contient **aucun `function`**.
> Le montage est sûr dans les deux cas ; le sens de `require` vs `require_once` est à confirmer (V5).

`declare(strict_types=1);` dans les quatre fichiers PHP · WordPress Coding Standards · **`array()` et
jamais `[]`** · conditions de Yoda · tabulations · pas de `?>` · plafond **PHP 8.1** · docbloc
`@package MTB\Core` · **aucune fonction i18n** · aucune variable globale · aucun
`flush_rewrite_rules()` · jamais `init` 99 · aucun appel HTTP sortant.

---

## 3. Balisage rendu — littéral, et c'est le cœur du contrat

### Côté public (`render.php`) — photo au-dessus du texte, cas par défaut

```html
<div class="mtb-fiche-information" data-position="dessus">
	<h2 class="mtb-fiche-information__titre">…</h2>
	<figure class="mtb-fiche-information__figure">
		<div class="mtb-fiche-information__photo" data-cadrage="centre">
			<img class="mtb-fiche-information__image" src="…" srcset="…" sizes="…"
			     width="…" height="…" alt="…" decoding="async" loading="lazy">
		</div>
		<figcaption class="mtb-fiche-information__legende">…</figcaption>
	</figure>
	<div class="mtb-fiche-information__prose"><p>…</p><ul><li>…</li></ul></div>
</div>
```

**Photo sous le texte** : la `<figure>` passe **après** `__prose` dans le DOM, la racine porte
`data-position="dessous"`. **Deux positions de balisage, jamais un `order` CSS.**

**Le titre est toujours en premier.** « Photo au-dessus du texte » signifie au-dessus des
**paragraphes**, jamais au-dessus du titre. Gelé, et dit tel quel dans la fiche d'aide.

### Crochets de classes — la liste close

| Crochet | Élément | Présent quand | Ce qui casse s'il manque |
|---|---|---|---|
| `mtb-fiche-information` | `<div>` racine | dès que le bloc rend quelque chose | plus de rythme vertical : le bloc se colle au voisin, et deux fiches empilées peuvent enfreindre l'écart de `--e-7` entre deux filets doubles (MASTER §2.1) |
| `mtb-fiche-information__titre` | `<h2>` ou `<h3>` | titre non vide | un titre à jeton long provoque un **défilement horizontal à 360 px** — échec dur de BRIEF §11 |
| `mtb-fiche-information__figure` | `<figure>` | la pièce jointe existe | photo et texte se touchent |
| `mtb-fiche-information__photo` | `<div>`, **l'emplacement** au sens MASTER §6.2 | avec la figure | **le pire cas** : plus de ratio 3/2 (§6.1), plus de cerne (§6.6, obligatoire sur **toute** photo), plus de fond `--calcaire-creux`, plus de cadrage |
| `mtb-fiche-information__image` | `<img>` | avec la figure | rien pour le thème — voir la note ci-dessous |
| `mtb-fiche-information__legende` | `<figcaption>` | légende non vide **et** photo présente | la légende touche le cerne |
| `mtb-fiche-information__prose` | `<div>` | prose non vide | toute liste est **collée** au texte qui la suit (`base.css` met `ul, ol` à `margin: 0`) |
| `mtb-fiche-information--editeur` | racine | **éditeur seul** | les règles d'atelier ne sont plus bornées à l'éditeur |
| `mtb-fiche-information__etat-vide` | `<div>` | éditeur seul, bloc entièrement vide | l'état vide s'affiche en texte nu : lisible, non conforme à MASTER §9.1 |
| `mtb-fiche-information__etat-vide-nom` | `<p>` | avec l'état vide | ligne 1 indistinguable de la ligne 2 |
| `mtb-fiche-information__etat-vide-phrase` | `<p>` | avec l'état vide | aucune règle ne s'y attache ; exigé pour la symétrie et pour `review-mtb` |

**Convention gelée, étendue d'une forme.** Le contrat #2 gèle `mtb-<bloc>` et `mtb-<bloc>__<element>`.
J'y ajoute **`mtb-<bloc>--<modificateur>`** pour les modificateurs **de racine** (ici `--editeur`).
⚑ **précédent** : il n'existe **aucun modificateur d'élément** — une variation d'élément passe par un
attribut `data-`, jamais par une troisième forme de classe (§4).

**Note sur `__image`.** Le thème cible l'image par `.mtb-fiche-information__photo > img`, **forme
littérale de MASTER §6.2**, et **ne dépend pas** de cette classe. Elle existe pour une seule raison :
`wp_get_attachment_image()` émet `attachment-large size-large` par défaut, et passer un `class` explicite
les **déplace**. Le thème ne doit ni styler `attachment-large`, ni `size-large`, ni s'appuyer sur
`__image`.

**Trois engagements fermes.** `.wp-block-mtb-fiche-information` **n'existe pas** · `attachment-large` et
`size-large` **ne sont pas émis** · **l'ordre du DOM est l'ordre visuel**, aucun `order` n'est nécessaire
et aucun n'est souhaité.

**Aucun conteneur vide n'est jamais émis** : ni `<figure>` sans image, ni `<figcaption>` vide, ni
`__prose` vide, ni `<h2></h2>`.

**Aucun attribut `style`, sur aucun élément, ni dans `render.php` ni dans `edit()`.**

### La racine est un `<div>`

**Pas de `<section>`, aucun `role`, aucun `aria-labelledby`.** Un `<section>` nommé créerait un
*landmark region* par fiche : sur la page BHPL, six régions à traverser, là où la navigation canonique
est déjà celle des titres. MASTER ne prescrit aucun landmark pour ce composant ; en inventer un serait
un ajout non ratifié.

### Côté éditeur (`edit()`)

Même structure et mêmes crochets qu'en public quand le bloc a du contenu — un écran d'édition qui ne
ressemble pas au site est un composant employé de travers. Trois écarts, tous intentionnels :

1. La racine porte **`mtb-fiche-information--editeur`**, toujours.
2. `__titre` et `__prose` sont **toujours présents même vides** : c'est la surface de saisie.
3. L'encart d'état vide est **ajouté** quand le bloc ne rendrait rien côté public — jamais substitué,
   sinon elle ne pourrait plus taper.

```html
<div class="mtb-fiche-information mtb-fiche-information--editeur">
	<h2 class="mtb-fiche-information__titre">…RichText, texte fantôme « Titre de la section »…</h2>
	<div class="mtb-fiche-information__prose">…InnerBlocks…</div>
	<div class="mtb-fiche-information__etat-vide">
		<p class="mtb-fiche-information__etat-vide-nom">Fiche d'information</p>
		<p class="mtb-fiche-information__etat-vide-phrase">Ce bloc n'affiche rien tant que vous n'avez
			pas ajouté un titre, du texte ou une photo.</p>
	</div>
</div>
```

**Le nom du composant est émis en casse normale.** La MAJUSCULE de l'étiquette est une décision CSS
(`text-transform`) : la restitution vocale reste normale et le texte reste lisible si la feuille manque.
⚑ **précédent** : l'extension n'émet jamais de MAJUSCULES décoratives.

### `save`, `$content` : l'articulation `InnerBlocks` sans build

```js
save: function () { return wp.element.createElement( wp.blockEditor.InnerBlocks.Content ); }
```

**Rien d'autre. Aucune enveloppe, aucun `useBlockProps.save()`.** Conséquences à connaître :

- `innerContent` vaut `array( null )` → **`$content` dans `render.php` contient exactement la prose déjà
  rendue par le cœur** (`<p>…</p>`, `<ul><li>…</li></ul>`) et rien de plus. C'est `render.php` qui
  l'enveloppe dans `__prose`.
- Si `save` renvoyait une enveloppe, elle se retrouverait **à l'intérieur** de `$content` et le rendu
  produirait deux conteneurs de prose imbriqués. **Écrit ici pour que personne n'« améliore » `save`.**
- Aucune invalidation de bloc possible : la sortie de `save` est calculée par le cœur à partir des seuls
  blocs enfants.

`edit()` appelle `useBlockProps`, `useInnerBlocksProps` et `useSelect` **inconditionnellement et avant
toute branche** — l'état vide est une branche de rendu, jamais une branche de hooks. `useBlockProps` est
**obligatoire en `apiVersion 3`** : le canevas est iframé, sans lui le bloc n'est ni sélectionnable ni
positionné.

---

## 4. Le véhicule du cadrage — arbitrage central

> **L'extension émet, sur `div.mtb-fiche-information__photo`, l'attribut `data-cadrage="<valeur>"`,
> avec exactement l'une de ces cinq chaînes, recopiées telles quelles depuis l'attribut enregistré :**
> **`haut_gauche` · `haut` · `centre` · `haut_droite` · `bas`**
> **Elle n'émet aucun attribut `style`. Le thème détient les cinq valeurs de cadrage.**

Le thème pose `--point-interet` depuis cinq sélecteurs d'attribut et conserve la forme littérale de
MASTER §6.2 — `object-position: var(--point-interet, 50% 38%)`.

| `data-cadrage` | `--point-interet` | Libellé MASTER §6.2 |
|---|---|---|
| `haut_gauche` | `left top` | *Haut gauche* |
| `haut` | `center top` | *Haut* |
| `centre` | `center center` | *Centre* |
| `haut_droite` | `right top` | *Haut droite* |
| `bas` | `center bottom` | *Bas* |

**Pourquoi un attribut de données et non un `style=`, ni une classe.** Trois raisons, la première étant
décisive :

1. **L'extension n'a pas la valeur et n'a pas le droit de l'inventer.** MASTER §6.2 donne les cinq
   **libellés** et **une seule valeur chiffrée** — le repli `50% 38%`. Il ne dit nulle part que « Haut
   gauche » vaut `0% 0%`. Écrire `style="--point-interet: 0% 0%"` obligerait l'extension à inventer cinq
   couples de pourcentages **absents de MASTER**, ce que son préambule interdit. Le débat sur la nature
   d'un `style=` en ligne est donc tranché **en amont** par un argument plus fort.
2. **Précédent gelé** : la décision 10 de `ETAT.md` fait déjà d'un `data-*` porteur d'une valeur du
   modèle le véhicule normal entre les deux moitiés (`data-libelle` sur chaque `<td>`).
3. **Symétrie de responsabilité** : l'extension dit *ce que l'éleveuse a choisi*, le thème dit *à quoi
   ça ressemble*. C'est la frontière du projet, mot pour mot. Et une classe aurait exigé d'étendre la
   convention du contrat #2 d'un **modificateur d'élément**, forme que ce contrat refuse (§3).

**La valeur est émise verbatim, en `snake_case`.** Les deux plans divergeaient — clés `snake_case`
stockées d'un côté, attribut `kebab-case` attendu de l'autre. **Aucune transformation** : une
translittération entre les deux moitiés est précisément la couche où une lettre se perd en silence. Un
`_` dans une valeur d'attribut de données est parfaitement valide.

**Mots-clés CSS, jamais des pourcentages** : `left top` … `center bottom` sont la **traduction littérale**
des cinq libellés de MASTER, portent zéro décision chiffrée et zéro valeur brute.

**Dégradation sûre** : attribut absent, mal orthographié ou inconnu → aucune règle ne s'applique → le
repli `50% 38%` de MASTER §6.2 s'exprime. Aucune page cassée (D12).

**`data-position="dessus|dessous"`** est émis sur la racine **quand une photo est rendue**, pour la
testabilité seule (`test-integration-mtb` et `review-mtb` peuvent affirmer la valeur enregistrée sans
déduire l'ordre du DOM). **Aucune règle CSS ne le lit.** Le thème ne doit **jamais** s'en servir pour
réordonner quoi que ce soit.

---

## 5. L'image — attributs attendus

`wp_get_attachment_image( $photo_id, 'large', false, array( 'class' => 'mtb-fiche-information__image', 'alt' => $description_brute ) )`

| Attribut | Traitement | Pourquoi |
|---|---|---|
| `src` + `srcset` | par le cœur | MASTER §6.9. **Aucune taille d'image à enregistrer** : le ratio est imposé par `aspect-ratio` + `object-fit: cover`, pas par une vignette recadrée. Seules les **largeurs** comptent, et `medium` 300 / `medium_large` 768 / `large` 1024 / `full` couvrent un emplacement de 576 px au plus, y compris à densité 2. `thumbnail` est écarté du `srcset` par `wp_calculate_image_srcset()`, qui n'y met que les tailles de même rapport. **Corollaire : `functions.php` ne se rouvre pas pour les images.** |
| `sizes` | **laissé au cœur**, non déclaré | Déclarer `sizes` supposerait d'écrire `36rem` dans l'extension, c'est-à-dire **une valeur de mise en page du thème** (`--l-texte`) — interdit par le contrat #1 §8. |
| `loading` / `fetchpriority` | **non forcés** : `wp_get_loading_optimization_attributes()` du cœur décide | **Écart nommé** : MASTER §6.9 écrit « toute image hors bandeau : `loading="lazy"` » sans nuance, BRIEF §12 dit « chargement différé **sous la ligne de flottaison** ». Le cœur rend `lazy` partout **sauf** sur la première image dans la fenêtre, qui reçoit `eager` + `fetchpriority="high"` — ce qui sert **mieux l'intention du brief**. À ratifier dans MASTER §6.9 ; la conformité littérale coûterait un `'loading' => 'lazy'`. |
| `decoding="async"` | oui, par le cœur | MASTER §6.9 |
| `width` / `height` | oui, dimensions réelles | MASTER §6.9, décalage cumulé nul. Redondants tant que la feuille charge, **indispensables si elle ne charge pas**. |
| `alt` | **du seul champ de la médiathèque** ; vide → `alt=""` | MASTER §6.5, §10.2, D7. **Jamais** le nom du fichier, **jamais** le titre du média, **jamais** une chaîne composée. Elle ne peut pas produire d'image sans alternative accessible. |
| `class` | `mtb-fiche-information__image` | déplace `attachment-large size-large` (§3) |
| `style` | **aucun** | contrat #1 §8 |

**`alt` est passé BRUT, non échappé** : `wp_get_attachment_image` applique `esc_attr()` lui-même sur
chaque attribut. L'échapper en amont produirait un **double encodage visible** (`&amp;` dans une
description contenant `&`).

**« Type once »** : au choix de la photo, `onSelect` pré-remplit `photo_description` avec la description
de la médiathèque **si le champ est encore vide**. Elle ne retape pas une description déjà saisie, et
peut l'ajuster pour cet emplacement précis.

---

## 6. Le CSS du thème — un seul fichier

`wp-content/themes/mtb/assets/css/blocs/mtb-fiche-information.css`, écrit par **`dev-ux-mtb`**.
**`dev-front-mtb` n'a rien à écrire dans cette issue** : tous les fichiers dont il est titulaire sont
interdits ici. Sa livraison conforme est **aucun fichier** — acté, pour qu'aucune écriture de
complaisance n'ait lieu dans un arbre sans branche.

Servi par la boucle générique : poignée `mtb-bloc-mtb-fiche-information`, dépendance `mtb-jetons`,
`ver = filemtime()`, `'path'` renseigné (le cœur peut l'écrire en ligne), **uniquement quand le bloc est
rendu**. **Aucune poignée n'est à demander : déposer le fichier suffit.**

> **⚠️ Contrainte de spécificité, à écrire dans le fichier.** La dépendance est `mtb-jetons`,
> **pas `mtb-base`**, et la feuille est mise en file pendant `render_block`, donc **après `wp_head()`** :
> on ne sait pas si elle est imprimée avant ou après `base.css`. **Aucune règle ne doit dépendre de
> l'ordre de source.** Chaque concurrence est gagnée à la spécificité seule : `img` (0,0,1) contre
> `.…__photo > img` (0,1,1) · `figcaption` contre `.…__legende` (0,1,0) · `figure { margin: 0 }` contre
> `.…__figure` (0,1,0) · `ul, ol { margin: 0 }` contre `.…__prose > ul` (0,1,1). Dans la toile de
> l'éditeur, le cœur préfixe tout par `.editor-styles-wrapper` : les deux côtés gagnent (0,1,0) et
> l'ordre relatif est **préservé**.

### Ce que la feuille écrit, et rien de plus

| Sélecteur | Déclarations | Jeton / autorisation MASTER |
|---|---|---|
| `.mtb-fiche-information` | `margin-block: var(--rythme-section)` | §7.3, §5.1 |
| `.mtb-fiche-information__titre` | `overflow-wrap: break-word` | §9.4, §7.7 |
| `.mtb-fiche-information__prose > ul, > ol` | `margin-block-end: var(--e-4)` | §5.1 — **extrapolation nommée** |
| `.mtb-fiche-information__figure` | `margin-block-end: var(--e-4)` | §5.1 |
| `.mtb-fiche-information__photo` | `position: relative` · `aspect-ratio: var(--r-paysage)` · `background-color: var(--calcaire-creux)` | §6.1 (« image d'une **fiche d'information** », nommément), §6.6 |
| `.mtb-fiche-information__photo::after` | `content:""` · `position:absolute` · `inset:0` · `box-shadow: var(--cerne-photo)` · `pointer-events:none` | §6.6 — cerne sur **toute** photo |
| `.mtb-fiche-information__photo > img` | `display:block` · `inline-size:100%` · `block-size:100%` · `object-fit:cover` · `object-position: var(--point-interet, 50% 38%)` · `font-size: var(--t-sm)` · `line-height:1.4` · `color: var(--texte-doux)` | §6.2 **recopié mot pour mot**, §6.6 (habillage du texte de remplacement) |
| `.mtb-fiche-information__photo[data-cadrage="…"]` ×5 | `--point-interet: …` | §6.2, table du §4 |
| `.mtb-fiche-information__legende` | `margin-block-start: var(--e-2)` | **extrapolation nommée** |
| `.mtb-fiche-information--editeur .…__etat-vide` | `border: 1px dashed var(--laiton)` · `border-radius: var(--r-0)` · `background-color: var(--calcaire-creux)` · `padding: var(--e-6)` · `font-size: var(--t-sm)` · `color: var(--texte-doux)` · `text-align: start` | §9.1, littéralement |
| `.mtb-fiche-information--editeur .…__etat-vide-nom` | `font-family: var(--sans)` · `600` · `var(--t-xs)` · `1.4` · `text-transform: uppercase` · `letter-spacing: .16em` · `color: var(--laiton-texte)` | §4.5, ligne « `h4` / étiquette » |

### Trois pièges à ne pas « simplifier »

1. **La fusion des marges est voulue sur la racine.** Sa marge haute fusionne avec le
   `margin-block-start: var(--e-7)` du `h2` (`base.css:202`) → `max(--rythme-section, --e-7)`, jamais
   l'addition. **Poser `display: flow-root`, `grid`, `flex` ou un `padding` sur la racine doublerait
   tout le rythme vertical.** Piège n° 1 de cette feuille.
2. **Le cerne passe obligatoirement par un pseudo-élément.** `--cerne-photo` est une **ombre
   intérieure**. L'ordre de peinture CSS place le contenu remplacé d'un `<img>` **au-dessus** du fond et
   des ombres intérieures de sa boîte : posée sur l'`<img>` **ou** sur l'emplacement, l'ombre serait
   **entièrement recouverte par la photo** — invisible, et §6.6 silencieusement enfreint. Un `::after`
   absolu porte le jeton dans sa propre couche. Aucune alternative ne conserve le jeton : un `outline`
   exigerait d'écrire `rgb(22 36 28 / .22)` en clair, valeur brute hors `tokens.css` (§13).
   **Recette obligatoire** : inspecter `::after` et **constater le cerne visible au-dessus de la photo**.
   S'il est invisible, la règle a été « simplifiée » — **livraison refusée.**
3. **Pas d'`overflow: hidden` sur l'emplacement.** MASTER §7.8 l'interdit sur un conteneur susceptible
   de contenir du texte, et cet emplacement contient précisément le texte de remplacement quand l'image
   échoue. Il n'est pas nécessaire non plus : l'image est dimensionnée exactement à 100 % / 100 %.

### Ce que la feuille n'écrit pas, et pourquoi

`base.css` habille déjà `h1 h2 h3 h4 p a blockquote hr figcaption table th td button input
:focus-visible`. Ne sont donc **pas** redéclarés : la famille, la graisse, la taille, l'interligne,
l'interlettre, l'encre et les marges du titre, **ni le segment de filet double de 6 rem** ; la
typographie de la légende (`--t-xs`, 1.45, `--texte-doux`, aligné à gauche — donc §6.8 est **déjà
tenu**) ; le corps de texte ; `:focus-visible` (§8.1) ; le soulignement permanent des liens.
Les recopier serait du CSS mort et une seconde source de vérité.

**`text-wrap: balance` n'est PAS ajouté** : MASTER §4.5 le réserve à `h1` et au `h1` de bandeau.
L'étendre à un `h2` serait une invention.

**Retrait et marqueurs de liste : rien n'est écrit.** MASTER ne donne aucune valeur pour le retrait
d'une liste. Écrire `--e-5` ou `--e-6` serait une invention ; laisser le retrait de l'agent utilisateur
n'écrit **aucune valeur brute** et est identique dans tous les moteurs modernes. À 324 px il reste
284 px de mesure. Question non bloquante n° 3.

**Aucun filet double**, sous aucune forme. Une fiche d'information **ne figure pas** dans la liste close
de huit emplacements de MASTER §2.1, et l'amendement 4 du contrat #2 réserve les quatre restants à des
issues nommées. Voir §7.

**Aucune transition, aucune animation** : MASTER ne définit aucun jeton de durée, et on n'en invente pas.
**Aucun `url()`**, donc zéro origine possible. **Aucune requête média**, donc aucune valeur littérale de
point de rupture.

**Valeurs brutes tolérées, liste close, chacune justifiée à l'endroit où elle est écrite** : `1px` du
contour tireté (§9.1) · `600`, `.16em`, `1.4` de l'étiquette (§4.5) · `1.4` du texte de remplacement
(§4.5) · `50% 38%` du repli de cadrage (§6.2) · les cinq mots-clés `left top` … `center bottom` (§6.2).

---

## 7. Le filet double — hérité, et c'est la bonne conséquence

La feuille n'en pose **aucun**. Mais le titre en héritera un : rendu comme un `h2` ordinaire, il reçoit
le segment de 6 rem de `base.css`. **C'est juste** — `base.css` porte ce filet au titre d'un **style
d'élément**, et un titre de fiche est un `h2` de page comme un autre.

- **Un `h3` n'en a aucun** (MASTER §4.5 : « h3 · sans filet »). La liste fermée à deux valeurs donne
  donc à l'éleveuse, **sans lui parler en jargon**, le choix entre une section marquée d'un filet et une
  sous-section sans. C'est un gain, pas un effet de bord.
- **Deux fiches empilées produisent deux filets successifs.** MASTER §2.1 exige au moins `--e-7`
  d'écart. Arithmétique dans le **pire cas** — une fiche réduite à son seul titre : `--e-4` sous le
  filet (16 px) + la fusion `max(--rythme-section, --e-7)` (≥ 48 px) = **≥ 64 px > 48 px**. **§2.1 est
  structurellement tenu**, sans qu'une seule valeur soit posée pour ça — c'est le
  `margin-block-start: var(--e-7)` du `h2` qui l'assure. **À mesurer en recette** (deux fiches empilées).
- **« Jamais deux fois dans le même bloc visuel »** est tenu : une fiche contient au plus un `h2`, donc
  au plus un filet. Et la restriction des `InnerBlocks` à Paragraphe + Liste **interdit mécaniquement un
  `<hr>` à l'intérieur**. ⚑ La restriction protège la signature en plus de protéger la mise en page :
  c'est un argument de **conservation** de la restriction, pas seulement de simplicité.

---

## 8. États spéciaux

| État | Émis par le serveur | Rendu par le thème |
|---|---|---|
| `aucune_portee` | **sans objet** — ce bloc ne lit aucune portée | sans objet |
| `donnee_absente` | **ne s'applique pas.** Un titre, une photo ou une légende absents ne produisent **jamais** « Non renseigné » : MASTER §10.3 le réserve à un champ de **fiche** non rempli, et §9.2 tranche qu'un emplacement facultatif absent **n'existe pas** | **le thème ne comble jamais un trou qui n'est pas là.** Aucune réserve, aucun tiret, aucun « Non renseigné » |
| `parent_hors_elevage` | sans objet | sans objet |
| `page_protegee` | **traité en amont par le cœur** : `post_password_required()` supprime tout le `post_content` avant qu'aucun `render_callback` ne soit appelé. `render.php` ne s'exécute pas — la photo ne fuit pas, aucun `alt` ne fuit | le formulaire natif, hérité de #2. **Rien à faire, rien à défaire** |
| **`rien_a_afficher`** ⚑ nouveau, propre aux composants | Côté public, **l'élément racine est absent du DOM** — `render.php` retourne une chaîne vide | **le thème ne doit jamais supposer la présence de `.mtb-fiche-information` sur une page**, ni styler un « conteneur vide » : il n'y en a pas. Côté éditeur, l'encart `__etat-vide` |

### D12, cas par cas — la table complète

| Contenu | Public (`render.php`) | Éditeur (`edit()`) |
|---|---|---|
| **Rien du tout** | **chaîne vide**, aucun élément dans le DOM | titre et prose vides pour la saisie + **`__etat-vide`** |
| **Titre seul** | racine + `__titre`, rien d'autre. Lit comme un titre de section : délibéré | idem + surfaces de saisie, **pas d'état vide** |
| **Texte seul** | racine + `__prose`. **Aucun `<h2>` vide** | idem, pas d'état vide |
| **Photo seule** | racine + `__figure`. **Contenu délibéré : rendu.** La masquer perdrait du contenu en silence | idem, pas d'état vide |
| **Titre + texte** | racine + `__titre` + `__prose`. **Aucun emplacement photo** (§9.2) | idem |
| **Photo jamais choisie, ou pièce jointe supprimée** | ni `<figure>`, ni `<img>`, ni `<figcaption>`, ni `data-position`. **L'emplacement n'existe pas** — aucun trou, aucune réserve, aucun avertissement PHP | le panneau affiche « Choisir une photo » |
| **Légende sans photo** | rien : une légende sans figure n'a pas de sens. **La valeur reste enregistrée, elle n'est pas perdue** | rien dans le canevas ; la fiche d'aide le dit |
| **Titre très long** | `hyphens: auto` (hérité) + `overflow-wrap: break-word`. **Jamais de troncature `…`** (§9.4) | idem |
| **Titre très court** | aucune hauteur minimale ; le filet suit le texte (§9.4) | idem |
| **Texte très long** | canal de 36 rem, il se lit. Aucune limite de hauteur, aucun « lire la suite » | idem |
| **Photo à transparence** | fond `--calcaire-creux` : jamais de damier, jamais de trou (§6.6) | idem |
| **Photo qui ne charge pas** | l'emplacement **garde son ratio** et son fond ; le texte de remplacement s'affiche dedans en `--texte-doux` `--t-sm` (§6.6). **Limite nommée** : le glyphe d'image cassée que certains moteurs dessinent n'est atteignable par aucun CSS — à constater, **pas à contourner par une bricole** | idem |
| **Photo de trop basse définition** | **limite nommée et bornée.** §6.6 (« largeur naturelle × 1,5 ») exigerait un `max-inline-size` posé en `style=` par le serveur — **interdit**. Or l'emplacement plafonne à 576 px : **toute image d'au moins 384 px respecte déjà la règle**. Elle n'est franchissable que par un original < 384 px, qu'aucune photo d'appareil ni aucune vignette WordPress ne produit. **Rien en CSS**, exposition écrite, mention dans la fiche d'aide | idem |
| **Cadrage absent ou inconnu** | repli `50% 38%` de §6.2 | idem |
| **Sans titre : décision de balisage** | `render.php` lit `niveau_titre` par une **liste blanche littérale** `array( 'h2', 'h3' )`, toute autre valeur retombe sur `h2`. **Titre vide → aucun élément de titre** : pas de `<h2></h2>`, qui porterait le filet dans le vide et annoncerait une section inexistante à un lecteur d'écran | idem |

**Comment « vide » est décidé.** `titre` et `$content` sont testés par
`'' === trim( wp_strip_all_tags( $valeur ) )`. La décision 20 de `ETAT.md` interdit
`wp_strip_all_tags` **sur une valeur recopiée** — c'est-à-dire sur une valeur qu'on stocke ou qu'on
affiche. Ici la fonction sert **uniquement à un test de vacuité**, sur une copie jetée immédiatement :
la valeur émise en sortie est l'originale, intacte. **À écrire en commentaire dans `rendu.php`** pour
que la revue n'ait pas à le redécouvrir. Cas couvert au passage : le `core/paragraph` vide du `template`
produit `<p></p>` → prose considérée vide → **une fiche fraîchement insérée et laissée telle quelle ne
rend rien côté public.**

### Si `mtb-core` est désactivée

| Élément | Devient |
|---|---|
| Le bloc dans l'insérteur | disparaît |
| La **prose** | **s'affiche toujours**, sans enveloppe ni CSS de bloc. Le cœur rend l'`innerHTML` d'un bloc inconnu. **Rien n'est perdu, rien n'est cassé** |
| Titre, photo, légende, cadrage, position | **n'apparaissent plus** : ils vivent dans le JSON du commentaire de bloc. **Aucune donnée n'est perdue** — le JSON reste intact dans `post_content`, réactiver l'extension les fait revenir à l'identique |
| Côté éditeur | l'encart du cœur « contenu inattendu », options *Résoudre* / *Convertir en HTML*. **Ne pas convertir : la conversion détruirait définitivement le JSON des attributs.** À écrire dans la fiche d'aide, section « En cas de doute » |
| La feuille du thème | n'est plus servie (la boucle itère sur le registre) ; aucune requête en échec |
| La page | reste lisible, aucun écran blanc. **D12 tenue** |

---

## 9. Deux contraintes de gabarit héritées — vérifiées, non corrigées ici

**Ces deux constats ont été vérifiés dans le code livré, pas supposés. Ils ne sont pas de mon
périmètre ; ils sont écrits ici pour que personne ne les redécouvre ni ne les traite comme un bug du
composant.**

1. **Un bloc inséré dans une Page est plafonné au canal texte de 36 rem, `alignwide` et `alignfull`
   compris.** `templates/singular.html` rend `wp:post-content` **enfant direct** du groupe `.mtb-canal` ;
   `base.css:517` épingle tout enfant direct à `grid-column: texte-debut / texte-fin`, donc le
   `<div class="wp-block-post-content">` fait 36 rem. Les blocs de la page sont ses **petits-enfants** :
   `.mtb-canal > .alignwide` (`base.css:522`) et `.mtb-canal > .alignfull` (`base.css:534`) ne les
   atteignent pas. **Ce composant n'en souffre pas** — une fiche d'information est de la prose, elle vit
   dans le canal texte, et `align` est à `false`. Le constat **renforce** le refus du côte-à-côte.
   **Signalé à `/lead-mtb` comme dette de gabarit**, urgente pour #6 (bandeau pleine largeur) et #8
   (galerie en canal large). **Corollaire pour la fiche d'aide : la photo ne dépassera pas 576 px de
   large tant que les gabarits n'existent pas. Ne rien promettre de plus.**

2. **`theme.json` porte `"blockGap": null`**, et `isset()` sur `null` vaut **faux** : le cœur n'émet
   **aucune** règle `blockGap`. Vérifié dans la stack —
   `isset( wp_get_global_settings()["spacing"]["blockGap"] )` → **`false`**. Donc **l'espacement vertical
   entre blocs d'une Page vient exclusivement des marges d'éléments de `base.css`** et de celles que
   pose la feuille du bloc. Personne n'a à neutraliser un `blockGap`, et **personne ne doit compter sur
   lui**. ⚑ **Quiconque le passerait à une valeur casserait l'espacement de tous les composants du lot.**

**Écart titre de page → première fiche** : `.wp-block-post-content` étant un élément de grille, il
établit un contexte de formatage indépendant, donc les marges du bloc **ne fusionnent pas** avec le `h1`
de la page. L'écart vaut `--e-5` + `max(--rythme-section, --e-7)`. Fait de gabarit, pas défaut du
composant.

---

## 10. La feuille de bloc n'atteint pas la toile de l'éditeur — dette nommée

> **Vérifié dans le cœur installé (WordPress 6.9), pas supposé.**
> `wp_should_load_block_assets_on_demand()` vaut **`true`**. Dans ce cas
> `wp_enqueue_block_style()` (`wp-includes/script-loader.php:3367-3392`) accroche son rappel à
> **`render_block`** puis **rend la main immédiatement**. Or la toile de l'éditeur bâtit ses feuilles
> dans `_wp_get_iframed_editor_assets()` (`wp-includes/block-editor.php:301-341`), qui instancie un
> `WP_Styles` neuf puis fait `do_action( 'enqueue_block_assets' )` — **`render_block` ne s'y déclenche
> jamais**. La boucle générique est **correcte pour le site public et inerte pour l'éditeur**.

**Ce que cela coûte, et ce n'est pas cosmétique** :

- l'apparence de MASTER §9.1 est **livrée dans la feuille** — c'est son domicile conforme, et elle
  s'appliquera dès que la résolution ci-dessous sera en place — mais elle est **inerte côté éditeur** ;
- dans l'éditeur, la photo s'affiche non rognée, sans cerne, sans ratio : **l'écran d'édition ment sur
  le rendu** ;
- surtout, **l'éleveuse ne voit pas l'effet de son réglage « Cadrage de la photo »**. C'est un défaut de
  **D1** sur le seul réglage photographique que MASTER lui accorde.

**Ce n'est pas un problème de cette issue, c'est un problème du lot** : les six chaînes déposent chacune
une feuille dans `assets/css/blocs/` et rencontrent la même absence. Deux résolutions, **toutes deux
hors de toute empreinte de chaîne, à attribuer nommément par `/lead-mtb`** :

- **(a) durable, recommandée** — compléter le **mécanisme générique** de `functions.php` pour passer
  aussi les feuilles de blocs existantes à `add_editor_style()`. L'itération reste sur
  `WP_Block_Type_Registry`, seul `file_exists()` touche le disque : aucun `glob`/`scandir`, la contrainte
  du contrat #2 tient. Bénéfice immédiat pour les six chaînes. Coût : `functions.php` se rouvre — mais
  c'est une modification **du mécanisme lui-même**, pas une liste à rallonger ; elle se fait une fois.
- **(b) provisoire** — quelques lignes dans `assets/css/editor.css`, **fichier partagé** : écrasement
  mutuel certain si deux chaînes l'ouvrent.

**Tant que ce n'est pas attribué, aucune chaîne n'y touche, et l'écart est celui écrit ici.**
**En aucun cas un `style=` improvisé dans `edit()`** : ce serait contourner l'interdit du contrat #1 §8
par la porte React.

**La catégorie de blocs `mtb`** — contrat #1 §10 la donne à « la première issue de composants », mais
#6, #7 et #8 sont **simultanées** et `includes/blocks/categorie-mtb/` n'existe pas (zéro occurrence de
`block_categories_all` dans tout `wp-content/`). **Vérifié** : le contrôle historique
« *must have a registered category* » **a été retiré du cœur** — zéro occurrence dans
`wp-includes/js/dist/blocks.min.js`. Donc un bloc déclarant une catégorie non enregistrée
**s'enregistre quand même** ; il est seulement **non regroupé dans un panneau de l'insérteur**
(trouvable par recherche). **Ce n'est pas fatal, mais c'est un recul de la contrainte 1.**

**Décision de cette chaîne** : `block.json` déclare `"category": "mtb"` et **le module n'est pas créé** —
rester dans l'empreinte prime sur écraser une sœur dans un arbre sans branche. Le `bootstrap.php`
**idempotent** est spécifié et prêt (`add_filter( 'block_categories_all' )` à l'inclusion — c'est un
filtre, autorisé ; **idempotence par le slug** et non par un drapeau, qui ne survivrait pas à deux
modules distincts ; `'icon' => null` explicite ; titre `Mont Brabant` en français littéral). **Si
`/lead-mtb` l'attribue à cette chaîne avant le commit, il est livré ; sinon l'écart est rapporté.**

---

## 11. Chaînes fournies par le serveur

**Côté public : aucune chaîne du serveur.** Tout le texte rendu vient de la saisie de l'éleveuse. Il n'y
a **rien à composer**, donc rien que le thème pourrait être tenté de composer. ⚑ **À écrire dans le
contrat de chaque composant éditorial**, pour que personne n'y ajoute plus tard une chaîne composée.

**Côté éditrice** — gelées, littérales, à reprendre **à l'identique** par `doc-client-mtb` :

| Où | Chaîne exacte |
|---|---|
| Nom du composant | `Fiche d'information` |
| Description dans l'insérteur | `Un titre, des paragraphes et une photo : la brique de base des pages de contenu.` |
| Mots-clés | `photo` · `texte` · `paragraphe` · `titre` · `section` |
| Catégorie de blocs | `Mont Brabant` |
| Texte fantôme du titre | `Titre de la section` |
| Réglage | `Niveau du titre` |
| Options | `Titre de section` (h2, défaut) · `Sous-titre` (h3) |
| Aide | `« Titre de section » pour un titre principal de la page, « Sous-titre » pour une subdivision.` |
| Section du panneau | `Photo` |
| Boutons | `Choisir une photo` · `Remplacer la photo` · `Retirer la photo` |
| Réglage | `Description de la photo (pour les personnes aveugles)` |
| Aide | `Décrivez ce que montre la photo. Laissez vide si la photo n'apporte aucune information.` |
| Réglage | `Légende de la photo` |
| Aide | `Texte affiché sous la photo. Facultatif.` |
| Réglage | `Position de la photo` |
| Options | `Photo au-dessus du texte` (défaut) · `Photo sous le texte` |
| Aide | `La photo se place avant ou après les paragraphes. Le titre reste toujours en premier.` |
| Réglage | `Cadrage de la photo` |
| Options | `Haut gauche` · `Haut` · `Centre` (défaut) · `Haut droite` · `Bas` |
| Aide | `Choisissez la partie de la photo qui doit rester visible si elle est recadrée.` |
| État vide, ligne 1 | `Fiche d'information` |
| État vide, ligne 2 | `Ce bloc n'affiche rien tant que vous n'avez pas ajouté un titre, du texte ou une photo.` |

Les quatre réglages de photo **n'apparaissent que lorsqu'une photo est choisie** : un réglage sans objet
est un réglage qui inquiète.

**Aucun mot interdit de MASTER §10.4** : ni `bloc réutilisable`, ni `média`, ni `image mise en avant`,
ni `alt`, ni `template`, ni `responsive`, ni `slug`, ni `permalien`. Le mot « bloc » n'apparaît qu'au
seul endroit où MASTER §9.1 l'impose littéralement — la phrase d'état vide.

**Chemin de clic** : *Pages › Toutes les pages › [la page] › + › Mont Brabant › Fiche d'information ›
taper le titre › taper le texte › (facultatif) Photo › Choisir une photo › Mettre à jour.*

---

## 12. Accessibilité — paires employées, ratios pris dans MASTER §12

| Endroit | Encre / fond | Ratio | Source |
|---|---|---|---|
| Titre (`base.css`) | `--pin` / `--calcaire` | **14,23:1** AAA | §12.1 |
| Prose (`base.css`) | `--texte` / `--calcaire` | **12,03:1** AAA | §12.1 |
| Lien dans la prose (`base.css`) | `--sauge-fonce` / `--calcaire` | **7,73:1** AAA | §12.1 |
| Légende (`base.css`) | `--texte-doux` / `--calcaire` | **6,47:1** AA | §12.1 |
| Texte de remplacement d'une photo | `--texte-doux` / `--calcaire-creux` | **5,79:1** AA | §12.3 |
| Phrase de l'état vide | `--texte-doux` / `--calcaire-creux` | **5,79:1** AA | §12.3 |
| Contour tireté de l'état vide | `--laiton` / `--calcaire-creux` | **3,15:1** non textuel | §12.3 |
| **Étiquette de l'état vide** | **`--laiton-texte` / `--calcaire-creux`** | **4,75:1 AA** | **absente de §12 — arbitrée, voir ci-dessous** |

### Une paire manque à MASTER — arbitrée, pas comblée en silence

MASTER §9.1 **exige** que la ligne 1 de l'état vide soit « le nom du composant en **étiquette laiton** »
sur fond `--calcaire-creux`. Or §12.3 ne liste pas `--laiton-texte` sur ce fond, et §13 range « une paire
encre/fond absente du §12 » parmi les interdits. **La contradiction est dans MASTER**, pas dans le code.

Paire recalculée deux fois indépendamment avec la formule du §3, méthode validée en reproduisant au
centième deux valeurs publiées (`--laiton-texte` sur `--calcaire` = 5,30 ✓ ; `--texte` sur
`--calcaire-creux` = 10,78 ✓) :

> **`--laiton-texte` `#7A5F2C` sur `--calcaire-creux` `#E7E5DA` = 4,75:1**
> (L = 0,12504 et 0,78106) — **AA pour du texte courant.**

**Décision (`lead-issue-mtb`) : on emploie `--laiton-texte`.** La prescription explicite de §9.1 gagne,
le ratio est AA, et l'apparence n'existe **que dans l'éditeur** — jamais devant un visiteur. C'est
exactement la forme de l'**amendement 5 du contrat #2** (marges de `h1`) : extrapolation minimale d'une
prescription explicite, écrite, à ratifier.

**À porter dans MASTER §12.3 par `lead-design-mtb`**, avec les **deux paires que l'amendement 5 réclame
déjà** (`--sauge` sur `--blanc` 5,69:1 et `--laiton` sur `--pin` 4,05:1). **Trois paires manquent au
tableau : autant les traiter d'un coup.**

### Le reste de la liste bloquante

- **Un seul `<h1>`** : garanti par la liste fermée `h2`/`h3` ; le `h1` reste celui de `post-title`.
- **Ordre de lecture = ordre visuel**, par construction. **Aucun `order`, aucune flexbox, aucune
  grille** sur la racine. Un `order` ferait diverger l'ordre visuel de l'ordre du DOM : la photo et sa
  légende **sont du contenu**, leur place est ce que l'éleveuse choisit, et un lecteur d'écran, une
  navigation clavier, un rendu sans CSS et une impression liraient l'inverse de ce qu'elle voit —
  **WCAG 1.3.2**. La divergence n'aurait ici **aucune contrepartie**, le serveur émettant les deux
  ordres à coût nul. **Le thème ne doit jamais réordonner la figure et la prose en CSS.**
- **Focus visible** : `:focus-visible` de §8.1 est **déjà dans `base.css`** et couvre les liens de la
  prose, seul élément focalisable du bloc. **Aucun `outline: none`, jamais.**
- **360 px sans défilement horizontal** : aucune largeur posée, ni fixe ni minimale, donc a fortiori
  **aucune supérieure à 300 px**. Emplacement 324 × 216. `overflow-wrap: break-word` sur le titre ferme
  le seul débordement possible.
- **Zoom 200 %** : aucune taille en `vw` seul (toutes sont des `clamp()` de §4.4), aucune hauteur fixe,
  `aspect-ratio` est relatif, aucun `overflow: hidden` sur un conteneur de texte.
- **Cibles ≥ 44 px** : aucune cible tactile dans ce bloc.
- **Aucune information par la couleur seule** : aucun état côté public ; l'état vide porte **le nom du
  composant et une phrase complète** — deux signaux textuels avant toute couleur.
- **Rien ne dépend du survol, rien ne dépend du JavaScript** : **zéro octet de JS public**, le rendu est
  serveur.

---

## 13. Sécurité, rôles, échappement

- **Aucun chemin d'écriture propre**, donc **aucun nonce et aucun `current_user_can` à écrire** :
  l'écriture est celle du cœur sur la Page, déjà gardée. ⚑ Écrire un nonce ici serait du théâtre.
- **Assainissement à la frontière, trois barrières** : (1) `block.json` — `type` + `enum`, et
  `WP_Block_Type::prepare_attributes_for_render()` **remplace toute valeur invalide par le défaut** ;
  c'est la garde la plus solide et elle est déclarative. (2) `rendu.php` — liste blanche pour la balise
  du titre et pour la valeur de cadrage, `absint()` puis `'attachment' === get_post_type()` pour
  `photo_id`. (3) échappement **au rendu**.
- **Aucun assainissement destructif sur une valeur recopiée** (décision 20) : ni `sanitize_text_field`,
  ni `wp_strip_all_tags`, ni `wp_kses` sur le titre, la description ou la légende — hors du test de
  vacuité du §8, qui n'écrit rien.
- **Rôle Éditeur natif, aucune capacité ajoutée.** `edit_pages` et `upload_files` suffisent.

| Valeur émise | Fonction | Pourquoi celle-là |
|---|---|---|
| `titre` | **`wp_kses_post()`** | `RichText` autorise `<strong>`, `<em>`, `<a>`. **`esc_html()` afficherait `<strong>` en clair et détruirait le titre.** |
| Balise du titre | **aucune** | vient d'une liste blanche littérale du code, jamais de l'entrée |
| Toutes les classes et `data-*` | **`esc_attr()`** | y compris composées depuis une liste fermée : la garde ne coûte rien et survit à un refactor |
| `$content` (la prose) | **aucune** — `echo` direct | déjà rendu par le cœur, chaque bloc enfant ayant échappé son contenu. `wp_kses_post` serait redondant et amputerait un balisage légitime d'une version future ; `esc_html` détruirait la page. `phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped` **avec cette justification en une ligne** |
| `photo_description` | **aucune** de notre côté | passée brute dans `$attr['alt']` ; `wp_get_attachment_image` applique `esc_attr()`. Échapper en amont **double-encoderait** |
| Sortie de `wp_get_attachment_image()` | **`wp_kses_post()`** | cohérent avec `includes/fields/chien/ecran.php:624`. **À vérifier (V6)** qu'aucun attribut n'est retiré au passage |
| `photo_legende` | **`esc_html()`** | texte simple, aucun balisage attendu dans une légende |
| Chaînes de l'état vide | **`esc_html()`** en PHP, texte nu en JS | littéraux du code, échappés par principe |

---

## 14. Poids

| Ressource | Ko | Budget |
|---|---|---|
| Socle livré (HTML + `tokens.css` + `base.css`) | ≈ 29 | référence |
| `mtb-fiche-information.css` | **≈ 6 estimés**, ~1,6 en gzip — **chiffre réel à mesurer et rapporter octet par octet** | conditionnelle au rendu du bloc ; `'path'` renseigné, seuil `styles_inline_size_limit` par défaut 20 000 o → **probablement écrite en ligne, 0 requête ajoutée** |
| JavaScript de thème | **0** | `assets/js/` reste vide |
| Image décorative, police d'icônes, SVG | **0** | §13 |
| **Total page portant le bloc** | **≈ 35** | **< 200** (BRIEF §12) |
| Requêtes vers un domaine tiers | **0** | D6 tenu |

Coût assumé : ~10 déclarations d'état vide servies au visiteur (≈ 300 o) alors que le balisage n'existe
jamais côté public. Le préfixe `--editeur` les rend inertes et l'intention explicite.

---

## 15. Interdits

- Le thème n'interroge **jamais** la base : ni `WP_Query`, ni `get_post_meta`, ni `get_posts`, ni
  `get_terms`, ni `MTB\`. **Frontière vérifiable par grep, gelée.**
- Le thème ne compose **jamais** une chaîne métier, n'accorde aucun genre, ne formate aucune date, ne
  reformate aucune valeur de santé. **Ici il n'y a rien à composer : le composant n'affiche que la
  saisie.**
- Le thème **n'imprime jamais « Non renseigné »** pour une photo, une légende ou un titre absents de ce
  composant.
- Le thème **ne réordonne jamais** la figure et la prose en CSS (`order`, `flex-direction:
  column-reverse`, `grid-row`) — ce serait dissocier l'ordre visuel de l'ordre de lecture.
- Le thème ne style **jamais** `.wp-block-mtb-fiche-information`, `attachment-large`, `size-large`, et
  ne s'appuie pas sur `data-position`.
- L'extension n'émet **aucune** règle visuelle, **aucun `style=`**, aucune valeur de couleur, de taille,
  d'espacement, de ratio ou de pourcentage ; **aucun `style`/`editorStyle` dans `block.json`** ; aucun
  `add_image_size`.
- **Aucun jeton inventé**, aucune couleur hors des quinze de §3.1, aucun fond hors des cinq de §3.2,
  aucune paire encre/fond absente de §12 **hors du cas unique arbitré au §12**.
- **Aucun `border-radius` > 2 px**, aucune ombre portée, aucun dégradé, aucun `filter:` sur une photo,
  aucun `mix-blend-mode`, aucune transition, aucune animation.
- **Aucun filet double**, sous aucune forme, nulle part dans ce composant.
- **Aucune étape de build**, aucun `package.json`, aucun préprocesseur, aucun cadre CSS, aucun JSX,
  aucun dossier `build/`, aucune dépendance Composer.
- **Aucune origine tierce**, aucun `url()` distant, aucun `@import`, aucune police d'icônes, aucun SVG
  distant. **Jamais `add_theme_support( 'wp-block-styles' )`** — interdit permanent, amendement 2 bis du
  contrat #2 : il tronquerait le filet double du séparateur à 100 px.
- `glob()`, `scandir()`, `opendir()`, `DirectoryIterator` : **interdits**.
- Noms de fichiers en **minuscules, sans accent** — développement Windows, production Linux.
- **Aucun fait d'élevage inventé**, nulle part, y compris dans `example` : aucun nom de chien, aucune
  date, aucun numéro LOF, aucun résultat.

---

## 16. Ce qui reste à vérifier dans la stack — commandes exactes

**La stack tourne : WordPress 6.9, port 3005** (et non 8080). À dérouler par les devs, et à **remonter**
en cas de divergence plutôt qu'à contourner (contrat #1 §10).

| # | À vérifier | Comment |
|---|---|---|
| **V1** | `wp.blockEditor.useInnerBlocksProps` est exposé | console de l'éditeur : `typeof wp.blockEditor.useInnerBlocksProps` → `"function"` |
| **V2** | `allowedBlocks` restreint aux deux blocs **n'interdit pas** `core/list-item` dans `core/list` (`allowedBlocks` ne contraint qu'un niveau) | insérer une liste à puces de trois items, enregistrer, recharger → items intacts |
| **V3** | Collage d'un HTML riche dans la prose : les blocs refusés sont-ils convertis en paragraphes ou perdus ? | coller un fragment avec `<h3>` et `<table>` → **noter le résultat pour #19-#21**, ne pas contourner |
| **V4** | **Piège 2 du contrat #1 §10** — un bloc enregistré côté PHP seul apparaît-il dans l'insérteur ? | `wp eval "var_dump( WP_Block_Type_Registry::get_instance()->is_registered('mtb/fiche-information') );"` puis console : `wp.blocks.getBlockType('mtb/fiche-information')` et `wp.blocks.getCategories().map(c=>c.slug)`. **Et surtout : le bloc est-il trouvable dans l'insérteur ?** |
| **V5** | `require` ou `require_once` du fichier de rendu | `grep -n "require.*template_path" /var/www/html/wp-includes/blocks.php` — puis **deux fiches sur une même page** |
| **V6** | `wp_kses_post` ne mange aucun attribut de l'image | source de la page publique : `srcset`, `sizes`, `width`, `height`, `loading`, `decoding` tous présents |
| **V7** | `supports.className: false` supprime bien `wp-block-mtb-fiche-information` | source de la page : la classe est **absente** |
| **V8** | La feuille est servie et écrite en ligne | `curl -s http://localhost:3005/<page>/ \| grep -c "mtb-fiche-information"` |
| **V9** | **Le cerne est visible au-dessus de la photo** | inspecter `.…__photo::after`. **Cerne invisible = règle « simplifiée » = livraison refusée** |
| **V10** | Aucun avertissement, aucune notice | `debug.log` : aucun `_doing_it_wrong` sur `editeur.asset.php`, aucun « Translation loading … triggered too early » |
| **V11** | **D6** : zéro origine tierce ajoutée | Réseau, filtre « domaine ≠ localhost » sur la page portant le bloc → **zéro**. Insérteur ouvert : le compte d'images `s.w.org` **reste à 15** (décision 15) |
| **V12** | Verrous vus par **`fabienne`**, pas par `admin` | connectée en `fabienne` : **aucun onglet « Styles »**, aucun bouton d'alignement, aucun « Modifier en HTML », et l'insérteur interne ne propose que **Paragraphe** et **Liste** |
| **V13** | 360 px et zoom 200 % | 360 px : aucun défilement horizontal, emplacement 324 × 216. 1280 px zoomé 200 % : canal 576 px |
| **V14** | **Deux fiches empilées** : écart entre les deux filets doubles ≥ `--e-7` (48 px) | mesurer dans l'inspecteur |
| **V15** | Poids réel de la feuille | `wc -c`, chiffre reporté octet par octet |

**Recette visuelle, dans l'ordre où les défauts se révèlent** : (a) bloc rempli à 1280 px · (b) à 360 px ·
(c) zoom 200 % · (d) `h3` au lieu de `h2` · (e) photo sous le texte · (f) **les cinq cadrages** ·
(g) sans photo · (h) sans prose · (i) sans titre · (j) totalement vide, dans l'éditeur · (k) deux fiches
empilées · (l) photo portrait dans l'emplacement paysage · (m) PNG à transparence · (n) image en 404 ·
(o) page protégée par mot de passe · (p) pièce jointe supprimée de la médiathèque.

---

## 17. Arbitrages

| # | Désaccord entre les deux plans | Décision | Raison |
|---|---|---|---|
| 1 | **Où vit le CSS du bloc** : dossier du bloc (empreinte initiale de l'issue) ou thème | **`themes/mtb/assets/css/blocs/mtb-fiche-information.css`** | Contrat #1 §8 et §13 interdisent à l'extension d'émettre la moindre règle visuelle ; contrat #2 place le CSS d'un bloc là et sa **boucle générique** est faite pour ça. Le fichier est **neuf et au nom unique du bloc** : collision impossible, aucun fichier partagé touché. Extension d'empreinte d'un fichier contre une infraction que la revue aurait remontée. |
| 2 | **Structure de la photo** : back voulait `<figure><img class="__photo">`, front voulait `<figure><div class="__photo"><img></div>` | **La version du front : `__photo` est un `<div>`, l'emplacement** | Trois raisons cumulatives, chacune suffisante. MASTER §6.2 écrit littéralement `.mtb-photo > img { … }` — donc un conteneur portant le ratio et une image qui le remplit. Le **cerne** de §6.6 est une ombre **intérieure** : posée sur l'`<img>`, elle est **entièrement recouverte par la photo** et §6.6 est silencieusement enfreint ; il faut un pseudo-élément sur un conteneur. Et §6.6 exige qu'une photo qui ne charge pas **garde son ratio** et affiche le texte de remplacement **dedans** — ce qui suppose une boîte qui n'est pas l'`<img>`. Le `<figure>` ne peut pas jouer ce rôle : le `<figcaption>` serait alors **dans** le ratio. |
| 3 | **Classe de l'`<img>`** : back la nommait `__photo` (désormais pris), front n'en voulait aucune | **`mtb-fiche-information__image`, que le thème n'utilise pas** | Le thème cible `> img`, forme littérale de §6.2. La classe existe pour une seule raison : **déplacer** `attachment-large size-large` que `wp_get_attachment_image()` émet par défaut. Un `class=""` aurait fait le même travail mais se lit comme un bug. Nommer les deux distinctement — `__photo` l'emplacement, `__image` l'élément — supprime toute ambiguïté. |
| 4 | **Véhicule du cadrage** : back proposait **cinq classes** sur la `<figure>`, front **`data-cadrage`** sur l'emplacement | **`data-cadrage` sur `div.…__photo`, valeur `snake_case` verbatim** | C'était la divergence la plus dangereuse : les deux moitiés refusaient `style=` mais visaient **des éléments différents avec des mécanismes différents** — la page aurait rendu, la photo cadrée au défaut, et **le réglage de l'éleveuse sans aucun effet visible**. Retenu le `data-*` : précédent gelé de la **décision 10** (`data-libelle`), posé sur l'élément qui a besoin de la propriété, et **sans étendre la convention du contrat #2 d'un modificateur d'élément**. **Valeur émise verbatim** : un `kebab-case` côté classe et un `snake_case` côté stockage auraient créé une couche de translittération, précisément là où une lettre se perd en silence. |
| 5 | **Nommage de l'état vide** : back `__etat-vide` + `--editeur`, front `__vide` sans discriminant | **`__etat-vide`, `__etat-vide-nom`, `__etat-vide-phrase`, plus `--editeur` sur la racine** | `état vide` est le terme de MASTER §9.1 lui-même. Et le modificateur `--editeur` rend « jamais visible d'un visiteur » **exigible en CSS** au lieu d'être vrai par accident : le front admettait servir ~300 o de règles mortes non bornées. |
| 6 | **Crochet de position** : back émettait `--photo-dessus`/`--photo-dessous`, front démontrait n'en avoir aucun usage | **Aucune classe de position. `data-position` sur la racine, pour la testabilité seule** | Le front écrit la feuille et a démontré arithmétiquement qu'**une seule** règle (`figure { margin-block-end: --e-4 }`) est juste dans les deux positions. Un crochet émis et jamais lu est du poids mort **et** une invitation à s'en servir pour un `order`. Le `data-position` reste parce qu'il a une valeur réelle : `review-mtb` et `test-integration-mtb` peuvent affirmer la valeur enregistrée sans déduire l'ordre du DOM. |
| 7 | **Phrase de l'état vide** : deux rédactions concurrentes, l'une inexacte, l'autre lourde | **« Ce bloc n'affiche rien tant que vous n'avez pas ajouté un titre, du texte ou une photo. »** | La forme « Ce bloc n'affiche rien tant que … » est imposée par §9.1. L'état vide n'apparaît que si les **trois** manquent, et **une seule** suffit à faire rendre le bloc : le **« ou »** est donc exact là où un « et » ment. Voix active, français simple (§10.1). La rédaction back omettait le titre ; la rédaction front enfilait trois négations. |
| 8 | **Paire `--laiton-texte` sur `--calcaire-creux`** absente de §12 mais exigée par §9.1 | **Employée. 4,75:1, recalculée, AA. À porter dans MASTER §12.3** | La contradiction est **dans MASTER**. La prescription explicite de §9.1 gagne, le ratio est AA, l'apparence n'existe que dans l'éditeur. Même forme que l'amendement 5 du contrat #2 : extrapolation minimale, nommée, ratifiable. Le repli (`--texte`, listée) est écrit mais **n'est pas pris** — il perdrait le caractère laiton pour rien. |
| 9 | **`loading`/`fetchpriority`** : §6.9 exige `lazy` sans nuance, le cœur décide depuis WP 6.3 | **Laissés au cœur, écart nommé** | `wp_get_loading_optimization_attributes()` rend `lazy` partout **sauf** la première image dans la fenêtre — c'est exactement « chargement différé **sous la ligne de flottaison** » de BRIEF §12, donc **mieux aligné sur l'intention** que la lettre de §6.9. À ratifier dans MASTER §6.9. La conformité littérale coûterait un `'loading' => 'lazy'`. |
| 10 | **Dette T7** (bouton du cœur hors jetons), dite « payée par la première issue de composants » | **Hors périmètre. T7 reste ouverte** | `allowedBlocks` rend `core/button` **mécaniquement inatteignable dans une fiche** : y écrire du CSS serait écrire pour un cas que le bloc ne peut pas produire. Et `assets/css/blocs/core-button.css` n'est le fichier « au nom unique » d'**aucune** des six chaînes : deux qui l'écrivent s'écrasent. La formulation d'`ETAT.md` **n'est pas décidable par une chaîne** quand six tournent. **À attribuer nommément par `/lead-mtb`.** |
| 11 | **`example` dans `block.json`** — back le livrait sous réserve d'approbation | **Livré** | Les deux chaînes ne contiennent **aucun fait d'élevage** : ni nom de chien, ni date, ni LOF, ni résultat, ni nom de race. Le retirer priverait l'éleveuse de l'aperçu **au moment précis où elle cherche quoi insérer** — c'est l'arbitrage de la décision 15, où la contrainte 1 gagne. `photo_id` reste à `0` : **zéro image distante**. |

---

## 18. Dettes et questions créées par cette issue

**À porter dans `docs/ETAT.md`, à ne pas redécouvrir dans trois lots.**

1. **⚑ Une feuille de `assets/css/blocs/` n'atteint jamais la toile de l'éditeur** (§10, vérifié dans le
   cœur 6.9). **Concerne les six chaînes du lot.** Conséquences : l'apparence §9.1 est inerte côté
   éditeur, et **l'éleveuse ne voit pas l'effet du réglage « Cadrage de la photo »** (défaut de D1). À
   solder par **(a)** compléter le mécanisme générique de `functions.php` — recommandé, bénéfique aux
   six — ou **(b)** `editor.css`. **Une seule chaîne, attribuée nommément par `/lead-mtb`.**
2. **`includes/blocks/categorie-mtb/` n'est attribuée à personne** (§10). Sans elle, les trois blocs de
   composants du lot sont **non regroupés dans l'insérteur** — trouvables par recherche, non fatals,
   mais c'est un recul de la contrainte 1. Le module idempotent est spécifié et prêt.
3. **Un bloc dans une Page est plafonné à 36 rem, `alignfull` compris** (§9). Sans effet sur ce
   composant, **bloquant pour #6 et #8**. À solder par l'epic Gabarits (#16/#17).
4. **`overflow-wrap: break-word` sur `h2`/`h3` appartient à `base.css`**, pas à une feuille de
   composant : il manque à **tous** les titres du site, pas seulement aux fiches. Posé ici scopé au bloc
   parce que `base.css` est propriété de la chaîne #6 dans ce lot. **Dette à hisser** quand le fichier
   sera relâché.
5. **Trois paires de contraste manquent à MASTER §12** : `--laiton-texte` sur `--calcaire-creux`
   (4,75:1, arbitrée ici) plus les deux de l'amendement 5 du contrat #2. Pour `lead-design-mtb`.
6. **`--point-interet` : le défaut `centre` rend le repli `50% 38%` de §6.2 inatteignable.**
   `includes/content/chien/choix.php:92` a déjà fixé ce défaut — **divergence préexistante**, alignée ici
   pour ne pas avoir deux comportements sur le même réglage. Conséquence : le pari « la tête d'un chien
   en pied est au-dessus du centre géométrique », que MASTER §11.4 identifie **lui-même** comme à
   revérifier, ne s'applique plus par défaut. Deux réponses possibles — le thème mappe `centre` vers
   `50% 38%` (le libellé « Centre » mentirait légèrement), ou MASTER révise son repli. **Décision
   visuelle : elle appartient à MASTER**, pas à cette chaîne.
7. **Cinq extrapolations nommées, à ratifier par `lead-design-mtb`**, aucune inventée en silence :
   écart image ↔ légende `--e-2` (MASTER ne le chiffre nulle part → §6.8) · marge basse d'une liste dans
   la prose `--e-4` (→ §5.1) · retrait et marqueurs de liste **laissés à l'agent utilisateur**, aucune
   valeur écrite (→ §5.1) · les cinq valeurs de cadrage en **mots-clés CSS** plutôt qu'en pourcentages
   inventés (→ §6.2, où la question D5 prévoit déjà une révision après import des photos) ·
   `loading`/`fetchpriority` laissés au cœur (→ §6.9).
8. **Trois limites nommées, non comblées** : le glyphe d'image cassée que certains moteurs dessinent est
   hors de portée du CSS (§6.6 « aucun pictogramme cassé ») — à constater dans les trois moteurs ·
   la règle « largeur naturelle × 1,5 » de §6.6 n'est franchissable que par un original < 384 px,
   exposition écrite plutôt que traitée · **un bloc ne connaît pas ses voisins** : rien n'empêche un
   « Sous-titre » avant tout « Titre de section » sur une page. Trois atténuations — deux niveaux
   seulement, l'aide du réglage, la fiche d'aide. Une validation inter-blocs supposerait de parcourir
   `post_content` au rendu : **coût réel, bénéfice hypothétique, non planifié, signalé.**
9. **Note pour les chaînes de reprise #19-#21** : un `<h3>` intercalé dans un flux de texte de l'ancien
   site **ne rentre pas dans la prose** — il devient un **second bloc Fiche d'information** avec
   `niveau_titre: "h3"`. Un `<table>` ou une image intercalée n'a **aucune place** dans ce composant.
   **À signaler quand `docs/migration/source/` existera, pas à deviner aujourd'hui.**

**Aucune question bloquante de fait d'élevage.** Ce composant n'affiche aucune donnée d'élevage : il ne
lit ni portée, ni chien, ni résultat, et n'invente aucun nom, aucune date, aucun résultat.

---

## 19. Où ce composant s'utilise, où il ne s'utilise pas

**S'utilise** dans une Page ou un Article — les pages de contenu de BRIEF §5.4 : BHPL, BHPL en France,
Littérature, Placement, présentation de la race. **Plusieurs fiches par page.**

**Ne s'utilise pas** sur une fiche Portée, Chien ou Résultat : elles emploient l'écran classique
(décision 17), l'insérteur n'y existe pas. **« Les pages se composent, les fiches se remplissent. »**

**Ne sert jamais à décrire une portée ni un chien.** ⚑ Ce composant est l'outil parfait pour recomposer
une portée à la main — c'est-à-dire pour **faire revenir la douleur IONOS par la porte de service**. Une
portée se remplit une fois et se dérive partout (contrainte 3). **La fiche d'aide doit le dire en une
phrase, sans détour.**

---

# Amendements — 2026-08-17, après implémentation

Le corps du contrat s'est révélé **faux sur quatre points**, tous découverts par les devs **en mesurant
dans la stack** plutôt qu'en supposant, et tous remontés au lieu d'être contournés en silence. Les
corrections ci-dessous sont **de même valeur contraignante** que le corps du document. Les quatre
concernent **les neuf composants suivants** autant que celui-ci.

## Amendement 1 — `wp_kses_post()` sur la sortie de `wp_get_attachment_image()` : RETIRÉ

**Le §13 se trompait, et il se contredisait avec le §5 et avec la recette V6 du §16.**

`wp-includes/kses.php:212-224` n'admet sur `img` que
`alt · align · border · height · hspace · loading · longdesc · vspace · src · usemap · width`.
Mesuré côte à côte par `dev-back-mtb` :

```
BRUT : <img width height src class alt decoding="async" loading="lazy" srcset="… 5 largeurs …" sizes="…">
KSES : <img width height src class alt loading="lazy">
```

**`srcset`, `sizes` et `decoding` disparaissent.** Et `srcset` **n'était pas rétabli** par le filtre de
contenu du cœur, précisément parce que la classe `wp-image-<id>` est celle que ce contrat fait déplacer
(§3).

Trois exigences étaient donc inconciliables avec cette ligne : le **§5** promet `srcset` et `sizes` « par
le cœur », la recette **V6** en fait un critère, et **MASTER §6.9** les exige (« `srcset` sur toutes les
tailles d'emplacement », « décalage cumulé nul »). **§5 + V6 + MASTER gagnent : la ligne du §13 est
retirée.**

`render.php` reprend donc le balisage **tel que le cœur l'a produit**, sans `kses`, avec la
justification écrite sur place. **Ce n'est pas un relâchement de sécurité** : aucune valeur saisie n'entre
dans cet appel sans passer par le cœur — la taille et la classe sont des **littéraux du code**,
l'identifiant est vérifié par `absint()` puis `'attachment' === get_post_type()`, et `alt` est échappé
par `wp_get_attachment_image()` elle-même.

> **⚑ Pour les neuf composants suivants : ne jamais passer la sortie de `wp_get_attachment_image()` à
> `wp_kses_post()`.** Elle est déjà sûre, et `kses` l'ampute. Le motif de
> `includes/fields/chien/ecran.php:624`, que le plan initial invoquait comme précédent, est à revoir par
> l'issue qui le possède — **il perd probablement `srcset` de la même façon.**

## Amendement 2 — `.wp-block-mtb-fiche-information` : « n'existe pas **côté public** »

Le §3 posait comme engagement ferme que `supports.className: false` fait que cette classe n'existe pas.
**C'est vrai côté public** (zéro occurrence dans le HTML servi, vérifié) et **faux dans le canevas de
l'éditeur**, où la racine porte `mtb-fiche-information mtb-fiche-information--editeur
wp-block-mtb-fiche-information`.

Cause, `block-editor.js:35634` :
`defaultClassName: hasLightBlockWrapper ? getBlockDefaultClassName( blockName ) : void 0` — **le cœur
l'ajoute inconditionnellement dès `apiVersion ≥ 2`, sans regarder `supports.className`.**

**L'engagement est reformulé en « n'existe pas côté public ».** La consigne au thème est **inchangée et
toujours valable** : ne jamais la styler. La feuille livrée ne la style pas.

## Amendement 3 — `wp_omit_loading_attr_threshold()` vaut **3**, pas 1

Le §5 et l'arbitrage 9 décrivaient « le cœur rend `lazy` partout **sauf sur la première image** dans la
fenêtre ». En réalité **les trois premières images du contenu** ne reçoivent aucun `loading`. L'écart
nommé avec MASTER §6.9 est donc **plus large qu'écrit** — sans changer l'arbitrage, qui reste : le
comportement du cœur sert mieux l'intention de BRIEF §12 (« chargement différé **sous la ligne de
flottaison** ») que la lettre de §6.9. **À porter dans MASTER §6.9 avec le bon chiffre.**

**Constat connexe, pour l'issue `perf`** : chaque `render_callback` décidant indépendamment, **deux
fiches avec photo produisent deux `fetchpriority="high"` sur la même page**. Ce n'est pas un défaut du
composant — c'est une conséquence du rendu par bloc, et elle se reproduira sur tout composant portant une
photo. À traiter au niveau du lot ou de l'issue `perf`, pas ici.

## Amendement 4 — `getMedia()` est déprécié en WordPress 6.9 : remplacé

Le §2 écrivait « le canevas lit l'URL par `wp.data.select('core').getMedia( photo_id )` ». Le cœur émet
désormais, à chaque chargement de l'éditeur :

> `'getMedia' is deprecated since version 6.9. Please use the 'postType', 'attachment' entity via the
> 'getEntityRecord' function instead.`

C'était le **seul** avertissement de console restant sur ce composant. La DoD interdit de livrer avec un
avertissement, le remplacement est **mécanique et sans changement de comportement**, et laisser
l'appel déprécié ferait recopier la dépréciation par les neuf composants suivants.

> **Forme gelée** : `getEntityRecord( 'postType', 'attachment', photo_id )`, et
> `hasFinishedResolution( 'getEntityRecord', array( 'postType', 'attachment', photo_id ) )` pour la
> résolution — jamais `hasFinishedResolution( 'getMedia', … )`, qui dépendait du premier.

## Amendement 5 — Poids de la feuille : le commentaire va dans ce contrat, pas dans le CSS

**Livré : 22 038 octets pour 1 930 octets de règles.** Environ **19 700 octets de commentaires**, que
j'avais exigés nommément — l'écart avec l'estimation de ≈ 6 Ko du §14 est donc **de ma responsabilité**,
pas de celle de `dev-ux-mtb`.

**Deux corrections factuelles au §14**, vérifiées dans le cœur installé et qui rendent l'écart coûteux :

1. **WordPress 6.9 a porté `styles_inline_size_limit` de 20 000 à 40 000 octets**
   (`script-loader.php:3062`, `@since 6.9.0`) — le §14 citait l'ancienne valeur.
2. Conséquence mesurée : la feuille **est bien écrite en ligne** dans le `<head>`, **zéro requête
   ajoutée**. Mais **le budget de 40 000 octets est cumulatif, partagé, et servi du plus petit au plus
   grand** : `tokens.css` et `base.css` ne déclarant pas de `path`, il est entièrement pour les feuilles
   de blocs. **À six feuilles sœurs de cette taille, le budget saute et les plus grosses repassent en
   requête HTTP.** C'est un fait de **lot**, pas de ce fichier.

**Décision : le CSS est condensé, et la justification vit ici.** ⚑ **Précédent pour les neuf composants
suivants** — le contrat gelé de l'issue est le domicile de l'argumentation ; la feuille garde ce qui
empêche une régression **sur place** et **renvoie au contrat** pour le reste :

- **conservés dans la feuille** : les trois pièges du §6 en forme courte (fusion des marges, cerne par
  pseudo-élément, pas d'`overflow: hidden`), la table de spécificité, et la justification **in situ** de
  chaque valeur brute ;
- **déplacés ici** : tout le reste de l'argumentation.

## Amendement 6 — État vide : le remplissage bas de MASTER §9.1 doit être tenu

`dev-ux-mtb` a mesuré, et signalé sans le corriger de son propre chef : la ligne 2 de l'encart est un
`<p>`, donc elle porte le `margin-block-end: var(--e-4)` de `base.css`. L'encart rend **32 px au-dessus
du texte et 48 px au-dessous**, dans un `padding: var(--e-6)`.

**MASTER §9.1 prescrit `padding: --e-6`, donc un remplissage égal.** Neutraliser une marge héritée pour
qu'une valeur **que MASTER prescrit** soit effectivement rendue **n'est pas une décision visuelle
nouvelle** : c'est rendre vraie la valeur du design system. La déclaration est donc **ajoutée** au §6 :

```
.mtb-fiche-information--editeur .mtb-fiche-information__etat-vide > p:last-child
  { margin-block-end: 0 }
```

## Ce qui est confirmé par la mesure, et qu'il ne faut pas re-vérifier

- **Le piège 2 du contrat #1 §10 est RÉEL**, démontré par expérience contrôlée : en désenregistrant le
  seul côté client, définition PHP intacte, `getInserterItems` passe de 1 à 0 et le bloc **n'est même pas
  trouvable par recherche**. Un `registerBlockType` côté client est **obligatoire**. Précision utile aux
  neuf suivants : `editeur.js` n'a besoin de fournir que **`edit` et `save`** — tout le reste vient de
  `block.json` via `unstable__bootstrapServerSideBlockDefinitions`.
- **Le fichier de rendu est inclus par un `require` nu** (`wp-includes/blocks.php:570-573`, littéral).
  Deux fiches sur une page rendent bien, **aucun `Cannot redeclare`** — parce que `render.php` ne déclare
  aucune fonction. **Le montage `rendu.php` séparé était nécessaire, pas prudentiel.**
- **Le cerne est visible au-dessus de la photo**, prouvé au pixel sur les **quatre** bords :
  `(98,118,91)` mesuré contre `(98,118,92)` attendu pour `rgb(22 36 28 / .22)` sur une photo uniforme.
  Le `::after` était bien la seule voie.
- **MASTER §2.1 est tenu** : écart entre deux filets doubles de fiches empilées, **pire cas** (deux
  fiches réduites à leur titre) = **75,72 px à 1280 px, 48,00 px à 640 et 360 px**. ≥ 48 px partout.
  **Mais c'est exactement 48,00 sous ~800 px, sans marge** : `--rythme-section` se rabat à 2,5 rem
  (40 px), et c'est le `margin-block-start: var(--e-7)` du `h2` qui tient **seul** la règle par la fusion
  `max(40, 48)`. ⚑ **Quiconque toucherait à cette marge de `h2` casserait §2.1 sur tous les composants.**
- **360 px** : `scrollWidth` = 360, aucun défilement horizontal, emplacement **324 × 216**, ratio
  1,5000. **Zoom 200 %** (viewport 640) : canal **576,00**, emplacement 576 × 384.
- **`--laiton-texte` sur `--calcaire-creux` = 4,75:1**, recalculé **indépendamment** par `dev-ux-mtb`
  après validation de sa méthode sur quatre valeurs publiées. L'arbitrage du §12 est confirmé au centième.
- **Page protégée par mot de passe : zéro fuite** — 0 occurrence de `mtb-fiche-information`, 0 de l'URL du
  fichier, 0 de l'`alt`. Seul le formulaire natif.
- **Les valeurs hostiles retombent sur le défaut** : `niveau_titre: "h1"` → `h2`, `cadrage:
  "nimportequoi"` → `centre`, `position_photo: "cote"` → `dessus`. La barrière déclarative des `enum`
  fonctionne.
- **Aller-retour d'échappement** : `Titre de <strong>section</strong> avec &amp; esperluette`,
  `Légende de la photo <ne pas interpréter>` et `l"herbe` ressortent **intacts**. Décision 20 tenue.

## Découverte majeure — pour la fiche d'aide et pour les chaînes de reprise #19-#21

> **Coller dans la prose d'une Fiche d'information un fragment contenant un seul `<h3>` ou un seul
> `<table>` ne fait ABSOLUMENT RIEN. Rien n'est inséré, rien n'est converti en paragraphe, et AUCUN
> message n'est affiché.**

Établi par les trois maillons, mesurés séparément (un collage authentique n'est pas reproductible en
navigateur sans tête — méthode nommée, pas contournée) :

1. `rawHandler` **ne perd rien** : il produit `core/heading · core/paragraph · core/table · core/list ·
   core/image`.
2. `canInsertBlockType` dans la fiche : `core/paragraph` **true**, `core/list` **true**, `core/heading`
   **false**, `core/table` **false**, `core/image` **false**.
3. `block-editor.js:12807-12820` — `replaceBlocks` boucle sur les blocs et fait une **sortie sèche**
   (`return`) au **premier** refusé : **tout le lot est abandonné, sans avis.**

Trois conséquences contraignantes :

- **La reprise du contenu ne doit JAMAIS passer par un collage.** Elle compose les blocs **côté serveur**
  (`serialize_blocks`, ou l'écriture directe des commentaires de bloc) — voie vérifiée, qui préserve tout.
- Le §18-9 est **durci** : un `<h3>` intercalé n'est pas seulement « hors de la prose », il est
  **silencieusement jeté** si l'on tente le collage. Il devient une **seconde Fiche d'information avec
  `niveau_titre: "h3"`**.
- **La fiche d'aide doit prévenir l'éleveuse** que coller du texte mis en forme depuis un traitement de
  texte ou une page web peut **ne rien produire, sans message**, et que le recours est de taper le texte
  ou de coller en texte simple.

## Écarts constatés, non corrigés, et pourquoi

1. **La feuille du bloc n'atteint pas la toile de l'éditeur** — confirmé par `dev-ux-mtb`
   (`_wp_get_iframed_editor_assets()` → feuille **absente**). L'apparence §9.1 est livrée dans son
   domicile conforme et vérifiée dans un harnais statique, **sans aucun contournement** : aucun `style=`,
   aucune ligne dans `editor.css` ni `functions.php`, aucun sélecteur d'éditeur dans la feuille. **Dette
   de lot, §10 et §18-1, à attribuer par `/lead-mtb`.**
2. **Un `h2` vide peint son filet de 6 rem dans le canevas de l'éditeur** — `__titre` y est toujours
   présent pour la saisie. Le texte fantôme « Titre de la section » l'occupe en pratique, et MASTER §9.4
   assume qu'« aucune hauteur minimale n'est imposée, le filet suit le texte ». **Éditeur seulement,
   jamais côté public.** Signalé, non traité : ni `base.css`, ni le balisage n'appartiennent à `dev-ux`.
3. **Le cœur ajoute `lock`, `metadata` et `style` aux attributs du bloc** malgré `supports` tout à
   `false`. Inertes — aucun `style=` observé en sortie. Rien à faire.
4. **`--cerne-photo` contraste ~1,54:1, pas 3,51:1.** MASTER §12.1 décrit `--laiton` (3,51:1) comme
   servant « filet bas, **cerne**, bordure d'étiquette », mais le jeton `--cerne-photo` du §5.3 vaut
   `rgb(22 36 28 / .22)`, qui composé donne **1,54:1** sur `--calcaire-creux`. §5.3 lui assigne « — » en
   contraste requis, donc **rien n'est enfreint** — c'est un liseré décoratif, la photo étant le contenu —
   **mais la ligne du §12.1 laisse croire que le cerne est en laiton.** Divergence de **documentation**,
   à trancher par `lead-design-mtb`. Le jeton est employé tel quel.
5. **Trois modules déclarent la catégorie `mtb`.** Elle a été livrée par une chaîne sœur pendant le lot —
   `wp.blocks.getCategories()` renvoie `mtb` **en première position** et le bloc est bien regroupé sous
   « Mont Brabant ». **La dette §18-2 est donc close, pas par cette chaîne.** Mais
   `galerie-photos/bootstrap.php` et `grille-chiens/bootstrap.php` ajoutent **aussi** un
   `block_categories_all`. **L'idempotence par slug tient — aucun doublon observé** — mais le contrat #1
   §10 disait « livrée **une seule fois** ». À consolider hors de ce lot.
6. **`WP_DEBUG` est actif mais `WP_DEBUG_LOG` n'est pas défini** : les erreurs partent sur `/dev/stderr`,
   donc dans `docker compose logs wordpress` et **non** dans `wp-content/debug.log` — le fichier existant
   est un reliquat. **Utile à la dette T-#31 et à toute chaîne qui croirait lire le journal.** Journal du
   conteneur après recette : **aucune ligne.**

---

# Amendements de reprise — 2026-08-17, seconde session

La chaîne d'origine s'est arrêtée avant le commit. Trois amendements de **même valeur contraignante**
que le corps du document, dont deux corrigent une **divergence entre chaînes sœurs** que le corps ne
pouvait pas voir : il a été gelé quand `includes/blocks/` était vide, avant que les cinq autres
composants du lot n'existent sur le disque.

## Amendement 7 — État vide : les crochets partagés du lot remplacent les crochets locaux

**Le §3 et l'arbitrage 5 sont corrigés. Le §6, ligne « état vide », est retiré de la feuille.**

Le corps gelait `__etat-vide`, `__etat-vide-nom`, `__etat-vide-phrase`, scopés sous le nom du bloc, et
plaçait l'apparence de MASTER §9.1 dans la feuille du bloc. **Mesuré sur le disque à la reprise, les
cinq composants sœurs du lot émettent tous trois crochets NUS** :

```
galerie-photos/editeur.js:42-44    .mtb-etat-vide .mtb-etat-vide__nom .mtb-etat-vide__phrase
derniere-portee/editeur.js:33-37   idem
grille-chiens/balisage.php:306-307 idem
```

MASTER §9.1 décrit une apparence **« identique pour les dix composants du catalogue »**. Trois
crochets scopés par bloc la rendent **impossible à écrire une seule fois** : dix copies d'une même
apparence, c'est exactement la duplication que la contrainte 3 refuse. L'arbitrage 5 avait raison sur
le **vocabulaire** (« état vide » est le terme de §9.1) et se trompait sur la **portée** — il a été
pris avant que le lot n'ait un précédent. **Le précédent du lot gagne, et il gagne au nom de §9.1.**

**Forme gelée, à l'identique de la galerie** (`galerie-photos/editeur.js:40-48`) :

```
el( 'div', { className: 'mtb-etat-vide mtb-fiche-information__vide' },
  el( 'p', { className: 'mtb-etat-vide__nom' }, 'FICHE D\'INFORMATION' ),
  el( 'p', { className: 'mtb-etat-vide__phrase' }, PHRASE )
)
```

- **Deux crochets sur la racine.** Le partagé porte l'apparence commune ; le local
  `mtb-fiche-information__vide` est **disponible pour un ajustement propre à ce composant** et
  **jamais pour porter l'apparence commune**. Rien ne l'emploie aujourd'hui, et c'est voulu : sa
  raison d'être est que la centralisation dans `editor.css` n'ait pas à rouvrir ce module.
- **Le nom du composant est émis en MAJUSCULES par le JavaScript** — `FICHE D'INFORMATION`, la chaîne
  littérale de la tâche D12 de l'issue #7. **Le §3 (« l'extension n'émet jamais de MAJUSCULES
  décoratives ») et le ⚑ précédent qu'il posait sont RETIRÉS** : la casse est ici imposée par l'issue
  et par le lot, elle ne dépend plus d'un `text-transform` que ce module ne détient pas. Corollaire
  perdu, assumé : la restitution vocale lit un sigle là où elle lisait un nom. Le prix est payé dans
  l'**éditeur seul**, jamais devant un visiteur, et l'alignement des six composants le vaut.
- **`mtb-fiche-information--editeur` reste sur la racine du canevas** (§3, écart 1). Aucune règle ne le
  lit plus, et il n'est pas retiré : c'est le discriminant documenté de la racine côté éditeur, et
  l'arbitrage 6 ne condamne un crochet émis-jamais-lu que lorsqu'il **invite à un usage fautif** — ce
  n'est pas le cas d'un préfixe d'éditeur.

### La phrase, recalée sur la forme imposée au lot

> **« Ce bloc n'affiche rien tant qu'aucun texte ni aucune photo n'est renseigné. »**

L'arbitrage 7 est **corrigé**. Sa rédaction — « tant que vous n'avez pas ajouté un titre, du texte ou
une photo » — viole la forme imposée au lot sur deux points : elle emploie **« vous »** et elle n'est
pas au **présent**. La forme du lot est : étiquette = nom du composant en majuscules, puis **une seule**
phrase « Ce bloc n'affiche rien tant que … », au présent, point final, rien d'ajouté.

Trois précisions sur le choix des mots, pour qu'il ne soit pas relu comme approximatif :

- **« photo » et non « image »** : la tâche D12 de l'issue écrit « aucun texte ni image », mais la
  règle du lot veut **le mot exact du panneau de réglages**, et le panneau dit *Photo*, *Choisir une
  photo*, *Position de la photo*, *Cadrage de la photo*. Deux mots pour un seul objet dans le même
  écran est un défaut d'interface ; **l'écart avec la lettre de l'issue est nommé ici et assumé.**
- **le titre n'est pas énuméré, et la phrase reste exacte** : un titre **est du texte**. L'encart
  n'apparaît que si les trois manquent, et une seule saisie — titre, paragraphe **ou** photo — suffit à
  faire rendre le bloc. « aucun texte » couvre donc le titre et la prose sans les épeler.
- **« ni aucune photo »** et non « ni photo » : l'accord réclame le déterminant, et le « ni … ni » rend
  vrai ce qu'un « ou » aurait faussé une fois la phrase mise à la forme négative.

### L'apparence §9.1 quitte la feuille du bloc

**Retirées de `mtb-fiche-information.css`** — les trois règles du §7 de la feuille : le cadre, la
ligne 1 en étiquette laiton, et la neutralisation de marge de l'**amendement 6**. Elles deviennent de
toute façon **du CSS mort** dès que les classes changent : leurs sélecteurs ne matchent plus rien.

**Ce qui est dû, et par qui** : l'apparence de MASTER §9.1 sur `.mtb-etat-vide`, `.mtb-etat-vide__nom`
et la neutralisation `> p:last-child` de l'amendement 6, **dans `themes/mtb/assets/css/editor.css`,
propriété de la chaîne #6**. **Aucune chaîne du lot ne doit la réécrire chez elle** : c'est précisément
la divergence qu'on éteint.

> **Correction factuelle, mesurée deux fois dans la même heure.** Cet amendement a d'abord été écrit
> avec « `editor.css` contient ZÉRO règle `.mtb-etat-vide` (4 260 octets) », et `dev-ux-mtb` l'a
> confirmé au moment de son retrait. **La chaîne #6 a livré son dû entre les deux mesures** :
> `editor.css` fait désormais **9 367 octets** et définit `.mtb-etat-vide` (l. 126),
> `.mtb-etat-vide__nom` (l. 142) et `.mtb-etat-vide__phrase` (l. 160). **L'apparence §9.1 n'est donc
> PAS « définie nulle part » — elle a son domicile unique et conforme.** La phrase initiale est
> retirée pour qu'aucune revue ne la reprenne comme un manque. Le dû est **soldé**, par #6.
> `.mtb-etat-vide__phrase` recevant finalement une règle chez #6, la note « aucune règle nulle part »
> du commentaire laissé dans la feuille du bloc est **périmée** — sans conséquence : ce module n'écrit
> aucune de ces trois règles, dans aucun des deux cas.

L'arbitrage 8 (paire `--laiton-texte` sur `--calcaire-creux`, 4,75:1, AA) **reste valable et reste à
porter dans MASTER §12.3** : il est la justification de l'étiquette laiton **où qu'elle soit écrite**.
Il change simplement de destinataire — `editor.css` au lieu de cette feuille.

## Amendement 8 — Interface de rendu réutilisable pour les gabarits #16 / #17

**Ajout au §1, qui n'exposait aucune fonction.** La décision 17 met les trois types `mtb_` sur l'écran
classique : **aucun bloc ne peut être inséré dans une fiche Portée, Chien ou Résultat** (§19). Or les
gabarits de fiche et d'archive de #16 / #17 auront à rendre le même motif — un titre, de la prose, une
photo cadrée et cernée. Sans point d'entrée, **ils le réécriraient côté thème** : deux balisages
concurrents pour un seul composant, l'un des deux dérivant en silence au premier ajustement.

> **`mtb_fiche_information_balisage( array $arguments = array() ): string`**
> Espace de noms **global**, dans `includes/blocks/fiche-information/interface.php`, sous
> `if ( ! function_exists( … ) )` — forme exacte de `includes/query/portee/bootstrap.php:19`, et
> clause d'`function_exists` de l'**amendement 3 du contrat #2**, qui cesse d'être vacante pour cette
> issue. Aucun hook n'est posé dans ce fichier. **Le thème n'écrit jamais `MTB\`** : la frontière
> reste vérifiable au grep.

| Clé | Type | Défaut | Note |
|---|---|---|---|
| `titre` | `string` | `''` | balisage en ligne admis, passé à `wp_kses_post()` |
| `niveau_titre` | `string` | `'h2'` | liste blanche `h2` / `h3`, toute autre valeur retombe sur `h2` |
| `prose` | `string` | `''` | HTML de paragraphes, **passé à `wp_kses_post()`** — voir ci-dessous |
| `photo_id` | `int` | `0` | vérifié par `absint()` puis `'attachment' === get_post_type()` |
| `photo_description` | `string` | `''` | le texte alternatif, passé brut à `wp_get_attachment_image()` |
| `photo_legende` | `string` | `''` | `esc_html()` |
| `cadrage` | `string` | `'centre'` | les cinq clés `snake_case` du §4, sinon `centre` |
| `position_photo` | `string` | `'dessus'` | `dessus` / `dessous`, sinon `dessus` |
| `classes` | `array<string>` | `array()` | classes **ajoutées** à la racine, `esc_attr()` ; `mtb-fiche-information` y est toujours et en premier |

**Retour** : le balisage littéral du §3, racine comprise — ou **la chaîne vide** quand les trois
contenus sont absents (état `rien_a_afficher` du §8, identique au bloc). **Une clé inconnue est
ignorée**, jamais rendue.

**Trois points structurants, à ne pas « améliorer »** :

1. **La composition n'existe qu'une fois.** `rendu.php` gagne une fonction `contenu()` qui compose
   titre + figure + prose dans l'ordre du §3 ; `render.php` l'appelle et l'enveloppe dans
   `get_block_wrapper_attributes()`, `mtb_fiche_information_balisage()` l'appelle et l'enveloppe dans
   un `<div>` qu'elle construit. **Deux entrées, un seul balisage.** `render.php` reste **purement
   procédural et sans aucune déclaration `function`** (§2, piège du `require` nu).
2. **`get_block_wrapper_attributes()` n'est PAS appelée par la fonction d'interface.** Hors d'un rendu
   de bloc elle ne rend pas ce qu'on croit : le thème n'est pas dans un contexte de bloc. La racine est
   construite littéralement, et `data-position` est émis **quand une photo est rendue**, comme au §3.
3. **`prose` est passée à `wp_kses_post()`, et c'est le seul écart de traitement entre les deux
   entrées.** Dans le bloc, `$content` est déjà rendu **et échappé par le cœur**, bloc enfant par bloc
   enfant : `kses` y serait redondant et amputerait un balisage légitime (§13). Par la fonction
   d'interface, la prose vient d'un **champ de saisie de fiche**, dont rien ne garantit le passage par
   le cœur : la barrière est nécessaire et son domicile est l'entrée. **Documenté sur place.** Ce
   n'est pas un double standard caché, c'est deux provenances aux garanties différentes.

**Ce que la fonction ne fait pas** : elle **ne lit aucune donnée** — ni portée, ni chien, ni résultat.
Le §1 reste vrai au mot : ce module n'expose **aucune fonction de lecture** et ne touche pas
`includes/query/`. C'est l'appelant qui lit, par `mtb_get_*`, et qui décide quoi rendre. **⚑ précédent
pour les neuf composants suivants** : un composant de mise en page expose son balisage, jamais une
requête.

**Rappel au thème, à reprendre dans le contrat de #16 / #17** : la fonction est gardée par
`function_exists()` côté extension ; **l'appelant doit l'être aussi**, sans quoi la désactivation de
`mtb-core` produit une erreur fatale de gabarit là où le §8 promet une page lisible.

## Amendement 9 — Deux dettes du §18 sont closes par la chaîne #6, pas par celle-ci

**À ne pas rapporter comme ouvertes, et à ne pas retraiter.**

1. **§18-2, catégorie de blocs** — `includes/blocks/categorie-mtb/` **existe sur le disque et
   fonctionne**, livrée par la chaîne #6. `block.json` garde `"category": "mtb"` et **ce module ne
   l'enregistre jamais**. Le §10 (« le module n'est pas créé, l'écart est rapporté ») est **soldé**.
   L'écart 5 des amendements de première session reste vrai sur un point : `galerie-photos` et
   `grille-chiens` ajoutent **aussi** un `block_categories_all`. Idempotence par slug, aucun doublon
   observé, **à consolider hors de ce lot**.
2. **§10 et §18-1, la feuille de bloc n'atteint pas la toile de l'éditeur** — la cause a été
   **mesurée dans le cœur 6.9 par le lot** et elle est plus précise que le §10 : les feuilles de blocs
   sont déclarées avec `deps => array( 'mtb-jetons' )`, et `mtb-jetons` n'est enregistrée que sur
   `wp_enqueue_scripts`, **jamais en administration** — `WP_Dependencies::all_deps()` écarte alors
   l'item entier. **Correction d'une ligne dans `functions.php`, attribuée à la chaîne #6** — c'est la
   résolution **(a)** que le §10 recommandait, au mécanisme et non par une liste. **Ce n'est donc plus
   une dette de ce contrat mais une dépendance résolue ailleurs.** Conséquence à vérifier **après**
   l'atterrissage de #6, et non avant : le réglage « Cadrage de la photo » redevient visible dans le
   canevas, et le défaut de **D1** signalé au §10 tombe. **Ce module ne touche ni `functions.php`, ni
   `editor.css`.**
