# Contrat d'interface — Issue #50 — Faire répondre 404 franc au sous-plan des utilisateurs du plan du site

**Gelé le 2026-09-08.** Milestone 13, labels `seo` et `feature`. Lot 20, en parallèle de **#53**
(`migration/redirections-301/commande.php`). **#49** ouvre le même module juste après cette chaîne et
attend son commit.

Ce contrat est **le point de réconciliation d'une chaîne à un seul côté** : #50 ne touche aucun fichier
du thème. Il n'y a donc pas eu de `leaddev-front-mtb`, et la section « Blocs enregistrés » du gabarit
habituel est **sans objet et le dit** (§9). Ce que ce contrat gèle, c'est la frontière avec **le cœur de
WordPress** — un chemin de code non versionné, donc à **mesurer et non à croire** — et avec les
**trois bornes** de l'amendement au §2 du contrat #1.

> **Convention d'amendement** (décision 65, reconduite) — ce contrat s'amende par **ajout daté en fin de
> fichier**, jamais par réécriture d'une section numérotée : une citation par numéro de § ne doit jamais
> se périmer. Un amendement porte sa date, son issue, et le motif du changement.

> **Ce contrat est gelé AVANT la mesure due du §3**, et c'est délibéré : toutes ses décisions — la
> forme du correctif, les sept gardes, la constante unique, la priorité, les bornes — sont **prises et
> opposables dès maintenant**. Ce que la mesure peut changer est **énuméré à l'avance, branche par
> branche** (§3.3). Le relevé lui-même s'ajoute **en fin de fichier, daté**, selon la convention
> ci-dessus. **Aucune ligne de ce contrat ne présente une déduction comme une mesure** — l'interdit
> institué par #24 §16 est ici la règle de rédaction, pas un rappel de politesse.

---

## 1. L'empreinte fichiers

| Chemin | Nature |
|---|---|
| `wp-content/plugins/mtb-core/includes/migration/indexation-heritee/plan-du-site.php` | modification |
| `wp-content/plugins/mtb-core/includes/migration/indexation-heritee/bootstrap.php` | **modification — le raccordement l'exige** (§6) |
| `docs/contracts/issue-50.md` | ce fichier |

**`bootstrap.php` est ouvert, et c'est déclaré plutôt que subi.** Sans la ligne `add_action`, la
fonction n'existe que sur le papier : le correctif serait **un module mort et silencieux**, la classe de
défaut que ce dépôt traque depuis T6/#27. **#49 ouvre ce même fichier juste après cette chaîne** — deux
ajouts d'une ligne chacun, en fin de fichier, sur des crochets différents. Aucune collision de contenu,
mais la séquence est réelle et le lead l'arbitre.

**Interdit, sans exception** : `migration/redirections-301/**` (**chaîne #53 en cours dans l'arbre
partagé, aucune isolation**) · `docs/contracts/issue-1.md` (§4 du présent contrat livre le texte prêt à
coller ; **le lead l'applique, pas cette chaîne**) · `docs/contracts/issue-24.md` et `issue-52.md`
(**contrats gelés, ils ne se rouvrent pas** — la relève est au §7 ci-dessous) · `docs/ETAT.md` ·
`docs/guide/**` · `conversion.php` · `fait.php` · `class-loader.php` · `mtb-core.php` ·
`includes/query/**` · `includes/content/**` · tout fichier du thème · `theme.json` · toute feuille sous
`themes/mtb/assets/css/**` · `Makefile` · `compose.yaml` · `docker/**`.

**Aucune feuille CSS n'est touchée : `make css` est sans objet pour cette issue**, et son artefact
`*.min.css` ne fait pas partie de cette empreinte.

**Aucune fiche de `docs/guide/` n'est écrite, et aucune n'est due.** #50 ne change **rien** de ce que
l'éleveuse voit : pas un écran, pas un mot, pas un bouton. Le seul visiteur de `/wp-sitemap-users-1.xml`
est un robot d'indexation. **D3 est sans objet ici, et c'est écrit plutôt que tu.**

---

## 2. Le défaut, et à qui il appartient

`/wp-sitemap-users-1.xml` répond **200 en `text/html`**, 16 035 o, corps quasi vide. C'est un
***soft-404*** : le pire cas pour un moteur, qui le tient pour **une page réelle et vide** au lieu d'un
sous-plan absent. Comparaisons relevées par la passe d'intégration du lot 18 :
`/wp-sitemap-posts-mtb_resultat-1.xml` rend un **404 franc** ; `/nexiste-pas-du-tout/` rend 18 284 o de
« Page introuvable ». Dette **T106** de `docs/ETAT.md`.

**Le §E de l'amendement de #24 avait raison sur le mécanisme, et il ne nous exonère pas.** Il écrit :
« c'est le comportement du cœur quand un fournisseur est retiré de l'index, **pas un effet de ce
module** ». C'est vrai du mécanisme. Mais **avant notre filtre, cette adresse rendait un document XML
valide.** C'est **notre geste** — le retrait du fournisseur `users`, exception motivée et datée du
2026-09-05 — qui a transformé une ressource valide en faux 404. La responsabilité n'est pas dans le
mécanisme du cœur, elle est dans le fait d'avoir **retiré la ressource sans traiter la réponse**.

> **Nous avons cassé cette ressource, nous la réparons. Et rien de plus.**

**Ce qui est dit sans dramatiser** : le site n'est pas en ligne, aucun moteur n'a jamais vu cette
adresse, elle n'est plus listée à l'index du plan, rien ne pointe dessus. `docs/ETAT.md` l'écrit
lui-même : « sans conséquence tant que le site n'est pas en ligne ». **L'urgence est nulle ; la dette se
paie maintenant parce qu'elle est petite, cernée, et dans un fichier qu'on ouvre de toute façon.**

**Et le corps servi est pire que le code.** Le grief n'est pas seulement « 404 contre 200 » : ces
16 035 octets sont, selon la déduction du §3, **l'index du blog du site servi à une adresse en `.xml`**.
Ce n'est pas grave, mais c'est faux, et c'est plus faux qu'un code de statut mal choisi.

### La ligne de DoD, dite exactement

**Aucune ligne D1–D12 ne couvre nommément le code HTTP d'un sous-plan de plan du site absent.** D5 porte
sur les 52 adresses de l'ancien site ; `/wp-sitemap-users-1.xml` n'en est pas une et n'a **jamais existé
sur mtbrabant.com**. L'issue sert **l'esprit de D5** — une URL du site répond exactement ce qu'elle doit
répondre — et rien d'autre. **C'est une dette d'hygiène, elle ne s'habille pas en obligation
contractuelle.**

---

## 3. La mesure due — bloquante, avant une ligne de code

`wp-includes/` **n'est pas versionné**. Tout ce qui suit au §3.1 est une **déduction de lecture, jamais
un relevé**. #24 §6.2 avait déjà imposé de lire `get_url_list()` dans le conteneur avant d'écrire ; #24
§16 a institué qu'on ne présente **jamais** une déduction pour une mesure. **Le même protocole
s'applique ici, et il est bloquant.**

### 3.1 La déduction, nommée comme telle

`WP_Sitemaps::render_sitemaps()`, accroché à `template_redirect` en priorité 10, contiendrait **deux
sorties d'échec de nature radicalement différente** :

```
$provider = $registry->get_provider( $sitemap );
if ( ! $provider ) { return; }                   // ← FOURNISSEUR ABSENT : return NU, aucun code posé
$url_list = $provider->get_url_list( ... );
if ( empty( $url_list ) ) { $wp_query->set_404(); status_header( 404 ); return; }  // ← LISTE VIDE : 404 franc
```

- **`mtb_resultat`** : le fournisseur `posts` **existe**, sa liste est vide (le type n'est pas public) →
  **seconde** branche → le cœur pose lui-même le 404 → `template-loader.php` sert `404.html`. D'où les
  18 Ko.
- **`users`** : notre filtre a empêché l'entrée au registre, le fournisseur **n'existe pas** →
  **première** branche → `return` nu, aucun code, aucune sortie → la requête poursuit son cours en
  `index.php?sitemap=users&paged=1`, donc `is_home()`, donc **200 sur `templates/index.html`**.

**La différence tient à un seul `return` sans code de statut.** « Le fournisseur existe mais sa liste est
vide » est un cas prévu ; « le fournisseur n'existe pas » est traité comme « cette requête ne me
concerne pas » — ce qui était juste tant que personne ne pouvait retirer un fournisseur **dont la règle
de réécriture, elle, reste en place**. *C'est là qu'est le trou.*

### 3.2 Ce qui doit être lu et relevé, nommément

À jouer **depuis le conteneur**, avant la première ligne de code, et à consigner **verbatim avec ses
numéros de ligne** :

| # | Question | Fichier |
|---|---|---|
| **Q1** | `render_sitemaps()` est-il accroché à `template_redirect`, et **à quelle priorité** ? | `wp-includes/sitemaps/class-wp-sitemaps.php` |
| **Q2** | **Ordre exact des sorties** : où est le bail sur le nom vide, où est traitée la feuille de style, où est `'index'`, où est `if ( ! $provider ) { return; }` | idem |
| **Q3** | Les deux `return` posent-ils un code de statut ? | idem |
| **Q4** | Par quelle méthode interroge-t-on le registre ? `wp_get_sitemap_providers()` existe-t-elle, que rend-elle ? | `class-wp-sitemaps-registry.php`, `sitemaps.php` |
| **Q5** | **Noms exacts** des variables de requête : sous-plan, sous-type, feuille de style, pagination | `wp-includes/sitemaps.php`, `register_rewrites()` |
| **Q6** | `init()` est-il conditionné à `wp_sitemaps_enabled()` ? Les règles sont-elles posées inconditionnellement ? | idem |
| **Q7** | `add_rewrite_tag( '%sitemap%', … )` enregistre-t-il `sitemap` en variable de requête **publique** ? | idem |

**Q2 porte le piège nommé par le lead.** Si le bail portait sur `$sitemap` **seul** et précédait la
branche de feuille de style, la route `.xsl` exigerait un `sitemap` non vide — et **la garde 1 du §5
attraperait les `.xsl`**. Conséquence : les cinq sous-plans deviendraient **illisibles pour un humain,
parfaitement valides pour un moteur, sans que rien ne le signale**. **La ligne se relève, elle ne se
suppose pas.**

**Q5 est de la même famille** : un nom de variable faux donne un rappel qui **ne mord jamais, en
silence** — la classe de panne exacte de cette issue.

### 3.3 Le test de falsification, et ce que chaque branche fait au plan

Relever la classe du `<body>` servi à `/wp-sitemap-users-1.xml` :

| Relevé | Verdict | Ce que devient ce contrat |
|---|---|---|
| `home blog …`, **pas** `error404` | Déduction **confirmée** | **Le contrat s'applique intégralement.** |
| `error404` présent, statut 200 | Déduction **fausse sur la cause** — la requête *est* un 404, le statut a été remis à 200 ailleurs | **Les sept gardes et la constante unique tiennent ; seul le corps du rappel change** : `set_404()` devient redondant, il ne reste que `status_header( 404 )`, et **il faut identifier qui remet 200 avant d'écrire**. Rejouer Q1–Q7, remonter au lead. |
| Ni l'un ni l'autre (`search`, `archive`, `paged`…) | Déduction **fausse sur la route** | **Ne rien coder.** Relever la classe exacte, relire `register_rewrites()`, **arrêt et remontée au lead.** |

### 3.4 Consigne de méthode, éprouvée et non négociable

Toute mesure HTTP se joue **depuis le conteneur**, **jamais** depuis le shell Git Bash de Windows, qui
transcode l'UTF-8 en Latin-1 et mesure ainsi **une adresse qui n'a jamais existé** — piège consigné à
#24 §16 point 6. `curl --path-as-is`, **sans cookie ni `--user`** : une mesure en session
d'administrateur ne mesure pas ce que voit un visiteur (précédent `issue-27.md` §11).

**Calibrage obligatoire avant toute autre mesure** : reproduire les deux valeurs de référence de T106
(`/wp-sitemap-users-1.xml` → `200 … 16035` ; `/nexiste-pas-du-tout/` → `404 … 18284`). Si elles ne se
reproduisent pas, **ne pas conclure que la dette a bougé** : vérifier d'abord la méthode. Si la méthode
est bonne et les chiffres ont bougé (le lot 19 a livré `query/mise-en-sommeil`), **prendre les valeurs
fraîches comme référence et écrire les deux relevés côte à côte**. *Un chiffre de contrat qu'on ne
reproduit pas se dit, il ne se recopie pas.*

**La pile est démarrée par `docker compose up -d` seul** — **jamais** `down`, `--build`,
`--force-recreate`, ni aucune commande touchant la base : **la chaîne #53 travaille dans le même arbre**.

---

## 4. La forme du correctif — décidée, et les trois écartées

**Décision : nous répondons pour le fournisseur que nous avons retiré.** Un rappel sur
`template_redirect` en **priorité 20** pose `set_404()` + `status_header( 404 )` quand la variable de
requête de sous-plan nomme **un fournisseur que ce module retire** et qui **n'est pas au registre**.

### Les trois options écartées, avec leur motif — fermées, non rouvrables sans amendement

| Option | Décision | Motif |
|---|---|---|
| **B — déclarer un fournisseur vide** (sous-classe de `WP_Sitemaps_Provider` rendant `array()`) | **Écartée** | Élégante : le 404 serait produit par le **chemin de code exact du cœur**, identique par construction et non par ressemblance. Mais **hériter d'une classe abstraite du cœur expose à un `E_COMPILE_ERROR`** le jour où WordPress y ajoute une méthode abstraite — PHP refuse de déclarer la classe, l'erreur **n'est pas rattrapée par le `try/catch` du chargeur** (`issue-1.md` §12), **site entier par terre**. **Le mode de panne décide** : l'option retenue dégrade vers le 200 d'aujourd'hui ; celle-ci a pour rayon d'explosion le site entier, pour un code de statut sur une URL machine. Écartée **sur le rayon d'explosion, pas sur l'esthétique** |
| **B′ — laisser le fournisseur et vider sa requête** (`wp_sitemaps_users_query_args`) | **Écartée** | **Panne ouverte** : si le filtre cesse de mordre, `/author/admin/` **revient au plan du site**, c'est-à-dire que **la fuite de l'identifiant de connexion réapparaît en silence**. Le `false` d'aujourd'hui tombe du côté sûr. **On ne troque pas une garantie de vie privée contre un code de statut** |
| **C — règle générale « tout sous-plan sans fournisseur répond 404 »** | **Écartée** | Intellectuellement la plus propre — le défaut est une **classe**, pas une instance — et **c'est exactement pourquoi il faut s'en méfier ici**. Elle traiterait des adresses que **ni l'ancien site ni nous** n'avons jamais touchées, et ferait tomber la **borne 3** : le module cesserait d'avoir « un périmètre clos et daté » pour devenir un correcteur de référencement à vocation ouverte, sous un nom qui dit « héritage ». **La borne a déjà plié une fois, le 2026-09-05, sur une donnée personnelle exposée. Elle ne plie pas une seconde fois pour du confort.** Si cette règle est voulue un jour, elle mérite **son propre module, son propre nom honnête et son propre amendement daté** — pas un élargissement discret sous couvert de la tâche 2 |
| **D — ne rien faire, solder T106 par écrit** | **Écartée** | Position défendable, et son argument est réel : le site n'est pas en ligne, aucune ligne de DoD ne l'exige, et le correctif ajoute un crochet de front. **Ce qui l'emporte** : (a) le défaut est **de notre fait**, pas seulement du cœur ; (b) le corps servi n'est pas « quasi vide », c'est l'index du blog à une adresse `.xml` ; (c) le remède coûte une dizaine de lignes dans un fichier qu'on ouvre de toute façon ; (d) *« on le fera quand le site sera en ligne »* est précisément la phrase qui ne se réalise jamais |

### La tâche 2, et sa lecture — arbitrage

« Vérifier qu'aucun autre sous-plan retiré ne produise le même faux 200 » se lit de deux façons qui
n'ont pas le même prix. **La lecture retenue est : « aucun autre sous-plan QUE NOUS RETIRONS »** — et
elle est rendue vraie **par construction** par la constante unique du §5, non par inspection à l'œil.
L'autre lecture est l'option C, écartée ci-dessus.

---

## 5. La liste unique des fournisseurs retirés — zéro recopie, et pourquoi

| Point | Décision gelée |
|---|---|
| **Forme** | `const FOURNISSEURS_RETIRES = array( 'users' );` |
| **Nom qualifié** | `MTB\Core\Migration\IndexationHeritee\FOURNISSEURS_RETIRES` |
| **Domicile** | `indexation-heritee/plan-du-site.php`, **sous le bloc d'exception motivée du 2026-09-05**, avant les deux fonctions |
| **Lecteurs** | **Exactement deux**, dans le même fichier, à quelques lignes l'un de l'autre : `ecarter_le_fournisseur_utilisateurs()` et `repondre_404_au_sous_plan_retire()` |
| **Recopie du littéral `'users'`** | **Zéro.** Après correctif, `grep -rn "'users'" wp-content/` ne rend **qu'une ligne** : celle de la constante. **Contrôle du protocole (P7), pas un vœu** |

**Ce que la constante rend vrai par construction — c'est la tâche 2 de l'issue.** Le jour où un second
fournisseur entre dans la liste, il est **du même geste** retiré du registre **et** répondu en 404. *Il
ne peut pas exister de fournisseur retiré sans 404, ni de 404 sans retrait.* C'est **la leçon de #52
appliquée au code** : deux mécanismes qui décrivent le même fait et peuvent diverger en silence, c'est
le mode de panne, pas la fonctionnalité.

**Pourquoi pas un filtre** (`apply_filters( 'mtb_fournisseurs_retires', … )`) : un point d'extension
ferait de ce module **un module de référencement à vocation ouverte** — **borne 3**, refusée. Le
périmètre se modifie en éditant la constante, dans un commit, avec son motif.

**Condition de renommage, écrite dans le code** : le jour où la constante porte **plus d'un nom**,
`ecarter_le_fournisseur_utilisateurs()` **doit être renommée**. Son nom est aujourd'hui **littéralement
vrai**, et il est cité par nom dans `docs/contracts/issue-52.md` §4 et §10 comme témoin que ce crochet
est **intact, « pas d'un caractère »**. Le renommer aujourd'hui périmerait deux citations d'un contrat
gelé pour zéro gain fonctionnel. **La condition est dans le docblock de la constante, pas seulement
ici.**

---

## 6. Les sept gardes — ordre imposé

`repondre_404_au_sous_plan_retire(): void`, rappel de `template_redirect` en **priorité 20**.
**Aucune garde ne se réordonne sans rouvrir ce contrat.**

| # | Garde | Ce qu'elle protège |
|---|---|---|
| **1** | Dériver le nom du sous-plan **avec la fonction même que le cœur applique à la même valeur** (`sanitize_text_field()` sur la variable de requête de sous-plan), puis **sortir si le nom est vide** | **La quasi-totalité du trafic**, en un test. C'est aussi la garde qui **protège les deux `.xsl`** (la route de feuille de style laisse le nom de sous-plan vide — **à confirmer par Q2**). Et c'est ce qui neutralise `?sitemap[]=users` : la valeur devient `''`, **sans notice PHP**. *On dérive le nom exactement comme le cœur l'a dérivé, pour ne jamais répondre 404 sur un nom que le cœur aurait servi.* |
| **2** | Sortir si la variable de requête de **feuille de style** est non vide | **Les deux `.xsl`, une seconde fois et localement. Délibérément redondante** avec la garde 1 tant que Q2 confirme le bail combiné. Le mode de panne qu'elle ferme est **totalement muet** : les cinq sous-plans illisibles pour un humain, valides pour un moteur, et **aucun autre contrôle ne le dirait**. Coût : un `get_query_var` sur les seules requêtes de plan du site |
| **3** | Sortir si `is_admin()`, `wp_doing_ajax()`, `wp_doing_cron()` ou `REST_REQUEST` | Contextes où poser un 404 de front n'a pas de sens. **Ceinture** — `template_redirect` n'y court pas. **Alignée mot pour mot sur `redirections-301/service.php`** : deux services de front du même groupe `migration/` ne divergent pas sur leur garde de contexte. Placée **après** la garde 1, et le commentaire dit pourquoi : `wp_doing_ajax()` et `wp_doing_cron()` appliquent chacun un filtre, la garde 1 est plus discriminante et moins chère |
| **4** | Sortir si le nom n'est pas dans `FOURNISSEURS_RETIRES` (**comparaison stricte**, `true` en troisième argument) | **La borne 3.** On ne répond 404 que pour ce que **ce module** retire. Un sous-plan inconnu garde son 200 d'aujourd'hui — **résidu nommé au §8, pas masqué** |
| **5** | Sortir si le nom **est au registre** des fournisseurs | **La garde de désarmement.** Le 404 est conditionné au retrait **effectif, constaté au registre** et non déduit de notre propre filtre. Si `users` est rétabli — retrait de notre `add_filter`, enregistrement tiers, renommage du dossier en `_indexation-heritee` — **le 404 cesse de lui-même. Aucun état mort à nettoyer.** Placée **après** la garde 4 : elle ne court que sur une requête portant déjà un nom de la liste, donc jamais en pratique. **Forme exacte arbitrée par Q4** |
| **6** | Sortir si la requête globale n'est pas une instance de `WP_Query` | **Assurance contre l'erreur fatale.** Ce rappel court sur **chaque requête de front du site** : un `Call to a member function set_404() on null` y serait **le site entier par terre** — exactement le rayon d'explosion pour lequel l'option B a été écartée. Le cœur ne fait pas ce test ; **nous le faisons, et le commentaire dit pourquoi** |
| **7** | `set_404()` sur la requête, puis `status_header( 404 )`, puis **fin de fonction** | **Le même geste que le cœur, dans le même ordre, sans un appel de plus.** Pas de `nocache_headers()`, pas d'en-tête `Content-Type`, pas de corps : le cœur n'en pose aucun sur sa branche « liste vide », et **deux comportements différents pour un même fait est précisément ce qu'on répare** |

**Aucun `exit`, jamais.** La fonction rend la main ; `template-loader.php` évalue `is_404()` **après**
`do_action( 'template_redirect' )` et charge `themes/mtb/templates/404.html`. **C'est le chemin exact de
`mtb_resultat`.**

### Le corps servi est celui du thème, et c'est un choix

Un 404 nu ou un mini-XML serait **plus léger**. Il donnerait aussi **deux comportements différents pour
un même fait** — « sous-plan absent » — selon qu'il vient du cœur ou de nous. Ce qu'on gagnerait, ce
sont ~18 Ko sur une réponse que personne ne demande ; ce qu'on perdrait, c'est l'unicité du comportement
et **la vérifiabilité « identique à `mtb_resultat` » que la tâche 3 de l'issue exige**. **D8 n'est pas en
cause : le budget porte sur les pages servies aux visiteurs.** *Cette raison a été cherchée avant d'être
écartée.*

### La priorité 20, et ses DEUX motifs

1. **Après `render_sitemaps()`** (priorité 10 — **à confirmer par Q1**) : le cœur a déjà rendu la main
   par son `return` nu, et nous répondons pour le fournisseur que nous avons retiré. **Déférence** : si
   une version future du cœur se met à répondre correctement, elle `exit` avant nous et notre rappel ne
   parle jamais. **Sûreté** : à 20 on ne peut pas pré-empter un sous-plan valide, déjà parti.
2. **Après `redirect_canonical`**, lui aussi en priorité 10, qui appelle `redirect_guess_404_permalink()`
   **quand la requête EST un 404**. Poser `set_404()` **avant** lui — en priorité 1, comme le module
   voisin — **lui donnerait un 404 à deviner**, et `/wp-sitemap-users-1.xml` partirait en **301 vers un
   contenu au hasard**. *Ce serait pire que la panne d'origine.*

> **Le point 2 est une déduction du chemin de code de `redirect_canonical`, pas une mesure.** Il se
> vérifie au contrôle **P5** : la réponse attendue est `404` avec un `redirect_url` **vide**, jamais un
> `301`. **Le contrôle a de la valeur dans les deux sens** — si le relevé donnait un 301, la déduction
> serait juste *et* la priorité 20 serait ce qui l'empêche.

**Ne jamais poser ce rappel en priorité inférieure à celle de `redirect_canonical`.**

---

## 7. Relève de l'énumération des hooks de front de l'amendement au §2 du contrat #1 — 2026-09-08

**Motif du domicile de cette relève, établi par constat et non par convenance** : le texte de
l'amendement au §2 rédigé à `issue-24.md` §15 **n'a jamais été collé dans `docs/contracts/issue-1.md`**
— vérifié par recherche : `redirections-301`, `template_redirect` et « bornes » n'y apparaissent que
dans le tableau du §11. **La pratique effectivement en vigueur** est celle de `query/page-protegee`
(« Amendement au §2 déclaré dans `docs/contracts/issue-23.md` §2.3 ») et de #52 §11.2 (« les contrats
gelés ne se modifient pas ; les relèves sont écrites et datées ici ») : **l'amendement vit dans le
contrat de son issue, et la ligne du §11 y renvoie.** La relève s'écrit donc **ici**, ni dans
`issue-1.md`, ni dans `issue-24.md`.

L'amendement du 2026-09-05 énumère **cinq** hooks de front pour le groupe `migration/` :
`redirections-301/` (`template_redirect` 1, `the_content` 20) et `indexation-heritee/` (`wp_robots` 20,
`wp_sitemaps_posts_query_args` 10, `wp_sitemaps_add_provider` 10).

**Cette énumération n'est plus exacte, et il faut le dire deux fois plutôt qu'une.**

- **Ce que #52 lui a retiré** (2026-09-07) : `indexation-heritee/` **ne pose plus** `wp_robots` 20 ni
  `wp_sitemaps_posts_query_args` 10 — les deux sont passés à `query/mise-en-sommeil`, qui n'appartient
  pas au groupe `migration/` et ne relève donc pas de cet amendement. L'énumération tombait de cinq à
  **trois**.
- **Ce que #50 lui ajoute** (2026-09-08) : `indexation-heritee/` accroche `template_redirect` **20**.
  L'énumération passe à **quatre**.

**Énumération exacte au 2026-09-08 — quatre hooks de front pour le groupe `migration/` :**

| Module | Hook | Priorité | Rappel |
|---|---|---|---|
| `redirections-301/` | `template_redirect` | 1 | `rediriger` |
| `redirections-301/` | `the_content` | 20 | réparation des douze ancres internes |
| `indexation-heritee/` | `wp_sitemaps_add_provider` | 10 | `ecarter_le_fournisseur_utilisateurs` |
| `indexation-heritee/` | `template_redirect` | **20** | `repondre_404_au_sous_plan_retire` |

**Pourquoi cette relève est écrite plutôt que tue** : *« un amendement qui sous-déclare sa propre portée
est exactement ce qu'il est censé empêcher »* (#24 §15, corrigeant son propre décompte le jour où il
était écrit).

### Les trois bornes tiennent, et voici à quoi elles tiennent

- **Borne 1 — lecture seule.** Le nouveau rappel n'écrit **rien** : ni option, ni contenu, ni
  métadonnée, ni terme. Il lit deux variables de requête, lit le registre, et pose un code de statut.
  **La borne 1 dit « il lit, il RÉPOND » : poser un 404 est répondre, pas écrire.** Le resserrement
  déclaré par #52 (les écritures de `conversion.php` sont hors de toute requête publique) est reconduit
  sans changement.
- **Borne 2 — aucune dépendance à un état en base pour se déclencher.** Le rappel lit une constante
  écrite dans son fichier. Il fonctionne **à la seconde où le dossier arrive par FTP**, sans réglage,
  sans visite de `wp-admin`, sans régénération de règles de réécriture. La lecture du registre est la
  lecture de **l'état de la requête en cours**, pas d'une configuration.
- **Borne 3 — périmètre clos et daté. C'est la borne qui a décidé de la forme du correctif**, et elle a
  **écarté la solution la plus générale** (option C). Le rappel ne répond que pour les noms que **ce
  module retire lui-même**, et se désarme dès que le nom est au registre. **Un sous-plan inconnu de
  notre liste garde son faux 200 — résidu nommé au §8.**

**Cette relève n'ouvre rien** : aucun groupe nouveau, aucun filtre sur `GROUPES`, ni `mtb-core.php` ni
`class-loader.php` touchés. Un module futur qui voudrait un hook de front dans `migration/` sans
satisfaire les trois bornes doit demander **son propre amendement, écrit et daté**.

---

## 8. États spéciaux, et le mode de panne silencieux

| État | Détecté par | Comportement imposé |
|---|---|---|
| `sous_plan_retire` | La variable `sitemap` nomme un membre de `FOURNISSEURS_RETIRES` **et** ce nom est absent du registre | `set_404()` + `status_header( 404 )`, **puis rendre la main**. Corps : `404.html`, **le même que tout autre 404 du site**. Jamais d'`exit`, jamais de corps sur mesure, jamais de mini-XML |
| `sous_plan_present` | Le nom **est au registre** | **Rien.** Le cœur a répondu ou répondra. Le 404 cesse de lui-même le jour où le fournisseur est rétabli |
| `sous_plan_inconnu` | Le nom n'est ni au registre ni dans `FOURNISSEURS_RETIRES` | **Rien** — la requête suit son cours, faux 200 compris. **Résidu déclaré, non masqué** (borne 3) |
| `route_feuille_de_style` | Variable de feuille de style non vide, ou nom de sous-plan vide | **Rien.** Deux gardes, dont une redondante à dessein |
| `plan_du_site_desactive` | La variable `sitemap` n'est pas enregistrée | **Rien** — le rappel se désarme seul. *Comportement exact arbitré par Q6 ; à écrire, pas à supposer* |

Les quatre états gelés au §9 du contrat #1 — `aucune_portee`, `donnee_absente`, `parent_hors_elevage`,
`page_protegee` — **ne sont ni touchés, ni étendus, ni réinterprétés.**

### Cas limites gelés

`/wp-sitemap-users-2.xml`, `-0.xml`, `-<sous-type>-1.xml` → **404** (le rappel **ne lit jamais la
pagination** : un fournisseur retiré n'a **aucune** page) · `?sitemap=users` et `?sitemap=users&paged=1`
sans permaliens jolis → **404** *(changement voulu, à mesurer ; dépend de Q7)* · `?sitemap=Users` →
**inchangé, 200** : **comparaison strictement sensible à la casse, comme le registre du cœur** —
normaliser la casse nous ferait répondre 404 pour un nom que le cœur n'a jamais routé, *c'est l'option C
par la petite porte* · `?sitemap[]=users` → **inchangé, et aucune notice PHP** · `POST` et `HEAD` →
**404** : **pas de garde de méthode, écart délibéré au module voisin** — une 301 sur un POST perd le
corps de la requête, un 404 ne perd rien, et le cœur ne teste pas la méthode sur sa branche 404 · les
deux `.xsl`, `/wp-sitemap.xml`, `/wp-sitemap-posts-mtb_resultat-1.xml` → **inchangés** · extension
désactivée → **retour au 200 d'aujourd'hui, et `users` revient au plan du site** : *dégradation
cohérente, les deux effets tombent ensemble puisqu'ils lisent la même constante*.

### Le mode de panne silencieux, écrit sans le maquiller

> **Si le rappel cesse de mordre, rien ne le dit. Rien.**

Ni journal, ni écran, ni avis d'administration, ni code de sortie. Et l'éleveuse ne visite jamais cette
adresse.

| Cause | Effet | Ce qui le dirait |
|---|---|---|
| La ligne `add_action` disparaît dans une reprise | Retour au 200 d'aujourd'hui | **Rien** |
| La priorité passe sous 10 | **301 vers un contenu deviné** par `redirect_guess_404_permalink()` | **Rien** — et c'est **pire** que la panne d'origine |
| `FOURNISSEURS_RETIRES` est vidée, ou le dossier renommé `_indexation-heritee` | Le 404 **et** le retrait tombent ensemble ; `users` revient au plan du site | **P1 le dirait, si on le joue** |
| Le cœur change le nom de la variable de requête | Le rappel ne mord plus jamais | **Rien** |
| Le cœur se met à poser un code sur son `return` nu | Notre rappel devient inutile et **inoffensif** | **Rien**, et ce n'est pas grave |

**Ce module n'a pas de commande WP-CLI, et il n'en aura pas.** Lui en donner une **contredirait le motif
3 gelé en tête de son `bootstrap.php`** — « témoins d'échec disjoints », ce qui le tient séparé de
`redirections-301`, dont le témoin *est* un code de sortie WP-CLI. En ajouter un troisième de la nature
du voisin **brouillerait la frontière que ce motif protège**.

**Le seul témoin est le protocole du §9, joué en recette, jamais en production.** Même classe de résidu
que `make css` et que le §17 de #24 : *le site reste correct, seule une adresse machine cesse d'être
franche, et rien ne le signale.* **Écrit ici plutôt que masqué par un avis d'administration hors
périmètre.**

---

## 9. Protocole de vérification — rejouable, joué DEUX fois

Depuis le conteneur, `curl --path-as-is`, sans cookie ni `--user`. **Le même script sert avant et après
le correctif. Un écart non attribuable est un échec.**

| # | Contrôle | Attendu |
|---|---|---|
| **P0** | `php -l` sur les **deux** fichiers touchés | **Avant tout `curl`.** Une erreur de syntaxe est un `E_COMPILE_ERROR` **non rattrapé par le `try/catch` du chargeur** (#1 §12) — site entier par terre |
| **P1** | **Sonde d'existence du module**, à jouer **en premier**, avant et après : les `<loc>` de `/wp-sitemap.xml` | **5 `<loc>`**, **aucune** portant `users`. *Si `users` réapparaît, le module n'est plus chargé et tout le reste du relevé se lirait à contresens* |
| **P2** | L'objet de l'issue | `/wp-sitemap-users-1.xml` : `200 … 16035` **→ 404**. **Égalité imposée** : après correctif, il rend **le même statut, le même type et la même taille, à l'octet**, que `/wp-sitemap-posts-mtb_resultat-1.xml`. *C'est la tâche 3 rendue mesurable. Un écart se nomme et s'explique ; il ne se tait pas* |
| **P3** | Les **6 documents** du plan du site — liste **extraite de l'index, jamais codée en dur** | Statut, type et **taille identiques à l'octet** avant/après, nombre de `<loc>` identique, **aucun `/author/`** dans aucun |
| **P4** | Les **2 feuilles `.xsl`** | `200`, XML, **taille identique à l'octet**. **Seul point du protocole dont la régression serait totalement muette** |
| **P5** | Cas limites du §8, **plus `redirect_url` sur `/wp-sitemap-users-1.xml`** | Celui-ci doit être **VIDE**. Un `301` prouverait que `redirect_guess_404_permalink()` a mordu — la panne que la priorité 20 empêche |
| **P6** | Non-régressions des lots 18 et 19 | `wp mtb verifier-redirections` → **code 0** · `/author/admin/` → **200** et `/?author=1` → **301** (**T105 n'est pas fermée par #50 et ne doit pas l'être par accident**) · `/`, `/portees/`, `/chien/jango/`, `/contact/`, `/travail/` → **200, tailles identiques** (*notre rappel court sur chacune*) · une adresse de la carte des 301 dans ses **deux formes** → 301 puis 200 · `/chien/halan/` → `<meta name='robots'>` **inchangée** (#52) · **`debug.log` inchangé** : une notice PHP est un échec |
| **P7** | `grep -rn "'users'" wp-content/` | **Exactement une ligne**, celle de la constante. **Toute seconde occurrence est un échec de revue.** Plus : **aucun** des 5 noms listés par l'index n'appartient à `FOURNISSEURS_RETIRES`, et chacun répond 200 — *la tâche 2 vérifiée par mesure, jamais par inspection à l'œil* |

**Ordre imposé qui rend les mesures attribuables** : écrire la constante et y rediriger le corps du
rappel existant → `php -l` → **rejouer P1 et P3 : rien ne doit avoir bougé d'un octet** — *c'est la
preuve que la constante est équivalente à l'ancien littéral, avant d'ajouter quoi que ce soit* → écrire
la fonction neuve **sans l'accrocher** → `php -l` → **vérifier que rien n'a changé**, *preuve qu'une
fonction non accrochée ne fait rien et que l'accroche seule est causale* → accrocher → relevé après
complet.

### Datation

Date · **sha du commit mesuré** · version de WordPress · hôte mesuré · `permalink_structure` ·
`blog_public`. *Une mesure n'a de valeur que datée d'un commit* (décision 68).

### Ce qui n'est PAS mesurable maintenant — à écrire comme tel, jamais à taire

Le comportement d'un moteur de recherche réel face à un soft-404 · l'hôte de production et le serveur
frontal qui y servirait ces adresses · le comportement d'une version future du cœur sur sa branche
`return` nu. **Faits d'exploitation, pas faits de code.**

---

## 10. Budget et frontières

- **D8 — poids ajouté sur les pages servies aux visiteurs : zéro octet.** Aucun CSS, aucun JS, aucune
  police, aucune image, aucune mise en file. **Une nuance chiffrée plutôt que tue** : la réponse à
  `/wp-sitemap-users-1.xml` **grossit**, d'environ 16 Ko à environ 18,3 Ko (`index.html` remplacé par
  `404.html`) — **+2,2 Ko sur une adresse qu'aucun visiteur ne demande**. Le budget n'est pas en cause ;
  **le chiffre se dit quand même**.
- **D6 — zéro requête sortante.** Aucun `wp_remote_*`, aucun `file_get_contents` distant. Zéro cookie,
  zéro traceur.
- **D10** — aucune extension tierce, aucun page builder.
- **D12** — le correctif **ne peut pas casser une page** : sept gardes en sortie anticipée, aucun rendu,
  aucun `exit`, et un `instanceof` qui ferme la seule erreur fatale possible.
- **Frontière** : ce module ne produit **aucune mise en page ni règle visuelle**. **Le thème n'est pas
  touché, ne le connaît pas, ne l'appelle pas, ne teste pas son existence.** La frontière du §8 du
  contrat #1 est intacte.

### Conventions de code, rappelées

`declare(strict_types=1);` · garde `if ( ! defined( 'ABSPATH' ) ) { exit; }` · namespace
`MTB\Core\Migration\IndexationHeritee` · **français littéral, aucune fonction i18n** (`issue-1.md` §7 —
jamais `__()`, `_e()`, `esc_html__()`) · `array()` et jamais `[]` · conditions de Yoda · tabulations ·
pas de `?>` final · plafond de syntaxe **PHP 8.1** · assainissement à l'entrée · WordPress Coding
Standards.

**À l'inclusion de `bootstrap.php`** (`issue-1.md` §3) : **seuls** `add_action`, `add_filter`, `define`,
`require_once` de ses propres fichiers, déclarations, et gardes de sortie anticipée.

---

## 11. Interdits

- **Ne jamais recopier le littéral `'users'`** ailleurs que dans `FOURNISSEURS_RETIRES` (contrôle P7).
- **Ne jamais généraliser le rappel** à « tout sous-plan sans fournisseur » — borne 3, option C écartée
  nommément.
- **Ne jamais retirer la garde de désarmement** : sans elle, le 404 survivrait au rétablissement du
  fournisseur, et ce serait un état mort à nettoyer à la main.
- **Ne jamais faire hériter une classe abstraite du cœur** (`WP_Sitemaps_Provider`) — option B, écartée
  pour son `E_COMPILE_ERROR` non rattrapé.
- **Ne jamais poser ce rappel en priorité inférieure à celle de `redirect_canonical`.**
- **Ne jamais `exit`** dans ce rappel : le corps doit être celui du thème.
- **Ne jamais donner de commande WP-CLI à ce module** — motif 3 gelé en tête de son `bootstrap.php`.
- **Ne jamais retirer un rappel de `wp_robots` « en croyant dédoublonner »** — interdit gelé par les
  contrats #23 et #24, reconduit par #52 (§12), reconduit ici.
- **Ne jamais réintroduire un filtre de plan du site lisant `_mtb_robots_source`** — c'est
  `query/mise-en-sommeil` qui porte cet état depuis #52.
- **Ne rien retirer du bloc d'exception motivée du 2026-09-05** dans `plan-du-site.php` : c'est un acquis
  contractuel. Il **s'augmente** d'un complément daté ; il ne s'ampute pas.
- **Ne jamais toucher `migration/redirections-301/**`** pendant cette chaîne — **#53 y travaille dans le
  même arbre, sans isolation.**
- **Ne jamais écrire dans `docs/contracts/issue-1.md`** : le texte est livré prêt à coller au §12, **le
  lead l'applique**.
- **Aucun fait de domaine n'est en jeu, et aucun ne doit apparaître.** Cette issue ne touche aucun nom de
  chien, aucune date, aucune généalogie, aucun résultat.

---

## 12. Ligne d'inventaire du §11 d'`issue-1.md` — prête à coller, appliquée par le lead

**Cette chaîne ne l'écrit pas.** Le §11 est un fichier partagé par les trois chaînes du lot ; la
**décision 70** en confie la tenue au lead. La ligne existante de `migration/ | indexation-heritee` est à
**compléter** — sa colonne « rôle » et sa colonne « hooks » deviennent incomplètes après #50 :

> **Rôle, à ajouter en fin de cellule** : « Conserve le retrait du fournisseur `users` du plan du site
> (exception motivée au §6.4 de #24, datée du 2026-09-05) **et, depuis le 2026-09-08 (#50), répond
> lui-même le 404 franc que le cœur ne pose pas pour un fournisseur retiré** — le cœur sort de
> `render_sitemaps()` par un `return` nu quand le fournisseur n'est pas au registre, laissant un faux
> 200 en HTML (dette T106). La constante `FOURNISSEURS_RETIRES` de `plan-du-site.php` est **l'unique
> écriture** du nom d'un fournisseur retiré et est lue par **les deux** rappels : il ne peut donc exister
> ni retrait sans 404, ni 404 sans retrait. »
>
> **Hooks, à ajouter à l'énumération** : « **`template_redirect` 20** (#50, priorité choisie pour passer
> après `render_sitemaps()` **et** après `redirect_canonical`, dont le devineur de 404 mordrait
> sinon). »
>
> **Renvoi, à ajouter** : « **relève de l'énumération des hooks de front dans
> `docs/contracts/issue-50.md` §7**. »

**Le compte de modules du §11 est inchangé : #50 ne crée aucun module.**

---

## 13. Arbitrages — chaque désaccord, la décision, sa raison

| # | Désaccord | Décision | Raison |
|---|---|---|---|
| 1 | Le défaut est-il de nous, ou « le comportement du cœur » comme l'écrit #24 §E ? | **De nous** | Le mécanisme est celui du cœur, mais **avant notre filtre l'adresse rendait un XML valide**. Nous avons retiré la ressource sans traiter la réponse |
| 2 | Corriger, ou solder T106 par écrit (option D) ? | **Corriger** | Dette petite, cernée, dans un fichier qu'on ouvre de toute façon. *« On le fera quand le site sera en ligne » est la phrase qui ne se réalise jamais* |
| 3 | Fournisseur vide hérité du cœur (option B), ou rappel qui répond (A) ? | **A** | **Le mode de panne décide** : A dégrade vers le 200 d'aujourd'hui ; B expose à un `E_COMPILE_ERROR` **non rattrapé par le `try/catch` du chargeur** — site entier par terre pour un code de statut sur une URL machine |
| 4 | Vider la requête du fournisseur plutôt que le retirer (B′) ? | **Non** | **Panne ouverte** : si le filtre cesse de mordre, la fuite de l'identifiant de connexion **réapparaît en silence**. On ne troque pas une garantie de vie privée contre un code de statut |
| 5 | Traiter le seul nom `users`, ou tout sous-plan sans fournisseur (option C) ? | **Le seul nom, via la constante** | **Borne 3.** Elle a déjà plié une fois, le 2026-09-05, sur une donnée personnelle exposée. **Elle ne plie pas une seconde fois pour du confort.** La règle générale mérite son propre module et son propre amendement |
| 6 | Comment rendre vraie la tâche 2 ? | **Par construction, pas par inspection** | Une **constante unique** lue par les deux rappels : il ne peut exister ni retrait sans 404, ni 404 sans retrait. *Leçon de #52 : deux mécanismes décrivant le même fait et pouvant diverger en silence, c'est le mode de panne* |
| 7 | Constante, fonction, fichier séparé, ou filtre ? | **Constante de fichier, à côté de ses deux lecteurs** | On voit **d'un coup d'œil** que rien ne peut diverger. Un filtre ferait du module un module de référencement à vocation ouverte — borne 3 |
| 8 | Renommer `ecarter_le_fournisseur_utilisateurs()` ? | **Non, et la condition de renommage est écrite dans le code** | Le nom est **littéralement vrai** aujourd'hui et **cité par nom dans `issue-52.md` §4 et §10** comme témoin que ce crochet est intact. Le renommer périmerait deux citations d'un contrat gelé pour zéro gain |
| 9 | Priorité 1 (comme le module voisin) ou 20 ? | **20** | Après `render_sitemaps()` **et après `redirect_canonical`** : à priorité 1, `redirect_guess_404_permalink()` transformerait notre 404 en **301 vers un contenu deviné** — pire que la panne d'origine |
| 10 | Corps servi : `404.html` du thème, 404 nu, ou mini-XML ? | **`404.html`** | Un corps sur mesure donnerait **deux comportements différents pour un même fait** selon qu'il vient du cœur ou de nous, et perdrait l'égalité mesurable avec `mtb_resultat` qu'exige la tâche 3. D8 porte sur les pages servies aux visiteurs |
| 11 | Garde de méthode HTTP, comme `redirections-301` ? | **Non, écart délibéré** | Une 301 sur un POST perd le corps ; **un 404 ne perd rien**. Le cœur ne teste pas la méthode sur sa branche 404 : la tester **ferait diverger nos deux 404** |
| 12 | Normaliser la casse du nom de sous-plan ? | **Non** | Répondre 404 pour un nom que **le cœur n'a jamais routé**, c'est l'option C par la petite porte |
| 13 | Donner une commande WP-CLI comme témoin ? | **Non** | **Contredirait le motif 3 gelé** en tête du `bootstrap.php` — « témoins d'échec disjoints », ce qui tient ce module séparé de `redirections-301`. Le résidu est **nommé au §8**, pas masqué |
| 14 | Où écrire la relève de l'énumération des hooks de front ? | **Ici, au §7** | **Constat** : le texte de l'amendement de #24 §15 **n'a jamais été collé dans `issue-1.md`**. La pratique en vigueur (`page-protegee`, #52 §11.2) est que l'amendement vit dans le contrat de son issue. **Les contrats gelés ne se rouvrent pas** |
| 15 | Qui écrit dans `docs/contracts/issue-1.md` ? | **Pas cette chaîne** | Fichier partagé par les trois chaînes du lot, décision 70. **Texte livré prêt à coller au §12** |
| 16 | Geler ce contrat avant ou après la mesure due ? | **Avant, avec ses branches écrites** | Toutes les décisions sont prises et opposables dès maintenant ; **ce que la mesure peut changer est énuméré branche par branche** (§3.3), et le relevé s'ajoute en fin de fichier, daté |

---

## 14. Ce qui n'est pas mesuré à l'heure du gel — aucune de ces lignes n'est présentée ailleurs comme une mesure

1. **L'ordre exact et le contenu des sorties de `render_sitemaps()`** — la totalité du §3.1 est une
   **déduction de lecture**. `wp-includes/` n'est pas versionné. **Mesure due et bloquante** (§3.2 Q1–Q3).
2. **La méthode d'interrogation du registre** et l'existence de `wp_get_sitemap_providers()` — **la garde
   de désarmement en dépend** (Q4). Si aucune voie n'existe, **la garde tombe : arrêt et remontée, ne pas
   coder sans désarmement.**
3. **Les noms exacts des variables de requête** (Q5). Un nom faux = **un rappel qui ne mord jamais, en
   silence**.
4. **Que la route `.xsl` laisse le nom de sous-plan vide** (Q2) — dont dépend la garde 1. Le mode de
   panne associé est **totalement muet**.
5. **Le comportement quand le plan du site est désactivé** (`blog_public = 0`, Q6).
6. **Que la variable `sitemap` soit publique**, donc que la forme `?sitemap=users` atteigne le rappel
   (Q7).
7. **Que `redirect_canonical` transformerait un 404 posé en priorité 1 en 301** — déduction du chemin de
   code, **vérifiée par P5 dans les deux sens**.
8. **Que le corps des 16 035 o soit l'index du blog** — test de falsification du §3.3, **dont deux
   branches sur trois changent ce contrat**.

---

## 15. Questions bloquantes

**Aucune.** Cette issue ne touche **aucun fait d'élevage** : aucun nom de chien, aucune date, aucune
généalogie, aucun numéro LOF, aucun résultat de test ou de concours, aucun écran de l'éleveuse.
`/wp-sitemap-users-1.xml` n'a jamais existé sur mtbrabant.com. **Rien à demander à l'éleveuse.**

**Une réserve, qui est une mesure et non une question** : le §3 est bloquant, et deux de ses trois
branches de falsification changent la forme du correctif avant qu'une ligne ne soit écrite.

**Un signalement de séquence, qui appartient au lead et non à cette chaîne** : #50 et #49 ouvrent **le
même module** à deux jours d'intervalle, sur le même sujet (le résidu d'énumération des auteurs), avec le
même protocole de mesure. **La fusion aurait été moins chère.** La séquence #50 puis #49 est tranchée
par `docs/ETAT.md` et n'est pas contestée ici ; **l'économie non faite est nommée plutôt que tue.**

---

# Amendement — 2026-09-08, issue #50 : le relevé des mesures dues, et les deux lignes du §8 qu'il dément

> Ajout daté, conforme à la **convention d'amendement** déclarée en tête de ce contrat : aucune section
> numérotée ci-dessus n'est réécrite. Les §3, §8 et §14 restent lisibles tels qu'ils ont été gelés ;
> **cet amendement en contredit deux lignes ouvertement, et dit pourquoi.**

## A. Le contexte de mesure

Relevé le **2026-09-08**, base de développement, **WordPress 6.9**, hôte `http://localhost:3005`,
mesuré **depuis le conteneur** (`curl --path-as-is --connect-to`, sans cookie ni `--user`), commit
`4bc8daa`. `permalink_structure` = `/%postname%/` · `blog_public` = `1`.

**Calibrage reproduit à l'octet** — les deux valeurs de référence de T106 sont inchangées :
`/wp-sitemap-users-1.xml` → `200 text/html 16035` · `/nexiste-pas-du-tout/` → `404 text/html 18284`.
**La dette n'avait pas bougé**, aucun double relevé n'était donc dû. Contre-épreuve de méthode : la
forme de repli `-H 'Host: …'` rend le **même triplet** que `--connect-to`.

## B. Le test de falsification du §3.3 — **branche 1**

| Adresse | `<body class>` avant correctif |
|---|---|
| `/wp-sitemap-users-1.xml` | **`blog`** — pas `error404` |
| `/wp-sitemap-posts-mtb_resultat-1.xml` | `error404` |
| `/nexiste-pas-du-tout/` | `error404` |

**Déduction confirmée. Le contrat s'applique intégralement.** Les 16 035 octets étaient bien **l'index
du blog servi à une adresse en `.xml`**, et la valeur cible mesurable était donc `18284`.

## C. Les sept questions du §3.2 — cinq confirmées, une confirmée avec un fait de plus, **une infirmée**

| # | Verdict | Relevé |
|---|---|---|
| **Q1** | **Confirmée** | `class-wp-sitemaps.php:69` — `add_action( 'template_redirect', array( $this, 'render_sitemaps' ) )`, **sans argument de priorité, donc 10**. La priorité 20 est un chiffre relevé, plus une supposition |
| **Q2** | **Confirmée, et le piège nommé au §3.2 ne se réalise pas** | `class-wp-sitemaps.php:172` — le bail est **combiné**, `if ( ! ( $sitemap \|\| $stylesheet_type ) )`, et **précède** la branche de feuille de style. Les deux règles `.xsl` (`sitemaps.php:140-141`) n'écrivent que `sitemap-stylesheet` et laissent `sitemap` **vide** : **la garde 1 protège bien les deux `.xsl`**, aucune inversion des gardes 1 et 2 n'est requise. La garde 2 reste écrite, **redondante à dessein** |
| **Q3** | **Confirmée. La dette existe exactement telle qu'écrite** | `:200-202` branche « fournisseur absent » = **`return` nu, aucun code de statut** · `:211-215` branche « liste vide » = `set_404()` + `status_header( 404 )` + `return` |
| **Q4** | **Confirmée — branche (a). Le désarmement est disponible** | `sitemaps.php:52-56`, `wp_get_sitemap_providers()` rend `registry->get_providers()`, **tableau associatif indexé par nom** (`class-wp-sitemaps-registry.php:26,55,83-85`). Garde 5 = `isset()`. **Corollaire relevé** : `default-filters.php:542` accroche `wp_sitemaps_get_server` à `init` — le serveur est déjà initialisé à `template_redirect`, la garde 5 ne provoque **aucune initialisation tardive** |
| **Q5** | **Confirmée verbatim** | `sitemaps.php:132,133,139,145-151` — `sitemap`, `sitemap-subtype`, `sitemap-stylesheet`, `paged`. Recopiés du fichier lu, jamais de mémoire |
| **Q6** | **INFIRMÉE sur le mécanisme** — voir §D.2 | `class-wp-sitemaps.php:65-75` — `register_rewrites()` **et** l'accroche sont **inconditionnels** ; `sitemaps_enabled()` ne saute que `register_sitemaps()` |
| **Q7** | **Confirmée, mais insuffisante à elle seule** — voir §D.1 | `rewrite.php:162-174` — `sitemap` est bien une variable de requête **publique** |

**Un cinquième fait du cœur, que la déduction du §3.1 ne portait pas** : `class-wp-sitemaps.php:176-180`,
le cœur pose lui-même `set_404()` + `status_header( 404 )` quand `sitemaps_enabled()` est faux. Écrit
ici parce qu'il change la lecture de l'état `plan_du_site_desactive` (§D.2).

**Le fait qui fonde la garde 1** : `formatting.php:5590,5643-5645` — `sanitize_text_field()` **n'a aucun
type déclaré** et rend `''` sur un tableau. `?sitemap[]=users` est donc neutralisé **sans `TypeError`
malgré `strict_types`, et sans notice** — vérifié, `debug.log` à 0 octet.

## D. Les deux lignes du §8 que la mesure dément — corrigées ici, le §8 reste lisible tel quel

### D.1 — `?sitemap=users` reste **301**, et ne peut pas devenir 404 sans casser le §6

Le §8 gèle `?sitemap=users` et `?sitemap=users&paged=1` en **404**, *« changement voulu, à mesurer ;
dépend de Q7 »*. **Q7 est confirmée — la variable est bien publique — et cela ne suffit pas.** Ces
formes partent en **301 vers `/` dès la priorité 10**, par `redirect_canonical`
(`default-filters.php:666`), qui `exit` **avant que la priorité 20 n'existe**.

**Ce n'est pas une régression : elles étaient déjà 301 avant le correctif.** C'est **une prévision qui
ne se réalise pas**, et elle ne peut se réaliser qu'en descendant le rappel **sous** la priorité de
`redirect_canonical` — ce que le §6 et le §11 **interdisent absolument**, puisque c'est exactement la
panne « 301 vers un contenu deviné ».

> **La priorité 20 et le 404 sur la forme en requête sont mutuellement exclusifs. Le contrat a tranché
> pour la priorité 20, et il ne se contourne pas.** L'arbitrage 9 est confirmé par la mesure ; c'est
> l'énumération du §8 qui était trop optimiste.

**Lignes du §8 corrigées :**

| Cas | Ce que le §8 gelait | **Comportement mesuré, avant ET après** |
|---|---|---|
| `?sitemap=users`, `?sitemap=users&paged=1` | 404 | **301 vers `/`, inchangé** |
| `?sitemap=Users`, `?sitemap=USERS` | « inchangé, 200 » | **301 vers `/`, inchangé** |

**Le fait qui comptait tient dans les deux cas** : nous ne répondons **pas** 404 sur une casse que le
cœur n'a jamais routée. Seul le code de la réponse inchangée était mal prédit.

### D.2 — `plan_du_site_desactive` : juste sur le résultat, **faux sur le mécanisme**

Le §8 écrit que le rappel **« se désarme tout seul »** quand le plan du site est désactivé, la variable
`sitemap` n'étant pas enregistrée. **C'est faux** : `register_rewrites()` et l'accroche sont
inconditionnels (Q6), la variable existe donc **même plan du site désactivé**, et la garde 1 ne désarme
rien.

**Ce qui se produit réellement** : le cœur a déjà posé le 404 aux l. 176-180, et notre rappel **repose
le même 404**. **Même réponse, aucun effet supplémentaire, aucun dommage.** L'état reste conforme sur
son résultat ; sa ligne « Détecté par » et sa ligne « Comportement » décrivaient un mécanisme qui
n'existe pas.

> **Non mesuré en direct, et dit comme tel** : basculer `blog_public` est une **écriture en base**,
> interdite pendant que la chaîne #53 travaille dans le même arbre. **Établi par lecture du code du
> cœur, pas par relevé.**

## E. Le relevé avant / après — P0 à P7

**P0** — `php -l` sur les deux fichiers, joué **quatre fois**, avant tout `curl` : **0 erreur**.

**P1 — sonde d'existence** : avant **et** après, **5 `<loc>`**, **aucune portant `users`**. Le module
est chargé dans les deux relevés ; **rien ne se lit à contresens**.

**P2 — l'objet de l'issue**

| Adresse | Avant | Après |
|---|---|---|
| `/wp-sitemap-users-1.xml` | `200 text/html 16035`, `<body class="blog …">` | **`404 text/html 18284`**, `<body class="error404 …">` |
| `/wp-sitemap-posts-mtb_resultat-1.xml` | `404 text/html 18284` | `404 text/html 18284` |
| `/nexiste-pas-du-tout/` | `404 text/html 18284` | `404 text/html 18284` |

> **L'égalité imposée est ATTEINTE** : après correctif, `/wp-sitemap-users-1.xml` et
> `/wp-sitemap-posts-mtb_resultat-1.xml` rendent **le même statut, le même type et la même taille, à
> l'octet — 18 284**. *La tâche 3 de l'issue est vérifiée par mesure, pas par appréciation.*

`redirect_url` sur `/wp-sitemap-users-1.xml` : **vide**, avant comme après. **Le devineur de 404 n'a pas
mordu** — le contrôle P5 de la priorité 20 est concluant.

**P3 — les cinq sous-plans**, liste extraite de l'index et jamais codée en dur : statut, type et taille
**identiques à l'octet** avant/après (`289`, `867`, `2014`, `3518`, `256`), nombres de `<loc>` internes
identiques (1, 7, 18, 32, 1), **aucun `/author/` nulle part**.

**P4 — les deux feuilles `.xsl`** : `200 application/xml 3534` et `2817`, **identiques à l'octet**. *Le
seul contrôle dont la régression aurait été totalement muette : aucune régression.*

**P5 — cas limites**

| Cas | Avant | Après |
|---|---|---|
| `/wp-sitemap-users-0.xml` | `200 16035` | **`404 18284`** |
| `/wp-sitemap-users-x-1.xml` (route à sous-type) | `200 16035` | **`404 18284`** |
| `POST /wp-sitemap-users-1.xml` | `200 16035` | **`404 18284`** |
| `HEAD /wp-sitemap-users-1.xml` | `200 0` | **`404 0`** |
| `/wp-sitemap-users-2.xml` | `404 18284` | `404 18284` |
| `/wp-sitemap-usersx-1.xml`, `/wp-sitemap-inconnu-1.xml` | `200 16035` | `200 16035` — **résidu assumé, borne 3** |
| `?sitemap[]=users` | `301 → /` | `301 → /`, **`debug.log` à 0 octet** |

**Surprise de mesure, consignée** : `/wp-sitemap-users-2.xml` répondait **déjà 404 avant** le correctif,
pour une **autre cause** (`paged=2` sur `is_home()` sans seconde page). Ni le contrat ni le plan ne
l'annonçaient. Il répond désormais 404 **par notre rappel** — même code, cause différente.

**P6 — non-régressions des lots 18 et 19** : `wp mtb verifier-redirections` → **code 0** ·
`/author/admin/` → `200 16379` **inchangé** et `/?author=1` → `301` inchangé (**T105 n'est pas fermée
par accident, et le protocole le prouve**) · `/` `25491`, `/portees/` `31819`, `/chien/jango/` `28084`,
`/contact/` `19409`, `/travail/` `35411` → **200, tailles identiques à l'octet** *(notre rappel court
sur chacune de ces requêtes)* · `/chien/halan/` → `<meta name='robots'>` **inchangée**, #52 intacte ·
`/bhpl/port%C3%A9e-m-2016/` **et** sa forme UTF-8 brute → `301` vers `/portees/m-2016/`, inchangé ·
**`debug.log` : 0 octet, avant comme après.**

**P7 — la recopie** : `grep -rn "'users'" wp-content/` rend **exactement une ligne**, celle de la
constante. Et **aucun** des 5 noms listés par l'index n'appartient à `FOURNISSEURS_RETIRES`. *La tâche 2
est vraie par construction et vérifiée par mesure.*

**Le `diff` avant/après ne porte que quatre blocs**, tous voulus. **Tout le reste est identique à
l'octet.** Les deux relevés intermédiaires imposés au §9 sont **strictement identiques au relevé
avant** : après la seule constante — *preuve d'équivalence avec l'ancien littéral* — et après la
fonction non encore accrochée — *preuve que l'accroche seule est causale*.

## F. Ce qui reste non mesuré après cet amendement — et n'est présenté nulle part comme mesuré

1. **La garde de désarmement (garde 5) n'est pas éprouvée en direct.** La voir se désarmer exigerait de
   retirer notre `add_filter` ou d'enregistrer un fournisseur tiers — **hors empreinte, et l'arbre est
   partagé avec #53**. Elle est établie **par lecture du registre**, pas par mesure.
2. **Q6 n'est pas mesuré en direct** : basculer `blog_public` est une écriture en base, interdite
   pendant #53. **Lecture de code seule.**
3. **Le triplet de `/wp-sitemap.xml` n'a été capturé qu'APRÈS** (`200 application/xml 622`) ; avant, seule
   sa liste de `<loc>` l'a été — identique. Il **ne peut pas** avoir changé (`'index'` n'est pas dans
   `FOURNISSEURS_RETIRES`, et le cœur `exit` à la l. 195 bien avant la priorité 20), **mais le chiffre
   d'avant manque et n'est pas maquillé**. Le rejouer supposerait de revenir en arrière dans un arbre
   partagé : **écarté sciemment.**
4. **Les numéros de ligne du cœur cités dans le code et ci-dessus sont épinglés à WordPress 6.9.**
   `wp-includes/` n'étant pas versionné, ils **se périmeront en silence** à la prochaine montée de
   version. Résidu nommé, non masqué.

## G. Ce que la passe de refacto a corrigé — quatre commentaires, aucune ligne exécutable

Aucun défaut de code : ni sécurité, ni robustesse, ni convention, ni duplication, ni origine tierce. Les
corrections sont **toutes de la même famille — des commentaires que #50 a rendus faux** :

1. `plan-du-site.php` — « `wp_sitemaps_add_provider`, **le seul crochet que ce fichier accroche
   encore** » : faux dès l'`add_action` de #50, resserré à « le seul crochet **du plan du site** ».
2. `bootstrap.php` — un renvoi qui pointait « au-dessus de `ecarter_le_fournisseur_utilisateurs()` »,
   devenu faux depuis que la constante s'est intercalée.
3. `bootstrap.php` — « SA PANNE EST INVISIBLE » prenait **le module entier** pour sujet et se trouvait
   **contredit cinq lignes plus bas** par le paragraphe de #50. Ramené à ce qu'il décrit — la panne de
   la conversion — et appareillé à la « deuxième surface silencieuse ».
4. `bootstrap.php`, motif 2 — **la correction qui comptait** : « le renommage du dossier en
   `_indexation-heritee` **ne remet plus rien dans Google** » était faux depuis le 2026-09-05 et
   davantage depuis #50. Ce renommage **rend au plan du site le fournisseur écarté — donc l'identifiant
   de connexion de l'administrateur — et fait tomber le 404**. *Un piège matériel pour qui aurait
   appliqué ce renommage de bonne foi.* Portée resserrée et augmentée des deux effets réels.

Deux points de prose ont été routés au lead et corrigés dans le même geste : le motif du `true` de
`in_array()`, qui invoquait un danger propre à PHP 7 sur un plafond PHP 8.1 — **le `true` reste, c'est
le pourquoi qui devient vrai** — et un en-tête annonçant « quatre faits » au-dessus d'un bloc qui en
porte cinq, *la même erreur en miniature que celle que #24 §15 s'interdisait*.
