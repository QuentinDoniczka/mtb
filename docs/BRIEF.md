# BRIEF — Berger Hollandais du Mont Brabant

**Refonte du site [mtbrabant.com](https://www.mtbrabant.com/) en WordPress sur mesure, éditable sans
développeur.**

Ce document décrit le **QUOI**. Le **COMMENT** appartient à `brainstorm-mtb` et aux deux leaddev.
Tout agent le lit avant d'agir. Toute ambiguïté se pose à l'utilisateur — **jamais d'invention
silencieuse**, en particulier sur un fait d'élevage (nom, date, résultat de test, généalogie).

---

## 1. Le projet en une phrase

Reconstruire à l'identique le site de l'élevage de bergers hollandais du Mont Brabant, sur WordPress,
de façon que **l'éleveuse — qui n'est pas développeuse — puisse ajouter une portée, un chien, une
photo ou un résultat sans jamais toucher à du code**, là où le site IONOS actuel exige de recopier une
page entière à chaque événement.

## 2. Les deux publics

| Public | Qui | Ce qu'il fait |
|--------|-----|---------------|
| **Visiteur** | Familles cherchant un chiot, conducteurs de chiens de travail, éleveurs, curieux de la race | Lit les portées, les fiches de chiens, les résultats de travail ; appelle ou écrit |
| **Éditrice** | Fabienne Guéneau, propriétaire de l'élevage, non technicienne | Publie une nouvelle portée, met à jour une disponibilité, ajoute des photos, ajoute un résultat de concours, corrige un texte |

L'éditrice est le public **le plus important pour la conception**. Un site magnifique qu'elle ne sait
pas mettre à jour est un échec — c'est exactement la situation actuelle.

## 3. La règle d'or

> **Tout ce qui change dans la vie de l'élevage se modifie depuis l'administration WordPress,
> en remplissant des champs nommés en français, sans copier-coller de mise en page.**

Test de conception, à appliquer à chaque décision : *« Pour faire ce changement, est-ce qu'elle doit
ouvrir un fichier, dupliquer une page, ou recopier une mise en forme ? »* Si oui, la conception est
fausse — on recommence.

Corollaires :

- Une donnée saisie **une seule fois** apparaît partout où elle est utile (fiche portée, index des
  portées, encart d'accueil, fiche du père, fiche de la mère).
- Les listes, tris, comptages et « dernière portée » sont **calculés**, jamais saisis.
- Un composant se **retire** aussi facilement qu'il s'ajoute, sans casser la page.
- Aucun écran d'administration ne doit exiger de comprendre un terme technique. Les libellés sont
  ceux du métier : *portée*, *saillie*, *père*, *mère*, *cotation*, *dysplasie*, pas *custom field*
  ni *taxonomy*.

## 4. Les 4 contraintes non négociables

Un agent qui en viole une s'arrête et le signale.

| # | Contrainte | Conséquence concrète |
|---|-----------|----------------------|
| 1 | **Éditable sans code, par une non-développeuse** | Chaque élément récurrent du site est un type de contenu ou un composant paramétrable. Tout composant livré arrive avec sa fiche d'aide en français (`doc-client-mtb`). Aucune évolution courante ne demande d'éditer un fichier. |
| 2 | **Thème sur mesure + extension `mtb-core`, aucun page builder, aucun thème acheté** | Le contenu appartient à WordPress, pas à un outil tiers. C'est précisément ce qui rend le site IONOS actuel impossible à faire évoluer. Pas d'Elementor, Divi, WPBakery ; pas de thème du dépôt ; CSS écrit à la main. |
| 3 | **Le contenu structuré ne se recopie jamais** | Une portée = une saisie. La fiche, l'entrée d'index, l'encart d'accueil et les liens parents/enfants en découlent. Interdit : « dupliquer la page de la portée précédente et modifier le texte ». |
| 4 | **Rien de l'ancien site ne se perd** | Les 52 pages actuelles sont reprises : textes, photos, coordonnées, résultats, généalogies. URL conservées quand c'est possible, **redirection 301** sinon. Aucune page migrée « en résumé » : le texte de l'éleveuse est repris intégralement. |

Trois règles transverses de même niveau :

- **Accessibilité AA** — contrastes, navigation clavier, focus visible, textes alternatifs, 200 % de
  zoom, 360 px de large sans défilement horizontal. Le public inclut des personnes âgées sur mobile.
- **Zéro donnée personnelle inutile, zéro traceur** — pas de Google Analytics, pas de police
  distante, pas de captcha tiers, pas de bandeau cookie parce qu'aucun cookie n'est déposé au
  visiteur anonyme. Le formulaire de contact est le seul point de collecte, et il est minimal.
- **Exactitude du domaine** — aucun nom de chien, date, affixe, numéro LOF, résultat de test ou de
  concours n'est reformulé, corrigé ou inventé. On recopie, on ne réécrit pas.

## 5. Le modèle de contenu

C'est le cœur du projet. Trois types de contenu structurés, le reste en pages libres.

### 5.1 Portée (27 existantes, ~1 à 3 nouvelles par an)

Identifiée par une lettre et une année : `A3 2025`, `V1 2024`, … jusqu'à `L 1995`.

| Champ | Type | Note |
|-------|------|------|
| Identifiant | lettre(s) + chiffre optionnel + année | sert au titre et à l'URL |
| Date de naissance | date | |
| Père | lien vers une fiche Chien **ou** nom libre + élevage | les étalons extérieurs n'ont pas de fiche |
| Mère | lien vers une fiche Chien **ou** nom libre | |
| Tests de santé des parents | texte structuré | repris de la fiche quand le parent est une fiche Chien |
| Nombre de mâles / femelles | entiers | |
| Chiots | liste : nom, sexe, n° LOF, devenir | |
| Disponibilité | choix : *chiots disponibles* / *tous réservés* / *portée passée* | pilote l'affichage sur l'accueil et la page Placement |
| Galerie photos | images | |
| Texte libre | contenu riche | commentaire de l'éleveuse |

**Ce qui s'en déduit automatiquement** : l'index chronologique des portées, l'encart « dernière
portée » de l'accueil, le bloc « portées » sur la fiche du père et de la mère, l'état affiché sur la
page Placement.

### 5.2 Chien (17 fiches)

| Champ | Type |
|-------|------|
| Nom d'usage + nom complet (avec affixe) | texte |
| Sexe, date de naissance, date de décès éventuelle | |
| Variété | poil long / poil court |
| Taille, couleur, masque et génétique de robe | texte |
| Père × Mère | liens ou noms libres |
| Tests de santé | dysplasie hanches (HD), coudes (ED), LTV, DM, SDCA 1, SDCA 2, ADN identifié, diversité génétique % |
| Titres et brevets | TC, CSAU, cotation LOF, résultats d'exposition |
| Statut | reproducteur / retraité / disparu / en cours de confirmation |
| Galerie photos | |
| Lien pedigree externe | URL (LOF Select) |
| Texte libre | |

**Ce qui s'en déduit** : la page « La meute » regroupée par statut, la liste des portées du chien, les
mentions du chien dans les tableaux de travail.

### 5.3 Résultat de travail (~7 disciplines, 1994 → aujourd'hui)

Discipline (RING, IGP/RCI, Mondioring, obéissance, pistage, recherche utilitaire, sauvetage, truffe),
chien concerné, année, niveau ou titre obtenu, conducteur, pays si étranger.

**Ce qui s'en déduit** : les tableaux de la page Travail, triés et groupés par discipline ; le palmarès
sur la fiche du chien. Ajouter une ligne de résultat doit être l'affaire de trente secondes.

### 5.4 Pages libres

Accueil · BHPL (présentation de la race, santé, génétique des couleurs) · BHPL en France ·
Littérature · Travail (texte + tableaux) · La meute (index) · Placement · Contact ·
Mentions légales · Politique de confidentialité (à créer).

Ces pages se composent avec les **composants du catalogue** (§6).

## 6. Le catalogue de composants

Un composant = un bloc que l'éditrice insère dans une page depuis l'éditeur WordPress, qui a des
réglages simples (titre, texte, image, choix dans une liste) et **qui ne peut pas casser le design**.

Catalogue cible (à confirmer par la conception, pas figé ici) :

| Composant | Sert à |
|-----------|--------|
| Bandeau d'ouverture | Image + titre + accroche en haut d'une page |
| Encart « dernière portée » | Affiche automatiquement la portée la plus récente et sa disponibilité |
| Liste de portées | Filtrable par année ; l'éditrice choisit seulement combien en afficher |
| Grille de chiens | Choix du statut (reproducteurs, retraités…) ; se remplit toute seule |
| Fiche d'information | Bloc titre + paragraphes + image, la brique de base des pages de contenu |
| Tableau de résultats | Une discipline, rempli depuis les résultats saisis |
| Galerie photos | |
| Encart d'appel | « Nous contacter », téléphone, bouton |
| Bandeau d'alerte | Message temporaire (« portée à venir », « pas de chiots actuellement ») |
| Coordonnées + plan | Adresse, téléphone, courriel, carte statique auto-hébergée |

Règles de conception des composants :

- Chaque composant a une **fiche d'aide d'une page** : à quoi il sert, ce qu'on peut régler, où il
  s'utilise, où il ne s'utilise pas. Livrée avec le composant, pas après.
- Un composant mal rempli ne casse pas la page : il se masque ou affiche un état vide propre.
- Les couleurs, polices et espacements ne sont **pas** réglables. Ce sont les garde-fous du design.
- Le nom affiché du composant est en français et parle métier.

## 7. Reprise de l'existant

- **52 URL** au sitemap actuel (1 accueil, 27 portées + 2 pages BHPL, 17 chiens + index, travail,
  placement, contact, mentions légales).
- Les URL actuelles contiennent des accents (`/bhpl/portée-a3-2025/`). Décision à prendre par la
  conception : conserver à l'identique, ou normaliser **avec redirection 301** — jamais de rupture
  silencieuse.
- Photos : récupérées en pleine résolution, réimportées dans la médiathèque WordPress, nommées
  lisiblement, avec texte alternatif.
- Coordonnées à reprendre telles quelles : **3060 Route de Salernes, 83570 Entrecasteaux ·
  0680505619 · mtbrabant@gmail.com · © Fabienne Guéneau**.
- Le contenu rédactionnel (philosophie du travail, conseils aux familles, santé, génétique) est repris
  **intégralement**, sans résumé ni réécriture.

## 8. L'espace protégé par mot de passe

Le site comporte au moins une page dont l'accès est **protégé par un mot de passe** que l'éditrice
choisit et communique elle-même (pas de création de compte visiteur, pas de gestion d'utilisateurs).

- Mécanisme : la protection par mot de passe **native de WordPress**, appliquée à une page ou à une
  portée depuis l'écran d'édition.
- L'éditrice doit pouvoir protéger, changer le mot de passe et retirer la protection **seule** —
  c'est un point obligatoire du guide d'utilisation.
- Une page protégée n'apparaît ni dans les index publics, ni dans le sitemap, ni dans la recherche.
- L'usage précis (suivi d'une portée pour les familles, avant-première, documents d'élevage) est à
  confirmer avec l'éleveuse ; le mécanisme, lui, est le même dans tous les cas.

## 9. Formulaire de contact

- Champs : nom, courriel, message. Rien d'autre.
- Anti-spam **sans service tiers** : piège à robots (honeypot) + délai minimal de soumission +
  question simple si nécessaire. Pas de reCAPTCHA, pas d'appel externe.
- Le message part par courriel à l'élevage. Conservation en base : à trancher (si oui, purge
  automatique et mention dans la politique de confidentialité).
- Mention RGPD sous le formulaire : finalité, destinataire, durée, droit d'accès et de suppression.

## 10. Design

Le rendu doit ressembler à **un site d'éleveur passionné, pas à un modèle de site**. Ancrages :
le chien de berger au travail, la Provence verte (Entrecasteaux, Var), la matière — pelage, terre
battue, bois. Registre : sérieux, chaleureux, jamais mièvre, jamais « startup ».

Le détail appartient à `lead-design-mtb`, qui produit `design-system/MASTER.md` **avant tout travail
visuel** : palette nommée, deux familles typographiques auto-hébergées, échelle d'espacement, concept
de mise en page, un élément signature, autocritique écrite, preuve de contraste.

Les photos de chiens sont la matière première du site : le design est au service des images.

## 11. Accessibilité (AA, bloquant)

Contraste AA sur toute paire texte/fond · un seul `<h1>` par page et une hiérarchie de titres logique ·
parcours clavier complet, focus visible partout · lien d'évitement · `alt` utile sur chaque photo
(nom du chien, contexte) · cibles tactiles ≥ 44 px · aucune information portée par la couleur seule ·
zoom 200 % sans perte · 360 px sans défilement horizontal · `lang="fr"`.

## 12. Performance et hébergement

- Budget page : **HTML + CSS + JS < 200 Ko** hors photos ; **2 fichiers de police maximum** ;
  photos servies en formats modernes et dimensionnées, chargement différé sous la ligne de flottaison.
- Aucune requête vers un domaine tiers depuis le navigateur (polices, scripts, cartes, icônes).
- Développement et recette en **Docker** (WordPress + base de données + outils), reproductible d'une
  commande.
- **Hébergement de production : non tranché.** Les agents restent neutres : rien ne doit dépendre de
  Docker en production, ni d'une extension propriétaire d'hébergeur. Contrainte à respecter par
  défaut : le site doit pouvoir tourner sur un hébergement mutualisé PHP standard.

## 13. Ce qui est livré à l'éleveuse

Livrable à part entière, au même titre que le site :

1. **Guide d'utilisation en français**, avec captures : ajouter une portée · modifier une
   disponibilité · ajouter un chien · ajouter un résultat de concours · ajouter/supprimer un
   composant dans une page · ajouter des photos · protéger une page par mot de passe · modifier le
   menu.
2. **Une fiche par composant** du catalogue.
3. **Une page « en cas de doute »** : ce qu'elle peut faire sans risque, ce qu'il vaut mieux ne pas
   toucher, qui appeler.
4. Le guide est écrit au fur et à mesure par `doc-client-mtb`, jamais reporté à la fin.

## 14. Definition of Done

Une issue n'est terminée que si **toutes** les lignes qui la concernent sont vraies :

- **D1** — Ce qui est livré s'édite depuis l'administration WordPress, sans toucher un fichier.
- **D2** — Aucun contenu structuré n'exige de recopier une mise en page.
- **D3** — Le composant ou l'écran livré a sa fiche d'aide en français.
- **D4** — Le contenu repris de l'ancien site est complet et exact (textes, photos, dates, noms, LOF).
- **D5** — Les URL de l'ancien site répondent : identiques ou redirigées en 301.
- **D6** — Aucune requête navigateur vers un domaine tiers.
- **D7** — Accessibilité AA vérifiée sur ce qui est livré (contraste, clavier, focus, alternatives).
- **D8** — Budget de poids respecté, chiffres à l'appui.
- **D9** — Le site fonctionne dans la stack Docker, du premier démarrage à la page rendue.
- **D10** — Aucun page builder, aucun thème tiers, aucune extension payante introduite.
- **D11** — Aucune donnée de domaine inventée ; les incertitudes sont remontées, pas comblées.
- **D12** — Un contenu mal rempli n'affiche jamais une page cassée.

## 15. Questions ouvertes (à trancher avec l'éleveuse)

1. Usage exact de la page protégée par mot de passe.
2. Conservation des messages du formulaire en base, ou envoi par courriel uniquement.
3. Conservation des URL accentuées, ou normalisation avec redirections.
4. Hébergement de production et propriété du nom de domaine.
5. Souhaite-t-elle une rubrique « actualités » (aujourd'hui les nouvelles vivent dans l'accueil) ?
6. Les tarifs des chiots doivent-ils apparaître sur le site ?

Aucune de ces questions ne bloque le démarrage. Chacune bloque une issue précise, qui le signalera.
