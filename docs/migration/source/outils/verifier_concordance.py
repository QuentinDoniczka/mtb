#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Confronte la sortie de `reduire.py` au corps des `.md` déjà commités.

C'est la preuve que l'outil est fidèle : il doit retrouver, à partir du seul HTML
archivé, ce que la passe #17 avait écrit à la main le 2026-08-20.

    python verifier_concordance.py

Aucun réseau, aucune écriture : le script lit `../html/`, `../pages/`, et compare.
Il ne corrige rien — une divergence est un fait à rapporter, pas à effacer.
"""

from __future__ import annotations

import difflib
import os
import re
import subprocess
import sys

ICI = os.path.dirname(os.path.abspath(__file__))
SOURCE = os.path.dirname(ICI)
OUTIL = os.path.join(ICI, "reduire.py")

# Les six pages dont il existe une capture de référence en convention #17 (5 zones).
CAS = [
    ("bhpl", "pages/bhpl.md"),
    ("placement", "pages/placement.md"),
    ("mentions-legales", "pages/mentions-legales.md"),
    ("litterature", "pages/litterature.md"),
    ("travail", "pages/travail.md"),
    ("accueil", "pages/accueil.md"),
]

MARQUEUR_VIDE = "**Zone vide dans le HTML"
MARQUEUR_ABSENT = "*(zone absente du document)*"


def zones(texte):
    """[(titre normalisé, contenu)] — la parenthèse explicative du titre est ignorée."""
    resultat = []
    lignes = texte.split("\n")
    i = 0
    titre = None
    while i < len(lignes):
        ligne = lignes[i]
        if ligne.startswith("## "):
            titre = re.sub(r"\s*\(.*\)\s*$", "", ligne[3:].strip())
            i += 1
            continue
        if titre and ligne.strip() == "```":
            j = i + 1
            bloc = []
            while j < len(lignes) and lignes[j].strip() != "```":
                bloc.append(lignes[j])
                j += 1
            resultat.append((titre, "\n".join(bloc)))
            titre = None
            i = j + 1
            continue
        if titre and ligne.strip().startswith(MARQUEUR_VIDE):
            resultat.append((titre, "<ZONE VIDE>"))
            titre = None
        elif titre and ligne.strip() == MARQUEUR_ABSENT:
            resultat.append((titre, "<ZONE ABSENTE>"))
            titre = None
        i += 1
    return resultat


def main():
    divergences = 0
    for slug, relatif in CAS:
        chemin_html = os.path.join(SOURCE, "html", slug + ".html")
        reference = os.path.join(SOURCE, relatif.replace("/", os.sep))
        rendu = subprocess.run(
            [sys.executable, OUTIL, chemin_html], capture_output=True, check=True
        ).stdout.decode("utf-8")
        with open(reference, encoding="utf-8") as f:
            attendu = dict(zones(f.read()))
        obtenu = zones(rendu)

        print("=" * 72)
        print("%s  vs  %s" % (slug + ".html", relatif))
        for titre, texte in obtenu:
            if titre not in attendu:
                print("  [zone absente de la référence] %s" % titre)
                divergences += 1
                continue
            if attendu[titre] == texte:
                print("  [identique] %s" % titre)
                continue
            divergences += 1
            print("  [DIVERGENCE] %s" % titre)
            for l in difflib.unified_diff(
                attendu[titre].split("\n"), texte.split("\n"),
                "référence", "outil", lineterm="", n=2,
            ):
                print("     " + l.replace(" ", "<U+00A0>"))

    print("=" * 72)
    print("Zones divergentes : %d" % divergences)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
