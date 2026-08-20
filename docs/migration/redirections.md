# Carte des redirections — ancienne URL vers nouvelle URL

Source de la carte des 301 attendue par **D5** (`docs/BRIEF.md` §14). Ce fichier **s'agrandit** : chaque
passe de reprise ajoute ses lignes, aucune ne réécrit celles des autres.

**État : 7 URL sur 52.** Les 7 sont celles de l'échantillon de démonstration de l'issue **#16**
(3 portées, 4 fiches de chiens). Le reste appartient à l'epic **#19-#21**.

**Aucune redirection n'est encore posée dans le code.** Ce fichier constate la correspondance ;
l'écriture des règles 301 appartient à l'issue `seo` (#24), qui devra aussi payer la dette **T6**
(aucune voie conforme pour ajouter une règle de réécriture).

## Avertissement : ces cibles sont provisoires

**Q4 est ouverte** (`docs/ETAT.md`) : « URL accentuées conservées (`/bhpl/portée-a3-2025/`) ou
normalisées avec redirections 301 ». Tant qu'elle n'est pas tranchée, les colonnes « Nouvelle URL »
ci-dessous ne sont que **ce que la base de développement produit aujourd'hui**, avec les préfixes
`/portees/` et `/chien/` déclarés par `mtb-core`. Elles changeront si Q4 est tranchée en faveur de la
conservation des URL accentuées.

## Portées

| Ancienne URL | Nouvelle URL | Identique ? |
|---|---|---|
| `https://www.mtbrabant.com/bhpl/port%C3%A9e-m-2016/` | `/portees/m-2016/` | **non** — chemin et slug changent (`/bhpl/portée-m-2016/` → `/portees/m-2016/`) |
| `https://www.mtbrabant.com/bhpl/port%C3%A9e-u2-2023/` | `/portees/u2-2023/` | **non** — idem |
| `https://www.mtbrabant.com/bhpl/port%C3%A9e-j-2014/` | `/portees/j-2014/` | **non** — idem |

## Fiches de chiens

| Ancienne URL | Nouvelle URL | Identique ? |
|---|---|---|
| `https://www.mtbrabant.com/la-meute/jango/` | `/chien/jango/` | **non** — `/la-meute/` devient `/chien/` ; le slug `jango` est conservé |
| `https://www.mtbrabant.com/la-meute/p%C3%A9gaz/` | `/chien/pegaz/` | **non** — chemin **et** slug : WordPress translittère `pégaz` en `pegaz` |
| `https://www.mtbrabant.com/la-meute/etch/` | `/chien/etch/` | **non** — chemin seul, slug conservé |
| `https://www.mtbrabant.com/la-meute/rolex/` | `/chien/rolex/` | **non** — chemin seul, slug conservé |

## URL capturées mais dont la cible n'existe pas encore

Elles sont dans `source/` parce qu'elles portent des faits d'élevage (voir `source/README.md`), mais
aucune page ne les remplace à ce jour. **Ne pas les compter comme reprises.**

| Ancienne URL | Nouvelle URL | Identique ? |
|---|---|---|
| `https://www.mtbrabant.com/travail/` | — page Travail non livrée (**#17**) | — |
| `https://www.mtbrabant.com/` | `/` — accueil livré, mais son **contenu** n'est pas repris | non applicable |
| `https://www.mtbrabant.com/la-meute/` | page « La meute » — **n'existe que dans la base de développement** (dette **T30**) | — |
