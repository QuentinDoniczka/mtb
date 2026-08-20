# Instantané du site source — pages libres de mtbrabant.com

Ce dossier prolonge `../README.md` (passe #16). **Il n'en remplace rien et n'en corrige rien.**
La méthode de capture, les trois seules transformations autorisées et la navigation commune sont
définies là-bas ; elles ne sont pas répétées ici. Ce fichier n'indexe que les **pages libres**
capturées pour l'issue #17.

**Ce dossier est une archive, pas une saisie.** Ces fichiers sont l'instantané du site source ; ils
ne sont le contenu d'aucune page du nouveau site. La saisie du contenu réel, elle, a été faite
**depuis ici** : les huit pages libres existent en **base de développement** (identifiants 128 à 135,
`page_on_front` réglé sur 128). Aucune ligne de ce contenu n'est versionnée en fichier — c'est la
règle du contrat #17 §2, le contenu vit en base.

## Périmètre de cette passe

| Fichier | URL source (encodée) | Réponse HTTP |
|---|---|---|
| `bhpl.md` | `https://www.mtbrabant.com/bhpl/` | 200 |
| `bhpl-en-france.md` | `https://www.mtbrabant.com/bhpl/bhpl-en-france/` | **302 — protégée par mot de passe, non capturée** |
| `litterature.md` | `https://www.mtbrabant.com/bhpl/litt%C3%A9rature/` | 200 |
| `placement.md` | `https://www.mtbrabant.com/placement/` | 200 |
| `mentions-legales.md` | `https://www.mtbrabant.com/mentions-l%C3%A9gales/` | 200 |

D'autres fichiers sont présents dans ce dossier — au moment d'écrire ces lignes `accueil.md`,
`travail.md` et `politique-de-confidentialite.md`. Ils **n'appartiennent pas à cette passe** et ne
sont pas décrits ici ; leurs en-têtes portent leur propre provenance. Deux d'entre eux portent une
URL déjà capturée par la passe #16 : voir la section « Deux captures de la même URL » ci-dessous.

**Il n'y a pas de page « Politique de confidentialité » sur le site source** (`BRIEF.md` §5.4 : « à
créer »). Elle a bien été cherchée : **trois adresses interrogées, trois 404**, aucune entrée au
`sitemap.xml` et aucune entrée de menu. Le constat, avec les trois adresses, est archivé dans
`politique-de-confidentialite.md`.

## Deux captures de la même URL — laquelle lire, et pour quoi

Deux URL de ce dossier sont **aussi** capturées par la passe #16, dans le dossier parent :

| URL source | Capture de la passe #16 | Capture de ce dossier |
|---|---|---|
| `https://www.mtbrabant.com/` | `docs/migration/source/accueil.md` | `docs/migration/source/pages/accueil.md` |
| `https://www.mtbrabant.com/travail/` | `docs/migration/source/travail.md` | `docs/migration/source/pages/travail.md` |

**Ce n'est ni une divergence ni une erreur.** Même URL, même jour (2026-08-20), deux réductions du
même HTML avec un **découpage de zones différent**. Aucune des deux n'énonce un fait que l'autre
contredit.

**Laquelle lire pour quoi :**

- **Pour une ligne de tableau** — un résultat de travail : année, chien, niveau — lire la version de
  **ce dossier** (`pages/travail.md`). Elle **conserve l'association de la ligne**, cellules séparées
  par ` | ` :

  ```
  1994 | ♂ Storm Haven Guépard | Brevet
  ```

  La version de `../travail.md` éclate chaque cellule sur sa propre ligne (`♂ Storm Haven Guépard`
  seul, ligne 174) : l'association année / chien / niveau n'y tient plus qu'à l'ordre des lignes.
  C'est précisément l'association dont a besoin la reprise des résultats de travail.

- **Pour la prose et pour tout le reste**, la version du dossier parent (`../accueil.md`,
  `../travail.md`) **reste la référence** : c'est elle qui est commitée par la passe #16.

**Aucune des deux ne se réécrit** pour ressembler à l'autre, et **aucune ne se supprime**. La passe
#16 est close ; ses fichiers décrivent honnêtement leur propre périmètre.

**Avertissement pour toute vérification future** : le **SHA-256 brut d'une page IONOS n'est pas
reproductible**, le HTML embarquant l'époque Unix de la requête dans les URL de `all.css` et
`all.js`. Deux fetch de la même page donnent la même taille et un SHA différent. Pour obtenir un
condensé rejouable, normaliser d'abord l'horodatage — c'est la méthode employée pour la colonne
« SHA-256 stable » ci-dessous :

```
sha256( HTML_reçu  avec  s/(929224983(&amp;|&)t=)[0-9]{10}/\1EPOCH/g )
```

## Empreintes de capture

Capture par `curl` le **2026-08-20**.

| Fichier | Taille du HTML reçu | SHA-256 du HTML reçu | SHA-256 **stable** (voir ci-dessous) | U+00A0 | Lignes de texte non vides |
|---|---|---|---|---|---|
| `bhpl.md` | 51 063 o | `65a31deed44f166cf8ecd62b9c2bc1484fa0bd720dff3a865e6f353ffabb77ab` | `580718e1d17f319d43dd406c7a5f0e4e297168847d245bc10d6d83dad6d3fbc1` | 38 | 121 |
| `bhpl-en-france.md` | 0 o (réponse 302 sans corps) | `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855` | — | 0 | — |
| `litterature.md` | 35 541 o | `bf0ba991f3c77842fed8ce8834659e00fbf1e76c6074b2ad1c2a5932e6cabf68` | `791371d931b6ce607641fbee61709a718498a9f128574e5f876c13a73fcc1a77` | 1 | 62 |
| `placement.md` | 31 205 o | `4c4cf8391b4dc4d4694156befe80fbde3552405e61d54424e2f50dd5d6653d2e` | `3ee1c12510350f592d1c943f7c3378abffc06d795f17fddcc262a5a2fab11486` | 3 | 35 |
| `mentions-legales.md` | 33 151 o | `ec9e97ab4c5918404acf454e87ed8e124955870bcfb439885ff24c49eb48dc53` | `75f55ffea4f64f18312262b2f8c54ee00c6ebff777fb07da8a73ad368df49d4b` | 1 | 46 |

« Lignes de texte non vides » compte les lignes non vides des blocs de zones du `.md`, marqueurs
`[LIEN]` / `[IMAGE]` / `[IFRAME]` compris.

### Le SHA-256 du HTML reçu n'est pas reproductible — et pourquoi

Le HTML servi par IONOS contient un **horodatage de cache qui change à chaque requête**, dans les URL
de la feuille de style et du script du gabarit :

```
…/s/misc/all.css?id=929224983&amp;t=1787208465   ← t = époque Unix de la requête
```

Deux requêtes consécutives sur la même page donnent donc **la même taille en octets mais un SHA-256
différent**. Constaté et mesuré : deux fetch de `/placement/` à huit secondes d'écart ne diffèrent
que par `t=1787208465` / `t=1787208473`.

Conséquence : un SHA-256 brut relevé un jour ne pourra jamais être revérifié. La colonne **SHA-256
stable** est donc donnée en plus. Elle se recalcule ainsi, et ne dépend que du contenu :

```
sha256( HTML_reçu  avec  s/(929224983(&amp;|&)t=)[0-9]{10}/\1EPOCH/g )
```

Vérifié : identique entre deux fetch successifs sur les quatre pages servies.

## Découpage en zones

Les fichiers de ce dossier découpent **cinq** zones du gabarit IONOS 2111, là où `../README.md`
en annonce trois :

| Section du `.md` | Classe HTML |
|---|---|
| Bandeau de gabarit | `diywebEmotionHeader` |
| Contenu principal | `diywebMain` |
| Colonne secondaire (rendu large) | `diywebSecondary` |
| Colonne latérale (rendu étroit) | `diywebSidebar` |
| Pied de page | `diywebFooter` |

`diywebSecondary` et `diywebSidebar` portent **le même contenu**, servi deux fois par le gabarit pour
deux largeurs d'écran. Sur `litterature.md`, `diywebSecondary` contient en plus le sous-menu « BHPL »
répété : c'est le gabarit, pas du contenu rédigé.

## Navigation — ce qui n'est pas dans `../README.md`

`../README.md` donne le menu principal et le sous-menu « BHPL ». Deux choses vues sur les pages de
cette passe n'y figurent pas :

1. **Le sous-menu déroulant de « La meute »**, présent sur toutes les pages capturées ici, dans cet
   ordre :

```
[LIEN href=https://www.mtbrabant.com/la-meute/very-best/]Very Best[/LIEN]
[LIEN href=https://www.mtbrabant.com/la-meute/you/]You[/LIEN]
[LIEN href=https://www.mtbrabant.com/la-meute/tesla/]Tesla[/LIEN]
[LIEN href=https://www.mtbrabant.com/la-meute/rolex/]Rolex[/LIEN]
[LIEN href=https://www.mtbrabant.com/la-meute/pégaz/]Pégaz[/LIEN]
[LIEN href=https://www.mtbrabant.com/la-meute/jango/]Jango[/LIEN]
[LIEN href=https://www.mtbrabant.com/la-meute/opium/]Opium[/LIEN]
[LIEN href=https://www.mtbrabant.com/la-meute/grocky/]Grocky[/LIEN]
[LIEN href=https://www.mtbrabant.com/la-meute/etch/]Etch[/LIEN]
[LIEN href=https://www.mtbrabant.com/la-meute/happy/]Happy[/LIEN]
[LIEN href=https://www.mtbrabant.com/la-meute/maya/]Maya[/LIEN]
[LIEN href=https://www.mtbrabant.com/la-meute/tara/]Tara[/LIEN]
[LIEN href=https://www.mtbrabant.com/la-meute/gribouille/]Gribouille[/LIEN]
```

   Soit **13 fiches**. Le `sitemap.xml` en compte **17** : `roxane`, `ray-ban`, `youry` et `halan`
   sont au sitemap **mais pas au menu**. Constat brut, aucune conclusion tirée ici.

2. **Le libellé du bouton de navigation mobile** : `Ouvrir/fermer la navigation`.

Par ailleurs : **aucune des quatre pages servies ne contient de lien vers `/placement/`** — ni le
menu principal, ni un sous-menu, ni le pied de page. La page est au `sitemap.xml` mais n'est atteinte
par aucun lien des pages de cette passe.

## Constats de capture

- **`bhpl-en-france` est une 53ᵉ URL.** Le `sitemap.xml` du site compte **52** `<loc>` (vérifié le
  2026-08-20) et **aucune** ne contient `bhpl-en-france`. L'URL existe pourtant, elle est au menu, et
  elle répond — par une redirection vers un formulaire de mot de passe. Elle est donc **hors du
  décompte de 52 de `BRIEF.md` §7**, et elle n'a **pas** pu être capturée. Voir `bhpl-en-france.md`.

- **La page « Littérature » n'a aucun contenu rédactionnel.** Sa zone `diywebMain` est vide. Le texte
  visible de la page entière ne contient que le gabarit : navigation, encart « Chiots nés le
  29/06/2026 », bouton de partage, carte, pied de page. Il n'y a **ni titre de livre, ni référence,
  ni lien de lecture** sur cette page. Ce n'est pas un défaut de capture : vérifié en extrayant le
  texte de la totalité du document, pas seulement de `diywebMain`.

- **Aucun montant de tarif** n'apparaît sur les cinq URL de cette passe. La page « Placement » ne
  porte que « Adultes à placer ! » et « Pas de chiens à replacer actuellement ». Le seul mot « prix »
  du lot est dans le texte « COMBIEN POUR UN CHIOT ? » de la page BHPL, qui n'énonce aucune somme.

- **L'encart « Chiots nés le 29/06/2026 / Tous réservés / Contacter nous au 0680505619 »** est le même
  encart global de site que celui décrit dans `../README.md`. Il est présent sur les quatre pages
  servies. Ce n'est pas une donnée de la page qui le porte.

## Écarts avec les coordonnées de référence de `BRIEF.md` §7

Relevés, **non corrigés**. La page Mentions légales est reproduite telle quelle dans
`mentions-legales.md`.

| Donnée | Mentions légales du site source | `BRIEF.md` §7 |
|---|---|---|
| Adresse | `ROUTE DE SALERNES` / `83570 ENTRECASTEAUX` — **sans numéro de voie**, en capitales | `3060 Route de Salernes, 83570 Entrecasteaux` |
| Téléphone (texte) | `680505619` — **neuf chiffres, sans le 0 initial** | `0680505619` |
| Téléphone (lien) | `tel:-680505619` — **avec un tiret** avant le numéro | — |
| Nom | `Fabienne Gueneau` — **sans accent** | `Fabienne Guéneau` |
| Courriel | `mtbrabant@gmail.com` | identique |
| Raison sociale | `Elevage du Mont Brabant` | non mentionnée |
| SIRET | `82237792500018` | non mentionné |

Le pied de page des mêmes pages écrit, lui, `© Fabienne Guéneau MAJ Juin 2026` — **avec** l'accent, et
l'encart latéral écrit le téléphone **avec** le 0 initial. Le site source n'est donc pas cohérent avec
lui-même sur ces deux points. Rien n'a été harmonisé.
