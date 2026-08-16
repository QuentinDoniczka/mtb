# ÉTAT DU PROJET — journal de bord

**Ce fichier est la première chose à lire après un redémarrage, avant le board GitHub.**
Il dit où on en est, ce qui a été décidé, et ce qui bloque. Il est mis à jour par `/lead-mtb` à la fin
de chaque lot, et par l'utilisateur quand il tranche une question.

Il ne remplace pas le board : le board porte le détail des issues, ce fichier porte le fil.

---

## Où on en est

**Phase : lot 1 clos le 2026-08-16. Le socle existe. Prochain lot : les trois types de contenu.**

| Étape | État |
|-------|------|
| Brief produit (`docs/BRIEF.md`) | ✅ écrit |
| Chaîne d'agents (`.claude/`) | ✅ 17 agents + `/lead-mtb` |
| Dépôt git | ✅ `main` sur `git@github.com:QuentinDoniczka/mtb.git` — commit d'amorçage `38d0935` |
| Board GitHub (issues + milestones) | ✅ 10 epics, 25 issues — [projet 10](https://github.com/users/QuentinDoniczka/projects/10) |
| Design system (`design-system/MASTER.md`) | ✅ 16 sections — vocabulaire en §10 |
| Stack Docker (`compose.yaml`) | ✅ 4 services, boot vérifié — `cp .env.example .env && make up` |
| Extension `mtb-core` | ✅ squelette + chargeur à auto-découverte (#1) — aucun type de contenu encore |
| Thème `mtb` | ✅ thème **de blocs**, `theme.json` verrouillé, CSS à la main, 2 polices auto-hébergées (#2) |
| Reprise du contenu (52 URL) | ❌ rien |
| Guide de l'éleveuse (`docs/guide/`) | ❌ rien |

**Prochaine action** : `/lead-mtb #3 #4 #5` (epic 2 — les trois types de contenu). Empreintes
**disjointes**, vérifiées par `github-boards` après le lot 1 : trois chaînes en parallèle, sans réserve.
Les trois questions qui bloquaient ce lot (Q8, Q9, Q10) sont tranchées.

Board : **27 issues**, milestone 1 fermé, milestone 12 « Dette technique » ouvert (#26 `scandir`, #27 réécritures).

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
| 15 | **D6 est tenue pour le visiteur, et bornée explicitement à lui.** Dans l'administration, l'éditeur du cœur charge **15 images** depuis `s.w.org` (10 pour le guide de bienvenue, 5 pour les aperçus de blocs de l'insérteur). **Aucune n'est supprimée, et c'est délibéré.** | 2026-08-16 (révisée le jour même) | Le brief §4 formule la règle « zéro traceur, zéro cookie » autour du **visiteur anonyme** et de l'absence de bandeau de consentement : c'est tenu et vérifié — zéro origine externe, zéro `Set-Cookie` sur le site public. Pour les 5 images de l'insérteur, le zéro absolu était atteignable en retirant les `example` des `block.json` du cœur, mais il retirerait à Fabienne les **aperçus visuels des blocs** au moment précis où elle cherche quoi insérer : contrainte 1 contre une fuite d'IP, la contrainte 1 gagne. **Ma rédaction initiale annonçait en plus le guide de bienvenue « écarté par défaut » : c'était faux, aucune ligne ne l'implémentait.** Le seul mécanisme disponible s'appuie sur la préférence `welcomeGuide` du méta `wp_persisted_preferences` — un détail interne du cœur, pas une API stable, qui céderait **en silence** à une mise à jour en laissant un contrat menteur derrière lui. Un écart de 15 images honnêtement nommé vaut mieux qu'un correctif fragile. Si l'écartement est un jour voulu, il appartient à une issue infra/admin sur **`mtb-core`**, qui survit à un changement de thème. **Rouvrable** si l'hébergement de production (Q5) impose plus strict. |
| 14 | **Polices conservées telles que livrées : 147 548 octets** (Newsreader 124 184 + Public Sans 23 364), au-dessus de la cible de 100 Ko de `MASTER.md` §4.1 | 2026-08-16 | La contrainte du brief §12 est un **nombre de fichiers — deux maximum — et elle est tenue** ; le budget chiffré de 200 Ko du brief porte sur HTML + CSS + JS, à 29 Ko. Les 100 Ko sont une **cible** interne au design system, et `MASTER.md` l'écrit ainsi. Quatre pistes de sous-ensemble ont été mesurées : la seule qui repasse dessous (97 344 o) exige de brider l'axe optique à 36 px, alors que les `h1` montent à 80 px — on paierait la cible avec la propriété même qui justifiait le choix de Newsreader. Écart assumé et documenté dans `docs/contracts/issue-2.md`. Ce qui protège le public du brief (personnes âgées sur mobile), c'est le préchargement, la même origine et `font-display`, pas 20 Ko de moins. |
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

## Dettes ouvertes — créées par un lot, payées par un autre

Ne pas les redécouvrir dans trois lots. Chacune est déjà écrite dans le contrat de l'issue qui l'a créée.

| # | Dette | Créée par | Payée par |
|---|-------|-----------|-----------|
| **T1** | **Fabienne ne pourra pas modifier son menu.** Dans un thème de blocs, le menu est un bloc Navigation de `parts/header.html`, éditable uniquement par l'éditeur de site — qui exige `edit_theme_options`, capacité qu'un rôle Éditeur n'a pas (vérifié dans la stack). Or le BRIEF §13 fait de « modifier le menu » une ligne du guide. **C'est la règle d'or qui est touchée** : à traiter comme telle, pas comme un détail de permission. | #2 | **#18** |
| **T2** | **Le lien d'évitement dépend du JavaScript** : le cœur l'injecte par script depuis WP 6.4, et sa cible `<main>` n'est pas focalisable. La ligne « lien d'évitement » de **D7 n'est pas cochée**. Se solde par un lien écrit à la main dans `parts/header.html` avec `tabindex="-1"` sur la cible. Le CSS est déjà écrit et bat celui du cœur. | #2 | epic Gabarits |
| **T3** | **L'accueil et la page de recherche n'ont aucun `<h1>`** : `templates/index.html` emploie `wp:query-title {"type":"archive"}`, qui ne rend rien sur l'index du blog ni sur la recherche. Les pages seules en ont bien un. L'accueil de production sera une Page, donc couvert — mais la page de recherche, non. | #2 | epic Gabarits |
| **T4** | **D6 n'est tenue que pour le visiteur, pas dans l'administration.** L'éditeur du cœur charge 15 images depuis `s.w.org` (10 pour le guide de bienvenue, 5 pour les aperçus de blocs de l'insérteur), aucune n'est supprimée. Le site public est irréprochable : zéro origine externe, zéro cookie anonyme. Voir décision 15. | #2 | facultatif — une issue infra/admin sur `mtb-core` si l'écartement du guide est un jour voulu |
| **T5** | `class-loader.php` emploie `scandir()` que `functions.php` déclare interdit pour cause de portabilité mutualisée. **Les deux moitiés du lot posent l'hypothèse inverse.** Si celle du chargeur est fausse, `scandir` renvoie `false` et **l'extension ne charge rien, en silence**, sur un site qui répond 200. | #1 et #2 | avant la mise en ligne (lié à Q5) |
| **T6** | L'empreinte du chargeur ne couvre que les types et taxonomies : **aucune voie conforme** pour une issue qui ajoute une règle de réécriture sans type `mtb_`. La parade manuelle (Réglages → Permaliens) exige `manage_options`, que Fabienne n'a pas. | #1 | à traiter avant `seo` (#24) et `prive` (#23) |
| **T7** | Le bloc Bouton du cœur rend `#32373c` / `#fff`, **hors des quinze jetons**, atteignable en un clic. `base.css` n'habille que `button` et `input[type=submit]`, jamais `.wp-block-button__link` qui est un `<a>`. | #2 | première issue de composants |
| **T8** | Le contrat #2 déclare « BRIEF §8 satisfait » : **vrai pour le formulaire natif, faux pour l'exclusion**. Le sitemap du cœur ne filtre pas `has_password` et la recherche native retourne les contenus protégés. Rien ne fuit aujourd'hui (aucun contenu protégé). | #2 | **#23** (`prive`) |

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
| 1 | 1. Infrastructure | #1, #2 | Extension `mtb-core` (chargeur à auto-découverte) + thème de blocs `mtb` (`theme.json` verrouillé, 2 polices auto-hébergées). Review **OK avec réserves**, D9 vérifié à froid. Milestone 1 fermé. 8 dettes tracées, 2 devenues issues (#26, #27). | `93dc6a5` |
