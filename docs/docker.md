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

## Diagnostics PHP

`WORDPRESS_DEBUG` (dans `.env`, livré à `1`) pilote **`WP_DEBUG`** et, depuis #31, **`WP_DEBUG_LOG`**,
qui le suit. **`WP_DEBUG_DISPLAY` est câblé à `false` en toutes circonstances** : aucune combinaison
de réglages ne peut imprimer une notice dans la page servie à un visiteur. C'est la moitié non
négociable de la fidélité à un hébergement mutualisé — un mutualisé journalise ses erreurs, il ne les
montre pas. Ces deux constantes sont posées par `WORDPRESS_CONFIG_EXTRA` dans `compose.yaml`, un bloc
que `wp-config.php` évalue à chaque requête : le réglage prend sur une stack déjà installée, sans
`make reset`.

| `WORDPRESS_DEBUG` | `WP_DEBUG` | `WP_DEBUG_LOG` | `WP_DEBUG_DISPLAY` |
|---|---|---|---|
| `1` (livré) | `true` | `true` | `false` |
| `0` | `false` | `false` | `false` |
| absente | `false` | `false` | `false` |

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
`docker/provision/provision.sh`** ; elle n'est plus atteinte en fonctionnement normal, mais son
message parle encore de la dette #29 (voir « Ce qui reste à corriger » ci-dessous).

La **dette T-#5-c est payée elle aussi** : `docker/fixtures/resultats.json` porte maintenant les
**clés fermées** attendues par le modèle — `"discipline": "ring"`, `"discipline": "igp_rci"` — et non
plus les libellés `"RING"` / `"IGP"`. Le fichier documente lui-même le piège dans son champ
`commentaire` : `igp_rci` **ne se déduit pas** du libellé affiché (« IGP / RCI »), et c'est l'entrée
qui aurait été perdue en silence avec l'ancienne valeur.

**Ce qui reste à corriger** (documentation et journal, sans effet sur le fonctionnement) : le message
de repli de `docker/provision/provision.sh` annonce encore « `mtb-core` n'a pas encore livré cette
commande (dette #29) ». Il est devenu inexact ; il n'est émis que si la commande cesse de répondre,
auquel cas il enverrait sur une fausse piste. À reprendre par l'issue qui touchera la stack —
ce document ne modifie pas `docker/`.

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

## État du thème et de l'extension

`wp-content/themes/mtb/` et `wp-content/plugins/mtb-core/` ne sont plus un squelette d'amorçage
depuis le lot 1 (`docs/ETAT.md`) : le thème de blocs et l'extension sont le code réel des chaînes
`leaddev-front-mtb`/`dev-front-mtb` et `leaddev-back-mtb`/`dev-back-mtb`, montés en direct depuis le
dépôt (voir « Services » ci-dessus). Voir `docs/ETAT.md` pour l'état courant module par module — ce
fichier ne le duplique pas.
