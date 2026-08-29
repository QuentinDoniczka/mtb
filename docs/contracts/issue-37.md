# Contrat d'interface — Issue #37 — Captures d'écran du guide de l'éditrice

**Gelé le 2026-08-29.** Contrat inhabituel : **#37 n'écrit aucune ligne de code.** Ni thème, ni
extension, ni `theme.json`. Les sections « fonctions de lecture » et « blocs enregistrés » du gabarit
sont donc **sans objet, et non pas vides par oubli**. Ce que ce document gèle, ce sont les décisions
qui font qu'une capture est une **preuve** et non une illustration : où elle est prise, sous quelle
session, avec quelles données, et ce qu'on s'interdit de fabriquer.

---

## 1. Le décompte — mesuré, pas hérité

L'issue s'intitule « Prendre les **33** captures ». Ce chiffre a été **recompté contre les fiches
réelles**, deux fois, par deux méthodes.

**Il était juste pour son périmètre, et il est périmé.** Les six fiches nommées par la tâche 1 de
l'issue promettent bien **exactement 33** captures :

| Fiche nommée par l'issue | Captures promises |
|---|---|
| `composant-bandeau-ouverture.md` | 6 |
| `composant-fiche-information.md` | 7 |
| `composant-galerie-photos.md` | 8 |
| `composant-encart-derniere-portee.md` | **0** |
| `composant-liste-portees.md` | 7 |
| `composant-grille-chiens.md` | 5 |
| **Total** | **33** |

Douze fiches ont été livrées depuis. **Le décompte réel au 2026-08-29 est de 116 promesses réparties
sur 18 fiches** : **109 noms de fichiers distincts** sur 17 fiches, plus **7 promesses sans nom de
fichier** dans `resultat-ajouter-un-resultat.md`.

**Deux pièges de comptage, à écrire pour que personne ne les repose :**

1. `portee-ajouter-une-portee.md` nomme ses **9** fichiers **sans le préfixe `captures/`**. Un
   `grep 'captures/'` — le réflexe naturel — les rate tous les neuf.
2. `resultat-ajouter-un-resultat.md` porte **7 encarts « Capture à prendre » sans aucun nom de fichier
   ni tableau récapitulatif**. C'est la promesse qu'aucun inventaire ne retrouve.

Erreur de l'issue elle-même, consignée : sa tâche 1 range `encart-derniere-portee` parmi les six
fiches à traiter, alors que cette fiche **ne promet aucune capture**.

## 2. L'arbitrage de fond : ce que #37 livre réellement

**L'issue nomme correctement le défaut et se trompe de remède.** Elle écrit : « le libellé de l'entrée
de menu qui supprime un composant n'est décrit que par sa place à l'écran, jamais nommé, faute de
capture à annoter ». Le manque est donc un **fait non relevé** — et un fait se relève **une fois**, il
ne se photographie pas onze fois.

Mesuré : les libellés inconnus de tout le guide se réduisent à **huit chaînes de WordPress**. La
première à elle seule est réclamée par **onze fiches**. Livrer onze images pour transmettre une chaîne
de neuf caractères est la version documentaire de la recopie que la **contrainte 3** interdit.

**L'objet de #37 est donc requalifié**, et l'objet requalifié est plus large que l'objet d'origine, pas
plus étroit :

> Aucune fiche du guide ne décrit plus un libellé par sa seule position à l'écran, et aucune fiche ne
> porte de liste de tâches adressée aux développeurs.

Cette requalification étend la portée de **6 fiches à 18**. Elle est déclarée au lead, pas subie.

## 3. Les huit libellés — relevés à l'écran, jamais devinés

Relevés le 2026-08-29 sur la stack en marche, **en session `fabienne`**, WordPress 6.9 `fr_FR`.
**Aucun n'est tiré d'un fichier `.po`** : traduire `Delete` ne prouverait pas que l'entrée du menu
*est* `Supprimer`.

| # | Ce que les fiches décrivaient par sa position | Chaîne exacte | Réclamée par |
|---|---|---|---|
| 1 | l'entrée du menu à trois points qui supprime le composant | **`Supprimer`** (`Shift+Alt+Z`) | **11 fiches** |
| 2 | le bouton qui pose un lien | **`Lien`** (`Ctrl+K`) | 1 fiche |
| 3 | le champ de recherche de l'insérteur | **`Rechercher`** | plusieurs |
| 4 | le bouton d'insertion de la médiathèque | **il n'y en a pas un seul — voir l'encadré ci-dessous** | 2 fiches |
| 5 | le bouton qui ferme « Choisir une composition » | **`Fermer`** (`aria-label` ; une croix, aucun texte visible) | 1 fiche |
| 6 | les entrées « Toutes les pages » / « Ajouter une page » | **`Toutes les pages`** · **`Ajouter une page`** | plusieurs |
| 7 | le champ de description d'un média | **`Texte alternatif`** — *les trois fiches qui l'écrivaient ainsi avaient raison* | 3 fiches |
| 8 | la rubrique de l'insérteur | **`Mont Brabant`**, en **première position** | plusieurs |

**Fait structurant, mesuré sur trois blocs différents** : le menu à trois points **n'a pas le même
contenu selon le bloc** (11 entrées sur `mtb/galerie-photos`, 10 sur `mtb/bandeau-alerte`, 15 sur un
paragraphe) — mais **`Supprimer` en est toujours la dernière entrée**. C'est cette invariance, et non
une capture, qui permet à une fiche de dire quelque chose de vrai pour tous les blocs.

Précision relevée et à ne pas confondre : `Texte alternatif` et `Description` sont **deux champs
distincts** de la médiathèque, dans l'ordre `Texte alternatif`, `Titre`, `Légende`, `Description`.
Les fiches visent bien le premier.

### Correction du libellé n° 4 — ce contrat s'était trompé

**La première version de ce contrat figeait `Sélectionner` comme *le* bouton d'insertion de la
médiathèque. C'est faux, et il faut l'écrire ici plutôt que de le corriger en silence.** Deux passes
indépendantes l'ont réfuté — l'une à l'écran, l'autre dans le code — et la vérification tient en deux
lignes :

| Écran d'où la fenêtre est ouverte | Libellé réel du bouton | Source |
|---|---|---|
| les composants (`galerie-photos`, `fiche-information`, `bandeau-ouverture`, `coordonnees-plan`) | **`Sélectionner`** | défaut du cœur, aucun `button` déclaré |
| encadré **Photos et pedigree** d'un **chien** | **`Ajouter à la galerie`** | `includes/fields/chien/galerie.js:165`, chaîne en dur |
| encadré **Galerie photos** d'une **portée** | **`Ajouter des photos`** | `includes/fields/portee/ecran.js:257-262`, repris de `bouton.textContent` |

**Aucune fiche ne peut donc affirmer un libellé unique**, et une fiche qui écrirait `Sélectionner` sur
l'écran d'une portée serait fausse. C'est la forme de défaut qui a bloqué les lots 6, 8 et 9 : le dépôt
affirme ce que le logiciel ne fait pas.

**Leçon de méthode, et elle vise ce document.** `ETAT.md` l'a déjà écrite au lot 9 : *un donneur d'ordre
qui dicte une formulation sans la vérifier fabrique la prochaine erreur du dépôt*. Un libellé relevé sur
**un** écran avait été généralisé à **tous** — la mesure était juste, la portée que je lui ai donnée ne
l'était pas.

Correction d'une affirmation du dépôt : `composant-formulaire-contact.md` déclare le nom de la
rubrique de l'insérteur inconnu. Il ne l'est pas — il est écrit dans le code
(`wp-content/plugins/mtb-core/includes/blocks/categorie-mtb/bootstrap.php`) **et confirmé à l'écran**.

## 4. Conditions de prise — contraignantes

| Règle | Valeur gelée | Pourquoi |
|---|---|---|
| Session | **`fabienne` (rôle éditeur) exclusivement** | Mesuré : la barre latérale diffère. `admin` affiche 15 entrées dont **`Apparence`, `Extensions`, `Réglages`** ; `fabienne` en affiche 12. Une capture prise en administrateur montrerait à l'éleveuse **quatre entrées qu'elle n'a pas**. |
| Jeu de données | la stack provisionnée, **`MTB_FIXTURES=1`** | Décision 50, Q17 « fictif assumé ». |
| Largeur | **1280 px** | Lisible sans zoom ; mesuré à ~75 Kio par capture, soit ~8 Mio pour l'ensemble. Le poids n'est pas un facteur limitant. |
| Bandeau de mise à jour | **masqué à la prise** (`.update-nag`) | Un bandeau « WordPress 7.1 est disponible ! » coiffe **tous** les écrans d'administration en session `fabienne` et polluerait une trentaine de captures. Masquer un bandeau du cœur qui n'appartient pas à l'écran documenté n'est pas une retouche du contenu documenté. |
| Retouche | **aucune** | Recadrer et entourer est permis ; ajouter, effacer ou reformuler un libellé ne l'est jamais. |

## 5. États spéciaux

| État | Traitement gelé |
|---|---|
| `photo_principale_absente` | **Capturé tel quel, cadres gris compris.** Voir §6 — c'est la décision la plus lourde de ce contrat. |
| `plan_acces_inexistant` | **1 seule capture déclarée impossible** — `coordonnees-legende-image.png`, la seule qui exige réellement une image de plan. Q18-Q20 ouvertes. **Corrigé après mesure : l'interdit d'origine portait sur 3 fichiers, il était trop large de deux** — voir l'encadré sous ce tableau. |
| `menu_absent` | Menu d'exemple construit **à partir de pages qui existent réellement**, et la fiche **dit que c'est un exemple**. Q22 ouverte. |
| `liste_admin_mouvante` | Captures des listes d'administration prises **après le commit de #28**, ou déclarées à reprendre. Voir §7. |
| `bloc_multiple_false` | Quatre blocs (`bandeau-alerte`, `bandeau-ouverture`, `derniere-portee`, `formulaire-contact`) apparaissent **désactivés** dans l'insérteur s'ils sont déjà dans la page. Leurs captures d'insérteur se prennent **sur une page vierge**. *Mesuré ensuite : sur une page vierge ils ne sont pas désactivés, la précaution suffit.* |
| `liste_deroulante_native` | **7 captures non prenables en pilotage automatique.** Chromium dessine les listes `<select>` et `<datalist>` **hors de la page**, dans un widget du système : `page.screenshot()` ne les voit pas. Ce n'est pas un défaut du site ni un manque de zèle — c'est une limite du navigateur. Elles exigent **une passe manuelle**. |

### Correction de l'interdit sur le plan d'accès — ce contrat était trop large

La première version interdisait **trois** captures au motif qu'aucune image de plan d'accès n'existe.
La passe de captures a contesté l'interdit **au lieu de s'y soumettre**, et elle a eu raison sur deux
des trois :

| Capture | Verdict après mesure | Raison |
|---|---|---|
| `coordonnees-panneau-plan.png` | **prenable** | Elle ne montre que le **panneau de réglages** — bouton **Choisir un plan**, zone **Description de la photo**, paragraphe **Légende**. Il s'affiche entièrement **sans qu'aucune image de plan existe**. |
| `coordonnees-plan-introuvable.png` | **prenable** | Elle montre l'**avertissement** affiché quand l'image choisie a disparu de la médiathèque. C'est un état réel, provoqué par un identifiant pointant dans le vide — **aucune carte n'est fabriquée**. |
| `coordonnees-legende-image.png` | **impossible, confirmé** | Elle exige une **image de plan à l'écran**. Aucune n'existe, et #11 a délibérément refusé d'en produire une plausible (Q18-Q20 : deux points GPS distants de 2 km, personne ne sait lequel est l'élevage). |

**Ce que j'avais confondu** : « aucune image de plan n'existe » et « aucun écran du plan d'accès n'est
montrable ». La première est vraie, la seconde ne l'était pas. Un interdit posé un cran trop large coûte
deux captures — et, si personne ne le conteste, il devient la raison écrite pour laquelle une fiche
reste incomplète.

## 6. Ce qu'on s'interdit de fabriquer — et le cas qui a failli passer

**Une capture est une mesure, au même titre qu'une valeur de `RELEVE.md`.** Une capture recomposée, un
état simulé, une flèche posée sur un écran non observé sont la même faute que le fait de domaine
inventé. Recadrer et surligner n'affirment rien de neuf ; ajouter un libellé, si.

**Le cas concret, et il faut l'écrire parce qu'il était tentant.** La reconnaissance a signalé que
l'accueil rend **quatre cadres gris vides** dans la grille des reproducteurs : mesuré, **0 portée sur
31** et **1 chien sur 22** possèdent une photo principale, et l'unique portrait existant est une image
de test barrée de « PHOTO DE TEST — ne pas utiliser en production ». La correction évidente était de
renseigner ces photos pour obtenir de plus belles captures.

**C'est refusé, et ce n'est pas un choix esthétique.** `contenu-repris-de-l-ancien-site.md` §3, intitulé
« Aucune fiche de chien n'a de portrait », **documente déjà cet état et l'explique à l'éleveuse** : sur
l'ancien site, la première image de chaque fiche était une **bannière de rubrique identique sur seize
fiches**, et la prendre pour un portrait aurait posé **la même fausse photo sur seize chiens**. Le lot 8
a délibérément refusé de le faire. Le faire ici, pour la photogénie d'une capture, serait exactement
l'invention que le lot 8 a refusée — avec l'aggravation que l'image deviendrait une **pièce du guide**.

**Les cadres gris sont donc l'état vrai d'un site fraîchement repris, et ils sont capturés tels quels.**
Conséquence assumée et à ne pas redécouvrir comme un défaut : les captures « sur le site » de la grille
de chiens et de l'index des portées ne montrent pas un site fini.

## 7. Ordonnancement imposé par le lot

L'issue **#28** ajoute colonnes et filtres aux **listes d'administration** (portées, chiens, résultats)
**pendant** cette chaîne, dans le même arbre de travail. La stack bind-monte `wp-content/` en direct :
toute capture d'une de ces listes reflète l'état du code **à l'instant de la prise**.

Règle gelée : les captures visant un écran « Toutes les portées / Tous les chiens / Tous les
résultats » se prennent **en dernier**. Si #28 n'a pas commité à temps, elles sont **déclarées
nommément à reprendre** — jamais livrées en silence alors que le lot lui-même les périme.

## 8. Interdits

- On ne renseigne **aucune** donnée de contenu (photo principale, statut, disponibilité) dans le seul
  but d'embellir une capture.
- On ne prend **aucune** capture en session administrateur.
- On ne **fabrique** ni ne retouche un écran ; une capture impossible est **déclarée nommément dans la
  fiche**, en français, adressée à l'éleveuse — jamais laissée en trou silencieux.
- On ne **devine** pas un libellé depuis un `.po` : on le lit à l'écran, ou on dit qu'on ne l'a pas lu.
- On n'exécute **jamais** `docker compose down -v` : la base est partagée avec deux autres chaînes.
- On n'écrit **rien** hors de l'empreinte : `docs/guide/captures/**`, `docs/guide/*.md`,
  `docs/contracts/issue-37.md`.

## 9. Arbitrages

| Désaccord | Décision | Raison |
|---|---|---|
| Périmètre 33 ou 116 ? | **116 promesses / 18 fiches** reconnues ; l'objet requalifié couvre les 18 | Livrer 33 laisserait **douze fiches** portant en bas de page « Aucune n'est encore prise » — une liste de courses de développeur dans un document client. Un guide à deux régimes visibles est pire qu'un guide illustré selon une règle uniforme. |
| Une image par promesse, ou aucun libellé décrit par sa position ? | **Le second** | Les deux objectifs ne se recouvrent pas, et le second est celui que l'issue nomme. Il se paie avec **huit chaînes relevées** au lieu de onze captures redondantes. |
| Un libellé écrit, ou une capture du menu ? | **Écrit en toutes lettres, la capture en appui** | Une chaîne écrite est lisible au lecteur d'écran, à 200 %, à l'impression. Une capture d'un menu déroulant où il faut retrouver le mot est **le moins accessible des supports** — et l'AA est bloquante. |
| Renseigner les photos principales pour de plus belles captures ? | **Non** | §6. |
| Les sections « Captures d'écran à prendre » restent-elles ? | **Retirées** | Ce sont des notes de chantier adressées à nous, à l'intérieur d'un document destiné à l'éleveuse. Ce qui fait paraître un guide inachevé n'est pas le nombre d'images : c'est l'aveu. |

## 10. Ce que ce contrat ne couvre pas

- **Le support de livraison du guide.** 23 fichiers Markdown dans un dépôt git ; l'éleveuse n'ouvrira
  jamais GitHub. C'est le périmètre de **#25**, bloquée. Les décisions de §4 (largeur 1280, recadrage
  au panneau) sont prises pour être **robustes** à ce choix, pas pour le préempter.
- **`docs/guide/README.md`** — le prompt de `doc-client-mtb` impose de le tenir à jour ; **ce fichier
  n'existe pas**. Le guide n'a aucun index. C'est un trou D3 réel, hors de l'objet de #37 : signalé,
  non comblé ici.
- **`docs/apercus/` et `docs/guide/captures/`** — deux conventions de captures d'écran coexistent
  désormais dans le dépôt. À trancher hors #37.

---

## 11. Ce qui a été livré — mesuré à la clôture

| Mesure | Valeur |
|---|---|
| Fichiers PNG dans `docs/guide/captures/` | **112** |
| Références d'image dans les fiches | **112** |
| Références mortes · fichiers orphelins | **0** · **0** |
| Textes alternatifs vides | **0** (minimum mesuré : 85 caractères) |
| Restes de chantier (« Aucune n'est encore prise », « reste à relever ») | **0** |
| Poids ajouté | **7,1 Mio** |
| Fiches modifiées | **21** |
| Volume de prose | **464 insertions, 733 suppressions** — le guide a *rétréci* |

Le décompte de départ était de **116 promesses sur 18 fiches**. La cible a bougé en cours de route, et
c'est normal : #28 a livré pendant cette chaîne une fiche neuve
(`listes-retrouver-un-contenu.md`) qui promettait **5 captures de plus**, et
`resultat-ajouter-un-resultat.md` a reçu les **7 noms** qui lui manquaient.

**9 captures sont déclarées manquantes, nommément, dans les fiches** — jamais effacées en silence :

| Capture | Raison |
|---|---|
| `portee-disponibilite` · `repris-disponibilite` · `liste-portees-annee-ouverte` · `coordonnees-ecran-page-de-contact` · `menu-selection` · `resultat-discipline` · `resultat-niveau-suggestions` | **Liste déroulante native.** Le navigateur les dessine hors de la page ; aucun outil de capture automatique ne les voit. Remplacées dans les fiches par **l'énumération des choix réels** — plus utile à l'éleveuse qu'une image, et lisible au lecteur d'écran. |
| `coordonnees-legende-image` | **Fait de domaine.** Exige une image de plan d'accès à l'écran ; aucune n'existe (Q18-Q20). |
| `liste-portees-etat-vide` | Aurait exigé de **dépublier les 31 portées** de démonstration. L'écran est décrit en une phrase. |

## 12. Affirmations non vérifiées, sorties du guide et conservées ici

Une section intitulée « Ce qui reste à vérifier à l'écran » vivait en fin de
`composant-formulaire-contact.md` : une note adressée **aux développeurs**, dans un document destiné à
l'éleveuse. Elle a été retirée du guide — **mais son contenu ne devait pas disparaître avec elle**, et
c'est ici qu'il vit désormais.

Deux affirmations restent **lues dans le code et jamais constatées à l'écran** :

1. Le composant se trouve aussi en tapant « formulaire », « écrire », « courriel », « message » ou
   « mail » dans la recherche de l'insérteur. **Seul « contact » a été essayé.**
2. La touche Entrée dans la phrase d'information va à la ligne et ne crée pas un second composant.

Trois autres ont été **reformulées dans le guide** pour ne plus rien affirmer au-delà du constat : le
sujet du courriel reçu et la recopie de l'adresse du visiteur ; le comportement du formulaire posé dans
un haut ou un bas de page ; et surtout la **remise réelle d'un courriel à une vraie boîte**, qui n'a
jamais été constatée. Cette dernière est devenue, dans la fiche, une **action confiée à l'éleveuse** —
un envoi d'essai vers sa propre adresse le jour de la mise en ligne, avec ce qu'elle doit voir arriver.
Un aveu qui ne lui servait à rien est devenu une vérification qui la protège.

**Deux libellés restent non relevés** et sont signalés comme tels dans les fiches : l'option
`Modifier` de la liste **Actions groupées** (la capture montre la liste fermée), et la casse exacte du
titre du panneau de modification groupée — affiché en capitales par mise en forme, donc **non nommé**
dans le guide plutôt qu'inventé.
