# Contrat d'interface — Issue #2 — Thème `mtb` : squelette et `theme.json` de base

**Gelé le 2026-08-15 par `lead-issue-mtb`. Contraignant à partir de maintenant.**

Cette issue ne touche que le thème : il n'y a donc pas de contrat extension ↔ thème.
Ce contrat est la **frontière entre `dev-front-mtb` et `dev-ux-mtb`**, qui travaillent en parallèle
et en aveugle l'un de l'autre dans le même arbre de travail. Il tient le même rôle qu'un contrat
front ↔ back : c'est le seul point où leurs deux moitiés sont réconciliées avant d'être écrites.

**Périmètre d'écriture de l'issue** — rien en dehors, sous aucun prétexte :

```
wp-content/themes/mtb/{style.css, functions.php, theme.json,
                       templates/**, parts/**, patterns/**, assets/**}
```

`wp-content/plugins/**` appartient à l'issue #1, en cours **en parallèle**. Arbre unique, aucune
branche : un fichier écrit hors périmètre est irrécupérable.

---

## Approche retenue — non rouvrable

**Option A : `tokens.css` est souverain, `theme.json` est un fichier de verrous.**

- `assets/css/tokens.css` est l'**unique domicile des valeurs brutes** (couleur, taille, espacement,
  durée), sous les **noms courts de `MASTER.md`** (`--sauge`, `--e-4`, `--t-base`, `--filet-double`).
- `theme.json` ne déclare **aucune palette, aucune taille de police, aucun pas d'espacement**.
  `MASTER.md` §14 : « la palette exposée à l'éditrice est **vide** ».
- Aucune section `styles`, aucune section `settings.custom` dans `theme.json`.

---

## Fonctions de lecture exposées par l'extension

**Aucune.** Le socle ne consomme rien de `mtb-core` et ne connaît pas son existence.

**Test de recette obligatoire** : `make wp cmd="plugin deactivate mtb-core"` → la page rendue est
**identique**. Le thème ne contient aucune règle métier et n'interroge jamais la base directement
(`CLAUDE.md`, frontière stricte).

Ce que le thème ne fera jamais, ici ni plus tard : un `WP_Query` sur une portée ou un chien ;
composer une chaîne métier (libellé de disponibilité, statut accordé au sexe, nom de discipline,
date formatée) ; reformater une valeur de test de santé ; décider quelle portée est « la dernière ».

## Blocs enregistrés

**Aucun.** Le socle n'enregistre aucun bloc et ne livre aucune composition (`patterns/` est vide,
`remove_theme_support( 'core-block-patterns' )` retire celles du cœur).

---

## Fichiers, et qui les écrit

| # | Fichier | Écrit par | Contenu |
|---|---|---|---|
| 0 | `index.php`, `header.php`, `footer.php` | `dev-front-mtb` | **SUPPRIMÉS** (placeholder de thème classique) |
| 1 | `style.css` | `dev-front-mtb` | En-tête de thème seul. **Jamais mis en file d'attente.** Aucune règle CSS. |
| 2 | `theme.json` | `dev-front-mtb` | Verrous + `layout.contentSize` / `wideSize` |
| 3 | `functions.php` | `dev-front-mtb` | Supports, 2 poignées gelées, `add_editor_style`, boucle générique, purge D6, préchargement conditionnel |
| 4 | `templates/index.html` | `dev-front-mtb` | Gabarit de secours ; cible du lien d'évitement |
| 5 | `templates/singular.html` | `dev-front-mtb` | **Arbitré : inclus** (voir Arbitrages) |
| 6 | `parts/.gitkeep`, `patterns/.gitkeep`, `assets/js/.gitkeep`, `assets/img/.gitkeep` | `dev-front-mtb` | Arborescence. **Aucun fichier JS dans le socle.** |
| 7 | `assets/css/tokens.css` | `dev-ux-mtb` | Jetons MASTER, **déclarations seules**, aucune règle |
| 8 | `assets/css/base.css` | `dev-ux-mtb` | `@font-face`, normalisation, éléments, `:focus-visible`, `.mtb-canal`, lien d'évitement |
| 9 | `assets/css/editor.css` | `dev-ux-mtb` | Correctifs de la toile de l'éditeur |
| 10 | `assets/css/blocs/.gitkeep` | `dev-ux-mtb` | Dossier câblé par la boucle générique, vide aujourd'hui |
| 11 | `assets/fonts/newsreader-var-latin.woff2` | `dev-ux-mtb` | Variable, axes `opsz` + `wght` |
| 12 | `assets/fonts/public-sans-var-latin.woff2` | `dev-ux-mtb` | Variable, axe `wght` |
| 13 | `assets/fonts/ofl-newsreader.txt`, `ofl-public-sans.txt` | `dev-ux-mtb` | OFL 1.1 **intégrale** + copyright de chaque fonderie |
| 14 | `assets/fonts/polices.md` | `dev-ux-mtb` | Source amont, commande `pyftsubset` littérale, tailles mesurées, axes vérifiés, `size-adjust` mesurés |

**Trois feuilles CSS. Deux requêtes CSS sur le site public** (`tokens.css`, `base.css`).
`editor.css` n'est jamais servi au visiteur.

---

## Poignées de feuilles — gelées

| Poignée | Fichier | Dépend de | Contexte |
|---|---|---|---|
| `mtb-jetons` | `assets/css/tokens.css` | — | site + éditeur |
| `mtb-base` | `assets/css/base.css` | `mtb-jetons` | site + éditeur |
| *(sans poignée)* | `assets/css/editor.css` | via `add_editor_style` | éditeur seul |
| `mtb-bloc-<espace>-<nom>` | `assets/css/blocs/<espace>-<nom>.css` | `mtb-jetons` | conditionnel au rendu du bloc |

Version de chaque feuille = `filemtime()`. **Jamais** un numéro incrémenté à la main, jamais
`wp_get_theme()->get('Version')`. Chaque mise en file d'attente est gardée par `file_exists()` :
on ne sert jamais un fichier absent, ce qui permet aux deux devs de committer dans le désordre.

`add_editor_style( array( 'assets/css/tokens.css', 'assets/css/base.css', 'assets/css/editor.css' ) )`
— **les trois** feuilles dans l'iframe de l'éditeur. C'est le coût assumé de l'option A : sans
`tokens.css` côté éditeur, `--l-texte` n'existe pas et la largeur de canal ne s'y affiche pas.

**La boucle générique est ce qui garantit que `functions.php` ne se rouvre plus** (décision 9 de
`docs/ETAT.md`) : `dev-ux-mtb` crée un fichier dans `assets/css/blocs/` et il est servi
automatiquement. Elle itère sur `WP_Block_Type_Registry`, **jamais sur le disque** — `glob()`,
`scandir()`, `opendir()`, `DirectoryIterator` sont **interdits** (désactivés sur une partie des
hébergements mutualisés ; décision 4, BRIEF §12). Seul `file_exists()` touche le disque.
`'path'` est renseigné pour autoriser le cœur à inliner les petites feuilles.

---

## Jetons que `tokens.css` doit exposer — noms courts de MASTER, aucun autre

- **Familles** (§4) : `--serif`, `--sans`
- **Couleurs** (§3.1, quinze) : `--calcaire`, `--calcaire-creux`, `--calcaire-ombre`, `--blanc`,
  `--pin`, `--pin-creux`, `--texte`, `--texte-doux`, `--sauge`, `--sauge-fonce`, `--laiton`,
  `--laiton-texte`, `--laiton-clair`, `--filet`, `--oxyde`
- **Signature** (§2.1) : `--filet-double`, `--filet-double-v`, `--filet-double-h`
- **Typographie** (§4.4) : `--t-xs`, `--t-sm`, `--t-base`, `--t-md`, `--t-lg`, `--t-xl`, `--t-2xl`, `--t-3xl`
- **Espacement** (§5.1) : `--e-1` … `--e-9`, `--rythme-section`, `--marge-page`
- **Rayons** (§5.2) : `--r-0`, `--r-1`
- **Bordures** (§5.3) : `--bord-fin`, `--bord-actif`, `--bord-fort`, `--cerne-photo`
- **Élévation / voiles** (§5.4, §6.4) : `--ombre-panneau`, `--voile-photo`
- **Ratios photo** (§6.1) : `--r-portrait`, `--r-paysage`, `--r-carre`, `--r-bandeau`, `--r-libre`
- **Canaux** (§7.1) : `--l-texte`, `--l-large`
- **Points de rupture** (§7.2) : `--bp-tableau`, `--bp-nav`, `--bp-fiche`

Trois précisions contraignantes :

1. **Collision de préfixe assumée** : `--r-0` / `--r-1` sont des **rayons**, `--r-portrait` …
   `--r-libre` sont des **ratios**. MASTER les nomme ainsi, on ne renomme pas. `tokens.css` sépare
   les deux familles par un commentaire explicite.
2. **Les `--bp-*` ne sont pas utilisables dans une requête média** — `@media (max-width: var(…))`
   est invalide en CSS. Les requêtes média écrivent la valeur **en littéral** (`48rem`, `60rem`,
   `64rem`) avec un commentaire renvoyant à MASTER §7.2. Ce n'est pas une entorse au §13 :
   MASTER §7.6 écrit lui-même `@media screen and (max-width: 47.999rem)` en littéral.
3. **Aucun jeton de durée, de transition ou d'animation n'existe.** MASTER n'en définit aucun.
   `dev-ux-mtb` **n'en invente pas** : soit aucune transition, soit question bloquante.

---

## Crochets de classes émis par le balisage

| Crochet | Émis par | Ce qu'il identifie | Qui le style |
|---|---|---|---|
| `#contenu` | `index.html`, `singular.html` (`anchor` du groupe) | Cible du lien d'évitement | `dev-ux-mtb` |
| `.mtb-canal` | idem (`className` du groupe) | Grille à trois canaux MASTER §7.1 | `dev-ux-mtb` |
| `.mtb-canal.is-layout-constrained > *` | cœur + balisage | **Neutralisation obligatoire** des `max-width` / `margin-inline` du cœur | `dev-ux-mtb` |
| `.alignwide` / `.alignfull` | cœur, sur les enfants | Canal large / canal pleine | `dev-ux-mtb` |
| `.skip-link.screen-reader-text` | **cœur** (JS) | Lien d'évitement — le cœur injecte **sa propre CSS en ligne**, à écraser explicitement | `dev-ux-mtb` |
| `.wp-site-blocks` | cœur | Enveloppe racine d'un thème de blocs | `dev-ux-mtb` |
| `.wp-block-group`, `.is-layout-flow`, `.is-layout-constrained` | cœur | Classes de mise en page | `dev-ux-mtb`, **uniquement pour neutraliser** |

**Convention gelée pour tout le projet** : tout crochet propre au thème est préfixé `mtb-`,
minuscules, sans accent. Une classe non préfixée est soit une classe du cœur, soit une erreur.

**Point de vigilance contractuel** : `layout: "constrained"` est retenu (il donne à l'éditrice les
commandes *Largeur étendue* / *Pleine largeur*), donc le cœur émet des règles de mise en page qui
font double emploi avec `.mtb-canal`. `dev-ux-mtb` les neutralise **et vérifie le résultat dans le
navigateur** — l'égalité de spécificité rend l'ordre de source déterminant. Si la neutralisation se
révèle instable, le repli est de passer `layout` à `"default"` (l'éditrice perd alors les boutons
d'alignement) : **ce repli se remonte à `lead-issue-mtb`, il ne se prend pas en silence.**

---

## Fichiers de police — chemins, noms et contraintes gelés

| Chemin | Contrainte |
|---|---|
| `assets/fonts/newsreader-var-latin.woff2` | **variable**, axes `opsz` + `wght` conservés |
| `assets/fonts/public-sans-var-latin.woff2` | **variable**, axe `wght` conservé |
| `assets/fonts/ofl-newsreader.txt` / `ofl-public-sans.txt` | OFL 1.1 **intégrale** + copyright exact recopié de l'amont |
| `assets/fonts/polices.md` | source, commande, tailles, axes, `size-adjust` |

Sources amont, **vérifiées joignables (HTTP 200) par `lead-issue-mtb`** :

- `https://raw.githubusercontent.com/google/fonts/main/ofl/newsreader/Newsreader%5Bopsz,wght%5D.ttf` (451 664 o)
- `https://raw.githubusercontent.com/google/fonts/main/ofl/publicsans/PublicSans%5Bwght%5D.ttf`
- les deux `OFL.txt` des mêmes répertoires

Outillage vérifié présent : `fontTools 4.63.0`, `brotli`, Python 3.13.

`unicode-range` gelé pour les deux familles, **U+2116 inclus** (voir Arbitrages) :

```
U+0000-00FF, U+0131, U+0152-0153, U+2000-206F, U+2116, U+20AC, U+2122, U+2212
```

- **Les fichiers restent variables.** Aucun `--instancer`, aucun épinglage d'axe. Vérification
  **obligatoire** après sous-ensemble :
  `python -c "from fontTools.ttLib import TTFont; f=TTFont('…woff2'); print([a.axisTag for a in f['fvar'].axes])"`
  → `['opsz', 'wght']` pour Newsreader, `['wght']` pour Public Sans. Toute autre sortie = livraison refusée.
- Fonctionnalités OpenType conservées au minimum : `kern, liga, clig, ccmp, locl, mark, mkmk, tnum`.
  **`tnum` est non négociable** (MASTER §4.5 : chiffres tabulaires sur les n° LOF et les %).
- **Budget dur : ≤ 100 Ko pour les deux réunis.** Le chiffre réel est **mesuré et rapporté**,
  octet par octet. **Un dépassement est un signalement à `lead-issue-mtb`, pas un arrondi et pas
  une négociation silencieuse du jeu de caractères.**
- Le `@font-face` est écrit par **`dev-ux-mtb`** dans `base.css`, chemin relatif `url("../fonts/…")`.
- Le `<link rel="preload">` est écrit par **`dev-front-mtb`** dans `functions.php`,
  `crossorigin="anonymous"` (**obligatoire même en même origine**, sans lui la police est
  téléchargée deux fois), **conditionné à `file_exists()`**.
- **Un renommage de fichier casse le préchargement en silence : tout renommage rouvre ce contrat.**

**Interdiction absolue** : aucun `@font-face` déclaré pour un fichier absent, aucun `preload`
pointant un fichier absent. Si `base.css` doit être committé avant les `.woff2`, il l'est **sans**
les blocs `@font-face`. Un socle qui a l'air fini alors qu'il ne l'est pas est le pire état possible
sur un projet à chaînes parallèles.

---

## `theme.json` — les verrous

Aucune palette, aucune taille, aucun pas d'espacement. `appearanceTools` **non** activé.

| Famille | Clés à `false` / vides |
|---|---|
| `color` | `palette: []`, `defaultPalette`, `custom`, `gradients: []`, `defaultGradients`, `customGradient`, `duotone: []`, `defaultDuotone`, `customDuotone`, `background`, `text`, `link`, `heading`, `button`, `caption` |
| `typography` | `fontFamilies: []`, `fontSizes: []`, `defaultFontSizes`, `customFontSize`, `fluid`, `fontStyle`, `fontWeight`, `letterSpacing`, `lineHeight`, `textDecoration`, `textTransform`, `textAlign`, `textColumns`, `writingMode`, `dropCap` |
| `spacing` | `spacingSizes: []`, `spacingScale: {steps: 0}`, `defaultSpacingSizes`, `customSpacingSize`, `padding`, `margin`, `units: []`, `blockGap: null` |
| `border` | `color`, `radius`, `style`, `width` |
| `shadow` | `presets: []`, `defaultPresets` |
| `dimensions` | `aspectRatio`, `minHeight`, `aspectRatios: []`, `defaultAspectRatios` |
| `position` | `sticky` |
| `background` | `backgroundImage`, `backgroundSize` |
| racine | `useRootPaddingAwareAlignments: false` |
| `layout` | `contentSize: "36rem"`, `wideSize: "68rem"`, `allowEditing`, `allowCustomContentAndWideSize` |
| `blocks` | `core/image` → `lightbox: {enabled: false, allowEditing: false}` |

**Correction acceptée par `lead-issue-mtb`** : ma consigne initiale demandait
`typography.fontFamily: false` et `typography.fontSize: false`. **Ces deux clés n'existent pas** dans
le schéma `theme.json` ; les écrire produirait un **verrou fantôme** silencieusement ignoré.
Les familles et les tailles se verrouillent en ne déclarant **aucun preset** (`fontFamilies: []`,
`fontSizes: []`) et en interdisant la saisie libre (`customFontSize: false`, `defaultFontSizes: false`).

**Quatre clés sont à vérifier dans le cœur installé, pas à supposer** — `layout.allowEditing`,
`layout.allowCustomContentAndWideSize`, `blocks."core/image".lightbox`, et la clé Openverse de
`block_editor_settings_all`. Conduite à tenir écrite : **si une clé est absente du cœur installé,
elle est retirée du fichier** (jamais laissée « au cas où » : une clé inconnue est ignorée en
silence, donc c'est un verrou fantôme) **et son absence est remontée à `lead-issue-mtb`**.

**La livraison n'est pas le JSON, c'est la vérification.** Quatre contrôles obligatoires :

1. **Le seul qui compte** — connecté en **`fabienne`** (rôle Éditeur, **pas** `admin`) : insérer un
   bloc **Paragraphe** → **aucun onglet « Styles »** dans le panneau latéral. Répéter sur **Groupe**,
   **Image**, **Liste**, **Titre**.
2. `make wp cmd="eval 'print_r( wp_get_global_settings() );'"`
3. `curl -s http://localhost:8080/ | grep -o "\-\-wp--preset--[a-z-]*" | sort -u` → **sortie vide**.
   ⚠️ **Ce contrôle est inatteignable par `theme.json` seul — voir l'amendement 1.**
4. Canal : ≈ 576 px à 1440 px ; 324 px à 360 px, sans défilement horizontal.

**Version du schéma** : `"version": 3` par défaut, `Requires PHP: 8.1`. `dev-front-mtb` vérifie le
cœur réel (`make wp cmd="core version"`) ; si < 6.6, bascule en v2, **retire**
`defaultFontSizes` / `defaultSpacingSizes` (inconnues en v2 = verrous fantômes) et le remonte.

---

## Étanchéité D6 — le thème la porte, maintenant

**Arbitrage rendu** : `mtb-core` serait un domicile défendable à long terme, mais son squelette est
livré **en parallèle** et je ne peux pas lui ajouter de périmètre. Renvoyer la purge à plus tard
ferait déclarer **D6 vraie alors qu'elle est fausse**. Le thème la porte donc dès maintenant.

| Origine tierce | Traitement |
|---|---|
| **Emoji** — `s.w.org` via `settings.baseUrl` du script de détection | `remove_action( 'wp_head', 'print_emoji_detection_script', 7 )` (**priorité 7 obligatoire**), `admin_print_scripts`, `print_emoji_styles` (< 6.4) **et** `wp_enqueue_emoji_styles` (≥ 6.4), `wp_staticize_emoji` sur `the_content_feed` / `comment_text_rss` / `wp_mail`, `tiny_mce_plugins` sans `wpemoji`, `wp_resource_hints` purgé de `s.w.org` |
| **Font Library** (WP 6.5+) — collection Google Fonts distante | `wp_unregister_font_collection( 'google-fonts' )` sur `init` tardif. **Verrou secondaire gratuit** : Fabienne est **Éditeur**, donc sans `edit_theme_options`, donc sans éditeur de site. **Verrou tertiaire** : `fontFamilies: []` laisse le sélecteur vide. |
| **oEmbed / contenus embarqués** | `wp_oembed_add_discovery_links`, `wp_oembed_add_host_js`, `embed_oembed_discover` → `false`, `wp_deregister_script( 'wp-embed' )` |
| **Gravatar** — `secure.gravatar.com` | `pre_option_show_avatars` → `'0'` |
| **Compositions distantes** (aperçus hébergés chez WordPress.org) | `should_load_remote_block_patterns` → `false` |
| **Répertoire de blocs** | `remove_action( 'enqueue_block_editor_assets', 'wp_enqueue_editor_block_directory_assets' )` |
| **Openverse** (onglet média de l'insérteur) | clé de `block_editor_settings_all` — **nom à vérifier dans le cœur installé**, pas à supposer |

Un `remove_action()` sur un crochet absent ne produit ni erreur ni avertissement : la liste couvre
donc les cœurs antérieurs et postérieurs à 6.4 sans détection de version.

**Vérification imposée** : `grep -n "emoji" /var/www/html/wp-includes/default-filters.php` — chaque
`add_action` / `add_filter` du cœur doit avoir son `remove_` symétrique **à la même priorité**.

**Hors périmètre D6, à ne pas toucher** : `api.wordpress.org` (mises à jour, Site Health) — ce sont
des appels **serveur**, et D6 dit « aucune requête **navigateur** ». Les couper serait une décision
d'exploitation, pas une décision de thème.

**Preuve finale à consigner** : DevTools → Réseau → filtre « domaine ≠ localhost », sur (a) l'accueil,
(b) une page, (c) `/wp-admin/post.php` en édition, (d) l'insérteur ouvert sur l'onglet Compositions
**et** l'onglet Média. **Attendu : zéro entrée dans les quatre cas.**

---

## États spéciaux

| État | Émis par le serveur | Rendu par le thème |
|---|---|---|
| `aucune_portee` | sans objet — le socle ne lit aucun contenu structuré | sans objet |
| `donnee_absente` | sans objet | sans objet |
| `parent_hors_elevage` | sans objet | sans objet |
| `page_protegee` | cœur WordPress, `get_the_password_form()` via `core/post-content` | `templates/singular.html` rend le formulaire natif — **BRIEF §8 satisfait sans une ligne de logique**. La mise en forme MASTER §9.5 appartient à l'epic Gabarits. |
| `aucun_resultat` (requête vide, 404, recherche) | cœur | `core/query-no-results` dans `index.html`. Formulation **provisoire**, signalée en commentaire ; les libellés MASTER §9.5 (« Page introuvable », les trois liens) appartiennent à l'epic Gabarits. |

## Chaînes fournies par le serveur

Le socle n'affiche **aucune chaîne métier**. Deux chaînes d'interface seulement :

| Chaîne | Origine | Contrainte |
|---|---|---|
| **« Aller au contenu »** | cœur WordPress (traduction `fr_FR` de *Skip to content*) | Libellé exact imposé par MASTER §10.3. **À vérifier dans la stack, pas à supposer** — voir Arbitrages. |
| « Aucun contenu à afficher. » | balisage du socle | **Provisoire**, à signaler en commentaire dans `index.html`. Remplacée par les formulations MASTER §9.5 à l'epic Gabarits. Ce n'est pas un fait d'élevage, donc pas une invention interdite par D11. |

---

## Interdits

- Le thème n'interroge **jamais** la base directement et ne contient **aucune règle métier**.
- Le thème ne compose **jamais** une chaîne métier ni ne reformate une valeur de santé.
- **`dev-front-mtb` ne touche jamais** `assets/css/**` ni `assets/fonts/**`. Il n'écrit **aucune
  règle CSS** — ni dans `style.css`, ni en ligne, ni via `wp_add_inline_style` — et **aucune valeur**
  de couleur, taille ou espacement, hors la duplication unique et commentée ci-dessous.
- **`dev-ux-mtb` ne touche jamais** `functions.php`, `theme.json`, `style.css`, `templates/**`,
  `parts/**`, `patterns/**`. Il ne demande **jamais** l'ajout d'une poignée : il crée un fichier dans
  `assets/css/blocs/`. Il ne renomme aucun fichier de police. Il n'invente **aucun jeton absent de
  MASTER** et n'écrit **aucune valeur brute hors `tokens.css`**, les valeurs littérales des requêtes
  média exceptées et commentées.
- **Les deux s'interdisent** : une origine tierce, une étape de construction, `node_modules`,
  `package.json`, un préprocesseur, un cadre CSS.
- **Aucun `parts/header.html`, aucun `parts/footer.html`, aucune navigation** — epic Gabarits et
  issue #18. En livrer un ici crée une collision d'empreinte fichiers dans un arbre sans branche.
- `glob()`, `scandir()`, `opendir()`, `DirectoryIterator` : **interdits** (portabilité mutualisé).
- Tous les noms de fichiers en **minuscules, sans accent** — développement Windows, production
  Linux : la casse ne pardonne pas.
- Aucun fichier dans `assets/js/` : **zéro JavaScript de thème dans le socle.**

---

## L'unique duplication tolérée

`contentSize: "36rem"` et `wideSize: "68rem"` sont écrits **en littéral** dans `theme.json`, et
`--l-texte: 36rem` / `--l-large: 68rem` dans `tokens.css`. **C'est voulu** : MASTER §7.1 prescrit
nommément ces deux valeurs dans sa colonne « Correspondance `theme.json` ».

Chaque côté porte un commentaire renvoyant à l'autre et à MASTER §7.1 :

- `tokens.css`, à côté de `--l-texte` / `--l-large` : *« Ces deux valeurs sont dupliquées en littéral
  dans `theme.json` (`settings.layout.contentSize` / `wideSize`) — MASTER §7.1 les y prescrit
  nommément. Seule duplication tolérée du socle. Toute modification se fait des deux côtés. »*
- `style.css`, en commentaire d'en-tête : la réciproque, renvoyant à `assets/css/tokens.css`.

**Toute autre valeur brute présente à la fois dans `theme.json` et dans `tokens.css` est une
non-conformité** que `review-mtb` doit remonter.

---

## Arbitrages

| # | Désaccord ou question | Décision | Raison |
|---|---|---|---|
| 1 | `tokens.css` ou `theme.json` comme source de vérité (options A / B du brainstorm) | **`tokens.css` souverain** | MASTER désigne `tokens.css` comme le fichier de jetons et `theme.json` comme son **miroir**. `review-mtb` audite **contre** MASTER : inverser la hiérarchie sans nécessité créerait un litige permanent. L'option B (`settings.custom`) est défendable mais paie en poids inline sur chaque page et exigerait d'amender MASTER §16 — pas de le contourner en silence. |
| 2 | Le socle livre-t-il `parts/header.html` / `footer.html` (option C) ? | **Non** | Collision d'empreinte fichiers avec l'epic Gabarits et l'issue #18 dans un arbre sans branche, et l'en-tête dépend de la question **D7** de MASTER §15 (logo image ou typographie seule), non tranchée. Contrepartie assumée : le lien d'évitement du socle est celui du cœur, **injecté en JavaScript depuis WP 6.4**. |
| 3 | `templates/singular.html` entre-t-il dans le périmètre ? | **Oui, inclus** | Sans lui, une page (Contact, page protégée) s'affiche **sans son contenu et sans aucun `<h1>`** : BRIEF §11 et MASTER §12.10 seraient faux et le socle invérifiable. Six lignes, **aucun rapport avec D7**, donc aucune collision avec l'epic Gabarits, qui le réécrira comme il réécrira `index.html`. Bénéfice supplémentaire : `core/post-content` rend nativement le formulaire de mot de passe (BRIEF §8). |
| 4 | Qui porte la purge des origines tierces du cœur ? | **Le thème, maintenant** | `mtb-core` serait défendable à long terme, mais son squelette est livré en parallèle et son périmètre est clos. Reporter la purge ferait déclarer **D6 vraie alors qu'elle est fausse**. Coût : ~30 lignes dans `functions.php`, triviales à déplacer plus tard. **Point d'attention pour `/lead-mtb`** : décider si la purge doit à terme migrer vers `mtb-core` (elle survivrait alors à un changement de thème) et le consigner dans `docs/ETAT.md`. |
| 5 | `№` (U+2116) : MASTER §4.1 l'inclut dans le sous-ensemble, l'`unicode-range` du §4.2 ne le couvre pas | **Inclus ; `unicode-range` étendu de `U+2116`** | §4.1 **énumère délibérément** le jeu de caractères, §4.2 n'est qu'un extrait illustratif du chargement. L'énumération fait foi. |
| 6 | `typography.fontFamily: false` / `fontSize: false` demandés dans ma consigne | **Retirés** — ces clés n'existent pas | `leaddev-front-mtb` a établi qu'elles ne sont pas dans le schéma. Une clé inconnue est ignorée en silence : c'est exactement le **verrou fantôme** que la consigne cherchait à éviter. Le verrou réel est `fontFamilies: []` + `fontSizes: []` + `customFontSize: false` + `defaultFontSizes: false`. |
| 7 | Libellé `fr_FR` du lien d'évitement du cœur | **À vérifier, non supposé.** Si ≠ « Aller au contenu » : filtre `gettext` **strictement borné** au couple `( 'Skip to content', domaine 'default' )` dans `functions.php` | MASTER §10.3 fige le libellé. Un filtre `gettext` borné par deux tests est une correction de libellé, **pas un morceau d'en-tête** : aucune collision d'empreinte avec l'epic Gabarits. Preuve attendue : `curl -s http://localhost:8080/ \| grep -i "skip\|Aller au contenu"`. |
| 8 | Crochet neutralisant la bibliothèque de polices | **`wp_unregister_font_collection( 'google-fonts' )`**, plus vérification d'un éventuel filtre dédié dans le cœur installé | C'est le nom **certifié** (WP 6.5+) et il retire la seule origine tierce du dispositif. Aucun nom de crochet ne doit être inventé : si le `grep` dans `wp-includes/fonts.php` révèle un filtre booléen dédié, c'est lui qu'on emploie. |
| 9 | `layout: "constrained"` en conflit avec la grille `.mtb-canal` | **`constrained` retenu**, neutralisation par `dev-ux-mtb` **avec vérification navigateur** ; repli `"default"` documenté | `constrained` est ce qui donne à l'éditrice *Largeur étendue* / *Pleine largeur* — le canal large de MASTER §7.1 existe pour ça. Le repli lui **retirerait** ces boutons : il se remonte à `lead-issue-mtb`, il ne se prend pas en silence. |

---

## Dettes explicites créées par cette issue

À porter dans `docs/ETAT.md` et à ne pas découvrir dans trois lots :

1. **Le lien d'évitement du socle dépend du JavaScript** (le cœur l'injecte par script depuis
   WP 6.4) et sa cible `<main>` n'est pas focalisable. BRIEF §11 en fait une exigence bloquante :
   **la ligne « lien d'évitement » de D7 n'est pas cochée par cette issue.** Elle se solde à l'epic
   Gabarits, par un lien écrit à la main dans `parts/header.html` avec `tabindex="-1"` sur la cible.
2. **Thème de blocs + rôle Éditeur natif : Fabienne ne pourra pas modifier son menu.** Dans un thème
   FSE, le menu est un bloc Navigation dans `parts/header.html`, éditable uniquement via l'éditeur de
   site, qui exige `edit_theme_options` — capacité qu'un Éditeur n'a pas. Or BRIEF §13 fait de
   « modifier le menu » une ligne du guide. **Dette de conception créée ici, payée par l'issue #18.**
3. **Question D7 de MASTER §15** (l'en-tête reprend-il le logo actuel en image, ou se compose-t-il en
   typographie seule ?) doit être tranchée **avant** l'epic Gabarits.

---

# Amendements — 2026-08-16

Le contrat gelé au 2026-08-15 s'est révélé faux ou incomplet sur six points. Les corrections
ci-dessous sont **de même valeur contraignante** que le corps du document.

## Amendement 1 — `theme.json` ne suffit pas à retirer les valeurs prédéfinies du cœur

**Le corps du contrat se trompait.** J'avais posé `defaultPalette: false`, `defaultFontSizes: false`,
`defaultSpacingSizes: false`, `defaultPresets: false`, `defaultAspectRatios: false` en croyant qu'ils
retiraient les presets du cœur du CSS émis. **C'est faux, et c'est vérifié dans le code du cœur** :
`wp_get_global_stylesheet()` code en dur `$origins = array( 'default', 'theme', 'custom' )`, et ces
clés ne pilotent que `prevent_override` — elles décident si un preset de thème *écrase* un preset du
cœur, elles n'en retirent **aucun** du CSS servi. Résultat mesuré avant correction : **49 définitions
`--wp--preset--*` et ~10,2 Ko morts par page.**

**L'enjeu n'est pas le poids, c'est le verrouillage** : tant que ces variables existent et
fonctionnent, une classe `has-vivid-red-color` présente dans un contenu rendrait réellement une
couleur hors des quinze jetons du §3.1 — ce que MASTER §13 interdit.

**Mécanisme retenu, contraignant pour toute la suite** : le filtre **`wp_theme_json_data_default`**,
qui vide les presets du cœur (`color.palette`, `gradients`, `duotone`, `typography.fontSizes`,
`spacing.spacingSizes` / `spacingScale`, `shadow.presets`, `dimensions.aspectRatios`).

**Résultat mesuré : 47 presets sur 49 retirés — ce n'est pas un succès complet, et c'est écrit ainsi.**

| Mesure | Avant | Après |
|---|---|---|
| Définitions `--wp--preset--*` | 49 | **2** |
| `global-styles-inline-css` | 10 397 o | **≈ 2 100 o** (−80 %) |
| Références pendantes (`var()` sans définition) | — | **0** (définies = 2, utilisées = 2) |

**Les deux résidus sont inertes et hors d'atteinte** : `--wp--preset--font-size--normal` et
`--wp--preset--font-size--huge` sont écrits **en dur** dans
`wp-includes/css/dist/block-library/common.css`, qu'aucun réglage de thème n'atteint. Vérifié :
aucune classe `has-*` n'est présente dans le rendu, et l'éditrice ne peut plus en appliquer.

**Zéro référence pendante** était le vrai risque de l'opération — vider des presets encore référencés
par une feuille de bloc du cœur aurait produit des `var()` sans valeur. Mesuré, pas supposé.

**Règle générale à retenir** : dans `theme.json`, une clé inconnue ou mal comprise est **ignorée en
silence**. Un verrou n'existe que si on a **observé son effet**, jamais parce qu'on a écrit la clé.

## Amendement 2 — Variations de style des blocs du cœur : vérifier le mécanisme AVANT de retirer

MASTER §13 et §14 interdisent que l'éditrice atteigne un arrondi, une ombre, un italique de design.
Or certaines **variations de style des blocs du cœur** les lui offrent en deux clics, **hors de portée
de `theme.json`**.

**Règle gelée : on retire la variation du cœur, on ne la contre jamais en spécificité depuis le CSS.**
Surenchérir en spécificité produit une course aux `!important` que la prochaine version de WordPress
rouvrira.

**Mais le mécanisme de retrait dépend de l'endroit où la variation est déclarée, et il faut le
vérifier, pas le supposer** — les trois cas rencontrés dans cette seule issue :

| Où la variation est déclarée | Mécanisme qui marche | Ce qui serait un verrou fantôme |
|---|---|---|
| `WP_Block_Styles_Registry` | `unregister_block_style( $bloc, $nom )` | — |
| Tableau `styles` du `block.json` du cœur | filtre **`block_type_metadata`** avant enregistrement | `unregister_block_style()` : **sans effet** |
| Classe héritée servie par la feuille du cœur, sans interface | **rien à retirer** — vérifier d'abord qu'aucun chemin d'édition n'y mène | les deux ci-dessus : sans effet |

**Cas traité et livré** : `core/image` → variation « Arrondis », déclarée dans le `block.json` du cœur,
retirée par `block_type_metadata`. `unregister_block_style()` aurait été sans effet.

**Pour #16, #17, #18 et les issues de composants** : avant de retirer une variation, exécuter les trois
contrôles — `grep` du `block.json` du cœur, `WP_Block_Styles_Registry::get_registered_styles_for_block()`,
et **l'observation sous le compte `fabienne`**. Si aucune interface ne mène à la variation, **ne rien
écrire** et le consigner.

### Variations tranchées par cette issue — état gelé

**Retirées** (chacune viole MASTER, et chacune était atteignable par l'éditrice) :

| Variation | Motif |
|---|---|
| `core/image` → *Arrondis* | `border-radius` sur une image — MASTER §13 l'interdit nommément, §14 range l'arrondi hors de sa portée |
| `core/site-logo` → *Arrondis* | jumelle exacte de la précédente, sur le logo |
| `core/separator` → *Ligne large* | attaque l'**élément signature** : MASTER §2.1 remplace tout `<hr>` par le filet double, liste close |
| `core/separator` → *Pointillés* | idem — un bouton qui casse la signature sans que l'éditrice puisse le deviner (BRIEF §6) |

**Conservées — décision explicite, pas un oubli.** Aucune ne viole §13, chacune est un choix éditorial
légitime, et les retirer appauvrirait l'éditrice sans raison. Elles seront habillées quand leur
composant arrivera : `core/button` → *Contour* · `core/quote` → *Uni* · `core/table` → *Rayures* ·
`core/tag-cloud` → *Contour*.

### Cas vérifié et écarté — `core/quote` → « Citation large »

**Aucune action. Ne pas refaire le diagnostic.** La règle du cœur
`.wp-block-quote.is-large … { font-style: italic; padding: 0 1em }` semble violer MASTER §13
(italique comme dispositif) et écraser la réserve du filet vertical. **Elle est pourtant hors sujet**,
et c'est vérifié :

- `WP_Block_Styles_Registry` pour `core/quote` : **vide** ;
- `block.json` du cœur : ne déclare que `default` et `plain` ;
- éditeur réel sous `fabienne` : ne propose que « Par défaut | Uni » ;
- la classe `is-large` n'est produite que par la fonction `save` d'une **déprécation**, pour du contenu
  enregistré par un WordPress pré-5.x avec `style: 2` ;
- la reprise de l'ancien site est du **HTML IONOS**, pas du balisage de blocs migré : aucun contenu du
  site ne peut porter cette classe.

`unregister_block_style( 'core/quote', 'large' )` aurait échoué sur un registre vide — **un verrou
fantôme de plus**. La consigne initiale, y compris de la part de l'orchestrateur, était fausse ; elle
a été corrigée après vérification.

### Point ouvert renvoyé à l'epic Gabarits

`core/social-links` et `core/tag-cloud` n'apparaissent nulle part dans le brief. Le bon outil n'est
**pas** le retrait de variation mais **`allowed_block_types_all`** : restreindre l'insérteur est une
décision de simplification de l'administration qui mérite son propre arbitrage, avec la **liste
complète des blocs autorisés**. Hors périmètre de #2.

## Amendement 2 bis — INTERDIT PERMANENT : ne jamais activer `wp-block-styles`

> **`add_theme_support( 'wp-block-styles' )` est interdit dans ce thème, définitivement.**

`wp-includes/blocks/separator/theme.min.css` contient une règle à **(0,3,0)** qui impose
**`width: 100px`** au séparateur. Cette feuille n'est chargée **que** si le thème active
`wp-block-styles`. Si elle l'était, elle **réduirait le filet double du `<hr>` à 100 px** — c'est-à-dire
qu'elle détruirait l'**élément signature** du site (MASTER §2.1), le seul dispositif visuel que le
design system revendique comme unique.

**La contre-mesure existante ne protège pas de ce cas.** `base.css` neutralise la `border` du
séparateur via `hr.wp-block-separator` (0,1,1), mais **ne couvre pas le `width`** : la règle du cœur
gagnerait.

C'est un piège à retardement — une issue de gabarit activerait `wp-block-styles` de bonne foi, pour
récupérer un style de bloc du cœur, et le lien de cause à effet avec un filet double tronqué serait
**introuvable**. D'où cet interdit écrit plutôt qu'une note en commentaire.

**Corollaire** : ce thème n'emprunte **aucun** style de bloc au cœur. Tout habillage de bloc passe par
`assets/css/blocs/<espace>-<nom>.css` et la boucle générique.

## Amendement 2 ter — Piège de méthode : ne pas filtrer les sorties de la stack sur « mtb-core »

**Pour `test-integration-mtb` et toute chaîne qui inspectera la stack.**

La `Description:` du thème, dans `style.css`, se **termine** par les mots « … appartient à l'extension
mtb-core ». Filtrer une sortie de la stack sur `mtb-core` — `grep -v mtb-core`, ou l'inverse pour
isoler l'extension — **supprime donc la description du thème**, ou la fait apparaître là où on
cherchait l'extension.

Conséquence observée : on conclut à tort que l'en-tête du thème est cassé ou tronqué, alors qu'il est
intact. `get_file_data()` renvoie bien les dix en-têtes complets et `is_block_theme()` renvoie **oui**.

**Vérifier l'en-tête par `get_file_data()`, jamais par un `grep` filtré.**

## Amendement 3 — Clause `function_exists()` héritée du contrat de l'issue #1

`docs/contracts/issue-1.md` engage le thème sur un point que cette chaîne n'a pas pu voir, les deux
tournant en parallèle. **Il est reporté ici pour que #16, #17 et #18 le trouvent sans relire #1** :

> **Toute fonction de lecture de l'extension est appelée derrière `function_exists()`** — exigence
> D12 : extension désactivée ⇒ page **dégradée**, jamais d'écran blanc.

**État dans le socle : la clause est respectée, de façon vacante.** Le socle **n'appelle aucune
fonction `mtb_*` de l'extension** — il n'y avait donc rien à garder. Preuves :

- `grep -rE "WP_Query|get_post_meta|get_posts|get_terms|MTB\\\\"` sur tout `wp-content/themes/mtb/`
  → **zéro occurrence** ;
- `wp plugin deactivate mtb-core` → accueil et `/contact/` **strictement identiques**, `diff` vide ;
- le seul `function_exists()` du thème garde `wp_unregister_font_collection()`, une fonction **du cœur**.

**#16, #17 et #18 seront les premières issues à consommer réellement l'extension : la clause devient
active pour elles, et leur contrat gelé doit la reprendre explicitement.**

**Frontière vérifiable par grep, gelée** : un fichier de `wp-content/themes/mtb/` contenant
`WP_Query`, `get_post_meta`, `get_posts`, `get_terms` ou `MTB\` est en infraction.

## Amendement 4 — Filet double : quatre emplacements livrés, quatre renvoyés

MASTER §2.1 fixe une **liste close de huit emplacements** et **un jeton unique** `--filet-double`.
Ma consigne initiale à `dev-ux-mtb` — « le socle n'en rend aucun » — était **une sur-contrainte de ma
part**, et l'agent l'a signalée au lieu de dévier en silence. Je l'ai levée.

**Décision, et elle est mienne** : les emplacements qui sont des **styles d'éléments** appartiennent à
`base.css` et sont livrés par cette issue ; ceux qui sont des **composants** appartiennent aux epics
qui les créent.

| # | Emplacement §2.1 | Nature | Statut |
|---|---|---|---|
| 1 | Sous le `<h1>` de page | élément | ✅ livré (`base.css`) |
| 2 | Sous chaque `<h2>`, segment de 6 rem | élément | ✅ livré |
| 3 | À la place de chaque `<hr>` | élément | ✅ livré, avec contre-mesure `hr.wp-block-separator` |
| 4 | Bord gauche d'une citation (`blockquote`), vertical | élément | ✅ livré (`--filet-double-v`) |
| 5 | Bord bas de l'en-tête du site | composant | ⏳ epic Gabarits |
| 6 | Bord haut du pied de page | composant | ⏳ epic Gabarits |
| 7 | Bord bas d'un bandeau photo pleine largeur | composant | ⏳ epic Gabarits |
| 8 | Au-dessus de l'encart « dernière portée » si *Chiots disponibles* | composant | ⏳ issue de l'encart |

**Aucun écart à la liste close, aucun jeton concurrent, aucun neuvième emplacement.** La règle
« jamais deux fois dans le même bloc visuel » est tenue par `h2 + hr { display: none }` (§2.1 :
« un `h2` immédiatement suivi d'un `<hr>` : le `<hr>` n'est pas rendu »).

**Contrainte pour l'epic Gabarits** : les quatre emplacements restants sont les **seuls** encore
disponibles. Le filet double est interdit partout ailleurs — boutons, champs, cellules, cartes,
vignettes, éléments de menu — et deux occurrences successives exigent au moins `--e-7` d'écart.

## Amendement 5 — `h1` : marge extrapolée, à ratifier par `lead-design-mtb`

MASTER §5.1 ne donne le rapport dessus/dessous que pour `h2` (`--e-7` / `--e-4`) et `h3`
(`--e-5` / `--e-3`). **Rien pour `h1` ni pour `h4`.** Livré sans marge, le filet double du `h1` se
retrouvait à ras du bloc suivant.

**Décision (`lead-issue-mtb`)** : `h1 { margin-block: var(--e-7) var(--e-5) }`. C'est l'extrapolation
**minimale** du principe explicite et universel de §5.1 — « l'espace au-dessus d'un titre est toujours
plus grand que l'espace au-dessous, un titre appartient à ce qui le suit » — et `h1` étant plus grand
que `h2`, son espace au-dessous ne peut pas être plus serré que le `--e-4` de `h2`.

**`h4` reste à zéro** : c'est une étiquette, et l'absence de marge n'y produit pas le défaut visible
qu'on corrige sur `h1`. Je préfère une lacune nommée à une seconde valeur inventée.

**À porter dans MASTER §5.1 par `lead-design-mtb`** : les rapports de `h1` et `h4`, plus les points
laissés ouverts par cette issue — le « §9.6 » auquel §7.6 renvoie et qui n'existe pas (donc **aucune
feuille d'impression n'est spécifiée**, alors qu'une fiche portée est imprimée pour une famille), et
les **deux paires de contraste** prescrites par §8 mais absentes du tableau §12 : `--sauge` sur
`--blanc` (5,69:1, bord de champ au focus) et `--laiton` sur `--pin` (4,05:1, cerne du bouton
survolé), toutes deux non textuelles et au-delà des 3:1 du critère 1.4.11.

## Amendement 6 — Poids des polices : écart assumé, chiffré, avec une piste non retenue

**Livré : `newsreader-var-latin.woff2` 124 184 o + `public-sans-var-latin.woff2` 23 364 o =
147 548 octets (144,1 Ko).** MASTER §4.1 pose une **cible** de ≤ 100 Ko pour les deux réunis.

**Ce qui est tenu, et ce qui ne l'est pas** — la distinction est le cœur de l'arbitrage :

- **BRIEF §12, contrainte dure : « 2 fichiers de police maximum » → TENUE.**
- **BRIEF §12, budget chiffré : « HTML + CSS + JS < 200 Ko » → TENU** (≈ 29 Ko). Les polices n'entrent
  pas dans ce budget, que le brief énonce séparément.
- **MASTER §4.1, cible ≤ 100 Ko → DÉPASSÉE de 45 148 o.** MASTER l'écrit « **Cible de poids** », pas
  plafond.

**Pistes mesurées** (par `dev-ux-mtb`, sur les fichiers réels) :

| Piste | Total | Verdict |
|---|---|---|
| Livré — strictement conforme au jeu de caractères de §4.1 | 147 548 | référence |
| Retirer `mark` + `mkmk` | 147 348 | ne rapporte rien |
| Retirer `kern` | 109 700 | crénage perdu **et** toujours hors cible |
| `wght` restreint (400–600 / 400–700) | 107 704 | toujours hors cible |
| `wght` restreint **+** `opsz` bridé à 14–36 | 97 344 | **sous la cible, mais refusée** |
| `opsz` épinglé | 75 828 | exclue, contredit §4.2 |

**Refus motivé de la seule piste qui passait** : brider `opsz` à 14–36 alors que les `h1` de bandeau
montent à 80 px (`--t-3xl`) revient à payer la cible avec **exactement la propriété qui justifiait de
choisir Newsreader** — MASTER §4.1 en fait l'argument central (« un seul fichier qui se comporte comme
deux polices »). Mauvais échange.

**Piste supplémentaire mesurée par `lead-issue-mtb`, NON appliquée, disponible en une passe** :
resserrer le **jeu de glyphes** au français réellement employé (155 glyphes : ASCII imprimable + les
accents français + la ponctuation typographique énumérée au §4.1, `№` et `−` compris) au lieu de la
plage `U+0000-00FF` du contrat, **sans toucher à aucun axe** :

| Variante | Newsreader | Public Sans | Total | Axes |
|---|---|---|---|---|
| **Fichiers livrés** (jeu du contrat) | 124 184 | 23 364 | **147 548** | intacts |
| **Jeu français strict, 155 glyphes** | 97 576 | 18 808 | **116 384** | **intacts** |
| Jeu français minimal, 144 glyphes | 91 876 | 17 556 | 109 432 | intacts |

*Note de mesure* : la re-passe de contrôle du jeu du contrat a rendu 124 128 + 23 404 = 147 532 o,
soit 16 octets d'écart avec les fichiers livrés — différence de drapeaux entre deux exécutions de
`pyftsubset`, sans portée. **Les chiffres qui font foi sont ceux des fichiers réellement livrés :
124 184 + 23 364 = 147 548 octets**, cohérents avec `assets/fonts/polices.md` et avec la décision 14
de `docs/ETAT.md`.

Soit **−31 164 o (−21 %) sans aucun coût de design** : `wght 200–800` et `opsz 6–72` restent entiers.
Elle **ne suffit pas** à atteindre 100 Ko — le plancher à axes intacts est ~109 Ko — ce qui **confirme
que la cible est inatteignable sans sacrifier l'axe optique**, et donc que le refus ci-dessus est le
bon. Elle est consignée ici parce qu'elle est disponible à faible coût si l'on veut réduire l'écart.

**Décision (orchestrateur, 2026-08-16, décision 14 de `docs/ETAT.md`) : on garde les fichiers tels que
livrés.** Ce qui protège réellement le public du brief (§2, §11 : personnes âgées sur mobile), c'est le
**préchargement**, la **même origine** et `font-display` — pas 20 Ko de moins.

**Mitigations en place, à vérifier à chaque issue qui touche les polices** : deux fichiers seulement,
servis en même origine depuis `assets/fonts/` ; **aucun `@import`, aucun `url()` distant** (vérifié :
les seuls `url()` du CSS sont `../fonts/newsreader-var-latin.woff2` et
`../fonts/public-sans-var-latin.woff2`) ; préchargement des deux avec `crossorigin="anonymous"`,
conditionné à `file_exists()` ; `font-display` réglé pour que le poids ne bloque jamais le premier
rendu du texte ; polices de repli métriquement ajustées (`size-adjust` mesurés, non inventés).
