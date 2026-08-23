"""4/4 — Depose les octets tels que servis, sous `<identifiant IONOS>.<extension>`. Reseau.

Aucune retouche, aucun recadrage, aucune conversion, aucune compression, aucun renommage
« propre » : la casse de l'extension servie est conservee (`.JPG` et `.jpg` coexistent).
Verifie apres ecriture le poids annonce par la sonde et les dimensions relues.

Relance = reprise : un fichier deja depose et conforme n'est pas retelecharge.
Ecrit telecharges.json et les fichiers de ../.
"""
import hashlib
import json
import os
import sys

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import chemins as C
import reseau as R

choix = json.load(open(C.CHOIX, encoding="utf-8"))
faits = json.load(open(C.TELECHARGES, encoding="utf-8")) if os.path.exists(C.TELECHARGES) else {}
os.makedirs(C.PHOTOS, exist_ok=True)

for i, (ident, c) in enumerate(sorted(choix.items())):
    depose = os.path.join(C.PHOTOS, ident)
    if faits.get(ident, {}).get("ok") and os.path.exists(depose) \
            and os.path.getsize(depose) == faits[ident]["octets_recus"]:
        continue
    r = c["retenu"]
    code, hdr, corps = R.requete(r["url"])
    res = {"url": r["url"], "prefixe": r["prefixe"], "code": code, "octets_recus": len(corps),
           "octets_sonde": r["octets"], "type": hdr.get("Content-Type"),
           "modifie": hdr.get("Last-Modified")}
    if code == 200 and corps:
        open(depose, "wb").write(corps)
        l, h, fmt = R.dimensions(corps)
        res.update(sha256=hashlib.sha256(corps).hexdigest(), largeur=l, hauteur=h, format=fmt,
                   conforme_poids=(len(corps) == r["octets"]),
                   conforme_dim=(l == r["largeur"] and h == r["hauteur"]),
                   ok=True, fichier=ident)
    else:
        res["ok"] = False
    faits[ident] = res
    if i % 20 == 0:
        json.dump(faits, open(C.TELECHARGES, "w", encoding="utf-8"), ensure_ascii=False, indent=1)
        print("%d/%d %s %s" % (i, len(choix), ident, code))
        sys.stdout.flush()

json.dump(faits, open(C.TELECHARGES, "w", encoding="utf-8"), ensure_ascii=False, indent=1)
ok = [r for r in faits.values() if r.get("ok")]
print("deposes            :", len(ok), "/", len(choix))
print("poids total         :", sum(r["octets_recus"] for r in ok), "octets")
print("ecarts de poids     :", [k for k, r in faits.items() if r.get("ok") and not r["conforme_poids"]])
print("ecarts de dimension :", [k for k, r in faits.items() if r.get("ok") and not r["conforme_dim"]])
print("echecs              :", [(k, r["code"]) for k, r in faits.items() if not r.get("ok")])
