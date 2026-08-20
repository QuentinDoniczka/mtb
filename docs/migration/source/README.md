# Instantané du site source — mtbrabant.com

Ce dossier est **la seule source légitime des données d'élevage** (`docs/ETAT.md`, « Faits du domaine
à ne jamais réinventer »). Rien ne se saisit dans WordPress qui ne soit d'abord recopié ici.

## Ce qui est capturé, et ce qui ne l'est pas

**Périmètre de cette passe (issue #16, échantillon de démonstration) : 10 URL sur 52.**
La reprise complète des 52 URL est l'epic #19-#21. Ce dossier est fait pour **s'agrandir**, pas pour
être refait : #19-#21 ajoute des fichiers, ne réécrit pas ceux-ci.

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

## Méthode de capture

- Récupéré par `curl` le **20 août 2026**, HTTP 200 sur les 10 URL. Chaque fichier porte la taille
  et le **SHA-256** du HTML reçu : la capture est rejouable et vérifiable.
- Le HTML est réduit en texte par découpage de zones (`diywebMain`, `diywebSidebar`, `diywebFooter`)
  du gabarit IONOS 2111 du site.

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

Identique sur les pages capturées. Menu principal :

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

La zone « Colonne latérale » capturée porte **exactement le même texte sur les 9 pages qui en ont
une**, fiches de chiens comprises (l'accueil, lui, n'a pas de colonne latérale — sa zone est vide) :

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
