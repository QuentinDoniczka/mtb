# Stack Docker — développement et recette

Stack de développement uniquement (BRIEF §12, décision 4 de `docs/ETAT.md`) : rien de production ne
dépend de Docker. La cible reste un hébergement mutualisé PHP standard.

## Services

| Service | Rôle | Image |
|---------|------|-------|
| `db` | Base de données | `mariadb:10.11` |
| `wordpress` | PHP + WordPress + serveur web | construit depuis `docker/wordpress/` (`wordpress:php8.1-apache` + msmtp) |
| `wpcli` | Installation, activation, provisionnement, fixtures | construit depuis `docker/wpcli/` (`wordpress:cli-php8.1` + msmtp) |
| `mail` | Capteur de courrier local (formulaire de contact) | `axllent/mailpit` |

**Pourquoi PHP 8.1** : c'est une version encore couramment proposée (par défaut ou au choix) par
l'hébergement mutualisé français au moment du bootstrap (o2switch, OVH, IONOS…). Elle correspond à
la version minimale réaliste visée en production — ne pas s'appuyer sur une fonctionnalité PHP plus
récente sans revérifier l'hébergement retenu (question ouverte Q5).

Le thème (`wp-content/themes/mtb/`) et l'extension (`wp-content/plugins/mtb-core/`) sont **montés
depuis le dépôt**, jamais copiés dans l'image : toute modification est visible immédiatement, sans
reconstruction.

## Démarrer

```sh
cp .env.example .env      # une seule fois — ne jamais commiter .env
make up                    # build + démarrage + provisionnement automatique (idempotent)
```

`make up` attend que la base de données soit saine avant de lancer WordPress, qui est lui-même
attendu avant que `wpcli` ne provisionne le site. Le provisionnement (`docker/provision/provision.sh`)
peut être relancé sans risque à tout moment — il ne duplique jamais un compte, une page ou un
contenu déjà présent.

- Site : http://localhost:3005
- Admin WordPress : http://localhost:3005/wp-admin/
- Capteur de courrier (Mailpit) : http://localhost:8025

Le port WordPress est piloté par `WP_PORT`/`WP_SITE_URL` dans `.env` (voir `.env.example`) — 3005
par défaut, choisi pour ne pas entrer en conflit avec un port 8080 déjà pris localement. En cas de
changement de port sur une stack déjà installée, relancer `make provision` : il nettoie le lien
d'administration resté codé en dur dans le contenu par défaut de WordPress (voir « Fixtures »
ci-dessous).

Identifiants de développement définis dans `.env.example` (à copier dans `.env`) :

| Compte | Rôle | Identifiant | Mot de passe |
|--------|------|-------------|---------------|
| Administrateur technique | Administrateur | `admin` | `mtb-dev-admin` |
| Éditrice (Fabienne) | Éditeur (rôle natif WordPress — jamais Administrateur) | `fabienne` | `mtb-dev-editrice` |

## Commandes utiles

| Commande | Effet |
|----------|-------|
| `make up` | Démarre (ou construit puis démarre) toute la stack et provisionne |
| `make provision` (alias `make seed`) | Relance le provisionnement sur une stack déjà démarrée — utile après activation du thème/de l'extension |
| `make ps` | État des services |
| `make logs` | Suit les journaux de tous les services |
| `make wp cmd="user list"` | Exécute une commande WP-CLI ponctuelle |
| `make shell` | Ouvre un shell dans le conteneur `wordpress` |
| `make down` | Arrête la stack, conserve les volumes (données, cœur WordPress) |
| `make reset` | Arrête la stack **et supprime les volumes** — repart de zéro |
| `make export-uploads` | Exporte les médias téléversés vers `./export-uploads/`, portables vers la production |
| `make debug-log` | Affiche le journal des diagnostics PHP (`wp-content/debug.log`, dans le volume) |
| `make debug-log-reset` | Vide ce journal, avant une mesure « aucune notice » |
| `make db-sql cmd="…"` | Accès direct à la base via le client du service `db` (outil, pas un repli — voir « Accès à la base et TLS ») |
| `make db-check` | Recette d'acceptation de #30 : rejoue `wp db query`, `wp db check` et `wp db export`, dit lequel échoue |
| `make css` | Régénère les 15 feuilles minifiées du thème — voir « Feuilles de style minifiées » |
| `make css-check` | Vérifie les 15 paires source/artefact sans rien écrire ; sort 1 dès qu'une paire n'est pas à jour |

## Accès à la base et TLS (#30)

Sur cette stack, `wp db query`, `wp db check` et `wp db export`, invoqués **dans le conteneur `wpcli`**,
échouaient systématiquement avec :

```
Error: Failed to get current SQL modes. Reason: ERROR 2026 (HY000): TLS/SSL error: SSL is
required, but the server does not support it
```

**Ce n'est pas le serveur qui impose TLS** : `db` (`mariadb:10.11`, `have_ssl = DISABLED`) reste fidèle
à un hébergement mutualisé standard — texte clair sur socket/hôte, jamais de certificat à
entretenir. **C'est le client MariaDB Connector/C 11.4** embarqué dans l'image `wordpress:cli-php8.1`
qui exige TLS par défaut, et c'est un **défaut compilé du paquet** (`ssl=TRUE`,
`ssl-verify-server-cert=TRUE`), pas un fichier de configuration : aucun fichier d'options ne porte
`ssl`, et `mysql --print-defaults` ne rend aucun argument.

**Un fichier `[client] ssl=0` posé dans `/etc/my.cnf.d/` est inopérant pour WP-CLI, même s'il répare
le client nu.** WP-CLI construit sa ligne de commande avec `--no-defaults` en tête, qui ordonne au
client d'ignorer tout fichier d'options — un tel fichier ne réparerait donc que des invocations
manuelles du client, jamais `wp db query`/`db check`/`db export`. Mesuré et détaillé dans
`docs/contracts/issue-30.md`.

**Le correctif** : six enrobages `sh` (`docker/wpcli/bin/mysql`, `mariadb`, `mariadb-dump`,
`mariadb-check`, `mysqldump`, `mysqlcheck`), posés par `docker/wpcli/Dockerfile` dans
`/usr/local/bin/`, qui précède `/usr/bin/` (où vivent les binaires réels, jamais déplacés ni
renommés) dans le `PATH` du conteneur. Chacun ajoute `--skip-ssl` **en fin de ligne** (jamais en
tête : `--no-defaults` doit rester la première option du client, sans quoi celui-ci répond
`unknown option '--no-defaults'`) avant d'`exec`er le binaire réel.

**Garde de retrait** : si un argument reçu commence déjà par `--ssl`, `--tls` ou `--skip-ssl`,
l'enrobage se retire entièrement et transmet les arguments reçus sans y toucher. Sans elle, un
`mariadb --ssl` tapé à la main recevrait le contraire de ce qu'il demande, en silence (la dernière
option gagne). Un `mariadb --ssl …` explicite continue donc, volontairement, de rendre l'erreur TLS —
c'est la preuve que l'enrobage s'efface devant une intention explicite plutôt que de fabriquer une
magie locale.

Les scripts n'ont **aucune extension** (leur nom est le nom du binaire qu'ils remplacent dans le
`PATH`) : le dépôt tourne en `core.autocrlf=true`, sans `.gitattributes` couvrant ce dossier, et un
motif générique comme `*.sh text eol=lf` ne les aurait de toute façon pas couverts. Plutôt qu'un
`.gitattributes` ciblé au dossier (une option valable, mais un second mécanisme de plus à maintenir),
`docker/wpcli/Dockerfile` normalise les fins de ligne au moment du `COPY` (`sed 's/\r$//'`), à
l'identique du geste déjà fait pour `docker/provision/provision.sh` dans `compose.yaml` — sans
dépendre du réglage Git de la machine qui construit l'image, et sans toucher un octet versionné.

**Ce que ce correctif ne fait pas** : `db` reste sans TLS, et le rester est une exigence, pas un
oubli (D9 — un mutualisé PHP standard parle à sa base en clair). N'activez jamais TLS sur `db` pour
faire taire ce message.

`make db-sql cmd="…"` (ex. : `make db-sql cmd="SHOW TABLES"`) exécute le client du service `db`
directement — client et serveur du même build, TLS jamais en jeu. Ce n'est **pas un repli de
`wp db query`** : c'est le seul chemin vers la base qui ne charge ni WordPress ni `mtb-core`, donc le
seul qui reste utilisable le jour où c'est justement `mtb-core` qui est cassé. Le service `db`
n'exposant aucun port à l'hôte, c'est aujourd'hui la seule porte d'entrée directe. `make db-check`
rejoue les trois commandes de WP-CLI et dit explicitement laquelle échoue — c'est la recette
d'acceptation de #30.

Le provisionnement rejoue lui aussi `wp db query 'SELECT 1'` en fin de course, en **avertissement
seulement** : le jour où l'image de base bougerait sous les enrobages, `docker compose logs wpcli` le
dirait de lui-même. Cette sonde ne fait jamais échouer le démarrage — c'est une commande de confort,
pas le chemin de connexion du site (celui-là est testé plus haut dans le même script, en `mysqli`).

## Diagnostics PHP

`WORDPRESS_DEBUG` (dans `.env`, livré à `1`) pilote **`WP_DEBUG`** et, depuis #31, **`WP_DEBUG_LOG`**,
qui le suit. **Sur le chemin web, `WP_DEBUG_DISPLAY` est câblé à `false`** : aucune combinaison de
réglages ne peut imprimer une notice dans la page servie à un visiteur. C'est la moitié non
négociable de la fidélité à un hébergement mutualisé — un mutualisé journalise ses erreurs, il ne les
montre pas. Ces deux constantes sont posées par `WORDPRESS_CONFIG_EXTRA` dans `compose.yaml`, un bloc
que `wp-config.php` évalue à chaque requête : le réglage prend sur une stack déjà installée, sans
`make reset`.

**La restriction au chemin web est littérale, pas une précaution de langage.** Le service `wpcli` ne
reçoit délibérément pas `WORDPRESS_CONFIG_EXTRA` (voir plus bas), donc `WP_DEBUG_DISPLAY` y vaut
`true` — la valeur par défaut du cœur. Cela reste sans conséquence : `display_errors` y est `Off`,
`WP_DEBUG` y est faux donc WordPress ne l'allume jamais, et WP-CLI ne sert aucun visiteur. Ce qui est
garanti partout, c'est qu'**aucune page servie à un visiteur ne porte de diagnostic**.

La table ci-dessous décrit la variable **telle que le conteneur `wordpress` la reçoit** — ce n'est
pas la même chose que ce qui est écrit dans `.env` :

| `WORDPRESS_DEBUG` reçue par le conteneur | `WP_DEBUG` | `WP_DEBUG_LOG` | `WP_DEBUG_DISPLAY` |
|---|---|---|---|
| `1` (livré) | `true` | `true` | `false` |
| `0` | `false` | `false` | `false` |
| absente **du conteneur** | `false` | `false` | `false` |

**Le piège du geste dans `.env`, qui ne donne pas la troisième ligne** : `compose.yaml:43` porte
`WORDPRESS_DEBUG: ${WORDPRESS_DEBUG:-1}`. **Supprimer** la ligne `WORDPRESS_DEBUG=` de `.env` retombe
donc sur la valeur par défaut `1` et donne `WP_DEBUG=true` — l'inverse de ce qu'on croit faire en
l'effaçant. Pour éteindre les diagnostics, écrire `WORDPRESS_DEBUG=0` ; ne pas effacer la ligne. La
troisième ligne de la table ne s'obtient qu'en retirant la variable du **service** dans
`compose.yaml`. Dans les trois cas, aucun diagnostic n'atteint le visiteur.

Les diagnostics d'une page rendue s'écrivent dans **`wp-content/debug.log`**, à l'intérieur du volume
`mtb_wp_data` — jamais dans le dépôt. On les lit avec `make debug-log` et on vide le journal avec
`make debug-log-reset`. Le fichier n'existe qu'à partir de la première écriture, et `make debug-log-reset`
le laisse présent mais vide : dans les deux cas `make debug-log` répond « journal vide », ce qui signifie
« rien à signaler » et non « journal introuvable ».

Le journal ne **dédoublonne plus** : `docker/wordpress/zz-mtb-debug.ini` annule le
`ignore_repeated_errors = On` de l'image officielle, qui faisait qu'une notice répétée sur vingt pages
n'apparaissait qu'une fois. Le même fichier pose `log_errors_max_len = 0` contre la troncature à
1 024 octets demandée par l'image — **sans effet mesurable ici** : cette directive PHP a été supprimée
en 8.0 et n'existe plus sur le PHP 8.1 de la stack, donc aucun message n'était tronqué de toute façon.

**Deux conséquences à connaître avant de conclure quoi que ce soit d'une mesure :**

- **Les diagnostics ne sortent plus dans `docker compose logs wordpress`.** Dès que `WP_DEBUG_LOG` est
  vrai, `wp_debug_mode()` repointe `error_log` sur le fichier de journal ; ils quittent donc
  `/dev/stderr`. Toute consigne antérieure renvoyant vers les journaux du conteneur est périmée.
- **`WP_DEBUG` reste `false` en WP-CLI** (décision 29 de `docs/ETAT.md`) : le service `wpcli` ne reçoit
  délibérément pas `WORDPRESS_DEBUG`, car `docker/provision/provision.sh` analyse la sortie de
  commandes WP-CLI et plusieurs contrats gelés s'appuient sur ce partage. **« Aucune notice » n'est
  donc une affirmation recevable que mesurée sur une page rendue**, journal vidé au préalable — jamais
  depuis un `make wp`.

## Fixtures

`docker/fixtures/portees.json`, `chiens.json`, `resultats.json` contiennent des données de
démonstration au vocabulaire du BRIEF §5 (identifiant, père/mère en fiche ou en texte libre,
disponibilité, tests de santé, discipline…), y compris :

- une portée avec chiots disponibles, une « tous réservés », une ancienne (`L 1995`) ;
- une portée dont le père est saisi en texte libre (hors élevage) et la mère en fiche ;
- une portée **délibérément incomplète** (pas de photo, pas de liste de chiots) — fixture de
  vérification de **D12** (un contenu mal rempli ne casse jamais la page) ;
- un chien avec tests de santé complets, un avec tests manquants, un décédé ;
- une page protégée par mot de passe (mécanisme natif WordPress, mot de passe de démo `chiot2026`).

Les clés de `disponibilite` dans `portees.json` sont celles de la liste close gelée par le contrat
#3 §11 et `MASTER.md` §3.3 : **`disponible` | `reserve` | `passee`**. N'importe quelle autre valeur
est traitée comme une absence par `Hydratation::champ_liste()` — aucun badge ne s'affiche, en
silence — donc ne jamais réintroduire les anciennes clés (`chiots_disponibles`, `tous_reserves`,
`portee_passee`) supprimées de ce fichier.

**État aujourd'hui** : les types de contenu *portée*, *chien* et *résultat de travail* existent et
s'éditent depuis le lot 2, et la commande WP-CLI `wp mtb import-fixtures` **est livrée** —
`wp-content/plugins/mtb-core/includes/migration/import-fixtures/`. La **dette #29 est donc payée** ;
la forme et la signature de la commande restent décrites dans `docs/contracts/issue-1.md` §
« `includes/migration/import-fixtures/` ».

**`make provision` suffit à semer les fixtures.** Le provisionnement sonde la commande
(`wp mtb import-fixtures --help`) et l'appelle si elle répond — ce qui est désormais le cas. La
branche de repli qui journalisait l'absence et passait à la suite sans erreur **existe toujours dans
`docker/provision/provision.sh`** ; elle n'est plus atteinte en fonctionnement normal, et son
message ne renvoie plus à la dette #29 mais à la régression qui la rendrait atteignable (voir
« Corrigé par #30 » ci-dessous).

La **dette T-#5-c est payée elle aussi** : `docker/fixtures/resultats.json` porte maintenant les
**clés fermées** attendues par le modèle — `"discipline": "ring"`, `"discipline": "igp_rci"` — et non
plus les libellés `"RING"` / `"IGP"`. Le fichier documente lui-même le piège dans son champ
`commentaire` : `igp_rci` **ne se déduit pas** du libellé affiché (« IGP / RCI »), et c'est l'entrée
qui aurait été perdue en silence avec l'ancienne valeur.

**Corrigé par #30** : le message de repli de `docker/provision/provision.sh` annonçait encore
« `mtb-core` n'a pas encore livré cette commande (dette #29) », devenu inexact depuis que la dette
est payée. Il ne s'atteint plus qu'en cas de régression (extension inactive, commande qui cesse de
répondre) ; le message le dit maintenant explicitement, au lieu d'orienter vers une dette soldée.

La page « Contact » et la page protégée par mot de passe, elles, sont créées dès aujourd'hui : ce
sont des pages WordPress natives, indépendantes du contenu structuré à venir.

### Photo de test portrait

`docker/fixtures/photos/portee-demo-portrait-test.png` est une image **entièrement synthétique**
(bandes de couleur et texte, aucun rapport avec un vrai chien, nommée sans ambiguïté comme un actif
de test) au format portrait 1200 × 1600 (3:4). Elle comble un trou du jeu de fixtures : les photos de
démonstration existantes sont toutes au format paysage 3:2, un ratio qui ne déborde d'aucun cadre du
thème (`fiche-information` : 576 × 384, également 3:2) ou ne déborde qu'horizontalement (`grille-chiens` :
272 × 272), si bien que le réglage « Cadrage de la photo » (haut / centre / bas, `MASTER.md` §6.2) ne
pouvait jamais être vérifié sur sa composante verticale. L'image porte deux repères visuels : le point
d'intérêt par défaut du thème (`--point-interet: 50% 38%`) et le centre géométrique (50% 50%), pour
vérifier à l'œil qu'ils ne coïncident pas (dette **T16-bis**).

Le provisionnement l'importe dans la médiathèque (`wp media import`, idempotent — pas de doublon au
redémarrage). **Elle n'est plus à assigner à la main** : depuis que `wp mtb import-fixtures` est
livrée, les fixtures la rattachent d'elles-mêmes par son slug `portee-demo-portrait-test`, que
l'import résout en identifiant de média —

- **`chiens.json`** : `"photo": "portee-demo-portrait-test"` sur la fiche `cadrage: "haut"`, la seule
  du jeu à porter une photo principale ;
- **`portees.json`** : `"galerie": ["portee-demo-portrait-test"]` sur la portée `demo-rex` × `demo-luna`.

Le réglage « Cadrage de la photo » (`MASTER.md` §6.2) se vérifie donc directement sur la fiche chien
de démonstration, sans manipulation préalable.

## Courrier

**Les deux conteneurs PHP** — `wordpress` **et** `wpcli` — embarquent `msmtp`, configuré pour relayer
tout courrier PHP (`mail()` / `wp_mail()`) vers `mail` (Mailpit), jamais vers un service externe. Ils
partagent les mêmes fichiers `docker/mail/msmtprc` et `docker/mail/mail.ini`, d'où le contexte de
build à la racine du dépôt.

**Pourquoi `wpcli` aussi** : c'est la seconde voie de courrier de la stack. Un `wp_mail()` appelé
depuis WP-CLI — provisionnement, `wp mtb import-fixtures`, toute commande future — échouait sans lui
sur `sendmail: can't connect to remote host (127.0.0.1)`, l'image officielle WP-CLI n'ayant pas de
sendmail local. C'est le motif du service `wpcli` construit plutôt que tiré tel quel.

C'est un détail de la stack de développement uniquement : la configuration de départ de courrier en production dépend de
l'hébergeur retenu (question ouverte Q5) et n'est pas décidée ici.

Le provisionnement dépose aussi un mu-plugin (`wp-content/mu-plugins/zz-mtb-docker-mail.php`, hors
dépôt, réécrit à chaque `make provision`) qui force une adresse d'expéditeur valide
(`no-reply@mtbrabant.local`). Sans lui, WordPress calcule par défaut `wordpress@localhost` à partir
de l'URL du site, une adresse que PHPMailer rejette (pas de domaine à point) — `wp_mail()` échoue
alors silencieusement. C'est un correctif d'environnement de développement, pas une décision produit ;
l'expéditeur réel du formulaire de contact reste à définir avec l'issue `contact`.

## Médias

Les fichiers téléversés vivent dans le volume nommé `mtb_wp_data` (sous
`wp-content/uploads/` à l'intérieur), jamais dans l'image ni dans le dépôt. `make export-uploads` les
copie vers `./export-uploads/` pour transfert vers l'hébergement de production.

## Feuilles de style minifiées (#40)

Les feuilles du thème sont livrées en double : la source `assets/css/<nom>.css`, seule éditée, et
son artefact `assets/css/<nom>.min.css`, **écrit par un outil et versionné**. La production est un
hébergement mutualisé sans étape de construction : l'artefact doit donc être dans le dépôt, à côté
de sa source, **dans le même dossier obligatoirement** — `base.css` porte des `url("../fonts/…")`
relatifs à l'emplacement de la feuille.

La transformation retire **les commentaires, et rien d'autre** : chaque commentaire devient
exactement une espace, les espaces de fin de ligne sont rognés, les lignes ainsi vidées
disparaissent — les lignes vides préexistantes, elles, sont conservées. Aucun autre octet ne
bouge : aucune indentation rognée, aucune suite d'espaces écrasée, aucun point-virgule retiré,
aucune couleur réécrite.

| Commande | Effet |
|----------|-------|
| `make css` | Régénère les 15 artefacts. Une ligne par feuille, plus un total. Sortie 0, sauf refus. |
| `make css-check` | N'écrit rien. Rend `à jour`, `PÉRIMÉ`, `ABSENT`, `ORPHELIN` ou `INVALIDE` par paire, et **sort 1 dès qu'une seule paire n'est pas à jour**. |

### Obligation de processus

> **Toute modification d'une feuille sous `wp-content/themes/mtb/assets/css/` s'accompagne d'un
> `make css` dans le même commit.**

Sans lui, l'artefact ne décrit plus sa source, le thème sert la source, et la page reste **correcte
mais plus lourde**. Rien ne casse, rien ne s'affiche à l'éleveuse, rien n'est écrit dans
`debug.log` : c'est `make css-check` qui le dit, et lui seul.

### Comment le thème choisit ce qu'il sert

Chaque artefact porte en tête un marqueur à **quatre champs** : `/*!mtb-src:<L>:<E>:<A>:<F>*/`.
`<L>` et `<E>` sont la longueur et l'empreinte de la **forme canonique de la source** — ses octets
avec les fins de ligne ramenées au saut de ligne seul ; `<A>` et `<F>` sont celles du **corps de
l'artefact lui-même**, c'est-à-dire tout ce qui suit le marqueur et le saut de ligne qui le termine.

À chaque requête, `mtb_feuille_a_servir()` (`wp-content/themes/mtb/functions.php`) lit les 128
premiers octets de l'artefact et ne le sert que si **les six conditions** sont vraies :

1. la source est lisible ;
2. l'artefact est lisible ;
3. les octets lus portent le marqueur, **ancré à l'octet 0** ;
4. la forme canonique de la source a exactement la longueur `<L>` ;
5. elle a exactement l'empreinte `<E>` ;
6. le corps de l'artefact, sous forme canonique, a exactement la longueur `<A>` **et** l'empreinte
   `<F>`.

Dans **tous** les autres cas — artefact absent, illisible, vide, tronqué, sans marqueur, marqueur
mal formé, longueur ou empreinte discordante d'un côté ou de l'autre, source illisible — la source
est servie, sans erreur, sans avertissement, sans notice, sans écriture dans `debug.log`.

### Pourquoi le marqueur atteste aussi l'artefact

Les deux premiers champs attestent la source, et **ils n'attestaient jamais l'artefact**. La passe
d'intégration a mesuré ce que ce trou laissait passer : un artefact **tronqué en queue, marqueur
intact, était servi tel quel** — `base.min.css` amputé à 5 307 o au lieu de 10 579, **107
déclarations perdues sur 255**, page rendue en 200, aucune notice, aucun signe. Ce n'était donc pas
la régression de poids que #40 accepte, mais une **régression visuelle silencieuse**.

Ce n'est pas une précaution de conception : c'est un défaut mesuré, puis corrigé. La cible du projet
est un hébergement mutualisé où l'on dépose par FTP, et où un transfert s'interrompt. Les champs
`<A>` et `<F>` ferment le trou — un artefact incomplet, ou modifié après son écriture, cesse d'être
servi, et la source reprend sa place.

### Pourquoi ni `filemtime()` ni `filesize()`

Git n'enregistre aucune date de modification et écrit dans l'ordre lexicographique de son index, où
`base.css` précède `base.min.css` : à un `clone`, un test sur les dates tranche **toujours** pour
l'artefact, même périmé.

Les longueurs `<L>` et `<A>` sont celles de la **forme canonique**, jamais un `filesize()` brut, et
c'est ce qui les rend utilisables en production. Le dépôt tourne en `core.autocrlf=true` : une même
feuille pèse 45 914 octets dans Git et 46 927 sur un disque Windows, et l'artefact écrit en LF par
l'outil est rendu en CRLF par un `git checkout`. Mesuré : `base.min.css` converti en CRLF passe de
10 602 à 11 102 octets sur le disque et **reste servi** — un `filesize()` brut, lui, l'aurait rejeté
partout en production, sans que rien ne le signale. Une longueur seule manquerait par ailleurs une
corruption à longueur constante ; mesuré aussi, ce cas dégrade bien vers la source, parce que
l'empreinte l'attrape.

> Les deux tailles de `base.min.css` citées ci-dessus ne se contredisent pas : la mesure de
> troncature date d'avant l'ajout des deux champs, et le marqueur a grossi de 23 octets en passant
> de deux à quatre champs.

`ver` vaut l'empreinte de la source, artefact servi comme source servie : une mise en ligne qui ne
touche pas une feuille laisse le cache du visiteur intact.

### Deux réserves à connaître

- **Appartenance des fichiers sur hôte Linux.** `docker compose run` entre dans le conteneur en
  `root` ; les artefacts régénérés appartiendront donc à `root`. Même famille que le `chown` de
  `make debug-log-reset` (voir le `Makefile`). Sans objet sur Docker Desktop.
- **`docker/provision/provision.sh` ne peut pas appeler le vérificateur** : le conteneur `wpcli`
  ne monte pas `docker/outils/`, et le lui ajouter demanderait `compose.yaml`. Limitation écrite,
  non contournée — `make css-check` reste une commande à jouer à la main, ou depuis la liste de
  `test-integration-mtb`.

`assets/css/editor.css` n'a **pas** d'artefact : elle n'atteint jamais un visiteur, et
`assets/css/editor.min.css` ne doit pas exister — son absence est vérifiée par `make css-check`.

## État du thème et de l'extension

`wp-content/themes/mtb/` et `wp-content/plugins/mtb-core/` ne sont plus un squelette d'amorçage
depuis le lot 1 (`docs/ETAT.md`) : le thème de blocs et l'extension sont le code réel des chaînes
`leaddev-front-mtb`/`dev-front-mtb` et `leaddev-back-mtb`/`dev-back-mtb`, montés en direct depuis le
dépôt (voir « Services » ci-dessus). Voir `docs/ETAT.md` pour l'état courant module par module — ce
fichier ne le duplique pas.
