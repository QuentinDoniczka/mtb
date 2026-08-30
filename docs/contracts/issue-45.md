# Contrat d'interface — Issue #45 — Réparer deux dettes dans `query/portee/hydratation.php` (T58)

**Lot 12 · epic 11 « Dette technique — chargeur et permaliens » · gelé le 2026-08-30.**

Cette issue **ne touche pas le thème**. Elle n'expose **aucune fonction de lecture nouvelle**, **aucun
bloc**, **aucun champ**, **aucun type de contenu**, **aucune capacité**, et n'ajoute **aucun octet**
rendu au visiteur. Il n'y a donc **pas de contrat de frontière thème↔extension à négocier**, et
`leaddev-front-mtb` n'a pas été lancé.

Ce document existe pour une autre raison : **enregistrer les arbitrages**, dont un élargissement de
périmètre décidé par moi, et **borner par écrit ce que le correctif ne répare pas**. Sur ce projet,
trois lots consécutifs ont été bloqués par de la prose fausse et non par du code faux ; la partie la
plus importante de ce contrat est donc le §6 (impact honnête) et le §7 (dettes non traitées).

**Empreinte d'écriture** : `wp-content/plugins/mtb-core/includes/query/portee/hydratation.php`, et ce
fichier. **Rien d'autre.** Deux chaînes tournent en parallèle sur le même arbre de travail, sans
isolation : #35 possède `includes/admin/**` et `docs/guide/*.md`, #30 possède `compose.yaml`,
`docker/**` et `Makefile`.

**Tous les constats de ce contrat sont datés du commit `f0b05f1`**, relus par
`git show f0b05f1:<chemin>` et jamais par un `grep` ni un `git status` nu.

---

## 1. Vérification de l'énoncé de l'issue

L'issue avançait cinq faits précis. **Les cinq sont vrais** au commit `f0b05f1`, numéros de ligne
compris. Aucune requalification n'est nécessaire — c'est le premier lot depuis trois où l'énoncé du
donneur d'ordre tient intégralement.

| Fait avancé par l'issue | Vérifié à `f0b05f1` |
|---|---|
| `hydratation.php:57-63` redéclare les trois disponibilités | **Vrai** — `public static function disponibilites()` |
| « mot pour mot identique » à `champs.php:26-32` | **Vrai** — les trois paires au caractère près |
| `trier()` est en `hydratation.php:144-176` | **Vrai** |
| `trier()` compare par `strcmp` | **Vrai** — ligne 162 |
| `ordre.php:162-184` porte le test de lisibilité, posé par `fa80eb3` | **Vrai** — test en `:181-184`, doctrine en `:161-169` ; `fa80eb3` existe, touche `admin/listes/ordre.php` et `docs/contracts/issue-28.md` |

---

## 2. Périmètre — élargi de 2 à 4 instances, et pourquoi

Les « deux dettes distinctes » de l'issue ne sont pas deux dettes. Ce sont **quatre instances d'une
faute unique — recopier au lieu d'appeler — dans un seul fichier** :

| # | Instance | Dans l'issue ? |
|---|---|---|
| 1 | `disponibilites()` recopie la liste fermée de `champs.php` | **Oui**, tâche 1 |
| 2 | `trier()` recopie la notion de « date absente » au lieu de lire celle de `date_en_toutes_lettres()` | **Oui**, tâche 2 |
| 3 | `sexes()` recopie la liste fermée de `champs.php` | **Non** |
| 4 | `annee` (`:264`) dérive de la date **brute** et non de la date **lisible** | **Non** |

**Décision : les quatre sont traitées.** Motifs, dans l'ordre de force :

1. **Les quatre tiennent dans le fichier unique de l'empreinte.** C'est le point décisif :
   l'empreinte à un fichier existe pour protéger les chaînes #30 et #35, pas pour brider ce
   fichier-là. L'élargissement ne menace aucune chaîne sœur.
2. **La Contrainte 3 vise nommément le cas** : « le contenu structuré ne se recopie jamais — y
   compris entre deux fonctions de l'extension elle-même ».
3. **D12 vise littéralement l'instance 4** : « un contenu mal rempli ne doit pas se comporter
   différemment selon l'écran qui le lit ». L'administration ne propose pas d'année fantôme ; le
   réglage « Année » du composant *Liste de portées* si. C'est la **seule des quatre instances avec
   un symptôme visible par l'éleveuse**.
4. **Traiter 2 sur 4 obligerait ce contrat à écrire « le vocabulaire est aligné » puis « sauf
   `sexes()` », deux lignes plus bas.** Un défaut vu et non écrit est pire qu'un défaut non vu.

**Ce qui reste dehors, délibérément.** `hydratation.php` recopie aussi des libellés de fonction à
fonction *à l'intérieur de lui-même* (« Nom », « N° LOF », « Devenir » en `:49` et `:410-413` ;
« Née le » en `:215` et `:265` ; « Disponibilité » en `:216` et `:266` ; « Père »/« Mère » en
`:207-209` et `:270-271`). **Ce n'est pas la même faute**, et le critère qui les sépare est précis :
**ce ne sont pas des listes fermées** — aucune clé stockée en base n'en dépend, et **aucun
assainisseur ne les consulte**. Leur divergence éventuelle ne ferait pas admettre ou refuser une
valeur en base. La faute traitée ici est *une liste fermée dont les clés sont stockées en base et
validées par un assainisseur ailleurs, recopiée loin de son propriétaire*. Les dériver par position
de tableau rendrait le code moins lisible. Autre issue, autre remède.

> **Correction apportée après la passe de refacto, et elle importe.** Une première rédaction de ce
> paragraphe motivait l'exclusion par « ces libellés n'ont aucun autre propriétaire ». **C'était
> faux**, et vérifié faux : `content/portee/champs.php` déclare `'Disponibilité'` (`:63`), `'Père'`
> (`:93`), `'Mère'` (`:123`) et `'Nom'` (`:105`, `:135`) dans `catalogue()`, au commit `f0b05f1`.
> Seul « Née le » n'a effectivement aucun autre déclarant. Le motif écrit était donc faux là où la
> décision, elle, reste juste — exactement la classe de défaut qui a bloqué les lots 9, 10 et 11, et
> que ce contrat prétendait combattre. Le critère opérant est celui ci-dessus (liste fermée /
> assainisseur), pas l'exclusivité de propriété. **La même phrase avait été recopiée dans l'en-tête
> de `hydratation.php` ; elle y est corrigée dans le même commit.**

---

## 3. Le sens de la dépendance — `query/` → `content/`

**Tranché : `query/portee` appelle `content/portee`. Ce n'est pas un arbitrage neuf, c'est la
convention déjà établie du dépôt, appliquée deux fois et documentée les deux fois.**

- `query/chien/lecture.php:12-23` importe **douze** fonctions de `content/chien`, dont `sexes` et
  `statuts`, par un bloc `use function`.
- `query/chien/bootstrap.php:20-25` : « Le vocabulaire du chien appartient au module
  "content/chien". On le `require_once` plutôt que de compter sur l'ordre de parcours du chargeur :
  **un module ne doit jamais dépendre de cet ordre**, et une seconde inclusion est sans effet. »
- `admin/listes/bootstrap.php:37-39` a déjà départagé les deux listes jumelles et choisi
  `content/portee/champs.php`, « **et non la liste jumelle du module de lecture** : c'est celle-ci
  que consulte l'assainisseur de la disponibilité ».

**C'est l'argument qui clôt le débat : la liste qui fait foi est celle contre laquelle la donnée est
validée à l'écriture.** Une clé n'entre en base que si `assainir_disponibilite()`
(`champs.php:320-328`) la reconnaît, et cet assainisseur lit `champs.php\disponibilites()`. Toute
autre liste est, par construction, une **conjecture** sur ce que la base contient.

**Sens inverse rejeté** — pas seulement parce qu'il est hors empreinte : il ferait dépendre
l'assainissement, qui tourne au chemin d'écriture, d'un module de présentation. Le dépôt a déjà ce
motif à contre-courant (`mtb_resultat_sexes()`, déclarée en `query/resultat/bootstrap.php:53`) et il
coûte — `fields/resultat/ecran.php:221-226` doit la consommer sous `function_exists` avec un repli.

**Ordre de chargement : aucun risque, et la déclaration reste obligatoire.**
`class-loader.php:145-152` parcourt `content` **avant** `query`. Le `require_once` est posé quand
même, pour les deux raisons que le dépôt a déjà écrites : un module ne dépend jamais de l'ordre du
chargeur, et `class-loader.php:216` permet de désactiver un groupe en préfixant son dossier par `_`,
ce qui rendrait `query/portee` fatal au lieu de dégradé.

**Placement du `require_once` : en tête de `hydratation.php`, et non dans `bootstrap.php`.** Le
précédent `chien` le place dans le bootstrap, qui est **hors empreinte**. Cette variante est en
réalité **plus robuste** : `admin/listes/bootstrap.php:47` inclut `hydratation.php` directement, sans
passer par `query/portee/bootstrap.php` ; le fichier qui référence le symbole est donc le seul
endroit où la déclaration vaut pour tous ses appelants.

**Forme du chemin : `MTB_CORE_DIR . 'includes/content/portee/champs.php'`, la chaîne exacte de
`admin/listes/bootstrap.php:45`.** `champs.php` **n'a aucune garde de réinclusion** (contrairement à
`content/chien/choix.php:24-26`) : le mode d'échec d'une erreur de chemin est une **redéclaration
fatale**. On réutilise donc une chaîne dont la production prouve déjà qu'elle cohabite avec la forme
`__DIR__` de `content/portee/bootstrap.php:16` ; on n'en fabrique pas une troisième. Le
`require_once` est posé **après** la garde `ABSPATH`, `MTB_CORE_DIR` n'étant défini qu'en
`mtb-core.php:62`.

---

## 4. Visibilité des membres — gelée par les appelants, pas par le goût

Recensement exhaustif de `Hydratation::` sur tout `wp-content/` au commit `f0b05f1` — **14
occurrences, aucune dans `wp-content/themes/`**. La frontière thème/extension tient et le correctif
ne l'entame pas.

| Membre | Visibilité | Appelants | Décision |
|---|---|---|---|
| `disponibilites()` | `public static` | `:216`, `:266`, **et `query/portee/bootstrap.php:123` (hors empreinte)** | **Reste `public`.** La supprimer ou la passer `private` **casse `mtb_get_portees()`**. Corps réduit à un passe-plat. |
| `sexes()` | `private static` | `:411` seul | **Reste `private`.** Corps réduit à un passe-plat. |
| `date_en_toutes_lettres()` | `public static` | `:527`, `admin/listes/ordre.php:182` et `:359`, `admin/listes/colonnes.php:182` | **Inchangée.** Elle est la décision de lisibilité du projet. |
| `ABSENCE` | `public const` | `admin/listes/ordre.php:181` | **Inchangée.** |

**`sexes()` reste un passe-plat privé plutôt qu'un appel direct en `:411`** : les deux listes fermées
se lisent alors selon une règle unique et visible côte à côte, et le diff se réduit à une
substitution de corps — ce qui rend **mécaniquement vérifiable** qu'aucun des cinq libellés n'a bougé
d'un caractère.

**Effet de bord voulu, obtenu sans écrire hors empreinte** : le commentaire
`query/portee/bootstrap.php:122` — « La liste close vient du module d'hydratation, **jamais d'une
copie de plus** » — est **faux aujourd'hui**, puisque la ligne 123 lit précisément la copie. Il
**devient vrai** par ce correctif.

---

## 5. La décision de lisibilité — une seule, lue et non recalculée

**Règle gelée : « sans date lisible » se décide par `date_en_toutes_lettres()`, et par elle seule.**

Ce n'est pas une règle neuve : c'est celle que `admin/listes/ordre.php:166-169` a écrite en `fa80eb3`
— « Refaire ici le test de validité fabriquerait une seconde notion de date absente, qui divergerait
de la première ; c'est exactement ce qui rangeait une date illisible en tête de liste ». Le correctif
la **transporte** de l'autre côté de la frontière ; il n'en invente aucune.

**Où** : dans `date_de()` (`:185-189`), **point unique par lequel les deux branches du comparateur
lisent la date** — jamais dans le comparateur, dont le corps (`:145-175`) ne change pas d'une ligne.
Que le comparateur soit intact **est la preuve que la cause n'était pas le tri**.

**Comment** : en lisant `date_naissance['affichage']`, **déjà calculé** par `champ_date()`
(`:523-529`) pour chaque portée, plutôt qu'en rappelant `date_en_toutes_lettres()`. On ne recalcule
pas la décision, **on lit celle qui a été prise** — la divergence devient structurellement
impossible, et pas seulement improbable. Coût : **zéro appel supplémentaire à `wp_date()`**, là où un
test dans le comparateur en aurait déclenché plusieurs centaines par requête (`usort` appelle le
comparateur O(n log n) fois, et `date_de()` deux fois par comparaison).

**Repli obligatoire** : si la clé `affichage` n'est pas une chaîne, appeler directement
`date_en_toutes_lettres()`. Le docbloc de `trier()` (`:139`) tolère « ou éléments portant
"date_naissance" et "id" ». Le repli ne doit **jamais** être un repli sur `''` — ce serait fabriquer
une troisième notion.

### Interdit absolu — la donnée brute ne se réécrit pas

`date_lisible()` ne renvoie ni n'écrit jamais une `valeur` modifiée. `issue-3.md:835` gèle `valeur`
comme la donnée **brute, jamais reformatée**. **Le tri ignore une date illisible ; il ne l'efface
pas**, ni en base, ni dans l'enveloppe rendue au thème. Une portée à date abîmée sort de
`mtb_get_portee()` avec sa `valeur` intacte, pour qu'une chaîne de réparation future puisse encore la
lire. Normaliser en amont serait la destruction d'une donnée d'élevage.

### `annee` suit la même décision

`:264` dérive `annee` de la date **lisible** et non de la date **brute** — soit exactement la forme
que `admin/listes/ordre.php:351-376` (`valeurs_de_filtre()`) applique déjà côté administration :
`$lisible ? substr( $date, 0, 4 ) : ''`. **Toute divergence de forme entre les deux modules après le
correctif est un défaut.** Une portée sans année lisible n'appartient à aucune année et **reste dans
la liste des portées** — `blocks/liste-portees/annees.php:43-46` sait déjà traiter ce cas.

---

## 6. Ce que le correctif répare, et ce qu'il ne répare pas

**Texte gelé, à reprendre tel quel dans le corps du commit et dans le rapport de lot. Toute
formulation plus flatteuse reproduirait le défaut du lot 9.**

> `assainir_date()` (`champs.php:283-311`, branché en `sanitize_callback` par `enregistrer_champs()`
> `:164-180`) refusant toute date malformée à l'écriture, **aucune portée saisie depuis l'écran
> d'administration ne peut aujourd'hui déclencher cette divergence** : le correctif ne répare **aucun
> comportement atteignable par la saisie**. Il répare un comportement atteignable par écriture
> directe en base — `issue-28.md:488` établit qu'une date illisible est « injectable seulement par
> `$wpdb` ». Il supprime l'unique option d'année fantôme que le composant *Liste de portées* pouvait
> proposer à l'éleveuse. Et il fait cesser, **dans ce module**, la seconde notion de « date absente »
> — celle de l'ordre — au profit de la seule décision de `date_en_toutes_lettres()`, à laquelle
> l'administration est alignée depuis #28. **Trois autres endroits, hors empreinte, décident encore
> « date absente » par la seule chaîne vide** ; ils sont enregistrés au §7, non traités. Les trois
> libellés de disponibilité et les deux de sexe **ne changent pas d'un caractère** : on change qui
> les déclare, pas ce qu'ils disent.

**Ce que l'éleveuse voit changer** : rien sur les écrans qu'elle utilise, à une exception près — le
réglage « Année » du composant *Liste de portées* ne peut plus proposer une option qui n'est pas une
année, option qui, choisie, affichait **toutes** les portées sans le dire
(`query/portee/bootstrap.php:116` repose `annee = ''`). Aucun libellé, aucun ordre, aucune date
visible ne change sur des données saines.

---

## 7. Dettes résiduelles — relevées, enregistrées, **non traitées**

Le correctif ne supprime pas la seconde notion de « date absente » **du projet**. Il la supprime **de
ce module**. Trois endroits, tous hors empreinte, décident encore « date absente » par la seule
chaîne vide :

| # | Emplacement (`f0b05f1`) | Ce qui reste faux | Effet du correctif |
|---|---|---|---|
| T65 | `query/portee/bootstrap.php:54` — `mtb_get_derniere_portee()` retient la première portée à `valeur` non vide | Défaut **théorique** : la portée illisible passe en fin de liste, donc inatteignable tant qu'au moins une portée a une date lisible | Masqué, pas corrigé |
| T66 | `query/portee/bootstrap.php:248` — `mtb_get_portee_voisine()` inclut la portée illisible dans la chaîne chronologique, alors que son docbloc `:240-242` promet que « seules les portées datées entrent dans cette chaîne » | Le docbloc reste **faux** | Déplace la portée illisible de la tête à la queue de la chaîne — **ne la répare pas** |
| T67 | `blocks/liste-portees/rendu.php:342-367` teste `'' === $valeur` | La liste publique imprime « Née le Non renseigné » et un attribut `datetime` invalide, que le docbloc `:336` du même fichier déclare ne pas être du français | **Inchangé.** Non réparable depuis cette empreinte : la seule voie serait de vider `valeur`, ce que `issue-3.md:835` interdit |

Matière d'une issue suivante, sur l'empreinte `query/portee/bootstrap.php` +
`blocks/liste-portees/rendu.php`.

**Dette mineure, séparée** : `content/portee/champs.php` n'a pas la garde de réinclusion que
`content/chien/choix.php:24-26` porte. Hors empreinte, sans effet mesuré aujourd'hui (les deux formes
de chemin coexistent déjà en production), mais l'auteur de `choix.php` a jugé utile de se ceinturer.

---

## 8. Interdits

- **Le tri ne passe jamais par une clause de méta.** Ni `orderby => meta_value`, ni `meta_key`, ni
  `meta_query`, ni jointure `postmeta`. Le lot 10 a mesuré que `orderby=meta_value` escamote
  **32→31** portées, et que la variante `meta_query EXISTS` escamote pareil. Le docbloc
  `hydratation.php:78-86` porte déjà cet interdit : **si le diff le rend faux, le correctif est
  faux.**
- **On n'exclut jamais une portée illisible de la requête.** L'idée « autant les écarter en amont »
  est exactement le geste qui produit 32→31. Interdite nommément.
- **On ne réécrit jamais `date_naissance['valeur']`.** Voir §5.
- **On n'écrit pas dans `content/portee/champs.php`**, ni dans `includes/admin/**` (chaîne #35), ni
  dans `docs/guide/*.md` (chaîne #35), ni dans `compose.yaml`, `docker/**`, `Makefile` (chaîne #30),
  ni dans `docs/ETAT.md` (niveau lot).
- **Aucun libellé n'est reformulé.** Les cinq sont attestés par `design-system/MASTER.md` §3.3 et
  §10.2 et gelés ; le correctif change leur déclarant, pas leur texte.

---

## 9. Critères d'acceptation mécaniques

Vérifiables sans jugement, sur le diff seul :

1. Le diff ne contient aucune occurrence de `meta_key`, `meta_query`, `orderby`, `meta_value`,
   `post__in`, `JOIN`, `postmeta`.
2. Le docbloc `:78-86` de `contenus()` est intact, et `contenus()` n'est pas modifiée.
3. Les cinq libellés — `Chiots disponibles`, `Tous réservés`, `Portée passée`, `Mâle`, `Femelle` —
   passent de **5 occurrences avant** à **0 après** dans `hydratation.php`, et ne sont modifiés à la
   source dans aucun fichier.
4. Le corps du comparateur de `trier()` (`:145-175`) est inchangé.
5. `disponibilites()` toujours `public static` ; `sexes()` toujours `private static` ;
   `date_en_toutes_lettres()` toujours `public static` ; `ABSENCE` toujours `public const`.
6. `git diff --name-only` ne rend **que** `hydratation.php`. Le commit passe par `commit-scoped` avec
   ce seul chemin.
7. `declare(strict_types=1)` conservé, WPCS, PHP 8.1.

---

## 10. Preuves exigées — mesurées, jamais affirmées

La démonstration porte sur une portée d'essai à date illisible, injectée **par `$wpdb`** (les
assainisseurs refusent la voie normale), et **supprimée avec preuve du nettoyage**. Une portée réelle
n'est **jamais** corrompue : elle afficherait « Née le Non renseigné » pendant toute la fenêtre, et si
la chaîne #35 capturait cet écran, le manuel de l'éleveuse publierait un **fait d'élevage faux sur une
vraie portée**. Au lot 10, une capture a photographié « PORTEE ESSAI 37 » ; le titre d'essai doit donc
être manifestement fictif.

| Preuve | Attendu |
|---|---|
| Rang public de la portée d'essai | **1 avant → dernier après** |
| Rang en administration | **dernier avant → dernier après**, inchangé (#28 l'avait déjà réparé ; la convergence se fait par le côté public) |
| Total de portées | **inchangé** avant/après. Rien ne disparaît. |
| Suite des portées **datées** | **identique caractère pour caractère** avant/après (tâche 4 de l'issue, non-régression) |
| `Annees::disponibles()` | option fantôme **présente avant, absente après** ; les vraies années **identiques caractère pour caractère** |
| SQL réellement émis (`posts_request`) | **0 jointure `postmeta`** dans les requêtes de liste, avant et après, même nombre de requêtes |
| Nettoyage | 0 ligne `postmeta` résiduelle, et retour aux comptes de référence |

**Formulation à respecter sur le contrôle D12** : « **0 jointure `postmeta` dans les requêtes de
liste (`posts_request`)** » — et non « 0 occurrence de `postmeta` dans tout le SQL », qui serait faux
et le resterait légitimement, l'amorçage du cache des champs interrogeant `postmeta` par des `SELECT`
distincts qui n'ont jamais escamoté personne.

**Si la mesure infirme un attendu, c'est la mesure qui gagne et ce contrat qui se corrige.** En
particulier : si l'option d'année fantôme n'est **pas** mesurée présente avant correctif, l'instance
4 reste juste au titre de D1 mais **perd son symptôme visible**, et le §6 doit être réécrit pour ne
plus rien promettre à l'éleveuse. C'est exactement le piège dans lequel `issue-28.md:518-521` est
tombé une fois.

---

## 11. Ce qui devient faux ailleurs — recensé, **rien planifié hors empreinte**

| Emplacement (`f0b05f1`) | Ce qui change | Propriétaire |
|---|---|---|
| `hydratation.php:6-8` — « libellés […] **écrits en toutes lettres dans ce module** » | **Devient faux** pour deux listes | **Cette empreinte — corrigé dans le même commit** |
| `query/portee/bootstrap.php:122` — « jamais d'une copie de plus » | Faux aujourd'hui, **devient vrai** | Aucune action requise |
| `docs/guide/listes-retrouver-un-contenu.md:54-58` — « sauf pour une date abîmée, que cette liste range en fin quand le site la range en tête » | **Devient faux.** La clause doit **disparaître**, pas être corrigée : les deux écrans s'accordent. Coût de documentation **négatif** — #45 retire un concept du manuel au lieu d'en ajouter un | **Chaîne #35** — arbitrage lot |
| `admin/listes/bootstrap.php:37` — « et non la liste jumelle du module de lecture » | Devient **impropre** : il n'y a plus de jumelle, mais une délégation. Inoffensif | Chaîne #35 |
| `docs/contracts/issue-28.md:152` — « à une exception près depuis `fa80eb3` » | **Périmé** : l'exception disparaît | Contrat gelé — arbitrage lot |
| `docs/contracts/issue-28.md:540` et `:541` | Les deux dettes enregistrées sont **soldées**, et `:541` au-delà (`sexes()` aussi) | Contrat gelé |
| `docs/contracts/issue-3.md:867` — « tri décroissant sur `date_naissance['valeur']`, non datées en fin » | **Précision, non contradiction** : le tri reste sur `valeur`, mais « non datée » recouvre désormais l'illisible. Noté pour que personne ne lise plus tard une divergence là où il n'y en a pas | Contrat gelé |

**Aucune écriture n'est proposée dans aucun de ces fichiers.**

---

## 12. Arbitrages

| # | Désaccord ou question | Décision | Raison |
|---|---|---|---|
| 1 | Périmètre : 2 instances (lettre de l'issue) ou 4 ? | **4** | Les quatre sont la même faute, dans le fichier unique de l'empreinte ; l'élargissement ne menace aucune chaîne sœur. Traiter 2 sur 4 obligerait ce contrat à se démentir deux lignes plus bas. |
| 2 | Sens de la dépendance `query/` ↔ `content/` | **`query/` appelle `content/`** | Convention déjà établie (`query/chien/lecture.php:12-23`, `query/chien/bootstrap.php:20-25`) ; la liste qui fait foi est celle que consulte l'assainisseur. |
| 3 | Supprimer `Hydratation::disponibilites()` ou la garder en passe-plat ? | **Garder, `public`** | `query/portee/bootstrap.php:123` l'appelle — hors empreinte. La supprimer casse `mtb_get_portees()`. |
| 4 | `sexes()` : passe-plat privé ou appel direct en `:411` ? | **Passe-plat privé** | Règle unique et visible côte à côte pour les deux listes ; diff réduit à une substitution de corps, donc vérifiable au caractère près. |
| 5 | Test de lisibilité : dans le comparateur ou en amont ? | **En amont, dans `date_de()`** | Le comparateur reste intact — preuve que la cause n'était pas le tri. Et O(n log n) appels à `wp_date()` évités. |
| 6 | Rappeler `date_en_toutes_lettres()` ou lire `affichage` déjà calculé ? | **Lire `affichage`** | On ne recalcule pas la décision, on lit celle qui a été prise : la divergence devient structurellement impossible. Coût zéro. |
| 7 | Normaliser une date illisible à `''` en amont ? | **Non, jamais** | `issue-3.md:835` gèle `valeur` comme donnée brute. Ce serait détruire une donnée d'élevage. |
| 8 | Placement du `require_once` (le précédent le met dans `bootstrap.php`, hors empreinte) | **En tête de `hydratation.php`** | `admin/listes/bootstrap.php:47` inclut `hydratation.php` directement : le fichier qui référence le symbole est le seul endroit où la déclaration vaut pour tous ses appelants. |
| 9 | Corrompre une portée réelle ou créer une portée d'essai ? | **Portée d'essai, titre manifestement fictif, supprimée avec preuve** | Corrompre une portée réelle publierait un fait d'élevage faux si #35 capturait l'écran. |
| 10 | Qui corrige `docs/guide/listes-retrouver-un-contenu.md:54-58` ? | **Remonté au lot, non traité ici** | Empreinte de la chaîne #35, qui tourne en parallèle. Séquence à ordonnancer avant la clôture du lot. |
| 11 | Amende-t-on `docs/contracts/issue-28.md`, contrat gelé, devenu périmé ? | **Remonté au lot** | Le dépôt n'a pas de règle écrite pour ce cas et il se reposera. |

---

## 13. Questions bloquantes

**Aucune.** Cette issue n'écrit, ne reformule et n'invente **aucun fait d'élevage** : elle déplace la
déclaration de cinq libellés déjà gelés et attestés (`MASTER.md` §3.3 et §10.2), et aligne une
décision de lisibilité sur la fonction qui la prenait déjà. Aucun nom, date, numéro LOF, généalogie,
résultat de test ou de concours n'est touché.
