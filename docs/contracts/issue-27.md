# Contrat d'interface — Issue #27 — Une voie conforme pour une règle de réécriture d'URL

**Gelé le 2026-08-29.** Ce contrat ne décrit pas une fonctionnalité : il décrit **la convention par
laquelle une issue future pose une règle de réécriture d'URL sans avoir à ouvrir
Réglages → Permaliens**, écran fermé au rôle Éditeur faute de `manage_options`.

Il amende `docs/contracts/issue-1.md`, qui reste le contrat de référence du chargeur. En cas de
divergence entre les deux, **l'amendement daté en fin d'`issue-1.md` fait foi**.

**Ce que l'éleveuse voit changer : rien.** C'est de l'infrastructure invisible, prérequis d'autres
issues. Aucune fiche d'aide n'est due (D3 sans objet).

---

## 1. Le défaut réparé — et ce n'est pas celui que l'issue annonçait

L'issue #27 est née de la dette **T6** :

> « L'empreinte du chargeur ne couvre que les types et taxonomies : aucune voie conforme pour une
> issue qui ajoute une règle de réécriture sans type `mtb_`. La parade manuelle
> (Réglages → Permaliens) exige `manage_options`, que Fabienne n'a pas. »

Le brainstorm a réfuté **deux prémisses** de cet énoncé, et en a trouvé une troisième que personne
n'avait écrite. Les trois sont consignées ici parce qu'elles routent le travail de #23 et #24.

### 1.1 Ce qui était exact

- **WordPress ne se répare pas tout seul.** `WP_Rewrite::wp_rewrite_rules()` ne reconstruit les règles
  que si l'option `rewrite_rules` est **vide ou absente**. Une option **peuplée mais périmée** n'est
  jamais réexaminée. Installation neuve → auto-guérison ; site en service qui reçoit une règle
  nouvelle → silence et 404.
- **`options-permalink.php` exige bien `manage_options`.** Fabienne est Éditeur natif (décision 44).
  La parade manuelle lui est fermée. Fait vérifié, inchangé.

### 1.2 Ce qui était faux — et qui change ce que #23 et #24 doivent faire

**T6 n'était pas « il n'existe aucun mécanisme ». C'était « il n'existe aucun mécanisme qu'une chaîne
parallèle ait le droit d'employer ».** Incrémenter `MTB_CORE_VERSION` aurait suffi techniquement — et
c'est précisément l'index central que la décision 9 proscrit et que l'arbitrage 5 du contrat #1 a
écarté. La nuance compte : au moment d'une mise en ligne séquentielle, `/lead-mtb` *pourrait* bumper
la version ; ce n'est jamais une réponse pour une chaîne.

**`add_rewrite_rule()` n'a jamais été interdit.** L'interdit du §13 porte sur `flush_rewrite_rules()`,
sur `init` 99 et sur la dépendance à l'ordre des groupes. **La confusion entre ces deux fonctions est
la cause de la dette T6.**

### 1.3 Le défaut réellement vivant, que l'issue ne nommait pas

`Loader::synchroniser_version()` observait les **noms** des types et taxonomies, jamais leurs
**arguments de réécriture**. Or `content/portee/bootstrap.php` déclare
`'rewrite' => array( 'slug' => 'portees', 'with_front' => false )` et `'has_archive' => true`.

> **Le jour où quelqu'un change `'slug' => 'portees'`** — et **Q4** (« URL accentuées conservées ou
> normalisées ? », qui bloque #24) remettra précisément ces slugs en jeu — **le nom `mtb_portee` ne
> bouge pas, l'empreinte ne bouge pas, la nouvelle adresse ne prend jamais effet, et l'ancienne
> continue de répondre.**

Ce n'est pas un 404 visible. C'est **un site qui a l'air de marcher avec les mauvaises URL** : le mode
de panne le plus cher, et le plus probable à court terme. **C'est lui l'objet principal de la
livraison.** Aucune des cinq tâches de la checklist d'origine ne le visait.

## 2. La convention — une phrase

> **Un module appelle `add_rewrite_rule()` depuis son rappel `init` 10, sans condition de contexte,
> et n'a rien d'autre à faire.**

Pas de sous-dossier à connaître, pas de filtre à brancher, pas de tableau à retourner, pas de préfixe
sur la cible. **Le geste réflexe de tout développeur WordPress fonctionne.** C'est le critère qui a
décidé de l'approche (§7, arbitrage 1).

Squelette exact de ce qu'une issue future pose sur le disque — un module ordinaire, contrat de
`bootstrap.php` inchangé :

```php
// includes/<groupe>/<module>/bootstrap.php
add_action( 'init', __NAMESPACE__ . '\\enregistrer', 10 );
add_filter( 'query_vars', __NAMESPACE__ . '\\declarer_variable' );

function enregistrer(): void {
	add_rewrite_rule( '^chiots-a-reserver/?$', 'index.php?mtb_vue=chiots', 'top' );
}
```

## 3. Partage des charges

| À la charge du module | À la charge du chargeur |
|---|---|
| Poser sa règle depuis un rappel de `init` 10, **jamais à l'inclusion** du `bootstrap.php` | Calculer l'empreinte à `init` 99, la comparer, régénérer **une seule fois** |
| L'enregistrer **sans condition de contexte** : jamais derrière `if ( is_admin() )` ni équivalent | Écrire la moitié identité à `init` 99, la moitié réécriture à `wp_loaded` 20 |
| Déclarer **lui-même** la variable de requête que sa règle introduit (`add_filter( 'query_vars', … )`) | Journaliser le changement sous `WP_DEBUG` |
| Préfixer `mtb_` ce qu'il introduit — variable de requête, permastructure, terminaison — **au titre du nommage du contrat #1 §6**, et non parce que l'empreinte l'exigerait : **elle ne l'exige pas** | Ne poser **aucune** règle, n'en corriger aucune |
| Ne **jamais** appeler `flush_rewrite_rules()` (§13 du contrat #1, inchangé) | Ne déclencher `mtb_core_mise_a_jour` que sur la moitié identité |

## 4. L'empreinte — deux moitiés, deux déclencheurs

L'option `mtb_core_empreinte` est scindée. **C'est la décision structurante de cette issue.**

```
mtb_core_empreinte = array(
    // ── moitié IDENTITÉ ──  déclenche do_action( 'mtb_core_mise_a_jour' )
    'version'    => MTB_CORE_VERSION,
    'types'      => noms_mtb( array_keys( get_post_types() ) ),   // inchangé depuis #1
    'taxonomies' => noms_mtb( array_keys( get_taxonomies() ) ),   // inchangé depuis #1

    // ── moitié RÉÉCRITURE ──  déclenche flush_rewrite_rules( false )
    // array() tant que get_option( 'permalink_structure' ) est vide
    'reecriture' => array(
        'permastructs' => <extra_permastructs,  ksort>,
        'etiquettes'   => <rewritecode[i] => array( rewritereplace[i], queryreplace[i] ), ksort>,
        'regles_haut'  => <extra_rules_top,     ksort>,
        'regles_bas'   => <extra_rules,         ksort>,
        'regles_hors'  => <non_wp_rules,        ksort>,
        'terminaisons' => <endpoints,            sort>,
    ),
)
```

**Pourquoi ces six collections et pas une liste d'arguments** — c'est l'arbitrage 2. L'empreinte
capture **l'état d'entrée exact à partir duquel `WP_Rewrite::rewrite_rules()` fabrique l'option
`rewrite_rules`**, plutôt que de re-dériver ce que le cœur dérive. Conséquences :

- `slug`, `with_front`, `has_archive`, `feeds`, `pages`, `ep_mask`, `query_var` et `hierarchical` y
  sont **déjà contenus sous leur forme utile** — `with_front` est même déjà **appliqué** au `struct`
  d'une permastructure. Les lire en plus dupliquerait la logique du cœur pour rien.
- **Rien n'est à tenir en phase avec une future version de WordPress** : ce sont les tableaux que le
  cœur lui-même lira.
- `public` et `publicly_queryable` sont **volontairement exclus** : ils décident d'un
  `$wp->add_query_var()`, qui n'entre dans aucune règle de réécriture.

**Le tri (`ksort`/`sort`) sur chaque sous-tableau n'est pas cosmétique** : sans lui, l'ordre
d'enregistrement suffirait à faire battre l'empreinte d'une requête à l'autre, et les règles seraient
régénérées en boucle. Même raison que le `sort()` de `noms_mtb()` depuis #1.

### 4.1 Ce qui déclenche quoi — la ligne qu'une chaîne future viendra chercher

| Changement | `flush_rewrite_rules( false )` | `mtb_core_mise_a_jour` |
|---|---|---|
| Nouveau type ou taxonomie `mtb_` **qui produit des règles** (`rewrite` ≠ `false`) | oui — via sa permastructure et ses étiquettes | **oui** |
| Nouveau type ou taxonomie `mtb_` **en `'rewrite' => false`** (cas de `mtb_resultat`) | **non** — il ne produit aucune règle, il n'y a rien à régénérer | **oui** |
| `MTB_CORE_VERSION` incrémentée **seule** | **non** — les entrées de réécriture n'ont pas bougé, les règles sont déjà justes | **oui** |
| Changement de `slug` / `with_front` / `has_archive` | **oui** | non |
| Règle ajoutée ou retirée par un module | **oui** | non |
| `permalink_structure` vide | non — moitié non calculée | la moitié identité reste active |

> **Corrigé le 2026-08-29, après la passe de refacto.** La première rédaction de ce tableau annonçait
> « oui » au flush pour les deux premières lignes et pour l'incrémentation de version. **C'était faux du
> code livré**, qui ne régénère que lorsque la moitié réécriture diffère — et le code a raison : si
> aucune entrée de réécriture n'a bougé, les règles en base sont déjà correctes, et flusher serait une
> opération coûteuse pour rien.
>
> **Changement de comportement à connaître par rapport à #1** : avant #27, **toute** variation de
> l'empreinte déclenchait un flush, la version en faisant partie. Une incrémentation de
> `MTB_CORE_VERSION` était donc un moyen — détourné, mais réel — de forcer une régénération.
> **Ce moyen n'existe plus.** Il n'est pas regretté : il passait par l'édition de `mtb-core.php`, c'est-à-dire
> l'index central que la décision 9 proscrit (§1.2). La voie légitime est désormais celle du §2 — poser
> la règle, l'empreinte la voit.

**Pourquoi séparer** : `mtb_core_mise_a_jour` est documenté au §5 du contrat #1 comme **le seul point
d'accroche d'une migration de données**. Une migration rejouée en boucle par un battement de règles
serait bien pire qu'un flush en trop. La séparation rend ce mode de panne **structurellement
impossible** : un battement de règles ne touche jamais la moitié identité.

Et l'énoncé gelé du §5 (« se déclenche une fois quand la version ou la liste des types de contenu
change ») reste **littéralement vrai** — la séparation l'honore plus exactement qu'avant.

### 4.2 Chaque moitié est écrite quand son effet a réellement eu lieu

`flush_rewrite_rules()` → `WP_Rewrite::flush_rules()` **se re-programme sur `wp_loaded`** tant que
`did_action( 'wp_loaded' )` est faux (`class-wp-rewrite.php:1873-1881`, WordPress 6.9). À `init` 99 il
l'est. La régénération n'a donc **pas encore eu lieu** quand l'option serait écrite.

| Moitié | Écrite | Pourquoi là |
|---|---|---|
| **identité** | `init` 99, immédiatement | son effet — `do_action( 'mtb_core_mise_a_jour' )` — **a déjà eu lieu**. L'écrire tard risquerait de **rejouer une migration** |
| **réécriture** | `wp_loaded` **20** | son effet — la régénération — n'a lieu qu'à `wp_loaded` 10. L'écrire là, c'est n'affirmer « c'est synchronisé » **qu'après que ça l'est** |

> **Règle générale, gelée ici : chaque moitié de l'empreinte est enregistrée au moment où son propre
> effet a réellement eu lieu.**

`wp_loaded` **20 est désormais réservée au chargeur**, au même titre qu'`init` 99. Aucun module ne
l'utilise.

Le contrat #1 §4 (« pas de hook d'activation ») **n'est pas touché** : `wp_loaded` est un hook de
requête, jamais un hook de cycle de vie d'extension.

### 4.3 Compatibilité ascendante — aucun code de migration

L'empreinte stockée avant #27 vaut
`{"version":"0.1.0","types":["mtb_chien","mtb_portee","mtb_resultat"],"taxonomies":[]}`.

Au premier déploiement de #27, avec la comparaison scindée :

- **moitié identité : identique** → `mtb_core_mise_a_jour` **ne se déclenche pas**. Une forme monobloc
  l'aurait déclenché pour un changement qui ne concerne que des URL ;
- **moitié réécriture : clé absente du stocké** → écart → **un flush, une fois**. Exactement ce qu'il
  faut.

Lecture de l'ancienne forme par repli (`$ancienne['reecriture'] ?? array()`). **Aucun code de
migration d'option, aucune version de schéma.** Vérifié : personne ne s'accroche à
`mtb_core_mise_a_jour` aujourd'hui — zéro `add_action`, zéro `has_action` dans tout le dépôt.

### 4.4 Poids — les tableaux entiers, aucun condensé

| Variante | Poids sérialisé |
|---|---|
| Avant #27 (`version` + `types` + `taxonomies`) | **140 o** |
| Moitié réécriture **filtrée** `mtb_` | 1 003 o (total ≈ 1 143 o) |
| Moitié réécriture **non filtrée** — **retenue** | **3 523 o** (total ≈ 3 663 o) |

Repère de proportion : l'option `rewrite_rules` du même site pèse **12 566 o** et est **elle aussi
autochargée**. Le surcoût est de 20 % de ce que le site autocharge déjà pour le même sujet.

Le condensé (`count` + `hash`) est **écarté** : `mtb_core_mise_a_jour` reçoit `$ancienne` et
`$nouvelle`, et le §5 en fait le seul point d'accroche d'une migration de données. Livrer à ce hook
deux empreintes de hachage, c'est livrer un contrat indéboguable le jour où il servira, pour
économiser 3 Ko.

Si le poids devenait un jour un sujet, la parade **n'est pas** le condensé mais `autoload = false`,
l'empreinte n'étant lue qu'une fois par requête. Piste notée, non retenue (une requête SQL de plus à
chaque requête pour 3 Ko : mauvais échange).

## 5. Les quatre angles morts — nommés pour qu'aucune chaîne ne les redécouvre à ses frais

1. **Un filtre de sortie n'est pas vu.** Un module qui modifierait les règles par
   `rewrite_rules_array`, `post_rewrite_rules` ou `<nom>_rewrite_rules` laisse toutes les entrées
   observées identiques : l'empreinte ne bouge pas, la modification ne prend jamais effet d'elle-même.
   **Ce chemin est à éviter** ; s'il devenait nécessaire, il demande un amendement écrit, pas un
   contournement.
2. **Un enregistrement conditionnel fait battre l'empreinte.** Une règle posée sur les seules requêtes
   d'administration (ou l'inverse) donne deux empreintes qui alternent : une régénération à chaque
   requête, indéfiniment. **Le coût est en lenteur, jamais en données** — `mtb_core_mise_a_jour` ne
   dépend que de la moitié identité (§4.1). Sous `WP_DEBUG`, le battement se voit à l'œil nu.
3. **Sans structure de permaliens, la moitié réécriture n'est pas calculée.** La garde
   `is_admin() || get_option( 'permalink_structure' )` du cœur (`class-wp-post-type.php:641`,
   `class-wp-taxonomy.php:385`, WordPress 6.9) normalise `rewrite` **différemment selon le contexte** :
   tableau brut sur une requête publique, tableau normalisé à cinq clés en administration. **Le
   battement était donc déjà armé dans le cœur**, sans qu'aucun module ait rien fait de travers. La
   garde du chargeur est celle du cœur **amputée de son `is_admin() ||`** — précisément le terme qui
   produit la divergence. La moitié identité, elle, reste calculée en toutes circonstances : une
   migration de données ne doit pas dépendre d'un réglage de permaliens.
4. **L'empreinte observe des entrées, pas un résultat.** Elle n'affirme pas que les URL servies sont
   les bonnes : elle affirme que les règles ont été régénérées après le dernier changement connu des
   entrées. **La vérification qu'une URL répond reste manuelle.**

## 6. Détection du battement — une ligne positive, pas une alarme négative

**Aucun compteur, aucun horodatage, aucune fenêtre glissante.** Un compteur stocké à côté de
l'empreinte s'écrirait à chaque changement — donc, sous battement, une écriture d'option
**supplémentaire** par requête : le détecteur aggraverait la pathologie qu'il mesure.

À la place, **une ligne sous `WP_DEBUG` à chaque changement de la moitié réécriture**, via
`Loader::journaliser()` (unique point d'appel à `error_log()`, contrat #1 §12), nommant les sous-clés
changées et le delta d'entrées :

```
[mtb-core] règles de réécriture régénérées — regles_haut +1, permastructs =, etiquettes =
```

**Trois états, pas deux — corrigé le 2026-08-29 après mesure.** La rédaction d'origine de ce contrat
n'en prévoyait que deux (`+n` / `=`). C'était insuffisant, et le défaut a été **mesuré à l'étape 5 du
protocole, pas déduit** : un **changement de slug** conserve le nombre d'entrées de chaque collection
et n'en change que le contenu, donc une ligne « tout `=` » annoncerait une régénération **sans rien qui
l'explique** — précisément pour le défaut que #27 répare. Le troisième état est `modifiée` (même
nombre d'entrées, contenu différent) :

```
[mtb-core] règles de réécriture régénérées — permastructs modifiée, etiquettes =, regles_haut modifiée, regles_bas =, regles_hors =, terminaisons =
```

**Piège de mesure pour quiconque rejouera le protocole** : Apache échappe le non-ASCII dans son
journal — la ligne y sort en `r\xc3\xa8gles de r\xc3\xa9\xc3\xa9criture…`. Un
`grep "règles de réécriture régénérées"` sur les journaux du conteneur renvoie donc **0 en toutes
circonstances** : une mesure qui passe parce qu'elle ne peut pas échouer (décision 56). **Filtrer sur
un motif ASCII**, par exemple `gles de r`.

Sous battement, cette ligne apparaît **à chaque rechargement** : un développeur qui recharge deux fois
comprend immédiatement. En régime normal, **une fois par déploiement**. Coût : zéro octet stocké, zéro
écriture supplémentaire, zéro coût en production.

Bénéfice secondaire, réel : c'est **l'accusé de réception** dont une chaîne future a besoin — « j'ai
déposé mon module, j'ai rechargé, le chargeur me dit `regles_haut +1` ».

**L'alarme négative demandée à l'origine — « journaliser toute règle dont la cible ne porte pas
`mtb_` » — est abandonnée, et il faut savoir pourquoi** : `extra_rules_top` contient **9 règles du
cœur** (4 pour l'API REST, 5 pour le plan de site). L'alarme littérale hurlerait **neuf fausses
alertes par requête**. Une alarme qui crie au loup neuf fois sur dix apprend à la chaîne suivante à ne
pas lire le journal.

**Résidu assumé, écrit** : en production, `WP_DEBUG` étant faux, un battement serait **silencieux**. Il
coûterait un `update_option` et un `flush_rewrite_rules( false )` par requête — mesurable en lenteur,
jamais en perte de données. Accepté.

**Piège de mesure à connaître** : `WP_DEBUG` vaut `!!getenv('WORDPRESS_DEBUG')` et `compose.yaml` ne
passe `WORDPRESS_DEBUG` **qu'au service `wordpress`**, pas à `wpcli`. Les lignes journalisées ne
sortent donc **que sur les requêtes HTTP**, jamais via WP-CLI.

## 7. Ce que #23 et #24 doivent faire — pour qu'elles ne cherchent pas un mécanisme inutile

**C'est la section la plus importante de ce contrat pour les chaînes à venir.** L'issue #27 était
annoncée comme leur prérequis bloquant. **Elle ne l'est pas.**

### 7.1 #24 (`seo`, 52 redirections 301) n'a besoin d'aucune règle de réécriture

Une règle de réécriture traduit une URL en **requête**. Une 301 est une **réponse**. Le chemin est
`template_redirect` : lire le chemin demandé, le chercher dans la table de
`docs/migration/redirections.md`, appeler `wp_safe_redirect( $cible, 301 )` puis `exit`.

**Aucune règle, aucune régénération, aucune dépendance à une option en base** — donc ça marche à la
seconde où le fichier arrive par FTP, sans attendre quoi que ce soit.

Le motif **existe déjà dans le dépôt et est éprouvé** :
`includes/blocks/formulaire-contact/bootstrap.php:69` accroche `template_redirect` en priorité **1**.
Trois vérifications de cœur qui le renforcent pour #24 :

- `template-loader.php:23` — `do_action( 'template_redirect' )` est **inconditionnel** dès que
  `wp_using_themes()`, **y compris sur un 404**. Les 52 anciennes URL, qui ne correspondent à rien, y
  passent bien ;
- `default-filters.php:666` et `:471` — `redirect_canonical` et `wp_old_slug_redirect` sont en
  priorité **10** ; la priorité **1** les pré-empte, donc aucune devinette du cœur ne s'interpose ;
- second précédent côté extension : `includes/admin/coordonnees/ecran.php:303` emploie déjà
  `wp_safe_redirect`.

**Question laissée ouverte à #24, à ne pas découvrir en cours de chaîne** : de quel **groupe** relève
un module qui accroche `template_redirect` en permanence ? Le §2 assigne à `migration/` une
« déclaration à l'inclusion, sous garde `WP_CLI` », or une table de 301 permanente est un service de
front, pas une commande. Recommandation : `includes/migration/redirections-301/`, **sans** garde
`WP_CLI`, **sous amendement déclaré** — le précédent étant le formulaire de contact, qui a déclaré un
amendement écrit au §2 plutôt que de dévier en douce (décision 46). **C'est le contrat de #24 qui
tranche, pas celui-ci.**

### 7.2 #23 (`prive`) n'en a pas besoin non plus

Ce que réclament **T8** et **T40**, c'est un filtre `wp_sitemaps_posts_query_args` (le cœur ne pose que
`post_status => publish`, jamais `has_password => false`), une exclusion de la recherche par
`pre_get_posts`, et le formulaire de mot de passe natif. **Aucune URL nouvelle.**

**Réserve consignée : Q1 est ouverte.** Si l'usage de l'espace protégé devenait « une URL par famille
de chiots », la donne changerait et #23 relèverait alors de la présente convention. En l'état de ce qui
est écrit, non.

## 8. Interdits

- Aucun module n'appelle `flush_rewrite_rules()`. **Inchangé, sans exception** — c'est le seul moyen de
  garantir un flush **par déploiement** et non un par module.
- Aucun module n'utilise `init` **99** ni `wp_loaded` **20** : les deux sont réservées au chargeur.
- Aucun module ne dépend de l'ordre de parcours des groupes. **#27 le renforce** : l'empreinte est
  triée précisément pour ne dépendre d'aucun ordre.
- Aucun module n'enregistre une règle **sous condition de contexte** (`is_admin()` ou équivalent).
- Aucune issue n'édite `mtb-core.php`. **Sans exception, sans amendement possible.**
- `includes/class-loader.php` ne s'ouvre que par **réouverture nominative** (voir l'amendement à
  `issue-1.md` §13).
- Le chargeur ne pose aucune règle, n'en corrige aucune, et ne déclare **aucune** `query_var`.
- Aucun `includes/routing/` : ce dossier n'a pas été créé et ne doit pas l'être (arbitrage 5).

## 9. Arbitrages

| # | Désaccord | Décision | Raison |
|---|---|---|---|
| 1 | Septième groupe `includes/routing/`, filtre exposé par le chargeur, ou élargissement de l'empreinte — **la tâche 1 de l'issue n'offrait que les deux premiers** | **Élargissement de l'empreinte.** Ni groupe, ni filtre | Un groupe ou un filtre transformerait `add_rewrite_rule()` — le geste réflexe de tout développeur WordPress — en **no-op silencieux** : ils créent le piège qu'ils prétendent fermer. Ils laissaient en outre ouvert le défaut réel du §1.3, et amendaient lourdement §1, §2 et §5 du contrat #1. L'élargissement n'ajoute **qu'une permission**, ne retire aucune ligne du §13, ne crée ni fichier ni groupe ni filtre, et ne touche pas `mtb-core.php` |
| 2 | Empreinte = liste d'arguments de réécriture (`slug`, `with_front`, `has_archive`…) **ou** les six collections d'entrée du cœur | **Les six collections** | La liste d'arguments **ne peut pas être lue telle quelle** : `WP_Post_Type::set_props()` place toute la normalisation de `rewrite` derrière `is_admin() \|\| get_option( 'permalink_structure' )`, donc la valeur diffère entre requête publique et requête d'administration — l'empreinte aurait **battu d'elle-même**. Les six collections contiennent déjà ces arguments sous leur forme utile, et ne re-dérivent pas ce que le cœur dérive. La liste d'origine omettait par ailleurs `hierarchical` et `add_rewrite_endpoint()` |
| 3 | Moitié réécriture **filtrée** sur `mtb_` (transposition de `noms_mtb()`) ou **non filtrée** | **Non filtrée** | Le filtre rouvrait exactement le piège que l'arbitrage 1 ferme : une règle de cible `index.php?pagename=…` — sans `mtb_` — serait redevenue un no-op silencieux. Le non-filtrage supprime **mécaniquement** deux angles morts, rend l'alarme du §6 inutile, et réduit la convention à une phrase. Coût mesuré : **+2,5 Ko** dans une option autochargée, à côté d'un `rewrite_rules` de 12,5 Ko lui aussi autochargé. Contrepartie assumée : une mise à jour de WordPress qui change une de ses 9 règles ou 19 étiquettes provoque **une** régénération — comportement correct, pas du bruit. Les deux contributeurs du cœur ont été vérifiés **inconditionnels** (`rest_api_register_rewrites`, `WP_Sitemaps::init`), et le projet n'admet aucune extension tierce |
| 4 | Empreinte monobloc ou scindée identité / réécriture | **Scindée**, deux comparaisons, une seule option | Rend **structurellement impossible** qu'un battement de règles rejoue une migration de données via `mtb_core_mise_a_jour`. Rend en outre la migration d'option de #27 silencieuse côté données (§4.3), et le correctif de séquencement du §4.2 gratuit |
| 5 | Créer `includes/routing/` malgré tout, ne serait-ce qu'avec un `.gitkeep` | **Non, aucun dossier créé** | L'approche retenue n'ajoute aucun fichier. Un septième groupe vide amenderait §1, §2 (« la liste des groupes est close ») et §11 pour zéro ligne de code |
| 6 | Détecteur de battement (compteur, horodatage, fenêtre) ou journalisation | **Une ligne positive sous `WP_DEBUG`** | Un compteur s'écrit à chaque changement : sous battement, il ajoute une écriture d'option par requête — le détecteur aggrave ce qu'il mesure. Disproportionné pour un site d'éleveur |
| 7 | Alarme « règle dont la cible ne porte pas `mtb_` » | **Abandonnée** | Neuf fausses alertes par requête, toutes du cœur (§6). Sans objet une fois l'arbitrage 3 rendu |
| 8 | Stocker les tableaux ou un condensé `count` + `hash` | **Les tableaux entiers** | `mtb_core_mise_a_jour` est le seul point d'accroche d'une migration de données : lui livrer deux hachages, c'est livrer un contrat indéboguable pour économiser 3 Ko |

## 10. Points restés ouverts

- **Q4** (URL accentuées conservées ou normalisées) ne bloque pas #27 — **la convention livrée y est
  indifférente, et c'est ce qui la rend utile à #24.** Mais Q4 détermine si le correctif du §1.3
  servira dans les semaines qui viennent ou dans un an.
- **Q1** (usage de l'espace protégé) : voir §7.2.
- **Q5** (mode de déploiement) : #27 **confirme** le raisonnement du §4 du contrat #1 — le correctif
  prend effet sur la première requête anonyme venue, sans réactivation d'extension et sans passage par
  Réglages → Permaliens. Le mode de déploiement reste sans incidence sur les règles de réécriture.
- **Volet de Q5 non tranché, et il vaut d'être posé** : le nouveau site sera-t-il servi sur
  `mtbrabant.com` lui-même ? Si non, **aucune 301 émise par WordPress ne peut sauver les 52 URL** ni
  les 12 liens absolus survivant dans la prose importée — D5 deviendrait une affaire de DNS et
  d'hébergement, pas de code, et #24 changerait de nature. À savoir avant de planifier #24.
- **Le groupe d'accueil du module de 301 de #24** : voir §7.1.
- **`add_rewrite_endpoint()`** est couvert par la collection `terminaisons`, mais **aucun module du
  projet n'en emploie aujourd'hui** : la couverture est théorique, jamais exercée.

## 11. État de la vérification — mesuré le 2026-08-29

Le protocole du §6 du plan technique a été exécuté **en entier**, l'étape 6 sous une forme substituée
(voir plus bas). Ce qui suit est mesuré ; les raisonnements sont nommés comme tels.

**Décision 56 appliquée : le défaut a été prouvé avant d'être corrigé.** Une règle de réécriture posée
par un mu-plugin de test, **avant** tout correctif, a donné les **trois** mesures attendues :
`/chiots-a-reserver/` → **404** · règle **ABSENTE** de `rewrite_rules` (135 règles, inchangé) ·
**empreinte identique au caractère près** (140 o). C'est la troisième qui prouve la cause — le chargeur
n'avait rien vu. **La dette T6 est bien ce qu'on croyait.**

| Ce qui est prouvé | Mesure |
|---|---|
| Une règle prend effet **sans `wp-admin`, sans Réglages → Permaliens** | `curl` **sans cookie ni `--user`** : **200 dès la première requête**. 135 → 136 règles |
| `mtb_core_mise_a_jour` **ne se déclenche pas** pour un changement d'URL seul | moitié identité rigoureusement inchangée après correctif (§4.3 tenu) |
| La borne | 20 requêtes front/administration alternées → condensé d'empreinte **identique** (`e1afecff…`), **0 ligne journalisée** ; **exactement 1** sur l'ensemble du protocole à ce point |
| **Le défaut du §1.3 est réparé** — c'est la mesure qui compte le plus | slug de `mtb_portee` changé par filtre `register_post_type_args` (le fichier `content/portee/bootstrap.php` **jamais ouvert**) → `/portees-verif-27/` répond **200 dès la première requête, sans aucune autre action**. 23 règles créées pour le nouveau slug, **0 restante** pour l'ancien. Filtre retiré → `/portees/` de nouveau **200** dès la première requête |
| Absence de battement en état propre | 20 requêtes supplémentaires après nettoyage → condensé **identique**, **0 ligne journalisée** |
| Analyse syntaxique | `php -l` sur **134 fichiers** de l'extension, **0 erreur** |

**`/portees/` répond 404 pendant que le slug de test est actif.** Code rapporté tel quel, non préjugé :
WordPress n'a conservé aucune règle pour l'ancien chemin dans ce cas de figure. C'est le comportement
attendu d'un changement de slug **sans** table de redirection — et c'est exactement pourquoi #24 doit
poser ses 301 sur `template_redirect` (§7.1).

**Poids réel : 3 523 o**, `autoload=on`, contre **12 566 o** pour `rewrite_rules`, lui aussi
autochargé. Le §4.4 prédisait ≈ 3 663 o : la prédiction **additionnait deux fois l'enveloppe du
tableau**. Le surcoût réel sur l'existant est de **+3 383 o**, non de +3 523.

### Ce qui n'a pas été vérifié, et pourquoi

- **L'étape 6 en contexte HTTP n'a PAS été exécutée.** Elle écrit `permalink_structure` globalement, et
  la chaîne #37 prenait au même moment des captures du guide dans la même stack. `wp option update
  permalink_structure ''` n'a jamais été lancé, ni sa restauration, ni le `wp rewrite flush --hard` qui
  l'accompagnait.
  **Substitut non destructif exécuté** : filtre `pre_option_permalink_structure` renvoyant `''` dans un
  unique processus `wp eval`, sans une seule écriture — moitié réécriture = **`array()` vide**,
  **GARDE OK** ; condensé de l'option en base **identique** après la mesure, donc rien n'a été écrit.
  **Ce que ce substitut ne prouve pas** : le comportement de `synchroniser_version()` lors d'une
  *transition* permaliens-actifs → permaliens-vides. Par lecture du code, cette transition produit
  **un** flush et **une** ligne journalisée, puis se stabilise. **C'est un raisonnement, pas une
  mesure.**
- **`add_rewrite_endpoint()`** reste couvert en théorie et **jamais exercé** : aucun module du projet
  n'en emploie (déjà noté au §10).
- **Le battement en production** (`WP_DEBUG` faux) reste silencieux par construction — résidu assumé du
  §6, non mesurable ici.

### Un cas de concurrence observé, qui n'est pas un battement

Deux lignes journalisées **identiques à 0,73 s d'intervalle** lors du nettoyage, depuis deux origines
distinctes (le *healthcheck* du conteneur, puis la requête `curl`). Le total du protocole est donc
**5** lignes et non 4. Ce n'est pas un battement — le contrôle en état propre qui a suivi donne
**0 ligne sur 20 requêtes** et un condensé stable. C'est la course entre deux requêtes traversant la
même transition, **cas idempotent déjà documenté dans le docbloc de `synchroniser_version()` depuis
#1** : coût d'un flush en double, une fois.
