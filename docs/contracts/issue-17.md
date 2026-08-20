# Contrat d'interface — Issue #17 — Gabarits des pages libres (accueil, BHPL, travail, placement)

Gelé le 2026-08-20. Opposable à `dev-front-mtb`, `contenu-mtb`, `refacto-mtb`,
`dev-integration-mtb`, `doc-client-mtb`.

Une seule moitié de plan a été produite (`leaddev-front-mtb`) : l'issue ne touche que le thème et ne
modifie aucune ligne de `mtb-core`. Les arbitrages du §7 portent donc sur les présupposés du corps de
l'issue et sur les questions que le plan ne pouvait pas trancher seul.

---

## 1. Le renversement fondateur

**Le corps de l'issue demandait huit gabarits `templates/page-*.html` et un `front-page.html`. Aucun
n'est livré.** Trois faits, tous vérifiés dans le dépôt :

1. **Décision 44** — Fabienne a le rôle Éditeur et `site-editor.php` lui répond **403**. Tout ce qui
   est écrit dans un gabarit `.html` est, pour elle, aussi verrouillé qu'un fichier PHP. Un gabarit
   qui compose une page enfreint frontalement la contrainte 1.
2. **`templates/singular.html` rend déjà correctement les huit pages** — `header` +
   `wp:post-title {"level":1}` + `wp:post-content {"className":"mtb-canal alignfull"}` + `footer`.
   La hiérarchie `page-{slug}` → `page-{id}` → `page` → `singular` n'a besoin d'aucun fichier de plus.
3. **`front-page.html` capture aussi l'accueil-blog** quand `show_on_front = posts` : `core/post-content`
   y rend une chaîne vide, donc **accueil blanc**. Le livrer serait une régression franche.

**Règle de partage gravée** :

> Le gabarit porte ce qui est vrai de **toutes** les pages (en-tête, `h1`, canal, pied de page).
> Le `post_content` porte ce qui est vrai de **cette** page.
> La composition d'une page libre vit dans le `post_content`, livrée sous forme de **motif**.

Corollaire, également gravé :

> **Un motif porte de la structure et des libellés de section, jamais la prose du site source.**
> La prose verbatim est saisie dans la base et archivée dans `docs/migration/source/pages/`.
> Raison : décision 1 — le contenu doit survivre au thème. De la prose dans un fichier de motif crée
> un deuxième exemplaire dès la première correction de Fabienne.

---

## 2. Ce que #17 livre

| Livrable | Chemin |
|---|---|
| Huit motifs de page | `wp-content/themes/mtb/patterns/{accueil,bhpl,bhpl-en-france,litterature,travail,placement,mentions-legales,politique-de-confidentialite}.php` |
| Dette T3 | `wp-content/themes/mtb/templates/index.html` (une ligne) |
| Sources verbatim archivées | `docs/migration/source/pages/**` |
| Contenu réel saisi | base de développement uniquement — **aucun fichier**, `provision.sh` interdit |
| Fiche d'aide | `docs/guide/` (nom de fichier neuf) |
| CSS | **aucun. Zéro octet ajouté.** |

**Aucun gabarit n'est créé.** `index.html` est le seul fichier de `templates/` modifié.

---

## 3. Blocs consommés — hypothèses exactes

Le thème **consomme** l'extension sans jamais l'appeler : un motif ne référence qu'un **commentaire de
bloc**. Décision 41 tenue à la lettre : après #17,
`grep -r "mtb_get_\|\$wpdb\|WP_Query\|get_posts" wp-content/themes/mtb/` doit rendre **zéro** sur de la
donnée d'élevage.

| Bloc | Attributs écrits par les motifs | Valeurs |
|---|---|---|
| `mtb/bandeau-ouverture` | aucun | — |
| `mtb/bandeau-alerte` | aucun | — |
| `mtb/derniere-portee` | aucun | — |
| `mtb/fiche-information` | `titre`, `niveau_titre` | `"Santé"`, `"Génétique des couleurs"` ; `niveau_titre: "h2"` |
| `mtb/galerie-photos` | aucun | — |
| `mtb/liste-portees` | aucun | `nombre:""`, `annee:""` = toutes |
| `mtb/grille-chiens` | `statut` | `"reproducteur"` |
| `mtb/encart-appel` | aucun | `page_id: 0` → repli sur `mtb_get_page_contact()` |
| `mtb/coordonnees-plan` | **aucun, impérativement** | les défauts viennent de `mtb_get_coordonnees_elevage()` via `block_type_metadata` |

**Interdits d'écriture dans un attribut de motif**, sans exception : l'adresse, le téléphone, le
courriel (un deuxième littéral détruirait la source unique de #38) · un identifiant de page · une URL ·
un libellé de discipline (on écrit la **clé**) · une couleur, une police, un espacement, un alignement.

**Aucun motif n'écrit d'URL.** Les huit ne portent ni `core/button`, ni `core/navigation-link`, ni
`<a href>`. Le seul lien produit est celui du bouton de `mtb/encart-appel`, composé par le serveur.

---

## 4. Le `h1` — mécanisme, et la règle qui en découle

`mtb-core/includes/blocks/bandeau-ouverture/titre-principal.php` efface le `core/post-title` du gabarit
quand `mtb/bandeau-ouverture` est le **premier bloc nommé de premier niveau** du `post_content`, sur une
page non protégée, avec un titre effectif non vide.

> **Si un motif porte `mtb/bandeau-ouverture`, ce bloc est le premier bloc du motif, sans exception,
> et rien ne le précède — pas même un `core/group` englobant** (la garde ne lit que le premier niveau).

Un motif sans bandeau est valide : le `h1` vient de `wp:post-title` de `singular.html`. C'est le cas
retenu pour **Mentions légales** et **Politique de confidentialité**, qui servent de preuve à l'écran
que les deux régimes rendent **exactement un** `h1`.

---

## 5. Dépendance résolue — le tableau de résultats de la chaîne #15

**#15 est commitée. L'hypothèse est levée : le bloc réel a été relu dans le dépôt le 2026-08-20**
(`wp-content/plugins/mtb-core/includes/blocks/tableau-resultats/`). Vérification faite fichier par
fichier, et non déduite :

| Élément | Supposé au gel | Réel, vérifié | Verdict |
|---|---|---|---|
| Nom du bloc | `mtb/tableau-resultats` | `block.json:4` — identique | ✅ |
| Attribut de discipline | `discipline`, `string` | `block.json:11-14` — identique | ✅ |
| Défaut | `""` | `block.json:13` — `"default": ""` | ✅ |
| Le bloc émet son propre `h2` | exigé (§5.1) | `balisage.php:143` — `<section data-discipline="…"><h2>…</h2>…</section>` | ✅ |
| Valeurs | les neuf clés de `mtb_resultat_disciplines()` | `donnees.php` ne recopie **jamais** la liste, il l'emprunte à sa source unique | ✅ |

**Un attribut non prévu au gel existe** : `source`, énum `discipline` | `chien-courant`, défaut
`discipline` (`block.json:15-19`). Il sert le palmarès d'une fiche chien. **Aucun motif de #17 ne
l'écrit** — le défaut est le bon pour une page libre.

### 5.0 Ce que `discipline: ""` rend réellement — et pourquoi cela renverse A3

Lu dans `donnees.php`, pas supposé :

- `choix_du_reglage()` place en **premier choix** `['value' => '', 'label' => 'Toutes les disciplines']`.
- `groupes('')` appelle `mtb_get_resultats_travail_par_discipline(['disciplines' => array()])`, c'est-à-dire
  **toutes**, et « ne trie ni ne regroupe : il passe l'ordre voulu et imprime ce qu'il reçoit,
  **groupes orphelins compris et dans le même ordre** ».
- La mémorisation est une **statique de fonction** : neuf instances ne coûtaient qu'une lecture. Le
  problème des neuf instances n'a donc jamais été un problème de performance.
- Le commentaire de `discipline_demandee()` est explicite : la valeur est assainie mais **jamais
  ramenée à une liste blanche**, « une instance réglée sur une discipline devenue orpheline doit
  continuer d'afficher ce groupe-là ».

Le défaut couvre donc strictement plus que l'énumération des neuf clés, et c'est délibéré côté #15 :
« il est complet par construction : il affiche un tableau par discipline, y compris une discipline
sortie de la liste, que le réglage ne pourrait plus proposer. C'est la seule façon d'atteindre un
résultat dont la discipline a été renommée. »

### 5.1 Point de contrat dur — le bloc émet son propre `h2`

> **`mtb/tableau-resultats` DOIT émettre lui-même son `h2` de discipline.** Le motif n'en pose aucun.

`MASTER.md` §7.6 : « une discipline sans aucune ligne n'affiche **ni titre ni tableau** ». Un
`wp:heading` écrit dans `patterns/travail.php` resterait à l'écran quand le bloc ne rend rien —
jusqu'à neuf titres orphelins et un outline cassé. **Il n'existe aucune écriture de motif qui rende ce
cas correct** : si #15 a figé autrement, la correction appartient à #15.

Libellé imprimé = celui de `mtb_resultat_disciplines()` (« RING », « IGP / RCI »…), **fourni par le
serveur, jamais composé par le thème**.

### 5.2 Écriture gelée de `patterns/travail.php`

Une seule ligne de composant, sans attribut, sans exception :

```
<!-- wp:mtb/tableau-resultats /-->
```

**Corollaire opposable** : la page Travail **ne compose aucun `h2` de discipline**, ni dans le motif,
ni dans le `post_content` saisi en base. Le composant les rend lui-même (`balisage.php:143`). Un `h2`
écrit à côté produirait des titres en double et un plan de page faux. Ce corollaire lie
`contenu-mtb` autant que `dev-front-mtb` : la prose verbatim de `travail.md` qui porte des intertitres
de discipline **s'arrête là où le composant prend le relais**.

---

## 5.3 Q17 tranchée — le contenu réel est recopié

**Tranchée par l'utilisateur le 2026-08-20 : on recopie le contenu réel de mtbrabant.com, on
n'invente rien.** Le contrat gelé prévoyait des pages en charpente ; il est amendé ici.

### Inventaire des sources, vérifié par `curl` le 2026-08-20 (HTTP 200 sur les 6)

| Page cible | URL source | État |
|---|---|---|
| Accueil | `https://www.mtbrabant.com/` | **déjà capturée** par #16 → `source/accueil.md` |
| Travail | `https://www.mtbrabant.com/travail/` | **déjà capturée** par #16 → `source/travail.md` |
| BHPL | `https://www.mtbrabant.com/bhpl/` | à capturer (51 063 o) |
| BHPL en France | `https://www.mtbrabant.com/bhpl/bhpl-en-france/` | ~~à capturer (29 049 o)~~ → **302, protégée par mot de passe, non capturable** (voir ci-dessous) |
| Littérature | `https://www.mtbrabant.com/bhpl/litt%C3%A9rature/` | à capturer (35 541 o) |
| Placement | `https://www.mtbrabant.com/placement/` | à capturer (31 205 o) |
| Mentions légales | `https://www.mtbrabant.com/mentions-l%C3%A9gales/` | à capturer (33 151 o) |
| Politique de confidentialité | **n'existe pas sur le site source** | `BRIEF.md` §5.4 : « à créer ». Charpente seule, cf. A7 |

**Constat remonté, hors périmètre de #17** : `https://www.mtbrabant.com/bhpl/bhpl-en-france/` répond
**200 mais n'est pas au `sitemap.xml`**. Le sitemap porte 8 URL hors portées et chiens ; 8 + 27 + 17
= **52**, le compte exact de `BRIEF.md` §7. « BHPL en France » est donc une **53ᵉ URL**, atteignable
depuis le menu du site et absente du décompte de reprise. À verser à l'epic #19-#21 (D5).

### Emplacement des captures

`docs/migration/source/pages/`, **fichiers neufs uniquement**. `source/README.md` et les fichiers de
#16 ne sont **pas modifiés** : ils décrivent honnêtement leur propre passe. Les captures de #17
portent leur propre `pages/README.md`.

### Règles de recopie, opposables à `contenu-mtb`

- **Verbatim.** On recopie, on ne reformule pas, on ne résume pas, on ne modernise pas. Même méthode
  de capture qu'au `source/README.md` de #16 (taille + SHA-256 du HTML reçu, trois transformations,
  compte des U+00A0), pour que les deux passes se relisent de la même façon.
- **Deux exceptions, où l'on ne recopie pas :**
  1. **Tout montant de tarif de chiot** — A6, Q6 tranchée. Le montant est **archivé dans la capture**
     (fidélité de l'instantané) mais **n'est pas saisi dans la Page**, et le fait est signalé.
  2. **Politique de confidentialité** — A7. Aucune durée de conservation inventée, aucun hébergeur
     nommé. Structure et trous nommés.
- **Q12 et Q14 restent ouvertes** (« truffe » et « cavage » sont-ils la même chose ; les quatre lignes
  « Autres disciplines » sont-elles des disciplines). **Le code n'attend pas la réponse** : le site
  source range lui-même ces lignes sous « Autres disciplines ». On recopie **son classement tel quel**,
  on ne fusionne rien, on ne promeut rien.
- **Q21** : une page `Privacy Policy` en brouillon au titre anglais existe en base. **Ne pas y
  toucher** ; seulement s'assurer que la page de confidentialité de #17 n'entre pas en collision de
  slug avec elle.
- **Hors périmètre** : les redirections 301 et les 52 URL appartiennent à #19-#21.

## 6. États spéciaux

| État | Émis par le serveur | Rendu par le thème |
|---|---|---|
| `aucune_portee` | `derniere-portee` et `liste-portees` ne rendent rien au visiteur (décision 26) | Accueil et Placement restent debout |
| `aucun_chien` | `grille-chiens` ne rend rien | Accueil reste debout |
| `discipline_vide` | le bloc de #15 ne rend **ni `h2` ni tableau** | Travail affiche bandeau + prose seuls |
| `donnee_absente` | « Non renseigné » (décision 18) | le motif ne compose rien |
| `photo_absente` | l'emplacement n'existe pas (§9.2) | rien |
| `coordonnees_vides` | `coordonnees-plan` rend **zéro octet**, pas de racine | Mentions légales restent lisibles |
| `page_protegee` | `core/post-content` rend le formulaire ; le `post-title` **n'est pas effacé** | exactement 1 `h1` |
| `motif_vide_apres_insertion` | — | huit cadres d'état vide dans l'éditeur, **aucun au public** |

**Réserve honnête (dette T13)** : ces états vides ne sont pas identiques entre eux — quatre conventions
coexistent depuis le lot 3, et `galerie-photos` met un **bouton** dans le sien, que §9.1 ne prévoit pas.
Les motifs de #17 les font **cohabiter sur la même page pour la première fois** (l'Accueil en porte
jusqu'à six). La divergence deviendra visible. **On la constate, on ne la corrige pas ici.**

---

## 7. Arbitrages

| # | Question | Décision | Raison |
|---|---|---|---|
| A1 | Huit `page-*.html` + `front-page.html` (lettre de l'issue) ? | **Non. Zéro gabarit créé.** Huit motifs. | Décision 44 : elle ne peut pas ouvrir un gabarit. `singular.html` rend déjà. `front-page.html` rendrait un accueil blanc. |
| A2 | La prose verbatim dans les motifs ? | **Non.** Structure et libellés de section seulement. | Décision 1 — le contenu survit au thème. Deux exemplaires sinon. |
| A3 | ~~Page Travail : neuf instances ou tableau filtrable ?~~ | ~~Neuf instances, une par discipline.~~ **RENVERSÉ le 2026-08-20 — voir A3bis.** | ~~Décision 11 ferme la liste à neuf : rien à maintenir.~~ Le raisonnement tenait la liste pour close ; `donnees.php` prouve qu'elle ne l'est pas. |
| **A3bis** | **Page Travail : combien d'instances du tableau ?** | **UNE seule, sans aucun attribut : `<!-- wp:mtb/tableau-resultats /-->`.** | Trois raisons, par ordre de gravité. **(1) Perte de donnée.** Neuf instances énumèrent neuf clés ; le défaut rend **tous** les groupes que la fonction de lecture renvoie, orphelins compris. Un résultat dont la discipline a été renommée ou n'a jamais été renseignée **n'apparaîtrait nulle part sur le site public** — la garantie de #5 « rien ne disparaît en silence » annulée par la surface d'insertion, et la contrainte 4 avec elle. **(2) Contrainte 1.** Une dixième discipline n'apparaîtrait qu'après réouverture de `patterns/travail.php` **par un développeur**. Fabienne ne le peut pas : c'est exactement le motif qui a écarté les `page-*.html` en A1, appliqué au fichier de motif lui-même. **(3) Le rendu est identique** dans le cas nominal — même `h2`, même ordre gelé, même silence sur une discipline vide (`groupes()` mémorise en statique, le coût de lecture est le même). Ce n'est donc pas un arbitrage de style : l'écriture à neuf instances marchait à l'écran et **perdait de la donnée**. Le filtre JavaScript reste écarté pour sa raison d'origine — zéro octet de JS public. |
| A4 | Dette T3 — quel `h1` pour `index.html` ? | **`wp:site-title {"level":1,"isLink":false}`**, et `page_on_front` posé en base de développement. | Aucun libellé inventé (« Actualités » contredirait Q6, « Journal » serait un acte de contenu). Le code garantit un `h1` dans **tous** les états de la base ; le réglage rend la route inatteignable. **Conséquence assumée** : tant que `page_on_front` n'est pas posé, le nom de l'élevage apparaît deux fois sur `/` — en petit dans l'en-tête, en très gros juste dessous. Défaut **visuel**, sur une route qui n'a plus vocation à recevoir personne. Consigné comme point pour `lead-design-mtb`. Option « masquer le `h1` en CSS » écartée : elle coûterait une feuille neuve pour un titre que personne ne voit. |
| A5 | Catégorie des motifs ? | **`Categories: text`** (rendue « Texte »). | Sans catégorie, le panneau range les huit sous « **Non classé** », qui sonne cassé. `register_block_pattern_category( 'mtb', 'Mont Brabant' )` exigerait `functions.php`, hors empreinte → **dette T28**. La modale de création de page ignore de toute façon les catégories : l'effet est limité au panneau « Motifs ». |
| A6 | Q6, volet tarifs des chiots | **Tranchée par l'utilisateur le 2026-08-20 : aucun tarif.** | Aucun emplacement de tarif dans `patterns/placement.php`, aucun champ, aucun montant, aucune fourchette, aucun « à partir de ». Si le contenu source porte un montant, `contenu-mtb` **ne le reprend pas** et le signale. `ETAT.md` est périmé sur ce point. |
| A7 | Politique de confidentialité | **Charpente seule, trous nommés.** | Interdiction explicite d'inventer une durée de conservation (Q3 ouverte) ; l'hébergeur dépend de Q5 ; la mention RGPD du formulaire appartient à #22-#24. Trois sections nues, aucun libellé. La page **n'est pas publiée** par cette chaîne. |
| A8 | Motif `mtb/liens-de-recours` pour `404.html` et `search.html` ? | **Non livré.** | `MASTER.md` §9.5 exige trois liens dont « La meute », dont l'adresse est **inconstructible** : `mtb_chien` est enregistré `has_archive => false`, donc « La meute » est une Page sans identifiant dérivable. Et les deux gabarits consommateurs appartiennent à #16. Remonté en question ouverte. |
| A9 | `dev-ux-mtb` lancé ? | **Non.** | Les motifs ne font que composer des blocs déjà habillés ; aucun crochet de classe neuf n'est créé. **Zéro octet de CSS ajouté, D8 tenue gratuitement.** Si un dev estime devoir écrire du CSS, c'est le signe qu'un motif a introduit une structure qu'il ne devait pas introduire. |
| A10 | Photos du site source importées ? | **Non.** | La reprise des médias appartient à #19-#21 (T12 : les formats modernes ne valent que pour les téléversements postérieurs au module d'images). Importer ici garantirait un doublon. Les galeries et bandeaux restent sans photo — ce qui démontre authentiquement D12. |

---

## 8. Interdits

- Le thème n'interroge jamais la base directement, ni ne lit aucune donnée d'élevage
  (décision 41 : zéro `$wpdb`, `WP_Query`, `get_posts`, `get_option` sur du contenu d'élevage).
- Le thème ne compose jamais une chaîne métier, ne reformate ni une date, ni un numéro LOF, ni un
  numéro de téléphone, ni une valeur de santé.
- Aucun motif n'est verrouillé, ni partiellement (`templateLock` interdit) : un motif verrouillé se
  comporte pour elle comme un fichier, exactement l'argument qui a écarté les `page-*.html`.
- L'extension n'émet aucune règle visuelle. #17 n'en modifie aucune ligne.
- Aucun montant de tarif nulle part.
- Aucune invention de fait d'élevage. Toute incertitude est une question bloquante.

---

## 9. Empreinte d'écriture

**Autorisé** : `wp-content/themes/mtb/templates/index.html` · `wp-content/themes/mtb/patterns/**` ·
toute **nouvelle** feuille sous `assets/css/` portant un nom neuf (*aucune n'est demandée*) ·
`docs/guide/` (fichiers neufs) · `docs/contracts/issue-17.md` · `docs/migration/source/pages/**`.

**Interdits absolus** : `assets/css/base.css` et `assets/css/tokens.css` (**#16 propriétaire exclusif
ce lot**, dettes T22 et T23) · `theme.json` · `functions.php` · `parts/header.html` · `parts/footer.html` ·
`assets/css/entete-pied.css` · `templates/single-*.html`, `archive-*.html`, `404.html`, `search.html`,
`singular.html` · tout `wp-content/plugins/mtb-core/**` · `assets/css/blocs/mtb-tableau-resultats.css` ·
`compose.yaml`, `docker/**`, `provision.sh` · `docs/ETAT.md`.

**Commit** : `git commit -m "…" -- <chemins>`, jamais `git add` puis `git commit` nu — l'index git est
partagé entre les trois chaînes parallèles (décision 23).

---

## 9bis. D8 — le budget de poids est la contrainte serrée de ce lot

Plafond `BRIEF.md` §12 : **200 000 octets** par page, HTML + CSS + JS décompressés, hors polices et
photos.

> **Chiffre périmé — voir §11, qui fait foi.** La valeur ci-dessous est une **mesure d'entrée**, prise
> en cours de lot. Elle a été rendue fausse par deux commits de chaînes sœurs arrivés après elle :
> `4ad6938` (#16, « repasser sous le budget de poids ») et `a09df8f` (#15, allègement de la feuille du
> tableau de résultats). La mesure finale, prise cache froid sur les octets réseau réels, donne
> **70 138 o de HTML + CSS + JS** sur la page la plus lourde. La règle gelée ci-dessous reste valable ;
> l'alarme sur la marge ne l'est plus.

**Mesure d'entrée, désormais périmée : `/essai-accueil/` = 196 434 o, soit 3 566 o de marge.**
C'est la page la plus lourde du site, et le contenu réel va l'alourdir.

**Règle gelée, opposable à tous les agents de la chaîne :**

> **On mesure chaque page après remplissage. Un dépassement se chiffre et se ventile — il ne se
> résout jamais en tronquant, en résumant ou en abrégeant le contenu.**

Raison : le contenu est le but du projet, le dépassement a une cause identifiée et externe — **le
projet expédie ~50 Ko de commentaires CSS au navigateur, faute d'étape de minification** (dette
ouverte au niveau du lot). Cette dette rendra la marge d'un coup, sans toucher à un mot. Une page
d'accueil appauvrie serait, elle, irréversible.

**Interdit de compensation** : on ne gagne pas d'octets en retouchant `base.css` ou `tokens.css` —
propriété exclusive de #16 ce lot (§9), et les rouvrir rouvrirait son travail.

## 10. Constaté, non corrigé

*Amendé le 2026-08-20 après mesure au navigateur : T22 et T23 ont été payées par #16 pendant ce lot.
Les valeurs héritées d'`ETAT.md` (0 px, 134/173 px) sont **périmées**.*

- **T22 — réparée par #16.** Mesuré sur une page d'essai portant un `core/separator` :
  `<hr class="wp-block-separator">` rend **576 px de large, 6 px de haut**, `inline-size: 576px`.
  Ce n'est plus 0 px. La conséquence de conception reste néanmoins appliquée : **aucun des huit motifs
  ne pose de `core/separator`** — la décision a été prise avant la mesure et rien n'oblige à la rouvrir.
- **T23 — réparée par #16.** Écart vertical entre composants consécutifs de `.mtb-canal`, mesuré à
  1440 px : **86 px, uniforme, sur les huit paires** relevées (Accueil ×3, BHPL ×2, Travail ×1,
  Placement ×2). Le lot 4 mesurait 134 px et 173 px. Aucune ligne de `base.css` n'a été touchée par #17.
- **T13 — toujours ouverte, et désormais visible.** Les quatre conventions d'état vide cohabitent
  effectivement sur une même page pour la première fois. Mesuré **dans l'éditeur** : 6 cadres d'état
  vide sur l'Accueil, 9 sur BHPL en France, **27 sur Travail**. **Au public : aucun** — décision 26 et
  D12 tenues. Constatée, non corrigée.
- **Hors empreinte, à router** : le favicon public est le logo WordPress
  (`/wp-includes/images/w-logo-blue-white-bg.png`, 4 405 o, première partie, aucun tiers).
  Cibles interactives sous 44 px relevées ailleurs : lien du titre de site dans l'en-tête **22 px** (#18),
  liens de chien dans le tableau de résultats **19 px** (#15), liens de nom dans la grille de chiens
  **28 px** (#13/#14). #17 n'introduit aucun élément interactif.

## 11. D8 — le budget, et comment il se lit

Mesuré cache froid, octets réseau réels (CDP `encodedDataLength`), page la plus lourde = **l'Accueil**
(et non Travail : Travail ne rend qu'un tableau sur neuf blocs posés, l'Accueil empile six composants).

| | Accueil | Travail |
|---|---|---|
| Total cache froid, polices comprises | 222 670 o | 207 476 o |
| **HTML + CSS + JS — le périmètre du budget** | **70 138 o** | **54 944 o** |
| dont les deux polices, hors budget | 148 127 o | 148 127 o |

**D8 est tenue, avec une marge de 2,8×.** La décision 14 d'`ETAT.md` est explicite : la contrainte du
brief §12 sur les polices est un **nombre de fichiers — deux maximum, tenu** ; le budget chiffré de
200 000 o porte sur **HTML + CSS + JS**. Le dépassement apparent des 200 000 o sur six pages est
entièrement imputable à `newsreader-var-latin.woff2` (124 474 o), actif partagé par tout le site, servi
une seule fois, hors de l'empreinte de #17.

**Poids public ajouté par #17 : 0 octet.** Aucun CSS, aucun JS, et un `index.html` allégé de 74 o.
Les 6 044 o des huit fichiers de motifs sont des fichiers de thème, jamais servis au navigateur.

## 12. Vocabulaire réel de WordPress 6.9 — mesuré à l'écran

Les libellés que voit Fabienne **ne sont pas** ceux que le plan supposait. À recopier exactement dans
la fiche d'aide (décision 43 — une fiche qui ment est un défaut bloquant) :

- la modale de création de page s'appelle **« Choisir une composition »**, pas « Choisir un motif » ;
- l'onglet de l'insérteur s'appelle **« Compositions »**, pas « Motifs » ;
- les catégories proposées sont **« Toutes », « Contenu de départ », « Texte »** — les huit
  compositions apparaissent dans les trois.
