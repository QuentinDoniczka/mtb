# Contrat d'interface — Issue #15 — Composant Tableau de résultats

**Gelé le 2026-08-20** par `lead-issue-mtb` (chaîne #15 du lot 6), après réconciliation des plans de
`leaddev-back-mtb` et `leaddev-front-mtb`, qui ont travaillé en aveugle l'un de l'autre.

**Il est contraignant à partir d'ici.** Les six désaccords entre les deux plans sont tranchés en §11 ;
aucun n'est laissé à l'appréciation d'un dev.

---

## 0. Le fait qui a reconfiguré toute l'issue

**Le thème `mtb` est un thème de blocs : `templates/*.html` n'exécute aucun PHP.**

Ma consigne d'origine — et le corps de l'issue — supposaient que le gabarit de fiche chien de #16
appellerait une fonction PHP de rendu. **C'est impossible.** La preuve est dans le dépôt :
`mtb_grille_chiens_rendu()`, livrée par #14 pour exactement cet usage, **n'a aujourd'hui aucun
appelant**, et `docs/contracts/issue-13.md` §19 le constate déjà.

Le seul chemin réel de #16 et #17 vers ce composant est le **commentaire de bloc dans le gabarit**.
Le composant est donc conçu pour ça, et c'est ce qui justifie le mode `chien-courant` du §3.

Effet de bord heureux : passer par `render_block()` déclenche le filtre `render_block_{$nom}` sur
lequel `wp_enqueue_block_style()` accroche la feuille de style. **Le risque « feuille jamais mise en
file » que je redoutais ne se réalise pas par ce chemin.** Il reste réel par un autre, voir la dette
T-#15-a en §12.

> **Amendement du 2026-08-20 — ce paragraphe est faux depuis le milieu du lot, et c'est le plus
> important de tous.** La prémisse était vraie au gel ; **#16 a ensuite introduit des gabarits PHP
> classiques** (`single-mtb_chien.php`, `single-mtb_portee.php`, `enveloppe-fiche.php`) et appelle la
> fonction PHP, `single-mtb_chien.php:237-238`. Le chemin que ce §0 déclarait inexistant est **le
> chemin réellement emprunté**. Conséquence mesurée : sur `/chien/luna/` et `/chien/rex/`,
> `mtb-tableau-resultats.css` est **absente, zéro occurrence**.
>
> **Le composant n'en souffre pas, et c'est mesuré, pas déduit.** #16 a aussi hissé la primitive dans
> `base.css` §10 (l. 964-1115), servie sur toutes les pages. Sur les deux chemins, à 360 px :
> `scrollWidth === clientWidth === 360`, **aucun défilement horizontal**, `thead` découpé, `td` en
> grille `128px 184px`, étiquette `::before` en `--laiton-texte`, cellule vide en `display: none`.
> Contre-épreuve : injecter la feuille de bloc dans la page de Rex ne change **aucune** des 21
> propriétés calculées relevées, ni à 360 px, ni à 1440 px.
>
> **La conclusion pratique tient en une phrase, et elle est écrite dans le code des deux côtés** :
> l'habillage de ce tableau ne vient plus de sa feuille de bloc mais de `base.css` §10 — donc **le
> jour où quelqu'un retirerait ce §10, les deux chemins tombent, pas seulement celui du gabarit.**

---

## 1. Fonctions de lecture consommées — jamais réimplémentées

Le module **consomme** `includes/query/resultat/`, il ne le modifie pas et n'en refait aucune requête
(décision 19 : le type qui possède la donnée possède sa lecture).

| Fonction | Passée par le bloc | Retour, cas vide |
|---|---|---|
| `mtb_get_resultats_travail_par_discipline( array $args = array() ): array` | `ordre => 'annee_desc'` (§7.6 le gèle) ; `disciplines => array()` ou `array( $cle )` | liste ordonnée de **groupes** ; **`array()`** |
| `mtb_get_resultats_travail_du_chien( int $chien_id, array $args = array() ): array` | **aucun `$args`** — le défaut `annee_asc` est le bon, une carrière se lit dans son sens | `array( 'colonnes' => …, 'lignes' => … )`, les deux vides |
| `mtb_resultat_disciplines(): array` | — | 9 clés → libellés, **seule énumération du projet** |

Les trois sont appelées **sous `function_exists()`**. Absentes, le bloc rend `''` sans erreur.

La forme des groupes, des lignes et des cellules est celle du **contrat gelé #5 §5.2 et §5.3**. Ce
contrat-ci ne la recopie pas et ne l'amende pas. Trois règles de #5 sont reprises ici parce qu'elles
sont la source des deux clauses les plus fragiles du présent contrat :

- **Lien si et seulement si `url` est une chaîne non vide.** `etat` n'est **jamais** lu pour décider
  d'un lien, ni pour quoi que ce soit d'autre.
- **Cellule vide si et seulement si `'' === $cellule['affichage']`.** On ne teste **jamais** `valeur`.
  En pratique **seule la colonne Pays** peut être vide : une année ou un conducteur absents valent
  « Non renseigné », donc **non vides**, et leur étiquette s'imprime.
- **Le consommateur parcourt `colonnes`**, jamais les clés de `cellules`, et **ne trie ni ne regroupe**.

---

## 2. Fonctions de rendu exposées par l'extension

Espace de noms **global**, dans un second bloc `namespace { }` à accolades de `bootstrap.php`,
déclarées sous `if ( ! function_exists( … ) )`. Précédent gelé : `docs/contracts/issue-14.md` §3 bis.

```php
mtb_tableau_resultats_rendu( string $discipline = '' ): string
mtb_tableau_resultats_du_chien_rendu( int $chien_id ): string
```

| Signature | Rend | Cas « rien à afficher » |
|---|---|---|
| `mtb_tableau_resultats_rendu( string $discipline = '' ): string` | Le balisage du §4.1. `''` = **toutes** les disciplines, dans l'ordre de la fonction de lecture, **groupes orphelins compris**. Une clé = ce groupe seul. Ordre `annee_desc`. | **`''`** — jamais l'état vide, jamais une enveloppe nue, jamais un commentaire HTML |
| `mtb_tableau_resultats_du_chien_rendu( int $chien_id ): string` | Le balisage du §4.2 : **un seul `<table>`, sans `<section>` et sans `<h2>`**. Ordre `annee_asc`. | **`''`** |

- **Jamais préfixées `mtb_get_`** : décision 16, `mtb_get_*` rend des données, jamais du HTML.
- **Retour `string`, jamais `echo`.**
- Les deux passent par **un seul chemin de code** interne,
  `\MTB\Core\Blocks\TableauResultats\rendu( array $attributs, bool $rendu_de_bloc = true, ?int $chien_id = null )`.
  Aucune ligne de balisage n'est écrite deux fois.
  *Le troisième paramètre a été ajouté à l'implémentation, il n'était pas au contrat : sans lui,
  `mtb_tableau_resultats_du_chien_rendu( 0 )` retombait sur la requête courante au lieu de rendre `''`.
  `null` signifie « déduis-le du contexte », un entier signifie « celui-ci, et aucun autre ».*
- **L'appelant ne prend aucune décision d'état** : il imprime ce qu'il reçoit, `''` compris.
- Le thème les appellerait sous `function_exists()` — mais **il ne les appellera pas** : voir §0.
  Elles sont livrées parce que la **décision 24** les exige, et comme filet si le contexte `postId`
  du mode `chien-courant` se révélait indisponible.

**Ces deux noms sont figés et ont été communiqués aux chaînes #16 et #17 le 2026-08-20, avant
l'écriture du code.** Ils ne changent plus.

---

## 3. Bloc enregistré

| Élément | Valeur — gelée |
|---|---|
| Nom | **`mtb/tableau-resultats`** |
| Titre dans l'insérteur | **« Tableau de résultats »** |
| Catégorie | **`mtb`** (« Mont Brabant »), livrée par `blocks/categorie-mtb/`. **Aucun filtre `block_categories_all` dans ce module** (décision 25) |
| Icône | `editor-table` (Dashicon, servi depuis notre domaine) |
| Attribut `discipline` | `string`, défaut **`""`** = toutes les disciplines. **Sans `enum`** — voir ci-dessous |
| Attribut `source` | `string`, défaut **`"discipline"`**, valeurs `discipline` \| `chien-courant`. **N'apparaît dans aucun réglage de l'éditeur** ; `editeur.js` ne l'écrit jamais |
| `usesContext` | `[ "postId", "postType" ]` |
| `multiple` | **`true`** — sans lui, deux tableaux de disciplines différentes sur une page seraient interdits |
| `supports` | Forme de `bandeau-ouverture` / `grille-chiens` : **l'absence ferme**. Pas de `color`, `typography`, `spacing`, `dimensions`, `border`, `shadow`, `background`, `layout`. `className`, `customClassName`, `html`, `anchor`, `ariaLabel`, `align`, `alignWide` à `false` |
| **Amendement du 2026-08-20** | **`className: false` et la racine à deux classes du §4.1 étaient incompatibles sous le cœur**, et je ne l'avais pas vu. C'est le support `className` — et non les classes passées à l'enveloppe — qui commande l'ajout de `wp-block-mtb-tableau-resultats` : fermé, la racine sortait avec **une seule** classe sur le chemin du bloc et **deux** sur le chemin de gabarit. Deux chemins exposés, deux balisages : exactement ce qu'un contrat doit interdire. **Correction retenue** : la classe engendrée est composée par `attributs_conteneur()` sur **les deux** chemins, son nom demandé au cœur par `wp_get_block_default_classname()` plutôt que recopié. `className` reste `false` — l'éleveuse n'atteint toujours aucune classe. **Mesuré : les trois chemins rendent `class="mtb-tableau-resultats wp-block-mtb-tableau-resultats"`.** La feuille du thème ne vise `.wp-block-*` nulle part, donc aucune décision visuelle n'en dépend |
| Pas de `"example"` | Il déclencherait un appel REST au survol dans l'insérteur et afficherait de **vrais résultats d'élevage** en guise d'aperçu |
| Pas de `"textdomain"` | Contrat #1 §7 : aucune fonction i18n dans `mtb-core` |
| Pas de `"style"` ni `"editorStyle"` | Décision 28 : le CSS du bloc vit dans le thème |
| Pas de `"viewScript"` ni `"script"` | **Zéro octet de JavaScript côté visiteur** |
| Rendu | `"render": "file:./render.php"` |
| Poignée du script d'éditeur | **`mtb-tableau-resultats-editeur`** |
| Objet JS de localisation | **`mtbTableauResultats`** |
| Espace de noms PHP | `MTB\Core\Blocks\TableauResultats` |
| Hook ou filtre exposé | **aucun** — surface close |

**Pourquoi `discipline` n'a pas d'`enum`.** Un `enum` fait **rejeter** par le cœur toute valeur hors
liste et retomber sur le défaut — ici `""`, c'est-à-dire **toutes les disciplines**. Une instance
réglée sur une discipline devenue orpheline basculerait donc **silencieusement en « toutes »** et
afficherait neuf tableaux là où l'éleveuse en attend un. C'est l'inverse exact de la garantie de #5,
et le même raisonnement que son arbitrage 9 (`sanitize_key` et non liste blanche). La liste close vit
**dans le réglage de l'éditeur**, pas dans le schéma.

### Le réglage, et pourquoi son défaut est « toutes »

| Élément | Chaîne exacte |
|---|---|
| Titre du panneau et étiquette | **« Discipline à afficher »** |
| Première option, valeur `""`, **cochée d'avance** | **« Toutes les disciplines »** |
| Options suivantes | Les neuf libellés de `mtb_resultat_disciplines()`, imprimés tels quels |
| Aide sous le réglage | **« Toutes les disciplines » affiche un tableau par discipline, chacun sous son titre — y compris une discipline qui ne serait plus dans la liste. Une discipline choisie n'affiche que son tableau.** |

Contrôle : **`RadioControl`**, comme `grille-chiens/editeur.js`. Dix options font un panneau plus haut
qu'un `SelectControl`, coût assumé : les dix choix restent visibles d'un coup, et deux composants
frères qui posent la même question ne divergent pas (esprit de la décision 39).

**La liste n'est jamais retapée en JavaScript.** Elle part de `mtb_resultat_disciplines()` vers
`editeur.js` par `wp_localize_script()`, appelé dans `enregistrer()` sur `init` 20 — précédent exact
`grille-chiens/bootstrap.php`. `mtb_resultat_disciplines()` est un tableau littéral, zéro requête,
donc rien à différer.

**L'arbitrage du défaut, et sa raison de fond.** Un sélecteur ne peut proposer que les neuf clés
canoniques. Une discipline **orpheline** — valeur stockée sortie de la liste, §6 état 3 de #5 —
serait donc **inatteignable par quiconque**, et la garantie de #5 (*rien ne disparaît en silence*)
serait annulée par la surface d'édition de #15. « Toutes » par défaut la restaure, et fait passer la
page Travail de neuf insertions à une.

### Le mode `chien-courant`

`source` n'est écrit que par un gabarit, sous cette forme et aucune autre :

```html
<!-- wp:mtb/tableau-resultats {"source":"chien-courant"} /-->
```

Résolution de l'identifiant, **trois crans, dans cet ordre** :

1. `$block->context['postId']` s'il est présent et `> 0` ;
2. sinon `get_queried_object()`, et **l'identifiant n'est retenu que si l'objet interrogé est un
   `WP_Post`** ;
3. **puis, dans les deux cas, un garde-fou de type** : si `get_post_type( $id ) !== 'mtb_chien'`, la
   fonction rend `''`.

> **Amendement du 2026-08-20 — le cran 2 a été corrigé après mesure, ma rédaction initiale était
> fausse.** J'avais écrit `get_queried_object_id()`, en pensant que le garde-fou de type fermerait la
> collision `term_id` / `post_id`. **`dev-back-mtb` l'a mesurée et elle passait** : sur une archive de
> terme, `get_queried_object_id()` rendait `20`, `get_post_type( 20 )` rendait `'mtb_chien'` (la fiche
> de Luna), le garde-fou laissait passer, et **le palmarès de Luna s'affichait sur une page sans
> rapport** — exactement le scénario que ce paragraphe prétendait empêcher. Un identifiant ne dit pas
> de quelle table il vient ; seul l'**objet** le dit. Demander `get_queried_object()` et n'accepter
> qu'un `WP_Post` ferme la collision à la source. Re-mesuré : collision simulée → `''` ; vraie fiche
> chien → identifiant correct, aucune régression. Le garde-fou de type reste : il est le second
> verrou, pas le premier.

`'mtb_chien'` est la **seule** chaîne littérale de type de contenu du module. Elle nomme le mode, pas
une colonne.

---

## 4. Le balisage rendu — gelé, ligne à ligne

### 4.1 Mode discipline

```html
<div class="mtb-tableau-resultats wp-block-mtb-tableau-resultats">
  <section data-discipline="ring">
    <h2>RING</h2>
    <table class="mtb-tableau">
      <caption>RING</caption>
      <thead>
        <tr>
          <th scope="col">Année</th>
          <th scope="col">Chien</th>
          <th scope="col">Niveau</th>
          <th scope="col">Pays</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td data-libelle="Année">2021</td>
          <td data-libelle="Chien"><a href="https://…/la-meute/upper-side/">Upper’Side du Mont Brabant</a></td>
          <td data-libelle="Niveau">Brevet</td>
          <td data-libelle="Pays" class="mtb-tableau__cellule--vide"></td>
        </tr>
      </tbody>
    </table>
  </section>
  <!-- une <section> par groupe, dans l'ordre rendu par la fonction de lecture -->
</div>
```

### 4.2 Mode `chien-courant`

```html
<div class="mtb-tableau-resultats wp-block-mtb-tableau-resultats">
  <table class="mtb-tableau">
    <caption>Palmarès de travail</caption>
    <thead><tr><th scope="col">Année</th><th scope="col">Niveau</th><th scope="col">Discipline</th></tr></thead>
    <tbody>
      <tr>
        <td data-libelle="Année">2019</td>
        <td data-libelle="Niveau">Brevet</td>
        <td data-libelle="Discipline">RING</td>
      </tr>
    </tbody>
  </table>
</div>
```

**Ni `<section>`, ni `<h2>`** : `MASTER.md` §7.5 confie le `h2` « Palmarès » au gabarit de la fiche.

### 4.3 Les crochets — liste close

**Principe d'arbitrage appliqué, et il est opposable** : *le serveur n'émet un crochet que si le CSS
le vise, ou s'il porte une donnée que le balisage ne dit pas déjà.* Tout le reste a été retiré des
deux plans. « Chaque crochet non stylé est une promesse que quelqu'un croira un jour. »

| Crochet | Porté par | Statut | Pourquoi il existe |
|---|---|---|---|
| `class="mtb-tableau-resultats"` | racine | **obligatoire** | Le thème l'affecte au canal large |
| `class="mtb-tableau"` | **chaque `<table>`** | **obligatoire — classe unique et nue** | La primitive de §7.6. **Aucune seconde classe sur le `<table>`** |
| `data-libelle="…"` | **chaque `<td>`** | **obligatoire — décision 10** | `content: attr(data-libelle)` du repli. Valeur = **exactement** `colonnes[].libelle`, à l'octet près |
| `class="mtb-tableau__cellule--vide"` | `<td>` vide | **obligatoire** | Doublure de `:empty`, voir §5 |
| `data-discipline="…"` | `<section>` | **obligatoire** | Porte la **clé stockée**, qui n'apparaît nulle part ailleurs dans le balisage (seul le libellé est imprimé). Précédent `data-statut` de `grille-chiens` |
| `<section>` sans classe | par groupe | **obligatoire** | Structure ; le CSS n'écrit rien dessus, délibérément |
| `<caption>` sans classe | premier enfant de chaque `<table>` | **obligatoire** | Nom accessible du tableau ; le CSS vise `.mtb-tableau > caption` |
| `<h2>` nu | par groupe, mode discipline seul | **obligatoire** | `base.css:201-216` l'habille déjà entièrement |
| `<thead>` avec `<th scope="col">` | chaque `<table>` | **obligatoire** | Jamais des `<td>`, **jamais** de `role` ni d'`aria-hidden` |
| `<tbody>` explicite | chaque `<table>` | **obligatoire** | Le CSS vise `tbody > tr` : filet et survol sans toucher la ligne d'en-tête |
| `<a href>` | cellule Chien, **ssi `url` non vide** | **obligatoire** | Sinon le nom en clair, **sans classe ni attribut distinct** |

**Retirés des deux plans, et interdits d'émission** — chacun était proposé par un plan et refusé par
l'autre, aucun n'est visé par une règle CSS : `class` sur le `<h2>`, sur le `<caption>`, sur le
`<tr>`, sur le `<a>` ; **seconde classe sur le `<table>`** (`…__tableau`) ; classe sur la `<section>`
(`…__groupe`) ; classe modificatrice de mode (`…--palmares`) ; **`data-colonne` sur `<th>` et
`<td>`** ; toute classe par colonne, tout `role`/`aria-*` sur les éléments de tableau ; toute classe
d'alignement du cœur (`alignwide`, `alignfull`) ; tout `style=` en ligne.

*Coût de réversibilité, mesuré : `data-colonne` économise ~2,4 Ko sur la page Travail (120 cellules).
Le réintroduire coûterait **une ligne** le jour où une règle en aurait besoin ; `:nth-child` ne peut
pas le remplacer, les colonnes étant conditionnelles.*

### 4.4 Les deux clauses de balisage bloquantes

Elles ne se voient ni à `php -l`, ni à la revue de code, ni au-dessus de 48 rem. Elles se vérifient
sur une page rendue, sous 48 rem, et nulle part ailleurs.

> **C-4 — la cellule vide est strictement vide.**
> Quand `'' === $cellule['affichage']`, le `<td>` est émis **sans un seul caractère entre la balise
> ouvrante et la balise fermante** : ni espace, ni retour à la ligne, ni tabulation, ni indentation,
> ni commentaire HTML. `:empty` échoue sur un simple `\n`.
> **Comment on le garantit, mécaniquement et non par discipline** : aucune chaîne de format
> `printf`/`sprintf` du module ne contient `\n`, `\t`, `PHP_EOL` ni indentation ; chaque format tient
> sur une seule ligne source ; le HTML est assemblé par concaténation. La cellule vide est une
> **branche dédiée**, pas le chemin général avec un contenu vide.
> **Le `<td>` porte en outre `class="mtb-tableau__cellule--vide"`.** La classe ne dispense pas de la
> vacuité stricte : elle la double, parce que `:empty` est le seul mécanisme du projet dont la
> correction dépende d'un octet d'espacement dans un `sprintf`.

> **C-5 — un `<td>` non vide contient exactement un nœud enfant.**
> Soit un unique passage de texte, soit un unique élément (`<a>` ou `<span>`), lequel peut contenir
> ce qu'il veut. **Deux enfants de premier niveau produisent deux éléments de grille** : dans la
> grille à deux colonnes du repli, le troisième élément se place à la ligne suivante **sous
> l'étiquette**, colonne 1 — une valeur alignée dans la colonne des libellés, à chaque ligne.
> Le cas réel est identifié : si le sexe du chien est un jour imprimé (§9), **le nom et le sexe sont
> enveloppés ensemble dans un unique `<span>`**, le `<a>` restant à l'intérieur — jamais
> `<a>Nom</a> <span>Mâle</span>` en frères directs du `<td>`.

### 4.5 La boucle, et sa vérification par recherche

Le rendu s'écrit exclusivement ainsi :

```
pour chaque $colonne de $groupe['colonnes'] :
    $cle     = $colonne['cle']
    $libelle = $colonne['libelle']
    $cellule = $ligne['cellules'][ $cle ]      // sous isset() + is_array()
```

**Les chaînes littérales `'chien'`, `'conducteur'`, `'pays'`, `'annee'`, `'niveau'` n'apparaissent
nulle part dans le rendu.** Un `grep` qui les y trouve signale que quelqu'un a réimplémenté une
décision de #5. La seule chaîne de domaine du module est `'mtb_chien'`, dans `donnees.php`, pour le
garde-fou de type.

> **Amendement du 2026-08-20 — `'discipline'` est retiré de cette liste, ma rédaction était
> impossible à satisfaire.** `data-discipline` est **obligatoire** au §4.3, et sa valeur ne peut venir
> que de `$groupe['discipline']` : la chaîne est donc structurellement nécessaire. Elle subsiste en
> **une seule ligne du chemin de rendu**, `balisage.php:135`, commentée sur place — **elle nomme le
> groupe, jamais une colonne**. La recette du §11.9 porte donc sur les cinq autres chaînes, qui
> doivent rendre zéro, et sur cette unique ligne, qui doit rester unique. Les cinq sont mesurées à
> zéro.
> *Précision du 2026-08-20, ma formulation était plus étroite que ce que le module peut satisfaire :
> `'discipline'` apparaît aussi dans `donnees.php` et `bootstrap.php`, où elle nomme **l'attribut du
> bloc** et non une colonne. La recette porte sur le **rendu**, pas sur le module entier.*

Conséquence directe : le bloc **ne sait pas**, et n'a pas à savoir, que le mode `chien-courant`
affiche une colonne Discipline et pas la colonne Chien. `colonnes` le lui dit.

Le libellé du `<th>` et celui du `data-libelle` viennent de **la même variable, dans la même
boucle** : il est structurellement impossible qu'ils divergent.

---

## 5. Ce que le thème habille — et où passe la coupure

**Fichier unique : `wp-content/themes/mtb/assets/css/blocs/mtb-tableau-resultats.css`.** Servi par la
boucle générique de `functions.php:195-250`, poignée `mtb-bloc-mtb-tableau-resultats`, dépendance
`mtb-jetons`. Rien à déclarer nulle part.

La feuille est coupée en deux sections, et **la coupure est une clause de ce contrat** :

```
§1  LA PRIMITIVE .mtb-tableau — bloc hissable tel quel dans base.css
    1.1  .mtb-tableau                          inline-size: 100%  (table-layout reste auto)
    1.2  .mtb-tableau > caption                masquage accessible
    1.3  .mtb-tableau tbody > tr               border-block-end: var(--bord-fin)
    1.4  .mtb-tableau tbody > tr:hover         background-color: var(--calcaire-creux)
    1.5  @media screen and (max-width: 47.999rem)     le repli de §7.6
         thead · tr · td · td::before · la cellule vide

§2  LE BLOC .mtb-tableau-resultats — ce qui reste ici après le hissage
    2.1  .mtb-canal > .mtb-tableau-resultats   affectation au canal large
```

> **Règle de coupure franche.** Aucun sélecteur du §1 ne nomme `mtb-tableau-resultats`, aucun
> sélecteur du §2 ne nomme `mtb-tableau`. **`.mtb-tableau-resultats .mtb-tableau { … }` est interdit
> dans cette feuille.** Le jour du hissage, on coupe entre §1 et §2, on colle le §1 à la place
> réservée de `base.css:331-335`, et **il n'y a pas un arbitrage à rendre**. C'est le test du contrat
> #12 §10.4a : le §1 décrit *ce qu'**est** un tableau*, le §2 *la place dont il dispose **ici***.

> **Amendement du 2026-08-20 — le hissage a eu lieu pendant le lot, et la coupure a servi.**
> J'ai demandé le hissage à l'orchestrateur avant de geler ce contrat ; **#16 l'a fait** et a écrit la
> primitive dans `base.css` §10 (l. 964-1115), y compris la requête média de §7.6 et la règle de
> cellule vide visant les deux crochets. `base.css` étant servie sur **toutes** les pages, **T-#15-a
> est payée** : le repli de §7.6 ne dépend plus de la présence du bloc. `base.css:917-923` demande en
> retour la suppression du §1 de la feuille de bloc, devenu redondant.
>
> **Second amendement du 2026-08-20 — le §1 a été supprimé. Il n'y a plus qu'une section.**
>
> J'avais d'abord décidé de garder le §1 pour le commit de cette chaîne et de séquencer sa suppression
> après le commit de `base.css` par #16. Raison : mono-branche, arbre partagé, et le `base.css` de #16
> **non commité** — si le §1 partait et que `base.css` n'atterrissait pas, le tableau se rendait sans
> repli mobile, échec AA **silencieux**.
>
> **L'orchestrateur a tranché dans l'autre sens, et il a repris le couplage à son niveau** :
> *« garde uniquement ce qui est propre à ton bloc — pas une ligne de la primitive, pas de copie de
> sécurité. Une duplication temporaire est une duplication permanente. »* La raison est meilleure que
> la mienne sur le fond : c'est le motif de T18, déjà matérialisé à **cinq exemplaires** dans ce
> projet, et la divergence avait **déjà commencé** (10,78 contre 10,79). J'ai remonté l'état exact —
> la primitive est dans `base.css` **dans l'arbre de travail mais dans aucun commit** — et
> l'orchestrateur vérifie le couplage au test d'intégration de lot. **Exécuté.**
>
> **Trois dettes payées d'un seul geste** : T-#15-a (primitive dans une feuille conditionnelle),
> T-#15-c (le doublage `.mtb-tableau.mtb-tableau td`, **recopié nulle part**), T-#15-e (recette de
> masquage écrite deux fois). **La règle de coupure franche n'a plus d'objet** et a été retirée.
>
> **Vérifié après suppression, pas déduit** : `base.css` §10 couvre le §1 supprimé **déclaration par
> déclaration** (comparaison automatique, commentaires retirés — une seule différence, le doublage,
> qui est précisément T-#15-c). Sur les quatre pages des deux chemins de rendu, à 360 et 1440 px,
> **zéro différence de valeur calculée** avant/après. Dans la toile de l'éditeur réduite, le
> rembourrage reste celui du repli **sans** la classe doublée : la toile est une iframe où le cœur ne
> préfixe pas, donc `td` y pèse (0,0,1) contre (0,1,1) pour `.mtb-tableau td` — l'écart de spécificité
> suffit, le doublage n'avait de raison d'être que tant que les deux règles vivaient dans deux
> feuilles.
>
> **Ce qui reste dans la feuille** : une règle, quatre déclarations — l'affectation au canal large.
> 37 070 o → **10 158 o**. Effet de bord mesuré : sous le seuil de 20 000 o, le cœur **incorpore** la
> feuille par `wp_maybe_inline_styles()` — plus de `<link>` sur le site public, un `<style
> id="mtb-bloc-mtb-tableau-resultats-inline-css">` à la place. **Tout contrôle qui cherchait la
> poignée dans un `<link>` doit désormais chercher le `<style>`.**

Points gelés à l'intérieur du §1 :

| Point | Décision |
|---|---|
| `thead` sous 48 rem | `position: absolute` · `inline-size: 1px` · `block-size: 1px` · `overflow: hidden` · `clip-path: inset(50%)` · `white-space: nowrap`. **Retiré à l'œil, conservé au lecteur d'écran.** Les deux dernières déclarations de §7.6 sont reprises verbatim ; les quatre autres sont ajoutées parce que la boîte, elle, existe toujours et capterait le pointeur |
| **Interdit formel** | Ni `display: none`, ni `visibility: hidden`, ni `aria-hidden` sur ce `<thead>`. Les trois le retirent de l'arbre d'accessibilité, ne laissant que du **contenu généré** pour nommer les colonnes — ni copiable, ni exposé au mode lecture, ni restitué par tous les couples navigateur/lecteur. **La raison est écrite en majuscules dans la feuille**, parce que quelqu'un « corrigera » ça un jour |
| Cellule vide sous 48 rem | **`display: none`**, et non `content: none`. `content: none` laisse la cellule dans la grille : `display: grid` + `padding: var(--e-1) 0` = 8 px de vide mort **par ligne**, ~200 px de blanc inexpliqué sur un tableau de 25 lignes. Le sélecteur vise **les deux crochets** : `td:empty` **et** `td.mtb-tableau__cellule--vide` |
| **Contrainte de portée, la panne la plus grave que la feuille puisse produire** | La règle de cellule vide vit **à l'intérieur** de la requête média. En mode tableau, `display: none` sur une cellule **décalerait toute la ligne d'une colonne**. Écrit en toutes lettres au-dessus de la règle |
| `td` sous 48 rem | `display: grid` · `grid-template-columns: 8rem 1fr` · `gap: var(--e-3)` · `padding: var(--e-1) 0`, verbatim de §7.6. Sélecteur écrit **`.mtb-tableau.mtb-tableau td`** — classe doublée : dans la toile de l'éditeur, le cœur préfixe `base.css` par `.editor-styles-wrapper`, si bien que son `td` y pèse (0,1,1), exactement le poids de `.mtb-tableau td` ; à égalité, l'ordre de source tranche, et il n'est garanti nulle part. **Seule règle doublée de la feuille** |
| `td::before` | `content: attr(data-libelle)` et les quatre déclarations de §7.6, verbatim |
| `tr` | `display: block` et `padding-block` sous 48 rem ; **`border-block-end` sorti de la requête média** et posé sur `tbody > tr` : un filet de séparation (§5.3) n'a aucune raison de n'exister que sur téléphone, `border-collapse: collapse` le peint correctement en mode tableau, et la portée `tbody >` évite un double trait sous la ligne d'en-tête qui porte déjà `--bord-fort` |
| Impression | **Aucune `@media print`, et c'est la bonne réponse.** Tout le repli est enfermé dans `@media screen`, media qui exclut `print` : en impression le tableau reste un tableau, en-têtes visibles, `::before` non générés, aucune cellule retirée. **Zéro règle à écrire.** Une feuille d'impression est globale ou n'est pas ; en écrire une ici produirait à terme neuf feuilles d'impression divergentes |
| `table-layout` | **reste `auto`.** Le nombre de colonnes varie d'un tableau à l'autre sur la même page ; `fixed` figerait des colonnes égales et écraserait la colonne Chien |

Points gelés à l'intérieur du §2 :

| Point | Décision |
|---|---|
| Canal | **Canal large pour tout le bloc, titres compris.** `grid-column: large-debut / large-fin` · `inline-size: 100%` · `max-inline-size: var(--l-large)` · `margin-inline: auto` — les quatre vont ensemble, forme reprise de `mtb-grille-chiens.css`. §7.1 se contredit sur ce composant (titres en canal texte, tableaux de résultats en canal large) ; §7.6 lie le `h2` à son tableau comme une **unité**, et un titre commençant 256 px à droite de son propre tableau serait pire. **Question ouverte Q-front-1**, réversible en quatre lignes isolées au §2 |
| Marges | **La racine du bloc n'a aucune marge verticale**, et **n'est jamais `display: grid` ni `display: flex`**. C'est la cause mécanique de T23 : `.mtb-canal` est une grille, les marges d'éléments de grille ne fusionnent jamais. En laissant la racine en flux normal, les marges des `h2` fusionnent normalement **à l'intérieur** du composant. Écrit comme un **interdit** dans la feuille : un futur besoin d'espacement pousserait naturellement vers `display: grid; gap:`, qui ferait rentrer T23 dans le composant par la fenêtre |
| `h2` | **Zéro règle.** `base.css:201-216` livre Newsreader 500, `--t-xl`, filet double de 6 rem, `margin-block: var(--e-7) var(--e-4)` — 48 px au-dessus, 16 px en dessous, exactement le rapport de §5.1. Les neuf paires se lisent comme neuf blocs sans qu'une valeur soit choisie ici |
| Lien de cellule | **Zéro règle.** `base.css:248-260` (§8.2) et l'anneau de focus unique de `base.css:465-470` (§8.1) suffisent. **Aucun style de « lien mort »** : un nom sans fiche se lit exactement comme un nom avec fiche, moins le soulignement |
| État vide | **Zéro règle.** `.mtb-etat-vide*` est déjà dans `editor.css`, hors empreinte. En figer une copie ici garantirait une divergence de plus (T13) |
| Interdits | Aucun conteneur à défilement horizontal · aucune largeur ni `min-inline-size` de colonne · aucune règle d'alignement par colonne · aucun filet double dans une cellule (§2.1 : liste d'emplacements close) · aucune bordure de tableau, aucun encadrement, aucun fond de cellule au repos · aucun rayon · aucune transition ni animation (aucun jeton de durée n'existe) · aucun `url()`, `@import`, image, police d'icônes · **zéro octet de JavaScript** |

**Mesures faites au plan, à confirmer à l'écran** — elles sont **calculées, pas lues sur des pixels
rendus** (décision 36) :

- **360 px** : `--marge-page` = 18 px → canal 324 px ; étiquette 128 px + `gap` 12 px → **184 px** à
  la valeur. « Upper'Side du Mont Brabant » passe sur deux lignes ; le plus long mot insécable fait
  ~78 px. **Aucun défilement horizontal.** `8rem` est la plus courte piste qui tienne « CONDUCTEUR »
  (~102 px) sur une ligne.
- **Zoom 200 %** : `--bp-tableau` = 48 rem (`tokens.css:165`) ; 1280 px à 200 % = 640 px CSS
  < 767,98 px → **le repli s'active**. Une requête média en `rem` se résout sur la racine initiale,
  donc la valeur est stable.
- **Contrastes** : `--pin`/`--calcaire` 14,23:1 · `--texte`/`--calcaire` 12,03:1 ·
  `--laiton-texte`/`--calcaire` 5,30:1 · `--texte`/`--calcaire-creux` 10,78:1 ·
  `--sauge-fonce`/`--calcaire-creux` 6,92:1 — toutes tabulées au §12. **Deux paires manquent au
  §12.3** et sont calculées ici : `--pin`/`--calcaire-creux` ≈ **12,7:1** et
  `--laiton-texte`/`--calcaire-creux` ≈ **4,74:1** (AA, marge 0,24). Voir Q-front-5 en §10.
- **Poids** : ~11 000 o bruts (~1 200 o de CSS effectif, le reste en commentaire de la maison),
  ~2 600 o transférés. Page Travail complète estimée ~130 000 o pour un budget de 200 000.

---

## 6. États spéciaux

| État | Émis par le serveur | Rendu par le thème |
|---|---|---|
| `aucun_resultat` | **`''`**, littéralement. Pas d'enveloppe, pas de section, pas de commentaire HTML. Côté public : **ni titre ni tableau** (§9.3, décision 26) | **Rien.** Aucune règle de la feuille ne suppose la présence du bloc sur une page |
| `discipline_orpheline` | Un groupe normal : `h2` = la **valeur brute** imprimée telle quelle, `data-discipline` = la clé brute. Le bloc **ne lit jamais** `orpheline` pour décider quoi que ce soit — c'est un renseignement, pas une branche | Comme les autres. **Ne jamais styler `data-discipline` par valeur** |
| `discipline_jamais_renseignee` | Cas 3 bis de #5 : `discipline_libelle` vaut **« Non renseigné »**, donc **jamais un `h2` vide** | Comme les autres |
| `donnee_absente` | `affichage` vaut **« Non renseigné »**, imprimé tel quel. Cellule **non vide**, son étiquette s'imprime sous 48 rem | Imprime. **N'invente jamais un tiret**, ni « Aucun », ni « Néant » (§9.3, D11). Aucun grisé, aucune italique |
| `cellule_vide` — **Pays seul en pratique** | Cellule **strictement vide** (C-4) **et** portant `mtb-tableau__cellule--vide` | Retirée sous 48 rem (`display: none`). **N'écrit jamais « France »**, jamais « Non renseigné », jamais un tiret |
| `chien_sans_fiche` (`parent_hors_elevage` chez #5) | Le nom, **sans `<a>`**, sans classe ni attribut distinct. `etat` n'est jamais lu | Le nom, **sans changement de forme** : ni grisé, ni italique, ni mention |
| `fiche_liee_non_consultable` (brouillon, protégée, planifiée) | Identique au précédent : `url` vide ⇒ pas de lien | Identique. **Voulu** : distinguer signalerait au visiteur l'existence d'un contenu réservé |
| `page_protegee` | **Non émis, délibérément.** `mtb_get_resultats_travail_*` filtre déjà `post_status => 'publish'` et `has_password => false` : aucune fuite | Rien de particulier |
| `contexte_absent` (mode `chien-courant` hors fiche chien) | **`''`** | Rien |
| `colonne_absente_de_cellules` | Ne doit pas arriver (#5 §5.3). Le rendu ne tombe pas pour autant : `isset()` + `is_array()`, sinon **cellule vide** — l'alignement des colonnes est préservé, l'étiquette repliée supprimée par la même parade. **Aucune invention** | Comme une cellule vide |
| `fonction_de_lecture_absente` | `function_exists()` sur les trois. Rend `''` ; le réglage ne propose que « Toutes les disciplines », **aucune discipline inventée** | Rien |

### L'état vide côté éditeur

Il vit dans **`editeur.js`**, pas dans le PHP — gain net : `render.php` et les deux fonctions de rendu
n'ont **que deux sorties**, le balisage ou `''`. Pas de `contexte_editeur()`, pas de branche
`REST_REQUEST`, pas de `current_user_can()` au rendu. Raccordement par `EmptyResponsePlaceholder` sur
`wp.serverSideRender`, précédent `coordonnees-plan/editeur.js`.

**Forme de référence, celle du lot 4** (`bandeau-alerte/`, `encart-appel/`, `coordonnees-plan/`) —
c'est la dette **T13**, et elle se paie ici :

```
div.mtb-etat-vide
  ├── span.mtb-etat-vide__nom      → « Tableau de résultats »   (casse naturelle)
  └── p.mtb-etat-vide__phrase      → la phrase
```

**Exactement deux enfants, jamais un troisième, AUCUNE classe modificatrice.** Le nom est porté par
un `span` et non un `p` (`editor.css` lui pose `display: block` sans remettre les marges à zéro), en
**casse naturelle** — les capitales sont posées par `text-transform`, sans quoi un lecteur d'écran
épellerait le mot. **Ne pas reprendre la forme de `galerie-photos`**, qui met un bouton que §9.1 ne
prévoit pas.

Trois phrases, à la forme canonique de §9.1 (« Ce bloc n'affiche rien tant que <ce qui manque>. ») :

| Mode | Phrase exacte |
|---|---|
| Toutes les disciplines | **« Ce bloc n'affiche rien tant qu'aucun résultat de travail n'est publié. »** |
| Une discipline | **« Ce bloc n'affiche rien tant qu'aucun résultat de travail n'est publié dans la discipline « RING ». »** — la discipline est **nommée et citée entre guillemets**, donc aucun accord ni préposition à composer. Précédent livré : `grille-chiens/balisage.php:350` |
| `chien-courant` | **« Ce bloc n'affiche rien tant que la fiche de chien affichée n'a aucun résultat de travail. »** — cas réel : une instance ouverte dans l'éditeur de site n'a aucun contexte `postId` en aperçu REST et déclencherait sinon une phrase fausse |

**Les onze phrases sont composées en PHP**, dans `donnees.php`, et transmises **finies** à `editeur.js`
par `wp_localize_script`. `editeur.js` **choisit** une phrase par index ; il n'en **compose** aucune.
C'est la règle que le serveur impose au thème, appliquée à notre propre JavaScript.

---

## 7. Chaînes fournies par le serveur

Le thème et le JavaScript de l'éditeur les **impriment**. Ils n'en composent, n'en accordent, n'en
traduisent, n'en abrègent et n'en reformatent aucune.

- **Libellés de discipline** : `RING` · `IGP / RCI` · `Mondioring` · `Obéissance` · `Pistage` ·
  `Recherche utilitaire` · `Sauvetage` · `Truffe` · `Autres disciplines` — dans le `h2` **et** dans
  le `<caption>`.
- **Libellés de colonne** : `Année` · `Chien` · `Niveau` · `Discipline` · `Conducteur` · `Pays` —
  dans le `<th>` **et**, à l'octet près, dans `data-libelle`.
- **Libellé d'absence** : `Non renseigné`, déjà dans `affichage`.
- **Légende du palmarès** : **`Palmarès de travail`**, recopié verbatim de `MASTER.md` §7.5:647.
- **L'année est déjà une chaîne décimale** (`'2021'`). **Interdiction formelle** d'appeler
  `number_format_i18n()`, `number_format()` ou `date_i18n()` dessus — `2 021` serait produit. Il n'y a
  **aucune date** dans ce modèle, donc aucun formatage de date nulle part.
- **Chaînes de l'éditeur**, transmises finies : `Discipline à afficher` · `Toutes les disciplines` ·
  l'aide du réglage · `Tableau de résultats` · les onze phrases d'état vide.

---

## 8. Interdits — vérifiables par recherche

1. Le thème n'interroge **jamais** la base : `WP_Query`, `get_posts`, `get_post_meta`, `get_terms`,
   `$wpdb`, `MTB\` dans `themes/mtb/` = infraction (contrat #1 §8, amendé par la décision 41 pour les
   seules API de navigation, qui ne concernent pas ce composant).
2. Le thème ne **compose** aucun libellé de discipline, de colonne, de sexe ou d'absence.
3. Le thème ne **trie ni ne regroupe** — **et le bloc non plus** : aucun `usort`, aucun `ksort`,
   aucune réorganisation dans `includes/blocks/tableau-resultats/**`. La ligne « tri par année » de
   la checklist de l'issue est **un interdit déguisé** : #5 §8 dit que le consommateur passe `ordre`
   et n'y touche plus.
4. **`ordre` n'est exposé nulle part** — ni en attribut de bloc, ni en paramètre des deux fonctions,
   ni dans un réglage. L'ordre juste est connu et **diffère selon le mode** ; un réglage ne pourrait
   que dégrader l'un des deux, sur une question à laquelle l'éleveuse n'a aucun critère pour
   répondre.
5. Le thème ne décide pas si un nom est cliquable — **`url` le dit, et lui seul**.
6. Personne ne réinterprète, ne normalise ni ne traduit un niveau, un pays ou un nom de chien.
   Personne n'invente de hiérarchie de niveaux.
7. **Les chaînes `'discipline'`, `'chien'`, `'conducteur'`, `'pays'`, `'annee'`, `'niveau'`
   n'apparaissent pas dans le rendu du bloc.**
8. Aucune règle visuelle dans l'extension : aucun `style=`, aucune `<style>`, aucune couleur, aucune
   dimension.
9. Le module ne redéclare **aucune** fonction `mtb_get_*` et ne lit **jamais** `mtb_resultat`
   lui-même.
10. **Aucun conteneur à défilement horizontal** autour du tableau — ni du serveur, ni du thème.
11. Le module n'édite ni `mtb-core.php`, ni `class-loader.php`, n'appelle pas
    `flush_rewrite_rules()`, n'utilise pas `init` 99, et **ne déclare aucune fonction dans
    `render.php`** ni dans un fichier inclus par lui (deux instances sur une page →
    `Cannot redeclare`, que le `try/catch` du chargeur ne rattrape pas).
12. **Décision 27** : `bootstrap.php` ne fait `require_once` que sur des fichiers écrits dans le même
    commit. Un `require` vers un fichier absent met **tout le site** en erreur fatale, `wp-admin`
    compris.

**Performance, lue et non supposée** : `query/resultat/interne.php:45-48` mémorise
`toutes_les_lignes()` en `static` **sans clé et sans `$args`**. Neuf blocs réglés sur neuf
disciplines coûtent donc **une** lecture, pas neuf ; seuls le regroupement et le tri se refont, en
PHP pur sur ~30 lignes. La cible de quatre requêtes de #5 §5.5 tient quel que soit le nombre
d'instances. Le bloc lui-même **n'ajoute aucune requête** — les URL viennent de `cellules.chien.url`.
Seule exception : le `get_post_type()` du garde-fou, sur un contenu déjà en cache.

**Conventions** : `declare(strict_types=1)` · garde `ABSPATH` (dans le **premier** bloc
`namespace { }` — PHP interdit tout code hors des accolades dès qu'il y en a deux) · namespace
`MTB\Core\Blocks\TableauResultats` · préfixe `mtb_` sur les deux globales · `array()` et non `[]` ·
conditions de Yoda · pas de balise fermante · **aucun accès à la base à l'inclusion** ·
`sanitize_key()` sur `discipline`, comparaison stricte sur `source`, `absint()` sur l'identifiant —
**les attributs sont recastés sans faire confiance au schéma de `block.json`**, `do_blocks()`
tournant aussi sur du contenu importé · échappement systématique en sortie (`esc_html`, `esc_attr`
sur `data-libelle`, `data-discipline` et toute classe, `esc_url` sur `href`) · **ne pas entourer
`get_block_wrapper_attributes()` d'`esc_attr()`**, il échappe lui-même · aucune écriture, donc aucun
nonce · français littéral, aucune fonction i18n · GPL v2+.

En rendu de bloc : `get_block_wrapper_attributes( array( 'class' => 'mtb-tableau-resultats' ) )`.
En appel hors bloc : une fonction interne équivalente, reprise de `grille-chiens/balisage.php` —
`get_block_wrapper_attributes()` **émet un avertissement PHP** hors du rendu d'une instance.

---

## 9. Ce qui n'est délibérément pas livré

**Le sexe du chien n'est pas rendu.** #5 émet `sexe` et `sexe_libelle` sur la cellule Chien, et le
site source imprime ♂/♀ devant chaque nom. Mais **`MASTER.md` §7.6 ne prévoit aucune place pour eux**
et la question Q-e de `docs/contracts/issue-5.md` §13 est explicitement assignée à `lead-design-mtb`,
qui ne tourne pas sur ce lot. Le rendre exigerait d'**inventer un placement**, ce que la décision 39
interdit à une chaîne. **Aucune donnée n'est perdue** : le sexe vit sur la fiche du chien, et le
tableau du site source ne le porte pas dans ses colonnes. **Dette T-#15-b**, et clause C-5 écrite
d'avance pour que son ajout futur ne casse pas le repli.

**Aucun filtre visiteur, aucun tri cliquable, aucune pagination, aucun export.** §7.6 décrit un
tableau statique ; le site entier compte ~30 lignes de résultats.

**Aucun contenu de démonstration**, aucune recopie de mtbrabant.com — périmètre de #17 sur ce lot.

---

## 10. Arbitrages — chaque désaccord entre les deux plans, la décision, sa raison

| # | Désaccord | Décision | Raison |
|---|---|---|---|
| 1 | **Parade de la cellule vide** : back veut la vacuité stricte **plus** une classe ; front veut `:empty` seul, et accepte la classe en repli | **Les deux crochets, le CSS vise leur union** | `:empty` est le **seul mécanisme du projet dont la correction dépende d'un octet d'espacement dans un `sprintf`** — invisible à `php -l`, à la revue, et à toute vérification au-dessus de 48 rem. Un dev qui reformate un format pour la lisibilité casserait le repli de la page Travail sans que rien ne le signale. Coût : ~40 o par cellule vide, sur **deux** lignes du contenu repris (le Pays ne figure que deux fois au site source) |
| 2 | **Nom de la classe de cellule vide** : back dit `mtb-tableau-resultats__cellule--vide`, front dit `mtb-tableau__cellule--vide` | **`mtb-tableau__cellule--vide`** — front | La règle vit dans le **§1 hissable**. Une classe `mtb-tableau-resultats__*` y violerait la règle de coupure franche et rendrait le hissage arbitrable au lieu d'être une coupure |
| 3 | **`content: none` ou `display: none`** sur la cellule vide | **`display: none`** — front | Mesuré : `content: none` laisse la cellule dans la grille, `display: grid` + `padding: var(--e-1) 0` = 8 px de vide mort **par ligne**, ~200 px de blanc inexpliqué sur un tableau de 25 lignes. Une déclaration au lieu d'une, un défaut de moins |
| 4 | **Classes sur `caption`, `h2`, `tr`, `a`, `table`, `section`, et `data-colonne`** : back les émet, front les refuse et n'en style aucune | **Toutes retirées**, sauf `.mtb-tableau-resultats` (racine) et `.mtb-tableau` (table) | Principe posé et opposable : *le serveur n'émet un crochet que si le CSS le vise, ou s'il porte une donnée que le balisage ne dit pas déjà.* « Chaque crochet non stylé est une promesse que quelqu'un croira un jour. » `data-discipline` **survit** au test — il porte la clé stockée, qui n'est imprimée nulle part ailleurs. `data-colonne` échoue : `data-libelle` identifie déjà la colonne, et il pèse ~2,4 Ko sur la page Travail. Réversible en une ligne |
| 5 | **Classe modificatrice `--palmares`** : back l'émet, front n'en veut pas | **Retirée** | L'absence de `<section>` et de `<h2>` dit déjà le mode. Si #16 en a besoin, c'est un amendement de contrat d'une ligne, pas une supposition |
| 6 | **Légende du mode `chien-courant`** : back propose « Palmarès de travail » et craint une collision avec le `h2` de #16 | **« Palmarès de travail »**, sourcé §7.5:647 — **et #16 garde « Palmarès »** pour son `h2`, sourcé §9.3:814 | Deux chaînes sourcées pour **deux objets distincts** : §7.5 nomme le tableau, §9.3 nomme la section. Les forcer identiques inventerait une règle que `MASTER.md` §10 ne porte pas. Redondance à l'oreille : cosmétique, pas un échec AA — et le mode discipline la porte déjà (`h2` RING + `caption` RING) |
| 7 | **Phrases d'état vide** : mes deux formulations contre les trois du plan back | **Les trois du plan back** | « résultat » → « **résultat de travail** », libellé de §10.2 : le raccourci fait dériver le vocabulaire d'un écran que l'éleveuse lit. « cette discipline » → **la discipline nommée entre guillemets** : « cette » oblige Fabienne à retrouver un panneau qu'elle a peut-être refermé, et rend la phrase identique pour les neuf. Et il **manquait un cas** : une instance `chien-courant` ouverte dans l'éditeur de site aurait affiché une phrase fausse |
| 8 | **Canal des `h2`** : §7.1 range les titres en canal texte et les tableaux de résultats en canal large ; le composant contient les deux | **Canal large pour tout le bloc** | §7.6 lie le `h2` à son tableau comme une **unité**. L'écart mesuré est réel — à 1440 px les `h2` commencent 256 px à gauche de la prose de la page Travail, famille de défaut de la décision 33 — mais un titre commençant 256 px à **droite** de son propre tableau serait pire. **Q-front-1**, réversible en quatre lignes isolées au §2 |
| 9 | **`enum` sur `discipline`** dans `block.json` | **Pas d'`enum`** | Le cœur ferait retomber toute valeur hors liste sur le défaut `""` = **toutes** : une instance réglée sur une discipline devenue orpheline basculerait **silencieusement** en neuf tableaux. Inverse exact de la garantie de #5, même raisonnement que son arbitrage 9 |
| 10 | **Clause C-5** (un seul nœud enfant par `<td>`) : découverte par front, absente du plan back | **Gelée** | Deux enfants directs = deux éléments de grille ; le troisième se place **sous l'étiquette**, colonne 1, à chaque ligne concernée — et **rien ne le signale au-dessus de 768 px**. Le cas est identifié, pas théorique : #5 §9 arbitrage 6 prévoit d'imprimer le sexe à côté du nom |
| 11 | **Où vit `.mtb-tableau`** : `base.css` (la place lui est réservée) ou la feuille de bloc | **La feuille de bloc**, dette T-#15-a nommée | `base.css` appartient exclusivement à #16 sur ce lot. Lui imposer treize déclarations en cours de route reproduirait l'échec de la décision 31 (« mes arbitrages arrivaient après que les chaînes avaient écrit leur code »). Le hissage a été **demandé à l'orchestrateur le 2026-08-20**, avant le gel |

---

## 11. Vérifications exigées avant de rendre la main

Aucune ne s'affirme sans avoir été faite. Une vérification non faite se **dit**, elle ne se déduit
pas. Rappel de la décision 29 : `WP_DEBUG` vaut `true` sur les requêtes web et `false` en WP-CLI —
une affirmation « aucune notice PHP » n'a de sens que **mesurée sur une page rendue**.

1. **Le contexte `postId` — risque numéro un de l'approche.** Le mécanisme est décrit
   (`render_block()` construit `$context` depuis le global `$post`, `WP_Block` n'en retient que les
   clés de `usesContext` et le transmet aux blocs internes) et **corroboré** par deux faits du dépôt :
   `bandeau-ouverture` lit `$block->context['postId']` en production depuis le lot 3, et
   `templates/singular.html:4` rend `core/post-title` sur les fiches. **Ce n'est pas une preuve
   d'exécution.** Le mesurer, ou dire qu'on ne l'a pas mesuré.
2. **Les onze états limites du §6**, un par un, sur page rendue.
3. **La cellule vide** : sous 48 rem,
   `document.querySelectorAll('.mtb-tableau td:empty').length` doit égaler le nombre de cellules Pays
   vides ; `grep -c ' </td>'` doit rendre **zéro**.
4. **360 px** : `scrollWidth === clientWidth` sur une page portant les neuf groupes.
5. **Le `thead`** absent à l'œil et **présent** dans l'arbre d'accessibilité.
6. **Aperçu d'impression** : le tableau est un tableau, en-têtes visibles.
7. **1280 px à 200 %** : le repli est actif.
8. **Toile de l'éditeur** : le rembourrage de cellule est bien celui du repli (classe doublée à
   l'œuvre), et l'aperçu montre le vrai tableau.
9. **`grep`** : zéro `#`, zéro `px` de couleur ou d'espacement hors de la liste close de littéraux ;
   zéro occurrence des six chaînes de colonne dans le rendu ; zéro `usort`/`ksort` dans le module.
10. **La fiche d'aide** : chaque affirmation vérifiée **à l'écran**, sur la pile qui tourne
    (décision 43 — trois fiches fausses ont bloqué le push du lot 5).

---

## 12. Dettes créées par ce contrat

| # | Dette | À payer par |
|---|---|---|
| ~~**T-#15-a**~~ | ~~**La primitive `.mtb-tableau` vit dans une feuille de bloc**, donc n'atteint que les pages portant le bloc — mèche datée vers un échec AA sur les fiches portée.~~ **PAYÉE PENDANT LE LOT PAR #16**, qui a hissé la primitive dans `base.css` §10 (l. 964-1115). Vérifié sur la pile : `base.css` est servie sur `/chien/luna/` et porte la requête média de §7.6 et la règle de cellule vide. **Reste dû** : la suppression du §1 de la feuille de bloc, séquencée **après** le commit de `base.css` par #16 — voir l'amendement du §5 | ✅ **payée** ; suppression du doublon à séquencer |
| ~~**T-#15-a bis**~~ | ~~La primitive définie deux fois.~~ **SANS OBJET** — le §1 a été supprimé le 2026-08-20 sur arbitrage de l'orchestrateur. Il n'y a plus qu'une définition, dans `base.css` §10. *La divergence annoncée avait bien commencé avant la suppression, et dans l'autre sens que je le supposais : la valeur exacte est **10,7854**, donc c'était la feuille de bloc qui avait raison et `base.css` qui arrondissait vers le bas. C'est exactement pourquoi un doublon ne se laisse pas vivre.* | ✅ **close** |
| **T-#15-b** | **Le sexe du chien n'est pas rendu** (§9). Q-e de `issue-5.md` §13 | `lead-design-mtb`, puis révision de §7.6 |
| ~~**T-#15-c**~~ | ~~La classe doublée `.mtb-tableau.mtb-tableau td`.~~ **PAYÉE le 2026-08-20** avec la suppression du §1 : elle n'est **recopiée nulle part**, et la mesure dans la toile confirme qu'elle était inutile une fois les deux règles dans la même feuille | ✅ **payée** |
| **T-#15-d** | **Le repli de §7.6 détruit les rôles de tableau** sous 48 rem (`display: block`/`grid`) : le `<thead>` découpé n'est plus *associé* aux cellules, il est lu comme une suite de mots avant les données pendant que chaque `::before` répète le même libellé — **double énoncé possible**. Patron de `MASTER.md`, pas de notre écriture ; le corriger demande des attributs ARIA côté serveur | `lead-design-mtb`, puis une issue `a11y` |
| ~~**T-#15-e**~~ | ~~La recette de masquage accessible écrite deux fois dans la feuille.~~ **PAYÉE le 2026-08-20** : les deux occurrences vivaient dans le §1, supprimé. *Le thème n'a toujours **aucune** primitive `.mtb-invisible` — la seule occurrence est `.skip-link.screen-reader-text:focus`, dont l'état masqué vient d'une feuille en ligne du cœur. La recette existe maintenant deux fois **dans `base.css` §10**, hors de cette empreinte* | ✅ payée ici ; le besoin de primitive reste, dans `base.css` |
| **T-#15-f** | **Trois conventions de `supports` coexistent** dans `includes/blocks/`. Ce module prend la plus sûre ; la divergence reste | une issue de consolidation |
| **T-#15-g** | **`docs/contracts/issue-1.md` §11 doit recevoir une ligne** `blocks / tableau-resultats / #15`. Hors de l'empreinte d'écriture de cette chaîne | `/lead-mtb` à la clôture du lot |

---

## 13. Questions ouvertes

**Questions de domaine bloquantes : aucune.** Ce composant n'affiche aucun fait d'élevage que le
serveur ne fournisse déjà en chaîne finie. Les neuf disciplines, leurs libellés, les six libellés de
colonne, la règle du Pays vide et les deux ordres de tri sont gelés et implémentés par #5.

**Q12** (« truffe » et « cavage » sont-ils la même chose ?) reste ouverte côté éleveuse et **ne bloque
pas ce code** : le site source range lui-même *Cavage* sous « Autres disciplines », et la neuvième
discipline se rend exactement comme les huit autres.

**Questions de design, pour `lead-design-mtb`** — aucune ne bloque l'écriture, toutes bloquent la
ratification.

> **Avertissement du 2026-08-20, à ne pas perdre.** Ces questions portaient sur des règles qui vivent
> désormais dans **`base.css` §10** (#16), hors de cette empreinte. Vérifié après la suppression du
> §1 : **Q-front-3, Q-front-4 et Q-front-7 sont bien reconsignées dans `base.css` §10** ; **Q-front-2
> (partiellement), Q-front-5 et Q-front-6 ne le sont nulle part.** Ce contrat est donc leur **seul**
> point de conservation — les cinq paires du §12.3 manquantes et la hauteur de cible en cellule se
> perdraient si on ne lisait que la feuille. Q-front-1 est la seule qui porte encore sur une règle
> présente dans `mtb-tableau-resultats.css`.

| # | Question | Coût de la réponse |
|---|---|---|
| **Q-front-1** | §7.1 se contredit sur ce composant (titres en canal texte, tableaux de résultats en canal large). Canal large retenu pour l'ensemble ; à 1440 px les `h2` commencent **256 px à gauche** de la prose de la page Travail | quatre lignes isolées au §2 de la feuille |
| **Q-front-2** | **`8rem` d'étiquette suit la taille de police racine.** À une racine de 32 px sur 360 px de fenêtre, l'étiquette prend 256 px des 324 disponibles et il reste **44 px** à la valeur. *Corrigé le 2026-08-20 : ma rédaction disait 56 px, elle supposait une gouttière fixe — `--e-3` vaut `.75rem` et **double aussi**, de 12 à 24 px. `324 − 256 − 24 = 44`. L'erreur rend la question un peu plus urgente, pas moins.* Le zoom **page** de §7.8 n'est pas concerné ; l'agrandissement du **texte seul** l'est. Correctif possible : `minmax(0, 8rem) 1fr` | une valeur |
| **Q-front-3** | Faut-il **restaurer les rôles de tableau par ARIA** sous 48 rem (T-#15-d), au prix d'attributs serveur, ou accepter la linéarisation ? | attributs serveur + arbitrage |
| **Q-front-4** | **`MASTER.md` ne mentionne `<caption>` nulle part.** Masqué visuellement ici — seule voie non inventive, le rendre visible exigerait une typographie de légende de tableau qui n'existe pas | une ligne de §4.5 ou §7.6 |
| **Q-front-5** | **Deux paires encre/fond manquent au §12.3**, produites par la rencontre du survol de ligne (prescrit par §12.9) et de styles déjà livrés : `--pin`/`--calcaire-creux` ≈ **12,7:1** et `--laiton-texte`/`--calcaire-creux` ≈ **4,74:1** (AA, marge 0,24). §12 déclare interdite toute paire absente du tableau | deux lignes de §12.3 |
| **Q-front-6** | **Hauteur de cible d'un lien dans une cellule.** §12.10 exige 44 px ; ni §7.6 ni §4.5 ne prescrivent de hauteur pour un lien de cellule, et la solution de `mtb-liste-portees.css` (`display: block; min-block-size: 44px`) déformerait la grille du repli | une ligne de §7.6 |
| **Q-front-7** | Le renvoi de §7.6 vers un « §9.6 » **pointe dans le vide** : le §9 s'arrête à 9.5. Déjà consigné par le contrat #2 ; reconsigné, non comblé | une correction de renvoi |
