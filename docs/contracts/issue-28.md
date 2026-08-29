# Contrat d'interface — Issue #28 — Colonnes et filtres des listes d'administration

**Lot 10 · epic 11 « Dette technique — chargeur et permaliens » · gelé le 2026-08-29.**

Cette issue **ne touche pas le thème**. Elle n'expose **aucune fonction de lecture nouvelle**, **aucun
bloc**, **aucun champ**, **aucun type de contenu**, **aucune capacité**, et ne rend **aucun octet** au
visiteur anonyme. Le contrat porte donc sur les **crochets d'administration posés**, les **libellés
gelés**, les **états spéciaux**, et surtout sur les **interdits** — qui sont ici la vraie substance,
puisque le piège central de l'issue (D12) se tient dans ce qu'on s'interdit de faire.

Empreinte d'écriture : `wp-content/plugins/mtb-core/includes/admin/**`, ce fichier, et une **édition
chirurgicale** dans `docs/guide/`. Rien d'autre.

---

## 1. Fonctions de lecture exposées par l'extension

**Aucune.** Ce lot n'ajoute aucune fonction au contrat thème↔extension. Les modules livrés sont
**consommateurs** de sources existantes, jamais producteurs.

Sources **lues vivantes**, jamais recopiées — c'est le patron déjà établi par
`includes/admin/coordonnees/bootstrap.php` :

| Source | Ce qui en est lu |
|---|---|
| `includes/content/portee/champs.php` | `disponibilites()` — les trois clés que la modification groupée doit valider |
| `includes/content/chien/choix.php` | `non_renseigne()`, `statuts()` (ordre gelé), `varietes()`, `libelle_statut()`, `libelle_statut_pluriel()` |
| `includes/query/portee/hydratation.php` | `date_en_toutes_lettres()`, et la règle de comparaison de `trier()` |
| `includes/query/resultat/interne.php` | l'ordre gelé des disciplines, des orphelines et des sans-discipline |
| `mtb_resultat_disciplines()` (globale) | les neuf disciplines, appelée sous `function_exists()` |

Ces fichiers sont **hors empreinte** : ils se lisent, ils ne s'écrivent pas.

**`includes/query/**` n'est pas réutilisable pour lister.** `Hydratation::contenus()` fige
`post_status => 'publish'` et `has_password => false` ; `mtb_get_chien()` rend `null` hors `publish`.
Une liste d'administration montre les brouillons, les planifiés, les protégés et la corbeille. Le
module lit donc `get_post_meta()` en direct — légitime : la frontière de `CLAUDE.md` interdit au
**thème** d'interroger la base, pas à l'extension.

---

## 2. Blocs enregistrés

**Aucun.**

---

## 3. Crochets posés — la liste close de ce lot

`$T` ∈ { `mtb_portee`, `mtb_chien`, `mtb_resultat` }.

| Crochet | Types visés | Ce que le module y fait |
|---|---|---|
| `manage_mtb_portee_posts_columns` | portée | insère `mtb_date_naissance`, `mtb_disponibilite` **entre `title` et `date`** |
| `manage_mtb_chien_posts_columns` | chien | insère `mtb_statut`, `mtb_variete` entre `title` et `date` |
| `manage_mtb_resultat_posts_columns` | résultat | insère `mtb_discipline`, `mtb_annee`, `mtb_chien` entre `title` et `date` |
| `manage_{$T}_posts_custom_column` | les trois | rend une cellule, échappée, **jamais vide** |
| `restrict_manage_posts` | les trois | imprime un `<select>` de filtre — `mtb_annee` / `mtb_statut` / `mtb_discipline` |
| `disable_months_dropdown` | les trois | rend **`true`** — la liste des mois est neutralisée |
| `pre_get_posts` | les trois, `edit.php` **seulement** | pose `post__in` (+ `orderby`) selon §6 |
| `bulk_edit_custom_box` | **portée seule** | imprime le `<select name="mtb_disponibilite">` |
| `bulk_edit_posts` | **portée seule** | écrit `_mtb_disponibilite`, **et cette clé seule** |
| `bulk_post_updated_messages` | les trois | **priorité 20** — **complète** la seule clé `untrashed` déjà posée par `includes/fields/**`, sans jamais écraser (§9, arbitrage I′) |

**`manage_edit-{$T}_sortable_columns` est enregistré pour les trois types, dans un seul et unique
but : retirer la marque « colonne triée par défaut » de la colonne **Date** native.** Aucune de nos
sept colonnes n'y est jamais ajoutée — c'est une **clause du contrat, pas un oubli** : les ajouter
réintroduirait la jointure `meta_key` que la tâche 7 proscrit. Voir les arbitrages A et L.

*Cette clause a été **regelée en cours de chaîne**. La version initiale interdisait ce filtre
purement et simplement ; l'implémentation a montré que l'interdiction, écrite pour empêcher une
jointure, empêchait aussi la réparation d'une **fausse annonce d'accessibilité que ce lot
introduit**. Détail et mesure à l'arbitrage L.*

**Paramètres d'URL réservés, noms gelés** : `mtb_annee` · `mtb_statut` · `mtb_discipline`, sur
`edit.php` uniquement. Aucun autre module ne doit les employer.

**Découverte** : un dossier sous `includes/admin/` portant un `bootstrap.php` suffit
(`includes/class-loader.php:119-165`). **Aucune ligne d'enregistrement n'est à ajouter hors
empreinte** — vérifié, pas supposé. Deux modules : `admin/listes/` (colonnes, ordre, filtres,
modification groupée) et `admin/corbeille/` (les messages groupés, seuls, désactivables d'un `_`
sans emporter les colonnes).

---

## 4. Colonnes — ce que chaque cellule rend

| Liste | Colonne | En-tête | Rendu | Absence |
|---|---|---|---|---|
| Portées | `mtb_date_naissance` | **Date de naissance** | `date_en_toutes_lettres()` | **Non renseigné** (y compris date illisible) |
| Portées | `mtb_disponibilite` | **Disponibilité** | `disponibilites()[ $cle ]` | **Non renseigné** — clé vide **ou inconnue**, jamais la clé technique |
| Chiens | `mtb_statut` | **Statut** | **`libelle_statut( $cle, $sexe )`** — accordé au sexe | **Non renseigné** |
| Chiens | `mtb_variete` | **Variété** | `varietes()[ $cle ]` | **Non renseigné** |
| Résultats | `mtb_discipline` | **Discipline** | `mtb_resultat_disciplines()[ $cle ]` ; clé non vide hors liste → **la clé brute** | **Non renseigné** (clé vide seulement) |
| Résultats | `mtb_annee` | **Année** | chiffres bruts, **aucun formatage** (« 2 021 » serait produit) | **Non renseigné** |
| Résultats | `mtb_chien` | **Chien** | quatre états, §7 | **Non renseigné** |

**L'accord ne se compose jamais ici.** `choix.php` désigne `accord()` comme point unique. Un sexe vide
donne la forme masculine canonique. **« Retraité(e) » est proscrit** — le fichier l'interdit nommément,
« se lit mal en synthèse vocale ».

**La colonne « Date » native est conservée** sur les trois listes (arbitrage F).

---

## 5. Filtres

| Liste | Paramètre | Première option | Options | Ordre |
|---|---|---|---|---|
| Portées | `mtb_annee` | **Toutes les années** | années **déduites des contenus existants**, jamais une plage codée en dur | décroissant |
| Chiens | `mtb_statut` | **Tous les statuts** | les quatre clés, libellé par `libelle_statut_pluriel()` | **ordre gelé de `choix.php`**, jamais alphabétique |
| Résultats | `mtb_discipline` | **Toutes les disciplines** | les neuf clés, puis les clés **orphelines réellement présentes** | **ordre gelé**, puis orphelines |

Le filtre s'applique **par liste d'identifiants**, jamais par `meta_query`. Une valeur de filtre
invalide est **ignorée**, jamais réparée : l'écran revient à « toutes ». C'est la seule des deux
conduites possibles qui **ne peut jamais cacher de contenu** — un `&mtb_annee=1900` tapé à la main
ramène donc toute la liste, il ne produit pas un écran vide.

**Conséquence à ne pas perdre, sur où la sentinelle `array( 0 )` s'exerce réellement.** Les années
proposées étant *déduites des contenus existants*, une année valide ramène toujours au moins une
portée : **le filtre des années ne peut pas produire d'écran vide**. Les filtres **statut** et
**discipline**, eux, proposent une **liste fermée** — « Reproducteurs » est une option valide même
quand aucun chien ne l'est. **C'est là, et là seulement, qu'un écran vide légitime se produit, et donc
là que la sentinelle doit être éprouvée.**

Les années et les orphelines viennent du **même balayage** que l'ordre, donc de la **même portée
d'écran** : on ne propose jamais un filtre qui ne mène nulle part.

**Chaque `<select>` porte un nom accessible** — arbitrage M. La liste des mois du cœur, que ce module
neutralise, était **étiquetée** (`<label for="filter-by-date" class="screen-reader-text">`) ; la
remplacer par un contrôle sans nom serait une **régression d'accessibilité introduite par ce lot**. On
reprend donc **exactement le balisage du cœur** : un `<label class="screen-reader-text">` lié par
`for`/`id`, portant « **Filtrer par année** », « **Filtrer par statut** », « **Filtrer par
discipline** ». `screen-reader-text` est une classe **du cœur** : la contrainte « zéro octet de CSS »
reste tenue.

**Mesuré, contre l'hypothèse de départ** : sur WP 6.9, `extra_tablenav()` **ne dépend pas** de
`has_items()` — le `tablenav` du haut est rendu inconditionnellement. **La barre de filtres survit à
un écran vide**, elle est toujours reposable. Aucune parade n'est nécessaire ; l'inquiétude portée au
brainstorm est levée par la mesure.

---

## 6. Ordre imposé — et le mécanisme qui ne peut rien escamoter

**Aucune colonne n'est triable au clic** (arbitrage A). À la place, un ordre par défaut imposé une
fois : calcul de la liste ordonnée des identifiants **en PHP**, puis `post__in` +
`orderby => 'post__in'`. C'est la doctrine déjà écrite dans `hydratation.php:78-86`.

| Liste | Ordre |
|---|---|
| **Portées** | date de naissance **décroissante**, **non datées en fin de liste**, égalités par identifiant — soit **exactement l'ordre du site public** |
| **Chiens** | alphabétique du nom d'usage — **natif** (`orderby => 'title'`), rien ne peut disparaître |
| **Résultats** | disciplines dans l'**ordre gelé**, puis orphelines, puis **les sans-discipline en tout dernier** ; dans un groupe, année décroissante, sans-année en fin |

**Le tri en PHP après la requête est interdit** : `WP_Query` applique le `LIMIT` en SQL, on ne
réordonnerait que la page courante.

**Portée d'écran** — c'est elle qui neutralise le piège de la corbeille. `$_GET['post_status']` validé
contre `get_post_stati()` → ce seul statut ; sinon
`get_post_stati( array( 'show_in_admin_all_list' => true ) )`.

### Les deux pièges silencieux, nommés

**Piège 1 — `post__in => array()` est ignoré.** `class-wp-query.php:2242` teste la valeur : un tableau
vide est *falsy*, la clause `AND ID IN (…)` n'est pas ajoutée, et **la liste affiche tout**. Un filtre
« 2019 » sans résultat montrerait les 27 portées — le contraire exact de la demande, sans un mot. **La
sentinelle est `array( 0 )`.**

**Piège 2 — `post_status => 'any'` exclut la corbeille** (`class-wp-query.php:2655-2660`). Une liste
d'identifiants calculée avec `'any'`, posée en `post__in` sur l'onglet Corbeille, **viderait l'onglet**
— la disparition silencieuse que l'issue combat. **`'any'` est proscrit dans ce module.**

### La règle de sûreté — clause centrale du contrat

> **`post__in` n'est jamais posé sur un écran non filtré dont la liste calculée est vide.**

Conséquence : sans filtre, un défaut du balayage ne peut **que** rater l'ordre — **jamais vider une
liste**. La sentinelle `array( 0 )` ne sert que dans le seul cas où un écran vide est la bonne
réponse : « elle a demandé 2019, il n'y a rien en 2019 ».

| Écran | Ordre imposé ? | Filtre appliqué ? |
|---|---|---|
| Onglet **Tous** | oui | oui |
| **Publiés / Brouillons / Planifiés / Privés** | oui | oui |
| **Corbeille** | **oui** — liste calculée sur `trash`, **l'onglet n'est jamais vidé** | oui |
| **Recherche** (`s=…`) | oui | oui |
| **Colonne native cliquée** (`orderby` dans l'URL) | **non** — sa demande explicite l'emporte | **oui**, le filtre reste actif |
| Site public, requête secondaire, autre écran | **non** | **non** |

---

## 7. États spéciaux

| État | Rendu |
|---|---|
| `donnee_absente` — champ vide **ou clé hors liste fermée** | **« Non renseigné »** — jamais un tiret, jamais « Aucun », jamais une cellule vide |
| `fiche_introuvable` — `_mtb_chien_id` ne résout plus vers un `mtb_chien` | **« Fiche introuvable »** (recopié de `fields/portee/ecran.php:209`) |
| `fiche_sans_titre` — fiche présente, titre vide | **« Fiche n° &lt;id&gt; »** (recopié de `fields/resultat/controle-chien.php:117`) |
| `chien_en_texte_libre` — `_mtb_chien_id === 0` | `_mtb_chien_nom` **tel qu'il est stocké**, sans reformatage. **C'est le cas de 60 résultats sur 61** |
| `discipline_orpheline` — clé hors liste mais non vide | **la clé brute**, jamais un blanc, jamais une omission |
| `filtre_sans_resultat` | liste vide (`not_found` du cœur), **barre de filtres présente et reposable** |
| `liste_vide_sans_filtre` | comportement natif inchangé — **`post__in` n'est pas posé** |
| `corbeille` | colonnes rendues, ordre appliqué, **onglet jamais vidé** |
| `pas_de_changement` (modification groupée) | **rien n'est écrit**, aucune clé n'est touchée |

La précédence de la colonne « Chien » est celle que l'écran de saisie énonce à l'éleveuse
(`controle-chien.php:67-68`) : « *Si une fiche est choisie, elle l'emporte. Le nom recopié ne sert que
si aucune fiche n'est choisie.* » Pas de lien vers la fiche : `get_edit_post_link()` rend `null` sans
la capacité, ce qui ferait **varier le rendu de la colonne selon le compte**.

---

## 8. Écriture — la modification groupée, et elle seule

**Un seul champ, sur la seule liste des portées : Disponibilité**, avec **« — Pas de changement — »**
en première option (valeur `-1`, la sentinelle du cœur), cochée d'avance.

Le crochet est **`bulk_edit_posts`** (WordPress ≥ 6.3 ; l'extension déclare `Requires at least: 6.5`),
et **pas** le vieux recours « `save_post` + reniflage de `$_REQUEST['bulk_edit']` ». Différence
décisive : il n'est atteint que par `wp-admin/edit.php` **après** `check_admin_referer( 'bulk-posts' )`,
et il court **après** toute la boucle `wp_update_post()`. Il ne peut **structurellement** pas se
déclencher sur une sauvegarde ordinaire, un enregistrement automatique, une révision, ou la
Modification rapide. **La garde la plus dangereuse du lot est structurelle, pas déclarative.**

Douze gardes, dans l'ordre : tableau non vide · type = `mtb_portee` · **nonce `bulk-posts`** (→ `return`,
jamais `wp_die()`) · `bulk_edit` présent · champ présent et scalaire · **sentinelle → rien n'est écrit**
· **valeur validée contre `disponibilites()` lue vivante** · identifiant > 0 · type du contenu ·
ni révision ni autosave · **`current_user_can( 'edit_post', $id )` sur chaque contenu** ·
`update_post_meta()` de **`_mtb_disponibilite` et de cette clé seule**, précédé de `wp_slash()`.

**Troisième filet, gratuit** : `_mtb_disponibilite` est enregistré avec
`sanitize_callback => assainir_disponibilite`, qui refuse toute valeur hors des trois. Même si la
garde de liste tombait, rien de faux ne se stockerait.

**Non-régression, à démontrer et non à croire** : la modification groupée déclenche `wp_update_post()`
→ `save_post_mtb_portee` → `fields/portee/sauvegarde.php::enregistrer_champs()`, qui **rend la main
immédiatement** faute de `$_POST['mtb_portee_ecran']` (lignes 41-43). **Cette garde est ce qui rend la
modification groupée sûre. Elle est hors empreinte, elle ne doit jamais être assouplie.**

**Pas d'option « Non renseigné » dans le champ groupé** : vider une disponibilité en masse est un geste
destructeur que l'issue ne demande pas ; il reste possible fiche par fiche.

---

## 9. Le bandeau de sortie de corbeille

Crochet `bulk_post_updated_messages( $bulk_messages, $bulk_counts )` (`wp-admin/edit.php:408`), **en
priorité 20**. On n'empile **jamais** un second `admin_notices`, qui donnerait deux bandeaux à l'écran
et deux annonces au lecteur d'écran.

**On complète, on n'écrase jamais** (arbitrage I′). Les trois types renseignent **déjà** leurs cinq
messages groupés depuis `includes/fields/**` ; ce module s'enregistre **après** eux et **ajoute** son
avertissement à la **seule** clé `untrashed`, en préservant la phrase existante en tête. Les quatre
autres clés ne sont pas touchées.

La formulation ne s'invente pas : elle est **recopiée** de `docs/guide/chien-ajouter-un-chien.md:220`,
« *Une fiche rétablie revient en brouillon, c'est-à-dire hors du site* », et accordée aux trois types.

Deux pièges mesurés : `edit.php:443` appelle `sprintf()` avec **un seul argument** — donc **exactement
un `%s`** par chaîne (un second lèverait une `ArgumentCountError` à PHP 8, soit un **écran blanc au
retour d'un rétablissement**) et **tout `%` littéral se double**. L'accord pluriel se fait à la main
d'après `$bulk_counts['untrashed']`, aucune fonction de traduction n'étant employée dans l'extension.

---

## 10. Interdits

- **Jamais `orderby => 'meta_value'`**, ni aucune clause qui suppose la présence d'un champ.
- **Jamais `post_status => 'any'`** — il exclut la corbeille.
- **Jamais `post__in => array()`** — il est ignoré et affiche tout. La sentinelle est `array( 0 )`.
- **Jamais `post__in` sur un écran non filtré dont la liste calculée est vide.**
- **Jamais d'écriture sur `save_post`**, ni sur un enregistrement automatique, ni sur une révision.
- **Jamais autre chose que `_mtb_disponibilite`**, et jamais sur un autre type que `mtb_portee`.
- **Jamais de recopie d'une liste fermée** ni d'un libellé de valeur : ils sont lus vivants.
- **Jamais d'accord composé** — `libelle_statut()` fait foi.
- **Jamais de formatage de nombre** : une année s'imprime en chiffres bruts.
- **Jamais un fichier hors `includes/admin/**`** ; aucune colonne native retirée ; ni menu, ni rôle, ni
  capacité touchés.
- **Zéro octet de CSS, zéro octet de JavaScript**, aucun `wp_enqueue_*`, aucun `style=`. Les classes
  employées pour le champ groupé sont **celles du cœur** (`inline-edit-col-right`, `inline-edit-col`,
  `inline-edit-group`, `title`), reprises du champ « Format » natif.
- **Jamais d'exécution hors administration** : première garde de chaque rappel.
- **Aucune requête `$wpdb` brute.** Tout passe par `WP_Query`, `get_post_meta`, `get_post`,
  `get_post_type`, `get_post_stati`, `update_post_meta`.

---

## 11. Libellés gelés par cette issue

| Contexte | Chaîne exacte |
|---|---|
| En-têtes de colonnes | **Date de naissance** · **Disponibilité** · **Statut** · **Variété** · **Discipline** · **Année** · **Chien** |
| Première option des filtres | **Toutes les années** · **Tous les statuts** · **Toutes les disciplines** |
| Nom accessible de chaque filtre | **Filtrer par année** · **Filtrer par statut** · **Filtrer par discipline** |
| Sentinelle de la modification groupée | **— Pas de changement —** |
| Donnée absente, partout | **Non renseigné** |
| Fiche de chien perdue | **Fiche introuvable** |
| Fiche de chien sans titre | **Fiche n° &lt;id&gt;** |

Les **libellés de valeurs** (disponibilités, statuts accordés, variétés, disciplines) **ne sont pas
gelés ici** : ils sont lus vivants. Ce module n'en recopie aucun.

---

## 12. Arbitrages

Onze décisions, prises par le lead de la chaîne. Chacune porte sa raison, parce qu'une décision sans
motif se re-litige au lot suivant.

**A — Aucune colonne n'est triable au clic.** Le tri n'est demandé **nulle part** dans l'issue ; il
n'apparaît que dans une garde (tâche 7). Trois raisons de ne pas le livrer : *(1)* personne ne l'a
demandé ; *(2)* une colonne triable en `ASC` rangerait les portées non datées **en tête**, ce qui
contredit une phrase **déjà écrite** dans `docs/guide/portee-la-liste-des-portees.md` (« une portée
sans date de naissance se range en fin de liste ») ; *(3)* une colonne triable est une **promesse
d'exactitude**, et sur ce projet un tri subtilement faux sur une généalogie est plus grave que
l'absence de tri. **La tâche 7 est ainsi satisfaite à la racine** : on ne fait jamais construire de
jointure `meta_key` à `WP_Query`, plutôt que de s'en garder après coup.

**B — À la place, un ordre par défaut qui a du sens.** Fait mesuré qui commande la décision :
l'importeur **n'écrit jamais `post_date`**, donc les 105 contenus portent l'horodatage de l'import à la
seconde près, et **l'ordre par défaut des trois listes est l'ordre d'import inversé** — un ordre
arbitraire. Le vrai défaut de ces listes n'était pas l'absence de tri, c'était que leur ordre n'avait
aucun rapport avec l'élevage. Un ordre imposé une fois est **plus sûr, plus court à documenter et
moins coûteux** qu'un tri cliquable : il ne peut pas être mis dans un état surprenant, il ne pose
aucune question d'`aria-sort`, il ne peut pas contredire le guide.

**C — Modification groupée, pas Modification rapide.** L'issue promet « les changer sans ouvrir chaque
fiche une par une » ; une colonne en lecture seule ne tient pas cette phrase, et il fallait trancher.
La **groupée** est retenue parce que le geste réellement urgent est **massif** — les 27 portées
importées ont **toutes** une disponibilité vide (Q-20-1) — et parce qu'elle ne préremplit rien **par
construction**, donc n'exige **aucune ligne de JavaScript**. La **rapide** est refusée pour ce lot :
WordPress ne préremplit pas un champ personnalisé en Modification rapide, il faudrait un script
d'administration pour recopier la valeur courante, et **sans lui le panneau s'ouvre sur la première
option** — un « Mettre à jour » distrait écrirait *Chiots disponibles* sur une portée de 1995. **Un
fait d'élevage faux, écrit en silence, par l'outil censé le protéger.** Reportée à une issue dédiée,
avec son JS, son a11y vérifiée au lecteur d'écran et sa fiche.

**D — Filtres par liste d'identifiants**, comme l'ordre, jamais par `meta_query`. Un seul mécanisme
pour les deux, donc une seule chose à vérifier.

**E — La liste déroulante des mois est neutralisée sur les trois listes.** Elle filtre sur la date de
publication, qui vaut l'horodatage de l'import sur 105 contenus ; sur la liste des portées elle
**collisionne** avec le filtre « Année » (deux listes voisines qui semblent toutes deux filtrer par
année, l'une sur la naissance, l'autre sur la publication) ; et c'est un filtre qu'elle peut
déclencher et qui **cache du contenu sur un critère qu'elle ne comprend pas** — soit exactement D12.

**F — La colonne « Date » native est conservée.** Elle est **vraie** (c'est bien la date de
publication) et redeviendra informative pour les contenus qu'elle créera elle-même. L'issue demande
d'ajouter, pas de retirer ; un retrait visible que personne n'a demandé sort du périmètre. *Le
brainstorm recommandait de la retirer ; arbitrage contraire, assumé.*

**G — Les trois colonnes des résultats sont livrées**, malgré le recouvrement avec le titre composé
(`discipline — chien — niveau — année`). Le brainstorm proposait de les abandonner comme redondantes.
**Un fait vérifié renverse l'objection** : le titre **omet les parties absentes en silence**
(`titre.php:43-49`). Un résultat sans discipline porte un titre qui commence par le nom du chien, et
**rien n'annonce le manque**. La colonne l'écrit : « Non renseigné ». **Une colonne nomme l'absence
que le titre cache** — c'est D12, et c'est la raison de les livrer.

**H — En-tête de la colonne « chien » : « Chien ».** `MASTER.md` §10.2 atteste la notion sous ce nom ;
l'écran de saisie dit « Chien concerné », mais un en-tête de colonne se lit court et MASTER fait foi.

**I — RETIRÉ. La mesure qui le fondait était fausse.** *Conservé ici, barré, parce qu'un arbitrage
effacé se reprend au lot suivant.*

L'arbitrage I étendait la tâche 10 aux cinq clés de messages groupés des trois types, au motif —
**mesuré par la planification, et cru par moi** — que les quatre autres clés retombaient sur celles de
« post » et affichaient donc « **1 article déplacé dans la corbeille.** », un mot interdit par MASTER
§10.2.

**Vérification faite à la main après la passe de refacto qui l'a signalé : c'est faux.** Les trois
types renseignent **déjà** les cinq clés, en français métier, sans le mot « article » —
`includes/fields/portee/bootstrap.php:33`, `includes/fields/chien/bootstrap.php:42`,
`includes/fields/resultat/bootstrap.php:25`, tous trois vers un `messages_par_lot()` qui pose
« Portée mise à jour. », « Portée déplacée dans la corbeille. », « Portée sortie de la corbeille. »,
et leurs équivalents accordés pour les fiches de chien et les résultats de travail.

**Ce que l'arbitrage I aurait causé** : `$bulk_messages[ $type ] = $messages;` **écrase** au lieu de
compléter, et le chargeur parcourt `fields` **avant** `admin`. Quinze phrases justes seraient devenues
du code mort en silence, quatre bandeaux auraient changé de libellé sur trois écrans, et **deux fiches
d'aide déjà écrites seraient devenues fausses** (`docs/guide/resultat-ajouter-un-resultat.md:132`,
`docs/guide/portee-ajouter-une-portee.md:102`), qui citent le bandeau mot pour mot.

**La leçon, et c'est la même qu'au lot 9 : un donneur d'ordre qui gèle une mesure sans la refaire
fabrique le défaut suivant.** J'ai gelé « les autres clés retombent sur post » sur la foi d'une ligne
de plan, sans ouvrir `includes/fields/**`. La passe de refacto a recompté et m'a contredit ; elle a eu
raison, et elle a eu raison de **ne pas corriger d'elle-même** un arbitrage gelé.

**I′ — À la place : on complète, on n'écrase jamais.** `admin/corbeille` s'enregistre en **priorité
20**, donc **après** les rappels de `includes/fields/**`, et **ajoute** l'avertissement à la **seule**
clé `untrashed` des trois types, en préservant la phrase existante en tête. Les quatre autres clés ne
sont **pas touchées**. C'est exactement la tâche 10, et rien de plus.

Le bandeau se lira donc : « **Portée sortie de la corbeille.** Elle revient en brouillon, c'est-à-dire
hors du site : ouvrez-la et cliquez sur « Publier » pour qu'elle réapparaisse. » — la première phrase
étant celle que les fiches d'aide citent déjà, **qui reste donc vraie**.

*Mesuré à la livraison, contre ce que ce contrat affirmait d'abord* : les phrases de `fields/**` ne
portent **aucun `%s`**, ni au singulier ni au pluriel — `accorder()` et `phrase()` concatènent le
nombre en clair. L'invariant « au plus un `%s` » tient donc a fortiori. Non-régression **mesurée** :
15 clés × 2 comptes = **30 comparaisons** avec et sans le rappel, **24 inchangées** (`updated`,
`locked`, `deleted`, `trashed` sur les trois types) et **6 complétées** avec la phrase d'origine
conservée en tête.

Contraintes de composition : la phrase existante est **reprise telle quelle**, jamais recomposée ni
recopiée en dur ; le total doit conserver **au plus un `%s`** ; tout `%` littéral se double ; et si
la clé `untrashed` est absente ou n'est pas une chaîne, **on rend le tableau inchangé** plutôt que de
fabriquer une phrase.

**J — Les options du filtre « Statut » sont au pluriel** (`libelle_statut_pluriel()`) :
« Reproducteurs », « Retraités », « Disparus », « En cours de confirmation ». Un filtre nomme un
**groupe**, et c'est exactement le couple déjà en place côté public — titre de groupe au pluriel, fiche
au singulier accordé. Conséquence assumée : elle choisit « Retraités » et lit « Retraitée » dans la
colonne. Ce n'est pas une invention : les deux formes sont **déjà gelées** dans `choix.php`.

**K — Pas d'option « Non renseigné » dans le filtre des disciplines.** Elle donnerait un chemin direct
vers les résultats incomplets, ce qui sert D12 — l'idée est bonne. Elle est refusée **ici** pour une
raison de cohérence, non d'utilité : une option « ce qui reste à compléter » appartient aux **trois**
filtres ou à aucun, et l'ajouter aux trois est une fonctionnalité que personne n'a demandée. L'ordre
imposé rassemble déjà les sans-discipline en **bloc unique en fin de liste**, ce qui couvre l'essentiel
du besoin. **Consigné comme dette** : « filtrer sur ce qui reste à compléter », transverse aux trois
listes.

**L — La colonne « Date » native ne se déclare plus triée par défaut.** *Arbitrage pris en cours de
chaîne, après mesure, et qui amende la clause A.*

Mesuré dans le cœur de la pile (WP 6.9), pas supposé. `class-wp-posts-list-table.php:783` déclare pour
tout type de contenu autre que « page » :

```php
'date' => array( 'date', true, __( 'Date' ), __( 'Table ordered by Date.' ), 'desc' ),
```

Le **cinquième** élément est `$initial_order`. `class-wp-list-table.php:1464` s'en sert : en l'absence
de `$_GET['orderby']`, il pose `$current_orderby = 'date'` et `$current_order = 'desc'`, ce qui donne
à l'en-tête **Date** la classe `sorted desc`, le texte masqué « Table ordered by Date. » et surtout
**`aria-sort="descending"`**.

C'était **exact avant ce lot** — la liste était bien rangée par date de publication décroissante. **Ce
ne l'est plus** : l'ordre réel est désormais la date de naissance, le nom d'usage ou l'ordre gelé des
disciplines. WordPress **annonce donc à un lecteur d'écran un ordre que la table n'a pas**, sur les
trois listes, et **c'est ce lot qui rend l'affirmation fausse**.

L'accessibilité AA est bloquante sur ce projet, et c'est ici la même famille de défaut que celle qui a
bloqué le lot 9 : **le dépôt affirme quelque chose que le code dément** — en ARIA cette fois, donc
invisible à l'œil et audible seulement en synthèse vocale.

**La réparation** : enregistrer `manage_edit-{$T}_sortable_columns` pour les trois types et y **retirer
le seul cinquième élément** de l'entrée `date`. La colonne Date **reste triable au clic** (le contrat
§6 prévoit déjà que sa demande explicite l'emporte) ; elle cesse simplement de se **déclarer** triée
quand elle ne l'est pas. Les valeurs du cœur sont **préservées telles quelles**, y compris ses chaînes
traduites : on retire un index, on n'en recompose aucune.

**Pourquoi cela n'entame pas l'arbitrage A** : l'interdiction du filtre avait été écrite pour empêcher
la **jointure `meta_key`** de la tâche 7. Retirer un marqueur sur une colonne native qui trie sur
`post_date` — une vraie colonne de table — n'introduit **aucune jointure de champ**. L'interdiction
était **trop large d'un cran**, et une interdiction trop large fabrique le défaut suivant. Ce que A
proscrit reste entier et se relit ainsi : **aucune de nos sept colonnes n'est jamais rendue triable.**

**M — Chaque `<select>` de filtre porte un nom accessible.** *Arbitrage pris en cours de chaîne, après
que la passe de refacto eut relevé l'omission.*

Le module neutralise la liste déroulante des mois du cœur (arbitrage E) et pose la sienne à la place.
Or celle du cœur est **étiquetée** — `<label for="filter-by-date" class="screen-reader-text">` — et la
nôtre ne l'était pas. Un lecteur d'écran annonçait donc désormais **une liste déroulante sans nom** là
où il en annonçait une nommée : **une régression d'accessibilité créée par ce lot**, exactement comme
l'arbitrage L, mais dans l'autre sens — un nom accessible **retiré** au lieu d'une annonce **de trop**.

**Les trois libellés ne sont pas une invention** : ils suivent le patron du cœur lui-même (« Filtrer
par date ») appliqué aux noms déjà gelés au §11 (« Année », « Statut », « Discipline »), et le bouton
voisin, « Filtrer », est celui du cœur. Composition d'un patron attesté, pas un libellé neuf.

Balisage **identique à celui qu'il remplace** : `<label class="screen-reader-text">` lié par
`for`/`id`. `screen-reader-text` est une classe **du cœur** — aucun octet de CSS n'est en jeu, la
contrainte tient.

**Ce que ces deux arbitrages tardifs disent du lot** : L et M ont tous deux été trouvés **après**
l'implémentation, tous deux portent sur l'accessibilité, et tous deux sont des régressions que **ce
lot introduisait**. Aucun n'était visible à l'œil : l'un ne s'entend qu'en synthèse vocale, l'autre non
plus. Un module qui **remplace** un contrôle du cœur hérite de ses obligations d'accessibilité, et
c'est le genre de dette qu'aucun vérificateur automatique ne signale — axe voit un `<select>` sans nom
seulement s'il l'atteint, et la fausse annonce de tri, il ne peut pas la savoir fausse.

**Questions bloquantes : aucune.** Cette issue n'écrit **aucun fait d'élevage** — sauf la
disponibilité, qui est une liste fermée de trois valeurs déjà gelées et déjà attestées par MASTER.

---

## 13. Dettes relevées, non traitées

| Réf. | Objet | Pourquoi pas ici |
|---|---|---|
| **Nouvelle** | **Réparation à la cause du retour en brouillon** : `wp_untrash_post_status` + `wp_untrash_post_set_previous_status()` (WP 5.6+, vérifié) rendraient à un contenu rétabli **le statut qu'il avait avant la corbeille**, en une ligne — les cinq étapes en deux endroits deviendraient un clic | Rendrait **fausses trois fiches d'aide** que l'empreinte ne permet pas de réécrire, et remettrait du contenu **en ligne sans confirmation** : demande l'accord de l'éleveuse, pas une décision d'agent |
| **Nouvelle** | **Modification rapide de la disponibilité**, ligne par ligne — la lecture forte de la promesse de l'issue | Arbitrage C. Exige un JS d'administration et son a11y vérifiée au lecteur d'écran : son propre lot |
| **Nouvelle** | **« Filtrer sur ce qui reste à compléter »**, transverse aux trois listes | Arbitrage K |
| **Nouvelle** | **Deux formulations concurrentes** pour une fiche de chien perdue : « Fiche introuvable » (`fields/portee/ecran.php:209`) et « Fiche n° 123 (introuvable) » (`fields/resultat/controle-chien.php:112-114`). La colonne « Chien » dira « Fiche introuvable » alors que **l'écran où ce champ se saisit** dit autre chose | `includes/fields/**` hors empreinte |
| **M4** (`docs/contracts/issue-13.md:800`) | MASTER §10 n'atteste aucun libellé d'option « tout ». Les trois libellés arbitrés ici **sont** le « libellé de lot » que la dette réclamait — mais **M4 reste ouverte** tant que MASTER §10 n'est pas amendé | `design-system/MASTER.md` hors empreinte — amendement à `lead-design-mtb` |
| **D3** (MASTER §15) | MASTER §10.2 énumère **8** disciplines, `mtb_resultat_disciplines()` en compte **9** (« Autres disciplines », employée par les 61 résultats importés) | MASTER hors empreinte. Le module **lit la liste vivante à neuf entrées** et ne recopie rien |
| **Q-20-1** | Les 27 disponibilités importées sont vides | Question ouverte à l'éleveuse. **La modification groupée livrée ici est précisément l'outil qui permettra d'y répondre en un geste** |
| **T49** | `leaddev-back-mtb` toujours non enregistré dans la session courante — la planification back de cette issue est passée par un agent générique adossé au fichier de prompt, comme au lot 9 | Hors code. La correction du frontmatter ne prend qu'à une session ultérieure |

**Relevé factuel, à réconcilier par `/lead-mtb`** : la consigne reçue pour cette chaîne décrivait T35
comme « renommer le champ Texte alternatif » et T36 comme « avertir quand une portée a une galerie
sans photo principale ». `docs/ETAT.md:444-445` dit autre chose : **T36** est le repli d'`alt` perdu
sur les galeries, **T35** est l'absence de rembourrage bas de `<main>`. Ce sont bien les intitulés des
**issues** #35 et #36 du board qui correspondent à la consigne — la numérotation des dettes `Txx`
d'`ETAT.md` et celle des issues GitHub **divergent**. Dans les deux lectures, **rien de tout cela ne
vit dans `includes/admin/**`** : rien n'a été touché.
