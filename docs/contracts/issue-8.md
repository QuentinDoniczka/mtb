# Contrat d'interface — Issue #8 — Composant Galerie photos

**Gelé le 2026-08-17.** Réconcilie les deux plans de `leaddev-back-mtb` et `leaddev-front-mtb`, qui ne
se sont pas vus. Six chaînes de composants tournaient en parallèle dans le même arbre de travail
(#6, #7, #8, #12, #13, #14), sans branche.

**Approche retenue** : bloc minimal rendu côté serveur, grille gelée par `MASTER.md` §6.7, **aucun
réglage de disposition**, chaque vignette étant un lien vers le fichier pleine taille. La visionneuse,
le cadrage par photo et la conversion en formats modernes sont **hors périmètre**, chacun avec sa
raison écrite en section 9.

---

## 1. Fonctions de lecture exposées par l'extension

**Aucune.** Cette issue ne crée, ne modifie et ne consomme aucune fonction `mtb_get_*`.

Elle **n'ajoute rien à la base** : aucun type de contenu, aucune taxonomie, aucune clé de méta, aucune
option. La signature de `mtb_core_empreinte` ne compte que les types et taxonomies `mtb_`
(contrat #1 §4) : elle ne bouge pas, donc **aucun `flush_rewrite_rules()` n'est déclenché**.

### 1.1 La surface globale reste close — arbitrage 5

`leaddev-back-mtb` proposait deux fonctions globales, `mtb_galerie_photos_rendu()` et
`mtb_galerie_photos_identifiants()`. **Refusé.** Le contrat #1 §5 gèle que la surface globale est
close : quatre constantes, plus les fonctions de lecture `mtb_*` de `includes/query/`, **et rien
d'autre**. Une fonction de rendu déclarée depuis `includes/blocks/` en serait une dérogation.

Elle est inutile, ce qui règle la question :

- **Réutilisation par #16/#17** : `render_block( array( 'blockName' => 'mtb/galerie-photos', 'attrs' => array( 'photos' => $identifiants ) ) )`.
  Fonction du cœur, **zéro surface globale nouvelle, une seule implémentation**. C'est le chemin
  normatif, et le seul que les issues futures ont besoin de connaître.
- **La normalisation `_mtb_galerie` n'a pas de client.** La divergence de stockage entre portée
  (tableau) et chien (chaîne « 1,2,3 »), consignée au contrat #4 §5, est **déjà absorbée par les
  fonctions de lecture des chaînes propriétaires** : `mtb_get_portee()` et `mtb_get_chien()` rendent
  toutes deux la forme hydratée `array( array( 'id' => int, 'alt' => string ), … )`
  (`query/portee/hydratation.php:471-475`, `query/chien/lecture.php:609-625`). #16/#17 en tirent les
  identifiants et les passent. C'est la décision 19 appliquée telle quelle : le propriétaire de la
  donnée possède la lecture, le consommateur ne réimplémente rien.

### 1.2 La fonction de rendu interne — sous espace de noms, jamais globale

```php
MTB\Core\Blocks\GaleriePhotos\rendre( array $identifiants, array $options = array() ): string
```

Déclarée dans `rendu.php`, **sous espace de noms**, donc hors de la surface globale que le contrat #1 §5
ferme. `render.php` l'appelle. Un module frère de `mtb-core` **peut** l'appeler ; le chemin recommandé
reste `render_block()`.

- Renvoie le balisage complet, **déjà échappé**.
- Renvoie **`''`** — jamais `null`, jamais un `<ul>` vide, jamais un enrobage — quand `$identifiants`
  est vide **ou** qu'aucun identifiant ne survit au filtre de validité de la section 4.
- Ne lit aucune méta, n'écrit rien, n'imprime rien, n'émet aucune requête sortante.

`$options`, **pauvre exprès**. Toute clé inconnue est ignorée sans erreur.

| Clé | Type | Défaut |
|---|---|---|
| `taille` | `string` (nom de sous-taille WordPress) | `'medium'` |
| `sizes` | `string` | la valeur dérivée en section 5.2 |

**Droit explicite accordé à #16/#17** : ajouter des clés, à condition que tout défaut reproduise le
comportement d'aujourd'hui et que la clé soit documentée dans le contrat de leur issue. Aucun filtre
WordPress n'est ouvert : l'appelant est toujours du PHP de l'extension.

**Le thème n'appelle jamais ni `render_block()` sur ce bloc, ni la fonction sous espace de noms.** La
formulation vérifiable par `grep` du contrat #1 §8 reste intacte : un fichier de `themes/mtb/` qui
contient `MTB\` ou `mtb_galerie_photos` est en infraction.

---

## 2. Blocs enregistrés

### `mtb/galerie-photos`

| Champ | Valeur |
|---|---|
| `$schema` | **absent** — `schemas.wp.org` est un domaine tiers. Jamais atteint au rendu, mais on ne l'écrit pas. |
| `apiVersion` | `3` |
| `name` | `mtb/galerie-photos` |
| `title` | **`Galerie photos`** — libellé gelé par `MASTER.md` §10.2 |
| `category` | `mtb` — voir la garde de la section 2.2 |
| `icon` | `format-gallery` (Dashicon du cœur, servi depuis notre domaine) |
| `description` | `Affiche une série de photos en vignettes. Chaque vignette ouvre la photo en grand.` |
| `keywords` | `["photos", "galerie", "vignettes"]` — jamais « diaporama » (carrousel interdit §13), jamais « média » (mot interdit §10.4) |
| `example` | **absent** — l'`example` du cœur est ce qui charge les 5 images de `s.w.org` de la dette T4. Conséquence assumée : **aucun aperçu du bloc dans l'insérteur** ; le nom et l'icône suffisent à l'identifier. |
| `render` | `file:./render.php` |
| `editorScript` | **`mtb-galerie-photos-editeur`** — une **poignée**, jamais `file:` (contrat #1 §10 pt 1) |
| `editorStyle` | **`mtb-galerie-photos-editeur-style`** — une poignée |
| `style` | **`mtb-galerie-photos-style`** — une poignée. Voir la section 3. |

**Poignées** : nommées `mtb-<module>-<usage>` par contrat #1 §6, et enregistrées par
`wp_register_style()` / `wp_register_script()` dans `bootstrap.php`, non par `file:`.

> Constat de `leaddev-front-mtb`, consigné pour qu'on ne le re-litige pas : `"style"` et `"editorStyle"`
> en `file:` seraient **sans danger**. Le piège du contrat #1 §10 pt 1 (`editeur.asset.php` manquant →
> `_doing_it_wrong`) ne concerne que `editorScript` et `viewScript` ; `register_block_style_handle()`
> résout l'URI et prend `filemtime()` comme version, sans jamais chercher d'`asset.php`. Nous
> enregistrons quand même les poignées nous-mêmes, pour deux lignes, afin que les noms respectent la
> convention gelée et restent lisibles dans la file.

### 2.1 Attributs

```json
{ "photos": { "type": "array", "default": [], "items": { "type": "integer" } } }
```

**Un seul attribut, et il ne contient que des identifiants de pièce jointe, dans l'ordre voulu.**

`items` n'est pas cosmétique : c'est ce qui fait valider et assainir le tableau par
`WP_Block_Type::prepare_attributes_for_render()` et par le point REST `block-renderer`. Sans lui, un
`["12","abc"]` bricolé à la main passerait tel quel.

**Ce qui n'est JAMAIS stocké dans le bloc** — ni URL, ni nom de fichier, ni largeur, ni hauteur, ni
légende, **ni texte alternatif** :

> `core/gallery` recopie l'`alt` de la médiathèque **dans le contenu de la page** au moment de
> l'insertion. Une description corrigée six mois plus tard n'atteint jamais la page déjà enregistrée.
> C'est la **contrainte 3** (« le contenu structuré ne se recopie jamais ») enfreinte **en silence**,
> sur la donnée même que D7 rend bloquante. Tout est donc résolu au rendu depuis la médiathèque : elle
> corrige une description **une fois**, et les douze pages qui montrent cette photo suivent.

**Aucun réglage de disposition.** `MASTER.md` §6.7 gèle la grille, §6.1 assigne `3/2` à une vignette de
galerie, §14 interdit à l'éditrice la largeur de colonne, la grille personnalisée, l'alignement et
l'espacement. Il ne reste rien de légitime à régler. Son réglage réel est **quelles photos et dans quel
ordre** — exactement ce que le BRIEF §6 promet.

### 2.2 `supports` — ce que chaque clé ferme

```json
{ "html": false, "className": false, "customClassName": false,
  "anchor": false, "align": false, "reusable": false, "multiple": true }
```

| Clé | Défaut du cœur | Ce que le `false` ferme |
|---|---|---|
| `html` | `true` | Retire **« Modifier en HTML »** du menu du bloc. La porte de sortie la plus directe vers du balisage et des classes de son cru (§13 « Éditrice », §14). |
| `customClassName` | `true` | Retire **« Classe(s) CSS additionnelle(s) »** du panneau Avancé. §14 : « un CSS personnalisé, une classe personnalisée ». **Sans ces deux clés, le verrou de `theme.json` fuit.** |
| `className` | `true` | Le cœur n'ajoute pas `wp-block-mtb-galerie-photos`. **Un seul crochet de classe racine**, identique que le balisage vienne du bloc ou de `render_block()` appelé par #16/#17 : c'est ce qui rend la promesse de réutilisation réelle. Corollaire : **`get_block_wrapper_attributes()` n'est pas appelé** ; la racine est émise littéralement. |
| `anchor`, `align` | `false`/`false` | Aucun alignement, aucune largeur étendue. Le canal large est posé **par le CSS**, pas par un bouton (§14 : « la largeur d'une colonne »). |
| `reusable` | `true` | Retire « Créer un motif ». Un motif **recopierait la liste de photos** dans un second contenu : contrainte 3, sous une forme que personne ne verrait venir. |
| `multiple` | `true` | Conservé : plusieurs galeries par page sont légitimes. |

Couleur, typographie, espacement, ombre, duotone, `dimensions`, `border`, `layout` : **non déclarés**,
donc désactivés. **Règle** : aucune clé de `supports` n'est ajoutée sans qu'une issue le justifie par
écrit.

### 2.3 La garde de catégorie — parade à un dossier que personne ne possède

Le contrat #1 §10 confie la **catégorie de blocs `mtb`** (« Mont Brabant », filtre
`block_categories_all`) à `includes/blocks/categorie-mtb/`, livré **une seule fois** par « la première
issue de composants ». **Ce dossier n'existe pas, et six chaînes de composants tournaient en parallèle,
aucune ne l'ayant dans son empreinte.** Six chaînes écrivant le même chemin, c'est l'index central que
la décision 9 interdit, sous sa forme la plus dangereuse : dernier arrivé gagne, en silence.

Parade retenue, dans **mon** `bootstrap.php` : `add_filter( 'block_categories_all', …, 10, 1 )` dont le
rappel

1. parcourt le tableau reçu ;
2. si une entrée porte `slug === 'mtb'`, **retourne le tableau inchangé** ;
3. sinon ajoute **en fin de liste** `array( 'slug' => 'mtb', 'title' => 'Mont Brabant', 'icon' => null )`.

Ajout **en fin**, jamais en tête : aucun onglet du cœur n'est déplacé. Six modules exécutant cette même
garde sont inoffensifs — le premier gagne, les cinq autres constatent la présence et rendent la main —
aucun fichier n'est partagé, et **les six gardes deviennent inertes le jour où `categorie-mtb/` est
livré**. Le titre « Mont Brabant » est celui que le contrat #1 §10 avait déjà retenu : rien d'inventé.

**Écarté** : se rattacher à la catégorie `media` du cœur, dont le libellé français est « Médias » — mot
**interdit à l'écran** par `MASTER.md` §10.4 — et qui obligerait à réécrire la fiche d'aide plus tard.

---

## 3. Où vit la feuille de style — arbitrage 1, le plus disputé

`MASTER.md` §13 interdit toute valeur brute hors de `tokens.css`, et le contrat #1 §8 interdit à
l'extension d'émettre une règle visuelle. Or **il n'existe aucune feuille de composant du thème que
cette issue puisse écrire** : `assets/css/blocs/` ne contient qu'un `.gitkeep`, `base.css` appartient à
la chaîne #6 dans ce lot, et `editor.css`, `theme.json`, `functions.php` sont hors empreinte.

Deux variantes ont été planifiées. **Contenu de la feuille identique ; seul le chemin change.**

| | Variante T — thème | **Variante P — extension (RETENUE)** |
|---|---|---|
| Chemin | `themes/mtb/assets/css/blocs/mtb-galerie-photos.css`, ramassée par `mtb_feuilles_de_blocs` (`functions.php:194-224`) | `includes/blocks/galerie-photos/galerie.css`, poignée en `"style"` du `block.json` |
| Contrat #1 §8 | intact | **dérogation, nommée en section 10** |
| Empreinte | +1 fichier hors empreinte écrite | respectée à la lettre |
| **Toile de l'éditeur** | **rien** — voir ci-dessous | **habillée par la même feuille** |
| Duplication | la grille entière recopiée dans `editeur.css` — **forme exacte de T9** | **aucune** ; `editeur.css` ne porte que l'état vide §9.1 |
| Jetons absents (thème échangé) | sans objet | grille survivante, **discipline photographique détruite** — voir 3.2 |

### 3.1 Le fait mécanique qui tranche

`leaddev-front-mtb` l'a établi mécanisme par mécanisme, et il faut le consigner parce qu'il est
contre-intuitif :

> **`wp_enqueue_block_style()` est côté visiteur uniquement.** Il n'accroche que
> `render_block_{$nom}`, `wp_enqueue_scripts` ou `wp_footer` — **aucun des trois ne tire dans
> `wp-admin`**. Le chemin conditionnel est de plus gouverné par
> `wp_should_load_separate_core_block_assets()`, qui retourne `false` si `is_admin()`. Et les feuilles
> injectées dans l'iframe sont collectées par `_wp_get_iframed_editor_assets()`, qui rejoue
> `enqueue_block_assets`, **pas** `wp_enqueue_scripts`.

Conséquence : **en variante T, la toile ne reçoit rien.** Ni la feuille de bloc, ni les trois feuilles
d'`add_editor_style()` (qui n'incluent que `tokens.css`, `base.css`, `editor.css` — `blocs/` en est
absent, `functions.php:56`). L'aperçu de Fabienne serait une **colonne d'images brutes**, et l'éviter
imposerait de recopier la grille dans `editeur.css` : deux fichiers, deux vérités, sur le rendu même
dont dépend D7. C'est la dette T9 à la ligne près.

En variante P, un `"style"` de `block.json` passe par `enqueue_block_assets` et atteint donc **les deux
contextes, toile comprise**. Une seule feuille habille la page et l'aperçu ; `editeur.css` se réduit à
l'état vide, qui n'existe pas sur le front.

`editorStyle` reste **obligatoire dans les deux variantes** — c'est la seule voie d'un bloc d'extension
vers l'apparence d'éditeur, `editor.css` et `add_editor_style()` étant hors empreinte. Et les jetons
**sont** dans la toile : `functions.php:56-64` passe les trois feuilles à `add_editor_style()`, et
l'en-tête de `editor.css:18-21` consigne la vérification que le bloc `:root` de `tokens.css` atterrit
sur `.editor-styles-wrapper`. Un `editorStyle` d'extension qui écrit `var(--laiton)` **résout**.

### 3.2 Ce que la variante P coûte, sans l'atténuer

`leaddev-front-mtb` a chiffré la dégradation si les jetons disparaissent (thème échangé un jour) :

| `var()` privé de `tokens.css` | Valeur calculée | Effet visible |
|---|---|---|
| `gap: var(--e-3)` | `normal` → 0 | les vignettes se touchent |
| `aspect-ratio: var(--r-paysage)` | `auto` | **le cadre ne gagne plus** : hauteurs en escalier — §6.3 rompu |
| `background-color: var(--calcaire-creux)` | `transparent` | plus de fond pour le PNG détouré ni pour l'image en échec |
| `box-shadow: var(--cerne-photo)` | `none` | plus de cerne — §6.6 rompu |
| la grille elle-même | intacte (aucun jeton) | survit |

**Et le durcissement évident — écrire `var(--e-3, .75rem)` — est interdit par §13.** La variante P ne
peut donc pas être rendue robuste sans violer un interdit. C'est le coût réel, il est assumé, et il est
tracé comme dette **T15**.

### 3.3 Le placement en canal large est conservé, et la dérogation s'en trouve approfondie

`MASTER.md` §7.4 pt 5 place la galerie dans le **canal large**. La règle de placement doit nommer les
lignes de grille du thème (`large-debut` / `large-fin`, `base.css:479-485`). Une feuille d'extension qui
les nomme **encode la structure de grille du thème** : la dérogation au contrat #1 §8 s'aggrave.

**Décision : on l'écrit quand même**, et on le dit. L'alternative — omettre la règle — ferait rendre la
galerie dans le canal texte : **1 à 3 colonnes au lieu de 7 au-delà de ~640 px**, soit §7.4 pt 5 non
tenu et un composant visiblement raté sur un écran d'ordinateur. Entre une dérogation déjà prise qui
s'approfondit d'une règle et une exigence du système de design non tenue, la dérogation coûte moins.

---

## 4. Balisage gelé

Émis par le serveur, **identique** que l'appelant soit le bloc ou `render_block()` depuis #16/#17.

```html
<div class="mtb-galerie-photos" data-mtb-total="12">
  <ul class="mtb-galerie-photos__grille" role="list">
    <li class="mtb-galerie-photos__element">
      <a class="mtb-galerie-photos__lien"
         href="{URL du fichier pleine taille}"
         data-mtb-photo="128" data-mtb-rang="1">
        <img class="mtb-galerie-photos__image"
             src="…" srcset="…" sizes="…" width="…" height="…"
             alt="…tel quel dans la médiathèque, vide compris…"
             loading="lazy" decoding="async">
        <span class="mtb-galerie-photos__rang">Photo 1 sur 12</span>
      </a>
    </li>
    …
  </ul>
</div>
```

**Aucun style en ligne, aucune classe du cœur, aucun `title`, aucun `fetchpriority`.**

### 4.1 La structure à deux niveaux est obligatoire — arbitrage 2

`leaddev-back-mtb` proposait un `<ul>` racine unique. **Refusé, sur preuve.**

`base.css:507-510` neutralise le cœur à **(0,3,0)** avec `margin-inline: 0 !important` sur
`.mtb-canal.is-layout-constrained > *:not(.alignwide)`. Une règle de placement en canal large posée sur
l'**enfant direct** du canal perd la course. À un seul niveau, il faudrait soit un second `!important`
du thème (le premier est documenté comme le seul), soit une surenchère de spécificité.

À deux niveaux, les deux préoccupations se séparent proprement :

- le `<div>` extérieur prend le **placement** (`grid-column`), que la neutralisation ne touche pas ;
- le `<ul>` intérieur, hors de portée du sélecteur `>`, prend le **plafond `--l-large`** et la
  **grille**.

Zéro `!important`, zéro course de spécificité.

**Note de portée** : la règle de placement exige que le `<div>` soit **enfant direct de `.mtb-canal`**.
Si l'imbrication d'une page l'en écarte, la galerie retombe dans le canal texte. **La grille elle-même
fonctionne partout** — les deux préoccupations sont séparées. À vérifier en Docker (contrôle 20).

### 4.2 Crochets de classes — liste close

| Crochet | Élément | Ce qu'il identifie |
|---|---|---|
| `.mtb-galerie-photos` | `div` racine | l'emplacement du composant dans le canal |
| `.mtb-galerie-photos__grille` | `ul` | la grille §6.7, le plafond `--l-large` |
| `.mtb-galerie-photos__element` | `li` | une case de la grille |
| `.mtb-galerie-photos__lien` | `a` | **le cadre** : ratio, fond, cible tactile, cible du focus |
| `.mtb-galerie-photos__lien::after` | — | le cerne `--cerne-photo`, dans tous les états |
| `.mtb-galerie-photos__image` | `img` | le recadrage `cover` + `object-position` |
| `.mtb-galerie-photos__rang` | `span` | **le nom accessible** (section 6) — masqué à l'œil |

Le préfixe est **`mtb-galerie-photos`**, pas `mtb-galerie` : contrat #1 §8 gèle `mtb-<bloc>` /
`mtb-<bloc>__<element>`, et le bloc s'appelle `galerie-photos`. *(`leaddev-front-mtb` proposait la forme
courte ; la forme longue est retenue pour rester littéralement conforme.)*

`role="list"` est **obligatoire, pas décoratif** : la feuille retire `list-style`, et Safari retire
alors la sémantique de liste. Un attribut, et « liste de 12 éléments » revient à la synthèse vocale.

### 4.3 Crochets de l'état vide côté éditeur — noms à partager avec les cinq chaînes sœurs

```html
<div class="mtb-etat-vide mtb-galerie-photos__vide">
  <p class="mtb-etat-vide__nom">GALERIE PHOTOS</p>
  <p class="mtb-etat-vide__phrase">Ce bloc n'affiche rien tant qu'aucune photo n'est choisie.</p>
  <button>Ajouter des photos</button>
</div>
```

**Deux crochets sur la racine, volontairement** : `mtb-etat-vide` est le crochet **partagé** — retenu
sur le vocabulaire de `MASTER.md` §9.1 lui-même (« L'état vide côté éditeur ») — pour qu'une règle
unique dans `editor.css` absorbe un jour les six copies ; `mtb-galerie-photos__vide` est le crochet
local. **La centralisation se fera alors sans toucher au JavaScript** : il suffira de supprimer la règle
locale. Dette **T13**.

### 4.4 Aucun titre, aucune légende, aucun compteur visible

La galerie ne porte pas de `h2` : sur une fiche c'est la fiche qui titre (§7.4 pt 5, §7.5 pt 7) et
§10.2 note « Galerie photos → (pas de titre) » côté public. **Aucune incidence sur le plan de titres** :
un seul `<h1>`, hiérarchie intacte.

Ni légende, ni compteur « 3 / 12 » aujourd'hui — voir section 9.

---

## 5. Rendu, dimensionnement et performance

### 5.1 Algorithme

`render.php`, ~15 lignes, aucune logique de rendu :

```
$html = MTB\Core\Blocks\GaleriePhotos\rendre( (array) ( $attributes['photos'] ?? array() ) );
if ( '' === $html ) { return; }          // rien du tout dans la page — MASTER §9.3
echo $html;                              // phpcs:ignore, justifié ci-dessous
```

La sortie anticipée est **avant toute émission** : pas de `<div>` d'enrobage, pas de `<ul>` vide.

Le `phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped` est **motivé** : `rendre()` échappe
pièce par pièce, et repasser par `wp_kses_post()` **retirerait les crochets `data-mtb-*` gelés pour la
visionneuse** ainsi que, selon la version, `decoding`. Un second filtrage qui détruit le contrat est
pire qu'un `echo` justifié.

`rendre()`, dans cet ordre :

1. **Normalisation défensive** : chaque entrée castée en `int`, les `<= 0` écartés. **Aucun
   dédoublonnage** — si un identifiant apparaît deux fois, la photo apparaît deux fois ; l'écarter en
   silence serait décider à sa place.
2. **Filtre de validité**, une passe, trois conditions. Un identifiant est retenu si et seulement si :
   - `'attachment' === get_post_type( $id )` — motif de `hydratation.php:465` et `lecture.php:570` ;
   - `wp_attachment_is_image( $id )` — **plus strict** que le motif existant, qui laisserait passer un
     PDF ; ferme le cas « elle a choisi un document dans la fenêtre des photos » ;
   - `'' !== wp_get_attachment_image_url( $id, 'full' )` — sans URL, le lien serait `href=""`. Écarter
     ici **supprime toute branche au rendu**.
3. **Aucun identifiant retenu → `return ''`.** Point unique qui réalise §9.3 (« Une galerie vide : le
   bloc n'est pas rendu ») **et** le cas « toutes les photos ont disparu ». Un seul chemin, un seul
   comportement.
4. `$total = count( $retenues )` — compté **sur les photos réellement rendues**, jamais sur les
   identifiants stockés. Une photo supprimée ne laisse pas de trou dans la numérotation annoncée.
   Règle déjà en vigueur à `fields/chien/ecran.php:599-603`.
5. Émission du balisage de la section 4, rang de 1 à `$total`, **ordre du DOM = ordre enregistré**.

**Échappement, exhaustivement** : `esc_url()` sur le `href` ; `esc_attr()` sur `data-mtb-photo`,
`data-mtb-rang`, `data-mtb-total` ; `esc_html()` sur « Photo N sur T ». L'`alt`, le `srcset` et le
`sizes` sont échappés par `wp_get_attachment_image()`. Aucune donnée d'origine utilisateur n'atteint le
HTML autrement.

### 5.2 `sizes` — dérivé, pas choisi

`auto-fill` n'est pas exprimable en `sizes`, mais **n est une fonction déterministe de la largeur de
fenêtre**, donc la largeur de case est bornable exactement. Avec W = largeur du canal large =
`min(V − 2·marge, 1088)` et n = `⌊(W+12)/156⌋`, la case vaut `(W − 12(n−1))/n`, de maximum
`144 + 156/n` :

| n | Bande de fenêtre | Case max |
|---|---|---|
| 1 | V < 333 px | ~300 px |
| 2 | 333 – 507 | **222** |
| 3 | 507 – 680 | **196** |
| 7 | ≥ 1176 | 145 |

D'où la valeur **gelée**, chaque nombre étant la borne supérieure exacte de sa bande :

```
sizes="(min-width: 32rem) 196px, (min-width: 21rem) 222px, 90vw"
```

`90vw` et non `100vw` : `100vw` surestimerait de 10 % et pourrait faire monter d'un cran.

**Sans cette valeur, le défaut du cœur est `sizes="(max-width: 300px) 100vw, 300px"`** — faux dès la
deuxième colonne et sur tout écran large. C'est le point `perf` le plus facile à rater ici, et il
gaspille silencieusement tout le budget d'images.

### 5.3 Sous-taille demandée : `medium`, **jamais `thumbnail`**

1. `medium` (300 px sur le grand côté) est la seule taille du cœur proche de la case de 144–222 px.
2. **`thumbnail` est rognée** (`thumbnail_crop` = 1 par défaut) : elle appliquerait un **second
   recadrage, centré, qui ignore le point d'intérêt** et contredirait la règle unique du §6.2.

> **Corollaire à retenir pour tout le projet : une galerie ne demande jamais une taille rognée par
> WordPress ; c'est le CSS qui recadre.**

`wp_get_attachment_image()` construit le `srcset` seul à partir des tailles de même rapport ;
`thumbnail`, de rapport 1:1, en est exclue par le cœur.

### 5.4 Attributs d'image — explicites, pas laissés au cœur

| Attribut | Valeur | Pourquoi il est écrit |
|---|---|---|
| `loading` | `lazy` | Depuis WP 6.3, `wp_get_loading_optimization_attributes()` peut poser `eager` + `fetchpriority="high"` sur la **première grande image de la page**. Ce bloc n'est **jamais** le bandeau d'ouverture ; l'heuristique ne le sait pas. §6.9 impose `lazy` sur toute image hors bandeau. |
| `decoding` | `async` | §6.9 |
| `fetchpriority` | **jamais émis** | réservé au bandeau (§6.5) |
| `width` / `height` | ceux de la sous-taille | §6.9. Le décalage cumulé est de toute façon nul avant chargement, le **cadre** portant `aspect-ratio`. |
| `class` | `mtb-galerie-photos__image` seule | remplace les `attachment-medium size-medium` du cœur. Un crochet, pas deux. |
| `title` | **jamais** | information au survol seul — §12.10 |

### 5.5 Le trou de dimensionnement, chiffré — dette T11

| Densité | Besoin réel (case 156 px) | Candidat retenu | Gaspillage |
|---|---|---|---|
| DPR 1 | 156 px | `medium` (300 w) | ×1,9 linéaire — acceptable |
| **DPR 2** | 312 px | **`medium_large` (768 w)** | **×2,5 linéaire, ≈ ×6 en pixels** |

**Il n'existe aucune sous-taille du cœur entre 300 et 768.** Sur un téléphone DPR 2 avec 12 photos, de
l'ordre de **1,2 Mo au lieu de ~250 Ko**. Les photographies sont hors du budget de 200 Ko, mais pas hors
du public du brief (§2, §11 : personnes âgées sur mobile).

Correctif, **hors empreinte** : `add_image_size( 'mtb-vignette-galerie', 400, 400, false )` — **non
rognée**, pour que le rapport soit préservé et le recadrage reste au CSS. DPR 1 → 300 w, DPR 2 →
400 w, `sizes` inchangé. **Urgence réelle** : `add_image_size` n'agit que sur les **nouveaux** envois ;
arrivée après la reprise de contenu, elle imposerait de régénérer toutes les sous-tailles. **Elle doit
précéder #19-#21.**

### 5.6 Arithmétique de mise en page — vérifiée, pas répétée

À **360 px** : `--marge-page` = 5 vw = 18 px ; canal texte = `min(324, 576)` = 324 px ; les deux pistes
`1fr` reçoivent 0 px, donc **canal large = canal texte = 324 px**. Les deux canaux ne divergent qu'à
partir d'environ **640 px** de fenêtre — la règle de placement de la section 3.3 ne change donc rien au
repli mobile, et tout au-dessus de 640 px.

Plancher de piste = `min(100%, 9rem)` = 144 px ; gouttière 12 px.
`144n + 12(n−1) ≤ 324` → `156n ≤ 336` → **n = 2**. Deux colonnes, `2×156 + 12 = 324` **exactement la
largeur disponible : aucun défilement horizontal.** Vignette **156 × 104 px**.

**La cible de 44 px du §6.7 tient, et structurellement** : pire cas du système (juste après l'ajout
d'une colonne, piste sur son plancher) = **144 × 96 px**. La hauteur ne passerait sous 44 px que si le
plancher descendait sous 66 px ; le plancher gelé est 144 px, **2,2× au-dessus**. Écart entre cibles :
`--e-3` = 12 px ≥ `--e-2` = 8 px (§5.1). ✓

**Zoom 200 %** (1280 → 640 px CSS) : marge 32 px, canal 576 px, `156n ≤ 588` → **3 colonnes** de
184 × 123 px, aucun défilement.

**Pour information à `lead-design-mtb`, sans modification proposée** : au plafond de 68 rem la formule
gelée donne **7 colonnes de 145 × 97 px**. C'est le comportement voulu par MASTER, implémenté tel quel ;
il est signalé parce qu'une galerie de 7 vignettes de 145 px sur grand écran est proche du reproche que
§2.2 adresse au module IONOS.

### 5.7 Budget

| Ressource | Ko |
|---|---|
| CSS + JS de référence (décision 14) | 29,0 |
| + feuille du composant (visiteur) | ≈ 3,5 – 4,5 |
| + JS de front | **0** (pas de visionneuse) |
| `editeur.css`, `editeur.js` | ~4 – 6 — **administration seule, hors budget visiteur** |
| **Total page visiteur** | **≈ 33 · budget 200 · marge ≈ 167** |

**Estimation d'architecte.** Le nombre d'octets réel est mesuré par `dev-ux-mtb` (`wc -c`) et le total
de page par `test-integration-mtb`. **Rien de ceci n'est rapporté comme mesuré tant que ce n'est pas
couru.** Le composant **n'introduit pas un seul octet d'image** (§6.9).

---

## 6. Le nom accessible d'une vignette — arbitrage 3, le défaut à ne pas livrer

**Le problème, posé net** : une vignette est un **lien**, et un lien dont le seul contenu est une image
en `alt=""` est un lien **sans nom accessible** (WCAG 2.4.4 et 4.1.2). `wp_get_attachment_image()` rend
`alt=""` dès que la médiathèque n'a pas de description — et elle n'en aura pas pour une partie des ~500
photos importées. **D7 tomberait à la première photo non décrite.** Ce n'est pas une hypothèse : c'est
le cas normal.

Trois parades étaient sur la table. **Retenue : un `<span>` dans le lien, masqué visuellement.**

| Parade | Verdict |
|---|---|
| `aria-label="Voir la photo 3 sur 12"` **toujours** sur le lien | **Écartée.** `aria-label` **écrase** le nom calculé : la description que Fabienne a écrite **n'est plus lue**. Le guide (`chien-ajouter-un-chien.md:168-171`) lui promet noir sur blanc qu'elle est « lue à voix haute ». On tiendrait D7 en trahissant la promesse qui la motive. |
| Repli du serveur sur **le titre du contenu porteur** (précédent : `hydratation.php:474`) | **Écartée.** Le précédent tient pour une fiche — une photo de portée dépeint bien cette portée. Sur une **page libre**, il produirait « Photo de la page Placement » : une **description fabriquée d'une image que personne n'a vue**. C'est D11. |
| **`<span class="mtb-galerie-photos__rang">Photo 3 sur 12</span>` dans le lien** | **RETENUE.** |

Interaction exacte, gelée :

- **Photo décrite** : `<img alt="Chien de profil dans l'herbe">`. Nom du lien =
  « Chien de profil dans l'herbe Photo 3 sur 12 ». **La description est lue.**
- **Photo non décrite** : `<img alt="">` — **exactement ce que la médiathèque contient, vide compris**.
  Nom du lien = « Photo 3 sur 12 ». **Rien n'est inventé** (D11) : le rang et le total sont des faits
  produits par le rendu.
- **Jamais** de repli « Photo de la page … », jamais le titre du fichier ni la légende comme `alt`.
- Balisage **uniforme** : aucune branche PHP, l'attribut n'est pas présent une fois sur trois.
- Le `<span>` est **déjà** l'élément que la visionneuse affichera comme compteur visible « 3 / 12 » :
  zéro réécriture le jour où elle arrive.

> **Conséquence à ne jamais « nettoyer » : la règle de masquage de `.mtb-galerie-photos__rang` est
> porteuse d'accessibilité, pas décorative.** Sans elle, « Photo 3 sur 12 » s'affiche sous chaque
> vignette. Elle doit figurer dans la feuille du composant **et** dans `editeur.css`. Le mode de panne
> est du **texte visible**, pas un lien cassé — le bon sens de la dégradation.

**« Photo 3 sur 12 » n'est pas dans MASTER**, et ce n'est pas une invention de domaine : §10.3 gèle
« 3 / 12 » pour le compteur **visible** de la visionneuse, et « 3 / 12 » lu à voix haute donne « trois
barre oblique douze ». La chaîne n'emploie que des mots déjà gelés (« photo ») et des nombres — même
méthode que l'arbitrage 8 du contrat #4. **Candidat d'amendement de `MASTER.md` §10.3**, signalé à
`lead-design-mtb`, ne bloque rien.

---

## 7. La feuille du composant, règle par règle

**Littéraux autorisés — liste close, tous recopiés de MASTER, aucun n'a de jeton** : `9rem`,
`min(100%, …)`, `1fr`, `auto-fill` (§6.7 les écrit lui-même), `50% 38%` (§6.2 l'écrit lui-même),
`1px dashed` (§9.1 : « Contour tireté 1 px --laiton »), plus les non-valeurs `0`, `100%`, `""` et les
mots-clés CSS, conformément à la convention posée en tête de `base.css:25-26`.

### 7.1 Placement dans le canal — §7.1, §7.4 pt 5

```
.mtb-canal > .mtb-galerie-photos { grid-column: large-debut / large-fin; }
```

Spécificité (0,2,0) contre (0,1,0) de `base.css:515` : gagne sans dépendre de l'ordre de source. Noms de
lignes de `base.css:479-485` — MASTER §7.1 nomme `-debut`/`-fin`, jamais `-start`/`-end`.

### 7.2 La grille — §6.7, gelée

```
.mtb-galerie-photos__grille
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(min(100%, 9rem), 1fr));
  gap: var(--e-3);
  max-inline-size: var(--l-large);   /* §7.1 : plafonné à 68rem, pas à l'écran */
  margin-inline: auto;  margin-block: 0;
  padding: 0;                        /* base.css:127 zéroie la marge des ul, pas le padding */
  list-style: none;
```

Aucune requête média. Aucune variante de disposition.

### 7.3 Le cadre — §6.1, §6.3, §6.6, §8.2

```
.mtb-galerie-photos__lien
  position: relative;                        /* support du cerne en ::after */
  display: block;
  aspect-ratio: var(--r-paysage);            /* 3/2 — le cadre porte l'image */
  background-color: var(--calcaire-creux);   /* §6.6 : PNG détouré ET image en échec */
  border-radius: var(--r-0);
  text-decoration: none;                     /* annule base.css:248-254 */
  color: var(--texte-doux);                  /* encre du texte alt en échec — 5,79:1, §12.3 ✓ */
  font-family: var(--sans);
  font-size: var(--t-sm);                    /* §6.6 : « en --texte-doux, --t-sm » */
```

- **Pas de `overflow: hidden`.** `object-fit: cover` clippe déjà l'image, et §7.8 / §13 l'interdisent
  sur un conteneur susceptible de contenir du texte — or celui-ci contient le texte `alt` en cas
  d'échec.
- **`text-decoration: none` n'est pas cosmétique** : avec un `<img>` pour unique enfant, le
  soulignement de `base.css` se dessinerait en travers du bas de la photo. §8.2 borne le soulignement
  permanent au « lien **dans le contenu** ».

### 7.4 Le cerne obligatoire — §6.6, en `::after` et pas autrement

```
.mtb-galerie-photos__lien::after { content: ""; position: absolute; inset: 0;
                                   box-shadow: var(--cerne-photo); }
```

**Deux raisons dures, l'une évitant un défaut réel :**

1. `base.css:465-470` pose `box-shadow: 0 0 0 2px var(--calcaire)` sur `:focus-visible`. Un cerne porté
   par le lien serait **remplacé au focus** : la photo perdrait son cerne au clavier.
2. Un `box-shadow` `inset` de parent est peint **sous** ses enfants : posé sur le cadre, il serait
   masqué par l'image pleine boîte.

Le `::after` est au-dessus de l'image et **présent dans les quatre états** : image chargée, image en
échec, PNG détouré, cadre sans image. Il est **à l'intérieur** du `<a>` : aucun `pointer-events: none`
n'est nécessaire, et on ne l'écrit donc pas — cette propriété est l'un des contournements IONOS
explicitement jetés (§1.1).

Ce `box-shadow` est un **cerne intérieur, pas une ombre portée** : même statut que celui déjà accepté à
`base.css:393` pour le survol de bouton. L'interdit du §13 vise l'ombre douce sur carte.

### 7.5 L'image — §6.2

```
.mtb-galerie-photos__image
  display: block;
  inline-size: 100%;
  block-size: 100%;                                  /* bat base.css:131-136, (0,1,0) > (0,0,1) */
  object-fit: cover;
  object-position: var(--point-interet, 50% 38%);    /* règle unique du §6.2, verbatim */
  border-radius: var(--r-0);
```

Aucun `filter:`, jamais (§6.6, §13 — la robe est une donnée d'élevage). Aucun `border-radius: 50%`,
aucun rayon > 2 px. **Aucun filet double sur une vignette** (§2.1, liste des *jamais*). Aucune ombre.
Aucun texte sur l'image (§6.4) — le texte `alt` en échec n'est pas « sur » une image, il **est** l'image
absente.

### 7.6 Le nom accessible masqué — porteur d'accessibilité

`.mtb-galerie-photos__rang` masqué à l'œil, **lisible par une synthèse vocale** : technique de masquage
sans `display: none` ni `visibility: hidden` (qui retirent l'élément de l'arbre d'accessibilité) et sans
`overflow: hidden` sur un conteneur de texte (§13). Voir section 6.

### 7.7 Focus, survol

- **Focus** : **rien à écrire.** `base.css:465-470` couvre `:focus-visible` globalement, avec la preuve
  du §12.8 (≥ 3,77:1 sur n'importe quel fond, **y compris une photographie**). Le
  `border-radius: var(--r-1)` que §8.1 y inscrit s'applique à la forme de l'anneau, **pas** à l'image
  (qui reste `--r-0`) : à ne pas « corriger ».
- **Survol** : **aucune règle.** MASTER n'a **aucune ligne** pour un lien-photographie — ni §8, ni
  §12.9. Transplanter le trait laiton de 2 px du §8.3 sur une photographie serait l'invention que
  MASTER interdit. Rien n'est perdu : la fonction n'est pas au survol (lien, clavier, curseur), et
  aucun critère AA n'exige un état de survol. Question ouverte Q-8-B.
- **Aucune transition, aucune animation** : le contrat #2 gèle qu'aucun jeton de durée n'existe et que
  `dev-ux-mtb` n'en invente pas.

### 7.8 État vide côté éditeur — §9.1, dans `editeur.css`

```
.mtb-etat-vide
  border: 1px dashed var(--laiton);   /* 1px recopié de §9.1 ; 3,15:1 non textuel, §12.3 ✓ */
  border-radius: var(--r-0);
  background-color: var(--calcaire-creux);
  padding: var(--e-6);
  color: var(--texte-doux);           /* 5,79:1 sur creux, §12.3 ✓ */
  font-family: var(--sans);
  font-size: var(--t-sm);
  text-align: start;                  /* « aligné à gauche » */

.mtb-etat-vide__nom                   /* étiquette, ligne h4 du §4.5 */
  font-family: var(--sans); font-weight: 600; font-size: var(--t-xs);
  text-transform: uppercase; letter-spacing: .16em;
  color: var(--laiton-texte);         /* paire ratifiée en section 10, arbitrage 4 */
```

« GALERIE PHOTOS » = deux mots, sous le plafond de trois mots du §4.5. ✓

**Aucune règle d'impression** : §7.6 renvoie à un « §9.6 » qui **n'existe pas** dans MASTER (déjà
signalé par le contrat #2, amendement 5). Pas d'invention.

---

## 8. États spéciaux

| État | Émis par le serveur | Rendu par le thème |
|---|---|---|
| **galerie vide** (aucune photo choisie) | `render.php` **n'émet rien du tout** | Rien. Zéro octet dans la page. Le thème n'a **rien** à prévoir (§9.3) |
| **toutes les photos disparues** | idem : rien du tout | idem. La distinction n'existe **que** dans l'éditeur |
| **un identifiant mort parmi d'autres** | la vignette est **omise**, sans trou ni avertissement PHP ; le rang et le total sont recomptés | rien à prévoir |
| **PNG à transparence** | rien de particulier | fond du cadre `--calcaire-creux`, **jamais de damier** (§6.6) |
| **le fichier ne charge pas** | rien de particulier | cadre conservé (ratio + `--calcaire-creux` + cerne), **le texte `alt` s'affiche dedans** en `--texte-doux` `--t-sm`, **aucun pictogramme cassé** (§6.6) |
| **photo portrait dans un `3/2`** | rien de particulier | rognée haut et bas, **le cadre gagne** (§6.3) — sans point d'intérêt tant que Q-8-A n'est pas tranchée |
| **une seule photo** | une vignette | une case de largeur de colonne, **calée à gauche**, sans étirement (§9.4) |
| **état vide éditeur** | `editeur.js`, **jamais le serveur** — le cadre §9.1 n'existe pas sur le front | cadre §9.1, feuille `editorStyle` du bloc |
| **photos choisies, aucune disponible** (éditeur) | réponse vide de `serverSideRender` → `EmptyResponsePlaceholder` | même cadre §9.1, seconde phrase (section 11) |
| `donnee_absente` | **non émis** — un `alt` vide reste vide, **jamais « Non renseigné » sur une image** | le nom accessible du lien est assuré par `__rang`, pas par un texte inventé |
| `aucune_portee`, `parent_hors_elevage` | **non émis** — sans objet : ce composant ne lit aucune donnée du domaine | — |
| `page_protegee` | **non émis** — le cœur ne rend pas le contenu d'une page protégée avant saisie du mot de passe, donc **les photos ne fuient pas** | encart §9.5, propriété du gabarit. La dette **T8** reste intouchée et chez #23 |

---

## 9. Ce que cette issue ne livre pas — manques, pas oublis

1. **Formats modernes d'images — NON tenu.** §6.9 dit « formats modernes **servis par WordPress** » ;
   le cœur **ne convertit pas** JPEG → WebP à l'envoi. Il y faut `image_editor_output_format` — **zéro
   occurrence dans le dépôt** — **et** GD/Imagick compilés avec WebP dans l'image Docker, non vérifié
   et non inventé. C'est une **décision média du site entier**, pas le `bootstrap.php` d'un bloc.
   > **D8 est tenue sur le dimensionnement, `srcset`, `sizes`, `width`/`height` et le chargement
   > différé. Elle n'est PAS tenue sur les formats modernes.** Dette **T12**.
   Même contrainte de calendrier que T11 : la conversion ne rattrape pas les images déjà importées.
2. **La visionneuse.** §6.7 la décrit comme le seul composant JavaScript visuel du site, et **sanctionne
   son absence** : « Sans JS, la vignette est un lien direct vers le fichier : rien n'est perdu. »
   Ce qui est **gelé maintenant** pour qu'elle soit un pur enrichissement progressif, sans réécriture :
   `.mtb-galerie-photos__lien` porte le fichier pleine taille en `href` ; il porte `data-mtb-photo` et
   `data-mtb-rang` ; la racine porte `data-mtb-total` ; **l'ordre du DOM est l'ordre enregistré** ; le
   `<span class="…__rang">` est l'élément qui deviendra le compteur visible. La visionneuse **pourra
   ajouter des attributs `data-mtb-*`** sur `__lien` sans changer la structure ni un nom de classe.
   **Coût assumé et nommé** : tant qu'elle n'existe pas, la légende et le compteur du §6.7 n'existent
   pas non plus, et cliquer une photo **quitte la page**.
3. **« Cadrage de la photo » par photo — hors périmètre.** Le défaut `50% 38%` du §6.2 « sauve la
   majorité des cadrages sans aucune intervention », et §15 D5 dit que les photos ne sont pas
   importées : personne ne peut savoir si c'est nécessaire. Surtout, le cadrage est une propriété de
   **la photo dans la médiathèque**, pas de chaque bloc qui l'emploie — sinon la même photo se recadre
   à la main dans chaque page, et c'est une recopie.
4. **Aucune légende sous une vignette.** MASTER est **silencieux** : §6.8 prévoit une légende sous une
   photo isolée, §6.4 interdit toute légende incrustée, §6.7 ne dit rien des vignettes. Légendes et
   compteur visible arrivent **avec la visionneuse**. Compensation gratuite : le `<ul role="list">`
   annonce le nombre d'éléments à la synthèse vocale.
5. **Le composant n'est pas insérable dans une fiche, et l'énoncé de l'issue #8 se trompe sur ce point.**
   « insérable dans une page **ou une fiche** » est **factuellement impossible** : la décision 17 met
   les trois types `mtb_` sur l'écran de saisie classique — vérifié, `fields/portee/bootstrap.php:28`
   et `content/chien/bootstrap.php:22` filtrent `use_block_editor_for_post_type`. **Aucun bloc ne peut
   être inséré dans une fiche portée, chien ou résultat.** Traité par la réutilisation via
   `render_block()` (section 1.1), pas par un contournement. À dire dans la fiche d'aide avec les mots
   qu'elle connaît déjà : *« les pages se composent, les fiches se remplissent »* (décision 17).
6. **Limite nommée, sans correctif disponible** : dans la toile de l'éditeur, le contenu est contraint à
   `layout.contentSize` = 36 rem, donc la grille y montrera **3 colonnes** quand le site en montrera
   jusqu'à **7**. C'est un mensonge de la toile, du type que l'en-tête de `editor.css:5-7` s'engage à
   éviter. La seule parade serait `supports.align: ["wide"]`, qui **rendrait à l'éditrice un bouton de
   largeur que §14 lui refuse**. **Fidélité au verrou plutôt qu'à la toile**, et l'écart est nommé
   plutôt que masqué.

---

## 10. Arbitrages

| # | Désaccord entre les deux plans | Décision | Raison |
|---|---|---|---|
| 1 | **Où vit la feuille** : thème (`assets/css/blocs/`) ou extension (dossier du bloc) | **Extension (variante P)** | `wp_enqueue_block_style()` est **côté visiteur uniquement** (mécanisme établi en 3.1) : en variante T, la toile ne reçoit **rien** et il faudrait **recopier la grille entière** dans `editeur.css` — la forme exacte de T9, sur le rendu dont dépend D7. En variante P, une seule feuille habille la page **et** l'aperçu, et `editeur.css` se réduit à l'état vide. C'est aussi l'instruction littérale du chef de lot. Coût assumé et tracé : **dérogation au contrat #1 §8** et dette **T15** (section 3.2). |
| 2 | **Structure du balisage** : `<ul>` racine unique (back) ou `<div>` + `<ul>` (front) | **Deux niveaux** | Preuve : `base.css:507-510` neutralise à **(0,3,0)** avec `margin-inline: 0 !important` sur l'enfant direct du canal. À un seul niveau, la règle de placement en canal large perd la course, et il faudrait un second `!important` ou une surenchère de spécificité. À deux niveaux, placement et grille se séparent : zéro `!important`. Le préfixe reste **`mtb-galerie-photos`** (contrat #1 §8 littéral), pas la forme courte `mtb-galerie`. |
| 3 | **Nom accessible d'une photo non décrite** : `<span>` masqué (back) ou repli du serveur sur le titre du contenu (front) | **`<span class="…__rang">` masqué** | Le repli sur le titre produirait « Photo de la page Placement » sur une page libre : une **description fabriquée d'une image que personne n'a vue** — D11. Le précédent de `hydratation.php:474` ne vaut que pour une fiche, où la photo dépeint bien son sujet. Le `<span>` n'invente rien (rang et total sont des faits), **n'écrase pas** la description que Fabienne a écrite — contrairement à un `aria-label` —, garde le balisage uniforme, et **devient le compteur visible de la visionneuse** sans réécriture. |
| 4 | **§9.1 exige une paire que §12 ne liste pas** : `--laiton-texte` sur `--calcaire-creux` | **On l'écrit** | Ce n'est **pas** une invention : **§9.1 mandate lui-même** « le nom du composant en étiquette laiton » sur fond `--calcaire-creux`, et l'étiquette est `--laiton-texte` (§4.5). MASTER se contredit — §12.3 a simplement omis de tabuler une paire que §9.1 impose. Ratio **calculé** par `leaddev-front-mtb` avec la formule du §3 : **4,75:1**, AA pour du texte normal (l'étiquette est en `--t-xs`, donc « normal » au sens WCAG). Méthode recoupée sur une valeur connue : la même chaîne de calcul rend 5,30:1 pour `--laiton-texte` sur `--calcaire`, exactement le §12.1. **Amendement de §12.3 à faire ratifier par `lead-design-mtb`** ; ne bloque pas. |
| 5 | **Réutilisation par #16/#17** : fonctions globales `mtb_galerie_photos_*` (back) ou `render_block()` du cœur (front) | **`render_block()`** | Le contrat #1 §5 gèle la surface globale : quatre constantes plus les fonctions de lecture de `includes/query/`, **rien d'autre**. Une fonction de rendu globale en serait une dérogation — inutile, `render_block()` donnant le même résultat avec **zéro surface nouvelle et une seule implémentation**. La fonction de normalisation `_mtb_galerie` proposée n'a **aucun client** : la divergence tableau/chaîne est déjà absorbée par `mtb_get_portee()` et `mtb_get_chien()`, qui rendent tous deux la forme hydratée (décision 19). La fonction de rendu interne existe **sous espace de noms**, donc hors surface globale. |
| 6 | **`editorStyle` / `style`** : poignée (back) ou `file:` (front) | **Poignée** | `file:` serait sans danger pour une feuille — le piège de l'`asset.php` ne concerne que `editorScript`/`viewScript`, et `register_block_style_handle()` ne cherche jamais d'`asset.php`. Mais deux lignes de `wp_register_style()` gardent les noms conformes à la convention gelée du contrat #1 §6 et lisibles dans la file. Le constat de front est consigné en section 2 pour qu'on ne le re-litige pas. |
| 7 | **Valeur de `sizes`** : `(max-width: 30rem) 45vw, 18rem` (back) ou dérivation par bandes (front) | **La dérivation par bandes** | La borne supérieure exacte de la case est calculable par bande de n (section 5.2). L'approximation de back est plus grossière, sur l'attribut précisément le plus facile à rater. |
| 8 | **Cerne de photo** : `box-shadow` sur le lien (implicite chez back) ou `::after` (front) | **`::after`** | Défaut réel évité : `base.css:465-470` pose un `box-shadow` sur `:focus-visible`, qui **remplacerait** un cerne porté par le lien — la photo perdrait son cerne au clavier. Et un `inset` de parent est peint sous ses enfants, donc masqué par l'image. |
| 9 | **Libellés des réglages** : repris au mot de la fiche chien (back) ou « Choisir les photos » (front) | **Repris au mot** | `fields/chien/ecran.php` et `docs/guide/chien-ajouter-un-chien.md:158-161` lui ont **déjà enseigné** *Ajouter des photos / Retirer / Monter / Descendre* et le panneau « Photos de la galerie ». Réemployer ces mots ne coûte rien et lui épargne un concept. Un infinitif neuf aurait été plus élégant et moins utile. |
| 10 | **Crochets de l'état vide** : `mtb-bloc-vide` (back) ou `mtb-etat-vide` (front) | **`mtb-etat-vide`** | Vocabulaire de `MASTER.md` §9.1 lui-même (« L'état vide côté éditeur »). Le crochet local `mtb-galerie-photos__vide` est conservé **en plus**, idée de back, pour que la centralisation future ne touche pas le JavaScript. **À diffuser aux cinq chaînes sœurs** — sinon six copies divergentes (T13). |

---

## 11. Chaînes fournies par le serveur

Le thème les imprime, il ne les compose jamais.

**Rendu public** — une seule chaîne :

- **« Photo \<rang\> sur \<total\> »**. Rang et total **comptés sur les photos réellement rendues**,
  jamais sur les identifiants stockés. Candidat d'amendement de `MASTER.md` §10.3, qui ne gèle que la
  forme visible « 3 / 12 » de la visionneuse (section 6).

**Insérteur** : **« Galerie photos »** (§10.2) · **« Mont Brabant »** (catégorie, contrat #1 §10) ·
`Affiche une série de photos en vignettes. Chaque vignette ouvre la photo en grand.`

**Éditeur** :

| Chaîne | Origine |
|---|---|
| **GALERIE PHOTOS** | étiquette d'état vide |
| **Ce bloc n'affiche rien tant qu'aucune photo n'est choisie.** | gabarit §9.1 — **formule à diffuser aux cinq sœurs** |
| **Ce bloc n'affiche rien tant qu'aucune photo choisie n'est disponible.** | même gabarit, cas « les fichiers ont disparu » ; distincte pour qu'elle comprenne qu'elle a bien choisi |
| **Photos de la galerie** | titre du panneau **et** de la fenêtre des photos — mot pour mot la fiche chien |
| **Ajouter des photos** | bouton — mot pour mot la fiche chien |
| **Retirer la photo N** · **Monter la photo N** · **Descendre la photo N** | mot pour mot `fields/chien/ecran.php` et `docs/guide/chien-ajouter-un-chien.md:158-161` |

**Le texte alternatif d'une photo est celui de la médiathèque, tel quel, vide compris.** Le serveur n'en
fabrique aucun ; le thème n'en fabrique aucun.

**Aucune date, aucun libellé de disponibilité, aucun nom de discipline, aucun nom de chien** : ce
composant ne touche à aucune donnée du domaine, et n'en compose donc aucune chaîne.

**Libellé venant du cœur, pas de nous** : `MediaUpload` ne permet pas de renommer le bouton
d'insertion de la fenêtre, là où la fiche chien affiche « Ajouter à la galerie ». Ce libellé sera celui
du cœur. Il rejoint la liste du contrat #4 §6 bis, et la fiche d'aide le **citera au mot après l'avoir
relevé à l'écran** — jamais de mémoire (contrôle 5).

---

## 12. Écran d'édition — le chemin exact

Aucun écran de saisie neuf, aucune colonne de liste, aucun menu à simplifier.

**Pages › \<une page\> › Modifier › `+` › onglet Mont Brabant › Galerie photos › Ajouter des photos ›
(sélection dans la fenêtre des photos) › bouton d'insertion du cœur › Mettre à jour.**

Puis, pour l'ordre : **colonne de droite › Photos de la galerie › Monter la photo 2 / Descendre la
photo 2 / Retirer la photo 2.**

- **`MediaUploadCheck` + `MediaUpload`**, pas un appel direct à `wp.media()` : `allowedTypes: ['image']`,
  `multiple: true`, `title: 'Photos de la galerie'`, et **`value` non transmise** — la fenêtre s'ouvre
  vide, `onSelect` ne renvoie que les nouvelles photos, qu'on **ajoute** à la fin en sautant les
  doublons. C'est mot pour mot la sémantique de `fields/chien/galerie.js:170-190` : *« Ajouter des
  photos »* **ajoute**, il ne remplace pas.
- **Réordonnancement au clavier** : c'est la raison d'être des boutons *Monter / Descendre*. La modale
  du cœur en état `gallery-edit` ne propose que du glisser-déposer, **non opérable au clavier** — D7
  tomberait si c'était le seul chemin. Le premier *Monter* et le dernier *Descendre* sont **désactivés
  plutôt que retirés**, pour que le parcours au clavier reste régulier (motif déjà argumenté à
  `fields/chien/ecran.php:631-635`). **À vérifier en Docker plutôt qu'à supposer** ; si un chemin
  clavier natif existe, on le signale et on garde les boutons — ils sont aussi la cohérence avec le
  guide déjà écrit.
- **Restauration du focus**, comme `fields/chien/galerie.js:111-147` : après un déplacement, le focus va
  au bouton homologue de la photo déplacée ; s'il vient d'être désactivé, repli sur *Retirer* ; si la
  photo a disparu, repli sur *Ajouter des photos*. Sans cela, le focus retombe dans le vide.
- **Coût assumé** : à douze photos, le panneau est long dans 280 px. C'est le prix d'un ordre opérable
  au clavier, et l'aperçu de la toile lui montre le résultat pendant qu'elle range.
- **Aperçu dans la toile** : `wp.serverSideRender` (les deux plans convergent). Une grille redessinée en
  JavaScript donnerait un aperçu instantané mais **deux implémentations d'un seul rendu** — filtre de
  validité, choix de taille, `srcset`, `sizes`, ordre du DOM, `<span>` de rang, `role="list"` — en PHP
  **et** en JavaScript : la forme exacte de T9, sur un rendu dont dépend D7. `serverSideRender` coûte un
  aller-retour REST par modification, sans champ de texte qui déclencherait un appel par frappe, et
  dépend du paquet `wp-server-side-render` **du cœur** : aucun tiers. Ce qu'on achète : **l'aperçu de
  l'éditrice est la page, par construction.**

---

## 13. Sécurité & rôles

**Cette issue n'ouvre aucun chemin d'écriture.** À dire explicitement, parce qu'un relecteur cherchera
un nonce :

- **Aucun `$_POST`, aucun `$_GET`, aucun `save_post`, aucun `update_post_meta`, aucune option.**
  L'attribut `photos` est écrit par l'enregistrement natif de la page, avec le nonce et le contrôle
  `edit_post` du cœur, sur une entité (les Pages) qui appartient à l'issue #18.
- **Deux points d'entrée serveur, tous deux en lecture** : le rendu du bloc, sur des attributs validés
  par le schéma de `block.json` ; et `/wp/v2/block-renderer/mtb/galerie-photos`, qui **exige
  `edit_posts`** — le rôle Éditeur l'a, un visiteur anonyme non.
- **Aucune capacité ajoutée, aucun rôle créé, aucun `add_cap()`.** Fabienne reste sur le rôle **Éditeur**
  natif.
- **Ce qu'elle ne peut pas casser** : ni le design (aucun réglage de couleur / police / espacement /
  alignement / classe atteignable, `supports.html: false`), ni une donnée d'élevage (le bloc n'en écrit
  aucune), ni une photo de la médiathèque (le rendu n'écrit jamais ; **un identifiant mort reste en
  base et il ne faut pas le corriger** — si elle réimporte la photo, la galerie se rétablit seule,
  contrat #4 §11).
- **Zéro requête sortante, zéro domaine tiers**, au rendu comme dans l'éditeur : pas de `$schema`, pas
  d'`example`, aucune police d'icônes, aucun sprite SVG distant, **aucune image décorative**.

---

## 14. Interdits

- Le thème n'interroge jamais la base directement, et **n'appelle jamais** `render_block()` sur ce bloc
  ni la fonction de rendu sous espace de noms. Un `grep` de `MTB\` ou de `mtb_galerie_photos` dans
  `themes/mtb/` est une infraction.
- Le thème ne compose, ne remplace, ne complète et ne reformate **jamais** un texte alternatif.
- Le thème ne réécrit pas « Photo 3 sur 12 » et ne le remplace pas par « 3 / 12 » sur une vignette.
- Le thème ne décide pas qu'une galerie est vide, n'écarte aucune photo, ne change pas l'ordre du DOM.
- Aucun titre, aucune légende, aucun compteur visible, **aucun texte superposé** à une vignette (§6.4).
- **Aucun `filter:` sur une photographie de chien** (§6.6, §13 — la robe est une donnée d'élevage).
- Aucun filet double sur une vignette (§2.1, liste des *jamais*), aucune ombre portée, aucun dégradé,
  aucun rayon > 2 px, aucun `border-radius: 50%` sur une image.
- Aucune couleur hors des quinze jetons, aucun fond hors des cinq, **aucune paire absente du §12** sans
  ratification écrite (arbitrage 4).
- Aucune valeur brute hors de la liste close de la section 7. **Aucun repli dans un `var()` qui durcirait
  un jeton** (`var(--e-3, .75rem)` est interdit, §13). **Exception unique, et ce n'en est pas vraiment
  une** : `var(--point-interet, 50% 38%)`, que §6.2 écrit lui-même verbatim. `--point-interet` **n'est
  pas un jeton** — il n'a aucune source de donnée (Q-8-A) — et sans son repli la déclaration serait
  invalide à la valeur calculée, ramenant le cadrage au `50% 50%` du navigateur et **perdant le défaut
  que MASTER impose**. Précisé après que `dev-ux-mtb` a relevé la contradiction entre les sections 7.5
  et 14 de la première rédaction.
- Aucune taille en `vw` seul, aucune largeur fixe > 300 px, aucun `overflow: hidden` sur un conteneur
  de texte.
- Aucune fonction accessible au survol seul, aucun carrousel, aucune animation d'apparition.
- Aucun `example` ni `$schema` dans `block.json`. Aucune étape de construction JavaScript : pas de
  `npm`, pas de JSX, pas de `build/`.
- Aucune modification de `mtb-core.php`, `class-loader.php`, `theme.json`, `functions.php`, `base.css`,
  `editor.css`, ni d'un autre dossier de `includes/blocks/`.

---

## 15. Dettes créées ou révélées

| # | Dette | Créée / révélée par | À payer par |
|---|---|---|---|
| **T11** | **Aucune sous-taille adaptée à une vignette de 144-222 px.** Le trou 300 → 768 du cœur fait télécharger `medium_large` à DPR 2 : ≈ ×6 en pixels, ~1,2 Mo au lieu de ~250 Ko pour 12 photos sur téléphone. Correctif : `add_image_size( 'mtb-vignette-galerie', 400, 400, false )` dans `functions.php`, hors empreinte | #8 | epic Gabarits ou issue `infra`, **avant #19-#21** |
| **T12** | **Formats modernes non servis.** Il manque `image_editor_output_format` **et** la vérification du support WebP de GD/Imagick. **D8 non conforme sur ce point.** Même contrainte de calendrier que T11 | #8 (constat) | issue `perf`/`infra`, **avant #19-#21** |
| **T13** | **L'apparence d'état vide du §9.1 n'a aucun domicile dans ce lot.** MASTER la place dans `editor.css` du thème et la déclare « identique pour les dix composants » ; or `editor.css` et `add_editor_style` sont hors empreinte, et **six chaînes de composants tournaient en parallèle** → six copies divergentes, motif de T9. Parade : que les six emploient les mêmes crochets `.mtb-etat-vide` / `__nom` / `__phrase` | #8 + #6, #7, #12, #13, #14 | une issue `theme`/`a11y` qui hissera la règle dans `editor.css` |
| **T14** | **`includes/blocks/categorie-mtb/` n'existe pas** et est hors de l'empreinte des six chaînes. Contrat #1 §10 le confie à « la première issue de composants » — il y en avait **six simultanées**. Parade livrée : garde idempotente sur `block_categories_all`, inerte le jour où le module arrive | contrat #1 vs le lot | `/lead-mtb`, puis effondrement des six gardes |
| **T15** | **La feuille du composant vit dans l'extension** (variante P, arbitrage 1), en **dérogation au contrat #1 §8**, et elle **nomme les lignes de grille du thème** (`large-debut`/`large-fin`) pour tenir §7.4 pt 5. Elle ne peut pas être durcie par des replis `var(--x, brut)`, que §13 interdit : thème échangé → grille survivante, **discipline photographique détruite** (section 3.2). Déplacement futur = copie + suppression du `"style"`, sinon **double chargement** | #8 | epic Gabarits, quand `assets/css/blocs/` aura un propriétaire |
| **T7** | **Non touchée.** La galerie ne contient **aucun bouton côté public**. Le cadre d'état vide **en porte un** (« Ajouter des photos », section 4.3) : c'est du **mobilier d'administration**, habillé par `base.css` §8.4, hors du site public sur lequel porte T7. #8 ne paie donc pas T7 et n'y ajoute rien. *(La première rédaction écrivait « ni dans l'état vide » et contredisait la section 4.3 ; corrigé après que les deux dev l'ont relevé indépendamment.)* | #2 | inchangé |
| **T10** | **Partiellement entamée.** Ce sera le **premier balisage `mtb-*` visible par un visiteur** dès qu'une page portera le bloc : le repli à 360 px, le zoom 200 %, l'anneau de focus sur photographie et le cerne du §6.6 deviennent vérifiables **en HTML réel** pour la première fois. **Sans prétendre solder T10**, qui porte sur le rendu des trois types | lot 2 | #16-#18 pour le reste |

---

## 16. Questions ouvertes — aucune ne bloque, aucune n'est comblée

Aucun fait d'élevage n'est requis par cette issue : le composant ne stocke et ne compose aucune donnée
du domaine. Il n'y a donc **aucune question bloquante pour l'éleveuse**.

| # | Question | Pour qui | Bloque |
|---|---|---|---|
| **Q-8-A** | **Le point d'intérêt d'une vignette de galerie n'a aucune source de donnée.** §6.2 en fait « le seul réglage photographique laissé à l'éditrice » et §6.3 pt 2 en fait **la seule atténuation** du rognage d'une photo portrait dans un `3/2`. Or `_mtb_cadrage` est un champ **unique par chien**, pour la **photo principale** (`content/chien/champs.php:61`), et une portée n'en a **aucun**. Conséquence : le repli `50% 38%` s'applique à **toutes** les vignettes, et **la promesse du §6.3 pt 2 est vide aujourd'hui**. Faut-il un cadrage **par photo** (ce qui contredirait « le bloc ne stocke que des identifiants » — il faudrait alors le porter par la médiathèque), ou §6.3 pt 2 doit-il être amendé ? | `lead-design-mtb` | **rien** — le CSS pose la règle du §6.2 avec son repli. Bloque l'exactitude de §6.3 et de la fiche d'aide |
| **Q-8-B** | **MASTER n'a aucun état de survol pour un lien-photographie** — ni §8, ni §12.9. Livré **sans survol** : légal, AA tenu, rien n'est au survol seul. Quel traitement si un est voulu ? Il devra être en jetons, sans anneau (§8.1), sans ombre (§5.4), sans `filter` (§6.6), sans transition (aucun jeton de durée n'existe) | `lead-design-mtb` | **rien** — une ligne à ajouter le jour de la réponse |
| **Q-8-C** | **§12.3 doit tabuler `--laiton-texte` sur `--calcaire-creux`**, paire que §9.1 mandate. Ratio calculé **4,75:1**, AA texte normal, méthode recoupée sur le §12.1. Ratification en une ligne, ou encre de remplacement ? | `lead-design-mtb` | **rien** — écrit sous l'arbitrage 4, à ratifier |
| **Q-8-D** | **Deux valeurs manquent pour le texte `alt` affiché dans un cadre en échec** (§6.6) : son **interligne** et son **retrait intérieur**. MASTER donne l'encre et la taille, rien d'autre. Livré sans retrait, interligne hérité — le texte est donc collé au cerne. Lacune nommée, pas comblée | `lead-design-mtb` | **rien** — défaut acceptable |
| **Q-8-E** | **« Photo 3 sur 12 » comme nom accessible** — amendement de §10.3, qui ne gèle que la forme visible « 3 / 12 » | `lead-design-mtb` | **rien** |
| **Q-8-F** | **Décision média du site entier, avant la reprise des photos** : conversion WebP (T12) **et** échelle des sous-tailles (T11). Les deux n'agissent que sur les fichiers téléversés **ensuite** ; prises après l'import des ~500 photos, elles exigeraient une régénération complète | `/lead-mtb` | **rien pour #8** — mais #8 ne peut pas cocher « formats modernes » de D8, et la fenêtre pour bien faire se ferme à l'epic de reprise |
| **Q-8-G** | **Convention de nommage des dix fiches de composants.** Trois fiches existent, toutes nommées par la **tâche** (`chien-ajouter-un-chien.md`). La mienne était `galerie-ajouter-des-photos.md`, parce que BRIEF §13 fait de « ajouter des photos » une page **requise** du guide. Les cinq sœurs livrent des composants sans tâche nommée par le brief : `composant-<nom>.md` leur conviendrait mieux. À figer une fois, sinon #25 héritera de six conventions — le défaut que la décision 22 vient de payer | `/lead-mtb` | **rien** — **tranchée, voir §20.6** |

---

## 17. À vérifier dans Docker, jamais à affirmer

**Aucune de ces lignes n'est cochée par ce contrat.** Elles ne sont vraies que si un agent les a
exécutées, et le rapport de chaîne dit lesquelles l'ont été.

| # | Contrôle |
|---|---|
| 1 | Le bloc apparaît dans l'insérteur, onglet **« Mont Brabant »**, garde de catégorie active |
| 2 | **Ce qu'il advient sans catégorie enregistrée** (garde retirée une minute) : onglet absent, trouvable seulement par la recherche ? Si oui, la garde est **bloquante pour Fabienne** |
| 3 | `editorScript` par **poignée** : script chargé dans l'éditeur en iframe, **aucun `_doing_it_wrong`** |
| 4 | Forme du global `wp.serverSideRender` (composant direct ou `.default`) sur la version installée — prévoir la lecture tolérante des deux |
| 5 | `MediaUpload` ouvre la fenêtre du cœur dans l'iframe ; **relever le libellé français exact du bouton d'insertion**, pour la fiche d'aide |
| 6 | Appel REST `block-renderer` réussi **avec le compte `fabienne`** (rôle Éditeur) ; le tableau d'entiers traverse la chaîne de requête et la validation du schéma |
| 7 | `loading="lazy"` sur **toutes** les vignettes, **y compris la première** — la valeur explicite bat `wp_get_loading_optimization_attributes()` |
| 8 | `width`, `height`, `srcset`, `decoding="async"` présents ; `sizes` est bien le nôtre ; **aucun candidat < 300 w**, `thumbnail` bien exclue |
| 9 | Quel candidat le navigateur **choisit** à DPR 1 puis DPR 2 |
| 10 | **Nom accessible du lien**, à l'inspecteur d'accessibilité, **photo décrite et photo non décrite** : jamais vide, et la description **est lue** quand elle existe |
| 11 | Page dont **toutes** les photos ont été supprimées : **rien** dans la source (pas d'`<ul>` vide, pas d'enrobage), et le second cadre §9.1 dans l'éditeur |
| 12 | Réordonnancement **au clavier** de bout en bout ; focus jamais perdu après un déplacement |
| 13 | **360 px** : deux colonnes, aucun défilement horizontal, vignette ≥ 44 px — **via iframe**, le headless mentant sous ~500 px |
| 14 | **Zoom 200 %** à 1280 px : trois colonnes, aucun défilement |
| 15 | Clavier : anneau du §8.1 **visible sur la photographie** ; `Entrée` ouvre le fichier |
| 16 | **JavaScript désactivé** : la vignette reste un lien vers le fichier (§6.7) |
| 17 | Image en échec : cadre conservé, `--calcaire-creux`, cerne, texte `alt` en `--texte-doux`, **aucun pictogramme cassé** |
| 18 | PNG détouré : `--calcaire-creux` derrière, **aucun damier** |
| 19 | La feuille de l'extension atteint **et** la page publique **et** la toile ; `editorStyle` résout `var(--laiton)` dans l'iframe |
| 20 | Placement en canal large effectif — le `<div>` racine est-il enfant direct de `.mtb-canal` dans une Page ordinaire ? |
| 21 | `supports.html: false` → « Modifier en HTML » absent ; panneau Avancé **sans** champ de classe CSS |
| 22 | Poids : octets de la feuille (`wc -c`), total de page, présence de l'inline |
| 23 | **D6** : zéro origine tierce sur une page portant la galerie, et à l'ouverture de l'éditeur (les 15 images de `s.w.org` de T4 restent celles du cœur ; **on n'en ajoute pas**) |
| 24 | Frontière : `grep -rE "WP_Query|get_post_meta|get_posts|get_terms|MTB\\\\|mtb_galerie_photos"` sur `themes/mtb/` → **0** |
| 25 | `WP_DEBUG` : aucune notice ni avertissement à l'insertion, à l'enregistrement, au rechargement, et sur une galerie dont les photos ont disparu |

---

## 18. Ordre d'implémentation

1. `bootstrap.php` — gardes, `require_once rendu.php`, `add_action( 'init', …, 20 )`, garde de
   catégorie, enregistrement des trois poignées.
2. `block.json` — **sans `style` ni `editorStyle` d'abord**, et **vérifier en Docker que le bloc
   apparaît dans l'insérteur sous « Mont Brabant »** avant d'écrire une ligne de rendu. C'est le point
   le plus susceptible de contredire le plan (contrat #1 §10 pt 2).
3. `rendu.php` — filtre de validité, balisage de la section 4, échappement complet.
4. `render.php` — quinze lignes, sortie anticipée sur `''`.
5. **Vérification front avant tout travail d'éditeur** : insérer le bloc à la main
   (`<!-- wp:mtb/galerie-photos {"photos":[…]} /-->`), contrôler les points 7-11 et 16-18.
6. `galerie.css` — sections 7.1 à 7.7.
7. `editeur.js` — dans l'ordre du risque : `registerBlockType` + état vide → `MediaUpload` →
   `serverSideRender` → panneau d'inspecteur (retirer / monter / descendre + restauration du focus).
8. `editeur.css` — état vide §9.1 (section 7.8) **et** la règle de masquage de `__rang`.
9. Passe clavier, 360 px, zoom 200 %, `WP_DEBUG` propre.
10. `docs/guide/composant-galerie-photos.md`, écrit **après** avoir relevé à l'écran le libellé exact
    du bouton d'insertion du cœur — jamais de mémoire.
11. Ligne d'inventaire au contrat #1 §11 : `blocks/ | galerie-photos | #8`.

---

## 19. Amendements après implémentation — re-gelés le 2026-08-17

Ce que le code réel a contredit. Chacun a été **relevé par un agent, pas deviné**, et corrigé ici plutôt
que contourné en silence.

### 19.1 Les sélecteurs de la section 7 montent à deux classes — défaut mesuré

La section 7 écrivait des sélecteurs d'**une seule classe**. C'est un défaut, et `dev-ux-mtb` l'a mesuré :
les feuilles passées à `add_editor_style()` sont **préfixées par `.editor-styles-wrapper`** dans la toile
(`editor.css:25-28`), donc `img` y pèse **(0,1,1)** et `a` **(0,1,1)** ; une feuille de bloc, elle, n'est
pas préfixée. `block-size: auto` de `base.css:131-136` l'emportait : **image de 220 px débordant d'un
cadre 3/2, plus le soulignement en travers de la photo.**

**Re-gelé** : chaque règle porte **deux classes du balisage gelé**, soit (0,2,0). Zéro écart visuel par
rapport à l'intention de la section 7 ; seuls les sélecteurs changent.

**Portée de cet amendement, bornée après remarque de `refacto-mtb`** : il vise **les règles de la
section 7 uniquement** — celles qui habillent le rendu public, où le défaut a été mesuré sur `img` et `a`
de `base.css`. Les règles d'état vide de la section 7.8 (`.mtb-etat-vide`, `.mtb-etat-vide__nom`)
**restent volontairement à une seule classe** : les préfixer du crochet local détruirait la mutualisation
que la section 4.3 vise, et le défaut de cascade ne les concerne pas (ce sont un `div` et un `p`
d'administration, qu'aucune règle d'élément de `base.css` ne dispute). **Ce n'est pas un oubli à
« corriger ».**

**Conséquence associée** : le `border-radius: var(--r-0)` que la section 7.3 prescrivait sur
`__lien` est **retiré**. À (0,2,0) il battait le `border-radius: var(--r-1)` que §8.1 pose sur l'anneau
de focus et **aplatissait les coins de l'anneau**. `--r-0` valant `0`, son retrait ne change rien à la
photo, qui garde son `--r-0` par sa propre règle.

### 19.2 Contrôle 20 — **ÉCHOUE, et la cause est structurelle**

`MASTER.md` §7.4 pt 5 place la galerie dans le **canal large**. La règle
`.mtb-canal > .mtb-galerie-photos` exige que la racine soit **enfant direct** de `.mtb-canal`. Elle ne
l'est jamais : `core/post-content` émet son propre `<div class="entry-content wp-block-post-content">`
(vérifié dans le cœur installé, `post-content.php:63`).

Mesuré par `dev-ux-mtb` : imbriquée = **576 px / 3 colonnes** ; directe = **992 px / 6 colonnes**.

> **§7.4 pt 5 n'est donc PAS tenu sur le front aujourd'hui, et aucun CSS ne peut le corriger** — le
> parent n'est pas une grille.

La règle est **conservée** : elle est juste, et elle prendra effet le jour où un gabarit composera la
galerie directement. Le correctif est dans `templates/singular.html`, **hors empreinte** : dette
**T16**, à payer par l'epic Gabarits (#16-#18).

Cela affaiblit une des deux justifications de l'arbitrage 1 (« la variante P conserve le placement en
canal large »). L'autre — la variante T laisse la **toile entièrement non habillée** — tient seule et
reste décisive. La décision n'est pas rouverte, mais la raison est honnêtement réduite à une.

### 19.3 Le cœur préfixe `sizes` de `auto, `

WordPress 6.9 (`wp_img_tag_add_auto_sizes`, arrivé en 6.7) préfixe `auto, ` dès que `loading="lazy"`.
Sortie réelle : `sizes="auto, (min-width: 32rem) 196px, (min-width: 21rem) 222px, 90vw"`.

Notre valeur est **intacte, jamais réécrite**. Non combattu : le seul levier est le filtre global
`wp_img_tag_add_auto_sizes`, qui agirait sur **toutes** les images du site et sort de l'empreinte.
Dégradation correcte dans les deux sens : les navigateurs qui gèrent `sizes=auto` emploient la largeur
réellement mise en page — **plus juste que nos bandes** —, les autres ignorent `auto` et retombent sur
notre liste. **Le contrôle 8 se lit désormais « le nôtre, précédé de `auto,` ».**

### 19.4 Le second cadre d'état vide est livré sans bouton

La section 11 demandait « même cadre §9.1, seconde phrase ». §9.1 ne contient que l'étiquette et la
phrase ; le bouton est un ajout de la section 4.3 au **premier** cas. Renfort technique relevé par
`dev-back-mtb` : un composant défini en ligne pour `EmptyResponsePlaceholder` change d'identité à chaque
rendu et fait démonter/remonter le cadre ; sans bouton, il est hissé au niveau du module et devient
stable. Le recours reste le panneau de droite, qui porte toujours « Ajouter des photos ». **Accepté.**

### 19.5 Balisage du panneau d'inspecteur — non spécifié, donc à consigner

Le contrat ne le spécifiait pas. Livré en `<ul>` / `<li>` + `wp.components.Button` (`variant: 'link'`
pour les trois boutons par photo, écho du `button-link` de la fiche chien ; `variant: 'secondary'` pour
« Ajouter des photos », écho de son `class="button"`). **Aucun crochet de classe nouveau** : le ciblage
du focus passe par `data-mtb-rang` sur la ligne et `data-mtb-bouton="retirer|monter|descendre"` sur les
boutons, **pour que la liste close de la section 4.2 reste close.** Re-gelé tel quel.

### 19.6 `inset(50%)` rejoint la liste close des littéraux

La section 7.6 exigeait « une technique de masquage » sans en nommer aucune. `MASTER.md` §7.6 écrit
lui-même `clip-path: inset(50%)` pour retirer un `thead` à l'œil en le gardant au lecteur d'écran :
c'est le dispositif de la maison, il est repris. `600`, `1.4` et `.16em` de la section 7.8 viennent de la
ligne `h4` du tableau §4.5, même classe de valeur que la liste close de `base.css:19-20`.

### 19.7 Le lot est incohérent sur l'arbitrage 1, et ce n'est pas #8 qui décide

Constat de `dev-ux-mtb` en fin de course : `themes/mtb/assets/css/blocs/` contient désormais **cinq**
feuilles, livrées par les cinq chaînes sœurs — **toutes en variante T**. #8 est le seul en variante P.

Conséquence à porter au niveau du lot, pas ici : si le mécanisme de 3.1 est exact — et il a été établi
mécanisme par mécanisme — **les cinq composants sœurs ont une toile d'éditeur entièrement non
habillée.** À vérifier et à arbitrer par `/lead-mtb` ; #8 ne change pas de variante sur la seule
cohérence de dossier, la toile pesant plus lourd qu'un rangement.

### 19.10 T14 est payée pendant le lot — la garde de #8 est inerte, comme prévu

`refacto-mtb` a constaté que **`includes/blocks/categorie-mtb/bootstrap.php` existe désormais** : une
chaîne sœur l'a livré. La section 2.3, qui le déclarait absent, est **obsolète sur ce point**.

Le comportement observé est exactement celui que la section 2.3 avait prévu, et il faut le consigner
parce qu'il valide la parade : `categorie-mtb` fait un `array_unshift` (catégorie **en tête**) sous sa
propre garde d'idempotence ; le chargeur parcourant par ordre alphabétique, il accroche son filtre
**avant** `galerie-photos`, donc s'exécute d'abord ; la garde de #8 **constate la présence et rend la
main**. Aucun doublon, aucune course, « Mont Brabant » en tête de l'insérteur.

**La garde de #8 reste en place** : la section 2.3 est gelée et l'ordre de chargement d'un module frère
n'est pas une dépendance dont #8 a le droit de dépendre (contrat #1 §2 : « un module ne doit jamais
dépendre de cet ordre »). Six modules portent désormais une garde sans emploi — **leur effondrement est
une décision de lot**, pas d'issue. **Dette T14 : payée quant au module, ouverte quant aux six gardes.**

### 19.11 T13 a échoué à l'échelle du lot — quatre conventions, avec une conséquence mesurable

Le crochet partagé de la section 4.3 n'a pas été adopté. `refacto-mtb` a relevé par `grep`, dans l'arbre
réel, **quatre** conventions pour le même état vide :

| Convention | Modules |
|---|---|
| `mtb-etat-vide` + `__nom` + `__phrase` | **#8**, `derniere-portee` |
| `mtb-etat-vide` + **`__composant`** + `__phrase` | `grille-chiens` (rendu **côté serveur**, `balisage.php:267-272`) |
| `mtb-bloc-vide` | `bandeau-ouverture`, `liste-portees` |
| crochet purement local | `fiche-information` |

Conséquence mécanique, si le mécanisme de la section 3.1 est exact : les cinq sœurs étant en variante T,
**l'`editorStyle` de #8 est aujourd'hui la seule feuille qui habille `.mtb-etat-vide` dans la toile** — il
habille donc aussi le cadre vide de `grille-chiens` (le conteneur oui, l'étiquette non, celle-ci
s'appelant `__composant`). **Ce n'est pas un défaut de #8**, qui suit ses sections 4.3 et 10 arbitrage 10
à la lettre ; c'est un arbitrage `/lead-mtb`, à trancher **avec** la section 19.7 plutôt que dossier par
dossier. ~~Noté aussi : `derniere-portee` écrit son étiquette en casse mixte (« Encart dernière portée ») là
où #8 et MASTER §9.1 l'écrivent en capitales — sans effet visuel (`text-transform: uppercase`), mais
**divergent à l'oreille d'un lecteur d'écran**.~~ **La divergence était réelle, mais le tort était du
côté de #8 : voir §19.13.**

### 19.13 Correction — l'étiquette d'état vide s'écrit en casse naturelle

`editeur.js` tapait `'GALERIE PHOTOS'` en capitales littérales. `editor.css:149-157` énonce pourtant la
règle du lot **mot pour mot**, et c'est `derniere-portee` — noté ci-dessus comme divergent — qui la
suivait :

> « **LES CAPITALES SONT POSÉES ICI, JAMAIS TAPÉES DANS LE JAVASCRIPT**, et cela vaut pour les cinq
> composants sœurs […] le texte reste lisible par un lecteur d'écran, qui épellerait lettre à lettre des
> capitales littérales. »

`editeur.js` déclare désormais `var NOM_AFFICHE = 'Galerie photos'` — **le titre de `block.json`
recopié tel quel**, donc celui que l'éleveuse lit dans l'inséreur — et `text-transform: uppercase` pose
les capitales.

**Pourquoi le défaut a survécu à toute la recette** : l'apparence est *identique*, la feuille
transformant déjà. Seul le DOM les distingue. Vérifié dans la toile de `/essai-8-galerie-vide/` :
`textContent` de `.mtb-etat-vide__nom` vaut `Galerie photos` (`G`, `a`, `l` — codes 71, 97, 108),
`getComputedStyle().textTransform` vaut `uppercase`, et le cadre affiche « GALERIE PHOTOS » comme
avant. **Un contrôle à l'œil ne pouvait pas trouver ce défaut ; il faut relever le `textContent`.**

Reste hors de cette empreinte, et remonté au lead : `fiche-information/editeur.js:108` tape
`'FICHE D\'INFORMATION'`, **même défaut, autre chaîne**.

### 19.12 Crochets émis sans règle — conforme, et à ne pas combler

`mtb-etat-vide__phrase` est émis sans aucune règle dans les deux feuilles, ce qui est **conforme** : la
section 7.8 ne spécifie que le conteneur et l'étiquette, et `MASTER.md` §9.1 ne donne à la phrase ni
interligne ni métrique propre. **Rien n'a été inventé.** Même statut pour
`mtb-galerie-photos__element` (case de grille, rien à porter) et `mtb-galerie-photos__vide` (crochet
local volontairement nu). À traiter avec la centralisation dans `editor.css` (T13).

### 19.13 Boutons de borne `disabled` — décision a11y assumée, pas un correctif

La section 12 justifie « désactivés plutôt que retirés » par la régularité du parcours clavier.
`refacto-mtb` relève justement qu'un bouton portant `disabled` **sort du parcours de tabulation** : la
régularité tient donc sur le **nombre de boutons rendus**, pas sur le nombre d'arrêts de tabulation.

**Conservé tel quel.** Le comportement est **identique au précédent invoqué**
(`fields/chien/ecran.php:641-648`, `disabled="disabled"`), donc cohérent avec le guide déjà écrit ;
`accessibleWhenDisabled` changerait le comportement et **divergerait de la fiche chien**. La formulation
de la section 12 est corrigée ici plutôt que le code.

### 19.8 Dettes ajoutées

| # | Dette | Créée / révélée par | À payer par |
|---|---|---|---|
| **T16** | **§7.4 pt 5 n'est pas tenu** : `core/post-content` interpose son propre `<div>`, donc la galerie n'est jamais enfant direct de `.mtb-canal` et rend dans le canal texte (3 colonnes au lieu de 6-7). Mesuré : 576 px contre 992 px. Aucun CSS ne peut le corriger ; le correctif est dans `templates/singular.html` | #8 (constat) | epic Gabarits (#16-#18) |

### 19.9 Question ouverte ajoutée

| # | Question | Pour qui | Bloque |
|---|---|---|---|
| **Q-8-H** | Sur une image qui ne charge pas, **Chrome dessine son propre glyphe d'image cassée** devant le texte `alt`, à l'intérieur du cadre. **`MASTER.md` §6.6 exige « Pas de pictogramme cassé ».** Aucun dispositif de MASTER ne le retire, et tout moyen connu de le faire serait un contournement inventé. **Constaté, non comblé.** | `lead-design-mtb` | **rien** — §6.6 est tenu sur tout ce qui dépend de nous (cadre conservé, ratio, `--calcaire-creux`, cerne, texte `alt` en `--texte-doux`) ; le glyphe est ajouté par le navigateur |

---

## 20. Passe de finition — re-gelée le 2026-08-17, après le lot

Cette section corrige les endroits où le contrat gelait du code qui n'existe plus, et consigne ce que
la finition a **mesuré** dans la pile plutôt que déduit. Toute divergence entre les sections 1-19 et
celle-ci se tranche en faveur de celle-ci.

### 20.1 T11 et T12 sont payées, et pas là où le contrat les envoyait

§5.5 et §15 situaient le correctif dans `themes/mtb/functions.php`, « hors empreinte ». Il est livré
dans **`mtb-core/includes/admin/medias/bootstrap.php`**, et c'est le bon domicile : le traitement des
photos doit survivre à un changement de thème, c'est de la logique métier et non de la présentation.
Contrat #1 §8 n'est donc pas contourné ici.

Le module déclare `add_image_size( 'mtb-vignette-galerie', 400, 400, false )` — **non rognée**, pour que
le rapport de la photo soit conservé, que le cadrage reste au CSS, et que le cœur accepte le fichier
comme candidat de `srcset` — et le filtre `image_editor_output_format`.

**Support WebP vérifié avant d'activer la conversion**, comme exigé, et non supposé :

| Mesure | Résultat |
|---|---|
| `gd_info()['WebP Support']` | oui |
| `Imagick::queryFormats('WEBP')` | `WEBP` |
| `wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) )` | `true` |
| `_wp_image_editor_choose(…)` | `WP_Image_Editor_GD` |

La conversion est donc activée. Le contrôle reste **dans le rappel du filtre** et non au moment de
l'accrocher : sur un hébergement sans WebP, la correspondance n'est pas ajoutée, un JPEG reste un JPEG,
rien n'échoue.

**Effet mesuré de bout en bout** sur un téléversement JPEG réel (pièce jointe #118, 1600×1067) :

- image principale **et** toutes les sous-tailles produites en WebP ;
- le fichier envoyé est conservé intact, désigné par `original_image` — rien n'est détruit ;
- conséquence à connaître : le lien « voir la photo en grand » sert désormais le **WebP**, pas le JPEG ;
- `mtb-vignette-galerie` produit `400×267`, et le `srcset` du bloc gagne le candidat **400 w** ;
- le PNG est laissé en PNG, délibérément : le WebP du cœur est avec perte, et un pedigree ou un numéro
  LOF numérisé ne se joue pas à quelques kilo-octets.

**Chiffres honnêtes.** Le rapport de pixels entre `medium_large` (768×512 = 393 216 px) et
`mtb-vignette-galerie` (400×267 = 106 800 px) est **3,68×**, et c'est un fait géométrique exact. Les
octets mesurés sur #118 donnent 2 194 o contre 804 o, soit 2,73× — mais l'image d'essai est un dégradé
synthétique, donc ces octets **ne représentent pas une photographie**. Les ~1,2 Mo contre ~250 Ko de
T11 restent une **projection, jamais une mesure**, et ne doivent pas être cités comme telle.

**Limite mesurée, pas seulement écrite** : la pièce jointe #13, téléversée avant le module, n'offre
**aucun candidat 400 w** — son `srcset` saute de 300 w à 768 w. Les deux réglages n'agissent que sur les
fichiers téléversés **ensuite**. La fenêtre pour bien faire se ferme donc à #19-#21, comme Q-8-F le
disait, et c'est maintenant constaté sur l'arbre et non déduit.

**Pas de garde `is_admin()`, et la raison a été corrigée.** La première rédaction du module invoquait
`wp_calculate_image_srcset()`, ce qui est faux : cette fonction lit les **métadonnées de la pièce
jointe** (`wp-includes/media.php:1361-1365` du cœur installé), pas la liste des tailles enregistrées.
La vraie raison est plus forte : la liste n'est lue qu'**au moment où les métadonnées sont produites**,
et cet instant n'est pas toujours dans `wp-admin` — sous WP-CLI comme sur la route REST des photos,
`is_admin()` vaut **faux** (mesuré : `bool(false)`). La pièce jointe #118 a justement été créée par
`wp media import`, et a bien reçu son fichier de 400 px ; derrière une garde, ce fichier **n'aurait
jamais été écrit sur le disque**. La reprise des photos de l'ancien site étant un import WP-CLI, la
garde doit rester absente.

**D8 « formats modernes servis » devient cochable** pour toute photo téléversée à partir de maintenant,
et pour aucune photo antérieure.

### 20.2 La garde de catégorie est retirée — §2.3 et §19.10 sont périmées

`includes/blocks/categorie-mtb/` existe et fonctionne. Contrat #1 §10 veut **une seule** déclaration ;
§19.10 conservait la garde de #8 au nom de l'indépendance à l'ordre de chargement, ce qui en faisait une
**troisième implémentation du même filtre** — la forme même de la dette T9. Le filtre
`block_categories_all` et la fonction `garantir_la_categorie()` sont **supprimés** de
`blocks/galerie-photos/bootstrap.php`. Le raccrochement passe par le seul `"category": "mtb"` de
`block.json`.

**Dette T14 : soldée pour #8.** Mesuré côté serveur : le créneau `mtb` est enregistré sous le titre
**« Mont Brabant »** et se trouve à l'**indice 0**, en tête de l'insérteur, devant « Texte », « Média »,
« Design », « Widgets », « Thème », « Contenus embarqués » et « Compositions ».

### 20.3 L'apparence d'état vide quitte `editeur.css` — §7.8 et §18 pt 8 sont périmées

`MASTER.md` §9.1 loge cette apparence dans `editor.css` du thème et la déclare identique pour les dix
composants. Les règles `.mtb-etat-vide` et `.mtb-etat-vide__nom` sont donc **retirées** de
`editeur.css` ; il n'y reste que la règle de masquage de `__rang`, qui est porteuse d'accessibilité et
reste volontairement copiée dans les deux feuilles (§6, §7.6).

**T13 est payée, et vérifiée sur l'arbre plutôt que crue** : `themes/mtb/assets/css/editor.css` porte
désormais `.mtb-etat-vide`, `.mtb-etat-vide__nom` **et** `.mtb-etat-vide__phrase` (chaîne #6). §19.12
disait que la phrase n'avait d'apparence nulle part ; elle en a une maintenant, et c'est un
enrichissement de #6, pas une invention de #8.

Le mécanisme qui empêchait toute feuille du thème d'atteindre la toile est **réparé par #6** et je l'ai
lu dans le code livré : `mtb-jetons` n'était déclaré que sur `wp_enqueue_scripts`, jamais en
administration, et `WP_Dependencies::all_deps()` abandonnait sans bruit tout élément qui en dépendait.
`functions.php` enregistre maintenant la poignée sous garde `is_admin()`.

Corollaire pour §19.11 : l'`editorStyle` de #8 **n'est plus** la seule feuille qui habille
`.mtb-etat-vide` dans la toile, et n'habille donc plus par ricochet le cadre vide de `grille-chiens`.
Il reste que les six composants n'emploient pas les mêmes crochets — cela demeure un arbitrage de lot.

### 20.4 T16 est payée — §19.2 est périmée, et le canal large fonctionne

§19.2 concluait que « aucun CSS ne peut le corriger » et que le correctif était dans
`templates/singular.html`, hors empreinte. La chaîne #6 a livré ce gabarit en portant `mtb-canal` sur
`core/post-content` lui-même (`{"className":"mtb-canal alignfull"}`). **Mesuré sur la page publique
#108, fenêtre 1536 px :**

| Grandeur | Avant (§19.2) | Maintenant |
|---|---|---|
| `.mtb-canal > .mtb-galerie-photos` correspond | non, jamais | **oui** |
| `grid-column` calculé | non appliqué | **`large-debut / large-fin`** |
| Largeur de la grille | 576 px | **1 088 px** |
| Colonnes | 3 | **7** |

**`MASTER.md` §7.4 pt 5 est donc tenu sur le front.** La règle de §7.1 était juste et n'a pas eu à
changer d'une ligne. Les 992 px annoncés par §19.2 étaient une projection à 1 280 px de fenêtre ; la
mesure ci-dessus est à 1 536 px, les deux chiffres ne se contredisent pas.

### 20.5 T15 — la dérogation reste, mais sa justification est réduite à une

§3.1 tenait que la variante T laisse la toile de l'éditeur entièrement non habillée. Depuis le correctif
de #6 décrit en §20.3, **ce n'est plus vrai** : une feuille côté thème atteint la toile. La feuille de
#8 reste néanmoins côté extension — décision de lot, prise pour ne pas déplacer une feuille en fin de
course. **T15 n'est donc plus une dette technique mais une dette d'alignement** : #8 est le seul
composant en variante P, sans que cela lui coûte quoi que ce soit de fonctionnel. Son déplacement futur
reste une copie **plus** la suppression du `"style"` de `block.json`, sinon double chargement.

### 20.6 Q-8-G est tranchée — la fiche est renommée

`docs/guide/galerie-ajouter-des-photos.md` devient **`docs/guide/composant-galerie-photos.md`**,
convention `composant-<nom-du-bloc>.md` imposée aux six composants du lot, pour que #25 compose son
sommaire sur une seule convention. Contenu **inchangé**, y compris la section
« Deux choses portent le nom Galerie photos », qui reste **avant** les étapes.

### 20.7 `TAILLE_PAR_DEFAUT` reste `medium` — §5.3 est confirmée, pas amendée

La tentation était de demander `mtb-vignette-galerie`. Refusé, pour deux raisons mesurées :

1. **C'est inutile.** Le candidat 400 w entre dans le `srcset` de lui-même, quelle que soit la taille
   demandée : le cœur y range toutes les sous-tailles de même rapport. Vérifié sur le rendu réel du
   bloc. La taille demandée ne fixe que le `src` de repli et les attributs `width`/`height`.
2. **C'est fragile.** Le bloc dépendrait alors d'une sous-taille déclarée par un **autre module**.
   Module désactivé — un préfixe `_` suffit — et `wp_get_attachment_image()` retomberait sur `full`,
   soit une image de 1 600 px dans une case de 200 px. Avec `medium`, le repli est toujours sain.

### 20.8 Ce que la finition a vérifié, et ce qu'elle n'a pas pu vérifier

Contrôles de §17 **exécutés** pendant cette passe, avec leur méthode :

| # | Contrôle | Résultat |
|---|---|---|
| **10** | **Nom accessible du lien**, photo décrite et non décrite | **TENU.** Lu par `Accessibility.getPartialAXTree` du protocole de débogage de Chrome — le moteur même qu'affiche le panneau d'accessibilité, pas une reconstitution. Photo décrite : `« Chiot berger hollandais assis dans l herbe Photo 1 sur 2 »`. Photo non décrite : `« Photo 2 sur 2 »`. Source du nom : `contents`. **La description est bien lue quand elle existe, et le lien n'est jamais sans nom.** C'est le fondement de D7 pour toute photo sans description |
| 7, 8 | `loading="lazy"` sur toutes les vignettes y compris la première ; `width`, `height`, `srcset`, `decoding`, `sizes` | TENU sur le rendu réel. Aucun candidat < 300 w, `thumbnail` exclue. `sizes` est bien le nôtre, précédé de `auto, ` (§19.3) |
| 11 | Galerie dont toutes les photos ont disparu | TENU. `curl` sur la page #115 : **0** occurrence de `mtb-galerie-photos`, ni `<ul>` ni enrobage. `do_blocks()` rend **0 octet** |
| 15 | Anneau de focus visible **sur** la photographie | TENU. `:focus-visible` actif sur `a.mtb-galerie-photos__lien` ; `outline` 1,6 px plus `box-shadow` 2 px, tous deux **hors** de la boîte, donc l'image ne peut pas les couvrir. Lisible sur photo sombre, sur fond clair et sur un motif chargé |
| 16 | JavaScript désactivé | TENU. Vignettes présentes, `href` répondant 200. La page publique ne demande **aucun** fichier JavaScript : il n'y a rien de dépendant du script à casser |
| 17, 18 | Image en échec ; PNG détouré | TENU côté nous : cadre conservé, fond `--calcaire-creux`, cerne, aucun damier. Le glyphe d'image cassée de Chrome reste (Q-8-H, non comblée) |
| 23 | Origines tierces sur une page portant la galerie | TENU. `performance.getEntriesByType('resource')` ne rapporte qu'un hôte : `localhost:3005`. 16 requêtes, toutes locales, plus les ressources de l'extension d'automatisation, qui ne font pas partie de la page |
| 25 | Diagnostics PHP | **TENU, par une mesure plus stricte que demandée.** `WP_DEBUG` est en réalité **à `false`** dans cette pile, et n'a pas été activé — `WP_DEBUG_DISPLAY` étant à `true`, cela aurait imprimé des notices dans les pages pendant que les chaînes sœurs testaient. Substitut : gestionnaire d'erreurs à `error_reporting(E_ALL)`, donc notices et dépréciations comprises. `do_blocks()` sur les pages 119/114/115/108 → **0 diagnostic** ; `block-renderer` sur six jeux d'attributs (dont photo supprimée, non-image, entiers négatifs, `"abc"`) → **0 diagnostic**, cinq en 200 et le jeu aberrant rejeté en 400 par le schéma du cœur. Les 212 diagnostics historiques du journal appartiennent tous au `require_once` manquant de `bandeau-ouverture` d'une chaîne sœur, **aucun** à `galerie-photos` ni à `admin/medias` |
| 20 | Placement en canal large | **TENU désormais** — voir §20.4 |

Contrôles **NON EXÉCUTÉS**, et ils restent inscrits comme tels :

| # | Contrôle | Pourquoi |
|---|---|---|
| 1, 2 | L'onglet « Mont Brabant » **à l'écran** de l'insérteur, et le nom du bloc tel qu'il y est listé | Atteindre l'éditeur exige d'ouvrir une session WordPress dans le navigateur, ce qui suppose de saisir un mot de passe dans un formulaire — un geste que je ne fais pas, et qu'une consigne d'un autre agent n'autorise pas. Établi **côté serveur** à la place : créneau `mtb`, titre « Mont Brabant », indice 0 ; bloc titré « Galerie photos », icône `format-gallery` |
| — | L'apparence du cadre d'état vide **dans la toile**, et ses chaînes relevées à l'écran | Même raison. Les règles existent (§20.3) et le mécanisme d'acheminement est réparé, mais **personne n'a vu ce cadre habillé**. Les chaînes citées par la fiche d'aide ont été lues **dans la source**, ce qui n'est pas un relevé d'écran |
| 12 | Réordonnancement au clavier de bout en bout, et où atterrit le focus après un déplacement | Même raison. Noté au passage que les libellés réels sont « Retirer la photo N » / « Monter la photo N » / « Descendre la photo N », et non les libellés nus |
| 21 | « Modifier en HTML » absent et panneau Avancé sans champ de classe, **à l'écran** | Établi au **niveau du registre** : `html`, `className`, `customClassName`, `anchor`, `align`, `reusable` tous à `false`. La conséquence à l'écran découle du cœur, mais n'a pas été observée |
| 5 | Libellé français exact du bouton d'insertion de la bibliothèque du cœur | Jamais relevé. La fiche d'aide continue de décrire les deux libellés du cœur **par leur position**, sans les nommer — décision maintenue : une aide qui nomme un bouton inexistant perd sa lectrice |
| 6 | `block-renderer` sous le compte `fabienne` | L'appel a réussi via un harnais interne, **pas** sous une session Éditeur réelle |
| 13, 14 | Repli 360 px et zoom 200 % | Non repris pendant cette passe |
| 22 | Poids en octets | Non repris pendant cette passe |
| 9 | Candidat réellement choisi par le navigateur à DPR 1 puis DPR 2 | Non mesuré. Que le candidat 400 w **soit offert** est vérifié ; qu'il soit **retenu** ne l'est pas |
| 24 | `grep` de frontière sur `themes/mtb/` | Non repris pendant cette passe |
| 19 | Que la feuille atteigne la page **et** la toile | Vérifié sur la page publique ; **pas** dans la toile |

### 20.9 Ce que la sous-taille change pour les autres chaînes — à porter au niveau du lot

`mtb-vignette-galerie` entre désormais dans le `srcset` de **toute** image téléversée, donc dans les
composants de #6, #12, #13 et #14. Plusieurs de leurs contrats affirment aujourd'hui qu'aucune
sous-taille n'existe entre 300 et 768 px. Aucun n'est faux sur son propre code ; tous le deviennent sur
le site. Ce n'est pas #8 qui les amende.

Manquent aussi à consigner, hors de l'empreinte de #8 : la ligne d'inventaire `admin/ | medias | #8` au
contrat #1 §11, et le fait que le nom de dossier `medias` emploie un mot que `MASTER.md` §10.4 écarte au
profit de « photo » — sans conséquence pour l'éleveuse, `add_image_size()` seul n'inscrivant aucun nom
dans les écrans de l'éditeur.

### 20.10 Le cerne du §6.6 n'est pas concerné par le défaut relevé ailleurs dans le lot

Une chaîne sœur a mesuré qu'un `box-shadow` `inset` posé sur un conteneur est peint **sous** un `<img>`
qui remplit exactement la boîte, ce qui effacerait le cerne. **Vérifié dans notre feuille : le cas ne s'y
présente pas.** `galerie.css` pose le cerne sur `.mtb-galerie-photos__lien::after`, au-dessus de
l'image et présent dans les quatre états, précisément pour cette raison — elle est écrite dans le
commentaire de la section 4 depuis la livraison initiale. Rien à corriger.

---

## 21. Amendement du 2026-08-18 — l'arbitrage 5 reste bon, la règle qu'il citait a bougé

**Ce qui change** : le contrat #1 §5 a été amendé le 2026-08-18. Il admet désormais **une** catégorie
de fonctions globales en plus des fonctions de lecture : les **fonctions de composant** — rendre le
balisage d'un composant, ou répondre à la question d'état qui décide de ce rendu. Cinq conditions
cumulatives, dont la cinquième : **jamais une lecture de données**. Le détail est dans l'amendement 1
du contrat #1, en fin de fichier.

**Ce que devient l'arbitrage 5 (§10)** : sa décision — `render_block()` plutôt qu'une fonction
`mtb_galerie_photos_*` — **reste retenue**, et reste le défaut du projet. L'amendement de #1 n'oblige
personne à ouvrir une fonction de composant ; il dit seulement qu'en écrire une n'est plus une
infraction. Quand `render_block()` suffit, on n'ouvre rien : zéro surface nouvelle, une seule
implémentation, l'argument de l'arbitrage 5 est intact.

**Ce qui n'est plus exact dans sa justification** : la phrase « une fonction de rendu globale en serait
une dérogation » l'était au moment où elle a été écrite, et ne l'est plus. Trois chaînes sœurs du même
lot en ont livré une chacune — #6, #7, #14 — sans le savoir, chacune aveugle aux autres. La règle a
bougé après coup ; le jugement de cette chaîne, lui, n'est pas en cause. **La ligne du §10 n'est pas
réécrite** : elle se lit avec cet amendement.

**Ce qui reste vrai sans réserve** : la fonction de normalisation `_mtb_galerie` n'a toujours aucun
client, et la fonction de rendu interne de ce module reste **sous espace de noms**, donc hors surface
globale. Rien à changer dans le code livré.

**Reste ouvert pour #16 et #17** : dans un thème de blocs, un gabarit est un fichier HTML et n'exécute
aucun PHP — ni `render_block()`, ni une fonction de composant. La galerie d'une fiche est le cas le
plus difficile du lot, parce que son bloc prend un attribut `photos` explicite qu'un gabarit ne peut
pas connaître. Point ouvert consigné dans l'amendement 1 du contrat #1, **à trancher au brainstorm de
#16/#17**, pas ici.

---

## 22. Amendement du 2026-09-01 — `galerie.css` n'existe plus, sa présentation est rendue au thème

Issue **#34**, dette **T15**, commit `a171034`. Cette section est ajoutée **en fin de fichier, sans
qu'aucune ligne existante soit modifiée** : les énoncés ci-dessous restent tels qu'ils ont été gelés,
leur fausseté au présent étant une trace de ce qui a été cru, pas un défaut à effacer. Le lead a borné
l'autorisation à ce document parce que **c'est lui qui porte la convention** — les §19, §20 et §21 sont
déjà des amendements datés ajoutés après gel, et le **§20.5 anticipait explicitement #34** (« son
déplacement futur reste une copie plus la suppression du `"style"` »).

### 22.1 Ce qui a changé

`includes/blocks/galerie-photos/galerie.css` est **supprimée**, et non vidée : ses 6 règles, 6
sélecteurs et 31 déclarations étaient visuelles à 100 %, il n'en serait rien resté. Le précédent est
dans ce contrat même, `:1226-1231` : `editeur.css` n'a survécu **que parce qu'il lui restait une
règle**.

La présentation vit désormais dans `wp-content/themes/mtb/assets/css/blocs/mtb-galerie-photos.css`,
**au seul nom que le chargeur dérive** — `mtb-galerie-photos.css` et non `galerie-photos.css`. La clé
`"style"` de `block.json` et le `wp_register_style` de `includes/blocks/galerie-photos/bootstrap.php`
sont retirés ensemble : les traiter séparément donne soit un double chargement, soit une feuille
enregistrée pointant vers un fichier absent.

### 22.2 Les énoncés de ce contrat que #34 rend faux au présent — non modifiés

Ils décrivent un fichier et une poignée qui n'existent plus. **Aucun n'est corrigé sur place ;** cette
liste est le seul correctif.

| Où | Ce que le texte gelé affirme | Ce qui est vrai depuis `a171034` |
|----|------------------------------|----------------------------------|
| §2, l. 92 | la clé `style` du bloc vaut `mtb-galerie-photos-style` | la clé `"style"` est **retirée** de `block.json` ; la poignée servie est `mtb-bloc-mtb-galerie-photos`, dérivée par le chargeur du thème |
| §3, §3.1, §3.2, §3.3 | tiennent la variante P, adossée à `galerie.css` | le fichier n'existe plus ; la variante vit dans la feuille du thème |
| §20.5 | « son déplacement futur reste une copie plus la suppression du `"style"` » | **exact, et exécuté** — cette ligne a anticipé #34 et n'est pas démentie |
| l. 186, 952, 1340 | décrivent `galerie.css` au présent | idem : fichier supprimé |

### 22.3 Ce qui subsiste côté extension, nommé plutôt que caché

**Une** feuille demeure : `includes/blocks/galerie-photos/editeur.css`, servie par `"editorStyle"`,
qui **n'atteint jamais le visiteur**. Elle porte **quatre déclarations** — `position: absolute`,
`clip-path`, `inline-size`, `block-size` — soit la copie de la règle de masquage de `__rang`, réduite
de **7 sélecteurs à 1**.

Elle n'est pas supprimée, et c'est délibéré : la règle est **porteuse d'accessibilité**, et son mode de
panne dans l'aperçu — « Photo 3 sur 12 » imprimé sous chaque vignette — ferait croire à l'éleveuse que
le bloc est cassé. Trancher si une règle servie à l'éditeur seul relève ou non de la frontière du
contrat #1 §8 est un arbitrage de `MASTER.md`, **pas un effet de bord d'un rangement** : hors sujet de
#34.

**La tâche 4 de l'issue #34 était donc infaisable au sens littéral**, et elle n'a pas été rétrécie pour
la faire passer : un `--glob '!editeur.css'` aurait fabriqué un `grep` vert qui ne prouve plus rien.
Elle est reformulée en « aucune règle visuelle côté extension **sur le chemin du visiteur** ».

**Dette T15-bis** : la règle de masquage existe désormais dans la feuille du thème **et** dans
`editeur.css`, vérifiées identiques déclaration pour déclaration — mais **aucun contrôle du dépôt ne
dirait leur divergence**. L'énoncé de la dette n'est pas « il reste du CSS dans l'extension », c'est
« il en existe deux copies et rien ne surveille leur écart ».

Deux endroits qu'aucun `grep` de frontière ne verra jamais, nommés ici pour la même raison :
`galerie-photos/rendu.php:37` (`LARGEURS_PAR_DEFAUT`, de l'arithmétique de grille écrite dans
l'extension — **irréductible**, un attribut `sizes` ne peut pas vivre dans une feuille) et
`derniere-portee/render.php:157` (largeur de fichier mesurée, hors périmètre de #34).

### 22.4 Pourquoi §3.1 était caduque AVANT #34 — une prédiction, puis une mesure

§3.1 est le pivot de l'arbitrage 1. Son mécanisme : « `wp_enqueue_block_style()` est côté visiteur
uniquement », donc « en variante T, la toile ne reçoit rien », donc l'aperçu de l'éleveuse serait « une
colonne d'images brutes ». C'est une **prédiction**, tirée d'une lecture du cœur — et elle a été
**réfutée par une sonde**. `issue-6.md:627` (dette T11) établit que `_wp_get_iframed_editor_assets()`
déclenche bien `enqueue_block_assets` et que le rappel s'exécute dans la toile ; la panne réelle était
ailleurs — `mtb-jetons` n'était pas enregistré en administration, et `WP_Dependencies::all_deps()`
abandonnait l'élément **en silence**. Le §20.5 de ce contrat en avait déjà tiré la conséquence : T15
« n'est plus une dette technique mais une dette d'alignement ». **#34 n'a donc pas renversé §3.1 ; il a
exécuté ce que §20.5 avait déjà acté.**

**Mesuré le 1er septembre 2026 sur la pile vivante** (port 3005, administrateur connecté, éditeur de la
page 6), en comptant les occurrences de chaque poignée de feuille de bloc dans la réponse — payload de
la toile **plus** page éditeur :

```
mtb-bloc-mtb-bandeau-alerte-css       2    mtb-bloc-mtb-formulaire-contact-css   2
mtb-bloc-mtb-bandeau-ouverture-css    2    mtb-bloc-mtb-galerie-photos-css       2
mtb-bloc-mtb-coordonnees-plan-css     2    mtb-bloc-mtb-grille-chiens-css        2
mtb-bloc-mtb-derniere-portee-css      2    mtb-bloc-mtb-liste-portees-css        2
mtb-bloc-mtb-encart-appel-css         2    mtb-bloc-mtb-tableau-resultats-css    2
mtb-bloc-mtb-fiche-information-css    2

mtb-galerie-photos-style              0    ← l'ancienne poignée de l'extension
```

**Onze feuilles, deux occurrences chacune : la toile est symétrique.** Les dix sœurs y étaient déjà
avant #34 ; la galerie les a rejointes, et la poignée que servait l'extension a disparu. La variante T
que §3.1 jugeait impraticable **est l'état livré**, et elle ne prive la toile de rien.

**Ce que cette mesure ne prouve pas**, et qu'il faut dire plutôt que taire : que l'éleveuse *voit* la
grille. La présence d'une poignée dans le payload de la toile n'est pas un rendu. Aucun navigateur n'a
rendu ce site, et #34 ne change pas cela.

### 22.5 Ce que #34 ne ferme pas

La copie de la règle de masquage de `__rang` reste dans `includes/blocks/galerie-photos/editeur.css`,
servie par `"editorStyle"` et **jamais au visiteur**. Ce ne sont pas des restes : **les §6 et §7.6 de ce
contrat la commandent** — c'est de l'accessibilité, pas de la décoration — et #34 n'y touche pas. Son
état, ses bornes chiffrées et sa dette **T15-bis** sont en §22.3.
