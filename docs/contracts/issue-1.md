# Contrat d'interface — Issue #1 — Extension `mtb-core` : squelette et auto-enregistrement

**Gelé le 2026-08-15.** Premier contrat du projet. Il ne décrit pas une fonctionnalité : il décrit
**la façon dont les ~20 issues suivantes se branchent sur l'extension**. Toute chaîne qui écrit une
ligne dans `wp-content/plugins/mtb-core/` le lit d'abord.

Il est la conséquence directe de la **décision 9 de `docs/ETAT.md`** : aucun index central à éditer à
la main, parce que trois chaînes tournent en parallèle sur un arbre de travail unique, sans branche
pour rattraper une collision.

**Contrepartie assumée et à tenir** : puisque aucun fichier ne liste les modules, *rien dans le code
ne dit ce qui existe*. L'inventaire de la section 11 est la carte du plugin. **Chaque issue qui livre
un module l'y ajoute** — c'est la seule ligne de ce document qu'une issue future a le droit de
modifier.

---

## 1. La règle fondatrice

> **Un module = un dossier.** `includes/<groupe>/<module>/bootstrap.php`
>
> **Aucune issue ne modifie jamais `mtb-core.php` ni `includes/class-loader.php`.**

Le chargeur parcourt deux niveaux et inclut **un seul fichier conventionnel par module** :
`bootstrap.php`. Le reste du module (`champs.php`, `render.php`, `class-*.php`) est inclus par le
module lui-même, quand il en a besoin.

- Dossier **sans** `bootstrap.php` → ignoré, avec une note journalisée si `WP_DEBUG` (pour qu'une
  chaîne future ne perde pas dix minutes sur une absence silencieuse).
- Dossier préfixé `_` → ignoré volontairement. C'est la façon de désactiver un module sans le
  supprimer.
- Dossier hors des six groupes → jamais chargé. La liste des groupes est **close, sans filtre**.

Approches écartées et pourquoi, pour qu'on ne les re-litige pas : l'inclusion récursive de tout
`*.php` (elle chargerait à vide les `render.php` de blocs, que WordPress inclut lui-même au rendu dans
une portée de variables précise — bug réel, pas théorique) ; un autoloader PSR-4 maison + interface
(sur-dimensionné pour ~35 modules connus d'avance, et le chargement paresseux n'achète rien puisque
tous les modules doivent accrocher leurs hooks à chaque requête) ; un manifeste mis en cache (gain en
fractions de milliseconde contre un mode de panne détestable — « j'ai déposé mon dossier en
production, rien n'apparaît » — au moment précis où personne n'a de console).

## 2. Les six groupes, leur ordre, leur hook

Ordre de parcours du chargeur, déterministe pour la reproductibilité :

`content` → `fields` → `query` → `blocks` → `admin` → `migration`

**Un module ne doit jamais dépendre de cet ordre.** Le séquencement réel passe par les hooks
WordPress, et par eux seuls. C'est ce qui fait qu'un bloc n'a pas besoin qu'un type de contenu soit
chargé avant lui : il en a besoin **au rendu**, bien après `init`.

| Groupe | Ce que le module contient | Hook et priorité imposés |
|---|---|---|
| `content` | `register_post_type`, `register_taxonomy`, `register_post_meta` | `init` **10** |
| `fields` | écrans de saisie en français, validation, sauvegarde | `add_meta_boxes`, `save_post_<type>`, `enqueue_block_editor_assets` ; enregistrement de scripts sur `init` **20** |
| `query` | fonctions de lecture `mtb_*` exposées au thème | **aucun hook** — simples déclarations de fonctions |
| `blocks` | `register_block_type`, `block.json`, `render.php` | `init` **20** |
| `admin` | colonnes de liste, filtres, menus, aide contextuelle | `admin_menu`, `admin_init`, `manage_*_columns`, `restrict_manage_posts` |
| `migration` | commandes WP-CLI, imports ponctuels | déclaration à l'inclusion, sous garde `WP_CLI` |

- `init` **99 est réservée au chargeur** (synchronisation de version et des règles de réécriture).
  Aucun module ne l'utilise.
- **Aucun module n'appelle jamais `flush_rewrite_rules()`.** Le chargeur s'en charge, une seule fois,
  quand la liste des types de contenu change (section 4).
- Un module porte **le même nom de dossier** dans `content/` et dans `fields/` : `content/portee/` et
  `fields/portee/` sont les deux moitiés du même sujet. `fields/` accueille en plus les **contrôles
  partagés** entre types (le contrôle « père : fiche ou nom libre », le contrôle galerie).

**Nom de dossier** : vocabulaire métier, minuscules, tirets, sans accent — `portee`, `chien`,
`resultat-travail`, `derniere-portee`, `grille-chiens`. Jamais un nom technique.

## 3. Ce qu'un `bootstrap.php` a le droit de faire **à l'inclusion**

**Autorisé, et rien d'autre** : `add_action`, `add_filter`, `define`, `require_once` de ses propres
fichiers, déclarations de fonctions et de classes, et les gardes de sortie anticipée
`if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) { return; }` / `if ( ! is_admin() ) { return; }`.

**Interdit à l'inclusion** :

- tout accès à la base : `get_option`, `get_posts`, `WP_Query`, `get_terms` ;
- `register_post_type`, `register_taxonomy`, `register_block_type`, `register_post_meta` **hors d'un
  rappel de hook** ;
- **toute fonction de traduction — `__()`, `_e()`, `_x()`, `_n()`, `esc_html__()` — et plus
  généralement tout appel de traduction avant le hook `init`**, `plugins_loaded` et
  `after_setup_theme` compris. WordPress 6.7+ y répond par un `_doing_it_wrong` « Translation loading
  … was triggered too early », précisément l'avertissement que la DoD interdit. L'avertissement vise
  **le moment**, pas la portée : un rappel sur `plugins_loaded` est frappé aussi ;
- **toute fonction remplaçable (« pluggable ») : `wp_get_current_user()`, `is_user_logged_in()`,
  `wp_mail()`, `wp_redirect()`.** Elles ne sont pas encore définies quand les extensions se chargent —
  l'appel est une erreur fatale immédiate ;
- toute sortie : `echo`, `print`, `printf`, HTML hors balises PHP, et jusqu'à l'espace ou la ligne
  vide après un `?>` final (il n'y a pas de `?>` final) ;
- `session_start`, `header()`, `ini_set`, `set_error_handler` ;
- tout appel HTTP sortant (D6, contrainte transverse « zéro domaine tiers »).

## 4. Cycle de vie : pas de hook d'activation, une empreinte

**Aucun `register_activation_hook`, aucun `register_deactivation_hook`, aucun `uninstall.php`.**

- *Pas de hook d'activation* : le mode de déploiement le plus probable en production (mutualisé,
  Q5 encore ouverte) est un dépôt FTP ou un `git pull` **sans désactiver-réactiver l'extension**. Une
  architecture qui pose ses règles de réécriture à l'activation livrerait un site dont les URL de
  portées répondent 404 après la première mise en ligne. L'absence de code est aussi la seule garantie
  qu'aucune erreur ne survient à l'activation (tâche « activation/désactivation propres »).
- *Pas d'`uninstall.php`* : **rien dans cette extension ne doit pouvoir supprimer une portée** (D4,
  contrainte 4 « rien ne se perd »).

À la place, le chargeur accroche sur `init` **99** une routine qui compare une **empreinte** à
l'option `mtb_core_empreinte` :

```
empreinte = MTB_CORE_VERSION
          + signature des types de contenu et taxonomies préfixés « mtb_ »
            effectivement enregistrés à cet instant (listes triées)
```

**Pourquoi la signature, et pas seulement la version** — c'est l'arbitrage central de ce contrat.
Une comparaison à `MTB_CORE_VERSION` seule obligerait **chaque issue qui ajoute un type de contenu à
éditer `mtb-core.php`** pour incrémenter la constante, sans quoi les règles de réécriture ne seraient
jamais régénérées. Cela recréerait exactement l'index central que la décision 9 interdit, sous sa
forme la plus dangereuse : deux chaînes parallèles écrivant la même ligne, dernier arrivé gagne, en
silence. Avec la signature, l'issue #3 ajoute `mtb_portee` dans son propre dossier, la signature
change toute seule, les permaliens fonctionnent, **et aucun fichier commun n'est touché**.

Conséquence à retenir : **`mtb-core.php` est écrit une fois à l'issue #1 et plus jamais rouvert.**

Comportement de la routine : sortie immédiate si `wp_installing()` (c'est la réponse à « pas d'erreur
si la base n'est pas encore prête ») ; si l'empreinte est identique, retour immédiat ; sinon
`do_action( 'mtb_core_mise_a_jour', … )`, puis `flush_rewrite_rules( false )`, puis écriture de
l'option. Le paramètre `false` est délibéré : `true` réécrirait `.htaccess`, ce qui suppose un
système de fichiers accessible en écriture — fausse hypothèse sur du mutualisé durci.

## 5. Surface globale — close

Quatre constantes, définies par `define()` (et non `const`, qui les enfermerait dans l'espace de noms
`MTB\Core` et les rendrait invisibles d'un `defined()`) :

`MTB_CORE_VERSION` · `MTB_CORE_FILE` · `MTB_CORE_DIR` · `MTB_CORE_URL`

Plus les fonctions de lecture `mtb_*` de `includes/query/`. **Rien d'autre ne sort dans l'espace
global. Aucune variable globale, jamais.**

Un seul hook est exposé par cette issue :

| Hook | Type | Signature | Usage |
|---|---|---|---|
| `mtb_core_mise_a_jour` | action | `( ?array $ancienne_empreinte, array $nouvelle_empreinte )` | Se déclenche une fois quand la version ou la liste des types de contenu change. **Seul point d'accroche pour une migration de données** d'une issue future. Le thème ne l'utilise pas. |

Le chargeur n'offre **aucun filtre** sur la liste des groupes : la surface est close volontairement.

## 6. Nommage — gelé pour tout le projet

| Élément | Règle | Exemple |
|---|---|---|
| Espace de noms PHP | `MTB\Core\<Groupe>\<Module>` en PascalCase | `MTB\Core\Content\Portee` |
| **Exception unique** | les **fonctions de lecture publiques** sont déclarées dans l'**espace de noms global**, préfixées `mtb_`, sous `if ( ! function_exists( … ) )` | `mtb_get_derniere_portee()` |
| Type de contenu | `mtb_<nom>`, **20 caractères maximum** (limite dure de `register_post_type` pour rester manipulable par WP-CLI et les hooks dynamiques ; `mtb_resultat_travail` fait exactement 20) | `mtb_portee`, `mtb_chien` |
| Taxonomie | `mtb_<nom>` (32 max) | `mtb_discipline` |
| Clé de méta | `_mtb_<champ>`, **tiret bas initial obligatoire** | `_mtb_date_naissance` |
| Bloc | `mtb/<nom-en-tirets>` | `mtb/derniere-portee` |
| Option | `mtb_core_<nom>` | `mtb_core_empreinte` |
| Hook maison | `mtb_core_<nom>` ou `mtb_<domaine>_<nom>` | `mtb_core_mise_a_jour` |
| Poignée de script/style | `mtb-<module>-<usage>` | `mtb-derniere-portee-editeur` |
| Action de nonce | `mtb_<module>_<ecran>` | `mtb_portee_chiots` |

**Le tiret bas initial des clés de méta n'est pas cosmétique** : il rend la méta *protégée*, donc
jamais listée par WordPress dans le panneau « Champs personnalisés ». C'est la garantie mécanique
qu'aucune clé technique n'atteint l'écran de l'éleveuse — `champ personnalisé` et `métadonnée` sont
des mots interdits à l'écran (`MASTER.md` §10.4). Impose un `auth_callback` explicite dans
`register_post_meta`.

## 7. Chaînes de caractères — français littéral, aucune fonction i18n

**Le texte de l'interface est écrit en français littéral dans le code. Aucune fonction de traduction
n'est utilisée nulle part dans `mtb-core`.**

Motifs : le site est monolingue français, aucun fichier `.mo` n'existe ni n'existera, `__()` sans
catalogue n'est qu'un appel de fonction sans effet, et l'absence totale d'i18n supprime **par
construction** toute la classe de bugs « translation loading triggered too early » (section 3).
L'en-tête `Text Domain: mtb-core` est conservé — exigé par la tâche de l'issue, inoffensif.

Corollaire : l'échappement en sortie utilise `esc_html()`, `esc_attr()`, `esc_url()`,
`wp_kses_post()` — **jamais** les variantes `*__()` ou `*_e()`.

Rouvrable, mais au prix d'une reprise de chaque module livré : c'est pourquoi c'est gelé ici et pas
découvert à l'issue #6.

## 8. Frontière thème / extension — vérifiable par `grep`

> **Un fichier de `wp-content/themes/mtb/` qui contient `WP_Query`, `get_post_meta`, `get_posts`,
> `get_terms` ou `MTB\` est en infraction.**

C'est la formulation exacte à reprendre en revue. Elle fonctionne parce que les fonctions de lecture
publiques sont déclarées dans l'espace de noms **global** : un thème conforme n'a jamais besoin
d'écrire `MTB\`.

**Ce que le thème ne fait jamais** :

- interroger la base, sous quelque forme que ce soit ;
- **composer une chaîne du domaine** — « 3 mâles, 2 femelles », un libellé de disponibilité, un nom de
  discipline. Le serveur la fournit finie, le thème l'imprime ;
- reformater une valeur de test de santé, un numéro LOF, une cotation : ces valeurs sont **recopiées,
  jamais normalisées** (décision 12) ;
- reformater une date : la fonction de lecture fournit **à la fois** la valeur brute et la valeur déjà
  formatée selon les réglages du site ;
- décider de ce qu'est « la dernière portée » ;
- appeler une classe ou une méthode de `MTB\Core\*`.

**Obligation symétrique, à porter par l'issue #2 (thème)** : le thème appelle toujours une fonction de
lecture derrière un `function_exists()`, pour qu'une extension désactivée donne une page dégradée et
non un écran blanc (D12).

**Et l'extension, en retour, n'émet aucune règle visuelle ni mise en page.** Un bloc rend une
structure et des crochets de classes — `mtb-<bloc>` et `mtb-<bloc>__<element>` — **aucun style en
ligne, aucune décision visuelle**. Le thème habille.

## 9. États spéciaux — identifiants figés maintenant

Les fonctions de lecture et les blocs signaleront ces cas avec **exactement** ces identifiants, dès
les issues suivantes. Le thème doit savoir les rendre.

| État | Émis par le serveur | Rendu par le thème |
|---|---|---|
| `aucune_portee` | Aucune portée publiée | État vide propre dans l'encart d'accueil ; le composant ne casse pas la page (D12) |
| `donnee_absente` | Le champ existe mais n'a pas été rempli. Le serveur fournit le libellé **« Non renseigné »** (`MASTER.md` §10.3) | Imprime le libellé fourni. **N'invente jamais un tiret** ni une mention de son cru |
| `parent_hors_elevage` | Le père ou la mère est un nom libre + élevage, sans fiche | Pas de lien ; **l'affichage ne change pas de forme** |
| `page_protegee` | Contenu protégé par mot de passe natif WordPress | Exclu des index publics, du sitemap et de la recherche (BRIEF §8) |

Règles de toute fonction de lecture, gelées ici :

- elle **n'imprime jamais rien** et ne renvoie jamais de HTML ;
- elle définit **explicitement ce qu'elle renvoie quand la donnée manque** — `null`, tableau vide, ou
  clé présente à `''` : jamais un avertissement PHP, jamais une clé absente que le thème devrait
  tester à l'aveugle ;
- elle peut utiliser `wp_cache_get`/`wp_cache_set` ; **elle n'utilise pas de transient** sans
  justification écrite dans son issue — un transient périmé après modification d'une portée est
  exactement l'échec de « saisi une fois, affiché partout » (contrainte 3) ;
- elle est la **seule** manière pour le thème d'obtenir une donnée.

## 10. Recettes de module, par groupe

Structure et signatures ; aucune implémentation. **L'issue #1 n'en livre aucune** — ce sont les
gabarits que les issues suivantes suivent.

### `includes/content/portee/` (issue #3)

```
content/portee/
├── bootstrap.php      namespace MTB\Core\Content\Portee
│                      add_action( 'init', …, 10 ) → enregistrer(): void
│                      require_once __DIR__ . '/champs.php'
└── champs.php         register_post_meta() par champ, même rappel init 10
```

Aucun libellé n'est construit par concaténation : chaque forme (singulier, pluriel, « Ajouter une
portée ») est écrite en toutes lettres, avec les libellés exacts de `MASTER.md` §10.2.

### `includes/fields/portee/` (issue #3)

```
fields/portee/
├── bootstrap.php      add_action( 'add_meta_boxes', … )
│                      add_action( 'save_post_mtb_portee', …, 10, 3 )
├── ecran.php          rendu des champs — échappement AU RENDU
└── sauvegarde.php     enregistrer_champs( int $post_id, \WP_Post $post, bool $mise_a_jour ): void
```

**Toute sauvegarde, dans cet ordre, sans exception** : sortie si `wp_is_post_autosave()` ou
`wp_is_post_revision()` → `wp_verify_nonce()` → `current_user_can( 'edit_post', $post_id )` →
assainissement champ par champ à la frontière → écriture. Un champ absent du `$_POST` est traité
comme vide, jamais comme « ne pas toucher », sauf décision contraire documentée dans l'issue.

### `includes/query/derniere-portee/`

```
query/derniere-portee/
└── bootstrap.php      PAS d'espace de noms — espace global, volontairement
                       if ( ! function_exists( 'mtb_get_derniere_portee' ) ) { … }
                       function mtb_get_derniere_portee(): ?array
```

### `includes/blocks/derniere-portee/`

```
blocks/derniere-portee/
├── bootstrap.php      add_action( 'init', …, 20 )
│                        wp_register_script( 'mtb-derniere-portee-editeur',
│                            MTB_CORE_URL . 'includes/blocks/derniere-portee/editeur.js',
│                            array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components' ),
│                            MTB_CORE_VERSION, true )
│                        register_block_type( __DIR__ )
├── block.json         apiVersion 3 · name "mtb/derniere-portee" · title en français ·
│                      category "mtb" · "render": "file:./render.php" ·
│                      "editorScript": "mtb-derniere-portee-editeur"  ← une POIGNÉE, pas un "file:"
├── render.php         reçoit $attributes, $content, $block — inclus par WordPress,
│                      JAMAIS par le chargeur
└── editeur.js         JavaScript simple : wp.blocks.registerBlockType + wp.element.createElement
```

**Aucune étape de build JavaScript, jamais** — pas de `npm`, pas de `@wordpress/scripts`, pas de JSX,
pas de dossier `build/`. C'est l'esprit de la contrainte 2 (aucune dépendance, portabilité sur
mutualisé, lisible dans six ans) et cela garde le montage Docker qui sert les sources telles quelles.
Deux conséquences à connaître **avant** d'écrire le premier bloc :

1. **`"editorScript": "file:./editeur.js"` est à proscrire.** WordPress chercherait un
   `editeur.asset.php` voisin, normalement produit par `@wordpress/scripts`, et émettrait un
   `_doing_it_wrong` s'il ne le trouve pas — l'avertissement interdit. La parade sans build est
   celle du squelette ci-dessus : enregistrer le script soi-même avec `wp_register_script()` et ne
   mettre que la **poignée** dans `block.json`. Officiellement supporté, strictement équivalent.
2. **Un bloc enregistré côté PHP seul n'apparaît pas dans l'inséreur.** Il faut un
   `registerBlockType` côté client ; sans JSX il s'écrit avec `wp.element.createElement`. À vérifier
   concrètement au premier bloc livré ; si l'observation contredit ce point sur la version de
   WordPress installée, **le remonter plutôt que de contourner**.

La **catégorie de blocs `mtb`** (filtre `block_categories_all`) est livrée **une seule fois**, par la
première issue de composants, dans `includes/blocks/categorie-mtb/`. Les suivantes s'y raccrochent et
ne la redéclarent pas. Nom affiché retenu : **« Mont Brabant »** — repris du nom de l'élevage établi
(`ETAT.md`, faits du domaine), donc pas inventé ; confirmable par l'éleveuse sans rien casser.

Rappel de la **décision 10 de `ETAT.md`**, applicable à tout composant tableau : chaque `<td>` porte
`data-libelle="…"` avec exactement le libellé de colonne de `MASTER.md` §10.

### `includes/admin/colonnes-portee/`

```
admin/colonnes-portee/
└── bootstrap.php      if ( ! is_admin() ) { return; }
                       add_filter( 'manage_mtb_portee_posts_columns', … )
                       add_action( 'manage_mtb_portee_posts_custom_column', …, 10, 2 )
```

Aucun module `admin/` n'appelle `add_cap()` sur le rôle `editor` si ce n'est pas l'objet déclaré de
son issue. L'éleveuse reste sur le rôle natif **Éditeur**, qui n'a ni `activate_plugins`, ni
`edit_plugins`, ni `edit_themes`, ni `manage_options`.

### `includes/migration/import-fixtures/` — **à ne pas implémenter dans l'issue #1**

```
migration/import-fixtures/
└── bootstrap.php      if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) { return; }
                       \WP_CLI::add_command( 'mtb import-fixtures', …,
                           array( 'shortdesc' => …, 'synopsis' => … ) )
```

`WP_CLI::add_command( 'mtb …' )` crée l'espace de noms parent `mtb` tout seul : **aucune classe de
commande centrale n'est nécessaire**, donc la décision 9 tient aussi côté CLI, et une future
`wp mtb import-ancien-site` s'ajoutera sans toucher un fichier commun.

**Signature exacte attendue par `docker/provision/provision.sh` (lignes 129-138), à respecter au
caractère près :**

- sonde d'existence : `wp mtb import-fixtures --help` — doit sortir en code 0 dès que la commande est
  enregistrée ; fournir `shortdesc` et `synopsis` garantit ce comportement ;
- appel réel : `wp mtb import-fixtures --portees=/fixtures/portees.json --chiens=/fixtures/chiens.json --resultats=/fixtures/resultats.json`

Contraintes pour l'issue qui la livrera : les trois options sont **facultatives** (un appel partiel
fonctionne) ; l'import est **rejouable sans doublon**, indexé sur `identifiant` pour une portée et sur
le slug pour un chien ; **il ne supprime jamais** ; un échec passe par `WP_CLI::error()` (code 1), que
`provision.sh` récupère par `|| log AVERTISSEMENT`. Forme des fixtures :
`docker/fixtures/portees.json` — noter `pere`/`mere` avec `"type": "fiche_chien"` ou
`"type": "nom_libre"`, qui préfigure l'état `parent_hors_elevage` de la section 9.

**Cette commande appartient aux issues #3/#4/#5 ou à une issue `infra` dédiée. L'issue #1 n'en écrit
pas une ligne** (empreinte fichiers).

## 11. Inventaire des modules — à tenir à jour par chaque issue

C'est la carte du plugin, puisque le code n'en contient aucune.

| Groupe | Module | Issue | Rôle |
|---|---|---|---|
| `content/` | `portee` | #3 | Type `mtb_portee`, ses 16 clés de méta `_mtb_`, listes fermées et assainisseurs |
| `fields/` | `portee` | #3 | Écran de saisie classique (5 boîtes), sauvegarde, avis par transient, `ecran.js` |
| `query/` | `portee` | #3 | 6 fonctions publiques : `mtb_get_portee`, `_derniere_portee`, `_portee_par_identifiant`, `_portees`, `_portees_du_chien`, `_portee_voisine` |
| `content/` | `chien` | #4 | Type `mtb_chien`, ses clés `_mtb_`, `choix.php` (listes fermées + accord au sexe), `assainissement.php` |
| `fields/` | `chien` | #4 | Écran de saisie classique (6 sections + adresse), sauvegarde, avis, `statut.js`, `galerie.js` |
| `query/` | `chien` | #4 | 2 fonctions publiques : `mtb_get_chien`, `mtb_get_chiens_par_statut` |
| `content/` | `resultat` | #5 | Type `mtb_resultat`, ses 8 clés `_mtb_`, `assainissement.php` |
| `fields/` | `resultat` | #5 | Écran de saisie en un seul bloc, titre composé au serveur, sauvegarde, messages d'enregistrement |
| `query/` | `resultat` | #5 | 4 fonctions publiques : `mtb_resultat_disciplines`, `mtb_resultat_sexes`, `mtb_get_resultats_travail_par_discipline`, `mtb_get_resultats_travail_du_chien` |

> Tenu à jour par `/lead-mtb` à la clôture de chaque lot, ce fichier étant hors de l'empreinte de
> toute chaîne. Dernière mise à jour : clôture du lot 2 (issues #3, #4, #5).

## 12. Conventions de code — gelées pour les 20 issues

- `declare(strict_types=1);` en tête de **tous** les fichiers PHP, après le docbloc, avant le
  `namespace`.
- Standards WordPress : indentation par tabulations, espaces à l'intérieur des parenthèses,
  conditions de Yoda (`if ( false === $x )`), **`array()` et non `[]`** (le standard
  `WordPress-Core` interdit la syntaxe courte via `Universal.Arrays.DisallowShortArraySyntax`), pas
  de balise fermante `?>`, une ligne vide finale.
- Nommage de fichiers : `class-<nom>.php` pour un fichier de classe, minuscules et tirets sinon,
  `bootstrap.php` invariable.
- Docbloc de fichier avec `@package MTB\Core` sur chaque fichier.
- Méthodes et fonctions en `snake_case` français, classes en `PascalCase`, constantes en
  `MAJUSCULES_AVEC_TIRETS_BAS`.
- Licence **GPL v2 or later**, déclarée dans l'en-tête du plugin ; pas d'en-tête de licence répété
  fichier par fichier.
- `error_log()` n'est appelé qu'à un seul endroit, dans `Loader::journaliser()`, avec son
  `phpcs:ignore` et sa justification.
- Échappement **au rendu**, jamais en amont. Assainissement **à la frontière d'entrée**.
- **Plafond de syntaxe : PHP 8.1**, pas « PHP 8 » en général. Piège rencontré à l'issue #1 : les
  **constantes de classe typées** (`public const array GROUPES`) datent de PHP **8.3** et provoquent
  une erreur fatale d'analyse sur 8.1. Le type se porte par un `@var` en docbloc. Même vigilance pour
  toute syntaxe 8.2/8.3 (`readonly` de classe, types `true`/`null` autonomes).
- **En-tête d'extension : une ligne par champ.** WordPress ne lit pas un champ replié sur plusieurs
  lignes — une `Description:` sur trois lignes est tronquée à la première dans la liste des
  extensions.

### Le cas particulier de `mtb-core.php`

`mtb-core.php` porte un garde-fou `version_compare( PHP_VERSION, '8.1', '<' )` qui rend la main avec
une notice d'administration avant d'inclure quoi que ce soit. **PHP compile un fichier entier avant
d'en exécuter la première ligne** : le `return` du garde-fou ne protège donc **que les fichiers
inclus après lui**, jamais le reste de `mtb-core.php`.

Règle opérationnelle qui en découle : **`mtb-core.php` en entier doit être analysable par PHP 7.0**
(le plancher de WordPress 6.5 lui-même). Sont donc interdits *dans ce seul fichier* : types d'union,
`?->`, `match`, `readonly`, énumérations, promotion de propriétés de constructeur, arguments nommés,
virgule finale en déclaration de paramètres, appelables de première classe, attributs `#[…]`,
`str_contains`/`str_starts_with`, et — piège discret — **le type de retour `void`, qui date de PHP
7.1**. `declare(strict_types=1)` existe depuis PHP 7.0 : il reste compatible.

C'est la raison pour laquelle **`mtb-core.php` ne déclare aucune fonction ni classe** : il ne contient
qu'une fermeture anonyme pour la notice. Aucune tentation, aucune erreur possible.

`class-loader.php` et les ~35 futurs modules vivent dans des fichiers inclus **après** le garde-fou et
utilisent PHP 8.1 sans réserve.

### Ce que le `try/catch` du chargeur attrape réellement

À dire honnêtement, parce qu'un module futur ne doit **pas** s'en croire protégé.

**Attrapé** par `try { require_once … } catch ( \Throwable )` : une `Exception` ou une `Error` levée à
l'inclusion du module — appel d'une fonction inexistante, `TypeError`, division par zéro, exception
volontaire.

**Non attrapé, et donc site entier par terre** :

- **une erreur de syntaxe dans le `bootstrap.php` d'un module.** Point vérifié
  expérimentalement sur PHP 8.1 lors de la livraison de l'issue #1 : un module non analysable produit
  un `Parse error: syntax error …` brut — **pas** un `Fatal error: Uncaught ParseError` — donc rien
  n'est intercepté et la réponse HTTP tombe. Un `require` d'un fichier non analysable reste un
  `E_COMPILE_ERROR` ; seul `eval()` lève un `ParseError` rattrapable. *(La première rédaction de ce
  contrat affirmait l'inverse, sur la foi du fait que `ParseError` hérite de `Throwable` ; c'était
  faux et c'est corrigé ici.)*
- une erreur d'analyse dans `mtb-core.php` ou `class-loader.php` eux-mêmes ;
- les autres `E_COMPILE_ERROR` : `Cannot redeclare function …` quand deux modules déclarent la même
  fonction `mtb_*`, `require` d'un fichier absent **à l'intérieur** d'un module, dépassement de
  mémoire ;
- **tout ce qui casse plus tard** — un module qui s'inclut proprement puis fait exploser son rappel
  sur `init` ou `save_post`. Le chargeur n'offre aucune protection au-delà de l'inclusion.

Les `E_WARNING`/`E_DEPRECATED` ne sont pas des `Throwable` : ils ne sont pas capturés et s'afficheront
en `WP_DEBUG`. C'est voulu — les masquer serait contraire à la tâche « sans notice ni warning ».

> **Le chargeur isole un module qui lève une exception à l'inclusion. Il n'isole ni une faute de
> frappe, ni un module qui plante à l'usage.**
> Le seul vrai filet est la vérification manuelle en Docker à chaque livraison, et le mode de
> récupération d'erreur fatale de WordPress, qui désactivera l'extension plutôt que de laisser le
> site blanc.

## 13. Interdits

- Le thème n'interroge jamais la base directement.
- Le thème ne compose jamais une chaîne métier ni ne reformate une valeur de santé, un LOF, une
  cotation ou une date.
- L'extension n'émet aucune règle visuelle ni mise en page.
- Aucune issue n'édite `mtb-core.php` ni `includes/class-loader.php`.
- Aucun module n'appelle `flush_rewrite_rules()`, n'utilise `init` 99, ni ne dépend de l'ordre de
  parcours des groupes.
- Aucune étape de build (npm, JSX, `build/`). Aucune dépendance Composer.
- Aucun appel HTTP sortant, aucun domaine tiers.
- Aucun `uninstall.php`, aucune suppression de contenu par l'extension.
- Aucune variable globale.

## 14. Arbitrages

| # | Désaccord | Décision | Raison |
|---|---|---|---|
| 1 | Mécanisme de découverte : parcours vs récursif vs autoloader vs manifeste caché | **Un `bootstrap.php` conventionnel par module, parcours à deux niveaux, sans cache** | Trois étapes pour une chaîne future, un seul concept à documenter, alignement naturel sur `register_block_type( __DIR__ )`. Les alternatives et leurs défauts sont en section 1. |
| 2 | Étape de build JavaScript pour les blocs | **Aucune, jamais** | Esprit de la contrainte 2 : zéro dépendance, portabilité mutualisé, lisible dans six ans. Gelé maintenant parce que ~10 composants seront écrits par des chaînes différentes qui ne se verront jamais. Coût assumé : code d'éditeur plus verbeux (section 10). |
| 3 | Plancher de versions | **`Requires PHP: 8.1`, `Requires at least: 6.5`** | PHP 8.1 = plancher de la stack, aligné sur le mutualisé français visé. WordPress 6.5 donne les modules de script tout en restant très en dessous de ce que fait tourner une installation de 2026. *(La propriété `render` de `block.json` date en fait de 6.1 — ce sont les modules de script qui justifient 6.5.)* |
| 4 | Fusionner `content/` et `fields/` ? | **Non, l'arborescence de `CLAUDE.md` est conservée** | `CLAUDE.md` décrit explicitement la séparation ; elle n'a aucun coût de collision (deux chaînes parallèles ne partagent jamais un nom de module) et elle sert la frontière que le squelette doit rendre évidente. Un module porte le même nom de dossier dans les deux groupes. *Alternative écartée : `content/<module>/` portant aussi ses champs — un seul dossier par concept, mais en déviation d'un fichier gelé.* |
| 5 | Déclencheur de la routine de version | **Empreinte = version + signature des types de contenu enregistrés**, et non `MTB_CORE_VERSION` seule | Correction apportée par `leaddev-back-mtb` à mon arbitrage initial, et acceptée : la version seule aurait obligé chaque issue à éditer `mtb-core.php`, recréant l'index central interdit par la décision 9 sous sa forme la plus dangereuse. Détail en section 4. |
| 6 | Internationalisation | **Français littéral, aucune fonction i18n** | Site monolingue, aucun `.mo`, et suppression par construction des bugs « translation loading too early ». Rouvrable, au prix d'une reprise de chaque module livré (section 7). |
| 7 | `array()` ou `[]` | **`array()`** | Imposé par le standard `WordPress-Core` que `CLAUDE.md` invoque. Inhabituel en 2026, donc explicité ici pour que dix chaînes n'écrivent pas dans deux styles. |
| 8 | Clés de méta | **`_mtb_<champ>`, tiret bas initial obligatoire** | Rend la méta protégée, donc jamais listée dans « Champs personnalisés » : garantie mécanique qu'aucune clé technique n'atteint l'écran de l'éleveuse (`MASTER.md` §10.4). |
| 9 | Portée du `try/catch` | **Conservé, mais sa limite est documentée** | Ma formulation initiale (« une erreur de syntaxe ne se rattrape jamais ») était inexacte ; corrigée en section 12. La formulation honnête compte plus que le mécanisme. |

## 15. Points restés ouverts

- **Q5 (`ETAT.md`) — mode de déploiement en production.** Ne bloque pas : la routine de version de la
  section 4 est correcte que le déploiement se fasse par FTP ou par `git pull` + activation. La
  réponse doit être connue avant la mise en ligne, pas avant l'issue #1.
- **Durcissement contre le listage de répertoire** (`index.php` « Silence is golden » dans les
  dossiers d'`includes/`) : hors empreinte de l'issue #1. Relève de la configuration serveur et de
  l'issue `infra` de mise en ligne — **à signaler à ce moment-là, pas à contourner ici**.
- **Nom affiché de la catégorie de blocs** : « Mont Brabant » retenu, confirmable par l'éleveuse.
