# Style 5 — « Sauge et calcaire »

Habillage complet de mtbrabant.com. Deux copier-coller dans l'éditeur IONOS, rien d'autre.

| Fichier | Où le coller |
|---|---|
| `style5.css` | zone **CSS** |
| `style5.html` | zone **HTML (gabarit)** |
| `apercu.html` | à ouvrir dans un navigateur, ne touche pas au site |

Les mêmes fichiers sont recopiés à l'identique dans `../A-COLLER/` sous les noms
`5-sauge-et-calcaire.css` et `5-sauge-et-calcaire.html`.

**Le gabarit HTML est rigoureusement inchangé** (même empreinte MD5 que les cinq autres
directions : `8d2a78093be047c115404acfa66c01b3`). Tout est fait en CSS. Si tu changes un jour
de direction, tu n'as que la zone CSS à recoller.

---

## La direction

**Famille esthétique : botanique sévère**, du côté de l'herbier d'apothicaire — vert sauge
froid, pierre calcaire pâle, et **un seul** métal chaud, le laiton, employé avec parcimonie.
Aucun fond jaune, aucune terre cuite, aucun beige chaud : la chaleur ne vient que de l'accent
laiton, qui ne sert que pour les filets, les numéros de l'index et le trait de survol.

Titres en **serif transitionnelle**, données et navigation en **sans neutre**. Le contraste
entre les deux familles fait la hiérarchie ; la couleur n'y participe presque pas.

### Élément signature — le filet double

Un trait **sauge de 3 px** posé sur un trait **laiton de 1 px**. Il revient partout, et c'est
lui qui donne son unité à la page :

- sous le bandeau d'identité (le logo) ;
- sous le bandeau de navigation, sur toute la largeur ;
- sous le `h1`, sur toute la largeur, et sous chaque `h2`, en segment de 96 px ;
- à la place de chaque `<hr>` du contenu ;
- en haut du bloc de colonne latérale et en haut du pied de page.

Un seul jeton le porte : `--filet-double`. Le modifier une fois change tous les séparateurs
du site.

### Deuxième signature — les médaillons ronds

Les photos des reproducteurs et les vignettes de galerie sont des **pastilles circulaires**
cerclées de laiton. Voir « Grands médaillons » plus bas.

---

## Palette et contrastes mesurés

Ratios calculés selon la formule WCAG 2.1 (luminance relative), pas estimés à l'œil.

| Jeton | Valeur | Rôle |
|---|---|---|
| `--calcaire` | `#F2F1EA` | fond de page, colonne de gauche |
| `--calcaire-creux` | `#E7E5DA` | fonds creusés, survol du menu, pied de page |
| `--blanc` | `#FBFAF6` | papier du bloc de contenu |
| `--pin` | `#16241C` | encre pin profond : titres, page courante |
| `--texte` | `#22312A` | texte courant |
| `--texte-doux` | `#4A5A50` | texte secondaire |
| `--sauge` | `#4A6B57` | bandeau de navigation, filets, liens |
| `--sauge-fonce` | `#35513F` | survol du bandeau, liens de contenu |
| `--laiton` | `#9A7B3F` | filets, anneaux, cerne de focus — **jamais de texte** |
| `--laiton-texte` | `#7A5F2C` | laiton lisible en texte (numéros de l'index) |
| `--laiton-clair` | `#E3CB9C` | trait de survol sur fond sauge et sur fond pin |
| `--filet` | `#CFCDBF` | hachures purement décoratives |

**Sur `--calcaire` `#F2F1EA`** — fond de page et colonne de gauche

| Couleur | Ratio | Verdict |
|---|---|---|
| `--pin` | **14,23:1** | AAA |
| `--texte` | **12,04:1** | AAA |
| `--texte-doux` | **6,46:1** | AA (AAA en grand) |
| `--sauge-fonce` | **7,73:1** | AAA |
| `--laiton-texte` | **5,30:1** | AA |
| `--sauge` | **5,25:1** | AA |
| `--laiton` | **3,51:1** | non textuel seulement (filets, anneaux, focus) |

**Sur `--blanc` `#FBFAF6`** — bloc de contenu

| Couleur | Ratio | Verdict |
|---|---|---|
| `--pin` | **15,43:1** | AAA |
| `--texte` | **13,05:1** | AAA |
| `--texte-doux` | **7,01:1** | AAA |
| `--sauge-fonce` | **8,38:1** | AAA |
| `--laiton-texte` | **5,74:1** | AA |
| `--laiton` | **3,81:1** | non textuel seulement |

**Sur `--calcaire-creux` `#E7E5DA`** — pied de page, états survolés

| Couleur | Ratio | Verdict |
|---|---|---|
| `--texte` | **10,79:1** | AAA |
| `--sauge-fonce` | **6,92:1** | AAA |
| `--texte-doux` | **5,79:1** | AA |
| `--laiton` | **3,15:1** | non textuel seulement (cerne de focus) |

**Sur `--sauge` `#4A6B57`** — le bandeau de navigation

| Couleur | Ratio | Verdict |
|---|---|---|
| `--calcaire` (libellés) | **5,25:1** | AA |
| `--calcaire` (cerne de focus) | **5,25:1** | ≥ 3:1 exigé, largement tenu |
| `--laiton-clair` (trait de survol) | **3,76:1** | ≥ 3:1, indicateur non textuel |

**Sur `--pin` `#16241C`** — la page courante du bandeau

| Couleur | Ratio | Verdict |
|---|---|---|
| `--calcaire` (libellé) | **14,23:1** | AAA |
| `--laiton-clair` (trait permanent) | **10,20:1** | très largement tenu |

Le lavande `#9288C4` écrit en dur dans le contenu (3,0:1, échec AA) est rattrapé et remplacé
par `--texte-doux`.

### Polices

Piles système uniquement, **zéro requête vers un domaine tiers**, aucun fichier de police.

```
titres   "Sitka Text", Cambria, Charter, "Bitstream Charter",
         Baskerville, "Palatino Linotype", Georgia, serif
données  system-ui, "Segoe UI", -apple-system, BlinkMacSystemFont,
         Roboto, "Helvetica Neue", Arial, sans-serif
```

---

## Le bandeau horizontal en haut de page : comment il est obtenu

C'est le point technique de cette passe. Le CMS ne fournit qu'**un seul** marqueur,
`<var>navigation[1|2|3]</var>`, placé dans `#navigation`, lui-même dans `#sidebar` — la
colonne de gauche. Il faut donc que le niveau 1 sorte en bandeau horizontal pendant que les
niveaux 2 et 3 restent dans la colonne, sans toucher au gabarit et sans JavaScript.

### Le mécanisme

```css
#navigation ul.mainNav1        { display: flex; flex-wrap: wrap; }
#navigation ul.mainNav1 > li   { display: contents; }
#navigation ul.mainNav1 > li > a          { order: 1; }   /* le bandeau */
#navigation ul.mainNav1 > li > .diyfeDropDownSubList,
#navigation ul.mainNav1 > li > ul.mainNav2 { order: 2; flex: 0 0 100%; }
```

`display: contents` sur les `<li>` de niveau 1 fait disparaître la boîte du `<li>` : le lien
de niveau 1 **et** le sous-menu qu'il contient deviennent tous deux des éléments directs du
même conteneur flex. `order: 1` regroupe les sept liens sur la première ligne — le bandeau.
`order: 2` renvoie les sous-menus derrière, chacun sur une ligne entière.

Sur écran large, `ul.mainNav1` reçoit `width: 940px` alors que `#sidebar` n'en fait que 239 :
le bandeau déborde volontairement vers la droite sur toute la largeur de la page, tandis que
les sous-menus sont ramenés dans les 239 px de gauche par
`padding-right: calc(940px - 239px)`. Le contenu est décalé de la hauteur du bandeau par
`#content { margin-top: var(--barre); }`.

### Pourquoi pas le `position: absolute` prescrit

La technique envisagée au départ — `#container { position: relative; padding-top }` puis
`ul.mainNav1 { position: absolute; top: 0 }` — sort **tout l'arbre du menu** du flux, y
compris les 42 entrées de niveau 2, puisque le CMS les imbrique dans les `<li>` de niveau 1
(vérifié sur le site en production, voir ci-dessous). La colonne de gauche ne réserverait
alors plus aucune hauteur : sur une page courte comme « Placement » ou « Contact », le menu
passerait par-dessus le pied de page. Le montage en flux ci-dessus n'a pas ce défaut : la
hauteur du menu est comptée, `#container` grandit, le pied de page se place dessous.

**Conséquence assumée** : le bandeau se place **juste sous le bandeau d'identité (le logo)**,
et non au-dessus de lui. C'est la position habituelle d'une barre de navigation et elle est
robuste ; la placer au pixel zéro imposait un calage sur la hauteur exacte du logo, qui casse
si l'image ne charge pas.

### L'ambiguïté imbriqué / frère : les deux cas sont écrits

Le code source du site en production a été lu directement. **Le CMS imbrique** :

```html
<li><a class="level_1"><span>BHPL</span></a>
    <span class="diyfeDropDownSubOpener">&nbsp;</span>
    <div class="diyfeDropDownSubList diyfeCA diyfeCA3">
      <ul class="mainNav2"> … 29 entrées … </ul>
    </div></li>
```

Les deux sous-menus (BHPL, 29 entrées, et La meute, 13 entrées) sont émis **sur toutes les
pages**, pas seulement dans leur rubrique. La feuille couvre malgré tout les trois formes
possibles, sans qu'elles se gênent :

1. **Imbriqué avec enveloppe** — `ul.mainNav1 > li > div.diyfeDropDownSubList > ul.mainNav2`.
   C'est la forme réelle. Traitée par la règle `order: 2` ci-dessus.
2. **Imbriqué sans enveloppe** — `ul.mainNav1 > li > ul.mainNav2`. Même règle, même
   sélecteur groupé : la liste devient elle-même l'élément flex à pleine ligne et le
   `padding-right` la ramène dans les 239 px.
3. **Frère** — `#navigation > ul.mainNav2`. La liste n'est alors pas un élément flex de
   `ul.mainNav1` : elle se pose naturellement sous le bandeau, dans la colonne de 239 px, et
   les règles d'apparence (index numéroté, filets, états) s'appliquent à l'identique puisque
   toutes sont écrites sur `#navigation ul.mainNav2`, sans dépendre du parent.

Les sections de la feuille sont marquées :
`cas IMBRIQUÉ dans le <li> de niveau 1`, `cas FRÈRE de ul.mainNav1`, `apparence commune aux
deux cas`.

Comme le bandeau déborde à droite au-dessus de `#content`, `ul.mainNav1` reçoit
`pointer-events: none` et seuls les liens, les `<li>` de niveau 2 et ceux de niveau 3 le
réactivent : aucune zone invisible n'intercepte les clics dans le contenu.

---

## États du menu — jamais la couleur seule

| État | Signalé par |
|---|---|
| Niveau 1, survol | fond `--sauge-fonce` **+** un trait laiton clair de 3 px qui **se dessine de gauche à droite** (`transform: scaleX(0 → 1)`, 160 ms) |
| Niveau 1, page courante | fond `--pin` (nettement plus sombre) **+** graisse 700 **+** le même trait laiton, dessiné en permanence |
| Niveau 1, focus clavier | cerne calcaire de 3 px à l'intérieur de la case (5,25:1) |
| Niveau 2, survol | fond creusé **+** rail laiton de 3 px à gauche |
| Niveau 2, page courante | fond creusé **+** rail **sauge** de 3 px **+** graisse 700 **+** numéro passé en sauge foncé |
| Niveau 3, page courante | graisse 700 **+** encre pin |
| Focus, partout ailleurs | cerne laiton de 3 px, décalé de 2 px |

Le survol et le focus sont donc **visuellement distincts** l'un de l'autre, et la page
courante ne repose jamais sur la seule teinte.

---

## La colonne de gauche : un index, pas une liste

Le niveau 2 est composé comme l'**index d'un ouvrage** : chaque entrée est précédée de son
numéro d'ordre en chiffres à chasse fixe (`01`, `02`, … `29`), en laiton, aligné sur une
gouttière fixe. Les 29 portées se lisent alors comme un sommaire numéroté plutôt que comme
une longue énumération. Un intitulé discret « DANS CETTE RUBRIQUE » coiffe chaque sous-menu.

Le niveau 3 est décalé et introduit par un **crochet de laiton** dessiné en CSS pur (deux
bordures de 1 px), sans caractère typographique ni image.

Toutes les cibles tactiles font **au moins 44 px** de haut : 52 px pour le bandeau, 44 px
pour les niveaux 2 et 3, 44 px pour les liens du pied de page.

---

## Responsive

La feuille est écrite **mobile d'abord**. La mise en page fixe 940 px n'apparaît qu'à partir
de `@media (min-width: 1000px)`. Conséquence importante : si la requête média ne s'applique
pas, on retombe sur la mise en page empilée, où **tout reste visible**. L'inverse — un menu
caché par défaut qu'une requête média doit venir rendre visible — est exactement ce qui avait
cassé la passe précédente ; il n'y en a aucun ici.

Sous 1000 px, `#wrapper` passe en colonne flex et le menu est remonté **au-dessus** du
contenu (`order`), le bandeau se replie sur plusieurs lignes, la colonne et le contenu
prennent toute la largeur.

Mesures relevées dans un navigateur réel :

| Largeur | Résultat |
|---|---|
| 1280 px | conteneur 940 px, bandeau 940 × 52 px sur une ligne, colonne 239 px, contenu 700 px aligné sous le bandeau, **aucun débordement** |
| 700 px | empilé, bandeau sur 2 lignes (4 puis 3 entrées), `scrollWidth` = largeur de fenêtre |
| 480 px | empilé, bandeau sur 3 lignes |
| 345 px | empilé, bandeau sur 4 lignes, les 7 entrées présentes, `scrollWidth` = 345 = largeur de fenêtre, **aucun défilement horizontal** |

Aucune unité `vw`/`vh`, aucun `position: fixed`, aucune case à cocher, aucun `:checked`,
aucun JavaScript, aucun `@import`, aucun `url()` vers un domaine tiers.

---

## Impression

Feuille `@media print` complète : le menu est retiré, le logo réduit, la mise en page
dépliée sur une colonne, les couleurs ramenées au noir sur blanc, les filets doubles
remplacés par des traits simples, les adresses des liens externes ajoutées entre parenthèses,
les coupures de page évitées dans les images, les tableaux et les citations.

Vérification : la page d'accueil complète sort en **3 pages** (elle en ferait une douzaine si
les 42 entrées de menu étaient imprimées).

---

## Grands médaillons : la seule ligne à changer

Les vignettes du module Galerie font aujourd'hui environ **25 px** de haut : au-delà de 44 px
un cercle devient flou. Les pastilles sont donc livrées à 44 px, nettes.

Pour obtenir de grands médaillons **nets**, dans cet ordre :

1. Dans l'éditeur, ouvrir une page contenant une galerie, sélectionner le bloc, porter la
   taille des vignettes à environ 200 px dans les réglages du module, enregistrer, publier.
2. Vérifier sans se fier au nom du réglage : clic droit sur une vignette publiée → « ouvrir
   l'image dans un nouvel onglet » → la taille annoncée doit être passée d'environ 25 px à
   200 px. Tant qu'elle annonce 25, ne rien changer au CSS.
3. À refaire galerie par galerie : le réglage n'est pas global.
4. **Alors seulement**, dans la zone CSS, changer **une seule ligne**, dans le bloc `:root`
   en haut de la feuille :

```css
  --medaillon: 44px;      /*  →  --medaillon: 200px;  */
```

C'est la seule propriété à toucher. Changer le CSS avant l'étape 1 donne de grands cercles
flous.

Les photos de reproducteurs de la page d'accueil ne passent pas par la galerie : ce sont des
images posées avec un `style="max-width: 253px"` écrit dans l'éditeur. Elles sont déjà
cerclées à 240 px et restent nettes.

---

## Ce que la feuille rattrape dans le contenu

Le contenu tapé dans l'éditeur contient des styles écrits en dur. Sont neutralisés :

- les balises `<font>` et les `face=` (dont Comic Sans) → ramenés aux piles du site ;
- les textes en 10, 11 et 12 px → ramenés à 14 px ;
- le lavande `#9288C4` (3,0:1, échec AA) → remplacé par `--texte-doux` (6,46:1) ;
- le faux titre en 36 px de la page d'accueil → composé comme un vrai titre d'affiche ;
- les modules Facebook et le compteur de visites → masqués.

---

## Risques connus, à vérifier une fois collé

1. **`display: contents` sur les `<li>` de niveau 1.** C'est la clé du bandeau. Les navigateurs
   à jour (Chrome ≥ 89, Firefox ≥ 91, Safari ≥ 15.4) conservent la sémantique de liste pour
   les lecteurs d'écran ; sur un navigateur plus ancien, les liens restent des liens mais la
   liste peut ne plus être annoncée comme telle. Aucun contenu n'est perdu.
2. **Débordement du bandeau hors de `#sidebar`.** Si la feuille d'usine IONOS applique un
   `overflow: hidden` à `#sidebar`, `#wrapper` ou `#container`, le bandeau serait rogné à
   239 px. La feuille force `overflow: visible` sur les trois, mais avec un poids ordinaire
   sur `#container` et `#wrapper` — à contrôler à l'œil dès le collage : **le bandeau doit
   aller d'un bord à l'autre**.
3. **Longueur des libellés de niveau 1.** Sur écran large les sept entrées tiennent sur une
   ligne sans retour (mesuré : 939 px pour 940 disponibles). Si une huitième page de niveau 1
   est ajoutée, le bandeau se met à déborder ; il faudra réduire le `letter-spacing` ou
   passer les libellés sur deux lignes.
4. **Colonne de menu très haute sur mobile.** Les 42 entrées de niveau 2 sont dépliées en
   permanence, donc au-dessus du contenu sur petit écran il faut faire défiler longtemps.
   C'est le prix de la règle « on ne cache jamais le menu » ; c'est le point à retravailler
   en priorité si la direction est retenue.
5. **`<var>sidebar</var>` vide.** Le site en production n'a aujourd'hui aucun module de
   colonne latérale. Le bloc `#sidebar_content` est stylé et se placera sous le menu, mais
   son rendu réel n'a pas pu être observé.
6. **Ce que le style ne peut pas corriger** (à traiter dans l'éditeur) : l'iframe Google Maps
   et le bouton Facebook, qui chargent des domaines tiers même masqués ; l'absence de `<h1>`
   sur la page d'accueil ; l'absence de texte alternatif sur les photos de galerie ; le lien
   « Lof Select » de la fiche Tesla coupé en deux balises ; le `maximum-scale=1` du viewport
   qui bloque le zoom.
