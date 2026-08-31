# Contrat d'interface — Issue #43 — Arbitrer le libellé du lien vers l'index des portées (T55)

**Gelé le 2026-08-31.** Milestone 12, epic « 11. Dette technique — chargeur et permaliens ».
Labels `design`, `task`. Révision `design-system/MASTER.md` **1.4**.

Ce contrat **remplace la ligne `portees` du tableau des libellés de `docs/contracts/issue-16.md` §10**.
Tout le reste de ce tableau — adresses calculées, cas de rendu vide, classe émise — reste en vigueur
mot pour mot. Un contrat gelé se surcharge par un contrat plus récent qui le nomme, jamais par une
réécriture silencieuse.

---

## L'arbitrage

**Le libellé de tout lien menant à l'index des portées est « Toutes les portées ».**

Il ne s'agit pas d'un choix de goût mais d'un départage entre trois formulations que le dépôt portait
simultanément, dont **deux étaient vivantes à l'écran public**. Trois appuis, dans cet ordre.

**Un — `MASTER.md` §10.3 est l'arbitre déclaré, et il écrivait déjà ce libellé.** Le préambule du §10
lui donne cette autorité. Sa ligne « Vers l'index des portées | **Toutes les portées** » **n'a pas
changé** en 1.4 : ce sont les §9.3 et §9.5 qui ont été recopiées sur elle.

**Deux — la révision 1.2, plus récente, avait explicitement décliné cette juridiction.** Son entrée au
journal écrit : « §10.2 et §10.3 ne sont pas touchés, leurs écarts ayant chacun leur propre issue ».
La valeur « Les portées » qu'elle laissait au §9.5 n'était donc **pas un arbitrage récent** mais une
valeur **recopiée en avant** par une révision qui s'était dite incompétente sur le sujet. **La règle
« le plus récent gagne » ne s'y applique pas** — et il fallait l'écrire, parce qu'elle avait une
apparence de sérieux et qu'elle aurait rouvert l'écart.

**Trois — un contrat gelé avait déjà tranché dans ce sens, et personne ne l'avait porté au document.**
`docs/contracts/issue-13.md`, arbitrage 7, au **lot 5** : « Toutes les portées », parce que « §10 se
déclare arbitre du vocabulaire ». Il avait ouvert la dette **M3** et **interdit au thème de réécrire
ce libellé**. M3 est payée ici.

### La règle générale, qui est le vrai livrable

> **§9.5 dit QUELS liens paraissent, sur quels écrans et dans quel ordre.
> §10.3 dit COMMENT ils s'appellent.**

Lues ainsi, les deux sections **ne se contredisent plus** : §9.5 recopiait un nom qui ne lui
appartenait pas. Une divergence future entre les deux listes se tranche **toujours** en faveur du
§10.3. Cette règle est écrite dans `MASTER.md` (§9.5 et §10.3) **et** pointée depuis le code — c'est
ce qui la rend vérifiable dans les deux sens.

---

## Requalification de l'énoncé de l'issue

L'énoncé décrivait un vrai défaut mais **sous-estimait sa portée**, et **se trompait sur la ligne de
DoD servie**. Les deux points ont été vérifiés dans le dépôt, pas déduits.

**1. La divergence n'était pas documentaire, elle était en production.** L'énoncé annonçait « trois
formulations » dont une seule dans le code. Il y avait **deux émetteurs vivants**, dans `mtb-core`,
pour exactement la même destination (`get_post_type_archive_link( 'mtb_portee' )`) :

| Émetteur | Libellé avant | Écrans |
|---|---|---|
| `blocks/lien-de-recours/` (`rendu.php`, `editeur.js`) | « Les portées » | page introuvable, recherche sans résultat, index vide |
| `blocks/liste-portees/` (`rendu.php`, méthode `sortie()`) | « Toutes les portées » | accueil, toute page portant le composant |

Une visiteuse qui cliquait depuis l'accueil lisait « Toutes les portées » ; depuis une page
introuvable, « Les portées » — **pour la même page**. C'est ce fait, et non la contradiction interne du
document, qui donne son poids à l'issue.

**2. La DoD servie n'est pas D1.** L'énoncé invoquait « §14 — D1 : cohérence du vocabulaire affiché ».
D1 dit : « Ce qui est livré s'édite depuis l'administration WordPress, **sans toucher un fichier** ».
Un libellé figé par un document de design est, par construction, ce que D1 décrit comme un problème —
on ne peut pas l'invoquer pour justifier un littéral, et **il ne faut pas** rendre ce libellé éditable :
le §10.3 s'appelle « Vocabulaire **figé** ». **Aucune ligne de §14 ne nomme la cohérence lexicale.**

La ligne réellement servie est **D3** : `docs/guide/page-creer-la-page-la-meute.md` imprimait
« Aujourd'hui il y en a **deux** — **Accueil** et **Les portées** », phrase que ce commit rendait
fausse. Elle est corrigée dans le même commit.

---

## Ce que `mtb-core` émet

`MTB\Core\Blocks\LienDeRecours\destination( string $cible ): ?array` — **signature et forme de retour
inchangées**.

```php
destination( 'accueil' )  → array{ url: string, libelle: 'Accueil' }
destination( 'portees' )  → array{ url: string, libelle: 'Toutes les portées' }   ← SEUL CHANGEMENT
destination( 'meute' )    → array{ url: string, libelle: 'La meute' }
destination( <autre> )    → null
```

| `cible` | Adresse, calculée au rendu | Libellé fourni par le serveur | Autorité du libellé |
|---|---|---|---|
| `accueil` | `home_url( '/' )` | **Accueil** | §9.5 — **pas d'entrée au §10.3**, et le document le dit |
| `portees` | `get_post_type_archive_link( 'mtb_portee' )` | **Toutes les portées** | §10.3 |
| `meute` | `get_page_by_path( 'la-meute' )` + `get_permalink()` | **La meute** | §10.3 |

La chaîne retenue fait **18 caractères, 19 octets UTF-8** ; son seul octet non-ASCII est `é` = U+00E9 =
`C3 A9` ; ses deux espaces sont des U+0020 ordinaires. **Les trois sites d'appel du dépôt portent
désormais la même séquence d'octets**, vérifié position par position contre `liste-portees`.

## Balisage — inchangé à l'octet

```html
<li class="mtb-lien-de-recours"><a href="…">Libellé</a></li>
```

Une seule classe, un `<a>` nu, aucun attribut de style, aucun crochet neuf. Le `<ul>` d'enveloppe hors
liste (garde de #39) reste **nu**. La phrase de `base.css` — « ce composant émet
`<li class="mtb-lien-de-recours"><a href>` » — **reste vraie** : #43 ne touche que le nœud texte à
l'intérieur du `<a>`.

## États spéciaux — aucun n'est modifié

| État | Émis par le serveur | Rendu par le thème |
| `destination_absente` | Chaîne vide | Rien : pas de `<li>` vide, pas de lien mort, pas de puce orpheline. Quatre cas : archive absente · page inexistante · page non publiée · page protégée |
| `page_protegee` | La page « La meute » protégée n'est **jamais** liée | Aucun contenu protégé ne fuit par ce composant |
| `hors_liste` | `<ul>` nu injecté autour du `<li>` (#39) | Aucune classe, aucun crochet |
| Nombre de liens | **Jusqu'à trois, jamais garantis trois** — la production en rend **deux** (T30) | `:last-child` est juste ; `:nth-child(3)` mentirait |

## Chaînes fournies par le serveur

« Accueil » · « **Toutes les portées** » · « La meute ». Le thème les **imprime** et ne les compose
jamais.

## Interdits

1. Le thème n'écrit **jamais** un de ces trois libellés en dur, dans un gabarit, un motif, un `.html`
   ou une feuille de style.
2. Le thème ne réécrit **jamais** « Toutes les portées » en « Voir toutes les portées » — interdit par
   `docs/contracts/issue-13.md`, contrat gelé, et confirmé ici.
3. **Le `h1` de `wp-content/themes/mtb/templates/archive-mtb_portee.html` ne s'aligne pas sur ce
   libellé.** C'est un **titre de page** ; §10.3 ne régit que des libellés de **lien**. Il reste
   « Les portées », **délibérément**. Le guide le nomme d'ailleurs ainsi à l'éleveuse.
4. **Les libellés d'administration du type Portée ne s'alignent pas non plus**, ni dans l'autre sens :
   leur coïncidence actuelle avec « Toutes les portées » est **fortuite**, ce sont des entrées de menu
   du cœur.
5. Le thème n'interroge jamais la base pour reconstruire un lien de recours ni pour décider quelle
   destination existe.
6. Personne ne compte sur trois liens : **deux est un état conforme**.
7. Ces liens ne se ciblent pas par leur texte (`:has`, contenu, `content:`) mais par la classe
   `mtb-lien-de-recours`.
8. **Le libellé ne se mutualise pas** dans une fonction ou une constante partagée. Ce n'est pas une
   donnée, c'est une constante de vocabulaire fixée par un document : **on ne l'indirecte pas, on la
   pointe.** Quatre sites d'appel sur trois modules sans autre rapport, un §10.3 qui change au plus
   une fois l'an, et une duplication qui n'a **jamais divergé** — elle a été *héritée* divergente, ce
   qui n'est pas la même panne. La bonne mutualisation existe déjà, et c'est le document.

---

## Arbitrages

| # | Désaccord | Décision | Raison |
|---|---|---|---|
| 1 | §9.5 (« Les portées ») contre §10.3 (« Toutes les portées »), §9.5 étant **plus récent** | **§10.3** | La révision 1.2 a **décliné** cette juridiction par écrit : sa valeur est recopiée en avant, pas arbitrée. L'arbitre déclaré l'emporte sur la section qui énumère ; **la date ne départage jamais** |
| 2 | §9.3 (« **Voir** toutes les portées ») | Aligné, **M3 payée** | Aucun émetteur du dépôt ne rendait cette chaîne. Dette d'un contrat **gelé** (#13) restée impayée depuis le lot 5 ; même destination, même arbitre, zéro ligne de code |
| 3 | Le code changeait-il, ou le document ? | **Les deux** | `liste-portees` était déjà juste, `lien-de-recours` non. Corriger dans `mtb-core` répare les **trois** gabarits d'un coup — ce qui prouve que le libellé appartient au serveur |
| 4 | Étendre l'empreinte à §9.5 | **Oui**, remonté au lead, appliqué sans réponse | Laisser §9.5 nommer « Les portées » pendant que le code rend autre chose fabriquait une **péremption neuve, par ce commit même**, dans l'unique section normative de ces trois écrans. Précédent du dépôt : les révisions 1.1 et 1.2 ont **chacune** amendé leur section, l'en-tête et le journal, et **1.2 a amendé §9.5 elle-même**. C'est la décision 58 appliquée à la section voisine |
| 5 | Ajouter « Vers l'accueil \| Accueil » au tableau du §10.3, par symétrie | **Non** | Personne n'a contesté ce mot ; l'étendre à l'arbitre pour la seule symétrie est une extension gratuite. Le §9.5 amendé **dit désormais d'où « Accueil » vient**, ce qui referme le trou sans ligne de tableau |
| 6 | Le contre-argument sémantique contre « toutes » | **Écarté, mais réel — et consigné** | « Toutes » est **contrastif** : dans `liste-portees` il s'oppose aux cinq affichées, et le mot porte une information. Sur une page introuvable il n'y a aucune liste tronquée en regard, donc plus de référent. **Ce coût est accepté** pour n'avoir qu'un nom par destination — et il est surclassé par l'arbitrage 7 |
| 7 | Faut-il craindre la collision avec l'entrée d'administration « Toutes les portées » ? | **C'est un argument POUR** | `includes/content/portee/` pose aussi `archives` à « Toutes les portées », **libellé par défaut d'une archive ajoutée à un menu de navigation**. Or les trois gabarits rendent l'en-tête, qui porte la navigation. Le jour où l'éleveuse ajoute l'index à son menu — un geste que le guide lui apprend —, **le menu et la sortie de secours seraient sur le même écran, vers la même adresse, sous deux noms**. Aujourd'hui la divergence est entre écrans ; demain elle serait **dans** un écran, **sans qu'aucune chaîne intervienne**. Retenir « Toutes les portées » la **prévient** |
| 8 | S'aligner sur « Les portées » | **Mort, et il faut l'écrire** | Exigerait `liste-portees` (hors empreinte), l'**inversion** de l'arbitrage 7 d'un contrat gelé, la réécriture d'un titre de section du guide, et **la reprise de deux captures livrées**. Deux promesses d'image cassées pour rien |

---

## Ce qui a été vérifié, et comment

- **Aucune capture du guide n'est à reprendre.** Les onze captures d'état vide ont été contrôlées :
  **aucune** ne montre la page introuvable, la recherche vide ni l'index vide.
  `liste-portees-etat-vide.png` montre le **cadre du composant dans l'éditeur**, pas les liens de
  recours. Coût image de cette issue : **nul**.
- **Les deux littéraux sont identiques à l'octet**, prouvé par un motif défini une seule fois : un même
  motif littéral ne peut pas matcher deux séquences d'octets différentes. Zéro U+00A0, zéro U+202F,
  zéro U+FEFF, zéro accent combinant, aucune espace finale.
- **Aucun BOM** en tête des cinq fichiers du module ; **zéro fin de ligne LF isolée** dans les deux
  fichiers modifiés (283/283 et 54/54) — la forme CRLF du dépôt est préservée (dette T49).
- **`block.json` a un `git diff` vide** : sa ligne 18 est citée par le contrat gelé
  `docs/contracts/issue-17.md`, et rien ne l'a décalée.
- **Syntaxe** : `php -l` sur `rendu.php` et `render.php`, `node --check` sur `editeur.js` — aucun
  binaire `php` sur l'hôte, exécuté dans le conteneur WordPress en marche.
- **Les affirmations des commentaires ont été relues contre le code qu'elles citent**, y compris
  `liste-portees` et l'entrée 1.4 du journal.

## Ce qui n'a **pas** été vérifié, et qu'il ne faut pas présenter autrement

**Aucun écran n'a été rendu dans un navigateur.** Que les trois gabarits affichent bien la nouvelle
chaîne au visiteur n'est **pas** prouvé par cette chaîne : le chemin est court et lisible (un littéral,
`esc_html()`, `printf()`, et les trois gabarits posent le même `{"cible":"portees"}`), mais c'est un
raisonnement, pas une mesure. **À vérifier à la passe d'intégration du lot**, sur une page introuvable,
sur `/?s=<terme sans résultat>` et sur l'état vide de l'index.

**D7 n'est pas vérifiée non plus.** Le libellé passe de 11 à 18 caractères ; aucune règle CSS ne
dimensionne sur le texte et `MASTER.md` §7.7 pose `overflow-wrap: anywhere` à la racine, donc aucun
débordement n'est attendu à 360 px — mais **aucun rendu n'a été fait**, à aucune largeur.

---

## Dettes ouvertes par cette issue — à inscrire par `/lead-mtb`

Le registre de `docs/ETAT.md` monte à **T79**. Les numéros ci-dessous sont donc **T80 à T83**.

**T80 — Deux textes du module `lien-de-recours` n'énumèrent que deux des trois écrans qu'il sert.**
`block.json` décrit « les pages "introuvable" et "résultats de recherche" », et `bootstrap.php` écrit
« posé une fois pour toutes dans "404.html" et "search.html" ». Depuis #41 (lot 11), le bloc est aussi
posé sur l'**état vide de `index.html`** — vérifié, il est dans les **trois** gabarits.
`MASTER.md` §9.5 nomme ce troisième écran « **Liste sans résultat** » dans son tableau clos, et c'est
le libellé que le correctif devrait reprendre. **Portée faible** : `inserter: false`, et l'éleveuse
n'ouvre pas l'éditeur de site — seul un développeur lit ces textes. **Non corrigé délibérément** :
`block.json` est cité par le contrat gelé `issue-17.md` pour sa ligne 18, et #43 s'est interdit d'en
déplacer une ligne. *Relevée au lot 14, imputable à #41.*

**T81 — `MTB_CORE_VERSION` est figé à `0.1.0` et rien ne l'incrémente.** `mtb-core.php` définit la
constante ; tous les `wp_register_script()` de l'extension la passent en version. L'URL servie reste
`?ver=0.1.0` d'une livraison à l'autre : un navigateur ayant déjà chargé un `editeur.js` peut afficher
un **aperçu périmé**. **Constaté à #43**, qui modifie `lien-de-recours/editeur.js` sans changer l'URL
de ce fichier. **Sans effet sur le livrable ni sur les données** : `editorScript` n'est jamais servi au
visiteur, le bloc n'est pas insérable, l'éleveuse n'accède pas à l'éditeur de site, et `save()` rend
`null` — un aperçu périmé ne peut rien figer dans un gabarit. Le rendu public est calculé à chaque
requête et est correct immédiatement. **La dette est générale**, elle vaut pour les onze `editeur.js`
du catalogue. Remède à arbitrer : incrémenter la constante à chaque livraison touchant un actif, ou
dériver la version d'un `filemtime()` — la seconde supprime la discipline humaine mais ajoute un accès
disque par actif et par écran d'administration. *Relevée au lot 14.*

**T82 — L'en-tête de `MASTER.md` renvoie au mauvais numéro de section pour son propre journal.** La
puce « Historique » écrit qu'une révision « ajoute une entrée datée au **§15** » ; le journal des
révisions est le **§16**, le §15 portant les questions remontées. **Non corrigé par #43** : le défaut
n'est pas causé par ce commit, et la règle du lot est de rapporter les divergences préexistantes plutôt
que de les corriger. Un caractère. *Relevée au lot 14.*

**T83 — Des contrats gelés enregistrent « Les portées » comme libellé du lien de recours.**
`docs/contracts/issue-16.md` (tableau `cible → libellé`, et sa liste des chaînes françaises) et
`docs/contracts/issue-18.md` (« Les trois liens du §9.5 sont Accueil, Les portées, La meute »). **Non
modifiés — ce sont des contrats gelés**, et le précédent du dépôt est explicite (T59, T60 : « contrat
gelé, délibérément non modifié »). La supersession est déclarée **ici**, en tête. **Un point mérite
d'être vu** : `issue-16.md` justifie le `h1` de l'archive par « aligné sur le libellé de recours de
§9.5 ». **Le `h1` reste juste, mais sa raison écrite ne tient plus** — §9.5 ne dit plus « Les portées ».
Une chaîne future qui relira cette justification pourrait en conclure qu'il faut aligner le `h1` : c'est
exactement contre cela que l'interdit 3 ci-dessus et la note du §10.3 sont écrits. *Relevée au lot 14.*

**Dette voisine, ni payée ni aggravée** : **T75** (`issue-16.md` décrit le rendu de
`mtb/lien-de-recours` comme s'il n'en existait qu'une forme) et **T79** (`MASTER.md` §9.1 écrit « les
**dix** composants du catalogue », décompte périmé d'une unité) — hors empreinte, non touchées.

## Observation, à ne pas transformer en dette sans arbitrage

La règle de rédaction retenue ici — **le document nomme les fichiers, le code nomme la section, ni l'un
ni l'autre ne nomme un numéro de ligne** — est respectée par tout ce que #43 ajoute. Mais `MASTER.md`
**cite déjà** des numéros de ligne de feuilles de style ailleurs (§7.7 et §9.5). La règle **ne
rétroagit pas** et #43 n'a rien corrigé de tel. Si le projet veut la rendre générale, c'est une
décision de `/lead-mtb`, pas un effet de bord de cette issue.
