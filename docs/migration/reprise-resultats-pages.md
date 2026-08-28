# Reprise des résultats de travail et des pages libres — issue #21

Ce que la reprise a repris de `mtbrabant.com`, ce qu'elle a **délibérément laissé vide**, et les
**écarts** qu'elle assume. Écrit pour être lu par quelqu'un qui n'était pas là — une revue,
l'éleveuse, ou la personne qui reprendra ce dépôt.

Le contrat gelé est `docs/contracts/issue-21.md`. Les fichiers de données transcrits vivent dans
`wp-content/plugins/mtb-core/includes/migration/resultats-pages/donnees/`, et le comparateur qui les
confronte à la capture dans `.../resultats-pages/concordance/concordance.py`.

**Pourquoi ce document existe.** Le reste écrit de l'issue #17 n'était pas « le contenu est faux » :
c'était que son contenu **ne vivait que dans une base de développement**, aux identifiants 128-135,
et devenait donc invérifiable par quiconque une fois cette base détruite. Un contenu qui n'existe que
dans une base n'est pas repris. Ici, tout est en fichiers versionnés, et **rejouable**.

---

## Ce qui est repris

| | Compte |
|---|---|
| Résultats de travail | **61 / 61** |
| Pages libres | **7 / 7** |
| Entrées de composition (blocs + écarts) | **48** — 28 blocs, 20 écarts |
| Paragraphes de prose recopiés | **59** |
| Correspondances chien confirmées | **1** sur 61 lignes |
| Photographies téléversées | **0** — téléversement différé, voir plus bas |

### Les 61 résultats

**61 = 57 lignes de tableau + 4 lignes « Autres disciplines »**, recompté trois fois de façon
indépendante (lead, plan back, note front), puis une quatrième fois par le comparateur, qui **échoue
si le compte change**.

| Discipline (clé stockée) | Libellé rendu | Lignes |
|---|---|---|
| `ring` | RING | 22 |
| `igp_rci` | IGP / RCI | 4 |
| `mondioring` | Mondioring | 4 |
| `obeissance` | Obéissance | 19 |
| `pistage` | Pistage | 3 |
| `recherche_utilitaire` | Recherche utilitaire | 4 |
| `sauvetage` | Sauvetage | 1 |
| `autres_disciplines` | Autres disciplines | 4 |
| **Total** | | **61** |

La neuvième discipline de la liste gelée, `truffe`, **n'a aucune ligne** : elle n'apparaît donc ni en
titre, ni en tableau, ni en légende. C'est l'état `discipline_vide`, et c'est le comportement voulu.

**L'ordre du fichier est l'ordre de la source, et ce n'est pas cosmétique.**
`includes/query/resultat/interne.php:503` départage deux lignes de même année **par identifiant de
contenu**, donc par ordre de création. L'import crée dans l'ordre du fichier : l'ordre du site source
est reproduit sans qu'aucun champ de tri n'ait été inventé.

### Les 7 pages

| Référence | Titre | Statut | Composition | Paragraphes |
|---|---|---|---|---|
| `bhpl` | BHPL | `publish` | 1 bandeau + 6 fiches, 3 écarts | 38 |
| `travail` | Travail | `publish` | 1 bandeau + 2 fiches + 1 tableau de résultats, 11 écarts | 17 |
| `placement` | Placement | `publish` | 1 bandeau + 1 fiche + liste de portées + encart d'appel, 1 écart | 1 |
| `mentions-legales` | Mentions légales | `publish` | 4 fiches + coordonnées et plan, 2 écarts, **pas de bandeau** | 3 |
| `litterature` | Littérature | `publish` | bandeau seul, 1 écart | 0 |
| `bhpl-en-france` | BHPL en France | `draft` | charpente seule : 1 bandeau + 2 fiches + 1 galerie, 1 écart | 0 |
| `politique-de-confidentialite` | Politique de confidentialité | `draft` | charpente seule : 3 fiches, 1 écart | 0 |

Trois de ces pages ne portent aucune prose reprise, et **chacune pour une raison écrite** :

- **`litterature` est publiée et vide.** La zone `Contenu principal` du HTML reçu (200, 35 541 octets)
  est présente et **ne porte aucun texte**. La source publie une page vide ; on publie une page vide.
  D11 est tenue, D4 n'est pas manquée.
- **`bhpl-en-france` est en brouillon.** La source répond **302** vers `/protected/…`, corps de
  **zéro octet** : la page est protégée par mot de passe et n'a pas pu être capturée. Publier une
  coquille vide donnerait au public une page que la source ne lui montre pas.
- **`politique-de-confidentialite` est en brouillon.** La source répond **404** et la page est absente
  du plan du site. Les 31 462 octets reçus sont le gabarit d'erreur d'IONOS, pas un contenu
  rédactionnel : l'écart de sa composition les réclame et dit pourquoi. **Aucune durée de
  conservation, aucun hébergeur, aucun responsable de traitement n'ont été inventés.**

### Trois pages sont hors périmètre, et c'est une décision mesurée

| Page | Pourquoi elle n'est pas ici |
|---|---|
| **Accueil** | `docker/provision/provision.sh:192` **réécrit son `post_content` à chaque provisionnement**, et `provision.sh` est hors empreinte. Toute reprise y serait effacée au provisionnement suivant. Sa moitié basse est en outre une grille de six reproducteurs que `mtb/grille-chiens` **calcule** depuis les fiches importées par #20. |
| **Contact** | Même cause, `provision.sh:214`. |
| **La meute** | Domaine `chiens` : elle appartient à l'issue **#20**, pas à celle-ci. |

Vérifié ligne à ligne : `provision.sh` **ne nomme, ne crée, ne met à jour et ne supprime aucune** des
sept pages ci-dessus. Sa ligne 258 (`search-replace` sur `wp_posts`) les balaie mais ne substitue
qu'un littéral absent de toute capture : **elle les touche sans rien y changer.**

---

## Ce qui fait foi

> **Le fichier de données fait foi pour l'écriture. La capture fait foi pour la vérité.**
> Une divergence se corrige **toujours** dans le fichier de données, **jamais** dans la capture.

`docs/migration/source/**` est une **archive en lecture seule absolue**. Rien de cette reprise n'y a
été touché, et rien ne doit y être « corrigé » : le jour où la transcription et la capture ne disent
plus la même chose, c'est la transcription qui a tort.

La source qui fait autorité pour les 61 résultats est la **re-dérivation** du HTML archivé :

```
python docs/migration/source/outils/reduire.py docs/migration/source/html/travail.html
```

et **non** `docs/migration/source/pages/travail.md`, dont la seule cellule à contenir un lien (la
ligne « Road Trip », une sur 207) est éclatée en trois. `docs/migration/source/travail.md` (passe #16)
est **inutilisable** : il éclate chaque cellule, et 57 lignes de résultats y perdent leur association
année / chien / niveau.

**Le comparateur a été écrit AVANT la première ligne de donnée**, et la transcription s'est faite
jusqu'à ce qu'il se taise. C'est l'ordre imposé par le contrat §14, et il n'est pas décoratif : écrit
après les données, un comparateur est calibré pour valider ce qui existe.

---

## Mode d'emploi

### Vérifier la fidélité des données à la capture — hors ligne, sans base

Le comparateur **n'écrit rien**, ne touche à aucun fichier, n'ouvre aucune connexion et n'a besoin
d'aucune pile démarrée. Il se lance **depuis l'arbre de travail du dépôt**, jamais depuis un
conteneur : `docs/` n'est monté dans aucun d'eux (`compose.yaml:85-89`).

```
cd wp-content/plugins/mtb-core/includes/migration/resultats-pages
PYTHONIOENCODING=utf-8 python concordance/concordance.py
PYTHONIOENCODING=utf-8 python concordance/concordance.py --silencieux   # échecs et conclusion seuls
```

`PYTHONIOENCODING=utf-8` **n'est pas facultatif sous Windows** : le rapport cite des lignes de la
source, glyphes `♂` et `♀` compris, et `cp1252` ne les connaît pas. Le comparateur pose lui-même
cette variable dans l'environnement du processus enfant qu'il lance (`reduire.py`) et reconfigure ses
propres flux ; la poser aussi au lancement met à l'abri d'un environnement exotique.

**Sortie 0** s'il n'existe aucune divergence inexpliquée, **1** sinon.

> **Pourquoi un fichier Python dans une extension WordPress.** C'est une anomalie assumée, et la
> raison est gravée en tête du fichier pour qu'aucune passe de refactorisation ne le « range » en PHP
> et ne le rende inexécutable : il n'y a **pas de binaire `php` sur la machine de développement**,
> `docs/` **n'est monté dans aucun conteneur**, et la **décision 34** interdit `docker compose run`
> sur `wpcli`. Un comparateur en PHP n'aurait aucun endroit où tourner. Dette **T-#21-b**.

### Importer

```
wp mtb reprise-resultats-pages --simuler                  # déroule tout, n'écrit rien
wp mtb reprise-resultats-pages --user=admin               # l'import réel
wp mtb reprise-resultats-pages --verifier                 # compare la base aux fichiers, n'écrit rien
wp mtb reprise-resultats-pages --raccrocher --user=admin  # pose les liens chien manquants
```

Options de chemin, toutes facultatives : `--resultats=<fichier>`, `--pages=<dossier>`,
`--correspondances=<fichier>` (par défaut, ceux livrés avec l'extension) et `--photos=<dossier>`, qui
**n'a délibérément aucune valeur par défaut**.

| Mode | Écrit ? | Ce qu'il fait |
|---|---|---|
| *(sans option)* | oui | Crée le contenu absent. **Ne met jamais à jour un contenu existant, ne supprime jamais rien.** |
| `--simuler` | non | Le même déroulé, contrôles compris, sans une écriture. |
| `--verifier` | non | Confronte la base aux fichiers et **signale** les écarts. Une divergence sur une page peut parfaitement être une correction de l'éleveuse : rien n'est réécrit. |
| `--raccrocher` | oui | Idempotent, lançable après #20. N'écrit que sur les résultats dont le lien chien vaut **actuellement 0** : un lien posé à la main n'est jamais écrasé. |

**`--user=admin` est obligatoire pour écrire.** Sans utilisateur, WP-CLI laisse `wp_filter_post_kses`
s'accrocher, et son `preg_replace( '/--+/', '-' )` s'applique **au contenu des commentaires de
blocs** : le balisage des sept pages serait détruit en silence. La commande **refuse alors d'écrire**
et affiche la commande exacte à relancer.

### Séquence de recette

1. `python concordance/concordance.py` → **0**.
2. `wp mtb reprise-resultats-pages --simuler`.
3. `wp mtb reprise-resultats-pages --user=admin`.
4. `wp mtb reprise-resultats-pages --verifier` immédiatement derrière → **0**.
5. **Ouvrir les sept pages dans l'éditeur** : zéro « Ce bloc contient du contenu inattendu ». *C'est
   le seul contrôle qu'aucun script ne peut faire — voir « Ce qui n'a pas été vérifié ».*
6. `wp mtb reprise-resultats-pages --raccrocher --user=admin`.

> ### Le piège opérationnel, et il faut le lire avant de lancer quoi que ce soit
>
> **Sur la base de développement actuelle, les sept pages EXISTENT DÉJÀ** — ce sont celles saisies à
> la main par l'issue #17, identifiants 128 à 135. L'import les trouvera par leur référence, les
> comptera **« déjà présentes »**, et **n'écrira strictement rien**. Ce n'est pas une panne : c'est
> la garantie que le travail de l'éleveuse n'est jamais écrasé par un outil qu'elle ne voit pas.
>
> **Pour observer la reprise, il faut détruire la base :** `docker compose down -v`, puis
> reprovisionner. **Ce cycle est destructeur** — tout ce qui n'est pas en fichier est perdu. À
> annoncer avant de le lancer.

### Un avertissement attendu au premier import sur une base provisionnée

`docker/provision/provision.sh:226-230` sème **5 résultats de démonstration** (affixe « de
Démonstration », références `demo-…`) dans le **même type de contenu** que les 61 réels. La page
`/travail/` **mélangera les deux jeux**, et l'ordre à année égale en dépend. La commande **les
détecte et les nomme ; elle ne supprime jamais rien** — supprimer du contenu qui n'est pas le sien
est hors de tout mandat. Le geste correct est `docker compose down -v`.

---

## La preuve, avec ses chiffres

Le contrôle de concordance **ne parse jamais la capture pour en tirer des champs** : le parsing est un
excellent vérificateur et un mauvais scribe. Dans un importeur, une erreur d'expression régulière
écrit un fait faux en base, en silence, sur un site qui répond 200 ; dans un comparateur, la même
erreur produit une ligne de rapport qu'un humain lit.

La chaîne de preuve a trois maillons, et c'est leur enchaînement qui la rend **non circulaire** :

```
champs transcrits  →  source.ligne déclarée  →  re-dérivation du HTML archivé
```

### Résultat de la dernière exécution, sur les données livrées

```
Concordance : aucune divergence inexpliquée.        (code de retour 0)
```

| Contrôle | Ce qu'il exige | Résultat |
|---|---|---|
| **1 — provenance des pages** | La concaténation, dans l'ordre, des `source` de **toutes** les entrées de composition (blocs **et** écarts) est **strictement égale**, caractère pour caractère, à la zone re-dérivée | **7 / 7.** `bhpl` 153 lignes, `travail` 241, `politique-de-confidentialite` 103, `mentions-legales` 22, `placement` 7 ; `bhpl-en-france` zone absente et `litterature` zone vide, composition ne réclamant rien |
| **1b — provenance des résultats** | Chaque `source.ligne` des 61 figure dans la zone re-dérivée, à des positions **strictement croissantes** | **61 / 61**, dans l'ordre |
| **1c — rattachement à la discipline** | La discipline déclarée est celle de **l'en-tête de tableau qui gouverne la ligne** dans la source | **61 / 61** |
| **2 — consommation de caractères** | Tout caractère de la ligne est soit consommé par un champ transcrit, soit un séparateur d'une liste **déclarée et close** | **61 / 61 entièrement consommées** |
| **3 — les deux niveaux parenthésés** | `IGP 3 (Finland)` et `Brevet Maitre Chien Drogue (Suisse)` sont les **seuls** niveaux finissant par une parenthèse, et `pays` est vide sur les 61 | conforme |
| **4 — espaces insécables** | Le compte d'U+00A0 du corps réduit est confronté au relevé de capture | **7 / 7** : 38 · 0 · 1 · 136 · 3 · 1 · 1 |
| **5 — condensés** | Le `sha256` déclaré par chaque page est le condensé **stable** du HTML archivé qu'elle cite | **7 / 7** |
| **6 — périmètre** | 61 résultats, la répartition du contrat §2, et la correspondance chien justifiée | conforme |

**Un écart délibéré n'est jamais une exemption.** C'est une entrée de composition qui **réclame des
lignes exactement comme un bloc**, sans rien produire, et porte sa raison à côté des lignes qu'elle
couvre. **Il n'existe aucune liste globale de suppression.** Conséquence structurelle : une ligne que
personne ne réclame casse l'égalité stricte du contrôle 1, donc fait échouer le contrôle. **Il n'y a
littéralement aucune façon d'omettre une ligne sans écrire pourquoi.**

### Les quatre transformations déclarées, attendu contre constaté

Une transformation qui se déclenche **zéro fois** fait échouer le comparateur : une transformation
morte est le signe que la source a changé.

| Transformation | Attendu | Constaté |
|---|---|---|
| **A3** — blanc de bord rogné (ASCII **et** U+00A0), aux deux bords, toute U+00A0 intérieure préservée | > 0, **listé exhaustivement** dans le rapport | **28** |
| **§9.1 / §9.2** — en-tête de tableau converti en clé de discipline | 61 | **61** |
| **§9.11** — balisage `[LIEN …]` d'une cellule, non repris | 1 (la seule cellule sur 207 à contenir un lien) | **1** |
| **§9.3** — glyphe `♂` / `♀` converti en clé de sexe | 61 | **61** |

Le rognage A3 est une **modification de la capture**, et c'est assumé : il est **compté et listé
ligne par ligne** dans le rapport du comparateur. Sa justification est au contrat (arbitrage A3) —
un blanc de bord n'est pas un fait d'élevage, c'est un artefact de cellule IONOS, et `niveau` comme
`chien_nom` sont deux des quatre membres du **tuple d'identité** : un caractère invisible y
fabriquerait des doublons fantômes et casserait `--verifier` à jamais.

### La recette d'acceptation : le comparateur doit ÉCHOUER sur des données vides

C'est elle qui prouve qu'il n'a pas été calibré pour valider ce qui existe. Elle a été exécutée
**avant** que la première ligne de donnée n'existe, puis rejouée après.

| Cas | Code attendu | Code obtenu |
|---|---|---|
| Dossier `donnees/` absent | 1 | **1** — 9 échecs « fichier absent … la reprise n'existe pas », plus 0 / 61 et les 4 transformations mortes |
| `resultats.json` à `[]`, les 7 pages à `{}` | 1 | **1** — 5 échecs d'égalité stricte, 7 « source absente », répartition 0/22, 0/4, 0/19… |
| Données livrées, intactes | 0 | **0** |
| Un écart de `travail` privé de sa `source` | 1 | **1** — la page et l'index de l'entrée sont nommés |

Six pièges ont en outre été injectés dans une copie des données, et **chacun a été attrapé** :

| Piège injecté | Attrapé par |
|---|---|
| `Ferrari` versé dans **Conducteur** | contrôle 2 — le reliquat `Prop.` n'est ni consommé ni déclaré |
| `pays = "Finland"` et niveau amputé de sa parenthèse | contrôle 2, puis contrôle 3 deux fois |
| Deux lignes interverties | contrôle 1b — l'ordre du fichier n'est plus celui de la source |
| Trois lignes de Pistage rangées en RING | contrôle 1c, puis la répartition du §2 |
| Une `source.ligne` fabriquée | contrôle 1b et contrôle 2 |
| Frontière nom / niveau déplacée | contrôle des frontières de champ |

Le tout dernier mérite un mot, parce qu'il est allé **au-delà de la lettre du contrat**. Verser
`Dixie du Mont Brabant : Qual. Chien de sauvetage` **entier** dans le nom du chien ne perd aucun
caractère et ne laisse aucun reliquat : le contrôle 2 seul le valide. Un contrôle supplémentaire
exige donc que le **délimiteur déclaré de la famille** (`|` en tableau, `:` hors tableau) se trouve
**entre** deux champs et **jamais à l'intérieur** de l'un d'eux. Il a effectivement attrapé trois
erreurs de ce type pendant la mise au point.

---

## Ce qui n'a PAS été vérifié

Trois choses, et elles sont écrites ici plutôt que dans un rapport que personne ne relira.

1. **La commande d'import n'a jamais tourné.** La pile n'était pas démarrée au moment de
   l'implémentation, et la décision 34 interdit `docker compose run` sur `wpcli`. Ne sont donc **pas**
   éprouvés en conditions réelles : l'enregistrement `WP_CLI::add_command`, la lecture du registre des
   types de blocs, `get_page_by_path`, les `meta_query` du tuple d'identité, et `media_handle_sideload`.
   Ce qui **a** été éprouvé : les 14 fichiers PHP passent `php -l` sans une erreur (PHP 8.1, la cible
   de l'extension), et le sérialiseur de blocs a été confronté au **vrai** parseur et au **vrai**
   sérialiseur du cœur de WordPress 6.5 — les sept pages composées vérifient
   `serialize_blocks( parse_blocks( $c ) ) === $c`, ne contiennent entre les délimiteurs d'une fiche
   **rien d'autre que des commentaires de blocs et des `<p>`**, n'émettent ni `core/list` ni
   `core/heading`, ne laissent survivre ni `mtbrabant.com`, ni `[IMAGE`, ni `[IFRAME`, ni `[LIEN`, et
   **chaque `<a>` ne porte que son `href`**.

2. **Le validateur de blocs est CLIENT. Aucun contrôle serveur ne le remplace.** Trois contrôles
   existent, et aucun ne subsume les autres : avant écriture, l'aller-retour sur le balisage composé ;
   après écriture, le même sur le contenu **relu en base** (il attrape un `wp_slash()` manquant et un
   filtrage kses) ; **à l'écran**, l'ouverture des sept pages dans l'éditeur. **La troisième reste à
   faire**, et on le dit plutôt que de faire semblant.

3. **L'échappement des nœuds de texte est la source d'invalidation la plus plausible s'il en reste
   une.** Il est calqué sur `escapeHTML` de `@wordpress/escape-html` : l'esperluette non déjà écrite
   en entité et le chevron ouvrant sont échappés, **le chevron fermant ne l'est pas** — pour que les
   nombreux `=>` de la source survivent tels quels. Ce choix n'est vérifiable qu'à l'étape 2
   ci-dessus. Trois autres points de rendu qui n'ont pas de contrôle automatique : la position exacte
   des espaces insécables de fin de paragraphe, les apostrophes typographiques, et le repli mobile du
   tableau sous 48 rem.

---

## Les 15 écarts de rendu déclarés

Ils sont énumérés au **contrat `docs/contracts/issue-21.md` §9**, et ne sont pas recopiés ici : un
fait recopié à deux endroits diverge tôt ou tard. **Aucun d'eux ne perd un fait**, et un écart non
écrit n'est imputable à personne — c'est pourquoi ils sont écrits.

Le principe qui les gouverne : **la page Travail ne porte aucun tableau recopié.** La prose est
recopiée, **le tableau est calculé** par `mtb/tableau-resultats` depuis les 61 résultats importés.
D'où l'ordre des disciplines qui devient celui de la liste gelée, « Autres disciplines » qui passe
d'avant tous les tableaux à après, les quatre lignes de ce groupe réordonnées en 2019 · 2018 · 2012 ·
2010, les intertitres en `<strong>` promus en `<h2>`, et le nom de la discipline qui sort du `<th>`
pour devenir un `<h2>` plus une légende masquée.

**Deux écarts perdent quelque chose de visible, et il faut les nommer séparément.**

### Le sexe des 61 lignes n'est jamais affiché — dette T-#21-i

Les `♂` et `♀` de la source **ne sont pas remplacés** par « Mâle » et « Femelle » : ils **ne sont pas
rendus du tout**. Vérifié : **zéro occurrence de `sexe`** dans `includes/blocks/tableau-resultats/`,
alors que la fonction de lecture expose bel et bien `sexe` et `sexe_libelle`.

La donnée, elle, est **stockée fidèlement** dans `_mtb_sexe` sur les 61 lignes, et la lecture
l'expose. **C'est l'affichage qui manque**, et il appartient au composant de l'issue #15, hors de
cette empreinte. **Perte visible sur les 61 lignes.** Une donnée saisie, exposée par la lecture, et
imprimée par personne : c'est exactement la forme de défaut qu'il ne faut pas laisser passer en
silence.

### Le lien externe de la ligne « Road Trip » disparaît

La source écrit, dans la seule cellule sur 207 à contenir un lien :

```
2024 | ♂ [LIEN href=https://www.sports-canins.net/clubs/ficheChien.php?IDchiens=5019312]Road Trip du Mont Brabant[/LIEN] | Selectifs 2025
```

Le nom `Road Trip du Mont Brabant` est repris **verbatim**. Le lien, lui, **n'est pas repris** :
`cellule_chien()` ne pose un `<a>` que vers une fiche **du site**, et aucun champ d'URL externe
n'existe sur un résultat de travail. Le fait « ce chien a une fiche sur sports-canins.net » **n'est
donc plus visible nulle part sur le nouveau site.** L'écart est déclaré, la transformation est
comptée par le comparateur (une fois, exactement).

---

## Q23 — la page Placement est importée publiée et indexable, et Q23 n'est PAS tenue

**À lire avant toute clôture de lot.**

Le site source retire **Placement** de tous ses menus **et** la marque « ne pas indexer » : vérifié à
l'octet, `docs/migration/source/html/RELEVE.md:53` — `placement.html` porte **deux** balises
`robots`, un `noindex, nofollow` **en tête de son `<head>`**, puis un `index,follow`. Elle est l'un
des cinq seuls fichiers de l'archive dans ce cas ; les quatre autres sont des fiches de chien, et
appartiennent à l'issue #20.

**Ce que cette issue a fait :** elle a importé la page comme une **page ordinaire**, en `publish`,
**indexable**, et elle consigne le fait ici.

**Ce qu'elle n'a pas fait, et ne pouvait pas faire :** le `noindex` **n'est pas implémentable depuis
cette empreinte**. WordPress n'a aucun réglage natif pour retirer une page seule de l'indexation ; il
faut un filtre `wp_robots`, plus l'exclusion du plan du site. C'est du **comportement public
permanent de référencement**, et cela appartient à l'issue **#24**.

> **Un lot qui se clôturerait en déclarant Q23 tenue se clôturerait sur un silence.**
> Le fait est **stocké et écrit** ; il n'est **pas honoré**. Tant que #24 n'a pas tourné, la page
> Placement est indexable alors que ce dépôt affirme que la source ne le veut pas. **Un fait consigné
> et non honoré est pire qu'un fait absent**, parce qu'il donne l'impression que la question est
> réglée.
>
> C'est exactement la forme de défaut qui a bloqué les lots 6 et 7 : une case cochée sur un travail
> qui n'était pas fait. Ici la case reste **décochée**, en toutes lettres.

Note de cohérence : la reprise des chiens (#20) a tranché le même point pour ses quatre fiches, et
aboutit à la même dette ouverte. Les deux se règlent d'un coup dans #24.

---

## Les faits d'élevage sur lesquels la chaîne a buté — non comblés

Aucun n'a été deviné, aligné ni reformulé. Pour chacun : ce que dit la source, ce qui a été fait, ce
qui reste à demander.

### 1. `Pegaz Eenhoorn` (tableau) contre `Pégaz Eenhoorn` (fiche)

**La source** : le tableau RING 2021 écrit `Pegaz Eenhoorn`, sans accent ; la fiche de la meute écrit
`Pégaz Eenhoorn`, avec accent. Un accent d'écart, très probablement le même chien.

**Fait** : le nom du tableau est repris **verbatim**, et **aucun lien** n'est posé vers la fiche.
« Très probablement » est un jugement, jamais une fonction de normalisation.

**À demander** : est-ce le même chien ? Si oui, une ligne dans
`donnees/correspondances-chiens.json` et un `--raccrocher` suffisent.

### 2. `Tina du Hameau des Trois Fontaines` (IGP 2005) contre la fiche `Tina dit Tara du H3F`

**La source** : deux graphies, dans deux pages différentes. `H3F` **ressemble** à l'abréviation de
« Hameau des Trois Fontaines ».

**Fait** : aucun lien. **Ressembler n'est pas être**, et l'abréviation n'est développée nulle part
dans la source.

**À demander** : même chienne ?

### 3. `Mines d'Odiles` contre `Mines d'Odile` — dette T-#21-n

**La source** : la **même page** écrit `Hardy des Mines d'Odiles` (2016, Pistage) et
`Hekla des Mines d'Odile` (2018, Recherche Utilitaire). Même élevage, deux graphies, un `s` d'écart.

**Fait** : les deux graphies sont reprises **telles quelles**. Aucune n'est alignée sur l'autre.

**À demander** : laquelle est la bonne ? Ni le brief ni aucune revue ne l'avaient relevé.

### 4. `- Prop. Ferrari` est un propriétaire, pas un conducteur

**La source** : `2018 - ♀ H'Alix du Domaine de Drenthe : Cavage Classe B ChF - Prop. Ferrari`.

**Fait** : la mention reste **verbatim dans le niveau**, qui vaut
`Cavage Classe B ChF - Prop. Ferrari`. Le champ **Conducteur reste vide**. Le contrôle 2 du
comparateur rend l'erreur inverse **mécaniquement impossible** : verser `Ferrari` dans Conducteur
laisserait le reliquat `Prop.` ni consommé ni déclaré, et ferait échouer la vérification. Testé.

**Le conducteur est vide sur les 61.** Ce n'est **pas** une omission de la reprise : c'est l'état de
la source. La colonne Conducteur n'apparaît donc sur aucun groupe.

### 5. Le pays reste vide sur les 61, y compris sur les deux niveaux parenthésés

**La source** écrit `IGP 3 (Finland)` et `Brevet Maitre Chien Drogue (Suisse)`. Ce sont les **deux
seuls** niveaux de toute la page à finir par une parenthèse — et le comparateur **échoue si un
troisième apparaît**.

**Fait** : le niveau reste **verbatim, parenthèse comprise**, et le champ **Pays reste vide sur les
61**. La raison est un renversement assumé : un pays vide ne signifie pas « inconnu » mais **« pas
obtenu à l'étranger »**. Le tableau IGP a **quatre** lignes et une seule porte « (Finland) » :
renseigner celle-là **ferait apparaître la colonne Pays** sur ce tableau, et donnerait donc aux trois
autres une cellule vide, c'est-à-dire l'affirmation **« obtenu en France »** — trois affirmations que
la source ne fait pas, fabriquées pour en éviter une. Laissé vide partout, **le site n'énonce aucune
revendication de pays**, et le visiteur lit les mots mêmes de la source dans le niveau.

**À demander** : faut-il un champ structuré pour le pays ? La sémantique fausse reste aujourd'hui
confinée au modèle de données, invisible du visiteur.

### 6. Vingt-deux niveaux distincts, et des abréviations que la source ne développe jamais

**La source** : les 61 lignes portent **22 valeurs de niveau distinctes**, dont
`Acc2`, `Certificat IFH-V`, `Certificat RCI`, `Classe 1 EXC`, `Classe 3 EXC`, `Niveau 2 EXC`,
`Pistage F Niv.1 EXC`, `Cavage Classe B ChF`, `Qual. Chien de sauvetage`, `Agility 3ème au GPF`.

**Fait** : toutes reprises **au caractère près**, aucune développée, aucune harmonisée — ni la casse,
ni les points d'abréviation, ni `Niv.` contre `Niveau`.

**À demander** : faut-il un glossaire pour le visiteur ? C'est une décision éditoriale, pas une
correction de donnée.

### 7. `Qual. Chien de sauvetage` (2012, Dixie) et le tableau Sauvetage de la même page

**La source** : la ligne figure sous « Autres disciplines », alors qu'un tableau **Sauvetage** de la
**même page** porte `2009 | ♀ Dixie du Mont Brabant | Brevet`. Même chienne, deux années, deux
emplacements.

**Fait** : **aucun rapprochement.** Les deux lignes sont importées là où la source les met, l'une en
`autres_disciplines`, l'autre en `sauvetage`.

### 8. Le site source se contredit avec lui-même, sur les Mentions légales

**La source** : la page Mentions légales écrit `ROUTE DE SALERNES` **sans numéro de voie**,
`680505619` à **neuf** chiffres, un `tel:-680505619` avec un tiret parasite, et `Fabienne Gueneau`
**sans accent** — quand la page Contact du même site écrit `3060 ROUTE DE SALERNES` et son propre
pied de page `© Fabienne Guéneau`.

**Fait** : « Siège social » et « Contact » **ne sont pas recopiés**. Ils sont rendus par le composant
`mtb/coordonnees-plan`, depuis la source unique des coordonnées. **Rien n'est harmonisé ni corrigé** :
la contradiction est citée ici, avec sa référence. En revanche **`Elevage du Mont Brabant` et
`SIRET : 82237792500018` sont repris verbatim** — ce sont des faits que le nouveau site ne portait
nulle part, et leur absence était l'un des restes écrits de #17.

**Conséquence assumée** : l'adresse, le téléphone et le courriel affichés **diffèrent des caractères
de la page source** (dix chiffres contre neuf, numéro de voie présent, accent présent).

### 9. Huit liens sortants en `http://`, dont un avec un `?fbclid=` — dette T-#21-f

**La source** : sur les **11 liens sortants** recopiés dans la prose des sept pages, **8 sont en
`http://`** et 3 en `https://`. L'un d'eux, vers `belgiandogs.be`, porte un
**`?fbclid=IwAR3TzncALyr_TQYKKdH4AEqqLw13v0ZEUX5ajLP-8OALpyPw63WQDPQ0z1Y`**.

**Fait** : les onze sont recopiés **tels quels**. Ni le passage en `https://`, ni le retrait du
paramètre de suivi : **modifier une URL, c'est modifier un fait**. Aucune requête n'est faite au
chargement de la page, D6 tient — ce sont des liens, pas des ressources chargées.

**À demander** : faut-il nettoyer le `?fbclid=` et tenter le `https://` ? Les deux changent une URL
que la source écrit.

### 10. Une phrase de la page BHPL s'arrête au milieu

**La source**, section Santé : `=> L'hypothyroïdie : Des recherches ont été effectué avec le
Professeur Siliart de l'université de Nantes. Un protocole de recherche a été mis en place` — **sans
point final, et sans suite**.

**Fait** : recopiée **exactement ainsi**, coupure comprise, faute d'accord comprise. Rien n'a été
complété.

**À demander** : la fin de la phrase.

Sur le même principe, restent verbatim dans les sept pages : « le le Berger Hollandais », « portie »,
« intérgrité », « responsabiité », « espèrer ». **Aucune orthographe n'a été corrigée.**

### 11. Les cinq saillies projetées 2026 → 2028

**La source** nomme des chiens réels dans des saillies **à venir**, sur l'Accueil et sur BHPL. Elles
**périment**.

**Fait** : recopiées au caractère près, jamais reformulées, jamais datées autrement. Celles de
l'Accueil sont hors périmètre (voir plus haut) ; celle de BHPL est reprise dans cette passe.

### 12. Les trois photos n'ont ni texte alternatif ni nom de fichier lisible — dette T-#21-a

**La source** : `16497476.png` (page Travail, **1 002 317 octets**), `6412830.jpg` et `7128435.png`
(page BHPL). Sur les **192 photos de l'archive**, 126 n'ont aucun attribut `alt`, 64 l'ont vide,
**2 seulement portent un nom lisible** — et aucune des trois n'en fait partie.

**Fait** : **téléversement DIFFÉRÉ**, et c'est un manque déclaré, pas une case cochée. Les trois
lignes d'image de la capture sont **réclamées par un écart** qui écrit la raison ; **aucun octet de
photo n'a été copié dans l'extension** — dupliquer une archive créerait une seconde source de vérité
pour une photographie, et l'engagerait dans git pour toujours. Les trois pages sont composées **sans
emplacement de photo** : l'emplacement n'existe pas, aucun trou ni réserve n'est rendu.

**Deux causes, et il faut les deux pour lever la dette :**

1. `docs/` n'est monté dans **aucun** conteneur (`compose.yaml:85-89`) — une ligne à ajouter, hors de
   cette empreinte ;
2. **le texte alternatif est une question ouverte à l'éleveuse.** Même le montage fait, les photos ne
   pourraient pas être reprises *correctement* : un `alt` inventé est un fait inventé.

**Précision honnête sur l'outillage.** La commande sait chercher des photos dans un dossier fourni
par `--photos` et avertir bruyamment si elles manquent. Mais **aucune des sept fiches de page ne cite
de nom de fichier de photo** : les trois images ne vivent que dans les `source` verbatim des écarts.
Cet avertissement ne se déclenchera donc **jamais** en l'état — ce sont ce document et la dette
T-#21-a qui portent le manque. Le jour où les photos seront versées, il faudra **aussi** ajouter la
clé `photo` aux trois entrées de composition concernées.

Deux des trois sont des **PNG**, et la conversion d'image ne mappe que JPEG vers WebP, délibérément —
dont un PNG de **1 002 317 octets** sur la page Travail. Dette **T-#21-c**, vers `perf`.

---

## Les dettes

Elles sont décrites au **contrat `docs/contracts/issue-21.md` §13 et §15**, et ne sont pas recopiées
ici. Rappel de leur objet et de leur destinataire, pour qu'aucune ne se perde :

| # | Objet | Vers |
|---|---|---|
| **T-#21-a** | Les 3 photos ne sont pas téléversées : `docs/` non monté, et l'`alt` est une question à l'éleveuse | `infra` + éleveuse |
| **T-#21-b** | Un comparateur Python vit dans une extension WordPress. **Ne pas le « ranger » en PHP** | information, gravée en tête du fichier |
| **T-#21-c** | Deux des trois photos sont des PNG, non converties en WebP ; dont un de 1 002 317 o | `perf` / utilisateur |
| **T-#21-d** | `docs/migration/redirections.md` est **périmé**, et le devient davantage | `/lead-mtb` |
| **T-#21-e** | `ETAT.md` et le manifeste des photos disent encore que T12 est un « prérequis dur » : c'est faux | `/lead-mtb` |
| **T-#21-f** | Le lien `belgiandogs.be` porte un `?fbclid=` ; le retirer serait modifier une URL | question utilisateur |
| **T-#21-g** | `--raccrocher` ne distingue pas « #20 n'a pas tourné » d'« un slug fautif » : le message nomme les deux hypothèses | information |
| **T-#21-h** | L'ordre à année égale est l'ordre de création : un résultat rejeté puis réimporté passe en fin d'année | recette |
| **T-#21-i** | **Le sexe des 61 lignes n'est jamais affiché** | issue de suite sur #15 |
| **T-#21-j** | `docs/guide/page-composer-une-page-libre.md` devient partiellement faux dès que les pages existent | `/lead-mtb` |
| **T-#21-k** | Le chiffre de 120 cellules du contrat #15 §4.3 est périmé : ce sera 183 | information |
| **T-#21-l** | La clé `source` en chaîne ne peut pas exprimer « je réclame une seule ligne vide » ; un garde bruyant couvre le cas | issue de suite |
| **T-#21-m** | `docs/migration/source/html/*.html` est stocké en **CRLF** : aucun `sha256sum` naïf ne retrouve une valeur du relevé | `/lead-mtb` |
| **T-#21-n** | `Mines d'Odiles` contre `Mines d'Odile`, deux graphies dans la même page | question à l'éleveuse |

**Deux d'entre elles méritent d'être lues même en diagonale.**

**T-#21-m — le piège des fins de ligne.** `docs/migration/source/html/*.html` est stocké en **CRLF**
(`core.autocrlf=true`, aucun `.gitattributes`) : `travail.html` pèse **64 800 octets sur le disque**
contre **63 623 au relevé de capture** — l'écart vaut exactement 1 177 fins de ligne. **Aucun
`sha256sum` naïf ne retrouvera jamais une valeur du `RELEVE.md`.** `reduire.py` et `concordance.py`
normalisent CRLF → LF avant de calculer ; toute vérification d'octets écrite plus tard sur cette
archive devra faire de même, sans quoi elle ne dira pas la même chose selon la machine — ce qui n'est
plus une vérification.

**T-#21-d — les redirections.** `docs/migration/redirections.md` déclare 7 URL sur 52 et **n'a pas
été mis à jour par cette passe** : il appartient à l'issue #24, avec le reste des 301. Ce document-ci
ne le corrige pas et n'y touche pas. Les sept pages reprises ajoutent autant de correspondances à
établir.

---

## Où se trouve quoi

| | |
|---|---|
| Contrat gelé | `docs/contracts/issue-21.md` |
| Données transcrites | `wp-content/plugins/mtb-core/includes/migration/resultats-pages/donnees/` |
| Comparateur hors ligne | `wp-content/plugins/mtb-core/includes/migration/resultats-pages/concordance/concordance.py` |
| Code de la reprise | `wp-content/plugins/mtb-core/includes/migration/resultats-pages/` |
| Capture de l'ancien site | `docs/migration/source/` — **archive en lecture seule absolue** |
| Relevé de capture (octets, condensés, U+00A0) | `docs/migration/source/html/RELEVE.md` |
| Outil de re-dérivation | `docs/migration/source/outils/reduire.py` |
| Reprise des portées et des chiens | `docs/migration/portees-chiens.md` (issue #20) |
