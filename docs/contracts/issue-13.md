# Contrat d'interface — Issue #13 — Composant « Liste de portées »

**Gelé le 2026-08-17.** Premier contrat de composant du projet, et **premier acompte réel sur la
dette T10** : jusqu'ici aucune ligne du site public n'appelait une fonction `mtb_get_*`.

Il s'applique **par-dessus** `docs/contracts/issue-1.md` (règle générale de tout module de `mtb-core`)
et **consomme** `docs/contracts/issue-3.md` (type de contenu Portée et ses six fonctions de lecture),
qu'il n'amende ni ne contredit sur aucun point.

Les deux plans qui l'ont nourri — `leaddev-back-mtb` et `leaddev-front-mtb` — ont travaillé **sans se
voir**. Les divergences qu'ils ont produites sont tranchées en section 12, une par une, avec leur
raison. **Sur tout point où ce document diverge d'un des deux plans, ce document l'emporte.**

---

## 1. Ce que l'issue livre

Un **bloc rendu côté serveur**, `mtb/liste-portees`, que l'éleveuse insère dans une Page. Il affiche
les portées publiées, de la plus récemment née à la plus ancienne, avec deux réglages : **combien en
afficher** et **quelle année**. Chaque entrée renvoie vers la fiche de la portée.

**Empreinte fichiers, close :**

```
wp-content/plugins/mtb-core/includes/blocks/liste-portees/**
wp-content/themes/mtb/assets/css/blocs/mtb-liste-portees.css
docs/contracts/issue-13.md
docs/guide/composant-liste-de-portees.md
```

Le fichier de thème est **un ajout d'empreinte demandé et motivé** (section 3). Il n'y a **aucune**
autre écriture : ni `functions.php`, ni `theme.json`, ni `base.css`, ni `tokens.css`, ni `editor.css`,
ni `templates/`, ni `parts/`, ni `patterns/`, ni `mtb-core.php`, ni `class-loader.php`, ni
`includes/query/portee/`.

**Six chaînes tournent en parallèle dans le même arbre de travail** (#6, #7, #8, #12, #13, #14), sans
branche et sans isolation. Tout fichier hors de la liste ci-dessus appartient à une chaîne sœur.

---

## 2. Les trois arbitrages de fond, rendus avant les plans

Ils sont tranchés, motivés, et ne se re-litigent pas sans raison nouvelle.

### 2.1 La forme est une liste, pas une grille de cartes, pas un tableau

Retenue sur les quatre options de `brainstorm-mtb` : **une `<ul>` de lignes, vignette facultative**.

Le critère qui départage est **D12 lu sur les données réelles**. Les 27 portées vont de `L 1995` à
`A3 2025` et une part inconnue n'a ni photo, ni compteurs, peut-être pas de date ; les quatre fixtures
actuelles n'ont **aucune photo**. Une grille de cartes rendrait ces portées comme des rectangles vides
portant le nom de la portée (`MASTER.md` §9.2, emplacement structurant) — une rangée de trous. La
liste les rend comme des lignes légèrement plus courtes.

Coût assumé et nommé : `tokens.css:141` documente `--r-paysage: 3 / 2` comme « vignette de galerie,
**carte de portée** », et §7.1 range les listes de portées avec les galeries. **On n'emploie pas la
carte que le système de design semble avoir prévue.** En compensation, le balisage (`<li>` +
`<figure>` facultatif) rend le passage à une grille de cartes **purement CSS**, sans retoucher une
ligne de rendu, le jour où la reprise de contenu aura montré combien de portées ont une photo.

### 2.2 La décision 10 (`data-libelle`) ne s'applique pas à ce composant

**Ce n'est pas une entorse.** La décision 10 vise les composants **tabulaires** — son propre texte
nomme « résultats de travail, chiots d'une portée » — et le contrat #3 §14.3 la lie mécaniquement aux
chaînes de `chiots_colonnes`, « lues depuis la fonction, jamais réécrites ».

L'argument décisif est venu du plan back : **aucune fonction de lecture n'expose de libellés de
colonnes pour une liste de portées.** Il n'existe pas de `portees_colonnes()`. Poser un tableau ici
obligerait à écrire les en-têtes **à la main**, donc à composer un vocabulaire que `MASTER.md` §10.2
n'atteste pas — §10.2 note même « (badge) » côté public pour la disponibilité, donc **aucun libellé de
colonne publique**. Le non-tabulaire n'est pas seulement plus robuste : c'est la seule forme que le
vocabulaire gelé permette.

Conséquences : **aucun `data-libelle`, aucun `<table>`, aucun `<thead>`, aucune règle à 48 rem, aucun
conteneur à défilement.** Le repli à 360 px est gratuit — et doit être **mesuré**, pas affirmé
(section 11).

### 2.3 Le filtre par année est un réglage d'insertion, jamais un filtre visiteur

L'issue le range sous « **Réglages** ». Un filtre cliquable imposerait un argument d'URL
(`?annee=1998`) sur une Page dont l'adresse n'a pas été conçue pour ça : contenu variable, pages
minces indexées, canonique — tout ce qui appartient à #24 (`seo`) et à #16/#17. Et à 27 portées, la
liste entière tient dans un défilement : c'est un mécanisme pour un problème que le volume ne crée
pas.

Corollaires : **aucun JavaScript public**, aucun `<form>`, aucun argument d'URL lu. **Aucune
pagination** — l'argument `page` de `mtb_get_portees()` reste inutilisé ; le seul foyer correct d'un
index paginé est l'archive `/portees/`, servie par la requête principale du cœur (epic Gabarits).

---

## 3. Le CSS vit dans le thème — pourquoi, et pourquoi c'est sûr

L'énoncé initial de la chaîne demandait que « le bloc embarque ses propres styles dans son propre
dossier », c'est-à-dire dans `mtb-core`. **C'était une violation directe du contrat gelé #1 §8** :

> « L'extension n'émet aucune règle visuelle ni mise en page. Un bloc rend une structure et des
> crochets de classes — `mtb-<bloc>` et `mtb-<bloc>__<element>` — aucun style en ligne, aucune
> décision visuelle. Le thème habille. »

Et l'interdiction était **inutile**. `wp-content/themes/mtb/functions.php:194-224`
(`mtb_feuilles_de_blocs()`, sur `wp_loaded`) itère le **registre des blocs** — jamais le disque,
`glob()`/`scandir()` étant proscrits — dérive `assets/css/blocs/<espace>-<nom>.css` du nom du bloc, et
appelle `wp_enqueue_block_style()` si le fichier existe. Son propre commentaire : « Déposer
`assets/css/blocs/core-image.css` suffit à l'habiller : **rien à déclarer ici** ».

Donc : **un seul fichier neuf, zéro ligne ailleurs.** Poignée `mtb-bloc-mtb-liste-portees`,
`deps => array( 'mtb-jetons' )`, `ver = filemtime`, `path` fourni (le cœur peut l'écrire en ligne si
elle est courte).

**Le nom du fichier est dérivé du nom du bloc, donc la collision est structurellement impossible** :
aucune des cinq chaînes sœurs ne s'appelle `liste-portees`. `assets/css/blocs/` existe déjà et ne
contient qu'un `.gitkeep`. C'est ce que la décision 9 (« aucun index central à éditer à la main ») a
acheté.

---

## 4. Fonctions de lecture consommées

```php
mtb_get_portees( array $args = array() ): array      // toujours sous function_exists()
get_post_type_archive_link( 'mtb_portee' )           // peut renvoyer false
```

**Aucune fonction n'est déclarée par cette issue.** Aucun hook, aucun filtre, aucune action n'est
exposé. La surface est close : un bloc, deux attributs.

Appels effectifs, et eux seuls :

| Appel | Quand |
|---|---|
| `mtb_get_portees( array( 'nombre' => -1 ) )` | aucun filtre d'année |
| `mtb_get_portees( array( 'nombre' => -1, 'annee' => 'AAAA' ) )` | filtre d'année actif |
| `mtb_get_portees( array( 'nombre' => 1 ) )` | **sonde**, uniquement sur le chemin vide, pour distinguer « aucune portée pour cette année » de « rien n'est publié » |

`page`, `ordre`, `disponibilite`, `exclure` ne sont **jamais** passés.

**Pourquoi `nombre => -1` puis `array_slice()` au rendu, plutôt que passer `nombre` à la fonction** :
sans le total retenu, le bloc ne peut pas savoir qu'il tronque, donc ne peut pas décider d'afficher le
lien de sortie. Deux appels (l'un avec `nombre`, l'autre avec `-1`) hydrateraient 54 portées au lieu de
27. `array_slice()` n'est pas une requête et ne réimplémente rien : **la sélection et l'ordre restent
entièrement produits par la chaîne propriétaire** (décision 19), le bloc ne fait que couper la fin
d'une liste déjà triée.

### 4.1 Clés consommées, et rien d'autre

| Clé | Forme livrée par #3 | Traitement |
|---|---|---|
| `titre_public` | `string`, **jamais vide** — vaut « Portée » au minimum (`hydratation.php:250`) | texte du lien |
| `lien` | `string`, `''` si non consultable | `''` → pas de `<a>`, **jamais un lien mort** |
| `date_naissance` | `array( libelle, valeur, affichage )` | `valeur === ''` → pas d'étiquette, pas de `<time>` |
| `effectif_texte` | **`string` nue**, `''` si les deux compteurs sont vides | `''` → l'élément n'est pas rendu |
| `disponibilite` | `array( libelle, valeur, affichage )` | badge **si et seulement si** `valeur !== ''` |
| `photo` | `array( id, alt )` **ou `null`** | `is_array()` obligatoire |
| `annee` | `string`, 4 chiffres ou `''` | dérivation de la liste d'années, **éditeur uniquement** |

Non consommées : `id`, `identifiant`, `statut`, `protege`, `etat`, `males`, `femelles`, `pere`, `mere`,
`chiots_colonnes`, `chiots`, `chiots_message`, `galerie`.

### 4.2 Les trois pièges du code de #3, vérifiés ligne à ligne

1. **`photo` vaut `null`**, pas `array( 'id' => 0, … )`, quand il n'y a pas de photo principale ou que
   l'attachement n'existe plus (`hydratation.php:458-476`). Un `$portee['photo']['id']` écrit de
   confiance produit un avertissement PHP 8.1 sur **chaque** portée sans photo. `galerie`, elle, vaut
   `array()` : **les deux ne se testent pas de la même façon.**
2. **`effectif_texte` est une chaîne nue**, pas une enveloppe (`hydratation.php:628-640`) : ni
   `libelle`, ni `affichage`. Elle est vide quand les deux compteurs sont vides — on n'écrit pas
   « 0 mâle » quand on ne sait pas (décision 21, **D11**).
3. **Le badge se décide sur `valeur`, jamais sur `affichage`** (contrat #3 §11). `champ_liste()`
   (`hydratation.php:542-550`) rend `valeur => ''` **et** `affichage => 'Non renseigné'` pour une clé
   vide ou inconnue. Imprimer `affichage` produirait un **quatrième badge**, alors que `MASTER.md`
   §3.3 n'en gèle que trois, chacun avec sa pastille et son ratio de contraste mesuré. Le quatrième n'a
   ni forme, ni couleur, ni preuve d'accessibilité : **il est interdit.**

Deux constats ajoutés par le plan back, qui commandent le rendu :

4. **`mtb_get_portees()` écarte une portée non datée dès qu'un filtre d'année est actif**
   (`bootstrap.php:151` compare l'année demandée à `$portee['annee']`, qui vaut `''`). C'est correct —
   une portée sans année n'appartient à aucune année — mais il en découle que « Aucune portée pour
   cette année. » peut s'afficher **alors que 27 portées existent**.
5. **`titre_public` n'est jamais vide.** Le lien n'a donc jamais de nom accessible vide, et le rendu
   n'a **aucun repli à composer**.

### 4.3 La garde `function_exists()` n'est pas un rituel, même ici

Elle est obligatoire (contrat #3 §9, exigence **D12**), et elle sert dans trois situations réelles
malgré le fait que le bloc vive dans la même extension que la fonction :

1. `query/portee/bootstrap.php` a levé une exception à l'inclusion et le chargeur l'a **isolé** hors
   `WP_DEBUG` (`class-loader.php:176-191`) : les modules sont indépendants, `blocks/liste-portees/`
   s'est chargé, et `render.php` s'exécute sans la fonction.
2. Le module `query/portee/` a été renommé `_portee` pour le désactiver — mécanisme prévu par le
   contrat #1 §1.
3. Dépôt FTP partiel : un dossier arrivé, l'autre non.

Sans garde, les trois donnent une **erreur fatale sur une page publique**. Avec garde : `return '';`
— la page perd sa liste, rien d'autre, et **aucun message n'est inventé** au visiteur.

---

## 5. Le bloc enregistré

`mtb/liste-portees` · titre **« Liste de portées »** · icône `list-view` (Dashicon du cœur, zéro
requête tierce) · `apiVersion` 3 · `render: file:./render.php`.

### 5.1 Fichiers

```
includes/blocks/liste-portees/
├── bootstrap.php   namespace MTB\Core\Blocks\ListePortees ; init 20 (hook imposé au groupe blocks)
├── block.json
├── editeur.js      registerBlockType sans étape de compilation
├── rendu.php       final class Rendu — tout le balisage
├── annees.php      final class Annees — dérivation des années, éditeur uniquement
└── render.php      inclus par WordPress au rendu, JAMAIS par le chargeur
```

**`render.php` ne déclare aucune fonction ni classe.** WordPress l'inclut **à chaque instance de
bloc**, dans une portée locale : une deuxième liste sur la même Page produirait `Cannot redeclare …`,
erreur **fatale non rattrapable** par le `try/catch` du chargeur (contrat #1 §12). Son contenu utile
tient en deux lignes : garde `ABSPATH`, puis
`echo Rendu::bloc( is_array( $attributes ) ? $attributes : array() );`.

Aucune garde `class_exists( Rendu::class )` : `Rendu` est chargé par le **même** `bootstrap.php` qui
enregistre le bloc — si l'un manque, l'autre n'existe pas et `render.php` n'est jamais atteint. La
garde serait un rituel.

**`editorScript` est une poignée** (`mtb-liste-portees-editeur`) enregistrée par `wp_register_script()`,
**jamais `file:./editeur.js`** : WordPress chercherait alors un `editeur.asset.php` et émettrait un
`_doing_it_wrong` (contrat #1 §10 point 1).

Dépendances de script, closes : `wp-blocks`, `wp-element`, `wp-block-editor`, `wp-components`,
`wp-server-side-render`, `wp-data`, `wp-core-data`. **Pas de `wp-i18n`** — français littéral
(contrat #1 §7), et **pas de `textdomain`** dans `block.json`, qui ferait passer `title` et
`description` par un catalogue inexistant.

### 5.2 `supports` — tout à `false`

```json
"supports": { "html": false, "customClassName": false, "align": false, "anchor": false,
              "spacing": false, "typography": false, "color": false,
              "border": false, "shadow": false, "dimensions": false }
```

C'est `MASTER.md` §14 : ni couleur, ni police, ni espacement, ni alignement, ni classe personnalisée,
ni CSS personnalisé. `html: false` parce qu'un bloc dynamique ne s'édite pas en HTML.

### 5.3 `example` — absent, et le motif n'est pas D11

Les attributs sont `nombre` et `annee` : un `example` ne contiendrait **aucune** donnée d'élevage, donc
D11 n'est pas en cause. Les motifs sont ailleurs :

- l'aperçu de l'inséreur rendrait le bloc **avec les vraies portées**, par un appel de rendu serveur à
  chaque survol — coût réel, aucun gain de compréhension pour une liste de texte ;
- sur un site où rien n'est publié, l'aperçu serait **vide**, ce qui suggère un bloc cassé au moment
  précis où elle cherche quoi insérer ;
- la décision 15 rappelle qu'un `example` peut déclencher un chargement d'aperçu côté cœur. Ne pas en
  poser garde le compte à **zéro requête tierce**, sans discussion.

**Ce que Fabienne voit donc dans l'inséreur** : le nom, l'icône de liste, la description française —
**pas d'aperçu visuel**. La fiche d'aide doit le dire, et donner le chemin qui marche dans tous les
cas : taper « portées » dans la recherche de l'inséreur.

### 5.4 La catégorie `mtb` n'est pas enregistrée par ce module

`block.json` déclare `"category": "mtb"`. **Ce module ne pose aucun filtre `block_categories_all` et
ne crée pas `blocks/categorie-mtb/`.**

Vérifié : la catégorie n'existe **nulle part** dans `wp-content/` (zéro occurrence). Le contrat #1 §10
la dit due par « la première issue de composants », et **ce lot en compte six**. Deux modules qui la
déclarent, c'est le doublon silencieux que la décision 9 interdit. L'arbitrage a été remonté au lead du
lot ; le repli est appliqué, pas contourné.

| Situation | Ce que Fabienne voit |
|---|---|
| Catégorie enregistrée (chaîne propriétaire arrivée) | Le bloc dans le groupe **« Mont Brabant »**, avec ses frères |
| Catégorie absente (aujourd'hui) | Le bloc **s'enregistre et fonctionne**, **reste trouvable par la recherche** de l'inséreur, et apparaît dans un groupe fourre-tout |

**Coût à ne pas cacher** : côté client, `registerBlockType` **avertit dans la console** quand la
catégorie déclarée n'existe pas, et retire la catégorie du bloc. Invisible pour Fabienne, visible pour
un relecteur. **Ne pas le faire taire.**

---

## 6. Attributs — deux, et leur défaut ne présume rien

| Attribut | Type | Défaut | Ce que le défaut **signifie** |
|---|---|---|---|
| `nombre` | `string` | `""` | **toutes les portées** — vide veut dire « je n'ai pas choisi », pas « zéro » |
| `annee` | `string` | `""` | **toutes les années** — aucun filtre |

**`nombre` est une chaîne et non un nombre**, et c'est la **décision 21 transposée au réglage**. Avec
`"type": "number"` et défaut `0`, le champ afficherait `0` à l'insertion : Fabienne lit « zéro
portée », et le code doit ensuite décréter que `0` veut dire « toutes » — ce qui n'est ni lisible ni
vrai. Chaîne vide : le champ est vide, et l'aide dit ce que le vide veut dire. **Aucun préremplissage
(D11).**

**Aucun autre attribut**, et chaque absence a sa raison :

- **pas de bascule « Afficher les vignettes »** : « vignette facultative » veut dire *rendue si la
  portée a une photo*, pas *réglable*. Une bascule serait une décision visuelle déguisée en réglage de
  contenu.
- **pas d'attribut de titre, et le bloc n'émet aucun `<h1>`–`<h6>`** : le niveau juste dépend de la
  page, le bloc ne peut pas le connaître, et le deviner casserait la hiérarchie (BRIEF §11). Fabienne
  pose un bloc **Titre** au-dessus si elle en veut un. **Le thème ne doit pas attendre de titre dans
  ce bloc.**
- **pas de bascule sur le lien de sortie** : il est **dérivé** (liste tronquée, ou année sans
  résultat).

### 6.1 Assainissement des attributs au rendu — aucune valeur n'est présumée propre

`Rendu::normaliser( array $attributs ): array` → `array( 'nombre' => int, 'annee' => string )`.

Un attribut vient de l'éditeur, donc d'une soumission : la route du cœur
`/wp/v2/block-renderer/mtb/liste-portees` accepte n'importe quel corps JSON d'un compte `edit_posts`.

| Entrée | Traitement | Résultat |
|---|---|---|
| clé absente, `null`, tableau, objet | `isset` + `is_scalar`, sinon `''` | toutes / aucune année |
| `nombre = "5"` | `preg_match( '/^\d+$/' )` puis `(int)` | `5` |
| `nombre` = `"0"`, `"-3"`, `"3,5"`, `"abc"`, `""`, `"1e9"` | hors motif, ou `< 1` | **`-1` = toutes.** Il n'existe aucun moyen de demander zéro portée : pour n'en afficher aucune, on **retire le bloc** |
| `nombre = "999"` | accepté | la liste n'affiche que ce qu'elle a |
| `annee = "1998"` | `trim()` puis `preg_match( '/^\d{4}$/' )` | filtre actif |
| `annee` = `"199"`, `"98"`, `"19 98"`, `"annee"` | hors motif | **`''`, aucun filtre** |
| tout autre attribut | jamais lu | ignoré |

**Point non évident, à ne pas rater** : `mtb_get_portees()` ignore **déjà** une année mal formée
(`bootstrap.php:116-118`). Il faut néanmoins normaliser **avant** l'appel, parce que **le message
d'état vide dépend de la validité du filtre**. Si `annee = "199"`, la fonction rend les 27 portées ; un
rendu qui aurait cru le filtre actif afficherait « Aucune portée pour cette année. » au-dessus de 27
portées. **Une seule source de vérité : l'année normalisée par `Rendu::normaliser()`.**

### 6.2 Réglages du panneau de droite — texte exact

`InspectorControls` › `PanelBody { title: "Réglages", initialOpen: true }`

| Contrôle | Étiquette exacte | Aide exacte |
|---|---|---|
| `TextControl` `type="number"` `min="1"` `step="1"` | **Nombre de portées à afficher** | *Laissez vide pour afficher toutes les portées.* |
| `SelectControl` | **Année** | *La liste n'affiche que les portées nées cette année-là.* |

Première option du sélecteur : `value=""`, libellé **« Toutes les années »**. Puis une option par
année, **dans l'ordre où la dérivation les livre** — décroissant, parce que la chaîne propriétaire
trie ainsi.

Aucun mot de `MASTER.md` §10.4 (`meta`, `champ personnalisé`, `taxonomie`, `slug`, `permalien`,
`extrait`, `média`, `template`…). Aucun mot anglais.

### 6.3 Dérivation de la liste des années — conforme à la décision 19, et sous deux verrous

```php
namespace MTB\Core\Blocks\ListePortees;
final class Annees {
    public static function disponibles(): array;   // ['2025','2024','2019',…]
}
```

- Sous `function_exists( 'mtb_get_portees' )`, sinon `array()`.
- Collecte la clé `annee` que la chaîne propriétaire **publie déjà** (`hydratation.php:264`), écarte
  `''`, `array_unique`, `array_values`. **Aucun tri** : la liste arrive déjà décroissante, et l'ordre
  appartient à la chaîne propriétaire (décision 19) — le refaire serait s'en approprier la garantie.
- **Jamais sous un nom global du genre `mtb_get_annees_de_portees()`.** Espace de noms du bloc,
  méthode statique. Un nom global serait exactement l'**ombrage silencieux** que la décision 19
  décrit, le jour où `query/portee/` exposera les années : `scandir()` charge par ordre alphabétique,
  la garde `function_exists()` ferait gagner la mauvaise implémentation **sans un mot**, sur un site
  qui répond 200.
- Appelée **uniquement** depuis `enqueue_block_editor_assets`, transmise par `wp_localize_script()`.
  **Jamais au rendu public.**
- Une portée non datée est absente de la **liste des années** ; elle reste dans la **liste des
  portées**.

**Détail qui évite une perte** : si `attributes.annee` est renseignée mais absente de la liste dérivée
(la dernière portée de 1998 a été dépubliée), l'année stockée est **ajoutée aux options**, avec
elle-même pour libellé. On préserve la liaison au lieu de l'effacer en silence — même principe que
« Fiche introuvable » du contrat #3 §18.4.

**Écart de hook ratifié** : `enqueue_block_editor_assets` employé depuis un module `blocks/`, alors
que le contrat #1 §2 range ce hook dans `fields/`. C'est le **seul hook qui ne s'exécute que dans
l'éditeur** ; sur `init` 20, la dérivation hydraterait les 27 portées **à chaque requête publique**.
Le contrat §2 impose un hook *pour l'enregistrement d'un bloc* (`init` 20, respecté) et n'interdit pas
les autres. **Ratifié, signalé, non dissimulé.**

---

## 7. Balisage rendu — gelé, élément par élément

C'est la moitié la plus importante du contrat : `dev-ux-mtb` code contre elle **sans voir**
`dev-back-mtb`.

### 7.1 Liste non vide

```html
<div class="wp-block-mtb-liste-portees mtb-liste-portees alignwide">
  <ul class="mtb-liste-portees__liste" role="list">

    <li class="mtb-liste-portees__entree">

      <!-- ABSENT si pas de photo exploitable — pas de conteneur vide, pas de réserve (§9.2) -->
      <figure class="mtb-liste-portees__vignette mtb-photo">
        <img class="mtb-liste-portees__image" src="…" srcset="…" sizes="144px"
             width="300" height="200" loading="lazy" decoding="async" alt="…">
      </figure>

      <div class="mtb-liste-portees__corps">

        <!-- <a> si lien !== '' , sinon <span class="…__nom"> — JAMAIS href="" ni href="#" -->
        <a class="mtb-liste-portees__lien" href="…">Portée A3 2025</a>

        <p class="mtb-liste-portees__meta">
          <span class="mtb-liste-portees__date">
            <span class="mtb-liste-portees__etiquette">Née le</span>
            <time class="mtb-liste-portees__date-valeur" datetime="2025-05-03">3 mai 2025</time>
          </span>
          <!-- ABSENT si effectif_texte === '' -->
          <span class="mtb-liste-portees__effectif">3 mâles, 2 femelles</span>
          <!-- ABSENT si disponibilite['valeur'] === '' -->
          <span class="mtb-liste-portees__dispo mtb-dispo mtb-dispo--disponible">Chiots disponibles</span>
        </p>

      </div>
    </li>
    …
  </ul>

  <!-- ABSENT si la liste n'est pas tronquée, ou si le lien d'archive n'est pas exploitable -->
  <p class="mtb-liste-portees__sortie">
    <a class="mtb-liste-portees__lien-index" href="…/portees/">Toutes les portées</a>
  </p>
</div>
```

### 7.2 Année filtrée sans résultat — état vide **public**

```html
<div class="wp-block-mtb-liste-portees mtb-liste-portees mtb-liste-portees--vide alignwide">
  <p class="mtb-liste-portees__vide">Aucune portée pour cette année.</p>
  <p class="mtb-liste-portees__sortie">
    <a class="mtb-liste-portees__lien-index" href="…/portees/">Toutes les portées</a>
  </p>
</div>
```

**Aucune `<ul>` dans ce cas.** Si le lien d'archive n'est pas exploitable, le message **reste seul** et
le `<p class="…__sortie">` est absent.

### 7.3 État vide **éditeur** (`MASTER.md` §9.1), rendu par `editeur.js`

```html
<div class="mtb-bloc-vide mtb-liste-portees__vide-editeur">
  <p class="mtb-bloc-vide__nom mtb-liste-portees__vide-editeur-nom">Liste de portées</p>
  <p class="mtb-bloc-vide__phrase mtb-liste-portees__vide-editeur-phrase">Ce bloc n'affiche rien tant qu'aucune portée n'est publiée.</p>
</div>
```

**Deux `<p>`, jamais deux `<span>`** : c'est ce qui garantit **deux lignes lisibles à zéro CSS** — et
ce n'est pas une précaution théorique, voir la dette **T-#13-c** en section 13.

Le texte est écrit **en minuscules**. La capitale de §9.1 est un `text-transform` du thème, jamais une
chaîne : `MASTER.md` §13 interdit majuscule + interlettre au-delà de trois mots, et « Liste de
portées » en fait exactement trois.

**Les deux jeux de classes sont émis ensemble, et c'est délibéré** — arbitrage 6 de la section 12.

### 7.4 Décisions de balisage, chacune avec son motif

- **`.mtb-dispo` est sur l'élément qui porte le texte du libellé**, jamais sur un conteneur. `MASTER.md`
  §3.3 dessine la pastille en `::before` héritant de `currentColor` : son contraste **est** celui du
  libellé, donc la classe doit vivre là où le texte vit.
- **Une seule classe de variante**, prise dans `mtb-dispo--disponible|--reserve|--passee`, construite
  depuis `disponibilite['valeur']` — garantie appartenir à la liste close par `champ_liste()`.
  `esc_attr()` malgré cette garantie. **Jamais `mtb-dispo` seule, jamais deux variantes, jamais une
  quatrième.** Les trois suffixes sont **les clés stockées gelées** du contrat #3 §19.6 : deux chaînes
  qui suivent le contrat **ne peuvent pas** produire deux noms différents. C'est le seul verrou
  disponible sans fichier partagé.
- **`<time datetime>` uniquement quand `valeur !== ''`**, et l'attribut reçoit la valeur brute
  `AAAA-MM-JJ`, **sans aucun reformatage**. Date absente → un `<span class="…__date-valeur">` portant
  « Non renseigné », et **aucun `<time>` vide**.
- **`__etiquette` (« Née le ») n'est rendue que quand la date existe.** « Née le Non renseigné » n'est
  pas du français. Les deux chaînes viennent du serveur ; le bloc décide seulement **quand** chacune
  s'imprime — décision de structure, qui lui appartient.
- **Le texte du lien est `titre_public`** (« Portée A3 2025 »), **pas « Voir la portée »**. §10.3
  atteste « Voir la portée » pour un lien *vers* une fiche, mais dans une liste de 27 entrées, 27 liens
  homonymes sont un échec d'accessibilité : aucun nom accessible distinctif. `titre_public` **est** un
  nom accessible correct, fourni fini par le serveur.
- **Un seul lien par entrée.** La vignette n'est jamais un lien ; l'entrée n'est jamais un lien
  englobant.
- **`role="list"` sur la `<ul>`** : Safari/VoiceOver perd la sémantique de liste dès que
  `list-style: none` est appliqué, et la feuille l'applique. Ce n'est pas un ARIA décoratif, c'est la
  restauration d'une sémantique **que le CSS retire**.
- **Un espace typographique entre les `<span>` de `__meta`** — un retour à la ligne d'indentation
  suffit. Sans lui, le rendu **sans CSS** colle « 3 mai 2025 3 mâles ».
- **`<figure>` sans `<figcaption>`** : l'`alt` porte l'alternative, une légende serait un doublon. Le
  `<figure>` est **absent**, jamais vide — donc rien à masquer en CSS.
- **Aucun `aria-label`, aucun `role` autre que `role="list"`.** Une `<ul>` de liens est nativement
  correcte ; un `aria-label` serait une chaîne de plus à composer.
- **Aucun attribut `style`, aucune classe visuelle, aucun `data-libelle`, aucun `<h1>`–`<h6>`.**

### 7.5 `alignwide` — la seule classe de mise en page que l'extension émet

`MASTER.md` §7.1 range explicitement les listes de portées dans le **canal large**, et
`base.css:522-532` en fait le mécanisme : `.mtb-canal > .alignwide` reçoit
`grid-column: large-debut / large-fin`, `max-inline-size: var(--l-large)`, `margin-inline: auto`.

Le thème **ne peut pas** obtenir ce résultat depuis sa feuille de bloc : la règle voisine
`.mtb-canal.is-layout-constrained > *:not(.alignwide)` (`base.css:507-510`) pose
`margin-inline: 0 !important` à spécificité (0,3,0), et `base.css` appartient à une chaîne sœur dans ce
lot. Un `!important` de riposte dans une feuille de bloc serait pire que le mal.

**Le bloc émet donc `alignwide` en dur**, via `get_block_wrapper_attributes()`, avec
`supports.align: false` pour que Fabienne ne puisse pas le défaire. C'est `MASTER.md` §7.1 qui assigne
le canal, pas l'éditrice (§14) : l'extension **transmet une affectation du système de design**, elle
n'invente pas une règle visuelle. La forme propre serait `.mtb-canal > .mtb-liste-portees` dans
`base.css` — **dette T-#13-h**, à la charge de qui possédera `base.css` après ce lot.

### 7.6 La photo

```php
wp_get_attachment_image(
    (int) $photo['id'],
    'medium',
    false,
    array(
        'class' => 'mtb-liste-portees__image',
        'alt'   => (string) $photo['alt'],
        'sizes' => '144px',
    )
);
```

- **Taille `medium`** — l'unique exception du contrat #3 §13 (« le rendu choisit lui-même la taille »).
  `thumbnail` est **refusé** : 150×150 **recadré en dur** par WordPress déciderait le cadrage côté
  serveur, alors que le cadrage est une décision de présentation faite en CSS (`aspect-ratio` +
  `object-fit`), et 150 px ne tient pas un écran à 2×.
- **`sizes="144px"` et la largeur de vignette `9rem` de la feuille sont UNE valeur écrite dans DEUX
  fichiers.** Chacun des deux porte un commentaire nommant l'autre. **Changer l'un sans l'autre livre
  un `srcset` qui ment** — au navigateur, pas à l'œil, donc sans symptôme visible.
- **Pas de `fetchpriority`.**
- L'`alt` est passé **brut** : `wp_get_attachment_image()` échappe lui-même ses attributs. Le
  pré-échapper ferait lire « `&#039;` » à un lecteur d'écran.
- **Piège obligatoire** : `photo` non nul garantit que le **contenu** de l'attachement existe, pas que
  le **fichier** soit là — `wp_get_attachment_image()` peut rendre `''`. Donc : **construire la chaîne
  d'image d'abord, n'émettre le `<figure>` que si elle est non vide.** Sinon on obtient un emplacement
  vide, exactement ce que `MASTER.md` §9.2 interdit.

### 7.7 Échappement — table complète

Les fonctions de lecture renvoient des données **non échappées** ; l'échappement appartient au rendu
(contrat #3 §9.2).

| Donnée | Fonction | Où |
|---|---|---|
| `titre_public` | `esc_html()` | texte du `<a>` ou du `<span class="…__nom">` |
| `lien` | `esc_url()` | `href` |
| `date_naissance['libelle']` | `esc_html()` | `…__etiquette` |
| `date_naissance['affichage']` | `esc_html()` | contenu du `<time>` / `<span>` |
| `date_naissance['valeur']` | `esc_attr()` | `datetime` |
| `effectif_texte` | `esc_html()` | `…__effectif` |
| `disponibilite['affichage']` | `esc_html()` | texte du badge |
| `disponibilite['valeur']` | `esc_attr()` | suffixe de `mtb-dispo--…` |
| `photo['id']` / `photo['alt']` | `(int)` / brut | `wp_get_attachment_image()` échappe |
| lien d'archive | `esc_url()` | `href` de `…__lien-index` |
| attributs de l'enveloppe | `get_block_wrapper_attributes()` | échappe seul |
| chaînes fixes du bloc | littérales, **échappées quand même** | `esc_html( 'Aucune portée pour cette année.' )` |

**Aucun `wp_kses_post()`** : ce bloc n'imprime aucune prose riche. **Aucun `esc_html__()`** — aucune
i18n dans `mtb-core` (contrat #1 §7).

### 7.8 Crochets de classes — liste close

**Émis par PHP :**

| Crochet | Élément | Toujours présent ? |
|---|---|---|
| `mtb-liste-portees` | `<div>` enveloppe, à côté de `wp-block-mtb-liste-portees` | oui |
| `alignwide` | même `<div>` | oui (§7.5) |
| `mtb-liste-portees--vide` | modifier sur l'enveloppe | **seulement** état « année sans résultat » |
| `mtb-liste-portees__liste` | `<ul role="list">` | oui, sauf état vide |
| `mtb-liste-portees__entree` | `<li>` | une par portée |
| `mtb-liste-portees__vignette` + `mtb-photo` | `<figure>` | **non** — absent sans photo exploitable |
| `mtb-liste-portees__image` | `<img>` (+ classes du cœur) | avec la vignette |
| `mtb-liste-portees__corps` | `<div>` | oui |
| `mtb-liste-portees__lien` | `<a>` | **non** — absent si `lien === ''` |
| `mtb-liste-portees__nom` | `<span>` | **non** — présent **à la place** de `__lien` |
| `mtb-liste-portees__meta` | `<p>` | oui (la date s'y imprime toujours) |
| `mtb-liste-portees__date` | `<span>` | oui |
| `mtb-liste-portees__etiquette` | `<span>` interne | **non** — absent si la date manque |
| `mtb-liste-portees__date-valeur` | `<time datetime>` si date, sinon `<span>` | oui |
| `mtb-liste-portees__effectif` | `<span>` | **non** — absent si `effectif_texte === ''` |
| `mtb-liste-portees__dispo` + `mtb-dispo` + une variante | `<span>` | **non** — absent si `disponibilite['valeur'] === ''` |
| `mtb-liste-portees__vide` | `<p>` | état vide public seulement |
| `mtb-liste-portees__sortie` | `<p>` | liste tronquée, **ou** état vide |
| `mtb-liste-portees__lien-index` | `<a>` | dans `__sortie`, si le lien d'archive existe |

**Émis par `editeur.js`, éditeur seulement :** `mtb-bloc-vide` + `mtb-liste-portees__vide-editeur` ·
`mtb-bloc-vide__nom` + `…__vide-editeur-nom` · `mtb-bloc-vide__phrase` + `…__vide-editeur-phrase`.

**Ce que le thème ne trouvera pas, et ne doit pas attendre** : aucun titre `h1`–`h6`, aucun libellé
« Voir la portée », aucun tableau, aucun `data-libelle`, aucun `<thead>`, aucune classe visuelle,
aucun `style` en ligne, aucun ARIA hors `role="list"`.

**Cinq éléments sont conditionnels** — `<figure>`, `<a>`/`<span class="__nom">`, `__etiquette`,
`__effectif`, le badge — **et une feuille qui suppose leur présence casse.**

---

## 8. Comment le rendu sait qu'il est dans l'éditeur — il ne le sait pas, et c'est voulu

C'est le point technique le plus glissant de l'issue. Il est **écarté**, pas résolu.

**Refusé** : `defined( 'REST_REQUEST' ) && REST_REQUEST`, avec ou sans `current_user_can`. Trois
défauts : la constante vaut `true` pour **toute** requête REST, pas seulement
`/wp/v2/block-renderer/` ; l'encadré §9.1 n'est stylé que dans l'éditeur, donc tout autre consommateur
REST recevrait un balisage **délibérément non stylé** ; et une détection fine passerait par
`$GLOBALS['wp']`, qui n'a rien à faire dans un rendu public.

**Retenu** : `render.php` ne connaît **que le public**. La décision « suis-je dans l'éditeur » est
prise là où elle est **exacte par nature**, dans `editeur.js` :

```
edit() :
  portees = useSelect( select => select( 'core' ).getEntityRecords(
              'postType', 'mtb_portee', { per_page: 1, status: 'publish', _fields: 'id' } ) )

  portees === null            → conteneur vide, useBlockProps()   // résolution en cours, aucune chaîne inventée
  Array.isArray && length 0   → l'encadré de la section 7.3
  sinon                       → wp.serverSideRender { block: 'mtb/liste-portees', attributes }
```

C'est possible **parce que** le contrat #3 §18.1 laisse `show_in_rest => true` sur le **type** — seules
les seize métas sont fermées. Le bloc n'a besoin que de l'**existence** d'une portée publiée, jamais
d'une méta. Effet secondaire utile : la sonde est publique, aucune capacité supplémentaire n'est
requise.

**Dégradation honnête** : si la résolution REST échoue (durcissement d'hébergeur), l'éditeur retombe
sur `serverSideRender`, qui affichera **son propre message du cœur**, pas le nôtre. Jamais un écran
cassé, et c'est consigné en section 14.

**Qui distingue « rien n'est publié » de « filtré sans résultat » : le serveur, toujours.** Il rend
soit une chaîne vide, soit `__vide`. Le thème ne doit jamais avoir à trancher, sous peine de porter une
règle métier.

---

## 9. États spéciaux

| État | Émis par le serveur | Rendu attendu du thème |
|---|---|---|
| `aucune_portee` — filtre d'année sans résultat | `--vide` + `__vide` (« Aucune portée pour cette année. ») + `__sortie` | Habiller le message. Ne rien ajouter, ne rien composer |
| `aucune_portee` — rien de publié, **public** | **chaîne vide**, aucun élément, pas même l'enveloppe | Rien à habiller. La page **ne réserve aucune place** (`MASTER.md` §9 : un composant sans contenu ne s'affiche pas) |
| `aucune_portee` — rien de publié, **éditeur** | `mtb-bloc-vide` (JS, section 7.3) | Apparence §9.1, **en bonus** : le balisage porte l'information seul |
| `donnee_absente` — date | `__date-valeur` contient **« Non renseigné »**, sans `<time>`, sans `__etiquette` | Imprimer tel quel, en `--texte-doux`. **Jamais un tiret, jamais « Aucun », jamais « Non testé »** |
| `donnee_absente` — photo | `<figure>` **absent** | Aucun emplacement, aucune réserve, **aucun pictogramme, aucune silhouette, aucune image de remplacement** (§9.2) |
| `donnee_absente` — effectif | `__effectif` **absent** | Rien. Jamais « 0 mâle » quand on ne sait pas |
| `donnee_absente` — disponibilité | badge **absent** | Jamais un quatrième badge |
| `donnee_absente` — lien | `<span class="__nom">` **à la place** du `<a>` | Même place, même apparence. **Jamais `href=""`, jamais `href="#"`** |
| `parent_hors_elevage` | **hors sujet** — ce bloc n'affiche aucun parent | — |
| `page_protegee` | **n'atteint jamais ce bloc** — `has_password => false` en amont (`hydratation.php:96`) | — |

Une portée qui cumule les cinq absences rend une `<li>` contenant un titre et « Non renseigné ».
**C'est exactement le cas des quatre fixtures actuelles, et c'est présentable.**

---

## 10. Chaînes fournies par le serveur — le thème les imprime, jamais ne les compose

**De `mtb_get_portees()`** : « Portée A3 2025 » · « Née le » · la date formatée selon les réglages du
site · « Non renseigné » · « 3 mâles, 2 femelles » (accord du singulier compris) · « Chiots
disponibles » / « Tous réservés » / « Portée passée » · les `alt` des photos.

**Du bloc, verbatim, non composées** :

| Chaîne | Source attestée |
|---|---|
| « Aucune portée pour cette année. » | `MASTER.md` §9.3 ligne 818 |
| « Toutes les portées » | `MASTER.md` §10 ligne 929 — voir arbitrage 7 |
| « Ce bloc n'affiche rien tant qu'aucune portée n'est publiée. » | `MASTER.md` §9.1, forme close |
| « Liste de portées » | BRIEF §6, catalogue |

**Du bloc, composées — aucune n'est un fait d'élevage, toutes ratifiées ici** :

| Chaîne | Origine |
|---|---|
| « Affiche les portées publiées, de la plus récente à la plus ancienne. » (`description`) | Descriptif du comportement ; vocabulaire de §10.2, forme active de §10.1 |
| « Réglages » (titre du panneau) | Terme employé par l'énoncé de l'issue |
| « Nombre de portées à afficher » | « Portées » (§10.2) + la capacité « Combien d'éléments affiche une liste » (§14) |
| « Laissez vide pour afficher toutes les portées. » | Décalque de l'aide ratifiée au contrat #3 §18.4 (« Laissez vide tant que vous ne le savez pas. ») |
| « Toutes les années » (première option) | « Année » (§10.2). **`MASTER.md` n'atteste aucun libellé d'option « tout »** — manque **M4**, section 13 |
| « La liste n'affiche que les portées nées cette année-là. » | Descriptif ; « Née le » est de §10.2 |
| `keywords` : « portée », « portées », « chiots » | Trois mots attestés §10.2 ; c'est le chemin de recherche de l'inséreur |

---

## 11. Exigences de l'habillage

Elles appartiennent à `dev-ux-mtb`, dans le fichier unique de la section 3.

1. **Aucune valeur brute, aucune couleur hors des quinze jetons.** C'est la dette T7 qu'on ne refait
   pas. Toute extrapolation est **étiquetée en commentaire**, avec sa source et son calcul.
2. **`base.css:127` remet `ul { margin: 0 }` mais ne touche ni `padding-inline-start` ni
   `list-style`** : la feuille retire elle-même les puces et le retrait de 40 px.
3. **Hériter, jamais redéfinir** : focus visible, liens, `p`, `figure` viennent de `base.css`. Le
   `:focus-visible` du thème s'applique aux liens d'entrée **sans une ligne de plus**.
4. **360 px sans défilement horizontal** et **zoom 200 %**. À **mesurer**, pas à affirmer : Chrome sans
   interface a une largeur minimale d'environ 500 px, donc la vérification à 360 px passe par une
   **iframe**, sinon la capture ment (mémoire de projet). Assertion attendue :
   `scrollWidth <= clientWidth` sur la racine.
5. **La vignette** : ratio en jeton, `object-fit`, `aspect-ratio`. **Largeur `9rem`** — unique
   extrapolation étiquetée, transposée du minimum de vignette de galerie §6.7, à ratifier par
   `lead-design-mtb` (question ouverte 1). Elle est **liée à `sizes="144px"`** de la section 7.6 par un
   commentaire croisé dans les deux fichiers.
6. **Ce que devient la mise en page sans vignette** : §9.2 dit « l'emplacement n'existe pas. Aucun
   trou, aucune réserve. » Une grille qui réserverait la colonne laisserait un trou. **Une seule règle
   doit servir l'entrée avec vignette et celle sans.**
7. **Le badge** : les trois variantes de §3.3, leurs trois couples fond/encre, la pastille **en CSS
   pur** — `border-radius: 50%`, `currentColor`, **aucune police d'icônes, aucun fichier image**. Le
   **mot seul suffit** : aucune information portée par la couleur seule. §3.3 exige en outre que le
   badge soit « toujours accompagné, en lecture d'écran comme à l'œil, de la date de naissance » —
   c'est tenu, la date est toujours rendue.
8. **`.mtb-dispo`, `.mtb-photo` et l'apparence §9.1 sont portées SCOPÉES**, jamais nues :
   `.mtb-liste-portees .mtb-dispo`, jamais `.mtb-dispo`. Le motif est en arbitrage 5.
9. **Contraste mesuré** pour chaque paire employée. §13 en fait un bloquant.
10. **Impression** : `MASTER.md` renvoie à un §9.6 qui **n'existe pas** (dette **T-#13-e**). **Aucun
    `print-color-adjust` n'est ajouté** : §3.3 garantit que le mot seul suffit, et le mot s'imprime.
    C'est précisément ce que « la couleur seule ne suffit jamais » achète.
11. **Zéro requête vers un domaine tiers** : aucun `@import`, aucune police, aucune image externe,
    aucun `url()` distant.
12. **Le poids ajouté est mesuré en octets** (`wc -c`), face au budget du brief §12 (200 Ko
    HTML+CSS+JS). Le « 29 Ko » de `ETAT.md` est à remesurer par la même occasion — le plan front
    l'estime plutôt vers 34 Ko pour les deux feuilles seules.

---

## 12. Arbitrages — les divergences entre les deux plans, tranchées

Les deux leaddev ne se voyaient pas. Ce sont les divergences réelles qu'ils ont produites.

| # | Désaccord | Décision | Raison |
|---|---|---|---|
| 1 | **Structure de la ligne secondaire.** Back : trois `<p>` de bloc (`__date`, `__effectif`, `__dispo`). Front : **un** `<p class="__meta">` contenant trois `<span>` | **Un `<p class="__meta">` avec trois `<span>`** | Le front écrit le CSS, et sa rangée souple qui se replie a besoin de trois éléments **en ligne** dans un seul conteneur. Trois `<p>` de bloc l'obligeraient à un `display: contents` ou à défaire la structure. Le back ne perd rien : ses cinq conditions de rendu s'appliquent identiquement à des `<span>` |
| 2 | **Texte du lien.** Back : `<a class="__lien"><span class="__titre">…</span></a>`. Front : `<a class="__lien">…</a>` directement | **Front** — pas de `<span>` interne | Le `<span>` intérieur est un emballage sans emploi : `__lien` peut porter la typographie directement. Il coûterait 27 nœuds de DOM et une ambiguïté sur lequel des deux porte le style |
| 3 | **Cas sans lien.** Back : le même `__titre` sans `<a>`. Front : `<span class="__nom">`, nom distinct | **Front** — `__nom` | Deux noms distincts coûtent un sélecteur de plus dans la feuille ; un nom unique porté tantôt par un `<a>` tantôt par un `<span>` coûte une règle qui doit deviner son élément. Et `__nom` dit ce que c'est |
| 4 | **Date absente : imprimer « Non renseigné », ou omettre la date ?** Back : imprimer. Front : omettre, **par symétrie avec la règle du badge** | **Imprimer « Non renseigné », sans l'étiquette « Née le »** | La symétrie invoquée est séduisante et fausse. Le badge est une **exception motivée** : §3.3 gèle trois états avec leurs ratios mesurés, un quatrième n'a **aucune forme définie**. Une date absente n'a pas ce problème — §9.3 la traite nommément : « **« Non renseigné » en `--texte-doux`, jamais un tiret seul, jamais un blanc** ». Omettre rendrait une portée sans date **indiscernable d'un bogue de rendu**, contre D12 |
| 5 | **`.mtb-dispo`, `.mtb-photo`, apparence §9.1 : primitives partagées qu'aucune chaîne ne possède** | **Portées scopées dans la feuille de bloc**, avec dette inscrite | L'argument du front est décisif et vaut d'être conservé : si deux chaînes écrivent `.mtb-dispo` **non scopée**, alors sur une page portant les deux blocs **les deux feuilles sont chargées**, et la cascade les fusionne déclaration par déclaration selon l'ordre du registre. On n'obtient pas deux badges divergents, on obtient **un troisième badge que personne n'a dessiné** — T9 refaite en CSS, en pire : T9 donne trois résultats prévisibles, ceci un résultat imprévisible. Le scopage rend chaque copie inerte hors de son bloc |
| 6 | **Nom du crochet de l'état vide éditeur.** Back : `mtb-bloc-vide`, à geler pour les six chaînes. Front : `__vide-editeur`, scopé, pour que le hissage ait une cible sans ambiguïté | **Les deux, sur les mêmes éléments** | Ce n'est pas un compromis mou, c'est la seule forme qui paie les deux arguments. `mtb-bloc-vide` est le nom que la future règle partagée d'`editor.css` visera : l'émettre **aujourd'hui** rend le hissage une **pure addition**, sans renommer six modules. Les noms scopés sont ce que ma feuille habille, donc aucune collision de cascade. Le solde de la dette devient : supprimer les règles scopées, la classe partagée est **déjà dans le balisage**. Coût : trois jetons de classe |
| 7 | **Libellé du lien de sortie.** `MASTER.md` §9.3 l. 818 écrit « **Voir** toutes les portées » ; §10 l. 929 écrit « **Toutes les portées** » | **« Toutes les portées »** | §10 **se déclare arbitre du vocabulaire**, et §9.3 le cite en prose. Divergence interne de `MASTER.md` à corriger par `lead-design-mtb` (**M3**) |
| 8 | **Nom du lien de sortie et de son conteneur.** Back : `__sortie` > `__toutes`. Front : le lien dans le `<p>` du message, classe `__lien-index` | **`__sortie` (back) contenant `__lien-index` (front), toujours son propre `<p>`, dans les deux contextes** | Le back a vu un contexte que le front avait manqué : **la liste tronquée** a aussi besoin du lien, et là il n'y a pas de message à l'accompagner. Un seul `<p class="__sortie">` sert les deux contextes, donc une seule règle. `__lien-index` est retenu contre `__toutes`, qui à une lettre près ne veut plus rien dire |
| 9 | **`role="list"` sur la `<ul>`.** Back : « aucun ARIA ». Front : indispensable | **Front** | Le fait est vérifiable : Safari/VoiceOver retire la sémantique de liste quand `list-style: none` est appliqué, et la feuille l'applique. Ce n'est pas un ARIA décoratif, c'est la **restauration d'une sémantique que le CSS retire**. L'interdit du back visait `aria-label`, qui reste interdit |
| 10 | **`mtb-photo` sur le `<figure>`.** Back ne l'émettait pas ; front en a besoin | **Émise**, à côté de `__vignette` | Même logique que l'arbitrage 6 : la classe partagée est dans le balisage dès aujourd'hui, l'habillage scopé la rejoint plus tard sans reprise du rendu |
| 11 | **Le canal large.** Back : `align: false`, donc **aucune** classe d'alignement, au thème de placer `.mtb-liste-portees`. Front : `alignwide` en dur sur la racine | **`alignwide` en dur, `supports.align: false`** | Le thème **ne peut pas** gagner : `.mtb-canal.is-layout-constrained > *:not(.alignwide)` pose `margin-inline: 0 !important` à (0,3,0), et `base.css` appartient à une chaîne sœur. Un `!important` de riposte serait pire. §7.1 assigne le canal, pas l'éditrice : l'extension **transmet** une affectation du système de design. Dette **T-#13-h** pour la forme propre |
| 12 | **Taille et `sizes` de la vignette** — non attestés par `MASTER.md` | **`medium` + `sizes="144px"`, liés par commentaire croisé aux `9rem` de la feuille** | `thumbnail` recadre en dur côté serveur, alors que le cadrage est une décision de présentation. Le couple `sizes`/largeur est **une valeur dans deux fichiers** : changer l'un sans l'autre livre un `srcset` qui ment au navigateur **sans symptôme visible** — d'où le commentaire croisé, obligatoire |
| 13 | **Hook `enqueue_block_editor_assets` depuis un module `blocks/`**, alors que le contrat #1 §2 le range dans `fields/` | **Ratifié** | Seul hook qui ne s'exécute **que** dans l'éditeur. Sur `init` 20, la dérivation des années hydraterait les 27 portées **à chaque requête publique**. Le contrat §2 impose un hook *d'enregistrement de bloc* (`init` 20, respecté) et n'interdit pas les autres. Signalé, pas dissimulé (**M7**) |

---

## 13. Interdits

- Le thème n'interroge **jamais** la base : `WP_Query`, `get_posts`, `get_post_meta`, `get_terms`,
  `$wpdb`. Frontière vérifiable par `grep`.
- Le thème n'appelle **jamais** `MTB\Core\*`.
- Le thème ne **décide jamais** de l'ordre des portées, ni de ce qu'est « la dernière ».
- Le thème ne **compose jamais** une chaîne du domaine : « Portée » + identifiant, « 3 mâles,
  2 femelles », un libellé de disponibilité, « Née le ».
- Le thème ne **reformate jamais** une date, un n° LOF, un résultat de test, un devenir de chiot.
- Le thème n'invente **jamais** une mention d'absence : ni tiret, ni « Aucun », ni « Non testé », ni
  « — ». Le serveur fournit « Non renseigné ».
- Le thème ne **réécrit jamais** « Toutes les portées » en « Voir toutes les portées ».
- **Aucun badge rendu depuis `affichage`.** Jamais un quatrième état de disponibilité.
- **Aucun `@media` qui masque de l'information** au lieu de la replier.
- L'extension n'émet **aucune règle visuelle ni mise en page**, à l'unique exception d'`alignwide`
  (§7.5, arbitrage 11), et **aucun style en ligne**.
- **Aucun transient, aucun cache maison** (contrat #1 §9) : `WP_Query` cache déjà dans le groupe
  `posts` contre `last_changed`, invalidé à chaque `save_post`.
- Aucun `add_cap`, aucun `add_role` : Fabienne reste **Éditeur** natif.
- **Aucun chemin d'écriture** : pas de `$_POST`, pas de `update_post_meta`, pas de `wp_insert_post`,
  donc **aucun nonce à poser**. La règle « nonce sur toute écriture » est **vide de sujet** ici, et
  c'est ce qu'il faut écrire dans le code plutôt que de poser un nonce décoratif. Le seul chemin
  privilégié est la route **du cœur** `/wp/v2/block-renderer/`, qui exige `edit_posts` et dont le cœur
  porte capacité et nonce.
- Aucune issue n'édite `mtb-core.php` ni `class-loader.php` ; aucun module n'appelle
  `flush_rewrite_rules()` ni n'emploie `init` 99.

---

## 14. Manques, dettes, et ce qui n'a pas pu être vérifié

### 14.1 Dettes créées ou révélées par cette issue

| # | Dette | Charge |
|---|---|---|
| **T-#13-a** | `.mtb-dispo`, `.mtb-photo` et l'apparence d'état vide §9.1 sont des **primitives partagées** que `MASTER.md` range dans `base.css` / `editor.css`, qu'**aucune chaîne ne possède**, et qui seront recopiées scopées dans jusqu'à **dix** feuilles de bloc | La chaîne propriétaire de `base.css` / `editor.css` après ce lot |
| **T-#13-b** | Le hissage devra **supprimer les copies scopées dans le même commit** : `.mtb-liste-portees .mtb-dispo` (0,2,0) battrait un `.mtb-dispo` (0,1,0) de `base.css` et **masquerait le hissage** | idem |
| **T-#13-c** | **Toute feuille de bloc de ce thème est probablement absente de l'éditeur.** `deps => array( 'mtb-jetons' )` (`functions.php:218`) référence une poignée enregistrée **uniquement** sur `wp_enqueue_scripts` (`functions.php:180`), qui ne tourne pas en administration ; `WP_Dependencies::all_deps()` écarte **silencieusement** une poignée dont une dépendance manque. **Concerne les six chaînes du lot.** Solde : enregistrer `mtb-jetons` aussi en administration, ou retirer la dépendance (les jetons arrivent déjà par `add_editor_style`) | **#18**, remonté au lead du lot |
| **T-#13-d** | **Aucune taille d'image dédiée** : le thème n'appelle `add_image_size()` nulle part. La vignette d'index se sert de `medium` du cœur, non recadré | #18 |
| **T-#13-e** | `MASTER.md` **renvoie à un §9.6 (impression) qui n'existe pas** (l. 676). Aucune règle d'impression n'existe dans le projet | `lead-design-mtb` |
| **T-#13-f** | La paire `--laiton-texte` / `--calcaire-creux`, **imposée par §9.1**, est **absente du §12** — donc interdite par la lettre du §13. Calculée à **4,75:1** (AA texte normal) par le plan front | `lead-design-mtb`, ajout au §12.3 |
| **T-#13-g** | `docker/fixtures/portees.json` emploie `chiots_disponibles` / `tous_reserves` / `portee_passee`, **hors de la liste close** : `champ_liste()` rend donc `valeur => ''` et **aucune fixture n'affiche de badge**. Déjà signalée par le contrat #3 §11, elle devient **bloquante pour la recette visuelle** de ce composant | L'issue d'import (#19-#21), ou une conversion des fixtures au niveau du lot |
| **T-#13-h** | `alignwide` émis par l'extension (§7.5). Forme propre : `.mtb-canal > .mtb-liste-portees` dans `base.css` | Propriétaire de `base.css` après ce lot |
| **M2** | **`query/portee/` n'expose ni la liste des années, ni une lecture légère.** Afficher cinq portées hydrate 27 fiches entières — chiots, galerie, parents, `alt` de chaque fichier joint ; la dérivation des années fait de même à chaque chargement de l'éditeur. **Nul à 27, à revoir bien avant 200** | La chaîne propriétaire de `query/portee/` |
| **M3** | `MASTER.md` §9.3 (« Voir toutes les portées ») contredit §10 (« Toutes les portées »), alors que §10 se déclare arbitre. Arbitré ici en faveur de §10 | `lead-design-mtb` |
| **M4** | `MASTER.md` **n'atteste aucun libellé d'option « tout »** pour un filtre fermé. « Toutes les années » est composé ; le cas reviendra pour la grille de chiens (statut) et le tableau de résultats (discipline) — **un libellé de lot vaudrait mieux que trois inventions** | `lead-design-mtb` |
| **M5** | **La catégorie `mtb` est due par une chaîne du lot qui n'est pas désignée.** Tant qu'elle manque : un avertissement de console dans l'éditeur, et le bloc rangé hors de « Mont Brabant » | Lead du lot |
| **M6** | `MASTER.md` ne chiffre **aucune largeur de vignette d'index** (§6.1 fixe le ratio, pas la dimension). `9rem` est une extrapolation étiquetée | `lead-design-mtb` |
| **M7** | Écart de hook ratifié (arbitrage 13) | ratifié ici |
| **M8** | §13 interdit tout dégradé hors `--filet-double` / `--voile-photo`, mais **§3.3 dessine la demi-pastille avec un dégradé**. On suit §3.3 | `lead-design-mtb` |
| **M9** | §6.2 veut un **point d'intérêt par image** ; l'extension ne peut pas émettre de style en ligne (contrat #1 §8), et aucun champ de point d'intérêt n'existe sur une portée. **Hors périmètre de cette issue** | `lead-design-mtb` + le lead du lot |

### 14.2 Ce qui n'a pas pu être vérifié, et doit l'être en conteneur

1. **Qu'un bloc enregistré en PHP + `registerBlockType` client apparaisse dans l'inséreur** sur la
   version de WordPress de la stack. Le contrat #1 §10 point 2 le laisse **expressément à vérifier au
   premier bloc livré**, et aucun bloc n'existe encore dans le dépôt : **c'est peut-être celui-ci.**
   Si l'observation contredit le contrat, **la remonter**.
2. **Le comportement de l'inséreur avec une catégorie inconnue**, et le libellé exact de
   l'avertissement de console.
3. **T-#13-c** — la présence ou l'absence réelle de `mtb-bloc-mtb-liste-portees` dans la toile de
   l'éditeur, et sa présence côté public. **Le résultat réel doit être consigné**, pas prédit.
4. **La résolution `getEntityRecords( 'postType', 'mtb_portee' )`** dans l'éditeur, et le rendu de
   `serverSideRender` sur une sortie vide.
5. **Les trois badges, l'entrée sans date et la vignette** sont **inobservables sur les fixtures
   actuelles** (T-#13-g, quatre portées toutes datées, aucune photo). La recette exige une **saisie
   manuelle** dans l'administration.
6. **Le repli à 360 px et le zoom 200 %** : à **mesurer en iframe**, jamais à prédire.
7. **Le cœur de WordPress est absent de l'arbre de travail** (volume Docker) : aucune référence de
   ligne du cœur dans les deux plans n'est de première main.

### 14.3 Questions ouvertes, aucune bloquante pour cette issue

Aucune n'est un fait d'élevage : **ce bloc n'introduit ni nom, ni date, ni généalogie, ni résultat.**

- **Largeur d'une vignette d'index** — `9rem`, extrapolation étiquetée, à ratifier (**M6**).
- **« L'index des portées », c'est l'archive `/portees/` ou une Page contenant ce composant ?** Les
  deux existeront techniquement, et le contrat #3 §3 **interdit** de créer une Page d'adresse
  `portees`. Position retenue, sans laquelle rien ne se livre : le lien de sortie vise
  `get_post_type_archive_link( 'mtb_portee' )`. Décide l'adresse que Fabienne communique, la cible des
  301 (D5) et le texte de la fiche d'aide.
- **Combien des 27 portées ont une photo utilisable, et les plus anciennes ont-elles une date et des
  compteurs ?** La réponse est sur le site source (`docs/migration/source/` est vide). **Ne bloque
  pas** l'approche retenue, conçue pour tenir dans tous les cas ; décide si la grille de cartes
  redeviendra un jour la bonne forme (section 2.1).
- **Fabienne veut-elle voir les portées de 1995 au même titre que celles de 2025 ?** Position retenue :
  oui — contrainte 4, rien ne se perd. Question d'élevage, pas de code.

---

## 15. Valeurs et clés gelées

Nom de bloc `mtb/liste-portees` · titre « Liste de portées » · catégorie déclarée `mtb`
(**non enregistrée ici**) · poignée de script `mtb-liste-portees-editeur` · poignée de feuille
`mtb-bloc-mtb-liste-portees` (dérivée par le thème) · fichier d'habillage
`assets/css/blocs/mtb-liste-portees.css` · espace de noms `MTB\Core\Blocks\ListePortees` ·
attributs `nombre` et `annee`, **tous deux de type `string`, défaut `""`** · taille d'image `medium`,
`sizes="144px"` ↔ largeur `9rem` · les **vingt crochets de classes** de la section 7.8 · les trois
variantes `mtb-dispo--disponible|--reserve|--passee` · les classes partagées `mtb-dispo`, `mtb-photo`,
`mtb-bloc-vide` / `__nom` / `__phrase`.

---

## 16. Obligations imposées aux autres issues

1. **La chaîne qui hisse `.mtb-dispo`, `.mtb-photo` ou l'apparence §9.1 supprime les copies scopées
   dans le même commit** (T-#13-b), et lit la liste des feuilles de bloc concernées.
2. **La chaîne propriétaire de la catégorie `mtb`** l'enregistre **une seule fois** ; ce module ne
   changera pas d'une ligne le jour où elle arrive.
3. **#18 solde T-#13-c** en rendant `mtb-jetons` disponible en administration, ou en retirant la
   dépendance de `mtb_feuilles_de_blocs()`. **Sans quoi aucune feuille de bloc du projet n'est visible
   dans l'éditeur** — pour les dix composants du catalogue.
4. **`lead-design-mtb`** traite M3, M4, M6, M8, M9, T-#13-e et T-#13-f.
5. **La conversion de `docker/fixtures/portees.json`** aux clés closes `disponible|reserve|passee`
   conditionne toute recette visuelle d'un badge de disponibilité, pour ce composant et les suivants.
6. **Aucune chaîne ne déclare `mtb_get_annees_*` ni aucune fonction globale de lecture sur les
   portées** en dehors de `query/portee/` (décision 19).

---

## 17. Amendements portés après implémentation

Ajoutés au gel initial le 2026-08-17, après les rapports de `dev-back-mtb`, `dev-ux-mtb`,
`refacto-mtb` et `dev-integration-mtb`, tous vérifiés en conteneur. Ils **complètent** le contrat, et
l'un d'eux **corrige une de ses prémisses**.

### 17.1 La prémisse de §7.5 est FAUSSE — `alignwide` est inerte, et c'est un défaut actuel

**§7.5 supposait que le bloc serait enfant direct de `.mtb-canal`.** Il ne l'est pas. Mesuré par
`dev-integration-mtb` sur une Page réelle à 1280 px : le bloc vit dans `.entry-content`
(`wp-block-post-content`, `is-layout-flow`), donc **petit-fils** du canal. Le sélecteur
`.mtb-canal > .alignwide` (`base.css:522-532`) **ne s'applique jamais** : `grid-column: auto / auto`,
`max-inline-size: none`, et le bloc rend à **576 px** — le canal de **texte**, jamais les 68 rem du
canal large que `MASTER.md` §7.1 lui assigne.

**Les deux côtés honorent pourtant le contrat** : l'extension émet la classe, la feuille de bloc
s'abstient de toute règle de canal. C'est le raisonnement de §7.5 qui était faux, pas le code.

**`alignwide` est CONSERVÉ**, et ce n'est pas de l'inertie : c'est la classe d'alignement standard de
WordPress, elle ne nuit pas, et elle rend le bloc **correct le jour où la plomberie du canal est
réparée**. La retirer ferait de ce composant le seul à rester hors du canal large après la
réparation — un défaut différé au lieu d'un défaut visible.

**`T-#13-h` change donc de nature : ce n'est plus une dette de propreté différée, c'est un défaut
actuellement visible**, et il concerne **tous** les blocs du catalogue qui visent le canal large, pas
seulement celui-ci. Le correctif est hors de l'empreinte de cette issue — une ligne de `base.css`
(cibler les descendants du canal plutôt que ses seuls enfants) ou du gabarit de Page.

### 17.2 Collision entre deux contrats gelés — #12 et #13 se contredisent sur les primitives partagées

**Trouvée par `dev-integration-mtb`, la seule instance de la chaîne à voir les deux côtés, et mesurée.**

- `docs/contracts/issue-13.md` §12 arbitrage 5 impose `.mtb-dispo` et `.mtb-photo` **scopées**.
- `docs/contracts/issue-12.md` T-#12-a impose **les mêmes classes nues**, « aucun renommage, seulement
  un déplacement », et charge « la deuxième issue qui rend un badge ou une photo » de les hisser dans
  `base.css`.

**#13 est cette deuxième issue, et son empreinte lui interdit `base.css`.** Les deux contrats sont
gelés, aucun n'a tort en isolation, et **la contradiction n'est visible qu'à la jonction**.

Conséquences mesurées sur une Page portant les deux blocs :

| Fuite | Effet | État |
|---|---|---|
| `white-space: nowrap` de `.mtb-dispo` **nue** (`mtb-derniere-portee.css`) | Le badge ne se replie plus. **`scrollWidth 400` pour `clientWidth 360` — défilement horizontal à 360 px, échec AA bloquant** | **Corrigé** : `white-space: normal` scopé, réaffirmation de la valeur initiale CSS, aucun jeton, aucune décision visuelle. Remesuré à **360/360** |
| `position: relative` + un `::after` de cerne complet, sur `.mtb-photo` **nue** | Ma vignette reçoit un cerne que ma feuille n'a pas dessiné | **Non corrigé, délibérément** : le neutraliser retirerait un cerne que `MASTER.md` §6.6 exige. Ce serait une décision visuelle, et elle n'appartient pas à cette chaîne |

C'est **T9 refaite en CSS**, exactement le mode d'échec que l'arbitrage 5 décrivait — sauf qu'il est
arrivé par une **contradiction entre deux contrats gelés**, et non par négligence d'une chaîne.
**À regeler au niveau du lot : qui possède les primitives partagées, et sous quelle forme.** Aucune des
deux chaînes ne peut trancher seule, et aucune ne peut écrire dans `base.css` pendant ce lot.

### 17.3 Précisions ratifiées

| Point | Décision |
|---|---|
| **`__nextHasNoMarginBottom: true`** sur `TextControl` et `SelectControl` | **Ratifié.** Sans ce drapeau, le cœur 6.9 émet *« Bottom margin styles for wp.components.* is deprecated since 6.7 »* à chaque ouverture du panneau. Il demande au composant **du cœur** son espacement courant : aucune règle visuelle de l'extension, aucun libellé gelé touché |
| **`fetchpriority="high"`** sur la vignette | ~~**Le cœur l'ajoute**, aucune ligne de `rendu.php` ne l'émet (vérifié par `grep`). §7.6 dit « pas de `fetchpriority` » et c'est tenu **au sens du code**. On ne combat pas l'heuristique LCP du cœur.~~ **RENVERSÉ, voir §17.5** |
| **`loading="lazy"`** dans le balisage de §7.1 | ~~Même famille : **c'est le cœur qui décide**, `rendu.php` ne l'émet pas. L'illustration de §7.1 montrait le HTML final, pas le code source.~~ **RENVERSÉ, voir §17.5** |
| **`sizes="auto, 144px"`** sur les images différées | Réécriture du cœur (mécanisme *auto-sizes* de 6.7+). Le couple `144px` ↔ `9rem` reste la valeur à tenir **des deux côtés** |
| `absint( $photo['id'] )` → **`(int) $photo['id']`** | **Correction de `refacto-mtb`, et son motif est juste** : `absint( -5 )` rend `5` et aurait servi à charger **une autre pièce jointe**, tout en rendant inatteignable la moitié `< 0` de la garde `<= 0`. Comportement identique sur toute donnée réelle, un identifiant de pièce jointe étant toujours positif |
| Volume de commentaires de la feuille | `refacto-mtb` l'a ramenée de **22 825 à 15 079 octets** sans toucher une règle (24 règles, 93 déclarations, confirmées par `dev-integration-mtb`), puis la correction de §17.2 la porte à **16 400 o / 94 déclarations**. Le poids restant n'est pas dans cette empreinte : `functions.php:217` fournit `path`, donc **la feuille est écrite EN LIGNE dans chaque page** portant le bloc, jamais mise en cache — à solder par **#18**, avec T-#13-c |
| **M5 est soldé** | La catégorie `mtb` **est** enregistrée, par le module `blocks/categorie-mtb/` d'une chaîne sœur. Observé : le bloc apparaît dans « Mont Brabant », aucun avertissement de console. **Deux chaînes sœurs posaient en outre le filtre en doublon** — hors empreinte, remonté au lead du lot |

### 17.4 Ce que la recette a confirmé, et ce qu'elle n'a pas pu atteindre

**Confirmé par observation, pas prédit** — les treize lignes du tableau de normalisation de §6.1
exécutées une par une · `render.php` ne déclare rien (**quatre** instances sur une Page, aucun
`Cannot redeclare`) · les trois badges, l'entrée sans date, sans compteurs, sans lien, sans photo · le
`<figure>` absent quand `wp_get_attachment_image()` rend `''` (pièce jointe sans fichier) · zéro
avertissement PHP sous `error_reporting( E_ALL )` · zéro origine tierce · la route
`block-renderer` en **401 anonyme, 200 avec nonce et `edit_posts`** · `javascript:` dans un lien → repli
`__nom` · **zéro sélecteur CSS orphelin** et les vingt crochets présents des deux côtés · 360 px et
640 px (zoom 200 %) sans défilement horizontal, **mesurés en iframe**.

**T-#13-c confirmé par observation** : la feuille est **présente côté public** (écrite en ligne) et
compte **0 occurrence dans les 683 Ko de `post.php`** — `mtb-jetons` n'y est enregistrée nulle part.
Les jetons atteignent la toile par `add_editor_style` (`functions.php:56`), **donc retirer la dépendance
est sûr**. Solde **#18**.

**Limite mesurée, non comblée** : sous un zoom du **texte seul** à 200 % (WCAG 1.4.4) **et** à 360 px,
`9rem` vaut 288 px ; 288 + 24 px de gouttière dépassent à eux seuls les 309 px du canal, **avant la
première lettre**. C'est de l'arithmétique : ni `min(100%, 9rem)` ni la coupure des mots n'y changent
rien. **Les deux exigences écrites — 360 px et zoom de page 200 % — sont tenues séparément.**
`MASTER.md` n'atteste aucune règle pour ce cas (§6.8 vise la fiche chien, pas une ligne de liste) :
question à `lead-design-mtb`, avec **M6**.

**Non atteint** : le listage à l'écran dans l'inséreur et l'encadré d'état vide **vus** (exécution de
JavaScript hors de portée des agents) — les preuves indirectes sont fortes, ce ne sont pas des
observations d'écran.

**Fuite hors périmètre, confirmée** : le **plan de site** (`wp-sitemap-posts-mtb_portee-1.xml`) et
l'**archive `/portees/`** divulguent une portée protégée par mot de passe. Le **bloc**, la recherche et
le flux ne divulguent rien. C'est la dette **T8**, due à **#23** — mais l'archive est la cible de
`__lien-index`, donc la jonction est concernée.

**Bruit du lot parallèle, à savoir pour lire les rapports** : le site public a répondu en **erreur
fatale** pendant plusieurs minutes (un module d'une chaîne sœur à moitié écrit), les fixtures de
portées ont été réduites de quatre à une par une chaîne sœur en cours d'essai, et une chaîne sœur a
piloté un navigateur sur la même base. Conformément au contrat #3 §18.5, **tout résultat rapporté ici a
été réobtenu après stabilisation**.

### 17.5 Correction — `loading` et `decoding` sont posés par le bloc (renverse §17.3)

Deux lignes de §17.3 laissaient au cœur le soin de décider du chargement des vignettes, au motif que
`rendu.php` n'émettant rien, la consigne « pas de `fetchpriority` » de §7.6 était tenue *au sens du
code*. **Le raisonnement était faux, et l'essai d'intégration du lot l'a mesuré.**

Ce qui a été observé sur le rendu réel, avant correction :

| Page | Mesure |
|---|---|
| `/ti3-les-six/` | `mtb-liste-portees__image` **sans `loading`** alors que la vignette est sous la ligne de flottaison |
| `/jonction-13-quatre/` | **deux** `mtb-liste-portees__image` portant `fetchpriority="high"` **en même temps** |

Deux priorités hautes simultanées s'annulent : le navigateur n'apprend rien de plus qu'avec aucune. Et
« tenu au sens du code » ne veut rien dire pour une exigence de performance, qui se lit **sur la page
livrée**, pas dans le source. Une liste de portées est une grille de vignettes de `9rem` — aucune n'est
le sujet d'ouverture, et le cœur ne peut pas le savoir.

`rendu.php` passe désormais `'loading' => 'lazy'` et `'decoding' => 'async'` à
`wp_get_attachment_image()`, **dans la forme et pour la raison des deux sœurs** qui documentaient déjà
le piège : `galerie-photos/rendu.php` (§10 arbitrage du contrat #8) et `grille-chiens/balisage.php:218`.
La valeur explicite bat `wp_get_loading_optimization_attributes()`, et `fetchpriority` n'est alors plus
émis du tout.

Mesuré après correction, sur les deux mêmes pages : `loading="lazy" decoding="async"` sur **toutes** les
vignettes, **zéro `fetchpriority`**. §7.6 est maintenant tenue au sens qui compte. La ligne
`sizes="auto, 144px"` de §17.3 reste exacte et le devient pour de bon — le préfixe `auto, ` du cœur
(6.7+) est **conditionné à `loading="lazy"`**, exactement comme sur la galerie sœur.

---

## 18. Regel du lot — deux points repris au niveau du lot (2026-08-17, reprise de chaîne)

La chaîne précédente est morte sur une limite de session **avant le commit**. Le code était sur le
disque, la fiche d'aide absente. Le lead du lot a rendu deux arbitrages qui **remplacent** des décisions
de ce contrat. Ils sont appliqués, pas contournés.

### 18.1 `.mtb-dispo` et `.mtb-photo` passent NUES — l'arbitrage 5 et les dettes T-#13-a/b sont ANNULÉS

**Ce qui est annulé** : l'arbitrage 5 de §12, l'exigence 8 de §11 (« portées SCOPÉES, jamais nues »),
les dettes **T-#13-a** et **T-#13-b**, et l'analyse de §17.2 en ce qu'elle recommandait le scopage.

**Ce qui les remplace** : `.mtb-dispo`, ses trois variantes, son `::before`, `.mtb-photo`, son cerne et
sa règle d'image sont écrites **nues**, exactement sous les noms que `MASTER.md` nomme (§3.3
`.mtb-dispo::before`, §6.2 `.mtb-photo > img`).

**Pourquoi l'arbitrage 5 tombe, alors que son raisonnement était juste.** Il l'était sur un point : deux
feuilles nues fusionnent dans la cascade. Il était faux sur sa conclusion, et la contre-analyse de la
chaîne #12 est décisive : **un descendant de `.mtb-liste-portees` est toujours un `.mtb-dispo`**. Les
règles nues de la feuille sœur atteignent donc nos badges de toute façon — c'est §17.2 qui l'a mesuré,
`white-space: nowrap` ayant fui dans notre badge. Le scopage n'a pas empêché la fusion : il l'a rendue
**asymétrique** (leur nue nous atteint, notre scopée ne les atteint pas) et **plus difficile à lire**.
**Le scopage ne protège que si tout le monde scope**, et `MASTER.md` ne nomme qu'**une** implémentation
du badge et de la photo. Le généraliser à quatre ou cinq composants institutionnaliserait la dette
**T9** — les trois assainisseurs divergents du premier jour.

**Ce que le nu achète** : les règles sont **hissables dans `base.css` sans renommage ni réécriture**,
comme le contrat #12 (T-#12-a) l'exige déjà. Le hissage devient un **déplacement**, non une
réconciliation de deux dialectes.

**Le hissage est une dette portée par le lead du lot**, payable par la deuxième issue qui rend un badge
ou une photo. **Cette chaîne n'ouvre pas `base.css`** — il est hors de son empreinte.

**Alignement délibéré sur `mtb-derniere-portee.css`** : là où `MASTER.md` ne nomme qu'une
implémentation, les déclarations de cette feuille sont écrites **à l'identique de la feuille sœur**,
pour que la fusion soit sans effet. En particulier le cerne du §6.6 passe par
**`.mtb-photo::after`** (surcouche absolue, `pointer-events: none`) et non par un `box-shadow` sur le
cadre — voir 18.3 — et la règle d'image emploie le sélecteur **doublé** `.mtb-photo.mtb-photo > img`,
qui pèse (0,2,1) et bat donc encore `img { block-size: auto }` de `base.css` **une fois préfixé dans la
toile de l'éditeur**.

**Deux divergences subsistent, nommées :**

| Déclaration | `mtb-derniere-portee.css` | Ici | Résolution |
|---|---|---|---|
| `white-space` de `.mtb-dispo` | `nowrap` | **`normal`** | **Question de lot, AA-bloquante.** `nowrap` **a été mesuré** à `scrollWidth 400` pour `clientWidth 360` sur une Page portant les deux blocs (§17.2). À spécificité égale (0,1,0), le gagnant dépend de l'ordre du registre de blocs : **indéterminé**. `normal` est la valeur **initiale** du CSS, aucune décision visuelle. **Le hissage DOIT retenir `normal`** ; retenir `nowrap` livre un échec AA. |
| `padding`, `gap`, `font-family` de `.mtb-dispo` | `padding: --e-2 --e-3`, `gap: --e-2`, `font-family: --sans` | valeurs propres | `MASTER.md` §5.1 **ne chiffre pas le rembourrage d'un badge** (la feuille sœur le signale aussi). Les deux sont des propositions. **Au hissage, une seule survit** ; l'écart est cosmétique et non bloquant. |

**Ce qui reste scopé, et ce n'est pas une entorse** : `.mtb-liste-portees__vignette` porte la
**dimension** (`9rem`), le **ratio** et le **rayon** — décisions d'**emplacement**, propres à une ligne
d'index, que `MASTER.md` n'attribue pas à la primitive partagée. La primitive porte le **traitement**
(fond, encre, cerne, cadrage de l'image) ; l'emplacement porte la **boîte**. Conséquence technique :
le `::after` du cerne reçoit `border-radius: inherit`, sinon un cerne à angles droits déborderait d'un
cadre arrondi.

### 18.2 L'état vide éditeur emploie les crochets de lot `mtb-etat-vide` — et cette feuille ne l'habille plus

**Ce qui est annulé** : le nom `mtb-bloc-vide` de §7.3, §7.8 et §15 ; l'arbitrage 6 de §12 en ce qu'il
retenait ce nom ; la **section 7 de la feuille de style** (apparence `__vide-editeur*`) ; la dette
**T-#13-f** en tant que dette de cette chaîne.

**Ce qui les remplace** — crochets **gelés au niveau du lot**, identiques pour les six composants :

```html
<div class="mtb-etat-vide mtb-liste-portees__vide-editeur">
  <p class="mtb-etat-vide__nom mtb-liste-portees__vide-editeur-nom">Liste de portées</p>
  <p class="mtb-etat-vide__phrase mtb-liste-portees__vide-editeur-phrase">Ce bloc n'affiche rien tant qu'aucune portée n'est publiée.</p>
</div>
```

`mtb-etat-vide` est le vocabulaire de `MASTER.md` §9.1 lui-même, et c'est le nom que les contrats **#8**
(arbitrage 10, « à diffuser aux cinq chaînes sœurs »), **#12** (E1-E3) et **#14** ont déjà gelé. Ce
contrat était **le dernier à porter `mtb-bloc-vide`** : le conserver aurait produit, pour une apparence
que `MASTER.md` déclare « identique pour les dix composants », **deux familles de crochets** — T9 en
CSS, exactement ce que l'arbitrage 6 voulait éviter.

**L'apparence appartient à `editor.css`** (§9.1 l'y place nommément), livrée par **#6**. **Cette chaîne
l'émet, ne la redéfinit pas.** Les trois classes locales `__vide-editeur*` sont **conservées dans le
balisage** sans aucune règle — même parti que #8 : le jour où l'apparence bouge, le JavaScript n'est pas
rouvert.

**Ce que la suppression de la section 7 ne coûte rien** : §17.4 a **constaté** (0 occurrence de
`mtb-bloc-mtb-liste-portees` dans les 683 Ko de `post.php`) que cette feuille **n'atteint pas la toile
de l'éditeur** — dette T-#13-c. Ces règles étaient **prouvées inertes**. La garantie qui portait
l'information reste entière : **deux `<p>`, lisibles à zéro CSS.**

**T-#13-c change de main** : le correctif d'une ligne dans `functions.php` (enregistrer `mtb-jetons`
aussi en administration, ou retirer la dépendance) appartient à **#6**, pas à #18. Une fois posé, la
grille de cartes et les badges sont à vérifier dans la toile.

### 18.3 Le cerne était peint SOUS l'image — défaut réel de cette feuille, corrigé ici

`--cerne-photo` vaut `inset 0 0 0 1px …` (`tokens.css:118`). Porté par le cadre, il se peint au-dessus
du fond de l'élément mais **sous ses descendants** ; l'`<img>` remplissant exactement la boîte
(`100 %` × `100 %`, sans rembourrage ni bordure), **le cerne est invisible dès que la photo charge** —
mesuré par la chaîne #12 sur des pixels, pas déduit. Or §6.6 l'exige sur **toute** photo.

Le `box-shadow` de la section 2 de la feuille est donc **remplacé** par la surcouche
`.mtb-photo::after` de la feuille sœur, `border-radius: inherit` en plus (18.1). Ce n'est **pas** une
décision visuelle nouvelle : c'est la seule forme qui rende le jeton visible tel quel.

### 18.4 Les quatre décisions autonomes de la chaîne précédente sont RATIFIÉES

Le filtre par année est un **réglage d'insertion** (§2.3) · **aucune pagination** (§2.3) · **aucun
`data-libelle`** (§2.2) · le lien de sortie lit **« Toutes les portées »** (arbitrage 7).

Sur ce dernier point, la divergence interne de `MASTER.md` reste **signalée pour #16 et #17** : §9.3
(l. 818) écrit « Voir toutes les portées », §10 (l. 929) écrit « Toutes les portées », et **§10 se
déclare arbitre du vocabulaire, donc §10 fait foi**. Manque **M3**, à corriger dans `MASTER.md`.

### 18.5 Vocabulaire visuel partagé avec l'encart de la chaîne #12

Nommé ici parce que `archive-portee.html` (**#16**) appellera ce rendu, et que les deux composants ne
doivent pas devenir des dialectes :

| Point | Ce composant (#13) | L'encart (#12) |
|---|---|---|
| Forme | **liste** de lignes, vignette facultative (§2.1) | **une** portée, à l'échelle d'accroche |
| Filet double `--filet-double` (§3.3) | **jamais** | **oui**, quand la disponibilité est *Chiots disponibles* |
| Badge de disponibilité | `.mtb-dispo` **nue**, trois variantes (18.1) | idem, **même implémentation** |
| Traitement de la photo | `.mtb-photo` **nue** — fond, encre, cerne `::after`, `object-fit: cover`, point d'intérêt `50% 38%` | idem, **même implémentation** |
| Ratio du cadre | `--r-carre`, propre à la ligne d'index | `--r-paysage` `3 / 2` |

Le **traitement** est commun et hissable ; le **ratio** et la **dimension** appartiennent à
l'emplacement.

### 18.6 Fonction de rendu réutilisable — interface pour #16 et #17

La décision 17 place les trois types `mtb_` sur l'écran d'édition classique : **aucun bloc ne peut être
inséré dans une fiche**. `archive-portee.html` (#16) et `page-placement.html` (#17) ont pourtant besoin
**exactement** de ce rendu.

L'entrée réutilisable est donc **la classe de rendu du module**, appelée sous garde :

```php
if ( class_exists( '\MTB\Core\Blocks\ListePortees\Rendu' ) ) {
    echo \MTB\Core\Blocks\ListePortees\Rendu::bloc( array( 'nombre' => '', 'annee' => '' ) );
}
```

- `Rendu::bloc( array $attributs ): string` — mêmes attributs que le bloc (`nombre`, `annee`, tous deux
  `string`), **assainis par `Rendu::normaliser()`** : un appelant ne peut pas lui passer une valeur sale.
- Rend **la chaîne vide** quand rien n'est publié, l'état vide public quand un filtre d'année ne rend
  rien (§7.2), la liste sinon (§7.1). **L'appelant n'a aucune décision d'état à prendre**, donc aucune
  règle métier ne remonte dans un gabarit.
- Un gabarit de thème **ne peut pas** l'appeler : la frontière stricte l'interdit (§13). #16 et #17
  passent par `render_block()` / un motif contenant le commentaire de bloc `wp:mtb/liste-portees`, ou
  demandent une fonction de lecture à `query/portee/`. **Le nom de classe ci-dessus n'est PAS une
  interface de thème** : il est documenté pour un appelant **côté extension**.

### 18.7 Empreinte de la reprise — corrigée

```
wp-content/plugins/mtb-core/includes/blocks/liste-portees/**
wp-content/themes/mtb/assets/css/blocs/mtb-liste-portees.css
docs/contracts/issue-13.md
docs/guide/composant-liste-portees.md      ← nom imposé au lot : composant-<nom-du-bloc>.md
```

§1 annonçait `docs/guide/composant-liste-de-portees.md` : **faux**. La convention du lot est
`composant-<nom-du-bloc>.md`, et le bloc s'appelle `liste-portees`.

---

## 19. Amendement du 2026-08-18 — §18.6 reste bon, la règle qu'il appliquait a bougé

**Ce qui change** : le contrat #1 §5 a été amendé le 2026-08-18. Il admet désormais **une** catégorie
de fonctions globales en plus des fonctions de lecture : les **fonctions de composant** — rendre le
balisage d'un composant, ou répondre à la question d'état qui décide de ce rendu. Cinq conditions
cumulatives, dont la cinquième : **jamais une lecture de données** (décision 19 — le type qui possède
la donnée possède sa lecture). Le détail est dans l'amendement 1 du contrat #1, en fin de fichier.

**Ce que devient §18.6** : sa décision — exposer la **classe de rendu sous espace de noms**,
`\MTB\Core\Blocks\ListePortees\Rendu::bloc()`, appelée sous garde `class_exists()` — **reste retenue**.
Elle ne coûte aucune surface globale, elle assainit ses attributs elle-même, et elle ne laisse aucune
décision d'état à son appelant. Rien à changer dans le code livré.

**Ce qui demande une nuance** : la phrase « **le nom de classe ci-dessus n'est PAS une interface de
thème** » reste vraie **pour ce module**, et pour la bonne raison — la frontière stricte du §13. Mais
elle ne décrit plus l'état du projet : trois chaînes sœurs du même lot ont livré une fonction de
composant globale, destinée précisément à être appelée depuis un gabarit — #6, #7, #14. Si un jour
`liste-portees` en avait besoin, la porte existe désormais, aux cinq conditions du §5 amendé. **La
ligne du §18.6 n'est pas réécrite** : elle se lit avec cet amendement.

**Une réserve que §18.6 ne pouvait pas voir, et qui vaut pour tout le monde** : dans un thème de blocs,
**un gabarit est un fichier HTML et n'exécute aucun PHP**. `archive-portee.html` (#16) ne peut donc
appeler ni `Rendu::bloc()`, ni `render_block()`, ni une fonction de composant. Son chemin réel est
l'insertion du bloc par son commentaire :

```html
<!-- wp:mtb/liste-portees {"nombre":"","annee":""} /-->
```

**Ce chemin convient à ce module** : la liste se remplit toute seule à partir de ce qui est publié, et
ses deux attributs ont des valeurs par défaut qui veulent dire « tout ». C'est la galerie d'une fiche
qui n'a pas de réponse, parce que son bloc prend un attribut `photos` explicite qu'un gabarit ne peut
pas connaître. Point ouvert consigné dans l'amendement 1 du contrat #1, **à trancher au brainstorm de
#16/#17**.

**Enfin, un constat du §18.6 devenu faux** : « le bloc vit dans `.entry-content`, petit-fils du canal,
la règle `.mtb-canal > .alignwide` ne s'applique jamais » (dette T-#13-h, également écrite en tête de
`mtb-liste-portees.css`). **Soldé par #6** : `templates/singular.html` donne maintenant la classe
`mtb-canal` au contenu lui-même, et `base.css` corrige le `margin-inline` qui annulait l'étirement de
l'élément de grille. `alignwide` est actif sur ce module. Le commentaire de la feuille est resté en
arrière et **appartient à une chaîne de correction, pas à ce document**.

---

## Amendement du 2026-08-18 — le canal large passe côté thème, `alignwide` est retiré

**Sections amendées : §7.5, §12 (ligne `alignwide`), §13 (l'« unique exception »), l'arbitrage 11, la
dette T-#13-h et le §17.1.** Ce qu'elles décrivent est l'état d'avant ; ce qui suit est l'état livré.

**Ce qui a changé sous le contrat.** Le §7.5 et l'arbitrage 11 reposaient tous deux sur une prémisse
d'infrastructure : « le thème ne peut pas gagner », parce que le bloc était le *petit-fils* du canal et
que `base.css` appartenait à une chaîne sœur. Le commit `ebdbf3a` a posé `mtb-canal alignfull` sur
`post-content` lui-même (`templates/singular.html:4`) : le bloc est désormais **enfant direct** d'un
`.mtb-canal`, et la feuille du thème peut lui affecter son canal sans toucher à `base.css`. Le §17.1
avait déjà constaté la moitié du basculement — `alignwide` était devenu **actif** — sans en tirer la
conséquence : la classe n'était plus nécessaire.

**Ce qui est livré.**

- `includes/blocks/liste-portees/rendu.php` n'émet plus **aucune** classe de mise en page. Les classes
  du `<div>` racine sont `mtb-liste-portees` et, à l'état vide, `mtb-liste-portees--vide`.
- `themes/mtb/assets/css/blocs/mtb-liste-portees.css` ouvre sur une **section 0** qui affecte le canal :
  `.mtb-canal > .mtb-liste-portees { grid-column: large-debut / large-fin; inline-size: 100%;
  max-inline-size: var(--l-large); margin-inline: auto }`. Spécificité (0,2,0) contre le (0,1,0) de
  `base.css:515` : gagne sans `!important` et sans dépendre de l'ordre des feuilles.
- **Le §13 redevient sans exception** : l'extension n'émet aucune règle visuelle ni mise en page. La
  ligne `alignwide` du tableau §12 est supprimée du balisage gelé ; le reste du balisage est inchangé.
- **Dette T-#13-h : soldée.** Elle appelait « `.mtb-canal > .mtb-liste-portees` dans `base.css` » ; la
  règle est écrite dans la feuille du bloc, qui appartient au thème tout autant et n'oblige personne à
  rouvrir le fichier partagé par six chaînes.

**Mesuré, page `/ti3-les-six/`, avant → après** (bords calculés, viewport 1425 px) :

| Composant | Avant | Après | Canal voulu par MASTER §7.1 |
|---|---|---|---|
| Liste de portées | 168 → 1256 (par `alignwide`) | **168 → 1256** (par le thème) | large — « listes de portées » |
| Grille de chiens | 424 → 1000 | **168 → 1256** | large — « grilles de chiens » |
| Galerie (témoin, hors empreinte) | 168 → 1256 | 168 → 1256 | large — « galeries » |

Rendu **identique au pixel près** pour ce module : le déplacement ne change pas la page, il change qui
décide. À 360 px (iframe de même origine) : `scrollWidth == clientWidth == 345`, aucun défilement
horizontal. À 200 % de zoom sur 1280 px : `scrollWidth == clientWidth == 1265`, tous les composants à
96 → 1169.

**Limite connue, sans effet aujourd'hui.** Dans un `.mtb-canal` porteur de `is-layout-constrained`,
`base.css:507-510` neutralise `max-inline-size` et impose `margin-inline: 0 !important` à (0,3,0) sur
tout enfant direct qui n'est pas `.alignwide` : la liste resterait dans la piste large, mais sans
plafond ni centrage. Aucun placement de ce genre n'existe — les blocs vivent dans `post-content`, qui
est `is-layout-flow`. La contourner demanderait un `!important` ou un sélecteur doublé, refusés tant que
le défaut n'est pas constaté.
