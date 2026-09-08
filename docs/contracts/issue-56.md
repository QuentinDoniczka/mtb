# Contrat d'interface — Issue #56 — Ne plus publier l'identité d'un compte (REST, flux, oEmbed)

**Gelé le 2026-09-08.** Milestone 16, labels `seo` et `feature`. Dette **T113**. Suite directe de #49,
dont il ferme les résidus **R1, R2, R4** du §9 — **et R3, que #49 avait classé inoffensif et que la
mesure dément.**

Ce contrat est **le point de réconciliation d'une chaîne à un seul côté** : #56 ne touche aucun fichier
du thème. Il n'y a donc pas eu de `leaddev-front-mtb`, et les sections de gabarit qui supposent un côté
thème sont **sans objet et le disent** (§10). Ce que ce contrat gèle, c'est la frontière avec **le cœur
de WordPress** — un chemin de code **non versionné, absent de l'arbre, donc à mesurer et non à croire.**

> **Convention d'amendement** (décision 65, reconduite de #50 et #49) — ce contrat s'amende par **ajout
> daté en fin de fichier**, jamais par réécriture d'une section numérotée : une citation par numéro de §
> ne doit jamais se périmer.

> **Ce contrat est gelé APRÈS la mesure.** Les quatre surfaces ont été relevées **dans le conteneur par
> le lead de chaîne** le 2026-09-08, avant la première ligne de code, et **les onze mesures dues
> réclamées par le plan ont été jouées avant le gel**. Le §3 est un **relevé**, pas une déduction.
> **Aucune ligne de ce contrat ne présente une déduction comme une mesure** ; ce qui reste déduit est
> isolé au §14 et nulle part ailleurs.

> **La règle de rédaction qui prime, et le motif pour lequel elle existe.** Le lot 20 a été bloqué par
> une prémisse énoncée au présent de l'indicatif dans le contrat gelé de #49 — « `parse_request()` ne
> court ni dans `wp-admin`, ni sur `admin-ajax.php`, ni sur `wp-cron.php` ». **La première branche était
> fausse**, et elle a produit un écran d'administration rendant 404 en affichant les 33 portées sous un
> onglet annonçant « Le mien (1) », sans un mot et sans une ligne au journal. **Toute affirmation sur le
> cœur qui n'est pas au §3 est une DÉDUCTION et figure au §14.** Aucune ancre `fichier:ligne` du cœur
> n'apparaît ici qui n'ait été **ouverte dans le conteneur par le lead de chaîne**.

---

## 1. L'empreinte fichiers

| Chemin | Nature |
|---|---|
| `wp-content/plugins/mtb-core/includes/migration/indexation-heritee/identite-des-comptes.php` | **création** |
| `wp-content/plugins/mtb-core/includes/migration/indexation-heritee/bootstrap.php` | modification — trois `add_filter`, un `require_once`, et **quatre blocs de commentaire rendus faux par #56** |
| `docs/contracts/issue-56.md` | ce fichier |

**Interdit, sans exception** : `includes/admin/**` et `docs/guide/**` (**chaînes #54 et #55, qui
tournent en parallèle dans le même arbre de travail — écriture jamais**) · `docs/ETAT.md` (niveau du
lead) · `docs/contracts/issue-*.md` autres que celui-ci (**gelés, ils ne se rouvrent pas** ; les
relèves sont au §13) · `archives-d-auteur.php`, `plan-du-site.php`, `conversion.php`, `fait.php`
(**lecture permise, écriture jamais**) · `class-loader.php` · `mtb-core.php` · `includes/query/**` ·
`includes/content/**` · `includes/blocks/**` · **tout fichier du thème** · `theme.json` · toute feuille
sous `themes/mtb/assets/css/**` · `Makefile` · `compose.yaml` · `docker/**` · `wp-login.php` et tout
fichier du cœur.

**Aucune feuille CSS n'est touchée : `make css` est sans objet**, et aucun artefact `*.min.css` n'entre
à cette empreinte.

**Aucune fiche de `docs/guide/` n'est écrite, et aucune n'est due.** #56 ne change **rien** de ce que
l'éleveuse voit : pas un écran, pas un mot, pas un bouton, pas une adresse qu'elle utilise. **D3 est
sans objet ici, et c'est écrit plutôt que tu.** *La seule surface de ce sujet qui aurait changé quelque
chose qu'elle lit est `wp-login.php` — et c'est précisément l'une des raisons pour lesquelles elle ne
fait pas partie de cette issue (§5).*

---

## 2. Le défaut — et les trois endroits où l'énoncé de l'issue ne dit pas juste

L'issue nomme trois surfaces. **La mesure en a trouvé quatre, en a disculpé trois autres, et corrige
l'énoncé sur deux points de fait.**

### 2.1 Ce qui fuit réellement, mesuré

| Surface | Code | Taille | Ce qui fuit |
|---|---|---|---|
| `/wp-json/wp/v2/users` | 200 | 672 | **les deux comptes** : `"slug":"admin"`, `"slug":"fabienne"`, **`"name":"Fabienne Guéneau"`**, et le `link` vers `/author/<slug>/` |
| `/wp-json/wp/v2/users/1` · `/2` | 200 | 335 · 334 | le slug, le nom — **et 404 sur `/3` et `/999` : l'oracle par identifiant** |
| **`/?rest_route=/wp/v2/users`** | 200 | 672 | **md5 identique à la forme jolie — la seconde écriture de la même route, que l'énoncé ne nomme pas** |
| **`/wp-json/wp/v2/users?search=fab`** | 200 | 336 | **ne rend QUE le compte 2 — un oracle par recherche de nom, non nommé par l'énoncé** |
| **`?_embed=1` sur `pages` et `posts`** | 200 | — | **republie l'objet utilisateur entier** dans `_embedded.author` — **une porte de derrière que fermer la seule route ne suffirait pas à condamner si elle ne passait pas par elle** |
| `/feed/`, `/feed/rdf/` | 200 | — | `<dc:creator><![CDATA[admin]]></dc:creator>` |
| **`/feed/atom/`** | 200 | 1658 | **`<name>admin</name>` — troisième gabarit, que l'énoncé ne nomme pas** |
| **`/wp-json/oembed/1.0/embed?url=<contenu singulier>`** | 200 | — | **`"author_name":"admin"`, `"author_url":".../author/admin/"`** |
| **la même, `&format=xml`** | 200 | 2335 | **`<author_name>admin</author_name>`** |
| `wp-login.php` | 200 | — | message distinct, **plus trois autres discriminants** (§5) |

### 2.2 Les deux points où l'énoncé de l'issue est inexact — dits, pas tus

1. **« `/feed/` → `dc:creator` … l'identifiant de connexion lui-même, pas un nom d'affichage. »**
   **C'est inexact, et la distinction change le remède.** `author-template.php:38` porte
   `apply_filters( 'the_author', is_object( $authordata ) ? $authordata->display_name : '' )` : le champ
   publie le **`display_name`**. Il se trouve que, sur le compte 1, `display_name` **vaut** `admin`
   (relevé M1 de #49 : `1,admin,admin,admin,administrator`). *Le flux ne publie pas le login ; il publie
   un nom d'affichage qui lui est égal sur ce compte-là, dans cette base-là.* **Un filtre existe** —
   aucun gabarit du cœur n'a donc à être recopié, et le croire aurait mené à le recopier.
2. **`/wp-json/wp/v2/users/3` → 404 « l'oracle par identifiant est donc intact ».** Exact. **Mais
   l'énoncé n'en tire pas la conséquence utile** : la même discrimination existe sous
   `?rest_route=`, et l'oracle le plus commode n'est ni la liste ni l'identifiant, c'est
   **`?search=`**, qui rend un compte sur un fragment de nom. **Les fermer à moitié serait une moitié
   présentée comme un tout** — la faute que #24 §16 institue comme interdite.

### 2.3 R3 — #49 §9 le classait inoffensif ; la mesure le dément

#49 §9 écrit : « `/wp-json/oembed/1.0/embed?url=…` → `"author_name":"Berger Hollandais du Mont Brabant"`
— **le nom du SITE, pas un compte.** *Moins grave que le plan ne le craignait, et c'est écrit* ».

**Sa conclusion était juste pour la seule URL mesurée, et fausse en général.** `embed.php:604-607` pose
le nom du site **par défaut**, et `:612-615` l'**écrase** dès qu'un auteur est résolu :

```php
'author_name'   => get_bloginfo( 'name' ),
'author_url'    => get_home_url(),
…
$author = get_userdata( $post->post_author );
if ( $author ) {
    $data['author_name'] = $author->display_name;
    $data['author_url']  = get_author_posts_url( $author->ID );
}
```

**#49 n'avait mesuré que l'accueil**, qui tombe sur la branche par défaut. Sur **tout contenu
singulier**, la branche `:613` publie `admin` et `.../author/admin/`.

> **Ce n'est pas un reproche à #49, c'est la démonstration de sa propre règle** : une conclusion juste
> adossée à une mesure qui ne la porte pas est une dette qui se paie un lot plus tard. **#49 §9 est
> relevé au §13.2 du présent contrat.**

### 2.4 La ligne de DoD, dite exactement

**Aucune ligne D1–D12 ne couvre nommément une fuite d'identité de compte par API.** L'issue sert la
règle transverse du **BRIEF §4 — « zéro donnée personnelle inutile »** — et, dans son esprit, **D5**.
**C'est une dette de vie privée, elle ne s'habille pas en obligation contractuelle.**

**Ce qui est dit sans dramatiser** : le site n'est pas en ligne, et un identifiant de connexion n'est
pas un secret — c'est la moitié publique d'un couple. **Ce qui distingue #56 de #49, en revanche, doit
être écrit** : `/wp-json/wp/v2/users` publie **le nom civil de l'éleveuse**, en une requête, sans
cookie. Ce n'est plus seulement un identifiant technique.

---

## 3. Le relevé — joué dans le conteneur, avant la première ligne de code

**Contexte de mesure** : 2026-09-08T10:56Z · commit de travail **`f9d7623`** · **WordPress 6.9** · hôte
`http://localhost:3005` · mesuré **depuis le conteneur `wordpress`**, `curl --path-as-is --connect-to
"localhost:3005:localhost:80"`, **sans cookie ni `--user`** sauf mention contraire ·
`permalink_structure` = `/%postname%/` · `blog_public` = `1` · **`debug.log` : 0 octet.**

**Calibrage reproduit à l'octet** — les valeurs de référence de #49 et #50 sont **toutes inchangées** :
`/nexiste-pas-du-tout/` → `404 · text/html · 18284`, **md5 `d9f9bae7edf3ba3b6787b341d41688cd`** ·
`/` `25491` · `/portees/` `31819` · `/travail/` `35411` · `/author/admin/` `404 · 18284 · d9f9bae7…`
(**#49 intact**) · `/?author=1` `404 · 18284 · d9f9bae7…` (**#49 intact**) ·
`/wp-sitemap-users-1.xml` `404 · 18284 · d9f9bae7…` (**#50 intact**).

### 3.1 La référence d'indiscernabilité REST

> **`/wp-json/wp/v2/nexiste-pas-du-tout` → `404 · 183 · application/json`, md5
> `4015aeea866cc65662e69632c8038bef`, corps `{"code":"rest_no_route", …}`.**
>
> **C'est la cible du §7.** Elle se **rejoue** dans le même relevé, elle ne se recopie **jamais** en
> constante — discipline de P2 chez #50, reconduite par #49 §7.

### 3.2 Les faits du cœur, relevés — aucun n'est déduit

| # | Fait | Ancre ouverte |
|---|---|---|
| **F1** | `$endpoints = apply_filters( 'rest_endpoints', $endpoints );` — **à l'intérieur de `get_routes()`** (`:956`) | `wp-includes/rest-api/class-wp-rest-server.php:973` |
| **F2** | `get_routes()` est appelée par **`dispatch()`** (`:1167`), **par l'index** (`:1373`) et en `:1527` (**appelant non identifié — voir §14**) | idem |
| **F3** | `serve_request()` : `:436` `$result = $this->check_authentication();` **puis** `:439` `$result = $this->dispatch( $request );`. **L'authentification court AVANT la table des routes** | idem |
| **F4** | `'rest_no_route'` — **la réponse du cœur pour une route absente** | idem `:1219` |
| **F5** | `$full_route = '/' . $clean_namespace . '/' . trim( $route, '/' );` — **la clé exacte du tableau `$endpoints`** | `wp-includes/rest-api.php:153` |
| **F6** | `$this->rest_base = 'users';` (`:42`) et **trois `register_rest_route` DISTINCTS** : `:58` `'/' . $this->rest_base` · `:79` `'/' . $this->rest_base . '/(?P<id>[\d]+)'` · `:126` `'/' . $this->rest_base . '/me'`, cette dernière avec `'permission_callback' => '__return_true'` | `wp-includes/rest-api/endpoints/class-wp-rest-users-controller.php` |
| **F7** | `<dc:creator><![CDATA[<?php the_author(); ?>]]></dc:creator>` | `wp-includes/feed-rss2.php:95` |
| **F8** | `<dc:creator><![CDATA[<?php the_author(); ?>]]></dc:creator>` | `wp-includes/feed-rdf.php:78` |
| **F9** | `<name><?php the_author(); ?></name>`, **sans CDATA**, et `<uri>` **sous condition** en `:58-61` | `wp-includes/feed-atom.php:56` |
| **F10** | `get_comment_author_rss()` — **auteur de commentaire, pas un compte** | `wp-includes/feed-rss2-comments.php:98` |
| **F11** | `return apply_filters( 'the_author', is_object( $authordata ) ? $authordata->display_name : '' );` — **le filtre unique des trois gabarits** | `wp-includes/author-template.php:38` |
| **F12** | `'author_name' => get_bloginfo( 'name' )`, `'author_url' => get_home_url()` **par défaut**, puis `$data['author_name'] = $author->display_name; $data['author_url'] = get_author_posts_url( $author->ID );` **quand l'auteur est résolu** | `wp-includes/embed.php:604-607` et `:612-615` |
| **F13** | `return apply_filters( 'oembed_response_data', $data, $post, $width, $height );` | `wp-includes/embed.php:627` |
| **F14** | `_oembed_rest_pre_serve_request()` **convertit en XML des données DÉJÀ assemblées** : `:789` teste `format`, `:801` `_oembed_create_xml( $data )`. **Le filtre F13 couvre donc JSON et XML** | `wp-includes/embed.php:782-810` |
| **F15** | `$author = get_the_author();` — **la colonne « Auteur » des écrans de liste passe par `the_author`** | `wp-admin/includes/class-wp-posts-list-table.php:1284` |
| **F16** | `incorrect_password` (`:212`) contre `invalid_username` (`:185`) | `wp-includes/user.php` |
| **F17** | `$user_login = ( 'incorrect_password' === $errors->get_error_code() \|\| 'empty_password' === … ) ? wp_unslash( $_POST['log'] ) : '';` — **la repopulation du champ est pilotée par le CODE d'erreur, pas par le message** | `wp-login.php:1493` |
| **F18** | `apply_filters( 'lostpassword_errors', … )` en `:3271`, **AVANT** la garde `! $user_data` de `:3277-3279` qui rajoute `invalidcombo` et rend la main | `wp-includes/user.php` |
| **F19** | `retrieve_password()` est appelée en `:839` et la redirection décidée en `:842` — **aucun filtre entre les deux** | `wp-login.php` |
| **F20** | `get_bloginfo_rss()` fait `strip_tags( get_bloginfo( $show ) )`, et le titre du canal passe par `wp_title_rss()`, **pas** par `get_bloginfo( 'name' )` | `wp-includes/feed.php:27-28`, `feed-rss2.php:41`, `feed-atom.php:34` |

**Les six clés réellement présentes dans l'index `/wp-json/`** — relevé, pas déduit, et **confirmé deux
fois** (index rendu, et F5+F6) :

```
/wp/v2/users
/wp/v2/users/(?P<id>[\d]+)
/wp/v2/users/me
/wp/v2/users/(?P<user_id>(?:[\d]+|me))/application-passwords
/wp/v2/users/(?P<user_id>(?:[\d]+|me))/application-passwords/introspect
/wp/v2/users/(?P<user_id>(?:[\d]+|me))/application-passwords/(?P<uuid>[\w\-]+)
```

**Aucun rappel de `rest_endpoints`, `the_author` ou `oembed_response_data` n'existe dans
`wp-content/`** — vérifié par recherche le 2026-09-08. **Aucune concurrence de priorité.**

### 3.3 Les onze mesures dues réclamées par le plan — TOUTES jouées avant le gel

| # | Verdict | Relevé |
|---|---|---|
| **M-C** | **Résolue, et elle corrige le plan** | Le défaut d'`author_url` est **`get_home_url()`**, et non `get_bloginfo( 'url' )` ni `site_url()`. **C'est cette expression-là qui s'écrit** (F12) |
| **M-D** | **FAUSSE ALERTE, et c'est un motif du §4 qui tombe** | `/portees/a1-2025/embed/` → `200 · 16450`. Les **deux** occurrences de `admin` sont **`dashicons-admin-comments`, un nom de classe CSS dans un SVG en `data:`**. `theme-compat/embed-content.php` **ne contient aucun appel d'auteur**, et le thème n'a **aucun gabarit d'embarquement**. **Le gabarit d'embarquement ne publie AUCUNE identité de compte** |
| **M-E** | **Confirmée, et elle rend la garde OBLIGATOIRE** | `class-wp-posts-list-table.php:1284` — `$author = get_the_author();`. Et l'observable le confirme : l'écran Pages rend `<td class="author column-author" …><a href="edit.php?post_type=page&author=1">admin</a>` |
| **M-G** | **Mesurée — et le contenu la désamorce** | `/feed/atom/` porte **un** `<uri>`. Sa valeur est **l'URL du site** (`"url":"http://localhost:3005"` du compte 1, relevé A2), **pas une donnée personnelle** |
| **M-I** | **PROPRE** | **Aucune** adresse de courriel, **aucun** `<email>`, dans `/feed/`, `/feed/atom/`, `/feed/rdf/`, `/portees/feed/` ni `/comments/feed/`. *La fuite d'un ordre supérieur que le plan redoutait n'existe pas* |
| **M-J** | **Confirmée, et le correctif la couvre** | `…&format=xml` → `200 · 2335 · text/xml`, `<author_name>admin</author_name>`, `<author_url>http://localhost:3005/author/admin/</author_url>`. **F14 établit que le XML est assemblé APRÈS F13 : un seul rappel couvre les deux formes** |
| **M-K** | **Mesurée — et elle nuance le §4** | Le titre du canal passe par `wp_title_rss()`, **pas** par `get_bloginfo( 'name' )` (F20). *L'argument « c'est la valeur du canal » ne tient donc pas tel quel ; celui de F12 — « c'est le défaut du cœur pour ce champ-là » — tient, lui, et c'est celui qui est retenu* |
| **M-A** | **NON JOUABLE AVANT LE CODE — branche de falsification écrite au §4** | Elle exige que les routes soient retirées. **Ligne de base AVANT captée en session** (§3.4). **Elle est due au §11, elle n'est présentée nulle part comme faite** |
| **M-B** | **NON JOUABLE AVANT LE CODE** | Idem. Cible et branche d'arrêt au §7 |
| **M-F** | **NON JOUABLE AVANT LE CODE** | Couverte par le rejeu pas-à-pas du §11 |
| **M-H** | **SANS OBJET** | Elle n'était due que si R8 devait être fermé. **M-G le désamorce : R8 n'est pas fermé** (§9) |

### 3.4 Ligne de base **authentifiée** — session Éditrice `fabienne`, avant tout code

| Écran | Code | Taille | md5 |
|---|---|---|---|
| `edit.php?post_type=mtb_portee` | 200 | **175 431** | `0c4a57b4a2e4c8f591fe2017ccb087bf` |
| `edit.php?post_type=page` | 200 | 161 962 | `dc60e795a118ce3ca07edfc7dc43af1e` |
| `edit.php?post_type=post` | 200 | 134 750 | `d6fd9a94771866b156e91255e8340b9c` |
| `upload.php?mode=list` | 200 | 186 838 | `e2041b368e7082cbcb2ddd0753733842` |
| `upload.php` (**grille, mode par défaut**) | 200 | 186 608 | `6df335320cad0f61dedd550c766279f0` |
| `post-new.php?post_type=page` | 200 | 760 073 | `89e331b515b468fdb4f6c5b7963ac94c` |
| `profile.php` | 200 | 125 826 | `d1dbb3a24761c510109007b614c58028` |
| `post.php?post=6&action=edit` (**éditeur de blocs**) | 200 | **765 282** | — |

> **`175 431` est identique à l'octet à la valeur relevée par l'amendement HIGH de #49.** *La ligne de
> base se recoupe avec celle du lot précédent : la session est réelle et l'écran n'a pas bougé.*

**Deux faits de cette ligne de base qui commandent le §6 :**

1. **La page d'édition d'une PAGE porte `wp/v2/users` dans son préchargement** — **une** occurrence,
   relevée par `grep -c` sur le HTML rendu. *C'est le risque de M-A, et il est chiffré plutôt que
   supposé.*
2. **La colonne « Auteur » de l'écran Pages rend `admin`** — donc `the_author` court bel et bien en
   administration (M-E). **Sans la garde du §6, cet écran afficherait « Berger Hollandais du Mont
   Brabant » comme auteur de tout, en silence.** *C'est mot pour mot le mode de panne de l'amendement
   HIGH de #49, et il est cette fois vu AVANT d'être livré.*

**Note de méthode, écrite parce qu'elle se lit de travers** : en session, `/wp-json/wp/v2/users/me`
rend **401** avec le seul cookie. **Ce n'est pas une session absente** : la REST exige un `X-WP-Nonce`
en plus du cookie. Les écrans d'administration du tableau ci-dessus rendent 200 avec leur contenu réel,
ce qui **prouve** la session. *Dit ici pour qu'un successeur ne conclue pas d'un 401 que la mesure était
invalide.*

---

## 4. La forme du correctif — décidée, et les options écartées

**Décision : trois rappels, un fichier, aucune donnée de compte lue.**

| # | Crochet | Priorité | Rappel | Ce qu'il fait |
|---|---|---|---|---|
| 1 | `rest_endpoints` | 10, 1 arg | `retirer_les_routes_d_identite` | `unset()` de **deux clés littérales** |
| 2 | `the_author` | 10, 1 arg | `substituer_le_nom_d_auteur` | rend `get_bloginfo( 'name' )`, **sans regarder l'argument** |
| 3 | `oembed_response_data` | 10, 1 arg | `substituer_l_auteur_oembed` | écrase `author_name` et `author_url` |

### 4.1 REST — retrait INCONDITIONNEL, et c'est l'arbitrage principal de l'issue

**Aucune capacité, aucune session, aucune garde de contexte.**

**L'argument décisif est le corps de la réponse, pas le code.** C'est le cœur qui répond `rest_no_route`
(F4), donc la réponse est **indiscernable à l'octet et au md5** de n'importe quelle route absente :
`404 · 183 · 4015aeea866cc65662e69632c8038bef`. **Toute option qui fabriquerait son propre `WP_Error`
rendrait un corps différent de celui d'une route absente, c'est-à-dire un oracle neuf** — « cette route
a été retirée » ne se lit pas comme « cette route n'existe pas ». *On ne remplace pas un oracle par un
oracle plus discret.* C'est la barre que #49 §7 a posée avec ses `md5sum`, et elle s'applique ici.

**L'argument qui tue la condition de capacité est écrit dans le dépôt, pas déduit.**
`query/page-protegee/bootstrap.php:156-161` refuse `is_user_logged_in()` avec ce motif, verbatim :

> « le BRIEF §8 est inconditionnel, **un index rendu conditionnel serait empoisonné en cache dès la
> première mise en cache** »

**`/wp-json/` EST un index**, et F2 établit qu'il est bâti par **le même `get_routes()`** que le
dispatch. Une table de routes conditionnée à la capacité produirait donc **un index dont le contenu
varie selon le demandeur, à URL identique, sans `Vary`** : le premier cache — serveur frontal de
l'hébergement mutualisé, cache d'objet, proxy — servirait à un anonyme l'index d'une session, ou
l'inverse. **La panne serait muette et intermittente**, la pire famille du dépôt.

**Second argument, et il porte sur ce que nous ne savons pas** : F2 atteste un appel de `get_routes()`
en `:1527` **dont l'appelant n'a pas été identifié**. Sous un retrait inconditionnel, **ce trou n'a
aucune importance**. Sous une condition de capacité, il faudrait savoir dans quel contexte
d'utilisateur il court — **une mesure de plus sur une prémisse de contexte, exactement la famille qui a
cassé le lot 20.** *Le retrait inconditionnel est la seule forme dont la justesse ne dépend d'aucun fait
que nous n'avons pas.*

**Ce que j'abandonne en le retenant, dit sans l'adoucir :**
- **Le panneau « Auteur » des écrans d'édition de Pages et d'Articles perd sa liste de comptes.** Les
  **trois types métier ne sont pas concernés** : `content/portee/bootstrap.php:53` et
  `content/chien/bootstrap.php:70` déclarent `'supports' => array( 'title', 'editor', 'revisions',
  'thumbnail' )` — **pas `author`** — et `content/resultat/bootstrap.php:77` déclare
  `'supports' => false`. L'éleveuse est **Éditrice**, seule rédactrice, et ne réassigne jamais un auteur.
- **Toute la route, tous verbes** : `POST /wp/v2/users`, `PUT`/`DELETE` sur `/wp/v2/users/<id>`
  disparaissent aussi. **L'inscription est fermée** (relevé : `?action=register` → 302
  `registration=disabled`), et `users.php`, `profile.php`, `user-edit.php` sont des écrans PHP
  classiques — `profile.php` est **mesuré à 200 · 125 826** en session (§3.4) et le sera de nouveau
  après (§11, contrôle S6).

> **BRANCHE DE FALSIFICATION, ÉCRITE D'AVANCE.** Si **M-A** (§11, sonde S1) montre que l'éditeur de
> blocs d'une Page **tombe** — notice d'erreur, enregistrement bloqué — et non qu'il perd seulement une
> liste, alors **et alors seulement** la forme de repli est `current_user_can( 'edit_posts' )`, **avec
> inscription au contrat de tout ce que ce repli abandonne** (§4.1, empoisonnement d'index) **et
> ouverture d'une mesure sur `:1527`**. Ce n'est pas une hésitation : c'est la branche écrite d'avance,
> dans la forme de #49 §7. **Le contrat ne gèle pas une prémisse de contexte non mesurée.**

### 4.2 Correspondance LITTÉRALE — interdit gelé

```
RETIRÉES     : /wp/v2/users
               /wp/v2/users/(?P<id>[\d]+)
PRÉSERVÉES   : /wp/v2/users/me
               /wp/v2/users/(?P<user_id>(?:[\d]+|me))/application-passwords
               /wp/v2/users/(?P<user_id>(?:[\d]+|me))/application-passwords/introspect
               /wp/v2/users/(?P<user_id>(?:[\d]+|me))/application-passwords/(?P<uuid>[\w\-]+)
```

**Interdits, à graver dans le fichier ET dans ce contrat** : `strpos`, `str_starts_with`,
`str_contains`, `preg_*`, `array_filter` sur motif, toute boucle sur `array_keys( $endpoints )`.
**Deux `unset()` sur deux clés littérales, et rien d'autre.**

> **Une correspondance par préfixe détruirait quatre routes sur six**, dont `/users/me` que le cœur
> laisse ouverte à `__return_true` (F6). **Et la panne serait invisible en anonyme**, puisque `/me` rend
> déjà 401 sans session : *elle ne se verrait qu'en session, c'est-à-dire seulement chez elle.*

`unset()` sur une clé absente est un non-opérant **sans notice** : **aucun `isset` préalable**, aucune
garde de forme. **Le tableau reçu est rendu, amputé — jamais remplacé.** Interdit repris de #49 §15
pour un motif d'un cran plus grave : ici, remplacer le tableau **détruirait toutes les routes du site**.

### 4.3 Les deux gardes de contexte envisagées pour le rappel 1, et REFUSÉES — chacune avec SON motif

**`is_admin()` — INTERDIT sur ce rappel, et le motif est neuf.** `is_admin()` vaut **faux** sur
`/wp-json/` — doctrine du dépôt, `query/page-protegee/bootstrap.php:147-148`. Mais le vrai danger est
ailleurs : le préchargement de l'éditeur de blocs court **en administration**, où `is_admin()` vaut
**vrai**, tandis que les requêtes XHR ultérieures du même écran courent sur `/wp-json/`, où il vaut
**faux**. Une garde `is_admin()` livrerait donc **une administration incohérente** : routes présentes au
préchargement, absentes ensuite. *Une garde qui rassure sans couvrir — et qui, en plus, ment à moitié.*

**`defined( 'REST_REQUEST' )` — INTERDIT ici aussi, mais l'interdit de #49 NE SE TRANSPORTE PAS : il se
REMPLACE.** Le motif de #49 lui est propre et il est écrit : son filtre `request` court **avant** que
`rest_api_loaded()` ne définisse la constante. **Ce motif tombe ici** — `rest_endpoints` court dans
`get_routes()`, donc **DÉDUCTION (§14)** : la constante serait déjà définie, le test toujours vrai, donc
inutile — *une garde contre un cas impossible est une garde qui rassure sans couvrir, famille T92*
(#49 arbitrage 10).

> **Il faut écrire le motif neuf plutôt que de recopier l'ancien**, sinon un successeur recopiera un
> raisonnement qui ne vaut plus. **Même verdict, motif différent.**

**Conclusion à graver : le rappel 1 n'a AUCUNE garde, et c'est une propriété, pas un oubli.** Le
commentaire doit dire lesquelles ont été envisagées et pourquoi chacune est refusée.

### 4.4 L'index `/wp-json/` — effet VOULU, dit comme tel

Les deux clés disparaissent aussi de l'index (F2, `get_routes()` commun). **Ce n'est pas un effet de
bord : c'est la cohérence.** Laisser les routes annoncées dans l'index pendant que le dispatch rend 404
fabriquerait exactement l'oracle que le §4.1 refuse — *« annoncée mais absente » ne se lit que d'une
façon.*

**Conséquence chiffrée** : l'index change de taille (**249 889 octets avant**). **L'identité à l'octet
ne lui est donc pas exigée** ; la propriété exigée est au §7.

### 4.5 Les flux — `the_author`, garde `is_admin()`, valeur `get_bloginfo( 'name' )`

**Un seul filtre couvre les trois gabarits** (F7, F8, F9, F11). **Aucun gabarit du cœur n'est recopié**,
et le recopier serait rejeté d'emblée : ce serait « on modifiera le fichier » déguisé en architecture,
et un fichier qui **se périme en silence** à chaque montée de version — la dette T114 créée
volontairement, dans sa forme la plus coûteuse.

**La valeur : `get_bloginfo( 'name' )`.** Cinq motifs, dont le dernier est le plus fort :
1. **Déjà publique** — c'est ce que D1 mesure pour `author_name` sur l'accueil.
2. **Aucune chaîne en dur composée par nous.**
3. **Pilotée par un réglage que l'éleveuse ouvre** (Réglages › Général › Titre du site).
4. **Aucun compte n'est lu** — le rappel **jette la valeur qu'il reçoit sans jamais la regarder**.
   *C'est la propriété de #49 conservée : la justesse ne dépend d'aucun état de la base des comptes.*
5. **C'est le défaut du cœur lui-même pour ce champ-là** (F12) : on n'invente pas une valeur, **on
   emprunte la sienne** — exactement comme #49 a emprunté `'404'`.

> **Nuance due à M-K, écrite plutôt que lissée** : le titre du canal, lui, passe par `wp_title_rss()`
> et non par `get_bloginfo( 'name' )` (F20). **L'argument « c'est la valeur du canal » ne tient donc pas
> tel quel** ; c'est l'argument 5 — le défaut du cœur **pour ce champ-là** — qui porte la décision.
> *Le plan proposait l'argument 1 sous une forme qui ne résiste pas à la mesure ; il est corrigé ici.*

**Interdits reconduits** : aucune chaîne littérale composée par nous (nom de personne, affixe,
pseudonyme) · **aucune table `identifiant → pseudonyme`** — *une liste qui ment le jour où un compte est
créé*, condamnée par #50 §5, #52 et #49 arbitrage 1.

**La garde `is_admin()` est OBLIGATOIRE sur ce rappel, et c'est mesuré** (F15 + §3.4) : la colonne
« Auteur » des écrans de liste passe par `the_author`. Sans elle, les écrans **Pages** et **Articles**
afficheraient « Berger Hollandais du Mont Brabant » comme auteur de tout. **Un écran qui a l'air de
marcher et qui ment sur ce qu'il montre est pire qu'un écran cassé** — le mode de panne de l'amendement
HIGH de #49, **cette fois vu avant d'être livré.**

**`is_feed()` est REFUSÉ. Trois motifs — et il y en avait quatre au plan : M-D en a tué un, et ce
contrat le dit plutôt que de garder le compte flatteur.**
1. **La propriété livrée serait amputée.** Avec `is_feed()` on livre « ce site ne publie pas un nom de
   compte **dans un flux** » ; sans, « ce site ne publie **jamais** un nom de compte à un visiteur ».
   La première est **une demi-fermeture par construction**.
2. **Il n'achète rien aujourd'hui** : le thème appelle `the_author` **zéro fois** — revérifié sur le
   disque le 2026-09-08.
3. **Il coûte demain** : le jour où un gabarit écrirait `the_author()`, la version gardée fuirait **en
   silence** ; la version non gardée **échoue du bon côté**.
4. ~~Le gabarit d'embarquement `/…/embed/` est un candidat vivant pour l'autre moitié.~~
   **RETIRÉ : M-D l'a mesuré et il ne publie aucune identité de compte** (les deux `admin` sont des noms
   de classe CSS). *Un motif qui tombe se raye, il ne se garde pas parce que la conclusion tient
   toujours.*

**Aucune interaction avec `archives-d-auteur.php` de #49** — regardée, pas supposée : son rappel ne mord
que si une clé de `CLES_D_AUTEUR` est présente ; le nôtre **ne lit aucune variable de requête** ;
`request` court à l'analyse de la requête et `the_author` au rendu. **Aucune concurrence de priorité,
aucun ordre à imposer entre les deux fichiers.**

### 4.6 L'oEmbed — substituer, ne pas retirer

**Décision : écraser `author_name` et `author_url`, ne retirer aucune clé.**

F12 établit que le cœur fait déjà du **nom du site** et de **`get_home_url()`** les valeurs par défaut
de ces deux champs. **Substituer, c'est donc rétablir le défaut du cœur** — et la propriété livrée
devient remarquablement mesurable : après correctif, l'oEmbed d'un contenu singulier publie **exactement
les deux mêmes valeurs** que celui de l'accueil (D1), **qui est la branche par défaut du cœur,
inchangée.**

Retirer les clés produirait au contraire **une forme qu'aucun défaut du cœur ne produit** — un document
oEmbed sans champ d'auteur, valide au sens de la spécification mais **signant qu'on a retiré quelque
chose**. *Même raisonnement qu'au §4.1, à un autre étage.*

**Les expressions exactes, et pas leurs équivalentes** (M-C, F12) :
`author_name` → **`get_bloginfo( 'name' )`** · `author_url` → **`get_home_url()`**.
*Le contrôle qui le prouve est bâti sur la comparaison à D1, pas sur l'expression : on ne prouve pas
qu'on a écrit la bonne fonction, on prouve qu'on rend la bonne valeur.*

**Aucune garde de contexte sur ce rappel**, et c'est délibéré : il filtre **la donnée**, pas le
contexte, et il est juste partout où le cœur assemble une réponse oEmbed. **F14 établit que la forme
XML est assemblée après ce filtre** : un seul rappel couvre `format=json` et `format=xml` — **mesuré par
M-J, pas déduit.**

**Fait à ne pas confondre avec une fermeture** : le thème retire déjà `wp_oembed_add_discovery_links`,
donc l'oEmbed n'est **annoncé nulle part** — **mais il reste joignable**, et D2/D3 le prouvent.
*Retirer l'annonce n'a jamais fermé une adresse : c'est exactement la leçon du troisième acte daté de
`plan-du-site.php`.* Et l'asymétrie mérite d'être écrite : **`feed_links` n'est PAS retiré**, donc les
flux sont **désignés depuis chaque page** — la surface la plus exposée des trois est celle qui est
annoncée.

### 4.7 Les options écartées — fermées, non rouvrables sans amendement daté

| Option | Décision | Motif |
|---|---|---|
| **A — `rest_user_query` + `rest_prepare_user`** (caviarder les objets rendus) | **Écartée, et c'est la première idée de tout le monde** | **Elle ne ferme pas l'oracle.** `rest_prepare_user` ne s'applique qu'à un objet **qu'on a décidé de rendre** : `/users/1` resterait 200 (corps vide) et `/users/3` 404. **La différence de code de statut survit intacte, et c'est ELLE l'oracle**, pas le contenu du corps. On aurait retiré le nom civil **en conservant l'énumération** — la moitié la moins dangereuse livrée comme un tout |
| **B — `rest_authentication_errors`** | **Écartée, trois motifs** | (1) **Mauvaise maille** : le filtre court une fois par requête REST, **pour toute l'API** ; le borner exige de lire la route, **qui s'écrit sous deux formes** (`/wp-json/…` et `?rest_route=…`) — *c'est le piège `?author=` contre `/author/` de #49, réintroduit ailleurs* ; (2) **rayon d'explosion = l'API entière** pour deux routes ; (3) **elle rend un corps que nous fabriquons**, donc distinguable d'une route absente : *elle rouvre un oracle de second ordre en fermant le premier* |
| **C — `rest_{$post_type}_collection_params`** | **Écartée, mauvaise maille** | Elle gouverne les **paramètres acceptés** d'une collection de contenus. Elle ne peut ni retirer `/wp/v2/users`, ni changer le 200/404 de `/wp/v2/users/{id}`. **Hors sujet**, et l'y faire entrer mélangerait #56 à une autre question |
| **D — retirer `dc:creator` en remplaçant le gabarit de flux** (`do_feed_rss2`) | **Écartée** | **Il n'existe aucun filtre du cœur sur le champ lui-même**, mais **il en existe un sur la valeur** (F11). Remplacer le gabarit imposerait de **recopier un fichier du cœur** qui se périmerait **en silence** à chaque montée de version — *« on modifiera le fichier » déguisé en architecture*, et la dette T114 créée volontairement |
| **E — renommer le `display_name` du compte `admin`** | **Écartée, et c'est l'option qui ferme le plus pour le moins cher** | Elle fermerait `dc:creator`, l'oEmbed **et** une partie de la REST **sans une ligne de code**. Mais c'est **une écriture en base sur un compte**, donc un geste d'exploitation qui **ne survit pas à une base neuve** et que **rien dans le dépôt ne rejouerait en production**. Son mode de panne n'est pas « elle cesse de mordre » mais **« elle ne mord jamais là où ça compte »**. Hors empreinte, hors issue. **Reconduction exacte de l'option D de #49, arbitrage 7** — et **le signalement du §18.2 en découle** |
| **F — éteindre les flux** (retirer `feed_links` + `do_feed`) | **Écartée** | Elle fermerait `dc:creator`, `/feed/`, `/feed/atom/` et `/portees/feed/` **d'un seul geste, sans un filtre**. Mais c'est **une décision produit adossée à une question ouverte du brief** (§15 Q5, « souhaite-t-elle une rubrique actualités ? »), elle toucherait `themes/mtb/functions.php` — **hors empreinte** — et **elle ne se prend pas dans une issue de dette `seo`**. La substitution **ne préempte rien** |
| **G — ne rien faire, solder T113 par écrit** | **Écartée** | Position défendable : le site n'est pas en ligne. **Ce qui l'emporte** : `/wp-json/wp/v2/users` publie **le nom civil de l'éleveuse** en une requête sans cookie, la dette est **déjà ouverte, nommée, chiffrée et routée**, le remède tient en trois rappels sans état, et *« on le fera quand le site sera en ligne » est la phrase qui ne se réalise jamais* (#50, arbitrage 2) |

---

## 5. `wp-login.php` (R6) — MESURÉ, NON CORRIGÉ, et le motif est mesuré lui aussi

**La tâche 3 de l'issue est tenue sur sa première moitié — « mesurer » — et sa seconde moitié — « si
oui, l'uniformiser » — est explicitement NON livrée. Ce n'est pas un oubli, et ce n'est pas une
réduction discrète : c'est une décision motivée, et le lead orchestrateur peut la renverser.**

### 5.1 Le relevé — il n'avait jamais été joué par personne (#49 §14.2)

| Cas | Code | Taille | Message |
|---|---|---|---|
| identifiant **connu** (`fabienne`) | 200 | 9 372 | « **Erreur :** ce mot de passe ne correspond pas à l'identifiant **fabienne**. *Mot de passe oublié ?* » |
| identifiant **connu** (`admin`) | 200 | — | « … ne correspond pas à l'identifiant **admin**. » |
| identifiant **inconnu** | 200 | 9 351 | « **Erreur :** l'identifiant **zzz-inexistant-zzz** n'est pas inscrit sur ce site. Si vous doutez de votre identifiant, essayez plutôt votre adresse e-mail. » |

**L'oracle est réel.** Codes du cœur : `incorrect_password` contre `invalid_username` (F16).

### 5.2 Les TROIS discriminants que l'uniformisation du message ne couvrirait PAS

1. **Le champ `log` est repeuplé pour un identifiant connu, vidé pour un inconnu** — `value="fabienne"`
   contre `value=""`. **Piloté par le CODE d'erreur, pas par le message** (F17).
2. **Le script de focus vise `user_pass` (connu) ou `user_login` (inconnu)** — même cause.
3. **`?action=lostpassword` : `302` · 0 octet vers `?checkemail=confirm` (connu) contre `200` · 3 603 +
   « il n'y a pas de compte avec cet identifiant ou cette adresse e-mail » (inconnu).**
   **C'est le signal le plus fort des quatre, et il ne passe par aucun message.**

### 5.3 Pourquoi le quatrième n'a AUCUNE couture dans le cœur — relevé, pas supposé

F18 : `apply_filters( 'lostpassword_errors', … )` court en `user.php:3271`, **AVANT** la garde
`! $user_data` de `:3277-3279` qui **rajoute `invalidcombo` et rend la main**. **Vider les erreurs dans
ce filtre ne fait donc pas passer un identifiant inconnu.**
F19 : **il n'existe aucun filtre entre le retour de `retrieve_password()` (`wp-login.php:839`) et la
décision de redirection (`:842`).**

> **Fermer ce discriminant imposerait de reprendre `login_form_lostpassword` et de réimplémenter un flux
> du cœur** — un chemin recopié qui **se périme en silence à chaque montée de version**, c'est-à-dire la
> dette **T114 (#57) créée volontairement, dans sa forme la plus coûteuse**.

### 5.4 La décision, et ce qui la fonde

**Ne fermer que le message serait une demi-fermeture rapportée comme entière** — la faute que #24 §16
institue comme interdite, et que ce dépôt a déjà payée deux fois.

**Trois motifs de plus, dans l'ordre de leur poids :**
1. **`wp-login.php` est hors de l'empreinte du §1.** Même un demi-correctif serait hors périmètre.
2. **C'est le seul volet du sujet qui change quelque chose que l'éleveuse LIT**, et au pire moment : le
   jour où elle n'arrive pas à entrer chez elle. **`docs/guide/` n'a aucune fiche de connexion** — 29
   fiches vérifiées — donc **une fiche est due au titre de D3**, et **`docs/guide/**` est l'empreinte de
   la chaîne #55, qui tourne en parallèle.** *#56 ne peut pas livrer la documentation que son propre
   correctif rendrait obligatoire.*
3. **C'est un arbitrage produit, pas technique** : uniformiser retire à l'éleveuse l'indication « votre
   identifiant est bon, c'est le mot de passe ». *Un message conditionnel honnête existe et ne ment pas
   — « si un compte correspond, un lien vient d'être envoyé » — mais le choisir engage l'autonomie de
   l'éditrice contre la vie privée, deux règles transverses du brief.* **Ce n'est pas à une issue `seo`
   de le trancher.**

**Issue neuve à ouvrir, labels `prive`/`infra`**, avec **sa fiche de guide** et **les quatre
discriminants du §5.2 comme périmètre explicite** — pas seulement le message.

### 5.5 Deux surfaces voisines mesurées, et PROPRES — dites plutôt que tues

- **XML-RPC** : `wp.getUsersBlogs` rend **la même réponse** pour un identifiant connu et inconnu —
  `<int>403</int> <string>Identifiant ou mot de passe incorrect.</string>`. **Aucun oracle.**
  *Mesuré, pas déduit — et c'était le frère jumeau redouté de R6.*
- **L'inscription est fermée** : `?action=register` → **302** vers `?registration=disabled`.

---

## 6. Les gardes, rappel par rappel — ordre imposé, et le mode de panne de chacune

**Aucune garde ne se réordonne, aucune ne s'ajoute, aucune ne se retire sans rouvrir ce contrat.**

### Rappel 1 — `retirer_les_routes_d_identite( array $routes ): array`, `rest_endpoints` 10, 1 arg

| # | Geste | **Mode de panne si on s'en écarte** |
|---|---|---|
| 1 | **Aucune garde.** Le rappel court toujours | Une garde `is_admin()` livrerait **une administration incohérente** (§4.3) ; une garde de capacité **empoisonnerait l'index en cache** (§4.1) |
| 2 | **`unset()` de DEUX clés littérales**, dans cet ordre : `/wp/v2/users`, puis `/wp/v2/users/(?P<id>[\d]+)` | **Une correspondance par préfixe détruirait quatre routes sur six**, dont `/users/me` et les trois routes de mots de passe d'application — **et la panne serait invisible en anonyme** |
| 3 | **Le tableau reçu est rendu, amputé — JAMAIS remplacé** | Un `return array( … )` **détruirait toutes les routes du site**. #49 §15 à un cran plus grave |

### Rappel 2 — `substituer_le_nom_d_auteur( string $nom ): string`, `the_author` 10, 1 arg

| # | Garde | **Mode de panne si on la retire** |
|---|---|---|
| 1 | **Sortir si `is_admin()`** — **la garde la plus grave du fichier** | **Les écrans Pages et Articles afficheraient « Berger Hollandais du Mont Brabant » comme auteur de tout**, en silence (F15 + §3.4). *Un écran qui ment sans rien dire : le mode de panne exact de l'amendement HIGH de #49* |
| 2 | **Rendre `get_bloginfo( 'name' )` sans jamais lire `$nom`** | Lire l'argument rouvrirait une dépendance à l'état de la base des comptes — **la propriété de #49 perdue pour rien** |
| — | **Pas de `is_feed()`** — trois motifs au §4.5 | Le poser livrerait « pas de nom de compte **dans un flux** » au lieu de « **jamais** un nom de compte » — **une demi-fermeture par construction** |
| — | **Pas d'échappement de notre main** | `the_author` alimente **trois gabarits aux règles d'échappement différentes** — CDATA en RSS2 et RDF, texte brut en Atom (F7, F8, F9). **Aucun échappement n'est juste pour les trois à la fois**, et en poser un casserait au moins l'un d'eux. **Résidu R9, hérité du cœur** (§9) |

### Rappel 3 — `substituer_l_auteur_oembed( array $donnees ): array`, `oembed_response_data` 10, 1 arg

| # | Geste | **Mode de panne si on s'en écarte** |
|---|---|---|
| 1 | **Aucune garde**, et c'est délibéré | Le rappel filtre **la donnée**, pas le contexte : il est juste partout où le cœur assemble une réponse oEmbed — **JSON et XML** (F14, M-J) |
| 2 | **Écraser `author_name` avec `get_bloginfo( 'name' )` et `author_url` avec `get_home_url()`** — **ces expressions-là** (M-C, F12) | Une expression équivalente-mais-autre rendrait une valeur **différente de celle de l'accueil**, et le contrôle du §7.3 — l'égalité champ à champ contre D1 — **ne passerait pas** |
| 3 | **Écraser sans lire**, et ne retirer aucune clé | Retirer les clés produirait **une forme qu'aucun défaut du cœur ne produit** — *un oracle plus discret à la place d'un oracle* (§4.6) |

**Le rappel 1 déclare `array` en entrée et en sortie ; les rappels 2 et 3 aussi. `declare(strict_types=1)`
est en vigueur : aucun des trois ne lit une valeur d'entrée, donc aucun `TypeError` n'est atteignable
par une valeur de requête.**

---

## 7. La cible chiffrée — la tâche 4 rendue mesurable

### 7.1 REST — l'indiscernabilité EST la propriété livrée

> **Les six adresses ci-dessous rendent toutes le même triplet — `404 · application/json · 183` — avec
> un corps dont le `md5sum` est identique à celui de `/wp-json/wp/v2/nexiste-pas-du-tout` :**
>
> `/wp-json/wp/v2/users` · `/wp-json/wp/v2/users/1` · `/wp-json/wp/v2/users/2` ·
> `/wp-json/wp/v2/users/3` · `/wp-json/wp/v2/users?search=fab` · `/?rest_route=/wp/v2/users`
>
> **`/wp-json/wp/v2/users/999` et `/?rest_route=/wp/v2/users/1` rendent le même triplet.**

**Trois précisions qui font la différence entre une cible et un vœu :**
1. **`183` ne se gèle pas par recopie.** C'est la taille de `rest_no_route` **relevée dans le MÊME
   relevé**, et l'égalité se prouve **par comparaison, jamais par constante**. *(Au 2026-09-08 elle vaut
   183 et le md5 vaut `4015aeea866cc65662e69632c8038bef` ; ces chiffres se rejouent, ils ne se
   recopient pas.)*
2. **Le `md5sum` du corps, pas seulement la taille.** *Deux réponses de même taille peuvent différer
   d'un mot — et le mot en question serait un slug.*
3. **`/users/3` est dans la cible, et ce n'est pas du zèle** : s'il ne rendait pas le même triplet que
   `/users/1`, **l'oracle survivrait sous une autre forme** — « ce compte existe, celui-là non ».

**Les quatre routes préservées** rendent, **inchangées à l'octet** : `/users/me` → `401 · 117` ·
`/users/1/application-passwords` → `501 · 138`.

**L'index `/wp-json/`** : l'identité à l'octet **n'est pas exigée** (§4.4). La propriété exigée est
`grep -o '"/wp/v2/users[^"]*"' | sort -u` → **exactement les quatre clés préservées, et aucune autre.**

**`?_embed=1` — M-B, branche d'arrêt écrite d'avance.** Cible : `pages?_embed=1` et `posts?_embed=1`
**ne portent plus ni `"slug":"admin"`, ni `"name":"admin"`, ni `/author/admin/`.** *La forme exacte du
résidu — clé `author` absente, ou objet d'erreur — n'est pas connue et n'est pas exigée.* **Si les
chaînes survivent, c'est une branche d'arrêt et une remontée au lead**, pas un arrondi.

### 7.2 Flux — l'identité de md5 ne s'applique PAS, et il faut le dire

Le corps **change** de contenu : c'est l'objet. **Ce qui est exigé, adresse par adresse :**

| Adresse | Exigé |
|---|---|
| `/feed/` | `200 · application/rss+xml` · **`<dc:creator><![CDATA[Berger Hollandais du Mont Brabant]]></dc:creator>`** · **zéro occurrence de `admin`** |
| `/feed/rdf/` | `200 · application/rdf+xml` · idem · **zéro occurrence de `admin`** |
| `/feed/atom/` | `200 · application/atom+xml` · **`<name>Berger Hollandais du Mont Brabant</name>`** · **zéro occurrence de `admin`** |
| `/portees/feed/` | `200 · application/rss+xml` · **zéro occurrence de `admin`** |
| `/comments/feed/` | **`200 · 1 690`, IDENTIQUE À L'OCTET** — *contrôle de non-débordement : `get_comment_author_rss()` est une autre fonction (F10), notre rappel ne doit pas y toucher* |

**La valeur attendue n'est pas recopiée en constante** : elle est **le titre du site relevé dans le même
relevé**. Si le titre change, la cible change avec lui — c'est la propriété.

### 7.3 oEmbed — l'égalité se mesure CHAMP À CHAMP contre l'accueil

> **`author_name` et `author_url` d'un contenu singulier deviennent EXACTEMENT ceux de l'accueil (D1),
> qui est la branche par défaut du cœur et reste inchangé.**

| Adresse | Exigé |
|---|---|
| `oembed/1.0/embed?url=<accueil>` | **inchangé à l'octet** — *c'est la référence, et elle ne doit pas bouger* |
| `oembed/1.0/embed?url=<une portée>` | `author_name` = `Berger Hollandais du Mont Brabant`, `author_url` = `http://localhost:3005` |
| `oembed/1.0/embed?url=<un chien>` | idem |
| la même, **`&format=xml`** | `<author_name>` et `<author_url>` idem — **M-J l'exige, F14 l'explique** |

**Aucune des trois ne porte plus `admin` ni `/author/admin/`.**

### 7.4 Non-régression — la ligne de base rejouée

`/nexiste-pas-du-tout/` `404 · 18284 · d9f9bae7…` · `/` `25491` · `/portees/` `31819` · `/travail/`
`35411` · `/author/admin/` et `/?author=1` `404 · 18284 · d9f9bae7…` (**#49**) ·
`/wp-sitemap-users-1.xml` `404 · 18284` (**#50**) · `wp mtb verifier-redirections` **code 0** ·
**`/wp-json/wp/v2/posts?author=1` → `200 · 1847`** *(il DOIT rester fonctionnel : c'est le contrôle qui
prouve que la garde REST de #49 mord encore)* · **`debug.log` 0 octet avant et après.**

**Et les sept écrans authentifiés du §3.4, identiques à l'octet** — sauf ceux que M-A montrerait
légitimement changés, auquel cas **la différence s'écrit et s'explique, elle ne s'arrondit pas.**

---

## 8. États spéciaux et cas limites — comportement imposé

| État / cas | Détecté par | Comportement imposé |
|---|---|---|
| `route_d_identite_retiree` | — | **`404 rest_no_route`, corps du cœur**, identique à toute route absente. **Nous ne posons aucun statut** |
| `route_preservee` | correspondance **littérale**, jamais préfixe | `/users/me` et les **trois** routes de mots de passe d'application : **inchangées à l'octet** |
| `index_rest` | même `get_routes()` (F2) | Les deux clés disparaissent **aussi** de `/wp-json/`. **Effet voulu** (§4.4) |
| `verbe_d_ecriture` (`POST`, `PUT`, `DELETE`) | — | **404 aussi.** Le retrait est à la maille de la route. **Conséquence assumée** : plus de création ni de modification de compte **par la REST**. L'inscription est fermée, les écrans classiques sont intacts (contrôles S5, S6) |
| `oembed_de_l_accueil` | branche par défaut du cœur | **Inchangé à l'octet.** Notre substitution rend **la valeur même du défaut** — c'est la référence D1 |
| `titre_du_site_vide` | `get_bloginfo( 'name' )` rend `''` | `dc:creator` vide, `author_name` vide. **Aucun compte n'est publié pour autant** : le mode dégradé est **muet, pas fuyant** |
| `titre_du_site_avec_&_ou_<` | — | **Flux Atom potentiellement malformé — résidu R9**, hérité du cœur, qui imprime déjà le `display_name` brut au même endroit (F9). **Aucun échappement n'est juste pour les trois gabarits à la fois** (§6) |
| `administration` | `is_admin()` | `the_author` **inchangé** : les colonnes « Auteur » disent vrai. `rest_endpoints` et `oembed_response_data` **ne sont PAS gardés** — délibérément (§4.3, §4.6) |
| `flux_de_commentaires` | `get_comment_author_rss()` (F10) | **Inchangé à l'octet.** Hors périmètre, mesuré, déclaré |
| `requete_d_auteur` | #49 | **Aucune interaction** : nos trois rappels ne lisent aucune variable de requête (§4.5) |
| `un_tiers_refiltre_apres_nous` | — | Notre filtre court à **chaque** `get_routes()` : la route serait re-retirée — **sauf** si un tiers filtrait `rest_endpoints` après nous. **Résidu muet, nommé** |
| `extension_desactivee` / dossier renommé `_indexation-heritee` | — | **SIX effets tombent ENSEMBLE** : `users` revient au plan du site · le 404 du sous-plan tombe · les archives d'auteur rouvrent (200 / 301) · la route REST republie **les deux comptes et le nom civil** · les flux republient `admin` · l'oEmbed republie `admin` **et `.../author/admin/`, qui redevient 200 au même instant**. **Dégradation cohérente, aucun état mort à nettoyer** — et **un piège matériel aggravé**, à écrire au motif 2 de `bootstrap.php` |
| `blog_public = 0` | — | **Sans effet** : les trois rappels ne lisent ni option, ni registre, ni base |

Les quatre états gelés au §9 du contrat #1 — `aucune_portee`, `donnee_absente`, `parent_hors_elevage`,
`page_protegee` — **ne sont ni touchés, ni étendus, ni réinterprétés.**

### Le mode de panne silencieux, écrit sans le maquiller

> **Si l'un des trois rappels cesse de mordre, RIEN ne le dit. Rien.**

| Cause | Effet | Ce qui le dirait |
|---|---|---|
| Une ligne `add_filter` disparaît dans une reprise | La surface correspondante republie `admin` — **ou les deux comptes et le nom civil** | **Rien** |
| Le cœur renomme une clé de route | Le rappel 1 ne mord plus | **Rien** |
| Le cœur cesse d'appeler `the_author()` dans un gabarit de flux | Le rappel 2 ne mord plus | **Rien** |
| Un tiers filtre `rest_endpoints` après nous et réenregistre la route | La route rouvre | **Rien** |
| Dossier renommé `_indexation-heritee` | **Les six effets tombent** | **Le protocole du §11, si on le joue** |

**Ce module n'a pas de commande WP-CLI et il n'en aura pas** — motif 3 gelé en tête de son
`bootstrap.php` (« témoins d'échec disjoints »). **Le seul témoin est le protocole du §11, joué en
recette.** *Reconduction explicite de #50 §8 et #49 §8, et aucune sonde supplémentaire n'est proposée :
la seule qui aurait du sens serait une commande WP-CLI, et le motif 3 l'interdit.*

---

## 9. Résidus déclarés et NON fermés

**Sans ce paragraphe, un rapport dirait « T113 close » quand elle ne le serait qu'en partie.**

| # | Surface | Ce qui reste, **mesuré** | Pourquoi hors périmètre |
|---|---|---|---|
| **R6** | `wp-login.php` | **Quatre discriminants** (§5.2), dont un `302`/`200` **sans couture dans le cœur** (§5.3) | **Hors empreinte**, arbitrage produit, **fiche de guide due dans une empreinte parallèle** (§5.4). **Issue neuve `prive`/`infra`** |
| **R7** | `wp/v2/pages`, `wp/v2/posts` | Le champ **`"author":1`** — **le NUMÉRO du compte** — reste lisible en anonyme, et `_links.author.href` est bâti par le contrôleur, non par la table des routes | **Arbitré acceptable par le lead** : ni slug, ni nom ; et `posts?author=N` est **mesuré comme n'étant pas un oracle d'énumération** (`?author=2` et `?author=999` rendent tous deux `[]`, 2 octets, **même md5**). **Au sens du BRIEF §4, un numéro sans nom n'est pas une donnée personnelle.** Le fermer supposerait `rest_prepare_page`/`rest_prepare_post` — autre surface, autre issue |
| **R8** | `/feed/atom/` | **Un** `<uri>`, dont la valeur est **l'URL du site** — le champ « Site web » du profil du compte 1 | **Mesuré comme inoffensif** (M-G) : ni identifiant, ni slug, ni nom. **Déclenchement nommé** : le jour où un profil y met autre chose, `<uri>` le publierait |
| **R9** | les trois gabarits de flux | Un titre de site contenant `&` ou `<` **rendrait l'Atom malformé** | **Hérité du cœur**, qui imprime déjà le `display_name` brut au même endroit (F9). **Aucun échappement n'est juste pour les trois gabarits à la fois** (§6) |
| **R10** | les trois surfaces | Le jour où l'éleveuse renommerait le site avec son nom civil, les flux et l'oEmbed le publieraient | **C'est son réglage**, sur une valeur **qu'elle publie déjà en titre de page et en pied de page** (BRIEF §7). Écrit plutôt que tu |
| **R11** | `/wp-json/` | L'index **change de taille** ; l'identité à l'octet ne lui est pas exigée | Effet **voulu** (§4.4), propriété exigée au §7.1 |

> **T113 ne se déclare close QUE sur R1, R2, R3 et R4.** **R6 reste ouvert et doit être nommé en regard
> dans tout rapport.** *Une dette soldée à moitié et rapportée comme entière est la faute que #24 §16
> institue comme interdite.*

---

## 10. Frontière, budget, et ce que ce contrat gèle à la place du thème

**Il n'y a pas de côté thème, et ce contrat le dit plutôt que de laisser un gabarit vide.**

- **Fonctions de lecture exposées au thème : AUCUNE.** Ce module n'ajoute ni fonction globale `mtb_*`,
  ni variable de requête, ni règle de réécriture, ni option, ni méta, ni terme, ni type de contenu.
- **Blocs enregistrés : AUCUN.** Section **sans objet**, et elle le dit — précédents `issue-50.md` §9 et
  `issue-49.md` §10.
- **Chaînes fournies par le serveur : AUCUNE.** Aucun libellé, aucune date, aucun nom de discipline.
  *La seule chaîne que ces rappels font sortir est le **titre du site**, que le cœur publie déjà.*
- **Hooks offerts au thème : AUCUN** — pas de `apply_filters( 'mtb_…', … )`, **borne 3**.
- **Le thème n'est pas touché, ne connaît pas ce module, ne l'appelle pas, ne teste pas son existence.**

**Ce que le thème ne doit JAMAIS faire** : appeler `the_author()`, `get_the_author()`,
`get_author_posts_url()` ni aucune de leurs sœurs · consommer `/wp/v2/users` depuis un script du front ·
**et surtout : masquer un nom d'auteur par du CSS**. Si un nom d'auteur apparaissait un jour dans le
thème, la réponse est **arrêt, relevé du fichier et de la ligne, remontée au lead** — **jamais** un
contournement côté extension. *Reconduction de #49 §10.*

### La frontière avec le CŒUR, gelée — chaque point est un relevé du §3

Les vingt faits **F1 à F20** du §3.2 sont la frontière. **Aucun autre fait du cœur n'est gelé ici.**

> **Les numéros de ligne sont épinglés à WordPress 6.9.** `wp-includes/` et `wp-admin/` n'étant pas
> versionnés dans ce dépôt, ils **se périmeront en silence** à la prochaine montée de version. **Résidu
> nommé, non masqué — c'est la dette T114, issue #57, que #56 ALIMENTE et ne solde pas.** *Vingt ancres
> de plus, et il faut le dire plutôt que de laisser #57 les découvrir.*

### Budget

- **D8 — poids ajouté sur les pages servies aux visiteurs : zéro octet.** Aucun CSS, aucun JS, aucune
  police, aucune image, aucune mise en file. **Une nuance chiffrée plutôt que tue** : `/wp-json/`
  **maigrit** (deux routes de moins sur 249 889 octets) et les réponses `users` passent de 672/335 à
  183 — **sur des adresses qu'aucun visiteur du site ne demande.**
- **D6 — zéro requête sortante.** Aucun `wp_remote_*`, zéro cookie, zéro traceur.
- **D10** — aucune extension tierce, aucun page builder.
- **D12** — les trois rappels **ne peuvent pas casser une page du front** : ils ne rendent rien,
  n'impriment rien, ne font aucun `exit`, et **ne courent sur aucune requête de page HTML** sauf le
  rappel 2, qui **sort immédiatement en administration** et dont le thème n'appelle jamais le crochet.

### Sécurité et conventions

**Aucun chemin d'écriture, donc aucun nonce et aucune vérification de capacité ne sont dus** — il n'y a
rien à autoriser, et **`current_user_can()` est délibérément absent** (§4.1). **Aucune valeur d'entrée
n'est lue** : le rappel 1 retire deux clés littérales, le rappel 2 **jette son argument sans le
regarder**, le rappel 3 **écrase deux champs sans les lire**. *Il n'y a donc rien à assainir, et c'est
plus sûr que d'assainir.* **Aucun échappement de notre main** — le motif est au §6. **Aucun `exit`.**
Le rôle Éditrice n'est **ni créé, ni modifié, ni consulté**.

`declare(strict_types=1);` · garde `if ( ! defined( 'ABSPATH' ) ) { exit; }` · namespace
`MTB\Core\Migration\IndexationHeritee` · **français littéral, aucune fonction i18n** (`issue-1.md` §7 —
jamais `__()`, `_e()`, `esc_html__()`) · `array()` et jamais `[]` · conditions de Yoda · tabulations ·
pas de `?>` final · plafond de syntaxe **PHP 8.1** · WordPress Coding Standards. **À l'inclusion de
`bootstrap.php`** (`issue-1.md` §3) : **seuls** `add_action`, `add_filter`, `define`, `require_once` de
ses propres fichiers, déclarations et gardes de sortie anticipée.

---

## 11. Protocole de vérification — rejouable, ordre imposé

Depuis le conteneur `wordpress`, `curl --path-as-is --connect-to "localhost:3005:localhost:80"`, **sans
cookie ni `--user`** sauf pour les sondes S1–S6, **jamais depuis Git Bash de Windows** (transcodage
UTF-8 → Latin-1, piège #24 §16 point 6). Pour WP-CLI sous Git Bash : **`MSYS_NO_PATHCONV=1`**.
**Note d'outillage relevée au lot 20** : Git Bash réécrit tout argument ressemblant à un chemin POSIX
avant `docker.exe`, et les guillemets simples n'y changent rien.
Pile **déjà démarrée** : **jamais** `down`, `--build`, `--force-recreate`, `wp db reset`, ni re-seed.
**`wp rewrite flush` n'est pas requis et ne doit pas être joué** — le correctif ne touche aucune règle.

### Ordre imposé qui rend les mesures attribuables

1. **`php -l`** sur les **deux** fichiers, **avant tout `curl`**.
2. **Relevé AVANT complet** — P1 à P12, plus les sondes S1–S6.
3. **Écrire `identite-des-comptes.php` en entier, SANS l'accrocher** (ni `require_once`, ni
   `add_filter`) → `php -l` → **rejouer : rien ne doit avoir bougé d'un octet.** *Preuve qu'un fichier
   non chargé ne fait rien.*
4. **Ajouter le `require_once` seul**, toujours sans `add_filter` → `php -l` → **rejouer : rien n'a
   bougé.** *Preuve qu'une fonction déclarée et non accrochée ne fait rien.*
5. **Accrocher les trois rappels, UN PAR UN**, avec un relevé entre chacun. *L'accroche seule est
   causale, et trois accroches posées ensemble rendraient une régression non attribuable.*
6. **Relevé APRÈS complet**, sondes authentifiées comprises.
7. Les corrections de prose (§12) dans une **étape séparée**, suivies d'un `php -l` et d'un **rejeu de
   P2/P7** : *zéro ligne exécutable se prouve, il ne s'affirme pas.*

### Contrôles

| # | Contrôle | Attendu |
|---|---|---|
| **P0** | `php -l` sur les deux fichiers | **Avant tout `curl`.** Une erreur de syntaxe est un `E_COMPILE_ERROR` **non rattrapé par le `try/catch` du chargeur** (#1 §12) — site entier par terre |
| **P1** | **Sonde d'existence du module, en PREMIER** : les `<loc>` de `/wp-sitemap.xml` | **5 `<loc>`, aucun `users`.** *Si `users` réapparaît, le module n'est plus chargé et tout le reste se lirait à contresens* |
| **P2** | **Objet 1** : les **huit** adresses REST du §7.1 | Toutes `404 · application/json · 183`, **md5 identique à `/wp-json/wp/v2/nexiste-pas-du-tout` rejoué dans le même relevé** |
| **P3** | Les **quatre routes préservées** | `/users/me` → `401 · 117` · les trois `application-passwords` → `501 · 138`. **Inchangées à l'octet** |
| **P4** | `/wp-json/` : `grep -o '"/wp/v2/users[^"]*"' \| sort -u` | **Exactement les quatre clés préservées** |
| **P5** | **Objet 2** : les cinq flux du §7.2 | Valeurs du §7.2 · **`grep -c admin` → 0** sur les quatre premiers · **`/comments/feed/` identique à l'octet (1 690)** |
| **P6** | **Objet 3** : les quatre adresses oEmbed du §7.3, **`format=xml` comprise** | Égalité **champ à champ** avec D1 · **zéro occurrence de `admin` et de `/author/admin/`** |
| **P7** | **Non-régression front** : `/` `25491` · `/portees/` `31819` · `/travail/` `35411` · `/nexiste-pas-du-tout/` `404 · 18284 · d9f9bae7…` | **Identiques à l'octet** |
| **P8** | **#49 intact** : `/author/admin/`, `/?author=1` · **#50 intact** : `/wp-sitemap-users-1.xml` | `404 · 18284 · d9f9bae7…` |
| **P9** | `/wp-json/wp/v2/posts?author=1` | **`200 · 1847`** — *il DOIT rester fonctionnel : c'est le contrôle qui prouve que la garde REST de #49 mord encore* |
| **P10** | `wp mtb verifier-redirections` | **code 0** |
| **P11** | **`debug.log`** | **0 octet avant, 0 octet après.** *Ligne de base du lot : tout octet écrit est un échec à signaler* |
| **P12** | **Anti-recopie.** `grep -rn "wp/v2/users\|'the_author'\|'rest_endpoints'\|'oembed_response_data'" wp-content/plugins/` | **Chaque littéral apparaît une seule fois EN CODE.** Les occurrences en **commentaire** sont permises et attendues : *le contrôle distingue le code du commentaire, sinon il interdirait d'expliquer ce qu'on fait* |

### Sondes authentifiées — et ce qui est automatisable est dit

| # | Sonde | Attendu | Automatisable ? |
|---|---|---|---|
| **S1** | **M-A — l'éditeur de blocs d'une PAGE, en session** : ouvrir, modifier, **enregistrer** ; observer le panneau « Auteur » | **S'ouvre et enregistre.** Le panneau perd sa liste — **acceptable**. *S'il TOMBE, branche de repli du §4.1* | **OUI, dans un vrai navigateur piloté** — et **elle doit l'être** : c'est la mesure qui décide de la forme du correctif |
| **S2** | `edit.php?post_type=mtb_portee` et l'onglet « Le mien » | **`175 431`, identique à l'octet** — *#49 non régressé* | oui |
| **S3** | **La colonne « Auteur » des écrans Pages et Articles** | **`admin`, inchangé** — *c'est la garde `is_admin()` du rappel 2 qui se prouve ici, par l'observable et sans lire le cœur* | oui |
| **S4** | `upload.php` (**grille**) et `upload.php?mode=list` | `186 608` et `186 838`, identiques à l'octet | oui |
| **S5** | `profile.php` | `125 826`, identique à l'octet — *les mots de passe d'application ne sont pas cassés* | oui |
| **S6** | `edit.php?post_type=page` et `post-new.php?post_type=page` | `161 962` et `760 073` — **ou une différence ÉCRITE et EXPLIQUÉE**, jamais arrondie | oui |

> **S1 n'est pas facultative.** #49 a livré son contrôle P14 **non joué** deux fois de suite, et son
> propre amendement le reconnaît. **Un vrai navigateur est disponible sur ce poste : l'excuse
> d'automatisation ne tient pas.** *Si S1 n'est pas jouée, le rapport doit le dire en toutes lettres et
> ne présenter nulle part la propriété comme vérifiée.*

---

## 12. Les blocs de prose de `bootstrap.php` que #56 rend faux

**On ajoute un acte daté ; on n'ampute jamais un bloc existant.** Format de #49 §16.

1. **Motif 2, l. 29-33** — « **TROIS EFFETS, ET ILS TOMBENT ENSEMBLE** ». **Il y en a SIX après #56**,
   et le comptage explicite devient faux. Le texte corrigé doit nommer les trois nouveaux : la route
   REST republiant **les deux comptes et le nom civil**, les flux republiant `admin`, l'oEmbed
   republiant `admin` **et `/author/admin/` redevenant 200 au même instant**. *Un piège matériel
   aggravé pour qui appliquerait ce renommage de bonne foi.*
2. **L. 105-107** — « Les **TROIS** hooks de front de ce module ». **Il y en a SIX.**
3. **Les surfaces silencieuses** — le fichier en énumère trois (l. 70, 77, 88). **Une QUATRIÈME est
   due** : si l'un des trois rappels de #56 cesse de mordre, une identité de compte reparaît dans un
   document public, **et rien ne le dirait**. *Et la nuance de l'enjeu doit être écrite comme #49 l'a
   fait pour la sienne : ce que #49 protège est l'identifiant de connexion de l'éleveuse ; **ce que #56
   protège est aussi son NOM CIVIL**, publié en une requête sans cookie.*
4. **La borne 1, l. 101-119** — elle se lit « il lit, il RÉPOND, et il peut AMENDER LA REQUÊTE EN
   MÉMOIRE ». **#56 ne l'étend pas** : ses trois rappels **lisent et répondent**, ils n'amendent aucune
   requête et n'écrivent rien. **Un acte daté doit le DIRE** — *une borne qu'on n'étend pas mérite de le
   constater par écrit, sinon le prochain croira l'avoir étendue sans le savoir.*

**Ce qui laisse `bootstrap.php` ouvert à #57 et #58** : les trois `add_filter` de #56 sont **groupés,
précédés d'un seul bloc de commentaire, et placés APRÈS ceux de #49** ; le `require_once` rejoint la
liste existante **sans réordonner les quatre autres** ; **aucun bloc de commentaire existant n'est
réécrit**, seulement augmenté d'actes datés. *Un enregistrement supplémentaire s'ajoute en deux lignes,
sans toucher une ligne de #49 ni de #50.*

---

## 13. Relèves — 2026-09-08

**Pratique en vigueur** : l'amendement vit dans le contrat de son issue, et les contrats gelés ne se
rouvrent pas.

### 13.1 Relève de l'énumération des hooks de front du groupe `migration/` — de CINQ à HUIT

| Module | Hook | Priorité | Rappel |
|---|---|---|---|
| `redirections-301/` | `template_redirect` | 1 | `rediriger` |
| `redirections-301/` | `the_content` | 20 | réparation des douze ancres internes |
| `indexation-heritee/` | `wp_sitemaps_add_provider` | 10 | `ecarter_le_fournisseur_utilisateurs` |
| `indexation-heritee/` | `template_redirect` | 20 | `repondre_404_au_sous_plan_retire` |
| `indexation-heritee/` | `request` | 10 | `neutraliser_la_requete_d_auteur` |
| `indexation-heritee/` | **`rest_endpoints`** | **10** | **`retirer_les_routes_d_identite`** |
| `indexation-heritee/` | **`the_author`** | **10** | **`substituer_le_nom_d_auteur`** |
| `indexation-heritee/` | **`oembed_response_data`** | **10** | **`substituer_l_auteur_oembed`** |

**Huit, et pas neuf** : aucune garde de ceinture n'est écrite. **Cette relève n'ouvre rien** — aucun
groupe nouveau, ni `mtb-core.php` ni `class-loader.php` touchés.

### 13.2 Relève du §9 de `docs/contracts/issue-49.md` — R3 n'était pas inoffensif

> **#49 §9, ligne R3, se lit** : « `/wp-json/oembed/1.0/embed?url=…` → `"author_name":"Berger Hollandais
> du Mont Brabant"` — **le nom du SITE, pas un compte.** *Moins grave que le plan ne le craignait, et
> c'est écrit* ».
>
> **Cette ligne est RELEVÉE, et le §2.3 du présent contrat dit en quoi** : elle est **juste pour la
> seule URL mesurée — l'accueil — et fausse en général**. Sur tout contenu singulier, la branche `:613`
> d'`embed.php` publie le `display_name`, soit `admin`, et `author_url` publie `.../author/admin/`.
> **#56 la ferme.**
>
> *Ce n'est pas un reproche : c'est la démonstration de la règle que #49 s'était lui-même donnée. Une
> conclusion juste adossée à une mesure qui ne la porte pas est une dette qui se paie un lot plus tard —
> et celle-ci s'est payée au lot suivant.*

### 13.3 Relève du contrôle **P11** de `docs/contracts/issue-49.md` §11

P11 exige « exactement deux occurrences » de `'author'` / `'author_name'` dans
`wp-content/plugins/`, **toutes deux dans `CLES_D_AUTEUR`**.

> **#56 ajoute des occurrences de `author_name` — dans `identite-des-comptes.php`, en CODE (le champ
> oEmbed) et en commentaire.** **P11 tel qu'il est écrit échouerait donc, sans qu'aucune régression
> n'ait eu lieu.**
>
> **P11 est relevé ainsi** : « exactement deux occurrences **en code** de `'author'` et `'author_name'`
> **en tant que CLÉS DE VARIABLE DE REQUÊTE**, toutes deux dans `CLES_D_AUTEUR` ; les occurrences de
> `author_name` **en tant que CHAMP DE RÉPONSE oEmbed**, dans `identite-des-comptes.php`, **sont
> attendues et n'entrent pas à ce compte**. » *Le contrôle de #56 est P12 du §11, bâti d'emblée pour
> distinguer le code du commentaire.*

### 13.4 Les bornes 1, 2 et 3

**Borne 1 : NON étendue par #56**, et c'est écrit plutôt que supposé (§12 point 4).
**Borne 2 intacte** : les trois rappels ne dépendent d'**aucun état en base**, ne lisent ni option ni
registre, et **fonctionnent à la seconde où le dossier arrive par FTP**, sans réglage, sans visite de
`wp-admin`, sans régénération de règles de réécriture.
**Borne 3 : ÉTIRÉE, et il faut le dire au lieu de réécrire « périmètre clos » une quatrième fois.**
Voir §18.1.

---

## 14. Ce qui n'est PAS mesuré — aucune de ces lignes n'est présentée ailleurs comme une mesure

1. **`REST_REQUEST` serait déjà définie** quand `rest_endpoints` court — **DÉDUCTION**, et **elle ne
   décide de rien** : le crochet est interdit par ailleurs (§4.3).
2. **L'appelant de `get_routes()` en `class-wp-rest-server.php:1527`** — **son existence est relevée
   (F2), son appelant n'est PAS identifié.** *Sans importance sous un retrait inconditionnel, et c'est
   un argument du §4.1 — pas une lacune tolérée par commodité.*
3. **`wp-admin/users.php`, `user-edit.php` sont des écrans PHP classiques et non REST** — **DÉDUCTION**.
   `profile.php` est **mesuré** (§3.4, S5) ; les deux autres ne le sont pas.
4. **Aucune réponse REST ne passe par `the_author`** — **DÉDUCTION**, couverte par le protocole et non
   par une lecture du cœur.
5. **M-A, M-B et M-F sont DUES et non jouées à l'heure du gel** (§3.3). Elles exigent le code. **Elles
   ne sont présentées nulle part comme faites**, et **M-A décide de la forme du correctif** (§4.1).
6. **Un compte à zéro contenu publié, et la création d'un troisième compte** : écriture en base, hors
   empreinte. La propriété — **les trois rappels ne lisent aucun compte** — est établie **par
   construction, jamais par mesure**.
7. **Le comportement d'un consommateur oEmbed réel, d'un agrégateur de flux réel, d'un moteur de
   recherche réel** · **l'hôte de production et son serveur frontal** · **le comportement d'une version
   future du cœur** sur `rest_endpoints`, sur `the_author` et sur les défauts d'`embed.php`. **Faits
   d'exploitation, pas faits de code.**
8. **Les vingt ancres F1–F20 sont épinglées à WordPress 6.9** et **se périmeront en silence**. **C'est
   la dette T114 (#57), que #56 alimente de vingt ancres et ne solde pas.**

---

## 15. Interdits

- **Ne jamais faire correspondre une clé de route par PRÉFIXE** — `strpos`, `str_starts_with`,
  `str_contains`, `preg_*`, boucle sur `array_keys()`. **Deux `unset()` littéraux** (§4.2).
- **Ne jamais REMPLACER le tableau** du filtre `rest_endpoints` : cela détruirait **toutes** les routes
  du site.
- **Ne jamais employer `defined( 'REST_REQUEST' )` ni `is_admin()` dans le rappel 1** — chacun avec son
  motif propre (§4.3), **et le motif de #49 ne s'y recopie pas**.
- **Ne jamais retirer la garde `is_admin()` du rappel 2** : les écrans de liste mentiraient en silence
  (F15).
- **Ne jamais poser `is_feed()` sur le rappel 2** — trois motifs (§4.5), dont un a été **rayé par la
  mesure et le reste rayé**.
- **Ne jamais lire la valeur reçue** par les rappels 2 et 3 : c'est ce qui garde la propriété « aucun
  compte n'est lu ».
- **Ne jamais échapper la valeur du rappel 2** : trois gabarits, trois règles, aucun échappement juste
  pour les trois (§6).
- **Ne jamais recopier un gabarit de flux du cœur** (`do_feed_rss2`, `feed-atom.php`) — option D.
- **Ne jamais écrire une liste d'identifiants, d'ID ou de `user_nicename`**, ni une table
  `identifiant → pseudonyme` : les rappels ne connaissent aucun compte, **et c'est la propriété.**
- **Ne jamais renommer un `display_name` ni un `user_nicename`** — option E, **consigne d'exploitation,
  pas ligne de code**.
- **Ne jamais `exit`** dans ces rappels, ne jamais poser un statut de notre main.
- **Ne jamais toucher aux règles de réécriture**, ne jamais appeler `flush_rewrite_rules()`.
- **Ne jamais toucher `wp-login.php`** ni aucun fichier du cœur — R6 est **hors empreinte**.
- **Ne jamais écrire dans `includes/admin/**` ni `docs/guide/**`** — **chaînes #54 et #55, en cours dans
  le même arbre de travail.**
- **Ne jamais amputer un bloc de commentaire de `bootstrap.php`** : il s'augmente d'actes datés.
- **Ne jamais renommer `ecarter_le_fournisseur_utilisateurs()`** ni
  `neutraliser_la_requete_d_auteur()` — trois contrats gelés les citent par leur nom.
- **Ne jamais masquer un nom d'auteur du thème par un contournement côté extension** — arrêt, relevé,
  remontée.
- **Aucun fait de domaine n'est en jeu, et aucun ne doit apparaître.** Aucun nom de chien, aucune date,
  aucune généalogie, aucun numéro LOF, aucun résultat.

---

## 16. Arbitrages — chaque désaccord, la décision, sa raison

| # | Désaccord | Décision | Raison |
|---|---|---|---|
| 1 | Trois surfaces, ou une issue par surface ? | **Un fichier, trois rappels, une issue** | **Leur mode de panne est identique** (une identité de compte reparaît dans un document public, en silence), **leur témoin est identique** (un corps HTTP joué en recette), et **leur demi-fermeture est le risque principal de l'issue**. Les tenir ensemble rend « on en a oublié un » plus difficile |
| 2 | Le périmètre : les trois surfaces de l'énoncé, ou quatre ? | **Quatre — R3 entre** | **La mesure a démenti #49 §9** : l'oEmbed publie `admin` sur tout contenu singulier. Même fuite, même identifiant, **et le remède est déjà écrit** puisque la valeur de substitution est la même. L'écarter aurait été **une demi-fermeture connue** |
| 3 | REST : retrait inconditionnel ou condition de capacité ? | **Inconditionnel** | **L'index REST est bâti par le même `get_routes()` que le dispatch** (F2) : une table conditionnée serait **empoisonnée en cache dès la première mise en cache** — objection déjà écrite dans le dépôt (`page-protegee/bootstrap.php:156-161`), transportée mot pour mot. **Et la propriété se prouve par une seule mesure anonyme.** Branche de repli écrite d'avance si **M-A** dit le contraire |
| 4 | Quel crochet REST ? | **`rest_endpoints`** | **C'est le seul des quatre où le CŒUR répond**, donc le seul dont la réponse est **indiscernable à l'octet d'une route absente**. Toute option fabriquant son `WP_Error` **remplace un oracle par un oracle plus discret** (options A, B, C au §4.7) |
| 5 | Caviarder les objets (`rest_prepare_user`) ? | **Non** | **Elle ne ferme rien** : `/users/1` resterait 200 et `/users/3` 404. **La différence de statut EST l'oracle**, pas le corps |
| 6 | Flux : retirer `dc:creator` ou substituer la valeur ? | **Substituer** | Retirer imposerait de **recopier un gabarit du cœur** (option D) — « on modifiera le fichier » déguisé en architecture. **Un filtre sur la valeur existe** (F11) et couvre **les trois gabarits d'un coup** |
| 7 | Quelle valeur de substitution ? | **`get_bloginfo( 'name' )`** | **C'est le défaut du cœur pour le champ jumeau** (F12) : on emprunte sa valeur au lieu d'en inventer une, comme #49 a emprunté `'404'`. Et **aucun compte n'est lu**. *L'argument « c'est le titre du canal » a été ÉCARTÉ : M-K montre que le canal passe par `wp_title_rss()`* |
| 8 | Garde du rappel 2 : `is_feed()` ? | **Non** | Elle livrerait « pas de nom de compte **dans un flux** » au lieu de « **jamais** un nom de compte » — **une demi-fermeture par construction** — et **échouerait du mauvais côté** le jour où un gabarit écrirait `the_author()`. *Un des quatre motifs du plan est tombé (M-D) et il est rayé plutôt que gardé* |
| 9 | Garde du rappel 2 : `is_admin()` ? | **OUI, obligatoire** | **Mesuré** : `class-wp-posts-list-table.php:1284` appelle `get_the_author()`, et la colonne « Auteur » de l'écran Pages rend bien `admin` (§3.4). Sans elle, **les écrans de liste mentiraient en silence** — le mode de panne de l'amendement HIGH de #49, **cette fois vu avant d'être livré** |
| 10 | oEmbed : retirer les clés ou les écraser ? | **Écraser** | Écraser **rétablit le défaut du cœur** (F12) et rend la propriété mesurable **champ à champ contre l'accueil**. Retirer produirait **une forme qu'aucun défaut du cœur ne produit** — signant le retrait |
| 11 | `wp-login.php` : corriger dans #56 ? | **Non — mesuré, non corrigé** | **Quatre discriminants, dont un sans couture dans le cœur** (F18, F19) : le fermer imposerait de réimplémenter un flux du cœur. **Ne fermer que le message serait une demi-fermeture rapportée comme entière.** Plus : hors empreinte · **fiche D3 due dans l'empreinte de #55** · **arbitrage produit** qui n'appartient pas à une issue `seo`. **Décision explicite, renversable par le lead** (§18.3) |
| 12 | `posts?author=N` : à fermer ? | **Non** | **Mesuré comme n'étant PAS un oracle** : `?author=2` et `?author=999` rendent tous deux `[]`, 2 octets, **même md5**. Ni slug, ni nom. **Et il doit rester fonctionnel** : c'est le contrôle P9 qui prouve que la garde REST de #49 mord encore |
| 13 | XML-RPC : à fermer ? | **Non** | **Mesuré comme propre** : même message pour un identifiant connu et inconnu. *Le frère jumeau redouté de R6 n'existe pas* |
| 14 | R7 — le numéro de compte reste lisible | **Acceptable, déclaré** | Ni slug ni nom, et `posts?author=N` n'est pas un oracle. **Au sens du BRIEF §4, un numéro sans nom n'est pas une donnée personnelle.** *Tranché par écrit plutôt que laissé dans une colonne que personne ne relit* |
| 15 | R8 — le `<uri>` d'Atom | **Non fermé** | **Mesuré** (M-G) : sa valeur est **l'URL du site**, pas une donnée personnelle. Le fermer serait **un mécanisme dont aucun effet ne serait observable aujourd'hui** — une garde qui rassure sans couvrir |
| 16 | Renommer le module ? | **Non, et l'objection est écrite** | Le module porte désormais **trois sujets** sous un nom qui n'en annonce qu'un (§18.1). **Mais** un renommage **périmerait des citations par nom dans trois contrats gelés**. **Ajourné, avec son motif — pas tu** |
| 17 | Un fichier ou trois ? | **Un : `identite-des-comptes.php`** | Trois fichiers nommeraient **trois transports** (REST, flux, oEmbed) pour **un seul sujet** — ranger par tuyau plutôt que par sens. Un fichier nomme **la propriété livrée**. *Et agrandir `archives-d-auteur.php` mentirait : une route REST n'est pas une archive d'auteur — motif 1 à l'échelle du fichier, précédent #49 arbitrage 9* |

---

## 17. Ordre d'implémentation imposé

1. `identite-des-comptes.php` **en entier, non accroché** → `php -l` → rejeu (§11 étape 3).
2. `require_once` **seul** → `php -l` → rejeu (étape 4).
3. **`rest_endpoints` seul** → relevé → **S1 (M-A) jouée dans un vrai navigateur**. *C'est ici que la
   branche du §4.1 se décide, et nulle part ailleurs.*
4. **`the_author` seul** → relevé → **S3** (la colonne « Auteur » doit être inchangée).
5. **`oembed_response_data` seul** → relevé.
6. Les quatre actes datés de `bootstrap.php` (§12) → `php -l` → rejeu de P2/P7.

---

## 18. Signalements et questions

### 18.1 Le nom du module — l'objection est écrite plutôt que tue, pour la deuxième fois

#49 écrivait que son installation dans ce module **« frôlait »** le motif 1, et l'assumait par deux
arguments. **#56 étire la borne 3 d'un cran de plus** : la route `wp/v2/users` n'a **rien** d'un
héritage de l'ancien site, et — contrairement à `/author/admin/`, que le bloc d'exception du
2026-09-05 nommait — **elle n'a jamais été nommée par aucun acte de ce module.**

**Ce qui le défend sur le fond, et c'est un argument plus fort que celui de #49 :**

> **La route REST publie littéralement l'adresse que #49 a fermée** — relevé A1 : `link` →
> `/author/<slug>/`. Fermer `/author/admin/` en 404 pendant que `/wp-json/wp/v2/users` en publie l'URL
> **et** le slug, **c'est exactement le mode de panne de #52** : deux mécanismes décrivant le même fait,
> divergeant en silence. **Sous un seul `bootstrap.php`, les six effets tombent et se relèvent
> ensemble.**

**Recommandation au lead, non bloquante** : le module porte désormais **trois sujets** — reprise
d'indexation, plan du site, **et vie privée des comptes** — sous un nom qui n'en annonce qu'un. Un
renommage serait honnête, **mais il périmerait des citations par nom dans trois contrats gelés**.
**Écrire « périmètre clos et daté » une quatrième fois serait un mensonge cumulatif ; l'ajourner avec
son motif ne l'est pas.** *À trancher avant le prochain ajout, pas dans cette issue.*

### 18.2 La consigne d'exploitation de #49 n'a toujours ni fichier, ni issue, ni propriétaire

**Trois issues** (#49, #50 en partie, #56) ont posé des crochets pour empêcher qu'un compte mal nommé
soit lu. **Aucune n'a demandé si ce compte doit exister.** #49 (option D, arbitrage 7) a renvoyé la
réponse à *« une consigne d'exploitation dans le document de mise en ligne »* — **ce document n'existe
pas.**

**Le fait, relevé** : il existe un compte dont `user_login` = `user_nicename` = `display_name` = `admin`,
qui possède 18 chiens, 27 portées, 61 résultats, 6 pages et 1 article. **C'est lui qui rend chaque
surface parlante.** Renommer son `display_name` fermerait `dc:creator`, l'oEmbed **et** une partie de la
REST **sans une ligne de code** — mais c'est un geste d'exploitation qui ne survit pas à une base neuve
(option E, §4.7).

> **Ce n'est pas un argument pour ne rien faire** — #56 ferme les surfaces, et c'est juste. **C'est un
> signalement de priorité** : si `admin` est un artefact de la base de développement, T113 est **moins
> urgente qu'annoncée** ; si c'est le compte que l'hébergeur créera, **aucun crochet ne suffira**.
> **Décision du lead, hors de cette chaîne.**

### 18.3 La question que ce contrat pose au lead — et qui est renversable

**#56 ne livre pas la seconde moitié de la tâche 3 de l'issue (« si oui, l'uniformiser »).** Le §5 en
donne les quatre motifs, dont deux sont **matériels** (hors empreinte ; fiche D3 due dans l'empreinte
d'une chaîne parallèle) et un est **mesuré** (le quatrième discriminant n'a aucune couture dans le
cœur).

**Si le lead juge que #56 doit tout de même l'uniformiser, la décision lui appartient** — et ce contrat
devra être amendé, l'empreinte élargie, et la coordination avec #55 arbitrée. **Rien n'est fait en
silence dans un sens ni dans l'autre.**

### 18.4 Questions bloquantes

**Aucune.** Cette issue ne touche **aucun fait d'élevage** : aucun nom de chien, aucune date, aucune
généalogie, aucun numéro LOF, aucun résultat de test ou de concours, aucun écran de l'éleveuse. **Rien
à demander à l'éleveuse.**

---

# Amendement — 2026-09-08, issue #56 : le relevé APRÈS, la sonde S1 enfin jouée, et deux corrections ouvertes

> Ajout daté, conforme à la **convention d'amendement** déclarée en tête. **Aucune section numérotée
> ci-dessus n'est réécrite** ; cet amendement en **corrige deux passages ouvertement, et dit pourquoi.**

## A. Le relevé APRÈS — joué par le lead de chaîne, pas seulement rapporté

Relevé le **2026-09-08**, après implémentation **et après la passe de refacto**, WordPress 6.9, hôte
`http://localhost:3005`, **depuis le conteneur**, sans cookie sauf pour les sondes authentifiées.
`php -l` sur les **six** fichiers du module : **0 erreur.**

**Objet 1 — la cible du §7.1 est ATTEINTE, à l'octet et au md5 :**

| Adresse | Avant | Après |
|---|---|---|
| `/wp-json/wp/v2/nexiste-pas-du-tout` *(référence)* | `404 · 183 · 4015aeea…` | **inchangée** |
| `/wp-json/wp/v2/users` | `200 · 672` — **2 comptes, nom civil** | **`404 · 183 · 4015aeea…`** |
| `/wp-json/wp/v2/users/1` · `/2` | `200 · 335` · `200 · 334` | **`404 · 183 · 4015aeea…`** |
| `/wp-json/wp/v2/users/3` · `/999` | `404 · 90` | **`404 · 183 · 4015aeea…`** |
| `/wp-json/wp/v2/users?search=fab` | `200 · 336` — **le compte 2 seul** | **`404 · 183 · 4015aeea…`** |
| `/?rest_route=/wp/v2/users` · `/users/1` | `200 · 672` · `200 · 335` | **`404 · 183 · 4015aeea…`** |

> **Les huit rendent le MÊME md5 que la référence rejouée dans le même relevé.** *L'oracle par
> identifiant, l'oracle par recherche et la seconde écriture de la route meurent ensemble.*

**Les quatre routes préservées, inchangées à l'octet** : `/users/me` → `401 · 117` · les trois
`application-passwords` → `501 · 138`. **P4** : l'index `/wp-json/` ne porte plus que **ces quatre
clés** (`242 093` octets contre `249 889`).

**M-B — répondue, et la prédiction du plan est confirmée** : `_embedded.author` porte désormais
`{"code":"rest_no_route",…}` sur `pages?_embed=1` et `posts?_embed=1` ; **zéro occurrence de
`slug":"admin"` et de `/author/admin/`.** *La porte de derrière est fermée par le même geste.*

**Objet 2 — les flux** : `/feed/` et `/feed/rdf/` rendent
`<dc:creator><![CDATA[Berger Hollandais du Mont Brabant]]></dc:creator>`, `/feed/atom/` rend
`<name>Berger Hollandais du Mont Brabant</name>`, `/portees/feed/` de même. **Zéro occurrence de
`admin`** dans `/feed/`, `/feed/atom/` et `/portees/feed/`. **`/comments/feed/` : `200 · 1 690`,
identique à l'octet et au md5** — le contrôle de non-débordement passe.

> **Un faux positif de mesure, consigné plutôt que lissé** : `/feed/rdf/` fait remonter **deux**
> occurrences de `admin`. Ouvertes : `xmlns:admin="http://webns.net/mvcb/"` (l. 6) et
> `<admin:generatorAgent rdf:resource="…" />` (l. 19) — **l'espace de noms `admin:` de RSS 1.0, pas un
> nom de compte.** *Le grep avait raison de les voir ; il aurait eu tort de les compter.*

**Objet 3 — l'oEmbed** : accueil, portée et chien rendent **tous les trois**
`author_name = "Berger Hollandais du Mont Brabant"` et `author_url = "http://localhost:3005"` ; **la
forme `&format=xml` aussi** (M-J confirmée par F14). L'oEmbed de l'accueil est **inchangé à l'octet
(2 162)** — *la référence n'a pas bougé, c'est ce qui rend l'égalité probante.*

**Non-régressions** : `/` `25 491` · `/portees/` `31 819` · `/travail/` `35 411` ·
`/nexiste-pas-du-tout/` `404 · 18 284 · d9f9bae7…` · `/author/admin/` et `/?author=1` **`404 · 18 284 ·
d9f9bae7…`** (**#49 intact**) · `/wp-sitemap-users-1.xml` **`404 · 18 284`** (**#50 intact**) ·
**P1 : 5 `<loc>` au plan du site, aucun `users`** · **P9 : `/wp-json/wp/v2/posts?author=1` → `200 ·
1 847`, inchangé** — *la garde REST de #49 mord toujours* · **P12 : chaque littéral une seule fois en
code** · **`debug.log` : 0 octet avant et après.**

> **Une erreur de mesure de ma main, corrigée et dite** : mon premier P1 a rendu « 1 » au lieu de 5.
> **Ce n'était pas une régression, c'était `grep -c`**, qui compte les **lignes** et non les
> occurrences, sur un XML tenant sur une seule ligne. Recompté avec `grep -o` : **5 `<loc>`, aucun
> `users`.** *Consigné parce qu'un contrôle qui se trompe dans le sens rassurant est le plus dangereux
> de tous — celui-ci s'est trompé dans l'autre sens, et c'est un coup de chance, pas une méthode.*

## B. S1 / M-A — JOUÉE, dans un vrai navigateur, et elle tranche

**#49 a déclaré son contrôle P14 non joué DEUX fois de suite.** Le §11 du présent contrat écrivait :
« **S1 n'est pas facultative** […] l'excuse d'automatisation ne tient pas ». **Elle a été jouée.**

**Méthode** : Chrome installé sur le poste, lancé en `--headless=new` et piloté par **CDP**, session
réellement ouverte sur `wp-login.php` en tant que **`fabienne` (rôle Éditeur)**, puis
`wp-admin/post.php?post=6&action=edit` — l'éditeur de blocs de la page **Accueil**.
*(L'extension de navigateur n'était pas connectée ; le pilotage direct par CDP a été employé à la place
plutôt que de déclarer la sonde injouable.)*

| Observation | Mesuré |
|---|---|
| Racine de l'éditeur, toile rendue | **oui** |
| Blocs chargés | **6** |
| `wp.data.select('core').getCurrentUser()` | **`id=2`, `name=Fabienne Guéneau`** — *son identité se résout, par la route `/users/me` préservée* |
| `getUsers({who:'authors'})` | **`null`** — *la liste d'auteurs est vide : exactement ce que le §4.1 déclarait abandonner* |
| Bouton d'enregistrement | « Enregistrer », **actif** |
| **`savePost()` — le geste réel de l'éleveuse** | **`ENREGISTRE ok — id=6 statut=publish modifié=2026-09-08T13:53:51`** |
| Exceptions JavaScript | **aucune** |
| Réponses HTTP ≥ 400 | **exactement deux**, toutes deux attendues : `wp/v2/users?context=edit&who=authors&per_page=100` et `wp/v2/users?context=view&_fields=id,name` |

> **VERDICT : la branche de falsification du §4.1 NE SE DÉCLENCHE PAS.** L'éditeur **s'ouvre, charge ses
> blocs, résout l'identité de l'éleveuse et ENREGISTRE**. Il **perd une liste**, il **ne tombe pas.**
> **Le retrait inconditionnel est confirmé par la mesure, et le repli par capacité n'a pas lieu d'être.**

**Deux précisions honnêtes sur cette sonde :**
1. **La notice « L'éditeur de blocs requiert JavaScript »** relevée dans le DOM **n'est pas une erreur** :
   c'est le repli `noscript` que le cœur imprime toujours, **présent à l'identique avant le correctif**
   (vérifié dans le diff avant/après). *Mon sélecteur l'a ramassée ; elle ne doit pas être comptée.*
2. **Les deux 404 apparaissent dans la console du navigateur.** Le §4.1 disait « perd sa liste » ; **la
   réalité mesurée est « perd sa liste ET journalise deux 404 en console ».** Invisible pour
   l'éleveuse, visible pour qui ouvre les outils de développement. **Écrit plutôt que lissé.**

**Les autres sondes authentifiées** (session Éditrice) : `edit.php?post_type=mtb_portee` **`175 431`,
identique à l'octet** (#49 non régressé) · `edit.php?post_type=page` **`161 962`** ·
`edit.php?post_type=post` **`134 750`** · `upload.php?mode=list` **`186 838`** · `upload.php` (grille)
**`186 608`** · `profile.php` **`125 826`** — **toutes identiques à l'octet.**

**S3 — la garde `is_admin()` du rappel 2 se prouve par l'observable** : la colonne « Auteur » de l'écran
Pages rend toujours `<a href="edit.php?post_type=page&author=1">admin</a>`, et le titre du site y
apparaît **zéro fois**. *Le mode de panne de l'amendement HIGH de #49 a été vu avant d'être livré, et
il n'a pas été livré.*

### La seule différence non nulle du lot, expliquée et non arrondie — **−53 octets**

`post.php?post=6&action=edit` passe de **765 282** à **765 229**, et
`post-new.php?post_type=page` de **760 073** à **760 020**. Le §7.4 exige qu'une différence soit
**écrite et expliquée**. Elle l'a été, **en désaccrochant le seul rappel 1 le temps d'un diff, puis en
le raccrochant** :

> **La totalité du delta est une seule chaîne**, dans le corps préchargé de `/wp/v2/users/me` :
> `"targetHints":{"allow":["GET","POST","PUT","PATCH"]}`.
>
> Ce sont les **verbes autorisés** que le serveur REST déduit **de la table des routes** pour le lien
> `_links.self` de `/users/me`, lequel pointe sur `/wp/v2/users/2`. **Cette route n'existant plus, le
> serveur ne peut plus énoncer ses verbes et omet l'indication.** Le reste du diff est **exclusivement
> composé de nonces et d'horodatages**, qui changent à chaque requête par construction.
>
> **Le corps de `/users/me` reste entier** — `id`, `name`, `slug`, `link` — donc l'amorçage de l'éditeur
> n'est pas touché, ce que la sonde B confirme par l'enregistrement réussi.

## C. La passe de refacto — deux corrections, toutes deux de la MÊME famille

**Zéro ligne exécutable modifiée** — prouvé par le rejeu de P2 et P7 après coup : md5 identiques à
l'octet sur les huit adresses REST, sur `/`, sur `/portees/` et sur le 404 de référence.

Les deux écarts trouvés étaient **une déduction énoncée comme une mesure**, aux deux endroits mêmes que
le §14 avait isolés d'avance :
1. « la constante serait déjà définie et **le test toujours vrai** » — écrit sans marqueur, alors que
   c'est le **§14 point 1**. Marqué DÉDUCTION.
2. « `users.php`, `profile.php`, `user-edit.php` […] **mesurés intacts** » — **faux au pluriel** : seul
   `profile.php` est mesuré (§3.4), les deux autres sont le **§14 point 3**. Phrase scindée.

**Les quinze ancres du cœur citées dans `identite-des-comptes.php` ont été vérifiées une à une contre le
§3.2 : toutes conformes, aucune inventée.** *C'est la classe de défaut la plus coûteuse de ce dépôt, et
elle est absente de cette livraison.*

**Le `php -l` de cette passe a été joué par le lead**, l'agent de refacto ayant **déclaré n'avoir aucun
outil d'exécution** plutôt que de le maquiller. **0 erreur.**

## D. Deux corrections ouvertes de ce contrat, plutôt qu'un silence

1. **§4.1 renvoie `profile.php` au « contrôle S6 ». C'est FAUX : le §11 lui attribue S5**, S6 portant sur
   `edit.php?post_type=page` et `post-new.php`. **La mesure, elle, est bonne** — `profile.php` est relevé
   à `125 826` avant et après. *Une citation par numéro qui pointe la mauvaise sonde est exactement ce
   que la convention d'amendement existe pour empêcher ; elle se corrige ici, ouvertement.*
   **Lire, au §4.1 : « contrôle S5 ».**
2. **Un CINQUIÈME bloc de prose de `bootstrap.php` a été corrigé, non listé au §12 — et c'est arbitré.**
   Le **motif 3 gelé** (l. 36-39) affirme « ce module-ci n'a PAS de commande, et **sa seule sonde est
   l'état écrit en base** ». **La phrase est fausse depuis #50** — dont la sonde est un code de statut
   HTTP — **et le fichier se contredisait déjà lui-même** quelques paragraphes plus bas. **#56
   l'aggrave** de trois surfaces dont la sonde est un corps HTTP.
   > **Arbitrage : la correction est retenue.** `bootstrap.php` est dans l'empreinte du §1 ; le bloc est
   > **rendu plus faux par #56** ; et la correction est un **acte daté par AJOUT qui ne touche pas une
   > phrase du motif et n'en change pas la conclusion** — les deux modules restent séparés, aucune sonde
   > n'est un code de sortie WP-CLI. **Précédent exact : #49, amendement HIGH §D**, qui a pris un
   > sixième bloc non listé au même titre. *Un contrat qui énumère quatre blocs ne rend pas le cinquième
   > acceptable ; il rend son omission visible, ce qui est le but.*

   Un troisième point signalé par la refacto a également été corrigé, **dans la prose neuve de #56** :
   le bloc des trois accroches écrivait « une garde contre un cas **mesuré** comme impossible », formule
   reprise du fait 5 de `archives-d-auteur.php` **où elle était exacte** et qui ne l'est ici pour
   **aucune** des deux gardes refusées. *Le mot attribuait une mesure à un raisonnement.*

## E. Ce qui reste NON fermé après cet amendement — et n'est présenté nulle part comme fermé

1. **R6 — `wp-login.php` reste OUVERT**, avec ses **quatre discriminants** mesurés (§5.2) dont un
   **sans couture dans le cœur** (§5.3). **T113 n'est close que sur R1, R2, R3 et R4.**
2. **R7 à R11** (§9) sont inchangés et restent déclarés.
3. **Une duplication de doctrine, signalée par la refacto et NON corrigée** : trois phrases sont
   identiques mot pour mot entre `archives-d-auteur.php` et `identite-des-comptes.php` (« LE FICHIER EST
   NEUF… », « MODULE N'EST PAS FICHIER… », « SOUS UN SEUL bootstrap.php, LES … EFFETS TOMBENT
   ENSEMBLE »). **Non corrigée parce que le premier fichier est hors empreinte**, et consignée parce que
   **le jour où la doctrine bouge, l'un des deux divergera en silence** — le mode de panne de #52, à
   l'échelle de la prose. **Résidu de prose, nommé.**
4. **Les points du §14 sont inchangés**, sauf **M-A et M-B, désormais JOUÉES** (§A et §B). **M-F** est
   couverte par le rejeu pas-à-pas. L'appelant de `get_routes()` en `:1527` **reste non identifié**, et
   **cela ne décide toujours de rien** sous un retrait inconditionnel.
5. **Les quinze ancres du cœur de `identite-des-comptes.php` sont épinglées à WordPress 6.9** et **se
   périmeront en silence** : **dette T114, issue #57, que #56 alimente et ne solde pas.**
