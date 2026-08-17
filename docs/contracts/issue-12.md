# Contrat d'interface — Issue #12 — Composant Encart « dernière portée »

**Gelé le 2026-08-17.** Sixième contrat du projet, **premier à livrer un bloc** et premier à faire
apparaître une donnée d'élevage sur une URL publique.

Il s'applique **par-dessus** `docs/contracts/issue-1.md` (règle générale de tout module de
`mtb-core`), `issue-2.md` (le thème, ses poignées et ses feuilles) et `issue-3.md` (le type Portée et
ses six fonctions de lecture), qu'il **consomme sans les amender** — à deux exceptions près, ratifiées
en §9 et signalées comme telles.

> **Cette issue paie le premier acompte sur la dette T10.** Jusqu'ici, D1 (« les valeurs apparaissent
> sur l'URL publique ») et D2 (« une saisie, quatre endroits ») n'étaient vérifiées **qu'au niveau des
> fonctions de lecture**. Ce bloc est le premier consommateur de `mtb_get_derniere_portee()` : c'est
> par lui que D1 et D2 deviennent vérifiables **dans le HTML**.

---

## 1. Ce que l'issue livre

Un bloc à **rendu serveur**, sans réglage de contenu, qui affiche la portée née le plus récemment et
se met à jour tout seul dès qu'une portée est publiée. **Un seul réglage** : un titre d'accroche
facultatif.

```
wp-content/plugins/mtb-core/includes/blocks/derniere-portee/**
wp-content/themes/mtb/assets/css/blocs/mtb-derniere-portee.css      ← fichier NEUF, voir §2
docs/contracts/issue-12.md
docs/guide/composant-encart-derniere-portee.md
```

**Aucun autre fichier.** Ni `functions.php`, ni `theme.json`, ni `base.css`, ni `editor.css`, ni
`tokens.css`, ni `templates/`, ni `parts/`, ni `patterns/`, ni `includes/query/**`, ni aucun autre
dossier de `includes/blocks/` — les cinq chaînes sœurs du lot (#6 `bandeau-ouverture`, #7
`fiche-information`, #8 `galerie-photos`, #13 `liste-portees`, #14 `grille-chiens`) travaillent dans
le même arbre de travail, sur `main`, sans isolation.

### Ce que l'issue ne livre pas, et pourquoi

| Non livré | Motif |
|---|---|
| Les **parents** | Exigeraient la fonction de lecture de **#4** pour le nom d'usage et le nom complet avec affixe (`MASTER.md` §7.4 point 2) : **dépendance croisée vers une chaîne sœur du même lot, interdite**. Et `pere['nom']` est le `post_title` de la fiche, dont `issue-3.md` §9.2 dit qu'il « n'est pas nécessairement le nom d'usage » : l'imprimer afficherait un nom approximatif — **D11** |
| Le **tableau des chiots** | Exigerait `data-libelle` (décision 10) et la bascule sous 48 rem. C'est le tableau de la fiche Portée, pas d'un encart de 36 rem |
| La **galerie** | Composant #8 |
| Le **commentaire de l'éleveuse** | `post_content` **n'est pas exposé** par la fonction de lecture. L'obtenir demanderait une septième fonction, hors empreinte |
| `annee`, `males`, `femelles` **pris séparément** | Imprimer `males['affichage']` ferait apparaître « Non renseigné » là où l'encart doit **se taire**. Seul `effectif_texte` est rendu, **jamais recomposé** |
| Une **fonction de lecture nouvelle** | `mtb_get_derniere_portee()` suffit **intégralement**. Décision 19 : le type qui possède la donnée possède la lecture |
| Un `example` dans `block.json` | Déclencherait un rendu serveur par requête REST au survol dans l'inséreur. L'identité du composant est portée par son titre, sa description et sa fiche d'aide |
| Un **choix de portée**, un **nombre de portées**, un **format de date** | Chacun contredirait la contrainte 3 : la dernière portée est **calculée, jamais choisie** (BRIEF §3), et la date suit les réglages du site |
| Une **règle `@media print`** | `MASTER.md` §7.6 renvoie à un « §9.6 » **qui n'existe pas** ; le contrat #2 amendement 5 l'a déjà consigné. Inventer une feuille d'impression serait la première décision visuelle hors document du projet |

---

## 2. Où vit la feuille de style — arbitrage rendu

**Elle vit dans le thème : `wp-content/themes/mtb/assets/css/blocs/mtb-derniere-portee.css`.**

Vérifié dans le code, pas déduit. `wp-content/themes/mtb/functions.php:194-224`,
`mtb_feuilles_de_blocs()` sur `wp_loaded` :

- l. 195 — itère `WP_Block_Type_Registry::get_instance()->get_all_registered()` : **le registre,
  jamais le disque** (`glob`, `scandir`, `opendir` sont réputés désactivés sur le mutualisé visé ;
  seul `file_exists()` touche le système de fichiers) ;
- l. 198 — garde `preg_match( '#^[a-z0-9-]+/[a-z0-9-]+$#', … )` : **`mtb/derniere-portee` passe** ;
- l. 202 — `$base = str_replace( '/', '-', $nom )` → **`mtb-derniere-portee`** ;
- l. 203 — `assets/css/blocs/mtb-derniere-portee.css` ;
- l. 210-221 — `wp_enqueue_block_style()`, poignée **`mtb-bloc-mtb-derniere-portee`**, `deps` =
  `array( 'mtb-jetons' )`, `path` renseigné (le cœur peut incorporer la feuille en ligne),
  `ver` = `filemtime()`.

`wp_loaded` s'exécute après `init` : un bloc enregistré sur `init` **20** est dans le registre.
**Déposer le fichier suffit. Aucune ligne ailleurs.** `functions.php` reste fermé (décision 9), et le
commentaire de `functions.php:188` le dit lui-même : « Déposer `assets/css/blocs/core-image.css`
suffit à l'habiller : rien à déclarer ici. »

**Pourquoi le thème et non le dossier du bloc**, alors que la consigne initiale du lot disait
l'inverse — trois raisons, dont une décisive :

1. `CLAUDE.md` (« l'extension ne produit aucune mise en page décorative ») et `issue-3.md` §13
   (« l'extension n'émet aucune règle visuelle ni mise en page ») rangent le CSS dans l'extension
   **dans les interdits**. Ce n'est pas une préférence de style.
2. **Dépendance inversée — l'argument décisif.** Les jetons (`--sauge`, `--filet-double`, `--e-6`,
   `--r-paysage`) vivent dans `tokens.css`, **dans le thème**. Une feuille servie par l'extension
   consommerait des variables que l'extension ne possède pas et ne peut pas déclarer, et `block.json`
   n'offre **aucun moyen propre** de déclarer une dépendance de style vers `mtb-jetons`. On aurait une
   feuille d'extension inutilisable sans le thème : le pire des deux mondes.
3. C'est la raison d'être de la boucle générique du contrat #2. Une seconde convention pour la même
   chose, sur un thème qui en a déjà une câblée, produirait **six conventions à la sortie de ce lot**.

**Empreinte étendue d'exactement un fichier neuf, au nom dérivé du nom du bloc** : aucune collision
possible avec une chaîne sœur, dont les feuilles s'appelleraient `mtb-bandeau-ouverture.css`,
`mtb-liste-portees.css`, etc. Les trois fichiers nommément interdits (`base.css`, `theme.json`,
`functions.php`) **ne sont pas touchés**.

*Note technique vérifiée, pour la mémoire du projet : `"style": "file:./style.css"` **n'exige aucun
fichier `.asset.php`** — la contrainte du contrat #1 §10 ne vise que les **scripts**
(`register_block_script_handle`), jamais les feuilles. La branche « CSS dans l'extension » était donc
techniquement viable ; elle est écartée pour les trois raisons ci-dessus, pas pour un obstacle
technique.*

---

## 3. Le nom du bloc est le point de rupture silencieux de cette issue

Les deux moitiés du travail ne sont pas couplées par une fonction PHP : elles sont couplées par une
**chaîne de caractères**.

> **`mtb/derniere-portee`** — minuscules, un tiret entre les mots, **sans accent** (`derniere`, pas
> `dernière`), sans tiret bas.

Si le `block.json` nommait le bloc `mtb/encart-derniere-portee`, `mtb/derniere_portee` ou
`mtb/dernière-portee`, la boucle de `functions.php:203` chercherait un autre fichier, `file_exists()`
renverrait `false`, `continue` — **aucune erreur, aucun avertissement, un encart sans style en
production, sur un site qui répond 200.** C'est le premier item de ce contrat.

---

## 4. Ce que le bloc consomme

Une seule fonction, déjà gelée par #3, appelée **une fois par rendu**, **derrière
`function_exists()`**, sans argument, sans transient, sans cache maison :

```php
mtb_get_derniere_portee(): ?array
```

**Aucune requête directe.** Ni `WP_Query`, ni `get_posts`, ni `get_post_meta`, ni `get_terms`, ni
`$wpdb`, ni aucune fonction de lecture nouvelle. Décision 19 : le chargeur emploie `scandir()`, qui
parcourt par ordre alphabétique — deux fonctions homonymes ne lèvent pas d'erreur mais produisent un
**ombrage silencieux**, la mauvaise implémentation gagnant sans un mot.

### 4.1 Trois garanties par construction, vérifiées dans le code — elles dispensent de gardes

`mtb_get_derniere_portee()` (`includes/query/portee/bootstrap.php:51-60`) parcourt
`Hydratation::liste()` et renvoie **le premier élément dont `date_naissance['valeur']` est non vide**,
sinon `null`.

| Garantie | Preuve |
|---|---|
| `date_naissance['valeur']` est **toujours non vide** | `bootstrap.php:54` — le test `'' !== …['valeur']` **est** la condition de retour |
| L'état `page_protegee` est **inatteignable** par cette fonction | `hydratation.php:96` — `Hydratation::contenus()` passe `'has_password' => false` : une portée protégée n'est jamais dans la liste, et ne peut donc jamais devenir « la dernière » |
| `statut` vaut toujours `publish` | idem, `'post_status' => 'publish'` |

**Conséquence directe et contractuelle : la ligne de date est rendue sans aucun test.** C'est ce qui
rend tenable l'exigence de `MASTER.md` §3.3 — « le badge est toujours accompagné, en lecture d'écran
comme à l'œil, de la date de naissance » — **sans une seule condition**. Aucun développeur n'ajoute de
garde sur la date : elle serait du code mort qui laisserait croire que le cas existe.

*Si une issue future assouplit ces garanties, elle doit rouvrir ce contrat : le badge devrait alors
suivre la date, et l'omettre quand la date est absente — un badge orphelin n'est pas un état prévu par
§3.3.*

### 4.2 Clés réellement lues — telles que `hydratation.php` les livre

| Clé | Type réel | Ce que le bloc en fait |
|---|---|---|
| `etat` | `string`, vaut `'ok'` | **Garde** : tout ce qui n'est pas `'ok'` ⇒ sortie vide |
| `titre_public` | `string`, **jamais vide** (vaut `'Portée'` seul si le titre l'est, `hydratation.php:250`) | Imprimé, `esc_html` |
| `lien` | `string` | Lien rendu si `'' !== …` |
| `date_naissance` | `array( libelle, valeur, affichage )`, `valeur` **jamais vide** (§4.1) | `libelle` et `affichage` imprimés dans **deux éléments distincts**, sans condition |
| `disponibilite` | `array( libelle, valeur, affichage )` | Badge **si et seulement si `'' !== valeur`** ; imprime `affichage` ; `valeur` sert au crochet de classe |
| `effectif_texte` | `string` **nue**, pas une enveloppe | Imprimée **telle quelle** si non vide |
| `photo` | `array( id, alt )` **ou `null`** | `is_array()` ⇒ image ; sinon **aucun élément** |

**Non lues, délibérément** : `id`, `identifiant`, `statut`, `protege`, `annee`, `males`, `femelles`,
`pere`, `mere`, `chiots`, `chiots_colonnes`, `chiots_message`, `galerie`.

### 4.3 Trois écarts entre `issue-3.md` et le code réel — consignés, aucun ne justifie de patcher `query/portee/`

Tous les trois vont dans le sens « le code est plus sûr que le contrat ». Ils sont écrits ici pour que
**les cinq autres composants ne les redécouvrent pas un par un.**

1. **`photo` peut valoir `null`.** `hydratation.php:276` appelle `self::photo()`, de type de retour
   `?array` (l. 458). Or `issue-3.md` §9.2 promet « toutes les clés sont toujours présentes : `''`
   pour une chaîne, `array()` pour une liste, `0` pour un identifiant » — **`null` n'est pas dans
   cette liste**. → **Le test est `is_array( $portee['photo'] ?? null )`**, jamais `'' !== …`, jamais
   `! empty( …['id'] )`. Vaut pour tout composant qui rend une photo.
2. **`effectif_texte` n'est pas une enveloppe** mais une chaîne nue (l. 269) — volontairement, et
   c'est cohérent avec `issue-3.md` §11 (« l'effectif … **n'existe pas** si les deux compteurs sont
   vides »), alors que §19.5 dit « **toute** valeur exposée » est enveloppée. Le code a raison, le
   contrat #3 est trop absolu sur ce point.
3. **`titre_public` n'est jamais vide** (l. 250). Aucun composant n'a à garder cette clé.

---

## 5. Le bloc enregistré

### 5.1 `block.json` — valeurs gelées

| Clé | Valeur |
|---|---|
| `$schema` | `https://schemas.wp.org/trunk/block.json` — chaîne inerte dans un JSON, **aucune requête réseau**, ni au rendu ni dans l'éditeur. D6 intacte |
| `apiVersion` | `3` |
| `name` | **`mtb/derniere-portee`** (§3) |
| `title` | **« Encart dernière portée »** — nom du composant de `MASTER.md` §9.1 |
| `category` | **`mtb`** — gelée par `issue-1.md` §10. **Dépend du module `categorie-mtb/`, hors empreinte : voir §11** |
| `icon` | `pets` — dashicon du cœur, **auto-hébergé**, le même que le `menu_icon` du type Portée (`issue-3.md` §3) : la même image désigne la même chose aux deux endroits où elle apparaît. Aucun SVG, aucune police d'icônes |
| `description` | « Affiche la portée née le plus récemment, avec sa date de naissance et sa disponibilité. Elle se met à jour toute seule dès qu'une nouvelle portée est publiée : il n'y a rien à recopier. » |
| `keywords` | `[ "portée", "portee", "chiots", "disponibilité", "disponibilite", "accueil" ]` — jamais affichés, seulement cherchés ; les variantes sans accent sont gratuites et couvrent une normalisation de diacritiques incertaine |
| `textdomain` | `mtb-core` |
| `attributes` | **`{ "accroche": { "type": "string", "default": "" } }`** — un seul, voir §5.2 |
| `render` | `file:./render.php` |
| `editorScript` | **`mtb-derniere-portee-editeur`** — une **poignée**, jamais un `file:` |
| `style`, `editorStyle`, `viewScript`, `viewStyle`, `example`, `variations`, `usesContext`, `providesContext`, `selectors` | **absentes** |

`supports`, entrée par entrée :

| Entrée | Valeur | Pourquoi |
|---|---|---|
| `align` | **`false`** | `MASTER.md` §14 range « la largeur d'une colonne » dans ce que l'éditrice **ne peut pas atteindre**. Et c'est **inopérant** ici : l'encart est enfant de `.entry-content`, plafonné à 36 rem — une commande qui ne fait rien est un piège, pas une liberté |
| `anchor` | **`false`** | Le champ du cœur s'intitule « Identifiant HTML », terme technique à l'écran (§10.4, §13) |
| `customClassName` | **`false`** | §14 lui refuse « une classe personnalisée » ; garantit aussi qu'aucune classe non préfixée `mtb-` n'entre dans le HTML sans venir du cœur |
| `className` | `true` (défaut, non écrit) | Produit `wp-block-mtb-derniere-portee`, classe **du cœur** |
| `html` | **`false`** | Un bloc à rendu serveur n'a pas de HTML éditable : « Modifier en HTML » ne pourrait que lui montrer du balisage et l'inquiéter |
| `multiple` | **`false`** | Deux encarts sur une page imprimeraient **deux fois la même portée** — et, à l'état *Chiots disponibles*, **deux filets doubles** dans la même section, interdit nommé de §2.1 et §13. Le cœur grise l'entrée dans l'inséreur dès qu'un encart est présent |
| `reusable` | **`false`** | Convertir en motif synchronisé un bloc qui se remplit tout seul ajoute une indirection pour un gain nul, et rend faux le « où ce composant est-il utilisé » de sa fiche d'aide |
| `interactivity`, `color`, `typography`, `spacing`, `dimensions`, `border`, `shadow`, `background`, `layout`, `position`, `filter` | **non déclarées** | Elles valent `false` par défaut, et `theme.json` verrouille déjà globalement. Les déclarer serait du bruit sans effet |

### 5.2 Le titre d'accroche — défaut vide, et aucun repli

`accroche` : `string`, **défaut `""`**.

> **Vide ⇒ aucun élément de titre dans le HTML.** Pas de `<h2>` vide, pas de chaîne de repli,
> **aucun défaut « Dernière portée ».**

Ce n'est pas de la propreté de balisage, c'est une conséquence visuelle mesurable :
`base.css:201-216` donne à tout `h2` un `padding-block-end`, un `margin-block: --e-7 --e-4` et un
fond en filet. Un `<h2></h2>` vide dessinerait **un trait double de 6 px flottant dans le vide,
entouré de 64 px de blanc**.

Et le libellé « Dernière portée » **n'est attesté ni en `MASTER.md` §10.2 ni en §10.3** : il n'existe
qu'en §9.1 comme **nom du composant** et en BRIEF §6. Le poser en défaut publierait sur l'accueil un
titre que le système de design n'a jamais validé pour un affichage public — et le faire composer par
le serveur ne changerait rien au problème, qui est éditorial, pas architectural.

Ce que l'éleveuse voit dans le panneau latéral, **et rien d'autre** :

| Élément | Valeur exacte |
|---|---|
| Titre du panneau (`PanelBody`) | **« Réglages de l'encart »** — libellé composé, non attesté, ratifié ici (aucun fait d'élevage ; `MASTER.md` n'atteste aucun titre de panneau d'inspecteur) |
| Étiquette du champ (`TextControl label`) | **« Titre d'accroche »** — les mots de l'énoncé de l'issue (« seul le titre d'accroche est réglable »), donc **attestés, pas inventés** |
| Aide (`TextControl help`) | **« Facultatif. Laissé vide, aucun titre n'apparaît au-dessus de l'encart. L'encart montre toujours la portée née le plus récemment : il n'y a rien à choisir. »** |
| `placeholder` | **absent, délibérément** — `MASTER.md` §13 interdit « un `placeholder` tenant lieu d'étiquette ». Aucun texte fantôme même en plus de l'étiquette : il ne servirait qu'à **suggérer** une accroche, donc à en inventer une |

Mots interdits (`MASTER.md` §10.4) vérifiés absents de **tout** ce qui atteint l'écran — `title`,
`description` et `keywords` du `block.json` compris : ni `slug`, ni `permalien`, ni `meta`, ni `champ
personnalisé`, ni `taxonomie`, ni `extrait`, ni `média`, ni `alt`, ni `template`, ni `CPT`. Le mot
« bloc » seul est employé : `MASTER.md` §9.1 l'emploie lui-même (« Ce bloc n'affiche rien tant
que… ») et c'est le mot du cœur.

### 5.3 Arborescence du module — quatre fichiers

```
wp-content/plugins/mtb-core/includes/blocks/derniere-portee/
├── bootstrap.php   add_action( 'init', …, 20 )
│                     wp_register_script( 'mtb-derniere-portee-editeur',
│                       MTB_CORE_URL . 'includes/blocks/derniere-portee/editeur.js',
│                       array( 'wp-blocks', 'wp-element', 'wp-block-editor',
│                              'wp-components', 'wp-server-side-render' ),
│                       MTB_CORE_VERSION, true )
│                     register_block_type( __DIR__ )
│                   — rien d'autre : aucun require_once, aucun autre hook, aucune sortie
├── block.json      §5.1
├── render.php      inclus par WordPress au rendu, JAMAIS par le chargeur
│                   ($attributes, $content, $block en portée locale)
└── editeur.js      wp.blocks.registerBlockType + wp.element.createElement
                    AUCUN JSX, AUCUNE étape de build, aucun package.json, aucun build/
```

**Aucun `style.css` dans l'extension** (§2). `class-loader.php:153-156` n'inclut que `bootstrap.php` :
un `render.php` posé dans le dossier n'est **jamais** inclus à vide, c'est WordPress qui l'inclut au
rendu. Aucun conflit.

Les deux pièges du contrat #1 §10, traités nommément :

- `"editorScript": "file:./editeur.js"` est **proscrit** : WordPress chercherait un `editeur.asset.php`
  normalement produit par `@wordpress/scripts` et émettrait un `_doing_it_wrong`. On enregistre le
  script soi-même et `block.json` ne porte que **la poignée**.
- **Un bloc enregistré côté PHP seul n'apparaît pas dans l'inséreur** : `editeur.js` doit appeler
  `registerBlockType`. **À vérifier concrètement dans la stack** ; si l'observation contredit ce point
  sur le cœur installé, **le remonter, pas contourner**.

---

## 6. Le HTML rendu — contrat de balisage, littéral et exhaustif

**Le nom d'une classe approximatif ici devient un encart sans style en production.** Les deux moitiés
du travail ont été planifiées en aveugle l'une de l'autre : cette section est le seul point où elles
se rencontrent.

### 6.1 Rendu public, portée présente — état *Chiots disponibles*, encart complet

```html
<section class="wp-block-mtb-derniere-portee mtb-derniere-portee mtb-derniere-portee--disponible"
         aria-labelledby="mtb-derniere-portee-1">
  <h2 class="mtb-derniere-portee__accroche" id="mtb-derniere-portee-1">Chiots à réserver</h2>
  <p class="mtb-derniere-portee__titre">Portée A3 2025</p>
  <p class="mtb-derniere-portee__identite">
    <span class="mtb-derniere-portee__date">
      <span class="mtb-derniere-portee__etiquette">Née le</span>
      <span class="mtb-derniere-portee__valeur">4 mars 2025</span>
    </span>
    <span class="mtb-dispo mtb-dispo--disponible">Chiots disponibles</span>
  </p>
  <p class="mtb-derniere-portee__effectif">3 mâles, 2 femelles</p>
  <div class="mtb-photo mtb-derniere-portee__photo" style="--photo-largeur-naturelle:1200px">
    <img width="1024" height="683" src="…" srcset="…" sizes="(min-width: 40rem) 576px, 100vw"
         alt="Portée A3 2025" loading="lazy" decoding="async">
  </div>
  <p class="mtb-derniere-portee__action">
    <a class="mtb-derniere-portee__lien" href="https://…/portees/a3-2025/">Voir la portée</a>
  </p>
</section>
```

### 6.2 Tableau des crochets — condition d'émission, contenu

| # | Élément | Classes, **littéralement** | Émis si | Contenu |
|---|---|---|---|---|
| 1 | `<section>` | `mtb-derniere-portee` **+** au plus une de `mtb-derniere-portee--disponible` \| `--reserve` \| `--passee` **+** les classes de `get_block_wrapper_attributes()` | toujours | — |
| 1b | attribut sur 1 | `aria-labelledby="<id>"` | **seulement** si 2 est émis | — |
| 2 | `<h2>` | `mtb-derniere-portee__accroche` (+ `id`, le même qu'en 1b) | `'' !== trim( accroche )` — **jamais un `<h2>` vide** | l'accroche |
| 3 | `<p>` | `mtb-derniere-portee__titre` | toujours | `titre_public` |
| 4 | `<p>` | `mtb-derniere-portee__identite` | toujours | contient 5 et, s'il existe, 8 |
| 5 | `<span>` | `mtb-derniere-portee__date` | toujours (§4.1) | contient 6 et 7 |
| 6 | `<span>` | `mtb-derniere-portee__etiquette` | toujours | `date_naissance['libelle']` (« Née le ») |
| 7 | `<span>` | `mtb-derniere-portee__valeur` | toujours | `date_naissance['affichage']` |
| 8 | `<span>` | `mtb-dispo` **+** exactement une de `mtb-dispo--disponible` \| `--reserve` \| `--passee` | **`'' !== disponibilite['valeur']`** | `disponibilite['affichage']`, tel quel |
| 9 | `<p>` | `mtb-derniere-portee__effectif` | `'' !== effectif_texte` | `effectif_texte`, **tel quel** |
| 10 | `<div>` | `mtb-photo` **+** `mtb-derniere-portee__photo`, **dans cet ordre** | `is_array( photo )` | contient 12 |
| 11 | attribut sur 10 | `style="--photo-largeur-naturelle:<largeur du fichier>px"` | avec 10 | voir §9.2 |
| 12 | `<img>` | aucune classe exigée | avec 10 | `wp_get_attachment_image()`, §9.1 |
| 13 | `<p>` | `mtb-derniere-portee__action` | `'' !== lien` | contient 14 |
| 14 | `<a href>` | `mtb-derniere-portee__lien` | avec 13 | **« Voir la portée »** (`MASTER.md` §10.3) |

**L'ordre du DOM est contractuel**, parce que c'est l'ordre de lecture d'un lecteur d'écran :

> accroche → titre → (date + badge) → effectif → photo → lien

**Aucun `order:` CSS, aucun `flex-direction: row-reverse`, aucun repositionnement** : ordre du DOM =
ordre visuel = ordre de lecture.

### 6.3 Variantes — ce qui disparaît, jamais ce qui se vide

| Situation | HTML |
|---|---|
| `accroche` vide | Le `<h2>` **n'existe pas**, et l'`aria-labelledby` non plus. Aucun élément vide, aucune chaîne de repli |
| `disponibilite['valeur'] === ''` | Le badge **n'existe pas**, et **l'enveloppe n'a aucune classe d'état** — donc aucun filet en haut |
| `effectif_texte === ''` | Le `<p class="…__effectif">` **n'existe pas**. Jamais « 0 mâle », jamais « Non renseigné » (décision 21, D11) |
| `photo === null` | Le `<div>` **n'existe pas** : aucun trou, aucune réserve, aucun ratio réservé, aucun pictogramme (§9.2, emplacement facultatif) |
| `lien === ''` | Le `<p class="…__action">` **n'existe pas**. Jamais un `<a>` sans `href`, jamais un lien mort |
| Aucune portée publiée | **Sortie totalement vide.** Pas même l'enveloppe |
| Portée protégée | Inatteignable (§4.1 + garde 3) ⇒ sortie vide |

### 6.4 Décisions de balisage, motivées

- **`<section>` et non `<div>`**, avec `aria-labelledby` **pointant sur l'accroche** quand elle
  existe. Pointer sur un texte déjà présent **n'invente aucune chaîne** — c'est précisément ce qui
  distingue `aria-labelledby` d'un `aria-label` composé. Sans accroche, une `<section>` sans nom
  accessible n'est **pas** exposée comme région : elle est inerte, jamais nuisible.
- **`titre_public` en `<p>`, jamais un titre HTML.** En `<h3>`, l'ossature serait `h1 → h3` dès que
  l'accroche est vide : **un niveau sauté, instable selon un champ de texte facultatif**. En `<h2>`,
  deux `h2` de même rang pour deux natures différentes, et sa typographie changerait avec la présence
  de l'accroche. Le titre public est **une valeur de donnée** — le nom de la portée — pas l'intitulé
  d'une section. Précédent explicite : `MASTER.md` §4.5 décrit « Nom complet d'un chien (avec
  affixe) » comme une ligne sérif **non titrée**.
- **Le badge est un `<span>` imbriqué dans le `<p>` de l'identité**, jamais un `<p>` frère.
  §3.3 exige que le badge soit « toujours accompagné, en lecture d'écran comme à l'œil, de la date de
  naissance » : **l'imbrication dans le même paragraphe est ce mécanisme**, rendu structurel au lieu
  d'être seulement visuel. Un `<p>` dans un `<p>` serait invalide, d'où le `<span>`.
- **La date est rendue en deux éléments** (`__etiquette` + `__valeur`), jamais concaténée en une
  chaîne. Joindre « Née le » et « 4 mars 2025 » dans le PHP serait **composer une chaîne** que le
  serveur a délibérément livrée en deux morceaux (`issue-3.md` §19.5) ; et `__etiquette` est le
  crochet dont le CSS a besoin pour l'étiquette laiton.
- **La photo n'est pas un lien.** Un seul élément focalisable dans l'encart, un seul nom accessible.
  WCAG 2.4.4 est satisfait par le contexte : le titre public est dans la même `<section>`, deux
  éléments au-dessus.
- **Le lien porte le seul libellé « Voir la portée », sans `aria-label`.** Le compléter (« Voir la
  portée A3 2025 ») serait **composer une chaîne du domaine**, interdit nommé de `issue-3.md` §13.
  `multiple: false` garantit qu'il n'y a qu'un encart par page.
- **`mtb-dispo` et `mtb-photo` sont des crochets partagés**, nommés par `MASTER.md` lui-même (§3.3 le
  bloc `.mtb-dispo::before`, §6.2 `.mtb-photo > img`) : ils seront émis par plusieurs composants. Les
  renommer en `__dispo` / `__photo` garantirait trois implémentations divergentes du badge — **T9
  rejouée**. Voir la dette **T-#12-a**.
- **Aucun `data-libelle`** : la décision 10 de `ETAT.md` ne vise que les composants **tableau**.
  L'encart n'en a pas.
- **Aucun style en ligne hormis l'unique custom property de §9.2.** Aucune couleur, aucun
  espacement, aucune taille dans le PHP.

### 6.5 Rendu de l'état vide — éditeur seul

| # | Élément | Classes | Contenu |
|---|---|---|---|
| E1 | `<div>` | `mtb-etat-vide` **+** `mtb-etat-vide--derniere-portee` — **pas** `mtb-derniere-portee` | — |
| E2 | `<p>` | `mtb-etat-vide__nom` | « Encart dernière portée » — **casse normale**, mise en capitales par `text-transform` |
| E3 | `<p>` | `mtb-etat-vide__phrase` | **verbatim** : « Ce bloc n'affiche rien tant qu'aucune portée n'est publiée. » |

**Deux éléments, sans tiret cadratin.** Le tiret de l'exemple de §9.1 appartient à la prose de
`MASTER.md`, qui cite son exemple en ligne. La casse normale + `text-transform: uppercase` donne un
rendu à l'œil **identique** à §9.1 sans qu'un lecteur d'écran épelle des capitales dures.

---

## 7. États spéciaux

| État | Émis par le serveur | Rendu attendu |
|---|---|---|
| `aucune_portee` | `mtb_get_derniere_portee()` renvoie `null` | **Public : rien du tout** — pas même un conteneur. **Éditeur : l'apparence de §9.1** (§6.5). Le thème n'a rien à prévoir : ni cadre, ni réserve, ni hauteur minimale |
| `donnee_absente` | Chaque valeur facultative est **omise**, jamais rendue à « Non renseigné » | **Rien.** Aucune règle CSS ne doit supposer la présence de `__effectif`, `__dispo` ou du cadre photo |
| `parent_hors_elevage` | **Sans objet** : ce bloc n'affiche aucun parent | — |
| `page_protegee` | **Inatteignable** — deux gardes (§4.1 et la garde 3 de §8) | Sortie vide |
| photo absente | **Aucun élément** (§9.2 de `MASTER.md`) | La mise en page doit tenir **sans** l'emplacement photo |
| photo qui ne charge pas | Le cadre garde son ratio et son fond `--calcaire-creux`, le texte `alt` s'affiche dedans | §6.6 |
| disponibilité absente | **Aucun badge, aucune classe d'état** | Le CSS ne doit pas supposer `--disponible` |

> ### ⚠️ La ligne de `issue-3.md` §10 qu'il ne faut PAS appliquer ici
>
> `issue-3.md` §10 dit, pour `donnee_absente` : « **Imprimer la chaîne fournie.** » **Cette ligne vise
> une fiche, pas un encart.**
>
> Dans cet encart, imprimer `affichage` sans tester `valeur` produirait un badge « **Non
> renseigné** » — soit un **quatrième état de disponibilité**, alors que `MASTER.md` §3.3 n'en gèle
> que **trois**, chacun avec sa pastille et son ratio de contraste mesuré. Le quatrième n'a ni forme,
> ni couleur, ni preuve d'accessibilité : **il est interdit.**
>
> **La règle du composant :**
>
> > **On rend l'élément si et seulement si `valeur !== ''`, et on imprime alors `affichage`.**
> > **On teste `valeur`, jamais `affichage`.**
>
> On n'imprime **jamais** « Non renseigné » dans ce composant, et **jamais** un tiret non plus.
> `MASTER.md` §9.3 est respecté parce qu'on n'imprime **rien** — ce que §9.2 et §9.4 autorisent
> explicitement pour un composant. §9.3 réserve « Non renseigné » à *un champ de fiche non rempli*,
> et pose au-dessus qu'*une section entière non remplie n'est pas rendue*. **L'encart n'est pas une
> fiche, c'est une accroche.**

---

## 8. `render.php` — les gardes, dans cet ordre exact

**Aucun `is_admin()`, aucun `defined( 'REST_REQUEST' )`, aucune détection de contexte, aucun
`is_front_page()`, aucun `get_option()`.** Un fichier `render` reçoit `$attributes`, `$content`,
`$block` en portée locale ; un `return` nu suffit à ne rien rendre.

| # | Garde | Rendu | Motif |
|---|---|---|---|
| 1 | `if ( ! function_exists( 'mtb_get_derniere_portee' ) ) { return; }` | `''` | Extension partiellement chargée (module isolé par le `try/catch` du chargeur, `issue-1.md` §12). **D12** : jamais de fatale, jamais d'écran blanc |
| 2 | `$portee = mtb_get_derniere_portee(); if ( ! is_array( $portee ) ) { return; }` | `''` | `null` ⇒ `aucune_portee` ⇒ **rien côté public**. `is_array()` plutôt que `null !==` : une seule garde couvre `null` **et** tout retour dégénéré |
| 3 | `if ( 'ok' !== ( $portee['etat'] ?? '' ) ) { return; }` | `''` | Test **exact** de « la charge utile est complète ». La charge réduite d'une portée protégée (`hydratation.php:241-248`) ne contient **aucune clé du domaine** ; ce test garantit que les lectures suivantes ne portent pas sur des clés absentes. **Aujourd'hui inatteignable** (§4.1), la garde rend le bloc immunisé si une issue future assouplit le filtre, **sans que personne ait à relire ce fichier** |

**Aucune garde sur `date_naissance`** : §4.1. Ajouter une garde morte laisserait croire que le cas
existe.

### 8.1 Échappement en sortie — systématique, fonction par fonction

Les données renvoyées par les fonctions de lecture **ne sont pas échappées** (`issue-3.md` §9.2) :
l'échappement appartient au rendu.

| Sortie | Fonction |
|---|---|
| Attributs de l'enveloppe | `get_block_wrapper_attributes( array( 'class' => … ) )` — **échappe lui-même**, `echo` brut. L'entourer d'`esc_attr()` produirait un double échappement et des `&quot;` visibles |
| `accroche` | `esc_html()` — texte d'éditeur, jamais du HTML : **aucun `wp_kses_post`** |
| `titre_public`, `date_naissance['libelle']`, `['affichage']`, `disponibilite['affichage']`, `effectif_texte` | `esc_html()` |
| `lien` | `esc_url()` |
| `id` / `aria-labelledby` | `esc_attr()`, valeur produite par `wp_unique_id()` |
| `--photo-largeur-naturelle` | `esc_attr()` sur un entier **casté** — voir §9.2 |
| « Voir la portée » | littéral du code, rien à échapper |
| Suffixes de classe d'état | **littéraux concaténés depuis une liste blanche** `disponible\|reserve\|passee`. **Jamais d'interpolation directe** de `valeur` dans un nom de classe. Une valeur inconnue ⇒ **aucun badge, aucun modificateur** |
| `<img>` | `wp_get_attachment_image()` — applique `esc_attr` sur tous ses attributs (dont `alt`) et `esc_url` sur `src`/`srcset`. **Ne pas pré-échapper `alt`** |

`wp_kses_post()` n'apparaît nulle part : aucun champ riche n'est rendu.

**Aucun assainisseur en entrée** : le bloc n'écrit rien. `accroche` est typé `string` dans
`block.json` (le cœur coerce et rejette une valeur non conforme) et vient d'un `TextControl`. **La
protection réelle est l'échappement au rendu.** Conformément à la décision 20, on n'applique **aucun**
assainisseur passant par `strip_tags()` — la règle du projet est uniforme, et il n'y a rien à gagner
à en faire une exception.

### 8.2 Sécurité et rôles

- **Aucun rôle, aucune capacité, aucun `add_cap`, aucun `add_role`.** L'éleveuse reste sur le rôle
  **Éditeur** natif.
- **Aucun chemin d'écriture, donc aucun nonce à poser** — et cette absence n'est pas un oubli, c'est
  la conséquence de l'absence d'écriture. La seule écriture est celle du cœur enregistrant la Page,
  avec son propre nonce et sa propre vérification de capacité.
- **Contenu protégé : jamais rendu.** Double garantie — `has_password => false` dans `contenus()`, et
  la garde 3. L'encart ne peut pas fuiter le nom, la date ou la disponibilité d'une portée protégée.
  *(Cela ne solde pas **T8** : l'archive, le sitemap et la recherche restent dus à #23.)*
- **Zéro requête tierce, zéro appel HTTP sortant, zéro cookie, zéro JavaScript sur le site public.**
- **Le pire qu'elle puisse faire est de retirer l'encart** : la page reste juste, et il se réinsère
  en trois clics.

---

## 9. Deux exceptions ratifiées à `issue-3.md` §13 — portée de lot

`issue-3.md` §13 accorde l'exception `wp_get_attachment_image()` **au thème**, au motif que « le choix
d'une taille d'image est une décision de présentation ». Ici c'est un **bloc de l'extension** qui rend
une photo. Trois faits règlent la question :

1. `issue-1.md` §8 dit qu'un bloc rend « **une structure** et des crochets de classes ». Un `<img>`
   avec `srcset`, `width` et `height` **est** de la structure : `MASTER.md` §6.9 les exige (décalage
   cumulé nul) et **D7** exige un `alt` utile.
2. **Il n'existe aucune autre voie.** Le thème n'a pas de point d'accroche dans le HTML d'un bloc à
   rendu serveur, et lui en donner un — un filtre par lequel il injecterait du HTML — serait bien
   plus grave que l'appel lui-même. Écrire les URL à la main perdrait `srcset` et les dimensions.
3. Ce qui reste au CSS reste au CSS : `aspect-ratio`, `object-fit`, `object-position`, le cerne. **Le
   PHP n'en écrit pas un mot.**

> **Ratifié : l'exception de `issue-3.md` §13 est étendue, explicitement, au rendu serveur d'un bloc
> de `mtb-core`.** Elle vaudra pour **#8 (galerie photos)** et **#14 (grille de chiens)** : à lire
> ainsi en revue, ce n'est pas une infraction.

### 9.1 Arguments de `wp_get_attachment_image()`

```php
wp_get_attachment_image(
  (int) $portee['photo']['id'],
  'large',
  false,                                        // jamais l'icône de type de fichier
  array(
    'alt'      => $portee['photo']['alt'],      // FOURNI PAR LE SERVEUR, avec repli
    'loading'  => 'lazy',
    'decoding' => 'async',
    'sizes'    => '(min-width: 40rem) 576px, 100vw',
  )
)
```

- **`alt` passé explicitement, et c'est le point le plus important.** Sans lui,
  `wp_get_attachment_image()` relit `_wp_attachment_image_alt` et produit `alt=""` quand la photo n'en
  a pas — on perdrait le repli sur le nom de la portée que `hydratation.php:469-475` construit exprès
  « pour qu'aucune photo ne part sans alternative » (**D7**, `issue-3.md` §19.5). *Contrepartie
  assumée : quand le repli joue, un lecteur d'écran entend le nom de la portée deux fois — une fois
  pour l'image, une fois pour le titre. L'alternative serait de re-décider côté bloc ce que le serveur
  a décidé.*
- **`sizes` est passé, et c'est la seconde exception.** La voie propre serait le filtre
  `wp_calculate_image_sizes` **dans le thème** — mais `functions.php` est fermé à tout le lot. La
  valeur `576px` est celle du canal de 36 rem. **Dette T-#12-c** : à déplacer dans un filtre du thème
  quand `functions.php` se rouvrira (#16/#17).
- **Taille `large`** (1024 px) : couvre 576 px logiques à 1,78× et met toutes les tailles
  intermédiaires dans le `srcset`. Aucune taille dédiée n'est enregistrée — `add_image_size()` vit
  dans `functions.php`, fermé. Le jour où #16/#17 en enregistre une, **seul cet argument change** ; la
  feuille ne bouge pas.
- **`width`/`height` ne sont pas passés** : la fonction les émet depuis les métadonnées du fichier.
- **`loading="lazy"`, pas de `fetchpriority`** : `MASTER.md` §6.9 est explicite — « toute image hors
  bandeau ». Réserve honnête consignée : sur l'accueil, l'encart peut être au-dessus de la ligne de
  flottaison, où `lazy` retarde l'image la plus visible de la page. **`MASTER.md` a tranché, on ne
  rouvre pas ici** ; signalé à `lead-design-mtb`.

### 9.2 La custom property `--photo-largeur-naturelle` — un fait mesuré, pas une règle visuelle

`MASTER.md` §6.6 impose, pour une photo de définition insuffisante : « l'emplacement ne s'agrandit pas
au-delà de la largeur naturelle × 1,5 ; au-delà elle est servie à sa largeur et centrée dans son
canal ». **C'est impossible en CSS seul** : aucune requête de conteneur ne connaît la largeur du
fichier.

Le `render.php` émet donc, sur l'élément 10 :

```
style="--photo-largeur-naturelle:<largeur du fichier en px>px"
```

la largeur étant lue par `wp_get_attachment_image_src( $id, 'full' )` (index 1), **castée en entier**,
et `esc_attr`-échappée. Si elle n'est pas résoluble, **l'attribut est omis** et le CSS retombe sur son
repli `100%` — comportement inchangé.

> **Pourquoi ce n'est pas « l'extension qui émet une règle visuelle ».** Le PHP émet **un fait mesuré
> sur le fichier** — sa largeur réelle — sous forme de custom property. Il ne dit pas quoi en faire :
> le facteur 1,5, le centrage et le plafonnement sont **entièrement dans la feuille du thème**. La
> frontière est tenue **en substance** : l'extension fournit la donnée, le thème décide de la forme.
> **Précédent pour #8 et #14.**

---

## 10. Ce que la feuille du thème porte — et la règle qui la gouverne

Contenu à la charge de `dev-ux-mtb`, énoncé ici pour que le contrat soit complet et vérifiable.
**Uniquement des jetons**, aucune valeur brute hors de la liste close ci-dessous, chacune **recopiée
de `MASTER.md`** et justifiée à l'endroit où elle est écrite.

> Littéraux autorisés, tous recopiés : `.6em` et `2px` de la pastille (§3.3) · `50%` de
> `border-radius` de la pastille (§3.3, autorisé nommément par §5.2) · `1px solid` du badge *Tous
> réservés* (§3.3) · `1px dashed` de l'état vide (§9.1) · `50% 38%` du point d'intérêt par défaut
> (§6.2) · `.12em` d'interlettre du badge et `.16em` de l'étiquette (§4.5) · `1.5` du plafond de
> largeur naturelle (§6.6). `0`, `100%`, `none`, `auto` et les mots-clés ne comptent pas.

### 10.1 La règle de spécificité — deux classes minimum

`editor.css:14-28` établit un fait vérifié : **dans la toile de l'éditeur, le cœur préfixe les
sélecteurs de `base.css` par `.editor-styles-wrapper`.** Donc `h2` y pèse **(0,1,1)** au lieu de
(0,0,1) ; `a`, `p` et `img` de même. Une feuille de bloc n'est **pas** préfixée : un sélecteur à une
seule classe pèse (0,1,0) et **perdrait dans l'éditeur alors qu'il gagne sur le site** — exactement le
mensonge que `editor.css` existe pour empêcher.

> **Gelé : tout sélecteur qui neutralise ou surcharge une règle d'élément de `base.css` porte au moins
> DEUX classes.** `.mtb-derniere-portee .mtb-derniere-portee__accroche` pèse (0,2,0) et bat (0,1,1)
> dans les deux contextes, **sans dépendre de l'ordre de chargement et sans `!important`**.

Concerne `__accroche` (contre `h2`), `__lien` (contre `a`), `__photo img` (contre `img`), `__titre` /
`__effectif` (contre `p`). Précédent de méthode : `editor.css:69` fait exactement cela sur
`h2 + hr.wp-block-separator`, en augmentant la spécificité plutôt qu'en touchant `base.css`.

### 10.2 Le double filet — neutralisation **inconditionnelle**

`base.css:201-216` donne à **tout** `h2` un filet double en segment de 6 rem ; `MASTER.md` §3.3 donne
à l'encart un filet double **au-dessus** à l'état *Chiots disponibles*. Deux filets dans le même bloc
visuel est un **interdit nommé** de §2.1 et §13.

**La neutralisation du filet du `h2` est inconditionnelle, dans les trois états** —
`background-image: none`, `padding-block-end: 0`, `margin-block: 0 var(--e-4)`.

*Les deux plans divergeaient ici : le plan back proposait une neutralisation **conditionnelle** au
seul état `disponible`, au motif que dans les deux autres états il n'y a pas de filet en haut, donc
pas de duplication. L'arbitrage retient l'inconditionnelle : sinon **l'apparence de l'accroche
dépendrait de la disponibilité de la portée** — un couplage arbitraire qui rend l'identité du
composant instable, pour une éleveuse qui verrait son titre changer de forme en changeant un menu
déroulant. Et c'est moins de CSS.*

Ce qui reste hérité de `base.css` et n'est pas touché : famille sérif, graisse 500, `--t-xl`,
interligne, interlettre, encre `--pin`. **On retire un filet, on ne redessine pas un `h2`.**

### 10.3 Le quatrième signal de §3.3 — l'état, pas la page

§3.3 dit que le filet double au-dessus de l'encart apparaît « sur l'accueil et la page Placement
uniquement » à l'état *Chiots disponibles*.

> **Gelé : la condition se réduit à l'état, portée par la classe `mtb-derniere-portee--disponible`.
> Le dessin reste au CSS.**

Un bloc de l'extension ne doit **pas** savoir sur quelle page il se trouve : `is_front_page()`
coupleraient l'extension au réglage « page d'accueil » du site **pour une règle purement
décorative**, et casserait si l'encart était posé ailleurs. Le CSS ne peut pas faire ce travail seul
non plus : `body.home` existe, mais « Placement » **n'a aucun crochet stable** — le cœur n'émet qu'un
`page-id-<N>`, un identifiant de base de données qu'aucune feuille ne doit graver. La troisième voie,
un interrupteur dans l'éditeur, est refusée par §14 (« le rendu d'un composant » n'est pas à elle).

L'écart assumé est donc le **champ d'application par page**, jamais la liste close des huit
emplacements de filet du §2.1 — **l'emplacement 8 reste unique et légitime**. Réserve à porter dans la
fiche d'aide plutôt que dans le code : **le composant s'utilise sur l'accueil et sur Placement.**
Signalé à `lead-design-mtb`.

### 10.4 Le badge — §3.3 à la lettre, trois signaux

`.mtb-dispo` : `inline-flex`, `align-items: center`, `gap: var(--e-2)`, `padding: var(--e-2)
var(--e-3)`, `border-radius: var(--r-1)` (§5.2 : « commandes uniquement : bouton, champ, **badge** »),
`var(--sans)` / `700` / `var(--t-sm)` / interligne 1.2, `uppercase`, `letter-spacing: .12em`,
`white-space: nowrap`. Les trois libellés font **deux mots** : l'interdit de §13 (majuscule +
interlettre au-delà de trois mots) est tenu.

`.mtb-dispo::before` — pastille **en CSS pur**, `currentColor`, **jamais une image, jamais une police
d'icônes** : `content: ""`, `.6em` carré, `border: 2px solid currentColor`, `border-radius: 50%` (la
**seule** exception d'arrondi du site, §5.2), `flex: none`.

| Classe | Fond | Encre | Bordure | Pastille | Ratio |
|---|---|---|---|---|---|
| `.mtb-dispo--disponible` | `var(--sauge)` | `var(--calcaire)` | aucune | `background: currentColor` → **disque plein** | **5,25:1** ✓ AA |
| `.mtb-dispo--reserve` | `var(--calcaire-creux)` | `var(--texte)` | `1px solid var(--laiton)` | `linear-gradient(to right, currentColor 50%, transparent 50%)` → **demi** | **10,78:1** ✓ AAA |
| `.mtb-dispo--passee` | `var(--calcaire)` | `var(--texte-doux)` | `var(--bord-actif)` | `background: transparent` → **anneau vide** | **6,47:1** ✓ AA |

**Aucune information par la couleur seule** : le **mot** (fourni fini par le serveur, jamais
reformulé), la **forme** (plein / demi / vide), la **couleur**. En niveaux de gris les trois pastilles
restent distinctes.

*Conflit trouvé et tranché : §13 interdit « tout dégradé sauf `--filet-double` et `--voile-photo` »,
or la pastille à moitié pleine de §3.3 **est** un `linear-gradient`, écrit par `MASTER.md` lui-même
dans son bloc de code normatif. On suit §3.3 — normative, spécifique, avec ses ratios mesurés — et on
ne bricole aucun substitut d'ombre interne. `lead-design-mtb` doit ajouter la pastille à la liste
d'exceptions du §13.*

### 10.4a Amendement — primitive nue, surcharge de contexte scopée

**Ajouté après le gel, sur arbitrage de `/lead-mtb`. Vaut pour les quatre composants restants.**

`white-space: nowrap` **sort** de la règle nue `.mtb-dispo` et devient
`.mtb-derniere-portee .mtb-dispo { white-space: nowrap }`.

**Le défaut réel, mesuré des deux côtés.** Cette feuille déclarait `.mtb-dispo { white-space: nowrap }`
**nue** ; `mtb-liste-portees.css` (#13) déclare `.mtb-dispo { white-space: normal }` **nue**. Les deux
pèsent (0,1,0) : sur une page portant les deux blocs, **le gagnant dépend de l'ordre du registre de
blocs — indéterminé**. Et les deux mesures sont vraies dans leur propre contexte :

| Contexte | Largeur disponible | `nowrap` |
|---|---|---|
| Encart #12, canal plein | **324 px** pour un badge de **238 px** | tient, aucun débordement (mesuré à 360 px, iframe de même origine) |
| Carte #13, à côté d'une vignette de 144 px | ≈ **156 px** | **déborde**, rompt le 360 px — échec AA bloquant |

> **La distinction, et c'est elle qui sert aux autres composants : scoper la PRIMITIVE est la faute
> (elle garantit des implémentations divergentes du badge — T9 rejouée) ; scoper une SURCHARGE DE
> CONTEXTE par-dessus une primitive restée nue est légitime, et c'est la seule façon d'exprimer qu'un
> contexte diffère.**
>
> Le test : la déclaration décrit-elle **ce qu'est le badge** (couleur, forme, pastille, casse,
> interlettre) ou **la place dont il dispose ici** ? Le premier appartient à la primitive nue et se
> déplacera sans renommage ; le second appartient au bloc et **ne se déplace pas**.

`.mtb-derniere-portee .mtb-dispo` pèse (0,2,0) et bat la primitive nue **sans dépendre de l'ordre de
chargement**. Ce n'est pas un retour au scoping refusé par le lot : la primitive reste **nue**, et la
doctrine « nues partout, jamais scopées » est intacte.

**Conséquence pour T-#12-a, retenue par `/lead-mtb`** : au hissage vers une feuille partagée, la
primitive prendra **`normal`, jamais `nowrap`** — un repli est toujours préférable à un débordement.
Amendement du présent §10.4 porté à `lead-design-mtb`.

*Écarts restants entre les deux déclarations de la primitive, laissés en l'état par `/lead-mtb` faute
de casse mesurable aujourd'hui : `gap` (`--e-2` ici, `--e-1` chez #13) · `padding` composé
différemment · `font-family` absente chez #13 · `border-radius: inherit` sur `.mtb-photo::after` et
`.mtb-dispo--passee::before { background-color: transparent }`, qui n'existent que chez #13.*

### 10.5 Mise en page, photo, lien

- **Une seule colonne, aucune requête média de mise en page.** L'encart est enfant de
  `.entry-content`, **plafonné à 36 rem (576 px) en toutes circonstances** ; à 360 px il fait 324 px.
  Une requête média sur la largeur de **fenêtre** mentirait sur la largeur du **conteneur**, et deux
  colonnes dans 576 px donneraient une photo de 220 px — indigne du principe « les photos sont la
  matière première du site » (BRIEF §10).
- **L'encart n'est pas une surface** : aucun fond, aucune bordure, aucun `padding` sur la racine.
  §3.2 ne range pas cet encart dans les usages de `--calcaire-creux`, et §1 comme §13 préfèrent « des
  filets qui séparent au lieu d'encadrer ». Séparation par le filet et le rythme de section.
- **Aucun `overflow`** sur l'encart : §7.8 l'interdit sur un conteneur de texte, et il rognerait
  l'anneau de focus du lien (§8.1, décalage de 2 px).
- **Cadre photo** : `.mtb-photo` porte le comportement transverse (fond `--calcaire-creux` pour le PNG
  détouré et l'image qui ne charge pas, cerne `--cerne-photo` de §6.6) ;
  `.mtb-derniere-portee__photo` porte le seul choix propre à cet emplacement, `aspect-ratio:
  var(--r-paysage)` — **`3/2`, le ratio que §6.1 nomme « carte de portée »**, aucun ratio inventé.
  L'`<img>` : `inline-size: 100%`, `block-size: 100%`, `object-fit: cover`, `object-position:
  var(--point-interet, 50% 38%)` (§6.2 mot pour mot).
- **Le point d'intérêt n'existe pas encore** : vérifié, les seize clés `_mtb_*` d'une portée n'ont
  **aucun** champ de cadrage, alors que `MASTER.md` §10.2 en nomme le libellé. Le défaut `50% 38%` est
  donc la valeur effective sur toutes les photos. La forme `var(--point-interet, …)` est écrite quand
  même : coût nul, et le jour où une issue ajoute le champ, elle pose la variable sans que la feuille
  bouge. **On n'exige pas cette variable du serveur : demander un champ qui n'existe pas serait une
  invention.**
- **Le lien est un lien de contenu, pas un bouton** (§8.2). Encre `--sauge-fonce` (7,73:1),
  soulignement permanent, anneau de focus de §8.1 : **tout est déjà dans `base.css`, on hérite, on ne
  réécrit rien.** Seul ajout : `display: inline-flex` + `padding-block: var(--e-2)`, qui porte la
  cible tactile à ≈ 44 px (§12.10) **sans écrire un seul pixel brut**.
- **La dette T7 ne concerne pas ce composant, vérifié.** T7 vit sur `.wp-block-button__link`, la
  classe du bloc **Bouton du cœur**. Le lien de l'encart est émis par `render.php` : le bloc Bouton
  n'est jamais dans la chaîne, `.wp-block-button__link` n'apparaît nulle part dans le balisage, et
  `#32373c`/`#fff` ne peuvent pas s'appliquer. **`base.css` n'est pas touché ; T7 reste à la chaîne
  #6.**
- **360 px** : une colonne, `flex-wrap` sur `__identite` (la date et le badge passent l'un sous
  l'autre au lieu de déborder), `overflow-wrap: break-word` sur le titre, aucune largeur fixe, **aucun
  conteneur à défilement horizontal**. Aucune requête média n'est nécessaire — c'est le résultat du
  choix « une seule colonne », pas un oubli.
- **Zoom 200 %** : aucun `vw` seul, aucune hauteur fixe sur un conteneur de texte, aucun `overflow:
  hidden`.

### 10.6 L'apparence d'état vide — B1, et la dette est nommée

`MASTER.md` §9.1 déclare l'apparence « **identique pour les dix composants** » et la loge dans
`editor.css` — **hors de l'empreinte de cette chaîne comme des cinq sœurs**, et `editor.css:65` la
mentionne déjà sans la livrer.

> **Gelé : la feuille de ce bloc n'écrit RIEN pour l'état vide. Seuls les noms de classes sont gelés**
> (§6.5) : `.mtb-etat-vide`, `.mtb-etat-vide--derniere-portee`, `.mtb-etat-vide__nom`,
> `.mtb-etat-vide__phrase`.

Si chacune des six chaînes du lot en écrivait sa copie, on refabriquerait la dette **T9** — trois
assainisseurs divergents dès le premier jour — **en six exemplaires**, sur une apparence que
`MASTER.md` déclare unique.

**Conséquence assumée, dite franchement : aujourd'hui, l'état vide de l'éditeur est lisible mais non
habillé.** La bordure tiretée, le fond et l'étiquette laiton manquent ; **la phrase française
s'affiche**. **D12 est donc tenue par le texte, pas par le cadre** — et c'est un défaut visible de
l'éditrice seule, jamais du visiteur.

**Dette T-#12-b** : l'apparence de §9.1 est à écrire **une fois** dans `editor.css`, par la chaîne à
qui `/lead-mtb` attribuera ce fichier ou par une issue de dette dédiée. Spécification prête à coller,
tirée de §9.1 ligne à ligne : `border: 1px dashed var(--laiton)` · `border-radius: var(--r-0)` ·
`background-color: var(--calcaire-creux)` · `padding: var(--e-6)` · `font-size: var(--t-sm)` ·
`color: var(--texte-doux)` (**5,79:1** ✓ AA) · `text-align: start` ; l'étiquette en `var(--sans)` 600
`var(--t-xs)` `uppercase` `letter-spacing: .16em` `color: var(--laiton-texte)`.

> ⚠️ **Question bloquante pour `lead-design-mtb`, trouvée par le calcul et non par la lecture.** La
> paire **`--laiton-texte` sur `--calcaire-creux`**, qu'exige §9.1 (« étiquette laiton » sur fond
> creusé), est **absente du §12** — dont le préambule est catégorique : « une paire absente de ce
> tableau est une paire interdite ». Calculée à la formule du §3 : `L(#7A5F2C) = 0,1251`,
> `L(#E7E5DA) = 0,7809`, ratio **4,75:1 → AA tenue** à cette taille. **Elle est donc sûre, mais elle
> doit être ajoutée au §12.3 par `lead-design-mtb`.** Elle n'est employée sur l'autorité de personne
> tant que ce n'est pas fait — ce qui est sans conséquence, puisque T-#12-b diffère déjà l'écriture.

---

## 11. `editeur.js` — aperçu fidèle, sans build

`registerBlockType` **sans JSX**, `wp.element.createElement`, `useBlockProps()` sur l'élément
extérieur (exigence de `apiVersion` 2+).

**Aperçu : `ServerSideRender`**, avec `EmptyResponsePlaceholder` portant l'apparence de §6.5. Trois
raisons, dans l'ordre :

1. `editor.css` l'écrit comme une doctrine : « Un composant qui n'a pas la même allure dans l'éditeur
   est un composant que l'éleveuse emploiera de travers. » **Contrainte 1.**
2. `EmptyResponsePlaceholder` donne l'état vide **sans aucune détection de contexte côté serveur** —
   c'est le mécanisme que le cœur prévoit pour ce cas exact.
3. Une représentation statique devrait, pour savoir s'il y a une portée, interroger REST elle-même —
   or **les seize métas `_mtb_*` sont hors REST** (`issue-3.md` §18.1) : le JS ne peut pas savoir
   laquelle est « la dernière », et s'il tentait de le déduire il violerait « le thème ne décide jamais
   ce qu'est la dernière portée ». Une représentation statique honnête serait **un carton qui ment sur
   le rendu**.

Points de contrat :

- **Dépendances de `wp_register_script()`, liste exacte et close** : `array( 'wp-blocks',
  'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render' )`. Cinq, et rien d'autre.
  Pas de `wp-i18n` (aucune fonction de traduction, `issue-1.md` §7), pas de `wp-data`, pas de
  `wp-primitives` (l'icône est un dashicon nommé dans `block.json`). **`wp-server-side-render` est
  livré par le cœur** : aucun paquet, aucune dépendance externe.
- **Aucun `title`, `description`, `keywords`, `category`, `icon` ni `attributes` répété dans le JS** :
  ils viennent de `block.json`. *À vérifier dans la stack, pas à croire : si le cœur installé exige
  `title` côté client (avertissement en console), le repli est de le répéter **à l'identique**, en le
  signalant comme une duplication à surveiller.*
- `LoadingResponsePlaceholder` et `ErrorResponsePlaceholder` **ne sont pas fournis** : les défauts du
  cœur n'introduisent aucune chaîne française inventée.
- **À vérifier avant d'écrire le reste, énoncé comme tel** : `ServerSideRender` n'appelle
  `EmptyResponsePlaceholder` que si une réponse vide aboutit à l'état `''` et non à l'état initial
  `null` (qui affiche le chargement). **Si l'observation contredit l'attendu, c'est un constat à
  remonter** — jamais à contourner par une détection de contexte dans `render.php`, qui est interdite.
- L'aperçu est une requête REST **de même origine**
  (`/wp-json/wp/v2/block-renderer/mtb/derniere-portee`), dans l'administration seulement, gardée par
  le cœur sur `edit_posts`. **Zéro domaine tiers, rien au rendu public.** Coût honnête : un
  aller-retour par insertion et par frappe débouncée dans le champ d'accroche, et un aperçu qui
  clignote légèrement — acceptable pour un bloc qu'on insère une fois.
- **La conséquence pour le front, à connaître** : dans l'éditeur, le DOM porte **une enveloppe de
  plus** que sur le site (celle de `useBlockProps` autour de celle de
  `get_block_wrapper_attributes()`), comme pour tous les blocs à rendu serveur du cœur.

---

## 12. Vérifications exigées dans la stack — aucune n'est présumée faite

| # | Vérification | Pourquoi elle ne peut pas être déduite |
|---|---|---|
| **V1** | **`wp_enqueue_block_style()` charge-t-il la feuille dans la toile de l'éditeur** (crochet `enqueue_block_assets`) ou seulement sur le site public ? | Le cœur n'est pas dans l'arbre de travail. Si négatif, la neutralisation du filet du `h2` **ne s'applique pas dans l'éditeur** : l'accroche y garderait un filet qu'elle n'a pas sur le site. À consigner dans ce contrat après mesure |
| **V2** | Le bloc apparaît-il dans l'inséreur, **et sous « Mont Brabant »** ? | Dépend de §11 du présent contrat *(la catégorie)* et du point 2 du contrat #1 §10 |
| **V3** | Les sept états dégradés, un par un : sans accroche · sans photo · sans disponibilité · les deux compteurs vides · sans lien · aucune portée publiée · portée protégée. **Aucun avertissement PHP avec `WP_DEBUG`, aucun élément vide** | C'est D12 et D11 vérifiées, pas déduites |
| **V4** | Console de l'éditeur **à zéro avertissement** : pas de `_doing_it_wrong`, pas de dépréciation de `TextControl`, pas de `title` manquant | |
| **V5** | L'aperçu **rempli**, puis l'aperçu **vide** en dépubliant toutes les portées (`EmptyResponsePlaceholder`) | Voir §11, dernier point à vérifier |
| **V6** | Les trois états côte à côte · le filet double **une seule fois** à l'état *disponible*, **aucun** dans les deux autres · **la même chose dans la toile** · portée sans photo · photo portrait dans le cadre 3/2 · photo de 300 px de large · **360 px en iframe** *(Chrome sans interface ne descend pas sous ~500 px : une capture directe mentirait)* · zoom 200 % à 1280 px · parcours clavier et anneau de focus · **rendu avec JavaScript désactivé** | |
| **V7** | **Mesure des octets** de la feuille et du HTML ajouté (D8 exige « chiffres à l'appui »). Estimation à remplacer, pas à recopier : ≈ 7 Ko de feuille (dont ≈ 4,4 Ko de commentaires, qui **coûtent réellement à chaque vue**), ≈ 1,2 Ko de HTML | |

**Budget** : `0` octet de JavaScript servi au visiteur · `0` octet de police, d'image ou d'icône
ajouté · zéro origine tierce · page d'accueil estimée à ≈ 37 Ko contre **200 Ko** de budget
(BRIEF §12).

---

## 13. Interdits

- Le thème n'interroge **jamais** la base : `WP_Query`, `get_posts`, `get_post_meta`, `get_terms`,
  `$wpdb`. Le grep de frontière du contrat #2 doit rester **à zéro occurrence** dans le thème après
  cette issue : **c'est le `render.php` de l'extension qui lit**, jamais le thème.
- Le thème n'appelle **jamais** `MTB\Core\*`.
- Le thème ne **décide jamais** ce qu'est « la dernière portée » et ne refait **jamais** un tri par
  date.
- Personne ne **compose** une chaîne du domaine — « Portée » + identifiant, un effectif, un libellé de
  disponibilité, « Née le » + une date — **ni en PHP, ni en JS, ni par `content:` en CSS.**
  > **Corollaire dur pour le CSS** : aucune règle n'ajoute de texte par `content:` — ni un libellé, ni
  > un tiret, ni « Dernière portée ». **La seule exception est le `content: ""` de la pastille**, qui
  > ne porte aucun mot. Écrire un mot en CSS, c'est composer une chaîne du domaine dans un fichier que
  > personne ne relit.
- Personne ne **reformate** une date, un N° LOF, un résultat de test de santé.
- Personne n'**invente** une mention d'absence : ni tiret, ni « Aucun », ni « Non testé », ni « — ».
  Dans cet encart, l'absence se rend par **l'omission de l'élément**.
- L'extension n'émet **aucune règle visuelle ni mise en page** — à la seule exception, ratifiée et
  motivée, de §9.
- Aucune issue n'édite `mtb-core.php` ni `class-loader.php` ; ce module n'appelle ni
  `flush_rewrite_rules()`, ni `init` 99, ni `wp_delete_post`, ni `delete_post_meta`.
- **Le thème ne filtre jamais `render_block_mtb/derniere-portee`** pour altérer le balisage : le HTML
  du bloc appartient à l'extension.
- **`base.css` n'est jamais modifié pour régler le conflit de filet** : la neutralisation se fait
  **par spécificité**, dans la feuille du bloc.
- Aucune police, icône, image, script ou requête vers un **domaine tiers**. Aucune ligne de
  JavaScript servie au visiteur. Aucun `wp-block-styles`. **Aucun neuvième emplacement de filet
  double.**
- Aucune supposition qu'un élément facultatif est présent.

---

## 14. Obligations imposées aux autres issues

1. **Le nom du bloc `mtb/derniere-portee` et le nom de fichier
   `assets/css/blocs/mtb-derniere-portee.css` sont couplés par une chaîne de caractères.** Renommer
   l'un sans l'autre produit un encart **sans style, sans erreur** (§3).
2. **`includes/blocks/categorie-mtb/` reste dû** — voir §11 des questions ouvertes. Sans lui, la
   catégorie `mtb` n'est pas enregistrée et le groupement du bloc dans l'inséreur n'est pas garanti.
3. **`.mtb-dispo` et `.mtb-photo` sont des crochets partagés**, nommés par `MASTER.md` : la deuxième
   issue qui rend un badge ou une photo les **déplace** vers une feuille partagée, **sans les
   renommer** (dette T-#12-a).
4. **L'exception `wp_get_attachment_image()` est étendue au rendu serveur d'un bloc de `mtb-core`**
   (§9) : #8 et #14 en bénéficient, ce n'est pas une infraction.
5. **L'epic de reprise (#19-#21) doit renseigner la Photo principale d'une portée**, pas seulement
   `_mtb_galerie` : l'encart n'affiche une photo que s'il y a une **Photo principale**. Une reprise qui
   ne remplirait que la galerie produirait un encart sans photo — dégradé proprement, mais pauvre.
6. **`docker/fixtures/portees.json` doit être converti** aux clés `disponible|reserve|passee` avant
   tout import : `Hydratation::champ_liste()` traite une clé inconnue comme une **absence** → `valeur`
   vide → **aucun badge, en silence**. Déjà signalé par `issue-3.md` §11.
7. **Quand `functions.php` se rouvrira (#16/#17)** : déplacer `sizes` dans un filtre
   `wp_calculate_image_sizes` (T-#12-c) et, si une taille d'image dédiée au ratio 3/2 est enregistrée,
   changer **le seul argument** de `wp_get_attachment_image()`.
8. **`editor.css` doit recevoir l'apparence de §9.1 une seule fois** (T-#12-b).

---

## 15. Valeurs et clés gelées

Nom du bloc **`mtb/derniere-portee`** · titre **« Encart dernière portée »** · catégorie `mtb` ·
icône `pets` · attribut unique **`accroche`** (`string`, défaut `""`) · poignée de script
**`mtb-derniere-portee-editeur`** · poignée de feuille **`mtb-bloc-mtb-derniere-portee`** · fichier de
feuille **`assets/css/blocs/mtb-derniere-portee.css`** · les quatorze crochets de classes de §6.2 ·
les quatre crochets d'état vide de §6.5 · les libellés **« Réglages de l'encart »**, **« Titre
d'accroche »** et son aide · la phrase d'état vide **verbatim** · le libellé de lien **« Voir la
portée »** · la liste blanche **`disponible|reserve|passee`** · la custom property
**`--photo-largeur-naturelle`** · l'ordre du DOM de §6.2.

---

## 16. Arbitrages — les désaccords entre les deux plans, et ce qui a été décidé

Les deux plans ont été écrits **en aveugle l'un de l'autre**. Huit désaccords, tous tranchés.

| # | Désaccord | Décision | Raison |
|---|---|---|---|
| 1 | **Où vit la feuille de style** : la consigne du lot disait « dans le dossier du bloc », les deux plans recommandaient le thème | **Le thème**, `assets/css/blocs/mtb-derniere-portee.css` | Les **jetons vivent dans le thème** et `block.json` n'offre aucun moyen propre de déclarer une dépendance vers `mtb-jetons` : une feuille d'extension consommerait des variables qu'elle ne possède pas. S'y ajoutent l'interdit explicite de `CLAUDE.md` et le fait, **vérifié ligne à ligne**, que déposer le fichier ne demande **aucune** édition de `functions.php`. Deux plans indépendants ont convergé (§2) |
| 2 | **Ordre du DOM** : back voulait accroche → **photo** → titre → date → badge ; front voulait accroche → titre → date+badge → effectif → **photo** | **Celui du front** | Le front possède la couche visuelle et a écrit toute sa feuille sur la règle « ordre du DOM = ordre visuel = ordre de lecture, **aucun `order:` CSS** », qui est la règle sûre pour un lecteur d'écran. Elle éloigne en outre la redondance du `alt` (qui répète le nom de la portée) du titre lui-même. La photo reste **au-dessus du lien** : l'accroche visuelle est préservée |
| 3 | **Élément racine** : `<div>` (back) ou `<section aria-labelledby>` (front) | **`<section>`**, avec `aria-labelledby` **si et seulement si** l'accroche est rendue | L'objection du back — « la nommer exigerait un `aria-label` composé » — **ne tient pas pour `aria-labelledby`**, qui **pointe** sur un texte déjà présent et n'invente aucune chaîne. Sans accroche, une `<section>` sans nom accessible est inerte, jamais nuisible |
| 4 | **Structure du badge et de la date** : back voulait deux `<p>` frères, avec la date en deux `<span>` ; front voulait un `<p>` d'identité contenant la date **concaténée** et le badge | **La structure du front, la granularité du back** : `<p class="__identite">` contenant `<span class="__date">` — lui-même composé de `__etiquette` et `__valeur` — puis le badge | La structure du front rend **structurelle** l'exigence de §3.3 (« le badge est toujours accompagné de la date ») au lieu de la laisser visuelle, et son `flex-wrap` est la réponse aux 360 px. Mais **concaténer « Née le » et la date serait composer une chaîne** que le serveur livre délibérément en deux morceaux, et `__etiquette` est le crochet dont le CSS a besoin. Les deux moitiés étaient bonnes, chacune sur un point différent |
| 5 | **Neutralisation du filet du `h2`** : conditionnelle au seul état `disponible` (back) ou inconditionnelle (front) | **Inconditionnelle** | Conditionnelle, **l'apparence de l'accroche dépendrait de la disponibilité de la portée** : l'éleveuse verrait la forme de son titre changer en changeant un menu déroulant, sans qu'aucun écran ne le lui dise. Couplage arbitraire, identité de composant instable — et plus de CSS pour un résultat moins bon (§10.2) |
| 6 | **Cadre photo** : `<figure>` (back) ou `<div>` (front) | **`<div>`** | Une `<figure>` sans `<figcaption>` revendique un contenu « autonome, référencé depuis le flux » — ce n'est pas le cas ici, et elle traîne les marges par défaut du cœur. Le front possède la couche visuelle et a demandé un `<div>` |
| 7 | **L'attribut `sizes` de l'image** : le back le laissait au filtre `wp_calculate_image_sizes` du thème ; le front l'exigeait dans l'appel | **Dans l'appel**, avec dette T-#12-c | La voie propre du back suppose d'éditer `functions.php`, **fermé à tout le lot**. Un `sizes` absent ou faux coûte des octets réels au visiteur. À déplacer dans le filtre quand `functions.php` se rouvrira |
| 8 | **`--photo-largeur-naturelle`** : le front l'exigeait, le back ne l'avait pas planifié et n'en voulait qu'un repli | **Exigé** | §6.6 est une règle du système de design **impossible en CSS seul**, et le repli la fait disparaître **en silence**. Le PHP n'émet **qu'un fait mesuré sur le fichier** — sa largeur — jamais une règle visuelle : le facteur 1,5 et le centrage restent dans la feuille. La frontière est tenue en substance (§9.2) |

Trois décisions de conception prises **avant** les plans, rappelées ici parce qu'elles ont été
confirmées par les deux :

| # | Point | Décision | Raison |
|---|---|---|---|
| 9 | **D12 : que voit-on quand aucune portée n'est publiée ?** L'énoncé de l'issue #12 demande « un état vide propre (« aucune portée pour le moment ») » | **Rien au visiteur ; l'apparence de §9.1 à l'éditrice** | `MASTER.md` §9 pose la règle transverse en sens inverse de l'énoncé : « **Côté public, un composant sans contenu ne s'affiche pas. Côté éditeur, il s'affiche toujours, avec la phrase française qui dit ce qui manque.** » — et §9.1 prend **ce composant** comme exemple littéral. La phrase « aucune portée pour le moment » **n'est attestée nulle part**. Écrire « aucune portée » sur l'accueil d'un élevage qui compte 27 portées donnerait un site à l'arrêt. **D12 exige « un état vide propre, pas une page cassée » et ne dit pas *pour qui*** |
| 10 | **Défaut du titre d'accroche** | **`""`, et aucun élément rendu si vide** | « Dernière portée » n'est attesté **ni en §10.2 ni en §10.3** : seulement comme *nom du composant* en §9.1. Le poser en défaut publierait un titre que le système de design n'a jamais validé pour un affichage public (§5.2) |
| 11 | **Périmètre du rendu** | Sept lignes possibles, **trois garanties** : titre, date, lien | Les parents auraient exigé une **dépendance croisée vers la chaîne sœur #4** et risqué d'afficher un `post_title` pour un nom d'usage (**D11**) ; le tableau des chiots et la galerie **sont** la fiche Portée, que #16/#17 livreront. Le plancher réaliste — « Portée A3 2025 / Née le 4 mars 2025 / Voir la portée » — est **trois lignes justes, aucun trou** |

---

## 17. Dettes créées par cette issue

| # | Dette | Pourquoi elle est créée | Qui la paie |
|---|---|---|---|
| **T-#12-a** | **`.mtb-dispo` et `.mtb-photo`, crochets transverses, sont logés dans une feuille de bloc.** Chargés avec ce bloc, ils ne styleront pas un badge rendu par un autre composant sur une page sans encart | L'empreinte est d'**un** fichier. Les renommer garantirait des implémentations divergentes du badge — **T9 rejouée**, et contre le nom que `MASTER.md` donne lui-même | La **deuxième** issue qui rend un badge ou une photo les **déplace** vers `base.css` ou une feuille partagée. **Aucun renommage, seulement un déplacement** |
| **T-#12-b** | **L'apparence d'état vide de §9.1 n'est écrite nulle part.** La phrase s'affiche, le cadre non | `editor.css` est hors de l'empreinte des six chaînes, et six copies divergeraient (§10.6) | La chaîne à qui `editor.css` est attribué, ou une issue de dette |
| **T-#12-c** | **`sizes` est écrit dans le PHP de l'extension** | La voie propre (`wp_calculate_image_sizes`) exige `functions.php`, fermé à tout le lot | **#16/#17** |

---

## 18. Points restés ouverts — aucun ne bloque cette issue

### Question de lot, à trancher par `/lead-mtb`

- **`includes/blocks/categorie-mtb/`** — la catégorie `mtb` (« Mont Brabant ») est due « **une seule
  fois**, par la première issue de composants » (`issue-1.md` §10 l. 315-318). Le dossier **n'existe
  pas** et n'est dans l'empreinte d'**aucune** des six issues du lot, toutes livrant un bloc.
  Vérifié sur les six corps d'issue. Sans elle, `"category": "mtb"` désigne une catégorie non
  enregistrée : **le bloc s'enregistre et se rend normalement**, mais son groupement dans l'inséreur
  n'est plus garanti — il peut n'être trouvable que par la recherche. **Remonté ; ni la catégorie ni
  le nom de catégorie ne sont changés par cette chaîne.**

### Questions pour `lead-design-mtb` — dettes de documentation du système de design

- **La paire `--laiton-texte` / `--calcaire-creux` est absente du §12**, alors que §9.1 l'exige.
  Calculée à **4,75:1**, AA tenue (§10.6). À ajouter au §12.3.
- **§13 interdit tout dégradé sauf deux, mais §3.3 dessine la pastille à moitié pleine avec un
  `linear-gradient`** — écrit par `MASTER.md` lui-même dans un bloc normatif. À ajouter aux
  exceptions (§10.4).
- **§3.3 restreint le filet de l'encart à « l'accueil et la page Placement »**, sans qu'aucun crochet
  stable n'existe pour « Placement » (§10.3). Amender la phrase, ou fournir le crochet.
- **Aucune ligne de §4.5 ne décrit un titre de portée non titré dans un encart.** La ligne « Nom
  complet d'un chien » (Newsreader 500, `--t-md`, 1.3) est employée par analogie ; alternative, la
  ligne `h3`. Quatre déclarations changent si la réponse diffère.
- **§5.1 ne chiffre pas le rembourrage d'un badge** : `var(--e-2) var(--e-3)` proposé. Sans
  rembourrage, le fond `--sauge` colle aux lettres. Deux noms de jetons changent si la réponse
  diffère.
- **Aucune feuille d'impression n'est spécifiée** : §7.6 renvoie à un « §9.6 » inexistant. Déjà
  remonté par le contrat #2 amendement 5, **toujours ouvert**. Aucun `@media print` n'est écrit.
- **`50% 38%` est un pari de `MASTER.md` §11.4** sur des chiens en pied, à revérifier quand
  `docs/migration/source/` sera rempli. Une valeur unique à corriger, dans `MASTER.md`.

### Questions de domaine — pour l'éleveuse. Aucune n'est comblée

- **L'encart doit-il s'afficher sur l'accueil quand la dernière portée est *Portée passée* ?**
  BRIEF §5.1 dit que la disponibilité « **pilote l'affichage** sur l'accueil et la page Placement »,
  ce qui se lit de deux façons : *l'encart s'affiche toujours et le badge change* (**lecture retenue**,
  celle de BRIEF §6 — « affiche automatiquement la portée la plus récente **et sa disponibilité** » —
  et des **trois** états de §3.3), ou *l'encart disparaît quand la portée est passée*. Conséquence de
  la lecture retenue : à l'entre-deux-portées, l'accueil affiche « Portée A3 2025 — Portée passée ».
  C'est un **choix éditorial d'éleveuse** : elle peut vouloir montrer sa dernière portée en
  permanence, ou préférer le *Bandeau d'alerte* de BRIEF §6 (« pas de chiots actuellement »).
  **Ne bloque pas** : l'autre lecture coûterait une garde de plus, ajoutable sans rien casser.
- **Quel texte veut-elle au-dessus de l'encart, s'il en faut un ?** Aucun libellé public n'est attesté
  par §10 ; le défaut est vide et **rien n'est inventé**. Le champ existe dans les deux cas.

### Constat de méthode, hérité de `issue-3.md` §18.5 et à ne pas réapprendre

Les chaînes sœurs créent et suppriment des contenus **dans la même base** pendant les essais. **Un
résultat d'essai obtenu en lot parallèle doit être réobtenu en isolation avant d'être cru.** Vaut
particulièrement pour V3 et V5, qui reposent sur « aucune portée publiée ».

Et pour la démo d'aujourd'hui : il n'existe **aucun** module `includes/migration/`, `wp mtb
import-fixtures` **n'existe pas**, et aucune Page « Accueil » n'est créée. **La démo se fabrique en
saisissant une portée à la main dans l'administration** — ce qui est, accessoirement, la meilleure
preuve possible de la contrainte 3.
