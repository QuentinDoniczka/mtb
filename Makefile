\
.PHONY: up provision seed reset down logs ps wp shell export-uploads debug-log debug-log-reset

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
