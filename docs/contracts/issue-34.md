# Contrat d'interface — Issue #34 — Déplacer toute la feuille de la galerie vers le thème (T15)

**Gelé le 1er septembre 2026**, lot 15, chaîne unique (l'issue exige « hors lot parallèle »).
Base : `origin/main` à `4d95429`.

Ce contrat réconcilie deux plans écrits à l'aveugle l'un de l'autre. Il est **contraignant** à partir
d'ici : `dev-back-mtb` et `dev-ux-mtb` l'appliquent, `refacto-mtb` et `dev-integration-mtb` le
vérifient, `review-mtb` l'audite.

---

## 0. Ce que fait cette issue, en une phrase

`includes/blocks/galerie-photos/galerie.css` — **6 règles, 6 sélecteurs, 31 déclarations, 100 %
visuelles**, en dérogation nommée au contrat #1 §8 — est **supprimée**, son contenu part **en entier**
dans `themes/mtb/assets/css/blocs/mtb-galerie-photos.css`, et le chemin `"style"` est débranché.

**Rien ne change à l'écran.** Ni sur la page publique, ni dans la toile de l'éditeur, ni dans
l'insérteur, ni pour l'éleveuse. Le balisage ne bouge pas d'un octet.

---

## 1. La mesure qui autorise cette issue — faite avant tout travail, pas déduite

Le dépôt portait **deux mécaniques contradictoires** sur « une feuille du thème atteint-elle la toile
de l'éditeur ? » :

- `docs/contracts/issue-7.md:576-582` répond **non**, par **lecture** du cœur : `wp_enqueue_block_style()`
  accroche son rappel à `render_block`, que `_wp_get_iframed_editor_assets()` ne déclenche jamais ;
- `docs/contracts/issue-6.md:627` (T11) répond **oui**, par **sonde** : le rappel *s'exécute bien* dans
  la toile ; la vraie cause de la panne était `mtb-jetons` non enregistré en administration, et
  `WP_Dependencies::all_deps()` qui abandonne l'élément **en silence**.

**Ce n'est pas une contradiction : `issue-7.md` §10 est une prédiction, `issue-6.md` T11 est la mesure
qui l'a réfutée et qui a nommé la vraie cause.** Le correctif d'une ligne vit aujourd'hui à
`themes/mtb/functions.php:229-235`. `issue-8.md:1267-1270` (§20.5) en tire la conclusion : T15 n'est
plus une dette technique mais **une dette d'alignement**.

**Mesuré le 1er septembre 2026 sur la pile vivante** (port 3005, admin connecté, éditeur de la page 6) :

```
id='mtb-bloc-mtb-bandeau-alerte-css'       2      id='mtb-bloc-mtb-fiche-information-css'   2
id='mtb-bloc-mtb-bandeau-ouverture-css'    2      id='mtb-bloc-mtb-formulaire-contact-css'  2
id='mtb-bloc-mtb-coordonnees-plan-css'     2      id='mtb-bloc-mtb-grille-chiens-css'       2
id='mtb-bloc-mtb-derniere-portee-css'      2      id='mtb-bloc-mtb-liste-portees-css'       2
id='mtb-bloc-mtb-encart-appel-css'         2      id='mtb-bloc-mtb-tableau-resultats-css'   2
id='mtb-galerie-photos-style-css'          2   ← la galerie, servie par l'extension
```

**Les dix feuilles sœurs du thème atteignent la toile aujourd'hui.** La galerie est la seule des onze à
ne pas emprunter ce chemin. **Le déplacement ne prive la toile de rien**, et cette phrase est un
constat, pas une citation de contrat.

**Ce que cette mesure ne prouve pas** : que l'éleveuse *voit* la grille. La présence d'une poignée dans
le payload de la toile n'est pas un rendu. Aucun navigateur n'a rendu ce site (`ETAT.md:92-94`) et #34
ne change pas cela.

---

## 2. Baseline gelée — l'état d'avant, à retrouver à l'identique après

Mesurée avant tout travail, le 1er septembre 2026.

| Témoin | Avant | Après (attendu) |
|---|---|---|
| `/chien/jango/` — `mtb-galerie-photos-style-css` | **1** | **0** |
| `/chien/jango/` — `mtb-bloc-mtb-galerie-photos(-inline)?-css` | 0 | **1** (une seule des deux formes) |
| `/chien/jango/` — crochets rendus | 1 `__grille`, 7 `__lien`, 7 `__image`, 7 `__rang` | **identique** |
| Éditeur page 6 — `mtb-galerie-photos-style-css` | 2 | **0** |
| Éditeur page 6 — `mtb-bloc-mtb-galerie-photos-css` | 0 | **2** |
| Éditeur page 6 — chaque feuille sœur | 2 | **2, inchangé** |

Les deux occurrences en contexte éditeur sont normales et attendues : **payload de la toile** (échappé
en `<`) **plus** page éditeur. C'est le régime des dix sœurs.

---

## 3. Fonctions de lecture exposées par l'extension

**Aucune n'est créée, modifiée ni supprimée.** #34 ne touche ni type de contenu, ni méta, ni option, ni
requête. La surface globale de l'extension reste close (contrat #8 §1.1) : la seule voie du thème reste

```php
render_block( array( 'blockName' => 'mtb/galerie-photos', 'attrs' => array( 'photos' => [int…] ) ) )
```

`themes/mtb/enveloppe-fiche.php` — **inchangé par #34**.

---

## 4. Blocs enregistrés

`mtb/galerie-photos` **reste enregistré à l'identique**, à un champ de manifeste près.

| Clé de `block.json` | Avant | Après |
|---|---|---|
| `apiVersion`, `name`, `title`, `category`, `icon`, `description`, `keywords`, `attributes`, `supports`, `render` | — | **inchangées, à l'octet** |
| `editorScript` | `mtb-galerie-photos-editeur` | **inchangée** |
| `editorStyle` | `mtb-galerie-photos-editeur-style` | **inchangée** |
| `style` | `mtb-galerie-photos-style` | **RETIRÉE** |

Après retrait, `WP_Block_Type::$style_handles` de `mtb/galerie-photos` vaut **`array()`**.

**Balisage — gelé par le contrat #8 §4, non rouvert, pas un octet ne change :**

```
div.mtb-galerie-photos[data-mtb-total]
└ ul.mtb-galerie-photos__grille[role="list"]
  └ li.mtb-galerie-photos__element
    └ a.mtb-galerie-photos__lien[href][data-mtb-photo][data-mtb-rang]
      ├ img.mtb-galerie-photos__image
      └ span.mtb-galerie-photos__rang  → « Photo N sur T »
```

`role="list"` **reste obligatoire** : la feuille retire `list-style`, et Safari retirerait alors la
sémantique de liste. `supports.align` **reste `false`** — voir §8, point 3 : c'est ce qui garantit
l'analyse de cascade.

---

## 5. Où vit la feuille, désormais

| Objet | Valeur gelée |
|---|---|
| Fichier | `wp-content/themes/mtb/assets/css/blocs/mtb-galerie-photos.css` — **ce nom exactement** |
| Artefact | `…/blocs/mtb-galerie-photos.min.css`, produit par `make css`, **commité dans le même commit** |
| Poignée | `mtb-bloc-mtb-galerie-photos` |
| Dépendance | `mtb-jetons`, et elle seule |
| Mise en file | `mtb_feuilles_de_blocs()`, `themes/mtb/functions.php`, sur `wp_loaded` — **rien à déclarer nulle part** |
| Portée | visiteur **et** toile de l'éditeur |
| Contenu | les **6 règles / 31 déclarations**, à la déclaration près |
| Version | empreinte de contenu via `mtb_feuille_a_servir()` — **plus `MTB_CORE_VERSION`** |

**Le nom est la seule chose qui décide, et il échoue en silence.** Le chargeur dérive
`str_replace( '/', '-', 'mtb/galerie-photos' )`. `galerie.css`, `galerie-photos.css`, `mtb-galerie.css`
→ `continue` sur `file_exists()`, **aucune erreur, aucun avertissement, page en 200, galerie nue**.

**Gain de version, à écrire et non à taire** : `MTB_CORE_VERSION` vaut `'0.1.0'` et `mtb-core.php` est
**interdit à toute issue** (contrat #1 §13 l. 498-500) — personne ne peut l'incrémenter. La feuille
était donc servie sous une version **gelée à jamais**, et un visiteur revenant pouvait être servi une
feuille périmée par son cache. Après le déplacement, la version suit le contenu. **#34 répare un défaut
de cache au passage.**

---

## 6. États spéciaux

| État | Émis par le serveur | Rendu par le thème |
|---|---|---|
| `aucune_portee` | inchangé | inchangé |
| `donnee_absente` | inchangé | inchangé |
| `parent_hors_elevage` | inchangé | inchangé |
| `page_protegee` | inchangé | inchangé |
| **galerie sans photo affichable** | **zéro octet** — ni enrobage, ni `<ul>` vide | **rien à styler**, et rien à ajouter |

**#34 ne crée, ne modifie et ne supprime aucun état.**

---

## 7. Chaînes fournies par le serveur

Une seule, inchangée : **`Photo N sur T`**, composée au rendu, échappée par `esc_html`. Le thème ne la
compose jamais, ne la traduit pas, et **doit continuer de la masquer sans la retirer de l'arbre
d'accessibilité** — ni `display:none`, ni `visibility:hidden`, ni `overflow:hidden` (MASTER §7.6, §7.8).

---

## 8. Cascade — pourquoi le déplacement est sûr, et ce qui le rendrait faux

**Les six règles sont hors d'atteinte de l'ordre de source.** Chacune est à (0,2,0) ou (0,2,1) ; chaque
règle de `base.css` qu'elles disputent est strictement moins spécifique, **dans les deux contextes**.
C'est de l'arithmétique de sélecteurs, pas une observation.

| Point disputé | Poids `base.css` | Poids galerie | Verdict |
|---|---|---|---|
| `:focus-visible` (anneau) | (0,1,0) | `__lien` = (0,2,0) et **ne déclare ni `border-radius` ni `box-shadow`** | sûr — l'anneau s'applique **parce que la galerie se tait** |
| marges du canal, `!important` | (0,3,0) | `__grille` = (0,2,0) | sûr — **aucun conflit** : le `<ul>` n'est jamais enfant direct de `.mtb-canal` |
| `.mtb-canal > *` | (0,1,0) | section 1 = (0,2,0) | sûr |
| `img, picture, video { block-size: auto }` | (0,0,1) site / **(0,1,1) toile réécrite** | `__image` = (0,2,0) | sûr **dans les deux contextes** |
| soulignement de `a`, marges de `ul` | (0,0,1) | (0,2,0) | sûr |

**Conclusion gelée : aucune des 31 déclarations ne dépend de l'ordre d'impression.** Le déplacement est
**cascade-neutre par construction** — et il l'est parce que le contrat #8 §19.1 a payé ce prix six lots
plus tôt.

**Les quatre choses qui rendraient cette analyse fausse — interdits durs :**

1. **Descendre un sélecteur à une seule classe.** C'est ce qui rendrait la feuille dépendante d'un ordre
   que personne n'a mesuré. Les six sélecteurs restent à deux classes.
2. **Passer la feuille à `add_editor_style()`.** Le cœur **réécrit** ces feuilles en préfixant tout
   sélecteur non racine de `.editor-styles-wrapper` : chaque sélecteur gagnerait une classe, toute la
   course de spécificité serait à refaire, et `.mtb-canal > .mtb-galerie-photos` deviendrait un
   sélecteur qui ne décrit pas la structure de la toile. **La feuille est servie par
   `wp_enqueue_block_style()` via `mtb_feuilles_de_blocs()`, et par rien d'autre.**
3. **Rendre `supports.align` autre chose que `false`.** `.mtb-canal > .alignwide` est aussi à (0,2,0) :
   si la racine pouvait porter les deux classes, il y aurait **égalité**, donc recours à l'ordre.
4. **Réintroduire un `border-radius` sur `__lien`.** `--r-0` vaut `0`, la déclaration n'aurait aucun
   effet propre — mais à (0,2,0) elle **battrait** le `var(--r-1)` du `:focus-visible`, et l'anneau
   perdrait ses coins de 2 px sur une vignette. **Silencieux tant que personne ne tabule sur une photo.**

**Le seul risque neuf que le déplacement introduit** : la dépendance `mtb-jetons`. La galerie n'avait
**jamais** eu de `deps` (`array()`). Si `mtb-jetons` n'est pas enregistré, `WP_Dependencies::all_deps()`
**abandonne l'élément entier** — grille, ratio, fond, cerne et cadrage perdus, page en 200,
`debug.log` vide. C'est la panne exacte qui emportait les dix composants avant #6, couverte sur le site
par `mtb_feuilles_du_site()` et en administration par `functions.php:229-235`. **À écrire dans le
docbloc, à vérifier en §12.**

---

## 9. Le transport du docbloc — c'est le cœur du travail, pas la copie des déclarations

Les 59 lignes d'en-tête sont un **savoir mesuré**. Deux passages deviennent **faux** et sont
**supprimés, pas rétrécis** (précédent contraignant : `ETAT.md:63-66`, lot 14, où une correction à un
mot fut refusée parce qu'elle rendait la phrase exacte en laissant la même fausse confiance).

| Passage source | Sort | Motif |
|---|---|---|
| **l. 4-9** « Servie AU VISITEUR ET DANS LA TOILE… c'est la raison d'être du chemin » | **SUPPRIMÉ** | Faux deux fois : la raison d'être du chemin disparaît avec le chemin, et « une feuille posée dans le thème laisserait la toile entièrement nue » est **la prédiction réfutée** de §1 |
| **l. 11-14** en-tête « DÉROGATION NOMMÉE — dette T15 » | **SUPPRIMÉ** | Il n'y a plus de dérogation. Une feuille **du thème** qui s'annoncerait comme dérogation au contrat interdisant à l'**extension** d'émettre du visuel est un contresens. Remplacé par **une ligne** : « T15 payée par #34 ; le catalogue est homogène, aucun composant n'est plus en variante P » |
| **l. 15-21** pt 1 « COUPLÉE AUX JETONS… thème échangé, la discipline photographique disparaît » | **SUPPRIMÉ comme coût, le fait est réécrit** | Le coût **cesse d'exister** : la feuille voyage désormais **dans** le thème qui porte `tokens.css`. Ce qui survit est autre : l'interdiction des replis `var(--e-3, .75rem)`, qui devient la **convention n° 2 des sœurs** (« jetons seuls »), en une ligne |
| **l. 22** pt 2 « elle nomme deux lignes de la grille du thème, donc elle encode la structure de `base.css` » | **SUPPRIMÉ** | C'était le **grief**. Une feuille du thème qui nomme la grille **du même thème** n'encode rien d'étranger. Aucune perte : le fait (« MASTER §7.1 nomme -debut/-fin, jamais -start/-end ») est déjà écrit verbatim en section 1 du corps |
| **l. 23-25** pt 3 « son domicile propre est… » | **RÉÉCRIT au présent** | Seul passage dont la substance survit. Gagne les deux choses qu'il n'a pas : le **mode de panne** du nom (jamais servi, sans erreur, page en 200) et la **poignée réelle** avec sa `deps` |
| **l. 26-28** pt 4 « LE DÉPLACEMENT FUTUR EST UNE COPIE PUIS LA SUPPRESSION… » | **SUPPRIMÉ comme instruction, réécrit en interdit** | C'est le mode d'emploi de #34 ; le laisser fait lire une action encore due. Son avertissement survit **en sens inverse**, une ligne au présent : « ne jamais rétablir un `"style"` dans `block.json` — les deux chemins chargeraient la même feuille deux fois » |
| **l. 30-42** conventions, liste close des valeurs brutes, absence de requête média et de transition | **EMPORTÉ VERBATIM**, une ancre corrigée | Exact et contraignant. **Correction obligatoire** : la citation `base.css:25-26` désigne aujourd'hui le paragraphe « numéros de ligne cités plus bas » ; la convention visée est à `base.css:20`. **Citer par texte, pas par ligne** — recopier une ancre fausse dans un fichier neuf serait la fabriquer |
| **l. 44-58** « Spécificité : pourquoi les sélecteurs sont doublés » | **EMPORTÉ VERBATIM — le passage le plus important du transfert** | C'est ce qui rend #34 sûre (§8). La phrase « cette feuille-ci, injectée dans l'iframe comme feuille de bloc, n'est pas réécrite » **reste vraie** après le déplacement : rien à changer |
| **corps, l. 61-220** — 6 sections, déclarations comprises | **EMPORTÉ VERBATIM** | Calculs à 360 px, 5,79:1, motif de chaque `!important` évité, absence de `border-radius`, cerne en `::after`, repli `50% 38%`, `__rang` porteur d'accessibilité : **rien ne dépend du chemin d'enfilage** |
| **absent de la source** | **AJOUT 1** | La dépendance `mtb-jetons` et son mode de panne (§8) |
| **absent de la source** | **AJOUT 2** | L'obligation `make css` et son mode de panne : sans elle la page reste **correcte, seulement plus lourde**, et **rien ne le signale** sauf `make css-check` |

**Règle d'ancrage gelée pour la feuille neuve** : les citations de `base.css` se font **par sélecteur ou
par section, jamais par numéro de ligne** — `base.css:20` le prescrit lui-même. Toutes les ancres de
`galerie.css` ont dérivé de ~23 à ~25 lignes depuis #40. On ne fabrique pas une onzième citation
périmée le jour même. **Les citations périmées des dix sœurs ne sont pas réparées ici — elles ont leur
propre dette (T88).**

---

## 10. Interdits

- **Le thème n'interroge jamais la base directement.**
- **Le thème ne compose jamais une chaîne métier** ni ne reformate une valeur de santé, un LOF, une
  cotation ou une date.
- **L'extension n'émet aucune règle visuelle ni mise en page** — rétabli par #34 **sur le chemin du
  visiteur**, avec la réserve nommée du §11.
- **`mtb-core.php` n'est pas édité.** Sans exception, sans amendement possible (contrat #1 §13).
- **`rendu.php`, `render.php`, `editeur.js` ne sont pas ouverts.** Vérifié : aucun ne dépend de la
  poignée supprimée. `rendu.php:70-72` cite « la feuille du composant » sans nommer ni fichier ni
  poignée — la phrase **reste vraie**, ne pas la toucher.
- **Aucune valeur brute** hors la liste close du contrat #8 §7 amendée par §19.6 : `9rem`,
  `min(100%, …)`, `1fr`, `auto-fill`, `50% 38%`, `inset(50%)`, plus `""`. Ne comptent pas : 0, 100 % et
  les mots-clés CSS.
- **Aucun `!important`** — la feuille n'en porte aucun aujourd'hui, elle n'en gagne pas.
- **La feuille est écrite UTF-8 sans BOM** : un BOM en tête fait **refuser** le pré-vol du minifieur.

---

## 11. La dérogation résiduelle, nommée — et le contrôle honnête qui en découle

`includes/blocks/galerie-photos/editeur.css` **reste**, avec sa règle `.mtb-galerie-photos
.mtb-galerie-photos__rang` (4 déclarations, servie par `"editorStyle"`, **jamais au visiteur**). #34 ne
la supprime pas : ce n'est pas un doublon décoratif mais **le seul masquage de `__rang` qui ne dépende
pas du thème**, et son mode de panne — « Photo 3 sur 12 » imprimé sous chaque vignette dans l'aperçu —
ferait croire à l'éleveuse que le bloc est cassé.

**Ce que #34 réduit, chiffré :**

| | Avant | Après |
|---|---|---|
| Feuilles CSS dans l'extension | **2** | **1** |
| Sélecteurs | **7** | **1** |
| Déclarations | **35** | **4** |
| Jetons du thème lus | **9** | **0** |
| Encode la grille du thème | oui | **non** |
| Décide fond, cerne, ratio, cadrage, typographie | oui | **non** |
| Servie au visiteur | oui | **non — jamais** |
| Nature | une **décision visuelle** | une **géométrie de masquage recopiée de MASTER §7.6** |

**La tâche 4 de l'issue est infaisable au sens littéral, et on ne rétrécira pas le motif pour la faire
passer.** Elle prescrit un `grep` prouvant qu'« aucune règle visuelle ou de mise en page ne subsiste
côté extension ». `editeur.css` porte `position: absolute` et `clip-path` : c'est de la mise en page.
Écrire `--glob '!editeur.css'` pour obtenir zéro serait la faute exacte refusée au lot 14.

**Formulation gelée du contrôle — reformulation ACCORDÉE par le lead le 1er septembre 2026** :

> **Aucune règle visuelle côté extension sur le chemin du visiteur ; subsiste la copie de masquage
> servie à l'éditeur seul.**

**La borne est écrite, pas sous-entendue** — exigence du lead, parce qu'une revendication rétrécie qui
ne nomme pas sa borne est précisément celle qui trompe. Ce qui subsiste est **une** feuille,
`includes/blocks/galerie-photos/editeur.css`, **un** sélecteur, et **ces quatre déclarations et pas une
de plus** :

```css
.mtb-galerie-photos .mtb-galerie-photos__rang {
  position: absolute;
  clip-path: inset(50%);
  inline-size: 100%;
  block-size: 0;
}
```

Servies par `"editorStyle"`, donc **dans la toile de l'éditeur uniquement, jamais au visiteur**. Zéro
`var(--…)`, zéro couleur, zéro typographie, zéro règle de mise en page du site.

**Le `grep` des deux côtés — celui qui passe, et celui qui montre ce qui reste.** Les deux sont dus :
donner le premier seul serait la revendication rétrécie.

```bash
# (1) chemin du VISITEUR — passe, et c'est la revendication de #34
rg -n '"style"' wp-content/plugins/mtb-core --glob 'block.json'          → 0
# aucun block.json du catalogue ne déclare plus de feuille de site

# (2) ce qui RESTE côté extension — ne passe pas, et ne doit pas prétendre passer
rg --files wp-content/plugins/mtb-core -g '*.css'
  → wp-content/plugins/mtb-core/includes/blocks/galerie-photos/editeur.css     (1 ligne)
rg -n 'wp_register_style|wp_enqueue_style|wp_add_inline_style|add_editor_style' \
     wp-content/plugins/mtb-core --glob '*.php'
  → …/galerie-photos/bootstrap.php:50: wp_register_style(     ← la feuille d'ÉDITEUR
```

**Aucun `--glob '!editeur.css'`, jamais.** Un `grep` qu'on ajuste jusqu'à ce qu'il soit vert ne prouve
plus rien : c'est le défaut de la passe du lot 11, qui concluait « D6 vert » sur **zéro feuille
inspectée**. C'est la dérogation résiduelle au contrat #1 §8, **réduite par #34 de sept sélecteurs à
un**. Elle est nommée, bornée et justifiée ; **elle n'est pas absente.**

**Le sort de cette copie n'appartient pas à #34** — position du lead, portée ici : son mode de panne
(« Photo 3 sur 12 » imprimé sous chaque vignette dans l'aperçu) est un **service rendu**, et trancher
si une règle servie à l'éditeur seul relève ou non de la frontière est un **arbitrage de `MASTER.md`**,
pas un effet de bord d'un rangement.

**Deux endroits que le `grep` ne verra jamais, nommés ici plutôt que tus :**

- `rendu.php:37` — `LARGEURS_PAR_DEFAUT = '(min-width: 32rem) 196px, (min-width: 21rem) 222px, 90vw'` :
  de l'**arithmétique de la grille du thème écrite dans l'extension**. Irréductible — un `sizes` ne peut
  pas vivre dans une feuille de style — donc ce n'est pas un défaut, mais c'est un endroit où
  l'extension **sait** la mise en page du thème, et #34 ne l'enlève pas.
- `derniere-portee/render.php:157` — un `style="--photo-largeur-naturelle:…px"` : une **largeur de
  fichier mesurée**, documentée comme fait et non comme règle. Hors périmètre, **ne pas « nettoyer »**.

---

## 12. Séquence d'implémentation, et les états dangereux

**Un seul commit.** C'est un *déplacement* : deux moitiés commitées séparément décriraient deux
opérations. L'ordre dans l'arbre de travail est celui que `galerie.css:26-28` prescrit depuis six lots —
**copie d'abord, débranchement ensuite** :

| # | Geste | Agent |
|---|---|---|
| **0** | Créer `themes/mtb/assets/css/blocs/mtb-galerie-photos.css`, UTF-8 sans BOM | `dev-ux-mtb` |
| **1** | `block.json` — retirer la l. 30 **et la virgule terminale de la l. 29** | `dev-back-mtb` |
| **2** | `bootstrap.php` — retirer le `wp_register_style` **et corriger le docbloc de `enregistrer()`** | `dev-back-mtb` |
| **3** | Supprimer `galerie.css` | `dev-back-mtb` |
| **4** | `make css` → 15 paires, puis `make css-check` → **exit 0** | `dev-ux-mtb` |
| **5** | Compte 14 → 15 aux **quatre** lignes (§13) | `dev-ux-mtb` |
| **6** | Vérifications du §14 | chaîne |

**Le geste 1 porte le seul piège qui casse fort.** Retirer la ligne 30 laisse la ligne 29 avec sa
**virgule terminale** → JSON invalide → `register_block_type()` échoue → **le bloc n'est plus enregistré
du tout** → la galerie **disparaît de toutes les pages qui la portent**, et l'éditeur affiche « ce bloc
contient du contenu inattendu ». La panne la plus grave possible sur cette issue, et elle vient d'un
caractère.

**Le geste 2 n'est pas une suppression de 11 lignes.** Le docbloc de `enregistrer()` dit « **Les trois
poignées** sont enregistrées ici… Pour **les deux feuilles**, un `file:` serait sans danger ». Laisser
ces deux nombres, c'est le péché du lot 14 : une phrase fausse qui passe la relecture. Le commentaire
« Une feuille déclarée en `style` … atteint la page publique ET la toile » est **supprimé, pas
reformulé** : il porte la prédiction réfutée du §1.

**Les trois états intermédiaires, nommément :**

| État | Verdict |
|---|---|
| poignée gardée + `"style"` gardée + fichier supprimé | **DANGEREUX** — un `<link>` vers un **404** sur chaque page à galerie, zéro diagnostic PHP, page en 200. Si la feuille du thème est déjà là : **aucune régression à l'œil**, silencieux. Sinon : perte visuelle totale **et** `__rang` démasqué. Piège de recette : `editeur.css` continue de masquer `__rang` dans la toile, donc **la toile aurait l'air correcte pendant que la page publique est cassée** — la recette se fait d'abord sur la page publique |
| poignée gardée + `"style"` retirée | **BÉNIN** — une poignée enregistrée que personne ne met en file ne produit rien. Du code mort : état de sonde acceptable, jamais livrable |
| poignée retirée + `"style"` gardée | **BÉNIN en effet, MENSONGER en manifeste** — `all_deps()` abandonne l'élément **en silence**. Mais `block.json` déclarerait une feuille qui n'existe pas, lue par une machine |

**Jamais** le débranchement avant que la feuille du thème existe.

---

## 13. Le compte d'artefacts — quatre lignes, mesurées

`make css` est **obligatoire dans le même commit** (§ Conventions de `CLAUDE.md`). La feuille neuve fait
passer le compte de **14 à 15**.

| Ligne | Texte porteur |
|---|---|
| **`Makefile:119`** | « Vérifie les **14** paires source/artefact… » — *(l'ancre `:116` est fausse : c'est la cible `css:`, qui ne porte aucun nombre)* |
| **`docs/docker.md:70`** | « Régénère les **14** feuilles minifiées du thème » |
| **`docs/docker.md:71`** | « Vérifie les **14** paires source/artefact » |
| **`docs/docker.md:304`** | « Régénère les **14** artefacts » — *(`:305` ne porte aucun nombre)* |

**La vérification ne se fait PAS par ce nombre — elle se fait par énumération du disque.**
`mtb_min_sources()` (`mtb-minifier-css.php:487`) globe `assets/css/*.css` puis `assets/css/blocs/*.css`.
**Aucune constante de compte n'existe dans l'outil.** Conséquences :

- `make css-check` couvre la 15ᵉ paire **automatiquement**, sans qu'aucune liste soit tenue ;
- oublier `make css` **est attrapé** : sortie `ABSENT`, exit 1 ;
- **oublier les quatre lignes de prose n'est attrapé par rien.** La mise à jour est un **livrable**.

**Cinquième porteur, nommé et NON modifié** : `docker/outils/mtb-minifier-css.php:406` — « vert sur les
**quinze** feuilles ». Ambigu **avant même #34** (le pré-vol balaie 14 sources, mais `issue-40.md:401`
titre « 14 artefacts sur 15 sources », donc « quinze » compte probablement le corpus, `editor.css`
comprise). Après #34 le corpus fait 16 et le pré-vol 15 : **la phrase devient fausse dans les deux
lectures**. C'est un livrable de #40 ; sa désambiguïsation n'appartient pas à cette chaîne.

**Jamais touchés** : `docs/contracts/issue-40.md` (11 lignes portant le compte — **contrat gelé**, il
décrit l'état à sa date, c'est sa fonction) et `docs/ETAT.md` (journal, propriété de `/lead-mtb`).

---

## 14. Vérifications — et ce que chacune ne prouve pas

```bash
# 1 — aucun double chargement, page publique
curl -s http://localhost:3005/chien/jango/ | grep -oE "mtb-bloc-mtb-galerie-photos(-inline)?-css" | sort | uniq -c   # attendu : 1, UNE seule forme
curl -s http://localhost:3005/chien/jango/ | grep -c "mtb-galerie-photos-style"                                      # attendu : 0

# 2 — la poignée ne pointe pas un fichier absent
curl -s -o /dev/null -w "%{http_code}\n" "<URL relevée>"    # attendu : 200, et l'URL finit par .min.css
make css-check                                              # attendu : exit 0, « pré-vol : 15 feuille(s) »

# 3 — le rendu est inchangé
curl -s http://localhost:3005/chien/jango/ | grep -oE "mtb-galerie-photos__[a-z]+" | sort | uniq -c
# attendu, identique à la baseline §2 : 1 __grille, 7 __lien, 7 __image, 7 __rang

# 4 — la toile de l'éditeur (admin connecté)
grep -c "mtb-bloc-mtb-galerie-photos-css"  → 2      grep -c "mtb-galerie-photos-style" → 0
grep -c "mtb-jetons"                       → ≥ 1    # zéro = galerie abandonnée sans bruit

# 5 — le bloc est toujours enregistré, et le manifeste lisible
wp eval '$b = WP_Block_Type_Registry::get_instance()->get_registered("mtb/galerie-photos");
         var_export( array( (bool) $b, $b->style_handles, $b->editor_style_handles ) );'
# attendu : array( true, array(), array( "mtb-galerie-photos-editeur-style" ) )
```

**Ce que ces contrôles ne prouvent pas, et qui doit être dit :**

- **Aucun ne prouve « le rendu identique au pixel ».** Personne ne l'a jamais démontré dans ce dépôt
  (`ETAT.md:97-100`) et #34 ne le démontrera pas davantage. Ce qui est démontrable est l'**identité du
  flux de déclarations** (6 règles, 31 déclarations) et l'**invariance de la cascade par spécificité**.
- **`make css-check` ignore WordPress entièrement.** Une feuille nommée `galerie-photos.css` passerait
  `css-check` avec une 16ᵉ paire parfaitement à jour, et **ne serait jamais servie**.
- **Un `debug.log` vide est compatible avec une feuille jamais mise en file** — c'est précisément le
  mode de panne du chargeur.
- **La présence d'une poignée dans le payload de la toile n'est pas un rendu.**

**Deux pièges de recette, à connaître avant de conclure :**

1. **La forme servie peut changer.** La poignée gagne un `path`, donc `wp_maybe_inline_styles()` devient
   éligible : le `<link>` peut devenir un `<style>` incorporé. Pire, l'incorporation trie **par taille
   croissante** sous un plafond global : ajouter un candidat **peut faire basculer une feuille voisine**
   de `<style>` vers `<link>` sur les pages à galerie. Un témoin qui compterait les `<link>` de la page,
   ou qui exigerait que `mtb-bloc-mtb-coordonnees-plan` reste incorporée, **peut rougir sans défaut**.
   Le témoin porte sur les **déclarations**, jamais sur la forme du transport.
2. **L'opcache.** Après édition de `bootstrap.php`, ne rien conclure sur une page servie du cache.
3. **La section 1 n'est vérifiée sur aucune fiche.** Sur `/chien/…` et `/portee/…`, la racine du bloc est
   **petite-fille** de `.mtb-canal` : `.mtb-canal > .mtb-galerie-photos` y est **inerte**. Elle ne
   s'applique que sur les compositions (`bhpl`, `bhpl-en-france`, `litterature`), où le bloc est enfant
   direct. **Un test qui ne rendrait que `/chien/jango/` ne vérifierait jamais la section 1.**
4. **`#34 allège la page.** Une comparaison d'octets stricte à la baseline **doit** diverger. Ce qui ne
   doit pas diverger, ce sont les 31 déclarations et les comptes de crochets.

---

## 15. Ce que l'éleveuse voit

**Rien.** Aucun champ, aucun libellé, aucune colonne, aucun panneau, aucun écran, aucune fiche du
guide. Aucun fichier de `docs/guide/` ne cite `galerie.css` ni la poignée. `doc-client-mtb` **n'a rien à
faire sur cette issue** — et si cela devait changer, la bijection du guide est à **122 captures /
122 références**, jamais une image sans sa référence ni l'inverse.

---

## 16. Arbitrages — chaque désaccord, la décision, sa raison

| # | Désaccord | Décision | Raison |
|---|---|---|---|
| 1 | **Chemin du `wp_register_style`** : l'issue et la consigne de lot disent `mtb-core/bootstrap.php` | **`includes/blocks/galerie-photos/bootstrap.php:55-60`** | Le fichier `mtb-core/bootstrap.php` **n'existe pas** : la racine ne porte que `mtb-core.php` et `includes/`. Corrigé et remonté au lead |
| 2 | `galerie.css` **vidée ou supprimée** ? L'empreinte dit « à vider de ses règles visuelles » | **SUPPRIMÉE** | Il ne resterait **rien** : 100 % des déclarations sont visuelles, et plus personne ne l'enregistrerait. Précédent du dépôt : `issue-8.md:1226-1231` (§20.3) — quand l'état vide a quitté `editeur.css`, les règles ont été **retirées**, et le fichier n'a survécu **que parce qu'il lui restait une règle**. Aucun fichier vide n'existe dans ce dépôt. Un `.css` mort dans un dossier d'extension est un aimant : la prochaine chaîne l'ouvrira et la dérogation renaîtra sans qu'aucune décision l'ait rouverte |
| 3 | **5ᵉ citation de `galerie.css` dans `editeur.css`** : le plan back dit `:50`, le plan front dit `:49` | **`:49`** | Mesuré : 6, 24, 33, 44, **49**. Le plan back s'est trompé d'une ligne |
| 4 | **Où vit le compte d'artefacts** : la consigne de lot dit `Makefile:116`, le plan front dit `:119` | **`:119`** | Mesuré. `:116` est la cible `css:`, sans nombre |
| 5 | **Combien de lignes portent le compte** : la consigne dit `docker.md:70-71` **et** `:304-305` | **`:70`, `:71`, `:304`** | Mesuré : `:305` ne porte aucun nombre. **Quatre** lignes au total avec le `Makefile`, pas six |
| 6 | **Taille de `galerie.css`** : le plan back dit « ~30 déclarations », le front dit 31 | **6 règles / 6 sélecteurs / 31 déclarations** | Compté sur le fichier décommenté |
| 7 | **Port de la pile** : le plan front dit 8080 (`compose.yaml:78`) | **3005** | `compose.yaml` lit `${WP_PORT:-8080}` — le plan a pris le **défaut** pour la valeur. La pile écoute sur 3005, où toutes les mesures de ce contrat ont été faites. **C'est le plus instructif des trois défauts de chiffre du lot, et il est consigné à la demande du lead** : une ligne `:78` était citée, le fichier avait bien été ouvert, et la lecture était pourtant fausse. **Une citation `fichier:ligne` exacte n'est pas une mesure** — elle prouve qu'on a lu, pas qu'on a compris ce qu'on lisait. C'est exactement le genre de lecture qui **paraît sourcée et ne l'est pas** |
| 8 | **La toile de l'éditeur est-elle atteinte par une feuille du thème ?** `issue-7.md:576-582` dit non, `issue-6.md:627` dit oui | **OUI, mesuré** | §1. Prédiction contre sonde : la sonde gagne, et la mesure du 1er septembre 2026 la confirme sur les dix sœurs |
| 9 | **Faut-il supprimer `editeur.css`** puisque son doublon devient redondant ? | **NON** | Hors sujet de #34, et la règle reste le seul masquage de `__rang` **indépendant du thème**. Sa présence devient **plus** cohérente, pas moins |
| 10 | **Numérotation de la dette résiduelle** : `T15-b` (ma proposition) ou `T15-bis` (plan back) | **`T15-bis`**, si dette il y a | Le seul précédent de dette dérivée du dépôt est **`T16-bis`**. `T15-b` ouvrirait une seconde convention pour un cas unique |
| 11 | **Un ou deux commits ?** | **Un seul** | C'est un déplacement. Deux commits laisseraient un état intermédiaire de double chargement — bénin, mais qui décrit deux opérations là où il n'y en a qu'une |
| 12 | **Réparer les citations `functions.php:NNN` périmées croisées en chemin ?** | **NON** | T88 : 48 citations, aucune exacte, dette propre. On **localise par symbole** et on **n'en fabrique aucune neuve** |

---

## 17. Élargissements d'empreinte — état final

Les trois demandes ont été **accordées** par le lead le 1er septembre 2026.

| Objet | État |
|---|---|
| `Makefile:119`, `docs/docker.md:70,71,304` — **le seul compte** | **ACCORDÉ d'avance** |
| `includes/blocks/galerie-photos/editeur.css` — **docbloc seul**, aucune déclaration CSS touchée | **ACCORDÉ.** Motif du lead : supprimer `galerie.css` en laissant cinq renvois vers un fichier disparu, c'est **fabriquer la péremption au moment même où l'on range**. Étendu par lui à la l. 14 (« dette T13 », payée) — « le même geste et la même famille » |
| `docs/contracts/issue-8.md` — **§22 ajouté en fin**, aucune ligne existante modifiée | **ACCORDÉ**, sur la distinction du §18 ci-dessous |
| `themes/mtb/assets/css/blocs/mtb-grille-chiens.css:129`, `mtb-liste-portees.css:89`, `assets/css/fiches.css:104` | **CONFIÉS À #33**, le lead s'engageant à les porter dans son mandat. Motif : la règle `make css` fait que **toute feuille touchée entraîne son artefact** — les rouvrir ici élargirait l'empreinte pour trois commentaires |
| `docs/ETAT.md` | **NON OUVERT.** Propriété de `/lead-mtb`. **T15 est payée par ce commit** ; l'inscription lui appartient |

## 18. La distinction sur les contrats gelés — gravée par le lead, citée ici

« Ne pas éditer les contrats gelés » **reste la règle**, et le lead a statué ainsi deux fois la veille
(T86 pour `issue-16/17/18`, T91 pour `issue-2/7/12/13`).

**Ce qui est interdit** : **réécrire un énoncé gelé**. Sa fausseté est une **trace historique, pas un
bug** — la corriger efface la preuve de ce qui a été cru.

**Ce qui est permis ici, et pourquoi** : **ajouter une section d'amendement datée, en fin de fichier,
dans un document qui porte déjà cette convention**. `issue-8.md` a §19, §20 et §21, et **§20.5
anticipait explicitement #34**. Rien d'existant n'est modifié, donc **aucune citation par numéro n'est
périmée** — leçon de `issue-17.md:412`, où une correction de dette avait décalé une ligne citée par un
contrat gelé.

**Cette autorisation ne se généralise pas** : elle vaut pour `issue-8.md` **parce que ce document porte
§19-§21**. Un contrat sans convention d'amendement ne s'ouvre pas de cette façon.

## 19. Dettes rendues au lead

**T15 est payée** par `a171034`. Trois dettes, avec leur énoncé et leur provenance :

1. **T15-bis** — nom retenu par le lead sur le seul précédent de dette dérivée du dépôt (`T16-bis`).
   **Énoncé, dans la forme que le lead a arrêtée** : ce n'est pas « il reste du CSS côté extension »,
   c'est **« il en reste deux copies, et rien ne surveille leur écart »**. La dette porte donc deux
   choses et pas une :
   - la **dérogation résiduelle** — les quatre déclarations de `.mtb-galerie-photos
     .mtb-galerie-photos__rang`, servies par `"editorStyle"`, **à l'éditeur seul, jamais au visiteur** ;
   - la **divergence possible entre les deux copies** de cette règle, l'une dans
     `themes/mtb/assets/css/blocs/mtb-galerie-photos.css`, l'autre dans
     `mtb-core/includes/blocks/galerie-photos/editeur.css`. Duplication **volontaire** (contrat #8 §6,
     §7.6) et vérifiée identique déclaration pour déclaration au 1er septembre 2026 — mais **aucun
     contrôle du dépôt ne dirait leur divergence** : `make css-check` ne balaie que les feuilles du
     thème et **ne voit jamais celle-ci**, et une relecture d'empreinte n'ouvre qu'un seul côté.
   Depuis #34, la duplication **traverse la frontière thème/extension** : les deux copies ont des
   propriétaires différents. *Provenance : #34, lot 15.*
2. **`docker/outils/mtb-minifier-css.php:406` — « vert sur les quinze feuilles ».** **Ambigu avant même
   #34** : le pré-vol balaie 14 sources, quand `issue-40.md:401` titre « 14 artefacts sur 15 sources ».
   Les deux lectures : « quinze » = le **corpus CSS** du thème (14 + `editor.css`), ou « quinze » = le
   **pré-vol**. Après #34, corpus à 16 et pré-vol à 15 : **la phrase est fausse dans les deux**.
   Indésambiguïsable par cette chaîne — il faudrait deviner ce que #40 voulait dire. *Provenance :
   livrable de #40, rendue fausse par #34.*
3. **Deux renvois pendants et une poignée renommée, confiés à #33** — et la distinction compte :
   `fiches.css:104` cite une **poignée qui change de nom** (`mtb-galerie-photos-style` →
   `mtb-bloc-mtb-galerie-photos`), tandis que `mtb-grille-chiens.css:129` et `mtb-liste-portees.css:89`
   renvoient à un **fichier supprimé**. **Ce n'est pas T88**, qui est une dérive de numéros : c'est une
   **cible qui disparaît**. *Provenance : #34, lot 15.*

## 20. Une erreur de raisonnement de cette chaîne, consignée parce qu'elle se répétera

Pendant la seconde passe, `HEAD` a avancé de deux commits. J'ai conclu que l'agent qui travaillait alors
en était l'auteur, et je l'ai écrit comme un fait établi. **C'était faux : les deux commits étaient ceux
du lead**, entré dans l'arbre pendant que la chaîne tournait.

**Ce qui rend l'erreur intéressante, c'est que le raisonnement était correct et la méthode mauvaise.**
J'avais trois observations — le commit ne touche que les deux fichiers accordés à cet agent, il est
horodaté pendant son exécution, l'auteur git est celui du dépôt. Les trois **corroborent** l'hypothèse
« c'est lui ». **Aucune ne la distingue de l'hypothèse « c'est le lead »** : le lead éditait exactement
les mêmes deux fichiers, au même moment, sous la même identité git — ce dépôt n'ayant qu'un auteur.
J'ai pris de la **corroboration** pour de la **discrimination**.

Et la prémisse qui portait tout — « aucune autre chaîne ne tourne » — était vraie **des chaînes** et
fausse **du lead**. Un raisonnement rigoureux sur un modèle incomplet du monde produit une conclusion
fausse **avec l'assurance d'une mesure**.

**La règle, qui généralise l'arbitrage 7** : celui-ci dit qu'une citation `fichier:ligne` exacte prouve
qu'on a lu, pas qu'on a compris. Celle-ci l'étend à toute inférence — **une observation qui s'accorde
avec mon hypothèse ne la démontre pas tant que je n'ai pas cherché l'hypothèse concurrente qu'elle
accorderait aussi bien.** Avant de conclure sur la provenance d'un changement, énumérer **qui d'autre a
la main sur l'arbre** — le lead en fait partie.

**Corollaire d'orchestration** : sur un projet mono-branche sans isolation, **une écriture non annoncée
dans l'empreinte d'une chaîne vivante est indiscernable d'une faute de cette chaîne**. Rien n'a été
perdu ici parce que la chaîne a amendé au lieu d'écraser — mais c'est de la chance, pas de la méthode.

## 21. T85 — ce que #34 en paie, et une correction à son énoncé

**#34 en paie une part, et une seule.** `MTB_CORE_VERSION` vaut `'0.1.0'` et `mtb-core.php` est
**interdit à toute issue** (contrat #1 §13) : personne ne peut l'incrémenter, donc tout ce qui est
versionné par elle garde son URL quel que soit son contenu. En déplaçant `galerie.css`, #34 retire de
ce régime **la seule feuille de style que l'extension servait au visiteur** ; elle passe au
versionnement par **empreinte de contenu** (`mtb_feuille_a_servir()`).

**Ce que #34 ne paie pas, et il faut le dire ainsi.** Il reste dans l'extension **1** seul
`wp_register_style` — celui de `editeur.css`, servi à l'éditeur seul — et **15** `wp_register_script`,
tous encore versionnés par la constante gelée. **#34 ne touche aucun script.** C'est donc une part de
la cause, pas une part du périmètre que T85 se donne.

**Et l'énoncé de T85 porte un nombre faux, antérieur à #34.** `docs/ETAT.md:1169` écrit « vaut pour les
**onze** `editeur.js` de l'extension ». Il y en a **douze** :

```
bandeau-alerte · bandeau-ouverture · coordonnees-plan · derniere-portee · encart-appel
fiche-information · formulaire-contact · galerie-photos · grille-chiens · lien-de-recours
liste-portees · tableau-resultats
```

**Ce n'est pas #34 qui l'a périmé** : `git ls-tree` à `5103bd6` — le commit de clôture du lot 14, celui
qui **inscrit** T85 — en rend déjà **douze**. Le compte était faux le jour où il a été écrit ;
`lien-de-recours`, livré par #39 au lot 13, a vraisemblablement été oublié derrière « les dix
composants du catalogue ». **Signalé, non corrigé** : `docs/ETAT.md` appartient à `/lead-mtb`.
