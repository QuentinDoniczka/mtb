# Contrat d'interface — Issue #24 — Redirections et référencement

**Gelé le 2026-09-05.** Milestone 9, label `seo`. Lot 18, en parallèle de #23 (page protégée) et #42
(alignement de `design-system/MASTER.md`).

Ce contrat est **le point de réconciliation d'une chaîne à un seul côté** : #24 ne touche aucun
fichier du thème. Il n'y a donc pas eu de `leaddev-front-mtb`, et la section « Blocs enregistrés » du
gabarit habituel est sans objet. Ce que ce contrat gèle, c'est la frontière avec **le cœur de
WordPress**, avec le **contrat fondateur #1**, et avec **#23** — la seule issue du lot avec laquelle
#24 partage un crochet.

> **Convention d'amendement** (décision 65) — ce contrat s'amende par **ajout daté en fin de fichier**,
> jamais par réécriture d'une section numérotée : une citation par numéro de § ne doit jamais se
> périmer. Un amendement porte sa date, son issue, et le motif du changement.

---

## 1. L'empreinte fichiers — l'annoncée est irréalisable, voici la réelle

**L'empreinte déclarée à l'issue est refusée.** Elle annonçait
`includes/seo/redirections/**` et `includes/seo/sitemap/**`.

Fait vérifié : `wp-content/plugins/mtb-core/includes/class-loader.php:145-152` déclare

```php
public const GROUPES = array( 'content', 'fields', 'query', 'blocks', 'admin', 'migration' );
```

— **six noms en dur, liste close par contrat** (`issue-1.md` §2 et §5 : aucun filtre ne permet d'y
ajouter un groupe). `charger()` ne boucle que sur `GROUPES` et `charger_groupe()` ne lit que
`__DIR__ . '/' . $groupe`. **Un `bootstrap.php` posé sous `includes/seo/` ne serait jamais inclus :
pas d'erreur, pas de ligne au journal, pas même la note en `WP_DEBUG` — module mort et silencieux.**
C'est exactement la classe de défaut que le projet traque depuis T6/#27.

`includes/routing/` a été **proposé puis refusé** à #27 (`issue-1.md` §11 : « `includes/routing/` n'a
**pas** été créé »). **`class-loader.php` reste fermé** : la seule réouverture nominative accordée à ce
jour, celle de #27, est refermée. Aucune n'est demandée ici, aucune n'est accordée.

### Empreinte réelle, close

| Chemin | Nature |
|---|---|
| `wp-content/plugins/mtb-core/includes/migration/redirections-301/**` | création |
| `wp-content/plugins/mtb-core/includes/migration/indexation-heritee/**` | création |
| `docs/migration/redirections.md` | **réécriture intégrale** (dettes T38, T-#21-d) |
| `docs/guide/` — une fiche | création (§9) |
| `docs/contracts/issue-24.md` | ce fichier |

**Interdit, sans exception** : `includes/class-loader.php` · `mtb-core.php` · `includes/content/**` ·
`includes/query/**` · `includes/blocks/**` · `includes/fields/**` · tout `donnees/**` · tout fichier
du thème · `theme.json` · `functions.php` · toute feuille sous `themes/mtb/assets/css/**` ·
`design-system/MASTER.md` (**empreinte entière de #42**) · tout `page-protegee/**` (**empreinte de
#23**) · `Makefile` · `compose.yaml` · `docker/**`.

**`docs/contracts/issue-1.md` n'est pas dans cette empreinte.** Son §11 dit lui-même être « tenu à
jour par `/lead-mtb` à la clôture de chaque lot, ce fichier étant hors de l'empreinte de toute
chaîne », et **#23 a la même ligne d'inventaire à y ajouter** — arbre partagé, aucune isolation. Les
deux lignes d'inventaire et le texte de l'amendement au §2 sont **livrés prêts à coller** (§10 et §11
ci-dessous) ; la chaîne #24 ne les écrit pas.

---

## 2. Le groupe d'accueil : `includes/migration/`, sous amendement déclaré

**Décision.** Les deux modules vivent dans `includes/migration/`, **sans garde `WP_CLI` sur leur
partie service de front**, sous **amendement écrit au §2 du contrat #1** (texte au §11).

**Pourquoi pas ailleurs**, jugé sur la colonne « hook et priorité imposés » du §2 de `issue-1.md` :

| Groupe | Verdict |
|---|---|
| `query` | « **aucun hook** — simples déclarations de fonctions ». Un service qui accroche `template_redirect` en permanence contredit la colonne frontalement. **Non.** |
| `admin` | Colonne = `admin_menu`, `admin_init`, `manage_*_columns`, `restrict_manage_posts` ; la recette du §10 impose `if ( ! is_admin() ) { return; }` en première ligne. Un service de **front** y serait activement trompeur. **Non.** |
| `content` · `fields` · `blocks` | N'enregistrent ni type, ni écran de saisie, ni bloc. **Non.** |
| `migration` | Colonne = « déclaration à l'inclusion, **sous garde `WP_CLI`** ». Contredite **sur la seule garde**. **Oui, sous amendement.** |

**Pourquoi `migration/` est le bon endroit sur le fond, et pas seulement le moins mauvais** : la table
des 301 **n'existe que parce que l'ancien site a existé**. Elle est finie, datée, gelée à jamais, et
vit à côté des données dont elle décrit les adresses (`migration/portees-chiens/donnees/`,
`migration/resultats-pages/donnees/`). Qui cherchera dans six ans « pourquoi
`/bhpl/portée-m-2016/` marche encore ? » cherchera dans `migration/`, jamais dans `query/`. Le
`noindex` des cinq contenus relève du même héritage.

C'est la recommandation de `issue-27.md` §7.1, qui écrivait « **c'est le contrat de #24 qui tranche,
pas celui-ci** ». Précédent d'amendement déclaré plutôt que de dérive silencieuse : **décision 46**,
le formulaire de contact.

### Deux modules, pas un — gelé

| Module | Rôle |
|---|---|
| `migration/redirections-301/` | les 46 × 301, la réparation des douze ancres internes, la commande de vérification |
| `migration/indexation-heritee/` | le `wp_robots` des cinq contenus et leur retrait du plan du site |

Trois motifs, tous gelés :
1. **Honnêteté du nom** — un module nommé « redirections-301 » qui poserait `wp_robots` mentirait sur
   son contenu.
2. **Réversibilité.** Si l'éleveuse demande demain « remettez Placement dans Google », la réponse est
   de renommer `indexation-heritee/` en `_indexation-heritee/` — la façon documentée de désactiver un
   module (`class-loader.php`, initiale `_`) — **sans toucher une ligne des 301**. Avec un module
   unique, on désactiverait les 46 redirections en même temps.
3. **Témoins d'échec disjoints.** Le module 1 se prouve vivant par un **code de sortie WP-CLI**, le
   module 2 par une **balise dans le HTML servi**. Deux sondes de nature différente : les réunir dans
   un dossier ferait croire qu'une seule suffit.

---

## 3. Ce que #24 ne fait pas — arbitrages fermés d'avance

### 3.1 Aucun changement de slug. `includes/content/**` reste fermé.

La moitié « normaliser » de la **décision 69** est **déjà acquise, sans une ligne de code** :

- `migration/portees-chiens/donnees/portees.json` — les 27 `slug_source` sont `a1-2025`, `m-2016`,
  `s2-2021`… **sans accent** ;
- `donnees/chiens.json` — les 17 `reference` sont `etch`, `pegaz`, `ray-ban`, `very-best`… **sans
  accent** (`pégaz` → `pegaz`) ;
- `donnees/pages/*.json` — `litterature`, `mentions-legales`, `bhpl`, `travail`, `placement` ;
- et pour l'avenir, `sanitize_title_with_dashes()` appelle `remove_accents()` : **une page future
  titrée « Élevage » produira `elevage` toute seule**. La portée « vaut pour toute URL future » de la
  décision 69 est honorée par le cœur.

**Conséquence gelée** : `content/portee/bootstrap.php:56` (`'slug' => 'portees'`) et
`content/chien/bootstrap.php:78` (`'slug' => 'chien'`) **ne sont pas ouverts**. Le correctif de #27
n'est pas exercé par cette issue.

**Option explicitement écartée** — passer `'slug' => 'chien'` à `'la-meute'`, ce qui aurait rendu **16
fiches sur 17 identiques** au lieu de redirigées. Motifs : coexistence fragile avec la page
`la-meute` que `docs/guide/page-creer-la-page-la-meute.md` **apprend déjà à l'éleveuse à créer** ; 17
permaliens changés ; hors empreinte (#4). **D5 autorise explicitement la 301 — c'est la réponse, pas
un pis-aller.** C'est une décision de conception sur le **schéma de permalien**, que la décision 69
ne préjugeait pas ; elle est motivée ici, elle n'est pas une conséquence de Q4.

### 3.2 Aucun générateur de plan du site

WordPress sert `wp-sitemap.xml` nativement depuis 5.5, sans code. En écrire un violerait la
contrainte 2 dans son esprit et le budget D8 pour zéro gain. **Ce que #24 livre sur le plan du site
est une mesure et un verdict écrit**, plus le seul retrait du §6.

### 3.3 Aucune règle de réécriture, aucune dépendance à la base pour se déclencher

Une règle de réécriture traduit une URL en **requête** ; une 301 est une **réponse**
(`issue-27.md` §7.1). **T6 est payée au lot 10 et n'est pas rouverte.** Aucun
`add_rewrite_rule()`, aucun `flush_rewrite_rules()`, aucune option en base, aucune visite de
`wp-admin` : le module fonctionne **à la seconde où le dossier arrive par FTP**.

### 3.4 `docs/` n'est jamais lu à l'exécution

`issue-27.md` §7.1 écrivait « chercher dans la table de `docs/migration/redirections.md` » — **à lire
comme une image, pas comme une spécification.** `docs/` n'est pas déployé par FTP ; parser du
Markdown à l'exécution est un mode de panne ; et un document se réordonne et s'annote — les 301
changeraient avec.

> **Le code est la source. `docs/migration/redirections.md` est un relevé daté**, reproductible par
> `wp mtb verifier-redirections`.

---

## 4. La carte des 52 — forme gelée

`redirections-301/carte.php` déclare `function carte(): array`, tableau **constant**, indexé par le
**chemin canonique décodé de l'ancien site, barre finale comprise**.

**52 entrées — pas 46.** Les six identités y figurent avec leur verdict, ce qui rend l'absence de
301 **structurelle et non oubliable**, et permet à la commande de vérifier « 52 `<loc>` − 52 clés = 0 »
d'un seul contrôle.

**Trois verdicts, et trois seulement :**

| Verdict | Nombre | Lesquels |
|---|---|---|
| `identique` | **5** | `/` · `/bhpl/` · `/travail/` · `/placement/` · `/contact/` |
| `identique_apres_creation` | **1** | `/la-meute/` — voir §5 |
| `301` | **46** | 27 portées · 17 chiens · `/bhpl/littérature/` · `/mentions-légales/` |

**La valeur d'une entrée n'est jamais une URL figée. C'est une identité**, résolue en permalien **au
moment de la requête** : `array( 'portee', 'M 2016' )`, `array( 'chien', 'pegaz' )`,
`array( 'page', 'litterature' )`.

**Motif, gelé** : une carte de cibles figées devient **fausse en silence** au premier changement de
schéma de permalien, au premier renommage par l'éleveuse — et « faux en silence » est exactement la
classe de défaut que #27 vient de réparer (`issue-27.md` §11 mesure : « `/portees/` répond 404
pendant que le slug de test est actif »).

**Les 46 identités se transcrivent depuis les fichiers de données, jamais de tête** : les 27
`identifiant.valeur` de `donnees/portees.json`, les 17 `reference` de `donnees/chiens.json`, et les
deux slugs de page. Le chemin ancien correspondant se lit dans le `sitemap.xml` source.

### Résolution d'une identité en permalien

| Identité | Voie | Rend `''` quand |
|---|---|---|
| `('portee', $identifiant)` | `mtb_get_portee_par_identifiant()`, clé `'lien'` ; **repli déclaré** : `get_posts()` sur `mtb_portee` par titre, statut `publish`, puis `get_permalink()` | aucune portée publiée ne porte cet identifiant |
| `('chien', $slug)` | `get_page_by_path( $slug, OBJECT, 'mtb_chien' )` + contrôle `'publish'` + `get_permalink()` | aucune fiche publiée ne porte ce slug |
| `('page', $slug)` | `get_page_by_path( $slug, OBJECT, 'page' )` + contrôle `'publish'` + `get_permalink()` | aucune page publiée ne porte ce slug |

**Le repli sur les portées est déclaré, pas discret**, et son motif est gelé :
`mtb_get_portee_par_identifiant()` passe par `Hydratation::contenus()`, qui impose
`'has_password' => false` (`includes/query/portee/hydratation.php:114`). C'est **juste** pour une
lecture publique — une portée protégée n'a rien à faire dans un index — mais **faux pour une 301** :
si l'éleveuse protège une portée par mot de passe, son ancienne URL cesserait de rediriger **sans un
mot**. Le repli conserve la 301 vers la page de mot de passe (200), et la commande **nomme** chaque
cible qui a eu besoin du repli.

`mtb_get_chien()` n'est pas employée : sa signature prend un **identifiant de contenu**, pas un slug
(`includes/query/chien/bootstrap.php:40`). Aucune fonction de lecture ne résout un slug de chien, et
en créer une serait hors empreinte (`includes/query/**` fermé). `get_page_by_path()` est une fonction
du cœur appelée **depuis l'extension** : la frontière du contrat #1 §8 vise le **thème**, pas
l'extension.

---

## 5. Les dix gardes du service 301 — ordre imposé

`redirections-301/service.php`, rappel de `template_redirect` en **priorité 1**.

1. Sortie si `is_admin()`, `wp_doing_ajax()`, `REST_REQUEST` ou `wp_doing_cron()`.
2. Sortie si la méthode n'est ni `GET` ni `HEAD` — une 301 sur un `POST` perd le corps.
3. **Normalisation du chemin demandé** : `wp_unslash()` sur `REQUEST_URI` · **couper la chaîne de
   requête** (`wp_parse_url( …, PHP_URL_PATH )`) · **`rawurldecode()`**, qui couvre d'un seul geste la
   forme percent-encodée du navigateur **et** la forme UTF-8 brute · **rejet dur si un octet nul
   subsiste** · **retrait du préfixe du site** (`wp_parse_url( home_url( '/' ), PHP_URL_PATH )`,
   inerte en Docker, indispensable sur un mutualisé en sous-dossier) · barres initiale et finale
   garanties.
   **Aucune fonction d'assainissement de texte n'est appliquée** — `sanitize_text_field()` détruirait
   le chemin. L'assainissement ici est le décodage, le rejet de l'octet nul, et le fait que la valeur
   ne serve **qu'à une lecture de clé dans un tableau constant** : jamais à une requête, jamais à une
   sortie.
   **La comparaison porte sur le chemin seul, jamais sur l'hôte.** C'est ce qui rend le module
   indifférent au domaine servi — donc utile même si `mtbrabant.com` n'est jamais pointé sur nous
   (BRIEF §15.4, ouverte).
4. Sortie si le chemin n'est pas une clé de la carte — un `isset()` sur 52 entrées, coût de toutes
   les autres requêtes du site.
5. Sortie si `'301' !== $entree['verdict']`. **C'est ici que les six identités sortent, par
   conception, écrite dans la donnée.**
6. Résolution de la cible.
7. **Cible non résolue ⇒ AUCUNE redirection.** *Une 301 vers un 404 déplace la rupture au lieu de la
   supprimer.* On laisse le 404 du thème rendre ses liens de recours (D12), et la commande le signale.
8. **Garde d'hôte** : si l'hôte de la cible diffère de celui de `home_url()`, ne rien faire. Sans
   elle, `wp_safe_redirect()` renverrait silencieusement vers `wp-admin` via `wp_validate_redirect()`.
9. **Anti-boucle, non négociable** : normaliser le chemin de la cible par la **même** fonction et
   sortir s'il égale le chemin demandé.
10. `wp_safe_redirect( $cible, 301 );` puis `exit;` — et rien après.

**`wp_safe_redirect()` n'est JAMAIS appelée à l'inclusion.** C'est une fonction **remplaçable** :
elle n'existe pas encore quand les extensions se chargent, et l'appeler à l'inclusion d'un
`bootstrap.php` est une **erreur fatale immédiate** (`issue-1.md` §3, quatrième interdit). Elle est
parfaitement légitime **dans le rappel**, qui s'exécute bien après `plugins_loaded`. Même règle pour
`home_url()`, `get_permalink()`, `get_page_by_path()`, `get_post_meta()`.

**La chaîne de requête n'est pas reportée sur la cible.** Écart mineur, assumé : aucun paramètre n'a
de sens sur ce site (zéro traceur, D6), et reporter une chaîne arbitraire venue d'un domaine tiers sur
une URL interne est une surface d'attaque gratuite.

**Pourquoi la priorité 1 suffit** : `template-loader.php:23` déclenche `do_action('template_redirect')`
**inconditionnellement, y compris sur un 404** — les 46 anciennes adresses, qui ne correspondent à
rien, y passent bien. `redirect_canonical` et `wp_old_slug_redirect` sont en priorité **10**
(`default-filters.php:666` et `:471`) : la priorité 1 les pré-empte, `redirect_guess_404_permalink()`
compris.

**Interaction connue avec le formulaire de contact** : `blocks/formulaire-contact/bootstrap.php:69`
accroche déjà `template_redirect` en priorité 1. À priorité égale, l'ordre est celui de
l'enregistrement, donc du parcours du chargeur : `blocks` (4ᵉ) avant `migration` (6ᵉ). Sans
conséquence — il ne traite que des `POST`, et aucune des 52 adresses n'est `/contact/` en `POST`.
**Aucun des deux modules ne dépend de cet ordre** (`issue-1.md` §2).

### `/la-meute/` — aucune redirection, et c'est un arbitrage

> **`docs/guide/page-creer-la-page-la-meute.md` est livré** et apprend à l'éleveuse à créer une page
> titrée « La meute », donc de slug `la-meute`, donc servie à `/la-meute/`. **Poser une 301 là
> casserait la page que le guide lui demande de créer.** Il n'existe par ailleurs **aucune cible de
> repli** : `mtb_chien` est déclaré `'has_archive' => false` (`content/chien/bootstrap.php:76`), il
> n'y a donc pas d'archive `/chien/` vers quoi rediriger.
>
> **Verdict : répond 404 avant l'acte d'édition documenté, 200 après — par conception, pas par
> oubli.** La dette **T30** est ainsi honorée sans être masquée. **Écart à D5 assumé et daté**, levé
> le jour où la page est publiée.

**Contrôle imposé, à mesurer et non à supposer** : une fois la page `la-meute` créée et publiée,
`/la-meute/jango/` doit **continuer** de tomber chez nous (priorité 1) et non d'être avalé par la
règle de pages `(.?.+?)/?$`. Mesure **avant et après** la création, les deux relevés consignés.

---

## 6. Le plan du site, et la frontière avec #23

### 6.1 Ce que #24 mesure et ne code pas

| À mesurer | Ce qui rendrait un correctif nécessaire |
|---|---|
| `mtb_portee` et `mtb_chien` sont-ils au plan du site ? | Leur **absence** — auquel cas c'est un défaut de leur enregistrement, pas de #24 : **remonté, pas corrigé** (`content/**` fermé) |
| `mtb_resultat` en est-il exclu ? | Sa **présence**. Attendu : absent — `'public' => false`, `'publicly_queryable' => false`, `'rewrite' => false` (`content/resultat/bootstrap.php:49-54`) |
| L'archive `/portees/` y est-elle ? | **Attendu : non**, le cœur n'a aucun fournisseur d'archives de type. **Verdict prévu : constat écrit, aucun code** — la page est liée depuis la navigation |
| Le fournisseur `users` publie-t-il `/author/<identifiant>/` ? | **Mesure due, correctif hors périmètre** — voir §6.4 |

### 6.2 Le retrait des cinq contenus `noindex` — le seul filtre écrit

`wp_sitemaps_posts_query_args` est **le seul crochet du cœur qui retire réellement une entrée**.
`wp_sitemaps_posts_entry` **ne le peut pas** : `WP_Sitemaps_Posts::get_url_list()` empile le retour du
filtre **sans le tester**, si bien que renvoyer un tableau vide produit un `<url/>` vide — un plan de
site sciemment abîmé. `wp_sitemaps_add_provider` est à la maille du fournisseur entier.

> **Ce verdict n'est acquis qu'après lecture de `get_url_list()` dans le conteneur**, `wp-includes/`
> n'étant pas versionné. La lecture est une étape obligatoire du protocole, **avant** d'écrire la
> première ligne du module.

**Critère de retrait : l'EXISTENCE de la clé `_mtb_robots_source`, pas sa valeur.** Le filtre
`wp_robots`, lui, **lit la valeur**. L'asymétrie est délibérée et son contrôle est gelé au §7 : *le
nombre de contenus portant la clé, le nombre rendus `noindex` et le nombre retirés du plan du site
doivent être égaux à 5.*

### 6.3 Condition de non-collision avec #23 — gelée, et à imposer aux deux contrats du lot

#23 possède **seule** le filtre d'exclusion du contenu **protégé par mot de passe**
(`has_password()`). #24 n'écrit pas ce filtre-là et ne partage **aucun fichier** avec elle : #23 vit
dans `page-protegee/**`, #24 dans `indexation-heritee/plan-du-site.php`. Les cinq contenus `noindex`
**ne sont pas protégés** : ils sont publiés, et un plan de site qui annonce une page `noindex` se
contredit.

`add_filter()` **empile** : deux rappels sur le même hook depuis deux modules distincts s'exécutent
tous les deux, la sortie du premier entrant dans le second. **L'ordre est indifférent à une condition,
et à elle seule :**

> **Chaque rappel MUTE des clés de `$args`. Aucun ne remplace `$args`.**
> Si l'un écrit `$args = array( … );`, il **efface l'autre** — sans erreur, sans avertissement, sans
> une ligne de journal. Le plan du site répond 200 en annonçant une page qui devait en être retirée.

**Forme conforme, imposée :**

```php
function ecarter_les_noindex( array $args, string $type_de_contenu ): array {
	$ancienne = isset( $args['meta_query'] ) && is_array( $args['meta_query'] ) ? $args['meta_query'] : array();
	$clause   = array( 'key' => '_mtb_robots_source', 'compare' => 'NOT EXISTS' );

	$args['meta_query'] = array() === $ancienne
		? array( $clause )
		: array( 'relation' => 'AND', $ancienne, $clause );

	return $args;
}
```

**Formes interdites, à refuser en revue :**

```php
return array( 'post_type' => $type_de_contenu, 'post_status' => 'publish', … ); // INTERDIT
$args = array( … );                                                            // INTERDIT
unset( $args['has_password'] );                                                // INTERDIT
$args['meta_query'][] = $clause;                                               // INTERDIT — voir ci-dessous
```

**Pourquoi l'enveloppe et non l'ajout** : si l'autre filtre avait posé `'relation' => 'OR'`, un ajout
naïf ferait passer nos cinq contenus **par un OU** et l'exclusion serait **sans effet,
silencieusement**. L'enveloppe sous `'relation' => 'AND'` est immunisée. En l'état, #23 emploie
`has_password` et non `meta_query` : le cas est **théorique aujourd'hui**, et c'est précisément
pourquoi il se gèle maintenant.

**Mesure de niveau lot, qu'aucun des deux contrats ne peut produire seul** : le plan du site rendu
**avec les deux modules actifs** ne doit contenir **ni** les 5 `noindex` **ni** le contenu protégé,
dans **le même relevé**.

### 6.4 Le fournisseur `users` — mesuré, rapporté, non corrigé par #24

Le fournisseur `users` du cœur publie `/author/<user_nicename>/` dès qu'un utilisateur a un contenu
publié dans un type public — et **les 44 contenus importés ont un auteur**. Le plan du site publie
donc probablement **l'identifiant de connexion de l'administrateur**, ce qui relève de BRIEF §4
(« zéro donnée personnelle inutile »).

**#24 le mesure et le rapporte ; #24 n'écrit pas le correctif.** Motif gelé : le remède
(`wp_sitemaps_add_provider` renvoyant `false` pour `'users'`) **n'a rien d'un héritage de l'ancien
site** — l'ancien site ne publiait aucune archive d'auteur. Le loger dans `indexation-heritee/`
affaiblirait la **borne 3** de l'amendement au §2 (« périmètre clos et daté, jamais un module de
référencement à vocation ouverte ») **le jour même où elle est écrite**, et le point n'est dans
aucune des sept tâches de l'issue. **Dette nommée, remède connu, trois lignes** — à router par
l'orchestrateur.

---

## 7. États spéciaux — identifiants et comportements figés

| État | Émis / détecté par | Comportement imposé |
|---|---|---|
| `chemin_hors_carte` | requête dont le chemin n'est pas une des 52 clés | **rien** — la requête suit son cours normal |
| `identique` | verdict de carte (5 adresses) | **aucune redirection**, la page répond directement |
| `identique_apres_creation` | verdict de carte (`/la-meute/` seul) | **aucune redirection** ; 404 avant l'acte d'édition documenté, 200 après |
| `cible_non_resolue` | la résolution rend `''` | **aucune redirection** ; le 404 du thème rend ses liens de recours (D12) ; **la commande le signale en échec** |
| `cible_par_repli` | portée trouvée seulement par le repli (protégée par mot de passe) | la 301 est servie ; **la commande le signale en avertissement nommé**, jamais en échec |
| `boucle` | permalien résolu = chemin demandé | **aucune redirection** ; la commande l'attrape sans attendre la requête |
| `hote_etranger` | l'hôte de la cible diffère de `home_url()` | **aucune redirection** |
| `ancre_non_couverte` | `href` vers `mtbrabant.com` absent de la carte et des `formes` | **le lien n'est pas touché** ; la commande **échoue** |
| `contenu_noindex` | méta `_mtb_robots_source` dont `valeur` contient `noindex` | `noindex` + `nofollow` rendus ; contenu **retiré du plan du site** |

---

## 8. Les douze ancres internes

**Mécanisme gelé : 301 + filtre `the_content` priorité 20, tous deux alimentés par la MÊME carte.**
Ce n'est pas un second mécanisme : c'est le même appliqué à un second point d'application.

**Motif du filtre** : les douze `href` sont **absolus vers `https://www.mtbrabant.com/…`** (une seule
en `http://` et en forme encodée). Une 301 posée sur notre WordPress ne les rattrape **que si notre
serveur répond pour ce domaine** — et la propriété du domaine est une **question ouverte** (BRIEF
§15.4). Le filtre les répare dans tous les cas.

Comportement imposé : sortie immédiate si le contenu ne porte pas `mtbrabant.com` (gratuit sur les
100 + contenus qui n'en portent pas, actif sur 7) · les deux schémas et les deux formes d'hôte
couverts · **normalisation par la même fonction que le service 301**, de sorte que la forme
percent-encodée et la forme UTF-8 brute retombent sur la même clé sans code dédié · `esc_url()` sur
le remplacement · **le texte visible de l'ancre n'est jamais touché** · **cible non résolue ou chemin
hors carte ⇒ on ne touche pas au lien**.

**Écarté, motif gelé** : réécrire `donnees/*.json` à la source est **hors empreinte ET inefficace** —
`migration/resultats-pages/pages.php:17-25` établit que l'import est en **création seule** (« Trouvée :
comptée "déjà présente", et AUCUNE écriture »), donc une base déjà peuplée ne bougerait pas d'un
octet.

### La clé `'formes'` — et la mesure qui la conditionne

`migration/portees-chiens/texte.php:162` produit les ancres par
`'<a href="' . esc_url( $url ) . '">'`. **Si** `esc_url()` filtre les caractères hors de sa classe
ASCII, les onze `href` accentués ont été écrits en base **sans leur accent**
(`https://www.mtbrabant.com/bhpl/porte-m-2016/`), et une carte qui ne connaîtrait que la forme
accentuée **ne réparerait rien du tout, sans un mot**.

**Ceci est une hypothèse fondée sur un chemin de code lu, jamais une mesure** — `wp-includes/` n'est
pas versionné. **Mesure due, bloquante pour la forme de la carte, à faire AVANT d'écrire `carte.php`** :
extraire de la base les `href` réellement stockés des 44 contenus et relever leur forme exacte.

- Forme stockée **canonique** ⇒ `'formes' => array()` partout, rien de plus.
- Forme stockée **altérée** ⇒ l'entrée concernée déclare la forme altérée dans `'formes'`, **avec son
  motif** (« forme produite par `esc_url()` à l'import (#20) ; n'a jamais existé sur l'ancien site »).

> **Les `formes` ne sont lues que par la réparation des ancres, JAMAIS par le service 301.** Le
> périmètre des 301 reste exactement les 52 adresses qui ont existé — **borne 3** de l'amendement. La
> commande les compte à part, pour qu'aucune n'entre dans le décompte des 52.

**Ce défaut appartient à #20, pas à #24.** #24 le mesure, le nomme, le contourne au point de rendu,
et le remonte. Il ne le corrige pas dans `donnees/**`.

### Écart déclaré — contenu stocké ≠ contenu servi

> Le contenu **stocké** garde l'ancienne URL ; le contenu **servi** porte la nouvelle. D4 porte sur le
> texte, qui ne bouge pas — mais c'est un écart, et **un écart non écrit n'est imputable à personne**
> (décision 46). Conséquence concrète : si l'éleveuse ouvre la fiche d'`Etch` dans l'éditeur, elle y
> verra encore `https://www.mtbrabant.com/bhpl/portée-m-2016/`. Rien ne l'invite à y toucher, et y
> toucher ne casserait rien.

À reprendre **verbatim** dans `docs/migration/redirections.md`.

---

## 9. Le `wp_robots` des cinq — décision, et le trou de contrainte 1 qu'elle ouvre

**Décision : on HONORE le `noindex`.** Elle est prise **contre** la recommandation du brainstorm, qui
proposait de différer au motif que le site source se contredit.

**Le fait, vérifié dans `docs/migration/source/html/`** : les cinq pages portent bien **deux balises
`robots` contradictoires** dans le même `<head>` — `noindex, nofollow` en tête, puis `index,follow` —
et `INVENTAIRE.md` §10 anomalie 4 refuse explicitement de dire laquelle ferait foi (question **Q23**,
ouverte).

**Les trois arguments qui tranchent, gelés :**

1. **`index,follow` est une constante de gabarit**, présente sur **les 54** fichiers archivés.
   `noindex, nofollow` est le **signal discriminant**, présent sur **5 seulement**, à une position que
   les 48 autres pages n'utilisent pas. Ce n'est pas une contradiction symétrique : c'est un ajout
   délibéré dans l'interface IONOS.
2. **Quand deux directives `robots` se contredisent, la plus restrictive s'applique.** Le comportement
   **effectivement observable** de l'ancien site était donc `noindex`. La **contrainte 4** (« rien de
   l'ancien site ne se perd ») porte sur ce comportement-là : **ne pas honorer, ce serait *changer*
   l'ancien site, pas le préserver.**
3. **D11 ne couvre pas ce cas.** Le BRIEF §14 D11 et §4 énumèrent ce qui est une donnée de domaine :
   « aucun nom de chien, date, affixe, numéro LOF, résultat de test ou de concours ». **Une directive
   `robots` n'en est aucune.** Le brainstorm avait étiré D11 au-delà de son texte.

La présence des cinq au `sitemap.xml` de l'ancien site ne contredit rien : **un plan de site est une
suggestion, une directive `robots` est une directive.**

**Le filtre lit la méta ; il ne code JAMAIS en dur la liste des cinq identifiants.** C'est ce qui le
rend juste si un sixième contenu portait la méta un jour, et indépendant des identifiants de contenu,
qui diffèrent d'une base à l'autre. Comportement : sortie si `! is_singular()` ; lecture de
`_mtb_robots_source` ; vérification que c'est un tableau dont `valeur` est une chaîne contenant
`noindex` ; alors `$robots['noindex'] = true; $robots['nofollow'] = true;` et retrait des clés
contraires. Priorité **20**, après les rappels du cœur.

### Le trou de contrainte 1, et comment il se ferme

Le brainstorm a raison sur un point retenu ici : **un `noindex` posé par filtre est invisible et
irréversible depuis `wp-admin`.** L'éleveuse n'aurait aucun moyen de voir que quatre fiches et sa page
Placement sont retirées des moteurs, ni de l'annuler. C'est le genre de réglage caché que le brief
proscrit.

**Il ne se ferme pas par un réglage** (hors périmètre, et l'issue admet « rien de visible pour elle »).
**Il se ferme par le guide** — D3. `doc-client-mtb` écrit une fiche courte portant :

| Contenu | Titre public | Adresse servie |
|---|---|---|
| Fiche de chien | Halan | `/chien/halan/` |
| Fiche de chien | Ray-Ban | `/chien/ray-ban/` |
| Fiche de chien | Roxane | `/chien/roxane/` |
| Fiche de chien | Youry | `/chien/youry/` |
| Page libre | Placement | `/placement/` |

*(Les titres publics exacts se relèvent **en base**, jamais ne se recomposent depuis un slug.)*

Phrase imposée, à reprendre telle quelle :

> « Ces cinq pages sont en ligne et lisibles par toute personne qui en connaît l'adresse, mais elles
> sont **volontairement tenues à l'écart des moteurs de recherche** — c'est ce que faisait déjà votre
> ancien site. Si vous souhaitez qu'une d'entre elles y revienne, il suffit de le demander. »

**Un état invisible et documenté vaut infiniment mieux qu'un état invisible et tu.**

**Point de vigilance produit, remonté et non masqué** : l'une des cinq est la page **Placement**,
celle qui parle des chiots aux familles. La désindexer est plausiblement contraire à son intérêt
commercial. La décision reste celle ci-dessus — c'est ce que faisait l'ancien site — et le retour en
arrière coûte **une ligne**.

---

## 10. Protocole de vérification — deux contrôles qui ne prouvent pas la même chose

**Ne jamais présenter l'un pour l'autre.** Le premier dit que la table est cohérente avec le
référentiel ; le second dit ce que le serveur **répond**.

### Contrôle 0 — préalables, avant d'écrire une ligne de code

Trois mesures, **aucune déductible du dépôt** : la forme réelle des 12 `href` en base (§8) · la
lecture de `WP_Sitemaps_Posts::get_url_list()` dans le conteneur (§6.2) · le contenu de
`wp-sitemap.xml` et de `wp-sitemap-users-1.xml` (§6.1, §6.4).

### Contrôle 1 — la carte : `wp mtb verifier-redirections`

Commande WP-CLI **sous garde `WP_CLI`**, déclarée avec `shortdesc` et `synopsis` pour que `--help`
sorte en code 0. Elle **n'écrit rien** — ni option, ni contenu, ni méta (**borne 1** de l'amendement).

| Étape | Elle échoue si |
|---|---|
| 0 | le condensé SHA-256 de `docs/migration/source/sitemap.xml` diffère de `bb78eebcd0fa3d8f3b739b6fad9df1ddf49b6abcd49da033d3f78f76cc09cd1e` — *le référentiel a bougé, la mesure ne veut plus rien dire* |
| 1 | une des 52 `<loc>` n'est **ni identique ni couverte** par une clé de carte |
| 2 | une clé de carte n'est **pas** l'une des 52 |
| 3 | **une cible de verdict `301` ne se résout pas en contenu publié** — la ligne nomme l'identité, pas seulement l'URL |
| 3 bis | *(avertissement, pas échec)* la cible n'a été obtenue que par le **repli** du §4 |
| 4 | un permalien résolu égale son chemin demandé (boucle) |
| 5 | un `href` vers `mtbrabant.com` **stocké en base** n'est couvert ni par une clé ni par une `forme`. Attendu : **12 ancres, 9 cibles distinctes** (`m-2016`, `j-2014`, `r-2020`, `n-2017`, `o-2018`, `p-2019`, `s2-2021`, `u2-2023`, `t-2022`) |
| 6 | le nombre de contenus portant `_mtb_robots_source` n'est pas **5** |
| 7 | — *(elle imprime le préfixe de site calculé et le premier chemin normalisé, pour qu'une installation en sous-dossier ne se plante pas en silence)* |

### Contrôle 2 — la réponse réellement servie

```
curl --path-as-is -s -o /dev/null -w '%{http_code} %{redirect_url}\n' http://localhost:3005<chemin>
```

- **Sans cookie ni `--user`** — une mesure en session d'administrateur ne mesure pas ce que voit un
  visiteur (précédent `issue-27.md` §11).
- **`--path-as-is` est obligatoire** : sans lui, curl normalise le chemin et la mesure ne teste plus
  ce qu'on croit.
- **Chaque URL dans ses deux formes** : percent-encodée et UTF-8 brute. 46 × 2 = 92 mesures, plus les
  6 identités.
- **Le second saut est le contrôle qui compte** : chaque cible obtenue est re-mesurée. *Une 301 vers
  un 404 est la rupture déplacée, pas supprimée.* Attendu : `301 <permalien>` puis `200`.
- **`/la-meute/` et `/la-meute/jango/` mesurés deux fois**, avant et après création de la page.
- Pour les cinq `noindex` : balise `robots` du HTML servi **et** absence de leur `<loc>` du sous-plan
  de site correspondant, le nombre de `<loc>` ayant diminué de **5**.

### La sonde d'existence — elle ferme le risque « module mort et silencieux »

**Première étape du script, avant toute autre :**

```
wp mtb verifier-redirections --help >/dev/null 2>&1 || { echo "MODULE NON CHARGÉ"; exit 1; }
```

Sans elle, un module absent donnerait **46 lignes « 404 »** qu'on lirait comme « les redirections ne
marchent pas » au lieu de « le module n'existe pas ». Motif éprouvé au dépôt :
`docker/provision/provision.sh:239` sonde déjà `wp mtb import-fixtures` exactement ainsi, et sa
branche d'échec explique en toutes lettres qu'une commande muette signifie « l'extension n'est pas
chargée ».
**Le module 2 n'a pas de commande** : sa sonde est la balise `robots` mesurée sur `/chien/halan/`, à
exécuter **en premier** parmi ses mesures, pour la même raison.

`php -l` sur **tous** les fichiers des deux modules est une étape obligatoire **avant toute mesure
HTTP** : une erreur de syntaxe dans `carte.php` est un `E_COMPILE_ERROR`, **non rattrapé** par le
`try/catch` du chargeur (`issue-1.md` §12) — site entier par terre.

### Ce qui n'est PAS mesurable maintenant — à écrire comme tel, jamais à taire

L'hôte de production et le fait que `mtbrabant.com` pointe un jour sur nous (BRIEF §15.4) · `https` et
la forme `www` / sans `www` · la préservation du percent-encodage par le serveur frontal · le
comportement d'un moteur de recherche réel face au `noindex`. **Faits d'exploitation, pas faits de
code.**

### Datation

Une mesure n'a de valeur que **datée d'un commit** (décision 68). Le relevé porte : la date, le **sha
du commit** mesuré, le condensé SHA-256 du `sitemap.xml` source, l'**hôte mesuré**, l'**état de la page
`la-meute`**, et la version de WordPress.

---

## 11. Structure imposée de `docs/migration/redirections.md` refait

Le fichier est **périmé** (dettes **T38**, **T-#21-d**) et se refait **intégralement**, jamais ne
s'amende. **Trois faussetés à ne pas reconduire** : « État : 7 URL sur 52 » · l. 10-11, #24 « devra
aussi payer la dette T6 » — **T6 est PAYÉE au lot 10 par #27** · l. 45, page Travail « non livrée
(#17) » — **elle est livrée**. Son § « Avertissement : ces cibles sont provisoires » est périmé aussi :
**Q4 est tranchée** (décision 69).

Sections imposées : (1) ce que le fichier est — *un relevé daté, reproductible ; la source de vérité
est `carte.php`* — et ce qu'il n'est pas — *il n'est jamais lu à l'exécution* · (2) le relevé daté
du §10 · (3) le décompte **52 = 6 identiques + 46 en 301**, les six nommées · (4) le tableau des 52
dans l'ordre du sitemap, colonnes **URL source lisible · URL source encodée · verdict · identité de
cible · permalien servi · code · second saut** · (5) le sort de `/la-meute/`, en section propre et
verbatim du §5 · (6) ce qui n'a pas changé de slug et pourquoi, option `'slug' => 'la-meute'`
explicitement écartée · (7) les douze ancres, leurs 9 cibles, la **forme réellement stockée mesurée**
et les `formes` déclarées le cas échéant · (8) l'écart « stocké ≠ servi », verbatim du §8 · (9) les
cinq contenus non indexés, leur motif, la phrase du guide · (10) ce qui n'est pas mesurable, verbatim
du §10.

---

## 12. Budget et frontières

- **D8 — poids ajouté côté navigateur : zéro octet.** Aucun CSS, aucun JS, aucune police, aucune
  image, aucune mise en file. Deux nuances chiffrées plutôt que tues : sur les 5 pages `noindex`, la
  balise `<meta name="robots">` **existe déjà** (le cœur y écrit `max-image-preview:large`) — on en
  change le contenu, delta ≈ **+10 à +20 octets sur 5 pages, 0 sur les autres** ; sur les 7 contenus
  portant une ancre, la valeur d'un `href` est remplacée par une **plus courte**, delta **négatif**.
  Une réponse 301 n'a pas de corps.
- **D6 — zéro requête sortante, jamais.** Aucun `wp_remote_*`, aucun `file_get_contents` distant, ni
  au rendu ni dans la commande. La commande ne lit que le disque et la base.
- **D10** — aucune extension tierce de redirection ou de référencement, aucun page builder.
- **Frontière** : ces modules ne produisent **aucune mise en page ni règle visuelle**. Le thème n'est
  pas touché, ne les connaît pas, et n'a rien à appeler.

### Conventions de code, rappelées

`declare(strict_types=1);` · garde `if ( ! defined( 'ABSPATH' ) ) { exit; }` · namespace
`MTB\Core\Migration\<Module>` · préfixe `mtb_` sur toute fonction globale · **français littéral,
aucune fonction i18n** (`issue-1.md` §7 — jamais `__()`, `_e()`, `esc_html__()`) · `array()` et jamais
`[]` · conditions de Yoda · tabulations · pas de `?>` final · plafond de syntaxe **PHP 8.1** ·
assainissement à l'entrée, échappement au rendu · WordPress Coding Standards.

**À l'inclusion d'un `bootstrap.php`** (`issue-1.md` §3) : **seuls** `add_action`, `add_filter`,
`define`, `require_once` de ses propres fichiers, déclarations, et gardes de sortie anticipée.
`\WP_CLI::add_command()` à l'inclusion sous garde `WP_CLI` est **imposé** au groupe `migration/` et
déjà pratiqué par `migration/import-fixtures/`.

---

## 13. Interdits

- Le thème n'interroge jamais la base directement, et n'a rien à faire de ces modules.
- L'extension n'émet aucune règle visuelle ni mise en page.
- **`class-loader.php` et `mtb-core.php` ne s'ouvrent pas.** Aucun septième groupe, aucun filtre sur
  `GROUPES`.
- **Aucun fichier de `docs/` n'est lu à l'exécution.**
- **`wp_safe_redirect()` n'est jamais appelée à l'inclusion** — fonction remplaçable, erreur fatale.
- **Aucun `$args = array( … )` ni `return array( … )` dans un rappel de
  `wp_sitemaps_posts_query_args`** (§6.3).
- **Aucune 301 vers une cible non résolue.**
- **Aucune liste d'identifiants de contenu codée en dur** dans le filtre `wp_robots`.
- **Aucun fait de domaine inventé.** Les 27 identifiants de portée, les 17 slugs de chien et les
  titres publics des cinq contenus se **transcrivent depuis les données ou la base**, jamais ne se
  recomposent de tête.

---

## 14. Arbitrages — chaque désaccord, la décision, sa raison

| # | Désaccord | Décision | Raison |
|---|---|---|---|
| 1 | Empreinte annoncée `includes/seo/**` | **Refusée**, remplacée par `includes/migration/{redirections-301,indexation-heritee}/**` | `GROUPES` est une liste close de six noms en dur ; un dossier hors liste est un **module mort et silencieux** |
| 2 | Groupe d'accueil : `query`, `admin` ou `migration` | **`migration`, sous amendement au §2** | Aucune colonne ne colle ; `migration` est le seul contredit **sur la seule garde**, et le seul juste **sur le fond** — la table n'existe que parce que l'ancien site a existé |
| 3 | Un module ou deux | **Deux** | Honnêteté du nom · réversibilité par renommage en `_indexation-heritee/` sans toucher aux 301 · témoins d'échec de nature différente |
| 4 | Table dans `docs/` (suggéré par `issue-27.md` §7.1) ou dans le module | **Dans le module, en PHP** | `docs/` n'est pas déployé par FTP ; parser du Markdown à l'exécution est un mode de panne ; un document se réordonne et s'annote |
| 5 | Cibles figées ou identités résolues | **Identités** | Une cible figée devient **fausse en silence** au premier changement de schéma ou renommage — la classe de défaut que #27 vient de réparer |
| 6 | 46 entrées ou 52 | **52** | Les six identités écrites rendent l'absence de 301 **structurelle et non oubliable**, et la commande vérifie « 52 − 52 = 0 » d'un seul contrôle |
| 7 | `/la-meute/` : 301 ou rien | **Rien**, verdict `identique_apres_creation` | Une 301 **casserait la page que `docs/guide/page-creer-la-page-la-meute.md` apprend à créer**. Aucune cible de repli : `mtb_chien` est `has_archive => false` |
| 8 | `'slug' => 'chien'` → `'la-meute'` (16 identités au lieu de 16 301) | **Écarté** | Coexistence fragile avec la page `la-meute` du guide ; 17 permaliens changés ; hors empreinte (#4). **D5 autorise explicitement la 301** |
| 9 | Écrire un générateur de plan du site | **Non** | Le cœur le fait depuis 5.5 ; en écrire un violerait la contrainte 2 et le budget D8 pour zéro gain |
| 10 | `wp_robots` : honorer ou différer (recommandation du brainstorm) | **Honorer** | Signal **discriminant** contre constante de gabarit · la directive la plus restrictive s'applique, donc le comportement **observable** de l'ancien site était `noindex` (contrainte 4) · **D11 ne couvre pas une directive `robots`** |
| 11 | Le trou de contrainte 1 ouvert par un `noindex` invisible | **Fermé par le guide, pas par un réglage** | Hors périmètre d'ajouter un écran ; un état invisible et **documenté** vaut mieux qu'un état invisible et tu (D3) |
| 12 | Douze ancres : 301 seule, réécriture des données, ou filtre | **301 + `the_content`, même carte** | Les `href` sont **absolus** et la propriété du domaine est ouverte ; réécrire `donnees/**` est hors empreinte **et inefficace** (import en création seule) |
| 13 | Retrait du fournisseur `users` du plan du site | **Mesuré et rapporté, non corrigé ici** | Le remède n'a rien d'un héritage de l'ancien site ; l'y loger affaiblirait la **borne 3** le jour même où elle est écrite, et le point n'est dans aucune tâche de l'issue |
| 14 | Qui écrit dans `docs/contracts/issue-1.md` | **Pas cette chaîne** | Son §11 se dit « hors de l'empreinte de toute chaîne » et **#23 a la même ligne à y ajouter** — arbre partagé. Lignes **livrées prêtes à coller** |

---

## 15. Texte de l'amendement au §2 du contrat #1 — à coller en fin d'`issue-1.md`

> **Amendement — 2026-09-05, issue #24 : le groupe `migration/` accueille des services de front, sans
> garde `WP_CLI`**
>
> Le §2 assigne à `migration/` une « déclaration à l'inclusion, sous garde `WP_CLI` ». L'issue #24 y
> dépose **deux modules qui accrochent cinq hooks de front en permanence** — `redirections-301/`
> (`template_redirect` 1, `the_content` 20) et `indexation-heritee/` (`wp_robots` 20,
> `wp_sitemaps_posts_query_args` 10, **`wp_sitemaps_add_provider` 10**) — et **déclare cet écart
> plutôt que de dévier en douce**.
>
> *(Le cinquième hook a été ajouté le 2026-09-05 par l'exception motivée au §6.4, datée en fin de ce
> contrat. Cette énumération a été corrigée dans le même geste : **un amendement qui sous-déclare sa
> propre portée est exactement ce qu'il est censé empêcher.** Correction d'un décompte factuel avant
> tout collage dans `issue-1.md` — aucun arbitrage n'est réécrit.)*
> Précédent : décision 46, le formulaire de contact ayant déclaré un amendement pour son second hook.
>
> **Pourquoi `migration/` et pas ailleurs.** L'empreinte annoncée à l'issue (`includes/seo/**`) est
> **irréalisable** : `class-loader.php:145-152` déclare six groupes en dur, liste close (§2 et §5), et
> un dossier hors des six n'est **jamais inclus, sans erreur ni ligne de journal** — module mort et
> silencieux. `includes/routing/` a été **proposé puis refusé** à #27 (§11), et `class-loader.php`
> reste fermé. La recommandation d'`issue-27.md` §7.1 est retenue.
> **Et c'est le bon endroit sur le fond** : la table des 301 **n'existe que parce que l'ancien site a
> existé**. Elle est finie, datée, gelée à jamais, et vit à côté des données dont elle décrit les
> adresses. Qui cherchera dans six ans « pourquoi `/bhpl/portée-m-2016/` marche encore ? » cherchera
> dans `migration/`, jamais dans `query/`. Le `noindex` des cinq contenus relève du même héritage.
>
> **Trois bornes, sans lesquelles cet amendement n'a pas lieu d'être. Un module de `migration/` qui
> accroche un hook de front :**
>
> 1. **est en lecture seule** — jamais `update_option`, `wp_insert_post`, `update_post_meta`,
>    `wp_set_object_terms`, `wp_delete_post`. Il lit, il répond ;
> 2. **ne dépend d'aucun état en base pour se déclencher** — sa table est dans ses fichiers ; il
>    fonctionne à la seconde où le dossier arrive par FTP, **sans réglage, sans visite de `wp-admin`,
>    sans régénération de règles de réécriture**. *(Lire la donnée du contenu qu'il est en train de
>    servir — une méta, un permalien — n'est pas un état de configuration et ne tombe pas sous cette
>    borne.)* ;
> 3. **a un périmètre clos et daté** — il ne traite que des adresses ou des faits **énumérés** de
>    l'ancien site, et **ne devient jamais un routeur général** ni un module de référencement à
>    vocation ouverte.
>
> Cet amendement **n'ouvre pas** `migration/` à n'importe quoi, **n'ajoute aucun groupe**, **n'ajoute
> aucun filtre sur `GROUPES`**, et **ne touche ni `mtb-core.php` ni `class-loader.php`**. Un module
> futur qui voudrait un hook de front dans `migration/` sans satisfaire les trois bornes doit demander
> son propre amendement, écrit et daté.

### Lignes d'inventaire à ajouter au §11 d'`issue-1.md`

| Groupe | Module | Issue | Rôle |
|---|---|---|---|
| `migration/` | `redirections-301` | #24 | Table gelée des 52 adresses de l'ancien site ; 301 sur `template_redirect` 1 ; réparation des ancres internes sur `the_content` 20 ; commande `wp mtb verifier-redirections` |
| `migration/` | `indexation-heritee` | #24 | `wp_robots` « noindex, nofollow » et retrait du plan du site pour les contenus portant `_mtb_robots_source` (5 aujourd'hui) ; retrait du fournisseur `users` du plan du site (exception motivée au §6.4, datée du 2026-09-05) |

---

## 16. Ce qui n'est pas mesuré à l'heure du gel — aucune de ces lignes n'est présentée ailleurs comme une mesure

1. Le comportement exact d'`esc_url()` sur un chemin accentué, donc **la forme réellement stockée des
   12 `href`**. `wp-includes/` n'est pas versionné. **Mesure due, bloquante pour la forme de la
   carte** (§8).
2. Que `wp_sitemaps_posts_entry` ne peut pas retirer une entrée. **Mesure due** : lire
   `WP_Sitemaps_Posts::get_url_list()` dans le conteneur (§6.2).
3. Le contenu réel de `wp-sitemap.xml` — présence de `mtb_portee` et `mtb_chien`, absence de
   `mtb_resultat`, absence de l'archive `/portees/`. **Déduits des arguments d'enregistrement lus,
   jamais mesurés.**
4. Que le fournisseur `users` publie l'identifiant de connexion de l'administrateur (§6.4).
5. Que `/la-meute/jango/` tombe bien chez nous une fois la page `la-meute` créée. Le raisonnement est
   solide ; **ce n'est pas une mesure** (§5).
6. Le comportement de curl sur un chemin UTF-8 brut face à ce serveur.
7. Le compte « 12 ancres vers 9 cibles », repris de `docs/migration/portees-chiens.md` § « Obligation
   pour #24 » et **non recompté en base** à l'heure du gel.
8. Les titres publics des cinq contenus `noindex` — **à relever en base**, jamais à recomposer.

---

## 17. Résidu assumé

**En production, rien ne signale une carte périmée.** Si l'éleveuse renomme un identifiant de portée
ou un slug de chien, la 301 correspondante cesse de résoudre — et **aucun témoin ne se déclenche**.
C'est la même classe de résidu que `make css` : le site reste correct, seulement une ancienne adresse
cesse d'être rattrapée, et rien ne le dit.

**Le témoin est `wp mtb verifier-redirections`, et il se joue en recette, pas en production.** C'est
écrit ici plutôt que masqué par un avis d'administration hors périmètre.

---

# Amendement — 2026-09-05, issue #24 : le fournisseur `users` du plan du site est retiré

> Ajout daté, conforme à la **convention d'amendement** déclarée en tête de ce contrat (décision 65) :
> aucune section numérotée ci-dessus n'est réécrite. Les §6.4, §14 arbitrage 13 et §16 point 4 restent
> lisibles tels qu'ils ont été gelés ; **cet amendement les contredit ouvertement, et dit pourquoi.**

## A. Ce que le contrat gelé disait, et qui est renversé

Le **§6.4** et l'**arbitrage 13** écartaient le correctif du périmètre de #24 : *« mesuré et rapporté,
non corrigé »*. Le motif gelé était solide — le remède *« n'a rien d'un héritage de l'ancien site »*,
l'ancien site ne publiait aucune archive d'auteur, et loger ce filtre dans `indexation-heritee/`
affaiblirait la **borne 3** de l'amendement au §2 du contrat #1 (« périmètre clos et daté, jamais un
module de référencement à vocation ouverte ») **le jour même où elle est écrite**.

**Le §6.4 subordonnait ce raisonnement à une mesure due.** Elle est faite. Elle change le poids des
deux plateaux, et la décision avec.

## B. La mesure, qui n'était pas faite au gel

Relevée le **2026-09-05**, base de développement, **WordPress 6.9**, hôte `http://localhost:3005/`,
commit `a765abf` :

| Fait mesuré | Relevé |
|---|---|
| `wp-sitemap.xml` listait-il un sous-plan `users` ? | **Oui** — `http://localhost:3005/wp-sitemap-users-1.xml` |
| Que contenait ce sous-plan ? | **Une seule entrée** : `http://localhost:3005/author/admin/` |
| `user_nicename` diffère-t-il de `user_login` ? | **Non. Les deux valent `admin`.** |

**C'est la question que le §6.4 laissait ouverte, et elle tombe du mauvais côté.** Si
`user_nicename` avait différé de `user_login`, l'adresse n'aurait publié qu'un pseudonyme et le sujet
aurait changé de nature — il n'y aurait pas eu de fuite d'identifiant, et le §6.4 serait resté debout.
Ce n'est pas le cas : **le plan du site publiait littéralement l'identifiant de connexion de
l'administrateur**, ce qui relève de **BRIEF §4** (« zéro donnée personnelle inutile »).

## C. La décision, et pourquoi elle ne fait pas céder la borne 3

**Décision : le fournisseur `users` est retiré, ici, dans `indexation-heritee/plan-du-site.php`.**
Un rappel sur `wp_sitemaps_add_provider` renvoie `false` **pour le seul nom `'users'`** et laisse
ressortir tout autre fournisseur **inchangé**. Aucune liste de fournisseurs n'est écrite en dur.

Le crochet est à la **maille du fournisseur entier**, et c'est précisément la maille voulue : on ne
retire pas une entrée d'un plan, on retire **une archive qui n'avait aucune raison d'exister sur ce
site**.

**La borne 3 tient, et voici à quoi elle tient.** Ce module ne devient pas un module de référencement à
vocation ouverte : il retire **un fournisseur nommé, un seul**, sur un fait mesuré et daté. Il n'expose
aucun réglage, n'ouvre aucune extension, ne prend aucune règle générale. **Toute demande ultérieure de
ce genre exige son propre amendement, écrit et daté** — celui-ci ne fait pas jurisprudence au-delà de
son objet.

**Ce qui a été pesé, et tranché dans l'autre sens qu'au gel** : la borne 3 protège contre la dérive
d'un module d'héritage vers un fourre-tout de référencement. Elle ne vaut pas qu'on publie l'identifiant
de connexion de l'administrateur en attendant l'issue qui aurait le bon nom. **Une borne de conception
ne prime pas sur une donnée personnelle exposée.**

## D. Vérifié après le correctif

- `wp-sitemap.xml` ne liste **plus** `wp-sitemap-users-1.xml` ; les **cinq** autres sous-plans y restent
  (`posts-post`, `posts-page`, `posts-mtb_chien`, `posts-mtb_portee`, `taxonomies-category`).
- **Aucun `/author/` ne subsiste dans aucun des plans du site** — les six documents relevés en comptent
  zéro.
- **Rien d'autre n'a été retiré** : `posts-mtb_chien` compte toujours **18** entrées et `posts-page`
  **7**, valeurs inchangées par ce correctif.
- **Non-régression** : `wp mtb verifier-redirections` sort toujours en **code 0**.

## E. Résidu, nommé et non masqué

**Le plan du site ne publie plus l'identifiant ; l'énumération reste possible pour qui la cherche.**

| Adresse | Réponse mesurée après correctif |
|---|---|
| `/author/admin/` | **200** — l'archive d'auteur répond toujours |
| `/?author=1` | **301** vers `/author/admin/` — la redirection d'énumération du cœur est intacte |
| `/wp-sitemap-users-1.xml` | **200**, mais le corps servi est **du HTML, pas un plan du site**, et ne contient **aucun** `/author/` |

**#24 ne ferme pas ce vecteur, et ne prétend pas le fermer.** Le fermer relève d'un autre geste —
neutraliser la requête d'auteur ou ses règles de réécriture — qui n'est ni un héritage de l'ancien
site, ni dans les sept tâches de cette issue. **Dette nommée, à router par l'orchestrateur.**

Noter aussi que `/wp-sitemap-users-1.xml` répond **200 sur un corps HTML** au lieu d'un 404 franc :
c'est le comportement du cœur quand un fournisseur est retiré de l'index, pas un effet de ce module.
Écrit ici pour qu'une revue future ne le lise pas comme une fuite résiduelle.

---

# Relevé des mesures dues — ce que le §16 laissait ouvert au gel

Le §16 énumérait huit points **« non mesurés à l'heure du gel »**, en interdisant de les présenter
ailleurs comme des mesures. Ils ont été mesurés le **2026-09-05** (commit `a765abf`, WordPress 6.9,
hôte `http://localhost:3005/`). **Le §16 reste lisible tel quel ci-dessus ; ce tableau le solde.**

| § 16 | Point laissé ouvert | Mesure |
|---|---|---|
| 1 | Forme réellement stockée des 12 `href` | **Accents intacts, UTF-8 brut** (`…/bhpl/portée-m-2016/`). L'hypothèse d'altération par `esc_url()` est **infirmée** — les `formes` de la carte sont vides **par mesure**, non par oubli |
| 2 | `wp_sitemaps_posts_entry` ne peut pas retirer une entrée | **Confirmé** par lecture de `WP_Sitemaps_Posts::get_url_list()` dans le conteneur (WP 6.9) : le retour du filtre est empilé sans être testé |
| 3 | Contenu réel de `wp-sitemap.xml` | `mtb_portee` et `mtb_chien` **présents** ; `mtb_resultat` **absent** (son sous-plan répond 404) ; **aucune** archive `/portees/` |
| 4 | Le fournisseur `users` publie-t-il l'identifiant de connexion ? | **Oui** — voir l'amendement ci-dessus. Corrigé |
| 5 | `/la-meute/jango/` tombe-t-il bien chez nous une fois la page créée ? | **Mesuré, avant et après.** `/la-meute/` : **404 → 200**. Et après création, `/la-meute/jango/`, `/la-meute/pégaz/` et `/la-meute/gribouille/` rendent **toujours 301** vers `/chien/…` : la règle de pages du cœur ne les avale pas, la priorité 1 la pré-empte. **Ce n'est plus un raisonnement, c'est une mesure** |
| 6 | Comportement de curl sur un chemin UTF-8 brut | **Mesuré, et il a fallu s'y reprendre.** Les 30 adresses accentuées rendent le **même 301 vers la même cible** dans leurs deux formes. **Piège de méthode consigné** : lancée depuis le shell Git Bash de Windows, la mesure donnait 404 sur toutes les formes brutes — le shell transcodait l'UTF-8 en Latin-1 (`%e9` au lieu de `%C3%A9`) et mesurait donc **une adresse qui n'a jamais existé**. La mesure valable se joue **depuis le conteneur** |
| 7 | « 12 ancres vers 9 cibles », repris d'un document et non recompté | **Recompté en base** : **12 liens, 6 fiches publiées, 9 cibles distinctes.** Le compte est juste |
| 8 | Titres publics des cinq contenus `noindex` | **Relevés en base** : **Halan**, **Ray-Ban**, **Roxane**, **Youry**, **Placement**. Conformes à la table du §9 |

## Les deux contrôles du §10, joués

**Ils ne prouvent pas la même chose et ne se présentent jamais l'un pour l'autre.**

- **Contrôle 1** — `wp mtb verifier-redirections` : **code de sortie 0**, huit étapes conformes.
  52 adresses au référentiel = 52 clés en carte ; **46 cibles résolues, dont 0 par le repli** ; 12 liens
  internes vers 9 cibles ; 5 contenus portant `_mtb_robots_source`.
- **Contrôle 2** — ce que le serveur **répond**, au `curl --path-as-is`, **sans cookie ni `--user`** :
  **82 mesures pour 52 adresses** (52 en forme encodée, 30 accentuées re-mesurées en UTF-8 brut).
  **46 × `301` → `200` au second saut** · **5 × `200`** (les identiques) · **1 × `404`**, `/la-meute/`,
  **par conception**. Aucune boucle, aucune 404 non prévue.
- **Le `noindex` rendu** : les cinq servent
  `<meta name='robots' content='max-image-preview:large, noindex, nofollow' />`, et trois témoins
  négatifs (`/chien/jango/`, `/portees/m-2016/`, `/litterature/`) ne portent que `max-image-preview`.
- **Le retrait du plan du site**, avec l'attribution que **ni #23 ni #24 ne pouvait produire seule**
  (§6.3) : sous-plan `mtb_chien` **22 publiés / 18 listés**, écart **4** — les quatre fiches marquées ;
  sous-plan `page` **9 / 7**, écart **2** = `placement` (#24) **+** `espace-prive` (#23) ; sous-plan
  `mtb_portee` **33 / 32**, écart **1** = la portée protégée par mot de passe (**#23**).
  **Total retiré par #24 : exactement 5.** Les deux modules cohabitent sur
  `wp_sitemaps_posts_query_args` **sans qu'aucun n'efface l'autre** — la convention de mutation gelée au
  §6.3 est donc **vérifiée en service**, et non seulement en lecture de code.
