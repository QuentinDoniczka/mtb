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

# Contexte de build = racine du dépôt (voir compose.yaml) : docker/mail/ est partagé avec
# l'image wpcli, qui a le même besoin d'envoyer du courrier (voir docker/wpcli/Dockerfile).
COPY docker/mail/msmtprc /etc/msmtprc
COPY docker/mail/mail.ini /usr/local/etc/php/conf.d/zz-mtb-mail.ini

# Les processus Apache qui traitent les requêtes web (donc wp_mail() appelé depuis PHP, ex.
# le formulaire de contact #22) tournent sous l'utilisateur "www-data", jamais "root" — c'est
# la configuration standard de cette image officielle. Le fichier doit donc appartenir à
# www-data pour rester lisible par le processus qui en a besoin, tout en restant fermé à
# quiconque d'autre (600 = lecture/écriture au seul propriétaire). "root:root 600" (l'ancien
# réglage) empêchait www-data de le lire : c'est ce qui rendait wp_mail() muet sur un
# démarrage à froid tout en semblant fonctionner après une correction manuelle perdue à la
# prochaine recréation du conteneur — d'où la correction ici, dans l'image, et non à la main.
RUN chown www-data:www-data /etc/msmtprc \
    && chmod 600 /etc/msmtprc

# Surcharge des réglages de journalisation d'erreurs de l'image officielle (#31) : sans elle, le
# journal dédoublonne les diagnostics répétés — une notice qui se répète sur vingt pages n'y
# apparaît qu'une fois —, ce qui rend non probante toute vérification qui le lit. Le fichier
# explique le détail, y compris pourquoi la directive de troncature qu'il porte n'a, elle, aucun
# effet sur le PHP 8.1 de cette stack. Le préfixe "zz-" du nom de destination est ce qui garantit
# qu'il est chargé après "error-logging.ini". Même contexte de build (racine du dépôt) que les
# COPY ci-dessus, d'où le chemin source complet.
COPY docker/wordpress/zz-mtb-debug.ini /usr/local/etc/php/conf.d/zz-mtb-debug.ini
