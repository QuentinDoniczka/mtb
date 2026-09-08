# Contrat d'interface — Issue #55 — Corriger occurrence par occurrence les mentions de « pages » qui n'en sont pas dans le guide (T112)

**Gelé le 2026-09-08.** Milestone 15, epic « Dette technique — témoin, écran de page et guide (lot 19) ».
Label `doc`. Empreinte : `docs/guide/**` et ce fichier.

Cette issue ne touche **ni le thème, ni l'extension**. Il n'y a donc **pas de contrat d'interface
front↔back** à geler : les deux `leaddev` n'ont pas été lancés, et `dev-integration-mtb` ne l'est pas
non plus. Ce document est un **contrat d'arbitrage** — il fige le critère, les faits mesurés, et les
deux listes (corrigé / laissé volontairement) qui font la preuve que la passe n'a pas été mécanique.

---

## 0. Ce qui rend cette dette dangereuse

T112 (`docs/ETAT.md:1969`) ne se solde pas par un remplacement. **Un `sed` sur « pages » abîmerait
précisément les occurrences justes.** Le guide parle de pages parce qu'il parle de pages : sur les
29 fichiers de `docs/guide/`, le mot est massivement employé à bon droit. La densité de défaut réelle
se compte en unités.

**Conséquence opposable : « aucune correction » est le verdict attendu sur la grande majorité des
fiches.** Une fiche qui parle de pages et ne décrit aucun geste sur un contenu qui n'en est pas une
sort de la passe **intacte**. Le zèle est ici le mode de panne, pas la négligence.

---

## 1. Le critère — test à deux détentes, appliqué au paragraphe

L'énoncé de l'issue pose « cette phrase décrit-elle un geste ? ». Le brainstorm a montré que ce
critère, seul, se trompe **dans les deux sens**. Il est donc remplacé par un test à **deux détentes**,
et **l'unité d'arbitrage est le paragraphe porteur du geste, pas la phrase isolée** :

| Détente | Question |
|---|---|
| 1 | Cette phrase **porte un geste**, ou **prépare / justifie** le geste de son paragraphe ? |
| 2 | Si elle égarait, **l'enverrait-elle cliquer dans le mauvais encadré du menu de gauche** ? |

**Correction seulement si les deux réponses sont oui.** Une seule suffit à laisser la phrase intacte.

Ce que la seconde détente protège, et qui serait perdu avec le critère simple : « pour corriger **la
page**, on corrige le champ », « **La page** qui s'ouvre », « en haut de **la page** », « cette **page**
ne vous fait rien modifier ». Ces phrases portent un geste, mais « page » y désigne la page publique ou
la fiche du guide elle-même — elles n'envoient personne dans le mauvais menu.

Ce que la première détente rattrape, et que « décrit-elle un geste ? » laisse passer : un paragraphe de
justification (« **Pourquoi cet ordre** ») qui suit immédiatement l'étape qu'il explique. Corriger
l'étape et laisser sa justification produit un écran où l'étape dit « ce contenu » et son explication
dit « la page », deux lignes plus bas.

### Formulations de remplacement, par ordre de préférence

1. **« ce contenu »** — quand le sujet peut être une page, une fiche de chien ou une portée. Mot déjà
   enseigné par le guide (« Retrouver **un contenu** dans vos listes », titre de la fiche du sommeil).
2. **Le chemin d'écran, pas seulement le nom** — quand la phrase porte un geste localisable. C'est la
   **seule formulation qui répare la panne réelle** : retirer une fausseté sans dire où cliquer laisse
   l'éleveuse sans la vérité.
3. **Le nom réel** (« la fiche de Halan », « la page Placement ») — quand le sujet est nommé et unique.
4. **Jamais « la page ou la fiche du chien »** — trois mots là où un suffit, pour une lectrice qui lit
   une fiche à la fois.

---

## 2. Faits mesurés au navigateur le 2026-09-08 — pas déduits

Mesures jouées sur la pile Docker (`localhost:3005`), **avec le compte de l'éleveuse**
(`fabienne`, rôle `editor`), Chrome installé piloté par Playwright en `--lang=fr-FR`.
Ces mesures **commandent** ce qui est écrit ci-dessous ; sans elles, deux des trois corrections
demandées auraient été fausses.

### 2.1 Écran des menus (`wp-admin/nav-menus.php`) — mesure décisive

| Encadré de la colonne de gauche | Visible par défaut | Case dans « Options de l'écran » |
|---|---|---|
| Pages | **oui** | cochée |
| Articles | oui | cochée |
| **Chiens** | **NON** (`display:none`, `hide-if-js`) | **décochée** |
| **Portées** | **NON** (`display:none`, `hide-if-js`) | **décochée** |
| Liens personnalisés | oui | cochée |
| Catégories | oui | cochée |
| Étiquettes | non | décochée |

**Conséquence opposable.** Une fiche qui écrirait « dans la colonne de gauche, l'encadré **Chiens** »
serait **fausse à l'écran** et **pire que le silence actuel** — la dette T112 recréée par sa propre
correction. La section neuve **doit** commencer par **Options de l'écran**.

Libellés exacts relevés, **point de code vérifié** : le bouton, en haut à droite, dit
**« Options de l’écran »** et la légende du groupe **« Éléments de l’écran »** — dans les deux cas
avec l'**apostrophe typographique U+2019**, et non l'apostrophe droite. *(Une première rédaction de ce
§ annonçait « apostrophe typographique » tout en écrivant elle-même l'apostrophe droite ; la
contradiction a été relevée par la passe de refacto et tranchée par une seconde mesure. Les quatre
occurrences de la fiche portent bien U+2019.)* La prose française du guide, elle, garde l'apostrophe
droite : seuls les libellés **recopiés du produit** sont concernés.

Après avoir coché **Chiens** et **Portées**, les deux encadrés apparaissent dans la colonne de gauche
et portent **exactement les mêmes trois onglets que Pages** — **Les plus récentes**, **Tout voir**,
**Rechercher** — avec **Les plus récentes** actif par défaut. Relevé sur Chiens : 15 entrées sous
« Les plus récentes », 22 sous « Tout voir ». **Le piège des dix minutes déjà documenté à l'étape 3
de la fiche est donc identique**, et c'est mesuré, pas supposé.

**Ces deux nombres ne sont PAS écrits dans le guide, et c'est délibéré** : ils seraient faux le jour où
l'éleveuse ajoute un 23ᵉ chien — or tout ce projet existe pour qu'elle en ajoute seule. Un inventaire
gravé dans une fiche vieillit en mensonge, et **rien ne le signalerait**. La fiche reprend donc le
patron durable déjà employé à son étape 3 pour les pages : « les plus récemment créés — vos chiens
anciens n'y sont pas », sans aucun chiffre.

**Ordre vertical réel de la colonne de gauche**, mesuré une fois les deux cases cochées (position en
pixels de chaque encadré) : **Pages**, puis **Articles**, puis **Chiens**, **Portées**, **Liens
personnalisés**, **Catégories**. **Articles s'intercale entre Pages et Chiens** : écrire que les
nouveaux encadrés sont « sous **Pages** » enverrait l'éleveuse regarder un endroit où elle trouve
**Articles** — la panne même que cette issue supprime, recréée par sa propre correction. La fiche nomme
donc le vrai voisin.

**Persistance vérifiée en base, pas déduite** : après avoir coché les deux cases, la métadonnée
`metaboxhidden_nav-menus` du compte de l'éleveuse ne contenait plus que `add-post_tag`. Les encadrés
**restent donc affichés aux visites suivantes** — ce que la fiche affirme. *L'état par défaut a été
**restauré** après mesure (les trois encadrés de nouveau masqués), pour ne pas laisser cette chaîne
modifier l'environnement partagé sous les chaînes voisines et le test d'intégration de lot.*

### 2.2 Le libellé de la rangée — la prémisse « Link » est REDRESSÉE

Relevé sur les deux routes d'édition, pour la page d'accueil (`page_on_front = 6`) et pour une page
ordinaire (Placement, 318) :

| Écran | Route | Accessible à l'éleveuse | Libellé de la rangée |
|---|---|---|---|
| Page ordinaire — Placement | `post.php?post=318&action=edit` | **oui** | **Adresse de la page** |
| **Page d'accueil** — Accueil | `post.php?post=6&action=edit` | **oui** | **Adresse de la page** |
| Page ordinaire — Placement | `site-editor.php` | **non — HTTP 403** | Slug |
| **Page d'accueil** — Accueil | `site-editor.php` | **non — HTTP 403** | **Lien** |

**Le contrat #54 §12 bis relève « la page d'accueil, dont la rangée dit « Link » et non « Slug », non
couverte ». Sur l'écran que le guide décrit, c'est faux — et il n'y a rien à corriger.**

- Sur `post.php`, la route par laquelle **toutes** les fiches du guide font passer l'éleveuse, la page
  d'accueil affiche **« Adresse de la page »**, **identique** à n'importe quelle autre page. Le module
  de #54 y **fire** normalement.
- La rangée « Lien » n'existe que dans l'**Éditeur de site**, qui rend **403** pour l'éleveuse :
  `fabienne` est `editor` et **n'a pas** `edit_theme_options` (vérifié). Elle **ne peut pas** atteindre
  cet écran.

**Arbitrage : le cas « Link » est clos par la mesure, hors périmètre du guide, et RIEN n'est écrit à son
sujet.** Écrire au guide que l'accueil se comporte autrement introduirait une fausseté là où il n'y en
a pas. La ligne `contenu-mettre-en-sommeil-et-reveiller.md:59` — figée par #54 — **est juste pour
l'accueil comme pour toute autre page**, et le reste.

**Constat versé, sans être une dette du guide** : l'Éditeur de site affiche encore **« Slug »** sur une
page ordinaire — le module de #54 n'y opère pas. Écran inaccessible à l'éleveuse ; **non documenté,
non élargi**, remonté au lead.

### 2.3 « Adresse de la page » est un libellé du produit, y compris sur un chien

`chien-ajouter-un-chien.md:238` décrit « La boîte **Adresse de la page**, tout en bas de l'écran » —
sur l'écran d'un **chien**. `portee-ajouter-une-portee.md:255` porte le même titre de section pour une
**portée**. **Ce n'est pas une faute du guide** : c'est le libellé que le produit affiche, gelé par
`design-system/MASTER.md` §10.2.

**Interdit qui en découle** : aucune règle du type « le mot *page* près du mot *chien* est suspect » ne
doit s'appliquer à ces deux endroits. Le guide **recopie ce que l'éleveuse voit**.

### 2.4 `page-proteger-une-page-par-mot-de-passe.md` ne porte aucune capture

Vérifié : **zéro** occurrence de `![` dans ce fichier. La correction de sa ligne 31 est donc **d'un
mot, sans reprise d'image et sans dette de capture**. La question posée à l'ouverture de la chaîne est
close par la mesure.

---

## 3. Corrections dues

| # | Emplacement | Ce qui est faux | Ce qui est écrit |
|---|---|---|---|
| C1 | `page-proteger-une-page-par-mot-de-passe.md:31` | énumère la rangée **Slug** ; le produit affiche **Adresse de la page** depuis #54 (`f462817`) | `Slug` → `Adresse de la page` |
| C2 | `contenu-a-l-ecart-des-moteurs-de-recherche.md`, paragraphe « Pourquoi cet ordre » (`:68-71`) | dit « une **page** que rien ne relie », « resterait une **page** orpheline », « Relier **la page** d'abord » — alors que **quatre des cinq sujets sont des chiens** ; justifie l'étape `:62`, déjà corrigée en « ce contenu » | formulation 1 (« ce contenu ») |
| C3 | `menu-modifier-le-menu.md` | ne documente que l'encadré **Pages** ; ne montre pas comment poser une fiche de chien dans un menu | section neuve, **précédée d'Options de l'écran** (§2.1) |
| C4 | `menu-modifier-le-menu.md`, passe de contrecoup | une dizaine de phrases disent « la page » d'une entrée de menu, **vraies tant que la fiche ne parlait que de pages** | formulation 1 ou 3 selon le cas |

**C2 est adjacent au bloc gelé** `:40-53`. La frontière est posée ici : **`:40` à `:53` ne bougent pas**,
`:68-71` est ouvert à la correction.

**C4 est constitutif de C3, pas optionnel.** Une extension qui laisserait « **La page n'est pas
supprimée.** Elle reste entière dans **Pages** » sous une section qui vient d'apprendre à poser un
chien serait la dette T112 **recréée par sa propre correction**.

### Portée de la section neuve (C3) : Chiens **et** Portées, pas seulement Chiens

- La décision 74 porte le sommeil sur les **trois** types ; documenter Chiens seul recréerait la dette
  à l'identique pour une portée.
- Le coût marginal est **d'une phrase**, les deux encadrés étant strictement identiques (§2.1).
- **Pas de troisième encadré** : `content/resultat/bootstrap.php` pose `show_in_nav_menus => false` —
  **l'encadré « Résultats de travail » n'existe pas**. En parler serait inventer un écran.

---

## 4. Laissé volontairement — la liste qui prouve que la passe n'est pas mécanique

| Emplacement | Pourquoi il ne bouge pas |
|---|---|
| `contenu-a-l-ecart-des-moteurs-de-recherche.md:40, :47, :53` | « pages » y est **porteur du raisonnement** sur les liens entre pages, et la section porte le **solde de la décision 72** (une corrélation se publie sans son pourquoi). Gelé par l'énoncé. |
| `contenu-a-l-ecart-des-moteurs-de-recherche.md:88` | libellé de menu. Gelé par l'énoncé. |
| `contenu-a-l-ecart-des-moteurs-de-recherche.md:99` | « L'une des cinq » — **vrai**, Placement étant bien une page. Gelé par l'énoncé. |
| `contenu-mettre-en-sommeil-et-reveiller.md:59` et l'`alt` de `:70` | **figés par #54** au commit `f462817`. Justes pour l'accueil aussi (§2.2). |
| `docs/guide/captures/sommeil-page-zone-laterale.png` | **appartient à #54**, qui a déjà une seconde reprise due. Interdite à cette chaîne. |
| `chien-ajouter-un-chien.md:238`, `portee-ajouter-une-portee.md:255` | **libellé du produit**, gelé par `MASTER.md` §10.2 (§2.3). |
| Tout emploi de « page » désignant la **page publique**, la **fiche du guide elle-même**, ou le **haut / bas de l'écran** | échoue à la seconde détente : n'envoie personne dans le mauvais encadré. |
| Les fiches qui parlent de pages **parce qu'elles parlent de pages** | verdict attendu, pas un manque. |

*La liste nominative complète, occurrence par occurrence, est ajoutée au §7 après vérification sur le
disque.*

---

## 5. Interdits

- **Aucun `sed`, aucun remplacement global, aucune passe automatisée** sur le mot « pages ».
- **Rien hors de `docs/guide/**`** : ni `docs/ETAT.md`, ni un fichier PHP, ni
  `migration/indexation-heritee/**` (#56 y travaille en parallèle, arbre de travail partagé).
- **Aucune capture régénérée ni ajoutée** sans que le `.png` entre dans le **même commit** — rien ne
  vérifie automatiquement l'invariant « référence ↔ fichier » dans `docs/guide/`.
- **Ne pas renuméroter « Les étapes »** de `menu-modifier-le-menu.md` : les renvois « étapes 3 à 7 »
  existent en `:155`, `:198` et `:245` et casseraient **en silence**. La section neuve pointe vers ces
  mêmes numéros.
- **Ne pas documenter « Slug » comme corrigé dans la Modification rapide** des listes (dette T-#54-b,
  distincte) ; ne pas l'élargir.
- **Ne rien écrire sur le cas « Link »** (§2.2) : il est clos par la mesure.

---

## 6. Arbitrages rendus

| # | Désaccord | Décision | Raison |
|---|---|---|---|
| A1 | Le critère de l'énoncé, « cette phrase décrit-elle un geste ? », suffit-il ? | **Non — remplacé par le test à deux détentes**, appliqué au paragraphe | Il condamne quatre phrases justes et laisse passer le paragraphe « Pourquoi cet ordre » |
| A2 | Étendre la fiche menu à Chiens seul, ou à Chiens **et** Portées ? | **Les deux, en une seule section courte** | Décision 74 ; coût marginal d'une phrase ; Chiens seul recrée la dette pour une portée |
| A3 | Y ajouter les résultats de travail ? | **Non** | `show_in_nav_menus => false` — l'encadré n'existe pas |
| A4 | La section neuve peut-elle dire « l'encadré Chiens, dans la colonne de gauche » ? | **Non — elle doit passer par Options de l'écran d'abord** | Mesuré : l'encadré est masqué par défaut (§2.1) |
| A5 | Le cas « Link » de la page d'accueil est-il dans le périmètre ? | **Non — clos par la mesure, rien n'est écrit** | Sur `post.php`, l'accueil dit « Adresse de la page » comme toute page ; « Lien » n'existe que dans l'Éditeur de site, **403** pour l'éleveuse (§2.2) |
| A6 | La passe de contrecoup dans la fiche menu est-elle dans cette issue ? | **Oui, elle est constitutive de C3** | Sinon la correction recrée la dette qu'elle solde |
| A7 | Faut-il corriger `chien-ajouter-un-chien.md:238` (« Adresse de la page » sur un chien) ? | **Non** | Libellé du produit, gelé par `MASTER.md` §10.2 |

---

## 7. Inventaire vérifié sur le disque

**Relu fichier par fichier après la passe, jamais sur la foi d'un rapport d'agent.** Quatre fiches
modifiées sur 29 relues.

### 7.1 Corrigé

| Fichier | Ligne | Avant → Après |
|---|---|---|
| `page-proteger-une-page-par-mot-de-passe.md` | 31 | rangée « **Slug** » → « **Adresse de la page** » (C1) |
| `contenu-a-l-ecart-des-moteurs-de-recherche.md` | 68-71 | « une **page** que rien ne relie… resterait une **page** orpheline… Relier **la page** d'abord » → « un **contenu** que rien ne relie… resterait **isolé — aucun chemin n'y mènerait**… Relier **ce contenu** d'abord » (C2) |
| `contenu-a-l-ecart-des-moteurs-de-recherche.md` | 63 | renvoi « *Modifier le menu* » → « *Modifier le menu du site* » (titre réel de la fiche ; relevé par la passe de refacto) |
| `menu-modifier-le-menu.md` | 112-147 | **section neuve** « Ajouter un chien ou une portée au menu » (C3) |
| `menu-modifier-le-menu.md` | 3-6, 156-158, 180-181, 193-196, 280-287, 292-295, 310-311 | passe de contrecoup : « la page » d'une entrée de menu → « ce contenu » / « sa liste — **Pages**, **Chiens** ou **Portées** » (C4) |
| `README.md` | 76 | « Choisir **les pages** du menu, leur ordre, leur nom » → « Mettre au menu **une page, un chien ou une portée**, choisir leur ordre et leur nom » |

**Trois défauts introduits par la première rédaction, rattrapés avant commit** — ils sont consignés
parce qu'ils sont instructifs, chacun étant une manière différente de mentir sans se tromper d'un mot :

1. **Un inventaire gravé.** « Sur vos 22 chiens, il n'en montre que 15 » : exact le jour où il est
   écrit, faux au 23ᵉ chien, et **rien ne l'aurait signalé**. Les nombres m'avaient servi de *preuve*
   que le piège existe ; ils ont été recopiés comme *texte*. Remplacé par le patron durable de
   l'étape 3.
2. **Un motif inventé.** « Il n'y a pas d'encadré pour les résultats de travail, **et c'est voulu : un
   résultat s'affiche sur la fiche du chien concerné** » — motif partiel (`resultat-ajouter-un-resultat.md:162-163`
   dit *palmarès de la fiche **et** tableau*) d'où était tirée une intention. C'est le glissement que la
   **décision 72** interdit nommément. Réduit au fait seul.
3. **Un repère faux.** « les encadrés Chiens et Portées… **sous Pages** » : **Articles s'intercale**
   (§2.1). Elle aurait regardé sous Pages, trouvé Articles, et cru que ça n'avait pas marché — la panne
   même que l'issue supprime, recréée par sa correction. La fiche nomme désormais le vrai voisin.

### 7.2 Laissé volontairement — vérifié occurrence par occurrence

Aux exclusions du §4 s'ajoutent, **relues et laissées** :

| Emplacement | Détente qui échoue |
|---|---|
| `chien-ce-qui-s-affiche-sur-le-site.md:4`, `portee-ce-qui-s-affiche-sur-le-site.md:4` — « pour corriger **la page**, on corrige le champ » | 2ᵉ : « page » = la page **publique** |
| `portee-ajouter-une-portee.md:19` — « **La page** qui s'ouvre » | 2ᵉ : l'écran de saisie |
| `chien-ajouter-un-chien.md:19, :38, :62`, `portee-ajouter-une-portee.md:48, :158` — « en haut de la page » | 2ᵉ : haut/bas de l'écran |
| `chien-ce-qui-s-affiche-sur-le-site.md:9`, `portee-ce-qui-s-affiche-sur-le-site.md:11`, `resultat-ajouter-un-resultat.md:18` — « cette page-ci » | 2ᵉ : la fiche du guide elle-même |
| `menu-modifier-le-menu.md:67` — H2 « Les étapes — **ajouter une page au menu** » | exact (cette section décrit bien l'encadré **Pages**) **et** gelé par quatre renvois |
| `menu-modifier-le-menu.md:82-98` — étapes 3 et 4, « l'encadré **Pages** » | exact : elles décrivent l'encadré Pages, et la section neuve y renvoie |
| `menu-modifier-le-menu.md:210-211` — « **Ajouter automatiquement les pages de premier niveau à ce menu** » | libellé du produit |
| `menu-modifier-le-menu.md:152-154`, `:105` | 2ᵉ : pages publiques du site |
| `menu-modifier-le-menu.md:237-238` — « les pages que le visiteur doit pouvoir retrouver » | sujet nommé et unique (mentions légales = une page) |
| `menu-modifier-le-menu.md:306` — « Une entrée mène à une autre page » | 1ʳᵉ : symptôme, pas geste |
| `contenu-mettre-en-sommeil-et-reveiller.md:118-119` — « une **page** endormie reste dans vos menus » | 1ʳᵉ : énoncé de ce que le sommeil ne fait pas |
| `page-ce-qui-a-ete-repris-de-l-ancien-site.md:327-335` — « **Vos pages** n'ont pas encore de menu » | la section solde la reprise **des pages** ; son inventaire n'en contient que |
| Les seize `1. Dans le menu de gauche, cliquez sur **Pages**` des fiches `composant-*` | exacts : les composants se posent dans des pages |

**25 fiches sur 29 relues et laissées entièrement intactes.** C'est le résultat attendu (§0), pas un
manque : elles parlent de pages parce qu'elles parlent de pages.

### 7.3 Invariants contrôlés après la passe

| Invariant | Résultat |
|---|---|
| `docs/guide/contenu-mettre-en-sommeil-et-reveiller.md` vs `f462817` | **identique, zéro octet d'écart** — `:59` et l'`alt` de `:70` intacts |
| `docs/guide/captures/sommeil-page-zone-laterale.png` | **intacte** — aucun fichier de `captures/` touché |
| Références d'images ↔ fichiers | **126 références, 126 `.png`, zéro manquante** |
| `contenu-a-l-ecart…md` `:40, :47, :53, :88, :99` | **inchangées**, numéros de ligne compris (les deux modifications de ce fichier sont neutres en nombre de lignes) |
| H2 « Les étapes » et sa numérotation 1→7 | **intacts** (lignes 67, 69, 73, 82, 90, 95, 97, 102) |
| Renvois « étapes 3 à 7 » | **quatre**, tous résolus : `:132` (neuf), `:196`, `:239`, `:287` |
| Vocabulaire technique dans `docs/guide/` | `slug`, `meta`, `taxonomie`, `custom`, `post type`, `métaboîte`, `permalien` → **zéro occurrence** |
| Origines tierces dans `docs/guide/` | `https?://` → **aucune** |
| Fichiers écrits hors empreinte | **aucun** |
| État de l'environnement Docker | `metaboxhidden_nav-menus` du compte de l'éleveuse **restauré** à son défaut après mesure |

### 7.4 Capture due — déclarée, non prise

**Aucune capture n'a été créée, régénérée ni référencée par cette chaîne**, et la section neuve n'en
porte aucune. Un appel d'image y avait été posé puis **retiré avant livraison**, faute du `.png`
correspondant : rien ne vérifie l'invariant « référence ↔ fichier » dans ce répertoire, et une
référence orpheline laisserait un cadre cassé dans la fiche.

**Reste donc DUE, et déclarée comme telle** :

| Fichier attendu | Écran | Ce qu'elle doit prouver |
|---|---|---|
| `docs/guide/captures/menu-options-de-l-ecran.png` | `wp-admin/nav-menus.php`, compte `fabienne`, panneau **Options de l’écran** déplié | le bouton en haut à droite, le groupe **Éléments de l’écran**, ses cases dans l'ordre, **Chiens** et **Portées** cochées — à insérer à l'étape 3 de la section neuve |

Souhaitable et non indispensable : `captures/menu-encadre-chiens.png` — l'encadré **Chiens** apparu
dans la colonne, ses trois onglets, **Tout voir** actif. Sans elle la section reste utilisable ; les
quatre autres étapes de la fiche ont la leur.

**Aucune capture existante n'est rendue périmée par cette issue.** `menu-ajouter-une-page.png` décrit
toujours l'encadré **Pages** de l'étape 4, inchangée ; l'`alt` de `contenu-mettre-en-sommeil…:70` dit
déjà « Adresse de la page », cohérent avec C1.

### 7.5 Versé au lead — hors périmètre, non documenté

1. **L'Éditeur de site affiche encore « Slug »** sur une page ordinaire : le module de #54 n'y opère
   pas. Écran **403** pour l'éleveuse. Famille T-#54-b, non élargie ici.
2. **Poser un chien dans un menu coûte quatre étapes de préparatifs** avant le premier geste utile,
   uniquement parce que les encadrés sont livrés décochés. Cocher ces deux cases par défaut pour le
   rôle de l'éleveuse supprimerait quatre étapes sur cinq **et** la capture due.
3. **`menu-modifier-le-menu.md` fait maintenant ~320 lignes et douze sections H2.** Si un lot cherche
   une fiche à scinder, c'est celle-là.
4. **`portee-la-liste-des-portees.md:78`** dit « appelez-nous » pour poser l'entrée menant à
   `/portees/` : un index d'archive n'est dans aucun encadré et demanderait **Liens personnalisés**,
   que le guide ne documente nulle part. **Seul geste de menu qu'elle ne peut toujours pas faire
   seule.**
5. **`page-ce-qui-a-ete-repris-de-l-ancien-site.md:335`** écrit « **Modifier le menu** » là où quatre
   autres fiches écrivent « Modifier le menu du site ». Coquetterie de nommage, ne trompe sur aucun
   geste, hors critère T112 : **déclarée plutôt que corrigée au passage.**
6. **L'ancre `:88`** citée par l'énoncé de l'issue désigne une **ligne vide** ; le libellé de menu
   qu'elle visait est à `:89`. Sans effet — la ligne n'a pas été touchée.

---

## 8. Acte du 2026-09-08 (après commit `a6ec9a9`) — l'énumération des rangées de l'étape 4

Routé par le lead après mesure de #54 : l'écran de la **page d'accueil** porte **huit** rangées, une
rangée **Révisions** s'intercalant avant **Parent**, alors que l'étape 4 de
`contenu-mettre-en-sommeil-et-reveiller.md` en énumère **sept**. Défaut **préexistant** à ce lot.

### 8.1 Le cadrage transmis était inexact — mesuré, pas déduit

Il m'a été routé comme « vraie pour toute page **sauf l'accueil** ». **Le seuil n'est pas l'accueil,
c'est l'historique.** Relevé en session éditrice, sur trois pages :

| Page | Révisions | Rangées |
|---|---|---|
| Placement (318) | 0 | **7** |
| Contact (4) | **1** | **7** |
| Accueil (6) | 6 | **8** — `Révisions` **avant** `Parent` |

La rangée apparaît **au-delà d'une** révision. La page 4, à une révision, en montre encore sept :
c'est elle qui discrimine, et sans elle on aurait conclu « l'accueil est un cas spécial ».

**Conséquence, plus large que le cas signalé** : la liste de sept devient fausse sur **toute page que
l'éleveuse a modifiée plusieurs fois**, pas seulement l'accueil — et **tout ce projet existe pour
qu'elle modifie ses pages elle-même**. Chaque page finira par franchir ce seuil. Ce n'est donc pas une
singularité de l'accueil à signaler, c'est un compte qui vieillit — la même famille de défaut que
l'inventaire gravé du §7.1.

### 8.2 Arbitrage, au critère du §1

- **1ʳᵉ détente : oui.** L'étape porte un geste — trouver la case sous les rangées.
- **2ᵈᵉ détente : oui, en substance.** L'énumération n'est pas la cible du geste, c'est le **repère**
  qui lui fait reconnaître le panneau. Une liste donnée pour exhaustive et démentie par l'écran lui
  fait douter d'être au bon endroit, au moment précis où elle cherche une case « qu'aucun titre
  n'annonce ».

**Décision : corriger, mais par le seul ajout qui ne dépende d'aucun compte.** Une incise en italique
— la forme déjà employée aux étapes 3 et 6 — dit qu'une rangée **Révisions** s'ajoute **juste avant
Parent** sur une page déjà modifiée plusieurs fois, et que le geste est inchangé.

### 8.3 Ce qui n'a PAS été touché, et c'est vérifié au diff

- **La ligne `:59` figée par #54 n'est pas modifiée du tout** : elle n'apparaît qu'en **contexte** dans
  le diff. L'énumération des sept, leur ordre et le libellé **Adresse de la page** sont intacts au
  caractère près. Le diff se réduit à **une phrase ajoutée** en fin d'étape.
- **Le mot « huit » n'est écrit nulle part**, et la liste n'a pas été portée à huit éléments.
- **L'`alt` de `:70` n'a pas bougé d'un caractère** : il décrit la capture de la page **318**, qui
  montre bien **sept** rangées — **il est exact pour son image**, et l'aligner sur huit aurait rendu
  faux un texte vrai.
- **`captures/sommeil-page-zone-laterale.png` intacte** ; invariant **126 références / 126 `.png`**
  tenu.
- « **Descendez sous la dernière** » reste juste dans les deux cas : la dernière rangée est **Parent**,
  avec ou sans **Révisions**.
