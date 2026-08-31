.PHONY: up provision seed reset down logs ps wp shell export-uploads debug-log debug-log-reset db-sql db-check css css-check

# Démarrage complet depuis zéro : build, lancement, provisionnement automatique par le
# conteneur wpcli (idempotent — voir docker/provision/provision.sh).
up:
	cp -n .env.example .env 2>/dev/null || true
	docker compose up -d --build

# Relance le provisionnement sur une stack déjà démarrée (utile après activation du thème
# ou de l'extension, ou après ajout d'une commande "wp mtb import-fixtures").
provision:
	docker compose restart wpcli

# Alias explicite demandé par le brief docker-mtb : "seed" = (re)provisionner les fixtures.
seed: provision

# Repart de zéro : supprime les volumes (base de données et cœur WordPress compris).
reset:
	docker compose down -v

down:
	docker compose down

logs:
	docker compose logs --tail=100 -f

ps:
	docker compose ps

# Exécute une commande WP-CLI ponctuelle, ex. : make wp cmd="user list"
wp:
	docker compose exec wpcli wp --path=/var/www/html $(cmd)

shell:
	docker compose exec wordpress bash

# Exporte les médias téléversés (volume nommé mtb_wp_data) vers ./export-uploads, pour
# migration vers l'hébergement de production.
export-uploads:
	mkdir -p export-uploads
	docker run --rm -v mtb_wp_data:/data -v "$$(pwd)/export-uploads:/export" alpine \
		sh -c "cp -r /data/wp-content/uploads/. /export/ 2>/dev/null || echo 'aucun média à exporter pour le moment'"
	@echo "Médias exportés dans ./export-uploads"

# Affiche le journal des diagnostics PHP (wp-content/debug.log), qui vit dans le volume et
# jamais dans le dépôt. Deux états valent « rien à signaler » et doivent se dire de la même
# façon : le fichier absent (aucune écriture depuis la création du volume) et le fichier présent
# mais vide (état laissé par "make debug-log-reset"). D'où le test sur "-s" et non sur "-f" : un
# "cat" d'un fichier vide n'afficherait rien du tout, et un écran muet ne se distingue pas d'une
# commande qui n'a pas tourné.
#
# Ce journal ne couvre QUE le chemin web : WP_DEBUG reste faux en WP-CLI (décision 29 de
# docs/ETAT.md), donc « aucune notice » ne se conclut jamais d'une commande "make wp".
debug-log:
	docker compose exec -T wordpress sh -c 'if [ -s /var/www/html/wp-content/debug.log ]; then cat /var/www/html/wp-content/debug.log; else echo "Journal vide : aucun diagnostic enregistré pour le moment (wp-content/debug.log absent ou de taille nulle)."; fi'

# Vide le journal, pour qu'une page puisse être mesurée à partir d'un journal dont on sait
# qu'il était vide au départ.
#
# Le "chown" n'est pas décoratif : "docker compose exec" entre dans le conteneur en root, alors
# que PHP écrit le journal sous www-data. Sans lui, un premier "make debug-log-reset" sur un
# volume neuf CRÉE le fichier en root:root, et PHP ne peut alors plus y écrire — les diagnostics
# repartent sur la sortie d'erreur du conteneur et "make debug-log" affiche un journal vide.
# Soit exactement la vérification faussement propre que l'issue #31 supprime. Le "chown" répare
# aussi un fichier laissé en root par une version antérieure de cette cible.
debug-log-reset:
	docker compose exec -T wordpress sh -c ': > /var/www/html/wp-content/debug.log && chown www-data:www-data /var/www/html/wp-content/debug.log'
	@echo "Journal des diagnostics vidé."

# Accès direct à la base via le client du service "db" — issue #30 (docs/contracts/issue-30.md).
#
# CECI N'EST PAS UN REPLI DE "wp db query" : c'est un OUTIL distinct, à ne jamais présenter
# comme une réparation. Client et serveur sont du même build ("mariadb:10.11"), donc TLS n'est
# jamais en jeu ici — ce que cette cible contourne n'a rien à voir avec ce que "wp db query"
# corrige (six enrobages dans docker/wpcli/, voir docker/wpcli/Dockerfile et docker/wpcli/bin/).
# Sa raison d'être : c'est le SEUL chemin vers la base qui ne charge ni WordPress ni "mtb-core",
# donc le seul qui reste utilisable le jour où c'est justement "mtb-core" qui est cassé. Le
# service "db" n'exposant aucun port à l'hôte, c'est aujourd'hui la seule porte d'entrée directe.
# Exemple : make db-sql cmd="SHOW TABLES"
# Sans "cmd", ouvre une invite interactive.
db-sql:
	docker compose exec -e MTB_DB_SQL_CMD="$(cmd)" db sh -c 'if [ -n "$$MTB_DB_SQL_CMD" ]; then exec mariadb -u"$$MYSQL_USER" -p"$$MYSQL_PASSWORD" "$$MYSQL_DATABASE" -e "$$MTB_DB_SQL_CMD"; else exec mariadb -u"$$MYSQL_USER" -p"$$MYSQL_PASSWORD" "$$MYSQL_DATABASE"; fi'

# Recette d'acceptation de l'issue #30 : rejoue "wp db query", "wp db check" et "wp db export"
# dans le conteneur "wpcli" et dit EXPLICITEMENT lequel échoue — les trois passent par les
# enrobages TLS de docker/wpcli/bin/, jamais par le raccourci de "db-sql" ci-dessus.
db-check:
	@printf '== wp db query ==\n'; \
	docker compose exec -T wpcli wp --path=/var/www/html db query 'SELECT 1' && r1=0 || r1=1; \
	printf '== wp db check ==\n'; \
	docker compose exec -T wpcli wp --path=/var/www/html db check && r2=0 || r2=1; \
	printf '== wp db export ==\n'; \
	docker compose exec -T wpcli wp --path=/var/www/html db export /tmp/mtb-db-check.sql && r3=0 || r3=1; \
	echo; \
	[ "$$r1" -eq 0 ] && echo "OK    : wp db query"  || echo "ECHEC : wp db query"; \
	[ "$$r2" -eq 0 ] && echo "OK    : wp db check"  || echo "ECHEC : wp db check"; \
	[ "$$r3" -eq 0 ] && echo "OK    : wp db export" || echo "ECHEC : wp db export"; \
	if [ "$$r1" -eq 0 ] && [ "$$r2" -eq 0 ] && [ "$$r3" -eq 0 ]; then exit 0; else exit 1; fi

# Feuilles de style minifiées (#40) — régénération des artefacts "*.min.css".
#
# TOUTE modification d'une feuille sous "wp-content/themes/mtb/assets/css/" s'accompagne d'un
# "make css" DANS LE MÊME COMMIT. Sans lui, l'artefact ne décrit plus sa source : le thème
# rebascule sur la source, la page reste correcte mais plus lourde, et "make css-check" le dit.
#
# "docker/outils/" n'est monté par aucun service (compose.yaml ne monte que le thème et
# l'extension) : le dépôt entier est donc monté à la volée sur "/depot", une seule racine, aucune
# ambiguïté sur la copie écrite. "--no-deps" parce que l'outil n'ouvre aucune base, et
# "--entrypoint php" pour court-circuiter docker-entrypoint.sh.
#
# MSYS_NO_PATHCONV=1 : sous Git Bash, la couche MSYS réécrit tout argument qui ressemble à un
# chemin absolu et transformerait "/depot/..." en un chemin Windows. Variable ignorée ailleurs.
#
# Sur un hôte Linux, "docker compose run" entre en root et les artefacts appartiendront à root —
# même famille que le "chown" de "debug-log-reset" ci-dessus. Sans objet sur Docker Desktop.
css:
	MSYS_NO_PATHCONV=1 docker compose run --rm --no-deps --entrypoint php -v "$$(pwd):/depot:rw" wordpress /depot/docker/outils/mtb-minifier-css.php --racine=/depot/wp-content/themes/mtb/assets/css

# Vérifie les 14 paires source/artefact sans rien écrire, et sort 1 dès qu'une seule paire n'est
# pas à jour. À jouer avant tout commit qui touche une feuille de style.
css-check:
	MSYS_NO_PATHCONV=1 docker compose run --rm --no-deps --entrypoint php -v "$$(pwd):/depot:rw" wordpress /depot/docker/outils/mtb-minifier-css.php --racine=/depot/wp-content/themes/mtb/assets/css --verifier
