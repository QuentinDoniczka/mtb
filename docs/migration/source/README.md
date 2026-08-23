# Instantané du site source — mtbrabant.com

Ce dossier est **la seule source légitime des données d'élevage** (`docs/ETAT.md`, « Faits du domaine
à ne jamais réinventer »). Rien ne se saisit dans WordPress qui ne soit d'abord recopié ici.

> **Ce README décrit la passe #16, et elle seule.** Le dossier porte désormais **les 52 URL** du
> `sitemap.xml`, plus leur HTML brut et leurs photographies — capturés par l'issue **#19**.
> **Le document d'entrée du dossier est [`INVENTAIRE.md`](INVENTAIRE.md)**, pas celui-ci.
> Ce fichier est conservé parce qu'il documente **comment les dix fichiers de la passe #16 ont été
> faits**, et qu'ils n'ont pas été refaits.

## Où est quoi — l'état réel du dossier

| Chemin | Ce que c'est |
|---|---|
| **[`INVENTAIRE.md`](INVENTAIRE.md)** | **Commencer ici.** Preuve de complétude des 52 URL, tableau des 52, anomalies, questions ouvertes |
| [`sitemap.xml`](sitemap.xml) | La **définition opposable des 52 URL** — il fait foi, et rien d'autre |
| [`html/`](html/) | Le **HTML brut** des 54 URL, tel que reçu. [`html/RELEVE.md`](html/RELEVE.md) le mesure, [`html/README.md`](html/README.md) dit comment s'en servir **et ce qu'il ne porte pas** |
| [`portees/`](portees/) · [`chiens/`](chiens/) · [`pages/`](pages/) | Les **réductions lisibles**, une par URL — 27 portées, 17 chiens, 9 pages. C'est de là que part la saisie |
| `accueil.md` · `travail.md` · `la-meute.md` | Les trois captures de la passe #16 restées à la racine (deux sont **doublées** dans `pages/` — voir `INVENTAIRE.md` §2) |
| [`photos/`](photos/) | **192 fichiers photo** archivés, et [`photos/MANIFESTE.md`](photos/MANIFESTE.md) — provenance, dimensions, empreintes, et la **dette T12** à lire avant tout téléversement |
| [`outils/`](outils/) | `reduire.py` — rejoue une réduction **depuis `html/`, sans réseau** — et `verifier_concordance.py` |
| [`ECART-PASSE-16.md`](ECART-PASSE-16.md) | **Ce que les dix fichiers ci-dessous ont perdu**, mesuré fichier par fichier, avec un **verdict** par fichier : à recapturer, ou utilisable tel quel |

La méthode de capture en vigueur est gelée au contrat **`docs/contracts/issue-19.md`** (§3).

## Ce que la passe #16 a capturé

**Périmètre de cette passe (issue #16, échantillon de démonstration) : 10 URL sur 52.**
Ce dossier était fait pour **s'agrandir**, pas pour être refait — et c'est ce qui s'est passé :
**#19 a ajouté les 42 autres URL sans réécrire ces dix-là**. Leur écart est **déclaré et chiffré**
dans `ECART-PASSE-16.md`, il n'est pas corrigé.

| Fichier | URL source |
|---|---|
| `portees/portee-m-2016.md` | `https://www.mtbrabant.com/bhpl/port%C3%A9e-m-2016/` |
| `portees/portee-u2-2023.md` | `https://www.mtbrabant.com/bhpl/port%C3%A9e-u2-2023/` |
| `portees/portee-j-2014.md` | `https://www.mtbrabant.com/bhpl/port%C3%A9e-j-2014/` |
| `chiens/chien-jango.md` | `https://www.mtbrabant.com/la-meute/jango/` |
| `chiens/chien-pegaz.md` | `https://www.mtbrabant.com/la-meute/p%C3%A9gaz/` |
| `chiens/chien-etch.md` | `https://www.mtbrabant.com/la-meute/etch/` |
| `chiens/chien-rolex.md` | `https://www.mtbrabant.com/la-meute/rolex/` |
| `travail.md` | `https://www.mtbrabant.com/travail/` |
| `accueil.md` | `https://www.mtbrabant.com/` |
| `la-meute.md` | `https://www.mtbrabant.com/la-meute/` |

`travail.md`, `accueil.md` et `la-meute.md` ne sont pas des fiches : elles sont capturées parce
qu'elles portent des faits d'élevage qui ne sont **nulle part ailleurs** — les résultats de travail
d'un chien, et les seules mentions de statut (« Femelle reproductrice », « Nos retraités et
disparus »).

## Méthode de capture de la passe #16 — trois zones, et ce n'est plus la convention

> **Attention.** Ce qui suit décrit **la passe #16**. La convention en vigueur depuis #19 découpe
> **cinq** zones (contrat `docs/contracts/issue-19.md` §3.1), pas trois. Les deux formes cohabitent
> dans le dossier : `INVENTAIRE.md` §3 dit quel fichier relève de laquelle, et `ECART-PASSE-16.md`
> chiffre ce que les trois zones ont coûté aux dix fichiers ci-dessus.

- Récupéré par `curl` le **20 août 2026**, HTTP 200 sur les 10 URL. Chaque fichier porte la taille
  et le **SHA-256** du HTML reçu : la capture est rejouable et vérifiable.
- Le HTML est réduit en texte par découpage de zones (`diywebMain`, `diywebSidebar`, `diywebFooter`)
  du gabarit IONOS 2111 du site. **Deux zones manquent à cette liste** — `diywebEmotionHeader` (le
  bandeau) et `diywebSecondary` (la colonne secondaire) : ce qu'elles portaient est **absent** des
  dix fichiers de la passe #16. C'est la perte principale que chiffre `ECART-PASSE-16.md`.
- **Aucune des deux conventions ne porte les métadonnées du `<head>`** — indexation, description de
  référencement, partage social. Voir `html/README.md` §4 : c'est cette limite qui a laissé passer
  les cinq pages en `noindex, nofollow` (`INVENTAIRE.md` §10, anomalie 4).

**Les trois seules transformations appliquées** — aucune ne touche un mot :

1. `<br>` et fins de blocs deviennent des retours à la ligne ; les lignes vides consécutives sont
   réduites à une seule. **Les retours à la ligne au milieu d'une phrase sont donc ceux du source**
   (ex. « HD:A- / ED0 ADN ») : ils viennent de vrais `<br>`, ils ne sont pas un artefact.
2. `<a>`, `<img>` et `<iframe>` sont remplacés par des marqueurs `[LIEN href=…]…[/LIEN]`,
   `[IMAGE src=… alt="…"]`, `[IFRAME src=…]`, pour ne perdre ni une adresse ni un texte alternatif.
3. Les entités HTML sont décodées (`&amp;` devient `&`). Les **espaces insécables (U+00A0) sont
   conservées** ; chaque fichier en donne le compte, parce qu'elles sont invisibles à la relecture.

Ce qui **n'a pas** été fait : aucune correction d'orthographe, d'accent, de date, de graphie de n° LOF
ni d'incohérence apparente. Ce qui semble faux dans un fichier de ce dossier **est** ce que le site
affiche.

## Navigation commune, non recopiée dans chaque fichier

Identique sur les **dix pages de cette passe**. Le recomptage à l'échelle des 54 fichiers HTML est à
`INVENTAIRE.md` §9, ligne « Menus » — c'est lui qui établit que cinq adresses du `sitemap.xml`
(`roxane`, `ray-ban`, `youry`, `halan`, `placement`) **ne sont dans aucun menu**. Menu principal :

```
[LIEN href=https://www.mtbrabant.com/]Accueil[/LIEN]

[LIEN href=https://www.mtbrabant.com/bhpl/]BHPL[/LIEN]

[LIEN href=https://www.mtbrabant.com/travail/]Travail[/LIEN]

[LIEN href=https://www.mtbrabant.com/la-meute/]La meute[/LIEN]

[LIEN href=https://www.mtbrabant.com/contact/]Contact[/LIEN]

[LIEN href=https://www.mtbrabant.com/mentions-légales/]Mentions légales[/LIEN]
```

Sous-menu déroulant (barre latérale de navigation) :

```
[LIEN href=https://www.mtbrabant.com/bhpl/portée-a3-2025/]Portée "A3" 2025[/LIEN]

[LIEN href=https://www.mtbrabant.com/bhpl/portée-a2-2025/]Portée "A2" 2025[/LIEN]

[LIEN href=https://www.mtbrabant.com/bhpl/portée-a1-2025/]Portée "A1" 2025[/LIEN]

[LIEN href=https://www.mtbrabant.com/bhpl/portée-v2-2024/]Portée "V2"2024[/LIEN]

[LIEN href=https://www.mtbrabant.com/bhpl/portée-v1-2024/]Portée "V1"2024[/LIEN]

[LIEN href=https://www.mtbrabant.com/bhpl/portée-u3-2023/]Portée "U3" 2023[/LIEN]

[LIEN href=https://www.mtbrabant.com/bhpl/portée-u2-2023/]Portée "U2" 2023[/LIEN]

[LIEN href=https://www.mtbrabant.com/bhpl/portée-u1-2023/]Portée "U1" 2023[/LIEN]

[LIEN href=https://www.mtbrabant.com/bhpl/portée-t-2022/]Portée "-T" 2022[/LIEN]

[LIEN href=https://www.mtbrabant.com/bhpl/portée-s2-2021/]Portée "S2" 2021[/LIEN]

[LIEN href=https://www.mtbrabant.com/bhpl/portée-s1-2021/]Portée "S1" 2021[/LIEN]

[LIEN href=https://www.mtbrabant.com/bhpl/portée-r-2020/]Portée "-R" 2020[/LIEN]

[LIEN href=https://www.mtbrabant.com/bhpl/portée-p2-2019/]Portée "P2" 2019[/LIEN]

[LIEN href=https://www.mtbrabant.com/bhpl/portée-p-2019/]Portée "-P" 2019[/LIEN]

[LIEN href=https://www.mtbrabant.com/bhpl/portée-o-2018/]Portée "-O" 2018[/LIEN]

[LIEN href=https://www.mtbrabant.com/bhpl/portée-n-2017/]Portée "-N" 2017[/LIEN]

[LIEN href=https://www.mtbrabant.com/bhpl/portée-m-2016/]Portée "-M" 2016[/LIEN]

[LIEN href=https://www.mtbrabant.com/bhpl/portée-j-2014/]Portée "-J" 2014[/LIEN]

[LIEN href=https://www.mtbrabant.com/bhpl/portée-h-2012/]Portée "-H" 2012[/LIEN]

[LIEN href=https://www.mtbrabant.com/bhpl/portée-g-2011/]Portée "-G" 2011[/LIEN]

[LIEN href=https://www.mtbrabant.com/bhpl/portée-f-2010/]Portée "-F" 2010[/LIEN]

[LIEN href=https://www.mtbrabant.com/bhpl/portée-e-2009/]Portée "-E" 2009[/LIEN]

[LIEN href=https://www.mtbrabant.com/bhpl/portée-d-2008/]Portée "-D" 2008[/LIEN]

[LIEN href=https://www.mtbrabant.com/bhpl/portée-c-2007/]Portée "-C" 2007[/LIEN]

[LIEN href=https://www.mtbrabant.com/bhpl/portée-s-2001/]Portée "-S" 2001[/LIEN]

[LIEN href=https://www.mtbrabant.com/bhpl/portée-m-1996/]Portée "-M" 1996[/LIEN]

[LIEN href=https://www.mtbrabant.com/bhpl/portée-l-1995/]Portée "-L" 1995[/LIEN]

[LIEN href=https://www.mtbrabant.com/bhpl/bhpl-en-france/]BHPL en France[/LIEN]

[LIEN href=https://www.mtbrabant.com/bhpl/littérature/]Littérature[/LIEN]
```

## Colonne latérale : un encart de site, pas une donnée de portée

La zone « Colonne latérale » capturée porte **exactement le même texte sur les 9 pages de cette
passe qui en ont une**, fiches de chiens comprises (l'accueil, lui, n'a pas de colonne latérale — sa zone est vide) :

```
Chiots nés le 29/06/2026

Tous réservés

Contacter nous au 0680505619
```

C'est donc un **encart global du site**, pas la disponibilité de la portée affichée. En particulier,
`portees/portee-m-2016.md` (portée de 2016) porte le même « Chiots nés le 29/06/2026 » que toutes les
autres. **Ne jamais lire cette zone comme la disponibilité d'une portée.**

## Ce que le site source ne dit nulle part

À ne pas combler, à ne pas déduire :

- **Aucune page de portée n'énonce sa disponibilité** (« chiots disponibles » / « tous réservés » /
  « portée passée »). Le seul « Tous réservés » du site est l'encart global ci-dessus.
- **Aucune fiche de chien n'énonce un statut** au sens du modèle (reproducteur / en cours de
  confirmation / retraité / disparu). Les deux seules formulations approchantes sont sur des pages
  d'index : « Femelle reproductrice » / « Etalon … » sur l'accueil, et le titre « Nos retraités et
  disparus » sur `la-meute/`, qui **réunit deux statuts du modèle en un seul groupe**.
- **Aucune portée du site n'est sans liste de chiots** : les 27 en ont une (vérifié page par page sur
  les 27 URL de portée du sitemap).
