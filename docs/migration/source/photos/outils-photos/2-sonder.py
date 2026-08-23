"""2/4 — Mesure chaque rendition AVANT tout telechargement. Reseau, sequentiel.

Une requete par rendition, avec plage d'octets : `Content-Range` donne le poids total du fichier,
les premiers octets suffisent a lire les dimensions. Aucune image n'est ecrite ici : c'est le
passage de mesure impose par le §4 du contrat (plafond de garde a 150 Mo).

Relance = reprise : les renditions deja dans mesures.json ne sont pas re-sondees.
Ecrit mesures.json.
"""
import json
import os
import re
import sys

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import chemins as C
import reseau as R

BASE = "https://www.mtbrabant.com/s/cc_images/"
PREFIXES = ["cache_", "teaserbox_", "thumb_", ""]  # "" = identifiant nu
# le bandeau du gabarit, servi hors cc_images mais propre a ce site
BANDEAU = ("https://www.mtbrabant.com/s/img/emotionheader.jpeg", "emotionheader.jpeg", "(nu)")

ids = json.load(open(C.IDS, encoding="utf-8"))
cibles = [(BASE + p + k, k, p or "(nu)") for k in sorted(ids) for p in PREFIXES] + [BANDEAU]

mes = json.load(open(C.MESURES, encoding="utf-8")) if os.path.exists(C.MESURES) else {}

for i, (url, ident, prefixe) in enumerate(cibles):
    if url in mes:
        continue
    code, hdr, corps = R.requete(url, entetes={"Range": "bytes=0-65535"})
    e = {"ident": ident, "prefixe": prefixe, "code": code}
    if code in (200, 206):
        m = re.search(r"/(\d+)$", hdr.get("Content-Range", ""))
        e["octets"] = int(m.group(1)) if m else int(hdr.get("Content-Length") or len(corps))
        l, h, fmt = R.dimensions(corps)
        if l is None and e["octets"] > len(corps):
            _, _, complet = R.requete(url)      # l'en-tete ne suffisait pas : relecture complete
            l, h, fmt = R.dimensions(complet)
        e.update(largeur=l, hauteur=h, format=fmt,
                 type=hdr.get("Content-Type"), modifie=hdr.get("Last-Modified"))
    else:
        e["erreur"] = hdr.get("erreur")
    mes[url] = e
    if i % 20 == 0:
        json.dump(mes, open(C.MESURES, "w", encoding="utf-8"), ensure_ascii=False, indent=1)
        print("%d/%d %s %s -> %s" % (i, len(cibles), prefixe, ident, code))
        sys.stdout.flush()

json.dump(mes, open(C.MESURES, "w", encoding="utf-8"), ensure_ascii=False, indent=1)
print("renditions sondees :", len(mes))
