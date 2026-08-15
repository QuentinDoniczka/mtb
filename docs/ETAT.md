# ÉTAT DU PROJET — journal de bord

**Ce fichier est la première chose à lire après un redémarrage, avant le board GitHub.**
Il dit où on en est, ce qui a été décidé, et ce qui bloque. Il est mis à jour par `/lead-mtb` à la fin
de chaque lot, et par l'utilisateur quand il tranche une question.

Il ne remplace pas le board : le board porte le détail des issues, ce fichier porte le fil.

---

## Où on en est

**Phase : bootstrap terminé le 2026-08-15. Premier lot (#1 #2) en cours.**

| Étape | État |
|-------|------|
| Brief produit (`docs/BRIEF.md`) | ✅ écrit |
| Chaîne d'agents (`.claude/`) | ✅ 17 agents + `/lead-mtb` |
| Dépôt git | ✅ `main` sur `git@github.com:QuentinDoniczka/mtb.git` — commit d'amorçage `38d0935` |
| Board GitHub (issues + milestones) | ✅ 10 epics, 25 issues — [projet 10](https://github.com/users/QuentinDoniczka/projects/10) |
| Design system (`design-system/MASTER.md`) | ✅ 16 sections — vocabulaire en §10 |
| Stack Docker (`compose.yaml`) | ✅ 4 services, boot vérifié — `cp .env.example .env && make up` |
| Extension `mtb-core` | ⚠️ **squelette placeholder** posé par `docker-mtb` pour que la stack démarre — à écraser par #1 |
| Thème `mtb` | ⚠️ **squelette placeholder** (thème classique) posé par `docker-mtb` — à écraser par #2, qui livre un thème **de blocs** |
| Reprise du contenu (52 URL) | ❌ rien |
| Guide de l'éleveuse (`docs/guide/`) | ❌ rien |

**Prochaine action** : `/lead-mtb #3 #4 #5` (epic 2 — les trois types de contenu), une fois le lot #1 #2
clos. Les trois questions qui bloquaient ce lot (Q8, Q9, Q10) sont tranchées.

**Comptes de développement** — `make up` puis http://localhost:8080/wp-admin/ : `admin`/`mtb-dev-admin`,
éditrice `fabienne`/`mtb-dev-editrice` (rôle **Éditeur** natif, délibérément pas Administrateur).
Attrape-courriels Mailpit sur http://localhost:8025.

---

## Décisions prises (ne pas les re-litiger sans raison)

| # | Décision | Date | Pourquoi |
|---|----------|------|----------|
| 1 | WordPress, **thème `mtb` sur mesure + extension `mtb-core`** | 2026-08-14 | Le contenu doit survivre au thème ; c'est ce qui rend le site IONOS actuel impossible à faire évoluer |
| 2 | **Champs et blocs faits maison**, aucun plugin payant (pas d'ACF Pro) | 2026-08-14 | Zéro dépendance, zéro licence à renouveler, interface de saisie entièrement maîtrisée |
| 3 | Trois types de contenu : **Portée, Chien, Résultat de travail** | 2026-08-14 | Ce sont les trois choses que l'éleveuse ajoute réellement |
| 4 | **Docker en développement**, hébergement de production non tranché | 2026-08-14 | Rien ne doit dépendre de Docker : le site doit tourner sur du mutualisé PHP standard |
| 5 | Page protégée = **mot de passe natif WordPress** | 2026-08-14 | Aucun compte à créer, aucune extension, elle le fait seule depuis l'écran d'édition |
| 6 | **Mono-branche** : tout sur `main`, pas de PR | 2026-08-14 | Repris du fonctionnement de la chaîne d'agents |
| 7 | Le guide de l'éleveuse s'écrit **pendant** les chaînes (D3), pas à la fin | 2026-08-14 | Un composant sans sa fiche d'aide n'est pas terminé |
| 8 | Design : **direction `styles/style5` « Sauge et calcaire » conservée, structure entièrement refaite** | 2026-08-15 | On garde ce qui a de la valeur (palette et ses ratios WCAG mesurés, filet double, médaillons ronds, registre botanique sévère). On jette ce qui n'était qu'un contournement du CMS IONOS : gabarit 940 px, colonne 239 px, piles de polices système, `display:contents` sur le menu. En thème sur mesure tout le HTML et le CSS nous appartiennent — mise en page moderne libre, deux familles typographiques **auto-hébergées** (§12 : 2 fichiers de police maximum, zéro requête tierce). |
| 13 | Le **statut d'un chien s'accorde au sexe** sur les écrans : *Reproductrice / Retraitée / Disparue* pour une femelle, *Reproducteur / Retraité / Disparu* pour un mâle | 2026-08-15 | Se lit naturellement en français sur une fiche de femelle. Coût assumé : le libellé affiché dépend d'un autre champ, à porter dans le contrat de l'issue #4. |
| 12 | **Cotation LOF et dysplasie (HD/ED) = champs en texte recopié**, pas de liste fermée | 2026-08-15 | Les grilles officielles ne sont nulle part dans le brief. Inventer une liste ferait courir deux risques : une grille qui n'existe pas, et une valeur réelle impossible à saisir — donc une éleveuse bloquée. Elle recopie le document officiel. Contrepartie assumée : pas de vérification de saisie, tri moins fiable. Rouvrable si les grilles sont fournies un jour. |
| 11 | **Huit disciplines de travail** : RING, IGP/RCI, Mondioring, obéissance, pistage, recherche utilitaire, sauvetage, truffe | 2026-08-15 | Le brief §5.3 annonce « ~7 » puis en énumère huit ; l'énumération fait foi, le « ~7 » était une approximation d'écriture. |
| 10 | **Tout composant tableau rendu côté serveur émet `data-libelle="…"` sur chaque `<td>`**, avec exactement le libellé de colonne de `MASTER.md` §10 | 2026-08-15 | C'est ce qui permet aux tableaux (résultats de travail, chiots d'une portée) de se déplier en lignes libellées sous 48 rem, **sans conteneur à défilement horizontal**. Sans l'attribut, les tableaux sont illisibles sur téléphone — donc échec de la contrainte 360 px. À porter dans le contrat gelé des issues #5, #15 et #3. |
| 9 | **Aucun index central à éditer à la main** dans `mtb-core` : chargeur à auto-découverte des sous-dossiers de `includes/{content,fields,blocks,query,migration,admin}/` | 2026-08-15 | C'est la condition technique du parallélisme. Un index de blocs édité à la main serait touché par presque toute issue visuelle et ferait entrer en collision des chaînes pourtant censées être disjointes. Conséquence : un bloc = un dossier auto-enregistré ; `functions.php` n'est touché que par #2 puis #18, jamais dans le même lot. |

## Questions ouvertes qui attendent l'utilisateur ou l'éleveuse

Reprises du §15 du brief. Aucune ne bloque le bootstrap ; chacune bloque une issue précise.

| # | Question | Bloque | État |
|---|----------|--------|------|
| Q1 | Usage exact de la page protégée par mot de passe (familles de chiots / avant-première / documents d'élevage) | l'issue `prive` | ⏳ en attente |
| Q2 | ~~Point de départ du design~~ | — | ✅ tranchée 2026-08-15 : voir décision 8 |
| Q3 | Conservation des messages du formulaire en base, ou envoi par courriel uniquement | l'issue `contact` | ⏳ en attente |
| Q4 | URL accentuées conservées (`/bhpl/portée-a3-2025/`) ou normalisées avec redirections 301 | l'issue `seo` | ⏳ en attente |
| Q5 | Hébergement de production et propriété du nom de domaine | la mise en ligne | ⏳ en attente |
| Q6 | Rubrique « actualités » séparée ? Tarifs des chiots affichés ? | l'issue #18 (navigation et plan de site) | ⏳ en attente |
| Q8 | ~~« ~7 disciplines » ou huit ?~~ | — | ✅ tranchée 2026-08-15 : voir décision 11 |
| Q9 | ~~Cotation LOF et dysplasie : texte ou liste ?~~ | — | ✅ tranchée 2026-08-15 : voir décision 12 |
| Q10 | ~~Le statut s'accorde-t-il au sexe ?~~ | — | ✅ tranchée 2026-08-15 : voir décision 13 |
| Q11 | Aucun fichier italique : le budget de 2 fichiers de police (§12) est consommé par les deux romains. L'italique reste synthétique et n'est jamais un dispositif de design. Rouvrable. | rien — arbitrage `design` | ⏳ pour information |
| Q7 | ~~Nom du dépôt GitHub à créer~~ | — | ✅ tranchée 2026-08-15 : `git@github.com:QuentinDoniczka/mtb.git`, fourni par l'utilisateur |

## Faits du domaine à ne jamais réinventer

Vérifiés sur le site source le 2026-08-14. Toute autre donnée d'élevage se lit dans
`docs/migration/source/`, jamais de mémoire.

- Élevage **Berger Hollandais du Mont Brabant**, affixe « du Mont Brabant », Fabienne Guéneau.
- 3060 Route de Salernes, 83570 Entrecasteaux · 0680505619 · mtbrabant@gmail.com
- Poil long (BHPL) et poil court. 27 portées de `L 1995` à `A3 2025`, 17 fiches de chiens.
- Site source : 52 URL au sitemap, slugs accentués.

## Journal des lots

| Lot | Epic | Issues | Résultat | Commit |
|-----|------|--------|----------|--------|
| 0 | Bootstrap | — | Dépôt + board (10 epics, 25 issues) + `MASTER.md` + stack Docker vérifiée | `38d0935` puis amorçage |
