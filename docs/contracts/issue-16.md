# Contrat d'interface — Issue #16 — Gabarits des fiches et index, portée et chien

**Gelé le 2026-08-20.** Lot 6, en parallèle de #15 (tableau de résultats) et #17 (gabarits des pages
libres). Arbre de travail unique, aucune isolation.

Ce document est **contraignant** à partir de son gel. Les deux plans amont — `leaddev-back-mtb` et
`leaddev-front-mtb` — ont été écrits à l'aveugle l'un de l'autre ; ce qui suit est leur réconciliation
et fait foi contre les deux.

---

## 0. Ce que cette issue paie

**Dette T10 : le site n'a aucun rendu public.** Le thème n'appelle aujourd'hui aucune fonction
`mtb_get_*` — zéro occurrence, vérifié. Les trois types de contenu sont saisissables depuis six mois et
un visiteur ne voit rien. C'est la démonstration bout en bout de la **contrainte 3** (« le contenu
structuré ne se recopie jamais ») : une saisie, quatre endroits.

**Nuance à ne pas survendre en fin de lot.** « Un visiteur ne voit rien » est un raccourci.
`mtb_portee` porte `supports => array('title','editor','revisions','thumbnail')` et `singular.html`
rend `wp:post-title` + `wp:post-content` : une portée publiée affiche **déjà** son identifiant en `h1`
et le commentaire de l'éleveuse. Ce qui manque, ce sont les **champs** — date, parents, chiots,
disponibilité, galerie, santé, palmarès. La mesure d'avant/après doit dire cela, pas autre chose.

---

## 1. L'approche retenue, et le mécanisme qui la fonde

### 1.1 Le point dur

Trois faits établis en lisant le code, pas déduits :

1. **Aucun bloc du catalogue ne rend les champs de la portée ou du chien courants.** Les dix blocs
   lisent des **attributs saisis dans l'éditeur**. Un seul est conscient du contenu courant :
   `mtb/bandeau-ouverture` (`usesContext: ["postId","postType"]`).
2. **Un gabarit de thème de blocs est un `.html` et n'exécute aucun PHP.** Il ne peut appeler ni
   `mtb_get_portee()`, ni `render_block()`, ni une fonction de composant. Le contrat #13 §19 l'écrit
   déjà et consigne le point comme « à trancher au brainstorm de #16/#17 ».
3. **La liaison de blocs `core/post-meta` est fermée deux fois** : le cœur refuse les métas protégées,
   et la **décision 16** a rendu les seize clés `_mtb_` protégées exprès ; et elle rendrait la valeur
   stockée (`reproducteur`) au lieu de l'`affichage` (`Reproductrice`), ce que la **décision 18**
   interdit.

### 1.2 Le mécanisme, mesuré et non supposé

Lu dans le cœur qui tourne, `wp-includes/block-template.php`, fonction `locate_block_template()` :

```php
if ( $template ) {                                    // un .php a été trouvé par locate_template()
    $index     = array_search( $relative_template_path, $templates, true );
    $templates = array_slice( $templates, 0, $index + 1 );   // ← LA TRONCATURE
}
$block_template = resolve_block_template( $type, $templates, $template );
if ( $block_template ) { … return ABSPATH . WPINC . '/template-canvas.php'; }
else { if ( $template ) { return $template; } … }     // ← le .php gagne
```

**La liste des gabarits de blocs candidats est tronquée à ceux qui sont au moins aussi spécifiques que
le `.php` trouvé.** Pour une portée, `get_single_template()` produit
`single-mtb_portee-<slug>.php · single-mtb_portee.php · single.php · singular.php · index.php`.
Avec `single-mtb_portee.php` présent, la liste est tronquée à ses deux premières entrées ; aucun
`wp_template` ne porte ces slugs ; **le `.php` est rendu et `template-canvas.php` n'est jamais chargé**.

### 1.3 Ce qui est retenu

| Livrable | Forme |
|---|---|
| Fiche portée | **`single-mtb_portee.php`**, racine du thème |
| Fiche chien | **`single-mtb_chien.php`**, racine du thème |
| Squelette de document partagé | **`enveloppe-fiche.php`**, racine du thème |
| Index des portées | **`templates/archive-mtb_portee.html`**, gabarit de blocs |
| « La meute » | **une Page**, pas un gabarit — voir §7 |
| Habillage des fiches | **`assets/css/fiches.css`**, feuille neuve — voir §8 |
| Dettes T22 et T23, primitive `.mtb-tableau` | **`assets/css/base.css`** — voir §9 |
| Recours §9.5 | **`templates/404.html`**, **`templates/search.html`** — voir §10 |

**Pourquoi cette voie et pas une autre.** C'est la seule qui honore `CLAUDE.md` au pied de la lettre
(« le thème n'interroge jamais la base directement — **il appelle les fonctions de lecture de
l'extension** ») et le contrat **gelé** #4 §2, qui place explicitement les appels du palmarès « dans le
gabarit de fiche chien (thème, epic Gabarits), pas dans ce module ». Les alternatives — deux blocs de
contexte dans `mtb-core`, un filtre `template_include` dans `functions.php` — exigent l'une et l'autre
un fichier hors de l'empreinte de cette chaîne.

### 1.4 Correction du corps de l'issue — les noms de fichiers

Les slugs de type sont **`mtb_portee`** (`content/portee/bootstrap.php:27-56`) et **`mtb_chien`**
(`content/chien/bootstrap.php:29-78`). `single-portee.html` et `archive-portee.html`, tels qu'écrits
dans le corps de l'issue, **ne sont résolus par personne** : un fichier mal nommé ne produit **aucune
erreur**, il est ignoré et `singular.html` continue de servir. Toute vérification de recette doit
chercher un marqueur propre au gabarit, jamais se contenter d'un code 200.

### 1.5 Extension d'empreinte, déclarée

Trois fichiers PHP neufs à la **racine du thème** et une feuille CSS neuve sortent de l'empreinte
littérale de l'issue. **Ce sont des fichiers neufs qui n'entrent en collision avec personne** : #17
possède `templates/page-*.html` et `patterns/**`, #15 possède un dossier de bloc et
`assets/css/blocs/mtb-tableau-resultats.css`. La disjonction que l'empreinte protège reste entière.
Remonté à `/lead-mtb` le 2026-08-20 **avant** le gel.

`enveloppe-fiche.php` porte délibérément un nom **hors de toute hiérarchie de gabarits** : ni
`header.php`, ni `footer.php`, ni `single.php`. Deux fichiers « header » dans le même thème seraient une
invitation à l'erreur à côté de `parts/header.html`.

---

## 2. Fonctions de lecture exposées par l'extension

Toutes appelées **sous `function_exists()`**, sans exception. Aucune n'est créée ni modifiée par cette
issue.

### 2.1 `mtb_get_portee( int $id ): ?array`

Clés de premier niveau : `id`, `identifiant`, `titre_public`, `lien`, `statut`, `protege`, `etat`,
`annee`, `date_naissance`, `disponibilite`, `males`, `femelles`, `effectif_texte`, `pere`, `mere`,
`chiots_colonnes`, `chiots`, `chiots_message`, `galerie`, `photo`.

- Les champs de valeur portent l'enveloppe **`array('libelle','valeur','affichage')`** (décision 18).
- `titre_public` est une **chaîne nue, jamais vide** (`hydratation.php:250`).
- `effectif_texte` est une **chaîne nue**, vide quand ni mâles ni femelles ne sont saisis.
- `chiots_message` est une **chaîne finie fournie par le serveur** : « Liste des chiots non renseignée. »
  (`hydratation.php:274`), verbatim de `MASTER.md` §9.3.
- `chiots` est un tableau de lignes ; chaque cellule (`nom`, `sexe`, `lof`, `devenir`) porte
  `libelle` / `valeur` / `affichage`.
- `pere` et `mere` portent `etat` ∈ `fiche` · `parent_hors_elevage` · `donnee_absente`, plus
  `fiche_id`, `lien`, `nom`, `elevage`, `sante`.
- **Contenu protégé** : `post_password_required()` est testé **avant tout champ du domaine**
  (`hydratation.php:241`). Le retour se réduit alors à `array('id','lien','protege'=>true,
  'etat'=>'page_protegee')` — **aucune clé de domaine, pas même vide**, « une clé présente à `''`
  dirait déjà qu'elle existe ».

### 2.2 `mtb_get_portees( array $args = array() ): array` · `mtb_get_portee_par_identifiant()` · `mtb_get_portee_voisine( int $id, string $sens ): ?array` · `mtb_get_portees_du_chien( int $chien_id ): array`

- Toutes écartent les contenus protégés (`has_password => false`, `hydratation.php:96`).
- `mtb_get_portee_voisine()` rend `null` aux extrémités **et pour toute portée non datée**.
- `mtb_get_portees_du_chien()` rend une forme **gelée** (`hydratation.php:202-222`) : `id`,
  `identifiant`, `lien`, `date_naissance{…}`, `disponibilite{…}`, `role{valeur,affichage}`.
  **`lien` et non `url` ; `role` et non `role_du_chien`.** Elle ne rend **aucune photo**.
- Tri de `mtb_get_portees()` : **en PHP**, sur `_mtb_date_naissance`, **décroissant**, les portées sans
  date **toujours en fin**, égalité départagée par identifiant de contenu (`hydratation.php:144-176`).
  Aucune clause de méta n'est employée — **une portée sans date n'est jamais escamotée**.

### 2.3 `mtb_get_chien( int $chien_id = 0 ): ?array`

- **Toutes les clés existent toujours** (`lecture.php:69-99`), y compris sur une fiche protégée
  (`etat === 'page_protegee'`, squelette vide) ou entièrement vide. Le gabarit ne teste jamais une clé
  à l'aveugle.
- **Les champs d'identité n'ont pas de `libelle`** (`lecture.php:292-330`). Voir §12, dette **T29**.
- `galerie` est stockée en **chaîne à virgules** côté chien (`lecture.php:609`) et en **tableau** côté
  portée. La fonction de lecture rend une forme normalisée ; **aucun agent n'écrit dans ces métas dans
  cette issue**, mais `contenu-mtb` doit connaître la divergence : un import qui écrirait la même forme
  des deux côtés produirait une galerie vide **en silence**.
- **`mtb_get_chien()` rend `null` sur un brouillon** (`lecture.php:43-45`) — asymétrie avec
  `mtb_get_portee()`. L'aperçu d'une fiche non publiée n'a aucune donnée. Voir §12, manque **M1**.

### 2.4 `mtb_get_chiens_par_statut( array $args = array() ): array`

Groupes dans l'ordre gelé de `statuts()` ; **un groupe sans chien n'est pas renvoyé**
(`lecture.php:198-220`). **Un chien sans statut n'apparaît dans aucun groupe** (`lecture.php:186-189`)
— donc invisible sur « La meute ». Fait à connaître pour la reprise de contenu (§13).

### 2.5 `mtb_get_resultats_travail_du_chien( int $chien_id, array $args = array() ): array`

**Forme fixe, jamais `null`, jamais de clé manquante.** Sur un chien sans résultat, et sur
`$chien_id <= 0` : `array( 'colonnes' => array(), 'lignes' => array() )` — `colonnes` est
**explicitement vidé** quand `lignes` l'est (`interne.php:663`, `:680`).

- **Lignes** : année **croissante** par défaut pour le palmarès (`bootstrap.php:95`, `'annee_asc'`) —
  une carrière se lit dans son sens. Année absente (`0`) **toujours en dernier**. Départage par
  identifiant de contenu croissant, jamais par niveau : il n'existe aucune hiérarchie officielle des
  niveaux.
- **Colonnes**, ordre réel (`interne.php:404-413`) : `annee` → **Année** · `niveau` → **Niveau** ·
  `discipline` → **Discipline** *(les trois toujours)* · `conducteur` → **Conducteur** · `pays` →
  **Pays** *(les deux seulement si au moins une ligne les remplit)*. `chien` n'apparaît jamais dans un
  palmarès — c'est sa propre fiche.

> **Correction d'un contrat gelé.** Le contrat **#4 §2** annonce l'ordre `discipline · annee · niveau ·
> conducteur · pays`. **Il est faux.** L'ordre réel est celui ci-dessus, et le contrat **#5 §5.2** le
> donne juste. **Le code fait foi.**

### 2.6 `mtb_resultat_disciplines()` · `mtb_resultat_sexes()`

Tables de correspondance, sans `get` (décision 16). **Non consommées par cette issue.**

---

## 3. Blocs consommés

Cette issue **n'enregistre aucun bloc**.

| Bloc | Où | Attributs employés | Défaut |
|---|---|---|---|
| `mtb/liste-portees` | `archive-mtb_portee.html` | `{"nombre":"","annee":""}` | les deux vides signifient **toutes les portées, non filtrées** (`rendu.php:110-120`, `:134-138`) |
| `mtb/galerie-photos` | les deux fiches, par `render_block()` | `{"photos":[int,…]}` | — |
| `mtb/grille-chiens` | Page « La meute », inséré par l'éditrice | `{"statut":"tous"}` | `"tous"`, `enum` fermée à `tous · reproducteur · en_cours_de_confirmation · retraite · disparu` (`block.json:9-20`) |
| `core/template-part` ×2 | `enveloppe-fiche.php` par `do_blocks()`, et l'archive par `wp:template-part` | `{"slug":"header"\|"footer","tagName":"header"\|"footer"}` | — |

**La galerie s'obtient par `render_block()`, jamais autrement.**
`MTB\Core\Blocks\GaleriePhotos\rendre()` est dans l'espace de noms `MTB\` : **le thème n'a pas le droit
de l'appeler**. `render_block( array( 'blockName' => 'mtb/galerie-photos', 'attrs' => array( 'photos'
=> $ids ) ) )` est la seule voie conforme, et elle déclenche l'enfilage de la feuille du bloc.

---

## 4. Le squelette de document — `enveloppe-fiche.php`

Quand le `.php` gagne, **`template-canvas.php` ne s'exécute plus**. Deux conséquences que le canevas
couvrait et qu'il faut désormais écrire :

1. **`_block_template_viewport_meta_tag` n'est jamais accroché.** Il est ajouté par
   `locate_block_template()` **uniquement sur le chemin du canevas**. Sans lui, aucune balise
   `viewport` — donc **le repli à 360 px et le zoom 200 % sont morts** sur les deux fiches.
   → `<meta name="viewport" content="width=device-width, initial-scale=1">` écrit à la main.
2. **`_block_template_render_title_tag` n'est jamais accroché**, et `_wp_render_title_tag` sort en
   silence faute de `add_theme_support('title-tag')` — le thème ne le déclare **nulle part**
   (vérifié, zéro occurrence dans `functions.php`).
   → `<title><?php echo esc_html( wp_get_document_title() ); ?></title>` écrit à la main. **Ne pas**
   ajouter `add_theme_support('title-tag')` : ce serait `functions.php`, hors empreinte.

Interface unique du fichier :

```php
mtb_fiche_rendre_le_document( string $classe_de_page, callable $corps ): void
```

Séquence, **et l'ordre est le livrable** :

| # | Étape | Pourquoi ici |
|---|---|---|
| 1 | `mtb_feuilles_du_site()` sous `function_exists()`, puis `mtb_mettre_feuille_en_file( 'mtb-fiches', 'assets/css/fiches.css', array( 'mtb-jetons' ) )` | Le tampon de l'étape 2 fait passer les feuilles de blocs dans la file **avant** `wp_enqueue_scripts`. Sans le pré-appel, `mtb-base` sortirait **après** les feuilles de blocs : la cascade s'inverserait et les composants perdraient contre `base.css` à spécificité égale |
| 2 | `ob_start()`, puis en-tête, `<main>`, `$corps()`, pied — tout en mémoire | Tout ce qui s'enfile pendant le rendu est connu avant `wp_head()` |
| 3 | `<!DOCTYPE html>` · `<html <?php language_attributes(); ?>>` | `lang="fr"` — `base.css:113-115` fait dépendre `hyphens: auto` de cet attribut |
| 4 | `<head>` · `<meta charset>` · **`<meta viewport>`** · **`<title>`** · `wp_head()` · `</head>` | voir les deux points ci-dessus |
| 5 | `<body <?php body_class( $classe_de_page ); ?>>` puis `wp_body_open()` | `body_class()` donne `single single-mtb_portee postid-N` ; `$classe_de_page` ajoute `mtb-fiche mtb-fiche--portee` / `--chien` |
| 6 | `echo` du tampon | |
| 7 | `wp_footer()` · `</body></html>` | |

**En-tête et pied** — exactement :

```php
echo do_blocks( '<!-- wp:template-part {"slug":"header","tagName":"header"} /-->' );
echo do_blocks( '<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->' );
```

**Et jamais `block_template_part()`**, qui rend le contenu de la partie **sans** son enveloppe :
ni `<header>`, ni `<footer>`, ni `class="wp-block-template-part"`. Les deux points de repère du site
disparaîtraient, `entete-pied.css` ne s'accrocherait plus, et le travail de #18 serait défait sur les
deux fiches. Le passage par `do_blocks()` va jusqu'à `render_block()`, donc les filtres de #18
(`mtb_retirer_la_navigation_sans_entree`, `mtb_nommer_la_navigation`, **décision 42**) s'appliquent.
**À vérifier au rendu**, pas à croire.

**Le `<main>`, et le piège n° 1 de cette issue** :

```html
<main id="contenu" tabindex="-1" class="mtb-canal">
```

`mtb_rendre_la_cible_focalisable()` (`functions.php:840-869`) est un filtre `render_block` gardé par
`isset($bloc['attrs']['anchor']) && 'contenu' === …`. **Un `<main>` écrit à la main en PHP n'est pas un
bloc : il n'en bénéficie pas.** Le `tabindex="-1"` s'écrit **littéralement**, comme `404.html:3`.
Oublier cela casse le lien d'évitement — la dette **T2**, tout juste payée — sur les deux fiches.
La recette doit le chercher **sur les deux fiches**, pas seulement sur 404.

---

## 5. `single-mtb_portee.php` — structure imposée par `MASTER.md` §7.4

Lecture unique, en tête : `$portee = function_exists('mtb_get_portee') ? mtb_get_portee( get_the_ID() ) : null;`

| § | Section | Balisage | Vide |
|---|---|---|---|
| §7.4-1 Identité | `<header class="mtb-fiche-portee__identite">` · `<h1>` ← `titre_public` · date ← `date_naissance.libelle` + `.affichage` · badge · effectif ← `effectif_texte` | badge **absent** si `disponibilite.valeur === ''` ; effectif absent si `effectif_texte === ''` |
| §7.4-2 Les parents | `<section><h2>Les parents</h2><ul class="mtb-cartes-parents" role="list">` ; une `<li>` par parent **connu** | `etat === 'donnee_absente'` → **pas de carte**. Les **deux** absents → **ni `h2` ni section** |
| §7.4-3 Les chiots | `<section><h2>Les chiots</h2><table class="mtb-tableau mtb-tableau--chiots alignwide">` | `chiots === []` → `h2` **conservé** + `chiots_message` en `.mtb-etat-doux` |
| §7.4-4 Commentaire | `<section class="mtb-fiche-portee__commentaire">`, **aucun `h2`** | prose vide → **section absente** |
| §7.4-5 La galerie | `<section><h2>La galerie</h2>` + `render_block()` | galerie vide → le bloc rend `''` (`galerie-photos/rendu.php:52-59`) → **ni `h2` ni section** |
| §7.4-6 Portées voisines | `<nav class="mtb-fiche-portee__voisines" aria-label="Portées">` | les deux `null` → **pas de `<nav>`** |

**Hiérarchie** : un seul `h1`. `h2` = « Les parents », « Les chiots », « La galerie ». `h3` = les noms
dans les cartes parent. Le commentaire de l'éleveuse peut porter ses propres `h2`/`h3` saisis dans
l'éditeur classique — c'est le bon niveau.

**Le bandeau d'ouverture n'est pas employé** : §7.4 ne le prévoit pas, la fiche commence au `h1` en
canal texte avec filet double. Ne pas l'ajouter.

**Le tableau des chiots n'est pas un composant du catalogue** — ni BRIEF §6, ni le contrat #12 §42, ni
le contrat #13 §2.2 ne le portent. Le rendre dans le gabarit **n'est donc pas une réécriture** de
composant au sens de la décision 24.

---

## 6. `single-mtb_chien.php` — structure imposée par `MASTER.md` §7.5

Lecture unique : `$chien = function_exists('mtb_get_chien') ? mtb_get_chien( get_the_ID() ) : null;`

| § | Section | Vide |
|---|---|---|
| §7.5-1 `h1` = `nom_usage`, dessous `nom_complet.affichage` | `nom_complet.valeur === ''` → **ligne omise** (§11, arbitrage 6) |
| §7.5-2 Deux colonnes ≥ `--bp-fiche` : portrait `4/5` + `<dl>` d'identité | photo absente → **emplacement structurant** §9.2 : cadre `4/5` conservé, `--calcaire-creux`, cerne, **nom d'usage au centre**, aucun pictogramme. Champ absent → `affichage` vaut déjà « Non renseigné ». **`date_deces` : ligne omise si `valeur === ''`** (§11, arbitrage 5) |
| §7.5-3 `<h2>Santé</h2>` + `<dl>` + `autres_tests` | **`sante_renseignee === false` → section entière absente.** Décision **serveur** (`lecture.php:429-441`), jamais du gabarit |
| §7.5-4 `<h2>Titres et brevets</h2>` | idem sur `titres_renseignes` |
| §7.5-5 `<h2>Palmarès de travail</h2>` | voir §11, arbitrage 3 |
| §7.5-6 `<h2>Portées</h2>` + cartes | `mtb_get_portees_du_chien()` → `[]` → **ni `h2` ni section** |
| §7.5-7 Galerie, puis lien pedigree en **lien externe** §8.6 (`rel="noopener"`, `target="_blank"`, `<span class="screen-reader-text">(nouvelle fenêtre)</span>`) | `pedigree === null` → rien |

**Hiérarchie** : un seul `h1`. `h2` = Santé · Titres et brevets · Palmarès de travail · Portées ·
Galerie. `h3` = les noms dans les cartes.

**Les cartes de portées ne portent pas de photo.** `mtb_get_portees_du_chien()` n'en rend pas, et §7.5-6
dit « en cartes », pas « avec photo ». **Interdit** d'appeler `mtb_get_portee()` par élément pour en
obtenir une, et **interdit** d'aller la chercher par `get_post_thumbnail_id()`.

---

## 7. `archive-mtb_portee.html` et « La meute »

### 7.1 L'index des portées

`has_archive => true`, `rewrite.slug => 'portees'` (`content/portee/bootstrap.php:54-58`) :
**l'adresse `/portees/` existe déjà** et l'éleveuse n'a rien à faire.

```
wp:template-part {slug:header, tagName:header}
<main id="contenu" tabindex="-1" class="mtb-canal">     ← tabindex écrit à la main
  wp:heading {level:1} → <h1>Les portées</h1>           ← littéral, PAS wp:query-title
  wp:mtb/liste-portees {"nombre":"","annee":""}
</main>
wp:template-part {slug:footer, tagName:footer}
```

**`h1` littéral et non `wp:query-title`** : sur une archive de type, celui-ci rendrait
`get_the_archive_title()`, dont le préfixe (« Archives : ») n'est dans **aucun** tableau de
`MASTER.md` §10. Le littéral « Les portées » est aligné sur le libellé de recours de §9.5.

**Aucune boucle du cœur** (`core/query`, `core/post-template`), aucune pagination, aucun compteur.
Ce n'est pas seulement une simplification : la requête principale du cœur **ne filtre pas
`has_password`**, alors que `mtb_get_portees()` l'écarte. **En n'imprimant jamais la requête
principale, l'archive referme de fait la moitié « archive » de la dette T8.**

Aucune portée publiée → le bloc rend **la chaîne vide**, pas même son conteneur (`rendu.php:151-154`,
décision 26). La page affiche en-tête, `h1`, pied. **C'est correct et voulu** — et c'est le seul état
où `/portees/` a l'air maigre : à dire dans la fiche d'aide.

### 7.2 « La meute » n'est pas un gabarit

`mtb_chien` porte **`has_archive => false`** (`content/chien/bootstrap.php:76`) : `archive-mtb_chien`
n'est **jamais résolu**. Le rallumer est du `mtb-core` (interdit) **et** serait un piège : l'empreinte
du chargeur ne couvre pas les paramètres des types (dette **T6**), donc aucune règle de réécriture ne
serait régénérée — `/chiens/` répondrait **404 en silence** sur un site qui répond 200, et la parade
manuelle exige `manage_options` que Fabienne n'a pas.

**« La meute » est une Page portant `wp:mtb/grille-chiens`**, adresse `/la-meute/`, créée dans la base
de développement. Le bloc **groupe déjà par statut** tout seul (`balisage.php:38-39`, `:283` : une
`<section data-statut>` par groupe, `h2` de groupe, `h3` par chien) : **il ne faut pas un bloc par
statut**, et la tâche de l'issue est servie par le défaut `{"statut":"tous"}`.

**Le trou, nommé** : `provision.sh` est hors empreinte et une routine d'activation serait du
`mtb-core`. **Sur l'installation de Fabienne, « La meute » n'existera pas.** → dette **T30**. La fiche
d'aide doit dire « **créez cette page** », jamais « elle existe » — c'est exactement le mensonge de
fiche que la **décision 43** a fait bloquer un lot.

**Le slug `/la-meute/` n'est pas « repris de l'ancien site ».** `docs/migration/source/` est vide :
personne ne sait quelle URL l'ancien site emploie. Le 301 appartient à #19-#21.

---

## 8. Où va le CSS

| Feuille | Contenu | Servie |
|---|---|---|
| **`assets/css/fiches.css`** *(neuve)* | tout l'habillage des deux fiches : identité, cartes parent, `<dl>`, portrait, cartes de portées, navigation entre portées, pedigree | **uniquement sur les deux fiches** |
| **`assets/css/base.css`** | T22 (1 déclaration), T23 (2 règles), la primitive **`.mtb-tableau`** de §7.6, et le strict nécessaire pour `.mtb-liens-de-secours` | toutes les pages |

`mtb_mettre_feuille_en_file()` (`functions.php:30-38`) est **publique, générique et gardée par
`file_exists()`** : appelée depuis `enveloppe-fiche.php` **avant `wp_head()`**, elle enfile la feuille
neuve **sans toucher `functions.php`**. Coût pour toutes les autres pages du site : **zéro octet**, au
lieu des ~13 Ko qu'une mise dans `base.css` aurait imposés partout.

> **`base.css` ne doit pas revenir élargi.** Consigne explicite de `/lead-mtb` : les deux dettes et la
> primitive nommée, rien d'autre.

---

## 9. Les deux dettes de `base.css`

### 9.1 T22 — le filet double rend 0 px de large

**Reproduction statique, que la revue n'avait pas trouvée.** `base.css:127-129` remet à zéro les marges
de `p, ul, ol, dl, dd, figure, blockquote, table` — **`hr` n'est pas dans la liste**. `base.css:302-311`
pose `margin-block`, qui n'écrit que `margin-block-start` et `-end` : les `margin-inline: auto` de la
feuille du navigateur **survivent intactes**. Sur un élément de grille, une marge en ligne `auto`
**désactive l'étirement** ; un `hr` est vide, donc largeur de contenu 0 → **boîte de 0 × 6 px**.

C'est le raisonnement que `base.css:499-504` écrit **déjà noir sur blanc** pour justifier le
`!important` de la ligne 509. Le thème connaissait le mécanisme ; il ne l'a pas appliqué à `hr`.

`base.css:507-510` ne couvre que `.mtb-canal.is-layout-constrained > *` : **le défaut est conditionnel
au conteneur.**

**Protocole de reproduction, obligatoire avant correction** — relever `getBoundingClientRect().width` :

| # | Emplacement | Prédiction |
|---|---|---|
| R1 | Séparateur dans une **Page** (`post-content`, `mtb-canal alignfull`, disposition **flow**) | **0 px** |
| R2 | Séparateur dans le **commentaire d'une portée** | à relever |
| R3 | `<hr>` dans `404.html` (`<main class="mtb-canal">` sans classe de disposition) | **0 px** |

**Si les trois sont corrects, le diagnostic est faux et on ne corrige rien** — et on le dit.

**Correctif** : `margin-inline: 0;` dans la règle existante `base.css:302-311`. `hr` pèse (0,0,1) et
`hr.wp-block-separator` (0,1,1) : la feuille du navigateur est battue sans `!important`.
`h2 + hr { display: none }` reste intact. Un `hr` hors `.mtb-canal` passe de 0 px à pleine largeur —
c'est une **apparition**, pas une régression.

**Remesure exigée** : R1, R2, R3, **plus** un `<hr>` dans une colonne étroite, **plus** le cas
`h2 + hr` (doit rester non rendu).

### 9.2 T23 — les marges se cumulent au lieu de fusionner

**`docs/ETAT.md` prescrit une correction fausse**, et l'arithmétique le prouve : « un `row-gap` unique
referme les dix composants » — or un `row-gap` **s'ajoute** aux marges, il aggraverait.

`mtb-bandeau-alerte.css:29` = `var(--e-7)` = 48 · `mtb-encart-appel.css:22` =
`max(--rythme-section, --e-7)` = 86,4 · `mtb-coordonnees-plan.css:27` = `--rythme-section` = 86,4.
48 + 86,4 = **134,4** (mesuré 134) ; 86,4 + 86,4 = **172,8** (mesuré 173). C'est un **cumul de marges
non fusionnées**, pas un `gap` manquant.

**Deux murs, et comment on passe :**

- **Spécificité** — chaque racine de bloc pèse (0,1,0) et les feuilles de blocs sont enfilées **après**
  `base.css` (`functions.php:181` puis `:237-248`). → **doubler la classe du conteneur** :
  `.mtb-canal.mtb-canal` = (0,2,0). Technique déjà employée dans le dépôt
  (`mtb-coordonnees-plan.css:60`, `:84`). Aucun `!important`.
- **Prose** — `h1` (`base.css:185`), `h2` (`:202`), `h3` (`:219`), `p` (`:241`) sont enfants directs du
  canal. → **rendre structurellement impossible de les atteindre** : chaque sélecteur exige qu'**au
  moins un des deux côtés de la frontière soit un composant**.

Définition, à écrire une fois en commentaire : **`COMPO` ≡ `[class*="mtb-"]:not(.mtb-canal)`**.
Le `:not(.mtb-canal)` n'est pas décoratif : `singular.html:6` pose `mtb-canal alignfull` sur
`post-content`, enfant direct du `<main class="mtb-canal">`. Sans l'exclusion, la première règle
annulerait la marge basse du `h1` de `post-title` et collerait le titre au contenu **sur toutes les
pages du site**.

| # | Sélecteur | Déclaration | Spécificité |
|---|---|---|---|
| T23-1 | `.mtb-canal.mtb-canal > *:has(+ [class*="mtb-"]:not(.mtb-canal))` | `margin-block-end: 0` | (0,4,0) |
| T23-2 | `.mtb-canal.mtb-canal > [class*="mtb-"]:not(.mtb-canal) + *:not([class*="mtb-"])` | `margin-block-start: 0` | (0,5,0) |

**Preuve que la prose ne bouge pas** : T23-1 exige que le frère **suivant** soit un composant ; T23-2
que le frère **précédent** en soit un. Une frontière `<p>`/`<p>` ou `<h2>`/`<p>` ne satisfait ni l'une
ni l'autre. C'est une propriété du sélecteur, pas une précaution.

**Mesures exigées, avant et après** — les quatre au minimum :

| Frontière | Avant | Attendu après |
|---|---|---|
| bandeau d'alerte → encart d'appel | 134 | **86** |
| encart d'appel → coordonnées | 173 | **86** |
| deux `<p>` consécutifs | à relever | **inchangé** |
| `h2` → `<p>` suivant | à relever | **inchangé** |

**Dégradation sûre à documenter** : `:has()` n'existe pas avant Chrome 105 / Firefox 121 / Safari 15.4.
Sur un moteur plus ancien, T23-1 est **entièrement ignorée** et on retombe sur le comportement
d'aujourd'hui — un écart trop grand, **jamais une page cassée**.

**Ce que cela ne ferme pas** : la valeur retenue est celle d'un seul côté, ce n'est pas un `max()`.
Deux composants dont aucun ne porte 86,4 donneraient 48. Fermer cela demanderait `mtb-bandeau-alerte.css`,
**hors empreinte**. **T21 reste due** ; T23 la contient désormais partiellement.

### 9.3 La primitive `.mtb-tableau` de §7.6

`MASTER.md` §7.6 nomme le dépliage d'un long tableau sur téléphone : `thead` retiré visuellement, `tr`
en bloc, `td::before { content: attr(data-libelle) }`, **aucun conteneur à défilement horizontal**.
Une primitive nommée par `MASTER.md` s'écrit **en classe nue, une seule fois** (**décision 30**) →
**dans `base.css`**, dont cette chaîne est propriétaire exclusif ce lot.

**Point de réconciliation avec #15, remonté à `/lead-mtb`** : si #15 écrit ce dépliage dans
`assets/css/blocs/mtb-tableau-resultats.css`, il y aura **deux implémentations du même repli à
360 px**. `mtb-tableau-resultats.css` ne doit porter que ce qui est **propre** au tableau de résultats.

---

## 10. `404.html` et `search.html` — les liens de recours

`MASTER.md` §9.5 exige **trois** liens sur chacune : Accueil, Les portées, La meute. Il y en a **un**
aujourd'hui, et son `href="/"` est en dur (`404.html:16`, `search.html:26`).

**Ce qui est mesuré :**

- **`core/home-link` est le seul bloc du cœur qui calcule un lien de site au rendu** — il écrit
  `esc_url( home_url() )`. Son `block.json` déclare `"parent": ["core/navigation"]`, contrainte
  **d'éditeur** qui n'empêche pas `render_block()` de le rendre. Fabienne ne peut de toute façon pas
  ouvrir l'éditeur de site (**403**, décision 44). **À vérifier au rendu** : balisage exact et absence
  d'avertissement.
- **Aucun bloc du cœur ne rend un lien d'archive de type de contenu.**
  `render_block_core_navigation_link()` — le seul candidat, qui connaît `kind: "post-type-archive"` —
  **imprime l'attribut `url` stocké** ; le calcul est fait dans l'éditeur et gelé dans le balisage.

**Correction d'une prémisse de l'issue.** Le corps de l'issue et `docs/ETAT.md:90` affirment que
`BRIEF.md` §12 « impose de supporter une installation en sous-répertoire ». **C'est faux** : le mot
n'apparaît nulle part dans le brief ; §12 dit « le site doit pouvoir tourner sur un hébergement
mutualisé PHP standard », et rien d'autre. `href="/"` reste **objectivement faux** dès qu'un site n'est
pas à la racine d'un domaine, et le défaut mérite d'être payé — simplement, **il n'est pas bloquant au
titre du brief**.

**Ce qui est livré** : « Accueil » par `wp:home-link` (URL calculée) ; « Les portées » et « La meute »
par `href` statique. → **dette T28 : deux liens de recours à URL non calculée dans deux gabarits
statiques**, à payer par la première issue qui rouvre `functions.php` ou par un bloc
`mtb/lien-de-recours` côté extension.

**Ce qui est refusé** : transformer `404.html`/`search.html` en `404.php`/`search.php`. Ce serait dans
l'empreinte, ça marcherait, et ça laisserait **deux fichiers `.html` morts** que la troncature du §1.2
rend inatteignables **sans un mot** — exactement le piège silencieux que ce projet paie déjà trois fois.

---

## 11. États spéciaux et arbitrages

### 11.1 Table des états

| État | Émis par le serveur | Rendu par le thème |
|---|---|---|
| `aucune_portee` | `mtb_get_portees()` → `[]`, le bloc rend `''` | `/portees/` : en-tête, `h1`, pied. Rien d'autre |
| `aucun_chien` | `mtb_get_chiens_par_statut()` → `[]`, `mtb_grille_chiens_rendu()` → `''` | la Page n'affiche que son titre. L'état vide de §9.1 **n'existe que dans l'éditeur** |
| `donnee_absente` | `affichage === 'Non renseigné'` — constante unique | imprimé tel quel, en `--texte-doux`. **Jamais** un tiret, « Aucun » ou « Non testé » |
| `section_absente` | `sante_renseignee` / `titres_renseignes` faux ; `chiots`, `galerie`, listes vides | **ni titre ni section** (§9.3 l. 813) |
| `parent_hors_elevage` | `pere.etat` / `mere.etat` (`hydratation.php:347`) | nom + élevage **en clair, sans lien, sans carte grisée** (§9.3 l. 819) |
| `parent_donnee_absente` | `etat === 'donnee_absente'` | **pas de carte** ; les deux → pas de section |
| `aucun_chiot_liste` | `chiots === []` **et** `chiots_message` = « Liste des chiots non renseignée. » | `h2` **conservé** + la phrase **du serveur**, en `.mtb-etat-doux` |
| `page_protegee` | `etat === 'page_protegee'` + charge minimale | encart §9.5 — voir arbitrage 4 |
| `photo_absente_structurante` | `photo_principale === null` | cadre `4/5` **conservé**, nom d'usage au centre (§9.2). Aucun pictogramme |
| `disponibilite_inconnue` | `disponibilite.valeur === ''` | **aucun badge**, en silence |
| `chien_decede` | **aucun drapeau** — `date_deces.affichage` vaut « Non renseigné » pour un chien vivant | ligne omise sur `date_deces.valeur === ''` — voir arbitrage 5 |
| `pas_de_portee_voisine` | `null` | le lien correspondant n'existe simplement pas. Ni lien désactivé, ni mention |
| `aucun_resultat` | `array('colonnes'=>[], 'lignes'=>[])` | section absente — voir arbitrage 3 |

**Aucune de ces conditions n'est calculée par le gabarit.** `sante_renseignee`, `titres_renseignes`,
`chiots_message`, `effectif_texte`, `disponibilite.valeur`, `pedigree === null`, `galerie === []` sont
**tous** des signaux du serveur. Le gabarit fait `if (…)`, il ne décide pas.

**T13 — aucune cinquième convention d'état vide.** La décision 26 (« un composant sans contenu ne
s'affiche pas ; l'état vide n'existe que dans l'éditeur ») porte sur des composants **insérés dans une
Page**. Une fiche n'est pas composée, elle est **remplie** (décision 17) : aucun bloc n'y est inséré,
donc l'apparence §9.1 est **inatteignable** sur une fiche. La règle applicable est **§9.3 et §9.4, et
rien d'autre**.

### 11.2 Les arbitrages

| # | Désaccord | Décision | Raison |
|---|---|---|---|
| **1** | Le `h2` du palmarès : « Palmarès » (§9.3) ou « Palmarès de travail » (§7.5-5) ? | **« Palmarès de travail »** | §7.5 est la section qui **spécifie la fiche chien** ; §9.3 s'y réfère en raccourci. Et la précision porte : les titres d'exposition vivent sous « Titres et brevets » — « Palmarès » seul serait ambigu sur un site d'élevage |
| **2** | `mtb_get_portees_du_chien()` ne rend pas de photo ; le back propose d'appeler `mtb_get_portee()` par élément pour en obtenir une | **Pas de photo sur les cartes de portées.** Aucune lecture supplémentaire | §7.5-6 dit « en cartes », pas « avec photo ». N lectures pour une décoration que le design ne demande pas |
| **3** | Qui décide de l'affichage du palmarès : la fonction de lecture (back) ou la chaîne rendue (front) ? | **La chaîne rendue est la seule garde** : `if ( '' !== $palmares_html )`. Et le contrat impose à #15 que sa fonction de rendu rende `''` **si et seulement si** `mtb_get_resultats_travail_du_chien()['lignes']` est vide | Le front a raison : deux gardes indépendantes produiraient un `h2` orphelin le jour où #15 manque. Le back a raison sur le fond : décider qu'une section est vide est une affaire de serveur. L'équivalence imposée à #15 réconcilie les deux — une seule garde dans le thème, la décision restant serveur |
| **4** | Page protégée : encart §9.5 **et** `the_content()` → deux formulaires | **Un seul.** Sous `post_password_required()`, rendre l'encart §9.5 contenant `get_the_password_form()` et **rien d'autre**. **Ne pas appeler `the_content()`** | `the_content()` rend le formulaire tout seul. Deux formulaires sur une même page est un défaut visible à l'écran |
| **5** | `date_deces` vide : « Décédée le Non renseigné » ou ligne omise ? | **Ligne omise.** Test `'' !== $chien['date_deces']['valeur']` | « Décédée le Non renseigné » n'est pas une donnée absente, c'est un **faux fait d'élevage** sur un chien vivant. §9.3 régit les champs non remplis, pas les champs dont le vide **est** l'information |
| **6** | `nom_complet` vide sous le `h1` : « Non renseigné » ou ligne omise ? | **Ligne omise** | Ce n'est pas une ligne de `<dl>`, c'est un sous-titre. « Non renseigné » sous le nom d'un chien est du bruit, pas une information |
| **7** | Les deux parents vides : section « Les parents » absente ? | **Oui, section entièrement absente** | §9.3 l. 813, « une section entière non remplie n'est pas rendue » |
| **8** | Le texte libre : `post_content` par la boucle, ou une clé `texte_libre` à demander au serveur ? | **La boucle native**, `the_content()` | C'est du `post_content` WordPress natif, pas une méta `_mtb_`. Aucune fonction de lecture ne l'expose, et aucune n'a à le faire |
| **9** | `data-libelle` sur le tableau des chiots : le gabarit peut-il le poser ? | **Oui**, à condition stricte : la source est le **`libelle` de chaque cellule** (`chiots[$i]['nom']['libelle']`), jamais l'index dans `chiots_colonnes`, jamais un littéral écrit à la main | Lire le `libelle` de la cellule rend impossible un décalage d'index si l'ordre changeait. Le gabarit **recopie** ce que le serveur fournit : ce n'est pas une remontée de règle métier |
| **10** | Les libellés d'identité du chien, que le serveur ne fournit pas (`lecture.php:292-330`) | **Recopie verbatim** de la colonne « Libellé côté public » de `MASTER.md` §10.2, jamais une invention ni une abréviation. **Duplication inscrite en dette T29** | §10.2 est l'arbitre déclaré (décision 39). Sans l'inscription en dette, la divergence future ne serait imputable à personne |
| **11** | Appeler `mtb_bandeau_ouverture_porte_le_titre()` avant d'émettre le `h1` ? | **Non.** À la place, la recette **compte les `h1`** sur les fiches rendues | Décision 24 : aucun bloc n'est insérable dans une fiche, donc aucun bandeau d'ouverture n'y existe, donc la fonction rendrait toujours `false`. Une mesure vaut mieux qu'une garde spéculative |
| **12** | `get_the_title()` préfixe **« Protégé : »** sur un contenu protégé | **Constaté et documenté, jamais contourné** | Comportement du cœur. Composer un titre pour le retirer serait fabriquer une chaîne du domaine |

---

## 10 bis. Le thème est hybride — lis ceci avant de croire à une erreur

**`wp-content/themes/mtb/` est un thème de blocs qui contient trois fichiers PHP à sa racine.
Ce n'est pas un vestige, ce n'est pas une erreur, et il ne faut pas les « nettoyer ».**

`single-mtb_portee.php`, `single-mtb_chien.php` et `enveloppe-fiche.php` rendent les deux fiches ; tout
le reste du site — accueil, pages libres, archive des portées, recherche, 404 — reste un thème de blocs
ordinaire. Le §1 de ce contrat donne le mécanisme et la raison ; ce qui suit est ce qu'un développeur
qui ouvre `templates/` dans six mois doit savoir avant de toucher à quoi que ce soit.

**Pourquoi du PHP.** Un `.html` de `templates/` n'exécute aucun PHP, aucun bloc du catalogue ne rend les
champs de la fiche courante, et la liaison `core/post-meta` est fermée deux fois (métas protégées par la
décision 16, et elle rendrait la valeur stockée au lieu de l'`affichage`, contre la décision 18). Le
contrat #13 §19 renvoyait ce point « à trancher au brainstorm de #16/#17 » : **il est tranché ici.**

**Ce que le PHP oblige à écrire à la main**, parce que `template-canvas.php` ne s'exécute plus :
`<!DOCTYPE>`, `language_attributes()`, **la balise `viewport`**, **le `<title>`**, `wp_head()`,
`body_class()`, `wp_body_open()`, `wp_footer()`. Les deux du milieu sont les pièges : le cœur ne les
accroche que sur le chemin du canevas, et le thème ne déclare pas `add_theme_support('title-tag')`.

**Le `tabindex="-1"` du `<main>` est écrit littéralement, et c'est délibéré.**
`mtb_rendre_la_cible_focalisable()` (`functions.php:840-869`) est un filtre `render_block` : un `<main>`
écrit en PHP n'est pas un bloc et n'en bénéficie pas. **Le retirer casserait le lien d'évitement sur les
deux fiches** — la dette T2, payée au lot 5.

**L'ordre de `enveloppe-fiche.php` est le livrable, pas un détail.** Le corps est rendu **en mémoire,
avant `wp_head()`**, pour que tout ce qui s'enfile pendant le rendu soit connu à temps ; et
`mtb_feuilles_du_site()` est pré-appelée pour que `mtb-base` sorte **avant** les feuilles de blocs, sans
quoi la cascade s'inverse et les composants perdent contre `base.css` à spécificité égale.

**Ce que l'éleveuse y perd : rien.** La décision 24 lui interdit déjà d'insérer un bloc dans une fiche,
et la décision 44 lui ferme l'éditeur de site (`site-editor.php` → **403**). Elle n'a jamais eu accès à
ces gabarits, en `.html` comme en `.php`.

**Le jour où l'extension livrerait `mtb/fiche-portee` et `mtb/fiche-chien`** (blocs de contexte sur
`usesContext: postId`, dont `mtb/bandeau-ouverture` est le précédent dans ce dépôt), les trois fichiers
PHP se suppriment et deux `templates/single-mtb_*.html` prennent leur place, **sans toucher ni au CSS ni
au contenu**. Rien n'est fermé.

> **Arbitrage.** `/lead-mtb` avait d'abord tranché pour les deux blocs de contexte dans `mtb-core`. Son
> message est arrivé **après** que les trois agents d'implémentation avaient rendu. Il a ensuite
> **ratifié le livré**, deux de ses trois objections étant tombées à la vérification (T2 traitée par le
> `tabindex` littéral ; le budget « sur toutes les pages » sans objet, `fiches.css` n'étant servie que
> sur les fiches), et la troisième étant contredite par un contrat **gelé** : **#4 §2 place explicitement
> les appels du palmarès « dans le gabarit de fiche chien (thème, epic Gabarits) »**, et `CLAUDE.md`
> autorise textuellement le thème à « appeler les fonctions de lecture de l'extension ».

---

## 11 bis. Amendements du 2026-08-20, après mesure

Trois points de ce contrat se sont révélés faux **à la mesure**. Ils sont corrigés ici ; le corps du
document reste tel quel là où il est juste.

### A. Le nom de la fonction de palmarès — le gel provisoire était faux

Le §11.2 arbitrage 3 et le §16 figeaient `mtb_tableau_resultats_rendu( array( 'chien' => int ) )`.
**#15 avait déjà gelé autre chose**, et son code était livré :

| Fonction réellement exposée par #15 | Ce qu'elle fait |
|---|---|
| `mtb_tableau_resultats_rendu( string $discipline = '' ): string` | le tableau d'une **discipline**, pour la page Travail (#17) |
| **`mtb_tableau_resultats_du_chien_rendu( int $chien_id ): string`** | **le palmarès d'un chien** — un seul `<table>`, **sans `<section>` ni `<h2>`**, le `h2` étant explicitement laissé au gabarit (contrat #15 §4.2) |

Passer un `array` à la première depuis un fichier en `declare(strict_types=1)` aurait levé un
**`TypeError`** — une erreur fatale sur chaque fiche chien. C'est la seconde que `dev-front-mtb` a
appelée, et il a eu raison contre la consigne. **Le gel retenu est
`mtb_tableau_resultats_du_chien_rendu( int $chien_id ): string`.**

La leçon de méthode vaut d'être écrite : le mécanisme d'isolation a **fonctionné**. L'appel était sur
une ligne unique repérée par un commentaire, et la divergence entre deux chaînes parallèles a coûté
exactement ce qu'elle devait coûter — une ligne.

### B. T23 — les deux règles du §9.2 étaient incomplètes

Écrites **telles quelles** puis mesurées sur 27 URL, elles tiennent leurs deux promesses chiffrées
(134,39 → 86,39 et 172,78 → 86,39, prose inchangée à 16 px) **mais produisent deux écarts nuls que le
contrat n'avait pas prévus** : `/portees/` `h1` → bloc liste, 24 → **0** ; « dernière portée » → grille
de chiens, 48 → **0**.

Cause mesurée : **quatre des dix composants** — `liste-portees`, `grille-chiens`, `tableau-resultats`,
`bandeau-ouverture` — ne déclarent **aucune** marge ; la première règle leur retirait le seul écart
existant. Le §9.2 annonçait la limite autrement (« deux composants dont aucun ne porte 86,4 donneraient
48 ») : il sous-estimait le cas où **aucun des deux** n'en porte.

**Trois règles, et rien de plus** : **9.2-1 restreinte** aux frontières composant/composant, les deux
valeurs chiffrées du contrat conservées · **9.2-2 conservée mot pour mot** · **9.2-3 ajoutée** — toute
frontière composant/composant vaut `--rythme-section`, ce qui est la lettre de `MASTER.md` §7.3.
Mesuré après : `/portees/` 24 restauré, dernière portée → grille 86,4, et deux défauts **antérieurs à
cette issue** refermés au passage (deux tableaux consécutifs 0 → 86,4 ; bandeau d'ouverture → composant
0 → 86,4). Retour en arrière : trois lignes.

### C. `.mtb-photo` et `.mtb-dispo` devaient bien être écrites

Le §12.3 disait « déjà habillées ailleurs, ne les redéfinis pas ». **La prémisse était fausse**, et la
mesure l'a montrée : sur une fiche chien, le `head` ne contient que `mtb-jetons`, `mtb-base`,
`mtb-entete-pied` et deux feuilles de blocs — **ni `mtb-liste-portees`, ni `mtb-derniere-portee`**,
seuls domiciles actuels des deux primitives. Sans les écrire, le portrait n'aurait eu ni ratio, ni
cerne, ni `object-fit` — l'image aurait dicté sa hauteur, ce que `MASTER.md` §6 interdit en tête — et le
badge aurait perdu sa pastille.

Elles sont donc écrites **nues et à l'identique de leurs sœurs**, dans `fiches.css`, où aucune feuille
sœur n'est servie : **aucun conflit de cascade, mesuré**. Le jour du hissage en primitive commune, ce
paragraphe de `fiches.css` est une **suppression**, jamais un arbitrage — c'est la condition posée par
la dette **T21**.

### D. L'étiquette d'un champ de **date** disparaît quand la date est absente — tranché

Le §5 imposait d'imprimer `date_naissance.libelle` **et** `.affichage` sans exception, donc « **Née le
Non renseigné** » sur une portée non datée. Le contrat gelé **#13** disait l'inverse pour le bloc liste.
Deux contrats gelés, deux règles, même donnée.

**`/lead-mtb` a tranché pour #13, qui avait déjà arbitré la question mot pour mot :**

> `__etiquette` (« Née le ») **n'est rendue que quand la date existe**. (`issue-13.md:478`)
> Date absente : imprimer « Non renseigné », **sans l'étiquette « Née le »**. […] Omettre rendrait une
> portée sans date **indiscernable d'un bogue de rendu**, contre D12. (`issue-13.md:742`)

**Règle retenue : valeur de date absente → « Non renseigné » en `--texte-doux`, étiquette supprimée.**
`MASTER.md` §10.2 est intacte — « Née le » reste le libellé public **quand la date existe**.

**La règle exacte — re-gelée le 2026-08-20 après que `dev-integration-mtb` a montré que la première
rédaction se contredisait elle-même.** Elle ne dépend pas du libellé, mais de **la structure qui le
porte** :

| Structure | Valeur absente |
|---|---|
| **Structure libre** — étiquette en `<span>`, carte, bandeau | **étiquette supprimée**, « Non renseigné » en `--texte-doux` |
| **Liste de définition** — `<dt>` / `<dd>` | **la paire est conservée**, `<dd>` = « Non renseigné » |
| Cas nommément arbitré | ligne **entièrement omise** — aujourd'hui « Décédé le » seul (§11.2 arbitrage 5) |

**Pourquoi la structure et non le libellé.** Dans une structure libre, l'étiquette est un fragment de
phrase et « Née le Non renseigné » n'est pas du français. Dans un `<dl>`, retirer le `<dt>` laisse un
`<dd>` **orphelin** : une valeur dont plus rien ne dit à quel champ elle se rapporte, une liste de
définition malformée, et un lecteur d'écran qui perd l'association libellé/valeur. **On dégraderait
l'accessibilité au nom de l'accessibilité.** Et « Né le | Non renseigné » se lit comme une ligne de
tableau, pas comme une phrase : l'objection de français ne s'applique pas à cette structure.

**Ce que le code fait, mesuré** : `single-mtb_portee.php:86` et `single-mtb_chien.php:370` suppriment
l'étiquette — ce sont des structures libres. La liste d'identité de la fiche chien conserve
`<dt>Né le</dt><dd>Non renseigné</dd>` — c'est un `<dl>`. **Les deux sont justes**, et c'est la première
rédaction de cet amendement qui était fautive en les opposant.

**Distinction à ne pas confondre, elle est réelle et cohérente** : dans un **composant** (encart
dernière portée, coordonnées), une valeur absente **fait disparaître sa ligne** — contrats #11 et #12,
décision 21. Dans un **champ de fiche**, elle imprime « Non renseigné » (`MASTER.md` §9.3). Ce contrat
construit des fiches : c'est la seconde règle. **T13 est déjà à quatre conventions ; il n'en est créé
aucune cinquième.**

### E. Les parents apparaissent sur la fiche chien — le §6 est amendé

Le §6 fermait la liste des `h2` de la fiche chien sur les sept points de `MASTER.md` §7.5, qui
**n'énumère aucune ascendance**. C'était suivre le design system contre le brief.

**`docs/BRIEF.md` §5.2 inscrit « Père × Mère | liens ou noms libres » parmi les champs d'un Chien**, et
le brief est la source de vérité produit, **au-dessus de `MASTER.md`**. Mesuré : « Icar de l'Orée des
Crayères » est en base sur la fiche de Jango et compte **0 occurrence** sur sa page. Fabienne remplit
deux champs pour rien — **la violation D1 la plus nette du projet**, et précisément ce que ce lot répare.

**Sans huitième section et sans nouveau `h2`** : deux paires `<dt>`/`<dd>` ajoutées **à la fin de la
liste de définition d'identité** de §7.5-2. Libellés **« Père »** et **« Mère »**, littéralement
`MASTER.md` §10 l. 876-878. Valeur : lien vers la fiche quand le parent est interne ; sinon le nom libre
en texte, avec l'élevage quand il existe. Absent → « Non renseigné », **étiquette conservée** (amendement
D).

**Piège** : sur une **fiche chien** un parent est décrit par **`type`** (`lecture.php:500`, déduit du
seul `_mtb_*_fiche`) ; sur une **portée** c'est **`etat`** (`hydratation.php:347`). Deux vocabulaires
pour la même notion — un test sur `etat` ne trouverait jamais rien sur un chien.

**Dette portée par `/lead-mtb`** : `MASTER.md` §7.5 est à amender par `lead-design-mtb` pour y inscrire
l'ascendance.

### F. Le palmarès passe par le bloc, non par la fonction

L'amendement A retenait `mtb_tableau_resultats_du_chien_rendu( int )`. **Mesuré : la poignée
`mtb-bloc-mtb-tableau-resultats` est absente du `head` d'une fiche chien** — un appel de fonction ne
déclenche jamais `wp_enqueue_block_style()`, donc **tout ce que #15 met de spécifique dans sa feuille
manque sur la fiche**. La primitive `.mtb-tableau` hissée en `base.css` sauve le repli à 360 px, pas le
reste.

**Interface retenue, figée par `/lead-mtb` pour les trois chaînes du lot :**

```php
do_blocks( '<!-- wp:mtb/tableau-resultats {"source":"chien-courant"} /-->' )
```

Un seul `<table>`, **sans `<section>` ni `<h2>`** : le `h2` « Palmarès de travail » reste à la charge du
gabarit (contrat #15 §4.2). L'enveloppe rendant le corps **en mémoire avant `wp_head()`**, l'enfilage
arrive à temps. La garde reste la chaîne rendue, seule (§11.2 arbitrage 3).

### G. Le commentaire de l'éleveuse s'affiche aussi sur une fiche chien

`MASTER.md` §7.5 ne prévoit **aucune** section de prose, là où §7.4-4 en prévoit une sur une portée.
Mais l'écran de saisie offre le champ (`fields/chien/ecran.php:274`) et le type déclare
`supports => editor` (`content/chien/bootstrap.php:70`) : **elle pouvait écrire un texte, l'enregistrer,
et ne jamais le voir.**

Ce n'était pas théorique : la reprise a placé **dans le texte libre des quatre fiches de chiens** tout ce
qu'aucun champ du modèle ne pouvait accueillir — les trois paragraphes de récit sur Rolex, les mentions
`✞ … DCD`, le `TI (x X x)` de Pégaz, les portées futures annoncées. **Contrainte 4 — « rien de l'ancien
site ne se perd » — enfreinte en silence, au moment même où l'issue est censée la démontrer.**

Section ajoutée sur le modèle de §7.4-4 : `<section class="mtb-fiche-chien__commentaire">`, **sans
`h2`**, prose par la boucle native, **section entièrement absente si la prose est vide**. Écart à
`MASTER.md` §7.5 **assumé et signalé** : les quatre contraintes non négociables passent avant le silence
d'un § du design system.

---

## 12. Chaînes fournies par le serveur

Le thème les **imprime**. Il n'en compose, n'en accorde et n'en reformate **aucune**.

- Les trois libellés de disponibilité : **Chiots disponibles · Tous réservés · Portée passée**
  (`hydratation.php:57-63`, verbatim de `MASTER.md` §3.3). Quatrième état **interdit**.
- « **Non renseigné** » — constante unique (`hydratation.php:33`, `choix.php:34`, `interne.php:19`).
- « **Liste des chiots non renseignée.** » (`hydratation.php:274`).
- Les libellés **accordés au sexe**, faits une seule fois dans tout le projet
  (`content/chien/choix.php:140-142`) : **Né le / Née le · Décédé le / Décédée le · Reproducteur /
  Reproductrice · Retraité / Retraitée · Disparu / Disparue**. *En cours de confirmation* est
  invariable. **Sexe vide → forme masculine canonique** ; jamais de point médian, jamais de « (e) ».
- Les titres de groupes de « La meute » : **Reproducteurs · En cours de confirmation · Retraités ·
  Disparus** (`choix.php:169-177`) — le pluriel **ne s'accorde pas au sexe**, c'est un groupe mixte.
- Les libellés de colonne des chiots et du palmarès, les dates formatées, `effectif_texte`.

**Les seuls littéraux français que le thème écrit**, tous repris verbatim de `MASTER.md` §7.4, §7.5,
§9.5 et §10.2 : les titres de section (`Les parents`, `Les chiots`, `La galerie`, `Santé`,
`Titres et brevets`, `Palmarès de travail`, `Portées`, `Galerie`), le `h1` `Les portées`, les libellés
`Portée précédente` / `Portée suivante`, `(nouvelle fenêtre)`, `Accueil` / `Les portées` / `La meute`,
et les libellés d'identité de la fiche chien de §10.2 (arbitrage 10).

---

## 13. Contenu de démonstration

**Q17 tranchée par l'utilisateur le 2026-08-20 : on recopie le réel de mtbrabant.com, verbatim.**
Échantillon **petit** — 2 à 3 portées, 3 à 4 chiens — strictement ce qu'il faut pour prouver que les
quatre gabarits rendent vraiment. **Ce n'est pas la reprise complète**, qui est l'epic #19-#21.

**Deux constats bloquants établis par le plan back :**

1. **`docs/migration/` n'existe pas.** Le dossier que `ETAT.md` désigne comme la seule source légitime
   des données d'élevage est absent du dépôt. `contenu-mtb` doit **d'abord constituer l'instantané**
   (un fichier par URL source, l'URL en tête), puis saisir depuis lui.
2. **Il n'existe aucun importeur.** `docker/provision/provision.sh:142-153` teste
   `wp mtb import-fixtures` et **constate que la commande n'existe pas** (dette #29) : les trois
   `docker/fixtures/*.json` ne sont **jamais chargés**. La saisie se fera à la main (WP-CLI ou
   administration), acceptable à cette échelle. **`provision.sh` reste hors empreinte.**

**L'échantillon se choisit pour couvrir les branches, pas pour être joli.** Au minimum : une portée à
**étalon extérieur sans fiche** (branche `parent_hors_elevage`) et une à **deux parents fichés** ; un
chien **mâle** et une **femelle** (accord au sexe) ; **au moins un chien qui a réellement des résultats
sur `mtbrabant.com/travail/`**, sinon « section absente » devient indistinguable de « cassé ».

**Pièges de correspondance à ne pas confondre :**

- `_mtb_males` et `_mtb_femelles` sont stockés **en chaîne**, jamais en entier (décision 21) : c'est la
  seule façon de distinguer « **0 mâle** » de « non renseigné ». Un champ absent reste `''`, **jamais**
  `'0'`.
- `_mtb_galerie` est un **tableau** sur une portée et une **chaîne à virgules** sur un chien. Écrire la
  même forme des deux côtés produit une galerie vide **en silence**.
- Une portée porte `_mtb_pere_type` ∈ `fiche` · `exterieur` ; **une fiche chien n'en a pas** —
  `parent_de()` (`lecture.php:500`) déduit du seul `_mtb_*_fiche`.
- **Un chien sans `_mtb_statut` n'apparaît dans aucun groupe de « La meute »** et devient invisible
  sans que personne ne le remarque.

**Ce que `contenu-mtb` doit refuser de faire** : déduire `passee` d'une date ancienne · déduire
`disparu` d'une date de décès, ou l'inverse · écrire « France » dans un pays vide · « nettoyer » la
graphie d'un n° LOF · ranger une ligne de travail sous une discipline que le site source ne lui donne
pas (le site range lui-même *Cavage* sous « Autres disciplines » — on recopie **son** classement).
**Toute ambiguïté est une question bloquante remontée, jamais un trou comblé.**

---

## 14. Interdits

1. **Interroger la base** : `WP_Query`, `get_posts`, `get_post_meta`, `get_terms`, `$wpdb`.
   Seule dérogation, ratifiée par la **décision 41** : les API de **navigation** du cœur, aucune donnée
   d'élevage. La frontière se vérifie d'un `grep`.
2. **Appeler `MTB\Core\*`.** Vérifiable d'un `grep` sur `MTB\` dans le thème.
3. **Composer une chaîne du domaine** : « 3 mâles, 2 femelles », un libellé de discipline, un titre de
   groupe, un libellé de statut, un « Né le ».
4. **Accorder au genre.** L'accord est fait une seule fois, `content/chien/choix.php:140-142`.
5. **Reformater** une date, un n° LOF, une cotation, une dysplasie, un pourcentage. `number_format()`,
   `number_format_i18n()` et `date_i18n()` sont **interdits** sur toute valeur venue d'une fonction de
   lecture.
6. **Assainir en entrée.** Le gabarit ne coupe rien, ne strippe rien : une cotation `<60%` sort telle
   quelle après `esc_html()` (**décision 20** — `sanitize_text_field` viderait la valeur en silence).
7. **Imprimer `disponibilite.affichage` sans avoir testé `disponibilite.valeur !== ''`.** Un quatrième
   badge n'a ni forme, ni couleur, ni preuve d'accessibilité.
8. **Imprimer `date_deces` sans avoir testé `date_deces.valeur !== ''`.**
9. **Réimplémenter le tableau du palmarès** à partir de `colonnes` / `lignes` / `cellules`, même
   « en attendant ». Ce serait un **troisième** tableau, trois occasions de diverger sur `data-libelle`
   et sur la règle à 48 rem ; et les deux règles du contrat #5 §5.3 (« lien ssi `url !== ''` »,
   « cellule vide ssi `affichage === ''` ») sont des **règles métier** que le thème n'a pas à porter.
10. **Réécrire le balisage d'un composant du catalogue.** La galerie passe par `render_block()`.
11. **Poser un `pre_get_posts`** pour colmater T8 : c'est du `mtb-core` et c'est **#23**.
12. **Émettre une couleur hors des quinze jetons**, introduire une requête vers un domaine tiers, ou
    introduire du JavaScript qui conditionne la lecture d'un contenu.
13. **L'extension n'émet aucune règle visuelle** — et cette issue n'écrit aucune ligne d'extension.

---

## 15. Accessibilité et poids — ce qui sera réellement mesuré

| Exigence | Vérification |
|---|---|
| **Un seul `h1`** par gabarit | compter les `<h1>` sur les 4 URL |
| Hiérarchie sans saut | `axe-core` `heading-order` |
| **Lien d'évitement** | tabuler, activer, vérifier le focus sur `<main>` — **sur les deux fiches**, pas seulement 404 |
| Focus visible | relever l'anneau sur **chaque** arrêt clavier de chaque fiche |
| **360 px sans défilement horizontal** (§7.7) | **par une iframe de 360 px** — Chrome headless a un plancher ~500 px sur ce poste, une capture directe **ment** |
| **Zoom 200 %** (§7.8) | 1440→720, 1280→640, 360→180 |
| Tableaux dépliés sous 48 rem | `data-libelle` présent sur **chaque** `<td>` ; **aucun conteneur à barre de défilement** (§7.6) |
| `alt` utile | **vérifié, et le repli EST perdu sur la galerie.** Le portrait, écrit par le thème depuis `photo_principale.alt`, porte bien `alt="Photo de Jango"`. Les images de **galerie** sortent `alt=""` : `render_block()` ne transmet que les identifiants, et `galerie-photos/rendu.php:152-166` **refuse délibérément** de composer un repli, le nom accessible du lien étant porté par `<span>Photo 1 sur 1</span>`. C'est le comportement **contractuel de #12**, pas un défaut du thème — mais les replis soignés de `hydratation.php:469-475` et `lecture.php:590-599` ne servent donc **pas** sur les fiches. À arbitrer par `lead-design-mtb` |
| Images | `loading="lazy"`, `decoding="async"`, `width`/`height`. **Aucun `fetchpriority="high"`** — §6.5 le réserve au bandeau d'ouverture, absent de ces fiches |
| Zéro origine tierce, zéro cookie anonyme, zéro `<script src>` | relevé réseau au navigateur |
| **Budget** | page la plus lourde ≤ **200 000 o** (117 279 o au lot 5). `fiches.css` n'entre que sur les fiches |

---

## 16. Dettes créées ou aggravées par cette issue

Numéros **proposés** ; `docs/ETAT.md` appartient à `/lead-mtb`.

| # | Dette |
|---|---|
| **T28** | **Deux liens de recours à URL non calculée** (« Les portées », « La meute ») dans `404.html` et `search.html`. Faux dès qu'un site n'est pas à la racine d'un domaine, et muets si un slug change. Payable par la première issue qui rouvre `functions.php`, ou par un bloc `mtb/lien-de-recours` côté extension |
| **T29** | **Les libellés d'identité de la fiche chien sont écrits dans le thème**, faute de `libelle` sur les champs de `mtb_get_chien()` (`lecture.php:292-330`), alors que la portée les fournit. Duplication : un libellé changé dans `MASTER.md` §10.2 devra l'être à deux endroits |
| **T30** | **« La meute » n'existe que dans la base de développement.** Sur l'installation de Fabienne, la page n'existera pas et le lien de recours pointera dans le vide. La fiche d'aide dit « créez cette page », jamais « elle existe » |
| **T31** | **Deux balisages de tableau coexistent** — le palmarès (extension, #15) et les chiots (thème, #16) — donc deux implémentations de la **décision 10**. Atténué, pas fermé, par la primitive `.mtb-tableau` unique de `base.css` : les deux **doivent** la porter |
| **T18 aggravée** | La règle `object-position: var(--point-interet, 50% 38%)` était écrite **cinq** fois ; le portrait de la fiche chien en fait une **sixième**. Pour `lead-design-mtb` |

**Dettes constatées et non payées par cette issue** : **T8** (la recherche et le sitemap ne filtrent
pas `has_password` — l'archive `/portees/` est refermée de fait par §7.1, le reste appartient à **#23**),
**T21**, **T13** pour le lot 3, **T14**, **T16-bis**, **#29** (aucun importeur de fixtures).

**Manques réels de l'extension, nommés et jamais comblés par le thème** : `mtb_get_chien()` rend `null`
sur un brouillon, donc l'aperçu d'une fiche non publiée n'a aucune donnée (**M1**) · aucun drapeau
« ce chien est décédé » (**M3**) · aucune fonction de rendu de galerie appelable depuis un gabarit
(**M4**, contourné par `render_block()`) · le parent d'une **portée** dont la fiche est protégée
retombe en `donnee_absente` et **perd son nom**, alors que le même cas sur une **fiche chien** conserve
le nom (`hydratation.php:311` contre `lecture.php:550`) — deux comportements pour la même situation,
et une perte de généalogie (**M6**).
