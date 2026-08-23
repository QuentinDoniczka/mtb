# Outils de l'archive photo

Les cinq scripts qui ont produit `../` et `../MANIFESTE.md`, avec leurs relevés intermédiaires.
Ils sont ici pour la même raison que le HTML brut de `../../html/` est versionné : **rendre le
travail contestable même quand le site source aura disparu.** Aucun chiffre du manifeste n'est
saisi à la main ; tout est régénéré depuis ces fichiers.

## Ordre

`0-explorer-prefixes.py` est une sonde ponctuelle, hors chaîne : elle justifie le choix des quatre
formes sondées par `2-sonder.py`. Les cinq autres se lancent dans l'ordre.

| Script | Réseau ? | Ce qu'il fait | Ce qu'il écrit |
|---|---|---|---|
| `0-explorer-prefixes.py` | **oui** | établit, sur 4 identifiants témoins et 31 formes d'URL, que seuls `cache_`, `teaserbox_` et `thumb_` existent — aucun `original_` | rien (affiche) |
| `1-recenser.py` | non | relève toute URL `cc_images` des 54 HTML de `../../html/`, déduplique par identifiant IONOS | `ids.json` |
| `2-sonder.py` | **oui** | mesure chaque rendition (`cache_`, `teaserbox_`, `thumb_`, identifiant nu) **avant** tout téléchargement | `mesures.json` |
| `3-choisir.py` | non | retient la plus grande rendition par identifiant et applique le plafond de garde de 150 Mo | `choix.json` |
| `4-telecharger.py` | **oui** | dépose les octets tels que servis dans `../` | `telecharges.json` + les images |
| `5-manifeste.py` | non | rédige `../MANIFESTE.md` | `../MANIFESTE.md` |

```
python 1-recenser.py && python 2-sonder.py && python 3-choisir.py \
  && python 4-telecharger.py && python 5-manifeste.py
```

Dépendance unique hors bibliothèque standard : **Pillow**, pour lire les dimensions d'une image
sur ses premiers octets. Les chemins sont déduits de l'emplacement des scripts (`chemins.py`) :
rien n'est codé en dur.

## Deux propriétés qui comptent

- **On mesure avant de télécharger.** `2-sonder.py` ne récupère que les premiers octets de chaque
  rendition (requête avec plage d'octets) : `Content-Range` donne le poids réel du fichier et les
  premiers octets suffisent aux dimensions. `3-choisir.py` refuse de continuer au-delà de 150 Mo
  (§4 du contrat `docs/contracts/issue-19.md`).
- **Relancer ne recommence pas.** `2-sonder.py` saute les renditions déjà mesurées,
  `4-telecharger.py` saute les fichiers déjà déposés et conformes. Relancer la chaîne complète sur
  un dossier à jour ne produit **aucune requête réseau** et ne réécrit aucune image.

## Ce que ces scripts ne font pas

Ils ne retouchent, ne recadrent, ne convertissent, ne compressent et ne renomment **rien** : le
fichier déposé porte les octets servis et l'extension servie, casse comprise. Ils n'écrivent aucun
nom de chien, aucune date, aucune description d'image — nommer une photo supposerait de savoir qui
est dessus, et c'est un fait d'élevage qui ne s'invente pas.

Ils n'interrogent qu'un seul domaine, `www.mtbrabant.com`, séquentiellement, avec une pause entre
deux requêtes.
