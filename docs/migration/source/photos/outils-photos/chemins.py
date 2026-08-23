"""Chemins communs aux outils photo, deduits de l'emplacement du script.

Aucun chemin code en dur : les cinq scripts numerotes se lancent depuis n'importe ou.
"""
import os

OUTILS = os.path.dirname(os.path.abspath(__file__))          # .../source/photos/outils-photos
PHOTOS = os.path.dirname(OUTILS)                             # .../source/photos
SOURCE = os.path.dirname(PHOTOS)                             # .../source
HTML = os.path.join(SOURCE, "html")

# les relevés intermediaires (JSON) sont deposes a cote des scripts
IDS = os.path.join(OUTILS, "ids.json")
MESURES = os.path.join(OUTILS, "mesures.json")
CHOIX = os.path.join(OUTILS, "choix.json")
TELECHARGES = os.path.join(OUTILS, "telecharges.json")
