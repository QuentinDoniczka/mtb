# Contrat d'outillage — Issue #30 — `wp db query` échoue sur la stack (TLS)

> **Ce contrat n'est pas un contrat d'interface thème ↔ extension.** L'issue #30 ne touche ni le
> thème ni `mtb-core` : son empreinte est entièrement dans la stack Docker (`compose.yaml`,
> `docker/**`, `Makefile`). Il n'y a donc ni fonction de lecture, ni bloc, ni chaîne de service à
> réconcilier. Comme `issue-31.md`, ce document tient le rôle que tient ailleurs le contrat gelé :
> il fixe ce sur quoi les chaînes à venir ont le droit de s'appuyer, et il consigne les mesures qui
> l'établissent.

Toutes les mesures ci-dessous ont été prises au SHA **`f0b05f1`**, sur une stack **reconstruite
depuis zéro** (`docker compose down -v` puis `docker compose up -d --build`), provisionnement mené
jusqu'au marqueur `[provision] terminé.` — jamais jusqu'au seul healthcheck de `wpcli`, qui passe
*healthy* très avant la fin du provisionnement (`issue-31.md` §11).

**Versions en jeu, relevées dans les conteneurs :**

| Élément | Version mesurée | Commande |
|---|---|---|
| Serveur `db` | **MariaDB 10.11.19**-ubu2204, `have_ssl = DISABLED` | `SHOW VARIABLES LIKE 'have_ssl'` |
| Client dans `wpcli` | **mariadb-client 11.4.8-r0** (Alpine 3.21.5) | `apk list --installed` |
| WP-CLI | **2.12.0** | `wp --version` |
| PHP (les deux conteneurs) | **8.1.34** | `php -v` |

---

## 1. L'énoncé de l'issue est vrai, et il est incomplet

**Le symptôme se reproduit intégralement**, au mot près, sur une stack neuve au SHA `f0b05f1` —
contrairement aux issues #31 et #34 du lot 11, dont l'énoncé était faux. Il n'y a **rien à
requalifier sur le fond** ; il y a une **portée à élargir**.

L'issue nomme une seule commande. **Trois échouent, pour la même cause :**

```
$ wp db query 'SELECT 1'
Error: Failed to get current SQL modes. Reason: ERROR 2026 (HY000): TLS/SSL error: SSL is
required, but the server does not support it

$ wp db check
mariadb-check: Got error: 2026: TLS/SSL error: SSL is required, but the server does not support it
when trying to connect

$ wp db export /tmp/t.sql
mariadb-dump: Got error: 2026: "TLS/SSL error: SSL is required, but the server does not support it"
when trying to connect
```

Le contournement documenté fonctionne toujours et confirme que **le site n'est pas affecté** :

```
$ wp eval 'global $wpdb; echo $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts}");'
22
```

**Pourquoi le contournement marche, et ce que cela prouve.** `mysqli`/`pdo_mysql` des images PHP
officielles sont bâtis sur **mysqlnd**, qui n'emprunte pas le chemin du client externe et n'emploie
jamais cette formulation. Le site, le provisionnement et `$wpdb` passent donc par une pile de
connexion entièrement distincte. « N'affecte pas le site ni le code livré » est **démontré**, pas
supposé.

**Ce que l'issue ne dit pas et qui relève le budget.** `wp db export` est le chemin plausible du jour
de la mise en ligne. Un client cassé n'est donc pas seulement une gêne de recette : c'est une panne
qui se découvrirait au pire moment. **La recette d'acceptation porte sur `query` + `check` +
`export`, jamais sur `query` seule.**

**Un faux témoin, à ne pas réutiliser — cette phrase corrige une erreur de ce contrat.** Une première
version de ce document affirmait que `wp --info` rend `SQL modes:` **vide**, que c'était « la trace la
plus rapide du défaut » et que le champ « cesserait d'être vide » une fois le correctif posé.
**Le champ est bien vide, mais ce n'est pas un témoin de ce défaut, et il reste vide après
correction** — mesuré, correctif en place, stack neuve. Signalé par la chaîne d'implémentation, puis
vérifié indépendamment dans le phar de WP-CLI 2.12.0 :

`wp --info` passe par `Utils\get_sql_modes()` (ligne 29993 de `/usr/local/bin/wp`), qui construit sa
commande **sans `--host`, sans `--user`, sans base** :

```php
Process::create( "$binary --no-auto-rehash --batch --skip-column-names --execute=\"SELECT @@SESSION.sql_mode\"" )
```

Elle dépend donc entièrement de variables d'environnement (`MYSQL_HOST`, `MYSQL_PWD`, `USER`…) que le
service `wpcli` n'exporte pas : elle ne peut **pas** joindre le serveur `db`, TLS ou pas.
`wp db query`/`check`/`export` passent, eux, par `DB_Command::get_current_sql_modes()`, qui **fusionne**
les arguments de connexion (ligne 187787). Deux chemins distincts ; seul le second était en cause.

**La leçon est celle du lot 11 appliquée à moi-même** : ce contrat a promis un témoin sans l'avoir
mesuré, et une chaîne qui l'aurait pris au mot aurait conclu son correctif en échec alors qu'il
fonctionne. Corriger ce champ n'est **pas** dans le périmètre de cette issue et n'a **pas** été fait.

**Le vrai témoin, lui, est mesurable et suffit** : les trois commandes de §1 passent ou ne passent
pas.

## 2. La cause, établie et non déduite

Quatre faits mesurés, qui s'enchaînent.

**a. C'est le client qui exige TLS, pas le serveur qui l'impose.** Le préfixe `TLS/SSL error:` est la
formulation de MariaDB Connector/C (`CR_SSL_CONNECTION_ERROR`) ; `libmysqlclient` écrit
`SSL connection error:`. Le détail « *SSL is required, but the server does not support it* » est émis
**côté client**, après la poignée de main, quand le client a TLS exigé et que le serveur n'annonce
pas `CLIENT_SSL`.

**b. Le serveur est conforme et hors de cause.** `have_ssl = DISABLED` sur `mariadb:10.11`. **C'est
exactement ce qu'est la base d'un hébergement mutualisé** : du texte clair sur socket ou localhost.

**c. L'exigence est un défaut compilé du client 11.4, pas un fichier de configuration.**

```
$ mariadb --help   (extrait de la table des valeurs par défaut)
ssl                               TRUE
ssl-verify-server-cert            TRUE
```

Aucun fichier d'options ne porte `ssl` : `grep -rn -i ssl /etc/my.cnf /etc/my.cnf.d/` ne renvoie
**rien**, et `mysql --print-defaults` ne rend **aucun argument**. La cause est donc la **génération
du paquet client**, non sa configuration.

**d. Et c'est le point qui décide de la forme du remède : WP-CLI passe `--no-defaults`.**

```
$ wp db query 'SELECT 1' --debug=db
Debug: Final MySQL command: /usr/bin/env mariadb --no-defaults --no-auto-rehash --batch
--skip-column-names --host='db' --user='mtb' --default-character-set='utf8mb4'
--execute='SELECT @@SESSION.sql_mode'

$ wp db export /tmp/t.sql --debug=db
Debug: Final MySQL command: /usr/bin/env mariadb-dump --no-defaults 'mtb' --no-tablespaces
--host='db' --user='mtb' --default-character-set='utf8mb4' --result-file='/tmp/t.sql'
```

`--no-defaults` ordonne au client d'**ignorer tous les fichiers d'options**. Vérifié
expérimentalement, et c'est le résultat le plus important de ce contrat :

> Un `.cnf` déposé dans `/etc/my.cnf.d/` portant `[client]\nssl=0` **répare le client nu et ne
> répare pas WP-CLI**. Mesuré : après dépôt, `mysql --print-defaults` rend bien `--ssl=0` et
> `mariadb -h db … -e 'SELECT 1'` réussit, tandis que `wp db query` **et** `wp db export`
> continuent d'échouer avec le même message.

**C'est le piège central de cette issue.** Le remède intuitif — poser un fichier de configuration —
est **inopérant**, tout en ayant l'air de marcher si on le teste avec le client nu. Une chaîne qui
n'aurait pas fait la mesure `--debug=db` aurait livré un correctif qui ne corrige rien, accompagné
d'une explication fausse.

**Ce qui ne marche pas non plus, et pourquoi.** `wp db query 'SELECT 1' --skip-ssl` **échoue** : la
préflight `get_sql_modes()` construit sa propre ligne de commande, sans les arguments de
l'utilisateur, et avorte avant. Il n'existe **aucune variable d'environnement, aucun fichier de
configuration WP-CLI et aucun drapeau** capable d'atteindre cette invocation : `run_mysql_command()`
compose `force_env_on_nix_systems($cmd) . assoc_args_to_str($assoc_args)`, et
`get_mysql_binary_path()` résout le binaire par `/usr/bin/env which mysql` puis `which mariadb`.

## 3. Le seul point d'intervention est donc le binaire résolu par le `PATH`

Deux contraintes de placement, toutes deux **mesurées**, qui verrouillent la forme du remède :

```
$ mariadb --skip-ssl --no-defaults … --execute="SELECT 1"
mariadb: unknown option '--no-defaults'          ← --no-defaults DOIT être la première option

$ mariadb --no-defaults … --execute="SELECT 1" mtb --skip-ssl
1                                                 ← appendre en QUEUE fonctionne
```

Et pour `mariadb-dump`, dont les positionnels sont `base [tables…]` — le `getopt` de MariaDB
permute, l'option appendue n'est pas prise pour un nom de table :

```
$ mariadb-dump --no-defaults mtb wp_posts --host=db --user=mtb --skip-ssl
-- MariaDB dump 10.19-11.4.8-MariaDB, for Linux (x86_64)
```

**Conséquence, gelée** : un enrobage ne peut **jamais** insérer `--skip-ssl` avant les arguments
reçus. Il l'**append en fin de ligne**, toujours.

**Le `PATH` du conteneur `wpcli` est `/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin`** —
`/usr/local/bin` **précède** `/usr/bin`, où vivent les six binaires réels (`mysql`, `mariadb`,
`mariadb-dump`, `mariadb-check`, `mysqldump`, `mysqlcheck`, tous présents et vérifiés). Un enrobage
posé dans `/usr/local/bin` est donc résolu en premier par `/usr/bin/env`, **sans que le binaire réel
soit déplacé, renommé ou masqué** : il reste atteignable à son chemin complet `/usr/bin/mariadb`.

## 4. Ce qui est livré

**Six enrobages `sh`**, un par binaire client, dans `/usr/local/bin/`, posés par
`docker/wpcli/Dockerfile`. Chacun `exec` son binaire réel en `/usr/bin/` et **append `--skip-ssl` en
fin de ligne**.

**Garde obligatoire, non négociable** : l'enrobage **inspecte `"$@"` et se retire complètement** dès
qu'un argument commence par `--ssl`, `--tls` ou `--skip-ssl`. Sans cette garde, un `mariadb --ssl`
tapé à la main recevrait **le contraire de ce qu'il demande, en silence** — mesuré : la dernière
option l'emporte, `--skip-ssl --ssl` rend l'erreur TLS, donc `… --ssl` suivi de notre `--skip-ssl`
appendu rendrait une connexion en clair. C'est précisément la magie locale que ce dépôt paie ensuite
en dettes documentaires. **Avec la garde, l'enrobage ne décide jamais contre une intention
explicite : il ne fournit qu'un défaut.**

**Contrainte de transparence** : l'enrobage doit rester transparent à `--version` et `--help` —
`get_mysql_binary_path()` exécute `mysql --version` et **teste la présence de la chaîne `MariaDB`**
dans la sortie pour décider s'il bascule sur `mariadb`. Un enrobage qui altérerait cette sortie
casserait la résolution du binaire.

**Deux compléments dans le `Makefile`**, qui ne sont pas le remède et ne doivent jamais être
présentés comme tel :

- `make db-sql cmd="…"` — accès direct à la base via `docker compose exec db mariadb …`, **client et
  serveur du même build, TLS jamais en jeu**. Ce n'est **pas un repli de `wp db query`** : c'est le
  seul chemin vers la base qui ne charge ni WordPress ni `mtb-core`, donc le seul qui reste utilisable
  **le jour où c'est justement `mtb-core` qui est cassé**. Le service `db` n'exposant aucun port à
  l'hôte, c'est aujourd'hui la seule porte d'entrée directe.
- `make db-check` — recette d'acceptation de cette issue : rejoue `query`, `check` et `export`, et dit
  lequel échoue.

**Une sonde de non-régression dans `provision.sh`**, en **avertissement seulement, jamais un `exit`** :
le jour où l'image de base rebouge, la stack le dit d'elle-même au lieu qu'un agent le redécouvre
trois lots plus loin. Un `exit` casserait le démarrage de **toutes** les chaînes pour une commande de
confort — interdit.

## 5. Ce qui est explicitement refusé

| Approche | Verdict | Raison |
|---|---|---|
| **Activer TLS sur le serveur `db`** (certificats, `command:` sur le service) | **REFUSÉ — D9** | C'est le contournement déguisé que l'issue nomme elle-même. Un mutualisé PHP standard parle à sa base **en clair**. Donner TLS au serveur pour satisfaire un client trop exigeant, c'est **s'éloigner** de la cible de production pour faire taire un message. En prime : certificats à régénérer, dates d'expiration, nouvelle source de panne datée. **Que ça marche est précisément le danger** — on garderait une stack durablement infidèle. |
| **Poser un `[client] ssl=0` dans `/etc/my.cnf.d/`** | **REFUSÉ — inopérant** | `--no-defaults` (§2.d). Répare le client nu, ne répare pas WP-CLI. Mesuré, pas déduit. |
| **Aliaser `wp db query` dans le `Makefile`** vers autre chose | **REFUSÉ** | Second contournement déguisé : la commande resterait cassée pour quiconque l'invoque directement, et le dépôt affirmerait le contraire. |
| **Rebaser `wpcli` sur Debian** | **HORS PÉRIMÈTRE** | Bonne refonte (elle supprimerait l'écart d'uid 82/33, le `chown` numérique et la divergence Alpine/Debian entre l'outil de mesure et l'objet mesuré), mauvais prétexte : reconstruction complète, adoption à vie d'un `wp-cli.phar` et de sa vérification d'intégrité, revalidation intégrale du provisionnement — **et rien ne garantit que le client Debian ne fasse pas la même chose**, auquel cas on retomberait ici. Mérite sa propre issue `infra`. |
| **Monter le serveur en MariaDB 11.4** pour aligner les versions | **REFUSÉ** | Les générations 11.4+ génèrent un certificat auto-signé au démarrage : on obtiendrait une stack qui parle TLS, donc l'option D par une autre porte. |

## 6. Ce sur quoi les chaînes futures peuvent s'appuyer

1. **`wp db query`, `wp db check` et `wp db export` fonctionnent dans le conteneur `wpcli`**, sans
   drapeau, sans variable d'environnement, sans contournement. Le passage par `$wpdb` dans `wp eval`
   **n'est plus nécessaire** — il reste correct, simplement il n'est plus imposé.
2. **Le serveur `db` reste sans TLS**, `have_ssl = DISABLED`, fidèle au mutualisé. Aucune chaîne n'a
   le droit de l'activer (§5).
3. **La sonde `db_reachable` de `provision.sh` reste en `mysqli` pur** et **ne doit pas** être
   rebasculée sur `wp db check` par symétrie : elle teste précisément le chemin de connexion que
   WordPress utilisera, qui n'est pas celui du client externe.
4. **Une mesure prise au WP-CLI ne vaut pas pour le chemin web** (décision 29 de `ETAT.md`) — cette
   issue ne change rien à cette règle et ne l'atténue pas.

## 7. Arbitrages

| Question | Décision | Raison |
|---|---|---|
| Corriger le client ou le serveur ? | **Le client** | Le message accuse le client (§2.a) ; le serveur est conforme (§2.b). Corriger le serveur réparerait la mauvaise extrémité **et** violerait D9. |
| Fichier d'options ou enrobage ? | **Enrobage** | Le fichier d'options est **inopérant** sur WP-CLI (§2.d). Ce n'est pas une préférence, c'est une mesure. |
| Enrobage silencieux ou avec garde ? | **Avec garde** | Un binaire qui réécrit ses arguments en silence est un piège pour le lecteur de dans six mois. La garde (§4) coûte trois lignes et supprime l'objection. |
| `--skip-ssl` en tête ou en queue ? | **En queue, toujours** | `--no-defaults` doit être la première option ; mesuré, §3. |
| Six enrobages ou seulement `mariadb` ? | **Six** | `get_mysql_binary_path()` sonde `mysql` **puis** `mariadb` ; `db export` et `db check` passent par `mariadb-dump` et `mariadb-check`. N'en couvrir qu'un laisserait `export` cassé — le chemin du jour de la mise en ligne. |
| Sonde de non-régression bloquante ? | **Non, avertissement** | Un `exit` casserait le démarrage de toutes les chaînes pour une commande de confort. |

## 8. Deux dettes documentaires payées au passage, dans l'empreinte

- **`docker/provision/provision.sh`, commentaire de `db_reachable`** : il portait une explication
  causale de seconde main (« ce dernier exige TLS par défaut sur les versions récentes du paquet
  client »). Elle est **confirmée exacte** par §2.c — mais elle était affirmée sans mesure. Elle est
  réécrite avec les versions relevées, et renvoie ici.
- **`docker/provision/provision.sh`, message de repli des fixtures** : il annonce encore « `mtb-core`
  n'a pas encore livré cette commande (dette #29) », payée depuis. `docs/docker.md:161-165` désigne
  **nommément « l'issue qui touchera la stack »** pour le corriger : c'est celle-ci. Deux lignes.

## 9. Protocole de démonstration exigé

Repris tel quel de `issue-31.md` §11, adapté :

1. `docker compose down -v` puis `docker compose up -d --build` — **stack neuve, volumes détruits**.
2. Attendre `[provision] terminé.` dans `docker compose logs wpcli`. **Jamais le healthcheck de
   `wpcli` seul**, qui passe *healthy* dès ~9 s, très avant la fin du provisionnement.
3. Rejouer les **trois** commandes de §1. **Ne pas se servir de `wp --info` comme verdict** : son
   champ `SQL modes:` reste vide dans les deux états (§1) — il ne prouve ni le défaut ni sa
   réparation.
4. Vérifier la **garde de retrait** : un `--ssl` explicite doit **retomber sur l'erreur TLS**. C'est
   la démonstration qu'aucune magie silencieuse n'a été fabriquée. Mesuré conforme.
5. Citer la sortie réelle, avant et après. Un correctif dont le défaut n'a pas été reproduit est un
   correctif non démontré.

## 10. Vérification finale, mesurée par le lead

Stack **reconstruite depuis zéro**, marqueur `[provision] terminé.` atteint, fixtures importées
(`14 contenus créés`), sonde de non-régression `ok.`, accueil en **200**, journal des diagnostics
vide (aucune régression de #31).

| Mesure | Avant (`f0b05f1`) | Après |
|---|---|---|
| `wp db query 'SELECT 1'` | `Error: Failed to get current SQL modes… TLS/SSL error` | `1`, sortie **0** |
| `wp db check` | `mariadb-check: Got error: 2026: TLS/SSL error…` | `Success: Database checked.`, sortie **0** |
| `wp db export /tmp/t.sql` | `mariadb-dump: Got error: 2026: "TLS/SSL error…"` | `Success: Exported to '/tmp/t.sql'.`, sortie **0** |
| Garde `--ssl` explicite | — | `ERROR 2026 … TLS/SSL error` — **l'enrobage se retire bien** |
| `make db-check` | — | `OK` × 3, sortie **0** |

**Effet de bord observable, sans conséquence, à ne pas prendre pour une anomalie** : `wp --info` rend
désormais `MySQL version: /usr/bin/mariadb from 11.4.8-MariaDB…` au lieu de
`mariadb from 11.4.8-MariaDB…`. Le client imprime son `argv[0]`, qui est maintenant le chemin complet
passé par l'`exec` de l'enrobage. La chaîne `MariaDB` reste présente, donc la bascule de
`get_mysql_binary_path()` fonctionne toujours — vérifié : `MySQL binary: /usr/local/bin/mariadb`.

**Un piège corrigé en cours de route, qui vaut d'être retenu** : la première version de `make db-sql`
interpolait la requête **dans** un `sh -c '…'` déjà entre apostrophes. Toute apostrophe de la requête
fermait la chaîne, et MariaDB recevait **une requête différente de celle demandée** — sans le dire :
`SELECT 'bonjour'` rendait `Unknown column 'bonjour'`, une erreur de schéma assez plausible pour
envoyer chercher un défaut de base là où il n'y en avait pas. Or un littéral de chaîne
(`post_type='mtb_portee'`) est la forme **normale** d'un contrôle sur cette base. Réparé en faisant
voyager la requête par une variable d'environnement (`docker compose exec -e`), qui ne traverse plus
aucune couche de requotage. Vérifié : `post_type='mtb_portee' AND post_status='publish'` rend **4**,
`SELECT 'bonjour'` rend **bonjour**.

**Et le nom du type de contenu est `mtb_portee`, pas `portee`** — noté ici parce que la consigne du
lead avait avancé `portee` sans le mesurer, et qu'un `0` parfaitement correct a failli passer pour une
régression.
