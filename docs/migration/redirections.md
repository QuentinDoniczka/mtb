# Carte des redirections — ancienne URL vers nouvelle URL

## 1. Ce que ce fichier est, et n'est pas

Ce fichier est **un relevé daté, reproductible** par la commande `wp mtb verifier-redirections`. Il
constate ce qu'un contrôle a mesuré à une date donnée, sur un commit donné.

**La source de vérité est `wp-content/plugins/mtb-core/includes/migration/redirections-301/carte.php`**,
en PHP, dans l'extension. C'est ce fichier PHP qui déclare les 52 chemins, leurs verdicts, les identités
de cible et les notes — jamais ce document Markdown.

**Ce fichier n'est JAMAIS lu à l'exécution.** `docs/` n'est pas déployé par FTP ; parser du Markdown au
moment de servir une page serait un mode de panne ; et un document Markdown se réordonne et s'annote —
les 301 ne doivent pas changer avec. Le service 301 (`template_redirect`, priorité 1) ne lit que
`carte.php`, jamais `docs/migration/redirections.md`.

**État : 52 URL sur 52.** Les 52 adresses du `sitemap.xml` de l'ancien site sont couvertes par la carte,
livrées et mesurées. Il n'y a plus de reste à reprendre pour cette carte.

---

## 2. Le relevé daté

- **Date de la mesure** : 2026-09-05
- **Sha du commit mesuré** : `a765abf`
- **Condensé SHA-256 du référentiel `docs/migration/source/sitemap.xml`** :
  `bb78eebcd0fa3d8f3b739b6fad9df1ddf49b6abcd49da033d3f78f76cc09cd1e`
  (forme à fins de ligne `\n` ; sur un disque Windows le fichier est en CRLF et son condensé brut
  diffère — la commande de vérification accepte les deux formes et indique laquelle a répondu)
- **Hôte mesuré** : `http://localhost:3005/`
- **Version de WordPress** : 6.9
- **État de la page `la-meute` au moment du relevé** : **absente** — elle a été créée puis supprimée
  exprès, le temps de mesurer l'état « avant » et l'état « après » exigés au §5.

### Contrôle 1 — `wp mtb verifier-redirections`

Code de sortie **0**, ses 8 étapes conformes :

- 52 adresses du référentiel = 52 clés en carte (aucune manquante, aucune en trop) ;
- **46 cibles résolues, dont 0 par le repli** (le repli sur les portées, déclaré au contrat §4, n'a été
  nécessaire pour aucune des 27 — aucune portée n'est protégée par mot de passe à ce jour) ;
- **12 liens internes** trouvés dans **6 contenus publiés** vers **9 cibles distinctes** ;
- **5 contenus** portant `_mtb_robots_source` (attendu 5) ;
- site servi à la racine, préfixe de chemin vide.

### Contrôle 2 — ce que le serveur RÉPOND

Mesuré au `curl --path-as-is`, **sans cookie et sans `--user`**, depuis le conteneur `wpcli` :

- **46 adresses de verdict `301`** → `301` puis **`200` au second saut**. Aucune 404, aucune boucle.
- **5 adresses de verdict `identique`** (`/`, `/bhpl/`, `/travail/`, `/placement/`, `/contact/`) →
  **`200`** directement, sans redirection.
- **1 adresse de verdict `identique_apres_creation`** (`/la-meute/`) → **`404`** avant création de la
  page, **`200`** après. Par conception (voir §5).
- **Les 30 adresses accentuées ont été mesurées DEUX FOIS** : en forme percent-encodée et en forme
  UTF-8 brute. **Les deux formes rendent le même `301` vers la même cible.** Total : **82 mesures**
  pour 52 adresses (30 × 2 + 22 × 1).

**Piège de méthode, à consigner comme une leçon et non comme une anecdote** : une première passe lancée
depuis le shell Git Bash de Windows donnait `404` sur toutes les formes brutes (accentuées, non
encodées). Ce n'était **pas** un défaut du service 301 : c'était un artefact du shell, qui transcodait
l'UTF-8 en Latin-1 avant d'envoyer la requête (`%e9` au lieu de `%C3%A9`) — il mesurait une adresse
**qui n'a jamais existé** sur l'ancien site. La mesure valable se joue **depuis le conteneur** `wpcli`,
dont l'environnement ne transcode pas. Deux contrôles peuvent mesurer deux choses différentes rien qu'en
changeant l'endroit d'où on lance la commande ; c'est pour cela que l'hôte, le commit et l'endroit de
la mesure sont consignés ci-dessus.

**Ne jamais présenter l'un des deux contrôles pour l'autre.** Le contrôle 1 dit que la table est
cohérente avec le référentiel (`sitemap.xml`) ; le contrôle 2 dit ce que le serveur **répond**
réellement à une requête. Une carte cohérente n'implique pas un serveur qui répond juste, et
inversement.

---

## 3. Décompte — 52 = 6 + 46

**52 entrées, pas 46.** Les six adresses qui ne redirigent pas figurent dans la carte avec leur propre
verdict, ce qui rend l'absence de 301 **structurelle et non oubliable** plutôt qu'un simple manque.

| Verdict | Nombre | Lesquelles |
|---|---|---|
| `identique` | **5** | `/` · `/bhpl/` · `/travail/` · `/placement/` · `/contact/` |
| `identique_apres_creation` | **1** | `/la-meute/` — voir §5 |
| `301` | **46** | 27 portées · 17 fiches de chien · `/bhpl/littérature/` · `/mentions-légales/` |

Total : 5 + 1 + 46 = **52**.

---

## 4. Le tableau des 52 — dans l'ordre du sitemap source

Pour les verdicts `identique` et `identique_apres_creation`, la colonne « Identité de cible » est vide
par construction (`carte.php` : `'cible' => array()`) — il n'y a rien à résoudre, la page répond
directement ou pas du tout. Le « permalien servi » indiqué pour ces cinq lignes est l'adresse locale
équivalente, servie sans redirection.

| URL source lisible | URL source encodée | Verdict | Identité de cible | Permalien servi | Code | Second saut |
|---|---|---|---|---|---|---|
| `https://www.mtbrabant.com/` | `https://www.mtbrabant.com/` | identique | — | `http://localhost:3005/` | 200 | — |
| `https://www.mtbrabant.com/bhpl/` | `https://www.mtbrabant.com/bhpl/` | identique | — | `http://localhost:3005/bhpl/` | 200 | — |
| `https://www.mtbrabant.com/bhpl/portée-a3-2025/` | `https://www.mtbrabant.com/bhpl/port%C3%A9e-a3-2025/` | 301 | (`portee`, `A3 2025`) | `http://localhost:3005/portees/a3-2025/` | 301 | 200 |
| `https://www.mtbrabant.com/bhpl/portée-a2-2025/` | `https://www.mtbrabant.com/bhpl/port%C3%A9e-a2-2025/` | 301 | (`portee`, `A2 2025`) | `http://localhost:3005/portees/a2-2025/` | 301 | 200 |
| `https://www.mtbrabant.com/bhpl/portée-a1-2025/` | `https://www.mtbrabant.com/bhpl/port%C3%A9e-a1-2025/` | 301 | (`portee`, `A1 2025`) | `http://localhost:3005/portees/a1-2025/` | 301 | 200 |
| `https://www.mtbrabant.com/bhpl/portée-v2-2024/` | `https://www.mtbrabant.com/bhpl/port%C3%A9e-v2-2024/` | 301 | (`portee`, `V2 2024`) | `http://localhost:3005/portees/v2-2024/` | 301 | 200 |
| `https://www.mtbrabant.com/bhpl/portée-v1-2024/` | `https://www.mtbrabant.com/bhpl/port%C3%A9e-v1-2024/` | 301 | (`portee`, `V1 2024`) | `http://localhost:3005/portees/v1-2024/` | 301 | 200 |
| `https://www.mtbrabant.com/bhpl/portée-u3-2023/` | `https://www.mtbrabant.com/bhpl/port%C3%A9e-u3-2023/` | 301 | (`portee`, `U3 2023`) | `http://localhost:3005/portees/u3-2023/` | 301 | 200 |
| `https://www.mtbrabant.com/bhpl/portée-u2-2023/` | `https://www.mtbrabant.com/bhpl/port%C3%A9e-u2-2023/` | 301 | (`portee`, `U2 2023`) | `http://localhost:3005/portees/u2-2023/` | 301 | 200 |
| `https://www.mtbrabant.com/bhpl/portée-u1-2023/` | `https://www.mtbrabant.com/bhpl/port%C3%A9e-u1-2023/` | 301 | (`portee`, `U1 2023`) | `http://localhost:3005/portees/u1-2023/` | 301 | 200 |
| `https://www.mtbrabant.com/bhpl/portée-t-2022/` | `https://www.mtbrabant.com/bhpl/port%C3%A9e-t-2022/` | 301 | (`portee`, `T 2022`) | `http://localhost:3005/portees/t-2022/` | 301 | 200 |
| `https://www.mtbrabant.com/bhpl/portée-s2-2021/` | `https://www.mtbrabant.com/bhpl/port%C3%A9e-s2-2021/` | 301 | (`portee`, `S2 2021`) | `http://localhost:3005/portees/s2-2021/` | 301 | 200 |
| `https://www.mtbrabant.com/bhpl/portée-s1-2021/` | `https://www.mtbrabant.com/bhpl/port%C3%A9e-s1-2021/` | 301 | (`portee`, `S1 2021`) | `http://localhost:3005/portees/s1-2021/` | 301 | 200 |
| `https://www.mtbrabant.com/bhpl/portée-r-2020/` | `https://www.mtbrabant.com/bhpl/port%C3%A9e-r-2020/` | 301 | (`portee`, `R 2020`) | `http://localhost:3005/portees/r-2020/` | 301 | 200 |
| `https://www.mtbrabant.com/bhpl/portée-p2-2019/` | `https://www.mtbrabant.com/bhpl/port%C3%A9e-p2-2019/` | 301 | (`portee`, `P2 2019`) | `http://localhost:3005/portees/p2-2019/` | 301 | 200 |
| `https://www.mtbrabant.com/bhpl/portée-p-2019/` | `https://www.mtbrabant.com/bhpl/port%C3%A9e-p-2019/` | 301 | (`portee`, `P 2019`) | `http://localhost:3005/portees/p-2019/` | 301 | 200 |
| `https://www.mtbrabant.com/bhpl/portée-o-2018/` | `https://www.mtbrabant.com/bhpl/port%C3%A9e-o-2018/` | 301 | (`portee`, `O 2018`) | `http://localhost:3005/portees/o-2018/` | 301 | 200 |
| `https://www.mtbrabant.com/bhpl/portée-n-2017/` | `https://www.mtbrabant.com/bhpl/port%C3%A9e-n-2017/` | 301 | (`portee`, `N_2 2017`) — voir note ci-dessous | `http://localhost:3005/portees/n-2017/` | 301 | 200 |
| `https://www.mtbrabant.com/bhpl/portée-m-2016/` | `https://www.mtbrabant.com/bhpl/port%C3%A9e-m-2016/` | 301 | (`portee`, `M 2016`) | `http://localhost:3005/portees/m-2016/` | 301 | 200 |
| `https://www.mtbrabant.com/bhpl/portée-j-2014/` | `https://www.mtbrabant.com/bhpl/port%C3%A9e-j-2014/` | 301 | (`portee`, `J 2014`) | `http://localhost:3005/portees/j-2014/` | 301 | 200 |
| `https://www.mtbrabant.com/bhpl/portée-h-2012/` | `https://www.mtbrabant.com/bhpl/port%C3%A9e-h-2012/` | 301 | (`portee`, `H 2012`) | `http://localhost:3005/portees/h-2012/` | 301 | 200 |
| `https://www.mtbrabant.com/bhpl/portée-g-2011/` | `https://www.mtbrabant.com/bhpl/port%C3%A9e-g-2011/` | 301 | (`portee`, `G 2011`) | `http://localhost:3005/portees/g-2011/` | 301 | 200 |
| `https://www.mtbrabant.com/bhpl/portée-f-2010/` | `https://www.mtbrabant.com/bhpl/port%C3%A9e-f-2010/` | 301 | (`portee`, `F 2010`) | `http://localhost:3005/portees/f-2010/` | 301 | 200 |
| `https://www.mtbrabant.com/bhpl/portée-e-2009/` | `https://www.mtbrabant.com/bhpl/port%C3%A9e-e-2009/` | 301 | (`portee`, `E 2009`) | `http://localhost:3005/portees/e-2009/` | 301 | 200 |
| `https://www.mtbrabant.com/bhpl/portée-d-2008/` | `https://www.mtbrabant.com/bhpl/port%C3%A9e-d-2008/` | 301 | (`portee`, `D 2008`) | `http://localhost:3005/portees/d-2008/` | 301 | 200 |
| `https://www.mtbrabant.com/bhpl/portée-c-2007/` | `https://www.mtbrabant.com/bhpl/port%C3%A9e-c-2007/` | 301 | (`portee`, `C 2007`) | `http://localhost:3005/portees/c-2007/` | 301 | 200 |
| `https://www.mtbrabant.com/bhpl/portée-s-2001/` | `https://www.mtbrabant.com/bhpl/port%C3%A9e-s-2001/` | 301 | (`portee`, `S 2001`) | `http://localhost:3005/portees/s-2001/` | 301 | 200 |
| `https://www.mtbrabant.com/bhpl/portée-m-1996/` | `https://www.mtbrabant.com/bhpl/port%C3%A9e-m-1996/` | 301 | (`portee`, `M 1996`) | `http://localhost:3005/portees/m-1996/` | 301 | 200 |
| `https://www.mtbrabant.com/bhpl/portée-l-1995/` | `https://www.mtbrabant.com/bhpl/port%C3%A9e-l-1995/` | 301 | (`portee`, `L 1995`) | `http://localhost:3005/portees/l-1995/` | 301 | 200 |
| `https://www.mtbrabant.com/bhpl/littérature/` | `https://www.mtbrabant.com/bhpl/litt%C3%A9rature/` | 301 | (`page`, `litterature`) | `http://localhost:3005/litterature/` | 301 | 200 |
| `https://www.mtbrabant.com/travail/` | `https://www.mtbrabant.com/travail/` | identique | — | `http://localhost:3005/travail/` | 200 | — |
| `https://www.mtbrabant.com/la-meute/` | `https://www.mtbrabant.com/la-meute/` | identique_apres_creation | — | — (absente au moment du relevé) | 404 | — |
| `https://www.mtbrabant.com/la-meute/very-best/` | `https://www.mtbrabant.com/la-meute/very-best/` | 301 | (`chien`, `very-best`) | `http://localhost:3005/chien/very-best/` | 301 | 200 |
| `https://www.mtbrabant.com/la-meute/you/` | `https://www.mtbrabant.com/la-meute/you/` | 301 | (`chien`, `you`) | `http://localhost:3005/chien/you/` | 301 | 200 |
| `https://www.mtbrabant.com/la-meute/tesla/` | `https://www.mtbrabant.com/la-meute/tesla/` | 301 | (`chien`, `tesla`) | `http://localhost:3005/chien/tesla/` | 301 | 200 |
| `https://www.mtbrabant.com/la-meute/roxane/` | `https://www.mtbrabant.com/la-meute/roxane/` | 301 | (`chien`, `roxane`) | `http://localhost:3005/chien/roxane/` | 301 | 200 |
| `https://www.mtbrabant.com/la-meute/ray-ban/` | `https://www.mtbrabant.com/la-meute/ray-ban/` | 301 | (`chien`, `ray-ban`) | `http://localhost:3005/chien/ray-ban/` | 301 | 200 |
| `https://www.mtbrabant.com/la-meute/rolex/` | `https://www.mtbrabant.com/la-meute/rolex/` | 301 | (`chien`, `rolex`) | `http://localhost:3005/chien/rolex/` | 301 | 200 |
| `https://www.mtbrabant.com/la-meute/youry/` | `https://www.mtbrabant.com/la-meute/youry/` | 301 | (`chien`, `youry`) | `http://localhost:3005/chien/youry/` | 301 | 200 |
| `https://www.mtbrabant.com/la-meute/pégaz/` | `https://www.mtbrabant.com/la-meute/p%C3%A9gaz/` | 301 | (`chien`, `pegaz`) — voir note ci-dessous | `http://localhost:3005/chien/pegaz/` | 301 | 200 |
| `https://www.mtbrabant.com/la-meute/jango/` | `https://www.mtbrabant.com/la-meute/jango/` | 301 | (`chien`, `jango`) | `http://localhost:3005/chien/jango/` | 301 | 200 |
| `https://www.mtbrabant.com/la-meute/opium/` | `https://www.mtbrabant.com/la-meute/opium/` | 301 | (`chien`, `opium`) | `http://localhost:3005/chien/opium/` | 301 | 200 |
| `https://www.mtbrabant.com/la-meute/grocky/` | `https://www.mtbrabant.com/la-meute/grocky/` | 301 | (`chien`, `grocky`) | `http://localhost:3005/chien/grocky/` | 301 | 200 |
| `https://www.mtbrabant.com/la-meute/etch/` | `https://www.mtbrabant.com/la-meute/etch/` | 301 | (`chien`, `etch`) | `http://localhost:3005/chien/etch/` | 301 | 200 |
| `https://www.mtbrabant.com/la-meute/happy/` | `https://www.mtbrabant.com/la-meute/happy/` | 301 | (`chien`, `happy`) | `http://localhost:3005/chien/happy/` | 301 | 200 |
| `https://www.mtbrabant.com/la-meute/halan/` | `https://www.mtbrabant.com/la-meute/halan/` | 301 | (`chien`, `halan`) | `http://localhost:3005/chien/halan/` | 301 | 200 |
| `https://www.mtbrabant.com/la-meute/maya/` | `https://www.mtbrabant.com/la-meute/maya/` | 301 | (`chien`, `maya`) | `http://localhost:3005/chien/maya/` | 301 | 200 |
| `https://www.mtbrabant.com/la-meute/tara/` | `https://www.mtbrabant.com/la-meute/tara/` | 301 | (`chien`, `tara`) | `http://localhost:3005/chien/tara/` | 301 | 200 |
| `https://www.mtbrabant.com/la-meute/gribouille/` | `https://www.mtbrabant.com/la-meute/gribouille/` | 301 | (`chien`, `gribouille`) | `http://localhost:3005/chien/gribouille/` | 301 | 200 |
| `https://www.mtbrabant.com/placement/` | `https://www.mtbrabant.com/placement/` | identique | — | `http://localhost:3005/placement/` | 200 | — |
| `https://www.mtbrabant.com/contact/` | `https://www.mtbrabant.com/contact/` | identique | — | `http://localhost:3005/contact/` | 200 | — |
| `https://www.mtbrabant.com/mentions-légales/` | `https://www.mtbrabant.com/mentions-l%C3%A9gales/` | 301 | (`page`, `mentions-legales`) | `http://localhost:3005/mentions-legales/` | 301 | 200 |

**52 lignes.**

**Note sur l'identifiant `N_2 2017`** : la portée servie à `/bhpl/portée-n-2017/` porte l'identifiant
`N_2 2017`, tiret bas compris — c'est la valeur transcrite du site source et celle qui est en base
(`post_title` du contenu servi à `/portees/n-2017/`). Ce n'est pas une faute de frappe qu'il faudrait
corriger : le remplacer casserait la redirection sans un mot.

**Note sur `pégaz` → `pegaz`** : seule adresse accentuée des 17 fiches de chien. Le slug servi
aujourd'hui est `pegaz`, sans accent — c'est la `reference` transcrite dans `donnees/chiens.json`, et
`sanitize_title_with_dashes()` aurait de toute façon produit la même chose pour l'avenir.

---

## 5. Le sort de `/la-meute/` — aucune redirection, et c'est un arbitrage

> **`docs/guide/page-creer-la-page-la-meute.md` est livré** et apprend à l'éleveuse à créer une page
> titrée « La meute », donc de slug `la-meute`, donc servie à `/la-meute/`. **Poser une 301 là
> casserait la page que le guide lui demande de créer.** Il n'existe par ailleurs **aucune cible de
> repli** : `mtb_chien` est déclaré `'has_archive' => false` (`content/chien/bootstrap.php:76`), il
> n'y a donc pas d'archive `/chien/` vers quoi rediriger.
>
> **Verdict : répond 404 avant l'acte d'édition documenté, 200 après — par conception, pas par
> oubli.** La dette **T30** est ainsi honorée sans être masquée. **Écart à D5 assumé et daté**, levé
> le jour où la page est publiée.

**Contrôle mesuré, avant ET après création de la page** :

- **Avant** création : `/la-meute/` → `404`.
- **Après** création et publication : `/la-meute/` → `200`.
- **Surtout, après création** : `/la-meute/jango/`, `/la-meute/pégaz/` et `/la-meute/gribouille/`
  **continuent de rendre `301`** vers leurs fiches (`/chien/jango/`, `/chien/pegaz/`,
  `/chien/gribouille/`) — la règle de pages du cœur (`(.?.+?)/?$`) ne les avale pas, la priorité 1 du
  service 301 les pré-empte.

Les deux relevés (avant / après) sont consignés ci-dessus, non supposés.

---

## 6. Ce qui n'a pas changé de slug, et pourquoi

**Aucun slug n'a été changé, et `includes/content/**` n'a pas été ouvert.** La moitié « normaliser » de
la décision 69 était **déjà acquise sans une ligne de code** :

- les 27 `slug_source` de `migration/portees-chiens/donnees/portees.json` sont `a1-2025`, `m-2016`,
  `s2-2021`… **sans accent** ;
- les 17 `reference` de `donnees/chiens.json` sont `etch`, `pegaz`, `ray-ban`, `very-best`… **sans
  accent** (`pégaz` → `pegaz`) ;
- `donnees/pages/*.json` porte `litterature`, `mentions-legales`, `bhpl`, `travail`, `placement` ;
- et pour l'avenir, `sanitize_title_with_dashes()` appelle `remove_accents()` : une page future titrée
  « Élevage » produirait `elevage` toute seule.

**Option explicitement écartée** : passer le slug du type « chien » de `chien` à `la-meute`, ce qui
aurait rendu **16 fiches sur 17 identiques** au lieu de redirigées. Motifs :

- coexistence fragile avec la page `la-meute` que le guide (`docs/guide/page-creer-la-page-la-meute.md`)
  apprend déjà à l'éleveuse à créer ;
- 17 permaliens changés ;
- hors empreinte de l'issue.

**D5 autorise explicitement la 301 — c'est la réponse, pas un pis-aller.**

---

## 7. Les douze ancres internes

**12 liens** dans **6 fiches de chiens publiées** — `etch`, `grocky`, `jango`, `opium`, `pegaz`,
`rolex` — vers **9 cibles distinctes** : `m-2016`, `j-2014`, `r-2020`, `n-2017`, `o-2018`, `p-2019`,
`s2-2021`, `u2-2023`, `t-2022`.

**Forme réellement stockée, MESURÉE** (et non déduite) : les `href` en base portent leurs **accents
intacts, en UTF-8 brut** — par exemple `https://www.mtbrabant.com/bhpl/portée-m-2016/`. L'hypothèse
d'une altération par `esc_url()` à l'import (envisagée au contrat #24 §8) est donc **INFIRMÉE** :
toutes les `formes` déclarées dans `carte.php` sont vides, par mesure et non par oubli.

**Vérifié au rendu** : **0 lien vers `mtbrabant.com` ne subsiste** dans le HTML servi des six fiches —
le filtre `the_content` (priorité 20) répare chacune des douze ancres au point d'application, sans
toucher au contenu stocké en base.

---

## 8. Écart déclaré — contenu stocké ≠ contenu servi

> Le contenu **stocké** garde l'ancienne URL ; le contenu **servi** porte la nouvelle. D4 porte sur le
> texte, qui ne bouge pas — mais c'est un écart, et **un écart non écrit n'est imputable à personne**
> (décision 46). Conséquence concrète : si l'éleveuse ouvre la fiche d'`Etch` dans l'éditeur, elle y
> verra encore `https://www.mtbrabant.com/bhpl/portée-m-2016/`. Rien ne l'invite à y toucher, et y
> toucher ne casserait rien.

---

## 9. Les cinq contenus non indexés

| Contenu | Titre public | Adresse servie |
|---|---|---|
| Fiche de chien | Halan | `/chien/halan/` |
| Fiche de chien | Ray-Ban | `/chien/ray-ban/` |
| Fiche de chien | Roxane | `/chien/roxane/` |
| Fiche de chien | Youry | `/chien/youry/` |
| Page libre | Placement | `/placement/` |

*(Titres publics relevés en base, jamais recomposés depuis un slug.)*

**Mesuré** : les cinq rendent `<meta name='robots' content='max-image-preview:large, noindex,
nofollow' />` dans le HTML servi. Trois témoins négatifs (`/chien/jango/`, `/portees/m-2016/`,
`/litterature/`) ne portent, eux, que `max-image-preview:large` — sans `noindex`.

**Mesuré aussi** : les cinq sont **retirés du plan du site** —

- sous-plan `mtb_chien` : 22 publiés, 18 listés, écart de 4 (les quatre fiches ci-dessus) ;
- sous-plan `page` : 9 publiés, 7 listés, écart de 2, dont **un seul est le nôtre** — `placement` —
  l'autre étant `espace-prive`, retiré par le filtre de l'issue #23 (contenu protégé par mot de passe,
  non `noindex`).

**Le motif, et la seule formulation autorisée** : sur les 17 fiches de chiens de l'ancien site, 4
portaient `noindex` et 13 non ; aucune n'était au menu principal ; mais `html/la-meute.html` liait
exactement les 13 sans `noindex`, et les 4 marquées plus Placement n'étaient liées depuis nulle part
(`docs/migration/source/README.md:87`). **La règle réelle : une page que plus rien ne lie était aussi
retirée des moteurs.**

Aucun motif d'élevage n'est avancé ici pour expliquer pourquoi ces quatre chiens ont quitté la meute —
la source ne l'énonce nulle part.

Phrase du guide, imposée, reprise telle quelle :

> « Ces cinq pages sont en ligne et lisibles par toute personne qui en connaît l'adresse, mais elles
> sont **volontairement tenues à l'écart des moteurs de recherche** — c'est ce que faisait déjà votre
> ancien site. Si vous souhaitez qu'une d'entre elles y revienne, il suffit de le demander. »

---

## 10. Ce qui n'est PAS mesurable maintenant

L'hôte de production et le fait que `mtbrabant.com` pointe un jour sur nous (BRIEF §15.4) · `https` et
la forme `www` / sans `www` · la préservation du percent-encodage par le serveur frontal · le
comportement d'un moteur de recherche réel face au `noindex`. **Faits d'exploitation, pas faits de
code.**

---

## 11. Fait nouveau — le fournisseur `users` du plan du site, écarté le 2026-09-05

Une **exception motivée** au contrat #24 §6.4, décidée le 2026-09-05 après mesure : le fournisseur
`users` du plan du site publiait `/author/admin/`, et en base `user_nicename` = `user_login` = `admin`
— le plan du site publiait donc l'**identifiant de connexion de l'administrateur** (BRIEF §4, « zéro
donnée personnelle inutile »).

Un filtre `wp_sitemaps_add_provider` renvoyant `false` pour le seul fournisseur `users` a été posé.

**Mesuré après** : `wp-sitemap.xml` ne liste plus `wp-sitemap-users-1.xml` ; les cinq autres sous-plans
y restent ; **plus aucun `/author/` n'apparaît dans aucun plan du site**.

**Résidu à écrire honnêtement** : l'archive d'auteur elle-même répond toujours `200` sur
`/author/admin/`, et `/?author=1` y redirige encore en `301`. Le plan du site ne publie plus
l'identifiant, mais **l'énumération reste possible pour qui la cherche** — #24 ne la ferme pas.
