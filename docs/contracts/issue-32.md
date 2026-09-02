# Contrat d'interface — Issue #32 — Habillage des écrans de saisie

**Gelé le 2026-09-02, avant implémentation.** Ce contrat tranche la tâche 1 de l'issue — *où vit le
CSS d'administration* — et il la tranche par la négative : **il n'y en a pas, et il n'y en aura pas
par cette issue.**

Il corrige aussi l'énoncé de l'issue sur six points. L'issue décrivait un défaut d'habillage ; la
mesure décrit **deux écrans qui ont quitté le balisage du cœur de WordPress, et un troisième qui ne
l'a pas quitté et qui n'a aucun défaut**. Ce n'est pas la même issue, et ce n'est pas le même
remède.

---

## 0. Ce que fait cette issue, en une phrase

Elle rend au cœur de WordPress le balisage que deux écrans de saisie lui avaient pris, et elle
n'écrit **aucune règle de style**, ni dans le thème, ni dans l'extension.

---

## 1. Les mesures qui autorisent ce contrat — faites avant tout travail, jamais déduites

Toutes les valeurs ci-dessous sont relevées dans un **Chrome réel piloté en CDP** sur la pile Docker
(port **3005**, `.env:12` — et non 8080, qui n'est que le défaut de `compose.yaml:78`), ou lues dans
le **source du cœur de WordPress 6.9 dans le conteneur**. Aucune n'est déduite d'une lecture de PHP.

### 1.1 Les quatre écrans, en hauteur et en débordement

| Écran | Contenu réel | Hauteur 1440 px | Hauteur 360 px | Débordement horizontal |
|---|---|---|---|---|
| Portée `post=176` « N_2 2017 » | **10 chiots, 18 photos** | 6 442 px | **9 904 px** | **oui aux deux largeurs** |
| Portée `post=196` « DEMO1 2025 » | 2 chiots, 1 photo | 2 783 px | 4 404 px | oui aux deux largeurs |
| Chien `post=192` « Luna » | 0 photo | 4 258 px | 6 252 px | 360 px seulement, **et ce n'est pas le nôtre** (§1.4) |
| Résultat `post=200` | — | 1 050 px | 1 916 px | **aucun** |

**Le cas volumineux que réclame la checklist existe déjà dans les données, et il est plus lourd que
ce qu'elle demande.** Elle demande « une portée à huit chiots avec dix photos » ; la base porte
`N_2 2017` avec **10 chiots et 18 photos**. Relevé en base sur les 27 portées : photos — 18, 11, 11,
9, 8, 7… ; chiots — cinq portées à 10. La vérification volumétrique porte donc sur le **pire cas
réel**, pas sur un cas fabriqué.

### 1.2 Le débordement horizontal — le défaut le plus grave, et il n'est pas dans l'issue

Sur l'écran Portée, `scrollWidth` vaut **1 520 px** pour une fenêtre de 1 440, et **1 511 px** pour
une fenêtre de **360**. La page est quatre fois plus large que l'écran.

L'unique élément fautif, isolé par mesure — tout le reste de la liste des débordants n'est que sa
descendance :

`<table id="mtb-portee-chiots-tableau" class="widefat">` — largeur **1 325 px** à 1440, **1 488 px**
à 360.

**Cause, lue dans le source du cœur** : `wp-admin/css/forms.css:461-463` → `.regular-text { width: 25em; }`.
L'écran aligne trois `regular-text` dans une même rangée (Nom, N° LOF, Devenir) plus un `<select>` :
la largeur minimale du tableau vaut donc ~1 490 px, et le `width: 100%` de `widefat` ne peut pas
descendre en dessous d'un contenu incompressible.

**C'est une violation directe du BRIEF §11 — « 360 px sans défilement horizontal » — et de D7.**
Elle est **absente de la checklist de l'issue**, qui ne parle que de galeries.

### 1.3 Les galeries — ce que coûte une photo

Portée `post=176`, `<ul class="mtb-portee-galerie">` : **18 `<li>`**, hauteur totale **2 907 px**.

| | 1440 px | 360 px |
|---|---|---|
| Hauteur d'un `<li>` | **155,8 px** | **245,4 px** |
| Largeur d'un `<li>` | 912 px | 312 px |
| Largeur utile (la vignette) | **150 px** | 150 px |

À 1440 px, **une ligne de 912 px de large en emploie 150** : plus de 80 % de la largeur ne porte
rien. C'est de là que vient la hauteur, et **aucun balisage du cœur ne sait mettre deux photos côte
à côte** — seul du CSS le sait. C'est le point où cette issue s'arrête, et le §7 dit pourquoi.

`display: block`, `list-style-type: none`, `gap: normal`, `padding: 0`.

### 1.4 Quatre affirmations de l'énoncé que la mesure réfute

1. **« les trois écrans de saisie »** → **deux**. `includes/fields/resultat/ecran.php:36` rend un
   simple `<table class="form-table" role="presentation">`, ses 14 jetons `mtb-resultat-*` sont des
   `id` et non des classes, et l'écran **ne déborde à aucune des deux largeurs**. Il n'a rien à
   corriger. Son docbloc `:5-6` l'assume : « *Aucune règle visuelle, aucun style en ligne, aucun
   JavaScript : les classes du cœur suffisent.* »
2. **« les deux galeries se rendent en liste à puces verticale »** → **il n'y a aucune puce**, et la
   règle qui l'établit a été lue dans le CSSOM : le cœur pose `ul { list-style-type: none }`
   (`wp-admin/load-styles.php`, groupe `common`). Le défaut réel n'est pas une puce, c'est une photo
   par ligne et une commande dissociée de son objet.
3. **« un écran de saisie non habillé ne garantit ni focus visible ni structure lisible »** → **le
   focus est garanti, et par le cœur**. Mesuré sur sept contrôles (champ texte, sélecteur, zone de
   texte, case à cocher de galerie, bouton de galerie) : tous `matchesFocusVisible: true`, tous
   `reachedByTab: true`, tous portant un anneau visible `box-shadow: rgb(34,113,177) 0 0 0 1px`
   (contrôles de saisie) ou `rgb(53,130,196)` (boutons), avec `border-color` accordée. 249 éléments
   atteignables au clavier sur l'écran Portée, 170 sur l'écran Chien. **D7 n'a rien à réclamer sur le
   focus.**
4. **« `mtb-core` ne contient aucun fichier CSS »** → il en contient **un**,
   `includes/blocks/galerie-photos/editeur.css`, qui se déclare lui-même « la dérogation résiduelle »
   au contrat #1 §8 et que la revue de #34 a maintenu.

### 1.5 Trois ancres fausses de l'énoncé, rectifiées

| L'issue écrit | La mesure dit |
|---|---|
| `mtb-portee-galerie` à `portee/ecran.php:439` | **`:566`** |
| L'interdit du contrat #5 à `§387-390` | **lignes 405-411** (les l. 387-390 sont dans le §7, « Le piège du Pays ») |
| « le thème `mtb` n'est pas chargé dans l'administration » | **le thème est chargé** — `functions.php` accroche `admin_init` et `admin_menu` — ce sont ses **feuilles** qui n'y entrent pas |

**Rectification des ancres de ce contrat lui-même, exigée par sa propre règle.** Les §3.3 et §4.1
citent `chien/ecran.php:637`, `:641`, `:646` : ces numéros étaient exacts **avant** l'implémentation
et valent désormais **645**, **650**, **655**, décalés de huit lignes par le commentaire que la
correction 3.2 a ajouté plus haut dans le fichier.

Le corps du contrat n'est **pas réécrit** — un contrat gelé ne se corrige pas, sa formulation
d'origine est une trace et non un défaut (`issue-34.md` §18). La rectification est écrite **ici**, et
c'est la forme que ce document a lui-même établie au présent §1.5. **On se repère au symbole**
(`rendre_galerie()`, les trois `printf` de `mtb-galerie__retirer` / `__avant` / `__apres`), jamais au
numéro : c'est la leçon de la dette **T88**, où 48 citations de ligne étaient devenues fausses.

### 1.6 Aucune feuille de style n'atteint ces écrans — mesuré

Sur les quatre écrans, aux deux largeurs : la liste des feuilles chargées ne contient **aucune**
feuille de `wp-content/themes/mtb/` et **aucune** feuille de `wp-content/plugins/mtb-core/`.
Le constat de départ de l'issue est juste sur ce point, et il est désormais mesuré et non supposé.

---

## 2. L'arbitrage — où vit le CSS d'administration

**Décision : nulle part. Cette issue n'écrit aucune règle de style.**

Trois options étaient au menu ; une quatrième est apparue à l'instruction.

| | Option | Retenue ? |
|---|---|---|
| **A** | Feuille dans le thème, servie par `admin_enqueue_scripts` | **Non** |
| **B** | Feuille dans l'extension, dérogation écrite et bornée | **Non** |
| **C** | Aucune feuille : rendre le balisage au cœur | **Oui** |
| **D** | C, puis B pour le résidu | **Non** — §7 |

### 2.1 Pourquoi pas A — la feuille dans le thème

- **Elle défait ce qu'elle répare, au premier changement de thème.** L'écran de saisie appartient à
  l'extension ; faire dépendre sa mise en page du thème, c'est accepter qu'il redevienne cassé le jour
  où le thème change. Or l'extension existe précisément pour que le contenu et ses écrans survivent au
  thème (`CLAUDE.md`, « Pourquoi une extension et pas tout dans le thème »).
- **`MASTER.md` ne décrit aucune apparence d'administration** — il n'en fixe que le vocabulaire (§10.2,
  §10.4) et son §12.10 renvoie exclusivement à des sections du site public. Il n'y a donc **rien de
  propre au projet** qu'un passage par le thème préserverait.
- **Elle empoisonne le pipeline CSS pour tous les lots suivants.** `mtb_min_sources()`
  (`docker/outils/mtb-minifier-css.php:487`) balaie `assets/css/*.css` : une feuille posée là est
  **prise d'office** et exige son `.min.css` dans le même commit. #32 entrerait alors en recouvrement
  d'empreinte avec **toute** issue CSS future. L'en exclure nommément demanderait deux retouches
  jumelles — `mtb_min_sources()` sur le modèle d'`editor.css` (`:499`) **et** `mtb_min_orphelins()`
  (`:577-579`) — dans un fichier hors empreinte.
- **Elle oblige à ouvrir `themes/mtb/functions.php`**, 1 101 lignes, déjà porteur de la dette T88
  (48 citations de ligne périmées), et à y écrire en dur `'mtb_chien'` et `'mtb_portee'` — un
  troisième exemplaire du ciblage d'écran, après ceux de `chien/bootstrap.php` et
  `portee/bootstrap.php`.

### 2.2 Pourquoi pas B — la feuille dans l'extension

L'argument du changement de thème joue **pour** B, et le précédent d'`editeur.css` montre que ce
projet sait borner une dérogation nommée. B était le meilleur des deux termes que posait la tâche 1.
Elle est pourtant écartée, sur un fait qu'aucune des deux instructions parallèles n'avait relevé :

**L'interdit vit dans quatre contrats gelés, et l'un des quatre ne peut pas recevoir d'amendement.**

| Contrat | Ligne | Convention d'amendement ? |
|---|---|---|
| `issue-1.md` | `:238-240` et `:497` | **oui** — « Amendement 1 », « Amendement 2 » |
| `issue-3.md` | `:528` | **oui** — §18 « Amendements portés après implémentation » |
| `issue-5.md` | `:405-411` | **oui** — §14 « Journal des amendements » |
| **`issue-4.md`** | **`:513`** | **NON** — le document s'arrête au §11, sans journal |

`docs/contracts/issue-34.md` §18 a gravé la règle, et elle est explicite : « *Cette autorisation ne se
généralise pas : elle vaut pour `issue-8.md` **parce que ce document porte §19-§21**. Un contrat sans
convention d'amendement ne s'ouvre pas de cette façon.* »

Servir une feuille depuis l'extension laisserait donc `issue-4.md:513` — « *L'extension n'émet
**aucune** règle visuelle ni mise en page.* » — énoncer gelé une règle que le code livré contredit,
**sans recours légitime pour l'écrire**. On peut plaider que le §4 n'est qu'une reformulation du §1 et
qu'amender le contrat fondateur suffirait ; c'est exactement le raisonnement commode que ce projet se
refuse. **B est écartée pour cette raison, et pour elle seule.**

### 2.3 Pourquoi C — rendre le balisage au cœur

**Trois écrans jumeaux, écrits par trois chaînes. Celui que son contrat obligeait au balisage du cœur
n'a aucun défaut ; les deux qui ont inventé des crochets de classes en ont trois, dont deux pèsent
sur D7.** Ce n'est pas un hasard de rendu, c'est le résultat de deux méthodes — et la mesure du §1.1
le confirme à l'écran : l'écran Résultat est le seul qui tienne à 360 px.

`issue-5.md:405-411` énonce déjà la règle que C applique : « *Les classes du cœur de WordPress sont
seules admises […] le critère n'est pas la liste, c'est l'origine — une classe du cœur n'émet aucune
décision visuelle de notre fait.* » C étend cette règle aux deux écrans qui s'en étaient écartés.
**Elle n'amende aucun contrat : elle en applique un.**

Le cœur paie mieux que nous ne le ferions, et c'est mesuré : `wp-includes/css/buttons.css:347-360`
donne à `.button`, **sous `@media (max-width: 782px)`**, `min-height: 40px`, `line-height: 38px`,
`padding: 0 14px` et **`margin-bottom: 4px`**. Autrement dit le cœur fournit lui-même des cibles de
40 px séparées de 4 px **sur la largeur même que le brief met sous tension**, sans que nous écrivions
une ligne. Aucune feuille de notre main ne peut revendiquer cela aussi proprement.

---

## 3. Ce que cette issue corrige, et à quelle cause

Trois corrections, chacune posée **à la cause** et non à l'endroit où le symptôme se voit. Toutes
emploient une classe du cœur, aucune n'écrit de règle.

### 3.1 Le débordement horizontal du tableau des chiots — priorité 1

**Symptôme mesuré** : `table#mtb-portee-chiots-tableau.widefat` fait 1 325 px dans une fenêtre de
1 440 et 1 488 px dans une fenêtre de 360 ; `overflow-x: visible`, aucun conteneur défilant, donc
**c'est le document entier qui défile latéralement**. À 1 440 px, la colonne « Retirer ce chiot »
tombe **hors de l'écran** : la commande qui retire un chiot n'est pas atteignable sans faire défiler
la page de côté. Ce n'est pas un défaut de petit écran.

**Cause** : `wp-admin/css/forms.css:461-463` → `.regular-text { width: 25em; }`, appliquée à trois
champs d'une même rangée (Nom, N° LOF, Devenir), plus un `<select>`. La largeur minimale du tableau
est incompressible, et le `width: 100%` de `widefat` ne peut rien contre un contenu plus large que
lui.

**Correction** : les champs de saisie d'une **rangée de tableau** ne portent plus `regular-text` —
classe faite pour un `form-table` en pleine largeur, pas pour une cellule. Le remède exact
(retrait sec, ou remplacement par `widefat`, qui vaut `width: 100%` — `wp-admin/css/common.css:464`)
est **arrêté par la mesure** et non par le raisonnement : les deux sont essayés dans le DOM, et l'on
retient celui qui supprime le débordement aux deux largeurs **sans** rendre les champs trop étroits
pour être utilisables. Le résultat de cet essai est consigné en §4.

### 3.2 Les libellés de choix soudés au bouton du choix suivant

**Symptôme mesuré** : groupe « Sexe », fenêtre de 1 440 — le libellé « Non renseigné » finit à
`x = 302,8` et le bouton radio de « Mâle » commence à **`x = 302,8`**. **L'écart vaut zéro.**
`display: inline`, `margin: 0`, sur **14 `.mtb-champ__choix`** répartis sur au moins quatre groupes
(Sexe, Variété, Statut, ADN identifié). L'œil apparie donc chaque bouton au **libellé précédent** :
c'est un contresens de lecture, pas une inélégance.

**Cause** : `chien/ecran.php`, `rendre_radios()` et `rendre_statut()` émettent les
`<label class="mtb-champ__choix">` **consécutivement, sans aucun séparateur**, dans une boucle
`printf`. `label` étant en ligne, rien ne les sépare.

**Correction retenue — et elle n'est pas celle que j'avais prescrite.** J'avais imposé le `<br />` du
cœur (`wp-admin/options-reading.php:186-187`). `dev-back-mtb` l'a écarté sur mesure et il a eu raison :
un simple retour à la ligne empile les choix mais laisse un pas vertical de l'ordre de la hauteur du
texte — **17 px**, soit **sous le seuil de 24 px** du §4.3, donc une correction qui aurait échoué la
vérification qu'elle devait passer.

**La forme retenue est `<p><label class="mtb-champ__choix">…</label></p>`, un choix par paragraphe**,
et ce n'est pas une invention : **c'est l'idiome que ce dépôt emploie déjà** pour ses boutons radio, à
`portee/ecran.php:227` et `:256` (`echo '<p><label>';`). Le paragraphe apporte ses marges de 13 px, et
c'est ce qui écarte deux cibles voisines assez pour qu'elles ne se recouvrent pas.

**C'est la quatrième fois de ce lot qu'une consigne du lead est corrigée par une mesure**, et la
quatrième fois que la mesure a raison. Consigné comme tel plutôt que lissé.

**Réserve levée par la mesure** : voir §4.3, seuil et verdict.

### 3.3 Les trois commandes soudées de la galerie Chien

**Symptôme** : `chien/ecran.php:637`, `:641`, `:646` rendent trois `<button class="button-link …">`
sans le moindre écart — « Retirer la photo 1Monter la photo 1Descendre la photo 1 ».

**Cause, lue dans le cœur** : `wp-includes/css/buttons.css:194-196` définit `.button-link` avec
`margin: 0; padding: 0;`. Zéro rembourrage, zéro marge : la cause est littéralement là.

**Correction** : `button-link` → `button`, **la classe que l'écran jumeau emploie déjà** — la Portée
rend ses commandes de galerie en `class="button"` (`portee/ecran.php:369`, `:578`), mesuré à
144,7 × **30 px**. Ce que le cœur donne alors, sans que nous écrivions une ligne
(`wp-includes/css/buttons.css:49-52` et `:347-360`) :

| | Au-dessus de 782 px | **Sous 782 px** |
|---|---|---|
| `min-height` | 30 px | **40 px** |
| `padding` | 0 10px | 0 14px |
| `margin-bottom` | — | **4 px** |

Le cœur fournit donc lui-même des cibles de 40 px séparées de 4 px **sur la largeur que le brief met
sous tension**. Aucune feuille de notre main ne revendiquerait cela aussi proprement.

### 3.4 Où la correction doit tomber — le point qui décide de tout sur l'écran Chien

**`galerie.js` est l'auteur réel du balisage que l'éleveuse voit.** La fonction anonyme de
`chien/galerie.js` se termine par `lireEtatInitial(); dessiner();` (l. 245-246), et `dessiner()`
**vide entièrement le `<ul>`** (`while ( liste.firstChild ) liste.removeChild( … )`, l. 65-67) puis
reconstruit chaque `<li>` et chaque bouton par `bouton()` (l. 41-49).

**Conséquence, et elle est sévère** : sur l'écran normal, le balisage rendu par `chien/ecran.php` est
**détruit à l'instant du chargement**. Une correction posée **uniquement côté PHP** ne changerait
donc **rien du tout** à ce que voit l'éleveuse, tout en passant une relecture de code sans encombre.
C'est exactement la forme de correction qui se déclare livrée et ne livre rien.

**Donc, sur l'écran Chien, la correction 3.3 tombe d'abord dans `galerie.js:45`**
(`element.className = 'button-link ' + classe`), et le PHP est aligné **ensuite**, pour que le rendu
initial et le cas dégradé disent la même chose que le cas normal. **Les deux moitiés vont ensemble ;
l'oubli de la moitié JS ne se voit pas dans une revue de PHP.**

**Corollaire consigné, non traité ici** : un balisage serveur systématiquement détruit au chargement
est une anomalie en soi — c'est du code rendu pour rien dans le seul cas qui compte. Ce n'est ni une
panne ni l'objet de #32 ; c'est une dette à ouvrir, et elle est nommée ici pour qu'elle ne se perde
pas.

---

## 4. Formes de balisage gelées

### 4.1 Les trois formes retenues

| # | Fichier | Ce qui change |
|---|---|---|
| 3.1 | `includes/fields/portee/ecran.php` — `rangee_chiot()` | `class="regular-text"` → **`class="widefat"`** sur les trois champs texte **et sur le `<select>`** |
| 3.2 | `includes/fields/chien/ecran.php` — `rendre_radios()`, `rendre_statut()` | chaque `<label class="mtb-champ__choix">` enveloppé d'un **`<p>`** — idiome déjà employé à `portee/ecran.php:227` et `:256` |
| 3.3 | `includes/fields/chien/galerie.js` l. 45 **puis** `includes/fields/chien/ecran.php:637,641,646` | `button-link` → `button`, **dans cet ordre de priorité** |

**Le remède 3.1 a été désigné par la mesure, et le concurrent a été éliminé par elle** — c'est le
point où l'instinct se serait trompé :

| Cas | `scrollWidth` / fenêtre | Tableau | Champ « Nom » | Débordants |
|---|---|---|---|---|
| **1440 avant** | 1 520 / 1 440 | 1 325,5 | 350 | 1 |
| 1440, retrait sec de `regular-text` | 1 440 / 1 440 | 912 | 177 | 0 |
| **1440 avec `widefat`** | **1 425 / 1 440** | 897 | 187,8 | **0** |
| **360 avant** | 1 511 / 360 | 1 488,5 | 400 | 3 |
| 360, retrait sec de `regular-text` | **920 / 360** | 897,5 | 203 | **3 — toujours en défaut** |
| **360 avec `widefat`** | **355 / 360** | 297 | 35,6 | **0** *(reste `span.display-name`, du cœur)* |

**Le retrait sec échoue à 360 px** : sans classe, un `<input>` garde sa largeur intrinsèque, et trois
champs plus un sélecteur ne descendent pas sous ~900 px. **`widefat` (`width: 100%`) laisse au
contraire les champs épouser leur cellule**, et le tableau tombe à 297 px. C'est pourquoi le `<select>`
la reçoit aussi : sans elle il serait resté le dernier élément incompressible de la rangée.

**Le prix, dit franchement** : à 360 px les champs deviennent étroits — Nom 35,6 px, N° LOF 28,6 px,
Devenir 51,8 px. Ils restent atteignables, focalisables et défilants, mais **saisir dix chiots sur un
téléphone reste pénible**. Ce n'est pas le cas d'usage principal — la saisie se fait au bureau, où les
champs mesurent 187,8 px — et l'alternative a été mesurée puis écartée : voir §4.5.

**Ce que la correction 3.1 récupère vraiment** : à 1 440 px, le bord droit de la case « Retirer ce
chiot » passe de **1 485 px** (hors d'un écran de 1 440) à **998 px**. La commande qui retire un chiot
**redevient atteignable sans faire défiler la page de côté**. C'est le gain le plus concret de l'issue.

### 4.2 Ce qui NE change pas — et pourquoi c'est une décision, pas un oubli

- **La structure `<ul><li>` des deux galeries est conservée.** La conversion en
  `<table class="widefat">` a été instruite et **écartée** : voir §4.4.
- **L'écran Résultat de travail n'est pas touché.** Aucun défaut mesuré, aucun débordement.
- **Le débordement de 10 px de l'écran Chien à 360 px n'est pas corrigé** : les deux seuls éléments
  fautifs mesurés sont `span.display-name` et `a.skiplink`, **du chrome de WordPress**. Ni notre
  balisage, ni notre périmètre.

### 4.3 La mesure SC 2.5.8 — seuil et lecture arrêtés d'avance

Après pose du `<br />`, sur `post-new.php?post_type=mtb_chien`, groupe **Statut** (5 choix, le plus
long), relever le `getBoundingClientRect()` des cinq `<label class="mtb-champ__choix">` et en tirer
**la distance centre à centre verticale entre deux `<label>` consécutifs**, à **360 px** puis à
**1440 px**.

**VERDICT — mesuré après correction, sur le groupe Statut (5 choix) :**

| Largeur | Pas centre à centre | Seuil | Résultat |
|---|---|---|---|
| **1440 px** | **34,5 px** (écarts 34,5 · 34,5 · 34,5 · 34,5) | ≥ 24 px | **conforme** |
| **360 px** | **36,3 px** (écarts 36,4 · 36,3 · 36,4 · 36,4) | ≥ 24 px | **conforme** |

Hauteur de chaque libellé : 17 px aux deux largeurs — donc **la cible elle-même reste sous 24 px, et
c'est l'espacement qui la rachète**, exactement le mécanisme que SC 2.5.8 prévoit. La réserve est
levée, et elle est levée **par la mesure**, pas par l'argument. 14 `.mtb-champ__choix` sur l'écran.

Barème d'origine, conservé pour mémoire :

- **≥ 24 px aux deux largeurs** → l'exception d'espacement de SC 2.5.8 est satisfaite, la réserve est
  levée, et ce contrat l'écrit comme **mesuré**.
- **< 24 px à l'une des deux** → **#32 ne peut pas être close en l'état.** Remontée immédiate à
  `/lead-mtb`, sans contournement : écarter deux cibles demanderait une règle d'espacement, donc une
  feuille d'administration, donc l'arbitrage que le §2 ferme. La sortie éventuelle — passer certains
  groupes en `<select>`, comme le fait `resultat/ecran.php` — **modifie ce que l'éleveuse manipule et
  ce que le guide décrit** : c'est une décision d'usage, elle ne se prend pas dans cette chaîne.
  *(Note : « Statut » ne s'y prêterait pas — ses libellés portent le `<span class="mtb-champ__libelle-accorde">`
  que lit `chien/statut.js` et qu'un `<option>` ne peut pas héberger.)*

### 4.4 La conversion des galeries en tableau — instruite, écartée, et le dossier laissé ouvert

Convertir les deux `<ul>` en `<table class="widefat">` était le chemin le plus attirant : une cellule
de tableau est alignée `vertical-align: middle` par défaut, ce qui corrigerait d'un coup le désordre
d'alignement de la galerie Portée — les commandes en haut de la rangée, le rang et la case de retrait
en bas de la vignette.

**Elle est écartée, et voici le prix exact qu'elle demandait**, trois pannes de la même famille : elles
cassent une fonction **sans rien casser à l'écran**, donc une vérification qui regarde la page les
déclare toutes les trois conformes.

| # | Point de casse | Effet |
|---|---|---|
| 1 | `portee/ecran.js:155` — `createElement( 'ul' )` puis `innerHTML` contenant un `<tr>` | hors contexte de tableau, le parseur **jette** le `<tr>` ; `firstElementChild` vaut `null`, sortie l. 161 : **ajouter une photo ne fait plus rien**, sans erreur |
| 2 | `portee/ecran.js:222` — `closest( 'li' )` | rend `null` sur un `<tr>`, sortie l. 224 : **Monter et Descendre meurent en silence**, boutons visibles et actifs |
| 3 | `chien/galerie.js:57-98` — `dessiner()` | à réécrire entièrement, sur la moitié JS qui est déjà l'auteur réel du balisage (§3.4) |

Les deux premières ont un remède connu et déjà présent dans le dépôt — `createElement( 'tbody' )`,
motif employé à `portee/ecran.js:75-76`, et `closest( 'tr' )`. **Le `<template>` n'est pas un piège** :
le mode « in template » du parseur conserve un `<tr>` nu, et `portee/ecran.php:362` en est déjà la
preuve dans le dépôt.

**Pourquoi on ne les paie pas** : ce que la conversion achète est un **alignement**, c'est-à-dire une
tenue visuelle — aucun manquement de conformité n'en dépend, chaque commande restant nommée sans
ambiguïté (« Monter la photo 2 »). Ce qu'elle coûte est un risque de panne **silencieuse** sur la
fonction que l'éleveuse emploie le plus, dans du code sans tests. Et l'utilisateur a explicitement
accepté le rendu en lignes (§7) — or **la structure actuelle rend déjà une photo par ligne** : la
conversion ne changerait pas le nombre de rangées, seulement leur tenue.

**Le dossier est laissé ouvert et complet** : une chaîne future qui voudra cette conversion trouvera
ici les trois points de casse nommés et leurs remèdes, et n'aura pas à les redécouvrir.

### 4.5 Le repli responsive du cœur — essayé, mesuré, écarté

Pour rendre les champs confortables à 360 px, une piste sérieuse existait : le **mécanisme de repli
que WordPress applique à ses propres tableaux**, piloté par le balisage et non par nos règles.
`wp-admin/css/list-tables.css:1953-1962` porte, sous media query,
`.wp-list-table tr … td:not(.column-primary)::before { content: attr(data-colname); }`. Il demande
`class="wp-list-table widefat"`, un `data-colname` par `<td>`, une cellule `column-primary`, et un
`<button class="toggle-row">` par rangée.

**Il a été essayé dans le DOM, à 360 px, dans les deux états** — et voici ce que la mesure donne :

| État | Cellules visibles | Largeur d'un champ | Hauteur d'une rangée |
|---|---|---|---|
| **replié** *(état par défaut)* | **Nom seule** — Sexe, N° LOF, Devenir et « Retirer ce chiot » sont en `display: none` | 235 px | 56 px |
| **déplié** *(après clic sur le bouton)* | les cinq, en `display: block`, étiquette en `::before` | 183,8 à 235 px | **225 px** |

**Écarté, et pour un motif d'usage, pas de goût.** Déplié, le résultat est excellent — c'est de loin
le meilleur rendu à 360 px. Mais **l'état par défaut est replié**, et il cache **quatre champs de
saisie sur cinq** derrière un bouton que l'éleveuse doit découvrir puis actionner **rangée par
rangée** — dix fois pour une portée de dix chiots. Ce mécanisme est conçu pour des tableaux qu'on
**lit**, où masquer une colonne secondaire est sans conséquence ; sur un tableau qu'on **remplit**,
un champ masqué par défaut est un champ qu'on ne remplit pas. S'y ajoute une interaction nouvelle que
le guide devrait décrire.

**Le dossier est laissé ouvert ici aussi** : si la saisie sur téléphone devenait un usage réel, c'est
cette piste qu'il faudrait reprendre — elle fonctionne, elle est mesurée, et elle ne coûte aucun CSS.

---

## 5. Les classes qui restent sans règle — et deux qui doivent le rester

Vingt classes `mtb-*` sont émises par les deux écrans sans qu'aucune règle ne les vise. **C'est un
état normal et non un oubli** : ce sont des crochets, et `issue-1.md:238-240` autorise explicitement
un module à rendre « une structure et des crochets de classes ». Trois seulement portaient un défaut
mesuré ; ce contrat les traite au §3.

**Deux doivent rester sans règle par nécessité, et non par négligence :**

- **`.mtb-portee-outil`** — `portee/ecran.php:364-368` l'explique : l'enveloppe est un `<span hidden>`
  **précisément parce que** la feuille du cœur impose `display: inline-block` à `.button`, ce qui
  l'emporterait sur `[hidden]`. Toute règle d'affichage y **ressusciterait un bouton sans effet**.
- **`.mtb-champ__libelle-accorde`** — crochet de `chien/statut.js`, qui échange le libellé masculin et
  féminin du statut. Il ne porte rien de visuel.

Une chaîne future qui « rangerait » ces deux classes casserait l'écran sans que rien ne le signale.

---

## 6. Interdits — reconduits, et un ajouté

- L'extension n'émet **aucune règle visuelle ni mise en page** — `issue-1.md:238-240` et `:497`,
  `issue-3.md:528`, `issue-4.md:513`, `issue-5.md:405-411`. **Cette issue ne les amende pas, elle les
  applique.**
- Aucun attribut `style=`, aucune balise `<style>`, aucune couleur, aucune dimension écrite par
  l'extension — **y compris une largeur ou une hauteur portée par un attribut de présentation**.
- Le thème n'est pas touché. **Aucun fichier sous `wp-content/themes/` n'entre dans l'empreinte de
  #32**, donc aucun `*.min.css` n'est régénéré et `make css` n'a pas à être joué. Les 15 paires
  restent 15 et `make css-check` reste à 0 sans que cette issue y touche.
- `mtb-core.php` et `includes/class-loader.php` restent fermés (`issue-1.md:498-500`).

### Le contrôle honnête

> **Aucune feuille de style d'administration n'existe, ni dans le thème ni dans l'extension. La seule
> feuille que porte `mtb-core` reste `includes/blocks/galerie-photos/editeur.css`, servie à la toile
> de l'éditeur et jamais au visiteur — la dérogation résiduelle bornée par #34 §11.**

Ce contrôle se vérifie par `find wp-content/plugins/mtb-core -name '*.css'`, qui doit rendre
**exactement un** chemin, et par l'absence de tout `admin*.css` sous `wp-content/themes/mtb/assets/css/`.

---

## 7. Le niveau de finition — tranché par l'utilisateur, pas par la chaîne

**Une photo par ligne suffit. La planche-contact n'est pas demandée.**

C'est une décision d'usage, prise le 2026-09-02 par l'utilisateur, sur présentation des deux rendus
et de leurs coûts. Elle a été prise en connaissance des deux termes : **dix photos font dix lignes à
faire défiler**, et l'autre rendu coûtait une dérogation au contrat plus une feuille hors du seul
contrôle automatique du projet.

**Ce que cette décision règle**, et c'est la seule chose qui n'appartenait pas à cette chaîne : le
niveau d'ambition visuelle. L'austérité n'est plus un défaut à compenser ni un renoncement à
justifier — **c'est le livrable demandé**. Le rendu en lignes n'est donc pas un pis-aller retenu
faute de mieux ; il est ce qu'on a choisi de faire.

**Ce qu'elle ne règle pas** : le chemin. Elle n'interdit pas d'écrire du CSS, elle interdit d'en
écrire **pour obtenir une planche-contact**. Si un défaut de conformité subsistait que le balisage du
cœur ne sait pas corriger, la question du domicile se rouvrirait **pour ce résidu seulement** — et
elle se retrancherait sur les arguments du §2, pas sur cette décision d'usage.

**Conséquence sur la checklist de l'issue, à porter au board et non à cocher** : la tâche 2 demande
« au minimum une **disposition en grille** pour les deux galeries ». Ce n'est pas ce qui est livré, et
ce n'est plus ce qui est voulu. **La tâche est à amender, pas à cocher.**

**Le chiffre du renoncement, écrit quand même** : à 1440 px une photo occupe une ligne de 912 px dont
elle emploie 150 ; dix-huit photos font 2 907 px ; une grille de cinq par rangée les ramènerait à
environ 700 px. Ce facteur quatre est connu, mesuré, et volontairement non pris. Il est écrit ici pour
qu'une chaîne future ne le redécouvre pas comme un défaut à réparer.

### 7.1 La seconde décision d'usage — l'étroitesse des champs à 360 px

Même forme, même auteur, même statut. Après la correction 3.1, le tableau des chiots tient dans la
page à 360 px, mais **ses champs y deviennent étroits** : `<select>` Sexe **37 px**, champs texte
**28 à 52 px**. Ils restent atteignables, focalisables et défilants ; ils sont pénibles à relire.

Le remède existait, il a été mesuré, et il est décrit au §4.5 : le repli responsive du cœur. Son prix
mesuré — **quatre colonnes sur cinq masquées** sous 782 px, un dépliage à actionner **rangée par
rangée**, une rangée dépliée à 225 px, et **aucun effet au-dessus de 782 px** — a été présenté à
l'utilisateur avec ces chiffres.

**Il a choisi de livrer tel quel, et n'a pas demandé d'issue de dette.** Ce point est donc **écarté
par décision d'usage**, exactement comme la compacité des galeries — et il ne doit **pas** être
consigné comme une dette ouverte. La saisie se fait au bureau, où les mêmes champs mesurent 187,8 px.

---

## 8. Séquencement — un fait neuf pour le board

- **#32 est indépendante de #48, #42, #26, #24 et #23** : aucune empreinte commune.
- **#32 doit passer AVANT #25.** #25 assemble le guide à partir des 122 captures de
  `docs/guide/captures/` ; #32 en périme plusieurs (§9). Assembler le guide d'abord obligerait à le
  refaire.
- **Le motif d'urgence écrit dans le corps de l'issue est périmé.** L'issue dit qu'il faut trancher
  « avant la reprise de contenu, sans quoi Fabienne saisira 27 portées et 17 fiches sur des écrans non
  mis en page ». **Cette reprise a déjà eu lieu** : `docs/guide/contenu-repris-de-l-ancien-site.md`
  écrit « Vos 27 portées et vos 17 fiches de chiens sont là, dans le nouveau site, déjà en ligne », et
  aucune des sept issues ouvertes n'est une issue `contenu`.
  **Le vrai motif est autre, et il est plus fort** : `docs/migration/portees-chiens.md` déclare que le
  site source ne renseignait que **2 textes alternatifs** sur 136 photographies, et que les autres
  sont composés — « une dette d'accessibilité déclarée ; seule l'éleveuse peut écrire ce que montre
  une photo ». **C'est sur ces écrans-là qu'elle devra reprendre 134 descriptions**, galerie par
  galerie. L'écran de saisie n'est pas un écran de création ponctuelle : c'est son plan de travail.

---

## 9. Ce que l'éditrice voit changer, et ce que le guide doit suivre

*(Complété après implémentation — voir le rapport de `doc-client-mtb`.)*

Captures candidates à la péremption, sur les 122 de `docs/guide/captures/` :
`portee-galerie.png`, `portee-mention-photo-principale.png`, `portee-chiots.png`, `chien-photos.png`,
`chien-identite.png`, `chien-sante.png`.

---

## 10. Arbitrages — chaque désaccord, la décision, sa raison

| # | Désaccord | Décision | Raison |
|---|---|---|---|
| 1 | Où vit le CSS d'administration : thème (A) ou extension (B) — les deux termes de la tâche 1 | **Ni l'un ni l'autre : aucun CSS (C)** | §2. Les deux écrans cassés sont les deux qui ont quitté le balisage du cœur ; le troisième, tenu au cœur par son contrat, n'a aucun défaut et ne déborde pas à 360 px |
| 2 | Si l'on devait choisir entre A et B seuls | **B** | L'argument du changement de thème est décisif contre A, et `MASTER.md` ne décrit aucune apparence d'administration : A ferait dépendre l'écran d'un thème qui n'a rien à en dire |
| 3 | B écartée pour quel motif exact | **`issue-4.md` ne porte pas de convention d'amendement** | §2.2. Ce n'est pas un jugement de goût sur la dérogation : c'est qu'elle ne peut pas être écrite là où l'interdit est gelé, et `issue-34.md` §18 interdit d'ouvrir un tel document |
| 4 | La mise en grille des galeries | **Non livrée — et non voulue** | §7. Tranché par l'**utilisateur** le 2026-09-02 : une photo par ligne suffit, dix photos font dix lignes, et il l'a accepté en voyant le coût de l'autre rendu. Ce n'est donc pas un renoncement de la chaîne. Le facteur quatre est écrit quand même, pour qu'on ne le redécouvre pas comme un défaut |
| 7 | Les trois boutons serveur de la galerie Chien seraient **inertes** (`ecran.php` n'écrit pas `data-mtb-position`, `galerie.js:206` sort sans lui) | **Voir §3 — mesuré, non déduit** | La lecture du code est exacte sur le balisage serveur, mais `galerie.js` se termine par `lireEtatInitial(); dessiner();` et `dessiner()` **vide le `<ul>` puis le reconstruit** : le balisage serveur est détruit au chargement. La conclusion « l'éleveuse clique et rien ne se passe » ne suit donc pas de la prémisse. Tranché à l'écran, pas sur lecture |
| 5 | Le débordement horizontal de l'écran Chien à 360 px (370 px pour 360) | **Non corrigé, et ce n'est pas un manquement de #32** | Les deux seuls éléments fautifs mesurés sont `span.display-name` (barre d'administration) et `a.skiplink` — **du cœur de WordPress**, pas de notre balisage |
| 6 | Le débordement de l'écran Portée | **Corrigé — il est le nôtre** | 1 488 px de tableau dans une fenêtre de 360. Cause mesurée : trois `.regular-text` à `width: 25em` dans une même rangée |

---

## 11. Points restés ouverts

| # | Question | Bloque | Qui tranche |
|---|---|---|---|
| **Q-a** | **Ouvrir un domicile légitime au CSS d'administration** — soit en dotant `issue-4.md` d'une convention d'amendement, soit en requalifiant son `:513` comme reformulation du contrat fondateur #1. Sans cela, aucune grille de galerie n'est possible, ni par cette issue ni par une autre | la mise en grille des galeries (§7), rien d'autre | **`/lead-mtb`** — décision de contrat, pas de style |
| **Q-b** | **La barre de cible tactile en administration.** `MASTER.md` §12.10 fixe « ≥ 44 px » et renvoie exclusivement à des sections du site public ; WCAG 2.2 AA demande 24 × 24 px. À quelle barre juge-t-on un écran d'administration ? Ce contrat se compare à **24 px (AA)** et signale les écarts à 44 px sans les traiter comme bloquants | rien ; change la qualification d'un défaut, pas sa correction | `/lead-mtb`, puis `lead-design-mtb` si `MASTER.md` doit le dire |
