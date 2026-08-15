\
.PHONY: up provision seed reset down logs ps wp shell export-uploads

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
