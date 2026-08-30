# Contrat d'outillage — Issue #31 — Les diagnostics PHP ne sont ni journalisés ni masqués

> **Ce contrat n'est pas un contrat d'interface thème ↔ extension.** L'issue #31 ne touche ni le
> thème ni `mtb-core` : son empreinte est entièrement dans la stack Docker. Il n'y a donc ni
> fonction de lecture, ni bloc, ni chaîne de service à réconcilier. Ce document tient le rôle que
> tient ailleurs le contrat gelé : il fixe ce sur quoi les chaînes à venir ont le droit de
> s'appuyer, et il consigne une **requalification de l'énoncé de l'issue**, mesurée et non déduite.

Toutes les mesures ci-dessous ont été prises au SHA **`e18c8ae`**, sur une stack **reconstruite
depuis zéro** (`docker compose down -v` puis `up -d --build`), provisionnement mené jusqu'au
marqueur `[provision] terminé.` (11:25:24 → 11:34:07, soit **8 min 42 s** sur ce poste).
Versions : **PHP 8.1.34**, **WordPress 6.9**.

---

## 1. L'énoncé de l'issue est faux, et il faut dire lequel

L'issue #31 affirme : « `.env.example` ligne 26 porte `WORDPRESS_DEBUG=1`, mais le `wp-config.php`
du conteneur définit `WP_DEBUG` à `false` ».

**La première moitié est vraie, la seconde est fausse.**

| Affirmation | Verdict | Mesure |
|---|---|---|
| `.env.example:26` porte `WORDPRESS_DEBUG=1` | **VRAI** | `git show e18c8ae:.env.example` |
| `WORDPRESS_DEBUG` n'atteint pas le conteneur | **FAUX** | `compose.yaml:43` porte `WORDPRESS_DEBUG: ${WORDPRESS_DEBUG:-1}` depuis `c64087c` (2026-08-15, commit de bootstrap), `git blame` à l'appui. `docker compose exec wordpress` renvoie `1`. |
| `wp-config.php` définit `WP_DEBUG` à `false` | **FAUX** | `wp-config.php:116` = `define( 'WP_DEBUG', !!getenv_docker('WORDPRESS_DEBUG', '') );` — **dynamique**, évalué à chaque requête. `WP_DEBUG` vaut donc **`true`** sur le chemin web. |

C'est exactement ce qu'écrit déjà **`docs/ETAT.md` décision 29** (2026-08-17) : « `WP_DEBUG` vaut
`true` sur les requêtes web et `false` en WP-CLI, dans cette stack ».

**D'où vient l'énoncé faux : `docs/contracts/issue-8.md:1305`**, qui affirme « `WP_DEBUG` est en
réalité **à `false`** dans cette pile ». Cette phrase est une **mesure WP-CLI généralisée à tort au
chemin web** — précisément le piège que la décision 29 existe pour signaler. `issue-8.md` est un
contrat gelé et hors empreinte : **il n'est pas modifié par cette issue**. Le fait faux est nommé
ici et remonté au lot pour arbitrage.

## 2. Le vrai défaut, en deux moitiés

`WP_DEBUG` est vrai ; ce sont les deux constantes qui gouvernent sa **destination** qui manquent.
Aucune des deux n'est définie nulle part (`grep` sur le `wp-config.php` généré ne renvoie que la
ligne 116).

Depuis le cœur, dans le conteneur :

- `wp-includes/default-constants.php:109-110` → `WP_DEBUG_LOG` par défaut **`false`**.
- `wp-includes/default-constants.php:104-105` → `WP_DEBUG_DISPLAY` par défaut **`true`**.
- `wp-includes/load.php`, `wp_debug_mode()` :
  `if ( WP_DEBUG ) { error_reporting( E_ALL ); if ( WP_DEBUG_DISPLAY ) { ini_set( 'display_errors', 1 ); } … }`
  et `$log_path = false` quand `WP_DEBUG_LOG` est faux.

**Moitié A — rien n'est journalisé.** `wp-content/debug.log` **n'existe pas** sur un volume neuf
(vérifié à l'`ls`, avant *et* après un provisionnement complet et une page rendue). Les diagnostics
partent sur `error_log = /dev/stderr` (posé par `/usr/local/etc/php/conf.d/error-logging.ini` de
l'image officielle), donc dans `docker compose logs wordpress`.
**Conséquence : toute chaîne ayant « vérifié que `debug.log` était propre » a lu un fichier absent.**

**Moitié B — les notices s'affichent au visiteur.** `wp_debug_mode()` fait `ini_set('display_errors', 1)`
et **écrase à l'exécution** le `display_errors = Off` de l'image. Une notice PHP se serait donc
imprimée **dans le HTML du visiteur**. C'est ce qui a fait renoncer la chaîne du lot 8 à activer le
débogage (`issue-8.md:1305` : « cela aurait imprimé des notices dans les pages pendant que les
chaînes sœurs testaient »).

**L'état livré au `e18c8ae` cumule donc les deux pires moitiés : affichage au visiteur, aucune trace.**

## 3. Troisième défaut, de la même famille : le journal ment par omission

`/usr/local/etc/php/conf.d/error-logging.ini` de l'image officielle porte :

```
ignore_repeated_errors = On
log_errors_max_len = 1024
```

Une notice qui se répète sur vingt pages **n'est journalisée qu'une fois**. Sur une stack dont la
raison d'être est la **valeur probante**, c'est la même faute que celle réparée ici : une
vérification qui ne prouve pas ce qu'elle croit prouver.

> **Correction de ce contrat, faite après mesure — la moitié « troncature » était fausse.**
> J'avais écrit ici que les messages étaient tronqués à 1 024 octets. **C'est faux sur cette stack**,
> et c'est la chaîne d'implémentation qui l'a relevé contre moi. `log_errors_max_len` a été
> **supprimée de PHP en 8.0** ; sur le PHP 8.1.34 de cette stack, `ini_get('log_errors_max_len')`
> renvoie **`bool(false)`** et `php -i` en compte **0 occurrence**. La ligne de l'image officielle
> n'a donc **aucun effet**, et la nôtre non plus : **aucun message n'est tronqué aujourd'hui**.
> La ligne est conservée dans `zz-mtb-debug.ini` uniquement pour rester juste si la stack
> redescendait sous PHP 8.0 — jamais comme un correctif d'un défaut observable.
>
> **Seule la déduplication était un vrai défaut, et elle est réparée** : `ignore_repeated_errors`
> passe de `On` (valeur de l'image) à `Off` — vérifié à `php -i` : `ignore_repeated_errors => Off => Off`.

## 4. Ce qui est décidé

| # | Décision | Où | Pourquoi |
|---|---|---|---|
| 1 | `WORDPRESS_CONFIG_EXTRA` sur le service `wordpress`, définissant `WP_DEBUG_LOG` = `WP_DEBUG` et `WP_DEBUG_DISPLAY` = `false` | `compose.yaml` | Voir §5 : évalué **à l'exécution**, donc sans dépendance à un volume neuf |
| 2 | `ignore_repeated_errors = Off` (le seul des trois qui corrige un défaut réel), plus `ignore_repeated_source = Off` et `log_errors_max_len = 0`, tous deux **sans effet sur PHP 8.1** — voir la correction du §3 | `docker/wordpress/zz-mtb-debug.ini`, copié par le `Dockerfile` en `conf.d` | Le préfixe `zz-` trie **après** `error-logging.ini` : la surcharge prend. Même motif que `zz-mtb-mail.ini` |
| 3 | **`WORDPRESS_DEBUG` n'est PAS ajouté au service `wpcli`** | `compose.yaml`, inchangé sur ce point | Voir §7, arbitrage 2 |
| 4 | `.env.example` documente la variable ; la valeur livrée reste `1` | `.env.example` | Voir §6 |
| 5 | Cibles `make debug-log` et `make debug-log-reset` | `Makefile` | Le journal vit dans le volume, pas dans le dépôt : sans cible, il est inatteignable sans connaître le chemin |
| 6 | La documentation de la stack dit où sont les diagnostics | `docs/docker.md` | Tâche 4 de l'issue ; il n'existe **aucun `README.md`** à la racine (vérifié à `e18c8ae`) |

### Forme exacte du réglage

```yaml
WORDPRESS_CONFIG_EXTRA: |
  define( 'WP_DEBUG_LOG', WP_DEBUG );
  define( 'WP_DEBUG_DISPLAY', false );
```

## 5. Pourquoi `WORDPRESS_CONFIG_EXTRA` et pas autre chose — le point qui décide

`wp-config.php:127` du fichier **généré** porte :

```php
if ($configExtra = getenv_docker('WORDPRESS_CONFIG_EXTRA', '')) {
	eval($configExtra);
}
```

Ce bloc est **évalué à chaque requête**, pas figé à la création du volume. Le réglage prend donc
effet **sur une stack déjà installée**, sans `make reset`, et il ne peut pas se désynchroniser.

C'est la transposition directe du refus de `register_activation_hook` du **contrat #1 §4** : rien ne
doit dépendre d'un évènement qui n'a lieu qu'une fois. Un correctif qui n'aurait pris qu'au premier
démarrage aurait reproduit, dans la stack, le défaut que l'extension refuse dans le code.

**Contrainte d'ordre, à ne jamais enfreindre** : l'`eval` de la ligne 127 s'exécute **après** le
`define` de la ligne 116. Le bloc peut donc définir `WP_DEBUG_LOG` et `WP_DEBUG_DISPLAY`, mais il ne
doit **jamais** redéfinir `WP_DEBUG` — cela émettrait à soi seul un avertissement « constant already
defined », c'est-à-dire fabriquer le bruit qu'on prétend capturer.

**Vérifié en reproduisant les lignes 116 et 127 à l'identique** dans le conteneur, pour les trois
valeurs possibles :

| `WORDPRESS_DEBUG` | `WP_DEBUG` | `WP_DEBUG_LOG` | `WP_DEBUG_DISPLAY` |
|---|---|---|---|
| `0` | `false` | `false` | `false` |
| `1` | `true` | `true` | `false` |
| absente | `false` | `false` | `false` |

## 6. D9 : le réglage par défaut, et pourquoi il reste à `1`

D9 exige une stack **fidèle à un hébergement mutualisé PHP standard** *et* des vérifications
**probantes**. Les deux tirent en sens contraire ; l'arbitrage est le suivant.

- **Un hébergement mutualisé n'affiche jamais une notice au visiteur.** C'est la moitié de D9 qui est
  **non négociable**, et c'est `WP_DEBUG_DISPLAY = false` qui la tient — quelle que soit la valeur de
  `WORDPRESS_DEBUG`, et y compris quand le débogage est actif.
- **La journalisation, elle, n'est pas infidèle à la production** : un mutualisé journalise ses
  erreurs, il ne les montre pas. `WP_DEBUG_LOG` suit donc `WP_DEBUG` sans réserve.
- `WORDPRESS_DEBUG=1` reste la valeur livrée dans `.env.example` : c'est une stack de développement et
  de recette, dont les seuls utilisateurs sont des agents, et dont l'issue dit qu'une vérification
  « aucune notice » n'y vaut rien sans journal.
- `WORDPRESS_DEBUG=0` reste le geste qui rapproche la stack de la production, et il fonctionne
  (ligne 1 du tableau du §5).

**Aucune combinaison ne peut afficher une notice au visiteur.** C'est la propriété que ce contrat gèle.

## 7. Arbitrages

**Arbitrage 1 — affichage : journal seul, pas de seconde variable.**
`WP_DEBUG_DISPLAY` est câblé à `false`, sans interrupteur dédié. Une variable d'adhésion aurait été
un réglage que personne n'active, dans une stack sans humain devant l'écran, et dont le seul effet
possible est de **polluer les mesures** : une notice imprimée dans le HTML fausse le poids de page
(D8), la validité du balisage, la passe axe (D7), et rend D12 intestable — puisque D12 se vérifie
précisément sur des pages mal remplies. Le journal donne la même information sans toucher à la page.

**Arbitrage 2 — `wpcli` ne reçoit pas `WORDPRESS_DEBUG`, délibérément.**
C'était l'option la plus tentante et elle est **écartée**. Trois raisons :

1. **Elle rendrait fausses des phrases écrites dans des documents gelés.** `docs/ETAT.md` décision 29
   et quatre contrats — `issue-15.md:695`, `issue-18.md:693`, `issue-22.md:826`, `issue-29.md:305` —
   s'appuient explicitement sur « `WP_DEBUG` est faux en WP-CLI ». Les basculer en silence, c'est le
   défaut exact qui a bloqué le lot 9 : *le dépôt affirme quelque chose que le code dément.*
2. **`docker/provision/provision.sh` est hors empreinte** et sonde des commandes WP-CLI en lisant leur
   sortie (`wp mtb import-fixtures --help`). Des diagnostics sur `stdout` pourraient en fausser
   l'analyse, et **je n'ai pas le droit de réparer ce fichier dans cette issue**.
3. **Ce n'est pas ce que l'issue demande.** Son grief est qu'une vérification « aucune notice » ne
   prouve rien ; or cette vérification n'a de sens que **sur une page rendue** (décision 29), donc sur
   le chemin web — celui que ce contrat répare.

**Contrepartie obligatoire** : le partage web/CLI cesse d'être un piège tacite. Il est écrit dans
`docs/docker.md` et rappelé dans le `Makefile`. Une issue ultérieure pourra l'étendre à `wpcli`, avec
`provision.sh` dans son empreinte — ce contrat ne s'y oppose pas, il refuse de le faire à l'aveugle.

**Arbitrage 3 — la destination des diagnostics change, et c'est une rupture à signaler.**
`wp_debug_mode()` fait `ini_set( 'error_log', $log_path )` dès que `WP_DEBUG_LOG` est vrai. Après ce
correctif, les diagnostics ne sortent donc **plus** sur `/dev/stderr` : ils vont dans
`wp-content/debug.log`. **`docs/contracts/issue-7.md:1127` devient périmé** — il dit d'aller regarder
`docker compose logs wordpress`. Contrat gelé, hors empreinte, **non modifié** ; le fait est remonté
au lot, et `docs/docker.md` dit la bonne adresse.

## 8. Ce sur quoi les chaînes à venir peuvent s'appuyer

- Sur le **chemin web**, `WP_DEBUG` suit `WORDPRESS_DEBUG` ; `WP_DEBUG_LOG` le suit aussi ;
  `WP_DEBUG_DISPLAY` est **toujours** faux.
- En **WP-CLI**, `WP_DEBUG` reste **faux** (décision 29, inchangée et désormais documentée).
- Les diagnostics d'une page rendue se lisent dans **`wp-content/debug.log`**, via `make debug-log`.
- Le journal ne **dédoublonne plus** et ne **tronque plus**.
- « Aucune notice » n'est une affirmation recevable **que** mesurée sur une page rendue, journal vidé
  au préalable (`make debug-log-reset`), et **jamais** depuis WP-CLI.

## 9. Interdits

- Ne **jamais** redéfinir `WP_DEBUG` dans `WORDPRESS_CONFIG_EXTRA` (§5).
- Ne **jamais** corriger `wp-config.php` à la main dans le conteneur : il vit dans le volume, il est
  régénéré, et c'est la douleur du lot 2 que cette issue supprime.
- Ne **jamais** activer `WP_DEBUG_DISPLAY` : cela casserait D7, D8 et D12 en même temps.
- L'issue ne touche **ni le thème, ni `mtb-core`, ni `docker/provision/`, ni `docker/wpcli/`**.

## 10. Empreinte fichiers — stricte

`compose.yaml` · `docker/wordpress/**` · `.env.example` · `Makefile` · `docs/docker.md`

Rien d'autre. `docs/contracts/issue-31.md` est le fichier du lead.

## 11. Démonstration exigée — le défaut d'abord, sur stack reconstruite depuis zéro

Une déclaration ne vaut rien ici ; l'issue existe parce qu'une vérification a été crue sans être
faite. La preuve se fait en **deux moitiés**, `down -v` puis `up`, en attendant **`[provision] terminé.`**
dans les journaux — **jamais** le healthcheck de `wpcli`, qui passe *healthy* vers 9 s alors que le
provisionnement dure 51 à 78 s selon `ETAT.md`, et **8 min 42 s** sur le poste de mesure.

### Résultat mesuré — A/B contrôlé, une seule variable

La stack a d'abord été reconstruite depuis zéro avec le correctif, puis le bloc
`WORDPRESS_CONFIG_EXTRA` a été **retiré temporairement** de `compose.yaml` et le seul conteneur
`wordpress` recréé, pour que **rien d'autre ne change** entre les deux moitiés : même volume, même
image, même sonde, même page. `compose.yaml` a été restauré depuis une copie de sauvegarde et son
empreinte MD5 revérifiée **identique bit pour bit** (`4d07fa3aa632b3a6f98af9c746e69e33`).

Sonde : un mu-plugin éphémère, écrit **dans le conteneur uniquement**, qui lit une clé de tableau
absente au rendu (`Undefined array key "mtb_avant_31"`).

| | HTML servi au visiteur | Poids de la page | `wp-content/debug.log` |
|---|---|---|---|
| **AVANT** (`WORDPRESS_CONFIG_EXTRA` absent) | **1 occurrence** du `Warning:` | **36 988 o** | **absent** |
| **APRÈS** (correctif posé) | **0 occurrence** | **36 873 o** | **présent**, 2 lignes |

La ligne fuitée dans la page, verbatim :
`Warning: Undefined array key "mtb_avant_31" in /var/www/html/wp-content/mu-plugins/zz-lead-avant-31.ph…`

**Les 115 octets d'écart sont la mesure du défaut** : c'est du diagnostic PHP imprimé dans la page
d'un visiteur, qui faussait d'autant le poids mesuré au titre de D8.

**Deux propriétés vérifiées au passage :**

- **La déduplication est bien levée** : le *même* avertissement, à la *même* ligne du *même*
  fichier, est journalisé **deux fois** (deux requêtes). Avec le `ignore_repeated_errors = On` de
  l'image, la seconde aurait été avalée.
- **`make debug-log-reset` ne casse pas la journalisation.** `docker compose exec` entre dans le
  conteneur en **root** (vérifié : `id -un` → `root`) alors que PHP écrit sous `www-data`. Sans le
  `chown` que porte la cible, un premier vidage sur volume neuf créerait le fichier en `root:root`,
  PHP ne pourrait plus y écrire, et `make debug-log` afficherait un journal vide — **soit exactement
  la vérification faussement propre que cette issue supprime**. Mesuré après vidage : fichier à
  `www-data www-data`, et l'écriture PHP suivante aboutit.

La sonde a été retirée ; l'arbre de travail ne porte **aucune trace** hors des fichiers de l'empreinte.
