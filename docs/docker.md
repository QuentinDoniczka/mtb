# Stack Docker — développement et recette

Stack de développement uniquement (BRIEF §12, décision 4 de `docs/ETAT.md`) : rien de production ne
dépend de Docker. La cible reste un hébergement mutualisé PHP standard.

## Services

| Service | Rôle | Image |
|---------|------|-------|
| `db` | Base de données | `mariadb:10.11` |
| `wordpress` | PHP + WordPress + serveur web | construit depuis `docker/wordpress/` (`wordpress:php8.1-apache` + msmtp) |
| `wpcli` | Installation, activation, provisionnement, fixtures | `wordpress:cli-php8.1` |
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
s'éditent depuis le lot 2, mais la commande WP-CLI `wp mtb import-fixtures` qui les créerait à partir
de ces fichiers JSON **n'est encore livrée par personne** (dette technique **#29** du board — décrite
au caractère près, forme et signature comprises, dans `docs/contracts/issue-1.md` §
« `includes/migration/import-fixtures/` »). Le provisionnement sonde la commande (`wp mtb
import-fixtures --help`) et l'appelle si elle existe ; sinon il journalise l'absence et passe à la
suite, sans erreur — comportement inchangé, seul le message a été corrigé pour ne plus laisser croire
que `mtb-core` lui-même manque encore.

**Ce qui reste à faire, côté extension** : `mtb-core` doit exposer une commande WP-CLI
`wp mtb import-fixtures --portees=… --chiens=… --resultats=…` qui lit ces fichiers JSON et crée les
publications avec ses propres clés de champs. Ce script Docker ne présuppose et ne fige aucune clé de
métadonnée — c'est volontaire, pour ne rien avoir à réécrire ici quand le modèle de contenu sera figé
dans `docs/contracts/`. Une fois la commande livrée, `make provision` suffit à semer les fixtures.
Note pour cette future commande : `resultats.json` porte encore des libellés (`"RING"`, `"IGP"`) et un
slug de chien là où le modèle attend des clés fermées (`ring`, `igp_rci`…) — signalé et volontairement
laissé tel quel par le contrat #5 (dette **T-#5-c**), à convertir par l'issue qui livrera l'import.

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
redémarrage), sans l'attacher à aucune fiche : tant que `wp mtb import-fixtures` n'existe pas, aucun
provisionnement automatique ne peut créer de portée ni de chien pour la porter. Elle est donc
disponible pour être assignée **à la main** (bloc « Fiche d'information », photo d'une fiche chien) le
temps de vérifier le rendu du cadrage.

## Courrier

Le conteneur `wordpress` embarque `msmtp`, configuré pour relayer tout courrier PHP
(`mail()` / `wp_mail()`) vers `mail` (Mailpit), jamais vers un service externe. C'est un détail de la
stack de développement uniquement : la configuration de départ de courrier en production dépend de
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
