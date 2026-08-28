# Contrat d'interface — Issue #21 — Import des résultats de travail et des pages libres

**Gelé le 2026-08-28.** Opposable à `dev-back-mtb`, `contenu-mtb`, `refacto-mtb`, `doc-client-mtb`
et à la revue de lot. Une chaîne sœur tourne en parallèle sur **#20** (`portees-chiens/**`).

## 0. Ce qui fait foi

> **Le fichier de données fait foi pour l'écriture. La capture fait foi pour la vérité.**
> Une divergence se corrige **toujours** dans le fichier de données, **jamais** dans la capture.
> **Le comparateur s'écrit AVANT la transcription**, et on transcrit jusqu'à ce qu'il se taise.

Motif de toute l'issue : le reste écrit de #17 n'est pas « le contenu est faux », c'est que
**D11 y est invérifiable par quiconque, une fois la base détruite** — son contenu ne vit qu'en base
de développement, identifiants 128-135. Un contenu qui n'existe que dans une base n'est pas repris.

**Source faisant autorité pour les 61 résultats** : la re-dérivation
`python docs/migration/source/outils/reduire.py ../html/travail.html`, **et non**
`docs/migration/source/pages/travail.md`. Vérifié par le lead en exécutant
`verifier_concordance.py` : **29 zones sur 30 identiques au caractère près, une seule divergence**,
la cellule `2024 | ♂ [LIEN …IDchiens=5019312]Road Trip du Mont Brabant[/LIEN] | Selectifs 2025`
(seule cellule à contenir un lien sur 207), que `pages/travail.md` éclate en trois et que l'outil
associe correctement. `docs/migration/source/travail.md` (passe #16) est **inutilisable** : il éclate
chaque cellule, et 57 lignes de résultats y perdent leur association année / chien / niveau.

## 1. Empreinte

**Écriture** : `wp-content/plugins/mtb-core/includes/migration/resultats-pages/**`, et rien d'autre.

**Ajouts hors empreinte, limitativement, et en fichiers neufs uniquement** :
`docs/contracts/issue-21.md` · `docs/migration/reprise-resultats-pages.md` ·
`docs/guide/page-ce-qui-a-ete-repris-de-l-ancien-site.md`.

**Lecture seule absolue** : `includes/migration/import-fixtures/**` · `includes/migration/portees-chiens/**` ·
`wp-content/themes/mtb/**` (dont `patterns/**`) · `docs/migration/source/**` · `docker/**` ·
`compose.yaml` · `docs/ETAT.md` · `docs/migration/redirections.md` ·
`docs/guide/page-composer-une-page-libre.md`.

**Commit** : `git commit -m "…" -- <chemins>`, jamais `git add` nu — l'index est partagé avec #20.

## 2. Périmètre

**61 résultats** = 57 lignes de tableau + 4 lignes « Autres disciplines ». Recompté trois fois de
façon indépendante (lead, plan back par l'outil, note front). Répartition : RING 22 · IGP (RCI) 4 ·
Mondioring 4 · Obéissance 19 · Sauvetage 1 · Pistage 3 · Recherche Utilitaire 4 · Autres 4.

**7 pages** : `bhpl` · `bhpl-en-france` · `litterature` · `travail` · `placement` ·
`mentions-legales` · `politique-de-confidentialite`.

**Hors périmètre, et c'est une décision mesurée, pas un oubli** :
- **Accueil** — `docker/provision/provision.sh:192` réécrit son `post_content` à **chaque**
  provisionnement, et `provision.sh` est hors empreinte. Sa moitié basse est en outre une grille de
  six reproducteurs que `mtb/grille-chiens` calcule depuis les fiches que **#20** importe en parallèle.
- **Contact** — même cause, `provision.sh:214`.
- **La meute** — domaine `chiens`, donc #20.

Vérifié ligne à ligne par le lead : `provision.sh` ne nomme, ne crée, ne met à jour et ne supprime
**aucune** des sept pages ci-dessus. Sa ligne 258 (`search-replace` sur `wp_posts`) les balaie mais
ne substitue qu'un littéral absent de toute capture : **elle les touche sans rien y changer.**

## 3. Forme des données

Règle de base, héritée de #29 §4 : **une clé JSON est la clé de méta du modèle privée de son préfixe
`_mtb_`.** `null`, clé absente et `""` signifient tous trois « écrire le défaut du modèle ». Jamais
`empty()` — `empty('0')` vaut vrai. **Une clé inconnue n'est jamais ignorée : elle fait rejeter
l'entrée, bruyamment.** L'ensemble des clés acceptées est **calculé à l'exécution** depuis le modèle
vivant, jamais recopié.

### 3.1 `donnees/resultats.json` — liste de 61 entrées, dans l'ordre de la source

```json
{
  "discipline": "ring", "chien_nom": "Uhinter Fell du Mont Brabant", "sexe": "male",
  "annee": 2026, "niveau": "Brevet", "conducteur": "", "pays": "",
  "source": { "famille": "tableau", "discipline_source": "RING",
              "ligne": "2026 | ♂ Uhinter Fell du Mont Brabant | Brevet " },
  "commentaire": ""
}
```

| Clé | Méta | Obligatoire | Absente |
|---|---|---|---|
| `discipline` | `_mtb_discipline` | oui (tuple) | rejet |
| `chien_nom` | `_mtb_chien_nom` | oui (tuple) | rejet |
| `sexe` | `_mtb_sexe` | non | défaut |
| `annee` | `_mtb_annee` | oui (tuple) | rejet |
| `niveau` | `_mtb_niveau` | oui (tuple) | rejet |
| `conducteur` | `_mtb_conducteur` | non | défaut — **les 61** |
| `pays` | `_mtb_pays` | non | défaut — **les 61** (voir A4) |
| `source` | **jamais écrite** | oui | rejet |
| `commentaire` | jamais écrite | non | — |

**Trois écarts déclarés à la règle #29 :**
1. **`reference` n'est pas une clé acceptée ici.** Aucune clé du fichier ne peut désigner une fiche
   chien. C'est **la propriété de sûreté de toute la stratégie de rattachement** : un transcripteur ne
   peut pas poser un lien faux, parce que le fichier qu'il écrit n'a pas de champ pour l'exprimer.
   Une entrée portant `reference` est rejetée comme clé inconnue.
2. **`source` est une clé réservée nouvelle**, sœur de `commentaire` : jamais lue par l'importeur,
   jamais écrite en base, lue uniquement par le comparateur. **Obligatoire** — une entrée sans
   provenance est une affirmation sans preuve, et c'est elle qui rend D11 vérifiable.
3. **`chien_nom` est obligatoire** (corollaire de 1 : seul porteur du chien, donc 4ᵉ membre du tuple).

**L'ordre du fichier est l'ordre de la source, et ce n'est pas cosmétique** : `interne.php:503`
départage deux lignes de même année par identifiant de contenu, donc par ordre de création.
L'importeur crée dans l'ordre du fichier ; l'ordre du site source est reproduit **sans qu'aucun champ
de tri ne soit inventé**.

### 3.2 `donnees/correspondances-chiens.json` — **une** entrée

Trois clés, et aucune autre : `chien_nom`, `reference` (slug), `justification`.

- `chien_nom` doit être **strictement égal**, caractère pour caractère, à un `chien_nom` de
  `resultats.json`. Sinon rejet : une correspondance qui ne correspond à rien est un no-op silencieux.
- **`justification` obligatoire et non vide.** C'est le mécanisme qui rend impossible de faire entrer
  « très probablement le même chien » : il faut écrire la phrase, et elle sera lue.
- Au départ le fichier contient **une seule paire** : `Jango de l'Orée des Crayères`.

### 3.3 `donnees/pages/<slug>.json`

Clés : `reference` → `post_name` · `titre` → `post_title` · `statut` → `post_status`
(`publish` ou `draft`, toute autre valeur rejetée) · `source` (réservée, obligatoire :
capture, html, sha256, zone) · `composition`.

**Pas de clé `parent` — c'est un refus motivé.** Voir A6.

### 3.4 `composition` — liste ordonnée de deux formes, et deux seulement

**Un bloc** : `bloc`, `attributs`, `paragraphes`, `photo`, `source`.
**Un écart** : `ecart` (la raison, en clair) et `source`.

- **`source` est toujours présente**, et porte les lignes **verbatim** de la zone de capture
  consommées par l'entrée, lignes vides comprises, dans l'ordre.
- `attributs` n'accepte que les clés du `block.json`, lues **vivantes** via
  `WP_Block_Type_Registry`. Clé hors schéma ou valeur hors `enum` → rejet.
- **`photo_id`, `photos` et `photo` entier sont interdits dans `attributs`** : un fichier ne peut pas
  connaître un identifiant de contenu. Le nom de fichier vit **à côté**, et l'importeur résout.
- `paragraphes` n'est accepté que pour `mtb/fiche-information`, seul bloc du catalogue à porter des
  enfants (`editeur.js:31`, `allowedBlocks: ['core/paragraph','core/list']`).
- Seul balisage inline autorisé : `[LIEN href=…]texte[/LIEN]`, la notation de la capture. Une chaîne
  contenant `[IMAGE` est **rejetée** — R3 rendue mécanique.

## 4. Balisage de blocs — la règle qui ferme le risque D1

> **L'importeur ne compose jamais de chaîne de balisage à la main. Il construit un tableau à la forme
> de `parse_blocks()` et appelle `serialize_blocks()`.**

Ce n'est pas du confort : `serialize_block_attributes()` échappe `--` en `--`, et **un `--`
dans une valeur d'attribut ferme le commentaire HTML et détruit la page.**

**Grammaire émise, quatre formes et rien d'autre** : bloc auto-fermant · enveloppe
`mtb/fiche-information` · `core/paragraph` (`<p>` **nu**, `"className": false`) · fermetures.

**`core/list` n'est jamais émis.** Son `save()` pose `class="wp-block-list"` et exige des
`core/list-item` imbriqués : c'est le seul vecteur d'invalidation réel de l'issue. Et la source n'en a
pas besoin — `reduire.py` rend le contenu en lignes séparées par des lignes vides, donc en
paragraphes, et les seuls vrais `<li>` du périmètre sont les quatre lignes « Autres disciplines », qui
deviennent des résultats. **`core/heading` non plus** : l'insérteur le refuse dans une fiche, donc
Fabienne pourrait supprimer ce qu'elle ne peut pas recréer.

**Onze des douze blocs ont `save: () => null`** — leur balisage enregistré est le commentaire
auto-fermant, le validateur compare du vide à du vide, **ils ne peuvent structurellement pas basculer
en « contenu inattendu »**. Seul `mtb/fiche-information` a un `save` réel
(`return el( InnerBlocks.Content )`), qui ne sérialise que ses enfants. D'où l'unique piège :

> **Rien d'autre que des commentaires de blocs et des blancs entre
> `<!-- wp:mtb/fiche-information … -->` et `<!-- /wp:mtb/fiche-information -->`.**

**Trois contrôles, aucun ne subsume les autres** : (1) avant écriture,
`serialize_blocks(parse_blocks($c)) === $c` ; (2) après écriture, le même sur le contenu relu en base
(attrape `wp_slash` manquant et kses) ; (3) **à l'écran**, ouverture des sept pages dans l'éditeur,
zéro « Ce bloc contient du contenu inattendu ». Le validateur est **client** : aucun contrôle serveur
ne le remplace, et on le dit plutôt que de faire semblant.

**Refus d'écrire si `current_user_can('unfiltered_html')` est faux**, avec la commande exacte à
relancer (`--user=admin`). En WP-CLI sans utilisateur, `wp_filter_post_kses` s'accroche et
`preg_replace('/--+/','-')` s'applique au contenu des commentaires.

## 5. Composition retenue, page par page

| Page | Statut | Composition | Photos |
|---|---|---|---|
| `travail` | `publish` | bandeau → fiche « Le BHPL et les disciplines de travail » + prose → **écart** (61 lignes → résultats) → `mtb/tableau-resultats` **sans attribut** → fiche « Chien de travail : pas pour les novices !… » + prose finale | 1, différée |
| `bhpl` | `publish` | bandeau → fiches successives (« Infos chiots », « Pour en savoir plus… », « Santé », « Génétique des couleurs ») | 2, différées |
| `placement` | `publish` | bandeau → fiche « Adultes à placer ! » / « Pas de chiens à replacer actuellement » → `liste-portees` → `encart-appel` | 0 |
| `mentions-legales` | `publish` | **pas de bandeau** · fiche « Informations légales » → **écart** (Siège social + Contact rendus par le composant) → fiches « Représentant légal », « Immatriculation » → `mtb/coordonnees-plan` **sans attribut** | 0 |
| `litterature` | `publish` | bandeau seul. **Zone principale vide dans le HTML reçu** — la source publie une page vide, on publie une page vide. **D11 tenue, pas D4 manquée** | 0 |
| `bhpl-en-france` | `draft` | charpente seule. Source en **302, protégée par mot de passe, non capturable** | 0 |
| `politique-de-confidentialite` | `draft` | charpente seule. Source en **404**, page absente du sitemap. Aucune durée, aucun hébergeur, aucun responsable de traitement inventés (#17 A7) | 0 |

**La composition peut légitimement différer du motif de #17 en nombre de blocs.** Le motif est l'outil
de création d'une page **neuve** par Fabienne ; l'import compose depuis le fichier de données. Ce
n'est pas une divergence.

**Mentions légales — le cas délicat, traité et non contourné.** Le contrat #17 §3 interdit un
deuxième littéral de l'adresse, du téléphone et du courriel. Or la page source les écrit **et les
écrit faux** : `ROUTE DE SALERNES` sans numéro de voie, `680505619` à **neuf** chiffres, un
`tel:-680505619` avec un tiret parasite, `Fabienne Gueneau` sans accent — quand la page Contact du
même site écrit `3060 ROUTE DE SALERNES` et son propre pied de page `© Fabienne Guéneau`. **Le site
source se contredit avec lui-même.** Donc : « Siège social » et « Contact » ne sont pas recopiés, ils
sont rendus par `mtb/coordonnees-plan` depuis la source unique de #38. **Rien n'est harmonisé ni
corrigé** ; la contradiction est citée avec sa référence. En revanche **`Elevage du Mont Brabant` et
`SIRET : 82237792500018` sont repris verbatim** : ce sont des faits que le nouveau site ne porte nulle
part, et leur absence est l'un des restes écrits de #17.

> **Correction d'une prémisse de la consigne de lot, faite en recomptant.** Il m'a été dit que « le
> siège social et la raison sociale manquent, et le site source ne les porte pas ». **C'est faux** :
> `docs/migration/source/pages/mentions-legales.md` porte `Elevage du Mont Brabant`,
> `ROUTE DE SALERNES`, `83570 ENTRECASTEAUX`, `Fabienne Gueneau` et le SIRET. La question bloquante
> réelle est différente et plus étroite : **la source et `BRIEF.md` §7 se contredisent sur trois
> valeurs**, et le site se contredit avec lui-même sur deux.

## 6. Le contrôle de concordance

**Il ne parse jamais la capture pour en tirer des champs** — le parsing est un excellent vérificateur
et un mauvais scribe. Chaîne de preuve à trois maillons, non circulaire :
`champs transcrits → source.ligne → re-dérivation du HTML archivé`.

- **Contrôle 1 — provenance.** La concaténation, dans l'ordre de `composition`, des `source` de toutes
  les entrées (blocs **et** écarts) doit être **strictement égale**, caractère pour caractère, à la
  zone re-dérivée. Cette seule égalité prouve d'un coup : rien perdu, rien ajouté, ordre préservé.
- **Contrôle 2 — consommation de caractères.** *Tout caractère de la ligne est soit consommé par un
  champ transcrit, soit un séparateur d'une liste déclarée et close.* C'est ce contrôle qui **rend
  mécaniquement impossible** de verser `Ferrari` dans Conducteur : le reliquat `Prop.` ne serait ni
  consommé ni déclaré → échec.
- **Contrôle 3 — les deux niveaux parenthésés.** `IGP 3 (Finland)` et
  `Brevet Maitre Chien Drogue (Suisse)` sont **déclarés nommément** comme les deux seuls niveaux
  finissant par une parenthèse avec `pays` vide (voir A4). Un troisième apparaîtrait → échec.
- **Contrôle 4 — les U+00A0**, comptées par zone et confrontées à l'en-tête de la capture.

**Un écart délibéré n'est jamais une exemption** : c'est une entrée de `composition` qui **réclame des
lignes exactement comme un bloc**, sans rien produire, et porte sa raison à côté des lignes qu'elle
couvre. **Il n'existe aucune liste globale de suppression.** Conséquence structurelle :
**une ligne que personne ne réclame casse l'égalité stricte, donc fait échouer le contrôle.**
Il n'y a littéralement aucune façon d'omettre une ligne sans écrire pourquoi.

**Transformations déclarées, comptées ; une transformation qui se déclenche zéro fois est signalée** —
une transformation morte est le signe que la source a changé.

Le comparateur est en **Python 3, bibliothèque standard**, et **importe `zones()` de
`verifier_concordance.py`** au lieu de le recopier. Mesuré et non supposé : `php` n'existe pas sur
l'hôte, `docs/` n'est monté dans aucun conteneur (`compose.yaml:85-89`), décision 34 interdit
`docker compose run` sur `wpcli`. **Un `.py` dans une extension WordPress est une anomalie : la raison
est écrite en tête du fichier pour qu'aucune passe de refacto ne le « range » en PHP et ne le rende
inexécutable.** Il pose lui-même `PYTHONIOENCODING=utf-8` — sans quoi Python retombe sur cp1252 et
plante en `UnicodeEncodeError` **même sans console**, `stdout` étant un tuyau.

Sortie **0** si zéro divergence inexpliquée, **1** sinon.

## 7. Le rattachement chien ↔ résultat

« Chien lié quand identifiable » est une formule creuse ; en l'état, une invitation à fabriquer des
liens. **L'affixe « du Mont Brabant » n'est pas un critère** : 19 noms le portent et n'ont aucune des
17 fiches.

1. Premier import : `_mtb_chien_id = 0` sur **les 61**, `_mtb_chien_nom` verbatim.
2. `correspondances-chiens.json` porte les paires confirmées, chacune avec sa justification écrite.
3. Résolution du slug **à l'exécution**. Fiche absente → **pas de lien, nom verbatim, avertissement,
   code de sortie inchangé.** Les deux chaînes deviennent **indépendantes de l'ordre**.
4. `--raccrocher`, idempotent, lançable après #20, n'écrit que sur les résultats dont
   `_mtb_chien_id` vaut **actuellement 0** : un lien posé à la main par Fabienne n'est jamais écrasé.

> **Piège à inverser délibérément** : `import-fixtures/resultats.php:40-50` **rejette** une entrée dont
> la référence ne résout pas. Juste pour un jeu fictif clos ; **faux ici** — cela ferait disparaître
> 60 résultats sur 61 si #20 n'a pas encore écrit.

## 8. États spéciaux

| État | Émis par le serveur | Rendu |
|---|---|---|
| `chien_sans_fiche` | `_mtb_chien_id = 0`, nom verbatim | nom affiché, aucun lien — **60 lignes sur 61** |
| `pays_vide` | `_mtb_pays = ''` sur les 61 | **la colonne Pays n'apparaît sur aucun groupe** ; jamais « Non renseigné » |
| `conducteur_vide` | `_mtb_conducteur = ''` sur les 61 | la colonne Conducteur n'apparaît sur aucun groupe |
| `discipline_vide` | `truffe`, sans ligne | **ni `h2`, ni `<caption>`, ni `<table>`** |
| `fiche_vide` | ni titre, ni photo, ni prose | **zéro octet** ; pas de `<div>` vide, pas de réserve |
| `photo_absente` | `photo_id = 0` | l'emplacement **n'existe pas** ; `data-position` non émis |
| `page_sans_contenu` | `litterature` | bandeau + `h1` seuls, page valide |
| `page_non_publiee` | `bhpl-en-france`, `politique-de-confidentialite` en `draft` | charpente seule |
| `lien_non_resolu` | fiche `mtb_chien` absente | nom verbatim, journalisé, **code 0** |
| `contenu_deja_present` | page trouvée par `post_name` | compté « présent », **aucune écriture** |

**`h1` unique, vérifié sur le mécanisme** : cinq pages le reçoivent du `mtb/bandeau-ouverture`, qui
**doit rester le tout premier bloc de premier niveau** ; `mentions-legales` et
`politique-de-confidentialite` n'ont pas de bandeau et le reçoivent de `wp:post-title {"level":1}` de
`singular.html`. Une `fiche-information` ne peut **pas** produire de `h1` : sa liste blanche est
`['h2','h3']` et toute autre valeur retombe sur `h2`.

## 9. Écarts de rendu déclarés

La page Travail ne porte **aucun tableau recopié** : la prose est recopiée, **le tableau est calculé**
(`<!-- wp:mtb/tableau-resultats /-->`, sans attribut, #17 A3bis). Les écarts qui en découlent, tous
énumérés — **aucun ne perd un fait, et un écart non écrit n'est imputable à personne** :

1. `Recherche Utilitaire` → **`Recherche utilitaire`**.
2. `IGP (RCI)` → **`IGP / RCI`** (décision 11).
3. **Le sexe disparaît.** `♂`/`♀` ne sont **pas** remplacés par « Mâle »/« Femelle » : ils **ne sont
   pas rendus du tout**. Vérifié par le lead : **zéro occurrence de `sexe`** dans
   `blocks/tableau-resultats/`, alors que `cellule_chien()` expose `sexe` et `sexe_libelle`. La donnée
   est **stockée fidèlement** dans `_mtb_sexe` ; c'est l'affichage qui manque, et il appartient au
   composant de #15, hors empreinte. **Perte visible sur les 61 lignes → dette T-#21-i.**
4. **L'ordre des disciplines** devient celui de `mtb_resultat_disciplines()` : la source imprime
   **Sauvetage entre Obéissance et Pistage**, la liste gelée le place après Recherche utilitaire.
5. **« Autres disciplines » passe d'avant tous les tableaux à après tous les tableaux.**
6. **Les quatre lignes « Autres disciplines » sont réordonnées** : source 2018, 2019, 2010, 2012 →
   rendu 2019, 2018, 2012, 2010. **C'est le seul groupe réordonné** — les sept autres sont déjà
   décroissants à la source.
7. Ces quatre lignes, `<li>` d'une `<ul>` à la source, deviennent des lignes de tableau.
8. Les intertitres en `<strong>` de la source deviennent des `<h2>` de `mtb/fiche-information`.
9. Le nom de la discipline sort du `<th>` et devient un `<h2>` plus un `<caption>` masqué (ajout AA).
10. Repli mobile sous 48 rem par `data-libelle` (décision 10) : la source ne fait rien de tel.
11. **Le lien externe de la ligne « Road Trip » disparaît** : `cellule_chien()` ne pose un `<a>` que
    vers une fiche du site, et aucun champ d'URL externe n'existe sur un résultat.
12. **La ligne source « Autres disciplines : » n'est pas recopiée en prose** — elle produirait un
    second `<h2>` identique à celui du composant.
13. **Trois lignes de tableau parasites** (`  |   |  `, en tête d'Obéissance, Sauvetage et Recherche
    Utilitaire) ne sont pas importées : ce ne sont pas des résultats. Les importer produirait trois
    lignes « Non renseigné » d'année 0, reléguées en fin de tableau.
14. Adresse, téléphone et courriel des Mentions légales viennent du composant, donc **diffèrent des
    caractères de la page source** (dix chiffres contre neuf, numéro de voie présent, accent présent).
15. Les lignes-espaceurs à U+00A0 seule de l'éditeur IONOS ne sont pas reprises, **comptées et
    déclarées page par page**.

## 10. Interdits

Écrire hors empreinte · **mettre à jour un contenu existant** · supprimer quoi que ce soit · `$wpdb` ·
recopier une liste fermée · ignorer une clé JSON inconnue · **écrire un assainisseur** · émettre un
bloc, un shortcode, une règle visuelle ou une fonction `mtb_*` globale · appeler un domaine tiers ·
**laisser survivre une URL `mtbrabant.com` dans le contenu** · écrire un `[IMAGE]` dans de la prose ·
poser un attribut autre que `href` sur un `<a>` · émettre `core/list` ou `core/heading` · faire d'une
ligne vide ou à U+00A0 seule un `core/paragraph` · écrire un littéral de coordonnées dans un attribut ·
**inventer** un `alt`, un nom de fichier, un lien chien↔résultat, un parent de page, un tarif, un
responsable de traitement ou une durée de conservation · **appeler cette commande depuis
`provision.sh`** · reformuler, résumer ou corriger l'orthographe de la source (« le le Berger
Hollandais », « portie », « intérgrité », « responsabiité », « espèrer » restent).

**T9 — acquis rare, vérifié et gravé** : `content/resultat/champs.php:21-23` enregistre
`assainir_texte_recopie` en `sanitize_callback` de `register_post_meta`, et son commentaire dit que ce
rappel « couvre toute écriture par `update_post_meta()`, **y compris celle d'un futur import** ».
**Ce module n'écrit donc AUCUN assainisseur** — ni cinquième copie, ni sémantique nouvelle.

## 11. Arbitrages

| # | Désaccord | Décision | Raison |
|---|---|---|---|
| **A1** | Forme du livrable | **Données versionnées + importeur qui crée seulement + comparateur hors ligne rejouable** | Le parsing est un excellent vérificateur et un mauvais scribe : dans un importeur, une erreur de regex écrit un fait faux en base, en silence, sur un site qui répond 200 ; dans un comparateur, la même erreur produit une ligne de rapport qu'un humain lit. Et un import dépendant de `docs/` casse en production, où `docs/` n'est pas déployé. |
| **A2** | Idempotence | **Chercher par `post_name` / par tuple. Absent → création. Présent → refus et signalement.** Modes `--simuler` et `--verifier`, qui **signalent sans jamais réécrire** | Les sept pages existent déjà en base de développement (ids 128-135, saisies par #17), et Fabienne corrigera des mots. Son travail n'est **jamais** écrasé par un outil qu'elle ne voit pas. |
| **A3** | U+00A0 de bord — **27 niveaux et ≥ 49 noms en portent une**, remontée par le plan back | **Rognée aux deux bords, ASCII **et** U+00A0, en transformation déclarée et comptée ligne par ligne. Toute U+00A0 intérieure est préservée verbatim.** | Trois raisons. (1) `assainir_texte_recopie()` rogne déjà les blancs ASCII et **pas** les U+00A0 : garder les unes et perdre les autres serait un régime incohérent que personne ne peut tenir en tête. (2) `niveau` et `chien_nom` sont deux des quatre membres du **tuple d'identité** — un caractère invisible y fabrique des doublons fantômes et casse `--verifier` à jamais dès que Fabienne retape « Brevet ». (3) **Un blanc de bord n'est pas un fait d'élevage**, c'est un artefact de cellule IONOS ; le principe verbatim protège les noms, les dates et les nombres, pas le rembourrage invisible. Contrepartie assumée et rendue visible : c'est une modification de la capture, donc elle est **comptée et listée exhaustivement** dans le rapport. |
| **A4** | Le pays de `IGP 3 (Finland)` et `Brevet Maitre Chien Drogue (Suisse)` | **`pays` reste VIDE sur les 61. Le niveau reste verbatim, parenthèse comprise.** — *renversement de ma propre consigne au plan back* | Décision 18 et `cellule_pays()` sont explicites : **un pays vide ne signifie pas « inconnu » mais « pas obtenu à l'étranger »**. Or le tableau IGP a **quatre** lignes et une seule porte « (Finland) ». Renseigner Nebula **ferait apparaître la colonne Pays** sur ce tableau et donnerait à **Rita, Dixie et Tina une cellule vide, donc l'affirmation « obtenu en France »** — trois affirmations que la source ne fait pas, fabriquées pour en éviter une. Laisser vide partout : la colonne **n'est rendue nulle part**, le visiteur lit les mots mêmes de la source dans le niveau, et **le site n'énonce aucune revendication de pays**. La sémantique fausse reste confinée au modèle de données, invisible. La question du champ structuré part à l'éleveuse (Q-c). |
| **A5** | Les 3 photos, `docs/` n'étant monté dans aucun conteneur | **Téléversement DIFFÉRÉ. Aucune duplication d'octets dans l'extension.** Les pages sont créées sans photo ; `--photos` sans défaut, avertissement bruyant nommant les trois fichiers et le montage manquant | Le plan back proposait de copier **1 125 604 octets** dans l'empreinte. Refusé : dupliquer une archive crée **une seconde source de vérité pour une photographie**, ce que la contrainte 3 interdit dans son esprit, et l'engage dans git pour toujours. Surtout, **le texte alternatif est déjà une question bloquante à l'éleveuse** : les photos ne peuvent de toute façon pas être reprises *correctement* aujourd'hui. Le montage `docs/:ro` dans `wpcli` est une ligne dans `compose.yaml`, hors empreinte. **Manque déclaré, pas case cochée.** L'état `photo_absente` est déjà géré : l'emplacement n'existe pas, D12 tenue. |
| **A6** | Hiérarchie et slugs | **Slugs sans accent, pages créées à plat, aucun `parent`** | Poser un parent décide d'une URL, or **Q4 est ouverte** et **D5 appartient à #24**. Une décision se laisse à son propriétaire. Mesure versée au dossier de #24 : `/bhpl/litterature/` reproduirait la forme de l'URL source, ce qui rendrait sa 301 triviale. Vérifié : aucun gabarit, aucun fil d'Ariane, aucun slug réservé du thème ne dépend d'une des sept. |
| **A7** | `bhpl-en-france` : publier une charpente vide ou `draft` ? | **`draft`** | Zéro octet capturé, et la page source est de toute façon **protégée par mot de passe** : publier une coquille vide donnerait au public une page que la source ne lui montre pas. `litterature`, elle, est **publiée** — la source publie une page vide, on publie une page vide. |
| **A8** | Les 5 résultats de démonstration de `provision.sh` partagent la table | **L'importeur DÉTECTE et AVERTIT ; il ne supprime jamais rien** | `provision.sh:226-230` sème 5 résultats fictifs (marqueurs `demo-*`, affixe « de Démonstration ») dans le même type que mes 61 réels : `/travail/` **mélangera les deux jeux**, et l'ordre intra-année en dépend. Supprimer du contenu qui n'est pas le sien est hors de tout mandat. Le geste correct est `docker compose down -v`, et il est écrit au mode d'emploi. **Point de vérification pour la passe d'intégration du lot.** |
| **A9** | Nom de la commande | **`wp mtb reprise-resultats-pages`** | Le nom de sous-commande est le nom du dossier d'empreinte : deux chaînes aux empreintes disjointes ont des noms disjoints **par construction**, sans coordination avec #20. `import-ancien-site`, que `import-fixtures/bootstrap.php:42` cite, est laissé libre. |

## 12. Faits d'élevage sur lesquels la chaîne a buté — non comblés

1. **`Pegaz Eenhoorn` (tableau) vs `Pégaz Eenhoorn` (fiche)** — un accent d'écart, très probablement le
   même chien. **« Très probablement » est un jugement, jamais une fonction de normalisation.** Pas de lien.
2. **`Tina du Hameau des Trois Fontaines` (IGP 2005) vs la fiche `Tina dit Tara du H3F`** — `H3F`
   ressemble à l'abréviation. **Ressembler n'est pas être.** Pas de lien.
3. **Une seule correspondance sûre sur 61 lignes** : `Jango de l'Orée des Crayères`, identique
   caractère pour caractère, sans aucune normalisation.
4. **Conducteur vide sur les 61.** *Ce n'est pas une omission de la reprise, c'est l'état de la
   source.* Et `- Prop. Ferrari` est un **propriétaire**, pas un conducteur : il reste verbatim dans le
   niveau, et le contrôle 2 rend l'erreur mécaniquement impossible.
5. **Aucun texte alternatif, aucun nom de fichier lisible** sur les trois photos. Sur 192 photos de
   l'archive : 126 sans attribut `alt`, 64 vides, **2 nommées** — aucune des miennes.
6. **Le site source se contredit** sur le téléphone, le numéro de voie et l'accent de « Guéneau ».
7. **`Qual. Chien de sauvetage` (2012, Dixie)** figure sous « Autres disciplines » alors qu'un tableau
   **Sauvetage** de la même page porte `2009 | ♀ Dixie du Mont Brabant | Brevet`. Même chienne, deux
   années, deux emplacements. **Aucun rapprochement n'est fait.**
8. **Q23 couvre `placement`, qui est dans ce périmètre.** Le site source la retire des menus **et** la
   marque « ne pas indexer ». **Le `noindex` n'est pas implémentable depuis cette empreinte** :
   WordPress n'a aucun réglage natif, il faut un filtre `wp_robots`, comportement permanent de
   référencement qui appartient à **#24**. Cette issue **importe et consigne** ; elle ne rend pas Q23
   tenue. **Un lot qui se clôturerait en déclarant Q23 tenue se clôturerait sur un silence.**
9. **Les cinq saillies projetées 2026→2028** de l'Accueil et celle de BHPL nomment des chiens réels et
   **périment**. Recopiées au caractère près, jamais reformulées.

## 13. Dettes créées ou constatées

| # | Dette | Vers |
|---|---|---|
| T-#21-a | **Les 3 photos ne sont pas téléversées** : `docs/` n'est monté dans aucun conteneur (`compose.yaml:85-89`) et l'`alt` est une question bloquante | issue `infra` + éleveuse |
| T-#21-b | **Un comparateur Python vit dans une extension WordPress.** Mesuré : pas de `php` sur l'hôte, `docs/` non monté, décision 34. **Ne pas « ranger » en PHP** | information, gravée en tête du fichier |
| T-#21-c | **Deux des trois photos sont des PNG**, et `format_de_sortie()` ne mappe que JPEG→WebP, délibérément. Dont un PNG de **1 002 317 o** sur la page Travail | `perf` / utilisateur |
| T-#21-d | `docs/migration/redirections.md` **est périmé** et le devient davantage (T38) | `/lead-mtb` |
| T-#21-e | **`ETAT.md` et `photos/MANIFESTE.md` disent encore que T12 est un « prérequis dur »** : c'est faux depuis `admin/medias/bootstrap.php`, qui **n'a délibérément pas de garde `is_admin()`** en nommant l'import WP-CLI | `/lead-mtb` |
| T-#21-f | Le lien `belgiandogs.be` recopié porte un **`?fbclid=…`**. Aucune requête au chargement, D6 tient ; le retirer serait modifier une URL, donc un fait | question utilisateur |
| T-#21-g | `--raccrocher` ne distingue pas « #20 n'a pas tourné » d'« un slug fautif » : le message nomme les deux hypothèses | information |
| T-#21-h | L'ordre à année égale est l'ordre de création : un résultat rejeté puis réimporté passe en fin d'année | recette |
| T-#21-i | **Le sexe des 61 lignes n'est jamais affiché** — donnée saisie, exposée par la lecture, imprimée par personne | issue de suite sur #15 |
| T-#21-j | **`docs/guide/page-composer-une-page-libre.md` devient partiellement faux** dès que les pages existent (décision 43 : une fiche qui ment est bloquante). **Hors empreinte, non corrigé** | `/lead-mtb` |
| T-#21-k | **Le chiffre de 120 cellules du contrat #15 §4.3 est périmé** : ce sera 183 | information |

## 14. Séquencement opposable

1. **`concordance.py` et `schema-fichier.php` d'abord**, avant qu'une ligne de donnée existe. Le
   comparateur doit **échouer sur des fichiers vides** — c'est sa recette d'acceptation. *Sinon il sera
   calibré pour valider ce qui existe.*
2. **En parallèle**, empreintes disjointes : `dev-back-mtb` le code, `contenu-mtb` les données.
3. **`contenu-mtb` transcrit jusqu'à ce que le comparateur se taise.** Sortie 0, ou ce n'est pas fini.
4. `--simuler`, puis import réel avec `--user=admin`, puis `--verifier` immédiatement derrière : 0.
5. Ouverture des sept pages dans l'éditeur : **zéro « contenu inattendu »**. Le seul contrôle
   qu'aucun script ne peut faire.
6. `--raccrocher` : aujourd'hui il journalisera « fiche `jango` absente » et sortira en 0.

**Piège opérationnel, en gras au mode d'emploi** : sur la base de développement actuelle, les sept
pages **existent déjà**. L'import comptera « présent » et **n'écrira rien**. Pour observer la reprise,
`docker compose down -v` — cycle destructeur, à annoncer.

---

## 15. Amendements portés après implémentation

Trois points de ce contrat se sont révélés faux au contact de la source. Ils sont **corrigés ici et
non réécrits plus haut** : la décision 46 veut qu'un écart soit *déclaré*, pas effacé, et le contrat
#29 a créé le précédent en portant son amendement après implémentation plutôt qu'en le contournant en
silence. Les trois ont été **remontés par `contenu-mtb`**, qui a contredit son donneur d'ordre en
lisant la source. C'est son travail, et il l'a fait.

**Am-1 — §5, page `travail`, seconde fiche : pas de titre.**
Le §5 nommait `Chien de travail : pas pour les novices ! Je n'ai pas retrouvé le nom de l'auteur.`
comme le **titre** de la seconde fiche. **C'est faux.** Vérifié dans `html/travail.html` : cette ligne
est un `<p>` **nu**, sans `<strong>` et sans balise de titre. Or le §9.8 ne licencie la promotion en
`<h2>` que pour les **intertitres en `<strong>`**. Promouvoir un paragraphe ordinaire inventerait un
niveau de titre que la source n'a pas, et logerait en prime une remarque éditoriale de l'éleveuse
(« Je n'ai pas retrouvé le nom de l'auteur ») dans un titre de section.
→ **`titre: ""`, et la ligne entière devient un paragraphe verbatim, non coupée en deux.**

**Am-2 — §5, page `bhpl` : la fiche « Génétique des couleurs » n'est pas créée.**
Le §5 la listait, par recopie de la charpente du motif `patterns/bhpl.php`. **La source n'écrit ce
titre nulle part** : « Génétique des couleurs » y est le **libellé d'un lien**. L'intertitre réel,
`=> Les couleurs chez le Berger Hollandais :`, a exactement la forme des six autres lignes `=>` de la
même section, qui restent des paragraphes ; **en promouvoir une seule serait un jugement.**
→ Les lignes vivent dans la fiche « Pour en savoir plus sur le Berger Hollandais ». **Aucune ligne
n'est perdue** — c'est le seul critère qui compte.
→ **Règle générale que cet amendement établit** : le §5 décrivait la charpente des motifs, pas la
source. **En cas de désaccord, la source gouverne.** Un motif est l'outil de création d'une page
neuve par Fabienne ; il ne dicte pas la structure d'une page reprise.

**Am-3 — §3.4, clé `source` : la forme chaîne est conservée, et son ambiguïté devient un bruit.**
Une `source` en **chaîne** ne distingue pas « je ne réclame aucune ligne » d'« une ligne vide ». Le
comparateur en a souffert : `"\n".join` fabriquait une ligne vide parasite pour tout bloc calculé,
d'où 5 divergences sur une transcription pourtant correcte.
→ La forme **chaîne est conservée** : réécrire 9 fichiers de données déjà vérifiés corrects, pour
changer une forme, risquerait d'introduire une vraie erreur de transcription là où il n'y a qu'une
ambiguïté latente.
→ **En contrepartie, le comparateur échoue bruyamment** (sortie 1, page et index nommés) si une
entrée porte une `source` vide **sans être un bloc calculé**. L'ambiguïté devient une erreur explicite
au lieu d'une vérification faussement passée. **Dette T-#21-l.**

### Dettes ajoutées

| # | Dette | Vers |
|---|---|---|
| T-#21-l | **La clé `source` en chaîne ne peut pas exprimer « je réclame une seule ligne vide ».** Aucune des 48 entrées n'en a besoin aujourd'hui ; un garde bruyant couvre le jour où ce sera le cas. La forme liste (`[]` vs `[""]`) lèverait l'ambiguïté | issue de suite |
| T-#21-m | **`docs/migration/source/html/*.html` est stocké en CRLF** (`core.autocrlf=true`, aucun `.gitattributes`) : `travail.html` pèse 64 800 o sur disque contre 63 623 o au `RELEVE.md` — écart = exactement 1 177 fins de ligne. **Aucun `sha256sum` naïf ne retrouve une valeur du relevé.** `reduire.py` et `concordance.py` normalisent ; le piège attend le prochain passant | `/lead-mtb` |
| T-#21-n | **`Mines d'Odiles` (2016, Hardy, Pistage) contre `Mines d'Odile` (2018, Hekla, Recherche Utilitaire)** — même élevage, deux graphies dans la même page source. Aucune alignée. Ni le brief ni aucune revue ne l'avaient relevé | question à l'éleveuse |
