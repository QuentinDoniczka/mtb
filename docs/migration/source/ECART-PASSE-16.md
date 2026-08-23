# Ce que les 10 fichiers de la passe #16 ont perdu

**Ce fichier est une mesure, pas une correction.** Il chiffre, fichier par fichier, l'écart entre les
dix `.md` produits par la passe #16 (découpage en 3 zones) et ce que la convention gelée de l'issue
#19 (§3 de `docs/contracts/issue-19.md`, découpage en 5 zones) rend du **même HTML archivé**. Les dix
fichiers mesurés **restent inchangés** : le §9 du contrat interdit de réécrire, renommer ou supprimer
un fichier existant de ce dossier, et la décision 46 d'`ETAT.md` veut qu'un écart soit *déclaré*, pas
effacé. Aucun mot, aucune date, aucun n° LOF n'a été corrigé ici — ni dans les fichiers mesurés, ni
dans les extraits recopiés ci-dessous.

Ce fichier ne remplace pas non plus `README.md` (convention #16) ni `pages/README.md` (convention
#17). Il dit seulement, pour #20 et #21, **s'il faut re-dériver un fichier avant d'importer**.

## Comment la mesure a été faite

Pour chacun des dix, l'URL a été **re-dérivée depuis le HTML archivé** de `html/` avec
`outils/reduire.py` — sans réseau — puis comparée au fichier commité :

```
python outils/reduire.py html/<slug>.html
```

Trois comparaisons, dans cet ordre :

1. **mot à mot**, sur les trois zones que les deux conventions ont en commun (Contenu principal,
   Colonne latérale, Pied de page), espaces et U+00A0 normalisés ;
2. **ligne à ligne**, pour distinguer une ligne réellement absente d'une ligne seulement recoupée ;
3. **adresse par adresse**, sur les `src=` et les `href=`.

### La précaution qui décide de la validité de la mesure

Le HTML de `html/` a été récupéré le **2026-08-23**, les `.md` de la passe #16 le **2026-08-20**. Une
ligne présente dans la re-dérivation et absente du `.md` pourrait donc avoir été **ajoutée au site
entre les deux dates** au lieu d'avoir été perdue par la convention. Trois constats séparent les deux
causes :

- **La taille du HTML est identique à l'octet sur 9 fichiers sur 10** (en-tête du `.md` du 20 août
  contre `html/RELEVE.md` du 23 août). Seul `accueil` diffère, de **2 octets** — voir la ligne du
  tableau et les anomalies.
- **Chaque page servie porte son horodatage de cache IONOS** (`<!-- rendered at … -->`), et les dix
  sont datés des **15 et 16 août 2026**, donc **antérieurs aux deux requêtes**. Le rendu servi le
  23 août est celui qui était déjà servi le 20.
- **La comparaison mot à mot ne trouve, sur aucun des dix, un mot rédactionnel présent d'un côté et
  absent de l'autre.** Aucune ligne de contenu n'a donc à être imputée à un changement du site.

**Conséquence : aucune des pertes chiffrées plus bas n'est un ajout du site depuis le 20 août.**

### Ce que « perdu » veut dire ici, et ce qu'il ne veut pas dire

La mesure distingue trois choses, qui ne coûtent pas la même chose à l'import :

| | Ce que c'est |
|---|---|
| **Ligne avalée** | Une ligne du document dont le seul contenu est une U+00A0. Le §3.2.1 du contrat en fait du contenu ; la réduction des lignes vides de la passe #16 la supprime. |
| **Zone absente** | `diywebEmotionHeader` et `diywebSecondary` ne sont pas découpées par la passe #16. Tout ce qu'elles portent est absent du `.md`. |
| **Coupure fantôme** | Un retour à la ligne que le `.md` de la passe #16 porte **au milieu d'une phrase** et que la convention #17 ne conserve pas, parce qu'il vient d'un **retour à la ligne littéral du texte HTML**, pas d'un `<br>`. Le navigateur l'affiche comme une espace ; le `.md` l'affiche comme une coupure. C'est le seul écart qui **ajoute** quelque chose au lieu d'en retirer, et c'est celui qui peut faire saisir une valeur fausse. |

**Aucun mot, aucune date, aucun nom, aucun n° LOF, aucun résultat de test n'est absent d'aucun des
dix fichiers.** Vérifié mot à mot sur les trois zones communes : la seule matière absente est celle
des deux zones non découpées, les lignes d'U+00A0, et — sur `travail.md` — l'**association** des
cellules de tableau.

### La règle de verdict, énoncée avant d'être appliquée

> **« à recapturer avant import »** si le fichier, lu tel quel et selon la consigne que porte son
> propre `README.md`, conduirait à **saisir une valeur différente de ce que le visiteur lit** :
> association de tableau rompue, ou coupure fantôme tombant dans un **nom, une date, un nombre ou un
> résultat de test**.
> **« utilisable tel quel »** sinon — y compris quand des lignes d'U+00A0 ont été avalées ou quand
> une coupure fantôme tombe entre deux mots de prose ordinaire.

---

## Le tableau des dix

| Fichier | Zones perdues | U+00A0 déclarées / réelles | Lignes de contenu perdues | Associations de tableau éclatées | `[IMAGE]` / `[LIEN]` perdus | Document modifié depuis ? | **Verdict** |
|---|---|---|---|---|---|---|---|
| `accueil.md` | `diywebEmotionHeader` (3 l.) · `diywebSecondary` (vide dans le document) | **6 / 22** | **14** lignes d'U+00A0 + **3** l. du bandeau. Aucune ligne de texte. **4 coupures fantômes**, dont une dans un nom (voir plus bas) | non (0 `<table>` dans les cinq zones) | 1 `[IMAGE]` : `…/s/img/emotionheader.jpeg?1786776796.920px.313px` (2 occurrences, 1 adresse). 0 `[LIEN]` perdu, **2 tronqués** | **oui, 2 octets** : 47 384 o déclarés le 20/08, 47 382 o archivés le 23/08. Aucun mot ne diffère ; cache `rendered at 15 Aug 2026 21:23:45` | **à recapturer avant import** |
| `la-meute.md` | `diywebEmotionHeader` (3 l.) · `diywebSecondary` (22 l., dont **13 absentes** de la Colonne latérale) | **0 / 3** | **2** lignes d'U+00A0 + **3** l. du bandeau + **13** l. de sous-menu. Aucune ligne de texte, **0 coupure fantôme** | non | 1 `[IMAGE]` (bandeau) · **13 `[LIEN]`** : le sous-menu « La meute » (`very-best`, `you`, `tesla`, `rolex`, `pégaz`, `jango`, `opium`, `grocky`, `etch`, `happy`, `maya`, `tara`, `gribouille`). **2 tronqués** | non — 34 317 o des deux côtés | **utilisable tel quel** |
| `travail.md` | `diywebEmotionHeader` (3 l.) · `diywebSecondary` (9 l., **toutes** déjà dans la Colonne latérale) | **47 / 136** | **28** lignes d'U+00A0 + **3** l. du bandeau. Aucune ligne de texte. **10 coupures fantômes** (prose) | **oui — 67 lignes**, dont **57 lignes de résultats** (année / chien / niveau), 7 lignes d'intitulé de discipline, 3 lignes vides. 207 cellules dans `diywebMain` | 1 `[IMAGE]` (bandeau) · 0 `[LIEN]` perdu, **2 tronqués** | non — 63 623 o des deux côtés | **à recapturer avant import** |
| `portees/portee-m-2016.md` | `diywebEmotionHeader` (3 l.) · `diywebSecondary` (38 l., dont **29 absentes** de la Colonne latérale) | **1 / 8** | **4** lignes d'U+00A0 + **3** l. du bandeau + **29** l. de sous-menu. Aucune ligne de texte, **0 coupure fantôme** | non | 1 `[IMAGE]` (bandeau) · **29 `[LIEN]`** : le sous-menu « BHPL » (27 portées + `bhpl-en-france` + `littérature`). **2 tronqués** | non — 39 765 o des deux côtés | **utilisable tel quel** |
| `portees/portee-u2-2023.md` | idem (`diywebSecondary` 38 l., 29 hors latérale) | **2 / 7** | **4** lignes d'U+00A0 + **3** + **29**. Aucune ligne de texte. **1 coupure fantôme, dans une ligne de résultats de santé** | non | idem `portee-m-2016` | non — 38 362 o des deux côtés | **à recapturer avant import** |
| `portees/portee-j-2014.md` | idem (`diywebSecondary` 38 l., 29 hors latérale) | **1 / 7** | **4** lignes d'U+00A0 + **3** + **29**. Aucune ligne de texte, **0 coupure fantôme** | non | idem `portee-m-2016` | non — 37 310 o des deux côtés | **utilisable tel quel** |
| `chiens/chien-jango.md` | `diywebEmotionHeader` (3 l.) · `diywebSecondary` (22 l., dont **13 absentes** de la Colonne latérale) | **4 / 20** | **13** lignes d'U+00A0 + **3** + **13**. Aucune ligne de texte, **0 coupure fantôme** | non | 1 `[IMAGE]` (bandeau) · **13 `[LIEN]`** : sous-menu « La meute ». **2 tronqués** | non — 42 564 o des deux côtés | **utilisable tel quel** |
| `chiens/chien-pegaz.md` | idem `chien-jango` | **3 / 15** | **10** lignes d'U+00A0 + **3** + **13**. Aucune ligne de texte, **0 coupure fantôme** | non | idem `chien-jango` | non — 40 813 o des deux côtés | **utilisable tel quel** |
| `chiens/chien-etch.md` | idem `chien-jango` | **5 / 18** | **10** lignes d'U+00A0 + **3** + **13**. Aucune ligne de texte, **0 coupure fantôme** | non | idem `chien-jango` | non — 40 968 o des deux côtés | **utilisable tel quel** |
| `chiens/chien-rolex.md` | idem `chien-jango` | **3 / 20** | **14** lignes d'U+00A0 + **3** + **13**. Aucune ligne de texte. **3 coupures fantômes** (prose) | non | idem `chien-jango` | non — 40 484 o des deux côtés | **utilisable tel quel** |

**Trois fichiers sur dix sont à recapturer avant import** : `accueil.md`, `travail.md`,
`portees/portee-u2-2023.md`. **Les sept autres sont utilisables tels quels**, et il faut le dire
franchement : aucun mot de l'éleveuse n'y manque.

Totaux : **103 lignes d'U+00A0 avalées** sur les dix ; **30 lignes de bandeau** (3 par page) ;
**233 lignes de colonne secondaire**, dont **152 absentes par ailleurs du `.md`** (les 81 autres sont
déjà dans la Colonne latérale, que la passe #16 découpe).

### Les deux colonnes d'U+00A0, et pourquoi elles ne sont pas un désaccord de comptage

La colonne « déclarées » est le chiffre que porte l'en-tête de chaque `.md` de la passe #16 — et il
est **exact pour le fichier tel qu'il est** : les dix comptes ont été revérifiés caractère par
caractère, les dix concordent. La colonne « réelles » est le compte du **document**, sous la
convention #17 ; elle concorde à l'unité près avec la colonne « U+00A0 du corps réduit » de
`html/RELEVE.md` sur les dix.

L'écart vient donc entièrement de la matière absente : lignes avalées, bandeau, colonne secondaire,
et U+00A0 de fin de ligne supprimées par la réduction des blancs.

---

## Ce que la perte coûterait à l'import — les trois fichiers à recapturer

### `travail.md` — l'association année / chien / niveau

C'est la perte la plus lourde du lot, et la seule qui touche une **donnée** et non une mise en forme.

`travail.md` **éclate chaque cellule de tableau sur sa propre ligne**. La ligne

```
1994 | ♂ Storm Haven Guépard | Brevet
```

y devient trois lignes successives — `1994`, `♂ Storm Haven Guépard`, `Brevet` — dont rien, dans le
fichier, ne dit qu'elles vont ensemble sinon leur ordre. **L'association année / chien / niveau
*est* la donnée** d'un résultat de travail (contrat §3.2.4).

**Chiffré : 67 lignes de tableau perdent leur association, dont 57 lignes de résultats.** Réparties
ainsi, sous les intitulés de discipline du site :

| Intitulé de la discipline (texte source) | Lignes de résultats |
|---|---|
| RING | 22 |
| IGP (RCI) | 4 |
| Mondioring | 4 |
| Obéissance | 19 |
| Sauvetage | 1 |
| Pistage | 3 |
| Recherche Utilitaire | 4 |
| **Total** | **57** |

Les 10 lignes de tableau restantes sont **7 lignes d'intitulé** (rendues ` | RING | Niveau`, la
première cellule ne contenant qu'une U+00A0) et **3 lignes entièrement vides**. Le `diywebMain` de
`travail.html` porte **13 tableaux, 73 lignes, 207 cellules** ; les 6 lignes non citées ici n'ont
qu'une seule cellule et ne perdent donc aucune association.

Une ligne de résultats sur les 57 contient un lien, et une seule : `2024 | ♂ [LIEN
href=https://www.sports-canins.net/clubs/ficheChien.php?IDchiens=5019312]Road Trip du Mont
Brabant[/LIEN] | Selectifs 2025`. `travail.md` la coupe en trois **et** coupe le nom du chien au
milieu (`Road Trip du Mont` / `Brabant[/LIEN]`).

**Ce qui manquerait à l'import** : pour 57 résultats, le rattachement d'un chien à son année et à son
niveau. Reconstruire ce rattachement depuis `travail.md` demanderait de **supposer** que les
lignes vont trois par trois — ce qui est faux dès qu'une cellule est vide, et il y en a.
`pages/travail.md` (passe #17) **conserve les associations** ; `pages/README.md` l'a déjà écrit.
Re-dériver depuis `html/travail.html` donne le même résultat, à une ligne près documentée dans
`outils/README.md` (la cellule « Road Trip », que `pages/travail.md` éclate et que l'outil associe).

### `accueil.md` — un affixe de chenil coupé en deux

Quatre coupures fantômes dans le Contenu principal, dont **une tombe à l'intérieur d'un nom de
chienne** :

```
2026-2027 :<U+00A0>♂? X ♀Rolex New Wave du Mont
Brabant
```

Dans `html/accueil.html`, ce texte s'écrit `<strong style="font-size:16px;">Rolex New Wave du
Mont\nBrabant</strong>` : le retour à la ligne est **littéral dans le texte HTML, il n'y a pas de
`<br>`**, et le visiteur lit « Rolex New Wave du Mont Brabant » d'un seul tenant. Saisi tel quel, le
nom de la chienne arriverait coupé, et l'affixe « du Mont Brabant » séparé.

Les trois autres coupures fantômes de la page, le symbole `⏎` marquant la coupure :

```
Berger Hollandais Poil long / Poil ⏎ court
Merci de noter que nous avons arrêter l'élevage de mudi et vous ⏎ tournez vers la SCC pour vos recherches
[LIEN href=…/contact/]Pour plus d'informations, nous ⏎ contacter.[/LIEN]
```

La première coupe la désignation de race affichée en titre de la page.

**Ce qui manquerait à l'import** : rien en mots. Ce qui serait **faux** : deux libellés coupés là où
le site n'affiche pas de coupure. `pages/accueil.md` (passe #17) porte la version non coupée.

### `portees/portee-u2-2023.md` — une coupure dans les résultats de santé de la mère

Une seule coupure fantôme, mais elle tombe dans une suite de résultats de test :

```
X ♀ Pegaz Eenhoorn (DM: N/N -SDCA2 1.1-<U+00A0>HD:A- ED0
ADN)
```

Le visiteur lit « HD:A- ED0 ADN ». Le `.md` de la passe #16 place un retour à la ligne entre `ED0` et
`ADN`. Aucun mot n'est perdu ; la valeur, elle, serait saisie autrement qu'elle n'est affichée.
Aucune version #17 de cette page n'existe encore : la re-dérivation depuis `html/portee-u2-2023.html`
est le seul recours.

### Les sept autres — ce que la perte ne coûte pas

Pour `la-meute.md`, `portee-m-2016.md`, `portee-j-2014.md`, `chien-jango.md`, `chien-pegaz.md`,
`chien-etch.md` et `chien-rolex.md`, la matière absente est **entièrement du gabarit** :

- **Le bandeau** (`diywebEmotionHeader`) ne contient qu'une image, deux fois, et une U+00A0 :
  `https://www.mtbrabant.com/s/img/emotionheader.jpeg?1786776796.920px.313px`. **C'est la même
  adresse sur les pages archivées** — une seule valeur distincte dans tout le dossier — et elle est
  déjà présente dans les fichiers `.md` capturés sous la convention #17. Rien d'unique n'est perdu.
- **La colonne secondaire** porte le sous-menu de navigation, puis exactement le même contenu que la
  Colonne latérale — que la passe #16, elle, découpe. Vérifié sur les dix : **la Colonne latérale est
  toujours incluse dans la Colonne secondaire**, à la ligne près. Les seules lignes réellement
  absentes sont donc les **13 liens du sous-menu « La meute »** (fiches de chiens et `la-meute/`) ou
  les **29 liens du sous-menu « BHPL »** (pages de portées), selon la page. Ces deux sous-menus sont
  déjà recopiés en clair, l'un dans `pages/README.md`, l'autre dans `README.md`.
- Les **lignes d'U+00A0 avalées** ne portent aucun mot.

Une conséquence à connaître tout de même : **lu seul, `la-meute.md` ne contient aucun lien vers une
fiche de chien.** Son Contenu principal tient en un titre (« Nos retraités et disparus »), une image
et une légende (« Tara - Etch - Ipad - Opium ») ; les 13 liens vers les fiches sont dans la colonne
secondaire, non découpée. Ce n'est pas une perte pour le projet — les 13 adresses sont ailleurs —
mais c'est un piège pour qui lirait ce fichier comme l'index de la meute.

---

## Les quatre lignes « Autres disciplines » — Q12 et Q14

**Elles survivent dans les deux versions**, mot pour mot, dans le même ordre. Recopiées ici telles
quelles :

```
Autres disciplines :<U+00A0>

2018 - ♀<U+00A0>H'Alix du Domaine de Drenthe :<U+00A0>Cavage Classe B ChF - Prop. Ferrari
2019 : ♀ I'tea's You des Terres d'Alfheim : Agility 3ème au GPF
2010 - ♀<U+00A0>C'Yuna du Mont Brabant :<U+00A0>Brevet Maitre Chien Drogue (Suisse)
2012 - ♀<U+00A0>Dixie du Mont Brabant :<U+00A0>Qual. Chien de sauvetage<U+00A0>
```

**L'intitulé de section exact est `Autres disciplines :`**, suivi d'une U+00A0. Les deux versions le
portent ; `travail.md` (passe #16) supprime seulement l'U+00A0 finale de l'intitulé et celle de la
ligne « Dixie ». Sa place dans la page : immédiatement après le paragraphe « Ci dessous les résultats
par discipline de chiens LOF. » et immédiatement avant le premier tableau, celui de RING.

Trois constats de forme, **relevés et non interprétés** :

1. Dans `html/travail.html`, `Autres disciplines :` est un **`<p>` ordinaire** (`<span
   style="font-size: 16px;">`), sans balise de titre et sans gras, et les quatre lignes sont des
   **`<li>` d'un `<ul>`**. Les sept disciplines RING, IGP (RCI), Mondioring, Obéissance, Sauvetage,
   Pistage et Recherche Utilitaire sont, elles, des **cellules de tableau**. Les quatre lignes ne
   sont donc pas structurées comme les sept autres disciplines. **Cela ne tranche pas Q14** : le site
   ne dit nulle part si « Autres disciplines » est une discipline, une rubrique fourre-tout, ou les
   deux. On recopie le classement, on ne le déduit pas (contrat §7).
2. Le `<ul>` compte **cinq** `<li>` : les quatre lignes, plus un cinquième ne contenant qu'une
   U+00A0. C'est l'une des lignes avalées par la passe #16.
3. « Qual. Chien de sauvetage » (2012, ♀ Dixie du Mont Brabant) figure sous « Autres disciplines »
   **alors qu'un tableau « Sauvetage » existe par ailleurs sur la même page**, portant `2009 | ♀
   Dixie du Mont Brabant | Brevet`. Deux entrées, même chienne, deux années, deux emplacements.
   **Aucun rapprochement n'est fait ici.**

Pour **Q12** : le mot **« truffe » n'apparaît dans aucun des 54 fichiers HTML archivés** — vérifié
sur la totalité de `html/`, pas seulement sur `travail.html`. Le mot **« cavage » n'apparaît que dans
`travail.html`**, à la ligne recopiée ci-dessus. Les deux mots ne sont **pas** rapprochés ici.

---

## Anomalies constatées

Relevées, **non corrigées**. Aucun fichier n'a été modifié.

1. **`README.md` (passe #16) attribue aux coupures de milieu de phrase une origine qu'elles n'ont
   pas.** Il écrit : « Les retours à la ligne au milieu d'une phrase sont donc ceux du source (ex.
   "HD:A- / ED0 ADN") : ils viennent de vrais `<br>`, ils ne sont pas un artefact. » Mesuré sur
   `accueil`, `travail`, `portee-u2-2023` et `chien-rolex` : ces coupures viennent d'un **retour à la
   ligne littéral à l'intérieur d'un nœud de texte HTML**, sans `<br>` — vérifié dans le HTML archivé
   (`<strong …>Rolex New Wave du Mont` + retour + `Brabant</strong>` ; « …Rolex est une femelle très
   énergique mais » + retour + « qui sait… »). Le navigateur les rend comme une espace. **18 coupures
   fantômes au total** sur les dix fichiers (4 accueil, 10 travail, 1 portee-u2-2023, 3 chien-rolex ;
   plus 1 par fichier dans le pied de page, sur le libellé « Connexion » du gabarit). C'est la phrase
   de ce `README.md` qui rend la chose dangereuse : elle demande au lecteur de **conserver** une
   coupure qui n'existe pas. Le contrat interdisant de réécrire un fichier existant, le constat est
   déposé ici.

2. **Trois tailles différentes pour la page d'accueil, dont deux le même jour.** Les trois relevés
   sont : **47 384 o** (`accueil.md`, 20/08), **47 385 o** (`pages/accueil.md`, 20/08) et
   **47 382 o** (`html/RELEVE.md`, 23/08) — pour la même URL. Le rendu servi porte pourtant
   `rendered at Sat, 15 Aug 2026 21:23:45 +0200`, antérieur aux trois requêtes, et la comparaison
   mot à mot du 20 et du 23 août ne montre **aucune différence de texte**. L'origine des 2 ou
   3 octets n'a pas pu être établie depuis l'archive seule : les octets du 20 août n'ont pas été
   conservés. **Aucune ligne de contenu n'est en jeu.** Les neuf autres tailles concordent à
   l'octet.

3. **Un `href` tronqué dans le pied de page des dix fichiers.** La passe #16 rend
   `[LIEN href=javascript:switchView(]Affichage Web[/LIEN]` là où le document porte
   `javascript:switchView('desktop');` puis `javascript:switchView('mobile');` : l'attribut est coupé
   à la première apostrophe et les deux liens deviennent indiscernables. Ce sont des liens de
   l'éditeur IONOS, pas du contenu ; aucun texte n'est perdu.

4. **Deux liens « Lof Select » du site source ont un libellé coupé en deux ou une cible
   inattendue.** Présents à l'identique dans les deux conventions — ce n'est donc pas un écart de
   passe, mais une particularité du site :
   - `accueil` : `[LIEN href=…/lofselect/chien/ray-ban-de-lodyssee-dhera-7578103]L[/LIEN][LIEN
     href=…/lofselect/chien/spacetime-freccia-8372603]of Select[/LIEN]` — le mot « Lof Select » est
     réparti sur **deux liens différents**, vers **deux chiens différents**.
   - `chiens/chien-pegaz.md` : `- société centrale canine
     :[LIEN href=…/lofselect/chien/pegaz-eenhoorn-7678490]<U+00A0>[/LIEN][LIEN
     href=…/lofselect/chien/ray-ban-7578103]https://www.centrale-canine.fr/lofselect[/LIEN]` — un
     premier lien vers Pégaz dont le seul texte est une U+00A0, puis un lien libellé « lofselect »
     qui pointe vers **ray-ban**.

   Aucun rapprochement, aucune correction. Signalé parce que #20-#21 y verra des liens de généalogie.

5. **`outils/README.md` compte « 207 cellules » pour le tableau de `travail`** ; recompté ici,
   `diywebMain` de `travail.html` en porte bien **207** (186 `<td>` + 21 `<th>`), en 73 lignes et 13
   tableaux. Concordance, pas anomalie — noté parce que le document entier en porte 214, les 7 de
   plus étant hors des cinq zones.

---

## Vérification

- Les dix re-dérivations ont été produites par `outils/reduire.py` depuis `html/`, sans réseau, et
  sont rejouables telles quelles.
- **Comparaison mot à mot** des trois zones communes, sur les dix : les seules différences sont des
  **frontières de marqueurs** (`[LIEN` séparé de son `href=` par un saut de ligne) et, sur
  `travail`, les **séparateurs ` | `** des lignes de tableau. **Aucun mot rédactionnel** n'est
  présent d'un côté et absent de l'autre. Comptes de mots du Contenu principal, #16 puis #17 :
  accueil 273/274, la-meute 17/17, travail 1123/1257, portee-m-2016 146/146, portee-u2-2023 110/110,
  portee-j-2014 133/133, chien-jango 373/373, chien-pegaz 230/232, chien-etch 353/353, chien-rolex
  355/355. Les écarts non nuls hors `travail` sont **entièrement** imputables aux frontières de
  marqueurs listées ci-dessus ; l'écart de `travail` l'est aux **134 séparateurs ` | `** que la
  convention #17 place entre les cellules d'une même ligne.
- **Colonne latérale : identique au caractère près sur les dix**, 9 lignes et 21 mots (0 pour
  `accueil`, dont la zone est vide dans le document). C'est la zone qui porte l'encart
  « Chiots nés le 29/06/2026 / Tous réservés / Contacter nous au 0680505619 » : **la passe #16 ne
  l'a pas perdu**.
- **U+00A0** : les dix comptes déclarés par les en-têtes de la passe #16 ont été recomptés sur les
  fichiers eux-mêmes — **les dix concordent**. Les dix comptes « réels » ont été confrontés à la
  colonne « U+00A0 du corps réduit » de `html/RELEVE.md` — **les dix concordent** (22, 3, 136, 8, 7,
  7, 20, 15, 18, 20).
- **Tailles** : en-tête de chaque `.md` du 20/08 contre `html/RELEVE.md` du 23/08 — **9 identiques à
  l'octet**, `accueil` à −2 o (anomalie 2).
- **Tableaux** : recomptés dans le HTML par zone. **0 `<table>` dans les cinq zones des neuf pages
  autres que `travail`** — l'absence d'association éclatée y est donc mesurée, pas supposée. Le pied
  de page ne contient aucun tableau : les `|` qu'on y lit sont des barres verticales du texte.
- **Adresses** : `src=` et `href=` extraits des deux côtés et comparés en ensembles. Résultat par
  page dans le tableau ; une seule adresse d'image absente partout (le bandeau), et elle est présente
  dans les autres `.md` du dossier capturés sous la convention #17.
- **Trois enregistrements vérifiés champ par champ** en plus des comptages : `portee-m-2016` (les
  deux lignes de généalogie et leurs résultats de test, identiques au caractère près hors U+00A0),
  `chien-etch` (les deux dates d'exposition `16/05/10` et `12/09/10` et les deux liens « Mère de la
  portée », identiques hors U+00A0 finale), `travail` (les 57 lignes de résultats, recopiées en
  entier et confrontées cellule par cellule à la re-dérivation).

## Questions pour l'éleveuse

aucune. Cette mesure n'a rencontré aucune valeur illisible ni ambiguë qui lui soit propre : les
questions ouvertes qu'elle croise (Q12, Q14) sont déjà posées ailleurs et restent posées.
