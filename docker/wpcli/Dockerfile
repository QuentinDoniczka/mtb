# Image officielle WP-CLI (Alpine) — même version PHP que le conteneur "wordpress" (voir
# docker/wordpress/Dockerfile pour le choix de 8.1).
FROM wordpress:cli-php8.1

# L'image officielle bascule sur l'utilisateur "www-data" (uid 82 sur cette image Alpine —
# voir le commentaire du service "wpcli" dans compose.yaml, qui force néanmoins l'exécution
# en uid 33 pour partager le volume avec le conteneur "wordpress", Debian). Les commandes
# d'installation de paquet et de permission qui suivent ont besoin de "root".
USER root

# msmtp fait ici défaut à l'image officielle : sans lui, wp_mail() appelé depuis WP-CLI
# (provisionnement, "wp mtb import-fixtures", toute commande future qui enverrait un
# courriel) échoue avec "sendmail: can't connect to remote host (127.0.0.1)" — sendmail_path
# pointe par défaut sur un sendmail local absent de cette image. Même correctif que le
# conteneur "wordpress", pour la même raison : router wp_mail() vers Mailpit, jamais vers un
# domaine externe (BRIEF §12 / D6).
RUN apk add --no-cache msmtp

# Contexte de build = racine du dépôt (voir compose.yaml) : fichiers partagés avec l'image
# "wordpress" (docker/mail/), le format msmtp/php.ini ne changeant pas d'une distribution à
# l'autre.
COPY docker/mail/msmtprc /etc/msmtprc
COPY docker/mail/mail.ini /usr/local/etc/php/conf.d/zz-mtb-mail.ini

# Le service "wpcli" du compose force "user: 33:33" (uid du "www-data" Debian de l'image
# "wordpress", pour que les deux conteneurs écrivent dans le même volume sans conflit de
# permission) — un uid qui n'a pas de nom dans /etc/passwd sur cette image Alpine. D'où un
# chown numérique explicite plutôt que "chown www-data", qui viserait le mauvais uid (82) ici.
# Le fichier ne contient aucun secret (voir docker/mail/msmtprc) : 600 reste le réglage le
# plus fermé qui laisse le propriétaire le lire.
RUN chown 33:33 /etc/msmtprc \
    && chmod 600 /etc/msmtprc

# Enrobages TLS des six clients — issue #30 (docs/contracts/issue-30.md).
#
# Le client MariaDB Connector/C 11.4 de cette image exige TLS par défaut (valeurs
# compilées, pas un fichier d'options), alors que le serveur "db" (mariadb:10.11,
# développement local) n'en propose pas — fidèle à un hébergement mutualisé PHP standard
# (D9). WP-CLI invoque toujours son client avec "--no-defaults" en tête de ligne, ce qui
# rend inopérant tout fichier "[client] ssl=0" : "wp db query", "wp db check" et
# "wp db export" échouaient donc systématiquement avec
# "TLS/SSL error: SSL is required, but the server does not support it" — mesuré au
# contrat. Six scripts, un par nom de client présent dans l'image, sont pour cela posés
# dans "/usr/local/bin", qui précède "/usr/bin" dans le PATH du conteneur (PATH relevé et
# cité au contrat, §3) : chacun "exec"e le binaire réel de "/usr/bin/<nom>", jamais
# déplacé, renommé ni masqué, en ajoutant "--skip-ssl" en fin de ligne (jamais en tête :
# "--no-defaults" doit rester la première option) sauf si l'appelant a lui-même demandé
# "--ssl"/"--tls"/"--skip-ssl", auquel cas le script se retire entièrement.
#
# Pourquoi les six et pas seulement ceux que WP-CLI appelle : il sonde "mysql" puis
# "mariadb", et invoque "mariadb-dump" pour "db export", "mariadb-check" pour "db check"
# (mesures au contrat) ; les noms "mysqldump" et "mysqlcheck" sont couverts en plus, pour
# qu'aucun client de l'image ne garde le défaut TLS selon le nom par lequel il est appelé.
#
# Ce commentaire et le contrat sont les DEUX seules sources de cette explication : chaque
# script ne porte qu'un en-tête court, limité à ce qui est vrai de LUI (les six diffèrent —
# seul "mysql" est sondé par "mysql --version" ; seuls les dumps ont pour sortie standard
# la sauvegarde elle-même). Ne pas y recopier cette explication : c'est en six copies de la
# même prose qu'une phrase fausse s'est propagée.
#
# "sed 's/\r$//'" plutôt qu'un ".gitattributes" : ces six fichiers n'ont volontairement
# AUCUNE extension (leur nom EST le nom du binaire qu'ils remplacent dans le PATH — un
# ".sh" les rendrait invisibles à "wp db query"), donc un motif ".gitattributes" du type
# "*.sh text eol=lf" ne les couvrirait pas ; il faudrait un motif propre à ce dossier
# ("docker/wpcli/bin/* text eol=lf"), qui fonctionnerait mais resterait un second
# mécanisme à maintenir en plus de celui déjà posé pour "provision.sh" dans compose.yaml.
# La normalisation au moment du "COPY" reproduit exactement ce précédent (même geste,
# même raison : "core.autocrlf=true" sans ".gitattributes" racine — un ".gitattributes"
# racine renormaliserait aussi "docs/migration/source/html/*.html", dont la fin de ligne
# EST une pièce à conviction d'archive, dette T-#21-m), sans dépendre du réglage Git de la
# machine qui construit l'image, et sans toucher un octet versionné : la normalisation ne
# vit que dans cette étape de build.
COPY docker/wpcli/bin/ /tmp/db-client-wrappers/
RUN set -eu; \
    for nom in mysql mariadb mariadb-dump mariadb-check mysqldump mysqlcheck; do \
        sed 's/\r$//' "/tmp/db-client-wrappers/$nom" > "/usr/local/bin/$nom"; \
        chmod 755 "/usr/local/bin/$nom"; \
    done; \
    rm -rf /tmp/db-client-wrappers

USER www-data
