# Contrat d'interface — Issue #36 — Avertir l'éleveuse quand une portée a une galerie mais pas de photo principale

**Gelé le 2026-08-31.** Périmètre : extension `mtb-core` uniquement, administration uniquement.
Aucune surface publique n'est ajoutée, modifiée ni retirée.

> **Les numéros de ligne cités ici sont des repères de lecture, jamais un contrat** — ceux de
> `ecran.php` comme ceux du cœur WordPress. Ils ont bougé **deux fois** pendant cette seule chaîne :
> une phrase ajoutée à un docbloc a décalé de **+1** les ancres ≥ 526, puis le re-gel de la phrase les
> a décalées de **+15**. À chaque fois elles ont été **revérifiées une à une contre le fichier** —
> `sed -n Np` — avant d'être réécrites, jamais recalculées. **Ce qui est gelé, ce sont les noms, la
> condition, la phrase et les interdits — pas les adresses.** Une chaîne future qui trouve une ancre
> fausse corrige l'ancre ; elle n'en conclut pas que le contrat a changé.
>
> **Le seul contrôle qui vaut sur la phrase n'est pas son adresse mais son contenu** : elle a été
> comparée au bloc « Phrase, gelée verbatim » **caractère pour caractère** — 531 caractères, même
> SHA-256, 7 × U+2019, 0 apostrophe ASCII, 0 espace insécable. C'est ce contrôle qu'une revue doit
> refaire, pas la lecture d'un numéro.

> **Ce contrat n'a pas réconcilié deux plans.** L'issue ne touche que l'extension : un seul plan,
> `leaddev-back-mtb`. Le contrat existe malgré tout, parce que c'est lui que lira une chaîne future —
> et parce que trois de ses décisions (la phrase, la borne, le refus de la promotion automatique) sont
> exactement le genre de choix qu'on repropose faute d'en trouver la trace.

---

## Ce que l'issue disait, et les deux endroits où elle avait tort

L'énoncé est **juste sur le mécanisme**, et c'est mesuré : `query/portee/hydratation.php:337` pose
`'photo' => photo( get_post_thumbnail_id( $id ), … )`, indépendamment de `'galerie'` (`:490`, qui lit
`_mtb_galerie`) ; `blocks/derniere-portee/render.php:125-127` ne lit que `$mtb_portee['photo']`.
Galerie remplie + Photo principale vide ⇒ encart sans photo. **Confirmé.**

Deux réserves, l'une et l'autre opposables :

1. **L'issue invoque D12 à tort.** D12 protège contre la **page cassée** ; ici la page est correcte.
   `docs/contracts/issue-12.md:893-895` avait déjà le bon mot : « dégradé proprement, mais pauvre ».
   **#36 sert D1 seule.** Ce n'est pas une querelle de mots : écrire « sert D12 » autoriserait une
   chaîne future à traiter l'absence de Photo principale comme un défaut à **empêcher**, et de là à la
   promotion automatique il n'y a qu'un pas (voir *Interdits*, point 1).
2. **La phrase prescrite par l'issue — « l'encart d'accueil » — est trop étroite.** Il y a **deux**
   consommateurs, mesurés ci-dessous. Une phrase d'écran qui n'en nommerait qu'un enseignerait **moins
   que ce que sa propre fiche dit déjà** (`docs/guide/contenu-repris-de-l-ancien-site.md:171-172`
   nomme les deux). Refusé.

Enfin, « rien à l'écran de saisie ne le signale » est vrai, mais « rien ne le signale » ne l'est pas :
`composant-encart-derniere-portee.md:86-97` et `contenu-repris-de-l-ancien-site.md:168-194` le disent
déjà. **Ce qui manquait n'était pas l'information, c'était sa présence au moment de la décision.**
Conséquence assumée : le bénéfice de cette issue est réel mais modeste, ce qui disqualifie d'avance
toute option coûteuse.

---

## Qui lit la Photo principale — le tableau qui borne la phrase

| Consommateur | Lit | Dépend de la Photo principale |
|---|---|---|
| `blocks/derniere-portee/render.php:125` | `$mtb_portee['photo']` | **oui** |
| `blocks/liste-portees/rendu.php:226` | `$portee['photo'] ?? null` → `wp_get_attachment_image()` | **oui**, une vignette par ligne |
| `themes/mtb/single-mtb_portee.php:65` | `$portee['galerie']` seulement | **non** — la page de la portée n'affiche **que** la galerie |
| balises sociales, données structurées | — | **aucune** : `og:image\|schema\|thumbnail` ne donne aucun résultat dans tout `themes/mtb/` |

Ce tableau n'est pas décoratif : la phrase gelée plus bas affirme la ligne 3 (« s'affichent bien sur la
page de la portée ») et énumère les lignes 1 et 2. **Il est la condition de vérité de la phrase.**

---

## Fonctions internes gelées

Portée : `MTB\Core\Fields\Portee`, dans `includes/fields/portee/ecran.php`. **Administration
uniquement.** Ni l'une ni l'autre n'est appelable depuis le thème ; aucune n'est préfixée `mtb_`.

```
photos_affichables( int $post_id ): array
    // array<int,int>, réindexé depuis 0, dans l'ordre stocké.
    // Critère unique : absint( valeur ) > 0 — le même que la boucle de rendu d'aujourd'hui,
    // pas un critère de plus.
    // N'atteste PAS que le fichier joint existe encore.
    // array() si $post_id <= 0, si la meta est absente ou n'est pas un tableau,
    // ou si aucune entrée ne survit au filtre.

mention_photo_principale_absente( int $post_id, array $photos ): string
    // '' , ou <div class="notice notice-warning inline"><p>…</p></div> déjà échappé.
    // $photos EST la liste que la boîte s'apprête à afficher, jamais la valeur stockée brute.
```

**Le point de contrat de la seconde fonction est son second paramètre.** La mention se pose sur ce que
l'éleveuse **voit**, jamais sur ce que la base **contient**. C'est ce qui interdit une mention au-dessus
d'une liste vide, et c'est pourquoi le filtrage sort de la boucle au lieu d'être compté deux fois.

**Contrepartie nommée** : une galerie ne contenant que des lignes « Photo introuvable »
(`ecran.php:624`) déclenche la mention. Elle reste juste — le remède est disponible quoi qu'il arrive —
et un critère plus sévère ferait mentir la mention devant une liste non vide.

---

## Condition, gelée

La mention est rendue **si et seulement si les trois** sont vraies :

1. `$post_id > 0`
2. `array() !== photos_affichables( $post_id )`
3. `0 === (int) get_post_thumbnail_id( $post_id )`

### La règle sous-jacente, opposable

> **On n'avertit que là où l'écran contient déjà le remède.**

| État de la portée | Mention | Motif |
|---|---|---|
| Galerie remplie, pas de Photo principale | **oui** | La photo à élire est sous ses yeux ; le remède est à trente centimètres, colonne de droite du même écran. |
| Ni galerie ni Photo principale | **non** | État normal d'une portée neuve. `portee-ajouter-une-portee.md:7-10` et `:239` lui promettent qu'elle peut publier à moitié rempli ; avertir sur un écran vierge **démentirait sa propre fiche**. Et le remède n'est pas dans l'écran : c'est « prendre une photo », un autre moment. |
| Photo principale sans galerie | **non** | Ce n'est pas un défaut. `single-mtb_portee.php:241-244` rend l'état vide proprement. Avertir ici **inventerait une obligation** qui n'existe dans aucun document du projet. |
| Galerie remplie **et** Photo principale | **non** | Rien à dire. |

**Ce que la condition ne regarde pas, délibérément** : le statut de publication (une portée en
brouillon porte la même mention — même remède, même effet), la date de naissance, la disponibilité, le
nombre de photos (une seule suffit à rendre le remède disponible), l'existence des fichiers joints, la
protection par mot de passe. **Aucun autre champ n'entre dans la condition.** Y ajouter un critère,
c'est ajouter un cas et non une règle : cela rouvre ce contrat.

**Objection traitée.** Une mention conditionnée à la galerie pourrait enseigner la règle inverse (« pas
de galerie ⇒ pas besoin de Photo principale »). La parade est **rédactionnelle**, pas mécanique : la
phrase énonce un **fait de provenance** — d'où l'encart et la liste tirent leur image — et non une
condition. Un fait de provenance reste vrai dans les quatre états ; il n'est affiché que là où il est
actionnable.

---

## Phrase, gelée verbatim

> Les photos de cette galerie s’affichent bien sur la page de la portée. Il manque seulement la
> « Photo principale » : c’est elle, et elle seule, qui apparaît dans la liste des portées et dans
> l’encart de la dernière portée. Pour en choisir une, allez dans la colonne de droite, encadré
> « Photo principale », cliquez sur « Choisir la photo principale », puis enregistrez la portée :
> tant qu’elle n’est pas enregistrée, la photo n’est pas en ligne et cette ligne reste affichée.
> Ou laissez ainsi : la liste et l’encart restent justes.

> **Re-gel du 2026-08-31, sur mesure — la troisième phrase a changé, et ce n'était pas un ajout de
> confort.** La version d'abord gelée s'arrêtait à « cliquez sur « Choisir la photo principale ». »
> **Ce geste était incomplet, donc faux par omission** : la mesure ci-dessous établit que choisir une
> photo n'écrit **rien** en base. La colonne de droite affiche pourtant la vignette immédiatement — le
> cœur y remplace le balisage côté navigateur. L'éleveuse voyait donc sa photo apparaître à droite et
> la ligne jaune rester à gauche, sans que rien ne lui dise pourquoi : **elle en aurait conclu que
> son geste avait échoué, et l'aurait recommencé.** La phrase dit désormais ce qu'il reste à faire et
> pourquoi. Le défaut a été relevé par le lead orchestrateur à partir de la mesure du cœur ; il ne
> l'aurait été par aucune relecture de l'écran.

Apostrophes typographiques `’`, guillemets français `« »` avec des espaces **ASCII** `0x20` — comme
partout dans le module, qui ne contient **aucune** espace insécable. `esc_html()` au rendu, par
discipline, la phrase étant un littéral sans interpolation.

> **Correction du 2026-08-31, après implémentation.** Ce bloc était d'abord tapé avec des apostrophes
> ASCII `'`, en contradiction avec sa propre clause normative ci-dessus et avec la pratique mesurée du
> module (`ecran.php` : 18 × U+2019, zéro apostrophe ASCII dans ses chaînes affichées). `dev-back-mtb`
> a suivi la clause plutôt que les octets, et l'a **signalé au lieu de le taire** — c'était le bon
> choix. Le bloc est désormais aligné sur le code : hors apostrophes, il lui était déjà identique à
> l'octet près. **Une revue peut comparer ce bloc à `ecran.php:542` caractère pour caractère.**

| Exigence | Où elle est tenue |
|---|---|
| Dit **d'abord ce qui fonctionne** | Phrase 1, avant toute mention d'un manque. Sans quoi elle laisserait croire que le travail de galerie déjà fait ne sert à rien. |
| Nomme les **deux** endroits, en **prose** | « la liste des portées », « l'encart de la dernière portée » — les mots exacts de `contenu-repris-de-l-ancien-site.md:171-172`, fiche déjà livrée. **Zéro vocabulaire neuf.** |
| Désigne le geste dans les mots livrés | « colonne de droite », « Photo principale », « Choisir la photo principale » — `content/portee/bootstrap.php:93-96` et `portee-ajouter-une-portee.md:179-182`, mot pour mot. Même parcours gauche→droite que `fields/chien/ecran.php:564`, déjà livré et déjà documenté. |
| Porte de sortie | « Ou laissez ainsi : la liste et l'encart restent justes. » — transposition littérale de `composant-encart-derniere-portee.md:188` au cas à deux endroits. |
| Registre | `notice-warning`, **jamais** `notice-error`. `avis.php:186-189` a déjà tranché : le préfixe d'erreur est réservé à l'erreur d'un champ, et **rien ici n'a échoué**. |
| Mots interdits | Ni « image mise en avant », ni « média », ni « alt » — `design-system/MASTER.md` **§10.2** (les libellés gelés) et **§10.4** (les mots interdits à l’écran) — cités par section et non par ligne, la chaîne #43 faisant bouger ce fichier pendant le lot. |

**Imprécision assumée et nommée** : sur une portée en brouillon, « s'affichent bien sur la page de la
portée » anticipe la publication. La phrase parle de l'endroit où vont les photos, pas de l'état de
mise en ligne ; l'alourdir d'une condition la rendrait moins lisible pour le cas majoritaire.

**Pourquoi la porte de sortie n'est pas une politesse.** **18 portées sur 31** porteront cette mention
en permanence, y compris des portées qui remontent aux années 1990. Sans cette dernière phrase, l'écran
devient un donneur de leçons sur des fiches anciennes, et la question « dois-je le faire sur les 18 ? »
reste ouverte à chaque ouverture. Avec elle, la réponse est écrite : **c'est son choix, et les deux
réponses sont correctes.** Aucun cas particulier n'est fait pour une « portée passée » — ce serait
décider à sa place qu'une vieille portée ne mérite pas de photo, et ce n'est pas une décision de code.

> **Correction du 2026-08-31, mesurée.** Ce paragraphe annonçait d'abord **27**, en confondant deux
> populations distinctes. `refacto-mtb` a relevé la contradiction entre ce chiffre et celui que
> `dev-back-mtb` avait mesuré, et **a refusé de trancher seul** — c'était le bon réflexe. Mesure faite
> dans la pile le 2026-08-31 : `portees=31 galerie_non_vide=18 avec_photo_principale=0 mention=18`.
>
> Les deux chiffres sont vrais et ne disent pas la même chose. **27** est le nombre de portées
> **reprises** de l'ancien site, et il reste juste partout où ce contrat l'emploie ailleurs — aucune
> d'elles ne peut avoir de Photo principale, la migration n'ayant aucun chemin d'écriture de
> `_thumbnail_id`. **18** est le nombre de portées qui portent effectivement la mention, la condition
> exigeant **en plus** une galerie non vide. Les 13 autres ont une galerie vide et se taisent.
>
> Ce chiffre vieillira dès qu'une galerie sera remplie ou qu'une Photo principale sera choisie : il
> vaut à la date de la mesure, comme état du dépôt, jamais comme invariant.

---

## Balisage, gelé

```html
<div class="notice notice-warning inline"><p>…</p></div>
```

Trois classes du **cœur**, **aucune classe `mtb-`**, **aucun fichier CSS**, **aucun JavaScript**.
Strictement le balisage de `ecran.php:237`.

Position : **premier élément** de la boîte « Galerie photos », **avant** le `<p class="description">`
existant de `ecran.php:564` — symétrie exacte de `ecran.php:236-238`, où la notice précède le champ
qu'elle concerne.

- **Pas de `is-dismissible`** : la mention décrit un **état persistant**, pas un événement. Le rejet du
  cœur est du JavaScript, et la mention reviendrait au rechargement suivant.
- **Pas de `role="alert"` ni d'`aria-live`** : rien ne change dynamiquement, la mention est présente au
  chargement.
- **Accessibilité sans toucher une feuille de style** : premier contenu de la boîte, donc rencontrée
  avant la ligne d'aide et avant les vignettes, dans l'ordre du DOM comme au lecteur d'écran. Contraste
  et apparence sont ceux de la feuille d'administration du cœur, déjà employés à `ecran.php:237`.

**Aucune feuille CSS n'accompagne cette mention, et c'est une décision, pas un oubli.** Le dossier
`includes/fields/portee/` ne contient aucun `.css` ; les deux avertissements existants de cet écran
(`avis.php:170`, `ecran.php:237`) s'habillent des seules classes du cœur. #36 reste dans ce registre.

---

## États spéciaux

| État | Émis par le serveur | Rendu à l'écran |
|---|---|---|
| `galerie_remplie_sans_photo_principale` | condition 1∧2∧3 vraie | la mention gelée ci-dessus |
| `galerie_vide` | `photos_affichables()` rend `array()` | rien |
| `photo_principale_presente` | `get_post_thumbnail_id()` > 0 | rien |
| `ecran_sans_contenu` (`$post_id <= 0`) | le rappel n'a pas reçu de `WP_Post` | rien |

**Sur `post-new.php`, précision mesurée** : le cœur y a déjà créé un brouillon automatique, donc
`identifiant_du_contenu()` y renvoie un identifiant **> 0**. La branche `ecran_sans_contenu` ne couvre
donc **pas** « écran d'ajout » — c'est un invariant bon marché, pas un cas produit. Le comportement
réel sur `post-new.php` vient de la condition 2 : `_mtb_galerie` y est vide, et des photos ajoutées par
le script vivent dans le DOM et non en base. **Aucune mention avant le premier enregistrement**, ce qui
est cohérent avec ce que la mention prétend décrire.

---

## Chaînes fournies par le serveur

La phrase gelée ci-dessus, et elle seule. Aucune chaîne composée, aucune interpolation, aucun
identifiant, aucun compteur, aucune date. **Le thème n'en voit rien** : la mention ne quitte jamais
l'administration.

---

## Limite connue, opposable — mesurée, non supposée

**La mention décrit l'état enregistré, lu en base au moment du rendu de la boîte.** Elle n'observe pas
le DOM et ne s'abonne à rien.

### La mesure qui corrige l'hypothèse des deux plans amont

Faite dans la pile, cœur **6.9**, le 2026-08-31. Les numéros de ligne du cœur sont des **repères de
lecture, jamais un contrat** — `docker/wordpress/Dockerfile:5` tire une étiquette flottante
(garde-fou posé par `blocks/lien-de-recours/rendu.php:132-136`).

`brainstorm-mtb` et `leaddev-back-mtb` ont tous deux raisonné sur une branche « le cœur écrit
immédiatement en AJAX, donc la mention reste affichée à tort après le choix d'une photo ». **Cette
branche est fausse pour le geste que le guide enseigne, et le sens réel est l'inverse.**

1. **Choisir une photo principale n'écrit rien en base.**
   `wp-includes/js/media-editor.js:619-635` — `wp.media.featuredImage.set()` appelle l'action
   **`get-post-thumbnail-html`**, et `wp_ajax_get_post_thumbnail_html()`
   (`wp-admin/includes/ajax-actions.php:2774`) ne fait que **rendre du HTML** : aucun
   `set_post_thumbnail()`, aucune écriture.
2. **La persistance passe par le champ caché** `<input type="hidden" id="_thumbnail_id"
   name="_thumbnail_id">` (`wp-admin/includes/post.php:1694`), soumis avec le formulaire, puis écrit
   par `wp_insert_post()` (`wp-includes/post.php:5043-5055`) — **avant** que `save_post_{type}`
   (`:5183`) et `save_post` (`:5194`) ne se déclenchent.
3. **L'action `set-post-thumbnail` écrit bien** (`ajax-actions.php:2761`), mais ses seuls appelants du
   cœur sont `WPRemoveThumbnail` (`wp-admin/js/post.js:137-157`) et `WPSetAsThumbnail`
   (`set-post-thumbnail.min.js`, le lien du volet de détail d'un fichier) — **pas** le parcours
   « Choisir la photo principale » → « Utiliser comme photo principale » que le guide enseigne.

**Conséquence contre-intuitive, à ne pas reperdre : poser une photo principale est différé à
l'enregistrement ; la retirer est immédiat.**

### Ce que cela change, concrètement

| Geste | Base | Mention à l'écran |
|---|---|---|
| Elle **choisit** une photo principale, sans enregistrer | inchangée | reste — et **elle a raison** : le site n'affiche toujours rien |
| Elle choisit puis **Mettre à jour** | écrite | disparue au rechargement |
| Elle **retire** la photo principale | écrite **immédiatement** | n'apparaît qu'au prochain affichage |

Le seul décalage réel est donc **au retrait**, pas à la pose — et c'est le sens le moins dommageable :
une mention **en retard** fait rouvrir un écran, une mention **en avance** ferait croire un travail
fait.

### Le parcours complet du bouton, mesuré maillon par maillon

Ce qui suit est la chaîne exacte du geste que la fiche d'aide enseigne, lue dans le cœur 6.9 :

1. Clic sur **Choisir la photo principale** → `media-editor.js:706-710` : `#postimagediv` délègue le
   clic sur `#set-post-thumbnail` vers `wp.media.featuredImage.frame().open()`.
2. Clic sur **Utiliser comme photo principale** dans la fenêtre → `media-editor.js:690-697`, le rappel
   `select` de l'état `featured-image`, qui appelle `wp.media.featuredImage.set( id )`.
3. `wp.media.featuredImage.set()` → `media-editor.js:619-635` : il appelle
   **`get-post-thumbnail-html`**, puis fait `$( '.inside', '#postimagediv' ).html( html )`.
4. `wp_ajax_get_post_thumbnail_html()` → `ajax-actions.php:2774` : il **rend du balisage et rien
   d'autre**. Aucun `set_post_thumbnail()`, **aucune écriture**.

**Il n'existe aucun chemin d'écriture à la pose.** L'unique appelant de l'action écrivante
`set-post-thumbnail` dans le cœur administratif est `WPRemoveThumbnail` (`post.js:137-157`) — le
**retrait**. `WPSetAsThumbnail` (`set-post-thumbnail.min.js`) sert le lien du volet de détail d'un
fichier, **pas** ce parcours.

**Conséquence exacte, et c'est le fait qui a fait changer la phrase** : à l'étape 3, la colonne de
droite **affiche la vignette immédiatement**, côté navigateur, alors que la base **n'a pas bougé**.
L'écran montre donc simultanément une photo à droite et, à gauche, une mention qui dit qu'il n'y en a
pas. **Les deux sont vraies** — l'une parle de ce qui est en cours de saisie, l'autre de ce qui est
enregistré — mais rien ne le disait à l'éleveuse. C'est ce trou que la troisième phrase comble.

### Lecture officielle de la tâche 3 de l'issue — requalifiée

La tâche 3 dit « vérifier que l'avertissement disparaît **dès qu'**une photo principale est choisie ».

> **Telle qu'écrite, elle n'est pas réalisable, et elle ne devrait pas l'être.** Elle se lit :
> **« la mention disparaît au premier affichage de l'écran qui suit l'enregistrement »**, et
> **la mention dit elle-même à l'éleveuse que c'est ce qui va se passer.**

Ce n'est pas un affaiblissement de l'exigence, c'est sa correction. **Il n'existe aucun instant où la
photo serait « choisie » au sens du site sans enregistrement** : avant celui-ci, le site continue de
n'afficher aucune photo, sur la page d'accueil comme dans la liste des portées. Une mention qui
s'effacerait au clic annoncerait donc un résultat qui n'existe pas.

**Ce que l'issue visait réellement est tenu** : l'éleveuse n'est pas laissée devant un écran qui la
contredit sans explication. Elle l'est par la **phrase**, pas par un effacement.

**Écrit explicitement, parce qu'une revue qui lirait « dès que » sans lire ceci déclarerait l'issue non
tenue.** Toute recette doit **enregistrer, puis recharger, avant de conclure**.

### Le refus du JavaScript, pour qu'il ne soit pas reproposé

1. **Cet écran est délibérément conçu pour fonctionner sans script** : `ecran.php:339` (trois rangées
   de chiots de secours « pour que l'écran reste utilisable quand le JavaScript ne s'exécute pas »),
   `ecran.php:369` et `:578` (outils rendus masqués, dévoilés par le script). Un avertissement dont
   l'effacement dépendrait du script romprait cette ligne pour la première fois.
2. **Il s'accrocherait aux identifiants internes du cœur** — `#postimagediv`, `#_thumbnail_id` — sur une
   étiquette d'image **flottante**. Le jour où le cœur renomme un conteneur, l'avertissement cesserait
   de disparaître **sans aucune erreur**, et personne ne le verrait.
3. **Deux sources de vérité sur le même état.** Ce qui gouverne l'affichage du site, c'est
   `_thumbnail_id` **en base**. Une mention pilotée par le DOM parlerait d'un état non enregistré —
   et, la mesure ci-dessus le montre, elle **disparaîtrait alors que le site continue de n'afficher
   aucune photo**. C'est précisément le mensonge que la limite assumée évite.
4. **Géographie** : la boîte « Photo principale » est en haut à droite, « Galerie photos » en bas à
   gauche. Un message qui s'efface hors du champ de vision n'enseigne rien.
5. **Coût nul** : zéro octet ajouté, zéro fichier en file, zéro dépendance.

---

## Interdits

1. **Ne jamais promouvoir automatiquement une photo de la galerie en Photo principale.**
   `docs/guide/contenu-repris-de-l-ancien-site.md:177-181`, fiche **déjà livrée à l'éleveuse**, écrit
   que le projet a refusé d'élire une photo à sa place — « **En élire une à votre place aurait été un
   choix arbitraire.** » — et `:137-139` **mesure** que la première image d'une fiche reprise était
   souvent une bannière de rubrique identique sur seize fiches. Trois raisons de plus : l'ordre de
   `_mtb_galerie` est un ordre d'affichage (`ecran.php:649-650` lui donne des boutons Monter/Descendre)
   et ne porte aucune intention de portrait ; la commande « Retirer la photo principale »
   (`content/portee/bootstrap.php:95`, livrée et documentée) serait annulée au prochain enregistrement,
   ce qui est pire qu'une commande absente ; et toute la doctrine de `sauvegarde.php:268-271` est que
   ce module **ne décide jamais à sa place sans le dire**. Promouvoir éteindrait le symptôme en
   masquant la cause — l'inverse de D1.
   *Variante également écartée* : un bouton « Utiliser la photo 1 comme photo principale » dans la
   boîte Galerie. Il duplique une commande que le cœur porte déjà à trois mètres à droite et crée un
   **deuxième vocabulaire pour un geste unique**. À rouvrir seulement si l'usage prouve que le renvoi
   en prose ne suffit pas.
2. **Ne jamais transformer l'absence de Photo principale en défaut bloquant** : pas de refus
   d'enregistrement, pas de retour en brouillon, pas de `notice-error`. **#36 ne sert pas D12.**
3. **Ne jamais livrer de CSS ni de JavaScript pour cette mention.** Ni fichier dans
   `includes/fields/portee/`, ni règle dans `themes/mtb/assets/css/**`.
4. **Ne jamais nommer les composants gelés dans cette phrase.** « Liste de portées » et « Encart
   dernière portée » (`issue-12.md:908`) sont les titres de l'**inséreur d'une page**, pas le
   vocabulaire d'une fiche de portée.
5. **Ne jamais poser cette mention par transient ni à l'enregistrement.** Les 27 portées reprises ne
   seront pas réenregistrées : elles ne la verraient jamais. C'est la raison d'être de l'option
   retenue.
6. **Mots interdits à l'écran** : « image mise en avant », « média » (dire « photo »), « alt ».
7. **Le thème n'a rien à faire de cette issue.** Aucune fonction de lecture n'est ajoutée, modifiée ni
   retirée ; `mtb_get_derniere_portee()` et la lecture de `liste-portees` sont **intactes**, leur forme
   de retour **inchangée**. **Aucun octet servi au visiteur ne change.**

---

## Obligations imposées aux autres issues

1. **La phrase 1 affirme un fait vérifiable sur le front** : la galerie d'une portée est bien rendue
   par `themes/mtb/single-mtb_portee.php:65`. **Toute issue qui cesserait de rendre la galerie sur la
   page d'une portée rend cette phrase fausse** et doit rouvrir la section *Phrase* de ce contrat.
2. **La phrase 2 nomme deux consommateurs de la Photo principale.** **Un troisième — balise sociale,
   donnée structurée, gabarit — oblige à rouvrir la phrase.** Mesuré aujourd'hui : aucune occurrence
   de `og:image|schema|thumbnail` dans tout `themes/mtb/`.
3. **Toute issue qui remplirait `_thumbnail_id` à la migration** (`migration/portees-chiens/portees.php`,
   aujourd'hui **sans aucun chemin d'écriture**, contrairement à `chiens.php:163-177`) **change la
   population qui voit la mention** et doit mettre à jour `docs/guide/contenu-repris-de-l-ancien-site.md`
   §4 dans le même mouvement.
4. **#32 (`includes/admin/listes/**`) n'est pas touché.** Si #32 ajoutait une colonne « photo » à la
   liste des portées, elle resterait cohérente avec cette mention **sans la modifier** — la mention
   nomme déjà « la liste des portées ». Un repère dans « Toutes les portées » a été **écarté** de #36 :
   il vit hors empreinte et entre en collision avec le périmètre non tranché de #32.

---

## Arbitrages

| # | Désaccord ou choix ouvert | Décision | Raison |
|---|---|---|---|
| 1 | Avis à l'enregistrement (famille `avis.php`) **ou** mention en ligne (famille `ecran.php:237`) ? | **Mention en ligne.** | **0 portée sur 31** a une Photo principale (`issue-37.md:179`) et la migration n'a **aucun** chemin d'écriture de `_thumbnail_id` : les 27 portées reprises ne peuvent structurellement pas en avoir, et l'éleveuse ne les **enregistrera** pas. Un avis d'enregistrement ne parlerait **jamais** à la population exacte que l'issue vise. La mention en ligne est aussi la seule qui ne dépende d'aucune hypothèse sur le cœur. |
| 2 | Les deux, avec exclusion mutuelle ? | **Non.** | Sans garde, les deux s'affichent ensemble — deux composants qui font presque la même chose. Avec garde, la garde devient le sujet : comparer l'avant et l'après dans `enregistrer_champs()`, trois lignes de mécanique **pour éviter une redite**, pas pour livrer une capacité. |
| 3 | Promotion automatique de la première photo | **Écartée.** | Voir *Interdits* 1. Elle rendrait fausse une fiche déjà remise à l'éleveuse. |
| 4 | Phrase en **prose** ou par **titres de composants gelés** ? | **Prose.** | Les titres gelés sont ce qu'elle voit dans l'inséreur d'une page, pas sur une fiche de portée. La prose est déjà celle de la fiche qu'elle a lue. **Zéro vocabulaire neuf.** |
| 5 | La phrase nomme-t-elle un endroit ou deux ? | **Deux.** | Mesuré : deux consommateurs. Une phrase à un seul endroit enseignerait moins que la fiche déjà livrée, et instaurerait une divergence entre deux textes qu'elle lit tous les deux. |
| 6 | Où vivent les deux nouvelles fonctions — `avis.php` ou `ecran.php` ? | **`ecran.php`.** | Le partage réel du module n'est pas *phrase / rendu* mais **avis d'enregistrement (composé, différé, transporté par transient) / texte d'écran (fixe, immédiat, rendu sur place)**. `ecran.php` porte déjà onze textes, dont `$phrase_perdue` (`:189-191`) — jumeau exact de notre phrase, rendu en `notice notice-warning inline` à `:237`. Poser la phrase dans `avis.php` rendrait faux son propre docblock (« Avis affichés **après l'enregistrement** »). |
| 7 | Faut-il avertir aussi dans les cas symétriques ? | **Non.** | Règle : *on n'avertit que là où l'écran contient déjà le remède.* Voir le tableau de la *Condition*. |
| 8 | JavaScript pour effacer la mention à la volée ? | **Non.** | Cinq raisons, section *Limite connue*. La mesure du cœur retourne l'argument : un effacement JS **mentirait**. |
| 9 | Le mécanisme AJAX du cœur devait-il être mesuré ? | **Mesuré, par le lead.** | L'option retenue n'en dépendait pas, mais la mesure a **corrigé l'hypothèse des deux plans amont**. Elle est consignée plus haut avec ses citations. |
| 10 | `docs/guide/contenu-repris-de-l-ancien-site.md` §4 est hors empreinte, et c'est la fiche qui décrit exactement cette situation aux 27 portées reprises. | **Remontée au lead orchestrateur**, non tranchée en chaîne. | C'est une extension d'empreinte, pas une décision de chaîne — même si le risque de collision est **nul** (#40 tient `assets/css/**` et `docker/**` ; #43 tient `MASTER.md` et `includes/blocks/lien-de-recours/**` ; **aucune ne touche `docs/guide/`**). Voir le rapport final pour l'issue de cette question. |
| 11 | **La mention trompe l'éleveuse** : elle choisit sa photo, la vignette apparaît à droite, la ligne jaune reste, rien ne dit pourquoi. Trois remèdes possibles — la mention s'efface au clic (JavaScript) ; elle reste mais **dit** qu'elle partira à l'enregistrement ; on requalifie la tâche 3 en l'expliquant. | **Le deuxième, et il rend le troisième vrai** : la phrase porte désormais le fait. | **Le JavaScript n'est pas seulement plus cher ici, il est plus faux.** Mesuré : au clic, la base n'a pas bougé et le site continue de n'afficher aucune photo, ni sur l'accueil ni dans la liste. Une mention qui s'effacerait là **annoncerait un résultat qui n'existe pas** : si l'éleveuse quitte l'écran sans enregistrer, elle est partie convaincue que c'était fait, et rien ne la détrompera. **Les deux erreurs ne se valent pas** — la mention qui reste fait recommencer un geste (agaçant, rattrapable) ; la mention qui s'efface trop tôt fait partir (silencieux, non rattrapable). Le remède retenu supprime l'agacement **sans** créer le silence. Les cinq raisons du refus du JavaScript tiennent par ailleurs, et la mesure en **renforce** la troisième. |
| 12 | La phrase doit-elle nommer le bouton d'enregistrement, « **Mettre à jour** » ? | **Non** — « puis enregistrez la portée ». | Le libellé du cœur **change avec le statut** : « Mettre à jour » sur une portée en ligne, « Publier » ou « Enregistrer le brouillon » sur un brouillon. Or la mention s'affiche sur les deux — le contrat pose explicitement que le statut n'entre pas dans la condition. Nommer un seul de ces boutons enseignerait **un libellé faux à une partie des écrans**. La fiche d'aide, elle, peut nommer « Mettre à jour », parce qu'elle décrit un parcours situé. **Ne « corrigez » pas cette phrase en y remettant un nom de bouton** : c'est une omission délibérée, et elle est mesurable en ouvrant une portée en brouillon. |

---

## Fiche d'aide (D3)

`docs/guide/portee-ajouter-une-portee.md`, deux endroits :

1. **Section « Les photos »** (`:175-184`), après le paragraphe *Photo principale* (`:179-182`) : ce que
   la mention dit et où elle apparaît, avec la précision que **ce n'est pas une erreur**.
2. **Section « Trois choses normales, qui peuvent surprendre »** (`:268`) : une **troisième puce**. Le
   titre annonce trois choses et n'en énumère que deux (`:270-275`) — incohérence antérieure à cette
   issue, que la puce ajoutée **résout gratuitement**. La puce doit porter la **même lecture de la
   tâche 3** que ce contrat (« au prochain affichage de l'écran »), sans quoi la fiche promettrait un
   effacement instantané que le code ne fait pas.

---

## Dette constatée, non réparée

**T77**, hors périmètre, **rapportée sans changement** : `fields/portee/ecran.js:264` ouvre la fenêtre
de choix de photos en `multiple: 'add'` là où `blocks/galerie-photos/editeur.js:190` l'ouvre en
`multiple: true`, et deux de ces fenêtres portent un titre identique. #36 ne passe pas par `ecran.js` et
n'y prescrit **aucun** changement.

### Dette neuve A — la pose d'une photo principale n'écrit rien, son retrait écrit tout de suite

**Fait, mesuré dans le cœur 6.9, à verser à `ETAT.md`.** Dans l'éditeur classique, le parcours
« Choisir la photo principale » → « Utiliser comme photo principale » ne touche **pas** la base :
`media-editor.js:706-710` ouvre la fenêtre, `:690-697` appelle `wp.media.featuredImage.set()`, et
`:619-635` appelle l'action **`get-post-thumbnail-html`**, dont le gestionnaire
(`ajax-actions.php:2774`) **rend du balisage et rien d'autre**. La persistance passe par le champ caché
`_thumbnail_id` (`post.php:1694`), écrit par `wp_insert_post()` (`post.php:5043-5055`) **avant** que
`save_post_{type}` (`:5183`) et `save_post` (`:5194`) ne se déclenchent. En regard, l'action
**écrivante** `set-post-thumbnail` (`ajax-actions.php:2761`) n'a pour appelants du cœur que
`WPRemoveThumbnail` (`post.js:137-157`) — le **retrait** — et `WPSetAsThumbnail` du volet de détail.

**Pourquoi c'est une dette et pas une note** : l'asymétrie est **invisible à l'écran**. La colonne de
droite affiche la vignette dans les deux cas, aussi vite. Toute chaîne future qui raisonnera sur cet
écran — un contrôle à l'enregistrement, un état dérivé de `_thumbnail_id`, une recette — se trompera si
elle suppose la symétrie. #36 s'en sort par le texte ; une issue qui aurait besoin du **fait** devra le
remesurer, ou lire ceci.

*Réserve, du même registre que `blocks/lien-de-recours/rendu.php:132-136`* : `docker/wordpress/Dockerfile:5`
tire une **étiquette flottante**. Les numéros ci-dessus sont des **repères de lecture**. Le livrable
est le **mécanisme**, pas l'adresse.

### Dette neuve B — la reprise n'écrit aucune photo principale de portée

**Fait mesuré, sans jugement, et hors empreinte de #36.**

- `migration/portees-chiens/portees.php:70-90` **n'a aucun chemin d'écriture de `_thumbnail_id`**.
- `migration/portees-chiens/chiens.php:163-177` **en a un** (`portrait_possible()` → `$metas['_thumbnail_id']`).
- Compte dans la pile au 2026-08-31 : **0 portée sur 31** possède une photo principale.
- Ce que l'éleveuse voit : l'encart d'accueil et la liste des portées sont **sans image pour toutes les
  portées reprises**, et le resteront jusqu'à ce qu'elle en désigne une à la main, portée par portée.

**#36 rend ce défaut visible ; elle ne le répare pas, et ce n'est pas son objet.**

**Ce qui n'est pas établi, et que personne ne doit supposer** : rien n'a été vérifié quant à savoir si
l'ancien site désignait une photo principale pour une portée. `docs/guide/contenu-repris-de-l-ancien-site.md:177-181`
dit au contraire que « votre ancien site ne désignait aucune photo comme *la* photo d'une portée ».
Trancher si c'est une **perte** au sens de la contrainte 4 ou une **notion que l'ancien site n'avait
pas** est une question à remonter à l'utilisateur — **jamais une invention de chaîne.**
