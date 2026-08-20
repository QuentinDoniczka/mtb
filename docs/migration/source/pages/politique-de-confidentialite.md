# politique-de-confidentialite — page **absente** du site source

- **URL recherchée** : `https://www.mtbrabant.com/politique-de-confidentialite/` → **404**
- **URL recherchée** : `https://www.mtbrabant.com/politique-de-confidentialit%C3%A9/` → **404**
- **URL recherchée** : `https://www.mtbrabant.com/privacy/` → **404**
- **Présence au sitemap** : aucune. Les 52 `<loc>` de `https://www.mtbrabant.com/sitemap.xml` ne
  portent aucune adresse de politique de confidentialité.
- **Présence dans le menu** : aucune, sur aucune des six pages capturées.
- **Vérifié le** : 2026-08-20

## Conséquence pour la reprise

Il n'y a **rien à recopier**. `docs/BRIEF.md` §5.4 la marque « (à créer) » ; la capture confirme
qu'elle n'existe pas.

**Aucune phrase de cette page n'est écrite par la reprise.** En particulier, aucune durée de
conservation, aucun nom d'hébergeur, aucune finalité de traitement, aucun destinataire et aucun
délai de réponse n'a été inventé.

## Ce qui manque, et qui le débloque

| Trou | Ce qu'il faudrait savoir | Question ouverte |
|---|---|---|
| Durée de conservation des messages du formulaire | les messages sont-ils conservés en base, ou seulement envoyés par courriel ? | **Q3** (`docs/ETAT.md`) |
| Identité et pays de l'hébergeur | l'hébergement de production n'est pas tranché | **Q5** (`docs/ETAT.md`) |
| Mention RGPD du formulaire de contact | appartient au périmètre du formulaire | issues **#22-#24** |
| Responsable de traitement | la page Mentions légales porte « Fabienne Gueneau » et un SIRET (voir `mentions-legales.md`) ; rien dans le source ne dit que c'est le responsable de traitement au sens du RGPD | à confirmer avec l'éleveuse |
| Cookies et mesure d'audience | l'**ancien** site pose un cookie `DIY_SB` et charge une carte Google ; le nouveau n'en pose aucun | à confirmer avec l'éleveuse |

## État de la page créée dans WordPress

La page « Politique de confidentialité » est créée avec la **charpente seule** du motif
`politique-de-confidentialite.php` (trois fiches d'information vides, sans titre). Aucune des trois
ne porte de texte. Côté public, une fiche d'information sans titre ni paragraphe **ne rend rien du
tout** (`fiche-information/render.php` : « Rien de saisi : l'élément racine n'existe pas »), donc la
page affiche son seul titre et reste valide.

Elle **n'est pas publiée** (arbitrage A7 du contrat #17) : statut `draft`.
