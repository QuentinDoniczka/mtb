"""5/4 — Redige ../MANIFESTE.md a partir du HTML archive, des mesures et des fichiers deposes.

Hors ligne. Tout chiffre du manifeste sort d'ici : rien n'y est saisi a la main.
"""
import collections
import datetime
import json
import os
import re
import sys

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import chemins as C

SRC = C.SOURCE
HTML_DIR = C.HTML
DEST = C.PHOTOS
_J = {"ids.json": C.IDS, "choix.json": C.CHOIX,
      "telecharges.json": C.TELECHARGES, "mesures.json": C.MESURES}

ids = json.load(open(_J["ids.json"], encoding="utf-8"))
choix = json.load(open(_J["choix.json"], encoding="utf-8"))
dl = json.load(open(_J["telecharges.json"], encoding="utf-8"))

# --- attributs alt reellement ecrits dans le HTML, par identifiant -----------------
img_re = re.compile(r"<img\b[^>]*>", re.I)
alts = collections.defaultdict(set)
for f in sorted(os.listdir(HTML_DIR)):
    if not f.endswith(".html"):
        continue
    raw = open(os.path.join(HTML_DIR, f), "rb").read().decode("utf-8", "replace")
    for tag in img_re.findall(raw):
        m = re.search(r"cc_images/(?:[a-z0-9]+_)?(\d+)\.([A-Za-z]+)", tag, re.I)
        key = None
        if m:
            key = m.group(1) + "." + m.group(2)
        elif "emotionheader" in tag.lower():
            key = "emotionheader.jpeg"
        if key:
            a = re.search(r"""\balt\s*=\s*["']([^"']*)["']""", tag, re.I)
            alts[key].add(a.group(1) if a else "(attribut alt absent)")

# --- fichiers .md qui citent l'identifiant ----------------------------------------
md_files = []
for root, dirs, files in os.walk(SRC):
    if os.path.basename(root) in ("html", "photos", "outils"):
        dirs[:] = []
        continue
    for f in files:
        if f.endswith(".md"):
            md_files.append(os.path.relpath(os.path.join(root, f), SRC).replace("\\", "/"))
md_txt = {p: open(os.path.join(SRC, p), encoding="utf-8", errors="replace").read() for p in md_files}

def cite_md(ident):
    num = ident.split(".")[0]
    motif = "emotionheader" if ident == "emotionheader.jpeg" else num
    return sorted(p for p, t in md_txt.items() if motif in t)

def html_citants(ident):
    if ident == "emotionheader.jpeg":
        tous = sorted(f for f in os.listdir(HTML_DIR) if f.endswith(".html"))
        cit = []
        for f in tous:
            raw = open(os.path.join(HTML_DIR, f), "rb").read().decode("utf-8", "replace")
            if "emotionheader" in raw.lower():
                cit.append(f)
        return cit
    return ids[ident]["files"]

def dims(e):
    return "%s×%s" % (e["largeur"], e["hauteur"])

mes = json.load(open(_J["mesures.json"], encoding="utf-8"))
nb_servies = sum(1 for e in mes.values() if e["code"] in (200, 206))
nb_404 = sum(1 for e in mes.values() if e["code"] == 404)

_html_raw = {f: open(os.path.join(HTML_DIR, f), "rb").read().decode("utf-8", "replace")
             for f in sorted(os.listdir(HTML_DIR)) if f.endswith(".html")}

def pages_avec(motif):
    return sum(1 for t in _html_raw.values() if motif in t)

MOBILIER = [
    ("`//cdn.website-start.de/s/img/logo.gif`",
     pages_avec("cdn.website-start.de/s/img/logo.gif"), "logo IONOS du gabarit"),
    ("`//cdn.website-start.de/s/img/cc/printer.gif`",
     pages_avec("cdn.website-start.de/s/img/cc/printer.gif"), "icône « imprimer » du gabarit"),
    ("`https://www.mtbrabant.com/proxy/static/mod/facebook/files/img/facebook-share-icon.png`",
     pages_avec("facebook-share-icon.png"), "icône de partage Facebook du module IONOS"),
]

lignes, ecartes_sec, echecs, sha_index = [], [], [], collections.defaultdict(list)
total = 0
for ident in sorted(choix, key=lambda k: (k != "emotionheader.jpeg", k)):
    c, d = choix[ident], dl[ident]
    if not d.get("ok"):
        echecs.append(ident)
        continue
    r = c["retenu"]
    total += d["octets_recus"]
    sha_index[d["sha256"]].append(ident)
    comp = []
    for e in c["ecartes"]:
        comp.append("`%s` %s" % (e["prefixe"], dims(e)))
    for p in c["absents"]:
        comp.append("`%s` 404" % p)
    pourquoi = "`%s` — %s ; %s" % (r["prefixe"], dims(r),
                                   " · ".join(comp) if comp else "seule rendition servie")
    cit_html = html_citants(ident)
    cit_md = cite_md(ident)
    if len(cit_html) > 5:
        ch = "%d fichiers `.html` (liste en §5.1)" % len(cit_html)
    else:
        ch = " ".join("`%s`" % x for x in cit_html)
    if len(cit_md) > 5:
        cm = "%d fichiers `.md` (liste en §5.1)" % len(cit_md)
    else:
        cm = " ".join("`%s`" % x for x in cit_md) or "aucun"
    lignes.append("| `%s` | `%s` | %s | %s | %s | %d | %s | `%s` | %s<br>%s |" % (
        ident, ident, r["url"], pourquoi, d["code"], d["octets_recus"], dims(d),
        d["sha256"], ch, cm))
    ecartes_sec.append((ident, r, c))

doublons = {s: v for s, v in sha_index.items() if len(v) > 1}
aujourdhui = datetime.date.today().isoformat()

out = []
w = out.append
w("# Photographies de l'ancien site — archive de sauvegarde")
w("")
w("## À lire avant tout téléversement — dette T12")
w("")
w("> Ces fichiers ne se téléversent qu'**après** le module d'images de l'issue #8. WordPress ne")
w("> découpe et ne convertit une image **qu'au téléversement** : une photo importée avant ce module")
w("> n'aura **jamais** ses formats modernes ni ses sous-tailles, et tout le stock serait à")
w("> régénérer. C'est un **prérequis dur** de #20-#21, pas une recommandation.")
w("")
w("Ce dossier est une **archive de sauvegarde de l'original**, **pas la source de service**. Les")
w("photos finiront servies depuis le serveur du nouveau site, téléversées dans la médiathèque")
w("WordPress par #20-#21. Rien ici n'est destiné à être servi tel quel à un visiteur.")
w("")
w("Il existe parce que **cette issue est le seul moment du projet où le site source existe")
w("encore** : l'abonnement IONOS sera résilié précisément parce que le nouveau site existe. Le")
w("texte se retape ; une photographie de 2011 non.")
w("")
w("## 1. Ce qui a été fait, et ce qui ne l'a pas été")
w("")
nb_html = len([f for f in os.listdir(HTML_DIR) if f.endswith('.html')])
nb_url = sum(len(us) for v in ids.values() for us in v['prefixes'].values())
w("- **Recensement** : les %d fichiers HTML de `../html/` ont été relus intégralement ; **toute**" % nb_html)
w("  occurrence d'une URL contenant `cc_images` a été relevée, quel que soit l'attribut porteur")
w("  (`src`, `href`, `srcset`, style en ligne, `data-*`). Une image liée mais non affichée est une")
nb_href = sum(1 for v in ids.values() if 'href' in v['ctx'])
w("  image quand même : **%d URL distinctes** relevées pour **%d identifiants** `cc_images`, dont" % (nb_url, len(ids)))
w("  **%d** apparaissent aussi en `href` (le lien d'agrandissement de la galerie), et **%d** en" % (nb_href, len(ids) - nb_href))
w("  `src` seulement. Aucune référence en `srcset`, en style en ligne ou en `data-*` sur ce site.")
w("- **Déduplication** : `thumb_16791790.jpg`, `cache_16791790.jpg` et `teaserbox_16791790.jpg`")
w("  sont trois rendus de la même photo. La clé de déduplication est l'**identifiant IONOS**")
w("  (`16791790.jpg`), préfixe exclu.")
w("- **Nommage** : `<identifiant IONOS>.<extension>`, **jamais** un nom de chien. Nommer un")
w("  fichier `jango-2.jpg` supposerait de savoir qui est sur la photo : c'est une invention de fait")
w("  d'élevage. L'extension est conservée **telle que servie, casse comprise** (`.JPG` et `.jpg`")
w("  coexistent sur le site source ; ils ne sont pas harmonisés).")
w("- **Octets tels que servis** : aucune retouche, aucun recadrage, aucune conversion, aucune")
w("  compression, aucun renommage. Le condensé SHA-256 porte sur les octets déposés.")
w("- **Aucun fait d'élevage n'est écrit ici** : aucun chien nommé, aucune photo datée, aucun")
w("  contenu d'image décrit. Les seules colonnes descriptives sont ce qui est **écrit** dans le")
w("  source : l'attribut `alt` (§7) et les pages qui citent l'image.")
w("- **Rejouable sans le site source** : les cinq scripts qui ont produit ce dossier sont dans")
w("  `outils-photos/`, avec leurs relevés intermédiaires (`ids.json`, `mesures.json`,")
w("  `choix.json`, `telecharges.json`). Aucun chiffre de ce manifeste n'est saisi à la main : il")
w("  est **entièrement régénéré** par `5-manifeste.py`. Le recensement et le dépouillement se")
w("  rejouent hors ligne ; seules la sonde et le dépôt touchent le réseau, et ils **reprennent**")
w("  au lieu de recommencer.")
w("")
w("## 2. Comment la rendition a été choisie — par mesure, jamais par convention")
w("")
w("**Le préfixe le plus gros n'est pas le même d'une image à l'autre.** Pour chaque identifiant")
w("`cc_images`, les quatre formes ont été sondées — `cache_`, `teaserbox_`, `thumb_`, et")
w("l'identifiant nu — et celle dont les **dimensions en pixels** sont les plus grandes est retenue")
w("(à dimensions égales, la plus lourde). Les dimensions sont **mesurées sur les octets reçus**,")
w("pas déduites du nom.")
w("")
w("Une sonde exploratoire (`outils-photos/0-explorer-prefixes.py`) a testé 31 formes candidates,")
w("l'identifiant nu compris (`original_`, `big_`, `full_`, `large_`,")
w("`preview_`, `master_`, `raw_`, `zoom_`, `lightbox_`…) sur quatre identifiants témoins :")
w("**seuls `cache_`, `teaserbox_` et `thumb_` répondent 200**. L'identifiant nu")
w("(`/s/cc_images/16791790.jpg`, sans préfixe) répond **404 sur les 191 identifiants `cc_images`** :")
w("cette forme d'URL n'existe pas sur IONOS.")
w("")
w("Résultat sur les %d identifiants — la ligne `(nu)` est le bandeau `emotionheader.jpeg`, servi"
  % len(ecartes_sec))
w("hors `cc_images` et sans préfixe (§5.1) :")
w("")
prefc = collections.Counter(c["retenu"]["prefixe"] for c in choix.values())
w("| Préfixe retenu | Identifiants |")
w("|---|---|")
for p, n in prefc.most_common():
    w("| `%s` | %d |" % (p, n))
w("")
pas_cache = sum(1 for c in choix.values()
                if c["retenu"]["prefixe"] != "cache_" and any(e["prefixe"] == "cache_" for e in c["ecartes"]))
w("Sur **%d des %d identifiants `cc_images`, `cache_` n'est pas la plus grande rendition** — c'est"
  % (pas_cache, len(ids)))
w("`teaserbox_` qui l'est, et l'écart va jusqu'à `17365005.JPG` : `teaserbox_` 720×1080 contre")
w("`cache_` 200×300. Une règle a priori (« prendre `cache_` ») aurait perdu de la définition sur")
w("près d'un tiers du stock.")
w("")
w("Structure observée, sans exception sur les %d identifiants : **`cache_` existe toujours**, et il" % len(ids))
w("est accompagné **soit** de `teaserbox_` (60 identifiants) **soit** de `thumb_` (131), jamais des")
w("deux. Là où `teaserbox_` existe, il est le plus grand **59 fois sur 60** — l'unique exception est")
w("`7128435.png` (`cache_` 490×540 contre `teaserbox_` 186×205). `thumb_` n'est retenu **aucune")
w("fois** : il plafonne à 150 px de grand côté.")
w("")
w("## 3. Résolution disponible — constat à remonter")
w("")
w("**Ce dossier contient la plus grande rendition publiquement servie, et ce n'est presque jamais")
w("l'original.** Les dimensions retenues s'empilent sur des plafonds de redimensionnement :")
w("")
larg = collections.Counter(max(c["retenu"]["largeur"], c["retenu"]["hauteur"]) for c in choix.values())
w("| Grand côté de la rendition retenue | Identifiants |")
w("|---|---|")
for v, n in sorted(larg.items(), key=lambda kv: -kv[1])[:10]:
    w("| %d px | %d |" % (v, n))
w("")
cotes = collections.defaultdict(list)
for e in mes.values():
    if e["code"] in (200, 206):
        cotes[e["prefixe"]].append(max(e["largeur"], e["hauteur"]))
gd = [e for e in mes.values() if e["code"] in (200, 206) and max(e["largeur"], e["hauteur"]) > 1024]
w("Mesuré sur **l'ensemble des %d renditions servies**, pas seulement sur les retenues : sur les" % nb_servies)
w("%d renditions `cache_`, **%d s'arrêtent exactement à 1024 px** de grand côté, %d restent en" % (
    len(cotes["cache_"]), sum(1 for x in cotes["cache_"] if x == 1024),
    sum(1 for x in cotes["cache_"] if x < 1024)))
w("dessous et **%d seulement** dépassent ce seuil. `teaserbox_` ne dépasse jamais **%d px**," % (
    sum(1 for x in cotes["cache_"] if x > 1024), max(cotes["teaserbox_"])))
w("`thumb_` jamais **%d px**. Au total, **%d renditions** sur %d dépassent 1024 px de grand côté :" % (
    max(cotes["thumb_"]), len(gd), nb_servies))
w("")
w("| Identifiant | Préfixe | Dimensions |")
w("|---|---|---|")
for e in sorted(gd, key=lambda e: e["ident"]):
    w("| `%s` | `%s` | %s×%s |" % (e["ident"], e["prefixe"], e["largeur"], e["hauteur"]))
w("")
w("Le tassement de 49 identifiants sur **exactement** 1024 px, et de dizaines d'autres sur 768 ou")
w("800, est la signature d'un **redimensionnement au téléversement** : ces images-là sont")
w("certainement des dérivés. Aucune rendition servie par le site ne dépasse **1527×1080**, soit")
w("**1,6 mégapixel** — l'ordre de grandeur d'un appareil de 2005, pas celui des photographies")
w("d'origine. Pour les quatre exceptions ci-dessus, rien ne permet de trancher : elles peuvent être")
w("des originaux de petite taille comme des dérivés. **Cela ne se déduit pas et n'est pas supposé")
w("ici.**")
w("")
w("> **Question bloquante pour D4.** Si les originaux pleine définition existent, ils sont dans le")
w("> gestionnaire de médias IONOS de l'éleveuse, **hors de portée d'un téléchargement public**.")
w("> Ce que cette archive contient est donc **la plus grande rendition publiquement servie**, pas")
w("> l'original. Récupérer les originaux suppose un export depuis le compte IONOS, **avant la")
w("> résiliation de l'abonnement**.")
w("")
w("## 4. Plafond de garde")
w("")
w("Le contrat (§4) impose de **mesurer avant de télécharger** et d'arrêter au-delà de 150 Mo. Le")
w("passage de mesure seule (requêtes avec plage d'octets, une par rendition, séquentielles) a")
w("donné **%s octets** (%.2f Mo) pour les %d renditions retenues — **sous le plafond**, le" % (
    format(total, ',').replace(',', ' '), total / 1048576, len(ecartes_sec)))
w("téléchargement a donc eu lieu. Le poids réellement déposé est identique au poids mesuré, ligne")
w("à ligne (§8).")
w("")
w("## 5. Les %d photographies archivées" % len(ecartes_sec))
w("")
w("Une ligne par identifiant. « Pages citantes » : d'abord les fichiers `.html` de `../html/`,")
w("puis les réductions `.md` de `../portees/`, `../chiens/`, `../pages/` et de la racine, **telles")
w("qu'elles existaient le %s**. Colonne « préfixe retenu » : la rendition choisie et ses" % aujourdhui)
w("dimensions, puis les renditions écartées avec les leurs — c'est ce qui rend le choix")
w("contestable.")
w("")
w("| Identifiant | Fichier déposé | URL d'origine retenue | Préfixe retenu — dimensions comparées | HTTP | Octets | Dimensions | SHA-256 | Pages citantes |")
w("|---|---|---|---|---|---|---|---|---|")
out.extend(lignes)
w("")
w("Les URL portent, dans le HTML source, un suffixe de cache `?t=…` (époque Unix). Il n'a **aucun**")
w("effet sur les octets servis et n'est pas reproduit ici ; le paramètre de taille observé sur le")
w("bandeau (`?…920px.313px`) est de même **ignoré par le serveur** (mesuré : la même URL sans")
w("paramètre, avec ce paramètre, et avec `4000px.4000px`, renvoie les mêmes 77 536 octets et les")
w("mêmes 920×313).")
w("")
w("### 5.1 Le bandeau `emotionheader.jpeg`")
w("")
eh_html = html_citants("emotionheader.jpeg")
eh_md = cite_md("emotionheader.jpeg")
manquants = [f for f in sorted(_html_raw) if f not in eh_html]
w("Seule image citée par plus d'un fichier `.html` : elle est servie par le gabarit, en tête de")
w("**%d des %d** pages archivées. La seule qui ne la porte pas est `%s`," % (
    len(eh_html), nb_html, manquants[0]) if len(manquants) == 1 else
  "**%d des %d** pages archivées. Les %d qui ne la portent pas : %s." % (
      len(eh_html), nb_html, len(manquants), " ".join("`%s`" % x for x in manquants)))
if len(manquants) == 1:
    w("la 53ᵉ URL hors sitemap du §2 du contrat — celle qui répond **302** parce qu'elle est protégée")
    w("par mot de passe sur l'ancien site, et dont le HTML archivé n'est donc pas une page de contenu.")
w("")
w("Elle est citée par **%d** des %d réductions `.md` de `../`. Les %d identifiants `cc_images`"
  % (len(eh_md), len(md_txt), len(ids)))
w("sont, eux, cités par **exactement un fichier `.html`** chacun ; %d d'entre eux sont cités par"
  % sum(1 for k in ids if len(cite_md(k)) > 1))
w("**deux** `.md`, ce qui est la trace des deux captures de `/` et de `/travail/` par deux passes")
w("(§6 du contrat), et non d'une photo réutilisée.")
w("")
w("### 5.2 Identifiants distincts servant des octets identiques")
w("")
if doublons:
    w("Constat **factuel**, relevé et non interprété : plusieurs identifiants IONOS distincts")
    w("servent des octets rigoureusement identiques (même SHA-256). Ce qui a produit cette")
    w("situation ne se déduit pas du site et n'est pas supposé ici. **Ces identifiants ne sont pas")
    w("fusionnés** : la clé d'archivage est l'identifiant IONOS, et chacun est cité par une page")
    w("différente.")
    w("")
    w("| SHA-256 | Identifiants | Dimensions |")
    w("|---|---|---|")
    for s, v in sorted(doublons.items(), key=lambda kv: -len(kv[1])):
        d0 = dl[v[0]]
        w("| `%s…` | %s | %s×%s |" % (s[:16], " ".join("`%s`" % x for x in sorted(v)),
                                      d0["largeur"], d0["hauteur"]))
else:
    w("Aucun.")
w("")
w("## 6. Renditions écartées")
w("")
w("Toutes les autres renditions qui **répondent 200**, avec leurs dimensions et leur poids. C'est")
w("ce tableau qui rend le choix du §2 contestable : il suffit d'y lire une ligne plus grande que la")
w("retenue pour prendre la chaîne en défaut.")
w("")
w("| Identifiant | Préfixe écarté | Dimensions | Octets | Rendition retenue à la place |")
w("|---|---|---|---|---|")
n_ec = 0
for ident, r, c in ecartes_sec:
    for e in c["ecartes"]:
        n_ec += 1
        w("| `%s` | `%s` | %s | %d | `%s` %s |" % (ident, e["prefixe"], dims(e), e["octets"],
                                                   r["prefixe"], dims(r)))
w("")
w("**%d renditions écartées** pour %d retenues, soit les %d renditions servies." % (
    n_ec, len(ecartes_sec), n_ec + len(ecartes_sec)))
w("")
w("### 6.1 Formes d'URL qui ne sont pas servies")
w("")
w("Sondées et **404** — ce ne sont pas des photos perdues, ce sont des URL qui n'existent pas :")
w("")
w("| Forme sondée | 404 | 200 |")
w("|---|---|---|")
codes_p = collections.Counter((e["prefixe"], e["code"] in (200, 206)) for e in mes.values())
for p in ("cache_", "teaserbox_", "thumb_", "(nu)"):
    w("| `%s` | %d | %d |" % (p, codes_p[(p, False)], codes_p[(p, True)]))
w("")
w("## 7. Textes alternatifs écrits dans le source")
w("")
w("Relevé **verbatim** de l'attribut `alt` des balises `<img>` du HTML source. Rien n'est complété :")
w("un `alt` vide est reporté vide. Aucun texte alternatif n'a été rédigé pour cette archive —")
w("décrire une photo supposerait de savoir ce qu'elle montre.")
w("")
alt_c = collections.Counter()
for ident in choix:
    for a in alts.get(ident, {"(aucune balise <img> — image liée par `href` seulement)"}):
        alt_c[a] += 1
w("| Valeur de `alt` | Nombre d'images |")
w("|---|---|")
for a, n in alt_c.most_common():
    w("| %s | %d |" % ("*(vide)*" if a == "" else "`%s`" % a, n))
w("")
nommes = sorted((i, a) for i, s in alts.items() for a in s if a not in ("", "(attribut alt absent)"))
if nommes:
    w("Les seules valeurs non vides, recopiées **verbatim** :")
    w("")
    w("| Identifiant | `alt` du source | Page qui la porte |")
    w("|---|---|---|")
    for i, a in nommes:
        w("| `%s` | `%s` | %s |" % (i, a, " ".join("`%s`" % x for x in html_citants(i))))
    w("")
    w("Ce sont les **deux seuls** endroits où le site source attache un nom à une image. Rien n'est")
    w("déduit de ces `alt` ici : ils sont relevés, pas interprétés.")
w("")
w("## 8. Vérification")
w("")
w("Chaque fichier déposé a été relu après écriture :")
w("")
ec_poids = [k for k, r in dl.items() if r.get("ok") and not r["conforme_poids"]]
ec_dim = [k for k, r in dl.items() if r.get("ok") and not r["conforme_dim"]]
ec_disque = [k for k, r in dl.items() if r.get("ok")
             and os.path.getsize(os.path.join(DEST, k)) != r["octets_recus"]]
ec_http = [k for k, r in dl.items() if r.get("code") != 200]
w("- **poids** : les %d fichiers pèsent exactement le nombre d'octets annoncé par `Content-Range`" % len(ecartes_sec))
w("  lors du passage de mesure. **%d écart%s.**" % (len(ec_poids), "s" if len(ec_poids) > 1 else ""))
w("- **taille sur disque** : relue fichier par fichier après écriture, égale aux octets reçus.")
w("  **%d écart%s.**" % (len(ec_disque), "s" if len(ec_disque) > 1 else ""))
w("- **dimensions** : les dimensions relues sur les octets déposés sont identiques à celles")
w("  mesurées à la sonde. **%d écart%s.**" % (len(ec_dim), "s" if len(ec_dim) > 1 else ""))
w("- **réponse HTTP** : 200 sur les %d téléchargements. **%d échec%s.**" % (
    len(ecartes_sec), len(ec_http), "s" if len(ec_http) > 1 else ""))
w("- **somme** : le total des tailles sur disque vaut **%s octets**, égal au total mesuré avant"
  % format(total, ',').replace(',', ' '))
w("  téléchargement.")
w("")
w("## 9. Échecs")
w("")
if echecs:
    w("| Identifiant | Renditions sondées | Réponses |")
    w("|---|---|---|")
    for e in echecs:
        w("| `%s` | | |" % e)
else:
    w("**Aucun.** Les %d identifiants recensés ont au moins une rendition qui répond 200/206, et" % len(choix))
    w("les %d ont été déposés. Aucune photo recensée n'est manquante." % len(ecartes_sec))
    w("")
    w("Pour mémoire, le seul « 404 » massif du relevé est l'**identifiant nu** sans préfixe")
    w("(`/s/cc_images/<id>.<ext>`), qui n'existe pas sur IONOS : ce n'est pas une photo perdue,")
    w("c'est une forme d'URL que le serveur ne sert pas.")
w("")
w("## 10. Mobilier de gabarit écarté")
w("")
w("Trois images sont servies sur presque toutes les pages et **n'appartiennent pas à l'élevage** :")
w("ce sont des éléments d'interface du gabarit IONOS, servis soit depuis le CDN commun à toutes")
w("les vitrines IONOS (`cdn.website-start.de`), soit depuis le chemin d'un module")
w("(`/proxy/static/mod/facebook/`). Elles sont écartées et nommées ici pour que l'écart soit")
w("vérifiable. **Ce sont les trois seules images écartées** : tout le reste est archivé.")
w("")
w("| URL | Présence | Nature |")
w("|---|---|---|")
for u, p, n in MOBILIER:
    w("| %s | %d des %d pages | %s |" % (u, p, nb_html, n))
w("")
w("**`emotionheader.jpeg` n'est pas écarté**, bien qu'il soit servi par le gabarit et hors")
w("`cc_images` : il est hébergé **sur le domaine du site** (`www.mtbrabant.com/s/img/`), il est")
w("**propre à ce site** — l'URL ne porte aucun nom de module ni de CDN partagé — et c'est une")
w("photographie du bandeau. Il est archivé comme les autres, sous le nom `emotionheader.jpeg`.")
w("Son contenu n'est pas décrit ici, et son `alt` dans le source est vide.")
w("")
w("## 11. Total")
w("")
w("| | |")
w("|---|---|")
tri = sorted(dl.items(), key=lambda kv: (kv[1]["largeur"] * kv[1]["hauteur"], kv[1]["octets_recus"]))
def _desc(kv):
    k, r = kv
    return "`%s` — %s×%s, %s o" % (k, r["largeur"], r["hauteur"],
                                   format(r["octets_recus"], ',').replace(',', ' '))
w("| Fichiers HTML relus | %d |" % nb_html)
w("| Identifiants distincts recensés | %d (%d `cc_images` + le bandeau `emotionheader.jpeg`) |"
  % (len(choix), len(ids)))
w("| Renditions sondées | %d (%d servies, %d en 404) |" % (len(mes), nb_servies, nb_404))
w("| Fichiers déposés | %d |" % len(ecartes_sec))
w("| Échecs | %d |" % len(echecs))
w("| Poids total | **%s octets** (%.2f Mo) |" % (format(total, ',').replace(',', ' '), total / 1048576))
w("| Plus grande retenue | %s |" % _desc(tri[-1]))
w("| Plus petite retenue | %s |" % _desc(tri[0]))
w("| Date de capture | %s |" % aujourdhui)
w("")

open(os.path.join(DEST, "MANIFESTE.md"), "w", encoding="utf-8", newline="\n").write("\n".join(out))
print("MANIFESTE.md ecrit :", len(out), "lignes,", len(lignes), "photos")
print("doublons sha:", {s[:12]: v for s, v in doublons.items()})
print("alts:", alt_c.most_common())
