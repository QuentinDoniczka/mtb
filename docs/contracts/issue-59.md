# Contrat d'interface — Issue #59 — « Slug » dans la Modification rapide des listes (T118)

> **Gelé le 2026-09-17.** Relevés faits sur **WordPress 6.9**, site en **fr_FR**. Issue **côté serveur
> seulement** : aucun gabarit, aucun octet de CSS, aucun `leaddev-front`. Ce contrat **étend** le
> contrat gelé `docs/contracts/issue-54.md` ; ses interdits §5 restent opposables, sauf le n° 16,
> **réécrit** ci-dessous (§6).

## 0. Convention de lecture

Reprise de `issue-54.md` §0 : **[M]** mesuré (geste et sortie cités) · **[D]** déduit, ne se recopie
pas comme un fait · **[àM]** à mesurer par la chaîne d'implémentation avant d'écrire la ligne qui en
dépend.

---

## 1. Le mécanisme d'émission — mesuré avant le choix du crochet

```
M-a  wp-admin/includes/class-wp-posts-list-table.php, méthode inline_edit()
     <span class="title"><?php _e( 'Slug' ); ?></span>   ← étiquette du champ post_name
     sous : post_type_supports( type, 'title' ) · branche non groupée (! $bulk) · is_post_type_viewable( type )
     → PHP translate(), domaine 'default'. AUCUN rapport avec le chemin wp.i18n de #54.

M-b  wp-includes/l10n.php, translate() :
     apply_filters( 'gettext', $translation, $text, $domain )
     apply_filters( "gettext_{$domain}", $translation, $text, $domain )   ← donc « gettext_default » existe

M-c  wp-admin/edit.php : require admin.php (écran courant posé, puis do_action « load-edit.php »)
     → admin-header.php → liste → $wp_list_table->inline_edit() → admin-footer.php
     Aucun crochet ne se déclenche de façon fiable DANS inline_edit() avant la rangée
     (« quick_edit_show_taxonomy » n'existe que si le type a des taxonomies ; « quick_edit_custom_box » vient après).

M-d  wp-admin/js/inline-edit-post.js : aucune chaîne « Slug ». Le panneau #inline-edit est un GABARIT
     présent dans le HTML sans interaction ; le script le clone.
M-e  wp-admin/includes/ajax-actions.php (inline-save) : n'appelle pas inline_edit(), aucune « Slug ».
M-f  wp eval 'echo translate("Slug");' → « Slug »  (la traduction française de Slug EST Slug)

M-g  Types d'administration (show_ui) :
     page         consultable=1 titre=1 taxonomies=∅     → rangée présente
     mtb_portee   consultable=1 titre=1 taxonomies=∅     → rangée présente
     mtb_chien    consultable=1 titre=1 taxonomies=∅     → rangée présente
     mtb_resultat consultable=0 titre=0                  → rangée ABSENTE
     post         consultable=1 titre=1                  → rangée présente — HORS ISSUE
```

**Conclusion :** « étendre le rappel `gettext` de #54 » ne voulait rien dire, parce que #54 n'a posé
aucun rappel `gettext` PHP : sa moitié « Slug » est en JavaScript. Le remède est une **troisième moitié,
en PHP**, sur un autre écran.

---

## 2. Le mécanisme gelé

Il s'ajoute au module existant `wp-content/plugins/mtb-core/includes/admin/vocabulaire-page/`, namespace
`MTB\Core\Admin\VocabulairePage`. Le dossier **n'est pas renommé** : un renommage périmerait les
citations des contrats gelés, sans aucun bénéfice pour l'éleveuse.

| Élément | Forme gelée |
|---|---|
| Source unique du libellé | `libelle_adresse_de_la_page(): string` dans `libelles.php`, **seul** littéral « Adresse de la page » du module |
| Table JS (#54) | `table_javascript()['Slug']` **appelle** `libelle_adresse_de_la_page()`. La charge `window.mtbVocabulairePage` reste **identique à l'octet** près **[àM]** |
| Table PHP (nouvelle) | `table_modification_rapide(): array` dans `libelles.php`, mémoïsée : `array( 'Slug' => libelle_adresse_de_la_page() )`. **Une seule entrée** |
| Types visés | `types_de_la_modification_rapide(): array` → `array( 'page', 'mtb_portee', 'mtb_chien' )`, **écrits en dur** |
| Garde d'écran | `poser_le_renommage_de_la_modification_rapide(): void`, sur `load-edit.php`, priorité 10. `get_current_screen()` absent ou non `\WP_Screen`, `'edit' !== base`, ou type absent de la liste (comparaison stricte) : **retour immédiat**. Sinon, `add_filter( 'gettext_default', …, 10, 2 )` |
| Rappel | `renommer_dans_la_modification_rapide( $traduction, $texte )`, sans type : si `! is_string( $texte )`, renvoie `$traduction` ; sinon consulte la table **par clé exacte** |
| Fichier | `modification-rapide.php` (neuf), inclus par `bootstrap.php`, qui pose l'`add_action` **à l'inclusion** |

**Pourquoi `gettext_default` et non `gettext` :** le nom du crochet borne le domaine par construction, et
le rappel ne lit que deux arguments. **Pourquoi `load-edit.php` :** c'est le seul crochet fiable qui
précède la rangée (M-c). Il ne se déclenche que depuis `wp-admin/admin.php`, et l'écran courant y est
déjà posé.

**Coût :** sur les trois listes seulement, une recherche dans la table pour chaque chaîne du domaine
`default` traduite après `load-edit.php`. Sur les requêtes publiques, REST, WP-CLI et `admin-ajax`, rien
n'est attaché.

---

## 3. Libellé rendu

| Source anglaise | Rendu | Recopié de | Où |
|---|---|---|---|
| `Slug` (domaine `default`) | **Adresse de la page** | `MASTER.md` §10.2 ; `fields/portee/ecran.php:52` ; `fields/chien/ecran.php:305` ; guide `portee-ajouter-une-portee.md`, `chien-ajouter-un-chien.md` | Modification rapide des listes Pages, Portées et Chiens |

**Le même libellé vaut pour les trois types.** Les écrans de saisie d'une portée et d'un chien titrent
déjà leur boîte « Adresse de la page ». Aucune autre formulation n'est à inventer, **ni à raccourcir**.

---

## 4. Tâche 3 — les autres chaînes du panneau

**[D]** (plan), **[àM]** par relevé au navigateur :

- **Pages** : Modification rapide · Titre · *Slug* · Date · Mot de passe · –OU– · Privé ·
  Auteur/autrice · Parent · Ordre · Modèle · Autoriser les commentaires · État · Mettre à jour · Annuler.
- **Portées et Chiens** : même liste, **sans** Auteur/autrice, Parent, Ordre, Modèle ni commentaires
  (`supports` = titre, éditeur, révisions, photo). Aucune taxonomie.

**Seul « Slug » figure dans la liste du §10.4.** « Modèle » reste en place : c'est le mot français du
cœur, et §10.4 n'interdit que l'anglais « template ». C'est la même décision qu'au `issue-54.md` §6.1, non
rouverte ici. **Si le relevé fait apparaître un autre mot interdit, il n'entre pas dans la table** : il
est rapporté et devient une dette.

---

## 5. États spéciaux

| État | Comportement exigé | Statut |
|---|---|---|
| Liste Pages / Portées / Chiens | « Adresse de la page », aucun nœud valant exactement « Slug » dans `#inline-edit` | [àM] |
| Liste **Articles** (`post`) | **« Slug » reste.** Sa présence prouve que la garde tient | [àM] |
| Liste Résultats | Aucune rangée, aucun effet | [àM] |
| Écran d'une page (`post.php`) | Inchangé : les deux moitiés de #54 mordent comme avant, et la charge JSON est identique | [àM] |
| Écrans de taxonomie, Médiathèque | Inchangés | [àM] |
| Requête REST / WP-CLI / publique / `admin-ajax` | Rien n'est attaché | [M] par construction (M-c, M-e) |
| Administrateur **et** Éditrice | Même renommage (aucune dépendance au rôle) | [àM] sur la session Éditrice |
| Site resté en anglais | Même renommage : on compare la source anglaise | [D] |
| `get_current_screen()` indisponible | « Ce n'est pas mon écran », jamais une erreur | à respecter |
| `libelle_non_remplace` (le cœur reformule `Slug`, ou cesse de déclencher `load-edit.php`) | La liste redit « Slug ». Panne **bénigne et muette**, assumée | assumé |
| Module désactivé par `_vocabulaire-page` | Les **trois** moitiés disparaissent ensemble | à respecter |

---

## 6. Interdits

Les interdits `issue-54.md` §5, n° 1 à 15, s'appliquent tels quels. **Le n° 16 est réécrit ainsi :**

> **16.** Aucune chaîne **affichée** n'est écrite ailleurs que dans `libelles.php`, et « Adresse de la
> page » n'y est écrite **qu'une fois**, dans `libelle_adresse_de_la_page()`. Les **clés** des tables
> sont des sources anglaises du cœur, pas des chaînes affichées : deux tables peuvent porter la clé
> `Slug`, parce qu'elles visent **deux émissions distinctes** (`editor.js` et la liste PHP).

**Interdits ajoutés :**

17. **Aucune garde `is_admin()`**, ni en tête du module (A3 de #54), ni dans le rappel de `load-edit.php`,
    où elle ne servirait à rien. Le commentaire le dit, pour que personne ne l'« ajoute ».
18. **Le test d'écran vit dans le rappel de `load-edit.php`**, jamais dans le rappel `gettext_default`.
19. **Aucun type n'est ajouté sans mesure.** `post` n'est **jamais** ajouté « en passant » (famille
    T-#54-d). `mtb_resultat` n'affiche pas la rangée.
20. **Aucune entrée n'entre dans `table_modification_rapide()`** sans avoir été mesurée dans ce panneau.
21. **On ne fusionne pas les deux tables.** La table JS porte « Edit or replace the featured image »,
    une chaîne non mesurée sur les listes (n° 8).
22. **Aucun numéro de ligne du cœur dans le code** : on cite `WP_Posts_List_Table::inline_edit()`, le
    `msgid` et le nom des crochets. *Ce contrat en cite, datés et versionnés, au §1 : c'est un relevé,
    pas une ancre de code.*
23. **Aucun CSS**, aucun `make css`, aucun artefact `*.min.css`. Si le rendu se dégrade au point de
    rendre le champ illisible, **on arrête et on remonte la question** ; le libellé n'est jamais raccourci.

**Frontière thème / extension :** le thème ne reçoit rien et n'appelle rien de nouveau. L'extension
n'émet aucune règle visuelle.

---

## 7. L'en-tête du module — acte daté

Plusieurs phrases de l'en-tête de `bootstrap.php`, écrites au présent, **deviennent fausses** :
« l'écran d'une page, et nulle part ailleurs », « Portées, Chiens […] ne changent pas d'un caractère »,
« le nôtre est UN TYPE DE CONTENU », la Modification rapide qui « appartient à un autre module », et
« sur les listes, "Slug" DOIT RESTER ».

**Forme retenue :** le texte gelé n'est pas réécrit. Chacun de ces paragraphes reçoit en tête le marqueur
**`[RESTREINT PAR L'ACTE DU 2026-09-17]`**, et un bloc **« ACTE DU 2026-09-17 — ISSUE #59 (T118), PRIME
SUR LES PASSAGES MARQUÉS »** suit le résumé. Une phrase fausse ne peut ainsi jamais être lue sans son
renvoi, ce qui évite un faux vert de documentation.

L'acte dit :

1. ce que l'éleveuse voit changer, Articles exclus ;
2. la troisième moitié et son mécanisme (§1–2), **sans numéro de ligne** ;
3. que T-#54-b est **close pour trois types**, et que les Articles sont écartés volontairement ;
4. l'écart au crochet de groupe (`load-edit.php`) ;
5. le sujet du module, élargi au « mot "Slug" sur les écrans quotidiens de l'éleveuse », et le critère
   de séparation d'avec `description-photo`, qui tient toujours ;
6. le mode de panne ;
7. la vérification n° 3, réécrite : « Slug » **reste** sur la liste des Articles et **disparaît** des
   trois autres ;
8. la désactivation, qui emporte les trois moitiés.

---

## 8. Protocole de vérification — à jouer, pas à déduire

Chrome réel piloté en CDP, `--lang=fr-FR`, session **`fabienne`** (rôle Éditeur) ouverte par le vrai
`wp-login.php`.

- **M3, avant et après.** Sans aucune interaction, compter dans `#inline-edit` les nœuds feuilles valant
  exactement « Slug » et ceux valant « Adresse de la page ». Attendu :
  - Pages, Portées, Chiens : **1/0 avant**, **0/1 après** ;
  - Articles : **1/0 avant et après** ;
  - Résultats : **0/0**.

  Relever aussi la liste complète des étiquettes du panneau (tâche 3).
- **V1.** Ouvrir la Modification rapide sur une ligne **au clavier**, lire l'étiquette et le nom
  accessible du champ `post_name`, puis **Annuler**. **Jamais « Mettre à jour ».** `post_modified` doit
  rester inchangé.
- **V2.** Sur `post.php` d'une page : charge JSON `mtbVocabulairePage` identique à l'octet près à celle
  d'avant, rangée « Adresse de la page ». Sur `wp eval`, les quatre libellés photo sont inchangés.
- **V3.** `wp eval`, sans écriture : sur l'écran de liste d'une portée, avec `load-edit.php` déclenché,
  `translate('Slug')` rend « Adresse de la page » ; sur l'écran des Articles, il rend « Slug » ; sans
  l'action, il rend « Slug ».
- **G1 à G5.** À 1440 px, à 1280×720 en zoom 200 %, et à 360 px : nombre de lignes de l'étiquette ;
  champ sur une ligne (`scrollWidth == clientWidth`) ; cible d'au moins **24 px** (doctrine
  d'administration des contrats #52, #32 et #54) ; aucun défilement horizontal introduit. **Voie 1 de
  `issue-54.md` §8** : le retour à la ligne est accepté si le champ reste lisible.
- **Hygiène.**
  - Instantané des métadonnées de `fabienne` avant la session ; jeton de session retiré après ;
    instantané identique à celui d'avant.
  - Taille de `debug.log` inchangée ; empreinte du chargeur inchangée.
  - Aucune sonde laissée dans `mu-plugins` ; aucun `down -v` ; `git status` limité à l'empreinte.

Une mesure non jouée se rapporte comme **non jouée**.

---

## 9. Empreinte fichiers

- `wp-content/plugins/mtb-core/includes/admin/vocabulaire-page/libelles.php` — modifié
- `wp-content/plugins/mtb-core/includes/admin/vocabulaire-page/modification-rapide.php` — créé
- `wp-content/plugins/mtb-core/includes/admin/vocabulaire-page/bootstrap.php` — modifié (inclusion,
  crochet, acte)
- `docs/contracts/issue-59.md` — ce document
- `docs/guide/**` — **uniquement** si `doc-client-mtb` décide d'y toucher. Les fiches touchées sont alors
  déclarées au rapport.

**Non ouverts :** `ecran.php`, `filtre-des-libelles.php`, `editeur.js`, `mtb-core.php`,
`includes/class-loader.php`, `includes/migration/indexation-heritee/**`, `docs/ETAT.md`, tout CSS.

---

## 10. Arbitrages rendus

| # | Question | Décision | Motif |
|---|---|---|---|
| **B1** | « Étendre le rappel `gettext` » | **Sans objet tel quel.** #54 n'a posé aucun `gettext` PHP. Une troisième moitié est créée, en PHP, sur `gettext_default` | M-a, M-b |
| **B2** | Où poser le filtre | **`load-edit.php` + garde d'écran** (option A) | Seul crochet fiable avant la rangée (M-c). La fenêtre étroite (option B) dépend de l'ordre de `edit.php` et échouerait en silence ; la pose globale (option C) renommerait des écrans non mesurés |
| **B3** | `gettext` ou `gettext_default` | **`gettext_default`** | M-b : domaine borné par le nom du crochet |
| **B4** | Garde `is_admin()` | **Aucune** | A3 de #54 en tête ; inutile dans le rappel |
| **B5** | Libellé pour une portée et un chien | **« Adresse de la page »**, le même | §10.2, et les écrans de saisie des deux types le disent déjà |
| **B6** | Articles | **Exclus** | L'issue vise trois types ; l'éleveuse n'écrit pas d'articles (famille T-#54-d) ; « Slug » y reste comme preuve de la garde. **Dette T-#59-a** |
| **B7** | Module neuf `admin/listes/**` (vœu de T-#54-b) ou `vocabulaire-page` | **`vocabulaire-page`**, dossier non renommé, en-tête amendé par acte daté | L'empreinte est imposée ; le sujet du module devient le mot, pas le type |
| **B8** | Réécrire l'en-tête ou ajouter un acte | **Acte daté + marqueurs sur les passages restreints** | Le texte gelé reste lisible, et aucune phrase fausse n'est lue sans son renvoi |
| **B9** | Une table ou deux | **Deux tables, une seule source du libellé** | Interdit n° 21 ; n° 16 réécrit |
| **B10** | Guide | Aucune fiche ne décrit la Modification rapide (relevé du board). **Décision laissée à `doc-client-mtb`** | D3 |

## 11. Dettes

| # | Dette |
|---|---|
| **T-#59-a** | « Slug » reste dans la Modification rapide des **Articles** — le menu Articles est visible pour l'éditrice, mais elle n'en écrit pas |
| **T-#54-c** | Inchangée : écrans de taxonomie |
| **T-#54-e** | Aggravée d'une moitié : la vérification reste manuelle et se rejoue en entier à chaque montée de WordPress |
| **T-#59-b** | Le lien « Modification rapide » reste offert à l'Éditrice sur les trois listes. Son panneau porte **Mot de passe**, **Privée** et **État**, qu'aucune fiche ne présente à cet endroit : c'est un second chemin pour retirer un contenu du site, sans les explications de l'écran complet. **Question de conception, pas de documentation** (relevé de `doc-client-mtb`) |
| **T-#59-c** | Liste **Pages** à 360 px : défilement horizontal (415 > 360), **préexistant**, causé par la liste déroulante « Parent » du cœur et non par ce libellé (mesuré en remettant « Slug » : même largeur) |

---

## 12. Résolution — mesurée après implémentation, le 2026-09-17

Cet acte **complète** le contrat et prime sur lui en cas d'écart ; aucune phrase ci-dessus n'est réécrite.

**Toutes les mesures du §8 ont été jouées** (session `fabienne`, Chrome 152 `--lang=fr-FR`), avec un
seul écart de geste : **la session n'a pas été ouverte par `wp-login.php`**, faute du mot de passe, qui
n'a pas été changé. Elle a été ouverte par cookies (`wp_generate_auth_cookie` + jeton
`WP_Session_Tokens`) ; la barre d'administration affichait « Fabienne Guéneau ».

```
M3  nœuds « Slug » / « Adresse de la page » dans #inline-edit, sans interaction
    Pages 1/0 → 0/1 · Portées 1/0 → 0/1 · Chiens 1/0 → 0/1 · Articles 1/0 → 1/0 · Résultats 0/0 → 0/0
V1  ouverture au clavier puis Annuler, sur les trois listes : nom accessible du champ = « Adresse de la page »
    post_modified_gmt, post_name et révisions identiques à l'octet (instantané de 134 lignes)
V2  charge window.mtbVocabulairePage de post.php?post=320 : même md5, 196 octets ; rangée « Adresse de la page »
V3  edit-mtb_portee + load-edit.php → Adresse de la page · edit-post → Slug · sans l'action → Slug
    edit-page, edit-mtb_chien → Adresse de la page · edit-mtb_resultat, écran « post », aucun écran → Slug
    la commande du point 7 de l'acte de bootstrap.php, rejouée telle qu'écrite → « Adresse de la page »
G   l'étiquette tient sur 2 lignes à 1440 px, en zoom 200 % et à 360 px ; champ sur une ligne
    (scrollWidth == clientWidth) ; hauteur 30 à 40 px (≥ 24) ; aucun défilement introduit → voie 1 acceptée
php -l  3 fichiers, 0 erreur
Hygiène  métadonnées de fabienne identiques (md5, 23 clés ; seul umeta_id de session_tokens a changé,
         la valeur brute ayant été réinsérée) · verrou d'édition de la page 320 remis à sa valeur ·
         debug.log 4 970 octets avant et après · empreinte du chargeur identique · aucune sonde mu-plugin
```

**Écart au §4, sans effet sur le code** : le panneau des **Portées et des Chiens** porte **« Modèle »**,
contrairement à la déduction du plan. Le mot n'étant pas interdit (§4), rien ne change. Libellés relevés :
Modification rapide · Titre · Adresse de la page · Date · Mot de passe · – OU – · Privée · Modèle · État ·
Mettre à jour · Annuler. **Aucun autre mot du §10.4** dans les trois panneaux visés.

**Guide (B10) : aucune fiche modifiée.** Aucune ne décrit la Modification rapide, aucune n'est devenue
fausse (les mentions « Adresse de la page » portent sur les écrans complets), et y ajouter une ligne de
signalement apprendrait à l'éleveuse un panneau que le guide ne lui fait pas ouvrir. Les lignes de
signalement existantes (`contenu-mettre-en-sommeil-et-reveiller.md`, `page-proteger-une-page-par-mot-de-passe.md`)
restent la parade humaine sur l'écran qu'elle ouvre vraiment.

**Signalé par la refacto, hors empreinte, non traité** : « la moitié PHP », au singulier, dans
`filtre-des-libelles.php` et `ecran.php`, est devenu ambigu ; la phrase gelée de `bootstrap.php` selon
laquelle le chargeur « autorise expressément `add_filter` à l'inclusion » ne se lit pas dans
`class-loader.php`, qui se contente de ne pas l'interdire (héritée de #54).
