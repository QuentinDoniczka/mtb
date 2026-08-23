# Contrat d'interface — Issue #29 — Jeu de contenus de démonstration : `wp mtb import-fixtures`

**Gelé le 2026-08-23 par `/lead-issue-mtb`.** Milestone « Dette technique », domaine `infra`.

Cette issue ne livre **aucune surface visible pour l'éleveuse** : pas d'écran, pas de champ, pas de
bloc, pas de fonction de lecture. Elle livre une commande WP-CLI et le jeu de données qu'elle importe.
Son interface réelle n'est donc pas thème↔extension : c'est **fichier JSON ↔ modèle gelé**. C'est cette
frontière-là que ce contrat gèle.

Lignes de la DoD servies : **D9** (la pile de développement fournit un jeu représentatif) et **D12**
(un contenu mal rempli n'affiche jamais une page cassée — preuve qui exige que la fixture à moitié
vide arrive *telle quelle*).

---

## 1. Ce que l'issue livre

| Livrable | Emplacement |
|---|---|
| Module WP-CLI `import-fixtures` | `wp-content/plugins/mtb-core/includes/migration/import-fixtures/` |
| Jeu de démonstration converti et enrichi | `docker/fixtures/chiens.json`, `portees.json`, `resultats.json` |

**Rien d'autre.** Aucun fichier de thème, aucun fichier hors de ces deux emplacements, aucun binaire.

---

## 2. Signature — gelée par `docs/contracts/issue-1.md`, à respecter au caractère près

```
wp mtb import-fixtures [--portees=<chemin>] [--chiens=<chemin>] [--resultats=<chemin>]
```

`docker/provision/provision.sh` sonde la commande par `wp mtb import-fixtures --help` (doit sortir en
**0** dès que l'extension est active) puis l'appelle avec les trois options pointant `/fixtures/*.json`.

**Aucune quatrième option ne sera ajoutée.** Le chemin des photos est **dérivé** du dossier du fichier
JSON qui porte la référence (`/fixtures/portees.json` → `/fixtures/photos/`). C'est ce qui laisse la
signature intacte et `provision.sh` hors d'atteinte.

---

## 3. Ce que la commande garantit

- **Les trois options sont facultatives.** Un appel partiel fonctionne ; un type non fourni
  n'apparaît dans aucune ligne du rapport. Un appel sans aucune option ne provoque pas d'échec.
- **Rejouable sans doublon** : *créer si absent, laisser strictement intact si présent*. **Aucune
  écriture sur un contenu trouvé.**
- **Elle ne supprime jamais rien** — ni `wp_delete_post`, ni `wp_trash_post`, ni suppression de méta.
- **Aucun marqueur d'origine** : aucune méta `_mtb_fixture`, aucune option, aucun terme. Un contenu
  importé est indiscernable d'un contenu saisi, et c'est voulu (§10, arbitrage 4).
- **Aucune écriture avant que tous les fichiers fournis ne soient lus et valides.**
- **Aucun appel réseau.** L'unique média est lu sur le disque local.
- **Elle n'écrit que dans `wp_posts` et `wp_postmeta`.** Aucune option, aucun réglage, aucun
  utilisateur, aucun menu, aucun terme, aucune règle de réécriture.
- **Code de sortie 1 dès un seul rejet**, ou sur l'un des trois échecs totaux du §7. **0 sinon.**

---

## 4. La règle qui engendre le format des fichiers

> **Une clé JSON est la clé de méta du modèle privée de son préfixe `_mtb_`.**
>
> Exceptions, limitativement : les clés de contenu WordPress (`identifiant` / `nom_usage` →
> `post_title`, `reference` → `post_name`, `texte_libre` → `post_content`, **`photo` →
> `_thumbnail_id`, acceptée pour `chiens.json` uniquement**), les quatre groupes
> (`tests_sante`, `titres`, `pere`, `mere`) dont les sous-clés sont les **clés courtes du modèle**, et
> la clé réservée **`commentaire`**, jamais lue ni écrite.
>
> **Toute clé JSON hors de cet ensemble fait rejeter l'entrée, bruyamment.**

**Amendement du 2026-08-23, porté après implémentation.** Ma première rédaction énonçait ces exceptions
« limitativement » et **omettait `photo`**, alors que le §7.1 la nomme par ailleurs (« `_thumbnail_id`,
qui n'a aucun assainisseur de modèle ») et que la fixture `demo-rex` l'exige. `photo` est une **clé de
contenu WordPress** — l'image mise en avant — et non une clé de méta du modèle : elle rejoint donc la
première liste. **Elle n'est acceptée que sur un chien** ; une portée qui porterait `photo` est rejetée
comme clé inconnue, sa galerie passant par `galerie`. Écart relevé par `dev-back-mtb` et ratifié ici
plutôt que contourné en silence.

L'ensemble des clés acceptées est **calculé à l'exécution** depuis `catalogue()`,
`schema_des_champs()`, `champs_sante()`, `champs_titres()` et `definitions()`. Il n'est écrit nulle
part dans le module. Conséquence voulue : le jour où une issue ajoute un champ, un fichier de fixture
peut l'employer **sans qu'une ligne de l'importateur bouge**.

**Règle transverse « donnée absente »** — `null`, la clé absente et la chaîne vide signifient tous
trois la même chose : la clé de méta est écrite **avec le défaut du modèle**. Aucun cas ne laisse une
clé non écrite ; le stockage est identique à celui que produit un enregistrement depuis l'écran de
saisie. C'est la contrainte 3 prise au mot.

---

## 5. Table de conversion — exhaustive, valeur par valeur

Cette table est la moitié invisible de l'issue. **Elle a déjà été appliquée à moitié une fois** : le
lot 2 a converti les clés de disponibilité et oublié les types de parent. Elle est donc écrite
intégralement, avec pour chaque ligne la fonction du modèle qui fait foi.

### 5.1 `portees.json`

| Actuel | Exigé | Fait foi |
|---|---|---|
| `nb_males` | `males` | `catalogue()['_mtb_males']` — `content/portee/champs.php:68` |
| `nb_femelles` | `femelles` | `catalogue()['_mtb_femelles']` — `champs.php:74` |
| `note_fixture` | `commentaire` | clé réservée, hors modèle |
| `pere.type: "fiche_chien"` | **`"fiche"`** | `assainir_type_parent()` — `champs.php:366-374` |
| `pere.type: "nom_libre"` | **`"exterieur"`** | idem |
| `mere.type` idem | idem | idem |
| `identifiant: "A3 2025"`, `"L 1995"` | **`"DEMO1 2025"` … `"DEMO4 2022"`** | §10, arbitrage 6 |
| `chiots[].devenir: "disponible"/"réservée"/"placée"` | **prose libre** (« Disponible », « Réservée », « Placée en famille ») | `assainir_chiots()` — `champs.php:434` : `devenir` passe par `assainir_texte()`, **il n'y a aucune liste fermée**. Les valeurs actuelles imitent les clés de disponibilité et **enseignent une liste qui n'existe pas** |
| `disponibilite`, `chiots[].sexe`, `date_naissance` | **inchangés — déjà justes** | `disponibilites()`, `sexes()`, `assainir_date()` |

### 5.2 `chiens.json`

| Actuel | Exigé | Fait foi |
|---|---|---|
| `tests_sante.HD ED LTV DM SDCA1 SDCA2` | **`hd ed ltv dm sdca1 sdca2`** | `champs_sante()` — `content/chien/choix.php:221-273` |
| `tests_sante.ADN_identifie` | **`adn_identifie`** | `choix.php:259` |
| `tests_sante.adn_identifie: true` | **`"oui"`** \| **`"non"`** | `oui_non()` — `choix.php:67-72` via `assainir_oui_non()` |
| `sexe`, `variete`, `statut` | **inchangés — déjà justes** | `sexes()`, `varietes()`, `statuts()` — `choix.php` |
| `pere` / `mere` *(nouveaux)* | `{ "reference": … }` **ou** `{ "nom": …, "elevage": … }` — **sans clé `type`** | `schema_des_champs()` — `content/chien/champs.php:52-59` : une fiche Chien n'a **aucun** `_mtb_<role>_type`. L'asymétrie avec la portée est celle du modèle, pas une erreur |
| `galerie` *(nouveau)* | liste de slugs → stockée en **chaîne à virgules** | `assainir_liste_identifiants()` — `content/chien/assainissement.php:237` |

### 5.3 `resultats.json`

| Actuel | Exigé | Fait foi |
|---|---|---|
| `discipline: "RING"` | **`"ring"`** | `mtb_resultat_disciplines()` — `query/resultat/bootstrap.php:31` |
| `discipline: "IGP"` | **`"igp_rci"`** | `query/resultat/bootstrap.php:32` |
| `chien` | **`reference`** | §10, arbitrage 3 |
| `niveau_ou_titre` | **`niveau`** | `definitions()['_mtb_niveau']` — `content/resultat/champs.php:87` |
| `conducteur: "Fabienne Guéneau"` | **supprimé** | §10, arbitrage 5 |

**Le piège central de cette table, à comprendre avant de la relire** : `sanitize_key('RING')` rend
`ring` — **juste par accident**. `sanitize_key('IGP')` rend `igp`, qui n'est **pas** une clé de la
liste. Et `content/resultat/champs.php:48-51` **refuse délibérément toute liste blanche** sur la
discipline. Une valeur fausse est donc stockée sans erreur et **sort de tout tableau de résultats**.
Aucune des six erreurs du jeu actuel ne produit d'erreur PHP.

---

## 6. Idempotence

**Règle unique : créer si absent, laisser strictement intact si présent.**

| Type | Identité | Résolution |
|---|---|---|
| Chien | le **slug** (`reference`) | `get_page_by_path( $slug, OBJECT, 'mtb_chien' )` — voit tous les statuts |
| Portée | l'**`identifiant`** (le `post_title`) | `get_posts( post_type => 'mtb_portee', title => …, post_status => 'any', fields => 'ids' )` — voir §10, arbitrage 2 |
| Résultat | le tuple **`discipline` + `annee` + `niveau` + (`chien_id` \| `chien_nom`)** | `get_posts()` + `meta_query` en `AND`, `post_status => 'any'` |

**Les quatre clés du tuple d'un résultat sont obligatoires.** Une entrée qui en manque une est rejetée.
Sans cette obligation, une clé jamais écrite ne serait pas trouvée par la `meta_query` et l'entrée
serait **recréée à chaque exécution, en silence** — mode de panne réel, pas théorique.

**Conséquence assumée, à écrire dans la note de développement** : modifier un fichier de fixture ne met
**pas** à jour une base existante. On fait `docker compose down -v`.

**Trois limites nommées et acceptées** : un contenu **à la corbeille** est hors de `'any'` et serait
recréé ; un contenu dont un développeur a **modifié le titre ou le slug** n'est plus reconnu et serait
recréé. Les corriger exigerait `$wpdb` (interdit), un marqueur d'origine (interdit) ou une modification
hors empreinte. **On les nomme plutôt que de les combler.**

---

## 7. L'échec est bruyant — et c'est la leçon de T39

`docker/provision/provision.sh` a livré un site en anglais **sans qu'aucune ligne de journal ne le
dise**, parce qu'il avalait un échec. Cette commande ne réussit jamais en silence.

**Granularité : importer ce qui passe, nommer chaque rejet.** Rejeter le lot entier sur une ligne
fautive produirait exactement le défaut de T39 — un site vide, un seul avertissement, et
`[provision] terminé.` derrière.

- **Un `WP_CLI::warning()` par rejet**, portant **fichier, index 0-fondé, identifiant métier, raison**,
  et les valeurs attendues quand une liste du modèle existe.
- **Une ligne de synthèse par type**, puis une synthèse générale.
- **`WP_CLI::error()` (code 1) dès un seul rejet.**
- **Échec immédiat et total dans trois cas seulement** — fichier illisible, JSON invalide, racine qui
  n'est pas une liste. Ce ne sont pas des fixtures fautives, c'est **l'absence de fixtures**, et
  annoncer « 0 importée » sans erreur serait le silence de T39.

### 7.1 Deux contrôles, et aucun ne subsume l'autre

**Contrôle amont**, avant toute écriture, en une règle qui ne recopie aucune liste :

> Si `$brut` diffère du défaut du modèle **et** que l'assainisseur déclaré du champ rend ce défaut,
> **l'entrée est rejetée**.

Cette règle attrape `fiche_chien`, `HD`, `true`. Elle **n'attrape pas** `IGP`, dont `sanitize_key` rend
`igp` — non vide. Pour les **deux seuls** champs passés à `sanitize_key` (`_mtb_discipline` et
`_mtb_sexe` d'un résultat), le contrôle amont ajoute donc une **appartenance** aux listes lues vivantes
via `mtb_resultat_disciplines()` et `mtb_resultat_sexes()`.

**Contrôle aval**, après écriture : relecture par `get_post_meta()` et comparaison à
`$assainir( $attendu )`. Une divergence est un rejet nommé, sur un contenu **déjà créé et jamais
supprimé**.

**Pourquoi les deux** — l'amont voit ce que le modèle refuse ; l'aval voit ce que l'écriture n'a pas
fait. L'aval seul ne verrait ni `fiche_chien` ni `igp` (l'assainisseur est d'accord avec lui-même).
L'amont seul ne verrait ni un `wp_slash` oublié, ni `post_title`/`post_content`/`_thumbnail_id`, qui
n'ont aucun assainisseur de modèle, ni une divergence T9 apparue plus tard.

**Piège PHP, à graver** : la comparaison au défaut se fait en `'' === $x`, **jamais `empty()`**.
`empty('0')` vaut vrai, et `'0'` mâle est un fait d'élevage légitime.

### 7.2 La bruyance s'arrête au bord de `provision.sh` — réserve écrite

`docker/provision/provision.sh:148` transforme le code 1 en `|| log "AVERTISSEMENT : …"`, sans
`set -e`, et imprime `[provision] terminé.` derrière. **Un import à 13 contenus sur 14 laisse donc une
pile qui se déclare saine.** La bruyance réelle de cette commande est **textuelle** (visible dans
`docker compose logs`), pas contractuelle. `provision.sh` est **hors empreinte** de cette issue et porte
déjà la dette **T39** : le défaut est routé, pas réglé, et **on ne fait pas semblant du contraire**.

---

## 8. Les antislashs — la corruption silencieuse à éviter

`update_metadata()` appelle `wp_unslash()` **puis** `sanitize_meta()`. `wp_insert_post()` appelle
`wp_unslash()` sur tout le tableau. Les deux attendent des données **échappées**, parce qu'elles sont
écrites pour recevoir du `$_POST`. **Une valeur venue de `json_decode()` ne l'est pas.**

Sans `wp_slash`, une valeur `N\N` devient `NN` : un résultat de test de santé perd un caractère, sans
erreur et sans avertissement — **D11 enfreinte par l'outillage**.

| Appel | Forme obligatoire |
|---|---|
| `wp_insert_post` / `wp_update_post` | `wp_slash( $postarr )`, tout le tableau, une fois |
| `update_post_meta` | `wp_slash( $valeur )`, **y compris les tableaux et les entiers** |
| `_wp_attachment_image_alt` | `wp_slash( $alt ) ` |
| `media_handle_sideload` | **`post_title` et `post_name`** de `$post_data` échappés |

**Aucune exception « c'est un entier, donc inutile »** : une exception est une règle qu'on oublie.
`wp_slash()` traverse récursivement et laisse les entiers intacts.

Le **contrôle aval compare `get_post_meta()` à la valeur PHP brute du JSON, non échappée** : toute
erreur d'échappement devient donc un rejet nommé. C'est sa seconde raison d'être.

---

## 9. Ce que l'issue n'expose pas

### Fonctions de lecture exposées par l'extension
**Aucune.** Ce module n'ajoute **aucune fonction `mtb_*` globale**. Il consomme les fonctions
existantes (`mtb_resultat_disciplines()`, `mtb_resultat_sexes()`), sous `function_exists()`.

### Blocs enregistrés
**Aucun.** Aucun `block.json`, aucune catégorie, aucun shortcode.

### Hooks et filtres
**Aucun hook posé, aucun filtre exposé.** La première ligne exécutable de `bootstrap.php` est la garde
`if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) { return; }`. Sur une requête web, **rien de ce module n'est
exécuté au-delà de cette ligne**.

### États spéciaux
L'issue **n'invente aucun état**. Elle **produit en base** les états existants pour qu'ils soient enfin
observables sur une pile fraîche :

| État | Émis par le serveur | Rendu par le thème | Produit par quelle fixture |
|---|---|---|---|
| `aucune_portee` | `mtb_get_portees()` → `array()` | état vide du composant | **non atteint** par ce jeu — exige une pile sans `--portees` |
| `donnee_absente` | défaut du modèle (`''`, `0`, `array()`) | « Non renseigné », ou section masquée | `DEMO4 2022` entière ; `tests_sante` vide de `demo-luna` |
| `parent_hors_elevage` | `_mtb_<role>_type = "exterieur"` | nom + élevage, sans lien | `DEMO2 2023` (père), `DEMO3 1995` (les deux) |
| `page_protegee` | — | — | **délibérément absent** — §10, arbitrage 8 |

### Chaînes fournies par le serveur
**Aucune chaîne n'est fournie au thème.** Les seules chaînes produites sont les **messages de la
commande**, destinés au développeur, tous logés dans un fichier unique (`journal.php`) — ce qui rend le
§7 vérifiable en lisant un seul fichier.

**L'éleveuse ne voit rien de nouveau** : ni écran, ni champ, ni bloc, ni message. **Aucune fiche d'aide
n'est due au titre de D3.** Ce qui est dû est une **note de développement** disant que ce contenu est
fictif, que `LOF DEMO` et `DEMO<n> <année>` en sont les marqueurs, et que **rien de ce jeu ne doit
atteindre la base de production**.

---

## 10. Arbitrages

| # | Désaccord | Décision | Raison |
|---|---|---|---|
| 1 | **`issue-1.md` sanctionne `"type": "fiche_chien"` comme forme de fixture ; `issue-3.md` §11 ordonne de convertir cette valeur exacte en `fiche`.** Contradiction directe entre deux contrats gelés | **`issue-3.md` l'emporte** | Il est le contrat **du type lui-même** et possède la liste fermée ; il est postérieur et écrit avec le code en main. La phrase d'`issue-1.md` décrivait le fichier **tel qu'il était alors**, pas une prescription. Décisif : garder `fiche_chien` obligerait l'importateur à porter une **seconde table de traduction** — exactement la duplication que le projet interdit — et le fichier continuerait d'enseigner le mauvais modèle. `issue-1.md` étant hors empreinte, l'arbitrage est consigné **ici** et signalé à `/lead-mtb` |
| 2 | Idempotence d'une portée : `mtb_get_portee_par_identifiant()` (que j'avais cité) ou `get_posts( post_status => 'any' )` | **`get_posts( post_status => 'any' )`, dans l'extension** | `query/portee/hydratation.php:95-96` fige `post_status => 'publish'` et `has_password => false` : une portée passée en brouillon ou protégée serait **recréée en double**, ce qui est un défaut visible sur l'index de démonstration. Ce que `issue-1.md` §10 gèle est **la clé d'identité** (« indexé sur `identifiant` »), pas la fonction qui la lit. `get_posts()` n'est ni `$wpdb`, ni une lecture depuis le thème, et c'est déjà la forme retenue pour les résultats. **Ma formulation initiale citait cette fonction en illustration ; le plan a eu raison de la contester** |
| 3 | `"chien": "rex-du-mont-brabant"` — la dette T-#5-c l'appelle « un slug, pas un identifiant » | **Le slug reste, la clé est renommée `reference`** | Un slug est la **seule** façon pour un fichier de pointer une autre entrée du même jeu ; un identifiant de contenu n'existe pas avant l'import. `portees.json` le fait déjà sous `reference`. Règle gelée : *un fichier emploie la clé du modèle partout où la valeur est stockée telle quelle, et une `reference` par slug là où la valeur stockée est un identifiant de contenu.* **T-#5-c est donc payée sur la discipline, et requalifiée sur le chien** |
| 4 | Marqueur d'origine des fixtures | **Aucun** | L'hébergement de production n'est pas tranché (BRIEF §15.4). Une clé technique posée sur du contenu d'apparence réelle est un fusil chargé dans une base qui deviendra peut-être celle de Fabienne. Et `wp_delete_post` + réimport détruirait le contenu qu'un développeur a saisi à la main — c'est précisément ce qui existait au lot 6 |
| 5 | `"conducteur": "Fabienne Guéneau"` sur deux résultats | **Supprimé** | Personne **réelle, vivante et nommée**, à qui la fixture attribue un Brevet RING 2021 et un IGP1 2015 qu'aucune source n'atteste. `issue-5.md` Q-f établit que le site source ne nomme **aucun** conducteur. Vider un champ n'invente rien ; l'y laisser invente un fait d'élevage. Une seule ligne porte « Conducteur de démonstration », pour exercer la colonne conditionnelle |
| 6 | Identifiants de portée `A3 2025` et `L 1995`, affixe « du Mont Brabant » | **Remplacés par `DEMO<n> <année>` et « de Démonstration »** | `BRIEF.md` §5.1 nomme `A3 2025` et `L 1995` comme les **bornes réelles** des 27 portées. Deux motifs, le second **mécanique** : un fait fictif porterait un identifiant vrai ; et l'idempotence indexant sur l'identifiant, le jour où #19-#21 importera la vraie `A3 2025`, l'import la trouverait « déjà présente » et **sauterait le contenu réel, sans un mot** |
| 7 | Registre de fiction des valeurs techniques | **Manifestement fictif pour tout ce qui identifie** (noms, affixe, n° LOF, identifiants, conducteur, élevages, pedigree, slugs) ; **plausible pour ce qui décrit** (`A`, `0`, `N/N`, « 62 cm ») | Une valeur descriptive n'identifie personne, et le chien qui la porte se déclare fictif dans son propre texte. Les rendre absurdes coûterait tout le rendu réaliste des fiches sans rien protéger |
| 8 | Une portée protégée par mot de passe, pour rendre T40 reproductible | **Non** | `provision.sh:134-139` crée déjà une **page** « Espace privé » protégée, qui reproduit T40 en permanence. Une seconde brouillerait l'index de démonstration pour un gain nul |
| 9 | `tests_sante` de `demo-luna` : le remplir (ma consigne « remplir intégralement les trois chiens ») ou le laisser vide | **Laissé vide** | **Ma consigne était contradictoire** : elle demandait par ailleurs, et le corps de l'issue avec elle, que cette fixture arrive *telle quelle* parce qu'elle **est** la preuve D12 du bloc Santé. `demo-rex` fournit désormais l'état complet. Détruire une preuve D12 déclarée pour obtenir un état qu'on a par ailleurs serait une perte nette. **Le plan a eu raison de résister** |
| 10 | Un `medias.json` (j'avais écrit « quatre fichiers JSON ») | **Trois fichiers, pas quatre** | Erreur de comptage de ma part : `docker/fixtures/` porte trois `.json` et un `.png`. Un quatrième fichier exigerait une option `--medias` que `provision.sh` ne passe pas. Le dossier `photos/` est **dérivé** du chemin du JSON |
| 11 | Un JPEG de démonstration, pour que le WebP et le `srcset` soient observables | **Non — hors empreinte** | `admin/medias/bootstrap.php:98` ne mappe que `image/jpeg → image/webp` ; le PNG existant n'aura donc **aucun format moderne**. Le corriger exige un **binaire dans `docker/fixtures/photos/`**, qui n'est pas dans l'empreinte de cette issue. **Constat porté en dette (§11), pas comblé** |
| 12 | `chiots[].devenir` : liste fermée ou texte libre | **Texte libre, en prose** | `assainir_chiots()` (`champs.php:434`) fait passer `devenir` par `assainir_texte()` : **il n'existe aucune liste fermée**. Les valeurs actuelles (`disponible`, `réservée`, `placée`) imitent les clés de disponibilité et **enseignent une liste qui n'existe pas** — le fichier mentirait sur le modèle dans l'autre sens |

---

## 11. Dettes créées ou constatées par cette issue

| # | Dette | À router vers |
|---|---|---|
| **T-#29-a** | **`provision.sh:148` avale le code 1 de la commande** et imprime `[provision] terminé.` derrière. Un import à 13 contenus sur 14 laisse une pile qui se déclare saine. C'est **T39 à l'identique**, sur la ligne voisine | l'issue `infra` qui porte **T39** |
| **T-#29-b** | **Le jeu de démonstration ne peut prouver aucun format moderne** : sa seule photo est un PNG et `admin/medias` ne convertit que le JPEG. Budget D8 et T12 invérifiables sur les fixtures | arbitrage utilisateur : autoriser un JPEG dans `docker/fixtures/photos/` |
| **T-#29-c** | **Le texte alternatif de la photo existe en deux littéraux** — `provision.sh:163` et le module d'import. Recopié au caractère près pour qu'ils ne divergent pas ; l'étape photo de `provision.sh` devient redondante et devrait disparaître | l'issue `infra` (T39) |
| **T-#29-d** | **`docs/contracts/issue-1.md` §11** (inventaire des modules) doit recevoir la ligne `migration \| import-fixtures \| #29`. Fichier hors empreinte | `/lead-mtb` |
| **T-#29-e** | **`issue-1.md` §10 dit encore que la forme de fixture est `"type": "fiche_chien"`**, ce que l'arbitrage 1 renverse. Tant que la phrase subsiste, deux contrats gelés se contredisent | `/lead-mtb` |
| **T-#29-f** | **Les contenus importés portent `post_author = 0`** (aucun utilisateur courant en CLI). Attribuer du contenu fictif à une personne réelle serait pire. Effet visible : colonne « Auteur » vide en administration | information |
| **T-#29-g** | **`WP_DEBUG` vaut `false` en WP-CLI** (décision 29) : un module d'import cassé échouerait **en silence**, et `provision.sh` retomberait dans la branche « aucune commande disponible » comme si l'issue n'était pas livrée | information de recette |

### Ce que l'issue constate sur T9, sans la payer

Les trois `assainir_texte_recopie()` divergent sur deux axes : `content/chien/assainissement.php:38-58`
**n'appelle pas** `wp_check_invalid_utf8()` et **remplace** les caractères de contrôle par une espace
(tabulation comprise) ; les deux autres **suppriment** et contrôlent l'encodage.

**L'import hérite de la divergence, mécaniquement et intégralement**, puisque chaque méta traverse
`sanitize_meta()`. Mais :

- **la moitié « UTF-8 » est inatteignable par ce chemin** — `json_decode()` échoue avec
  `JSON_ERROR_UTF8` et la commande sort en erreur **avant toute écriture** ;
- **la moitié « caractères de contrôle » est atteignable mais non exercée** : aucune valeur du jeu ne
  porte de tabulation, et le `\n` des champs multilignes est traité identiquement par les trois.

**Interdit explicite** : ne pas insérer une tabulation dans une fixture pour rendre T9 visible. Une
fixture enseigne le modèle, pas ses défauts. Ce que cette issue apporte à T9 est différent et suffisant :
**le contrôle aval du §7.1 la rendrait bruyante** le jour où une valeur en subirait une, sans savoir ni
se soucier de quel assainisseur a tourné. **Elle ne paie pas T9, elle la met sous surveillance.**

Observation consignée : **la divergence UTF-8 est la plus dangereuse des deux**, parce qu'elle ne
modifie pas une valeur — elle la **vide**. Le jour où #19-#21 importera du contenu réellement recopié,
les deux chemins ne perdront pas les mêmes données.

---

## 12. Interdits

- **Supprimer ou modifier un contenu existant** — portée, chien, résultat, pièce jointe, page, option,
  utilisateur, menu.
- **Écrire un marqueur d'origine** (`_mtb_fixture` ou équivalent).
- **Recopier une liste fermée du modèle.** Aucune énumération de disciplines, statuts, variétés,
  disponibilités, sexes ou cadrages n'est écrite dans ce module.
- **Tolérer un libellé à la place d'une clé.** `"RING"`, `"IGP"`, `"fiche_chien"` sont des **rejets**,
  jamais des synonymes : c'est le fichier qui parle le modèle, pas l'importateur qui parle deux langues.
- **Ignorer une clé JSON inconnue.**
- **Passer par `includes/fields/**`** : ni `$_POST` forgé, ni nonce fabriqué, ni `wp_set_current_user`.
  Ces gestionnaires sortent par un `return` **muet** en l'absence de nonce, et publieraient du vide en
  silence ; `controler()` rétrograderait de surcroît en brouillon la fixture lacunaire, détruisant la
  preuve D12.
- **Utiliser `$wpdb`** ou toute requête SQL écrite à la main.
- **Émettre une règle visuelle, un bloc, un shortcode ou une fonction `mtb_*` globale.**
- **Appeler un domaine tiers.**
- **Publier un fait d'élevage réel** : ni « Fabienne Guéneau », ni l'affixe « du Mont Brabant », ni un
  identifiant de portée réel, ni un n° LOF réel.
- **Écrire hors de l'empreinte** : `docker/provision/provision.sh`, `docker/fixtures/photos/**`,
  `docs/ETAT.md`, `docs/migration/source/**`, `includes/contact/**`,
  `includes/blocks/formulaire-contact/**`, et tout `includes/{content,fields,query,blocks,admin}/**`.

---

## 13. Points restés ouverts

- **Q17 (`docs/ETAT.md`) reste formellement ouverte** — « contenu de démonstration : réel recopié,
  fictif assumé, ou rien ? ». Ce contrat applique **fictif assumé** sur instruction de chaîne, **pas sur
  une décision datée**. Tant que la décision n'est pas inscrite au tableau d'`ETAT.md`, la première
  relecture rouvrira le débat sur du contenu déjà écrit. **À trancher par l'utilisateur.**
- **T-#29-b** — le JPEG de démonstration, arbitrage utilisateur.
- L'état `aucune_portee` n'est atteint par aucune fixture, par construction : le vérifier demande une
  pile provisionnée sans l'option `--portees`.

---

## 14. Amendements portés après implémentation et refacto

**14.1 `photo` est une clé de contenu, pas une clé de méta** — voir §4. Omission de ma première
rédaction, relevée par `dev-back-mtb`, ratifiée plutôt que contournée.

**14.2 `post_name` d'une pièce jointe est échappé, au même titre que `post_title`** — §8 amendé.
`media_handle_sideload()` passe `$post_data` à `wp_insert_attachment()`, donc à `wp_insert_post()`, qui
**déséchappe tout le tableau** : le `post_name` traverse exactement le même chemin que le titre. Ma
table ne nommait que `post_title`. **`refacto-mtb` a trouvé le manque dans le code et l'a corrigé** ;
la table dit désormais la vérité du code. C'est la démonstration que la règle « `wp_slash` sans
exception » du §8 valait la peine d'être écrite sans exception : la seule exception réellement présente
dans le module était une omission, pas une décision.

**14.3 `composer_titre()` peut être appelée hors de `modele.php`** — ratifié. `modele.php` est la porte
unique vers **`content/**` et `query/**`** ; `\MTB\Core\Fields\Resultat\composer_titre()` appartient au
groupe **`fields/`**, que cette doctrine ne couvre pas. L'appel est une **lecture pure** (quatre
`get_post_meta` puis une concaténation), il est gardé par `function_exists()`, et il ne franchit aucun
des interdits du §12 — ni `$_POST` forgé, ni nonce, ni `wp_set_current_user`. Ajouter une délégation
dans `modele.php` serait un déplacement sans effet qui **élargirait la doctrine** au lieu de l'appliquer.
**L'appel reste où il est**, et la frontière est écrite ici pour qu'aucune revue ne la relitige.

**14.4 `example.org` dans `chiens.json` n'est pas une origine tierce** — ratifié, pour que la revue ne
s'en émeuve pas à froid. `"pedigree": "https://example.org/pedigree/demo-luna"` est **une valeur de
données**, rendue par le thème dans un `href` : ce n'est ni un `enqueue`, ni un `@font-face`, ni un
`@import`, ni un `src`, donc **aucune requête navigateur au chargement** et la règle transverse « zéro
requête vers un domaine tiers » n'est pas en jeu. `example.org` est le domaine réservé par l'IANA pour
les exemples : il ne peut pas être pris pour une source de pedigree réelle, ce qui sert **D11**. C'est
la seule occurrence de `http(s)` de tout le diff.
