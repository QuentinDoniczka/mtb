# Contrat d'interface — Issue #5 — Type de contenu Résultat de travail

**Gelé le 2026-08-16.** Issue **extension seule** : aucun fichier de thème n'est touché, aucun bloc
n'est enregistré. Ce contrat n'a donc pas réconcilié deux plans parallèles — il réconcilie ce que
l'extension **livre** avec ce que deux issues futures **consommeront** :

- **#15** — composant « Tableau de résultats » de la page Travail ;
- **#4** — le palmarès de la fiche chien.

Il est subordonné à **`docs/contracts/issue-1.md`**, qui fait foi sur tout ce qu'il couvre déjà
(un module = un dossier, clés de méta `_mtb_`, français littéral sans i18n, `array()`, PHP 8.1,
frontière thème/extension, états spéciaux figés).

---

## 1. Ce que cette issue livre, et ce qu'elle ne livre pas

| Livré | Non livré, et à qui ça appartient |
|---|---|
| Le type de contenu `mtb_resultat` et ses huit champs | Le composant « Tableau de résultats » → **#15** |
| L'écran de saisie en français, avec nonce et assainissement | Les colonnes et le filtre de la liste d'administration → issue `admin` (dette **T-#5-a**) |
| Quatre fonctions de lecture dans l'espace de noms global | La reprise des ~30 lignes du site source → issue `contenu` |
| La fiche d'aide `docs/guide/resultat-ajouter-un-resultat.md` (D3) | `wp mtb import-fixtures` → issue `migration`/`infra` |
| | Toute règle visuelle, y compris d'administration |

**Aucun bloc n'est enregistré par cette issue.** `includes/blocks/**` est hors empreinte.

## 2. Le type de contenu

`mtb_resultat` — libellé **« Résultat de travail »** / **« Résultats de travail »** (`MASTER.md` §10.2).

| Réglage | Valeur | Raison |
|---|---|---|
| `public` | `false` | Un résultat n'a **aucune page publique**. `MASTER.md` §7.5 et §7.6 ne l'affichent que dans un tableau de la page Travail et dans le palmarès d'une fiche chien. Supprime d'un coup l'URL à slug accentué, le gabarit `single`, la question SEO et le risque de sitemap. |
| `publicly_queryable`, `has_archive`, `rewrite` | `false` | Corollaires. Aucune règle de réécriture ajoutée — cf. dette T6. |
| `exclude_from_search` | `true` | Corollaire. La dette **T8** n'est pas aggravée par cette issue. |
| `show_ui` | `true` | Elle doit le voir et le remplir. |
| `show_in_rest` | `false` | **Décision porteuse** : fait retomber WordPress sur l'écran d'édition **classique**. La boîte de saisie est visible d'emblée — pas de chargement de Gutenberg, pas de panneau latéral à dérouler, pas de double clic de publication. C'est ce qui achète les trente secondes. Coût assumé : invisible en REST ; aucun consommateur n'en a besoin. |
| `supports` | **`false`** — surtout pas `array()` | Pas de boîte de titre : le titre est composé au serveur (§4). Un champ de titre qu'elle remplit et que le serveur écrase à l'enregistrement est un mensonge fait à l'éditrice. **Correction du 2026-08-16, constatée en Docker :** ma première rédaction disait `array()` vide, et elle produisait **l'inverse exact** de l'intention. `WP_Post_Type::set_props()` (`wp-includes/class-wp-post-type.php`) teste `} elseif ( false !== $this->supports ) {` puis ajoute d'office **`title`, `editor`, `autosave`**. Un tableau vide est lu comme « rien de demandé », pas comme « rien du tout ». Seul `false` supprime réellement toute prise en charge. Corollaire vérifié : `wp_insert_post_empty_content` ne bloque pas, un résultat entièrement vide se publie. |
| `capability_type` | `'post'`, `map_meta_cap => true` | Le rôle **Éditeur natif** de Fabienne possède déjà `edit_posts`, `edit_others_posts`, `publish_posts`, `delete_posts`… Elle peut créer, modifier et supprimer un résultat **le jour de la livraison, sans qu'aucune ligne ne touche à un rôle**. `add_cap()` est interdit hors issue dédiée (contrat #1 §10). |
| `show_in_menu` | menu de **premier niveau**, `menu_position` **25** | Jamais `'edit.php?post_type=mtb_chien'` : le menu disparaîtrait entièrement tant que `mtb_chien` n'est pas enregistré — l'état exact du dépôt pendant que la chaîne #4 tourne. |
| `menu_icon` | **aucun** | L'extension n'émet aucune décision visuelle. Les trois menus du lot portent donc l'icône par défaut du cœur. Relève d'une issue `admin`. |

**Une boîte du cœur est retirée depuis `fields/resultat/`** : `remove_meta_box( 'slugdiv', 'mtb_resultat', 'normal' )`. WordPress ajoute d'office à tout écran classique une boîte intitulée **« Slug »**, et la propose dans « Options de l'écran » — mot **proscrit** par `MASTER.md` §10.4, et sans le moindre objet pour un type de contenu qui n'a aucune adresse publique.

## 3. Les huit champs

Clés `_mtb_<champ>` à **tiret bas initial obligatoire** (contrat #1 §6) : la méta est *protégée*, donc
jamais listée dans le panneau « Champs personnalisés » — garantie mécanique qu'aucune clé technique
n'atteint l'écran de l'éleveuse. `auth_callback` explicite sur chacune.

| Clé de méta | Type | Assainissement | Étiquette à l'écran (`MASTER.md` §10.2) |
|---|---|---|---|
| `_mtb_discipline` | `string` | `sanitize_key` — **jamais une liste blanche** (§6, état 3) | **Discipline** |
| `_mtb_chien_id` | `integer` | `absint` | **Chien concerné** |
| `_mtb_chien_nom` | `string` | `assainir_texte_recopie` — **jamais `sanitize_text_field`** | **Nom du chien (si le chien n'a pas de fiche)** |
| `_mtb_sexe` | `string` | `sanitize_key` | **Sexe** — liste fermée *Mâle* · *Femelle* |
| `_mtb_annee` | `integer` | `absint` | **Année** |
| `_mtb_niveau` | `string` | `assainir_texte_recopie` — **jamais `sanitize_text_field`** | **Niveau ou titre obtenu** |
| `_mtb_conducteur` | `string` | `assainir_texte_recopie` — **jamais `sanitize_text_field`** | **Conducteur** |
| `_mtb_pays` | `string` | `assainir_texte_recopie` — **jamais `sanitize_text_field`** | **Pays** |

Toutes en `single => true`, `show_in_rest => false`.

**Deux filets d'assainissement, délibérément** : à la frontière d'entrée dans `sauvegarde.php` (le
chemin utilisateur), **et** en `sanitize_callback` de `register_post_meta` (qui couvre tout
`update_post_meta()`, y compris un futur import WP-CLI qui ne passerait pas par le formulaire).
Les deux appellent **la même fonction**, `MTB\Core\Content\Resultat\assainir_texte_recopie()` — deux
filets, une seule définition de « valeur propre ».

### `sanitize_text_field()` est interdite sur une valeur recopiée

Elle passe par `wp_strip_all_tags()`, donc `strip_tags()`. **Sur une valeur commençant par `<`, PHP
supprime tout jusqu'à un `>` qui n'existe pas : la valeur est vidée, sans erreur et sans
avertissement.** Le champ exposé est **`_mtb_niveau`** — « Niveau ou titre obtenu » est du texte
recopié d'un palmarès officiel, où `<60` est plausible — et `_mtb_chien_nom`, qu'on ne réécrit jamais
non plus. Une donnée d'élevage réelle effacée en silence, c'est **D11 enfreinte par l'outillage**.

`assainir_texte_recopie( $valeur ): string` fait exactement ceci, et rien d'autre :

1. valeur non scalaire → `''` ;
2. `wp_check_invalid_utf8()` ;
3. `preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', … )` — les seuls caractères de contrôle ;
4. `preg_replace( '/[\r\n\t]+/', ' ', … )` — aplatissement des monolignes ;
5. `trim()`.

**Jamais `strip_tags`, jamais `wp_kses`, jamais `esc_*`.** C'est sûr **parce que** l'échappement est
systématique **en sortie** et que seul un compte disposant de `edit_post` écrit.

`sanitize_key` reste juste sur `_mtb_discipline` et `_mtb_sexe`, `absint` sur `_mtb_annee` et
`_mtb_chien_id` : ce sont des **clés canoniques et des entiers**, pas des valeurs recopiées. Et le
`sanitize_text_field()` appliqué au **nonce** reste : un nonce est une clé.

**Échappement des antislashs** — l'asymétrie qui piège : **les deux API veulent des données encore
échappées.** `update_metadata()` appelle lui-même `wp_unslash()` puis `sanitize_meta()`. Une valeur
venue de `$_POST` est déjà échappée ; une chaîne composée par nous (le titre) ne l'est pas et exige
`wp_slash()`. Vérifié à l'exécution : une valeur à antislash et apostrophe enregistrée trois fois de
suite rend le même octet.

**Aucune valeur par défaut sur l'année.** Pré-remplir l'année en cours économise trois secondes et
publie une date fausse le jour d'une saisie rétroactive — D11 tranche, champ vide. Même raison :
**aucun bornage** de l'année côté serveur.

**`_mtb_niveau`, `_mtb_pays`, `_mtb_conducteur` sont recopiés, jamais interprétés ni normalisés**
(décision 12, D11). `Finland` se stocke et s'affiche `Finland` ; il ne se traduit pas en `Finlande`.

### Le couple « chien concerné »

Deux champs **toujours visibles tous les deux**, jamais l'un masqué par l'autre, **sans une ligne de
JavaScript** — `MASTER.md` §7.3 : on ne cache jamais un contrôle en attendant une interaction, et un
contrôle qui dépend du JS casse au clavier.

Règle, écrite sous les champs : **si une fiche est choisie, elle l'emporte ; le nom recopié ne sert
que si aucune fiche ne l'est.**

Ce n'est **pas une soupape rare** : relevé sur `https://www.mtbrabant.com/travail/`, la majorité des
chiens cités n'appartiennent pas à l'élevage (« de la Légion des Loups », « des Terres d'Alfheim »,
« Pegaz Eenhoorn », « du Domaine de Drenthe »). Le chemin sans fiche est **le cas courant**.

### Pourquoi le champ Sexe existe

Le site source imprime **♂ ou ♀ devant chaque nom de chien**. Pour un chien lié à une fiche, le sexe
vient de la fiche (#4) et **la fiche fait autorité**. Pour un chien sans fiche — le cas majoritaire —
l'information serait **perdue** sans ce champ : c'est la contrainte 4, pas un ajout au brief. Libellé
et valeurs repris tels quels de `MASTER.md` §10.2 ; stem `sexe`, commun aux trois types du lot.

Le serveur fournit **« Mâle » / « Femelle »**, jamais un pictogramme : un symbole sans équivalent
textuel échoue à l'accessibilité, et le composer serait une décision de rendu.

## 4. Le titre, composé au serveur

**Le titre n'est jamais saisi.** Il est recomposé à chaque enregistrement à partir des seules valeurs
tapées : `discipline — chien — niveau — année`.

- Les parties absentes sont **omises, jamais comblées**.
- Tout vide → **« Résultat de travail (à compléter) »**. C'est une consigne, pas un fait d'élevage —
  et elle rend la ligne incomplète repérable dans la liste d'administration.
- Recomposé à chaque enregistrement : corriger l'année corrige le titre. Contrainte 3 tenue.
- Le titre est un **objet d'administration**. Il n'apparaît sur aucune page publique et **aucun
  consommateur ne le lit** : ni #15, ni #4 ne doivent en dépendre.

## 5. Les fonctions de lecture

Espace de noms **global**, préfixe `mtb_`, sous `if ( ! function_exists( … ) )`, dans
`includes/query/resultat/` (contrat #1 §6). Elles n'impriment rien et ne renvoient aucun HTML.

| Signature | Retour normal | Retour quand il n'y a rien |
|---|---|---|
| `mtb_resultat_disciplines(): array` | `clé => libellé`, ordre gelé (§5.1) | jamais vide |
| `mtb_resultat_sexes(): array` | `array( 'male' => 'Mâle', 'femelle' => 'Femelle' )` | jamais vide |
| `mtb_get_resultats_travail_par_discipline( array $args = array() ): array` | liste ordonnée de **groupes** (§5.3) | **`array()`** |
| `mtb_get_resultats_travail_du_chien( int $chien_id, array $args = array() ): array` | `array( 'colonnes' => …, 'lignes' => … )` | `array( 'colonnes' => array(), 'lignes' => array() )` |

`$args` — deux clés, **et aucune autre**, aujourd'hui ni demain sans révision de ce contrat :
`ordre` (`'annee_desc'` \| `'annee_asc'`) et `disciplines` (liste de clés ; vide = toutes).

> ### Frontière avec la chaîne #4 — le point le plus important de ce contrat
>
> **Tout ce qui lit des résultats appartient à `includes/query/resultat/`. La fiche chien
> `appelle` `mtb_get_resultats_travail_du_chien()` ; elle ne le réimplémente pas.**
>
> Le danger n'est pas un `Cannot redeclare` visible : le `function_exists()` imposé par le contrat #1
> §6 l'empêche. C'est **l'ombrage silencieux** — le chargeur parcourt `query/` par ordre alphabétique
> (`scandir()`), `chien` passe **avant** `resultat`, et une fonction homonyme déclarée par #4
> gagnerait **sans un mot**. Un écran blanc se voit ; une fonction fantôme qui renvoie presque la
> bonne chose ne se voit pas. Les noms retenus ici (`…_travail_…`) sont choisis pour que #4 ne les
> écrive pas spontanément.

**Obligation symétrique du thème** (contrat #1 §8) : tout appel derrière `function_exists()`, pour
qu'une extension désactivée donne une page dégradée et non un écran blanc (D12).

### 5.1 La liste des disciplines — source unique

`mtb_resultat_disciplines()` est **la seule** énumération des disciplines de tout le projet. Ni
l'écran de saisie, ni le tri, ni le groupement, ni #15 n'en refont une. Conséquence voulue : ajouter
ou retirer une discipline coûte **une ligne**.

| Clé stockée | Libellé affiché |
|---|---|
| `ring` | **RING** |
| `igp_rci` | **IGP / RCI** |
| `mondioring` | **Mondioring** |
| `obeissance` | **Obéissance** |
| `pistage` | **Pistage** |
| `recherche_utilitaire` | **Recherche utilitaire** |
| `sauvetage` | **Sauvetage** |
| `truffe` | **Truffe** |
| `autres_disciplines` | **Autres disciplines** — 9ᵉ valeur, **arbitrée et retenue**, voir §9 arbitrage 3 |

**On stocke une clé stable, jamais le libellé.** `MASTER.md` §15 D3 marque encore les disciplines
« à confirmer » : une révision de graphie ne doit jamais obliger à réécrire des lignes existantes.

Ordre gelé par la **décision 11** (le nombre et l'ordre) ; graphies gelées par **`MASTER.md` §10.2**
(la typographie). Les deux documents disaient la même liste dans deux graphies : §10.2 est l'arbitre
déclaré des libellés, il l'emporte.

### 5.2 Les colonnes — décision 10, la partie qui casse le téléphone si on la rate

Chaque groupe (et le palmarès) annonce **`colonnes`**, une liste ordonnée de
`array( 'cle' => …, 'libelle' => … )`.

**Ces `libelle` sont exactement les chaînes que #15 recopie dans `data-libelle="…"` sur chaque
`<td>`** (décision 10, `MASTER.md` §7.6). C'est ce qui permet au tableau de se déplier en lignes
libellées sous 48 rem **sans conteneur à défilement horizontal**. Un libellé approximatif rend le
tableau illisible sur téléphone — c'est un échec de la contrainte 360 px, pas un détail.

| `cle` | `libelle` | Page Travail | Palmarès d'un chien |
|---|---|---|---|
| `annee` | **Année** | toujours | toujours |
| `chien` | **Chien** | toujours | **absente** — c'est sa propre fiche |
| `niveau` | **Niveau** | toujours | toujours |
| `discipline` | **Discipline** | **absente** — elle est le titre du groupe | toujours |
| `conducteur` | **Conducteur** | **seulement si** au moins une ligne du groupe la remplit | idem |
| `pays` | **Pays** | **seulement si** au moins une ligne du groupe la remplit | idem |

Les trois colonnes toujours présentes sont **exactement les trois du tableau du site source**
(Année · Chien · Niveau). *Conducteur* et *Pays* sont des champs du brief §5.3 sans aucune donnée au
source : les rendre inconditionnels afficherait deux colonnes entièrement vides.

### 5.3 Forme d'un groupe et d'une ligne

```
groupe = array(
    'discipline'         => 'ring',                 // clé stockée
    'discipline_libelle' => 'RING',                 // chaîne finie, à imprimer telle quelle
    'orpheline'          => false,                  // true = valeur hors liste, voir §6 état 3
    'colonnes'           => array( … ),             // §5.2
    'lignes'             => array( … ),
)

ligne = array(
    'donnees'  => array(                            // valeurs brutes, pour trier ou tester
        'id'         => 42,
        'annee'      => 2021,                       // int ; 0 si non renseignée
        'discipline' => 'ring',
        'chien_id'   => 17,                         // 0 si aucune fiche liée
    ),
    'cellules' => array(                            // ce qui s'imprime, déjà fini
        'annee'      => array( 'texte' => '2021', 'etat' => '',                   'vide' => false ),
        'chien'      => array( 'texte' => 'Upper’Side du Mont Brabant',
                               'url'   => 'https://…/la-meute/upper-side/',
                               'sexe'  => 'male', 'sexe_libelle' => 'Mâle',
                               'etat'  => '',                                     'vide' => false ),
        'niveau'     => array( 'texte' => 'Brevet',        'etat' => '',          'vide' => false ),
        'conducteur' => array( 'texte' => 'Non renseigné', 'etat' => 'donnee_absente', 'vide' => true ),
        'pays'       => array( 'texte' => '',              'etat' => '',          'vide' => true ),
    ),
)
```

**Toutes les clés sont toujours présentes.** Jamais une clé absente que le consommateur devrait tester
à l'aveugle, jamais un avertissement PHP (contrat #1 §9).

**Les six cellules — `annee`, `chien`, `niveau`, `discipline`, `conducteur`, `pays` — sont émises sur
chaque ligne**, y compris celles qu'aucune colonne du consommateur n'affichera. L'exemple ci-dessus
est abrégé, pas normatif. Seul **`colonnes`** varie, et c'est lui que le consommateur parcourt :
il n'énumère jamais les clés de `cellules` lui-même.

Deux règles de décision, à ne jamais réinterpréter :

- **Le lien existe si et seulement si `url` est une chaîne non vide.** Une seule condition dans tout
  le thème ; on ne lit jamais `etat` pour décider d'un lien.
- **`vide === true`** signale une cellule sans valeur. C'est le crochet du repli mobile : sous 48 rem,
  une cellule vide ne doit pas imprimer son étiquette `data-libelle` suivie de rien.

**`sexe_libelle` vaut `''` quand le sexe est inconnu**, et non « Non renseigné » : le sexe n'est pas
une colonne du tableau, y placer un texte de remplissage à côté de chaque nom serait du bruit. Règle
pour #15 : **imprimer `sexe_libelle` seulement s'il est non vide.**

### 5.4 Tri

| Consommateur | Ordre | Raison |
|---|---|---|
| Page Travail (#15) | **année décroissante** | `MASTER.md` §7.6 le gèle. |
| Palmarès d'une fiche chien (#4) | **année croissante** | Une carrière se lit dans son sens : Brevet, RING 1, RING 2, RING 3. `MASTER.md` ne gèle que la page Travail. |

L'ordre est un **paramètre** (`$args['ordre']`) : ni #15 ni #4 ne retrient jamais ce qu'ils reçoivent.

**Départage à l'intérieur d'une même année : l'identifiant du contenu, croissant.** Surtout pas le
niveau — il n'existe aucune hiérarchie officielle des niveaux (même raisonnement que la décision 12),
et en inventer une fabriquerait un fait d'élevage. Ordre **stable et reproductible**, jamais laissé au
hasard de MySQL.

**Une année absente (`0`) se trie en dernier dans les deux sens.** Une ligne incomplète ne remonte
jamais en tête d'un tableau.

### 5.5 Coût en requêtes

Une page Travail à huit groupes ne doit pas coûter huit requêtes plus une par chien. **Cible : quatre
requêtes**, indépendamment du nombre de disciplines et du nombre de chiens liés ; un second appel dans
la même requête HTTP coûte **zéro** (mémorisation en statique).

Le mécanisme : une **seule** lecture de tous les résultats publiés, groupement en PHP ; les fiches
chiens liées hydratées **en un seul lot** (`post__in`), jamais une requête par chien.
`wp_cache_*` est autorisé ; **les transients sont interdits sans justification écrite** (contrat #1
§9) — un transient périmé après modification d'un résultat est exactement l'échec de « saisi une fois,
affiché partout ».

## 6. États spéciaux

Vocabulaire **réutilisé** du contrat #1 §9, jamais réinventé.

| État | Émis par le serveur | Rendu par le consommateur |
|---|---|---|
| `donnee_absente` | `cellules.<colonne>.etat`, avec `texte` déjà égal à **« Non renseigné »** (`MASTER.md` §10.3) | Imprime `texte`. **N'invente jamais un tiret**, ni « Aucun », ni « Néant » (§9.3) |
| `parent_hors_elevage` | `cellules.chien.etat` — **et uniquement quand le nom vient du champ recopié**, aucune fiche n'étant liée | Imprime le nom **sans lien**. **L'affichage ne change pas de forme** : ni grisé, ni italique, ni mention |
| *(aucun état)* | `cellules.chien.etat` vaut **`''`** quand une fiche **est** liée mais n'est pas publiquement consultable (brouillon, protégée, planifiée) : le nom provient bien de la fiche, il n'y a donc rien d'anormal à signaler | Imprime le nom **sans lien** — `url` est vide. Exactement le même rendu que ci-dessus |
| `aucune_portee` | — | Sans objet ici |
| `page_protegee` | **Non émis, délibérément** | Une fiche chien protégée par mot de passe se rend **exactement** comme une fiche en brouillon : le nom, sans lien. Un état distinct obligerait #15 à une branche sans effet et **signalerait au visiteur l'existence d'un contenu réservé** |

> **Note d'impropriété, assumée et écrite ici pour qu'un relecteur de 2027 n'y voie pas un
> copier-coller fautif** : `parent_hors_elevage` est un mot **faux** dans ce contexte — le chien d'un
> résultat n'est le parent de personne. Il est réutilisé tel quel parce que le contrat #1 §9 l'a gelé
> et que la chaîne #3 l'implémente en ce moment : renommer un état pendant qu'une chaîne sœur l'écrit
> serait dangereux pour un gain purement cosmétique. Le coût est nul à l'exécution — le thème regarde
> `url`, jamais `etat`, pour décider d'un lien.

### Les cinq cas de robustesse (D12)

| # | Cas | Comportement garanti |
|---|---|---|
| 1 | **`mtb_chien` pas encore enregistré** — l'état réel du dépôt pendant que #4 tourne | Sélecteur de fiche **vide**, champ « Nom du chien » pleinement utilisable, **aucune erreur, aucune notice**. Les lignes se rendent normalement, `chien.url` vaut `''`. Aucun accès à la base **à l'inclusion** (contrat #1 §3) ; `get_post_type_object()` peut renvoyer `null` et c'est traité |
| 2 | **Résultat sans chien, ou sans année** — nommé explicitement par D12 | La ligne **est renvoyée**. Sans chien : `texte` = « Non renseigné », `etat` = `donnee_absente`. Sans année : `donnees.annee` = `0`, cellule « Non renseigné », **triée en dernier** dans les deux sens. Aucun champ n'est obligatoire à la saisie |
| 3 bis | **Discipline entièrement vide** — le champ n'a jamais été rempli | Traitée **comme une orpheline** : groupe créé, placé **en tout dernier**, `orpheline => true`, mais `discipline_libelle` vaut **« Non renseigné »** (`MASTER.md` §10.3) et non la valeur brute `''` — qui ferait imprimer à #15 un `h2` vide. *Ajouté au contrat le 2026-08-16 : le cas n'était pas couvert.* |
| 3 | **Discipline orpheline** — valeur stockée qui n'est plus dans la liste | **Rien ne disparaît, ni de la page, ni en silence.** Un groupe est créé, ajouté **en fin** de retour, `orpheline => true`, `discipline_libelle` = **la valeur brute recopiée**. À la saisie, la valeur stockée est **ajoutée comme option supplémentaire présélectionnée** : un ré-enregistrement ne l'efface pas. C'est pourquoi le `sanitize_callback` est `sanitize_key` et **non une liste blanche** — une liste blanche détruirait la donnée (contrainte 4) |
| 4 | **Fiche chien liée passée en brouillon, à la corbeille, protégée par mot de passe, ou disparue** | Le **nom reste affiché**, `url` vaut `''` → pas de lien. **C'est la fonction de lecture qui décide, jamais le composant.** Contenu disparu → repli sur `_mtb_chien_nom`, puis `donnee_absente`. À la saisie, l'option reste présélectionnée : le lien n'est pas perdu à l'enregistrement suivant. **Le sous-cas « corbeille » n'a pas pu être exercé** : `mtb_chien`, tel que livré par la chaîne #4, **refuse la mise à la corbeille** (`Posts of type 'mtb_chien' do not support being sent to trash`). Le statut `trash` est bien exclu de la requête ; le code est prêt si #4 revient sur ce choix |
| 5 | **Résultat en brouillon, planifié ou privé** | **Absent** des deux fonctions de lecture (`post_status => 'publish'`, `has_password => false`). Aucune fuite, aucune casse. Le titre est composé pour tous les statuts, donc la liste d'administration reste lisible |

## 7. Chaînes fournies par le serveur

Le consommateur les **imprime**. Il n'en **compose** aucune, il n'en **reformate** aucune
(contrat #1 §8 et §13).

- **Libellés de discipline** — `RING` · `IGP / RCI` · `Mondioring` · `Obéissance` · `Pistage` ·
  `Recherche utilitaire` · `Sauvetage` · `Truffe` (+ `Autres disciplines`, §9 arbitrage 3).
- **Libellés de colonne** — `Année` · `Chien` · `Niveau` · `Discipline` · `Conducteur` · `Pays`.
  Ce sont **exactement** les chaînes à recopier dans `data-libelle` (décision 10).
- **Libellés de sexe** — `Mâle` · `Femelle`.
- **Libellé d'absence** — `Non renseigné`, **déjà placé dans `texte`**.
- **L'année est déjà une chaîne décimale** dans `cellules.annee.texte` (`'2021'`).
  **Interdiction formelle d'appeler `number_format_i18n()`, `number_format()` ou `date_i18n()`
  dessus** : `2 021` serait produit. Il n'y a **aucune date** dans ce modèle de contenu, donc **aucun
  formatage de date** nulle part.

### Le piège du Pays — la seule exception à « Non renseigné »

**Un Pays vide ne signifie pas « inconnu », il signifie « le résultat n'a pas été obtenu à
l'étranger ».** `MASTER.md` §10.2 l'écrit : *« Pays — rempli seulement si le résultat est étranger. »*

Conséquences, toutes obligatoires :

- Le serveur **n'écrit jamais « Non renseigné » dans une cellule Pays vide** — ce serait faux. C'est
  la **seule** exception à la règle de `MASTER.md` §9.3, et elle est justifiée par §10.2 lui-même.
- Le consommateur **n'écrit jamais « France »** dans une cellule Pays vide : le site source ne le dit
  pas, l'écrire inventerait un fait (D11).
- La colonne Pays **n'existe pas du tout** dans un groupe où aucune ligne n'est étrangère (§5.2).
  Sans cela, sous 48 rem, `MASTER.md` §7.6 replierait chaque ligne avec une étiquette « PAYS » suivie
  de rien — vingt-cinq fois.

## 8. Interdits

- Le thème n'interroge **jamais** la base. Un fichier de `wp-content/themes/mtb/` qui contient
  `WP_Query`, `get_posts`, `get_post_meta`, `get_terms`, `$wpdb` ou `MTB\` est en infraction
  (contrat #1 §8).
- **Aucune fonction lisant `mtb_resultat` ne vit hors de `includes/query/resultat/`.** #4 appelle,
  il ne réécrit pas.
- Le consommateur ne **compose** aucun libellé de discipline, de colonne, de sexe ou d'absence.
- Le consommateur ne **trie ni ne regroupe** ce qu'il reçoit : il passe `ordre` et n'y touche plus.
- Le consommateur ne **décide pas** si une fiche est cliquable — `url` le dit.
- Personne ne **réinterprète, normalise ou traduit** un niveau, un pays ou un nom de chien.
  Personne n'invente de **hiérarchie de niveaux**.
- L'extension n'émet **aucune règle visuelle ni mise en page**, y compris en administration :
  **aucun attribut `style=`, aucune balise `<style>`, aucune couleur, aucune dimension, aucune classe
  décorative inventée.** Les classes **du cœur de WordPress** sont seules admises — `form-table`,
  `regular-text`, `description`, `small-text`, `screen-reader-text` et leurs semblables.
  *Précision du 2026-08-16 : ma première rédaction énumérait trois classes et se lisait comme une
  liste close. Le critère n'est pas la liste, c'est l'origine — une classe du cœur n'émet aucune
  décision visuelle de notre fait ; `screen-reader-text` porte même de l'accessibilité.*
- Aucun module n'édite `mtb-core.php` ni `class-loader.php`, n'appelle `flush_rewrite_rules()`,
  n'utilise `init` 99, ni ne dépend de l'ordre de parcours des groupes.
- **Aucun hook, aucun filtre n'est exposé par cette issue.** Surface close, comme le chargeur. Si #15
  a besoin d'un point d'extension, il se demande et se gèle — il ne s'improvise pas.

## 9. Arbitrages

| # | Désaccord | Décision | Raison |
|---|---|---|---|
| 1 | Préfixe des clés de méta : `mtb_` (règle de lot) ou `_mtb_` (contrat #1 §6) | **`_mtb_`** | Le contrat #1 est gelé et son arbitrage 8 est motivé : le tiret bas rend la méta protégée, donc invisible du panneau « Champs personnalisés ». C'est la garantie mécanique qu'aucun mot interdit de `MASTER.md` §10.4 n'atteint son écran. Signalé à `/lead-mtb` pour alignement des trois chaînes du lot. |
| 2 | Discipline en **méta à liste fermée** ou en **taxonomie `mtb_discipline`** | **Méta** | La taxonomie ouvre à l'éleveuse un écran « ajouter un terme » qui **crève la liste close** de la décision 11, impose les mots interdits `taxonomie`/`terme`/`slug` (§10.4), et exige un semis en base — donc un mode de panne silencieux. Elle n'achète aucune performance sur ~30 lignes qu'on charge de toute façon en entier. À `show_ui => false`, on réécrit quand même le `<select>` à la main : tout le coût, la moitié du bénéfice. |
| 3 | **Neuvième valeur `autres_disciplines` → « Autres disciplines »** | **Retenue — arbitrée par `/lead-mtb` le 2026-08-16, qui a révisé la décision 11 à neuf valeurs** | Relevé sur le site source : la section « Autres disciplines : » contient quatre lignes réelles dont **Agility** et **Brevet Maitre Chien Drogue**, qu'aucune des huit disciplines gelées ne peut exprimer. Sans neuvième valeur, ces lignes sont **perdues à la reprise** — contrainte 4 et D4. Le libellé est **recopié du site source**, donc pas inventé. **Réserve honnête** : une valeur fourre-tout perd la discipline réelle des quatre lignes. La vraie réponse est un fait d'élevage — *ces quatre-là sont-elles des disciplines à part entière, ou la rubrique « Autres » du site actuel ?* — et elle appartient à l'éleveuse. L'architecture garantit qu'ajouter chacune coûte **une ligne** dans `mtb_resultat_disciplines()`, et **aucune donnée n'est encore saisie**. |
| 4 | Graphie des disciplines : décision 11 (`IGP/RCI`, minuscules) ou `MASTER.md` §10.2 (`IGP / RCI`, capitales) | **`MASTER.md` §10.2** | §10 est l'arbitre déclaré des libellés. La décision 11 tranchait le **nombre** et l'**ordre**, pas la typographie. Note : le site source écrit une troisième graphie, `IGP (RCI)` — c'est un intertitre de page, pas un libellé de champ ; il ne fait pas foi. |
| 5 | Libellé de « chien concerné », **absent de `MASTER.md` §10** | Étiquette du **groupe de saisie** = **« Chien concerné »** (formulation du BRIEF §5.3), libellé de **colonne publique** = **« Chien »** (§10.2 tel quel) | **Arbitrage en deux temps, à lire comme tel.** `/lead-mtb` avait d'abord tranché « Chien » partout, pour ne pas fabriquer une entrée de §10 ni livrer deux mots pour une même chose. Décision **révisée et assumée le 2026-08-16**, pour trois raisons : ce champ est un **groupe de deux contrôles** (sélecteur de fiche *et* nom recopié), que « Chien » seul ne peut pas nommer à la fois ; « Chien concerné » vient du **BRIEF §5.3**, donc sourcé et non inventé ; et **la fiche d'aide dit le même mot**, donc l'écran et le guide concordent — ce qui est le seul critère qui compte pour Fabienne. Le risque écarté à l'origine — deux mots pour une même chose — ne se réalise pas : **aucun autre écran ne porte ce champ**. La colonne publique reste « Chien », conforme à §10.2 et à la décision 10 pour `data-libelle`. Le manque de §10.2 part en **dette de documentation** à charge de `lead-design-mtb`. |
| 6 | Champ **Sexe** : hors du brief §5.3 | **Ajouté, facultatif** | Le site source imprime ♂/♀ devant chaque nom. Pour le cas majoritaire — un chien sans fiche — l'information serait **perdue**. Libellé et valeurs repris de §10.2, donc rien n'est inventé. Ce n'est pas un élargissement du brief, c'est ce qu'il faut pour ne pas perdre une donnée affichée par le site qu'on reprend. |
| 7 | Colonnes *Conducteur* et *Pays* : inconditionnelles ou calculées | **Calculées** — présentes seulement si au moins une ligne du groupe les remplit | Le tableau réel du site source n'a que trois colonnes. Le Conducteur est **absent du site source en totalité** ; le Pays n'y figure que deux fois. Les rendre inconditionnelles afficherait deux colonnes entièrement vides, et sous 48 rem deux étiquettes suivies de rien. |
| 8 | État `page_protegee` pour une fiche chien protégée | **Non émis** | Comportement strictement identique au brouillon (nom, pas de lien). Un état distinct obligerait #15 à une branche sans effet et **signalerait au visiteur l'existence d'un contenu réservé**. |
| 9 | `sanitize_callback` de la discipline : `sanitize_key` ou liste blanche | **`sanitize_key`** | Une liste blanche **détruirait** une valeur devenue orpheline au premier ré-enregistrement. La contrainte 4 interdit qu'une donnée disparaisse en silence. La liste close vit à l'écran et au rendu, pas dans l'assainissement. |
| 10 | `show_in_rest => false` | **Retenu** | C'est ce qui donne l'écran classique, donc les trente secondes. Seul coût : invisibilité en REST — aucun consommateur n'en a besoin, WP-CLI n'en dépend pas, il n'y a pas de page publique. |

## 10. Le coût d'une saisie — exigence mesurable, pas un slogan

Menu **« Résultats de travail » → Ajouter** *(1 clic, 1 chargement — écran classique, léger)* →
elle remplit **8 contrôles, dont 5 en pratique** : Discipline · Chien concerné *ou* Nom du chien ·
Sexe · Année · Niveau ou titre obtenu · Conducteur · Pays *(vide sauf étranger)* → **Publier**
*(1 clic, 1 chargement)*.

**2 chargements de page · 2 clics de navigation · ~5 champs frappés.** Aucun titre à inventer, aucune
mise en forme à recopier.

Trois accélérateurs, **sans une ligne de JavaScript** : `<datalist>` alimenté par les conducteurs et
les niveaux déjà saisis · `inputmode` numérique sur l'année · les deux champs du chien toujours
visibles tous les deux.

**Ce compte porte sur *ajouter*, pas sur *retrouver*.** Voir dette T-#5-a.

## 11. Inventaire des modules — ajout à `docs/contracts/issue-1.md` §11

| Groupe | Module | Issue | Rôle |
|---|---|---|---|
| `content` | `resultat` | #5 | Enregistre `mtb_resultat` et ses huit champs |
| `fields` | `resultat` | #5 | Écran de saisie en français, nonce, assainissement, composition du titre |
| `query` | `resultat` | #5 | Les quatre fonctions de lecture exposées au thème |

## 12. Dettes créées par cette issue

| # | Dette | Payée par |
|---|---|---|
| **T-#5-a** | **La liste d'administration des résultats n'a ni colonne Discipline, ni filtre, ni tri par année** — `includes/admin/**` est hors empreinte de ce lot. Sa seule surface de balayage est le titre composé, dans l'ordre de publication. Supportable à zéro résultat, **pénible dès l'import des ~30 lignes** : « trente secondes » vaut pour ajouter, pas pour retrouver. | une issue `admin`, **avant la reprise de contenu** |
| **T-#5-b** | **Le contrôle « fiche ou nom recopié » est dupliqué** entre `fields/portee/` (#3) et `fields/resultat/` (#5). À **examiner, pas forcément à fusionner** : celui de #3 porte deux sous-champs (*Nom* + *Élevage*, §10.2), le nôtre un seul — le site source imprime une chaîne unique où l'affixe fait partie du nom. Une fusion prématurée imposerait un champ « Élevage » que le domaine ne demande pas ici. | une issue de consolidation |
| **T-#5-e** | **`assainir_texte_recopie()` est dupliquée dans les trois modules du lot** (`content/resultat/`, `content/chien/`, `portee`). Les empreintes disjointes interdisaient un fichier partagé — c'est le prix assumé du parallélisme, pas un oubli. Trois copies d'une même définition de « valeur propre » finiront par diverger. | une issue de consolidation, qui la hisse dans un module commun |
| **T-#5-c** | **`docker/fixtures/resultats.json` est incompatible avec ce modèle** : il porte `"discipline": "RING"` / `"IGP"` (des libellés, pas des clés) et `"chien": "rex-du-mont-brabant"` (un slug, pas un identifiant). Non touché — hors empreinte. | l'issue qui livrera `wp mtb import-fixtures` |
| **T-#5-d** | **Aucune des trois issues du lot ne livre `wp mtb import-fixtures`** (`includes/migration/**` hors des trois empreintes). `provision.sh` se dégrade proprement (`\|\| log AVERTISSEMENT`), mais **Docker restera sans contenu de démonstration** après le lot 2. | une issue `migration`/`infra` |

## 13. Points restés ouverts

| # | Question | Bloque | Qui tranche |
|---|---|---|---|
| **Q-a** | **La neuvième valeur `autres`, et la question de fond derrière** : Cavage, Agility, Brevet Maître Chien Drogue et Qualification Chien de sauvetage sont-elles des disciplines à part entière, ou la rubrique « Autres » du site actuel ? Sans réponse, quatre lignes du site source sont mal classées ou perdues. **Ne bloque pas le code** — seulement la reprise. | la reprise des résultats | **l'éleveuse**, via `/lead-mtb`. Recoupe `MASTER.md` §15 D3. |
| **Q-b** | **Valeurs stockées de `_mtb_sexe` par la chaîne #4** : `male`/`femelle` ou `Mâle`/`Femelle` ? Ne bloque pas — le repli « valeur brute telle quelle » couvre les deux cas sans coupler les modules. À aligner au niveau du lot pour éviter une divergence durable. | rien | `/lead-mtb`, au gel des trois contrats |
| **Q-c** | **`post_updated_messages` depuis `fields/resultat/`** : le contrat #1 §2 ne liste pas ce hook dans le groupe `fields`. Sans lui, l'écran affiche **« Article publié. »** — mot proscrit par `MASTER.md` §10.2 — sur l'écran même que livre cette issue. **Retenu** : il est dans l'empreinte et sert directement l'objet de l'issue. | rien | `/lead-mtb`, pour confirmation |
| **Q-d** | **`menu_position`** : **25** retenu. #3 et #4 choisissent les leurs sans me voir ; une collision ne casse rien (WordPress décale) mais rend l'ordre du menu imprévisible d'une installation à l'autre. | rien | `/lead-mtb`, réserver 21/22/25 |
| **Q-e** | **Où placer le sexe dans la cellule Chien**, et **la règle CSS qui supprime l'étiquette d'une cellule vide sous 48 rem** : ni l'un ni l'autre n'existe dans `MASTER.md` §7.6. Ce sont des décisions de rendu, pas de contrat. | le rendu de #15 | **`lead-design-mtb`**, puis révision de `MASTER.md` §7.6 |
| **Q-g** | **`MASTER.md` §10.2 fige la liste des disciplines à huit et ne contient pas « Autres disciplines ».** Le code en livre neuf (§9 arbitrage 3). Si la neuvième est confirmée, §10.2 a besoin d'une ligne — **hors de mon empreinte**, je n'y touche pas. Si elle est refusée, c'est une ligne à retirer de `mtb_resultat_disciplines()`, et aucune donnée n'est saisie. | rien | `/lead-mtb`, puis `lead-design-mtb` |
| **Q-f** | **Le champ Conducteur sera vide sur toutes les lignes reprises** : le site source ne nomme aucun conducteur. Sa seule mention approchante, `Prop. Ferrari`, désigne un **propriétaire** — **ne pas l'y verser à la reprise**, ce serait requalifier un fait (D11). Corollaire RGPD : cette issue ne publie **rien de nouveau**. | la reprise, pas le code | issue `contenu` |

## 14. Journal des amendements

Ce contrat a été gelé **avant** l'implémentation, puis corrigé **par** elle. Les cinq entrées ci-dessous
sont des rectifications de ma rédaction, pas des dérives du développement : les consigner vaut mieux
que de les lisser.

| Date | Section | Ce qui a changé | Pourquoi |
|---|---|---|---|
| 2026-08-16 | §2 | `supports => array()` → **`supports => false`** | Ma valeur produisait **l'inverse exact** de l'intention : `WP_Post_Type::set_props()` lit un tableau vide comme « rien de demandé » et ajoute d'office `title`, `editor`, `autosave`. Constaté à l'écran en Docker, pas déduit. |
| 2026-08-16 | §2 | Ajout du retrait de la boîte **« Slug »** du cœur, de `menu_position` 25 et de l'absence de `menu_icon` | La boîte « Slug » est ajoutée d'office par le cœur à tout écran classique et propose un **mot proscrit** (`MASTER.md` §10.4) sur l'écran même que livre cette issue. |
| 2026-08-16 | §5.3 | Précision : **les six cellules sont toujours émises**, l'exemple est abrégé et non normatif | Deux phrases du contrat se contredisaient. Le consommateur parcourt `colonnes`, jamais les clés de `cellules`. |
| 2026-08-16 | §6 | Ajout de l'état **3 bis** (discipline entièrement vide) et distinction de `parent_hors_elevage` (nom recopié) d'avec `etat = ''` (fiche liée non consultable) | Le cas « champ jamais rempli » n'était pas couvert et aurait fait imprimer un `h2` vide. |
| 2026-08-16 | §8 | La liste des classes du cœur admises n'est plus close | Le critère est **l'origine** (une classe du cœur n'émet aucune décision visuelle de notre fait), pas l'énumération. `screen-reader-text` porte même de l'accessibilité. |

| 2026-08-16 | §3 | `sanitize_text_field` → **`assainir_texte_recopie()`** sur les quatre champs recopiés, appelée par les **deux** filets | Relevé par `/lead-mtb` sur l'arbre, après mon premier commit. `strip_tags()` **vide en silence** toute valeur commençant par `<` — or `<60` est un niveau plausible. D11 enfreinte par l'outillage. Vérifié à l'exécution : `<60` saisi par le vrai formulaire ressort exactement `<60`. |
| 2026-08-16 | §5 | `mtb_resultats_travail_*` → **`mtb_get_resultats_travail_*`** | Le contrat gelé #1 §6 impose le préfixe de lecture `mtb_get_*`. Le segment `_travail_` est conservé, c'est lui qui protège de la collision avec `query/chien/`. **Ce n'était pas cosmétique** : #4 est commitée et appelle `mtb_get_resultats_travail_du_chien()` derrière un `function_exists()` — avec l'ancien nom, le test rendait **toujours faux**, donc un palmarès vide en permanence sur un site qui répond 200, sans une ligne au journal. |
| 2026-08-16 | §5.1, §9 | Clé de la 9ᵉ discipline `autres` → **`autres_disciplines`** | Graphie arbitrée par `/lead-mtb`, qui a révisé la **décision 11 à neuf valeurs**. Aucune donnée n'était saisie. |

**Correction technique reportée ici pour la chaîne suivante** : le `TypeError` d'une clé de discipline
purement numérique ne tombait **pas** là où l'analyse statique le plaçait. Le rappel typé passé à
`array_filter()` coerce silencieusement `int` → `string` parce qu'il est appelé depuis une fonction
interne ; le fatal tombait un cran plus loin, sur `groupe( 2019, 2019, … )`, **les deux** premiers
arguments étant des `int` (le libellé hérite du type de la clé). La parade est donc à la **sortie du
tableau** (`array_map( 'strval', array_keys( … ) )`), jamais au point d'appel. Reproduit puis corrigé
en Docker, non déduit.
