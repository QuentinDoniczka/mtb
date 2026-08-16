# Contrat d'interface — Issue #4 — Type de contenu Chien

**Gelé le 2026-08-16.** Contraignant à partir d'ici. En cas de divergence avec un plan ou un
commentaire de code, **ce document fait foi**.

Il hérite intégralement de [`issue-1.md`](issue-1.md) — nommage (§6), français littéral sans i18n
(§7), frontière thème/extension (§8), états spéciaux (§9), recettes de module (§10), conventions de
code (§12), interdits (§13). Ce contrat ne réécrit rien de tout cela ; il ne dit que ce qui est
propre au Chien.

---

## 1. Le type de contenu

| Élément | Valeur | Motif |
|---|---|---|
| Slug | **`mtb_chien`** — gelé, il ne bouge plus | Consommé par le champ père/mère de la Portée (#3) et par le champ « chien concerné » du Résultat de travail (#5). 9 caractères, très en dessous de la limite dure de 20. |
| Écran de saisie | **Formulaire classique** — `use_block_editor_for_post_type` → `false`, **borné à `mtb_chien`** | Décision de lot. Une fiche ne se compose pas, elle se remplit. En éditeur de blocs, les vingt champs atterrissent en boîtes **sous** le canevas : elle ferait défiler à chaque chien pour atteindre ce pourquoi elle est venue. |
| `show_in_rest` | **`true`**, conservé | L'API reste disponible pour un futur composant et pour un import en ligne de commande. Le filtre ci-dessus ramène l'écran classique sans toucher à REST : les deux ne sont pas liés. |
| `has_archive` | **`false`** | « La meute » est une page libre composée de composants (BRIEF §5.4). Une archive créerait un index concurrent. **Point de vigilance pour #24 (`seo`) et l'epic Gabarits** : c'est une ligne à rouvrir s'ils en ont besoin, pas une impasse. |
| `rewrite` | préfixe **`chien`** → `/chien/<nom-dusage>/` | Choix produit, pas fait du domaine. Les URL de l'ancien site ne sont pas connues (`docs/migration/source/` n'existe pas). Toute reprise passera par une **301** de l'issue `seo` — lié à Q4 de `ETAT.md`. |
| `menu_position` | **22** | Proposition de lot : Portées 21, Chiens 22, Résultats de travail 23. Deux types à la même position, et WordPress en décale un **en silence** : l'ordre du menu devient alors dépendant de l'installation. |
| Titre WordPress | **c'est le Nom d'usage** | `enter_title_here` filtré sur `mtb_chien`. Zéro champ dupliqué, l'URL et la recherche natives fonctionnent, et MASTER §7.5 fait déjà du nom d'usage le `h1` de la fiche. |
| Photo principale | image mise en avant, **relabellisée « Photo principale »** | MASTER §10.2. « image mise en avant » est un **mot interdit à l'écran** (§10.4). |
| `flush_rewrite_rules()` | **jamais appelé** | Contrat #1 §4 et §13 : l'empreinte du chargeur s'en charge toute seule. |

## 2. Fonctions de lecture exposées par l'extension

**Surface publique de cette issue — close. Deux fonctions, pas une de plus.**

### `mtb_get_chien( int $chien_id = 0 ): ?array`

Renvoie `null` si l'identifiant ne désigne pas un `mtb_chien` publié. Sinon un tableau dont
**toutes les clés ci-dessous sont toujours présentes** — jamais une clé absente que le thème devrait
tester à l'aveugle (contrat #1 §9).

```
'id'                   int
'etat'                 'normal' | 'page_protegee'
'nom_usage'            string   — le titre ; jamais vide (WordPress impose un titre)
'nom_complet'          array{ valeur: string, affichage: string }   affichage = 'Non renseigné' si vide
'sexe'                 array{ valeur: 'male'|'femelle'|'', affichage: 'Mâle'|'Femelle'|'Non renseigné' }
'variete'              array{ valeur: 'poil_long'|'poil_court'|'', affichage: 'Poil long'|'Poil court'|'Non renseigné' }
'date_naissance'       array{ valeur: 'AAAA-MM-JJ'|'', affichage: string, libelle: 'Né le'|'Née le' }
'date_deces'           array{ valeur: 'AAAA-MM-JJ'|'', affichage: string, libelle: 'Décédé le'|'Décédée le' }
'statut'               array{ valeur: string, affichage: string }   ← accord au sexe, voir §4
'taille'               array{ valeur, affichage }
'couleur'              array{ valeur, affichage }
'masque'               array{ valeur, affichage }
'genetique_robe'       array{ valeur, affichage }
'sante'                array — voir §5
'sante_renseignee'     bool  — false si AUCUN des neuf champs de santé n'est rempli
'titres'               array — voir §5
'titres_renseignes'    bool
'pere'                 array — voir §6
'mere'                 array — voir §6
'photo_principale'     array{ id: int, alt: string } | null
'cadrage'              array{ valeur: string, affichage: string }   défaut 'centre' / 'Centre'
'galerie'              array de array{ id: int, alt: string }       array() si vide
'pedigree'             array{ url: string, libelle: 'Voir le pedigree' } | null
```

`etat === 'page_protegee'` : **aucun champ du domaine n'est renseigné** dans le retour. Le contenu
protégé ne fuit pas par la fonction de lecture (BRIEF §8).

### `mtb_get_chiens_par_statut( array $args = array() ): array`

Renvoie une **liste ordonnée de groupes**. Un groupe :

```
'statut'   string   clé canonique
'libelle'  string   titre de groupe au pluriel, fourni par le serveur
'chiens'   array    liste de fiches, chacune au format de mtb_get_chien()
```

**Un groupe vide n'est pas renvoyé du tout.** MASTER §9.3 : une section non remplie n'est pas
rendue, et **c'est le serveur qui en décide, jamais le thème**.

**Ordre des groupes — gelé** : `reproducteur` → `en_cours_de_confirmation` → `retraite` →
`disparu`. Le visiteur qui cherche un chiot voit d'abord les chiens qui produisent ; la génération
qui arrive suit ; puis l'arc de la vie. *(L'énumération de BRIEF §5.2 est un ordre d'écriture, pas
un ordre d'affichage.)*

**Ordre interne — gelé** : date de naissance **décroissante** ; les chiens sans date en fin de
groupe ; départage alphabétique sur le nom d'usage. Déterministe et explicable — c'est ce qui
compte, puisque la date est facultative.

Titres de groupes fournis par le serveur : **Reproducteurs** · **En cours de confirmation** ·
**Retraités** · **Disparus**.

`$args` accepte `'ordre'` pour un futur composant « Grille de chiens » qui offrirait le choix à
l'éditrice. **Le thème ne le passe jamais.**

### Ce que cette issue ne déclare PAS

| Fonction | Propriétaire | Comment on l'obtient |
|---|---|---|
| `mtb_get_portees_du_chien( int $chien_id ): array` | **issue #3**, `includes/query/portee/bootstrap.php` | appelée sous `function_exists()` |
| `mtb_get_resultats_travail_du_chien( int $chien_id, array $args = array() ): array` | **issue #5**, `includes/query/resultat/bootstrap.php` | appelée sous `function_exists()` |

**C'est le seul nom à appeler pour le palmarès.** Le segment `_travail_` est délibéré : c'est ce que
personne n'écrit par hasard depuis `query/chien/`, ce qui écarte définitivement l'ombrage silencieux
décrit ci-dessus.

Elle renvoie une **structure de tableau**, pas une liste plate — forme **fixe même quand tout est
vide** (`array( 'colonnes' => array(), 'lignes' => array() )`) :

```php
array(
    'colonnes' => array( array( 'cle' => 'discipline', 'libelle' => 'Discipline' ), … ),
    'lignes'   => array(
        array(
            'id'       => int,
            'cellules' => array(
                'discipline' => array( 'valeur' => 'ring', 'affichage' => 'RING', 'url' => '', 'etat' => '' ),
                // même forme pour annee, niveau, conducteur, pays
            ),
        ),
    ),
)
```

Cinq colonnes : `discipline` · `annee` · `niveau` · `conducteur` · `pays`. Tri par défaut **année
croissante** — une carrière se lit dans son sens — les résultats sans année toujours en dernier,
jamais présentés comme les plus anciens.

La fiche teste `empty( $palmares['lignes'] )` et **ne rend pas la section Palmarès** : pas de titre
orphelin, pas de tableau vide. Les cinq `colonnes[].libelle` sont ceux que le composant tableau
recopiera dans `data-libelle` (décision 10) : **ne les réécris pas, ne les abrège pas** — imprime-les
tels que la fonction les donne.

> **Le palmarès n'est pas chez moi.** La ligne « palmarès (agrège les résultats de travail) » de la
> checklist de l'issue #4 est **servie par appel, pas par implémentation**. La prochaine chaîne qui
> lit ce contrat ne doit pas croire l'inverse.

**Pourquoi**, et il faut le retenir : le principe de lot est que **le type qui possède la donnée
possède la lecture**. Les portées appartiennent à `mtb_portee`, les résultats à `mtb_resultat`. Si
j'avais déclaré ces fonctions, il n'y aurait eu **aucune erreur fatale** — le contrat #1 §6 impose
`function_exists()` — mais `scandir()` parcourt `includes/query/` **par ordre alphabétique**, et
`chien` passe avant `portee` comme avant `resultat` : **ma version aurait silencieusement ombré la
leur.** Un site qui répond 200, avec la mauvaise implémentation gagnante, et pas une ligne au
journal. C'est la forme même de la dette T5.

**Au moment où le code de cette issue est écrit, les deux fonctions peuvent ne pas exister.** La
garde `function_exists()` est obligatoire ; l'absence donne un **état vide propre**, jamais une
erreur (D12). Ces appels ont lieu dans le **gabarit de fiche chien** (thème, epic Gabarits), pas
dans ce module — le thème appelle les fonctions de lecture depuis ses gabarits.

### Réservation de noms — à respecter par #3 et #5

Aucune autre chaîne ne déclare de fonction commençant par **`mtb_get_chien`**, **`mtb_get_chiens`**
ou **`mtb_chien_`**. Sans cette réservation, `function_exists()` ne produit pas une panne bruyante
mais un **premier-arrivé-gagne silencieux**, réglé par l'ordre alphabétique des dossiers — la panne
la plus difficile à diagnostiquer du projet.

## 3. Champs et clés de méta

**Toutes les clés portent le tiret bas initial** (`_mtb_…`, contrat #1 §6 et arbitrage 8) : il rend
la méta **protégée**, donc jamais listée dans le panneau « Champs personnalisés ». C'est la garantie
**mécanique** qu'aucune clé technique n'atteint l'écran de l'éleveuse — `champ personnalisé` et
`métadonnée` sont des mots interdits (MASTER §10.4). `auth_callback` explicite sur chaque
`register_post_meta`.

Racines partagées avec les chaînes sœurs, employées à l'identique : `date_naissance`, `sexe`,
`galerie`.

### Identité

| Libellé exact à l'écran | Clé | Contrôle | Vide → |
|---|---|---|---|
| *(le champ titre)* **Nom d'usage** | `post_title` | texte natif | — |
| **Nom complet (avec affixe)** | `_mtb_nom_complet` | texte | `Non renseigné` |
| **Sexe** | `_mtb_sexe` | radios | `Non renseigné` |
| **Variété** | `_mtb_variete` | radios | `Non renseigné` |
| **Date de naissance** | `_mtb_date_naissance` | date | `Non renseigné` |
| **Date de décès** | `_mtb_date_deces` | date | *(section masquée)* |
| **Statut** | `_mtb_statut` | radios | `Non renseigné` |

### Taille et robe

| **Taille** | `_mtb_taille` | texte | `Non renseigné` |
|---|---|---|---|
| **Couleur** | `_mtb_couleur` | texte | `Non renseigné` |
| **Masque** | `_mtb_masque` | texte | `Non renseigné` |
| **Génétique de robe** | `_mtb_genetique_robe` | texte | `Non renseigné` |

### Santé — **toutes les valeurs sont recopiées, jamais interprétées** (décision 12)

| **Dysplasie des hanches (HD)** | `_mtb_sante_hd` | texte | `Non renseigné` |
|---|---|---|---|
| **Dysplasie des coudes (ED)** | `_mtb_sante_ed` | texte | `Non renseigné` |
| **LTV** | `_mtb_sante_ltv` | texte | `Non renseigné` |
| **DM** | `_mtb_sante_dm` | texte | `Non renseigné` |
| **SDCA 1** | `_mtb_sante_sdca1` | texte | `Non renseigné` |
| **SDCA 2** | `_mtb_sante_sdca2` | texte | `Non renseigné` |
| **ADN identifié** | `_mtb_adn_identifie` | **trois** radios | `Non renseigné` |
| **Diversité génétique** | `_mtb_diversite_genetique` | texte | `Non renseigné` |
| **Autres tests de santé** | `_mtb_autres_tests` | zone de texte, une ligne par test | *(masqué)* |

**« ADN identifié » n'est pas une case à cocher.** Une case a deux états ; le domaine en a trois —
*Oui*, *Non*, *pas encore renseigné*. Une case décochée par défaut **affirmerait « Non » sur les 17
fiches dès l'import** : c'est inventer un fait d'élevage (D11). Trois radios, valeur vide par
défaut, et **jamais** « Aucun » ni « Non testé » à l'affichage — seulement « Non renseigné »
(MASTER §9.3).

**« Autres tests de santé » tient la contrainte 1** : un neuvième test ne doit jamais exiger
d'ouvrir un fichier. Les huit connus gardent en revanche des **clés fixes**, parce que la fiche
Portée (#3) doit lire `_mtb_sante_hd` d'un parent sans deviner un libellé tapé à la main
(BRIEF §5.1 : « tests repris de la fiche quand le parent est une fiche Chien »).

### Titres et brevets

| **TC** | `_mtb_tc` | texte | `Non renseigné` |
|---|---|---|---|
| **CSAU** | `_mtb_csau` | texte | `Non renseigné` |
| **Cotation LOF** | `_mtb_cotation_lof` | texte | `Non renseigné` |
| **Confirmation** | `_mtb_confirmation` | texte | `Non renseigné` |
| **N° LOF** | `_mtb_lof` | texte | `Non renseigné` |
| **Autres titres et brevets** | `_mtb_autres_titres` | zone de texte, une ligne par titre | *(masqué)* |

**Les résultats d'exposition vont dans « Autres titres et brevets ».** BRIEF §5.2 les liste comme
titres du chien, et la décision 11 ferme les disciplines de travail à **huit** — exposition n'en
fait pas partie. Frontière avec #5, à retenir : *ce qui a une discipline, une année et un conducteur
est un Résultat de travail ; ce que le chien porte de façon permanente est un champ de la fiche.*

### Filiation — **profondeur 1 stricte**

Trois métas par parent, **forme gelée à l'identique avec #3** :

| **Père** | `_mtb_pere_fiche` (identifiant, `0` si aucune) · `_mtb_pere_nom` · `_mtb_pere_elevage` |
|---|---|
| **Mère** | `_mtb_mere_fiche` · `_mtb_mere_nom` · `_mtb_mere_elevage` |

Saisie : une liste déroulante `<select>` native des fiches Chien — **article courant exclu** — plus
deux champs de repli *Nom* et *Élevage*, employés quand aucune fiche n'est choisie.

**Aucune remontée d'ascendance, nulle part, jamais.** Le brief ne demande aucun arbre généalogique :
il demande un père, une mère, et un **lien pedigree externe LOF Select** qui fait exactement ce
travail. Donc **aucune récursion**, et la question de la boucle infinie est sans objet. Il ne reste
que l'auto-référence directe, refusée à la source *et* à la sauvegarde.

### Photos et pedigree

| **Photo principale** | image mise en avant native, relabellisée |
|---|---|
| **Cadrage de la photo** | `_mtb_cadrage` — `haut_gauche` · `haut` · `centre` · `haut_droite` · `bas` (défaut `centre`) |
| **Galerie photos** | `_mtb_galerie` — liste ordonnée d'identifiants, **modale média du cœur** |
| **Lien pedigree (LOF Select)** | `_mtb_pedigree` — URL, `esc_url_raw` en entrée |
| **Commentaire de l'éleveuse** | `post_content` — éditeur de contenu habituel, **bridé** (voir ci-dessous) |

La modale média est celle du cœur, servie depuis notre domaine : **aucune requête tierce** (D6).
Rien de maison, rien de nouveau à lui apprendre.

### L'éditeur du commentaire est bridé — délibérément

Deux portes du système de design sont fermées sur l'écran Chien, chacune atteignable en deux clics
sans rien connaître :

| Retiré | Filtre | Pourquoi |
|---|---|---|
| Bouton **« Ajouter un média »** | `wp_editor_settings` → `media_buttons => false` | `média` est un **mot interdit à l'écran** (§10.4). Surtout : une photo insérée au fil de la prose échapperait au traitement des photos du design system. **Les photos ont leur chemin, c'est le champ Galerie photos.** |
| **Sélecteur de couleur de texte et de fond** (2ᵉ barre d'outils) | `mce_buttons_2` → retrait de `forecolor` et `backcolor` | Met l'éleveuse à deux clics d'une couleur **hors des quinze jetons** de §3.1, et le CSS ne peut rien y faire : la couleur part en **style en ligne**. C'est la dette **T7** transposée à la prose. |

> **Les deux filtres sont GLOBAUX et sont bornés par `get_current_screen()`** (`post_type ===
> 'mtb_chien'` et `base === 'post'`). Sans cette borne ils éteindraient les mêmes boutons **sur les
> Pages**, qui appartiennent à l'issue #18 — un débordement hors empreinte **par effet de bord**, le
> pire genre, puisqu'il n'apparaît dans aucun `git status`. Toute issue future qui touche à ces deux
> filtres doit reprendre la borne.

**Conséquence assumée pour l'éleveuse** : elle ne peut plus insérer de photo dans le commentaire.
C'est le prix de la contrainte 2 — le design ne se contourne pas depuis un champ de texte — et la
fiche d'aide le dit en clair.

## 4. Listes fermées — clé stockée courte, libellé long affiché

**La valeur stockée est neutre et immobile ; l'accord ne vit qu'à l'affichage.** Si on stockait
« Reproductrice », **changer le sexe d'un chien réécrirait sa donnée** — inacceptable.

| Clé stockée | Mâle *ou sexe vide* | Femelle |
|---|---|---|
| `reproducteur` | Reproducteur | **Reproductrice** |
| `retraite` | Retraité | **Retraitée** |
| `disparu` | Disparu | **Disparue** |
| `en_cours_de_confirmation` | En cours de confirmation | En cours de confirmation *(invariable)* |

Même mécanisme, **même endroit dans le code**, pour la date de décès : « Décédé le » / « Décédée
le », et pour la date de naissance : « Né le » / « Née le ».

> **Sexe vide → forme masculine canonique**, celle de MASTER §10.2. **Jamais**
> « Reproducteur·rice », **jamais** « Retraité(e) », **jamais** un tiret. C'est du D12 et de
> l'accessibilité : un point médian ou une parenthèse se lisent mal en synthèse vocale, et le
> public du brief inclut des personnes âgées.

`MASTER.md` **§3.3 ne dit rien du statut d'un chien** — cette table ne couvre que les trois états de
*disponibilité d'une portée* (`disponible`/`reserve`/`passee`), qui est le champ de #3. Les clés
ci-dessus font donc foi.

Autres listes fermées : `sexe` → `male`/`femelle` (**Mâle**/**Femelle**) · `variete` →
`poil_long`/`poil_court` (**Poil long**/**Poil court**) · `adn_identifie` → `oui`/`non`
(**Oui**/**Non**) · `cadrage` → cinq clés ci-dessus.

**Première option : « Non renseigné »** — jamais un « — Choisir — » inventé — **pour les listes qui
portent un fait d'élevage** : Sexe, Variété, Statut, ADN identifié.

**« Cadrage de la photo » en est explicitement exempt**, avec le défaut `centre` / **Centre**. Ce
n'est pas un fait d'élevage mais un **réglage d'affichage** : « Non renseigné » pour un point
d'ancrage n'aurait aucun sens à l'écran, et l'absence de choix doit produire un cadrage utilisable,
pas un vide. *(Tension relevée par `refacto-mtb` entre cette règle et le défaut gelé en §2/§3 ; elle
est levée ici, dans le sens du défaut.)*

## 5. Formes de retour détaillées

`sante` et `titres` : un tableau associatif dont chaque entrée est
`array{ libelle: string, valeur: string, affichage: string }`, **toujours présente**, où `libelle`
est le libellé public de MASTER §10.2 (`Hanches (HD)`, `Coudes (ED)`, `LTV`, `DM`, `SDCA 1`,
`SDCA 2`, `ADN identifié`, `Diversité génétique`, `TC`, `CSAU`, `Cotation LOF`, `Confirmation`,
`N° LOF`), et `affichage` vaut `Non renseigné` quand la valeur est vide.

**Clés de `sante`** : `hd` · `ed` · `ltv` · `dm` · `sdca1` · `sdca2` · `adn_identifie` ·
`diversite_genetique`. **Clés de `titres`** : `tc` · `csau` · `cotation_lof` · `confirmation` ·
`lof`. Un `foreach` naïf du thème est sûr : **toutes les entrées ont la même forme**.

`autres_tests` et `autres_titres` : `array` de chaînes, une par ligne non vide, **jamais** `null`.
Ils sont **au premier niveau du retour**, PAS dans `sante` / `titres`.

> *Correction du 2026-08-16, relevée par `dev-back-mtb` : la première rédaction de ce paragraphe les
> plaçait implicitement dans `sante`/`titres` tout en imposant que **chaque** entrée de ces tableaux
> soit `array{ libelle, valeur, affichage }`. Les deux ne pouvaient pas être vrais à la fois. Les
> sortir au premier niveau préserve l'uniformité de `sante`/`titres` — un `foreach` du thème ne peut
> pas tomber sur une entrée de forme différente, donc aucun avertissement PHP. `sante_renseignee`
> tient bien compte des **neuf** champs, `autres_tests` compris.*

`galerie` est stockée en base sous forme d'une **chaîne d'identifiants séparés par des virgules**
(forme `ids="1,2,3"` du cœur : une seule ligne en base, ordre préservé). Le contrat gèle la clé, pas
la forme du stockage. **À connaître par #3**, qui partage la racine `galerie` : ce n'est pas un
conflit — les types de contenu sont distincts — mais une divergence à connaître pour la reprise de
contenu.

## 6. États spéciaux

| État | Émis par le serveur | Rendu par le thème |
|---|---|---|
| `aucune_portee` | **non émis par cette issue** — il appartient à l'encart d'accueil de #3 | — |
| `donnee_absente` | `affichage === 'Non renseigné'` sur le champ concerné | Imprime le libellé fourni. **N'invente jamais un tiret**, ni « Aucun », ni « Néant », ni un blanc |
| `parent_hors_elevage` | `pere['type'] === 'hors_elevage'` : aucune fiche choisie, mais un nom libre saisi. Le retour porte `nom` et `elevage`, et **aucun lien** | Pas de lien ; **l'affichage ne change pas de forme**, la carte garde la même taille que celle d'un parent qui a une fiche (MASTER §9.4) |
| `page_protegee` | `etat === 'page_protegee'`, **aucun champ du domaine renseigné** | Encart de MASTER §9.5, aucun indice sur le contenu |
| *section non renseignée* | `sante_renseignee === false`, `titres_renseignes === false`, `galerie === array()` | **La section n'est pas rendue du tout.** La décision appartient au serveur |
| *groupe vide* | `mtb_get_chiens_par_statut()` **ne renvoie pas** le groupe | Rien à faire côté thème |

Trois valeurs possibles pour `pere['type']` / `mere['type']` : `fiche` · `hors_elevage` ·
`non_renseigne`. **Aucun identifiant d'état nouveau n'est créé par cette issue.**

## 6 bis. Libellés qui viennent du cœur de WordPress, pas de nous

Ces mots apparaissent à l'écran de Fabienne et **dans la fiche d'aide**, mais **aucun n'existe dans
le dépôt** : ils sont rendus par WordPress. Ils ne sont donc ni vérifiables par `grep`, ni
modifiables depuis cette issue.

| Libellé | Où | Remarque |
|---|---|---|
| **Publier** | bouton de la boîte de publication | |
| **Mettre à jour** | même bouton, sur une fiche déjà publiée | |
| **Mettre à la corbeille** | lien de la boîte de publication | |
| **Rétablir** | action d'une ligne dans l'onglet Corbeille | **restaure en brouillon**, pas en publié |

> **Le jour où une issue `admin` francisera ou renommera ces actions, c'est cette liste qu'il faut
> relire** — les trois fiches d'aide du lot les citent, et une fiche qui nomme un bouton disparu perd
> la confiance de sa lectrice sur tout le reste.

Deux libellés du cœur ont en revanche été **remplacés** par cette issue, parce qu'ils employaient des
mots interdits (§10.4) : la boîte **« Slug »** du cœur est retirée au profit d'une boîte
**« Adresse de la page »** portant le même champ `post_name` — on ne retire pas la capacité, on la
renomme — et **« Image mise en avant »** devient **« Photo principale »** dans les libellés du type.

**Leçon à porter par toute issue future qui enregistre un type de contenu public** : la boîte
« Slug » est **ajoutée par le cœur lui-même**, elle n'apparaît donc dans aucun fichier du dépôt et
**aucune relecture de code ne peut la détecter**. Elle ne se voit qu'à l'écran. C'est le premier
défaut livré par le trou « aucun rendu navigateur » de cette chaîne.

## 7. Chaînes fournies par le serveur

Le thème les **imprime** ; il ne les compose jamais.

Les quatre libellés de statut accordés · les titres de groupes au pluriel de « La meute » · les
libellés de dates accordés (`Né le`/`Née le`, `Décédé le`/`Décédée le`) · `Mâle`/`Femelle` ·
`Poil long`/`Poil court` · `Oui`/`Non` · tous les libellés publics de santé et de titres ·
les dates **formatées** selon les réglages du site (`affichage`), **en plus** de la valeur ISO brute
(`valeur`) · **`Non renseigné`** · les cinq libellés de cadrage · `Voir le pedigree` · les textes
alternatifs des photos.

## 8. Validation — et surtout ses limites

**Ce qui est validé :**
- l'auto-référence en filiation (un chien ne peut pas être son propre père ou sa propre mère) ;
- l'appartenance d'une valeur de liste fermée à sa liste ;
- l'URL de pedigree (`esc_url_raw`) ;
- la cohérence « date de décès postérieure à la date de naissance », **quand les deux sont
  remplies**.

**Ce qui n'est JAMAIS validé** — décision 12, et c'est une exigence, pas une négligence :

> Aucune valeur de HD, ED, LTV, DM, SDCA 1, SDCA 2, cotation LOF, diversité génétique, TC, CSAU,
> Confirmation ou N° LOF n'est vérifiée, normalisée, mise en majuscules, ni comparée à une grille.
> **Les grilles officielles ne sont nulle part dans le brief.** En inventer une ferait courir deux
> risques : une grille fausse, et une valeur réelle que Fabienne ne pourrait pas saisir — donc une
> éleveuse bloquée. Une validation trop zélée est un échec, pas une sécurité.

**Aucun champ n'est obligatoire.** Les messages d'erreur sont en français et commencent par
« Erreur : » (MASTER §10.3) — sauf l'avis de statut vide, qui est un **avertissement**, pas une
erreur.

**Ce que « la saisie survit au rechargement » recouvre exactement**, dit honnêtement plutôt que
promis en bloc :

- **Tenu** pour l'incohérence de dates (décès antérieur à la naissance) : **les deux valeurs sont
  conservées**, et l'avis demande la correction.
- **Non tenu** pour une date, une clé de liste fermée ou une URL **refusées** : le champ est
  enregistré **vide**, et l'avis le dit franchement (« Le champ a été laissé vide : ressaisissez-la »).

> *Arbitrage, relevé par `refacto-mtb` : conserver une valeur refusée supposerait de **stocker une
> donnée invalide**, ce qui déplacerait le problème dans la base au lieu de le régler à l'écran. Le
> chemin est de toute façon presque inatteignable depuis l'écran — les contrôles sont un
> `<input type="date">`, un `<input type="url">` et des radios, qui n'émettent pas de valeur hors
> liste sans manipulation délibérée. **La clause générale était trop large ; elle est resserrée ici
> plutôt que laissée à moitié fausse.***

**Avis non bloquant quand le statut est vide** : après enregistrement, un avis d'administration en
français prévient que le chien n'apparaîtra pas sur la page « La meute ». Elle publie quand même,
elle est prévenue immédiatement, et le rattrapage ne dépend d'aucune issue future.

### Le piège d'assainissement, nommément

`sanitize_text_field()` passe par `wp_strip_all_tags()`. Une valeur recopiée commençant par `<` —
par exemple **« <60% »** en diversité génétique — serait **silencieusement vidée**. C'est une
atteinte à D11 **par outillage**, pas par intention.

**Les champs recopiés ne passent donc pas par `sanitize_text_field()`.** Ils sont nettoyés en
préservant le contenu : suppression des caractères de contrôle et des sauts de ligne, découpage des
espaces de bord, puis **échappement au rendu** par `esc_html()`. Le `<` survit à la saisie et
s'affiche correctement. Contrat #1 §12 : assainissement **à la frontière d'entrée**, échappement
**au rendu**, jamais en amont.

### Le second piège : le dé-échappement double

> **La valeur qui atteint `update_post_meta()` doit être ENCORE échappée.**

`update_metadata()` applique `wp_unslash()` de son côté. Une valeur déjà dé-échappée par le code
appelant est donc dé-échappée **deux fois**, et **un antislash légitime disparaît à chaque
enregistrement** — `Rex\Test` devient `RexTest`. Comme le piège précédent, c'est une perte de donnée
d'élevage **par outillage**, silencieuse, sur le type qui porte le plus de valeurs recopiées.

Deux chemins corrects, et il faut en suivre **un jusqu'au bout** : soit ne jamais dé-échapper et
passer `$_POST` tel quel ; soit dé-échapper pour inspecter et assainir, **puis `wp_slash()` juste
avant l'écriture**. Prendre le début du second sans sa fin est le défaut exact qui a été livré dans
le commit `a2ab414` et corrigé ensuite.

**Toutes les écritures de méta passent par une fonction d'écriture unique** qui applique `wp_slash()`.
Ce n'est pas une redondance : la retirer réintroduit la perte. *(Le nonce et le code d'avis lu en
`$_GET` ne sont pas des écritures de méta — leur `wp_unslash()` est correct et reste.)*

### Avis par transient — justification

Les avis d'administration transitent par un **transient par utilisateur**, et non par un argument
d'URL. Motif : c'est le seul mécanisme qui permet de **citer à l'éleveuse la valeur qu'elle a
tapée**, au lieu d'un code opaque. Le pire mode de panne est un **avis manquant**, jamais une donnée
perdue. L'interdit de transient du contrat #1 §9 vise les **fonctions de lecture** — pour éviter
qu'une portée modifiée reste masquée derrière un cache périmé — et ne porte pas sur un message
d'interface à usage unique.

## 9. Interdits

- Le thème n'interroge jamais la base : aucun `WP_Query`, `get_post_meta`, `get_posts`, `get_terms`,
  ni `MTB\` dans `wp-content/themes/mtb/` (formulation `grep` du contrat #1 §8).
- Le thème ne compose jamais une chaîne du domaine : accorder un statut, écrire « Née le »,
  traduire `poil_long`, fabriquer un texte alternatif.
- Le thème ne reformate **jamais** une valeur de santé, un N° LOF, une cotation, une confirmation ou
  une diversité génétique — **ni n'ajoute une espace avant un `%`**.
- Le thème ne reformate jamais une date : `valeur` pour la machine, `affichage` pour l'œil.
- Le thème ne remplace jamais « Non renseigné » par un tiret, « Aucun », « Néant » ou un blanc.
- Le thème ne décide jamais qu'une section est vide, ni de l'ordre des groupes, ni de l'ordre
  interne.
- Le thème n'appelle jamais une fonction `mtb_*` sans `function_exists()`.
- L'extension n'émet **aucune** règle visuelle ni mise en page.
- Aucune remontée d'ascendance : **profondeur 1 stricte**, il n'existe aucun grand-parent ici.
- Aucune étape de build JavaScript, aucune dépendance, aucun appel HTTP sortant, aucune variable
  globale, aucune fonction i18n.

## 10. Arbitrages

| # | Désaccord | Décision | Raison |
|---|---|---|---|
| 1 | Écran de saisie : blocs, panneaux Gutenberg, ou formulaire classique | **Formulaire classique borné à `mtb_chien`** | Décision de lot, pour que les trois fiches se ressemblent et que le guide ne double pas. En panneaux Gutenberg, vingt champs dans une colonne de 280 px forment un accordéon — un champ jamais déplié est un champ jamais rempli — et le parcours se dégrade à 200 % de zoom. |
| 2 | Statut/Sexe/Variété en taxonomies ? | **Non, listes fermées en métas** | La métaboîte native de taxonomie est une liste de **cases à cocher, donc multi-sélection** : elle pourrait cocher « Reproducteur » **et** « Disparu », et l'accord de la décision 13 n'aurait plus de valeur unique à accorder. Elle affiche en plus « **+ Ajouter un nouveau Statut** » — la liste fermée cesserait de l'être. Et l'écran de gestion expose `slug` et `terme`, mots interdits (§10.4). |
| 3 | Palmarès et portées du chien : chez #4 ou chez les sœurs ? | **Chez #5 et #3. #4 ne déclare rien, il appelle sous garde** | Le type qui possède la donnée possède la lecture. Déclarer ici aurait **silencieusement ombré** la version des sœurs — `scandir()` parcourt par ordre alphabétique et `chien` passe avant `portee` et `resultat`. Aucune fatale, aucun journal : la panne la plus difficile à diagnostiquer du projet. *(Un mécanisme de filtres avait d'abord été retenu ; l'arbitrage du lead est meilleur — il supprime le problème au lieu de le contourner.)* |
| 4 | Tests de santé : huit champs plats ou tableau répétable ? | **Huit champs à clés gelées + une zone « Autres tests de santé »** | Un tableau répétable obligerait à retaper « HD » à chaque fiche, et la reprise des tests des parents par #3 deviendrait infiable — elle a besoin d'une clé stable, pas d'un libellé dépendant de la frappe. La zone libre tient la contrainte 1 pour un neuvième test. |
| 5 | « ADN identifié » en case à cocher ? | **Non — trois radios** | Une case a deux états, le domaine en a trois. Une case décochée par défaut affirmerait « Non » sur les 17 fiches : invention d'un fait d'élevage (D11). |
| 6 | TC, CSAU, Confirmation : oui/non ou texte ? | **Texte libre recopié** | Logique de la décision 12 étendue : un oui/non **perdrait** une mention réelle (« obtenu le … à … »), le texte ne perd rien. Le choix non destructif. |
| 7 | Libellé de la mère hors élevage | **« Mère — hors élevage »**, et « Père — étalon extérieur » conservé à l'identique | Reprendre « étalon extérieur » pour la mère serait un contresens : un étalon est un mâle. « hors élevage » reprend le vocabulaire de l'état gelé `parent_hors_elevage`, donc rien n'est inventé. Le libellé du père est gelé **littéralement** par §10.2 ; l'asymétrie est assumée, et MASTER pourra être amendé par la chaîne design. |
| 8 | Cinq libellés absents de MASTER §10.2 | « Autres tests de santé » · « Autres titres et brevets » · « Taille et robe » · « Né le » · « Non renseigné » en première option de liste | Aucun synonyme inventé : « Né le » est le masculin direct d'un libellé gelé et §10.2 exige déjà l'accord sur le décès ; « Taille et robe » n'emploie que des mots de §10.2 ; « Non renseigné » réemploie §10.3 plutôt que d'inventer un « — Choisir — ». |
| 9 | Fiche sans statut : elle disparaît de « La meute » | **Statut facultatif + avis non bloquant après enregistrement** | Un groupe sans titre exigerait un libellé inventé ; rendre le statut obligatoire contredirait « aucun champ obligatoire » ; dépendre d'une colonne d'administration reporterait le problème sur une issue future. L'avis prévient immédiatement et ne dépend de rien. |
| 10 | Ordre de « La meute » | Groupes `reproducteur` → `en_cours_de_confirmation` → `retraite` → `disparu` ; interne par date de naissance décroissante, non datés en fin, alphabétique en départage | Le visiteur qui cherche un chiot voit d'abord les chiens qui produisent. L'ordre interne est déterministe malgré une date facultative, et il est explicable. |
| 11 | `_mtb_lof` et `_mtb_confirmation` : à livrer ou non ? | **Livrés**, et la tension est remontée | Deux consignes de lot se tendent : « un terme de §10.2 absent de BRIEF §5.2 ne devient pas un champ », mais la table §10.2 énumérée pour le Chien inclut les deux. **Garder est la direction réversible** : un champ facultatif jamais rempli ne coûte rien et ne peut rien inventer ; un champ absent coûte une question bloquante en pleine reprise et une reprise de schéma sur 17 fiches. `CLAUDE.md` contrainte 4 exige en outre que les N° LOF soient migrés à l'identique. |

## 11. Points restés ouverts — ni comblés, ni oubliés

- **Dates de naissance partielles.** Un champ date force un jour. Si une fiche ancienne ne porte que
  l'année, le champ **reste vide** (il est facultatif et rien ne force jamais une valeur) et la
  lecture émet `donnee_absente`. **La question mord la chaîne de reprise de contenu, pas celle-ci** :
  à trancher avant l'epic 8, jamais en inventant un jour.
- **`includes/admin/` est hors empreinte.** La liste « Chiens » ne montrera que Titre et Date : ni
  photo, ni sexe, ni statut, ni filtre. Vivable à 17 fiches, mais ce n'est pas l'écran de travail
  promis. Trois relabellisations tombent aussi hors empreinte et emploient des **mots interdits par
  §10.4** : « Permalien » sur l'écran classique (§10.2 impose « Adresse de la page »), « Texte
  alternatif » dans la modale média (§10.2 impose « Description de la photo (pour les personnes
  aveugles) »), et le mot « Média » du menu. **À ouvrir en issue `admin`.**
- **`wp mtb import-fixtures` n'est livré par personne.** `includes/migration/` est hors des trois
  empreintes du lot, or `docker/provision/provision.sh` (lignes 129-138) attend sa signature. La
  fixture `Luna` (`"tests_sante": {}`), écrite exprès pour prouver D12, **ne sera donc pas
  importée** — le lot se termine sans cette preuve mécanique.
- **Locale du site.** `wp_date()` produit les noms de mois depuis le catalogue du cœur : si la stack
  tourne en `en_US`, `affichage` rendra « May 1, 2018 » sur un site français **sans qu'une seule
  ligne de ce module soit fautive**. À vérifier une fois au niveau du lot.
- **Les six sections sont rendues SOUS la zone de contenu** (contexte de métaboîte `normal`). C'est
  une atténuation de l'arbitrage 1, qui reprochait précisément à l'éditeur de blocs de reléguer les
  champs sous le canevas. Assumé : la zone de contenu classique est **une seule boîte courte**, sans
  commune mesure avec l'accordéon de vingt champs dans 280 px qu'on a écarté. Les remonter par
  `edit_form_after_title` est un détournement connu mais **fragile**, et le gain — passer une boîte
  au défilement — ne le justifie pas. **Rouvrable** si l'observation en Docker contredit ce jugement.
- **Les métas ne sont pas exposées dans les réponses REST**, bien que `show_in_rest` soit à `true` :
  le type ne déclare pas le support `custom-fields`, **volontairement**, parce que ce support ferait
  apparaître le panneau « Champs personnalisés » — mot interdit à l'écran (§10.4). Aucune erreur,
  aucun avertissement. À rouvrir le jour où un composant ou un import en ligne de commande en aura
  besoin.
- **Un identifiant de photo devenu mort reste en base** si l'éleveuse enregistre sans toucher à la
  galerie. **C'est délibéré et il ne faut pas le « corriger »** : ne pas supprimer une donnée qu'elle
  n'a pas demandé de supprimer est la règle de ce projet, et la lecture filtre déjà les
  identifiants qui ne désignent plus une photo. Si elle réimporte la photo, la galerie se rétablit
  seule.
- **Dettes T5 (`scandir`) et T6 (règles de réécriture) : intouchées**, elles appartiennent à #26 et
  à un travail `seo` ultérieur.
