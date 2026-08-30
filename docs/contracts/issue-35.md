# Contrat d'interface — Issue #35 — Renommer le champ « Texte alternatif »

**Gelé le 2026-08-30.** SHA de référence des mesures du dépôt : `f0b05f1`.
**Version de WordPress des mesures du cœur : 6.9**, relevée par deux voies concordantes
(`wp-includes/version.php` dans le conteneur, et le pied de page de `wp-admin` **sur le chemin web**).
`docker/wordpress/Dockerfile` porte `FROM wordpress:php8.1-apache` : **la version n'est pas épinglée**.
Toute reconstruction d'image peut la changer, et toute mesure du cœur citée ici est datée « 6.9 ».

Ce contrat n'a **qu'un seul côté**. #35 ne touche pas au thème : aucun gabarit, aucune feuille de
style, aucun bloc, aucun octet sous `wp-content/themes/`. Il n'y avait donc pas deux propositions à
réconcilier, et ce document consigne les **arbitrages** plutôt qu'une frontière.

**Il a été écrit après l'implémentation, et non avant.** C'est un écart à la règle de la chaîne,
assumé pour une raison : sa pièce centrale est une table de chaînes sources du cœur WordPress, et le
cœur **n'est pas versionné dans ce dépôt** — `compose.yaml` ne bind-monte que le thème et l'extension.
Ces chaînes ne pouvaient être recopiées que depuis le conteneur. Les geler avant mesure aurait été les
écrire de mémoire, c'est-à-dire fabriquer exactement la sorte d'affirmation invérifiable qui a bloqué
les lots 9, 10 et 11.

---

## 1. Ce que l'issue prescrivait, et pourquoi c'est écarté — **mesuré, non supposé**

La tâche 1 de l'issue #35 prescrivait `add_filter( 'attachment_fields_to_edit' )`. **Ce crochet ne
pouvait pas mordre.** L'issue a été rédigée sans lire la dette **T16**, que `docs/contracts/issue-6.md:632`
(à `f0b05f1`) portait déjà et qui nommait le bon remède :

> « Le panneau de fichier du cœur nomme le texte alternatif **« Texte alternatif »**, là où MASTER
> §10.2 fige **« Description de la photo (pour les personnes aveugles) »**. […] **Correctif éventuel :
> filtre `gettext` strictement borné.** »

### La réfutation, avec ses chiffres

Sonde jetable (`attachment_fields_to_edit`, 10, 2) journalisant **inconditionnellement** les clés
reçues et posant la sentinelle `SONDE35ALT` sur `$champs['image_alt']['label']` si et seulement si la
clé existe. Relevés **sur le chemin web réel**, jamais au WP-CLI — leçon explicite de #31.

| Parcours | `SONDE35ALT` | `debug.log` |
|---|---|---|
| écran d'une portée (`post=16`) | **0** | vide |
| écran d'un chien (`post=12`) | **0** | vide |
| `upload.php` | **0** | vide |
| `upload.php?mode=grid` | **0** | vide |
| écran d'une photo (`post=15`) | **0** | 1 ligne, `cles=` **vide** |
| `admin-ajax.php action=query-attachments` | **0** | 1 ligne, `cles=` **vide** |
| `wp-includes/js/dist/block-library.js` (actif servi) | **0** | — |

**La cause exacte, dans le cœur 6.9**, et elle est plus précise que « le cœur retire `image_alt` » —
le cœur ne l'a jamais mis. Le crochet a **deux** points d'application :

- `wp-admin/includes/media.php:1509`, dans `get_attachment_fields_to_edit()` — **celui-ci porte bien
  `image_alt`**, mais n'est atteint que par `get_media_item()` (l'iframe hérité de téléversement).
  **Zéro ligne de journal : aucun écran d'aujourd'hui ne l'ouvre.**
- `wp-admin/includes/media.php:1935`, dans `get_compat_media_markup()` — **le seul qui s'exécute**. Il
  ne construit que les champs « compat » des extensions tierces, d'où `cles=` vide ; et il **détruit**
  `image_alt` trois lignes plus bas (`media.php:1937-1944`, `unset( …, $form_fields['image_alt'], … )`).

Là où le crochet s'exécute, `image_alt` est absent à l'entrée et détruit à la sortie ; là où
`image_alt` existe, le crochet ne s'exécute pas.

**La tâche 1 de l'issue est donc écartée avec sa preuve, et la sonde a été supprimée** (aucune trace
de `sonde-35` ni de `SONDE35` dans le dépôt).

---

## 2. Chaînes interceptées — la table gelée

Table portée par `wp-content/plugins/mtb-core/includes/admin/description-photo/bootstrap.php`.
Les clés sont les chaînes **sources anglaises**, recopiées **octet pour octet depuis la source PHP du
conteneur**, jamais depuis un `.po` ni un `.mo`.

| `msgid` source (domaine `default`) | Émissions dans le cœur 6.9 | Libellé français rendu |
|---|---|---|
| `Alternative Text` | **7** | `Description de la photo (pour les personnes aveugles)` |
| `Alt Text` | **1** | `Description de la photo (pour les personnes aveugles)` |

Le libellé est recopié verbatim de `design-system/MASTER.md:1100` (§10.2), dont la note ajoute :
*« Jamais « alt », jamais « attribut alt ». »* §10.4 range en outre `alt` parmi les **mots interdits à
l'écran, site et administration**.

### Carte d'émission complète — 8 sites, tous des étiquettes de description d'image

`Alternative Text` :

| Fichier : ligne | Fonction | Écran |
|---|---|---|
| `wp-admin/includes/media.php:1485` | `get_attachment_fields_to_edit()` | chemin hérité de téléversement |
| `wp-admin/includes/media.php:2982` | `wp_media_insert_url_form()` | insertion d'une image par adresse |
| `wp-admin/includes/media.php:3233` | `edit_form_image_editor()` | **écran de modification d'une photo** |
| `wp-includes/media-template.php:516` | `wp_print_media_templates()` | `tmpl-attachment-details-two-column` |
| `wp-includes/media-template.php:1074` | `wp_print_media_templates()` | `tmpl-embed-image-settings` |
| `wp-includes/media-template.php:1137` | `wp_print_media_templates()` | `tmpl-image-details` |
| `wp-includes/widgets/class-wp-widget-media-image.php:98` | `WP_Widget_Media_Image::get_instance_schema()` | composant image |

`Alt Text` : `wp-includes/media-template.php:768`, `wp_print_media_templates()`, **`tmpl-attachment-details`**
— le volet de droite de la fenêtre des photos, celui que l'éleveuse ouvre au bouton « Ajouter des photos ».

**Aucun débordement** : les 8 sites sont, sans exception, l'étiquette du champ de description d'image.
**Aucun ne passe par `_x()`** — tous sont `__()` ou `_e()` — donc `gettext_with_context` **n'est pas
posé**. Un crochet inutile est un coût non payé.

**Les deux clés rendent le même libellé et restent deux entrées.** C'est le même champ vu de deux
écrans ; les fondre ferait qu'une reformulation de l'une entraînerait l'autre en silence.

### Trois chaînes voisines écartées explicitement

Elles produisent aussi « Texte alternatif » à l'écran, mais ne sont **pas** nos `msgid` :

- `Alternative text to display when attachment is not displayed.` — `class-wp-rest-attachments-controller.php:1064` :
  description du **schéma REST**, une donnée d'API, jamais une étiquette d'écran ;
- `Alternative text` (t minuscule) — 9 occurrences dans `wp-includes/js/dist/block-library.js`, servies
  à `wp.i18n` : **hors d'atteinte de tout filtre PHP** ;
- la même avec contexte de barre d'outils — même canal.

---

## 3. Le texte d'aide du cœur n'est **pas** remplacé — décision motivée

Source, relevée en deux endroits (`wp-includes/media-template.php:161` et `wp-admin/includes/media.php:3241`) :

```
<a href="%1$s" %2$s>Learn how to describe the purpose of the image%3$s</a>. Leave empty if the image is purely decorative.
```

Le plan prévoyait de la remplacer par la phrase déjà gelée pour le champ jumeau du bloc
(`includes/blocks/fiche-information/editeur.js:235`). **La condition d'abstention s'est appliquée**, et
c'est le bon résultat :

1. **Elle porte une information factuelle que notre phrase supprimerait** — un lien vers l'arbre de
   décision du W3C. **On ne remplace jamais un texte par un autre qui en dit moins.**
2. Elle est passée à `sprintf()` et contient `%1$s`, `<`, `>` : exactement ce que le troisième interdit
   du module proscrit (un `%` de trop lève une `ArgumentCountError`, c'est-à-dire un écran blanc).
3. Elle est déjà correctement traduite en français par le cœur.

**Vérifié après la pose du module** — l'aide est intacte, lien compris :
*« Apprendre à décrire le but de l'image (ouvre un nouvel onglet). Laissez vide si l'image est purement
décorative. »*

**Corollaire à ne pas surévaluer** : le lien sortant vers `w3.org` **reste**. Ce n'était de toute façon
pas un gain D6 — un `href` n'est pas une requête, la décision 15 borne D6 au visiteur, et T4 consigne
que l'administration charge déjà 15 images depuis `s.w.org`, qui sont, elles, de vraies requêtes.

---

## 4. Écrans couverts — les deux chemins de la tâche 2, mesurés **séparément**

La tâche 2 exigeait « la fenêtre des médias **ET** l'écran de modification d'une image ». Ce sont deux
chemins WordPress distincts, avec **deux `msgid` différents** et **deux sources de rendu différentes**.
Ils sont donc mesurés et rapportés séparément.

### Chemin A — la fenêtre des photos (`wp.media`)

Le libellé est imprimé **côté serveur**, dans les gabarits underscore de `wp_print_media_templates()` ;
le JavaScript les clone tels quels. Le HTML brut de tout écran appelant `wp_enqueue_media()` fait foi.

| Écran, session `fabienne` | avant | après |
|---|---|---|
| `upload.php` | 4× « Texte alternatif » | **4× libellé français, 0 résiduel** |
| `upload.php?mode=grid` | 4× | **4×, 0 résiduel** |
| écran d'une portée (`post=16`) | 4× | **4×, 0 résiduel** |
| écran d'un chien (`post=12`) | 4× | **4×, 0 résiduel** |

Les quatre gabarits : `tmpl-attachment-details-two-column` · `tmpl-attachment-details` ·
`tmpl-embed-image-settings` · `tmpl-image-details`.

`tmpl-attachment-details` est **exactement** le volet qu'ouvre le bouton « Ajouter des photos » d'une
portée : `includes/fields/portee/ecran.js:260` (`f0b05f1`) appelle `window.wp.media( … )`. La chaîne va
du clic de l'éleveuse à la table sans rupture.

### Chemin B — l'écran de modification d'une photo (`post.php`)

Libellé imprimé directement par `edit_form_image_editor()`, **sans passer par aucun gabarit**.

| Écran, session `fabienne` | avant | après |
|---|---|---|
| `post.php?post=15&action=edit` | 1× « Texte alternatif » | **1× libellé français, 0 résiduel** |

**Les deux chemins sont couverts.**

---

## 5. Écrans NON couverts — nommés, avec leur portée réelle

| Non couvert | Pourquoi | Portée |
|---|---|---|
| **Blocs Image et Galerie du cœur, dans l'éditeur** | Étiquettes servies par `wp.i18n` depuis un catalogue JSON inline (`block-library.js`, **9 occurrences** de `Alternative text`). **Aucun filtre PHP ne les atteint.** La seule voie serait `load_script_translations`, qui réintroduirait de la machinerie i18n dans une extension dont le contrat #1 §7 gèle « aucune fonction i18n, aucun `.mo` », et dépendrait d'un artefact téléchargé au provisionnement, absent du dépôt. | **Réelle, vérifiée.** `git grep allowed_block_types f0b05f1 -- wp-content` ne rend **qu'une ligne, et c'est un commentaire** (`blocks/categorie-mtb/bootstrap.php:25`) : **aucun `allowed_block_types_all` n'est enregistré**. Le thème façonne bien `core/image` (`functions.php`, `theme.json`). **Ces blocs sont insérables par l'éleveuse aujourd'hui, et elle y lira encore « Texte alternatif ».** → dette **T-#35-a** |
| **Schéma REST des médias** | Chaîne distincte, et `is_admin()` vaut faux sur une requête REST | **Voulu.** REST rend des données, jamais des libellés d'écran. Écrit dans l'en-tête du module |
| **Le quatrième champ, `Description`** | Champ du cœur, hors du sujet de #35 et hors de MASTER §10.2 | La collision de vocabulaire (§7) subsiste à l'écran et n'est désamorcée **que par le guide** |
| **Chemin `media-upload.php` hérité** | Hors de tout parcours documenté ; aucune sonde ne l'a vu s'ouvrir | Renommé **par construction** (même `msgid`), mais **non observé à l'écran** |
| **`wp_media_insert_url_form()` et le composant image** | Deux des huit émissions | Renommées **par construction**, **non observées à l'écran**. Dit ici plutôt que présenté comme vérifié |

---

## 6. États spéciaux

**Aucun état spécial nouveau.** `aucune_portee`, `donnee_absente`, `parent_hors_elevage`,
`page_protegee` sont **inchangés** : #35 ne rend aucun contenu.

Un état propre à cette issue, à consigner :

| État | Émis par le serveur | Rendu à l'écran |
|---|---|---|
| `libelle_non_remplace` | **Rien — et aucun code ne peut le détecter.** Un filtre `gettext` ne peut pas savoir qu'une chaîne qu'il n'a jamais reçue existe | Le cœur a reformulé sa chaîne source : l'écran redit « Texte alternatif ». Panne **bénigne** (rien n'est cassé, aucune donnée perdue, retour exact à l'état d'avant) mais **silencieuse** |

**Parades retenues**, aucune en code :

1. **La formulation ordinale du guide** — « le premier des quatre », « le dernier des quatre » — reste
   vraie **que le filtre morde ou non**. Coût : zéro.
2. **Une ligne dans la rubrique « Ce n'est pas normal, signalez-le »** des fiches qui la portent :
   l'éleveuse devient le détecteur, et c'est le seul qui regarde vraiment l'écran.
3. **L'en-tête du module est daté** : les huit lignes du cœur, avec la version à laquelle elles ont été
   lues. Une chaîne future sait en dix secondes quoi remesurer.

Parades examinées et **écartées**, pour qu'on ne les re-litige pas : un `_doing_it_wrong` (impossible,
voir ci-dessus) · revérifier que la traduction diffère (rappellerait `translate()`, donc récursion) ·
mémoriser la dernière chaîne vue dans une option (une écriture en base sur un chemin de lecture).

---

## 7. Arbitrages

**A1 — `gettext` et non `attachment_fields_to_edit`.** L'issue prescrivait le second. Écarté sur
**mesure**, pas sur opinion (§1). T16 avait raison depuis #6.

**A2 — Un module distinct, `admin/description-photo/`, et non un ajout à `admin/medias/`.** Trois
raisons, toutes vérifiables : `admin/medias/bootstrap.php:25-36` assume **en majuscules** de n'avoir
aucune garde `is_admin()`, avec un motif entièrement valide — notre besoin est exclusivement wp-admin ;
y ranger notre code imposerait d'abandonner la garde, ou d'en ajouter une qui rendrait **à moitié
fausse** l'affirmation en majuscules de l'en-tête. Préfixer `_medias` pour désactiver un renommage de
libellé emporterait la conversion WebP et la sous-taille de galerie, **irrattrapables** sans régénérer
tout le stock. Enfin `admin/medias` agit sur le **fichier**, ce module sur le **mot** — le précédent est
écrit au dépôt : `admin/corbeille/bootstrap.php:16-17`.

**A3 — Le texte d'aide n'est pas remplacé.** Voir §3. On ne remplace jamais un texte par un autre qui
en dit moins.

**A4 — Comparaison sur la chaîne source anglaise, jamais sur la traduction française.** Le module mord
donc à l'identique sur une installation restée en `en_US` — cas que `docker/provision/provision.sh`
documente comme possible.

**A5 — Le module n'échappe rien, en écart explicite à `CLAUDE.md`.** Il **n'imprime rien** : il rend une
chaîne que le cœur échappe et imprime lui-même. Échapper ici doublerait l'échappement et sortirait une
apostrophe en `&#039;` au milieu de l'étiquette. La règle de la maison vise **le rendu** ; ce module
n'est pas le rendu. Corollaire de conception, pas d'espoir : **aucune chaîne de remplacement ne porte
`%`, `<`, `>` ni `&`**, ce qui la rend juste que le cœur l'échappe ou non.

**A6 — Paramètres et retour non typés**, comme `Medias\format_de_sortie()` et
`Corbeille\completer_messages()`, et pour le même motif écrit : sous `declare(strict_types=1)`, un tiers
passant autre chose qu'une chaîne transformerait un rendu d'étiquette en **erreur fatale au milieu d'un
écran**. Un libellé resté en anglais vaut infiniment mieux qu'un écran blanc.

**A7 — La collision de vocabulaire est payée par le guide, en formulation ordinale.** Après renommage,
la colonne de droite porte **deux** champs commençant par « Description » :

> **Description de la photo (pour les personnes aveugles)** — Titre — Légende — **Description**

Or `composant-galerie-photos.md:119` et `chien-ajouter-un-chien.md:178` disaient, mot pour mot :
*« N'écrivez pas dans le champ Description, plus bas »*. **Le nom actuel, si mauvais soit-il,
désambiguïsait par accident ce que le nouveau nom confond** — c'est le coût réel du renommage, et il est
payé côté guide : on désigne les champs par leur **rang** (« le premier des quatre », « le dernier des
quatre »), jamais par leur seul nom. Bénéfice second : la formulation ordinale reste vraie même si le
filtre cesse un jour de mordre (§6).

**A8 — `docs/contracts/issue-37.md:95` est rendu caduc, et il faut l'écrire.** Cette ligne figeait au
lot 10 : *« le champ de description d'un média : `Texte alternatif` — les trois fiches qui l'écrivaient
ainsi avaient raison »*. Elle **était juste alors** : #37 relevait ce que l'écran disait ; #35 change
l'écran. Ce qui devient faux est son **verdict**, pas sa mesure. Sans cette note, deux contrats gelés se
contrediraient.

**A9 — Le chiffre « trois fiches » de l'issue est faux : il y en a SIX.** Mesuré à `f0b05f1`,
`git grep '[Tt]exte alternatif' -- docs/guide/` → **6 fiches, 9 lignes** (sur 24 fiches au total) :
`chien-ajouter-un-chien.md:172` · `portee-ajouter-une-portee.md:191` ·
`composant-galerie-photos.md:111,117,126` · `composant-coordonnees-plan.md:117` ·
`composant-bandeau-ouverture.md:206` · `contenu-repris-de-l-ancien-site.md:233,236`.
Le « trois » venait de `issue-37.md:95` et datait de la rédaction de l'issue.

**A10 — Q17-bis n'est pas rouverte.** Tranchée au lot 4 en faveur de `MASTER.md` §10.2 (décision 39).
#35 applique §10.2, point. **Un élément neuf est toutefois versé au dossier sans rien trancher** : le
cœur ne rend le champ que sous garde de type de fichier — et la garde n'est **pas**
`wp_attachment_is_image()` comme le plan l'annonçait, mais `str_starts_with( $post->post_mime_type, 'image' )`
(`wp-admin/includes/media.php:3231` et `:1474`) et `'image' === data.type` côté client
(`media-template.php:514`, `:766`). **Un PDF — un pedigree numérisé — n'affiche donc ni l'ancien libellé
ni le nouveau.** Si cela se confirme par observation, l'objection de Q17-bis ne survit **que** du côté
des attributs de bloc, là où elle est née, et pas du côté de la médiathèque. **Raisonné depuis le code,
non observé** : voir §9.

---

## 8. Coût mesuré du filtre `gettext`

A/B par le préfixe `_` du chargeur (contrat #1 §1) : aucune ligne de code modifiée. Contrôle des deux
bascules : à l'état `_`, `upload.php` rend **0** libellé français ; à la remise en place, **4**.
**Le dossier est remis en place et actif.**

**Nombre d'appels par écran** (session `fabienne`), identiques avec et sans le module — il ajoute un
rappel, pas un appel : `edit.php?post_type=mtb_portee` **1 599** · écran d'une portée **1 710** ·
`upload.php` **1 062** · `post-new.php?post_type=page` **3 445**. Témoin public anonyme : 575, **rappel
non attaché**.

**Temps**, deux séries de 20 requêtes par écran et par état, internes au conteneur :

| Écran | médiane sans | médiane avec | Δ | bande de bruit (min–max) |
|---|---|---|---|---|
| `edit.php?post_type=mtb_portee` | 840,3 ms | 901,2 ms | +60,9 ms (+7,2 %) | sans 790,4 · avec 1 279,9 |
| écran d'une portée | 723,0 ms | 873,0 ms | +150,0 ms (+20,7 %) | sans 2 256,4 · avec 1 179,6 |
| `upload.php` | 534,3 ms | 669,0 ms | +134,7 ms (+25,2 %) | sans 1 003,6 · avec 1 326,7 |
| `post-new.php?post_type=page` | 624,1 ms | 650,3 ms | +26,2 ms (+4,2 %) | sans 358,3 · avec 1 234,5 |

> **Δ est inférieur à la dispersion de la mesure (358,3 ms dans le meilleur cas, 2 256,4 ms dans le
> pire) : la mesure ne distingue pas les deux états.**

Le mot « négligeable » n'est pas employé, et la série hôte→Docker le confirme *a contrario* : elle rend
**Δ négatif sur trois écrans sur quatre**, impossible pour un effet réel. La machine portait trois
chaînes en parallèle et une reconstruction d'image.

**Coût par appel, banc hors requête clairement étiqueté comme tel** : `remplacer_libelle()` appelée
3 445 fois sur quatre échantillons tournants, 12 séries, médiane → **≈ 0,092 µs par appel** (boucle et
indexation comprises, donc **sur-estimation**). Sur l'écran le plus chargé : 3 445 × 0,092 µs =
**0,32 ms**, soit **trois ordres de grandeur sous la bande de bruit** de la mesure HTTP. C'est ce qui
explique pourquoi celle-ci ne peut rien voir.

---

## 9. Ce qui n'a **pas** été vérifié — à ne pas lire comme vérifié

- **PDF et documents non-images : non mesuré dynamiquement.** La médiathèque ne contenait **qu'une
  seule pièce jointe** (`image/png`), et aucun document n'a été téléversé — la pile est partagée avec
  deux autres chaînes. Les gardes du cœur sont citées en A10 ; **le raisonnement tient, l'observation
  manque.**
- **Deux des huit émissions non observées à l'écran** : `wp_media_insert_url_form()` et
  `WP_Widget_Media_Image::get_instance_schema()`. Renommées par construction, non constatées.
- **Le chemin `media-upload.php` hérité** : jamais ouvert par aucun parcours mesuré.
- **Aucune passe axe, aucun parcours clavier réel, aucun 360 px, aucun zoom 200 %.** Ce qui a été
  vérifié en accessibilité est **l'appariement `for`/`id`**, 1:1 sur les cinq identifiants de champ des
  trois écrans, `aria-describedby="alt-text-description"` intact et sa description toujours présente.
  Le libellé passe de 16 à **52 caractères** : **son rendu à 360 px et à zoom 200 % n'a pas été
  observé.**

---

## 10. Captures du guide rendues fausses — **travail de #46, pas de #35**

`docs/ETAT.md` consigne le recouvrement **#35 ↔ #46 sur `docs/guide/*.md`**. #46 n'est pas dans ce lot.
#35 **nomme** les captures à reprendre plutôt que de les corriger en douce ou de les laisser mentir —
une capture montrant un libellé que l'écran ne dit plus est exactement le défaut qui a bloqué trois lots.

| Capture | Verdict | Preuve |
|---|---|---|
| `captures/galerie-description-photo.png` | **fausse, certain** | `composant-galerie-photos.md:126` — **son propre texte de remplacement nomme le champ** (« avec le champ **Texte alternatif** en premier »). **L'image ET son alternative** sont à reprendre |
| `captures/galerie-fenetre-photos.png` | **fausse, certain — mesuré à l'image** | `composant-galerie-photos.md:58`. Le PNG a été **ouvert et regardé**, non déduit : la colonne de droite **est dans le cadrage**, sous le titre « DÉTAILS DU FICHIER JOINT », et l'étiquette **« Texte alternatif » y est lisible en clair**, suivie de Titre, Légende, Description, URL du fichier. Son texte de remplacement, lui, ne nomme pas le champ : **il reste juste et n'a pas été touché** — seuls les pixels sont à reprendre |

**Deux écarts relevés en ouvrant `galerie-fenetre-photos.png`, versés à #46 et non corrigés ici** (ils
relèvent du cadrage de la reprise, pas du texte) : l'image montre **une seule photo cochée** alors que
son texte de remplacement dit « trois photos cochées » ; et la valeur visible dans le champ
(« Photographie publiée sur la page de la portée U3 2023 ») est cohérente avec
`contenu-repris-de-l-ancien-site.md` §6 — **ce n'est pas une donnée d'essai**, contrairement au défaut
rattrapé au lot 10.

**Indemnes, vérifiées une par une et non supposées** : `chien-photo-principale.png`,
`portee-photo-principale.png`, `repris-photo-principale.png` (encadré « Photo principale ») ·
`portee-galerie.png`, `chien-photos.png` (encadré de la fiche) · `fiche-information-reglages-photo.png`,
`fiche-information-panneau-sans-photo.png`, `coordonnees-panneau-plan.png`,
`coordonnees-plan-introuvable.png` (**panneaux de nos blocs**, qui portent déjà le libellé de MASTER et
**restent vrais**) · `galerie-photos-disparues.png`, `galerie-panneau-ordre.png`,
`galerie-etat-vide.png`, `galerie-apercu.png`, `galerie-inserteur.png`.

Conditions gelées dont #46 hérite (`docs/contracts/issue-37.md` §4) : session **`fabienne`**,
`MTB_FIXTURES=1`, 1280 px, `.update-nag` masqué, aucune retouche. **Et aucun encart « Capture à
prendre » ne doit reparaître** : les 84 retirés au lot 10 étaient des consignes de développeur dans le
manuel de l'éleveuse.

---

## 11. Interdits

- **Le thème n'est pas concerné**, et cette phrase est elle-même la clause : il ne présume jamais du
  libellé d'un champ d'administration, ne reformate jamais `_wp_attachment_image_alt` (il l'imprime tel
  quel), et ne pose aucun filtre `gettext`.
- **Le module n'expose rien** : aucune fonction de lecture, aucun bloc, aucun `apply_filters`, aucun
  `do_action`, aucune constante, aucune variable globale. La table **n'est pas filtrable, délibérément**
  — un libellé figé par `MASTER.md` §10.2 n'a pas à être surchargeable.
- **Aucun accès base, aucune requête, aucun appel HTTP, aucun `$wpdb`.**
- **Aucune fonction de traduction dans le rappel** — récursion infinie garantie sinon.
- **Aucun `get_current_screen()`, aucune expression régulière, aucun `stripos`, aucun `str_replace`** :
  comparaison stricte et exacte de la chaîne entière, ou rien. C'est ce qui borne mécaniquement le
  module aux huit émissions relevées.
- **La valeur saisie n'est jamais touchée.** Vérifié octet pour octet sur l'écran d'une photo
  (`sha256` identique avant/après, 217 octets) et sur les deux réponses REST (anonyme et `fabienne`,
  **identiques octet pour octet**). La décision 12 est respectée : rien n'est normalisé, rien n'est
  déplacé.

---

## 12. Dette ouverte par cette issue

| Dette | Description | Portée | Quand |
|---|---|---|---|
| **T-#35-a** | Les blocs **Image** et **Galerie** du cœur diront toujours « Texte alternatif » dans l'éditeur : leurs étiquettes viennent de `wp.i18n`, hors d'atteinte de tout filtre PHP. **Aucun `allowed_block_types_all` n'est enregistré** (vérifié à `f0b05f1`), donc ces blocs sont **insérables par l'éleveuse aujourd'hui**. C'est le trou le plus visible qui subsiste, et le guide doit le dire plutôt que de la laisser chercher. | `includes/blocks/` ou l'epic Gabarits | avec `allowed_block_types_all`, epic Gabarits |
| **T-#35-b** | Le menu d'administration s'appelle **« Médias »**, alors que `MASTER.md` §10.4 range « média » parmi les mots interdits (« dire *photo* »). Le `msgid` `Media` a **6 émissions** `__( 'Media' )` et **12** au total : le renommer exige **sa propre carte d'émission mesurée**, sinon il déborde. Le module `description-photo` est fait pour accueillir la ligne le jour venu. | `includes/admin/description-photo/` | issue `a11y`/`doc` dédiée |
| **T-#35-d** | **La collision de vocabulaire (A7) n'est pas réparable par le guide, seulement contournable.** La formulation ordinale fonctionne, mais elle demande à l'éleveuse de **compter des champs au lieu de lire un nom** — exactement ce que le projet cherche à lui épargner. Le vrai remède serait de renommer ou de masquer le **quatrième** champ du cœur, `Description`, qui ne sert dans **aucun** parcours documenté. Relevé par `doc-client-mtb` en payant la parade. | `includes/admin/description-photo/` | avec T-#35-b, même méthode : carte d'émission mesurée d'abord |
| **T-#35-c** | La version de WordPress **n'est pas épinglée** (`docker/wordpress/Dockerfile`, `FROM wordpress:php8.1-apache`). Toute reconstruction peut reformuler une chaîne source et faire cesser le renommage **en silence** (§6). Hors empreinte de #35 (`docker/**` appartient à #30). | `docker/wordpress/Dockerfile` | issue `infra` |
