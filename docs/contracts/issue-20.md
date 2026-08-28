# Contrat d'import — Issue #20 — Import des portées et des chiens

**Statut : gelé.** Contraignant pour toute la chaîne #20. Opposable à #21, #23, #24.

Cette issue ne touche **que l'extension**, et à l'intérieur de l'extension un seul dossier. Il n'y a
donc **pas de frontière thème↔extension à arbitrer** : ce document ne décrit ni fonction de lecture,
ni bloc, ni règle visuelle. Il gèle à la place la **convention de transcription et d'import**, seul
endroit où deux agents peuvent diverger en silence et rendre la reprise approximative.

**Écart de chaîne, déclaré** : l'agent `leaddev-back-mtb` prévu par la chaîne **n'existe pas dans cet
environnement** (seul `leaddev-front-mtb` est disponible, et l'issue n'a pas de côté thème). Le plan
technique a donc été arrêté par le lead de l'issue et vit dans ce document. Aucune étape n'a été
sautée en silence.

## 0. Empreinte d'écriture

`wp-content/plugins/mtb-core/includes/migration/portees-chiens/**`, **ce fichier**, et deux
livrables nommés que la chaîne doit produire pour être terminée :

- `docs/migration/portees-chiens.md` — la déclaration de reprise (ce qui est repris, ce qui est
  volontairement vide, les écarts). **Fichier neuf, nommé sur le domaine de cette issue**, donc sans
  collision possible avec #21.
- `docs/guide/contenu-repris-de-l-ancien-site.md` — la fiche d'aide (**D3**). Idem, fichier neuf.

**`docs/migration/redirections.md` reste interdit** : c'est un fichier **partagé et additif**, et #21
peut y écrire au même instant. Dans un arbre unique sans branche, deux écritures concurrentes se
perdent sans recours. La carte des 52 URL vit déjà dans `source/INVENTAIRE.md` §2 ; #24 la lit là.

Partage interne, pour que trois agents écrivent en parallèle sans se recouvrir :

| Agent | Écrit | N'écrit jamais |
|---|---|---|
| `dev-back-mtb` | tout le PHP du module | `donnees/**` |
| `contenu-mtb` (chiens) | `donnees/chiens.json`, `donnees/recaptures/**` | le PHP, `donnees/portees.json` |
| `contenu-mtb` (portées) | `donnees/portees.json` | le PHP, `donnees/chiens.json` |

Interdits durs, arbre de travail partagé, aucune branche :

- `includes/migration/import-fixtures/**` — **lecture seule** (#29). Forme à imiter, jamais à
  modifier ni à `require`.
- `includes/migration/resultats-pages/**` — chaîne sœur #21, en parallèle.
- `includes/content/`, `includes/fields/`, `includes/query/`, `includes/blocks/`, le thème.
- `docs/migration/source/**` — **gelé** par le contrat #19 §9.
- `docs/migration/redirections.md` — fichier partagé et additif ; #21 peut y écrire au même instant.
  **#20 n'y touche pas.** La carte des 52 URL vit déjà dans `source/INVENTAIRE.md` §2 ; #24 la lit là.

## 1. L'approche gelée

**Transcription versionnée + importeur idempotent.** Les 44 entités deviennent des fichiers de
données **commités**, chaque valeur portant sa **provenance** (fichier source + extrait verbatim).

La raison tient en une phrase, et c'est le reste écrit de #17 : *un contenu qui n'existe que dans une
base n'est pas repris.* Un tiers, dans six mois, base détruite, doit pouvoir ouvrir un fichier, lire
à côté de chaque valeur l'extrait dont elle vient, et relancer l'import.

Écartées : analyseur direct des `.md` (§7 — les captures ne sont pas structurées) ; WXR (exige
`wordpress-importer` du dépôt, contrainte 2, et importe les médias par URL) ; squelette laissé vide
(chemin d'édition « elle recopie » — porte fermée du projet).

## 2. Ce que le module expose

| Commande | Rôle |
|---|---|
| `wp mtb importer-portees-chiens --chiens=<f> --portees=<f>` | crée ce qui manque, ne modifie jamais ce qui existe |
| `wp mtb verifier-portees-chiens` | les cinq contrôles du §9, sans rien écrire |

Nommage **sans ambiguïté** avec `wp mtb import-fixtures`, qui sème du **fictif** (décision 50). Un
espace de noms `mtb` déjà créé accueille la commande sans toucher un fichier commun (décision 9).

**Garde de non-mélange** : l'import réel **refuse de s'exécuter** si la base porte un contenu dont le
titre contient « Démonstration ». Une base semée de fixtures n'est pas une base d'accueil pour la
reprise, et le mélange ne se voit plus à l'œil nu une fois fait.

## 3. Format des fichiers de données — la provenance est obligatoire

Toute valeur non vide s'écrit comme un objet à trois clés :

    { "valeur": "…", "source": "portees/portee-a3-2025.md", "extrait": "…verbatim…" }

`extrait` est une **sous-chaîne littérale du fichier `source`**. C'est ce que le contrôle du §9.2
vérifie, et c'est ce qui rend la transcription contestable par un tiers.

**Distinguer les deux vides**, sans quoi un oubli ressemble à une absence :

| Écriture | Sens |
|---|---|
| clé **absente** | **oubli** — le contrôle §9.4 le signale |
| `{ "valeur": "", "motif": "…" }` | **vide voulu** : la source ne l'énonce pas, et le motif le dit |

Le `motif` est du texte libre destiné à un humain. Il est **obligatoire** sur tout vide voulu.

## 4. Ce que la source n'énonce pas — arbitrages

### 4.1 `disponibilite` — vide sur les 27

Le site ne l'énonce **jamais**. L'encart « Chiots nés le 29/06/2026 / Tous réservés » est du
**mobilier de gabarit**, identique sur les 52 pages, y compris sur une portée de 1995 (contrat #19
§7). Le lire comme la disponibilité de la portée affichée est interdit.

Déduire « portée passée » d'une date de naissance est un **raisonnement**, pas une recopie, sur un
champ qui pilote l'encart d'accueil et la page Placement.

**Le vide est ici quasi gratuit, et c'est mesuré** : `blocks/derniere-portee/render.php` lignes 56-57
et 104-106 n'affiche **aucun badge** sur une valeur inconnue, jamais « Non renseigné ». Le tri par
année vient de `_mtb_date_naissance` et fonctionne. → **27 vides, motif écrit, Q-20-1 remontée.**

### 4.2 `statut` — recopié uniquement sous forme close

**Réglé sur les octets, les 17 fiches relues** (un premier comptage à 4 était incomplet ; il portait
sur 8 fiches seulement — la mesure ci-dessous porte sur 17) :

| Le site écrit | Fiches | Valeur |
|---|---|---|
| `✞` et/ou `DCD` sur la **ligne d'identité du sujet** | **7** — `etch`, `gribouille`, `grocky`, `jango`, `maya`, `opium`, `tara` | `disparu` |
| « Femelle reproductrice » (page d'accueil, sous la photo et le lien LOF) | **3** — `very-best`, `rolex`, `tesla` | `reproducteur` |
| rien de clos | **7** — `halan`, `happy`, `pegaz`, `ray-ban`, `roxane`, `you`, `youry` | **vide** |

`DCD → disparu` est un **report dans une liste fermée**, pas un ajout de fait : le modèle n'offre pas
d'autre mot pour la mort. C'est un arbitrage, il est écrit, il se ratifie (Q-20-2).

**Interdits nommés, chacun vérifié sur les octets :**

- **Aucune correspondance par mot-clé.** `chien-grocky.md` ligne 94 écrit « mâle **retraité**
  d'intervention de l'armée de l'air » — et Grocky est `DCD`. Un filtre sur « retraité » classerait un
  chien mort en retraité. La transcription est **lue, entité par entité**.
- **`happy` porte `STERILISEE`, pas une mention de décès.** Stérilisée n'est pas morte. Sa prose dit
  « coule à présent une retraite heureuse en Allemagne » : c'est de la prose, pas une forme close.
  → **vide**, question Q-20-3.
- **`date_deces` reste vide sur les 7.** Le site écrit `DCD` sans date, ou `DCD: 06/2018` — mois et
  année, **sans jour**. Le champ est une date : écrire `2018-06-01` inventerait un jour. La mention
  survit **verbatim dans le texte libre**, où le site l'a mise.
- **La légende « Tara - Etch - Ipad - Opium » n'est pas une liste de statuts.** C'est la légende
  d'**une** photographie sous le titre « Nos retraités et disparus » ; `ECART-PASSE-16.md` ligne 220
  avertit que lire ce fichier comme l'index de la meute est un piège. Preuve interne : **Jango porte
  `DCD` et n'y figure pas.** Les trois noms qui ont une fiche y sont **déjà** marqués sur leur propre
  fiche : l'intitulé à double sens (« retraités **et** disparus ») **n'oblige donc à choisir pour
  personne.**

**Le prix du vide est cher ici, et il est mesuré** : `query/chien/lecture.php` écarte tout chien sans
statut de `groupes_par_statut()`. **7 fiches sans statut n'apparaissent ni sur « La meute » ni dans la
Grille de chiens**, et leur fiche affiche « Statut · Non renseigné ». C'est visible, c'est assumé, et
c'est le bon défaut : il crie au lieu de mentir.

### 4.3 `sexe` — absent des 17 fiches, recopié depuis les portées

Aucune fiche ne porte `♂`/`♀`. Les **lignes de parents des portées** le disent, et c'est le site qui
le dit. Chaque attribution porte sa provenance, **fiche par fiche**.

**Piège vérifié sur le lead lui-même** : un premier relevé automatique a attribué `♂` à **Tara**,
parce que `portee-c-2007` écrit « ♂ Vandale X Tina dit Tara… » — deux chiens sur une ligne, un seul
marqueur. `portee-d-2008` tranche : « ♂ Vandale X ♀ Tina dit Tara ». **Preuve par chien, jamais par
motif.**

## 5. Filiation

**Fait mesuré qui commande la règle** : le site ne lie **jamais** un parent à une fiche —
`la-meute/` apparaît **0 fois** dans les 27 captures de portées. « On lie quand le site lie » donnerait
**0 lien sur 27** et viderait la tâche 2 de la checklist.

| Niveau | Règle | Verdict |
|---|---|---|
| 1 | le site lie par un lien interne | 0 portée — inutilisable seul |
| 2 | le nom est **identique caractère pour caractère** au nom d'une fiche | **retenu** |
| 2 bis | **la page de portée et la fiche portent la même URL LOF Select**, à l'octet | **retenu — amendement, voir ci-dessous** |
| 3 | le nom **ressemble** | **refusé** |

### 5 bis — Amendement au niveau 2 bis, et pourquoi il n'affaiblit pas la règle

*Ajouté après la transcription, sur une preuve que la première rédaction n'avait pas vue.*

L'identité stricte de nom, appliquée seule, **laissait six liaisons non posées — dont Opium, la lice
la plus employée de l'élevage, sur ses quatre portées, pour une seule espace.** La cause n'est pas
une divergence de nom : la fiche `chien-opium.md` **coupe le nom en deux ancres** (`Opium` puis
` des Leus Chapellois`) qui pointent **toutes deux vers la même URL**. C'est le défaut de gabarit
IONOS déjà rencontré sur Pégaz et sur Tesla — un libellé réparti sur deux liens — et non un fait
d'élevage.

**Le niveau 2 bis ne devine rien : il lit une identité que le site énonce deux fois.** Une URL LOF
Select désigne un chien **inscrit au registre**, par son numéro. La même URL des deux côtés, à
l'octet, est le site qui affirme « c'est le même chien » — au même titre qu'un lien interne, et avec
une autorité supérieure, puisqu'elle passe par la Société centrale canine. Ce n'est pas la
ressemblance du niveau 3, qui reste **refusée**.

Vérifié à l'octet, les deux côtés :

| Portées | Rôle | Fiche | URL identique des deux côtés |
|---|---|---|---|
| `o-2018`, `p-2019`, `r-2020` | mère | `opium` | `…/lofselect/chien/opium-des-leus-chapellois-6248841` |
| `r-2020` | père | `grocky` | `…/lofselect/chien/fgrocky-7332435` |

**`s2-2021` n'est pas dans ce tableau** : sa page ne porte **aucune** URL LOF Select. Elle est liée
par le **§5 ter** ci-dessous, sur un autre critère, et c'est l'écart le plus délicat de cette chaîne.

**Ce que le niveau 2 bis ne rattrape pas, et c'est voulu** — la preuve doit être des **deux** côtés :

| Cas | Pourquoi il reste non lié |
|---|---|
| `u1-2023` mère, `Ray Ban` / `Ray-Ban` | `portee-u1-2023.md` ne porte **aucune** URL LOF Select. Preuve d'un seul côté. |
| `o-2018` père, `C'Halan von Bavaria` | `chien-halan.md` ne porte **aucune** URL LOF Select — sa zone de contenu est vide. Preuve d'un seul côté. |
| `s1-2021`, `u2-2023` mère, `Pegaz` / `Pégaz` | aucune URL des deux côtés. Un accent ne se tranche pas. |
| `v1-2024` mère, `R'U2 dit You` / `R'U2 aka You` | aucune URL. `dit` et `aka` ne se rapprochent pas. |
| `c-2007`, `d-2008` mère, `H3F` / `Hameau des Trois Fontaines` | aucune URL. Le cas fondateur du refus reste refusé. |

Ces cinq-là restent en **nom libre verbatim** et partent en ratification (Q-20-6).

### 5 ter — L'attestation d'URL s'étend à une chaîne côté portée byte-identique

*Ajouté après la première passe corrective, sur une mesure.*

`s2-2021` écrit la mère « Opium des Leus Chapellois » — **exactement les mêmes octets** que
`o-2018`, `p-2019` et `r-2020`, dont les trois liaisons vers `opium` sont attestées par l'URL LOF
Select. Or `portee-s2-2021.md` **ne porte aucune URL** : le niveau 2 bis, lu à la lettre, la laissait
seule non liée — **une lice sur quatre portées, trois cartes et un texte nu.**

**Règle, et elle est étroite** : quand le site a attesté par URL qu'une chaîne donnée, écrite côté
portée, désigne une fiche, **cette attestation vaut pour la même chaîne écrite à l'octet sur une
autre page du même corpus**. Ce n'est pas une ressemblance : c'est la même chaîne, et c'est le site
qui a dit une fois pour toutes ce qu'elle désigne.

**Mesuré : un seul cas dans tout le corpus** — les 25 autres noms de parents restés `exterieur` ne
répètent aucune chaîne attestée. La règle ne rouvre donc rien d'autre, et surtout pas les refus du
§5 bis, dont aucune chaîne n'a jamais été attestée.

**Ce qui reste interdit, et qui explique pourquoi la règle est étroite** : la différence entre la
fiche (`Opium des Leus Chapellois`, **espace insécable**) et les portées (espace ordinaire) est
**un artefact du gabarit IONOS**, qui coupe le nom en deux ancres. On **ne normalise pas les espaces
pour rapprocher deux noms** — ce serait le niveau 3 déguisé. On s'appuie uniquement sur l'attestation
d'URL et sur l'égalité stricte côté portée.

Le niveau 3 refusé, avec son cas : « Tina dit Tara du **H3F** » (fiche) contre « du Hameau des Trois
Fontaines » (c-2007) et « du Hameau des **trois** Fontaines » (d-2008, minuscule). Que `H3F` soit
l'abréviation est **presque certain** — et « presque certain » n'est pas « recopié ». Ces portées
gardent le **nom libre verbatim**. Le lien coûtera deux clics à l'éleveuse depuis un écran qui existe.

**Chaque liaison posée est listée dans le journal**, ratifiable une par une.

Parent sans fiche : `type = exterieur`, `nom` **verbatim**, `elevage` **vide** si le site ne le sépare
pas, `sante` = le contenu de la parenthèse **verbatim** (`HD:-`, `ED`, `1.1` compris, aucune
normalisation).

**Conséquence contre-intuitive du lien, à ne pas taire** : `query/portee/hydratation.php` lignes
316-331 rend `champ_muet` pour les tests de santé dès qu'un parent est une fiche — au motif juste que
« ses tests sont ailleurs ». **Lier Etch comme mère de M 2016 fait donc disparaître de la page la
chaîne `(DM : N/N - HD:A/A -ADN comp. Kbr/Ky - Em/E)` que le site source y affichait** — et cette
chaîne peut différer de celle de la fiche, parce qu'elle **date de la saillie**. La valeur reste
**stockée** dans `_mtb_*_sante`, elle cesse d'être **affichée**.

**Décision : on lie, et on ouvre la dette nommée**, propriétaire = l'issue des gabarits de portée.
Un écart non écrit n'est imputable à personne (décision 46).

**Aucune fiche fabriquée.** Ni pour **« Ipad »** — nommé **une seule fois** dans tout le corpus, en
légende de photo : une fiche ne porterait qu'un nom d'usage, et n'apparaîtrait même pas sur « La
meute » faute de statut. Ni pour `Upper'Side`, `Jerry Lewis`, `Peregrine`, `Road Trip`, `Delhya` du
Mont Brabant. Le site n'en a pas fait de fiches ; nous non plus. Les noms survivent **verbatim** en
nom libre et dans le texte. Ratification demandée (Q-20-4) : le nom d'un chien de l'élevage n'est pas
un détail de conception.

## 6. Photos

**192 fichiers, 150 images distinctes** — recompté par SHA-256 sur les octets déposés.

**Quatre groupes byte-identiques.** Trois sont les **bandeaux de section** répétés sur chaque fiche de
chien (« Résultats expositions », « Résultats tests génétiques - santé », « Autres ») ; le quatrième
est une photographie pleine taille citée par deux entités.

| SHA-256 | Octets | Fichiers | Nature |
|---|---|---|---|
| `f920725d…` | 7 758 | 16 | bandeau de section |
| `ce63e3c1…` | 7 674 | 16 | bandeau de section |
| `68914df7…` | 15 208 | 12 | bandeau de section |
| `05dc5ac1…` | 1 095 104 | **2** — `14834172.png`, `16372512.png` | **photographie citée deux fois** |

**Correction d'une erreur de ce contrat, relevée par la chaîne et vérifiée par le lead.** La première
rédaction annonçait « trois groupes » et donnait trois condensés — exacts, mais **non exhaustifs**.
Une liste codée en dur aurait donc laissé passer le quatrième.

**D'où la règle, qui n'est pas une liste** : *un condensé cité par plus d'une entité ne peut pas être
un portrait.* Elle est **mesurée à l'exécution**, elle couvre les quatre groupes, et elle couvrira
ceux que personne n'a comptés. La liste des trois bandeaux reste en dur **en plus**, comme garde-fou
lisible — jamais comme seule défense.

**Conséquence dure : jamais « première image = image mise en avant ».** La première image d'une fiche
de chien **est** un de ces bandeaux — `16458617.jpg`, en tête de `chien-tesla.md`, est le bandeau de
7 758 o partagé avec 15 autres identifiants. La règle naïve donnerait **le même faux portrait à
16 chiens**. C'est la classe d'erreur exacte de « Eenhoorn Sire Eenhoorn » (décision 46).

Règles gelées :

1. **Nom de fichier inchangé** : `<identifiant IONOS>.<ext>`, casse comprise. Nommer par chien
   supposerait de savoir qui est dessus (contrat #19 §10).
2. **Une pièce jointe par SHA-256 distinct**, pas par identifiant : les doublons se rejoignent.
3. **Les 3 SHA de bandeau ne sont attachés à aucune galerie** et ne portent aucun portrait. Motif
   écrit : *une image byte-identique sur 16 pages ne peut représenter aucun individu.* Elles entrent
   tout de même en médiathèque — rien n'est supprimé — mais sans rattachement.
4. **Image mise en avant seulement si le SHA de l'image de tête est unique à cette page.** Sinon,
   **pas de portrait** : une fiche sans portrait est un état de rendu prévu ; un faux portrait, non.
5. **Rattachement** par la colonne « Pages citantes » de `photos/MANIFESTE.md`, mesurée sur le HTML.
   Ordre de galerie = ordre d'apparition dans le `.md`.
6. **Titre de pièce jointe lisible** — c'est lui que l'éleveuse lit, pas le nom de fichier :
   « Photo de la portée A3 2025 (3 sur 8) », « Photo de la fiche de Tesla (2 sur 6) ». Il dit **où la
   photo est publiée**, jamais ce qu'elle montre. La tâche 3 de la checklist est tenue sans invention.
7. **Texte alternatif** : les **2** `alt` renseignés du source **verbatim** ; pour les autres, un
   `alt` **factuel de contexte**. **C'est une dette d'accessibilité déclarée, pas une case cochée** :
   pour un lecteur d'écran, huit alternatives voisines sur une page sont du bruit. `alt=""` serait
   pire — il affirmerait que ces photos sont décoratives, ce qui est faux. Entre un bruit vrai et un
   silence faux, on prend le bruit vrai et on l'écrit. Seule l'éleveuse peut la payer, depuis la
   médiathèque.
8. **T12, prérequis d'ordre** : rien ne se téléverse avant le module d'images de #8 (livré :
   `includes/admin/medias/bootstrap.php`, sous-taille `mtb-vignette-galerie` 400 px, JPEG→WebP). Le
   module n'a **pas** de garde `is_admin()` précisément parce que l'import est en WP-CLI.
9. **Voie de versement** : `media_handle_sideload()`, qui enchaîne `wp_generate_attachment_metadata()`
   — l'appel qui écrit réellement les sous-tailles. Il **déplace** le fichier : on **copie dans un
   temporaire d'abord**, `docs/migration/source/photos/` étant une **archive** et non un stock de
   travail.

**« Pleine résolution » (tâche 3 de la checklist) est intenable et la tâche doit se relire.** Mesuré :
sur 383 renditions servies, **4 dépassent 1024 px** ; 49 plafonnent exactement à 1024 px ; 16 à
274 px, 16 à 246 px, 12 à 237 px. On importe **la plus grande rendition publiquement servie, telle
qu'archivée**. Toute formulation qui promet mieux ment (Q-20-8).

## 7. Ce qui se lit, et ce qui tranche

`.md` = ce qu'on **transcrit**. `.html` archivé = ce qui **tranche**. Une valeur qui diverge entre les
deux est transcrite depuis le `.html`, et la divergence est écrite. C'est le §4 du contrat #19 rendu
opérationnel, pas amendé. Le `.html` n'est **jamais** importé tel quel.

**Sur la métadonnée de page, le `.md` est muet et son silence ne prouve rien** (contrat #19 §3.1 bis) :
seul le `.html` fait foi.

**Une seule capture est à re-dériver** : `portees/portee-u2-2023.md`, verdict « à recapturer avant
import ». Une coupure fantôme place un retour à la ligne entre `ED0` et `ADN` dans les tests de santé
de la mère ; le visiteur lit `(DM: N/N -SDCA2 1.1- HD:A- ED0 ADN)`. Les 26 autres portées et les
17 chiens sont « utilisable tel quel ».

La re-dérivation se fait par `source/outils/reduire.py` (rejouable **sans réseau**) et son résultat
est déposé dans **`donnees/recaptures/`**, jamais dans `source/`. Le contrat #19 §9 est respecté au
mot : aucun fichier de `source/` n'est réécrit, renommé ni supprimé.

### 7 bis — Écart au §7 : `donnees/recaptures/` n'existe pas

*Constaté à la revue de lot, et nommé ici parce qu'un écart non écrit n'est imputable à personne.*

**Le dossier prescrit ci-dessus n'a jamais été créé.** `donnees/` ne porte que `chiens.json` et
`portees.json`.

**Le résultat visé est pourtant atteint, et il est vérifiable** : la seule valeur qu'affectait la
coupure fantôme — la santé de la mère de `u2-2023` — est transcrite
`DM: N/N -SDCA2 1.1- HD:A- ED0 ADN`, avec `ED0` et `ADN` **réunis** comme le visiteur les lit, et sa
`source` déclarée est **`html/portee-u2-2023.html`**, le HTML brut qui tranche. La transcription est
donc allée chercher l'octet au bon endroit.

**Ce qui a changé, c'est le moyen** : le contrat prévoyait de matérialiser une capture re-dérivée sur
le disque ; la chaîne a lu directement le HTML archivé pour la valeur concernée et a cité cette
source. Aucun fichier de `source/` n'a été touché, l'interdit du §9 du contrat #19 est tenu.

**Ce que le raccourci coûte, et il faut le dire** : le §7 voulait une capture re-dérivée **complète**,
relisible d'un bloc et comparable ligne à ligne à la version défectueuse. On a à la place **une
valeur** correctement sourcée. Le contrôle §9.2 la vérifie comme les autres — l'extrait doit
apparaître littéralement dans le `.html` — donc rien n'est cru sur parole. Mais si une **autre**
divergence dormait dans cette page, rien dans ce dispositif ne l'aurait révélée : c'est
`ECART-PASSE-16.md` qui affirme qu'il n'y en a qu'une, et cette affirmation n'a pas été recontrôlée
ici.

**Piège CRLF, mesuré, à traiter avant tout condensé** : `core.autocrlf = true`, aucun
`.gitattributes`. `html/portee-u2-2023.html` fait **38 887 o** sur disque, porte **525 CR**, et
38 887 − 525 = **38 362** = la taille déclarée par `RELEVE.md`. **Aucun SHA de `RELEVE.md` ne se
vérifie sans normaliser CRLF en LF d'abord.**

## 8. Recopie littérale — ce qui ne se normalise jamais

Vérifié sur les octets. *Ce qui semble faux dans un fichier source **est** ce que le site affiche.*

- N° LOF en cinq graphies : `LOF_13979`, `LOF_ 13462` (espace parasite), `LOF : 178`, `LOF31/`,
  `LOF : 3330/572`.
- `LOF_` **vide** sur les 10 chiots de `portee-a2-2025`, tous nommés « A du Mont Brabant » — portée
  2025 non encore inscrite. C'est l'état réel du site, pas un trou.
- `Diversité génétique : --%` — `--%` est la valeur.
- La faute « vertébre de transition lombonsacrée » — non corrigée.
- « Hameau des Trois Fontaines » (c-2007) contre « Hameau des trois Fontaines » (d-2008) : **chaque
  page garde sa casse.**
- `portee-n-2017` : le titre dit `"N_2"` alors que l'URL source est `/portée-n-2017/`. Les deux faits
  sont conservés — l'identifiant vient du titre, le slug de l'URL.
- Le `devenir` des chiots **existe** sur les portées anciennes : « - ♂ Cooper du Mont Brabant
  LOF : 178 - France (50) - décédé », « - ♀ Crevett du Mont Brabant LOF : 179 - export Israël ».
- Compteurs `_mtb_males` / `_mtb_femelles` : **chaînes**, jamais des entiers. `"0"` et `""` sont deux
  faits différents (décision 21).

**Décision 20, rappelée parce qu'elle est contre-intuitive** : **jamais** `sanitize_text_field`,
`wp_strip_all_tags` ni `wp_kses` sur une valeur recopiée. Elles passent par `strip_tags()` : une
valeur commençant par `<` — `<60%` en dysplasie, plausible — serait **vidée en silence**.

**Dette T9** : aucune **cinquième sémantique** d'assainisseur. On adopte **verbatim** la plus stricte
déjà en place — contrôle par `wp_check_invalid_utf8()` **et suppression** des caractères de contrôle.
Le hissage est une dette ouverte, pas le travail de cette issue. **À signaler** :
`content/chien/assainissement.php` est la copie la **plus laxiste** (elle *remplace* les contrôles par
une espace et n'appelle pas `wp_check_invalid_utf8()`), et c'est **elle** que `sanitize_meta()`
invoquera sur tous les champs de chien.

**`wp_slash()` sur tout** ce qui part vers `wp_insert_post()` et `update_post_meta()`, entiers
compris. Sans lui, un résultat `N/N` s'écrit `N\N`, **sans erreur ni avertissement**.

## 9. La vérification qui prouve — tâche 4 de la checklist

Une vérification qui se déclare faite ne vaut rien. Cinq passes, dont quatre automatiques.

1. **Complétude par soustraction.** 27 − 27 = 0, 17 − 17 = 0, 44 entrées − 44 contenus créés = 0,
   recompté deux fois de façon indépendante.
2. **Chaque valeur est un extrait de sa source.** Pour toute valeur non vide, la chaîne `extrait`
   doit apparaître **littéralement** dans le fichier `source` déclaré. **Un échec est nommé, jamais un
   avertissement.** Exceptions **limitatives** : clés de listes closes (`disparu`, `reproducteur`,
   `male`, `femelle`, `fiche`, `exterieur`), identifiant de portée, slug. *Une liste d'exceptions qui
   grossit est le signal que l'approche dérape.*
3. **Contrôle aval.** Relecture par `get_post_meta()` et comparaison à la valeur brute re-assainie,
   sur les **44**, pas sur un échantillon. C'est ce contrôle, et lui seul, qui attrape `N/N` devenu
   `N\N`, une valeur vidée, une insécable perdue.
4. **Tableau de relecture humaine**, 44 lignes versionnées : entité · fichier source · champs
   transcrits · **champs volontairement vides et leur motif** · photos rattachées · divergence · vu.
   La colonne des vides est celle qui compte : sans elle, un oubli ne se distingue pas d'une absence.
5. **Rejeu.** Sur base peuplée : **0 création, 0 modification, 44 « déjà importée »**. Sur base neuve :
   même résultat qu'au premier passage. **C'est la démonstration de D11.**

## 10. Idempotence

- Clé d'identité : **`post_name`** pour un chien, **le titre** pour une portée. C'est ce que fait
  `import-fixtures/references.php` et `portees.php` lignes 150-170, délibérément **sans** passer par
  les fonctions de lecture publiques, qui figent `publish` — sinon une fiche passée en brouillon
  serait recréée en double au rejeu.
- **Existe déjà ⇒ on ne touche à rien**, et on le journalise. Pas de `--force` par défaut.
- **L'import ne supprime jamais rien.**
- Ordre : chiens → photos → filiation → portées. Un chien référence un chien qui figure plus loin
  dans le même fichier : **deux passes**, sinon la moitié du jeu reçoit une filiation vide, sans un mot.

## 11. Les quatre fiches non indexées

Vérifié sur les octets : **4 fiches de chiens** portent `noindex, nofollow` en tête de `<head>` —
`chien-halan`, `chien-ray-ban`, `chien-roxane`, `chien-youry` — et **aucune des 27 portées**.

Q23 est tranchée par l'utilisateur : **importées, hors menus, non indexées.**

- **Hors menus : gratuit.** Les menus sont construits à la main par l'éleveuse. Une fiche importée n'y
  entre que si quelqu'un l'ajoute. Rien à coder.
- **Ni privé, ni mot de passe, ni brouillon** : le source sert ces pages **200 à tout le monde**. Les
  soustraire retirerait au visiteur un contenu qu'il pouvait lire, et ferait tomber D5.
- **Mon empreinte porte le fait**, recopié **verbatim** du `<head>` archivé, avec sa provenance.
- **Mon empreinte ne porte pas le rendu.** `wp_robots` et l'exclusion du sitemap sont du comportement
  public : ils vivent dans `includes/query/` ou dans **#23 / #24**.
- **Dette nommée, née dans le même commit** : *si le fait est stocké et que personne ne le rend, les
  quatre fiches sont indexables et le dépôt affirme le contraire.* Un fait stocké et non honoré est
  pire qu'un fait absent (Q-20-9).

**Une contradiction du source qu'on ne peut pas recopier** : ces pages sont **au sitemap** *et* en
`noindex`. Recopier les deux est impossible. On **suit la balise**, et on écrit que c'est un
**arbitrage entre deux énoncés contradictoires du source**, pas une recopie fidèle (Q-20-10).

## 12. Origines tierces

Relevé sur mes 44 fichiers : `www.google.com` (162), `cdn.website-start.de` (88),
`124.sb.mywebsite-editor.com` (88), `www.facebook.com` (81), `login.1and1-editor.com` (44) —
**tous dans le mobilier de gabarit** (pied de page, colonnes), qui n'est pas du contenu et n'est pas
importé. Dans le contenu rédactionnel : `www.centrale-canine.fr` (42), `www.abnf.fr` (9),
`www.youtube.com` (4), plus deux liens isolés.

**Règle : un `[LIEN]` est un ancrage que le visiteur clique — ce n'est pas une requête. Un `[IMAGE]`
ou un `[IFRAME]` tiers en est une.**

- Les liens externes du texte sont conservés **verbatim**.
- **Les deux `[IFRAME]` YouTube** (`portee-c-2007`, `portee-o-2018`) deviennent des **liens
  cliquables**. **Aucune vidéo n'est restaurée** dans un cadre : un `iframe` YouTube ferait tomber
  **D6**. La vidéo n'est pas jouable *sur* le site, mais elle reste **atteignable en un clic** —
  c'est écrit, pas subi (Q-20-7).

### 12 bis — Les marqueurs de capture ne se publient pas

*Amendement, sur une mesure : `247 [LIEN]`, `180 [IMAGE]`, `2 [IFRAME]` sur **34 des 44 entités**.*

`[LIEN …]`, `[IMAGE …]` et `[IFRAME …]` sont la **notation** de la capture (contrat #19 §3.2), **pas
le texte du site source**. Le site affichait un lien et une image ; il n'a **jamais** affiché la
chaîne `[IMAGE src=…]`. Les publier telles quelles reproduirait **l'outil au lieu de la source** —
la même classe d'artefact que le CRLF ou l'ancre coupée en deux. **D4 exige de reproduire le site,
pas le format de capture.**

**La conversion vit dans le moteur, jamais dans `donnees/`** : les fichiers de données restent
fidèles à la capture, et la transformation reste lisible et contestable dans le code.

| Motif | Devient | Pourquoi |
|---|---|---|
| `[LIEN]` … `[IMAGE]` … `[/LIEN]`, l'ancre ne portant que l'image | **supprimé en entier** | lien d'agrandissement du gabarit IONOS ; la photographie est **déjà** dans la galerie de l'entité |
| `[IMAGE src=… alt="…"]` restant | **supprimé** | déjà rattachée en médiathèque, et la restaurer serait **une requête vers `mtbrabant.com` — D6, sur un domaine qui sera résilié** |
| `[LIEN href=X]texte[/LIEN]` | `<a href="X">texte</a>` | un ancrage **n'est pas une requête** : il est conservé |
| ancre vide ou d'une seule espace (**le cas de Pégaz**) | l'**URL en texte visible** | un lien invisible est incliquable, donc perdu |
| `[IFRAME src=…]` | `<a href="…">…</a>` | D6 tient, et la vidéo cesse d'être perdue |

**Invariant vérifiable après conversion : zéro `<img>`, zéro `<iframe>`, zéro origine tierce.**
Seuls des `<a href>` subsistent. Aucun mot de l'éleveuse n'est touché.

## 13. Interdits

- On n'écrit rien hors du §0.
- On n'**agrège** jamais deux mentions divergentes d'un même chien sans le dire : la fusion est
  visible et discutable, c'est tout l'objet de la provenance (contrat #19 §5).
- On ne **corrige** ni orthographe, ni accent, ni date, ni graphie de n° LOF, ni incohérence apparente.
- On ne **comble** aucun champ que la source n'énonce pas.
- On ne **fabrique** aucune fiche pour un chien que le site ne fiche pas.
- On ne **normalise** aucune valeur recopiée.

## 14. Arbitrages — désaccords tranchés et pourquoi

| Désaccord | Décision | Raison |
|---|---|---|
| Lire `source/` directement, ou transcrire dans des fichiers versionnés ? | **Transcrire, avec provenance** | Le reste écrit de #17 : un contenu qui ne vit que dans une base est invérifiable une fois la base détruite. |
| Analyseur automatique des `.md` ? | **Non** | 4 formats de ligne de parents sur 27 portées, un père nommé `X'Zorro`, un « Igor … x Maya » sans marqueur. Chaque heuristique est une machine à inventer un fait d'élevage. |
| `disponibilite` déduite de la date ? | **Non — 27 vides** | Raisonnement, pas recopie. Et le vide n'affiche **aucun** badge : coût quasi nul. |
| `statut` : tout vide, ou recopié quand le site l'énonce ? | **Recopié sous forme close (10/17), vide sinon (7/17)** | `DCD` et « Femelle reproductrice » sont des énoncés du site. La prose et le silence ne le sont pas. |
| `DCD` → `disparu` est-il une recopie ? | **Oui, et ratifiable** | Report dans une liste fermée ; le modèle n'a pas d'autre mot pour la mort. |
| Correspondance par mot-clé sur « retraité » ? | **Non** | `chien-grocky.md` ligne 94 classerait un chien **mort** en retraité. |
| Lier un parent sur une ressemblance de nom ? | **Non — identité stricte** | `H3F` = « Hameau des Trois Fontaines » est *presque certain*, et « presque certain » n'est pas « recopié ». |
| Lier, sachant que lier masque la santé de saillie ? | **Lier, et ouvrir la dette nommée** | La checklist l'exige, la valeur reste stockée, et un écart non écrit n'est imputable à personne (décision 46). |
| Fiche pour « Ipad » ? | **Non, à ratifier** | Une seule mention, en légende de photo. Une fiche ne porterait qu'un nom, et n'apparaîtrait nulle part faute de statut. |
| Première image = portrait ? | **Non** | Byte-identique sur 16 fiches : c'est un bandeau de rubrique. La règle naïve donnait 16 faux portraits. |
| Une pièce jointe par identifiant ou par SHA ? | **Par SHA** | 192 fichiers, 150 images. Les doublons sont le même octet. |
| `alt` composé, ou `alt` vide ? | **Composé, factuel, et dette déclarée** | Un `alt` vide affirmerait que 190 photos de contenu sont décoratives — un silence faux. |
| Recapturer `u2-2023` dans `source/` ? | **Non — dans `donnees/recaptures/`** | Contrat #19 §9 : aucun fichier de `source/` n'est réécrit. |
| Écrire dans `docs/migration/redirections.md` ? | **Non** | Fichier partagé et additif ; #21 peut y écrire au même instant, et rien ne rattraperait la perte. |
| Réutiliser `import-fixtures` par `require` ? | **Non — imiter la forme** | Il est déclaré lecture seule, et un couplage le rendrait immodifiable. Le fictif doit rester manifestement fictif (décision 50). |

## 15. Questions remontées — aucune n'est comblée

`Q-20-1` disponibilité des 27 · `Q-20-2` `DCD → disparu` et « Femelle reproductrice » → `reproducteur`
· `Q-20-3` statut des 7 fiches muettes, dont `happy` « retraite heureuse » · `Q-20-4` fiche pour
« Ipad » et les 5 autres chiens sans fiche · `Q-20-5` pedigree de Pégaz et de Tesla · `Q-20-6`
liaison par identité stricte, et la santé de saillie masquée · `Q-20-7` les deux vidéos YouTube ·
`Q-20-8` originaux pleine définition chez IONOS · `Q-20-9` qui **rend** le `noindex` · `Q-20-10` la
contradiction sitemap / `noindex`.

## 16. Ratifications de fin de chaîne

Trois points remontés par la chaîne, tranchés ici pour que le dépôt soit homogène.

| Point | Décision | Raison |
|---|---|---|
| **`_mtb_robots_source`**, clé non déclarée par `content/chien/champs.php` | **Ratifiée** — écrite, protégée, non enregistrée, **au format à provenance `{valeur, source, extrait}`** | C'est la **seule** façon de porter le fait `noindex` des 4 fiches sans sortir de l'empreinte. `content/` est interdit à cette chaîne. **Deux suites dues, et elles ne m'appartiennent pas** : déclarer la clé dans `content/chien/`, et la rendre (#23 / #24). |
| **`reference`, `identifiant`, `slug_source`, `pere.type` / `mere.type`** et la `reference` d'un parent : chaîne nue ou objet à provenance ? | **Les deux acceptées, sur ces clés techniques uniquement** | Ce ne sont pas des faits recopiés, ce sont des **identifiants**. Le §9.2 les exempte déjà du contrôle d'extrait. Imposer une forme obligerait à réécrire deux fichiers de 3 000 et 6 000 lignes **sans gagner un seul fait**. Toute autre clé exige la forme complète. |
| **Empreinte : deux fichiers hors du dossier du module** (`docs/migration/portees-chiens.md`, `docs/guide/contenu-repris-de-l-ancien-site.md`) | **Ratifiée**, §0 amendé | La chaîne doit produire sa déclaration de reprise et sa fiche d'aide (**D3**) : les laisser dans le module les rendrait introuvables. Les deux sont **neufs et nommés sur le domaine de l'issue** — aucune collision avec #21, qui se réserve `page-ce-qui-a-ete-repris-de-l-ancien-site.md`. Le commit est **scopé fichier par fichier**, l'index étant partagé. |
| **`pere.type` / `mere.type` en chaîne nue faisaient rejeter les 27 portées** | **Corrigé côté code**, jamais côté données | `type` n'est **pas un fait recopié** : `fiche` / `exterieur` est **notre décision de modélisation**, que le site source n'énonce jamais. Lui exiger un `extrait` verbatim est **impossible par construction**. Réécrire 54 clés pour inventer une provenance à une valeur que la source ne dit pas serait une **fausse provenance** — exactement ce que ce dispositif existe pour empêcher. Le contrôle de **valeur** (liste fermée) reste, seul celui de **forme** est levé. |
| **`docs/migration/source` n'était monté dans aucun conteneur** | **LEVÉ dans ce lot**, par le commit `01d4489` | Sans ce montage, ni le versement des photographies ni le contrôle des extraits ne pouvaient s'exécuter dans Docker. `01d4489` monte l'archive **en lecture seule** et livre l'interrupteur `MTB_FIXTURES`. **Ce n'est donc plus un prérequis, et plus une excuse** : tout message ou commentaire du module qui affirme encore que « `docs/` n'est monté nulle part » énonce un fait périmé et doit être réécrit. Les options `--source` et `--photos` restent, comme porte de sortie sur une pile ancienne ou un dossier déplacé. |

## 17. La forme de `_mtb_robots_source` — tranchée au niveau du lot

*Ajouté après la revue de lot, qui a mesuré la divergence.*

Cette clé est **partagée avec la chaîne #21**, et les deux chaînes l'ont d'abord écrite sous deux
formes incompatibles :

| Module | Forme écrite d'abord |
|---|---|
| `portees-chiens/` (#20) | une **chaîne** : `'noindex, nofollow'` |
| `resultats-pages/` (#21) | un **tableau à provenance** : `valeur`, `source`, `extrait` |

**Aggravant, et c'est le vrai défaut** : les deux documentations affirmaient l'alignement, **sans
qu'aucune ne dise que la forme différait**. Deux chaînes parallèles peuvent diverger — c'est le prix
assumé du parallélisme — mais elles ne peuvent pas *affirmer* converger sans l'avoir vérifié. C'est
la classe d'erreur de la décision 46 : pas un fait inventé, un fait tu.

**Décision du lot : la forme à provenance l'emporte**, et #20 s'aligne. Raison : *une valeur recopiée
sans sa source est une affirmation.* Cette clé existe précisément pour porter un fait du site source
que #24 devra honorer ; la dépouiller de sa provenance la réduirait à une assertion invérifiable.

L'`extrait` porte **les octets et rien d'autre** — pour les quatre fiches concernées, **les deux
balises `robots` de la page, dans l'ordre du document**, sans remplissage ni commentaire. C'est la
contradiction interne du source, et elle se recopie entière.

**Sur le caractère qui les joint, ne pas lire ce paragraphe seul** : le fichier de données porte
un \n littéral, mais **la valeur stockée en base porte une espace**. L'assainisseur replie tout
caractère de contrôle — il remplace la classe \x00 à \x1F plus \x7F par une espace — et le saut
de ligne vaut 0x0A, donc il tombe dans cette classe. **Le §17 ter fait foi.**

**Rappel de l'état réel** : la clé est **stockée**, elle n'est **pas rendue**. `wp_robots` et
l'exclusion du plan du site appartiennent à #23 / #24 (Q-20-9).

### 17 bis — La seule exemption au contrôle de sous-chaîne du §9.2, et pourquoi elle est inévitable

La consigne « l'`extrait` porte les deux balises » et la règle du §9.2 « l'`extrait` doit apparaître
**littéralement** dans sa source » **ne peuvent pas être vraies en même temps**, et il fallait le
mesurer pour le voir.

**Mesuré** : sur ces pages, les deux balises `robots` sont **à 1 716 octets l'une de l'autre**
(positions 266 et 1982). Une chaîne qui les réunit **n'apparaît nulle part** dans le fichier. Vérifié
aussi sur l'`extrait` de la chaîne #21, qui **n'est pas** une sous-chaîne littérale de
`html/placement.html` — la divergence n'est donc pas propre à #20.

**Ce qui cède est le contrôle, pas le fait.** La donnée à recopier est précisément la **coexistence**
d'un `noindex, nofollow` en tête de `<head>` et d'un `index,follow` bien plus loin. **Aucune
sous-chaîne contiguë ne peut l'exprimer.** Ne garder qu'une balise donnerait à lire une page
franchement non indexée — ce que la source ne dit pas, et ce serait un fait faux.

**Exemption gelée, et elle est unique** : le chemin **`robots_source.extrait`**, et lui seul, échappe
au contrôle de **contiguïté**. Pas la clé entière — `valeur` et `source` y restent soumises.

**Ce qui la remplace, pour ne pas troquer un contrôle contre rien** : *chacune des deux balises,
prise séparément, doit être présente dans le fichier source*, fins de ligne normalisées. On perd la
**contiguïté**, on ne perd pas la **vérifiabilité**. Mesuré sur les quatre fiches : **4 / 4**.

**Mesuré, pour que le coût soit chiffré et non supposé** : le balayage complet des deux fichiers de
données donne **1 115 valeurs non vides, 272 exemptées, 839 contiguës, et exactement 4 échecs** —
les quatre `robots_source.extrait`, aucun autre. L'exemption est donc étroite **au sens propre** :
elle ne couvre rien d'autre que ce qu'elle nomme.

### Deux précisions, sur des affirmations d'abord fausses

**1. Les espaces retirés n'étaient pas du remplissage.** La première rédaction disait qu'ils
n'étaient pas dans le document. **Ils y sont** : le `<head>` IONOS porte littéralement quatre espaces
d'indentation avant la balise et quatre après. L'ancienne valeur était donc **une sous-chaîne
contiguë parfaitement valide**. Ce qui a été fait n'est pas *retirer un remplissage inventé*, c'est
**écarter des octets réels d'indentation** pour adopter la forme de #21. L'écart est assumé, et il
doit être nommé pour ce qu'il est.

**2. Le contrôle de contiguïté ne rejetait rien aujourd'hui — le défaut est latent.** Les deux
chaînes n'implémentent pas le même contrôle :

| Chaîne | Implémentation | La forme jointe y |
|---|---|---|
| #21, `concordance.py` (`controle_7`) | **scinde sur `\n`** et compare ligne à ligne | passe par construction |
| #20, `verification.php` (`echec_dextrait`) | `strpos()` sur **la chaîne entière** | échouerait |

Or `robots_source` est **hors modèle**, donc absente de `sources_json()` : elle n'entre pas
aujourd'hui dans le contrôle de `verification.php`. **Le jour où quelqu'un l'y raccorde, il doit
scinder sur `\n` comme #21**, faute de quoi les quatre fiches tombent d'un coup. C'est écrit ici
pour que ce ne soit pas redécouvert par un échec.

**Garde-fou, rappelé du §9.2** : *une liste d'exceptions qui grossit est le signal que l'approche
dérape.* Celle-ci est la **première et la seule**. Toute exemption suivante doit être discutée, pas
ajoutée.

### 17 ter — Le caractère qui joint les deux balises : espace en base, `\n` dans le fichier

*Question posée par la chaîne, tranchée ici.*

**Le fichier de données joint les deux balises par `\n` ; la valeur écrite en base les joint par une
espace.** L'assainisseur de texte recopié est appelé en mode ligne unique
(`schema-fichier.php`, `assainir_recopie( …, false )`), et il replie les retours à la ligne — exactement
comme celui de #21, qui ne sait pas faire autrement.

**Ce n'est pas une perte de fidélité, parce qu'il n'y avait pas de fidélité à perdre.** Les deux
balises sont **à 1 716 octets l'une de l'autre** dans le `<head>` : **ni `\n` ni l'espace ne
reproduisent le document.** Les deux sont des jointures composées, et aucune n'est plus vraie que
l'autre. Le critère de fidélité étant hors-jeu, c'est **l'homogénéité du dépôt** qui tranche — et
c'était le motif même de la décision du §17.

**Conséquence à connaître, pour qui comparera un jour le fichier et la base** : la valeur stockée
**n'est pas caractère pour caractère celle du fichier de données**. C'est le seul champ de cette
reprise dans ce cas. Le contrôle aval ne s'y trompe pas — il compare la valeur stockée à la valeur
**ré-assainie**, pas à la valeur brute du fichier.

**Ce qui reste vérifiable, et qui est le vrai garde-fou** : chaque balise prise séparément figure
littéralement dans le HTML archivé, contrôle rejoué **4 / 4**. Le repli d'un caractère de jointure ne
touche aucune balise.