# Contrat d'interface — Issue #54 — Le vocabulaire de l'écran d'une page

> **Gelé le 2026-09-08.** Relevés faits sur **WordPress 6.9**, site en **fr_FR**, page témoin **318
> « Placement »**. Toute chaîne future qui rouvre ce module **remesure d'abord** : les relevés portent
> leur version et leur date parce qu'ils se périmeront en silence (dette **T114**).

## 0. Convention de lecture, opposable à tout le document

Le lot 20 a été bloqué par une **prémisse énoncée comme un fait** dans un contrat gelé, qui a échappé
à la liste des mesures dues parce qu'elle était écrite au présent de l'indicatif. Ce contrat marque
donc chaque affirmation :

| Marque | Sens |
|---|---|
| **[M]** | **Mesuré.** Le geste est cité, sa sortie est recopiée. Opposable. |
| **[D]** | **Déduit.** Vraisemblable, jamais vérifié. **Ne se recopie pas comme un fait.** |
| **[àM]** | **À mesurer** par la chaîne d'implémentation, avant d'écrire la ligne qui en dépend. |

---

## 1. Ce que l'issue demandait, et pourquoi son énoncé était faux

L'issue #54 s'intitule « **Étendre le rappel gettext** aux libellés "Slug" et "Définir l'image mise en
avant" de l'écran d'une page ». **Cette prémisse est fausse pour les deux chaînes, et pas de la même
façon** — c'est le résultat central de cette chaîne, et il est mesuré.

| Chaîne | Ce que l'issue supposait | Ce qui est **[M]** |
|---|---|---|
| « Définir l'image mise en avant » | filtre `gettext` PHP | **Ni `gettext`, ni du JavaScript.** Le cœur émet `_x( 'Set featured image', 'page' )` — donc `gettext_with_context`, jamais `gettext`. Mais surtout : **le bouton lit `get_post_type_object('page')->labels->set_featured_image`**, donc le crochet juste est **`post_type_labels_page`**, qui **ne compare aucune chaîne** |
| « Slug » | filtre `gettext` PHP | **Aucun PHP n'émet cette chaîne sur cet écran.** Elle vient de `wp.i18n` dans `wp-includes/js/dist/editor.js`, sous le `msgid` nu `__("Slug")`. Un filtre `gettext` PHP y serait **inerte ici et débordant ailleurs** |

**L'analyse de brainstorm rangeait les deux chaînes du côté JavaScript. Elle se trompait sur la
première** : c'est la mesure M3 qui l'a établi, pas le raisonnement. La bonne conclusion n'était donc
« ni le titre de l'issue, ni le brainstorm », et aucune des deux n'était devinable.

### 1.1 Les relevés qui fondent tout le reste

Tous **[M]**, WordPress 6.9, 2026-09-08.

```
M1  get_post_type_object('page')->labels->{featured_image|set_featured_image|remove_featured_image|use_featured_image}
    → Image mise en avant | Définir l’image mise en avant | Supprimer l’image mise en avant | Utiliser comme image mise en avant
```

> **L'apostrophe du cœur est U+2019 (’), jamais U+0027 (').** Écrit ici parce que l'énoncé de l'issue
> emploie l'apostrophe droite : **tout `grep` visant le texte français échouerait sans le savoir.**
> Aucun de nos libellés de remplacement n'en porte, la question est donc théorique **aujourd'hui**
> et opposable **demain**.

```
M2  wp-includes/class-wp-post-type.php — les quatre sources anglaises passent par _x( …, 'page' )
    source de set_featured_image = « Set featured image »
    crochet disponible : post_type_labels_page   (filtre, 1 argument)
    il n'existe AUCUN filtre « get_post_type_labels » — c'est une fonction, pas un crochet
```

```
M3  sonde mu-plugin forçant labels->set_featured_image = SENTINELLE-MTB-54, sans garde is_admin(),
    puis Chrome réel piloté en CDP, écran post.php?post=318&action=edit :
    document.querySelector('.editor-post-featured-image__toggle').textContent → "SENTINELLE-MTB-54"
    VERT. Le bouton lit bien le libellé du type de contenu.
```

```
M4  grep "Slug" wp-includes/js/dist/editor.js → 5 chaînes littérales, TOUTES __("Slug"), AUCUNE _x()
    block-editor.js, edit-post.js, edit-site.js, blocks.js, components.js, block-library.js → 0
    catalogue fr_FR-bf0f094965d3d4a95b47babcb35fc136.json (= md5 de « wp-includes/js/dist/editor.js »),
      poignée « wp-editor », textdomain « default », messages["Slug"] = ["Slug"]
      → la traduction française de « Slug » EST « Slug ». Ce n'est pas une traduction manquante.
    au navigateur : wp.hooks.addFilter('i18n.gettext', …) MORD sur 6.9
    carte d'émission sur cet écran : 3 émissions (rangée, titre de la fenêtre volante, étiquette du champ)
```

```
M5  post_type_supports('page','excerpt') → false        → « Extrait » n'est PAS sur cet écran
    post_type_supports('page','thumbnail') → true
    rangées de l'onglet « Page » : État | Publier | Slug | Auteur/autrice | Modèle | Commentaires | Parent
    avec une photo posée : les boutons disent « Remplacer » / « Retirer », qui sont __("Replace") /
      __("Remove") EN DUR — labels->remove_featured_image N'EST PAS LU par cet écran (0 occurrence
      de « remove_featured_image » dans editor.js)
```

---

## 2. Le mécanisme gelé — deux moitiés qui ne se ressemblent pas

**Un module neuf : `wp-content/plugins/mtb-core/includes/admin/vocabulaire-page/`**, namespace
`MTB\Core\Admin\VocabulairePage`. Chargement **automatique** par le chargeur de groupe (un dossier =
un module, seul `bootstrap.php` est inclus) : **ni `mtb-core.php` ni `includes/class-loader.php` ne
sont ouverts**, et ils sont hors empreinte.

| Moitié | Crochet | Portée | Compare-t-elle une chaîne ? |
|---|---|---|---|
| **Photo** | `post_type_labels_page` (PHP, posé **à l'inclusion**) | **structurelle** — le nom du filtre *est* la garde | **Non.** Donc **aucune panne possible par reformulation d'une chaîne du cœur** |
| **« Slug »** | `i18n.gettext` (`wp.hooks`, JavaScript), sous garde d'écran serveur | par **écran** | **Oui**, sur la source **anglaise** |

**Pourquoi ne pas étendre `admin/description-photo`**, dont c'est pourtant le sujet voisin : sa garde
`is_admin()` en tête de fichier, qu'il revendique par écrit, **tuerait** notre filtre PHP (§4) ; le
neutraliser par le préfixe `_` emporterait deux pannes sans rapport ; et son sujet est *un champ à
travers toutes les surfaces médias*, quand le nôtre est *un type de contenu*. Ce sont les trois
critères de séparation que son propre en-tête a fixés, appliqués.

---

## 3. Libellés rendus — recopiés, jamais inventés

| Clé / source | Libellé rendu | D'où il est **recopié** | Paraît-il sur cet écran ? |
|---|---|---|---|
| `featured_image` | **Photo principale** | `MASTER.md` §10.2 **et** `content/portee/bootstrap.php:93` | **[M]** oui — titre de la fenêtre des photos |
| `set_featured_image` | **Choisir la photo principale** | `content/portee/bootstrap.php:94` | **[M]** oui — le bouton |
| `remove_featured_image` | **Retirer la photo principale** | `content/portee/bootstrap.php:95` | **[M] NON** — cet écran dit « Retirer », en dur |
| `use_featured_image` | **Utiliser comme photo principale** | `content/portee/bootstrap.php:96` | **[D]** non relevé — vraisemblablement la fenêtre des photos |
| `Slug` (source anglaise) | **Adresse de la page** | `MASTER.md` §10.2 **et** `fields/portee/ecran.php:52` | **[M]** oui — 3 émissions |

**Les quatre clés photo sont écrites, bien que deux seulement soient mesurées à l'écran** — et cette
asymétrie est **déclarée, pas dissimulée**. Quatre motifs, dans cet ordre :

1. Renommer `set_featured_image` sans `featured_image` donnerait un bouton « Choisir la photo
   principale » sous un titre « Image mise en avant » : le mot interdit resterait à l'écran.
2. La fiche d'aide deviendrait inécrivable — elle emploierait le mot interdit pour désigner l'endroit
   où il ne faut pas l'employer.
3. `mtb_portee` et `mtb_chien` disent **déjà** les quatre. Une `page` qui n'en dirait que deux créerait
   un écart qu'une chaîne future « corrigerait » au hasard, dans un sens ou dans l'autre — le défaut
   exact que la dette T56 a fait payer au lot 13.
4. Le coût est de deux affectations.

**§10.2 ne fige que le nom, « Photo principale ».** Les trois verbes ne sont dans aucune section de
`MASTER.md` : la règle qui s'y applique n'est pas « recopier §10.2 » mais **« recopier le dépôt »**,
pour que les trois types disent le même mot. Un dev qui les retaperait produirait une quatrième
formulation. **Le geste prescrit est : ouvrir `content/portee/bootstrap.php:93-96`, copier, coller.**

---

## 4. États spéciaux

| État | Comportement exigé | Statut |
|---|---|---|
| **`is_admin()` sur le filtre PHP** | **INTERDIT.** Voir l'encadré ci-dessous | **[M]** nuisible |
| **Site resté en anglais** | Les **deux** moitiés mordent à l'identique : la comparaison porte sur la source anglaise, jamais sur la traduction. `docker/provision/provision.sh` documente ce cas comme possible | **[M]** pour le JS ; **[M]** trivialement pour le PHP (il ne compare rien) |
| **`get_current_screen()` indisponible** | « Ce n'est pas mon écran », **jamais une erreur**. Retour immédiat, aucun script mis en file | à respecter |
| **Requête REST** | La moitié PHP **doit** mordre. C'est le cœur du dossier | **[M]** |
| **WP-CLI** | La moitié PHP mord ; la moitié JS est absente. Aucune écriture, aucune option, aucun `error_log` | **[M]** |
| **Requête publique anonyme** | Le filtre PHP est attaché et court, pour le coût d'une affectation. **Aucun libellé d'administration n'est imprimé au visiteur** | à respecter |
| **Éditeur de site / de motifs / de widgets** | Écartés par `'post' !== $ecran->base`. Et fermés à l'éditrice, qui n'a ni `switch_themes` ni `edit_theme_options` | à respecter |
| **Éditeur de blocs désactivé sur les pages** | La moitié PHP continue de mordre ; la moitié JS ne se met pas en file. **Le module reste juste, à moitié** | **[D]** |
| **`libelle_non_remplace`** | Le cœur a reformulé sa source : l'écran redit le mot interdit. Panne **bénigne** (rien de cassé, rien de perdu) mais **silencieuse**. **Aucun code ne peut la détecter** | assumé, §7 |
| **Module désactivé par `_vocabulaire-page`** | Retour exact à l'état d'avant #54, sur les deux moitiés à la fois, sans emporter aucun autre module | à respecter |

> ### AUCUNE GARDE `is_admin()` SUR `post_type_labels_page` — c'est le piège le plus coûteux de l'issue, et il est **mesuré**
>
> Une variante gardée par `if ( ! is_admin() ) { return $labels; }` a été jouée. Résultat **[M]** :
>
> ```
> bouton, au premier rendu (préchargement né dans wp-admin) → "SENTINELLE-MTB-54-GARDEE"
> wp.apiFetch('/wp/v2/types/page?context=edit')             → "Définir l’image mise en avant"
> fetch('/wp-json/…', X-WP-Nonce)                           → "Définir l’image mise en avant"
> WP-CLI                                                     → "Définir l’image mise en avant"
> ```
>
> La garde **ne casse pas** le premier affichage : elle fait **diverger le préchargement de la façade
> REST**. Dès que l'éditeur re-sollicite `/wp/v2/types/page`, le libellé **retombe au mot interdit**.
> **Un écran qui se répare au chargement puis se casse à l'usage est pire qu'un écran qui ne change
> jamais** — et il ne laisse ni erreur, ni ligne au journal.
>
> Le contexte se teste **dans le rappel qui met le script en file**, jamais au chargement du module.
> C'est le partage que `fields/sommeil/bootstrap.php` documente déjà : « l'un a besoin de la garde
> d'ouverture, l'autre serait TUÉ par elle ».

---

## 5. Les interdits que le module s'impose — opposables à toute chaîne future

1. **Aucune garde `is_admin()`** sur `post_type_labels_page`, ni en tête de fichier, ni dans le rappel.
2. **Le filtre PHP est posé à l'inclusion, jamais sur `init`** : sur `init` il ne mordrait rien, en silence.
3. **Aucune fonction de traduction dans les rappels**, PHP comme JavaScript — `__()`, `_x()`,
   `translate()`, `wp.i18n.__()`, `sprintf()`. Nous **sommes** dans le point d'extension : récursion
   infinie dès la première chaîne.
4. **Aucun échappement dans les rappels** : ils n'impriment rien. Le cœur, ou React, échappe. Échapper
   ici sortirait une apostrophe en `&#039;` au milieu d'une étiquette. Écart explicite et motivé à la
   règle générale de `CLAUDE.md`.
5. **Aucune valeur de remplacement ne porte `%`, `<`, `>` ni `&`** — plusieurs chaînes du cœur sont
   passées à `sprintf` (PHP comme `@wordpress/i18n`), et un `%` de trop y lève une `ArgumentCountError`,
   c'est-à-dire un écran blanc. C'est le piège que `admin/corbeille/bootstrap.php` documente déjà.
6. **Comparaison stricte de la chaîne entière** : aucune expression régulière, aucun `stripos`, aucun
   `indexOf`, aucun `str_replace`, aucun `replace`, aucun `toLowerCase`. C'est ce qui borne le module
   aux émissions relevées et lui interdit de déborder.
7. **Aucun `get_current_screen()` dans les rappels.** La borne d'écran est posée **côté serveur**, dans
   la mise en file. La reposer côté client serait deux vérités à tenir.
8. **Aucune chaîne n'entre dans une table sans avoir été mesurée sur l'écran d'une page.** Une entrée
   écrite « au cas où » est un renommage non mesuré.
9. **Aucune chaîne n'est composée côté JavaScript** : elles arrivent par `wp_add_inline_script` +
   `wp_json_encode`, qui échappe le non-ASCII en `\uXXXX` — propriété gelée par le contrat #52.
10. **`wp-editor` et `wp-edit-post` ne sont jamais déclarées en dépendances du script** : elles
    inverseraient l'ordre d'impression et tueraient le filtre. Contre-intuitif, donc à écrire.
11. **Aucun octet de CSS**, aucun `make css`, aucun artefact `*.min.css` dans l'empreinte. Le contrat
    #52 gèle déjà « aucun octet de CSS » sur cet écran même ; et `CLAUDE.md` ferait entrer les
    artefacts régénérés dans l'empreinte, donc un recouvrement avec toute autre issue CSS du lot.
12. **Aucun numéro de ligne du cœur n'est cité** (dette T114) : on cite un **nom de filtre** — API
    publique — ou une **chaîne source**, avec sa version et sa date de relevé.
13. **Aucune commande WP-CLI n'est déclarée dans `admin/**`** : toutes celles du dépôt vivent dans
    `migration/**`, et `admin/**` est un groupe d'écrans.
14. **Les quatre libellés photo, ou aucun.** Jamais un sous-ensemble (§3).
15. **`mtb-core.php` et `includes/class-loader.php` ne sont pas ouverts.**
16. **Aucune chaîne rendue n'est recopiée ailleurs** que par appel des deux fonctions de table.
    L'interdit est écrit **sans compte**, à dessein : le §12 bis a fait entrer une sixième chaîne
    (le nom accessible du bouton photo) après le gel, et un nombre inscrit ici se serait périmé en
    silence — le défaut exact que ce contrat traque partout ailleurs. Les tables font foi.

---

## 6. Périmètre — ce que cette issue ferme, et ce qu'elle laisse ouvert

**La frontière retenue est une règle de sélection des chaînes, pas une borne d'exécution**, et cette
distinction doit être écrite parce qu'elle a failli être promise à l'envers :

> Une fois le script chargé sur l'écran d'une page, le filtre `i18n.gettext` s'applique à **tout le
> JavaScript de cet écran**, dialogues et inserteur compris. On ne peut pas le borner au panneau. Ce
> qui est borné, c'est **ce qui entre dans la table** : uniquement des chaînes sources **mesurées sur
> l'onglet « Page »**. Si l'une d'elles paraît aussi ailleurs sur le même écran, elle y est renommée
> aussi, et **c'est accepté**. Aucune chaîne n'entre dans la table parce qu'elle a été vue ailleurs.

### 6.1 Arbitrages de périmètre

| Cas | Verdict | Motif |
|---|---|---|
| « Slug » (3 émissions) | **dans #54** | §10.4 l'interdit tel quel ; §10.2 fournit « Adresse de la page » |
| Famille « image mise en avant » (4 clés) | **dans #54** | §10.4 l'interdit ; §10.2 fournit « Photo principale » ; §3 pour les quatre |
| **« Extrait »** | **hors #54, et la question est CLOSE** | **[M]** `post_type_supports('page','excerpt')` rend `false` : **le panneau n'est pas sur cet écran**. La question bloquante que le plan avait ouverte (§10.2 ne fournit aucun remplacement) **se dissout par la mesure** — il n'y a rien à remplacer |
| « Modèle » | **hors #54** | §10.4 interdit `template`, l'anglais. Le mot **français** « Modèle » n'est dans aucune liste, et c'est le mot du cœur. Le renommer serait inventer un vocabulaire que §10.2 ne fige pas |
| État · Publier · Auteur/autrice · Commentaires · Parent | **hors #54** | aucun mot interdit |
| **`aria-label` « Modifier ou remplacer l'image mise en avant »** (quand une photo est posée) | **dans #54, SOUS CONDITION DE MESURE** — voir §6.2 | mot interdit §10.4, dans le **nom accessible** du bouton. L'accessibilité AA est **bloquante** au brief : laisser le mot interdit là où un lecteur d'écran le prononce, en l'ayant retiré du libellé visible, créerait l'asymétrie exacte que §3 refuse |
| **« permalien »**, dans le texte d'aide de la fenêtre volante « Slug » | **hors #54 — dette T-#54-g** | §6.3 |
| « Slug » en **Modification rapide** (Pages, Portées, Chiens, Articles) | **hors #54 — dette T-#54-b** | §6.4 |
| « Slug » sur les écrans de **taxonomie** | hors #54 — dette T-#54-c | l'éditrice n'y va pas |
| « Slug » sur **Réglages → Permaliens** | **hors sujet** | écran fermé à l'éditrice, faute de `manage_options` |
| « Image mise en avant » sur un **Article** | hors #54 — dette T-#54-d | une ligne (`post_type_labels_post`), mais l'éditrice n'écrit pas d'articles. **Ne pas le faire « en passant »** : l'issue ne ferme qu'un écran |
| Titres de blocs de l'**inserteur** | hors #54 | classe entière, mécanisme encore autre, issue distincte |
| « Texte alternatif » des blocs Image/Galerie | hors #54 | **T-#35-a**, inchangée |
| Menu « Médias » | hors #54 | **T-#35-b**, inchangée, et elle appartient à `description-photo` par son en-tête |

### 6.2 L'`aria-label` — la seule entrée conditionnelle de la table, et sa condition est stricte

**[M]** Avec une photo posée, le bouton porte `aria-label = "Modifier ou remplacer l’image mise en
avant"`. Le mot interdit y survit, **invisible à l'œil, prononcé à voix haute**.

**[àM] Condition d'entrée dans la table, à remplir toutes les trois :**

1. la **source anglaise exacte** est relevée dans `wp-includes/js/dist/editor.js`, et c'est un `msgid`
   **nu et entier** (`__( '…' )`), non composé, non `sprintf` ;
2. cette source **ne porte ni `%`, ni `<`, ni `>`, ni `&`** (interdit n° 5) ;
3. le remplacement est **la phrase française du cœur avec le seul groupe nominal substitué** —
   « Modifier ou remplacer **la photo principale** » —, jamais une phrase rédigée.

**Si l'une des trois échoue : la chaîne N'ENTRE PAS dans la table**, et devient la dette **T-#54-f**,
datée, avec sa source relevée. On n'invente pas une phrase qu'aucune source ne fige.

### 6.3 « permalien » n'entre pas, et le motif est un précédent, pas une commodité

`admin/description-photo/bootstrap.php` a **refusé** de remplacer le texte d'aide du cœur, et a écrit
pourquoi : la phrase du cœur portait une information factuelle que notre phrase n'aurait pas dite, et
« **on ne remplace jamais un texte par un autre qui en dit moins** » ; elle portait de surcroît `%`,
`<` et `>`. **Ce précédent porte exactement sur cette classe** : un texte d'aide, pas une étiquette.
§10.2 fige des **libellés**, jamais des phrases. → dette **T-#54-g**.

### 6.4 La Modification rapide — découverte de cette chaîne, et elle élargit le constat de l'issue

**[M]** Le cœur émet « Slug » **en PHP** à sept endroits :

```
class-wp-terms-list-table.php:194, :221, :701   ·   edit-tag-form.php:155   ·   edit-tags.php:469
meta-boxes.php:943, :1668
class-wp-posts-list-table.php:1708              ← Modification rapide
```

`class-wp-posts-list-table.php:1708` sert la **Modification rapide de toutes les listes** — donc
**Pages, Portées, Chiens et Articles**. L'énoncé de l'issue, « `page` est le seul type de contenu
jamais traité par le rappel de vocabulaire », **est donc plus optimiste que la réalité** : le mot
interdit paraît aussi sur deux écrans quotidiens de l'éleveuse.

**Décision : #54 ne l'élargit pas.** Le remède mord sur trois types, appelle un autre module
(`admin/listes/**`), et une chaîne sœur partage l'arbre de travail. → dette **T-#54-b**, à ouvrir en
issue. **[M]** Aucune de ces sept émissions ne paraît sur l'écran de #54 (`grep ">Slug<\|slugdiv"` sur
le HTML servi par `post.php?post=318` : vide).

---

## 7. Mode de panne et témoin — l'aveu est explicite

| | Moitié photo (PHP) | Moitié « Slug » (JS) |
|---|---|---|
| **Comment elle tombe** | Le cœur cesse de lire `labels` sur cet écran (M3 devenu rouge après une montée de version), ou renomme une clé de libellé — quasi impossible, c'est l'API publique de `register_post_type` | (a) le cœur reformule la source `Slug` ; (b) une dépendance manque ; (c) la chaîne est capturée avant notre filtre ; (d) le point d'extension change de nom ; (e) la charge globale n'est pas imprimée |
| **Panne par reformulation d'une chaîne ?** | **Non — elle ne compare aucune chaîne.** C'est la supériorité structurelle de cette moitié | **Oui** |
| **Bruit** | **Silencieuse** | **Silencieuse dans les cinq cas.** L'écran redit « Slug », la page s'enregistre, `debug.log` reste vide |
| **Témoin** | `wp eval 'echo get_post_type_object("page")->labels->set_featured_image;'` prouve que **le filtre mord**. Il **ne prouve pas** que l'écran le lit — ça, c'est M3, et M3 se remesure au navigateur | **Aucun depuis PHP.** Chercher `"Slug"` dans un paquet minifié est un **canari faux vert** : la présence de la source ne dit rien de l'interception, et son absence ne dirait rien non plus. Le seul témoin honnête est un navigateur réel |

**Aucun témoin automatique n'est livré, et c'est une décision, pas un oubli.** Toutes les commandes
WP-CLI du dépôt vivent dans `migration/**`, hors empreinte ; en inventer une dans `admin/**` casserait
deux conventions pour un témoin d'une ligne, et ce témoin serait de surcroît **faux vert** sur la
moitié qui en aurait le plus besoin. **La vérification de #54 est manuelle, rejouable, et se rejoue en
entier à chaque montée de WordPress.** Elle est écrite dans l'en-tête du module et au §8. C'est plus
honnête que le vert automatique dont la dette **T109** vient d'établir le coût.

**Parades écartées, pour qu'on ne les re-litige pas** : un `_doing_it_wrong` (un filtre ne peut pas
savoir qu'une chaîne qu'il n'a jamais reçue existe) · re-vérifier que la traduction diffère (récursion)
· mémoriser un état en option (une écriture en base sur un chemin de lecture).

**La seule parade retenue est humaine** : une ligne dans la rubrique « Ce n'est pas normal,
signalez-le » de la fiche du guide. L'éleveuse est le seul détecteur qui regarde vraiment l'écran.

---

## 8. Protocole de vérification — à jouer, pas à déduire

**[àM]** Toutes ces mesures sont dues avant que la chaîne se déclare terminée. Une mesure non jouée se
rapporte comme non jouée.

**M6 — le filtre posé depuis une extension ordinaire mord-il ?**
La sonde de M3 était un **mu-plugin**, chargé **avant** `create_initial_post_types()` ; une extension
ordinaire est chargée **après**. La mesure a relevé **[M]** qu'une **seconde construction des libellés
a lieu après `plugins_loaded`**, ce qui **[D]** suffit — mais cela reste une déduction. **Contrôle
dû** : `wp eval` sur les quatre libellés, le module en place, sans sonde.

**V1 — l'écran d'une page**, Chrome réel piloté en CDP, `post.php?post=<id>&action=edit` :
« Adresse de la page » présent · **recherche exhaustive des nœuds feuilles valant exactement
« Slug » → liste vide** · « Choisir la photo principale » présent · « Image mise en avant » absent.

**V2 — l'ordre d'impression.** Non couvert par la mesure amont, qui a posé son filtre **à la main dans
la console, après le rendu**. Il faut prouver que le filtre est en place **avant le premier rendu**,
donc **sans** bascule d'onglet ni interaction.

**V3 — non-débordement.** Écran d'une portée · écran d'un chien · liste des Pages · liste des Portées ·
Médiathèque · écran d'un article : **inchangés**. « Slug » **doit rester** là où l'issue ne mord pas :
c'est la preuve que la garde tient, jamais un échec.

**V4 — la fenêtre des photos** ouverte depuis le bouton : titre et bouton en français métier.

**G1 à G6 — le rendu de la rangée**, mesuré **après coup**, jamais déduit. « Adresse de la page » fait
18 caractères contre 4 pour « Slug », dans une zone latérale d'environ 280 px, sur une rangée à deux
colonnes.

| | |
|---|---|
| **G1** | Le libellé passe-t-il à la ligne ? Sur combien de lignes ? La valeur cliquable reste-t-elle sur une seule ? |
| **G2** | La valeur cliquable est-elle encore lisible, ou tronquée au point de ne plus se lire ? |
| **G3** | Cible de la valeur cliquable **≥ 24 px** — **et non 44 px**. Doctrine gelée pour les écrans d'administration par les contrats #52 et #32 (SC 2.5.8, exception d'espacement). Le seuil de 44 px est le seuil **public**. **#54 ne rouvre pas cette question.** |
| **G4** | Zoom 200 % à 1280×720 : la rangée tient, sans défilement horizontal introduit dans la zone latérale |
| **G5** | Fenêtre 360 px : la zone latérale bascule en surcouche — la rangée y est lisible |
| **G6** | Les deux moitiés ensemble, à 1440 px et à 360 px |

**Si le rendu se dégrade** — trois voies, dans cet ordre, et deux interdits :

1. **Accepter le retour à la ligne** si la valeur reste lisible et cliquable. `description-photo` a
   fait passer un libellé de 16 à 52 caractères sans que cela devienne un blocage.
2. Si la valeur devient illisible : **dette déclarée et question remontée**. §10.2 est figé ; seul
   `lead-design-mtb` ou l'utilisateur peut le réviser.
3. **Interdit — on ne raccourcit pas un libellé figé en douce.** Ni « Adresse », ni « Adresse page »,
   ni une abréviation.
4. **Interdit — aucun CSS d'administration dans cette issue** (interdit n° 11).

**Non-régression du chargeur** : empreinte identique (aucun type, aucune taxonomie, aucune règle de
réécriture n'a bougé) · `debug.log` **vide** · `git status` sans aucun fichier hors empreinte.

---

## 9. Empreinte fichiers

**Écrits par cette chaîne, et rien d'autre :**

- `wp-content/plugins/mtb-core/includes/admin/vocabulaire-page/**` (module neuf)
- `docs/guide/contenu-mettre-en-sommeil-et-reveiller.md` — **uniquement** la ligne 59 et l'`alt` de la
  ligne 70, **après** que l'écran a changé
- `docs/guide/captures/sommeil-page-zone-laterale.png` — reprise
- `docs/contracts/issue-54.md` — ce document

**Sources à recopier, jamais à modifier** : `content/portee/bootstrap.php:93-96` ·
`fields/sommeil/editeur-de-blocs.php:82-92` (la garde d'écran) ·
`admin/description-photo/bootstrap.php` (la forme de preuve) · `MASTER.md` §10.2.

---

## 10. Arbitrages rendus

| # | Désaccord ou question | Décision | Motif |
|---|---|---|---|
| **A1** | L'issue prescrit « étendre le rappel `gettext` » | **Refusé pour les deux chaînes.** Photo → `post_type_labels_page` ; « Slug » → `i18n.gettext` (JS) | Mesuré, §1. Un `gettext` PHP serait **inerte ici et débordant ailleurs** — deux fautes pour le prix d'une |
| **A2** | Le brainstorm rangeait les deux chaînes du côté JavaScript | **Refusé pour la photo** | M3 : le bouton lit `labels->set_featured_image`. Le brainstorm avait raison sur « Slug », tort sur la photo |
| **A3** | Recopier la garde `is_admin()` de `description-photo` | **INTERDIT** | Mesuré nuisible : libellé **intermittent**, réparé au chargement puis cassé à l'usage, sans erreur ni journal |
| **A4** | Quatre libellés photo ou seulement les deux mesurés à l'écran | **Les quatre** | §3, quatre motifs. Et l'asymétrie mesuré/écrit est **déclarée** au §3, pas dissimulée |
| **A5** | « Extrait » — question bloquante ouverte par le plan | **Close par la mesure** | `post_type_supports('page','excerpt') === false` : le panneau n'est pas sur cet écran. **Rien à remonter à l'utilisateur** |
| **A6** | L'`aria-label` portant le mot interdit | **Entre dans la table, sous trois conditions strictes** (§6.2), sinon dette T-#54-f | AA est bloquante au brief. Retirer le mot interdit de l'étiquette visible en le laissant dans le nom accessible reproduirait, pour un lecteur d'écran, l'asymétrie que A4 refuse |
| **A7** | « permalien » dans le texte d'aide | **Hors périmètre — dette T-#54-g** | Précédent gelé de `description-photo` : on ne remplace pas un texte d'aide du cœur ; §10.2 fige des libellés, pas des phrases. **Asymétrie assumée avec A6** : un nom accessible est une étiquette, un texte d'aide n'en est pas une |
| **A8** | La Modification rapide dit « Slug » sur trois types | **Hors périmètre — dette T-#54-b** | Autre écran, autre mécanisme (PHP), autre module, trois types. Une chaîne sœur partage l'arbre |
| **A9** | `page-proteger-une-page-par-mot-de-passe.md:31` dit « Slug » et sort de l'empreinte | **Non touché ici — remonté au lot** | §11 |
| **A10** | Un témoin automatique | **Aucun.** Vérification manuelle, rejouable, écrite | §7. Un canari faux vert est pire que pas de témoin — c'est le défaut que T109 nommait |
| **A11** | Le seuil de cible tactile | **24 px**, doctrine d'administration des contrats #52 et #32. **Non rouvert** | Le seuil de 44 px est le seuil public |
| **A12** | Étendre `admin/description-photo` | **Refusé** — module neuf `admin/vocabulaire-page/` | §2, les trois critères de séparation que `description-photo` a lui-même fixés |

---

## 11. Ce qui sort de l'empreinte et remonte au lot

**`docs/guide/page-proteger-une-page-par-mot-de-passe.md:31`** écrit, mot pour mot comme la fiche du
sommeil : « une suite de rangées : **État**, **Publier**, **Slug**, **Auteur/autrice**, **Modèle**,
**Commentaires**, **Parent** ».

**Le fait, dit sans l'atténuer** : dès que ce module est livré, **cette fiche ment à l'éleveuse** —
elle cherchera « Slug » et lira « Adresse de la page ». Ce n'est pas une coquetterie de vocabulaire,
c'est un guide qui décrit un écran qui n'existe plus.

**Coût chiffré : une ligne** (`:31`), plus sa capture si elle en porte une sur cette rangée.

**Ce n'est pas du ressort de cette chaîne** : `docs/guide/**` appartient à l'issue **#55**, séquencée
juste après celle-ci, et l'écraser depuis ici détruirait son travail. **Remonté au lot**, et écrit ici
pour ne pas dépendre d'une mémoire.

---

## 12. Dettes ouvertes par cette issue

| # | Dette | Payée par |
|---|---|---|
| **T-#54-a** | Les quatre libellés photo sont désormais écrits **trois fois** dans le dépôt — `content/portee/`, `content/chien/`, et ici. `content/**` est hors empreinte, donc rien ne tient les trois ensemble : **une divergence future serait muette** | l'issue qui rouvrira `content/**` |
| **T-#54-b** | Le cœur émet « Slug » **en PHP** dans la **Modification rapide** (`class-wp-posts-list-table.php`), donc sur **Pages, Portées, Chiens et Articles** — deux écrans quotidiens de l'éleveuse. **L'énoncé de #54, « `page` est le seul type jamais traité », était plus optimiste que la réalité** | une issue `contenu`, sur `admin/listes/**` |
| **T-#54-c** | « Slug » sur les écrans de taxonomie (`class-wp-terms-list-table.php`, `edit-tag-form.php`, `edit-tags.php`) | priorité basse — l'éditrice n'y va pas |
| **T-#54-d** | « Image mise en avant » sur un **Article** (`post_type_labels_post`, une ligne) | à ne pas faire « en passant » |
| **T-#54-e** | La vérification de #54 est **entièrement manuelle** et se rejoue **en entier** à chaque montée de WordPress. Même famille que **T114** | une passe d'outillage |
| **T-#54-f** | *(conditionnelle)* L'`aria-label` du bouton photo porte le mot interdit, si les trois conditions du §6.2 n'ont pas été remplies | une issue `a11y` |
| **T-#54-g** | Le texte d'aide de la fenêtre volante « Slug » emploie « **permalien** », mot interdit §10.4. Non traité par précédent gelé (§6.3) | une issue qui arbitrera les textes d'aide du cœur |

---

## 12 bis. Résolution des conditionnelles — mesurée après implémentation, le 2026-09-08

Ce paragraphe **complète** le contrat, il ne le rouvre pas : il consigne l'issue des deux seules
branches que le gel avait laissées ouvertes, et le résultat des contrôles du §8.

**L'`aria-label` entre dans la table. La dette T-#54-f n'est pas ouverte.** Les trois conditions du
§6.2 sont **[M]** vertes :

```
source, wp-includes/js/dist/editor.js, une seule occurrence :
  "aria-label": !featuredImageId ? null : __( "Edit or replace the featured image" )
  → msgid nu et entier, non composé, aucun sprintf                              condition 1 VERTE
la source ne porte ni % ni < ni > ni &                                          condition 2 VERTE
catalogue : "Edit or replace the featured image":["Modifier ou remplacer l’image mise en avant"]
  substitution du seul groupe nominal → « Modifier ou remplacer la photo principale »
  verbe, conjonction et ordre des mots inchangés ; aucune phrase rédigée        condition 3 VERTE
```

**M6 est vert, et la déduction du §8 est levée.** Le filtre posé depuis une **extension ordinaire**
— donc après `create_initial_post_types()`, contrairement au mu-plugin de la sonde amont — mord bien :

```
WP-CLI : Photo principale|Choisir la photo principale|Retirer la photo principale|Utiliser comme photo principale
REST   : Photo principale|Choisir la photo principale|Retirer la photo principale|Utiliser comme photo principale
post / attachment : « Définir l’image mise en avant » — intacts
```

REST et `wp-admin` disent la **même** chose : c'est la divergence que la garde `is_admin()` aurait
créée, et elle n'existe pas. **Aucun repli sur `init` n'a été nécessaire.**

**V2 est vert, et c'est le contrôle que la mesure amont ne pouvait pas rendre** : elle avait posé son
filtre à la main dans la console, **après** le rendu, puis forcé une bascule d'onglet. Le contrôle
d'implémentation a lu la rangée **au premier rendu, sans un clic** : elle portait « Adresse de la
page ». Le filtre est donc en place **avant** le premier rendu.

**V3 confirme les dettes sur pièces, et « Slug » subsistant est la preuve que la garde tient** :
écran d'un article (1 nœud « Slug » + 1 « image mise en avant » → **T-#54-d**), Modification rapide
des listes **Pages et Portées** (1 nœud « Slug » chacune → **T-#54-b**). La charge
`window.mtbVocabulairePage` n'est imprimée sur **aucun** de ces six écrans.

**G1 à G6.** Le seul point non trivial est **G1** : à 1440 px, « Adresse de la page » passe sur
**deux lignes** et la rangée monte de 32 à 52 px. **Accepté par la voie 1 du §8**, et l'appui est
mesuré : « **Auteur/autrice** » occupe **déjà** 46 px sur deux lignes sur ce même écran — un libellé
sur deux lignes y est un état normal du cœur, pas un défaut que nous introduisons. La valeur cliquable
reste sur une ligne, non tronquée (`textOverflow: clip`, `scrollWidth == clientWidth`), cible
**84 × 32 px** contre le seuil de 24 px. **Au zoom 200 % et à 360 px le rendu s'améliore** : la zone
latérale bascule en surcouche et le libellé revient sur une ligne. **Aucun libellé n'a été raccourci,
aucun octet de CSS n'a été écrit.**

**Non-régression [M]** : empreinte du chargeur inchangée · `debug.log` **2838 octets avant et après**
une requête publique anonyme *et* une requête d'administration · page publique `/placement/` en 200,
**zéro** occurrence d'un libellé d'administration · aucune sonde laissée dans `mu-plugins/` · page 318
inchangée (`thumb=0`, `post_modified` intact, aucune révision créée).

**Reste [D], et déclaré comme tel** : le site basculé en anglais (propriété structurelle — les clés de
table sont les sources anglaises et la moitié PHP ne compare rien — mais non jouée, elle exigerait une
écriture en base) · l'éditeur de blocs désactivé sur les pages · la restitution vocale réelle du
nouveau nom accessible (l'attribut est mesuré, sa prononciation ne l'est pas) · `use_featured_image`,
posé au titre de l'interdit n° 14 mais **affiché par aucun écran relevé** — c'est l'asymétrie déclarée
au §3, pas un oubli · la page d'**accueil**, dont la rangée dit « Link » et non « Slug », non couverte.

---

## 12 ter. La capture du guide — sa provenance, et la reprise qui reste DUE

**Reprise le 2026-09-08**, en même temps que l'`alt` qu'elle illustre, dans le commit `f462817` :
`docs/guide/captures/sommeil-page-zone-laterale.png`, **294 × 705** — les dimensions exactes de la
capture qu'elle remplace, pour que l'échelle de la fiche ne dérive pas. Chrome installé, piloté en CDP,
lancé en **`--lang=fr-FR`** : sans ce drapeau les dates du panneau sortent en anglais et l'image
mentirait sur un détail que l'éleveuse voit.

**Six assertions jouées AVANT l'écriture du fichier** — la prise avorte plutôt que de mentir (dette
**T103**), et elle a effectivement avorté une fois avant d'aboutir :

```
« Adresse de la page » présent                                        OK
aucune feuille de l'arbre ne vaut exactement « Slug »                 OK   (recherche exhaustive)
case « Mettre ce contenu en sommeil » cochée                          OK
« image mise en avant » absent de l'écran                             OK   (0 occurrence)
bouton « Enregistrer » présent                                        OK
bouton « Enregistrer » compris dans le cadre de 294 px                OK
```

Les trois autres captures de la fiche ont été **relues une par une** et **aucune n'est concernée** par
ce renommage : `sommeil-page-accueil-avertissement.png` ne montre aucune rangée ·
`sommeil-liste-chiens-mention.png` est une liste sans Modification rapide ouverte ·
`sommeil-portee-encadre-publier.png` est l'encadré **Publier** de l'éditeur classique d'une portée,
que #54 ne touche pas.

### ⚠️ REPRISE DUE — cette capture porte un défaut vivant qui n'est pas le nôtre

L'image montre la rangée **Auteur/autrice** valant **« (Aucun auteur/autrice) »**, alors que
`post_author` de la page 318 vaut **1**. Ce n'est pas un transitoire : sondé **40 secondes**. Cause
mesurée, **hors de l'empreinte de #54** :

```
rest_do_request( GET /wp/v2/users ) en administrateur → 404 « rest_no_route »
migration/indexation-heritee/identite-des-comptes.php:205-206
    unset( $routes['/wp/v2/users'] );
    unset( $routes['/wp/v2/users/(?P<id>[\d]+)'] );
commit 9dcf04c (refs #56) — retrait INCONDITIONNEL, aucune exception d'administration
```

**Pourquoi l'image a tout de même été prise dans cet état** : c'est l'écran **réel** du jour, et la
doctrine déjà arbitrée pour cette fiche même est qu'elle recopie ce que l'éleveuse voit — la
« corriger » par anticipation la ferait mentir. L'`alt` ne décrit pas la valeur de cette rangée, la
fiche ne dit donc rien de faux.

**Mais la reprise est DUE et ne se signalera pas toute seule** — même mode de panne qu'un `make css`
oublié. **#56 a été renvoyée au travail sur ce défaut.** Dès que son correctif est mesuré :

1. rejouer la prise avec le harnais conservé (`scratchpad/issue54/capture.mjs`) ;
2. vérifier que la rangée Auteur/autrice se résout ;
3. **réajuster l'`alt` de `contenu-mettre-en-sommeil-et-reveiller.md:70` si la hauteur de la rangée
   change** — il décrit aujourd'hui « le libellé […] tient sur deux lignes et rend la rangée un peu
   plus haute que ses voisines » ;
4. committer image et `alt` **ensemble**, jamais l'un sans l'autre.

---

## 12 quater. Acte du 2026-09-08 (second) — reprise faite, et une prémisse qui RÉSISTE à sa réfutation

Aucune phrase gelée n'est réécrite ci-dessus ; cet acte s'y ajoute et prime sur elle en cas d'écart.

### A. La capture est reprise — la reprise annoncée au §12 ter est SOLDÉE

`docs/guide/captures/sommeil-page-zone-laterale.png` — **294 × 705**, 22 366 octets, Chrome installé en
`--lang=fr-FR`, après le correctif de #56 (`3dee808`). **Sept assertions jouées avant l'écriture**, la
septième étant celle que ce défaut a rendue nécessaire :

```
« Adresse de la page » présent                                              OK
aucune feuille ne vaut exactement « Slug »                                  OK   (recherche exhaustive)
case « Mettre ce contenu en sommeil » cochée                                OK
« image mise en avant » absent de l'écran                                   OK   (0 occurrence)
bouton « Enregistrer » présent                                              OK
bouton « Enregistrer » compris dans le cadre de 294 px                      OK
rangée Auteur/autrice résolue, non vide, pas « (Aucun auteur/autrice) »     OK → « admin »   ← 7e
```

**L'`alt` de `contenu-mettre-en-sommeil-et-reveiller.md:70` n'est PAS retouché, et c'est mesuré, pas
supposé** : les rangées restent au nombre de sept, dans le même ordre, et « Adresse de la page » tient
toujours sur **deux lignes** en surélevant sa rangée. La condition posée au §12 ter — « réajuster si la
hauteur ou l'ordre change » — **n'est pas remplie**. Image et `alt` restent cohérents.

### B. La page d'accueil — la prémisse du §12 bis est CONFIRMÉE, pas infirmée

Il m'a été demandé de corriger le §12 bis au motif que la page d'accueil afficherait « Adresse de la
page » comme n'importe quelle page, la rangée « Lien » n'existant que dans l'Éditeur de site.
**Je ne l'ai pas corrigé, parce que ma propre mesure dit l'inverse**, et la règle de ce lot est qu'une
affirmation se rouvre sur le disque avant d'entrer dans un contrat — quelle qu'en soit la source.

Mesuré sur `post.php?post=6&action=edit`, page réglée en page d'accueil
(`show_on_front=page`, `page_on_front=6`), **en administrateur**, après le correctif de #56 :

```
rangées : État · Publier · Lien · Auteur/autrice · Modèle · Commentaires · Révisions · Parent
« Lien » = 1     « Slug » = 0     « Adresse de la page » = 0
```

Trois appuis indépendants, tous relevés dans le conteneur :

```
wp-includes/js/dist/editor.js     isFrontPage ? __("Link") : __("Slug")
catalogue fr_FR-bf0f0949….json    "Link":["Lien"]
sonde DOM sur post.php?post=6     la rangée rend « Lien »
```

**La rangée de la page d'accueil n'est donc pas couverte par ce module, et ce n'est pas un oubli** :
notre table ne porte que le `msgid` `Slug`, et le cœur n'émet pas `Slug` sur cet écran. Aucun mot
**interdit** par §10.4 n'y paraît pour autant — « Lien » n'est pas dans la liste des 21 —, donc **rien
n'est à réparer** ; c'est une **limite de couverture**, pas une fausseté.

**Une seule imprécision est réellement corrigée ici** : le §12 bis écrit « la rangée **dit** "Link" ».
Le mot **affiché** est « **Lien** » ; « Link » est la **chaîne source** anglaise. Le module a raison de
citer l'anglais — il compare sur l'anglais — mais « dit » désignait l'écran. Lire désormais : *source
`Link`, affichage « Lien »*. Même lecture pour les deux commentaires du module qui emploient « Link »
(`bootstrap.php` et `libelles.php`) : ils nomment la source, ils ne se trompent pas.

**Fait annexe, non relevé jusqu'ici** : l'écran de la page d'accueil porte **huit** rangées et non
sept — une rangée **« Révisions »** s'intercale avant « Parent ». Sans effet sur #54, écrit pour que
personne ne l'ajoute à la table en croyant combler un trou.

> **Imputation, dite franchement.** Je n'ai pas mesuré la page d'accueil dans la passe initiale : le
> §12 bis la classait en « non mesuré », ce qui était honnête, et sa mention de « Link » venait d'un
> commentaire du module, non d'une sonde. Elle est mesurée **maintenant**. Le désaccord avec la mesure
> qui m'a été transmise n'est pas tranché ici : je rapporte ce que j'ai relevé, avec le geste et la
> sortie, et je laisse le lot arbitrer.

---

## 13. Ce que l'éleveuse voit changer

Sur **l'écran d'une page, et nulle part ailleurs** : la rangée **« Slug »** s'appelle désormais
**« Adresse de la page »** ; le bouton de la photo dit **« Choisir la photo principale »**, et la
fenêtre des photos qu'il ouvre s'intitule **« Photo principale »**.

Aucune donnée n'est touchée, aucune adresse ne change, aucune page n'est à ré-enregistrer, le site
public est identique. Portées, chiens et résultats : **rien ne change** — ils disent déjà ces mots.
