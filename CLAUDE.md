# MTB — Berger Hollandais du Mont Brabant

Refonte du site [mtbrabant.com](https://www.mtbrabant.com/) en **WordPress sur mesure** : thème maison
+ extension dédiée, contenu structuré, **entièrement éditable par l'éleveuse, qui n'est pas
développeuse**.

**Source de vérité produit : [`docs/BRIEF.md`](docs/BRIEF.md).** Tout agent lit le brief avant d'agir.
Le brief décrit le QUOI ; le COMMENT appartient à `brainstorm-mtb` et aux deux leaddev.

## Reprise après un redémarrage de contexte

Ordre de lecture, sans exception :

1. **`docs/ETAT.md`** — où on en est, les décisions déjà prises, les questions en attente. C'est le fil
   du projet ; il se lit en vingt secondes et évite de re-litiger ce qui est tranché.
2. **`docs/BRIEF.md`** — le quoi.
3. **Le board GitHub** (`github-boards` → `get-board-status`) — l'état de vérité issue par issue.
4. `docs/contracts/` (contrats gelés), `docs/guide/` (ce que l'éleveuse sait déjà faire),
   `docs/migration/` (ce qui a été repris de l'ancien site).

`docs/ETAT.md` est mis à jour par `/lead-mtb` à la fin de chaque lot, et dès qu'une question ouverte est
tranchée. Un lot qui se termine sans mettre `ETAT.md` à jour n'est pas terminé.

---

## La règle d'or

> Tout ce qui change dans la vie de l'élevage — une portée, un chien, une photo, un résultat, un
> texte — se modifie depuis l'administration WordPress, en remplissant des champs nommés en français.

À chaque décision : *« pour faire ce changement, doit-elle ouvrir un fichier, dupliquer une page, ou
recopier une mise en forme ? »* Si oui, la conception est fausse. C'est exactement la douleur du site
IONOS actuel, où chaque portée est une page recopiée à la main.

## Les 4 contraintes non négociables

| # | Contrainte | Conséquence concrète |
|---|-----------|----------------------|
| 1 | **Éditable sans code** | Chaque élément récurrent est un type de contenu ou un composant paramétrable, livré avec sa fiche d'aide en français. |
| 2 | **Thème sur mesure + extension `mtb-core`** | Aucun page builder, aucun thème acheté ou du dépôt, aucune extension payante, aucun framework CSS générique. CSS écrit à la main. |
| 3 | **Le contenu structuré ne se recopie jamais** | Une portée = une saisie. Fiche, index, encart d'accueil et liens parents en découlent automatiquement. |
| 4 | **Rien de l'ancien site ne se perd** | 52 URL reprises ou redirigées en 301 ; textes, photos, dates, noms et numéros LOF migrés à l'identique. |

Trois règles transverses de même niveau :

- **Accessibilité AA bloquante** — contraste, clavier, focus visible, `alt` utile, zoom 200 %, 360 px
  sans défilement horizontal.
- **Zéro requête navigateur vers un domaine tiers, zéro traceur, zéro cookie au visiteur anonyme** —
  donc pas de bandeau de consentement. Polices, images, scripts : tout auto-hébergé.
- **Exactitude du domaine** — aucun nom, date, généalogie, résultat de test ou de concours n'est
  reformulé ni inventé. On recopie. Une incertitude est une question bloquante, pas un trou à combler.

## Architecture cible

```
wp-content/
├── themes/mtb/                  # thème sur mesure — présentation uniquement
│   ├── templates/               # gabarits (accueil, portée, chien, archives)
│   ├── parts/                   # en-tête, pied de page, fragments réutilisables
│   ├── patterns/                # compositions prêtes à insérer
│   ├── theme.json               # tokens issus de design-system/MASTER.md
│   └── assets/{css,js,fonts,img}
└── plugins/mtb-core/            # extension dédiée — contenu et logique métier
    ├── includes/content/        # types de contenu : portée, chien, résultat de travail
    ├── includes/fields/         # écrans de saisie en français, validation
    ├── includes/blocks/         # composants du catalogue (rendu serveur)
    ├── includes/query/          # fonctions de lecture exposées au thème
    ├── includes/migration/      # reprise du contenu de l'ancien site
    └── includes/admin/          # simplification de l'admin, aide contextuelle
```

**Frontière stricte** : le thème ne contient aucune règle métier et n'interroge jamais la base
directement — il appelle les fonctions de lecture de l'extension. L'extension ne produit aucune mise
en page décorative ; elle rend la structure des composants, le thème l'habille.

**Pourquoi une extension et pas tout dans le thème** : le contenu (portées, chiens, résultats) doit
survivre à un changement de thème. C'est la garantie que le site ne sera jamais prisonnier de sa
présentation — le piège du site actuel.

## Domaines fonctionnels (labels des issues)

`portees` · `chiens` · `travail` · `blocs` · `theme` · `contenu` · `contact` · `prive` · `design` ·
`seo` · `a11y` · `perf` · `infra` · `doc`

## Chaîne d'agents

Commande orchestratrice : **`/lead-mtb`**. Elle ne code jamais et n'exécute aucune chaîne elle-même :
elle constitue un lot de 3 issues, lance **3 chaînes complètes en parallèle**, puis valide le lot.

```
                            /lead-mtb
                                │
        ┌───────────────────────┼───────────────────────┐
        ▼                       ▼                       ▼
  lead-issue-mtb          lead-issue-mtb          lead-issue-mtb
     issue #A                issue #B                issue #C
        │                       │                       │
  brainstorm-mtb             (idem)                  (idem)
  leaddev-back-mtb ∥ leaddev-front-mtb
  gel du contrat  →  docs/contracts/issue-<n>.md
  dev-back-mtb ∥ dev-front-mtb ∥ dev-ux-mtb
  contenu-mtb                (si l'issue reprend du contenu de l'ancien site)
  refacto-mtb
  dev-integration-mtb        (si thème ET extension touchés)
  doc-client-mtb             (si l'éditrice voit quelque chose de nouveau)
  git-mtb commit
        │                       │                       │
        └───────────────────────┼───────────────────────┘
                                ▼
                  niveau lot, une seule fois :
        test-integration-mtb → review-mtb → docker-mtb
                → git-mtb push → github-boards
```

- Chaque `lead-issue-mtb` porte **une seule issue** de bout en bout, dans son propre contexte — c'est
  ce qui préserve la qualité par rapport à un orchestrateur unique qui ferait tout.
- `lead-design-mtb` tourne **une seule fois** au bootstrap (et sur révision explicite) — il produit
  `design-system/MASTER.md`, préalable à tout travail visuel.
- `docker-mtb` tourne au bootstrap (création de la stack) et en fin de lot (vérification).
- `doc-client-mtb` tourne **dans la chaîne**, pas à la fin du projet : un composant sans sa fiche
  d'aide n'est pas terminé (D3).
- **Lots de 3 issues maximum** (contrainte de tokens). Parallèle uniquement si les empreintes fichiers
  des 3 issues sont disjointes ; sinon séquentiel. Arbre de travail unique, aucune isolation.
- **Pas de tests unitaires.** Un seul agent de test, une fois par lot, en intégration dans Docker.

## Conventions

- **Langue** : français pour l'interface, l'administration, le contenu, la documentation, les commits
  et les échanges avec l'utilisateur ; anglais dans les prompts d'agents.
- **Vocabulaire d'administration** : métier, jamais technique. *Portée*, *saillie*, *père*, *mère*,
  *cotation*, *disponibilité* — pas *custom post type*, *meta*, *taxonomy*.
- **Git** : **mono-branche**. Tout est commité et poussé directement sur `main` — pas de branche
  feature, pas de PR, pas de worktree.
  Conséquence : les chaînes parallèles partagent le même arbre de travail. La **disjonction des
  empreintes fichiers** est la seule protection contre l'écrasement mutuel ; les commits mi-lot passent
  par `commit-scoped` avec une liste de fichiers explicite.
- **Commits** : Conventional Commits en français, scope = domaine fonctionnel, référence d'issue en
  fin de sujet (`feat(portees): saisie d'une portée en un seul écran (closes #12)`).
- **PHP** : WordPress Coding Standards, préfixe `mtb_` / namespace `MTB\`, `declare(strict_types=1)`,
  échappement systématique en sortie (`esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`),
  assainissement systématique en entrée, `$wpdb->prepare` pour toute requête, nonces sur toute écriture.
- **Licence** : GPL v2 or later pour le thème et l'extension.
- **Ambiguïté** : question à l'utilisateur, jamais d'invention silencieuse.
