# ÉTAT DU PROJET — journal de bord

**Ce fichier est la première chose à lire après un redémarrage, avant le board GitHub.**
Il dit où on en est, ce qui a été décidé, et ce qui bloque. Il est mis à jour par `/lead-mtb` à la fin
de chaque lot, et par l'utilisateur quand il tranche une question.

Il ne remplace pas le board : le board porte le détail des issues, ce fichier porte le fil.

---

## Où on en est

**Phase : bootstrap non démarré.**

| Étape | État |
|-------|------|
| Brief produit (`docs/BRIEF.md`) | ✅ écrit |
| Chaîne d'agents (`.claude/`) | ✅ 17 agents + `/lead-mtb` |
| Dépôt git | ❌ **pas encore initialisé** |
| Board GitHub (issues + milestones) | ❌ pas encore créé |
| Design system (`design-system/MASTER.md`) | ❌ pas encore produit |
| Stack Docker (`compose.yaml`) | ❌ pas encore créée |
| Extension `mtb-core` | ❌ rien |
| Thème `mtb` | ❌ rien |
| Reprise du contenu (52 URL) | ❌ rien |
| Guide de l'éleveuse (`docs/guide/`) | ❌ rien |

**Prochaine action** : lancer `/lead-mtb`. Son bootstrap fait, dans l'ordre : `git init` → création du
board et découpage du brief en epics de 3 issues → `design-system/MASTER.md` → stack Docker → premier lot.

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

## Questions ouvertes qui attendent l'utilisateur ou l'éleveuse

Reprises du §15 du brief. Aucune ne bloque le bootstrap ; chacune bloque une issue précise.

| # | Question | Bloque | État |
|---|----------|--------|------|
| Q1 | Usage exact de la page protégée par mot de passe (familles de chiots / avant-première / documents d'élevage) | l'issue `prive` | ⏳ en attente |
| Q2 | Point de départ du design : reprendre `styles/style5` « Sauge et calcaire » (filet double sauge + laiton) ou repartir de zéro | `lead-design-mtb` (bootstrap) | ⏳ en attente |
| Q3 | Conservation des messages du formulaire en base, ou envoi par courriel uniquement | l'issue `contact` | ⏳ en attente |
| Q4 | URL accentuées conservées (`/bhpl/portée-a3-2025/`) ou normalisées avec redirections 301 | l'issue `seo` | ⏳ en attente |
| Q5 | Hébergement de production et propriété du nom de domaine | la mise en ligne | ⏳ en attente |
| Q6 | Rubrique « actualités » séparée ? Tarifs des chiots affichés ? | le plan de site | ⏳ en attente |
| Q7 | Nom du dépôt GitHub à créer (privé ou public) | `git-mtb sync-remote` | ⏳ en attente |

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
| — | — | — | *rien pour l'instant* | — |
