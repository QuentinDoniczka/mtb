# PHP 8.1 : version encore couramment proposée par défaut (ou au choix) sur l'hébergement mutualisé
# français visé par le projet (o2switch, OVH, IONOS...) au moment du bootstrap (2026-08). On ne monte
# pas plus haut ici pour ne jamais laisser le thème ou l'extension dépendre d'une fonctionnalité PHP
# indisponible sur le futur hébergement (BRIEF §12, décision 4 de ETAT.md).
FROM wordpress:php8.1-apache

# msmtp relaie wp_mail() vers le capteur de courrier local "mail" (Mailpit) en développement.
# Ce paquet est un détail de la stack Docker : il ne remplace ni ne présuppose la configuration
# d'envoi de courrier de la production, qui reste celle de l'hébergeur (sendmail local classique
# sur mutualisé). Rien dans le thème ou l'extension ne dépend de sa présence.
RUN apt-get update \
    && apt-get install -y --no-install-recommends msmtp \
    && rm -rf /var/lib/apt/lists/*

COPY msmtprc /etc/msmtprc
COPY mail.ini /usr/local/etc/php/conf.d/zz-mtb-mail.ini

RUN chmod 600 /etc/msmtprc
