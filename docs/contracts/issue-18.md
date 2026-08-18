# Contrat d'interface — Issue #18 — Navigation et plan de site

**Version 1, écrite sur le disque avant toute ligne de code.** Labels `doc`, `theme`, `feature` ·
milestone « 7. Gabarits et thème ».

Les points encore ouverts sont marqués **OUVERT** et datés. Un contrat gelé incomplet vaut mieux
qu'un contrat parfait resté dans un contexte : trois agents de la chaîne #38 ont disparu sans rien
rendre, et seul ce que la chaîne avait écrit de sa propre main a survécu.

Cette issue ne touche **que le thème `mtb`**. Aucun fichier de `mtb-core` n'entre dans son empreinte,
donc **aucun contrat back n'est à réconcilier** : il n'y a pas deux plans à faire converger, il y a
une frontière à ne pas franchir. C'est cette frontière que le présent document gèle.

---

## 1. Empreinte fichiers — la seule protection du projet

Écriture autorisée, et rien d'autre :

| Fichier | État | Rôle |
|---|---|---|
| `wp-content/themes/mtb/parts/header.html` | **neuf** | en-tête : lien d'évitement, nom de l'élevage, navigation |
| `wp-content/themes/mtb/parts/footer.html` | **neuf** | pied de page : coordonnées, plan du site, mentions |
| `wp-content/themes/mtb/functions.php` | **ajouts seulement** | emplacement de navigation, capacité T1 |
| `wp-content/themes/mtb/templates/search.html` | **neuf** | dette T3 |
| `wp-content/themes/mtb/templates/404.html` | **neuf** | dette T3 |
| `wp-content/themes/mtb/assets/css/entete-pied.css` | **neuf** | habillage §7.3 — extension d'empreinte accordée |
| `wp-content/themes/mtb/templates/index.html` | **insertion minimale** | les deux `wp:template-part`, **rien d'autre** |
| `wp-content/themes/mtb/templates/singular.html` | **insertion minimale** | idem |
| `docs/contracts/issue-18.md` | ce fichier | |
| `docs/guide/menu-modifier-le-menu.md` | **neuf** | D3, BRIEF §13.1 |

**Interdits absolus**, chacun appartenant à une autre issue ou à une autre chaîne :
`theme.json` · `assets/css/base.css` · `assets/css/tokens.css` · `assets/css/editor.css` ·
`assets/css/blocs/**` · `assets/js/**` · tout autre `templates/*.html` · `patterns/**` ·
tout fichier de `wp-content/plugins/mtb-core/`.

### 1.1 Les deux extensions d'empreinte, et leur raison

Accordées par l'orchestrateur le 2026-08-18, après vérification des fichiers.

**`assets/css/entete-pied.css` (neuf) + sa mise en file dans `functions.php`.** Sans elle, l'issue
livre du balisage juste et **non habillé** (voir §2). Un argument décisif s'ajoute à ceux de la
chaîne : `mtb_feuilles_de_blocs()` itère **le registre des blocs, jamais le disque**
(`functions.php:189-194`, qui l'écrit et interdit `glob()` / `scandir()`). Déposer un fichier dans
`assets/css/blocs/` n'habille donc **qu'un bloc enregistré** — or un en-tête et un pied de page ne
sont pas des blocs. Une feuille dédiée avec sa propre mise en file n'est pas un contournement : c'est
la **seule forme** que le mécanisme du thème autorise.
Mise en file à côté des appels existants (`functions.php:180-181`), avec `array( 'mtb-jetons' )` en
dépendance, comme `mtb-base`.

**`templates/index.html` et `templates/singular.html`, insertion minimale.** Vérification de
l'orchestrateur sur les empreintes de l'epic : #16 prend `single-portee`, `single-chien`,
`archive-portee`, `archive-chien` ; #17 prend `front-page` et les gabarits `page-*`. **Aucune des
deux ne possède `index.html` ni `singular.html`.** Le trou signalé par la chaîne n'appartenait donc à
personne, et le router vers #16/#17 revenait à ne le router nulle part : le site aurait eu un en-tête
et un pied de page rendus **uniquement** sur la recherche et le 404 — un échec invisible, dont tout
le mesurable passe.

> **Insertion minimale, au sens strict** : les deux `wp:template-part`, et **rien d'autre**. Aucun
> réagencement, aucun `wp:group` touché, et le commentaire provisoire en tête de `index.html` est
> **laissé mot pour mot** — il annonce que l'epic Gabarits réécrit le fichier, et #17 le fera.

**Refusé, et la raison de la chaîne est la raison du refus : aucun JavaScript.** `MASTER.md` §7.3
veut le panneau visible par défaut dans le HTML et replié par le JS, **précisément pour qu'une panne
de JS laisse le menu entier**. Un menu entièrement visible et navigable au clavier à toute largeur
n'est donc pas une version dégradée du §7.3 : c'est **son repli, livré en permanence**. Le zéro
JavaScript public est un fait mesuré du projet et ne se dépense pas dans une issue de navigation.

**Deux conséquences à tenir ensemble** : la feuille de style ne doit **jamais** cacher une entrée de
menu derrière une interaction qui n'existe pas ; et 360 px doit rester sans défilement horizontal,
**toutes les entrées visibles**. Si ces deux exigences se combattent, la chaîne le remonte — elle ne
tranche pas en cachant quelque chose.

**La feuille se construit à partir de `tokens.css` uniquement, sans valeur brute**, et suit la
décision 30 : une primitive nommée par `MASTER.md` s'écrit en classe nue ; seule une surcharge de
contexte se scope sous la classe du bloc.

`functions.php` n'est rouvert **que** par #2 puis par #18 (décision 9). C'est le seul passage de cette
issue : les ajouts se font **en fin de fichier**, sans reformatage d'une ligne existante.

---

## 2. Le fait qui commande toute cette issue

> **`theme.json` verrouille l'intégralité des réglages visuels du cœur. Aucun bloc posé dans
> `parts/header.html` ou `parts/footer.html` ne peut porter la moindre couleur, marge, taille ou
> graisse.**

Relevé le 2026-08-18 dans `wp-content/themes/mtb/theme.json`, `settings` :

```
color.palette        = []        color.custom          = false
color.background     = false     color.text            = false
color.link           = false     color.heading         = false
color.gradients      = []        color.duotone         = []
spacing.margin       = false     spacing.padding       = false
spacing.units        = []        spacing.spacingSizes  = []
typography.*         = false sur toutes les clés
```

`color.palette` étant **vide**, il n'existe **aucun slug** — ni `pin`, ni `calcaire`, ni `sauge` — à
poser en `backgroundColor` ou `textColor` sur un bloc. `color.custom = false` ferme aussi la valeur
brute, que `MASTER.md` §13 interdirait de toute façon.

**Conséquence directe et non négociable** : tout l'habillage du thème passe par `assets/css/`, comme
#2 l'a construit et comme la décision 28 le formule. Le balisage de cette issue ne peut donc porter
que **de la structure et des crochets de classes**.

Ce que `MASTER.md` §7.3 exige et qui est, de ce fait, **hors de l'empreinte livrée** : en-tête sur
fond `--calcaire` avec filet double en bord bas · pied de page sur fond `--pin`, liens
`--laiton-clair`, texte secondaire `--calcaire-ombre` · plan du site en trois colonnes · cibles
≥ 44 px · panneau de navigation replié sous `--bp-nav`, qui demande en plus un fichier JavaScript.

**TRANCHÉ le 2026-08-18** : l'orchestrateur accorde `assets/css/entete-pied.css` (voir §1.1) et
refuse tout JavaScript. Le §7.3 devient donc atteignable **sauf** son panneau replié, qui est
volontairement remplacé par son propre repli : un menu entièrement visible, à toute largeur.

### 2.1 Les crochets de classes gelés

Ils sont **stables quelle que soit l'issue A/B/C** : c'est le contrat entre ce balisage et la feuille
qui l'habillera, ici ou ailleurs.

| Classe | Portée |
|---|---|
| `mtb-entete` | conteneur de l'en-tête de site |
| `mtb-entete__nom` | nom de l'élevage, à gauche |
| `mtb-nav` | conteneur de la navigation principale |
| `mtb-pied` | conteneur du pied de page |
| `mtb-pied__coordonnees` | zone portant le composant de coordonnées |
| `mtb-plan-du-site` | plan du site — **et l'aiguillage vers le second emplacement de menu, voir §4.6** |
| `mtb-pied__mentions` | ligne des mentions légales et du copyright |
| `mtb-liens-de-secours` | les liens de recours de `404.html` et `search.html` (§9.5) |

Aucune de ces classes n'est habillée par `base.css` aujourd'hui — vérifié, zéro occurrence.

> **`mtb-plan-du-site` n'est pas qu'un crochet de style.** Le §4.6 s'en sert pour décider **quel
> menu** un bloc Navigation rend. La renommer casserait le pied de page **en silence** : il
> retomberait sur le menu principal et afficherait deux fois la même liste, sans erreur ni
> avertissement. À ne toucher qu'en connaissance de cause.

---

## 3. Le pied de page ne recopie AUCUNE coordonnée

Gelé par `docs/contracts/issue-38.md` §10, écrit explicitement pour cette chaîne. Repris ici parce
qu'il s'agit du point le plus important de l'issue.

`parts/footer.html` insère **exactement** ceci, et rien d'autre, pour les coordonnées :

```html
<!-- wp:mtb/coordonnees-plan /-->
```

Une instance **nue, sans un seul attribut**. Le filtre `block_type_metadata` du module
(`includes/blocks/coordonnees-plan/bootstrap.php:98-118`) recharge les défauts **à chaque requête**
depuis l'option centrale `mtb_core_coordonnees` : le pied de page suit donc toute évolution de
l'écran « Coordonnées de l'élevage » **sans PHP, sans recopie et sans qu'aucune page soit rouverte**.

### 3.1 Interdits, formellement

- **Aucun `patterns/*.php`** qui exécuterait `<?php echo mtb_get_telephone_elevage(); ?>`. Un motif
  de thème produit du balisage de blocs **figé au moment de l'insertion** : la valeur serait gravée
  dans la page enregistrée, et ce serait exactement le défaut de recopie que #38 existe pour
  supprimer. `patterns/**` est de toute façon hors empreinte.
- **Le thème n'appelle jamais** `mtb_get_telephone_elevage()`, `mtb_get_page_contact()` ni
  `mtb_get_coordonnees_elevage()`. La frontière de `CLAUDE.md` l'interdit : le thème n'interroge
  jamais la base et ne compose jamais une chaîne métier. Que ces fonctions existent désormais
  réellement (#38 a été implémentée et commitée) **ne change rien** à cette règle.
- **Aucune coordonnée n'est écrite en clair** dans `parts/footer.html` : ni adresse, ni numéro, ni
  courriel. `MASTER.md` §7.3 les recopie dans sa prose ; c'est un quatrième exemplaire du numéro dans
  le dépôt, signalé par #38 §16 à `lead-design-mtb`, et ce n'est **pas** une consigne de balisage.

### 3.2 Manque signalé, non résolu ici

`assets/css/blocs/mtb-coordonnees-plan.css` habille le composant pour un fond **clair**
(`--laiton-texte` sur `--calcaire`, 5,30:1, règle 3 de la feuille). `MASTER.md` §7.3 veut un pied de
page sur `--pin`. Les deux ne tiennent pas ensemble.

Si le pied de page doit porter un balisage **différent** de la liste de définitions du composant,
ce balisage appartient à `mtb-core` et **non au thème** : ce serait un **composant neuf**, hors du
périmètre de #18 et hors de celui de #38, exactement comme `docs/contracts/issue-38.md` §10.1
l'anticipe. **La chaîne le signale et ne le résout pas.**

> **Piège posé pour qui peindra le pied de page en `--pin`.** `mtb-coordonnees-plan.css` habille son
> étiquette en `--laiton-texte` sur `--calcaire` — **5,30:1, mesuré et documenté par le contrat #11**.
> Ce nombre **ne survit pas** à un déplacement sur `--pin` : le couple change des deux côtés à la
> fois, l'encre restant claire sur un fond devenu sombre. Aucune règle de `entete-pied.css` ne doit
> tenter de rattraper le contraste **à l'intérieur** du composant : ce serait le thème qui réhabille
> un composant du catalogue, et la divergence tomberait en silence à la première évolution de la
> feuille du bloc.
>
> **Conséquence gelée pour cette issue** : la zone `mtb-pied__coordonnees` reste sur fond **clair**,
> même à l'intérieur d'un pied de page dont le reste porte `--pin`. Le jour où un composant de pied
> de page naîtra dans `mtb-core`, il portera son propre habillage sombre et cette exception
> disparaîtra.

---

## 4. L'emplacement de navigation et la capacité — dette T1

Tout ce qui suit a été **lu dans les sources du cœur de la stack** (WordPress **6.9**, conteneur
`wordpress`), pas de mémoire. Chaque affirmation porte son fichier et sa ligne.

### 4.1 `Apparence > Menus` existe bien dans un thème de blocs — fait vérifié

`wp-admin/menu.php:247-249`, WP 6.9, **verbatim** :

```php
if ( current_theme_supports( 'menus' ) || current_theme_supports( 'widgets' ) ) {
	$submenu['themes.php'][10] = array( __( 'Menus' ), 'edit_theme_options', 'nav-menus.php' );
}
```

**Aucune garde `wp_is_block_theme()`** sur cette ligne — contrairement à `Personnaliser`
(`:243`, masqué sur un thème de blocs) et à `Éditeur` (`:227-228`, ajouté seulement sur un thème de
blocs). `register_nav_menu()` appelant `add_theme_support( 'menus' )`, **l'enregistrement de
l'emplacement suffit à faire apparaître l'écran**. La promesse du corps de l'issue tient.

`wp-admin/nav-menus.php:23` refuse l'accès à qui n'a pas `edit_theme_options`.

### 4.2 Le piège qui rendrait cet écran décoratif — fait vérifié, et il commande la conception

Un `wp:navigation` **sans `ref` et sans blocs internes** retombe sur
`block_core_navigation_get_fallback_blocks()` (`wp-includes/blocks/navigation.php:296-314, 1054`),
qui appelle `WP_Navigation_Fallback::get_fallback()`.

`wp-includes/class-wp-navigation-fallback.php:70-99` : si aucun contenu `wp_navigation` n'existe,
`create_classic_menu_fallback()` (`:139-171`) **convertit le menu classique en un contenu
`wp_navigation` et l'enregistre en base, une seule fois**. À partir de cet instant,
`get_most_recently_published_navigation()` (`:110`) rend ce contenu et **le menu classique n'est plus
jamais relu**.

> **Conséquence, si on ne fait rien** : Fabienne modifie son menu dans `Apparence > Menus`, la page
> publique ne change pas, et **rien à l'écran ne le lui dit**. C'est le pire défaut que ce projet
> puisse produire — un écran qui répond, un site qui ment.

### 4.3 La parade gelée — le menu classique reste la source d'autorité, à chaque requête

Deux filtres, dans `functions.php` :

1. **`wp_navigation_should_create_fallback` → `false`.** Point d'entrée officiel, documenté
   `@since 6.3.0` au-dessus de son `apply_filters` (`class-wp-navigation-fallback.php:70-77`). Il
   empêche la création du contenu `wp_navigation`, donc la conversion unique du §4.2.
2. **`block_core_navigation_render_fallback`** (`navigation.php:1088-1098`) : la chaîne y rend les
   blocs **reconstruits à chaque requête** depuis le menu classique assigné à l'emplacement, par
   `WP_Classic_To_Block_Menu_Converter::convert( $menu )` —
   `wp-includes/class-wp-classic-to-block-menu-converter.php:26`, **`public static`**, donc API
   appelable et **non dépréciée** (les fonctions `block_core_navigation_get_classic_menu_fallback*()`
   le sont depuis 6.3 et émettraient un `_deprecated_function` : elles ne sont **pas** employées).

Résultat : `Apparence > Menus` est lu **à chaque rendu**, une modification est visible immédiatement,
et aucun contenu `wp_navigation` n'est créé dans le dos de l'éleveuse.

**Condition d'existence** : le `wp:navigation` de `parts/header.html` ne porte **ni `ref`, ni blocs
internes** — c'est ce qui déclenche la voie de repli. En poser un seul désactiverait tout le
mécanisme, en silence.

**Garde nécessaire** : si un contenu `wp_navigation` existe déjà en base, il gagne (`:80-83`) et la
parade est sans effet. À vérifier dans la stack avant de conclure, et à dire dans le guide.

### 4.4 Zéro JavaScript — attributs gelés du bloc Navigation

`navigation.php:58` : `is_responsive()` est vrai dès que `overlayMenu` est absent **ou** différent de
`'never'`. `navigation.php:110-112` : le bloc devient interactif si
`( $has_submenus && ( $openSubmenusOnClick || $showSubmenuIcon ) ) || $is_responsive_menu`, et
`:630` n'appelle `wp_enqueue_script_module( '@wordpress/block-library/navigation/view' )` que dans ce
cas.

Attributs **gelés**, qui rendent la condition fausse en toutes circonstances :

```json
{"overlayMenu":"never","openSubmenusOnClick":false,"showSubmenuIcon":false}
```

`overlayMenu: "never"` sert deux fins d'un coup : il supprime le module de vue **et** il supprime le
menu replié derrière un bouton, que `MASTER.md` §7.3 interdit en toutes lettres.

> **Piège documenté, et il est réel** : sans `showSubmenuIcon: false`, il suffirait que Fabienne crée
> une **sous-entrée** dans `Apparence > Menus` pour que `$has_submenus` devienne vrai et que le
> premier octet de JavaScript public du projet apparaisse — sans qu'elle ait rien demandé, et sans
> qu'aucune vérification de cette issue puisse le voir.
>
> **Une garantie de zéro JavaScript qu'un geste d'éditrice peut révoquer n'est pas une garantie.**
> C'est la raison d'être de `showSubmenuIcon: false`, et elle est plus importante que celle
> d'`overlayMenu: "never"` : la seconde ferme une porte que personne n'ouvrira, la première ferme une
> porte qu'un usage parfaitement légitime aurait ouverte.

**Limite assumée et signalée** : avec ces attributs, un menu à **deux niveaux** n'a plus de mécanisme
d'ouverture. Cette issue livre donc un menu **à un seul niveau**, la fiche d'aide le dit, et le
sous-menu part en manque routé (§13). Il n'est **pas** remplacé par un survol : un sous-menu au
survol seul est inutilisable au doigt.

### 4.5 La capacité — ce qu'elle ouvre, ce qu'on referme

`wp-admin/menu.php:207` : `$appearance_capability = current_user_can( 'switch_themes' ) ?
'switch_themes' : 'edit_theme_options';`. Le rôle Éditeur n'a **ni l'une ni l'autre** : le menu
`Apparence` lui est aujourd'hui **entièrement invisible**.

Un `add_cap( 'edit_theme_options' )` nu sur le rôle Éditeur ouvrirait donc **d'un coup** :

| Ouvert par ricochet | Fichier |
|---|---|
| le menu `Apparence` lui-même | `menu.php:207-209` |
| `Apparence > Thèmes` (liste des thèmes ; l'activation reste fermée, elle exige `switch_themes`) | `menu.php:225` |
| **`Apparence > Éditeur` — l'éditeur de site entier : styles globaux, gabarits, parties de gabarit, compositions** | `menu.php:227-228` |
| `Apparence > Menus` — **le seul écran voulu** | `menu.php:247-249` |

**Décision gelée : la capacité n'est PAS écrite en base.** Deux raisons, la seconde décisive :

1. `add_cap()` écrit dans l'option `wp_user_roles` et **survit au changement de thème**. Un thème qui
   élargit durablement un rôle laisse derrière lui une permission que plus personne ne relie à sa
   cause — exactement le genre de panne muette que la décision 27 et la dette T5 documentent déjà.
2. Elle ouvre l'éditeur de site **en permanence**, et le retrait d'une entrée de menu ne referme
   rien : `remove_submenu_page()` masque un lien, il ne refuse pas une URL tapée ni un appel REST.

**Mécanisme retenu** : un filtre **`user_has_cap`** dans `functions.php`, qui accorde
`edit_theme_options` **uniquement sur la requête de l'écran des menus**, et uniquement à un compte
possédant déjà `edit_pages` — la capacité que `docs/contracts/issue-38.md` §7.1 a retenue pour
désigner « la personne qui tient les pages du site ». Rien n'est écrit en base ; la permission
disparaît avec le thème ; l'éditeur de site n'est ouvert à aucun moment, ni par le menu, ni par une
URL, ni par REST, puisque la capacité n'y est tout simplement pas accordée.

**Corollaire obligatoire, sans quoi le mécanisme est inutilisable** : sur toute requête *autre* que
l'écran des menus, `Apparence` reste invisible — donc **Fabienne n'a aucun chemin pour y arriver**.
La chaîne doit lui en fournir un, atteignable avec ses propres capacités. Le plan `leaddev-front-mtb`
tranche la forme exacte et la **vérifie à l'écran** ; le libellé est **Menus**, jamais un mot du
§10.4.

> **Avis au relecteur : la case de la checklist dit `add_cap`, et elle est pourtant tenue.**
> Le corps de l'issue nomme le mécanisme, pas l'objectif. L'objectif — « accorder cette capacité au
> rôle Éditeur **de façon ciblée**, sans lui ouvrir le reste de l'éditeur de site (styles globaux,
> gabarits) » — est **strictement mieux tenu** par `user_has_cap` que par `add_cap` : `add_cap`
> ouvre `Apparence > Éditeur` **en permanence** et ne peut pas le refermer, tandis que le filtre ne
> fuit vers aucun autre écran et ne laisse **aucun résidu** si le thème disparaît. Ratifié par
> l'orchestrateur le 2026-08-18. **Ne lisez pas cette case comme inachevée.**

**OUVERT (2026-08-18)** : la liste exacte des requêtes à couvrir (`nav-menus.php` et les actions
`admin-ajax` de cet écran), et la forme du point d'entrée. **Critère d'acceptation inchangé et non
négociable** : observé en session `fabienne` / `mtb-dev-editrice`, jamais en `admin`, jamais déduit
d'une table de capacités. Si Fabienne ne peut pas modifier son menu, l'issue n'est pas faite.

---

### 4.6 Deux emplacements, et l'aiguillage qui les distingue

`MASTER.md` §7.3 demande un **plan du site en trois colonnes** dans le pied de page. Il est donc rendu
par un **second** bloc Navigation — et c'est là qu'un défaut fonctionnel guettait, relevé à la
relecture du code livré : deux `wp:navigation` sans `ref` passent par **le même repli**, qui lisait un
emplacement unique. **Le pied de page affichait une copie exacte du menu principal.**

**Deux emplacements sont donc enregistrés**, tous deux éditables dans `Apparence > Menus` :

| Slug | Libellé lu par l'éleveuse | Rendu par |
|---|---|---|
| `principal` | **Menu principal** | le bloc Navigation de `parts/header.html` |
| `pied` | **Plan du site** | le bloc Navigation de `parts/footer.html`, classe `mtb-plan-du-site` |

Le filtre `block_core_navigation_render_fallback` ne reçoit **que** les blocs de repli : il ne peut pas
savoir quel bloc il sert. La résolution par instance passe donc par un crochet antérieur
(`render_block_data` / `pre_render_block` sur `core/navigation`) qui **mémorise l'emplacement courant
d'après la classe du bloc**. Défaut en cas d'ambiguïté : **`principal`**, jamais « rien ».

Chacun des deux suit la règle du §4.3 et la décision 26 : sans menu assigné, **le bloc ne s'affiche
pas au visiteur**. Un pied de page sans plan du site est un pied de page valide.

Conséquence heureuse pour l'issue : les liens « mentions légales » et « confidentialité » que la
checklist réclame ne sont **pas** écrits en dur dans le balisage. Fabienne les ajoute au **Plan du
site** depuis le même écran que son menu — c'est la règle d'or appliquée, et non contournée.

### 4.7 Une entrée qui vise un brouillon coupe la liste en deux — trouvé, corrigé, à connaître

Défaut relevé sur la page rendue pendant la relecture, **pas dans le code** : la navigation de
l'en-tête rendait **deux `<ul>` frères** dans un seul `<nav>`. Aucun lien n'était dupliqué —
2 `<ul>` mais 6 `<li>` et 6 `<a>` pour 6 entrées : la liste était **coupée**, pas répétée.

**Cause, lue dans le cœur — `wp-includes/blocks/navigation.php:183-208`, `get_inner_blocks_html()` :**
le cœur ouvre un `<ul>` au premier bloc interne dont le balisage contient un `<li>`, **le referme dès
qu'un bloc rend un balisage sans `<li>`**, puis en rouvre un neuf au suivant. Or un
`core/navigation-link` visant un contenu **non publiquement visible rend une chaîne vide**.

**Preuve par les données**, et non par la vraisemblance : le menu d'essai porte `16 Nous contacter ·
17 Contact · 18 Sample Page · 19 Espace privé · 20 Privacy Policy · 21 Accueil`, et **`Privacy Policy`
est la seule page en brouillon de la base**. La coupure tombait **exactement de part et d'autre de
l'entrée 20**. Le plan du site, dont les trois entrées sont toutes publiées, rendait **un seul `<ul>`**
sous le même filtre et la même requête — l'asymétrie s'explique entièrement par cette règle.

*Deux fausses pistes écartées en chemin, notées pour qu'on ne les reprenne pas : ce n'était ni une
duplication, ni un traitement particulier de l'entrée d'accueil. « Accueil » se retrouvait seul parce
qu'il était **le premier après le brouillon**, et il est de type `custom`, que le convertisseur ne
traite pas différemment.*

**Ce n'est donc ni le repli du §4.3, ni le convertisseur : c'est le cœur.** Notre repli est fidèle, il
convertit toutes les entrées. Et **le déclencheur est parfaitement ordinaire** : préparer une page en
brouillon tout en la posant déjà dans le menu.

**Correction gelée** : `mtb_entrees_du_menu_classique()` écarte, après `parse_blocks()`, les entrées
`kind: post-type` dont la cible n'est pas publiquement visible — **le critère même du cœur, appliqué
une étape plus tôt**. Rien n'est caché qui aurait été visible : ces entrées rendaient déjà une chaîne
vide. Les `custom`, `taxonomy` et `post-type-archive` sont **conservés tels quels** : le thème ne juge
pas ce qu'il ne sait pas juger.

**Le défaut réparé est structurel, pas éditorial.** Après correction : une navigation, une liste. Mais
ce que Fabienne vit ne change pas — elle pose une entrée vers une page qu'elle prépare, et l'entrée
n'apparaît pas, sans que rien ne le lui dise. C'est le comportement du cœur, et on ne le combat pas.
**La fiche d'aide le dit donc en une phrase**, dans ses mots et sans le mécanisme.

**Le critère est recopié des deux fonctions de rendu du cœur, pas d'une fonction qui y ressemble.**
`render_block_core_navigation_link()` (`navigation-link.php:172-203`) et
`render_block_core_navigation_submenu()` (`navigation-submenu.php:67-80`) rendent une chaîne vide sur
un libellé absent, et sur un contenu dont le statut n'est pas `publish`.

> **`is_post_publicly_viewable()` a été lue et écartée**, bien qu'elle paraisse faite pour cela.
> `wp-includes/post.php:2508-2519` la définit comme `is_post_type_viewable() &&
> is_post_status_viewable()` — **ce n'est pas le critère des deux blocs**. Sur un type de contenu non
> visible publiquement mais dont le contenu est publié, elle rend faux là où le cœur rendrait
> l'entrée : le thème aurait **masqué une entrée que Fabienne voyait**, soit l'inverse exact du but,
> et de façon invisible — une entrée manquante d'un menu, sous un commentaire affirmant suivre le
> cœur. Le nom d'une fonction n'est pas son contrat ; il a fallu ouvrir les trois fichiers.

**Couplage à surveiller, et c'est le genre qui dérive deux versions plus tard** : le cœur expose
depuis **6.8** le filtre `render_block_core_navigation_link_allowed_post_status`, qui peut **élargir**
les statuts rendus. Il n'est pas appliqué dans le tri — il attend un `WP_Block`, que le tri n'a pas.
**Si quelqu'un s'en sert un jour, la liste des statuts du tri doit suivre.**
`navigation-submenu.php`, lui, n'expose aucun filtre et fige `publish`.

Deux comportements vérifiés et voulus : une entrée déroulante écartée **emporte ses enfants**, comme
le cœur qui ne rend pas son sous-arbre ; et une entrée écartée n'est **jamais** remplacée ni signalée
au visiteur — le thème n'invente rien.

**#16 et #17 rendront de la navigation eux aussi : ils tomberont dessus.**

---

## 5. Structure du menu livré par défaut

**Q6 est tranchée par l'orchestrateur : pas d'entrée « Actualités ».** Les nouvelles restent sur
l'accueil, comme aujourd'hui. Raison : #18 rend justement le menu modifiable depuis l'administration,
donc ajouter cette entrée plus tard ne coûtera **aucune ligne de code**, alors qu'une entrée livrée
maintenant pointerait dans le vide jusqu'à ce que #17 livre la page.

Le volet « tarifs des chiots » de Q6 **ne concerne pas cette issue** : rien dans l'empreinte n'affiche
un prix. Il n'est ni traité, ni mentionné dans le guide.

**OUVERT (2026-08-18)** — la liste exacte. Contrainte dure : **aucune Page n'existe encore** et les
index de #16/#17 ne sont pas livrés. Une entrée qui pointe dans le vide est un lien mort livré à
l'éleveuse. Les libellés, eux, sont figés par `MASTER.md` §10.3 dès que la destination existera :
**Toutes les portées** · **La meute** · **Nous contacter**.

---

## 6. Lien d'évitement — dette T2

Écrit **à la main** dans `parts/header.html`, en **tout premier** élément.

| Élément | Valeur gelée |
|---|---|
| Libellé | **Aller au contenu** (`MASTER.md` §10.3, verbatim) |
| Classes | `skip-link screen-reader-text` |
| Cible | `#contenu` |

Le lien du cœur, injecté par script depuis WP 6.4, est écarté : il dépend du JavaScript et vise un
`<main>` non focalisable. La cible doit donc porter **`tabindex="-1"`**.

### 6.1 La cible focalisable — posée au rendu, pas dans le balisage

`404.html` et `search.html` écrivent un `<main>` simple et portent l'attribut directement.
`index.html` et `singular.html`, eux, encapsulent leur `<main>` dans un `wp:group`
(`"tagName":"main"`, `"anchor":"contenu"`) : **y ajouter `tabindex="-1"` dans le balisage enregistré
provoquerait une erreur de validation de bloc** dans l'éditeur de site, le HTML sauvegardé ne
correspondant plus à ce que le bloc sait produire.

L'attribut est donc posé **au rendu**, depuis `functions.php`, avec `WP_HTML_Tag_Processor` — jamais
par une expression régulière sur du HTML. Trois raisons, dans l'ordre d'importance :

1. Le balisage enregistré reste **valide dans l'éditeur**.
2. La garantie devient **uniforme sur tout le site**, au lieu d'être vraie sur deux gabarits d'erreur
   et fausse partout ailleurs. **Cette asymétrie-là était le vrai danger** : un test clavier passé sur
   la page 404 aurait réussi sans rien prouver.
3. Les gabarits que **#16 et #17** écriront en héritent **sans avoir à y penser** — et c'est
   décisif, puisque `index.html` et `singular.html` n'appartiennent à **aucune** issue de l'epic
   (#16 prend `single-*` et `archive-*`, #17 prend `front-page` et `page-*`). Une convention écrite
   dans un contrat n'aurait été payée par personne.

Le CSS est **déjà livré** par #2 (`base.css`, section 6, sélecteur
`.skip-link.skip-link.screen-reader-text:focus`, spécificité doublée pour battre la feuille en ligne
du cœur). Rien à écrire de ce côté.

---

## 7. `search.html` et `404.html` — dette T3

Les deux fichiers sont **neufs**. Ils ne sont couverts par aucune autre issue de l'epic Gabarits :
l'accueil de production sera une Page, donc couvert par #17, mais ni la recherche ni le 404 ne le
sont.

**Le `<h1>` est un `wp:heading`, jamais un `wp:query-title {"type":"archive"}`** : ce dernier ne rend
**rien** sur l'index du blog ni sur la recherche, ce qui est la dette T3 elle-même.

Libellés figés par `MASTER.md` §9.5 et §10.3 :

| Gabarit | `h1` | Contenu |
|---|---|---|
| `404.html` | **Page introuvable** | un paragraphe, un champ de recherche, trois liens |
| `search.html` | **Aucun résultat pour « … ». (état vide)** | les résultats, sinon les mêmes trois liens |

Les trois liens du §9.5 sont **Accueil**, **Les portées**, **La meute**.

### 7.1 Le terme cherché — fait vérifié, et l'écart qui en découle

`wp-includes/blocks/query-title.php:44-54` : `core/query-title` **rend bien quelque chose sur la
recherche** quand `type` vaut `"search"` — la dette T3 ne vise que `type="archive"`, qui ne rend rien
ni sur l'index du blog ni sur la recherche. Avec `showSearchTerm: true`, il interpole le terme.

Chaînes réellement rendues, lues dans la stack en `fr_FR` (`wp eval` sur `__()`), pas traduites de
mémoire :

| Attributs | Rendu français |
|---|---|
| `type: "search"`, `showSearchTerm: true` | **Résultats de recherche pour « … »** |
| `type: "search"`, `showSearchTerm` absent | **Résultats de recherche** |

**Écart signalé, non comblé.** `MASTER.md` §9.5 fige « **Aucun résultat pour « … ».** » pour le cas
**sans résultat**. Or `wp:query-no-results` ne contient que du contenu statique : **aucun bloc du
cœur n'interpole le terme cherché à l'intérieur de l'état vide**, et le faire demanderait du PHP —
donc un composant, donc `mtb-core`. Deux conséquences tenues :

1. Le terme cherché est porté par le `h1`, où le cœur sait l'interpoler.
2. La phrase de l'état vide est livrée **sans le terme**, et l'écart part au rapport. Elle **n'est pas
   reformulée** pour faire semblant : décision 39, un libellé de §10 ne s'amende pas depuis une chaîne.

**Arbitré par l'orchestrateur le 2026-08-18** : on livre la phrase **sans le terme**, et on
n'**invente aucune variante** — une variante mettrait sur le site une formule que `MASTER.md` n'a
jamais approuvée. **Et on n'écrit aucune cale PHP dans cette issue** pour la rattraper : ce serait un
composant, donc `mtb-core`, donc une autre issue. L'écart part en question pour `lead-design-mtb`,
à côté du trou de vocabulaire déjà signalé par #38 §16.

**OUVERT (2026-08-18)** — les destinations « Les portées » et « La meute » n'existent pas encore.
Voir §5.

Ni humour, ni illustration, ni coche verte : §9.5 est explicite.

---

## 8. Zéro JavaScript public

`docs/ETAT.md` fait du **zéro octet de JavaScript public** un fait mesuré aux lots 3 et 4. Cette issue
ne le casse pas sans arbitrage explicite.

Conséquence sur le bloc Navigation du cœur : sa configuration doit être choisie de façon à
**n'enqueuer aucun module de vue**. Et `MASTER.md` §7.3 est catégorique par ailleurs — *« on ne cache
jamais un menu en attendant une interaction »* : un menu replié derrière un bouton qui n'ouvre que par
JavaScript est **interdit**, pas seulement coûteux.

Le repli acceptable, et c'est celui que §7.3 décrit lui-même, est un menu **entièrement visible dans
le HTML**. Sans CSS ni JS dans l'empreinte, c'est aussi le seul atteignable.

---

## 9. États spéciaux

| État | Émis par | Rendu attendu |
|---|---|---|
| `menu_vide` | aucune entrée enregistrée | à établir — **jamais** une liste de pages inventée par le thème |
| `page_courante` | le cœur | `wp-includes/blocks/navigation-link.php:226,239` émet **`aria-current="page"` et la classe `current-menu-item`** sur l'entrée active — vérifié. La feuille accroche l'un des deux et pose le trait bas `--sauge` 3 px + graisse 600 (§8.3) |
| `recherche_sans_resultat` | `search.html` | « Aucun résultat pour « … ». » + les trois liens (§9.5) |
| `page_introuvable` | `404.html` | « Page introuvable » + paragraphe + recherche + trois liens |
| `coordonnees_vides` | option centrale vidée par l'éleveuse | le composant ne s'affiche pas au visiteur (#38 §8) ; le pied de page **ne comble rien** |
| `sans_javascript` | toujours, aujourd'hui | le menu reste entièrement visible et navigable au clavier |

---

## 10. Interdits

- Le thème n'interroge **jamais** la base directement et n'appelle **aucune** fonction `mtb_get_*`.
- Le thème ne compose **jamais** une chaîne métier ni ne reformate une coordonnée.
- **Aucune coordonnée recopiée** dans `parts/footer.html`.
- **Aucun `patterns/*.php`** exécutant du PHP pour composer du balisage (#38 §10).
- **Aucune entrée de menu codée en dur.**
- `functions.php` reçoit des **ajouts** ; pas une ligne existante n'est reformatée.
- **Aucun fichier hors de l'empreinte du §1 n'est touché**, même d'un octet, même pour corriger une
  erreur constatée : la constatation part au rapport.
- Aucun libellé de `MASTER.md` §10 n'est reformulé — décision 39.
- Aucun mot de `MASTER.md` §10.4 n'atteint l'écran : ni `template`, ni `responsive`, ni `slug`, ni
  `permalien`, ni `breakpoint`, ni `hero`.
- Aucune donnée d'élevage n'est inventée. Une incertitude est une question remontée, jamais un trou
  comblé.

---

## 11. Vérifications à observer, jamais à déduire

Dans la stack, port **3005**, WP-CLI par `make wp` et jamais par un `docker compose run wpcli` nu
(décision 34). `php -l` **n'est pas disponible** : PHP n'est pas installé sur l'hôte, toute
vérification de syntaxe passe par le conteneur.

1. **T1, en session `fabienne` / `mtb-dev-editrice`** — pas en `admin`. L'écran qui modifie le menu
   est visible, ouvrable, une modification aboutit, et ce que la capacité ouvre par ricochet est
   **constaté à l'écran**, pas déduit d'une table.
2. **Navigation au clavier complète** : chaque entrée atteignable, anneau de focus visible (§8.1),
   ordre de tabulation cohérent, lien d'évitement en premier arrêt et **qui déplace réellement le
   focus**.
3. **360 px sans défilement horizontal**, sur l'en-tête et sur le pied de page. Attention : Chrome
   sans interface ne descend pas sous ~500 px de large — la mesure passe par une iframe, sinon la
   capture ment.
4. **Zéro requête vers un domaine tiers** sur une page portant l'en-tête et le pied de page.
5. **Zéro octet de JavaScript public** ajouté, ou l'écart nommé.
6. **Aucun diagnostic PHP** sur les pages rendues. `WP_DEBUG` vaut `true` sur les requêtes web et
   `false` en WP-CLI (décision 29) : une affirmation « aucune notice » ne vaut que **mesurée sur une
   page rendue**.

---

### 11.1 Résultats observés le 2026-08-19 — mesurés, pas déduits

Relevés par la chaîne elle-même sur la stack (port 3005), en plus de ceux des agents.

| Vérification | Résultat |
|---|---|
| Accueil, `/contact/`, `/?s=…` | **200** ; URL inexistante **404** |
| Diagnostics PHP sur les quatre pages **rendues** | **0** (décision 29 : la mesure ne vaut que sur le web) |
| `<script src>` sur une page portant en-tête et pied de page | **0** — zéro octet de JavaScript public |
| Origines tierces | **0** |
| Feuilles servies | `mtb-jetons`, `mtb-base`, **`mtb-entete-pied`** |
| `entete-pied.css` dans la toile de l'éditeur | **présente** (`$GLOBALS['editor_styles']`) |
| Emplacements enregistrés | `principal` → « Menu principal », `pied` → « Plan du site » |
| Navigation de l'en-tête | **1 `<ul>`, 5 `<li>`, 5 `<a>`** après correction du §4.7 |
| Plan du site | **1 `<ul>`, 3 `<li>`** |
| `aria-current="page"` sur `/contact/` | **1 occurrence**, sur la bonne entrée |
| `h1` de la recherche | « Résultats de recherche pour « zzzznresultat » » |
| `h1` du 404 | « Page introuvable » · état vide : « Aucun résultat pour « … ». » |
| `tabindex="-1"` + `id="contenu"` sur l'accueil (servie par `index.html`) | **présents** |

**Dette T1, en session `fabienne` / `mtb-dev-editrice` — jamais en `admin` :**

| Écran | Résultat |
|---|---|
| `nav-menus.php` | **200**, « Structure du menu » présent |
| `site-editor.php` | **403** |
| `themes.php` | **403** |
| `options-general.php` | **403** (inchangé) |
| Barre latérale | Tableau de bord · Articles · Médias · Pages · Portées · Chiens · Résultats de travail · Coordonnées · **Menus** · Profil · Outils — **`Apparence` absente** |

**La promesse centrale de l'issue, démontrée et non affirmée** : une entrée ajoutée au menu apparaît
**immédiatement** sur la page publique, et `wp post list --post_type=wp_navigation` reste à **0**.
C'est le §4.2 désarmé, mesuré de bout en bout.

---

## 12. Arbitrages

| # | Question | Décision | Raison |
|---|---|---|---|
| 1 | Entrée « Actualités » dans le menu livré ? | **non** | Tranché par l'orchestrateur. Le menu devient modifiable sans code ; l'ajouter plus tard ne coûte rien, la livrer maintenant crée un lien mort. |
| 2 | Le pied de page recopie-t-il les coordonnées ? | **jamais** | #38 §10. Une instance nue de `mtb/coordonnees-plan`, dont les défauts sont recalculés à chaque requête. |
| 3 | Un motif PHP pour le pied de page ? | **interdit** | Il figerait la valeur dans le balisage à l'insertion — le défaut même que #38 supprime. |
| 4 | Le thème appelle-t-il `mtb_get_*` ? | **jamais** | Frontière de `CLAUDE.md`. Vrai même depuis que #38 les a réellement déclarées. |
| 5 | Balisage de pied de page différent du composant ? | **signalé, pas résolu** | Il appartiendrait à `mtb-core` : composant neuf, hors de #18 et hors de #38 (#38 §10.1). |
| 6 | Menu replié derrière un bouton ? | **non** | §7.3 : on ne cache jamais un menu en attendant une interaction. Et le JS est hors empreinte. |
| 7 | `wp:query-title` pour le `h1` de recherche et de 404 ? | **non, `wp:heading`** | C'est la dette T3 elle-même : `type="archive"` ne rend rien sur ces deux gabarits. |
| 8 | Habillage §7.3 de l'en-tête et du pied de page | **OUVERT** | Voir §2 : `assets/css/**` est hors empreinte et `theme.json` ferme toute mise en forme en balisage. |

---

## 13. Manques et dettes à router — la chaîne les signale, ne les résout pas

| # | Constat | Pourquoi pas ici |
|---|---|---|
| 1 | **`theme.json` ne déclare aucun `templateParts`.** L'aire et le **titre en français** des deux parts en dépendent ; sans elle, Fabienne peut lire un intitulé anglais dans l'éditeur de site. Le repli du cœur range tout de même les slugs `header` et `footer` dans leurs aires — à confirmer au navigateur. | `theme.json` est hors empreinte. |
| 2 | **Les deux liens de recours manquants de §9.5 — « Les portées » et « La meute ».** Seul « Accueil » est livré sur `404.html` et `search.html` : les deux index n'existent pas, et un lien mort ou un `href="#"` serait pire que son absence (D12). | Les deux destinations sont livrées par **#16**. **À payer par #16**, qui devra rouvrir ces deux gabarits pour ajouter les liens. |
| 3 | **Menu à deux niveaux.** Les attributs gelés du §4.4 suppriment tout mécanisme d'ouverture de sous-menu, JavaScript compris. Le remplacer par un survol serait inutilisable au doigt. | Demanderait soit du JavaScript (refusé), soit un composant dédié. |
| 4 | **Dettes T22 et T23** — le `<hr>` rend 0 px de large ; les marges verticales entre composants se cumulent au lieu de fusionner (`.mtb-canal` est une grille, `base.css:477`). `docs/ETAT.md` les range sous « epic Gabarits (#16-#18) ». | Les deux vivent dans **`base.css`**, qui reste hors empreinte. **À replacer par l'orchestrateur** vers #16, #17 ou une issue de dette. La chaîne n'y touche pas. |
| 5 | **Composant de pied de page dans `mtb-core`**, si le pied de page doit porter un balisage de coordonnées différent de la liste de définitions du catalogue, et un habillage sombre. | Voir §3.2 : composant neuf, hors de #18 **et** hors de #38 (#38 §10.1). |
| 6 | **Vocabulaire manquant dans `MASTER.md` §10.** Ni « plan du site », ni « mentions légales », ni « politique de confidentialité » n'y sont figés, alors que le pied de page doit les afficher. #38 §16 signale déjà le même trou pour « adresse », « téléphone » et « courriel ». | La décision 39 interdit à une chaîne d'amender le système de design. **Pour `lead-design-mtb`.** |

---

## 14. Questions de domaine — remontées, jamais comblées

Toute incertitude apparue pendant l'implémentation est **ajoutée ici** et remontée au rapport, jamais
tranchée par un agent.

1. **Que met-on dans le menu tant qu'il n'y a rien à pointer ?** La base ne contient que quatre
   Pages — `Contact` (publiée), `Espace privé (démonstration)` (publiée), `Sample Page` (publiée),
   `Privacy Policy` (**brouillon, titre anglais**). Ni index des portées, ni index des chiens.
   `MASTER.md` §10.3 fige pourtant « Toutes les portées » et « La meute » comme libellés. Un menu
   livré maintenant pointerait dans le vide ; un menu vide laisse l'en-tête nu au premier
   chargement. **Question pour l'orchestrateur, pas pour un agent.**
2. **`Privacy Policy` est un brouillon au titre anglais**, et l'issue demande un lien « mentions
   légales / confidentialité » dans le pied de page. Publier cette page, la renommer, ou en créer une
   autre sont **trois actes de contenu**, aucun ne relève d'une chaîne de développement. Le lien
   pointe donc vers ce que le site a réellement, ou vers rien. **Question pour l'éleveuse.**
3. **`Espace privé (démonstration)` doit-elle figurer au plan du site ?** C'est un contenu de
   démonstration créé par le provisionnement, pas une page du site réel. Elle n'est pas ajoutée.

### 14.1 Questions que la chaîne n'a PAS pu poser

`brainstorm-18` et `leaddev-18` n'ont **jamais rendu de rapport**, malgré une relance chacun. Ce sont
deux agents en lecture seule : le disque confirme qu'ils n'ont rien écrit, il n'y a donc pas de
travail silencieux à récupérer. La chaîne a tourné sans eux, l'analyse ayant été refaite directement
dans la stack. **Ce qui manque, et qui n'a donc été soumis à aucun regard extérieur :**

- la **comparaison des formes de point d'entrée** vers l'écran des menus (§4.5), tranchée par le seul
  `dev-front-mtb` au lieu d'être arbitrée entre deux propositions ;
- la **critique de l'approche** elle-même : personne n'a cherché si une voie plus simple que le
  double filtre du §4.3 existait, ni si le menu par défaut méritait un tout autre parti ;
- la **spécification écrite de `entete-pied.css`** avant son écriture : `dev-ux-mtb` travaille
  directement contre `MASTER.md` et ce contrat, sans plan intermédiaire à confronter.
