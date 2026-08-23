# HTML brut de l'ancien site — ce que c'est, et comment on s'en sert

54 fichiers : les **52 URL du `sitemap.xml`** (`../sitemap.xml`, qui fait foi) et les **2 URL hors
sitemap** du §2 du contrat `docs/contracts/issue-19.md` — `bhpl-en-france` (302) et
`politique-de-confidentialite` (404). Un fichier par URL, **octets bruts tels que reçus**, aucun
reformatage. Les mesures — code HTTP, taille, SHA-256 brut, SHA-256 stable, espaces insécables — sont
dans `RELEVE.md`.

## 1. Règle de préséance

**Le `.md` est ce qu'on lit et ce qu'on importe. Le `.html` est ce qui tranche en cas de doute.**

Les réductions lisibles vivent dans `../portees/`, `../chiens/` et `../pages/` : c'est d'elles que
part la saisie dans WordPress. Quand une réduction paraît douteuse — un mot avalé, un retour à la
ligne suspect, une date qu'on croit mal recopiée — la réponse est dans le `.html` de ce dossier, pas
dans un souvenir ni dans une autre page.

**Le `.html` n'est jamais importé tel quel.** Il porte le gabarit IONOS, sa navigation, ses scripts
et ses liens d'édition ; rien de tout cela n'appartient au nouveau site.

Pour rejouer une réduction sans toucher au réseau :

```
python ../outils/reduire.py placement.html
python ../outils/reduire.py placement.html --entete
```

## 2. Pourquoi le HTML brut est archivé

Pour rendre **D11 auditable après coup**.

Le reste écrit de l'issue #17 le dit sans détour : « D11 y est invérifiable par quiconque,
définitivement, une fois la base détruite ». Le HTML brut est l'antidote exact. Tant qu'il est
versionné, n'importe qui peut reprendre une affirmation de reprise — un numéro LOF, une date de
naissance, un nom de père — et la confronter aux octets que le site servait.

Il règle aussi, au passage, le problème du **SHA non reproductible** : le HTML IONOS embarque
l'époque Unix de la requête, donc un condensé brut relevé un jour ne se revérifie jamais (voir
`RELEVE.md`). En archivant les octets, on garde **ce qu'on a haché**, et la question cesse de se
poser.

Et surtout : **cette issue est le seul moment du projet où le site source existe encore.**
L'abonnement IONOS sera résilié précisément parce que le nouveau site existe. Une capture qui ne
retiendrait que des adresses ferait dépendre la contrainte 4 de la survie d'un service que personne
ici ne contrôle.

## 3. Avertissement — ce que ces fichiers contiennent, et qui n'est pas un secret

Le HTML de gabarit d'IONOS contient, en clair :

- la **clé d'API Google Maps d'IONOS** (`AIzaSy…`), déjà visible en clair dans
  `../chiens/chien-pegaz.md` depuis le lot 6 ;
- des **liens de connexion à l'éditeur IONOS** (`login.1and1-editor.com`,
  `124.sb.mywebsite-editor.com`).

**Ce ne sont pas des secrets du projet.** Ce sont les clés d'IONOS, servies à tout visiteur de
n'importe quelle page publique de `mtbrabant.com`. Les versionner ne les expose pas davantage
qu'elles ne le sont déjà, et le projet n'a aucun moyen — ni aucune raison — de les changer.

En revanche, **l'analyse de secrets de GitHub peut les signaler**. C'est écrit ici une fois, noir sur
blanc, pour que personne n'ait à rouvrir la question dans six mois : une alerte sur `AIzaSy…` dans ce
dossier est attendue et sans objet.

## 4. Limite de méthode — la réduction ne porte presque rien du `<head>`

**À lire avant de traiter un `.md` de ce dossier comme complet.**

La convention gelée au contrat `docs/contracts/issue-19.md` §3.1 découpe **cinq zones**, et les cinq
sont des conteneurs du **`<body>`** : `diywebEmotionHeader`, `diywebMain`, `diywebSecondary`,
`diywebSidebar`, `diywebFooter`. `outils/reduire.py` n'en sort pas.

**Du `<head>`, une seule chose atteint un `.md` : le `<title>` du document**, et seulement dans
l'en-tête du §3.3, jamais dans le corps. **Tout le reste du `<head>` est hors du champ de la
convention** et n'est donc archivé que dans le `.html` :

| Métadonnée du `<head>` | Présente sur | Dans un `.md` ? |
|---|---|---|
| `<title>` | 53 pages servies | **oui**, en-tête §3.3 |
| `name="robots"` | **58 balises** — voir `RELEVE.md` | **non** |
| `name="description"`, `name="keywords"` | 53 pages, valeurs propres à la page | **non** |
| `property="og:*"` (`og:title`, `og:description`, `og:image`, `og:url`, `og:type`) | 53 pages | **non** |
| `property="business:contact_data:*"` (adresse, localité, code postal, téléphone, courriel) | 53 pages | **non** |
| `name="generator"`, `viewport`, `format-detection`, `Content-Type` | 53 pages | **non** |

**Ce que cette limite a déjà coûté une fois.** Cinq pages — `chien-halan`, `chien-ray-ban`,
`chien-roxane`, `chien-youry`, `placement` — portent un `robots` à `noindex, nofollow` que les 48
autres n'ont pas. Le fait était dans les octets archivés depuis le premier jour, et **aucun livrable
de l'issue #19 ne le mentionnait** : il est hors des cinq zones, donc invisible à qui ne lit que les
`.md`. Il a fallu la revue du lot 7 pour le voir. Voir `RELEVE.md` et `../INVENTAIRE.md` §10,
anomalie 4.

**Règle qui en découle, opposable à #20, #21 et #24** : la règle de préséance du §1 — « le `.md` est
ce qu'on lit, le `.html` tranche en cas de doute » — **ne vaut que pour le contenu rédactionnel**.
Pour tout ce qui relève de la **métadonnée de page** — indexation, description de référencement,
partage social, coordonnées de l'en-tête — **le `.md` ne dit rien du tout, et son silence n'est pas
une absence dans le source.** La seule lecture valable est le `.html`, ou une nouvelle passe
d'extraction dédiée.

**Aucun `.md` n'est réécrit pour combler cette limite** (contrat §9) : elle est **déclarée**, pas
corrigée — décision 46 d'`ETAT.md`.
