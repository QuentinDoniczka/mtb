# Relevé de capture du HTML brut — 54 URL

Ce fichier est la **mesure** de ce que contient `docs/migration/source/html/`. Il ne décrit pas le
contenu des pages : il décrit les octets reçus. La phase d'inventaire de l'issue #19 le consomme tel
quel ; l'outil `../outils/reduire.py --entete` y lit les valeurs de l'en-tête §3.3 des `.md`.

- **Capturée le** : 2026-08-23
- **Outil** : `curl` sans `-L` (les redirections ne sont **pas** suivies), une requête à la fois,
  1,5 s de pause entre deux requêtes.
- **Périmètre** : les **52 `<loc>` du `sitemap.xml`** archivé à côté (`../sitemap.xml`), plus les
  **2 URL hors sitemap** du §2 du contrat — `bhpl-en-france` (302) et
  `politique-de-confidentialite` (404).
- **Poids total de `html/`** : **2 079 221 octets** (54 fichiers).

## Comment lire les deux colonnes de condensé

Le HTML servi par IONOS embarque l'**époque Unix de la requête** dans les URL de `all.css` /
`all.js`. Deux requêtes donnent **la même taille et un SHA-256 brut différent** : le condensé brut
vaut pour la requête de sa ligne, jamais pour le document. La colonne **SHA-256 stable** ne dépend
que du contenu et se recalcule ainsi, et seulement ainsi :

```
sha256( HTML_reçu  avec  s/(929224983(&amp;|&)t=)[0-9]{10}/\1EPOCH/g )
```

**Vérifié le 2026-08-23 sur `/placement/`**, deux requêtes à quatre secondes d'écart : même taille
(31 205 o), SHA brut `3ce1f62d…` puis `91daf6e8…`, **SHA stable identique**
`3ee1c12510350f592d1c943f7c3378abffc06d795f17fddcc262a5a2fab11486` — et cette valeur est celle que
la passe #17 avait relevée le 2026-08-20. Les deux seuls octets qui diffèrent sont les horodatages
`t=1787498114` / `t=1787498118`.

## Les deux colonnes d'espaces insécables

- **U+00A0** : le compte des U+00A0 **littérales du HTML reçu**.
- **U+00A0 du corps réduit** : le compte des U+00A0 du texte que rend `../outils/reduire.py`.

**Les deux colonnes sont égales sur les 54 fichiers** — 775 des deux côtés, tous documents
confondus. Aucune insécable du HTML n'est perdue par la réduction. C'est exactement l'écart qui
avait fait perdre 16 insécables à la passe #16 sur la seule page d'accueil (contrat §3).

Les entités `&nbsp;` du document, elles, ne sont **pas** comptées dans ces colonnes : il y en a
**2 par page servie** (106 au total), toutes dans les ouvreurs de sous-menu
(`<span class="diyfeDropDownSubOpener">&nbsp;</span>`) de la navigation, donc **hors des cinq zones**.

## Deux URL n'ont pas de corps de page

| Slug | Ce qui a été reçu |
|---|---|
| `bhpl-en-france.html` | **302 Moved Temporarily**, corps **vide (0 octet)**, en-tête `Location: https://www.mtbrabant.com/protected/?comeFrom=%2Fbhpl%2Fbhpl-en-france%2F`. Le fichier archivé est donc vide : **c'est la donnée**, pas un échec de capture. Son SHA est celui de la chaîne vide. |
| `politique-de-confidentialite.html` | **404 Not Found**, mais avec un **corps de 31 462 octets** : IONOS rend une page de gabarit complète pour une adresse inexistante. Le `.html` est archivé tel quel ; il ne contient aucun contenu rédactionnel. |

## Le HTML servi est du cache, daté d'avant la capture

Chaque page servie se termine par un commentaire `<!-- rendered at … -->`. Sur les 53 fichiers non
vides, ces dates s'échelonnent du **15 au 20 août 2026** — toutes **antérieures** à la requête du
23 août. C'est la raison mesurable pour laquelle les tailles et les SHA stables de `bhpl`,
`litterature`, `placement`, `mentions-legales`, `accueil`, `travail` et `portee-u2-2023` sont
**identiques** à ceux relevés par les passes #16 et #17 les 20 et 21 août : le site n'a pas changé
entre-temps, et IONOS a resservi le même rendu.

## Relevé

`Octets` est la taille du fichier archivé. `HTTP` est le code de la réponse, redirection **non
suivie**.

| URL source (encodée) | Fichier | HTTP | Octets | SHA-256 brut | SHA-256 stable | U+00A0 | U+00A0 du corps réduit |
|---|---|---|---|---|---|---|---|
| `https://www.mtbrabant.com/` | `accueil.html` | 200 | 47382 | `357a930642d9941aad607108dfa8defd098cdb853b0aa6dc4286f96259722025` | `bafe478b715e40363bdaf7692931a5de6746b4a47ff89a6b06ed0341634dd9c2` | 22 | 22 |
| `https://www.mtbrabant.com/bhpl/` | `bhpl.html` | 200 | 51063 | `75f6fc8df45aec6c2b63005c2a9a70fe0d15336d3e4c841244fd689d82c18f11` | `580718e1d17f319d43dd406c7a5f0e4e297168847d245bc10d6d83dad6d3fbc1` | 38 | 38 |
| `https://www.mtbrabant.com/bhpl/port%C3%A9e-a3-2025/` | `portee-a3-2025.html` | 200 | 39935 | `a25293b7e12c0363c6e094f97fa39758a8017b64e12113c3d3e7ff6b2d3a8acf` | `9b43e12b7670879af966c8f0eb5273105512d8dc8312703b4af40e0c6268eab8` | 5 | 5 |
| `https://www.mtbrabant.com/bhpl/port%C3%A9e-a2-2025/` | `portee-a2-2025.html` | 200 | 37011 | `f7458da3c898458956e149ec1d4790164e5967b5ed7c5b6eacd58acb96aa8631` | `64b78dec4e58f07d16eeadf3b0494b28a24d7218482ea462174b8efc4df39426` | 10 | 10 |
| `https://www.mtbrabant.com/bhpl/port%C3%A9e-a1-2025/` | `portee-a1-2025.html` | 200 | 40006 | `91fe71fcf25362afc1c2c2432441172b9c620aa9f626ff03522932f2c6c11f72` | `503fa054902c9ba1fef097be2cce550dff6b977aef6609b9a8078e9d1b1e20ce` | 5 | 5 |
| `https://www.mtbrabant.com/bhpl/port%C3%A9e-v2-2024/` | `portee-v2-2024.html` | 200 | 37985 | `5010468eb20fd9ae898fd9b31b9a87b265ef127c2fb7db5d131f6360d8e4ee94` | `b94634cef3b6436dc7db6cc15c890e6b3cae13ad59e1d8636d86baea346994d1` | 11 | 11 |
| `https://www.mtbrabant.com/bhpl/port%C3%A9e-v1-2024/` | `portee-v1-2024.html` | 200 | 36924 | `303ef483dbf0772db214513b9d831230e523bd05f682ef1bd9b4dd3356f154a2` | `126ca5a96f248a76c54cf7ff7e60fe65b6bd1e4d690fb54d8c200096c58e32f7` | 6 | 6 |
| `https://www.mtbrabant.com/bhpl/port%C3%A9e-u3-2023/` | `portee-u3-2023.html` | 200 | 38676 | `6d4527bed91bd3115c8f057c061dc62d3df900ff32f4b1ff03d0b347325ec31c` | `a5f4abd87be25b65d6fdcf09d8d79830c6cd14e0e755d7f5276cdaf384374ac8` | 16 | 16 |
| `https://www.mtbrabant.com/bhpl/port%C3%A9e-u2-2023/` | `portee-u2-2023.html` | 200 | 38362 | `ad53e25cfece9d16ce9dcf29983f256edcb73057cd8764cd34df1ed374406cbd` | `6aa8874de7a01f6e1bb29cd0ffa16c16eff6cbda1001a6f5625ff4c9c648f815` | 7 | 7 |
| `https://www.mtbrabant.com/bhpl/port%C3%A9e-u1-2023/` | `portee-u1-2023.html` | 200 | 39297 | `1cd966c5149c42ad6bb80506edeeb8f40066c252cb00e1ad2dc99986b7021139` | `2be89d7db68ba237f0b071a909a649c0d16a507b8135af6be543ddfab7e5ca47` | 7 | 7 |
| `https://www.mtbrabant.com/bhpl/port%C3%A9e-t-2022/` | `portee-t-2022.html` | 200 | 39338 | `d21f2b0ccdc857108b38ce50a5fb6f849caa5f07bed35f361579067810a25164` | `123a02d66778a5f85e5c8e581c2c52f0a6c875802b625b7fde4f841766dd5002` | 4 | 4 |
| `https://www.mtbrabant.com/bhpl/port%C3%A9e-s2-2021/` | `portee-s2-2021.html` | 200 | 40025 | `8797eb6415ae32bf0ff13fc026e3683a7f277b6e4b8b4dc34444d02fe08435ce` | `19be1da07ffcf6850e43a9cb11256634a2821dfceb532d5f22417550ef79769a` | 24 | 24 |
| `https://www.mtbrabant.com/bhpl/port%C3%A9e-s1-2021/` | `portee-s1-2021.html` | 200 | 39156 | `e07045bc9267ba3a4aab4b671ffaf2e85877b65dc1dde5882e924874d1c3ba0e` | `474d3eb9456ebb611a1c9a5c688dfae851f592f3c695c1e3082d0902c12180bd` | 8 | 8 |
| `https://www.mtbrabant.com/bhpl/port%C3%A9e-r-2020/` | `portee-r-2020.html` | 200 | 42244 | `5175cc53f9d32554aef2c9f8158d242359cc2ecfca31c5a698a3971c9e8d0a8b` | `a00bf8c5ee53f5edbeb7e62a027387f89d0970af5e5a663e274124b0f502c2a0` | 11 | 11 |
| `https://www.mtbrabant.com/bhpl/port%C3%A9e-p2-2019/` | `portee-p2-2019.html` | 200 | 41605 | `c7363807ce1c96ebfbc4e0906cfe12d1874ef710c4f414c2b2caf66b9aa29ed6` | `f14cd0c9fcecaa4eec55b635153fc3d425276dfa2dfeecfb584d00bfb12d7a63` | 12 | 12 |
| `https://www.mtbrabant.com/bhpl/port%C3%A9e-p-2019/` | `portee-p-2019.html` | 200 | 39712 | `c1bce0f263ef66a012119917d5461531744cadec6c88b014693f6287782735b2` | `bcace4550e55e32cb55c1818ca8e579e8f3af1adfe1c723e9c00f37012826e04` | 10 | 10 |
| `https://www.mtbrabant.com/bhpl/port%C3%A9e-o-2018/` | `portee-o-2018.html` | 200 | 43177 | `0d637c30096909f7c0cb023d691e4f108f561d10ddbdebe4edbd5e779a157c7a` | `4484bb9545e34e832bdf92cf1b3fe96639219972bff177361aecefd512650bb1` | 13 | 13 |
| `https://www.mtbrabant.com/bhpl/port%C3%A9e-n-2017/` | `portee-n-2017.html` | 200 | 44548 | `551bb08723e46805759d9c4e349e97dfcf82218b3103298deeeaf611a95ba337` | `1b70e80dba64408bfb1ac7f23d1684cd502915bb6275ff4eeeafedc8216219f7` | 18 | 18 |
| `https://www.mtbrabant.com/bhpl/port%C3%A9e-m-2016/` | `portee-m-2016.html` | 200 | 39765 | `516ee4a95d4469ad9f0a2a66af18d901be1d50ac7b9f0394ab9295b5f71c2306` | `79daaaaf80e7ee48c96c84f88363f62bd772a9cbfc2da9c80bd96c0c91fed939` | 8 | 8 |
| `https://www.mtbrabant.com/bhpl/port%C3%A9e-j-2014/` | `portee-j-2014.html` | 200 | 37310 | `ae35a6083e3bb6fc88b3743f9a05659b24523652fce7d743e6192dc0193ba579` | `e6322355f13a7aeed37aacf402a075038e9c6a4a345b8b277376b778e6f653cc` | 7 | 7 |
| `https://www.mtbrabant.com/bhpl/port%C3%A9e-h-2012/` | `portee-h-2012.html` | 200 | 36504 | `256352413833cc9affd6b5fc19dcf420a10252e0a74659faaeb01c66e0e2c324` | `f708ac4c9feeb797d8065adecf26be84a52d80b0cfaf3b755228fe1624bc5f9d` | 9 | 9 |
| `https://www.mtbrabant.com/bhpl/port%C3%A9e-g-2011/` | `portee-g-2011.html` | 200 | 37067 | `18301e39692db030cb8c11fbd2ac6961d8a54af7fb011e7b6b5c572275cd144c` | `fde726b4cf6aff7a12aadca14792b98e86bab74e6296e43c0cb46e2ee6db4f96` | 6 | 6 |
| `https://www.mtbrabant.com/bhpl/port%C3%A9e-f-2010/` | `portee-f-2010.html` | 200 | 37707 | `fd2f5b5c54e457e3cf4473d242589248770d592cde9fee5ade8e62be69ef4a40` | `d6e0d379bbeff436ca3ccc6ea947ddd5aa1bbe5371e1e93fd6768421b782a119` | 8 | 8 |
| `https://www.mtbrabant.com/bhpl/port%C3%A9e-e-2009/` | `portee-e-2009.html` | 200 | 37176 | `526bde984cddce2f94120347144f938e2f78575cbbc0c560d12d19d05f4661a7` | `d26dddffd07295c198749a7dc7627d238132cfe3a84f9d893e1211c516e9809a` | 6 | 6 |
| `https://www.mtbrabant.com/bhpl/port%C3%A9e-d-2008/` | `portee-d-2008.html` | 200 | 36638 | `ba8ad61e4dce9744eb95e8630074947c9dab444628c077258e2715e9df2bf596` | `b66db73fdd9115bd9c72b796f48d5e626c68172a356896a2432ab35c6f1f335a` | 5 | 5 |
| `https://www.mtbrabant.com/bhpl/port%C3%A9e-c-2007/` | `portee-c-2007.html` | 200 | 38791 | `6328d0be6404511eb250784a7414d81d2ce41e354873cdd882db96d83e4cf5ba` | `7a6d25f5e2aa1304534dc02ce306fa3859a2b2963f6411c0031affb414128b79` | 11 | 11 |
| `https://www.mtbrabant.com/bhpl/port%C3%A9e-s-2001/` | `portee-s-2001.html` | 200 | 37143 | `1dad06c55258ef84eb75ea3b144ef8ffaf068178fa6974e3a9a0c5ff77cec8f0` | `2d0303e98902986c07d3a1f012d5c5a09f147104c2a4545d38d789544f2f867b` | 26 | 26 |
| `https://www.mtbrabant.com/bhpl/port%C3%A9e-m-1996/` | `portee-m-1996.html` | 200 | 36646 | `c42d86bf8d56d264665a0976d05622a5e3ec0b27e67f8150dd1d05886b8ca144` | `84b0146148c75c0a22368397bbb133c8fcc89ed5ea9b2000470f6ce4247f63c8` | 5 | 5 |
| `https://www.mtbrabant.com/bhpl/port%C3%A9e-l-1995/` | `portee-l-1995.html` | 200 | 36867 | `987420da656589fb304b0271048a99b8bbeddcc1d246e4f99000bd81c874c162` | `1e22012f995f1504e3e59efe2e2f2381c23b7f7474a47905adae0e0af7cbc2e6` | 28 | 28 |
| `https://www.mtbrabant.com/bhpl/litt%C3%A9rature/` | `litterature.html` | 200 | 35541 | `0a96c4ba207e9836f779b3c13730ab01ba345bb5597ad4ecee517d0be8b93e7d` | `791371d931b6ce607641fbee61709a718498a9f128574e5f876c13a73fcc1a77` | 1 | 1 |
| `https://www.mtbrabant.com/travail/` | `travail.html` | 200 | 63623 | `8792bd7f3df858dfee00f9868aaa4c49ef10c1f8b6d86006b27435049a0479db` | `c181bffead4a6ef606a1a9e3c7dcd9551ede8ca8710770c08ae9cc25c1a31c74` | 136 | 136 |
| `https://www.mtbrabant.com/la-meute/` | `la-meute.html` | 200 | 34317 | `35bbfc911cd76b49915f15e4082d138c7982dd50c356031586f726c93feef6ff` | `749e8e0632e01086c92b2d486beb347e7d6c0e49693c55382af8c098dba3db36` | 3 | 3 |
| `https://www.mtbrabant.com/la-meute/very-best/` | `chien-very-best.html` | 200 | 38657 | `2cf9da8f4b29b9eda11a3468ab471fd00a327003bcca8459819cfcf299d5228b` | `3808b7cebac2d3381c933765440c92d97f22281e74ad5add5d2470107f7fbd18` | 21 | 21 |
| `https://www.mtbrabant.com/la-meute/you/` | `chien-you.html` | 200 | 39115 | `0706250a0c411bcf4c9ecde0698b760d745640e227e06b4ed8ab3383206c265c` | `c5911e74a20d82c4f7e7ba0829cc52cb0fa46a87cd6f4235a2da033eb74d9d00` | 23 | 23 |
| `https://www.mtbrabant.com/la-meute/tesla/` | `chien-tesla.html` | 200 | 40936 | `319abab2f1b2e39f1dfe8ba6441694d11b9281429f624f81d84224f4e51bcb40` | `3bcbba6128cc7f76822420fb988c5a64e4a70e9c678d98129c6defea4ae332f7` | 25 | 25 |
| `https://www.mtbrabant.com/la-meute/roxane/` | `chien-roxane.html` | 200 | 41318 | `566fdf0f798413f592734bcef816ae91f12dfc4f6f66088f85b095af2fe64f26` | `381d7434d58022df6922eecd8b2b28c8c51d566e8c2a36149c9221a08cc82cdd` | 30 | 30 |
| `https://www.mtbrabant.com/la-meute/ray-ban/` | `chien-ray-ban.html` | 200 | 40813 | `92e14e0f9490d9b79307e607a51732ce510770b75798340ae0a5ffa8aa2d48be` | `dd4e2228e465742aa28cb6c7e2294e4225868a3e4fa5168f1859a6f1cc24d6a5` | 30 | 30 |
| `https://www.mtbrabant.com/la-meute/rolex/` | `chien-rolex.html` | 200 | 40484 | `c89a040cfee7fe273c96f449149154d4c89e6a1ebe8871bca4ba75bdd1ca3a64` | `6f3ee50f8a3e07f32d9eae42541b86172cd892c96ed409e9b6e83adc2e3c19c1` | 20 | 20 |
| `https://www.mtbrabant.com/la-meute/youry/` | `chien-youry.html` | 200 | 38461 | `ce0b1af64f4ad7cd6cbab4a348219f909964a46327227a8519692ae0c9b754c1` | `17e9ec1af72a5c3539f051164417eab103a2f63539a642bdda177b0462119f3d` | 20 | 20 |
| `https://www.mtbrabant.com/la-meute/p%C3%A9gaz/` | `chien-pegaz.html` | 200 | 40813 | `db3765eede23b09d44524db84da4f619256e84b008825902aad9064f8cc10ddd` | `f95b19391dfbd066616e40a388079b4a7f744a868cc4c7060a3b539476379c4d` | 15 | 15 |
| `https://www.mtbrabant.com/la-meute/jango/` | `chien-jango.html` | 200 | 42564 | `ec7cbaaf378d684aaec003665bedb7e5118b8190c09b7ae0d7426305217bd1cc` | `f143360f3b6dc0c03c77bbf8dc1fb2305f7ed2958ea7903ab07259f0da012fe7` | 20 | 20 |
| `https://www.mtbrabant.com/la-meute/opium/` | `chien-opium.html` | 200 | 41568 | `6fe685f9a3e83a98ad35f5a570b09c1fd1ef08ff2e921e6baa80ccb8db9396b3` | `acea49a76e0ecb072a120c1e57247cada447c02b63cc4ca5510da2f393605448` | 19 | 19 |
| `https://www.mtbrabant.com/la-meute/grocky/` | `chien-grocky.html` | 200 | 40009 | `2ace95c8c6e8fb02e472247631146dbd54c5be699e8600f3bef0dd82265649c7` | `c968e66033084fb8e294f66a139a4d248079c8115c77c8d856787a3db8551f47` | 13 | 13 |
| `https://www.mtbrabant.com/la-meute/etch/` | `chien-etch.html` | 200 | 40968 | `ba37e12a00db555d298d18503db61cb85f928cae6e254002dd6fc9d06eca29c7` | `ed192603beef86f5c667fb1cda261cc37fa89b909c1841586de5b132c76b141b` | 18 | 18 |
| `https://www.mtbrabant.com/la-meute/happy/` | `chien-happy.html` | 200 | 37325 | `2786e0ed05b8306aca17e51143e7a8e43908c99af5cb420d1214932973f7fc0e` | `0502e4ab7ed2f062846d639201121e3207939d818a6152f0b2df2b96ba1f53a9` | 14 | 14 |
| `https://www.mtbrabant.com/la-meute/halan/` | `chien-halan.html` | 200 | 32712 | `f6b9a1e9fec3d6e2d8d053c2e8c066ba9d246da5b0ae4d4166fed0115cd5d8b4` | `cb18e729821c4d3300419de36dbfc8669abe095098148e84ac5d6f16dae7a1db` | 1 | 1 |
| `https://www.mtbrabant.com/la-meute/maya/` | `chien-maya.html` | 200 | 35602 | `e7cc78396211554e904c93860205c31e5120e73ac1000f0dc9930f2ad77e4403` | `ad42a09adc99377c49202db25130ed1208b04e49de666f0ea67addbe0cb47090` | 3 | 3 |
| `https://www.mtbrabant.com/la-meute/tara/` | `chien-tara.html` | 200 | 35577 | `d9882fffcfa300405ddaa3a6bcb7528137617ac0cdb82c1c04a13841a650cf92` | `c172be4505dbbd29121830ea0f8a418248cca94a6a811ed47551c09e42e53415` | 2 | 2 |
| `https://www.mtbrabant.com/la-meute/gribouille/` | `chien-gribouille.html` | 200 | 36621 | `18b55865341beab4704da831cd5c4007aec7eb8020f8352502bd59a86b86b9a1` | `507c076c13627bc3da7a6f659cb065d0b1dc6aaa120b82c641a71f927890482e` | 3 | 3 |
| `https://www.mtbrabant.com/placement/` | `placement.html` | 200 | 31205 | `8ffc5a46a377fb4134b71ed669fc7b36a53da97b638941800aa015463e4892f8` | `3ee1c12510350f592d1c943f7c3378abffc06d795f17fddcc262a5a2fab11486` | 3 | 3 |
| `https://www.mtbrabant.com/contact/` | `contact.html` | 200 | 38319 | `e42b28e3fa4fe9811a731dd6d78a518c3b206d08618c5053ef035f6aa44656c9` | `89a270c585df9482c8d4fd5624ee869599f2b17c0db26c14979baae40c311267` | 7 | 7 |
| `https://www.mtbrabant.com/mentions-l%C3%A9gales/` | `mentions-legales.html` | 200 | 33151 | `4001f9a5ac2683732fdfc1d10896ff176113636d04d14e689ba76947c04f7575` | `75f55ffea4f64f18312262b2f8c54ee00c6ebff777fb07da8a73ad368df49d4b` | 1 | 1 |
| `https://www.mtbrabant.com/bhpl/bhpl-en-france/` | `bhpl-en-france.html` | 302 | 0 | `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855` | `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855` | 0 | 0 |
| `https://www.mtbrabant.com/politique-de-confidentialite/` | `politique-de-confidentialite.html` | 404 | 31462 | `23e0320be27d3c352db84d798d07f56df1850efb44442fb630e2f6d196a3a01a` | `31541ce02b7071c795d8211f55b07331139688d44e1d59fd20ae4ced3817b17b` | 1 | 1 |
