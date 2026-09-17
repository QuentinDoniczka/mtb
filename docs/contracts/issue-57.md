# Contrat d'interface — Issue #57 — Détecter la péremption des ancres de ligne du cœur WordPress (T114)

Gelé le 2026-09-17 par le lead de l'issue. Aucun côté thème : l'issue ne touche ni le thème, ni
l'écran de l'éleveuse, ni aucune fonction de lecture. Ce contrat fixe la forme du témoin, pour que la
passe d'intégration, `docker-mtb` et les chaînes futures sachent ce qu'elles lisent.

## 0. Ce que le témoin prouve, et ce qu'il ne prouve pas

- Il prouve qu'**un fichier du cœur cité avec un numéro de ligne** est, octet pour octet, celui qui a été
  relevé (empreinte md5), et il dit quelle version de WordPress est installée.
- Il **ne prouve pas** qu'un rappel de `mtb-core` mord encore : « fichier inchangé » n'est pas « rappel
  vivant » (T115, #58).
- Il **ne certifie pas chaque citation passée** : le registre atteste l'état des fichiers au
  2026-09-17, sur un WordPress 6.9 dont on a mesuré la version. Une citation déjà fausse ce jour-là n'est pas
  rendue vraie par lui (arbitrage Q-b).
- Un changement dans un fichier du cœur **non cité** reste invisible, et c'est voulu.
- Il ne tourne **que dans la pile Docker de développement**. La production ne l'exécute pas.

## 1. Registre — `docker/provision/ancres-coeur.txt`

Une ligne par fichier du cœur cité avec un numéro de ligne, où que ce soit, sous
`wp-content/plugins/mtb-core/`, `wp-content/themes/mtb/`, `docs/contracts/` ou `docs/ETAT.md`. **Le périmètre
est le défaut, pas la liste de l'issue** (décision 78) : la liste de l'issue en nommait 7, l'inventaire en
compte plusieurs dizaines.

```
<version relevée> <AAAA-MM-JJ> <md5 en minuscules> <chemin relatif à la racine du cœur>
```

- Séparateur : une ou plusieurs espaces. Lignes `#` et lignes vides ignorées. Tri par chemin.
- Version `^[0-9]+\.[0-9]+(\.[0-9]+)?$` · date `^[0-9]{4}-[0-9]{2}-[0-9]{2}$` · md5 `^[0-9a-f]{32}$` ·
  chemin relatif, sans `..`, sans espace, unique.
- Le fichier peut arriver en CRLF (checkout Windows) : le script retire les `\r` avant lecture.
- Exclus : `wp-config.php` (généré par l'image), tout fichier du dépôt.
- Nom nu ambigu que le contexte ne tranche pas : **tous les candidats sont inscrits**.

**Règles d'en-tête, obligatoires et gelées** :
1. Toute nouvelle citation d'une ligne du cœur ajoute la ligne de ce fichier **dans le même commit**.
2. Une empreinte ne change qu'**après relecture de toutes les citations** du fichier (y compris « même
   fichier, l. N » et `:N` nus). Version, date et empreinte changent ensemble. Une citation fausse dans
   un contrat gelé se corrige par acte daté.
3. Sur AVERTISSEMENT, on re-date la version et la date, **jamais l'empreinte**.
4. Une ligne ne se retire que lorsque la recherche ne rend plus aucune citation.
5. Préférer citer un nom de crochet ou de fonction plutôt qu'un numéro de ligne.
6. Provenance et statut de validation des empreintes (sommes officielles ou non) écrits en tête.

## 2. Script — `docker/provision/temoin-ancres.sh`

sh POSIX (busybox ash du conteneur `wpcli`). Lancé par `sed 's/\r$//' <script> | /bin/sh` ou
`tr -d "\r" < <script> | sh`. Il lit le registre par redirection explicite et donne `</dev/null` à toute
commande externe.

| Variable | Défaut |
|---|---|
| `MTB_ANCRES_REGISTRE` | `/provision/ancres-coeur.txt` |
| `MTB_ANCRES_RACINE` | `/var/www/html` |
| `MTB_ANCRES_VERSION_OBSERVEE` | sortie de `wp --path=<racine> core version` |

Aucune de ces variables n'est posée par `compose.yaml` : elles servent à la preuve.
Aucune écriture hors d'un fichier temporaire, aucun réseau, aucune base.
La comparaison des versions est une **égalité littérale** (`6.9` ≠ `6.9.7` ≠ `6.9.0`).

## 3. Verdicts, lignes et statuts

Préfixe fixe : `[provision] ANCRES DU CŒUR : `. Recherche dans les journaux par la sous-chaîne ASCII
`ANCRES DU C`. **Exactement une ligne de bilan, toujours la dernière.**

| Verdict | Condition | Statut |
|---|---|---|
| `ok` | registre lu, ≥ 1 ligne valide, 0 anomalie, version lue, toutes les empreintes égales, toutes les versions = version observée | 0 |
| `AVERTISSEMENT` | toutes les empreintes égales, au moins une version ≠ version observée | 10 |
| `TÉMOIN INOPÉRANT` | registre introuvable ou vide, ligne mal formée, chemin en double, version illisible, aucun outil d'empreinte | 20 |
| `ALERTE` | au moins un fichier changé ou disparu | 30 |

Tout autre statut signifie « interrompu, aucun bilan fiable ». Les lignes de détail (ALERTE par fichier,
avec la commande `git grep -n -F -e '<basename>' -- wp-content/plugins/mtb-core wp-content/themes/mtb
docs/contracts docs/ETAT.md` ; ALERTE « fichier cité disparu » ; TÉMOIN INOPÉRANT avec son motif) et les
quatre formes de bilan sont celles du plan back du 2026-09-17, recopiées dans le script. `ok` ne s'émet
jamais à côté d'une anomalie.

Conséquence assumée : `wp-includes/version.php` étant cité avec un numéro de ligne, **toute montée de version
réelle lève au moins une ALERTE**.

## 4. Intégration — `docker/provision/provision.sh`

- Appel **après** la sonde « wp db query » (#30), **avant** `terminé.`, précédé d'un test `-r` sur le
  script. Script absent : ligne TÉMOIN INOPÉRANT et statut 20.
- **Jamais d'`exit`, jamais d'échec du provisionnement** du fait du témoin (`restart: unless-stopped`
  mettrait `wpcli` en boucle).
- Après `terminé.` et le rappel T39 : une ligne de rappel pour les statuts 30, 20 et « autre ». Rien pour
  0 et 10.
- `compose.yaml` n'est pas modifié : `/provision` est déjà monté en lecture seule.

## 5. Frontière avec le module `indexation-heritee`

Le témoin **n'est pas une sonde de ce module** : il ne vit pas dans `mtb-core`, n'est pas une commande
WP-CLI et ne lit aucun état du module. **Le motif 3 gelé est intact.** Les fichiers du module ne reçoivent que
des **actes datés en commentaire** (contrôle de la décision 75 : diff hors commentaires vide, zéro
suppression).

## 6. Interdits

- Changer une empreinte sans relire les citations du fichier.
- Lire `ok` comme « les rappels mordent ».
- Exécuter ce témoin en production, ou le poser dans `mtb-core`.
- Faire échouer `provision.sh` à cause du témoin.
- Réécrire en silence une phrase d'un contrat gelé : seuls les actes datés sont admis.

## Arbitrages

| # | Question | Décision | Motif |
|---|---|---|---|
| 1 | Version seule, ou empreintes ? | **Empreintes md5 par fichier. La version sert de contexte** | La version est un indicateur indirect : une 6.9.x qui ne touche aucun fichier cité produirait une alerte que l'on apprend à ignorer (décision 83). L'empreinte mesure le fait réel. |
| 2 | Scanner le texte, ou tenir un registre ? | **Registre déclaratif indexé par fichier du cœur** | Le scan remonte nos propres fichiers et des noms nus ambigus, et `docs/` n'est pas monté dans `wpcli`. Le registre couvre aussi les ancres des fichiers qu'on n'a pas le droit d'ouvrir. |
| 3 | Périmètre : 7 fichiers ou le défaut entier ? | **Le défaut entier** | Décision 78. La liste de l'issue omettait déjà des ancres de ses propres contrats. |
| 4 | Où le témoin tourne-t-il ? | **`provision.sh`** | Il tourne à chaque démarrage du conteneur `wpcli` : `make provision`, ou `make up` quand ce dernier crée ou redémarre `wpcli` (un `make up` sur un `wpcli` déjà en marche et inchangé ne le relance pas — rectifié le 2026-09-17 avant commit, relevé par la refacto). `docker-mtb` relit ce journal en fin de lot. `verifier-redirections` attribuerait la panne au mauvais module. Une cible `make` serait une chose de plus à retenir. Une commande dans `indexation-heritee` contredirait le motif 3. |
| 5 | Quatre verdicts, ou cinq ? | **Cinq, dont TÉMOIN INOPÉRANT, avec les statuts 0/10/20/30** | Un registre vide ou illisible ferait sinon passer pour vrai un témoin qui ne vérifie rien. |
| 6 | Que certifie le registre ? | **L'état des fichiers au 2026-09-17, pas chaque citation passée** | C'est honnête : on ne peut pas remesurer ce jour-là toutes les citations antérieures. |
| 7 | Extension d'empreinte | **Les fichiers de `docker/provision/` et `docs/docker.md`. Plus, en actes datés limités aux commentaires : `identite-des-comptes.php`, `admin/vocabulaire-page/bootstrap.php`, `issue-54.md`, `issue-56.md`** | Ces fichiers affirment que les ancres « se périment en silence ». Le témoin rend cette phrase fausse, et la décision 78 ouvre le motif, pas la liste. Aucune chaîne sœur n'est active. |
| 8 | Quel acte daté pour le motif 1 ? | **Placé sous le motif 1 de `bootstrap.php`, avant tout autre ajout** | Il dit les trois sujets réels et les maintient sans renommage, avec son décompte mesuré. Il remplace « périmètre clos et daté » par un critère formulé par le défaut. Obligation portée par `ETAT.md` (lots 21 et 22). |
| 9 | `auto_update_core_major = enabled` | **Hors périmètre : constat mesuré pour le rapport** | Le témoin en limite les dégâts au provisionnement suivant. Épingler l'image ou désactiver les mises à jour est une autre issue, de type `infra`. |
