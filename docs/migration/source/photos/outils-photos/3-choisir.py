"""3/4 — Retient, par identifiant, la plus grande rendition. Hors ligne.

Le critere est la surface en pixels MESUREE, jamais le nom du prefixe : sur ce site, `cache_`
n'est pas toujours la plus grande. A dimensions egales, le fichier le plus lourd l'emporte.
Refuse de valider si le total depasse le plafond de garde de 150 Mo (§4 du contrat).

Ecrit choix.json.
"""
import collections
import json
import os
import sys

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import chemins as C

PLAFOND = 150 * 1024 * 1024

mes = json.load(open(C.MESURES, encoding="utf-8"))
par_ident = collections.defaultdict(list)
for url, e in mes.items():
    par_ident[e["ident"]].append(dict(e, url=url))

choix, echecs = {}, []
for ident, lst in par_ident.items():
    servies = [e for e in lst if e["code"] in (200, 206)]
    if not servies:
        echecs.append((ident, sorted((e["prefixe"], e["code"]) for e in lst)))
        continue
    servies.sort(key=lambda e: ((e.get("largeur") or 0) * (e.get("hauteur") or 0),
                                e.get("octets") or 0), reverse=True)
    choix[ident] = {"retenu": servies[0], "ecartes": servies[1:],
                    "absents": sorted(e["prefixe"] for e in lst if e["code"] not in (200, 206))}

total = sum(c["retenu"]["octets"] for c in choix.values())
print("identifiants                :", len(par_ident))
print("sans aucune rendition servie:", len(echecs), echecs)
print("prefixes retenus            :", dict(collections.Counter(
    c["retenu"]["prefixe"] for c in choix.values())))
print("POIDS TOTAL A TELECHARGER   : %d octets (%.2f Mo)" % (total, total / 1048576))

if total >= PLAFOND:
    print("PLAFOND DE GARDE ATTEINT : on ne telecharge pas, on remonte le chiffre.")
    sys.exit(1)

json.dump(choix, open(C.CHOIX, "w", encoding="utf-8"), ensure_ascii=False, indent=1)
print("ecrit :", C.CHOIX)
