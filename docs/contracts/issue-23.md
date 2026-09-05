# Contrat d'interface — Issue #23 — Page protégée par mot de passe

**Gelé le 2026-09-05.** Lot 18, epic 9. Issue **serveur seule** : le thème n'est pas touché, aucune
feuille de style n'est modifiée, aucun gabarit n'est rouvert.

Ce contrat n'a pas de moitié « thème ». Il a en revanche une section inhabituelle et qui est le cœur
du livrable : le **§4, tableau des points de sortie**, qui solde par la mesure une déclaration que le
contrat #2 avait faite sans la vérifier — la dette **T8**.

> **Ce que #23 livre en une phrase** : un module de dix-huit lignes de code et soixante-dix lignes de
> commentaire, qui retire le contenu protégé par mot de passe du plan du site, de la recherche et des
> flux ; plus la **preuve écrite** que les vingt-deux autres points de sortie du site étaient déjà
> tenus.

---

## 1. Ce qui était tranché avant ce contrat

**Q1, tranchée par l'utilisateur le 2026-09-05** (`docs/ETAT.md`, en tête ; BRIEF §15 question 1). La
page protégée porte **du texte et des tableaux réservés à certaines personnes**, « on peut faire
simplement pour le moment ». Donc :

- **une page ordinaire**, écrite comme les autres, protégée par le **mot de passe natif de
  WordPress**, que l'éditrice pose, change et retire seule (BRIEF §8) ;
- les tableaux se font au **bloc Tableau du cœur**, sans code ;
- la page n'apparaît ni dans les index, ni dans le sitemap, ni dans la recherche : **le lien et le mot
  de passe se transmettent de la main à la main** ;
- **aucune des trois pistes du BRIEF §15 n'est retenue nommément** — ni « familles de chiots », ni
  « avant-première », ni « documents d'élevage » : le contenu est laissé libre, **c'est la protection
  qui est le livrable**.

**Consigne explicite de l'utilisateur : ne pas sur-concevoir.** Ni rôles, ni comptes, ni espace
multi-pages, ni type de contenu nouveau, ni écran de saisie, tant que « pour le moment » tient.

**Le statut natif « Privé » est fermé, définitivement.** Il ferait tout cela gratuitement — le cœur
l'exclut lui-même du plan du site, de la recherche, des flux et de la REST anonyme — mais il est
interdit par le BRIEF §8 en toutes lettres : « pas de création de compte visiteur ». Un contenu privé
n'est lisible que connecté. Ne pas rouvrir.

---

## 2. Domicile du module — l'arbitrage structurant

### 2.1 L'empreinte annoncée à l'issue était irréalisable

L'issue #23 déclarait `wp-content/plugins/mtb-core/includes/privacy/page-protegee/**`.
`includes/class-loader.php:145` déclare :

```php
public const GROUPES = array( 'content', 'fields', 'query', 'blocks', 'admin', 'migration' );
```

Six noms **en dur**, liste **close par contrat** (`issue-1.md` §1 l. 36, §5 l. 174, §13, arbitrage 10 ;
`issue-27.md`, où l'ajout d'un septième groupe a été **proposé et refusé**). `charger_groupe()`
(l. 193-239) ne balaie que ces six noms.

> Un `bootstrap.php` posé sous `includes/privacy/` **n'aurait jamais été inclus** : pas d'erreur, pas
> de ligne au journal, page en 200, **module mort**. C'est la classe de défaut invisible que le projet
> traque depuis T6, T8 et #27.

**`class-loader.php` n'est pas rouvert.** La seule réouverture nominative accordée à ce jour, celle de
#27, est refermée (`issue-1.md` §13). `mtb-core.php` n'est pas ouvert non plus.

### 2.2 Décision : `includes/query/page-protegee/`

Retenu contre `includes/admin/page-protegee/`, sur une **asymétrie de danger**, chiffrée sur le code.

**Ce qui condamne `admin/`** — quatre faits vérifiés :

| Ancre | Ce qu'elle dit |
|---|---|
| `admin/description-photo/bootstrap.php:102` | `if ( ! is_admin() ) { return; }` — garde de module, sortie à l'inclusion |
| `admin/listes/bootstrap.php:65` | idem |
| `admin/corbeille/bootstrap.php:59` | « **Première garde, comme dans tout ce groupe** : rien ne s'exécute hors administration » — la norme du groupe, écrite noir sur blanc **par un module qui n'en avait pas besoin** (sa garde l. 63 est dans le rappel, pas dans la portée du fichier) |
| `admin/listes/bootstrap.php:56-63` | « Tous les crochets ci-dessous sont propres à wp-admin, **sauf `pre_get_posts`, qui court sur chaque requête — raison de plus pour ne pas l'y attacher** » |

La dernière ligne est décisive : **le module `admin/` qui ressemble le plus au nôtre a déjà tranché
contre nous, explicitement, sur le hook exact que nous posons.** Et la garde dominante du groupe
**éteindrait nos deux filtres sur la seule façade où ils servent**, en silence.

**Ce qui désigne `query/`** :

- `admin/coordonnees/bootstrap.php:5-11` **domicilie ses lectures publiques dans `query/` précisément
  pour échapper à ce réflexe**, et cite « le piège que `admin/medias/bootstrap.php` documente sur dix
  lignes ». Un contrat gelé pointe déjà la direction retenue ici.
- Dans `query/`, la déviation est **stylistique et bruyante** : un relecteur l'objectera à voix haute
  et rien ne casse. Dans `admin/`, elle serait **mortelle et muette**.

**Rectification de fait, portée au contrat parce qu'elle a servi à décider.** Le chiffre « 3 modules
`admin` sur 5 portent la garde » vient de **`brainstorm-mtb`**, qui l'a posé à l'ouverture de la
chaîne ; il a été **relayé tel quel par `lead-issue-mtb`** dans sa délégation à `leaddev-back-mtb`,
qui l'a **réfuté sur le code**. *(Il n'est pas de l'orchestrateur de lot, qui n'a jamais compté ces
gardes — attribution corrigée le 2026-09-05 ; une fausse trace de provenance dans un contrat gelé est
le défaut que la décision 64 a coûté au lot 15.)*

**Ils sont 2 sur 5** : `corbeille` ne l'a pas au niveau du fichier. La conclusion est inchangée et
l'argument est **plus fort** avec le bon chiffre, puisque `corbeille` écrit la norme du groupe sans en
avoir besoin.

### 2.3 Le prix, écrit ici pour que la revue n'ait pas à le découvrir

**La ligne `query` du §2 d'`issue-1.md` est distendue sur ses deux moitiés** :

| Ce que le §2 dit de `query` | Ce que fait `page-protegee` |
|---|---|
| « fonctions de lecture `mtb_*` exposées au thème » | **n'expose aucune fonction** — le thème ne l'appelle jamais |
| « Hook et priorité imposés : **aucun** » | **pose trois crochets** |

> **Amendement au contrat #1 §2 — `query` accroche trois crochets, déclaré ici et nulle part
> ailleurs.**
> `includes/query/page-protegee/` pose `wp_sitemaps_posts_query_args`, `pre_get_posts` et `wp_robots`,
> que le tableau du §2 d'`issue-1.md` n'assigne pas à ce groupe. **L'amendement est écrit dans le
> présent contrat, pas dans `issue-1.md`, qui n'est ouvert ni au §2 ni au §11.**
> Forme reprise du précédent du dépôt : `docs/contracts/issue-22.md:783` déclare
> « **`template_redirect` priorité 1 : amendement au contrat #1 §2** » **dans son propre contrat** ;
> `issue-1.md` §2 n'a jamais été édité pour cela, ses seuls amendements datés étant ceux de #27.
> **C'est la seule forme qui permette à deux chaînes d'amender le même §2 le même jour sans se
> voir** — et c'est exactement le cas du lot 18, où #24 en écrit un aussi.

Lecture qui fonde l'amendement : la colonne s'intitule « Hook et priorité **imposés** » — « aucun » y
dit *rien ne t'est imposé*, non *tu n'as pas le droit* ; et le §3, qui énumère ce qu'un
`bootstrap.php` a le droit de faire à l'inclusion, autorise `add_action` et `add_filter` **sans
distinguer les groupes**.

**Objection légitime, à assumer telle quelle en revue** : *« deux des quatre modules `query/`
affirment dans leur en-tête "aucun hook n'est posé" (`portee/bootstrap.php:6`,
`coordonnees/bootstrap.php:15`) ; ce module contredit une norme documentaire du groupe. »* C'est
exact. La réponse est celle du §2.2 : une impureté de forme se dit en revue, une mort silencieuse ne
se dit nulle part.

**Interdit à toute issue future** : « ranger » ce module derrière une garde de contexte à l'inclusion.
L'en-tête du fichier le dit, et le §7 le grave.

---

## 3. Le module — forme gelée

### 3.1 Arborescence

```
wp-content/plugins/mtb-core/includes/query/page-protegee/
└── bootstrap.php      namespace MTB\Core\Query\PageProtegee
                       3 accroches, 3 rappels
```

**Un seul fichier.** Trois crochets, aucune donnée. Précédent exact : `admin/medias/bootstrap.php`
(deux crochets, un fichier, un en-tête plus long que le code). Découper coûterait trois `require_once`
et trois docblocs pour vingt lignes de corps.

**Aucun `class-*.php`, aucun `block.json`, aucun `editeur.js`, aucun CSS, aucune constante globale.**

### 3.2 Accroche 1 — le plan du site

```php
add_filter( 'wp_sitemaps_posts_query_args', __NAMESPACE__ . '\\exclure_du_plan_du_site', 10, 1 );
```

| | |
|---|---|
| **Priorité** | 10 — aucune contrainte d'ordre : le cœur n'a aucun écouteur par défaut, et **#24 ne pose pas ce filtre** (§6) |
| **Arguments acceptés** | **1, et c'est structurel.** Le cœur en passe deux (`$args`, `$post_type`) ; le second est **délibérément refusé** : la règle est la même pour `page`, `post`, `mtb_portee` et `mtb_chien`. N'accepter qu'un argument rend **impossible** l'ajout d'une branche par type de contenu — la porte par laquelle un type futur serait oublié. Précédent : `admin/medias/bootstrap.php:39` |
| **Signature** | `exclure_du_plan_du_site( $arguments )` — **paramètre et retour non typés** |
| **Effet** | `! is_array( $arguments )` → rend la valeur reçue telle quelle ; sinon `$arguments['has_password'] = false;` et rend le tableau |
| **Ne touche pas** | aucune autre clé (`post_status`, `post_type`, `orderby`, `posts_per_page`, `no_found_rows`) · ne branche pas par type · ne filtre **ni** `wp_sitemaps_post_types` (retirer un type entier casserait le plan du site, pas une entrée) **ni** `wp_sitemaps_posts_pre_url_list` (court-circuiterait la requête du cœur) |

**Pourquoi non typé** : précédent explicite du dépôt, `admin/corbeille/bootstrap.php:49-50` — « un
filtre tiers peut avoir rendu autre chose qu'un tableau, et `strict_types` en ferait une **erreur
fatale** ». Sur le plan du site, une erreur fatale servirait un XML tronqué à un moteur de recherche.

#### Convention de cohabitation sur `wp_sitemaps_posts_query_args` — gelée à l'identique dans les contrats #23 et #24

**Ce hook est filtré par deux modules du lot 18** : `query/page-protegee/` (ici) et
`migration/indexation-heritee/plan-du-site.php` (#24, pour ses cinq contenus repris `noindex`, qui
sont **publiés et non protégés** — un plan du site qui annonce une page `noindex` se contredit). Les
deux rappels sont additifs et ne partagent aucun fichier. **Sans convention, ils se détruisent en
silence** : pas d'erreur, pas de journal, le plan du site répond 200 en annonçant une page qui devait
en sortir.

> **Chaque rappel MUTE des clés de `$args`. Aucun ne remplace `$args`.**
> Interdit : `return array( … );` · `$args = array( … );` · `unset( $args['has_password'] );`
> Et une `meta_query` préexistante s'**enveloppe** sous `'relation' => 'AND'`, jamais ne se complète
> par ajout : si l'autre filtre avait posé `'relation' => 'OR'`, un ajout naïf ferait passer
> l'exclusion par un OU et elle serait **sans effet, silencieusement**.

**Le cas de la `meta_query` est théorique pour #23**, qui emploie `has_password` et non `meta_query`.
**C'est précisément pourquoi il est gelé maintenant** : la convention doit exister avant le premier
module qui en aura besoin, pas après.

**Vérifié à ce gel** : `wp_sitemaps_posts_entry` **ne peut pas** retirer une entrée — le cœur empile
le retour sans le tester, et un tableau vide produit un `<url/>` vide, pire que rien ;
`wp_sitemaps_add_provider` est à la maille du fournisseur entier. `wp_sitemaps_posts_query_args` est
donc bien le seul point d'accroche des deux issues.

### 3.3 Accroche 2 — la recherche et les flux

```php
add_action( 'pre_get_posts', __NAMESPACE__ . '\\exclure_de_la_recherche', 10, 1 );
```

Signature : `exclure_de_la_recherche( \WP_Query $requete ): void` — typée, précédent
`admin/listes/ordre.php:383`.

**Trois gardes, dans cet ordre, toutes dans le rappel, jamais à l'inclusion :**

```
1.  if ( is_admin() )                                        { return; }
2.  if ( ! $requete->is_main_query() )                       { return; }
3.  if ( ! $requete->is_search() && ! $requete->is_feed() )   { return; }
    $requete->set( 'has_password', false );
```

**L'ordre est porteur, et chaque garde a un motif distinct :**

- **Garde 1 est le cœur de l'issue, pas une formalité.** En administration, `wp_edit_posts_query()`
  peuple le `$wp_query` global : sur `edit.php?post_type=page&s=espace`, `is_main_query()` **et**
  `is_search()` valent vrai. Sans elle, **la page protégée disparaîtrait de la recherche de
  l'éleveuse dans son propre back-office**. C'est le mode de panne n° 1 de cette issue.
- **Garde 2 est la seule qui protège la REST**, donc le sélecteur de lien de l'éditeur de blocs.
  `is_admin()` vaut **faux** sur `/wp-json/` — fait corroboré trois fois dans le dépôt
  (`admin/medias/bootstrap.php:31`, `admin/description-photo/bootstrap.php:97`,
  `blocks/grille-chiens/donnees.php:184`). `is_main_query()` compare `$this` à `$GLOBALS['wp_query']` ;
  un contrôleur REST instancie un `WP_Query` local, jamais le global. Elle protège au passage le plan
  du site — **d'où l'accroche 1 séparée : `pre_get_posts` + `is_main_query()` ne peut pas filtrer le
  plan du site**, ce n'est pas une ceinture de plus.
- **Garde 3** restreint aux deux façades visées. `is_search()` seul serait trop **large** en
  administration (garde 1 le règle) et trop **étroit** pour les flux.

**Ne fait pas** : ne touche aucune autre variable de requête · n'exclut aucun **type de contenu** de la
recherche · ne touche ni `posts_search`, ni `posts_where`, ni `the_posts` · **ne teste jamais la
session** (§3.5).

**Effet de bord connu, à ne pas corriger** : sur un flux **singulier** (`/portees/<slug>/feed/`), la
garde 3 est vraie et la clause s'applique. **Sans objet ici** :
`content/portee/bootstrap.php:53` déclare `'supports' => array( 'title', 'editor', 'revisions',
'thumbnail' )` — **pas `comments`**. Ne pas ajouter de garde `! is_singular()` pour un cas qui
n'existe pas ; connaître la ligne.

### 3.3 bis Accroche 3 — la directive d'indexation

**Ajoutée le 2026-09-05, sur arbitrage de lot, après le premier gel.** Le contrat renvoyait d'abord
`wp_robots` à #24 (§6, première rédaction) et laissait la limite §8.2 ouverte. **L'orchestrateur s'est
corrigé et me l'a rendue, et le motif est juste** : #24 possède `wp_robots` **pour ses cinq contenus
repris de l'ancien site**, qui sont des **faits recopiés, stockés avec leur provenance** dans
`_mtb_robots_source` (décision 55). *Notre* règle n'est pas un fait recopié — c'est **une règle**. La
verser dans cette table corromprait ce que la décision 55 protège, et obligerait #24 à connaître
`post_password_required()`.

```php
add_filter( 'wp_robots', __NAMESPACE__ . '\\interdire_indexation_du_contenu_protege', 20 );
```

| | |
|---|---|
| **Priorité** | **20**, alignée sur `migration/indexation-heritee/bootstrap.php:47` et pour le même motif écrit là-bas : **après** `wp_robots_noindex_embeds`, `wp_robots_max_image_preview` et les autres rappels du cœur |
| **Arguments** | 1 |
| **Signature** | `interdire_indexation_du_contenu_protege( array $robots ): array` |
| **Condition** | vue **singulière** dont l'objet interrogé porte un mot de passe non vide |
| **Effet** | rend `wp_robots_no_robots( $robots )` — helper du cœur, **non remplaçable**, déjà employé dans le dépôt (`blocks/formulaire-contact/traitement.php:246`). On ne pose pas `noindex` à la main : le helper gère aussi `follow`/`nofollow` selon `blog_public` |

**La condition porte sur la présence d'un mot de passe, JAMAIS sur `post_password_required()`.**
C'est le même raisonnement qu'au §3.5, et il est structurant : `post_password_required()` répond
*« ce visiteur-ci est-il enfermé dehors ? »*, donc la directive dépendrait du **cookie du visiteur**.
Une page mise en cache pour quelqu'un qui vient de saisir le mot de passe serait alors resservie
**sans `noindex`** — l'empoisonnement de cache que le §3.5 refuse déjà pour les index. Le BRIEF §8 est
inconditionnel ; cette directive l'est aussi.

Forme retenue, sur précédent du dépôt : `'' !== (string) get_post_field( 'post_password', … )`
(`blocks/encart-appel/rendu.php:182`, `blocks/lien-de-recours/rendu.php:99`).

**Ne fait pas** : ne touche aucune autre clé de `$robots` · ne s'applique **qu'aux vues singulières**
(les archives, la recherche et les flux n'ont plus à contenir le contenu protégé — accroches 1 et 2)
· ne lit ni n'écrit `_mtb_robots_source`, qui appartient à #24 · ne teste jamais la session.

**Pourquoi ce n'est pas de la sur-conception** : exclure du plan du site **n'empêche pas**
l'indexation d'une URL partagée de la main à la main, et Q1 dit « réservées à certaines personnes ».
C'est la complétion minimale d'un objectif déjà écrit à l'énoncé, pas un périmètre élargi.

**Écart assumé au présent §, ratifié le 2026-09-05.** Le code livré intercale une garde que le
tableau ci-dessus ne nomme pas : `if ( $identifiant <= 0 ) { return $robots; }`, entre
`is_singular()` et la lecture du mot de passe. **Elle est conservée.** Elle a son précédent
(`query/portee/bootstrap.php:28`), elle ne change **ni la condition gelée ni son résultat** — elle
écarte seulement le cas où `get_queried_object_id()` ne rend rien d'exploitable —, et **retirer une
garde pour se conformer à la lettre d'un tableau serait payer la forme avec de la robustesse.**

**Deuxième écart, sur un attendu de mesure et non sur le code.** Le §5.4 bis B4 exigeait « aucun
`noindex` sur `/?s=Z8` ». **Mesuré : il y en a un, et il vient du cœur** (`wp_robots_noindex_search`),
**identique avant et après #23**. C'est l'attendu écrit qui décrivait mal l'état natif de WordPress,
pas le module qui déborde. **Ni le code ni la condition ne sont touchés** ; B4 se lit désormais
« aucun `noindex` **introduit par #23** hors des vues singulières ».

### 3.4 No-op quand rien n'est protégé — la formulation honnête

**No-op au résultat : oui, exactement. No-op littéral : non.** La clause `post_password = ''` est
ajoutée au SQL dans tous les cas. Elle est **sans effet observable** parce que
`wp_posts.post_password` est `varchar NOT NULL DEFAULT ''` : aucune ligne n'y porte `NULL`. Même
ensemble, même ordre, même `found_rows`.

Éviter la clause supposerait de compter les contenus protégés à chaque requête — **un accès base pour
économiser une comparaison sur une colonne indexée : refusé**.

### 3.5 Aucun test de session — décision, pas oubli

Aucune garde du type « sauf si l'utilisateur peut lire les contenus privés ». Trois motifs :

1. **BRIEF §8 est inconditionnel** : « Une page protégée n'apparaît ni dans les index publics, ni dans
   le sitemap, ni dans la recherche. » Sans « pour les visiteurs anonymes ».
2. Un plan du site conditionnel serait **empoisonné en cache** : servi une fois à un connecté, servi
   ensuite à tous.
3. `is_user_logged_in()` et `wp_get_current_user()` sont des **fonctions remplaçables**, interdites
   par `issue-1.md` §3.

**Conséquence assumée, à écrire aussi dans la fiche d'aide** : la recherche du **site public** ne
remontera pas la page protégée **même pour l'éleveuse connectée, même après saisie du mot de passe**.
Elle la retrouve dans `Pages` et `Portées` de son administration, où **rien n'est filtré**.

### 3.6 Conventions et interdits d'inclusion — vérifiés avant écriture

`issue-1.md` §12 : `declare(strict_types=1)` après le docbloc, avant le `namespace` · docbloc
`@package MTB\Core` · tabulations · espaces dans les parenthèses · **Yoda** · **`array()` jamais
`[]`** · pas de `?>` final · ligne vide finale · `snake_case` français · **PHP 8.1 plafond**.

`issue-1.md` §3, interdits à l'inclusion — **aucun n'est approché** : aucun accès base · aucun
`register_*` · **aucune fonction de traduction** (§7 : le module **n'émet aucune chaîne de caractères,
nulle part**, ce qui rend l'interdit structurellement intenable à violer) · **aucune fonction
remplaçable**, ni à l'inclusion ni dans les rappels · aucune sortie · aucun `session_start`,
`header()`, `ini_set` · aucun appel HTTP.

`is_admin()`, `WP_Query::is_main_query()`, `::is_search()` et `::is_feed()` **ne sont pas
remplaçables** : elles sont toujours définies.

**#23 ne pose aucune URL nouvelle** : aucun `add_rewrite_rule()`, aucun `flush_rewrite_rules()`, aucun
`init` 99, aucun `wp_loaded` 20. Conforme à `issue-27.md` §7.2, qui l'avait prédit et qui est ici
confirmé.

### 3.7 L'en-tête du module est normatif

Le `bootstrap.php` porte un commentaire d'en-tête calqué dans l'esprit sur
`admin/medias/bootstrap.php:25-36`. Il doit dire **trois** choses, et le dev le recopie sans le
paraphraser :

- **(a)** ce module pose ses filtres **sans aucune garde de contexte à l'inclusion**, et surtout pas
  `if ( ! is_admin() ) { return; }` : les trois façades visées sont des **requêtes de façade
  publique, hors administration**, où `is_admin()` vaut faux. Recopier la garde habituelle du groupe
  `admin` **l'éteindrait sur la seule façade où il sert**. Ne pas « ranger » ce module ;
  > **Rectifié le 2026-09-05, après mesure.** Le premier gel écrivait « des requêtes **anonymes** ».
  > **C'est faux depuis le §4.1** : la moitié utile de l'accroche 2 est précisément la recherche vue
  > par une **personne connectée** — l'éleveuse elle-même —, la recherche anonyme étant déjà tenue
  > par le cœur. Seul l'adjectif était faux ; **l'argument porteur ne bouge pas**, la clause opérante
  > est « où `is_admin()` vaut faux », et c'est elle qui condamne la garde d'inclusion. Le fichier
  > livré porte la formulation rectifiée.
- **(b)** le **test de contexte vit dans le rappel**, jamais au chargement ;
- **(c)** **la panne de ce module est invisible** — il ne rend rien, n'imprime rien, ne journalise
  rien, ne déclare aucune fonction : s'il ne se charge pas, ou si une garde est mal posée, la page
  répond **200**, rien n'entre dans `debug.log`, et une page réservée réapparaît simplement **dans le
  plan du site, dans les flux, dans la recherche pour toute personne connectée, et devient
  indexable** *(façades rectifiées le 2026-09-05 : le premier gel n'en nommait qu'une, la mesure en a
  ajouté deux — §4.1 — et l'arbitrage de lot une quatrième — §3.3 bis)*. **Le seul témoin est le
  protocole du §5.**

Il doit également nommer le domicile et son prix (§2.3), pour qu'une chaîne future ne redécouvre pas
l'arbitrage.

---

## 4. Tableau des points de sortie — la preuve, et le solde de la dette T8

**C'est la section la plus importante de ce contrat.** La dette T8 est née d'une déclaration non
vérifiée dans un contrat gelé (le contrat #2 déclarait BRIEF §8 satisfait ; c'était faux).
`issue-3.md:448` ne déclarait le reste qu'en « acompte ». Le voici soldé, ligne par ligne, **ancre
vérifiée dans le code**.

| # | Point de sortie public | Ce qui le sert | Où le contenu protégé est écarté | Ancre | #23 écrit-elle du code ? |
|---|---|---|---|---|---|
| 1 | Encart d'accueil « dernière portée » | `mtb_get_derniere_portee()` → `liste()` → `contenus()` | `has_password => false` | `query/portee/hydratation.php:114` ; appel `bootstrap.php:52` | non |
| 1b | *idem, seconde ceinture* | bloc `mtb/derniere-portee` | `if ( 'ok' !== $mtb_portee['etat'] ) return;` | `blocks/derniere-portee/render.php:42` | non |
| 2 | Bloc « Liste de portées » | `mtb_get_portees()` → `liste()` | idem | `query/portee/bootstrap.php:139` | non |
| 3 | Archive `/portees/` | `templates/archive-mtb_portee.html:8` → `mtb/liste-portees` | idem que 2 | `archive-mtb_portee.html:8` | non |
| 4 | « Portées de ce chien » | `mtb_get_portees_du_chien()` → `contenus()` | idem | `query/portee/bootstrap.php:192` | non |
| 5 | Navigation portée précédente / suivante | `mtb_get_portee_voisine()` → `liste()` | idem | `query/portee/bootstrap.php:247` | non |
| 6 | Résolution par identifiant | `mtb_get_portee_par_identifiant()` → `liste()` | idem | `query/portee/bootstrap.php:81` | non |
| 7 | Fiche portée ouverte directement | `mtb_get_portee()` → `Hydratation::portee()` | **charge réduite** `array( 'id', 'lien', 'protege' => true, 'etat' => 'page_protegee' )` — aucun champ du domaine | `hydratation.php:299-305` | non |
| 8 | Père / mère d'une portée → lien | `Hydratation` | `post_password_required()` → pas de lien, état `donnee_absente` | `hydratation.php:369-375` | non |
| 9 | Grille de chiens / « La meute » | `mtb_get_chiens_par_statut()` | `has_password => false`, commentaire citant BRIEF §8 | `query/chien/lecture.php:165-166` | non |
| 10 | Fiche chien ouverte directement | `mtb_get_chien()` | `squelette( $post, 'page_protegee' )` | `query/chien/lecture.php:51-52` | non |
| 11 | Père / mère d'un chien → lien | `lecture.php` | `post_password_required()` | `query/chien/lecture.php:550` | non |
| 12 | Tableaux de la page Travail | `interne.php` | `has_password => false` | `query/resultat/interne.php:56` | non |
| 12b | Lien « résultat → fiche du chien » | `interne.php` | `'' === (string) $fiche->post_password` | `query/resultat/interne.php:290` | non |
| 13 | Encart d'appel (cible de page) | `rendu.php` | `'' !== get_post_field( 'post_password', … )` → 0 | `blocks/encart-appel/rendu.php:182` | non |
| 14 | Lien de recours | `rendu.php` | `'' !== $page->post_password` → null | `blocks/lien-de-recours/rendu.php:99` | non |
| 15 | `h1` d'un bandeau d'ouverture | `titre-principal.php` | `post_password_required()` → le bandeau ne réclame pas le `h1` | `blocks/bandeau-ouverture/titre-principal.php:103` | non |
| 16 | POST du formulaire de contact | `traitement.php` | `post_password_required()` → null, aucun courriel | `blocks/formulaire-contact/traitement.php:74` | non |
| 17 | Métadonnées en REST | `register_post_meta` | `'show_in_rest' => false` sur **les seize clés**, motif écrit | `content/portee/champs.php:158-162`, `:174` | non |
| 18 | Résultats de travail, toutes façades | type de contenu | `public => false`, `publicly_queryable => false`, `exclude_from_search => true`, `has_archive => false`, `rewrite => false`, `show_in_rest => false` | `content/resultat/bootstrap.php:49-54`, `:67` | non |
| **19** | **Recherche du site `/?s=…`, visiteur ANONYME** | requête principale, `templates/search.html:6` `"inherit":true` | **le cœur le fait déjà** : `WP_Query::parse_search()` ajoute `AND post_password = ''` **sous condition `! is_user_logged_in()`** | `wp-includes/class-wp-query.php`, `parse_search()` — **mesuré dans la pile, voir §4.1** | **non — corrigé après mesure** |
| **19b** | **Recherche du site `/?s=…`, personne CONNECTÉE** | idem | **RIEN AVANT #23** — la clause du cœur est conditionnée à la session, elle ne pouvait donc pas tenir la promesse **inconditionnelle** du BRIEF §8 | mesuré : 1 → 0 (R9) | **OUI — accroche 2** |
| **20** | **Flux `/portees/feed/`, `/feed/`, flux de recherche** | requête principale | **RIEN AVANT #23**, à aucun moment et pour personne | `content/portee/bootstrap.php:54` `has_archive => true` ; mesuré : 3 occurrences → 0 (F1) | **OUI — accroche 2** |
| **21** | **Plan du site `wp-sitemap*.xml`** | `WP_Sitemaps_Posts` (`post_status => publish` seul) | **RIEN AVANT #23** ; aucun `wp_sitemaps_*` dans le dépôt | mesuré : 4 → 3 `<url>` (S2), 33 → 32 (S3) | **OUI — accroche 1** |
| 22 | REST `?search=`, **anonyme** | contrôleur du cœur | **le cœur le fait déjà**, même clause conditionnée à la session : **0 résultat**, avec et sans #23 | mesuré (X1) | **non — le cœur** |
| 22b | REST `?search=` ou `/wp/v2/search`, **connectée** | contrôleur du cœur | titre (préfixé) et lien exposés ; **contenu et métas clos** | `content/portee/champs.php:158-162` ; mesuré (X1 bis, X5) | **non — limite assumée §8.1** |
| 22c | REST par **accès direct à l'ID**, anonyme | contrôleur du cœur | `id`, `slug`, `link`, titre préfixé, `content` **vide** et `protected: true`, **aucune méta** | mesuré (X4) | **non — limite assumée §8.1** |
| 23 | Menu de navigation | `themes/mtb/functions.php:615` — ne teste que `'publish'` | rien : **une page protégée est publiée** | `functions.php:615` | **non — geste éditorial, §8.3** |
| 24 | Un `core/query` posé dans une page | aucun `allowed_block_types_all` dans le dépôt | rien | `blocks/categorie-mtb/bootstrap.php:25` renvoie à « l'epic Gabarits » | **non — hors #23, §8.4** |
| 25 | Archives de date / auteur (`templates/index.html:6`) | requête principale | non filtré | `MASTER.md` l. 1088-1090 : aucun index d'élevage n'y tombe. *(Rectifié après mesure : la pile porte **un** `post` publié, `Hello world!` ID 1, qui sort dans `/feed/`. Sans conséquence — il n'est pas protégé — mais la ligne ne peut plus s'appuyer sur « aucun `post` publié ».)* | **non — sans objet, nommé §8.5** |

| **26** | **Indexation d'une URL protégée partagée hors du site** | le moteur suit un lien donné de la main à la main ; l'exclusion du plan du site n'y peut rien | **RIEN AVANT #23** — aucun `wp_robots` sur le contenu protégé | *ajoutée le 2026-09-05, arbitrage de lot — voir §3.3 bis* | **OUI — accroche 3** |

> **27 points de sortie. 4 à écrire (19b, 20, 21, 26). 18 déjà tenus à la source. 5 hors périmètre et
> nommés.**

### 4.1 Rectification après mesure — la ligne 19 telle qu'elle avait été gelée était fausse

**Gelée le 2026-09-05 avant implémentation, rectifiée le 2026-09-05 après mesure dans la pile.** Le
contrat écrivait « Recherche du site `/?s=…` → **RIEN AVANT #23** ». **C'était faux, et c'est la
mesure qui a tranché** — précédent du lot 16, où une mesure a réfuté un contrat gelé et où c'est la
mesure qui avait raison.

`WP_Query::parse_search()` ajoute lui-même la clause, **mais sous condition de session** :

```php
if ( ! empty( $search ) ) {
    $search = " AND ({$search}) ";
    if ( ! is_user_logged_in() ) {
        $search .= " AND ({$wpdb->posts}.post_password = '') ";
    }
}
```

Mesuré avant/après pose du module, sur `/?s=espace` :

| | anonyme | connectée |
|---|---|---|
| avant #23 | 0 occurrence — **déjà tenu par le cœur** | **1 occurrence — la fuite réelle** |
| après #23 | 0 | **0** |

**Ce que l'accroche 2 ferme réellement**, et c'est plus étroit **et** plus intéressant que ce que le
contrat annonçait :

1. **la recherche du site pour une personne connectée** — le cœur conditionne sa clause à la session,
   il ne pouvait donc **pas** tenir la promesse **inconditionnelle** du BRIEF §8 (« Une page protégée
   n'apparaît ni dans les index publics, ni dans le sitemap, ni dans la recherche », sans « pour les
   visiteurs anonymes ») ;
2. **les flux**, que le cœur ne filtre **à aucun moment et pour personne** — mesuré : 3 occurrences de
   `z9-2026-demo` dans `/portees/feed/` avant, 0 après.

**Conséquence à ne pas manquer : l'arbitrage 3 (ajouter les flux, hors lettre de la checklist) est ce
qui sauve l'accroche 2.** Sans lui, elle n'aurait fermé qu'un cas de session, et la moitié utile du
travail aurait été laissée de côté au nom du périmètre.

**Ce que cela change au solde de la dette T8** : la promesse du BRIEF §8 reste tenue après #23 sur les
trois façades visées, mais **le trou réel n'était pas celui que le contrat #2 puis celui-ci
décrivaient**. Il était : *plan du site pour tous · flux pour tous · recherche pour les connectés*.
Le contrat #2 déclarait §8 satisfait sans mesurer ; ce contrat l'a d'abord déclaré ouvert sans mesurer
non plus. **La leçon est la même dans les deux sens : seule la mesure solde une déclaration.**

**Deux corrections de fait établies en instruisant ce tableau, consignées et non réparées :**

- **`mtb_get_portee()` ne passe pas par `contenus()`.** `query/portee/bootstrap.php:38` appelle
  `Hydratation::portee( $post )` directement. Elle est gardée **autrement, et mieux** (ligne 7 du
  tableau). La formule « les six fonctions passent toutes par `contenus()` ou `liste()` » est inexacte
  pour une des six ; le résultat est identique.
- **`issue-3.md:449` est périmé.** Il écrit « l'archive `/portees/` est servie par la requête
  principale du cœur, qui ne filtre pas `has_password` ». C'était vrai au gel ; ça ne l'est plus :
  `archive-mtb_portee.html:8` rend le corps de l'archive par `mtb/liste-portees`. **La requête
  principale de l'archive contient bien la portée protégée, mais rien ne la rend.** `issue-3.md` n'est
  pas ouvert par #23.

**Nos propres listes ne reçoivent aucune ligne de code.** Écrire un `pre_get_posts` pour elles serait
une **sixième écriture de la même règle** — exactement la classe de défaut consignée en **T104** à la
revue du lot 17.

---

## 5. Protocole de vérification — le seul témoin d'un module dont la panne est muette

**Tâche 1 de l'issue (« vérifier que le mécanisme natif fonctionne sur une page et sur une portée »)
n'est pas du code : c'est ce protocole.** Il se rejoue **à chaque livraison qui touche ce fichier**.

### 5.0 Préparation

| | Geste | Attendu |
|---|---|---|
| P1 | `docker compose ps` | 4 services `healthy` |
| P2 | `wp option get permalink_structure` | `/%postname%/` (`provision.sh:107`). Sans structure, ni `/portees/feed/` ni les URL de plan du site ne répondent |
| P3 | `wp post list --post_type=page --name=espace-prive --fields=ID,post_title,post_status` | 1 ligne, **« Espace privé (démonstration) »**, `publish` (`provision.sh:155-160`) |
| P4 | `wp eval "echo get_post_field('post_password', <ID>);"` | `chiot2026` |
| P5 | `wp post create --post_type=mtb_portee --post_title="Z9 2026" --post_status=publish --post_name=z9-2026 --post_password="chiot2026"` — **sûr** : `fields/portee/bootstrap.php` sort sur `! is_admin()`, et `sauvegarde.php:41-47` sortirait faute de nonce | un ID |
| P6 | Créer le **témoin non protégé** `Z8 2026`, `post_name=z8-2026` | un ID. **Sans témoin, un filtre qui vide tout passerait pour un succès** |
| P7 | `wp rewrite flush` puis vider `debug.log` | — |

Session éditrice : `${WP_EDITOR_USER}` / `${WP_EDITOR_PASSWORD}` de `docker/.env`
(`provision.sh:133-137`, rôle natif `editor`).

### 5.1 Mesure de référence — obligatoire, avant le code

**Jouer S2, S3, R1, R2, R9 et F1 AVANT que le module n'existe.** Sans cette passe, on ne saura jamais
si le filtre a agi ou si l'index était déjà vide.

**Résultat mesuré le 2026-09-05, et il a rectifié ce contrat (§4.1) :** la page et la portée
protégées apparaissaient dans **S2, S3, R9 (connectée) et F1**, mais **pas** dans **R1/R2
(anonyme)** — le cœur y pourvoyait déjà. C'est cette mesure, et elle seule, qui a montré que le
périmètre réel de l'accroche 2 est *recherche des connectés + flux pour tous*, et que l'ajout des flux
(§3.3, hors lettre de l'énoncé) est **ce qui sauve cette accroche**.

### 5.2 Plan du site

| | En anonyme | Attendu |
|---|---|---|
| S1 | `GET /wp-sitemap.xml` | 200, XML valide, non vide ; **lire dans l'index les noms réels des sous-plans** plutôt que les supposer |
| S2 | `GET /wp-sitemap-posts-page-1.xml` | **ne contient pas** `/espace-prive/` · **contient** `/contact/` (témoin) |
| S3 | `GET /wp-sitemap-posts-mtb_portee-1.xml` | **ne contient pas** `/portees/z9-2026/` · **contient** `/portees/z8-2026/` |
| S4 | `GET /wp-sitemap-posts-mtb_chien-1.xml` | 200, non vide — le filtre ne doit rien casser sur un type sans contenu protégé |
| S5 | **Compte** : nombre de `<url>` dans S3 **=** `wp post list --post_type=mtb_portee --post_status=publish --format=count` **− 1** | Vérifie que le filtre gouverne **aussi le comptage** (`get_max_num_pages()`). **Point le plus fragile du dispositif** — à mesurer, jamais à supposer |
| S6 | `GET /wp-sitemap-posts-mtb_resultat-1.xml` | **404 attendu** (`content/resultat/bootstrap.php:49` `public => false`). **Un 200 ici réfute la ligne 18 du §4 : arrêt et remontée** |
| S7 | **Jonction #24** — rejouer S2 et S3 **une fois #24 livrée** | Même verdict. Exigé par `ETAT.md:210-213` : c'est le seul test qui exerce la jonction entre les deux issues |

### 5.3 Recherche — le positif **et** le négatif

| | Geste | Attendu |
|---|---|---|
| R1 | `GET /?s=espace` anonyme | aucun lien vers `/espace-prive/`, aucune occurrence de « Espace privé » |
| R2 | `GET /?s=Z9` anonyme | aucun lien vers `/portees/z9-2026/` |
| R3 | `GET /?s=Z8` anonyme | **le lien vers `/portees/z8-2026/` EST présent** — témoin |
| R4 | `GET /?s=contact` anonyme | la page Contact est listée |
| R5 | `GET /?s=zzzintrouvable` | l'état vide de `templates/search.html:19-31`. **Pas de 404, pas d'erreur PHP** |
| **R6** | Session éditrice, `GET /wp-admin/edit.php?post_type=page&s=espace` | **« Espace privé (démonstration) » EST dans la liste. Mode de panne n° 1 de l'issue.** |
| **R7** | Session éditrice, `GET /wp-admin/edit.php?post_type=mtb_portee&s=Z9` | **« Z9 2026 » EST dans la liste** |
| R8 | Session éditrice, `edit.php?post_type=mtb_portee` sans `s` | Les deux témoins présents ; **ordre et filtre d'année d'`admin/listes/ordre.php` inchangés** (non-régression du seul autre `pre_get_posts` du dépôt) |
| R9 | Session éditrice, `GET /?s=espace` sur le **site public** | **Absente — et c'est conforme** (§3.5). À écrire dans la fiche, sinon elle croira à une panne |

### 5.4 Flux

| | En anonyme | Attendu |
|---|---|---|
| F1 | `GET /portees/feed/` | 200, XML · **pas** `z9-2026` · **contient** `z8-2026` |
| F2 | `GET /feed/` | 200 · aucune mention de `/espace-prive/` |
| F3 | `GET /?s=Z9&feed=rss2` | 200 · aucune mention de `z9-2026` |

### 5.4 bis Directive d'indexation — accroche 3

**Mesure de référence d'abord** : jouer B1 et B2 **avant** d'écrire l'accroche 3. Aucun `noindex` ne
doit y apparaître, sans quoi on ne saura pas si le filtre a agi.

| | En anonyme | Attendu |
|---|---|---|
| B1 | `GET /espace-prive/` — chercher `<meta name="robots"` dans le `<head>` | **avant** : pas de `noindex` · **après** : `noindex` présent |
| B2 | `GET /portees/z9-2026-demo/` | idem |
| **B3** | `GET /contact/` **et** `GET /portees/z8-2026-demo/` (témoins **non protégés**) | **AUCUN `noindex`, avant comme après.** Témoin décisif : un filtre trop large marquerait tout le site `noindex` et **rien à l'écran ne le dirait** |
| **B4** | `GET /` (accueil), `GET /portees/` (archive), `GET /?s=Z8` (recherche) | **aucun `noindex` introduit par #23** — l'accroche 3 ne vise **que** les vues singulières. *Rectifié après mesure : `/?s=…` porte un `noindex` du cœur (`wp_robots_noindex_search`), **identique avant et après**. La comparaison avant/après est le verdict, pas la présence absolue.* |
| B5 | `GET /espace-prive/` **après avoir saisi le mot de passe** (cookie `wp-postpass_*` posé) | **`noindex` TOUJOURS présent.** C'est ce qui distingue la condition retenue (« l'objet porte un mot de passe ») de `post_password_required()` (« ce visiteur-ci est enfermé dehors ») — voir §3.3 bis. Un `noindex` qui disparaîtrait ici serait empoisonnable en cache |
| B6 | Après **retrait** de la protection (N6), `GET /espace-prive/` | **plus de `noindex`** — la page redevient indexable, comme elle redevient visible |
| B7 | Non-régression #24 : les cinq contenus repris (halan, ray-ban, roxane, youry, page placement) | **`noindex` toujours présent** une fois #24 livrée. Deux filtres additifs sur `wp_robots`, aucun ne doit annuler l'autre |

### 5.5 Le mécanisme natif — page **et** portée

| | Geste | Attendu |
|---|---|---|
| N1 | `GET /espace-prive/` anonyme | **200, pas 404.** Formulaire rendu. **Aucun extrait du contenu dans le HTML** |
| N2 | `GET /portees/z9-2026/` anonyme | **200**, formulaire rendu, **encart `mtb-encart-protege` présent** (`enveloppe-fiche.php:100-108`) |
| N3 | POST de N1 avec `chiot2026` | Contenu lisible ; cookie `wp-postpass_*` posé |
| N4 | POST de N2 avec `chiot2026` | Contenu de la portée lisible |
| N5 | POST avec un **mauvais** mot de passe | Le formulaire réapparaît **sans message** — comportement du cœur, **dette 2 du §9 confirmée**, à consigner, pas à réparer |
| **N6** | **Écran d'édition**, session éditrice : changer le mot de passe, publier ; retirer la protection (Visibilité → Public) ; la remettre | Les trois gestes **sans code** — c'est **D1** et le point obligatoire du BRIEF §8. Rejouer S2 et R1 **après le retrait** : la page **réapparaît** |
| N7 | Replacer la protection, rejouer S2 + R1 | Elle **redisparaît**. Prouve qu'aucun cache ne fige le verdict |

### 5.6 Aucune fuite de la portée protégée sur la façade

Vérifie le **§4**, pas du code neuf. Toutes en anonyme, `Z9 2026` protégée, `Z8 2026` témoin.

| | URL | Attendu |
|---|---|---|
| L1 | `/` | L'encart « dernière portée » affiche une portée **non protégée**, jamais `Z9 2026`, jamais un encart vide |
| L2 | `/portees/` | `Z8` listée, `Z9` absente |
| L3 | Page portant `mtb/liste-portees` avec `nombre` et `annee` | `Z9` absente |
| L4 | Fiche du père et de la mère de `Z9` (renseigner `_mtb_pere_fiche` par `wp post meta set` si besoin) | Le bloc « portées de ce chien » **ne cite pas** `Z9` |
| L5 | Fiche `Z8` → navigation précédente / suivante | **Ne mène jamais** à `Z9` |
| L6 | `/travail/` | Aucun lien vers un contenu protégé |
| L7 | « La meute » | Aucune fiche protégée |
| L8 | `grep -c "z9-2026"` sur le HTML de L1, L2, L6, L7 | **0 partout** |

### 5.7 REST — la limite assumée, **mesurée**

| | En anonyme | Attendu |
|---|---|---|
| X1 | `GET /wp-json/wp/v2/pages?search=espace` **anonyme** | **0 résultat — mesuré, avec ET sans le module.** *(Le gel initial attendait 1 et qualifiait 0 de régression : c'était faux, même cause qu'au §4.1 — le contrôleur REST passe par `parse_search()`, dont la clause est conditionnée à la session. Rectifié le 2026-09-05.)* |
| X1 bis | même route, **session éditrice** | **1 résultat**, titre préfixé, `link` présent, avec ET sans le module. **C'est la limite du §8.1, pas un échec.** Un résultat absent **ici** serait une régression : le sélecteur de lien de l'éditeur ne trouverait plus la page |
| X2 | même appel, `content.rendered` | **vide**, `content.protected === true` |
| X3 | `GET /wp-json/wp/v2/mtb_portee?search=Z9` | idem ; **`meta` vide ou absent** (`champs.php:174`) |
| X4 | `GET /wp-json/wp/v2/mtb_portee/<ID>` | ni chiots, ni parents, ni tests de santé |
| **X5** | Session éditrice, éditeur de blocs, insérer un lien, taper « espace » | **La page est proposée.** Preuve fonctionnelle que la garde 2 a fait son travail |

### 5.8 Silence et non-régression

| | Geste | Attendu |
|---|---|---|
| **Z1** | `wp eval "var_dump( has_filter('wp_sitemaps_posts_query_args'), has_action('pre_get_posts'), has_filter('wp_robots') );"` | **Les trois non-faux.** Seul témoin direct que le module a été **inclus**, donc que le dossier est dans un groupe balayé. **`false` ici est le module mort d'`ETAT.md:196-197`.** Sur `wp_robots`, non-faux ne distingue pas ce module de `migration/indexation-heritee`, qui filtre le même crochet à la même priorité (§3.3 bis) : ce sont **B1, B2 et B7** du §5.4 bis qui séparent les deux effets, pas cette sonde |
| Z2 | `debug.log` après toute la passe | **vide** — aucune notice, aucun `_doing_it_wrong`, aucun avertissement de traduction |
| Z3 | `wp eval "var_dump( get_option('mtb_core_empreinte') );"` avant / après | **identique.** #23 n'ajoute ni type, ni taxonomie, ni règle : un battement signalerait un enregistrement conditionnel, interdit (`issue-1.md` §13, amendement 2 règle 3) |
| Z4 | `php -l` sur `bootstrap.php` | 0 erreur |
| Z5 | `make css-check` | **exit 0** — #23 ne touche aucune feuille ; la vérification prouve qu'elle n'en a pas touché |
| Z6 | `git status` en fin de chaîne | **aucun fichier hors** `includes/query/page-protegee/`, `docs/guide/`, `docs/contracts/issue-23.md` |

### 5.9 Ce qui n'a pas pu être vérifié en lecture de dépôt

**Le cœur de WordPress n'est pas dans ce dépôt** (il vit dans le volume Docker). Les points suivants
sont **déduits ou connus, jamais mesurés ici** — c'est précisément la distinction dont l'absence a
produit T8 :

1. **Le comptage du plan du site** (S5) : que `wp_sitemaps_posts_query_args` gouverne aussi
   `get_max_num_pages()`. **Le plus fragile.**
2. Que `"inherit":true` de `templates/search.html:6` fait rendre la **requête principale** et non un
   `WP_Query` neuf. Sinon `is_main_query()` serait faux et **R1/R2 échoueraient sans la moindre
   erreur**. Mesuré par R1+R3.
3. Que `is_main_query()` est faux sur une route REST (X1, X5).
4. Que `is_search()` et `is_main_query()` sont vrais sur `edit.php?s=…`, donc que la garde 1 est
   indispensable. Mesuré par R6/R7.
5. La forme SQL exacte produite par `has_password => false`.
6. L'entrée d'accueil synthétique du plan du site selon `show_on_front` (`provision.sh:211-212` pose
   `show_on_front = page`) — sans effet ici, l'accueil n'étant pas protégée, mais **non vérifié**.
7. Les noms exacts des fichiers de sous-plan — S1 les lit dans l'index plutôt que de les supposer.
8. La liste réelle des sous-plans. Déduite : `page`, `post`, `mtb_portee`, `mtb_chien` ; **pas**
   `mtb_resultat` (S6).

---

## 6. Répartition avec l'issue sœur #24 — gelée

#24 (« redirections et référencement ») tourne **en parallèle**, sur le même arbre, sans branche.

**Rectifiée le 2026-09-05, après arbitrage de lot.** La première rédaction donnait `wp_robots` à #24
en entier ; c'était trop large, et l'orchestrateur l'a resserré. Le tableau ci-dessous **remplace**
cette version.

| Sujet | Propriétaire | Ce que l'autre ne fait pas |
|---|---|---|
| `wp_sitemaps_posts_query_args` | **partagé** — #23 pour le contenu protégé, #24 pour ses cinq contenus repris | Les deux **mutent** `$args`, aucun ne le remplace : **convention gelée au §3.2**, à l'identique dans les deux contrats |
| `wp_robots` — **contenu protégé par mot de passe** | **#23, seule** | #24 ne connaît pas `post_password_required()` et n'a pas à l'apprendre |
| `wp_robots` — **les cinq contenus repris `noindex`** (halan, ray-ban, roxane, youry, page placement) | **#24, seule** | **#23 ne lit ni n'écrit `_mtb_robots_source`** : ce sont des **faits recopiés avec leur provenance** (décision 55), pas des règles. Y verser notre règle corromprait la table |
| Générateur de plan du site | **#24, seule** | #23 ne le touche pas |
| Redirections 301, `template_redirect` | **#24, seule** | #23 ne redirige rien |
| `docs/migration/redirections.md` | **#24, seule** | #23 ne l'ouvre pas |

**`wp_robots` est donc filtré par deux modules, délibérément, et ce n'est pas un doublon.** Les deux
rappels répondent à deux questions différentes (*ce contenu porte-t-il un mot de passe ?* contre *ce
contenu était-il déjà `noindex` sur l'ancien site ?*), sur deux ensembles disjoints, avec deux
provenances de vérité distinctes. **Aucune chaîne future ne doit en « retirer un » en croyant
dédoublonner.** Les filtres `wp_robots` sont additifs ; les deux emploient la priorité **20**, pour le
motif écrit à `migration/indexation-heritee/bootstrap.php:45-47`.

Tous ces filtres sont **additifs** et se combinent **dans n'importe quel ordre** : aucune contrainte
de séquencement entre les deux chaînes.

**Vérification de jonction due à `test-integration-mtb`** (§5.2 S7, exigée par `ETAT.md:210-213`) :
une page protégée posée par #23 n'apparaît pas dans le plan du site produit par #24. **C'est le seul
test qui exerce réellement la jonction entre les deux issues, et il n'est observable qu'une fois les
deux livrées.**

---

## 7. Interdits

- **Le thème n'interroge jamais la base directement.** Inchangé — #23 ne touche pas le thème.
- **L'extension n'émet aucune règle visuelle ni mise en page.** Inchangé — #23 n'émet **rien**.
- **Aucune garde de contexte à l'inclusion.** Jamais `if ( ! is_admin() ) { return; }`, jamais
  `if ( is_admin() ) { return; }`, jamais `wp_doing_ajax()`, jamais `REST_REQUEST`. Le test de
  contexte vit **dans le rappel**.
- **Aucune fonction remplaçable**, nulle part — en particulier **jamais `is_user_logged_in()` ni
  `current_user_can()`** : fonction remplaçable **et** faute fonctionnelle (§3.5).
- **Aucune fonction globale `mtb_*`.** Ce module n'entre ni par la porte des fonctions de lecture (il
  n'en expose aucune) ni par celle des fonctions de composant (il ne rend rien) — `issue-1.md`
  amendement 1, condition 1. Voir l'arbitrage 2 du §11.
  > **Rectifié le 2026-09-05, arbitrage de lot.** Le premier gel écrivait « il ne lit rien ».
  > **C'est faux depuis l'accroche 3**, qui lit `post_password` par `get_post_field()`. Seule la
  > clause fausse tombe : **l'argument porteur ne bouge pas**, car ce qui fonde le domicile dans
  > `query/` n'a jamais été ce que ce module consulte, mais ce qu'il **expose au thème** — rien.
  > Le titre de l'en-tête du module porte la même rectification.
- **Aucun branchement par type de contenu** dans le filtre du plan du site : `accepted_args = 1` le
  rend structurellement impossible.
- **`wp_robots` est posé, et son périmètre est clos** : la seule condition admise est *« la vue est
  singulière et son objet porte un mot de passe »*. **Aucune autre directive d'indexation, aucune
  lecture ni écriture de `_mtb_robots_source`** (table de #24, faits recopiés — décision 55), **aucun
  `noindex` sur une archive, une recherche ou un flux.**
- **Aucune redirection, aucune règle de réécriture, aucun `flush_rewrite_rules()`, aucun `init` 99,
  aucun `wp_loaded` 20.**
- **Ne jamais remplacer `$args` dans un filtre de plan du site** — muter les clés, envelopper une
  `meta_query` sous `'relation' => 'AND'`. Convention gelée au §3.2, partagée avec #24.
- **Aucune écriture en base**, aucune option, aucun transient, aucun `wp_cache_set`.
- **Aucun filtre `the_password_form`.** Il produirait un **encart dans l'encart** sur les fiches,
  `enveloppe-fiche.php:100-108` imprimant déjà l'encart **avant** d'appeler `get_the_password_form()`
  (l. 105). Voir la dette 1 du §9.
- **Aucune sortie**, aucun `error_log`, **aucune chaîne de caractères**.
- **Ne pas ouvrir** : `mtb-core.php` · `includes/class-loader.php` · `docs/contracts/issue-1.md` ·
  `docs/contracts/issue-3.md` · aucun module `query/` existant (`chien`, `coordonnees`, `portee`,
  `resultat`) · `design-system/MASTER.md` (empreinte de #42) · `docs/migration/redirections.md` et
  tout `<groupe>/{redirections,sitemap}/**` (empreinte de #24) · `theme.json` · `functions.php` ·
  toute feuille sous `wp-content/themes/mtb/assets/css/**`.

---

## 8. Limites assumées — écrites, pas oubliées

1. **La REST n'est pas filtrée, et c'est délibéré.** On ne la filtre pas : **c'est ce dont l'éditeur
   de blocs se sert pour poser un lien vers la page**, et le couper priverait l'éleveuse du geste
   central de cette issue — le lien est justement ce qu'elle doit transmettre de la main à la main.
   Mesuré : la route réellement appelée par le sélecteur de lien (`/wp/v2/search?search=…`, session
   éditrice) rend bien la page, avec et sans le module (X5).
   **Périmètre exact, mesuré et non supposé** — plus étroit que ce que ce contrat annonçait à son
   premier gel (rectifié le 2026-09-05, même cause qu'au §4.1) :
   - **par recherche, en anonyme : rien ne sort** — le cœur applique déjà `post_password = ''`,
     conditionné à la session (X1) ;
   - **par recherche, connectée : titre préfixé + lien** (X1 bis, X5) ;
   - **par accès direct à l'ID, en anonyme : `id`, `slug`, `link`, titre préfixé**, `content` **vide**
     avec `protected: true` (X2, X4).

   **Atténuation acquise et créditée** : **aucune méta n'est exposée**, documenté depuis #3 pour cette
   raison exacte — `content/portee/champs.php:158-162` (« `WP_REST_Post_Meta_Fields` ne teste pas le
   mot de passe d'un contenu »), les seize clés en `show_in_rest => false` (`:174`).
   **Ce qui fuit : un titre et une URL. Ce qui ne fuit pas : chiots, parents, tests de santé,
   texte.**
2. ~~**Une URL partagée reste indexable.**~~ **LIMITE FERMÉE le 2026-09-05 — voir §3.3 bis.** Elle
   était ouverte au premier gel parce que le contrat renvoyait `wp_robots` à #24. L'arbitrage de lot
   l'a rendue à #23, et l'accroche 3 la ferme : une vue singulière dont l'objet porte un mot de passe
   rend `wp_robots_no_robots()`. **Ce qui subsiste, et qui n'est pas une limite mais un fait** : un
   moteur qui ignore la directive verrait toujours ce que voit un humain sans le mot de passe — le
   titre préfixé et le formulaire, **jamais le contenu**.
3. **Le menu.** `themes/mtb/functions.php:615` :
   `return $contenu instanceof WP_Post && 'publish' === $contenu->post_status;` — aucun test de mot de
   passe. **Une page protégée EST publiée** : ajoutée au menu, son nom s'affiche sur tout le site.
   **Ce n'est pas un défaut de code, c'est un geste que l'éleveuse peut faire.** La fiche d'aide le lui
   **interdit par écrit** (§10).
4. **Aucun `allowed_block_types_all` dans le dépôt.** Un `core/query` inséré dans une page listerait
   le contenu protégé. Relève de qui possède ce filtre — `blocks/categorie-mtb/bootstrap.php:25` le
   renvoie à « l'epic Gabarits » — **pas de #23**.
5. **Les archives de date et d'auteur** (`templates/index.html:6`, `"inherit":true`) ne sont pas
   filtrées. **Sans objet** : aucun `post` n'est publié, et `MASTER.md` l. 1088-1090 établit qu'aucun
   index d'élevage n'y tombe. **À rouvrir si une rubrique d'actualités existait un jour** (BRIEF §15
   question 5).
6. **La recherche du site public ne remonte pas la page protégée pour l'éleveuse connectée**, même
   après saisie du mot de passe (§3.5). Voulu. Dit dans la fiche.
7. **Mot de passe composé uniquement d'espaces** : les collations `PAD SPACE` de MySQL le rendraient
   équivalent à l'absence de mot de passe **pour la clause d'index seulement** — le contenu resterait
   protégé à l'affichage, `post_password_required()` ne faisant pas cette comparaison. **Comportement
   du cœur, identique pour les trois lectures existantes, non introduit par #23.** Non vérifié ; coût
   de vérification supérieur au risque.
8. **Le plan du site est le seul index tiers couvert.** Aucune autre production XML n'existe dans le
   projet.

---

## 9. Dettes — consignées, aucune ligne de code

| # | Dette | Verdict |
|---|---|---|
| 1 | **`MASTER.md` §9.5 non tenu sur une *page* protégée** | **CONFIRMÉE.** §9.5 (l. 1102-1104) impose l'encart `--calcaire-creux`, l'étiquette « Page protégée » et la phrase, **sans restreindre aux fiches**. Or seul `themes/mtb/enveloppe-fiche.php:100-108` l'implémente, et `fiches.css` n'est mise en file qu'à `enveloppe-fiche.php:41`. Une **page** protégée passe par `templates/singular.html` → `core/post-content` → `get_the_password_form()` **nu**. **Thème + CSS : hors empreinte de #23.** **Piège pour l'issue qui la paiera** : un filtre `the_password_form` posé dans `mtb-core` produirait un **encart dans l'encart** sur les fiches (`enveloppe-fiche.php` imprime l'encart **puis** appelle le formulaire, l. 105). **La réparation est thème + extension d'un seul tenant, jamais un filtre isolé.** |
| 2 | **Un mot de passe faux ne dit rien** | **CONFIRMÉE.** Le cœur réaffiche le formulaire sans message. **WCAG 3.3.1 (identification des erreurs) non tenu.** Mesurée par N5. Même issue future que la dette 1. |
| 3 | **Deux phrases explicatives sur une *fiche* protégée** | **CONFIRMÉE** : celle de MASTER (`enveloppe-fiche.php:104`) puis celle du cœur, dans le formulaire (l. 105). Observation, pas défaut. Même issue future. |
| 4 | **Le menu accepte une page protégée** | **CONFIRMÉE au caractère près** (`functions.php:615`). **Pas un défaut de code** — nourrit la fiche d'aide, qui l'interdit par écrit. |
| 5 | **Aucun `allowed_block_types_all`** | **CONFIRMÉE** ; renvoyée à l'epic Gabarits. Sans propriétaire déclaré à ce jour. |
| 6 | **`issue-3.md:449` est périmé** | **NEUVE.** L'archive `/portees/` n'est plus servie par la requête principale (§4). `issue-3.md` n'est pas ouvert. |
| 7 | **T103 — aucun agent de chaîne ne sait produire une capture** | **Héritée du lot 17** (`ETAT.md:89-96`). Le parcours « protéger une page par mot de passe » est **le seul des huit du BRIEF §13 sans aucune capture**. Position de ce contrat au §10. |

---

## 10. Fiche d'aide — `docs/guide/page-proteger-une-page-par-mot-de-passe.md`

Écrite par `doc-client-mtb`. **D3 : sans elle, l'issue n'est pas terminée.** BRIEF §13 la nomme
explicitement dans les huit parcours du guide.

Elle doit contenir **six** choses, dont **quatre sont des pièges que rien d'autre ne dira à
l'éleveuse** :

1. **Le geste**, sur une page : **Visibilité → Protégé par mot de passe → saisir → Publier**. Le
   retrait : **Visibilité → Public**. Puis **copier le lien et le donner** — c'est l'étape qu'on
   oublie le plus souvent.
2. **Le mot de passe et le lien se transmettent de la main à la main.** Aucun compte, aucune
   inscription, personne à créer.
3. **N'ajoutez jamais une page protégée au menu** : son nom s'afficherait sur tout le site (limite
   §8.3).
4. **Elle ne la trouvera pas dans la recherche du site**, même connectée, même après avoir saisi le
   mot de passe. **Elle la retrouve dans `Pages`** (ou `Portées`). **Sans cette phrase, elle croira à
   une panne.**
5. **Un mot de passe faux ne dit rien** : le formulaire réapparaît sans message (dette 2). Et **ce que
   voit quelqu'un qui arrive sans le mot de passe** : le titre, précédé de « Protégé : », et un champ
   — donc **ne rien mettre de confidentiel dans le titre**.
6. **Changer le mot de passe redemande le mot de passe à ceux qui l'avaient déjà saisi**, et **retirer
   la protection rend la page visible partout immédiatement**.

**Deux écrans, un seul geste — à ne pas découvrir en écrivant la fiche** : une **page** est en éditeur
de blocs (visibilité dans l'encadré **Résumé**), une **portée** est en éditeur classique
(`fields/portee/bootstrap.php:28` filtre `use_block_editor_for_post_type` ; visibilité dans l'encadré
**Publier**, derrière un lien **Modifier**, avec un bouton **OK**). Les deux écrans ne se ressemblent
pas. La fiche documente **la page** en parcours principal — c'est le cas tranché par Q1 — et **nomme
la portée** avec la différence d'écran, sans en refaire le pas-à-pas complet.

**Position assumée sur les captures (dette 7 / T103)** : la fiche est **livrée sans capture**, avec
un renvoi explicite à T103 dans le rapport de chaîne. **Elle ne déclare aucune capture « à
prendre » ni « candidate » dans son texte** — c'est ce qui a produit le HIGH bloquant de la revue du
lot 17, un guide qui se contredit lui-même. La passe de captures relève d'un agent hors chaîne, et
elle doit avoir lieu **avant #25**, qui assemble le guide **à partir de** `docs/guide/captures/` et ne
les refait pas.

---

## 11. Arbitrages

| # | Désaccord | Décision | Raison |
|---|---|---|---|
| 1 | Domicile du module : `privacy/` (annoncé à l'issue), `admin/`, `query/` | **`includes/query/page-protegee/`** | `privacy/` est **irréalisable** : module mort, silencieux (§2.1). `admin/` est **dangereux** : 2 des 5 modules sortent à l'inclusion sur `! is_admin()`, un troisième écrit que c'est « la première garde, comme dans tout ce groupe », et `admin/listes` pose cette garde **précisément sur un `pre_get_posts`** en écrivant qu'un crochet courant sur chaque requête est « une raison de plus pour ne pas l'y attacher ». `admin/coordonnees:5-11` a déjà tranché dans notre sens. **Une impureté de forme se dit en revue ; une mort silencieuse ne se dit nulle part.** Prix écrit au §2.3. |
| 2 | Exposer une fonction globale `mtb_arguments_sans_contenu_protege()` pour « légitimer » le domicile dans `query/` (recommandée par le brainstorm) | **Refusée** | `issue-1.md` §5 clôt la surface globale aux constantes + **fonctions de lecture** ; l'amendement 1 ouvre **une** porte, celle des **fonctions de composant**, dont la condition 1 dit « rien d'autre n'entre par cette porte ». Une fonction rendant des arguments de requête n'est **ni l'une ni l'autre** : ce serait une **troisième catégorie**, donc l'amendement d'un contrat que dix chaînes lisent — **pour une fonction que personne n'appellerait**. Elle n'annulait d'ailleurs pas l'objection qu'elle prétendait annuler. **La règle s'écrit une fois par une `const` sous espace de noms, si le dev le juge utile** : hors surface globale, invisible du thème, ne viole rien. |
| 3 | Les flux RSS sont-ils dans le périmètre ? La checklist ne nomme que sitemap et recherche | **Oui, ajoutés** | BRIEF §8 dit « ni les **index publics**, ni le sitemap, ni la recherche » : **un flux est un index public au sens littéral**. `content/portee/bootstrap.php:54` (`has_archive => true`) rend `/portees/feed/` disponible et le cœur n'y filtre pas `has_password`. **Le coût est une condition dans un rappel déjà écrit.** Refuser au nom du périmètre laisserait ouverte la moitié d'un trou qu'on vient de reboucher. |
| 4 | Écrire un filtre pour **nos** listes (encart d'accueil, liste de portées, grille de chiens) | **Aucune ligne** | Déjà tenu **à la source**, ancre par ancre (§4, lignes 1-18). Un filtre de plus serait la **sixième écriture de la même règle** — la classe de défaut consignée en **T104** au lot 17. Le périmètre de #23 sur ces points est **la preuve, pas le code**, et c'est ce que le §4 solde (dette **T8**). |
| 5 | Filtrer la REST | **Non** | La recherche REST est ce dont l'éditeur de blocs se sert pour **poser le lien que l'éleveuse doit transmettre** ; la couper priverait l'issue de son geste central. Ce qui fuit est un titre et une URL ; les métas sont closes depuis #3 pour cette raison exacte. **Limite assumée §8.1, mesurée par X1-X5**, pas un oubli. |
| 6 | `noindex` sur un contenu protégé | ~~Hors #23~~ → **RENVERSÉ le 2026-09-05 : #23 le pose, dans son propre module** (§3.3 bis) | Première décision : `wp_robots` appartenait à #24 en entier, donc #23 s'abstenait malgré l'utilité, la répartition d'empreinte primant. **L'orchestrateur s'est corrigé et le motif emporte l'adhésion** : #24 possède `wp_robots` **pour ses cinq contenus repris**, qui sont des **faits recopiés avec leur provenance** (`_mtb_robots_source`, décision 55) ; notre règle est **une règle**, la verser là corromprait la table et obligerait #24 à connaître `post_password_required()`. Les filtres `wp_robots` étant additifs, chacun pose le sien sur sa propre condition — et **toute la politique « le contenu protégé est invisible » (plan du site, recherche, flux, robots) tient dès lors en un seul endroit**, celui qui porte le concept. C'est le contraire de T95 et T104, où une même règle vit en deux copies que rien ne surveille. Ce n'est pas de la sur-conception : exclure du plan du site n'empêche pas l'indexation d'une URL partagée, et Q1 dit « réservées à certaines personnes ». |
| 7 | Un fichier ou plusieurs | **Un seul `bootstrap.php`** | Deux crochets, aucune donnée. Précédent `admin/medias`. Découper coûterait deux `require_once` et deux docblocs pour vingt lignes de corps. |
| 8 | `accepted_args` du filtre de plan du site : 1 ou 2 | **1** | Le cœur passe `$post_type` en second. **Le refuser rend structurellement impossible** l'ajout d'une branche par type — la porte par laquelle un type de contenu futur serait oublié. La règle est la même pour tous les types. |
| 9 | Ligne d'inventaire d'`issue-1.md` §11 | **#23 n'ouvre pas `issue-1.md`** | Le fichier se contredit sur son propriétaire (l. 12-14 « chaque issue l'y ajoute » contre l. 407-408 « hors de l'empreinte de toute chaîne »), et **#24 y écrira dans le même lot** : recouvrement d'empreinte sur un arbre unique, sans branche. Remonté à l'orchestrateur. **Ligne rédigée au §12, à coller par `/lead-mtb` à la clôture du lot.** |
| 10 | Le chiffre « 3 modules `admin` sur 5 portent la garde », **posé par `brainstorm-mtb` et relayé par `lead-issue-mtb`** — *ni posé ni relayé par l'orchestrateur de lot* | **Corrigé en 2 sur 5 par `leaddev-back-mtb`** | `corbeille/bootstrap.php:63` est **dans le rappel**, pas dans la portée du fichier. **La conclusion est inchangée et l'argument est plus fort** : `corbeille:59` écrit la norme du groupe (« comme dans tout ce groupe ») **sans en avoir besoin**. Consigné parce que le chiffre a servi à décider. |
| 11 | *(après implémentation, 2026-09-05)* La ligne 19 du §4 — « la recherche du site n'est tenue par rien avant #23 » — **est réfutée par la mesure** | **Le contrat est rectifié, pas le code.** Voir §4.1 | `WP_Query::parse_search()` pose déjà `AND post_password = ''`, **mais sous `! is_user_logged_in()`**. Mesuré : la page protégée était **absente** de `/?s=` en anonyme avant #23, **présente** pour une connectée. Précédent du lot 16 : quand une mesure réfute un contrat gelé, **la mesure gagne**. Le code n'a pas été touché pour masquer l'écart, et le fait est écrit dans le docbloc du rappel pour qu'une chaîne future ne le redécouvre pas. **Le périmètre réel de l'accroche 2 est *recherche des connectés + flux pour tous* — et c'est l'arbitrage 3 qui la sauve.** |
| 13 | *(arbitrage de lot, 2026-09-05)* Deux modules filtreront `wp_sitemaps_posts_query_args` — comment les empêcher de s'annuler ? | **Convention gelée : chaque rappel MUTE des clés, aucun ne remplace `$args`** (§3.2) | Sans elle, deux rappels additifs se détruisent **en silence** : pas d'erreur, pas de journal, le plan du site répond 200 en annonçant une page qui devait en sortir. Rédaction reprise **à l'identique** dans les contrats #23 et #24 — deux formulations divergentes vaudraient pas de convention du tout. Le volet `meta_query` est **théorique pour #23**, qui emploie `has_password` : c'est précisément pourquoi on le gèle avant le premier module qui en aura besoin. |
| 14 | *(arbitrage de lot)* Où déclarer que `query/` accroche des hooks que le §2 du contrat #1 ne lui assigne pas | **Dans le présent contrat, §2.3. Jamais dans `issue-1.md`** | Précédent du dépôt : `docs/contracts/issue-22.md:783` déclare « `template_redirect` priorité 1 : amendement au contrat #1 §2 » **dans son propre contrat** ; `issue-1.md` §2 n'a jamais été édité pour cela. **C'est la seule forme qui permette à deux chaînes d'amender le même §2 le même jour sans se voir** — le cas exact du lot 18, où #24 en écrit un aussi. |
| 12 | *(après implémentation)* Créer une `const ARGUMENT_EXCLUSION` sous espace de noms — laissé ouvert par l'arbitrage 2 | **Non créée** | Décision du dev, retenue : le contrat décrit ce module comme « deux crochets, **aucune donnée** », et une constante y introduirait la seule donnée du fichier ; l'indirection cacherait au point d'usage le nom exact de la variable de requête, qui est précisément ce qu'un relecteur doit lire ; et les deux occurrences vivent dans deux fonctions dont les docblocs expliquent séparément la clause — elles ne varieraient pas ensemble. |

---

## 12. Ligne d'inventaire — pour `issue-1.md` §11

**Non collée par #23** (arbitrage 9). À coller par `/lead-mtb` à la clôture du lot 18, une fois le
recouvrement d'empreinte avec #24 arbitré :

```
| `query/` | `page-protegee` | #23 | Exclut le contenu protégé par mot de passe du plan du site, de la recherche et des flux, et le déclare non indexable — trois crochets du cœur, aucune fonction globale, aucune option, aucune méta |
```

Note à joindre sous le tableau : *#23 est le premier module du groupe `query/` à poser des crochets.
La déviation, son motif chiffré et son prix sont écrits au §2 de `docs/contracts/issue-23.md` et dans
l'en-tête du module.*

---

## 13. Ce que l'éleveuse voit changer

**Dans son administration : rien.** Aucun nouvel écran, aucun nouveau champ, aucune nouvelle colonne,
aucun nouveau bloc, aucun libellé modifié. Ses listes `Pages` et `Portées` sont **identiques**,
recherche comprise (R6, R7).

**Sur le site : trois disparitions, zéro apparition.** Ce qu'elle protège cesse d'apparaître dans la
recherche du site, dans les flux et dans le plan du site.

**Et une quatrième exclusion qu'elle ne verra jamais à l'écran** : la page protégée demande aux
moteurs de recherche de ne pas l'indexer (accroche 3, §3.3 bis). Rien ne change dans son
administration ni sur la page ; c'est un ordre adressé aux moteurs, dans le code de la page. Il vaut
même une fois le mot de passe saisi — c'est la page qui est réservée, pas la visite. La fiche d'aide
le dit en une puce, sans jargon.

**Ce qu'elle faisait déjà seule et continue de faire** : poser, changer et retirer un mot de passe
depuis l'écran d'édition. **#23 ne modifie pas ce geste — elle en rend la promesse vraie.**

C'est la formulation exacte du livrable : la protection existait, **l'exclusion n'existait pas**, et
le contrat #2 avait déclaré le contraire.
