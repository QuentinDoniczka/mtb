# Contrat d'interface — Issue #40 — Introduire une étape de minification du CSS (T52)

**Gelé le 2026-08-31.** Lot 14 (#36, #40, #43). HEAD de départ : `e8a35f4`.
Empreinte disjointe des deux chaînes sœurs : aucune ne touche le thème.

Ce contrat gèle une issue **qui ne touche pas `mtb-core`**. Il n'y a donc pas de contrat front↔back au
sens habituel : il n'y a qu'un côté. Ce que ce document gèle à la place, c'est la **frontière entre
l'outil hors ligne qui écrit les artefacts et le code du thème qui décide de les servir** — deux
morceaux qui doivent s'accorder **octet pour octet** sur une empreinte, et dont la divergence serait
**silencieuse**.

---

## 1. La porte de sortie était ouverte, elle s'est refermée sur une mesure

L'issue s'auto-déclare non bloquante. J'avais posé, **avant de mesurer**, le seuil qui aurait fermé T52
sans code : *gain réseau sur l'Accueil < 10 000 o **et** aucune feuille de blocs supplémentaire ne
bascule en incorporation*.

**Les deux branches sont franchies, très largement.** Mesuré sur la pile, `mod_deflate` niveau 6
**vérifié octet pour octet** contre `gzencode($s, 6)` (les corps servis rendent 3 740 / 13 151 / 10 478 /
16 504 / 10 573 o, exactement les valeurs calculées) :

> **Accueil : 66 553 o → 10 729 o de réseau (HTML + CSS), soit −55 824 o et −83,9 %.**
> **Et −2 requêtes HTTP bloquantes** (5 `<link>` → 3).

Décomposition, parce qu'elle commande tout le reste du contrat :

| | réseau gz6 | delta |
|---|---:|---|
| A — aujourd'hui | 66 553 | — |
| B — commentaires retirés seulement | 11 488 | **−55 065** |
| C — B + incorporation automatique du cœur | **10 729** | **−759** |

**98,6 % du gain vient du retrait des commentaires ; 1,4 % du basculement d'incorporation.** Le
basculement vaut pour ses **2 requêtes bloquantes** et pour la **marge de budget** qu'il rend, jamais
pour ses octets.

**La prémisse du brainstorm était fausse, et c'est la mesure qui l'a dit.** On tenait pour acquis que
« gzip mange déjà les commentaires » et que le gain réseau serait marginal. Sur la somme des 15
feuilles, les commentaires pèsent **82,7 % des octets bruts** (259 582 → 44 823) et **88,0 % des octets
gzippés** (101 866 → 12 275). Gzip ne les mange pas : il les compresse à 30 % et les expédie quand
même. **Une estimation qui décide est le motif d'échec des lots 11 et 12 ; elle a de nouveau été
prise en défaut ici, dans le sens favorable.**

---

## 2. Les cinq arbitrages du lead

### A1 — Le seuil d'incorporation est **40 000 o**, pas 20 000. Le plan se trompait, une source du dépôt aussi

Le plan front raisonne partout sur 20 000 o. **C'est périmé.** Cité depuis le cœur réellement installé
(WordPress 6.9, `wp-includes/version.php:19`) :

```
wp-includes/script-loader.php:3062   $total_inline_limit = 40000;
wp-includes/script-loader.php:3067   * @since 6.9.0 The default limit increased from 20K to 40K.
wp-includes/script-loader.php:3071   apply_filters( 'styles_inline_size_limit', $total_inline_limit );
wp-includes/script-loader.php:3098   usort( … )        // tri par taille CROISSANTE
wp-includes/script-loader.php:3117   if ( $total + $style['size'] > $limite )
wp-includes/script-loader.php:3118       break;        // et NON `continue`
wp-includes/script-loader.php:3141   $total += (int) $style['size'];
```

Trois précisions que la seule ligne 3062 ne donne pas, et qui comptent :

1. **C'est un `break`, pas un `continue`** (`:3118`) : dès qu'une feuille ne rentre pas, **toutes les
   suivantes sont abandonnées**.
2. **Le budget est partagé avec les feuilles de blocs du cœur.** Sur l'Accueil, les candidates du cœur
   (`post-content` 41 o, `group` 117 o, `site-title` 232 o, `paragraph` 655 o, `common.min.css` 3 505 o)
   consomment **4 550 o** avant qu'une feuille MTB ne soit servie.
3. **`tokens`, `base`, `entete-pied` et `fiches` ne sont jamais candidates** : `mtb_mettre_feuille_en_file()`
   appelle `wp_enqueue_style()` **sans clé `path`**. Elles resteront des `<link>` quoi qu'il arrive.
   **Seules les 10 feuilles de blocs** passent par `wp_enqueue_block_style()` avec `'path'`.

**Une source du dépôt portait le chiffre périmé** : `assets/css/blocs/mtb-bandeau-ouverture.css:153`
écrivait « `styles_inline_size_limit` (20 000 octets par défaut) ». C'était vrai avant WP 6.9. Le
raisonnement qui l'entoure — l'interdiction des chevrons dans les commentaires de cette feuille, parce
qu'une feuille incorporée met ses commentaires dans le document — **reste entièrement juste**, et il
devient même plus fort : après #40 cette feuille **est** incorporée sur l'Accueil.

**Décision : le chiffre est corrigé, sous trois conditions strictes** (voir A5).

**Amendement du 2026-08-31, après retour de `dev-front-mtb`.** L'agent a refusé la correction en
constatant que la condition (2) se déclenche : `grep -rn "mtb-bandeau-ouverture\.css:1[0-9][0-9]"` rend
**cinq** citations visant une ligne ≥ 145. Le constat est exact ; sa lecture de la condition l'est trop.
**Les cinq citations vivent toutes dans ce contrat, et toutes visent `:153`** — que la correction laisse
à `:153`, puisque la condition (1) impose un nombre de lignes identique. Le but de la condition (2) est
d'empêcher qu'un **décalage de lignes** périme une citation `fichier:ligne` ailleurs dans le dépôt ;
aucune citation n'est ici périmée par un décalage, il n'y en a pas. La condition (2) est donc
**re-gelée en : aucune citation `fichier:ligne` visant une ligne ≥ 145 hors de
`docs/contracts/issue-40.md`**. L'objection de fond de l'agent — que la phrase ci-dessus, écrite au
présent, deviendrait fausse — était **juste**, et c'est pourquoi elle est passée au passé dans le même
geste.

### A2 — La porte de sortie est fermée : on livre

Voir §1. L'issue est exécutée.

### A3 — `wp-content/themes/mtb/functions.php` entre dans l'empreinte de #40

Borné à trois endroits :

- **`:19-38`** — `mtb_mettre_feuille_en_file()` **et son docbloc** (`:24` affirme « La version vaut
  `filemtime()` », qui devient faux) ;
- **`:194-252`** — la boucle de blocs (`src`, `path`, `ver`) ;
- **`:6-12`** — l'amendement du docbloc de tête (voir §7).

**Élargissement d'empreinte déclaré, et la condition exacte qui l'autorise.** `functions.php` est hors
de l'empreinte que l'issue #40 déclare (`assets/css/**`, `docker/**` ou `Makefile`). L'orchestrateur
l'a accordé, après avoir vérifié **le texte** de la décision 9 plutôt que sa réputation.

**Ce que dit la décision 9 (`docs/ETAT.md:962`)** : « Aucun index central à éditer à la main dans
`mtb-core` […] **C'est la condition technique du parallélisme.** Un index de blocs édité à la main
serait touché par presque toute issue visuelle et ferait entrer en collision des chaînes pourtant
censées être disjointes. Conséquence : un bloc = un dossier auto-enregistré ; `functions.php` **n'est
touché que par #2 puis #18, jamais dans le même lot**. »

**C'est une contrainte de collision, pas de sacralité.** Elle ne dit nulle part que le fichier ne doit
plus être rouvert : elle interdit que **deux chaînes d'un même lot** le rouvrent, et elle **prévoit
explicitement** qu'il le soit, en nommant #2 puis #18. La formule « conçu pour ne plus être rouvert »
venait de **l'ancien en-tête de ce fichier même**, pas de la décision — elle en disait moins.
**#40 applique la condition de la décision 9 ; elle ne la révise pas.**

**La condition est vérifiée pour le lot 14, comme un fait et non comme une permission de principe** :
#36 vit dans `mtb-core/includes/fields/portee/**` (commit `fe7f068`) ; #43 dans
`design-system/MASTER.md`, `mtb-core/includes/blocks/lien-de-recours/**` et une fiche du guide
(commits `7d82c0a`, `e0a735b`). **Aucune des deux n'entre dans `wp-content/themes/mtb/`.** Le
recouvrement est **nul**, et il n'existe aucun autre point d'intervention côté thème sans violer la
frontière thème/`mtb-core`.

**Conséquence pour le lot suivant** : `functions.php` a été rouvert par #40. Toute issue future qui
voudrait le rouvrir doit vérifier la même condition — pas de chaîne sœur du même lot sur ce fichier.
Une empreinte élargie sans trace écrite est une empreinte fausse pour le lot suivant ; c'est le motif
de ce paragraphe.

**Ajout borné hors des trois plages** : `functions.php:178` écrit « Les **deux** feuilles servies au
visiteur » ; il y en a **trois** (`:180-182`). Un mot, dans le docbloc immédiatement voisin de ce que
#40 rouvre. Le laisser faux dans un fichier qu'on rouvre serait exactement le motif « prose que le code
dément » relevé en revue aux lots 12 et 13.

### A4 — « Rendu identique au pixel » est réénoncé, parce qu'il est indélivrable tel qu'écrit

Aucun navigateur automatisé n'existe sur cette pile (constat du lot 13, `docs/ETAT.md`). La tâche 2 de
l'issue est donc **réénoncée** :

> **Le flux de déclarations reçu par le navigateur est inchangé — prouvé par reconstruction, avec
> contrôles positifs qui doivent échouer.**

Ce qui est prouvé, ce qui ne l'est pas : §9. **Aucun compte rendu de cette chaîne ne dira « rendu
identique au pixel vérifié ».**

### A5 — Ce qui est refusé, et pourquoi

- **`.gitattributes` : refusé dans #40.** Le plan le recommande (`assets/css/**/*.css text eol=lf`) pour
  supprimer la divergence CRLF/LF. **Mais poser ce motif renormalise les 15 sources dans la copie de
  travail** — c'est-à-dire touche exactement les fichiers que #40 promet de ne pas toucher (R8). Et la
  correction n'en dépend pas : la **forme canonique** du §4 rend l'empreinte insensible aux fins de
  ligne, par construction. Reporté en dette, pas exécuté.
- **`CLAUDE.md` : refusé.** La règle de processus que #40 crée (« toute modification d'une feuille sous
  `assets/css/` s'accompagne d'un `make css` dans le même commit ») a son domicile durable dans la
  section « Conventions » de `CLAUDE.md`, et `make css-check` a le sien dans la liste de
  `test-integration-mtb`. Les deux sont **hors empreinte** et relèvent de l'orchestrateur. La règle est
  écrite dans `docs/docker.md`, dans le `Makefile` et ici ; **le routage est remonté, pas exécuté**.
- **`dev-ux-mtb` : non lancé.** #40 n'écrit **pas un sélecteur, pas une déclaration, pas un jeton**.
  L'attestation indépendante qu'il aurait rendue (sources intactes, 11 sites de `calc()` intacts dans
  leur artefact) est transférée à `refacto-mtb`, qui tourne de toute façon.
- **Un avertissement d'administration sur artefact périmé : refusé.** Ce serait un message que
  l'éleveuse voit et ne peut pas traiter — la règle d'or interdit qu'un écran lui demande d'ouvrir un
  fichier — et il déclencherait **D3** pour un message inactionnable.
- **Un filtre `styles_inline_size_limit` : refusé.** #40 ne re-litige pas l'heuristique du cœur.
- **La correction de `mtb-bandeau-ouverture.css:153` est autorisée sous trois conditions cumulatives**,
  et **abandonnée si l'une tombe** : (1) le **nombre total de lignes du fichier reste identique**
  (586) ; (2) `grep -rn "mtb-bandeau-ouverture\.css:1[0-9][0-9]"` ne rend **aucune** citation
  `fichier:ligne` visant une ligne ≥ 145 **hors de `docs/contracts/issue-40.md`** (condition re-gelée le
  2026-08-31, voir A1) ; (3) seul le chiffre et sa provenance changent, le raisonnement sur les chevrons
  reste mot pour mot.
  **Provenance à inscrire si elle tient sans changer le nombre de lignes** : « 40 000 octets par défaut
  depuis WordPress 6.9 ». Si elle ne tient pas, la correction se réduit à `20 000` → `40 000` et la
  provenance reste consignée ici seulement — la valeur juste sans provenance vaut mieux qu'une valeur
  fausse, mais moins qu'une valeur juste dont on sait d'où elle vient (leçon de #44, lot 13).

  **Extension A5-bis, gelée le 2026-08-31 après la passe de refacto.** Deux autres sources portent le
  même chiffre périmé, et il y est **plus trompeur encore**, puisqu'il sert de justification à la
  discipline de poids de leur feuille :
  `assets/css/blocs/mtb-tableau-resultats.css:5` et `assets/css/blocs/mtb-formulaire-contact.css:5`
  écrivent « FEUILLE INCORPORÉE : sous 20 000 o le cœur l'écrit EN LIGNE dans le HTML ».
  **Les deux sont corrigées, aux mêmes trois conditions que `mtb-bandeau-ouverture.css:153`** — nombre
  total de lignes du fichier identique, aucune citation `fichier:ligne` visant la zone touchée hors
  `docs/contracts/issue-40.md`, et seuls le chiffre et sa provenance changent. Leurs deux artefacts sont
  **régénérés** dans le même geste (`make css`), sans quoi ils deviendraient périmés — donc inertes, donc
  une perte de poids silencieuse. Motif de l'extension : laisser dans deux feuilles de l'empreinte un
  chiffre qui commande le mécanisme même de #40, le jour où on le corrige dans une troisième, serait la
  définition du travail à moitié fait.

---

## 3. Ce que l'extension `mtb-core` doit livrer

**Rien.** Aucune fonction de lecture, aucun bloc, aucune chaîne serveur n'est demandé, et pas un octet
de `wp-content/plugins/mtb-core/**` ne bouge.

Le seul couplage est **passif et déjà en place** : la boucle `functions.php:194-252` dérive le nom de
feuille du nom de bloc enregistré. Un bloc nouveau de `mtb-core` fera donc naître une source de feuille
dans le thème — **et son artefact devra être généré par `make css`**, comme les 14 autres.

---

## 4. Le marqueur d'empreinte — gelé, octet par octet

C'est le point dur de l'issue, et la proposition initiale était fausse.

**Pourquoi `filemtime(artefact) >= filemtime(source)` est disqualifié.** Git n'enregistre aucune date
de modification ; à un `clone` ou un `checkout`, chaque fichier reçoit la date de l'opération. Et
l'ordre d'écriture **n'est pas aléatoire** : git écrit dans l'ordre lexicographique de l'index, où
`base.css` précède `base.min.css` (`.` puis `c` = 0x63 contre `m` = 0x6D). Avec une granularité de
`mtime` d'une seconde, les deux dates sont le plus souvent **égales**, et le `>=` tranche pour
l'artefact. **Le test ne dégénère pas en hasard : il dégénère en « toujours l'artefact ».** Un dépôt de
production ne servirait donc jamais sa source, quel que soit l'état réel de fraîcheur. Un envoi FTP
produit le même effet.

**Pourquoi un hachage naïf est disqualifié aussi.** Le dépôt tourne en `core.autocrlf=true` et
`.gitattributes` ne couvre pas `wp-content/**`. Mesuré : `base.css` fait **45 914 o dans git** (LF) et
**46 927 o sur disque** (CRLF). Un marqueur écrit sur une machine Windows avec `filesize()` et
`sha256(file_get_contents())` bruts **ne correspondrait jamais** aux valeurs LF de la production :
l'artefact serait rejeté **partout en production, systématiquement**, et #40 rendrait exactement zéro
octet là où elle vise à en rendre — sans que rien ne le signale.

### Forme canonique

> La **forme canonique** d'un fichier, ce sont ses octets avec `\r\n` **et** `\r` isolés remplacés par
> `\n`. **Rien d'autre.** Aucune autre normalisation : ni espaces, ni casse, ni BOM.

### Empreinte

```
E = substr( hash( 'sha256', "mtb-min/1\n" . $canonique_de_la_source ), 0, 16 )
L = strlen( $canonique_de_la_source )
```

- **`sha256`**, et jamais `xxh3`/`xxh128` : le générateur tourne dans le conteneur (PHP 8.1) mais **le
  vérificateur tourne sur l'hébergement**, dont la version n'est pas tranchée (Q5). `sha256` existe
  depuis PHP 5.1.2. **Deux algorithmes selon la disponibilité serait le pire choix** : l'empreinte
  dépendrait du PHP qui l'a écrite.
- **16 hexadécimaux (64 bits)** : ce n'est pas une frontière de sécurité, c'est un détecteur de
  modification accidentelle.
- **`"mtb-min/1\n"` — la révision du générateur, dans l'entrée du hachage.** Si la transformation change
  un jour (correction d'un défaut du dépouilleur), les artefacts changent d'octets alors que les
  sources n'ont pas bougé ; sans révision, **une correction de bug ne serait jamais distribuée**. En la
  mettant dans l'entrée du hachage, un changement de révision périme **tous** les artefacts d'un coup —
  conduite voulue : tant qu'ils ne sont pas régénérés, les sources sont servies.
  **Couplage assumé** : ce littéral est écrit à deux endroits (le générateur et `functions.php`). Une
  divergence **de ce littéral-là** dégrade vers « la source est servie » — les empreintes cessant de
  concorder — et non vers « un artefact accepté à tort ». Chacun des deux **porte un commentaire
  nommant son jumeau**. **La portée de cette phrase est le jumeau, et rien d'autre** : elle ne dit
  rien de la corruption d'un artefact, que les conditions 1 à 5 ne voyaient pas — voir « La sixième
  condition » plus bas, où une formule trop large de ce contrat a été mesurée fausse.

### Marqueur, en tête d'artefact

```
/*!mtb-src:<L>:<E>:<A>:<F>*/\n
```

| Position | Contenu |
|---|---|
| octet 0 | `/*!mtb-src:` (`2F 2A 21 6D 74 62 2D 73 72 63 3A`) |
| suite | `<L>`, longueur de la forme canonique de **la source**, `[1-9][0-9]{0,9}` |
| suite | `:` · `<E>`, empreinte de la source, **exactement** `[0-9a-f]{16}` |
| suite | `:` · `<A>`, longueur de la forme canonique du **corps de l'artefact**, `[1-9][0-9]{0,9}` |
| suite | `:` · `<F>`, empreinte de ce corps, **exactement** `[0-9a-f]{16}` |
| suite | `*/` (`2A 2F`) |
| suite | `\n` — **hors marqueur au sens de la vérification** |

`<F> = substr( hash( 'sha256', "mtb-min/1\n" . $corps_canonique ), 0, 16 )`, même révision de
générateur que `<E>`.

**Aucune circularité** : le corps est tout ce qui suit la ligne du marqueur, donc `<A>` et `<F>`
portent sur un texte que le marqueur ne modifie pas. Vérifié par reconstruction, et non supposé — un
cas de la table P6 le rejoue à chaque exécution.

Longueur du marqueur : **50 à 68 octets** (borne arithmétique : `11 + 10 + 1 + 16 + 1 + 10 + 1 + 16 + 2`).
Le `/*!` porte la convention universelle « commentaire à préserver ».

**Lecture à l'exécution** : `file_get_contents( $artefact, false, null, 0, 128 )` puis
`preg_match( '#^/\*!mtb-src:([1-9][0-9]{0,9}):([0-9a-f]{16}):([1-9][0-9]{0,9}):([0-9a-f]{16})\*/#', $tete, $m )`.
**128 > 68**, marge de 60 octets. **Ancrée à l'octet 0** — aucun espace, aucun BOM, aucune ligne avant.

**Cette borne est un invariant vérifié par la machine, pas par la relecture** : `MTB_MIN_MARQUEUR_MAX`
et `MTB_MIN_BORNE_LECTURE` sont des constantes de l'outil, et **P6 refuse si `BORNE <= MAX`**. Une
borne trop courte tronquerait le marqueur et ferait rejeter **tous** les artefacts en silence — c'est
le piège le plus probable de toute évolution du format.

### Règle de bascule — sans ambiguïté

> **L'artefact `<X>.min.css` est servi si et seulement si les cinq conditions sont toutes vraies :**
>
> 1. la source `<X>.css` existe et `file_get_contents()` y réussit ;
> 2. l'artefact `<X>.min.css` existe et `file_get_contents( …, 0, 64 )` y réussit ;
> 3. les octets lus correspondent, **ancrés à l'octet 0**, à
>    `#^/\*!mtb-src:([1-9][0-9]{0,9}):([0-9a-f]{16})\*/#` ;
> 4. le groupe 1 vaut **exactement** `L` ;
> 5. le groupe 2 vaut **exactement** `E`.
>
> **6.** le **corps** de l'artefact, sous forme canonique, a **exactement** la longueur `<A>` et
>    l'empreinte `<F>`.
>
> **Dans tous les autres cas — absent, illisible, vide, tronqué en tête, tronqué en queue, corrompu à
> longueur constante, sans marqueur, marqueur mal formé, longueur discordante, empreinte discordante,
> source illisible — la source `<X>.css` est servie**, sans erreur PHP, sans avertissement, sans
> notice, sans écriture au journal.
>
> **`src` et, pour une feuille de bloc, `path` désignent toujours le même fichier.** Jamais l'un sur
> l'artefact et l'autre sur la source.

**Aucun de ces états n'écrit au journal des diagnostics.** Un artefact périmé est un état **normal**
entre le moment où une chaîne modifie une source et celui où elle joue `make css` ; en faire une notice
remplirait `wp-content/debug.log` et ruinerait la mesure « aucune notice » dont dépendent plusieurs
recettes gelées (`docs/docker.md`, décision 29).

**La dégradation est conservatrice** : la source étant toujours servie à sa place, une feuille écartée
ne produit qu'une régression de poids — le site revient exactement à son état d'avant #40. Elle reste
observable à tout instant, sans outillage, par le **nom du fichier dans le source de la page**.

### La sixième condition — ce que le contrat affirmait à tort, et comment on le sait

**Cette phrase, dans sa version du gel initial, était fausse, et c'est une mesure qui l'a établi.**
Le contrat écrivait « toute divergence dégrade vers *la source est servie*, **jamais** vers *un
artefact accepté à tort* » et « la panne est conservatrice — **jamais une régression visuelle** ». La
passe d'intégration l'a réfuté sur **17 avaries fabriquées**, dont une passait :

> **Artefact tronqué en queue, marqueur intact.** Attendu : la source est servie. **Obtenu :
> l'artefact est servi** — `base.min.css` amputé à **5 307 o au lieu de 10 579**, **107 déclarations
> perdues sur 255**, page rendue en **200**, aucune notice. Les 16 autres avaries dégradaient bien.

**La cause est celle que le gel initial avait nommée sans en tirer la conséquence : les cinq premières
conditions attestent la SOURCE, jamais l'ARTEFACT.** Un artefact amputé de sa moitié passait les cinq.
Le résultat n'était donc pas une régression de poids mais une **régression visuelle silencieuse** —
l'inverse exact de ce que le contrat promettait, et sur la promesse la plus rassurante du dossier.

**Le scénario n'est pas théorique** : la cible de D9 est un hébergement mutualisé, où l'on dépose par
FTP, où un transfert s'interrompt, où un disque se remplit. Ce contrat écartait d'ailleurs déjà
`filemtime()` en observant qu'« un envoi FTP produit le même effet » — la garde couvrait ce cas pour
la source et pas pour l'artefact.

**Pourquoi `<A>` et `<F>`, et pas un simple `filesize()`** — deux motifs, tous deux mesurés :

1. **Un `filesize()` brut serait discordant partout en production.** L'artefact est écrit en LF et
   rendu en **CRLF** après un `git checkout` (`core.autocrlf=true`) : `base.min.css` passe de 10 602 à
   11 102 o sur disque. Vérifié : avec la forme canonique, **il reste servi**. Avec un `filesize()`
   brut, #40 aurait rendu **zéro octet en production**, silencieusement — exactement le mode de panne
   que l'arbitrage 4 avait écarté pour la source.
2. **Un `filesize()` seul manquerait une corruption à longueur constante.** Rétrécir la garde de
   l'artefact à ce qui a été refusé pour la source (arbitrage 4, voie `filesize`) aurait été
   incohérent. Vérifié : un caractère hexadécimal changé dans `tokens.min.css`, **longueur inchangée**,
   dégrade désormais vers la source.

**Preuve que la borne de lecture n'est pas devenue le nouveau trou.** Le marqueur passe de 31-40 à
**50-68 octets** ; la lecture bornée passe de 64 à **128**. Une borne trop courte tronquerait le
marqueur et ferait rejeter **tous** les artefacts, en silence. `MTB_MIN_MARQUEUR_MAX = 68` et
`MTB_MIN_BORNE_LECTURE = 128` sont des constantes, et **P6 refuse à chaque exécution si
`BORNE <= MAX`** : le piège est devenu une panne bruyante de `make css`.

**Coût mesuré, en octets et non en supposition.** L'artefact est désormais lu en plus de la source :
sur l'Accueil, **218 917 → 254 329 o lus par requête (× 1,16)** et **213 962 → 247 795 o hachés
(× 1,16)**. Nuance qui réduit le supplément réel : les 10 artefacts de blocs portent un `path`, donc
`wp_maybe_inline_styles()` **les lisait déjà** intégralement — le supplément inédit se limite aux 3
feuilles de site, soit **16 973 o**. Le temps **n'est pas mesurable de façon fiable ici** (variance du
montage lié Windows, min 76 ms / max 421 ms sur 40 tirages) ; sur un système de fichiers local au
conteneur, médiane **1,318 → 1,531 ms, soit +0,213 ms par page** — reproductible, mais ne disant rien
de l'hébergement de production.

**Ce que la correction ne coûte pas** : `mtb_feuille_a_servir()` étant en **fin de fichier** depuis le
remède R10, elle grossit de 36 lignes sans en pousser aucune. **La première ligne modifiée est la
975 ; la citation `functions.php:NNN` la plus haute du dépôt vise `:861-869`. Cette passe ne périme
donc pas une seule citation.** C'est le bénéfice direct de R10, et il n'était pas prévu.

### Invariant de nommage — non négociable

> Pour toute source `<dossier>/<nom>.css`, l'artefact est `<dossier>/<nom>.min.css` — **même dossier,
> obligatoirement**. Une source dont le nom se termine par `.min.css` n'est jamais une source.

Ce n'est pas une préférence esthétique. `base.css:41-42` et `:51-52` portent `url("../fonts/…")`,
**relatif à l'emplacement de la feuille**. Un artefact rangé dans `assets/css/min/` renverrait vers
`assets/css/fonts/…`, inexistant — **404 sur les deux polices et retour silencieux à Georgia/Arial**,
que `base.css:59-70` rend **invisible à l'œil** en ajustant métriquement les replis pour qu'ils ne
décalent rien.

### `ver` = l'empreinte

`ver` vaut **`E`** (16 hexadécimaux), artefact servi comme source servie. Elle est **déjà calculée**,
donc gratuite ; elle est **adressée par le contenu**, donc un déploiement qui ne touche pas une feuille
**laisse le cache du visiteur intact** — alors qu'aujourd'hui chaque mise en ligne fait re-télécharger
tout le CSS à tout le monde. C'est ce qui repaie une partie du coût de vérification.

`mtb_mettre_feuille_en_file():24` (« La version vaut `filemtime()` ») **devient faux et doit être
amendé dans le même geste**.

---

## 5. Périmètre — 14 artefacts sur 15 sources

| Source | Artefact | Motif |
|---|---|---|
| `tokens.css`, `base.css`, `entete-pied.css` | **oui** | servies au visiteur par `mtb_feuilles_du_site()` |
| `fiches.css` | **oui** | servie au visiteur par `enveloppe-fiche.php:41` |
| les **10** `blocs/*.css` | **oui** | servies au visiteur par la boucle `:194-252` |
| **`editor.css`** | **non** | n'atteint **jamais** un visiteur (`editor.css:4-5`) ; `add_editor_style()` prend des chemins relatifs résolus par le cœur, donc basculer là exigerait un second mécanisme **hors des plages bornées** ; et livrer un artefact que rien ne sert, ce serait poser dans le dépôt un fichier dont personne ne sait dire à quoi il sert |
| `style.css` (racine) | **non** | ne contient aucune règle, n'est jamais mis en file |

**`assets/css/editor.min.css` ne doit pas exister** — son absence est une vérification (P5).

**Les chemins de l'éditeur restent intégralement sur les sources.** `add_editor_style()` (`:56` et
`:883-884`) n'est pas touché, **et la pré-inscription de `mtb-jetons` à `:214-219` non plus**, bien
qu'elle soit dans l'empreinte : elle est gardée par `is_admin()` et ne sert que la toile de l'éditeur.
La conséquence est délibérée et **elle protège une phrase existante** — `:203-207` affirme que
`tokens.css` entre deux fois dans la toile, « le même fichier, **octet pour octet**, donc impossible à
faire diverger ». Basculer `:218` sur l'artefact **rendrait cette phrase fausse**.

**`fiches.css` est couverte sans qu'un octet de `enveloppe-fiche.php` ne bouge**, parce que la bascule
vit **à l'intérieur** de `mtb_mettre_feuille_en_file()`. C'est l'argument décisif de ce placement :
tout appelant présent ou futur en hérite.

---

## 6. La transformation — gelée, étape par étape

L'entrée est la **forme canonique** (LF seuls) ; la sortie est écrite en **LF**.

1. **Balayage gauche-droite à états** : `CODE`, `CHAÎNE"`, `CHAÎNE'`, `URL_NON_CITÉE`, `COMMENTAIRE`.
   - En `CODE` : `/*` ouvre un commentaire ; `"` et `'` ouvrent une chaîne ; `url(` (insensible à la
     casse, suivi d'espaces optionnels puis d'un caractère autre que `"` ou `'`) ouvre une url non
     citée, close par le `)` correspondant.
   - En chaîne : `\` échappe le caractère suivant ; le guillemet correspondant ferme.
   - En commentaire : **seul** `*/` ferme. CSS n'a pas de commentaires imbriqués.
   - Un `/*` **dans** une chaîne ou une url non citée **n'est pas un commentaire**.
2. **Chaque commentaire est remplacé par exactement un espace (`0x20`), jamais par rien.** C'est la
   règle qui rend **structurellement impossible** la soudure de deux jetons : `margin:0/*x*/auto`
   devient `margin:0 auto`, jamais `margin:0auto`.
3. Puis, ligne à ligne : **rognage à droite** des espaces et tabulations.
4. Puis : une ligne est supprimée (saut de ligne compris) **si et seulement si** elle est devenue vide
   **et** qu'elle a été touchée à l'étape 2. **Les lignes vides préexistantes sont conservées.**
5. **Rien d'autre n'est touché** : aucun rognage à gauche, aucun écrasement d'espaces, aucun `;`
   retiré, aucun changement de casse, aucune réécriture de nombre ni de couleur, aucun réordonnancement.
6. Sortie = marqueur + `\n` + corps. Le corps se termine par `\n`.

**Il n'y a aucune exception `/*!`** : tous les commentaires sont retirés, y compris ceux commençant par
`/*!`. Le marqueur est **écrit par le générateur**, jamais préservé depuis la source. Aucune des 15
feuilles ne porte d'en-tête de licence (la GPL est déclarée dans `style.css`).

### Pourquoi on ne minifie **que** les commentaires

Deux motifs mesurés, pas supposés.

1. **Les espaces ne rendent presque rien après gzip**, alors que les commentaires rendent 88 % des
   octets gzippés (§1). Un minifieur complet paierait tous les risques pour un gain quasi nul.
2. **Le seul écueil réellement armé sur nos feuilles est celui des espaces signifiants.** Relevé sur les
   15 sources : **12 `calc(`, 3 `min(`, 11 `max(`, 10 `clamp(`** réels — dont
   `base.css:182,198,258,459`, `entete-pied.css:84,129,381`, `fiches.css:641`,
   `mtb-derniere-portee.css:33`, `mtb-encart-appel.css:29`, `mtb-bandeau-alerte.css:44`,
   `mtb-formulaire-contact.css:213`. Coller les espaces autour d'un `+` ou d'un `-` y **casse la mise en
   page**. **Un retrait de commentaires n'y touche jamais.**

### Le pré-vol reste dans la chaîne, même si son constat est vert aujourd'hui

Mesuré sur les 15 feuilles : **0 chaîne portant `/*` ou `*/`** ; **0 commentaire collé à un caractère
non blanc** ; **0 commentaire à l'intérieur d'un `calc(`/`clamp(`/`min(`/`max(`** ; **0 `@import`, 0
`@charset`, 0 hack navigateur** (`\9`, `*prop`, `_prop`) ; **1 seul `!important` réel**, dans
`base.css`. Les seules `url()` sont les **4** `../fonts/…` de `base.css`. Les seuls `content:` sont
`""` (×15), `none` et `attr(data-libelle)`.

> **Le remplacement par une espace est donc strictement neutre sur ces 15 fichiers — mais c'est une
> propriété du contenu d'aujourd'hui, pas de la règle.** Le pré-vol reste obligatoire à chaque
> exécution : on ne fait pas confiance à un constat daté.

### Le piège de comptage du lot 13, retrouvé ici — à connaître avant de compter quoi que ce soit

Un compteur naïf sur le fichier entier se trompe sur **8 des 15 feuilles**, dans les deux sens :

- `url(` : **4 réels** contre **9 bruts** — 5 occurrences vivent dans des commentaires.
- `content:` : **17 réels** contre **29 bruts** — deux erreurs cumulées, les occurrences en commentaire
  **et** les faux positifs `justify-content:` / `align-content:` / `…__content:hover`.
  `entete-pied.css` compte **7 en naïf pour 1 réel**.
- `!important` : **1 réel** contre **9 bruts** dans `base.css` seul (8 mentions en commentaire).
- `calc(` : 12 réels contre 16 bruts.

**Vérifier les totaux, pas seulement les manquants.**

---

## 7. États spéciaux

| État | Émis par | Rendu |
|---|---|---|
| `artefact_conforme` | la règle de bascule §4 | l'**artefact** est servi, `ver` = `E` |
| `artefact_absent` | `file_exists()` faux | la **source**, `ver` = `E` ; `make css-check` dit `ABSENT` |
| `artefact_perime` | `L` ou `E` discordant | la **source** ; `make css-check` dit `PÉRIMÉ` |
| `artefact_illisible` | lecture en échec | la **source** |
| `artefact_vide` | 0 octet | la **source** |
| `artefact_marqueur_invalide` | regex non ancrée / mal formée / tronquée en tête | la **source** ; `make css-check` dit `INVALIDE` |
| `artefact_corps_corrompu` | `<A>` ou `<F>` discordant — **tronqué en queue, corrompu à longueur constante, réécrit** | la **source** ; `make css-check` dit `INVALIDE — le marqueur ne décrit plus le corps de l'artefact`. **Ajouté après réfutation par la passe d'intégration** : cet état était auparavant servi, avec 107 déclarations perdues sur 255 |
| `artefact_orphelin` | `*.min.css` sans `*.css` de même racine | **signalé, jamais supprimé automatiquement** |
| `source_illisible` | `file_exists()` faux sur la source | **rien n'est mis en file** — comportement **actuel** de `:33-35`, inchangé |

**Aucun de ces états n'est visible par l'éleveuse, et aucun n'écrit au journal.**

### Amendement du docbloc `functions.php:6-12`

Le texte actuel — « Ce fichier est conçu pour ne plus être rouvert (décision 9 de `docs/ETAT.md`) » —
doit dire trois choses, et rien de plus :

1. **Ce qui reste vrai** : la mise en file des feuilles de bloc est **générique**, il n'y a **aucune
   liste à rallonger**. Déposer `assets/css/blocs/<espace>-<nom>.css` suffit toujours.
   **Et ce que la phrase ne doit surtout pas prétendre** — corrigé le 2026-08-31 sur relecture de
   l'orchestrateur : le docbloc ne doit **pas** présenter « conçu pour ne plus être rouvert » comme
   étant « le vrai sens de la décision 9 ». Cette formule venait de l'ancien en-tête de ce fichier ;
   la décision 9 (`docs/ETAT.md:962`) dit autre chose et dit plus — elle interdit **la collision entre
   deux chaînes d'un même lot**, motive cet interdit par le parallélisme, et **prévoit** que
   `functions.php` soit rouvert (« par #2 puis #18 »). Le docbloc cite donc la décision à sa ligne, en
   donne la vraie raison, et **constate** que la condition est tenue au lot 14 au lieu de l'affirmer.
2. **Ce qui a changé** : #40 a rouvert le fichier en deux points bornés — la bascule **à l'intérieur de
   `mtb_mettre_feuille_en_file()`** (donc héritée par tout appelant, `enveloppe-fiche.php` compris,
   sans qu'il soit touché), et le `src`/`path`/`ver` de la boucle de blocs.
3. **Ce que la promesse de généricité devient** : un composant nouveau n'exige toujours rien ici —
   **mais il exige un `make css`**, sans quoi sa feuille est servie non minifiée (correcte, plus
   lourde), et `make css-check` le dit.

---

## 8. Chaînes fournies par le serveur

**Aucune.** #40 ne compose, ne reformule et n'affiche aucune chaîne de domaine, aucun libellé, aucune
date, aucun nom. La seule chaîne littérale introduite est **`"mtb-min/1\n"`**, la révision du
générateur, qui n'atteint jamais un écran.

**Ce que l'éleveuse voit : rien.** Aucun écran, aucun réglage, aucun bloc, aucun libellé. La toile de
l'éditeur est **inchangée** (§5). **D3 n'est pas déclenchée ; `doc-client-mtb` ne tourne pas sur #40.**
Si un agent de la chaîne introduit malgré tout quoi que ce soit qu'elle voit, il **doit** le remonter :
D3 redeviendrait obligatoire.

---

## 9. Protocole de preuve — et ce qu'il ne prouve pas

### Preuve par reconstruction — `make css-check`, sur les 14 paires

| # | Contrôle | Ce qu'il établit |
|---|---|---|
| **P1** | `dépouiller( canonique(source) ) == canonique( corps(artefact) )`, **octet pour octet** | L'artefact est exactement ce que l'outil produit aujourd'hui. Fraîcheur **et** déterminisme. |
| **P2** | `N(source) == N(corps(artefact))`, où **`N`** retire les commentaires **et** écrase toute suite de blancs en **un seul espace** | Le flux de jetons est identique. **Écraser une suite de blancs en UN espace, ce n'est pas la supprimer** : `calc(1px + 2px)` et `calc(1px+2px)` ont des `N` différents. **P2 est l'oracle direct de l'écueil des espaces signifiants.** |
| **P3** | `corps(artefact)` ne contient **aucune** occurrence de `/*` hors chaîne | Les commentaires ont réellement été retirés. Sans P3, un dépouilleur qui ne ferait rien passerait P1 et P2. |
| **P4** | `taille(artefact) < taille(source)` pour chacune des 14 | Attrape un artefact vide ou deux fichiers intervertis. |
| **P5** | Aucun `*.min.css` orphelin ; `editor.min.css` **absent** | Le périmètre du §5 est celui du disque. |
| **P6** | **Table de cas à sortie attendue écrite à la main**, jouée par `--verifier` | Le seul oracle réellement indépendant. |

**P1 compare des formes canoniques des deux côtés, et ce n'est pas un détail.** Le dépôt tourne en
`core.autocrlf=true` sans `.gitattributes` sur `wp-content/**` : le générateur écrit l'artefact en LF,
mais **un `git checkout` le rendra en CRLF**. Une comparaison d'octets bruts échouerait donc sur tout
dépôt fraîchement cloné — c'est-à-dire en production. Corollaire : le **déterminisme** se vérifie par
`git status` propre après deux `make css` consécutifs, **jamais** par comparaison d'octets sur disque.

Cas obligatoires de **P6** (entrée → sortie attendue) :

- `a{b:c/*x*/d}` → `a{b:c d}` — *l'espace de remplacement, la règle qui sauve `calc()`*
- `a{width:calc(var(--x) + 1px)/*n*/}` → l'espace autour du `+` **intact**
- `a{content:"/*"}` → **inchangé**
- `a{content:'*/'}` → **inchangé**
- `a{background:url(/*.png)}` → **inchangé** — *url non citée contenant `/*`*
- `a{content:"x\\"/*y*/"}` → **inchangé** — *guillemet échappé*
- `/* seul sur sa ligne */\n` → ligne supprimée, saut de ligne compris
- `\n\n` (lignes vides préexistantes) → **conservées**
- `a{b:c}/*fin` → **refus**, sortie 1, aucun fichier écrit

**Réserve d'honnêteté sur P2, à ne pas taire** : `N` est une seconde implémentation avec un objectif
différent, **pas un oracle pleinement indépendant**. Un défaut du balayeur de commentaires partagé par
les deux serait invisible à P2. C'est pourquoi P6 existe.

### Contrôles positifs — le protocole doit savoir échouer

| # | Manipulation | Attendu | Ce qu'il condamne |
|---|---|---|---|
| **CP1** | artefact fabriqué à la main où l'espace autour d'un `+` de `calc()` a été retiré | **P2 échoue**, nommément | prouve que P2 n'est pas un tampon |
| **CP2** | source modifiée **sans changer sa longueur** (`1rem` → `2rem`), non régénérée | la **source** est servie | **condamne la voie `filesize` seule** |
| **CP3** | source modifiée en changeant sa longueur, non régénérée | la source est servie | |
| **CP4** | **un** caractère hexadécimal du marqueur altéré | la source est servie | |
| **CP5** | artefact vidé (0 octet) | la source est servie ; **aucun diagnostic PHP** (`make debug-log-reset` avant, `make debug-log` après) ; aucun `<link>` vers un fichier vide | |
| **CP6** | artefact supprimé | la source est servie | |
| **CP7** | **déploiement Git simulé** : `touch` de **tous** les CSS à la même seconde, puis CP2 rejoué | la source est **encore** servie | **condamne la voie `filemtime`** : rejoué avec sa règle, ce contrôle servirait l'artefact périmé |
| **CP8** | source convertie CRLF→LF (ou l'inverse) **sans autre modification**, non régénérée | l'artefact **reste servi** | **condamne un hachage naïf sur octets bruts** — c'est le contrôle qui prouve que #40 rend un gain **en production**, pas seulement en local |

L'instrument de la sonde a déjà passé quatre contrôles positifs équivalents (`;` retiré → échec ;
`#16241C`→`#16241D` → échec à l'octet 1982 ; `}` retirée → échec ; feuille portant
`content: "/* pas un commentaire */"` → refusée par le pré-vol, 3 occurrences aux offsets 68, 126, 177).
**Ce résultat ne dispense pas de rejouer CP1-CP8 sur le code réellement livré** : l'instrument de la
sonde n'est pas le code du dépôt.

### Ce que ce protocole **ne** prouve **pas** — à ne jamais présenter autrement

1. **Il ne prouve pas un rendu identique au pixel.** Aucun navigateur automatisé sur cette pile. Il
   prouve que **le flux de déclarations est identique** ; le pas « donc le navigateur en tire le même
   rendu » est **déduit de la spécification CSS, pas mesuré**.
2. **Il ne prouve rien sur l'hébergement de production.** Il prouve que la règle de bascule ne dépend
   d'**aucune propriété que Git, FTP ou rsync détruisent** — propriété du code, établie par CP7 et CP8,
   jamais une observation sur le serveur final.
3. **La bascule n'est attestée que sur les pages ouvertes.** P1-P6 portent sur les **fichiers**, donc
   couvrent les 14 paires ; « quelle URL est servie » s'observe page par page.
4. **Il ne dit rien de l'éditeur**, qui reste sur les sources — c'est précisément pourquoi rien n'y est
   changé.
5. **Il ne mesure pas le rendu sur un cache navigateur déjà peuplé** : le bénéfice de l'empreinte comme
   `ver` est raisonné, non mesuré.

---

## 10. Le basculement d'incorporation — acté, avec sa contrepartie chiffrée

Recalculé au **vrai** seuil de 40 000 o (A1), budget du cœur déduit :

| Page | budget avant | `<link>` MTB avant | budget après | `<link>` MTB après | requêtes économisées |
|---|---|---|---|---|---|
| **Accueil** | **25 364 / 40 000** | **2** — `mtb-grille-chiens` (26 786 o), `mtb-bandeau-ouverture` (34 566 o) | **15 106 / 40 000** | **0** | **−2** |
| Contact | 27 368 | 0 | 8 736 | 0 | 0 |
| Archive portées | 31 988 | 0 | 10 463 | 0 | 0 |
| Fiche portée / chien | 11 442 | 0 | 5 994 | 0 | 0 |
| Espace privé | 11 744 | 0 | 6 296 | 0 | 0 |
| Page simple | 12 443 | 0 | 6 995 | 0 | 0 |

**Ce qui compte davantage que les 2 requêtes gagnées : la marge.** Contact est aujourd'hui à
**27 368 / 40 000** et Archive portées à **31 988 / 40 000**. Il suffit d'ajouter une feuille de bloc de
10 Ko sur l'une d'elles pour que le **`break` de `:3118` éjecte une feuille déjà incorporée**, sans
avertissement. Après #40, la page la plus chargée descend à **15 106 / 40 000** : **le budget cesse
d'être un piège silencieux.**

**La contrepartie, mesurée et non estimée** : une feuille incorporée n'est plus mise en cache d'une
page à l'autre. Coût exact : **760 o par affichage de l'Accueil** (HTML 6 206 o contre 5 446 o). En
face, l'incorporation économise **1 519 o** de `<link>` à la première visite.

- Première visite : **−759 o et −2 requêtes**.
- Chaque visite suivante de l'Accueil : **+760 o**.
- **Point mort à 2 affichages de l'Accueil.**

**Ce qui borne le problème** : `mtb-bandeau-ouverture` et `mtb-grille-chiens` **ne servent que sur
l'Accueil**. Il n'y a donc **aucun coût de répétition entre pages différentes**. Et les feuilles
réellement répétées de page en page (`mtb-coordonnees-plan`, sur les 6 pages) sont **déjà** incorporées
aujourd'hui : #40 **réduit** ce coût-là, de 6 933 o à 1 485 o par page.

**Arbitrage : on le veut, et on ne s'en protège pas dans #40.** Le plafond du cœur borne le pire cas ;
c'est l'heuristique de l'équipe performance de WordPress ; s'en protéger exigerait un filtre hors
empreinte ; et le tri croissant joue en notre faveur.

**Réserve CRLF/LF à écrire dans tout compte rendu de mesure** : les tailles mesurées dans Docker (qui
monte l'arbre Windows) sont **supérieures** aux tailles de production d'environ un octet par ligne. Une
feuille qui s'incorpore dans Docker s'incorpore *a fortiori* en production ; **l'inverse n'est pas
vrai**. Le sens de l'erreur est connu : il s'écrit, il ne se devine pas.

---

## 11. Ce qui est livré — fichiers et auteur

**Un seul agent : `dev-front-mtb`.** Le dépouilleur (dans l'outil) et le vérificateur (dans
`functions.php`) doivent s'accorder **octet pour octet** sur la forme canonique et l'empreinte. Écrits
par deux agents à l'aveugle, ils divergeraient — et **la divergence serait silencieuse** : les artefacts
ne seraient jamais servis, sur toutes les pages, sans la moindre erreur. Supprimer ce risque coûte zéro.

### Créés

| Chemin | Nature |
|---|---|
| `docker/outils/mtb-minifier-css.php` | outil CLI, **jamais chargé par WordPress**, jamais déployé sur un chemin servi par le web |
| `wp-content/themes/mtb/assets/css/{tokens,base,entete-pied,fiches}.min.css` | 4 artefacts |
| `wp-content/themes/mtb/assets/css/blocs/mtb-{bandeau-alerte,bandeau-ouverture,coordonnees-plan,derniere-portee,encart-appel,fiche-information,formulaire-contact,grille-chiens,liste-portees,tableau-resultats}.min.css` | 10 artefacts |

**14 artefacts. `editor.min.css` ne doit pas exister.**

### Modifiés

| Chemin | Modification, bornée |
|---|---|
| `wp-content/themes/mtb/functions.php` | `:6-12` docbloc de tête (§7) · `:19-38` bascule + `ver` + docbloc `:24` · `:178` « deux » → « trois » · `:194-252` `src`/`path`/`ver`. **`:214-219` reste sur la source** (§5) ; `:203-207` reste vrai et n'est pas touché |
| `Makefile` | `.PHONY` + cibles `css` et `css-check` |
| `.gitignore` | **bloc de commentaire seul, aucune règle** — aucune règle existante n'exclut `*.min.css` ; un `.gitignore` ne peut pas « dé-ignorer » ce qui n'est pas ignoré, une ligne de négation serait un faux-semblant. Le commentaire arrête la main de qui écrirait `*.min.css` par réflexe |
| `docs/docker.md` | section « Feuilles de style minifiées (#40) » : les deux cibles, la règle de bascule, la réserve d'appartenance des fichiers sur hôte Linux, et **l'obligation de processus** |
| `assets/css/blocs/mtb-bandeau-ouverture.css:153` | **le seul octet de source touché**, sous les trois conditions de A5 |

### Non touchés, et à ne pas toucher

`enveloppe-fiche.php` (hérite de la bascule sans un octet) · `theme.json` · `design-system/MASTER.md` ·
`wp-content/plugins/mtb-core/**` · `compose.yaml` · `CLAUDE.md` · `.gitattributes` · **les 14 autres
sources CSS, pas une ligne, pas un espace**.

**Aucun `README` racine n'est créé** : le dépôt n'en a pas, et l'endroit où l'on cherche « comment on
construit » sur ce projet est `docs/docker.md`.

### L'outil — contrat

`docker/outils/` **n'est monté nulle part** : `compose.yaml` ne monte dans `wordpress` que le thème et
l'extension, et **`compose.yaml` est hors empreinte**. La cible Makefile monte donc le dépôt à la
volée, forme indicative :

```
docker compose run --rm --no-deps --entrypoint php -v "<dépôt>:/depot:rw" wordpress \
  /depot/docker/outils/mtb-minifier-css.php --racine=/depot/wp-content/themes/mtb/assets/css [--verifier]
```

- `--no-deps` : la commande n'ouvre aucune base.
- `--entrypoint php` : court-circuite `docker-entrypoint.sh`.
- **Le dépôt entier est monté à `/depot`** : une seule racine, aucune ambiguïté sur la copie écrite.
- **`css-check`** et non `css-verifier` : `db-check` existe déjà, on suit la convention du fichier.
- **Pas d'appel depuis `docker/provision/provision.sh`** : `wpcli` n'a pas le montage et l'ajouter
  demanderait `compose.yaml`. **Limitation écrite, non contournée.**
- **Réserve à écrire dans `docs/docker.md`** : sur un hôte Linux, `docker compose run` entre en `root`
  et les artefacts appartiendront à `root` — même famille que le `chown` documenté pour
  `debug-log-reset` (`Makefile:60-65`). Sans objet sur Docker Desktop.

**Sans `--verifier`** : régénère les 14 artefacts, une ligne par fichier
(`source → artefact : <avant> o → <après> o (−NN %)`) plus un total. Sortie 0 sauf refus.
**Avec `--verifier`** : n'écrit **rien** ; par paire `à jour` / `PÉRIMÉ` / `ABSENT` / `ORPHELIN` /
`INVALIDE` ; **sortie 1** si une seule paire n'est pas `à jour`.

**Découverte des sources** : `glob()` sur `assets/css/*.css` et `assets/css/blocs/*.css`, avec
**annotation obligatoire dans l'en-tête du script** : *ce `glob()` est licite ici et nulle part
ailleurs. La proscription de `glob()`/`scandir()`/`opendir()` (décision 4 de `docs/ETAT.md`, rappelée en
`functions.php:189-193`) vise le chemin de requête du thème sur un hébergement mutualisé. Ce script ne
tourne jamais sous WordPress, jamais sur l'hébergement de production, jamais dans une requête web.*

**Exclusions**, chacune avec son motif écrit dans le code : `editor.css` (§5) · tout `*.min.css` (sans
quoi on produirait `base.min.min.css`) · tout fichier non `.css` (`.gitkeep`).

**Refus** (message explicite, sortie 1, **aucun fichier écrit**) : BOM UTF-8 en tête · source commençant
déjà par `/*!mtb-src:` · commentaire non terminé · chaîne non terminée ou saut de ligne brut dans une
chaîne · artefact orphelin (**signalé, jamais supprimé**).

---

## 12. Vérifications qui font foi

**Bloquantes** :

| # | Vérification |
|---|---|
| **R1** | `make css-check` sort **0** sur l'arbre commité (P1-P6 verts) |
| **R2** | Sur l'Accueil, une fiche de chien, une fiche de portée, `/travail/`, `/contact/`, la page protégée et le 404 : **chaque `<link>` de feuille du thème** pointe un `.min.css`, et **chaque `<style>` incorporé** commence par `/*!mtb-src:` |
| **R3** | **V-polices** : `200` sur `…/assets/fonts/newsreader-var-latin.woff2` **et** `…/public-sans-var-latin.woff2` ; **aucune** requête vers un chemin contenant `/assets/css/fonts/`. **Le contrôle visuel ne fait pas foi** : `base.css:59-70` ajuste métriquement Georgia et Arial pour que le repli ne décale rien — une chute serait **invisible à l'œil**. Le code 200 fait foi ; la police calculée sur `<h1>` ne vient qu'en confirmation |
| **R4** | CP1 à CP8 tous conformes, **sur le code livré**, joués **avant** toute mesure de poids |
| **R5** | `make debug-log-reset` → parcours des 7 pages → `make debug-log` → **journal vide** |
| **R6** | Basculement d'incorporation : liste avant/après des poignées en `<link>` et en `<style>`, **plus** les octets réseau d'une **seconde** vue. Réserve CRLF/LF écrite |
| **R7** | Poids réseau **et décompressé** de l'Accueil et de `/travail/`, avant et après, **mesurés au même instrument** (gzip 6, nommé) — chiffres à verser à D8 |
| **R8** | **Les 14 sources non touchées ont un `git diff` vide**, aucune ligne décalée. La 15e (`mtb-bandeau-ouverture.css`) : **nombre total de lignes identique**, diff borné au seul chiffre de `:153` |
| **R9** | Aucun `.min.css` orphelin ; `editor.min.css` absent ; les 14 artefacts **suivis par Git** (`git ls-files`) ; `make css` joué deux fois de suite laisse `git status` **propre** |
| **R10** | **Le décalage de lignes de `functions.php` est minimisé, puis mesuré, puis rapporté.** Voir ci-dessous — ajoutée le 2026-08-31, c'est une lacune de mon gel initial |

### R10 — la lacune que je reconnais, et ce qu'elle coûte

**R8 protégeait les lignes des sources CSS et n'a rien dit du fichier que #40 rouvrait.** La passe de
refacto a mesuré la conséquence : `mtb_feuille_a_servir()` a été insérée **au-dessus** de
`mtb_mettre_feuille_en_file()`, décalant `functions.php` de **114 lignes** et périmant une vingtaine de
citations `functions.php:NNN` dans le dépôt — dont **huit dans des sources CSS que R8 interdit
précisément d'ouvrir**, deux dans `docs/ETAT.md`, une dans
`plugins/mtb-core/includes/blocks/lien-de-recours/rendu.php:227` (**empreinte active de #43, à ne
jamais toucher**), et une quinzaine dans des contrats gelés. C'est exactement le défaut attrapé sur
`block.json:18` au lot 13, et #40 l'a commis dans le geste même où elle promettait de ne pas décaler
une ligne.

**Remède, dans cet ordre :**

1. **Déplacer `mtb_feuille_a_servir()` à la fin de `functions.php`.** En PHP, une fonction déclarée au
   niveau supérieur d'un fichier est disponible avant son point de déclaration : l'ordre n'a aucune
   incidence sur le comportement. Le gain est mécanique et sûr — les ~110 lignes du bloc cessent de
   décaler tout ce qui suit.
2. **Rendre les retouches en place aussi neutres que possible** dans `mtb_mettre_feuille_en_file()` et
   dans la boucle de blocs, sans jamais sacrifier la lisibilité ni un commentaire porteur de raison.
3. **Mesurer le décalage résiduel** et le **rapporter nommément** : pour chaque citation
   `functions.php:NNN` du dépôt, dire si elle reste vraie, et sinon donner son nouveau numéro.

**Un décalage résiduel nul n'est pas exigé** — les retouches ajoutent de la logique réelle. Ce qui est
exigé, c'est qu'il soit **minimisé, mesuré et déclaré**, jamais découvert en revue. Les citations qui
restent périmées dans des **contrats gelés**, dans `docs/ETAT.md` et dans les **sources CSS** ne sont
pas corrigées par #40 : elles sont **remontées à l'orchestrateur**, qui seul peut les arbitrer.

**Leçon à retenir pour les gels futurs, et elle est à moi** : une issue qui protège les numéros de ligne
d'un jeu de fichiers doit se demander si elle en décale un autre. Protéger les sources CSS et rouvrir
`functions.php` sans y penser, c'est avoir vu le risque et l'avoir borné au mauvais fichier.

**Résultat obtenu** : le décalage passe d'une bande **+105/+116** à une bande **+10/+17**, en déplaçant
`mtb_feuille_a_servir()` **en fin de fichier** (`:970-1064`). Les 10 lignes irréductibles sont celles du
docbloc de tête, que le §7 **impose** de faire dire trois choses — et comme elles grossissent
**au-dessus de tout le reste**, aucune citation visant une ligne ≥ 11 ne peut redevenir vraie, quel que
soit le soin apporté ailleurs. Descendre à ~9 lignes en amputant ce docbloc **ne rendrait pas une seule
citation valide** : cela ne changerait que l'arithmétique. Le décalage résiduel est donc assumé.

**48 citations `functions.php:NNN` existent dans le dépôt, aucune n'est encore exacte, et 7 étaient
déjà fausses à `e8a35f4`, avant #40.** La table de correspondance complète — citant → cible → nouveau
numéro — figure au rapport de chaîne remis à l'orchestrateur ; elle rend la reprise mécanique. Aucune
n'est corrigée ici : 8 vivent dans des sources CSS que R8 interdit d'ouvrir, 2 dans `docs/ETAT.md`, 15
dans des contrats gelés, et **1 dans `plugins/mtb-core/includes/blocks/lien-de-recours/rendu.php:227`,
empreinte active de #43**. Le fait que 7 fussent déjà périmées avant #40 dit que la pratique de
citation par numéro de ligne sur ce fichier se dégradait **déjà** : c'est un problème de méthode, pas
un accident de cette issue, et il se route comme tel.

**La seule citation dont #40 était à la fois l'auteur et le fossoyeur est close** :
`docker/outils/mtb-minifier-css.php` citait `functions.php:303-306`, périmé par le déplacement
ci-dessus. Elle n'a **pas** été renumérotée — elle a été **désancrée du numéro** et réduite à son ancre
nominale (« dans le docbloc de `mtb_feuilles_de_blocs()` »), avec la raison écrite sur place pour qu'un
relecteur futur ne « répare » pas l'ancre en y remettant un chiffre. **Un nom survit aux décalages, un
numéro non** — c'est le remède, la renumérotation n'en est qu'un report.

### Note de lecture sur ce contrat lui-même

**Tous les `functions.php:NNN` cités dans ce document désignent le fichier tel qu'il était à
`e8a35f4`, avant #40** — c'est la seule lecture qui rende vraies les plages d'empreinte que j'ai
autorisées (`:19-38`, `:194-252`, `:6-12`, `:178`). Ils ne sont **pas** mis à jour vers l'état livré :
un contrat gèle ce qui a été décidé, et le renuméroter effacerait le périmètre réellement accordé. Pour
retrouver l'état livré, ajouter **+10 à +17** selon la zone, ou mieux, chercher la fonction par son nom.

**Non bloquante, à consigner** : temps de rendu avant/après sur 20 chargements, pour donner un chiffre
au coût de vérification plutôt qu'une estimation (borne haute raisonnée : **≈ 0,8 ms par page**, soit
l'ordre de grandeur d'**une requête SQL** ; le cœur fait déjà exactement ce geste dans
`wp_maybe_inline_styles()`, qui appelle `wp_filesize()` puis `file_get_contents()` sur chaque feuille
porteuse d'un `path`).

---

## 13. Interdits

- Le thème n'interroge jamais la base directement. **#40 n'ajoute ni requête SQL, ni transient, ni
  option** — c'est le motif explicite du rejet de la voie « verdict mémorisé ».
- Le thème ne compose jamais une chaîne métier ni ne reformate une valeur de santé.
- L'extension n'émet aucune règle visuelle. Elle n'est pas touchée.
- **Aucune source CSS n'est réécrite, aucune de ses lignes décalée, aucun de ses commentaires retiré**
  (sauf le seul chiffre de A5).
- **Aucun artefact n'est servi sans preuve de fraîcheur.**
- Aucune dépendance tierce, aucun `node_modules`, aucun `vendor/`, aucun paquet, aucune origine réseau,
  **aucune étape de construction exigée en production**.
- Le thème n'écrit aucun fichier à l'exécution.
- `glob()`, `scandir()`, `opendir()`, `DirectoryIterator` restent proscrits sur le chemin de requête du
  thème.

---

## 14. Arbitrages — les désaccords tranchés

| # | Désaccord | Décision | Raison |
|---|---|---|---|
| 1 | Le brainstorm estimait le gain réseau à ~23 000 o et le jugeait peut-être marginal | **Mesuré : 55 824 o.** On livre | Une estimation qui décide est le motif d'échec des lots 11 et 12 |
| 2 | Le plan raisonne sur un seuil d'incorporation de **20 000 o** | **40 000 o**, cité depuis le cœur installé (`script-loader.php:3062`, `@since 6.9.0`) | La source du plan était `mtb-bandeau-ouverture.css:153`, périmée depuis WP 6.9 |
| 3 | Le brainstorm proposait `filemtime()` comme test de fraîcheur | **Rejeté.** Empreinte de contenu sur forme canonique | Git écrit dans l'ordre lexicographique de l'index : le test dégénère en « **toujours l'artefact** », pas en hasard |
| 4 | Un hachage sur octets bruts | **Rejeté.** Forme canonique (LF) obligatoire | `base.css` = 45 914 o dans git, 46 927 o sur disque. Un marqueur brut serait rejeté **partout en production** |
| 5 | `ver` = `filemtime` ou empreinte | **Empreinte** | Déjà calculée, donc gratuite ; adressée par le contenu, donc **le cache du visiteur survit au déploiement** |
| 6 | `.gitattributes` recommandé par le plan | **Refusé dans #40** | Poser le motif **renormalise les 15 sources** — exactement ce que #40 promet de ne pas faire. La correction n'en dépend pas |
| 7 | `CLAUDE.md` recommandé par le plan | **Refusé dans #40** | Hors empreinte, fichier d'orchestration. Routage remonté au lead |
| 8 | `dev-ux-mtb` | **Non lancé** | #40 n'écrit pas un sélecteur. L'attestation est transférée à `refacto-mtb` |
| 9 | Avertissement d'administration sur artefact périmé | **Refusé** | Message inactionnable pour l'éleveuse, et **D3 se déclencherait** pour rien |
| 10 | P1 comparant des octets bruts | **Formes canoniques des deux côtés** | Sinon P1 échoue sur tout dépôt fraîchement cloné, c'est-à-dire en production |
| 11 | `editor.css` minifiée ? | **Non** | N'atteint jamais un visiteur ; basculer là exigerait un second mécanisme hors empreinte |
| 12 | Corriger `mtb-bandeau-ouverture.css:153` ? | **Oui, sous 3 conditions cumulatives** (A5) | Laisser faux, dans une feuille de l'empreinte, le chiffre du mécanisme dont dépend #40, serait le motif « prose que le code dément » des lots 12 et 13 |

---

## 15. Deux faits mesurés que je ne sais pas réconcilier — à ne jamais arrondir

1. **Le « 56 140 o réseau » du lot 9 n'est pas reproductible.** Les corps réellement servis pèsent
   **54 446 o** (gzip 6, mesuré au serveur). Aucun niveau gzip ne donne 56 140 (niveau 4 : 56 263 ;
   niveau 5 : 54 831 ; niveau 9 : 54 382). En ajoutant les en-têtes de réponse (5 × ~283 o) on obtient
   **55 860 o**, soit **280 o de moins** que la référence. Hypothèse **non vérifiée** : le lot 9
   comptait corps + en-têtes et une réponse de plus. Le « 68 749 o page totale » ne se reproduit pas
   davantage. **Le « 147 492 o décompressé », lui, se retrouve exactement** :
   `10 422 + 34 566 + 26 786 + 46 927 + 28 791`.
2. **Une cellule de la sonde n'est pas réconciliée** : elle donne « Accueil, `<link>` décompressé après
   = 68 620 o », là où la somme des feuilles attendues donne 18 149 o en `<link>` plus 5 029 o
   incorporés. **R7 doit re-mesurer le décompressé sur le code livré** plutôt qu'hériter de cette
   cellule.

3. **`/travail/` n'existe pas sur cette pile**, et j'ai dit **deux faussetés successives** à son sujet.
   R2 et R7 la nomment ; la page rend **404** et les mesures ont porté sur `/portees/` à sa place,
   **sans jamais être présentées comme `/travail/`**.

   **Première fausseté — la cause.** J'avais imputé le 404 à `has_archive => false`. `mtb_resultat` est
   `public = false`, `publicly_queryable = false`, `rewrite = false` — il n'a donc **aucun slug**, et
   `has_archive` n'y est pour rien. La vraie cause : **la page n'existe dans aucun statut**,
   `wp mtb reprise-resultats-pages` n'ayant jamais été lancée sur ce volume. Commande non jouée, pas un
   défaut ; rien n'est perdu.

   **Seconde fausseté, et elle est plus instructive — la conséquence que j'en tirais.** J'écrivais que
   « `mtb-tableau-resultats.min.css` n'est attesté servi sur aucune page rendue ». **C'est faux, mesuré
   sur les 108 pages en 200** : cette feuille **est servie, incorporée, 325 o**, sur **4 pages** —
   `/chien/demo-rex/`, `/chien/demo-vega/`, `/chien/demo-cesar/` et leur forme `?p=`. Deux observables
   indépendants le montrent : les noms de fichier des `<link rel="stylesheet">` **et** le
   `/*# sourceURL=… */` que le cœur ajoute au bas de chaque feuille incorporée, qui nomme le fichier
   réellement lu.

   > **La cause de mon erreur, qui vaut plus que le fait corrigé : le composant « Tableau de résultats »
   > vit sur les fiches de chiens qui portent des résultats de travail, pas seulement sur une page
   > « Travail ». J'ai raisonné depuis la page absente au lieu de chercher où le bloc est réellement
   > posé.** Une absence de page ne dit rien de l'emploi d'un bloc ; seul le contenu le dit.

   **Ce qui est vrai a une autre forme et un autre compte** : **trois artefacts sur quatorze** ne sont
   servis sur aucune page de ce volume — `mtb-bandeau-alerte.min.css`, `mtb-fiche-information.min.css`,
   `mtb-liste-portees.min.css`. Fraîcheur prouvée par `make css-check`, **service non observé** : trois
   composants qu'aucun contenu de ce jeu ne place. Ma phrase désignait donc **la mauvaise feuille et
   sous-comptait le nombre**.

   **Deux constats favorables qui manquaient, mesurés sur les mêmes 108 pages** : **aucune source non
   minifiée n'est servie sur aucune page**, et les quatre feuilles de socle — `base`, `entete-pied`,
   `tokens`, `mtb-coordonnees-plan` — sont servies sur **les 108**.

---

## 17. Chiffres définitifs, mesurés après la sixième condition

Les 14 artefacts ayant été réécrits par l'élargissement du marqueur, tous les poids ont été
**re-mesurés, pas reconduits**. Les valeurs ci-dessous font foi et remplacent celles du §1, qui
décrivaient le marqueur à deux champs.

| | avant #40 | après, marqueur à 4 champs |
|---|---:|---:|
| 14 artefacts, octets canoniques | — | **41 810 o** (contre 41 504 au marqueur à 2 champs : **+306 o, soit +21,9 o par artefact**) |
| Accueil, réseau gz6 | 66 553 | **10 867 o** |
| Accueil, décompressé | 185 218 | **43 831 o** |
| Pire page, `/chien/jango/`, réseau / décompressé | — | **17 936 / 62 932 o** — soit **9,0 % du plafond** de 200 000 |
| Coût réseau du marqueur élargi, sur l'Accueil | — | **+137 o** |

**Une prévision tenue et une prévision fausse, je donne les deux.** J'avais annoncé 41 826 o
d'artefacts : c'est **41 810**, seize octets d'écart — sans conséquence, mais **un contrat se cite**,
donc le chiffre juste est ici. J'avais annoncé 43 839 o décompressés sur l'Accueil par simple
arithmétique : mesuré **43 831**, soit **8 octets d'écart** — l'arithmétique tenait, et j'avais eu
raison de laisser le réseau gzippé à la mesure plutôt qu'à mon calcul.

**Le correctif tient, sans réserve : 48 avaries sur 48, zéro écart**, dégradation silencieuse sur les
48 cas, `debug.log` vide. La corruption à longueur constante est bien couverte. **Les quatre champs
mordent chacun séparément** — le champ 3 seul ou le champ 4 seul suffit à écarter l'artefact — donc la
garde **ne tient pas par conjonction**, ce qui la rend robuste à la perte d'un champ. Et l'identité des
14 paires a été **re-prouvée après la réécriture** : comptes de jetons identiques au jeton près, la
réécriture n'ayant touché que la ligne du marqueur.

**Un fait établi contre ma propre borne, et à mon crédit** : l'ancienne valeur de **64 perdait le
marqueur maximal en silence**. Le relèvement à 128 n'était donc pas une précaution, c'était une
**réparation** — et la garde `P6` refuse pour de vrai, éprouvée en bac à sable.

---

## 16. Dettes ouvertes par ce contrat, non payées ici

- **`.gitattributes`** : `wp-content/themes/mtb/assets/css/**/*.css text eol=lf` rendrait les octets CSS
  identiques entre développement et production et ferait coïncider les mesures. Refusé dans #40 (A5)
  parce qu'il renormaliserait les 15 sources. **Sa pose est une issue à part, à jouer seule.**
- **`compose.yaml:140-151` et la dette T48** affirment que le dépôt est « sans `.gitattributes` ».
  **C'est faux depuis #47** : le fichier existe à la racine (`.claude/agents/*.md text eol=crlf`). Le
  raisonnement de T48 reste juste — il porte sur la *portée* d'un motif racine — mais sa prémisse est
  périmée, et le prochain lecteur en conclura qu'il faut créer un fichier qui existe déjà.
- **Domicile durable de la règle de processus** : `CLAUDE.md` § Conventions (« toute modification d'une
  feuille sous `assets/css/` s'accompagne d'un `make css` dans le même commit ») et ajout de
  `make css-check` à la liste de `test-integration-mtb`.
- **`docker/provision/provision.sh` ne peut pas appeler le vérificateur** faute de montage, et
  l'ajouter demanderait `compose.yaml`, hors empreinte.
- **`base.css:25-27`** annonce qu'un allègement passé « décale les commentaires d'une centaine de lignes
  de plus ». Le passage reste exact ; **#40 ne décale rien** — signalé pour qu'un relecteur ne le prenne
  pas pour une trace de #40.

- **Dette : le jumeau à tenir en accord.** *Énoncé* — la révision de générateur `"mtb-min/1\n"`, la
  définition de la forme canonique et le calcul de l'empreinte sont écrits **deux fois** : dans
  `docker/outils/mtb-minifier-css.php` et dans `wp-content/themes/mtb/functions.php`. Une divergence
  entre les deux serait **silencieuse** — les artefacts cesseraient simplement d'être servis, sur
  toutes les pages, sans erreur, sans notice, sans journal ; seul le poids augmenterait.
  *Provenance* — c'est le coût assumé du §4, retenu **délibérément** contre la factorisation : le
  thème doit pouvoir vérifier une empreinte **sans dépendre d'un outil de `docker/`**, qui n'est ni
  déployé chez l'hébergeur ni chargeable par WordPress. Une divergence **du jumeau** dégrade vers « la
  source est servie » — les empreintes cessant de concorder — et non vers « un artefact accepté à
  tort » ; **cette garantie porte sur le jumeau seul**, et ne s'étend à aucun autre mode de panne (la
  formule large qui figurait ici a été mesurée fausse, voir « La sixième condition »). *Garde-fous en
  place* — chacun des deux porte un commentaire nommant son jumeau, et `make css-check` rend la
  divergence visible en 14 paires `PÉRIMÉ`. *Ce qui manque* — rien ne **force** la relecture conjointe :
  la garde reste humaine. **Sans numéro : l'orchestrateur numérote à la clôture du lot.**

  **Première mise à l'épreuve, réussie.** L'ajout de la sixième condition a fait bouger le marqueur, donc
  **les deux moitiés ensemble**. Le jumeau porte désormais **cinq** points et non trois — révision,
  forme canonique, motif à quatre champs, borne de lecture, condition 6 — et les commentaires croisés
  les énumèrent. Deux garde-fous **machine** sont nés de cette passe, qui ne dépendent plus d'une
  relecture : P6 refuse si la borne de lecture n'excède pas la longueur maximale du marqueur, et le
  générateur **refuse d'écrire** un artefact dont le marqueur ne vérifie pas son propre motif (cas d'un
  corps vide, où `<A>` vaudrait 0 et où l'artefact serait écrit puis ignoré à jamais). La dette reste
  ouverte — la garde de fond est toujours humaine — mais elle est **moins nue qu'à son inscription**.
