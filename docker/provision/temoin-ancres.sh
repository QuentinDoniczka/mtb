#!/bin/sh
# Témoin des ancres du cœur WordPress — issue #57 (T114), contrat docs/contracts/issue-57.md.
#
# RÔLE. Le cœur WordPress n'est pas versionné dans ce dépôt, et pourtant l'extension, le thème et les
# contrats en citent des numéros de ligne comme preuves. Ce script compare, fichier par fichier,
# l'empreinte md5 du cœur installé à celle que docker/provision/ancres-coeur.txt a relevée, et dit
# quelle version de WordPress est installée. Il remplace le silence d'une montée de version par une
# ligne de journal.
#
# CE QU'IL NE PROUVE PAS. « Fichier identique » n'est pas « rappel de mtb-core vivant » (T115, #58).
# Un fichier du cœur non cité reste invisible, et c'est voulu. Il ne certifie pas chaque citation
# passée : le registre atteste l'état des fichiers à la date de leur relevé.
#
# OÙ IL TOURNE. Appelé par docker/provision/provision.sh, après la sonde « wp db query » et avant
# « terminé. » — donc à chaque démarrage du conteneur wpcli : « make provision », et tout « make up »
# qui crée ou démarre wpcli (un « make up » qui le trouve déjà en marche ne le relance pas). Pile
# Docker de développement seulement : la production ne l'exécute pas. À la demande, depuis la racine
# du dépôt :
#   docker compose exec -T wpcli sh -c 'tr -d "\r" < /provision/temoin-ancres.sh | sh; echo "statut=$?"'
#
# POURQUOI « </dev/null » PARTOUT. provision.sh le lance par « sed … | /bin/sh » : l'entrée standard
# de ce shell EST le texte du script. Toute commande externe qui lirait l'entrée standard avalerait la
# suite du script, en silence. Le registre se lit donc par redirection explicite, et chaque commande
# externe reçoit « </dev/null ».
#
# STATUTS (contrat #57 §3), le plus grave l'emporte : 30 ALERTE · 20 TÉMOIN INOPÉRANT ·
# 10 AVERTISSEMENT · 0 ok. Tout autre statut veut dire « interrompu, aucun bilan fiable ».
# Aucune écriture hors d'un fichier temporaire, aucun réseau, aucune base.
#
# VARIABLES, aucune n'est posée par compose.yaml — elles servent à la preuve :
#   MTB_ANCRES_REGISTRE          registre à lire      (défaut /provision/ancres-coeur.txt)
#   MTB_ANCRES_RACINE            racine du cœur       (défaut /var/www/html)
#   MTB_ANCRES_VERSION_OBSERVEE  version à supposer   (défaut : « wp core version »)
set -u

# Les classes de caractères des motifs « case » ci-dessous doivent valoir octet pour octet, quelle que
# soit la locale du conteneur.
LC_ALL=C
export LC_ALL

REGISTRE="${MTB_ANCRES_REGISTRE:-/provision/ancres-coeur.txt}"
RACINE="${MTB_ANCRES_RACINE:-/var/www/html}"
PORTEE_DE_RECHERCHE='wp-content/plugins/mtb-core wp-content/themes/mtb docs/contracts docs/ETAT.md'

dire() {
	printf '%s\n' "[provision] ANCRES DU CŒUR : $*"
}

anomalies=0
signaler_inoperant() {
	anomalies=$((anomalies + 1))
	dire "TÉMOIN INOPÉRANT — $*"
}

lignes_de_donnees=0
lignes_valides=0
fichiers_verifies=0
alertes=0
a_redater=0

bilan_inoperant() {
	dire "TÉMOIN INOPÉRANT — ${anomalies} anomalie(s) ci-dessus ; ${fichiers_verifies} fichiers vérifiés sur ${lignes_de_donnees} lignes. Ce bilan ne vaut pas un « ok »."
	exit 20
}

if [ ! -f "$REGISTRE" ] || [ ! -r "$REGISTRE" ]; then
	signaler_inoperant "registre introuvable : ${REGISTRE}"
	bilan_inoperant
fi

# Une version vide ou de forme inattendue est « illisible » : la comparer littéralement produirait un
# AVERTISSEMENT sur chaque ligne, c'est-à-dire un bruit qu'on apprend à ignorer (décision 83).
if [ -n "${MTB_ANCRES_VERSION_OBSERVEE+x}" ]; then
	version_observee="$MTB_ANCRES_VERSION_OBSERVEE"
else
	version_observee="$(wp --path="$RACINE" core version </dev/null 2>/dev/null)"
fi

# Une seule définition de la forme ^[0-9]+\.[0-9]+(\.[0-9]+)?$ (contrat #57 §1), pour la version
# installée comme pour celle du registre : deux copies du motif pourraient diverger sans bruit.
forme_de_version() {
	case "$1" in
		'' | *[!0-9.]* | .* | *. | *..* | *.*.*.* ) return 1 ;;
		*.* ) return 0 ;;
		* ) return 1 ;;
	esac
}

if forme_de_version "$version_observee"; then
	version_lisible=1
else
	version_lisible=0
fi
if [ "$version_lisible" -eq 0 ]; then
	signaler_inoperant "version installée illisible"
	version_affichee="(version illisible)"
else
	version_affichee="$version_observee"
fi

if command -v md5sum </dev/null >/dev/null 2>&1; then
	outil=md5sum
elif command -v php </dev/null >/dev/null 2>&1; then
	outil=php
else
	outil=''
	signaler_inoperant "ni md5sum ni php disponible"
fi

empreinte_de() {
	case "$outil" in
		md5sum )
			sortie="$(md5sum "$1" </dev/null 2>/dev/null)"
			printf '%s' "${sortie%% *}"
			;;
		php )
			php -r 'echo md5_file($argv[1]);' -- "$1" </dev/null 2>/dev/null
			;;
	esac
}

# Copie sans « \r » : le registre arrive en CRLF sur un checkout Windows, et un « \r » collé au chemin
# ferait déclarer disparu un fichier présent. Sans fichier temporaire, aucun bilan n'est possible : on
# sort sur un statut hors barème, que provision.sh rapporte comme une interruption.
copie="$(mktemp </dev/null 2>/dev/null)" || copie=''
if [ -z "$copie" ]; then
	printf '%s\n' "[provision] ANCRES DU CŒUR : le témoin s'interrompt, aucun fichier temporaire n'a pu être créé." >&2
	exit 1
fi
trap 'rm -f "$copie" </dev/null' EXIT
trap 'exit 1' HUP INT TERM
tr -d '\r' < "$REGISTRE" > "$copie"

# Les chemins déjà vus, encadrés de sauts de ligne pour qu'un chemin ne se confonde jamais avec la fin
# d'un autre.
chemins_vus='
'
blancs=" $(printf '\t')"
numero=0
while IFS= read -r ligne || [ -n "$ligne" ]; do
	numero=$((numero + 1))
	# Blancs de tête retirés, pour qu'une ligne de blancs ou un commentaire indenté soient ignorés comme
	# les autres.
	tete="${ligne%%[!${blancs}]*}"
	case "${ligne#"$tete"}" in
		'' | '#'* ) continue ;;
	esac
	lignes_de_donnees=$((lignes_de_donnees + 1))

	set -f
	# shellcheck disable=SC2086 # le découpage en champs est ici le but.
	set -- $ligne
	set +f
	forme_valide=1
	if [ "$#" -ne 4 ]; then
		forme_valide=0
	else
		version_relevee="$1"
		date_relevee="$2"
		empreinte_relevee="$3"
		chemin="$4"
		forme_de_version "$version_relevee" || forme_valide=0
		case "$date_relevee" in
			[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9] ) ;;
			* ) forme_valide=0 ;;
		esac
		case "$empreinte_relevee" in
			*[!0-9a-f]* ) forme_valide=0 ;;
		esac
		[ "${#empreinte_relevee}" -eq 32 ] || forme_valide=0
		case "$chemin" in
			/* | *..* ) forme_valide=0 ;;
		esac
	fi
	if [ "$forme_valide" -eq 0 ]; then
		signaler_inoperant "ligne ${numero} mal formée : ${ligne}"
		continue
	fi

	case "$chemins_vus" in
		*"
${chemin}
"* )
			signaler_inoperant "chemin en double : ${chemin}"
			continue
			;;
	esac
	chemins_vus="${chemins_vus}${chemin}
"
	lignes_valides=$((lignes_valides + 1))

	[ -n "$outil" ] || continue

	fichier="${RACINE}/${chemin}"
	nom="${chemin##*/}"
	recherche="git grep -n -F -e '${nom}' -- ${PORTEE_DE_RECHERCHE}"
	fichiers_verifies=$((fichiers_verifies + 1))

	if [ ! -f "$fichier" ] || [ ! -r "$fichier" ]; then
		alertes=$((alertes + 1))
		dire "ALERTE — fichier cité disparu : ${chemin} (relevé ${version_relevee} du ${date_relevee}, WordPress ${version_affichee} installé). Toute citation de ce fichier est à reprendre. Citations, à la racine du dépôt : ${recherche}"
		continue
	fi

	# Une empreinte illisible compte comme un changement : on ne peut pas attester l'égalité, et le
	# geste demandé — relire les citations — est le seul qui ne ment pas.
	if [ "$(empreinte_de "$fichier")" != "$empreinte_relevee" ]; then
		alertes=$((alertes + 1))
		dire "ALERTE — ${chemin} a changé depuis le relevé ${version_relevee} du ${date_relevee} (WordPress ${version_affichee} installé). Les numéros de ligne qui le citent ne sont plus garantis : les relire tous, y compris les « même fichier, l. N », avant de changer son empreinte. Citations, à la racine du dépôt : ${recherche}"
		continue
	fi

	if [ "$version_lisible" -eq 1 ] && [ "$version_relevee" != "$version_observee" ]; then
		a_redater=$((a_redater + 1))
	fi
done < "$copie"

if [ "$lignes_de_donnees" -eq 0 ]; then
	signaler_inoperant "registre sans aucune ligne"
fi

if [ "$alertes" -gt 0 ]; then
	complement=''
	if [ "$a_redater" -gt 0 ]; then
		complement="${complement} ${a_redater} autres fichiers inchangés sont à re-dater."
	fi
	if [ "$anomalies" -gt 0 ]; then
		complement="${complement} ${anomalies} anomalie(s) du registre."
	fi
	dire "ALERTE — ${alertes} fichier(s) cité(s) changé(s) ou disparu(s) sur ${lignes_valides} (WordPress ${version_affichee} installé) ; voir les lignes « ALERTE » ci-dessus. Rien n'est cassé sur le site.${complement}"
	exit 30
fi

if [ "$anomalies" -gt 0 ] || [ "$lignes_valides" -eq 0 ]; then
	bilan_inoperant
fi

if [ "$a_redater" -gt 0 ]; then
	dire "AVERTISSEMENT — WordPress ${version_affichee} installé ; ${a_redater} fichiers datés d'une autre version dans le registre sont identiques au relevé, leurs numéros de ligne restent justes. Re-dater ces lignes de docker/provision/ancres-coeur.txt (version et date seulement, jamais l'empreinte)."
	exit 10
fi

dire "ok — ${lignes_valides} fichiers du cœur cités avec un numéro de ligne, tous identiques au relevé ; WordPress ${version_affichee} installé."
exit 0
