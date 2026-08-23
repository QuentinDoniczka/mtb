# Contrat de capture — Issue #19 — Capture des 52 URL de l'ancien site

**Statut : gelé le 2026-08-21.** Contraignant pour toute la chaîne #19 et opposable à #20 et #21.

Cette issue ne touche **ni le thème ni l'extension**. Il n'y a donc **pas de frontière front↔back à
arbitrer** et ce document ne décrit ni fonction de lecture, ni bloc, ni chaîne de serveur. Il gèle à
la place la **convention de capture**, qui est le seul endroit où deux passes peuvent diverger en
silence et rendre la reprise approximative.

Empreinte d'écriture de la chaîne : **`docs/migration/source/**` uniquement**, plus ce fichier.

---

## 1. La définition opposable des 52 URL

**`sitemap.xml` fait foi, et rien d'autre.** Aucun menu du site ne liste les 52 : le menu principal en
donne 6, le sous-menu « BHPL » 27+2, le sous-menu « La meute » **13** — alors que le sitemap porte
**17** fiches de chiens. `roxane`, `ray-ban`, `youry` et `halan` ne sont dans **aucun** menu.

Le `sitemap.xml` est donc **capturé comme fichier source**, daté et haché, sous
`docs/migration/source/sitemap.xml`. La complétude se prouve par **soustraction** :

> 52 `<loc>` du sitemap − 52 lignes d'inventaire = 0

Relevé de référence, à confronter et non à recopier de confiance
(vérifié le 2026-08-21 par la chaîne #19) : `sitemap.xml` = **7 621 octets**, SHA-256
`bb78eebcd0fa3d8f3b739b6fad9df1ddf49b6abcd49da033d3f78f76cc09cd1e`, **52 `<loc>`**, composition
1 accueil · `/bhpl/` · **27 portées** · `/bhpl/littérature/` · `/travail/` · `/la-meute/` ·
**17 chiens** · `/placement/` · `/contact/` · `/mentions-légales/`. **Les 52 répondent 200**, une à
une.

**`/sitemap/`** (la page HTML « Plan du site » liée depuis le pied de page) **n'est pas dans les 52** :
c'est une page de gabarit IONOS. Constat tranché, ne pas le rouvrir.

## 2. Les URL hors des 52 — annexe nommée, jamais comblée

| URL | Réponse | Traitement |
|---|---|---|
| `/bhpl/bhpl-en-france/` | **302** — protégée par mot de passe sur l'ancien site | **53ᵉ URL**, hors sitemap. `pages/bhpl-en-france.md` existe déjà et dit pourquoi il est vide. **Le conserver tel quel.** |
| `/politique-de-confidentialite/` | **404** — absente du sitemap et de tout menu | `pages/politique-de-confidentialite.md` existe et archive le constat (trois adresses testées). **Le conserver tel quel.** |
| `/bhpl/littérature/` | **200**, mais `diywebMain` **vide** | La page source n'a **aucun contenu rédactionnel**. `pages/litterature.md` le dit. **Mention conservée, rien n'est comblé.** |

Ces trois fichiers sont **D11 tenue, pas D4 manquée**. Toute passe qui les « remplirait » viole le
contrat.

## 3. Convention de capture applicable aux nouveaux fichiers

Deux conventions cohabitent déjà dans le dossier, et **elles ne capturent pas la même chose** :

| | passe #16 (racine) | passe #17 (`pages/`) |
|---|---|---|
| Zones découpées | **3** | **5** |
| `diywebEmotionHeader` (bandeau) | **perdu** | présent |
| `<title>` du document | non relevé | relevé |
| U+00A0 sur `/` | **6** | **22** |
| Cellules de tableau | éclatées, une par ligne | **associées** par ` \| ` |

Les 16 insécables d'écart ne sont pas un désaccord de comptage : la réduction des lignes vides de la
passe #16 **avale les paragraphes ne contenant qu'une insécable**.

**Décision gelée : la convention de référence des nouveaux fichiers est celle de la passe #17 (5 zones).**
Elle est strictement plus fidèle, et le dossier contient déjà des fichiers dans les deux formes — il
n'y a donc pas de convention unique à préserver, seulement la plus fidèle des deux à généraliser.

**Corollaire de même rang, décision 46 d'`ETAT.md` (« un écart non écrit n'est imputable à personne ») :
l'écart des 10 fichiers de la passe #16 est *déclaré* dans l'inventaire — zones manquantes, comptage
d'insécables non comparable — il n'est *pas corrigé*.** Aucun fichier existant n'est réécrit,
renommé ni supprimé.

### 3.0 bis — L'écart de la passe #16 se chiffre, fichier par fichier

**Un écart déclaré mais non chiffré se redécouvre trop tard.** Déclarer « la passe #16 est moins
fidèle » ne dit pas à #20-#21 s'il faut recapturer avant d'importer. Le livrable porte donc un fichier dédié,
**`docs/migration/source/ECART-PASSE-16.md`**, cité depuis `INVENTAIRE.md` : « Ce que les 10 fichiers
de la passe #16 ont perdu », mesuré et non estimé.

Méthode, sans échappatoire : pour **chacun** des 10 fichiers, re-dériver son URL depuis le HTML
archivé **sous la convention #17**, puis comparer à ce qui est commité. Une ligne par fichier :

| Colonne | Contenu |
|---|---|
| Fichier | `accueil.md`, `la-meute.md`, `travail.md`, `portees/*` (3), `chiens/*` (4) |
| Zones perdues | `diywebEmotionHeader` ? `diywebSecondary` ? |
| U+00A0 déclarées / réelles | ex. **6 / 22** |
| Lignes de contenu perdues | compte **et** extrait de chacune |
| Associations de tableau éclatées | oui/non, et combien de lignes |
| `[IMAGE]` / `[LIEN]` perdus | compte **et** liste des `src` / `href` |
| **Verdict** | **à recapturer avant import** / **utilisable tel quel** |

Le **verdict** est la colonne qui compte : c'est elle que #20-#21 lira. Il se prononce sur la matière
réellement perdue, pas sur la convention employée — un fichier dont la re-dérivation ne change rien
est utilisable tel quel, et il faut le dire.

**Les fichiers restent inchangés.** Cette section les documente ; elle ne les remplace pas.

### 3.1 Les cinq zones

| Section du `.md` | Classe HTML du gabarit IONOS 2111 |
|---|---|
| `## Bandeau de gabarit` | `diywebEmotionHeader` |
| `## Contenu principal` | `diywebMain` |
| `## Colonne secondaire` | `diywebSecondary` |
| `## Colonne latérale` | `diywebSidebar` |
| `## Pied de page` | `diywebFooter` |

Une zone absente du document rend une section absente du `.md`, **jamais une section vide muette** :
le fichier écrit alors « *(zone absente du document)* ».

### 3.2 Les trois seules transformations autorisées

Aucune ne touche un mot.

1. `<br>` et fins de blocs deviennent des retours à la ligne ; les lignes vides consécutives sont
   réduites à une seule. **Une ligne dont le seul contenu est une U+00A0 est du contenu, pas une ligne
   vide** — elle survit. Les retours à la ligne au milieu d'une phrase sont **ceux du source**.
2. `<a>`, `<img>`, `<iframe>` deviennent `[LIEN href=…]…[/LIEN]`, `[IMAGE src=… alt="…"]`,
   `[IFRAME src=…]`. Ni une adresse ni un texte alternatif ne se perd.
3. Les entités HTML sont décodées. **Les U+00A0 sont conservées** et **comptées** dans l'en-tête.
4. Les cellules d'une même ligne de tableau sont **associées sur une seule ligne**, séparées par ` | ` :
   `1994 | ♂ Storm Haven Guépard | Brevet`. L'association année / chien / niveau est une donnée.

### 3.3 En-tête obligatoire de chaque fichier

```
# <nom-du-fichier-sans-extension>

- **URL source (encodée)** : …
- **URL source (lisible)** : …
- **Capturée le** : AAAA-MM-JJ
- **Réponse HTTP** : …
- **Taille du HTML reçu** : … octets
- **SHA-256 du HTML reçu** : `…`   (non reproductible — voir §3.4)
- **SHA-256 stable** : `…`          (reproductible — voir §3.4)
- **`<title>` du document** : …
- **HTML brut archivé** : `../html/<slug>.html`
```

Puis, verbatim :

> Recopie **verbatim**. Aucun mot, aucune date, aucun numéro n'a été corrigé, complété ni
> reformulé. Voir `../CONVENTION` (`docs/contracts/issue-19.md`) pour la méthode de capture.
> Espaces insécables (U+00A0) présentes dans ce fichier : **N** — conservées telles quelles.

### 3.4 Le SHA-256 brut n'est pas reproductible, et ce n'est pas un défaut

Le HTML servi par IONOS embarque l'**époque Unix de la requête** dans les URL de `all.css` / `all.js`.
Deux requêtes donnent **la même taille et un SHA différent**. La colonne « SHA-256 stable » se
recalcule ainsi et ne dépend que du contenu :

```
sha256( HTML_reçu  avec  s/(929224983(&amp;|&)t=)[0-9]{10}/\1EPOCH/g )
```

**Les invariants comparables sont la taille en octets et le compte d'U+00A0.**

## 4. Ce qui est archivé — arbitrage central de l'issue

**Décision gelée : on archive le HTML brut et les fichiers photo, pas seulement leurs adresses.**

La raison tient en une phrase : **cette issue est le seul moment du projet où le site source existe
encore**, et l'abonnement IONOS sera résilié *précisément parce que* le nouveau site existe. Une
capture qui ne retient que des URL fait dépendre la **contrainte 4** de la survie d'un service que
personne ici ne contrôle. Le texte se retape ; une photographie de 2011 non.

**Ratifié par l'utilisateur le 2026-08-23**, l'engagement de binaires dans l'historique git étant
irréversible et donc hors du pouvoir d'arbitrage de la chaîne. Ses mots : « *oui on peut archiver les
photos sur github, elles seront sur un serveur plus tard via le back de toute façon.* »

**Ce que cette réponse fixe, au-delà du oui** : dans son esprit, ces fichiers ne sont **pas la
destination finale**. Les photos finiront **servies depuis le serveur, téléversées par
l'administration**. `docs/migration/source/photos/` est donc exactement ce que ce contrat en fait —
une **archive de sauvegarde de l'original**, jamais la source de service.

**Conséquence directe et dure pour #20-#21** : puisque ces fichiers devront être **téléversés dans la
médiathèque**, la dette **T12** cesse d'être une recommandation et devient un **prérequis d'ordre**.
Voir §8.

| Artefact | Chemin | Rôle |
|---|---|---|
| `sitemap.xml` | `source/sitemap.xml` | **définition opposable des 52** |
| HTML brut, 1 fichier par URL | `source/html/<slug>.html` | **la source** — rend D11 auditable après coup |
| Réduction lisible, 1 fichier par URL | `source/{portees,chiens,pages}/…md` | **la lecture** — ce que #20-#21 utilise |
| Fichiers photo dédupliqués | `source/photos/<identifiant-ionos>.<ext>` | le contenu **irremplaçable** |
| Manifeste photos | `source/photos/MANIFESTE.md` | provenance, rendition, dimensions, empreinte |
| Outil de réduction | `source/outils/` | rend la réduction **rejouable sans refetch** |
| Preuve de complétude | `source/INVENTAIRE.md` | la soustraction du §1 |

**Règle de préséance, à écrire dans `html/README.md`** : le `.md` est ce qu'on lit et ce qu'on importe ;
le `.html` est ce qui **tranche** en cas de doute. Le `.html` n'est **jamais** importé tel quel.

**Plafond de garde sur les photos** : le volume total est **mesuré (HEAD) avant tout téléchargement**.
Au-delà de **150 Mo**, la chaîne s'arrête et remonte le chiffre au lieu de télécharger.

**Avertissement à écrire une fois, dans `html/README.md`** : le HTML brut contient la clé d'API Google
Maps d'IONOS (`AIzaSy…`, déjà visible en clair dans `chiens/chien-pegaz.md` depuis le lot 6) et les
liens de connexion à l'éditeur IONOS. Ce ne sont **pas des secrets du projet** — ce sont les clés
d'IONOS sur une page publique — mais l'analyse de secrets de GitHub peut les signaler.

## 5. Granularité : un fichier par URL, jamais par entité

**Interdit gelé : ne jamais agréger « tout ce que le site dit de Jango » dans un seul fichier.**

Agréger exige de **choisir** quelle mention fait foi quand la fiche du chien et le tableau de la page
Travail divergent. C'est un arbitrage de contenu, donc une reformulation, donc **D11 violée à la
capture** — et c'est exactement la classe d'erreur de la décision 46 (« Eenhoorn Sire Eenhoorn » :
une généalogie que personne n'avait saisie). La fusion appartient à #20-#21, où elle est visible et
discutable.

Ce qui est légitime ici : un **index croisé dérivé** (« ce nom est cité sur telles URL »), qui
n'écrase aucun texte et ne tranche rien.

## 6. Les deux captures de la même URL ne se suppriment pas

`/` et `/travail/` sont capturées deux fois, par deux passes, le même jour, avec deux découpages.
`pages/README.md` a déjà écrit lequel fait foi pour quoi. **Aucune n'est réécrite, aucune n'est
supprimée** ; l'inventaire les cite toutes deux sur la même ligne et rend l'arbitrage trouvable
depuis la racine. **Un doublon indexé cesse d'être une incohérence.**

## 7. Ce qui se documente et ne se tranche jamais

| Question | Ce que la capture fait |
|---|---|
| **Q12** — « truffe » et « cavage » sont-ils la même chose ? | Recopier la ligne *Cavage* et constater que le mot *truffe* n'apparaît nulle part. **Ne pas rapprocher les deux.** |
| **Q14** — « Autres disciplines » : disciplines ou rubrique fourre-tout ? | Recopier les quatre lignes **sous leur intitulé de section source**. Le site range lui-même Cavage sous « Autres disciplines » : **on recopie ce classement, on ne le déduit pas.** |
| **Q18** — lequel des deux points GPS est l'élevage ? | Relever **les deux** iframes avec leur zone et leur zoom (`43.514689, 6.242809` zoom 10 · `43.533404, 6.248086` zoom 16). **N'en désigner aucune.** |
| Disponibilité d'une portée | **Aucune page de portée n'énonce sa disponibilité.** L'encart « Chiots nés le 29/06/2026 / Tous réservés » est un **encart global de site**, identique sur toutes les pages, y compris sur une portée de 2016. **Ne jamais le lire comme la disponibilité de la portée affichée.** |
| Statut d'un chien | **Aucune fiche de chien n'énonce un statut.** Les seules formulations approchantes sont sur les pages d'index. |

## 8. Ce qui survit à cette issue — à écrire dans le livrable, pas seulement ici

**Dette T12, en tête de `photos/MANIFESTE.md`** : WordPress ne découpe et ne convertit une image
**qu'au téléversement**. Cette capture ne téléverse rien. **#20-#21 doit téléverser *après* le module
d'images de #8**, sinon tout le stock est à régénérer. La personne qui téléversera lit le manifeste,
pas `ETAT.md`.

## 9. Interdits

- On **n'efface, ne renomme et ne réécrit** aucun fichier existant de `docs/migration/source/`.
- On ne **corrige** ni orthographe, ni accent, ni date, ni graphie de n° LOF, ni incohérence
  apparente. *Ce qui semble faux dans un fichier de ce dossier **est** ce que le site affiche.*
- On n'**agrège** pas par entité (§5).
- On ne **tranche** aucune des questions du §7.
- On n'écrit **rien** hors de `docs/migration/source/**`. En particulier :
  `docs/ETAT.md`, `docs/migration/redirections.md`, `wp-content/**`, `docker/**` appartiennent à
  d'autres.

## 10. Arbitrages — désaccords tranchés et pourquoi

| Désaccord | Décision | Raison |
|---|---|---|
| Convention #16 (3 zones) ou #17 (5 zones) pour les nouveaux fichiers ? | **#17** | Mesuré strictement plus fidèle : `emotionheader` perdu et 16 insécables avalées côté #16. Le dossier porte déjà les deux formes ; il n'y a pas de convention unique à préserver, seulement la meilleure à généraliser. **L'écart des anciens est déclaré, pas corrigé.** |
| Photos : références seules, ou fichiers versionnés ? | **Fichiers versionnés**, avec plafond de garde à 150 Mo | Sans les fichiers, la contrainte 4 dépend de la survie de l'abonnement IONOS, qui sera résilié parce que le nouveau site existe. Coût assumé et irréversible : les binaires restent dans l'historique git. |
| Archiver le HTML brut, ou seulement le `.md` ? | **Les deux**, `.md` fait foi pour l'import, `.html` tranche | +2,1 Mo pour rendre D11 auditable après coup — l'antidote exact au reste écrit de #17 (« D11 y est invérifiable par quiconque, définitivement »). |
| Un fichier par URL ou par entité ? | **Par URL** | Agréger, c'est arbitrer, donc reformuler à la capture. Détruit aussi la preuve de complétude, qui est par URL. |
| Supprimer le doublon `accueil.md` / `pages/accueil.md` ? | **Non** | Deux lectures du même HTML, aucune ne contredit l'autre, et `pages/README.md` a déjà écrit laquelle sert à quoi. On l'**indexe**. |
| Versionner l'outil de réduction ? | **Oui**, `source/outils/` | Avec le HTML brut archivé, la réduction devient rejouable **sans refetch** — donc contestable même si le site source a disparu. |
| Nommer les fichiers photo par chien ? | **Non — par identifiant IONOS** | Nommer une photo « jango-2.jpg » suppose de savoir qui est dessus. C'est une invention de fait d'élevage. Le manifeste dit factuellement **quelles pages la citent**. |
