<?php
/**
 * Indexation héritée : conversion du fait relevé sur l'ancien site en un état que l'éleveuse pilote.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\IndexationHeritee;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * MODULE DISTINCT DE « redirections-301 », POUR TROIS MOTIFS GELÉS :
 *
 *   1. HONNÊTETÉ DU NOM — un module nommé « redirections-301 » qui convertirait une directive
 *      d'indexation mentirait sur son contenu.
 *   2. RÉVERSIBILITÉ. Elle ne passe plus par le code depuis le 2026-09-07 (#52), ET C'EST TOUT
 *      L'OBJET DE L'ISSUE : à « remettez Placement dans Google », la réponse est désormais
 *      « ouvrez Placement et décochez "Mettre ce contenu en sommeil" ». Le renommage du dossier en
 *      « _indexation-heritee » — la façon documentée de désactiver un module
 *      (« class-loader.php », initiale « _ ») — NE RÉVEILLE PLUS AUCUN CONTENU MIS EN SOMMEIL : il
 *      empêcherait seulement la conversion des contenus PAS ENCORE convertis, l'état des autres
 *      étant déjà en base et servi par « query/mise-en-sommeil ». IL N'EST PAS NEUTRE POUR AUTANT, et
 *      il faut le dire ici parce que c'est UN PIÈGE MATÉRIEL pour qui l'appliquerait de bonne foi.
 *      TROIS EFFETS, ET ILS TOMBENT ENSEMBLE : il rendrait au plan du site le fournisseur écarté le
 *      2026-09-05 ; il ferait tomber avec lui le 404 franc posé depuis le 2026-09-08 (#50), les deux
 *      lisant la même constante ; et, depuis le 2026-09-08 (#49), IL ROUVRIRAIT TOUTES LES ARCHIVES
 *      D'AUTEUR DU SITE — « /author/<slug>/ » de retour à 200 et la forme en requête de retour à 301
 *      vers elle — DONC L'ÉNUMÉRATION DES COMPTES, CELUI DE L'ÉLEVEUSE COMPRIS, DE NOUVEAU LISIBLE.
 *      Le module reste néanmoins séparé pour que ce renommage, s'il devient nécessaire, n'emporte pas
 *      une ligne de la carte des 52 adresses reprises.
 *   3. TÉMOINS D'ÉCHEC DISJOINTS. « redirections-301 » se prouve vivant par un code de sortie
 *      WP-CLI ; ce module-ci n'a PAS de commande, et sa seule sonde est l'état écrit en base sur les
 *      contenus repris. Deux sondes de nature différente : les réunir dans un dossier ferait croire
 *      qu'une seule suffit.
 *
 *      ACTE DATÉ DU 2026-09-08 (#56) — « SA SEULE SONDE EST L'ÉTAT ÉCRIT EN BASE » N'EST PLUS VRAI, ET
 *      LE FICHIER SE CONTREDISAIT DÉJÀ LUI-MÊME PLUS BAS. La phrase était exacte à #52, quand ce module
 *      ne faisait que convertir. Elle a cessé de l'être le 2026-09-08 avec #50, dont la sonde est un
 *      CODE DE STATUT HTTP — ce que la deuxième surface silencieuse écrit noir sur blanc quelques
 *      paragraphes plus loin (« Sa sonde n'est pas en base, c'est un code de statut HTTP »). #56
 *      l'éloigne encore : ses trois rappels se prouvent vivants par un CORPS HTTP, jamais par un état
 *      en base. LE DÉCOMPTE HONNÊTE EST DONC : ce module a désormais des sondes DE DEUX NATURES —
 *      l'état en base pour la conversion, un corps ou un code HTTP pour les cinq autres effets.
 *      CE QUI NE CHANGE PAS, ET C'EST TOUT L'OBJET DU MOTIF : aucune n'est un code de sortie WP-CLI.
 *      La frontière avec « redirections-301 » tient donc intacte, et la conclusion du motif 3 — les
 *      réunir ferait croire qu'une seule sonde suffit — est INCHANGÉE. Seule la description du témoin
 *      de CE module-ci était périmée. Corrigé par ajout, jamais par amputation : le motif garde la
 *      trace de ce qu'il a cru.
 *
 * PREMIER ACTE DATÉ DU 2026-09-08 (#56) — LE COMPTAGE DU MOTIF 2 EST DEVENU FAUX : SIX EFFETS.
 * Le motif 2 ci-dessus écrit « TROIS EFFETS, ET ILS TOMBENT ENSEMBLE ». Depuis le 2026-09-08 (#56),
 * ILS SONT SIX, et le comptage n'est pas un détail de rédaction : c'est ce qui dit à qui renommerait le
 * dossier en « _indexation-heritee » ce qu'il rouvre exactement. LE PIÈGE MATÉRIEL EST DONC AGGRAVÉ.
 * Aux trois déjà nommés — le fournisseur rendu au plan du site, le 404 franc du sous-plan qui tombe avec
 * lui, et toutes les archives d'auteur du site qui rouvrent (« /author/<slug>/ » de retour à 200, la
 * forme en requête de retour à 301) — s'ajoutent TROIS AUTRES, du même instant :
 *   4. « /wp-json/wp/v2/users » et « /wp-json/wp/v2/users/<id> » REPUBLIENT LES DEUX COMPTES, leurs
 *      slugs ET LE NOM CIVIL DE L'ÉLEVEUSE, en une requête sans cookie — sous leurs DEUX écritures,
 *      jolie adresse et forme en requête, et l'oracle par recherche de nom avec elles ;
 *   5. « /feed/ », « /feed/rdf/ » et « /feed/atom/ » republient le nom d'affichage du compte 1 ;
 *   6. l'oEmbed de tout contenu singulier republie ce même nom ET l'adresse « /author/admin/ » —
 *      ADRESSE QUI, AU MÊME INSTANT, REDEVIENT 200 PAR L'EFFET 3. Les deux se rallument ensemble : le
 *      document en publie l'URL pendant qu'elle recommence à répondre.
 * C'est exactement ce que le regroupement sous un seul « bootstrap.php » achète — une dégradation
 * COHÉRENTE, jamais une moitié fermée et une moitié ouverte — et c'est aussi ce qui rend le renommage
 * plus coûteux qu'il n'en a l'air. Aucun état mort à nettoyer dans les six cas.
 *
 * CE QUE CE MODULE FAIT DEPUIS LE 2026-09-07 (#52), ET CE QU'IL NE FAIT PLUS.
 *
 * Il ne SERT PLUS AUCUNE DIRECTIVE. Il ne pose plus « wp_robots », il ne retire plus rien du plan du
 * site à partir du fait hérité : « robots.php » est supprimé, « ecarter_les_noindex() » aussi, et
 * leurs deux « add_filter » avec eux. Il CONVERTIT, une fois et pour toutes, le fait relevé sur
 * l'ancien site en l'état « en sommeil » — une case à cocher dans un écran, que l'éleveuse peut
 * décocher. C'est « query/mise-en-sommeil » qui sert la directive, à partir de CET état-là.
 *
 * CE QU'IL FAIT EN PLUS DEPUIS LE 2026-09-08 (#50) : IL RÉPOND POUR LE FOURNISSEUR QU'IL RETIRE.
 * Ce n'est pas une directive d'indexation de plus — il n'en sert toujours aucune. C'est la
 * réparation de ce que le retrait avait cassé : « /wp-sitemap-users-1.xml » rendait 200 sur du
 * HTML, un faux 404. Le détail, la mesure et la borne sont écrits en tête de « plan-du-site.php ».
 *
 * POURQUOI LA CONVERSION ET NON LA COHABITATION DES DEUX MÉCANISMES. Les faire vivre côte à côte
 * produit le pire mode de panne de l'issue : l'éleveuse réveille Halan, l'ancien filtre continue de
 * rendre « noindex » et de le retirer du plan du site, LE RÉVEIL NE RÉVEILLE RIEN — sur un écran qui
 * affirme le contraire. Et le RATTRAPAGE PARTIEL EST REFUSÉ : on peut retirer un « noindex » posé par
 * un voisin, on ne peut pas défaire la clause qu'il a posée dans les arguments du plan du site sans
 * violer la convention de cohabitation gelée aux contrats #23 et #24. On obtiendrait un demi-réveil :
 * balise rétablie, plan du site toujours amputé. La démonstration complète est en tête de
 * « conversion.php ».
 *
 * POURQUOI LE RATTRAPAGE PARTIEL EST AUSSI REFUSÉ CÔTÉ DÉCLENCHEUR. « mtb_core_mise_a_jour » ne se
 * déclenche que si l'empreinte d'identité change ; #52 n'ajoute ni type ni taxonomie et ne touche pas
 * « mtb-core.php ». CE CROCHET NE SE DÉCLENCHERA DONC PAS UNE SEULE FOIS du fait de #52 sur une base
 * déjà pourvue de « mtb_core_empreinte », et là où il se déclenche il court AVANT l'import. Une
 * conversion accrochée à lui seul ne convertirait rien, en silence. D'où trois accroches sur la même
 * fonction idempotente, chacune avec son rôle nommé, « admin_init » étant le déclencheur porteur.
 *
 * PREMIÈRE SURFACE SILENCIEUSE : LA PANNE DE LA CONVERSION EST INVISIBLE. Elle ne rend rien,
 * n'imprime rien, ne journalise rien et ne déclare aucune fonction globale. Si ce module cesse
 * d'être chargé APRÈS la conversion, l'état converti ne bouge pas : il est en base, et c'est
 * « query/mise-en-sommeil » qui le lit. S'il cesse d'être chargé AVANT, les contenus repris ne sont
 * jamais convertis et restent indexables — la page répond 200, le journal reste vide. Le seul
 * témoin est le protocole du §13 du contrat #52, points C1 à C6.
 *
 * DEUXIÈME SURFACE SILENCIEUSE, DEPUIS #50, ET DE NATURE DIFFÉRENTE. Si le rappel de
 * « template_redirect » cesse de mordre — ligne « add_action » perdue dans une reprise, priorité
 * changée, « FOURNISSEURS_RETIRES » vidée, dossier renommé en « _indexation-heritee » —
 * « /wp-sitemap-users-1.xml » revient à 200 sur du HTML, silencieusement. RIEN NE LE DIRAIT : pas
 * une ligne au journal, pas un écran, et l'éleveuse ne visite jamais cette adresse. Sa sonde n'est
 * pas en base, c'est un code de statut HTTP, et elle se joue en recette — protocole du §9 du
 * contrat #50. LUI DONNER UNE COMMANDE WP-CLI CONTREDIRAIT LE MOTIF 3 CI-DESSUS, qui tient ce
 * module séparé de « redirections-301 » précisément parce que leurs témoins d'échec sont de
 * natures différentes ; en ajouter un troisième de la nature du voisin brouillerait la frontière
 * que ce motif protège. Résidu nommé, non masqué.
 *
 * TROISIÈME SURFACE SILENCIEUSE, DEPUIS #49, DE MÊME NATURE QUE LA PRÉCÉDENTE ET D'UN TOUT AUTRE ENJEU.
 * Si le rappel du filtre « request » cesse de mordre — ligne « add_filter » perdue dans une reprise,
 * « CLES_D_AUTEUR » vidée, dossier renommé en « _indexation-heritee », le cœur renommant une de ces
 * clés ou cessant d'honorer « error », un tiers filtrant « request » après nous et les remettant —
 * « /author/<slug>/ » revient à 200 et la forme en requête à 301, silencieusement. RIEN NE LE DIRAIT :
 * pas une ligne au journal, pas un écran, et l'éleveuse ne visite jamais ces adresses. LA NUANCE QUI SE
 * DIT PLUTÔT QU'ELLE NE SE LISSE : ce que #50 protège est une adresse machine que personne ne demande ;
 * CE QUE CELUI-CI PROTÈGE EST L'IDENTIFIANT DE CONNEXION DE L'ÉLEVEUSE. Le mode de panne est muet dans
 * les deux cas, l'enjeu ne l'est pas. Sa sonde est de même nature que celle de #50 — un code de statut
 * HTTP, joué en recette, protocole du §11 du contrat #49 — et AUCUNE SONDE SUPPLÉMENTAIRE N'EST
 * PROPOSÉE POUR AUTANT : la seule qui aurait du sens serait une commande WP-CLI, et LUI EN DONNER UNE
 * CONTREDIRAIT LE MOTIF 3 CI-DESSUS. Résidu nommé, non masqué, et son poids réel écrit plutôt que lissé.
 *
 * QUATRIÈME SURFACE SILENCIEUSE, DEPUIS LE 2026-09-08 (#56) — DEUXIÈME ACTE DATÉ. De même nature que les
 * deux précédentes, et d'un enjeu supérieur aux trois. Si l'un des trois rappels de
 * « identite-des-comptes.php » cesse de mordre — une ligne « add_filter » perdue dans une reprise, le
 * dossier renommé en « _indexation-heritee », le cœur renommant une clé de route ou cessant d'appeler
 * « the_author() » dans un gabarit de flux, un tiers filtrant « rest_endpoints » APRÈS nous et
 * réenregistrant la route — UNE IDENTITÉ DE COMPTE REPARAÎT DANS UN DOCUMENT PUBLIC. RIEN NE LE DIRAIT :
 * pas une ligne au journal, pas un écran, pas un ralentissement, et l'éleveuse ne visite jamais ces
 * adresses. Pire que les précédentes sur un point : les trois surfaces sont DISJOINTES, si bien que
 * DEUX PEUVENT CONTINUER DE MORDRE PENDANT QUE LA TROISIÈME FUIT — une vérification faite sur la
 * première dirait « c'est fermé » en toute bonne foi.
 * LA NUANCE QUI SE DIT PLUTÔT QU'ELLE NE SE LISSE, ET ELLE MONTE D'UN CRAN À CHAQUE ISSUE : ce que #50
 * protège est une adresse machine que personne ne demande ; ce que #49 protège est L'IDENTIFIANT DE
 * CONNEXION de l'éleveuse ; CE QUE #56 PROTÈGE EST AUSSI SON NOM CIVIL, publié en une requête sans
 * cookie, par une adresse qu'un aspirateur trouve avant un être humain. Le mode de panne est muet dans
 * les trois cas, l'enjeu ne l'est pas. Sa sonde est de même nature que celles de #50 et #49 — un corps
 * HTTP, joué en recette, protocole du §11 du contrat #56 — et AUCUNE SONDE SUPPLÉMENTAIRE N'EST PROPOSÉE
 * POUR AUTANT : la seule qui aurait du sens serait une commande WP-CLI, et LUI EN DONNER UNE
 * CONTREDIRAIT LE MOTIF 3. Résidu nommé, non masqué, pour la troisième fois.
 *
 * AMENDEMENT DÉCLARÉ À LA BORNE 1 (contrat #24 §15) : ce module ÉCRIT désormais, ce qu'il ne faisait
 * pas. La borne 1 porte sur ses hooks de FRONT ; les trois accroches de la conversion sont
 * « mtb_core_mise_a_jour », « added_post_meta » / « updated_post_meta » (administration ou WP-CLI) et
 * « admin_init » (administration seule) : AUCUNE ÉCRITURE SUR UNE REQUÊTE PUBLIQUE.
 * Les TROIS hooks de front de ce module — « wp_sitemaps_add_provider » 10, « template_redirect » 20
 * depuis le 2026-09-08 (#50) et « request » 10 depuis le 2026-09-08 (#49) — n'écrivent RIEN. Le
 * premier rend « false » au cœur ; le deuxième pose un code de statut sur la réponse en cours ; le
 * troisième AMENDE LA REQUÊTE EN MÉMOIRE, pour le seul processus en cours — et « de front » se dit ici
 * de son EFFET, non de son contexte d'exécution : le cœur applique aussi « request » sur les écrans de
 * liste de l'administration, où ce rappel sort par sa première garde sans rien amender (voir le renvoi
 * de son « add_filter », plus bas). La borne 1 disait « il lit », #50 l'a resserrée en « il lit, il
 * RÉPOND » — poser un 404 est répondre, pas écrire — et #49 l'étend une seconde fois, par écrit et non
 * en silence (contrat #49 §13.1) : « IL LIT, IL RÉPOND, ET IL PEUT AMENDER LA REQUÊTE EN MÉMOIRE —
 * JAMAIS L'ÉTAT PERSISTANT. » Aucun « update_option », « wp_insert_post », « update_post_meta »,
 * « wp_set_object_terms » ni « wp_delete_post » sur une requête publique, et aucune règle de
 * réécriture touchée. Les bornes 2 et 3 sont intactes : aucun état en base ne déclenche la conversion
 * — c'est la requête elle-même qui la borne — le périmètre reste clos aux seuls faits
 * « _mtb_robots_source » relevés sur l'ancien site, et celui de #49 est clos et daté aux archives
 * d'auteur de ce site.
 *
 * TROISIÈME ACTE DATÉ DU 2026-09-08 (#56) — « LES TROIS HOOKS DE FRONT DE CE MODULE » EN COMPTE SIX.
 * Le paragraphe ci-dessus en énumère trois ; l'énumération est juste à sa date et le comptage a cessé de
 * l'être. Les SIX hooks de front de ce module sont désormais : « wp_sitemaps_add_provider » 10,
 * « template_redirect » 20 (#50), « request » 10 (#49), puis « rest_endpoints » 10, « the_author » 10 et
 * « oembed_response_data » 10 (#56). SIX, ET PAS SEPT : aucune garde de ceinture n'est écrite, et il ne
 * faut pas en écrire « au cas où ». Et « de front » continue de se dire de l'EFFET, non du contexte
 * d'exécution : « rest_endpoints » court aussi au préchargement de l'éditeur de blocs, et « the_author »
 * court aussi sur les écrans de liste — où son rappel SORT PAR SA PREMIÈRE GARDE sans rien substituer,
 * faute de quoi la colonne « Auteur » afficherait le titre du site pour chaque ligne, en silence.
 *
 * QUATRIÈME ACTE DATÉ DU 2026-09-08 (#56) — LA BORNE 1 N'EST PAS ÉTENDUE, ET C'EST ÉCRIT PLUTÔT QUE
 * SUPPOSÉ. #50 l'avait resserrée en « il lit, il RÉPOND », #49 l'a étendue en « il lit, il RÉPOND, ET IL
 * PEUT AMENDER LA REQUÊTE EN MÉMOIRE — JAMAIS L'ÉTAT PERSISTANT ». #56 S'ARRÊTE EN DEÇÀ : ses trois
 * rappels LISENT ET RÉPONDENT, et rien de plus. Aucun n'amende une requête — aucun ne lit ni ne modifie
 * une variable de requête, aucun ne consulte « $_GET », « $_POST » ni aucune superglobale ; aucun
 * n'écrit — ni « update_option », ni « wp_insert_post », ni « update_post_meta », ni
 * « wp_set_object_terms », aucune règle de réécriture touchée, aucun « flush » requis, aucun état laissé
 * en base ; et aucun ne lit un compte, une option ou un registre. LA FORMULATION DE LA BORNE 1 RESTE
 * DONC MOT POUR MOT CELLE DE #49. UNE BORNE QU'ON N'ÉTEND PAS MÉRITE D'ÊTRE CONSTATÉE PAR ÉCRIT : sans
 * cette ligne, le prochain qui ajoutera un rappel ici croira l'avoir étendue sans le savoir, ou croira
 * qu'elle l'a été par #56 et s'autorisera un cran de plus sans amendement. Les bornes 2 et 3 sont
 * traitées en tête de « identite-des-comptes.php » : la 2 intacte, la 3 ÉTIRÉE et non pas « close »,
 * avec sa recommandation de renommage du module, ajournée avec son motif.
 *
 * CINQUIÈME ACTE DATÉ DU 2026-09-08 (#56) — LA FORME LIVRÉE LE MATIN FAISAIT MENTIR UN ÉCRAN.
 * « retirer_les_routes_d_identite() » amputait la table des routes INCONDITIONNELLEMENT, donc aussi pour
 * l'éleveuse authentifiée. Mesuré dans un vrai navigateur, session Éditrice, sur l'écran d'édition de la
 * page 318 dont le « post_author » vaut 1 : le panneau « Auteur/autrice » affichait « (Aucun
 * auteur/autrice) », DOUZE SCRUTATIONS SUR DOUZE, état stable. LE CONTENU A UN AUTEUR. L'écran ne
 * tombait pas, IL MENTAIT — le mode de panne de la décision 79, celui qui a bloqué le lot 20.
 * CE QUE LA FORME CORRIGÉE GARANTIT, ET C'EST LA SEULE PROPRIÉTÉ QUI COMPTE ICI : toute défaillance du
 * mécanisme — appariement raté, capacité non résoluble, filtre non chargé, « get_routes() » appelée hors
 * dispatch — LAISSE LA TABLE INTACTE. ELLE PEUT ÉCHOUER EN FUYANT, ELLE NE PEUT PAS ÉCHOUER EN MENTANT ;
 * la forme du matin avait l'inversion exacte.
 * ET LA SONDE QUI DEVAIT L'ATTRAPER NE LISAIT PAS CE QUE L'ÉCRAN AFFICHE : S1 a été jouée, dans un vrai
 * navigateur, et elle a mesuré que l'éditeur s'ouvre et ENREGISTRE — c'est vrai — puis a consigné « il
 * perd une liste, il ne tombe pas ». Entre « la liste des auteurs est vide » et « le panneau ANNONCE
 * qu'il n'y a pas d'auteur » il y a toute la différence entre une fonction perdue et une AFFIRMATION
 * FAUSSE lue par l'éleveuse. Le détail, les six faits du cœur qui portent le correctif et le motif de
 * chaque geste sont écrits en tête de « identite-des-comptes.php ».
 *
 * SIXIÈME ACTE DATÉ DU 2026-09-08 (#56) — « SIX, ET PAS SEPT » DEVIENT SEPT, ET LE SEPTIÈME N'EST PAS
 * UNE GARDE DE CEINTURE. Le troisième acte daté ci-dessus écrit « SIX, ET PAS SEPT : aucune garde de
 * ceinture n'est écrite, et il ne faut pas en écrire "au cas où" ». LA PHRASE RESTE JUSTE SUR CE QU'ELLE
 * INTERDIT, et le comptage a cessé de l'être : le septième hook de front de ce module est
 * « rest_pre_dispatch » 10, rappel « armer_le_retrait_des_routes_d_identite » (#56, correctif du
 * 2026-09-08). CE N'EST PAS UNE CEINTURE — une ceinture protège deux fois contre le même cas, et celle
 * qu'on refusait ici en était une. Celui-ci est UN ARMEMENT : il porte LA DÉCISION que le sixième hook
 * ne peut pas prendre, faute de connaître le demandeur au moment où le cœur lui passe la table. Sans lui,
 * le sixième retirait pour tout le monde ; AVEC LUI, LE SIXIÈME EST JUSTE. Un hook de plus qui rend le
 * précédent correct n'est pas un hook « au cas où », et confondre les deux ferait retirer celui-ci au
 * premier ménage.
 *
 * SEPTIÈME ACTE DATÉ DU 2026-09-08 (#56) — LA QUATRIÈME SURFACE SILENCIEUSE GAGNE DES CAUSES, PAS UNE
 * SŒUR. Il y en a toujours QUATRE, PAS CINQ : le correctif n'ouvre aucune surface neuve, il ajoute des
 * CAUSES à la quatrième. SON ÉNONCÉ CI-DESSUS PARLE DE « l'un des trois rappels » de
 * « identite-des-comptes.php » : LE FICHIER EN PORTE QUATRE DEPUIS LE CORRECTIF — trois qui retirent ou
 * substituent, un qui arme — et la phrase se lit désormais des quatre. Aux causes déjà nommées
 * s'ajoutent : le rappel d'armement non accroché ou perdu dans une reprise, un cœur qui cesserait
 * d'appliquer « rest_pre_dispatch » avant « get_routes() », un cœur qui renommerait ou remanierait ses
 * routes d'identité de sorte que l'appariement ne les couvre plus, et une capacité qui ne se
 * résoudrait pas. TOUTES CES CAUSES FONT FUIR, AUCUNE NE FAIT MENTIR —
 * c'est la propriété du cinquième acte, et c'est ce qui les rend tolérables là où l'inversion ne l'était
 * pas. RIEN NE LES DIRAIT davantage qu'avant : pas une ligne au journal, pas un écran.
 * ET LE COMPTAGE « SIX EFFETS » DU MOTIF 2 RESTE JUSTE, avec une précision qui se dit plutôt qu'elle ne
 * se lisse : depuis le correctif, l'effet 4 ne se dit plus que d'un visiteur SANS « edit_posts ». Pour un
 * compte qui a « edit_posts », ces deux routes répondent DÉJÀ — c'est le prix assumé, écrit au deuxième
 * acte daté en tête de « identite-des-comptes.php ». Ce qui revient au renommage du dossier reste donc
 * exactement ce que le module fermait : la publication à un anonyme, en une requête sans cookie.
 *
 * HUITIÈME ACTE DATÉ DU 2026-09-08 (#56) — LA BORNE 2 N'EST PLUS « INTACTE » AU SENS LITTÉRAL. Le
 * quatrième acte daté ci-dessus renvoie à « identite-des-comptes.php » pour « la 2 intacte ». RIEN N'EST
 * ÉCRIT EN BASE, et c'est toujours vrai : aucune option, aucune méta, aucun transient, aucun réglage,
 * aucune visite de « wp-admin », et le module fonctionne toujours à la seconde où le dossier arrive par
 * FTP. Mais le correctif introduit UN ÉTAT EN MÉMOIRE, pour le seul processus en cours — un loquet à sens
 * unique, « drapeau_de_retrait() ». LA PHRASE JUSTE EST « SANS ÉTAT PERSISTANT », ET CE N'EST PAS LA MÊME
 * PHRASE QUE « SANS ÉTAT ». La borne 1, elle, N'EST TOUJOURS PAS ÉTENDUE : le rappel neuf lit une route
 * et une capacité, il rend son argument inchangé sur TOUS ses chemins, il n'amende aucune requête et
 * n'écrit rien.
 *
 * MESURE D'ÉGALITÉ DU CONTRAT #24 §6.2, RELEVÉE LE 2026-09-07 : « le nombre de contenus portant
 * _mtb_robots_source, le nombre rendus noindex et le nombre retirés du plan du site sont ÉGAUX » cesse
 * d'être vrai, et ce n'est pas une régression. Le fait hérité ne PRODUIT plus la directive, il a été
 * CONVERTI. Deux nombres distincts le remplacent : 5, constante historique des contenus portant
 * « _mtb_robots_source », que l'étape 6 de « wp mtb verifier-redirections » compte EN BASE et
 * continue de vérifier ; et n, nombre vivant de contenus portant l'état « en sommeil », piloté par
 * l'éleveuse et destiné à changer.
 */

require_once __DIR__ . '/fait.php';
require_once __DIR__ . '/plan-du-site.php';
require_once __DIR__ . '/conversion.php';
require_once __DIR__ . '/archives-d-auteur.php';
require_once __DIR__ . '/identite-des-comptes.php';

/*
 * Trois accroches sur une seule fonction idempotente, parce qu'aucune ne couvre à elle seule les
 * ordres possibles entre le déploiement du code et l'import. Leur partage exact, et le motif de
 * chacune, sont écrits en tête de « conversion.php » ; ne pas en retirer une en croyant dédoublonner.
 */
add_action( 'mtb_core_mise_a_jour', __NAMESPACE__ . '\\convertir', 10, 0 );
add_action( 'added_post_meta', __NAMESPACE__ . '\\sur_arrivee_du_fait', 10, 4 );
add_action( 'updated_post_meta', __NAMESPACE__ . '\\sur_arrivee_du_fait', 10, 4 );
add_action( 'admin_init', __NAMESPACE__ . '\\rattraper', 10 );

// Exception motivée au contrat #24 §6.4, datée du 2026-09-05 : voir le bloc d'exception en tête de
// « plan-du-site.php », au-dessus de « FOURNISSEURS_RETIRES ». Priorité par défaut, aucun autre
// rappel connu sur ce crochet dans ce dépôt.
add_filter( 'wp_sitemaps_add_provider', __NAMESPACE__ . '\\ecarter_le_fournisseur_utilisateurs', 10, 2 );

// Réparation du faux 404 laissé par le retrait ci-dessus (dette T106, issue #50, 2026-09-08).
// PRIORITÉ 20, ET CE N'EST PAS QU'UNE QUESTION D'ORDRE AVEC LE CŒUR :
//   1. « WP_Sitemaps::render_sitemaps() » court en priorité 10 (relevé le 2026-09-08 dans le
//      conteneur, « wp-includes/sitemaps/class-wp-sitemaps.php:69 », accroche sans argument de
//      priorité) ; à 20 il a déjà rendu la main par son « return » nu, et nous répondons pour le
//      fournisseur que nous avons retiré. Si une version future du cœur se met à répondre
//      correctement, elle « exit » avant nous et ce rappel ne parle jamais ;
//   2. « redirect_canonical » court AUSSI en priorité 10 (« wp-includes/default-filters.php:666 »),
//      et appelle « redirect_guess_404_permalink() » quand la requête EST un 404. Poser
//      « set_404() » AVANT lui — en priorité 1, comme le module voisin — lui donnerait un 404 à
//      deviner, et « /wp-sitemap-users-1.xml » partirait en 301 vers un contenu au hasard, ce qui
//      serait PIRE que la panne d'origine. La priorité 20 laisse le devineur endormi, parce qu'il a
//      déjà statué sur une requête qui n'était pas un 404.
// Ne jamais descendre ce rappel sous la priorité de « redirect_canonical ». Pas d'« accepted_args » :
// « template_redirect » ne passe aucun argument.
add_action( 'template_redirect', __NAMESPACE__ . '\\repondre_404_au_sous_plan_retire', 20 );

// Neutralisation des archives d'auteur (dette T105, issue #49, 2026-09-08). Le détail et les neuf faits
// du cœur sont écrits en tête de « archives-d-auteur.php » ; l'ordre imposé des cinq gardes est écrit
// au-dessus du rappel lui-même, dans le même fichier.
// LE FILTRE « request », ET NON « template_redirect » 20 COMME LE RAPPEL CI-DESSUS. Le motif est
// mesuré, pas déduit : #50 a relevé que « redirect_canonical » « exit » en priorité 10
// (« wp-includes/default-filters.php:666 »), si bien qu'un rappel à 20 ne mordrait JAMAIS sur la forme
// en requête et laisserait l'oracle d'énumération des comptes — la moitié la plus dangereuse —
// entièrement ouvert, EN SILENCE. « request » court dans « WP::parse_request() »
// (« class-wp.php:409 »), donc AVANT l'action « parse_request » (l. 418), donc avant
// « rest_api_loaded() » et avant que « REST_REQUEST » ne soit défini : la garde REST de ce rappel est
// « isset( $variables['rest_route'] ) », et « defined( 'REST_REQUEST' ) » y est interdit comme
// trompeur. Le tableau n'est jamais remplacé : seules des clés nommées en sont retirées.
// ET « request » COURT AUSSI EN ADMINISTRATION. Ce renvoi affirmait le contraire ; c'était FAUX, et
// personne ne l'avait mesuré. FAIT RELEVÉ le 2026-09-08 dans le conteneur : « wp() » a EXACTEMENT DEUX
// sites d'appel dans tout « wp-admin/ » — « includes/post.php:1319 » dans « wp_edit_posts_query() » et
// « includes/post.php:1407 » dans « wp_edit_attachments_query() » — et « wp() » applique « request » par
// « WP::parse_request() ». Toute liste « edit.php » et la Médiathèque en mode liste passent donc par ce
// rappel, et l'onglet « Le mien » met une clé d'auteur dans leur adresse : sans garde, l'écran Portées
// rendait LES 33 PORTÉES SOUS UN ONGLET QUI EN ANNONCE UNE, en 404, sans un mot et sans une ligne au
// journal. LA GARDE 1 PORTE DONC « is_admin() » À CÔTÉ DE « rest_route ». Elle ne rouvre rien sur le
// front, et c'est mesuré et non déduit : « wp-admin/admin.php:104 » appelle « auth_redirect() » AVANT
// que « edit.php » n'atteigne « wp_edit_posts_query() », si bien qu'un anonyme part en 302 vers
// « wp-login.php » avec un corps de zéro octet, avant tout appel à « wp() » ; et « admin-ajax.php »
// comme « admin-post.php », les deux seuls autres contextes où « is_admin() » vaut vrai, n'appellent
// jamais « wp() ». Le détail est écrit au fait 9 en tête de « archives-d-auteur.php ».
// PRIORITÉ 10, UN ARGUMENT. Aucun autre rappel de ce crochet n'existe dans ce dépôt — vérifié par
// recherche sur « wp-content/ » le 2026-09-08 — donc aucune concurrence de priorité.
add_filter( 'request', __NAMESPACE__ . '\\neutraliser_la_requete_d_auteur', 10, 1 );

// Vie privée des comptes : ni la route REST, ni les flux, ni l'oEmbed ne publient plus une identité
// (dette T113, issue #56, 2026-09-08). Le détail, les vingt et un faits relevés et le motif de chaque garde
// — comme de chaque garde REFUSÉE — sont écrits en tête de « identite-des-comptes.php » et au-dessus de
// chacun des trois rappels ; ce renvoi ne les recopie pas, il dit ce qu'on ne verrait pas d'ici.
// TROIS RAPPELS GROUPÉS PARCE QU'ILS FERMENT UN SEUL SUJET PAR TROIS TRANSPORTS. Leur mode de panne est
// identique — une identité de compte reparaît dans un document public, en silence — et leur témoin
// aussi : un corps HTTP joué en recette. Les tenir ensemble rend « on en a oublié un » plus difficile ;
// c'est la demi-fermeture qui est le risque principal de cette issue, pas la panne franche.
// CE QUE #49 A FERMÉ, LA ROUTE REST LE PUBLIAIT ENCORE : relevé du 2026-09-08, « /wp-json/wp/v2/users »
// rend pour chaque compte un « link » vers « /author/<slug>/ » — l'adresse même que « request » fait
// répondre 404 — ET le slug, ET le nom civil de l'éleveuse. Deux mécanismes décrivant le même fait et
// divergeant en silence : le mode de panne de #52, ici fermé par le regroupement sous ce fichier.
// PRIORITÉ 10, UN ARGUMENT CHACUN, ET AUCUNE CONCURRENCE : aucun rappel de « rest_endpoints », de
// « the_author » ni de « oembed_response_data » n'existait dans « wp-content/ » — vérifié par recherche
// le 2026-09-08. Aucun ordre n'est à imposer entre ces trois-là ni avec les quatre accroches ci-dessus :
// leurs crochets courent à des instants disjoints et aucun ne lit ce qu'un autre écrit.
// AUCUNE GARDE DE CEINTURE N'EST ÉCRITE, et c'est délibéré : une garde contre un cas impossible rassure
// sans couvrir. LE MOT « MESURÉ » NE FIGURE PAS DANS CETTE PHRASE, ET C'EST VOULU : la formule est reprise
// du fait 5 de « archives-d-auteur.php », où l'impossibilité était RELEVÉE ; ici elle ne l'est pour aucune
// des deux gardes refusées — « is_admin() » l'est pour incohérence d'administration, et
// « defined( 'REST_REQUEST' ) » sur une DÉDUCTION (contrat #56 §14, point 1). Recopier « mesuré comme
// impossible » aurait attribué une mesure à un raisonnement — la faute même que ce module traque.
// Les deux gardes envisagées pour le premier rappel — « is_admin() » et
// « defined( 'REST_REQUEST' ) » — sont REFUSÉES, chacune avec son motif propre, et LE MOTIF DE #49 NE S'Y
// RECOPIE PAS : il se remplace. Le motif neuf est écrit au-dessus de « retirer_les_routes_d_identite() ».
// LA SEULE GARDE DE CONTEXTE DE CES TROIS RAPPELS EST « is_admin() » SUR LE DEUXIÈME, et elle est
// obligatoire : « wp-admin/includes/class-wp-posts-list-table.php:1284 » appelle « get_the_author() »,
// donc sans elle les écrans Pages et Articles afficheraient le titre du site comme auteur de tout, EN
// SILENCE. « LA SEULE GARDE DU LOT » AURAIT CESSÉ D'ÊTRE JUSTE LE JOUR MÊME : le correctif ajoute plus
// bas une CONDITION DE CAPACITÉ sur le rappel d'armement, et une lecture de drapeau en tête du premier
// rappel — ni l'une ni l'autre n'est une garde de CONTEXTE, et c'est pourquoi la phrase est bornée aux
// trois rappels ci-dessous plutôt que rayée.
add_filter( 'rest_endpoints', __NAMESPACE__ . '\\retirer_les_routes_d_identite', 10, 1 );
add_filter( 'the_author', __NAMESPACE__ . '\\substituer_le_nom_d_auteur', 10, 1 );
add_filter( 'oembed_response_data', __NAMESPACE__ . '\\substituer_l_auteur_oembed', 10, 1 );

// NE JAMAIS « HARMONISER » LES QUATRE RAPPELS DE CE GROUPE. Ils se ressemblent de loin et n'ont ni le
// même rôle, ni les mêmes gardes — et ils sont NOMMÉS plutôt que numérotés, l'ordre d'accroche ci-dessus
// n'étant pas l'ordre d'exécution : « armer_le_retrait_des_routes_d_identite() » ARME,
// « retirer_les_routes_d_identite() » RETIRE, « substituer_le_nom_d_auteur() » SUBSTITUE sous une garde
// « is_admin() », « substituer_l_auteur_oembed() » ÉCRASE sans garde. Uniformiser leurs gardes casserait
// « is_admin() » sur « substituer_le_nom_d_auteur() » — LA GARDE LA PLUS GRAVE DU FICHIER : sans elle,
// les écrans Pages et Articles afficheraient le titre du site comme auteur de toutes les lignes, en
// silence. Et l'appariement de route du rappel d'armement ne se recopie NULLE PART ailleurs : sous
// « rest_endpoints », le même geste détruirait quatre routes sur six (interdit gelé du §15).
//
// ARMEMENT DU RETRAIT REST (correctif de régression, #56, 2026-09-08). Le rappel de « rest_endpoints »
// ci-dessus retirait pour TOUT LE MONDE, éleveuse comprise : son écran d'édition annonçait « (Aucun
// auteur/autrice) » sur un contenu qui a un auteur. Ce rappel-ci décide, AVANT que la table des routes
// ne soit calculée, s'il faudra l'amputer ; « rest_pre_dispatch » est le PREMIER geste de
// « WP_REST_Server::dispatch() » (« class-wp-rest-server.php:1078 ») et la table n'est demandée qu'en
// l. 1167, sur un « get_routes() » QUI N'EST PAS MÉMOÏSÉ (l. 956-973). L'utilisateur courant y est déjà
// résolu SUR LE CHEMIN DE « serve_request() » — « check_authentication() » l. 436, « dispatch() »
// l. 439 ; sur les chemins qui passent par « rest_do_request() », il l'est PAR DÉDUCTION du contexte
// appelant, et la portée exacte de ce fait est écrite sous les faits 2 et 3 en tête de
// « identite-des-comptes.php ».
// IL NE COURT-CIRCUITE JAMAIS : il rend son premier argument inchangé sur tous ses chemins, y compris
// quand il arme. Rendre autre chose avalerait le court-circuit d'un tiers, ou fabriquerait un corps de
// notre main — l'oracle de second ordre pour lequel l'option B du §4.7 a été écartée.
// TROIS ARGUMENTS, PARCE QUE LA REQUÊTE EST LE TROISIÈME. PRIORITÉ 10 : aucun autre rappel de
// « rest_pre_dispatch » n'existe dans « wp-content/ » — vérifié par recherche le 2026-09-08 — donc
// aucune concurrence, et aucun ordre à imposer avec les trois accroches ci-dessus.
// LA CAPACITÉ EST « edit_posts » ET LA GARDE EST SOUSTRACTIVE : le cœur accorde déjà la collection
// « users » à un anonyme par défaut, si bien que ce rappel ne donne JAMAIS plus que le cœur — il ne fait
// que retirer. Son pire échec est « nous n'avons rien retiré ». Le motif de cette capacité-là plutôt que
// « list_users » ou « edit_others_posts », et l'inversion de sûreté qui commande d'apparier
// GÉNÉREUSEMENT ici et LITTÉRALEMENT sous « rest_endpoints », sont écrits au-dessus du rappel lui-même.
add_filter( 'rest_pre_dispatch', __NAMESPACE__ . '\\armer_le_retrait_des_routes_d_identite', 10, 3 );
