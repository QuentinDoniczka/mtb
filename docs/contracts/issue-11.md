# Contrat d'interface — Issue #11 — Composant Coordonnées et plan d'accès

**Gelé le 2026-08-18.** Lot 4 (epic 4 « Composants génériques II »), chaînes sœurs #9 et #10 en
parallèle dans le même arbre de travail.

Ce document est contraignant à partir de sa date de gel. Les deux plans (`leaddev-back-mtb` et
`leaddev-front-mtb`) ont été écrits **en aveugle l'un de l'autre** ; ce qui suit est la réconciliation,
et il l'emporte sur les deux plans partout où ils divergent.

---

## 0. L'approche retenue, et la carte

**La carte est un emplacement d'image réglable depuis la médiathèque. Aucune image de plan n'est
commitée dans le dépôt.**

La ligne `wp-content/themes/mtb/assets/img/plan-acces.*` de l'empreinte de l'issue **disparaît**. Elle
était le seul point de contact de cette chaîne avec les fichiers du thème hors de sa feuille de bloc,
donc son seul risque de collision avec les chaînes sœurs.

Quatre raisons, dans l'ordre de leur poids :

1. **Le point géographique ne peut être certifié par aucun agent de la chaîne.** Vérification faite sur
   le site source : `https://www.mtbrabant.com/contact/` porte **deux** iframes Google Maps, encodant
   **deux points distincts, distants d'environ 2 km** — `43.514689, 6.242809` (zoom 10) dans la colonne
   principale, `43.533404, 6.248086` (zoom 16) dans la colonne latérale, laquelle est un gabarit global
   qu'on retrouve à l'identique sur `/mentions-légales/`. Aucun élément de la page ne dit lequel est
   l'élevage. Produire une image de plan reviendrait à trancher un **fait géographique** par déduction.
   Question bloquante Q-11-a.
2. **Une image commitée dans le thème inverserait la frontière de `CLAUDE.md`** : l'extension devrait
   appeler `get_theme_file_uri()`, donc `mtb-core` dépendrait du thème `mtb`. Le plan d'accès est du
   contenu ; il doit survivre à un changement de thème.
3. **Dette T12** : WordPress ne découpe et ne convertit une image qu'**au téléversement**. Une image
   déposée dans le thème n'aurait ni sous-tailles, ni `srcset`, ni format moderne. Passer par la
   médiathèque **solde T12 sur cet objet** au lieu de la contourner.
4. **La carte échapperait définitivement à D1.** Par la médiathèque, l'éleveuse peut la remplacer,
   décrire l'image et corriger sa légende sans développeur.

**Conséquence, et ce n'est pas un mode dégradé** : tant qu'aucune image n'est posée, `MASTER.md` §9.2
« emplacement facultatif » s'applique à la lettre — **l'emplacement n'existe pas ; aucun trou, aucune
réserve**. Le bloc se termine proprement sur le courriel. **C'est le comportement nominal du composant
au jour de sa livraison**, et la mise en page doit être aussi juste sans carte qu'avec.

Le repli et le cas nominal étant le **même code**, la question du plan peut être tranchée après la
livraison sans réécrire une ligne.

---

## 1. Faits de domaine — recopiés, jamais reformatés

`docs/BRIEF.md` §7 fixe les valeurs par défaut, telles quelles :

| Donnée | Valeur par défaut, au caractère près |
|---|---|
| Adresse | `3060 Route de Salernes, 83570 Entrecasteaux` |
| Téléphone | `0680505619` |
| Courriel | `mtbrabant@gmail.com` |

- **Le numéro s'affiche `0680505619`, d'un seul tenant, sans regroupement en paires.** Le brief dit
  « telles quelles », et la vérification du site source confirme cette graphie : la colonne latérale
  écrit « Contacter nous au 0680505619 », dix chiffres sans espace. Ni le serveur ni le thème ne le
  regroupent, ne le préfixent, ni ne lui ajoutent `+33`.
- **L'adresse par défaut garde la virgule du brief, sur une seule ligne.** Le site source la dispose sur
  deux lignes en capitales (`3060 ROUTE DE SALERNES` / `83570 ENTRECASTEAUX`), mais les capitales sont
  une présentation IONOS et la coupure supprimerait la virgule que le brief écrit. Le champ étant une
  zone de texte multiligne, l'éleveuse obtient deux lignes d'une frappe. La disposition définitive
  appartient à la reprise (#19-#21).
- `© Fabienne Guéneau` est une mention de pied de page du site source (« © Fabienne Guéneau MAJ Juin
  2026 »), **pas une coordonnée** : elle n'entre pas dans ce bloc. Elle appartient au pied de page de
  l'epic Gabarits.

---

## 2. Fonctions de lecture exposées par l'extension

> **⚠ Section amendée le 2026-08-18 par l'arbitrage inter-chaînes du §17.**
> `mtb_coordonnees_elevage()` **n'est plus une fonction globale et n'est plus exposée au thème.** Elle
> est devenue `MTB\Core\Blocks\CoordonneesPlan\coordonnees_elevage()`, **interne au module**. Sa
> description ci-dessous reste exacte quant à la **forme du retour** ; seules sa portée et sa
> consommabilité changent. Les trois fonctions réellement exposées sont désormais
> `mtb_coordonnees_lien_telephone()`, `mtb_coordonnees_lien_courriel()` et
> `mtb_coordonnees_plan_rendu()`.

Les fonctions exposées sont en **espace de noms global**, déclarées sous `function_exists()`, dans
`includes/blocks/coordonnees-plan/coordonnees.php` (dérivations d'URI) et `interface.php` (rendu).
**À appeler sous `function_exists()`** (décision 19).

### ~~`mtb_coordonnees_elevage(): array`~~ → `coordonnees_elevage(): array`, interne au module

Retour — **exactement trois clés, toujours présentes, toujours des chaînes non vides** :

| Clé | Valeur | Garantie |
|---|---|---|
| `adresse` | `3060 Route de Salernes, 83570 Entrecasteaux` | jamais vide |
| `telephone` | `0680505619` | jamais vide, sans regroupement |
| `courriel` | `mtbrabant@gmail.com` | jamais vide |

**Le cas « donnée absente » n'existe pas** : ce sont des constantes du code, pas une lecture de contenu.
Aucune requête, aucune option, aucun `null`, aucun `?array`. Appelable à tout moment, y compris avant
`init`.

**Depuis l'arbitrage du §17**, la clé `telephone` provient de `mtb_get_telephone_elevage()` quand cette
fonction existe (traitement défensif, repli sur la constante locale sinon) ; `adresse` et `courriel`
restent des littéraux recopiés de BRIEF §7, aucune fonction centrale n'étant gelée pour eux.

**Corollaire que le thème doit connaître** : cette fonction **ne reflète pas** ce que l'éleveuse a saisi
dans un bloc donné. Elle rend les valeurs de référence de l'élevage. Ce qu'elle a tapé sur une page vit
dans les attributs du bloc de cette page.

Nom **sans `get`**, comme `mtb_resultat_disciplines()` : c'est une table de constantes, pas une lecture
de contenu (décision 16).

### `mtb_coordonnees_lien_telephone( string $telephone ): string`

Rend l'URI, **jamais échappée** — l'échappement appartient au rendu.

- Retire **uniquement les espaces** : U+0020, U+00A0 (insécable) et U+202F (fine insécable, que
  produisent le clavier et le traitement de texte français). **Rien n'est ajouté** : aucun `+33`, aucun
  zéro de tête, aucune reformulation.
- `'06 80 50 56 19'` → `'tel:0680505619'` · `'0680505619'` → `'tel:0680505619'`
- **Rend `''`** si la valeur ne contient rien une fois les espaces retirés. Le rendu affiche alors le
  numéro en texte nu, jamais un lien vide.

### `mtb_coordonnees_lien_courriel( string $courriel ): string`

- Rend `'mailto:' . $courriel` **si et seulement si `is_email( $courriel )`**, sinon `''`.
- La valeur n'est **jamais modifiée** : `is_email()` décide s'il y a un lien, jamais ce qui s'affiche.
- `mailto:` **en clair**, sans obfuscation. L'adresse est déjà publique sur le site source (où elle est
  même en texte brut, sans lien), et zéro octet de JavaScript est une contrainte gelée : aucune parade
  n'existe sans JS.

### `mtb_coordonnees_plan_rendu( array $arguments = array() ): string`

Fonction de **rendu réutilisable** (décision 24), pour le pied de page de l'epic Gabarits et pour tout
gabarit qui a besoin du même motif. Rend du HTML, donc **pas de préfixe `mtb_get_`**.

| Clé de `$arguments` | Type | Défaut |
|---|---|---|
| `adresse` | `string` | `mtb_coordonnees_elevage()['adresse']` |
| `telephone` | `string` | `mtb_coordonnees_elevage()['telephone']` |
| `courriel` | `string` | `mtb_coordonnees_elevage()['courriel']` |
| `plan_id` | `int` | `0` — le pied de page n'a rien à passer |
| `plan_description` | `string` | `''` |
| `classes` | `string[]` | `array()` — ajoutées après `mtb-coordonnees-plan` |

Toute clé inconnue est **écartée** (`array_intersect_key`, jamais `wp_parse_args`). Retour : le balisage
complet, racine comprise, **ou `''` si les trois coordonnées sont vides**. N'imprime rien. Ne rend
**jamais** l'état vide de l'éditeur.

---

## 3. Bloc enregistré

**`mtb/coordonnees-plan`** — nom **gelé au caractère près**. C'est lui qui compose le nom de fichier
`assets/css/blocs/mtb-coordonnees-plan.css` dans la boucle de `functions.php`. Un écart d'une lettre :
aucune feuille servie, **aucune erreur, aucun avertissement**.

| Point | Valeur |
|---|---|
| Nom affiché | **Coordonnées et plan d'accès** |
| Description | « L'adresse, le téléphone et le courriel de l'élevage, et un plan d'accès si vous en avez un. » |
| Catégorie | `"mtb"` — décision 25. **Aucun `add_filter( 'block_categories_all' )` dans ce module.** |
| Icône | `location-alt` (Dashicon du cœur, aucune ressource tierce) |
| Mots-clés | `coordonnées`, `contact`, `adresse`, `téléphone`, `courriel`, `plan`, `accès` |
| `supports.align` | **`false`** — §7.1 ne range ce composant ni en canal large ni en canal pleine ; c'est aussi ce qui désamorce le piège de la décision 32 par construction et rend le `sizes` de l'image exact |
| `supports` (autres) | `alignWide`, `anchor`, `customClassName`, `html`, `lock`, `renaming`, `reusable`, `color`, `typography`, `spacing`, `dimensions`, `background`, `filter` → **tous `false`** (§14, garde-fous du design) |
| `multiple` | `true` — coordonnées possibles en tête **et** en pied d'une page longue ; deux `<address>` restent valides |
| `example` | fourni, avec les trois défauts et `plan_id: 0` |
| `style` / `viewStyle` / `editorStyle` | **absents du `block.json`** — décision 28, le CSS vit dans le thème. **Ne pas reproduire T15.** |
| `viewScript` / `script` | **absents** — zéro octet de JavaScript servi au visiteur |

### 3.1 Attributs

| Clé | Libellé écran | Type | Défaut | Vide ⇒ |
|---|---|---|---|---|
| `adresse` | **Adresse** (zone de texte, 3 lignes) | `string` | valeur du brief §7 | la paire `<dt>`/`<dd>` n'est pas rendue |
| `telephone` | **Téléphone** | `string` | `0680505619` | idem |
| `courriel` | **Courriel** | `string` | `mtbrabant@gmail.com` | idem |
| `plan_id` | (bouton « Choisir un plan ») | `integer` | `0` | aucun emplacement — §9.2 |
| `plan_description` | **Description de la photo (pour les personnes aveugles)** | `string` | `""` | `alt=""` — image décorative |

**Aucun champ obligatoire. Aucune liste fermée. Aucun réglage de cadrage** (voir §4.4).

> **Arbitrage de revue du 2026-08-18 — le libellé de `plan_description`.**
> Le contrat avait d'abord gelé **« Description de l'image (pour les personnes aveugles) »**, au
> motif qu'un plan d'accès n'est pas une photographie — et il avait gelé cette divergence **sans la
> nommer**, alors qu'il en nomme trois autres (§13). C'était le défaut : une divergence tue est une
> divergence qui se propage.
> La revue du lot l'a relevée en **HIGH** : `MASTER.md:918` (§10.2) fige
> **« Description de la photo (pour les personnes aveugles) »**, et `fiche-information/editeur.js:234`
> l'emploie déjà mot pour mot. Deux libellés pour le même champ dans le même catalogue.
> **Décision : alignement sur `MASTER.md` §10.2, au caractère près.** La règle du projet est que le
> tableau de vocabulaire du §10 tranche, **pas l'argument de la chaîne qui bute dessus** : le système
> de design ne s'amende pas depuis une issue de composant. L'argument de fond reste recevable et
> **reste ouvert** pour la prochaine révision de `lead-design-mtb` — `MASTER.md` §10.2 n'a aucune ligne
> pour une image qui n'est **pas** une photographie, alors que le catalogue en produira d'autres.
> **En attendant, le catalogue parle d'une seule voix. Ne pas rouvrir ce libellé sans cette révision.**

### 3.2 Les valeurs par défaut n'existent qu'une fois dans le code

`block.json` est du JSON statique : aucun appel de fonction n'y est évaluable. Les trois `default` y
sont donc déclarés à `""`, et `bootstrap.php` accroche **`block_type_metadata`** (cœur, depuis WP 5.5),
**gardé sur `'mtb/coordonnees-plan' === $metadata['name']`**, pour y injecter les valeurs lues dans
`mtb_coordonnees_elevage()`. Le filtre s'exécute **dans** `register_block_type_from_metadata()`, donc
avant l'enregistrement : la valeur atteint le rendu **et** l'éditeur.

**Mode de panne, et il est bénin** : si le filtre ne s'appliquait pas, un bloc fraîchement inséré aurait
trois attributs vides — donc **rien côté public** et l'apparence d'état vide côté éditeur, immédiatement
visible. Jamais une valeur fausse, jamais un silence.

> **Vérification obligatoire V1** — insérer le bloc dans une Page neuve et constater que les trois
> champs de l'inspecteur sont **pré-remplis**. Si ce n'est pas le cas : replier sur des littéraux dans
> `block.json`, **écrire le commentaire qui nomme la duplication**, et la remonter comme dette. Ne pas
> laisser le doute.

### 3.3 Où le bloc s'utilise

Dans les **Pages** — Contact au premier chef, Placement et Accueil si elle le souhaite. **Jamais dans
une fiche Portée, Chien ou Résultat** : décision 24, obtenue **mécaniquement** (ces trois types
emploient l'écran de saisie classique, aucun bloc n'y est insérable). Rien à coder.

---

## 4. Balisage rendu — le contrat que le thème habille

### 4.1 Cas nominal complet

```html
<div class="wp-block-mtb-coordonnees-plan mtb-coordonnees-plan">
  <address class="mtb-coordonnees-plan__coordonnees">
    <dl class="mtb-coordonnees-plan__liste">
      <dt class="mtb-coordonnees-plan__etiquette">Adresse</dt>
      <dd class="mtb-coordonnees-plan__valeur mtb-coordonnees-plan__valeur--adresse">3060 Route de Salernes, 83570 Entrecasteaux</dd>

      <dt class="mtb-coordonnees-plan__etiquette">Téléphone</dt>
      <dd class="mtb-coordonnees-plan__valeur mtb-coordonnees-plan__valeur--telephone">
        <a class="mtb-coordonnees-plan__lien" href="tel:0680505619">0680505619</a>
      </dd>

      <dt class="mtb-coordonnees-plan__etiquette">Courriel</dt>
      <dd class="mtb-coordonnees-plan__valeur mtb-coordonnees-plan__valeur--courriel">
        <a class="mtb-coordonnees-plan__lien" href="mailto:mtbrabant@gmail.com">mtbrabant@gmail.com</a>
      </dd>
    </dl>
  </address>

  <figure class="mtb-coordonnees-plan__figure">
    <div class="mtb-coordonnees-plan__cadre">
      <img class="mtb-coordonnees-plan__image" src="…" srcset="…" sizes="…"
           width="1024" height="683" alt="" loading="lazy" decoding="async">
    </div>
    <figcaption class="mtb-coordonnees-plan__legende">…légende de la pièce jointe…</figcaption>
  </figure>
</div>
```

### 4.2 Liste exhaustive des crochets de classes

**Tout crochet absent de cette liste n'existe pas dans le HTML ; tout crochet inventé hors de cette
liste habille du vide.**

| Classe | Élément | Toujours présent ? |
|---|---|---|
| `mtb-coordonnees-plan` | `<div>` racine | oui |
| `mtb-coordonnees-plan__coordonnees` | `<address>` | oui, dès qu'un champ est rempli |
| `mtb-coordonnees-plan__liste` | `<dl>` | oui, dès qu'un champ est rempli |
| `mtb-coordonnees-plan__etiquette` | `<dt>` | une par champ **rendu** |
| `mtb-coordonnees-plan__valeur` | `<dd>` | une par champ **rendu** |
| `mtb-coordonnees-plan__valeur--adresse` | `<dd>` | si adresse rendue |
| `mtb-coordonnees-plan__valeur--telephone` | `<dd>` | si téléphone rendu |
| `mtb-coordonnees-plan__valeur--courriel` | `<dd>` | si courriel rendu |
| `mtb-coordonnees-plan__lien` | `<a>` du téléphone **et** du courriel | si le champ est rendu **et** que le lien est possible |
| `mtb-coordonnees-plan__figure` | `<figure>` | **non** — absent sans plan |
| `mtb-coordonnees-plan__cadre` | `<div>` autour de l'`<img>` | **non** |
| `mtb-coordonnees-plan__image` | `<img>` | **non** |
| `mtb-coordonnees-plan__legende` | `<figcaption>` | **non** — absent si la légende est vide |

Les trois modificateurs `__valeur--*` ne portent **aucune règle** aujourd'hui, et c'est assumé : sans
eux, aucune vérification ne peut affirmer « le champ téléphone n'est pas rendu » sur un sélecteur
stable, et un besoin futur de ciblage par champ rouvrirait le bloc entier. Coût : trois classes.

### 4.3 Règles de balisage, opposables à toute divergence

1. **Un seul `<address>`**, qui contient les trois coordonnées. C'est sa définition HTML : les
   coordonnées du `body`. Le `<figure>` est **hors** de l'`<address>` — une carte n'est pas une
   coordonnée.
2. **Les coordonnées d'abord, le plan ensuite, dans l'ordre du DOM.** C'est **D7** : l'information de
   localisation est du texte réel, rendu avant l'image, et intacte si le plan disparaît. Le thème peut
   les disposer côte à côte sans jamais toucher à l'ordre de lecture — **aucun `order`,
   `flex-direction: row-reverse` ni `grid-area` déplaçant.**
3. **Liste de définition.** `MASTER.md` §7.5 atteste le dispositif — « composée en **liste de
   définition** (`<dl>`), libellé en étiquette laiton, valeur en corps » — pour l'identité d'une fiche
   chien. *Extrapolation nommée* : je l'étends aux coordonnées, qui sont la même chose (une liste courte
   de faits libellés). Bénéfice structurel décisif : **un champ vidé retire la paire entière `<dt>`+`<dd>`,
   et aucun autre champ ne bouge** — aucune règle CSS ne dépend de la présence d'un champ.
4. **Adresse multiligne** : les retours à la ligne saisis par l'éleveuse sont rendus par des `<br>`
   **littéraux** à l'intérieur du `<dd>`. **Aucun `<span>` de ligne** : la coupure est portée par le
   HTML, le thème n'écrit rien sur ce point, et le balisage reste juste feuille de style désactivée.
5. **Libellé et valeur sont deux éléments distincts.** Le serveur émet `Adresse`, `Téléphone`, `Courriel`
   **sans ponctuation**. Le thème **n'ajoute ni deux-points, ni tiret, ni `content:` en CSS** : ce serait
   composer une chaîne.
6. **Le thème ne doit pas `display: none` les `<dt>`** : ils sortiraient de l'arbre d'accessibilité et
   les liens perdraient leur contexte. Une mise en retrait visuelle est libre.
7. **Aucun titre.** Le bloc n'émet **aucun `<h1>`…`<h6>`**. Le `<h1>` appartient à la page ; un composant
   qui émet un titre casse le plan de titres dès qu'il est inséré deux fois. L'éleveuse pose un bloc
   Titre au-dessus si elle en veut un.
8. **Aucun élément n'est rendu pour un champ vide.** Pas de `<dt>` orphelin, pas de `<dd>` vide, pas de
   `<figure>` sans image, pas de `<figcaption>` sans légende. **Jamais « Non renseigné »** sur une page
   Contact : `MASTER.md` §9.3, ligne « une section entière non remplie : la section n'est pas rendue ».
9. **Aucun `style=""`**, aucune valeur brute, aucun `--point-interet` en ligne (§13).
10. **Aucun modificateur `--avec-plan` sur la racine.** Le thème ne le demande pas ; une classe
    qu'aucune règle ne cible est une classe morte, donc une orthographe qui dérive.

### 4.4 Le cadre du plan — trois arbitrages, motivés

**a) `.mtb-coordonnees-plan__cadre` ne porte PAS la classe `.mtb-photo`, et la feuille du bloc écrit
elle-même ses propriétés de cadre.**

Décision 30 voudrait que la primitive nommée par `MASTER.md` §6.2 s'écrive en classe nue. Mais les deux
plans ont constaté indépendamment que **`.mtb-photo` est une primitive sans domicile** : ses règles nues
vivent en double, à l'identique, dans `mtb-liste-portees.css` et `mtb-derniere-portee.css`, deux feuilles
que `wp_enqueue_block_style` ne sert **que si ces blocs rendent sur la page**. Sur une page Contact — le
cas normal — un cadre qui compterait sur elles n'aurait ni fond, ni cerne, ni `object-fit`, et l'image
déborderait. C'est le défaut de contamination déjà diagnostiqué et corrigé dans
`mtb-bandeau-ouverture.css`. Les déclarations sont donc écrites **mot pour mot** comme celles des cinq
sœurs, sur le crochet du bloc, pour que la mise en commun future soit une **suppression** et jamais un
arbitrage. **Le point est remonté à `lead-design-mtb`** : une primitive servie conditionnellement n'est
pas une primitive.

**b) L'image du plan est rendue en `object-fit: contain`, pas `cover`.**

C'est le seul point où les deux plans se contredisaient frontalement, et c'est un arbitrage de fond.

`MASTER.md` §6.3 pose « le cadre gagne toujours » et interdit toute bande — mais **§6 est intitulé
« Photographies » et argumente sur des chiens** (« sur une photo de chien en pied, la tête est au-dessus
du centre géométrique »). Un plan d'accès n'est pas une photographie : c'est un **document**. Rogner une
photo de chien perd un bout de pelage ; **rogner un plan peut couper le point d'arrivée** — c'est-à-dire
l'information que l'image existe pour porter. La règle d'exactitude du domaine s'y oppose. Et comme ce
bloc n'offre **aucun réglage de cadrage**, l'éleveuse n'aurait aucun moyen de rattraper un rognage
qu'elle n'a pas choisi, sur une image dont le ratio est inconnu.

Le cadre garde donc son ratio `--r-paysage` (3/2) comme **espace réservé** ; l'image y tient **entière**,
centrée, sur fond `--calcaire-creux` — fond que §6.6 emploie déjà pour un PNG détouré, « jamais de
damier, jamais de trou ». **Écart délibéré à §6.3, à signaler à la revue et à `lead-design-mtb`** :
`MASTER.md` §6 n'atteste rien pour une image qui n'est pas une photographie de chien.

**c) Aucune déclaration `object-position` n'est écrite, et aucun réglage de cadrage n'est offert.**

`object-position: var(--point-interet, 50% 38%)` est déjà écrit **cinq fois dans le thème** et une
sixième dans l'extension (dette **T18**), et la divergence a commencé (**T16-bis**). Le défaut `50% 38%`
est justifié par la tête d'un chien : il n'a **aucun sens** sur un plan. Ne rien écrire donne l'initiale
CSS `50% 50%` — le centre géométrique, seul ancrage qu'un plan ait une raison d'avoir. **T18 n'augmente
pas ; T16-bis ne touche pas ce composant.** Aucun attribut `data-cadrage`, aucun libellé de cadrage :
cinq libellés dont le sens est en litige (Q15) n'ont rien à faire sur une carte.

### 4.5 Attributs de l'image

`wp_get_attachment_image( $plan_id, 'large', false, $attributs )` fournit `src`, `srcset`, `width`,
`height`. Le module impose en plus :

| Attribut | Valeur | Motif |
|---|---|---|
| `class` | `mtb-coordonnees-plan__image` | remplace les `attachment-large size-large` du cœur : un seul crochet |
| `alt` | `plan_description`, **`""` par défaut** | l'extension n'invente jamais d'alternative — voir §4.6 |
| `loading` | `lazy` | explicite : depuis WP 6.3 le cœur peut poser `eager` + `fetchpriority="high"` sur la première grande image, et un plan d'accès n'ouvre jamais une page |
| `decoding` | `async` | idem |
| `sizes` | **`(min-width: 42rem) 576px, 92vw`** | **dérivé, pas choisi** — le front a tranché le canal : **canal texte**, `--l-texte` = 36 rem = 576 px, atteint dès que la fenêtre dépasse 36 rem + 2 × `--marge-page`. `align: false` gèle cette valeur. |

Le balisage de `wp_get_attachment_image()` est émis **sans `wp_kses_post()`** : la liste blanche de kses
n'admet sur `img` ni `srcset`, ni `sizes`, ni `decoding`, et les supprimerait (vérification V6 du
contrat #8). La fonction échappe elle-même.

**Échappatoire unique**, sur le modèle de `mtb_bandeau_ouverture_attributs_image` :

```php
apply_filters( 'mtb_coordonnees_plan_attributs_image', array $attributs, int $plan_id, int $post_id ): array
```

Elle permet au thème de corriger `sizes` en une ligne sans toucher à l'extension. Elle ne peut ni retirer
la description (réappliquée après le filtre), ni changer l'image. **Aucun autre filtre** : le balisage
est le contrat.

### 4.6 Texte alternatif du plan

`alt=""` par défaut, pour une raison plus solide que « le texte adjacent porte l'information » : **on ne
peut pas écrire l'alternative d'une image qu'on n'a jamais vue.** Aucune image n'est fournie ; fabriquer
un `alt` par défaut serait l'invention que D11 interdit. `bandeau-ouverture/render.php` a déjà tranché ce
point pour le projet : l'extension lit ce qui est saisi et, à défaut, rend l'image décorative.

**Nuance à écrire dans la fiche d'aide** : un plan qui porte une information absente de l'adresse
— « entrée par le chemin après le pont » — **n'est pas décoratif** et exige une description (WCAG 1.1.1).
C'est ce que dit l'aide du champ.

### 4.7 Pièce jointe supprimée de la médiathèque

`plan_id` reste en base, la pièce jointe n'existe plus. Garde en quatre points, sur le modèle durci de
`galerie-photos/rendu.php` : `0 < $id` **et** `'attachment' === get_post_type( $id )` **et**
`wp_attachment_is_image( $id )` **et** `'' !== wp_get_attachment_image( … )`.

Un seul échec ⇒ `plan_id` retombe à `0` ⇒ **l'emplacement n'existe pas du tout** (§9.2). Ni cadre vide,
ni image cassée, ni avertissement, ni `<figure>` orpheline. Le reste du bloc rend normalement.

### 4.8 La mention d'attribution vient de la légende de la pièce jointe

`wp_get_attachment_caption()`, échappée par `esc_html()` comme `fiche-information/rendu.php` fait pour la
sienne. **Pas d'attribut de bloc concurrent** : deux sources pour une même ligne, dont l'une deviendrait
fausse.

Pourquoi la légende plutôt qu'une chaîne gravée dans le code : elle **voyage avec l'image**. Si le plan
est un jour remplacé par un dessin à la main, la mention part avec l'ancienne image au lieu de mentir sur
la nouvelle — même logique que le texte alternatif, déjà propriété de la pièce jointe.

Coût assumé et nommé : **incohérence d'interface avec `fiche-information`**, qui stocke sa légende dans un
attribut de bloc. Atténué par la fenêtre de la médiathèque, qui expose le champ *Légende* à droite au
moment même du choix, et par le paragraphe d'aide fixe du panneau. **Réserve ouverte** : une attribution
qui exigerait un **lien cliquable** ressortirait en texte, `esc_html()` la neutralisant. Passer à
`wp_kses_post()` serait une ligne, mais un écart avec le composant sœur : à trancher quand la nature du
plan sera connue. Sans image, la question est théorique.

---

## 5. États spéciaux

| État | Émis par le serveur | Rendu par le thème |
|---|---|---|
| `nominal` | tout ce qui est rempli, dans l'ordre du §4.1 | la mise en page complète |
| `donnee_absente` (un champ vidé) | **la paire `<dt>`+`<dd>` n'existe pas**. Jamais « Non renseigné » | l'espacement est porté par `margin-block-start` sur `.__etiquette` + `:first-child` à 0 : quel que soit le champ retiré, la mise en page reste juste. **Aucune règle ne dépend de la présence d'un champ.** |
| `coordonnees_absentes` (les trois vidés) | **rien du tout** : aucun élément, aucune racine, zéro octet — **même si un plan est choisi** | une page qui ne contient pas le composant |
| `plan_absent` | pas de `<figure>`, donc pas de `.__cadre` ni de `.__legende` | **§9.2 : aucun trou, aucune réserve.** La racine ne porte **ni grille, ni flex, ni gouttière** — rien ne peut laisser une colonne vide. La marge de séparation est portée par `.__figure` elle-même : absente, la marge est absente. **C'est le comportement nominal**, pas un mode dégradé. |
| `plan_supprime` | strictement identique à `plan_absent` (§4.7) | rien de plus |
| `attribution_absente` | pas de `<figcaption>` | une `<figure>` à un seul enfant ; `.__legende` ne porte que sa marge |
| `lien_impossible` | téléphone ou courriel rendu **en texte nu**, sans `<a>` | `.__lien` **peut ne pas exister** ; la valeur est alors un nœud de texte dans le `<dd>` |
| `image_qui_ne_charge_pas` | rien de particulier | le cadre garde son ratio et son fond `--calcaire-creux`, le texte `alt` s'affiche dedans en `--texte-doux` / `--t-sm` (§6.6). Aucun pictogramme cassé. |
| `etat_vide_editeur` | le balisage de §6, **uniquement dans l'éditeur** | **rien** — `editor.css` fait tout |

**Pourquoi « les trois vidés » l'emporte sur un plan choisi** : un bloc réduit à une image de carte, sans
une ligne d'adresse en texte, **échoue à D7**. La règle « les coordonnées sont le composant, le plan en
est l'accessoire » rend D7 vraie par construction, quel que soit le remplissage — et la phrase d'état
vide est alors littéralement exacte.

`page_protegee`, `aucune_portee` et `parent_hors_elevage` **ne concernent pas ce composant.**

---

## 6. État vide de l'éditeur — forme exacte, dette T13 soldée

```html
<div class="mtb-etat-vide">
  <span class="mtb-etat-vide__nom">Coordonnées et plan d'accès</span>
  <p class="mtb-etat-vide__phrase">Ce bloc n'affiche rien tant qu'aucune coordonnée n'est renseignée.</p>
</div>
```

- **`<span>` pour le nom, jamais `<p>`** : `editor.css` lui pose `display: block` et **ne remet pas les
  marges à zéro** — un `p` hériterait du `margin-block-end: var(--e-4)` de `base.css` et déséquilibrerait
  le cadre. Les six blocs existants émettent un `p` : **on ne les recopie pas**, c'est la dette **T13**
  qu'on solde ici.
- **Aucune classe modificatrice.** Pas de `mtb-coordonnees-plan__vide`. Écart délibéré avec les six
  sœurs, dans le sens de l'alignement.
- **Casse naturelle.** Les capitales viennent du `text-transform: uppercase` de `editor.css` ; tapées en
  dur, un lecteur d'écran les épellerait lettre à lettre.
- **`editor.css` n'est ni ouvert, ni modifié, ni dupliqué.** La feuille du bloc ne contient **aucune**
  règle d'état vide.
- « Vide » ne signifie **pas** « valeurs par défaut ». Vide = les trois champs vidés par l'éleveuse.

---

## 7. Chaînes fournies par le serveur

Le thème les **imprime**, il ne les compose jamais.

| Contexte | Chaîne exacte |
|---|---|
| Étiquette de l'adresse | `Adresse` |
| Étiquette du téléphone | `Téléphone` |
| Étiquette du courriel | `Courriel` |
| Nom du composant (insérteur, état vide) | `Coordonnées et plan d'accès` |
| Phrase d'état vide | `Ce bloc n'affiche rien tant qu'aucune coordonnée n'est renseignée.` |
| Adresse, téléphone, courriel | **recopiés tels que saisis** — aucun formatage, aucun regroupement, aucun préfixe |
| Attribution du plan | légende de la pièce jointe, telle quelle |

Aucune date n'est formatée par ce composant, aucun nom de discipline, aucun statut.

**Réserve** : `MASTER.md` §10 ne fige nulle part les étiquettes « Adresse », « Téléphone », « Courriel ».
Elles sont conformes à la voix du §10.1 et au mot « courriel » du brief §9, mais **§10 est l'arbitre du
vocabulaire** : l'ajout des trois lignes lui revient. Remonté à `lead-design-mtb` avec Q15 et T16-bis.

---

## 8. Ce que la feuille du thème couvre

Fichier unique : `wp-content/themes/mtb/assets/css/blocs/mtb-coordonnees-plan.css`, découvert
automatiquement par la boucle de `functions.php`. **Onze règles, ~31 déclarations, plafond dur 6,5 Ko
commentaires compris.**

| # | Sélecteur | Ce qu'il fait |
|---|---|---|
| 1 | `.mtb-coordonnees-plan` | `margin-block: var(--rythme-section)` — **et rien d'autre**. Pas de `display`, pas de `padding` : la fusion des marges avec un `h2` qui précède est le mécanisme, pas un accident |
| 2 | `.mtb-coordonnees-plan__coordonnees` | `font-style: normal` — **corrige l'italique par défaut de `<address>`** |
| 3 | `.mtb-coordonnees-plan__etiquette` | recette « étiquette » de §4.5 : `var(--sans)`, `600`, `var(--t-xs)`, `line-height: 1.4`, `text-transform: uppercase`, `letter-spacing: .16em`, `color: var(--laiton-texte)`, `margin-block-start: var(--e-5)` |
| 4 | `.mtb-coordonnees-plan__etiquette:first-child` | `margin-block-start: 0` |
| 5 | `.mtb-coordonnees-plan__valeur` | `margin-block-start: var(--e-3)` |
| 6 | `.mtb-coordonnees-plan__lien` | `display: inline-flex; align-items: center; min-block-size: 44px; overflow-wrap: break-word` |
| 7 | `.mtb-coordonnees-plan__figure` | `margin-block-start: var(--e-4)` |
| 8 | `.mtb-coordonnees-plan__cadre` | `inline-size: 100%` **puis** `aspect-ratio: var(--r-paysage)`, `background-color: var(--calcaire-creux)`, `color: var(--texte-doux)`, `font-size: var(--t-sm)`, `position: relative` |
| 9 | `.mtb-coordonnees-plan__cadre::after` | le cerne : `content:""; position:absolute; inset:0; border-radius: inherit; box-shadow: var(--cerne-photo); pointer-events: none` — **mot pour mot** la forme des cinq sœurs |
| 10 | `.mtb-coordonnees-plan__cadre.mtb-coordonnees-plan__cadre > img` | `display:block; inline-size:100%; block-size:100%; object-fit: contain` — **et pas une ligne de plus**. Sélecteur **doublé** (0,2,1) : dans la toile de l'éditeur le cœur préfixe les sélecteurs de `base.css`, ce qui porte `img` à (0,1,1) ; à sélecteur simple il y aurait égalité, tranchée par un ordre de source **inconnu** |
| 11 | `.mtb-coordonnees-plan__legende` | `margin-block-start: var(--e-2)` — **même valeur que `mtb-fiche-information.css`**, pour ne pas rouvrir une divergence de type T16-bis |

**Interdits dans cette feuille** :

- Aucun `max-block-size` sur le cadre — **jamais**. Sans plafond de hauteur, le mécanisme du piège de la
  décision 32 est physiquement inaccessible. `inline-size: 100%` est écrit **avant** `aspect-ratio`
  malgré tout, comme seconde ceinture : la largeur devient définie, le ratio ne peut plus calculer que la
  hauteur. L'interdiction est écrite **en toutes lettres dans la feuille**, avec son scénario de
  réouverture.
- Aucun `overflow: hidden` (§7.8 ; il rognerait aussi l'anneau de focus).
- Aucun `outline: none` (§13).
- Aucune règle `.mtb-etat-vide*`.
- Aucune règle `.mtb-photo` nue — ce serait une **troisième** copie d'une primitive servie
  conditionnellement, dans une feuille qui n'en est pas propriétaire.
- Aucun `:hover` porteur d'information ; aucune règle `@media print` (voir §11).
- Aucune valeur brute hors de `tokens.css`.
- Aucune largeur fixe, aucune longueur en `rem` sur un axe en ligne — c'est ce qui rend le débordement
  mesuré chez #13 au zoom du texte seul **structurellement impossible** ici.
- Aucun `order`, `flex-direction: row-reverse`, `grid-area` ou `position` déplaçant : **l'ordre du DOM
  est l'ordre visuel** (règle 2 du §4.3).

**Ce que la feuille ne réécrit pas** : la couleur, le soulignement, le survol et le focus des liens
viennent de `base.css` ; la typographie de la légende vient de la règle `figcaption` de `base.css` ;
l'anneau de focus est universel (`:focus-visible`).

> **Recette imposée au dev (décision 32)** — ouvrir la page Contact à **1440 px puis 2560 px** et lire
> `document.querySelector('.mtb-coordonnees-plan__cadre').getBoundingClientRect().width`. La valeur
> attendue est **exactement** celle de `.mtb-coordonnees-plan`. **Mesurer la racine du bloc ne prouve
> rien** : c'est l'erreur qui a survécu à six chaînes et à une revue complète sur #6.

---

## 9. Accessibilité — ce qui est garanti, et par quoi

| Exigence | Mécanisme |
|---|---|
| **D7** — localisation en texte, pas seulement sur l'image | les coordonnées sont du texte réel, **avant** le plan dans le DOM ; le bloc ne rend rien du tout si les trois champs sont vides, quel que soit le plan |
| Cibles ≥ 44 px dans les deux dimensions | `inline-flex` + `min-block-size: 44px` sur `.__lien`. `inline-flex` et non `block` : la cible épouse la largeur du texte — un lien `tel:` de 576 px de large déclencherait un appel sur un clic tombé à 400 px du numéro |
| Écart entre cibles ≥ `--e-2` | le `<dt>` interposé porte `--e-5` (24 px) + le `<dd>` `--e-3` (12 px) : **≥ 36 px réels** |
| Anneau de focus visible | `base.css` `:focus-visible`, universel. Garanti **non rogné** (aucun `overflow: hidden`) et **non supprimé** (aucun `outline: none`) ; la cible en `inline-flex` fait que l'anneau épouse la boîte de 44 px |
| Contraste AA | étiquette `--laiton-texte` sur `--calcaire` **5,30:1** · valeur `--texte` **12,03:1** · lien `--sauge-fonce` **7,73:1** · survol `--pin` **14,23:1** · légende `--texte-doux` **6,47:1** · texte de remplacement sur `--calcaire-creux` **5,79:1**. Toutes tabulées en §12, aucune paire nouvelle, aucun recalcul |
| Aucune information par la couleur seule | soulignement permanent du lien (§12.9) ; l'étiquette porte le **mot** « Téléphone » / « Courriel » |
| Plan de titres | le bloc n'émet **aucun titre** ; les `<dt>` sont des termes de liste, pas des titres |
| 360 px sans défilement horizontal | aucune largeur fixe d'aucune taille ; cadre en `inline-size: 100%` ; cibles à la largeur de leur contenu ; `overflow-wrap: break-word` sur le courriel |
| Zoom 200 %, texte seul compris | aucune longueur en `rem` sur un axe en ligne, aucune hauteur fixe, aucun `overflow: hidden` |
| Zéro octet de JavaScript public | `editorScript` seul, chargé en administration uniquement |

---

## 10. Assainissement et échappement

**Il n'existe aucun chemin d'écriture propre à ce module.** Les attributs arrivent dans `post_content`
par le chemin du cœur : `edit_post` / `edit_page` vérifiée par `wp_insert_post()`, nonce REST `wp_rest`
vérifié par `rest_cookie_check_errors()`. Le module n'ajoute **ni écran d'administration, ni route REST,
ni action AJAX, ni `update_option`** : il n'y a aucune écriture à laquelle poser un nonce de plus. Le
dire ainsi vaut mieux qu'inventer une garde décorative.

**L'assainissement a donc lieu au rendu**, sur des valeurs qui peuvent avoir été forgées à la main ou
importées par la reprise.

**Assainisseur retenu** : celui de `content/resultat/assainissement.php` (identique à
`content/portee/champs.php`), et **pas** celui de `content/chien/assainissement.php`. Deux raisons :
la variante « chien » n'appelle pas `wp_check_invalid_utf8()` avant un `preg_replace` portant le
modificateur `u` — sur une entrée mal encodée, `preg_replace` rend `null` et la branche de repli renvoie
la valeur **non nettoyée** ; et elle **remplace** les caractères de contrôle par une espace au lieu de les
supprimer, donc elle **injecte un caractère jamais tapé** dans une valeur recopiée.

**Ce que cela crée, et il faut le nommer** : une **quatrième copie** de l'assainisseur, à ajouter à la
ligne **T9** de `docs/ETAT.md`. Prix connu et assumé du parallélisme (décision 20).

**Une divergence déclarée** : les trois copies existantes aplatissent `\r\n\t` en espace. L'adresse étant
multiligne par conception, `texte_multiligne()` **normalise `\r\n` et `\r` en `\n` et préserve `\n``.
Ce n'est pas une divergence sur « ce qu'est une valeur propre » : c'est le même nettoyage sur un champ
dont le domaine autorise le retour à la ligne. `texte_ligne()`, employée pour le téléphone, le courriel
et la description, est **rigoureusement identique** à la variante de référence.

**Jamais `sanitize_text_field`, jamais `wp_strip_all_tags`, jamais `wp_kses`** sur une valeur recopiée —
décision 20 : elles passent par `strip_tags()` et videraient **en silence** une valeur commençant par `<`.

| Sortie | Barrière |
|---|---|
| Racine du bloc | `get_block_wrapper_attributes()` — échappe seule ; l'entourer d'`esc_attr()` doublerait l'encodage |
| Lignes d'adresse, étiquettes, téléphone, courriel | `esc_html()` |
| `href="tel:…"` et `href="mailto:…"` | `esc_url()` — `tel` et `mailto` figurent dans `wp_allowed_protocols()` |
| Classes et `data-*` | `esc_attr()` |
| `<img>` du plan | produit **et échappé** par `wp_get_attachment_image()`, émis **sans `wp_kses_post()`** (§4.5) |
| `alt` du plan | passé **brut** à `wp_get_attachment_image()`, qui applique `esc_attr()` — le pré-échapper ferait lire « `&#039;` » à une synthèse vocale |
| `<figcaption>` | `esc_html()` |
| Textes de l'éditeur | React échappe ; aucun `dangerouslySetInnerHTML` |

---

## 11. Interdits

- **Le thème n'interroge jamais la base** : ni `WP_Query`, ni `get_post_meta`, ni `$wpdb`, ni
  `wp_get_attachment_*`.
- **Aucun numéro de téléphone, adresse ou courriel écrit dans un fichier du thème.** Le pied de page
  appellera `mtb_coordonnees_plan_rendu()` ou `mtb_coordonnees_elevage()`, sous `function_exists()`.
- **Le thème ne compose jamais un `href="tel:"` ou `"mailto:"`** : la dérivation appartient aux fonctions
  du §2.
- **Le thème ne reformate jamais le numéro** — pas de regroupement en paires par `word-spacing`,
  `letter-spacing` ou `content:`, pas de `+33` ajouté.
- **Le thème n'ajoute aucune ponctuation** entre l'étiquette et la valeur.
- **L'extension n'émet aucune règle visuelle**, aucune feuille de style, aucune mise en page.
- **Aucune requête vers un domaine tiers** : aucune carte embarquée, aucun `<iframe>`, aucun tuilage,
  aucune police, aucune icône distante, aucun service de géocodage — au rendu comme en administration.
  Le site source appelle aujourd'hui `www.google.com` deux fois sur sa page Contact ; ce composant existe
  précisément pour que ce ne soit plus le cas.
- **Aucun lien « Itinéraire » vers un service de cartographie.** Non cliqué, il n'émet rien et D6 est
  tenue à la lettre ; **cliqué, il livre l'IP du visiteur** à un tiers sur un site dont l'argument entier
  est « zéro traceur, zéro bandeau ». L'alternative « zéro domaine », l'URI `geo:`, **ne fait rien** sur
  un ordinateur de bureau — un lien mort est pire qu'aucun lien. Arbitrage produit, rouvrable.
- **Aucun JSON-LD ni donnée structurée** ici : c'est une chaîne, jamais requêtée, donc sans problème D6 —
  mais le graphe appartient à l'issue `seo` (#24), et deux émetteurs concurrents sont une nuisance
  garantie.
- **Aucune règle `@media print`.** `MASTER.md` **ne traite pas l'impression et croit le faire** : §7.6
  renvoie à un « §9.6 » qui n'existe pas (le §9 s'arrête à §9.5), et le mot « impression » n'apparaît
  nulle part ailleurs. Inventer une feuille d'impression serait une décision visuelle qui n'appartient pas
  à cette chaîne. **Ce qui est déjà juste sans une ligne** : pile verticale, aucune colonne, aucune
  hauteur fixe, aucun `overflow: hidden` ; et le numéro comme le courriel étant **le texte du lien**, ils
  s'impriment en clair sans aucun `content: attr(href)`. Question remontée à `lead-design-mtb`.
- **Aucune image commitée dans le dépôt** — ni dans `assets/img/`, ni dans le dossier du bloc.

---

## 12. Empreinte fichiers de cette issue

**Extension** — `wp-content/plugins/mtb-core/includes/blocks/coordonnees-plan/` :
`block.json`, `bootstrap.php`, `coordonnees.php`, `rendu.php`, `render.php`, `interface.php`,
`editeur.js`.

**Thème** — `wp-content/themes/mtb/assets/css/blocs/mtb-coordonnees-plan.css` (fichier neuf, unique).

**Documentation** — `docs/guide/composant-coordonnees-plan.md`, `docs/contracts/issue-11.md`.

**Rien d'autre.** En particulier : `theme.json`, `functions.php`, `tokens.css`, `base.css`, `editor.css`,
`includes/blocks/categorie-mtb/` et tout autre dossier de `includes/blocks/` ne sont **pas** touchés.
Aucun `add_image_size`, aucune mise en file, aucun dequeue, aucune composition, aucun pattern, aucun
`register_block_style`.

**Décision 27** : `bootstrap.php` ne fait de `require_once` que vers des fichiers réellement écrits — un
`require` orphelin met tout le site en erreur fatale, `wp-admin` compris.

**Décision 23** : commit par `git commit -m "…" -- <chemins explicites>`, jamais `git add` puis `commit`
nu. L'index est partagé avec les chaînes sœurs.

---

## 13. Arbitrages — les désaccords entre les deux plans, et ce qui a été décidé

| # | Désaccord | Décision | Raison |
|---|---|---|---|
| 1 | **Structure des coordonnées.** Back : `<p class="__adresse">` + `<p class="__contact">` avec `<span class="__libelle">`. Front : `<dl>` / `<dt>` / `<dd>`. | **`<dl>`, celle du front** | `MASTER.md` §7.5 atteste le dispositif « liste de définition, libellé en étiquette laiton, valeur en corps » ; et retirer un champ retire **la paire entière**, donc aucune règle CSS ne dépend de la présence d'un champ. Extrapolation nommée : §7.5 l'atteste pour une fiche chien, je l'étends aux coordonnées. |
| 2 | **Étiquette de l'adresse.** Back n'en émettait pas ; le `<dl>` du front en exige une. | **`<dt>Adresse</dt>` est émise** | Symétrie de la liste de définition. Le site source lui-même libelle ses coordonnées (« Email : »). |
| 3 | **`object-fit` de l'image du plan.** Back : `contain`. Front : `cover` (§6.3, « le cadre gagne toujours »). | **`contain`, celle du back** | §6 est intitulé « Photographies » et argumente sur des chiens. Un plan est un **document** : le rogner peut couper le point d'arrivée, c'est-à-dire l'information qu'il porte. Et le bloc n'offrant aucun réglage de cadrage, l'éleveuse n'aurait aucun moyen de rattraper un rognage qu'elle n'a pas choisi. **Écart délibéré à §6.3**, signalé à la revue et à `lead-design-mtb`. |
| 4 | **Source de la légende d'attribution.** Back : légende de la pièce jointe. Front (tableau des réglages) : champ du bloc. | **Légende de la pièce jointe** | Elle voyage avec l'image et reste vraie si l'image est remplacée ; deux sources pour une même ligne en rendraient une fausse. Coût nommé en §4.8 : incohérence avec `fiche-information`. |
| 5 | **Modificateur `--avec-plan` sur la racine.** Back l'émettait ; le front ne le demande pas. | **Retiré** | Une classe qu'aucune règle ne cible est une classe morte, donc une orthographe qui dérivera. Le back l'avait lui-même proposé sous condition. |
| 6 | **Noms des crochets du plan.** Back : `__plan`, `__attribution`. Front : `__cadre`, `__legende`. | **Ceux du front** | Le front écrit la feuille et a nommé sa liste comme exhaustive. Un crochet non demandé habille du vide. |
| 7 | **`<span class="__ligne">` autour de chaque ligne d'adresse.** Back le voulait ; le front n'en veut pas. | **Retiré : `<br>` littéraux seuls** | Le front n'écrit rien dessus ; le span n'existait que pour se prémunir d'un `display: block` que le front s'interdit explicitement. Un crochet de moins. |
| 8 | **`sizes` de l'image.** Le back demandait au front de trancher le canal. | **`(min-width: 42rem) 576px, 92vw`** | Le front a tranché : **canal texte** (`--l-texte` = 36 rem = 576 px), §7.1 ne rangeant ce composant ni en canal large ni en canal pleine. `supports.align: false` gèle la valeur. |
| 9 | **`.mtb-photo` sur le cadre.** Les deux plans ont conclu **indépendamment** qu'il ne fallait pas. | **Confirmé : pas de `.mtb-photo`** | La primitive est servie conditionnellement par deux feuilles de blocs absents d'une page Contact. Décision 30 suppose une primitive domiciliée ; elle ne l'est pas. Point remonté à `lead-design-mtb`. |

---

## 14. Dettes créées ou touchées par cette issue

| # | Dette | Effet |
|---|---|---|
| **T19** *(numéro à confirmer — **partiellement caduque depuis l'arbitrage du §17** : la table de constantes est désormais **interne au module** et mourra avec lui, donc elle n'a plus vocation à être hissée ; seules les deux dérivations d'URI restent partageables. La dette est **remplacée** par l'issue « Coordonnées de l'élevage — écran de réglages unique ».)* | **Une table de constantes de l'élevage est domiciliée dans un dossier de bloc.** `includes/blocks/coordonnees-plan/coordonnees.php` déclare `mtb_coordonnees_elevage()` et ses deux dérivations. Sa place est `includes/query/`, hors du catalogue : le pied de page (epic Gabarits) et l'encart d'appel (#10) la consomment sans rien avoir à voir avec ce bloc, et si le bloc était retiré la fonction disparaîtrait avec lui. **Le fichier est écrit pour être déplacé tel quel** : espace de noms global, aucune dépendance vers le module, aucun hook. Parente de **T9**. | créée |
| **T9** | Une **quatrième copie** de `assainir_texte_recopie()`, avec la divergence multiligne déclarée au §10. | augmentée |
| **T13** | Forme d'état vide **rigoureusement** conforme à §9.1 (`<span>` pour le nom, aucune classe modificatrice). | soldée pour ce composant |
| **T12** | Le plan passe par la médiathèque, donc sous-tailles, `srcset` et format moderne. **Réserve** : l'image doit être téléversée **après** le module de #8, sinon elle n'aura pas de candidat moderne. | soldée sur cet objet |
| **T14** | `mtb-vignette-galerie` entre dans le `srcset` de toute image téléversée, celle du plan comprise. | rappelée |
| **T18** / **T16-bis** | Aucune septième copie d'`object-position` ; aucun réglage de cadrage. | **non aggravées** |
| **T15** | Aucune feuille servie depuis l'extension. | non reproduite |
| *(nouvelle, mineure)* | **`font-style: normal` sur `<address>` est un style d'élément qui appartient à `base.css`.** Il est écrit dans la feuille du bloc parce que `base.css` est partagé par les chaînes parallèles. **Le pied de page (#18) portera lui aussi un `<address>`** (§7.3) et redécouvrira le même défaut. Correctif propre : une ligne dans `base.css`, hors lot parallèle. | créée |

---

## 15. Questions bloquantes — aucun agent ne peut y répondre

| # | Question | Bloque |
|---|---|---|
| **Q-11-a** | **Lequel des deux points géographiques du site source est l'élevage ?** `43.514689, 6.242809` (carte principale, zoom 10) ou `43.533404, 6.248086` (carte latérale, zoom 16) — **environ 2 km les séparent**, et rien sur la page ne dit lequel désigne le 3060 Route de Salernes. Seule Fabienne peut trancher. | **toute production d'une image de plan.** Pas le composant, qui est livré complet. |
| **Q-11-b** | **Fabienne accepte-t-elle qu'un plan pointant son domicile soit publié ?** L'adresse en texte figure déjà sur le site actuel ; un point sur une carte n'est pas la même exposition. Décision qui lui appartient. | **l'existence même de la carte** |
| **Q-11-c** | **Qui fournit et téléverse l'image de plan, et sous quelle licence ?** Le rendu standard OpenStreetMap est sous CC-BY-SA et sa *Tile Usage Policy* encadre les exports ; la formulation exacte de l'attribution et l'obligation d'un lien vers la licence sont **à lire à la source**, pas à recopier de mémoire. La mention retenue devra être saisie dans la **légende** de la pièce jointe. | la mise en ligne de la carte, et le passage éventuel de `esc_html()` à `wp_kses_post()` sur la légende (§4.8) |

**Questions non bloquantes remontées à `lead-design-mtb`** : les étiquettes « Adresse / Téléphone /
Courriel » manquent au §10 (§7 ci-dessus) · `MASTER.md` §6 n'atteste rien pour une image qui n'est pas
une photographie de chien (§4.4b) · `.mtb-photo` est une primitive sans domicile (§4.4a) · §7.6 renvoie à
un §9.6 inexistant et l'impression n'est traitée nulle part (§11).

---

## 16. Amendements post-livraison — ratifiés le 2026-08-18

Quatre écarts au texte gelé, **tous mesurés**, tous ratifiés par la chaîne. Ils sont commentés dans le
code à l'endroit où ils se produisent.

| # | Écart | Mesure | Raison de la ratification |
|---|---|---|---|
| **A1** | `mtb_coordonnees_plan_rendu()` est domiciliée dans `interface.php`, non dans `coordonnees.php` comme l'écrit le §2 | — | Le §14 (dette **T19**) exige que `coordonnees.php` reste **déplaçable tel quel** vers `includes/query/` ; une fonction de rendu y créerait une dépendance vers `rendu.php`. C'est aussi la convention du dépôt (`fiche-information/interface.php`). Le §2 est amendé sur ce seul point : la fonction reste en espace de noms global, sous `function_exists()`, dans l'empreinte contractuelle. |
| **A2** | Règle 6 : `overflow-wrap: anywhere` au lieu de `break-word` (§8) | à 360 px sous **zoom du texte seul à 200 %** : `break-word` → `scrollWidth` **387** pour une fenêtre de 360 (lien courriel 355,41 px) ; `anywhere` → **345**. Mesure reproduite indépendamment par l'intégration. | `break-word` ne réduit pas la taille **min-content**, or l'`inline-flex` qu'exige la cible de 44 px se dimensionne sur son contenu. **La garantie « 360 px sans défilement horizontal » du §9 l'emporte sur la lettre d'une déclaration du §8.** Aucun effet sur un rendu normal : le courriel ne se coupe qu'à défaut de place. |
| **A3** | Règles 5 et 7 : classes **doublées** (le §8 ne double que la 10) | dans la toile de l'éditeur, sans le doublage : `dd` retombe à **0 px** et `figure` à **19 px** au lieu de 12 (`--e-3`) et 16 (`--e-4`) | La toile préfixe les règles d'élément de `base.css` (`p, …, figure { margin: 0 }` y pèse (0,1,1)). Sans le doublage, **l'éditeur mentirait sur le rendu**. Sans aucun effet sur le site. |
| **A4** | La feuille pèse **6 783 o** pour un plafond de 6 500 (§8) | mesuré | 283 o de dépassement, tous en commentaires que le §8 **exige lui-même** — notamment l'interdiction de `max-block-size` « écrite en toutes lettres, avec son scénario de réouverture ». Supprimer la justification pour tenir un budget de confort serait payer la mauvaise chose : le budget réel du brief (200 Ko par page) est intact, et cette feuille reste la 3ᵉ plus légère des huit. |

**Précision au §4.5, à ne pas prendre pour une dérive** : le rendu porte
`sizes="auto, (min-width: 42rem) 576px, 92vw"`. Le préfixe `auto, ` est ajouté par le cœur
(`wp-includes/media.php`, *auto-sizes* de toute image `loading="lazy"` depuis WP 6.7) ; la valeur du
contrat est présente **intacte** derrière. Comportement commun à `galerie-photos`, `fiche-information`
et `grille-chiens`.

**Point signalé, non corrigé, à arbitrer par `lead-design-mtb`** : dans l'état
`image_qui_ne_charge_pas`, le navigateur peint son **pictogramme d'image cassée** à gauche du texte de
remplacement. Le cadre, le ratio, le fond et l'encre sont conformes ; le glyphe est un rendu d'agent
utilisateur. Le supprimer exigerait `overflow: hidden` ou un `content:`, **tous deux interdits par le
§8**. La formule « aucun pictogramme cassé » de `MASTER.md` §6.6 n'est donc pas tenable à la lettre sans
rouvrir un interdit.

---

---

## 17. Arbitrage inter-chaînes — appliqué le 2026-08-18, après le commit `0e6d1d4`

L'orchestrateur du lot a tranché après la livraison : **la donnée « coordonnées de l'élevage »
n'appartient à aucune des deux chaînes du lot**, et sera livrée par une issue à venir
« Coordonnées de l'élevage — écran de réglages unique », qui fera basculer #10 et #11 d'un coup.

**Motif** : deux déclarations homonymes chargées par l'auto-découverte s'ombrent dans l'ordre
alphabétique des dossiers **sans lever d'erreur**, sur un site qui répond 200 — la panne serait
silencieuse et attribuée au mauvais module (décision 19).

### Ce qui a changé

| Point | Avant | Après |
|---|---|---|
| Table des coordonnées | `mtb_coordonnees_elevage()`, **globale**, annoncée au §2 et au §14 comme la fonction que #10 et #18 consommeraient | `MTB\Core\Blocks\CoordonneesPlan\coordonnees_elevage()`, **interne au module**, sans garde `function_exists()`. **Aucun autre module ne doit la lire.** C'est un **repli local**, exactement comme la constante `TELEPHONE_ELEVAGE` de `encart-appel/rendu.php` |
| Téléphone | littéral | `mtb_get_telephone_elevage()` sous `function_exists()`, **traitement défensif aligné mot pour mot sur celui de #10**, repli sur la constante locale. **La fonction n'est déclarée nulle part aujourd'hui** : le comportement observable est donc inchangé |
| `mtb_get_page_contact()` | — | **non consommée, délibérément** : ce composant n'a ni bouton ni lien vers une page Contact, l'appeler serait un couplage gratuit. Commenté comme tel pour qu'une revue n'y voie pas un oubli |
| `mtb_coordonnees_plan_rendu()` | globale | **inchangée** — c'est une fonction de **rendu** contractée pour #18, elle ne prétend posséder aucune donnée |
| `mtb_coordonnees_lien_telephone()` / `_lien_courriel()` | globales | **inchangées** — ce sont des dérivations d'URI, pas des lectures de la donnée d'élevage |

**Aucune fonction interdite n'a jamais été déclarée par ce module** : `mtb_get_telephone_elevage` et
`mtb_get_page_contact` n'y apparaissent qu'en **consommation**, jamais en déclaration. Vérifié par
`grep` sur tout `wp-content/`.

### Ce que cette chaîne ne tranche pas

**La forme du retour de `mtb_get_telephone_elevage()` reste ouverte** — `?string` nu, ou l'enveloppe
`array( 'libelle', 'valeur', 'affichage' )` de la décision 18. Elle appartient à l'issue qui
**déclarera** la fonction. Les deux formes sont acceptées défensivement, et le cas a été éprouvé en
déclarant la fonction à la volée (chaîne nue, enveloppe complète, tableau sans `valeur`, entier, `null`).

### Un point que l'issue de réglages devra trancher

`mtb_coordonnees_plan_rendu()` (pied de page #18) lira le numéro central **à chaque rendu**. Mais les
**défauts du bloc sont injectés à l'enregistrement** : un numéro central ne touchera que les blocs
**nouvellement insérés**, jamais les pages déjà enregistrées, dont les attributs sont sérialisés dans
`post_content`. C'est le fonctionnement contracté de #11 (V1), pas un effet de cet arbitrage — et c'est
exactement le piège que #10 évite en résolvant au rendu. **L'issue de réglages devra décider si ce
composant passe lui aussi à une résolution au rendu.**

### Divergence de libellé avec #10, assumée

#10 nomme son champ **« Téléphone affiché »**, avec l'aide « Laissez ce champ vide pour afficher le
numéro de l'élevage. » Ce composant garde **« Téléphone »**, et **ne s'aligne pas**.

Les sémantiques sont opposées et les deux sont justes dans leur contexte : chez #10, un champ vide
signifie *« affiche le numéro de l'élevage »* ; ici, un champ vide signifie *« cette ligne disparaît »*
(§9.3, décision 26). Adopter le libellé de #10 sur cette sémantique-ci mentirait à l'écran — et
surtout, la règle de repli de #10 rendrait **impossible** le retrait de la ligne « Téléphone » d'un bloc
de coordonnées, ce qui est un acte légitime sur une page Contact. **Conséquence pour le guide** : une
fiche qui couvrirait les deux composants ne doit pas les confondre.

---

## 18. Aperçu visuel — décision 33

Pris le 2026-08-18 dans la stack (Chrome sans interface, 1440 px), **regardés** et déposés dans
`docs/apercus/lot-4/` :

- `coordonnees-plan-sans-plan.png` — **l'état qui est livré aujourd'hui**, sans carte. Le bloc se
  termine proprement sur le courriel : aucun trou, aucune réserve, aucune marge orpheline. §9.2 tenu à
  l'œil, pas seulement au calcul.
- `coordonnees-plan-nominal.png` — avec un plan. Cadre mesuré **576 × 382 px**, soit **1,508** — le 3/2
  de `--r-paysage`.

**À savoir pour ne pas s'y tromper** : sur l'aperçu nominal, le cadre du plan paraît vide. Ce n'est
**pas** un défaut — l'image d'essai est un aplat pâle de 3 144 o, servi en **HTTP 200**, visuellement
indistinguable du fond `--calcaire-creux`. Le balisage est complet et conforme : `srcset` à quatre
candidats, `sizes`, `width`/`height`, `loading="lazy"`, `decoding="async"` et un `alt` rempli.

---

**Constat pour la reprise (#19-#21), hors périmètre de cette issue** : la page Contact du site source
porte deux paragraphes rédigés (« Le contact téléphonique avec la futur famille est très important pour
nous. » et « Aucune réservation ne se fera par mail ou par SMS... Un rendez vous à l'élevage sera un plus
dans notre décision de vous accorder notre confiance. ») et un formulaire à quatre champs. Ce contenu
**n'appartient pas à ce composant** et ne doit pas se perdre.
