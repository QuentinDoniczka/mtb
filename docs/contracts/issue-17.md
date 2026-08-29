# Contrat d'interface — Issue #17 — Gabarits des pages libres (accueil, BHPL, travail, placement)

Gelé le 2026-08-20. Opposable à `dev-front-mtb`, `contenu-mtb`, `refacto-mtb`,
`dev-integration-mtb`, `doc-client-mtb`.

**Amendé le 2026-08-29**, par ajouts datés et sans rien retirer — A5 (renumérotation T28 → T33),
A8 (levée) et **A8bis** (le reliquat livré dans `index.html`), §10 (cinq constats neufs), §11
(le « 0 octet » corrigé par la mesure), **§11.1 (D8 tranchée, T37 fermée)** et **§13 (mesures de
clôture)**. **L'arbitrage A1 n'est pas rouvert** : aucun `page-*.html`, aucun `front-page.html`.
**L'empreinte d'écriture de cette passe est plus étroite que le §9** — voir la réserve du §9.

**Amendé une seconde fois le 2026-08-29, après la passe d'intégration**, qui a reproduit les mesures
du §13 et **en a réfuté quatre**. Toutes les conclusions tiennent ; ce sont des **raisons fausses
sous des conclusions justes**, et elles sont corrigées à l'endroit où elles étaient écrites : §10 (le
`tabindex` du `<main>` — **constat retiré, il n'y a pas d'écart**), §13.2 (le `THEAD` et non les
`TH` ; le volet `INPUT#pwbox` **est** mesurable), §13.4 (l'`incomplete` de contraste court sur les
six pages), §13.5 (formulation des blocs de nom nul), §13 en-tête et §11 (**l'Accueil n'a jamais reçu
de contenu importé**). **D8 n'est pas rouverte** : tranchée en octets réseau, T37 fermée.

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
| **A8bis — liens de recours** *(ajout du 2026-08-29)* | `wp-content/themes/mtb/templates/index.html` (huit lignes ajoutées, deux retirées) |
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
| A5 | Catégorie des motifs ? | **`Categories: text`** (rendue « Texte »). | Sans catégorie, le panneau range les huit sous « **Non classé** », qui sonne cassé. `register_block_pattern_category( 'mtb', 'Mont Brabant' )` exigerait `functions.php`, hors empreinte → **dette T33** (numérotée T28 au gel, **renumérotée T33** par `ETAT.md` le 2026-08-23 : T28 était déjà pris par une dette du contrat #16, désormais payée. `ETAT.md` fait foi). La modale de création de page ignore de toute façon les catégories : l'effet est limité au panneau « Motifs ». **Toujours ouverte le 2026-08-29** — vérifié : `mtb-core/includes/blocks/categorie-mtb/bootstrap.php` déclare bien « Mont Brabant », mais par `block_categories_all`, qui est la catégorie des **blocs** de l'insérteur, pas celle des **compositions**. Les deux mécanismes sont distincts ; celui des compositions reste non appelé. |
| A6 | Q6, volet tarifs des chiots | **Tranchée par l'utilisateur le 2026-08-20 : aucun tarif.** | Aucun emplacement de tarif dans `patterns/placement.php`, aucun champ, aucun montant, aucune fourchette, aucun « à partir de ». Si le contenu source porte un montant, `contenu-mtb` **ne le reprend pas** et le signale. `ETAT.md` est périmé sur ce point. |
| A7 | Politique de confidentialité | **Charpente seule, trous nommés.** | Interdiction explicite d'inventer une durée de conservation (Q3 ouverte) ; l'hébergeur dépend de Q5 ; la mention RGPD du formulaire appartient à #22-#24. Trois sections nues, aucun libellé. La page **n'est pas publiée** par cette chaîne. |
| A8 | ~~Motif `mtb/liens-de-recours` pour `404.html` et `search.html` ?~~ | ~~**Non livré.**~~ **LEVÉE le 2026-08-29 — voir A8bis.** | ~~`MASTER.md` §9.5 exige trois liens dont « La meute », dont l'adresse est **inconstructible**…~~ La prémisse a été renversée : l'adresse se calcule au rendu. |
| **A8bis** | **Le reliquat de A8 pour la part « motifs » de #17.** | **Livré dans `templates/index.html`** : les trois `mtb/lien-de-recours` posés dans l'état « aucun résultat », et le commentaire périmé de la ligne 1 supprimé. | **Les deux raisons du refus de A8 ont disparu, et je les ai vérifiées dans le code, pas déduites.** **(1) « La meute » n'est plus inconstructible** : `blocks/lien-de-recours/rendu.php:76-101` la résout par `get_page_by_path('la-meute')` avec double garde (`publish` **et** mot de passe vide), et `render.php:39-41` fait un `return;` nu quand la destination manque — le cœur met le fichier en tampon, et c'est le **tampon vide** qui devient le rendu : pas de `<li>` vide, pas de puce orpheline. L'omission silencieuse est **mesurée**, pas crue (voir §13). **(2) Les gabarits consommateurs ne sont plus tous hors empreinte** : `404.html` et `search.html` le sont et portaient déjà les trois liens (payés par #16) ; **`index.html` ne l'a jamais été**, et son propre commentaire de ligne 1 déclarait ce reliquat au présent. Le balisage posé est recopié **caractère pour caractère** de `search.html:24-30` — les trois gabarits capables d'un état vide rendent désormais la même sortie de secours. **Zéro octet de CSS** : `.mtb-liens-de-secours` est déjà habillée dans `base.css` §11, dont le commentaire prévoit lui-même une liste « à trois, deux ou un élément ». |
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

> **Réserve du 2026-08-29 — l'empreinte de la passe de clôture est plus étroite que celle-ci.**
> L'orchestrateur a restreint l'écriture à `templates/index.html`, `patterns/**`, `docs/guide/` et
> ce fichier. **`docs/migration/source/pages/**` en a été retiré**, bien que le §9 l'autorise depuis
> le gel : les pages libres y ont été importées par #20 et #21 au lot 8, et rouvrir leurs fichiers
> risquait de défaire leur travail. **Conséquence concrète, assumée** : le constat sur le `<meta>`
> de `<head>` (§10, ajouts du 2026-08-29) est consigné **ici** et non dans
> `source/pages/README.md`, qui en est le porteur naturel. C'est un pis-aller, il est déclaré, et il
> est routé.

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
  vide sur l'Accueil, 9 sur BHPL en France, et **27 sur Travail — chiffre périmé**. Les 27 ont été
  comptés quand `patterns/travail.php` posait **neuf** blocs de tableau ; depuis **A3bis** il n'en pose
  qu'un, et le motif ne compte plus que quatre blocs au total. Le chiffre n'a pas été recompté et **ne
  doit être repris nulle part** — `doc-client-mtb` a eu raison de refuser de l'écrire dans la fiche
  d'aide. **Au public : aucun cadre** — décision 26 et D12 tenues, cela reste vrai. Constatée, non corrigée.
- **Hors empreinte, à router** : le favicon public est le logo WordPress
  (`/wp-includes/images/w-logo-blue-white-bg.png`, 4 405 o, première partie, aucun tiers).
  Cibles interactives sous 44 px relevées ailleurs : lien du titre de site dans l'en-tête **22 px** (#18),
  liens de chien dans le tableau de résultats **19 px** (#15), liens de nom dans la grille de chiens
  **28 px** (#13/#14). #17 n'introduit aucun élément interactif.

*Ajouts du 2026-08-29, à la clôture de la chaîne.*

- **`MASTER.md` §9.5 nomme deux écrans de recours, le thème en a désormais trois.** §9.5 spécifie la
  404 et la recherche sans résultat ; A8bis pose la même sortie de secours sur l'**état « aucun
  résultat » de `index.html`**, que §9.5 ne couvre pas. Extrapolation assumée, à zéro octet de CSS et
  sans un libellé composé par le thème. **À router vers `lead-design-mtb`** : étendre §9.5, ou faire
  retirer le balisage. Corollaire : le `<p>Aucun contenu à afficher.</p>` est **conservé faute de
  libellé figé** — §9.5 et §10.3 n'en donnent aucun pour l'état « archive vide », et en inventer un
  serait un acte de contenu, exactement ce que A4 a refusé pour le `h1`.
- **`MASTER.md` se contredit sur le libellé du lien vers l'index des portées, et #17 n'y touche pas.**
  §9.5 écrit « Accueil, **Les portées**, La meute » ; §9.3 « Voir toutes les portées » ; **§10.3
  (« Vocabulaire figé », qui se déclare lui-même l'arbitre) écrit « Toutes les portées »**. Le code
  (`lien-de-recours/rendu.php:71` et `editeur.js:26`) dit « Les portées », aligné sur §9.5 —
  `issue-16.md:378` le dit explicitement et **ne cite jamais §10.3**. L'écart n'a donc pas été
  arbitré, il a été hérité. Il vit **entièrement dans `mtb-core`**, hors empreinte : le corriger là
  corrigerait les **trois** gabarits d'un coup, ce qui prouve que le libellé appartient au serveur.
  #17 n'ajoute pas une occurrence de la chaîne — il ajoute un consommateur de plus d'une chaîne
  unique. **Ne pas y embarquer** le `h1` de `archive-mtb_portee.html` (§10.3 régit des libellés de
  lien, pas un titre de page).
- ~~**Le `<main>` d'`index.html` n'est pas focusable, contrairement à celui de ses deux frères.**~~
  **RÉFUTÉ le 2026-08-29 par la passe d'intégration, et vérifié à nouveau ici. Aucune issue `a11y` à
  ouvrir : il n'y a pas d'écart.** Le constat lisait le **fichier** et concluait sur le **rendu** —
  c'est l'erreur, et elle mérite d'être nommée. `index.html` produit bien son `<main>` par un
  `core/group` (`tagName:main`, `anchor:contenu`) dont le balisage sauvegardé ne porte pas de
  `tabindex` ; mais `wp-content/themes/mtb/functions.php`, fonction
  `mtb_rendre_la_cible_focalisable` accrochée à `render_block`, **pose l'attribut au rendu** : elle
  ne se déclenche que sur `attrs.anchor === 'contenu'`, ouvre un `WP_HTML_Tag_Processor` sur la
  première balise, et **n'écrit que si `tabindex` est absent** — donc sans jamais doubler celui que
  `404.html` et `search.html` écrivent en dur. `enveloppe-fiche.php:56` documentait déjà ce filtre.
  Mesuré sur le HTML servi des trois routes :
  `/author/fabienne/` → `<main tabindex="-1" class="wp-block-group mtb-canal is-layout-constrained…" id="contenu">`
  (attribut injecté, d'où l'ordre différent) · `/?s=zzz` et une 404 →
  `<main id="contenu" tabindex="-1" class="mtb-canal">` (attribut littéral). La cible du lien
  d'évitement est focalisable **sur les trois**, et la passe d'intégration l'a éprouvé au
  comportement (Tab → « Aller au contenu » → Entrée pose le focus sur `MAIN#contenu`), pas seulement
  à l'attribut. **Leçon à garder : sur ce thème, l'absence d'un attribut dans un `.html` ne prouve
  rien — un filtre `render_block` peut le poser.**
- **Dette neuve, au débit de #16 et non de #17 : `mtb/lien-de-recours` émet un `<li>` sans garde de
  conteneur.** Le bloc rend `<li class="mtb-lien-de-recours">…</li>` sans vérifier qu'un `<ul>` ou un
  `<ol>` l'enveloppe ; posé seul au premier niveau d'un `post_content`, il produit un `<li>` orphelin
  dans `.entry-content`. **Aucun gabarit livré ne fait cela** — `404.html`, `search.html` et
  désormais `index.html` le placent tous les trois dans un `core/list`. **Précision qui change la
  gravité** : `block.json:18` porte `"supports": {"inserter": false}`, vérifié — le composant
  n'apparaît dans aucun panneau d'insertion ni aucune recherche de bloc, donc **le chemin d'édition
  ordinaire de l'éleveuse ne peut pas produire ce cas**. Il reste atteignable par l'éditeur de code
  ou par un collage de balisage brut, chemins délibérés et non offerts. Constaté, non corrigé, **à
  router vers #16** — l'extension est hors de l'empreinte de #17.
- **La valeur du numéro de voie existe dans le `<head>` des pages archivées, et n'est écrite dans
  aucun `.md` du dépôt.** Mesuré par moi : **53 des 54 fichiers** de `docs/migration/source/html/`
  portent `<meta property="business:contact_data:street_address" content="ROUTE DE SALERNES 3060"/>`
  — numéro **après** le nom de voie, là où la page Contact écrit `3060 ROUTE DE SALERNES`. Le seul
  fichier sans est `bhpl-en-france.html`, dont le corps est vide (302). **Ce n'est pas une donnée des
  Mentions légales** : c'est un `<meta>` de gabarit global, présent sur toutes les pages, et l'écrire
  sous une section « Mentions légales » ferait croire que cette page porte le numéro — un pas vers
  l'harmonisation, que la règle d'exactitude interdit. La **classe** de fait est déjà déclarée deux
  fois (`source/html/README.md` §4 et `issue-19.md` §3.1 bis, qui posent que la réduction ne découpe
  que le `<body>` et qu'aucune métadonnée de `<head>` n'atteint une capture) ; seule la **valeur**
  manquait. Consignée ici faute de mieux : le porteur naturel est
  `docs/migration/source/pages/README.md`, que le §9 de ce contrat autorise mais que l'empreinte
  restreinte de la passe du 2026-08-29 n'autorisait pas. **À router.**
- **La contradiction interne des Mentions légales est vérifiée, et elle était déjà écrite.** Contrôlé
  sur `docs/migration/source/html/mentions-legales.html` (blob git `4001f9a5…`, la copie de l'arbre
  de travail est en CRLF et donne une autre empreinte — artefact de dépôt, pas d'altération) :
  `Gueneau` sans accent une fois, `Guéneau MAJ Ju…` une fois dans le pied de page du même fichier,
  `ROUTE DE SALERNES` sans numéro dans le corps, ` 680505619` à neuf chiffres. `ETAT.md` affirmait
  que la source ne portait ni siège social ni raison sociale : **c'est faux, elle porte les deux**
  (`Elevage du Mont Brabant`, SIRET `82237792500018`). **Rien n'a été harmonisé, et c'est le bon
  choix.** Les écarts sont déjà consignés en trois endroits — `source/pages/README.md`
  (« Écarts avec les coordonnées de référence »), `issue-21.md` §5, et la clé `ecart` de
  `migration/resultats-pages/donnees/pages/mentions-legales.json` — plus, dans les mots de
  l'éleveuse, `docs/guide/page-ce-qui-a-ete-repris-de-l-ancien-site.md`. **Aucun quatrième
  exemplaire n'a été créé.** Une seule nuance à ne pas graver de travers : le `0680505619` à dix
  chiffres est celui de l'**encart latéral global du site**, pas d'une seconde écriture propre à la
  page — `source/pages/README.md` le dit déjà (« Ce n'est pas une donnée de la page qui le porte »).

## 11. D8 — le budget, et comment il se lit

Mesuré cache froid, octets réseau réels (CDP `encodedDataLength`), page la plus lourde = **l'Accueil**
(et non Travail : Travail ne rend qu'un tableau sur neuf blocs posés, l'Accueil empile six composants).

| | Accueil | Travail |
|---|---|---|
| Total cache froid, polices comprises | 222 670 o | 207 476 o |
| **HTML + CSS + JS — le périmètre du budget** | **70 138 o** | **54 944 o** |
| dont les deux polices, hors budget | 148 127 o | 148 127 o |

**Les polices sont hors budget, et c'est acquis.** La décision 14 d'`ETAT.md` est explicite : la
contrainte du brief §12 sur les polices est un **nombre de fichiers — deux maximum, tenu** ; le budget
chiffré porte sur **HTML + CSS + JS**. Le dépassement apparent des 200 000 o sur six pages, mesuré
polices comprises, est entièrement imputable à `newsreader-var-latin.woff2` (124 474 o), actif partagé
par tout le site, servi une seule fois, hors de l'empreinte de #17.

### 11.1 Une ambiguïté du brief que #17 ne peut pas trancher seule — **TRANCHÉE le 2026-08-29**

> **ARBITRAGE RENDU. Le budget de `BRIEF.md` §12 se lit en octets réseau, c'est-à-dire compressés.**
>
> - **Tranché le** : 2026-08-29.
> - **Par** : l'orchestrateur `/lead-mtb`, au niveau du lot, sur la réponse de l'utilisateur. Ce n'est
>   pas une mesure, c'est une décision — c'était précisément ce que la dette T37 réclamait.
> - **Conséquence** : Accueil **72 413 o** et Travail **58 439 o** contre un plafond de 200 000 o.
>   **D8 est tenue**, sans réserve, sur les deux pages. La **dette T37 se ferme**.
> - **Ce qui ne se ferme pas** : le dépassement en octets **décompressés** (Accueil 210 680 o, soit
>   10 680 o au-dessus) reste un fait mesuré, et sa cause reste identifiée et chiffrée — **~50 Ko de
>   commentaires CSS non minifiés** expédiés au navigateur. Il devient une **dette non bloquante
>   distincte**, ouverte au niveau du lot. #17 ne l'ouvre pas et n'agit pas dessus.
>
> Le tableau ci-dessous est conservé tel quel : il porte les deux mesures qui ont motivé l'arbitrage,
> et elles restent vraies l'une comme l'autre. Seul le verdict change.


**`BRIEF.md` §12 écrit « HTML + CSS + JS < 200 Ko hors photos » et ne dit pas si les octets sont
comptés compressés ou décompressés.** Les deux lectures ne donnent pas le même verdict :

| Page | Octets réseau (compressés) | Décompressés |
|---|---|---|
| Travail | 58 439 o ✅ | 170 658 o ✅ (marge 29 342 o) |
| **Accueil** | 72 413 o ✅ | **210 680 o ❌ — 10 680 o au-dessus** |

**Je ne déclare donc pas D8 tenue sans réserve.** Elle est tenue en octets réseau, et manquée de 5,3 %
sur l'Accueil en octets décompressés. L'arbitrage appartient au niveau du lot, pas à cette chaîne.

**La cause est identifiée, chiffrée, et entièrement hors de l'empreinte de #17** : le projet expédie
au navigateur des feuilles non minifiées, commentaires compris — `base.css` ~~42 648 o~~ **46 927 o
(remesuré le 2026-08-29 : la feuille a grossi depuis le gel ; c'est ce chiffre qui fait foi)**,
`mtb-bandeau-ouverture.css` 33 980 o, `entete-pied.css` 28 269 o, `mtb-grille-chiens.css` 26 333 o.
Une étape de minification rendrait la marge d'un coup, sans retirer un mot de contenu.

**Ce que #17 ajoute au poids public : 0 octet** — aucun CSS, aucun JS, et un `index.html` qui a
maigri. Aucun octet de dépassement ne lui est imputable, et la règle gelée au §9bis reste entière :
**un dépassement se chiffre et se ventile, il ne se résout jamais en tronquant le contenu.**

**Poids public ajouté par #17 : 0 octet.** Aucun CSS, aucun JS, et un `index.html` allégé de 74 o.
Les 6 044 o des huit fichiers de motifs sont des fichiers de thème, jamais servis au navigateur.

> **Chiffre amendé le 2026-08-29 : « 0 octet » n'est plus exact.** La passe A8bis ajoute des octets sur
> **une seule route**, et le chiffre prévu au plan était faux — c'est la mesure qui fait foi.
>
> | | octets |
> |---|---|
> | Prévu au plan | +71 o |
> | **Mesuré** sur `/author/fabienne/`, `curl -s … \| wc -c` avant/après | **19 708 → 19 910 = +202 o** |
>
> **L'écart de 131 o n'est pas dans notre balisage.** Le cœur de WordPress inline **219 o** de
> `wp-includes/blocks/list/style.min.css` la première fois qu'un `core/list` apparaît sur ce gabarit.
> Coût du cœur, déjà payé de la même façon sur `404.html` et `search.html`. Reste, à nous : le retrait
> du commentaire périmé (−258 o) contre le balisage rendu (+235 o) et sa sérialisation (≈ +6 o).
>
> **Sur les huit pages libres, sur l'accueil, sur les fiches et sur les archives de portées : toujours
> 0 octet.** `index.html` n'est plus servi par `/` (`page_on_front` est posé) ; la seule route qui
> l'atteint est une archive vide. **D8 n'est pas entamée** : les 202 o portent sur une page de
> ~19,9 Ko, et la marge en cause au §11.1 est sur l'Accueil, que cette passe ne touche pas d'un octet.

## 12. Vocabulaire réel de WordPress 6.9 — mesuré à l'écran

Les libellés que voit Fabienne **ne sont pas** ceux que le plan supposait. À recopier exactement dans
la fiche d'aide (décision 43 — une fiche qui ment est un défaut bloquant) :

- la modale de création de page s'appelle **« Choisir une composition »**, pas « Choisir un motif » ;
- l'onglet de l'insérteur s'appelle **« Compositions »**, pas « Motifs » ;
- les catégories proposées sont **« Toutes », « Contenu de départ », « Texte »** — les huit
  compositions apparaissent dans les trois.

---

## 13. Mesures de clôture — 2026-08-29

Prises sur la pile Docker, **avec le contenu réel importé au lot 8** et non sur une base d'essai
légère. Chaque mesure affirme d'abord la présence de son objet (décision 56).

> **Réserve du 2026-08-29 : cette phrase est fausse pour l'Accueil, et l'Accueil est justement la
> page dont le poids a servi d'argument. L'exception doit être lue avant tout chiffre de ce §13 et
> du §11.**
>
> **L'Accueil n'a jamais reçu de contenu importé.** Trois faits vérifiés :
> `docker/provision/provision.sh` fait un `wp post update "$accueil_id"` **inconditionnel** à chaque
> provisionnement — « contenu réaffirmé depuis le motif `mtb/accueil` » — donc la page est
> **réécrite depuis la charpente** à chaque `make up` ; son `post_modified` est **2026-08-29
> 19:15:25**, quand les pages réellement importées portent **2026-08-28 23:03:22** (`bhpl`,
> `travail`, ids 249-255, dont **aucun accueil**) ; et `docs/migration/reprise-resultats-pages.md`
> déclare déjà l'Accueil **hors périmètre de reprise**, précisément pour cette raison.
>
> **Ce que cela invalide** : les **63 mots** relevés au §13.1 pour l'Accueil sont ceux d'une
> charpente, pas d'un contenu ; et par ricochet, les **210 680 o décompressés** du §11.1, qui
> portaient l'alarme sur D8, ont été pris sur un état de base que **plus personne ne peut rejouer**.
> Remesuré à neuf : **Travail 169 207 o** (concorde avec les 170 658 o d'origine), **Accueil
> 185 188 o — soit 25 Ko sous le chiffre d'origine, et sous le plafond**. Le dépassement de 10 680 o
> **n'est pas observable sur cette pile**.
>
> **Ce que cela n'invalide pas, et il ne faut pas s'y tromper** : **D8 reste tranchée** par
> l'utilisateur le 2026-08-29, en **octets réseau**, et **T37 reste fermée** — voir §11.1. Rien ici
> ne rouvre cet arbitrage. Et **la cause est intacte, mesurée, indépendante du contenu** : le projet
> expédie **147 492 o de CSS décompressé pour 56 140 o sur le réseau**. C'est la dette de
> minification, et elle ne se règle pas en retirant un mot de contenu.
>
> **Les mesures d'accessibilité des §13.2 à §13.4 ne sont pas touchées** : elles portent sur les cinq
> autres pages autant que sur l'Accueil, et le seul débordement relevé est sur **Travail**, dont le
> contenu est bien celui du lot 8.

### 13.1 Présence, avant tout jugement

Les six pages libres publiées répondent **200**, avec leur `<title>`, leur `h1` et leur volume de
texte : Accueil 63 mots, BHPL 1 083, Littérature 30, Mentions légales 57, Placement 309, Travail
1 141 (et **8 tableaux**). Les deux brouillons — `bhpl-en-france` et `politique-de-confidentialite` —
répondent **404 en anonyme**, ce qui est leur état voulu ; ils ont été mesurés par prévisualisation
et **n'ont pas été publiés**.

### 13.2 Zoom 200 % et 360 px — 42 combinaisons, 41 propres

Méthode du projet : au lieu de zoomer, on divise par deux la largeur du viewport CSS (360 px zoomé à
200 % = 180 px CSS). Largeurs jouées : 1440, 1280, 768, 720, 640, 360, 180. Critère :
`scrollWidth > clientWidth`.

- **Les six pages passent à 360 px.** La ligne du brief est tenue.
- **Un seul débordement, sur 42** : **Travail à 180 px**, `scrollWidth 231 / clientWidth 180`.
  Seuil exact relevé par balayage : propre jusqu'à **232 px**, déborde à partir de **230 px**.
- Élément fautif nommé, chaîne complète :
  `MAIN#contenu > DIV.entry-content > DIV.mtb-tableau-resultats > SECTION > TABLE.mtb-tableau`.
  Le `SECTION` parent est en `overflow-x: visible` ; la table la plus large est **« Mondioring »,
  215 px** de largeur min-content. Les `TH` remontent d'abord comme fautifs et sont des faux
  positifs — ils ne contribuent pas au `scrollWidth`.

  > **Raison corrigée le 2026-08-29 : ce ne sont pas les `TH` qui sont hors flux, c'est le `THEAD`.**
  > La première formulation écrivait « les `TH` sont hors flux (`position:absolute; width:1px`) » ;
  > c'est faux, et vérifié : les `TH` sont `position: static`, larges de 63 à 133 px. C'est
  > `.mtb-tableau thead` qui porte `position:absolute; inline-size:1px; block-size:1px;
  > clip-path:inset(50%)` (`base.css` §10.5.1, sous `max-width:47.999rem`) — le patron du tableau
  > empilé en cartes. Les `TH` sont donc **écrêtés par leur conteneur**, pas retirés du flux. **La
  > conclusion ne bouge pas** (ils ne contribuent pas au `scrollWidth`) ; seule la raison était
  > fausse, et sur ce projet une raison fausse est ce qui fait ouvrir une issue pour rien.

**C'est la dette T42, et la mesure corrige le chiffre qu'on m'avait transmis.** T42 était annoncée à
« 4 combinaisons sur 36 » ; ce chiffre avait été relevé **sur les fiches de portée et de chien**, pas
sur les pages libres. Sur les huit pages libres, T42 vaut **1 combinaison sur 42**. Contre-vérifié
hors périmètre pour ne pas conclure à tort : `/portees/a3-2025/` (244/180,
`TABLE.mtb-tableau--chiots`), `/portees/v1-2024/` (194/180, idem), `/chien/jango/` (198/180,
`TABLE.mtb-tableau`), `/chien/very-best/` propre.

> **Correction du 2026-08-29 — j'avais écrit que le volet `INPUT#pwbox` de T42 « n'est pas mesurable
> sur cette installation, aucun contenu n'y portant de mot de passe ». C'est FAUX, et la source de
> l'erreur est identifiée : je me suis fié à un relevé d'agent au lieu d'interroger la base.**
> Vérifié moi-même : `wp post list --post_type=page --fields=ID,post_name,post_status,post_password`
> rend `5,espace-prive,publish,chiot2026`. La page **« Espace privé (démonstration) » est publiée ET
> protégée**, créée par `provision.sh` ; `/espace-prive/` répond **200** en anonyme et rend
> `post-password-form` avec `<input id="pwbox-5" size="20">`. La passe d'intégration l'a mesurée à
> **263/180** — **la valeur exacte d'`ETAT.md`**. Le volet est donc intact, et T42 est reproduite
> dans ses trois composantes.

**Total de T42, réénoncé** : **1 combinaison sur 42 sur les pages libres** — ce qui reste la
requalification juste, le chiffre d'origine ayant bien été relevé sur les fiches — et **5
combinaisons vivantes au total** sur l'ensemble du site, les quatre d'origine ayant été rejouées et
vivant toutes : `/portees/a3-2025/` 244/180, `/portees/v1-2024/` 194/180, `/chien/jango/` 198/180,
`/espace-prive/` 263/180, plus `/travail/` 231/180.

**Mesuré, non corrigé** : les deux feuilles fautives appartiennent à #15 et #16, et le troisième
coupable (`size="20"` codé en dur) est dans `post_password_form()` du cœur de WordPress.

### 13.3 Parcours clavier

| Page | arrêts | sans anneau de focus |
|---|---|---|
| Accueil | 11 | **0** |
| BHPL | 13 | **0** |
| Littérature | 4 | **0** |
| Mentions légales | 6 | **0** |
| Placement | 36 | **0** |
| Travail | 11 | **0** |

Le lien d'évitement « Aller au contenu » est le **premier arrêt sur les six pages**, avec un double
anneau (`outline: solid 2px rgb(22,36,28)` décalé de 2 px, plus `box-shadow 0 0 0 2px
rgb(242,241,234)`). Aucun arrêt orphelin, aucun piège au clavier, aucun `tabindex` positif.

**Un faux positif écarté, et il vaut d'être consigné** : une première passe comptait 5 échecs sur
l'Accueil (liens de la grille de chiens), dont l'`outline` propre est bien `none`. L'anneau est en
réalité dessiné sur la carte ancêtre en `:focus-within`, le lien portant un `::after` étiré. Vérifié
par comparaison des styles calculés avant/après focus **et par capture d'écran**. Le chiffre juste
est **0**. C'est la leçon du lot 5 rejouée : un audit mesure une propriété, jamais une expérience.

### 13.4 axe-core

**0 violation, toutes gravités confondues, sur les six pages** (26 à 40 règles par page).

> **Corrigé le 2026-08-29 : l'`incomplete` de `color-contrast` n'est pas propre à Travail — il court
> sur les SIX pages, et c'est un silence, pas un détail.** J'avais écrit « un seul `incomplete` sur
> Travail ». La passe d'intégration a compté les nœuds non jugés : **6 / 7 / 3 / 6 / 6 / 12** sur
> Accueil, BHPL, Littérature, Mentions légales, Placement, Travail — message identique partout
> (**« background gradient »**), et **le `h1` ainsi que le lien de titre de site de l'en-tête en font
> partie sur chaque page**. Les **0 violation** restent vrais : axe ne signale rien, parce qu'il
> **refuse de trancher**, ce qui n'est pas la même chose que passer.
>
> **Énoncé qu'il faut laisser écrit noir sur blanc** : *le contraste du texte sur fond dégradé n'est
> vérifié par personne, sur la totalité des pages de ce lot.* Ce n'est ni mesuré ni infirmé — c'est
> un trou. La règle transverse du brief fait de l'accessibilité AA une exigence bloquante, et
> l'en-tête est le composant le plus vu du site. **À router** : la vérification demande une lecture
> de contraste sur pixels rendus, au pire pixel du dégradé, comme le lot 4 l'a fait pour ses onze
> contrastes (décision 36). Hors empreinte de #17, qui ne touche ni l'en-tête ni un dégradé.

`color-contrast` a bien tourné et ne rend aucune violation. Passent également
`skip-link`, `bypass`, `landmark-one-main`, `heading-order`, `link-name`, `region` et
`meta-viewport` (le zoom n'est pas bloqué). **Zéro requête vers un domaine tiers** relevée au
navigateur sur les pages instrumentées.

### 13.5 A8bis — l'omission silencieuse, mesurée et non crue

Route qui sert réellement `index.html` et atteint son état vide : **`/author/fabienne/`, HTTP 200**
(l'autrice existe, elle n'a écrit aucun article). Preuve que le fichier est bien la source servie, et
non une copie en base : le commentaire périmé, propre à ce fichier, disparaît de la réponse après
modification (`grep -c` : 1 → 0).

| Mesure | Avant | Après |
|---|---|---|
| `grep -c 'mtb-lien-de-recours'` | 0 | **2** |
| `wc -c` de la réponse | 19 708 | 19 910 |

**Deux liens, pas trois, et c'est le comportement voulu.** La page `la-meute` n'existe pas en base
(`/la-meute/` → 404) : le bloc s'omet, sans `<li>` vide ni puce orpheline
(`grep -c '<li[^>]*>[[:space:]]*</li>'` → **0**). C'est la **dette T30**, ouverte, qui devient ainsi
observable.

> **Point de vocabulaire, consigné parce qu'il a déjà causé une confusion le 2026-08-29 :** la
> **dette T30** est « la page *La meute* n'existe que dans la base de développement ». Elle n'a
> **aucun rapport avec l'issue #30**. Les deux se lisent presque pareil ; ce contrat parle
> exclusivement de la dette. Les deux liens rendus pointent sur `home_url()` et sur l'archive des portées, aucune URL
n'étant écrite par le gabarit (`grep 'href=' index.html` → **0**).

`parse_blocks()` sur le gabarit rend un arbre propre —
`core/query-no-results → core/paragraph, core/list → mtb/lien-de-recours ×3`.

> **Formulation corrigée le 2026-08-29.** J'avais écrit « **sans bloc de nom nul**, donc sans
> balisage hors bloc » : faux à la lettre. `parse_blocks()` en rend **trois**, comme pour tout
> fichier de gabarit — ce sont les sauts de ligne entre blocs de premier niveau, et leur `innerHTML`
> est **vide**. L'énoncé juste est : **aucun bloc de nom nul ne porte de HTML**, donc aucun balisage
> libre n'est servi hors d'un bloc. C'est ce que la mesure d'origine disait ; c'est mon résumé qui
> avait laissé tomber la qualification. La région « aucun résultat » d'`index.html` et celle de `search.html`
ne diffèrent **que** par le texte du paragraphe.

### 13.6 Ce qui n'a pas été mesuré, et doit être dit

- **Le zoom navigateur réel à 200 %** : c'est la réduction du viewport CSS qui a été jouée, méthode
  du projet. Elle ne modifie ni le `devicePixelRatio` ni le rendu des polices.
- **Firefox et WebKit** : Chromium seul. La reflowabilité multi-moteurs n'est pas couverte.
- **Le parcours clavier à d'autres largeurs que 1280 px** — un menu replié en dessous de 768 px
  changerait l'ordre de tabulation.
- **L'ordre visuel de tabulation** : seul l'ordre DOM a été vérifié, pas sa concordance à l'écran.
- **Le contraste des anneaux de focus** sur chaque fond : `color-contrast` d'axe ne les juge pas.
- **Les libellés de l'éditeur du §12** n'ont pas été relevés à nouveau à l'écran cette passe ; ils
  sont repris de la mesure du 2026-08-20.
- **Le volet `INPUT#pwbox` de T42** : sans objet sur cette installation (§13.2).
