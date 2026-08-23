"""1/4 — Recense les identifiants d'images cites par le HTML archive. Hors ligne.

Lit ../../html/*.html, releve TOUTE occurrence d'une URL contenant `cc_images` quel que soit
l'attribut porteur, et deduplique par identifiant IONOS (le prefixe `cache_` / `teaserbox_` /
`thumb_` n'est PAS dans la cle : ce sont trois rendus de la meme photo).

Ecrit ids.json.
"""
import collections
import json
import os
import re
import sys

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import chemins as C

url_re = re.compile(r"""[^\s"'()<>]*cc_images[^\s"'()<>]*""", re.I)
attr_re = re.compile(r"""([a-zA-Z_:.-]+)\s*=\s*["']?$""")
decoupe = re.compile(r"/s/cc_images/(?:([a-z0-9]+)_)?([0-9]+)\.([A-Za-z]+)", re.I)

refs = collections.defaultdict(set)
ctx = collections.defaultdict(set)
fichiers = sorted(f for f in os.listdir(C.HTML) if f.endswith(".html"))

for f in fichiers:
    brut = open(os.path.join(C.HTML, f), "rb").read().decode("utf-8", "replace")
    for m in url_re.finditer(brut):
        u = m.group(0).replace("&amp;", "&")
        refs[u].add(f)
        avant = brut[max(0, m.start() - 80):m.start()]
        a = attr_re.findall(avant)
        ctx[u].add(a[-1].lower() if a else "?")

ids = collections.defaultdict(lambda: {"prefixes": collections.defaultdict(set),
                                       "files": set(), "ctx": set()})
non_decoupees = []
for u, fs in refs.items():
    m = decoupe.search(u)
    if not m:
        non_decoupees.append(u)
        continue
    cle = m.group(2) + "." + m.group(3)
    ids[cle]["prefixes"][m.group(1) or ""].add(u)
    ids[cle]["files"] |= fs
    ids[cle]["ctx"] |= ctx[u]

print("fichiers HTML relus       :", len(fichiers))
print("URL cc_images distinctes  :", len(refs))
print("identifiants distincts    :", len(ids))
print("URL non decoupables       :", len(non_decoupees), non_decoupees[:5])
print("prefixes rencontres       :", dict(collections.Counter(
    p for v in ids.values() for p in v["prefixes"])))
print("attributs porteurs        :", dict(collections.Counter(
    c for v in ids.values() for c in v["ctx"])))

json.dump({k: {"prefixes": {p: sorted(us) for p, us in v["prefixes"].items()},
               "files": sorted(v["files"]), "ctx": sorted(v["ctx"])}
           for k, v in sorted(ids.items())},
          open(C.IDS, "w", encoding="utf-8"), ensure_ascii=False, indent=1)
print("ecrit :", C.IDS)
