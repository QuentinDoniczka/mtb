# Contrat d'interface — Issue #6 — Composant Bandeau d'ouverture

**Gelé le 2026-08-17** par `lead-issue-mtb`, à partir des deux plans de `leaddev-back-mtb` et
`leaddev-front-mtb`, écrits en parallèle et sans se voir.

Ce document est contraignant. Les deux moitiés du travail s'écrivent en parallèle, dans un arbre
unique, sans branche : **une seule faute de frappe sur un nom de classe laisse le composant non
habillé, sans erreur, sans avertissement, sur un site qui répond 200.** C'est le mode de défaillance
que ce contrat existe pour rendre impossible.

**Approche retenue** — option A du brainstorm : bloc serveur avec écran d'éditeur réel, trois réglages
(photo, titre, accroche), le champ titre **vide par défaut se repliant sur le titre de la page**, un
seul bandeau par page (`multiple: false`), et un filtre qui efface `core/post-title` quand le bandeau
est le premier bloc — le bandeau porte alors le `<h1>`.

**Empreinte d'écriture de l'issue, close.** Six chaînes partagent l'arbre de travail sur `main`.

| Fichier | Écrit par |
|---|---|
| `wp-content/plugins/mtb-core/includes/blocks/categorie-mtb/**` | `dev-back-mtb` |
| `wp-content/plugins/mtb-core/includes/blocks/bandeau-ouverture/**` | `dev-back-mtb` |
| `wp-content/themes/mtb/assets/css/blocs/mtb-bandeau-ouverture.css` (**neuf**) | `dev-ux-mtb` |
| `wp-content/themes/mtb/assets/css/base.css` (**T7 seulement, en fin de fichier**) | `dev-ux-mtb` |
| `docs/contracts/issue-6.md` | `lead-issue-mtb` |
| `docs/guide/…` (la fiche) | `doc-client-mtb` |

Hors empreinte, sans exception : `theme.json`, `functions.php`, `editor.css`, `templates/`, `parts/`,
`patterns/`, `mtb-core.php`, `class-loader.php`, tout autre dossier de `includes/blocks/`. Un besoin
qui tombe là est une **dette déclarée**, jamais une étape.

---

## 1. Le nom du bloc — bloquant, et silencieux s'il est faux

```
mtb/bandeau-ouverture
```

`functions.php:194-224` compose le nom de la feuille de style par `str_replace( '/', '-', $nom )`.
Un bloc nommé `mtb/bandeau` ou `mtb/bandeau_ouverture` reçoit **aucun CSS, aucune erreur, aucun
avertissement**. La feuille servie est donc, mécaniquement :

`wp-content/themes/mtb/assets/css/blocs/mtb-bandeau-ouverture.css`
poignée `mtb-bloc-mtb-bandeau-ouverture`, dépendance `mtb-jetons`, rien à déclarer.

## 2. Fonctions de lecture exposées par l'extension

```php
mtb_bandeau_ouverture_porte_le_titre( int $post_id = 0 ): bool
```

- `true` — le bandeau de ce contenu émet le `<h1>` de la page. Un gabarit **ne doit pas** émettre le
  sien.
- `false` — il ne l'émet pas (absent, pas premier, page protégée non déverrouillée, sans texte, hors
  vue singulière). Le gabarit est libre.
- `0` = le contenu actuellement interrogé. **Rend `false` dès qu'elle ne peut pas décider.**
  N'imprime rien, ne rend aucun HTML. **À appeler derrière `function_exists()`.**

Espace de noms global, déclarée dans `blocks/bandeau-ouverture/titre-principal.php`.

**Écart de rangement, assumé** : le contrat #1 §2 domicilie les fonctions `mtb_*` publiques dans
`includes/query/`. Celle-ci reste dans le module du bloc, parce que ce n'est **pas une lecture de
contenu** mais un prédicat de décision — même nature que `mtb_resultat_disciplines()`, exception déjà
tranchée par la décision 16 — et parce qu'elle décrit le comportement du bloc auquel elle appartient.
Le nom est unique, donc aucun ombrage possible (décision 19).

**Aucune fonction `mtb_get_*` n'est livrée, et c'est une information contractuelle, pas un oubli.** Le
bandeau n'affiche aucune donnée d'élevage : ni portée, ni chien, ni résultat, ni date, ni discipline.
**Le thème ne doit planifier aucun appel de lecture pour ce composant.** La dette **T10** reste
entière ; cette issue ne la solde pas et ne doit pas prétendre le contraire.

## 3. Blocs enregistrés

| | |
|---|---|
| Nom | `mtb/bandeau-ouverture` |
| Titre dans l'insérteur | **Bandeau d'ouverture** |
| Description | **Une photo en haut d'une page, avec le titre de la page et une phrase de présentation.** |
| Catégorie | `mtb` — **Mont Brabant**, en **tête** de l'insérteur, livrée par `includes/blocks/categorie-mtb/` |
| `apiVersion` | `3` |
| `icon` | `cover-image` (Dashicon du cœur, police locale — aucune requête, aucun SVG à écrire) |
| `textdomain` | **absente** — contrat #1 §7 : aucune i18n, français littéral |
| `usesContext` | `[ "postId", "postType" ]` — **indispensable** : `$block->context['postId']` n'est peuplé que pour les clés déclarées ici. Sans cette ligne, le repli sur le titre de la page et la décision du `<h1>` tombent **en silence** dans une boucle. |
| Rendu | **serveur** (`render` → `file:./render.php`). Aucun balisage enregistré, donc **aucune dérive de validation de bloc** possible — plus de « Ce bloc contient du contenu inattendu », que Fabienne ne saurait pas résoudre. **Correction du 2026-08-17, mesurée par `dev-back-mtb` : écrire `save: null` casse le bloc.** WP 6.9 teste `if ( "function" == typeof a.save )`, échoue, imprime `The "save" property must be a valid function.` et **n'enregistre pas le bloc**. La forme livrée est `save: function () { return null; }`, qui tient exactement la même intention. **Ma rédaction initiale était fausse ; les neuf blocs sœurs doivent copier la forme corrigée.** |
| JavaScript public | **zéro octet** (`viewScript` absente) |
| `style` / `editorStyle` | **absentes** — l'extension n'émet aucune CSS (contrat #1 §8) |
| `editorScript` | **la poignée** `mtb-bandeau-ouverture-editeur`, **jamais** `file:./editeur.js` — `file:` ferait chercher un `editeur.asset.php` produit par un bundler qui n'existe pas, et déclencherait un `_doing_it_wrong` |

### Attributs — liste close

| Clé | Type | Défaut | Vide → |
|---|---|---|---|
| `photo` | `integer` | `0` | modificateur `--sans-photo`, bande de texte sur `--pin` |
| `titre` | `string` | `""` | **repli sur le titre de la page**, jamais sur du vide |
| `accroche` | `string` | `""` | l'élément `__accroche` n'est pas émis |

**Aucun autre attribut, et surtout pas ceux-là** : ni `url` de photo, ni `alt`, ni `largeur`/`hauteur`,
ni `ratio`, ni `align`, ni couleur. Le texte alternatif vit **sur la photo, dans la bibliothèque,
saisi une fois** ; une correction s'y propage partout. Recopier l'`alt` dans un attribut de bloc le
figerait à la date de l'insertion — la règle d'or appliquée aux blocs.

### `supports`

Neuf clés booléennes à `false` : `align`, `alignWide`, `anchor`, `customClassName`, `html`, `lock`,
`multiple`, `renaming`, `reusable`. `className` reste à son défaut (`true`).

**Aucune clé à forme d'objet n'est écrite** — `color`, `typography`, `spacing`, `dimensions`,
`border`, `shadow`, `background`, `filter`, `position`, `layout` : elles ne s'éteignent **pas** en
écrivant `false` (ce n'est pas dans leur schéma, la clé serait **ignorée en silence** et on croirait
avoir posé un verrou — la leçon du « verrou fantôme » du contrat #2, amendement 1). Elles s'éteignent
en **n'étant pas déclarées du tout**. `theme.json` verrouille les *valeurs* offertes ; `block.json`
décide de l'*existence* du contrôle. Les deux sont nécessaires.

### `example`

```json
{ "attributes": { "titre": "Titre de la page",
                  "accroche": "Une phrase de présentation, sous le titre." },
  "viewportWidth": 800 }
```

`photo` reste à `0` : **zéro image distante** (D6), l'aperçu de l'insérteur montre l'état
`--sans-photo`. **Zéro fait d'élevage** (D11) : pas un nom de chien, pas une portée, pas une date, pas
un lieu. Deux chaînes purement génériques.

### Réglages, tels qu'ils apparaissent à l'écran

Dans la toile : **l'aperçu réel rendu par le serveur** (`wp.serverSideRender`) et rien d'autre — pas
de saisie en place, pas de bouton flottant sur la photo. Coût assumé : elle tape le titre et
l'accroche dans le panneau latéral. En échange, ce qu'elle voit est ce que verra le visiteur, et le
balisage n'a **qu'une seule vérité**.

`urlQueryArgs: { post_id: <postId> }` sur `serverSideRender` **n'est pas optionnel** : sans lui
l'aperçu n'a aucun contexte de contenu, le repli sur le titre de la page est vide, et elle verrait un
bandeau muet là où le site en montrera un titré.

Panneau latéral (`InspectorControls`), deux sections, dans cet ordre :

1. **Photo** — `MediaUploadCheck` autour de `MediaUpload`, `allowedTypes: [ 'image' ]`,
   `multiple: false`.
2. **Titre et accroche** — `TextControl` puis `TextareaControl` (3 lignes).

**Le titre et l'accroche sont des champs de texte simple, jamais un `RichText`.** Conséquence : aucun
format, aucun gras, aucun italique, **aucun lien**. Ce n'est pas de la frilosité, c'est une mesure :
`--laiton-clair` sur le composé du voile donne **4,09:1**, échec AA pour du texte normal, et MASTER ne
définit aucune encre de lien pour un fond « voile sur photo ». Une chaîne future qui voudrait passer
ces champs en `RichText` doit d'abord faire définir cette paire de contraste par `lead-design-mtb`.

Aucune `BlockControls`, aucun bouton d'alignement, aucun sélecteur de ratio, aucun `MediaReplaceFlow` :
**un seul chemin pour changer la photo.**

## 4. La décision du `<h1>` — un seul juge, deux consommateurs

Le niveau **n'est jamais un réglage** et n'apparaît nulle part à l'écran. Il est **calculé** par une
seule fonction du module. `effacer_le_titre_du_coeur()` et `render.php` l'appellent ; **aucun des deux
ne réimplémente un morceau de la règle.**

Gardes, dans cet ordre, chacune rendant `false` :

| # | Garde | Pourquoi elle existe |
|---|---|---|
| 1 | `$post_id <= 0`, ou `get_post()` n'est pas un `WP_Post` | Aucun contexte, aucune décision. |
| 2 | `! is_singular()` **ou** `get_queried_object_id() !== $post_id` | **Correction apportée par `leaddev-back-mtb` à ma décision initiale, et c'est un vrai bug qu'elle évite.** Sans elle, sur une archive ou une page de recherche listant une page dont le contenu commence par un bandeau, le filtre efface le **titre-lien de cette entrée dans la liste** — alors que le bandeau, lui, n'y est pas rendu (les listes rendent l'extrait). On perdrait des liens de navigation en silence. Portée dans la fonction commune pour que les deux consommateurs en héritent **par construction**. |
| 3 | `post_password_required( $post_id )` | `core/post-content` rend alors le formulaire de mot de passe et `render.php` **ne s'exécute jamais** : effacer quand même le titre du cœur laisserait la page protégée **sans aucun `<h1>`**. C'est bien `post_password_required()` et non `has_password` — une fois le mot de passe saisi, le bandeau se rend et le titre du cœur doit repartir. |
| 4 | Le bandeau n'est pas le **premier bloc de premier niveau** du contenu | Voir ci-dessous. |
| 5 | Ni l'attribut `titre`, ni `post_title` ne portent de texte après `trim` | Un bandeau sans texte ne peut pas porter le titre principal. |

**Détermination de « premier bloc »** — `core/post-title` est rendu **avant** `post-content`, la
décision ne peut donc pas dépendre du bloc ayant déjà rendu :

1. Pré-test bon marché `has_block( 'mtb/bandeau-ouverture', $post )`. Faux → `false`, sans analyse.
2. `parse_blocks( $post->post_content )`, parcours du **premier niveau seulement**, en **sautant les
   blocs vides** intercalés par l'analyseur (`null === $bloc['blockName']` et `'' === trim(
   $bloc['innerHTML'] )`).
3. Le premier bloc réellement nommé doit être `mtb/bandeau-ouverture`.

**Un bandeau imbriqué dans un Groupe ou une Colonne n'est jamais premier** : le titre du cœur est
conservé, le bandeau rend un `<p>`. C'est délibéré et c'est le comportement le plus sûr.

**Mémoïsation** : `static array $memo` clé `$post_id`, portée de la requête. **Ni transient, ni cache
persistant** : la réponse dépend de `post_content`, qui change à chaque enregistrement, et un cache
périmé après modification est exactement l'échec de « saisi une fois, affiché partout » (contrat #1
§9).

**Filtre employé** : `render_block_core/post-title` (dynamique, cœur ≥ 5.7), et non `render_block` —
il ne s'exécute que pour le bloc visé au lieu d'être appelé pour chaque bloc de chaque page, et son
3ᵉ paramètre donne l'instance donc le contexte. `postId` se lit sur `$instance->context['postId']`,
**avec repli `get_queried_object_id()`** si la clé est absente.

**Aucune garde `is_admin()` dans `bootstrap.php`.** Les trois modules `fields/` commencent par
`if ( ! is_admin() ) { return; }` ; un module `blocks/` qui recopierait cette garde **ne
s'enregistrerait pas côté public** — le bloc disparaîtrait du site tout en fonctionnant dans
l'éditeur, sans erreur. **À écrire en commentaire**, parce que neuf blocs sœurs copieront ce fichier.

**Résultat net : exactement un `<h1>`, dans tous les cas de figure.**

**Obligation transmise à l'epic Gabarits (#16/#17), à lire deux fois** : le titre d'une page se rend
par `core/post-title`, jamais par un bloc Titre écrit à la main ni par `core/query-title`. Tout
gabarit futur qui émet son propre `<h1>` **doit** interroger
`mtb_bandeau_ouverture_porte_le_titre()`. C'est la seule façon d'obtenir deux `<h1>` sur une page, et
le filtre ne le verra pas. Et **ne jamais retirer `core/post-title` d'un gabarit** parce qu'un bandeau
pourrait s'y trouver : l'effacement est conditionnel et appartient à l'extension ; le retirer du
gabarit priverait de titre toutes les pages **sans** bandeau.

## 5. Balisage et crochets de classes — la moitié dont dépend le thème

État complet (photo + titre + accroche, bandeau premier bloc d'une page singulière) :

```html
<div class="mtb-bandeau-ouverture">
  <div class="mtb-bandeau-ouverture__photo mtb-photo">
    <img class="mtb-bandeau-ouverture__image"
         src srcset sizes="100vw" width height alt
         fetchpriority="high" loading="eager" decoding="async">
  </div>
  <div class="mtb-bandeau-ouverture__texte">
    <h1 class="mtb-bandeau-ouverture__titre">…</h1>
    <p class="mtb-bandeau-ouverture__accroche">…</p>
  </div>
</div>
```

### Table des crochets — exhaustive, close, à recopier littéralement

| Crochet exact | Élément | Émis quand | Si l'extension l'oublie |
|---|---|---|---|
| `mtb-bandeau-ouverture` | racine `<div>`, via `get_block_wrapper_attributes()` | **toujours** (sauf rendu vide) | rien n'est habillé |
| `mtb-bandeau-ouverture--sans-photo` | racine, **en plus** | **aucune `<img>` n'a été rendue** — champ vide **ou** pièce jointe supprimée **ou** fichier non-image **ou** `wp_get_attachment_image()` a renvoyé une chaîne vide. **Le modificateur décrit la sortie rendue, jamais le champ enregistré.** | bande sans fond `--pin` et texte sans voile : **contraste indéterminé** |
| `mtb-bandeau-ouverture__photo` | `<div>` enveloppant l'image | si photo | ratio, cerne et fond de repli absents |
| `mtb-photo` | **le même `<div>`, en plus** | si photo | rien aujourd'hui — voir §9 |
| `mtb-bandeau-ouverture__image` | l'`<img>`, via `'class' => …` passé à `wp_get_attachment_image()` | si photo | **photo non recadrée, non positionnée : le bandeau se dérègle en silence.** Le point de mésentente le plus probable de tout ce contrat — les deux plans l'ont signalé indépendamment. |
| `mtb-bandeau-ouverture__texte` | `<div>` enveloppant titre et/ou accroche | si titre **ou** accroche | **le voile n'existe pas : échec de contraste AA** |
| `mtb-bandeau-ouverture__titre` | **`<h1>` ou `<p>`, même classe** | si titre effectif | titre rendu comme un `h1` ordinaire, **avec un second filet double** |
| `mtb-bandeau-ouverture__accroche` | `<p>` | si accroche | accroche en corps de texte `--sans` `--texte` sur photo |

**Modificateurs qui ne doivent PAS être émis** : `--avec-photo`, `--sans-texte`, `--sans-accroche`,
`mtb-bloc-vide__nom`, `mtb-bloc-vide__phrase`.

> **Arbitrage.** `leaddev-back-mtb` proposait `--sans-texte` ; `leaddev-front-mtb` refusait de le
> recevoir. Le front a raison : le voile vit sur `__texte`, donc **l'absence de `__texte` produit déjà
> exactement le rendu voulu** (bande photo nue, cerne, filet, aucun voile). Aucune règle CSS ne cible
> `--sans-texte` : c'est une classe morte, et une classe morte est un nom que l'une des neuf chaînes
> sœurs orthographiera mal un jour. **Un seul modificateur : `--sans-photo`.**

### Convention de nommage — gelée pour les dix composants du catalogue

Base `mtb-<nom-du-bloc-sans-espace>` · éléments `mtb-<bloc>__<element>` · états
`mtb-<bloc>--<etat>`. Tout en minuscules, **sans accent**, éléments et états **en français**. Les neuf
blocs sœurs dérivent de ce patron sans se concerter. Un modificateur ne s'émet **que si une règle le
cible**.

### Attributs de l'image — contractuels

`fetchpriority="high"` · `loading="eager"` · `decoding="async"` (le cœur pose `lazy`/`async` par
défaut : **il faut les écraser**) · `width` et `height` explicites (décalage cumulé nul) · `srcset`
généré par la taille `full` · `sizes="100vw"` · `alt` = **exactement** la valeur de la médiathèque,
sinon `alt=""`.

`alt` est lu explicitement — `trim( (string) get_post_meta( $id, '_wp_attachment_image_alt', true ) )`
— et **passé en dur**, plutôt que laissé au repli historique du cœur sur le titre ou la légende du
fichier. **Aucun repli sur le titre, la légende ou le nom de fichier : l'extension n'invente jamais
d'alternative.** Vide → `alt=""`, image décorative, **le `<h1>` porte le sens** (MASTER §6.5,
littéral). Pas de `role="presentation"` en plus : redondant.

**Aucune `add_image_size()` n'est ajoutée** — elle n'agirait que sur les photos importées après,
obligerait à régénérer les vignettes des 52 pages reprises, et le `srcset` des tailles du cœur
(768 / 1024 / 1536 / 2048 / `full`) suffit jusqu'à 2560 px.

**`sizes="100vw"` est conservé malgré le repli de largeur du §7, et c'est un arbitrage, pas un
oubli.** Le bandeau rend aujourd'hui à 576 px : `100vw` sur-dimensionne donc la photo téléchargée.
L'alternative — inscrire la largeur du canal dans l'extension — graverait un fait de mise en page du
thème, temporaire, dans le module serveur de dix composants. **La frontière l'emporte sur les
octets** ; les photos sont hors du budget chiffré (BRIEF §12), et le filtre
`mtb_bandeau_ouverture_attributs_image` existe pour corriger le jour où T12 sera payée.

### Interdits de balisage

Aucun `style=""` · aucune propriété personnalisée en ligne · aucun `<figure>`/`<figcaption>` (il
hériterait des styles de légende de `base.css:324`) · aucun `<a>` autour de l'image · aucun `aria-*` ·
aucune classe `has-*-color` · **aucune classe `alignwide` ni `alignfull`** · racine `<div>`, **ni
`<section>` ni `<header>`** (un `<section>` sans nom accessible est un repère générique inutile, et un
second repère dans `<main>` brouille la carte de la page — le sens est porté par le `<h1>`).

**Aucun élément de voile n'est émis** : `__photo` et la racine offrent chacun un pseudo-élément.
L'ordre du balisage — **photo puis texte** — permet la superposition sans `order` ni
`flex-direction: reverse`.

### Six garanties, six interdits symétriques

| L'extension garantit | Le thème ne doit pas |
|---|---|
| La racine porte toujours `mtb-bandeau-ouverture` | s'accrocher à `wp-block-mtb-bandeau-ouverture` si le cœur l'ajoute — c'est une classe du cœur ; ni à `attachment-full`, ni à `size-full` |
| L'ordre est **photo puis texte** | l'inverser par `order` ou `flex-direction` |
| `__texte` **n'existe pas** quand il n'y a aucun texte | styliser un `__texte` vide |
| **Aucun** style en ligne, **aucun** `alignfull`/`alignwide`, **aucune** décision visuelle | attendre `.alignfull` pour le débordement pleine largeur — **il n'y en a pas** |
| Aucun élément de voile n'est émis | attendre un `__voile` |
| `__titre` a **toujours** la même classe, quelle que soit la balise | styliser `h1` ou `p` **par la balise** à l'intérieur du bandeau |

### Deux obligations transmises au thème

1. **Le débordement pleine largeur est entièrement à la charge du thème**, sur
   `.mtb-bandeau-ouverture`. La racine est un enfant direct du conteneur `constrained` de
   `core/post-content` ; sans règle de thème elle est plafonnée. Voir §7 — le repli est acté.
2. **Neutraliser la règle `h1` de `base.css`** à l'intérieur du bandeau : `background-image` (le
   filet double), `margin-block` et `padding-block-end`. Sans cela le bandeau afficherait **deux
   filets doubles dans le même bloc visuel** — ce que MASTER §2.1 interdit nommément — et seulement
   dans la variante `<h1>`, donc **de façon asymétrique avec la variante `<p>`**, rendant le mécanisme
   visible. Les deux plans l'ont signalé indépendamment ; c'est le second point de mésentente le plus
   coûteux de ce contrat.

**L'invisibilité du mécanisme `<h1>` / `<p>` est un critère de recette, pas une intention.** Le
sélecteur du titre est **une classe seule** — jamais `h1.…`, jamais `p.…`, jamais `h1, p` — et toute
propriété que `base.css` pose sur `h1` **ou** sur `p` est redéclarée. Contrôle imposé : deux pages,
l'une avec le bandeau en premier bloc (`<h1>`), l'autre avec un paragraphe avant (`<p>`), puis **diff
de `getComputedStyle()` sur `.mtb-bandeau-ouverture__titre`** — **toute propriété doit être égale. Un
seul écart = mécanisme visible = non conforme.**

## 6. Le voile et le contraste — ratifié, avec sa correction

Le voile est porté par **`__texte`**, non par la photo : `background-image: var(--voile-photo)`,
`no-repeat`, `left bottom`, `background-size: 100% 300%`. Le bloc de texte n'expose donc jamais que le
**tiers bas** du dégradé, quelle que soit la hauteur du texte : l'opacité va de **0,86** (bas) à
**0,7228** (haut). Le seuil ≥ 0,72 de MASTER §6.4 devient **structurel** — garanti par construction,
pas par la retenue de l'éleveuse.

**Correction apportée par `leaddev-front-mtb`, ratifiée.** Ce dispositif seul crée une **arête franche
à 0,72 en travers de la photo** — une barre sombre visible sur une photo claire, qu'aucune lecture de
§6.4 n'autorise. Un pseudo-élément `__texte::before` (hauteur `200 %`, `inset-block-end: 100%`, même
`--voile-photo` en `background-size: 100% 150%`, ancré en haut) rétablit la portion 33,3 % → 100 % du
même dégradé, soit **0,7228 → 0,06**. Les deux morceaux se raccordent **exactement à 0,7228** : le
résultat est le dégradé de MASTER §6.4 à l'identique, de hauteur `3H`, dont le palier tombe pile sur
le bord haut du texte. L'arête ne disparaît pas, elle passe d'un saut de **72 %** à un saut de **6 %**,
sous le seuil perceptible sur une photographie.

`300 %`, `200 %`, `150 %` ne sont pas des valeurs de design inventées : elles sont **dérivées du
palier 34 % de `--voile-photo` lui-même** (1/3 ≈ 33,3 % ≤ 34 %). `3` est la seule valeur défendable :
en dessous l'opacité au bord haut tombe sous 0,72 et l'AA casse, au-dessus on assombrit la photo plus
que MASTER ne le demande. **L'arithmétique s'écrit en commentaire dans le fichier.**

Ratios mesurés contre les **deux pixels extrêmes possibles** (blanc pur, noir pur), jamais une moyenne
de photo :

| Point mesuré | Pixel sous le voile | Opacité | Fond composé | Encre `--calcaire` | Verdict |
|---|---|---|---|---|---|
| **Bord haut du bloc de texte** — le pire point | blanc pur `#FFFFFF` | 0,7228 | `#57615B` | **5,71:1** | ✓ AA toutes tailles |
| Bord bas du bandeau | blanc pur | 0,86 | `#37433C` | **9,18:1** | ✓ AAA |
| Bord haut du bloc de texte | noir pur `#000000` | 0,7228 | — | **15,72:1** | ✓ AAA |
| Photo qui ne charge pas (`--calcaire-creux` sous le voile) | `#E7E5DA` | 0,7228 | — | **6,38:1** | ✓ AA |
| **Sans photo** (fond `--pin` plein) | — | — | `#16241C` | **14,23:1** | ✓ AAA |

Plage du composant : **[5,71:1 ; 15,72:1]**. Le plancher est le pixel blanc au bord haut du texte, et
il est **structurel** : aucune photo, aucune longueur d'accroche, aucun zoom ne peut le faire
descendre. **Ces chiffres sont à re-mesurer sur le rendu réel** (pipette + calcul WCAG sur une photo
volontairement surexposée), pas à recopier d'ici.

## 7. La pleine largeur — refus motivé, repli acté

Diagnostic confirmé par les deux plans : `core/post-content` émet son propre conteneur, donc **tout
bloc inséré par l'éditrice est petit-fils de `.mtb-canal`**, alors que `base.css:515` ne place que les
**enfants directs**. Le bandeau est plafonné au **canal texte (576 px ; 324 px à 360 px)**, et
`alignwide`/`alignfull` n'y changent rien.

`leaddev-front-mtb` a démontré — et non supposé — que **aucune expression bornée en pourcentages ne
peut élargir le bandeau sans provoquer un défilement horizontal** : `100 %` du parent vaut
`min(100 % − 2·--marge-page, 36rem)`, donc une constante de 576 px dès qu'il existe du mou latéral,
qui ne transporte aucune information sur la largeur de fenêtre — or le mou vaut zéro à 360 px.

Une requête média **en pixels** donnerait une solution prouvée sans débordement (`min(0px,
calc((1024px − 42rem) / −2))` sous `@media (min-width: 1024px)`). **Elle est refusée**, pour une raison
qui n'est pas la mienne : elle produit une largeur **constante de 928 px** pour toutes les fenêtres
≥ 1024 px, soit une **largeur fixe hors des trois canaux** de la liste close de MASTER §7.1 et
au-delà du plafond de §13. On achèterait 350 px de photo contre deux infractions au système de design.

**Repli retenu, et il est explicite : le bandeau rend à la largeur du canal où il est posé**, sans une
seule règle de largeur dans sa feuille. Il devient pleine fenêtre le jour où le canal est réparé,
**sans aucune modification du bloc ni de sa feuille**. Refusé également : la ligne globale
`overflow-x: clip` sur `.wp-site-blocks` que le `100vw` aurait exigée — une règle globale appendue au
seul fichier partagé, pendant six chaînes concurrentes, pour acheter de la largeur, alors que la
contrainte qu'elle met en jeu (360 px sans défilement horizontal) est bloquante.

**Ce que ce repli coûte vraiment, chiffré par le front, à savoir avant de montrer une capture** :
`--r-bandeau` est piloté par la largeur de **fenêtre**, pas par celle du bandeau. À partir de 64 rem,
un bandeau de 576 px prend le ratio `21/9` → **247 px de haut**, alors que titre (`--t-3xl`, deux
lignes) + accroche + `--e-6` réclament ~235 px. **Sur grand écran, le texte occupe donc presque toute
la bande** et un pied `--pin` apparaît souvent sous la photo. Ce n'est pas cassé — le contraste y est
prouvé à 14,23:1 — mais « ça a l'air fini » tient **parce que le pied `--pin` est un état dessiné**,
pas parce que la géométrie est bonne. **Aucune source de ratio concurrente n'est introduite** : écrire
`16/9` et `21/9` dans la feuille du bloc, à côté de `tokens.css`, ferait deux vérités pour un jeton et
serait un piège garanti pour les neuf composants suivants.

## 8. États spéciaux

| État | Émis par le serveur | Rendu par le thème |
|---|---|---|
| `bandeau_complet` | racine + `__photo` + `__texte` | bande photo au ratio `--r-bandeau`, cerne, voile continu ancré en bas, titre `--t-3xl` `--calcaire`, accroche `--t-md`, **filet double en bord bas** |
| `sans_photo` | racine + `--sans-photo` + `__texte`, **pas de `__photo`** | **bande de texte sur `--pin`**, `padding-block: --e-8` des deux côtés, **aucun voile** (rien à voiler, et sur `--pin` il serait inerte), filet double conservé — **14,23:1** |
| `sans_texte` | racine + `__photo`, **pas de `__texte`** | bande photo nue, cerne, filet double, **aucun voile**. *(Arbitrage : MASTER §6.5 est muet sur ce cas — voir §12.)* |
| `photo_introuvable` | racine + `--sans-photo` — pièce jointe supprimée, fichier non-image, ou `wp_get_attachment_image()` vide | **identique à `sans_photo`. Une pièce jointe supprimée ne laisse jamais un cadre vide.** |
| `image_qui_ne_charge_pas` | inchangé (le fichier existe en base, pas sur le disque) | `__photo` garde son ratio, fond `--calcaire-creux`, cerne, et le navigateur imprime le texte alternatif en `--texte-doux` `--t-sm` — **6,38:1**. Aucun pictogramme cassé. |
| `bandeau_vide` — ni photo, ni titre, ni titre de page, ni accroche | **chaîne vide côté public** : `render.php` rend `''`. Côté éditeur, `Placeholder` **avant** l'appel serveur | **rien** — le bloc n'est pas là. Aucune réserve, aucun trou (MASTER §9, règle transverse). C'est le quatrième état, celui que ni ma décision 7 ni ma décision 8 ne couvraient : une page sans titre aurait sinon rendu une **barre sombre vide**, c'est-à-dire précisément la page cassée que D12 interdit. |
| `page_protegee`, non déverrouillée | `render.php` **ne s'exécute pas** ; `core/post-title` est **conservé** (garde 3) | le formulaire natif porte la page, **un seul `<h1>`, celui du cœur**. La mise en forme du §9.5 reste à l'epic Gabarits |
| `bandeau_pas_premier` | balisage complet, `__titre` en **`<p>`** | rendu **visuellement identique** au `<h1>`. Le titre apparaît alors **deux fois à l'écran** → avertissement dans l'éditeur, voir ci-dessous |
| `donnee_absente` | **jamais par ce bloc** — un champ vide fait disparaître son élément, il n'affiche pas « Non renseigné ». Ce bloc porte de la prose, pas une fiche | rien à prévoir |
| `aucune_portee`, `parent_hors_elevage` | sans objet | sans objet |

**État vide côté éditeur** : `wp.components.Placeholder`, avec `className: 'mtb-bloc-vide'`. Le cœur
l'habille correctement dès aujourd'hui — l'état vide est donc **fini, pas provisoirement laid** — et
la classe donne son point d'accroche à l'apparence commune de MASTER §9.1 le jour où `editor.css`
s'ouvrira (dette T13). **`mtb-bloc-vide__nom` et `mtb-bloc-vide__phrase` ne sont pas émis** :
`Placeholder` possède son balisage interne.

**Le titre affiché deux fois** est le seul cas où un remplissage plausible produit un résultat
visiblement bancal. Trois pistes ont été pesées, la décision est **d'accepter le rendu et de
l'avertir dans l'éditeur** : l'accepter en silence lui ferait voir son titre deux fois sans
comprendre ; supprimer le repli quand le bandeau n'est pas premier ferait **disparaître** son titre à
cause d'un paragraphe vide placé au-dessus, sans le moindre indice — **un défaut muet est pire qu'un
défaut visible**. L'avertissement est un `wp.components.Notice` `warning` non fermable, alimenté par
l'arbre **vivant** de l'éditeur (`getBlockRootClientId( clientId ) === ''` et
`getBlockIndex( clientId ) === 0`), donc plus frais que la décision du serveur qui juge sur le contenu
enregistré. Deux vérités, mais l'une est un conseil et l'autre un rendu, et l'écart se referme au
premier enregistrement. **À écrire en commentaire.**

**Dans l'éditeur, la garde 2 rend toujours `false`** (la vue d'édition n'est pas une vue singulière) :
le titre du bandeau y est donc **toujours un `<p>`**. Aucun second `<h1>` n'est injecté dans le DOM de
l'éditeur, et la décision n'a **aucune conséquence visuelle** puisque le thème stylise la classe et
non la balise.

## 9. Le crochet générique `mtb-photo`

L'extension émet `mtb-photo` sur `__photo`, **en plus** du crochet d'élément. MASTER §6.2 l'écrit
littéralement (`.mtb-photo > img { object-fit: cover; object-position: var(--point-interet, 50% 38%) }`)
— ce n'est donc pas un nom inventé.

> **Arbitrage.** `leaddev-back-mtb` le proposait comme convention pour les dix composants ;
> `leaddev-front-mtb` constatait qu'aucun `.mtb-photo` n'existe dans le thème et refusait d'en
> dépendre. Les deux ont raison de leur côté. **Décision : l'extension l'émet, le thème n'en dépend
> pas.** Le thème habille `__photo` et `__image` nommément, comme prévu ; la classe générique est
> inerte aujourd'hui et devient le point d'accroche gratuit le jour où le traitement photo partagé
> trouve un domicile. Coût : onze octets. Sans cela, chacun des dix blocs devrait être rouvert.
> **Le domicile du traitement photo partagé est une dette**, pas une étape de cette issue.

## 10. Chaînes fournies par le serveur

**Le rendu public de ce bloc ne contient AUCUNE chaîne du serveur.** Il n'imprime que ce que Fabienne
a tapé, ou le titre de la page. Ni libellé, ni ponctuation de liaison, ni date formatée, ni « Non
renseigné ». **Le thème n'a donc aucune chaîne à imprimer et rien à composer.**

Toutes les chaînes du serveur vivent dans l'administration, aucune n'est visible du visiteur :

| Emplacement | Chaîne littérale |
|---|---|
| Catégorie de l'insérteur | **Mont Brabant** |
| Titre du bloc | **Bandeau d'ouverture** |
| Description du bloc | **Une photo en haut d'une page, avec le titre de la page et une phrase de présentation.** |
| Mots-clés | bandeau · photo · titre · accroche · ouverture |
| État vide, étiquette | **Bandeau d'ouverture** |
| État vide, phrase | **Ce bloc n'affiche rien tant qu'aucune photo n'est choisie et que la page n'a pas de titre.** |
| État vide, bouton | **Choisir une photo** |
| Section | **Photo** |
| Boutons | **Choisir une photo** · **Remplacer la photo** · **Retirer la photo** |
| Aide photo | **La description de la photo — celle que lisent les personnes aveugles — se saisit sur la photo elle-même, une seule fois, et sert partout où la photo apparaît.** |
| Section | **Titre et accroche** |
| Champ | **Titre du bandeau** — aide : **Laissez vide : le bandeau reprend alors le titre de la page.** |
| Champ | **Accroche** — aide : **Une phrase de présentation, sous le titre. Facultative.** |
| Avertissement de position | **Ce bandeau n'est pas le premier bloc de la page : le titre s'affichera deux fois. Déplacez-le tout en haut.** |
| Avertissement de fichier | **Le fichier choisi n'est pas une photo. Choisissez une photo.** |

Contrôlées contre MASTER §10 : forme de l'état vide conforme au §9.1, verbes d'action à l'infinitif
(§10.1), **aucun** mot de la liste interdite du §10.4 — ni `hero`, ni `média`, ni `image mise en
avant`, ni `alt`, ni `template`, ni `bloc réutilisable`, ni `slug`. **Aucune fonction i18n**, français
littéral (contrat #1 §7).

## 11. Le module `categorie-mtb`

Un seul fichier, un seul rôle. `add_filter( 'block_categories_all', …, 10, 2 )` à l'inclusion —
`block_categories_all` (WP ≥ 5.8), jamais `block_categories`, déprécié.

Entrée `array( 'slug' => 'mtb', 'title' => 'Mont Brabant', 'icon' => null )`, insérée **tout en haut**
par `array_unshift` : ses dix composants sont ce qu'elle insère ; « Texte », « Média », « Widgets » et
« Thème » sont le bruit. Elle ouvre l'insérteur et « Mont Brabant » est la première chose lue.

**Garde d'idempotence** : si une catégorie de slug `mtb` est déjà présente, rendre le tableau
inchangé. Coût nul, et protège du jour où une chaîne sœur enfreindra « livrée une seule fois ».

**Comment une sœur s'y raccroche** : elle écrit `"category": "mtb"` dans son `block.json`. **Rien
d'autre. Aucun fichier partagé, aucun index, zéro collision d'empreinte.** Elle ne redéclare pas le
filtre, ne réordonne pas les catégories, ne touche pas à ce dossier. **Ce dossier ne grossit jamais** :
pas de second filtre, pas d'icône, pas de restriction d'insérteur (`allowed_block_types_all` est
renvoyé à l'epic Gabarits par le contrat #2 ; l'y mettre en ferait un point de passage obligé pour dix
issues).

## 12. Dette T7 — le bloc appendu à `base.css`

Forme **append-only, en fin de fichier, jamais une ligne au-dessus.** Sélecteur **doublé** à (0,2,0) :
les styles globaux du cœur sont imprimés **en ligne dans le `<head>`, après nos feuilles**, l'ordre de
source ne peut donc pas gagner. Même dispositif qu'au lien d'évitement (`base.css:553`).

Ce que le cœur impose aujourd'hui, à battre : `:root :where(.wp-block-button__link)` (0,1,0) →
`background-color:#32373c`, `color:#fff`, **`border-radius:9999px`** (une gélule, quand §13 plafonne
le rayon à 2 px), `padding:calc(.667em + 2px) calc(1.333em + 2px)` ; et
`:where(.wp-block-button__link){text-decoration:none}` (0,0,0) qui **perd** contre `a` (0,0,1) — d'où
le soulignement laiton constaté sur le bouton du cœur.

| # | Sélecteur | Contenu |
|---|---|---|
| 1 | `.wp-block-button__link.wp-block-button__link, .wp-element-button.wp-element-button` | `inline-flex` + centrage · `min-block-size: 48px` · `padding-inline: --e-4` · `padding-block: --e-2` · `border: 0` · `border-radius: --r-1` · `background-color: --sauge` · `color: --calcaire` · `font-family: --sans` · `700` · `--t-sm` · `line-height: 1.2` · `uppercase` · `letter-spacing: .12em` · **`text-decoration: none`** · `cursor: pointer` |
| 2 | `…:hover` | `background-color: --pin` · **`color: --calcaire` obligatoire** — `a:hover` (0,1,1) pose `--pin`, soit pin sur pin, invisible · `box-shadow: inset 0 0 0 1px --laiton` |
| 3 | `…:active` | `transform: translateY(1px)` |
| 4 | `…:focus-visible`, **placée après le survol** | `box-shadow: 0 0 0 2px --calcaire`. À égalité de spécificité l'ordre tranche **en faveur du focus** : survolé **et** focalisé, on garde l'anneau complet et on perd le cerne de survol. La visibilité du focus est bloquante, le cerne est décoratif. |
| 5 | `…[aria-disabled="true"]` | `background-color: --calcaire-creux` · `color: --texte-doux` · `box-shadow: none` · `cursor: not-allowed` — **5,79:1, reste lisible**. Un `<a>` n'a pas d'attribut `disabled` : `aria-disabled` est le seul crochet possible. |
| 6 | variation **« Contour »** | remise à l'apparence de la règle 1 |

`min-block-size: 48px` couvre les 44 px de BRIEF §11 avec de la marge ; `inline-flex` + centrage sont
nécessaires parce qu'un `<a>` ne centre pas son libellé comme un `<button>`. Ce sont des mécaniques,
pas des valeurs de design.

**Les deux extensions de T7 proposées par le front sont ACCEPTÉES**, pour ~150 octets :

- **`.wp-element-button`** — le cœur le cible à (0,1,0), ce qui **bat** `button` (0,0,1) de
  `base.css:370`. Le bouton du bloc Recherche, donc celui de la page 404 et de la recherche, est
  **aujourd'hui** en `#32373c` en gélule malgré `base.css`. Même défaut, même famille, même ligne : le
  rogner laisserait en place exactement la couleur hors jetons que T7 existe pour tuer.
- **La neutralisation de la variation « Contour »** — ses règles du cœur pèsent (0,2,1) et **battent**
  le (0,2,0) : `background: transparent` + `color: currentColor`, ce qui donne avec l'encre
  `--calcaire` un **bouton calcaire sur calcaire, illisible, atteignable en un clic**. C'est un échec
  AA franc, pas une imperfection. Contrepartie honnête : la variation devient un réglage qui ne change
  rien, donc trompeur dans l'éditeur. **Un bouton trompeur mais lisible vaut mieux qu'un bouton
  honnête et invisible.** Le vrai correctif est de retirer la variation par `block_type_metadata` dans
  `functions.php` — hors empreinte → dette T14.

**Dette T7-bis, créée sciemment** : les déclarations des règles 1 à 5 **dupliquent** celles de
`base.css:370-410`. Le correctif propre serait d'ajouter les deux sélecteurs doublés aux quatre listes
existantes (diff de 4 lignes), impossible sous la consigne append-only pendant six chaînes
concurrentes. Risque en attendant : **une modification du bouton faite d'un seul côté passe
inaperçue.** À écrire **en commentaire au-dessus du bloc appendu**, pas seulement dans ce contrat.

## 13. Interdits — les deux frontières, énoncées sans ambiguïté

**Le thème ne doit jamais** : interroger la base (`WP_Query`, `get_post_meta`, `get_posts`,
`get_terms`, ou écrire `MTB\`) · **décider si le bandeau porte le `<h1>`** — l'unique juge est
`mtb_bandeau_ouverture_porte_le_titre()`, à ne pas reproduire même « juste pour vérifier » · **émettre
son propre `<h1>` sans avoir interrogé cette fonction** · **retirer `core/post-title` d'un gabarit** ·
composer une chaîne du domaine, reformater une date, un n° LOF, une cotation, un résultat de test ·
inventer un texte alternatif · styliser `h1` ou `p` **par la balise** dans le bandeau · attendre
`.alignfull`, un `__voile`, un `--point-interet` en ligne, ou un `style` venant de l'extension ·
écrire une règle CSS hors de ses deux fichiers · introduire une origine tierce, une police, une image
de design, une étape de construction ou un octet de JavaScript.

**L'extension ne doit jamais** : émettre une règle visuelle, un `style=""`, une propriété
personnalisée, une classe de mise en page · ajouter `editorStyle` à `block.json` pointant une feuille
du thème · injecter un style en ligne dans `render.php` — **les deux derniers sont nommément interdits
parce qu'ils sont exactement ce qu'une chaîne pressée ferait si la vérification (b) du §14 échoue.
C'est une dette à déclarer, pas un contournement à écrire.**

**Assainissement et échappement.** Ni `sanitize_text_field`, ni `wp_strip_all_tags`, ni `wp_kses`, ni
plafond de caractères, ni troncature, ni `wptexturize`, ni `nl2br` (décision 20, MASTER §9.4) : un
titre commençant par `<` serait vidé **en silence** par l'outillage. `esc_html()` au rendu sur `titre`
et `accroche`, `get_block_wrapper_attributes()` pour la racine, `wp_get_attachment_image()` pour
l'image entière. `render.php` recaste `(int)` et `(string)` sans faire confiance au schéma, parce que
`do_blocks()` peut aussi tourner sur du contenu importé par la reprise (#19-#21).

**Le repli sur le titre de la page lit `post_title` brut, jamais `get_the_title()`** :
`get_the_title()` applique le filtre `the_title`, qui **préfixe « Privé : » ou « Protégé : »** — un
mot du cœur inséré dans un `<h1>` sans qu'on l'ait décidé — et `wptexturize`, qui convertit `1x2` en
`1×2`. Un seul régime pour les deux chemins, celui qu'elle a tapé comme celui du titre de la page :
**brut, échappé, rien d'autre.**

**Un seul hook exposé** : `mtb_bandeau_ouverture_attributs_image` — filtre,
`( array $attributs, int $id_photo, int $post_id ): array`. Échappatoire pour ajuster `sizes`,
`loading`, `fetchpriority` ou `class` sans toucher aux fichiers de l'autre côté. Ne peut ni retirer
`alt`, ni changer la photo. **Aucun filtre sur le balisage** : les crochets de classes sont
l'interface, et un filtre de balisage rendrait ce contrat non vérifiable.

## 14. Deux vérifications à constater, jamais à supposer

Le contrat #1 §311 demande à **la première issue de composants** — celle-ci — de constater et de
**rapporter**, pas de contourner.

**(a) Un bloc enregistré côté PHP seul apparaît-il dans l'insérteur ?** Protocole, entièrement dans
l'empreinte : bloc complet et fonctionnel, puis **renommer `editeur.js` en `_editeur.js`** (fichier à
nous, préfixe `_` déjà la convention de désactivation du chargeur), recharger l'éditeur sous
`fabienne`, chercher « Bandeau » dans l'insérteur, **consigner** ce qui est listé, ce qui est
insérable, ce que dit la console, et ce que devient un bandeau déjà présent. Corroborer côté serveur
par `/wp-json/wp/v2/block-types/mtb/bandeau-ouverture`. Renommer en sens inverse. **La conception ne
change pas quel que soit le résultat** — la poignée enregistrée à la main est de toute façon la bonne
solution.

**(b) Une feuille servie par `wp_enqueue_block_style` atteint-elle l'iframe de l'éditeur ?** Le front
prédit **non** (`_wp_get_iframed_editor_assets()` compose sa liste à partir des `style_handles` des
types de blocs, que `wp_enqueue_block_style()` n'alimente pas). Trois contrôles, du moins cher au plus
sûr : (1) `style_handles` du bloc enregistré contient-il `mtb-bloc-mtb-bandeau-ouverture` ? (2) le
témoin côté site — `curl … | grep -oE "mtb-bloc-mtb-bandeau-ouverture(-inline)?-css"`, attendu une
occurrence, en `<link>` **ou en ligne** (`path` autorise le cœur à inliner) ; (3) dans le navigateur,
`document.querySelector('iframe[name="editor-canvas"]').contentDocument.querySelectorAll(…).length` —
avec `apiVersion: 3` la toile **est** iframée, interroger `document` donnerait une réponse fausse.

**Mode de défaillance, mesuré et non bloquant** : `tokens.css` et `base.css` **sont** dans la toile.
Sans la feuille de bloc, l'aperçu montre la photo à sa hauteur naturelle puis le titre en `h1`
ordinaire sur fond calcaire — **structurellement juste, visuellement faux, parfaitement lisible et
modifiable**. L'éditrice n'est pas bloquée, elle est mal informée. **Si (b) échoue : rien à écrire.**
C'est la dette T11, correctif dans `functions.php`, hors empreinte.

## 15. Arbitrages — chaque désaccord entre les deux plans, la décision, sa raison

| # | Désaccord | Décision | Raison |
|---|---|---|---|
| 1 | Le back émet `--sans-texte` ; le front refuse de le recevoir | **Ne pas l'émettre.** Un seul modificateur : `--sans-photo` | Le voile vit sur `__texte` : son absence produit déjà le rendu voulu. Aucune règle ne cible `--sans-texte` — c'est une classe morte, donc un nom que l'une des neuf chaînes sœurs orthographiera mal un jour. Règle générale gelée : **un modificateur ne s'émet que si une règle le cible.** |
| 2 | Le back propose `mtb-photo` comme convention des dix composants ; le front constate qu'aucun `.mtb-photo` n'existe et refuse d'en dépendre | **L'extension l'émet, le thème n'en dépend pas** | MASTER §6.2 écrit littéralement ce crochet — il n'est pas inventé. Onze octets aujourd'hui inertes évitent de rouvrir dix blocs le jour où le traitement photo partagé trouve un domicile. Le thème habille `__photo`/`__image` nommément, donc rien ne casse dans l'intervalle. |
| 3 | État vide : le back veut `wp.components.Placeholder`, le front attend les crochets `mtb-bloc-vide__nom` / `__phrase` de MASTER §9.1 | **`Placeholder` avec `className: 'mtb-bloc-vide'`**, sans les sous-crochets | Le cœur habille `Placeholder` dès aujourd'hui : l'état vide est **fini**, pas provisoirement laid, et il ne demande aucune règle dans `editor.css` (hors empreinte, partagé avec cinq chaînes). La classe donne son point d'accroche à l'apparence commune du §9.1 quand elle viendra. Un état vide écrit à la main serait non habillé jusqu'à ce que T13 soit payée. |
| 4 | `allowedFormats: []` exigé par le front | **Plus fort : champs de texte simple, aucun `RichText`** | `TextControl` / `TextareaControl` rendent le formatage inexistant, non seulement interdit. La raison du front — un lien sur le voile mesure **4,09:1** — est portée au contrat pour qu'une chaîne future ne « améliore » pas ces champs en `RichText`. |
| 5 | Garde « vue singulière » absente de ma décision initiale, ajoutée par le back | **Acceptée, et obligatoire** | Sans elle, sur une archive ou une recherche, le filtre efface le titre-lien de chaque entrée dont le contenu commence par un bandeau — alors que le bandeau n'y est pas rendu. On perdrait des liens de navigation **en silence**. |
| 6 | Voile porté par `__texte` : ratifié par le front, qui en corrige le coût | **Ratifié avec la correction** — pseudo-élément haut à `200 %` / `150 %` | Le dispositif seul crée une arête à 0,72 en travers de la photo. La correction ramène le saut à 6 %, sous le seuil perceptible, en réemployant le **même jeton** et des pourcentages **dérivés du palier 34 %** — aucune valeur de design inventée. |
| 7 | Pleine largeur | **Refusée sous toutes ses formes ; repli au canal, acté** | `100vw` exige une règle globale `overflow-x` dans le seul fichier partagé, pendant six chaînes, contre la contrainte AA la plus dure. La requête média en pixels est prouvée sans débordement mais fabrique une largeur fixe de 928 px **hors des trois canaux** (§7.1) et au-delà du plafond de §13. Le repli devient pleine fenêtre quand T12 sera payée, **sans toucher au bloc**. |
| 8 | Extensions de T7 (`.wp-element-button`, variation « Contour ») | **Les deux acceptées**, ~150 octets | Rogner laisserait en place une couleur hors jetons atteignable en un clic (le bouton de recherche de la page 404) et un **échec AA franc** (calcaire sur calcaire). C'est précisément ce que T7 existe pour tuer. |
| 9 | Forme de T7 : bloc appendu (déclarations dupliquées) ou sélecteurs ajoutés aux quatre listes existantes (4 lignes) | **Bloc appendu**, T7-bis déclarée | Consigne append-only contraignante : `base.css` est partagé et six chaînes tournent. La dette est nommée, avec son risque écrit **en commentaire dans le fichier**. |
| 10 | `sizes="100vw"` alors que le bandeau rend à 576 px | **Conservé** | Inscrire la largeur du canal dans l'extension graverait un fait de mise en page du thème, temporaire, dans le module serveur de dix composants. La frontière l'emporte sur les octets ; le filtre d'échappatoire existe pour le jour où T12 sera payée. |
| 11 | Rangement de `mtb_bandeau_ouverture_porte_le_titre()` hors de `includes/query/` | **Reste dans le module du bloc** | Ce n'est pas une lecture de contenu mais un prédicat de décision — même nature que l'exception `mtb_resultat_disciplines()` de la décision 16 — et le nom est unique, donc aucun ombrage (décision 19). |
| 12 | Quatrième état (ni photo, ni titre, ni accroche) absent de mes décisions 7 et 8, signalé par le front | **Rien côté public, `Placeholder` côté éditeur** | Une page sans titre aurait rendu une **barre sombre vide** — précisément la page cassée que D12 interdit. |
| 13 | « Photo sans aucun texte » : MASTER §6.5 est muet | **La bande photo se rend, sans texte, filet compris** | Lecture raisonnable, tranchée par moi faute de `lead-design-mtb` en cours d'exécution. **À faire ratifier** — c'est une décision d'orchestration, pas une ligne de MASTER. |
| 14 | Valeurs que MASTER ne chiffre pas : écart titre → accroche, interlettre du titre de bandeau | **`--e-4` et `-.015em` retenus** | Ni l'un ni l'autre n'invente : `--e-4` est une **sélection** dans l'échelle existante, `-.015em` une **recopie** de la ligne `h1` du §4.5, nécessaire à l'identité `<h1>`/`<p>`. Ratifications dues à MASTER §4.5, comme l'amendement 5 a ratifié les marges de `h1`. |

## 16. Dettes déclarées par cette issue

| # | Dette | Domicile hors empreinte | À payer par |
|---|---|---|---|
| **T7-bis** | Les déclarations du bouton existent **deux fois** dans `base.css`. Une modification faite d'un seul côté passe inaperçue. | `base.css` (partagé) | `refacto-mtb`, hors lot parallèle |
| **T11** | **Les feuilles de blocs n'atteignent PAS l'iframe de l'éditeur — constaté, plus prédit** : `style_handles` du bloc enregistré est **vide**, et les trois contrôles du §14 (b) donnent 0 occurrence dans la toile. Aperçu structurellement juste, visuellement faux, **pour les dix composants**. **La cause n'est pas celle qui était prédite, et `dev-back-mtb` l'a isolée par sonde** : `_wp_get_iframed_editor_assets()` déclenche bien `enqueue_block_assets` et le rappel s'exécute, mais la feuille déclare `'deps' => array( 'mtb-jetons' )` et **`mtb-jetons` n'est enregistré que sur `wp_enqueue_scripts`, qui ne se déclenche jamais en administration** : `WP_Styles::do_items()` ne peut pas résoudre la dépendance et **abandonne l'élément en silence**. Preuve : la même sonde, après un simple `wp_register_style( 'mtb-jetons', … )`, rend la feuille **présente** dans l'iframe. **Le correctif est donc d'une ligne** — enregistrer `mtb-jetons` aussi en administration — et non le reversement dans `add_editor_style()` que je recommandais. Un correctif pour les dix composants. | `functions.php:194-224` | epic Gabarits (#16-#18) |
| **T12** | **Le canal plafonne tout composant à 576 px** : `core/post-content` émet son conteneur, chaque bloc inséré est **petit-fils** de `.mtb-canal`, `base.css:515` ne place que les enfants directs, `alignwide`/`alignfull` sont inatteignables. Frappe **à l'identique** #7, #8, la grille de chiens, la liste de portées et les tableaux de résultats, que MASTER place au canal **large**. Correctif purement de gabarit : `<!-- wp:post-content {"className":"mtb-canal alignfull"} /-->`, en gardant `#contenu` sur `main` — **sans toucher au CSS ni aux blocs**. | `templates/singular.html` | **epic Gabarits (#16/#17)** |
| **T13** | L'**apparence commune d'état vide** de MASTER §9.1 (contour tireté laiton, `--calcaire-creux`, étiquette + phrase) n'a aucun domicile : §9.1 la réserve à `editor.css`, hors empreinte et commun aux dix composants. Le crochet `mtb-bloc-vide` est posé, la règle non écrite. | `editor.css` | première issue qui rouvre `editor.css` (idéalement avec T11) |
| **T14** | La variation **« Contour »** du bouton n'a aucune définition dans MASTER §8.4, qui ne définit **qu'un** bouton. Neutralisée ici vers l'apparence unique, ce qui la rend **trompeuse** dans l'éditeur. Correctif propre : la retirer par `block_type_metadata`. | `functions.php:142-174` | issue `design`, après réponse de `lead-design-mtb` |
| **T15** | Aucune taille d'image dédiée : `srcset` repose sur les tailles du cœur. Suffisant, non optimal — et sur-dimensionné tant que T12 n'est pas payée (`sizes="100vw"` pour un bandeau de 576 px). | `functions.php` (`add_image_size`) | epic Gabarits |
| **T16** | Le panneau de fichier du cœur nomme le texte alternatif **« Texte alternatif »**, là où MASTER §10.2 fige **« Description de la photo (pour les personnes aveugles) »**. Contre-mesure en place : l'aide du bloc et la fiche emploient la formulation de MASTER. Correctif éventuel : filtre `gettext` strictement borné. | `includes/admin/` | issue `doc`/admin |
| **T17** | **Où vit le « Cadrage de la photo »** (MASTER §6.2, liste fermée de cinq, « seul réglage photographique laissé à l'éditrice ») : sur la photo une fois pour toutes, ou sur le bloc par usage ? Non livré ici — l'approche retenue arrête trois réglages — donc **tous les bandeaux sont cadrés au défaut `50% 38%`** et une photo dont le sujet est bas sera rognée sans recours. **À trancher avant le troisième composant qui affiche une photo**, sinon trois blocs répondront différemment. Voie conforme le jour venu : `data-cadrage="…"` sur `__photo`, cinq règles, jamais un `style=""` émis par l'extension. | — | avant le 3ᵉ composant photo |

## 17. Recette — ce qui doit être constaté, non affirmé

- **Un seul `<h1>`** : `curl … | grep -c "<h1"` → **1**, sur **chaque ligne** de la table du §8, y
  compris page protégée, page sans titre, bandeau non premier, bandeau dans un Groupe.
- **Identité `<h1>` / `<p>`** : diff de `getComputedStyle()` sur `.mtb-bandeau-ouverture__titre` entre
  deux pages. **Toute propriété égale.**
- **Contraste** : pipette + calcul WCAG au bord haut et au bord bas du bloc de texte, sur une photo
  volontairement surexposée. Attendus **5,71:1** et **9,18:1**.
- **360 px sans défilement horizontal** : page servie en **HTTP** (jamais `file://`), chargée dans une
  **iframe de 360 × 800** — Chrome sans interface ne descend pas sous ~500 px et la capture mentirait.
  Assertions : `documentElement.scrollWidth <= 360`, aucun élément de `.mtb-bandeau-ouverture *` dont
  `getBoundingClientRect().right > 360` ni `.left < 0`.
- **Zoom 200 %, deux mesures distinctes** : (a) fenêtre divisée par deux, assertion
  `__texte.scrollHeight <= __texte.clientHeight + 1` ; (b) `documentElement.style.fontSize = '32px'`,
  qui attrape les pièges `rem`/`px` que le zoom navigateur masque — notamment `32rem` sur la photo.
- **Parcours de la table des états ligne par ligne, dans la pile, sous le compte `fabienne`** — y
  compris supprimer la pièce jointe depuis Médias et recharger, forcer un `photo` pointant un PDF, et
  protéger la page par mot de passe.
- **Bouton** : sous `fabienne`, insérer un bloc Bouton, vérifier repos / survol / actif / focus
  clavier / `aria-disabled` / variation Contour, et confirmer par `getComputedStyle` que ni
  `#32373c`, ni `#fff`, ni `border-radius: 9999px` ne subsiste.
- **Poids** : `wc -c` sur les deux fichiers, chiffres rapportés, jamais recopiés d'une estimation.
- **Zéro origine tierce** : `curl` de la page publique, aucune requête sortante ajoutée.

## 18. Ce que cette issue ne fait pas — à ne pas laisser croire

- **Elle ne solde pas T10.** Le bandeau n'affiche aucune donnée d'élevage ; le thème n'appelle toujours
  aucune fonction `mtb_get_*`.
- **Elle ne satisfait pas BRIEF §8.** Le volet exclusion (sitemap, recherche) reste la dette T8, à
  l'issue #23.
- **Elle ne livre pas le réglage de cadrage** de MASTER §6.2 (T17).
- **La variante photographique est écrite mais non observée sur une vraie image** :
  `docs/migration/source/` est vide, aucune photo n'est importée, et MASTER §11.4 signale déjà que le
  `21/9` est « le ratio le plus risqué du système ». **Livrer sans l'avoir vu est acceptable ; le
  déclarer vérifié ne l'est pas.**

---

# Amendements — 2026-08-17, reprise de la chaîne #6

La chaîne #6 a été interrompue avant le commit. À la reprise, le lot lui confie **trois biens
partagés** que le corps du contrat déclarait hors empreinte, et dont **cinq chaînes sœurs dépendent** :
`editor.css`, le correctif de `functions.php`, et le canal de `templates/singular.html`. L'empreinte
d'écriture est **élargie en conséquence**, et trois dettes déclarées au §16 sont **payées ici**.

Ce qui suit **remplace** les passages du corps qu'il nomme. Le reste du contrat est inchangé.

## Empreinte d'écriture révisée

| Fichier | Écrit par | Statut |
|---|---|---|
| `plugins/mtb-core/includes/blocks/bandeau-ouverture/**` | `dev-back-mtb` | inchangé |
| `plugins/mtb-core/includes/blocks/categorie-mtb/**` | — | **clos, vérifié par une sœur, ne plus toucher** |
| `themes/mtb/assets/css/blocs/mtb-bandeau-ouverture.css` | `dev-ux-mtb` | inchangé |
| `themes/mtb/assets/css/base.css` | — | **clos** — le bloc T7 est livré et accepté à cet endroit |
| `themes/mtb/assets/css/editor.css` | `dev-ux-mtb` | **ouvert — bien partagé, paie T13** |
| `themes/mtb/functions.php` | `dev-front-mtb` | **ouvert — correctif d'une ligne, paie T11** |
| `themes/mtb/templates/singular.html` | `dev-front-mtb` | **ouvert — paie T12** |
| `docs/contracts/issue-6.md` · `docs/guide/composant-bandeau-ouverture.md` | chaîne #6 | — |

`theme.json` reste **verrouillé et interdit**. `templates/index.html` reste **hors empreinte** : le
correctif de canal n'y est donc **pas** appliqué, et c'est une limite à rapporter, pas un oubli.

---

## Amendement 1 — L'état vide partagé : `editor.css` et ses trois crochets gelés

**Remplace l'arbitrage 3 du §15 et le dernier paragraphe du §8.** La raison de l'arbitrage 3 était que
`editor.css` n'avait aucun domicile ; elle a cessé d'être vraie. `wp.components.Placeholder` et la
classe `mtb-bloc-vide` sont **retirés**.

MASTER §9.1 déclare l'apparence de l'état vide **identique pour les dix composants du catalogue** et la
domicilie dans `editor.css`. Les crochets sont **gelés au niveau du lot** — aucune des six chaînes ne
les choisit :

| Crochet | Élément | Contenu |
|---|---|---|
| `mtb-etat-vide` | racine, un `div` | — |
| `mtb-etat-vide__nom` | un `span` | **le titre du bloc, en casse naturelle** |
| `mtb-etat-vide__phrase` | un `p` | la phrase « Ce bloc n'affiche rien tant que … » |

**Exactement deux éléments, jamais un troisième.** Le bouton « Choisir une photo » que la version
interrompue plaçait dans le `Placeholder` est **supprimé** : le §3 de ce contrat interdit déjà tout
second chemin vers la photo (« un seul chemin »), le panneau latéral est toujours présent, et un
troisième élément chez un seul des dix composants ferait de la feuille commune une négociation par
bloc. L'état vide est **littéralement le même** pour les six chaînes.

**Les capitales sont posées par la feuille, jamais tapées dans le JavaScript.** `editor.css` porte
`text-transform: uppercase` sur `__nom` ; le script émet « Bandeau d'ouverture ». Trois raisons, et
elles valent pour les cinq sœurs : une sœur recopie son titre de bloc sans risque de coquille ; les
accents des capitales françaises (« ENCART DERNIÈRE PORTÉE ») sont produits par le moteur et non par la
frappe ; et le texte reste lisible par un lecteur d'écran qui épellerait des capitales littérales.

**Encres — l'une est recalculée, l'autre est déjà dans MASTER.**

§9.1 écrit « étiquette laiton », et §3.1 écrit `--laiton` « **JAMAIS de texte** ». La seule lecture
conforme est `--laiton-texte`. Or §12.3 **ne tabule pas** cette paire.

| Paire | Ratio | Provenance |
|---|---|---|
| `--laiton-texte` `#7A5F2C` sur `--calcaire-creux` `#E7E5DA` | **4,75:1** — AA texte normal | **recalculé ici** avec la formule du §3 : `L = 0,1250` et `L = 0,7810`, ratio `(0,7810 + 0,05) / (0,1250 + 0,05)` |
| `--texte-doux` sur `--calcaire-creux` | **5,79:1** — AA | §12.3, déjà tabulé |
| contour tireté `--laiton` sur `--calcaire-creux` | **3,15:1** | §12.3, « non textuel » — suffisant pour une bordure |

Le **4,75:1 est un amendement à faire ratifier par `lead-design-mtb`**, pas une valeur inventée : la
formule est celle du §3, le calcul a été refait indépendamment, et il complète une ligne manquante du
§12.3. Il est écrit **en commentaire dans `editor.css`**, avec sa méthode.

**Apparence** — strictement §9.1, aucun réglage libre : contour tireté 1 px `--laiton`, rayon `--r-0`,
fond `--calcaire-creux`, `padding: --e-6`, texte `--t-sm` `--texte-doux`, aligné à gauche. Deux
mécaniques et non des valeurs de design : `display: block` sur `__nom` (un `span` est en ligne) et
remise à zéro des marges de `__phrase` (la toile préfixe les règles `p` de `base.css`, qui pèsent alors
(0,1,1)). Aucune règle propre au bandeau : **la feuille ne nomme aucun bloc.**

**Phrase du bandeau, littérale et close** :

> **Ce bloc n'affiche rien tant qu'aucune photo n'est choisie et que la page n'a pas de titre.**

*Arbitrage.* Le corps de l'issue #6 écrit « tant qu'aucune image ni titre n'est renseigné ». Le mot
retenu est **photo**, pas *image* : la consigne du lot veut le mot exact du panneau de réglages, et le
panneau dit « Photo » et « Choisir une photo » (§10), graphie de MASTER §10.2. La phrase est **une
seule** phrase, au présent, terminée par un point, sans « vous » et sans rien ajouter.

**Rappel qui ne change pas** : côté public, un bandeau sans rien **n'est pas rendu du tout** (§8,
`bandeau_vide`). Et le cas partiel — photo absente, titre présent — **n'est pas un état vide** : c'est
la bande de texte sur `--pin` de MASTER §6.5, publiquement visible, à 14,23:1.

**T13 est payée.**

## Amendement 2 — `functions.php` : la dépendance qui abandonnait la feuille en silence

**Remplace la dette T11 du §16, dont le diagnostic est confirmé et le correctif retenu.**

La cause a été **mesurée** dans le cœur 6.9, pas déduite : `functions.php:218` déclare chaque feuille de
bloc avec `'deps' => array( 'mtb-jetons' )`, `mtb-jetons` n'est enregistré que sur
`wp_enqueue_scripts` — qui **ne se déclenche jamais en administration** — et `WP_Dependencies::all_deps()`
**abandonne alors l'élément entier, sans erreur ni avertissement**. Aucune des dix feuilles de blocs
n'atteint la toile.

**Correctif retenu : enregistrer `mtb-jetons` en administration**, en tête de `mtb_feuilles_de_blocs()`,
sous garde `is_admin()` et `wp_style_is( …, 'registered' )`, avec **exactement la même source et la même
version** que `mtb_mettre_feuille_en_file()` produit côté site.

Pourquoi celui-là plutôt que la suppression de `deps` — les deux étaient autorisés : retirer `deps`
marche aujourd'hui (les jetons entrent déjà dans la toile par `add_editor_style()`, et les feuilles de
blocs l'emportent par spécificité, jamais par l'ordre), mais il **retire une garantie** : la première
feuille sœur qui aurait besoin de l'ordre casserait **en silence**, exactement le mode de défaillance
que ce contrat existe pour fermer. L'enregistrement, lui, coûte une **copie de `tokens.css` dans la
toile de l'éditeur** — même fichier, octet pour octet, jamais servie au visiteur, impossible à faire
diverger. Un doublon documenté vaut mieux qu'une garantie retirée.

**Interdits sur ce correctif** : aucun `glob()`, `scandir()`, `opendir()` ni `DirectoryIterator`
(contrat #2, dette T5) · aucun `add_editor_style()` par-dessus le registre des blocs · aucune mise en
file, **un enregistrement seulement** · aucune autre ligne de `functions.php` touchée. C'est un
correctif **du mécanisme**, non une liste à étendre : `functions.php` **se referme** derrière lui
(décision 9).

**À constater dans la toile, jamais en relisant le code** : la feuille
`mtb-bloc-mtb-bandeau-ouverture` présente dans `iframe[name="editor-canvas"]`. **T11 est payée.**

## Amendement 3 — `templates/singular.html` : le canal rendu traversant

**Remplace le §7 (« refus motivé, repli acté ») et la dette T12 du §16. Le repli n'a plus lieu d'être.**

Diagnostic, **mesuré** : `wp:post-content` émet `div.wp-block-post-content` en enfant direct de
`.mtb-canal` ; `base.css:517` place les enfants **directs** dans le canal texte de 36 rem, si bien que
tout bloc inséré est **petit-fils** et que `.mtb-canal > .alignwide` (`base.css:522`) comme
`.mtb-canal > .alignfull` (`base.css:534`) ne l'atteignent jamais. Imbriqué : **576 px / 3 colonnes** ;
enfant direct : **992 px / 6 colonnes**.

**Correctif retenu, purement de gabarit, zéro ligne de CSS** :

```
<!-- wp:post-content {"className":"mtb-canal alignfull"} /-->
```

L'enveloppe devient **elle-même** la grille à trois canaux, posée sur la piste `pleine` de la grille
extérieure : les blocs insérés redeviennent des **enfants directs** d'un `.mtb-canal`, et les trois
canaux du §7.1 leur sont acquis sans qu'une seule largeur soit fabriquée. `#contenu` reste sur `main`,
l'ordre de lecture est inchangé, `wp:post-title` reste dans le canal texte.

Ce que le correctif **n'emploie pas**, et c'est la moitié de sa valeur : **aucune unité de fenêtre,
aucun `100vw`, aucune règle `overflow-x` globale** — la chaîne précédente avait raison de refuser ce
troc, qui met en jeu la contrainte AA bloquante des 360 px. **Aucun `display: contents`** non plus : la
décision 8 l'a écarté sur le menu, et il est ici inutile. Le `subgrid` a été écarté pour une raison de
lot, non de technique : il demanderait une règle dans `base.css`, **seul fichier partagé**, pendant six
chaînes concurrentes, pour un résultat que le gabarit obtient seul.

**Conséquence sur le bandeau, qui rend le §7 caduc.** La pleine largeur redevient atteignable, et
l'obligation 1 du §5 — « le débordement pleine largeur est **entièrement à la charge du thème**, sur
`.mtb-bandeau-ouverture` » — devient payable. Une règle, dans la feuille du bloc, **dans son domicile
prévu** :

```
.mtb-canal > .mtb-bandeau-ouverture { grid-column: pleine-debut / pleine-fin; }
```

**L'extension reste inchangée** : elle n'émet toujours ni `alignfull`, ni `alignwide`, ni aucune classe
de mise en page (§13). Le bandeau retrouve la pleine largeur de fenêtre de MASTER §6.5 **sans qu'une
ligne du module serveur bouge** — ce que le §7 annonçait, par le chemin qu'il n'avait pas.

Les conséquences chiffrées au §7 (`--r-bandeau` piloté par la fenêtre, texte occupant presque toute la
bande à 576 px, pied `--pin` fréquent sur grand écran) **tombent avec le repli** : le bandeau prend
désormais la largeur de la fenêtre, pour laquelle les trois ratios du §6.5 ont été conçus.

**À constater rendu dans la pile Docker, jamais affirmé** : 360 px sans défilement horizontal (page
servie en HTTP, dans une iframe de 360 × 800 — Chrome sans interface ne descend pas sous ~500 px et la
capture mentirait), zoom 200 % par les deux mesures du §17, et jusqu'à 2560 px. **T12 est payée pour
`singular.html` uniquement** ; `templates/index.html` garde le défaut, hors empreinte.

## Amendement 4 — Nom de la fiche d'aide

`docs/guide/composant-bandeau-ouverture.md`. Convention imposée aux six chaînes du lot,
`composant-<nom-du-bloc>.md`, pour que la vue d'ensemble de #25 compose sur dix noms réguliers. Le
contenu est conservé ; seul le nom change, et toute référence au nom précédent avec lui.

## Amendement 5 — Classes partagées nues

`.mtb-dispo` (§3.3) et `.mtb-photo` (§6.2) sont nommées par MASTER et émises par plusieurs composants.
**Doctrine du lot : nues, jamais portées sous une classe de bloc**, pour être hissées un jour dans une
feuille commune sans renommage. Elles **ne sont définies ni dans `base.css` ni dans `editor.css`** dans
ce lot : ce hissage est une dette portée au niveau du lot. L'arbitrage 2 du §15 est confirmé —
l'extension émet `mtb-photo`, le thème n'en dépend pas.

## Ce que ces amendements ne font pas

- Ils **ne rouvrent ni `base.css` ni `categorie-mtb/`** : les deux sont clos et vérifiés.
- Ils **ne paient ni T14, ni T15, ni T16, ni T17**, ni la dette de hissage de l'amendement 5.
- Ils **ne corrigent pas `templates/index.html`** : accueil du blog, archives et recherche gardent le
  canal plafonné.
- Ils **ne rendent pas la variante photographique observée sur une vraie image** : la réserve du §18
  tient entière.

## Amendement 6 — `base.css` rouvert pour UNE déclaration : la piste large

L'Amendement révisé déclarait `base.css` **clos**. Il se rouvre pour **une seule déclaration**, en fin
de fichier, et la raison est que l'Amendement 3 a rendu visible un défaut qui dormait.

**Mesuré au navigateur par `dev-front-mtb`, pas déduit** : `.mtb-canal > .alignwide` (`base.css:522`)
porte `margin-inline: auto`. Sur un **élément de grille**, une marge automatique **annule
l'étirement** : l'élément est dimensionné en `fit-content` au lieu d'occuper sa piste. À 1280 px, un
groupe `alignwide` à texte court mesure **171 px** là où **1088 px** sont attendus ; `gridColumn` vaut
bien `large-debut / large-fin` et la piste fait bien 1184 px — **seule la largeur employée est fausse**.

Ce n'est pas une régression de l'Amendement 3 : le défaut existait déjà, et **rien ne pouvait
l'atteindre** tant que les blocs insérés étaient petits-fils du canal. L'Amendement 3 l'a rendu
atteignable, donc visible, donc **à corriger par la chaîne qui l'a exposé**.

Pourquoi ce n'est pas une dette qu'on peut porter : MASTER §7.1 place au **canal large** la grille de
chiens, la liste de portées et les tableaux de résultats — **trois composants de chaînes sœurs de ce
lot**. Et « Largeur étendue » est offert à l'éleveuse **en un clic** : le laisser rendre un bloc *plus
étroit* qu'avant serait un réglage qui dégrade, c'est-à-dire la contrainte 1 enfreinte.

**Correctif, mesuré au navigateur avant d'être écrit** — `inline-size: 100%` donne **1088 px centré**
(constaté) ; `margin-inline: 0` donnerait 1088 px collé à `large-debut`. Le premier est retenu : il
**conserve l'intention de centrage** que `base.css` écrit en commentaire, au lieu de la contredire.

```
.mtb-canal > .alignwide { inline-size: 100%; }
```

Forme **append-only, en fin de fichier**, comme le bloc T7 : même sélecteur et même spécificité que
`base.css:522`, donc c'est **l'ordre de source dans la même feuille** qui tranche, et il joue en faveur
de la déclaration appendue. Aucun sélecteur doublé n'est nécessaire — on ne combat pas le cœur ici,
mais une déclaration à nous.

**Dette T18, créée sciemment** : comme T7-bis, la déclaration vit **loin** de la règle qu'elle complète.
Le correctif propre est d'ajouter `inline-size: 100%` à `base.css:522` (diff d'une ligne), impossible
sous la consigne append-only pendant six chaînes concurrentes. À écrire **en commentaire au-dessus du
bloc appendu**, pas seulement ici.

**Ce qui N'EST PAS corrigé, et n'a pas à l'être** : `alignleft` / `alignright` ne flottent plus (un
flottement est inopérant sur un élément de grille) ; une image `alignleft` s'étire désormais à la
largeur du canal texte. **MASTER §6.8 l'écrit littéralement** : « Une image **jamais** flottée dans le
texte (`float`), jamais habillée », et il en donne la raison — les 360 px sans défilement horizontal.
Le comportement d'avant était donc une **infraction au §6.8** que la grille neutralise. Reste un
réglage **trompeur** dans l'éditeur, même famille que la variation « Contour » : **dette T19**,
correctif propre par `block_type_metadata`, hors de l'empreinte d'une chaîne de composant.

---

## Amendement 7 — Le cerne du §6.6 : deux fautes, dont une invisible

**Ajouté le 2026-08-18**, sur défaut **HIGH** de la revue de lot. Ne remplace rien : complète
l'amendement 5, dont il tire la conséquence que personne n'avait écrite. Empreinte inchangée —
`mtb-bandeau-ouverture.css` et ce fichier.

### Faute 1 — une déclaration conforme en apparence, inerte en fait

La feuille portait `box-shadow: var(--cerne-photo)` **sur le cadre `__photo`**. `--cerne-photo` est une
ombre `inset` (`tokens.css:118`) ; une ombre intérieure se peint au-dessus du fond de son élément mais
**sous ses descendants**, et l'`img` remplit exactement la boîte du cadre. **Le cerne existait sur le
cadre vide et disparaissait à la seconde où la photo chargeait.** Mesuré : `__photo` et `__image`
partagent le même rectangle au centième de pixel près (`0 / 32 / 1194.662 / 511.988`).

C'est la faute la plus coûteuse du système, parce qu'elle **relit juste** : le §6.6 est cité, le jeton
est le bon, la ligne passe toute revue de source. Seul le navigateur la contredit. Les cinq composants
sœurs l'avaient déjà rencontrée et résolue ; `mtb-grille-chiens.css:221` l'interdit même par son nom.

**Recette gelée pour tout composant qui affiche une photo** — le cerne se porte sur un pseudo-élément,
jamais sur le cadre, jamais sur l'`img` :

```css
.…__photo::after {
  content: ""; position: absolute; inset: 0;
  border-radius: inherit; box-shadow: var(--cerne-photo); pointer-events: none;
}
```

Le cadre prend `position: relative` pour lui servir de bloc conteneur. `pointer-events: none` n'est pas
décoratif : le pseudo couvre toute la photo et intercepterait sinon les clics.

### Faute 2 — une apparence qui dépendait des voisins

`render.php:150` émet `class="mtb-bandeau-ouverture__photo mtb-photo"`, et l'amendement 5 laisse
`.mtb-photo` **nue, définie dans les feuilles des composants #12 et #13**. Or
`wp_enqueue_block_style` ne sert une feuille de bloc **que si ce bloc rend sur la page**.

**Conséquence, mesurée, que l'amendement 5 n'avait pas tirée** : le bandeau recevait `position:
relative`, le `::after` et le cerne **par contamination**, seulement quand un composant sœur
l'accompagnait.

| Page mesurée | `__photo` `position` | `::after` `content` | Cerne |
|---|---|---|---|
| `/essai-6-a-…/` — bandeau seul | `static` | `none` | **absent** |
| `/ti3-les-six/` — six composants | `relative` | `""` | présent |

**La recette de lot mesurait la seconde page.** C'est ainsi que le défaut a traversé l'intégration :
la page de contrôle la plus riche est la moins révélatrice, parce qu'elle est la seule où toutes les
feuilles sont servies.

### Règle que ce contrat gèle pour les dix composants

> **Une feuille de bloc doit suffire à son bloc.** Aucun composant ne tire son apparence d'une classe
> partagée tant que cette classe vit dans une feuille servie conditionnellement. Les déclarations sont
> **recopiées à l'identique** sous le crochet du bloc — donc la fusion reste sans effet quand plusieurs
> feuilles se rencontrent, et le hissage de l'amendement 5 restera une **suppression**, jamais un
> arbitrage.
>
> **Toute recette visuelle se constate sur une page ne portant QUE le composant visé.** Une page
> multi-composants ne prouve rien sur l'autonomie d'une feuille.

Appliqué ici : `__photo` déclare sous son propre crochet `background-color`, `color`, `font-size`,
`position` et le `::after` — mêmes jetons, mêmes valeurs que les sœurs.

### Vérifié au navigateur, WordPress 6.9, port 3005

Après correctif, **toutes** les propriétés mesurées du cadre, de son `::after`, de l'`img` et des deux
rectangles sont **identiques** entre `/essai-6-a-…/` (feuille du bandeau seule) et `/ti3-les-six/`
(cinq feuilles sœurs en plus) : cerne `rgba(22, 36, 28, 0.22) 0px 0px 0px 1px inset` sur le `::after`,
`box-shadow: none` sur le cadre — donc aucun doublement. Géométrie inchangée par rapport à l'état
fautif : le correctif ne déplace aucun pixel. `elementFromPoint` sur la photo rend l'`img` ou
`__texte`, **jamais le `::after`**. À 360 px : ratio `4 / 3`, aucun défilement horizontal, aucun
rognage du titre, cerne présent.

### Le §6.6 n'admet pas d'exception ici, et c'est une décision

La question a été posée : sur un bandeau pleine fenêtre il n'y a pas de fond calcaire autour de la
photo, donc la justification du §6.6 (« le bord de l'image existe toujours contre le fond calcaire »)
pourrait ne pas porter. **Le cerne est maintenu.** §6.6 range le cerne parmi les comportements
**obligatoires** et l'écrit « sur **toute** photo » ; §6.5 décrit le bandeau en détail et **n'écrit
aucune exception**. Le bord haut de la photo rencontre l'en-tête et son bord bas le filet double sur
`--pin` : il y a bien des bords à tenir. Surtout, **une exception doit être écrite** par
`lead-design-mtb` — jamais obtenue par une règle inerte, qui donne le pire des deux : ni le cerne, ni
la trace de la décision de s'en passer.
