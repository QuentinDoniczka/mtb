# Contrat d'interface — Issue #22 — Formulaire de contact

**Gelé le 2026-08-23.** Labels `contact`, `feature` · milestone « 9. Contact, page protégée, SEO/redirections ».

Cette issue touche **l'extension** (le module du bloc) **et le thème** (une seule feuille de style).
Elle ne crée **aucun** écran d'administration, **aucun** type de contenu, **aucune** table, **aucune**
option, **aucune** fonction globale nouvelle.

---

## 0. Ce que l'issue livre, en une phrase

Un composant `mtb/formulaire-contact` que l'éleveuse pose dans sa page Contact : trois champs (nom,
courriel, message), un anti-spam sans service tiers, un envoi par courriel vers l'adresse **déjà**
réglée par l'écran « Coordonnées » de #38, et une mention d'information qu'elle tape en place.

**Décision 45 (2026-08-21) est le cadre : le formulaire n'écrit rien en base.** Aucun schéma de
stockage, aucun écran de consultation, aucune purge, **aucun transient**, aucun cookie, aucun fichier
journal. Le message existe en mémoire PHP le temps d'une requête, puis nulle part.

Corollaire assumé, à écrire dans la fiche d'aide : **un courriel perdu est perdu.**

---

## 1. Emplacement — et le piège qui a failli coûter le module

### 1.1 `includes/contact/` est ABANDONNÉ

Le corps de l'issue et l'empreinte initiale nommaient `includes/contact/**`. **Ce dossier n'aurait
jamais été chargé.**

`includes/class-loader.php` porte une liste **close** de six groupes (constante `GROUPES` :
`content`, `fields`, `query`, `blocks`, `admin`, `migration`), et `charger_groupe()` ne parcourt que
`includes/<groupe>/`. Un dossier hors liste n'est **ni lu, ni journalisé, ni signalé** : le site
répond 200 et le formulaire n'existe pas. C'est la classe de panne de la décision 27, en pire —
silencieuse et permanente.

`class-loader.php` **n'est pas modifiable** : fichier partagé, trois chaînes tournent en parallèle sur
cet arbre sans isolation.

**Décision : tout le code de l'extension vit dans `includes/blocks/formulaire-contact/`**, module
auto-découvert du groupe `blocks`, dont le `bootstrap.php` requiert ses propres fichiers — exactement
ce que fait `includes/blocks/encart-appel/bootstrap.php` avec `rendu.php`.

L'empreinte de la chaîne est donc **plus étroite** que celle qui lui a été donnée. Aucun fichier hors
périmètre n'est touché.

### 1.2 Les fichiers livrés

```
wp-content/plugins/mtb-core/includes/blocks/formulaire-contact/
├── block.json
├── bootstrap.php        # require_once ×7, add_action init 20, add_action template_redirect 1
├── editeur.js           # administration seulement — zéro octet servi au visiteur
├── assainissement.php   # 4e copie de l'assainisseur de recopie (T9) + aides de la mention
├── destination.php      # destination(), destination_utilisable(), telephone_de_recours()
├── jeton.php            # creer(), verifier()
├── messages.php         # TOUTES les chaînes françaises
├── etat.php             # final class Etat — résultat de requête + compteur d'instances
├── traitement.php       # doit_traiter(), traiter(), valider(), envoyer()
├── rendu.php            # compose et échappe, rend des chaînes
└── render.php           # require NU — AUCUNE fonction, un seul echo

wp-content/themes/mtb/assets/css/blocs/mtb-formulaire-contact.css
```

**Le nom de la feuille est contractuel.** `functions.php:229-235` compose le chemin par
`str_replace('/','-', 'mtb/formulaire-contact') . '.css'` puis `continue` **en silence** si
`file_exists()` échoue. Une lettre d'écart donne un formulaire nu, sans erreur ni journal.
`functions.php` n'est **pas** modifié : la boucle est générique, et c'est ce qui rend cette chaîne
parallélisable.

---

## 2. Modèle de données

**Aucun.** Cette section est volontairement vide de stockage.

| Ce qui pourrait exister | Décision | Motif |
|---|---|---|
| Type de contenu « Message », table, option, méta | **non** | décision 45 |
| **Transient** (jeton, anti-rejeu, débit, transport d'état) | **non** | un transient est une écriture en base |
| Cookie, `session_start()` | **non** | zéro cookie au visiteur anonyme (brief §4) |
| Fichier journal des messages | **non** | ce serait une copie sous un autre nom |

**Lu, jamais écrit** : l'option `mtb_core_coordonnees`, **uniquement** à travers les fonctions
publiques ci-dessous. Le module n'appelle **jamais** `get_option()` lui-même (décision 19).

La seule donnée persistée est l'attribut `mention` du bloc, qui vit dans le `post_content` de la page.
C'est du contenu de page, pas une donnée d'élevage.

---

## 3. Fonctions de lecture consommées — jamais réimplémentées

Les trois sous garde `function_exists()`, toutes traitées défensivement (toute forme inattendue vaut
« rien »), pour qu'aucune page portant le bloc ne puisse tomber sur un `TypeError`.

```php
mtb_get_coordonnees_elevage(): array             // ['courriel']['valeur'] — le destinataire
mtb_get_telephone_elevage(): ?string             // recours quand le courriel manque
mtb_coordonnees_lien_courriel( string ): string  // mailto:, '' si non valide
```

> **INTERDIT ABSOLU, vérifiable par recherche :** aucun littéral d'adresse de courriel dans
> `includes/blocks/formulaire-contact/`. `includes/query/coordonnees/option.php:48` détient le **seul**
> littéral `mtbrabant@gmail.com` du dépôt. Un second exemplaire violerait la contrainte 3 du brief et
> ferait tomber la moitié « destination modifiable sans toucher un fichier » de **D1**.

**Cette issue n'expose AUCUNE fonction nouvelle**, aucun filtre, aucun hook. La surface globale reste
close (contrat #1 §5).

Elle n'expose pas non plus de fonction de rendu au thème : **le thème ne doit jamais rendre ce bloc
lui-même**, puisque le traitement dépend de `has_block()` sur le contenu de la page.

---

## 4. Bloc enregistré

### `mtb/formulaire-contact`

| Champ | Valeur |
|---|---|
| Nom dans l'insérteur | **Formulaire de contact** |
| Catégorie | `mtb` (« Mont Brabant ») |
| Icône | `email` |
| Description | « Un formulaire nom, courriel, message. Le message part par courriel à l'élevage ; il n'est pas enregistré sur le site. » |
| Mots-clés | `contact`, `formulaire`, `écrire`, `courriel`, `message`, `mail` |
| `usesContext` | `["postId"]` |
| `render` | `file:./render.php` |
| `editorScript` | `mtb-formulaire-contact-editeur` |

### 4.1 Attributs — un seul

| Clé | Type | Défaut |
|---|---|---|
| `mention` | `string` | `Votre message est envoyé par courriel à l'élevage. Il n'est pas enregistré sur ce site.` |

Filtré à la sortie par `wp_kses()` sur `a` (href, target, rel, data-type, data-id) et `br`, protocoles
`http`, `https`, `mailto`, `tel` — liste reprise de `bandeau-alerte/rendu.php`.

**Pourquoi ces deux phrases par défaut, et rien d'autre.** Ce ne sont pas une rédaction juridique :
ce sont **deux descriptions du comportement du code**, vraies ligne à ligne tant qu'aucun stockage
n'est ajouté, et vérifiables par lecture du module.

**Interdit d'écrire par défaut** : une durée de conservation (décision 45 : il n'y a pas de
conservation), un droit d'accès ou de suppression, un nom de responsable de traitement. Ce sont des
**engagements pris au nom de l'éleveuse** dans un texte à portée juridique, sur des faits que personne
dans ce dépôt ne connaît. Un nom de chien inventé se corrige ; un engagement RGPD inventé l'engage.

### 4.2 `supports` désactivés

```
align:false · alignWide:false · anchor:FALSE · ariaLabel:false · className:false ·
customClassName:false · html:false · lock:false · renaming:false · splitting:false ·
layout:false · position:false · border:false · shadow:false · inserter:true ·
multiple:FALSE · reusable:FALSE ·
color{background,text,link,gradients,button,enableContrastChecker}:false ·
typography{fontSize,lineHeight,textAlign}:false ·
spacing{margin,padding,blockGap}:false ·
dimensions{aspectRatio,minHeight}:false ·
background{backgroundImage,backgroundSize}:false · filter{duotone}:false
```

`anchor:false` est **indispensable** : l'ancre `id="mtb-formulaire-contact"` est fixée par le rendu et
fait partie de ce contrat ; une ancre saisie par l'éleveuse la casserait.

### 4.3 Aperçu dans l'éditeur — représentation statique, PAS `ServerSideRender`

Quatre motifs, dans l'ordre de poids :

1. **La mention se tape en place.** `ServerSideRender` rend une image morte : la mention y serait en
   lecture seule et l'unique réglage du composant deviendrait inatteignable. Ce point tranche à lui seul.
2. Un aperçu serveur injecterait un **`<form method="post">` vivant dans `wp-admin`**, où l'écran
   d'édition est lui-même un formulaire. Formulaires imbriqués : HTML invalide.
3. **Un jeton horodaté n'a aucun sens dans un aperçu** : re-frappé à chaque rendu, périmé avant d'avoir servi.
4. `ServerSideRender` passe par REST, où `get_queried_object()` est nul et `$_POST` appartient à la
   requête REST. Des chemins de code à garder pour zéro bénéfice.

La représentation statique **n'émet aucun `<form>`** ; ses champs sont de vrais `<input readonly>` /
`<textarea readonly>` **sans attribut `name`**. Elle porte **les mêmes crochets et les mêmes éléments**
que le rendu public — à la seule exception du `<form>`, remplacé par un `<div>` (voir §6.3).

### 4.4 État vide côté éditeur

Forme `MASTER.md` §9.1, réutilisant `.mtb-etat-vide`, `.mtb-etat-vide__nom`, `.mtb-etat-vide__phrase`
déjà habillées par `editor.css` : **zéro CSS d'éditeur nouveau**. Le nom du composant est émis en
**casse naturelle** — `editor.css:157` pose les capitales lui-même, et la raison y est écrite.

Les trois phrases, dans cet ordre de priorité :

1. destination absente → « Ce bloc n'affiche rien tant qu'aucun courriel n'est enregistré dans le menu Coordonnées. »
2. destination présente mais non valide → « Ce bloc n'affiche rien tant que l'adresse enregistrée dans le menu Coordonnées n'est pas une adresse de courriel valide. »
3. mention vide → « Ce bloc n'affiche rien tant que la mention d'information n'est pas écrite. »

> **ARBITRAGE — la formulation imposée à la chaîne était FAUSSE, et corrigée ici.** La consigne gelée
> disait « dans **Réglages → Coordonnées** ». **Vérifié dans le fichier** :
> `includes/admin/coordonnees/ecran.php:63-72` appelle `add_menu_page()` — un menu de **premier
> niveau** « Coordonnées », position 24 — et le commentaire l. 56-59 explique que `add_options_page()`
> a été délibérément refusé. La phrase aurait envoyé l'éleveuse dans un menu qu'elle n'a pas (elle n'a
> pas `manage_options`). C'est exactement la classe de défaut de la **décision 43**, attrapée avant
> l'écriture. **Retenu : « dans le menu Coordonnées ».**

**Écart déclaré à §9.1** : l'encadré est posé **au-dessus** de la représentation, jamais à sa place.
Motif : dans le cas 3, l'encadré cacherait le seul champ qui permet de le faire disparaître — un état
vide dont on ne peut pas sortir.

**Comment l'éditeur connaît l'état de la destination** : `wp_add_inline_script` sur la poignée de
`editeur.js`, calculé dans le rappel `init 20`, portant **l'état seul** (`presente|invalide|absente`)
— **l'adresse elle-même ne transite pas**, et aucun appel REST n'est ajouté. Valeur périmable : si
l'éleveuse change le réglage dans un autre onglet, elle recharge l'écran. À dire dans la fiche.

---

## 5. Le traitement

### 5.1 La garde de `template_redirect`, dans l'ordre

Accroche : `add_action( 'template_redirect', …, 1 )`. **Priorité 1** pour qu'aucun autre écouteur ne
puisse transformer le POST en redirection avant que le message ne parte.

```
1. 'POST' === strtoupper( $_SERVER['REQUEST_METHOD'] ?? '' )   sinon → retour
2. is_admin()                                                  → retour (ceinture)
3. is_singular()                                               sinon → retour
4. $post = get_queried_object(); $post instanceof WP_Post       sinon → retour
5. ! post_password_required( $post )                            sinon → retour
6. has_block( 'mtb/formulaire-contact', $post )                 sinon → retour
7. isset( $_POST['mtb_contact_action'] )                        sinon → cas 7 bis, puis retour
```

`has_block()` reçoit l'objet `WP_Post` **explicitement**, jamais `null` : le repli sur
`$GLOBALS['post']` marche aujourd'hui mais serait une dépendance implicite à un global, que le contrat
#1 §5 interdit.

**Cas 7 bis — le corps de requête perdu.** Si la méthode est POST, `$_POST` **vide** et
`$_SERVER['CONTENT_LENGTH'] > 0`, PHP a jeté le corps (dépassement de `post_max_size`). Sans ce cas,
la visiteuse reçoit un formulaire vierge et **son texte a disparu sans un mot**. On pose alors un
résultat `corps_perdu`. Six lignes, et c'est un refus muet de moins.

**Amendement déclaré au contrat #1 §2**, qui impose `init 20` au groupe `blocks` : l'enregistrement du
bloc reste sur `init 20` ; le traitement est un hook **supplémentaire**, unique dans le groupe. Écart
assumé et écrit, non fait en douce (leçon T32 / décision 46).

### 5.2 Le jeton — écart assumé à « nonce sur toute écriture »

Champ caché `mtb_contact_jeton`, valeur
`<horodatage>.<hash_hmac('sha256', horodatage, wp_salt('mtb-contact'))>`.

Vérification, dans cet ordre : format (exactement deux segments) → `ctype_digit()` sur le segment de
tête, longueur ≤ 12 → **`hash_equals()`**, comparaison à temps constant → âge `time() - t`, refusé si
`< 3 s` ou `> 3600 s`.

**Motif de l'écart à `CLAUDE.md`** :

- un formulaire public anonyme n'a **pas de session à protéger** et **n'écrit rien en base** : il n'y a
  pas de CSRF à empêcher, l'attaquant peut de toute façon poster directement ;
- le nonce ne sait pas exprimer un **âge minimal**, exigence explicite du brief §9 ;
- derrière un cache de page, un nonce périmé rend « Êtes-vous sûr de vouloir faire cela ? » — un
  message de sécurité incompréhensible pour le public du brief §2 ;
- le jeton, lui, se récupère : **jeton invalide ou expiré ne vide JAMAIS les champs**, le formulaire se
  réaffiche complet avec un jeton neuf et une phrase française, et **la réponse d'erreur est un POST,
  donc jamais cachée**.

**Deux limites écrites, non cachées :**

1. **Le jeton est rejouable dans son heure.** Il prouve « un formulaire a été servi à l'instant *t* »,
   pas « ceci est une première soumission ». Le rendre à usage unique exigerait de mémoriser les jetons
   consommés, donc d'écrire en base — refusé par la décision 45.
2. **Aucune limitation de débit n'est livrée**, même motif.

Garde : si `wp_salt()` rendait la chaîne vide, la vérification **échoue fermée**. Jamais d'acceptation
par défaut.

### 5.3 Le piège à robots

Champ `mtb_contact_reference` — nom délibérément hors des jetons que les navigateurs ciblent
(`telephone`, `site-web`, `url`, `adresse`, `société`, `code postal`). `autocomplete="off"`,
`tabindex="-1"`, conteneur `aria-hidden="true"`, étiquette « Ne remplissez pas ce champ. » pour le cas
où la feuille ne serait pas chargée.

> **ARBITRAGE — masquage : `display: none`.** Les deux plans s'opposaient. Le plan thème refusait
> `display: none` au motif qu'un robot sérieux lit le style calculé et saute un champ invisible ; le
> plan extension l'exigeait pour la règle axe `aria-hidden-focus`. **Retenu : `display: none`**, pour
> une raison qui prime les deux : c'est la recette qui **minimise les faux positifs sur un humain**,
> car elle empêche le remplissage automatique de la plupart des navigateurs. Or la décision 45 dit
> qu'un courriel perdu est perdu : un faux positif coûte un message humain définitivement détruit,
> pendant qu'un robot sophistiqué contourne **aussi** le masquage par découpe. L'objection du plan
> thème est réelle et **consignée** : elle vaut contre les robots qui lisent le CSS, lesquels
> contournent de toute façon les deux recettes.
>
> Effet de bord favorable : la dette **T-#22-a** (troisième copie de la recette de masquage
> accessible dans le thème) **n'est pas créée**.

> **ARBITRAGE — quand le piège se déclenche, ON NE MENT PAS.** Le plan thème proposait le rejet
> silencieux (rendre la confirmation), pour priver le robot de tout signal. **Refusé.** Afficher
> « Message envoyé. » à un humain dont le message n'existera plus nulle part est une **affirmation
> fausse**, et la décision 45 dit sans détour qu'un courriel perdu est perdu. Le formulaire se
> réaffiche **rempli**, jeton neuf, et l'encart d'information donne l'adresse de l'élevage en clair.
> Le texte affiché **ne nomme pas le piège** : le robot n'apprend pas quel champ l'a trahi.
> **Même traitement quand `wp_mail()` rend `false`.**

**Exposition déclarée** : un robot qui déclenche le piège reçoit l'adresse. Elle est **déjà publique
sur tout le site** — `coordonnees-plan` est au pied de page de toutes les pages depuis #18, en
`mailto:` non obfusqué. Aucune exposition nouvelle.

### 5.4 Assainissement — quatrième copie, T9 rallongée

`assainissement.php` reprend **la sémantique de `query/coordonnees/option.php:129-176`** :
`wp_check_invalid_utf8()`, normalisation des fins de ligne, **suppression** — jamais remplacement — de
`[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]`, `trim()`.

**Ni `sanitize_text_field`, ni `wp_strip_all_tags`, ni `wp_kses`, ni `sanitize_email` sur une valeur
recopiée** : toutes passent par `strip_tags()` et videraient **en silence** un nom de famille
commençant par `<` (décision 20). C'est sûr parce que l'échappement est systématique en sortie et que
le courriel part **en texte brut**, sans rien à interpréter.

**La variante copiée est nommée** : celle de `coordonnees` / `resultat` (suppression + contrôle
d'encodage), **pas** celle de `content/chien/assainissement.php:51` qui remplace par une espace et
n'appelle pas `wp_check_invalid_utf8()`. Trois définitions de « valeur propre » existent déjà ; une
quatrième **qui diverge** serait pire qu'une quatrième identique.

`wp_unslash()` sur **chaque** lecture de `$_POST`, avant tout — sans quoi une apostrophe dans un nom
français deviendrait `\'`.

### 5.5 L'injection d'en-tête — la priorité de sécurité du module

| Surface | Parade |
|---|---|
| `Reply-To` | L'adresse passe `assainir_ligne()` (CR/LF convertis en espace **avant** tout) **puis** `is_email()`. En-tête **nu** : `Reply-To: adresse` — **jamais** `Nom <adresse>` |
| `From` | **On n'y touche pas.** WordPress pose `wordpress@<domaine>`, seule adresse susceptible de passer SPF. **Aucun filtre `wp_mail_from` / `wp_mail_from_name`** : ils affecteraient tout le courrier du site, réinitialisations de mot de passe comprises. L'adresse de la visiteuse est **en plus recopiée dans le corps**, pour survivre à un `Reply-To` retiré par un relais |
| `Subject` | Contient le nom, **après `assainir_ligne()`** — CR/LF impossibles par construction — plafonné à 200 caractères |
| `$to` | `is_email()` **avant** l'appel. Une adresse non valide bascule en « destination inutilisable », jamais en formulaire qui échoue toujours |
| Corps | Texte brut, `Content-Type: text/plain; charset=UTF-8` posé explicitement. Aucun HTML, donc aucun `<` ne disparaît |

### 5.6 Le courriel

**Sujet** : `Message de <nom> — site de l'élevage`. Le nom y est parce que l'éleveuse trie son courrier
au téléphone ; un sujet constant lui donnerait vingt lignes identiques.

**Corps** :

```
Message reçu depuis le formulaire de contact du site.

Nom : <nom>
Courriel : <courriel>

Message :
<message>

---
Envoyé depuis <adresse de la page> le <date> à <heure>.
```

Date et heure : **deux appels `wp_date()` concaténés** — `wp_date('j F Y')`, `' à '`,
`wp_date('H\hi')`. **Jamais** `'j F Y \à H\hi'` : l'échappement de `date()` porte sur un octet, et
`\à` n'échappe que le premier des deux octets du « à ».

### 5.7 Le passage du résultat au rendu

`etat.php` déclare `final class Etat`, **entièrement statique et non instanciable**, constructeur
privé, comme `MTB\Core\Loader`. Aucune variable globale (contrat #1 §5).

| Membre | Rôle |
|---|---|
| `poser( array ): void` | appelé une seule fois, par `traiter()` |
| `resultat(): ?array` | rend le résultat **sans le consommer** |
| `prochaine_instance(): int` | incrémente et rend le rang de l'instance rendue |

Forme du résultat, clés **toujours toutes présentes** :

```
'post_id'  int
'issue'    'erreurs' | 'piege' | 'envoi_echoue' | 'destination_absente' | 'corps_perdu'
'globales' string[]
'champs'   array<'nom'|'courriel'|'message', string>   // clé absente = champ non fautif
'valeurs'  array{nom:string, courriel:string, message:string}  // jamais absentes
```

| Situation | Comportement |
|---|---|
| **Bloc absent** de la page postée | la garde 6 a rendu la main ; `Etat::resultat()` vaut `null` |
| **Bloc présent deux fois** | `render.php` appelle `prochaine_instance()` en première ligne ; **tout rang ≠ 1 rend `return;`** — rien, pas même une enveloppe. Deux `<form>` postant sur la même URL sont indiscernables du serveur, et les `id` seraient dupliqués |
| **Bloc rendu hors de la page postée** | `render.php` compare `Etat::resultat()['post_id']` au contenu courant (`$block->context['postId']`, repli `get_queried_object_id()`). En cas d'écart le résultat est **ignoré** et le formulaire se rend vierge. Jamais l'erreur d'une page sur une autre |

### 5.8 Le marqueur de succès et le cache

`wp_safe_redirect( add_query_arg( 'mtb_contact', 'envoye', get_permalink( $post ) ) . '#mtb-formulaire-contact', 303 ); exit;`

Nom préfixé, donc aucune collision avec une variable du cœur, et **aucune donnée personnelle dans
l'URL**.

`redirect_canonical` s'exclut des méthodes autres que GET/HEAD, donc le POST n'est pas en jeu. Le
risque porte sur la **requête GET de confirmation**. **Règle de décision écrite d'avance** (mesure V2) :

- si `?mtb_contact=envoye` **survit** → **ne rien livrer**, et écrire la mesure ici (décision 46 : un
  garde-fou qui ne garde rien est du code mort qui ment) ;
- s'il est **retiré** → `add_filter( 'redirect_canonical', '__return_false' )` posé **dans le seul
  rappel de la requête de confirmation**, jamais globalement.

**En-têtes de la réponse de confirmation ET de la réponse POST d'erreur** :
`nocache_headers()` — puis **vérifier que le `Cache-Control` émis contient `no-store`** (V3) ; et
`wp_robots_no_robots`.

**Aveu écrit** : `?mtb_contact=envoye` se tape à la main. **La confirmation est un écho d'URL, pas une
preuve d'envoi.** Sans conséquence, et inévitable dès lors que rien n'est stocké.

---

## 6. Le balisage rendu — gelé

### 6.1 Enveloppe commune

```html
<div class="wp-block-mtb-formulaire-contact mtb-formulaire-contact"
     id="mtb-formulaire-contact" tabindex="-1">
```

`tabindex="-1"` **n'est pas décoratif** : après un rechargement, l'ancre `#mtb-formulaire-contact` fait
de cette enveloppe le point de départ de la navigation séquentielle, donc la touche Tab suivante entre
dans le formulaire. C'est **le seul substitut sans JavaScript à une mise au focus**.

### 6.2 État 1 — formulaire vierge

```html
<div class="wp-block-mtb-formulaire-contact mtb-formulaire-contact" id="mtb-formulaire-contact" tabindex="-1">
  <form class="mtb-formulaire-contact__formulaire" method="post"
        action="https://…/contact/#mtb-formulaire-contact"
        accept-charset="UTF-8" novalidate aria-label="Formulaire de contact">

    <div class="mtb-formulaire-contact__champ">
      <label class="mtb-formulaire-contact__etiquette" for="mtb-contact-nom">Votre nom <span class="mtb-formulaire-contact__obligatoire">(obligatoire)</span></label>
      <input class="mtb-formulaire-contact__saisie" type="text" id="mtb-contact-nom" name="mtb_contact_nom" value="" autocomplete="name" required>
    </div>

    <div class="mtb-formulaire-contact__champ">
      <label class="mtb-formulaire-contact__etiquette" for="mtb-contact-courriel">Votre adresse de courriel <span class="mtb-formulaire-contact__obligatoire">(obligatoire)</span></label>
      <span class="mtb-formulaire-contact__aide" id="mtb-contact-courriel-aide">Pour que l'élevage puisse vous répondre.</span>
      <input class="mtb-formulaire-contact__saisie" type="email" id="mtb-contact-courriel" name="mtb_contact_courriel" value="" autocomplete="email" inputmode="email" required aria-describedby="mtb-contact-courriel-aide">
    </div>

    <div class="mtb-formulaire-contact__champ">
      <label class="mtb-formulaire-contact__etiquette" for="mtb-contact-message">Votre message <span class="mtb-formulaire-contact__obligatoire">(obligatoire)</span></label>
      <textarea class="mtb-formulaire-contact__saisie mtb-formulaire-contact__zone" id="mtb-contact-message" name="mtb_contact_message" rows="8" required></textarea>
    </div>

    <div class="mtb-formulaire-contact__piege" aria-hidden="true">
      <label for="mtb-contact-reference">Ne remplissez pas ce champ.</label>
      <input type="text" id="mtb-contact-reference" name="mtb_contact_reference" value="" autocomplete="off" tabindex="-1">
    </div>

    <input type="hidden" name="mtb_contact_action" value="envoi">
    <input type="hidden" name="mtb_contact_jeton" value="1755000000.3f9a…">

    <p class="mtb-formulaire-contact__mention">Votre message est envoyé par courriel à l'élevage. Il n'est pas enregistré sur ce site.</p>

    <div class="mtb-formulaire-contact__actions">
      <button type="submit">Envoyer le message</button>
    </div>
  </form>
</div>
```

Trois choix contractuels :

- **`novalidate` + `required`.** `required` garde la sémantique « obligatoire » pour les technologies
  d'assistance ; `novalidate` empêche la bulle native, pour que **le serveur reste la seule source des
  messages** (§10.3). **Conséquence : ne stylez JAMAIS `:invalid`, `:valid`, `:user-invalid`** — un
  champ obligatoire vide est `:invalid` dès le premier affichage, et le peindre en erreur accuserait la
  visiteuse avant qu'elle ait tapé un caractère.
  *`novalidate` **résout** la question Q-front-22-6 du plan thème : sans validation native, Firefox ne
  dessine pas son halo `:-moz-ui-invalid`, et il n'y a rien à neutraliser. À confirmer en V-front-15.*
- **`type="email"` et `inputmode="email"`** sont là pour le clavier des téléphones, pas pour valider.
- **Aucun `maxlength`.** Il tronquerait un collage en silence. Les plafonds (200 / 254 / 20 000) sont
  contrôlés côté serveur et **refusés avec une phrase**, jamais rognés.

### 6.3 Les éléments sont contractuels

| Crochet | Élément public | Élément dans l'aperçu d'éditeur |
|---|---|---|
| `__formulaire` | `<form>` | **`<div>`** — seule divergence admise |
| `__champ`, `__piege`, `__actions` | `<div>` | `<div>` |
| `__etiquette` | `<label>` | `<label>` |
| `__obligatoire`, `__aide` | `<span>` | `<span>` |
| `__saisie` | `<input>` / `<textarea>` | `<input readonly>` / `<textarea readonly>`, **sans `name`** |
| `__erreur` | `<strong>` | — |
| `__mention`, `__reprise` | `<p>` | `<p>` |

> **ARBITRAGE — `<div>` et non `<p>` pour `__champ` et `__actions`.** Le plan extension proposait
> `<p>`. Refusé : `base.css:223-225` donne au `p` un `margin-block-end: --e-4` qui se battrait avec la
> gouttière de grille, et la toile de l'éditeur **préfixe les règles d'élément** en les portant à
> (0,1,1), ce qui obligerait la feuille à doubler ses classes pour reprendre la main — mécanisme que le
> plan thème documente en détail. Le `<div>` supprime le problème au lieu de le contourner.

> **ARBITRAGE — la règle « aucun sélecteur qualifié par l'élément » est RESTREINTE à `__formulaire`.**
> Le plan extension la posait en interdit général. Trop large : elle interdirait `textarea.__saisie`,
> nécessaire et parfaitement stable. Le tableau ci-dessus fige les éléments ; seul `__formulaire` change
> entre le public et l'aperçu. **La feuille ne doit donc jamais écrire `form.mtb-formulaire-contact__formulaire`.**

### 6.4 État 2 — formulaire en erreur (rendu en place, même requête POST)

```html
<div class="wp-block-mtb-formulaire-contact mtb-formulaire-contact" id="mtb-formulaire-contact" tabindex="-1">

  <div class="mtb-formulaire-contact__resume">
    <h2 class="mtb-formulaire-contact__resume-titre">Votre message n'a pas été envoyé.</h2>
    <ul>
      <li><a href="#mtb-contact-courriel">Erreur : cette adresse de courriel n'est pas valide. Vérifiez qu'elle est de la forme nom@exemple.fr.</a></li>
      <li><a href="#mtb-contact-message">Erreur : écrivez votre message.</a></li>
      <li>Erreur : cette page est restée ouverte plus d'une heure. Cliquez de nouveau sur le bouton Envoyer le message : vos réponses sont conservées.</li>
    </ul>
  </div>

  <form class="mtb-formulaire-contact__formulaire" …>
    …
    <div class="mtb-formulaire-contact__champ">
      <label class="mtb-formulaire-contact__etiquette" for="mtb-contact-courriel">Votre adresse de courriel <span class="mtb-formulaire-contact__obligatoire">(obligatoire)</span></label>
      <span class="mtb-formulaire-contact__aide" id="mtb-contact-courriel-aide">Pour que l'élevage puisse vous répondre.</span>
      <input class="mtb-formulaire-contact__saisie" type="email" id="mtb-contact-courriel" name="mtb_contact_courriel"
             value="sophie@" autocomplete="email" inputmode="email" required
             aria-invalid="true"
             aria-describedby="mtb-contact-courriel-erreur mtb-contact-courriel-aide">
      <strong class="mtb-formulaire-contact__erreur" id="mtb-contact-courriel-erreur">Erreur : cette adresse de courriel n'est pas valide. Vérifiez qu'elle est de la forme nom@exemple.fr.</strong>
    </div>
    …
  </form>
</div>
```

- **Les trois valeurs sont toujours rappelées**, relues de `$_POST`, assainies, échappées par
  `esc_attr()` (inputs) et **`esc_textarea()`** (zone).
- **Un jeton neuf** est émis à chaque réaffichage.
- Une erreur **globale** (jeton, délai) est une `<li>` **sans lien**.
- **Pas de `role="alert"`** : c'est du contenu de document initial, non une mise à jour dynamique ;
  l'annonce au chargement est incohérente d'un lecteur d'écran à l'autre. Le dispositif retenu est le
  couple **ancre + `tabindex="-1"` + titre**.
- Le second signal exigé par §12.9 est **le préfixe « Erreur : » écrit dans le texte**, jamais un
  `::before` : un `content:` disparaît de la copie de texte et de certains modes de restitution.
- Le bord 2 px `--oxyde` est **déjà** livré par `base.css:426-431` via `[aria-invalid="true"]`.

> **ARBITRAGE — le `<h2>` du résumé est CONSERVÉ.** Le plan thème le refusait, au motif qu'un titre
> qui apparaît et disparaît selon la requête rend le plan de titres instable. Objection recevable mais
> non retenue : la page a **réellement** un contenu différent, le motif « titre + `tabindex="-1"` +
> ancre » est le patron de référence d'un résumé d'erreurs sans JavaScript, et sans titre l'encart n'a
> pas de nom accessible.
>
> **Conséquence que ce `<h2>` entraîne, et que ni l'un ni l'autre des plans n'avait vue** :
> `base.css` applique à **tout `<h2>`** le filet double en segment de `6rem` (§2.1, « Sous chaque
> `<h2>` »). Le filet apparaîtrait donc **à l'intérieur** de l'encart de résumé — emplacement absent de
> la liste close du §2.1, qui interdit de surcroît le filet « à l'intérieur d'un bloc qui en porte déjà
> un ». **La feuille DOIT neutraliser le filet** sur `__resume-titre` et sur `__information-titre`
> (`background-image: none` et reprise du `padding-block-end`), en **surcharge de contexte** au sens de
> la décision 30, et l'écrire en commentaire. **Vérification V-front-17.**

### 6.5 État 3 — confirmation après envoi (requête GET, après 303)

```html
<div class="wp-block-mtb-formulaire-contact mtb-formulaire-contact" id="mtb-formulaire-contact" tabindex="-1">
  <div class="mtb-formulaire-contact__confirmation">
    <p><strong>Message envoyé.</strong> Votre message a été envoyé par courriel à l'élevage.</p>
  </div>
  <p class="mtb-formulaire-contact__reprise"><a href="https://…/contact/#mtb-formulaire-contact">Écrire un autre message</a></p>
</div>
```

**Le formulaire n'est pas re-rendu** (Q-back-3, ratifiée) : ses champs seraient vides — rien n'est
stocké — et un formulaire vierge sous « Message envoyé. » invite à un second envoi qu'aucune
déduplication ne pourrait absorber.

Deux phrases et pas une de plus : **aucune promesse de réponse**. « L'élevage vous répondra » serait un
engagement pris au nom de Fabienne.

### 6.6 État 4 — piège déclenché, ou envoi échoué

```html
<div class="wp-block-mtb-formulaire-contact mtb-formulaire-contact" id="mtb-formulaire-contact" tabindex="-1">
  <div class="mtb-formulaire-contact__information">
    <h2 class="mtb-formulaire-contact__information-titre">Votre message n'a pas pu être envoyé.</h2>
    <p class="mtb-formulaire-contact__information-texte">Erreur : le courriel n'a pas pu partir du site.</p>
    <p class="mtb-formulaire-contact__information-recours">Vous pouvez écrire directement à <a href="mailto:…">…@….com</a>, en recopiant votre message ci-dessous.</p>
    <p class="mtb-formulaire-contact__information-recours">Vous pouvez aussi appeler l'élevage au <a href="tel:…">…</a>.</p>
  </div>
  <form class="mtb-formulaire-contact__formulaire" …>   <!-- rempli, jeton neuf -->
</div>
```

| Cause | Texte de `__information-texte` |
|---|---|
| piège déclenché | Erreur : ce message n'a pas pu être envoyé. |
| `wp_mail()` a rendu `false` | Erreur : le courriel n'a pas pu partir du site. |
| destination absente ou non valide au moment de l'envoi | Erreur : le site n'a plus d'adresse de destination enregistrée. |
| corps de requête perdu | Erreur : ce message n'a pas pu être reçu — il dépasse la taille acceptée par le serveur. Votre texte n'est pas arrivé jusqu'au site. |

La ligne de recours **courriel** disparaît sans adresse ; la ligne **téléphone** disparaît sans numéro.
Dans le cas « corps perdu », « en recopiant votre message ci-dessous » est retiré — **il n'y a rien
ci-dessous**, et l'écrire serait faux.

**Exception déclarée à la décision 26** : c'est le seul cas où le bloc s'affiche alors que la
destination est inutilisable. Motif : une visiteuse qui vient de cliquer ne doit jamais recevoir le
silence (dette T26).

### 6.7 État 5 — bloc non rendu

**Rien.** Pas d'enveloppe, pas de commentaire HTML, pas de marge fantôme. `return;` nu dans
`render.php`. Aucune règle de la feuille ne doit supposer la présence du bloc.

### 6.8 Table de décision du rendu public

1. rang d'instance ≠ 1 → rien
2. marqueur `?mtb_contact=envoye` présent → **État 3**
3. résultat posé, `post_id` concordant → **État 2** ou **État 4** selon `issue`
4. destination inutilisable **ou** mention vide → rien
5. permalien vide → rien
6. sinon → **État 1**

---

## 7. Crochets de classes — LISTE CLOSE

> **L'ARBITRAGE CENTRAL DE CETTE ISSUE.** Les deux plans ont employé **`__champ` pour deux choses
> différentes** : le groupe étiquette+champ côté extension, l'élément de saisie côté thème. C'est
> exactement le mode d'échec que le travail en parallèle produit, et il n'aurait levé **aucune erreur** :
> la feuille aurait habillé un conteneur en croyant habiller un champ.
>
> **Tranché : `__champ` = le groupe. `__saisie` = l'élément de saisie.** Le vocabulaire suit celui de
> l'éleveuse (§10.2) : « le champ Nom » désigne l'étiquette et sa case, pas la case seule.

> **Règle opposable (décision 46).** Chaque crochet de cette liste doit, dans
> `mtb-formulaire-contact.css`, **soit porter au moins une déclaration, soit être nommé dans un
> commentaire comme délibérément non stylé.** Un crochet ni stylé ni commenté est un fait faux en
> attente. **Réciproquement : le serveur n'émet AUCUN crochet absent de cette liste.**

| # | Crochet | Élément | Ce qu'il porte |
|---|---|---|---|
| 1 | `mtb-formulaire-contact` | `div` | rythme vertical du composant dans la page |
| 2 | `__formulaire` | `form` / `div` | écart vertical entre groupes. **Jamais qualifié par l'élément** |
| 3 | `__champ` | `div` | **le GROUPE** étiquette + aide + saisie + erreur ; écart interne |
| 4 | `__etiquette` | `label` | §8.5 : étiquette visible au-dessus, `display:block`, graisse |
| 5 | `__obligatoire` | `span` | « (obligatoire) » en `--texte-doux`, `--t-sm`. **Jamais un astérisque** |
| 6 | `__aide` | `span` | `display:block`, `--t-sm`, `--texte-doux` |
| 7 | `__saisie` | `input` / `textarea` | **largeur uniquement**. Hauteur, fond, bord, rayon, focus, bord d'erreur : **déjà dans `base.css:393-431`** |
| 8 | `__zone` | `textarea` | hauteur minimale, `resize: vertical` |
| 9 | `__erreur` | `strong` | `display:block`, `--oxyde`, écart au-dessus. Le préfixe est **dans le texte** |
| 10 | `__mention` | `p` | `--t-sm`, `--texte-doux`, écarts |
| 11 | `__actions` | `div` | **empêche l'étirement du bouton** sur toute la grille |
| 12 | `__piege` | `div` | **`display: none`** — crochet fonctionnel : sans lui, « Ne remplissez pas ce champ. » s'affiche à la visiteuse |
| 13 | `__resume` | `div` | encart de résumé : `--calcaire-creux`, `--r-0`, `--e-6`. **Aucun filet double** |
| 14 | `__resume-titre` | `h2` | titre du résumé **+ neutralisation du filet double hérité de `base.css`** |
| 15 | `__confirmation` | `div` | **§9.5** : `--calcaire-creux`, **filet double vertical `--sauge`**, pas de coche verte |
| 16 | `__reprise` | `p` | lien « Écrire un autre message » |
| 17 | `__information` | `div` | encart d'envoi impossible : `--calcaire-creux`, **aucun filet double**, **distinguable de la confirmation autrement que par la couleur** |
| 18 | `__information-titre` | `h2` | **+ neutralisation du filet double** |
| 19 | `__information-texte` | `p` | message préfixé « Erreur : », `--oxyde` |
| 20 | `__information-recours` | `p` | ligne(s) de recours ; `overflow-wrap: break-word` pour l'adresse ; cible ≥ 44 px sur les liens, par sélecteur descendant |

**Crochets d'éditeur : aucun de nouveau.** Réemploi de `.mtb-etat-vide`, `__nom`, `__phrase`.

### 7.1 Crochets DEMANDÉS PAR UN PLAN ET RETIRÉS — avec le motif

Le plan extension en proposait **31**. Onze sont retirés : un crochet qui ne porte rien est une dette,
pas une facilité.

| Retiré | Motif |
|---|---|
| `__champ--nom`, `--courriel`, `--message` | aucune largeur différenciée n'est prescrite par `MASTER.md`. Les trois champs occupent le canal texte. En inventer trois serait une décision de design non ratifiée |
| `__champ--en-erreur` | ne porte rien : le signal est sur la saisie (`aria-invalid`, `base.css`) et dans le texte du `<strong>` |
| `__envoi` | `base.css:349-389` habille déjà le bouton ; l'étirement est empêché par `__actions` (#11). Aucune déclaration ne resterait |
| `__resume-liste`, `__resume-ligne` | sélecteurs descendants (`.__resume ul`, `.__resume li + li`) suffisent et coûtent zéro crochet |
| `__confirmation-texte`, `__confirmation-prefixe` | `<strong>` porte déjà sa graisse ; la marge du dernier enfant se règle par `.__confirmation > :last-child` |
| `__information-adresse`, `__information-telephone` | remplacés par `.__information-recours a` — un sélecteur, deux besoins (césure et cible tactile) |

**Si `dev-ux-mtb` démontre qu'un de ces crochets est indispensable, c'est un amendement à ce contrat,
écrit et daté — jamais une classe ajoutée en silence.**

---

## 8. Attributs d'accessibilité émis

| Attribut | Où | Valeur |
|---|---|---|
| `id="mtb-formulaire-contact"` | enveloppe | **ancre fixe et contractuelle** : cible du `action`, du `Location:` du 303, des liens de reprise |
| `tabindex="-1"` | enveloppe | point de départ de la navigation séquentielle après saut d'ancre |
| `aria-label="Formulaire de contact"` | `form` | nomme le repère `form` ; reprend le titre du bloc |
| `novalidate` | `form` | le serveur est la seule source des messages |
| `required` | les 3 saisies | sémantique « obligatoire » |
| `aria-invalid="true"` | saisie en erreur **seulement** | **jamais `="false"`** : l'absence vaut « valide ». Déclenche le bord `--oxyde` de `base.css:426` |
| `aria-describedby` | saisie concernée | `"<id>-erreur <id>-aide"` — **l'erreur en premier** |
| `id="mtb-contact-<champ>-erreur"` / `-aide` | `strong` / `span` | cibles du `describedby` |
| `id="mtb-contact-nom\|courriel\|message"` | les 3 saisies | cibles des `for` **et** des liens du résumé |
| `aria-hidden="true"` + `tabindex="-1"` | conteneur du piège / sa saisie | **ensemble**, sans quoi axe lève `aria-hidden-focus` |
| `autocomplete` | `name`, `email`, `off` | |
| `role` | **aucun** | pas de `role="alert"` — motif au §6.4 |

---

## 9. Chaînes fournies par le serveur — toutes

Le thème n'en compose aucune, n'en accorde aucune, n'en reformate aucune, n'en préfixe aucune en CSS.
Elles vivent toutes dans `messages.php`.

**Interface** — `Votre nom` · `Votre adresse de courriel` · `Votre message` · `(obligatoire)` ·
`Pour que l'élevage puisse vous répondre.` · `Envoyer le message` (§10.3, figé) ·
`Ne remplissez pas ce champ.` · `Formulaire de contact`

**Résumé** — `Votre message n'a pas été envoyé.`

**Erreurs de champ**, toutes préfixées `Erreur : `
- `Erreur : indiquez votre nom.`
- `Erreur : votre nom dépasse 200 caractères.`
- `Erreur : indiquez votre adresse de courriel.`
- `Erreur : cette adresse de courriel n'est pas valide. Vérifiez qu'elle est de la forme nom@exemple.fr.`
- `Erreur : cette adresse de courriel dépasse 254 caractères.`
- `Erreur : écrivez votre message.`
- `Erreur : votre message dépasse 20 000 caractères. Raccourcissez-le, ou écrivez directement à <adresse>.`

**Erreurs globales**
- `Erreur : le formulaire n'a pas pu être vérifié. Cliquez de nouveau sur le bouton Envoyer le message : vos réponses sont conservées.`
- `Erreur : cette page est restée ouverte plus d'une heure. Cliquez de nouveau sur le bouton Envoyer le message : vos réponses sont conservées.`
- `Erreur : le message est parti moins de trois secondes après l'ouverture de la page. Cliquez de nouveau sur le bouton Envoyer le message : vos réponses sont conservées.`

**Envoi impossible** — `Votre message n'a pas pu être envoyé.` · les quatre textes de cause (§6.6) ·
`Vous pouvez écrire directement à <adresse>, en recopiant votre message ci-dessous.` ·
`Vous pouvez écrire directement à <adresse>.` · `Vous pouvez aussi appeler l'élevage au <numéro>.`

**Confirmation** — `Message envoyé.` (§10.3, figé) ·
`Votre message a été envoyé par courriel à l'élevage.` · `Écrire un autre message`

**Éditeur** — `Formulaire de contact` (casse naturelle) · les trois phrases d'état vide (§4.4) ·
`Écrivez ici la mention d'information.`

**Mention par défaut** — `Votre message est envoyé par courriel à l'élevage. Il n'est pas enregistré sur ce site.`

**Courriel** — sujet et corps du §5.6.

---

## 10. États spéciaux

| État | Émis par le serveur | Rendu par le thème |
|---|---|---|
| `vierge` | destination utilisable, mention écrite, aucun POST | formulaire complet |
| `erreurs` | validation échouée | résumé + marquage par champ, **valeurs rappelées** |
| `confirmation` | après 303, marqueur en URL | encart §9.5, **sans formulaire** |
| `envoi_impossible` | piège, `wp_mail()` faux, destination perdue, corps perdu | encart d'information **+ formulaire rempli** |
| `masque` | destination inutilisable ou mention vide, hors POST | **rien du tout** |
| `page_protegee` | mot de passe non saisi | rien : le cœur ne rend pas le contenu ; le POST est refusé (garde 5) |
| `aucune_portee`, `donnee_absente`, `parent_hors_elevage` | **sans objet** | — |

---

## 11. Interdits — vérifiables par recherche

**Le thème ne doit jamais** : interroger la base ni écrire `MTB\` · appeler une fonction de rendu de ce
module · composer une chaîne (« Erreur : » en `::before`, « (obligatoire) », une adresse) · reformater
l'adresse, le numéro ou la mention · styler `:invalid`, `:valid`, `:user-invalid` · redéclarer ce que
`base.css` porte déjà (48 px, fond, bord, rayon, focus, `[aria-invalid]`, bouton) · qualifier
`__formulaire` par son élément · masquer le piège autrement que par `display: none` · ajouter un octet
de JavaScript public.

**L'extension ne doit jamais** : émettre une règle visuelle · écrire un littéral d'adresse · déclarer
une fonction dans `render.php` · poser un filtre `wp_mail_from` / `wp_mail_from_name` · écrire en base ·
poser un cookie · appeler `session_start()`.

---

## 12. Ce qui n'est délibérément PAS livré

| Non livré | Motif |
|---|---|
| Le bouton désactivé « Envoi en cours… » (**§8.4**) | exige du JavaScript ; zéro octet de JS public, mesuré à trois lots. **Conséquence écrite : un double-clic envoie deux courriels identiques.** Dédupliquer exigerait d'écrire en base (décision 45) |
| Toute déduplication, toute limitation de débit, un jeton à usage unique | même motif |
| La « question simple » du brief §9 | le brief dit « si nécessaire » ; ce serait un 4ᵉ champ contre « nom, courriel, message, rien d'autre », un CAPTCHA au sens de WCAG 1.1.1 sans solution de remplacement pour un public qui compte des personnes âgées, et **aucun spam n'a été mesuré** |
| **La durée de conservation, le droit d'accès et de suppression** (brief §9) | décision 45 supprime la durée ; les droits et le responsable de traitement sont des **engagements pris au nom de l'éleveuse** que personne ici ne peut prendre. **Question ouverte Q-22-1, déclarée et non comblée** |
| Un accusé de réception à la visiteuse | non demandé ; second envoi, seconde surface d'injection |
| Un volet de réglages, un compteur de caractères, une fonction de lecture, un filtre | surface non demandée |

---

## 13. Écarts déclarés — à ratifier

1. **Pas de nonce**, jeton HMAC horodaté (§5.2).
2. **`template_redirect` priorité 1** : amendement au contrat #1 §2.
3. **T9 rallongée** : 4ᵉ copie de l'assainisseur, variante nommée (§5.4).
4. Seconde copie de `est_vide` / `balises_admises` / `protocoles_admis` depuis `bandeau-alerte`.
5. **§9.1** : encadré d'état vide **au-dessus** de la représentation.
6. **La phrase d'état vide dit « dans le menu Coordonnées »**, pas « Réglages → » — la consigne était fausse (§4.4).
7. Une adresse enregistrée mais non valide au sens d'`is_email()` est traitée comme **absente**.
8. **Exception à la décision 26** : sur un POST, le bloc rend l'encart d'information même destination inutilisable.
9. **§8.4** : état désactivé non livré ; double-clic = deux courriels.
10. **Brief §9** : la mention est **au-dessus du bouton** (« sous les champs ») et non littéralement « sous le formulaire ».
11. **La confirmation est un écho d'URL**, reproductible par quiconque tape le paramètre.
12. **Le jeton est rejouable** dans son heure ; aucune limitation de débit.
13. **L'encart d'information affiche l'adresse** à qui déclenche le piège — déjà publique au pied de page depuis #18.
14. **`is_email()` refuse les adresses internationalisées** (partie locale non ASCII).
15. **Plafonds serveur** 200 / 254 / 20 000, refusés avec une phrase, jamais rognés.
16. **§2.1 contre §9.5** : le filet double vertical de la confirmation n'est pas dans la liste close. **§9.5 appliqué**, écart consigné (Q-22-5).
17. **§3.2 contre §12.3** : `--oxyde` sur `--calcaire-creux` absent du tableau des cinq fonds, mais tabulé **6,04:1 AA** au §12.3. **Employé**, écart consigné (Q-22-4).
18. **Le filet double du `<h2>` est neutralisé** dans les deux encarts (§6.4). Surcharge de contexte, décision 30.
19. **La feuille de bloc est mise en file au rendu du bloc**, y compris sur une page où il ne rend rien — coût à chiffrer (V12).

---

## 14. Arbitrages — chaque désaccord entre les deux plans, la décision, sa raison

| # | Désaccord | Décision | Raison |
|---|---|---|---|
| **A1** | **`__champ`** : le groupe (back) ou l'élément de saisie (front) | **le GROUPE** ; l'élément devient **`__saisie`** | La collision la plus dangereuse du lot : aucune erreur levée, une feuille qui habille un conteneur en croyant habiller un champ. Le vocabulaire suit celui de l'éleveuse (§10.2) |
| **A2** | 31 crochets (back) contre 10 (front) | **20**, liste close, 11 retirés avec motif (§7.1) | Décision 46 : un crochet non stylé est un fait faux en attente. Les 3 modificateurs de largeur auraient inventé une décision de design non ratifiée |
| **A3** | Étiquette et bouton **sans classe** (front) ou avec (back) | **`__etiquette` OUI, `__envoi` NON** | `base.css` ne style aucun `label` et une règle `label` nue fuirait sur toute la page ; il habille en revanche déjà `button` entièrement |
| **A4** | Groupe en `<p>` (back) ou `<div>` (front) | **`<div>`** | Le `p` de `base.css:223` se bat avec la gouttière de grille, et la toile de l'éditeur préfixe les règles d'élément à (0,1,1) |
| **A5** | Interdit **général** de qualifier par l'élément (back) | **restreint à `__formulaire`** | Trop large : interdirait `textarea.__saisie`, stable et utile. Les éléments sont figés au §6.3 |
| **A6** | `<h2>` de résumé : refusé (front) / posé (back) | **conservé** | Patron de référence d'un résumé d'erreurs sans JS ; sans titre l'encart n'a pas de nom accessible. **Et cela a révélé le filet double hérité, que ni l'un ni l'autre n'avait vu** |
| **A7** | Piège : masquage par découpe (front) ou `display:none` (back) | **`display: none`** | Minimise les **faux positifs sur un humain** — le seul coût irréversible (décision 45). L'objection de front vaut contre des robots qui contournent **aussi** la découpe. Effet de bord : la dette T-#22-a n'est pas créée |
| **A8** | Piège déclenché : confirmation silencieuse (front) ou refus explicite (back) | **refus explicite, sans nommer le piège** | Afficher « Message envoyé. » à un humain dont le message est détruit est une **affirmation fausse**. Front l'admettait lui-même en citant la décision 45 |
| **A9** | Échec d'envoi : réemploi de `__resume` (front) ou `__information` (back) | **`__information`**, 4 crochets au lieu de 5 | Sémantiquement distinct (il porte des recours, pas des erreurs de saisie) et il doit être **distinguable autrement que par la couleur** (§12.9) |
| **A10** | La phrase d'état vide « Réglages → Coordonnées » | **« dans le menu Coordonnées »** | **Vérifié** : `add_menu_page()`, menu de premier niveau. La consigne gelée était fausse (décision 43) |
| **A11** | Q-front-22-6, halo `:-moz-ui-invalid` de Firefox | **sans objet** | `novalidate` désarme la validation native : il n'y a rien à neutraliser. **À confirmer en V-front-15** |
| **A12** | Q-back-3, le formulaire après un envoi réussi | **non re-rendu** | Champs vides + invitation à un second envoi non déduplicable |

---

## 15. Vérifications exigées avant de rendre la main

Aucune ne s'affirme sans avoir été faite ; **une vérification non faite se dit.** Décision 29 :
`WP_DEBUG` est vrai en web, faux en WP-CLI — « aucune notice » n'a de sens que sur **page rendue**.
Pile : http://localhost:3005, page « Contact » **ID 4**.

**Extension** — V1 `template_redirect` ne court pas en REST · V2 `redirect_canonical` face à
`?mtb_contact=envoye`, **résultat écrit au contrat dans les deux cas** · V3 `Cache-Control` contient
`no-store` + `noindex` · V4 **aucun `Set-Cookie`** sur les trois réponses · V5 **0 octet de JS**, 0
requête tierce · V6 envoi nominal : 303, encart, **courriel dans Mailpit** avec bon sujet, bon
`Reply-To`, adresse recopiée dans le corps · V7 les cinq états rendus · V8 **injection d'en-tête** :
`Sophie\r\nBcc: x@y.z`, `a@b.c%0aBcc:…`, un nom commençant par `<` (**doit arriver intact**), une
apostrophe (**ne doit pas devenir `\'`**) · V9 deux blocs collés · V10 jeton à 1 s / falsifié / vieux
d'une heure, **les trois champs toujours remplis** · V11 courriel vidé dans « Coordonnées » · V12
poids · V13 axe-core, **`aria-hidden-focus` nommément** · V14 contrastes **lus sur pixels rendus**
(décision 36) · V15 360 px, zoom 200 %, clavier · V16 Tab depuis `#mtb-formulaire-contact` entre bien
dans le formulaire · V17 le piège avec la feuille désactivée : l'étiquette est lisible · V18 bloc posé
dans un **élément de gabarit** → confirme que `has_block()` rend faux, **limite écrite dans la fiche
avant qu'elle ne la découvre** · V19 **zéro notice PHP** sur les cinq états · V21
`grep -r 'mtbrabant@gmail.com'` → **zéro** · V22 `grep 'function ' render.php` → **zéro** · V23 les 20
crochets, chacun **stylé ou commenté**.

**Thème** — V-front-1 la feuille atteint la page (`.mtb-formulaire-contact{` dans le HTML) ·
V-front-2 décision 46 dans les **deux** sens · V-front-15 **`novalidate` désarme bien le halo Firefox**
(A11) · V-front-17 **le filet double du `<h2>` est bien neutralisé dans les deux encarts** ·
V-front-18 le résumé et l'encart d'information sont **distinguables autrement que par la couleur** ·
`grep` : zéro `#`, zéro `px` hors liste close, zéro `url(`, zéro `!important`, zéro `content:`.

**Fiche d'aide** — V20 : **chaque affirmation vérifiée à l'écran** (décision 43), y compris « le
message n'est pas enregistré », « un double-clic envoie deux courriels » et **« un courriel perdu est
perdu »**.

---

## 16. Dettes créées

| # | Dette | À payer par |
|---|---|---|
| **T-#22-a** | ~~3ᵉ copie de la recette de masquage accessible~~ **NON CRÉÉE** — `display: none` retenu (A7) | — |
| **T-#22-b** | **`base.css:426-431` ne couvre pas `select`** : un futur formulaire à liste déroulante n'aurait aucune bordure d'erreur, **sans le moindre signe**. Sans effet ici (aucun `<select>`) | issue `a11y` |
| **T-#22-c** | **T9 rallongée** : 4ᵉ copie de l'assainisseur de recopie. Variante nommée pour qu'elle ne diverge pas | issue de consolidation T9 |
| **T-#22-d** | `__erreur` et `__aide` sont scopés faute de primitive nommée par `MASTER.md`. Au 2ᵉ formulaire du site, **les hisser plutôt que les recopier** (décision 30, leçon T-#15-a) | le lot qui crée un second formulaire |
| **T-#22-e** | **`docs/guide/coordonnees-modifier-les-coordonnees.md` devient faux par ricochet** : il enseigne « Laissez vide pour retirer le courriel de tout le site » sans savoir que ce geste **fait disparaître le formulaire de contact**. Fiche de #38, **hors de l'empreinte de cette chaîne** — non modifiée | `/lead-mtb`, en clôture de lot |
| **T-#22-f** | **La remise réelle du courriel n'est pas prouvable dans ce lot** : Mailpit accepte tout. Un envoi d'essai vers la boîte de Fabienne reste dû **à la mise en ligne**, et sans copie en base personne ne saura qu'un message manque | à la mise en production (lié à Q5) |
| **T-#22-g** | **`docs/contracts/issue-1.md` §11 doit recevoir une ligne** `blocks / formulaire-contact / #22`. Hors empreinte | `/lead-mtb` à la clôture |

---

## 17. Questions ouvertes

**Questions de domaine bloquantes pour le CODE : aucune.** La conception est faite pour qu'aucun fait
inconnu n'ait à être inventé : la mention par défaut ne décrit que le comportement du code, et tout ce
qui relève d'un engagement est laissé à l'éleveuse dans une zone qu'elle édite.

| # | Question | Pour qui |
|---|---|---|
| **Q-22-1** | **Le droit d'accès et de suppression, et le responsable de traitement.** Le brief §9 les exige ; la décision 45 les rend paradoxaux — le site ne détient rien, les messages ne vivent que dans la boîte de Fabienne. Écrire « vous pouvez demander la suppression » l'engagerait à vider sa boîte sur demande. **Rien n'est écrit par défaut.** Il faut soit sa phrase exacte, soit l'arbitrage « la zone reste libre, elle la remplit ». Aggravant : `ETAT.md` note que la **raison sociale et le siège social** manquent toujours des Mentions légales, donc l'identité du responsable n'est écrite nulle part dans le dépôt | **utilisateur / éleveuse** |
| **Q-22-2** | **Y a-t-il une adresse de courriel sur le futur domaine**, ou l'élevage reste-t-il sur `mtbrabant@gmail.com` ? Conditionne la délivrabilité, donc tout : sans copie en base, un message non délivré est perdu et le site ne peut pas le savoir. Liée à Q5 (hébergement) | **utilisateur** |
| **Q-22-3** | **Clore #22 avec une remise vérifiée uniquement contre Mailpit** (+ dette T-#22-f), ou #22 dépend-elle de Q5 ? | **utilisateur** |
| **Q-22-4** | **§3.2 contre §12.3** sur `--oxyde` / `--calcaire-creux` (6,04:1, AA). Employé ; le tableau des cinq fonds doit gagner une ligne | `lead-design-mtb` |
| **Q-22-5** | **§9.5 contre §2.1** : le filet double vertical de la confirmation n'est pas dans la liste close, qui écrit même « où il n'apparaît jamais : … champs de formulaire ». §9.5 appliqué | `lead-design-mtb` |
| **Q-22-6** | **`MASTER.md` ne décrit aucun résumé d'erreurs en tête de formulaire**, ni aucun espacement de formulaire (§5.1), ni de ligne §4.5 pour l'étiquette et la note d'aide. Valeurs **dérivées** de règles voisines, à ratifier | `lead-design-mtb` |
| **Q-22-7** | **Un champ invalide *et* focalisé garde le bord `--oxyde`** : `base.css:426` suit `base.css:420` à spécificité égale, alors que §8.5 annonce « au focus, bord porté à `--sauge` ». Le comportement observé paraît le bon — l'anneau dit le focus, la bordure dit l'erreur — mais §8.5 ne le dit pas | `lead-design-mtb` |
| **Q-22-8** | **Le résumé et l'encart d'information sont deux encarts `--calcaire-creux`.** Ils ne coexistent jamais, mais §12.9 exige qu'ils ne se distinguent pas par la seule couleur — ils se distinguent par leurs mots et par le filet de la confirmation. Suffisant ? | `lead-design-mtb` |

---

## 18. Mesures faites APRÈS le gel — addendum du 2026-08-23

Le §5.8 exigeait que le résultat de V2 revienne au contrat **dans les deux cas**. Le voici, avec les
deux autres mesures qui changent une clause. Le texte gelé au-dessus n'est pas réécrit : ce qui suit
le corrige explicitement.

| Mesure | Attendu au gel | **Mesuré sur la pile** | Conséquence |
|---|---|---|---|
| **V2 — `redirect_canonical` face à `?mtb_contact=envoye`** | inconnu, règle de décision écrite d'avance | **le paramètre SURVIT** : `200`, aucun `Location`, `num_redirects=0` | **Aucun garde-fou livré.** Il n'existe aucun `add_filter('redirect_canonical', …)` dans le module. Un garde-fou qui ne garde rien serait du code mort qui ment (décision 46) |
| **V3 — `nocache_headers()` émet-il `no-store` ?** | à vérifier, filtre de secours prévu | **oui** : `Cache-Control: no-cache, must-revalidate, max-age=0, no-store, private`, sur la confirmation GET **et** sur les réponses POST d'erreur | **Aucun filtre `nocache_headers` livré.** `noindex` présent sur la confirmation, **absent** de la page nue : la pose est bien ciblée |
| **V1 — `template_redirect` en REST** | attendu « ne court pas », fondé sur le mécanisme | **confirmé par sonde** : 5 requêtes, le hook n'est atteint sur **aucune** des 3 requêtes REST | L'aperçu d'éditeur ne peut rien traiter |
| **Écart 19 — la feuille est-elle mise en file quand le bloc ne rend rien ?** | supposé oui, coût à chiffrer | **INFIRMÉ** : la feuille **n'est pas** mise en file du tout. Page sans bloc **19 166 o**, avec bloc **37 381 o** | **L'écart 19 est SANS OBJET.** Coût nul, rien à optimiser |

### 18.1 Une classe hors liste close, légitime et à ne pas styler

L'enveloppe porte **`wp-block-mtb-formulaire-contact`** en plus du crochet `mtb-formulaire-contact`.
Ce n'est **pas** un crochet de style : c'est la classe de bloc du cœur, que le §6.1 fige dans le
balisage et que `supports.className:false` (§4.2) empêche le cœur de poser — elle est donc écrite à la
main. **La feuille ne la style pas**, et ne doit pas la styler.

### 18.2 Écarts d'implémentation ratifiés après coup

Neuf écarts mineurs au §1.2 et au §5.1, tous internes au module et **sans fonction globale nouvelle** :
noms `jeton_creer()` / `jeton_verifier()` ; `destination.php` expose sept fonctions au lieu de trois
(les quatre autres sont exigées ailleurs par le contrat) ; consommation d'une **quatrième** fonction de
lecture publique, `mtb_coordonnees_lien_telephone()`, sous garde `function_exists()` — la recomposer
aurait fait une troisième copie de la recette `tel:` dans le dépôt ; `preparer_confirmation()` appelée
avant la garde 1 pour poser les en-têtes du §5.8, **toutes les autres gardes étant rejouées** ;
ordre du traitement piège → destination → jeton + validation → envoi ; pas de clé `example` dans
`block.json` ; bouton de l'aperçu en `type="button"` ; sur un POST une mention vide ne masque pas le
bloc (il faut répondre à la visiteuse) ; repli sur permalien vide.

### 18.3 Ce qui n'a été vérifié par PERSONNE dans cette chaîne

**À exécuter par la passe d'intégration de lot** — aucune de ces lignes ne doit être présentée comme
faite :

- **axe-core sur les cinq états**, et nommément la règle **`aria-hidden-focus`** sur le piège. La
  précondition structurelle est en place (`aria-hidden="true"` sur le conteneur **et** `tabindex="-1"`
  sur la saisie, appariés), mais **la règle n'a pas été exécutée**.
- **Le parcours clavier complet** sur page rendue, et **V16** (Tab depuis `#mtb-formulaire-contact`
  entre bien dans le formulaire). Le dispositif est vérifié **dans le balisage**, pas à l'usage.
- **V17** — le piège avec la feuille désactivée : l'étiquette est bien émise en texte, mais le rendu
  n'a pas été regardé.
- **V-front-15** — `novalidate` désarme-t-il bien le halo `:-moz-ui-invalid` **sous Firefox** ? Aucun
  Firefox disponible. Ce qui est sûr : la feuille ne contient aucun sélecteur `:invalid` / `:valid` /
  `:user-invalid`.
- **La toile de l'éditeur** n'a pas été regardée par la chaîne UX : la discipline de spécificité
  (classe doublée) est écrite et justifiée, **non vérifiée à l'écran**.
- **V18** — le bloc n'a pas été posé dans un **vrai** élément de gabarit ; la mesure décisive
  (`has_block()` faux hors `post_content`, POST refusé) a été faite, le bout-en-bout non.
- **La logique d'expéditeur (`From:`) est INVÉRIFIABLE en local** : `docker/provision/provision.sh`
  filtre `wp_mail_from` vers `no-reply@mtbrabant.local`, ce qui écrase tout `From:`. Mailpit prouve le
  `Reply-To:` et le corps, **jamais** l'expéditeur.

### 18.4 Défaut de pile trouvé pendant l'écriture — hors empreinte, NON corrigé

`/etc/msmtprc` est livré **`root:root 0600`** dans le conteneur `wordpress`. `www-data` ne peut donc
pas le lire, et **tout `wp_mail()` déclenché par une requête web échoue** (`msmtp: account default not
found: no configuration file available`). Le formulaire affiche alors, correctement, « Erreur : le
courriel n'a pas pu partir du site. »

Un `chmod 0644` a été posé **dans le conteneur en marche** pour permettre la vérification V6, et il
saute à chaque recréation de la pile. **Rien n'a été écrit dans `docker/`** — c'est hors de l'empreinte
de cette chaîne. **À corriger par `docker-mtb`** : sans cela, aucun courriel ne part du site en local.

---

## 19. Amendements du 2026-08-23, après la passe de refacto

### 19.1 §3 — il y a QUATRE fonctions de lecture consommées, pas trois

Le §3 en listait trois. Le module en consomme une quatrième, et c'est **une ligne qui manquait au
contrat**, pas un défaut de code :

```php
mtb_coordonnees_lien_telephone( string ): string   // le lien tel: du §6.6, '' si inutilisable
```

Déclarée par `includes/blocks/coordonnees-plan/coordonnees.php`, consommée sous garde
`function_exists()`. La recomposer aurait fait une **troisième** copie de la recette `tel:` dans le
dépôt — exactement ce que la décision 19 refuse. **Ratifiée.**

### 19.2 §4.4 — le script en ligne peut porter les LIBELLÉS, jamais l'adresse

**Le problème, trouvé par la refacto.** `editeur.js` porte **douze chaînes françaises en dur**, dont
**huit dupliquent mot pour mot** des constantes de `messages.php` (`Votre nom`, `Votre adresse de
courriel`, `Votre message`, `(obligatoire)`, `Pour que l'élevage puisse vous répondre.`, `Ne
remplissez pas ce champ.`, `Envoyer le message`, `Formulaire de contact`).

Le §9 exige que **toutes** les chaînes vivent dans `messages.php`. Le §4.4, tel que gelé, laissait
croire que le script en ligne ne pouvait porter que l'état de destination. Les deux ne pouvaient pas
être satisfaits ensemble.

**Le risque est réel et silencieux** : une retouche d'un libellé dans `messages.php` laisserait
l'aperçu d'éditeur afficher l'ancien, **sans erreur ni journal**. L'éleveuse verrait dans son écran
d'édition un mot que le site n'emploie plus. C'est la classe de défaut de la décision 46 — une
divergence en attente — et de la décision 43 — un écran qui ment.

**Amendement.** Le `wp_add_inline_script` du rappel `init 20` porte désormais **l'état de destination
ET les libellés d'interface**, tous lus de `messages.php`, source unique. L'interdit du §4.4 est
**précisé, pas levé** :

> **L'adresse de l'élevage ne transite jamais vers l'éditeur.** Seuls transitent l'**état**
> (`presente|invalide|absente`) et des **libellés d'interface**. Aucune donnée de coordonnées, aucune
> adresse, aucun numéro. Et toujours **aucun appel REST supplémentaire**.

**Exception admise, à ne pas « corriger »** : le `title` de `block.json` reste un troisième exemplaire
de « Formulaire de contact ». C'est une frontière JSON/PHP/JS que le cœur impose — `block.json` est lu
avant tout PHP du module. Consigné en dette T-#22-h plutôt que contourné.

### 19.3 Corrections de refacto ratifiées

Six, toutes à comportement identique : un garde-fou `function_exists('wp_salt')` **qui ne gardait
rien** (retiré, avec un commentaire disant pourquoi ne pas le remettre) · une branche **inatteignable**
dans `telephone_de_recours()` dont le commentaire était en outre **faux** sur le contrat #38 · le même
commentaire inexact sur `destination_brute()` (code laissé, c'est une vraie ceinture) ·
`horodatage_lisible()`, dernière chaîne française PHP hors de `messages.php`, **déplacée** ·
`'#mtb-formulaire-contact'` en littéral remplacé par `'#' . ANCRE` (une divergence future aurait fait
atterrir le 303 sur une ancre inexistante, **sans erreur**) · la **liste close des valeurs brutes** de
la feuille rendue exacte : elle annonçait `44px` seule alors que la règle 4 pose `font-weight: 600`.

> **AUCUNE de ces six corrections n'a été exécutée.** La passe de refacto n'avait pas de shell. Cinq
> d'entre elles touchent du PHP, dans quatre fichiers. **Elles doivent être re-mesurées sur la pile
> avant tout commit** — c'est la première tâche de la jonction front↔back.

---

## 20. Amendement du 2026-08-23, après la jonction front↔back

### 20.1 §7 crochet 8 — « hauteur minimale » est RETIRÉE du contrat

Le §7 demandait à `__zone` de porter « hauteur minimale, `resize: vertical` ». **La hauteur minimale
n'est pas livrée, et elle est retirée de l'exigence** — ce n'est pas une dérive, c'est une ligne du
contrat qui demandait l'impossible :

- `MASTER.md` **ne chiffre aucune hauteur de zone de saisie**. En inventer une serait précisément la
  valeur brute qu'interdit le §13 ;
- une hauteur fixe **casserait le zoom 200 %** du §7.8, dont `base.css:415-417` dépend explicitement
  (« aucune hauteur fixe : `rows` dimensionne la zone, et le texte croît sans être rogné »).

La hauteur vient donc de `rows="8"` (contractuel, §6.2) et du plancher de 48 px de `base.css`.
**Mesurée** : 243 px à 360 px de large, 250 px à 640 px, sans débordement. `__zone` reste stylé par
`resize: vertical`, qui empêche l'élargissement au-delà du canal (§7.7).

### 20.2 Ce que la jonction a mesuré, et qui clôt des lignes du §18.3

| Ligne du §18.3 | Statut | Mesure |
|---|---|---|
| **axe-core sur les cinq états** | **CLOSE** | axe-core 4.13 : **0 violation** sur les états 1, 2, 3 et 4. L'état 5 ne rend aucun nœud |
| **`aria-hidden-focus`** | **CLOSE** | exécutée en `runOnly` : `{violations:0, passes:1}` sur les états 1, 2 et 4 ; `inapplicable` sur l'état 3. La règle **passe**, elle n'est plus « structurellement préparée » |
| **V-front-1** | **CLOSE** | 25 lignes de sélecteurs `.mtb-formulaire-contact*` dans le HTML de `/contact/`, incorporées en ligne |
| **V16 — Tab depuis l'ancre** | **CLOSE** | `activeElement` = l'enveloppe, **premier Tab → `mtb-contact-nom`**. Le substitut sans JavaScript **fonctionne réellement** |
| **Parcours clavier** | **CLOSE** | `nom → courriel → message → bouton → liens du pied`. **Le piège n'est jamais atteint** |
| **V17 — piège sans la feuille** | **CLOSE** | étiquette « Ne remplissez pas ce champ. » mesurée **185 × 17 px, lisible** |
| **Toile de l'éditeur** | **CLOSE** | la discipline de spécificité **fonctionne** : `__mention` `margin-block-end: 0`, `__piege` `display:none`, `__etiquette` `600`, saisie 48,95 px, bouton `rgb(74,107,87)` |
| **360 px / zoom 200 %** | **CLOSES** en viewport réel | `scrollWidth 360 = innerWidth 360` ; 640×800 à `zoom:200%` sans débordement |
| **V-front-15 — halo Firefox** | **CLOSE**, A11 **confirmé** | Firefox 153 : avant interaction `:invalid`=true mais **`:-moz-ui-invalid`=false**, `box-shadow:none`. **`novalidate` désarme bien le halo : il n'y a rien à neutraliser** |
| **V18 — bloc dans un élément de gabarit** | **TOUJOURS OUVERTE** | la mesure décisive est faite (`has_block()` faux hors `post_content`, POST refusé), le bout-en-bout non |
| **Logique d'expéditeur `From:`** | **TOUJOURS OUVERTE, et structurellement invérifiable en local** | `provision.sh` filtre `wp_mail_from` : Mailpit montre `no-reply@mtbrabant.local`, qui n'est **pas** la logique du module |

### 20.3 Deux invariants vérifiés que le contrat n'exigeait pas explicitement

- **Contenu protégé non divulgué** : mot de passe posé sur la page 4 → bloc **0 occurrence**, feuille
  absente, POST → 200 **sans envoi** (Mailpit inchangé), recherche `?s=` → 0, flux → 0. Mot de passe
  retiré. La garde 5 tient donc **à l'exécution**, pas seulement en lecture.
- **Aucun chemin public n'atteint une écriture** : `grep` du module → 0 `update_option`, 0
  `set_transient`, 0 `$wpdb`, 0 `setcookie`, 0 `session_start`, 0 `wp_mail_from`. **La décision 45 est
  vérifiable par recherche**, ce qui est la meilleure garantie qu'elle survive à un futur lot.

### 20.4 Dette confirmée

**T-#22-i — la feuille est incorporée en ligne AVEC ses ~5 Ko de commentaires**, sur chaque page
portant le bloc (19 166 o sans bloc → **37 553 o** avec). Choix assumé et écrit en tête de feuille ;
consigné ici parce que le §18 le chiffrait sans le nommer comme un coût récurrent.

---

## 21. Amendement du 2026-08-23 — reprise ciblée : Q-22-1 tranchée, T-#22-e payée

Deux points restaient après la jonction. L'un attendait un arbitrage de l'utilisateur, l'autre était
dû par ricochet à une fiche d'une autre issue. Les voici, avec ce qui a été **mesuré à l'écran**.

### 21.1 Q-22-1 est TRANCHÉE — « texte par défaut minimal et vrai »

**Arbitrage de l'utilisateur, 2026-08-23.** La mention par défaut ne dit **que ce qui est
vérifiable** : à quoi sert le message, à qui il est envoyé, et qu'aucune copie n'est conservée sur le
site. **Pas de durée de conservation** — il n'y en a pas. **Pas de responsable de traitement nommé** —
la raison sociale manque encore aux Mentions légales, et l'inventer serait un fait faux. L'éleveuse
remplace ce texte par le sien quand elle veut : **la mention reste un réglage libre du bloc**, jamais
une constante.

**Trois passages du texte gelé sont amendés** — et, selon la convention de ce contrat (§18), ils ne
sont pas réécrits au-dessus : ce qui suit les corrige. Ce sont le **§4.1** (tableau des attributs), le
**§6.2** (l'exemple de balisage de l'état 1, dont la ligne `__mention` porte encore les deux anciennes
phrases) et le **§9** (ligne « Mention par défaut »). **Nouvelle valeur par défaut de l'attribut
`mention`** :

> Votre message est envoyé par courriel à l'élevage. Votre nom et votre adresse de courriel
> l'accompagnent, pour que l'élevage puisse vous répondre. Le site n'en garde aucune copie : ni votre
> message, ni votre nom, ni votre adresse ne sont enregistrés ici.

**Ce ne sont pas trois phrases de rédaction juridique : ce sont trois descriptions du comportement du
code**, chacune adossée à une ligne, comme les deux qu'elles remplacent.

| Phrase | Ce qui la rend vraie |
|---|---|
| « Votre message est envoyé par courriel à l'élevage. » | `traitement.php:405-410` — `wp_mail( $adresse, … )`, où `$adresse` vient de `destination()` (`destination.php:101-107`), tirée de `mtb_get_coordonnees_elevage()['courriel']['valeur']` |
| « Votre nom et votre adresse de courriel l'accompagnent, pour que l'élevage puisse vous répondre. » | `messages.php` `corps_courriel()`, lignes `'Nom : '` et `'Courriel : '` ; et `traitement.php:399-401`, l'en-tête `Reply-To:` de la visiteuse, qui n'existe que pour cela. Le membre de phrase reprend **mot pour mot** `AIDE_COURRIEL` (`messages.php:42`) — reprise voulue |
| « Le site n'en garde aucune copie : ni votre message, ni votre nom, ni votre adresse ne sont enregistrés ici. » | Décision 45, **vérifiable par recherche et re-mesurée ce jour** : le module compte 0 `update_option`, 0 `add_option`, 0 `set_transient`, 0 `$wpdb`, 0 `setcookie`, 0 `session_start`, 0 `wp_insert_post` |

**Ce que le texte tait, et pourquoi il continue de le taire** — le §12 reste valable mot pour mot :
aucune durée (il n'y a pas de conservation), aucun droit d'accès ou de suppression, aucun responsable
de traitement. **Interdit d'écriture nouveau et opposable : jamais « vous pouvez demander la
suppression ».** Le site n'a rien à supprimer, et cette phrase engagerait l'éleveuse à vider sa propre
boîte de courriels sur demande — ce que ni le code ni personne ne garantit. C'est la promesse la plus
tentante à recopier d'un autre site ; elle est la seule expressément proscrite.

**Aucune promesse de réponse n'est prise** : « pour que l'élevage **puisse** vous répondre » énonce une
finalité, pas un engagement. Cohérent avec le §6.5, qui refusait déjà « L'élevage vous répondra ».

### 21.2 Q-22-2, second volet : la destination reste `mtbrabant@gmail.com`, et n'est écrite nulle part

**Tranché** : la destination reste l'adresse de l'écran **Coordonnées**, livré par #38. **Mesuré sur
la pile**, option `mtb_core_coordonnees` **absente de la base** — donc dans l'état le plus froid
possible :

```
mtb_get_coordonnees_elevage()['courriel']['valeur'] = 'mtbrabant@gmail.com'
destination()             = 'mtbrabant@gmail.com'
destination_utilisable()  = true
```

Elle vient de `valeurs_de_depart()` (`includes/query/coordonnees/option.php:44-51`, recopiée du brief
§7). **Le V21 du contrat tient** : `grep -r 'mtbrabant@gmail.com'` sur
`includes/blocks/formulaire-contact/` rend **zéro**, et le dépôt entier n'en compte **qu'un seul
exemplaire**, celui de `option.php`. C'est ce qui rend **D1** vraie ici : l'éleveuse change la
destination du formulaire sans qu'on ouvre un fichier.

### 21.3 §4.1 amendé — le littéral par défaut n'a plus qu'UN exemplaire

**Défaut trouvé et corrigé pendant la reprise.** `messages.php:94` déclarait
`const MENTION_PAR_DEFAUT`, **référencée nulle part** : ni `rendu.php` (le cœur remplit l'attribut
manquant par `WP_Block::prepare_attributes_for_render()`), ni `editeur.js` (il lit l'attribut). C'était
un **second exemplaire du même littéral français**, que la retouche de la mention aurait fait diverger
en silence — la classe de défaut des décisions 43 et 46, et le §9 aurait été satisfait à la lettre en
étant faux dans les faits.

**La constante est retirée.** Le littéral vit désormais **une seule fois**, dans
`block.json → attributes.mention.default`, seul endroit qui puisse le porter : le cœur lit ce fichier
**avant tout PHP du module**. Un commentaire prend sa place dans `messages.php`, porte la copie de
lecture **explicitement non normative**, l'adossement du tableau ci-dessus, et l'interdiction de
réintroduire une constante.

**T-#22-h est élargie** : la frontière JSON/PHP ne couvre plus seulement le `title`, mais aussi la
valeur par défaut de la mention. Deux littéraux français vivent dans `block.json` parce que le cœur
l'impose, et c'est écrit plutôt que contourné.

### 21.4 T-#22-e est PAYÉE — la fiche des coordonnées

`docs/guide/coordonnees-modifier-les-coordonnees.md` (livrée par #38 au lot 5) enseignait « Laissez
vide pour retirer le courriel de tout le site » **sans savoir** que ce geste fait désormais
disparaître le formulaire de contact. Elle est **le seul endroit** où l'éleveuse pouvait l'apprendre
au moment du geste. Décision 43 : une fiche fausse vaut un bug. Corrigée dans cette reprise.

`docs/guide/composant-formulaire-contact.md` est mise à jour de la nouvelle mention par défaut et de
l'interdit du §21.1.

### 21.5 Ce qui a été mesuré à l'écran

Pile `http://localhost:3005`, page **Contact** (ID 4) portant le composant, **session de l'éleveuse**
(`fabienne`, Éditeur natif) — jamais en session d'administration. Pilotage par Chrome sans affichage,
protocole de débogage.

| Mesure | Résultat |
|---|---|
| Mention par défaut **rendue au visiteur**, bloc posé **sans attribut `mention`** | les trois phrases, entières, dans `<p class="mtb-formulaire-contact__mention">`. Apostrophes typographiées par `wptexturize` — mise en forme du cœur, pas un écart de texte |
| Menu de gauche de l'éleveuse | `… Résultats de travail · Coordonnées · Commentaires …` — **menu de premier niveau**, sous `edit_pages`. La formulation « dans le menu Coordonnées » (A10) est **confirmée à l'usage**, pas seulement en lecture |
| Aide du champ **Courriel**, lue à l'écran | « L'adresse électronique à laquelle on vous écrit. […] Laissez vide pour retirer le courriel de tout le site. » |
| Champ **Courriel** vidé, **Enregistrer** | avis « **Coordonnées enregistrées.** » |
| **Site public**, aussitôt après | **0 occurrence** du composant, **feuille de style non chargée**, aucun cadre vide, aucune marge fantôme. **Il reste le titre « Écrire à l'élevage » tapé au-dessus, suivi de rien** — 285 px de contenu |
| **Écran de modification**, courriel vide | encadré gris **576 × 145 px**, **au-dessus** de la représentation : « FORMULAIRE DE CONTACT » puis « Ce bloc n'affiche rien tant qu'aucun courriel n'est enregistré dans le menu Coordonnées. » La représentation **reste visible en dessous**, champ de la mention compris |
| Adresse retapée, **Enregistrer** | le formulaire **revient seul**, **sans rouvrir ni republier** la page Contact |

**Constat nouveau, consigné parce qu'il n'était écrit nulle part** : le composant disparaît, mais **le
titre que l'éleveuse a tapé au-dessus reste**. Le visiteur lit alors une invitation à écrire suivie du
vide. Ce n'est pas un défaut du composant — il ne peut pas effacer un bloc voisin — mais c'est une
conséquence qu'elle doit connaître, et elle est désormais dans sa fiche.

### 21.6 Ce qui n'a PAS été vérifié dans cette reprise

- **Aucun envoi de courriel n'a été refait.** Ce n'était pas nécessaire : les deux vérifications
  portent sur du texte rendu et sur une disparition de bloc. Les lignes du §20.2 restent en l'état.
- **L'adresse présente mais NON VALIDE** (§4.4, phrase 2) n'a **pas** été regardée à l'écran pendant
  cette reprise. Le comportement est lu dans le code et déjà écrit dans la fiche du composant ; il n'a
  pas été remesuré.
- **V18** (bloc dans un vrai élément de gabarit) reste **ouverte**, inchangée.
- La **logique d'expéditeur `From:`** reste ouverte, inchangée.
