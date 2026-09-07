# Contrat d'interface — Issue #53 — Faire mesurer par `verifier-redirections` l'effet de la conversion, pas la présence de la clé (T109)

Gelé le 2026-09-08. Domaine `seo`. Milestone 15, lot 20.

**Aucun thème en face.** Cette issue ne touche ni gabarit, ni bloc, ni feuille de style, ni `theme.json`.
Il n'y a donc pas deux moitiés à réconcilier : ce contrat gèle la forme du **livrable serveur**, ses
**verdicts**, ses **chaînes françaises** et ses **interdits**. `leaddev-front-mtb` n'a pas été lancé, et
c'est un choix, pas un oubli.

**Rien de visible pour l'éleveuse.** Aucun écran, aucun libellé d'administration, aucune donnée, **aucune
fiche de guide** (`docs/guide/` n'est pas ouvert). Livrable strictement interne, joué à la main en recette.

---

## 0. La dette, en une phrase

L'étape 6 de `wp mtb verifier-redirections` compte la clé `_mtb_robots_source` **en base** et reste
**verte même si `convertir()` n'a jamais couru** — donc muette sur toute base restaurée par copie SQL,
c'est-à-dire muette exactement le jour où ça compterait. **Un contrôle qui peut répondre « tout va bien »
sans avoir rien observé n'est pas un contrôle, c'est un décor.**

---

## 1. L'approche retenue, et les trois écartées

**Retenue — « verdict d'état + effet mesuré + contraste attribuant ».** L'étape rend un verdict **par
contenu** sur l'état en base, puis mesure l'**effet** sur la seule façade honnêtement mesurable en
processus — le plan du site que le cœur fabrique —, puis **attribue** cet effet à notre rappel par une
mesure de contraste.

| Écartée | Motif du rejet |
|---|---|
| **État seul** | Paie la dette nommée, mais laisse le titre de l'issue à moitié faux : « mesurer l'effet » deviendrait « mesurer la présence d'une **autre** clé ». |
| **Effet sans contraste** | Livre un contrôle **qu'on ne sait pas mettre au rouge** sur sa moitié « effet ». Disqualifiant sur une issue qui existe précisément parce qu'un vert ne prouvait rien. |
| **Simuler `is_singular()` et remplacer la requête principale** pour mesurer aussi `wp_robots` et la recherche interne | **Rejetée.** Elle **forge les gardes mêmes qu'elle prétend vérifier**, et resterait verte sur une page ayant perdu `wp_head`. C'est la dette reproduite un cran plus haut. |

---

## 2. Les trois natures de contrôle — la doctrine passe de deux à trois

L'en-tête de `commande.php` gelait deux natures qui « ne se présentent jamais l'une pour l'autre ».
**#53 en ajoute une troisième, au milieu**, et la règle vaut pour les trois :

1. **Cohérence de table** — ce que le dépôt déclare (étapes 0 à 5 et 7). Ne dépend d'aucune règle chargée.
2. **Règle chargée et produisant son effet** — l'étape 6 bis, **en processus**, sur les listes que le cœur
   fabrique. Elle mesure un **effet**, jamais la présence d'une clé : une clé présente dont l'effet est
   absent la fait **échouer**. Elle **déclare elle-même, à chaque exécution, ce qu'elle ne mesure pas**.
3. **Ce que le serveur répond** — le « curl », et les groupes B et R du protocole du §13 du contrat #52.
   **Hors de cette commande, à jamais.**

**Périmètre refusé, et imprimé à chaque exécution** : la balise `robots` du HTML servi et la recherche
interne **ne sont pas mesurées** par cette étape. Un témoin qui déclare son propre périmètre est la seule
forme de témoin qui ne redevient pas une dette.

---

## 3. Emplacement — étape « 6 bis », nichée

Une fonction privée appelée en fin de `etape_6_noindex()`, sur le précédent du fichier lui-même :
l'**étape 3 bis**, de nature différente, est déjà nichée dans `etape_3_et_4_cibles()`. **Pas d'étape 8** ;
`verifier()` n'est pas modifiée hormis sa phrase de succès.

---

## 4. La population mesurée — la borne qui protège le troisième état

> **Population = « porte `_mtb_robots_source` » ET « `demande_noindex()` est vrai ».**

C'est **exactement** la borne de `convertir_un()`. Conséquence garantie, et c'est le but : **une page
ordinaire du site — sans le fait hérité, sans `_mtb_en_sommeil`, « jamais réglée » au troisième état —
n'entre jamais dans la population et ne peut jamais rougir.** La borne est la clé héritée, pas l'état de
sommeil.

La requête conserve **exactement** `post_type => any` et `post_status => any`, les bornes de
`a_convertir()`. Toute autre borne rougirait sur des contenus que la conversion ne voit
**structurellement jamais**.

---

## 5. Fonctions — forme exacte

```
// signature INCHANGÉE, corps augmenté d'un appel final
MTB\Core\Migration\Redirections301\etape_6_noindex( array &$echecs ): void

// nouvelles, privées au fichier, jamais appelées par le thème
etape_6_bis_effet( array $porteurs, array &$echecs ): void
sonde_des_modules( array &$echecs ): bool
population_convertible( array $porteurs ): array
    // array<int, array{ id:int, titre:string, adresse:string, type:string, statut:string,
    //                   protege:bool, etat:'absente'|'endormi'|'indistinct', octet:string }>
octet_stocke( int $identifiant ): string
plan_du_site_mesurable( array &$echecs ): ?\WP_Sitemaps_Provider
chemins_du_fournisseur_posts( \WP_Sitemaps_Provider $posts, string $etiquette ): array
    // array<string, true> — chemins normalisés par normaliser_chemin()
adresses_attendues( array $sous_types ): array
    // array<string, int> — chemin normalisé => identifiant de l'endormi éligible
```

Toutes dans `namespace MTB\Core\Migration\Redirections301`, `declare(strict_types=1)`.

**Aucune fonction `mtb_*` globale. Aucune fonction de lecture exposée au thème. Aucun bloc enregistré.**

### Constantes lues, jamais recopiées (§12 du contrat #52)

`MTB\Core\Query\MiseEnSommeil\CLE` · `MTB\Core\Migration\IndexationHeritee\CLE`, toujours derrière
`defined()`. Les verdicts passent par `est_en_sommeil()` et `metadata_exists()`, **jamais** par
`get_post_meta()`.

**Recopie fermée au passage** : la chaîne littérale `'_mtb_robots_source'` de la `meta_query` de l'étape 6
cède la place à `\MTB\Core\Migration\IndexationHeritee\CLE`, **placée après la sonde d'existence**. Prix
dit ouvertement : l'étape 6 **cesse de compter** quand `indexation-heritee` est désactivé, et **échoue en
le disant** au lieu d'imprimer sereinement « 5 (attendu 5) » alors que plus rien ne convertit. **C'est un
gain, pas un coût** — un vert sans convertisseur est le mensonge que T109 nomme.

---

## 6. La table des verdicts — état par état

Deux moitiés **indépendantes** : l'**état** (toujours mesuré) et l'**effet** (mesuré si et seulement si le
contenu est éligible : publié **et** non protégé **et** d'un type servi par le fournisseur `posts`).

| Cas | Moitié **état** | Moitié **effet** | Motif |
|---|---|---|---|
| `_mtb_en_sommeil` **absente** | **ROUGE** `conversion_jamais_jouee` | non tentée | La conversion n'a jamais couru sur cette base. C'est **l'état d'une base restaurée par `wp db import`**, et le message porte **son remède**. |
| `'1'` (`est_en_sommeil()`) | **VERT** | **assertion dure** : son adresse doit être dans la différence | Le seul cas où l'effet est concluant. |
| **ni absente ni `'1'`** | **AVERTISSEMENT** `etat_indistinct` — **jamais rouge** | assertion faible → avertissement | Le seau mêle le **réveil explicite `'0'`, décision de l'éleveuse, sacrée**, et la valeur abîmée. **Le témoin dit qu'il ne sait pas les séparer** et imprime l'octet stocké pour qu'un humain tranche. Rougir ici ferait d'une décision de l'éleveuse une erreur. |
| **non publié** | rendu normalement | **hors mesure d'effet**, ligne imprimée | Le plan du site ne liste que `publish`, `identifiants_en_sommeil()` non plus : l'absence ne prouve rien. |
| **protégé par mot de passe** | rendu normalement | **hors mesure d'effet**, motif nommé | `query/page-protegee` le retire **dans les deux passes** : le contraste ne peut pas l'attribuer, donc on ne conclut pas. |
| porte la clé **sans « noindex »** | **hors population**, ligne d'information | sans objet | `convertir_un()` ne l'aurait jamais visé. Rougir serait inventer une règle que la conversion n'a jamais eue. |
| type non servi par `posts` | rendu | **hors mesure d'effet** | Théorique aujourd'hui ; gardé parce qu'un type futur le rendrait vrai **en silence**. |

---

## 7. La mesure d'effet — la vraie liste que le cœur fabrique

Aucun nombre supposé ; tout est produit par le cœur, en processus, **sans aucune requête réseau** (D6).

- `wp_sitemaps_get_server()`, précédé du relevé `isset( $GLOBALS['wp_sitemaps'] )` **imprimé** : on dit ce
  qu'on a provoqué (dette T97 — aucun nombre sans sa recette).
- **Façade éteinte** : `$serveur->sitemaps_enabled()`, la **méthode du cœur**, jamais une re-dérivation de
  `blog_public`. Rend `false` → **avertissement, pas échec** : la moitié « effet » est déclarée **NON
  MESURÉE** et la moitié « état » continue.
- Fournisseur **`posts` seul**, gardé par `instanceof \WP_Sitemaps_Provider`.
- Toutes les pages balayées (`get_max_num_pages()` puis `get_url_list()`), **jamais la seule page 1** : le
  jour où le site dépassera `wp_sitemaps_max_urls`, une lecture partielle deviendrait fausse **en
  silence**. Pages et adresses **imprimées par sous-type et par passe**.
- Comparaison sur des **adresses**, jamais des identifiants : `get_url_list()` rend des `loc`. Les deux
  côtés sont réduits par **`normaliser_chemin()` de `redirections-301/chemin.php`** — le normalisateur du
  module lui-même, jamais un second.

---

## 8. La sonde de contraste — bornée, rétablie, opposable

Mesure de la même liste **avec** puis **sans**
`MTB\Core\Query\MiseEnSommeil\exclure_du_plan_du_site` ; la **différence** doit être exactement l'ensemble
attendu. C'est ce qui **attribue** l'effet à notre règle plutôt que de constater une corrélation.

**Quatre gardes, toutes obligatoires :**

1. **Un seul crochet touché**, `wp_sitemaps_posts_query_args`. **Jamais `wp_robots`** — trois contrats
   (#23, #24, reconduits par le §12 de #52) interdisent nommément d'en retirer un rappel. *« On ne le
   retire qu'une seconde, pour mesurer »* est le premier pas exact de cette faute : la tentation est
   **nommée pour être fermée**, pas pour être décrite.
2. **Retrait par nom pleinement qualifié, jamais par crochet.** `query/page-protegee` accroche un
   **homonyme** au même crochet à la même priorité ; un retrait par crochet emporterait les deux et
   **attribuerait à notre règle un effet qui n'est pas le sien**.
3. **Rétablissement en `finally`, puis ré-assertion `has_filter()` imprimée.** Échec du rétablissement →
   **`WP_CLI::error()`, échec dur** : un témoin qui laisse le processus amputé ment au suivant.
4. Priorité **relue par `has_filter()`**, jamais `10` recopié ; `accepted_args = 1`, à l'identique de la
   déclaration d'origine. Passe nominale **d'abord**, passe amputée ensuite.

**Deux faits vérifiés sur le code, et non supposés :**
- **La liste des endormis est la même dans les deux passes** — `identifiants_en_sommeil()` mémoïse par
  processus, sans invalidation ni crochet de purge. **Seule la présence du rappel change**, et c'est ce
  qui rend la différence attribuable.
- **L'ordre dans le seau de priorité change et c'est sans effet** — le rappel rétabli passe après
  l'homonyme ; la convention de cohabitation gelée aux contrats #23/#24 garantit un résultat identique
  dans les deux ordres, les deux rappels mutant des clés disjointes (`has_password` / `post__not_in`)
  sans lire celle de l'autre.

### Verdicts du contraste

| Écart | Verdict | Code |
|---|---|---|
| adresse **attendue** absente de la différence | **ROUGE** | `effet_absent` |
| adresse **présente** dans la différence sans appartenir à un endormi éligible | **ROUGE** | `effet_hors_perimetre` |
| différence **exactement égale** à l'ensemble attendu | **VERT**, deux nombres imprimés | — |

`adresses_attendues()` couvre **tous** les endormis, pas seulement les cinq contenus repris : le rappel
les retire tous, l'assertion doit donc les attendre tous — sans quoi elle rougirait dès le premier sommeil
décidé par l'éleveuse.

---

## 9. La sonde d'existence — le module mort et silencieux

`indexation-heritee` peut être désactivé par le renommage documenté en `_indexation-heritee`.
**Aucun `require_once` n'est ajouté** : l'étape s'exécute au dispatch de la commande, donc **après** le
chargement de tous les modules ; un `require_once` n'apporterait rien qu'une **erreur fatale** le jour du
renommage, et ferait dépendre `redirections-301` de l'ordre de parcours des groupes.

Sonde par **nom de symbole et nom de rappel** (idiome Z1 du §13 de #52, figure du §10 de #24), avec **deux
échecs distincts — et la distinction est le cœur de l'issue** :

- **`temoin_indisponible:<symbole>`** — le module est absent : *« le témoin NE PEUT PAS MESURER »*. Ni
  erreur fatale, ni vert silencieux.
- **`rappel_non_accroche`** — les fonctions existent mais le rappel n'est pas accroché : *« la règle est
  chargée et n'agit plus »*.

---

## 10. La lecture brute — affichage de diagnostic, jamais un verdict

`octet_stocke()` n'est appelée que dans la branche `etat_indistinct`.

**Pourquoi ce n'est pas la lecture que le §12 du contrat #52 interdit** : le mal que §12 ferme est qu'un
contenu « se lise endormi à un endroit et visible à un autre ». Ici **rien n'est dérivé** — aucune branche,
aucune conséquence, aucun verdict : **on affiche**. Le verdict reste `est_en_sommeil()` et
`metadata_exists()`, sans exception.

**Garde `is_scalar()` obligatoire avant tout cast** : `etat.php` documente qu'un `(string)` sur une méta
sérialisée lève « Array to string conversion », donc **une ligne de journal par affichage** — et cette
commande ne doit **rien** journaliser. Non scalaire → le **type** (`gettype()`), jamais la valeur.
Scalaire non imprimable → hexadécimal borné. Scalaire imprimable → valeur bornée à 40 octets.

---

## 11. Chaînes françaises imprimées — verbatim, elles sont l'interface du livrable

| Situation | Texte exact |
|---|---|
| En-tête | `── Étape 6 bis — la conversion a-t-elle produit son effet ?` |
| Périmètre (à **chaque** exécution) | `   Périmètre : cette étape mesure l'ÉTAT en base et l'EFFET sur le plan du site que le cœur fabrique en processus. Elle ne mesure NI la balise « robots » du HTML servi, NI la recherche interne du site : celles-là se mesurent au « curl » et par les groupes B et R du protocole du §13 du contrat #52.` |
| Ce que ceci prouve | `   Ce que ceci prouve : la règle est chargée, accrochée, et elle retire bien du plan du site les contenus convertis. Ce que ceci ne prouve pas : ce que le serveur répond à un visiteur.` |
| Population | `   Population mesurée : %d contenu(s) portant le fait hérité ET dont « demande_noindex() » est vrai, sur %d porteur(s) de la clé.` |
| Hors population | `   « %s » (#%d) — porte le fait hérité SANS directive « noindex » : hors population, la conversion ne l'a jamais visé.` |
| Serveur | `   Serveur du plan du site : %s.` — `déjà instancié par le cœur` \| `instancié par cette étape` |
| État converti | `   « %s » (#%d) — état : en sommeil. Converti.` |
| **ROUGE** conversion jamais jouée | `Erreur (étape 6 bis) : « %s » (#%d) porte le fait hérité et demande « noindex », mais AUCUNE valeur « en sommeil » n'a jamais été écrite : « convertir() » n'a pas couru sur cette base. Remède : ouvrir une page de l'administration une seule fois — le rattrapage court sur « admin_init » —, puis rejouer cette commande. C'est l'état normal d'une base restaurée par copie SQL : ce n'est pas le témoin qui est cassé.` |
| **AVERTISSEMENT** état indistinct | `Avertissement (étape 6 bis) : « %s » (#%d) porte une valeur « en sommeil » qui n'est ni absente ni « 1 » — soit un réveil explicite décidé par l'éleveuse, soit une valeur abîmée. CE TÉMOIN NE SAIT PAS LES SÉPARER et ne tranche pas. Valeur stockée, pour qu'un humain tranche : %s.` |
| Hors effet — non publié | `   « %s » (#%d) — hors mesure d'effet : non publié. Le plan du site ne liste que le contenu publié.` |
| Hors effet — protégé | `   « %s » (#%d) — hors mesure d'effet : protégé par mot de passe. « query/page-protegee » le retire du plan du site dans LES DEUX passes ; son absence ne prouve rien de notre règle.` |
| Hors effet — type non servi | `   « %s » (#%d) — hors mesure d'effet : son type « %s » n'est pas servi par le fournisseur « posts » du plan du site.` |
| Passe, par sous-type | `   Plan du site, fournisseur « posts », sous-type « %s » : %d page(s), %d adresse(s) — %s.` — `avec le rappel` \| `sans le rappel` |
| Contraste | `   Contraste : %d adresse(s) présentes sans le rappel et absentes avec ; %d attendue(s).` |
| Rétablissement | `   Rappel rétabli : « has_filter() » rend la priorité %d.` |
| **ÉCHEC DUR** rétablissement | `Le rappel « %s » n'a PAS pu être rétabli après la sonde de contraste. Le processus est amputé : aucune autre mesure de cette exécution ne veut plus rien dire.` |
| **ROUGE** effet absent | `Erreur (étape 6 bis) : « %s » (#%d) est en sommeil, publié et non protégé, et pourtant son adresse « %s » ne disparaît PAS du plan du site quand on retire le rappel. La clé est posée, l'effet est absent.` |
| **ROUGE** effet hors périmètre | `Erreur (étape 6 bis) : l'adresse « %s » disparaît du plan du site quand le rappel agit, alors qu'elle n'appartient à aucun contenu en sommeil. Le rappel retire plus que ce qu'il annonce.` |
| **AVERTISSEMENT** présence attendue absente | `Avertissement (étape 6 bis) : « %s » (#%d) n'est pas en sommeil, il est publié et non protégé, et pourtant son adresse est absente du plan du site. Ce témoin ne dit pas pourquoi : un autre rappel du même crochet peut en être la cause.` |
| **AVERTISSEMENT** façade éteinte | `Avertissement (étape 6 bis) : le plan du site est DÉSACTIVÉ sur ce site (« blog_public » à 0, ou le filtre « wp_sitemaps_enabled »). La moitié « effet » de cette étape N'A PAS ÉTÉ MESURÉE : il n'y a aucune façade à mesurer. Ne pas lire l'absence d'erreur comme une preuve.` |
| **ROUGE** témoin indisponible — **symbole d'un module `mtb-core`** | `Erreur (étape 6 bis) : « %s » est introuvable. Le témoin NE PEUT PAS MESURER — le module concerné est probablement désactivé (dossier renommé avec un souligné initial). Ce n'est pas un verdict sur les contenus.` |
| **ROUGE** témoin indisponible — **fournisseur `posts` du cœur** | `Erreur (étape 6 bis) : le fournisseur « posts » du plan du site est introuvable, alors que le plan du site est actif. Le témoin NE PEUT PAS MESURER l'effet — un rappel sur « wp_sitemaps_add_provider » l'a probablement retiré. Ce n'est pas un verdict sur les contenus.` |
| Étape 6 **non mesurée** | `── Étape 6 — NON MESURÉE : « migration/indexation-heritee » est absent, sa clé n'est donc pas déclarée. Rien n'est compté ici ; l'étape 6 bis nomme le symbole manquant et échoue.` |
| **ROUGE** rappel non accroché | `Erreur (étape 6 bis) : le rappel « MTB\Core\Query\MiseEnSommeil\exclure_du_plan_du_site » n'est PAS accroché à « wp_sitemaps_posts_query_args ». La règle est chargée et n'agit plus : tout contenu endormi est de retour au plan du site, et rien d'autre ne le dirait.` |
| Adresse illisible | `   %d adresse(s) du plan du site n'ont pas pu être normalisées et sont écartées de la comparaison.` |

**La ligne « %d (attendu %d) » de l'étape 6 est INCHANGÉE au caractère près** : le contrôle **C6** du
protocole gelé de #52 attend la sous-chaîne « 5 (attendu 5) ». Y toucher casserait un protocole gelé.

---

## 12. Ce que la commande continue de garantir

> **« CETTE COMMANDE N'ÉCRIT RIEN — ni option, ni contenu, ni méta »** (borne 1 de l'amendement au §2 du
> contrat #1). **Préservé, sonde de contraste comprise.**

**Une mutation de filtre n'est pas une écriture** : elle vit dans `$GLOBALS['wp_filter']`, en mémoire,
dans un processus WP-CLI **qui ne sert aucun visiteur** ; elle est rétablie avant la fin de la fonction,
avec ré-assertion imprimée, et rien n'en subsiste après la sortie du processus. **La borne 1 vise la
persistance d'un état ; ce geste n'en crée aucun.**

**Effet de bord assumé et déclaré** : `wp_sitemaps_get_server()` **instancie** le serveur du plan du site
s'il ne l'était pas, ce qui déclenche `wp_sitemaps_add_provider` — donc
`ecarter_le_fournisseur_utilisateurs()`, **lu, jamais modifié**. La ligne « Serveur du plan du site : … »
le dit à chaque exécution.

**Aucune requête réseau** : le plan du site est **construit en processus**, jamais récupéré en HTTP (D6).

---

## 13. Empreinte fichiers réelle

**Écrit** : `wp-content/plugins/mtb-core/includes/migration/redirections-301/commande.php` et ce contrat.
**Rien d'autre.** `normaliser_chemin()` existait déjà — aucun fichier nouveau n'a été nécessaire.

Deux chaînes du même fichier hors « étape 6 » stricte, dont **le lead de lot accorde nommément
l'ouverture**, parce que les laisser inexactes serait un mensonge de trois mots dans le fichier même dont
l'issue répare l'honnêteté :

| Ligne | Avant | Après |
|---|---|---|
| docbloc de `verifier()` | `Exécute les huit étapes de vérification.` | `Exécute les huit étapes de vérification, dont deux sous-étapes — 3 bis et 6 bis.` |
| phrase de succès | `… ancres et contenus non indexés.` | `… ancres, état des contenus repris et effet mesuré sur le plan du site.` |

**Le compte « huit » ne bouge pas, et c'est délibéré** : la 3 bis est nichée et non comptée depuis
l'origine, la 6 bis l'est aussi. Écrire « neuf » romprait la convention du fichier **et contredirait le
`longdesc` de `bootstrap.php`, que cette chaîne n'ouvre pas**.

### Extension d'empreinte ACCORDÉE par le lead de lot — `redirections-301/bootstrap.php:78-79`

**Elle sort de l'empreinte publiée de l'issue : elle se lit ici, elle ne se devine pas.**

La chaîne avait d'abord **refusé** d'écrire ce fichier (arbitrage A5 ci-dessous) et remonté le texte au
lead, l'empreinte n'ouvrant un autre fichier que pour « un service qui n'existe pas encore ». **Le lead
de lot a accordé l'extension**, avec ce motif, qui est celui de l'issue elle-même :

> `--help` annonce, **avant** qu'on joue la commande, une étape 6 décrite pour ce qu'elle n'est plus et
> une doctrine restée à deux natures. Laisser cela reconduirait au niveau de l'aide le défaut que T109
> répare au niveau du témoin : **un texte qui affirme plus que ce qu'il couvre.** La contrainte « un
> témoin doit prouver ce qu'il prétend prouver » vaut aussi pour ce que la commande **prétend** dans son
> aide.

**Bornes strictes de l'extension** : les **deux lignes 78-79 seulement** — la phrase des natures et
l'énumération des étapes. **Aucune ligne exécutable**, aucun changement d'enregistrement de la commande,
rien d'autre dans ce fichier. Le fichier est **disjoint** de `indexation-heritee/bootstrap.php`, que la
chaîne #50 a touché : deux fichiers distincts dans deux modules distincts, aucune collision.

**Le compte « Huit » ne bouge pas ; l'amorce annonce désormais ses deux sous-étapes.** Texte livré :
« **Huit étapes, dont deux sous-étapes — 3 bis et 6 bis :** … ». Le lead de lot a **recompté** la phrase —
elle porte les étapes **principales 0 à 7**, et `3 bis` n'y était **déjà pas** compté, donc ajouter `6 bis`
ne la change pas — puis a retenu la forme longue, au motif qu'elle est **plus exacte que celle d'avant,
`3 bis` y étant déjà tue**. On n'écrit **jamais** « neuf ».

*Trace d'un aller-retour, gardée parce que c'est le genre de chose qu'on refait sinon* : la clause a été
écrite, retirée sur une première consigne (« n'y touche pas »), puis rétablie sur la seconde. **Le compte
n'a jamais été en cause** ; seul l'aveu des sous-étapes l'était.

---

## 14. Interdits opposables aux chaînes futures

1. **Ne jamais toucher `wp_robots` dans une sonde**, ni pour mesurer, ni « une seconde ».
2. **Ne jamais retirer un filtre par crochet** sur `wp_sitemaps_posts_query_args` — l'homonyme de
   `page-protegee` y est au même rang.
3. **Ne jamais copier la sonde de contraste hors d'un outil de recette.** Sur une requête de front, elle
   produirait exactement la panne que `query/mise-en-sommeil` existe pour empêcher : un contenu endormi de
   retour dans un index, sur une page qui répond 200 et un journal qui reste vide.
4. **Ne jamais dériver un verdict d'un `get_post_meta()`** ; `octet_stocke()` est un **affichage**, et le
   jour où quelqu'un en tirera une branche, il aura rouvert la faute que le §12 de #52 ferme.
5. **Ne jamais faire rougir le troisième état** (`ni absente ni '1'`) : `'0'` est une décision de
   l'éleveuse, et elle n'est jamais défaite.
6. **Ne jamais élargir la population** au-delà de « porte la clé **ET** `demande_noindex()` » : c'est cette
   borne, et elle seule, qui garantit qu'une page ordinaire jamais réglée ne rougit pas.
7. **Ne jamais changer les bornes `post_type => any` / `post_status => any`** : elles copient
   `a_convertir()`.
8. **Ne jamais assérer quoi que ce soit sur le fournisseur `users`** — terrain de #49 et #50.
9. **Ne jamais réécrire ni effacer `_mtb_robots_source`** (décision 55).
10. **Ne jamais retoucher la sous-chaîne « %d (attendu %d) » de l'étape 6** : C6 du §13 de #52 en dépend.
11. **Ne jamais supposer une seule page de plan du site.**
12. **Ne jamais faire écrire cette commande** — option, contenu, méta, fichier, réseau.

---

## 15. Arbitrages rendus

| # | Désaccord ou question ouverte | Décision | Raison |
|---|---|---|---|
| **A1** | Séparer le réveil explicite `'0'` d'une valeur abîmée exigerait une fonction de lecture nouvelle dans `query/mise-en-sommeil/etat.php` — **hors empreinte**, et rouvrirait un module que #52 vient de geler | **Non.** Le témoin imprime un seau « réglé, non endormi » en **avertissement** et **dit qu'il ne sait pas trancher**, en affichant l'octet stocké sous garde `is_scalar()` | Honnête et gratuit. Rouvrir `etat.php` coûterait un module gelé pour un gain qu'un humain obtient d'un coup d'œil sur la ligne imprimée. |
| **A2** | Le rouge sur une base fraîchement restaurée par copie SQL est-il accepté ? | **Oui**, et la ligne d'erreur **porte son remède** en une phrase | C'est **l'objet même** de la dette. Sans le remède écrit, la première personne qui voit le rouge « réparera » le témoin. Note vérifiée : une base **provisionnée par WP-CLI** reste verte — l'import déclenche `added_post_meta` ; seule la **copie SQL** saute les crochets. |
| **A3** | Étape 6 bis nichée, ou étape 8 nouvelle ? | **6 bis nichée** | Le fichier a déjà cet idiome (3 bis). Respecte « étape 6 uniquement » à la lettre et satisfait « une étape de plus » à l'affichage. |
| **A4** | Plan du site désactivé (`blog_public = 0`) : échec ou avertissement ? | **Avertissement**, avec « NON MESURÉE » en toutes lettres et « ne pas lire l'absence d'erreur comme une preuve » | `blog_public = 0` **supprime la façade entière** : la promesse est alors vraie trivialement. Faire échouer la commande sur une recette légitimement fermée aux moteurs apprendrait à réparer le témoin. |
| **A5** | Ouvrir `redirections-301/bootstrap.php` pour corriger le `longdesc` de `--help` ? | **Non.** Refusé et remonté au lead de lot avec son texte exact | L'empreinte n'ouvre un autre fichier que pour « un service qui n'existe pas encore » ; une chaîne de documentation n'en est pas un. Dans un arbre partagé, l'empreinte est la seule protection, et le lead a un canal pour ces amendements. |
| **A6** | Fermer la recopie de `'_mtb_robots_source'` dans la `meta_query` de l'étape 6 ? | **Oui**, par `IndexationHeritee\CLE`, après la sonde d'existence | Ferme une recopie réelle et transforme un vert silencieux en échec parlant quand le convertisseur est absent. |
| **A7** | Un porteur de la clé sans `demande_noindex()` vrai : ajuster `CONTENUS_NOINDEX_ATTENDUS` ou la borne ? | **Ni l'un ni l'autre.** Le consigner au rapport, ne rien ajuster | Ce serait un fait de reprise à remonter, pas un paramètre à corriger (D11). |
| **A8** | Le refacto relève que la branche « fournisseur `posts` introuvable » réemploie la chaîne qui nomme « dossier renommé avec un souligné initial » — **cause fausse pour cette branche**, le fournisseur venant du **cœur**, jamais d'un dossier `mtb-core` | **Corrigé** : une chaîne propre, qui nomme la vraie cause (« un rappel sur `wp_sitemaps_add_provider` l'a probablement retiré ») | Un témoin qui nomme la mauvaise cause **envoie chercher au mauvais endroit** — le défaut exact que cette issue répare. Le corriger coûte une chaîne ; le taire coûte une heure à qui le lira. La chaîne ne nomme **pas** le fournisseur `users` : c'est le terrain de #49/#50. |
| **A9** | Quand `IndexationHeritee\CLE` n'est pas déclarée, l'étape 6 ne rendait **aucune ligne** — pas même son en-tête — dans une séquence numérotée de 0 à 7 | **Corrigé** : une ligne « ── Étape 6 — NON MESURÉE : … » | Rien n'était faussement vert (la sonde de 6 bis échoue et nomme le symbole), mais **une étape qui disparaît d'une séquence numérotée se lit comme une étape qui a passé**. Dire « non mesurée » coûte une ligne et ferme la dernière ambiguïté du livrable. |
