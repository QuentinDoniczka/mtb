#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Réduction d'une page HTML archivée en texte lisible — convention de l'issue #19.

Lit UNIQUEMENT un fichier de `docs/migration/source/html/`. N'accède jamais au réseau :
la réduction reste rejouable même si le site source a disparu.

Usage :
    python reduire.py ../html/placement.html             → le corps du .md (les cinq zones)
    python reduire.py ../html/placement.html --entete     → en-tête §3.3 + corps
    python reduire.py ../html/placement.html --insecables → le seul compte d'U+00A0 du corps

Ce que ce script applique, et rien d'autre (contrat `docs/contracts/issue-19.md` §3) :

  §3.1  cinq zones — diywebEmotionHeader, diywebMain, diywebSecondary, diywebSidebar,
        diywebFooter. Zone absente → « *(zone absente du document)* », jamais une
        section vide muette.
  §3.2  1. <br> et fins de blocs → retours à la ligne ; lignes vides consécutives
           réduites à une seule. Une ligne dont le seul contenu est une U+00A0 est du
           CONTENU : elle survit.
        2. <a> → [LIEN href=…]…[/LIEN] · <img> → [IMAGE src=… alt="…"] ·
           <iframe> → [IFRAME src=…]
        3. entités décodées, U+00A0 conservées.
        4. cellules d'une même ligne de tableau associées par «  |  ».

Aucun mot n'est touché : pas de correction d'orthographe, d'accent, de date ni de n° LOF.
"""

from __future__ import annotations

import html
import os
import re
import sys
from html.parser import HTMLParser

NBSP = " "

# Les cinq zones du gabarit IONOS 2111, dans l'ordre imposé par le contrat §3.1.
ZONES = [
    ("Bandeau de gabarit", "diywebEmotionHeader"),
    ("Contenu principal", "diywebMain"),
    ("Colonne secondaire", "diywebSecondary"),
    ("Colonne latérale", "diywebSidebar"),
    ("Pied de page", "diywebFooter"),
]

ZONE_ABSENTE = "*(zone absente du document)*"

VOID = {
    "area", "base", "basefont", "br", "col", "embed", "frame", "hr", "img", "input",
    "isindex", "keygen", "link", "meta", "param", "source", "track", "wbr",
}

# Éléments dont la fin est un retour à la ligne (§3.2.1 « fins de blocs »).
BLOCS = {
    "address", "article", "aside", "blockquote", "body", "center", "dd", "details",
    "dir", "div", "dl", "dt", "fieldset", "figcaption", "figure", "footer", "form",
    "h1", "h2", "h3", "h4", "h5", "h6", "header", "hgroup", "legend", "li", "main",
    "menu", "nav", "ol", "p", "pre", "section", "summary", "table", "tbody", "tfoot",
    "thead", "tr", "ul",
}

# Contenu non rédactionnel : ni texte de la page, ni adresse à conserver.
IGNORES = {"script", "style"}

CELLULES = {"td", "th"}


class Noeud:
    __slots__ = ("tag", "attrs", "enfants")

    def __init__(self, tag, attrs=None):
        self.tag = tag
        self.attrs = dict(attrs or [])
        self.enfants = []


class Arbre(HTMLParser):
    """Construit un arbre tolérant : une balise fermante orpheline est ignorée."""

    def __init__(self):
        super().__init__(convert_charrefs=True)
        self.racine = Noeud("#racine")
        self.pile = [self.racine]

    def handle_starttag(self, tag, attrs):
        noeud = Noeud(tag, attrs)
        self.pile[-1].enfants.append(noeud)
        if tag not in VOID:
            self.pile.append(noeud)

    def handle_startendtag(self, tag, attrs):
        self.pile[-1].enfants.append(Noeud(tag, attrs))

    def handle_endtag(self, tag):
        if tag in VOID:
            return
        for i in range(len(self.pile) - 1, 0, -1):
            if self.pile[i].tag == tag:
                del self.pile[i:]
                return
        # Balise fermante sans ouvrante : ignorée. Aucun texte n'est perdu.

    def handle_data(self, data):
        self.pile[-1].enfants.append(data)


def classes(noeud):
    return (noeud.attrs.get("class") or "").split()


def zones_du_document(racine, classe):
    """Éléments portant ce jeton de classe, sans redescendre dans un élément déjà retenu.

    Le jeton est comparé entier : « diywebMainGutter » n'est pas « diywebMain ».
    """
    trouves = []

    def descendre(n):
        for enfant in n.enfants:
            if isinstance(enfant, str):
                continue
            if classe in classes(enfant):
                trouves.append(enfant)
                continue  # pas de zone dans la zone : elle serait rendue deux fois
            descendre(enfant)

    descendre(racine)
    return trouves


def normaliser_texte(texte):
    """Espaces ASCII compactés. U+00A0 n'est PAS un espace ici : c'est du contenu."""
    return re.sub(r"[ \t\r\n\f\v]+", " ", texte)


def rendre(noeud, en_cellule=False):
    """`en_cellule` : dans une cellule de tableau, un lien ne casse pas la ligne (§3.2.4)."""
    if noeud.tag in IGNORES:
        return ""

    if noeud.tag == "br":
        return "\n"

    if noeud.tag == "img":
        src = noeud.attrs.get("src", "")
        alt = noeud.attrs.get("alt", "")
        return "\n[IMAGE src=%s alt=\"%s\"]\n" % (src, alt)

    if noeud.tag == "iframe":
        return "\n[IFRAME src=%s]\n" % noeud.attrs.get("src", "")

    if noeud.tag == "a" and "href" in noeud.attrs:
        interieur = rendre_enfants(noeud, en_cellule)
        if en_cellule:
            # Dans une cellule, casser la ligne casserait l'association année/chien/niveau.
            return "[LIEN href=%s]%s[/LIEN]" % (noeud.attrs["href"], interieur)
        # Ailleurs, un lien tient sur sa propre ligne : il ne colle pas au bloc voisin.
        return "\n[LIEN href=%s]%s[/LIEN]\n" % (noeud.attrs["href"], interieur)

    if noeud.tag == "hr":
        return "\n"

    if noeud.tag == "tr":
        # §3.2.4 : l'association des cellules d'une ligne EST une donnée.
        cellules = [
            nettoyer_cellule(rendre(e, en_cellule=True))
            for e in noeud.enfants
            if not isinstance(e, str) and e.tag in CELLULES
        ]
        if cellules:
            return "\n" + " | ".join(cellules) + "\n"
        return "\n" + rendre_enfants(noeud, en_cellule) + "\n"

    if noeud.tag in BLOCS:
        return "\n" + rendre_enfants(noeud, en_cellule) + "\n"

    return rendre_enfants(noeud, en_cellule)


def rendre_enfants(noeud, en_cellule=False):
    morceaux = []
    for enfant in noeud.enfants:
        if isinstance(enfant, str):
            morceaux.append(normaliser_texte(enfant))
        else:
            morceaux.append(rendre(enfant, en_cellule))
    return "".join(morceaux)


def nettoyer_cellule(texte):
    """Une cellule tient sur une ligne : ses retours internes deviennent des espaces."""
    lignes = [l.strip(" \t\r\f\v") for l in texte.split("\n")]
    return " ".join(l for l in lignes if l != "").strip(" \t\r\f\v")


def mettre_en_lignes(texte):
    """§3.2.1 : lignes vides consécutives réduites à une ; une ligne d'U+00A0 survit."""
    sortie = []
    for ligne in texte.split("\n"):
        # Les suites d'espaces ASCII du source ne portent rien : le navigateur les
        # affiche déjà comme un espace unique. Les U+00A0, elles, restent intactes.
        ligne = re.sub(r"[ \t]+", " ", ligne).strip(" \t\r\f\v")
        if ligne == "" and (not sortie or sortie[-1] == ""):
            continue
        sortie.append(ligne)
    while sortie and sortie[-1] == "":
        sortie.pop()
    return "\n".join(sortie)


def corps(html_source):
    arbre = Arbre()
    arbre.feed(html_source)
    arbre.close()

    sections = []
    for titre, classe in ZONES:
        elements = zones_du_document(arbre.racine, classe)
        if not elements:
            sections.append("## %s\n\n%s" % (titre, ZONE_ABSENTE))
            continue
        rendu = mettre_en_lignes("\n\n".join(rendre(e) for e in elements))
        if rendu == "":
            # La zone est dans le document mais ne porte aucun texte : le dire,
            # dans les mots exacts de la passe #17 (`pages/litterature.md`).
            sections.append(
                "## %s\n\n**Zone vide dans le HTML reçu.** Aucun contenu à reprendre." % titre
            )
            continue
        sections.append("## %s\n\n```\n%s\n```" % (titre, rendu))
    return "\n\n".join(sections) + "\n"


def titre_du_document(html_source):
    m = re.search(r"<title[^>]*>(.*?)</title>", html_source, re.S | re.I)
    return html.unescape(m.group(1)).strip() if m else ""


def entete(chemin, texte_corps, html_source):
    """En-tête §3.3. Les mesures viennent de `html/RELEVE.md`, jamais du réseau."""
    from urllib.parse import unquote

    slug = os.path.splitext(os.path.basename(chemin))[0]
    releve = os.path.join(os.path.dirname(os.path.abspath(chemin)), "RELEVE.md")
    champs = {"url": "", "code": "", "octets": "", "brut": "", "stable": "", "date": ""}
    if os.path.exists(releve):
        with open(releve, encoding="utf-8") as f:
            contenu = f.read()
        m = re.search(r"\*\*Capturé[e]? le\*\* +: +(\d{4}-\d{2}-\d{2})", contenu)
        if m:
            champs["date"] = m.group(1)
        for ligne in contenu.splitlines():
            cols = [c.strip().strip("`") for c in ligne.strip().strip("|").split("|")]
            if len(cols) >= 7 and cols[1] == slug + ".html":
                champs.update(
                    url=cols[0], code=cols[2], octets=cols[3].replace(" ", ""),
                    brut=cols[4], stable=cols[5],
                )
                break
    return (
        "# %s\n\n"
        "- **URL source (encodée)** : %s\n"
        "- **URL source (lisible)** : %s\n"
        "- **Capturée le** : %s\n"
        "- **Réponse HTTP** : %s\n"
        "- **Taille du HTML reçu** : %s octets\n"
        "- **SHA-256 du HTML reçu** : `%s`   (non reproductible — voir §3.4)\n"
        "- **SHA-256 stable** : `%s`          (reproductible — voir §3.4)\n"
        "- **`<title>` du document** : %s\n"
        "- **HTML brut archivé** : `../html/%s.html`\n\n"
        "> Recopie **verbatim**. Aucun mot, aucune date, aucun numéro n'a été corrigé, complété ni\n"
        "> reformulé. Voir `../CONVENTION` (`docs/contracts/issue-19.md`) pour la méthode de capture.\n"
        "> Espaces insécables (U+00A0) présentes dans ce fichier : **%d** — conservées telles quelles.\n"
        % (
            slug,
            champs["url"],
            unquote(champs["url"]),
            champs["date"],
            champs["code"],
            champs["octets"],
            champs["brut"],
            champs["stable"],
            titre_du_document(html_source),
            slug,
            texte_corps.count(NBSP),
        )
    )


def main(argv):
    args = [a for a in argv[1:] if not a.startswith("--")]
    options = set(a for a in argv[1:] if a.startswith("--"))
    if len(args) != 1:
        sys.stderr.write(__doc__)
        return 2

    chemin = args[0]
    with open(chemin, "rb") as f:
        octets = f.read()
    html_source = octets.decode("utf-8")

    texte = corps(html_source)

    if "--insecables" in options:
        sys.stdout.write("%d\n" % texte.count(NBSP))
        return 0

    if "--entete" in options:
        texte = entete(chemin, texte, html_source) + "\n" + texte

    sys.stdout.buffer.write(texte.encode("utf-8"))
    return 0


if __name__ == "__main__":
    raise SystemExit(main(sys.argv))
