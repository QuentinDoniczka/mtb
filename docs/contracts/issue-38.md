# Contrat d'interface — Issue #38 — Coordonnées de l'élevage, écran de réglages unique

**Gelé le 2026-08-18.** Labels `contact`, `infra` · milestone « 11. Dette technique ».

Cette issue ne touche que l'extension `mtb-core`. Aucun fichier du thème n'entre dans son empreinte.

---

## 0. Ce que l'issue ferme, en une phrase

L'adresse, le téléphone et le courriel de l'élevage étaient écrits **en dur dans deux modules**.
Changer le numéro était un travail de développeur sur deux fichiers — la règle d'or en échec sur une
donnée que `docs/BRIEF.md` §7 nomme explicitement. Après cette issue, **un seul littéral de chaque
valeur subsiste dans tout le dépôt** : celui des valeurs de départ de l'option, et il n'est plus lu dès
que l'éleveuse a enregistré l'écran une première fois.

---

## 1. Emplacement — deux modules, et pourquoi pas un seul

| Module | Rôle | Hooks |
|---|---|---|
| `includes/query/coordonnees/` | l'option, ses valeurs de départ, les trois fonctions de lecture | **aucun** |
| `includes/admin/coordonnees/` | l'écran et le traitement de son formulaire | `admin_menu`, `admin_post_…` |

Trois raisons, vérifiées et non supposées :

1. `includes/class-loader.php:24-32` définit les groupes **par leur hook** : `query → aucun hook,
   simples déclarations de fonctions` ; `admin → admin_menu, admin_init`. Les trois fonctions de
   lecture sont consommées sur la **façade publique** (`encart-appel/render.php:47`,
   `coordonnees-plan/bootstrap.php:108` à `init` 20), jamais en administration.
2. La convention existe déjà : `includes/query/chien/lecture.php`, `includes/query/portee/hydratation.php`.
3. **Le piège est documenté dans le dépôt.** `includes/admin/medias/bootstrap.php:25-35` consacre dix
   lignes au dégât que produit quelqu'un qui « range » un module du groupe `admin` derrière une garde
   `is_admin()`. Domicilier `mtb_get_telephone_elevage()` dans `admin/` armerait ce piège, et il
   **éteindrait le numéro sur tout le site public, en silence**.

`includes/fields/` est exclu **par la définition du groupe** : ses hooks sont `add_meta_boxes` et
`save_post_<type>`, or cette issue n'a aucun type de contenu.

Aucun index central n'est édité (décision 9) : deux dossiers, deux `bootstrap.php`, l'auto-découverte
fait le reste.

---

## 2. L'option

**Nom : `mtb_core_coordonnees`.** Seule convention de nom existante : `mtb_core_empreinte`
(`class-loader.php:220,243`). `autoload = true` — la valeur est lue sur toute requête publique portant
un composant, et une option non autochargée coûterait une requête par page.

```php
array(
    'adresse'      => string,  // retours à la ligne admis
    'telephone'    => string,
    'courriel'     => string,
    'page_contact' => int,     // identifiant de Page, 0 = aucune
)
```

### 2.1 Valeurs de départ — recopiées, jamais reformulées (D11)

| Clé | Valeur de départ | Source |
|---|---|---|
| `adresse` | `3060 Route de Salernes, 83570 Entrecasteaux` | `docs/BRIEF.md:166` |
| `telephone` | `0680505619` | `docs/BRIEF.md:167` |
| `courriel` | `mtbrabant@gmail.com` | `docs/BRIEF.md:167` |
| `page_contact` | `0` | aucune Page n'existe encore — voir §9 |

Le numéro est écrit **d'un seul tenant**, sans regroupement par paires, sans `+33`, sans zéro ajouté
ni retiré. Le groupage par paires de `encart-appel` est une **typographie d'affichage** (décision 38)
et ne remonte jamais dans le réglage.

### 2.2 La règle qui décide de tout : présence, pas vacuité

> **Une clé PRÉSENTE dans l'option stockée gagne, même vide. Une clé ABSENTE retombe sur la valeur de
> départ.** La lecture emploie `array_key_exists()`, **jamais** `isset()` ni `empty()`.

C'est la seule façon de distinguer « l'éleveuse a délibérément vidé son numéro » de « cette clé n'a
jamais été écrite ». Même raisonnement que la décision 21 sur les compteurs de mâles et de femelles.

Corollaire opérationnel, qui est le critère d'acceptation de l'issue :

> **`aucune_action` et `sans_telephone` deviennent atteignables parce que l'éleveuse vide un champ,
> jamais parce que l'installation est neuve.**

Conséquence directe : `get_option( 'mtb_core_coordonnees', $depart )` **seul ne suffit pas**. Le second
argument de `get_option()` n'est rendu que si la ligne d'option est **absente** ; il ne comble pas une
clé manquante d'un tableau existant. La lecture fait donc une **fusion clé par clé**, avec retypage de
chaque clé.

### 2.3 Option absente, corrompue, ou d'un autre type

| État en base | Ce que rend la lecture |
|---|---|
| ligne absente | les quatre valeurs de départ |
| valeur qui n'est pas un tableau (chaîne, entier, `null`, objet) | les quatre valeurs de départ |
| tableau auquel il manque des clés | les clés présentes ; les autres, valeur de départ |
| clé présente d'un type inattendu | retypée : `(string)` pour les trois textes, `absint()` pour `page_contact` ; un tableau ou un objet vaut la valeur de départ |
| clé présente et vide | **vide** — c'est un choix de l'éleveuse, jamais un défaut à combler |

Aucun de ces états ne lève d'erreur, n'écrit en base, ni ne casse une page (D12).

---

## 3. Fonctions de lecture exposées par l'extension

Déclarées **une seule fois**, en espace de noms global, chacune sous `if ( ! function_exists( … ) )`
— décision 19 : deux homonymes chargés par `scandir()` s'ombrent **sans lever d'erreur**, sur un site
qui répond 200.

### `mtb_get_telephone_elevage(): ?string`

**Zéro paramètre requis.** Signature gelée par `docs/contracts/issue-10.md` §1.1.

- Rend le numéro **tel qu'il est stocké**, jamais mis en forme.
- Rend **`null`** quand le réglage est vide.

> Le zéro paramètre requis n'est pas une élégance. Un paramètre obligatoire lèverait un
> `ArgumentCountError` **fatal** derrière les gardes `function_exists()` déjà en place, sur **toute**
> page portant un encart d'appel.

### `mtb_get_page_contact(): ?int`

**Zéro paramètre requis.** Signature gelée par `docs/contracts/issue-10.md` §1.1.

- Rend l'**identifiant de Page** enregistré. **Jamais une URL, jamais un titre, jamais un objet.**
- Rend **`null`** quand aucune page n'est choisie (`page_contact` à `0`).
- **Ne valide pas** que la page existe encore, est publiée, et n'est pas protégée par mot de passe :
  voir la frontière du §6.

### `mtb_get_coordonnees_elevage(): array`

La forme **enveloppe** de la décision 18, pour le thème.

```php
array(
    'adresse'   => array( 'libelle' => 'Adresse',   'valeur' => '…', 'affichage' => '…' ),
    'telephone' => array( 'libelle' => 'Téléphone', 'valeur' => '…', 'affichage' => '…' ),
    'courriel'  => array( 'libelle' => 'Courriel',  'valeur' => '…', 'affichage' => '…' ),
)
```

Les trois clés sont **toujours présentes**. `valeur` et `affichage` sont **toujours des chaînes**,
éventuellement vides. `page_contact` n'y figure pas : un identifiant n'est pas une valeur affichée, et
il n'entre dans aucune enveloppe.

**Deux règles fermes, opposables à toute divergence future :**

1. **`affichage` est identique à `valeur`, sur les trois champs.** Il ne porte **jamais** le groupage
   par paires de `encart-appel`. La décision 38 autorise un composant à fixer la **typographie** d'une
   donnée recopiée ; elle interdit de déplacer cette typographie dans le réglage, et l'imposer ici
   l'imposerait aussi à `coordonnees-plan`, qui a délibérément choisi de ne pas grouper.
   *Une chaîne future ne doit pas chercher la forme groupée dans `affichage` : elle n'y est pas, et
   elle n'y sera pas.*
2. **Quand la valeur est vide, `affichage` vaut la chaîne vide — jamais « Non renseigné ».**
   `MASTER.md` §9.3 réserve « Non renseigné » à « un champ de **fiche** non rempli ». Une coordonnée
   absente relève de la ligne voisine, « une section entière non remplie n'est pas rendue », et c'est
   la sémantique déjà ratifiée par la décision 37 : *vider un champ retire la ligne*. Écrire
   « Non renseigné » à côté de « Téléphone » dans un pied de page **affirmerait un non-fait sur
   l'éleveuse** — D11.

---

## 4. Les deux substitutions dans les composants existants

### 4.1 `blocks/encart-appel/rendu.php`

| Avant | Après |
|---|---|
| `const TELEPHONE_ELEVAGE = '0680505619';` (`:31`) et son bloc de commentaire | **supprimés** |
| `telephone_retenu()` : `return TELEPHONE_ELEVAGE;` (`:72`) | `return '';` |

Le traitement défensif du retour (`:59-69` — chaîne nue **ou** enveloppe, toute autre forme vaut
« rien ») est **conservé mot pour mot**. Il coûte six lignes et protège d'un `TypeError` sur toute page
portant un encart.

### 4.2 `blocks/coordonnees-plan/coordonnees.php`

| Avant | Après |
|---|---|
| `const TELEPHONE_ELEVAGE = '0680505619';` (`:46`) | **supprimée** |
| `coordonnees_elevage()` : littéraux d'adresse (`:63`) et de courriel (`:65`) | lus depuis `mtb_get_coordonnees_elevage()` sous `function_exists()`, `''` sinon |
| `telephone_elevage()` : `return TELEPHONE_ELEVAGE;` (`:110`) | `return '';` |

**Trois commentaires deviennent faux et sont amendés dans le même geste :**

- l'en-tête `:22-24` — « ne lit aucune option : tout y est appelable à tout moment, y compris avant
  `init` ». La lecture d'option est sûre avant `init` (l'accès base est disponible dès
  `wp-settings.php`), mais la phrase telle quelle devient mensongère ;
- l'en-tête `:5-17` — « REPLI LOCAL, PAS SOURCE DE VÉRITÉ » : il n'y a plus de repli local, il y a une
  lecture centrale ;
- le bloc de `coordonnees_elevage()` `:52-58` — « les trois clés sont toujours présentes et toujours
  des chaînes **non vides** ». Elles restent toujours présentes et toujours des chaînes ; elles
  peuvent désormais être **vides**.

**Ce qui ne change pas, et c'est délibéré** : `render.php`, `bootstrap.php`, `interface.php`,
`block.json` et `editeur.js` des deux modules ne sont **pas touchés**. Voir §5.

---

## 5. Le mécanisme de rétroactivité — à lire avant de le redécouvrir

Les deux composants héritent du réglage central **sans qu'aucune page soit rouverte, ni aucun bloc
réinséré**. Par deux chemins différents, et il faut connaître les deux.

**`encart-appel` — résolution au rendu.** `render.php:47` appelle `telephone_retenu()` à chaque rendu.
Rien à expliquer de plus.

**`coordonnees-plan` — défaut d'attribut recalculé à chaque requête.** Ce module n'a **aucun** repli au
rendu : `render.php:35-54` ne lit que `$attributes`. Sa rétroactivité vient de trois faits qui se
combinent :

1. `bootstrap.php:40` accroche `defauts_du_bloc()` au filtre du cœur `block_type_metadata` ;
   `bootstrap.php:108` réécrit `$metadata['attributes'][ $champ ]['default']` avec les valeurs de
   `coordonnees_elevage()`. Le filtre s'exécute **dans `register_block_type_from_metadata()`**, donc à
   `init` 20, **sur chaque requête** — public, administration, REST.
2. Le sérialiseur du cœur (`getCommentAttributes()`, `@wordpress/blocks`) **omet tout attribut égal à
   son défaut**. Un bloc que l'éleveuse n'a pas édité n'a donc **aucun** de ces trois attributs écrit
   dans `post_content`.
3. Au rendu, `WP_Block_Type::prepare_attributes_for_render()` **recharge les attributs manquants avec
   le défaut du moment**.

> **Conséquence** : faire lire la source centrale à `coordonnees_elevage()` suffit. Le changement est
> **rétroactif sur toutes les instances non surchargées**, y compris celles enregistrées avant cette
> issue.
>
> **Et donc : aucune bascule de ce composant vers une résolution au rendu n'est nécessaire.** Elle
> casserait la sémantique « vider un champ retire la ligne » ratifiée par la décision 37 et enseignée
> par `docs/guide/composant-coordonnees-plan.md:65`.

**Ce qui, à l'inverse, ne suit pas — et doit ne pas suivre** : une instance où l'éleveuse a réellement
**tapé** une valeur. Son attribut est sérialisé, il gagne, c'est la surcharge par page et elle doit
survivre. Idem pour du contenu forgé avec attributs explicites — un import, la reprise #19-#21 : à
porter au contrat de la reprise.

### 5.1 Correction d'une affirmation du contrat #11

`docs/contracts/issue-11.md` §17, sous « Un point que l'issue de réglages devra trancher », affirme
qu'un numéro central « ne touchera que les blocs **nouvellement insérés**, jamais les pages déjà
enregistrées, dont les attributs sont sérialisés dans `post_content` ».

**C'est faux.** La prémisse confond deux enregistrements : les défauts sont injectés à
l'**enregistrement du bloc** (`register_block_type`, à chaque requête), pas à l'**enregistrement de la
page**. Et les attributs non édités ne sont précisément **pas** sérialisés dans `post_content`.

La question que ce §17 laissait ouverte — « l'issue de réglages devra décider si ce composant passe lui
aussi à une résolution au rendu » — **est close par la négative** : il n'a pas à y passer.

---

## 6. Frontière de responsabilité sur la page de contact

`mtb_get_page_contact()` rend l'identifiant **enregistré**, sans le valider.

La validation existe déjà, et elle est plus exigeante que ce qu'un réglage peut savoir :
`encart-appel/rendu.php` → `page_utilisable()` vérifie **quatre** conditions (type `page`, statut
`publish`, aucun mot de passe, permalien non vide) et rend `0` si l'une manque.
**Elle n'est pas dupliquée ici.**

Motif : la validité d'une page dépend du moment et du contexte du rendu, pas du réglage. Un réglage qui
« corrigerait » l'identifiant remplacerait en silence le choix de l'éleveuse — ce que
`docs/contracts/issue-10.md` §1.3 interdit explicitement.

Conséquence si la page choisie est supprimée, dépubliée ou protégée : `mtb_get_page_contact()` rend
toujours son identifiant, `page_utilisable()` rend `0`, **aucun bouton n'est émis**, et rien d'autre ne
bouge dans l'encart. Jamais de bouton mort, jamais de `href="#"` (D12).

---

## 7. Écran de réglages

### 7.1 Capacité — le point qui décide si l'issue est faite

> **Capacité requise : `edit_pages`. Jamais `manage_options`.**

Fabienne est **Éditeur natif** (compte de développement `fabienne`), pas Administrateur. Le rôle
Éditeur possède `edit_pages`, `edit_others_pages`, `publish_pages`, `edit_posts`, `upload_files`. Il ne
possède **ni `manage_options`, ni `edit_theme_options`** — la dette **T6** de `docs/ETAT.md` le
confirme sur un autre écran.

Un écran sous `manage_options` lui serait **invisible**, sur un site qui répond 200, et personne ne le
verrait tant qu'on testerait en `admin`. C'est la règle d'or : si elle ne peut pas ouvrir l'écran,
l'issue n'est pas faite.

`edit_pages` sépare proprement : Éditeur et Administrateur l'ont, Auteur et Contributeur ne l'ont pas.
Et elle dit quelque chose de juste — *la personne qui tient les pages du site tient les coordonnées du
site*.

**Menu de premier niveau**, pas `add_options_page`. Ce dernier ferait apparaître à Fabienne un menu
« Réglages » ne contenant **qu'une seule entrée**, dont le nom promet dix écrans. Position **24** : les
trois types de contenu occupent 21, 22 et 23 (`content/portee/bootstrap.php:37`,
`chien/bootstrap.php:62`, `resultat/bootstrap.php:60`).

**Formulaire écrit à la main**, pas la Settings API. Trois raisons : la Settings API poste vers
`wp-admin/options.php`, qui exige `manage_options` et n'accepte l'Éditeur qu'au prix du filtre
`option_page_capability_{$groupe}` — correct mais peu lu, et dont le suffixe est le *groupe d'options*,
pas le nom de l'option, ce qui en fait une panne muette classique ; elle impose son propre vocabulaire
d'avis ; et c'est le **premier écran d'administration du projet**, donc celui qui pose la grammaire des
suivants (T1, T6).

### 7.2 Sécurité

- Nonce sur l'écriture, vérifié avant toute lecture de `$_POST`.
- `current_user_can( 'edit_pages' )` vérifié **deux fois** : à l'affichage du menu et **à nouveau** dans
  le traitement du formulaire. Un utilisateur sans la capacité qui poste directement sur
  `admin-post.php` reçoit un `wp_die` en **403**, et rien n'est écrit.
- Aucune requête SQL directe : API Options uniquement, donc pas de `$wpdb->prepare` à écrire.
- Échappement systématique en sortie : `esc_attr`, `esc_html`, `esc_textarea`.
- Redirection après enregistrement (motif *POST/Redirect/GET*), avis en `role="status"`.

### 7.3 Assainissement — décision 20, et une quatrième copie assumée

**Interdits sur ces valeurs recopiées** : `sanitize_text_field`, `wp_strip_all_tags`, `wp_kses`,
`sanitize_email`. Toutes passent par `strip_tags()` : une valeur commençant par `<` serait **vidée en
silence**. C'est D11 enfreinte par l'outillage.

Le module écrit son propre assainisseur : **UTF-8 invalide et caractères de contrôle seulement**.
Sémantique reprise de la majorité existante (`content/portee/champs.php:236`,
`content/resultat/assainissement.php:48-50`) : les caractères de contrôle sont **supprimés**, et
l'encodage est contrôlé — et non celle de `content/chien/assainissement.php:51`, qui les remplace par
une espace et ne contrôle pas l'encodage.

`adresse` admet les **retours à la ligne** (`\r\n` normalisés en `\n`) ; `telephone` et `courriel` sont
sur une seule ligne.

**Cela crée une quatrième copie de `assainir_texte_recopie()` — dette T9, aggravée en connaissance de
cause.** Les empreintes disjointes interdisaient un fichier partagé ; ici l'obstacle est le périmètre
de l'issue. À hisser dans un module commun **avant la reprise #19-#21**, comme T9 le dit déjà.

### 7.4 Libellés

| Élément | Libellé exact |
|---|---|
| Titre de l'écran | **Coordonnées de l'élevage** |
| Entrée de menu | **Coordonnées** |
| Champ 1 | **Adresse** |
| Champ 2 | **Téléphone** |
| Champ 3 | **Courriel** |
| Champ 4 | **Page de contact** |
| Bouton | **Enregistrer** |
| Avis de succès | **Coordonnées enregistrées.** |
| Choix vide de la page | **Aucune** |

Les trois premiers sont **repris verbatim** de ce que `coordonnees-plan` affiche déjà à l'éleveuse
(`coordonnees-plan/editeur.js:248,259,269`, contrat #11 §7) et que `docs/guide/composant-coordonnees-plan.md:57-59`
lui a déjà enseigné. Zéro vocabulaire nouveau là où un mot existe déjà à l'écran (décision 39).

**L'aide de chaque champ doit dire la différence entre les deux niveaux** — c'est la phrase la plus
délicate de l'issue : *ce que vous écrivez ici s'applique à tout le site ; le champ Téléphone d'un
composant ne change que la page où il est posé.* Et pour le vide : *laissez vide pour retirer cette
information de tout le site.*

---

## 8. États spéciaux

| État | Émis par le serveur | Rendu / effet |
|---|---|---|
| `donnee_absente` (un champ vidé) | `valeur` et `affichage` à `''` ; `mtb_get_telephone_elevage()` rend `null` | la ligne ou l'élément **disparaît** ; jamais « Non renseigné », jamais un trou |
| `aucune_page_contact` | `mtb_get_page_contact()` rend `null` | `encart-appel` n'émet **aucun bouton** ; le reste de l'encart est inchangé |
| `page_contact_invalide` | l'identifiant est rendu tel quel | `page_utilisable()` rend `0` → traité comme `sans_bouton` |
| `option_absente` | les quatre valeurs de départ | rendu identique à celui d'avant l'issue, à l'octet près |
| `option_corrompue` | les quatre valeurs de départ | idem ; aucune erreur, aucune écriture |
| `module_query_absent` | `function_exists()` faux partout | `encart-appel` rend `''`, `coordonnees_elevage()` rend trois chaînes vides → **les deux composants ne s'affichent pas** au visiteur, état vide dans l'éditeur |
| `sans_telephone` (#10 §6) | **désormais atteignable** | l'élément `__telephone` n'est pas émis |
| `aucune_action` (#10 §6) | **désormais atteignable** | rien du tout au visiteur ; cadre d'état vide dans l'éditeur seul |

---

## 9. Amendement du contrat #10 §1.2 — trois crans deviennent deux

`docs/contracts/issue-10.md` §1.2 gèle la chaîne de résolution du téléphone. **Clause remplacée**,
citée verbatim :

> **Téléphone**, dans cet ordre, le premier cran non vide gagne :
> 1. attribut d'instance `telephone`, `trim()` non vide → **il gagne, tel quel** ;
> 2. `mtb_get_telephone_elevage()` sous `function_exists()` ;
> 3. constante d'espace de noms du module `TELEPHONE_ELEVAGE = '0680505619'` — **cas d'aujourd'hui**.

**Texte de remplacement, en vigueur à partir de cette issue :**

> **Téléphone**, dans cet ordre, le premier cran non vide gagne :
> 1. attribut d'instance `telephone`, `trim()` non vide → **il gagne, tel quel** ;
> 2. `mtb_get_telephone_elevage()` sous `function_exists()` — **source d'autorité, y compris quand
>    elle rend vide**.
>
> Il n'y a **pas de troisième cran**. La constante `TELEPHONE_ELEVAGE` est supprimée.

**Raison de l'amendement.** Le troisième cran rendait les états `aucune_action` et `sans_telephone` de
`issue-10.md` §6 **structurellement inatteignables** : `telephone_retenu()` retombait sur la constante
même lorsque la fonction centrale existait et rendait vide (`rendu.php:63-72`). L'amendement 4 du
contrat #10, ratifié le 2026-08-18, l'avait constaté sans pouvoir le corriger. Le conserver aurait
livré un réglage central qu'une constante contredisait en dernier ressort — c'est-à-dire le défaut même
que #38 existe pour fermer.

**Risque déplacé, et il est assumé** : sans constante, une option manquante retire le numéro **partout
à la fois** au lieu de ne le retirer nulle part. La §2.2 est ce qui rend ce risque acceptable, et le
§11 est ce qui le vérifie.

---

## 10. Ce que #18 devra appeler — section écrite pour la chaîne suivante

**Lire cette section avant d'écrire le pied de page.**

Le thème `mtb` est un **thème de blocs** : `wp-content/themes/mtb/parts/` et `patterns/` sont
**vides** aujourd'hui, et `parts/footer.html` sera du **balisage de blocs, qui ne peut appeler aucune
fonction PHP**.

**Ce qu'il faut poser dans `parts/footer.html`, et c'est tout :**

```html
<!-- wp:mtb/coordonnees-plan /-->
```

Une instance **nue, sans aucun attribut**. Les trois attributs manquants sont remplis au rendu par le
défaut recalculé à chaque requête (§5), donc le pied de page affiche les coordonnées centrales et
**suit chaque changement** de l'écran de réglages. Zéro PHP, zéro recopie, zéro page à rouvrir.

**Le piège à ne pas tomber dedans.** Un `patterns/*.php` de thème peut exécuter du PHP, mais son
résultat est du balisage de blocs **figé au moment de l'insertion**. Y écrire
`<?php echo mtb_get_telephone_elevage(); ?>` gèlerait la valeur dans la page enregistrée et
**recréerait exactement le défaut de recopie que #38 supprime**. Ne le faites pas.

**`mtb_coordonnees_plan_rendu()` n'est pas la bonne porte.** Le contrat #11 §2 la présente comme
« exactement ce dont un pied de page a besoin » : c'est vrai d'un gabarit **PHP classique**, et ce thème
n'en a pas. Elle reste utile à un appelant PHP (un futur `render_callback`, un module de l'extension),
pas à `parts/footer.html`.

**Si #18 veut des valeurs brutes** — pour un balisage qui n'est pas celui du composant — il appelle
`mtb_get_coordonnees_elevage()` (§3), qui rend l'enveloppe de la décision 18, et
`mtb_get_page_contact()` pour le lien vers la page de contact. Mais voir le manque ci-dessous.

### 10.1 Manque signalé, non résolu par cette issue

Si le pied de page doit porter un **balisage différent** de la liste de définitions de
`coordonnees-plan` — ce que `MASTER.md` §7.3 laisse entendre —, ce balisage appartient à `mtb-core` et
**non au thème** (frontière de `CLAUDE.md` : le thème ne contient aucune règle métier et n'interroge
jamais la base). Ce serait donc un **composant neuf**, hors du périmètre de #18 **et** hors de celui de
#38. À router par l'orchestrateur avant de lancer #18.

---

## 11. Vérifications à exécuter avant de clore — observées, pas déduites

1. **Le test « sans ligne d'option ».** Sur une base où `mtb_core_coordonnees` n'existe pas, une page
   portant un encart d'appel doit afficher `06 80 50 56 19`, exactement comme avant l'issue. C'est ce
   qui prouve que la suppression des constantes n'a pas vidé le numéro d'une installation neuve.
   WP-CLI par `make wp`, jamais par un `docker compose run wpcli` nu (décision 34).
2. **La capacité, en session `fabienne`** — pas en `admin`. L'entrée de menu visible, l'écran ouvrable,
   l'enregistrement qui aboutit. Une table de capacités lue dans les sources est une déduction ; la
   règle d'or n'est tenue que par une observation.
3. **Le contrôle négatif** : un compte sans `edit_pages` qui poste sur `admin-post.php` reçoit un 403
   et n'écrit rien.
4. **La rétroactivité**, sur une instance de `coordonnees-plan` enregistrée **avant** le changement de
   réglage : le `post_content` ne doit porter aucun des trois attributs, et la page rendue doit montrer
   la nouvelle valeur.

---

## 12. Interdits

- Le thème n'interroge jamais la base directement ; il appelle les fonctions de lecture.
- Le thème ne compose jamais une chaîne métier ni ne reformate une coordonnée.
- L'extension n'émet aucune règle visuelle ni mise en page depuis cette issue.
- **Aucune valeur d'élevage n'est reformulée.** Le numéro reste `0680505619` en base. Aucun `+33`,
  aucun groupage, aucun zéro ajouté ni retiré.
- Le groupage par paires de `encart-appel` ne remonte **jamais** dans le réglage ni dans `affichage`.
- Aucun index central n'est édité ; aucun fichier du thème n'est touché.
- Aucun second champ « téléphone » n'est ajouté « au cas où » — voir §14.

---

## 13. Arbitrages

| # | Désaccord | Décision | Raison |
|---|---|---|---|
| 1 | Forme du retour : `?string` nu ou enveloppe de la décision 18 ? | **Les deux, sur deux fonctions distinctes** | Les deux consommateurs actuels sont des modules **PHP de l'extension**, qui ont besoin de la valeur brute pour leur propre mise en forme ; le thème, lui, reçoit l'enveloppe. La signature gelée de `mtb_get_telephone_elevage()` est respectée sans que la décision 18 soit contournée. |
| 2 | `affichage` sur une valeur vide : « Non renseigné » ou chaîne vide ? | **chaîne vide** | Voir §3, règle 2. Borne à la décision 18, de la même famille que ses deux exceptions déjà écrites (élevage d'un parent extérieur, pays d'un résultat). |
| 3 | `coordonnees-plan` doit-il passer à une résolution au rendu (question laissée ouverte par #11 §17) ? | **non** | §5. Le mécanisme de défaut est déjà rétroactif, et y toucher casserait la sémantique « vider retire la ligne » (décision 37). |
| 4 | Un ou deux modules ? | **deux** | §1. Le troisième argument — le piège `is_admin()` documenté dans `admin/medias/bootstrap.php` — est décisif. |
| 5 | Settings API ou formulaire à la main ? | **formulaire à la main** | §7.1. `options.php` exige `manage_options` et n'accepte l'Éditeur qu'au prix d'un filtre peu lu. |
| 6 | L'écran refuse-t-il un champ vide ? | **non, il l'accepte** | La règle d'or. Vider est un acte légitime, et c'est la seule route vers `sans_telephone` en production. D12 tient : la ligne disparaît, la page ne casse pas. |
| 7 | Valider la page de contact dans le réglage ? | **non** | §6. La validation existe déjà et dépend du contexte de rendu. |

---

## 14. Questions de domaine — remontées, jamais comblées

1. **L'élevage a-t-il un second numéro** (fixe, second portable) ?
   `docs/guide/composant-encart-appel.md:69-71` enseigne déjà de mettre « le second dans la Phrase
   d'accroche », ce qui suppose qu'il puisse y en avoir deux — mais **aucune source du projet n'en
   atteste un**. Le modèle livre **un** champ Téléphone. Ajouter un second champ « au cas où »
   inventerait un fait d'élevage **par la structure**. À poser à l'éleveuse.
2. **Quelle Page est « la page de contact » ?**
   Aucune Page n'existe encore. Le réglage part donc sur **« Aucune »**, `mtb_get_page_contact()` rend
   `null`, et `encart-appel` n'émet simplement pas de bouton — comportement déjà décrit par
   `docs/guide/composant-encart-appel.md:79-81`. **Non bloquant pour la livraison.**

---

## 15. Contrats amendés et dettes

| Document | Amendement |
|---|---|
| `docs/contracts/issue-10.md` §1.2 | trois crans → **deux** ; la constante disparaît (§9 ci-dessus) |
| `docs/contracts/issue-10.md` §6 + amendement 4 | `aucune_action` et `sans_telephone` deviennent **atteignables** |
| `docs/contracts/issue-11.md` §17 | l'affirmation de non-rétroactivité est **fausse** ; la question laissée ouverte est **close par la négative** (§5.1) |
| `docs/contracts/issue-11.md` §2 | `mtb_coordonnees_plan_rendu()` n'est **pas** ce dont ce pied de page a besoin (§10) |
| Dette **T19** (#11) | close autrement que prévu : la table n'est pas hissée, elle est **remplacée** par l'option centrale |
| Dette **T9** | **aggravée** : quatrième copie de l'assainisseur (§7.3) |

---

## 16. Lacune de vocabulaire — pour `lead-design-mtb`

**`MASTER.md` §10 ne fige aucun des quatre libellés dont cet écran a besoin.**

| Mot | Dans §10 ? |
|---|---|
| adresse postale | **non** — §10.2 porte « Adresse de la page », qui est l'**adresse web** d'une page : quasi-homonyme dangereux |
| téléphone | **non** — le mot n'apparaît dans `MASTER.md` que pour désigner l'appareil |
| courriel | **non** — zéro occurrence |
| page de contact | **non** — §10.3 ne fige que le libellé public « Nous contacter » |

Seul ancrage existant : **§7.3**, qui nomme « coordonnées de l'élevage » et **recopie les trois
valeurs** pour le pied de page — soit un quatrième exemplaire du numéro dans le dépôt, en documentation.

La décision 39 interdit à une chaîne d'amender le système de design. Les libellés du §7.4 sont donc
**repris de ce qui est déjà à l'écran**, et l'ajout de ces lignes au §10.2 revient à
`lead-design-mtb`. La suppression du numéro recopié en §7.3 lui revient aussi.
