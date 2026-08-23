# Photographies de l'ancien site — archive de sauvegarde

## À lire avant tout téléversement — dette T12

> Ces fichiers ne se téléversent qu'**après** le module d'images de l'issue #8. WordPress ne
> découpe et ne convertit une image **qu'au téléversement** : une photo importée avant ce module
> n'aura **jamais** ses formats modernes ni ses sous-tailles, et tout le stock serait à
> régénérer. C'est un **prérequis dur** de #20-#21, pas une recommandation.

Ce dossier est une **archive de sauvegarde de l'original**, **pas la source de service**. Les
photos finiront servies depuis le serveur du nouveau site, téléversées dans la médiathèque
WordPress par #20-#21. Rien ici n'est destiné à être servi tel quel à un visiteur.

Il existe parce que **cette issue est le seul moment du projet où le site source existe
encore** : l'abonnement IONOS sera résilié précisément parce que le nouveau site existe. Le
texte se retape ; une photographie de 2011 non.

## 1. Ce qui a été fait, et ce qui ne l'a pas été

- **Recensement** : les 54 fichiers HTML de `../html/` ont été relus intégralement ; **toute**
  occurrence d'une URL contenant `cc_images` a été relevée, quel que soit l'attribut porteur
  (`src`, `href`, `srcset`, style en ligne, `data-*`). Une image liée mais non affichée est une
  image quand même : **379 URL distinctes** relevées pour **191 identifiants** `cc_images`, dont
  **188** apparaissent aussi en `href` (le lien d'agrandissement de la galerie), et **3** en
  `src` seulement. Aucune référence en `srcset`, en style en ligne ou en `data-*` sur ce site.
- **Déduplication** : `thumb_16791790.jpg`, `cache_16791790.jpg` et `teaserbox_16791790.jpg`
  sont trois rendus de la même photo. La clé de déduplication est l'**identifiant IONOS**
  (`16791790.jpg`), préfixe exclu.
- **Nommage** : `<identifiant IONOS>.<extension>`, **jamais** un nom de chien. Nommer un
  fichier `jango-2.jpg` supposerait de savoir qui est sur la photo : c'est une invention de fait
  d'élevage. L'extension est conservée **telle que servie, casse comprise** (`.JPG` et `.jpg`
  coexistent sur le site source ; ils ne sont pas harmonisés).
- **Octets tels que servis** : aucune retouche, aucun recadrage, aucune conversion, aucune
  compression, aucun renommage. Le condensé SHA-256 porte sur les octets déposés.
- **Aucun fait d'élevage n'est écrit ici** : aucun chien nommé, aucune photo datée, aucun
  contenu d'image décrit. Les seules colonnes descriptives sont ce qui est **écrit** dans le
  source : l'attribut `alt` (§6) et les pages qui citent l'image.

## 2. Comment la rendition a été choisie — par mesure, jamais par convention

**Le préfixe le plus gros n'est pas le même d'une image à l'autre.** Pour chaque identifiant,
les quatre formes ont été sondées — `cache_`, `teaserbox_`, `thumb_`, et l'identifiant nu — et
celle dont les **dimensions en pixels** sont les plus grandes est retenue (à dimensions égales,
la plus lourde). Les dimensions sont **mesurées sur les octets reçus**, pas déduites du nom.

Une sonde exploratoire a testé 31 préfixes candidats (`original_`, `big_`, `full_`, `large_`,
`preview_`, `master_`, `raw_`, `zoom_`, `lightbox_`…) sur quatre identifiants témoins :
**seuls `cache_`, `teaserbox_` et `thumb_` répondent 200**. L'identifiant nu
(`/s/cc_images/16791790.jpg`, sans préfixe) répond **404 sur les 191 identifiants `cc_images`** :
cette forme d'URL n'existe pas sur IONOS.

Résultat sur les 192 identifiants :

| Préfixe retenu | Identifiants |
|---|---|
| `cache_` | 132 |
| `teaserbox_` | 59 |
| `(nu)` | 1 |

Sur **59 identifiants, `cache_` n'est pas la plus grande rendition** — c'est `teaserbox_` qui
l'est. Une règle a priori aurait donc perdu de la définition sur près d'un tiers du stock.

## 3. Résolution disponible — constat à remonter

**IONOS ne sert aucun original.** Les dimensions retenues s'empilent sur des plafonds de
redimensionnement :

| Grand côté de la rendition retenue | Identifiants |
|---|---|
| 1024 px | 49 |
| 768 px | 39 |
| 274 px | 16 |
| 246 px | 16 |
| 800 px | 15 |
| 237 px | 12 |
| 900 px | 10 |
| 600 px | 7 |
| 960 px | 6 |
| 851 px | 3 |

Mesuré sur **l'ensemble des 383 renditions servies**, pas seulement sur les retenues : le grand
côté de `cache_` ne dépasse jamais **1527 px** (49 renditions exactement à 1024 px), celui de
`teaserbox_` **1080 px**, celui de `thumb_` **150 px**. **4 renditions seulement**, toutes
hauteurs confondues, dépassent 1024 px de grand côté :

| Identifiant | Préfixe | Dimensions |
|---|---|---|
| `17365005.JPG` | `teaserbox_` | 720×1080 |
| `6477274.JPG` | `teaserbox_` | 759×1080 |
| `7437995.jpg` | `cache_` | 719×1080 |
| `7437996.jpg` | `cache_` | 1527×1080 |

Le tassement de 49 identifiants sur exactement 1024 px, et de dizaines d'autres sur 768 ou 800,
est la signature d'un **redimensionnement au téléversement**, pas de photographies natives.

> **Question bloquante pour D4.** Si les originaux pleine définition existent, ils sont dans le
> gestionnaire de médias IONOS de l'éleveuse, **hors de portée d'un téléchargement public**.
> Ce que cette archive contient est donc **la plus grande rendition publiquement servie**, pas
> l'original. Récupérer les originaux suppose un export depuis le compte IONOS, **avant la
> résiliation de l'abonnement**.

## 4. Plafond de garde

Le contrat (§4) impose de **mesurer avant de télécharger** et d'arrêter au-delà de 150 Mo. Le
passage de mesure seule (requêtes avec plage d'octets, une par rendition, séquentielles) a
donné **33 694 075 octets** (32.13 Mo) pour les 192 renditions retenues — **sous le plafond**, le
téléchargement a donc eu lieu. Le poids réellement déposé est identique au poids mesuré, ligne
à ligne (§7).

## 5. Les 192 photographies archivées

Une ligne par identifiant. « Pages citantes » : d'abord les fichiers `.html` de `../html/`,
puis les réductions `.md` de `../portees/`, `../chiens/`, `../pages/` et de la racine, **telles
qu'elles existaient le 2026-08-23**. Colonne « préfixe retenu » : la rendition choisie et ses
dimensions, puis les renditions écartées avec les leurs — c'est ce qui rend le choix
contestable.

| Identifiant | Fichier déposé | URL d'origine retenue | Préfixe retenu — dimensions comparées | HTTP | Octets | Dimensions | SHA-256 | Pages citantes |
|---|---|---|---|---|---|---|---|---|
| `emotionheader.jpeg` | `emotionheader.jpeg` | https://www.mtbrabant.com/s/img/emotionheader.jpeg | `(nu)` — 920×313 ; seule rendition servie | 200 | 77536 | 920×313 | `5389a5451b50128071bfc58045a9070e4f52e126849ea7d57af87a2817cdd6b3` | 53 fichiers `.html` (voir §5)<br>45 fichiers `.md` (voir §5) |
| `13346836.jpg` | `13346836.jpg` | https://www.mtbrabant.com/s/cc_images/teaserbox_13346836.jpg | `teaserbox_` — 184×274 ; `cache_` 67×99 · `(nu)` 404 · `thumb_` 404 | 200 | 7758 | 184×274 | `f920725d8563a666e3d3b8f0c6845dae05730855e35c808c799027208bc53ddd` | `chien-youry.html`<br>`chiens/chien-youry.md` |
| `13346839.png` | `13346839.png` | https://www.mtbrabant.com/s/cc_images/teaserbox_13346839.png | `teaserbox_` — 246×205 ; `cache_` 121×100 · `(nu)` 404 · `thumb_` 404 | 200 | 7674 | 246×205 | `ce63e3c1c67d0fb958436f51cc0573cb9461f3ba6186c9fae19ad8ed5ba77708` | `chien-youry.html`<br>`chiens/chien-youry.md` |
| `13346842.jpg` | `13346842.jpg` | https://www.mtbrabant.com/s/cc_images/teaserbox_13346842.jpg | `teaserbox_` — 237×213 ; `cache_` 103×92 · `(nu)` 404 · `thumb_` 404 | 200 | 15208 | 237×213 | `68914df7a4107796b5621f0779e87dce2bdf324ecbbbd35eeabeb12e0c774811` | `chien-youry.html`<br>`chiens/chien-youry.md` |
| `13346847.jpg` | `13346847.jpg` | https://www.mtbrabant.com/s/cc_images/teaserbox_13346847.jpg | `teaserbox_` — 184×274 ; `cache_` 67×99 · `(nu)` 404 · `thumb_` 404 | 200 | 7758 | 184×274 | `f920725d8563a666e3d3b8f0c6845dae05730855e35c808c799027208bc53ddd` | `chien-grocky.html`<br>`chiens/chien-grocky.md` |
| `13346851.png` | `13346851.png` | https://www.mtbrabant.com/s/cc_images/teaserbox_13346851.png | `teaserbox_` — 246×205 ; `cache_` 121×100 · `(nu)` 404 · `thumb_` 404 | 200 | 7674 | 246×205 | `ce63e3c1c67d0fb958436f51cc0573cb9461f3ba6186c9fae19ad8ed5ba77708` | `chien-grocky.html`<br>`chiens/chien-grocky.md` |
| `13346853.jpg` | `13346853.jpg` | https://www.mtbrabant.com/s/cc_images/teaserbox_13346853.jpg | `teaserbox_` — 237×213 ; `cache_` 103×92 · `(nu)` 404 · `thumb_` 404 | 200 | 15208 | 237×213 | `68914df7a4107796b5621f0779e87dce2bdf324ecbbbd35eeabeb12e0c774811` | `chien-grocky.html`<br>`chiens/chien-grocky.md` |
| `13553829.jpg` | `13553829.jpg` | https://www.mtbrabant.com/s/cc_images/teaserbox_13553829.jpg | `teaserbox_` — 184×274 ; `cache_` 67×99 · `(nu)` 404 · `thumb_` 404 | 200 | 7758 | 184×274 | `f920725d8563a666e3d3b8f0c6845dae05730855e35c808c799027208bc53ddd` | `chien-happy.html`<br>`chiens/chien-happy.md` |
| `13553855.png` | `13553855.png` | https://www.mtbrabant.com/s/cc_images/teaserbox_13553855.png | `teaserbox_` — 246×205 ; `cache_` 121×100 · `(nu)` 404 · `thumb_` 404 | 200 | 7674 | 246×205 | `ce63e3c1c67d0fb958436f51cc0573cb9461f3ba6186c9fae19ad8ed5ba77708` | `chien-happy.html`<br>`chiens/chien-happy.md` |
| `14079082.jpg` | `14079082.jpg` | https://www.mtbrabant.com/s/cc_images/cache_14079082.jpg | `cache_` — 960×640 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 175541 | 960×640 | `92414e31395c29ded1f98527889bf3013bc41913da4780f4eca68f90044caee1` | `portee-n-2017.html`<br>`portees/portee-n-2017.md` |
| `14079083.jpg` | `14079083.jpg` | https://www.mtbrabant.com/s/cc_images/cache_14079083.jpg | `cache_` — 400×600 ; `thumb_` 16×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 33971 | 400×600 | `8a993d861428bce5559f1c26378369f5707a38d457ee9b2f881cb90148d6e151` | `portee-n-2017.html`<br>`portees/portee-n-2017.md` |
| `14079084.jpg` | `14079084.jpg` | https://www.mtbrabant.com/s/cc_images/cache_14079084.jpg | `cache_` — 800×533 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 79400 | 800×533 | `1ec974617c50a2693ec277232dccf4229ec7b190eccc80eb1b672a3f28b8a6ad` | `portee-n-2017.html`<br>`portees/portee-n-2017.md` |
| `14079085.jpg` | `14079085.jpg` | https://www.mtbrabant.com/s/cc_images/cache_14079085.jpg | `cache_` — 800×533 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 63768 | 800×533 | `ee9c3b362cf58e6e4cf1498b7099269f6d4c27ac7dbc88016e930515ab96cfd3` | `portee-n-2017.html`<br>`portees/portee-n-2017.md` |
| `14079086.JPG` | `14079086.JPG` | https://www.mtbrabant.com/s/cc_images/cache_14079086.JPG | `cache_` — 1024×682 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 484621 | 1024×682 | `68abb17ea8305a14df542ec186d90bd4aad76980636699db422c5bf45ac1416d` | `portee-n-2017.html`<br>`portees/portee-n-2017.md` |
| `14079087.jpg` | `14079087.jpg` | https://www.mtbrabant.com/s/cc_images/cache_14079087.jpg | `cache_` — 512×768 ; `thumb_` 16×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 42569 | 512×768 | `f267470aae07ca3d8c64dd21f7df42c52bc7ca7d77e4f1cfb2b08a6cc2445059` | `portee-n-2017.html`<br>`portees/portee-n-2017.md` |
| `14079088.JPG` | `14079088.JPG` | https://www.mtbrabant.com/s/cc_images/cache_14079088.JPG | `cache_` — 1024×682 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 294354 | 1024×682 | `490c79245a788bf3ba6c5d8e6abc95dde1f684524ea69768b5385773189fd23b` | `portee-n-2017.html`<br>`portees/portee-n-2017.md` |
| `14079089.JPG` | `14079089.JPG` | https://www.mtbrabant.com/s/cc_images/cache_14079089.JPG | `cache_` — 1024×682 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 285245 | 1024×682 | `85c6f18fd6f22b60ae50db657c92bfd8f94fb905b897da2f72dbd5208d052463` | `portee-n-2017.html`<br>`portees/portee-n-2017.md` |
| `14079090.JPG` | `14079090.JPG` | https://www.mtbrabant.com/s/cc_images/cache_14079090.JPG | `cache_` — 1024×682 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 332397 | 1024×682 | `aad5db1e3704df971916b837079de1518b643eeb85de7b67406d4ada3273cb2e` | `portee-n-2017.html`<br>`portees/portee-n-2017.md` |
| `14079091.JPG` | `14079091.JPG` | https://www.mtbrabant.com/s/cc_images/cache_14079091.JPG | `cache_` — 1024×682 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 294552 | 1024×682 | `8fa92d1206c6fcf1028eed2a1f09d8722f15f25838e8202041dc66cf775fe6ef` | `portee-n-2017.html`<br>`portees/portee-n-2017.md` |
| `14079092.JPG` | `14079092.JPG` | https://www.mtbrabant.com/s/cc_images/cache_14079092.JPG | `cache_` — 512×768 ; `thumb_` 16×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 115910 | 512×768 | `3a2e24825387fd90742dbf94dc806ee0f54676f7c69b99b0fa73f6e9fde2a779` | `portee-n-2017.html`<br>`portees/portee-n-2017.md` |
| `14079093.JPG` | `14079093.JPG` | https://www.mtbrabant.com/s/cc_images/cache_14079093.JPG | `cache_` — 512×768 ; `thumb_` 16×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 81802 | 512×768 | `472f06ce3cf365c2351ac58fe8ddb589ceb65483ce06d2f53b71ad0d584ce966` | `portee-n-2017.html`<br>`portees/portee-n-2017.md` |
| `14079094.JPG` | `14079094.JPG` | https://www.mtbrabant.com/s/cc_images/cache_14079094.JPG | `cache_` — 512×768 ; `thumb_` 16×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 72869 | 512×768 | `8e484cb36b40f2a425711ab436557e1ffe1cd2076e5d56d4b477e64b12fb9e3d` | `portee-n-2017.html`<br>`portees/portee-n-2017.md` |
| `14079095.JPG` | `14079095.JPG` | https://www.mtbrabant.com/s/cc_images/cache_14079095.JPG | `cache_` — 1024×682 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 339659 | 1024×682 | `0c82d2f985e560c33492f5ab3e0ae4c0eb5dd225e8e0f2bf4510e44d04d0b671` | `portee-n-2017.html`<br>`portees/portee-n-2017.md` |
| `14079096.JPG` | `14079096.JPG` | https://www.mtbrabant.com/s/cc_images/cache_14079096.JPG | `cache_` — 512×768 ; `thumb_` 16×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 104471 | 512×768 | `944364018738dee9d6d4262db452dc8dacbbab68fd7fc8346487b516b46b9451` | `portee-n-2017.html`<br>`portees/portee-n-2017.md` |
| `14079097.jpg` | `14079097.jpg` | https://www.mtbrabant.com/s/cc_images/cache_14079097.jpg | `cache_` — 960×640 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 63705 | 960×640 | `8075642dd1e9d754c447b07b3d0b6c87ed12dc71524b9caa452d4e738d1eca8f` | `portee-n-2017.html`<br>`portees/portee-n-2017.md` |
| `14079098.jpg` | `14079098.jpg` | https://www.mtbrabant.com/s/cc_images/cache_14079098.jpg | `cache_` — 1024×682 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 193269 | 1024×682 | `d8c7ec5d3f9b915f3c0926d57b955a815c00ff4a183cc5462e0a9f4176c9ac3a` | `portee-n-2017.html`<br>`portees/portee-n-2017.md` |
| `14079099.jpg` | `14079099.jpg` | https://www.mtbrabant.com/s/cc_images/cache_14079099.jpg | `cache_` — 576×768 ; `thumb_` 18×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 43681 | 576×768 | `5f266e0e58cf27afbe49927e50ab61f64f1773c4d65acc3ce6b1c64d10e06867` | `portee-n-2017.html`<br>`portees/portee-n-2017.md` |
| `14335936.jpg` | `14335936.jpg` | https://www.mtbrabant.com/s/cc_images/cache_14335936.jpg | `cache_` — 800×553 ; `thumb_` 150×103 · `(nu)` 404 · `teaserbox_` 404 | 200 | 72082 | 800×553 | `8e0a37c4b55f0a8cb315cf64730cf7b4a1eb0606d6e7581e1bf6b9b0f3590637` | `chien-gribouille.html`<br>`chiens/chien-gribouille.md` |
| `14359713.jpg` | `14359713.jpg` | https://www.mtbrabant.com/s/cc_images/cache_14359713.jpg | `cache_` — 399×600 ; `thumb_` 16×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 102340 | 399×600 | `1f19ad4c796ee28a412d963f6ad08948b542ce7a4b8563c3af1b2bdacdd925d9` | `portee-f-2010.html`<br>`portees/portee-f-2010.md` |
| `14801494.jpg` | `14801494.jpg` | https://www.mtbrabant.com/s/cc_images/teaserbox_14801494.jpg | `teaserbox_` — 184×274 ; `cache_` 67×99 · `(nu)` 404 · `thumb_` 404 | 200 | 7758 | 184×274 | `f920725d8563a666e3d3b8f0c6845dae05730855e35c808c799027208bc53ddd` | `chien-rolex.html`<br>`chiens/chien-rolex.md` |
| `14801503.png` | `14801503.png` | https://www.mtbrabant.com/s/cc_images/teaserbox_14801503.png | `teaserbox_` — 246×205 ; `cache_` 121×100 · `(nu)` 404 · `thumb_` 404 | 200 | 7674 | 246×205 | `ce63e3c1c67d0fb958436f51cc0573cb9461f3ba6186c9fae19ad8ed5ba77708` | `chien-rolex.html`<br>`chiens/chien-rolex.md` |
| `14801505.jpg` | `14801505.jpg` | https://www.mtbrabant.com/s/cc_images/teaserbox_14801505.jpg | `teaserbox_` — 237×213 ; `cache_` 112×100 · `(nu)` 404 · `thumb_` 404 | 200 | 15208 | 237×213 | `68914df7a4107796b5621f0779e87dce2bdf324ecbbbd35eeabeb12e0c774811` | `chien-rolex.html`<br>`chiens/chien-rolex.md` |
| `14834170.jpg` | `14834170.jpg` | https://www.mtbrabant.com/s/cc_images/cache_14834170.jpg | `cache_` — 812×544 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 138788 | 812×544 | `32d2424b7a87a838848fa0413906b6df2a3128e5bd305e31370b0f880cf3f201` | `chien-grocky.html`<br>`chiens/chien-grocky.md` |
| `14834171.JPG` | `14834171.JPG` | https://www.mtbrabant.com/s/cc_images/cache_14834171.JPG | `cache_` — 1024×682 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 449396 | 1024×682 | `7c9b549d97151b4d562db4768b4739201cd098efcc7163dd05bb325079dee808` | `chien-grocky.html`<br>`chiens/chien-grocky.md` |
| `14834172.png` | `14834172.png` | https://www.mtbrabant.com/s/cc_images/cache_14834172.png | `cache_` — 1024×621 ; `thumb_` 40×24 · `(nu)` 404 · `teaserbox_` 404 | 200 | 1095104 | 1024×621 | `05dc5ac1d58ad73905f7ad44cea4c66366c1671a9f6a95de3dab95d8488ace37` | `chien-grocky.html`<br>`chiens/chien-grocky.md` |
| `14834173.JPG` | `14834173.JPG` | https://www.mtbrabant.com/s/cc_images/cache_14834173.JPG | `cache_` — 512×768 ; `thumb_` 16×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 82501 | 512×768 | `ff6c3fea5c9a5a2a4ccf0ade9cf8d8a89292f59ae07da71f80b30f9f7c5e67c6` | `chien-grocky.html`<br>`chiens/chien-grocky.md` |
| `14893746.jpg` | `14893746.jpg` | https://www.mtbrabant.com/s/cc_images/teaserbox_14893746.jpg | `teaserbox_` — 184×274 ; `cache_` 67×99 · `(nu)` 404 · `thumb_` 404 | 200 | 7758 | 184×274 | `f920725d8563a666e3d3b8f0c6845dae05730855e35c808c799027208bc53ddd` | `chien-ray-ban.html`<br>`chiens/chien-ray-ban.md` |
| `14893748.png` | `14893748.png` | https://www.mtbrabant.com/s/cc_images/teaserbox_14893748.png | `teaserbox_` — 246×205 ; `cache_` 120×100 · `(nu)` 404 · `thumb_` 404 | 200 | 7674 | 246×205 | `ce63e3c1c67d0fb958436f51cc0573cb9461f3ba6186c9fae19ad8ed5ba77708` | `chien-ray-ban.html`<br>`chiens/chien-ray-ban.md` |
| `14894211.jpg` | `14894211.jpg` | https://www.mtbrabant.com/s/cc_images/teaserbox_14894211.jpg | `teaserbox_` — 237×213 ; `cache_` 103×92 · `(nu)` 404 · `thumb_` 404 | 200 | 15208 | 237×213 | `68914df7a4107796b5621f0779e87dce2bdf324ecbbbd35eeabeb12e0c774811` | `chien-ray-ban.html`<br>`chiens/chien-ray-ban.md` |
| `14894226.jpg` | `14894226.jpg` | https://www.mtbrabant.com/s/cc_images/cache_14894226.jpg | `cache_` — 873×768 ; `thumb_` 28×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 155528 | 873×768 | `9edc1df83e8c6b8d198b012e062c802249cffded6bf1630a02d83b7da86983ff` | `chien-rolex.html`<br>`chiens/chien-rolex.md` |
| `15015993.jpg` | `15015993.jpg` | https://www.mtbrabant.com/s/cc_images/teaserbox_15015993.jpg | `teaserbox_` — 184×274 ; `cache_` 67×99 · `(nu)` 404 · `thumb_` 404 | 200 | 7758 | 184×274 | `f920725d8563a666e3d3b8f0c6845dae05730855e35c808c799027208bc53ddd` | `chien-pegaz.html`<br>`chiens/chien-pegaz.md` |
| `15016002.png` | `15016002.png` | https://www.mtbrabant.com/s/cc_images/teaserbox_15016002.png | `teaserbox_` — 246×205 ; `cache_` 120×100 · `(nu)` 404 · `thumb_` 404 | 200 | 7674 | 246×205 | `ce63e3c1c67d0fb958436f51cc0573cb9461f3ba6186c9fae19ad8ed5ba77708` | `chien-pegaz.html`<br>`chiens/chien-pegaz.md` |
| `15016007.jpg` | `15016007.jpg` | https://www.mtbrabant.com/s/cc_images/teaserbox_15016007.jpg | `teaserbox_` — 237×213 ; `cache_` 103×92 · `(nu)` 404 · `thumb_` 404 | 200 | 15208 | 237×213 | `68914df7a4107796b5621f0779e87dce2bdf324ecbbbd35eeabeb12e0c774811` | `chien-pegaz.html`<br>`chiens/chien-pegaz.md` |
| `15016098.jpg` | `15016098.jpg` | https://www.mtbrabant.com/s/cc_images/cache_15016098.jpg | `cache_` — 1024×682 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 98146 | 1024×682 | `ea4ad7718540882befc48c336b1246936329141e3381f44144fddb9d155d40dd` | `portee-m-2016.html`<br>`portees/portee-m-2016.md` |
| `15016099.jpg` | `15016099.jpg` | https://www.mtbrabant.com/s/cc_images/cache_15016099.jpg | `cache_` — 512×768 ; `thumb_` 16×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 40331 | 512×768 | `ffdcb9f6bd3d63987acfa7ac169556a856e27aa19509d54233d9a85992271a90` | `portee-m-2016.html`<br>`portees/portee-m-2016.md` |
| `15016100.jpg` | `15016100.jpg` | https://www.mtbrabant.com/s/cc_images/cache_15016100.jpg | `cache_` — 512×768 ; `thumb_` 16×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 96585 | 512×768 | `eab5eb50d9c672d5280548e7ae0ff81b78aa0c9b74f13e9a36e88800834ab7e7` | `portee-m-2016.html`<br>`portees/portee-m-2016.md` |
| `15221411.jpg` | `15221411.jpg` | https://www.mtbrabant.com/s/cc_images/cache_15221411.jpg | `cache_` — 851×506 ; `thumb_` 40×23 · `(nu)` 404 · `teaserbox_` 404 | 200 | 396606 | 851×506 | `87a97dceddd188045896fd10b9c7b251949c75e1732ee0154237af95d6de5d30` | `portee-p-2019.html`<br>`portees/portee-p-2019.md` |
| `15221412.JPG` | `15221412.JPG` | https://www.mtbrabant.com/s/cc_images/cache_15221412.JPG | `cache_` — 849×570 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 47869 | 849×570 | `962ce8c80f3e9ed9ce3179e7361556e8f0a61de00325c2fe8210d2e4c357d1ca` | `portee-p-2019.html`<br>`portees/portee-p-2019.md` |
| `15221413.jpg` | `15221413.jpg` | https://www.mtbrabant.com/s/cc_images/cache_15221413.jpg | `cache_` — 948×637 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 210132 | 948×637 | `e8784fef1ff48e69f47f1682c6687df38276cb23aa14472c3d9af10161e24806` | `portee-p-2019.html`<br>`portees/portee-p-2019.md` |
| `15221414.jpg` | `15221414.jpg` | https://www.mtbrabant.com/s/cc_images/cache_15221414.jpg | `cache_` — 1024×727 ; `thumb_` 35×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 142669 | 1024×727 | `e01eb5d90ea88841ff4233fdd26b7de671c8f7ff813ce421f0e7d3b1b469041f` | `portee-p-2019.html`<br>`portees/portee-p-2019.md` |
| `15221415.jpg` | `15221415.jpg` | https://www.mtbrabant.com/s/cc_images/cache_15221415.jpg | `cache_` — 233×396 ; `thumb_` 14×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 18838 | 233×396 | `e95d17988e795796573fe56134f7d19b2645508a3d54a292fd9cdf6970c55a41` | `portee-p-2019.html`<br>`portees/portee-p-2019.md` |
| `15456230.jpg` | `15456230.jpg` | https://www.mtbrabant.com/s/cc_images/cache_15456230.jpg | `cache_` — 1024×682 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 127575 | 1024×682 | `46397a4dbcc8200bb37d422171b71523aefdaf289f2ca3135c912f99b75eaa1f` | `chien-opium.html`<br>`chiens/chien-opium.md` |
| `15456231.jpg` | `15456231.jpg` | https://www.mtbrabant.com/s/cc_images/cache_15456231.jpg | `cache_` — 512×768 ; `thumb_` 16×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 53669 | 512×768 | `87d915216885efda32dd1fb7974e51a55be807d20e71926b903dccc20b599edb` | `chien-opium.html`<br>`chiens/chien-opium.md` |
| `15456232.JPG` | `15456232.JPG` | https://www.mtbrabant.com/s/cc_images/cache_15456232.JPG | `cache_` — 512×768 ; `thumb_` 16×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 269889 | 512×768 | `62c1de0117b11c3fa0981e021968e17c485b5b0bc618bd34607aaaf7f0bf5fa8` | `chien-opium.html`<br>`chiens/chien-opium.md` |
| `15456233.jpg` | `15456233.jpg` | https://www.mtbrabant.com/s/cc_images/cache_15456233.jpg | `cache_` — 1024×748 ; `thumb_` 34×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 76320 | 1024×748 | `22b1d33d018d5943176ea6d77e441aab09fd9af5442f2be0803c8aadb8a00ce7` | `chien-opium.html`<br>`chiens/chien-opium.md` |
| `15465856.jpg` | `15465856.jpg` | https://www.mtbrabant.com/s/cc_images/cache_15465856.jpg | `cache_` — 1024×724 ; `thumb_` 35×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 138628 | 1024×724 | `38fd1431ff751bf2544fbba33cc3f399475beb1a88a187d4e854cf2b7bcaf3ca` | `portee-s1-2021.html`<br>`portees/portee-s1-2021.md` |
| `15465857.png` | `15465857.png` | https://www.mtbrabant.com/s/cc_images/cache_15465857.png | `cache_` — 1024×679 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 1477946 | 1024×679 | `504d3cecb728fc1042b2fac2d600422700da55a508dd0a443b479fb7be0fe3eb` | `portee-s1-2021.html`<br>`portees/portee-s1-2021.md` |
| `15465858.png` | `15465858.png` | https://www.mtbrabant.com/s/cc_images/cache_15465858.png | `cache_` — 1024×609 ; `thumb_` 40×23 · `(nu)` 404 · `teaserbox_` 404 | 200 | 1217002 | 1024×609 | `e063c55e67673d73753d667167f503889fc8249e95c7d9ea626d844d632478bc` | `portee-s1-2021.html`<br>`portees/portee-s1-2021.md` |
| `15465859.png` | `15465859.png` | https://www.mtbrabant.com/s/cc_images/cache_15465859.png | `cache_` — 1024×666 ; `thumb_` 38×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 1242972 | 1024×666 | `11eae0b6442016cc14ce4b0d29c63491c69328af14f508051042bcd53c7d0056` | `portee-s1-2021.html`<br>`portees/portee-s1-2021.md` |
| `15501544.JPG` | `15501544.JPG` | https://www.mtbrabant.com/s/cc_images/teaserbox_15501544.JPG | `teaserbox_` — 580×822 ; `cache_` 475×673 · `(nu)` 404 · `thumb_` 404 | 200 | 117315 | 580×822 | `155428cc05479210fd3bf65bf17c276151e6c5d4551b97e69764dfa6b44b3a07` | `portee-s2-2021.html`<br>`portees/portee-s2-2021.md` |
| `16372509.jpg` | `16372509.jpg` | https://www.mtbrabant.com/s/cc_images/cache_16372509.jpg | `cache_` — 851×506 ; `thumb_` 40×23 · `(nu)` 404 · `teaserbox_` 404 | 200 | 385209 | 851×506 | `2e83688945feb8c19aee131db43c72c0dc2e6732c452a4027fc2ea5555bc4a86` | `portee-r-2020.html`<br>`portees/portee-r-2020.md` |
| `16372510.JPG` | `16372510.JPG` | https://www.mtbrabant.com/s/cc_images/cache_16372510.JPG | `cache_` — 800×533 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 283752 | 800×533 | `17835eb1da7e77995576dfce04834cc38f8304faa132485d7dcf374889efdf50` | `portee-r-2020.html`<br>`portees/portee-r-2020.md` |
| `16372511.JPG` | `16372511.JPG` | https://www.mtbrabant.com/s/cc_images/cache_16372511.JPG | `cache_` — 400×600 ; `thumb_` 16×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 56697 | 400×600 | `c19d0da66d032e6f40d25919c975099ac63f06f42f7d7eb7d06af7047205aa2f` | `portee-r-2020.html`<br>`portees/portee-r-2020.md` |
| `16372512.png` | `16372512.png` | https://www.mtbrabant.com/s/cc_images/cache_16372512.png | `cache_` — 1024×621 ; `thumb_` 40×24 · `(nu)` 404 · `teaserbox_` 404 | 200 | 1095104 | 1024×621 | `05dc5ac1d58ad73905f7ad44cea4c66366c1671a9f6a95de3dab95d8488ace37` | `portee-r-2020.html`<br>`portees/portee-r-2020.md` |
| `16372513.JPG` | `16372513.JPG` | https://www.mtbrabant.com/s/cc_images/cache_16372513.JPG | `cache_` — 529×768 ; `thumb_` 17×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 63722 | 529×768 | `d21612c8c8bcb957ca62983e417f83906734b5c2bcde28b880b9ce2957e42aa6` | `portee-r-2020.html`<br>`portees/portee-r-2020.md` |
| `16372514.png` | `16372514.png` | https://www.mtbrabant.com/s/cc_images/cache_16372514.png | `cache_` — 480×640 ; `thumb_` 18×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 484087 | 480×640 | `3bf03c17a7045ec684afe40afae29c4ec17cea6a9ed86203e2ad40077299f7cc` | `portee-r-2020.html`<br>`portees/portee-r-2020.md` |
| `16372515.jpg` | `16372515.jpg` | https://www.mtbrabant.com/s/cc_images/cache_16372515.jpg | `cache_` — 1024×682 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 89180 | 1024×682 | `787e961f5bf3cba97bba48dd2288254b955e9243aeaadbeb94e2489bd284f6e7` | `portee-r-2020.html`<br>`portees/portee-r-2020.md` |
| `16372516.JPG` | `16372516.JPG` | https://www.mtbrabant.com/s/cc_images/cache_16372516.JPG | `cache_` — 891×590 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 52229 | 891×590 | `1bafaae61a0f518b903e515f16cc5d2722dae6d13e72d9f70dcdba7a1a39bd7f` | `portee-r-2020.html`<br>`portees/portee-r-2020.md` |
| `16372517.JPG` | `16372517.JPG` | https://www.mtbrabant.com/s/cc_images/cache_16372517.JPG | `cache_` — 1024×682 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 353932 | 1024×682 | `b4bb79e9ee2a2863afb59b7772d53b61882c3f4b8accc32623ab20da5aaeef1a` | `portee-r-2020.html`<br>`portees/portee-r-2020.md` |
| `16372518.jpg` | `16372518.jpg` | https://www.mtbrabant.com/s/cc_images/cache_16372518.jpg | `cache_` — 1024×682 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 190975 | 1024×682 | `885f711ba1ddadfc17025a7c4e53ac1bb18990f70d9d0e55c65a3505f3084e77` | `portee-r-2020.html`<br>`portees/portee-r-2020.md` |
| `16372519.jpg` | `16372519.jpg` | https://www.mtbrabant.com/s/cc_images/cache_16372519.jpg | `cache_` — 552×768 ; `thumb_` 17×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 69003 | 552×768 | `546526832c761e4ebd3c9cb0d719cbb3305be4e4cb79911dcf586a743e3daf42` | `portee-r-2020.html`<br>`portees/portee-r-2020.md` |
| `16410057.jpg` | `16410057.jpg` | https://www.mtbrabant.com/s/cc_images/cache_16410057.jpg | `cache_` — 1024×682 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 113740 | 1024×682 | `8e6399cc0d8b12996d0128b05a4b12e3108c12c85a2a53d9134ee2305f3be9fb` | `portee-o-2018.html`<br>`portees/portee-o-2018.md` |
| `16410058.jpg` | `16410058.jpg` | https://www.mtbrabant.com/s/cc_images/cache_16410058.jpg | `cache_` — 812×562 ; `thumb_` 36×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 166913 | 812×562 | `ca319f352ae52c391bb4c972f1a3864c775573cf88ad625499205b0aff260360` | `portee-o-2018.html`<br>`portees/portee-o-2018.md` |
| `16410059.jpg` | `16410059.jpg` | https://www.mtbrabant.com/s/cc_images/cache_16410059.jpg | `cache_` — 1024×682 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 79892 | 1024×682 | `69d096e86c85f906839cdca00c37c8cac8b292dfdc8c65606c0c8756999da64b` | `portee-o-2018.html`<br>`portees/portee-o-2018.md` |
| `16410060.jpg` | `16410060.jpg` | https://www.mtbrabant.com/s/cc_images/cache_16410060.jpg | `cache_` — 1024×654 ; `thumb_` 39×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 125355 | 1024×654 | `7b15a8c6115b9525c69bc8a7d72e627c70da05245cfcf1e910ee31d10cd1ffd6` | `portee-o-2018.html`<br>`portees/portee-o-2018.md` |
| `16410061.jpg` | `16410061.jpg` | https://www.mtbrabant.com/s/cc_images/cache_16410061.jpg | `cache_` — 960×640 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 108607 | 960×640 | `d556d7789625e2713f73323d94f770a2868343a9591c6ae20adc5474a32ce6b4` | `portee-o-2018.html`<br>`portees/portee-o-2018.md` |
| `16410062.JPG` | `16410062.JPG` | https://www.mtbrabant.com/s/cc_images/cache_16410062.JPG | `cache_` — 512×768 ; `thumb_` 16×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 215924 | 512×768 | `520591bd6cc11da2e68ad5303688edd9828df1c35b5edc91b9fc7130fdbc4b5b` | `portee-o-2018.html`<br>`portees/portee-o-2018.md` |
| `16410063.JPG` | `16410063.JPG` | https://www.mtbrabant.com/s/cc_images/cache_16410063.JPG | `cache_` — 1024×682 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 351456 | 1024×682 | `1453ac30d2699b6834f09085f3cd65376398081f432228e527a2e9962b9ea71d` | `portee-o-2018.html`<br>`portees/portee-o-2018.md` |
| `16410064.jpg` | `16410064.jpg` | https://www.mtbrabant.com/s/cc_images/cache_16410064.jpg | `cache_` — 542×768 ; `thumb_` 17×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 43951 | 542×768 | `a7910fe5d32ca46379d9e7a422eafd0080f475b0aea6ecb397aa4d95de05adf2` | `portee-o-2018.html`<br>`portees/portee-o-2018.md` |
| `16410065.jpg` | `16410065.jpg` | https://www.mtbrabant.com/s/cc_images/cache_16410065.jpg | `cache_` — 768×768 ; `thumb_` 25×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 68099 | 768×768 | `3f320e6a70dc0b551538cd0222b5aee3a71dd94b2a1d78454165e18774c9fc7c` | `portee-o-2018.html`<br>`portees/portee-o-2018.md` |
| `16410066.jpg` | `16410066.jpg` | https://www.mtbrabant.com/s/cc_images/cache_16410066.jpg | `cache_` — 851×506 ; `thumb_` 40×23 · `(nu)` 404 · `teaserbox_` 404 | 200 | 90733 | 851×506 | `b5d927ad7d58533fb1f4646c49a14e37eb9d2feb67eac9c1f51b6ef8a1eb1654` | `portee-o-2018.html`<br>`portees/portee-o-2018.md` |
| `16410067.jpg` | `16410067.jpg` | https://www.mtbrabant.com/s/cc_images/cache_16410067.jpg | `cache_` — 1024×682 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 801902 | 1024×682 | `db4161ddd4810703826fe2006195fa64f9599c9f28658ccd3a5b495a5df779ca` | `portee-o-2018.html`<br>`portees/portee-o-2018.md` |
| `16427154.jpg` | `16427154.jpg` | https://www.mtbrabant.com/s/cc_images/cache_16427154.jpg | `cache_` — 400×600 ; `thumb_` 16×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 54311 | 400×600 | `70a1f5f32f297d0563257b26d8ae312d555cf8e0eba0db4ebaa3ff35a92085b1` | `chien-ray-ban.html`<br>`chiens/chien-ray-ban.md` |
| `16427155.jpg` | `16427155.jpg` | https://www.mtbrabant.com/s/cc_images/cache_16427155.jpg | `cache_` — 557×768 ; `thumb_` 18×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 91176 | 557×768 | `e010b42110b4dd70c18184e0038d9352d10455ac501f0102ab46410ca6bb6eb2` | `chien-ray-ban.html`<br>`chiens/chien-ray-ban.md` |
| `16427156.JPG` | `16427156.JPG` | https://www.mtbrabant.com/s/cc_images/cache_16427156.JPG | `cache_` — 512×768 ; `thumb_` 16×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 50899 | 512×768 | `e73dbe32ac03fe47616ee3fa30f52cf31d105b5e5378d1d82fb48738cd2cda08` | `chien-ray-ban.html`<br>`chiens/chien-ray-ban.md` |
| `16458617.jpg` | `16458617.jpg` | https://www.mtbrabant.com/s/cc_images/teaserbox_16458617.jpg | `teaserbox_` — 184×274 ; `cache_` 66×98 · `(nu)` 404 · `thumb_` 404 | 200 | 7758 | 184×274 | `f920725d8563a666e3d3b8f0c6845dae05730855e35c808c799027208bc53ddd` | `chien-tesla.html`<br>`chiens/chien-tesla.md` |
| `16458622.png` | `16458622.png` | https://www.mtbrabant.com/s/cc_images/teaserbox_16458622.png | `teaserbox_` — 246×205 ; `cache_` 120×100 · `(nu)` 404 · `thumb_` 404 | 200 | 7674 | 246×205 | `ce63e3c1c67d0fb958436f51cc0573cb9461f3ba6186c9fae19ad8ed5ba77708` | `chien-tesla.html`<br>`chiens/chien-tesla.md` |
| `16458627.jpg` | `16458627.jpg` | https://www.mtbrabant.com/s/cc_images/teaserbox_16458627.jpg | `teaserbox_` — 237×213 ; `cache_` 103×92 · `(nu)` 404 · `thumb_` 404 | 200 | 15208 | 237×213 | `68914df7a4107796b5621f0779e87dce2bdf324ecbbbd35eeabeb12e0c774811` | `chien-tesla.html`<br>`chiens/chien-tesla.md` |
| `16497476.png` | `16497476.png` | https://www.mtbrabant.com/s/cc_images/teaserbox_16497476.png | `teaserbox_` — 900×585 ; `cache_` 658×428 · `(nu)` 404 · `thumb_` 404 | 200 | 1002317 | 900×585 | `4824e6a8bbf6953693af54514230f77e993d86c3da5f6b057d2ed6d66b533d15` | `travail.html`<br>`pages/travail.md` `travail.md` |
| `16503147.jpg` | `16503147.jpg` | https://www.mtbrabant.com/s/cc_images/cache_16503147.jpg | `cache_` — 563×768 ; `thumb_` 18×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 74376 | 563×768 | `c04ccef160826be4729799a0fb16b4c0a5d336328fbbf6815f1650d2a82f64bc` | `portee-t-2022.html`<br>`portees/portee-t-2022.md` |
| `16503148.JPG` | `16503148.JPG` | https://www.mtbrabant.com/s/cc_images/cache_16503148.JPG | `cache_` — 512×768 ; `thumb_` 16×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 65480 | 512×768 | `81eac3b5db07802de61611ae3cfc6c6548dc80cb8bba6ac13735e90258e470ca` | `portee-t-2022.html`<br>`portees/portee-t-2022.md` |
| `16503149.JPG` | `16503149.JPG` | https://www.mtbrabant.com/s/cc_images/cache_16503149.JPG | `cache_` — 512×768 ; `thumb_` 16×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 51634 | 512×768 | `d4b5bc52fe96bbf7015a2ae855cc0a1450b3e271324cf6dac6fd152be98afd9b` | `portee-t-2022.html`<br>`portees/portee-t-2022.md` |
| `16503150.jpg` | `16503150.jpg` | https://www.mtbrabant.com/s/cc_images/cache_16503150.jpg | `cache_` — 1024×768 ; `thumb_` 33×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 149225 | 1024×768 | `05dedd0907cab1c6f28abd6fbe8c5bf8b40b1a232be10a1524c39c1354edf37d` | `portee-t-2022.html`<br>`portees/portee-t-2022.md` |
| `16503151.JPG` | `16503151.JPG` | https://www.mtbrabant.com/s/cc_images/cache_16503151.JPG | `cache_` — 645×768 ; `thumb_` 21×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 103471 | 645×768 | `975e9db71cc228f763109bf1d3ebe8997cb3bb50898640ce0c6104d7586f6f8e` | `portee-t-2022.html`<br>`portees/portee-t-2022.md` |
| `16717128.jpg` | `16717128.jpg` | https://www.mtbrabant.com/s/cc_images/cache_16717128.jpg | `cache_` — 945×525 ; `thumb_` 40×22 · `(nu)` 404 · `teaserbox_` 404 | 200 | 383842 | 945×525 | `f6aff665580e929519756dfc6c9ee9cc018c113a2e75e0d26851bbc5ba8f0572` | `portee-p2-2019.html`<br>`portees/portee-p2-2019.md` |
| `16717129.jpg` | `16717129.jpg` | https://www.mtbrabant.com/s/cc_images/cache_16717129.jpg | `cache_` — 437×600 ; `thumb_` 18×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 46140 | 437×600 | `82828dd33c452fd1f94d0b44c4fa46dc339c78aea39a9d456d881a9e05c9165e` | `portee-p2-2019.html`<br>`portees/portee-p2-2019.md` |
| `16717130.JPG` | `16717130.JPG` | https://www.mtbrabant.com/s/cc_images/cache_16717130.JPG | `cache_` — 1024×682 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 519133 | 1024×682 | `b309661f3af881d1264bd3d5b73e110e08b417a7d38e5c7faca2a54ba6e40893` | `portee-p2-2019.html`<br>`portees/portee-p2-2019.md` |
| `16717131.jpg` | `16717131.jpg` | https://www.mtbrabant.com/s/cc_images/cache_16717131.jpg | `cache_` — 512×768 ; `thumb_` 16×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 39083 | 512×768 | `9c3bf31613d8a7c77ff68cfe3b1fca1281890f83671e65d94ab1602aeb2a1747` | `portee-p2-2019.html`<br>`portees/portee-p2-2019.md` |
| `16717132.jpg` | `16717132.jpg` | https://www.mtbrabant.com/s/cc_images/cache_16717132.jpg | `cache_` — 512×768 ; `thumb_` 16×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 58312 | 512×768 | `d867b470e4fb0dc6dd936dee3e5f324906d3826b5af8b26f30ea0fc4fcb421d2` | `portee-p2-2019.html`<br>`portees/portee-p2-2019.md` |
| `16717133.JPG` | `16717133.JPG` | https://www.mtbrabant.com/s/cc_images/cache_16717133.JPG | `cache_` — 1024×682 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 398151 | 1024×682 | `a820249559c7910e85d2f36fc37429b34d19233da7e6d022ac7b25e6832d9e4e` | `portee-p2-2019.html`<br>`portees/portee-p2-2019.md` |
| `16717134.jpg` | `16717134.jpg` | https://www.mtbrabant.com/s/cc_images/cache_16717134.jpg | `cache_` — 1024×682 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 193601 | 1024×682 | `927fe4d514d1911eaec307a2badec1530851c8d1340f890fc8dcb5f2b984d9bb` | `portee-p2-2019.html`<br>`portees/portee-p2-2019.md` |
| `16717135.jpg` | `16717135.jpg` | https://www.mtbrabant.com/s/cc_images/cache_16717135.jpg | `cache_` — 1024×682 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 103654 | 1024×682 | `e338c2871500fcd0b8f83a23b5c31a32a681878e064aa19861595f3be07c6d14` | `portee-p2-2019.html`<br>`portees/portee-p2-2019.md` |
| `16717136.jpg` | `16717136.jpg` | https://www.mtbrabant.com/s/cc_images/cache_16717136.jpg | `cache_` — 512×768 ; `thumb_` 16×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 62911 | 512×768 | `ce6ea4f09562e0e852cb39cd9f0f9e7a0f959050f69ddd180cf34632fa09ed30` | `portee-p2-2019.html`<br>`portees/portee-p2-2019.md` |
| `16791790.jpg` | `16791790.jpg` | https://www.mtbrabant.com/s/cc_images/cache_16791790.jpg | `cache_` — 1024×682 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 79599 | 1024×682 | `86b667f2f38dcc020c14e56c32e7bedb63c2e47c876428264ba77442058a70bf` | `chien-jango.html`<br>`chiens/chien-jango.md` |
| `16791791.jpg` | `16791791.jpg` | https://www.mtbrabant.com/s/cc_images/cache_16791791.jpg | `cache_` — 512×768 ; `thumb_` 16×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 98589 | 512×768 | `64b061d5a39fa088582afb3dabfae5a444ea8c1aee0963462e3fa4f053d84a98` | `chien-jango.html`<br>`chiens/chien-jango.md` |
| `16791792.jpg` | `16791792.jpg` | https://www.mtbrabant.com/s/cc_images/cache_16791792.jpg | `cache_` — 512×768 ; `thumb_` 16×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 46396 | 512×768 | `580593de9f11563636c8ca69f2fd033ed42c40ae7ee1bc5f64315bcf2c66ec2b` | `chien-jango.html`<br>`chiens/chien-jango.md` |
| `16791793.jpg` | `16791793.jpg` | https://www.mtbrabant.com/s/cc_images/cache_16791793.jpg | `cache_` — 1024×682 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 124048 | 1024×682 | `ea0978f48da51516c4630c65bb4e55ec878726cdc73a8cf7740ec5528b28195e` | `chien-jango.html`<br>`chiens/chien-jango.md` |
| `16791794.jpg` | `16791794.jpg` | https://www.mtbrabant.com/s/cc_images/cache_16791794.jpg | `cache_` — 512×768 ; `thumb_` 16×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 42076 | 512×768 | `4659af607656475d649ff408f9c8d4b563eda3f46420e1097dea91bb6cb2e89c` | `chien-jango.html`<br>`chiens/chien-jango.md` |
| `16791795.jpg` | `16791795.jpg` | https://www.mtbrabant.com/s/cc_images/cache_16791795.jpg | `cache_` — 901×655 ; `thumb_` 34×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 138058 | 901×655 | `86f6ebba9a3a970b681f9325db24102bed345a132adcd82d9d717a6d2e574337` | `chien-jango.html`<br>`chiens/chien-jango.md` |
| `16791796.JPG` | `16791796.JPG` | https://www.mtbrabant.com/s/cc_images/cache_16791796.JPG | `cache_` — 506×768 ; `thumb_` 16×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 82717 | 506×768 | `aae15d4ac7d623fa3a9efa3aba2602a54d2f3461d3ebd2005de3e43d4c9dd83f` | `chien-jango.html`<br>`chiens/chien-jango.md` |
| `17248756.JPG` | `17248756.JPG` | https://www.mtbrabant.com/s/cc_images/teaserbox_17248756.JPG | `teaserbox_` — 900×900 ; `cache_` 253×253 · `(nu)` 404 · `thumb_` 404 | 200 | 305492 | 900×900 | `67ab652b7eece4db37685b19d06bedb0abc4a7f12867eeb7694332a09dfdf948` | `accueil.html`<br>`accueil.md` `pages/accueil.md` |
| `17364989.jpg` | `17364989.jpg` | https://www.mtbrabant.com/s/cc_images/teaserbox_17364989.jpg | `teaserbox_` — 184×274 ; `cache_` 67×99 · `(nu)` 404 · `thumb_` 404 | 200 | 7758 | 184×274 | `f920725d8563a666e3d3b8f0c6845dae05730855e35c808c799027208bc53ddd` | `chien-roxane.html`<br>`chiens/chien-roxane.md` |
| `17364999.jpg` | `17364999.jpg` | https://www.mtbrabant.com/s/cc_images/teaserbox_17364999.jpg | `teaserbox_` — 237×213 ; `cache_` 103×92 · `(nu)` 404 · `thumb_` 404 | 200 | 15208 | 237×213 | `68914df7a4107796b5621f0779e87dce2bdf324ecbbbd35eeabeb12e0c774811` | `chien-roxane.html`<br>`chiens/chien-roxane.md` |
| `17365000.png` | `17365000.png` | https://www.mtbrabant.com/s/cc_images/teaserbox_17365000.png | `teaserbox_` — 246×205 ; `cache_` 119×99 · `(nu)` 404 · `thumb_` 404 | 200 | 7674 | 246×205 | `ce63e3c1c67d0fb958436f51cc0573cb9461f3ba6186c9fae19ad8ed5ba77708` | `chien-roxane.html`<br>`chiens/chien-roxane.md` |
| `17365005.JPG` | `17365005.JPG` | https://www.mtbrabant.com/s/cc_images/teaserbox_17365005.JPG | `teaserbox_` — 720×1080 ; `cache_` 200×300 · `(nu)` 404 · `thumb_` 404 | 200 | 69269 | 720×1080 | `1a1433a737a2fa425e98ea591ae8b1f8d765278e819d2f51f51b86afcbc85254` | `chien-roxane.html`<br>`chiens/chien-roxane.md` |
| `17422748.jpg` | `17422748.jpg` | https://www.mtbrabant.com/s/cc_images/teaserbox_17422748.jpg | `teaserbox_` — 900×629 ; `cache_` 650×454 · `(nu)` 404 · `thumb_` 404 | 200 | 468713 | 900×629 | `21ce9ffeca07224fd61c9f84b9e6344caa4db9c96d8be1d94688ec3df80c6f18` | `chien-roxane.html`<br>`chiens/chien-roxane.md` |
| `17457668.png` | `17457668.png` | https://www.mtbrabant.com/s/cc_images/cache_17457668.png | `cache_` — 511×600 ; `thumb_` 21×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 492777 | 511×600 | `6c56e16d73dede7b01ccc70f79c7958fdaa35dfbdbc9f097a2d9b44942043cf2` | `portee-u2-2023.html`<br>`portees/portee-u2-2023.md` |
| `17457669.JPG` | `17457669.JPG` | https://www.mtbrabant.com/s/cc_images/cache_17457669.JPG | `cache_` — 1024×682 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 658049 | 1024×682 | `85632e3eadff9bdf35a21bc2c7c319921f929effdbbf5424c053a55456e649ca` | `portee-u2-2023.html`<br>`portees/portee-u2-2023.md` |
| `17457670.JPG` | `17457670.JPG` | https://www.mtbrabant.com/s/cc_images/cache_17457670.JPG | `cache_` — 512×768 ; `thumb_` 16×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 42121 | 512×768 | `ed0cc962623ce639e153790258cc0ffc6317c0d615ec47256014e517f50f75db` | `portee-u2-2023.html`<br>`portees/portee-u2-2023.md` |
| `17457677.jpg` | `17457677.jpg` | https://www.mtbrabant.com/s/cc_images/cache_17457677.jpg | `cache_` — 800×555 ; `thumb_` 36×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 198138 | 800×555 | `9e8abf00385264ee1fe1b0f15c7c48b993efc076f4bceb9298f49a94cd544a08` | `portee-u1-2023.html`<br>`portees/portee-u1-2023.md` |
| `17457678.jpg` | `17457678.jpg` | https://www.mtbrabant.com/s/cc_images/cache_17457678.jpg | `cache_` — 614×768 ; `thumb_` 20×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 35835 | 614×768 | `9958c3eac83d66ebe80e792157b7f4d1a4a582cf646df263e2fd5fd5998aa1c7` | `portee-u1-2023.html`<br>`portees/portee-u1-2023.md` |
| `17457679.jpg` | `17457679.jpg` | https://www.mtbrabant.com/s/cc_images/cache_17457679.jpg | `cache_` — 1024×768 ; `thumb_` 33×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 94754 | 1024×768 | `a14ea0c764d3d8707ec81ab7d6b28e0290610f8f7d09b65fe208586e995553bf` | `portee-u1-2023.html`<br>`portees/portee-u1-2023.md` |
| `17457680.jpg` | `17457680.jpg` | https://www.mtbrabant.com/s/cc_images/cache_17457680.jpg | `cache_` — 511×768 ; `thumb_` 16×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 70770 | 511×768 | `f05afc10b8979240801163b04ec603d1006ef827b51a4f6b63844f3803a5e225` | `portee-u1-2023.html`<br>`portees/portee-u1-2023.md` |
| `17457681.JPG` | `17457681.JPG` | https://www.mtbrabant.com/s/cc_images/cache_17457681.JPG | `cache_` — 1024×682 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 430481 | 1024×682 | `a899957e13e086fe8ef08d822294a8e88b0b266a94633a604bf24c8b8c0040eb` | `portee-u1-2023.html`<br>`portees/portee-u1-2023.md` |
| `17603603.jpg` | `17603603.jpg` | https://www.mtbrabant.com/s/cc_images/teaserbox_17603603.jpg | `teaserbox_` — 184×274 ; `cache_` 66×98 · `(nu)` 404 · `thumb_` 404 | 200 | 7758 | 184×274 | `f920725d8563a666e3d3b8f0c6845dae05730855e35c808c799027208bc53ddd` | `chien-you.html`<br>`chiens/chien-you.md` |
| `17603607.png` | `17603607.png` | https://www.mtbrabant.com/s/cc_images/teaserbox_17603607.png | `teaserbox_` — 246×205 ; `cache_` 120×100 · `(nu)` 404 · `thumb_` 404 | 200 | 7674 | 246×205 | `ce63e3c1c67d0fb958436f51cc0573cb9461f3ba6186c9fae19ad8ed5ba77708` | `chien-you.html`<br>`chiens/chien-you.md` |
| `17603625.jpg` | `17603625.jpg` | https://www.mtbrabant.com/s/cc_images/teaserbox_17603625.jpg | `teaserbox_` — 237×213 ; `cache_` 103×92 · `(nu)` 404 · `thumb_` 404 | 200 | 15208 | 237×213 | `68914df7a4107796b5621f0779e87dce2bdf324ecbbbd35eeabeb12e0c774811` | `chien-you.html`<br>`chiens/chien-you.md` |
| `17603639.jpg` | `17603639.jpg` | https://www.mtbrabant.com/s/cc_images/cache_17603639.jpg | `cache_` — 616×600 ; `thumb_` 25×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 77209 | 616×600 | `f816269e1a0ffb0b9a1f2ab13641ae9dec18445b1ba98b59c3a9410fd337287e` | `chien-you.html`<br>`chiens/chien-you.md` |
| `17603640.jpg` | `17603640.jpg` | https://www.mtbrabant.com/s/cc_images/cache_17603640.jpg | `cache_` — 800×533 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 30688 | 800×533 | `62055d0371a3b2c38f65eec48ab9e066e6bda46eab6a674c380873e488e6d510` | `chien-you.html`<br>`chiens/chien-you.md` |
| `18214736.jpg` | `18214736.jpg` | https://www.mtbrabant.com/s/cc_images/cache_18214736.jpg | `cache_` — 800×559 ; `thumb_` 35×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 393478 | 800×559 | `4adba8a49672ec11dca5d0ddc4ef3ec6e48679f33c0961e27bcfeb1aed1ef7ea` | `portee-u3-2023.html`<br>`portees/portee-u3-2023.md` |
| `18214737.JPG` | `18214737.JPG` | https://www.mtbrabant.com/s/cc_images/cache_18214737.JPG | `cache_` — 1024×682 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 359068 | 1024×682 | `048a6eb8b409226afc8f0286d2be3864f53bcc57fbe708948cb58715371a4311` | `portee-u3-2023.html`<br>`portees/portee-u3-2023.md` |
| `18214738.JPG` | `18214738.JPG` | https://www.mtbrabant.com/s/cc_images/cache_18214738.JPG | `cache_` — 1024×654 ; `thumb_` 39×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 136433 | 1024×654 | `fe498fbefbcf04b4d29c7338aa3f3b072f6b4455924ed885a5d8aa4659150556` | `portee-u3-2023.html`<br>`portees/portee-u3-2023.md` |
| `18273970.JPG` | `18273970.JPG` | https://www.mtbrabant.com/s/cc_images/cache_18273970.JPG | `cache_` — 1024×682 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 362959 | 1024×682 | `0ab9b6db524238239fd2e732504c079cee8cb63e5d3d95669eddbdcd6f357a0e` | `portee-v2-2024.html`<br>`portees/portee-v2-2024.md` |
| `18273971.JPG` | `18273971.JPG` | https://www.mtbrabant.com/s/cc_images/cache_18273971.JPG | `cache_` — 1024×682 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 466105 | 1024×682 | `c5e30d76b95830a8d1db649c0b5aff0c6f7493cf75d8cbaa11ce12c6f95a84c9` | `portee-v2-2024.html`<br>`portees/portee-v2-2024.md` |
| `18507513.jpg` | `18507513.jpg` | https://www.mtbrabant.com/s/cc_images/cache_18507513.jpg | `cache_` — 960×640 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 124342 | 960×640 | `f6b2f57a0c4435fbe8ad181aea29258ea58c6a212c2cfef4f9facb912193eb4a` | `chien-tesla.html`<br>`chiens/chien-tesla.md` |
| `18507514.jpg` | `18507514.jpg` | https://www.mtbrabant.com/s/cc_images/cache_18507514.jpg | `cache_` — 960×640 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 128270 | 960×640 | `9cd79ccbf4b3a9923e62c1f77d3e00c965c94e4b58b16bf3966517fe13111a81` | `chien-tesla.html`<br>`chiens/chien-tesla.md` |
| `18507515.jpg` | `18507515.jpg` | https://www.mtbrabant.com/s/cc_images/cache_18507515.jpg | `cache_` — 500×600 ; `thumb_` 20×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 34698 | 500×600 | `5a67f7a57bf1585a1e03d2f00d9d13629575634b4a0306e863bd897dfbb7bd19` | `chien-tesla.html`<br>`chiens/chien-tesla.md` |
| `18507516.jpeg` | `18507516.jpeg` | https://www.mtbrabant.com/s/cc_images/cache_18507516.jpeg | `cache_` — 1024×750 ; `thumb_` 34×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 138712 | 1024×750 | `3ba0f5b09b7a239e3f8217190fa226e3e4a45027c14d9d95765251104cbfef88` | `chien-tesla.html`<br>`chiens/chien-tesla.md` |
| `18507517.JPG` | `18507517.JPG` | https://www.mtbrabant.com/s/cc_images/cache_18507517.JPG | `cache_` — 499×768 ; `thumb_` 16×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 90432 | 499×768 | `cd281de9fae0bb2298152cfca21532cc692cf7a7bd588abf8b7395a1efe02d38` | `chien-tesla.html`<br>`chiens/chien-tesla.md` |
| `18507518.jpeg` | `18507518.jpeg` | https://www.mtbrabant.com/s/cc_images/cache_18507518.jpeg | `cache_` — 1024×764 ; `thumb_` 33×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 191364 | 1024×764 | `6916c6625ed5a5090b72cc55a4890b3f39be43790c25f21408dba8fc188b990f` | `chien-tesla.html`<br>`chiens/chien-tesla.md` |
| `18989099.jpg` | `18989099.jpg` | https://www.mtbrabant.com/s/cc_images/teaserbox_18989099.jpg | `teaserbox_` — 184×274 ; `cache_` 66×98 · `(nu)` 404 · `thumb_` 404 | 200 | 7758 | 184×274 | `f920725d8563a666e3d3b8f0c6845dae05730855e35c808c799027208bc53ddd` | `chien-very-best.html`<br>`chiens/chien-very-best.md` |
| `18989103.png` | `18989103.png` | https://www.mtbrabant.com/s/cc_images/teaserbox_18989103.png | `teaserbox_` — 246×205 ; `cache_` 116×96 · `(nu)` 404 · `thumb_` 404 | 200 | 7674 | 246×205 | `ce63e3c1c67d0fb958436f51cc0573cb9461f3ba6186c9fae19ad8ed5ba77708` | `chien-very-best.html`<br>`chiens/chien-very-best.md` |
| `18989107.jpg` | `18989107.jpg` | https://www.mtbrabant.com/s/cc_images/teaserbox_18989107.jpg | `teaserbox_` — 237×213 ; `cache_` 103×92 · `(nu)` 404 · `thumb_` 404 | 200 | 15208 | 237×213 | `68914df7a4107796b5621f0779e87dce2bdf324ecbbbd35eeabeb12e0c774811` | `chien-very-best.html`<br>`chiens/chien-very-best.md` |
| `18989112.png` | `18989112.png` | https://www.mtbrabant.com/s/cc_images/cache_18989112.png | `cache_` — 1024×682 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 1198183 | 1024×682 | `e31ea4130bd305d4bd495625bbfce5746e46d13d7c0f4240e081d35845ca0e72` | `chien-very-best.html`<br>`chiens/chien-very-best.md` |
| `18989138.jpg` | `18989138.jpg` | https://www.mtbrabant.com/s/cc_images/cache_18989138.jpg | `cache_` — 1024×646 ; `thumb_` 39×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 75155 | 1024×646 | `29bf820978df55060cf0c7249a0bcaef52bac08085b1530d6cb83aa67792b3f0` | `chien-pegaz.html`<br>`chiens/chien-pegaz.md` |
| `18989139.jpg` | `18989139.jpg` | https://www.mtbrabant.com/s/cc_images/cache_18989139.jpg | `cache_` — 1024×576 ; `thumb_` 40×22 · `(nu)` 404 · `teaserbox_` 404 | 200 | 165906 | 1024×576 | `651580bc9e652387dfdcda14b9ccbc5c4a2ed5065d66869de680684ed9e30bf5` | `chien-pegaz.html`<br>`chiens/chien-pegaz.md` |
| `18989140.jpg` | `18989140.jpg` | https://www.mtbrabant.com/s/cc_images/cache_18989140.jpg | `cache_` — 512×768 ; `thumb_` 16×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 71080 | 512×768 | `cfb6c2f3b8416d1f8b00f8c711e982bbccbb496487c595a69150f03af7afc0f7` | `chien-pegaz.html`<br>`chiens/chien-pegaz.md` |
| `18989141.jpg` | `18989141.jpg` | https://www.mtbrabant.com/s/cc_images/cache_18989141.jpg | `cache_` — 512×768 ; `thumb_` 16×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 79550 | 512×768 | `734198c8afba1a6fa1f5159e8f40fef87f38cbe39eac9dd01ddb5d37bdeb1f80` | `chien-pegaz.html`<br>`chiens/chien-pegaz.md` |
| `18989142.JPG` | `18989142.JPG` | https://www.mtbrabant.com/s/cc_images/cache_18989142.JPG | `cache_` — 1024×682 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 656990 | 1024×682 | `300eb0b190dd38d9ab1d4521a081b8e12ef22034b2de4c7f298e830fe4013755` | `chien-pegaz.html`<br>`chiens/chien-pegaz.md` |
| `19031597.jpg` | `19031597.jpg` | https://www.mtbrabant.com/s/cc_images/cache_19031597.jpg | `cache_` — 511×768 ; `thumb_` 16×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 71725 | 511×768 | `3b89f55f8041ce147405cd233085573e4d5efd275aa32040b585152b441905b3` | `portee-a1-2025.html`<br>`portees/portee-a1-2025.md` |
| `19031598.jpg` | `19031598.jpg` | https://www.mtbrabant.com/s/cc_images/cache_19031598.jpg | `cache_` — 557×768 ; `thumb_` 18×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 109321 | 557×768 | `42d2b3d8324df2b692eb9f8d488292a487a762db0b24c802a4c3bf071e2ee25c` | `portee-a1-2025.html`<br>`portees/portee-a1-2025.md` |
| `19031599.jpg` | `19031599.jpg` | https://www.mtbrabant.com/s/cc_images/cache_19031599.jpg | `cache_` — 548×768 ; `thumb_` 17×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 97290 | 548×768 | `faa1ce5e0bb79752994f22ef1546ee4b5a91b3f5471820ff7457051e43b41167` | `portee-a1-2025.html`<br>`portees/portee-a1-2025.md` |
| `19031600.JPG` | `19031600.JPG` | https://www.mtbrabant.com/s/cc_images/cache_19031600.JPG | `cache_` — 1024×768 ; `thumb_` 33×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 114148 | 1024×768 | `7a4eeb4e4bee61276ff075273b52f7bc498538068c7fef6727594585a2b2d2f7` | `portee-a1-2025.html`<br>`portees/portee-a1-2025.md` |
| `19031601.jpg` | `19031601.jpg` | https://www.mtbrabant.com/s/cc_images/cache_19031601.jpg | `cache_` — 511×768 ; `thumb_` 16×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 76828 | 511×768 | `c0dfb11e3e68d9dd3c8a1a7cd51d096640a1c83dd8cfc03d775ff163afcd340e` | `portee-a1-2025.html`<br>`portees/portee-a1-2025.md` |
| `19031602.JPG` | `19031602.JPG` | https://www.mtbrabant.com/s/cc_images/cache_19031602.JPG | `cache_` — 1024×682 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 299398 | 1024×682 | `dbb5103755fa17e45cce7ac5ae96c0f92bc1c9f6696f1027141263760b300ad4` | `portee-a1-2025.html`<br>`portees/portee-a1-2025.md` |
| `19031603.JPG` | `19031603.JPG` | https://www.mtbrabant.com/s/cc_images/cache_19031603.JPG | `cache_` — 1024×682 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 314492 | 1024×682 | `cd9f2e1ff70ebd979c1769cb20d2d85ce051ed1ae0b2733c76c2927ca53fb431` | `portee-a1-2025.html`<br>`portees/portee-a1-2025.md` |
| `19031691.JPG` | `19031691.JPG` | https://www.mtbrabant.com/s/cc_images/cache_19031691.JPG | `cache_` — 800×533 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 257349 | 800×533 | `65dde9d02c269ae450323a258c853558687a6b658c83c6acee0b52d63ba17231` | `portee-a3-2025.html`<br>`portees/portee-a3-2025.md` |
| `19031692.JPG` | `19031692.JPG` | https://www.mtbrabant.com/s/cc_images/cache_19031692.JPG | `cache_` — 800×533 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 256325 | 800×533 | `b98f1dc6805e50af95b582b663b883f17d4c817d32d200f101fca9019771cbf6` | `portee-a3-2025.html`<br>`portees/portee-a3-2025.md` |
| `19031693.JPG` | `19031693.JPG` | https://www.mtbrabant.com/s/cc_images/cache_19031693.JPG | `cache_` — 800×533 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 262966 | 800×533 | `4c755906f60da918a4c5648788eb6c459796e4365b0e6c76e431694962a8b1ae` | `portee-a3-2025.html`<br>`portees/portee-a3-2025.md` |
| `19031694.JPG` | `19031694.JPG` | https://www.mtbrabant.com/s/cc_images/cache_19031694.JPG | `cache_` — 800×533 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 280266 | 800×533 | `afda1df4f1bfb05ef14c472517852ef65ed29f06bb17c7712f520555db780435` | `portee-a3-2025.html`<br>`portees/portee-a3-2025.md` |
| `19031695.JPG` | `19031695.JPG` | https://www.mtbrabant.com/s/cc_images/cache_19031695.JPG | `cache_` — 800×533 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 269966 | 800×533 | `a523bb294399d17a3607aa716c93b2d1af36ca868838060f9fbb01a77c54e3f7` | `portee-a3-2025.html`<br>`portees/portee-a3-2025.md` |
| `19031696.JPG` | `19031696.JPG` | https://www.mtbrabant.com/s/cc_images/cache_19031696.JPG | `cache_` — 800×533 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 238904 | 800×533 | `03f100e06c3ea91b227bc5317d8afa391e7e6a7c528c16bce67d9b055b846bb8` | `portee-a3-2025.html`<br>`portees/portee-a3-2025.md` |
| `19031697.JPG` | `19031697.JPG` | https://www.mtbrabant.com/s/cc_images/cache_19031697.JPG | `cache_` — 800×533 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 299621 | 800×533 | `593f741020c4e8ce012dd0146cc188d15c77e2bf677ffabf0c745fb30c3f209c` | `portee-a3-2025.html`<br>`portees/portee-a3-2025.md` |
| `19031698.JPG` | `19031698.JPG` | https://www.mtbrabant.com/s/cc_images/cache_19031698.JPG | `cache_` — 800×533 ; `thumb_` 37×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 301074 | 800×533 | `46cca3e59dd5a50a8065d9acf41b45e67ba6cb78fdfa859b061a1cb748f1509e` | `portee-a3-2025.html`<br>`portees/portee-a3-2025.md` |
| `19228730.jpg` | `19228730.jpg` | https://www.mtbrabant.com/s/cc_images/teaserbox_19228730.jpg | `teaserbox_` — 900×600 ; `cache_` 328×218 · `(nu)` 404 · `thumb_` 404 | 200 | 232717 | 900×600 | `80dabf6789238f184841d2e47aa2aede91b99967a9ac0c79b83e5c8bdd68aadf` | `accueil.html`<br>`accueil.md` `pages/accueil.md` |
| `5971927.JPG` | `5971927.JPG` | https://www.mtbrabant.com/s/cc_images/teaserbox_5971927.JPG | `teaserbox_` — 792×792 ; `cache_` 253×253 · `(nu)` 404 · `thumb_` 404 | 200 | 132372 | 792×792 | `e91123de545520392fe9668c557d012673bf0b1df77c180612052d39d7198167` | `accueil.html`<br>`accueil.md` `pages/accueil.md` |
| `5971928.JPG` | `5971928.JPG` | https://www.mtbrabant.com/s/cc_images/teaserbox_5971928.JPG | `teaserbox_` — 900×892 ; `cache_` 253×250 · `(nu)` 404 · `thumb_` 404 | 200 | 180516 | 900×892 | `b67cc724d267be1f0312b3aabe7f75917d14827a2999d6d653d73b4c15cf4a56` | `accueil.html`<br>`accueil.md` `pages/accueil.md` |
| `5972679.jpg` | `5972679.jpg` | https://www.mtbrabant.com/s/cc_images/teaserbox_5972679.jpg | `teaserbox_` — 900×892 ; `cache_` 254×251 · `(nu)` 404 · `thumb_` 404 | 200 | 320934 | 900×892 | `6024a5230bddee4c90a413d5256e34c794ff1fd240a2738d10f2378dfd2fce97` | `accueil.html`<br>`accueil.md` `pages/accueil.md` |
| `5983206.jpg` | `5983206.jpg` | https://www.mtbrabant.com/s/cc_images/teaserbox_5983206.jpg | `teaserbox_` — 900×636 ; `cache_` 658×465 · `(nu)` 404 · `thumb_` 404 | 200 | 73814 | 900×636 | `d8a32d409ec793f519158fb7131c718150bf254b0131ddf706e5f0d316c3d126` | `portee-m-2016.html`<br>`portees/portee-m-2016.md` |
| `5992782.jpg` | `5992782.jpg` | https://www.mtbrabant.com/s/cc_images/teaserbox_5992782.jpg | `teaserbox_` — 900×508 ; `cache_` 658×372 · `(nu)` 404 · `thumb_` 404 | 200 | 114383 | 900×508 | `782e54fbf572fc88aeb3924056ba80fc5f1cdb074a9a5801bb5957c923aa54e2` | `la-meute.html`<br>`la-meute.md` |
| `6163654.jpg` | `6163654.jpg` | https://www.mtbrabant.com/s/cc_images/teaserbox_6163654.jpg | `teaserbox_` — 184×274 ; `cache_` 67×99 · `(nu)` 404 · `thumb_` 404 | 200 | 7758 | 184×274 | `f920725d8563a666e3d3b8f0c6845dae05730855e35c808c799027208bc53ddd` | `chien-opium.html`<br>`chiens/chien-opium.md` |
| `6163665.jpg` | `6163665.jpg` | https://www.mtbrabant.com/s/cc_images/teaserbox_6163665.jpg | `teaserbox_` — 184×274 ; `cache_` 67×99 · `(nu)` 404 · `thumb_` 404 | 200 | 7758 | 184×274 | `f920725d8563a666e3d3b8f0c6845dae05730855e35c808c799027208bc53ddd` | `chien-jango.html`<br>`chiens/chien-jango.md` |
| `6163682.png` | `6163682.png` | https://www.mtbrabant.com/s/cc_images/teaserbox_6163682.png | `teaserbox_` — 246×205 ; `cache_` 121×100 · `(nu)` 404 · `thumb_` 404 | 200 | 7674 | 246×205 | `ce63e3c1c67d0fb958436f51cc0573cb9461f3ba6186c9fae19ad8ed5ba77708` | `chien-jango.html`<br>`chiens/chien-jango.md` |
| `6163702.jpg` | `6163702.jpg` | https://www.mtbrabant.com/s/cc_images/teaserbox_6163702.jpg | `teaserbox_` — 184×274 ; `cache_` 67×99 · `(nu)` 404 · `thumb_` 404 | 200 | 7758 | 184×274 | `f920725d8563a666e3d3b8f0c6845dae05730855e35c808c799027208bc53ddd` | `chien-etch.html`<br>`chiens/chien-etch.md` |
| `6163707.png` | `6163707.png` | https://www.mtbrabant.com/s/cc_images/teaserbox_6163707.png | `teaserbox_` — 246×205 ; `cache_` 121×100 · `(nu)` 404 · `thumb_` 404 | 200 | 7674 | 246×205 | `ce63e3c1c67d0fb958436f51cc0573cb9461f3ba6186c9fae19ad8ed5ba77708` | `chien-etch.html`<br>`chiens/chien-etch.md` |
| `6410246.jpg` | `6410246.jpg` | https://www.mtbrabant.com/s/cc_images/teaserbox_6410246.jpg | `teaserbox_` — 237×213 ; `cache_` 112×100 · `(nu)` 404 · `thumb_` 404 | 200 | 15208 | 237×213 | `68914df7a4107796b5621f0779e87dce2bdf324ecbbbd35eeabeb12e0c774811` | `chien-jango.html`<br>`chiens/chien-jango.md` |
| `6410334.png` | `6410334.png` | https://www.mtbrabant.com/s/cc_images/teaserbox_6410334.png | `teaserbox_` — 246×205 ; `cache_` 121×100 · `(nu)` 404 · `thumb_` 404 | 200 | 7674 | 246×205 | `ce63e3c1c67d0fb958436f51cc0573cb9461f3ba6186c9fae19ad8ed5ba77708` | `chien-opium.html`<br>`chiens/chien-opium.md` |
| `6410368.jpg` | `6410368.jpg` | https://www.mtbrabant.com/s/cc_images/teaserbox_6410368.jpg | `teaserbox_` — 237×213 ; `cache_` 103×92 · `(nu)` 404 · `thumb_` 404 | 200 | 15208 | 237×213 | `68914df7a4107796b5621f0779e87dce2bdf324ecbbbd35eeabeb12e0c774811` | `chien-opium.html`<br>`chiens/chien-opium.md` |
| `6412830.jpg` | `6412830.jpg` | https://www.mtbrabant.com/s/cc_images/teaserbox_6412830.jpg | `teaserbox_` — 640×960 ; `cache_` 116×174 · `(nu)` 404 · `thumb_` 404 | 200 | 61084 | 640×960 | `90cd91b87c24b8d221a890b01f5ea1a7f7ef81fe842e0e67575ca030556adbe5` | `bhpl.html`<br>`pages/bhpl.md` |
| `6419772.jpg` | `6419772.jpg` | https://www.mtbrabant.com/s/cc_images/teaserbox_6419772.jpg | `teaserbox_` — 237×213 ; `cache_` 101×90 · `(nu)` 404 · `thumb_` 404 | 200 | 15208 | 237×213 | `68914df7a4107796b5621f0779e87dce2bdf324ecbbbd35eeabeb12e0c774811` | `chien-etch.html`<br>`chiens/chien-etch.md` |
| `6427420.jpg` | `6427420.jpg` | https://www.mtbrabant.com/s/cc_images/teaserbox_6427420.jpg | `teaserbox_` — 184×274 ; `cache_` 67×99 · `(nu)` 404 · `thumb_` 404 | 200 | 7758 | 184×274 | `f920725d8563a666e3d3b8f0c6845dae05730855e35c808c799027208bc53ddd` | `chien-tara.html`<br>`chiens/chien-tara.md` |
| `6427427.jpg` | `6427427.jpg` | https://www.mtbrabant.com/s/cc_images/teaserbox_6427427.jpg | `teaserbox_` — 184×274 ; `cache_` 67×99 · `(nu)` 404 · `thumb_` 404 | 200 | 7758 | 184×274 | `f920725d8563a666e3d3b8f0c6845dae05730855e35c808c799027208bc53ddd` | `chien-maya.html`<br>`chiens/chien-maya.md` |
| `6427430.png` | `6427430.png` | https://www.mtbrabant.com/s/cc_images/teaserbox_6427430.png | `teaserbox_` — 246×205 ; `cache_` 121×100 · `(nu)` 404 · `thumb_` 404 | 200 | 7674 | 246×205 | `ce63e3c1c67d0fb958436f51cc0573cb9461f3ba6186c9fae19ad8ed5ba77708` | `chien-maya.html`<br>`chiens/chien-maya.md` |
| `6427437.jpg` | `6427437.jpg` | https://www.mtbrabant.com/s/cc_images/teaserbox_6427437.jpg | `teaserbox_` — 184×274 ; `cache_` 67×99 · `(nu)` 404 · `thumb_` 404 | 200 | 7758 | 184×274 | `f920725d8563a666e3d3b8f0c6845dae05730855e35c808c799027208bc53ddd` | `chien-gribouille.html`<br>`chiens/chien-gribouille.md` |
| `6427450.png` | `6427450.png` | https://www.mtbrabant.com/s/cc_images/teaserbox_6427450.png | `teaserbox_` — 246×205 ; `cache_` 120×100 · `(nu)` 404 · `thumb_` 404 | 200 | 7674 | 246×205 | `ce63e3c1c67d0fb958436f51cc0573cb9461f3ba6186c9fae19ad8ed5ba77708` | `chien-tara.html`<br>`chiens/chien-tara.md` |
| `6427455.png` | `6427455.png` | https://www.mtbrabant.com/s/cc_images/teaserbox_6427455.png | `teaserbox_` — 246×205 ; `cache_` 120×100 · `(nu)` 404 · `thumb_` 404 | 200 | 7674 | 246×205 | `ce63e3c1c67d0fb958436f51cc0573cb9461f3ba6186c9fae19ad8ed5ba77708` | `chien-gribouille.html`<br>`chiens/chien-gribouille.md` |
| `6477274.JPG` | `6477274.JPG` | https://www.mtbrabant.com/s/cc_images/teaserbox_6477274.JPG | `teaserbox_` — 759×1080 ; `cache_` 215×305 · `(nu)` 404 · `thumb_` 404 | 200 | 186973 | 759×1080 | `050278639896982ee6867b353eeb97029baa276d8919b6d17f044fadb724ac22` | `accueil.html`<br>`accueil.md` `pages/accueil.md` |
| `6477315.JPG` | `6477315.JPG` | https://www.mtbrabant.com/s/cc_images/teaserbox_6477315.JPG | `teaserbox_` — 900×896 ; `cache_` 253×251 · `(nu)` 404 · `thumb_` 404 | 200 | 137064 | 900×896 | `593fe0a1cc7be36d88f5ab2da6cda6a22cb31a1b0d45ba4b82e386fa144b7774` | `accueil.html`<br>`accueil.md` `pages/accueil.md` |
| `7127390.jpg` | `7127390.jpg` | https://www.mtbrabant.com/s/cc_images/teaserbox_7127390.jpg | `teaserbox_` — 900×600 ; `cache_` 435×290 · `(nu)` 404 · `thumb_` 404 | 200 | 64563 | 900×600 | `07ef36329ad03315ae0de0ca8a2357c42dd1c5633d77e7936df594d37f2882d7` | `portee-c-2007.html`<br>`portees/portee-c-2007.md` |
| `7128435.png` | `7128435.png` | https://www.mtbrabant.com/s/cc_images/cache_7128435.png | `cache_` — 490×540 ; `teaserbox_` 186×205 · `(nu)` 404 · `thumb_` 404 | 200 | 62203 | 490×540 | `46c909d31d216e520251bbaaeb1094edfce99480620195ebfcbe070ceedb9433` | `bhpl.html`<br>`pages/bhpl.md` |
| `7437995.jpg` | `7437995.jpg` | https://www.mtbrabant.com/s/cc_images/cache_7437995.jpg | `cache_` — 719×1080 ; `thumb_` 25×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 87224 | 719×1080 | `db58fdf603a19da7c7b7773073751449f84fa5d346ceeeaa6965562bf275c1b5` | `chien-etch.html`<br>`chiens/chien-etch.md` |
| `7437996.jpg` | `7437996.jpg` | https://www.mtbrabant.com/s/cc_images/cache_7437996.jpg | `cache_` — 1527×1080 ; `thumb_` 25×25 · `(nu)` 404 · `teaserbox_` 404 | 200 | 316914 | 1527×1080 | `f8f7b14d4bfe1f66eabeca70354018563d917638700c0fe4db95180631daf860` | `chien-etch.html`<br>`chiens/chien-etch.md` |

Les URL portent, dans le HTML source, un suffixe de cache `?t=…` (époque Unix). Il n'a **aucun**
effet sur les octets servis et n'est pas reproduit ici ; le paramètre de taille observé sur le
bandeau (`?…920px.313px`) est de même **ignoré par le serveur** (mesuré : la même URL sans
paramètre, avec ce paramètre, et avec `4000px.4000px`, renvoie les mêmes 77 536 octets et les
mêmes 920×313).

### Identifiants distincts servant des octets identiques

Constat **factuel**, relevé et non interprété : plusieurs identifiants IONOS distincts
servent des fichiers au SHA-256 identique — la même image téléversée plusieurs fois dans
l'éditeur IONOS. **Ils ne sont pas fusionnés** : la clé d'archivage est l'identifiant, et
chacun est cité par une page différente.

| SHA-256 | Identifiants | Dimensions |
|---|---|---|
| `f920725d8563a666…` | `13346836.jpg` `13346847.jpg` `13553829.jpg` `14801494.jpg` `14893746.jpg` `15015993.jpg` `16458617.jpg` `17364989.jpg` `17603603.jpg` `18989099.jpg` `6163654.jpg` `6163665.jpg` `6163702.jpg` `6427420.jpg` `6427427.jpg` `6427437.jpg` | 184×274 |
| `ce63e3c1c67d0fb9…` | `13346839.png` `13346851.png` `13553855.png` `14801503.png` `14893748.png` `15016002.png` `16458622.png` `17365000.png` `17603607.png` `18989103.png` `6163682.png` `6163707.png` `6410334.png` `6427430.png` `6427450.png` `6427455.png` | 246×205 |
| `68914df7a4107796…` | `13346842.jpg` `13346853.jpg` `14801505.jpg` `14894211.jpg` `15016007.jpg` `16458627.jpg` `17364999.jpg` `17603625.jpg` `18989107.jpg` `6410246.jpg` `6410368.jpg` `6419772.jpg` | 237×213 |
| `05dc5ac1d58ad739…` | `14834172.png` `16372512.png` | 1024×621 |

## 6. Textes alternatifs écrits dans le source

Relevé **verbatim** de l'attribut `alt` des balises `<img>` du HTML source. Rien n'est complété :
un `alt` vide est reporté vide. Aucun texte alternatif n'a été rédigé pour cette archive —
décrire une photo supposerait de savoir ce qu'elle montre.

| Valeur de `alt` | Nombre d'images |
|---|---|
| `(attribut alt absent)` | 126 |
| *(vide)* | 64 |
| `Nyx du Mont Brabant` | 1 |
| `Pluton` | 1 |

Les seules valeurs non vides, recopiées **verbatim** :

| Identifiant | `alt` du source | Page qui la porte |
|---|---|---|
| `14079090.JPG` | `Nyx du Mont Brabant` | `portee-n-2017.html` |
| `16717136.jpg` | `Pluton` | `portee-p2-2019.html` |

Ce sont les **deux seuls** endroits où le site source attache un nom à une image. Rien n'est
déduit de ces `alt` ici : ils sont relevés, pas interprétés.

## 7. Vérification

Chaque fichier déposé a été relu après écriture :

- **poids** : les 192 fichiers pèsent exactement le nombre d'octets annoncé par `Content-Range`
  lors du passage de mesure. **0 écart.**
- **taille sur disque** : relue fichier par fichier après écriture, égale aux octets reçus.
  **0 écart.**
- **dimensions** : les dimensions relues sur les octets déposés sont identiques à celles
  mesurées à la sonde. **0 écart.**
- **réponse HTTP** : 200 sur les 192 téléchargements. **0 échec.**
- **somme** : le total des tailles sur disque vaut **33 694 075 octets**, égal au total mesuré avant
  téléchargement.

## 8. Échecs

**Aucun.** Les 192 identifiants recensés ont au moins une rendition qui répond 200/206, et
les 192 ont été déposés. Aucune photo recensée n'est manquante.

Pour mémoire, le seul « 404 » massif du relevé est l'**identifiant nu** sans préfixe
(`/s/cc_images/<id>.<ext>`), qui n'existe pas sur IONOS : ce n'est pas une photo perdue,
c'est une forme d'URL que le serveur ne sert pas.

## 9. Mobilier de gabarit écarté

Trois images sont servies sur presque toutes les pages et **n'appartiennent pas à l'élevage** :
ce sont des éléments du gabarit IONOS. Elles sont écartées et nommées ici pour que l'écart soit
vérifiable.

| URL | Présence | Nature |
|---|---|---|
| `//cdn.website-start.de/s/img/logo.gif` | 53 des 54 pages | logo IONOS du gabarit |
| `//cdn.website-start.de/s/img/cc/printer.gif` | 53 des 54 pages | icône « imprimer » du gabarit |
| `https://www.mtbrabant.com/proxy/static/mod/facebook/files/img/facebook-share-icon.png` | 51 des 54 pages | icône de partage Facebook du module IONOS |

**`emotionheader.jpeg` n'est pas écarté** : bien que servi par le gabarit
(`/s/img/`, hors `cc_images`), c'est une photographie de l'élevage. Il est archivé comme les
autres, sous le nom `emotionheader.jpeg`.

## 10. Total

| | |
|---|---|
| Fichiers HTML relus | 54 |
| Identifiants distincts recensés | 192 (191 `cc_images` + le bandeau `emotionheader.jpeg`) |
| Renditions sondées | 765 (383 servies, 382 en 404) |
| Fichiers déposés | 192 |
| Échecs | 0 |
| Poids total | **33 694 075 octets** (32.13 Mo) |
| Plus grande retenue | `7437996.jpg` — 1527×1080, 316 914 o |
| Plus petite retenue | `13346836.jpg` — 184×274, 7 758 o |
| Date de capture | 2026-08-23 |
