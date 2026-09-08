# Contrat d'interface — Issue #49 — Neutraliser l'énumération des auteurs (`/author/`, `?author=`)

**Gelé le 2026-09-08.** Milestone 13, labels `seo` et `feature`. Lot 20, **après** les chaînes #50
(`indexation-heritee/plan-du-site.php`) et #53 (`redirections-301/commande.php`), toutes deux
committées. Dette **T105** de `docs/ETAT.md`.

Ce contrat est **le point de réconciliation d'une chaîne à un seul côté** : #49 ne touche aucun fichier
du thème. Il n'y a donc pas eu de `leaddev-front-mtb`, et la section « Blocs enregistrés » du gabarit
habituel est **sans objet et le dit** (§10). Ce que ce contrat gèle, c'est la frontière avec **le cœur de
WordPress** — un chemin de code **non versionné, absent de l'arbre, donc à mesurer et non à croire** — et
la relève des **trois bornes** de l'amendement au §2 du contrat #1.

> **Convention d'amendement** (décision 65, reconduite de #50) — ce contrat s'amende par **ajout daté en
> fin de fichier**, jamais par réécriture d'une section numérotée : une citation par numéro de § ne doit
> jamais se périmer. Un amendement porte sa date, son issue, et le motif du changement.

> **Ce contrat est gelé APRÈS la mesure**, contrairement à #50. Les quatorze questions dues ont été
> jouées **dans le conteneur par le lead de chaîne** le 2026-09-08 au commit `2574bf9` — le
> `leaddev-back-mtb` n'a pas d'outil de shell et l'a **déclaré au lieu de le maquiller**. Le §3 est donc
> un **relevé**, pas une déduction. **Aucune ligne de ce contrat ne présente une déduction comme une
> mesure** ; ce qui reste déduit est isolé au §14 et nulle part ailleurs. L'interdit institué par #24
> §16 est ici la règle de rédaction.

---

## 1. L'empreinte fichiers

| Chemin | Nature |
|---|---|
| `wp-content/plugins/mtb-core/includes/migration/indexation-heritee/archives-d-auteur.php` | **création** |
| `wp-content/plugins/mtb-core/includes/migration/indexation-heritee/bootstrap.php` | modification — le raccordement l'exige, plus deux blocs de commentaire rendus faux par #49 |
| `wp-content/plugins/mtb-core/includes/migration/indexation-heritee/plan-du-site.php` | modification — troisième acte daté du bloc d'exception, plus deux dettes de prose |
| `docs/contracts/issue-49.md` | ce fichier |

**Interdit, sans exception** : `migration/redirections-301/**` (**chaîne #53, committée — lecture
permise, écriture jamais**) · `docs/contracts/issue-1.md` (§12 du présent contrat livre le texte prêt à
coller ; **le lead l'applique, pas cette chaîne** — décision 70) · `docs/contracts/issue-50.md`,
`issue-53.md`, `issue-24.md`, `issue-23.md`, `issue-52.md` (**contrats gelés, ils ne se rouvrent pas** ;
les relèves sont au §8) · `docs/ETAT.md` · `docs/guide/**` · `conversion.php` · `fait.php` ·
`class-loader.php` · `mtb-core.php` · `includes/query/**` · `includes/content/**` · **tout fichier du
thème** · `theme.json` · toute feuille sous `themes/mtb/assets/css/**` · `Makefile` · `compose.yaml` ·
`docker/**`.

**Aucune feuille CSS n'est touchée : `make css` est sans objet**, et aucun artefact `*.min.css` n'entre
à cette empreinte.

**Aucune fiche de `docs/guide/` n'est écrite, et aucune n'est due.** #49 ne change **rien** de ce que
l'éleveuse voit : pas un écran, pas un mot, pas un bouton, pas une adresse qu'elle utilise. **D3 est sans
objet ici, et c'est écrit plutôt que tu.**

---

## 2. Le défaut — et ce que l'énoncé de l'issue ne portait pas

L'issue nomme deux adresses : `/author/admin/` (200) et `/?author=1` (301 vers la première). **La mesure
en a trouvé six, et deux comptes au lieu d'un.**

### 2.1 Ce qui fuit réellement, mesuré

| Adresse | Code | Taille | Type | Ce qui fuit |
|---|---|---|---|---|
| `/author/admin/` | 200 | 16 379 | text/html | `<title>admin`, `<body class="… author-admin author-1 …">` |
| **`/author/fabienne/`** | **200** | **14 457** | text/html | **`<title>Fabienne Guéneau`, `<body class="… author-fabienne author-2 …">`** |
| `/?author=1` | 301 | 0 | — | `Location: …/author/admin/` |
| **`/?author=2`** | **301** | 0 | — | **`Location: …/author/fabienne/`** |
| `/?author=3`, `/?author=999` | 404 | 18 284 | text/html | — |
| **`/?author_name=admin`** | **200** | 16 379 | text/html | la même archive, **forme non nommée par l'issue** |
| **`/author/admin/feed/`** | **200** | **1 704** | **application/rss+xml** | flux RSS de l'auteur |

**Les trois faits qui commandent le périmètre :**

1. **Il y a DEUX comptes, et `user_nicename` = `user_login` pour les deux.** Relevé : `1,admin,admin,admin,administrator` et `2,fabienne,fabienne,"Fabienne Guéneau",editor`. **Le second est celui de l'éleveuse.** Un correctif borné à `admin` — la lettre de l'énoncé — aurait laissé **son identifiant de connexion publié**, *un défaut plus grave que celui qu'il répare.*
2. **L'oracle d'énumération est réel et précis.** `?author=1` et `?author=2` rendent des `Location`
   **distincts** ; `?author=3` et `?author=999` rendent 404. **Trois réponses discernables : la liste
   complète des comptes se lit en quelques requêtes.** L'énumération réelle, ce n'est pas « lire
   `/author/admin/` » — il faut déjà connaître le slug — c'est **parcourir `?author=N` et lire
   l'en-tête `Location`**. *C'est la forme la plus dangereuse, et c'est celle que l'énoncé nommait le
   moins.*
3. **Deux formes que l'issue ne nomme pas fuient autant** : `?author_name=<slug>` et
   `/author/<slug>/feed/`. Les fermer à moitié serait **une moitié présentée comme un tout** — la faute
   que #24 §16 institue comme interdite.

### 2.2 La ligne de DoD, dite exactement

**Aucune ligne D1–D12 ne couvre nommément une fuite d'identifiant de connexion.** L'issue sert la règle
transverse du **BRIEF §4 — « zéro donnée personnelle inutile »** — et, dans son esprit, **D5** : une URL
du site répond exactement ce qu'elle doit répondre. **C'est une dette de vie privée, elle ne s'habille
pas en obligation contractuelle.**

**Ce qui est dit sans dramatiser** : le site n'est pas en ligne, et un identifiant de connexion n'est pas
un secret — c'est la moitié publique d'un couple. **L'urgence est faible ; la dette se paie maintenant
parce qu'elle est petite, cernée, dans un module qu'on ouvre de toute façon, et parce qu'elle est déjà
nommée, chiffrée et portée par une issue.**

---

## 3. Le relevé — joué dans le conteneur, avant la première ligne de code

**Contexte de mesure** : 2026-09-08 · commit **`2574bf9`** · **WordPress 6.9** · hôte
`http://localhost:3005` · mesuré **depuis le conteneur**, `curl --path-as-is --connect-to
"localhost:3005:localhost:80"`, **sans cookie ni `--user`** · `permalink_structure` = `/%postname%/` ·
`blog_public` = `1` · `debug.log` **0 octet**.

**Calibrage reproduit à l'octet** — les valeurs de référence de #50 (commit `4bc8daa`) sont **toutes
inchangées** deux commits plus tard : `/nexiste-pas-du-tout/` → `404 text/html 18284` ·
`/wp-sitemap-users-1.xml` → `404 text/html 18284` (**#50 intact**) ·
`/wp-sitemap-posts-mtb_resultat-1.xml` → `404 text/html 18284` · `/` `25491` · `/portees/` `31819` ·
`/chien/jango/` `28084` · `/contact/` `19409` · `/travail/` `35411`. **md5 du corps de 404 de
référence : `d9f9bae7edf3ba3b6787b341d41688cd`.**

### 3.1 Les quatorze questions — verdicts

| # | Verdict | Relevé, verbatim |
|---|---|---|
| **M1** | **Deux comptes, les deux fuient** | `1,admin,admin,admin,administrator` · `2,fabienne,fabienne,"Fabienne Guéneau",editor`. **`user_nicename` = `user_login` pour les deux.** Contenus publiés : compte 1 en a (18 `mtb_chien`, 27 `mtb_portee`, 61 `mtb_resultat`, 6 `page`, 1 `post`), **compte 2 en a un** (1 `mtb_portee`) |
| **M2** | **Sans objet en l'état** | Aucun compte à zéro contenu publié n'existe aujourd'hui. **Non mesuré, et la propriété livrée le rend sans importance** : le rappel ne lit aucun compte (§14.1) |
| **M3** | **Confirmée. C'est le cœur** | `/?author=1` rend `X-Redirect-By: WordPress`, `Location: http://localhost:3005/author/admin/`. Émetteur : `redirect_canonical`, **priorité 10** (`default-filters.php:666`, relevé par #50) |
| **M4** | **Confirmée** | `canonical.php:319` — `} elseif ( is_author() && ! empty( $_GET['author'] )` … `:327` `$redirect_url = get_author_posts_url( $author->ID, $author->user_nicename );`. **La branche exige `is_author()` : lui retirer son objet la neutralise entièrement** |
| **M5** | **L'oracle existe** | `?author=1` → 301 `/author/admin/` · `?author=2` → 301 `/author/fabienne/` · `?author=3` → 404 18284 · `?author=999` → 404 18284. **Trois réponses discernables** |
| **M6** | **Confirmée** | `class-wp-query.php:1138` — `if ( '404' == $query_vars['error'] ) {`. La valeur `'404'` **en chaîne** est celle que le cœur porte lui-même (`:824`, `:917`, `:928`) |
| **M7** | **Confirmée, et elle TUE la garde de ceinture** | `class-wp.php:743-744` — `if ( is_404() ) { return; }`, **sortie anticipée en tête de `handle_404()`**. Un 404 déjà posé **survit** ; le statut ne peut pas revenir à 200. **La garde 6 envisagée au plan est retirée : elle n'a pas d'objet** |
| **M7b** | **Confirmée** | `class-wp.php:455-463` — `send_headers()` lit `$this->query_vars['error']`, et sur `404 === $status` pose les en-têtes anti-cache et le `Content-Type` HTML |
| **M8** | **Confirmée** | `canonical.php:960` — le devineur ne travaille que `if ( get_query_var( 'name' ) )`. Une requête d'auteur vidée **n'a pas de `name`** : le devineur ne mord pas. Deux filtres de secours existent (`do_redirect_guess_404_permalink`, `pre_redirect_guess_404_permalink`) — **inutiles ici, et à ne pas poser** |
| **M9** | **Confirmée. Le piège REST est réel** | `class-wp.php:409` — `$this->query_vars = apply_filters( 'request', $this->query_vars );` · `class-wp.php:418` — `do_action_ref_array( 'parse_request', array( &$this ) );`. **Le filtre court AVANT l'action.** `rest-api.php:436-438` — `rest_api_loaded()` lit `$GLOBALS['wp']->query_vars['rest_route']` sur cette action, **donc après nous**, et c'est **elle** qui définit `REST_REQUEST` |
| **M10** | **INFIRMÉE — l'hypothèse du lead était fausse, celle du leaddev juste** | `class-wp.php:319-336` — `foreach ( $this->public_query_vars as $wpvar )` contient `elseif ( isset( $perma_query_vars[ $wpvar ] ) ) { $this->query_vars[ $wpvar ] = $perma_query_vars[ $wpvar ]; }`. **Les variables issues d'une règle de réécriture passent bien par `public_query_vars`.** Le filtre `query_vars` couperait donc les DEUX formes — **et c'est pour un autre motif qu'il est écarté** (§4) |
| **M11** | **Le corps fuit autant que l'URL** | `/author/admin/` : `<body class="archive author author-admin author-1 …">` et un lien `…/author/admin/feed/`. Le slug est **dans le corps**, pas seulement dans l'adresse |
| **M12** | **Mesurée — voir §9** | `/wp-json/wp/v2/users` → **200, 672 o, liste les DEUX comptes** : `"slug":"admin"`, `"slug":"fabienne"`, `"name":"Fabienne Guéneau"` · `/wp-json/wp/v2/users/1` → 200, 335 o, `"slug":"admin"` · `/feed/` → 200, `<dc:creator>` · `/wp-json/oembed/1.0/embed?url=…` → 200, `"author_name":"Berger Hollandais du Mont Brabant"` (**le nom du site, pas un compte**) · `/?feed=rss2&author=1` → **404, 262 o** |
| **M13** | **Cinq règles en base** | `author/([^/]+)/?$` → `index.php?author_name=$matches[1]`, plus `feed`, `feed/<type>`, `embed`, `page/<n>`. **Toutes routent vers `author_name`** — relevé pour documenter le rejet de l'option C, **pas pour la rouvrir** |
| **M14** | **Confirmée — le thème est propre** | `grep -rn "the_author\|get_the_author\|author_link\|get_author_posts_url\|is_author"` sur `wp-content/themes/mtb/` → **0 ligne**. Les deux seuls `"author"` de blocs `wp:query` (`templates/index.html:6`, `templates/search.html:6`) sont **`"author":""`, vides, verbatim**. `templates/` ne porte **aucun** gabarit d'auteur (`404`, `archive-mtb_portee`, `search`, `singular`, `index`) |

**Un fait de plus, que la mesure a ajouté et qui rend une garde obligatoire** :
`class-wp-query.php:1833-1839` — `set_404()` **préserve délibérément `is_feed`** (`$is_feed = $this->is_feed;` … `$this->is_feed = $is_feed;`), et `template-loader.php:57` teste `is_feed()` **avant** `is_404` (`:69`). **Sans retirer la clé `feed`, `/author/admin/feed/` rendrait le flux du site entier à une adresse d'auteur, avec un statut 404 et un corps RSS** — un index servi à une URL étrangère, *le défaut même que #50 vient de réparer.* **Ce n'est pas une déduction : c'est lu.**

**Aucun autre rappel sur le filtre `request` n'existe dans ce dépôt** — vérifié par recherche sur
`wp-content/`. Aucune concurrence de priorité.

---

## 4. La forme du correctif — décidée, et les quatre écartées

**Décision : nous neutralisons la REQUÊTE d'auteur, nous ne répondons pas après coup.**

Un rappel du filtre **`request`**, **priorité 10**, retire les clés `author` et `author_name` du tableau
de variables de requête et y pose `error = '404'`.

Deux gestes, deux rôles disjoints, et c'est ce qui rend la forme lisible :

1. **Retirer les clés d'auteur** → `is_author()` ne peut plus être vrai. La branche auteur de
   `redirect_canonical` (`canonical.php:319`) **n'est pas contournée : elle est privée d'objet.**
   L'oracle meurt **à la racine**, et il meurt **avant** la priorité 10 — donc sans jamais entrer en
   concurrence avec elle.
2. **Poser `error = '404'`** → **c'est le cœur qui répond, par son propre chemin** :
   `WP::send_headers()` (`class-wp.php:455`) pose le statut et les en-têtes, `WP_Query::parse_query()`
   (`class-wp-query.php:1138`) pose le drapeau. **Nous ne posons aucun statut de notre main.**

> **L'argument décisif** : aucun rappel sur `template_redirect`, donc **aucune guerre de priorité avec
> `redirect_canonical`**, donc aucun `exit`, donc aucun statut posé par nous. Nous ne répondons pas
> *après* le cœur — **nous lui donnons une requête dont il tire lui-même la bonne réponse.**

**Corollaire opérationnel** : cette forme **n'exige aucun `wp rewrite flush`**, ne touche **aucune règle
de réécriture**, n'appelle **jamais** `flush_rewrite_rules()`, et ne laisse **aucun état en base**.
Retirer le dossier suffit à tout défaire.

### Les quatre options écartées — fermées, non rouvrables sans amendement daté

| Option | Décision | Motif |
|---|---|---|
| **A — rappel `template_redirect` 20, calqué sur #50** | **Écartée, et c'est l'option la plus évidente** | **#50 §D.1 a MESURÉ que `redirect_canonical` `exit` en priorité 10** — verbatim : *« la priorité 20 et le 404 sur la forme en requête sont mutuellement exclusifs »*. Un rappel à 20 fermerait `/author/<slug>/` et **laisserait `?author=N` entièrement ouvert, en silence** : il livrerait **la moitié la moins dangereuse en la présentant comme un tout**. Descendre sous 10 pour l'attraper est **interdit par #50 §11** et rouvrirait `redirect_guess_404_permalink()`. **Option morte, acte de décès daté** |
| **B — filtre `query_vars`** (retirer `author`/`author_name` des variables publiques) | **Écartée** | **M10 a infirmé le motif que le lead lui prêtait** : les variables de réécriture passent bien par `public_query_vars` (`class-wp.php:334`), donc l'option couperait les deux formes. **Elle est écartée pour une raison plus grave** : elle **ne pose aucun 404**. La requête devient vide → `is_home()` → **200 sur `templates/index.html`**, soit *exactement le faux 200 que #50 vient de réparer, reproduit à une autre adresse.* **Un remède qui produit le défaut voisin** |
| **C — retirer les règles de réécriture** (`author_rewrite_rules`) | **Écartée, cinq motifs tous écrits dans le dépôt** | (1) `class-loader.php:96-100`, **angle mort 1**, nomme d'avance `<nom>_rewrite_rules` comme un chemin où « **l'empreinte ne bouge pas, la modification ne prend jamais effet d'elle-même** » ; (2) le contrat #1 **interdit** à tout module d'appeler `flush_rewrite_rules()` — il n'existe donc **aucune voie légale** de la rendre effective ; (3) la **borne 2** exige qu'un module marche « à la seconde où le dossier arrive par FTP, **sans régénération de règles de réécriture** » : l'option la viole **par définition, pas par accident** ; (4) **mode de panne asymétrique** — l'effet vivrait dans l'option `rewrite_rules` **en base**, et retirer le module **ne rendrait pas** `/author/` : c'est **l'état mort à nettoyer à la main** que la garde de désarmement de #50 existe pour interdire ; (5) **elle ne fermerait même pas `?author=N`**, qui ne passe par aucune règle (M13) — *on paierait la borne 2 pour ne fermer que la moitié la moins utile* |
| **D — renommer le `user_nicename`** | **Écartée** | **Elle ne ferme pas l'oracle** : `?author=1` redirigerait vers le nouveau slug, la liste des comptes resterait lisible. C'est **une écriture en base sur un compte**, donc un geste d'exploitation qui **ne survit pas à une base neuve** et que **rien dans le dépôt ne rejouerait en production**. Son mode de panne n'est pas « elle cesse de mordre » mais **« elle ne mord jamais là où ça compte, et personne ne s'en aperçoit »**. Hors empreinte, hors issue. Si elle est voulue un jour, c'est **une consigne d'exploitation** dans le document de mise en ligne |
| **E — ne rien faire, solder T105 par écrit** | **Écartée** | Position défendable : le site n'est pas en ligne, aucune ligne de DoD ne l'exige, un identifiant de connexion n'est pas un secret. **Ce qui l'emporte** : la dette est **déjà ouverte, nommée, chiffrée et routée par #24** ; le remède tient en une vingtaine de lignes sans état ni règle ; et *« on le fera quand le site sera en ligne » est la phrase qui ne se réalise jamais* (#50, arbitrage 2) |

### Le périmètre, et pourquoi ce n'est PAS l'option C de #50 par la petite porte

**Décision : toutes les archives d'auteur de ce site, tous comptes, présents et futurs.**

L'option C écartée chez #50 portait sur une **classe d'objets que ni l'ancien site ni nous n'avions
touchés**. Ici, ce n'est pas la même chose : **l'ancien site ne publiait aucune archive d'auteur**
(`plan-du-site.php:54`), **le thème n'a aucun gabarit d'auteur** (M14), et une archive d'auteur y tombe
sur `index.html` — *l'index du blog servi à une URL d'auteur, le cousin exact du défaut de #50.*
« Les archives d'auteur de ce site, fonctionnalité du cœur que ce site n'emploie pas, décision datée du
2026-09-08 » **est** un périmètre clos et daté. **La borne 3 dit « clos et daté », pas « minuscule ».**

**Et l'inverse est le vrai piège.** Borner à `admin` ou à l'ID 1 exigerait **d'écrire une liste
d'identifiants** — exactement le mécanisme qui **diverge en silence** que #50 §5 et #52 condamnent : le
jour où un compte est créé, la liste ment. **Aucune liste d'identifiants, aucun ID en dur, aucun
`user_nicename` en dur, nulle part.** *Le rappel ne connaît aucun compte : c'est la seule forme dont la
justesse ne dépend d'aucun état de la base.*

### Le domicile — arbitré, et l'objection écrite plutôt que tue

**Le module reste `migration/indexation-heritee/` ; le fichier est neuf.**

**L'objection est réelle et elle est notée** : `/author/` **n'est pas un héritage de l'ancien site**,
c'est un défaut de WordPress nu. Un module nommé « indexation héritée » qui neutralise une fonctionnalité
du cœur que l'ancien site n'a jamais eue **frôle le motif 1 gelé en tête de son propre `bootstrap.php`**
(« un module nommé `redirections-301` qui convertirait une directive d'indexation mentirait sur son
contenu »).

**Ce qui tranche pour rester** :
1. **Le bloc d'exception motivée du 2026-09-05, en tête de `plan-du-site.php`, a déjà pris en charge
   `/author/admin/` NOMMÉMENT** — c'est littéralement la même adresse, la même fuite, le même motif
   (BRIEF §4). **#49 finit ce que cette exception a commencé** ; il n'ouvre pas un troisième sujet.
2. **Séparer les deux mécanismes créerait précisément le mode de panne de #52** : si l'un tombait, le
   plan du site republierait `/author/admin/` pendant que l'autre le ferait répondre 404. Sous un seul
   `bootstrap.php`, **les trois effets tombent et se relèvent ensemble.**
3. L'empreinte a été fixée par le lead orchestrateur, et **rien n'autorise cette chaîne à écrire hors
   d'elle**.

**Le fichier, lui, est neuf** : `archives-d-auteur.php`. Loger une neutralisation d'archives d'auteur
dans `plan-du-site.php` donnerait **un fichier qui ment sur son contenu** — le motif 1 appliqué à
l'échelle du fichier. **Module ≠ fichier** : les deux mécanismes vivent sous le même `bootstrap.php`,
donc la cohésion du point 2 est intégralement conservée.

> **Alternative écartée, remontée au lead** : ouvrir un module neuf `query/archives-auteur/`. Le nom
> cesserait de frôler le mensonge et la borne 3 n'aurait pas à plier du tout — mais on perdrait la
> cohésion du point 2, et l'empreinte ne le permet pas. **Nommée ici plutôt que tue.**

---

## 5. Les deux constantes — zéro recopie

| Constante | Valeur | Rôle |
|---|---|---|
| `CLES_D_AUTEUR` | `array( 'author', 'author_name' )` | **Détectent ET sont retirées.** **Unique écriture de ces deux noms dans tout `wp-content/plugins/`** — même discipline que `FOURNISSEURS_RETIRES`, et le contrôle **P11** le vérifie |
| `CLES_EMPORTEES` | `array( 'feed' )` | **Retirées seulement, jamais détectantes.** L'asymétrie est le fait qu'il faut voir d'un coup d'œil : `feed` seul n'est **jamais** une requête d'auteur, et une requête d'auteur n'a **jamais** de flux légitime |

Noms qualifiés : `MTB\Core\Migration\IndexationHeritee\CLES_D_AUTEUR` et `…\CLES_EMPORTEES`, domiciliées
dans `archives-d-auteur.php`, **au-dessus de leur unique lecteur**.

**Pas de filtre d'extension** (`apply_filters( 'mtb_cles_d_auteur', … )`) : ce serait faire de ce module
un module de référencement **à vocation ouverte** — **borne 3**, refusée par #50 §5 pour la même raison.
Le périmètre se modifie **en éditant la constante, dans un commit, avec son motif.**

**`FOURNISSEURS_RETIRES` ne gagne aucun nom** : la condition de renommage de
`ecarter_le_fournisseur_utilisateurs()` **n'est pas déclenchée**, et cette fonction **ne se renomme pas**
(#52 §4 et §10 la citent par son nom comme témoin d'un crochet intact).

---

## 6. Les cinq gardes — ordre imposé

`neutraliser_la_requete_d_auteur( array $variables ): array`, rappel du filtre **`request`**,
**priorité 10, un argument**. **Aucune garde ne se réordonne sans rouvrir ce contrat.**

| # | Garde | Ce qu'elle protège | **Mode de panne si on la retire** |
|---|---|---|---|
| **1** | **Sortir si `isset( $variables['rest_route'] )`** | **La garde la plus grave du fichier.** `rest_api_loaded()` lit `rest_route` sur `parse_request`, **après ce filtre** (M9, `class-wp.php:409` vs `:418`, `rest-api.php:436-438`). Et `author` **est** une variable publique : `/wp-json/wp/v2/posts?author=1` la porte | **L'éditeur de blocs par terre.** Rayon d'explosion « site entier » côté administration, pour la fermeture d'une archive publique. Doctrine reprise de `query/page-protegee/bootstrap.php:139-185` (« `is_admin()` vaut FAUX sur `/wp-json/` ») — **rien n'est réinventé** |
| **1-bis** | **Interdit, pas garde** : on **retire des clés nommées**, on ne **remplace jamais** le tableau. Le `return` porte toujours le tableau reçu, amputé | Idem garde 1, par l'autre bout : un `return array( 'error' => '404' )` détruirait `rest_route` **et** tout ce qu'un voisin y aurait mis | **Idem, et invisible en recette** si l'on ne teste que le front |
| **2** | **Sortir si aucune clé de `CLES_D_AUTEUR` n'est présente**, testée par **`array_key_exists()`** — jamais par comparaison de valeur | **La quasi-totalité du trafic, en un test**, avant tout autre travail. `array_key_exists()` est **sûr sur une valeur tableau** : `?author[]=1` est neutralisé **sans `TypeError` malgré `strict_types`, et sans notice** | Coût inutile sur chaque requête ; et une comparaison de valeur à sa place **rouvrirait la dette exacte de `plan-du-site.php:110`** — un test dont la sémantique dépend de la version de PHP |
| **3** | **Retirer les clés de `CLES_D_AUTEUR`** | `is_author()` ne peut plus être vrai : la branche auteur de `redirect_canonical` (`canonical.php:319`) est **privée d'objet**, et le SQL de la requête principale ne nomme plus jamais l'auteur | **L'oracle rouvre en entier.** Poser `error` seul laisserait `author_name` dans la requête |
| **4** | **Retirer les clés de `CLES_EMPORTEES`** (= `feed`), **et seulement quand la garde 2 a mordu** | **Le flux, et ce n'est pas une précaution : c'est mesuré.** `set_404()` **préserve `is_feed`** (`class-wp-query.php:1833-1839`) et `template-loader.php:57` teste `is_feed()` **avant** `is_404` (`:69`) | **`/author/admin/feed/` rendrait le flux du SITE ENTIER à une adresse d'auteur** — statut 404, corps RSS. *Un index servi à une URL étrangère : le défaut même que #50 vient de réparer.* Et la cible du §7 serait manquée. Second effet : `is_feed()` resté vrai ferait courir les exclusions de recherche de `mise-en-sommeil` et `page-protegee` **pour rien**, à chaque requête d'auteur |
| **5** | **Poser `$variables['error'] = '404';`** — **chaîne, jamais entier** | **Le 404 rendu par le cœur et non par nous.** `'404'` est **la valeur exacte que le cœur porte lui-même** (`class-wp-query.php:824`, `:917`, `:928`) : on n'invente pas une valeur, **on emprunte la sienne** | **Le faux 200 de #50, à une autre adresse** : requête vide → `is_home()` → `index.html` en 200. **Le remède deviendrait le défaut voisin** |

**Pourquoi cet ordre et pas un autre.** REST **avant tout**, parce que c'est la seule garde dont l'oubli
casse **autre chose que cette issue**. Détection **avant** modification, pour que le coût sur les
requêtes ordinaires soit **un `array_key_exists()` et rien de plus**. Retraits **avant** la pose de
`error`, pour que l'état intermédiaire « requête vidée sans 404 » — le faux 200 — **n'existe à aucun
instant du filtre**.

### La garde de ceinture envisagée est RETIRÉE, et c'est la mesure qui l'a tuée

Le plan prévoyait une sixième garde conditionnelle — un `template_redirect` 20 reposant le 404 au cas où
`WP::handle_404()` remettrait 200. **M7 l'a rendue sans objet** : `class-wp.php:743-744` porte
`if ( is_404() ) { return; }` **en tête de la fonction**. Un 404 déjà posé survit.

> **Elle n'est donc pas écrite. Une ceinture posée « au cas où » contre un cas mesuré comme impossible
> serait une garde qui rassure sans couvrir — famille T92.** L'énumération des hooks de front reste à
> **cinq**, pas six.

### La garde de contexte des modules voisins n'est PAS recopiée — écart délibéré, motivé

Les deux services de front voisins portent
`is_admin() || wp_doing_ajax() || wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST )`.
**Ici, la recopier serait une garde qui rassure sans couvrir** : `parse_request()` ne court ni dans
`wp-admin`, ni sur `admin-ajax.php`, ni sur `wp-cron.php` — et surtout **`REST_REQUEST` n'est PAS ENCORE
DÉFINI** quand ce filtre court, puisque c'est `rest_api_loaded()` qui le définit, **après** (M9).

> **La recopier donnerait l'illusion de la protection REST tout en ne protégeant rien.** C'est
> exactement pour cela que la garde 1 teste `rest_route`. **`defined( 'REST_REQUEST' )` est INTERDIT
> dans ce fichier, et le commentaire doit dire pourquoi.**

---

## 7. La cible chiffrée — la tâche 3 rendue mesurable

> **Les treize adresses ci-dessous rendent toutes le même triplet — `404 · text/html · <T>` — avec un
> `redirect_url` VIDE, et un corps dont le `md5sum` est identique à celui de
> `/nexiste-pas-du-tout/` :**
>
> `/author/admin/` · `/author/fabienne/` · `/author/inexistant/` · `/?author=1` · `/?author=2` ·
> `/?author=3` · `/?author=999` · `/?author_name=admin` · `/?author_name=fabienne` ·
> `/author/admin/feed/` · `/author/fabienne/feed/` · `/author/admin/page/2/` · `/?feed=rss2&author=1`
>
> **L'INDISCERNABILITÉ est la propriété livrée ; le code 404 n'en est que la forme.**

**Trois précisions qui font la différence entre une cible et un vœu :**

1. **`<T>` ne se gèle pas à `18284` par recopie.** C'est la taille de `/nexiste-pas-du-tout/` **relevée
   dans le MÊME relevé**, et l'égalité se prouve **par comparaison, jamais par constante** — discipline
   de P2 chez #50. *(Au commit `2574bf9` elle vaut 18 284 et le `md5sum` vaut
   `d9f9bae7edf3ba3b6787b341d41688cd` ; ces chiffres se rejouent, ils ne se recopient pas.)*
2. **Le `md5sum` du corps, pas seulement le `Content-Length`.** *Deux réponses de même taille peuvent
   différer d'un mot* — et le mot en question serait un slug.
3. **`/author/inexistant/` est dans la cible, et ce n'est pas du zèle** : s'il ne rendait pas le même
   triplet que `/author/admin/`, **l'oracle survivrait sous une autre forme** — « ce slug existe, celui-là
   non ».

**Le flux change de type** : `application/rss+xml 1704` → `text/html <T>`. **C'est exigé, pas supposé** ;
si la mesure le refuse, **c'est une branche d'arrêt et une remontée au lead**, pas un arrondi.

---

## 8. États spéciaux et cas limites — comportement imposé

| État / cas | Détecté par | Comportement imposé |
|---|---|---|
| `requete_d_auteur_neutralisee` | Une clé de `CLES_D_AUTEUR` présente | Clés retirées, `feed` emporté, `error = '404'`. **Le cœur pose le statut, nous jamais.** Corps : `404.html`, **le même que tout autre 404 du site** |
| `requete_ordinaire` | Aucune clé d'auteur | **Rien.** Le tableau est rendu **identique** |
| `requete_rest` | `rest_route` présent | **Rien, sortie immédiate** — même sur `/wp-json/wp/v2/posts?author=1` |
| `?author=`, `?author=0`, `?author=abc` | Clé présente, valeur vide, nulle ou non numérique | **404. Choix assumé** : la règle est **la présence de la clé, sans arithmétique**. Elle sur-couvre légèrement le cœur (`?author=0` sert aujourd'hui l'accueil) et **c'est le bon côté de l'erreur** — une règle sans comparaison de valeur **ne peut pas dériver avec une version de PHP**. *Recopier `0 != $qv['author']` rouvrirait la dette de `plan-du-site.php:110` en miniature.* **Arbitré : on ne recopie pas la sémantique du cœur** |
| `?author[]=1`, `?author[]=1&author[]=2` | `array_key_exists()` sur une valeur tableau | **404, et zéro notice PHP.** Aucune valeur n'est lue, donc aucun `TypeError` possible sous `strict_types`. **`debug.log` à 0 octet est le contrôle** |
| `/author/<slug>/feed/`, `/feed/atom/` | `author_name` + `feed` | **404 · text/html**, corps `404.html`. **Le type change** — garde 4 |
| `/author/admin/page/2/` | `author_name` + `paged` | **404.** `paged` **n'est PAS retiré** : `set_404()` remet `is_paged` à faux, donc `get_body_class()` ne pose ni `paged` ni `paged-2`. **Si la mesure montre un corps différent d'un octet, la valeur exacte s'écrit et `paged` rejoint `CLES_EMPORTEES` avec son motif** |
| `/AUTHOR/admin/`, `?AUTHOR=1` | — | **Inchangé.** La règle de réécriture et la collecte des variables publiques sont **sensibles à la casse** : répondre 404 pour un nom **que le cœur n'a jamais routé**, c'est *l'option C par la petite porte* — **arbitrage 12 de #50, reconduit sans discussion** |
| `POST`, `HEAD` | — | **404. Aucune garde de méthode**, écart délibéré au module `redirections-301` et **aligné sur #50 arbitrage 11** : une 301 sur un POST perd le corps, **un 404 ne perd rien** |
| `/?s=chien&author=2` | `author` présent | **404.** C'est bien une requête d'auteur, et **une recherche restreinte à un auteur EST un oracle** |
| `compte_sans_contenu_publie` | — | **Indiscernable des autres.** *C'est la propriété* : ce rappel ne lit **jamais** la base des utilisateurs, donc il ne peut distinguer ni un compte peuplé d'un compte vide, ni un compte existant d'un compte inventé |
| `extension_desactivee` / dossier renommé `_indexation-heritee` | — | **Retour intégral au comportement d'aujourd'hui** : `/author/fabienne/` à 200, `?author=N` à 301, **et** `users` de retour au plan du site, **et** le 404 de #50 tombé. **Les trois effets tombent ensemble** — dégradation cohérente, **aucun état mort à nettoyer** |
| `plan_du_site_desactive` (`blog_public = 0`) | — | **Sans effet sur ce rappel** : il ne lit ni option, ni registre, ni base |

Les quatre états gelés au §9 du contrat #1 — `aucune_portee`, `donnee_absente`, `parent_hors_elevage`,
`page_protegee` — **ne sont ni touchés, ni étendus, ni réinterprétés.**

### Le mode de panne silencieux, écrit sans le maquiller

> **Si le rappel cesse de mordre, RIEN ne le dit. Rien.**

| Cause | Effet | Ce qui le dirait |
|---|---|---|
| La ligne `add_filter` disparaît dans une reprise | `/author/fabienne/` → 200, **l'oracle rouvre en entier** | **Rien** |
| `CLES_D_AUTEUR` vidée, ou dossier renommé `_indexation-heritee` | Idem, **et** `users` revient au plan du site, **et** le 404 de #50 tombe | **P1 le dirait, si on le joue** |
| Le cœur renomme `author_name` | Le rappel ne mord plus sur la forme jolie | **Rien** |
| Le cœur cesse d'honorer `error` | **Faux 200 sur `index.html`** — le défaut de #50 à une autre adresse | **Rien** |
| Un tiers filtre `request` après nous et remet `author` | Idem | **Rien** |

**Ce module n'a pas de commande WP-CLI et il n'en aura pas** — motif 3 gelé en tête de son
`bootstrap.php` (« témoins d'échec disjoints »). **Le seul témoin est le protocole du §11, joué en
recette.**

> **Une nuance qui n'existait pas chez #50 et qui doit être dite** : #50 protégeait **une adresse machine
> que personne ne demande**. **#49 protège l'identifiant de connexion de l'éleveuse.** Le mode de panne
> est le même — muet — **mais l'enjeu ne l'est pas.** Aucune sonde supplémentaire n'est proposée pour
> autant : la seule qui aurait du sens serait une commande WP-CLI, et le motif 3 l'interdit. **Résidu
> nommé, non masqué, et son poids réel écrit plutôt que lissé.**

---

## 9. Résidus déclarés et NON fermés — mesurés, nommés, laissés ouverts

**Sans ce paragraphe, un rapport dirait « T105 close » quand elle ne le serait qu'en partie.** Chacun a
été **mesuré** (M12) ; aucun n'est fermé ici.

| # | Surface | Ce qui fuit, **mesuré** | Pourquoi hors périmètre |
|---|---|---|---|
| **R1** | `/wp-json/wp/v2/users` | **200, 672 o — liste les DEUX comptes** : `"slug":"admin"`, `"slug":"fabienne"`, `"name":"Fabienne Guéneau"`, et le `link` vers `/author/<slug>/`. **C'est l'oracle le plus riche du site, et il survit intact à #49** | Le fermer touche la REST, donc **l'éditeur de blocs et le sélecteur d'auteur**. **Autre issue, autre mesure, autre contrat** |
| **R2** | `/wp-json/wp/v2/users/1` | **200, 335 o, `"slug":"admin"`.** Le même oracle par ID | Idem R1 |
| **R3** | `/wp-json/oembed/1.0/embed?url=…` | `"author_name":"Berger Hollandais du Mont Brabant"` — **le nom du SITE, pas un compte.** *Moins grave que le plan ne le craignait, et c'est écrit* | Idem R1 |
| **R4** | `/feed/` | `<dc:creator>` = nom public de l'auteur de chaque entrée | Retirer `dc:creator` change **le contenu des flux du site**, pas une archive d'auteur |
| **R5** | `/?feed=rss2&author=1` | **Déjà 404 (262 o) AVANT #49**, pour une autre cause. **Sera 404 par notre rappel après** — même code, cause différente. *À consigner comme #50 a consigné `/wp-sitemap-users-2.xml`* | Sans objet |
| **R6** | `wp-login.php` | Message **distinct** entre identifiant connu et inconnu → **oracle d'identifiant de connexion**, indépendant des archives. **Non mesuré** — hors périmètre et hors empreinte | Ni `seo`, ni ce module. **Issue `infra`/`prive` à ouvrir** — et c'est le résidu **le plus proche de l'enjeu réel** |

> **T105 ne se déclare close QUE sur son énoncé** — « `/author/…` et `?author=…` ». **R1, R2, R4 et R6
> restent ouverts et doivent être nommés en regard dans tout rapport.** *Une dette soldée à moitié et
> rapportée comme entière est la faute que #24 §16 institue comme interdite.*

---

## 10. Frontière, budget, et ce que ce contrat gèle à la place du thème

**Il n'y a pas de côté thème, et ce contrat le dit plutôt que de laisser un gabarit vide.**

- **Fonctions de lecture exposées au thème : AUCUNE.** Ce module n'ajoute ni fonction globale `mtb_*`, ni
  variable de requête, ni règle de réécriture, ni option, ni méta, ni terme, ni type de contenu.
- **Blocs enregistrés : AUCUN.** Section **sans objet**, et elle le dit — précédent `issue-50.md` §9.
- **Chaînes fournies par le serveur : AUCUNE.** Aucun libellé, aucune date, aucun nom de discipline.
- **Hooks offerts au thème : AUCUN** — pas de `apply_filters( 'mtb_cles_d_auteur', … )`, **borne 3**.
- **Le thème n'est pas touché, ne connaît pas ce module, ne l'appelle pas, ne teste pas son existence.**
  La frontière du §8 du contrat #1 est intacte.

**Ce que le thème ne doit JAMAIS faire** : appeler `the_author()`, `get_the_author()`,
`get_author_posts_url()`, `is_author()` ni aucune de leurs sœurs · poser un `"author"` non vide dans un
bloc `wp:query` · déclarer un gabarit `author.html` ou `archive-author.html` · **et surtout : masquer un
lien d'auteur par du CSS**. Si un lien d'auteur apparaissait un jour dans le thème, la réponse est
**arrêt, relevé du fichier et de la ligne, remontée au lead** — **jamais** un contournement côté
extension.

### La frontière avec le CŒUR, gelée — chaque point est un relevé du §3, pas une croyance

1. `apply_filters( 'request', … )` court dans `WP::parse_request()` **avant**
   `do_action_ref_array( 'parse_request', … )` — `class-wp.php:409` vs `:418`.
2. `rest_api_loaded()` lit `rest_route` sur `parse_request` et **définit `REST_REQUEST` à ce
   moment-là, donc APRÈS notre filtre** — `rest-api.php:436-438`. **Corollaire gelé : la garde REST de
   ce module est `isset( $variables['rest_route'] )`, et `defined( 'REST_REQUEST' )` y est INTERDIT
   comme trompeur.**
3. `WP::send_headers()` lit `query_vars['error']` et pose lui-même le statut — `class-wp.php:455-463`.
4. `WP_Query::parse_query()` appelle `set_404()` sur `'404' == $qv['error']` —
   `class-wp-query.php:1138`.
5. `WP::handle_404()` **sort tôt** sur un `is_404()` déjà posé — `class-wp.php:743-744`. **C'est ce qui
   rend la garde de ceinture sans objet.**
6. `set_404()` **préserve `is_feed`** — `class-wp-query.php:1833-1839` — et `template-loader.php:57`
   teste `is_feed()` **avant** `is_404` (`:69`). **C'est ce qui rend la garde 4 obligatoire.**
7. `redirect_guess_404_permalink()` ne travaille que `if ( get_query_var( 'name' ) )` —
   `canonical.php:960`. **Contrôle à double sens : `redirect_url` attendu VIDE.**
8. La branche auteur de `redirect_canonical` exige `is_author()` — `canonical.php:319`.

> **Les numéros de ligne ci-dessus sont épinglés à WordPress 6.9.** `wp-includes/` n'étant pas versionné,
> ils **se périmeront en silence** à la prochaine montée de version. **Résidu nommé, non masqué** — même
> famille que #50 §F.4.

### Budget

- **D8 — poids ajouté sur les pages servies aux visiteurs : zéro octet.** Aucun CSS, aucun JS, aucune
  police, aucune image, aucune mise en file. **Une nuance chiffrée plutôt que tue** : les archives
  d'auteur **grossissent** (16 379 et 14 457 → ~18 284) et le flux d'auteur passe de 1 704 à ~18 284 —
  **sur des adresses qu'aucun visiteur ne demande.** Le budget n'est pas en cause ; **le chiffre se dit
  quand même.**
- **D6 — zéro requête sortante.** Aucun `wp_remote_*`, zéro cookie, zéro traceur.
- **D10** — aucune extension tierce, aucun page builder.
- **D12** — le correctif **ne peut pas casser une page** : sortie anticipée sur la quasi-totalité du
  trafic, aucun rendu, aucun `exit`, aucune valeur lue.

### Sécurité et conventions

**Aucun chemin d'écriture, donc aucun nonce et aucune vérification de capacité ne sont dus** — il n'y a
rien à autoriser. **Aucune valeur n'est lue** : seules des **présences de clés** sont testées et des clés
retirées. *Il n'y a donc rien à assainir, et c'est plus sûr que d'assainir.* Aucune sortie, donc aucun
échappement. Aucun `exit`.

`declare(strict_types=1);` · garde `if ( ! defined( 'ABSPATH' ) ) { exit; }` · namespace
`MTB\Core\Migration\IndexationHeritee` · **français littéral, aucune fonction i18n** (`issue-1.md` §7 —
jamais `__()`, `_e()`, `esc_html__()`) · `array()` et jamais `[]` · conditions de Yoda · tabulations ·
pas de `?>` final · plafond de syntaxe **PHP 8.1** · WordPress Coding Standards. **À l'inclusion de
`bootstrap.php`** (`issue-1.md` §3) : **seuls** `add_action`, `add_filter`, `define`, `require_once` de
ses propres fichiers, déclarations et gardes de sortie anticipée.

---

## 11. Protocole de vérification — rejouable, joué DEUX fois

Depuis le conteneur, `curl --path-as-is --connect-to "localhost:3005:localhost:80"`, **sans cookie ni
`--user`**, **jamais depuis Git Bash de Windows** (transcodage UTF-8 → Latin-1, piège #24 §16 point 6).
Pour WP-CLI sous Git Bash : **`MSYS_NO_PATHCONV=1`**. Pile **déjà démarrée** : **jamais** `down`,
`--build`, `--force-recreate`, `wp db reset`, ni re-seed. **`wp rewrite flush` n'est pas requis et ne
doit pas être joué** — le correctif ne touche aucune règle. **Le même script sert avant et après ; un
écart non attribuable est un échec.**

**Datation** : date · **sha du commit mesuré** · version de WordPress · hôte · `permalink_structure` ·
`blog_public` (décision 68).

| # | Contrôle | Attendu |
|---|---|---|
| **P0** | `php -l` sur les **trois** fichiers | **Avant tout `curl`.** Une erreur de syntaxe est un `E_COMPILE_ERROR` **non rattrapé par le `try/catch` du chargeur** (#1 §12) — site entier par terre |
| **P1** | **Sonde d'existence du module, en PREMIER** : les `<loc>` de `/wp-sitemap.xml` | **5 `<loc>`, aucun `users`.** *Si `users` réapparaît, le module n'est plus chargé et tout le reste se lirait à contresens* |
| **P2** | **L'objet de l'issue** : les **13 adresses du §7** | Avant : le tableau du §2.1. Après : **toutes `404 · text/html · <T>`, `redirect_url` VIDE, `md5sum` du corps identique à celui de `/nexiste-pas-du-tout/`** |
| **P3** | **#50 intact** : `/wp-sitemap-users-1.xml` | **`404 text/html <T>`**, `redirect_url` vide |
| **P4** | Les **6 documents** du plan du site — liste **extraite de l'index, jamais codée en dur** | Statut, type, **taille identique à l'octet**, nombre de `<loc>` identique, **aucun `/author/`** |
| **P5** | Les **2 feuilles `.xsl`** | `200 application/xml`, **identiques à l'octet** |
| **P6** | `wp mtb verifier-redirections` | **code 0** |
| **P7** | `/` `25491` · `/portees/` `31819` · `/chien/jango/` `28084` · `/contact/` `19409` · `/travail/` `35411` | **200, tailles identiques à l'octet.** *Notre filtre court sur chacune de ces requêtes* — **c'est le contrôle qui prouve que la garde 2 sort bien** |
| **P8** | `/bhpl/port%C3%A9e-m-2016/` **et sa forme UTF-8 brute** | **301** vers `/portees/m-2016/` |
| **P9** | `/chien/halan/` → `<meta name='robots'>` | **inchangée** (#52) |
| **P10** | **`debug.log`** | **0 octet avant, 0 octet après.** *Ligne de base du lot : tout octet écrit est un échec à signaler* |
| **P11** | `grep -rn "'author'\|'author_name'" wp-content/plugins/` | **Exactement deux occurrences, toutes deux dans `CLES_D_AUTEUR`.** *L'anti-recopie de #50 appliquée aux clés d'auteur* |
| **P12** | `grep -rn "'users'" wp-content/` | **Exactement une ligne** — contrôle **P7 de #50 reconduit** |
| **P13** | **Tâche 2** : les deux greps de M14 sur le thème, **plus** `curl \| grep -c '/author/'` → **0** sur `/`, `/portees/`, `/chien/jango/`, `/contact/`, `/travail/` | *Le cœur pose des liens d'auteur que le thème n'écrit pas : **seule la mesure sur le RENDU le dirait**.* **Si un gabarit portait un lien auteur : ARRÊT, relevé, remontée. Jamais un contournement côté extension** |
| **P14** | **L'éditeur de blocs, à la main, connecté** : ouvrir une portée, ouvrir le sélecteur de lien, enregistrer | **Fonctionne.** *La seule sonde du piège REST, et elle n'est pas automatisable ici* |

### Ordre imposé qui rend les mesures attribuables

1. **`php -l`** sur les trois fichiers, **avant tout `curl`**.
2. **Relevé AVANT complet** — P1 à P13.
3. **Écrire `archives-d-auteur.php` en entier, SANS l'accrocher** (ni `require_once`, ni `add_filter`) →
   `php -l` → **rejouer : rien ne doit avoir bougé d'un octet.** *Preuve qu'un fichier non chargé ne fait
   rien.*
4. **Ajouter le `require_once` seul**, toujours sans `add_filter` → `php -l` → **rejouer : rien n'a
   bougé.** *Preuve qu'une fonction déclarée et non accrochée ne fait rien, et que le fichier s'inclut
   sans effet de bord.*
5. **Accrocher** → `php -l` → **relevé APRÈS complet.** *L'accroche seule est causale.*
6. Les corrections de prose (§13) dans une **étape séparée**, suivies d'un `php -l` et d'un **rejeu de
   P4/P5** : *zéro ligne exécutable se prouve, il ne s'affirme pas.*

---

## 12. Ligne d'inventaire du §11 d'`issue-1.md` — prête à coller, appliquée par le lead

**Cette chaîne ne l'écrit pas.** Le §11 est partagé par les trois chaînes du lot ; la **décision 70** en
confie la tenue au lead. La ligne `migration/ | indexation-heritee` est **doublement périmée** : #50 ne
l'a jamais complétée. **Deux ajouts en fin de cellule, sans rien réécrire.**

> **Colonne « rôle », à ajouter en fin de cellule** : « **Depuis le 2026-09-08 (#50)**, répond lui-même
> le **404 franc** que le cœur ne pose pas pour un fournisseur de plan du site retiré —
> `render_sitemaps()` sort par un `return` nu quand le fournisseur n'est pas au registre, laissant un
> faux 200 en HTML (dette T106) ; la constante `FOURNISSEURS_RETIRES` de `plan-du-site.php` est
> **l'unique écriture** du nom d'un fournisseur retiré et est lue par **les deux** rappels, si bien
> qu'il ne peut exister ni retrait sans 404, ni 404 sans retrait. **Depuis le 2026-09-08 (#49)**,
> **neutralise la requête d'auteur** dans `archives-d-auteur.php` : les clés `author` et `author_name`
> sont **retirées de la requête** — jamais une réponse posée après coup — et `error` est mis à `'404'`,
> si bien que `/author/<slug>/`, `/author/<slug>/feed/`, `?author=N` et `?author_name=<slug>` rendent
> **tous le même 404 que n'importe quelle adresse inexistante du site, sans `Location` distinct** :
> l'oracle d'énumération des comptes est fermé. **Aucun identifiant, aucun ID, aucun `user_nicename`
> n'est écrit nulle part** — le rappel ne lit **aucun compte**, donc il vaut pour les comptes présents
> **et futurs**. Périmètre clos et daté : les archives d'auteur de ce site, fonctionnalité du cœur que
> ce site n'emploie pas (l'ancien site n'en publiait aucune, le thème n'a aucun gabarit d'auteur).
> **Aucune règle de réécriture n'est touchée, aucun `flush` n'est requis, aucun état n'est laissé en
> base** (dette T105). »
>
> **Colonne « hooks », à ajouter à l'énumération** : « **`template_redirect` 20** (#50, priorité choisie
> pour passer après `render_sitemaps()` **et** après `redirect_canonical`, dont le devineur de 404
> mordrait sinon) · **`request` 10** (#49, choisi **contre** `template_redirect` 20 : #50 §D.1 a
> **mesuré** que `redirect_canonical` `exit` en priorité 10, donc un rappel à 20 **ne mordrait jamais**
> sur `?author=N` et laisserait l'oracle ouvert en silence. Le filtre `request` court **avant** l'action
> `parse_request`, donc avant `rest_api_loaded()` et avant que `REST_REQUEST` ne soit défini : la garde
> REST y est `isset( $variables['rest_route'] )`, jamais `defined( 'REST_REQUEST' )`, et **le tableau
> n'est jamais remplacé, seules des clés nommées sont retirées**). »
>
> **Renvois, à ajouter** : « relève de l'énumération des hooks de front dans
> `docs/contracts/issue-50.md` §7, **portée à cinq par `docs/contracts/issue-49.md` §13** ; **relève de
> la borne 1 dans `docs/contracts/issue-49.md` §13**. »

**Le compte de modules du §11 est inchangé : #49 ne crée aucun module.**

---

## 13. Relèves des bornes et de l'énumération — 2026-09-08

Pratique en vigueur, établie par `query/page-protegee` (amendement déclaré à `issue-23.md` §2.3) et
reconduite par #52 §11.2 et #50 §7 : **l'amendement vit dans le contrat de son issue, et la ligne du §11
y renvoie. Les contrats gelés ne se rouvrent pas.**

### 13.1 Relève de la BORNE 1 — la modification de la requête en mémoire

**C'est l'amendement que #49 ne peut pas éviter, et il faut le dire au lieu de le glisser.** La borne 1
se lisait « lecture seule » ; #50 l'a resserrée en « **il lit, il RÉPOND** : poser un 404 est répondre,
pas écrire ». **#49 va un cran plus loin : il MODIFIE LA REQUÊTE.**

> Le rappel `request` de `archives-d-auteur.php` **retire des clés du tableau de variables de requête et
> y pose `error`**. Ce n'est ni une lecture ni une réponse : c'est **une modification de l'objet de la
> requête, en mémoire, pour le seul processus en cours**. **Rien n'est persisté** : aucun
> `update_option`, aucun `wp_insert_post`, aucun `update_post_meta`, aucun `wp_set_object_terms`, aucune
> règle de réécriture, **aucune écriture en base d'aucune sorte**.
>
> **La borne 1 est étendue, explicitement et par écrit, à : « il lit, il RÉPOND, et il peut AMENDER LA
> REQUÊTE EN MÉMOIRE — jamais l'état persistant. »** *L'étendre en silence aurait été la faute que la
> borne existe pour empêcher.*

**Bornes 2 et 3 intactes.** Borne 2 : le rappel ne dépend d'**aucun état en base** pour se déclencher —
il lit deux constantes de son propre fichier et **fonctionne à la seconde où le dossier arrive par FTP,
sans réglage, sans visite de `wp-admin`, sans régénération de règles de réécriture**. Borne 3 : le
périmètre est **clos et daté** (§4), et **la prochaine demande de ce genre exige son propre amendement
écrit et daté**.

### 13.2 Relève de l'énumération des hooks de front du groupe `migration/` — de quatre à CINQ

| Module | Hook | Priorité | Rappel |
|---|---|---|---|
| `redirections-301/` | `template_redirect` | 1 | `rediriger` |
| `redirections-301/` | `the_content` | 20 | réparation des douze ancres internes |
| `indexation-heritee/` | `wp_sitemaps_add_provider` | 10 | `ecarter_le_fournisseur_utilisateurs` |
| `indexation-heritee/` | `template_redirect` | 20 | `repondre_404_au_sous_plan_retire` |
| `indexation-heritee/` | **`request`** | **10** | **`neutraliser_la_requete_d_auteur`** |

**Cinq, et pas six** : la garde de ceinture envisagée au plan est **retirée**, M7 l'ayant rendue sans
objet (§6). *« Un amendement qui sous-déclare sa propre portée est exactement ce qu'il est censé
empêcher »* (#24 §15) — **et un qui la sur-déclare ment aussi.**

**Cette relève n'ouvre rien** : aucun groupe nouveau, aucun filtre sur `GROUPES`, ni `mtb-core.php` ni
`class-loader.php` touchés.

---

## 14. Ce qui n'est PAS mesuré — aucune de ces lignes n'est présentée ailleurs comme une mesure

1. **Le comportement d'un compte à zéro contenu publié** (M2) : aucun n'existe aujourd'hui, et en créer
   un serait **une écriture en base, hors empreinte**. La propriété est établie **par construction** —
   le rappel ne lit aucun compte — **pas par mesure**.
2. **`wp-login.php`** (R6) : non mesuré, hors périmètre et hors empreinte.
3. **La création d'un troisième compte** pour éprouver « présents et futurs » : écriture en base, hors
   empreinte. **Par construction, pas par mesure.**
4. **Le comportement d'un moteur de recherche réel** face à une archive d'auteur passée de 200 à 404 ·
   **l'hôte de production et son serveur frontal** · **le comportement d'une version future du cœur** sur
   `error`, sur `set_404()` et sur la préservation de `is_feed`. **Faits d'exploitation, pas faits de
   code.**
5. **Les numéros de ligne du cœur** cités au §3 et au §10 sont **épinglés à WordPress 6.9** et **se
   périmeront en silence** à la prochaine montée de version.

---

## 15. Interdits

- **Ne jamais recopier `'author'` ni `'author_name'`** hors de `CLES_D_AUTEUR` — contrôle **P11**.
- **Ne jamais recopier le littéral `'users'`** hors de `FOURNISSEURS_RETIRES` — contrôle **P12**,
  reconduit de #50.
- **Ne jamais REMPLACER le tableau** du filtre `request` : on retire des clés nommées, rien d'autre.
  Un `return array( … )` détruirait `rest_route` — **l'éditeur de blocs par terre**.
- **Ne jamais employer `defined( 'REST_REQUEST' )` dans ce fichier** : il n'est pas encore défini à cet
  instant (M9). **Il rassurerait sans couvrir.**
- **Ne jamais descendre la neutralisation sur `template_redirect`** — #50 §D.1 en a **mesuré** la mort.
- **Ne jamais toucher aux règles de réécriture**, ne jamais appeler `flush_rewrite_rules()`, ne jamais
  exiger un `wp rewrite flush` — option C, cinq motifs au §4.
- **Ne jamais renommer un `user_nicename`** — option D.
- **Ne jamais écrire une liste d'identifiants, d'ID ou de `user_nicename`** : le rappel ne connaît
  aucun compte, et c'est la propriété.
- **Ne jamais `exit`** dans ce rappel.
- **Ne jamais retirer un rappel de `wp_robots` « en croyant dédoublonner »** — interdit gelé par #23 et
  #24, reconduit par #52, #50, et **reconduit ici**.
- **Ne jamais renommer `ecarter_le_fournisseur_utilisateurs()`** — #52 §4 et §10 la citent par son nom.
- **Ne jamais amputer le bloc d'exception motivée du 2026-09-05** dans `plan-du-site.php` : c'est un
  acquis contractuel. **Il s'augmente d'un acte daté ; il ne se retranche pas.**
- **Ne jamais toucher `migration/redirections-301/**`** — chaîne #53, committée.
- **Ne jamais écrire dans `docs/contracts/issue-1.md`** : le texte est livré prêt à coller au §12, **le
  lead l'applique**.
- **Ne jamais masquer un lien d'auteur du thème par un contournement côté extension** — arrêt, relevé,
  remontée.
- **Aucun fait de domaine n'est en jeu, et aucun ne doit apparaître.** Aucun nom de chien, aucune date,
  aucune généalogie, aucun numéro LOF, aucun résultat.

---

## 16. Les corrections de prose dues — trois blocs rendus faux, deux dettes héritées

### 16.1 `plan-du-site.php:110` — dette héritée 1 : le motif du `true`, **jamais le `true`**

Le `true` d'`in_array()` est **juste** et **reste**. C'est le *pourquoi* qui est périmé : il invoque un
danger propre à PHP 7 sur un projet dont le plancher est **PHP 8.1**. Le motif à écrire est **la règle
qui vaut à toutes les versions** : ce test porte sur des **noms**, et un nom se compare à l'identique.

### 16.2 `plan-du-site.php:119` — dette héritée 2 : le décompte

L'en-tête annonce `QUATRE FAITS DU CŒUR` au-dessus d'un bloc qui en porte **cinq**. **`CINQ`.** Le
cinquième est un relevé comme les autres : **« AUCUN N'EST DÉDUIT » reste vrai**, seul le titre mentait.

### 16.3 `plan-du-site.php` — le TROISIÈME acte daté du bloc d'exception

À **ajouter en fin de bloc**, jamais en amputation. Il doit dire : le 2026-09-05 a **retiré**
`/author/admin/` du plan du site ; #50 a fait **répondre** 404 au sous-plan ainsi vidé ; **ni l'un ni
l'autre n'a fermé l'adresse elle-même**, qui répondait encore 200 (T105). **#49 la ferme, pour tous les
comptes** — *il y en avait **deux**, et le second est celui de l'éleveuse ; un correctif borné à `admin`
aurait laissé **son** identifiant publié.* Le mécanisme vit dans `archives-d-auteur.php`, **dans le même
module**, donc il tombe et se relève avec les deux autres — *ce qui est exactement voulu (leçon de #52)*.
**Borne 3 tenue**, avec son motif : l'ancien site ne publiait aucune archive d'auteur, le thème n'a aucun
gabarit d'auteur, une archive d'auteur y tombe sur l'index du blog.

### 16.4 `bootstrap.php` motif 2 — **il devient faux, et c'est un piège matériel**

Le texte actuel (l. 27-32) dit que renommer le dossier en `_indexation-heritee` rendrait au plan du site
le fournisseur écarté « **et ferait tomber avec lui le 404** … **les deux effets tombent ensemble** ».
**Après #49 il y en a TROIS, et le comptage explicite « les deux » devient faux.** Le texte corrigé doit
nommer le troisième : **toutes les archives d'auteur du site rouvriraient**, `/author/<slug>/` à 200 et
`?author=N` à 301, **donc l'énumération des comptes — celui de l'éleveuse compris — redeviendrait
lisible**. *Un piège matériel pour qui appliquerait ce renommage de bonne foi.*

### 16.5 `bootstrap.php` — une TROISIÈME surface silencieuse

À ajouter après le paragraphe existant : si le rappel de `request` cesse de mordre, `/author/<slug>/`
revient à 200 et `?author=N` à 301, et **rien ne le dirait**. **Ce que #50 protégeait était une adresse
machine que personne ne demande ; ce que celui-ci protège est l'identifiant de connexion de
l'éleveuse.** Le témoin est de même nature — un code de statut joué en recette, protocole du §11 — et
**lui donner une commande WP-CLI contredirait le motif 3**.

---

## 17. Arbitrages — chaque désaccord, la décision, sa raison

| # | Désaccord | Décision | Raison |
|---|---|---|---|
| 1 | Le périmètre de l'issue : les deux adresses de l'énoncé, ou toutes les archives d'auteur ? | **Toutes** | **La mesure a trouvé deux comptes, le second étant celui de l'éleveuse.** S'en tenir à la lettre de l'énoncé aurait laissé **son** identifiant publié — *un défaut plus grave que celui qu'on répare*. Et borner exigerait une **liste d'identifiants**, le mécanisme qui diverge en silence (#50 §5, #52) |
| 2 | Est-ce l'option C de #50 par la petite porte ? | **Non** | L'option C portait sur **une classe d'objets que personne n'avait touchée**. Ici : l'ancien site ne publiait aucune archive d'auteur, le thème n'a aucun gabarit d'auteur, l'archive tombe sur l'index du blog. **La borne 3 dit « clos et daté », pas « minuscule »** |
| 3 | `template_redirect` 20 (comme #50) ou le filtre `request` ? | **`request` 10** | **#50 §D.1 a MESURÉ que `redirect_canonical` `exit` en priorité 10.** Un rappel à 20 laisserait `?author=N` — **l'oracle, la forme la plus dangereuse** — entièrement ouvert, **en silence** |
| 4 | Répondre après coup, ou neutraliser la requête ? | **Neutraliser** | On ne contourne pas la branche auteur de `redirect_canonical` : **on la prive de son objet**. Aucun statut posé de notre main, aucune guerre de priorité, aucun `exit` |
| 5 | Filtre `query_vars` ? | **Non** | **M10 a infirmé le motif que je lui prêtais** (les variables de réécriture passent bien par `public_query_vars`) — **et l'option reste écartée pour pire** : elle ne pose aucun 404, donc **200 sur `index.html`**, *le faux 200 de #50 reproduit à une autre adresse* |
| 6 | Retirer les règles de réécriture (option C) ? | **Non** | **Cinq motifs**, dont l'angle mort 1 de `class-loader.php:96-100`, l'interdiction de `flush_rewrite_rules()`, la **borne 2 violée par définition**, un **état mort en base**, et le fait qu'elle **ne fermerait même pas `?author=N`** |
| 7 | Renommer le `user_nicename` (option D) ? | **Non** | Elle ne ferme pas l'oracle, c'est **une écriture en base qui ne survit pas à une base neuve**, et son mode de panne est *« elle ne mord jamais là où ça compte »*. **Consigne d'exploitation, pas ligne de code** |
| 8 | Domicile : `indexation-heritee/` ou un module neuf `query/archives-auteur/` ? | **`indexation-heritee/`, fichier neuf** | Le bloc d'exception du 2026-09-05 **a déjà pris `/author/admin/` en charge nommément** : #49 finit ce qu'il a commencé. Et séparer créerait **le mode de panne de #52** (le plan du site republiant ce que l'autre ferme). **L'objection sur le nom est réelle et écrite au §4**, avec l'alternative, remontée au lead |
| 9 | Le fichier : agrandir `plan-du-site.php` ou en créer un ? | **Neuf : `archives-d-auteur.php`** | `plan-du-site.php` porte un nom qui parle du plan du site ; y loger ceci donnerait **un fichier qui ment sur son contenu** — motif 1 à l'échelle du fichier. **Module ≠ fichier** : la cohésion de l'arbitrage 8 est conservée par le `bootstrap.php` commun |
| 10 | La garde de ceinture `template_redirect` 20 ? | **Retirée** | **M7 l'a rendue sans objet** : `class-wp.php:743-744`, `handle_404()` sort tôt sur un `is_404()` déjà posé. **Une ceinture contre un cas mesuré comme impossible est une garde qui rassure sans couvrir** (famille T92). L'énumération reste à cinq |
| 11 | Recopier la garde de contexte des modules voisins ? | **Non, écart délibéré** | `parse_request()` ne court ni en admin, ni en ajax, ni en cron — **et `REST_REQUEST` n'est pas encore défini** (M9). La recopier **donnerait l'illusion de la protection REST sans rien protéger**. La garde 1 teste `rest_route` |
| 12 | `?author=0` et `?author=` : 404, ou recopier le test du cœur ? | **404, présence de la clé, sans arithmétique** | Une règle **sans comparaison de valeur ne peut pas dériver avec une version de PHP**. Recopier `0 != $qv['author']` rouvrirait **la dette de `plan-du-site.php:110` en miniature**. La sur-couverture tombe **du bon côté** |
| 13 | Normaliser la casse ? | **Non** | Répondre 404 pour un nom **que le cœur n'a jamais routé**, c'est l'option C par la petite porte — **arbitrage 12 de #50, reconduit** |
| 14 | Garde de méthode HTTP ? | **Non** | **#50 arbitrage 11 reconduit** : une 301 sur un POST perd le corps, **un 404 ne perd rien** |
| 15 | Fermer les surfaces REST (R1, R2) dans cette issue ? | **Non — déclarées, non fermées** | Toucher la REST touche **l'éditeur de blocs et le sélecteur d'auteur**. **Autre issue, autre mesure, autre contrat.** Mais **elles sont mesurées et nommées au §9** : sans cela, un rapport dirait « T105 close » quand elle ne l'est qu'en partie |
| 16 | Geler ce contrat avant ou après la mesure ? | **Après** | Le `leaddev-back-mtb` n'a pas d'outil de shell et **l'a déclaré au lieu de le maquiller** ; le lead de chaîne a joué les quatorze questions dans le conteneur. **Geler avant la mesure est légitime (#50, arbitrage 16) ; coder avant la mesure ne l'est pas** |
| 17 | Qui écrit dans `docs/contracts/issue-1.md` ? | **Pas cette chaîne** | Fichier partagé par les trois chaînes du lot, **décision 70**. Texte livré prêt à coller au §12 |

---

## 18. Questions bloquantes

**Aucune.** Cette issue ne touche **aucun fait d'élevage** : aucun nom de chien, aucune date, aucune
généalogie, aucun numéro LOF, aucun résultat de test ou de concours, aucun écran de l'éleveuse. **Rien à
demander à l'éleveuse.**

**Deux signalements qui appartiennent au lead orchestrateur, et non à cette chaîne :**

1. **L'énoncé de l'issue et la consigne de lancement disaient « il n'y en a qu'un aujourd'hui » à propos
   des identifiants d'auteur. La mesure dit deux**, et le second est celui de l'éleveuse. Le périmètre a
   été élargi en conséquence (arbitrage 1). **Fait constaté, pas reproche** : c'est précisément ce que
   la consigne « mesure, ne déduis pas » devait produire.
2. **R1, R2, R4 et R6 restent ouverts** (§9). `/wp-json/wp/v2/users` publie **les deux identifiants de
   connexion à un visiteur anonyme** — un oracle plus riche que celui que #49 ferme. **C'est une dette
   neuve à ouvrir en issue**, et **T105 ne doit être déclarée close que sur son énoncé.**

---

# Amendement — 2026-09-08, issue #49 : le relevé après correctif, et la ligne du §12 que la refacto dément

> Ajout daté, conforme à la **convention d'amendement** déclarée en tête de ce contrat : aucune section
> numérotée ci-dessus n'est réécrite. Le §12 reste lisible tel qu'il a été gelé ; **cet amendement en
> corrige une formule ouvertement, et dit pourquoi.**

## A. Le relevé APRÈS — mesuré par le lead de chaîne, pas seulement rapporté

Relevé le **2026-09-08**, arbre de travail après implémentation **et après la passe de refacto**,
WordPress 6.9, hôte `http://localhost:3005`, mesuré **depuis le conteneur** (`curl --path-as-is
--connect-to`, sans cookie ni `--user`). `php -l` sur les **cinq** fichiers du module : **0 erreur**.

**L'objet de l'issue — la cible du §7 est ATTEINTE :**

| Adresse | Avant (`2574bf9`) | Après |
|---|---|---|
| `/nexiste-pas-du-tout/` *(référence)* | `404 · 18284 · md5 d9f9bae7` | **inchangée** |
| `/author/admin/` | `200 · 16379` | **`404 · 18284 · md5 d9f9bae7`** |
| `/author/fabienne/` | `200 · 14457` | **`404 · 18284 · md5 d9f9bae7`** |
| `/?author=1` | `301 → /author/admin/` | **`404 · 18284 · md5 d9f9bae7`** |
| `/?author=2` | `301 → /author/fabienne/` | **`404 · 18284 · md5 d9f9bae7`** |
| `/?author_name=admin` | `200 · 16379` | **`404 · 18284 · md5 d9f9bae7`** |
| `/author/admin/feed/` | `200 · 1704 · application/rss+xml` | **`404 · 18284 · text/html · md5 d9f9bae7`** |
| `/?feed=rss2&author=1` | `200 · 1710 · application/rss+xml` | **`404 · 18284 · md5 d9f9bae7`** |

> **`redirect_url` VIDE sur toutes**, et **le `md5sum` du corps est identique à celui de
> `/nexiste-pas-du-tout/`** : l'indiscernabilité est vérifiée **sur le corps**, pas sur la seule taille.
> *L'oracle d'énumération est fermé — `?author=1`, `?author=2`, `?author=3` et `?author=999` sont
> désormais indiscernables.* **Le devineur de 404 n'a pas mordu** (contrôle du §10 point 7, concluant).

**La garde REST mord, et c'est mesuré en anonyme** : `/wp-json/wp/v2/posts?author=1` → **`200 ·
1847 o · application/json`**, **filtre auteur toujours honoré par la REST**. *C'est la preuve
décisive : cette requête porte `author` **et** `rest_route` ; sans la garde 1, le filtre aurait retiré
`author` et posé `error = '404'`, et la REST serait cassée.* **La sonde connectée du contrôle P14 reste
due — elle n'a pas été jouée et n'est pas présentée comme faite** — mais la garde teste `rest_route`,
dont la présence est identique en anonyme et en session : *le raisonnement est écrit, il ne remplace pas
la sonde.*

**Non-régressions** : `/` `25491` · `/portees/` `31819` · `/travail/` `35411` — **identiques à
l'octet** *(le filtre court sur chacune : c'est ce qui prouve que la garde 2 sort bien)* ·
`/wp-sitemap-users-1.xml` → `404 · 18284` (**#50 intact**) · **`debug.log` : 0 octet avant, 0 octet
après** · **P11** : `'author'`/`'author_name'` → **une seule ligne dans `wp-content/plugins/`**, celle de
`CLES_D_AUTEUR` · **P12** : `'users'` → **une seule ligne dans `wp-content/`**, celle de
`FOURNISSEURS_RETIRES`.

**Les deux relevés intermédiaires du §11 sont concluants** : après le fichier écrit **non accroché**, et
après le `require_once` **seul**, le relevé est **identique à l'octet au relevé avant**. *Un fichier non
chargé ne fait rien ; l'accroche seule est causale.*

## B. La ligne du §12 que la refacto dément — corrigée ici

Le §12 livre au lead, prêt à coller dans `issue-1.md`, la formule : « **Aucun identifiant, aucun ID,
aucun `user_nicename` n'est écrit nulle part** ». **Elle est trop absolue, et le dépôt la contredit
lui-même** : le bloc d'exception motivée du 2026-09-05, en tête de `plan-du-site.php`, **cite
`user_login` = `user_nicename` = `admin`** — c'est le relevé qui a fondé l'exception, et il est
contractuellement inamovible.

Le §4 de ce contrat écrivait pourtant le bon qualificatif — « aucun ID **en dur** ». **C'est le §12 qui
l'avait laissé tomber.**

> **Texte corrigé, à coller à la place** : « **Aucun identifiant en dur, aucun numéro de compte, aucun
> `user_nicename` n'entre dans le code de ce module** — les seules occurrences du dépôt sont des relevés
> datés, en commentaire — donc le rappel ne lit **aucun compte** et vaut pour les comptes présents **et
> futurs**. »

*Un absolu qu'on ne peut pas tenir vaut moins qu'une vérité qualifiée : c'est la même discipline que
« aucune déduction présentée comme une mesure ».* **Trois formules de cette famille ont été corrigées
dans le code par la passe de refacto** ; celle-ci était dans le contrat, et elle se corrige ici plutôt
que de partir dans `issue-1.md`.

## C. Deux constats de mesure consignés plutôt que lissés

1. **`/?s=chien&author=2` rend `404` mais `18 289` octets — cinq de plus que la référence.** Cette
   adresse **n'est pas** dans les treize de la cible du §7, l'identité de `md5` ne lui est donc pas
   exigée. **Cause mesurée** : le champ de recherche du gabarit 404 est pré-rempli avec le terme du
   visiteur (`value="chien"`). **Aucun slug, aucun nom de compte : c'est la saisie du visiteur qui lui
   est renvoyée.** Le §8 n'exigeait que « 404 » : tenu.
2. **`/contact/` rend un `md5` différent à chaque requête, à taille identique à l'octet.** Cause
   isolée par `diff` : un jeton horodaté du formulaire de contact
   (`name="mtb_contact_jeton"`). **Indépendant de #49**, et sans rapport avec les archives d'auteur.
   **Ce qui n'est pas prouvé, et qui est écrit comme tel** : que cette instabilité préexistait — elle
   n'a été constatée qu'après l'édition. Elle est certaine par construction (horodatage dans la
   valeur), **mais elle n'a pas été rejouée en « avant ».**

## D. Un sixième bloc de prose corrigé dans `bootstrap.php`, non listé au §16 — arbitré

Le §16 nommait deux blocs de `bootstrap.php` rendus faux par #49. **Il y en avait un troisième** : « Les
**DEUX** hooks de front de ce module … n'écrivent RIEN », qui devient faux dès la troisième accroche, et
qui citait encore la borne 1 dans sa version #50 (« il lit, il RÉPOND ») alors que **le §13.1 du présent
contrat l'étend**. Le décompte a été corrigé et l'extension inscrite.

> **Arbitrage : la correction est retenue.** `bootstrap.php` est dans l'empreinte du §1, le bloc est
> **rendu faux par #49 lui-même**, et c'est exactement la classe de défaut que le §16.4 fait réparer sur
> le motif 2. **Zéro ligne exécutable** — prouvé par le rejeu des contrôles P4/P5, identiques à l'octet
> et au `md5`. *Un contrat qui énumère « deux blocs » ne rend pas le troisième acceptable ; il rend son
> omission visible, ce qui est le but.*

## E. Ce qui reste non joué après cet amendement — et n'est présenté nulle part comme joué

1. **Le contrôle P14** — l'éditeur de blocs ouvert **en session**. Le §A ci-dessus dit ce qui a été
   mesuré à sa place, et pourquoi cela ne l'en dispense pas. **Il revient à la passe d'intégration du
   lot.**
2. **Les résidus R1, R2, R4 et R6 du §9 restent OUVERTS.** Remesurés après correctif :
   `/wp-json/wp/v2/users` → **`200 · 672 o`, les deux comptes, `"slug":"admin"` et
   `"slug":"fabienne"`** ; `/wp-json/wp/v2/users/1` → `200 · 335 o`. **C'est un oracle plus riche que
   celui que #49 ferme, et il survit intact.** *T105 n'est close que sur son énoncé.*
3. Les points du §14 sont inchangés : le compte sans contenu publié, la création d'un troisième compte,
   et le comportement d'une version future du cœur restent établis **par construction, jamais par
   mesure**.

---

# Amendement — 2026-09-08, issue #49, correctif HIGH : la prémisse du §6 que la mesure dément, et l'écran qu'elle a cassé

> Ajout daté, conforme à la **convention d'amendement** déclarée en tête de ce contrat. **Le §6 et le
> §17 restent lisibles tels qu'ils ont été gelés ; cet amendement en contredit trois passages
> ouvertement, et dit pourquoi.** *Un contrat qu'on réécrit en silence ne prouve plus rien ; un contrat
> qu'on amende par écrit garde la trace de ce qu'il a cru.*

## A. La prémisse fausse, et les trois endroits où ce contrat l'écrit

Le §6, sous-section « La garde de contexte des modules voisins n'est PAS recopiée », gèle :

> « `parse_request()` ne court ni dans `wp-admin`, ni sur `admin-ajax.php`, ni sur `wp-cron.php` »

**Les deux dernières branches sont vraies. La première est FAUSSE.** Elle n'a été mesurée par personne :
les quatorze questions du §3.2 **ne la posaient pas**, et le §14 — « ce qui n'est pas mesuré » — **ne la
nommait pas**. *Elle est passée entre les mailles précisément parce qu'elle avait l'air d'une évidence,
et c'est la seule ligne de ce contrat que personne n'a songé à falsifier.*

**Les trois passages atteints, tous laissés lisibles tels quels :**

| § | Passage | Ce qui est faux |
|---|---|---|
| **§6**, tableau, ligne **garde 1** | La garde ne porte que `isset( $variables['rest_route'] )` | **Incomplète** : il y manque `is_admin()` |
| **§6**, sous-section « La garde de contexte … n'est PAS recopiée » | « `parse_request()` ne court ni dans `wp-admin` … » | **La branche `wp-admin` est fausse** |
| **§17**, arbitrage **11** | Même prémisse, mot pour mot | **Idem** |

**Ce qui reste juste et n'est PAS touché** : le §15 (interdits), le §11 (contrôles P7 et P11), et **le
motif pour lequel `defined( 'REST_REQUEST' )` reste interdit** — voir §D.

## B. Le fait manquant — **M15**, ajouté à la liste du §3

> **M15 — Le filtre `request` court-il en administration ?**
> **Réponse : OUI.** Relevé le 2026-09-08 dans le conteneur, WordPress 6.9.
>
> - `wp-admin/includes/post.php:1319` — `wp( $query );` dans `wp_edit_posts_query()`
> - `wp-admin/includes/post.php:1407` — `wp( wp_edit_attachments_query_vars( $q ) );` dans
>   `wp_edit_attachments_query()`
> - Ce sont les **deux seuls** sites d'appel de `wp()` dans tout `wp-admin/` — la recherche rend
>   **trois** lignes, la troisième (`class-wp-posts-list-table.php:164`) étant **un commentaire**.
> - `wp()` appelle `WP::main()`, qui appelle `WP::parse_request()`, qui applique le filtre `request`.
> - `author` **est une variable publique** (`class-wp.php:18`), donc elle entre dans `query_vars` en
>   administration — **le fait 1 de ce contrat le disait déjà, pour un autre usage.**
>
> **Corollaires relevés, chacun avec son ancre :**
> - `wp-cron.php` **n'appelle jamais `wp()`** : recherche sur le fichier → **aucune occurrence** ; il
>   pose `define( 'DOING_CRON', true )` (`:42`) et charge `wp-load.php` (`:46`), rien de plus. **La
>   branche `wp-cron.php` du §6 était vraie — et elle n'était, elle non plus, adossée à aucun relevé.**
> - `admin-ajax.php` et `admin-post.php` définissent `WP_ADMIN` mais **n'appellent jamais `wp()`**.
> - **Médiathèque — et le détail se lit de travers, donc il s'écrit en entier.** Ce sont **deux
>   fonctions de noms voisins et de rôles disjoints** : `post.php:1333`,
>   `wp_edit_attachments_query_vars()`, qui **n'appelle jamais `wp()`** et se contente de fabriquer des
>   variables de requête ; et `post.php:1406`, `wp_edit_attachments_query()`, qui **appelle `wp()` en
>   `:1407`**. Seule la seconde est appelée par le **mode liste**
>   (`class-wp-media-list-table.php:102`). Le **mode grille** (`upload.php:140`,
>   `if ( 'grid' === $mode )`) appelle bien `wp_edit_attachments_query_vars()` en `:158` — **dans sa
>   propre branche** — mais c'est **l'autre fonction**, et il va chercher ses données par
>   `admin-ajax.php:103` (`'query-attachments'`, traitée en `ajax-actions.php:3021`), qui n'appelle
>   jamais `wp()`. Et **le mode par défaut est `grid`** (`upload.php:137`) : **le défaut ne touchait donc
>   la Médiathèque QUE hors de son mode par défaut** — *une raison de plus pour qu'il passe inaperçu, et
>   une raison de plus de ne pas se fier à « personne ne s'en est plaint ».*
>
>   > **Rectification du jour, écrite plutôt que passée sous silence.** La première rédaction de ce
>   > corollaire plaçait `:158` « dans la branche **liste** » : **c'est faux**, `:158` est dans la
>   > branche **grille**, et le chemin réel passe par deux fonctions distinctes. La **conclusion** —
>   > seul le mode liste atteint `wp()` — était juste ; **le chemin ne l'était pas.** Corrigé le
>   > 2026-09-08, avant tout push, sur relevé. *Laisser un chemin faux sous un titre qui dit « AUCUN
>   > N'EST DÉDUIT » aurait reproduit le défaut même que cet amendement répare.*

## C. Ce que la prémisse fausse a coûté — et c'est un écran de l'éleveuse

**Mesuré par moi, en session connectée comme `fabienne` (rôle Éditeur), AVANT correctif :**

| Écran | Avant | Après |
|---|---|---|
| `edit.php?post_type=mtb_portee` | `200 · 175 431 · 20 lignes · « 33 éléments »` | **identique à l'octet** |
| `edit.php?post_type=mtb_portee&author=2` | **`404`** · 175 619 · **20 lignes** · **« 33 éléments »** | **`200` · 125 845 · `1` ligne · « 1 élément »** |
| `edit.php?post_type=mtb_chien&author=1` | **`404`** · **20 lignes** | **`200` · `19` lignes · « 19 éléments »** |
| `upload.php?mode=list&author=2` | **`404`** · **20 lignes** | **`200` · `0` ligne** *(le compte 2 n'a aucun média — état vide du cœur, vérifié en base)* |
| `upload.php` (**grille**) | `200 · 186 608` | **identique à l'octet** |

> **Elle possède 1 portée sur 33.** L'onglet « Le mien » s'affichait donc pour elle sur l'écran
> **Portées**, celui qu'elle utilise le plus. Elle cliquait, et obtenait **les 33 portées** sous un
> onglet qui en annonce une, **servies en 404** — sans un message, sans un mot technique, **sans une
> ligne au journal** (`debug.log` à 0 octet, vérifié).
>
> **Un écran qui a l'air de marcher et qui ment sur ce qu'il montre est pire qu'un écran cassé : elle
> n'a aucune raison de le signaler.** C'est le mode de panne que ce contrat traquait sur le front, et
> qu'il a laissé entrer par l'administration.

**Le compte de lignes est la preuve, pas le code de statut** : la liste filtrée rend **1 ligne**, pas
20, et le contrôle croisé en base donne bien **1** `mtb_portee` au compte 2 et **19** `mtb_chien`
publiés au compte 1. *Rendre 200 ne suffisait pas : il fallait que l'écran dise vrai.*

## D. Le correctif — **une seule ligne exécutable**, et pourquoi elle ne rouvre rien

**§6, garde 1, forme corrigée** : la condition de sortie devient
`is_admin() || isset( $variables['rest_route'] )`, le corps et l'ordre des cinq gardes restant
inchangés.

**Vérifié sur le diff : c'est la SEULE ligne exécutable modifiée dans tout le dépôt.** Tout le reste est
du commentaire.

**La fermeture de T105 sur le front reste ENTIÈRE — mesuré, pas déduit**, parce que c'est exactement la
classe de prémisse qui nous a menés ici :

| Adresse, **en anonyme** | Mesuré |
|---|---|
| `/wp-admin/edit.php?post_type=mtb_portee&author=2` | **`302` · 0 octet** → `wp-login.php?redirect_to=…&reauth=1` |
| `/wp-admin/upload.php?author=2&mode=list` | **`302` · 0 octet** → `wp-login.php…` |
| `/wp-admin/edit.php?author=1` | **`302` · 0 octet** → `wp-login.php…` |
| `/wp-admin/admin-ajax.php?action=…&author=1` | `400` |
| `/wp-admin/admin-post.php?action=…&author=1` | `400` |

`wp-admin/admin.php:104` appelle **`auth_redirect()`** *avant* que `edit.php` n'atteigne
`wp_edit_posts_query()`. Un anonyme est donc renvoyé à la connexion **avant que `wp()` ne coure**, avec
un **corps de zéro octet**. Et les deux seuls autres contextes où `is_admin()` vaut vrai
— `admin-ajax.php`, `admin-post.php` — **n'appellent jamais `wp()`** (§B).

> **`is_admin()` RESTREINT la surface d'action du rappel, il ne l'étend pas.** C'est `auth_redirect()`
> du cœur, mesuré, qui garde la porte.

**Les 13 adresses de front du §7 sont INCHANGÉES** — toutes `404 · text/html · 18 284`,
`redirect_url` **vide**, md5 du corps `d9f9bae7`, identique à `/nexiste-pas-du-tout/` relevé dans le
même relevé. *C'est le contrôle qui prouve que le correctif ne rouvre rien.* La garde REST est intacte :
`/wp-json/wp/v2/posts?author=1` → `200 · 1 847 o`, filtre auteur honoré ; `?author=2` → `200 · 2 o`
(`[]`), **et la différence prouve que le filtre est réellement appliqué, pas ignoré.**
Non-régressions : `/` `25 491` · `/portees/` `31 819` · `/travail/` `35 411` ·
`/wp-sitemap-users-1.xml` `404 · 18 284` · `wp mtb verifier-redirections` **code 0** ·
`/bhpl/port%C3%A9e-m-2016/` et sa forme UTF-8 brute → `301` · les cinq contenus en sommeil →
`noindex, follow` · **`debug.log` : 0 octet avant et après** · `php -l` : **0 erreur**.

### `defined( 'REST_REQUEST' )` reste INTERDIT — l'interdit du §15 est CONFIRMÉ, pas assoupli

Le §15 tient sans changement, et la revue l'a revérifié : appliquer le filtre à un tableau portant
`rest_route` et `author` rend le tableau **intact**. À l'instant où ce filtre court, `REST_REQUEST`
**n'est pas encore définie** — c'est `rest_api_loaded()` qui la définit, **après** (fait 2). **C'est
`rest_route` qui protège, et lui seul.**

**Et `wp_doing_ajax()` / `wp_doing_cron()` ne sont toujours PAS recopiés** — mais désormais **pour un
motif relevé et non supposé** (§B) : ni `admin-ajax.php`, ni `admin-post.php`, ni `wp-cron.php`
n'appellent `wp()`. *Le §6 avait la bonne conclusion sur ces deux termes, avec un motif qu'il n'avait
pas mesuré. La conclusion tient ; le motif est maintenant écrit.*

## E. L'arbitrage 11 du §17, corrigé

> **Recopier la garde de contexte des modules voisins ? — Décision révisée : `is_admin()` OUI, le reste
> NON.**
> Le motif gelé (« `parse_request()` ne court ni en admin, ni en ajax, ni en cron ») était **faux sur
> son premier tiers**. `is_admin()` entre donc à la garde 1, **non pour aligner sur les voisins**, mais
> parce que **la mesure montre que ce filtre court sur les écrans de liste de l'administration**.
> `wp_doing_ajax()`, `wp_doing_cron()` et `defined( 'REST_REQUEST' )` restent écartés, avec le motif
> relevé du §D. *La forme de la garde des voisins n'est toujours pas recopiée ; c'est un terme sur
> quatre qui est repris, pour une raison qui lui est propre.*

## F. La leçon, écrite parce qu'elle vaut plus que le correctif

**Ce contrat a fait tout ce qu'il fallait sur le cœur de WordPress** : quatorze questions, chacune avec
son fichier, ses numéros de ligne, ses branches de falsification écrites d'avance. **Et il a laissé
passer une phrase de contexte qu'il n'a pas pensé à interroger** — parce qu'elle décrivait *où le code
ne court pas*, et qu'on falsifie spontanément ce qu'on affirme, pas ce qu'on nie.

> **La liste des mesures dues protège ce qu'on a pensé à y mettre. Une prémisse énoncée comme une
> évidence, dans un commentaire, n'entre dans aucune liste — et c'est là qu'elle coûte le plus cher.**

Corollaire appliqué ici : les **deux corollaires du §B** — `wp-cron.php` et le mode grille — étaient
**vrais mais non mesurés**, sous un titre affirmant « AUCUN N'EST DÉDUIT ». **Ils sont désormais
relevés et ancrés.** *Une affirmation vraie sans ancre est la même dette que celle qui vient de casser
un écran ; elle n'a simplement pas encore été payée.*

## G. Ce qui reste non joué après cet amendement

1. **Le contrôle P14** — l'éditeur de blocs ouvert **à la main dans un navigateur**, sélecteur de lien
   manipulé, enregistrement joué. **Toujours non joué.** Mesuré à sa place : `post.php?post=…&action=edit`
   et `post-new.php` en session → `200` — **mais `post.php` n'appelle jamais `wp()`, il n'était donc pas
   exposé au défaut** : cette mesure ne tient pas lieu de sonde. Revient à la passe d'intégration du lot.
2. **Le mode grille de la Médiathèque en usage réel** : la page `upload.php` est mesurée (identique à
   l'octet), **pas** l'appel ajax `query-attachments` avec un filtre auteur depuis l'interface.
3. **Les résidus R1, R2, R4 et R6 du §9 restent OUVERTS.** `/wp-json/wp/v2/users` publie toujours **les
   deux identifiants** à un visiteur anonyme. **T105 n'est close que sur son énoncé.**
4. Les numéros de ligne du **fait 9** sont, comme les huit autres, **épinglés à WordPress 6.9** et se
   périmeront en silence.
