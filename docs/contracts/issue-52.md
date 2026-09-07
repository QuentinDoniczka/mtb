# Contrat d'interface — Issue #52 — Mettre en sommeil et réveiller une portée, une page ou une fiche de chien

**Gelé le 2026-09-07.** Une seule face est touchée : l'extension `mtb-core`. **Aucun fichier de thème,
aucun CSS, donc aucun `make css`.** Il n'y a pas eu de plan front, et il n'y a pas de jonction
front↔back à faire : le thème ne reçoit aucune clé nouvelle et n'appelle aucune fonction nouvelle.

Ce document gèle ce que la chaîne #52 livre. Les sections « Interdits » et « Amendements » sont
**opposables aux chaînes futures**.

---

## 1. Ce que le geste fait, et ce qu'il ne fait pas

Un contenu **en sommeil** est retiré des **moteurs de recherche**, du **plan du site**, de la
**recherche du site** et des **flux**. Son **adresse directe continue de l'ouvrir en 200**, sans mot de
passe. Il **reste affiché** dans les listes du site — « La meute », l'index des portées, l'encart
« dernière portée » de l'accueil, les menus.

C'est l'énoncé littéral de l'issue, et c'est ce que `query/page-protegee/` livre déjà pour le contenu
protégé, moins le mur du mot de passe.

**Le sommeil est une archive réversible** (décision 74) et il porte sur **trois** types : `page`,
`mtb_portee`, `mtb_chien`. Aucune restriction par type n'est posée, y compris sur les portées, dont
aucune n'est recensée endormie à ce jour.

**Hors périmètre, et déclaré tel** : le sommeil ne retire rien des listes du site. Une question a été
posée à l'éleveuse là-dessus (§10, Q-52-1) ; sa réponse, quelle qu'elle soit, **n'oblige à ressaisir
aucune valeur** — elle ajouterait des lecteurs à une règle déjà en place.

---

## 2. Métadonnée gelée — `_mtb_en_sommeil`

| Valeur | Sens | Écrite par |
|---|---|---|
| `'1'` | en sommeil | la case cochée · `migration/indexation-heritee/conversion.php` |
| `'0'` | **réveillé explicitement — elle a décidé** | la case décochée, à l'enregistrement |
| **clé absente** | jamais réglé | personne : c'est l'état de départ |

**Le troisième état n'est pas un raffinement.** Distinguer « absente » de `'0'` est ce qui rend la
conversion des cinq contenus repris **ré-exécutable sans jamais rendormir ce que l'éleveuse a
réveillé** — sans option-drapeau, sans commande WP-CLI, sans état de migration à maintenir. Un état de
plus contre un mécanisme de garde en moins.

**Repli ouvert, délibéré** : **seule la chaîne exacte `'1'` endort.** Toute autre valeur — corrompue,
tronquée, sérialisée par erreur — vaut **visible**. Motif, à écrire dans le code : *une méta abîmée ne
doit pas retirer un contenu des moteurs à jamais sans que rien ne le dise.*

Types déclarés : `page`, `mtb_portee`, `mtb_chien`. **Pas `post`** (le site n'en publie aucun de
l'élevage). **Pas `mtb_resultat`** (`public => false` : ni adresse, ni plan du site, ni recherche — un
sommeil y serait sans objet).

`show_in_rest` **vrai pour `page` seule**, avec `auth_callback` **obligatoire** (clé protégée par son
souligné initial), testant **`current_user_can( 'edit_post', $object_id )` — la capacité sur l'objet,
jamais `edit_posts`**. `show_in_rest` **faux** pour `mtb_portee` et `mtb_chien`, qui n'ont pas
d'éditeur de blocs.

**Aucun `default`**, jamais : il rendrait `get_post_meta()` incapable de séparer « absente » de `'0'`
et détruirait l'idempotence de la conversion.

Clé préfixée d'un souligné, donc hors du panneau « champs personnalisés » : **l'éleveuse ne voit jamais
cette clé sous son nom.**

**`_mtb_robots_source` (décision 55) n'est ni lue ni écrite** par `query/mise-en-sommeil/`, ni par
`fields/sommeil/`, ni par `admin/sommeil/`. Le seul point du dépôt où les deux clés se rencontrent est
`migration/indexation-heritee/conversion.php`, qui lit l'une pour écrire l'autre — c'est sa raison
d'être.

---

## 3. Modules, domiciles, gardes

| Module | Groupe | Garde d'inclusion |
|---|---|---|
| `includes/query/mise-en-sommeil/` | `query` | **aucune, jamais** |
| `includes/fields/sommeil/` | `fields` | **aucune, jamais** |
| `includes/admin/sommeil/` | `admin` | **`if ( ! is_admin() ) { return; }` obligatoire** |
| `includes/migration/indexation-heritee/` | `migration` | inchangée |

Les six groupes de `class-loader.php:145` sont une **liste close**. Un `bootstrap.php` hors de ces six
**n'est jamais inclus** : pas d'erreur, pas de ligne au journal, module mort. C'est le piège du lot 18.

**`query/mise-en-sommeil/` est le module FRÈRE de `page-protegee/`, jamais son extension.** Chaque
module de ces crochets répond à **une** question, sur **un** ensemble, avec **une** provenance : « ce
contenu porte-t-il un mot de passe ? » se relit dans `post_password` ; « l'éleveuse a-t-elle endormi ce
contenu ? » est une **troisième** question. Les fondre obligerait `page-protegee` à quitter
`has_password` — une forme **gelée au contrat #23** — pour un besoin qui n'est pas le sien. Et le
témoin d'inclusion Z1 est **par module** : deux modules, deux sondes.

**`fields/sommeil/` ne porte AUCUNE garde d'inclusion, et c'est le piège le plus coûteux de cette
issue.** `fields/chien/bootstrap.php:17` en porte une, à juste titre. Ici, `if ( ! is_admin() ) return;`
empêcherait `register_post_meta` de courir sur la façade REST, où `is_admin()` vaut **faux** : la case
du panneau **Résumé** se cocherait, la page s'enregistrerait, et la case reviendrait **décochée** —
sans erreur, sans journal, sur un écran qui répond 200. **Le contexte se teste dans les rappels, jamais
au chargement.**

**`admin/sommeil/` est un module séparé de `fields/sommeil/` pour cette raison exacte** : l'un a besoin
de la garde, l'autre serait tué par elle. Les réunir fabriquerait le piège que décrit le §2.2 du
contrat #23.

---

## 4. Crochets posés

| Crochet | Priorité / args | Rappel | Module |
|---|---|---|---|
| `wp_robots` | **20** / 1 | `interdire_indexation_du_contenu_en_sommeil` | `query/mise-en-sommeil` |
| `wp_sitemaps_posts_query_args` | 10 / **1** | `exclure_du_plan_du_site` — mute **`post__not_in`** | `query/mise-en-sommeil` |
| `pre_get_posts` | 10 / 1 | `exclure_de_la_recherche` — recherche **et** flux | `query/mise-en-sommeil` |
| `init` | 20 | `declarer_le_champ`, `declarer_le_script` | `fields/sommeil` |
| `post_submitbox_misc_actions` | 10 / 1 | `rendre_la_case` | `fields/sommeil` |
| `save_post_mtb_portee` · `save_post_mtb_chien` · `save_post_page` | 10 / 3 | `enregistrer` | `fields/sommeil` |
| `enqueue_block_editor_assets` | 10 | `mettre_le_script_en_file` | `fields/sommeil` |
| `display_post_states` | 10 / 2 | `ajouter_la_mention` | `admin/sommeil` |
| `mtb_core_mise_a_jour` | 10 / 0 | `convertir` | `migration/indexation-heritee` |
| `added_post_meta` · `updated_post_meta` | 10 / 4 | `sur_arrivee_du_fait` | `migration/indexation-heritee` |
| `admin_init` | 10 | `rattraper` | `migration/indexation-heritee` |

**Crochets RETIRÉS par #52** : `wp_robots` / `marquer_noindex` · `wp_sitemaps_posts_query_args` /
`ecarter_les_noindex`.
**Crochet INTACT, pas d'un caractère** : `wp_sitemaps_add_provider` / `ecarter_le_fournisseur_utilisateurs`
— terrain de **#49 et #50**, hors de ce lot.

Priorité **20** sur `wp_robots` : alignée sur les rappels existants, pour le motif écrit à
`indexation-heritee/bootstrap.php:45-47` — passer après les rappels du cœur, pour ne pas travailler sur
un tableau incomplet.

`accepted_args = 1` sur `wp_sitemaps_posts_query_args` : **le second argument du cœur est refusé**, ce
qui rend structurellement impossible une branche par type de contenu — la porte par laquelle un type
futur serait oublié du plan du site, en silence (arbitrage 8 du contrat #23).

**Signatures non typées** sur les deux filtres exposés au cœur (`$robots`, `$arguments`) : précédent
`admin/corbeille` — un filtre tiers peut rendre autre chose qu'un tableau, et `strict_types` en ferait
une erreur fatale, ici un `<head>` ou un XML tronqué servi à un moteur.

---

## 5. La clause du plan du site : `post__not_in`, et pourquoi la `meta_query` est écartée

**Arbitrage A1, ratifié.** La forme envisagée au brainstorm —
`OR( _mtb_en_sommeil NOT EXISTS , _mtb_en_sommeil != '1' )` — est **écartée sur mesure du cœur**, pas
sur préférence :

- `'NOT EXISTS'` produit un **`LEFT JOIN`** dont le `ON` porte `AND alias.meta_key = %s` ;
- **toute autre** comparaison, `'!='` comprise, produit un **`INNER JOIN`** dont le `ON` ne porte que
  `wp_posts.ID = alias.post_id` ;
- `WP_Meta_Query::find_compatible_table_alias()` ne partage un alias entre frères d'un `OR` que pour une
  liste blanche de comparaisons positives (`=`, `IN`, `BETWEEN`, `LIKE`, `REGEXP`, `RLIKE`, `>`, `>=`,
  `<`, `<=`) — **où ne figurent ni `!=` ni `NOT EXISTS`**.

Donc deux alias, dont un `INNER JOIN` non qualifié par la clé, que le `OR` du `WHERE` ne peut pas
rattraper : **tout contenu ne portant AUCUNE ligne dans `wp_postmeta` disparaîtrait du plan du site** —
c'est-à-dire les pages créées par `wp post create`, dont l'**Accueil**, auxquelles WordPress n'attache
ni `_edit_lock` ni `_edit_last`. Le plan du site répondrait 200, XML valide, amputé, **sans un mot**.

**Forme gelée** : `exclure_du_plan_du_site()` **mute `post__not_in`**, en fusionnant avec la valeur
existante, par `array_values( array_unique( array_merge( … ) ) )`. Les identifiants viennent de
`identifiants_en_sommeil()`, **une seule clause `=`** (`meta_key` + `meta_value = '1'`), **mémoïsée par
requête, avec garde de ré-entrance**.

Trois propriétés, dont deux décisives :

1. **Aucune jointure `NOT EXISTS`, donc aucun piège d'alias.** La clause `=` sélectionne **uniquement**
   les endormis.
2. **Disjonction de clés parfaite** : `page-protegee` mute `has_password`, `mise-en-sommeil` mute
   `post__not_in`. **Aucun des deux ne lit la clé de l'autre** — propriété plus forte que l'additivité,
   et qui rend le résultat identique dans les deux ordres d'exécution.
3. `array()` est un no-op strict de `WP_Query` : « aucun contenu endormi » ne change pas une ligne du
   SQL du cœur.

**La convention de cohabitation gelée aux contrats #23 et #24 n'est pas abrogée, elle est tenue** :
chaque rappel **mute des clés**, aucun ne remplace `$args`. Restent interdits `return array( … );`,
`$arguments = array( … );` et `unset( $arguments['has_password'] );`. Le bloc de commentaire qui porte
cette convention est **transplanté** de `plan-du-site.php` vers `query/mise-en-sommeil/bootstrap.php`,
augmenté de la mesure d'alias ci-dessus, et remplacé sur place par un renvoi d'une ligne.

**Garde de ré-entrance, obligatoire** : `identifiants_en_sommeil()` exécute une requête depuis
l'intérieur de `pre_get_posts` et de la construction du plan du site. La mémoïsation par requête **et**
le drapeau de ré-entrance sont le module, pas un ornement ; la garde 2 de
`exclure_de_la_recherche()` (`! is_main_query()`) empêche par ailleurs la requête interne de se
re-filtrer elle-même.

---

## 6. La conversion du fait hérité — jamais la cohabitation

**Arbitrage A2, tranché : les deux mécanismes ne peuvent pas cohabiter.** Démonstration, sans
hypothèse : l'éleveuse réveille Halan → `_mtb_en_sommeil = '0'` → `query/mise-en-sommeil` se tait —
**mais** `marquer_noindex()` lit toujours `_mtb_robots_source` et rend `noindex`, et
`ecarter_les_noindex()` retire toujours Halan du plan du site sur `NOT EXISTS`. **Le réveil ne réveille
rien** : T108 non payée, plus un écran qui affirme le contraire. C'est le mode de panne le plus grave
possible sur cette issue.

**Le rattrapage partiel est refusé, et il est écrit ici pour que personne ne l'essaie** : on *peut*
poser un `wp_robots` en priorité 30 qui retire le `noindex` hérité, mais on **ne peut pas** retirer la
clause qu'un rappel voisin a posée dans les arguments du plan du site — la convention gelée interdit de
reconstruire `$args`, et défaire la clause d'un voisin est précisément le geste que les contrats #23 et
#24 interdisent. On obtiendrait un demi-réveil : balise rétablie, plan du site toujours amputé.

**Ce qui est fait**, précisément :

| Fichier | Geste |
|---|---|
| `fait.php` | **la donnée et sa provenance sont inchangées** — `_mtb_robots_source`, `CLE`, `demande_noindex()` et le format à provenance de la décision 55 ne bougent pas. **Un seul bloc de commentaire est corrigé**, voir A11 |
| `robots.php` | **supprimé** — `marquer_noindex()` en était le seul corps, il n'en reste rien de vivant |
| `plan-du-site.php` | `ecarter_les_noindex()` **supprimée** ; `ecarter_le_fournisseur_utilisateurs()` et son bloc d'exception **inchangés** |
| `bootstrap.php` | les `add_filter` des lignes 47 et 52 **retirés** ; celui de la ligne 57 **inchangé** ; en-tête réécrit |
| `conversion.php` | **créé** |

> **Arbitrage A11, rendu le 2026-09-07 — ce que « inchangé, octet pour octet » couvrait, et ce qu'il ne
> couvrait pas.** La première rédaction de ce §6 gelait `fait.php` « octet pour octet ». **L'intention
> était de protéger la DONNÉE recopiée et sa provenance** — `_mtb_robots_source`, la constante `CLE`,
> `demande_noindex()` et le format à provenance de la **décision 55** —, et elle reste entière : aucune
> de ces lignes ne bouge, et aucune méta n'est réécrite.
>
> **Elle ne couvrait pas une description de mécanique devenue fausse.** Le bloc de commentaire de
> `fait.php` décrivait « l'asymétrie entre les deux lecteurs de cette clé » — le filtre `wp_robots` et
> le retrait du plan du site — et renvoyait à la mesure d'égalité du §6.2 de #24 **comme si elle
> valait**. Or #52 a supprimé **les deux lecteurs**, et le §11.3 déclare cette mesure **morte**.
> Laisser ce bloc, c'était **HIGH-1 dans le code plutôt que dans le contrat**, et lu bien plus souvent :
> une chaîne future y aurait trouvé l'invitation à rétablir précisément ce que le **§12** lui interdit —
> un filtre lisant `_mtb_robots_source` —, ce qui rendrait **le réveil de l'éleveuse inopérant en
> silence**.
>
> **Le bloc est donc réécrit ; rien d'exécutable ne change** (diff vérifié : toutes les lignes ajoutées
> ou retirées sont des lignes de commentaire). Il dit désormais que la clé est un **fait recopié avec sa
> provenance**, qu'elle **n'agit plus** depuis la conversion, que les deux lecteurs **ont été
> supprimés**, que la mesure du §6.2 est **relevée au §11.3**, et il porte l'**interdit du §12**. La
> règle générale que cet arbitrage pose : **un gel « octet pour octet » protège une donnée et sa
> provenance, jamais une affirmation sur le fonctionnement — une affirmation fausse ne se gèle pas,
> elle se corrige.**

**Le commentaire historique de `robots.php` ne disparaît pas en silence.** Celui qui déclare qu'« un
`noindex` posé par filtre est invisible et irréversible depuis `wp-admin` » est **relevé, daté et
déclaré payé** en tête de `conversion.php` — le fichier qui transforme cet état invisible en
interrupteur. Il porte l'issue d'origine (#24, 2026-09-05), sa suppression (#52, 2026-09-07), et la
consigne de ne pas rouvrir le trou : *toute règle d'indexation future doit se poser sur un état visible
dans un écran.*

**La règle de conversion** : *pour chaque contenu dont `demande_noindex()` est vrai et qui ne porte
**aucune** valeur `_mtb_en_sommeil`, écrire `'1'`.* La valeur écrite **dérive de la méta déjà en base,
jamais d'une liste d'identifiants en dur** — c'est la tâche 4 au sens littéral : **on recopie l'état,
on ne l'invente pas.**

Le test d'absence est **`metadata_exists( 'post', $id, CLE )`, jamais `get_post_meta()`** : lui seul
sépare « absente » de `'0'`.

**Les deux questions restent séparément lisibles après conversion, et c'est une exigence, pas une
conséquence heureuse.** `_mtb_robots_source` répond à *« qu'est-ce que l'ancien site déclarait ? »* et
garde sa provenance (décision 55) ; `_mtb_en_sommeil` répond à *« qu'est-ce que l'éleveuse a décidé
depuis ? »*. **Les deux clés coexistent sur les cinq contenus et ne fusionnent jamais** : un contenu
réveillé porte le fait hérité **inchangé** et l'état à `'0'`. Une chaîne future qui effacerait
`_mtb_robots_source` « puisqu'elle n'agit plus » détruirait la seule trace de ce que la source
déclarait, et rendrait la reprise invérifiable.

**L'idempotence se prouve par la mesure, jamais par la lecture du code** : `convertir()` jouée deux fois
de suite rend **`n` puis `0`**, et sur un contenu remis à `'0'` elle rend **`0`** en laissant `'0'`
intact. C'est le contrôle **C4**, et il est la preuve que T108 est payée — un réveil n'est jamais défait.

**Le trou du déclencheur, mesuré et fermé.** `mtb_core_mise_a_jour` ne se déclenche que si
l'empreinte d'identité change (`MTB_CORE_VERSION` + types + taxonomies `mtb_`). #52 n'ajoute ni type ni
taxonomie et ne touche pas `mtb-core.php`. **Ce crochet ne se déclenchera donc pas une seule fois du
fait de #52 sur une base déjà pourvue de `mtb_core_empreinte`** — et là où il se déclenche (base
neuve), il court **avant** que les commandes de reprise n'aient écrit `_mtb_robots_source`. Une
conversion accrochée à lui seul **ne convertirait rien et laisserait les cinq contenus indexables, en
silence.** D'où **trois accroches sur la même fonction idempotente**, chacune avec son rôle nommé :

| Accroche | Ce qu'elle couvre |
|---|---|
| `mtb_core_mise_a_jour` | installation neuve sur base déjà peuplée. **Gardée parce qu'elle est le point d'accroche contractuel et coûte une ligne — jamais présentée comme le déclencheur porteur** |
| `added_post_meta` / `updated_post_meta`, filtrées sur `'_mtb_robots_source'` | l'ordre « code déployé, puis import » — la conversion suit le fait à la milliseconde, sous WP-CLI comme ailleurs |
| `admin_init` | **le déclencheur porteur** : l'ordre « import déjà joué, puis code déployé », donc la base d'aujourd'hui et toute base restaurée par copie |

`admin_init` court **avant que `post.php` ne rende un écran d'édition** : c'est ce qui garantit que
l'éleveuse ne verra jamais une case décochée sur Halan avant la conversion — sans quoi un simple
« Mettre à jour » écrirait `'0'` et **le réveil silencieux aurait été fait par elle, sans qu'elle l'ait
voulu.** Garde `if ( wp_doing_ajax() ) { return; }` en tête.

**Aucune option-drapeau** : la **borne 2** de l'amendement au §2 du contrat #1 (`issue-24.md` §15)
interdit à un module de `migration/` de dépendre d'un état en base pour se déclencher. Le garde-fou est
la requête elle-même — `fields => ids`, `meta_query` **sans aucun `OR`** (`_mtb_robots_source` `EXISTS`
**AND** `_mtb_en_sommeil` `NOT EXISTS`), rendant **0 ligne** dès le lendemain de la conversion, et
**jamais sur une requête publique**.

**Aucune récursion** : `sur_arrivee_du_fait()` sort sur toute clé autre que `_mtb_robots_source`, donc
l'écriture de `_mtb_en_sommeil` ne se rappelle pas elle-même.

**Aucun nonce, aucune capacité sur la conversion, et c'est motivé, pas oublié** — même raisonnement que
`class-loader.php:276-285` : cette écriture n'est **pas d'origine utilisateur**, sa valeur dérive d'une
méta déjà en base, elle doit tourner sur un import WP-CLI comme sur la première visite d'administration
venue, et une capacité la rendrait inopérante là où elle sert. La règle « nonce sur toute écriture » de
`CLAUDE.md` vise les écritures d'origine utilisateur ; ce n'en est pas une.

---

## 7. Écrans, libellés, chaînes fournies par le serveur

**Une seule source pour toutes les chaînes : `includes/fields/sommeil/libelles.php`.** Le JavaScript les
**reçoit du serveur et les imprime ; il n'en compose aucune.** Les trois écrans ne peuvent pas diverger.

| Emplacement | Texte, au caractère près |
|---|---|
| Case à cocher (action) | **Mettre ce contenu en sommeil** |
| Mention dans les listes (état) | **En sommeil** |
| Aide sous la case | *Le contenu reste en ligne : son adresse continue de l’ouvrir normalement. Il n’est plus proposé par les moteurs de recherche, ne figure plus dans le plan du site et ne remonte plus dans la recherche de votre site. Il reste affiché dans vos pages et dans vos menus.* |
| Avertissement, page d’accueil seule | **Cette page est la page d’accueil de votre site : la mettre en sommeil retire l’adresse principale du site des moteurs de recherche.** |

> **Arbitrage A8, rendu le 2026-09-07 sur remontée de l'implémentation — l'apostrophe est TYPOGRAPHIQUE
> (`’`), jamais ASCII (`'`), dans les quatre chaînes ci-dessus.** La première rédaction de ce contrat
> les portait en ASCII et exigeait la recopie « au caractère près » ; l'implémentation a relevé que tout
> le reste des écrans de l'extension emploie `’` (`fields/portee/ecran.php:126,138,145,186`,
> `admin/corbeille/bootstrap.php`), si bien que l'aide de la case et le `<p class="description">` voisin
> auraient affiché **deux apostrophes différentes sur le même écran**. La clause « au caractère près »
> porte sur **la formulation** — aucun mot n'est reformulé, aucune phrase raccourcie — et non sur un
> détail typographique introduit par inadvertance à la rédaction. La convention du dépôt l'emporte, et
> le tableau ci-dessus est la forme opposable.

Vérifié contre MASTER.md §10.4 : **aucun mot interdit**. Pas d'emoji, pas de pastille de couleur, **ni
« masqué » ni « caché »** — deux mots qui promettraient que la page ne s'ouvre plus. « En sommeil » est
**le mot de l'éleveuse elle-même**, spontané, relevé le 2026-09-05 ; il se garde. §10.1 impose le verbe
à l'infinitif pour une action, d'où le partage action / état.

**Portée et chien — éditeur classique.** Case dans l'encadré **Publier**, rendue par
`post_submitbox_misc_actions`. **Position réelle, à écrire et à reprendre dans la fiche d'aide** : ce
crochet court à la fin de `#misc-publishing-actions`, donc **après** la ligne *Visibilité* **et après**
la ligne *Publier le* — il n'existe aucun crochet entre les deux. La fiche décrit **ce qu'elle voit**,
jamais ce qu'on aurait voulu.

**Page — éditeur de blocs.** `wp.editor.PluginPostStatusInfo`, avec repli **écrit et commenté** vers
`wp.editPost.PluginPostStatusInfo`, **déprécié depuis WordPress 6.6** — dans ce sens et jamais
l'inverse, sinon la console imprime un avertissement de dépréciation à chaque ouverture. ES5,
`wp.element.createElement`, **aucun JSX, aucune étape de construction**.

> **Arbitrage A10, rendu le 2026-09-07 — le panneau « Résumé » N'EXISTE PAS, et l'emplacement réel est
> celui-ci.** La première rédaction de ce §7 situait la case dans un panneau **« Résumé »**, nom repris
> du guide et jamais mesuré. **Relevé au navigateur réel, WordPress 6.9, le 2026-09-07** : la recherche
> exhaustive des nœuds feuilles rend `[]` pour « Résumé » — **le mot n'est nulle part sur cet écran.**
> Ce qui existe : une **zone latérale de droite à deux onglets, « Page » et « Bloc »** ; sous l'onglet
> **« Page »**, un titre, puis les rangées **État · Publier · Slug · Auteur/autrice · Modèle ·
> Commentaires · Parent** ; **la case est dans la même section, après ces rangées, sans aucun titre de
> panneau au-dessus d'elle.**
>
> **Ce n'est pas nous qui choisissons cet emplacement, c'est `PluginPostStatusInfo`** — le code était
> juste, c'est ce document qui était faux. Un contrat gelé qui décrit un écran que la version installée
> ne porte pas ne protège plus rien : il fige une erreur. D'où la règle que cet arbitrage pose pour la
> suite : **un fait d'interface gelé ici porte la version dans laquelle il a été relevé et la date du
> relevé.**
>
> **Libellés du cœur mesurés le même jour, WordPress 6.9**, parce que la fiche d'aide les recopie :
>
> | Écran | Ligne de date | Bouton |
> |---|---|---|
> | Portée / chien **publié** (éditeur classique) | **« Publié le : »** | **« Mettre à jour »** |
> | Portée / chien **neuf** | **« Publier tout de suite »** | **« Publier »** |
> | Page **publiée** (éditeur de blocs) | — | **« Enregistrer »**, et il **ne change pas** selon l'état |
>
> **« Publier le » n'existe sur aucun de ces écrans** : ne pas le réintroduire. Les deux éditeurs
> diffèrent sur le bouton, et c'est l'origine mesurée d'une erreur qui vit encore dans
> `docs/guide/page-proteger-une-page-par-mot-de-passe.md` — **hors de l'empreinte de #52**, signalée au
> lead, non corrigée ici.

**Transport des chaînes jusqu'au JavaScript — propriété gelée, pas seulement mesurée.** Les libellés
partent par `wp_add_inline_script()` dans `window.mtbSommeil`, sérialisés par **`wp_json_encode()`**,
qui échappe les caractères non-ASCII en `\uXXXX` : **la charge utile part en ASCII pur sur le fil** et
le moteur JavaScript la restitue en U+2019. Le risque de mojibake sur les apostrophes typographiques
est donc **structurellement nul, et pas seulement absent à la mesure**. **Conséquence opposable** :
aucune chaîne future ne compose un libellé à la main dans le JavaScript ni ne contourne
`wp_json_encode()` — elle perdrait cette propriété sans qu'aucun écran ne le dise.

**`show_in_rest` est vrai pour `page` SEULE, et c'est motivé.** Seule la page emploie l'éditeur de
blocs : sans `show_in_rest`, `editPost({ meta })` n'écrirait rien, **sans erreur visible**. `mtb_portee`
et `mtb_chien` emploient l'éditeur classique (`use_block_editor_for_post_type` rendu faux) et passent
par `save_post_*` : leur ouvrir la REST serait une surface sans usage. C'est aussi le précédent de
`content/portee/champs.php`, dont les clés sont fermées à la REST parce que
`WP_REST_Post_Meta_Fields` ne teste pas le mot de passe d'un contenu.

**Aucun octet de CSS n'est produit par l'extension** : classes du cœur uniquement (`misc-pub-section`,
`howto`), `wp.components.CheckboxControl` côté blocs. **Aucun `make css`, aucun artefact `*.min.css`
dans l'empreinte de cette issue.**

**La page d'accueil garde son interrupteur** — décision 74 : l'éleveuse seule décide de ce qu'elle
archive, et le lui retirer serait décider à sa place. **Mais rien n'est silencieux** : l'avertissement
ci-dessus paraît sur la seule page réglée comme page d'accueil. Il est **factuel et sans
dramatisation** : il dit ce que le geste fait, il ne dit pas de ne pas le faire. Détection par le seul
couple d'options qui décide de la page d'accueil —
`'page' === get_option( 'show_on_front' ) && $id === (int) get_option( 'page_on_front' )` — **jamais par
lecture de permalien ni comparaison d'URL**, deux chemins qui se trompent dès qu'un site est servi en
sous-dossier.

**Mention dans les listes** : `display_post_states`, la primitive native du cœur qui écrit déjà
« Brouillon », « Protégé par un mot de passe », « Page d'accueil ». La mention **dérive de la méta, sans
liste de types** : elle paraît donc dans Portées, Chiens **et Pages**, et resterait juste si un type
futur portait la clé. **`admin/listes/types.php` n'est pas ouvert.** Aucune colonne, aucun CSS, **du
texte et jamais une couleur seule** (D7).

**Q24 — cibles tactiles.** La requalification de #32 est **appliquée, pas réinventée** : **24 px, SC
2.5.8, exception d'espacement** (`issue-32.md:573-574`), pour les écrans d'administration. **Q24 reste
ouverte pour l'utilisateur** ; #52 ne la rouvre pas. L'espacement est **mesuré au navigateur réel**
(contrôle A1), sur les deux écrans, à 1440 px et à 360 px — **un verdict déduit est refusé.**

---

## 8. États spéciaux

| État | Émis par le serveur | Rendu par le thème |
|---|---|---|
| `en_sommeil` | `_mtb_en_sommeil === '1'` — retire des moteurs, du plan du site, de la recherche, des flux | **rien.** Le thème n'en connaît rien et ne reçoit aucune clé nouvelle |
| `reveille_explicitement` | `_mtb_en_sommeil === '0'` — jamais rendormi par la conversion | rien |
| `jamais_regle` | clé absente — état de départ, convertible | rien |
| `page_protegee` | inchangé (#23), se **cumule** avec le sommeil sans que l'un annule l'autre | inchangé |
| `donnee_absente` | inchangé | inchangé |

**Aucune fonction globale `mtb_*` n'est exposée par #52.** Le thème n'est pas touché : ni
`mtb_get_chiens_par_statut()`, ni `mtb_get_portees()`, ni `mtb_get_derniere_portee()`, ni aucune autre
fonction de lecture ne change de comportement. Amendement identique à celui du §2.3 du contrat #23,
restaté ici et **jamais écrit dans `issue-1.md`** (décision 70 : le §11 est tenu par le lead à la
clôture du lot, jamais par une chaîne).

Surface **interne**, sous espace de noms, consommée uniquement par l'extension :

```
MTB\Core\Query\MiseEnSommeil\CLE                              // '_mtb_en_sommeil'
MTB\Core\Query\MiseEnSommeil\ENDORMI                          // '1'
MTB\Core\Query\MiseEnSommeil\REVEILLE                         // '0'
MTB\Core\Query\MiseEnSommeil\est_en_sommeil( int ): bool
MTB\Core\Query\MiseEnSommeil\identifiants_en_sommeil(): array // array<int>, mémoïsée
MTB\Core\Fields\Sommeil\TYPES                                 // array( 'page', 'mtb_portee', 'mtb_chien' )
MTB\Core\Fields\Sommeil\CHAMP                                 // 'mtb_en_sommeil' — nom du champ de formulaire
MTB\Core\Fields\Sommeil\libelle_case(): string
MTB\Core\Fields\Sommeil\aide(): string
MTB\Core\Fields\Sommeil\libelle_etat(): string
MTB\Core\Fields\Sommeil\avertissement_accueil(): string
MTB\Core\Fields\Sommeil\est_la_page_d_accueil( int ): bool
MTB\Core\Migration\IndexationHeritee\convertir(): int
```

> **Arbitrage A9, rendu le 2026-09-07 sur remontée du refacto — trois recopies fermées, deux laissées.**
> `TYPES` et `CHAMP` sont **ajoutés** à la surface interne, et la clé `CLE` est **transportée jusqu'au
> JavaScript** par `window.mtbSommeil.cle`. Motif commun : ces trois valeurs étaient recopiées d'un
> fichier à l'autre, et **chacune des trois divergences aurait été muette** — un quatrième type déclaré
> à `register_post_meta` mais absent de l'écran classique ; un nom de champ changé d'un côté seulement,
> qui ferait lire « décochée » à chaque enregistrement ; et une clé changée en PHP que le JavaScript
> continuerait d'écrire sous son ancien nom, la case du panneau **Résumé** cessant de persister **sans
> une ligne au journal**. C'est exactement la famille d'interdit du §12, appliquée à la valeur au lieu
> de la clé. **Le contrôle E4 se rejoue** après ce changement, la charge serveur→JavaScript ayant bougé.
>
> **Une recopie de plus, laissée sciemment et déclarée ici — c'est le geste qui manquait à A9.**
> `editeur.js` recopie les valeurs `'1'` et `'0'` en littéraux, là où `CLE` lui est **transportée** par
> `window.mtbSommeil.cle` et **gardée** par un repli. L'asymétrie est réelle : trois recopies fermées,
> une quatrième laissée. **Elle est laissée pour une raison mesurée, pas par omission** — le risque
> pratique est nul (`ENDORMI` et `REVEILLE` sont les deux états d'un booléen stocké en chaîne, gelés au
> §2 de ce contrat et interdits de changement par le §12), tandis que la fermer imposerait de rouvrir
> `editeur-de-blocs.php` et de **rejouer le contrôle E4 au navigateur réel**, seule sonde prouvant que
> le panneau écrit. **Ce que la revue relevait n'était pas le risque, c'était le silence** : A9 déclare
> nommément ce qu'il laisse, celle-ci ne l'était pas. Elle l'est désormais. **Dette portée au lot, pas
> à cette chaîne.**
>
> **Deux points signalés et laissés en l'état, déclarés ici pour qu'ils ne se redécouvrent pas** :
> (a) `indexation-heritee/bootstrap.php` conserve son `require_once` de `fait.php`, devenu un no-op
> strict depuis que `conversion.php` le requiert lui-même — le bootstrap sert de **manifeste des
> fichiers du module**, convention tenue ailleurs dans le dépôt ; (b) `est_la_page_d_accueil()` vit dans
> `libelles.php` sans être un libellé, ce que l'en-tête du fichier **déclare honnêtement** ; le §8 gèle
> son espace de noms, pas son fichier, et la déplacer coûterait plus que le chargement d'une fonction
> inutilisée en administration.

---

## 9. Arbitrages rendus

| # | Désaccord | Décision | Motif |
|---|---|---|---|
| **A1** | `meta_query` en `OR` (brainstorm) contre `post__not_in` (plan back) | **`post__not_in`** | Mesuré sur `WP_Meta_Query` : la forme en `OR` fait disparaître du plan du site tout contenu **sans aucune ligne de `wp_postmeta`**, dont l'Accueil, en silence. Voir §5. Le contrôle **S5** rend le verdict mesurable |
| **A2** | conversion du fait hérité contre cohabitation des deux mécanismes | **conversion, `indexation-heritee/**` ouvert** | La cohabitation produit un **réveil qui ne réveille pas** : T108 non payée, plus un écran qui affirme le contraire. Voir §6 |
| **A3** | `wp_robots_no_robots()` contre reproduction littérale de `noindex, nofollow` | **`wp_robots_no_robots()`** | Écart **déclaré** : sur `blog_public = 1`, les cinq passent de `noindex, nofollow` à `noindex, follow`. Le **fait recopié reste intact en base**, avec sa provenance ; seule la **directive servie** s'aligne sur la règle, ce qui est le sens même de la conversion. Le helper du cœur est **non remplaçable** et accorde seul `follow`/`nofollow` selon `blog_public` ; un `noindex` posé à la main ne le ferait pas. Un `nofollow` sur une fiche de chien retiendrait au passage l'exploration de fiches qui, elles, doivent être indexées. **Alternative disponible en une ligne**, consignée ici pour qu'une chaîne future ne « répare » pas ce qui est voulu |
| **A4** | les flux sont-ils inclus, alors que l'énoncé nomme « moteurs, plan du site, recherche » ? | **inclus** | Un flux est un index public au sens littéral du BRIEF §8 — c'est l'**arbitrage 3 du contrat #23**, déjà tranché pour le contenu protégé. Coût nul (une condition dans un rappel déjà écrit) ; l'omettre créerait entre deux modules frères une asymétrie inexplicable, qu'une chaîne future « corrigerait » dans un sens ou dans l'autre |
| **A5** | l'interrupteur doit-il être retiré de la page d'accueil ? | **non retiré, mais jamais silencieux** | Décision 74 : l'éleveuse seule décide de ce qu'elle archive ; le lui retirer serait décider à sa place, sur l'axe même que l'utilisateur vient de clore. Le mode de panne visé — un `noindex` sur la porte d'entrée que rien ne montre — est fermé par l'**avertissement en clair** du §7, pas par une interdiction |
| **A6** | où déclarer `register_post_meta`, `content/**` étant hors empreinte ? | **`fields/sommeil/declaration.php`, sur `init` 20** | Voir l'amendement §11.1 |
| **A7** | `robots.php` : fichier réduit ou supprimé ? | **supprimé** | Une fonction que rien n'appelle est du code mort ; un fichier réduit à un commentaire ferait croire à un mécanisme encore actif. Le commentaire historique est **transplanté, daté et déclaré payé** (§6) |

---

## 10. Ce que ce contrat ne tranche pas

**Q-52-1 — pour l'éleveuse, question posée, non bloquante.** *Quand vous dites qu'un contenu est « en
sommeil », voulez-vous dire (a) qu'il n'est plus proposé par les moteurs de recherche mais reste affiché
sur votre site, ou (b) qu'il n'apparaît plus non plus dans les listes de votre site — « La meute » pour
un chien, l'index des portées pour une portée ?*

Sur l'ancien site, les cinq contenus étaient **les deux à la fois** : hors des moteurs **et** reliés
depuis aucune page. Sur le site livré, les quatre chiens paraissent dans « La meute ». Son mot est
« *placement je l'affiche parfois* » — **afficher** est un verbe d'affichage, pas d'indexation, et
personne ne lui a jamais demandé laquelle des deux elle appelle « sommeil ».

**#52 livre (a)** — le périmètre littéral de l'énoncé — avec la phrase d'aide qui dit explicitement
« *Il reste affiché dans vos pages et dans vos menus* ». **Rien n'est menti, rien ne sera à ressaisir** :
si elle répond (b), ce sera une issue qui **ajoute des lecteurs** à `est_en_sommeil()`, sans qu'une
seule valeur soit retouchée. **Ce défaut n'est pas la réponse à sa question** : c'est le sous-ensemble
sûr, en attendant qu'elle tranche.

**Q23 reste ouverte, et le motif reste inconnu.** Décision 72 : une corrélation mesurée n'est pas un
motif. **Ni le code, ni ses commentaires, ni la fiche d'aide n'avancent d'explication** au fait que ces
cinq contenus-là dorment.

**Q24 reste ouverte pour l'utilisateur.** #52 applique la doctrine de #32 et la déclare (§7).

**T105 et T106 restent ouvertes** sur `migration/indexation-heritee/`, et sont les issues **#49** et
**#50**. #52 a ouvert ce dossier et **ne les a pas soldées, délibérément** :
`ecarter_le_fournisseur_utilisateurs()` et son bloc d'exception ne sont pas touchés d'un caractère, et
le contrôle **Z7** prouve que ce rappel est toujours accroché après #52.

**Surface REST déclarée, non fermée** : `show_in_rest => true` ouvre la **lecture anonyme** de
`_mtb_en_sommeil` sur les pages (l'`auth_callback` ne garde que l'écriture). Ce n'est ni une donnée de
domaine ni une donnée personnelle, et **l'information est déjà publiquement observable** dans le
`<head>` de la page endormie elle-même : la REST n'ajoute aucune connaissance. C'est néanmoins une
surface nouvelle : elle est **déclarée et mesurée** par une sonde, jamais supposée close.

---

## 11. Amendements aux contrats gelés

Les contrats gelés **ne se modifient pas**. Les relèves qui suivent sont écrites et datées ici,
précédent établi au lot 18 par #23 et #24 (`issue-22.md:783`). **`docs/contracts/issue-1.md` n'est pas
ouvert par cette chaîne** (décision 70).

**11.1 — Écart déclaré au découpage `content` / `fields`.**
> *Un module de `fields/` peut déclarer une métadonnée par `register_post_meta` sur `init` 20, quand
> cette métadonnée porte sur plusieurs types dont au moins un n'appartient à aucun module de
> `content/`.* Motifs : `content/**` est hors empreinte de #52 et fermé ; les trois modules de
> `content/` possèdent **un** type chacun, quand `_mtb_en_sommeil` en porte **trois** dont `page`, que
> aucun module de `content/` ne possède ni ne possédera (c'est un type du cœur) ; et la clé ne décrit
> pas le modèle de contenu de l'élevage, elle décrit un **geste d'édition**. **Cet écart n'ouvre rien
> d'autre** : un champ propre à un seul type `mtb_` reste déclaré dans `content/`.

**11.2 — Resserrement de la borne 1 de l'amendement au §2 du contrat #1** (`issue-24.md` §15).
> La borne 1 — « un module de `migration/` qui accroche un hook de front est en lecture seule, jamais
> `update_post_meta` » — porte sur **ses hooks de front**.
> `migration/indexation-heritee/conversion.php` écrit `_mtb_en_sommeil`, mais **jamais sur une requête
> publique** : ses trois accroches sont `mtb_core_mise_a_jour`, `added_post_meta` / `updated_post_meta`
> (donc administration ou WP-CLI) et `admin_init` (administration seule). Le seul hook de front restant
> du module, `wp_sitemaps_add_provider`, demeure **strictement en lecture**. **Les bornes 2 et 3 sont
> intactes** : aucun état en base ne déclenche la conversion, et son périmètre reste clos aux seuls
> faits `_mtb_robots_source` relevés sur l'ancien site.

**11.3 — Relève de la mesure d'égalité du §6.2 du contrat #24.**
> **L'égalité en question.** Le contrat #24 §6.2 pose comme invariant que *le nombre de contenus portant
> `_mtb_robots_source`, le nombre rendus `noindex` et le nombre retirés du plan du site sont ÉGAUX*.
>
> **Pourquoi elle valait.** Parce qu'une **seule** source produisait les trois nombres : la méta
> `_mtb_robots_source`, lue par `marquer_noindex()` pour la directive et par `ecarter_les_noindex()`
> pour le plan du site. L'égalité était la façon de réconcilier l'**asymétrie** que `fait.php` documente
> — le filtre `wp_robots` lit la **valeur** de la méta, le retrait du plan du site teste son
> **existence** —, asymétrie sûre tant que rien d'autre n'agissait sur ces façades.
>
> **Ce qui la rompt.** Dès qu'une règle vivante se superpose, l'égalité meurt et l'asymétrie devient une
> divergence réelle entre ce que la balise dit et ce que le plan du site fait. C'est précisément
> pourquoi #52 **convertit** au lieu de faire cohabiter (§6) : après conversion, il y a **une clé, une
> valeur, une règle, trois façades d'accord**.
>
> **Cet invariant cesse donc d'être vrai le 2026-09-07**, et ce n'est **pas** une régression : le fait
> hérité ne **produit** plus la directive, il a été **converti** en une règle que l'éleveuse pilote. Il
> est remplacé par **deux nombres distincts** :
> - **5** — contenus portant `_mtb_robots_source`. Constante historique, mesurée par l'étape 6 de
>   `wp mtb verifier-redirections` (`CONTENUS_NOINDEX_ATTENDUS`), qui reste **verte** parce qu'elle
>   compte la **méta en base** et n'a jamais lu la balise rendue (vérifié :
>   `redirections-301/commande.php:301-338`) ;
> - **n** — contenus portant `_mtb_en_sommeil = '1'`. **Nombre vivant, piloté par l'éleveuse**, égal à 5
>   au lendemain de la conversion et destiné à changer.
>
> Le **contrôle 2 du §10 de `issue-24.md`** (balise `robots` des cinq, et leur absence du plan du site)
> **n'est plus vrai qu'à moitié, et il faut le dire ainsi** : **vrai** de l'absence du plan du site,
> **faux** de la balise — voir 11.4. `migration/redirections-301/**` **n'est pas ouvert par #52** et
> `CONTENUS_NOINDEX_ATTENDUS` n'est pas touchée.

**11.4 — Relève de ce que l'arbitrage A3 rend faux dans deux documents gelés.**
> A3 fait passer la directive servie aux cinq contenus repris de `noindex, nofollow` à ce que rend
> `wp_robots_no_robots()` — soit **`noindex, follow`** quand `blog_public` vaut 1 (mesuré le
> 2026-09-07 : `max-image-preview:large, noindex, follow`). **Trois passages en deviennent faux. Ils ne
> sont pas réécrits — décision 66, un contrat gelé se relève et se date, il ne se corrige pas :**
>
> | Passage | Ce qu'il dit | Ce qui est vrai depuis le 2026-09-07 |
> |---|---|---|
> | `docs/contracts/issue-24.md:391` | l'état `contenu_noindex` promet « `noindex` **+ `nofollow`** rendus » | `noindex` **+ `follow`** (selon `blog_public`) |
> | `docs/contracts/issue-24.md:891-892` | contrôle 2 du §10, chaîne mesurée `<meta name='robots' content='max-image-preview:large, noindex, nofollow' />` | `…, noindex, follow` — **la moitié « absence du plan du site » du même contrôle reste vraie** |
> | `docs/migration/redirections.md:254-255` | même chaîne périmée | idem |
>
> **Le danger que cette relève ferme, et il est nommément celui d'A3** : une chaîne future rejouera ce
> contrôle, mesurera `follow`, lira le contrat gelé, conclura à une régression et **« réparera » très
> exactement ce qu'A3 a voulu faire**. Sans cette relève, l'écart le mieux motivé du lot serait défait
> par un contrat qui a raison sur le papier et tort dans le dépôt.
>
> **Ce qui n'a PAS changé, et qu'il ne faut pas relever par confusion** : le **fait recopié**
> `_mtb_robots_source` porte toujours `noindex, nofollow` **avec sa provenance**, intact en base
> (mesuré sur Halan : `noindex, nofollow` / `html/chien-halan.html`). Seule la **directive servie**
> s'aligne sur la règle. Le contrat #24 dit toujours la vérité sur **ce que la source déclarait** ; il
> ne dit plus la vérité sur **ce que le site rend**.

---

## 12. Interdits opposables aux chaînes futures

- **Ne jamais recopier la chaîne `'_mtb_en_sommeil'`** : passer par `MTB\Core\Query\MiseEnSommeil\CLE`.
- **Ne jamais lire l'état par `get_post_meta()` directement** : `est_en_sommeil()` porte le repli
  ouvert.
- **Ne jamais tester l'absence autrement que par `metadata_exists()`** : c'est le seul test qui sépare
  « absente » de `'0'`, donc la seule chose qui empêche la conversion de rendormir ce qu'elle a
  réveillé.
- **Ne jamais poser de `default` sur cette méta.**
- **Ne jamais remplacer `$args` dans un filtre de plan du site** ; muter des clés — et **ne jamais y
  poser une `meta_query` en `OR` mêlant `NOT EXISTS` et une comparaison négative** : le `INNER JOIN` du
  cœur retire alors tout contenu sans aucune ligne de `wp_postmeta`, en silence (§5).
- **Ne jamais poser de garde d'inclusion sur `query/mise-en-sommeil/` ni sur `fields/sommeil/`** ; ne
  jamais retirer celle de `admin/sommeil/`.
- **Ne jamais réintroduire un `wp_robots` ni un filtre de plan du site lisant `_mtb_robots_source`** :
  le fait est converti, il n'agit plus. **Ne jamais réécrire ni effacer `_mtb_robots_source`**
  (décision 55).
- **Ne jamais retirer l'un des rappels de `wp_robots` en croyant dédoublonner** : questions distinctes,
  ensembles distincts, provenances distinctes.
- **Ne jamais toucher `ecarter_le_fournisseur_utilisateurs()`** sans être #49 ou #50.
- **Ne jamais écrire, ni en code ni en commentaire ni au guide, le motif pour lequel les cinq contenus
  dorment** (décision 72, Q23 ouverte).
- **Ne jamais retirer un contenu endormi de « La meute », de l'index des portées, de l'encart d'accueil
  ou d'un menu** tant que Q-52-1 n'est pas tranchée : le sommeil ne touche pas les listes du site.
- **Ne jamais faire dépendre la directive `robots` du visiteur** (`post_password_required()`,
  `is_user_logged_in()`, cookie) : la condition porte sur le **contenu**, sous peine d'empoisonnement de
  cache — arbitrage déjà rendu à `page-protegee`.

---

## 13. Protocole de vérification

**Décision 71, impérative** : toute mesure HTTP portant un caractère non-ASCII se joue **depuis le
conteneur**, jamais depuis le shell Windows — sinon on mesure des adresses qui n'ont jamais existé et on
casse du code juste en croyant le réparer. Toutes les commandes sont préfixées
`docker compose exec -T wpcli sh -lc '…'`.

Le protocole complet, contrôle par contrôle avec sa commande exacte, est celui du plan back, repris
sans retrait. Ses points **obligatoires**, dont aucun ne peut être déduit :

| Groupe | Ce qu'il prouve | Points bloquants |
|---|---|---|
| **P** | préalables et **mesure de référence avant le code** — sans elle, on ne saura jamais si le filtre a agi ou si l'index était déjà vide | P2 (`php -l` **avant** toute mesure HTTP), P5 |
| **Z** | **inclusion** — le seul témoin direct que les modules sont chargés, par **nom de rappel** et non par crochet | Z1, Z2, **Z6** (les deux rappels retirés rendent `bool(false)`), **Z7** (#49/#50 intacts), Z9 (`mtb_core_empreinte` **identique**), Z10 (`debug.log` **vide**), Z11 (`git status` : aucun fichier hors empreinte) |
| **C** | la conversion, y compris dans **les deux ordres** | **C4** (idempotence : un réveil n'est jamais défait), **C5** (l'ordre inverse), C6 (`wp mtb verifier-redirections` exit 0, étape 6 « 5 (attendu 5) ») |
| **B** | la balise `robots`, **comparée à P5** — la présence absolue n'est pas le verdict | B2 (témoin non endormi), B4 (non-régression #23) |
| **S** | le plan du site | **S5 — le piège de la jointure** : une page sans aucun champ **doit être** au plan du site. C'est le contrôle qui aurait attrapé la forme écartée en A1 |
| **R** | recherche, flux, **et son écran à elle** | **R4 — mode de panne n° 1** : en session éditrice, `edit.php?post_type=mtb_chien&s=halan` doit **toujours** rendre Halan, avec sa mention |
| **L** | **la promesse centrale** : le lien direct s'ouvre en **200** pour les **trois** types | L1, L2, L3 · L4/L5 (les listes du site ne bougent pas) |
| **E** | les écrans, et **le cycle complet** | **E4** — navigateur réel piloté en CDP, seule sonde qui prouve que le panneau de l'éditeur de blocs existe **et écrit** ; **E7** — endormir → réveiller → l'état revient **et ne se rendort pas** : la preuve de bout en bout que T108 est payée |
| **A** | Q24, doctrine #32 | **A1 — mesuré au navigateur réel**, deux écrans, 1440 px et 360 px. **Un verdict déduit est refusé** |

**La panne de `query/mise-en-sommeil/` est invisible** : il ne rend rien, n'imprime rien, ne journalise
rien, n'écrit rien. S'il cesse d'être chargé, un contenu endormi redevient indexable, revient au plan du
site et à la recherche — **la page répond 200 et `debug.log` reste vide.** **La panne du panneau de
l'éditeur de blocs est muette aussi** : le panneau disparaît, la page s'enregistre, rien au journal.
Ce protocole est leur **seul** témoin, et il se rejoue **en entier** à chaque livraison touchant ces
fichiers.
