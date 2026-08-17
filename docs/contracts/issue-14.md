# Contrat d'interface — Issue #14 — Composant Grille de chiens

**Gelé le 2026-08-17.** Contraignant à partir d'ici. En cas de divergence avec un plan, un commentaire
de code ou un rapport d'agent, **ce document fait foi**.

> **Amendé DEUX FOIS le 2026-08-17 après observation en navigateur — voir §13.**
>
> **Première passe.** Trois affirmations de la rédaction initiale étaient fausses et sont corrigées à la
> source : le canal large est **inatteignable** (donc la grille rend **2 colonnes, jamais 4**), la
> catégorie `mtb` **a été livrée** par une chaîne sœur, et le `position: relative` du nom **ne suffisait
> pas** à le rendre sélectionnable.
>
> **Seconde passe, de finition.** Cinq décisions changent : le filtre de catégorie local **est
> supprimé** (§3, arbitrage 15), le module **expose désormais une fonction globale de rendu** pour #16 et
> #17 (§3 bis, et cela contredit la lettre du §3 et du §10 d'origine), la seconde phrase d'état vide
> **est reformulée** et son **choix dépend maintenant de la base** et non du seul réglage (§8), le
> crochet `mtb-etat-vide__composant` devient **`mtb-etat-vide__nom`** (§4, §8), et le cerne photo passe
> **obligatoirement par un pseudo-élément** (§6). Les paragraphes concernés portent la correction ; §13
> en tient le journal.

Il hérite intégralement de [`issue-1.md`](issue-1.md) — nommage (§6), français littéral sans i18n (§7),
frontière thème/extension (§8), états spéciaux (§9), recette d'un module `blocks/` **sans étape de
build** (§10), conventions de code et plafond PHP 8.1 (§12), interdits (§13). Il **consomme** sans le
modifier le contrat gelé [`issue-4.md`](issue-4.md), et le contrat gelé [`issue-2.md`](issue-2.md) pour
le thème.

**C'est le premier bloc du projet.** `includes/blocks/` était vide. La recette du contrat #1 §10 n'avait
jamais tourné : ce document dit ce qui a été supposé et ce qui reste à vérifier en navigateur.

---

## 1. L'approche retenue

Un bloc `mtb/grille-chiens`, **un seul réglage** — **« Statut à afficher »** — à cinq choix mutuellement
exclusifs :

| Valeur d'attribut | Libellé affiché | Origine du libellé |
|---|---|---|
| `tous` **(défaut)** | **Tous les statuts, groupés** | chaîne d'interface du module |
| `reproducteur` | Reproducteurs | `libelle_statut_pluriel()` de `content/chien/choix.php` |
| `en_cours_de_confirmation` | En cours de confirmation | idem |
| `retraite` | Retraités | idem |
| `disparu` | Disparus | idem |

**Le défaut `tous` n'est pas cosmétique, il protège la fidélité.** Si « La meute » se composait de
quatre blocs filtrés, le jour où un chien reçoit un statut dont aucun bloc n'a été inséré, **le chien
deviendrait invisible sans que rien ne le signale** — l'inverse exact de la contrainte 3. Le mode groupé
est complet par construction.

**Alternatives écartées** — ne pas les re-litiger : un bloc sans aucun réglage (ne sert pas la lettre de
BRIEF §6, interdit une grille de reproducteurs sur l'accueil) ; des **cases à cocher** multi-statuts
(permet un sous-ensemble incomplet, donc un chien invisible faute d'avoir été coché — le défaut même
que le défaut `tous` évite) ; un index typographique sans photo ni grille (contredit BRIEF §10, « les
photos de chiens sont la matière première du site »).

## 2. Empreinte fichiers — close

**Créés par cette issue, et rien d'autre :**

```
wp-content/plugins/mtb-core/includes/blocks/grille-chiens/
├── bootstrap.php    namespace MTB\Core\Blocks\GrilleChiens · add_action( 'init', …, 20 )
├── block.json       apiVersion 3 · render: "file:./render.php" · editorScript = une POIGNÉE
├── donnees.php      liste fermée, assainissement de l'attribut, lecture + filtrage, mémo
├── balisage.php     construction du HTML — échappement AU RENDU, ici et nulle part ailleurs
├── render.php       inclus par WordPress SEUL — une seule instruction de sortie
└── editeur.js       wp.blocks.registerBlockType + wp.element.createElement, zéro build

wp-content/themes/mtb/assets/css/blocs/mtb-grille-chiens.css
docs/contracts/issue-14.md
docs/guide/composant-grille-chiens.md
```

> **Renommée le 2026-08-17 (`git mv`), et c'est une convention de lot, non un goût.** La fiche
> s'appelait `composant-grille-de-chiens.md`. Les dix fiches de composants suivent désormais
> **`composant-<nom-du-bloc>.md`**, et le bloc s'appelle `grille-chiens` : trois conventions
> différentes cohabitaient sur le disque, et l'issue #25 doit composer un sommaire par-dessus dix
> fiches. Le nom du fichier se déduit donc du nom du bloc, sans exception.

**Lus, jamais modifiés** : `includes/query/chien/{bootstrap,lecture}.php`,
`includes/content/chien/choix.php`, `themes/mtb/assets/css/{tokens,base}.css`,
`themes/mtb/functions.php`.

**Pourquoi la feuille de style vit dans le THÈME et non dans le bloc.** Le contrat #1 §8 est littéral :
« L'extension n'émet **aucune** règle visuelle ni mise en page. Un bloc rend une structure et des
crochets de classes. Le thème habille. » Et le thème porte **déjà** le mécanisme prévu pour ce cas :
`themes/mtb/functions.php:194` → `mtb_feuilles_de_blocs()` parcourt le registre des blocs sur
`wp_loaded` et met en file `assets/css/blocs/<espace>-<nom>.css` **si le fichier existe**, via
`wp_enqueue_block_style()` avec `'deps' => array( 'mtb-jetons' )` et `'path'` renseigné.

Conséquences gelées : **`functions.php` n'est pas touché** (aucune déclaration n'est nécessaire),
`base.css`, `tokens.css`, `editor.css`, `theme.json`, `templates/`, `parts/` et `patterns/` **non
plus**, et le nom du fichier contient le nom du bloc — **collision avec une chaîne sœur impossible par
construction**.

## 3. Ce que l'extension enregistre

| Élément | Nom exact |
|---|---|
| Bloc | **`mtb/grille-chiens`** — gelé |
| Attribut | **`statut`**, `string`, défaut **`tous`**, `enum` des cinq valeurs du §1 |
| Poignée de script d'éditeur | **`mtb-grille-chiens-editeur`** (contrat #1 §6 : `mtb-<module>-<usage>`) |
| Objet JS de localisation | **`mtbGrilleChiens`** |
| Filtre exposé | **`mtb_grille_chiens_tailles_image`** — voir §7 |
| Espace de noms PHP | `MTB\Core\Blocks\GrilleChiens` |

| Fonction globale de rendu | **`mtb_grille_chiens_rendu()`** — voir §3 bis, **ajoutée à la finition** |

**Le module ne réserve aucun nom de LECTURE** et ne redéclare aucune fonction `mtb_get_*`. **Rien ne
peut donc ombrer `query/chien/` ni `query/portee/`** (décision 19).

> **Amendement de finition.** La rédaction d'origine disait « **aucune** fonction `mtb_*` globale n'est
> déclarée par cette issue » et le §10 en faisait un interdit. **Ce n'est plus vrai**, sur ordre du lot :
> une fonction globale de rendu est désormais exposée, pour la raison écrite au §3 bis. L'interdit
> d'origine est **remplacé, pas contourné** — sa forme exacte est réécrite au §10.

**`block.json` — décisions à ne pas « corriger »** :

- **Aucun objet `supports` pour `color`, `typography`, `spacing`, `dimensions`, `background`, `shadow`,
  `filter`.** Leur **absence** est ce qui les ferme. C'est ainsi que la dette **T7** n'est pas recréée :
  aucune couleur, aucune taille, aucun espacement hors des quinze jetons n'est atteignable depuis ce
  bloc (MASTER §14).
- `"align": false` · `"anchor": false` · `"html": false` · `"customClassName": false` ·
  `"reusable": false` · `"multiple": true` · `"interactivity": false`.
- **Pas de clé `"example"`** : elle déclencherait un appel REST par survol dans l'insérteur et
  afficherait l'état vide en guise d'aperçu. N'aggrave pas la dette T4 d'une ligne.
- **Pas de clé `"textdomain"`** : contrat #1 §7, aucune fonction i18n.
- **`icon: "grid-view"`** — Dashicon, servi depuis notre domaine (D6). `pets` écarté : MASTER §13
  interdit une silhouette de chien ; l'icône décrit la disposition, pas l'animal.
- L'`enum` est la liste fermée écrite **une seconde fois** (`block.json` n'est pas du PHP et ne peut pas
  appeler `statuts()`). Assumé et **borné** : la seule autorité au rendu est `statuts()`. Une divergence
  ne peut produire qu'un repli en mode groupé, **jamais un statut inventé**.

### La catégorie de blocs `mtb` — arbitrage RÉVISÉ, et la dette T-#14-b est SOLDÉE

**Décision en vigueur : `"category": "mtb"` dans `block.json`, et RIEN D'AUTRE.** Le module **ne porte
aucun filtre `block_categories_all`**. La catégorie « Mont Brabant » est déclarée **une seule fois pour
tout le catalogue** par `includes/blocks/categorie-mtb/`, livré par l'issue #6, comme le contrat #1 §10
l'exigeait depuis le début.

**Pourquoi le filtre local a existé, et pourquoi il n'existe plus.** Au gel, `includes/blocks/` était
vide et aucune chaîne du lot n'avait `categorie-mtb/` dans son empreinte. Or `register_block_type()` **ne
valide pas** la catégorie : ni erreur, ni `_doing_it_wrong` — l'insérteur construit ses sections depuis
les catégories **enregistrées**, et un bloc dont la catégorie n'y figure pas **n'est rendu nulle part**
tout en restant trouvable par la recherche. La pire panne possible : **muette et à moitié vraie**, une
DoD cochée sur un composant que Fabienne ne trouve pas. Un filtre local **idempotent** était le
garde-fou proportionné à ce risque.

`categorie-mtb/` étant livré et **vérifié en exécution**, le garde-fou n'a plus d'objet et devient une
**déclaration en double** — la forme exacte de la dette T9, en trois exemplaires dans ce lot. Il est
donc supprimé. Dette **T-#14-b : SOLDÉE.**

**Vérifié en exécution le 2026-08-17**, après suppression : `apply_filters( 'block_categories_all' )`
rend **exactement une** entrée de slug `mtb`, intitulée « Mont Brabant », et le bloc est enregistré avec
`category: mtb`, titre « Grille de chiens ». Constaté aussi dans l'insérteur de la toile : le bloc est
présent avec son titre et sa description en français.

> **À ne pas « corriger » par prudence.** Réintroduire un filtre local ici, même idempotent, recrée la
> déclaration en double. Si l'insérteur perdait un jour la rubrique, le correctif est dans
> `categorie-mtb/`, **jamais ici**.

## 3 bis. La fonction de rendu réutilisable — interface pour #16 et #17

**Ajoutée à la finition, sur ordre du lot.** C'est la seule surface publique du module hors du bloc.

```php
mtb_grille_chiens_rendu( string $statut = 'tous' ): string
```

**Pourquoi elle existe.** La décision 17 a mis les trois types `mtb_` sur l'**écran d'édition
classique** : aucun bloc ne peut donc être inséré dans une fiche. Or `archive-chien.html` et la page
« La meute » de l'issue #16 ont besoin **exactement de ce rendu**. Sans interface, #16 et #17 le
réécriraient — trois balisages de grille à faire diverger, l'inverse de la contrainte 3.

**Contrat de la fonction, gelé :**

| Point | Engagement |
|---|---|
| Retour | Le balisage du §4, **tel que le visiteur le voit**. N'imprime rien |
| État vide | **Jamais rendu.** Côté visiteur un composant sans contenu ne s'affiche pas (MASTER §9) : la fonction rend `''` |
| Argument inconnu | Retombe sur `tous`. **Jamais un statut inventé** — même assainissement que le bloc, par `statut_demande()` |
| Extension désactivée | À appeler **sous `function_exists()`** |
| Chemin de code | **Un seul**, celui du bloc : la fonction appelle `rendu()` avec `$rendu_de_bloc = false`. Aucune ligne de balisage n'est recopiée |

**Trois choix de forme, à ne pas « corriger »** :

1. **Espace de noms GLOBAL, dans un second bloc `namespace { }`** de `bootstrap.php`, syntaxe à
   accolades. Un thème conforme n'écrit jamais `MTB\` : c'est la frontière **vérifiable par recherche**
   entre le thème et l'extension (contrat #1 §8). PHP n'admet deux espaces de noms dans un fichier
   qu'avec cette syntaxe, et interdit alors tout code hors des accolades — d'où la garde `ABSPATH` dans
   le premier bloc. **Vérifié : les quatre fichiers PHP du module passent `php -l` en 8.1.**
2. **Le nom ne commence pas par `mtb_get_`, et c'est délibéré** : une fonction `mtb_get_*` rend des
   **données**, jamais du HTML. `mtb_grille_chiens_*` est réservé à ce module ; aucun autre ne peut
   l'ombrer.
3. **Le second paramètre de `rendu()` n'est pas un drapeau de confort.**
   `get_block_wrapper_attributes()` lit le bloc en cours dans une propriété statique du cœur : hors du
   rendu d'une instance, elle vaut `null` et la fonction **émet un avertissement PHP**. D'où
   `attributs_conteneur()`, qui compose le conteneur en demandant la classe générée au cœur plutôt qu'en
   la recopiant. **Vérifié : `wp_get_block_default_classname()` existe bien en WordPress 6.9**
   (`wp-includes/block-supports/generated-classname.php:19`), la garde `function_exists()` reste
   correcte, et **les deux chemins produisent le même jeu de classes dans le même ordre** :
   `class="mtb-grille-chiens wp-block-mtb-grille-chiens"`.

> **Honnêteté sur son utilité réelle, à lire avant de s'appuyer dessus.** Dans un thème de blocs, un
> gabarit est un fichier **HTML** et ne peut pas appeler de PHP. Le chemin de réutilisation le plus
> probable pour #16 et #17 est donc d'**insérer le bloc** dans le gabarit :
> `<!-- wp:mtb/grille-chiens {"statut":"reproducteur"} /-->`. Cette fonction sert le cas d'un
> `render.php` de gabarit, d'un motif dynamique ou d'un futur bloc de niveau gabarit. **Les deux chemins
> mènent au même code**, et c'est tout ce qui compte. Elle n'a aujourd'hui **aucun appelant** : c'est
> normal, elle est livrée *pour* #16 et #17.

## 4. Le balisage rendu — littéral, exhaustif, gelé

**C'est la moitié la plus contraignante de ce contrat** : la feuille de style est écrite contre ce
balisage. Aucun style en ligne, aucun attribut visuel, nulle part.

### Mode groupé (le défaut). Mode filtré = le même balisage, avec une seule `<section>`.

> **Ordre réel des deux classes de racine, mesuré : `class="mtb-grille-chiens wp-block-mtb-grille-chiens"`.**
> L'exemple ci-dessous les écrit dans l'autre sens ; c'est l'exemple qui est inexact, pas le code.
> `get_block_wrapper_attributes()` place la classe passée en `extra` **avant** celle que le cœur génère,
> et les deux chemins de rendu (bloc et §3 bis) produisent le même ordre. **Aucun sélecteur du projet ne
> dépend de cet ordre** : c'est une précision de lecture, pas une décision.

```html
<div class="mtb-grille-chiens wp-block-mtb-grille-chiens">
	<section class="mtb-grille-chiens__groupe" data-statut="reproducteur">
		<h2 class="mtb-grille-chiens__titre-groupe">Reproducteurs</h2>
		<ul class="mtb-grille-chiens__liste" role="list">

			<!-- carte avec photo -->
			<li class="mtb-grille-chiens__carte">
				<div class="mtb-grille-chiens__cadre-photo mtb-grille-chiens__cadre-photo--cadrage-centre">
					<img class="mtb-grille-chiens__photo" src="…" srcset="…" sizes="…"
					     width="768" height="1152" alt="…" loading="lazy" decoding="async" />
				</div>
				<h3 class="mtb-grille-chiens__nom">
					<a class="mtb-grille-chiens__lien" href="https://…/chien/nom-dusage/"><span class="mtb-grille-chiens__nom-texte">Nom d'usage</span></a>
				</h3>
			</li>

			<!-- carte sans photo : le cadre EST PRÉSENT et VIDE -->
			<li class="mtb-grille-chiens__carte">
				<div class="mtb-grille-chiens__cadre-photo mtb-grille-chiens__cadre-photo--absente"></div>
				<h3 class="mtb-grille-chiens__nom">
					<a class="mtb-grille-chiens__lien" href="…"><span class="mtb-grille-chiens__nom-texte">Autre nom</span></a>
				</h3>
			</li>

			<!-- carte sans permalien utilisable : AUCUN <a>, le nom est nu -->
			<li class="mtb-grille-chiens__carte">
				<div class="mtb-grille-chiens__cadre-photo mtb-grille-chiens__cadre-photo--cadrage-haut-droite">
					<img class="mtb-grille-chiens__photo" … />
				</div>
				<h3 class="mtb-grille-chiens__nom"><span class="mtb-grille-chiens__nom-texte">Troisième nom</span></h3>
			</li>

		</ul>
	</section>
	<!-- … autres groupes, dans l'ordre gelé du contrat #4 §2 … -->
</div>
```

### Les crochets, un par un

| Crochet | Élément | Toujours présent ? |
|---|---|---|
| `wp-block-mtb-grille-chiens` | racine | oui — ajouté par le cœur |
| `mtb-grille-chiens` | racine, via `get_block_wrapper_attributes()` | oui, **sauf rendu vide côté visiteur : rien du tout** |
| `mtb-grille-chiens__groupe` | `<section>` | 1 à 4 fois |
| `data-statut="<clé>"` | `<section>` | oui. Clé machine. **Le thème n'en dérive JAMAIS un libellé** |
| `mtb-grille-chiens__titre-groupe` | `<h2>` | oui, un par groupe rendu |
| `mtb-grille-chiens__liste` | `<ul role="list">` | oui, un par groupe. **C'est ici que vit la grille CSS, jamais sur la racine** |
| `mtb-grille-chiens__carte` | `<li>` | un par chien. **Le `<li>` EST la carte** — aucun nœud intérieur |
| `mtb-grille-chiens__cadre-photo` | `<div>` | **oui, même sans photo** |
| `…__cadre-photo--cadrage-{haut-gauche\|haut\|centre\|haut-droite\|bas}` | même `<div>` | **quand une photo existe** — les cinq clés de `cadrage['valeur']` |
| `mtb-grille-chiens__cadre-photo--absente` | même `<div>` | quand `photo_principale === null` — **et alors aucun modificateur de cadrage** : il n'y a rien à ancrer |
| `mtb-grille-chiens__photo` | `<img>` | quand une photo existe |
| `mtb-grille-chiens__nom` | `<h3>` | un par chien |
| `mtb-grille-chiens__lien` | `<a>` | **seulement si un permalien utilisable existe** |
| `mtb-grille-chiens__nom-texte` | `<span>` | **oui, toujours** — dans l'`<a>` quand il y en a un, dans le `<h3>` sinon |
| `mtb-grille-chiens--vide` + `mtb-etat-vide` | racine | état vide, **éditeur seulement** |
| **`mtb-etat-vide__nom`** / `mtb-etat-vide__phrase` | deux `<p>` | état vide, **éditeur seulement**. **`__nom` remplace le `__composant` de la rédaction initiale** — crochet gelé pour les six composants du lot, voir §8 |

### Ce qui justifie chaque choix, et ce qu'il ne faut pas « corriger »

- **`<h2>` pour le titre de groupe, `<h3>` pour le nom.** `base.css` habille les éléments **sans
  classe** : le `h2` reçoit le filet double en segment de 6 rem (MASTER §2.1, liste close « sous chaque
  `h2` »), le `h3` la serif 500 en `--t-lg`. Un bloc inséré dans une page dont le `h1` est le titre donne
  `h1 → h2 → h3`. **Aucun réglage de niveau de titre n'est exposé** : c'est un concept technique, absent
  de la colonne « l'éditrice décide » de MASTER §14. Risque tracé : insérée sous un `h3` qu'elle aurait
  tapé, l'ordre saute. Correctif éventuel = un attribut futur, dans le seul dossier du bloc.
- **Le filet double des `h2` de groupe est CONSERVÉ, pas neutralisé.** Les deux garde-fous de §2.1
  tiennent : chaque `h2` ouvre une unité visuelle distincte, et deux filets consécutifs sont séparés par
  une grille entière **plus** le `margin-block-start` du `h2` suivant — très au-delà du `--e-7` exigé.
  Aucun filet n'entre dans une carte.
- **`<ul role="list">`** : le `list-style: none` nécessaire à la grille supprime la sémantique de liste
  sous Safari/VoiceOver. Le `role` explicite est une décision de structure, donc du serveur.
- **Un seul `<a>` par carte, sur le nom d'usage.** L'image n'est **jamais** un lien et n'a **jamais** de
  `tabindex`. Deux liens vers la même fiche doubleraient les arrêts de tabulation (34 sur « La meute »)
  et la verbosité au lecteur d'écran. La cible ≥ 44 px est obtenue par l'étirement CSS (§6).
- **`mtb-grille-chiens__nom-texte` est toujours émis**, même sans lien, pour que la feuille de style
  n'ait qu'une forme à cibler. Dans le cas lié, il permet de remonter le texte au-dessus du calque
  étiré et de **rendre le nom sélectionnable**.
- **Le cadrage passe par une CLASSE, jamais par un style en ligne.** Contrat #1 §8 : « une structure et
  des crochets de classes ».
- **Aucune classe modificatrice par statut** sur la `<section>` (`…__groupe--reproducteur` n'existe pas),
  volontairement : elle inviterait à colorer les groupes, donc à porter une information par la couleur
  seule (MASTER §13, §12.9). `data-statut` suffit aux tests et au CSS structurel.
- **`mtb-photo` n'est PAS émis** — voir l'arbitrage 5 du §11.
- **Aucun filet double sur une carte, aucun médaillon rond** (MASTER §2.1, §2.2) : rien dans le balisage
  ne les rend possibles.

## 5. Ce qu'une carte porte, et ce qu'elle ne porte pas

> **Une carte porte la photo carrée et le nom d'usage. Rien d'autre.**

Ni statut, ni sexe, ni variété, ni date de naissance, ni nom complet, ni badge, ni bouton.

**Pourquoi, et c'est un raisonnement de fidélité, pas de goût** :

- `sexe`, `variete`, `date_naissance` et `nom_complet` valent **`Non renseigné`** quand ils sont vides, et
  le contrat #4 §9 **interdit** de remplacer « Non renseigné » par un tiret, un blanc, « Aucun » ou
  « Néant ». On écrirait donc littéralement « Né le Non renseigné » sous une vignette d'index.
- `date_naissance` porte en outre un `libelle` **accordé au sexe** (`Né le` / `Née le`) : dans une grille
  mixte on obtiendrait une **alternance de libellés** sous les vignettes. Deux bruits pour zéro
  information d'index.
- La carte minimale donne aux 17 chiens **la même silhouette, remplis ou non** — c'est exactement ce que
  MASTER §9.4 demande, et c'est la seule forme qui ne crée pas la contradiction ci-dessus.

**Le statut n'apparaît jamais sur une carte : il est porté par le titre du groupe.** Cela règle d'un
seul coup la redite en mode groupé **et** en mode filtré, et cela évite d'afficher un libellé accordé au
sexe dans une grille où les deux sexes se mélangent (décision 13).

> **Note d'accord en genre, à connaître.** `libelle_statut_pluriel()` fournit **une seule** forme
> plurielle, **invariable** — « Reproducteurs » titre aussi un groupe qui ne contient que des femelles.
> C'est le serveur propriétaire (#4) qui en décide ; le thème et le bloc l'**impriment tel quel**, ne
> l'accordent pas et n'en inventent aucune variante. Rien dans cette issue n'affiche un statut au
> **singulier** sur une fiche de chien, donc la décision 13 n'est ni contredite ni contournée : elle n'a
> simplement aucun point d'application ici. Si le pluriel accordé est un jour voulu, il appartient à #4.

## 6. Ce que la feuille de style du thème habille — et ce qu'elle laisse

`assets/css/blocs/mtb-grille-chiens.css`, dépendance garantie `mtb-jetons`. **Aucune valeur hors des
jetons de `tokens.css`**, à l'exception des trois littéraux justifiés ci-dessous.

| Habillé par la feuille du bloc | Laissé à `base.css` (hors empreinte) |
|---|---|
| La grille sur `__liste` : colonnes, gouttière, retrait de puces, `align-items: start` | La typographie du `h2` de groupe **et son filet double de 6 rem** |
| Le canal large sur `.mtb-grille-chiens` (voir ci-dessous) | Le rapport d'espace `--e-7` / `--e-4` autour du `h2` |
| Le cadre carré : `--r-carre`, fond `--calcaire-creux`, `--cerne-photo` **par pseudo-élément — voir ci-dessous** | La typographie du `h3` et son `--e-5` au-dessus |
| L'image : `object-fit: cover`, point d'intérêt, typographie du texte `alt` de repli | La couleur, le soulignement `--laiton` et le survol du lien (§8.2) |
| Les cinq classes de cadrage → `object-position` | L'anneau de focus du §8.1 **quand `:has()` manque** |
| `position: relative` de la carte et le calque étiré du lien | `img { max-inline-size: 100% }`, `box-sizing`, `hyphens: auto` |
| Le déplacement de l'anneau du §8.1 vers la carte entière | Tout le reste |

**Le canal large est réclamé par la feuille du bloc elle-même**, non par une classe `alignwide` émise par
l'extension. `alignwide` est une classe de **mise en page**, donc interdite au plugin (contrat #1 §8 et
§13) ; et la règle de canal du thème vit dans `base.css`/`theme.json`, hors empreinte. La feuille du
bloc pose donc `max-inline-size: var(--l-large)` et un centrage sur `.mtb-grille-chiens`. Sémantique
correcte et voulue : **quand le bloc est imbriqué dans un conteneur plus étroit, le conteneur gagne** —
la grille se dégrade en colonnes, elle ne casse pas. Le support `align` restant à `false`, Fabienne
n'a **aucun réglage de largeur** (MASTER §14).

### Le cerne photo passe OBLIGATOIREMENT par un pseudo-élément — corrigé à la finition

**Défaut mesuré, puis corrigé.** `tokens.css:118` définit `--cerne-photo` comme une ombre **INTÉRIEURE**
(`inset 0 0 0 1px …`). La feuille la posait en `box-shadow` sur `.mtb-grille-chiens__cadre-photo`, et
`.mtb-grille-chiens__photo` remplit ce cadre **exactement** (`100 %`, `100 %`, `object-fit: cover`) : le
contenu remplacé de l'`<img>` se peint par-dessus l'ombre intérieure du cadre, et **le cerne était
invisible dès que la photo chargeait**. MASTER §6.6 l'exige sur **toute** photo — il ne l'était sur
aucune. La même mesure a été faite indépendamment par une chaîne sœur sur `.mtb-photo`.

**Recette en vigueur, dite « V9 », identique à celle de `mtb-fiche-information.css` (#7) :**

```css
.mtb-grille-chiens__cadre-photo       { position: relative; /* référent du ::after, et rien d'autre */ }
.mtb-grille-chiens__cadre-photo::after {
  content: ""; position: absolute; inset: 0;
  border-radius: inherit; box-shadow: var(--cerne-photo); pointer-events: none;
}
```

Quatre points qu'il ne faut pas « simplifier » :

- **`pointer-events: none` est obligatoire.** Le pseudo couvre toute la photo et intercepterait sinon le
  clic du calque étiré du lien, qui rend la carte entière cliquable.
- **Le pseudo n'est PAS scopé au cas photographié** : le cerne existe aussi sur le `<div>` vide de
  `--absente` et quand la photo échoue. Même silhouette avec ou sans photo (§7, MASTER §9.4).
- **Le `position: relative` du cadre ne casse pas l'ancrage du calque du lien.** Le calque se résout sur
  le plus proche **ancêtre** positionné de l'`<a>` — chaîne `<a>` → `<h3>` → `<li>.__carte`. Le cadre est
  un **frère** du `<h3>`, donc jamais dans cette chaîne. **Vérifié en navigateur** par
  `elementFromPoint()` au centre de chaque cadre : chaque sonde rend le lien de **sa** carte, avec son
  propre `href`. La panne nommée dans la feuille — toute la grille menant à un seul chien — n'a pas lieu.
- **Aucun `overflow: hidden`** sur le cadre : MASTER §7.8 l'interdit sur un conteneur de texte, et ce
  cadre porte le texte de remplacement de l'image.

**Vérifié en navigateur, dans la toile de l'éditeur** : `box-shadow` du cadre = `none`, `::after` =
`rgba(22,36,28,.22) 0 0 0 1px inset` avec `pointer-events: none`, et le cerne **visible à l'œil sur une
photo BLANC PUR** injectée dans le DOM vivant — le pire cas de §6.6, « photo pâle, ciel blanc ».
`content: ""`, `inset: 0`, `border-radius: inherit` et `pointer-events: none` **ne sont pas des valeurs
de design** : la liste close des littéraux ci-dessous est inchangée.

> **Cette recette n'est PAS spécifique à ce bloc.** Elle vaut pour toute boîte photo dont l'`<img>`
> remplit exactement le cadre — c'est-à-dire tous les composants à photo du catalogue. Dette **T-#14-g**,
> aggravée d'un exemplaire : à hisser dans un crochet mutualisé du thème.

### Les trois seuls littéraux autorisés dans la feuille, et leur justification

| Littéral | Où | Justification |
|---|---|---|
| **`14rem`** | piste minimale de `minmax()` | **Justification amendée le 2026-08-17, la première était fausse — voir §13, point 1.** La grille rend **2 colonnes de 272 px**, jamais 4 : le canal large est inatteignable. Ce qui fait tenir `14rem` malgré cela, et qui est **mesuré** : dans le canal réellement obtenu (canal texte, 36 rem = 576 px), `14rem` donne **2 colonnes de 272 px** là où `9rem` en donnerait **3 de 186 px**. Sur un site dont BRIEF §10 fait des photographies la matière première, 272 px valent mieux que 186 px — et à 360 px, `14rem` donne **1 photo pleine largeur de 324 px** au lieu de deux vignettes de 146 px, en supprimant au passage la coupure d'un nom de 28 px dans 146 px. La décision survit donc à l'effondrement de son argument d'origine, mais **pour une autre raison, et il faut lire celle-ci.** **Valeur absente de `tokens.css` — signalée à la chaîne design, réversible en un littéral.** |
| **`50% 38%`** | `--point-interet` par défaut, et cadrage `centre` | **Recopié de MASTER §6.2**, qui en donne la justification chiffrée (« sur une photo de chien en pied, la tête est au-dessus du centre géométrique »). |
| **`(min-width: 37.5em) 14rem, 45vw`** | valeur de `sizes` | Dérivée de la piste réelle ci-dessus. Voir §7. |

### `centre` vaut `50% 38%`, pas le centre géométrique — arbitrage rendu

MASTER §6.2 fait de `50% 38%` le **défaut justifié** ; le contrat #4 §4 fait de `centre` le **choix par
défaut de la liste**, avec le libellé « Centre ». Interpréter `centre` géométriquement
**enterrerait le défaut de MASTER sur les 17 fiches d'un coup**. Le cadrage `centre` est donc mappé sur
`50% 38%`, et `haut` reste distinct (`0 %` en vertical) : les cinq choix restent discernables.

> **Conséquence pour la fiche d'aide, à respecter** : « Centre » signifie **« le cadrage par défaut,
> qui garde la tête du chien »**, pas « le centre géométrique de la photo ». La fiche ne promet jamais un
> centre géométrique.

### Accessibilité — gelé

- **L'anneau de focus est celui de MASTER §8.1, un seul, recopié**, et **tiré sur la carte entière** par
  `.mtb-grille-chiens__carte:has(.mtb-grille-chiens__lien:focus-visible)`, dans un
  `@supports selector(:has(*))`.
  - `:has(… :focus-visible)` et **jamais** `:focus-within`, qui s'allumerait aussi au clic souris —
    MASTER §8.1 : « le survol n'utilise **jamais** d'anneau ».
  - La neutralisation de l'anneau propre au lien est **enfermée dans le même `@supports`** : sans
    `:has()`, le lien conserve l'anneau de `base.css`. **Jamais d'`outline: none` sans remplacement
    conforme** (MASTER §13).
  - §12.8 a déjà prouvé cet anneau à **≥ 3,77:1 sur n'importe quel fond, photographie comprise** : la
    preuve est réutilisée, aucune paire n'est recalculée.
- **Aucune information n'est portée par la couleur seule** (§12.9) : le statut est **du texte** dans le
  titre de groupe, le lien porte un **soulignement permanent** (§8.2 — il n'est jamais retiré, même sur
  un titre de vignette), l'absence de photo est signalée par l'absence de photo.
- **Aucun badge, aucune pastille.** Les trois pastilles de MASTER §3.3 concernent la **disponibilité
  d'une portée**, pas le statut d'un chien : elles ne sont pas réemployées ici.
- **Aucune troncature à `…` sur un nom de chien** (§9.4) : `overflow-wrap: break-word`.
- **`auto-fill`, jamais `auto-fit`** : §9.4 exige qu'« un seul chien dans une grille de 4 colonnes garde
  sa largeur de colonne et reste à gauche ». `auto-fit` l'étirerait sur toute la ligne. C'est un piège
  nommé.
- **Aucune requête média**, aucune hauteur fixe, aucun `overflow: hidden`, aucune taille en `vw` seul —
  §7.7 (360 px sans défilement horizontal) et §7.8 (zoom 200 %) tenus par une grille intrinsèque.
- **Aucun effet de survol sur la photo** : ni zoom, ni assombrissement, ni `filter` (interdit §6.6 et
  §13 — il falsifierait la robe), ni ombre, ni transition (**aucun jeton de durée n'existe** et le
  contrat #2 interdit d'en inventer un). Le survol est déjà signalé, gratuitement, par `a:hover` de
  `base.css`, sur toute la surface de la carte puisque le calque appartient au lien.
- **Aucune règle `@media print`** : le « §9.6 » auquel MASTER §7.6 renvoie **n'existe pas** dans le
  document, et le contrat #2 l'a déjà consigné. Écrire une règle d'impression ici serait une invention
  visuelle. Rien ne casse sans elle. Une feuille d'impression doit être **globale**, pas dix règles
  recopiées dans dix feuilles de blocs.
- **La vignette est une vignette, pas une carte** : ni fond, ni rembourrage, ni bordure sur `__carte`.
  `--blanc` sur `--calcaire` fait **1,18:1** — invisible — et forcerait à inventer un rembourrage de
  carte. La « carte chien » de MASTER §3.2 désigne la carte parent de §7.4, qui porte du contenu.

## 7. La photo, et le poids des images

Rendue par **`wp_get_attachment_image()`**, jamais par une `<img>` composée à la main.

- **Taille demandée : `medium_large`** (768 px, non rognée, enregistrée d'origine par WordPress).
  **Aucun `add_image_size()`** : c'est global, hors empreinte, et cela ne s'appliquerait **pas aux photos
  déjà téléversées** — donc à la reprise de l'ancien site, la taille n'existerait sur aucune image.
- **Le rognage carré est fait par le CSS** (`--r-carre` + `object-fit: cover`), jamais par une taille
  d'image. **Piège à connaître par toute issue future** : une taille recadrée en dur
  (`add_image_size( …, 400, 400, true )`) produirait un carré **déjà rogné au centre par WordPress**, ce
  qui **annulerait en silence le choix de cadrage de Fabienne**. La taille servie doit être
  **redimensionnée, pas recadrée**.
- **`loading="lazy"` et `decoding="async"` sont passés explicitement**, alors que le cœur les ajouterait
  souvent seul : depuis WordPress 6.3, `wp_get_loading_optimization_attributes()` peut décider que la
  première grande image du contenu est la candidate LCP et lui poser `loading="eager"` **et
  `fetchpriority="high"`**. La première carte de la grille est exactement ce cas. MASTER §6.9 impose
  `loading="lazy"` hors bandeau. **`fetchpriority="high"` ne doit apparaître sur aucune image de la
  grille** — à vérifier dans le HTML rendu.
- **`class` : notre valeur remplace intégralement celle du cœur.** Les classes
  `attachment-medium_large size-medium_large` **n'apparaîtront pas** ; la feuille de style ne doit pas
  les attendre.
- **Le texte alternatif est passé BRUT.** `wp_get_attachment_image()` applique déjà `esc_attr()` à chaque
  attribut : un `esc_attr()` de notre côté produirait un **double échappement visible** (`d&#039;Ulysse`).
  **Ne jamais pré-échapper l'`alt`.**
- **L'`alt` n'est jamais fabriqué, jamais complété, jamais reformaté** (contrat #4 §7 et §9). Il vient de
  `photo_principale['alt']`, que la fonction propriétaire a déjà rempli — description saisie dans la
  médiathèque, sinon le repli composé par le serveur propriétaire. **Si `alt` vaut `''`, on passe
  `'alt' => ''`** — donc `alt=""`, image décorative — et on ne laisse pas le cœur en chercher un autre.
  Conséquence assumée et bonne : la carte reste entièrement nommée par son `<h3><a>`, aucune information
  n'est perdue, et une image décorative doublant un titre adjacent est le comportement correct.
- **`sizes` passe par le filtre `mtb_grille_chiens_tailles_image`** (chaîne). `sizes` est une valeur de
  **mise en page**, donc du thème ; le filtre est le seul point d'extension du module et **le levier de
  D8 sur cette page**. **Défaut gelé : `(min-width: 37.5em) 14rem, 45vw`** — dérivé de la piste réelle
  du §6, et non d'une valeur prudente. Sans `sizes` correct, le cœur écrirait
  `(max-width: 768px) 100vw, 768px` et la page téléchargerait 17 images de 768 px pour des vignettes de
  248 px.
- **Le décalage de mise en page est nul, et il ne dépend pas du serveur** : c'est l'`aspect-ratio` du
  **cadre** qui réserve la boîte, avant même que l'image existe.

### Photo manquante — MASTER §9.2, avec son écart assumé

```html
<div class="mtb-grille-chiens__cadre-photo mtb-grille-chiens__cadre-photo--absente"></div>
```

Un `<div>` **vide, sans enfant** : ni pictogramme d'appareil photo, ni silhouette, ni image générique
(MASTER §9.2 et §13). La feuille lui donne son ratio, son fond `--calcaire-creux` et son cerne — donc
**la même silhouette** qu'une carte avec photo (§9.4). L'emplacement est **rendu, jamais supprimé**.

**Deux dispositifs de §9.2 sont délibérément retirés sur une vignette d'index** :

1. **Le filet double en bord bas** — MASTER §2.1 **interdit** le filet double sur une carte ou une
   vignette de galerie. §2.1 gagne.
2. **Le nom d'usage au centre de l'emplacement** — sur une carte, le nom est déjà dans le `<h3>` juste
   dessous : le répéter le ferait **lire deux fois** par un lecteur d'écran, à l'intérieur de la zone
   cliquable, et remplirait la vignette de texte.

Le cas visé par §9.2 est le **portrait d'une fiche**, où aucun nom n'est adjacent ; une vignette d'index
n'y est pas nommée. Elle en hérite le ratio, le fond et le cerne, et laisse tomber les deux dispositifs
qui servaient à **nommer un emplacement anonyme**. **Arbitrage signalé à la chaîne design, pas passé en
douce** : si elle nous contredit, le crochet à ajouter est un `<span
class="mtb-grille-chiens__cadre-photo-nom">` et c'est un **amendement de ce contrat**.

### Source de mauvaise qualité — MASTER §6.6

| Cas | Traitement |
|---|---|
| Photo pâle, ciel blanc | `--cerne-photo` sur le cadre, donc sur **toutes** les vignettes |
| Photo sombre | **aucune correction** — `filter:` est interdit et falsifierait la robe |
| PNG détouré | fond `--calcaire-creux` du cadre. Ni damier, ni trou |
| Photo qui ne charge pas | le cadre garde ratio et fond ; le texte `alt` s'affiche en `--texte-doux` / `--t-sm` (**5,79:1 sur `--calcaire-creux`**, §12.3). **Aucun pictogramme ajouté** |
| Photo de trop basse définition | **non traité, et c'est nommé.** §6.6 exige « pas au-delà de la largeur naturelle × 1,5 » : **le CSS n'a aucun accès à la largeur intrinsèque d'une image en `cover`**. Atténuation réelle : la piste plafonne la vignette à ≈ 248 px, la règle ne mordrait que sous ≈ 165 px de source — inutilisable de toute façon. Dette **T-#14-f** |

## 8. États spéciaux

| État | Émis par le serveur | Rendu |
|---|---|---|
| `donnee_absente` | **jamais émis par ce bloc** : aucun champ susceptible de valoir « Non renseigné » n'est affiché (§5) | — |
| **groupe vide** | `mtb_get_chiens_par_statut()` **ne renvoie pas le groupe** (contrat #4 §2) | rien à faire : ni `<section>`, ni titre. **Jamais de titre orphelin** |
| **statut choisi sans chien** | le groupe n'est pas dans le retour → le filtrage ne trouve rien | **état vide** (ci-dessous) |
| **aucun chien n'a de statut** | `mtb_get_chiens_par_statut()` rend `array()` | **état vide** (ci-dessous) |
| `page_protegee` | **la fiche est absente de la grille** : la fonction propriétaire l'exclut à la source (`lecture.php:167`, `'has_password' => false`) | rien. **Aucune fuite, et le préfixe « Protégé : » du cœur n'atteint jamais la grille.** La dette T8 n'est ni aggravée ni soldée |
| **fiche sans permalien utilisable** | `get_permalink()` rend `false`, `''`, ou une URL qu'`esc_url()` refuse | le `<h3>` contient le nom **nu**. **Jamais `href=""`, jamais `href="#"`, jamais un `<a>` sans destination** |
| **`query/chien` désactivé** (`mtb_get_chiens_par_statut()` absente) | — | le bloc rend `''` **dans les deux contextes**. Pas d'état vide : sa phrase serait un mensonge, le problème n'étant pas l'absence de statut. Aucune erreur, aucune notice (D12) |

### L'état vide, deux publics distincts

**Le visiteur : rien du tout.** `rendu()` renvoie `''` — pas de conteneur, pas de `<section>`, pas de
titre, **pas même le `<div>` du wrapper**. Règle transverse de MASTER §9 : côté public, un composant sans
contenu ne s'affiche pas.

**Fabienne dans l'éditeur : l'apparence de MASTER §9.1.**

```html
<div class="mtb-grille-chiens mtb-grille-chiens--vide mtb-etat-vide wp-block-mtb-grille-chiens">
	<p class="mtb-etat-vide__nom">Grille de chiens</p>
	<p class="mtb-etat-vide__phrase">Ce bloc n'affiche rien tant qu'aucune fiche de chien publiée n'a de statut.</p>
</div>
```

**Les deux phrases exactes, gelées — REFORMULÉES ET REDISTRIBUÉES à la finition.** Ce ne sont plus deux
phrases choisies sur le réglage, mais **deux états distincts de la base**, et c'est la correction de
fond :

| État réel | Phrase | Réglage concerné |
|---|---|---|
| **Aucune fiche de chien publiée ne porte de statut** — la base est à remplir | **« Ce bloc n'affiche rien tant qu'aucune fiche de chien publiée n'a de statut. »** | **tous les réglages**, y compris un statut choisi |
| **Des fiches portent un statut, mais aucune celui qui est choisi** | **« Ce bloc n'affiche rien tant qu'aucune fiche de chien publiée ne porte le statut « Retraité ». »** | un statut choisi, seulement |

- **Pourquoi la distinction compte.** L'ancienne règle choisissait sur le seul réglage : avec un statut
  choisi et une base vide, elle affirmait « aucune fiche ne porte le statut « Retraité » » alors que le
  vrai problème était qu'**aucune fiche ne porte de statut du tout**. Vrai à la lettre, trompeur en
  pratique, et elle envoyait Fabienne chercher le mauvais geste.
- **Le second état n'est PAS une erreur de l'éleveuse.** Préparer une grille de retraités avant qu'un
  chien le soit est légitime ; la phrase le constate, elle ne le reproche pas.
- **Aucune des deux phrases ne présume le genre des chiens** (décision 13). Le sujet grammatical est
  « aucune fiche de chien publiée » — l'accord porte sur la **fiche**. Le statut est un **libellé cité
  entre guillemets français**, jamais un adjectif accordé à un chien : ni « Retraitée », ni « Retraité(e) ».
- Le départage se lit sur le **tableau de groupes déjà rendu** par la fonction propriétaire
  (`array() !== tous_les_groupes()`). **Aucun chien n'est compté, aucune requête n'est refaite** : ce
  serait interroger un type que le module ne possède pas (décision 19). Un seul appel, mémorisé, sert le
  filtrage **et** ce départage.
- **Les trois branches sont vérifiées en exécution** : base sans statut + réglage `tous` → phrase 1 ;
  base sans statut + réglage `retraite` → **phrase 1** ; base avec des statuts + réglage `retraite` →
  phrase 2, « … ne porte le statut « Retraité ». ».
- Elle est **actionnable** : elle nomme les deux conditions, publier **et** donner un statut, et elle
  recoupe exactement l'avis d'enregistrement que le contrat #4 §8 affiche déjà quand une fiche est
  enregistrée sans statut.
- Le nom du statut au **singulier** vient de **`libelle_statut( $cle, '' )`** — la forme masculine
  canonique de MASTER §10.2, fournie par le serveur propriétaire. Ni accord, ni pluriel détourné en
  singulier, ni invention. **Guillemets français.**
- Forme de §9.1 respectée : ligne 1 = le nom du composant, ligne 2 = « Ce bloc n'affiche rien tant
  que… ». Le mot « bloc » est **prescrit par §9.1 lui-même** ; §10.4 n'interdit que « bloc réutilisable ».
  *fiche*, *chien*, *publiée*, *statut* sont tous de §10.2/§10.3.
- **L'étiquette est écrite en casse normale.** Les majuscules de §9.1 sont un `text-transform` de
  `editor.css` : les mettre en dur en PHP serait une décision visuelle, et un lecteur d'écran
  **épellerait**.
- **Les classes `mtb-etat-vide*` sont PARTAGÉES et restent NUES, jamais scopées sous une classe de bloc**
  — arbitrage du lot entier, et c'est délibéré. MASTER §9.1 exige **une seule apparence pour les dix
  composants** du catalogue. Une classe `mtb-grille-chiens__etat-vide` la rendrait **mécaniquement
  impossible** et garantirait neuf copies divergentes — la dette T9 en dix exemplaires. Nues, elles
  pourront être hissées dans une feuille partagée **sans renommage**. Le modificateur scopé `--vide`
  reste sur la racine pour qu'un ajustement propre à ce bloc soit possible plus tard, **jamais pour
  reproduire l'apparence commune**. **Arbitrage assumé contre la lettre de la convention
  `mtb-<bloc>__<element>` du contrat #1 §8** : la contrainte de §9.1 est plus forte.
- **Les trois crochets sont GELÉS pour le lot** : `.mtb-etat-vide`, **`.mtb-etat-vide__nom`**,
  `.mtb-etat-vide__phrase`. La rédaction initiale écrivait `__composant` ; **le lot a retenu `__nom`**, que
  cinq composants frères émettent aussi. Le module les **émet**, l'issue #6 en **livre l'apparence** dans
  `editor.css` : ce module ne la redéfinit jamais.
- **T-#14-c : SOLDÉE.** L'apparence vit bien dans `editor.css`, hors empreinte, et **elle est désormais
  livrée** par #6 (`.mtb-etat-vide`, `.mtb-etat-vide__nom`, `.mtb-etat-vide__phrase`). **Vérifié dans la
  toile** : fond `--calcaire-creux`, filet tireté `--laiton`, rembourrage `--e-6`, et
  `text-transform: uppercase` sur `__nom` — les majuscules de §9.1 sont donc bien obtenues **par le CSS**
  et non écrites en dur en PHP, exactement comme la puce suivante l'exigeait. **MASTER §9.1 est
  satisfait.**

### Comment le contexte éditeur est distingué du contexte visiteur

```php
defined( 'REST_REQUEST' ) && REST_REQUEST && current_user_can( 'edit_posts' )
```

L'aperçu d'un bloc à rendu serveur passe par la route REST `/wp/v2/block-renderer/mtb/grille-chiens`,
et pendant une requête REST `REST_REQUEST` vaut `true`. La capacité garantit qu'un visiteur anonyme ne
l'atteint jamais.

**Fiable suffisamment, pas parfaitement — dit honnêtement** :

- **`is_admin()` ne marche pas** : il vaut `false` pendant une requête REST. C'est la réponse naïve, et
  elle est fausse.
- **Faux positif possible** : une éditrice connectée qui récupère une Page par REST (`content.rendered`)
  obtiendrait le balisage d'état vide dans la réponse. Conséquence nulle — du balisage sans style, jamais
  vu par un visiteur.
- **Faux négatif possible** : un futur contexte d'aperçu qui ne passerait pas par REST n'afficherait pas
  l'état vide. Il n'en existe aucun aujourd'hui.

> **Couplage à écrire dans le fichier, sinon quelqu'un le cassera dans six mois** : l'aperçu
> `ServerSideRender` **est** le mécanisme qui produit la requête REST. Retirer l'aperçu ferait
> disparaître l'état vide de l'éditeur **en silence, sans erreur**.

## 9. Chaînes fournies par le serveur

Le thème et la feuille de style les **impriment** ; ils ne les composent jamais.

Les **quatre titres de groupes au pluriel** (`libelle_statut_pluriel()`) · les **cinq libellés du
sélecteur** · le **libellé de statut au singulier** de la phrase d'état vide (`libelle_statut( $cle, '' )`)
· les **noms d'usage** · les **textes alternatifs** des photos · l'étiquette et la phrase d'état vide ·
l'étiquette du réglage **« Statut à afficher »** et son texte d'aide.

> **`editeur.js` ne contient AUCUN texte français.** Pas un libellé, pas une étiquette, pas un titre de
> panneau. Tout arrive par `wp_localize_script()` dans l'objet `mtbGrilleChiens`, construit **en PHP**
> depuis `content/chien/choix.php`. **Un « Reproducteurs » écrit dans `editeur.js` est un rejet**, et
> c'est vérifiable par un simple `grep`.

Le pont vers le vocabulaire propriétaire est un `require_once` de
`MTB_CORE_DIR . 'includes/content/chien/choix.php'` précédé d'un **`is_readable()`** — un `require_once`
d'un fichier absent est un `E_COMPILE_ERROR` que le `try/catch` du chargeur **n'attrape pas** (contrat #1
§12). Précédent établi : `query/chien/bootstrap.php:25`. Si `choix.php` ou ses fonctions sont absents :
le sélecteur ne rend que le premier choix, tout attribut retombe sur `tous`, **aucune erreur, aucune
notice, aucun statut inventé**.

## 10. Interdits

- Le thème et la feuille de style **n'interrogent jamais la base** : aucun `WP_Query`, `get_post_meta`,
  `get_posts`, `get_terms`, ni `MTB\` dans `wp-content/themes/mtb/`.
- Le thème **ne compose jamais une chaîne du domaine**, n'accorde jamais un statut, **et ne dérive jamais
  un libellé de `data-statut`**.
- L'extension **n'émet aucune règle visuelle ni mise en page** : aucun style en ligne, aucune classe de
  mise en page (`alignwide` compris), aucune couleur, aucune dimension.
- **Le module ne modifie aucun fichier de `includes/query/` ni de `includes/content/`**, et ne
  réimplémente **aucune** requête sur `mtb_chien` (décision 19). Le filtrage porte sur le tableau de
  groupes **déjà rendu** par la fonction propriétaire.
- **Le module ne déclare AUCUNE fonction de lecture globale** — aucun `mtb_get_*`, aucun nom qu'une
  chaîne sœur ou `includes/query/` pourrait revendiquer. **Interdit réécrit à la finition** : la forme
  d'origine (« aucune fonction `mtb_*` globale ») est remplacée par celle-ci, parce que le module expose
  désormais **une** fonction globale, `mtb_grille_chiens_rendu()` (§3 bis). Elle rend du **HTML**, pas des
  données ; elle est gardée par `function_exists()` ; et le préfixe `mtb_grille_chiens_` est réservé à ce
  module. **Toute autre fonction globale reste interdite ici**, et une nouvelle demanderait un amendement
  de ce contrat.
- **`render.php` ne déclare AUCUNE fonction.** WordPress l'inclut **une fois par instance de bloc** :
  deux blocs sur la même page donneraient `Cannot redeclare function …`, un `E_COMPILE_ERROR` hors de
  toute portée de `try/catch` — **site par terre**. Il contient une garde `ABSPATH` et **une seule**
  instruction de sortie.
- **`bootstrap.php` n'inclut jamais `render.php`.**
- **Aucune étape de build**, aucun `npm`, aucun JSX, aucun dossier `build/`, aucune dépendance.
- **Aucune fonction i18n nulle part** (contrat #1 §7). `"editorScript"` porte une **poignée**, jamais
  `"file:./editeur.js"` — WordPress chercherait un `editeur.asset.php` et émettrait un
  `_doing_it_wrong`.
- **Aucun appel HTTP sortant, aucune origine tierce, aucun traceur, aucun cookie.**
- Aucun `wp_cache_set()` pour mémoriser les groupes — voir l'arbitrage 8.
- **Aucun fait d'élevage inventé** : aucun nom de chien, date, affixe, numéro LOF, résultat de test ou de
  concours n'apparaît dans le code, dans le CSS, dans le JavaScript ou dans la fiche d'aide.

## 11. Arbitrages — les désaccords entre les deux plans, et leur règlement

| # | Désaccord | Décision | Raison |
|---|---|---|---|
| **1** | **Nom des trois éléments de la photo.** Le plan serveur nommait `__photo` le `<div>` et `__image` l'`<img>` ; le plan thème nommait `__cadre-photo` le `<div>` et `__photo` l'`<img>` | **`__cadre-photo` pour le `<div>`, `__photo` pour l'`<img>`** (le plan thème) | **C'est le défaut classique du travail en parallèle, et il était là.** `__photo` désignait **deux éléments différents** selon la moitié : la feuille aurait habillé un `<div>` avec les règles d'une image, silencieusement, sur un site répondant 200. Le mot « cadre » dit sans ambiguïté qu'il s'agit du conteneur ; `__photo` sur un `<div>` qui contient un `__image` se lit à l'envers. |
| **2** | **Forme des cinq classes de cadrage** : `__photo--centre` contre `__cadre-photo--cadrage-centre` | **`mtb-grille-chiens__cadre-photo--cadrage-<clé en tirets>`** | L'infixe `--cadrage-` distingue sans effort un cadrage de `--absente`. **Et les tirets sont impératifs** : le geste naturel côté serveur est `'…--' . $cadrage`, qui produirait `--cadrage-haut_gauche` — **deux cadrages sur cinq muets**, la photo retombant sur le défaut, en silence. Le remplacement `_` → `-` puis `sanitize_html_class()` est obligatoire. |
| **3** | **Photo absente : `--absente` ou `--vide` ?** | **`--absente`** | Aligné sur le vocabulaire gelé du projet : MASTER §9.3 s'intitule « Donnée absente » et l'état du contrat #1 §9 s'appelle `donnee_absente`. Et `--vide` est déjà pris par l'état vide du bloc entier : le même mot pour deux choses différentes est le prochain arbitrage 1. |
| **4** | **Titre de groupe : `__titre` ou `__titre-groupe` ?** | **`__titre-groupe`** | Explicite. Le bloc n'a qu'un type de titre aujourd'hui ; il en aura peut-être deux demain. |
| **5** | **La classe utilitaire partagée `mtb-photo` est-elle émise par le plugin ?** (MASTER §6.2 écrit son sélecteur `.mtb-photo > img`) | **Non. Le plugin n'émet que `mtb-<bloc>` et `mtb-<bloc>__<element>`** | Le contrat #1 §8 est littéral sur ces deux formes, et une classe utilitaire partagée est un objet **du thème**. La feuille scope donc son traitement photo à `.mtb-grille-chiens__cadre-photo`. **Coût assumé et tracé** : le prochain composant à cadre photo recopiera la règle de §6.2 — dette **T-#14-g**, à hisser dans un crochet mutualisé du thème **avant qu'elle existe en cinq exemplaires**. C'est une décision à prendre **une fois pour tous les composants**, pas bloc par bloc. **Amendement de finition, à remonter au lot** : l'arbitrage du lot veut désormais que `.mtb-photo` reste une classe **nue**, jamais scopée sous une classe de bloc, précisément pour pouvoir être hissée **sans renommage**. Ce module, lui, **n'émet pas `.mtb-photo` du tout** — il émet `__cadre-photo`. La décision reste bonne au regard du contrat #1 §8, mais elle a une conséquence qu'il faut dire : **le hissage que le lot prépare ne couvrira pas ce composant**, qui devra être repris à la main. Point d'attention du lot, pas une dérive à corriger ici : changer les classes émises modifierait le balisage gelé du §4. |
| **6** | **Canal large : `alignwide` émis par le plugin, ou règle de canal du thème ?** | **Décision maintenue, mais elle N'ATTEINT PAS son but — voir §13, point 1.** La feuille pose `max-inline-size: var(--l-large)` sur `.mtb-grille-chiens`, et **ce plafond est inerte** : le bloc n'est jamais enfant direct de `.mtb-canal`, donc il hérite du canal **texte** (36 rem). Le canal large exigerait `grid-column: large-debut / large-fin` sur un enfant direct de `.mtb-canal`, ce que le bloc ne peut pas être | `alignwide` est une classe de **mise en page**, interdite au plugin (contrat #1 §8 et §13) ; et la règle de canal du thème vit dans `base.css`/`theme.json`, **hors empreinte des six chaînes du lot**. Aucune des deux moitiés n'avait vu la troisième voie, qui est légitime — la feuille **est** du thème — et qui **supprime la dette** au lieu de la reporter. Imbriqué dans un conteneur plus étroit, le conteneur gagne : dégradation, pas casse. |
| **7** | **Piste minimale de la grille : `9rem` ou `14rem` ?** Le plan thème livrait `9rem` et remontait la question à la chaîne design | **`14rem`** | **MASTER §9.4 est le seul endroit du document qui chiffre cet objet** — il parle d'« une grille de **4 colonnes** » pour une grille de chiens — et `14rem` est la seule valeur qui la produit. Le `9rem` du §6.7 chiffre une **galerie** : objet différent, gouttière différente. Emprunter le nombre d'un autre objet est le plus faible des deux appuis. Six vignettes de 155 px sous un nom de 28 px est un rapport que le plan thème jugeait lui-même « discutable » sur un site dont la matière première est la photographie. **Contrepartie assumée : 1 colonne à 360 px** — une photo de 324 px de large, ce qui sert BRIEF §10 mieux que deux vignettes de 146 px, et supprime la coupure d'un nom long. Valeur absente de `tokens.css`, **signalée à la chaîne design, réversible en un littéral**. |
| **8** | **Mémorisation des groupes entre deux instances du bloc** | **Variable statique de fonction. Jamais `wp_cache_set()`, jamais de transient** | Sur une installation dotée d'un cache objet **persistant** (Redis, Memcached), `wp_cache_set()` avec expiration 0 **survit à la requête** : la grille resterait périmée après qu'elle a modifié un chien. C'est exactement l'échec que le contrat #1 §9 reproche aux transients, obtenu par une autre porte. Une statique de fonction ne franchit jamais la limite de la requête et n'est pas une variable globale. |
| **9** | **Défaut de `sizes`** : `(min-width: 48rem) 22vw, 45vw` (serveur, prudent) contre une valeur dérivée de la grille réelle (thème) | **`(min-width: 37.5em) 14rem, 45vw`**, dérivée de la piste du §6 | Le filtre `mtb_grille_chiens_tailles_image` ne peut être appliqué par **personne dans ce lot** (`functions.php` est hors empreinte) : **le défaut EST la valeur livrée**. Un défaut prudent-et-faux aurait laissé la dette D8 ouverte sans que rien ne la signale. Le défaut est donc correct par construction, et la dette T-#14-d est **close au gel**, pas reportée. |
| **10** | **Classes d'état vide : scopées au bloc, ou partagées ?** | **Les deux, avec l'apparence portée par les classes partagées** | Détail et motif en §8. §9.1 (une seule apparence pour dix composants) est une contrainte plus forte que la convention de nommage de #1 §8. |
| **11** | **Le `<li>` est-il la carte, ou faut-il un nœud intérieur ?** | **Le `<li>` EST la carte** | Les deux plans convergeaient. Un nœud de moins × 17, et c'est le `<li>` que la feuille pose en `position: relative` pour le calque étiré. |
| **12** | **Le nom d'usage est-il répété au centre d'un emplacement photo vide** (lettre de MASTER §9.2) ? | **Non**, et le filet double de §9.2 est retiré lui aussi | Les deux plans convergeaient, pour la même raison. Détail et remontée à la chaîne design en §7. |
| **13** | **`<span class="__nom-texte">` autour du texte du nom** : demandé par le thème, non planifié par le serveur | **Émis, et toujours** — y compris sans lien | Coût d'un `<span>`, gain réel : le nom redevient **sélectionnable** malgré le calque étiré. Toujours émis pour que la feuille n'ait qu'une forme à cibler. **Honnêteté** : un glissement démarré sur du texte de lien déclenche un glisser-déposer dans Chrome — le gain est **partiel** et vérifiable seulement en navigateur. |
| **14** | **Fiche protégée par mot de passe dans la grille** (question du plan thème) | **Sans objet : elle en est absente à la source** | Vérifié dans le code propriétaire, `lecture.php:167` : `'has_password' => false`. Le préfixe « Protégé : » du cœur n'atteint donc jamais la grille, et rien ne fuit. La dette **T8** n'est ni aggravée ni soldée par cette issue. |
| **15** | **Catégorie de blocs `mtb`** | **PÉRIMÉ. Décision en vigueur : `"category": "mtb"` seul, aucun filtre.** La rédaction d'origine — un filtre `block_categories_all` idempotent dans le `bootstrap.php` du module — était le garde-fou d'un temps où `categorie-mtb/` n'existait pas | `categorie-mtb/` est livré par #6 et vérifié en exécution : la catégorie est déclarée **une fois pour tout le catalogue**, comme le contrat #1 §10 l'exigeait. Garder le filtre en faisait une **déclaration en double**, la forme même de la dette T9. Détail, mode de panne d'origine et vérification en §3. Dette T-#14-b **soldée**. |
| **16** | **`RadioControl` ou `SelectControl` pour le réglage ?** | **`RadioControl`, `initialOpen: true`** | Les cinq choix sont visibles sans rien déplier — même argument que l'arbitrage 1 du contrat #4 (« un champ jamais déplié est un champ jamais rempli »), **même contrôle que le champ Statut de la fiche Chien** qu'elle connaît déjà, cinq cibles au lieu d'une liste déroulante, et lisible à 200 % de zoom. |

## 12. Points restés ouverts — ni comblés, ni oubliés

- **~~La feuille de bloc n'atteint pas l'iframe de l'éditeur.~~ Dette T-#14-h : SOLDÉE par l'issue #6.**
  Le diagnostic écrit ici était **incomplet, et sa conclusion fausse**. La cause réelle, mesurée par une
  chaîne sœur : les feuilles de blocs sont mises en file avec `'deps' => array( 'mtb-jetons' )`, or
  `mtb-jetons` n'est enregistré que par `mtb_feuilles_du_site()` sur `wp_enqueue_scripts`, **qui ne se
  déclenche jamais en administration** — et `WP_Dependencies::all_deps()` **abandonne l'élément entier,
  sans erreur ni avertissement**, dès qu'une dépendance est introuvable. Ce n'était donc pas le crochet
  de `wp_enqueue_block_style()` qui manquait, mais **une dépendance non résolue**, pour les dix
  composants du catalogue à la fois. Le correctif de #6 enregistre `mtb-jetons` en administration, dans
  `functions.php` — **hors de l'empreinte de ce module, et il ne fallait donc pas le contourner ici.**
  **Vérifié dans la toile le 2026-08-17** : `mtb-bloc-mtb-grille-chiens-css` et `mtb-jetons-css` sont
  tous deux présents dans l'iframe, la grille rend `grid-template-columns: 272px 272px` avec
  `gap: 32px`, et le cadre mesure **272 × 272** en `aspect-ratio: 1 / 1`. Ce que Fabienne voit dans
  l'éditeur est désormais ce que le visiteur voit.
- **La feuille est probablement imprimée en pied de page** sur le site public (le rendu du bloc a lieu
  après `wp_head` dans un thème de blocs), d'où un bref affichage non habillé. À observer avant de
  décider ; le mécanisme est hors empreinte. Même dette.
- **Coût d'hydratation, non mesuré.** `mtb_get_chiens_par_statut()` hydrate **chaque fiche entièrement**
  — santé, titres, deux parents, galerie, pedigree, pièces jointes — pour un bloc qui affiche **une photo
  et un nom** ; et **en mode filtré, tous les groupes sont hydratés puis jetés**. Plancher estimé ~4
  requêtes (17 fiches, aucune photo, aucune filiation) ; réaliste estimé **~280 à 340 requêtes** (17
  fiches, 1 photo principale + 6 photos de galerie, filiation renseignée), parce que `photo()` coûte 2
  requêtes par pièce jointe distincte. **Tenable à 17 chiens, pas extensible à 40.** Dette **T-#14-a**,
  à payer par une option d'hydratation ou une lecture allégée **chez le propriétaire du type**, jamais
  ici.
- **Deux trous réels du contrat #4, contournés proprement et signalés.** **Les deux contournements sont
  validés par le lot et restent écrits comme dettes** : ils ne se corrigent **pas** ici, et #16/#17
  buteront sur le même mur — c'est précisément pourquoi ils doivent rester lisibles.
  1. **`mtb_get_chien()` ne renvoie aucune URL de fiche** — aucune clé `lien` dans ses 24 clés, alors
     que `pere`/`mere` en portent une. Le bloc appelle donc `get_permalink()` **depuis l'extension**, sur
     un identifiant que la fonction propriétaire lui a donné : l'interdit du contrat #1 §8 vise le
     **thème**, et récupérer un permalien n'est pas réimplémenter une requête. **Correctif juste, pour une
     issue future : ajouter `'lien'` à la fiche, chez #4.** Les issues #16 et #17 buteront sur le même mur.
  2. **Aucune fonction publique n'expose la liste fermée des statuts ni leurs libellés pluriels.**
     Contourné par le pont vers `content/chien/choix.php` (§9). Le correctif propre serait
     `mtb_chien_statuts()` chez #4, sur le précédent **accepté** de `mtb_resultat_disciplines()`
     (décision 16).
- **Le module n'est pas insérable dans une fiche Chien**, mécaniquement, puisque la décision 17 y a
  remplacé l'éditeur de blocs par un formulaire classique. **Où il ne s'utilise pas est garanti par
  construction, pas par une consigne.**
- **Ligne d'inventaire à ajouter dans `docs/contracts/issue-1.md` §11** — hors empreinte, à écrire par
  `/lead-mtb` à la clôture du lot :
  `| blocks/ | grille-chiens | #14 | Bloc mtb/grille-chiens : un réglage « Statut à afficher », rendu serveur, état vide éditeur |`
- **Premier bloc du projet : trois hypothèses du contrat #1 §10 à éprouver en navigateur**, jamais à
  déclarer tenues sans observation — la poignée d'`editorScript` (panne = `_doing_it_wrong`), la
  transmission des métadonnées de `block.json` au client via
  `unstable__bootstrapServerSideBlockDefinitions` (panne = titre brut dans l'insérteur ; **repli : passer
  `titre`/`description` par `wp_localize_script`, jamais en dur dans `editeur.js`**), et l'existence de
  la poignée `wp-server-side-render` (panne = aucun aperçu, **donc plus d'état vide dans l'éditeur**).
  Si l'observation contredit le contrat #1 §10, **le remonter au lieu de contourner**.
- **Tout le chiffrage de mise en page de ce contrat est calculé, pas mesuré** (dette T10 : aucun rendu
  public n'a jamais été observé sur ce projet). Colonnes réelles à 360 px et à 200 % de zoom, rendu du
  texte `alt` d'une image en échec, comportement de la sélection de texte, support de `:has()`, et
  l'endroit où la feuille est imprimée : **à vérifier en navigateur.**
- **~~Aucune fiche de chien n'existe en base.~~ Ce n'est plus vrai depuis le 2026-08-17** : deux fiches
  d'essai **explicitement fictives**, publiées avec le statut « Reproducteur », existent en base —
  `#116 « Essai fictif A (a supprimer) »` et `#117 « Essai fictif B (a supprimer) »`. Elles n'ont **pas**
  été créées par cette chaîne, et **aucun fait d'élevage n'a été inventé** : leur nom dit ce qu'elles
  sont et qu'elles sont à supprimer, elles n'ont ni photo, ni sexe, ni date, ni LOF, ni généalogie
  (D11 tenue). Elles ont permis de **vérifier le rendu réel de la grille**, ce que l'état vide seul ne
  permettait pas. `wp mtb import-fixtures` n'est toujours livré par personne (contrat #4 §11), et **elles
  sont à retirer avant toute recette de production**.

## 13. Journal des amendements — après observation en navigateur

### Passe 3 — le cadrage « Centre » converge, 2026-08-18

**Ce contrat ne change pas. C'est `mtb-fiche-information.css` qui s'aligne sur lui.** Le §« `centre` vaut
`50% 38%` » est confirmé, appliqué partout, et mesuré en navigateur.

**Le défaut constaté par le test d'intégration du lot.** Sur la **même photo**, le même choix « Centre »
de l'éleveuse donnait deux cadrages différents, `object-position` **calculé par le navigateur** :

| Composant | Avant | Après |
|---|---|---|
| `.mtb-fiche-information__photo > img` | `50% 50%` | **`50% 38%`** |
| `.mtb-grille-chiens__photo` | `50% 38%` | **`50% 38%`** (inchangé) |

**Pourquoi c'est #7 qui bouge, et pas ce module.** Les deux lectures ne se valaient pas :

1. MASTER §6.2 donne un défaut **chiffré** — `50% 38%`, « sur une photo de chien en pied, la tête est
   au-dessus du centre géométrique » — et une liste fermée de **cinq** libellés, dont **aucun** n'est
   chiffré. Aucun des cinq n'encode 38 %, `haut` valant `center top` (0 % en vertical).
2. `includes/content/chien/choix.php:92` fait de `centre` le **défaut stocké**. Si `centre` valait le
   centre géométrique, **aucune** des cinq clés ne pourrait être renvoyée par `cadrage_par_defaut()` sans
   enterrer le défaut de MASTER sur les 17 fiches. Les seules issues seraient d'inventer une sixième
   valeur — interdit — ou de réviser MASTER — hors chaîne.
3. `docs/contracts/issue-7.md:858` (dette 6) **classe lui-même sa lecture géométrique comme une dette**
   et écrit que « la décision visuelle appartient à MASTER, pas à cette chaîne ».

La lecture de ce contrat est donc la seule implémentable sans rien inventer. **Aucune règle
`--cadrage-centre` n'a été ajoutée ici** : l'absence de règle, qui fait retomber `centre` sur le repli
`50% 38%`, reste la seule écriture où « Centre » et le défaut **ne peuvent pas** diverger. Seul le
commentaire annonçant la divergence comme non tranchée a été réécrit.

**Mesuré en navigateur** (WordPress 6.9, port 3005, `/ti3-les-six/`, qui porte les deux composants sur la
même photo), `getComputedStyle(img).objectPosition` sur les cinq clés **plus** le cas de la valeur absente
ou inconnue. Les deux composants rendent désormais **exactement la même paire**, et les cinq choix
restent discernables :

| Clé | `fiche-information` | `grille-chiens` |
|---|---|---|
| `haut_gauche` | `0% 0%` | `0% 0%` |
| `haut` | `50% 0%` | `50% 0%` |
| **`centre`** | **`50% 38%`** | **`50% 38%`** |
| `haut_droite` | `100% 0%` | `100% 0%` |
| `bas` | `50% 100%` | `50% 100%` |
| *absente ou inconnue* | `50% 38%` | `50% 38%` |

**Limite de la mesure, à ne pas redécouvrir.** Le fixture des pages TI3 est un **paysage 1536×1024**.
Dans le cadre `3/2` de la fiche il n'y a **aucun débordement vertical**, et dans le carré de la grille le
débordement est **horizontal**. La composante verticale du point d'intérêt n'est donc **visuellement
exercée par aucun fixture existant** : la convergence est établie sur les valeurs calculées — la mesure
même dans laquelle le défaut avait été constaté — et non à l'œil. **Une photo portrait manque au jeu de
fixtures**, et c'est elle qu'il faudra pour voir le pari de MASTER §6.2 mordre. À rapprocher de la
question **D5** de MASTER, qui prévoit déjà de revérifier `50% 38%` après l'import des vraies photos.

**Ce qui reste ouvert, et qui n'appartient à aucune chaîne de code.** « Centre » étant le défaut, le
choisir ne change **rien** à ce que l'éleveuse voit — c'est le propre de l'option par défaut d'une liste
fermée, les quatre autres agissant normalement. Deux conséquences à porter au lot, **non corrigées ici** :

- L'aide de l'écran de saisie, `includes/fields/chien/ecran.php:571`, dit « **Par défaut, c'est le
  centre.** » C'est la phrase que ce contrat interdit : elle promet un centre géométrique. Elle doit dire
  « le cadrage par défaut, qui garde la tête du chien ». **Hors empreinte de ce correctif.**
- Rendre « Centre » distinct du défaut supposerait un **sixième** cadrage nommé dans MASTER §6.2, ou une
  révision du repli. **Décision de `lead-design-mtb`, pas de cette chaîne.**

### Passe 2 — la finition, 2026-08-17

Le composant était livré et commité (`009eaa7`). Cette passe **ne l'a pas redessiné** : elle a payé trois
dettes, retiré une déclaration en double, corrigé un défaut visuel mesuré et ouvert une interface pour la
suite. **Sept points, dont cinq amendent ce contrat.**

**1. Le filtre `block_categories_all` local est SUPPRIMÉ.** La passe 1 avait écrit « le filtre local N'A
PAS été retiré, et c'est délibéré », au motif qu'on ne supprime pas un garde-fou en s'appuyant sur un
livrable écrit en parallèle. **Ce motif est périmé** : `categorie-mtb/` n'est plus « écrit en parallèle »,
il est **livré et vérifié en exécution**. Trois modules déclaraient la même catégorie — la dette T9 dans
sa forme la plus littérale. Vérifié après suppression : `apply_filters( 'block_categories_all' )` rend
**exactement une** entrée `mtb`, « Mont Brabant ». **T-#14-b soldée.** Voir §3 et l'arbitrage 15.

**2. Une fonction globale de rendu est exposée** pour #16 et #17 : `mtb_grille_chiens_rendu()`. Elle
**contredit la lettre du §3 et du §10 d'origine**, et l'interdit a été **réécrit** plutôt que contourné.
Voir §3 bis pour son contrat, ses trois choix de forme et l'honnêteté sur son utilité réelle dans un thème
de blocs.

**3. Le cerne photo était invisible dès qu'une photo chargeait**, et personne ne l'avait vu — le
commentaire de la feuille affirmait le contraire. `--cerne-photo` est une ombre **intérieure**, que le
contenu remplacé de l'`<img>` recouvrait entièrement. Corrigé par la recette V9, en pseudo-élément.
**Leçon à porter, et elle vaut pour tout le catalogue** : une ombre `inset` sur une boîte qu'un `<img>`
remplit exactement **n'existe pas**, quoi qu'en dise le CSS. Voir §6.

**4. La seconde phrase d'état vide est reformulée, et son choix ne dépend plus du réglage seul.**
L'ancienne règle pouvait affirmer « aucune fiche ne porte le statut « Retraité » » alors que le vrai
problème était qu'aucune fiche ne portait **de statut du tout** — vrai à la lettre, trompeur en pratique.
Voir §8.

**5. `mtb-etat-vide__composant` devient `mtb-etat-vide__nom`**, crochet gelé pour les six composants du
lot. Voir §4 et §8.

**6. Les deux dettes d'apparence de l'éditeur sont soldées par l'issue #6**, pas par ce module :
**T-#14-h** (la feuille n'atteignait pas la toile — et le diagnostic écrit en passe 1 était **faux**, voir
§12) et **T-#14-c** (l'apparence de l'état vide, désormais dans `editor.css`). Le correctif vivait dans
`functions.php` : il ne fallait pas le contourner ici, et il ne l'a pas été.

**7. Ce qui a été vérifié dans la toile de l'éditeur, avec deux fiches d'essai fictives en base** —
`debug.log` à **0 octet** : les deux feuilles présentes dans l'iframe (`mtb-bloc-mtb-grille-chiens-css`,
`mtb-jetons-css`) ; la grille à `272px 272px` et `gap: 32px` ; le cadre à **272 × 272**, `aspect-ratio:
1 / 1`, fond `--calcaire-creux`, rayon nul ; le cerne sur le `::after` avec `pointer-events: none`, et
**visible à l'œil sur une photo blanc pur** ; le calque du lien **correctement ancré carte par carte**
(chaque sonde rend le lien de sa carte) ; le nom **sélectionnable** sur ses deux lignes ; l'apparence
d'état vide de #6 avec ses majuscules obtenues par le CSS ; le titre de groupe « Reproducteurs » ; et les
**deux phrases exactes** dans leurs trois branches.

> **Une mesure fausse, corrigée, à ne pas refaire.** Une première sonde `elementFromPoint()` au **centre
> du rectangle englobant** du nom rendait le lien, ce qui semblait contredire le point 3 de la passe 1. La
> sonde était fausse : le nom tient sur **deux lignes**, et le milieu du rectangle englobant d'un élément
> en ligne tombe **entre les deux boîtes de ligne**, là où le `<span>` n'a aucune boîte. Sondé sur chaque
> boîte de ligne (`getClientRects()`), le `<span>` répond aux quatre sondes. **Le point 3 de la passe 1
> tient. Sur un texte en ligne qui se coupe, on sonde les boîtes de ligne, jamais le rectangle
> englobant.**

### Passe 1 — après le premier rendu réel du projet, 2026-08-17

**2026-08-17.** `dev-integration-mtb` a été le premier agent du projet à **observer un rendu réel** de
données d'élevage (dette T10). Trois affirmations de la rédaction initiale de ce contrat étaient
fausses. Elles sont corrigées **à la source** dans les paragraphes concernés ; ce journal dit ce qui a
changé et pourquoi, pour qu'une chaîne future ne re-litige pas une décision sur un argument périmé.

### 1. Le canal large est inatteignable — la grille rend 2 colonnes, jamais 4

**Mesuré, pas déduit.** La chaîne d'ancêtres réelle est
`body > .wp-site-blocks > main.mtb-canal > .entry-content.wp-block-post-content > .mtb-grille-chiens`.
Or `base.css` pose `.mtb-canal > * { grid-column: texte-debut / texte-fin }` : l'enfant direct de
`.mtb-canal` est **`.entry-content`**, donc **tout le contenu de la page** est confiné au canal texte
(36 rem = 576 px). Le `max-inline-size: var(--l-large)` de la feuille vaut 1088 px : **il est totalement
inerte**, un plafond au-dessus du conteneur. Mesure à 1280 px : `grid-template-columns: 272px 272px`.

**Ce qui s'effondre** : l'argument d'origine de l'arbitrage 7 (« `14rem` est la seule valeur qui produit
les 4 colonnes de MASTER §9.4 dans le canal large ») portait sur un canal que le bloc **ne peut pas
occuper**. L'arbitrage 6 avait choisi un mécanisme incapable de réclamer un canal nommé, et
`supports.align: false` prive par ailleurs l'éditrice de tout levier.

**Ce qui tient quand même, et c'est la raison à retenir** : dans le canal réellement obtenu, `14rem`
donne 2 colonnes de **272 px** contre 3 de **186 px** pour `9rem`. BRIEF §10 fait des photographies la
matière première du site : la valeur reste la bonne, **pour une autre raison que celle écrite d'abord**.

**Rien n'est cassé** — 2 colonnes de 272 px sont lisibles, tiennent 360 px sans défilement horizontal et
2 colonnes à zoom 200 %. Mais **MASTER §7.1, qui donne nommément le canal large aux « grilles de
chiens », n'est pas tenu.** Le correctif vit dans `templates/`, `base.css` ou `theme.json` — **hors de
l'empreinte des six chaînes du lot**. C'est un arbitrage pour l'epic Gabarits, pas une dérive de code :
dette **T-#14-i**.

### 2. La catégorie de blocs `mtb` a été livrée par une chaîne sœur

Le §3 et l'arbitrage 15 affirmaient qu'« aucune chaîne du lot ne l'a dans son empreinte ». **C'était vrai
au gel, ce ne l'est plus** : `includes/blocks/categorie-mtb/` a été livré pendant le lot, avec un filtre
`block_categories_all` idempotent équivalent.

**Les deux coexistent sans dégât, vérifié en exécution** : `apply_filters( 'block_categories_all' )` rend
**une seule** entrée `mtb`, « Mont Brabant », en tête. C'est exactement ce que l'idempotence promettait.

**Le filtre local N'A PAS été retiré** à cette passe, et c'était délibéré : supprimer un garde-fou en
s'appuyant sur un livrable écrit **en parallèle** rouvrirait la panne muette que le §3 décrit — un bloc
absent de l'insérteur, sans une erreur. La suppression est **une ligne, dans le seul dossier du bloc**,
pour une issue de suite : dette **T-#14-b**, alors **payable** au lieu d'ouverte.

> **Périmé par la passe 2, ci-dessus : le filtre EST retiré et T-#14-b est SOLDÉE.** `categorie-mtb/`
> n'était plus « écrit en parallèle » mais livré et vérifié. Le raisonnement ci-dessus reste consigné
> parce qu'il était juste **au moment où il a été écrit** — et parce qu'il dit à quelle condition on
> retire un garde-fou : quand le livrable dont il couvrait l'absence est **constaté**, pas annoncé.

### 3. `position: relative` seul ne rendait pas le nom sélectionnable

Le §11 arbitrage 13 promettait que `__nom-texte` « rend le nom sélectionnable malgré le calque étiré ».
**La feuille ne le tenait pas.** Le `::after` du lien est le **dernier enfant** de l'`<a>` : il vient
donc après le `<span>` dans l'ordre du document, et deux éléments positionnés en `z-index: auto` se
peignent dans cet ordre. Mesuré avant correction, `elementFromPoint()` sur le texte du nom rendait **le
lien**, jamais le span.

**Corrigé** par `z-index: 1` sur `.mtb-grille-chiens__nom-texte`. Après correction, le texte est
atteignable et sélectionnable, et le calque garde tout le reste de la carte. Le `<span>` restant à
l'intérieur de l'`<a>`, le lien demeure activable sur toute sa surface. `z-index` est une primitive
d'empilement, pas une valeur de design : **aucun jeton n'a été créé**, et l'écart est consigné dans
l'en-tête de la feuille.

**Leçon à porter** : « je pose `position: relative` pour remonter au-dessus » est un réflexe **faux** dès
que l'élément à dépasser est un pseudo-élément **frère plus tardif**. Les autres composants du lot
emploient le même motif de carte cliquable.

### 4. Quatre observations qui ne changent aucune décision, mais qu'il ne faut pas redécouvrir

| Observation | Conséquence |
|---|---|
| **`sizes` rendu = `auto, (min-width: 37.5em) 14rem, 45vw`.** Le cœur (WP 6.9, `wp_img_tag_add_auto_sizes`) préfixe `auto,` sur toute image `loading="lazy"` | Aucune dérive du module. Le défaut gelé survit comme **repli** ; les navigateurs qui supportent `sizes=auto` emploient la largeur de mise en page réelle, donc plus juste que notre déclaration |
| **L'échelle de `srcset` n'a aucune candidate près de 272 px** : Chrome choisit `683×1024` pour une vignette de 272 px, l'échelle WordPress étant 200 / 683 / 768 / 900. Chaque vignette télécharge ≈ 2,5 × trop large | Conséquence **assumée** de l'interdit d'`add_image_size()` du §7 (une taille nouvelle ne s'appliquerait pas aux photos déjà téléversées, donc à rien après la reprise de l'ancien site). **À consigner comme un coût de D8, pas comme une victoire.** Dette **T-#14-j** |
| **La feuille est servie EN LIGNE dans le `<head>`** (`<style id="mtb-bloc-mtb-grille-chiens-inline-css">`), pas en pied de page | La crainte du §12 **ne se réalise pas** : aucun affichage non habillé. Corollaire : les ~16 Ko de la feuille, dont ~5 Ko de commentaires, sont servis **verbatim** sur chaque page portant le bloc (`wp_maybe_inline_styles`, limite 20 000 o). Les feuilles sœurs pèsent davantage et passeront en `<link>` : le lot livre **deux modes de distribution** pour six composants. Préoccupation globale (minification), hors empreinte |
| **`nom_usage` peut valoir « Non renseigné »** : la prémisse du contrat #4 §2 (« WordPress impose un titre ») est **fausse** — une fiche `mtb_chien` publiée sans titre est acceptée | `mtb_get_chien()` la couvre, donc **aucun lien vide, aucun trou d'accessibilité**. Mais la carte affiche alors littéralement « Non renseigné » comme nom — ce que le raisonnement du §5 voulait précisément éviter. **Territoire de #4**, à signaler |

### 5. Ce qui a été observé vrai, et qui ne l'avait jamais été sur ce projet

Les **trois hypothèses** que le contrat #1 §10 et le §12 de ce document donnaient comme non éprouvées
sont désormais **observées** : la poignée d'`editorScript` n'entraîne aucun `_doing_it_wrong`, le titre
français atteint le client via `unstable__bootstrapServerSideBlockDefinitions`, et la poignée
`wp-server-side-render` existe. **Le repli documenté au §12 (passer `titre`/`description` par
`wp_localize_script`) n'est donc pas nécessaire et n'a pas été mis** — une chaîne de moins à faire
diverger.

Également observé, avec `debug.log` à **0 octet** après une session complète d'édition et de rendu : le
bloc dans l'insérteur sous « MONT BRABANT » avec son titre français, le `RadioControl` et ses cinq
libellés, l'aperçu serveur dans la toile, les **deux phrases exactes** de l'état vide, un chien sans
photo à **même silhouette** qu'un chien avec photo, **un seul arrêt de tabulation par carte**, l'anneau
de focus de §8.1 sur la carte entière, **aucun `fetchpriority`**, **aucune origine tierce**, un nom de
63 caractères coupé sur 4 lignes sans débordement, et **1 colonne de 324 px à 360 px sans défilement
horizontal**.

> **Périmé par la passe 2** : T-#14-h est **soldée** par l'issue #6, et les deux feuilles atteignent
> désormais la toile. Le paragraphe suivant décrit l'état d'avant le correctif.

**Et T-#14-h est confirmée à la lettre** : dans la toile de l'éditeur, la feuille n'entrant pas dans
l'iframe, les photos s'empilent pleine largeur, sans grille et sans carré. Lisible, jamais cassé, et
long à faire défiler.
