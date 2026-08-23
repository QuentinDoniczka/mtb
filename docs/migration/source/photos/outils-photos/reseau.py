"""Acces reseau : une requete a la fois, avec pause, et lecture des dimensions d'une image.

Le seul domaine interroge est www.mtbrabant.com, le site source.
"""
import io
import time
import urllib.error
import urllib.request

UA = "Mozilla/5.0 (archivage local mtbrabant.com - migration #19)"
PAUSE = 0.35  # secondes entre deux requetes : on est courtois, ce n'est pas une course

opener = urllib.request.build_opener()


def requete(url, methode="GET", entetes=None, delai=30):
    """Renvoie (code, en-tetes, corps). Ne leve jamais : un echec devient un code negatif."""
    h = {"User-Agent": UA}
    if entetes:
        h.update(entetes)
    req = urllib.request.Request(url, method=methode, headers=h)
    try:
        with opener.open(req, timeout=delai) as r:
            corps = b"" if methode == "HEAD" else r.read()
            return r.status, dict(r.headers), corps
    except urllib.error.HTTPError as e:
        try:
            e.read()
        except Exception:
            pass
        return e.code, dict(e.headers or {}), b""
    except Exception as e:
        return -1, {"erreur": repr(e)}, b""
    finally:
        time.sleep(PAUSE)


def dimensions(octets):
    """(largeur, hauteur, format) lues sur les octets — meme partiels ; sinon (None, None, None)."""
    try:
        from PIL import Image
        im = Image.open(io.BytesIO(octets))
        return im.width, im.height, im.format
    except Exception:
        return None, None, None
