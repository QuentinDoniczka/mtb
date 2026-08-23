# Outil de réduction — du HTML archivé au texte lisible

Deux scripts Python, sans dépendance et **sans accès réseau**. Ils lisent `../html/`, rien d'autre :
la réduction reste rejouable même quand le site source aura disparu.

| Script | Ce qu'il fait |
|---|---|
| `reduire.py` | Prend **un** fichier de `../html/` et rend le corps d'un `.md` à la convention §3 du contrat `docs/contracts/issue-19.md`. |
| `verifier_concordance.py` | Confronte la sortie de `reduire.py` au corps des `.md` déjà commités par la passe #17. C'est la preuve de fidélité de l'outil. |

## Usage

```
python reduire.py ../html/placement.html               # les cinq zones
python reduire.py ../html/placement.html --entete       # en-tête §3.3 puis les cinq zones
python reduire.py ../html/placement.html --insecables   # le seul compte d'U+00A0
python verifier_concordance.py                          # les six pages de référence
```

`--entete` lit ses valeurs — URL, code HTTP, taille, les deux SHA, la date de capture — dans
`../html/RELEVE.md`. Le `<title>` et le compte d'U+00A0, eux, sont calculés depuis le HTML lui-même.

## Ce que l'outil fait, et rien de plus

Les cinq zones du gabarit IONOS 2111 (§3.1), dans cet ordre :

| Section du `.md` | Classe HTML |
|---|---|
| `## Bandeau de gabarit` | `diywebEmotionHeader` |
| `## Contenu principal` | `diywebMain` |
| `## Colonne secondaire` | `diywebSecondary` |
| `## Colonne latérale` | `diywebSidebar` |
| `## Pied de page` | `diywebFooter` |

Le jeton de classe est comparé **entier** : `diywebMainGutter` n'est pas `diywebMain`. C'est ce qui
évite de prendre la gouttière du gabarit pour le contenu principal — elle apparaît quatre fois par
page. `diywebSidebar` est **imbriquée dans** `diywebSecondary` dans le HTML servi : les deux zones
portent donc le même texte, servi deux fois par le gabarit pour deux largeurs d'écran.

Deux cas distincts, et ils ne se disent pas de la même façon :

- **zone absente du document** → `*(zone absente du document)*` (§3.1) ;
- **zone présente mais sans aucun texte** → `**Zone vide dans le HTML reçu.** Aucun contenu à
  reprendre.` — les mots exacts de la passe #17 (`../pages/litterature.md`).

Les quatre transformations du §3.2, aucune ne touchant un mot :

1. `<br>` et fins de blocs → retours à la ligne ; lignes vides consécutives réduites à une seule.
   **Une ligne dont le seul contenu est une U+00A0 est du contenu : elle survit.** Les suites
   d'espaces **ASCII** sont ramenées à un espace — le navigateur les affiche déjà ainsi — mais
   **jamais** une U+00A0, qui n'est pas traitée comme un espace par cet outil.
2. `<a>` → `[LIEN href=…]…[/LIEN]`, `<img>` → `[IMAGE src=… alt="…"]`, `<iframe>` → `[IFRAME src=…]`.
   Un `alt` absent est rendu `alt=""`, comme dans les fichiers de la passe #17.
3. Entités décodées, U+00A0 conservées et comptées.
4. Cellules d'une même ligne de tableau associées sur une seule ligne par ` | `. **Dans une cellule,
   un lien ne casse pas la ligne** : casser la ligne casserait l'association année / chien / niveau,
   qui est précisément la donnée.

`<script>` et `<style>` sont ignorés : ni texte de la page, ni adresse à conserver.

## Preuve de fidélité — 30 zones comparées, 1 divergence

`verifier_concordance.py` compare les cinq zones de six pages aux `.md` de la passe #17. **29 zones
sur 30 sont identiques au caractère près**, y compris les comptes d'U+00A0 : `accueil` 22, `bhpl` 38,
`travail` 136, `placement` 3, `litterature` 1, `mentions-legales` 1.

La trentième est une **ligne du tableau de `../pages/travail.md`** :

```
référence (passe #17)                      outil
------------------------------------------ ------------------------------------------
2024 | ♂ 
[LIEN href=…IDchiens=5019312]Road Trip      2024 | ♂ [LIEN href=…IDchiens=5019312]Road
du Mont Brabant[/LIEN]                      Trip du Mont Brabant[/LIEN] | Selectifs
| Selectifs 2025                            2025
```

Dans `travail.html`, la cellule s'écrit `<td …><strong>…<span …>♂ </span>…<a href="…IDchiens=5019312">
…Road Trip du Mont Brabant…</a></strong></td>` : le caractère après le `♂` est une **U+00A0
littérale**, et il **n'y a aucun espace** entre elle et le lien. C'est **la seule cellule de tout le
tableau qui contienne un lien** : 207 cellules, une seule concernée. La passe #17 y a éclaté la ligne
en trois, ce que le §3.2.4 du contrat interdit — « l'association année / chien / niveau est une
donnée ».

**`../pages/travail.md` n'a pas été corrigé** : le §9 du contrat interdit de réécrire un fichier
existant, et la décision 46 d'`ETAT.md` veut qu'un écart soit *déclaré*, pas effacé. Il est déclaré
ici, et l'outil, lui, tient la règle.
