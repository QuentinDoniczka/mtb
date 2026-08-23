"""0/4 — Sonde exploratoire : quelles formes d'URL IONOS existent reellement ? Reseau.

Ne sert pas a l'archivage : elle etablit, sur quelques identifiants temoins, qu'aucun prefixe
autre que `cache_`, `teaserbox_` et `thumb_` n'est servi — en particulier aucun `original_`.
C'est ce qui fonde le §3 du manifeste (« IONOS ne sert aucun original »).

Relancer ce script produit 124 requetes HEAD. Resultat obtenu le 2026-08-23 :
    16791790.jpg  -> cache_ 79 599 o, thumb_ 3 707 o
    14801494.jpg  -> cache_ 1 981 o, teaserbox_ 7 758 o
    17457668.png  -> cache_ 492 777 o, thumb_ 2 110 o
    19031698.JPG  -> cache_ 301 074 o, thumb_ 1 486 o
Toutes les autres formes : 404.
"""
import os
import sys

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import reseau as R

BASE = "https://www.mtbrabant.com/s/cc_images/"
FORMES = ["", "cache_", "thumb_", "teaserbox_", "teaser_", "preview_", "original_", "orig_",
          "big_", "large_", "full_", "gallery_", "lightbox_", "medium_", "slide_", "slider_",
          "banner_", "zoom_", "popup_", "detail_", "photo_", "image_", "master_", "source_",
          "raw_", "cc_", "picture_", "img_", "galleryimage_", "highlight_", "content_"]
TEMOINS = ["16791790.jpg", "14801494.jpg", "17457668.png", "19031698.JPG"]

for ident in TEMOINS:
    print("###", ident)
    for p in FORMES:
        code, hdr, _ = R.requete(BASE + p + ident, methode="HEAD")
        if code == 200:
            print("   200  %-16s %s o  %s" % (p or "(nu)", hdr.get("Content-Length"),
                                              hdr.get("Content-Type")))
        elif code != 404:
            print("   %s  %s" % (code, p or "(nu)"))
    sys.stdout.flush()
