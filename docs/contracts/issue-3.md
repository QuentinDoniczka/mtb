# Contrat d'interface — Issue #3 — Type de contenu Portée

**Gelé le 2026-08-16.** Troisième contrat du projet, premier à livrer un type de contenu.

Cette issue est **purement extension** : elle n'écrit pas une ligne de thème et n'enregistre aucun
bloc. Le thème ne la consomme donc pas encore — mais **les epics « Composants du contenu structuré »
(#12–#14) et « Gabarits » la consommeront entièrement**. C'est pour elles que ce document existe.

Il s'applique **par-dessus** `docs/contracts/issue-1.md`, qui reste la règle générale de tout module
de `mtb-core` et qui n'est ni amendé ni contredit ici, à une exception près, signalée en section 10.

---

## 1. Ce que l'issue livre

Le type de contenu **Portée**, son écran de saisie en français, sa validation, et **six fonctions de
lecture**. Trois modules, aucun fichier hors de cette empreinte :

```
wp-content/plugins/mtb-core/includes/content/portee/**
wp-content/plugins/mtb-core/includes/fields/portee/**
wp-content/plugins/mtb-core/includes/query/portee/**
```

**Un seul module `query/portee/`** porte les six fonctions, et non un dossier par fonction comme le
suggère la recette de `issue-1.md` §10 (`query/derniere-portee/`). L'empreinte fichiers de l'issue
l'emporte sur l'exemple. **Ce n'est pas une infraction** — à lire ainsi en revue.

---

## 2. L'écran de saisie : un formulaire, pas un canevas

**Décision de lot, prise par `/lead-mtb` pour #3, #4 et #5 ensemble.** `mtb_portee` désactive
l'éditeur de blocs (`use_block_editor_for_post_type` → `false`) et présente un **formulaire ordonné**.

Le motif tient en une phrase, celle que le guide de l'éleveuse doit reprendre **telle quelle** :

> **Les pages se composent, les fiches se remplissent.**

Une portée, c'est douze faits et un paragraphe. L'éditeur de blocs est fait pour agencer une page, pas
pour saisir un enregistrement : y mettre les douze faits les relègue **sous** le canevas, et demande à
Fabienne de faire défiler pour atteindre ce pour quoi elle est venue. Les blocs du catalogue
(BRIEF §6) restent entiers, sur les Pages, là où ils servent. **Ce n'est pas deux paradigmes subis,
c'est une frontière nette.**

Conséquences à connaître, sans les maquiller :

- La dette **T7** (bloc Bouton rendu hors des quinze jetons) devient **inatteignable sur une portée** :
  il n'y a pas d'inséreur sur cet écran.
- La question de la perte d'iframe du canevas quand une meta box est enregistrée — soulevée au
  brainstorm, non mesurable depuis cette chaîne — **devient sans objet**. Aucune architecture n'est
  fondée ici sur un fait invérifiable.
- Le choix des photos passe par **la modale média du cœur**, celle que la boîte « Photo principale »
  de WordPress emploie depuis dix ans. Elle dépend de JavaScript ; l'éditeur de blocs en dépend
  autant. **Ce n'est pas une dette de cette issue.**

---

## 3. Enregistrement du type de contenu — valeurs définitives

| Paramètre | Valeur | Pourquoi elle est définitive |
|---|---|---|
| `name` | `mtb_portee` | Gelé par `/lead-mtb` pour tout le lot |
| `rewrite.slug` | `portees` | voir l'encadré ci-dessous |
| `rewrite.with_front` | `false` | idem |
| `has_archive` | `true` | idem |
| `capability_type` | `post` + `map_meta_cap => true` | Le rôle **Éditeur** natif obtient la maîtrise complète des portées **sans qu'aucune capacité ne soit ajoutée**. Aucun `add_cap`, aucun `add_role` |
| `show_in_rest` | `true` | Les sélecteurs des composants #12–#14 en auront besoin |
| `supports` | ⚠️ **PÉRIMÉ — voir §19.2.** Valeur réelle : `array( 'title', 'editor', 'revisions', 'thumbnail' )` | `editor` a été **rétabli** par §19.2 : l'éditeur natif reste sous le titre. `custom-fields` demeure exclu — le panneau « Champs personnalisés » emploie des mots interdits (`MASTER.md` §10.4) |
| `menu_icon` | `dashicons-pets` | Fourni par le cœur, aucune requête tierce |

> **Ces paramètres ne pourront plus changer par une voie conforme.**
> L'empreinte du chargeur vaut `MTB_CORE_VERSION` + la **liste triée des types et taxonomies `mtb_`
> enregistrés** (`class-loader.php:214-218`, vérifié). Elle change quand un type **apparaît ou
> disparaît**, jamais quand **ses paramètres** changent. Modifier `slug`, `with_front` ou
> `has_archive` plus tard ne régénérerait donc jamais les règles de réécriture : les URL de portées
> tomberaient en **404 silencieux**, sur un site répondant 200. C'est la dette **T6** de `ETAT.md`,
> et c'est la raison pour laquelle `has_archive` s'allume **maintenant** — il n'y aurait pas de
> chemin conforme pour le rallumer.

`has_archive => true` livre `/portees/` **avant** qu'aucun gabarit d'archive n'existe : l'URL répond
dès aujourd'hui à travers `templates/index.html`, qui n'a pas de `<h1>` (dette **T3**). Assumé,
signalé, à solder par l'epic Gabarits.

**Q4 (URL accentuées du site source) reste ouverte et ne bloque pas.** Elle porte sur le **sort des
27 anciennes URL**, que #24 (`seo`) traitera en **301** — ce que **D5** autorise explicitement.

**Obligation portée aux autres issues** : ne jamais créer une **Page** dont l'adresse serait
`portees` — elle entrerait en conflit avec l'archive du type de contenu.

---

## 4. L'identifiant est le titre

`post_title` **est** l'identifiant de la portée, exactement tel que Fabienne le tape : `A3 2025`,
`L 1995`. **Aucune méta `_mtb_identifiant`, aucun titre recomposé à l'enregistrement.**

- Le texte fantôme du champ titre est remplacé par `add_filter( 'enter_title_here', … )` :
  **« Identifiant de la portée — exemple : A3 2025 »**. Ce filtre est honoré par l'éditeur classique.
- **Aucune normalisation, jamais.** L'identifiant est **recopié** (D11). Aucune validation de format.
- Elle ne voit jamais les mots « slug » ni « permalien » (`MASTER.md` §10.4).
- Un **doublon** d'identifiant produit un **avertissement**, jamais un refus d'enregistrer.

Pourquoi le titre plutôt qu'une méta : la recherche native, la liste d'administration, la corbeille et
les révisions l'affichent gratuitement, et `post_name` s'en déduit seul.

**Le commentaire de l'éleveuse est `post_content`** — pas une méta. Révisions, enregistrement
automatique, recherche et `wp_kses_post` natifs ; et c'est là qu'atterrira la prose intégrale des
pages migrées (**D4**). Libellé à l'écran : **« Commentaire de l'éleveuse »** (`MASTER.md` §10.2) —
**jamais « Texte libre »**, qui est le mot du brief et non celui de l'écran.

> ⚠️ **Contrainte technique devenue SANS OBJET — voir §19.2.** Elle disait : si le support `editor`
> est retiré pour replacer `wp_editor()` dans une boîte ordonnée, l'identifiant de la zone de texte
> doit rester exactement `content`, faute de quoi l'enregistrement automatique et les révisions
> cessent de fonctionner en silence.
> **Le code livré ne retire plus le support `editor` et n'appelle ni `wp_editor()` ni
> `remove_post_type_support()`.** L'éditeur natif est sous le titre, et un intitulé
> « **Commentaire de l'éleveuse** » le nomme (§19.9).

---

## 5. Clés de méta — seize clés, gelées

Toutes préfixées `_mtb_`, **tiret bas initial obligatoire** (`issue-1.md` §6 et arbitrage 8) : il rend
la méta *protégée*, donc jamais listée dans « Champs personnalisés ». C'est la garantie **mécanique**
qu'aucun mot technique n'atteint l'écran de Fabienne. `auth_callback` explicite sur chacune :
`current_user_can( 'edit_post', $object_id )`.

| Clé | Type stocké | Valeur quand la donnée manque | Note |
|---|---|---|---|
| `_mtb_date_naissance` | `string` `AAAA-MM-JJ` | `''` | Tri lexicographique = tri chronologique |
| `_mtb_disponibilite` | `string` | `''` | Liste close `disponible` \| `reserve` \| `passee` |
| `_mtb_males` | **`string`** | `''` | **`string` et non `int`** : c'est la seule façon de distinguer « **0 mâle** », qui est un fait, de « **non renseigné** », qui n'en est pas un |
| `_mtb_femelles` | **`string`** | `''` | idem |
| `_mtb_chiots` | `array` de tableaux | `array()` | Sous-clés `nom`, `sexe`, `lof`, `devenir`. **Hors REST** |
| `_mtb_galerie` | `array` d'entiers | `array()` | Identifiants de fichiers joints, **dans l'ordre choisi** |
| `_mtb_pere_type` | `string` | `''` | Liste close `fiche` \| `exterieur` |
| `_mtb_pere_fiche` | `int` | `0` | Identifiant d'un contenu `mtb_chien` |
| `_mtb_pere_nom` | `string` | `''` | |
| `_mtb_pere_elevage` | `string` | `''` | |
| `_mtb_pere_sante` | `string` multiligne | `''` | **Uniquement pour un parent sans fiche.** Recopié |
| `_mtb_mere_type` | `string` | `''` | |
| `_mtb_mere_fiche` | `int` | `0` | |
| `_mtb_mere_nom` | `string` | `''` | |
| `_mtb_mere_elevage` | `string` | `''` | |
| `_mtb_mere_sante` | `string` multiligne | `''` | |

**`_mtb_chiots` reste hors REST.** Un tableau d'objets y exige un schéma complet, faute de quoi la
méta est **refusée en silence** — un mode de panne détestable pour une donnée d'élevage. Aucun
consommateur REST n'est prévu ; le jour où il y en aura un, c'est son issue qui écrira le schéma.

**`_mtb_*_type` fait foi, toujours.** Une branche parentale inactive **conserve sa valeur** (pour que
Fabienne puisse basculer d'avant en arrière sans rien reperdre), donc `_mtb_pere_fiche` peut encore
valoir `12` alors que la portée déclare un étalon extérieur. **Aucun module ne déduit jamais le type
d'un parent de la présence d'un identifiant** — le faire afficherait une généalogie fausse, donc
violerait **D11**.

---

## 6. L'écran de saisie — libellés et vocabulaire

Tout libellé ci-dessous est **attesté** dans `design-system/MASTER.md`, sauf ceux de la section 6.3,
qui sont signalés comme tels.

### 6.1 Libellés attestés — à reprendre verbatim

`Portée` / `Portées` · **Identifiant de la portée** · **Date de naissance** · **Père** · **Mère** ·
**Père — étalon extérieur** · **Nom** · **Élevage** · **Nombre de mâles** · **Nombre de femelles** ·
**Chiot** · **Sexe** · **N° LOF** · **Devenir** · **Mâle** · **Femelle** · **Disponibilité** ·
**Chiots disponibles** · **Tous réservés** · **Portée passée** · **Galerie photos** ·
**Commentaire de l'éleveuse** · **Photo principale** · **Non renseigné** · *(obligatoire)* ·
**Erreur : …** · **Née le** · **Liste des chiots non renseignée.**

Mots **interdits à l'écran** (`MASTER.md` §10.4), y compris dans l'aide contextuelle : `custom post
type`, `CPT`, `métadonnée`, `meta`, `champ personnalisé`, `taxonomie`, `slug`, `permalien`, `extrait`,
`média` (dire « photo »), `image mise en avant` (dire « photo principale »), `alt`, `template`.

### 6.2 Père et mère — les deux chemins sont à égalité

**Le parent hors élevage n'est pas une soupape rare : c'est probablement le cas courant.** La page
`/travail/` du site source cite majoritairement des chiens d'autres élevages (« de la Légion des
Loups », « des Terres d'Alfheim », « Pegaz Eenhoorn », « du Domaine de Drenthe »). **Si l'écran rend
la saisie d'un nom libre plus lente ou moins évidente que le choix d'une fiche, la conception est
fausse pour le cas le plus fréquent.**

Deux boutons radio dans un `<fieldset><legend>`, **jamais** une liste déroulante mélangeant les deux
mondes, **jamais** un chemin « étalon extérieur » qu'on n'atteint qu'après avoir cherché une fiche
inexistante :

```
Père
  ( ) Il a une fiche sur le site     → [ liste des chiens, ordre alphabétique ]
  ( ) Étalon extérieur               → Nom [______]   Élevage [______]

Mère
  ( ) Elle a une fiche sur le site   → [ liste des chiens, ordre alphabétique ]
  ( ) Elle n'a pas de fiche sur le site → Nom [______]   Élevage [______]
```

**Le libellé de la mère n'est pas « Étalon extérieur ».** *Étalon* désigne un reproducteur mâle ;
`MASTER.md` §10.2 ne l'atteste que pour le père et se contente d'« Idem » pour la mère. Employer
« lice extérieure » **inventerait** un terme d'élevage. La formulation descriptive retenue n'invente
rien et ne commet aucun accord fautif.

Le sous-bloc de la branche non cochée est **masqué par JavaScript** et **simplement affiché sans lui**.
**Les deux branches sont toujours soumises et assainies** ; on n'écrit que celle du radio coché.

### 6.3 Libellés non attestés — composés, jamais inventés

Aucun n'est un fait d'élevage. Ratifiés par `/lead-mtb`.

| Libellé | Origine |
|---|---|
| **Tests de santé du père** / **Tests de santé de la mère** | Composé de « Tests de santé des parents » (BRIEF §5.1) et de « Père » / « Mère » (§10.2). **`MASTER.md` §10.2 n'a aucune ligne de santé côté Portée** — les lignes santé n'existent que pour le type Chien. C'est un **manque de `MASTER.md`**, consigné en dette de documentation à la charge de `lead-design-mtb`, et non un choix de cette chaîne |
| « Identifiant de la portée — exemple : A3 2025 » | Texte fantôme du champ titre |
| « Il a une fiche sur le site » / « Elle a une fiche sur le site » / « Elle n'a pas de fiche sur le site » | Boutons radio, section 6.2 |
| « Aucune fiche de chien n'est encore enregistrée. » / « — Aucune fiche — » | État de dégradation, section 7 |
| « Ajouter un chiot » · « Retirer ce chiot » · « Chiot ajouté. » | Actions à l'infinitif (§10.1) |
| ~~« Ajouter des photos » · « Retirer cette photo » · « Monter » / « Descendre »~~ | ⚠️ **PÉRIMÉ.** Les trois libellés de galerie ont été arbitrés en **§19.11** : « **Monter la photo 1** » / « **Descendre la photo 1** » / « **Retirer la photo 1** ». Seul « Ajouter des photos » subsiste. *C'est cette ligne, laissée en place, qui a produit un commentaire menteur dans `sauvegarde.php` — d'où l'avertissement.* |
| « La fiche de chien liée n'existe plus. Choisissez une autre fiche, ou saisissez le père comme étalon extérieur. » | Avis |

### 6.4 Les chiots

Une méta unique `_mtb_chiots`, **tableau de tableaux**, réindexé par `array_values()` après
assainissement. Champs nommés `chiots[0][nom]`, `chiots[0][sexe]`, `chiots[0][lof]`,
`chiots[0][devenir]` — **jamais `chiots[][nom]`**, dont les crochets vides désolidarisent les quatre
sous-champs.

- **Ajout** : clonage JavaScript d'un `<template>`, **plus trois rangées vierges toujours présentes**
  en secours, pour que l'écran reste utilisable sans JavaScript.
- **Suppression** : une case **« Retirer ce chiot »** par rangée, qui fonctionne **sans** JavaScript.
  La suppression d'une ligne du milieu ne décale rien : les indices sont explicites et réindexés au
  serveur.
- **Sexe** : liste close **Mâle** / **Femelle**, **première option vide**. Un `Mâle` présélectionné
  inventerait un fait d'élevage (**D11**).
- Une rangée dont les quatre sous-champs sont vides est **écartée** à l'enregistrement.
- **N° LOF : recopié, jamais normalisé** (décision 12 de `ETAT.md`).

### 6.5 Compteurs et liste des chiots sont indépendants

*Nombre de mâles* et *Nombre de femelles* ne sont **jamais** calculés depuis la liste, et une
divergence entre les deux **n'est jamais signalée comme une erreur**. Les compteurs sont connus à la
naissance ; les n° LOF arrivent des semaines plus tard ; `L 1995` aura des compteurs sans liste.
**Ce ne sont pas les mêmes faits** — ce n'est donc pas une violation de la contrainte 3.

---

## 7. Dégradation quand `mtb_chien` n'existe pas

La chaîne #4 livre `mtb_chien` **en parallèle** de celle-ci. Le code doit tourner proprement sans lui.

**« Type non enregistré » et « aucune fiche publiée » sont traités identiquement** : la liste ne
contient que `— Aucune fiche —`, le bouton radio « Il a une fiche sur le site » est désactivé, et une
phrase honnête et non technique s'affiche — **« Aucune fiche de chien n'est encore enregistrée. »**
Vraie dans les deux cas.

**Aucune fatale, aucune notice, aucun écran cassé.** Un `WP_Query` sur un type non enregistré renvoie
zéro ligne sans rien lever ; la garde `post_type_exists()` sert le **message**, pas la survie.

Deux pièges à ne pas rouvrir :

- **Un `<option>` d'un `<select>` désactivé n'est pas soumis.** On ne désactive donc jamais le bouton
  radio `fiche` quand la valeur **stockée** vaut déjà `fiche` — sinon le premier enregistrement
  effacerait la relation.
- `get_permalink( 0 )` et `get_the_title( 0 )` renvoient les valeurs **du contenu global courant**.
  Aucun appel sans avoir vérifié `$id > 0` **et** `null !== get_post( $id )`.

Côté lecture, un identifiant qui ne résout plus (fiche mise à la corbeille) donne l'état
`donnee_absente` — **jamais un lien mort**.

---

## 8. Validation — on ne lui fait jamais perdre son texte

**Deux champs obligatoires, et deux seulement** : *Identifiant de la portée* (il fait le titre et
l'URL) et *Date de naissance* (elle fait le tri, la navigation entre portées et « la dernière
portée »). **Tout le reste est facultatif** — y compris la disponibilité, les compteurs et les chiots.

| Situation | Comportement |
|---|---|
| Un obligatoire manque, portée **pas encore publiée** | Enregistrée **en brouillon**, avec un avis nommant les champs manquants **par leurs libellés exacts** |
| Un obligatoire manque, portée **déjà publiée** | **On ne la dépublie pas.** Rétrograder une URL vivante la ferait tomber en 404 pour les familles qui l'ont en favori. On avertit ; le public dégrade proprement (§9.3) |
| Doublon d'identifiant | **Avertissement**, publication acceptée |
| Quoi qu'il arrive | **Jamais de `wp_die`, jamais de refus d'enregistrement, jamais de redirection qui perde le `$_POST`** |

`(obligatoire)` figure dans l'étiquette (`MASTER.md` §10.3).

**Aucun préremplissage, nulle part (D11)** : pas de date du jour, pas de disponibilité par défaut, pas
de sexe par défaut. **Une portée sans disponibilité n'affiche aucun badge** — §3.3 ne connaît que
trois états et il n'y a pas de quatrième. Défaulter les vieilles portées sur *Portée passée* serait
une invention.

### 8.1 La dérogation à `issue-1.md` §10 — écrite ici parce qu'elle doit l'être

`issue-1.md` §10 pose : « **un champ absent du `$_POST` est traité comme vide, jamais comme "ne pas
toucher", sauf décision contraire documentée dans l'issue** ». **Cette issue est la décision
contraire, et la voici :**

> **La sauvegarde sort immédiatement si le champ de nonce `mtb_portee_ecran` n'est pas présent dans
> `$_POST`.**

Sans cette sortie, l'**édition rapide**, l'**édition en lot**, `wp_publish_post()` et
l'**enregistrement automatique** — qui postent tous un formulaire partiel — **effaceraient tous les
champs de la portée**. C'est le test de non-régression le plus important de l'issue.

**Conséquence à connaître pour l'issue `admin/`** : l'édition rapide et l'édition en lot ne toucheront
**jamais** aux champs d'une portée. Une issue qui voudrait un réglage de disponibilité en ligne depuis
la liste devra poser **son propre nonce**.

Ordre imposé de toute sauvegarde, sans exception (`issue-1.md` §10) : sortie si
`wp_is_post_autosave()` ou `wp_is_post_revision()` → **nonce** → `current_user_can( 'edit_post' )` →
`wp_unslash()` → assainissement champ par champ → écriture.

`wp_unslash()` avant tout assainissement n'est pas un détail de style : l'oublier enregistre
`L\'Élevage`, c'est-à-dire **une donnée d'élevage altérée**.

---

## 9. Fonctions de lecture exposées au thème

Six fonctions, déclarées dans l'**espace de noms global**, sous `if ( ! function_exists( … ) )`, dans
`includes/query/portee/`. **Aucun hook** (`issue-1.md` §2).

```php
mtb_get_portee( int $id ): ?array
mtb_get_derniere_portee(): ?array
mtb_get_portee_par_identifiant( string $identifiant ): ?array
mtb_get_portees( array $args = array() ): array
mtb_get_portees_du_chien( int $chien_id ): array
mtb_get_portee_voisine( int $id, string $sens ): ?array
```

**Le préfixe est `mtb_get_`**, conformément à `issue-1.md` §6 et §10. `get` est **la seule exception
anglaise tolérée** du projet, elle n'est **jamais vue par Fabienne**, et elle est consignée comme
telle dans `ETAT.md` plutôt que laissée silencieuse.

**Le thème appelle toujours ces fonctions derrière `function_exists()`** (`issue-1.md` §8, exigence
**D12** : extension désactivée ⇒ page **dégradée**, jamais d'écran blanc).

### 9.1 Deux fonctions méritent une justification

**`mtb_get_portees_du_chien( int $chien_id )` appartient à cette issue, et à elle seule.** Principe
tranché par `/lead-mtb` : **le type qui possède la donnée possède la lecture**. Les portées sont à
`mtb_portee`. La chaîne #4 l'appelle sous `function_exists()`.

Ce n'est pas une question d'élégance. Le chargeur parcourt `includes/query/` **par ordre
alphabétique** : `chien` passe avant `portee`. Deux déclarations concurrentes, et la garde
`function_exists()` aurait fait gagner celle de #4 **en silence**, sans erreur fatale, sur un site
répondant 200.

Elle ne dépend **d'aucune fonction de #4** : un `meta_query` en `OR` sur `_mtb_pere_fiche` /
`_mtb_mere_fiche`, **assorti du test de `_mtb_*_type` sur `fiche`** — indispensable, puisqu'une
branche inactive conserve sa valeur (section 5). Sans ce test, la fiche du chien 12 afficherait une
portée qui n'est pas la sienne : **une généalogie fausse, donc D11 violée**.

**`mtb_get_portee_voisine( int $id, string $sens )`** ne figure pas dans la checklist de l'issue. Elle
y a été **ajoutée délibérément** : `MASTER.md` §7.4 point 6 impose « **Portée précédente** /
**Portée suivante**, par date de naissance », le thème n'a pas le droit d'interroger la base, et
`mtb_get_portees()` ne la remplace pas — il faut une requête **positionnelle**. Sans elle, l'epic
Gabarits serait bloquée. `$sens` vaut `precedente` | `suivante` ; le tri se fait sur
`_mtb_date_naissance`, une égalité de date est départagée par `ID`, et les extrémités renvoient
`null`.

### 9.2 Forme de retour d'une portée

> ⚠️ **La forme décrite ci-dessous a été REMPLACÉE par l'enveloppe de champ de la section 19.5.**
> Elle est conservée pour l'histoire du contrat, **elle ne décrit plus le code livré.**
>
> **Les clés suivantes n'existent plus** : `url` (devenue `lien`), `protegee` (devenue `protege`),
> `date_naissance_texte`, `disponibilite_libelle`, `males_texte`, `femelles_texte`, `sexe_libelle`,
> `nom_texte`, `titre_fiche`, `role_du_chien`, `role_du_chien_libelle`. **Une chaîne future qui code
> contre cette liste code contre une forme morte : lire la section 19.5, et elle seule.**

Forme initiale, périmée :

```
id, identifiant, titre_public, url, statut, protegee, etat,
date_naissance, date_naissance_texte, date_naissance_libelle, annee,
disponibilite, disponibilite_libelle,
males, males_texte, femelles, femelles_texte, effectif_texte,
pere, mere,
chiots_colonnes, chiots, chiots_message,
galerie, photo
```

**Ce qui reste vrai de cette section**, et qui vaut pour la forme actuelle :

- `chiots_colonnes` → **toujours** `array( 'Nom', 'Sexe', 'N° LOF', 'Devenir' )`.
- **Toutes les clés sont toujours présentes** : `''` pour une chaîne, `array()` pour une liste,
  `0` pour un identifiant. **Jamais une clé absente, jamais un avertissement PHP.**
- Les fonctions à résultat unique renvoient `null` quand rien ne correspond ; les fonctions de liste
  renvoient `array()`, **jamais `null`**.
- **Les données renvoyées ne sont pas échappées.** L'échappement appartient au rendu.

**Une exception ajoutée après implémentation** : `pere.sante` / `mere.sante` rendent `valeur => ''`
**et `affichage => ''`** quand `etat === 'fiche'`. Motif : les tests d'un parent qui a une fiche vivent
**sur cette fiche** (section 9.3). Rendre « Non renseigné » ferait afficher à un composant
« Tests de santé du père : Non renseigné » **sur un chien testé** — un fait d'élevage faux, donc
**D11** enfreinte. La donnée n'est pas *absente*, elle est *ailleurs*. Même logique que l'exception de
l'`elevage` d'un parent extérieur (§19.5). **La faute est ainsi rendue impossible plutôt que
documentée** : une règle qu'une dizaine de composants doivent se rappeler d'appliquer finit par être
oubliée une fois.

**Les données renvoyées ne sont pas échappées.** L'échappement appartient au rendu (`issue-1.md` §12).

**`titre_fiche` est le titre du contenu `mtb_chien`, pas nécessairement le nom d'usage.** Cette issue
ne présume rien du choix de #4. Pour le nom d'usage et le nom complet avec affixe qu'exige
`MASTER.md` §7.4, le composant carte parent appelle **la fonction de lecture de #4**.

### 9.3 Ce que cette issue ne fournit pas, volontairement

**Les tests de santé d'un parent qui a une fiche ne sont pas dans la portée, et n'y seront jamais.**
BRIEF §5.1 les dit « repris de la fiche quand le parent est une fiche Chien » : c'est la contrainte 3
appliquée à la lettre, **une saisie unique, la fiche fait foi**. Cette issue expose `pere.fiche_id` ;
c'est le composant carte parent (#12–#14) qui appelle la fonction de lecture de #4.

Le champ `pere.sante` / `mere.sante` **n'est renseigné que pour un parent sans fiche**.

### 9.4 Règles de requête

- Le tri se fait sur `_mtb_date_naissance` (`AAAA-MM-JJ`, tri lexicographique = tri chronologique),
  **jamais sur `post_date`**, qui est la date de **saisie**. Bénéfice gratuit de la jointure `meta` :
  **une portée sans date ne peut pas devenir « la dernière »**.
- Toute liste porte `'post_status' => 'publish'` **et `'has_password' => false'`.
- **Aucun transient** (`issue-1.md` §9), **aucun cache maison** : `WP_Query` cache déjà ses résultats
  dans le groupe `posts` contre `last_changed`, invalidé à chaque `save_post`. Un `wp_cache_set`
  maison serait un second mécanisme à invalider à la main.
- Les dates se formatent par `DateTimeImmutable::createFromFormat( '!Y-m-d', … , wp_timezone() )` puis
  `wp_date( get_option( 'date_format' ), … )`. **Le `!` en tête n'est pas décoratif** : sans lui,
  l'heure courante est reprise et une portée née le 31 peut basculer au 1er selon le fuseau.
  `date_i18n()` est **proscrite**.

---

## 10. États spéciaux

| État | Émis par le serveur | Rendu attendu du thème |
|---|---|---|
| `aucune_portee` | `mtb_get_derniere_portee()` renvoie `null` ; `mtb_get_portees()` renvoie `array()` | État vide propre ; le composant ne casse pas la page (**D12**) |
| `donnee_absente` | Toute clé `*_texte` vaut **« Non renseigné »** ; `pere.etat` / `mere.etat` valent `donnee_absente` | Imprimer la chaîne fournie. **Jamais un tiret, jamais « Aucun », jamais « Non testé »** (§9.3) |
| `parent_hors_elevage` | `pere.etat` / `mere.etat` | Nom et élevage en clair, **sans lien, sans carte grisée** ; la forme d'affichage **ne change pas** (§9.4) |
| `page_protegee` | `mtb_get_portee()` renvoie `etat => 'page_protegee'` et une **charge réduite** | Le formulaire natif ; aucune donnée d'élevage n'est disponible, donc rien à masquer côté thème |

**`has_password => false'` sur les six fonctions est un acompte sur la dette T8, pas son solde** :
l'archive `/portees/` est servie par la requête principale du cœur, qui ne filtre pas `has_password` ;
le **sitemap** et la **recherche** non plus. **#23 (`prive`) reste dû.**

---

## 11. Chaînes fournies finies par le serveur

Le thème les **imprime**, il ne les compose **jamais**. Le **principe** de cette section reste
intégralement valable ; **les noms de clés, non** — ils sont ceux de l'enveloppe de la section 19.5,
où chaque valeur porte son `libelle`, sa `valeur` brute et son `affichage` fini.

Concrètement, le serveur fournit finis : le titre public (« Portée A3 2025 ») · le libellé de
disponibilité (« Chiots disponibles » / « Tous réservés » / « Portée passée » ; `''` = **aucun
badge**) · la date **à la fois brute et formatée** · son libellé (« Née le ») · l'effectif (« 3 mâles,
2 femelles ») · le libellé de sexe d'un chiot (« Mâle » / « Femelle ») · `chiots_colonnes` · le
message d'absence de liste (« Liste des chiots non renseignée. ») · les libellés « Père » / « Mère » ·
le rôle d'un chien dans une portée.

L'effectif accorde le singulier (« 1 mâle, 0 femelle ») et **n'existe pas** si les deux compteurs sont
vides — on n'écrit pas « 0 mâle » quand on ne sait pas.

**Séparation clé / libellé, règle de lot** : la **clé stockée** est stable, sans accent, courte
(`disponible`, `reserve`, `passee`, `fiche`, `exterieur`, `male`, `femelle`) ; le **libellé affiché**
vient de `MASTER.md`. Un ajustement de typographie ne doit **jamais** réécrire la base.

*(Les clés de disponibilité sont celles de `MASTER.md` §3.3 lignes 202-204, table normative qui porte
aussi les badges et leurs ratios de contraste mesurés. `docker/fixtures/portees.json` emploie d'autres
valeurs — `chiots_disponibles`, `tous_reserves`, `portee_passee`, `fiche_chien`, `nom_libre` — et
**c'est le fichier de fixtures qui devra être converti**, par l'issue qui livrera l'import. Hors
empreinte de cette issue, signalé à `/lead-mtb`.)*

---

## 12. Blocs enregistrés

**Aucun.** Cette issue ne livre aucun bloc, aucun `block.json`, aucune catégorie. La catégorie `mtb`
(« Mont Brabant ») reste due par **la première issue de composants** (`issue-1.md` §10).

Aucun hook, aucun filtre n'est exposé par cette issue. **La surface est close : six fonctions, rien
d'autre.**

---

## 13. Interdits

- Le thème n'interroge **jamais** la base : `WP_Query`, `get_posts`, `get_post_meta`, `get_terms`,
  `$wpdb`. *Frontière vérifiable par `grep` (`issue-1.md` §8).*
- Le thème n'appelle **jamais** `MTB\Core\*`.
- Le thème ne **décide jamais** ce qu'est « la dernière portée », et ne refait jamais un tri par date.
- Le thème ne **compose jamais** une chaîne du domaine : « Portée » + l'identifiant, « 3 mâles,
  2 femelles », un libellé de disponibilité, « Père » / « Mère ».
- Le thème ne **reformate jamais** une date, un **N° LOF**, un résultat de test de santé, un devenir
  de chiot. Ces valeurs sont **recopiées, jamais normalisées** (décision 12).
- Le thème n'invente **jamais** une mention d'absence : ni tiret, ni « Aucun », ni « Non testé », ni
  « — ». Le serveur fournit **« Non renseigné »**.
- L'extension n'émet **aucune règle visuelle ni mise en page**.
- Aucune issue n'édite `mtb-core.php` ni `class-loader.php` ; aucun module n'appelle
  `flush_rewrite_rules()` ni n'emploie `init` 99.
- **Aucune suppression** : ce module n'appelle jamais `wp_delete_post` ni `delete_post_meta`. Retirer
  une photo de la galerie retire un **identifiant** du tableau, **jamais le fichier de la
  médiathèque** (contrainte 4).

**Une seule exception explicite** : pour afficher une photo, le thème appelle
`wp_get_attachment_image( $photo['id'], $taille )` en **choisissant lui-même la taille** — le choix
d'une taille d'image est une décision de présentation, elle lui appartient.

---

## 14. Obligations imposées aux autres issues

1. **Tout créateur de portée écrit `_mtb_date_naissance`, fût-elle vide.** Une portée dont la clé
   n'existe pas du tout est **invisible** de `mtb_get_derniere_portee()` et de toutes les listes, par
   construction de la jointure. Vise l'import WP-CLI et la reprise de contenu (epic 8).
2. **Aucun module ne réécrit la branche parentale inactive** ni ne déduit un type de parent de la
   présence d'un identifiant : `_mtb_*_type` fait foi (section 5).
3. **Tout composant tableau émet `data-libelle="…"` sur chaque `<td>`**, avec exactement les chaînes
   de `chiots_colonnes` — **lues depuis la fonction, jamais réécrites** (décision 10 de `ETAT.md`,
   `MASTER.md` §7.6). C'est ce qui permet au tableau des chiots de se déplier en lignes libellées sous
   48 rem **sans conteneur à défilement horizontal**.
4. **L'issue `admin/` n'ajoute aucune écriture depuis la liste sans son propre nonce** (section 8.1).
5. **#23 (`prive`) reste dû** : `has_password => false'` couvre les six fonctions, **pas** l'archive
   `/portees/`, ni le sitemap, ni la recherche (**T8**).
6. **Ne jamais créer une Page d'adresse `portees`** (section 3).

---

## 15. Valeurs et clés gelées

Type `mtb_portee` · archive `/portees/` · `with_front = false` · route REST
`/wp-json/wp/v2/mtb_portee` · action de nonce `mtb_portee_ecran` · poignée de script
`mtb-portee-ecran` · clé de transient d'avis `mtb_portee_avis_<user_id>_<post_id>`, durée 60 s
(§19.3 — **l'argument d'URL `mtb_avis` de la première rédaction n'existe plus**) ·
les **seize clés `_mtb_*`** de la section 5 ·
les listes closes `disponible|reserve|passee`, `fiche|exterieur`, `male|femelle`,
`precedente|suivante`.

---

## 16. Arbitrages

| # | Désaccord | Décision | Raison |
|---|---|---|---|
| 1 | Écran de blocs ou formulaire classique | **Formulaire classique**, pour les trois types du lot | Une fiche ne se compose pas, elle se remplit. En blocs, les douze faits tombent sous le canevas. Rend en outre **T7 inatteignable** et la question de l'iframe **sans objet** — on ne fonde pas une architecture sur un fait qu'on ne peut pas mesurer depuis cette chaîne. Arbitrage de lot, pas d'issue |
| 2 | Identifiant : méta ou titre | **Le titre** | Recherche, liste, corbeille et révisions gratuites ; `post_name` déduit ; aucun mot interdit à l'écran. La méta + titre recomposé ajoutait un état désynchronisable pour un gain nul |
| 3 | Commentaire : méta ou `post_content` | **`post_content`** | Révisions, enregistrement automatique, recherche natifs — et c'est là qu'atterrira la prose intégrale des pages migrées (D4) |
| 4 | Chiots : une méta par ligne, des clés numérotées, ou un tableau | **Un tableau dans une méta unique** | Ordre garanti, écriture atomique. Les clés numérotées imposent une **renumérotation** à la suppression d'une ligne du milieu — le décalage qu'on voulait éviter. Rien ne justifie de requêter un chiot |
| 5 | Nommage des fonctions : `mtb_get_*` ou `mtb_*` | **`mtb_get_*`** | `issue-1.md` l'écrit trois fois, dont deux dans une **recette de code** — la forme la plus concrète d'un gel. Réécrire la convention ferait du contrat #1 un document menteur que toute chaîne future lit au démarrage. Arbitrage rendu par `/lead-mtb` après que cette chaîne eut proposé l'inverse |
| 6 | Clés de méta : `mtb_` ou `_mtb_` | **`_mtb_`** | Le tiret bas rend la méta **protégée**, donc jamais listée dans « Champs personnalisés » : garantie mécanique de `MASTER.md` §10.4. `/lead-mtb` a corrigé sa propre consigne sur ce point |
| 7 | Clés de disponibilité : `MASTER.md` §3.3 ou `docker/fixtures/` | **§3.3** (`disponible`/`reserve`/`passee`) | §10 se déclare arbitre, et §3.3 est **normative** : elle porte les badges et leurs **ratios de contraste mesurés**. Aucun CSS livré ne dépend encore de l'un ou l'autre jeu, le choix est sans casse |
| 8 | Compteurs déduits de la liste des chiots ? | **Non, jamais, et aucune divergence signalée** | Faits distincts, connus à des moments différents. Signaler une « erreur » sur une donnée légitime serait un faux positif permanent |
| 9 | Valeur de disponibilité par défaut ? | **Aucune** | Défaulter les vieilles portées sur *Portée passée* inventerait un fait d'élevage (**D11**) |
| 10 | Ajouter `mtb_get_portee_voisine()` hors checklist ? | **Oui** | `MASTER.md` §7.4 l'exige, le thème ne peut pas la produire, elle est dans l'empreinte. Sans elle l'epic Gabarits serait bloquée |
| 11 | Champ **Saillie** de `MASTER.md` §10.2 | **Non livré** | §10 est une table de **vocabulaire** — comment le dire si on le dit — pas un inventaire de champs. L'inventaire est BRIEF §5.1, qui ne la contient pas. Question remontée à l'utilisateur par `/lead-mtb` |
| 12 | Libellé du second chemin, côté mère | **« Elle n'a pas de fiche sur le site »** | *Étalon* désigne un reproducteur **mâle** ; §10.2 ne l'atteste que pour le père. « Lice extérieure » **inventerait** un terme d'élevage |

---

## 17. Points restés ouverts

Aucun ne bloque cette issue. Chacun bloque une issue nommée.

- **Q4 (`ETAT.md`)** — sort des 27 URL accentuées du site source. Ce contrat en fait des **301** à la
  charge de #24 (`seo`), ce que **D5** autorise. Décision de l'utilisateur, pas de cette chaîne.
- **Champ Saillie** — question d'élevage remontée à l'utilisateur : Fabienne consigne-t-elle cette
  date ?
- **Manque de `MASTER.md` §10.2** — aucune ligne de vocabulaire santé côté Portée. Dette de
  documentation à la charge de `lead-design-mtb`, avec les deux autres manques trouvés par #5.
- **`wp mtb import-fixtures`** — `docker/provision/provision.sh` (l. 129-138) l'appelle, `issue-1.md`
  §10 la range dans « #3/#4/#5 ou une issue `infra` », et **les trois empreintes du lot excluent
  `includes/migration/**`**. Prise en charge par `/lead-mtb` au niveau du lot.
- **Colonnes et filtres de la liste des portées** (`includes/admin/**`, hors empreinte). Livrée telle
  quelle, la liste n'affiche ni la date de naissance ni la disponibilité — soit exactement ce que
  Fabienne scrute pour retrouver une portée parmi 27. **Quelle issue les porte ?**
- **Inventaire des modules, `issue-1.md` §11** — le contrat #1 demande à chaque issue livrant un
  module de s'y ajouter, mais ce fichier est **hors de l'empreinte de #3**. Les trois lignes
  `content/portee`, `fields/portee`, `query/portee` doivent y être portées par `/lead-mtb`, faute de
  quoi **la carte du plugin est fausse dès son premier module**.
- **Révisions et métas** — WordPress versionne `post_title` et `post_content`, donc l'identifiant et le
  commentaire, **mais pas les métas**. Une liste de chiots écrasée par erreur n'est **pas récupérable
  depuis une révision**. Le versionnage des métas est faisable, n'est pas demandé par le brief, et
  n'est pas livré ici. **Signalé plutôt que découvert.**
- **Doublon d'identifiant** — toléré (section 4), résolu par `mtb_get_portee_par_identifiant()` vers
  **la plus récente par date de naissance**, de façon déterministe. Mais `issue-1.md` §10 impose à la
  future commande d'import d'être « **indexée sur `identifiant`** » : sur un doublon, cette indexation
  devient **non déterministe**. À trancher par l'issue d'import, pas ici.

---

## 18. Amendements portés après implémentation

Ajoutés au gel initial le 2026-08-16, après le rapport de `dev-back-mtb` et sa vérification en
conteneur. Ils **complètent** le contrat, ils ne le contredisent pas.

### 18.1 Les seize métas sont **hors REST** — correction d'une fuite

La rédaction initiale de la section 5 ne mettait `_mtb_chiots` hors REST que pour une raison de
schéma. **La vraie raison est plus grave et vaut pour les seize clés** : `WP_REST_Post_Meta_Fields`
**ne teste pas le mot de passe d'un contenu**. Une portée protégée exposée en `show_in_rest` livrerait
donc en anonyme, sur `/wp-json/wp/v2/mtb_portee`, la liste de ses chiots, le nom de ses parents et
leurs tests de santé — alors que les six fonctions de lecture, elles, ne fuient pas (section 10).

BRIEF §8 veut qu'une page protégée n'apparaisse « ni dans les index publics, ni dans le sitemap, ni
dans la recherche ». Laisser REST ouvert reviendrait à contourner la porte que cette issue vient de
poser. **`show_in_rest => false` sur les seize clés `_mtb_*`.**

**Le type de contenu garde `show_in_rest => true`** : ce sont les portées (identifiant, titre, lien)
dont les sélecteurs de #12–#14 ont besoin, pas leurs métas. Le jour où un composant aura besoin d'une
méta en REST, **c'est son issue qui l'ouvrira, en traitant le mot de passe**.

*(Ce n'est pas un solde de la dette **T8**, qui reste due à #23 pour l'archive, le sitemap et la
recherche.)*

### 18.2 « Adresse de la page » — la capacité est conservée, c'est le mot qui changeait

La boîte `slugdiv` du cœur porte l'intitulé « **Slug** », mot interdit (`MASTER.md` §10.4). La retirer
**avec** `get_sample_permalink_html` refermait le problème de vocabulaire mais **retirait à Fabienne
une capacité** : si elle saisit un identifiant fautif et corrige le titre après publication,
`post_name` **ne suit pas**, et elle reste avec une adresse fausse qu'elle ne peut plus corriger.
C'est la règle d'or qui est touchée.

Or **`MASTER.md` §10.2 ligne 919 atteste le libellé** : *Adresse de la page* → « **Adresse de la
page** », « jamais *slug*, jamais *permalien* ». Le problème n'était donc pas la capacité, c'était le
mot.

`fields/portee/` retire la boîte du cœur **et l'encart `get_sample_permalink_html` sous le titre**,
qui portait le même mot interdit, et enregistre à sa place une boîte « **Adresse de la page** »
portant le seul champ `post_name`, `sanitize_title()` en entrée, vide autorisé (WordPress régénère
depuis le titre).

### 18.3 Précisions ratifiées

| Point | Décision |
|---|---|
| `etat` d'une portée nominale | **`'ok'`**. Le gel initial ne nommait que `page_protegee` |
| Arguments de `mtb_get_portees()` | Liste **close** : `nombre`, `page`, `ordre`, `annee`, `disponibilite`, `exclure`. Tout autre argument est **ignoré**, jamais présumé propre — un composant passe des attributs venus de l'éditeur |
| `mtb_get_portee_par_identifiant()` | **Aucune jointure méta** : elle récupère les titres identiques et départage en PHP (date décroissante, puis `ID`). Avec une clause `EXISTS` sur `_mtb_date_naissance`, une portée sans date serait **introuvable par son nom** — inacceptable pour une recherche par identifiant. C'est la seule fonction qui déroge à la jointure de la section 9.4 |
| Date impossible (`2025-02-30`) | Traitée comme **absente**, non stockée. L'avis nomme alors « Date de naissance » parmi les champs à remplir |
| Première option de *Sexe* | `value=""`, texte « **Non renseigné** » (§9.3), plutôt qu'une option muette. **Aucun sexe présélectionné** |
| `(obligatoire)` sur l'identifiant | **Absent** : le champ titre n'a pas d'étiquette, seulement le texte fantôme. L'avis nomme le champ manquant |
| Préfixe « Erreur : » des avis d'enregistrement | **Retiré.** `MASTER.md` §10.3 le réserve à une *erreur de champ*. Annoncer une erreur quand l'enregistrement a **réussi** alarme pour rien et contredit **D12** |

### 18.4 Libellés ratifiés, écrits à l'implémentation

Aucun n'est un fait d'élevage. Tous en français métier, aucun mot de §10.4.

**Boîtes** : « La portée » · « Père et mère » · « Les chiots » · « Adresse de la page » (§18.2).

**Étiquettes** : « Fiche du père » / « Fiche de la mère » · « Fiche introuvable » (option qui préserve
une liaison cassée plutôt que de l'effacer) · « Photo introuvable » · « Photo 1 » · les étiquettes
vocales de rangée (« Nom du chiot 1 », « Sexe du chiot 1 », « N° LOF du chiot 1 », « Devenir du
chiot 1 », « Retirer le chiot 1 »).

**Aides** — une ligne par champ, dont trois qui portent une règle du brief et doivent être conservées
telles quelles si le texte est un jour repris :

- *Nombre de mâles / de femelles* — « Laissez vide tant que vous ne le savez pas. **Zéro est une
  réponse, vide n'en est pas une.** » (**D11**, section 5)
- *Père et mère* — « Deux façons de renseigner un parent : choisir une fiche déjà présente sur le
  site, ou saisir son nom et son élevage. **Les deux se valent.** » (section 6.2)
- *Les chiots* — « Les noms et les numéros arrivent souvent plusieurs semaines après la naissance :
  ce tableau se remplit en plusieurs fois. **Les compteurs de la boîte « La portée » ne s'en déduisent
  jamais.** » (section 6.5)
- *Galerie photos* — « Retirer une photo d'ici **ne la supprime jamais du site**. » (contrainte 4)
- *Tests de santé* — « Recopiez les résultats tels qu'ils sont écrits sur les documents, **sans les
  reformuler**. » (décision 12)

**Variante mère de l'avis de liaison cassée**, la section 6.3 n'attestant que la version père :
« La fiche de chien liée n'existe plus. Choisissez une autre fiche, ou saisissez la mère sans fiche
sur le site. »

### 18.4 bis Libellés fournis par le cœur de WordPress — **non vérifiables dans le code de l'issue**

Ces libellés apparaissent sur l'écran d'une portée et dans la fiche d'aide, mais **aucune ligne de ce
module ne les produit** : ils viennent du cœur et l'issue ne les redéfinit pas.

**Publier** · **Mettre à jour** · **Mettre à la corbeille** · **Rétablir** · **Corbeille** (onglet de
la liste) · le champ de recherche en haut de « Toutes les portées ».

**Où s'arrête exactement la maîtrise de cette issue sur le vocabulaire de la corbeille** : le **seul**
libellé de corbeille que ce module pose lui-même est `not_found_in_trash` (« Aucune portée dans la
corbeille. »), et la fiche d'aide ne le cite même pas. Tout le reste du geste — le lien, l'onglet,
l'action de restauration — est du cœur.

**Pourquoi ils sont groupés ici** : le jour où une issue `admin` voudra franciser ou renommer ces
actions, **c'est cette liste qu'il faudra relire**, et elle doit être trouvable d'un coup d'œil plutôt
que dispersée dans le contrat et dans la fiche d'aide. Toute modification de l'un d'eux se répercute
sur `docs/guide/portee-ajouter-une-portee.md`.

**Comportement du cœur à connaître, vérifié en base sur les trois types de contenu du lot** :
**WordPress restaure un contenu sorti de la corbeille en `brouillon`, jamais en `publié`.** Une portée
rétablie revient donc **hors ligne**, champs, chiots et photos intacts, et **rien ne le dit à
l'éleveuse au moment du clic**. Ce n'est pas un défaut de ce module et il n'est pas corrigé ici — il
est **documenté dans la fiche d'aide**, à la charge de qui voudra un jour l'adoucir.

### 18.5 Ce qui n'a pas pu être vérifié

**Le comportement de `ecran.js` dans un navigateur** — clonage du `<template>`, fenêtre de choix des
photos, « Monter » / « Descendre » — **n'a pas été exercé**. Le PHP qui le nourrit l'a été. À couvrir
par `test-integration-mtb` au niveau du lot.

**Constat de méthode à connaître pour les prochains lots** : les chaînes sœurs créent et suppriment
des contenus **dans la même base** pendant les essais. Un premier passage a rapporté à tort un message
de dégradation absent, parce que des fiches `mtb_chien` existaient à cet instant puis avaient disparu.
Un résultat d'essai obtenu en lot parallèle doit être **réobtenu en isolation** avant d'être cru.

---

## 19. Conventions de lot — gelées par `/lead-mtb` après lecture des plans de #4 et #5

Rendues **après** la première implémentation, elles **priment sur les sections antérieures de ce
contrat** partout où elles divergent. Une incohérence entre Portée, Chien et Résultat coûte plus cher
que la reprise : Fabienne apprendrait trois écrans jumeaux à trois dispositions différentes.

### 19.1 Interdit d'assainissement — la perte de données la plus grave du lot

> **`sanitize_text_field()`, `sanitize_textarea_field()`, `wp_strip_all_tags()` et `wp_kses_post()`
> sont interdits sur toute valeur recopiée.**

Toutes passent par `strip_tags()`. Sur une valeur commençant par `<`, PHP supprime **tout jusqu'à un
`>` qui n'existe pas** : la valeur devient vide, **sans erreur, sans avertissement**. C'est **D11
enfreinte par l'outillage**.

La cible la plus exposée de cette issue est `_mtb_pere_sante` / `_mtb_mere_sante` : ce sont des
résultats de dysplasie, où **`<60%` et `≥ 60 %` sont des saisies parfaitement réelles**. Concernés
aussi : `_mtb_*_nom`, `_mtb_*_elevage`, et `nom` / `lof` / `devenir` de chaque chiot.

Assainisseur imposé, écrit dans le module : rejet du non-scalaire → `''` · `wp_check_invalid_utf8()` ·
suppression des **seuls** caractères de contrôle `[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]` · variante
monoligne, sauts de ligne aplatis · variante multiligne, `\r\n` normalisés en `\n` et **conservés** ·
`trim()`. **Rien d'autre.**

C'est sûr **parce que** l'échappement est systématique au rendu (section 13) et que seul un compte
`edit_post` écrit.

**Dette inscrite** : les trois chaînes écrivent chacune leur copie de cet assainisseur, les empreintes
interdisant un fichier partagé. À hisser par une issue ultérieure.

**Précision sur le dé-échappement** : la règle n'est *pas* « ne jamais dé-échapper avant
`update_post_meta` ». `update_metadata()` applique lui-même `wp_unslash()` puis `sanitize_meta()`. La
règle exacte est que **la valeur qui atteint `update_post_meta()` doit être encore échappée**. Un
`wp_unslash( $_POST )` suivi d'un `wp_slash( $assainie )` est un aller-retour **neutre**, et il rend
les assainisseurs plus lisibles puisqu'ils travaillent sur du texte propre. C'est la forme retenue.

### 19.2 L'éditeur natif reste sous le titre

`supports => array( 'title', 'editor', 'revisions', 'thumbnail' )`. **Aucun `wp_editor()` en boîte,
aucun `remove_post_type_support( 'editor' )`.** Ceci **remplace** la contrainte technique de la
section 4 sur l'identifiant `content`, devenue sans objet.

Le déplacement du commentaire sous les champs était techniquement juste, et il est **refusé** :

- il reposait sur **trois détails internes du cœur** (`_wp_translate_postdata`, l'identifiant `content`
  codé en dur dans `autosave.js`, l'ordre d'exécution face à `edit-form-advanced.php`), dont chacun
  peut céder à une mise à jour **en silence** — pas d'erreur, pas de console, juste une prose qui ne
  s'enregistre plus ;
- **#4 garde l'éditeur natif sous le titre.** Déplacé ici, Fabienne trouverait son commentaire en haut
  sur une fiche de chien et en bas sur une portée.

**Contrepartie assumée** : les champs viennent après la prose.

**Le retrait de `forecolor`** (`mce_buttons_2`) est **conservé** : sans lui elle atteint en deux clics
une couleur hors des quinze jetons — la dette **T7** transposée à la prose. **Il doit être borné par
`get_current_screen()` au seul `mtb_portee`** : `mce_buttons_2` est un filtre **global** et ne doit pas
toucher les Pages, qui appartiennent à #18.

### 19.3 Avis : transient, et non argument d'URL

Clé `mtb_portee_avis_<user_id>_<post_id>`, durée **60 s**, lue **puis supprimée** sur `admin_notices`,
éléments `array( 'niveau' => 'error'|'warning', 'texte' => string )`.

Motif décisif : **un argument d'URL ne peut pas transporter la valeur qu'elle a tapée.** Citer sa
saisie — « Vous aviez saisi : « … » » — est ce qui rend *vrai* « la saisie n'est jamais perdue », au
lieu de simplement l'affirmer.

**Ce n'est pas une infraction au contrat #1 §9**, dont l'interdit de transient vise **les fonctions de
lecture**. Un avis d'administration de 60 secondes n'en est pas une.

`notice-warning` et non `notice-error` quand rien n'a échoué ; **pas de préfixe « Erreur : »** sur un
enregistrement réussi (§18.3).

### 19.4 Tri en PHP — `orderby => meta_value` est interdit

Cette forme **exclut purement et simplement les contenus qui n'ont pas la méta** : une portée sans date
de naissance **disparaîtrait de l'index sans un mot**. C'est le contraire de **D12**.

> **La section 9.4 présentait cette exclusion comme un bénéfice. C'était vrai pour « la dernière
> portée » seulement, et faux pour tout le reste. Corrigé ici.**

Règle qui satisfait les deux besoins : `WP_Query` **simple**, sans clause méta ni `orderby` méta →
**tri en PHP**, décroissant sur la date, **non datées en fin** → `mtb_get_derniere_portee()` renvoie le
premier élément **qui possède une date**. Une portée sans date ne peut toujours pas devenir « la
dernière », **mais elle reste visible dans l'index**. À 27 portées, le coût est nul.

### 19.5 Enveloppe de champ — forme commune aux trois types

Toute valeur exposée par une fonction de lecture :

```php
array( 'libelle' => string, 'valeur' => string, 'affichage' => string )
```

- `libelle` — le libellé **public** de `MASTER.md` §10.2, **fourni par le serveur**.
- `valeur` — la donnée **brute**, jamais reformatée.
- `affichage` — ce que le thème imprime : la valeur, ou « **Non renseigné** » si vide.
- **Exception** : l'**élevage** d'un parent extérieur rend `''`, jamais « Non renseigné ».
- **Date** : `valeur` = `AAAA-MM-JJ` ; `affichage` = `wp_date()` sur un horodatage construit **à
  midi** — sinon un fuseau négatif décale le jour. *(Remplace le `!Y-m-d` de la section 9.4.)*
- **Liste fermée** : `valeur` = clé canonique, `affichage` = libellé français.
- **Images** : identifiants d'attachement **+ `alt`**, jamais d'URL ni de HTML. Le serveur décide
  l'`alt`, **avec repli sur le nom quand l'attachement n'en a pas** — ainsi aucune photo ne part sans
  alternative (**D7**).

`chiots_colonnes` est **conservé tel quel** : la décision 10 en dépend.

**Portée protégée** : `array( 'id', 'lien', 'protege' => true, 'etat' => 'page_protegee' )` —
**aucun champ du domaine, pas même vide**. Plus restrictif que la « charge réduite » de la section 10.

### 19.6 Forme gelée d'un élément de `mtb_get_portees_du_chien()` — #4 la consomme telle quelle

Noms de clés compris : **`lien`** et non `url`, **`role`** et non `role_du_chien`.

```php
array(
  'id'             => int,
  'identifiant'    => string,   // « A3 2025 » ; '' si absent
  'lien'           => string,   // '' si non consultable
  'date_naissance' => array( 'libelle' => 'Née le', 'valeur' => 'AAAA-MM-JJ'|'', 'affichage' => string ),
  'disponibilite'  => array( 'libelle' => 'Disponibilité',
                             'valeur'    => 'disponible'|'reserve'|'passee'|'',
                             'affichage' => 'Chiots disponibles'|'Tous réservés'|'Portée passée'|'Non renseigné' ),
  'role'           => array( 'valeur' => 'pere'|'mere'|'', 'affichage' => 'Père'|'Mère'|'' ),
)
```

Tri **décroissant** sur `date_naissance['valeur']`, non datées **en fin**. `array()` s'il n'y en a
aucune — **jamais `null`, jamais de notice**. **La garantie de forme et d'ordre appartient à cette
issue** : #4 ne normalise rien et **ne déclare aucune fonction concurrente** (section 9.1).

### 19.7 Dates acceptées en saisie

**ISO `AAAA-MM-JJ`** et **`JJ/MM/AAAA`**, ce dernier converti par **regex explicite** puis
`checkdate()` — un vieux navigateur sans champ date natif poste du texte libre, et une date française
non ambiguë ne change pas de sens en changeant de notation. Tout le reste est **refusé, avec citation
de la saisie dans l'avis** (ce que le transient de §19.3 rend possible). **`strtotime()` reste
proscrite.**

### 19.8 Deux gardes à ne pas confondre avec des règles métier

`use_block_editor_for_post_type` renvoie `false` **si et seulement si** `'mtb_portee' === $type`, et
`$utiliser` **inchangé** sinon. Trois chaînes accrochent ce filtre ; si l'une ne garde pas son type,
elle **éteint l'éditeur de blocs des Pages** et casse tout le catalogue de composants.

**Plafonds de 100 chiots et 200 photos** : ce sont des **gardes contre une soumission forgée, pas des
règles d'élevage**. Le code doit le dire, pour qu'aucune revue ne les prenne pour une limite métier.

**N° LOF et identifiant de portée ne sont jamais normalisés** : ni majuscules, ni espaces retirés, ni
regroupement de chiffres, aucune validation de format. **Elle recopie.**

### 19.9 L'éditeur du commentaire est bridé sur trois points — voulu, pas subi

Le retour à l'éditeur natif (§19.2) a fait réapparaître deux choses que l'écran-formulaire évitait.
Les trois brides ci-dessous sont **des décisions, pas des effets de bord**, et la fiche d'aide doit
les présenter comme telles.

| Bride | Filtre | Ce qu'elle protège |
|---|---|---|
| **`forecolor` et `backcolor` retirés** | `mce_buttons_2`, borné au seul `mtb_portee` | Les deux sélecteurs produisent une couleur **hors des quinze jetons**, en style en ligne, qu'aucune feuille de style ne rattrape. Même famille que la dette **T7**. Ne retirer que `forecolor` laisserait la couleur de fond passer au travers |
| **`media_buttons => false`** | `wp_editor_settings`, borné au seul `mtb_portee` | Le bouton « **Ajouter un média** » emploie un mot **interdit** par `MASTER.md` §10.4. Et une image insérée au fil du texte échapperait au système de design |
| **Intitulé « Commentaire de l'éleveuse »** | `edit_form_after_title` | Sans lui, Fabienne voit une **grande boîte de texte anonyme**. Un champ qu'elle ne sait pas nommer est un champ qu'elle ne remplit pas — contrainte 1 |

**Conséquence à formuler positivement dans le guide, jamais par la privation** : les photos ont leur
chemin, c'est le champ **Galerie photos** — *« les photos s'ajoutent dans Galerie photos, pas dans le
commentaire »*. Et le gras et l'italique restent disponibles : le dire, sinon elle s'interdit aussi ce
qui marche encore.

**Chaque module borne sur son propre écran.** `mce_buttons_2` et `wp_editor_settings` sont des filtres
**globaux** : un module qui oublierait sa borne éteindrait ces boutons sur les **Pages**, qui
appartiennent à #18. Vérification exigée dans les deux sens — absents sur une portée, **présents sur
une Page**.

### 19.10 La navigation entre portées ignore les portées non datées

`mtb_get_portee_voisine()` ne considère que les portées **ayant une date de naissance**.

« Portée précédente » / « Portée suivante » est une chaîne **chronologique** : une portée sans date n'y
a pas de place, et l'y faire entrer produirait un enchaînement que **rien n'explique au visiteur**,
entre deux fiches sans rapport de date.

**Elles restent dans `mtb_get_portees()` et dans l'index** — les masquer serait perdre du contenu
(contrainte 4). **C'est la navigation seule qui les ignore.**

### 19.11 Libellés des boutons de la galerie — arbitrés pour les deux écrans

```
Monter la photo 1     Descendre la photo 1     Retirer la photo 1
```

Ni la première forme de cette issue (**Monter** / **Descendre** / **Retirer cette photo**), ni celle
de #4 (« Déplacer la photo 1 avant / après »). La première est immédiate — dans une liste verticale,
« Monter » se comprend sans rien apprendre — mais **une série de boutons identiques ne dit pas au
lecteur d'écran *quoi* monter**. La seconde nomme la photo mais laisse la phrase en suspens :
*avant quoi ?* La forme retenue prend le **verbe spatial** de l'une et le **rang** de l'autre.

Le texte visible et le nom accessible sont **identiques** : rien à maintenir en double.

**Le rang est recalculé à chaque redessin de la liste, jamais renuméroté à la main** — `ecran.js`
reconstruit la liste depuis le tableau de photos après chaque mutation, le rang ne peut donc pas
dériver après un déplacement ou un retrait.
