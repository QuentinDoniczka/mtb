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

USER www-data
