# Reprise des portées et des chiens — issue #20

Ce que l'import a repris de `mtbrabant.com`, ce qu'il a **délibérément laissé vide**, et les
**écarts** qu'il assume. Écrit pour être lu par quelqu'un qui n'était pas là — une revue, l'éleveuse,
ou la personne qui reprendra ce dépôt.

Le contrat gelé est `docs/contracts/issue-20.md`. Les fichiers de données transcrits vivent dans
`wp-content/plugins/mtb-core/includes/migration/portees-chiens/donnees/`.

## Ce qui est repris

| | Compte |
|---|---|
| Portées | **27 / 27** |
| Fiches de chiens | **17 / 17** |
| Photographies rattachées | **136** |
| Liaisons père/mère vers une fiche | **21** |
| Parents en nom libre | **33** |

Chaque valeur transcrite porte sa **provenance** : le fichier source et l'**extrait verbatim** dont
elle vient. C'est ce qui rend la reprise contestable par un tiers **une fois la base détruite** —
la leçon écrite de l'issue #17, dont le contenu ne vivait que dans une base de développement.

## Les quatre fiches non indexées — Q23, tranchée par l'utilisateur

`chien-halan`, `chien-ray-ban`, `chien-roxane`, `chien-youry` portent `noindex, nofollow` **en tête
de leur `<head>`** sur le site source, et sont **absentes des menus**, alors que le plan du site les
déclare. Vérifié à l'octet sur les 17 fiches : **ces quatre-là et aucune autre**. Aucune des
27 portées n'est concernée.

**Traitement retenu, sur décision de l'utilisateur : importées, hors menus, non indexées.**

- Elles sont **publiées**, comme sur le source, qui les sert `200` à tout le monde. Les mettre en
  privé ou en brouillon retirerait au visiteur un contenu qu'il pouvait lire, et ferait tomber D5.
- Elles n'entrent dans **aucun menu** : les menus sont construits à la main par l'éleveuse.
- Le fait `noindex, nofollow` est **recopié et stocké** avec sa provenance.
- **Le motif n'est pas écrit, parce que le site ne l'écrit nulle part.** C'est un fait d'élevage, et
  personne ici ne l'invente.

**Dette ouverte, et elle est sérieuse** : le fait est **stocké**, il n'est **pas encore rendu**.
`wp_robots` et l'exclusion du sitemap sont du comportement public et appartiennent aux issues #23
et #24. **Tant qu'elles n'ont pas tourné, ces quatre fiches sont indexables alors que ce dépôt
affirme le contraire.** Un fait stocké et non honoré est pire qu'un fait absent.

**Une contradiction du source qu'on ne peut pas recopier** : ces pages sont **au sitemap** *et* en
`noindex`. Les deux ne se recopient pas ensemble. On suit la balise — c'est un **arbitrage entre deux
énoncés contradictoires du source**, pas une recopie fidèle.

## Une généalogie que la source énonce et que le modèle ne sait pas porter

**La lice Rolex New Wave du Mont Brabant est née dans la portée R 2020, et rien dans la base ne le
dit.**

Le fait, mesuré des deux côtés :

| | |
|---|---|
| Portée | **R 2020** (`portee-r-2020.md`, lignes 74-76) |
| Chiot | `- ♀ Rolex du Mont Brabant`, `LOF_8832/1345` |
| URL portée par le chiot | `https://www.centrale-canine.fr/lofselect/chien/rolex-new-wave-7526621` |
| Fiche visée | `chien-rolex` (`chiens/chien-rolex.md` ligne 26 : **la même URL, à l'octet**) |
| Corroboration | la fiche `rolex` s'ouvre sur `17/03/2020`, qui **est** la date de naissance de R 2020 |

C'est la **même preuve** que celle qui a justifié quatre des vingt et une liaisons de parents : une
URL LOF Select identique des deux côtés, c'est-à-dire le site qui affirme, par le registre de la
Société centrale canine, qu'il s'agit du même chien.

**Rien n'a été posé, et c'est volontaire** : le modèle de contenu stocke les chiots comme une
**liste de valeurs** (`nom`, `sexe`, `lof`, `devenir`) et n'offre **aucun champ de liaison vers une
fiche** sur un chiot. Il n'existe donc nulle part où écrire ce fait sans détourner un champ de son
sens.

**Ce serait la première ligne à traiter le jour où le modèle offrira une liaison sur un chiot.** Et
la preuve d'un tel champ est déjà là : le corpus porte d'autres chiots liés par URL —
`ohrus-6984166` (O 2018), `pow-wow-7246387` (P 2019), `ragnar`, `ravie`, `resco`, `roadtrip`
(R 2020) — dont seul `rolex` a une fiche aujourd'hui.

Consigné ici parce qu'**un fait vrai que le code ne peut pas exprimer et qu'aucun document ne
consigne disparaît**. C'est la forme de défaut que les revues des lots 6 et 7 ont bloquée deux fois :
pas un fait inventé, un fait tu.

## Ce qui est resté vide, et pourquoi

Un champ vide dans cette reprise n'est **jamais** un oubli : chacun porte un `motif` écrit.

| Champ | Portée | Raison |
|---|---|---|
| `disponibilite` | **27 / 27** | Le site ne l'énonce **jamais**. L'encart « Chiots nés le 29/06/2026 / Tous réservés » est du mobilier de gabarit, identique sur les 52 pages, **y compris sur une portée de 1995**. Le lire comme la disponibilité de la portée affichée serait une invention. |
| `statut` | **7 / 17** | Le site n'énonce rien de clos pour `halan`, `happy`, `pegaz`, `ray-ban`, `roxane`, `you`, `youry`. |
| `date_deces` | les 7 chiens morts | Le site écrit `DCD` sans date, ou `DCD: 06/2018` — **mois et année, sans jour**. Écrire un jour l'inventerait. La mention survit verbatim dans le texte libre. |
| `pedigree` | `pegaz`, `tesla` | Voir « Les deux pedigrees » ci-dessous. |
| Portrait | **17 / 17** | Voir « Les photographies » ci-dessous. |
| Père de **S2 2021** | 1 portée | **Double saillie** : le site nomme deux mâles, `Jerry Lewis du Mont Brabant` **et** `Prinz Einhorn dit FITZ`. Le modèle ne sait porter qu'un père. Le champ reste vide **avec son motif**, et les deux noms survivent dans le texte libre. |

### Le statut : ce qui a été recopié, et ce qui ne l'a pas été

Le statut n'est écrit **sur aucune fiche**. Il a été recopié **seulement là où le site l'énonce sous
une forme close** :

- **`disparu` — 7 chiens** : `etch`, `gribouille`, `grocky`, `jango`, `maya`, `opium`, `tara`. Leur
  fiche porte `✞` et/ou `DCD` **sur la ligne d'identité du sujet**.
- **`reproducteur` — 3 chiennes** : `very-best`, `rolex`, `tesla`. La page d'accueil écrit
  « Femelle reproductrice » sous leur photographie.

**Ce qui a été refusé, et il faut le savoir :**

- **Aucune correspondance par mot-clé.** `chien-grocky.md` écrit « mâle **retraité** d'intervention de
  l'armée de l'air » — et Grocky est **`DCD`**. Un filtre sur « retraité » aurait classé un chien mort
  en retraité.
- **`happy` porte `STERILISEE`, pas une mention de décès.** Stérilisée n'est pas morte. Sa prose dit
  « coule à présent une retraite heureuse en Allemagne » : c'est de la prose, pas une forme close.
- **La légende « Tara - Etch - Ipad - Opium » n'est pas une liste de statuts.** C'est la légende
  d'**une** photographie sous le titre « Nos retraités et disparus ». Preuve interne : **Jango porte
  `DCD` et n'y figure pas.**

**Conséquence visible, assumée** : la page « La meute » et la Grille de chiens groupent **par
statut**. Les **7 fiches sans statut n'y apparaissent pas**, et leur fiche affiche « Statut · Non
renseigné ». C'est le bon défaut : il crie au lieu de mentir. Seule l'éleveuse peut le lever.

## Les photographies

**192 fichiers archivés, mais seulement 150 images distinctes.** **Quatre** groupes byte-identiques :
trois sont les **bandeaux de section** répétés sur chaque fiche de chien (16, 16 et 12 fichiers —
« Résultats expositions », « Résultats tests génétiques - santé », « Autres ») ; le quatrième est une
**photographie pleine taille citée par deux entités** (`14834172.png` et `16372512.png`,
1 095 104 octets).

Ce quatrième groupe mérite d'être noté : la première rédaction du contrat n'en comptait que trois, et
la chaîne l'a corrigée en recomptant. La règle appliquée n'est donc **pas une liste de fichiers** —
c'est une mesure : *une image dont l'empreinte est citée par plus d'une entité ne peut pas être un
portrait.* Elle couvre les quatre groupes, et ceux que personne n'a comptés.

**Aucune fiche de chien n'a de portrait, et c'est délibéré.** La première image d'une fiche **est**
un de ces bandeaux — celle de `chien-tesla` est un fichier de 7 758 octets partagé avec 15 autres
identifiants. Une règle « première image = portrait » aurait donné **le même faux portrait à
16 chiens**. Un portrait n'est posé que si l'image de tête a une empreinte **unique à sa page** ;
aucune ne l'a. **Une fiche sans portrait est un état prévu ; un faux portrait ne l'est pas.**

L'arithmétique se referme : **180 identifiants cités** par les 44 pages − **44 identifiants de
bandeau** = **136 rattachés**.

Les fichiers gardent leur **nom d'origine** (identifiant IONOS), jamais un nom de chien : nommer une
photo `jango-2.jpg` supposerait de savoir qui est dessus.

**La « pleine résolution » demandée par l'issue n'est pas atteignable.** IONOS ne sert aucun
original : sur 383 renditions, **4 dépassent 1024 px**, 49 plafonnent exactement à 1024 px, et
44 photographies font moins de 280 px de grand côté. **Ce qui est archivé est ce qu'on aura**, sauf
si l'éleveuse retrouve son accès administrateur IONOS.

**Texte alternatif** : le source n'en renseigne que **2**, repris verbatim. Les autres sont
**composés**, factuels, et disent **où la photo est publiée** — jamais qui est dessus. C'est une
**dette d'accessibilité déclarée**, pas une case cochée : seule l'éleveuse peut écrire ce que montre
une photo.

## Les deux pedigrees laissés vides

- **Pégaz** : sa page porte **deux** liens LOF Select — le sien
  (`…/chien/pegaz-eenhoorn-7678490`, dont le texte d'ancre est **une simple espace**, donc invisible)
  **et celui de Ray-Ban** (`…/chien/ray-ban-7578103`, qui porte le libellé visible). Le bon pedigree
  **est** sur la page ; c'est le lien **visible** qui pointe ailleurs. Choisir est un arbitrage.
- **Tesla** : la page d'accueil répartit son libellé « Lof Select » sur **deux chiens tiers**.

C'est un défaut de gabarit IONOS — un libellé coupé sur deux ancres — rencontré **trois fois** dans
ce corpus. Les deux champs restent **vides** en attendant l'éleveuse.

## Les liaisons père/mère

Le site ne lie **jamais** un parent à une fiche : `la-meute/` n'apparaît **pas une seule fois** dans
les 27 pages de portées. Les 21 liaisons ont donc été posées sur trois voies nommées, et **jamais sur
une ressemblance de nom** :

| Voie | Liaisons | Preuve |
|---|---|---|
| 1 | **16** | Nom **identique caractère pour caractère** à celui d'une fiche |
| 2 | **4** | **La même URL LOF Select des deux côtés, à l'octet** — le registre de la Société centrale canine affirme que c'est le même chien |
| 3 | **1** | Chaîne **byte-identique**, côté portée, à une chaîne déjà attestée par la voie 2 |

Les voies 2 et 3 ont été ajoutées en cours de route : sans elles, **Opium — la lice la plus employée
de l'élevage — restait non liée sur ses quatre portées**, à cause d'une **espace insécable**
introduite par le gabarit, qui coupe son nom en deux ancres. Ce n'est pas un fait d'élevage, c'est un
artefact.

**La voie 3 ne concerne qu'une seule liaison, et elle est nommée** : `S2 2021` mère → `opium`.
Sa page ne porte **aucune** URL LOF Select ; elle écrit la mère « Opium des Leus Chapellois » avec
**exactement les mêmes octets** que `o-2018`, `p-2019` et `r-2020`, dont les trois liaisons sont
attestées par le registre. C'est l'écart le plus délicat de cette reprise, et il est ici pour être
contesté : sans lui, Opium sortait avec **trois cartes de parent et un texte nu** sur ses quatre
portées. **Ce n'est pas une normalisation d'espaces** — la différence entre la fiche
(`Opium` + espace **insécable** + `des Leus Chapellois`, ancre coupée en deux par le gabarit) et les
portées n'a **jamais** servi à rapprocher les deux noms.

La voie 3 a été **bornée avant d'être appliquée** : les ancres du corpus ont été relevées, croisées
avec les 17 fiches, quatre chaînes se sont révélées attestées, et **une seule répétition** existe
dans tout le corpus. Elle ne rouvre donc rien d'autre.

**Ce qui reste non lié, faute de preuve des deux côtés** — et qui attend l'éleveuse :
`Ray Ban de l'Odyssée d'Hera` (u1-2023), `C'Halan von Bavaria` (o-2018), `Pegaz Eenhoorn` (s1-2021,
u2-2023, l'accent diffère), `R'U2 dit You` (v1-2024, « dit » contre « aka »), et les deux
`Tina dit Tara du H3F` (c-2007, d-2008).

**Un écart à connaître** : lier un parent **masque sur la page de portée** la chaîne de tests de
santé que le site y affichait, parce que le gabarit considère que « ses tests sont ailleurs ». Or
cette chaîne **date de la saillie** et peut différer de celle de la fiche. **La valeur reste stockée**
et n'est pas perdue ; elle cesse d'être affichée. Dette ouverte, propriétaire : l'issue des gabarits
de portée.

## Les chiens sans fiche

**Aucune fiche n'a été fabriquée.** Six chiens sont nommés par le site sans avoir de fiche :
**« Ipad »** — cité **une seule fois dans tout le corpus**, en légende d'une photographie — ainsi que
`Upper'Side`, `Jerry Lewis`, `Peregrine`, `Road Trip` et `Delhya` du Mont Brabant.

Leur fiche ne porterait **qu'un nom** : ni date, ni parents, ni test. Et sans statut, elle
n'apparaîtrait même pas sur « La meute ». Les noms survivent **verbatim** en nom libre et dans les
textes. Question ouverte pour l'éleveuse.

## Deux vidéos qui ne seront pas rejouables

`portee-c-2007` et `portee-o-2018` intègrent une vidéo **YouTube**. Un `iframe` YouTube est une
requête vers un domaine tiers : il ferait tomber la contrainte **D6** (zéro requête tierce, zéro
traceur, aucun bandeau de consentement). Le marqueur est **conservé verbatim dans le texte**, la
vidéo n'est **pas** rejouée. **C'est écrit, pas subi** — et c'est une question pour l'éleveuse.

## Ce qui a été recopié tel quel, et qui a l'air faux

*Ce qui semble faux dans le source **est** ce que le site affiche.* Rien n'a été corrigé :

- **16 graphies différentes de numéro LOF** — `LOF_13979`, `LOF_ 13462` (espace parasite),
  `LOF : 178`, `LOF31/`, `LOF : 3330/572`…
- **10 chiots de la portée A2 2025** nommés « A du Mont Brabant » avec un `LOF_` **vide** : la portée
  n'est pas encore inscrite. C'est l'état réel du site.
- `Diversité génétique : --%` — `--%` **est** la valeur.
- La faute « vertébre de transition lombonsacrée ».
- « Hameau des **Trois** Fontaines » (c-2007) contre « Hameau des **trois** Fontaines » (d-2008) :
  chaque page garde sa casse.
- La portée dont le titre dit **`"N_2"`** alors que son adresse est `/portée-n-2017/` : **les deux
  faits sont conservés**.

## Écarts de méthode, déclarés

- **`portee-u2-2023`** : la capture de l'ancienne passe coupait une ligne de tests de santé entre
  `ED0` et `ADN`. La valeur a été reprise depuis le **HTML brut archivé**, que le visiteur lit
  `(DM: N/N -SDCA2 1.1- HD:A- ED0 ADN)`. **Un seul signalement, documenté, aucun autre trouvé.**
  Aucun fichier de `docs/migration/source/` n'a été réécrit.
- **Dates** : 13 dates écrites en toutes lettres (« 31 Août 2025 ») ont été converties en
  `AAAA-MM-JJ`, le champ étant une date. **La forme d'origine survit** dans l'extrait de provenance
  et dans le texte libre.
- **Deux portées se contredisent elles-mêmes** sur leur effectif — le compte annoncé ne correspond
  pas à la liste des chiots. **Les deux versions ont été recopiées**, aucune n'a été « corrigée ».

## Trois pièges que la transcription a désamorcés

Chacun aurait produit un **fait d'élevage faux**, et chacun a été mesuré, pas supposé.

**1. La fiche de Maya inverse ses parents.** `chien-maya.md` écrit
`(Gribouille Langhske v.t Noorder Erf X Falco v.d Drei Eidgenossen)`, alors que `portee-m-1996.md`
— la portée du 02/12/1996, où Maya figure comme chiot, et qui est sa date de naissance — écrit
`♂ Falco v.d Drei Eidgenossen X ♀ Gribouille Langhske v.t Noorder Erf`. **L'ordre « (père X mère) »
est donc faux une fois sur cinq** (etch, happy, rolex, very-best concordent ; maya non).
Conséquence : la filiation d'une fiche n'a été remplie **que là où un marqueur `♂`/`♀` ou une phrase
du site donne le rôle** — 7 fiches sur 17. Les 10 autres gardent la parenthèse intégrale dans leur
texte libre. Sans ce constat, Maya recevait un père et une mère intervertis.

**2. La fiche de Halan est vide sur le site source.** `chien-halan.md` porte
« **Zone vide dans le HTML reçu.** Aucun contenu à reprendre », et le HTML brut le confirme : `Halan`
n'y apparaît que dans le `<title>` et l'`og:title`, et `cc_images` **zéro fois**. Sa fiche ne porte
donc **qu'un seul fait** — son nom d'usage — et **33 champs vides motivés**. Le rapprochement
tentant avec « C'Halan von Bavaria », le père de la portée O 2018, **n'a pas été fait** : c'est
exactement la ressemblance de nom que la reprise refuse.

**3. Une fiche porte bien un marqueur `♂`.** `chien-tara.md` écrit
`♂ Mac-Mahon des Hyènes de la Sensée X Loupi Pakita v.t Frouwkes Hof` — le marqueur ne donne pas le
sexe de Tara, mais celui de **son père**, sur sa propre fiche.

## Anomalies du site source — signalées, jamais corrigées

- **Deux graphies fautives différentes du même terme** : `vertébre de transition lombonsacrée`
  (tesla, you) et `vertebre de transition lombosacrée` (ray-ban, roxane). **Chacune conservée sur sa
  fiche.**
- **`chien-etch.md` écrit `MD : N/N`** au lieu de `DM`. Recopié tel quel.
- **`chien-etch.md` : « Dysplasie hanche et coude : HD : A/A »** — un seul résultat pour deux
  articulations. Le champ Coudes reste **vide**.
- **`chien-youry.md` : `DM : 1.1`** — une notation de type SDCA sur un champ DM. Recopié tel quel.
- **`chien-pegaz.md` : le lien « Portée du 11 Août 2021 » mène à `/portée-s2-2021/`**, or S2 est datée
  du 20 Août et sa mère est Opium. C'est **S1**, datée du 11 Août, dont Pegaz est la mère. **Le lien
  de la fiche désigne la mauvaise portée.** Rien n'a été corrigé.
- **`chien-rolex.md` nomme son père deux fois différemment** : `F'Grocky` puis « Le papa de Rolex est
  GROCKY ».
- **`chien-tesla.md` écrit `Identifié ADN` deux fois** sur la même fiche.
- **Espaces insécables porteuses de sens**, toutes préservées : c'est ce qui fait que
  `R'U2 aka You`, cité sur la fiche de Very Best, **n'est pas le même octet** que le nom de la
  fiche `you`.

## Un décès que le site raconte et que la fiche ne portera pas

`chien-you.md` écrit `R'U2 aka You 04/10/2020 - 15/05/2024`, puis « Malheureusement nous avons perdu
U2 suite à sa première portée ».

Le site **dit** que cette chienne est morte. Mais il le dit **en prose**, et la seconde date n'est
étiquetée ni `DCD`, ni « décès ». La règle de cette reprise ne recopie un statut que sous **forme
close** : `statut` et `date_deces` restent donc **vides**, et la phrase survit intégralement dans le
texte libre.

**C'est le vide le plus gênant du lot, et il est signalé comme tel** : une chienne dont le site
raconte la mort arrivera sur le nouveau site sans statut, donc **absente de « La meute »**. Une
réponse de l'éleveuse suffit à le lever.

## Ce que le modèle ne sait pas porter — constaté, jamais supprimé

Tout ce qui suit est **conservé intégralement** dans le texte libre, mais aucun champ ne l'accueille :

- **Les résultats d'exposition** — `etch` en a **15**, `jango` **10**. Ils tiennent une place majeure
  sur les fiches et n'ont aucun champ.
- **Les renvois de portées** depuis une fiche (« Mère de la portée "M" », « Portée prévue 2025 »).
- **Le qualificatif de l'identification ADN** : la case fermée `oui`/`non` perd « SCC »,
  « compatible parents », « + MyDogDna », « - SCC - Labo Genindexe ».
- **Les grands-parents** (« Vidéo du grand père de Opium : Symba ») : aucun champ.
- **`TI`** (grocky, pegaz) et « Enregistrée à Titre initiale » (tesla) : abréviation **non
  développée**, le champ Confirmation reste vide partout.

## Ce que porte la fiche de Pégaz — réponse explicite

La question était : le lien erroné a-t-il été recopié, ou le champ est-il vide ? **Les deux, et à deux
endroits différents.** Mesuré sur la fiche importée :

| Emplacement | Ce qui s'y trouve |
|---|---|
| Champ **Lien pedigree (LOF Select)** | **vide**, avec un motif écrit qui nomme les deux ancres et renvoie à Q-20-5 |
| **Texte libre** | **les deux ancres, verbatim** — celle de Pégaz (`…/chien/pegaz-eenhoorn-7678490`) et celle de Ray-Ban (`…/chien/ray-ban-7578103`) |

Autrement dit : **l'erreur du site source est recopiée fidèlement dans le texte**, comme tout le
reste, et **le champ structuré reste vide** parce que choisir laquelle des deux ancres est « le »
pedigree serait un arbitrage, pas une lecture.

**Une conséquence heureuse, et mesurée.** Sur le site source, l'ancre du **bon** lien est **une seule
espace insécable** : elle est invisible, donc incliquable. La conversion des marqueurs rend visible
l'URL de toute ancre vide — et c'est **le seul cas du corpus**, vérifié sur les 247 ancres. Le lien
correct de Pégaz, que l'ancien site cachait, devient donc cliquable pour la première fois. Le lien
erroné, lui, reste présent avec son libellé d'origine : on ne corrige pas le source.

## Obligation pour #24 — douze liens internes qui casseront

Après conversion, le texte des 44 contenus porte **69 ancres**, dont **12 pointent vers des pages de
portées de l'ancien domaine**. `mtbrabant.com` sera résilié : **si la carte de redirections de #24 ne
couvre pas ces douze cibles, le nouveau site porte douze liens internes cassés.**

Le danger tient à la façon dont on teste : les contrôles de #24 vérifieront **la carte**, pas les
liens qui s'appuient dessus. Ces douze-là ne se signaleront donc nulle part.

| Fiche / portée | URL citée dans le texte |
|---|---|
| `etch` | `https://www.mtbrabant.com/bhpl/portée-m-2016/` |
| `etch` | `https://www.mtbrabant.com/bhpl/portée-j-2014/` |
| `grocky` | `https://www.mtbrabant.com/bhpl/portée-r-2020/` |
| `jango` | `https://www.mtbrabant.com/bhpl/portée-m-2016/` |
| `jango` | `http://www.mtbrabant.com/bhpl/port%C3%A9e-n-2017/` — **seule en `http://` et en forme encodée** |
| `opium` | `https://www.mtbrabant.com/bhpl/portée-o-2018/` |
| `opium` | `https://www.mtbrabant.com/bhpl/portée-p-2019/` |
| `opium` | `https://www.mtbrabant.com/bhpl/portée-r-2020/` |
| `opium` | `https://www.mtbrabant.com/bhpl/portée-s2-2021/` |
| `pegaz` | `https://www.mtbrabant.com/bhpl/portée-s2-2021/` |
| `pegaz` | `https://www.mtbrabant.com/bhpl/portée-u2-2023/` |
| `rolex` | `https://www.mtbrabant.com/bhpl/portée-t-2022/` |

Neuf cibles distinctes : `m-2016`, `j-2014`, `r-2020`, `n-2017`, `o-2018`, `p-2019`, `s2-2021`,
`u2-2023`, `t-2022`. **Toutes les neuf sont des portées reprises par cette issue**, donc toutes ont
une page d'arrivée : la redirection est possible, il faut seulement qu'elle existe.

**Un bon signe, à dire aussi** : **aucune de ces douze ne pointe vers `cc_images`.** Les 180 adresses
d'images de l'ancien domaine étaient toutes enfermées dans un lien d'agrandissement, et la conversion
les a **toutes** retirées. Il ne reste donc, vers l'ancien domaine, **que des liens de page** — jamais
une image, jamais une requête.

## Deux écarts de comptage à ne pas redécouvrir trop tard

**136 photographies citées, 135 pièces jointes.** Ce n'est pas une perte. Deux identifiants cités —
`14834172.png` (fiche de `grocky`) et `16372512.png` (portée `R 2020`) — **partagent le même condensé
SHA-256** (`05dc5ac1…`, 1 095 104 octets) : c'est **une seule image**, publiée à deux endroits. La
règle « une pièce jointe par condensé distinct » la verse **une fois** et la rattache **aux deux**
entités. Un relecteur qui compte 136 d'un côté et 135 de l'autre a ici son explication.

**`photo` est un objet à provenance sur les 17 chiens**, là où la consigne de transcription annonçait
un identifiant nu. Le lecteur du moteur tolère les deux formes, donc **rien n'est à corriger dans le
code** — mais la consigne et la réalité divergent, et **un écart non écrit n'est imputable à
personne** (décision 46).
