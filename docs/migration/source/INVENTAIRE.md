# Inventaire de la capture de l'ancien site — preuve de complétude

**Point d'entrée de `docs/migration/source/`.** Qui arrive dans ce dossier lit ce fichier d'abord : il
dit **ce qui est capturé**, **ce qui ne l'est pas et pourquoi**, et **à quel fichier se fier pour
quoi**. C'est le livrable de la phase d'inventaire de l'**issue #19**, opposable à **#20** et **#21**.

- **Écrit le** : 2026-08-23
- **Contrat applicable** : `docs/contracts/issue-19.md` (gelé le 2026-08-21)
- **Aucune requête réseau n'a été faite pour l'écrire.** Tous les chiffres de ce fichier sont
  recalculés depuis les octets déjà archivés dans `html/`, `photos/` et `sitemap.xml`. La méthode et
  le résultat de chaque recalcul sont au §9.

Ce fichier **ne réécrit rien**. `README.md` (passe #16), `pages/README.md` (passe #17),
`ECART-PASSE-16.md`, `html/README.md`, `html/RELEVE.md`, `outils/README.md` et
`photos/MANIFESTE.md` restent valables pour leur propre périmètre ; l'inventaire les indexe et y
renvoie.

---

## 1. La preuve de complétude, par soustraction

### `sitemap.xml` fait foi, et rien d'autre

**Aucun menu du site ne liste les 52 URL.**

| Source de la liste | URL atteintes | D'où vient le chiffre |
|---|---|---|
| Menu principal | **6** | recopié en entier dans `README.md` |
| Sous-menu « BHPL » | **29** (27 portées + `bhpl-en-france` + `littérature`) | recompté dans `html/bhpl.html` : **30** adresses `/bhpl/…` distinctes, soit les 29 entrées plus `/bhpl/` lui-même |
| Sous-menu « La meute » | **13** — alors que le `sitemap.xml` porte **17** fiches de chiens | recompté dans `html/bhpl.html` : **14** adresses `/la-meute/…` distinctes, soit les 13 fiches plus `/la-meute/` |

Cinq des 52 URL ne sont atteintes par **aucun lien d'aucune autre page archivée** : leur adresse
n'apparaît que dans leur propre fichier HTML.

| URL orpheline de lien | Vérification |
|---|---|
| `/la-meute/roxane/` | `la-meute/roxane/` n'apparaît que dans `html/chien-roxane.html` |
| `/la-meute/ray-ban/` | n'apparaît que dans `html/chien-ray-ban.html` |
| `/la-meute/youry/` | n'apparaît que dans `html/chien-youry.html` |
| `/la-meute/halan/` | n'apparaît que dans `html/chien-halan.html` |
| `/placement/` | `mtbrabant.com/placement/` n'apparaît que dans `html/placement.html` |

Par comparaison, l'adresse d'une fiche au menu — `la-meute/jango/` — apparaît dans **53 des 54**
fichiers HTML (partout sauf `bhpl-en-france.html`, dont le corps est vide). **Une capture guidée par
les menus aurait donc perdu cinq pages sans jamais s'en apercevoir.** C'est la raison mesurée pour
laquelle le `sitemap.xml` est la définition opposable des 52 (contrat §1), et pourquoi il est
archivé comme fichier source à côté de ce document.

**Ces cinq mêmes pages, et elles seules, portent aussi un `robots` à `noindex, nofollow`** dans leur
HTML — alors qu'elles sont au sitemap. Le fait est mesuré à l'**anomalie 4 du §10** ; son motif est
la question ouverte **Q23**. **Ne pas les traiter comme des pages ordinaires avant d'avoir la
réponse** : cela concerne directement la reprise (#20-#21) et les redirections (#24).

- `sitemap.xml` — **7 621 octets**, SHA-256
  `bb78eebcd0fa3d8f3b739b6fad9df1ddf49b6abcd49da033d3f78f76cc09cd1e`, **52 `<loc>`**, 52 adresses
  distinctes. *Recalculé pour ce fichier, concorde avec le relevé de référence du contrat §1.*

### La soustraction

> **52 `<loc>` du `sitemap.xml` − 52 lignes d'inventaire = 0.**

**Aucune URL du sitemap n'est sans capture. Zéro manquante.** Faite ici en confrontant chaque `<loc>`
à l'en-tête « URL source (encodée) » de chacun des `.md` du dossier, et non en recopiant un décompte
antérieur.

Le dossier porte **56 fichiers de capture `.md`** :

| | Fichiers |
|---|---|
| Rattachés à une des 52 URL du sitemap | **54** |
| Annexe, hors des 52 (§4) | **2** — `pages/bhpl-en-france.md`, `pages/politique-de-confidentialite.md` |

54 fichiers pour 52 URL : **deux URL ont deux captures**, et ce n'est ni une divergence ni une
erreur (§2 ci-dessous).

---

## 2. Le tableau des 52

Une ligne par `<loc>`, **dans l'ordre du sitemap**.

- **Octets**, **SHA-256 stable** et **U+00A0** décrivent le **document HTML** reçu, tels que
  `html/RELEVE.md` les a relevés le 2026-08-23 — et tels que ce fichier les a **recalculés** depuis
  les octets archivés (§9). Le SHA-256 **brut** n'est pas reproductible et n'est donc pas repris ici :
  il est dans `html/RELEVE.md`.
- **Conv.** : `#17 5 z.` = les cinq zones du gabarit (bandeau, contenu principal, colonne secondaire,
  colonne latérale, pied de page) ; `#16 3 z.` = trois zones, sans le bandeau ni la colonne
  secondaire (§5).
- **Fait foi pour** : la colonne qui sert à #20-#21 — quel fichier lire, et pour quoi.

| # | URL source (lisible) | HTTP | Octets | SHA-256 stable | U+00A0 | Fichier(s) de capture | Conv. | Fait foi pour |
|---|---|---|---|---|---|---|---|---|
| 1 | `https://www.mtbrabant.com/` | 200 | 47 382 | `bafe478b715e40363bdaf7692931a5de6746b4a47ff89a6b06ed0341634dd9c2` | 22 | `accueil.md` <br> `pages/accueil.md` | #16 3 z. <br> #17 5 z. | `pages/accueil.md` (5 z.) fait foi pour **tout**, notamment les libellés non coupés (« Rolex New Wave du Mont Brabant », « Poil long / Poil court »). `accueil.md` (3 z.) est la capture commitée par #16 — verdict **à recapturer avant import** |
| 2 | `https://www.mtbrabant.com/bhpl/` | 200 | 51 063 | `580718e1d17f319d43dd406c7a5f0e4e297168847d245bc10d6d83dad6d3fbc1` | 38 | `pages/bhpl.md` | #17 5 z. | l'intégralité de la page, 5 zones |
| 3 | `https://www.mtbrabant.com/bhpl/portée-a3-2025/` | 200 | 39 935 | `9b43e12b7670879af966c8f0eb5273105512d8dc8312703b4af40e0c6268eab8` | 5 | `portees/portee-a3-2025.md` | #17 5 z. | l'intégralité de la page, 5 zones |
| 4 | `https://www.mtbrabant.com/bhpl/portée-a2-2025/` | 200 | 37 011 | `64b78dec4e58f07d16eeadf3b0494b28a24d7218482ea462174b8efc4df39426` | 10 | `portees/portee-a2-2025.md` | #17 5 z. | l'intégralité de la page, 5 zones |
| 5 | `https://www.mtbrabant.com/bhpl/portée-a1-2025/` | 200 | 40 006 | `503fa054902c9ba1fef097be2cce550dff6b977aef6609b9a8078e9d1b1e20ce` | 5 | `portees/portee-a1-2025.md` | #17 5 z. | l'intégralité de la page, 5 zones |
| 6 | `https://www.mtbrabant.com/bhpl/portée-v2-2024/` | 200 | 37 985 | `b94634cef3b6436dc7db6cc15c890e6b3cae13ad59e1d8636d86baea346994d1` | 11 | `portees/portee-v2-2024.md` | #17 5 z. | l'intégralité de la page, 5 zones |
| 7 | `https://www.mtbrabant.com/bhpl/portée-v1-2024/` | 200 | 36 924 | `126ca5a96f248a76c54cf7ff7e60fe65b6bd1e4d690fb54d8c200096c58e32f7` | 6 | `portees/portee-v1-2024.md` | #17 5 z. | l'intégralité de la page, 5 zones |
| 8 | `https://www.mtbrabant.com/bhpl/portée-u3-2023/` | 200 | 38 676 | `a5f4abd87be25b65d6fdcf09d8d79830c6cd14e0e755d7f5276cdaf384374ac8` | 16 | `portees/portee-u3-2023.md` | #17 5 z. | l'intégralité de la page, 5 zones |
| 9 | `https://www.mtbrabant.com/bhpl/portée-u2-2023/` | 200 | 38 362 | `6aa8874de7a01f6e1bb29cd0ffa16c16eff6cbda1001a6f5625ff4c9c648f815` | 7 | `portees/portee-u2-2023.md` | #16 3 z. | la page moins le bandeau et la colonne secondaire. **Verdict à recapturer avant import** (coupure fantôme dans les résultats de santé de la mère). **Aucune version 5 zones n'existe** : re-dériver depuis `html/portee-u2-2023.html` |
| 10 | `https://www.mtbrabant.com/bhpl/portée-u1-2023/` | 200 | 39 297 | `2be89d7db68ba237f0b071a909a649c0d16a507b8135af6be543ddfab7e5ca47` | 7 | `portees/portee-u1-2023.md` | #17 5 z. | l'intégralité de la page, 5 zones |
| 11 | `https://www.mtbrabant.com/bhpl/portée-t-2022/` | 200 | 39 338 | `123a02d66778a5f85e5c8e581c2c52f0a6c875802b625b7fde4f841766dd5002` | 4 | `portees/portee-t-2022.md` | #17 5 z. | l'intégralité de la page, 5 zones |
| 12 | `https://www.mtbrabant.com/bhpl/portée-s2-2021/` | 200 | 40 025 | `19be1da07ffcf6850e43a9cb11256634a2821dfceb532d5f22417550ef79769a` | 24 | `portees/portee-s2-2021.md` | #17 5 z. | l'intégralité de la page, 5 zones |
| 13 | `https://www.mtbrabant.com/bhpl/portée-s1-2021/` | 200 | 39 156 | `474d3eb9456ebb611a1c9a5c688dfae851f592f3c695c1e3082d0902c12180bd` | 8 | `portees/portee-s1-2021.md` | #17 5 z. | l'intégralité de la page, 5 zones |
| 14 | `https://www.mtbrabant.com/bhpl/portée-r-2020/` | 200 | 42 244 | `a00bf8c5ee53f5edbeb7e62a027387f89d0970af5e5a663e274124b0f502c2a0` | 11 | `portees/portee-r-2020.md` | #17 5 z. | l'intégralité de la page, 5 zones |
| 15 | `https://www.mtbrabant.com/bhpl/portée-p2-2019/` | 200 | 41 605 | `f14cd0c9fcecaa4eec55b635153fc3d425276dfa2dfeecfb584d00bfb12d7a63` | 12 | `portees/portee-p2-2019.md` | #17 5 z. | l'intégralité de la page, 5 zones |
| 16 | `https://www.mtbrabant.com/bhpl/portée-p-2019/` | 200 | 39 712 | `bcace4550e55e32cb55c1818ca8e579e8f3af1adfe1c723e9c00f37012826e04` | 10 | `portees/portee-p-2019.md` | #17 5 z. | l'intégralité de la page, 5 zones |
| 17 | `https://www.mtbrabant.com/bhpl/portée-o-2018/` | 200 | 43 177 | `4484bb9545e34e832bdf92cf1b3fe96639219972bff177361aecefd512650bb1` | 13 | `portees/portee-o-2018.md` | #17 5 z. | l'intégralité de la page, 5 zones |
| 18 | `https://www.mtbrabant.com/bhpl/portée-n-2017/` | 200 | 44 548 | `1b70e80dba64408bfb1ac7f23d1684cd502915bb6275ff4eeeafedc8216219f7` | 18 | `portees/portee-n-2017.md` | #17 5 z. | l'intégralité de la page, 5 zones |
| 19 | `https://www.mtbrabant.com/bhpl/portée-m-2016/` | 200 | 39 765 | `79daaaaf80e7ee48c96c84f88363f62bd772a9cbfc2da9c80bd96c0c91fed939` | 8 | `portees/portee-m-2016.md` | #16 3 z. | la page **moins** le bandeau et la colonne secondaire. Verdict *utilisable tel quel* — `ECART-PASSE-16.md` |
| 20 | `https://www.mtbrabant.com/bhpl/portée-j-2014/` | 200 | 37 310 | `e6322355f13a7aeed37aacf402a075038e9c6a4a345b8b277376b778e6f653cc` | 7 | `portees/portee-j-2014.md` | #16 3 z. | la page **moins** le bandeau et la colonne secondaire. Verdict *utilisable tel quel* — `ECART-PASSE-16.md` |
| 21 | `https://www.mtbrabant.com/bhpl/portée-h-2012/` | 200 | 36 504 | `f708ac4c9feeb797d8065adecf26be84a52d80b0cfaf3b755228fe1624bc5f9d` | 9 | `portees/portee-h-2012.md` | #17 5 z. | l'intégralité de la page, 5 zones |
| 22 | `https://www.mtbrabant.com/bhpl/portée-g-2011/` | 200 | 37 067 | `fde726b4cf6aff7a12aadca14792b98e86bab74e6296e43c0cb46e2ee6db4f96` | 6 | `portees/portee-g-2011.md` | #17 5 z. | l'intégralité de la page, 5 zones |
| 23 | `https://www.mtbrabant.com/bhpl/portée-f-2010/` | 200 | 37 707 | `d6e0d379bbeff436ca3ccc6ea947ddd5aa1bbe5371e1e93fd6768421b782a119` | 8 | `portees/portee-f-2010.md` | #17 5 z. | l'intégralité de la page, 5 zones |
| 24 | `https://www.mtbrabant.com/bhpl/portée-e-2009/` | 200 | 37 176 | `d26dddffd07295c198749a7dc7627d238132cfe3a84f9d893e1211c516e9809a` | 6 | `portees/portee-e-2009.md` | #17 5 z. | l'intégralité de la page, 5 zones |
| 25 | `https://www.mtbrabant.com/bhpl/portée-d-2008/` | 200 | 36 638 | `b66db73fdd9115bd9c72b796f48d5e626c68172a356896a2432ab35c6f1f335a` | 5 | `portees/portee-d-2008.md` | #17 5 z. | l'intégralité de la page, 5 zones |
| 26 | `https://www.mtbrabant.com/bhpl/portée-c-2007/` | 200 | 38 791 | `7a6d25f5e2aa1304534dc02ce306fa3859a2b2963f6411c0031affb414128b79` | 11 | `portees/portee-c-2007.md` | #17 5 z. | l'intégralité de la page, 5 zones |
| 27 | `https://www.mtbrabant.com/bhpl/portée-s-2001/` | 200 | 37 143 | `2d0303e98902986c07d3a1f012d5c5a09f147104c2a4545d38d789544f2f867b` | 26 | `portees/portee-s-2001.md` | #17 5 z. | l'intégralité de la page, 5 zones |
| 28 | `https://www.mtbrabant.com/bhpl/portée-m-1996/` | 200 | 36 646 | `84b0146148c75c0a22368397bbb133c8fcc89ed5ea9b2000470f6ce4247f63c8` | 5 | `portees/portee-m-1996.md` | #17 5 z. | l'intégralité de la page, 5 zones |
| 29 | `https://www.mtbrabant.com/bhpl/portée-l-1995/` | 200 | 36 867 | `1e22012f995f1504e3e59efe2e2f2381c23b7f7474a47905adae0e0af7cbc2e6` | 28 | `portees/portee-l-1995.md` | #17 5 z. | l'intégralité de la page, 5 zones |
| 30 | `https://www.mtbrabant.com/bhpl/littérature/` | 200 | 35 541 | `791371d931b6ce607641fbee61709a718498a9f128574e5f876c13a73fcc1a77` | 1 | `pages/litterature.md` | #17 5 z. | l'intégralité de la page, 5 zones — mais le **`diywebMain` est vide** : la page source n'a **aucun contenu rédactionnel**. Voir l'annexe §4 |
| 31 | `https://www.mtbrabant.com/travail/` | 200 | 63 623 | `c181bffead4a6ef606a1a9e3c7dcd9551ede8ca8710770c08ae9cc25c1a31c74` | 136 | `travail.md` <br> `pages/travail.md` | #16 3 z. <br> #17 5 z. | `pages/travail.md` (5 z.) fait foi pour les **57 lignes de résultats** : il conserve l'association année / chien / niveau. `travail.md` (3 z.) éclate chaque cellule sur sa propre ligne — verdict **à recapturer avant import** |
| 32 | `https://www.mtbrabant.com/la-meute/` | 200 | 34 317 | `749e8e0632e01086c92b2d486beb347e7d6c0e49693c55382af8c098dba3db36` | 3 | `la-meute.md` | #16 3 z. | la page **moins** le bandeau et la colonne secondaire. Verdict *utilisable tel quel* — `ECART-PASSE-16.md` |
| 33 | `https://www.mtbrabant.com/la-meute/very-best/` | 200 | 38 657 | `3808b7cebac2d3381c933765440c92d97f22281e74ad5add5d2470107f7fbd18` | 21 | `chiens/chien-very-best.md` | #17 5 z. | l'intégralité de la page, 5 zones |
| 34 | `https://www.mtbrabant.com/la-meute/you/` | 200 | 39 115 | `c5911e74a20d82c4f7e7ba0829cc52cb0fa46a87cd6f4235a2da033eb74d9d00` | 23 | `chiens/chien-you.md` | #17 5 z. | l'intégralité de la page, 5 zones |
| 35 | `https://www.mtbrabant.com/la-meute/tesla/` | 200 | 40 936 | `3bcbba6128cc7f76822420fb988c5a64e4a70e9c678d98129c6defea4ae332f7` | 25 | `chiens/chien-tesla.md` | #17 5 z. | l'intégralité de la page, 5 zones |
| 36 | `https://www.mtbrabant.com/la-meute/roxane/` | 200 | 41 318 | `381d7434d58022df6922eecd8b2b28c8c51d566e8c2a36149c9221a08cc82cdd` | 30 | `chiens/chien-roxane.md` | #17 5 z. | l'intégralité de la page, 5 zones |
| 37 | `https://www.mtbrabant.com/la-meute/ray-ban/` | 200 | 40 813 | `dd4e2228e465742aa28cb6c7e2294e4225868a3e4fa5168f1859a6f1cc24d6a5` | 30 | `chiens/chien-ray-ban.md` | #17 5 z. | l'intégralité de la page, 5 zones |
| 38 | `https://www.mtbrabant.com/la-meute/rolex/` | 200 | 40 484 | `6f3ee50f8a3e07f32d9eae42541b86172cd892c96ed409e9b6e83adc2e3c19c1` | 20 | `chiens/chien-rolex.md` | #16 3 z. | la page **moins** le bandeau et la colonne secondaire. Verdict *utilisable tel quel* — `ECART-PASSE-16.md` |
| 39 | `https://www.mtbrabant.com/la-meute/youry/` | 200 | 38 461 | `17e9ec1af72a5c3539f051164417eab103a2f63539a642bdda177b0462119f3d` | 20 | `chiens/chien-youry.md` | #17 5 z. | l'intégralité de la page, 5 zones |
| 40 | `https://www.mtbrabant.com/la-meute/pégaz/` | 200 | 40 813 | `f95b19391dfbd066616e40a388079b4a7f744a868cc4c7060a3b539476379c4d` | 15 | `chiens/chien-pegaz.md` | #16 3 z. | la page **moins** le bandeau et la colonne secondaire. Verdict *utilisable tel quel* — `ECART-PASSE-16.md` |
| 41 | `https://www.mtbrabant.com/la-meute/jango/` | 200 | 42 564 | `f143360f3b6dc0c03c77bbf8dc1fb2305f7ed2958ea7903ab07259f0da012fe7` | 20 | `chiens/chien-jango.md` | #16 3 z. | la page **moins** le bandeau et la colonne secondaire. Verdict *utilisable tel quel* — `ECART-PASSE-16.md` |
| 42 | `https://www.mtbrabant.com/la-meute/opium/` | 200 | 41 568 | `acea49a76e0ecb072a120c1e57247cada447c02b63cc4ca5510da2f393605448` | 19 | `chiens/chien-opium.md` | #17 5 z. | l'intégralité de la page, 5 zones |
| 43 | `https://www.mtbrabant.com/la-meute/grocky/` | 200 | 40 009 | `c968e66033084fb8e294f66a139a4d248079c8115c77c8d856787a3db8551f47` | 13 | `chiens/chien-grocky.md` | #17 5 z. | l'intégralité de la page, 5 zones |
| 44 | `https://www.mtbrabant.com/la-meute/etch/` | 200 | 40 968 | `ed192603beef86f5c667fb1cda261cc37fa89b909c1841586de5b132c76b141b` | 18 | `chiens/chien-etch.md` | #16 3 z. | la page **moins** le bandeau et la colonne secondaire. Verdict *utilisable tel quel* — `ECART-PASSE-16.md` |
| 45 | `https://www.mtbrabant.com/la-meute/happy/` | 200 | 37 325 | `0502e4ab7ed2f062846d639201121e3207939d818a6152f0b2df2b96ba1f53a9` | 14 | `chiens/chien-happy.md` | #17 5 z. | l'intégralité de la page, 5 zones |
| 46 | `https://www.mtbrabant.com/la-meute/halan/` | 200 | 32 712 | `cb18e729821c4d3300419de36dbfc8669abe095098148e84ac5d6f16dae7a1db` | 1 | `chiens/chien-halan.md` | #17 5 z. | l'intégralité de la page, 5 zones |
| 47 | `https://www.mtbrabant.com/la-meute/maya/` | 200 | 35 602 | `ad42a09adc99377c49202db25130ed1208b04e49de666f0ea67addbe0cb47090` | 3 | `chiens/chien-maya.md` | #17 5 z. | l'intégralité de la page, 5 zones |
| 48 | `https://www.mtbrabant.com/la-meute/tara/` | 200 | 35 577 | `c172be4505dbbd29121830ea0f8a418248cca94a6a811ed47551c09e42e53415` | 2 | `chiens/chien-tara.md` | #17 5 z. | l'intégralité de la page, 5 zones |
| 49 | `https://www.mtbrabant.com/la-meute/gribouille/` | 200 | 36 621 | `507c076c13627bc3da7a6f659cb065d0b1dc6aaa120b82c641a71f927890482e` | 3 | `chiens/chien-gribouille.md` | #17 5 z. | l'intégralité de la page, 5 zones |
| 50 | `https://www.mtbrabant.com/placement/` | 200 | 31 205 | `3ee1c12510350f592d1c943f7c3378abffc06d795f17fddcc262a5a2fab11486` | 3 | `pages/placement.md` | #17 5 z. | l'intégralité de la page, 5 zones |
| 51 | `https://www.mtbrabant.com/contact/` | 200 | 38 319 | `89a270c585df9482c8d4fd5624ee869599f2b17c0db26c14979baae40c311267` | 7 | `pages/contact.md` | #17 5 z. | l'intégralité de la page, 5 zones |
| 52 | `https://www.mtbrabant.com/mentions-légales/` | 200 | 33 151 | `75f55ffea4f64f18312262b2f8c54ee00c6ebff777fb07da8a73ad368df49d4b` | 1 | `pages/mentions-legales.md` | #17 5 z. | l'intégralité de la page, 5 zones |

### Les deux URL capturées deux fois — indexées, jamais supprimées

| URL | Passe #16 (3 zones) | Passe #17 (5 zones) |
|---|---|---|
| `https://www.mtbrabant.com/` | `accueil.md` | `pages/accueil.md` |
| `https://www.mtbrabant.com/travail/` | `travail.md` | `pages/travail.md` |

Même URL, **même jour** (2026-08-20), **deux découpages de zones** du même HTML. Le contrat §6 le
tranche : **aucune n'est réécrite, aucune n'est supprimée — on les indexe.** C'est fait, sur la même
ligne du tableau, avec ce que chacune conserve. **Un doublon indexé cesse d'être une incohérence.**

Deux constats déposés, qui ne se contredisent pas et dont aucun ne réécrit l'autre :

- `pages/README.md` (passe #17) écrit que pour une **ligne de tableau** — un résultat de travail,
  année / chien / niveau — c'est `pages/travail.md` qu'il faut lire, parce qu'il conserve
  l'association ; et que **pour la prose**, la version du dossier parent reste la référence, étant
  celle que la passe #16 a commitée.
- `ECART-PASSE-16.md`, écrit après et **à partir du HTML archivé**, mesure que `accueil.md` porte
  **quatre coupures de ligne que le document n'a pas**, dont une **à l'intérieur de « Rolex New Wave
  du Mont Brabant »**, et que `travail.md` **éclate 67 lignes de tableau**, dont 57 lignes de
  résultats. Verdict pour les deux : **à recapturer avant import**.

Ce que l'inventaire ajoute, et qui ne contredit ni l'un ni l'autre : des deux captures de `/` et des
deux captures de `/travail/`, **seules les versions 5 zones redonnent le document au caractère près**
quand on les re-dérive depuis `html/` (§9).

### Les U+00A0 déclarées par les dix fichiers de la passe #16

La colonne « U+00A0 » du tableau compte les insécables **du document**. Les dix `.md` de la passe #16
déclarent, eux, les insécables **de leur propre fichier** — un chiffre **exact pour le fichier tel
qu'il est**, et plus petit. Les deux ne se comparent pas ; les voici côte à côte pour qu'on ne les
prenne jamais l'un pour l'autre. **Aucun n'est corrigé.**

| Fichier (passe #16) | Déclarées dans le `.md` | Du document (colonne du tableau) |
|---|---|---|
| `accueil.md` | 6 | 22 |
| `la-meute.md` | 0 | 3 |
| `travail.md` | 47 | 136 |
| `portees/portee-j-2014.md` | 1 | 7 |
| `portees/portee-m-2016.md` | 1 | 8 |
| `portees/portee-u2-2023.md` | 2 | 7 |
| `chiens/chien-etch.md` | 5 | 18 |
| `chiens/chien-jango.md` | 4 | 20 |
| `chiens/chien-pegaz.md` | 3 | 15 |
| `chiens/chien-rolex.md` | 3 | 20 |

L'écart vient de la matière absente — lignes ne contenant qu'une insécable, bandeau, colonne
secondaire — et non d'un désaccord de comptage : `ECART-PASSE-16.md` le chiffre ligne par ligne.

---

## 3. Répartition des 52 par convention

| | URL |
|---|---|
| Capturées **en 5 zones** seulement (passe #17, convention gelée) | **42** |
| Capturées **en 3 zones** seulement (passe #16) | **8** |
| Capturées **dans les deux** conventions | **2** — `/` et `/travail/` |

Les huit URL qui n'existent qu'en 3 zones :
`/bhpl/portée-u2-2023/` · `/bhpl/portée-m-2016/` · `/bhpl/portée-j-2014/` · `/la-meute/` ·
`/la-meute/rolex/` · `/la-meute/pégaz/` · `/la-meute/jango/` · `/la-meute/etch/`.

**Une seule d'entre elles porte un verdict « à recapturer » sans version 5 zones de secours :
`/bhpl/portée-u2-2023/`.** Pour celle-là, et pour elle seule, la re-dérivation depuis
`html/portee-u2-2023.html` est le seul recours (§5).

---

## 4. Annexe — les URL hors des 52, nommées et jamais comblées

| URL | Réponse | Ce que dit le fichier |
|---|---|---|
| `https://www.mtbrabant.com/bhpl/bhpl-en-france/` | **302** — protégée par mot de passe sur l'ancien site | **53ᵉ URL**, hors sitemap. `pages/bhpl-en-france.md` : corps de **0 octet**, `Location:` vers le formulaire de mot de passe du gabarit. La seule chose que le site dise d'elle sans mot de passe est **son libellé de menu**. |
| `https://www.mtbrabant.com/politique-de-confidentialite/` | **404** — absente du sitemap et de tout menu | `pages/politique-de-confidentialite.md` : **trois adresses testées**, trois 404. IONOS rend malgré tout une page de gabarit complète (31 462 octets), sans aucun contenu rédactionnel. |
| `https://www.mtbrabant.com/bhpl/littérature/` | **200**, mais `diywebMain` **vide** | `pages/litterature.md` : la page source n'a **aucun contenu rédactionnel** — ni titre de livre, ni référence, ni lien de lecture. Cette URL est, elle, **dans les 52** (ligne 30 du tableau) : c'est son contenu qui est vide, pas sa capture. |

**Ces trois fichiers sont D11 tenue, pas D4 manquée.** Toute passe future qui les « remplirait »
violerait le contrat. Ce n'est **pas un oubli à réparer** : c'est le constat, archivé, que le site
source ne dit rien de plus.

**`/sitemap/`** — la page HTML « Plan du site » liée depuis le pied de page — **n'est pas dans les
52** : c'est une page de gabarit IONOS. Constat tranché par le contrat §1, à ne pas rouvrir.

---

## 5. Les deux conventions, et où en est la mesure

Le dossier porte **deux conventions de capture**, et elles ne capturent pas la même chose :

| | Passe #16 (racine) | Passe #17 (`pages/`, et tous les fichiers du 2026-08-23) |
|---|---|---|
| Zones découpées | **3** | **5** |
| `diywebEmotionHeader` (bandeau) | perdu | présent |
| `diywebSecondary` (colonne secondaire) | perdu | présente |
| `<title>` du document | non relevé | relevé |
| Cellules d'une ligne de tableau | éclatées, une par ligne | **associées** par ` \| ` |
| Ligne ne contenant qu'une U+00A0 | avalée | conservée |

**La convention de référence est celle de la passe #17 (5 zones)** — décision gelée du contrat §3,
prise parce qu'elle est **mesurément plus fidèle**, pas par goût.

**L'écart des dix fichiers de la passe #16 est déclaré, il n'est pas corrigé.** Il est chiffré
fichier par fichier dans **`ECART-PASSE-16.md`** — zones perdues, insécables, lignes perdues,
associations de tableau éclatées, adresses perdues, et un verdict par fichier. Le chiffre qui décide,
et le seul repris ici :

> **Trois des dix fichiers de la passe #16 sont « à recapturer avant import » :**
> **`accueil.md`**, **`travail.md`**, **`portees/portee-u2-2023.md`**.
> **Les sept autres sont « utilisables tels quels »** : `la-meute.md`, `portees/portee-m-2016.md`,
> `portees/portee-j-2014.md`, `chiens/chien-jango.md`, `chiens/chien-pegaz.md`,
> `chiens/chien-etch.md`, `chiens/chien-rolex.md`.

Pour le détail chiffré et les extraits, **lire `ECART-PASSE-16.md`** : il n'est pas recopié ici.

---

## 6. Renvois — le reste du dossier

| Fichier | Ce qu'il règle |
|---|---|
| `html/README.md` | **La règle de préséance** : le `.md` est ce qu'on lit et ce qu'on importe, le `.html` est ce qui **tranche** en cas de doute ; le `.html` n'est **jamais** importé tel quel. Porte aussi l'avertissement sur la clé d'API Google Maps d'IONOS visible en clair dans le HTML archivé — **ce n'est pas un secret du projet**, mais l'analyse de secrets de GitHub peut la signaler. |
| `html/RELEVE.md` | La **mesure** des 54 fichiers HTML : code HTTP, octets, SHA brut, SHA stable, insécables. La matière première du tableau du §2. |
| `outils/README.md` | L'outil de **réduction**, `reduire.py` et `verifier_concordance.py` : sans dépendance et **sans réseau**, donc rejouable quand le site source aura disparu. Déclare aussi la seule divergence connue entre l'outil et `pages/travail.md` (la cellule « Road Trip », la seule des 207 qui contienne un lien). |
| `photos/MANIFESTE.md` | L'**archive photo** — 192 fichiers, provenance, rendition, dimensions, empreinte, textes alternatifs du source — **et la dette T12**, écrite en tête du fichier parce que c'est le manifeste que lira la personne qui téléversera. |
| `README.md` | L'index de la **passe #16** (10 URL) : méthode, navigation commune, et le constat que l'encart « Chiots nés le 29/06/2026 / Tous réservés » est un **encart global de site**, jamais la disponibilité de la portée affichée. Toujours valable pour son périmètre. |
| `pages/README.md` | L'index de la **passe #17** (pages libres) : les cinq zones, le SHA non reproductible, le sous-menu « La meute », et les **écarts relevés entre les mentions légales du site et `BRIEF.md` §7** (adresse, téléphone, accent du nom) — relevés, non corrigés. Toujours valable pour son périmètre. |
| `ECART-PASSE-16.md` | La mesure de ce que la passe #16 a perdu, et le verdict par fichier (§5). |

---

## 7. Ce que cette capture laisse à #20 et #21

Sept points, tous vérifiables dans les fichiers de ce dossier.

### 7.1 T12 est un prérequis dur, pas une recommandation

**WordPress ne découpe et ne convertit une image qu'au téléversement.** Les **192 photographies** de
`photos/` (**33 694 075 octets**, recomptés pour ce fichier) devront donc être téléversées **après**
le module d'images de l'issue **#8** — sinon aucune n'aura ses formats modernes ni ses sous-tailles,
et **tout le stock sera à régénérer**. C'est écrit en tête de `photos/MANIFESTE.md`, à l'endroit que
lira la personne qui téléverse.

### 7.2 La « pleine résolution » du brief §7 n'est pas atteignable depuis le site public

**IONOS ne sert aucun original.** `photos/MANIFESTE.md` §3 le mesure : sur les 383 renditions
servies, **4 seulement** dépassent 1 024 px de grand côté, 49 s'arrêtent **exactement** à 1 024 px, et
`thumb_` plafonne à 150 px. Ce tassement est la signature d'un redimensionnement au téléversement.

Ce que l'archive contient est donc **la plus grande rendition publiquement servie**, pas l'original.
Si les originaux existent, ils sont dans le gestionnaire de médias IONOS de l'éleveuse, **hors de
portée d'un téléchargement public** — et il faudrait les exporter **avant la résiliation de
l'abonnement**. **C'est une question pour l'éleveuse, pas un trou à combler.**

### 7.3 Trois fichiers sont à recapturer avant import

`accueil.md`, `travail.md`, `portees/portee-u2-2023.md` — voir §5 et `ECART-PASSE-16.md`. Pour les
deux premiers, la version 5 zones existe déjà à côté (`pages/accueil.md`, `pages/travail.md`) ; pour
le troisième, **aucune version 5 zones n'existe** et la re-dérivation depuis
`html/portee-u2-2023.html` est le seul recours. **Les sept autres sont utilisables tels quels.**

### 7.4 `docs/migration/redirections.md` est périmé — dette T38

Il annonce **« État : 7 URL sur 52 »** et, dans sa dernière section, « page Travail **non livrée**
(#17) » et « accueil livré, mais son **contenu** n'est pas repris ».

**Ce fichier est hors de l'empreinte d'écriture de l'issue #19 (contrat §9) : il n'a donc pas été
corrigé.** C'est dit ici pour que le prochain lecteur ne croie pas l'epic non commencée. La carte des
301 attendue par **D5** reste à écrire à partir des 52 lignes du §2.

### 7.5 T30 — la page « La meute » n'existe que dans la base de développement

Sur l'installation de l'éleveuse, la page n'existera pas. **La reprise devra la créer.**

### 7.6 Deux vidéos sont intégrées dans des pages de portée

Relevé dans le HTML archivé, dans le **contenu principal** (`diywebMain`) des deux pages, et présent
dans les `.md` correspondants sous forme de marqueur `[IFRAME]` :

| Page | `src` de l'`<iframe>` |
|---|---|
| `/bhpl/portée-c-2007/` | `//www.youtube.com/embed/JmLjWaCfQFQ?fs=1&wmode=opaque&rel=0` |
| `/bhpl/portée-o-2018/` | `//www.youtube.com/embed/xtt2xQW0poE?fs=1&wmode=opaque&rel=0` |

Ce sont les **deux seules** intégrations vidéo de tout le site archivé. Elles sont signalées parce
qu'elles se heurtent frontalement à la règle transverse « **zéro requête navigateur vers un domaine
tiers** » de `CLAUDE.md` : **rien n'est décidé ici**, mais #20-#21 ne peut pas les rencontrer par
surprise.

### 7.7 Le site source n'énonce ni disponibilité de portée, ni statut de chien

Rappel du contrat §7, parce que c'est le trou que la reprise sera tentée de combler :

- **Aucune page de portée n'énonce sa disponibilité.** L'encart « Chiots nés le 29/06/2026 / Tous
  réservés / Contacter nous au 0680505619 » est un **encart global de site**, identique sur toutes les
  pages, **y compris sur une portée de 2016**. Ne jamais le lire comme la disponibilité de la portée
  affichée.
- **Aucune fiche de chien n'énonce un statut.** Les seules formulations approchantes sont sur les
  pages d'index (« Femelle reproductrice » sur l'accueil, « Nos retraités et disparus » sur
  `/la-meute/`, qui réunit deux statuts du modèle en un seul groupe).

---

## 8. Quatre questions ouvertes — documentées, jamais tranchées

### Q12 — « truffe » et « cavage » sont-ils la même chose pour l'éleveuse ?

Deux constats, aucun rapprochement :

- Le mot **« truffe » n'apparaît nulle part** sur le site source — vérifié sur la totalité des
  54 fichiers HTML archivés, pas seulement sur la page Travail (`ECART-PASSE-16.md`).
- La page Travail porte une ligne **`Cavage`**, recopiée verbatim :
  `2018 - ♀<U+00A0>H'Alix du Domaine de Drenthe :<U+00A0>Cavage Classe B ChF - Prop. Ferrari`
  (les insécables sont notées `<U+00A0>` pour cette citation ; elles sont littérales dans les fichiers).

**Le site range lui-même Cavage sous « Autres disciplines » : la capture recopie ce classement, elle
ne le déduit pas.** Les deux mots ne sont pas rapprochés ici, et ne doivent pas l'être sans elle.

### Q14 — « Autres disciplines » : quatre disciplines, ou une rubrique fourre-tout ?

Les quatre lignes sont capturées **telles qu'affichées, sous leur intitulé de section source** —
`Autres disciplines :` — dans les deux conventions, mot pour mot et dans le même ordre :
`Cavage` · `Agility` · `Brevet Maitre Chien Drogue` · `Qual. Chien de sauvetage` — recopiés
tels quels, **aucune abréviation n'est développée**.

Constat de forme relevé et **non interprété** (`ECART-PASSE-16.md`) : dans `html/travail.html`,
`Autres disciplines :` est un `<p>` ordinaire et les quatre lignes sont des `<li>`, tandis que les
sept disciplines RING, IGP (RCI), Mondioring, Obéissance, Sauvetage, Pistage et Recherche Utilitaire
sont des **cellules de tableau**. Les quatre lignes ne sont donc pas structurées comme les sept
autres. **Cela ne tranche pas Q14** : le site ne dit nulle part si « Autres disciplines » est une
discipline, une rubrique, ou les deux.

### Q18 — deux points GPS distants d'environ 2 km, et rien ne dit lequel est l'élevage

L'ancien site porte **deux `<iframe>` Google Maps encodant deux points distincts**. **Les deux sont
capturées, aucune n'est désignée.** Ce que l'inventaire ajoute — relevé sur les 54 fichiers HTML
archivés, et personne ne l'avait encore fait à l'échelle du site : **où chacune apparaît**.

| Point encodé (`q=`) | Zoom | Pages qui la portent | Zone du gabarit |
|---|---|---|---|
| `43.533404, 6.248086` | **16** | **51 des 52 URL du sitemap.** La seule qui ne la porte pas est **l'accueil** (`html/accueil.html` ne contient **aucune** `<iframe>`) | `diywebSecondary` et `diywebSidebar` — la colonne du gabarit, servie deux fois pour deux largeurs d'écran |
| `43.514689, 6.242809` | **10** | **une seule page : `/contact/`** | `diywebMain` — le contenu principal de la page |

Autrement dit : la carte au zoom 16 est celle que **le gabarit** répète sur presque tout le site ; la
carte au zoom 10 apparaît **une seule fois**, dans le **contenu rédigé** de la page Contact, qui porte
donc **les deux**.

**Aucune conclusion n'est tirée de cette répartition.** Elle est relevée parce qu'elle est factuelle
et vérifiable ; ce qu'elle veut dire — s'il y en a un — appartient à l'éleveuse.

### Q23 — cinq pages désindexées par balise, et le site ne dit pas pourquoi

Quatre fiches de chiens — **`halan`, `ray-ban`, `roxane`, `youry`** — et la page **`placement`**
portent un `robots` à `noindex, nofollow` que les 48 autres pages n'ont pas. Ce sont **exactement**
les cinq pages qu'aucun menu ne lie, et elles sont **pourtant** au `sitemap.xml`.

La mesure complète, le comptage des 58 balises et les trois faits qui se recoupent sont à l'**anomalie
4 du §10** ; elle n'est pas recopiée ici.

**La question posée à l'éleveuse** : *pourquoi ces cinq pages-là sont-elles à la fois retirées des
menus et marquées « ne pas indexer », alors que le plan du site les déclare ? Faut-il que le nouveau
site les reprenne — et si oui, visibles dans les menus, et indexées ou non ?*

**Aucune hypothèse n'est avancée** — ni chiens vendus, ni chiens retirés, ni page obsolète. Le site
source énonce le **fait**, jamais son **motif**, et le motif est un fait d'élevage. Comme pour Q18,
ce qui est relevé ici est mesuré et vérifiable ; ce que cela veut dire appartient à l'éleveuse.

---

## 9. Vérification

Rien de ce fichier n'est repris de confiance d'un fichier antérieur. Voici comment chaque chiffre a
été établi, et le résultat.

**Aucune requête réseau.** Tous les recalculs partent des octets archivés dans le dépôt.

| Contrôle | Méthode | Résultat |
|---|---|---|
| Complétude | Chaque `<loc>` du `sitemap.xml` confronté à l'en-tête « URL source (encodée) » de chacun des `.md` du dossier | **52 `<loc>`, 52 lignes, 0 manquante.** 54 fichiers rattachés aux 52 (2 doublons), 2 fichiers d'annexe hors des 52 |
| `sitemap.xml` | Taille et SHA-256 recalculés | **7 621 o**, `bb78eebc…cd1e`, **52 `<loc>`**, 52 distinctes — concorde avec le contrat §1 |
| `html/RELEVE.md` | Les **4 colonnes mesurables** des **54** lignes recalculées depuis les fichiers de `html/` : octets, SHA-256 brut, SHA-256 **stable** (avec la normalisation `t=` du §3.4), insécables du HTML | **216 valeurs recalculées, 0 écart.** Poids total de `html/` recompté : **2 079 221 octets**, 54 fichiers — concorde |
| Fidélité des captures 5 zones | Les **38** `.md` qui déclarent leur HTML archivé re-dérivés par `outils/reduire.py` depuis `html/`, sans réseau, et comparés **au caractère près** au corps commité | **38 identiques sur 38, 0 divergence** |
| Fidélité des 6 `.md` de la passe #17 antérieurs à `html/` | `outils/verifier_concordance.py` (5 zones × 6 pages) | **29 zones identiques sur 30.** La 30ᵉ est la divergence **déjà déclarée** dans `outils/README.md` : la cellule « Road Trip » de `pages/travail.md`, la seule des 207 cellules du tableau qui contienne un lien |
| En-têtes des `.md` contre `html/RELEVE.md` | Taille du HTML reçu et SHA-256 stable de chaque en-tête confrontés à la ligne correspondante du relevé | **Concordance sur les 38 fichiers qui portent les deux champs, 0 écart.** Les 16 autres — les 10 de la passe #16 et les 6 `.md` de `pages/` antérieurs à `html/` — ne portent pas de champ « SHA-256 stable » : leur en-tête est d'un format antérieur au contrat §3.3. Constat, pas écart. Sur les tailles seules, **52 des 54 concordent à l'octet** ; les 2 exceptions sont les deux captures de l'accueil (anomalie 1) |
| Insécables | Les 10 comptes déclarés par les `.md` de la passe #16 recomptés **caractère par caractère** dans les fichiers eux-mêmes ; les comptes du document repris de `html/RELEVE.md`, lui-même recalculé ci-dessus | **Les 10 comptes déclarés sont exacts pour le fichier tel qu'il est.** Le tableau du §2 donne le compte du document ; les deux sont mis côte à côte au §2 |
| Menus | Adresses `href` distinctes recomptées dans `html/bhpl.html` ; présence de chaque adresse orpheline recherchée dans les **54** fichiers HTML | Sous-menu BHPL **29**, sous-menu La meute **13** ; `roxane`, `ray-ban`, `youry`, `halan` et `placement` n'apparaissent **que dans leur propre fichier** ; `jango` apparaît dans **53 des 54** |
| Balises `robots` (Q23) | Toutes les balises `<meta name="robots">` extraites du `<head>` des **54** fichiers HTML, avec leur **contenu** et leur **position en octets**, puis groupées ; présence des 5 URL concernées recherchée dans `sitemap.xml` | **58 balises** : 48 fichiers à une seule (`index,follow`), **5 à deux** (`noindex, nofollow` en tête de `<head>`, puis `index,follow`), 1 sans aucune (`bhpl-en-france`, 302 vide). Les 5 sont **les mêmes** que les 5 orphelines de la ligne « Menus », et **les 5 sont au `sitemap.xml`**. Anomalie 4 du §10 |
| Cartes (Q18) | Toutes les balises `<iframe>` extraites des 54 fichiers HTML, groupées par `src` ; zone d'appartenance relevée dans les `.md` correspondants | **4 `src` distincts** : la carte zoom 16 (51 pages), la carte zoom 10 (1 page, `/contact/`), et 2 vidéos YouTube (`portée-c-2007`, `portée-o-2018`) |
| Photos | Fichiers de `photos/` recomptés et repesés | **192 fichiers**, **33 694 075 octets** — concorde avec le total du §11 de `photos/MANIFESTE.md` |

### Trois enregistrements vérifiés champ par champ

Au-delà des comptages, trois lignes du tableau du §2 ont été reprises valeur par valeur contre les
octets archivés :

1. **Ligne 3 — `/bhpl/portée-a3-2025/`** : 39 935 o, SHA stable `9b43e12b…eab8`, 5 insécables,
   `portees/portee-a3-2025.md`, 5 zones. Les quatre valeurs recalculées depuis
   `html/portee-a3-2025.html` ; corps du `.md` re-dérivé et **identique au caractère près**.
2. **Ligne 31 — `/travail/`** : 63 623 o, SHA stable `c181bffe…1c74`, 136 insécables, deux captures.
   Recalculées ; `pages/travail.md` re-dérivé et confronté zone par zone — **4 zones identiques sur
   5**, la cinquième portant la seule divergence déjà déclarée (cellule « Road Trip »).
3. **Ligne 51 — `/contact/`** : 38 319 o, SHA stable `89a270c5…1267`, 7 insécables,
   `pages/contact.md`, 5 zones. Recalculées ; corps re-dérivé et **identique au caractère près** ;
   les deux `<iframe>` de la page retrouvées, l'une en `diywebMain`, l'autre en
   `diywebSecondary`/`diywebSidebar`.

---

## 10. Anomalies constatées

Relevées, **jamais corrigées**. *Ce qui semble faux dans ce dossier **est** ce que le site affiche.*
Les anomalies de contenu déjà déposées ailleurs ne sont pas recopiées ici : `pages/README.md` porte
les écarts des mentions légales (adresse sans numéro, téléphone à neuf chiffres, `tel:-680505619`,
nom avec et sans accent) ; `ECART-PASSE-16.md` porte les deux liens « Lof Select » du site source
dont le libellé est réparti sur deux chiens différents.

1. **Trois tailles différentes pour la page d'accueil, dont deux le même jour.** `accueil.md`
   déclare **47 384 o** (20/08), `pages/accueil.md` **47 385 o** (20/08), et le fichier archivé pèse
   **47 382 o** (23/08) — recompté pour ce document. La comparaison mot à mot ne montre **aucune
   différence de texte** et le rendu servi est daté du 15/08, antérieur aux trois requêtes. L'origine
   des deux ou trois octets n'a pas pu être établie depuis l'archive seule : les octets du 20 août
   n'ont pas été conservés. **Aucune ligne de contenu n'est en jeu.** Les **52 autres** fichiers de
   capture rattachés aux 52 URL déclarent une taille identique à l'octet à celle du HTML archivé.
   Déjà déposée comme anomalie 2 d'`ECART-PASSE-16.md`.

2. **Six `.md` de la passe #17 ne portent pas de champ « SHA-256 stable » dans leur en-tête**, leur
   format étant antérieur au contrat §3.3 : `bhpl`, `litterature`, `placement`, `mentions-legales`,
   `accueil` et `travail`. Pour les quatre premiers, la valeur est dans le tableau d'empreintes de
   `pages/README.md`. Pour **`pages/accueil.md` et `pages/travail.md`, elle n'est ni dans l'en-tête,
   ni dans cet index-là** : ces deux fichiers n'appartenaient pas au périmètre de cette passe. Elle
   est, pour l'URL, dans `html/RELEVE.md` et dans le tableau du §2. Constat, pas défaut : aucun
   fichier n'est réécrit pour l'ajouter.

3. **`la-meute.md`, lu seul, ne contient aucun lien vers une fiche de chien.** Son contenu principal
   tient en un titre, une image et une légende ; les 13 liens vers les fiches sont dans la colonne
   secondaire, que la passe #16 ne découpe pas. **Ce n'est pas une perte** — les 17 adresses sont au
   `sitemap.xml` et dans le tableau du §2 — mais c'est un piège pour qui lirait ce fichier comme
   l'index de la meute. Déjà déposé dans `ECART-PASSE-16.md`.

4. **Cinq pages portent, en plus de la balise commune, un `robots` à `noindex, nofollow` — et le
   site source se contredit deux fois à leur sujet.** Relevé sur les 54 fichiers de `html/`,
   **jamais interprété**.

   | Fichier de `html/` | URL | Au `sitemap.xml` ? | Balises `name="robots"` du `<head>` |
   |---|---|---|---|
   | `chien-halan.html` | `/la-meute/halan/` | **oui** | `noindex, nofollow` **puis** `index,follow` |
   | `chien-ray-ban.html` | `/la-meute/ray-ban/` | **oui** | `noindex, nofollow` **puis** `index,follow` |
   | `chien-roxane.html` | `/la-meute/roxane/` | **oui** | `noindex, nofollow` **puis** `index,follow` |
   | `chien-youry.html` | `/la-meute/youry/` | **oui** | `noindex, nofollow` **puis** `index,follow` |
   | `placement.html` | `/placement/` | **oui** | `noindex, nofollow` **puis** `index,follow` |

   Comptage exhaustif des **58** balises `name="robots"` des 54 fichiers : **48 fichiers** n'en
   portent **qu'une**, `index,follow` ; les **5 ci-dessus** en portent **deux** ;
   `bhpl-en-france.html` n'en porte **aucune** — c'est le 302 au corps vide (0 octet), il n'a pas de
   `<head>`. 48 + 5×2 = 58, et 48 + 5 + 1 = 54 : aucun fichier n'échappe au comptage.

   Trois faits mesurés, et **c'est leur recoupement qui fait l'anomalie** :

   1. **Ce sont exactement les cinq pages orphelines des menus.** La ligne « Menus » du §9 mesure
      déjà, sans en tirer de conclusion, que `roxane`, `ray-ban`, `youry`, `halan` et `placement`
      « n'apparaissent que dans leur propre fichier ». Les **cinq mêmes**, ni une de plus, ni une de
      moins. Aucune page indexable n'est orpheline, aucune orpheline n'est sans `noindex`.
   2. **Les cinq sont pourtant au `sitemap.xml`**, donc dans les 52 — vérifié `<loc>` par `<loc>`.
      Le site **demande** leur indexation par le sitemap et **l'interdit** par la balise.
   3. **La contradiction est aussi à l'intérieur d'un même `<head>`.** Les deux balises n'y sont pas
      au même endroit : le `noindex, nofollow` est au **début du `<head>`** (offset ~272 o),
      immédiatement après `<meta name="generator" content="IONOS MyWebsite"/>` ; l'`index,follow`
      est **bien plus loin** (offset ~1 900 à 2 200 o), dans le bloc qui porte les `keywords` et la
      `description` propres à la page — et c'est à **cette** position que les 48 autres fichiers
      portent leur unique `index,follow`. Autrement dit : le `noindex, nofollow` est un **ajout** à
      une position que les autres pages n'utilisent pas. **On n'en conclut pas laquelle des deux
      ferait foi pour un moteur de recherche** : l'archive ne le dit pas.

   **Ce que cette anomalie coûte si elle reste tue.** `html/README.md` §1 pose que le `.md` est ce
   qu'on lit et ce qu'on importe, le `.html` ne tranchant qu'en cas de doute — et personne n'a de
   doute sur une balise dont il ignore l'existence. #20-#21 importerait quatre fiches de chiens et la
   page Placement comme des pages ordinaires, et #24 les redirigerait en 301 vers des pages
   indexables. **Une intention éditoriale que le site exprime deux fois serait effacée par
   omission** — la forme exacte du défaut de la décision 46 d'`ETAT.md` (« un écart non écrit n'est
   imputable à personne »).

   **Cause structurelle, et c'est elle qui explique le silence** : la convention des cinq zones
   (contrat §3.1, `outils/reduire.py`) ne découpe que le `<body>`. **Du `<head>`, la réduction ne
   retient que le `<title>`** ; aucune autre métadonnée ne peut atteindre un `.md`. La limite est
   désormais déclarée dans `html/README.md` §4 et au contrat §3.1 bis.

   **Aucune explication n'est avancée.** Pourquoi ces cinq pages précises sont désindexées —
   chiens vendus, chiens retirés, page obsolète, autre chose — est un **fait d'élevage que seule
   l'éleveuse connaît**. Rangé en **Q23**, §11 ci-dessous.

---

## 11. Questions pour l'éleveuse

**Une** question nouvelle est née de la relecture de la capture — **Q23**, ci-dessous : elle n'était
pas là à la première rédaction de ce document, qui affirmait qu'aucune question nouvelle n'en
naîtrait. Elle ne vient pas d'une valeur illisible ou ambiguë, mais d'une **métadonnée que la
convention des cinq zones ne pouvait pas voir** (§10, anomalie 4). Les autres questions que
l'inventaire croise sont déjà posées ailleurs et **restent posées** :

| Question | Où elle est posée |
|---|---|
| **Q12** — « truffe » et « cavage » | §8 ci-dessus, contrat §7 |
| **Q14** — « Autres disciplines » : disciplines ou rubrique ? | §8 ci-dessus, contrat §7 |
| **Q18** — lequel des deux points GPS est l'élevage ? | §8 ci-dessus, contrat §7 |
| **Q23** — pourquoi les fiches de `halan`, `ray-ban`, `roxane`, `youry` et la page `placement` sont-elles les **cinq seules** à porter `noindex, nofollow` et les **cinq seules** absentes des menus, alors qu'elles sont au `sitemap.xml` ? **Faut-il que le nouveau site les reprenne, et si oui, indexées ou non ?** | §10, anomalie 4 |
| Le **mot de passe** de `/bhpl/bhpl-en-france/`, ou le constat que la page n'a pas à être reprise — et ce que devient alors son entrée de menu | `pages/bhpl-en-france.md` |
| Les **originaux pleine définition** des photographies existent-ils dans le gestionnaire de médias IONOS, et peuvent-ils être exportés **avant la résiliation de l'abonnement** ? | §7.2 ci-dessus, `photos/MANIFESTE.md` §3 |
