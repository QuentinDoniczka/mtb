#!/bin/sh
# Provisionnement idempotent de la stack MTB — appelé au démarrage du conteneur wpcli.
# Relançable sans risque sur une stack déjà en place (pas de duplication de contenu).
set -u

WP_PATH=/var/www/html
WP="wp --path=${WP_PATH}"

log() {
	echo "[provision] $*"
}

wait_for() {
	description="$1"
	shift
	i=0
	until "$@" >/dev/null 2>&1; do
		i=$((i + 1))
		if [ "$i" -ge 60 ]; then
			echo "[provision] ERREUR : $description toujours indisponible après 60 tentatives (~2 min)" >&2
			exit 1
		fi
		sleep 2
	done
}

log "attente de la base de données…"
# On teste la connexion en PHP pur (mysqli) plutôt que via "wp db check", qui délègue au
# client mariadb-check. Ce dernier exige TLS par défaut sur les versions récentes du paquet
# client, alors que le service "db" (mariadb:10.11, développement local) ne l'active pas.
db_reachable() {
	php -r '
		$link = @mysqli_connect(getenv("WORDPRESS_DB_HOST"), getenv("WORDPRESS_DB_USER"), getenv("WORDPRESS_DB_PASSWORD"), getenv("WORDPRESS_DB_NAME"));
		exit($link ? 0 : 1);
	'
}
wait_for "la base de données" db_reachable

log "attente du cœur WordPress (wp-load.php, déposé par le conteneur wordpress)…"
wait_for "wp-load.php" test -f "${WP_PATH}/wp-load.php"

installation_fraiche=0
if ! $WP core is-installed 2>/dev/null; then
	log "installation de WordPress en français (fr_FR)…"
	$WP core install \
		--url="${WP_SITE_URL}" \
		--title="${WP_SITE_TITLE}" \
		--admin_user="${WP_ADMIN_USER}" \
		--admin_password="${WP_ADMIN_PASSWORD}" \
		--admin_email="${WP_ADMIN_EMAIL}" \
		--locale=fr_FR \
		--skip-email
	installation_fraiche=1
else
	log "WordPress déjà installé — étape ignorée."
fi

log "expéditeur du courrier sortant (mu-plugin de développement)…"
# WordPress calcule par défaut un expéditeur du type "wordpress@localhost" à partir de
# WP_SITE_URL. Cette adresse est rejetée par la validation d'adresse de PHPMailer (pas de
# domaine à point), donc wp_mail() échoue silencieusement en local. On force une adresse
# valide via un mu-plugin, écrit à chaque provisionnement (idempotent car identique à chaque
# fois) — un simple détail d'environnement de développement, pas une décision produit.
mkdir -p "${WP_PATH}/wp-content/mu-plugins"
cat > "${WP_PATH}/wp-content/mu-plugins/zz-mtb-docker-mail.php" <<'PHP'
<?php
// Placeholder d'amorçage Docker — expéditeur de courrier de développement uniquement.
// Écrit par docker/provision/provision.sh, hors du dépôt (wp-content n'est pas versionné ici).
add_filter( 'wp_mail_from', static fn () => 'no-reply@mtbrabant.local' );
add_filter( 'wp_mail_from_name', static fn () => 'MTB (développement)' );
PHP

log "langue, fuseau horaire, format des permaliens…"
$WP language core install fr_FR --activate >/dev/null 2>&1 || log "AVERTISSEMENT : installation de la langue fr_FR impossible (hors-ligne ?)"
$WP option update WPLANG fr_FR >/dev/null
# "option update WPLANG" passe par sanitize_option('WPLANG'), qui n'accepte que les locales
# réellement installées (wp-content/languages/…) et vide silencieusement l'option sinon — sans
# faire échouer la commande ci-dessus. Si le téléchargement du paquet fr_FR a échoué juste
# au-dessus (poste hors-ligne, miroir indisponible…), le site sert alors <html lang="en-US">
# tout en laissant ce script conclure "terminé." comme si tout s'était bien passé. On relit
# l'option pour transformer ce silence en signal explicite (dette T39).
wplang_effectif="$($WP option get WPLANG 2>/dev/null)"
if [ "$wplang_effectif" != "fr_FR" ]; then
	site_non_francophone=1
	log "ERREUR : la locale du site n'est PAS fr_FR après provisionnement (WPLANG='${wplang_effectif}'). Le paquet de langue fr_FR n'a probablement pas pu être téléchargé (poste hors-ligne ?). Le site sert <html lang=\"en-US\">. Relancer 'make provision' avec un accès réseau pour corriger."
else
	site_non_francophone=0
fi
$WP option update timezone_string "Europe/Paris" >/dev/null
$WP option update date_format "d/m/Y" >/dev/null
$WP option update blogdescription "Élevage de bergers hollandais du Mont Brabant" >/dev/null
# "wp rewrite ... --hard" écrit un .htaccess et avertit qu'il ne sait pas détecter la
# configuration du serveur web depuis WP-CLI — bénin ici : l'image "wordpress:php8.1-apache"
# active déjà mod_rewrite et AllowOverride (permaliens vérifiés fonctionnels dans la stack).
# On filtre ce seul avertissement connu pour ne pas noyer un vrai avertissement futur.
filtrer_avertissement_htaccess() {
	grep -v '^Warning: Regenerating a .htaccess file requires special configuration' || true
}
$WP rewrite structure "/%postname%/" --hard 2>&1 >/dev/null | filtrer_avertissement_htaccess
$WP rewrite flush --hard 2>&1 >/dev/null | filtrer_avertissement_htaccess

log "activation du thème mtb…"
if [ -f "${WP_PATH}/wp-content/themes/mtb/style.css" ]; then
	if $WP theme activate mtb >/dev/null 2>&1; then
		log "thème mtb activé."
	else
		log "AVERTISSEMENT : le thème mtb existe mais son activation a échoué (voir wp theme activate mtb en direct)."
	fi
else
	log "thème mtb absent de wp-content/themes/mtb — étape ignorée (attendu tant que leaddev-front-mtb/dev-front-mtb n'a pas livré)."
fi

log "activation de l'extension mtb-core…"
if [ -f "${WP_PATH}/wp-content/plugins/mtb-core/mtb-core.php" ]; then
	if $WP plugin activate mtb-core >/dev/null 2>&1; then
		log "extension mtb-core activée."
	else
		log "AVERTISSEMENT : l'extension mtb-core existe mais son activation a échoué (voir wp plugin activate mtb-core en direct)."
	fi
else
	log "extension mtb-core absente de wp-content/plugins/mtb-core — étape ignorée (attendu tant que leaddev-back-mtb/dev-back-mtb n'a pas livré)."
fi

log "comptes utilisateur…"
if ! $WP user get "${WP_EDITOR_USER}" >/dev/null 2>&1; then
	$WP user create "${WP_EDITOR_USER}" "${WP_EDITOR_EMAIL}" \
		--role=editor \
		--user_pass="${WP_EDITOR_PASSWORD}" \
		--display_name="Fabienne Guéneau" >/dev/null
	log "compte éditrice « ${WP_EDITOR_USER} » créé avec le rôle natif WordPress « Éditeur » — c'est le rôle le plus étroit disponible tant que mtb-core ne définit pas de rôle métier dédié plus précis. Ne jamais lui donner « Administrateur »."
else
	log "compte éditrice déjà présent."
fi
if [ "$installation_fraiche" -eq 1 ]; then
	log "compte administrateur « ${WP_ADMIN_USER} » créé par wp core install."
else
	log "compte administrateur « ${WP_ADMIN_USER} » déjà présent (créé par la première installation)."
fi

log "pages fixes (contact, espace protégé)…"
if [ -z "$($WP post list --post_type=page --name=contact --field=ID)" ]; then
	$WP post create --post_type=page --post_title="Contact" --post_status=publish --post_name=contact \
		--post_content="Page de contact — à composer avec les blocs du catalogue une fois livrés (voir BRIEF §9)." >/dev/null
	log "page « Contact » créée."
fi

if [ -z "$($WP post list --post_type=page --name=espace-prive --field=ID)" ]; then
	$WP post create --post_type=page --post_title="Espace privé (démonstration)" --post_status=publish \
		--post_name=espace-prive --post_password="chiot2026" \
		--post_content="Page de démonstration du mécanisme natif WordPress de protection par mot de passe (BRIEF §8)." >/dev/null
	log "page protégée par mot de passe « Espace privé » créée (mot de passe de démo : chiot2026)."
fi

log "composition de l'accueil et de la page Contact (décision utilisateur du 23/08/2026 : un démarrage à froid doit montrer le site, pas une coquille vide)…"
# Contenu de démonstration uniquement : les blocs ci-dessous tirent leur texte des fixtures
# (docker/fixtures/*.json, "DEMO…", affixe « de Démonstration »), jamais d'un fait d'élevage réel.
theme_actif="$($WP theme list --status=active --field=name 2>/dev/null)"
plugin_actif="$($WP plugin list --status=active --field=name 2>/dev/null)"

motif_accueil="${WP_PATH}/wp-content/themes/mtb/patterns/accueil.php"
accueil_id=""
if [ "$theme_actif" != "mtb" ]; then
	log "AVERTISSEMENT : thème mtb non actif — accueil non composée. Relancer le provisionnement une fois le thème actif."
elif [ ! -f "$motif_accueil" ]; then
	log "AVERTISSEMENT : motif mtb/accueil introuvable (${motif_accueil} absent) — accueil non composée."
else
	# Le balisage de blocs de la page vient du motif "mtb/accueil" du thème (patterns/accueil.php),
	# LU à chaque provisionnement plutôt que recopié à la main ici : ce script ne devient jamais une
	# seconde source de vérité sur « ce qui compose l'accueil » à côté du thème.
	#
	# Volontairement PAS "<!-- wp:pattern {"slug":"mtb/accueil"} /-->" (qui référencerait le motif
	# sans le développer) : plusieurs composants du catalogue — le bandeau d'ouverture en tête — lisent
	# le PREMIER BLOC de post_content pour décider qui porte le "h1" de la page (garde 4/5 de
	# titre-principal.php). Cette lecture porte sur les blocs réellement présents dans post_content ;
	# un simple renvoi vers le motif la laisserait voir "core/pattern" et casserait la déduplication du
	# titre (h1 en double constaté à l'écran lors de la vérification du 23/08/2026). On extrait donc le
	# balisage tel qu'un clic "Insérer" sur ce motif l'aurait copié dans la page — pas une composition
	# inventée par ce script.
	blocs_accueil="$(awk '/^\?>/{trouve=1; next} trouve{print}' "$motif_accueil")"

	if [ -z "$blocs_accueil" ]; then
		log "AVERTISSEMENT : le motif mtb/accueil (patterns/accueil.php) ne contient aucun balisage de bloc après son en-tête PHP — accueil non composée."
	else
		accueil_id="$($WP post list --post_type=page --name=accueil --field=ID)"
		blocs_accueil_tmp="$(mktemp)"
		printf '%s\n' "$blocs_accueil" > "$blocs_accueil_tmp"
		if [ -z "$accueil_id" ]; then
			accueil_id="$($WP post create --post_type=page --post_title="Accueil" --post_status=publish --post_name=accueil \
				--porcelain "$blocs_accueil_tmp")"
			log "page « Accueil » créée (id ${accueil_id}), composée avec le motif mtb/accueil."
		else
			$WP post update "$accueil_id" "$blocs_accueil_tmp" >/dev/null
			log "page « Accueil » déjà présente (id ${accueil_id}) — contenu réaffirmé depuis le motif mtb/accueil."
		fi
		rm -f "$blocs_accueil_tmp"
	fi
fi

if [ -n "$accueil_id" ]; then
	page_on_front_actuel="$($WP option get page_on_front 2>/dev/null)"
	show_on_front_actuel="$($WP option get show_on_front 2>/dev/null)"
	if [ "$show_on_front_actuel" != "page" ] || [ "$page_on_front_actuel" != "$accueil_id" ]; then
		$WP option update show_on_front page >/dev/null
		$WP option update page_on_front "$accueil_id" >/dev/null
		log "page d'accueil du site réglée sur « Accueil » (id ${accueil_id})."
	else
		log "page d'accueil du site déjà réglée sur « Accueil »."
	fi
fi

if echo "$plugin_actif" | grep -qx "mtb-core"; then
	contact_id="$($WP post list --post_type=page --name=contact --field=ID)"
	if [ -n "$contact_id" ]; then
		$WP post update "$contact_id" --post_content='<!-- wp:mtb/formulaire-contact /-->' >/dev/null
		log "page « Contact » composée avec le bloc mtb/formulaire-contact (id ${contact_id})."
	else
		log "AVERTISSEMENT : page « Contact » introuvable — composition impossible (voir l'étape « pages fixes » ci-dessus)."
	fi
else
	log "AVERTISSEMENT : extension mtb-core non active — page Contact non composée, le bloc mtb/formulaire-contact n'est pas enregistré. Relancer le provisionnement une fois l'extension active."
fi

log "contenu structuré (portées, chiens, résultats de travail)…"
# MTB_FIXTURES=0 saute le jeu de démonstration SANS RIEN SUPPRIMER (#29) : c'est ce qui
# débloque le garde de non-mélange de "wp mtb importer-portees-chiens" (#20), qui refuse de
# s'exécuter tant que du contenu de démonstration est présent — et qu'aucun "docker compose
# down -v" ne peut satisfaire seul, puisque le provisionnement suivant reseme aussitôt. Nom de
# variable gelé, déjà communiqué à la chaîne #20 : ne pas le renommer.
if [ "${MTB_FIXTURES:-1}" = "0" ]; then
	log "MTB_FIXTURES=0 — jeu de démonstration (4 portées, 5 chiens, 5 résultats) volontairement NON importé. Rien n'a été supprimé. Relancer avec MTB_FIXTURES=1 (ou sans la variable, c'est la valeur par défaut) pour l'importer."
elif $WP mtb import-fixtures --help >/dev/null 2>&1; then
	log "commande « wp mtb import-fixtures » détectée (fournie par mtb-core) — import des fixtures…"
	$WP mtb import-fixtures \
		--portees=/fixtures/portees.json \
		--chiens=/fixtures/chiens.json \
		--resultats=/fixtures/resultats.json \
		|| log "AVERTISSEMENT : l'import des fixtures via mtb-core a échoué (voir sortie ci-dessus)."
else
	# mtb-core enregistre bien ses types de contenu (portée, chien, résultat) depuis le lot 2 —
	# seule la commande WP-CLI d'import reste à livrer (dette technique #29 du board, décrite au
	# caractère près dans docs/contracts/issue-1.md §"includes/migration/import-fixtures/").
	log "aucune commande « wp mtb import-fixtures » disponible — mtb-core n'a pas encore livré cette commande (dette #29). Étape ignorée ; les fichiers docker/fixtures/*.json sont prêts et attendent cette commande. Relancer le provisionnement (make provision) une fois livrée."
fi

log "photo de test portrait (vérification du cadrage vertical, MASTER.md §6.2)…"
# En attendant "wp mtb import-fixtures", cette image synthétique est déposée directement dans
# la médiathèque : elle donne un attachement portrait réel à assigner à la main (fiche
# d'information, photo d'une fiche chien) tant que l'import automatisé n'existe pas.
if [ -z "$($WP post list --post_type=attachment --name=portee-demo-portrait-test --field=ID)" ]; then
	$WP media import /fixtures/photos/portee-demo-portrait-test.png \
		--title="portee-demo-portrait-test" \
		--alt="Image de test synthétique, cadrage portrait — jamais une vraie photo d'élevage" >/dev/null \
		&& log "photo de test portrait importée dans la médiathèque." \
		|| log "AVERTISSEMENT : import de la photo de test portrait impossible (voir sortie ci-dessus)."
else
	log "photo de test portrait déjà présente dans la médiathèque."
fi

log "nettoyage du contenu par défaut de WordPress (lien codé en dur vers l'ancien port)…"
# "wp core install" imprime l'URL d'administration en dur dans le contenu de la page par
# défaut ("Sample Page"). Si le port a changé depuis (ex. 8080 -> 3005, cas vécu sur cette
# stack), ce lien pointe vers un port mort et devient la seule URL du site qui ne se corrige
# pas toute seule au redémarrage. Idempotent : aucune correspondance après la première passe,
# aucune erreur.
$WP search-replace 'http://localhost:8080/wp-admin/' '/wp-admin/' wp_posts --precise >/dev/null 2>&1 || true

# La chaîne "[provision] terminé." est le signal de fin de provisionnement attendu par les
# scripts/agents qui patientent sur les logs (ex. docker-mtb) — elle doit toujours apparaître
# telle quelle, y compris quand une erreur non bloquante a été détectée plus haut : sinon plus
# rien n'attend jamais la fin du provisionnement. L'alerte T39 est répétée juste après, dans
# une ligne distincte, pour rester visible sans casser ce contrat.
log "terminé."
if [ "${site_non_francophone:-0}" -eq 1 ]; then
	log "ERREUR : rappel — le site n'est PAS en fr_FR (voir « ERREUR » ci-dessus). Relancer 'make provision' avec un accès réseau."
fi

# Le conteneur reste en vie pour que WP-CLI reste disponible en cas d'exécution ponctuelle
# (make wp, make shell) et pour que le healthcheck du service reflète l'état du provisionnement.
tail -f /dev/null
