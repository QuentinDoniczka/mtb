#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Comparateur hors ligne de la reprise « résultats de travail + pages libres » (issue #21).

POURQUOI UN FICHIER PYTHON DANS UNE EXTENSION WORDPRESS — NE PAS « RANGER » EN PHP
=================================================================================

Ce fichier est une anomalie assumée, et la raison est écrite ici pour qu'aucune passe de
refactorisation ne le réécrive en PHP : réécrit en PHP, il deviendrait inexécutable.

Trois faits mesurés, pas supposés (dette T-#21-b du contrat `docs/contracts/issue-21.md`) :

  1. il n'y a pas de binaire `php` sur la machine de développement — le PHP du projet ne vit que
     dans les conteneurs ;
  2. `docs/` n'est monté dans AUCUN conteneur (`compose.yaml:85-89` ne monte que
     `wp-content/themes/mtb`, `wp-content/plugins/mtb-core`, `docker/provision` et
     `docker/fixtures`) : un comparateur exécuté dans le conteneur ne verrait donc jamais le HTML
     archivé, qui est sa seule source de vérité ;
  3. la décision 34 du projet interdit `docker compose run` sur le service `wpcli`.

Un comparateur en PHP n'aurait donc aucun endroit où tourner. Celui-ci tourne sur l'hôte, en
Python 3 de la bibliothèque standard, sans réseau, sans base de données, sans dépendance.

CE QU'IL FAIT, ET CE QU'IL NE FAIT PAS
======================================

Il **ne parse jamais la capture pour en tirer des champs** : le parsing est un excellent
vérificateur et un mauvais scribe. Il confronte trois maillons, et l'enchaînement est ce qui rend
la preuve non circulaire :

    champs transcrits  ->  source.ligne déclarée  ->  re-dérivation du HTML archivé

Il n'écrit rien, ne corrige rien, ne touche à aucun fichier. Une divergence est un fait à
rapporter.

    python concordance.py                 # rapport complet
    python concordance.py --silencieux    # seulement les échecs et la conclusion

Sortie 0 s'il n'existe aucune divergence inexpliquée, 1 sinon. **Sur des fichiers de données vides
ou absents, il sort en 1** : c'est sa recette d'acceptation, et la raison pour laquelle il a été
écrit avant la première ligne de donnée (contrat §14-1).
"""

from __future__ import annotations

import difflib
import hashlib
import json
import os
import re
import subprocess
import sys

# Aucun fichier compilé n'est déposé à côté des sources. Sans cette ligne, l'import de
# `verifier_concordance` créerait un « __pycache__/ » dans `docs/migration/source/outils/`, hors de
# l'empreinte de cette issue, dans un dossier d'archive qui doit rester exactement ce qu'il est.
sys.dont_write_bytecode = True

# ---------------------------------------------------------------------------------------------
# Périmètre gelé par le contrat. Ces nombres ne décrivent pas ce qui existe : ils décrivent ce que
# le contrat exige. C'est très exactement ce qui fait échouer le comparateur sur un fichier vide.
# ---------------------------------------------------------------------------------------------

# Contrat §2 : 57 lignes de tableau + 4 lignes « Autres disciplines ».
TOTAL_RESULTATS = 61

# Contrat §2, recompté trois fois de façon indépendante.
REPARTITION = {
    "ring": 22,
    "igp_rci": 4,
    "mondioring": 4,
    "obeissance": 19,
    "pistage": 3,
    "recherche_utilitaire": 4,
    "sauvetage": 1,
    "autres_disciplines": 4,
}

# Contrat §2 et §5 : les sept pages, leur statut attendu et le fichier HTML archivé qui fait foi.
PAGES = [
    ("bhpl", "publish", "bhpl.html"),
    ("bhpl-en-france", "draft", "bhpl-en-france.html"),
    ("litterature", "publish", "litterature.html"),
    ("travail", "publish", "travail.html"),
    ("placement", "publish", "placement.html"),
    ("mentions-legales", "publish", "mentions-legales.html"),
    ("politique-de-confidentialite", "draft", "politique-de-confidentialite.html"),
]

# La seule zone du gabarit IONOS qui porte du contenu rédactionnel (contrat §6).
ZONE_ATTENDUE = "Contenu principal"

# ---------------------------------------------------------------------------------------------
# Transformations déclarées. Chacune est comptée ; une transformation qui se déclenche zéro fois
# est SIGNALÉE — une transformation morte est le signe que la source a changé (contrat §6).
#
# Ces tables sont écrites ici, et c'est un écart assumé à la règle « aucune liste fermée
# recopiée » : cette règle vise l'importeur PHP, qui lit `mtb_resultat_disciplines()` et
# `mtb_resultat_sexes()` vivantes. Le comparateur, lui, tourne hors de WordPress et n'a aucun
# moyen de les lire. Il les DÉCLARE donc, et le contrôle « une transformation qui ne se déclenche
# jamais est signalée » est ce qui rend la divergence visible si la liste vivante bouge.
# ---------------------------------------------------------------------------------------------

# U+2642 et U+2640, les deux glyphes imprimés par la source.
SEXES_SOURCE = {"♂": "male", "♀": "femelle"}

# En-tête de tableau imprimé par la source => clé de discipline attendue au fichier de données.
# « Recherche Utilitaire » -> « recherche_utilitaire » est l'écart §9-1 ; « IGP (RCI) » ->
# « igp_rci » est l'écart §9-2 (décision 11).
DISCIPLINES_SOURCE = {
    "RING": "ring",
    "IGP (RCI)": "igp_rci",
    "Mondioring": "mondioring",
    "Obéissance": "obeissance",
    "Sauvetage": "sauvetage",
    "Pistage": "pistage",
    "Recherche Utilitaire": "recherche_utilitaire",
}

# La ligne d'intertitre qui ouvre les quatre lignes hors tableau.
INTERTITRE_AUTRES = "Autres disciplines"

# Contrat §6 contrôle 3 : les DEUX SEULS niveaux finissant par une parenthèse, nommés. Un
# troisième ferait échouer le comparateur, parce qu'il rouvrirait l'arbitrage A4 (« pays » reste
# vide sur les 61), qui a été tranché sur le fait qu'il n'y en a que deux.
NIVEAUX_PARENTHESES = {
    "IGP 3 (Finland)",
    "Brevet Maitre Chien Drogue (Suisse)",
}

# Contrat §6 contrôle 2 : les séparateurs déclarés et clos, par famille de ligne. Tout caractère
# non consommé par un champ transcrit doit appartenir à cet ensemble. C'est ce qui rend
# mécaniquement impossible de verser « Ferrari » dans Conducteur : le reliquat « Prop. » ne serait
# ni consommé ni déclaré.
SEPARATEURS = {
    "tableau": set(" |\u00a0"),
    "autres": set(" -:\u00a0"),
}

# Nom des transformations déclarées, pour le compteur.
T_BORD_ROGNE = "A3 — blanc de bord rogné (ASCII ou U+00A0)"
T_LIEN_SOURCE = "§9-11 — balisage [LIEN …] d'une cellule, non repris"
T_DISCIPLINE = "§9-1 / §9-2 — en-tête de tableau converti en clé de discipline"
T_SEXE = "§9-3 — glyphe de sexe converti en clé de sexe"

# `reduire.py` rend ces deux marqueurs à la place d'un bloc de texte (voir son README).
ZONE_VIDE = "<ZONE VIDE>"
ZONE_ABSENTE = "<ZONE ABSENTE>"


class Rapport:
    """Compteurs, lignes de rapport et code de sortie. Aucun état hors de cet objet."""

    def __init__(self, silencieux=False):
        self.echecs = 0
        self.silencieux = silencieux
        self.transformations = {}

    def dire(self, ligne=""):
        if not self.silencieux:
            print(ligne)

    def titre(self, ligne):
        self.dire()
        self.dire("=" * 78)
        self.dire(ligne)
        self.dire("=" * 78)

    def ok(self, ligne):
        self.dire("  [ok] %s" % ligne)

    def note(self, ligne):
        self.dire("  [note] %s" % ligne)

    def echec(self, ligne):
        self.echecs += 1
        print("  [ECHEC] %s" % ligne)

    def transformation(self, nom, combien=1):
        self.transformations[nom] = self.transformations.get(nom, 0) + combien

    def declarer_transformation(self, nom):
        """Enregistre une transformation attendue, même si elle ne se déclenche jamais."""
        self.transformations.setdefault(nom, 0)


def visible(texte):
    """Rend les U+00A0 lisibles dans un message : elles sont invisibles à la relecture."""
    return texte.replace("\u00a0", "<U+00A0>")


# ---------------------------------------------------------------------------------------------
# Localisation des fichiers
# ---------------------------------------------------------------------------------------------

ICI = os.path.dirname(os.path.abspath(__file__))
MODULE = os.path.dirname(ICI)


def racine_du_depot():
    """Remonte jusqu'au dossier qui porte `docs/migration/source/outils/reduire.py`."""
    dossier = MODULE
    for _ in range(12):
        candidat = os.path.join(dossier, "docs", "migration", "source", "outils", "reduire.py")
        if os.path.isfile(candidat):
            return dossier
        parent = os.path.dirname(dossier)
        if parent == dossier:
            break
        dossier = parent
    return ""


def importer_zones(dossier_outils):
    """Importe `zones()` de `verifier_concordance.py` au lieu de le recopier (contrat §6).

    Recopier ce découpage en ferait une seconde définition de « ce qu'est une zone », qui
    dériverait de la première sans que personne ne le voie.
    """
    if dossier_outils not in sys.path:
        sys.path.insert(0, dossier_outils)

    import verifier_concordance

    return verifier_concordance.zones


def reduire(outil, chemin_html, options=()):
    """Re-dérive le texte d'un HTML archivé en appelant `reduire.py`.

    PYTHONIOENCODING est posé dans l'environnement du processus enfant : sans lui, Python retombe
    sur cp1252 sous Windows et plante en UnicodeEncodeError DÈS QUE la sortie n'est pas un
    terminal — c'est-à-dire ici, où c'est un tuyau.
    """
    environnement = dict(os.environ)
    environnement["PYTHONIOENCODING"] = "utf-8"

    execution = subprocess.run(
        [sys.executable, outil, chemin_html] + list(options),
        capture_output=True,
        env=environnement,
    )

    if execution.returncode != 0:
        return None, execution.stderr.decode("utf-8", "replace")

    return execution.stdout.decode("utf-8"), ""


def sha_stable(chemin):
    """SHA-256 « stable » d'un HTML archivé, au sens de `html/RELEVE.md`.

    Deux normalisations, et pas une de plus :

      1. l'époque Unix qu'IONOS embarque dans les URL de `all.css` / `all.js` est remplacée par
         `EPOCH` — deux requêtes donnent sinon deux condensés différents pour le même document ;
      2. les CRLF sont ramenés à des LF. Le dépôt est en `core.autocrlf=true` sans
         `.gitattributes` (mesuré) : sous Windows, `placement.html` pèse 31 701 octets sur le
         disque et 31 205 dans git. Sans cette normalisation, aucun condensé ne correspondrait au
         relevé sous Windows et tous correspondraient sous Linux — un contrôle qui ne dit pas la
         même chose selon la machine ne contrôle rien.
    """
    with open(chemin, "rb") as fichier:
        octets = fichier.read()

    octets = octets.replace(b"\r\n", b"\n")
    octets = re.sub(rb"(929224983(&amp;|&)t=)[0-9]{10}", rb"\1EPOCH", octets)

    return hashlib.sha256(octets).hexdigest()


def lire_releve(chemin):
    """Relevé de capture : fichier => {octets, sha_brut, sha_stable, insecables}."""
    mesures = {}

    if not os.path.isfile(chemin):
        return mesures

    with open(chemin, encoding="utf-8") as fichier:
        for ligne in fichier:
            colonnes = [c.strip().strip("`") for c in ligne.strip().strip("|").split("|")]
            if len(colonnes) < 8 or not colonnes[1].endswith(".html"):
                continue
            try:
                mesures[colonnes[1]] = {
                    "octets": int(colonnes[3].replace(" ", "").replace("\u00a0", "")),
                    "sha_brut": colonnes[4],
                    "sha_stable": colonnes[5],
                    "insecables": int(colonnes[7].replace(" ", "").replace("\u00a0", "")),
                }
            except ValueError:
                continue

    return mesures


def charger_json(chemin, rapport, quoi):
    """Lit un fichier de données. Absent, illisible ou vide => ÉCHEC, jamais un silence."""
    if not os.path.isfile(chemin):
        rapport.echec(
            "%s : fichier absent (%s). Rien n'a été transcrit — la reprise n'existe pas."
            % (quoi, chemin)
        )
        return None

    with open(chemin, "rb") as fichier:
        octets = fichier.read()

    if octets.strip() == b"":
        rapport.echec("%s : fichier vide (%s)." % (quoi, chemin))
        return None

    try:
        return json.loads(octets.decode("utf-8"))
    except (ValueError, UnicodeDecodeError) as erreur:
        rapport.echec("%s : JSON illisible (%s) — %s." % (quoi, chemin, erreur))
        return None


# ---------------------------------------------------------------------------------------------
# Contrôle 1 — provenance des pages
# ---------------------------------------------------------------------------------------------


def sources_de_la_composition(composition, slug, rapport, zone_porte_du_texte):
    """Concatène, dans l'ordre, les `source` de toutes les entrées — blocs ET écarts.

    UNE AMBIGUÏTÉ LATENTE, RENDUE BRUYANTE PLUTÔT QUE RÉSOLUE EN SILENCE

    Une `source` portée par une CHAÎNE ne sait pas distinguer deux choses :

      - « je ne réclame aucune ligne de la capture » — le cas de tout bloc CALCULÉ
        (`bandeau-ouverture`, `tableau-resultats`, `liste-portees`, `encart-appel`,
        `coordonnees-plan`, et les fiches de charpente), qui ne recopie rien ;
      - « je réclame une ligne, et cette ligne est vide » — une ligne blanche de la capture.

    Les deux s'écrivent `""`. Concaténer sans distinction fabrique une ligne vide parasite pour
    chaque bloc calculé, et fait échouer l'égalité stricte sur une transcription pourtant juste.

    La forme chaîne est CONSERVÉE — réécrire neuf fichiers de données déjà vérifiés pour lever une
    ambiguïté latente risquerait d'introduire une vraie erreur de transcription là où il n'y a
    qu'une imprécision de forme. La première lecture est donc retenue : une source vide ne réclame
    rien.

    En contrepartie, la seconde lecture ne devient pas un silence : une entrée qui recopie
    réellement de la capture — un écart, ou un bloc porteur de prose — et dont la source est vide
    fait ÉCHOUER le comparateur, en se nommant. Le jour où une entrée aura légitimement besoin de
    réclamer une seule ligne vide, elle rencontrera une erreur explicite plutôt qu'un contrôle qui
    la valide à tort.

    Le garde ne s'applique QUE si la zone re-dérivée porte du texte. Quand la source ne publie rien
    — zone absente d'un 302 au corps vide, zone présente et sans aucun texte — un écart qui déclare
    cette absence ne peut réclamer aucune ligne : il n'en existe pas. Sa source vide est alors la
    seule écriture possible, et non une imprécision.
    """
    morceaux = []

    for rang, entree in enumerate(composition):
        if not isinstance(entree, dict):
            return None

        source = entree.get("source")

        if source is None:
            source = ""

        if not isinstance(source, str):
            return None

        if source == "" and zone_porte_du_texte and recopie_de_la_capture(entree):
            rapport.echec(
                "%s : composition [%d] recopie de la capture (écart ou prose) mais sa « source » "
                "est vide. Une source vide est lue « cette entrée ne réclame aucune ligne » ; elle "
                "ne peut donc pas servir à réclamer une ligne blanche." % (slug, rang)
            )

        if source != "":
            morceaux.append(source)

    return "\n".join(morceaux)


def recopie_de_la_capture(entree):
    """L'entrée recopie-t-elle des lignes de la capture, plutôt que d'être un bloc calculé ?"""
    if "ecart" in entree:
        return True

    paragraphes = entree.get("paragraphes")

    return isinstance(paragraphes, list) and len(paragraphes) > 0


def controle_1(rapport, pages_lues, zones_par_slug):
    """La concaténation des `source` doit être STRICTEMENT égale à la zone re-dérivée.

    Cette seule égalité prouve d'un coup : rien perdu, rien ajouté, ordre préservé. Une ligne que
    personne ne réclame la casse — il n'existe donc aucune façon d'omettre une ligne sans écrire
    pourquoi.
    """
    rapport.titre("Contrôle 1 — provenance des pages (égalité stricte à la zone re-dérivée)")

    for slug, _statut, fichier_html in PAGES:
        page = pages_lues.get(slug)

        if page is None:
            continue

        zone = zones_par_slug.get(slug)

        if zone is None:
            rapport.echec("%s : la zone « %s » n'a pas pu être re-dérivée." % (slug, ZONE_ATTENDUE))
            continue

        composition = page.get("composition")

        if composition is None:
            composition = []

        if not isinstance(composition, list):
            rapport.echec("%s : « composition » n'est pas une liste." % slug)
            continue

        reclame = sources_de_la_composition(
            composition, slug, rapport, zone not in (ZONE_VIDE, ZONE_ABSENTE)
        )

        if reclame is None:
            rapport.echec(
                "%s : une entrée de « composition » n'a pas de « source » en chaîne." % slug
            )
            continue

        if zone in (ZONE_VIDE, ZONE_ABSENTE):
            # La source ne publie rien : la composition ne peut rien réclamer.
            if reclame.strip() != "":
                rapport.echec(
                    "%s : la zone re-dérivée est %s, mais la composition réclame des lignes."
                    % (slug, zone)
                )
            else:
                rapport.ok(
                    "%s : zone %s, composition sans ligne réclamée — cohérent." % (slug, zone)
                )
            continue

        if reclame == zone:
            rapport.ok(
                "%s : %d lignes réclamées, identiques au caractère près à la zone re-dérivée (%s)."
                % (slug, len(zone.split("\n")), fichier_html)
            )
            continue

        rapport.echec("%s : la composition ne réclame pas exactement la zone re-dérivée." % slug)

        for ligne in difflib.unified_diff(
            zone.split("\n"),
            reclame.split("\n"),
            "zone re-dérivée",
            "composition",
            lineterm="",
            n=1,
        ):
            print("        " + visible(ligne))


# ---------------------------------------------------------------------------------------------
# Contrôle 1b / 1c — provenance et rattachement des 61 résultats
# ---------------------------------------------------------------------------------------------


def entete_de_tableau(ligne):
    """Rend le nom de discipline d'une ligne d'en-tête de tableau, ou None."""
    trouve = re.match(r"^\s*\|\s*(.+?)\s*\|\s*Niveau\s*$", ligne)

    return trouve.group(1) if trouve else None


def controle_1b(rapport, resultats, lignes_travail):
    """Chaque `source.ligne` existe dans la zone re-dérivée, et l'ordre du fichier est celui de la
    source.

    L'ordre n'est pas cosmétique : `interne.php:503` départage deux lignes de même année par
    identifiant de contenu, donc par ordre de création, et l'importeur crée dans l'ordre du
    fichier.
    """
    rapport.titre("Contrôle 1b — chaque ligne transcrite vient du HTML archivé, dans l'ordre")

    curseur = 0
    positions = []

    for rang, entree in enumerate(resultats):
        source = entree.get("source") if isinstance(entree, dict) else None

        if not isinstance(source, dict) or not isinstance(source.get("ligne"), str):
            rapport.echec("résultat [%d] : « source.ligne » absente ou non textuelle." % rang)
            positions.append(None)
            continue

        ligne = source["ligne"]

        try:
            position = lignes_travail.index(ligne, curseur)
        except ValueError:
            if ligne in lignes_travail:
                rapport.echec(
                    "résultat [%d] : la ligne « %s » existe dans la source mais avant la ligne du "
                    "résultat précédent — l'ordre du fichier n'est plus l'ordre de la source."
                    % (rang, visible(ligne))
                )
            else:
                rapport.echec(
                    "résultat [%d] : la ligne « %s » ne figure pas dans la zone re-dérivée."
                    % (rang, visible(ligne))
                )
            positions.append(None)
            continue

        curseur = position + 1
        positions.append(position)

    trouvees = len([p for p in positions if p is not None])

    if trouvees == len(resultats) and trouvees > 0:
        rapport.ok("%d lignes retrouvées dans la zone re-dérivée, dans l'ordre." % trouvees)

    return positions


def controle_1c(rapport, resultats, lignes_travail, positions):
    """La discipline déclarée est celle de l'en-tête qui gouverne la ligne dans la source.

    Sans ce contrôle, `discipline` serait une affirmation libre du transcripteur : une ligne de
    RING pourrait être rangée en Pistage sans qu'aucun contrôle ne bronche.
    """
    rapport.titre(
        "Contrôle 1c — la discipline déclarée est celle de l'en-tête qui précède la ligne"
    )

    rapport.declarer_transformation(T_DISCIPLINE)

    avant = rapport.echecs

    for rang, entree in enumerate(resultats):
        position = positions[rang] if rang < len(positions) else None

        if position is None or not isinstance(entree, dict):
            continue

        source = entree.get("source", {})
        famille = source.get("famille") if isinstance(source, dict) else None
        declaree = source.get("discipline_source") if isinstance(source, dict) else None
        discipline = entree.get("discipline")

        gouvernante = None
        sous_autres = False

        for index in range(position - 1, -1, -1):
            nom = entete_de_tableau(lignes_travail[index])
            if nom is not None:
                gouvernante = nom
                break
            if lignes_travail[index].strip().rstrip(":").strip() == INTERTITRE_AUTRES:
                sous_autres = True
                break

        if famille == "autres":
            if not sous_autres:
                rapport.echec(
                    "résultat [%d] : famille « autres » déclarée, mais la ligne n'est pas sous "
                    "l'intertitre « %s » dans la source." % (rang, INTERTITRE_AUTRES)
                )
                continue
            if declaree != INTERTITRE_AUTRES:
                rapport.echec(
                    "résultat [%d] : « discipline_source » vaut « %s », attendu « %s »."
                    % (rang, declaree, INTERTITRE_AUTRES)
                )
                continue
            if discipline != "autres_disciplines":
                rapport.echec(
                    "résultat [%d] : discipline « %s » sous l'intertitre « %s »."
                    % (rang, discipline, INTERTITRE_AUTRES)
                )
                continue
            rapport.transformation(T_DISCIPLINE)
            continue

        if famille != "tableau":
            rapport.echec(
                "résultat [%d] : « source.famille » vaut « %s » ; seules « tableau » et « autres » "
                "existent." % (rang, famille)
            )
            continue

        if gouvernante is None:
            rapport.echec(
                "résultat [%d] : aucune ligne d'en-tête de tableau ne précède la ligne dans la "
                "source." % rang
            )
            continue

        if declaree != gouvernante:
            rapport.echec(
                "résultat [%d] : « discipline_source » vaut « %s », mais l'en-tête qui gouverne la "
                "ligne dans la source est « %s »." % (rang, declaree, gouvernante)
            )
            continue

        attendue = DISCIPLINES_SOURCE.get(gouvernante)

        if attendue is None:
            rapport.echec(
                "résultat [%d] : l'en-tête « %s » n'est déclaré par aucune transformation. Un "
                "huitième tableau est apparu dans la source." % (rang, gouvernante)
            )
            continue

        if discipline != attendue:
            rapport.echec(
                "résultat [%d] : discipline « %s » déclarée sous l'en-tête « %s », qui donne "
                "« %s »." % (rang, discipline, gouvernante, attendue)
            )
            continue

        rapport.transformation(T_DISCIPLINE)

    if rapport.echecs == avant and resultats:
        rapport.ok(
            "%d lignes rangées sous la discipline que leur en-tête de source impose."
            % len(resultats)
        )


# ---------------------------------------------------------------------------------------------
# Contrôle 2 — consommation de caractères
# ---------------------------------------------------------------------------------------------


def consommer(ligne, valeur, curseur, consomme):
    """Marque la première occurrence de `valeur` à partir de `curseur`. Rend le nouveau curseur."""
    if valeur == "":
        return curseur, True

    position = ligne.find(valeur, curseur)

    if position < 0:
        return curseur, False

    for index in range(position, position + len(valeur)):
        consomme[index] = True

    return position + len(valeur), True


# Délimiteur de champ déclaré, par famille de ligne : c'est le caractère qui, dans la source,
# sépare deux cellules d'un même enregistrement.
DELIMITEUR = {"tableau": "|", "autres": ":"}


def controler_les_frontieres(ligne, famille, etendues):
    """Vérifie que deux champs voisins sont bien séparés par le délimiteur déclaré de la famille.

    La consommation de caractères, à elle seule, ne voit pas une frontière déplacée : verser
    « Dixie du Mont Brabant : Qual. Chien de sauvetage » entier dans le nom du chien ne perd aucun
    caractère et ne fabrique aucun reliquat. C'est ce contrôle-ci qui l'attrape : le délimiteur
    « : » se retrouverait alors À L'INTÉRIEUR du nom, et non entre le nom et le niveau.

    Rend une phrase de reproche, ou None si la ligne est conforme.
    """
    delimiteur = DELIMITEUR.get(famille)

    if delimiteur is None or "chien_nom" not in etendues or "niveau" not in etendues:
        return None

    for nom in ("chien_nom", "niveau"):
        debut, fin = etendues[nom]
        if delimiteur in ligne[debut:fin]:
            return (
                "le champ « %s » contient le délimiteur « %s » de la famille « %s » : la frontière "
                "entre deux champs a été déplacée." % (nom, delimiteur, famille)
            )

    entre = ligne[etendues["chien_nom"][1] : etendues["niveau"][0]]

    if delimiteur not in entre:
        return (
            "aucun délimiteur « %s » entre le nom du chien et le niveau : ces deux champs ne "
            "viennent pas de deux cellules distinctes." % delimiteur
        )

    return None


def controle_2(rapport, resultats):
    """Tout caractère de la ligne est consommé par un champ transcrit, ou est un séparateur déclaré.

    C'est le contrôle qui rend mécaniquement impossible de verser « Ferrari » dans Conducteur : le
    reliquat « Prop. » ne serait ni consommé ni déclaré.
    """
    rapport.titre("Contrôle 2 — consommation de caractères (aucun reliquat non déclaré)")

    rapport.declarer_transformation(T_BORD_ROGNE)
    rapport.declarer_transformation(T_LIEN_SOURCE)
    rapport.declarer_transformation(T_SEXE)

    conformes = 0
    bords = []

    for rang, entree in enumerate(resultats):
        if not isinstance(entree, dict):
            rapport.echec("résultat [%d] : l'entrée n'est pas un objet JSON." % rang)
            continue

        source = entree.get("source")

        if not isinstance(source, dict) or not isinstance(source.get("ligne"), str):
            continue

        ligne = source["ligne"]
        famille = source.get("famille")
        separateurs = SEPARATEURS.get(famille)

        if separateurs is None:
            continue

        consomme = [False] * len(ligne)

        # §9-11 : le balisage de lien d'une cellule n'est pas une donnée ; il est consommé comme
        # décor de source, et cela se compte — une fois par ligne, pas une fois par balise.
        marques = list(re.finditer(r"\[LIEN href=[^\]]*\]|\[/LIEN\]", ligne))

        for trouve in marques:
            for index in range(trouve.start(), trouve.end()):
                consomme[index] = True

        if marques:
            rapport.transformation(T_LIEN_SOURCE)

        glyphe = ""
        sexe = entree.get("sexe")

        for symbole, cle in SEXES_SOURCE.items():
            if sexe == cle:
                glyphe = symbole
                rapport.transformation(T_SEXE)
                break

        if sexe not in ("", None) and glyphe == "":
            rapport.echec(
                "résultat [%d] : sexe « %s » — aucun glyphe de la source ne lui correspond."
                % (rang, sexe)
            )
            continue

        sequence = [
            ("annee", "" if entree.get("annee") in (None, "") else str(entree.get("annee"))),
            ("sexe", glyphe),
            ("chien_nom", entree.get("chien_nom") or ""),
            ("niveau", entree.get("niveau") or ""),
            ("conducteur", entree.get("conducteur") or ""),
            ("pays", entree.get("pays") or ""),
        ]

        curseur = 0
        introuvable = None
        etendues = {}

        for nom, valeur in sequence:
            if not isinstance(valeur, str):
                introuvable = nom
                break
            debut = curseur
            curseur, trouve = consommer(ligne, valeur, curseur, consomme)
            if not trouve:
                introuvable = nom
                break
            if valeur != "":
                etendues[nom] = (curseur - len(valeur), curseur)
            else:
                etendues[nom] = (debut, debut)

        if introuvable is not None:
            rapport.echec(
                "résultat [%d] : le champ « %s » ne se retrouve pas, dans cet ordre, dans la ligne "
                "« %s »." % (rang, introuvable, visible(ligne))
            )
            continue

        reliquat = "".join(
            caractere for index, caractere in enumerate(ligne) if not consomme[index]
        )
        hors_declaration = [c for c in reliquat if c not in separateurs]

        if hors_declaration:
            rapport.echec(
                "résultat [%d] : reliquat non consommé et non déclaré « %s » dans la ligne « %s »."
                % (rang, visible("".join(hors_declaration)), visible(ligne))
            )
            continue

        frontiere = controler_les_frontieres(ligne, famille, etendues)

        if frontiere is not None:
            rapport.echec("résultat [%d] : %s Ligne « %s »." % (rang, frontiere, visible(ligne)))
            continue

        conformes += 1

        # A3 : le blanc de bord rogné est une modification de la capture. Elle est comptée et
        # listée exhaustivement, comme le contrat l'exige.
        if ligne != ligne.strip(" \t\u00a0"):
            rapport.transformation(T_BORD_ROGNE)
            bords.append((rang, ligne))

    if conformes:
        rapport.ok("%d lignes sur %d entièrement consommées." % (conformes, len(resultats)))

    if bords:
        rapport.dire()
        rapport.dire("  Lignes dont un bord a été rogné (transformation A3), exhaustivement :")
        for rang, ligne in bords:
            rapport.dire("    [%d] « %s »" % (rang, visible(ligne)))


# ---------------------------------------------------------------------------------------------
# Contrôle 3 — les deux niveaux parenthésés, et « pays » vide sur les 61
# ---------------------------------------------------------------------------------------------


def controle_3(rapport, resultats):
    """Les deux seuls niveaux finissant par une parenthèse sont nommés ; un troisième fait échouer.

    Arbitrage A4 : `pays` reste VIDE sur les 61, et le niveau reste verbatim, parenthèse comprise.
    Renseigner un pays ferait apparaître la colonne Pays sur son tableau, donc donnerait aux
    autres lignes du même tableau une cellule vide — c'est-à-dire l'affirmation « obtenu en
    France », que la source ne fait pas.
    """
    rapport.titre("Contrôle 3 — les deux niveaux parenthésés, et « pays » vide sur les 61")

    trouves = set()

    for rang, entree in enumerate(resultats):
        if not isinstance(entree, dict):
            continue

        niveau = entree.get("niveau") or ""
        pays = entree.get("pays")

        if isinstance(niveau, str) and niveau.rstrip(" \u00a0").endswith(")"):
            trouves.add(niveau.strip(" \u00a0"))

        if pays not in (None, ""):
            rapport.echec(
                "résultat [%d] : « pays » vaut « %s ». L'arbitrage A4 le veut vide sur les 61 : un "
                "pays vide ne signifie pas « inconnu » mais « pas obtenu à l'étranger »."
                % (rang, pays)
            )

    inattendus = sorted(trouves - NIVEAUX_PARENTHESES)
    manquants = sorted(NIVEAUX_PARENTHESES - trouves)

    for niveau in inattendus:
        rapport.echec(
            "niveau parenthésé non déclaré : « %s ». Le contrat n'en nomme que deux ; un troisième "
            "rouvre l'arbitrage A4 et doit être tranché, pas absorbé." % niveau
        )

    for niveau in manquants:
        rapport.echec(
            "niveau parenthésé déclaré au contrat mais absent des données : « %s »." % niveau
        )

    if not inattendus and not manquants:
        rapport.ok(
            "les deux niveaux parenthésés sont présents et sont les seuls : %s."
            % ", ".join("« %s »" % n for n in sorted(NIVEAUX_PARENTHESES))
        )


# ---------------------------------------------------------------------------------------------
# Contrôle 4 — les espaces insécables
# ---------------------------------------------------------------------------------------------


def controle_4(rapport, outil, dossier_html, mesures, zones_par_slug):
    """Les U+00A0 sont comptées par zone et confrontées au relevé de capture."""
    rapport.titre("Contrôle 4 — espaces insécables (U+00A0), comptées et confrontées au relevé")

    for slug, _statut, fichier_html in PAGES:
        chemin = os.path.join(dossier_html, fichier_html)

        if not os.path.isfile(chemin):
            rapport.echec("%s : HTML archivé absent (%s)." % (slug, chemin))
            continue

        sortie, erreur = reduire(outil, chemin, ("--insecables",))

        if sortie is None:
            rapport.echec("%s : `reduire.py --insecables` a échoué — %s." % (slug, erreur.strip()))
            continue

        compte = int(sortie.strip())
        attendu = mesures.get(fichier_html, {}).get("insecables")
        zone = zones_par_slug.get(slug) or ""
        dans_la_zone = 0 if zone in (ZONE_VIDE, ZONE_ABSENTE) else zone.count("\u00a0")

        if attendu is None:
            rapport.echec("%s : aucune ligne de relevé pour %s." % (slug, fichier_html))
            continue

        if compte != attendu:
            rapport.echec(
                "%s : %d U+00A0 dans le corps réduit, %d au relevé de capture. Le HTML archivé a "
                "changé depuis la capture." % (slug, compte, attendu)
            )
            continue

        rapport.ok(
            "%s : %d U+00A0 au corps réduit, conformes au relevé ; %d dans la zone « %s »."
            % (slug, compte, dans_la_zone, ZONE_ATTENDUE)
        )


# ---------------------------------------------------------------------------------------------
# Contrôle 5 — condensés des HTML archivés
# ---------------------------------------------------------------------------------------------


def controle_5(rapport, pages_lues, dossier_html, mesures):
    """Le `sha256` déclaré par une page est celui du HTML archivé qu'elle cite."""
    rapport.titre("Contrôle 5 — le condensé déclaré est celui du HTML archivé")

    for slug, _statut, fichier_html in PAGES:
        page = pages_lues.get(slug)

        if page is None:
            continue

        source = page.get("source")

        if not isinstance(source, dict):
            rapport.echec(
                "%s : « source » absente ou mal formée — une reprise sans provenance est une "
                "affirmation sans preuve." % slug
            )
            continue

        manquantes = [c for c in ("capture", "html", "sha256", "zone") if c not in source]

        if manquantes:
            rapport.echec(
                "%s : « source » incomplète, sous-clés manquantes : %s."
                % (slug, ", ".join("« %s »" % c for c in manquantes))
            )
            continue

        if os.path.basename(str(source["html"])) != fichier_html:
            rapport.echec(
                "%s : « source.html » cite « %s », attendu « %s »."
                % (slug, source["html"], fichier_html)
            )
            continue

        if source["zone"] != ZONE_ATTENDUE:
            rapport.echec(
                "%s : « source.zone » vaut « %s », attendu « %s »."
                % (slug, source["zone"], ZONE_ATTENDUE)
            )
            continue

        chemin = os.path.join(dossier_html, fichier_html)

        if not os.path.isfile(chemin):
            rapport.echec("%s : HTML archivé absent (%s)." % (slug, chemin))
            continue

        calcule = sha_stable(chemin)
        releve = mesures.get(fichier_html, {}).get("sha_stable")

        if releve is not None and calcule != releve:
            rapport.echec(
                "%s : le condensé stable du fichier archivé (%s…) diffère du relevé (%s…)."
                % (slug, calcule[:16], releve[:16])
            )
            continue

        if str(source["sha256"]) != calcule:
            rapport.echec(
                "%s : « source.sha256 » vaut %s…, le condensé stable du HTML archivé vaut %s…."
                % (slug, str(source["sha256"])[:16], calcule[:16])
            )
            continue

        rapport.ok(
            "%s : condensé stable %s… conforme au relevé et à la déclaration." % (slug, calcule[:16])
        )


# ---------------------------------------------------------------------------------------------
# Contrôle 6 — périmètre et correspondances
# ---------------------------------------------------------------------------------------------


def controle_6(rapport, resultats, correspondances):
    """Le périmètre gelé : 61 résultats, leur répartition, et l'unique correspondance de chien."""
    rapport.titre("Contrôle 6 — périmètre du contrat (61 résultats, répartition, correspondances)")

    if len(resultats) != TOTAL_RESULTATS:
        rapport.echec(
            "%d résultats transcrits, %d attendus par le contrat §2 (57 lignes de tableau + 4 "
            "lignes « Autres disciplines »)." % (len(resultats), TOTAL_RESULTATS)
        )
    else:
        rapport.ok("%d résultats transcrits." % TOTAL_RESULTATS)

    comptes = {}

    for entree in resultats:
        if isinstance(entree, dict):
            cle = entree.get("discipline")
            comptes[cle] = comptes.get(cle, 0) + 1

    ecart = False

    for discipline, attendu in REPARTITION.items():
        obtenu = comptes.get(discipline, 0)
        if obtenu != attendu:
            ecart = True
            rapport.echec(
                "discipline « %s » : %d lignes transcrites, %d attendues (contrat §2)."
                % (discipline, obtenu, attendu)
            )

    for discipline in sorted(str(d) for d in set(comptes) - set(REPARTITION)):
        ecart = True
        rapport.echec(
            "discipline « %s » : %d lignes, alors que le contrat §2 n'en prévoit aucune."
            % (discipline, comptes[discipline])
        )

    if not ecart:
        rapport.ok(
            "répartition conforme : %s."
            % ", ".join("%s %d" % (d, a) for d, a in REPARTITION.items())
        )

    if correspondances is None:
        return

    if not isinstance(correspondances, list):
        rapport.echec("correspondances : la racine n'est pas une liste.")
        return

    if not correspondances:
        rapport.echec(
            "correspondances : aucune paire. Le contrat §3.2 en attend une, « Jango de l'Orée des "
            "Crayères », identique caractère pour caractère."
        )
        return

    noms = set()

    for entree in resultats:
        if isinstance(entree, dict) and isinstance(entree.get("chien_nom"), str):
            noms.add(entree["chien_nom"])

    for rang, entree in enumerate(correspondances):
        if not isinstance(entree, dict):
            rapport.echec("correspondance [%d] : l'entrée n'est pas un objet JSON." % rang)
            continue

        inconnues = sorted(set(entree) - {"chien_nom", "reference", "justification"})

        if inconnues:
            rapport.echec(
                "correspondance [%d] : clés inconnues %s."
                % (rang, ", ".join("« %s »" % c for c in inconnues))
            )
            continue

        nom = entree.get("chien_nom")

        if nom not in noms:
            rapport.echec(
                "correspondance [%d] : « %s » n'est le nom d'aucun des résultats transcrits. Une "
                "correspondance qui ne correspond à rien est un no-op silencieux."
                % (rang, visible(str(nom)))
            )
            continue

        if not str(entree.get("justification") or "").strip():
            rapport.echec(
                "correspondance [%d] « %s » : « justification » vide. C'est elle qui rend "
                "impossible de faire entrer « très probablement le même chien »." % (rang, nom)
            )
            continue

        if not str(entree.get("reference") or "").strip():
            rapport.echec("correspondance [%d] « %s » : « reference » vide." % (rang, nom))
            continue

        rapport.ok("correspondance « %s » vers « %s », justifiée." % (nom, entree["reference"]))


# ---------------------------------------------------------------------------------------------
# Programme principal
# ---------------------------------------------------------------------------------------------


def main(argv):
    # Le rapport cite des lignes de la source, glyphes ♂ et ♀ compris. Sous Windows, `sys.stdout`
    # retombe sur cp1252 dès que la sortie n'est pas un terminal — et cp1252 ne connaît ni ♂ ni ♀ :
    # le comparateur planterait en UnicodeEncodeError au lieu de rapporter la divergence.
    for flux in (sys.stdout, sys.stderr):
        if hasattr(flux, "reconfigure"):
            flux.reconfigure(encoding="utf-8")

    rapport = Rapport(silencieux="--silencieux" in argv[1:])

    depot = racine_du_depot()

    if depot == "":
        print(
            "  [ECHEC] `docs/migration/source/outils/reduire.py` est introuvable au-dessus de %s. "
            "Ce comparateur se lance depuis l'arbre de travail du dépôt, jamais depuis un "
            "conteneur : `docs/` n'y est pas monté (compose.yaml:85-89)." % MODULE
        )
        return 1

    outils = os.path.join(depot, "docs", "migration", "source", "outils")
    dossier_html = os.path.join(depot, "docs", "migration", "source", "html")
    outil = os.path.join(outils, "reduire.py")
    zones = importer_zones(outils)
    mesures = lire_releve(os.path.join(dossier_html, "RELEVE.md"))

    donnees = os.path.join(MODULE, "donnees")

    rapport.titre("Fichiers de données")

    resultats = charger_json(os.path.join(donnees, "resultats.json"), rapport, "résultats")
    correspondances = charger_json(
        os.path.join(donnees, "correspondances-chiens.json"), rapport, "correspondances de chiens"
    )

    pages_lues = {}

    for slug, _statut, _html in PAGES:
        page = charger_json(
            os.path.join(donnees, "pages", slug + ".json"), rapport, "page « %s »" % slug
        )
        if isinstance(page, dict):
            pages_lues[slug] = page
        elif page is not None:
            rapport.echec("page « %s » : la racine n'est pas un objet JSON." % slug)

    if resultats is not None and not isinstance(resultats, list):
        rapport.echec("résultats : la racine n'est pas une liste d'entrées.")
        resultats = None

    if resultats is None:
        resultats = []
    else:
        rapport.ok("%d entrées lues dans resultats.json." % len(resultats))

    if pages_lues:
        rapport.ok("%d fiches de page lues sur %d." % (len(pages_lues), len(PAGES)))

    # Re-dérivation des zones, une fois pour toutes.
    zones_par_slug = {}

    for slug, _statut, fichier_html in PAGES:
        chemin = os.path.join(dossier_html, fichier_html)

        if not os.path.isfile(chemin):
            continue

        sortie, erreur = reduire(outil, chemin)

        if sortie is None:
            rapport.echec("%s : `reduire.py` a échoué — %s." % (slug, erreur.strip()))
            continue

        for titre, contenu in zones(sortie):
            if titre == ZONE_ATTENDUE:
                zones_par_slug[slug] = contenu

    lignes_travail = (zones_par_slug.get("travail") or "").split("\n")

    controle_1(rapport, pages_lues, zones_par_slug)
    positions = controle_1b(rapport, resultats, lignes_travail)
    controle_1c(rapport, resultats, lignes_travail, positions)
    controle_2(rapport, resultats)
    controle_3(rapport, resultats)
    controle_4(rapport, outil, dossier_html, mesures, zones_par_slug)
    controle_5(rapport, pages_lues, dossier_html, mesures)
    controle_6(rapport, resultats, correspondances)

    rapport.titre("Transformations déclarées et comptées")

    for nom in sorted(rapport.transformations):
        compte = rapport.transformations[nom]
        if compte == 0:
            rapport.echec(
                "transformation « %s » : déclenchée 0 fois. Une transformation morte est le signe "
                "que la source a changé." % nom
            )
        else:
            rapport.ok("« %s » : %d fois." % (nom, compte))

    print("=" * 78)

    if rapport.echecs == 0:
        print("Concordance : aucune divergence inexpliquée.")
        return 0

    print(
        "Concordance : %d divergence%s inexpliquée%s. La reprise n'est pas terminée."
        % (rapport.echecs, "s" if rapport.echecs > 1 else "", "s" if rapport.echecs > 1 else "")
    )
    return 1


if __name__ == "__main__":
    raise SystemExit(main(sys.argv))
