# ÉTAT DU PROJET — journal de bord

**Ce fichier est la première chose à lire après un redémarrage, avant le board GitHub.**
Il dit où on en est, ce qui a été décidé, et ce qui bloque. Il est mis à jour par `/lead-mtb` à la fin
de chaque lot, et par l'utilisateur quand il tranche une question.

Il ne remplace pas le board : le board porte le détail des issues, ce fichier porte le fil.

---

## Où on en est

**Phase : lot 16 (#33, hisser `.mtb-dispo`, `.mtb-photo` et l'`object-position` dans une feuille
partagée — dette T18) livré, testé et revu le 2026-09-02. L'issue est fermée.** Quatre commits,
`92f3202` → `242d603`. Intégration : **42 vérifications, 42 passées, 0 échec**. Revue **OK avec
réserves — 0 CRITICAL, 0 HIGH**, 6 MEDIUM et 4 LOW, dont **les quatre MEDIUM qui étaient dans
l'empreinte ont été corrigés avant le push**.

**La leçon du lot tient en une phrase : ce lot est le premier où une mesure a réfuté un contrat
gelé, et où c'est le code, écrit contre le contrat, qui avait raison.** Le contrat §5 rendait
obligatoire un sélecteur doublé (0,3,0) au motif que la toile de l'éditeur préfixe `base.css` par
`.editor-styles-wrapper` et pas les feuilles de blocs. **La toile est une iframe : le cœur n'y
préfixe rien.** Mesuré deux fois indépendamment — par la chaîne, qui a livré `a8bec34` en
conséquence, puis par la passe d'intégration au Chrome réel, qui compte **0 sélecteur préfixé sur
les 11** présents dans la toile et établit que retirer la règle 13.10 du CSSOM n'y change aucun
pixel. Le doublage **reste en place** : il gagne dans les trois contextes, avec ou sans préfixe, et
l'inertie d'un motif n'est pas une raison de le retirer. Voir décision 66 pour l'erratum.

**Ce que #33 livre.** Les trois exemplaires de `.mtb-dispo` nue, les trois de `.mtb-photo` nue et
**sept** `object-position` — l'énoncé de l'issue et ce journal en annonçaient **cinq**, le recensement
de la chaîne a rétabli sept — deviennent **une** primitive au §13 de `base.css`, et quatre
`object-position` survivantes justifiées **par disjonction d'élément** : aucun de ces éléments ne
porte `mtb-photo`. Un seul crochet scopé survit, `blocs/mtb-derniere-portee.css:126`, doublé à
(0,3,0) pour le `white-space: nowrap`. Domicile choisi sur **décision 30** (`issue-16.md:519-520` :
une primitive nommée par `MASTER.md` s'écrit en classe nue, une seule fois, dans `base.css`) et sur
le précédent du §10. `MASTER.md` passe en **1.5** : le rembourrage du badge y est chiffré.

**L'arbitrage de valeur mérite d'être retenu, parce qu'il va contre la majorité.** Trois feuilles
écrivaient le rembourrage du badge, deux d'une façon et une de l'autre. La chaîne a retenu **la
minoritaire** — `gap: var(--e-1)`, `padding: var(--e-1) var(--e-2)` — après avoir lu que
`fiches.css:117-119` **déclarait avoir recopié** `mtb-derniere-portee.css:96` pour rester
littéralement identique : il n'y avait donc pas trois écritures indépendantes, mais **une
proposition écrite deux fois contre une autre**. Trancher par la fréquence aurait reconduit
exactement le défaut que #33 répare.

**Ce que l'éleveuse voit changer sans avoir rien fait** : le badge de disponibilité de l'accueil et
des deux fiches **rétrécit de 12 px en largeur et de 8 px en hauteur**. Aucune capture du guide ne
le montre — les quatre fiches qui décrivent le badge décrivent le mot, jamais la boîte — donc
**le guide reste vrai sans retouche**, et D3 n'avait rien à livrer. Effet de bord mesuré et
favorable : sur `/chien/demo-luna/`, le badge « Tous réservés » passe de deux lignes à une, cessant
de heurter le `max-inline-size` de 200 px de `fiches.css`.

**Le commit de correction ne change que des commentaires, et c'est prouvé, pas déclaré.** Les cinq
`*.min.css` régénérés par `242d603` gardent une **taille et une empreinte d'artefact identiques** au
champ près (`11980:dc5ba…`, `1382:068e…`, `1581:bdd2…`, `1628:f265…`, `5785:07cb…`) ; seule bouge la
moitié « source » du marqueur. **Le CSS servi au navigateur est le même octet pour octet**, donc le
42/42 de l'intégration vaut encore pour l'arbre poussé sans avoir été rejoué. C'est la garde à
quatre champs de #40 qui rend cette preuve possible.

**Deux de mes propres ancres ne survivent pas à la mesure, et la chaîne a eu raison de refuser.**
Je lui ai transmis « `base.css` cite la l. 124, le vrai est 123 » : la l. 123 est **vide**, la 124
est bien `img,` — le texte était déjà juste, et le « corriger » aurait **fabriqué** l'erreur. Je lui
ai transmis dans le même message que « `§4.5 l. 334` est exact » : la 334 est « Tableau, en-tête »,
**la 335 est « Bouton, badge »**. J'ai donc affirmé faux dans les deux sens — une correction
inutile et une exactitude imaginaire — dans une consigne qui lui demandait précisément de mesurer
elle-même plutôt que de me croire. Elle l'a fait. C'est la troisième fois de suite que le lead se
trompe sur une ancre et qu'une mesure l'attrape.

**Lignes de DoD non vérifiées par ce lot, à ne jamais présenter autrement** : **D4** et **D5** sans
objet (rien de la migration ni des réécritures n'est touché) · **D9** au sens fort — la pile n'a pas
été démarrée à froid, volume partagé préservé sur ma consigne · **D11** jugée par la revue, non
mécanisable. À quoi s'ajoute le non-vérifié propre à ce lot, et il est visuel : **personne n'a jugé
la nouvelle boîte du badge à l'œil sur le site rendu.** Elle est mesurée, contrastée AA sur ses
trois états, sans débordement à 360 px ni à 200 % — mais l'asymétrie optique de 1,9 px assumée par
`MASTER.md` et la lisibilité de la pastille à 2,2 interlettres ne se jugent qu'à l'écran, et
`MASTER.md:429-433` le dit lui-même : « si une mesure sur le site rendu montre le contraire, c'est
une question ». **C'est le seul point que je remonte à l'utilisateur.**

**Stack Docker vérifiée à volume conservé** : les 4 services `healthy` — `wordpress` étant observé
repassant de lui-même de *unhealthy* à *healthy*, le `timeout: 5s` du healthcheck étant plus court
que le TTFB réel de 9 à 25 s sur ce bind mount Windows, connu et non imputable au lot. `build` sans
erreur, `make css-check` **exit 0 sur 15 paires**, accueil et **les 10 portées en 200** (dont
`demo3-1995`, à moitié remplie — D12), fiche chien en 200, `debug.log` **vide** après une douzaine
de rendus. **Un orphelin `mtb-wpcli-run-b69aa70b344f` vieux de 9 h a été trouvé et retiré par moi**,
volumes `mtb_db_data` et `mtb_wp_data` vérifiés intacts après coup — **T94 reste ouverte et sa cause
intacte** : le point d entrée de `wpcli` ignore toujours la commande qu on lui passe, donc toute
chaîne future refera le geste sans être avertie.

**Prochaine action** : `/lead-mtb #32`, **seule**. C'est la seule issue ouverte que ne bloque aucune
question du §15.

```
/lead-mtb #32
```

- **#32** — habillage des écrans de saisie, personne ne le porte · **empreinte non arrêtée** :
  `wp-content/plugins/mtb-core/includes/admin/**` **ou** `wp-content/themes/mtb/assets/css/**`.
  Le périmètre est **à trancher par l'issue elle-même**, pas par l'orchestrateur — c'est une
  dérogation éventuelle à la frontière du contrat #1 §8, donc un arbitrage de conception.

**Bloquées, à exclure de tout lot jusqu'à réponse** : #23 par **Q1** · #24 par **Q4** · #26 et #48
par **Q5** · #42 par **Q14**. **Quatre réponses débloqueraient cinq issues sur les sept restantes** :
c'est désormais le goulot du projet, devant toute considération technique. #25 (assemblage du guide)
ne porte aucune question bloquante mais **suit** #23 et #24, dont elle documentera les livrables.

**Reste au board après #33** : **7 issues ouvertes** — 4 de dette (#48, #42, #32, #26) et 3 de
fonctionnalité (#25, #24, #23). Trois milestones ouverts : 9, 10 et 12.

**Une seule question en attente pour toi** : regarder le badge de disponibilité à l'écran, sur
l'accueil et sur une fiche portée, et dire si sa nouvelle boîte est juste à l'œil.

**Phase : lot 15 (#34, rendre au thème toute la feuille de la galerie — dette T15) livré, testé et
revu le 2026-09-01. L'issue est fermée.** Six commits, `a171034` → `0a47ebc`, **dont un seul porte du
code**. Revue **OK — 0 CRITICAL, 0 HIGH**, 1 MEDIUM **corrigé avant le push**, 5 LOW. Intégration :
**80 vérifications, 78 passées**, les deux échecs antérieurs au lot et déjà déclarés (D5 non posée,
T71). **Aucune régression imputable à #34.**

**La leçon du lot est inconfortable et vaut d'être écrite ainsi : c'est le lot où le lead s'est trompé
le plus souvent, et où chacune de ses erreurs est tombée sur une mesure, jamais sur une relecture.**
Deux ancres transmises sans être ouvertes (`Makefile:116` au lieu de `:119`, `docs/docker.md:304-305`
au lieu de `:304`) · un chemin recopié d'un énoncé sans vérification (`mtb-core/bootstrap.php`, qui
**n'existe pas** — c'est `includes/blocks/galerie-photos/bootstrap.php`) · une consigne **fausse sur le
fond** (« la vérification se fait par ce nombre » : `mtb_min_sources()` **énumère le disque**, aucune
constante de compte n'existe) · un compte faux dans ce journal même (**douze** `editeur.js` et non
onze, faux **le jour où je l'ai écrit**) · une convention de colonnes de `MASTER.md` **inventée** au lot
14 · et **une écriture dans l'empreinte d'une chaîne encore vivante**, décision 64. La chaîne a mesuré
tout ce que je lui ai envoyé, et elle a eu raison à chaque fois.

**Ce que #34 livre.** `galerie.css` est **supprimée** et non vidée — ses 6 règles, 6 sélecteurs et 31
déclarations étaient visuelles à 100 %, il n'en serait rien resté ; le précédent est dans le contrat
même (`issue-8.md:1226-1231`, où `editeur.css` n'a survécu **que parce qu'il lui restait une règle**).
La présentation vit dans `themes/mtb/assets/css/blocs/mtb-galerie-photos.css`, **au seul nom que le
chargeur dérive** — `mtb-galerie-photos.css`, jamais `galerie-photos.css` —, la clé `"style"` et le
`wp_register_style` étant retirés **ensemble** : les traiter séparément donne soit un double
chargement, soit une feuille enregistrée vers un fichier absent. La toile de l'éditeur est désormais
**symétrique à 11 × 2** : la galerie a rejoint ses dix sœurs.

**Le pré-vol est ce qui a débloqué l'issue, et sa méthode vaut plus que son résultat.** Le dépôt portait
deux mécaniques contradictoires sur « une feuille du thème atteint-elle la toile ? » —
`issue-7.md:576-582` disait non, `issue-6.md:627` disait oui. **Ce n'était pas une contradiction : la
première est une prédiction, la seconde la mesure qui l'a réfutée.** La chaîne a tranché sur la pile
vivante plutôt qu'en arbitrant entre deux textes, et a posé **avant de livrer** un témoin falsifiable —
ancienne poignée à 0, nouvelle à 1 en public et 2 dans la toile — qui s'est vérifié à la fin.

**La prise du lot.** L'index de la chaîne ne portait un moment **que la suppression**. Un `git commit`
nu produisait exactement l'état qu'elle avait elle-même classé DANGEREUX : `"style"` encore déclarée,
poignée encore enregistrée, `galerie.css` supprimée, feuille du thème absente — soit un `<link>` vers un
**404 sur chaque page à galerie**, le « Photo N sur T » démasqué sous chaque vignette, **page en 200 et
zéro diagnostic**. Attrapée **dans** la chaîne, par sa passe d'intégration.

**Trois élargissements d'empreinte accordés, tous sur précédent du dépôt.** (1) `editeur.css` **en
docbloc seul** : supprimer `galerie.css` y laissait **cinq renvois vers un fichier disparu**, plus deux
faussetés antérieures. (2) **La tâche 4 de l'issue reformulée**, parce qu'elle était **infaisable au sens
littéral** — `editeur.css` porte de la mise en page servie à l'éditeur seul. La chaîne a refusé de
rétrécir le `grep` pour le faire passer : **un `--glob '!editeur.css'` aurait fabriqué un vert qui ne
prouve plus rien**, la faute exacte du lot 11. (3) **Le §22 d'`issue-8.md`**, exception à « ne pas éditer
les contrats gelés » — voir décision 65, et la condition `105 ajouts / 0 suppression` **vérifiée trois
fois**, dont une depuis les objets git.

**Le MEDIUM corrigé avant le push mérite d'être nommé, parce qu'il portait sur l'accessibilité.** Le
docbloc neuf de la feuille énumérait ce que perd une dépendance `mtb-jetons` non résolue — « grille,
ratio, fond, cerne et cadrage » — en omettant la section 6. Or `all_deps()` **n'abandonne pas règle par
règle** : le masquage de `__rang` tombe avec le reste, et « Photo 3 sur 12 » s'imprimerait sous chaque
vignette **sur la page publique**. Le texte décrivait comme purement esthétique une panne qui atteint le
nom accessible — le défaut même que la duplication existe pour éviter, décrit comme tel deux paragraphes
plus bas dans le même fichier. Corrigé en `0a47ebc`, **et la correction a exercé la règle `make css`
inscrite la veille** : modifier un commentaire périme l'artefact, `make css-check` **a bien échoué**
avant régénération. Le mécanisme de #40 se comporte comme annoncé.

**Lignes de DoD non vérifiées par ce lot, à ne jamais présenter autrement** : **D5** (aucune redirection
posée) · **D7 pour l'essentiel** — **aucun navigateur n'a rendu ce site**, une poignée dans le payload de
la toile **n'est pas un rendu** · **D9** (pile jamais démarrée à froid, sur ma consigne de préserver le
volume et parce que la pile est partagée) · **D11** jugée par la revue. À quoi s'ajoute un non-vérifié
propre à ce lot : **la section 1 de la feuille ne s'exerce sur aucun contenu en base** — le *matching*
est prouvé par une sonde `do_blocks()` **en mémoire**, le *painting* ne l'est pas. **La chaîne a refusé
de fabriquer de la donnée d'élevage pour verdir ce test**, et c'est le bon refus.

**Stack Docker vérifiée à volume conservé** : les 4 services `healthy`, `make css-check` **exit 0 sur
15 paires**, `make css` **idempotent au même octet**, `debug.log` vide, page à galerie en **200** servant
bien `mtb-galerie-photos.min.css` (le `sourceURL` le nomme), `galerie.css` **absente du disque du
conteneur**, aucun orphelin `mtb-wpcli-run-*`. Fait utile relevé au passage : **le bloc galerie n'est pas
dans le `post_content` des portées** — il est injecté par `render_block()` depuis
`themes/mtb/enveloppe-fiche.php:139`, et sa feuille est servie **inline**, donc ces pages ne portent
aucun `<link>` de style à faire tomber en 404.

**Prochaine action** : `/lead-mtb #33`, **seule**. Verdict obtenu de `github-boards` le 2026-08-31 et
inchangé.

```
/lead-mtb #33
```

- **#33** — hisser `.mtb-dispo`, `.mtb-photo` et l'`object-position` en variable partagée (T18) ·
  `themes/mtb/assets/css/blocs/*.css` · `themes/mtb/assets/css/base.css` · **plus les artefacts
  `*.min.css` que `make css` régénère**.

**Trois dettes lui sont confiées explicitement**, parce que leurs fichiers sont sous son glob et que les
rouvrir ailleurs coûterait deux fois : **T99** (le compte « dix feuilles » de `base.css:792` et les trois
« neuf sœurs » de `mtb-bandeau-ouverture.css`, périmés par la onzième feuille) · et les **trois renvois
laissés par #34** — `fiches.css:104` cite une **poignée renommée** (`mtb-galerie-photos-style` →
`mtb-bloc-mtb-galerie-photos`), tandis que `mtb-grille-chiens.css:129` et `mtb-liste-portees.css:89`
citent `galerie.css:73`, **fichier supprimé**. La distinction compte : une poignée qui change de nom
n'est pas une cible qui disparaît, et **ni l'un ni l'autre n'est T88**, qui est une dérive de numéros.

**Après #33** : **#32** ne peut entrer dans aucun lot tant que son périmètre (`assets/css/**` **ou**
`includes/admin/**`) n'est pas tranché **par l'issue elle-même**, pas par l'orchestrateur.

**Bloquées, à exclure de tout lot jusqu'à réponse** : #23 et #24 par **Q1** et **Q4** · #25 par
transitivité · **#26 et #48** par le report assumé de **Q5** · #42 par **Q14**.

**Reste au board après #33** : **7 issues ouvertes** — 4 de dette (#48, #42, #32, #26) et 3 de
fonctionnalité (#25, #24, #23). Trois milestones ouverts : 9, 10 et 12.

**Aucune question en attente pour l'utilisateur.** Les trois du lot 14 ont été tranchées le
2026-09-01 : `make css` est inscrit à `CLAUDE.md`, la dernière capture du guide est prise (bijection
**122/122**), et T81 était résolue par le dépôt lui-même.

**Phase : lot 14 (#36 la mention « galerie remplie, photo principale vide », #40 la minification du CSS,
#43 le libellé du lien vers l'index) livré, testé et revu le 2026-08-31. Les trois issues sont fermées.**
Douze commits, `7d82c0a` → `d9ed66d`. Revue **OK — 0 CRITICAL, 0 HIGH**, 4 MEDIUM, 5 LOW : le push n'a
pas été bloqué. Quatrième lot consécutif dans ce cas, et le premier à ne porter aucune réserve.

**La leçon du lot tient en une phrase, et c'est la chaîne #40 qui l'a formulée : aucune erreur de ce lot
n'a été attrapée en relisant un texte — chacune est tombée sur un chiffre.** Cinq fois, sur trois
chaînes, et **deux fois sur mes propres consignes**. La relecture avait validé les cinq énoncés. C'est un
renversement par rapport aux lots 11 à 13, où le travail était de requalifier des énoncés faux : ici les
énoncés étaient justes et ce sont les **gardes** qui se croyaient plus fortes qu'elles n'étaient.

**Ce que #36 livre, et le défaut qu'elle a trouvé dans son propre travail.** Une mention rendue **au
serveur** en tête de « Galerie photos » — seule approche qui atteigne les 27 portées reprises, que
l'éleveuse ne réenregistrera jamais. **18 portées sur 31** la portent ; les 13 autres ont une galerie
vide et se taisent. Mais son apport n'est pas la mention : en ouvrant le cœur 6.9 elle a établi que
**poser** une photo principale ne s'écrit qu'à l'enregistrement quand **retirer** s'écrit tout de suite
(T80) — si bien que **sa propre phrase gelée était fausse par omission**. Elle disait « cliquez sur
Choisir la photo principale » et s'arrêtait, alors que ce clic seul ne produit rien pendant que la
vignette apparaît à droite : l'éleveuse voyait sa photo à droite, la ligne jaune à gauche, et
recommençait. **Le JavaScript a été écarté par un argument meilleur que le mien** — j'avais classé « la
mention se met à jour à l'écran » en tête des remèdes acceptables ; la chaîne démontre que c'est le
**pire**, la base n'ayant pas bougé au clic. Sa hiérarchie fait doctrine : celle qui reste fait
**recommencer un geste** (agaçant, rattrapable), celle qui s'efface trop tôt fait **partir** (silencieux,
non rattrapable).

**Ce que #43 livre.** Un libellé unique, **« Toutes les portées »**, là où le dépôt en portait deux
vivants pour la même destination — « Les portées » depuis une 404, « Toutes les portées » depuis
l'accueil. **La divergence était en production, pas sur le papier** : l'énoncé de l'issue la croyait
documentaire. Le choix ne repose pas sur l'argument circulaire « §10.3 se déclare arbitre » mais sur deux
faits — la révision 1.2 avait **explicitement décliné** cette juridiction, et le contrat **gelé**
`issue-13.md:745` avait déjà tranché au lot 5. **La DoD servie est D3 et non D1**, D1 ne s'invoquant pas
pour un littéral figé. `MASTER.md` passe en **1.4**, et la divergence `h1` « Les portées » / lien
« Toutes les portées » y est écrite comme **délibérée**, avec son motif, pour qu'aucune chaîne ne
l'aligne.

**Ce que #40 livre, et la prise du lot.** Retrait des **commentaires seuls**, en 14 artefacts `.min.css`
commités à côté de leurs sources et servis sous condition d'un marqueur. **245 252 → 41 810 o**
d'artefacts ; Accueil **147 492 → 43 831 o** décompressés et **10 867 o** réseau ; pire des 108 pages,
`/chien/jango/`, à **9,0 %** du plafond de 200 000. Sa prémisse de brainstorm était fausse — les feuilles
sont à **88,0 % de commentaires en octets gzippés**, gzip ne les mange pas. Ses trois refus valent son
livrable : `filemtime()` **dégénère en « toujours l'artefact »**, Git écrivant dans l'ordre
lexicographique de son index ; un hachage sur octets bruts aurait été rejeté **partout en production**
(`core.autocrlf=true`, 45 914 o dans Git contre 46 927 sur disque) ; la minification à l'exécution aurait
fait tomber les deux polices en **404**, et `base.css:59-70` ajustant métriquement les replis, **la chute
aurait été invisible à l'œil**.

**La prise du lot vient de la passe d'intégration, et elle a réfuté la revendication porteuse de #40.**
Sur 17 avaries fabriquées, un **artefact tronqué en queue était servi tel quel** — 107 déclarations
perdues sur 255, page en 200, aucune notice. Cause structurelle : **le marqueur attestait la source,
jamais l'artefact**. La phrase « jamais une régression visuelle, seulement une régression de poids »
était donc fausse, et le scénario est ordinaire sur la cible de D9, où l'on dépose par FTP. J'ai refusé
la correction à un mot dans le docbloc — elle aurait rendu la phrase exacte en laissant la même fausse
confiance. Le marqueur passe de deux à **quatre champs** ; la corruption **à taille constante** est
couverte aussi, et les phrases fausses sont **supprimées et non rétrécies**. Voir décision 62 : mon
propre remède (`filesize()` brut) était **impraticable**, l'artefact étant écrit en LF et rendu en CRLF.

**La passe d'intégration a rendu 443 vérifications, 439 passées**, puis **48 avaries sur 48** après
correctif — dégradation restée **silencieuse** dans les 48 cas, `debug.log` vide. Deux gestes de méthode
à retenir : les quatre conditions du marqueur **mordent chacune séparément** (le champ 3 seul ou le
champ 4 seul suffit), donc la garde ne tient pas par conjonction ; et la borne de lecture n'a pas été
crue mais **fait échouer** en bac à sable — établissant que **l'ancienne valeur de 64 perdait le marqueur
maximal en silence**, donc que le relèvement à 128 était une réparation et non une précaution.
**D1 et D2 vérifiées** pour la première fois depuis plusieurs lots : portée créée par le formulaire en
session réelle, aucun `wp post meta set`, puis **une saisie retrouvée à cinq endroits**. **D6 vert** sur
108 pages et 5 feuilles, extracteur auto-testé sur **22 formes de citation**.

**Les trois échecs non corrigés, tous classés.** **T71** (page protégée dans le sitemap du cœur) —
préexistant, non aggravé. **D4 sur `s2-2021`** — la source porte **deux pères** (« Double saillie » :
Jerry Lewis du Mont Brabant **et** Prinz Einhorn dit FITZ), le modèle n'en a qu'un, la migration **n'en a
élu aucun** plutôt que d'inventer, et les quatre noms restent visibles sur la page rendue : **limite de
modèle face à la source, pas perte** — même précédent que les Mentions légales. **`/travail/` en 404** :
la page **n'existe dans aucun statut** (5 pages en base, aucune nommée `travail`), `wp mtb
reprise-resultats-pages` n'ayant jamais été lancée sur ce volume. Conséquence à connaître : **le témoin
« Travail 60 491 o » cité depuis le lot 9 porte sur un état du volume que la pile ne porte plus**, donc
D8 n'a jamais été mesurée que sur une page là où trois lots l'ont présentée comme tenue sur deux. Le
témoin de remplacement est meilleur : **les 108 pages en 200, pire cas nommé**. `/la-meute/` en 404 est
cohérent avec T30, **mesuré** et non déduit : les gabarits rendent `['Accueil', 'Toutes les portées']`.

**Lignes de DoD non vérifiées par ce lot, à ne jamais présenter autrement** : **D5** — aucune redirection
n'est encore posée, le document le déclare lui-même, #24 étant bloquée par Q1/Q4 · **D7 pour
l'essentiel** — **aucun navigateur n'a rendu ce site**, donc aucune passe axe, aucun contraste calculé,
aucun parcours clavier, aucun focus visible, aucun zoom 200 %, **aucun rendu à 360 px** ; ce qui en est
rendu (`h1`, `lang`, `alt`, lien d'évitement) est une fraction et non D7 · **D9** — la pile n'a **pas**
été démarrée à froid, sur ma consigne de préserver le volume migré · **D11** — non testable
mécaniquement, jugée par la revue. Deux revendications de chaîne restent explicitement non démontrées :
le **« rendu identique au pixel »** de #40 (le pas « donc même rendu » est déduit de la spécification
CSS, pas mesuré ; ce qui **est** démontré est l'identité du flux de déclarations sur les 14 paires, par
deux chemins indépendants) et, pour #43, **aucun écran rendu dans un navigateur**.

**Stack Docker vérifiée au commit `655366e`, volume conservé** : build ✓, boot ✓, les 4 services
`healthy`, `make css-check` à 0 et 14 paires à jour, `make css` **idempotent**, `debug.log` vide après
rendu de l'Accueil et de `/wp-admin/`, artefacts `.min.css` réellement servis (3 `<link>` plus les
`sourceURL` des styles incorporés), et `docker/outils/` **ni monté ni copié** dans aucun conteneur —
vérifié de trois façons, pas supposé. Les deux derniers commits (`e7701f4`, `181138f`, `d9ed66d`) ne
touchent que des fiches Markdown. **Un flapping `unhealthy` de ~9 min a été observé et sa cause est
T94** : trois conteneurs `mtb-wpcli-run-*` orphelins tournaient depuis 4–5 h sur les mêmes volumes ; je
les ai retirés (`docker rm -f`), volumes vérifiés intacts après coup.

**Prochaine action** : **ce n'est pas un lot de 3, c'est une séquence de 2.** Verdict obtenu de
`github-boards` le 2026-08-31.

```
/lead-mtb #34      (puis, seulement après)
/lead-mtb #33
```

- **#34** — déplacer la feuille de la galerie vers le thème (T15) · `includes/blocks/galerie-photos/{galerie.css,block.json}` · `includes/blocks/galerie-photos/bootstrap.php` · `themes/mtb/assets/css/blocs/mtb-galerie-photos.css`. **Doit tourner seule**, sa propre note d'exécution l'exige.
- **#33** — hisser `.mtb-dispo`, `.mtb-photo` et `object-position` en variable partagée (T18) · `themes/mtb/assets/css/blocs/*.css` · `base.css`.

**L'ordre est imposé et non interchangeable** : #33 doit balayer la feuille que #34 vient de créer ;
l'inverse laisserait `mtb-galerie-photos.css` hors de son recensement.

**Un fait neuf du lot 14 qui durcit tous les recouvrements CSS à venir, et qu'il ne faut pas
redécouvrir** : toute issue touchant une feuille sous `themes/mtb/assets/css/` doit jouer `make css`
**dans le même commit**, ce qui fait entrer les **14 artefacts `*.min.css`** (15 dès que #34 aura livré la
sienne) dans l'empreinte de **toute** issue CSS. Deux issues CSS d'un même lot se recouvrent donc
désormais plus qu'avant, même quand leurs sources diffèrent.

**Recouvrements à ne pas redécouvrir** : **#34 ↔ #33** (le glob `assets/css/blocs/*.css` de #33 couvre la
feuille créée par #34, plus `base.css`, plus les `*.min.css` communs) · **#32 a une empreinte que l'issue
elle-même n'a jamais tranchée** (`assets/css/**` **ou** `includes/admin/**`) : dans la première branche
elle recouvre #33 et #34 ; dans la seconde elle pourrait toucher `themes/mtb/functions.php`, rouvert par
#40 — autorisé par la décision 61 tant qu'**une seule chaîne du lot** y touche. **Elle ne peut entrer
dans aucun lot parallèle avant que son périmètre soit tranché par l'issue, pas par l'orchestrateur.**

**Bloquées, à exclure de tout lot jusqu'à réponse** : #23 et #24 par **Q1** et **Q4** · #25 par
transitivité · **#26 et #48** par le report assumé de **Q5** · #42 par **Q14**.

**Reste au board après #34 et #33** : **7 issues ouvertes** — 4 de dette (#48, #42, #32, #26) et
3 de fonctionnalité (#25, #24, #23). Trois milestones encore ouverts : 9, 10 et 12.

**Questions du lot 14 — les trois sont tranchées, le 2026-09-01, par l'utilisateur** :
1. ~~T89, l'obligation de `make css`~~ — **TRANCHÉE : elle est inscrite au § Conventions de
   `CLAUDE.md`**, avec ce qui la rend piégeuse plutôt qu'en simple consigne : l'oublier ne casse rien de
   visible — la page est correcte, seulement plus lourde — et **rien ne le signale** ; `make css-check`
   est le seul témoin. Sa conséquence d'orchestration y figure aussi : **deux issues qui touchent chacune
   une feuille CSS se recouvrent**, même quand leurs sources diffèrent, puisqu'elles régénèrent les mêmes
   artefacts. **T89 est payée**, et c'est ce qui impose la séquence #34 puis #33 plutôt qu'un lot
   parallèle.
2. ~~La capture manquante~~ — **PRISE le 2026-09-01**, commit `1e598f2` :
   `docs/guide/captures/portee-mention-photo-principale.png`, 806 × 774 px, sur la portée reprise
   **U2 2023** (ID 186, galerie de 3 photos, aucune photo principale) — contenu réel et non fixture,
   conformément à la décision 59. Cadrage aligné au pixel sur la convention des 121 captures existantes
   (viewport 1280, `deviceScaleFactor: 1`, 14 px de fond d'administration sur les quatre côtés, vérifié
   en décodant `portee-galerie.png` et `portee-la-portee.png`). **Bijection du guide : 122 fichiers,
   122 références**, 1:1 stricte, aucun orphelin, aucun `alt` vide. **Aucune capture ne reste déclarée
   manquante** — deuxième lot consécutif dans ce cas.
   Fait relevé au passage, qui confirme un refus de #36 : **la première photo de cette portée est une
   bannière de rubrique**, pas un portrait. Élire automatiquement la première image aurait posé cette
   bannière comme photo de la portée — exactement ce que `contenu-repris-de-l-ancien-site.md:137-139`
   annonçait, désormais visible à l'image, au dossier.
3. **T81 est résolue et n'appelait pas de question** : l'absence de photo principale sur les 27 portées
   reprises **n'est pas une perte au sens de la contrainte 4** — `contenu-repris-de-l-ancien-site.md:177`,
   fiche livrée et revue à un lot antérieur, établit que l'ancien site ne désignait aucune photo comme
   *la* photo d'une portée. À rouvrir seulement si l'utilisateur sait autre chose de l'ancien site.

**Phase : lot 13 (#46 les deux dernières captures du guide, #44 l'ordre des statuts de `MASTER.md`,
#39 la garde de conteneur du lien de recours) livré, testé et revu le 2026-08-31. Les trois issues sont
fermées.** Quatre commits, `015c237` → `911935d`. Revue **OK AVEC RÉSERVES — 0 CRITICAL, 0 HIGH**,
2 MEDIUM, 4 LOW : le push n'a pas été bloqué. Troisième lot consécutif dans ce cas.

**La leçon du lot rompt avec les trois précédents, et c'est une bonne nouvelle : pour la première fois
depuis le lot 9, aucun énoncé d'issue n'était faux.** Les lots 11 et 12 avaient vu deux issues sur trois
se tromper — sur les faits, puis sur le mécanisme. Ici les trois énoncés décrivaient un vrai défaut et
prescrivaient le bon remède. Ce que les chaînes ont apporté en plus n'est donc pas une requalification
mais un **dépassement** : chacune a livré ce qui était demandé **et** la raison qui empêche le défaut de
revenir. C'est un régime de travail différent, et il vaut d'être noté comme tel.

**Ce que #44 livre, et pourquoi la permutation n'était pas l'essentiel.** L'issue prescrivait de
permuter quatre mots dans `MASTER.md:1079` (l'ancre `:1014` de l'énoncé avait dérivé). La chaîne a livré
la permutation **et la provenance de l'ordre**, en jugeant que **T56 n'était pas née d'un ordre faux
mais d'une énumération nue dont rien n'expliquait l'origine** — sans provenance, la ligne serait restée
aussi « réparable à l'envers » qu'avant, et c'est précisément le piège que T56 décrivait. La cellule dit
désormais que l'ordre est celui, **gelé**, des groupes de la page publique « La meute ». La revue a
vérifié que **le sens de la dépendance est écrit des deux côtés** : `content/chien/choix.php:96` porte
« dans l'ordre d'affichage gelé des groupes de La meute », inchangé par le lot. Une chaîne future qui
lira l'un ou l'autre saura lequel commande.

**Un arbitrage d'empreinte que je rends explicite, parce que je m'étais trompé.** J'avais borné #44 à
« §10.2 uniquement ». La chaîne a remonté que cette consigne, appliquée à la lettre, ferait de son
propre commit une péremption neuve : l'en-tête `:8-9` affirmait « seuls les §7.7 et §9.5 sont amendés »,
et cette phrase devenait fausse dès que §10.2 l'était — dans la ligne la plus lue du document, le jour
même où l'issue prescrit qu'un document de référence périmé induit en erreur toute chaîne future. Son
argument décisif n'était pas un raisonnement mais **un précédent du dépôt** : les entrées 1.1 (« §7.7
seul. ») et 1.2 (« §9.5 seul. ») ont **chacune** touché leur section, écrit leur entrée au journal **et**
bougé l'en-tête. Dans ce dépôt, « §X seul » est le **titre d'une entrée de journal**, pas un compte
d'octets. Et l'entrée 1.2 écrivait elle-même que « §10.2 et §10.3 ne sont pas touchés, leurs écarts
ayant chacun leur propre issue » : le document **attendait** #44. J'ai autorisé l'élargissement, borné à
l'en-tête et au journal. Voir décision 58. `MASTER.md` passe en **1.3**.

**Ce que #39 livre, et la prise qui n'était dans aucune issue.** La garde de conteneur est posée sur le
**parent** et non sur un ancêtre, et la passe de test a prouvé que ce choix n'est pas cosmétique :
l'état d'avant produisait un `<li>` **dans** un `<li>` — balisage invalide — alors même que le bloc
**avait** un ancêtre `<ul>`. **Un test formulé sur l'ancêtre l'aurait déclaré conforme.** Mais le fait
le plus important du lot vient de la passe de refacto : la clé `usesContext` avait d'abord été posée
après `keywords`, ce qui **décalait `block.json:18`** — la ligne citée par le contrat **gelé**
`issue-17.md:412` **et par l'énoncé de T51 elle-même**. Une correction de dette qui aurait, en silence,
périmé la citation du contrat gelé et l'énoncé de la dette qu'elle payait. Attrapée **dans** la chaîne.
Le garde-fou posé dans la foulée dépasse le cas : `rendu.php:132-136` déclare que les numéros de ligne
du cœur cités dans le code sont des **repères de lecture, pas un contrat**, parce que
`docker/wordpress/Dockerfile:5` tire une **étiquette flottante** — le cœur peut bouger sous nous entre
deux builds.

**Ce que #46 livre, et une première du projet.** Un seul cycle Docker forcé (`down -v` →
`MTB_FIXTURES=0` → migration → `MTB_FIXTURES=1`), parce que **l'ordre des états est imposé, pas
choisi** : l'état vide n'existe qu'avant la migration, les captures de médiathèque qu'après.
**Aucune capture ne reste déclarée manquante — c'est la première fois.** La chaîne a jugé que
`issue-37.md` **avait tort** de déclarer `coordonnees-legende-image.png` impossible : la fiche enseigne
un **rang de champ**, pas un plan, et aucun plan n'a été fabriqué — Q18, Q19 et Q20 restent intactes.
Elle a aussi corrigé son propre point de départ : **la bijection n'était pas « ouverte à 121 »**, le
guide était cohérent à 119/119/119, et déposer les PNG sans ajouter leurs deux références aurait
fabriqué deux orphelins.

**Le vrai apport de #46 n'est pourtant aucune de ses quatre images.** En photographiant l'étape 6, la
chaîne a trouvé **le geste qui faisait perdre son travail à l'éleveuse** : la fiche disait « cochez
celles que vous voulez » sans dire comment, et un clic au milieu de la troisième vignette effaçait les
deux premières. La cause, vérifiée par la revue et non reprise sur rapport :
`blocks/galerie-photos/editeur.js:190` ouvre la fenêtre en `multiple: true`, quand
`fields/chien/galerie.js:167` et `fields/portee/ecran.js:264` l'ouvrent en `multiple: 'add'` — **et deux
de ces fenêtres portent un titre identique**. Rien à l'écran ne prévient qu'on a changé de règle. La
fiche la protège désormais ; **la cause reste** (T77).

**T65 est levée sur preuve, et sa prédiction était fausse sur toute la ligne.** La dette annonçait
52 caractères sur environ cinq lignes coupées en travers des mots. Mesuré à l'image, puis contre-mesuré
par la revue qui a ouvert les fichiers : **53 caractères, quatre lignes, aucune coupure intra-mot** — le
mot le plus large, « Description », fait 60 px pour une colonne bornée à 80 px, donc
`word-wrap: break-word` n'a **jamais** à se déclencher. Le libellé est lisible, entier, non tronqué.
`MASTER.md` §10.2 **n'a pas à être rouverte**. La chaîne déclare de surcroît n'avoir essayé **ni autre
largeur ni image alternative** pour obtenir un meilleur rendu, et les images le confirment : elles
montrent le débordement plutôt que de le cadrer hors champ. Ce qui subsiste est réel mais autre — la
boîte du libellé fait 80 × 72 px face à un champ de 174 × 50 px, donc **le libellé est plus haut que le
champ qu'il nomme** et déborde de 21 px — et se juge à l'image, qui est au dossier. La revue nuance
justement la portée de la levée : le constat vaut **à une largeur et à un zoom**, la marge n'étant que
de 20 px. D'où T78, qui n'est pas « le libellé casse » mais « il n'a été rendu qu'une fois ».

**L'état du volume Docker est un fait de ce lot, et il n'est reproductible depuis aucun fichier.** La
migration a été lancée comme **prérequis de capture**, pas comme livrable : la base porte désormais
**31 portées** (27 reprises + 4 fixtures), **22 chiens** (17 + 5), **136 pièces jointes** (135 photos
migrées + la photo de test) et **5 résultats de travail seulement** — les 61 résultats de l'ancien site
ne sont pas repris, c'est une commande distincte (`wp mtb reprise-resultats-pages`) non lancée.
**Conséquence à connaître avant de perdre une session dessus : la pile refusera
`wp mtb importer-portees-chiens` tant qu'elle reste ainsi.** Effet secondaire favorable relevé par la
revue : cet état rend la vérification de **D4** possible pour la première fois à moindre coût.

**Deux consignes en circulation sont fausses, et c'est mesuré, pas déduit.** (1) `docker compose restart
wpcli` **ne relit pas `.env`** — il reprovisionne avec l'ancien environnement ; il faut
`docker compose up -d wpcli`, qui **recrée** le conteneur, avant tout `restart` dès que `MTB_FIXTURES`
a changé. Toute consigne du genre « changer `.env` puis `make provision` » est **fausse pour cette
variable**. (2) Le « 6 min 5 s » consigné au lot 12 **n'est pas la durée de tout provisionnement** :
sans fixtures le marqueur `[provision] terminé.` est arrivé en **~40 s**, et le reprovisionnement en
~30 s. Le chiffre du lot 12 vaut pour un provisionnement **complet à froid**. **La méthode, elle, ne
change pas : on boucle sur le marqueur, jamais sur une durée.**

**La passe d'intégration a rendu 21 vérifications, 0 échec, et sa valeur tient à son protocole.** Le
témoin de #39 — `<main>` des trois écrans identique **octet pour octet** entre `6f88b98` et
`6f88b98^`, 987 / 578 / 626 o — a été rendu **falsifiable** avant d'être cru : instrument sondé sur deux
captures à code inchangé, **contrôle positif** sur deux pages forgées qui bougent bien de 9 octets (ce
qui écarte l'hypothèse d'un opcache servant du code périmé), contre-épreuve après restauration.
« Zéro écart » est une négation, et une négation non falsifiable ne vaut rien. **D6 : zéro origine
tierce** sur 28 pages et 7 feuilles de style lues (191 379 o), avec un extracteur **auto-testé sur
quatre formes de citation** — précisément le bug de la passe du lot 11, qui concluait « D6 vert » sur
**zéro feuille inspectée**. La bijection de #46 a été **revérifiée indépendamment** : 121/121/121,
strictement 1:1, zéro orphelin, zéro référence morte, zéro `alt` vide.

**Un piège d'outillage à connaître.** Le compteur d'`alt` de la passe a crié à tort sur les 28 pages :
il comptait la chaîne littérale `<img>` écrite dans des **commentaires CSS en français** à l'intérieur
des `<style>` en ligne. Corrigé, il rend 24 images réelles, 24 avec `alt`. Mais sa correction consiste à
**retirer des zones du texte analysé**, donc un durcissement futur pourrait le faire **taire à tort**.
Parade laissée par l'agent : **revérifier le total d'images, pas le compte de manquantes** — « 0 sans
alt » sur 3 images au lieu de 24 se lit exactement comme un succès. Référence : **24**.

**Lignes de DoD non vérifiées par ce lot, à ne jamais présenter autrement** : **D1**, **D2** et **D5**
(hors empreinte, aucun test écrit) · **D4** — aucune date, aucun nom, aucun numéro LOF, aucun résultat
de santé comparé à `docs/migration/source/`, alors même que le lot a fait entrer 27 portées et 17 chiens
repris dans le volume · **D7 pour l'essentiel** — aucun navigateur, donc aucune passe axe, aucun
contraste calculé, aucun parcours clavier, aucun focus visible, aucun zoom 200 %, aucun rendu à 360 px ;
ce qui en est rendu sont des contrôles de balisage au `curl`, **une fraction et non D7** · **D9** — la
pile n'a pas été démarrée à froid, sur ma consigne de ne pas détruire le volume migré · **D11** — non
testable mécaniquement, jugée par la revue (voir décision 59). La revue conclut qu'**aucune de ces
lignes n'est mise en danger par ce lot**, en distinguant « non mesurée » de « menacée ».

**Prochaine action** : `/lead-mtb #36 #40 #43` — **empreintes disjointes, parallèle sûr**, verdict
obtenu de `github-boards` le 2026-08-31, **sans réserve**. La réserve sur #36 est **levée sur preuve** :
son empreinte n'est pas tranchée par l'issue elle-même (`includes/fields/portee/**` **ou**
`includes/admin/**`), mais le dépôt porte déjà le précédent exact — `fields/portee/avis.php:170` et
`fields/portee/ecran.php:237` posent des avertissements d'écran de saisie avec les classes du cœur
`notice notice-warning` / `notice-<niveau>`, **sans aucune feuille CSS dédiée**. #36 étant un
avertissement de la même famille (galerie remplie, photo principale vide) au même endroit du code,
**rien ne la fait sortir vers `assets/css/**`**, donc aucun chevauchement avec #40. #43 paie T55 et
hérite d'un précédent frais : `MASTER.md` est en 1.3, la numérotation de sa propre entrée sera **1.4**,
et le recouvrement #39 ↔ #43 est levé.

**Recouvrements à ne pas redécouvrir** : #33 ↔ #40 (`base.css`) · #33 ↔ #34 (l'empreinte élargie de #34
englobe `assets/css/blocs/*.css`) · **#39 ↔ #43 est levé, #39 étant livrée** · **#43 ↔ #44 est levé,
#44 étant livrée** — mais #43 touche `design-system/MASTER.md`, donc il rouvre le recouvrement avec
**toute issue future touchant ce fichier** · **#32** a une empreinte que l'issue elle-même n'a jamais
tranchée (`assets/css/**` **ou** `includes/admin/**`) — elle chevaucherait #40 dans le premier cas, #36
dans le second, et **ne peut donc entrer dans aucun lot parallèle avant d'avoir tranché son propre
périmètre**. Celle de #36 n'était pas tranchée non plus ; elle l'est désormais (voir « Prochaine
action »). **Vérifier aussi si #32 porte une contrainte de séquencement du genre de T15**, ce qui n'a
jamais été établi.

**Bloquées, à exclure de tout lot jusqu'à réponse** : #23 et #24 par **Q1** et **Q4** · #25 par
transitivité · **#26 et #48 garées** par le report assumé de **Q5** · #42 par **Q14** · **#34 rouverte
mais doit tourner seule**, hors lot parallèle, comme T15 l'exige.

**Phase : lot 12 (#30 les commandes base de WP-CLI en TLS, #45 les deux dettes de `hydratation.php`,
#35 le libellé de la description de photo) livré, testé et revu le 2026-08-30. Les trois issues sont
fermées.** Trois commits, `7939c42` → `6af2add`. Revue **OK AVEC RÉSERVES — 0 CRITICAL, 0 HIGH**,
4 MEDIUM, 5 LOW : le push n'a pas été bloqué. C'est le deuxième lot dans ce cas, après le lot 10.

**La leçon du lot est un motif, plus une surprise : deux issues sur trois prescrivaient un remède qui
ne pouvait pas fonctionner, et dans les deux cas la chaîne l'a mesuré avant de l'appliquer.** C'est le
troisième lot consécutif où requalifier l'énoncé du donneur d'ordre est le vrai travail. Mais la forme
est neuve et vaut d'être retenue : au lot 11 les énoncés étaient **faux sur les faits** ; ici ils sont
**faux sur le mécanisme**. #30 nommait une commande, il y en avait trois ; #35 prescrivait un crochet
WordPress qui, mesuré, **ne s'exécute jamais là où il faudrait**. Un énoncé peut décrire un vrai défaut
et se tromper entièrement sur ce qui le répare.

**Ce que #30 livre, et pourquoi le diagnostic est l'essentiel.** Le défaut était réel et vieux du
lot 2, mais inversé dans l'énoncé : **c'est le client qui exige TLS, pas le serveur qui en manque.** Le
`mariadb-client 11.4.8` de l'image Alpine `wordpress:cli-php8.1` a `ssl=TRUE` en défaut compilé, face à
un `mariadb:10.11` en `have_ssl = DISABLED`. Trois commandes tombaient, pas une : `wp db query`,
`wp db check`, `wp db export`. **Un fichier d'options ne pouvait pas suffire** — WP-CLI 2.12.0 invoque
toujours son client avec `--no-defaults` en tête de ligne, mesuré : un `[client] ssl=0` répare le client
nu et **ne répare pas WP-CLI**. Le seul point d'intervention est donc le binaire résolu par le `PATH`.
Livré : six enrobages `sh` dans `/usr/local/bin/` qui `exec` le binaire réel de `/usr/bin/` — jamais
déplacé ni masqué — en ajoutant `--skip-ssl` **en fin** de ligne, `--no-defaults` devant rester la
première option. Avec une **garde de retrait** : si l'appelant passe lui-même `--ssl`, `--tls` ou
`--skip-ssl`, l'enrobage se retire entièrement, pour qu'aucune intention explicite ne soit contredite en
silence.

**Le refus le plus important de #30 est d'avoir laissé le serveur en clair.** Activer TLS côté base
aurait fait taire le message ; c'est exactement pour cela que ça a été écarté. Un hébergement mutualisé
parle à sa base en clair, et une stack qui s'en éloigne pour se rassurer ne teste plus la cible de
production. **D9 est un critère de fidélité, pas de confort.**

**Ce que #45 livre, et l'honnêteté de son propre commit est le point à retenir.** Quatre instances
d'une même faute — recopier au lieu d'appeler — dans un fichier unique : les listes fermées des
disponibilités **et des sexes** étaient redéclarées mot pour mot alors que `content/portee/champs.php`
les possède et qu'un assainisseur les y consulte à chaque écriture. Le tri public, lui, comparait la
date de naissance brute sans le test de lisibilité que `fa80eb3` (#28) avait posé côté administration :
une date illisible se rangeait **en tête sur le site et en fin en administration**. Mesuré sur portée
d'essai injectée puis supprimée avec preuve du nettoyage : rang 1 public contre dernier en
administration **avant**, dernier des deux côtés **après**, total inchangé, suite des portées datées
identique caractère pour caractère, **0 jointure `postmeta`** avant comme après.

**Mais le commit écrit lui-même ce que le correctif ne fait pas, et c'est ce qui le rend fiable** :
`assainir_date()` refusant toute date malformée à l'écriture, **aucune portée saisie depuis
l'administration ne pouvait déclencher cette divergence**. Le défaut n'était atteignable que par
écriture directe en base. Ce qui est réellement gagné pour l'éleveuse est plus modeste et plus vrai :
l'unique **option d'année fantôme** que le composant « Liste de portées » pouvait lui proposer
disparaît. Une chaîne qui borne elle-même la portée de sa victoire est une chaîne à qui on peut se fier.

**Ce que #35 livre : le remède prescrit ne mordait pas, et il était nommé au dossier depuis #6.**
L'issue imposait `attachment_fields_to_edit`. Une sonde jetable posant une sentinelle sur le libellé de
`image_alt` **ne la fait apparaître sur aucun des six parcours sondés** — et la cause est dans le cœur
6.9 : là où le crochet s'exécute (`get_compat_media_markup()`, `media.php:1935`) `image_alt` est absent
à l'entrée et détruit à la sortie ; là où `image_alt` existe (`media.php:1509`) le site d'appel n'est
atteint par **aucun écran d'aujourd'hui**. Le bon remède — un filtre `gettext` strictement borné —
était écrit dans la **dette T16 du contrat #6** depuis le lot 2. **Une dette correctement rédigée a fait
gagner un lot entier à la chaîne qui l'a lue.**

**Deux refus de #35 méritent d'être gravés.** L'aide sous le champ n'est **pas** remplacée : celle du
cœur porte un lien vers l'arbre de décision du W3C, que notre phrase ne dit pas — *on ne remplace jamais
un texte par un autre qui en dit moins*. Et le filtre est borné à **deux chaînes anglaises exactes**,
dont les 8 émissions sont toutes des étiquettes de description d'image : aucun débordement, vérifié.

**Le guide comptait six fiches, pas trois** — le chiffre de l'issue datait de sa rédaction, comme celui
de #37 au lot 10. Le nouveau nom crée une collision (la colonne porte désormais **deux** champs
commençant par « Description »), payée par une désignation ordinale qui a l'avantage de **rester vraie
si le renommage cessait un jour de mordre**. Le paragraphe qui promettait ce renommage à l'éleveuse a
été supprimé, pas rafistolé.

**Ce que la passe d'intégration établit : 15 vérifications, 0 échec imputable au lot**, sur une image
`wpcli` reconstruite **sans cache** — la première reconstruction à froid depuis `690024f`. Elle a de
nouveau **sondé son propre instrument avant de conclure** (leçon du lot 11) et signalé un **faux positif
de son détecteur** : un `"Warning: …"` compté comme diagnostic PHP alors qu'il vivait dans un catalogue
de traduction JS. **Une première instance de cette même passe a tourné ~54 minutes et n'a rien rendu**
(T64) ; la relance a été faite avec obligation d'écrire le rapport **sur disque au fil de l'eau**, et
c'est ce qui l'a sauvée. **C'est la parade mécanique à retenir contre T64.**

**Trois écarts consignés hors lot, à ne pas reperdre** : la **page protégée figure dans le sitemap** du
cœur — aucun crochet `wp_sitemaps` dans `mtb-core` ni dans le thème, à arbitrer avec #23/#24 ;
`/wp-admin/widgets.php` répond **500**, mais c'est le `wp_die()` normal du cœur pour un thème sans zone
de widgets, ni fatale ni effet du lot ; et un `datetime="1888-13-45"` **invalide** rendu pendant
l'injection, pendant que le texte visible dit « Non renseigné » — inatteignable par la saisie.

**Ce que la revue relève, et son constat le plus utile est ergonomique, pas technique.** Le libellé
figé par `MASTER.md` §10.2 fait **52 caractères**, et il s'affiche dans la colonne gauche du volet
« Détails du fichier joint », que `media-views.css:409-411` borne à `max-width: 80px` en 12 px. « Texte
alternatif » (16 caractères) y tenait sur une ligne ; 52 y feront environ **cinq lignes coupées en
travers des mots**, à côté d'une zone de saisie de trois lignes. **Ce n'est pas une casse prouvée** — la
revue n'avait aucun navigateur — mais un mécanisme CSS établi, sur l'écran exact où six fiches du guide
envoient l'éleveuse. À rendre pour de vrai avant de considérer #35 comme close sur le fond.

**Deux MEDIUM sont, une fois de plus, de la prose que le code dément.** Le docbloc du module #35
affirme que « le cœur échappe et imprime lui-même » : **il n'échappe pas** (`_e( 'Alternative Text' )`
imprime brut en cinq points, et `{$field['label']}` est interpolé brut en deux autres) — la sûreté tient
en réalité à l'interdit n°3 du même docbloc, et le contrat le dit correctement là où le module se
trompe. Et `docker/wpcli/Dockerfile:68` motive un choix par « `core.autocrlf=true` **sans**
`.gitattributes` racine » : **les deux moitiés sont fausses**, ce fichier existe depuis `73f1ea4` (#47)
et ne renormalise que `.claude/agents/*.md`. Quatrième lot consécutif où la prose bloque ou entache là
où le code tient.

**T-#35-b sous-estime sa propre portée, et c'est le piège classique d'une dette mal bornée.** Elle ne
nomme que le menu « Médias ». Mesuré en session admin réelle : « Médiathèque » ×1, « Médias » ×3,
« fichier joint » ×12, « URL du fichier » ×5. `MASTER.md` §10.4 interdit la racine « média » ;
« Médiathèque » tombe sous la même règle **et le guide demande à l'éleveuse de cliquer dessus par son
nom**. Une chaîne future payant T-#35-b telle qu'écrite laisserait « Médiathèque » debout.

**Ce que la passe n'a pas couvert, à ne pas lire comme vérifié** : **D4 en entier** (aucun contenu repris
en base, la migration n'a pas été lancée) · **tout D7 pour l'essentiel** — aucun navigateur disponible,
donc aucune passe axe, aucun contraste calculé, aucun parcours clavier, aucun zoom 200 %, aucun 360 px ·
**tout ce qui dépend de JavaScript**, dont la fenêtre des photos telle qu'elle se comporte réellement —
la mesure de #35 porte sur les gabarits **imprimés**, pas sur ce que l'éditrice voit après exécution de
`wp.media` · **D11**, non testable mécaniquement, jugée par la revue : **tenue** · **D5** (aucun
mécanisme de redirection n'existe, il appartient à #24 ; 7 URL cartographiées sur 52) · le comportement
en production · **l'état « avant correctif » de #45, non remesuré** — l'agent a refusé de remettre en
place l'ancienne version d'un fichier livré, et ne contresigne donc pas la divergence que le commit
décrit · la divergence de #45 est prouvée sur **5 portées**, pas sur les 32 du lot 10.

**Quatre faits d'infrastructure, dont un qui remplace enfin une approximation.** La contre-épreuve
Docker a **daté l'écart entre *healthy* et *provisionné*** : le healthcheck `wpcli` passe *healthy* dès
**~8 s** (juste après `wp core install`), mais `[provision] terminé.` n'apparaît qu'à **6 min 5 s** après
`up -d` — une première boucle d'attente de 300 s a expiré avant lui. Le lot 11 disait « très avant » ;
on a maintenant le chiffre, et **une attente de 5 minutes ne suffit pas**. Ensuite : `build --no-cache`
en **32,6 s**, images de base déjà locales — ce n'est **pas** comparable aux 8 min 42 / 3 min 11 / 2 min 59
du lot 11, conditions différentes, et l'agent le dit lui-même. Puis un fait à connaître avant d'écrire un
`up` scripté : **`wordpress` est passé *unhealthy* transitoirement pendant 35 s** au plus fort du
provisionnement, et est revenu seul — rien dans #30 ne touche ce mécanisme, mais un script qui
dépendrait strictement de son « healthy » au lieu du marqueur `wpcli` tomberait. Enfin, **T72 est
confirmée empiriquement** : un septième enrobage déposé dans `docker/wpcli/bin/` est copié, ignoré par
la boucle, puis jeté — **sans la moindre erreur de build**.

**Prochaine action** : `/lead-mtb #46 #44 #39` — **empreintes disjointes, parallèle sûr**, verdict
obtenu de `github-boards` le 2026-08-30. #46 (captures du guide), #44 (`MASTER.md` §10.2 seul, aucun
code), #39 (`includes/blocks/lien-de-recours/**`). **Aucun des trois ne demande quoi que ce soit à
l'utilisateur ni à l'éleveuse.** **#46 a été amendée avant le lot** : son corps ne couvrait que les
deux captures manquantes de T57 et ignorait les **deux captures rendues fausses par #35**, que
`issue-35.md` §10 lui verse pourtant nommément — c'était un vrai trou de couverture.

**Recouvrements à ne pas redécouvrir** : #33 ↔ #40 (`base.css`) · #33 ↔ #34 (l'empreinte élargie de #34
englobe `assets/css/blocs/*.css`) · #39 ↔ #43 (`includes/blocks/lien-de-recours/**`) · #43 ↔ #44 **et
toute issue future touchant `design-system/MASTER.md`** · **#35 ↔ #46 est levé, #35 étant livrée** ·
#32 et #36 ont une empreinte que l'issue elle-même n'a jamais tranchée.

**Bloquées, à exclure de tout lot jusqu'à réponse** : #23 et #24 par **Q1** et **Q4** · #25 par
transitivité · **#26 et #48 garées** par le report assumé de **Q5** · #42 par **Q14** · **#34 rouverte
mais doit tourner seule**, hors lot parallèle, comme T15 l'exige.


**Phase : lot 11 (#31 les notices PHP, #34 la feuille de la galerie, #41 les écrans de recours de
`MASTER.md`) livré, testé et revu le 2026-08-30. #31, #41 et #47 sont fermées ; #34 est requalifiée et
rouverte.** Six commits, `2637429` → `02ff0c1`. Revue **BLOQUANTE — 0 CRITICAL, 2 HIGH**, les deux
levés et contre-vérifiés avant le push.

**La leçon du lot, et elle est double.** D'abord : **deux issues sur trois reposaient sur un énoncé
faux, et dans les deux cas c'est la chaîne qui l'a mesuré au lieu de l'exécuter.** #31 affirmait un
mécanisme qui n'a jamais existé ; #34 avançait trois prémisses fausses sur trois. C'est le rendement
direct de la consigne du lot 10 — chaque constat daté d'un commit — appliquée cette fois **à l'énoncé
du donneur d'ordre** et non aux seules lectures de fichiers. Ensuite : **aucun des deux HIGH n'est un
défaut de code.** `git diff e18c8ae..HEAD -- wp-content/` renvoie **0 fichier** sur tout le lot. Les
deux blocages sont des **affirmations écrites que le code dément** — troisième lot consécutif où c'est
la prose, et non le code, qui bloque le push.

**Ce que #31 livre, et ce n'est pas ce que l'issue décrivait.** L'issue disait « `WP_DEBUG` est à
`false` ». **Faux** : `compose.yaml:43` porte la variable depuis le bootstrap `c64087c` et
`wp-config.php:116` la lit dynamiquement. Le vrai défaut était pire et inverse : `WP_DEBUG_LOG` à
`false` — **`debug.log` n'existait pas du tout sur un volume neuf**, donc tout agent ayant « vérifié
que le journal était propre » lisait un fichier inexistant — pendant que `WP_DEBUG_DISPLAY` à `true`
écrasait le `display_errors = Off` de l'image et **imprimait les diagnostics dans la page du
visiteur**. Le pire des deux mondes. Démontré en A/B contrôlé **deux fois indépendamment** (la chaîne,
puis la passe d'intégration) : **141 octets de diagnostic fuyaient dans le HTML**, comptés au titre de
D8 ; 0 après, journal présent en `www-data:www-data`, déduplication levée (**5 requêtes → 5 lignes**).
D9 vérifiée sur les **trois** valeurs de `WORDPRESS_DEBUG`, **sur le chemin web** et non au WP-CLI.

**Le résultat le plus important du lot est une négation devenue falsifiable.** Sondes retirées, journal
vidé, balayage complet public et admin : « Journal vide ». Avant #31 le fichier n'existait pas — **la
même phrase, dans les passes des lots 9 et 10, ne pouvait pas être fausse.** Leur valeur probante en
est entamée rétroactivement, et c'est à savoir avant de s'appuyer sur elles.

**Ce que #34 livre : rien, délibérément, et c'est le bon résultat.** La chaîne a refusé d'exécuter son
issue et le refus a été accepté. Le `grep` de frontière rend **5** déclarations de mise en page, pas
une : déplacer la seule `grid-column` n'aurait restauré aucune frontière et aurait **fabriqué** la
seconde source de vérité que l'issue prétendait éviter. La bonne unité de travail est la feuille
entière — c'est T15, que l'issue prescrivait elle-même de traiter **hors lot parallèle**, ce que le lot
11 n'avait pas respecté. **Attention, une moitié du motif de refus était fausse et a été retirée de
l'issue** : la chaîne avançait que le chemin thème → toile de l'éditeur n'était démontré nulle part.
Il l'est — `themes/mtb/functions.php:194-250` existe pour ça et son commentaire raconte la panne et le
correctif de #6. Laissée au dossier, elle aurait coûté un aller-retour à la chaîne qui paiera T15.

**Ce que #41 livre : §9.5 était fausse sur trois points, l'issue n'en nommait qu'un.** Le plus coûteux
n'était pas le comptage des écrans mais le fait que le document promettait **« trois liens »** là où la
production en rend **deux** — `render.php:39-41` fait un `return;` nu quand la destination manque.
Écrire « trois » aurait créé une dette neuve en croyant en payer une. Le libellé
`Aucun contenu à afficher.` a été **figé sans qu'un mot soit inventé** : `issue-2.md:368` le déclarait
provisoire et promettait son remplacement à l'epic Gabarits, close au lot 9 sans que ça se fasse.

**Le HIGH le plus instructif du lot est né de la correction elle-même.** §9.5, écrite pour retirer des
faussetés, en a introduit une **normative** : elle déclarait que la feuille de style s'abstient de tout
espacement de liste « et doit pouvoir continuer », alors que `base.css:954-962` écrit une cible tactile
de 44 px et un écart `--e-2` **sur ordre de §12.10**. Prise au mot, elle autorisait une chaîne future à
supprimer les deux seules règles de cette feuille qui existent pour une raison d'accessibilité. **Une
fausseté qui donne un ordre est pire qu'une fausseté qui décrit.** Corrigée en bornant l'abstention, et
§12.10 renvoie désormais vers §9.5 — la moitié symétrique, sans quoi on recréait l'asymétrie d'origine.

**Le second HIGH est le mien, et il porte sur la manière de mesurer.** J'avais constaté avec `od` que
les deux fichiers d'agents en échec étaient en LF et les quinze autres en CRLF, et je l'ai écrit dans
le corps du commit. C'était vrai **de l'arbre de travail** et faux **du dépôt** : git stocke les 17 en
LF (`core.autocrlf=true`, pas de `.gitattributes`), les blobs parent et enfant portent **0 CR**, et le
commit ne changeait que 2 lignes là où une conversion en aurait produit 269. **Le corollaire compte
plus que l'erreur** : l'état qui distinguait les deux fichiers n'était pas versionné et ne pouvait pas
l'être — sur un poste en `autocrlf=input`, un `checkout` rendrait les **17** en LF d'un coup. Réparé
avant le push par un `.gitattributes` restreint et la réécriture du message ; **la cause reste non
établie entre les deux variables changées ensemble**, et le commit le dit.

**T49 est payée et le correctif du lot 9 est formellement infirmé.** Il visait un `: ` non échappé, il
a été appliqué, et le symptôme a survécu à un vrai redémarrage. Vérifié en session neuve au début du
lot. **La passe d'intégration du lot 11 est la première du projet exécutée par le vrai
`test-integration-mtb`** — et elle a trouvé un bug dans son propre extracteur, qui ne captait que les
`href` en guillemets doubles et concluait donc « D6 vert » sur **zéro** feuille de style inspectée.

**Ce que la revue confirme et qui est pire que consigné** : `issue-8.md:1305` porte **deux** faussetés
dans une phrase (T59) ; `issue-7.md:1127` en porte deux aussi (T60) ; et `ETAT.md:623` — le registre
T30 — se contredisait avec **T28, trois lignes plus bas dans le même tableau**, la fiche imprimée
disant déjà le vrai. Corrigé ici. Le défaut WP-CLI→web est en revanche **étroit et non récurrent** :
quatre autres contrats disent juste.

**Ce que la passe d'intégration n'a pas couvert, à ne pas lire comme vérifié** : D1, D2, D3, D4, D5,
D12 (aucun test écrit, sur consigne — l'empreinte du lot ne les touche pas, mais c'est un raisonnement,
pas une mesure) · D11 (non testable mécaniquement, jugée par la revue : **tenue**) · **D7 en entier** —
aucune passe axe, aucun parcours clavier, aucun 360 px, aucun zoom 200 % · le formulaire de contact et
Mailpit · la page protégée au-delà de son code 200 · **tout ce qui dépend de JavaScript**, les mesures
étant au `curl` sans navigateur réel · la fidélité de la migration et les redirections.

**Trois faits d'infrastructure à ne pas reperdre.** Le healthcheck du service `wpcli` passe *healthy*
dès ~9 s, **très avant** la fin du provisionnement — confirmé quatre fois ce lot ; le marqueur qui fait
foi reste `[provision] terminé.`. Les mesures de durée divergent selon l'état du cache d'images :
**8 min 42 s** (chaîne #31), **3 min 11 s** et **2 min 59 s** (passe d'intégration et vérification
finale, **cache chaud dans les deux cas**) — aucune n'arbitre les autres, et **aucune reconstruction
d'image à froid n'a été faite depuis `690024f`**. Enfin, `core.autocrlf=true` sans `.gitattributes`
global reste une dette (T-#21-m) : une édition non prudente renormalise un fichier entier.

**Prochaine action** : `/lead-mtb #30 #45 #35` — **empreintes disjointes, parallèle sûr**, verdict
obtenu de `github-boards` le 2026-08-30. #30 (`compose.yaml`, `docker/**`, `Makefile`) est enfin
libérée, #31 étant close. Seul point de vigilance : si #30 veut aussi `docs/docker.md`, aucune des deux
autres ne le touche.

**Recouvrements à ne pas redécouvrir** : #33 ↔ #40 (`base.css`) · **#33 ↔ #34, neuf depuis la
requalification** (l'empreinte élargie de #34 englobe `assets/css/blocs/*.css`) · #39 ↔ #43
(`includes/blocks/lien-de-recours/**`) · #43 ↔ #44 **et toute issue future touchant
`design-system/MASTER.md`** · #35 ↔ #46 (`docs/guide/*.md`) · #32 et #36 ont une empreinte que l'issue
elle-même n'a jamais tranchée.

**Bloquées, à exclure de tout lot jusqu'à réponse** : #23 et #24 par **Q1** et **Q4** · #25 par
transitivité · #26 par **Q5** · **#48 par Q5** (neuve : rien ne garantit qu'un diagnostic PHP reste
invisible en production, le correctif de #31 passant par un mécanisme Docker) · #42 par **Q14** ·
**#34 est rouverte mais doit tourner seule**, hors lot parallèle, comme T15 l'exige.


**Phase : lot 10 (#27 voie conforme pour une règle de réécriture, #28 colonnes et filtres des listes
d'administration, #37 les captures du guide) livré, testé et revu le 2026-08-30. Les trois issues sont
fermées.** Sept commits, `0c46eeb` → `17d0e01`. Revue **OK — 0 CRITICAL, 0 HIGH**, le push n'a pas été
bloqué : c'est le premier lot dans ce cas depuis le lot 5.

**La leçon du lot est neuve, et elle nous a eus quatre fois, sur quatre agents, dont deux fois moi : en
mono-branche, une mesure n'a de valeur que datée d'un commit.** Trois chaînes partagent un arbre de
travail unique sans isolation ; entre le moment où un agent lit et celui où il conclut, une autre a pu
écrire. Un `grep` ou un `git status` rapporte alors un fait **vrai**, à un instant **que personne n'a
nommé**, et l'interprétation qu'on en tire est fausse. Les quatre occurrences : le lead annonce **118**
captures promises contre 109, comptées pendant que #37 écrivait · #37 lit `git status` avant que
`includes/admin/listes/` n'existe et déclare fausse une phrase de la fiche des portées · #28 grep au HEAD
**après** `d95fc71` et infirme à tort un constat juste sur **cinq libellés faux présents dans son propre
commit** · le lead relit `git status` et croit non commitée une capture déjà enregistrée en `73591c4`.
**La parade est mécanique** : `git show <sha>:<chemin>`, `git blame`, `git log <sha>..HEAD -- <chemin>` —
jamais un `grep` ni un `git status` nu, et le SHA cité à côté du constat.

**Et le point qui explique pourquoi ça se reproduira si on ne l'écrit pas : la disjonction des empreintes
protège les écritures, pas les lectures.** Les trois chaînes étaient correctement isolées en écriture —
aucune n'a écrasé l'autre, vérifié — et ont quand même produit quatre conclusions fausses. La parade
n'est donc pas un meilleur découpage des fichiers, c'est une discipline de mesure. C'est le corollaire de
la décision 57 appliqué au **parallélisme**, et non à la relecture.

**Corollaire le plus coûteux, relevé par #28 sur elle-même** : elle n'a pas seulement conclu faux, elle a
**attribué à sa propre prose une faute qui était dans un livrable**. Cette inversion disculpe le dépôt et
accuse un message, alors que c'est le dépôt qui portait le mot faux. Elle a failli entrer dans un contrat
gelé — soit exactement le défaut qui a bloqué le lot 9. Vérifié : elle n'y est pas entrée.

**Ce que #27 livre, et ce n'est pas ce que l'issue annonçait.** L'empreinte du chargeur observait les
**noms** des types de contenu, jamais leurs **arguments de réécriture** : changer `'slug' => 'portees'` ne
la faisait pas bouger, la nouvelle adresse ne prenait jamais effet et l'ancienne continuait de répondre.
**Pas un 404 visible — un site qui a l'air de marcher avec les mauvaises URL.** Aucune des cinq tâches de
la checklist ne visait ce défaut, et **Q4 remet précisément ces slugs en jeu**. La chaîne a écarté les
**deux** approches que l'issue imposait de départager (sous-dossier auto-découvert, point d'extension par
filtre) au motif qu'elles transforment `add_rewrite_rule()` en **no-op silencieux** pour toute chaîne
future n'ayant pas lu le contrat — *elles créent le piège qu'elles prétendent fermer*. Retenu :
élargissement de l'empreinte, scindée en deux moitiés (identité → migration ; réécriture →
`flush_rewrite_rules( false )`), pour qu'un battement de règles ne puisse plus **rejouer une migration de
données**. Démontré, **défaut prouvé avant correctif** : `/chiots-a-reserver/` en 404 et règle absente
avant, **200 dès la première requête** après, en `curl` sans cookie et **sans jamais ouvrir `wp-admin`**.

**Une tâche d'issue a été refusée, et le refus est fondé — la revue l'a jugé.** La tâche 2 disait
« borner `flush_rewrite_rules()` à l'activation ». Le contrat #1 §4 refuse délibérément
`register_activation_hook` parce que le déploiement probable est un dépôt FTP ou un `git pull` **sans
réactivation** : borner à l'activation aurait livré **un site en 404 après la première mise en ligne**.
Le bornage est fait par comparaison d'empreinte — mesuré à **1 seule régénération sur 20 requêtes
alternées**, aller-retour idempotent, empreinte de hachage identique bit pour bit.

**Ce que #28 livre**, et c'est le lot le plus directement au service de la règle d'or : retrouver une
portée parmi 27, un chien parmi 17 ou un résultat parmi 61 ne passe plus par la recherche d'identifiant.
Colonnes métier, trois filtres, ordre par défaut imposé, et la **modification groupée de la
disponibilité** — six étapes, zéro fichier à ouvrir, zéro page à dupliquer.

**D12 est démontrée par contre-exemple, et le piège a deux portes.** La chaîne l'avait montré sur les
portées ; la passe d'intégration l'a rejoué sur les trois types et **la même perte se produit partout** :
`orderby=meta_value` escamote **32→31** (portées), **23→22** (chiens), **67→66** (résultats), et la
variante `meta_query EXISTS` escamote pareil. Preuve que le code livré ne prend aucune des deux voies :
**13 requêtes de liste capturées au SQL réellement émis, 0 occurrence de `postmeta`**, 13 fois
`FROM wp_posts` sans une seule jointure.

**Un défaut que la refacto a rattrapé contre son propre donneur d'ordre**, et il vaut d'être retenu : le
module de corbeille de #28 renseignait les cinq clés de message et **écrasait les 15 phrases justes**
posées par `includes/fields/**` — 15 phrases tuées en silence et 2 fiches d'aide rendues fausses.
L'arbitrage avait été gelé **sur une mesure jamais refaite**. Corrigé en priorité 20 : on complète, on
n'écrase jamais. **Un agent qui recompte l'énoncé de son donneur d'ordre fait son travail** — troisième
lot consécutif où cela sauve quelque chose.

**Ce que #37 livre : 119 captures, et le manuel cesse de parler aux développeurs.** Le chiffre de 33 de
l'issue était juste **pour son périmètre d'origine** et périmé depuis douze fiches. **84 encarts
« Capture à prendre » — des consignes de développeur dans le manuel de l'éleveuse — ont été retirés de
toutes les fiches**, et il n'en reste aucun. Bijection vérifiée trois fois de façon indépendante :
**119 citées, 119 sur le disque, 119 suivies par git, 0 orpheline, 0 lien cassé, 0 alternative vide**.

**Le périmètre de #37 a été requalifié, et il fallait pour cela lire le brief plutôt que les fiches** :
`BRIEF.md` §13.1 n'exige pas une capture par composant, il exige des captures pour **huit parcours
nommés**. Les ~116 promesses ne venaient donc pas du brief mais des fiches elles-mêmes, lot après lot.
Sept parcours sur huit sont illustrés ; le huitième (« protéger une page par mot de passe ») est reporté
à **#23**, sa fonctionnalité n'existant pas. **Et la chaîne a trouvé seule un trou du plancher que je
n'avais pas vu** : « modifier une disponibilité » était le seul parcours nommé sans image. Elle l'a
mesuré au lieu de supposer les sept couverts parce que sept fiches existaient.

**Arbitrage retenu et généralisé : une liste déroulante se capture fermée, ses options écrites en toutes
lettres dans la fiche.** Le navigateur dessine les menus natifs hors de la page. La parade n'est pas
seulement technique : un écran que Fabienne reconnaît, plus une liste de choix **lisible par un lecteur
d'écran**, vaut mieux qu'une image de menu déroulé — qu'aucune synthèse vocale ne lira jamais.

**Deux promesses seulement ont été abandonnées** — pas 80, comme je l'avais craint :
`liste-portees-etat-vide.png` (exigerait de **dépublier les 31 portées**) et
`coordonnees-legende-image.png`. Mesuré par différence entre les noms cités à `4049269` et au HEAD, pas
déclaré. Le chiffre de « 9 bloquées par Q18-Q20 et Q22 » est **périmé** : Q18-Q20 n'en bloque qu'une,
Q22 plus aucune — la chaîne a photographié le **panneau d'administration** du plan d'accès au lieu de
fabriquer une carte.

**Deux défauts relevés par la passe d'intégration, tous deux réparés avant le push.** Une capture
photographiait une **donnée d'essai** (« PORTEE ESSAI 37 ») dans le manuel de l'éleveuse — même famille
que les encarts retirés, passée du texte à l'image ; reprise en `73591c4`, relue **à l'image** et non
déduite de la propreté de la base. Et une **date de naissance illisible se rangeait en tête de liste**
alors que sa colonne annonçait « Non renseigné », contredisant le §6 du contrat #28 et une phrase de la
fiche ; réparée en `fa80eb3`, aux deux endroits (l'ordre **et** la valeur de filtre), adossés à la source
unique `date_en_toutes_lettres()`.

**Une prémisse de ma consigne était fausse et la chaîne l'a mesurée plutôt que de livrer une preuve
arrangée** : j'avais transmis que le filtre « proposerait une option `pas-` ». **Faux** — `filtres.php`
filtre déjà par une expression à quatre chiffres avant de composer les options, et `filtre_actif()`
revalide toute valeur d'URL : **deux gardes en aval, déjà en place**. La seconde correction ne répare donc
**aucun comportement visible** ; elle supprime un docbloc faux et une seconde notion de « date absente ».
Le contrat l'écrit sous cette forme, vérifié par la revue — la version flatteuse aurait reproduit le
défaut du lot 9.

**Ce que la revue a validé en propre, et qui ne se mesure pas** : la **frontière thème/extension** tient
sur les 2 308 lignes de #28 — aucun fichier de thème touché, **zéro octet de CSS et de JS**, quatre
classes toutes issues du cœur. `listes-retrouver-un-contenu.md` a été **relue en cherchant le mensonge**,
quatorze affirmations non triviales confrontées à la ligne de code qui les produit : **aucun mensonge
trouvé**. Et les quatre libellés que #28 avait arbitrés sans `MASTER.md` sont **validés**.

**Sur les deux désaccords avec `MASTER.md`, la revue tranche que c'est MASTER qui est périmé, pas le
code** : `MASTER.md:1028` donne 8 disciplines contre 9 au code — « Autres disciplines » porte **4 des 61
résultats importés** — et `MASTER.md:1014` donne un ordre des statuts divergent de `choix.php:105-127`,
qui est celui de la page publique. Ces deux écarts sont désormais **imprimés dans le manuel de
l'éleveuse**. Une chaîne future qui ouvrirait MASTER « réparerait » le code et casserait à la fois la
page publique et la fiche.

**D9 démontrée à froid, deux cycles complets** — ce que la passe d'intégration avait explicitement
déclaré non couvert, la stack étant occupée par la prise des captures. `[provision] terminé.` en **51 s**
(démo) et **78 s** (réel), idempotence **mesurée** au rejeu (`0 créé, 44 déjà importés` puis `0 créé, 68
déjà présents`), **0 rejet**, T39 confirmé en direct (`WPLANG` = `fr_FR`). Le journal porte la signature
exacte de #27 : `permastructs +5, etiquettes +19, regles_haut +13`.

**Un fait de base à ne pas reperdre, découvert par la vérification à froid** : la base sur laquelle les
119 captures ont été prises portait **27 portées réelles + 4 de démonstration**, soit un état **mélangé
qu'un simple `make up` ne produit jamais**. Une reconstruction propre donne **27/17/61**, pas 31/22/66. Le
garde de `mtb importer-portees-chiens` refuse de tourner si de la démo est présente, mais rien n'empêche
l'inverse — rejouer les fixtures démo *après* un import réel.

**Un piège d'infrastructure à connaître avant d'écrire le moindre script d'attente** : le healthcheck du
service `wpcli` passe *healthy* dès `wp core is-installed`, à **9 s**, soit **bien avant la fin réelle du
provisionnement** (51 à 78 s). Quiconque scripte « attendre que ce soit prêt » sur ce seul healthcheck
sera trompé ; le marqueur qui fait foi est `[provision] terminé.` dans les logs.

**T49 : ne pas conclure au défaut de code — le correctif n'a jamais eu sa chance, et c'est moi qui me
suis trompé en écrivant « second lot consécutif ».** `test-integration-mtb` et `leaddev-back-mtb` ne sont
toujours pas enregistrés comme types d'agents, et la passe d'intégration du lot 10 a donc encore tourné
sur un agent générique adossé au fichier de prompt. Mais le frontmatter des deux fichiers a été
**revérifié** : YAML valide, guillemets fermés, pas de BOM, aucun guillemet interne non échappé — le
correctif de `6180741` est sain. **Les lots 9 et 10 ont tourné dans la même session continue**, jamais
redémarrée depuis ce commit, ce que le lot 9 avait lui-même prédit (« la correction ne prend qu'à la
session suivante »). **À vérifier au tout début du prochain lot, en session neuve, avant d'ouvrir quoi
que ce soit.** Si le symptôme persiste après un vrai redémarrage, alors seulement une issue `infra`
d'epic 11 se justifie — avec la preuve du symptôme en session neuve.

**Prochaine action** : `/lead-mtb #31 #34 #41` — **empreintes disjointes, parallèle sûr**, verdict obtenu
de `github-boards` le 2026-08-30, vérifié fichier par fichier. Aucune des trois ne partage un seul
fichier avec les deux autres.

**Pourquoi #31 et pas #30** : les deux se disputent `compose.yaml` et le `Makefile`, un seul tient dans un
lot disjoint. #31 (`WORDPRESS_DEBUG` n'atteint pas `wp-config.php`) compromet **toute** passe
d'intégration future — c'est ce qui avait forcé le lot 2 à patcher `wp-config.php` à la main — alors que
#30 a déjà un contournement qui fonctionne (`$wpdb` via `wp eval`). #30 reste ouverte, à reprendre seule
ou avec un tiers qui ne touche pas `docker/**`.

**Recouvrements à ne pas redécouvrir** : #30 ↔ #31 (`compose.yaml`, `Makefile`) · #33 ↔ #34 (le glob
`assets/css/blocs/*.css` englobe `galerie-photos.css`) · #33 ↔ #40 (`base.css`) · #39 ↔ #43
(`includes/blocks/lien-de-recours/**`) · #41 ↔ #43 **et la future issue « ordre des statuts »** vivent
toutes dans `design-system/MASTER.md` — jamais deux d'entre elles dans le même lot · #32 et #36 ont une
empreinte **que l'issue elle-même n'a jamais tranchée**, à clarifier avant de les inclure.

**Bloquées, à exclure de tout lot jusqu'à réponse** : #23 et #24 par **Q1** et **Q4** · #25 par
transitivité (le pas-à-pas « protéger une page par mot de passe » qu'elle assemble est un livrable de
#23 — et c'est elle, pas une issue neuve, qui porte le `docs/guide/README.md` manquant) · #26 par **Q5**
· #42 par **Q14**, et c'est #42 qui couvre déjà mot pour mot le désaccord **8 contre 9 disciplines**.

**Phase : lot 9 (#17 gabarits des pages libres) livré, testé et revu le 2026-08-29. #17 est fermée et
l'épic 7 « Gabarits et thème » est close.** Le lot le plus petit du projet en volume de code — **huit
lignes de gabarit, zéro octet de CSS, de JS ou de PHP** — et l'un des plus instructifs.

**Ce que #17 devait encore et qu'elle livre** : A8 est **levée**. Les trois `mtb/lien-de-recours` sont
posés sur l'état vide d'`index.html`, recopiés **octet pour octet** de `search.html` — les trois
gabarits capables d'un état vide rendent désormais la même sortie de secours. Les deux prémisses du
refus d'origine étaient tombées : l'adresse de « La meute » se calcule au rendu depuis que T28 est
payée, et `index.html` n'a jamais été hors empreinte. **Mesuré, pas cru** : `/author/fabienne/` rend
**2 liens, pas 3** — « La meute » s'omet en silence, sa page n'existant pas (T30), sans `<li>` vide ni
puce orpheline. L'exception D1 est écrite dans le guide, la contradiction des Mentions légales est
vérifiée et **délibérément non harmonisée**, D8 est tranchée, T33 est sous son bon numéro.

**La leçon du lot, et elle est neuve : ce lot n'a été bloqué par aucun défaut de code.** La revue a
déclaré les huit lignes irréprochables et a bloqué le push sur **deux affirmations écrites qui
contredisaient le code** — une fiche annonçant à l'éleveuse un lien cassé que le code ne produit
jamais (il **n'affiche aucun lien**, il n'en propose pas un qui tombe dans le vide), et une section du
contrat gelé déclarant l'AA invérifiée sur toutes les pages du lot. Les lots 6, 7 et 8 avaient été
bloqués par un **silence** ; celui-ci l'a été par des **paroles fausses dans des documents gelés**.
C'est la même famille de défaut, vue de l'autre côté : le dépôt affirme quelque chose que le code
dément.

**Et il faut écrire d'où venait la seconde, parce que c'est la forme qui se répétera** : de *ma*
consigne. J'avais dicté « le contraste sur fond dégradé n'est vérifié par personne sur la totalité des
pages du lot ». C'était un cran trop large, et cette formulation route du travail : le prochain lecteur
ouvre une issue AA pour un **filet décoratif de 6 px**. Le dégradé en cause est `--filet-double`, ancré
en `left bottom` avec sa hauteur réservée en padding — il **ne passe jamais sous les glyphes**. axe rend
`incomplete` parce qu'il voit un `linear-gradient` sur un ancêtre et refuse de raisonner sur
`background-size`. **Un donneur d'ordre qui dicte une formulation sans la vérifier fabrique la prochaine
erreur du dépôt** — corollaire de la décision 57, et la chaîne a eu raison de recompter au lieu de
recopier. Ce qui reste réellement non attribué est borné et écrit : **1 à 10 nœuds par page**, personne
ne les a tracés.

**La passe d'intégration a réfuté quatre affirmations du contrat**, et c'est sa raison d'être. La plus
coûteuse : §10 déclarait le `<main>` d'`index.html` non focusable et demandait d'ouvrir une issue
`a11y`. **Faux** — `functions.php:861-865` pose le `tabindex="-1"` au rendu, vérifié sur le HTML brut de
trois routes **et au comportement clavier**. Le contrat contredisait le commentaire du thème lui-même.
L'issue n'a pas été ouverte. Également réfuté : le volet `pwbox` de T42 déclaré « non mesurable » alors
qu'`espace-prive` est publiée **et** protégée et déborde à **263/180** ; les `TH` dits hors flux quand
c'est le `THEAD` ; et l'`incomplete` de contraste restreint à Travail quand il court sur les six pages.

**Ce que la passe d'intégration a mesuré** : les cinq revendications du contrat reproduites **au chiffre
près**, aucune valeur recopiée. Un seul débordement sur **42 combinaisons** (Travail à 180 px, 231/180,
Mondioring à 215 px de min-content) ; **81 arrêts clavier, 0 sans anneau de focus** ; **0 violation
axe** sur les six pages ; **D6 une seule origine, 0 cookie, 0 requête échouée** sur sept routes ; **D8
tenue en octets réseau** — Accueil 68 749 o, Travail 60 491 o contre 200 000 ; **D12 éprouvée sur une
page composée exprès de treize composants mal remplis**, rendue sous `E_ALL` forcé, **0 diagnostic**.
**D1 vérifiée dans une vraie session `fabienne`** : enregistrement réel de l'accueil ressorti en
anonyme puis contenu restauré, et le refus sur le **choix** de la page d'accueil confirmé par **trois
chemins indépendants**. Deux faux positifs écartés à la main, dont un anneau de focus prouvé visible
**aux pixels** (2 323 pixels changés en cadre fermé, contraste 14,2:1) après qu'une première tentative
eut donné 73 % de faux positif par saut de défilement.

**T42 est requalifiée, pas rétrécie** : elle valait « 4 combinaisons sur 36 », chiffre relevé sur les
fiches de portée et de chien. Sur les pages libres elle vaut **1 sur 42** ; les quatre d'origine ont été
rejouées et **vivent toutes**, plus le volet `pwbox` — **5 combinaisons vivantes au total**.

**D9 démontrée à froid**, ce que la passe d'intégration avait explicitement déclaré non couvert : deux
cycles `down -v` → `up` complets, `[provision] terminé.` en 62 s et 72 s, provisionnement **rejouable
sur base neuve en démonstration comme en réel** (17 chiens, 27 portées, 61 résultats, 7 pages, **0
rejet**), garde T39 confirmé par `wp option get WPLANG` en direct, et **le changement du lot survit
intact au cycle complet**. Un résidu de session à connaître : un conteneur orphelin d'un
`docker compose run` antérieur tenait le volume et le réseau, et **sans son nettoyage le « à froid »
n'aurait pas été à froid**.

**T49 avait une cause, et elle est réparée** : `leaddev-back-mtb` et `test-integration-mtb` portaient un
`: ` **non échappé** dans leur `description:` YAML, donc un frontmatter impossible à parser, donc deux
agents jamais enregistrés — pendant que `CLAUDE.md` les décrivait comme des maillons de la chaîne. Les
descriptions sont désormais entre guillemets. **La correction ne prend qu'à la session suivante** : la
passe d'intégration du lot 9 a encore tourné sur un agent générique adossé au fichier de prompt.

**Deux 404 qui sont le bon comportement, contre-vérifiées trois fois ce lot** : `bhpl-en-france` et
`politique-de-confidentialite` répondent 404 en anonyme parce qu'elles sont en `draft`, et ce statut
vient des **fichiers de données versionnés** de l'import (`"statut": "draft"`), donc rejoué à
l'identique à chaque provisionnement. C'est D11 tenue, pas D4 manquée.

**Phase : lot 8 (#20 import des portées et des chiens, #21 import des résultats de travail et des
pages libres) livré, testé et revu le 2026-08-28. C'est le lot où le contenu de l'ancien site entre
dans le nouveau : **27 portées, 17 chiens, 61 résultats de travail, 7 pages libres, 135 photographies**
— importés depuis des **fichiers versionnés**, pas depuis une base. #20 et #21 sont fermées.**

**Ce que ce lot corrige du lot 6, et c'est sa raison d'être** : le reproche fait à #17 n'était pas « le
contenu est faux » mais « **D11 y est invérifiable une fois la base détruite** ». Les deux chaînes ont
donc transcrit dans des fichiers, écrit un importeur qui **crée et ne met jamais à jour**, et un
comparateur hors ligne rejouable. **D11 est démontrée au byte près** : base détruite, reconstruite,
imports rejoués → **106 empreintes de `post_content` identiques**, **47 des 48 URL rendant un HTML
identique à l'octet**, la 48ᵉ à **1 octet** près — une galerie franchissant la borne 99/100 d'un
compteur, prédiction posée **avant** mesure puis vérifiée.

Ce lot est le premier dont les **trois chaînes ont été menées à terme dans la même session**, avec deux
passes correctives et une décision produit prise en cours de route. Il a aussi coûté **cinq questions à
l'utilisateur**, toutes tranchées : les photos versionnées dans git (30-75 Mo estimés, **33,7 Mo
réels**), Q3 (courriel uniquement), la mention d'information, la destination, Q17 (fictif assumé) et la
composition des pages au provisionnement.

Ce que la passe d'intégration a **mesuré** sur le lot 7 : **≈627 vérifications, 6 échecs, aucun
imputable au code des trois issues**. **D2 démontrée pour de bon** : créer une portée fait basculer
l'encart d'accueil tout seul, et il **revient en arrière** à la suppression — c'est calculé, jamais
saisi. **Le formulaire n'écrit rien** : les cinq chaînes soumises cherchées dans `wp_options`,
`wp_postmeta`, `wp_posts` et `wp_comments` → **zéro ligne** ; six champs postés, pas un de plus, aucune
adresse IP. D6 : **82 requêtes, une seule origine**, 0 cookie anonyme, 0 octet de JS public. D7 :
axe-core **0 violation sur 17 vues**, 8 arrêts clavier et 8 anneaux de focus sur `/contact/`. D8 :
54 213 o réseau / 148 091 o décompressés contre 200 000. Sécurité du formulaire éprouvée et non crue —
injection d'en-tête, nom commençant par `<`, apostrophe française, piège à robots inatteignable au
clavier, délai minimal, jeton forgé : **tous rejetés proprement**.

**La capture de #19 a été contre-vérifiée sans complaisance** : les 38 captures **re-dérivées depuis le
HTML brut dans un conteneur `--network none` sont identiques au caractère près**, les 216 valeurs de
`RELEVE.md` recalculées sans un écart, les 21 fichiers antérieurs **identiques au blob git**, et
192 photos / 33 694 075 octets concordant à l'octet. La preuve de complétude est faite **par
soustraction** — 52 `<loc>` moins 52 lignes d'inventaire égale 0 — et recomptée trois fois de façon
indépendante.

**La leçon du lot, à ne pas perdre** : la revue a de nouveau bloqué sur un **silence**, et cette fois
elle l'a trouvé seule. Cinq pages du site source — `chien-halan`, `chien-ray-ban`, `chien-roxane`,
`chien-youry`, `placement` — portent `noindex, nofollow`, et ce sont **exactement** les cinq orphelines
des menus que `INVENTAIRE.md` avait mesurées sans en tirer de conclusion. Aucun livrable ne portait le
mot. Cause structurelle : la réduction à cinq zones ne découpe que le `<body>`, donc **aucune
métadonnée de `<head>` n'atteint une capture** — sauf le `<title>` — et cette limite n'était déclarée
nulle part. Sans ce constat, #20-#21 aurait réimporté quatre fiches de chiens et la page Placement
comme des pages ordinaires, et #24 les aurait redirigées en 301 vers des pages indexables. **Une
intention éditoriale que le site exprime deux fois était effacée par omission.** C'est la même forme de
défaut qu'au lot 6 : pas un fait inventé, un fait tu.

**Corollaire, et il vaut pour tout le projet** : la chaîne chargée de réparer ce silence a **corrigé
trois affirmations de ma propre consigne** en recomptant — il y a **58 balises `robots` sur 54
fichiers** (48 / 5 / 1) et non « 5 et 53 » ; les cinq pages ne sont pas « en `noindex` », elles portent
**les deux balises**, le `noindex` en tête de `<head>` et l'`index,follow` bien plus loin, donc la
contradiction est **à l'intérieur d'un même `<head>`** ; et « aucune métadonnée n'atteint un `.md` »
était faux, le `<title>` passe. **Un agent qui recompte l'énoncé de son donneur d'ordre fait son
travail.** La question est ouverte sous Q23 et **aucune hypothèse n'a été avancée** sur le motif : le
site énonce le fait, jamais sa raison, et la raison est un fait d'élevage.

Ce lot a failli être perdu, et il faut savoir pourquoi. Ses trois chaînes ont tourné le 2026-08-20 et
sont **mortes avant la validation de lot** : code committé, contrats gelés, guides écrits, aperçus pris
— mais aucune passe d'intégration, aucune revue, aucun push, et `ETAT.md` annonçait toujours ce lot
comme « prochaine action ». Il a été retrouvé le 2026-08-23 en constatant que le dépôt local était
**15 commits en avance** sur `origin/main`. **Leçon de méthode** : `git rev-list origin/main..HEAD` fait
partie de l'étape 0 au même titre que la lecture d'`ETAT.md` — un lot non poussé ne se voit nulle part
ailleurs, et `ETAT.md` ment par omission quand la chaîne qui devait l'écrire est morte.

Ce que la passe d'intégration a **mesuré** sur le lot 6 : **147 vérifications, 2 échecs de code source,
tous deux hors du périmètre du lot** (T39 la langue du provisionnement, T40 le sitemap qui publie une
page protégée). **D1 et D2 vérifiées au HTML** : 12/12 valeurs d'une portée saisie à l'écran par le
compte `fabienne` retrouvées sur `/portees/k9-2026/`, puis sur ses quatre emplacements. Zéro requête
tierce (**26 URL**, origine unique, 0 cookie anonyme, 0 octet de JS public), **0 diagnostic PHP** sur
26 URL dont cinq contenus délibérément mal remplis (D12), axe-core sans violation sérieuse ni critique,
**91 arrêts clavier sur 12 pages, 0 sans anneau de focus**, aucun débordement à 320/360/768/1440 px.
Trois dettes payées et mesurées : **T3** (`h1` unique sur l'accueil), **T22** (le filet double rend
**576 × 6 px** au lieu de 0), **T23** (marges composant↔composant **86,39 px uniformes** au lieu de
134 et 173).

**La leçon du lot, à ne pas perdre** : la revue a bloqué le push sur **un fait d'élevage faux**, pas sur
un bug. La fiche d'un chien affichait « **Eenhoorn Sire Eenhoorn** » — le nom d'usage et l'affixe collés
faute d'une seule déclaration CSS, donc une généalogie que personne n'a saisie. Le code était par
ailleurs le plus solide du projet (zéro CRITICAL, frontière thème/extension irréprochable, zéro `MTB\`
et zéro requête directe dans tout le thème). **La même donnée était correctement séparée sur la fiche de
portée** : deux fiches du même lot, deux lectures. Un crochet CSS émis et jamais stylé n'est pas une
coquille — c'est un fait faux en attente. Corollaire : la revue a aussi bloqué sur un **silence**, la
carte parent amputée du portrait et du nom complet que `MASTER.md` prescrit, sans qu'aucun document ne
le déclare (T32). Un écart non écrit n'est imputable à personne.

**Ce que le lot 6 confirme sur les fiches d'aide** : les six fiches ont été relues « en cherchant le
mensonge », chaque affirmation confrontée au code qui la produit — **aucun mensonge trouvé**. Elles
disent « créez cette page » là où le lot 5 disait « elle existe », elles nomment leurs propres captures
manquantes, et l'une d'elles **liste les quatre libellés WordPress qu'elle n'a pas relevés à l'écran**
au lieu de les affirmer. La décision 43 a porté.


Ce lot n'était pas celui qui était annoncé, et il faut savoir pourquoi. `/lead-mtb #18` a été demandé ;
#38 était un prérequis déclaré (son pied de page aurait créé un **troisième** exemplaire en dur de
l'adresse et du téléphone). Le prérequis s'est révélé plus faible que prévu — le pied de page insère le
bloc de coordonnées **nu**, donc une seule source de vérité dans tous les cas — mais #38 était déjà
lancée et a été menée à son terme.

Ce que la passe d'intégration a **mesuré** sur le lot 5 : **~190 vérifications, 7 échecs**, aucun fatal,
aucun diagnostic PHP sur page rendue. La règle de défaut de l'option de #38 est vérifiée sur **six
états de base différents**, dont une base **sans aucune ligne d'option** — et revérifiée à froid après
`down -v` : l'adresse et le `0680505619` s'affichent toujours. Zéro requête tierce (10 pages, toutes
les requêtes réseau interceptées au navigateur), zéro cookie anonyme, **zéro `<script src>`**,
117 279 o pour la page la plus lourde (budget 200 000), 2 fichiers de police, axe-core sans violation
sérieuse ni critique, aucun débordement à 320, 360, 768 et 1440 px.

**La leçon du lot, à ne pas perdre** : un audit d'accessibilité mesure une **propriété**, jamais une
**expérience**. La correction évidente de l'`aria-label` manquant produisait
`aria-label="Menu principal Menu principal"` — et `landmark-unique` **passait au vert**, puisque les
deux noms diffèrent bien, pendant qu'un lecteur d'écran aurait bégayé sur chaque page du site. Seul le
fait de rendre la page **après** avoir appliqué une consigne approuvée l'a attrapé.

Ce que la passe d'intégration a **mesuré** sur le lot 4 (et non déduit) : **31 vérifications passées,
zéro échouée**. Le **zoom de page à 200 %**, qu'aucune chaîne du lot 3 n'avait vérifié, est enfin
mesuré — **24 combinaisons sur 24 sans débordement** (1440 → 720 px CSS, 1280 → 640, 360 → 180, plus
le zoom du texte seul). Zéro requête vers un domaine tiers (**47 requêtes, origine unique**), zéro
cookie anonyme, zéro octet de JavaScript public, zéro diagnostic PHP sur **11 URL** dont des pages
délibérément mal remplies, axe-core **0 violation**, **70 625 o** pour la page portant les trois
composants (budget 200 000). Insérteur réellement cliqué en session `fabienne` (rôle **Éditeur**) :
« Mont Brabant » en première position, les trois composants insérés et réglés, page publiée en 200.
**Les onze contrastes annoncés par les trois chaînes ont été confirmés sur les pixels rendus, écart
zéro** — c'est la première fois du projet qu'un contraste est lu et non seulement calculé.


Ce que la passe d'intégration a **mesuré** sur le lot 3 (et non déduit) : zéro requête vers un domaine
tiers côté visiteur, zéro cookie anonyme, **zéro octet de JavaScript public**, zéro erreur PHP sur
43 URL dont dix délibérément mal remplies, aucun débordement à 360 px (9 pages sur 9) ni au zoom
200 %, un anneau de focus sur les **37 arrêts clavier**, contraste minimal **5,25:1**, texte du bandeau
sur photographie à **6,31:1** au pire pixel, **167 627 o** pour la page portant les six composants
(budget 200 000), **2** fichiers de police. Insérteur réellement cliqué : « Mont Brabant » **en première
position**, les six composants présents. Une affirmation de ce paragraphe s'est révélée **fausse** au lot 4 : les six états vides n'étaient **pas** identiques (voir dette T13).

| Étape | État |
|-------|------|
| Brief produit (`docs/BRIEF.md`) | ✅ écrit |
| Chaîne d'agents (`.claude/`) | ✅ 17 agents + `/lead-mtb` |
| Dépôt git | ✅ `main` sur `git@github.com:QuentinDoniczka/mtb.git` — commit d'amorçage `38d0935` |
| Board GitHub (issues + milestones) | ✅ 10 epics, 25 issues — [projet 10](https://github.com/users/QuentinDoniczka/projects/10) |
| Design system (`design-system/MASTER.md`) | ✅ 16 sections — vocabulaire en §10 |
| Stack Docker (`compose.yaml`) | ✅ 4 services, **revérifiée à froid le 2026-08-23**. Port **3005** (jamais 8080), Mailpit **8025**. Lot 7 : les **deux voies de courrier réparées dans les images** (`/etc/msmtprc` appartenait à `root` et était illisible par `www-data` ; `wpcli` n'avait pas `msmtp` du tout) — donc persistantes à un `--force-recreate`. **T39 payée** : `provision.sh` relit `WPLANG` et crie si la locale a été avalée en silence. **T41 payée** : l'accueil et la page Contact sont composés au provisionnement, `make up` montre le site. Piège toujours valable : le healthcheck de `wpcli` devient vrai **avant** la fin de `provision.sh` — attendre la ligne `[provision] terminé.` |
| **En-tête et pied de page** | ✅ livrés par #18 sur **toutes** les pages (`parts/header.html`, `parts/footer.html`, raccordés dans les quatre gabarits). Lien d'évitement écrit à la main, deux emplacements de menu, pied portant le bloc de coordonnées **nu** |
| **Réglages des coordonnées** | ✅ écran unique (#38), accessible au rôle Éditeur par `edit_pages`. Adresse, téléphone, courriel, page de contact. **Un seul littéral** de chaque valeur dans tout le dépôt (`query/coordonnees/option.php:46-48`) |
| Extension `mtb-core` | ✅ squelette + chargeur à auto-découverte (#1), portée, chien, résultat (#3, #4, #5) |
| **Catalogue de composants** | ✅ **11 composants** — les dix du lot 6 plus le **formulaire de contact** (#22). S'ajoute `mtb/lien-de-recours`, non insérable et exempté de fiche par écrit |
| Thème `mtb` | ✅ thème **de blocs**, `theme.json` verrouillé, CSS à la main, 2 polices auto-hébergées (#2) |
| **Saisie** portée / chien / résultat | ✅ trois écrans classiques, en français, éditables par le rôle Éditeur sans capacité ajoutée |
| **Rendu public** des trois types | ✅ **livré au lot 6 (#16)** — `single-mtb_portee.php`, `single-mtb_chien.php`, `enveloppe-fiche.php`, `archive-mtb_portee.html`. Une portée saisie apparaît sur son URL, dans l'index, dans l'encart d'accueil et sur la fiche de sa mère. **Six appels `mtb_get_*`, zéro requête directe, zéro `MTB\` dans le thème** |
| Reprise du contenu (52 URL) | ✅ **capturée au lot 7 (#19), importée au lot 8 (#20, #21)** — la capture (309 fichiers, 36 Mo, dont **192 photographies**) reste la pièce à conviction, montée **en lecture seule** dans `wpcli` depuis `01d4489` et prouvée telle (trois écritures refusées, empreinte identique avant/après). L'import a versé **27 portées, 17 chiens, 61 résultats, 7 pages, 135 pièces jointes**, depuis `donnees/**` versionnés. **3 photos des pages libres non téléversées** (arbitrage A5 : leur `alt` est une question à l'éleveuse). ⚠️ **Restent #23-#24** pour honorer les faits de non-indexation et les 301 |
| Guide de l'éleveuse (`docs/guide/`) | ✅ **21 fiches**. D3 tenue. Les deux fiches du lot 8 ont été relues « en cherchant le mensonge » — **et il y en avait** : « sept pages avec leur texte » quand trois sont vides à dessein, un cadre décrit « gris » qui est **beige à contour ocre**, un palmarès annoncé pour des chiens dont **60 résultats sur 61 ne sont rattachés à aucune fiche**, et une section entière décrivant des marqueurs `[IMAGE …]` que le code **ne produit plus**. Toutes corrigées avant le push. **BRIEF §13.1 toujours pas tenu** : le dossier `docs/guide/captures/` n'existe pas, et le lot 8 nomme **6 captures manquantes** de plus |

**Prochaine action au moment du lot 9** : ~~`/lead-mtb #27 #28 #37`~~ — **fait au lot 10**, les trois
issues sont fermées. La prochaine action courante est en tête de fichier.

**Pourquoi ce lot et pas l'épic 9** : #23 et #24 sont bloquées par Q1 et Q4. Et **#25 l'est aussi, par
transitivité** — le guide qu'elle assemble promet un pas-à-pas « protéger une page par mot de passe »
qui **n'existe pas** dans `docs/guide/` et qui est un livrable de #23. La lancer maintenant reviendrait
à inventer ce pas-à-pas ou à l'omettre. #27 est en outre le prérequis d'infra de #23 et #24 : c'est
elle qui ouvre une voie conforme pour une règle de réécriture (dette T6).

**Recouvrements à ne pas découvrir en cours de route** : #26 partage `class-loader.php` avec #27 (mais
#26 est bloquée par Q5) · #30 et #31 se disputent `compose.yaml`, `docker/**` et le `Makefile` · #28,
#35 et #36 atterrissent toutes dans `includes/admin/**` — **jamais deux des trois dans le même lot** ·
#33 (glob `assets/css/blocs/*.css`) englobe le fichier exact que vise #34 · #39 et #43 vivent toutes
deux dans `includes/blocks/lien-de-recours/**` · **#42 est bloquée par Q14**, à exclure de tout lot
jusqu'à réponse.

**Ce que le lot 8 a déjà payé dans le reste de #17** : le point 1 (contenu ne vivant dans aucun fichier)
est clos, et le point 2 l'est à moitié — l'Accueil et Travail sont repris par #21. Ne redécouvre pas ces
deux-là.

**Deux questions à poser à l'utilisateur avant de lancer l'épic 9**, et elles ne se découvrent pas au
milieu d'une chaîne :
- **Q1 — l'usage exact de la page protégée par mot de passe** (familles de chiots ? avant-première ?
  documents d'élevage ?). Bloque #23.
- **Q4 — URL accentuées conservées ou normalisées** (`/bhpl/portée-a3-2025/`). Bloque #24, et le lot 8
  vient de la rendre plus concrète : **les 12 liens internes survivants pointent vers des URL accentuées
  de l'ancien site** (`/bhpl/portée-m-2016/`…). La réponse détermine directement ces douze redirections.

~~**Le corps de #24 ne porte aucun des deux faits que le lot 8 lui lègue** — vérifié sur le board.~~
**FAUX, corrigé le 2026-08-29** : les deux faits **sont** dans le corps de #24, verbatim et avec leur
détail fichier par fichier — la tâche des douze ancres internes et celle de `_mtb_robots_source` /
`wp_robots`. L'issue a été éditée le 2026-08-28 à 21:12Z, **à la clôture du lot 8**, et cette ligne n'a
pas été mise à jour derrière. Rien à ajouter à sa checklist. Leçon interne : une affirmation « vérifié
sur le board » **périme dès le geste suivant sur le board** — elle se revérifie, elle ne se relaie pas.

**Ce que le lot 8 lègue à #23 et #24, et qu'aucun corps d'issue ne portait :**
- **12 liens vers `mtbrabant.com`** survivent dans la prose importée, sur six fiches de chien, se résolvant
  en **neuf cibles distinctes qui sont toutes des portées importées par #20**. Chaque redirection est donc
  possible ; elle doit juste exister. Sans elle, le site porte **12 liens internes cassés** que les tests de
  #24 ne verront pas, puisqu'ils vérifieront la carte et non les liens qui s'y appuient. Les 12 sont nommées
  une par une dans `docs/migration/portees-chiens.md`.
- **Cinq contenus portent `_mtb_robots_source`** — les fiches `halan`, `ray-ban`, `roxane`, `youry` et la page
  `placement` — sous la forme `valeur` / `source` / `extrait`, **homogène sur les cinq et vérifiée en base**.
  Le fait de non-indexation est **stocké et documenté, pas honoré** : #24 doit poser le filtre `wp_robots`,
  #23 l'exclusion du plan du site. Sans eux, cinq pages que la source retirait des moteurs y seront publiées.
- **`docs/migration/redirections.md` est périmé** (T38, T-#21-d) et doit être refait, pas amendé.

**Le reste écrit de #17, toujours ouverte** (verdict de `review-mtb`, 2026-08-23, **réduit par le lot 8**) :
1. ~~Le contenu recopié ne vit dans aucun fichier~~ — **payé par #20 et #21** : le contenu des pages libres
   vit désormais dans `resultats-pages/donnees/pages/*.json`, et D11 est démontrée au byte près.
2. **D4 partiellement payée** : les lignes manquantes de l'Accueil et de Travail sont reprises par #21.
   **Restent les Mentions légales** — non pas parce que le siège social et la raison sociale manqueraient
   (*la source les porte*, contrairement à ce que ce fichier a affirmé), mais parce que **la page se
   contredit avec elle-même** : téléphone à neuf chiffres, adresse sans numéro, `Gueneau` sans accent contre
   `Guéneau` dans son propre pied de page. Rien n'a été harmonisé, et c'est le bon choix.
3. La page d'accueil réelle **n'est pas choisissable par l'éleveuse** (`options-reading.php` exige
   `manage_options`) — D1 tenue **avec une exception nommée**.
4. ~~**A8 ouverte** : les motifs de recours de `MASTER.md` §9.5 ne sont pas livrés pour les motifs.~~
   **LEVÉE au lot 9.** Les deux prémisses du refus étaient tombées : l'adresse de « La meute » se calcule
   au rendu, et `index.html` n'a jamais été hors empreinte. Les trois `mtb/lien-de-recours` sont posés sur
   l'état vide de l'index. **Mesuré** sur `/author/fabienne/` : **2 liens rendus, pas 3** — « La meute »
   s'omet en silence, sa page n'existant pas (dette T30), sans `<li>` vide ni puce orpheline.
5. ~~**D8 non tranchée** sur l'Accueil (T37).~~ **TRANCHÉE par l'utilisateur le 2026-08-29** : le budget
   de `BRIEF.md` §12 se lit en **octets réseau**. Mesuré à neuf : Accueil **68 749 o**, Travail **60 491 o**,
   plafond 200 000. **D8 tenue, T37 fermée.** La cause du dépassement décompressé survit sous **T52**.

**Deux pages vides à ne pas prendre pour un oubli** : `bhpl-en-france` (source en 302, protégée par mot de
passe sur l'ancien site) et `politique-de-confidentialite` (source en 404, absente du sitemap) sont vides
**pour la bonne raison**, et les fichiers source le disent au lieu de combler. **C'est D11 tenue, pas D4
manquée.** Idem `litterature` : la page source n'a aucun contenu rédactionnel.

**Piège de ce fichier, corrigé le 2026-08-18** : il annonçait `#16 #17 #18` alors que l'epic 4 n'était pas
fait. **Le board fait foi — vérifie-le avant de lancer un lot annoncé ici.** Et `git rev-list origin/main..HEAD`
fait partie de l'étape 0 : au lot 8, le dépôt local était **13 commits en retard**, le lot 7 ayant été livré
et poussé depuis un autre contexte.


**Comptes de développement** — `make up` puis http://localhost:3005/wp-admin/ (port **3005**, jamais 8080) : `admin`/`mtb-dev-admin`,
éditrice `fabienne`/`mtb-dev-editrice` (rôle **Éditeur** natif, délibérément pas Administrateur).
Attrape-courriels Mailpit sur http://localhost:8025.

---

## Décisions prises (ne pas les re-litiger sans raison)

| # | Décision | Date | Pourquoi |
|---|----------|------|----------|
| 66 | **Un contrat gelé que la mesure réfute n'est pas corrigé : son erratum est écrit ici, daté, et la mesure prime sur lui** | 2026-09-02 | Arbitrage rendu au lot 16, et il **complète la décision 65 sur le cas qu'elle ne couvrait pas**. La 65 autorise l'amendement daté d'un contrat qui porte déjà cette convention ; `docs/contracts/issue-33.md` ne la porte pas, et ses **§5, §8(3) et §10** reposent sur une prémisse fausse : « la toile de l'éditeur préfixe `base.css` par `.editor-styles-wrapper` et pas les feuilles de blocs ». **La toile est une iframe, le cœur n'y préfixe rien** — mesuré par la chaîne #33, qui a livré `a8bec34` contre son propre contrat, puis **indépendamment** par la passe d'intégration au Chrome réel : 0 sélecteur préfixé sur les 11 présents dans la toile, et retirer la règle 13.10 du CSSOM n'y change aucun pixel. **Ce qui est gelé, c'est la décision, pas sa justification** : quand les deux se séparent, la décision peut survivre à sa raison. Ici elle survit — le doublage (0,3,0) **reste en place**, il gagne dans les trois contextes avec ou sans préfixe, et l'inertie d'un motif n'est pas une raison de le retirer. **Le remède appliqué au code est la note de solde datée** — cinq posées dans l'empreinte de #33, sur la forme validée de `mtb-bandeau-ouverture.css:54-57`, chacune renvoyant au §13 point 4 de `base.css` plutôt que de recopier l'argument. **Le risque que cela ferme est nommé par la chaîne elle-même** : « une garde dont la justification est démentie se fait supprimer par la chaîne suivante ». **Ce qui reste dû** : les occurrences de la même prémisse **hors empreinte de #33** n'ont pas été soldées — voir T101 |
| 65 | **Un contrat gelé s'amende par ajout daté en fin de fichier — mais seulement s'il porte déjà cette convention** | 2026-09-01 | Arbitrage rendu au lot 15 sur remontée de la chaîne #34, et il **borne** la règle « ne pas éditer les contrats gelés » sans la renverser. Ce qui reste interdit : **réécrire un énoncé gelé**. Sa fausseté au présent est une **trace de ce qui a été cru**, pas un défaut ; la corriger sur place efface la preuve. Ce qui est autorisé : **ajouter une section d'amendement datée en fin de fichier**, quand le document porte déjà cette convention — `issue-8.md` a ses §19, §20 et §21, ajoutés après gel, et son **§20.5 anticipait explicitement #34** (« son déplacement futur reste une copie plus la suppression du `"style"` »). Le contrat attendait l'issue, comme `MASTER.md` attendait #44 au lot 13 (décision 58). **Deux garde-fous** : l'ajout en **fin de fichier** ne périme aucune citation par numéro — leçon de `issue-17.md:412` —, et la condition a été **vérifiée trois fois** (par la chaîne, par moi, par la passe d'intégration **depuis les objets git**) : `105 ajouts, 0 suppression`, les 1 374 lignes existantes de même hash des deux côtés. **Ne se généralise pas** : un contrat sans convention d'amendement ne s'ouvre pas ainsi. Piste ouverte, non vérifiée : si les cibles de **T86** et **T91** portent la même convention, ces dettes se paient par ajout daté au lieu d'attendre une passe d'alignement hypothétique |
| 64 | **La disjonction des empreintes protège les chaînes entre elles ; elle ne dit rien du lead** | 2026-09-01 | **Trou de ma propre méthode, nommé par la chaîne #34 après que je l'ai commis.** Au lot 15, j'ai édité `editeur.css` et `issue-8.md` — dans l'empreinte d'une chaîne **encore vivante** — parce que mes messages ne lui parvenaient plus et que je voulais éviter un aller-retour de plus. Rien n'a été perdu, la chaîne ayant **amendé au lieu d'écraser** : c'est de la chance, pas de la méthode. Le corollaire qu'elle en tire vaut au-delà du cas : **sur un mono-branche sans isolation, une écriture non annoncée dans l'empreinte d'une chaîne vivante est indiscernable d'une faute de cette chaîne** — et elle l'a effectivement prise pour telle, accusant à tort un de ses agents d'avoir menti sur la provenance de deux commits qui étaient les miens. **Règle** : le lead n'écrit pas dans l'empreinte d'une chaîne tant qu'elle n'a pas rendu son rapport final ; s'il doit le faire, il l'annonce **avant**, et le `reflog` ne suffit pas à réparer après coup. Corollaire de raisonnement, que la chaîne a formulé mieux que moi : ses trois « preuves » (mêmes fichiers, même horodatage, même auteur git) **corroboraient** son hypothèse sans jamais la **distinguer** de l'hypothèse concurrente — ce dépôt n'ayant qu'un seul auteur git. **Prendre de la corroboration pour de la discrimination est une faute de raisonnement, pas une lacune d'information** ; avant de conclure sur la provenance d'un changement, **énumérer qui d'autre a la main sur l'arbre — le lead en fait partie** |
| 63 | **Quand la fausseté précède le lot mais la contradiction est du lot, on corrige avant de pousser** | 2026-08-31 | Arbitrage rendu au lot 14 sur `docs/guide/contenu-repris-de-l-ancien-site.md:181`. La clause « elles sont **bien remplies** » était fausse **avant** ce lot pour dix des vingt-sept portées reprises, dont la galerie est vide. Le paragraphe neuf de #36 (« Certaines portées reprises n'ont aucune photo ») ne l'a pas rendue fausse — **il l'a rendue contradictoire à quinze lignes de distance, dans la même section**. C'est la contradiction, et non la fausseté, qui coûte : une phrase fausse isolée passe inaperçue ; deux phrases qui se démentent apprennent à l'éleveuse que **son manuel n'est pas fiable**, sur la fiche même censée lui expliquer un contenu qu'elle n'a pas saisi. Donc **corrigée, pas versée en dette**. Corollaire appris dans le même geste : la première correction avait retiré « sans exception » **et** « elles sont toutes là », **deux fragments vrais** — toutes les photos ont bien été reprises, il n'y en avait simplement aucune pour ces dix. Le geste juste ne retire **que la clause fausse** ; corriger une fausseté en emportant du vrai avec elle est le même défaut à plus petite échelle. Écart net du lot sur ce paragraphe après resserrement : **quatre mots** |
| 62 | **Le coût de lecture de la garde d'artefact est accepté, et sa condition est nommée** | 2026-08-31 | Arbitrage rendu au lot 14, **contre ma propre décision de la veille dans la même chaîne**. J'avais écarté une vérification du contenu de l'artefact au motif qu'elle « échangerait un incident rare contre un coût permanent sur toutes les pages ». Le raisonnement était bon, **le chiffre que j'y supposais était faux** : mesuré, le supplément réellement inédit est de **16 973 o par requête** et non du double, parce que les 10 artefacts de blocs portent un `path` et que `wp_maybe_inline_styles()` **les lisait déjà** intégralement. Face à cela : une **régression visuelle silencieuse**, page en 200, sur un octet retourné en transit — scénario ordinaire d'un dépôt FTP interrompu sur la cible de D9. Le marqueur porte donc **quatre champs**, dont deux attestent le corps de l'artefact ; la troncature en queue **et** la corruption à taille constante sont couvertes, mesurées une par une. **La condition est écrite pour être rejouable** : si les artefacts de blocs cessaient de porter un `path`, le supplément inédit repasserait à ~34 580 o et le marché redeviendrait celui que je décrivais. C'est la conclusion **et** son chiffre porteur qui sont consignés — ce qui manquait à `mtb-bandeau-ouverture.css:153`, où un chiffre juste avait survécu à la version de WordPress qui le rendait faux. Corollaire : mon remède initial (`filesize()` brut) était **impraticable**, l'artefact étant écrit en LF et rendu en CRLF après un `git checkout` — il aurait été rejeté partout en production, le mode de panne même que la chaîne avait déjà écarté pour la source |
| 61 | **La décision 9 est une contrainte de collision, pas une sacralité de `functions.php`** | 2026-08-31 | Arbitrage rendu au lot 14 pour autoriser #40 à rouvrir `wp-content/themes/mtb/functions.php`, hors de son empreinte déclarée. L'en-tête du fichier portait « conçu pour ne plus être rouvert (décision 9) » ; **la décision 9 ne dit pas cela.** Texte réel (`ETAT.md:962`) : aucun index central à éditer à la main, « **c'est la condition technique du parallélisme** […] un index de blocs édité à la main […] ferait entrer en collision des chaînes pourtant censées être disjointes. Conséquence : […] `functions.php` **n'est touché que par #2 puis #18, jamais dans le même lot** ». Elle **prévoit** donc que le fichier soit rouvert, et n'interdit qu'une chose : que **deux chaînes d'un même lot** y touchent. Condition vérifiée **comme un fait** avant l'autorisation — #36 dans `mtb-core/includes/fields/portee/**`, #43 dans `MASTER.md` et `blocks/lien-de-recours/**`, aucune n'entrant dans `wp-content/themes/mtb/`. **La formule fautive était celle de l'ancien en-tête du fichier, qui disait moins que la décision qu'il citait** — corrigée au lot 14. Toute issue future qui voudra rouvrir ce fichier doit **revérifier la même condition**, jamais invoquer ce précédent comme une permission acquise |
| 60 | **Un `<ul>` sans nom accessible n'est pas une dette d'accessibilité** | 2026-08-31 | `blocks/lien-de-recours/rendu.php:372-375` déclare honnêtement qu'un lecteur d'écran annoncera « liste, 1 élément » sans dire de quoi, pour l'enveloppe posée par #39. La revue tranche que **ce n'en est pas une** : aucun critère WCAG AA n'impose un nom accessible à une liste. Y remédier supposerait de **fabriquer une chaîne de domaine dans `mtb-core` hors de tout arbitrage de `MASTER.md`** — ce que la chaîne a eu raison de refuser. Classé plutôt que laissé à flotter, pour qu'aucune chaîne future n'ouvre une issue `a11y` sur ce point. Même sort pour la cible tactile de 44 px (`MASTER.md` §12.10) : le crochet est `.mtb-liens-de-secours > li > a` (`base.css:954-958`) et le `<ul>` émis est **nu**, donc l'orphelin enveloppé ne l'obtient pas — mais **l'orphelin d'avant ne l'obtenait pas davantage, pour exactement la même raison**. Manque préexistant, non régression, sur un chemin qu'aucun geste d'édition n'atteint (`inserter: false` vérifié **à l'exécution dans le registre**, et un `post_content` forgé par WP-CLI a été nécessaire pour l'atteindre). Ligne de contrat, pas d'issue ; condition de réveil : le jour où un `parent`/`ancestor` rendrait le bloc insérable |
| 59 | **La décision 50 porte sur les fixtures fabriquées, pas sur le contenu repris par la migration** | 2026-08-31 | Q17 avait tranché « contenu de démonstration **fictif assumé** ». #46 a fait tourner `wp mtb importer-portees-chiens` comme **prérequis de capture**, si bien que trois captures du manuel impriment désormais des noms et des dates réels de l'élevage. La revue juge la chose **conforme, et le meilleur choix disponible**, en séparant deux objets que la question réunissait. **D11 interdit d'inventer ou de reformuler** — or #46 n'a rien fait de tel : elle a exécuté le mécanisme construit pour que la copie soit exacte et auditable, et la revue est allée **recouper à la source** les identifiants lisibles à l'écran (« U2 2023 », « V2 2024 » → `docs/migration/source/portees/portee-u2-2023.md` et `portee-v2-2024.md`, orthographiés à l'identique). C'est D11 **honorée**, pas violée. **Q17 et la décision 50 gouvernent un autre objet : les fixtures**, c'est-à-dire ce que le projet **fabrique** pour peupler une base jetable ; le risque nommé au lot 8 était celui d'une **recopie à la main, approximative et non traçable**, pas celui d'une migration versionnée et rejouable. Le corollaire opposable — « toute fixture future reste manifestement fictive » — est intact : `DEMO1 2025`, affixe « de Démonstration » et `LOF DEMO 000001` cohabitent avec le réel sans avoir été rendus plausibles. **Sur le fond, #46 a échangé un défaut certain contre aucun** : sans la migration, trois captures du manuel montraient l'image barrée « PHOTO DE TEST », et une éleveuse qui n'y reconnaît pas son site se défie de son manuel dès la première page. Objection examinée et écartée : « une capture fige un état du contenu » vaut d'une capture de contenu, pas de ces trois-là, qui sont des captures **d'écran d'administration** dont l'enseignement est un rang de champ et un geste — le nom de la portée est un décor. **Formulation à opposer aux chaînes futures : faire tourner la reprise pour obtenir une capture est permis et souhaitable ; semer du réel plausible à la main dans des fixtures reste interdit.** Réserve consignée, non une faute : deux des chaînes imprimées sont **fabriquées par la migration** (`migration/portees-chiens/photos.php:384-394` et `:414-430`), leur dette d'accessibilité étant déjà déclarée au docbloc `:397-405` et expliquée à l'éleveuse (`docs/guide/contenu-repris-de-l-ancien-site.md:225-234`) — **toute issue qui rouvre `photos.php:384-430` doit reprendre les trois captures concernées**, faute de quoi elles décriront en silence un écran qui ne dit plus la même chose |
| 58 | **Dans ce dépôt, « §X seul » est le titre d'une entrée de journal, pas un compte d'octets** | 2026-08-31 | Arbitrage rendu au lot 13 sur remontée de la chaîne #44, **contre ma propre consigne d'empreinte**. J'avais borné #44 à « §10.2 uniquement » ; appliqué à la lettre, cela aurait rendu **faux** l'en-tête `MASTER.md:8-9` (« seuls les §7.7 et §9.5 sont amendés ») le jour même où §10.2 l'était — une péremption neuve dans la ligne la plus lue du document, ouverte par l'issue qui prescrit de fermer les péremptions. Le précédent tranche : les révisions **1.1 (« §7.7 seul. »)** et **1.2 (« §9.5 seul. »)** ont **chacune** modifié leur section, écrit leur entrée au §16 **et** bougé l'en-tête. La formule désigne donc la **section de fond rouverte**, par opposition à « rien d'autre n'est rouvert ». L'entrée 1.2 écrivait de surcroît que « §10.2 et §10.3 ne sont pas touchés, leurs écarts ayant chacun leur propre issue » : le document **attendait** #44. Conséquence pratique : une empreinte portant « §X seul » sur `MASTER.md` **inclut** l'en-tête et le journal des révisions, et **rien d'autre** — au lot 13, §10.3 (T55, #43), les deux moitiés de T62 et T54 sont restées intactes, vérifié au diff. Corollaire de méthode, plus large que le fichier : **une chaîne qui démontre par un précédent du dépôt qu'une consigne du lead est mal formulée a raison de la remonter plutôt que de la suivre** — livrer la version stricte et déclarer la péremption en dette aurait été, selon ses propres mots, « le choix sûr, pas le choix juste » |
| 57 | **Un fait recopié que rien ne confronte est une affirmation** | 2026-08-28 | Formulée par un agent du lot 8 en ajoutant, de sa propre initiative, un septième contrôle qui exige que chaque balise recopiée figure **au caractère près** dans le HTML archivé, puis en l'éprouvant sur la falsification d'**une seule espace**. C'est la généralisation de la décision 46 : la provenance ne suffit pas, il faut le contrôle qui la vérifie. |
| 56 | **Sur ce projet, le défaut n'est pas la mesure manquante mais la mesure périmée** | 2026-08-28 | Trois cas dans le seul lot 8 : une vérification d'accessibilité **passée sur une page 404** (donc verte pour de mauvaises raisons), un état d'index lu juste avant qu'il ne change, et un correctif de code arrivé **43 minutes après l'import** qu'il devait corriger — l'importeur ne mettant jamais à jour l'existant, corriger le code n'a pas corrigé l'état. **Une vérification qui passe parce que son objet a disparu ressemble en tout point à une vérification qui passe.** Corollaire opératoire : toute mesure doit affirmer la **présence** de ce qu'elle examine avant de le juger. |
| 55 | **Une valeur recopiée se stocke avec sa provenance, jamais nue** | 2026-08-28 | Arbitrage rendu sur `_mtb_robots_source`, que deux chaînes parallèles écrivaient sous deux formes incompatibles — une chaîne pour #20, un tableau `valeur`/`source`/`extrait` pour #21 — **pendant que les deux documentations affirmaient l'alignement**. La forme à provenance l'emporte : #24 doit pouvoir lire les cinq contenus avec un seul code. Prix payé : la base a dû être **détruite et reconstruite**, l'import ne mettant jamais à jour l'existant. |
| 54 | **Le paragraphe vide de charpente est une propriété de la sérialisation, jamais de la donnée** | 2026-08-28 | Une chaîne demandait d'assouplir le contrôle qui refuse les paragraphes vides pour rendre une fiche éditable. Refusé : ce contrôle porte un fait durement acquis — *« une ligne d'espacement de l'éditeur IONOS n'est pas un paragraphe »* — et l'assouplir aurait payé une contrainte structurelle avec une monnaie qui ne lui appartient pas. Le `<p></p>` naît à l'écriture du bloc ; aucun fichier de données n'en porte. **D1 et D12 tiennent ensemble.** |
| 53 | **Quand une règle est contournée trois fois, on amende la règle, pas une quatrième exemption** | 2026-08-28 | `MASTER.md` §7.7 prescrivait `break-word` pour les URL ; trois emplacements livrés employaient `anywhere`. La mesure a montré que **la règle était fausse** : les occasions de coupure de `break-word` ne comptent pas dans le calcul des tailles min-content, donc **347,25 px sous `break-word` comme sous `normal`**, contre 15,5 px sous `anywhere`. `MASTER.md` passe en 1.1 ; `th`/`td` restent exceptés, avec leur raison. |
| 52 | **Les cinq pages que la source retire des moteurs sont reprises en recopiant son statut** | 2026-08-28 | Q23 tranchée pour l'import après que l'utilisateur a demandé de « reprendre ce qu'il y a sur le site actuel ». On recopie le fait — hors menus, non indexées — **sans inventer le motif**, qui est un fait d'élevage que le site n'énonce jamais. Le fait est stocké avec sa provenance et écrit ; **il n'est pas honoré** tant que #23 et #24 ne l'ont pas rendu, et aucun document ne doit laisser croire l'inverse. |
| 51 | **Un jeton HMAC horodaté remplace le nonce sur le formulaire public** | 2026-08-23 | Écart assumé à `CLAUDE.md` (« nonces sur toute écriture »), **ratifié par `review-mtb`** après examen, au motif que le substitut est **plus fort et non plus faible**. Quatre raisons, toutes vérifiées : le formulaire est **public, anonyme et n'écrit rien**, donc il n'y a aucune session à protéger et aucun CSRF à empêcher — l'attaquant peut poster directement, nonce ou pas ; le nonce **ne sait pas exprimer un âge minimal**, que le brief §9 exige explicitement (« délai minimal de soumission ») ; derrière un cache, un nonce périmé afficherait « Êtes-vous sûr de vouloir faire cela ? », incompréhensible pour le public du brief §2 et **destructeur** puisqu'un courriel perdu est perdu ; le jeton échoue **fermé** (`jeton.php:105-107, 123-129`), compare en temps constant (`hash_equals`) et **ne vide jamais les champs** — un refus se rattrape d'un clic. Ses deux limites sont **écrites et non cachées** (`jeton.php:19-24`) : rejouable dans l'heure, aucune limitation de débit. |
| 50 | **Le contenu de démonstration est fictif et manifestement fictif** | 2026-08-23 | Q17 tranchée par l'utilisateur. `DEMO1 2025`, affixe « de Démonstration », `LOF DEMO 000001` : personne ne peut confondre une fixture avec un fait d'élevage. Raison du refus des deux autres options : **du réel recopié dans une base jetable finit un jour repris pour vrai**, et aller le chercher sur mtbrabant.com serait faire un bout de la chaîne #19 hors de son périmètre, avec le risque d'une recopie approximative ; ne rien semer, à l'inverse, ramènerait à l'état où chaque agent de test créait son contenu à la main et où personne ne « voyait » le site en le démarrant. **Corollaire opposable** : toute fixture future reste manifestement fictive — un numéro LOF plausible qui traîne dans une démonstration est exactement ce qui finit recopié. |
| 49 | **La disjonction des empreintes fichiers protège les fichiers, pas la base de données** | 2026-08-23 | Sur le seul lot 7, la base partagée a été détruite (`down -v`) **quatre fois pendant que des chaînes sœurs mesuraient** : trois par la chaîne #29, une par `docker-mtb`, qui a remis à zéro la page Contact et fait disparaître la page d'accueil sous la chaîne #22 en pleine vérification d'écran. Le projet n'a **aucune isolation** — mono-branche, arbre unique, pile unique — et le verdict d'empreinte de `github-boards`, qui est la seule protection du parallélisme, ne dit **rien** de l'état partagé de la base. Or une chaîne qui doit prouver un départ à froid **doit** détruire les volumes : un `wp option get` sur une base déjà peuplée ne prouve rien. Ce n'est donc pas une faute d'agent, c'est un trou du dispositif. **Parade retenue pour l'instant : la coordination par l'orchestrateur** — une chaîne annonce son cycle destructeur, l'orchestrateur relaie, et l'agent regroupe ses épreuves froides en une seule au lieu de les égrener. `docker-mtb` a décrit deux paliers supérieurs sans les implémenter (un jeu de volumes nommé pour une pile jetable sous un second nom de projet Compose ; une cible `make verify-cold` seule autorisée à détruire) — le premier coûte des ressources et un cinquième environnement, le second ne fait que formaliser la discipline actuelle. À rouvrir si le prochain lot se cogne encore. |
| 48 | **La mention d'information du formulaire ne dit que ce qui est vérifiable dans le code, et la destination reste `mtbrabant@gmail.com`** | 2026-08-23 | Q-22-1 et Q-22-2 tranchées par l'utilisateur. Le brief §9 exige finalité, destinataire, durée, droit d'accès et de suppression — mais la **décision 45** (aucune conservation) rend la moitié de ces mentions **paradoxale** : annoncer une durée de conservation quand il n'y a pas de conservation serait faux, et écrire « vous pouvez demander la suppression » engagerait Fabienne à vider sa boîte de courriels sur demande, ce que ni le code ni personne ne garantit. La mention livrée par défaut dit donc **trois choses seulement, chacune adossée à une ligne de code** : à quoi sert le message, à qui il est envoyé, et qu'aucune copie n'est conservée sur le site. **Aucun responsable de traitement n'est nommé** — la raison sociale manque encore des Mentions légales (reste de #17), et l'inventer serait un fait faux dans un texte légal. La mention reste un **champ libre** : Fabienne la remplace par la sienne quand elle l'a. Destination : la valeur de l'écran Coordonnées de #38, **jamais un littéral**. Risque assumé et à traiter à la mise en ligne (Q5) : un envoi depuis le futur domaine vers Gmail peut partir en indésirables si SPF/DKIM ne sont pas réglés — et **sans copie en base, un message non délivré est perdu sans que le site puisse le savoir**. |
| 47 | **Un lot n'existe pas tant qu'il n'est pas poussé : `git rev-list origin/main..HEAD` appartient à l'étape 0** | 2026-08-23 | Le lot 6 a été développé le 2026-08-20 par trois chaînes qui sont **mortes avant la validation de lot**. Code committé, contrats gelés, guides écrits, aperçus pris — et rien nulle part pour le dire : `ETAT.md` annonçait toujours ce lot comme « prochaine action », et le board le montrait « In Progress ». **Quinze commits de travail fini ont failli être perdus** parce qu'aucune des trois sources de vérité de la reprise de contexte ne regarde l'écart entre le dépôt local et le distant. La lecture d'`ETAT.md`, du brief et du board ne suffit pas : `ETAT.md` **ment par omission** quand la chaîne qui devait l'écrire est morte. Conséquence de méthode : l'étape 0 compare désormais systématiquement `main` à `origin/main`, et un écart non nul se traite **avant** de constituer le lot suivant. |
| 46 | **Un crochet CSS émis et jamais stylé n'est pas une coquille : c'est un fait faux en attente** | 2026-08-23 | La fiche d'un chien affichait « **Eenhoorn Sire Eenhoorn** » — un nom d'usage et un affixe collés faute d'**une seule** déclaration, donc une généalogie que personne n'a jamais saisie. C'est la classe d'erreur que D11 et la règle d'exactitude du domaine existent pour empêcher, et c'était le **seul** défaut du lot à **produire** un fait faux au lieu d'en omettre un — sur un lot par ailleurs sans le moindre CRITICAL. Aggravant : la **même** donnée était correctement séparée sur la fiche de portée, deux fiches du même lot pour deux lectures. Corollaire retenu du contrat #15 : « chaque crochet non stylé est une promesse que quelqu'un croira un jour ». Deuxième moitié de la décision, de même rang : la revue a **aussi** bloqué sur un **silence** — la carte parent amputée du portrait et du nom complet que `MASTER.md` §7.4-2 prescrit, sans qu'aucun contrat, aucune dette ni aucune feuille ne le déclare (T32). **Un écart non écrit n'est imputable à personne** ; c'est le silence qui bloquait, pas le manque. |
| 45 | **Le formulaire de contact n'écrit rien en base : envoi par courriel uniquement** | 2026-08-21 | Q3 tranchée par l'utilisateur. Le message part vers l'adresse de l'élevage et rien n'est stocké sur le site. Conséquences pour #22 : aucun schéma de stockage, aucun écran de consultation, aucune purge automatique à écrire, et la **mention RGPD est courte et vraie** — finalité, destinataire, pas de durée de conservation à annoncer puisqu'il n'y a pas de conservation. Aucune donnée personnelle ne repose sur le site, ce qui sert directement la règle transverse « zéro traceur ». Risque assumé et à écrire dans la fiche d'aide : **un courriel perdu est perdu**, le site n'en garde aucune copie. L'anti-spam reste sans service tiers (piège à robots + délai minimal de soumission). |
| 44 | **Une capacité s'accorde à la requête, jamais en base.** `edit_theme_options` est donnée à Fabienne par un filtre `user_has_cap`, sur la seule requête de l'écran des menus | 2026-08-19 | La checklist de #18 disait `add_cap`. Un `add_cap` nu **survit au thème** et ouvre `Apparence > Éditeur`, donc l'éditeur de site entier — styles globaux et gabarits — à un compte Éditeur. Le filtre pose trois conditions cumulatives (capacité demandée, `edit_pages` détenue, requête reconnue comme celle des menus ou l'une de quatre actions AJAX), n'écrit rien dans `wp_user_roles` et ne laisse aucun résidu. **Vérifié à l'écran en session `fabienne`** : `nav-menus.php` 200, `themes.php` 403, `site-editor.php` 403, `options-general.php` 403, et le HTML de `nav-menus.php` ne contient **aucune** occurrence de `site-editor`. La revue a cherché une fuite et n'en a trouvé aucune. |
| 43 | **Une fiche d'aide fausse est un défaut bloquant, au même titre qu'un bug.** Trois ont bloqué le push du lot 5 | 2026-08-19 | Le code du lot était solide — aucun CRITICAL, aucune faille, aucune fuite de capacité — et la revue a **bloqué quand même**. Les trois fiches disaient à l'éleveuse : que le site ne regroupe pas les chiffres d'un numéro (il le fait, dans l'encart d'appel) ; qu'elle peut poser une page non publiée dans son menu (l'écran ne propose que les pages publiées) ; que ses deux menus « existent déjà » (rien dans le dépôt ne les crée — vrai sur la base de dev, faux chez elle). Une fiche qui ment est pire qu'une fiche absente : elle apprend à l'éleveuse que le guide ne se croit pas, et l'une des trois lui demandait de **signaler comme anomalie le comportement normal du site**. Corollaire de méthode : **chaque affirmation d'une fiche se vérifie à l'écran**, sur la pile qui tourne. Les trois avaient franchi une chaîne complète, une passe d'intégration et une revue. |
| 42 | **Un audit d'accessibilité mesure une propriété, jamais une expérience** | 2026-08-19 | Les deux `<nav>` n'avaient pas de nom accessible (`landmark-unique`). La correction évidente — l'attribut `ariaLabel` du bloc — rend `aria-label="Menu principal Menu principal"` sur WP 6.9 : deux mécanismes du cœur émettent l'attribut pour la même valeur (`navigation.php` via `get_unique_navigation_name()`, et le support `aria-label` ajouté en 6.8), et `class-wp-block-supports.php:184` range `aria-label` dans la liste des attributs **concaténés**, avec `class` et `style`. **La vérification serait passée au vert** — les deux noms diffèrent bien — pendant qu'un lecteur d'écran bégayait sur chaque page. Le nom est donc posé **au rendu**, une seule fois, depuis `get_registered_nav_menus()`, et **seulement s'il est absent** pour qu'un futur correctif du cœur reprenne la main. Interdit gravé au contrat §4.5 bis : ne pas « simplifier » en remettant `ariaLabel` dans le balisage. |
| 41 | **Le thème peut interroger les API de navigation du cœur ; il ne lit aucune donnée d'élevage** | 2026-08-19 | Le contrat de #18 écrivait « le thème n'interroge **jamais** la base directement ». `functions.php` appelle `get_post()`, `get_nav_menu_locations()`, `wp_get_nav_menu_object()` et `WP_Classic_To_Block_Menu_Converter::convert()`. Aucune donnée d'élevage n'y transite (grep : **0** occurrence de `mtb_get_`, `$wpdb`, `WP_Query`, `get_posts`, `get_option` dans tout le thème) et la navigation est un domaine de thème par construction WordPress. **Écart ratifié et le contrat amendé** — sinon #16 et #17 hériteraient d'une règle que #18 a déjà enfreinte, ce qui est la pire façon de transmettre une convention. |
| 40 | **Une entrée de menu mise en retrait s'affiche.** Ce qui n'existe pas, c'est la hiérarchie visible | 2026-08-19 | Le contrat **et** la fiche affirmaient tous deux qu'une entrée en retrait ne s'afficherait pas. Mesuré : elle s'affiche, à sa place dans le flux, juste après son parent — c'est le comportement voulu de `entete-pied.css:265-278`, dont le commentaire l'écrit noir sur blanc. Le manque réel est le **signal** qu'elle dépend de son parent ; le montrer exigerait un dépliage (donc du JavaScript, refusé) ou un parti visuel qu'aucun § de `MASTER.md` ne décrit. Pour `lead-design-mtb`. Piège de méthode associé : le premier test de ce point a **confirmé l'erreur** parce qu'il visait un identifiant de menu périmé — la passe d'intégration avait reconstruit les menus, et l'entrée orpheline disparaissait. Une conclusion fausse qui **concorde avec le texte existant** est la plus difficile à attraper. |
| 39 | **Le tableau de vocabulaire de `MASTER.md` §10 tranche, même quand la chaîne a raison sur le fond** | 2026-08-18 | #11 libellait son champ « Description de l'**image** » au motif exact qu'un plan d'accès n'est pas une photographie — argument recevable. Mais §10.2 fige « Description de la **photo** », et `fiche-information` l'emploie déjà verbatim depuis le lot 3. Deux libellés pour le même champ dans le même catalogue, c'est une éleveuse perdue, et une chaîne n'amende pas le système de design sur lequel elle bute. **Aligné sur §10.2** (`b90bcf1`) ; la distinction photographie / document part en question ouverte pour la prochaine révision `lead-design-mtb`. |
| 38 | **Un composant peut fixer la mise en forme d'une donnée recopiée ; il ne peut jamais en changer la valeur** | 2026-08-18 | BRIEF §7 impose de reprendre `0680505619` « tel quel ». #10 le **groupe par paires** à l'affichage quand il fait exactement dix chiffres (`encart-appel/rendu.php:87-93`) et garde les chiffres bruts dans le `tel:` ; #11 ne groupe pas. Ni chiffre ni ordre n'est touché : la contrainte porte sur la **valeur**, pas sur sa typographie, et le public de BRIEF §2 doit pouvoir lire et dicter ce numéro. Conséquence assumée : **les deux composants affichent le même numéro de deux façons**, ce qui reste invisible tant qu'aucune page ne porte les deux. À fermer avant #19-#21. |
| 37 | **Trois écrans de réglage peuvent employer trois conventions différentes, si ce sont trois gestes différents** | 2026-08-18 | #9 n'a **aucun** libellé (le champ *est* le bloc, tapé en place) ; #10 pose « Téléphone affiché », où vider signifie *« affiche le numéro de l'élevage »* ; #11 pose « Téléphone », où vider signifie *« retire cette ligne »*. Aligner les libellés rendrait **impossible** de retirer la ligne Téléphone d'un bloc de coordonnées, acte légitime sur une page Contact. Ce qui doit être identique, c'est le **mot** désignant une même chose (décision 39), pas la mécanique d'un champ qui fait autre chose. |
| 36 | **Un contraste n'est tenu que lu sur des pixels rendus ; un calcul concordant n'en est que la promesse** | 2026-08-18 | Le lot 3 s'était clos sur des contrastes calculés (décision 35 le dit en toutes lettres). Le lot 4 a **relevé les couleurs réellement rendues** — couleur modale du fond, encre la plus éloignée en luminance, anti-crénelage écarté — et recalculé : **les onze valeurs annoncées par les trois chaînes se confirment, écart zéro**. La méthode marche et devient la règle. Corollaire utile : le **4,70:1** de #10 n'est pas le texte du bouton (**5,25:1**) mais sa **limite non textuelle** (WCAG 1.4.11) ; les deux chiffres coexistent et sont tous deux justes. |
| 35 | **Le contraste du texte du bandeau est borné par construction : plancher absolu 5,70:1**, quelle que soit la photo | 2026-08-18 | Le bloc de texte porte le voile en `background-size: 100% 300%` ancré en bas, donc il n'expose que le **premier tiers** du dégradé : l'opacité sous le texte reste entre **0,7227** (bord haut) et **0,86** (bord bas), indépendamment de la hauteur du bloc, de la longueur de l'accroche, du zoom **et du recadrage de la photo**. `--calcaire` sur le composé donne **5,70:1 sur une photo blanche pure** — le pire cas concevable — et 14,4:1 sur une photo sombre. **AA tenu partout**, AAA manqué seulement sous le bord haut d'une photo blanche. Vaut mieux qu'une mesure ponctuelle : le résultat ne dépend d'aucune photo, donc aucun recadrage ne peut le dégrader. Réserve : calcul sur le modèle du dégradé et l'interpolation sRGB, **pas une lecture de pixels rendus** — le contenu d'essai avait disparu au démarrage à froid. Le 6,31:1 mesuré par l'intégration tombe bien entre les lignes « ciel délavé » (6,08) et « herbe claire » (7,23). |
| 34 | **On appelle WP-CLI par `make wp`, jamais par un `docker compose run wpcli` nu** | 2026-08-18 | Un `run` crée un conteneur jetable qui **rejoue tout `provision.sh`** puis reste bloqué sur le `tail -f /dev/null` du service. **17 conteneurs `mtb-wpcli-run-*` zombies** ont été trouvés au démontage, certains vieux de 15 heures, et ils **empêchaient `docker compose down -v`** (« Network / Volume resource is still in use »). `make wp` fait un `exec` sur le conteneur du service et ne laisse rien derrière. Attention aussi : ce poste fait tourner six autres projets Docker — tout nettoyage doit être filtré sur le préfixe `mtb-`. |
| 33 | **On regarde la page rendue avant de clore un lot.** Les aperçus vont dans `docs/apercus/lot-<n>/`, sans retouche | 2026-08-18 | Deux défauts visibles ont survécu à six chaînes, à une passe d'intégration de 43 URL et à une revue complète — **le bandeau amputé de 17 % de sa largeur et trois bords gauches désalignés** — parce que tout le monde mesurait des choses justes sans jamais ouvrir l'image. La capture les a rendus évidents en une seconde. Corollaire : les aperçus se prennent **avant** un démontage de la stack, sinon le contenu d'essai disparaît et il ne reste du lot qu'un rapport. |
| 32 | **Une largeur de bloc juste ne prouve rien sur le cadre qu'elle contient.** Tout composant dont un cadre porte `aspect-ratio` **et** un plafond de hauteur se mesure **large**, et la vérification porte sur le **cadre**, jamais sur le bloc | 2026-08-18 | Le bandeau occupait bien 0 → 1520 px, mais son cadre interne s'arrêtait à **1194,66 px** : `aspect-ratio: 21/9` + `max-block-size: 32rem` + une largeur venue de l'étirement font **re-dériver la largeur depuis la hauteur plafonnée** (512 × 21/9 = 1194,67, au centième). Correctif : `inline-size: 100%`, une déclaration — la largeur devient définie, le ratio ne calcule plus que la hauteur, et c'est la photo qui se recadre. **Le défaut était strictement proportionnel à la largeur et nul sous 1194,67 px** : la recette AA à 360 px et l'aperçu de l'éditeur ne pouvaient structurellement pas le montrer. Mesuré : 245 px de vide à 1440 px, **1365 px — 53 % de la fenêtre — à 2560 px**. |
| 31 | **Un lot peut dépasser 3 issues sur arbitrage explicite de l'utilisateur.** Le lot 3 en a porté **6** | 2026-08-17 | `CLAUDE.md` plafonne à 3 pour cause de tokens ; l'utilisateur a demandé le maximum de parallélisme pour obtenir un visuel présentable en un jour. Le plafond reste la règle par défaut. **Coût observé, à connaître avant de recommencer** : mes arbitrages arrivaient régulièrement **après** que les chaînes avaient écrit leur code — trois consignes de nommage, la catégorie de blocs et l'emplacement du CSS ont dû être rattrapées après coup, et une convention imposée (l'état vide) n'a pas pris chez les six. À six, l'orchestrateur devient le goulot. |
| 30 | **Une primitive nommée par `MASTER.md` s'écrit en classe nue ; seule une surcharge de contexte se scope sous la classe du bloc** | 2026-08-17 | `.mtb-dispo` (§3.3) et `.mtb-photo` (§6.2) sont émises par plusieurs composants. #13 les avait scopées pour éviter que deux feuilles nues fusionnent dans la cascade — argument réel. Mais #12 a montré que **le scoping ne protège que si tout le monde scope** : un descendant de `.mtb-liste-portees` reste un `.mtb-dispo`, donc les règles nues de la sœur l'atteignent quand même, de façon asymétrique et plus difficile à lire. Scoper la primitive à quatre ou cinq composants institutionnaliserait **T9**. Le test à appliquer, écrit au contrat #12 §10.4a : la déclaration décrit-elle **ce qu'est** le badge (couleur, forme, casse) ou **la place dont il dispose ici** ? Le premier est la primitive, le second appartient au bloc. |
| 29 | **`WP_DEBUG` vaut `true` sur les requêtes web et `false` en WP-CLI**, dans cette stack | 2026-08-17 | `wp-config.php:116` le définit par `!!getenv_docker('WORDPRESS_DEBUG','')` ; le service `wordpress` porte `WORDPRESS_DEBUG=1` (`compose.yaml:42`), le service `wpcli` ne le porte pas. Deux chaînes se contredisaient — **elles avaient toutes les deux raison, dans leur contexte**. Conséquence permanente : une affirmation « aucune notice PHP » n'a de sens que **mesurée sur une page rendue**, jamais depuis WP-CLI. |
| 28 | **Le CSS d'un bloc vit dans le thème** (`assets/css/blocs/<espace>-<nom>.css`), servi par la boucle générique de `functions.php` | 2026-08-17 | Contrat #1 §8 : l'extension n'émet aucune règle visuelle. Le mécanisme existait déjà et rend `functions.php` inutile à rouvrir (décision 9). **Piège découvert et corrigé dans ce lot** : les feuilles étaient déclarées avec `deps => array('mtb-jetons')`, or `mtb-jetons` n'était enregistrée que sur `wp_enqueue_scripts`, jamais en administration — `WP_Dependencies::all_deps()` **abandonnait alors la feuille entière, en silence**, donc aucun composant n'était habillé dans l'éditeur. Une ligne. **Exception assumée : #8 sert sa feuille depuis l'extension** (dette T15), non basculée à chaud. |
| 27 | **Un `require` vers un fichier absent dans un `bootstrap.php` met tout le site en erreur fatale**, `wp-admin` compris | 2026-08-17 | Prix du chargeur à auto-découverte (décision 9) : le chargeur relance l'exception quand `WP_DEBUG` est vrai. Arrivé deux fois dans le lot ; une chaîne sœur y a perdu **9 de ses 25 vérifications**. Règle : ne jamais commiter un `bootstrap.php` dont un `require` pointe un fichier non écrit. |
| 26 | **Un composant sans contenu ne s'affiche pas au visiteur ; l'apparence d'état vide n'existe que dans l'éditeur** | 2026-08-17 | `MASTER.md` §9. Écrire « aucune portée pour le moment » sur l'accueil d'un élevage qui en compte 27 donnerait un site à l'arrêt. Forme imposée : étiquette = nom du bloc en capitales, puis **une seule phrase** « Ce bloc n'affiche rien tant que <ce qui manque>. » **Exceptions attestées en §9.3** : un filtre d'année sans résultat affiche bien « Aucune portée pour cette année. » **au visiteur** — c'est une réponse, pas un trou. Sept corps d'issues promettaient l'inverse et ont été corrigés. |
| 25 | **La catégorie de blocs « Mont Brabant » est livrée une seule fois**, par `includes/blocks/categorie-mtb/` | 2026-08-17 | Contrat #1 §10. Elle n'était dans **aucune** des six empreintes alors que les six blocs en dépendent : sans elle, les composants existent mais Fabienne ne les trouve dans aucun panneau de l'insérteur — contrainte 1 manquée sur un site qui répond 200. **Piège de vérification** : compter les déclarations doit chercher l'**appel** `add_filter( *'block_categories_all'`, jamais la chaîne nue, sinon les commentaires qui documentent l'absence du filtre sont recomptés comme des déclarations (deux faux positifs dans ce lot). |
| 24 | **Aucun bloc ne peut être inséré dans une fiche portée, chien ou résultat.** Les composants vivent dans les Pages ; les gabarits de #16/#17 appellent la **fonction de rendu réutilisable** exposée par chaque module | 2026-08-17 | Conséquence directe de la décision 17 (écran de saisie classique, `use_block_editor_for_post_type` → `false`). Le corps de l'issue #8 promettait à l'éleveuse une insertion impossible ; corrigé. La fonction de rendu évite que le thème réécrive un composant — contrainte 3 appliquée au code. |
| 23 | **En parallèle, on commite par `git commit -m "…" -- <chemins>`**, jamais par `git add` suivi d'un `git commit` nu | 2026-08-17 | **L'index git est partagé entre les chaînes.** Constaté sur ce lot : une chaîne a ajouté ses huit chemins, et `git diff --cached` en a rendu **seize** — huit fichiers d'une sœur étaient entrés dans l'index **entre son `git add` et sa vérification**. `git add` puis `git commit` ne peut pas gagner cette course, et le `git reset` de rattrapage **désindexe aussi le travail du voisin**. La forme avec chemins après `--` prend le contenu dans l'arbre de travail et **ignore totalement l'index** : aucune course, aucun risque pour les autres. Ajouter d'abord les seuls fichiers **neufs**, qu'un pathspec ne peut pas atteindre s'ils ne sont pas suivis. |
| 22 | **Les trois fiches d'aide nomment « En cas de doute » leur section de recours**, terme du BRIEF §13.3 | 2026-08-17 | Elles en avaient trois noms différents. C'est le seul endroit du guide où l'éleveuse arrive **inquiète** : ce n'est pas là qu'il faut la faire hésiter, et la vue d'ensemble de #25 aurait dû composer avec trois intitulés. |
| 21 | **Les compteurs de mâles et de femelles d'une portée sont stockés en chaîne, pas en entier** | 2026-08-16 | C'est la seule façon de distinguer « **0 mâle** » — un fait d'élevage légitime — de « non renseigné ». En entier, WordPress rend `0` dans les deux cas, et le site affirmerait qu'une portée n'a aucun mâle alors que rien n'a été saisi. D11. |
| 20 | **Jamais `sanitize_text_field`, `wp_strip_all_tags` ni `wp_kses` sur une valeur recopiée.** Chaque module écrit son assainisseur : UTF-8 invalide et caractères de contrôle seulement | 2026-08-16 | Elles passent par `strip_tags()` : une valeur commençant par `<` — `<60%` en dysplasie, plausible — est **vidée en silence**. C'est D11 enfreinte **par l'outillage**, sans que personne l'ait voulu. Sûr sans retirer les balises parce que l'échappement est systématique en sortie et que seul un compte `edit_post` écrit. Coût assumé : trois copies de l'assainisseur, les empreintes disjointes interdisant un fichier partagé (dette T-#5-e). |
| 19 | **Le type qui possède la donnée possède la lecture.** Aucun module ne réimplémente une requête sur un type qu'il n'enregistre pas ; il appelle la fonction de la chaîne propriétaire sous `function_exists()` | 2026-08-16 | Le chargeur emploie `scandir()`, qui parcourt par ordre alphabétique : deux fonctions homonymes ne produisent pas d'erreur mais un **ombrage silencieux**, la mauvaise implémentation gagnant sans un mot sur un site qui répond 200. Corollaire : la garantie de forme **et d'ordre** appartient à la chaîne propriétaire, jamais au consommateur. |
| 18 | **Enveloppe de champ `array( 'libelle', 'valeur', 'affichage' )`** pour toute valeur exposée au thème ; `colonnes` + `lignes` + `cellules` aux mêmes clés pour le tabulaire | 2026-08-16 | La donnée brute d'un côté, ce qui s'imprime de l'autre, et le **libellé fourni par le serveur** : c'est ce qui garantit que le thème n'a jamais à composer un texte, donc jamais à accorder, traduire ou inventer. `affichage` vaut « Non renseigné » quand le champ est vide — sauf l'élevage d'un parent extérieur et le **pays** d'un résultat, où le vide signifie « français », pas « absent ». |
| 17 | **Écran de saisie classique sur les trois types**, pas l'éditeur de blocs (`use_block_editor_for_post_type` → `false`, ou `show_in_rest => false`) | 2026-08-16 | **Une fiche ne se compose pas, elle se remplit.** Une portée, c'est douze faits et un paragraphe ; en blocs, les douze faits tombent sous un canevas que la fiche n'utilise jamais, et Fabienne fait défiler à chaque saisie pour atteindre ce pourquoi elle est venue. Effet de bord acquis : la dette T7 (couleur hors jetons) devient inatteignable sur une fiche. À dire ainsi dans le guide : **« les pages se composent, les fiches se remplissent »**. |
| 16 | **Clés de méta `_mtb_` à tiret bas initial** (et non `mtb_`), **fonctions de lecture `mtb_get_*`** | 2026-08-16 | Le tiret bas rend la méta **protégée**, donc jamais listée dans « Champs personnalisés » : c'est la garantie **mécanique** qu'aucune clé technique n'atteint l'écran de l'éleveuse (MASTER §10.4). Le préfixe `mtb_get_` vient du contrat gelé #1. Exception tranchée : `mtb_resultat_disciplines()` et `mtb_resultat_sexes()` restent sans `get` — ce sont des tables de correspondance, pas des lectures de contenu. |
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
| 11 | **NEUF disciplines de travail** — révisée le 2026-08-16. Graphies de `MASTER.md` §10.2 : RING · IGP / RCI · Mondioring · Obéissance · Pistage · Recherche utilitaire · Sauvetage · Truffe · **Autres disciplines** | 2026-08-15, **révisée le 2026-08-16** | Huit d'abord : le brief §5.3 annonce « ~7 » puis en énumère huit, l'énumération faisant foi. **Puis la chaîne #5 est allée lire `mtbrabant.com/travail/`** : la page porte une section « Autres disciplines : » contenant quatre lignes réelles, dont **Agility** et **Brevet Maitre Chien Drogue**, qu'aucune des huit ne peut exprimer. Avec une liste strictement close, ces deux lignes étaient **inexprimables, donc perdues à la reprise** — contrainte 4 et D4. Le neuvième libellé est **recopié du titre de section du site source**, pas inventé. « Truffe » reste, bien qu'absente du site : le brief et §10.2 la portent tous deux, comme le champ *Conducteur*, vide sur toutes les lignes reprises. **La ligne « Cavage » du source se range sous « Autres disciplines », comme le site la range lui-même** — aucune déduction n'est nécessaire. Réserve honnête portée par #5 : une valeur fourre-tout perd la discipline réelle de ces quatre lignes ; ajouter chacune coûterait **une ligne**, et aucune donnée n'est encore saisie. Voir Q12. |
| 10 | **Tout composant tableau rendu côté serveur émet `data-libelle="…"` sur chaque `<td>`**, avec exactement le libellé de colonne de `MASTER.md` §10 | 2026-08-15 | C'est ce qui permet aux tableaux (résultats de travail, chiots d'une portée) de se déplier en lignes libellées sous 48 rem, **sans conteneur à défilement horizontal**. Sans l'attribut, les tableaux sont illisibles sur téléphone — donc échec de la contrainte 360 px. À porter dans le contrat gelé des issues #5, #15 et #3. |
| 9 | **Aucun index central à éditer à la main** dans `mtb-core` : chargeur à auto-découverte des sous-dossiers de `includes/{content,fields,blocks,query,migration,admin}/` | 2026-08-15 | C'est la condition technique du parallélisme. Un index de blocs édité à la main serait touché par presque toute issue visuelle et ferait entrer en collision des chaînes pourtant censées être disjointes. Conséquence : un bloc = un dossier auto-enregistré ; `functions.php` n'est touché que par #2 puis #18, jamais dans le même lot. |

## Questions ouvertes qui attendent l'utilisateur ou l'éleveuse

Reprises du §15 du brief. Aucune ne bloque le bootstrap ; chacune bloque une issue précise.

| # | Question | Bloque | État |
|---|----------|--------|------|
| Q23 | **Pourquoi cinq pages du site source sont-elles à la fois retirées des menus et marquées « ne pas indexer », alors que le plan du site les déclare ?** `chien-halan`, `chien-ray-ban`, `chien-roxane`, `chien-youry`, `placement`. Mesuré au lot 7 : **58 balises `robots` sur 54 fichiers** — les cinq portent le `noindex, nofollow` **en tête de `<head>`** et l'`index,follow` bien plus loin, donc la contradiction est **à l'intérieur d'un même `<head>`**. **Aucune hypothèse n'est avancée** : le site énonce le fait, jamais son motif, et le motif est un fait d'élevage. | **Tranchée au lot 8 pour l'import, ouverte pour le rendu.** L'utilisateur ayant demandé de « reprendre ce qu'il y a sur le site actuel », les cinq pages sont **importées en recopiant le statut de la source** — sans inventer le motif ni contredire l'intention. Les cinq portent la méta `_mtb_robots_source` (`valeur` / `source` / `extrait`, l'extrait montrant **les deux** balises dans l'ordre du document), homogène sur les cinq, vérifiée en base. **Le fait est stocké et documenté, il n'est pas honoré** : les cinq sont publiées, indexables et au plan du site. **#24** doit poser le filtre `wp_robots`, **#23** l'exclusion du plan du site. Le volet « hors menus » tient **par construction** — aucun menu n'existe. | ⏳ **le motif reste pour l'éleveuse** ; le rendu est dû par **#23 et #24** |
| Q20 | **Une image de plan d'accès : qui la fournit, sous quelle licence, avec quelle mention exacte en légende ?** Le composant #11 est livré et fonctionne sans elle (l'emplacement n'existe pas tant qu'aucune image n'est posée, §9.2). Format attendu : paysage, ~1200 × 800 px, **téléversé par la médiathèque** — jamais déposé dans le thème — et **après** le module d'images de #8, sinon pas de format moderne (dette T12). | rien — le composant est complet sans plan | ⏳ **pour l'éleveuse** |
| Q19 | **Accepte-t-elle qu'un plan pointant son domicile soit publié ?** L'adresse en texte est déjà publique sur l'ancien site ; un point sur une carte n'est pas la même exposition. | l'image de Q20 | ⏳ **pour l'éleveuse** |
| Q18 | **Lequel des deux points GPS est l'élevage ?** L'ancien site porte **deux iframes Google Maps encodant deux points distants d'environ 2 km** — `43.514689, 6.242809` (zoom 10, colonne principale) et `43.533404, 6.248086` (zoom 16, colonne latérale). Rien ne dit lequel est le bon, et c'est un fait géographique : il ne se devine pas. C'est la raison pour laquelle #11 n'a livré **aucune** carte plutôt qu'une carte plausible. | toute production d'image de plan | ⏳ **pour l'éleveuse** |
| Q17-bis | **« Description de la photo » convient-il à un document qui n'est pas une photographie** (plan d'accès, pedigree scanné) ? Arbitré au lot 4 en faveur de `MASTER.md` §10.2 (décision 39), mais l'objection de #11 reste entière sur le fond. | rien — le code est aligné | ⏳ **révision `lead-design-mtb`** |
| Q17 | ~~Contenu de démonstration : réel recopié, fictif assumé, ou rien ?~~ | — | ✅ **tranchée 2026-08-23 : fictif assumé, confirmé.** C'est ce que #29 a livré — `DEMO1 2025`, affixe « de Démonstration », `LOF DEMO 000001`. La revue a vérifié qu'**aucun nom du site source** n'y figure et qu'aucun numéro LOF plausible ne traîne. La chaîne #29 avait appliqué cette règle sur instruction, **pas sur décision datée**, et le signalait elle-même : c'est désormais réglé. Raison du choix : du réel recopié dans une base jetable finit un jour repris pour vrai. Voir décision 50 |
| Q16 | **L'encart « dernière portée » doit-il rester affiché quand la dernière portée est *Portée passée* ?** En l'état oui, donc entre deux portées l'accueil affichera « Portée A3 2025 — Portée passée ». L'autre lecture ne coûte qu'une garde. | rien — rouvrable sur #12 | ⏳ **pour l'éleveuse** |
| Q15 | **« Centre » doit-il signifier le centre géométrique, ou le cadrage par défaut de §6.2 (tête du chien) ?** Voir dette T16-bis. C'est le sens d'un libellé offert à l'éleveuse. | l'harmonisation de #7 et #14 | ⏳ **révision `lead-design-mtb`** |
| Q12 | **« Truffe » et « cavage » sont-ils la même chose pour Fabienne ?** Le mot *truffe* n'apparaît **nulle part** sur le site source ; la page Travail porte une ligne *Cavage* — et le cavage **est** la recherche de truffes. La chaîne #5 a refusé de graver la déduction, à raison. Le code n'attend pas la réponse : le site range lui-même Cavage sous « Autres disciplines », donc la reprise recopie son classement. | rien aujourd'hui — la reprise (#19-#21) et la qualité du tableau de la page Travail | ⏳ **pour l'éleveuse** |
| Q13 | **Fabienne consigne-t-elle la date de saillie ?** `MASTER.md` §10.2 fige le libellé « Saillie » (champ de date), mais BRIEF §5.1 ne l'inscrit pas dans les champs d'une portée. §10 dit **comment nommer**, pas **quoi livrer** : le champ n'a donc pas été livré. Le livrer aurait présumé qu'elle tient cette date. | rien — rouvrable sur #3 si la réponse est oui | ⏳ **pour l'éleveuse** |
| Q14 | **Les quatre lignes « Autres disciplines » du site source sont-elles des disciplines à part entière, ou la rubrique « Autres » du site actuel ?** (Agility · Brevet Maitre Chien Drogue · Cavage · Qualification chien de sauvetage) | rien — la reprise les exprimera de toute façon | ⏳ **pour l'éleveuse** |
| Q1 | Usage exact de la page protégée par mot de passe (familles de chiots / avant-première / documents d'élevage) | l'issue `prive` | ⏳ en attente |
| Q2 | ~~Point de départ du design~~ | — | ✅ tranchée 2026-08-15 : voir décision 8 |
| Q3 | ~~Conservation des messages du formulaire en base, ou envoi par courriel uniquement~~ | — | ✅ **tranchée 2026-08-21 : envoi par courriel uniquement**, rien n'est écrit en base. Voir décision 45 |
| Q4 | URL accentuées conservées (`/bhpl/portée-a3-2025/`) ou normalisées avec redirections 301 | l'issue `seo` | ⏳ en attente |
| Q5 | Hébergement de production et propriété du nom de domaine | la mise en ligne, #26 et #48 | ⏸️ **REPORTEE par l'utilisateur le 2026-08-30, decision assumee : « on verra quand tout est pret ».** Ce n'est plus une question en attente de reponse, c'est un report volontaire : rien ne se decide avant que le reste du site soit pret. **Consequence a connaitre : #26 et #48 sont gares tant que le report tient**, et ne doivent entrer dans aucun lot. **A verifier au moment de les reprendre** : #48 depend reellement de Q5 (rien dans le depot ne garantit qu'un diagnostic PHP reste invisible chez l'hebergeur, le correctif de #31 passant par un mecanisme Docker) ; en revanche il n'est **pas etabli** que #26 en depende vraiment — l'incompatibilite `scandir()` du chargeur pourrait se lever sans connaitre l'hebergeur. A trancher quand #26 revient, plutot que de la laisser bloquee par heritage |
| Q6 | ~~Rubrique « actualités » séparée ?~~ **Tranchée le 2026-08-19 : non**, pas d'entrée « Actualités » dans le menu livré par défaut, les nouvelles restent sur l'accueil — le menu étant modifiable par l'éleveuse, l'ajouter plus tard ne coûte aucune ligne. **Tarifs des chiots affichés ?** toujours ouverte | ~~#18~~, désormais **#17** (page Placement) | ⏳ **volet tarifs seul** |
| Q22 | **Que met-on dans le menu livré par défaut, et faut-il en livrer un ?** Aujourd'hui `provision.sh` ne crée **aucun** menu et le thème n'enregistre que deux *emplacements* — donc sur l'installation de l'éleveuse, l'écran dira « Créez votre premier menu ». La fiche a été réécrite pour décrire cet état et lui apprendre à créer le sien. Livrer un menu par défaut demanderait de savoir quelles entrées, et l'inventer serait un acte de contenu. | rien — la fiche couvre le cas | ⏳ **pour l'éleveuse** |
| Q21 | **`Privacy Policy` est une page en brouillon au titre anglais**, présente dans la base de développement. La publier ou la renommer sont des actes de contenu, pas des décisions techniques. Elle est aussi ce qui a révélé le découpage du menu (décision de lot 5). | rien aujourd'hui | ⏳ **pour l'éleveuse** |
| Q8 | ~~« ~7 disciplines » ou huit ?~~ | — | ✅ tranchée 2026-08-15 : voir décision 11 |
| Q9 | ~~Cotation LOF et dysplasie : texte ou liste ?~~ | — | ✅ tranchée 2026-08-15 : voir décision 12 |
| Q10 | ~~Le statut s'accorde-t-il au sexe ?~~ | — | ✅ tranchée 2026-08-15 : voir décision 13 |
| Q11 | Aucun fichier italique : le budget de 2 fichiers de police (§12) est consommé par les deux romains. L'italique reste synthétique et n'est jamais un dispositif de design. Rouvrable. | rien — arbitrage `design` | ⏳ pour information |
| Q7 | ~~Nom du dépôt GitHub à créer~~ | — | ✅ tranchée 2026-08-15 : `git@github.com:QuentinDoniczka/mtb.git`, fourni par l'utilisateur |

## Dettes ouvertes — créées par un lot, payées par un autre

Ne pas les redécouvrir dans trois lots. Chacune est déjà écrite dans le contrat de l'issue qui l'a créée.

| # | Dette | Créée par | Payée par |
|---|-------|-----------|-----------|
| **T102** | **Le jeu de démonstration ne rend que cinq des neuf porteurs du badge et de la photo.** Une **seule image** sur tout le site, et la portée la plus récente n'a ni photo ni disponibilité : les porteurs 1, 2, 3 et 6 du recensement `issue-33.md` §2 ne sont **affichés par aucune page**. La passe du lot 16 les a couverts par **sonde DOM injectée** au point d'ancrage exact, avec la liste de classes exacte du PHP — et l'a **dit comme tel** plutôt que de maquiller. Le banc a été validé (les 10 mesures d'émetteurs naturellement présents sont identiques au centième de pixel entre sonde et site vivant), donc la couverture est honnête ; elle n'est pas un rendu. **Ce n'est pas une dette de code mais de fixtures, et elle affaiblira toute passe visuelle à venir**, pas seulement celle-ci. La chaîne a **refusé de fabriquer de la donnée d'élevage** pour verdir le test : c'est le bon refus (D11), et il rend la dette inévitable tant que les fixtures ne portent pas de photos | lot 16, passe d'intégration et revue | `docker-mtb`, ou l'issue qui enrichira le jeu de démonstration |
| **T101** | **La prémisse de préfixage réfutée survit hors de l'empreinte de #33.** Les cinq occurrences **dans** l'empreinte portent leur note de solde datée (décision 66). Restent au moins `mtb-bandeau-ouverture.css:48`, qui porte **en plus** la citation `functions.php:56` — instance de **T88**, corrigée dans `base.css` au lot 16 et non ici. **Le risque est nommé** : une garde dont la justification est démentie se fait supprimer par la chaîne suivante, qui croira retirer du mort. Toute issue qui rouvre une feuille de blocs solde ses occurrences **au passage** | lot 16, #33 | au fil de l'eau, par l'issue qui rouvre la feuille |
| **T100** | **Le renvoi `base.css:NNN` dérive dans tout le thème — même classe que T88, autre cible.** Relevé par #33 au-delà des trois cas qu'elle a corrigés : `mtb-bandeau-alerte.css:26` et `:52`, `editor.css:163` (tous trois vers `base.css:241`), plus `base.css:465`, `:477`, `:515`, `:522`, `:369`, `:324` cités depuis quatre feuilles et **tombant en plein commentaire ou sur un en-tête de section**. **Les décalages sont incohérents entre eux — +3, +6, +17, +18** : c'est de l'accumulation commit après commit, pas un décalage unique, donc **aucune reprise mécanique n'est possible**, contrairement à T88 qui porte sa table de correspondance. La solder dans #33 aurait été un ré-audit de cinq feuilles, hors empreinte | lot 16, #33 | une passe d'alignement, avec T88 |
| **T99** | **Le compte des feuilles de blocs du thème passe de dix à onze, et quatre phrases le disent encore à dix.** `assets/css/base.css:792` (« bat les (0,1,0) des **dix** feuilles de composants ») · `assets/css/blocs/mtb-bandeau-ouverture.css:149` (« neuf feuilles sœurs »), `:159`, `:186`. **Les quatre étaient exactement vraies avant `a171034`** et sont fausses après : c'est une péremption créée par le lot 15, et ce n'est ni T79 (compte du **catalogue**) ni T88 (dérive de **numéros**). **Confiée à #33**, dont le glob `assets/css/blocs/*.css` et `base.css` couvrent les deux fichiers : les rouvrir entraîne leurs artefacts par la règle `make css`, donc les faire reprendre ailleurs coûterait deux fois | lot 15, revue (LOW) | ✅ **PAYÉE au lot 16 par #33**, et **l'énoncé ci-contre se trompait de fichier deux fois** : les ancres réelles étaient `mtb-bandeau-ouverture.css:161, 171, 190, 191, 198`, non `:149, 159, 186`. La revue a relu **les 25 occurrences de « neuf / dix / onze »** du dossier CSS, une par une. **Fait à retenir avant de toucher à ces comptes** : deux comptes cohabitent légitimement dans le même fichier — `mtb-bandeau-ouverture.css:34` dit « neuf sœurs » en comptant des **composants** (9 sœurs de 10), les l. 161/171/198 disent « dix sœurs » en comptant des **feuilles** (10 sœurs de 11). Les deux sont vraies simultanément ; `:12-19` explique désormais la cohabitation et **interdit nommément le `sed`**. Même cas pour `editor.css:92` (composants), hors empreinte et à ne pas toucher |
| **T98** | **La feuille neuve du thème est en LF quand ses dix sœurs sont en CRLF sur disque.** `assets/css/blocs/mtb-galerie-photos.css` a été créée en LF par la chaîne #34 ; `base.css`, `mtb-liste-portees.css`, `mtb-grille-chiens.css` et les autres sont en CRLF. **Sans conséquence fonctionnelle** — git normalise au `checkout` (`core.autocrlf=true`), et le minifieur de #40 travaille sur une **forme canonique** précisément pour que la question ne se pose pas — mais l'écart est réel dans cet arbre, et il vaut d'être connu de qui comparera des octets bruts entre feuilles sœurs. Même famille que T49 | lot 15, relevée par moi | au fil de l'eau, ou l'issue qui rouvrira la feuille |
| **T97** | **Le témoin md5 `70f2488b…` cité par la chaîne #34 n'est reproductible sous aucune normalisation connue.** La passe d'intégration a établi l'identité de la présentation par **six formes canoniques indépendantes** — flux ordonné, déclarations triées, décommenté brut, décommenté sans blancs, lignes non vides, et comparaison du CSS réellement servi sur 30 pages — **et aucune ne rend cette valeur**. L'identité n'est pas en cause, elle est démontrée. **C'est la valeur qui n'est pas un témoin citable** : personne ne peut la recalculer sans connaître la normalisation employée, et un chiffre invérifiable dans un rapport se recopie comme s'il était vérifié. Règle à retenir : **un témoin numérique se publie avec sa recette, sinon il ne vaut rien** | lot 15, passe d'intégration | rien à réparer ; à opposer à tout rapport futur |
| **T96** | **La mention « dette T13 » a disparu du module galerie, et T13 n'est pas close.** Le retrait, fait au lot 15, est **sourcé et juste sur la phrase qu'il annotait** : `issue-8.md:1233` déclare T13 payée pour le **domicile de l'apparence d'état vide**, et `editor.css` porte bien `.mtb-etat-vide` (`:126`), `__nom` (`:142`) et `__phrase` (`:160`). Mais T13 reste ouverte sur un autre point — le bouton « Ajouter des photos » que `editeur.js:254` met dans le cadre vide de **ce bloc précisément**. Après le lot 15, le module galerie ne porte **plus aucune trace de T13** ; elle survit ailleurs (`mtb-coordonnees-plan.css:8`, `fiches.css:67`). **À ne pas lire comme « T13 close »** | lot 15, revue (LOW) | l'issue qui paiera T13 |
| **T95** | **La règle de masquage de `__rang` existe en deux copies, et rien ne surveille leur écart.** C'est **T15-bis**, l'héritière de T15 : `blocks/galerie-photos/editeur.css` (servie à l'éditeur seul, 4 déclarations) et `themes/mtb/assets/css/blocs/mtb-galerie-photos.css` (servie au visiteur). Duplication **volontaire** — la règle est porteuse d'accessibilité et son mode de panne dans l'aperçu, « Photo 3 sur 12 » sous chaque vignette, ferait croire à l'éleveuse que le bloc est cassé. Vérifiées **identiques à l'octet** au lot 15 (`editeur.css:73-78` ≡ `mtb-galerie-photos.css:255-262`). **Le motif mécanique de la dette** : `make css-check` ne balaie que les feuilles du **thème** et **ne voit jamais celle de l'extension**, donc aucun contrôle du dépôt ne dirait leur divergence. L'énoncé n'est pas « il reste du CSS dans l'extension » mais **« il en reste deux copies et rien ne surveille leur écart »**. Trancher si une règle servie à l'éditeur seul relève de la frontière du contrat #1 §8 est un arbitrage de `MASTER.md`, pas un effet de bord d'un rangement | lot 15, #34 — successeur de T15 | `lead-design-mtb`, ou une issue `blocs` |
| **T94** | **`docker compose run wpcli <cmd>` ne fait pas ce qu'il paraît faire.** L'`entrypoint` de `wpcli` est figé sur `provision.sh` (`compose.yaml`), donc la commande passée est **ignorée** : le conteneur rejoue le provisionnement complet puis reste vivant sur le `tail -f /dev/null` terminal du script. Constaté au lot 14 : **trois conteneurs `mtb-wpcli-run-*` orphelins**, vivants depuis 4 à 5 h, tournant en parallèle sur les mêmes volumes que la pile — cause très probable de la contention disque et du flapping `unhealthy` de `wordpress` observés pendant la vérification finale. Retirés par moi (`docker rm -f`, volumes intacts, vérifiés après coup). **La cause est intacte** : toute chaîne future refera le geste, et rien ne l'avertit | lot 14, `docker-mtb` | une issue `infra` |
| **T93** | **La prémisse de T48 est périmée.** `compose.yaml:140-151` affirme que le dépôt est « sans `.gitattributes` » — **faux depuis #47**. Relevée par #40 sans être corrigée, hors empreinte | lot 14, #40 | l'issue qui rouvrira `compose.yaml` |
| **T92** | **Le minifieur ne scanne que deux dossiers, et « 14 » est figé en dur à trois endroits.** `docker/outils/mtb-minifier-css.php:487` ne parcourt que `assets/css/*.css` et `assets/css/blocs/*.css` : une feuille future déposée dans un troisième dossier serait **ignorée en silence**, et `make css-check` rendrait quand même vert. Le compte `14` est écrit en dur à `Makefile:116` et `docs/docker.md:70-71, 304-305`. Même famille que le défaut que #40 vient de réparer : **une garde qui rassure sans couvrir** | lot 14, revue (LOW-2) | une issue `perf` ou `infra` |
| **T91** | **Quatre contrats gelés affirment que la version d'une feuille vaut `filemtime()`, et #40 ne le déclare nulle part.** `issue-2.md:87` (« Version de chaque feuille = `filemtime()` »), `issue-7.md:393`, `issue-12.md:68`, `issue-13.md:108` ; s'y ajoute `issue-2.md:85`, dont la poignée `mtb-bloc-<espace>-<nom>` peut désormais servir un `.min.css`. **Ce n'est pas T88** : T88 couvre la dérive de numéros de ligne et est déclarée en toutes lettres à `issue-40.md:751-800`, alors qu'ici c'est une **affirmation positive** qui meurt sans un mot au contrat. Les interdits de ces contrats survivent (ni numéro à la main, ni version du thème) ; seule l'affirmation positive est morte. **Ne pas éditer les contrats gelés** — précédent T59/T60 | lot 14, revue (MEDIUM-4) | une passe d'alignement des contrats, ou l'issue qui rouvrira les enqueues |
| **T90** | **`make css` monte le dépôt entier en écriture, ce que `compose.yaml:124` déclare interdit.** `Makefile:112` et `:118` passent le dépôt en `:rw`, ce qui inclut `docs/migration/source/`, monté ailleurs en `:ro` avec le motif « lecture seule absolue — c'est une pièce à conviction, jamais une donnée de travail ». **Aucun risque réel aujourd'hui** : le seul `file_put_contents` de l'outil (`mtb-minifier-css.php:920`) n'écrit que `<source>.min.css` sous la racine passée, et il n'y a ni `unlink`, ni `rename`, ni `mkdir`. Mais la garantie cesse de valoir pour une commande que **toute chaîne future touchant du CSS devra jouer**, et l'exception n'est écrite nulle part à côté du montage. Remède étroit : monter `assets/css` seul | lot 14, revue (MEDIUM-3) | une issue `infra` |
| ~~**T89**~~ | **L'obligation de `make css` n'est écrite dans aucun document qu'une chaîne lit avant d'éditer une feuille.** Elle vit dans `Makefile:100-104`, `docs/docker.md:307-314`, l'en-tête de `functions.php:19-21` et `issue-40.md` — et **nulle part** dans `CLAUDE.md`, `docs/ETAT.md`, `MASTER.md`, ni l'en-tête des 15 sources CSS. Or un `dev-ux-mtb` qui ouvre `assets/css/blocs/mtb-encart-appel.css` lit le brief, `CLAUDE.md`, `MASTER.md`, son contrat et le fichier — **jamais `docs/docker.md`**, qui appartient à `docker-mtb`. Sans `make css`, la feuille repart non minifiée : page correcte, plus lourde, **rien dans `debug.log`, rien à l'écran**. C'est le coût permanent introduit par #40, et il n'a pas de domicile lisible. **Question posée à l'utilisateur au lot 14** : l'inscrire au § Conventions de `CLAUDE.md` | lot 14, revue (MEDIUM-1) | ✅ **PAYÉE le 2026-09-01** — l'utilisateur a tranché : la règle est inscrite au § Conventions de `CLAUDE.md`, avec le fait qu'un oubli ne se signale nulle part et que `make css-check` est le seul témoin, plus sa conséquence sur les empreintes de lot |
| **T88** | **48 citations `functions.php:NNN` dans le dépôt, aucune exacte** (R10 du contrat #40, déclarée à `issue-40.md:751-800`). #40 a décalé de +11/+18 lignes après remède ; 8 sont dans des sources CSS que son propre gel interdisait d'ouvrir, 2 dans `docs/ETAT.md`, 15 dans des contrats gelés, 1 dans `blocks/lien-de-recours/rendu.php:227`. **Mais 7 étaient déjà fausses à `e8a35f4`** : la pratique de citer ce fichier par numéro se dégradait déjà seule — c'est un problème de **méthode**, pas un dégât de #40. Table complète citant → cible → nouveau numéro au contrat ; la reprise est mécanique | lot 14, #40 | une passe d'alignement, ou au fil de l'eau |
| **T87** | **Le jumeau à tenir en accord entre `functions.php` et `docker/outils/mtb-minifier-css.php`.** La révision du marqueur, la forme canonique et le calcul d'empreinte y sont écrits **deux fois**. Une divergence serait **silencieuse** : les artefacts cesseraient d'être servis, sur toutes les pages, sans erreur ni notice ; seul le poids augmenterait. **Coût retenu délibérément contre la factorisation** — le thème ne peut pas dépendre d'un outil de `docker/`, qui n'est ni déployé chez l'hébergeur ni chargeable par WordPress ; toute divergence dégrade vers « la source est servie », jamais vers « un artefact accepté à tort ». Garde-fous : chacun nomme son jumeau, `make css-check` la rendrait visible en 14 paires `PÉRIMÉ`, et deux gardes machine ont été ajoutées au lot 14 — P6 refuse si la borne de lecture n'excède pas le maximum du marqueur, et le générateur refuse d'écrire un artefact dont le marqueur ne vérifie pas son propre motif. **Ce qui manque** : rien ne *force* la relecture conjointe, et le `128` de `functions.php` est un **littéral** que P6 ne compare pas | lot 14, #40 | une issue `perf` si la garde humaine casse |
| **T86** | **Sept lignes de contrats gelés sont périmées par le commit de #43, et l'une d'elles est un piège.** `issue-16.md:378, 530, 564, 877-878` · `issue-17.md:380-382` · `issue-18.md:584, 758` enregistrent « Les portées » comme libellé du lien. **Le point qui compte est `issue-16.md:378`** : il justifie le `h1` de `archive-mtb_portee.html` par « aligné sur le libellé de recours de §9.5 ». **Le `h1` reste juste, sa raison écrite ne tient plus** — une chaîne future pourrait en conclure qu'il faut l'aligner, ce que la note du §10.3 interdit désormais explicitement. **Non modifiés à dessein**, précédent T59/T60 ; supersession déclarée dans `issue-43.md` | lot 14, #43 | une passe d'alignement des contrats |
| **T85** | **`MTB_CORE_VERSION` est figé à `0.1.0`** : un `editeur.js` modifié garde son URL. Sans effet sur le livrable du lot 14, mais vaut pour les **douze** `editeur.js` de l'extension. **Deux corrections apportées au lot 15 par la chaîne #34, sur mesure.** (1) **Le compte était faux le jour où j'ai écrit cette dette** : `git ls-tree` à `5103bd6` — le commit qui l'inscrit — rend déjà **douze** `editeur.js`, non onze. L'oublié est `lien-de-recours` (#39, lot 13), tombé derrière la formule « les **dix** composants du catalogue » qui revient partout dans le dépôt : ce bloc est `inserter: false`, donc **hors catalogue**, mais il a bien un `editeur.js`. **Deux ensembles distincts que j'ai confondus** — même contamination que T79, qui compte dix là où il y en a onze. (2) **#34 paie une part de la cause, pas du périmètre** : elle retire du régime de la constante gelée la **seule feuille que l'extension servait au visiteur**, qui passe au versionnement par empreinte ; mais il reste **1** `wp_register_style` (d'éditeur) et **15** `wp_register_script` sur `MTB_CORE_VERSION`, et **#34 ne touche aucun script**. Le périmètre que T85 se donne — les `editeur.js` — est intact | lot 14, #43 ; corrigée au lot 15 par #34 | une issue `infra` |
| **T84** | **Le module `lien-de-recours` n'énumère que 2 des 3 écrans qu'il sert**, à deux endroits (`block.json` et `bootstrap.php`). `MASTER.md` §9.5 nomme le troisième, « Liste sans résultat » | lot 14, #43 | une issue `blocs` |
| **T83** | **Les cinq chiens sans photo enseignent un préalable qui n'existe pas.** `docs/guide/contenu-repris-de-l-ancien-site.md:161-164` écrit qu'il faudra ajouter une photo « **dans Galerie photos**, avant de pouvoir en choisir une comme portrait ». **Faux deux fois** : la fenêtre du portrait offre elle-même l'ajout depuis l'ordinateur, et surtout **cela fait de la galerie un préalable au portrait** alors que les deux champs sont indépendants — la confusion exacte que #36 existe pour dissiper. **Antérieure à ce lot**, autre type de contenu ; trouvée parce que la première rédaction du §4 par #36 avait **recopié cette tournure**, corrigée chez elle et laissée là sur ma consigne | lot 14, #36 | une issue `doc` |
| **T82** | **Triple lecture de `_mtb_galerie` avec le même filtre, dans trois modules** : `fields/portee/ecran.php` (`photos_affichables()`), `query/portee/hydratation.php:489-506`, `fields/portee/sauvegarde.php:250-260`. Relevée par `refacto-mtb`, **non corrigée à raison** : extraire une lecture commune depuis `fields` franchirait la frontière `fields`/`query` et contredirait l'arbitrage 6 du contrat #36. **Décision de plan, pas de refacto** | lot 14, #36 | une issue dédiée, côté `query` |
| **T81** | **La reprise n'écrit aucune photo principale de portée.** `migration/portees-chiens/portees.php:70-90` n'a **aucun** chemin d'écriture de `_thumbnail_id` là où `chiens.php:163-177` en a un. **0 portée sur 31** en possède une, **18 sur 31** portent donc la mention de #36. Ce que l'éleveuse voit : encart d'accueil et liste des portées **sans image** pour toutes les portées reprises, jusqu'à ce qu'elle en désigne une à la main, 27 fois. **Ce n'est PAS une perte au sens de la contrainte 4** : `docs/guide/contenu-repris-de-l-ancien-site.md:177`, fiche livrée et revue à un lot antérieur, établit que **l'ancien site ne désignait aucune photo comme *la* photo d'une portée** — la migration n'avait rien à reprendre. C'est une **notion inexistante, pas un contenu perdu**. Reste que le travail manuel, lui, est réel | lot 14, #36 | une issue `portees` ou `contenu`, si l'éleveuse veut être aidée |
| **T80** | **Poser une photo principale est différé à l'enregistrement, la retirer est immédiat — et rien ne le montre.** Mesuré dans le cœur 6.9, en conteneur : « Choisir la photo principale » → « Utiliser comme photo principale » appelle `get-post-thumbnail-html` (`media-editor.js:619-635`), dont le gestionnaire (`ajax-actions.php:2774`) **rend du balisage et rien d'autre** ; la persistance passe par le champ caché `_thumbnail_id` (`post.php:1694`), écrit par `wp_insert_post()` (`:5043-5055`) **avant** `save_post` (`:5194`). L'action écrivante `set-post-thumbnail` (`:2761`) n'a pour appelants du cœur que `WPRemoveThumbnail` (`post.js:137-157`) et le lien du volet de détail. **Pourquoi c'est une dette** : la colonne de droite affiche la vignette **identiquement dans les deux cas** ; toute chaîne qui supposera la symétrie — un contrôle à l'enregistrement, un état dérivé de `_thumbnail_id`, une recette — se trompera **sans rien voir**. #36 s'en sort par le texte, pas par le mécanisme. Numéros de ligne = **repères de lecture**, `docker/wordpress/Dockerfile:5` tirant une étiquette flottante | lot 14, #36 | toute issue touchant l'écran de saisie d'une portée |
| **T78** | **Le libellé de `MASTER.md` §10.2 n'a été rendu qu'à une seule largeur et à un seul zoom.** Successeur de T65, et l'énoncé change de nature : ce n'est **pas** « le libellé casse » — il ne casse pas, c'est mesuré — mais « on ne l'a vu qu'une fois ». À 1280 px et au zoom natif, « Description de la photo (pour les personnes aveugles) » (**53** signes) se rend sur **quatre** lignes sans aucune coupure intra-mot, le mot le plus large (« Description », **60 px**) tenant dans une colonne bornée à **80 px**. **La marge n'est donc que de 20 px.** À 200 % de zoom texte, ou dans un volet d'administration plus étroit, elle n'est pas garantie — et c'est là que la coupure en travers des mots que T65 annonçait pourrait réapparaître. Reste par ailleurs un déséquilibre constaté et assumé : la boîte du libellé fait **80 × 72 px** face à une zone de saisie de **174 × 50 px**, donc le libellé est **plus haut que le champ qu'il nomme** et déborde de **21 px** — « aveugles) » flotte seule sous la zone de texte, au niveau du lien « Apprendre à décrire le but de l'image ». Ce n'est **pas** un motif suffisant pour rouvrir §10.2 : changer un libellé déjà imprimé dans six fiches du manuel pour 21 px serait disproportionné | lot 13, T65 levée et requalifiée par la revue | une passe de rendu à 360 px et au zoom 200 % |
| **T77** | **Les trois écrans de choix de photos ne sont pas réglés pareil, et deux d'entre eux portent le même titre.** `blocks/galerie-photos/editeur.js:190` ouvre la fenêtre en `multiple: true` — un clic au milieu d'une vignette **efface la sélection en cours** et la remplace par cette seule photo — quand `fields/chien/galerie.js:167` et `fields/portee/ecran.js:264` l'ouvrent en `multiple: 'add'`, où le même geste **ajoute**. Et les deux fenêtres s'intitulent identiquement « **Photos de la galerie** » (`editeur.js:191` contre `fields/chien/galerie.js:164`) : **rien à l'écran ne prévient l'éleveuse qu'elle vient de changer de règle**. C'est du travail perdu sur un geste qu'elle refera souvent. **La fiche la protège depuis le lot 13** (`composant-galerie-photos.md:56-58` lui donne la case, `:211-213` lui dit quoi faire quand c'est arrivé) ; **la cause, elle, est intacte**. Remède probable — aligner sur `'add'` — mais **à mesurer avant d'appliquer** : ici la propriété est reçue par le composant `MediaUpload` de `@wordpress/media-utils`, pas par un `wp.media()` direct comme dans les deux autres écrans, donc la valeur qui atteint réellement la fenêtre doit être **constatée**. Y joindre `editeur.js:127-130`, commentaire **localement vrai et globalement trompeur** : il décrit exactement le gestionnaire `ajouterDesPhotos` (`:132-149`, qui empile bien et saute les doublons) mais laisse conclure que la sélection multiple fonctionne, alors que la fenêtre remplace **en amont**. C'est le raisonnement que #46 a dû défaire pour trouver le défaut ; la prochaine chaîne le refera | #46, lot 13 (MEDIUM-2 et LOW-6 de la revue) | une issue `blocs` |
| **T76** | **Dix-neuf occurrences du guide décrivent « gris » ce qui ne l'est pas, et l'inventaire n'est pas clos.** **Dix-huit** décrivent le cadre d'état vide, qui est **beige à contour en tirets** — un seul CSS le produit, `themes/mtb/assets/css/editor.css:126-140` (`border: 1px dashed var(--laiton)` sur `background-color: var(--calcaire-creux)`, `#E7E5DA`), partagé par les **onze** composants via `.mtb-etat-vide` : `composant-formulaire-contact.md:193, 233, 236, 242, 244, 246, 326` · `composant-bandeau-alerte.md:116, 141, 155, 157, 178` · `composant-tableau-resultats.md:161, 170, 182` · `composant-fiche-information.md:245` · `coordonnees-modifier-les-coordonnees.md:137` · `composant-grille-chiens.md:41`. **Deux corrections d'inventaire dues par la revue** : (a) `composant-grille-chiens.md:41` **ne vient pas d'`editor.css`** mais de `themes/mtb/assets/css/blocs/mtb-grille-chiens.css:235` (`--calcaire-creux`, au titre de `MASTER.md` §9.2) — c'est un état **public**, pas un état d'éditeur, et une chaîne qui grepperait `editor.css` chercherait au mauvais endroit ; (b) une **dix-neuvième** occurrence n'est dans aucune liste, `composant-formulaire-contact.md:156` (« la phrase grise au-dessus du bouton »), qui décrit la mention d'information **côté visiteur**, rendue en `--texte-doux` `#4A5A50` (`tokens.css:39`), un **vert désaturé** — troisième catégorie à créer. **DANGER, à lire avant toute correction** : quatorze autres « gris » du guide sont **vrais** et rendus par le cœur de WordPress — entrées grisées de la liste du bouton **+** (`bandeau-alerte.md:98, 164` · `bandeau-ouverture.md:131, 209` · `encart-derniere-portee.md:135, 199`), invites de saisie (`bandeau-alerte.md:53, 159` · `fiche-information.md:34` · `page-composer-une-page-libre.md:37` · `portee-ajouter-une-portee.md:24`), boutons grisés (`galerie-photos.md:91, 94, 218` · `encart-appel.md:166`). **Les « corriger » en beige écrirait quatorze faussetés neuves pour en réparer dix-huit — un dégât net.** Deux phrases enfin **nient** l'existence du cadre sans en affirmer la couleur et restent justes telles quelles : `coordonnees-plan.md:129` (section gelée) et `contenu-repris-de-l-ancien-site.md:173` | #46, lot 13 (relevée par la chaîne, corrigée deux fois par la revue) | une issue `doc`, le volume justifiant une passe à part |
| **T75** | **`docs/contracts/issue-16.md:567` décrit le rendu de `mtb/lien-de-recours` comme s'il n'en existait qu'une forme.** La ligne écrit « Rendu : `<li class="mtb-lien-de-recours"><a href="…">Libellé</a></li>` — **une seule classe, un `<a>` nu** », sans réserve de placement. Depuis `6f88b98` (#39, T51), c'est le rendu de la branche « parent `core/list` » **seulement** : hors d'un conteneur de liste, le composant émet la même sous-chaîne `<li>…</li>` **enveloppée d'un `<ul>` nu**. La phrase reste **vraie sur les trois gabarits livrés** et sur tout chemin d'édition offert — le bloc est `inserter: false` — mais elle est désormais **incomplète** : un lecteur qui s'y fie croit que le composant n'a qu'une sortie. Contrat **gelé**, hors empreinte, délibérément non modifié. **Quatrième dette de contrat gelé** : à payer d'un seul geste avec T59, T60 et T63 plutôt qu'à laisser flotter | #39, lot 13 | l'issue de correction des contrats gelés (avec T59, T60, T63) |
| **T74** | **`MASTER.md` ne documente aucune des trois formes plurielles que le code rend et que le manuel imprime.** `content/chien/choix.php` porte la clé `pluriel` de `statuts()` — `:109` `Reproducteurs`, `:114` `En cours de confirmation` (invariant), `:119` `Retraités`, `:124` `Disparus` —, exposée par `libelle_statut_pluriel()` (`:169-176`, docbloc `:163` : « Titre de groupe au pluriel, pour la page “La meute” »). **Trois** fiches du guide les impriment comme titres de groupes : `composant-grille-chiens.md:36, 52-53, 72, 74, 75, 115` · `page-creer-la-page-la-meute.md:50-51` · `listes-retrouver-un-contenu.md:39, 79, 233`. Et **zéro occurrence dans `MASTER.md`**, vérifié par `grep -c` après les commits du lot 13. Le document qui se **déclare l'arbitre du vocabulaire** ne fixe donc aucun des trois libellés. Même famille que T54, T55 et T56. Cas aggravant déjà imprimé pour l'éleveuse : `listes-retrouver-un-contenu.md:233` l'avertit que « la colonne **Statut** dit *Retraitée* alors que le filtre dit *Retraités* » — **deux formes coexistent à l'écran et §10.2 n'en documente aucune**. Non ajoutées par #44 à raison : **aucune revue ne les a arbitrées**, et les graver de sa propre initiative aurait été l'invention silencieuse que le projet interdit. (Le « cinq fiches » du brainstorm de #44 était faux : `chien-ajouter-un-chien.md:71` et `contenu-repris-de-l-ancien-site.md:55` n'impriment que le singulier et relèvent de T56, payée) | #44, lot 13 | `lead-design-mtb`, ou l'issue qui rouvrira §10.2 |
| **T79** | **Le décompte des composants du catalogue est périmé d'une unité, à deux endroits.** `design-system/MASTER.md:913` (§9.1) écrit « Cette apparence est identique pour les **dix** composants du catalogue » et `blocks/lien-de-recours/bootstrap.php:24` « les **dix** autres modules de ce dossier sont des composants offerts à l'éleveuse ». Ils sont **onze** — comptés par la revue du lot 13 sur les modules qui émettent `.mtb-etat-vide` : bandeau-alerte, bandeau-ouverture, coordonnees-plan, derniere-portee, encart-appel, fiche-information, formulaire-contact, galerie-photos, grille-chiens, liste-portees, tableau-resultats. `includes/blocks/` contient douze modules de bloc (onze offerts + `lien-de-recours`), plus `categorie-mtb` qui n'est pas un bloc. Sans conséquence aujourd'hui, mais **§9.1 est normatif et se déclare exhaustif** (« identique pour les dix »), donc il affirme faux, et un mainteneur qui vérifie l'exhaustivité par ce chiffre en manquera un. **Correctement non corrigé au lot 13** : l'empreinte de #44 était §10.2, et l'élargissement autorisé s'arrêtait à l'en-tête et au journal | relevée au lot 13 par la revue | **RE-ROUTÉE au lot 14** : le rendez-vous désigné a eu lieu — la révision 1.4 de la chaîne #43 — et a **décliné à raison**, §9.1 étant hors de son empreinte. Une dette qui pointe un événement déjà consommé dormirait indéfiniment. Réattribuée à **une issue `design` dédiée**, à ouvrir avec T62 |
| ~~**T65**~~ | ~~**Le libellé figé par `MASTER.md` §10.2 fait 52 caractères, et il n'a jamais été rendu à aucune largeur.**~~ **LEVÉE SUR PREUVE au lot 13 par #46, et la prédiction était fausse sur toute la ligne.** Mesuré à l'image, puis contre-mesuré par la revue qui a ouvert les fichiers livrés : **53** caractères et non 52 (la parenthèse fermante), **quatre** lignes et non « environ cinq » (`Description de` / `la photo (pour` / `les personnes` / `aveugles)`), et **aucune coupure intra-mot** — le mot le plus large, « Description », mesure **60 px** pour une colonne bornée à **80 px**, donc `word-wrap: break-word` n'a **jamais** à se déclencher. Les valeurs de `media-views.css:409-411` sont confirmées à l'écran ; **le libellé est lisible, entier, non tronqué**, et `MASTER.md` §10.2 **n'a pas à être rouverte**. La chaîne déclare n'avoir essayé **ni autre largeur ni image alternative** pour obtenir un meilleur rendu, et les images le confirment : elles montrent le débordement plutôt que de le cadrer hors champ. **Ce qui subsiste est réel mais autre** — la boîte du libellé fait 80 × 72 px face à une zone de saisie de 174 × 50 px, donc le libellé est **plus haut que le champ qu'il nomme** et déborde de **21 px** — et se juge désormais à l'image, qui est au dossier. Successeur : **T78**, dont l'énoncé n'est pas « le libellé casse » mais « il n'a été rendu qu'à une largeur et à un zoom » | lot 12, relevée par la revue | ✅ **levée au lot 13 par #46**, requalifiée en T78 |
| **T66** | **T-#35-b sous-estime sa propre portée.** La dette ne nomme que le menu « Médias ». Mesuré en session admin réelle sur `post.php?post=14&action=edit` : « Médiathèque » ×1, « Médias » ×3, « fichier joint » ×12, « URL du fichier » ×5. `MASTER.md` §10.4 interdit la racine « média » ; **« Médiathèque » (msgid `Media Library`, distinct de `Media`) tombe sous la même règle**, et le guide demande à l'éleveuse de cliquer dessus **par son nom** (`composant-galerie-photos.md:110`, `portee-ajouter-une-portee.md:190`). Une chaîne future payant T-#35-b **telle qu'écrite** laisserait « Médiathèque » debout. Corriger l'énoncé de la dette avant de la payer | #35, relevée au lot 12 | la chaîne qui paiera T-#35-b |
| **T67** | **Le docbloc de `admin/description-photo/bootstrap.php:158-160` affirme une sûreté que le cœur ne fournit pas.** Il dit « Ce module N'IMPRIME RIEN : il rend une chaîne que le cœur échappe et imprime lui-même. » **Le cœur n'échappe pas ce libellé** : `_e( 'Alternative Text' )` imprime brut à `media-template.php:516, 768, 1074, 1137` et `media.php:3233`, et `{$field['label']}` est interpolé brut à `media.php:1804` et `:1996`. La sûreté tient en réalité à **l'interdit n°3 du même docbloc** (aucun `%`, `<`, `>`, `&` dans la chaîne de remplacement) — et le contrat §A5 le dit correctement (« ce qui la rend juste que le cœur l'échappe ou non »). Sans conséquence aujourd'hui (libellé sûr, table gelée et non filtrable), mais **un mainteneur lisant le module et non le contrat croirait pouvoir y mettre n'importe quel caractère** | #35, relevée au lot 12 | une correction de phrase, renvoyant à l'interdit n°3 |
| **T68** | **`docker/wpcli/Dockerfile:68` motive un choix par deux affirmations toutes deux fausses.** Il écrit « `core.autocrlf=true` **sans** `.gitattributes` racine — un `.gitattributes` racine renormaliserait aussi `docs/migration/source/html/*.html` ». Or **un `.gitattributes` racine existe et est suivi** (posé par `73f1ea4`, #47) et **il ne renormalise que `.claude/agents/*.md`** — un motif de chemin ne déborde pas. `docs/docker.md:109` dit la même chose correctement (« sans `.gitattributes` **couvrant ce dossier** »). Le correctif livré fonctionne (enrobages en LF, mesuré) ; **c'est le motif écrit qui est faux, et il décourage une option que le dépôt pratique déjà** | #30, relevée au lot 12 | une correction de commentaire |
| **T69** | **Trois endroits hors empreinte décident encore « date absente » par la seule chaîne vide.** #45 a fait cesser cette seconde notion **dans son seul fichier** ; les trois autres sont enregistrés au §7 de `docs/contracts/issue-45.md` et non traités. Tant qu'ils vivent, la notion de « date absente » reste double dans l'extension | #45, lot 12 | une passe d'alignement sur `date_en_toutes_lettres()` |
| **T70** | **La conformité WPCS n'est vérifiable par aucun outil du dépôt.** Ni `composer.json`, ni `phpcs.xml`, ni binaire `phpcs` dans le dépôt ou les conteneurs — #45 le signale dans son propre commit. `CLAUDE.md` impose pourtant les WordPress Coding Standards. **Toutes les revues de conformité du projet ont donc été tenues à la relecture, jamais par un outil** — c'est à savoir avant de s'appuyer sur l'une d'elles | relevée au lot 12 | une issue `infra` |
| **T71** | **La page protégée par mot de passe figure dans le sitemap du cœur.** `GET /wp-sitemap-posts-page-1.xml` rend `<loc>…/espace-prive/</loc>`. C'est le comportement par défaut de WordPress (il n'exclut pas les contenus protégés) et **rien dans `mtb-core` ni dans le thème ne pose ce filtre** — aucun crochet `wp_sitemaps` ni `has_password` dans les deux. Le reste de la protection est conforme : aucune fuite du corps, ni en recherche, ni au flux. À arbitrer avec **#23** (page protégée) et **#24** (référencement) | relevée au lot 12, hors lot | #23 ou #24 |
| **T72** | **`docker/wpcli/Dockerfile:74-79` code en dur la liste des six enrobages**, indépendamment du contenu réel de `docker/wpcli/bin/`. Un septième fichier déposé dans le dossier serait copié dans `/tmp` puis **jeté sans un mot**. `for nom in $(ls /tmp/db-client-wrappers)` supprimerait la double source de vérité | #30, lot 12 | une retouche du `Dockerfile` |
| **T73** | **Un permalien rend une adresse qui répond 404.** `/?post_type=mtb_resultat&p=18` → **404**, alors que `get_permalink()` rend cette adresse : les résultats de travail n'ont pas de page publique individuelle (ils s'affichent par le composant « Tableau de résultats »). Aucune archive publique non plus pour `mtb_chien` ni `mtb_resultat`. Comportement **stable et non touché par le lot 12**, consigné parce que **c'est un piège pour toute passe SEO future** | relevée au lot 12, hors lot | l'issue `seo` #24 |
| **T64** | **Les sous-agents d'une chaîne ne rendent pas toujours leur rapport, et le lot 11 en donne les deux issues possibles.** Chaîne #31 : `brainstorm31` et `dev31` n'ont jamais rendu — le lead a **refait la démonstration en entier** plutôt que de reprendre une preuve qu'il n'avait pas lue, et l'issue a abouti. Chaîne #34 : **quatre agents lancés, zéro retour en ~90 min**, dont un dont la tâche était une seule commande — et comme un lead n'écrit jamais de code lui-même, la chaîne n'a **rien pu livrer**. Le sous-système n'était pas en panne : #31 et #41 ont abouti pendant ce temps. Symptôme observé : les sous-agents passent *idle/available* sans remettre de résultat, et certains tentent un `SendMessage` vers « lead-issue-mtb », qui **n'est pas une adresse** — un type d'agent n'en est pas une, et l'envoi échoue toujours. **La remise du résultat se fait par la fin d'exécution, pas par message.** | lot 11 | une issue `infra`, ou une consigne d'adressage dans le prompt de `lead-issue-mtb` |
| **T63** | **`docs/contracts/issue-2.md` exige un commentaire dans `index.html` qui n'a jamais existé.** La ligne 368 demandait que le caractère provisoire de `Aucun contenu à afficher.` soit « signalé en commentaire dans `index.html` ». A8bis a supprimé le commentaire de la ligne 1 au lot 9 ; `templates/index.html` (35 lignes) n'en porte aucun. §9.5 portant désormais le gel et sa provenance (T53), l'instruction est **redondante mais formellement non tenue**. À classer explicitement plutôt qu'à laisser pendante | #2, relevée au lot 11 | classement, ou une ligne de gabarit |
| **T62** | **Deux renvois internes faux dans `MASTER.md`, préexistants et volontairement non corrigés au lot 11.** `:16` annonce que les révisions s'inscrivent au « §15 » alors que le journal est **§16** (les trois autres renvois à §15 sont justes) ; `§7.6` renvoie à un « §9.6 » **inexistant**, §9 s'arrêtant à 9.5 — déjà noté en `issue-11.md:624`. Relevées par #41, laissées intactes parce que hors de ses quatre points ; la revue a **vérifié octet pour octet** qu'elles n'avaient pas été touchées | antérieures, relevées au lot 11 | **RE-ROUTÉE au lot 14**, même motif que T79 : la passe 1.4 a eu lieu et a décliné, l’en-tête étant hors empreinte de #43. **Ses deux moitiés sont entières**, vérifié — ni le renvoi `:16` vers « §15 », ni celui du §7.6 vers un « §9.6 » inexistant. Réattribuée à **une issue `design` dédiée**, avec T79 |
| **T61** | **Le piège de `.env` est documenté là où on ne le rencontre pas.** `docker.md:94-99` avertit qu'**effacer** la ligne `WORDPRESS_DEBUG=` donne `WP_DEBUG=true` — l'inverse de ce qu'on croit faire — parce que `compose.yaml:43` porte `${WORDPRESS_DEBUG:-1}`. Mesuré au lot 11, pas déduit. Mais le geste se fait dans `.env`, et `.env.example:26-35` ne le dit pas. Deux lignes de commentaire au bon endroit | #31, lot 11 | une passe `infra`, ou la prochaine issue qui rouvre `.env.example` |
| **T60** | **`docs/contracts/issue-7.md:1127` a été rendu faux par le correctif du lot 11 lui-même.** Il écrit que les erreurs partent sur `/dev/stderr`, « donc dans `docker compose logs wordpress` et **non** dans `wp-content/debug.log` — le fichier existant est un reliquat ». **Deux moitiés mortes** : la destination (démentie par `compose.yaml:70`) et « le fichier existant », démenti par la mesure de #31 (`debug.log` **absent** sur volume neuf, avant *et* après provisionnement). Contrat gelé, hors empreinte, **délibérément non modifié** ; `docker.md:115-117` donne la bonne adresse et annonce la péremption | #31, lot 11 | une issue de correction des contrats gelés |
| **T59** | **`docs/contracts/issue-8.md:1305` porte deux faussetés dans une seule phrase, et c'est lui qui a fabriqué l'énoncé faux de #31.** Il écrit que « `WP_DEBUG` est en réalité **à `false`** dans cette pile […] **`WP_DEBUG_DISPLAY` étant à `true`** ». La première moitié est une **mesure WP-CLI généralisée à tort au chemin web** — le piège que la décision 29 signale ; la seconde était vraie et vient d'être rendue fausse par `690024f`. **Le défaut est étroit, pas un motif récurrent** : `issue-15.md:695`, `issue-18.md:693`, `issue-22.md:826` et `issue-29.md:305` disent au contraire le vrai. Contrat gelé, **délibérément non modifié** | #8, mesurée au lot 11 | une issue de correction des contrats gelés |
| **T58** | **Deux dettes dans le même fichier, à payer d'un seul geste.** `includes/query/portee/hydratation.php` porte (a) le **libellé des trois disponibilités déclaré une seconde fois** (`:57-63`, jumeau de `content/portee/champs.php:26-32`, identiques mot pour mot aujourd'hui — le jour où l'une est retouchée, la colonne d'administration et le badge public diront deux choses différentes **en silence**), et (b) le **tri d'une date illisible resté faux côté public** (`:144-176` compare la date brute par `strcmp`, sans le test de lisibilité que `fa80eb3` a posé côté administration). Conséquence de (b) : depuis `fa80eb3`, l'administration et le site public **divergent sur cet état**, ce que la fiche a dû nuancer. **Une seule issue** (epic 11, `portees`) pour ne pas consommer deux créneaux disjoints sur le même fichier. | lot 10 | une issue `portees` |
| **T57** | **Les deux dernières captures promises du guide.** `liste-portees-etat-vide.png` exige **aucune portée publiée** — impossible sans dépublier les 31 de la stack, destruction non réversible ; `coordonnees-legende-image.png` est un détail de la fenêtre de médiathèque, déclaré non pris. Tout le reliquat de #37 tient dans ces deux-là : **119 des 121 promesses sont tenues**. | #37, lot 10 | une issue `doc`, epic 10 |
| **T56** | **L'ordre des statuts de `MASTER.md:1014` diverge de `choix.php:105-127`**, et la revue tranche que **c'est MASTER qui est périmé** : l'ordre du code est celui, gelé, des groupes de la page publique « La meute ». L'écart est désormais **imprimé dans le manuel de l'éleveuse** par la fiche du lot 10. Une chaîne future qui ouvrirait MASTER « réparerait » le code et casserait **à la fois la page publique et la fiche**. **Aucune issue ouverte ne le couvre** — ni #41 (§9.5, écrans de recours) ni #42 (Q14, disciplines). Empreinte : `design-system/MASTER.md` §10.2 seul, aucun code. | lot 10 | une issue à ouvrir, jamais dans le même lot que #41 ou #43 |
| ~~**T55**~~ | **`MASTER.md` se contredit sur le libellé du lien vers l'index des portées, et c'est §10.3 qui perd.** §9.5 écrit « **Les portées** », §9.3 « Voir toutes les portées », et **§10.3 — « Vocabulaire figé », qui se déclare lui-même l'arbitre — écrit « Toutes les portées »**. Le code (`lien-de-recours/rendu.php:71`, `editeur.js:26`) dit « Les portées », aligné sur §9.5 ; `issue-16.md:378` le dit explicitement et **ne cite jamais §10.3**. L'écart n'a donc jamais été arbitré, il a été hérité. Il vit **entièrement dans `mtb-core`** : le corriger là corrigerait les **trois** gabarits d'un coup, ce qui prouve que le libellé appartient au serveur. Ne pas y embarquer le `h1` de `archive-mtb_portee.html` — §10.3 régit des libellés de lien, pas un titre de page. | #16, relevé au lot 9 | ✅ **PAYÉE au lot 14 par #43** — libellé unique « Toutes les portées » sur les trois gabarits, §10.3 arbitré avec sa provenance et §9.3 aligné (M3 payée au passage) |
| **T54** | **`MASTER.md` §10.2 liste huit disciplines, le code en rend neuf.** `query/resultat/bootstrap.php:29-41` rend neuf clés — la neuvième est `autres_disciplines` — et `fields/resultat/ecran.php:209-213` la propose à l'écran de saisie. **Les fiches d'aide ont raison d'écrire « neuf » ; c'est `MASTER.md` qui est périmé**, alors même que son §10.3 se déclare l'arbitre du vocabulaire. Cette neuvième clé est l'objet de **Q14**, ouverte. | relevé au lot 9 | **l'issue #42**, qui couvre déjà ce désaccord mot pour mot — elle est garée sur Q14 (énoncé corrigé au lot 13 : la ligne désignait `lead-design-mtb` sans nommer son propriétaire réel). **Le libellé « Autres disciplines » est recopiable hors Q14** (`query/resultat/bootstrap.php:38`, déjà rendu à l'écran de saisie et dans les tableaux publics) ; **le compte, lui, ne l'est pas** — si Q14 tranche que les quatre lignes du site source sont des disciplines à part entière, la liste close gagne des entrées et « neuf » est re-périmé aussitôt |
| ~~**T53**~~ | ~~**`MASTER.md` §9.5 nomme deux écrans de recours, le thème en a désormais trois.**~~ **PAYÉE au lot 11 par #41** (`2637429`, `e75b744`, `02ff0c1`). §9.5 porte désormais une règle unique de la sortie de secours et un **tableau clos** des écrans qui la portent ; elle dit « **jusqu'à** trois liens » et non trois, parce que la production en rend **deux** (`render.php:39-41`) ; elle ne s'appelle plus « Pages d'erreur », un seul de ses cinq écrans en étant une. Le `<p>Aucun contenu à afficher.</p>` est **figé avec sa provenance et sans qu'un mot soit inventé** : `issue-2.md:368` le déclarait « provisoire, remplacé par les formulations MASTER §9.5 à l'epic Gabarits », epic close au lot 9 sans que le remplacement ait lieu — le figer honore une promesse écrite, ce n'est pas un acte de contenu | #17, lot 9 | ✅ **payée au lot 11** |
| ~~**T52**~~ | **Le projet expédie ~50 Ko de commentaires CSS au navigateur, faute d'étape de minification.** Mesuré au lot 9 sur l'Accueil : **147 492 o de CSS décompressé pour 56 140 o sur le réseau** — `base.css` 46 927, `mtb-bandeau-ouverture.css` 34 566, `entete-pied.css` 28 791, `mtb-grille-chiens.css` 26 786, `tokens.css` 10 422. **Non bloquante** : D8 se lit en octets réseau depuis la décision de l'utilisateur du 2026-08-29, et le site y est très largement sous le plafond. C'est le reliquat vivant de T37, fermée. | héritée, chiffrée au lot 9 | ✅ **PAYÉE au lot 14 par #40** — 245 252 → 41 810 o d’artefacts, Accueil 147 492 → 43 831 o décompressés ; successeurs T87 à T92 |
| **T51** | **`mtb/lien-de-recours` émet un `<li>` sans garde de conteneur.** Posé au premier niveau d'une page plutôt que dans un `core/list`, il rend un `<li>` orphelin directement dans `.entry-content`. **Atténuée et non nulle** : le bloc est `inserter: false` (`blocks/lien-de-recours/block.json:18`), donc le chemin d'édition ordinaire ne peut pas produire ce cas, et aucun des trois gabarits livrés ne le fait. | #16, relevé au lot 9 par la passe d'intégration | une passe d'alignement sur `mtb-core` |
| **T50** | **Les fiches d'aide promettent l'historique des versions, que rien ne garantit.** Deux fiches disent à l'éleveuse que « le site garde les versions précédentes de votre texte ». C'est vrai — par le **défaut de WordPress**, `WP_POST_REVISIONS` n'étant défini nulle part dans le projet. Le jour où quelqu'un désactive les révisions, **deux fiches mentent d'un coup**. Soit le projet fixe la constante, soit les fiches cessent de promettre. | lot 8 | non affectée |
| ~~**T49**~~ | ~~**`test-integration-mtb` et `leaddev-back-mtb` ne s'enregistrent pas.**~~ **PAYÉE au lot 11 par #47** (`73f1ea4`). Les deux fichiers existaient ; ils ne s'enregistraient pas. **Le correctif du lot 9 (`6180741`, le `: ` non échappé) est infirmé** : il était en place et le symptôme a survécu à un vrai redémarrage — constaté en session neuve au début du lot 11, les deux noms absents, les quinze autres présents. Ce qui les distinguait **sur le disque** : seuls en LF (les quinze en CRLF) et seuls avec une description entre guillemets contenant un `: `. Écartés par la mesure : la longueur (`review-mtb`, 431 caractères, fonctionne) et le non-ASCII. Réalignés sur la forme des quinze ; **les deux se sont enregistrés immédiatement, vérifié en session**. **Réserve à ne pas perdre : deux variables ont été changées ensemble, la cause n'est pas établie entre les deux.** Un `.gitattributes` restreint rend la forme durable — sans lui, git stocke les 17 en LF et un `checkout` en `autocrlf=input` les rendrait tous LF d'un coup | lot 8 | ✅ **payée au lot 11** |
| **T48** | **Le contournement CRLF du provisionnement.** Sur un checkout Windows (`core.autocrlf=true`, aucun `.gitattributes`), `/bin/sh` refuse `provision.sh` et **la pile ne démarre pas du tout**. Contourné dans `compose.yaml` par un `sed` à l'entrypoint, documenté à l'endroit exact. La vraie réparation serait un `.gitattributes` **ciblé sur `docker/**` seul, jamais à la racine** — un fichier racine renormaliserait `docs/migration/source/html/*.html` et **détruirait la preuve d'archive** (T-#21-m). | lot 8 | non affectée |
| **T47** | **`.env.example` promet un site « vide de démonstration » que `MTB_FIXTURES=0` ne produit pas.** L'interrupteur ne saute que le semis des 14 contenus ; la page **« Espace privé (démonstration) »** et la pièce jointe **`portee-demo-portrait-test.png`** sont créées inconditionnellement (`provision.sh:148-151` et `:249-252`). Soit les passer sous le même interrupteur, soit corriger la phrase. | lot 8 | non affectée |
| **T46** | **Asymétrie de contrôle sur le fait de non-indexation.** #21 rejette en amont un fait de robots incomplet ; #20 n'a pas l'équivalent — un `extrait` vide ne serait attrapé par rien. Les quatre fiches sont complètes aujourd'hui ; la parité vaut une vingtaine de lignes. Déclarée par la chaîne #20 elle-même. | lot 8 | non affectée |
| **T45** | **Deux régimes pour le même artefact dans le même lot.** La ligne d'espacement de l'éditeur IONOS (`<p>&nbsp;</p>`) est **refusée par le garde-fou de #21** et **publiée par #20** sur neuf pages (`chien_opium`, `chien_youry`, `portees_a2_2025`, `d_2008`, `e_2009`, `g_2011`, `l_1995`, `m_1996`, `s_2001`). Les deux régimes se défendent — prose recopiée d'un côté, donnée structurée de l'autre — mais le choix doit être écrit pour ne pas se lire comme une incohérence. | lot 8 | non affectée |
| **T44 bis** | **`wptexturize` altère des valeurs d'élevage recopiées, et produit deux graphies du même fait sur une même page.** `chien-tesla` rend `--%` dans le champ structuré et `–%` dans le texte libre, alors que le contrat pose que **`--%` est la valeur** ; `portees/a2-2025` rend `Upper'Side` et `Upper’Side` ; 46 `&#8211;` sur `chien_etch`. T44 couvre le voisinage (interface contre rédactionnel), **pas** ce cas. Aucun document ne le déclare. | lot 8 | non affectée |
| **T43 bis** | **L'affixe disparaît dès qu'un résultat est rattaché à une fiche.** `/travail/` affiche `Jango` là où la source écrit `Jango de l'Orée des Crayères` : le titre de la fiche l'emporte sur le nom recopié. **Une ligne sur 61 aujourd'hui, toutes celles que l'éleveuse rattachera demain.** Le fait n'est pas perdu (`_mtb_chien_nom` le porte, la fiche l'affiche) ; il est **tu sur la page où le visiteur le lisait**. Écart déclaré au §9 du contrat #21 après coup, la liste s'étant crue exhaustive. | lot 8 | rendu : #15 |
| **T40** | **Une page protégée par mot de passe est publiée au sitemap.** `BRIEF.md` §8 (l. 180) interdit sa présence « ni dans les index publics, ni dans le sitemap, ni dans la recherche ». Mesuré : `wp-sitemap-posts-mtb_portee-1.xml` contient `/portees/p1-2026/` et `wp-sitemap-posts-page-1.xml` contient `/espace-prive/`, tous deux à `post_password` non vide. Cause : WordPress 6.9, `class-wp-sitemaps-posts.php:123` et `:244` ne posent que `post_status => publish`, **jamais `has_password => false`** ; aucun filtre `wp_sitemaps_posts_query_args` dans `mtb-core`. **Seconde moitié, non mesurée par la recette et relevée par la revue** : `templates/search.html` imprime la requête principale et les deux types sont `public => true`, donc la **recherche native** liste aussi les contenus protégés — c'est la dette **T8**. Les deux autres exigences sont tenues (absent de `/portees/`, aucune fuite de contenu dans le source anonyme) | cœur WordPress + lot 2, mesuré au lot 6 | **#23** (`prive`) — qui doit porter **les deux moitiés**, sitemap *et* recherche, pas une seule |
| **T44** | **Deux apostrophes se côtoient sur la même page.** Les chaînes d'interface des blocs emploient l'apostrophe droite `'` (U+0027) tandis que le contenu rédactionnel, passé par `wptexturize`, affiche l'apostrophe courbe `’`. Mesuré au lot 7 sur une même page : « Écrire à l’élevage » (courbe) puis « Votre message n'a pas été envoyé. » (droite). **Ce n'est pas propre à #22 : les onze blocs du catalogue font pareil.** Question de cohérence typographique à trancher globalement, jamais bloc par bloc | antérieure au lot 7, relevée par lui | `lead-design-mtb` — une règle unique, puis une passe d'alignement |
| **T43** | **L'expéditeur réel (`From:`) des courriels du formulaire n'est ni prouvé ni infirmé, et ne peut pas l'être en local.** `provision.sh` installe un mu-plugin qui filtre `wp_mail_from`, donc Mailpit affiche toujours `MTB (développement) <no-reply@mtbrabant.local>` — la valeur du provisionnement, jamais celle du bloc. Ce qui **est** établi : `grep` sur toute l'extension ne trouve **aucun** `add_filter('wp_mail_from')` ni `wp_mail_from_name`, et le `Reply-To:` de la visiteuse est prouvé à l'arrivée. La preuve est donc **par absence dans le code**, jamais par observation d'un en-tête. Enjeu réel : sans copie en base (décision 45), **un message non délivré est perdu sans que le site puisse le savoir** — un `From:` mal formé le ferait taire | #22, lot 7 | mise en ligne (Q5), avec le réglage SPF/DKIM |
| **T42** | **Débordement horizontal à 180 px CSS** (zoom 200 % sur un écran de 360 px) sur **4 combinaisons de 36**. Mesuré : `scrollWidth 219 > clientWidth 180` sur deux fiches de portée, `198 > 180` sur une fiche de chien, `263 > 180` sur la page protégée. Coupables identifiés élément par élément : `TABLE.mtb-tableau--chiots` et `TABLE.mtb-tableau` (palmarès), **livrées au lot 6**, et `INPUT#pwbox` + son `LABEL`, produits par **`post_password_form()` du cœur WordPress**, qui code `size="20"` en dur. **Le bloc du lot 7 ne déborde pas.** Non détecté aux lots précédents parce que la combinaison « 360 px **et** zoom 200 % » n'avait jamais été jouée sur les gabarits de fiche | #15 et #16, lot 6 — mesurée au lot 7 | une issue `a11y` |
| ~~**T41**~~ | ~~Au démarrage à froid, un relecteur ne voit que la moitié du site.~~ **PAYÉE au lot 7**, sur décision de l'utilisateur. `provision.sh` compose désormais l'accueil et la page Contact, et pose l'accueil en page statique. **Le contenu n'est pas recopié dans le script** : il est **relu à chaque provisionnement** dans `wp-content/themes/mtb/patterns/accueil.php`, donc le thème reste l'unique source de vérité sur la composition — contrainte 3 tenue jusque dans la pile de développement. Vérifié à froid : bandeau, encart « Portée DEMO1 2025 », grille des reproducteurs, formulaire sur `/contact/`, **0 diagnostic PHP**, et au rejeu « contenu réaffirmé » sans aucune duplication. **Piège trouvé en chemin, à ne pas reperdre** : référencer le motif par `<!-- wp:pattern -->` au lieu d'en copier le balisage casse `titre-principal.php`, qui inspecte le **premier bloc réel** de `post_content` pour décider qui porte le `<h1>` — le premier bloc devient `core/pattern`, le titre du cœur n'est plus effacé, et la page rend **deux `<h1>`**. Documenté en commentaire à l'endroit exact. **Arrive après le verdict de la revue** : validé par la seule vérification à froid de `docker-mtb` | #29 et infra, lot 7 | ✅ **payée au lot 7** |
| ~~**T39**~~ | ~~`docker/provision/provision.sh:74-75` peut livrer un site en anglais, en silence.~~ **PAYÉE au lot 7.** Le script **relit `WPLANG` juste après l'avoir écrit** et journalise une ligne `ERREUR` explicite si `sanitize_option()` l'a vidée en silence (paquet `fr_FR` non téléchargeable) — le marqueur `[provision] terminé.` reste **inchangé et toujours émis**, pour ne pas casser les scripts qui l'attendent. Réserve honnête : sur le démarrage à froid de vérification le réseau fonctionnait, donc **la branche d'erreur n'a pas pu être observée en conditions réelles**, seulement relue (`sh -n` propre). Le défaut d'origine, lui, avait été reproduit de façon déterministe au lot 6 |
| **T38** | **`docs/migration/redirections.md:41-43` est périmé** depuis les commits de #17 : il écrit « page Travail **non livrée** (#17) » et « accueil livré, mais son contenu n'est pas repris ». Techniquement encore vrai du point de vue du dépôt — rien n'est versionné — mais trompeur pour le prochain lecteur, qui croira l'epic non commencée | revue du lot 6 | #19-#21, à la première relecture du fichier |
| ~~**T37**~~ | ~~**D8 n'est pas tranchée : deux mesures vraies, deux contenus différents.**~~ **FERMÉE au lot 9, sur décision de l'utilisateur du 2026-08-29 : le budget de `BRIEF.md` §12 se lit en octets réseau.** Mesuré indépendamment à cette lecture — Accueil **68 749 o**, Travail **60 491 o**, plafond 200 000 : **D8 tenue, sans réserve.** Deux chiffres de la ligne d'origine sont morts et il faut savoir pourquoi : les 210 680 o décompressés **ne sont pas reproductibles** — ils portaient sur un Accueil que `docker/provision/provision.sh:192` réécrit **inconditionnellement** à chaque provisionnement, si bien qu'aucun contenu repris n'y a jamais vécu (l'import du lot 8 ne comporte pas d'accueil, et `docs/migration/reprise-resultats-pages.md:85` le déclarait déjà) ; l'Accueil remesuré donne **185 188 o décompressés**, soit 25 Ko sous le chiffre du contrat et **sous le plafond**. Et `base.css` n'est plus à 42 648 o mais à **46 927 o** — la feuille a grossi. **La cause, elle, est intacte et mesurée** : 147 492 o de CSS décompressé pour 56 140 o sur le réseau. Elle survit sous **T52**, non bloquante. ~~Suite de la ligne d'origine, conservée :~~ `issue-17.md:351-362` mesure **210 680 o décompressés** sur l'Accueil **avec le contenu réel recopié** — 10 680 o au-dessus du budget — et 72 413 o en octets réseau, sous le plafond. La recette du lot repasse au vert (**188 753 o décompressés**) mais **sur une base d'essai légère**. Les deux mesures portent sur des contenus différents et ne se contredisent pas. Cause chiffrée et externe au contenu : **141 473 o de CSS décompressé, non minifié**, commentaires compris (`base.css` 42 648, `mtb-bandeau-ouverture.css` 33 980, `entete-pied.css` 28 269, `mtb-grille-chiens.css` 26 333). **Le lot ne peut pas se clore en déclarant D8 tenue sans dire laquelle des deux lectures on retient** — c'est une décision, pas une mesure | revue du lot 6 | une issue `perf` (étape de minification) + arbitrage de la lecture retenue |
| **T36** | **Le repli d'`alt` est perdu sur les galeries des fiches.** `galerie-photos/rendu.php:152-166` refuse délibérément de composer un repli, donc `lecture.php:590-599` (« Photo de Jango ») ne sert pas. Nuance à ne pas perdre : l'`alt` de la médiathèque **est** transmis tel quel — l'image ne sort `alt=""` que si l'éleveuse a laissé la description vide, et le nom accessible du lien reste porté par `<span>Photo n sur N</span>`. Comportement contractuel de #12, déclaré | #12, rendu visible au lot 6 | arbitrage `lead-design-mtb` |
| **T35** | **`<main>` n'a aucun rembourrage bas** (`enveloppe-fiche.php:60`) : le dernier élément de contenu touche le pied de page à **0 px**. Aucune feuille du thème ne porte de sélecteur `main` ; `fiches.css:52-60` porte délibérément tout l'écart sur la marge **haute**. Vrai sur **toutes** les pages, pas seulement les fiches | antérieure au lot 6, chiffrée par lui | epic `design` ou passe d'alignement |
| **T34** | **Décrochement horizontal de 256 px entre `h2` de même niveau, à l'intérieur d'une même fiche.** `/chien/pegaz/` : « Santé » et « Titres et brevets » à **left = 432 px**, « Palmarès de travail », « Portées » et « Galerie » à **left = 176 px**. Idem `/portees/k9-2026/` et `/portees/`. Chaque affectation prise isolément suit `MASTER.md` §7.1 ; c'est leur **voisinage** qui produit une fiche qui se lit comme deux pages cousues. La question ouverte Q-front-1 du contrat #15 anticipait l'écart **entre** pages, pas **dans** une fiche | #15 et #16, lot 6 | **`lead-design-mtb`** — extension de Q-front-1 aux deux fiches |
| **T33** | **Les huit motifs de pages libres sont rangés sous la catégorie « Texte » du cœur, pas sous « Mont Brabant ».** `register_block_pattern_category( 'mtb', 'Mont Brabant' )` exigerait `functions.php`, hors de l'empreinte de #17. Effet limité au panneau « Motifs » : la modale de création de page ignore les catégories. **Cette dette était numérotée T28 par le contrat #17, en collision avec le T28 du contrat #16** — renumérotée ici, `ETAT.md` fait foi | #17, lot 6 | une issue `theme` qui rouvre `functions.php`, hors lot parallèle |
| **T32** | **La carte parent d'une fiche de portée ne rend ni portrait ni nom complet**, là où `MASTER.md` §7.4-2 (l. 627-630) prescrit les deux — « portrait `4/5`, nom d'usage en Newsreader 500, nom complet avec affixe en dessous » — et où §9.2 (l. 802) la nomme explicitement *emplacement structurant*. Cause de fond : `query/portee/hydratation.php:345-372` n'expose **ni `photo` ni `nom_complet`** sur un parent, dans aucun de ses trois états ; le thème ne peut donc pas les rendre sans lire la base lui-même, ce que la **décision 41** lui interdit. Le même traitement §9.2 a été livré **impeccablement** pour le portrait de la fiche chien, où la donnée, elle, est exposée. **Le défaut bloquant était le silence** : le contrat §5 réécrivait §7.4-2 en le raccourcissant, sans arbitrage, sans dette, sans signalement dans la feuille. Déclaré depuis au **§5 bis** du contrat #16 | #16, lot 6 — déclarée après la revue | issue de suite sur `mtb-core` (exposer `photo` + `nom_complet`), **après** arbitrage `lead-design-mtb` |
| **T31** | **Deux balisages de tableau coexistent** — le palmarès (extension, #15) et les chiots (thème, #16) — donc deux implémentations de la **décision 10**. Atténué, pas fermé, par la primitive `.mtb-tableau` unique de `base.css` : les deux **doivent** la porter | #15 et #16, lot 6 | une passe d'alignement |
| **T30** | **« La meute » n'existe que dans la base de développement.** Sur l'installation de Fabienne, la page n'existera pas — **mais le lien de recours ne pointe pas dans le vide : il ne s'affiche pas du tout.** `lien-de-recours/render.php:39-41` fait un `return;` nu quand la destination manque. **La conséquence écrite dans cette ligne était fausse depuis le 2026-08-20 et l'est restée quatre lots** ; corrigée au lot 11 sur constat de la revue. Le plus instructif : **T28, trois lignes plus bas dans ce même tableau, disait déjà le vrai**, comme la fiche imprimée (`page-creer-la-page-la-meute.md:7-9`) — trois documents contre un, dont deux voisins. La fiche dit « créez cette page », jamais « elle existe » — le piège qui avait bloqué le lot 5. **Deux conditions de plus, écrites dans §9.5 au lot 11** : l'adresse doit être exactement `la-meute` (`rendu.php:75`, `get_page_by_path`) et la page ne doit pas être protégée par mot de passe (`rendu.php:88`) | #16, lot 6 | #19-#21 (reprise du contenu) ou une création guidée |
| **T29** | **Les libellés d'identité de la fiche chien sont écrits dans le thème**, faute de `libelle` sur les champs de `mtb_get_chien()` (`lecture.php:292-330`), alors que la portée les fournit. Duplication : un libellé changé dans `MASTER.md` §10.2 devra l'être à deux endroits | #16, lot 6 | une issue de suite sur `mtb-core` |
| ~~**T28**~~ | ~~Deux liens de recours à URL non calculée.~~ **PAYÉE le 2026-08-20** par le bloc `mtb/lien-de-recours`. Les trois adresses sont calculées au rendu et le lien s'omet en silence quand sa destination n'existe pas. **Reste dû, hors de cette issue** : les deux mêmes URL en dur dans les **entrées de menu** en base, qui sont du contenu et non du code | #16, lot 6 | ✅ **payée** (reliquat de contenu ouvert) |
| **T27** | **Les deux composants affichent le même numéro de deux façons, désormais sur la même page.** L'encart d'appel groupe par paires tout numéro de dix chiffres nus (`encart-appel/rendu.php:78-84`) ; le bloc de coordonnées l'affiche tel quel. La **décision 38** acceptait l'écart au motif qu'il resterait « invisible tant qu'aucune page ne porte les deux » — **#18 a mis le bloc de coordonnées dans le pied de page de toutes les pages**, donc la condition est tombée. Mesuré : `06 80 50 56 19` dans le corps, `0680505619` en pied, même page. Aggravant : la ligne d'aide de l'écran (`encart-appel/editeur.js:162`) promet « s'affiche exactement tel quel », ce que le groupage dément. Les fiches disent maintenant la vérité, mais l'éleveuse lit d'abord l'écran. | lot 5, condition tombée | **`lead-design-mtb` + arbitrage utilisateur, avant #19-#21** |
| **T26** | **La liste « Page de contact » propose une page protégée par mot de passe**, que le rendu refuse ensuite en silence (`ecran.php:227-233` emploie `get_pages(post_status => 'publish')`, qui les inclut ; `encart-appel/rendu.php:182` les rejette). Mesuré : `Espace privé (démonstration)` est proposé ; s'il est choisi, l'écran affiche « Coordonnées enregistrées. » et **aucun bouton n'apparaît nulle part**, sans un mot. La non-validation dans le réglage est défendable ; l'offrir dans la liste sans le dire ne l'est pas. | #38 | issue à ouvrir |
| **T25** | **Le code de #38 n'a jamais été relu par un agent de refacto ni linté.** Sa chaîne d'origine est morte avant. La revue de lot l'a examiné ligne à ligne et **n'a trouvé aucune faille** — `declare(strict_types=1)`, garde `ABSPATH`, nonce et capacité sur l'écriture, échappement systématique en sortie, assainisseur propre au module, aucune requête SQL directe, contrôles négatifs à 403 et 400 — mais `phpcs` reste **impossible** faute de `phpcs.xml` (T24), et `php -l` seul ne dit rien du style ni des règles WPCS. | lot 5 | avec T24 |
| **T24** | **Aucun jeu de règles `phpcs` n'est versionné.** L'intégration du lot 4 a relevé **9 erreurs WPCS, toutes chez #11**, dont **4 ne sont qu'une seule décision d'architecture** (`coordonnees.php` déclare dans un espace de noms **et** dans l'espace global, ce que seule la syntaxe à accolades permet) — et la revue a établi que **ce motif existe déjà deux fois sur `main` depuis le lot 3** (`bandeau-ouverture/titre-principal.php`, `grille-chiens/bootstrap.php`). Sans `phpcs.xml` commité, le relevé n'est **reproductible par personne** et n'est donc opposable à personne, alors que `CLAUDE.md` impose WPCS. | lot 4 | une issue de dette : versionner le ruleset, puis rejuger |
| **T23** | ~~Les marges verticales entre composants se cumulent au lieu de fusionner (134 px et 173 px mesurés).~~ **PAYÉE au lot 6 pour composant↔composant** : quatre frontières mesurées sur l'accueil, **86,39 px, uniforme** ; encart d'appel → coordonnées 86,39 ; coordonnées → dernière portée 86,39. Prose inchangée (`p`→`p` 16 px, `p`→`h2` 64 px). **Résidu déclaré et confirmé** : frontière **prose→composant à 102,39 px** (16 + 86,39), annoncée par le commentaire de `base.css`. **T21 reste due** | antérieure au lot 4, chiffrée par lui | ✅ **payée au lot 6**, résidu prose→composant ouvert |
| ~~**T22**~~ | ~~Le `<hr>` du site rend 0 px de large.~~ **PAYÉE au lot 6.** Mesuré au navigateur : séparateur dans une Page **576 × 6 px**, dans le commentaire d'une portée **576 × 6 px**, en colonne étroite à 30 % **172,8 × 6 px**. La règle `h2 + hr` reste intacte (`display:none`). Le `<hr>` de `404.html` a disparu, remplacé par la liste de recours — le cas est sans objet | #2 | ✅ **payée au lot 6** |
| **T21** | **Les deux encarts frères ne sont pas interchangeables.** `mtb-bandeau-alerte.css:29` porte `margin-block: var(--e-7)` là où `mtb-encart-appel.css:22` porte `max(var(--rythme-section), var(--e-7))` (48 px contre 86,4), et `color: var(--texte)` est déclarée chez le premier, **absente chez le second** — qui pose donc le fond `--calcaire-creux` sans son encre, paire §3.2 non tenue. Sans conséquence visible aujourd'hui (`base.css:108` fait hériter la bonne encre), mais la paire tomberait **en silence** si un ancêtre changeait. Le contrat #9 §15 exigeait deux feuilles **littéralement identiques**, pour qu'un hissage futur en primitive `.mtb-encart` soit une **suppression** et jamais un arbitrage. | lot 4 | `lead-design-mtb` — hissage en primitive |
| **T18** | *(numérotée T18 et non T17 : le contrat #6 §16 emploie déjà T17 pour le réglage de cadrage — collision relevée par la chaîne #6, mon erreur de numérotation)* **`object-position: var(--point-interet, 50% 38%)` est écrit CINQ fois** — `bandeau-ouverture`, `fiche-information`, `liste-portees`, `derniere-portee`, `grille-chiens`. La dette annonçait « à hisser **avant** que la règle existe en cinq exemplaires » : c'est raté, et **la divergence a commencé** (voir T16-bis). Le crochet mutualisé sort de toutes les empreintes du lot. | lot 3 | ✅ **PAYÉE au lot 16 par #33** — et **le compte « cinq » était faux** : le recensement de la chaîne en a trouvé **sept**, `fiches.css:206` se déclarant lui-même « SIXIÈME ÉCRITURE ». Les trois `.mtb-dispo` nues, les trois `.mtb-photo` nues et trois des sept `object-position` sont hissées au **§13 de `base.css`**, en un seul exemplaire ; les **quatre survivantes** sont justifiées par **disjonction d'élément** — aucun de ces éléments ne porte `mtb-photo`, vérifié par grep exhaustif du PHP. Un seul crochet scopé survit (`blocs/mtb-derniere-portee.css:126`, doublé à (0,3,0) pour `white-space: nowrap`). **Aucun jeton `--cadrage-photo` créé** : le piège est écrit à `base.css:1332-1338`. T16-bis reste ouverte — le hissage unifie la **présentation**, pas le **sens du libellé « Centre »** |
| **T16-bis** | **Le réglage « Cadrage de la photo » n'a pas le même sens selon le composant.** `mtb-fiche-information.css:119` (#7) écrit `--point-interet: center center` pour « Centre » ; `mtb-grille-chiens.css` n'a **aucune** règle `--cadrage-centre` et retombe sur `50% 38%`, le défaut justifié de `MASTER.md` §6.2 (« sur une photo de chien en pied, la tête est au-dessus du centre géométrique »). **Fabienne choisit « Centre » une fois et obtient deux cadrages.** Les deux lectures ne peuvent pas être justes ; c'est le sens d'un libellé offert à l'éleveuse, donc `MASTER.md`. Lu dans le code, **jamais observé sur une vraie photo**. | #7 et #14 | **révision `lead-design-mtb`** |
| ~~**T15**~~ | **#8 sert sa feuille de bloc depuis l'extension**, contrairement aux cinq autres et au contrat #1 §8. Le motif technique a disparu (le correctif de #6 fait arriver les feuilles du thème dans la toile) : **ce n'est plus une dette technique mais une dette d'alignement**, assumée pour n'avoir pas fait basculer une chaîne en fin de course. | #8 | ✅ **PAYÉE au lot 15 par #34** — `galerie.css` **supprimée** et non vidée, sa présentation rendue au thème sous le seul nom que le chargeur dérive ; `"style"` et `wp_register_style` retirés ensemble ; toile de l'éditeur **symétrique à 11 × 2**, la galerie ayant rejoint ses dix sœurs. Successeur : **T95 (T15-bis)**, la dérogation résiduelle servie à l'éditeur seul |
| **T14** | **La sous-taille `mtb-vignette-galerie` entre dans le `srcset` de toute image téléversée**, donc dans les composants de #6, #12, #13 et #14, dont plusieurs contrats affirment qu'aucune sous-taille n'existe entre 300 et 768 px. **Aucun n'est faux sur son code ; tous le deviennent sur le site.** | #8 | amendement des quatre contrats |
| **T13** | **La forme d'état vide imposée n'a pas pris : quatre conventions coexistent entre les six composants**, alors que `MASTER.md` §9.1 la déclare identique pour les dix. Mes consignes sont arrivées après l'écriture du code (voir décision 31). | lot 3 | **partiellement payée au lot 4** : les trois composants de #9, #10 et #11 émettent la forme de référence (`<span class="mtb-etat-vide__nom">`, aucune classe modificatrice) et les deux états vides atteignables sont **identiques au pixel** (576 × 119). Le lot 3, lui, compte **quatre conventions** — hauteurs mesurées 119 / 135 / 135 / 161 / 161 / 183 — et `galerie-photos` va jusqu'à mettre **un bouton** dans son état vide, que §9.1 ne prévoit pas. Reste dû avant #25. **Point au lot 6** : `tableau-resultats` (#15) émet la forme de référence — cinq cadres sur une même page, **une seule forme** (`1px dashed rgb(154,123,63)`, fond `rgb(231,229,218)`, `padding 32px`, nom en laiton puis « Ce bloc n'affiche rien tant que… »), conforme à `MASTER.md` §9.1 ; les hauteurs 119/145/161/161/183 diffèrent par la **longueur du texte**, pas par la convention. **T13 reste ouverte sur un seul point** : `galerie-photos` met toujours un **bouton « AJOUTER DES PHOTOS »** dans son cadre, troisième ligne que §9.1 ne prévoit pas — et les motifs de #17 font cohabiter les conventions sur une même page pour la première fois. Côté public, rien ne fuit |
| **T12** | **Les formats modernes ne valent que pour les photos téléversées à partir de maintenant.** WordPress découpe et convertit **une seule fois, au téléversement** : la pièce jointe #13, antérieure au module, n'offre aucun candidat 400 w — son `srcset` saute de 300 w à 768 w. | #8 | **impérativement avant #19-#21**, sinon tout le stock photo est à régénérer | **Mesuré au lot 8, et à moitié payé** : sur les 135 pièces jointes versées, **128 ont au moins une sous-taille WebP, 7 n'en ont aucune — les 7 PNG**, `format_de_sortie()` ne mappant que JPEG→WebP. 592 WebP produits, 759 fichiers, 56,8 Mo. La sous-taille `mtb-vignette-galerie` est présente sur **134/135** : la 135ᵉ mesure 233 × 396 px et WordPress n'agrandit pas — absence légitime. Le module d'images appartient à #8 ; l'écart est écrit, pas corrigé.
| **T11** | **Le journal d'erreurs du développement contient 212 diagnostics** attribués au `require_once` manquant de `bandeau-ouverture` pendant la panne du matin. Reliquat probable, **non confirmé éteint**. | #6 | à vérifier au prochain démarrage propre de la stack |
| **T9** | **`assainir_texte_recopie()` existe désormais en QUATRE copies** — une quatrième est arrivée avec le formulaire de contact au lot 7 (T-#22-c). Rappel de l'état d'origine : **trois copies** — une par module, les empreintes disjointes interdisant un fichier partagé. Prix assumé du parallélisme. **Et les trois divergent déjà, au premier jour** : `content/chien/assainissement.php:51` remplace les caractères de contrôle par une **espace** et n'appelle pas `wp_check_invalid_utf8()` ; `content/portee/champs.php:236` et `content/resultat/assainissement.php:48-50` les **suppriment** et contrôlent l'encodage. Trois définitions de « valeur propre », trois résultats sur la même saisie. | #3, #4, #5 | à hisser dans un module commun, avant que la reprise n'écrive du contenu réel |
| ~~**T10**~~ | ~~Aucun rendu public : le thème n'appelle aucune fonction `mtb_get_*`.~~ **PAYÉE au lot 6 (#16).** Une portée saisie de bout en bout à l'écran par le compte `fabienne` (rôle Éditeur natif) a été relue sur son URL publique : **12/12 valeurs retrouvées**, puis sur ses **quatre** emplacements — sa page, `/portees/`, l'encart « dernière portée » de l'accueil, la fiche de la mère. **D1 et D2 sont vérifiées au HTML pour la première fois du projet.** La frontière est tenue à la lettre : zéro `WP_Query`, `get_posts`, `get_post_meta`, `get_terms`, `$wpdb` et zéro `MTB\` dans tout le thème, six appels `mtb_get_*` et rien d'autre | lot 2 | ✅ **payée au lot 6** |
| ~~**T1**~~ | ~~Fabienne ne pourra pas modifier son menu.~~ **PAYÉE au lot 5 (#18).** `edit_theme_options` accordée par un filtre `user_has_cap` sur la seule requête de l'écran des menus, rien en base (décision 44). Vérifié en session `fabienne` : `nav-menus.php` 200, `themes.php` et `site-editor.php` 403, « Apparence » absente de sa barre latérale. | #2 | ✅ **payée** |
| ~~**T2**~~ | ~~Le lien d'évitement dépend du JavaScript.~~ **PAYÉE au lot 5 (#18).** Lien écrit à la main en tête de `parts/header.html`, script `wp-block-template-skip-link` du cœur **déqueué seul** (pas l'action, sinon la feuille en ligne qui le masque partait avec) ; `tabindex="-1"` posé **au rendu** par `WP_HTML_Tag_Processor`, donc valable sur tous les gabarits — **#16 et #17 en héritent sans y penser**. | #2 | ✅ **payée** |
| ~~**T3**~~ | ~~L'accueil et la page de recherche n'ont aucun `<h1>`.~~ **INTÉGRALEMENT PAYÉE au lot 6 (#17)** : `templates/index.html:6` porte désormais un `h1` par `wp:site-title level 1` — mesuré **un seul** `h1` (« Berger Hollandais du Mont Brabant »), 0 violation axe, `lang="fr-FR"`. `search.html` et `404.html` l'avaient reçu au lot 5. **L'accueil n'échoue plus à D7** | #2 | ✅ **payée** (moitié lot 5, moitié lot 6) |
| **T4** | **D6 n'est tenue que pour le visiteur, pas dans l'administration.** L'éditeur du cœur charge 15 images depuis `s.w.org` (10 pour le guide de bienvenue, 5 pour les aperçus de blocs de l'insérteur), aucune n'est supprimée. Le site public est irréprochable : zéro origine externe, zéro cookie anonyme. Voir décision 15. | #2 | facultatif — une issue infra/admin sur `mtb-core` si l'écartement du guide est un jour voulu |
| **T5** | `class-loader.php` emploie `scandir()` que `functions.php` déclare interdit pour cause de portabilité mutualisée. **Les deux moitiés du lot posent l'hypothèse inverse.** Si celle du chargeur est fausse, `scandir` renvoie `false` et **l'extension ne charge rien, en silence**, sur un site qui répond 200. | #1 et #2 | avant la mise en ligne (lié à Q5) |
| ~~**T6**~~ | ~~L'empreinte du chargeur ne couvre que les types et taxonomies : aucune voie conforme pour une règle de réécriture sans type `mtb_`.~~ **PAYÉE au lot 10 par #27**, et son énoncé était partiellement inexact : une voie existait — l'incrémentation de `MTB_CORE_VERSION` — mais illégitime, passant par l'index central que la décision 9 proscrit. **Le trou réel était ailleurs et il est bouché** : l'empreinte observait les **noms** des types, jamais leurs **arguments de réécriture**, si bien que changer `'slug' => 'portees'` ne la faisait pas bouger et que l'ancienne adresse continuait de répondre — **pas un 404, un site qui a l'air de marcher avec les mauvaises URL**. Convention gelée : *un module appelle `add_rewrite_rule()` depuis son rappel `init` 10, sans condition de contexte, et n'a rien d'autre à faire.* Démontré en `curl` sans cookie, **200 dès la première requête**, sans jamais ouvrir `wp-admin`. **Conséquence de routage, mesurée et contraire à ce que ce tableau affirmait** : **#23 et #24 n'ont jamais eu besoin de #27** — une règle de réécriture traduit une URL en *requête*, une 301 est une *réponse* (`template_redirect`, motif déjà éprouvé dans le dépôt). | #1 | ✅ **payée au lot 10** |
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
| 16 | 11. Dette technique | #33 | **Le premier lot où une mesure réfute un contrat gelé, et où c'est le code — écrit contre le contrat — qui a raison.** **#33 paie T18** : trois `.mtb-dispo` nues, trois `.mtb-photo` nues et **sept** `object-position` (l'énoncé et ce journal en annonçaient **cinq** ; `fiches.css:206` se déclarait déjà « SIXIÈME ÉCRITURE ») deviennent **une** primitive au §13 de `base.css`, plus quatre `object-position` survivantes justifiées **par disjonction d'élément**. **La réfutation** : le contrat §5 rendait le doublage (0,3,0) obligatoire au motif que la toile préfixe `base.css` et pas les feuilles de blocs — **la toile est une iframe, le cœur n'y préfixe rien**, mesuré par la chaîne puis **indépendamment** par la passe (0 préfixé sur 11, et retirer 13.10 du CSSOM ne change aucun pixel). Le doublage **reste**, il gagne dans les trois contextes ; c'est sa **justification** qui tombe, d'où la décision 66 et cinq **notes de solde datées**. **L'arbitrage de valeur va contre la majorité, et a raison** : le rembourrage retenu est le **minoritaire**, parce que `fiches.css:117-119` **déclarait avoir recopié** `mtb-derniere-portee.css:96` — deux écritures pour une seule proposition, et trancher par la fréquence aurait reconduit le défaut même que #33 répare. Intégration : **42 vérifications, 42 passées**, au Chrome réel en CDP, A/B contre `git archive` de la base ; les 7 divergences sont **exactement** celles annoncées, `.mtb-photo` inchangée à zéro pixel, et le porteur n° 6 — le badge **sans classe d'élément**, principal risque du hissage — reçoit la primitive entière. Contrastes AA sur les trois états, 0 débordement à 360 px et à 200 %, focus mesuré au **vrai clavier** (118 éléments, 0 KO), **0 origine tierce, 0 cookie**, journal vide, net **−2 888 o** sur 15 artefacts. Revue **OK AVEC RÉSERVES** — 0 CRITICAL, 0 HIGH, 6 MEDIUM, 4 LOW ; **les quatre MEDIUM en empreinte corrigés avant le push**, dont un « cadre arrondi de l'index des portées » **qui n'existe nulle part** (`--r-0` vaut 0, aucun cadre photo du thème n'est arrondi) — une affirmation technique inventée dans un dépôt dont toute la culture est de ne pas inventer. **La leçon est encore pour le lead** : deux des ancres que j'ai transmises ne survivent pas à la mesure — j'ai demandé de corriger une ligne **déjà juste** (`base.css` l. 124) et affirmé **exacte** une ligne fausse (`MASTER.md` §4.5 l. 334, la bonne est 335), dans la consigne même qui demandait de me vérifier. La chaîne l'a fait. Troisième lot de suite. | `92f3202` → `242d603`, 4 commits |
| 15 | 11. Dette technique | #34 | **Le plus petit lot depuis le lot 9 — une issue, six commits, dont un seul porte du code — et le lot où le lead s'est trompé le plus souvent.** **#34 paie T15** : `galerie.css` est **supprimée** et non vidée, ses 6 règles / 31 déclarations étant visuelles à 100 % ; la présentation vit désormais dans le thème, **au seul nom que le chargeur dérive**, et la toile de l'éditeur devient **symétrique à 11 × 2** — la galerie a rejoint ses dix sœurs. **Le pré-vol est ce qui a débloqué l'issue** : le dépôt portait deux mécaniques contradictoires sur « une feuille du thème atteint-elle la toile ? », `issue-7.md:576-582` disant non et `issue-6.md:627` disant oui. **Ce n'était pas une contradiction : la première est une prédiction, la seconde la mesure qui l'a réfutée** — établi sur la pile vivante plutôt qu'en arbitrant entre deux textes. **La prise du lot** : l'index de la chaîne ne portait un moment que la suppression ; un `git commit` nu produisait **exactement l'état qu'elle avait elle-même classé DANGEREUX** — `<link>` vers un **404 sur chaque page à galerie**, « Photo N sur T » démasqué, **page en 200, zéro diagnostic**. Attrapé **dans** la chaîne. **Trois élargissements accordés**, tous sur précédent du dépôt : `editeur.css` en docbloc seul (sa jumelle supprimée y laissait **cinq renvois morts**), la **tâche 4 reformulée** parce qu'elle était infaisable au sens littéral, et le **§22 d'`issue-8.md`** — exception à « ne pas éditer les contrats gelés », accordée parce que **ce document porte déjà la convention** et que son §20.5 anticipait #34 (décision 65). La chaîne a refusé de rétrécir son `grep` pour le faire passer : **un `--glob '!editeur.css'` aurait fabriqué un vert qui ne prouve plus rien**. Intégration : **80 vérifications, 78 passées**, les 2 échecs antérieurs et déjà déclarés ; identité de la présentation établie par **six normalisations indépendantes** et le CSS servi comparé à la source sur **30 pages** ; la substance CSS d'`editeur.css` mesurée **identique à 138 octets sur 8 shas plus le disque**, à travers deux `--amend`. Revue **OK** — 0 CRITICAL, 0 HIGH, 1 MEDIUM **corrigé avant le push** (le docbloc décrivait comme purement esthétique une panne qui imprimerait « Photo 3 sur 12 » **sur la page publique**), 5 LOW. **Les erreurs du lead sont la vraie leçon** : deux ancres transmises sans être ouvertes, un chemin recopié d'un énoncé (`mtb-core/bootstrap.php`, inexistant), une consigne fausse sur le fond (« la vérification se fait par ce nombre » — le minifieur **énumère le disque**), un compte faux dans le journal (**douze** `editeur.js`, pas onze, et faux **le jour de son écriture**), une convention de colonnes inventée, et **une écriture dans l'empreinte d'une chaîne vivante** (décision 64). Toutes tombées sur une mesure, aucune sur une relecture. | `a171034` → `0a47ebc`, 6 commits |
| 14 | 11. Dette technique | #36, #40, #43 | **Le lot où la mesure a battu la relecture — cinq fois, sur trois chaînes et sur deux de mes propres consignes.** Aucune erreur de ce lot n'a été attrapée en relisant un texte ; chacune est tombée sur un chiffre. **#36** livre une mention rendue **au serveur**, seule approche qui atteint les 27 portées reprises que l'éleveuse ne réenregistrera jamais. Son vrai apport n'est pas la mention : en ouvrant le cœur 6.9 elle établit que **poser une photo principale ne s'écrit qu'à l'enregistrement** (`get-post-thumbnail-html` ne rend que du balisage) quand **la retirer s'écrit tout de suite** — si bien que sa **propre phrase gelée était fausse par omission**, disant « cliquez sur Choisir la photo principale » et s'arrêtant, alors que ce clic seul ne produit rien pendant que la vignette apparaît à droite. L'éleveuse voyait sa photo à droite, la ligne jaune à gauche, et recommençait. **Le JavaScript a été écarté par un argument meilleur que le mien** : j'avais classé « la mention se met à jour à l'écran » en tête des remèdes acceptables ; la chaîne démontre que c'est le pire — au clic la base n'a pas bougé, donc une mention qui s'effacerait là **annoncerait un résultat inexistant**. Sa hiérarchie fait doctrine : celle qui reste fait **recommencer un geste** (agaçant, rattrapable), celle qui s'efface trop tôt fait **partir** (silencieux, non rattrapable). **#43 paie T55** en retenant « Toutes les portées », non sur l'argument circulaire « §10.3 se déclare arbitre » mais sur deux faits vérifiables : la révision 1.2 avait **explicitement décliné** cette juridiction, et le contrat **gelé** `issue-13.md:745` avait déjà tranché au lot 5. Deux requalifications retenues : **la divergence était en production** — deux émetteurs vivants pour la même destination, « Les portées » depuis une 404 contre « Toutes les portées » depuis l'accueil — et **la DoD servie est D3, pas D1**, D1 ne s'invoquant pas pour un littéral figé. `MASTER.md` passe en **1.4** ; la divergence `h1` « Les portées » / lien « Toutes les portées » est écrite comme **délibérée**, avec son motif, pour qu'aucune chaîne ne l'aligne. **#40 paie T52** : artefacts **245 252 → 41 810 o**, Accueil **147 492 → 43 831 o** décompressés et **10 867 o** réseau, pire des 108 pages `/chien/jango/` à **9,0 %** du plafond. Sa prémisse de brainstorm était fausse — les feuilles sont à **88,0 % de commentaires en octets gzippés**, gzip ne les mange pas — et ses trois refus valent son livrable : `filemtime()` **dégénère en « toujours l'artefact »**, Git écrivant dans l'ordre lexicographique de son index ; un hachage sur octets bruts aurait été rejeté **partout en production** (`core.autocrlf=true`, 45 914 o dans Git contre 46 927 sur disque) ; la minification à l'exécution aurait fait tomber les deux polices en **404**, et `base.css:59-70` ajustant métriquement les replis, **la chute aurait été invisible à l'œil**. **La prise du lot vient de la passe d'intégration** : sur 17 avaries fabriquées, un **artefact tronqué en queue était servi tel quel** — 107 déclarations perdues sur 255, page en 200, aucune notice — ce qui **réfutait la revendication porteuse** de #40, « jamais une régression visuelle ». Le marqueur passe de deux à **quatre champs**, couvrant aussi la corruption à taille constante, et les phrases fausses sont **supprimées et non rétrécies**. Intégration : **443 vérifications, 439 passées**, puis **48 avaries sur 48** après correctif ; **D1 et D2 vérifiées** pour la première fois depuis plusieurs lots (une saisie → **cinq** endroits) ; **D6 vert** sur 108 pages et 5 feuilles, extracteur auto-testé sur **22 formes de citation**. Revue **OK** — 0 CRITICAL, 0 HIGH, 4 MEDIUM, 5 LOW. **La leçon, formulée par #40 elle-même** : chacune de ses trois erreurs a **passé la relecture** et n'est tombée que sur une mesure — et deux de mes consignes, le `filesize()` et une convention de colonnes qui n'existe pas dans `MASTER.md`, sont tombées de la même façon. | `7d82c0a` → `d9ed66d`, 12 commits |
| 13 | 10. Guide + 11. Dette technique | #46, #44, #39 | **Premier lot depuis le lot 9 dont aucun énoncé n'était faux** — les trois décrivaient un vrai défaut et prescrivaient le bon remède. Ce que les chaînes ont ajouté n'est donc pas une requalification mais un **dépassement** : chacune a livré la demande **et** la raison qui empêche le défaut de revenir. **#44 paie T56** en livrant la permutation **et la provenance** — T56 n'était pas née d'un ordre faux mais d'une **énumération nue dont rien n'expliquait l'origine**, donc « réparable à l'envers » ; le sens de la dépendance est désormais écrit **des deux côtés** (`MASTER.md:1079` et `choix.php:96`). **Un arbitrage d'empreinte rendu contre ma propre consigne** : « §10.2 uniquement » aurait rendu faux l'en-tête le jour même, et le dépôt prouve par ses entrées 1.1 et 1.2 que « §X seul » est un **titre de journal, pas un compte d'octets** (décision 58) ; `MASTER.md` passe en 1.3. **#39 paie T51** avec une garde sur le **parent** et non l'ancêtre — choix validé par la mesure : l'état d'avant produisait un `<li>` **dans** un `<li>` alors que le bloc **avait** un ancêtre `<ul>`, qu'un test formulé sur l'ancêtre aurait déclaré conforme. **La prise du lot vient de sa refacto** : `usesContext` posée après `keywords` **décalait `block.json:18`**, ligne citée par le contrat **gelé** `issue-17.md:412` **et par l'énoncé de T51 elle-même** — une correction de dette qui aurait périmé en silence sa propre citation. Garde-fou généralisé : les numéros de ligne du cœur sont des **repères de lecture, pas un contrat**, le `Dockerfile` tirant une étiquette **flottante**. **#46 paie T57** et **aucune capture ne reste déclarée manquante — une première du projet** ; `issue-37.md` avait tort d'en déclarer une impossible, et la bijection n'était pas « ouverte à 121 » mais **cohérente à 119**. Son vrai apport n'est aucune image : elle a trouvé **le geste qui faisait perdre son travail à l'éleveuse** (`multiple: true` contre `'add'`, sur deux fenêtres au **titre identique**) — fiche réparée, cause versée en T77. **T65 levée sur preuve et fausse sur toute la ligne** : 53 caractères et non 52, **quatre** lignes et non cinq, **aucune** coupure intra-mot ; §10.2 n'est pas rouverte. Intégration : **21 vérifications, 0 échec**, témoin rendu **falsifiable** par un contrôle positif à +9 o écartant l'opcache, et extracteur d'origines **auto-testé** — le bug exact de la passe du lot 11. Revue **OK AVEC RÉSERVES** — 0 CRITICAL, 0 HIGH, 2 MEDIUM, 4 LOW. **La leçon** : quand l'énoncé est juste, le travail qui reste est d'écrire **pourquoi**, sans quoi la dette repousse — et une chaîne qui prouve par un précédent du dépôt qu'une consigne du lead est mal formulée a raison de la remonter plutôt que de la suivre. | `015c237`, `7f3f422`, `6f88b98`, `911935d` |
| 12 | 11. Dette technique | #30, #45, #35 | **Deux issues sur trois prescrivaient un remède qui ne pouvait pas fonctionner** — au lot 11 les énoncés étaient faux **sur les faits**, ici ils le sont **sur le mécanisme**. **#30 requalifiée** : trois commandes tombaient et non une (`wp db query`, `db check`, `db export`), et **c'est le client qui exige TLS, pas le serveur qui en manque** (`mariadb-client 11.4.8`, `ssl=TRUE` compilé, face à `have_ssl = DISABLED`). Un fichier d'options ne pouvait pas suffire — WP-CLI invoque son client avec `--no-defaults`, **mesuré** : un `[client] ssl=0` répare le client nu et pas WP-CLI. Six enrobages `sh` avec **garde de retrait** ; **TLS délibérément non activé côté serveur** — D9 est un critère de fidélité au mutualisé, pas de confort. **#35 requalifiée** : `attachment_fields_to_edit`, que l'issue imposait, **ne mord sur aucun des six parcours sondés** (dans le cœur 6.9, `image_alt` est absent à l'entrée et détruit à la sortie là où le crochet s'exécute) — et le bon remède, un filtre `gettext` borné, **était écrit dans la dette T16 du contrat #6 depuis le lot 2** : une dette bien rédigée a fait gagner un lot entier. Guide : **six** fiches et non trois. Deux refus gravés — l'aide du cœur n'est pas remplacée (elle porte un lien W3C que notre phrase ne dit pas), et le filtre reste borné à deux chaînes exactes. **#45 paie T58** et **borne elle-même sa victoire** : `assainir_date()` refusant les dates malformées à l'écriture, **aucune portée saisie en administration ne pouvait déclencher la divergence** — le gain réel pour l'éleveuse est la disparition d'une **option d'année fantôme**. Intégration : **15 vérifications, 0 échec imputable au lot**, sur image `wpcli` reconstruite **sans cache** (première à froid depuis `690024f`), avec un **faux positif de son propre détecteur** signalé. Revue **OK AVEC RÉSERVES** — 0 CRITICAL, 0 HIGH, 4 MEDIUM, 5 LOW. **La leçon** : un énoncé peut décrire un vrai défaut et se tromper entièrement sur ce qui le répare. **Et la parade à T64 est trouvée** — une première passe d'intégration a tourné ~54 min sans rien rendre ; la relance, **obligée d'écrire son rapport sur disque au fil de l'eau**, a survécu. | `7939c42`, `071bc60`, `6af2add` |
| 11 | 11. Dette technique | #31, #34, #41 (+#47) | **Deux issues sur trois reposaient sur un énoncé faux, et les chaînes l'ont mesuré au lieu de l'exécuter.** **#31 requalifiée** : `WP_DEBUG` n'a jamais valu `false` sur le chemin web (`compose.yaml:43` depuis `c64087c`) ; le vrai défaut était que `debug.log` **n'existait pas** pendant que les diagnostics **s'imprimaient dans la page du visiteur** — **141 octets** mesurés, A/B démontré **deux fois indépendamment**, déduplication levée (5 requêtes → 5 lignes), D9 vérifiée sur trois valeurs **sur le chemin web**. Résultat le plus important : « journal vide » est **devenu falsifiable**, ce qui entame rétroactivement les passes des lots 9 et 10. **#34 n'a rien livré, délibérément** : 3 prémisses fausses sur 3, et déplacer la seule `grid-column` sur 5 déclarations aurait **fabriqué** la seconde source de vérité qu'elle prétendait éviter — requalifiée en T15, hors lot parallèle ; une moitié du motif de refus s'est elle-même révélée fausse et a été retirée. **#41 paie T53** : §9.5 était fausse sur **trois** points, dont « trois liens » là où la production en rend **deux**. **#47 paie T49** et **infirme le correctif du lot 9**. Intégration : **21 vérifications, 0 échec imputable au lot**, un bug trouvé par l'agent **dans son propre extracteur** — première passe du projet exécutée par le vrai `test-integration-mtb`. Revue **BLOQUANTE** — 0 CRITICAL, **2 HIGH, aucun défaut de code** (`git diff -- wp-content/` → 0 fichier) : une clause **normative** de §9.5 qui autorisait la suppression de deux règles AA, et **mon propre message de commit** affirmant une conversion CRLF que git n'avait jamais enregistrée. Les deux levés avant push. **La leçon** : une fausseté qui **donne un ordre** est pire qu'une fausseté qui décrit ; et une mesure d'arbre de travail n'est pas une mesure de dépôt. | `2637429`, `690024f`, `73f1ea4`, `7e7ae07`, `e75b744`, `02ff0c1` |
| 10 | 11. Dette technique + 10. Guide | #27, #28, #37 | **Premier lot depuis le lot 5 dont la revue n'a bloqué le push sur rien** — 0 CRITICAL, 0 HIGH, 5 MEDIUM, 6 LOW. **#27 paie T6, mais pas le défaut annoncé** : l'empreinte du chargeur observait les *noms* des types, jamais leurs *arguments de réécriture*, si bien qu'un `'slug'` changé ne prenait jamais effet — **pas un 404, un site qui a l'air de marcher avec les mauvaises URL**. Les **deux** approches imposées par l'issue ont été écartées (elles font de `add_rewrite_rule()` un no-op silencieux) et la **tâche 2 refusée à raison** : borner le flush à l'activation aurait livré un site en 404 après une mise en ligne par FTP. Démontré **défaut prouvé avant correctif**, 200 dès la première requête, sans jamais ouvrir `wp-admin`. **#28 met les listes d'administration au service de la règle d'or** — colonnes métier, trois filtres, ordre imposé, modification groupée de la disponibilité — et **D12 est démontrée par contre-exemple sur les trois types** (32→31, 23→22, 67→66), avec **13 requêtes SQL capturées sans une seule jointure de méta**. Sa refacto l'a rattrapée contre son propre arbitrage gelé : le module de corbeille **écrasait 15 phrases justes**. **#37 livre 119 captures et retire 84 encarts « Capture à prendre »** — des consignes de développeur qui vivaient dans le manuel de l'éleveuse. Périmètre requalifié sur le brief §13.1 (huit parcours nommés, pas une capture par composant) ; **seulement 2 promesses abandonnées**, mesurées par différence de commits. Intégration : **391 vérifications, 0 échec imputable au lot**, 2 défauts réparés avant push (une donnée d'essai photographiée, une date illisible classée en tête). **D9 démontrée à froid**, deux cycles, idempotence mesurée. **La leçon du lot** : en mono-branche, **une mesure n'a de valeur que datée d'un commit** — quatre agents s'y sont trompés, dont moi deux fois ; la disjonction des empreintes protège les écritures, **pas les lectures**. | `0c46eeb`, `ad80f45`, `d95fc71`, `cefb203`, `73591c4`, `fa80eb3`, `17d0e01` |
| 9 | 7. Gabarits et thème (clôture) | #17 | **Le plus petit lot du projet, et aucun de ses blocages n'était du code** : huit lignes de gabarit, **zéro octet de CSS, JS ou PHP**. A8 **levée** — les trois `mtb/lien-de-recours` posés sur l'état vide d'`index.html`, recopiés octet pour octet de `search.html` ; **2 liens rendus et non 3**, « La meute » s'omettant en silence (T30). Exception D1 écrite au guide, Mentions légales vérifiées et **délibérément non harmonisées**, T33 renumérotée. **D8 tranchée par l'utilisateur** : budget lu en **octets réseau** — Accueil 68 749 o, Travail 60 491 o contre 200 000, **T37 fermée**. Intégration : les cinq revendications du contrat reproduites **au chiffre près**, 1 débordement sur **42 combinaisons**, **81 arrêts clavier / 0 sans anneau**, **0 violation axe**, D12 éprouvée sur treize composants mal remplis sous `E_ALL` forcé, **D1 mesurée en session `fabienne` réelle**. Elle **réfute quatre affirmations du contrat**, dont une qui allait faire ouvrir une issue `a11y` pour un `<main>` **déjà focusable**. Revue **BLOQUANTE** — zéro CRITICAL, **deux HIGH tous deux de prose** : une fiche annonçant un lien cassé que le code ne produit jamais, et une section du contrat déclarant l'AA invérifiée sur tout le lot — **cette seconde née de ma propre consigne**, un cran trop large sur un filet décoratif de 6 px. Les deux levés, contre-revus, **aucune régression**. **D9 démontrée à froid** sur deux cycles complets. **T49 a une cause et elle est réparée** : un `: ` non échappé en YAML privait la chaîne de deux agents. **Cinq dettes neuves consignées** (T51-T55) qui ne vivaient que dans le contrat. | `5c7cf34`, `60f36f4`, `072d390`, `e10ca03` |
| 8 | 8. Reprise du contenu | #20, #21 | **Le contenu de l'ancien site entre dans le nouveau** : 27 portées, 17 chiens, 61 résultats, 7 pages libres, 135 pièces jointes, **592 WebP**. Transcrit dans des **fichiers versionnés**, importé par une commande qui **crée et ne met jamais à jour**, contrôlé par un comparateur hors ligne rejouable. **D11 démontrée au byte près** après destruction/reconstruction : 106 empreintes de contenu identiques, 47/48 URL identiques à l'octet, la 48ᵉ à 1 octet (borne 99/100 d'un compteur, prédite avant mesure). Intégration : **~900 vérifications, 2 échecs bloquants**. Le premier était invisible sans exécution : `est_une_liste()` déclarait le tableau vide « liste », donc **tout `"attributs": {}` était rejeté et 6 pages sur 7 perdues** — 13 occurrences dans les données, 13 avertissements, et la seule page sans occurrence était la seule créée. Revue **BLOQUANTE** puis **OK avec réserves** : un CRITICAL (**13 fichiers, 643 lignes non commités** — dont le fait `noindex` de Placement et `MASTER.md` 1.1 : ce qui avait été mesuré n'était pas ce qu'un push aurait envoyé) et trois HIGH (une fiche décrivant des marqueurs que le code ne produit plus ; **la même clé `_mtb_robots_source` sous deux formes incompatibles**, chaîne contre tableau, les deux documentations affirmant l'alignement ; l'**affixe de Jango** perdu dès qu'un résultat est rattaché à une fiche, contre une liste d'écarts qui se déclarait exhaustive). Tous levés. **`MASTER.md` passe en 1.1** : `break-word` ne comptant pas dans le calcul des tailles min-content, la règle §7.7 était **fausse**, pas contournée — 347,25 px sous `break-word` **comme sous `normal`**, 15,5 px sous `anywhere`. **`MTB_FIXTURES` livré** : le garde de non-mélange de #20 prescrivait un remède (`down -v`) que le provisionnement défaisait aussitôt. | `2018abe` → `9418979`, dont `031e59b`, `01d4489`, `efb8bc2`, `dcac163`, `0430fa8`, `50e37fc` |
| 7 | 8. Reprise + 9. Contact + dette | #19, #22, #29 | **Les 52 URL de l'ancien site sont archivées** — 309 fichiers, 36 Mo, dont **192 photographies**, sur décision de l'utilisateur de les versionner : la contrainte 4 ne dépend plus de la survie de l'abonnement IONOS. **Formulaire de contact** qui n'écrit rien en base (décision 45), anti-spam sans service tiers, mention d'information adossée ligne à ligne au code (décision 48). **`wp mtb import-fixtures`** enfin livrée : la pile sème 14 contenus. Deux passes correctives : msmtp réparé dans les images (**les deux voies de courrier étaient mortes**) et composition des pages au provisionnement. **Dettes T39 et T41 payées.** Intégration : **≈627 vérifications, 6 échecs, aucun imputable au lot**. Revue **BLOQUANTE** — zéro CRITICAL, deux HIGH : **cinq pages en `noindex` que rien ne signalait** (Q23) et trois livrables non commités. Les deux levés. Le jeton HMAC substitué au nonce est **ratifié** (décision 51). **Cinq questions tranchées par l'utilisateur** dans la session. | `3879b0d` → `a...`, dont `1464a7e`, `ca5e6b7`, `35aa919`, `3c4d047`, `ddaaea4`, `f0d06ff`, `e235160` |
| 6 | 6. Tableau de résultats + 7. Gabarits | #15, #16, **#17 (ouverte)** | **Le lot qui paie T10 : le site a un rendu public.** Une portée saisie apparaît sur son URL, dans l'index, dans l'encart d'accueil et sur la fiche de sa mère — **D1 et D2 vérifiées au HTML pour la première fois**. Tableau de résultats (10ᵉ composant), gabarits des fiches portée et chien, bloc `lien-de-recours` à URL calculées, 8 motifs de pages libres, 6 fiches de guide, 14 aperçus. **Dettes T3, T22, T23 payées et mesurées.** **Lot développé le 2026-08-20 par trois chaînes mortes avant validation**, retrouvé le 2026-08-23 à 15 commits d'avance sur `origin/main`. Intégration : **147 vérifications, 2 échecs, tous deux hors périmètre** (T39, T40). Revue **BLOQUANTE** — zéro CRITICAL, deux HIGH : un **fait d'élevage faux** affiché (« Eenhoorn Sire Eenhoorn », T32/L2) et un **écart silencieux** au `MASTER.md` (carte parent amputée, T32). Les deux levés et contre-vérifiés avant le push. **#17 non fermée** : son contenu recopié ne vit dans aucun fichier, D11 y est invérifiable. | `7cc371c` → `4da55b2`, puis `e80944e`, `25cdd0a` |
| 5 | 7. Gabarits (partiel) + dette | #18, #38 | **Lot non prévu** : `/lead-mtb #18` demandé, #38 embarquée comme prérequis. En-tête et pied de page sur toutes les pages, deux emplacements de menu que l'éleveuse compose seule, `search.html` et `404.html`, `entete-pied.css` (28 Ko). Écran de réglages unique des coordonnées. **Dettes T1, T2, T3 payées.** Intégration : **~190 vérifications, 7 échecs**. Revue **BLOQUANTE** — aucun défaut de code, **trois affirmations fausses dans les fiches d'aide**, corrigées avant le push (voir décision 43). **Deux trouvailles majeures** : le cœur ne relit jamais le menu classique après conversion (le menu aurait menti en silence), et `ariaLabel` sur un bloc Navigation est **doublé** par le cœur en 6.9. **La chaîne complète n'a été appliquée à aucune des deux issues** — `brainstorm-18`, `leaddev-18`, `leaddev-38` et `dev-38` n'ont jamais rendu ; #38 n'a **jamais eu de passe refacto**. | `0093f0f`, `94a78ee`, `01bccba`, `19d4e97`, `73e7165`, `4385b30`, `e82853c` |
| 4 | 4. Composants génériques II | #9, #10, #11 | Bandeau d'alerte, encart d'appel, coordonnées et plan d'accès — **trois chaînes en parallèle, empreintes strictement disjointes**, aucune collision. Catalogue à **9 composants sur 10**. Intégration : **31 vérifications, 0 échec** ; le **zoom 200 %** est mesuré pour la première fois (24 combinaisons), et les **onze contrastes sont lus sur pixels rendus** et non plus seulement calculés (décision 36). Revue **OK avec réserves**, **un seul HIGH** — un libellé divergent, aligné sur `MASTER.md` §10.2 avant le push (décision 39). **T13 payée** pour les trois composants ; **T21 à T24 créées**. #11 n'a livré **aucune carte** : deux points GPS distants de 2 km sur le site source, personne ne peut certifier lequel est l'élevage (Q18). | `166153b`, `c67f7cc`, `0e6d1d4`, `9367705`, `b90bcf1`, `d2f7ca0` |
| 0 | Bootstrap | — | Dépôt + board (10 epics, 25 issues) + `MASTER.md` + stack Docker vérifiée | `38d0935` puis amorçage |
| 3 | 3-6. Composants | #6, #7, #8, #12, #13, #14 | **Lot de 6 sur arbitrage de l'utilisateur** (décision 31). Six composants livrés avec leurs six fiches d'aide. Quatre dettes de lot payées : **T7** (bouton du cœur hors jetons), **T16** (canal large inatteignable — 1 088 px sur 7 colonnes contre 576 sur 3), le **CSS absent de la toile de l'éditeur** (`mtb-jetons` non enregistrée en admin, `all_deps()` abandonnait la feuille en silence), et l'**apparence d'état vide** sans propriétaire. **T10 partiellement payée** : premier HTML public du projet. 11 commits. | `ebdbf3a`, `a9250e4`, `96fda88` + 8 |
| 2 | 2. Types de contenu | #3, #4, #5 | Portée, chien, résultat : trois écrans de saisie classiques, en français, éditables par le rôle Éditeur. Guide à 3 fiches. | voir journal du lot 2 |
| 1 | 1. Infrastructure | #1, #2 | Extension `mtb-core` (chargeur à auto-découverte) + thème de blocs `mtb` (`theme.json` verrouillé, 2 polices auto-hébergées). Review **OK avec réserves**, D9 vérifié à froid. Milestone 1 fermé. 8 dettes tracées, 2 devenues issues (#26, #27). | `93dc6a5` |
