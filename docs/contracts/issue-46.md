# Contrat d'interface — Issue #46 — Les deux dernières captures du guide (T57)

**Gelé le 2026-08-31.** Contrat de la même famille que `issue-37.md` : **#46 n'écrit aucune ligne de
code.** Ni thème, ni extension, ni `theme.json`. Les sections « fonctions de lecture » et « blocs
enregistrés » du gabarit sont donc **sans objet, et non pas vides par oubli**. Ce que ce document gèle,
c'est l'ordonnancement d'états de la pile qui rend quatre captures possibles, et ce qu'on s'interdit de
fabriquer pour les obtenir.

---

## 1. Le décompte réel — mesuré à `943ab4f`, contre l'énoncé de l'issue

L'issue demande de « vérifier la bijection après ajout : **121 citées / 121 présentes / 121 suivies** ».
Le mot **après ajout** est la clé, et il est facile à lire de travers. Mesuré à `943ab4f`, par la
méthode gelée par `issue-37.md` §1 :

```
grep -roh "[A-Za-z0-9._-]*\.png" docs/guide/ | sed 's|.*/||' | sort -u | wc -l   → 119
ls docs/guide/captures/*.png | wc -l                                             → 119
git ls-files docs/guide/captures/ | wc -l                                        → 119
```

**Le guide est aujourd'hui cohérent avec lui-même à 119/119/119.** Il n'a pas deux références mortes en
attente : #37 a **retiré** les deux références et les a remplacées par un paragraphe « Cet écran n'est
pas illustré ». Les noms `liste-portees-etat-vide.png` et `coordonnees-legende-image.png` ne vivent
plus que dans `docs/ETAT.md` et `docs/contracts/issue-37.md` — **zéro occurrence dans `docs/guide/`**.

**Conséquence opératoire, et c'est le piège n° 1 de cette issue** : déposer les deux PNG sans **ajouter
la ligne `![…](captures/…)`** dans chaque fiche fabrique **deux orphelins** et casse la bijection qu'on
croit fermer. Le geste est en deux temps, jamais en un.

## 2. Ce que l'énoncé de l'issue se trompe à demander — quatrième instance du motif

`docs/ETAT.md` consigne depuis trois lots qu'« un énoncé peut décrire un vrai défaut et se tromper
entièrement sur ce qui le répare ». En voici une quatrième instance, dans l'énoncé de #46 lui-même.

L'issue demande de reprendre **« l'image ET son texte de remplacement »** de
`galerie-description-photo.png`, au motif que ce texte « nomme déjà le champ (*avec le champ **Texte
alternatif** en premier*) ». **C'était vrai à la rédaction, ce ne l'est plus.** Mesuré à `943ab4f`,
`composant-galerie-photos.md:131` porte déjà :

> …avec le champ **Description de la photo (pour les personnes aveugles)** en premier, suivi de Titre,
> Légende et Description

Le texte de remplacement **a été corrigé par la chaîne #35 elle-même** (`6af2add`). **Il ne reste que
les pixels.** Une chaîne qui « corrigerait » cette alternative réécrirait une phrase déjà juste.

**Et le corollaire, qui est le piège n° 2** : `grep -rn "Texte alternatif" docs/guide/` ne rend plus
qu'**une seule ligne** dans tout le guide — `composant-galerie-photos.md:124`. Elle est **vraie et
délibérée** : c'est l'exception des blocs **Image** et **Galerie** du cœur, dont les étiquettes viennent
de `wp.i18n` et restent hors d'atteinte de tout filtre PHP (dette **T-#35-a**). La dernière tâche de
l'issue la nomme d'ailleurs comme exception. **Ne pas la toucher.**

## 3. Arbitrage A — `coordonnees-legende-image.png` : `issue-37.md` avait tort

`issue-37.md` §5 et §11 déclarent cette capture **« impossible, confirmé »** : « elle exige une **image
de plan à l'écran**. Aucune n'existe, et #11 a délibérément refusé d'en produire une plausible
(Q18-Q20) ». **Cet interdit est faux, et c'est le troisième interdit trop large du même tableau** — le
contrat #37 a déjà confessé les deux autres (`coordonnees-panneau-plan.png` et
`coordonnees-plan-introuvable.png`, tous deux déclarés impossibles puis mesurés prenables).

**Décision : la capture est prenable, sans fabriquer aucun plan.** Motif, lu dans la fiche et non
déduit : `composant-coordonnees-plan.md:114-118` enseigne **deux faits**, et aucun des deux ne dépend de
ce que l'image représente :

1. **où** est la colonne (« la colonne de droite de la fenêtre où vous choisissez l'image ») ;
2. **à quel rang** y figure **Légende** (« en troisième position »).

La colonne de droite du sélecteur de médias rend ses quatre champs pour **n'importe quelle** pièce
jointe de type image. « Il faudrait une image de plan » n'a jamais été une mesure : c'est l'interdit de
`issue-37.md` §5 **recopié dans le guide**, adressé à l'éleveuse en tant que raison pour laquelle elle
n'a pas d'image. `docs/ETAT.md` (T57) la décrivait d'ailleurs correctement : « *un détail de la fenêtre
de médiathèque* » — jamais « exige un plan ».

**Deux parades obligatoires, cumulatives.** Une capture plein cadre montrant une photo de chien
sélectionnée dans une fenêtre ouverte par **Choisir un plan** mettrait en scène un geste que la fiche
**interdit trois lignes plus haut**. Ce serait un mensonge non pas sur un libellé mais sur une
intention, et il est plus difficile à repérer.

| # | Parade | Contenu gelé |
|---|---|---|
| 1 | **Recadrage sur la colonne de droite** | La grille de vignettes **sort du cadre**. Il ne reste que ce que le paragraphe promet : les quatre champs dans l'ordre, **Légende** mis en évidence. C'est permis par `issue-37.md` §4 (« recadrer et entourer est permis »). |
| 2 | **Réécriture du paragraphe 114-118** | La fausse raison saute. La phrase de remplacement **n'appelle jamais l'image un plan** et dit qu'aucun plan n'existe encore, en renvoyant à la section suivante. |

**Ce qui ne bouge pas d'un mot** : la section « Aujourd'hui, il n'y a pas de plan — et c'est voulu »
(lignes 122-145), les **deux questions posées à l'éleveuse**, et l'absence de carte. **La capture n'a
pas le droit de laisser croire que Q18-Q20 sont tranchées.**

## 4. Arbitrage B — « trois photos cochées » : l'image s'aligne sur le texte

L'issue demande de « corriger l'écart *une photo cochée* contre *trois photos cochées* » **sans dire
dans quel sens**. Décision : **on reprend les pixels avec trois photos réellement cochées**, et le texte
de remplacement de `galerie-fenetre-photos.png` **n'est pas touché**.

| Raison | Preuve |
|---|---|
| Le pluriel **est** l'enseignement | `composant-galerie-photos.md:53-54`, étape 5 : « cochez **celles** que vous voulez ». Une image à une seule coche illustre un cas que l'étape ne décrit pas et suggère en creux un choix un par un. |
| Le PNG est repris de toute façon | Sa reprise est due pour le libellé de #35. Cocher trois vignettes au lieu d'une ne coûte que deux clics de plus. |
| Dégrader l'alternative serait le travers refusé par #35 | `issue-35.md` §3 a refusé de remplacer le texte d'aide du cœur par une phrase qui en disait moins. Réparer une phrase juste par une phrase plus pauvre est la même faute. |

**Et cela impose la migration**, ce qui est le vrai coût de cet arbitrage : la médiathèque provisionnée
ne contient qu'**une seule** pièce jointe (`issue-35.md` §9, et `docker/fixtures/photos/` ne porte qu'un
fichier), et cette image est **barrée « PHOTO DE TEST — ne pas utiliser en production »**. La mettre
trois fois dans le manuel de l'éleveuse répéterait le défaut « PORTEE ESSAI 37 » que la passe
d'intégration du lot 10 a fait reprendre — **en pire, puisqu'il serait livré**.

La capture d'origine avait d'ailleurs **elle-même été prise sur une base migrée** : `issue-35.md` §10 a
lu dans le PNG la valeur « *Photographie publiée sur la page de la portée U3 2023* ». Reprendre ces
pixels sur une médiathèque à une image ne serait pas une réparation, ce serait une **régression de
fidélité**. Une reprise change **une** chose, et rien d'autre.

**Repli, et uniquement celui-là** : si la médiathèque ne peut pas être peuplée honnêtement, on corrige
l'alternative en « une photo cochée ». **On ne téléverse jamais d'images pour faire nombre.**

## 5. Arbitrage C — l'ordonnancement des états : forcé, pas choisi

L'état vide n'existe **qu'avant** la migration ; les trois captures média n'existent **qu'après**.
Un seul `down -v`, **deux** provisionnements, **une** migration. Aucun troisième cycle.

| # | Action | État obtenu | Ce qu'on y prend |
|---|---|---|---|
| 1 | `down -v`, puis démarrage **`MTB_FIXTURES=0`**, attendre `[provision] terminé.` | 0 portée, 0 chien, 0 résultat ; 1 image (test) ; pages Accueil / Contact / Espace privé | **`liste-portees-etat-vide.png`** |
| 2 | `wp mtb importer-portees-chiens --user=<admin>` | 27 portées, 17 chiens, **135 photos réelles** | **`coordonnees-legende-image.png`** · **`galerie-description-photo.png`** · **`galerie-fenetre-photos.png`** |
| 3 | Re-provisionnement **`MTB_FIXTURES=1`**, **sans `down -v`** (idempotent, ne supprime rien) | **31 portées / 22 chiens** + 136 images | rien — c'est l'état rendu au lot |

- **Le marqueur de fin de provisionnement est la ligne `[provision] terminé.`**, jamais le « healthy »
  du service `wordpress` : `docs/ETAT.md` a mesuré au lot 12 que `wpcli` passe *healthy* vers **~8 s**
  alors que `[provision] terminé.` n'apparaît qu'à **6 min 5 s**, et que `wordpress` passe
  *unhealthy* transitoirement **35 s** au plus fort du provisionnement avant de revenir seul. Une
  attente de 300 s **a déjà expiré avant le marqueur**.
- **La migration exige un administrateur** (`--user=`) : sans quoi `wp_kses()` échappe les chevrons des
  textes libres. **Ce n'est pas une session de capture** — les captures restent en `fabienne`, et
  l'interdit §8 de `issue-37.md` est intact.
- **Étape 1, la capture elle-même** : aucune page provisionnée ne porte de Liste de portées. Le
  composant s'**insère dans une page existante, on capture, et on quitte sans enregistrer** — c'est
  exactement le parcours que la fiche décrit, et il **n'écrit rien**. **Ne pas créer de page
  brouillon** : elle survivrait dans l'état rendu.
- **`down -v` est permis à cette chaîne, et à elle seule.** `issue-37.md` §8 l'interdisait **parce que**
  la base était partagée par trois chaînes ; l'interdit tombe avec sa raison, pas sans elle. Le lead a
  attribué le cycle de vie de la pile à cette chaîne pour ce lot, en contrepartie de la rendre
  provisionnée et peuplée.

**Coût assumé, déclaré et non caché** : à l'étape 3, réel et fictif cohabitent — exactement ce que la
garde de non-mélange (`portees-chiens/garde.php:17-30`) existe pour empêcher. Ce n'est **pas** une porte
à sens unique (`down -v` + `MTB_FIXTURES=0` reconstruit une base propre en ~10 min, tout étant
reproductible depuis le dépôt), mais **tant que ce volume reste ainsi,
`wp mtb importer-portees-chiens` y sera refusée**. À consigner dans `docs/ETAT.md` — **hors empreinte,
donc remonté au lead**, jamais écrit dans le guide.

## 6. Conditions de prise — héritées de `issue-37.md` §4, non renégociées

| Règle | Valeur gelée | Écart pour #46 |
|---|---|---|
| Session | **`fabienne` (rôle éditeur) exclusivement** | aucun — une capture en `admin` montrerait 4 entrées de menu que l'éleveuse n'a pas |
| Largeur | **1280 px** | aucun |
| Bandeau de mise à jour | **masqué à la prise** (`.update-nag`) | aucun |
| Retouche | **aucune** — recadrer et **mettre en évidence** est permis, ajouter/effacer/reformuler un libellé ne l'est jamais | **précision** : voir l'encadré ci-dessous |

### Précision sur « mettre en évidence » — soulevée par la passe de refacto

`coordonnees-legende-image.png` fait **deux** choses au champ **Légende** : un cadre rouge autour de la
zone de saisie, **et le libellé lui-même recoloré en rouge gras**. La passe de refacto a relevé que
« entourer » ne couvre pas littéralement « recolorer », et elle a raison de le relever.

**Décision : c'est permis, et la formulation du contrat est élargie plutôt que la capture reprise.**
L'interdit porte sur **le contenu** du libellé — ajouter, effacer, reformuler. Ici le mot « Légende »
est **intact, entier, lisible et à sa place** ; seule sa couleur change, exactement comme le cadre
rouge, et pour la même raison : dire à l'éleveuse **où regarder** sur une colonne qui porte cinq
champs. La mise en évidence est en outre **déclarée dans le texte de remplacement**, donc lisible par
qui ne voit pas l'image.

**Reste interdit, sans changement** : masquer, déplacer, remplacer ou réécrire un libellé, et toute
retouche qui ferait dire à l'écran autre chose que ce qu'il dit.
| Jeu de données | `issue-37.md` gelait **`MTB_FIXTURES=1`** | **écart déclaré** : la capture 1 se prend sous `MTB_FIXTURES=0` (c'est sa définition même), les trois autres sur base **migrée**. L'état **rendu** est bien `MTB_FIXTURES=1`. |

## 7. États spéciaux

| État | Rendu attendu | Traitement |
|---|---|---|
| `aucune_portee_publiee` | Dans l'éditeur : un cadre **beige au contour en tirets** à deux lignes, l'étiquette **LISTE DE PORTÉES** (rendue en capitales par la feuille de l'éditeur) puis « Ce bloc n'affiche rien tant qu'aucune portée n'est publiée. » Sur le site : **rien du tout** | C'est l'objet de `liste-portees-etat-vide.png`. Le distinguer de « aucune portée pour **cette année** », qui rend une phrase (`rendu.php:151-165`) et a **déjà** sa capture |
| `plan_inexistant` | Le composant se termine sur le courriel, aucun trou | **Inchangé.** La nouvelle capture ne le contredit pas et n'affirme aucun plan |
| `photo_principale_absente` | Cadre vide côté chien, **rien** côté portée | Capturé tel quel, comme au lot 10 |
| `capture_impossible` | — | **Déclarée nommément dans la fiche, en français, adressée à l'éleveuse.** Jamais un trou silencieux |

### Correction — ce contrat écrivait « un cadre gris », et c'était faux

La première version de §7 décrivait l'état vide comme « un cadre **gris** à deux lignes ». **Mesuré à
l'image livrée puis confirmé dans le code** (`themes/mtb/assets/css/editor.css:130-132` :
`border: 1px dashed var(--laiton)` sur `background-color: var(--calcaire-creux)`), le cadre est
**beige, à contour en tirets**. J'avais recopié le mot du paragraphe du guide que cette même issue
existe pour retirer — **la fausseté que je m'apprêtais à corriger, reproduite dans le document qui la
corrige**. Écrit ici plutôt que remplacé en silence.

Le mot « gris » est faux **partout** où il décrit ce cadre. Quatre occurrences subsistent hors de
l'empreinte de #46 et sont **signalées non corrigées** (§11).

## 7 bis. Découverte de la chaîne — la sélection multiple ne s'obtient pas au clic

**Ce n'est pas une capture, c'est un défaut d'usage trouvé en prenant une capture**, et il change ce que
le guide doit dire. Mesuré **dans le code du cœur**, `wp-includes/js/media-views.js` de WordPress 6.9,
et non déduit d'un comportement observé :

`Attachment.toggleSelection()` calcule `method = _.isUndefined( method ) ? selection.multiple : method;`
Pour un clic **sans modificateur**, hors mode grille, `method` vaut donc `selection.multiple`. Dans une
fenêtre ouverte en `multiple: true`, cette valeur est `true` — qui n'est ni `'between'`, ni `'toggle'`,
ni `'add'`. La suite tombe alors sur `if ( method !== 'add' ) method = 'reset';` : **la sélection est
remise à zéro et remplacée par la seule photo cliquée.**

**Et nos trois écrans de choix de photos ne sont pas réglés pareil :**

| Écran | Réglage | Effet d'un clic simple sur une vignette |
|---|---|---|
| Composant **Galerie photos** | `blocks/galerie-photos/editeur.js:190` → `multiple: true` | **remplace** la sélection |
| Encadré **Photos et pedigree** d'un chien | `fields/chien/galerie.js:167` → `multiple: 'add'` | **ajoute** |
| Encadré **Galerie photos** d'une portée | `fields/portee/ecran.js:264` → `multiple: 'add'` | **ajoute** |

**Conséquence pour l'éleveuse** : l'étape 5 de `composant-galerie-photos.md` disait « cochez **celles**
que vous voulez ». Le pluriel était juste et **le geste manquait** — qui clique trois photos l'une après
l'autre n'en obtient **qu'une**. Les gestes qui ajoutent réellement, tous lus dans le même code :
la **pastille à cocher** du coin de la vignette (`checkClickHandler`, ligne 4225 : ajoute si non
sélectionnée, retire sinon), **Ctrl/Cmd + clic** (`method = 'toggle'`), **Maj + clic**
(`method = 'between'`, toute une série).

**Décision : la fiche est réparée dans ce lot** — elle est dans l'empreinte, et une fiche qui prescrit un
geste qui ne marche pas est pire qu'une fiche sans image. **Le code ne l'est pas** :
`blocks/galerie-photos/editeur.js` n'appartient pas à #46, et une chaîne sœur travaille dans le même
arbre. **L'incohérence des trois écrans est versée au rapport comme dette**, non corrigée ici.

## 8. Interdits

- On ne renseigne **aucune** donnée de contenu, on ne téléverse **aucune** image, pour embellir une
  capture ou pour faire nombre.
- On ne prend **aucune** capture en session administrateur.
- On ne **fabrique** ni ne retouche un écran. Aucun plan d'accès n'est produit, ni plausible ni autre.
- **Aucun encart « Capture à prendre » ne reparaît** : les 84 retirés au lot 10 étaient des consignes de
  développeur dans le manuel de l'éleveuse.
- On n'écrit **rien** hors de l'empreinte : `docs/guide/captures/**`,
  `docs/guide/composant-liste-portees.md`, `docs/guide/composant-coordonnees-plan.md`,
  `docs/guide/composant-galerie-photos.md`, et ce contrat.
  **`design-system/MASTER.md` est formellement interdit** — empreinte exclusive de la chaîne #44, en
  cours dans le même arbre de travail.
- **Chaque PNG livré est rouvert et regardé** avant d'être déclaré juste. C'est la méthode qui a
  rattrapé `galerie-fenetre-photos.png` à #35, et la seule qui aurait attrapé « une photo contre trois ».

## 9. Observation due au lead — T65

Le libellé figé par `MASTER.md` §10.2 fait **52 caractères** et n'a **jamais été rendu à aucune
largeur**. `media-views.css:409-411` borne la colonne gauche du volet « Détails du fichier joint » à
`max-width: 80px` / `min-width: 30%` / `font-size: 12px` / `text-align: right` / `word-wrap: break-word`.
« Texte alternatif » (16 caractères) y tenait sur une ligne.

**Trois des quatre captures de cette issue photographient exactement cet écran.** Cette chaîne est donc
la première à en avoir la preuve visuelle. Consigne gelée : **mesurer et décrire ce que l'image montre
réellement**, dans un sens ou dans l'autre. **Ne pas prendre une capture flatteuse** : si le libellé
casse sur cinq lignes, c'est cette image-là qui entre dans le guide, parce que c'est ce que l'éleveuse
verra. **Ne pas corriger `MASTER.md`** — il appartient à #44.

### Résultat mesuré — la prédiction est fausse sur ses deux moitiés

Relevé sur `coordonnees-legende-image.png` et `galerie-description-photo.png`, images livrées et
rouvertes une par une :

| Prédiction de T65 | Mesure |
|---|---|
| 52 caractères | **53** (la parenthèse fermante) |
| « environ **cinq** lignes » | **quatre** : `Description de` / `la photo (pour` / `les personnes` / `aveugles)` |
| « coupées **en travers des mots** » | **aucune coupure intra-mot.** Le mot le plus large, « Description », mesure 60 px pour une colonne de 80 px : `word-wrap: break-word` n'a jamais à se déclencher |

Les valeurs CSS de `media-views.css:409-411` sont confirmées à l'écran (`max-width: 80px`,
`min-width: 30%`, `font-size: 12px`, `line-height: 16px`, `text-align: right`, `overflow-wrap:
break-word`). **Le libellé est lisible, entier, non tronqué.**

**Mais un écart réel subsiste, et ce n'est pas celui qu'on attendait** : la boîte du libellé mesure
**80 × 72 px** face à une zone de saisie de **174 × 50 px**. Le libellé est donc **plus haut que le
champ qu'il nomme** et **dépasse de 21 px sous son bord inférieur** — la dernière ligne, « aveugles) »,
flotte seule sous la zone de texte. C'est un déséquilibre visuel, pas une casse.

**T65 est donc levée sur preuve dans sa formulation d'origine.** Ce qui la remplace est plus modeste et
se juge à l'image, qui est au dossier. **Aucune capture flatteuse n'a été cherchée** : ni largeur ni
image alternative n'ont été essayées pour obtenir un meilleur rendu.

## 10. Arbitrages

| Désaccord | Décision | Raison |
|---|---|---|
| `coordonnees-legende-image.png` : impossible (`issue-37.md`) ou prenable (#46) ? | **Prenable**, recadrée sur la colonne de droite | §3. La fiche enseigne un **rang de champ**, pas un plan. Troisième interdit trop large du même tableau. |
| « une photo cochée » ou « trois » ? | **Trois — l'image s'aligne sur le texte** | §4. Le pluriel est l'enseignement de l'étape 5. |
| Lancer la migration ? | **Oui** | §4. Sans elle, trois captures montreraient l'image barrée « PHOTO DE TEST », livrée dans le manuel. |
| Corriger l'alternative de `galerie-description-photo.png` ? | **Non — elle est déjà juste** | §2. `6af2add` l'a corrigée ; l'énoncé de l'issue est périmé. |
| Toucher `composant-galerie-photos.md:124` (« Texte alternatif ») ? | **Non** | §2. Exception vraie et délibérée des blocs du cœur, dette T-#35-a. |
| Viser 121 ou constater 119 ? | **119 aujourd'hui → 121 après ajout des deux références** | §1. Déposer les PNG sans les référencer fabriquerait deux orphelins. |
| Seconde pile Docker en parallèle ? | **Écartée** | Elle ne résout rien : la médiathèque riche exige la migration, qui exige une base sans démonstration — la pile principale doit de toute façon être reconstruite. |

## 11. Signalé, non corrigé — hors empreinte

Rien de ce qui suit n'a été touché : ces fichiers appartiennent à d'autres chaînes ou à des issues
futures. Nommé ici pour ne pas être reperdu.

| Constat | Où | Pourquoi non corrigé |
|---|---|---|
| **Le cadre d'état vide est décrit « gris » alors qu'il est beige à contour en tirets** — un seul CSS le produit (`themes/mtb/assets/css/editor.css:126-140`), partagé par les **onze** composants via `mtb-etat-vide`. **Ce contrat en annonçait quatre : il y en a dix-huit, dans six fiches, dont quatre dans des textes alternatifs** (voir le détail ci-dessous) | `composant-formulaire-contact.md:193, 233, 236, **242**, **244**, 246, 326` · `composant-bandeau-alerte.md:116, 141, 155, 157, 178` · `composant-tableau-resultats.md:161, **170**, 182` · `composant-fiche-information.md:245` · `coordonnees-modifier-les-coordonnees.md:137` · `composant-grille-chiens.md:**41**` | Ces six fiches ne sont pas dans l'empreinte de #46. **Le volume justifie une issue à part**, pas une reprise en passant |
| Même mot dans `composant-coordonnees-plan.md:129` (« aucun cadre gris, aucun emplacement vide, aucun trou ») | dans l'empreinte, **mais dans la section gelée** « Aujourd'hui, il n'y a pas de plan » | La phrase **nie** l'existence du cadre plus qu'elle n'en décrit la couleur : le sens tient. Laissée délibérément plutôt que d'ouvrir une section que ce contrat déclare intouchable |
| **Les trois écrans de choix de photos ne sont pas réglés pareil** (`multiple: true` contre `multiple: 'add'`) — voir §7 bis | `blocks/galerie-photos/editeur.js:190` contre `fields/chien/galerie.js:167` et `fields/portee/ecran.js:264` | **Code, hors empreinte.** La fiche est réparée, la cause ne l'est pas. Le remède serait d'aligner le composant sur `'add'`, mais il se mesure avant de s'appliquer |
| **Les résultats de travail de l'ancien site ne sont pas en base** : `wp mtb importer-portees-chiens` ne reprend que portées et chiens ; la reprise des résultats est une commande distincte (`wp mtb reprise-resultats-pages`), non lancée | pile Docker | Hors consigne de cette chaîne. **La pile rendue est malgré tout plus riche qu'avant le lot** (voir §12) |

## 12. État de la pile rendue au lot

| | Avant ce lot | Après |
|---|---|---|
| Portées | 4 (fixtures) | **31** (27 reprises + 4 fixtures), toutes publiées |
| Chiens | 5 (fixtures) | **22** (17 repris + 5 fixtures) |
| Résultats de travail | 5 (fixtures) | **5** (fixtures) — la reprise des 61 résultats reste à faire |
| Pièces jointes | 1 (photo de test) | **136** (135 photos reprises + la photo de test) |

Pile **démarrée, provisionnée et peuplée**, `.env` remis à `MTB_FIXTURES=1`, quatre services *Up
(healthy)*, front en HTTP 200. **`docs/ETAT.md` est hors empreinte** : le fait que ce volume porte
désormais réel **et** fictif — et refusera donc `wp mtb importer-portees-chiens` tant qu'il reste ainsi
— est **remonté au lead**, à lui de le consigner.

**Deux faits d'infrastructure mesurés, qui corrigent des consignes en circulation** :

1. **`docker compose restart wpcli` ne relit pas `.env`.** Un `restart` reprovisionne avec l'ancien
   environnement. Il faut `docker compose up -d wpcli` (qui **recrée** le conteneur) avant tout
   `restart` dès que `MTB_FIXTURES` a changé. Toute consigne qui dit « changer `.env` puis
   `make provision` » est **fausse pour cette variable**.
2. **Le « 6 min 5 s » de `docs/ETAT.md` n'est pas la durée de tout provisionnement.** Sans fixtures, le
   marqueur `[provision] terminé.` est arrivé en **~40 s**, et le reprovisionnement de l'étape 3 en
   **~30 s**. Le chiffre du lot 12 vaut pour un provisionnement complet à froid. **La méthode ne change
   pas** — on boucle sur le marqueur, jamais sur une durée.

**Précision sur le « cadre gris », mesurée par la passe de refacto et non par moi.** Les dix-huit
occurrences se distinguent des « gris » **vrais**, qui restent justes et ne doivent pas être
« corrigés » par une chaîne future : les entrées grisées de la liste du bouton **+**
(`bandeau-ouverture.md:131, 209` · `bandeau-alerte.md:98` · `encart-derniere-portee.md:135, 199`), les
invites de saisie grises (`bandeau-alerte.md:53, 159` · `fiche-information.md:34` ·
`page-composer-une-page-libre.md:37` · `portee-ajouter-une-portee.md:24`) et les boutons grisés
(`galerie-photos.md:92, 95, 219` · `encart-appel.md:166`) — **tous rendus par le cœur de WordPress, et
réellement gris**. `contenu-repris-de-l-ancien-site.md:173` (« rien de gris ») **nie** le cadre, comme
la phrase gelée de `coordonnees-plan.md:129` : le sens tient, la couleur n'est pas affirmée.

## 13. Trois écarts signalés et laissés — arbitrés, non oubliés

| Écart | Décision | Raison |
|---|---|---|
| **« les quatre champs » alors que l'écran en montre cinq** — sous *Description* vient **URL du fichier :**, avec sa zone de saisie et son bouton (`galerie-photos.md:125-133`, `coordonnees-plan.md:114-118`) | **Laissé** | Les quatre sont **nommés** juste après le décompte, et la désignation ordinale de #35 (« le dernier des quatre, celui qui s'appelle **Description** tout court ») est **levée par le nom**, pas par le rang seul. Corriger toucherait deux fiches, leurs textes alternatifs, et une formulation que #35 a délibérément choisie pour rester vraie si le renommage cessait de mordre |
| **`http://localhost:3005/…` visible** dans le champ *URL du fichier* des trois captures de médiathèque | **Laissé** | Inhérent à toute capture prise sur la pile Docker, donc **commun aux 121**. Ce n'est pas un défaut de ce lot, et le corriger supposerait une pile servie sous le nom de production — qui n'existe pas |
| **`galerie-photos.md:169-174` promet une visionneuse « pour plus tard »** | **Laissé, signalé** | Seule promesse au futur restant dans les trois fiches. Tenir ou retirer cet engagement est une **décision produit**, pas une question de formulation — hors du mandat de #46 |
