<?php
/**
 * Vie privée des comptes : ni la route REST, ni les flux, ni l'oEmbed ne publient plus une identité.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\IndexationHeritee;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * ─────────────────────────────────────────────────────────────────────────────────────────────
 * POURQUOI CE MÉCANISME VIT DANS « indexation-heritee », ET POURQUOI IL A SON PROPRE FICHIER
 * DÉCIDÉ LE 2026-09-08 (#56), APRÈS MESURE — dette T113
 *
 * L'OBJECTION EST RÉELLE, ET ELLE EST ÉCRITE PLUTÔT QUE TUE — POUR LA DEUXIÈME FOIS. #49 reconnaissait
 * déjà que son installation ici « frôlait » le motif 1 gelé en tête de « bootstrap.php ». #56 ÉTIRE LA
 * BORNE 3 D'UN CRAN DE PLUS, et il vaut mieux l'écrire que le laisser découvrir : la route
 * « wp/v2/users » n'a RIEN d'un héritage de l'ancien site — c'est une fonctionnalité de WordPress nu —
 * et, contrairement à « /author/admin/ » que le bloc d'exception du 2026-09-05 nommait EXPRESSÉMENT,
 * ELLE N'A JAMAIS ÉTÉ NOMMÉE PAR AUCUN ACTE DE CE MODULE. Les flux et l'oEmbed non plus.
 *
 * CE QUI TRANCHE POUR RESTER ICI, ET C'EST UN ARGUMENT PLUS FORT QUE CELUI DE #49 :
 *   1. LA ROUTE REST PUBLIE LITTÉRALEMENT L'ADRESSE QUE #49 A FERMÉE. Relevé du 2026-09-08, corps de
 *      « /wp-json/wp/v2/users » : chaque compte y porte un « link » vers « /author/<slug>/ », ET son
 *      slug, ET son nom. Fermer « /author/admin/ » en 404 pendant qu'une autre adresse en publie l'URL
 *      et le slug, C'EST EXACTEMENT LE MODE DE PANNE DE #52 : deux mécanismes décrivant le même fait et
 *      divergeant en silence. La fermeture de #49 n'était donc entière que sur la moitié qu'elle voyait.
 *   2. SOUS UN SEUL « bootstrap.php », LES SIX EFFETS TOMBENT ET SE RELÈVENT ENSEMBLE. Séparer les
 *      mécanismes ferait qu'un renommage du dossier en « _indexation-heritee », ou une reprise
 *      maladroite, rouvrirait une moitié en laissant l'autre fermée — l'état le plus difficile à voir
 *      et le plus facile à rapporter comme sain.
 *
 * LE FICHIER EST NEUF, ET C'EST LA MÊME RÈGLE À L'ÉCHELLE DU FICHIER. Trois fichiers — un par transport,
 * REST, flux, oEmbed — rangeraient par TUYAU ce qui est UN SEUL SUJET : une identité de compte publiée à
 * un visiteur. Un fichier nomme la propriété livrée, pas la plomberie. Et agrandir « archives-d-auteur.php »
 * mentirait sur son contenu : UNE ROUTE REST N'EST PAS UNE ARCHIVE D'AUTEUR — motif 1 appliqué au
 * fichier, précédent #49. MODULE N'EST PAS FICHIER : la cohésion du point 2 tient au « bootstrap.php »
 * commun, pas au partage d'un même fichier.
 *
 * LES BORNES, DITES UNE PAR UNE PLUTÔT QUE RÉSUMÉES EN « PÉRIMÈTRE CLOS ».
 * BORNE 1 : NON ÉTENDUE. Les trois rappels de ce fichier LISENT ET RÉPONDENT ; aucun n'amende une
 * requête, aucun n'écrit quoi que ce soit — ni option, ni méta, ni terme, ni règle de réécriture. Le
 * constat est porté par écrit dans « bootstrap.php », parce qu'une borne qu'on n'étend pas mérite d'être
 * constatée : sans cela, le prochain croira l'avoir étendue sans le savoir.
 * BORNE 2 : INTACTE. Aucun des trois ne dépend d'un état en base, ne lit une option, un registre ou un
 * compte ; ils fonctionnent à la seconde où le dossier arrive par FTP, sans réglage et sans visite de
 * « wp-admin ».
 * BORNE 3 : ÉTIRÉE, et l'écrire vaut mieux que d'inscrire « périmètre clos et daté » une quatrième fois.
 * Le périmètre reste nommé et daté — deux clés de route littérales, le nom d'auteur des flux, deux
 * champs d'un document oEmbed — mais le module porte désormais TROIS SUJETS (reprise d'indexation, plan
 * du site, vie privée des comptes) sous un nom qui n'en annonce qu'un. UN RENOMMAGE SERAIT HONNÊTE ;
 * il périmerait des citations par nom dans trois contrats gelés. AJOURNÉ AVEC SON MOTIF, à trancher par
 * le lead AVANT le prochain ajout — pas dans cette issue.
 * ─────────────────────────────────────────────────────────────────────────────────────────────
 */

/*
 * QUINZE FAITS DU CŒUR, RELEVÉS DANS LE CONTENEUR LE 2026-09-08, WordPress 6.9. AUCUN N'EST DÉDUIT.
 *
 *    1. « wp-includes/rest-api/class-wp-rest-server.php:973 » —
 *       « $endpoints = apply_filters( 'rest_endpoints', $endpoints ); », À L'INTÉRIEUR de
 *       « get_routes() » (l. 956). Le filtre ne décore pas une réponse : il façonne LA TABLE DES ROUTES.
 *    2. Même fichier — « get_routes() » est appelée par « dispatch() » (l. 1167), PAR L'INDEX (l. 1373)
 *       et en l. 1527. L'APPELANT DE CETTE TROISIÈME LIGNE N'EST PAS IDENTIFIÉ, et c'est dit plutôt que
 *       tu : sous un retrait INCONDITIONNEL ce trou n'a aucune importance ; sous une condition de
 *       capacité, il faudrait savoir dans quel contexte d'utilisateur elle court — une prémisse de
 *       contexte non mesurée, exactement la famille qui a cassé le lot 20.
 *    3. Même fichier — « serve_request() » : l. 436 « $result = $this->check_authentication(); » PUIS
 *       l. 439 « $result = $this->dispatch( $request ); ». L'AUTHENTIFICATION COURT AVANT LA TABLE DES
 *       ROUTES : un retrait de route ne peut donc pas être « rattrapé » par la session.
 *    4. Même fichier, l. 1219 — « 'rest_no_route' », LA RÉPONSE DU CŒUR POUR UNE ROUTE ABSENTE. C'est
 *       elle qui rend le correctif indiscernable à l'octet, et c'est l'argument décisif du fichier :
 *       fabriquer notre propre « WP_Error » rendrait un corps différent de celui d'une route absente,
 *       c'est-à-dire UN ORACLE NEUF — « cette route a été retirée » ne se lit pas comme « cette route
 *       n'existe pas ». On ne remplace pas un oracle par un oracle plus discret.
 *    5. « wp-includes/rest-api.php:153 » —
 *       « $full_route = '/' . $clean_namespace . '/' . trim( $route, '/' ); ». C'EST LA CLÉ EXACTE du
 *       tableau reçu par le filtre : une chaîne, pas un motif, pas un objet.
 *    6. « wp-includes/rest-api/endpoints/class-wp-rest-users-controller.php » — l. 42
 *       « $this->rest_base = 'users'; », et TROIS « register_rest_route » DISTINCTS : l. 58
 *       « '/' . $this->rest_base », l. 79 « '/' . $this->rest_base . '/(?P<id>[\d]+)' », l. 126
 *       « '/' . $this->rest_base . '/me' », CETTE DERNIÈRE AVEC
 *       « 'permission_callback' => '__return_true' ». Trois routes déclarées séparément : elles se
 *       retirent séparément, et il n'y a aucune raison d'en retirer une quatrième.
 *    7. « wp-includes/feed-rss2.php:95 » — « <dc:creator><![CDATA[<?php the_author(); ?>]]></dc:creator> ».
 *    8. « wp-includes/feed-rdf.php:78 » — la même ligne, le même champ, le même CDATA.
 *    9. « wp-includes/feed-atom.php:56 » — « <name><?php the_author(); ?></name> », SANS CDATA, et un
 *       « <uri> » sous condition en l. 58-61. Troisième gabarit, troisième règle d'échappement.
 *   10. « wp-includes/feed-rss2-comments.php:98 » — « get_comment_author_rss() » : AUTEUR DE
 *       COMMENTAIRE, PAS UN COMPTE. Autre fonction, hors périmètre. Relevé pour que personne ne croie
 *       ce fichier responsable de « /comments/feed/ », qui doit rester identique à l'octet.
 *   11. « wp-includes/author-template.php:38 » —
 *       « return apply_filters( 'the_author', is_object( $authordata ) ? $authordata->display_name : '' ); ».
 *       UN SEUL FILTRE COUVRE LES TROIS GABARITS 7, 8 ET 9 : aucun fichier du cœur n'a donc à être
 *       recopié. ET LE CHAMP PUBLIE LE « display_name », PAS LE « user_login » — l'énoncé de l'issue
 *       disait le contraire, et le croire aurait mené à recopier un gabarit pour rien. Ils coïncident
 *       sur le compte 1 de cette base, voilà tout.
 *   12. « wp-includes/embed.php:604-607 » — « 'author_name' => get_bloginfo( 'name' ) » et
 *       « 'author_url' => get_home_url() », PAR DÉFAUT ; puis l. 612-615, DÈS QU'UN AUTEUR EST RÉSOLU :
 *       « $data['author_name'] = $author->display_name; » et
 *       « $data['author_url'] = get_author_posts_url( $author->ID ); ». #49 §9 avait conclu ce champ
 *       inoffensif SUR LA FOI DE L'ACCUEIL SEUL, qui tombe sur la branche par défaut ; sur tout contenu
 *       singulier, c'est la seconde branche qui parle. Conclusion juste pour la seule URL mesurée,
 *       fausse en général.
 *   13. « wp-includes/embed.php:627 » —
 *       « return apply_filters( 'oembed_response_data', $data, $post, $width, $height ); ».
 *   14. « wp-includes/embed.php:782-810 » — « _oembed_rest_pre_serve_request() » convertit en XML des
 *       données DÉJÀ ASSEMBLÉES : l. 789 teste le format, l. 801 appelle « _oembed_create_xml( $data ) ».
 *       LE FILTRE 13 COURT DONC AVANT LA MISE EN FORME : UN SEUL RAPPEL COUVRE « format=json » ET
 *       « format=xml ». Mesuré sur les deux formes, pas déduit de l'une.
 *   15. « wp-admin/includes/class-wp-posts-list-table.php:1284 » — « $author = get_the_author(); ».
 *       LA COLONNE « Auteur » DES ÉCRANS DE LISTE PASSE PAR LE FILTRE 11, et l'observable le confirme :
 *       en session, l'écran Pages rend « <td class="author column-author" …>…admin</a> ». C'est ce fait,
 *       et lui seul, qui rend obligatoire la garde 1 du deuxième rappel.
 *
 * CES NUMÉROS DE LIGNE SONT ÉPINGLÉS À WordPress 6.9. « wp-includes/ » et « wp-admin/ » n'étant pas
 * versionnés dans ce dépôt, ILS SE PÉRIMERONT EN SILENCE à la prochaine montée de version — sans une
 * erreur, sans une ligne au journal, et sans que le correctif cesse forcément de mordre au même instant.
 * C'est la dette T114 (issue #57), que ce fichier ALIMENTE de quinze ancres de plus et ne solde pas.
 * Résidu nommé, non masqué.
 */

/*
 * ACTE DATÉ DU 2026-09-08 (#56, correctif de régression) — SIX FAITS DE PLUS, RELEVÉS DANS LE CONTENEUR,
 * WordPress 6.9 : CINQ DU CŒUR (faits 16 à 20) ET UN DU DÉPÔT (fait 21). AUCUN N'EST DÉDUIT, ET LA SEULE
 * CONSÉQUENCE DÉDUITE — celle du fait 19 — EST MARQUÉE COMME TELLE À SA LIGNE. AUCUN N'EN REMPLACE UN
 * DES QUINZE CI-DESSUS : le bloc précédent n'est pas amputé, il est augmenté. Le comptage « quinze »
 * était juste à sa date ; les faits relevés dans ce fichier sont désormais VINGT ET UN, DONT VINGT SONT
 * DES ANCRES DU CŒUR.
 *
 *   16. « wp-includes/rest-api/class-wp-rest-server.php:1062 » — « dispatch() » ; et l. 1078
 *       « apply_filters( 'rest_pre_dispatch', null, $this, $request ) », PREMIER FILTRE DE LA MÉTHODE
 *       — ET NON SA PREMIÈRE INSTRUCTION : « dispatch() » ouvre en l. 1062 sur
 *       « $this->dispatching_requests[] = $request; ». La nuance ne change RIEN à la conclusion, et
 *       elle s'écrit quand même : une ancre qui fait dire à une ligne un peu plus que ce qu'elle dit
 *       est la dette exacte que ce module paie depuis deux lots. Rectifié sur relevé le 2026-09-08,
 *       avant tout commit. La table des routes, elle, n'est calculée qu'en l. 1167
 *       (« $routes = $this->get_routes(); »). L'ARMEMENT PRÉCÈDE DONC LE CALCUL DE LA TABLE, DANS LE
 *       MÊME DISPATCH : c'est ce fait, et lui seul, qui rend ce correctif possible sans rien mémoriser
 *       d'un processus à l'autre. Le premier argument du filtre vaut « null » : le cœur y attend un
 *       court-circuit éventuel, jamais un ordre.
 *   17. Même fichier, l. 956-973 — « get_routes() » ouvre sur « $endpoints = $this->endpoints; » puis
 *       applique « rest_endpoints ». AUCUNE MÉMOÏSATION : le filtre est réappliqué À CHAQUE APPEL, sur
 *       la table brute. Un armement posé entre deux appels mord donc sur le second, et le premier reste
 *       intact — la propriété exacte dont ce correctif a besoin.
 *   18. Même fichier, l. 1838 — SECONDE APPLICATION de « rest_pre_dispatch », pour les requêtes
 *       groupées. Le rappel d'armement doit donc supporter d'être appelé PLUSIEURS FOIS par processus ;
 *       il le supporte par construction, un loquet à sens unique étant idempotent.
 *   19. « wp-includes/rest-api.php:592-594 » — « rest_do_request() » appelle
 *       « rest_get_server()->dispatch( $request ) ». CE SEUL ÉNONCÉ EST L'ANCRE ; CE QUI SUIT EN EST UNE
 *       DÉDUCTION, ET ELLE EST MARQUÉE PLUTÔT QUE LISSÉE. SI les sous-requêtes de « ?_embed=1 »
 *       empruntent « rest_do_request() », ALORS elles passent par « dispatch() », donc par
 *       « rest_pre_dispatch », donc par leur propre « get_routes() » (fait 17), et la porte de derrière
 *       se fermerait du même geste, sans un rappel de plus. CE QUE LA MESURE PORTE, ET CE QU'ELLE NE
 *       PORTE PAS : M-B a été relevée le 2026-09-08 sous la forme INCONDITIONNELLE du matin (contrat
 *       #56, amendement §A) — « _embedded.author » y portait « rest_no_route » ; QUE LA FORME ARMÉE
 *       FERME LA MÊME PORTE N'EST PAS REJOUÉ ICI, et le chemin exact de ces sous-requêtes n'a pas été
 *       ouvert dans le conteneur. L'écrire au présent de l'indicatif serait la faute qui a bloqué le
 *       lot 20.
 *   20. « wp-includes/rest-api.php:1417-1419 » — « rest_authorization_required_code() » rend
 *       « is_user_logged_in() ? 403 : 401 ». C'EST LE CŒUR QUI REFUSE une route préservée, jamais nous :
 *       relevé pour que personne ne croie devoir écrire un refus de sa main sur « /users/me ».
 *   21. AUCUN AUTRE RAPPEL DE « rest_pre_dispatch » n'existe dans « wp-content/ » — vérifié par
 *       recherche le 2026-09-08. Aucune concurrence de priorité, aucun ordre à imposer.
 *
 * LES FAITS 2 ET 3 SONT RAPPELÉS ICI PARCE QU'ILS PORTENT LE CORRECTIF, ET NON RECOMPTÉS. Le fait 3
 * (l. 436 « check_authentication() » PUIS l. 439 « dispatch() ») établit que L'UTILISATEUR COURANT EST
 * DÉJÀ RÉSOLU quand « rest_pre_dispatch » court : sans lui, « current_user_can() » y statuerait sur un
 * utilisateur vide et armerait pour tout le monde, y compris pour l'éleveuse. Le fait 2 établit les
 * appels de « get_routes() » HORS dispatch — l. 1373 pour l'index, l. 1527 dont l'appelant reste non
 * identifié : sur ceux-là le drapeau est DÉSARMÉ, et l'acte daté suivant dit pourquoi c'est le bon côté
 * de l'erreur.
 *
 * PORTÉE EXACTE DU FAIT 3, ET ELLE N'EST PAS TOUTE LA COUVERTURE. Il est relevé DANS « serve_request() » :
 * il vaut pour une requête REST SERVIE PAR HTTP, et pour elle seule. Les appels de « dispatch() » qui ne
 * viennent pas de là — préchargement de l'éditeur de blocs en administration, sous-requêtes de
 * « ?_embed=1 » par « rest_do_request() » (fait 19) — N'ONT AUCUNE ANCRE ICI : que le demandeur y soit
 * déjà résolu, et donc que « current_user_can() » y statue sur lui, se DÉDUIT du contexte appelant et ne
 * se lit dans aucune ligne relevée du cœur. CE QUI EST MESURÉ SUR CE CHEMIN EST UN OBSERVABLE, PAS UNE
 * LIGNE DU CŒUR : après correctif, en session Éditrice et dans un vrai navigateur piloté, le panneau
 * « Auteur/autrice » de la page 318 affiche de nouveau son auteur — la même sonde qui avait lu
 * « (Aucun auteur/autrice) ». UNE ANCRE DU CŒUR ET UN OBSERVABLE NE PROUVENT PAS LA MÊME CHOSE, et les
 * confondre est la faute que ce module traque.
 *
 * CINQ ANCRES DE PLUS ÉPINGLÉES À WordPress 6.9 — les faits 16 à 20 —, qui SE PÉRIMERONT EN SILENCE
 * comme les quinze autres. LE FAIT 21 N'EN EST PAS UNE : c'est un relevé de « wp-content/ », qui se
 * périmerait par une reprise du dépôt et jamais par une montée de version du cœur. Dette T114
 * (issue #57), alimentée de cinq, non soldée.
 *
 * ACTE DATÉ DU 2026-09-17 (#57) — TROIS PHRASES DE CE FICHIER SE LISENT DÉSORMAIS AVEC CET ACTE.
 *   1. « ILS SE PÉRIMERONT EN SILENCE à la prochaine montée de version — sans une erreur, sans une ligne
 *      au journal » et « C'est la dette T114 […], que ce fichier ALIMENTE […] et ne solde pas » (fin
 *      des quinze faits), puis « CINQ ANCRES DE PLUS […], qui SE PÉRIMERONT EN SILENCE » et « Dette
 *      T114 […], alimentée de cinq, non soldée » (paragraphe ci-dessus). T114 EST TRAITÉE PAR #57, DANS LA PILE
 *      DOCKER DE DÉVELOPPEMENT : « docker/provision/temoin-ancres.sh », lancé à chaque provisionnement,
 *      compare chaque fichier du cœur cité avec un numéro de ligne à son empreinte relevée dans
 *      « docker/provision/ancres-coeur.txt », et les fichiers des faits 1 à 20 y sont tous inscrits. Si
 *      l'un change, le journal de wpcli porte « ANCRES DU CŒUR : ALERTE » : il y a donc désormais une
 *      ligne au journal. CE QUI RESTE VRAI : la production n'exécute pas ce témoin ; il dit « le fichier
 *      a changé », jamais « la ligne a bougé » ni « le rappel ne mord plus » — « sans que le correctif
 *      cesse forcément de mordre au même instant » est donc toujours juste, dans les deux sens (T115,
 *      #58) ; et il atteste l'état des fichiers au 2026-09-17, pas la justesse de chaque numéro relevé le
 *      2026-09-08.
 *   2. « il périmerait des citations par nom dans trois contrats gelés » (borne 3, en tête). LE DÉCOMPTE
 *      ÉTAIT FAUX PAR DÉFAUT : le nom du module est écrit 77 fois dans 11 contrats gelés avant #57, et
 *      79 fois dans 12 contrats en comptant « docs/contracts/issue-57.md ». Relevé du 2026-09-17 ; les
 *      commandes sont recopiées sous le motif 1 de « bootstrap.php ». LE
 *      RENOMMAGE « AJOURNÉ […], à trancher par le lead AVANT le prochain ajout » EST TRANCHÉ : pas de
 *      renommage, et le périmètre du module se lit désormais par le critère de ce même acte.
 *   3. CE QUI NE CHANGE PAS : les phrases sur un GABARIT DU CŒUR RECOPIÉ, qui « se périme en silence à
 *      chaque montée de version », restent vraies. Une copie vivrait dans le dépôt, et ce témoin ne
 *      surveille que des fichiers du cœur ; il ne la verrait pas diverger de son original. Une ancre du
 *      cœur garde aussi son coût : elle s'inscrit au registre et se relit à chaque ALERTE.
 */

/*
 * ─────────────────────────────────────────────────────────────────────────────────────────────
 * ACTE DATÉ DU 2026-09-08 (#56) — LA FORME LIVRÉE CE JOUR-LÀ FAISAIT MENTIR UN ÉCRAN. CORRECTIF.
 *
 * CE QUI A ÉTÉ LIVRÉ, ET CE QUE ÇA A FAIT. « retirer_les_routes_d_identite() » amputait la table des
 * routes INCONDITIONNELLEMENT — donc aussi pour l'éleveuse authentifiée. Mesuré dans un vrai navigateur
 * piloté, session Éditrice, sur l'écran d'édition de la page 318, dont le « post_author » vaut 1 : le
 * panneau « Auteur/autrice » affichait « (Aucun auteur/autrice) », DOUZE SCRUTATIONS SUR DOUZE, état
 * stable ; en session, « wp.apiFetch( '/wp/v2/users/1' ) » rendait « rest_no_route ». LE CONTENU A UN
 * AUTEUR. L'ÉCRAN NE TOMBAIT PAS : IL MENTAIT. C'est le mode de panne de la décision 79, celui qui a
 * bloqué le lot 20, et c'est la seule raison pour laquelle ce correctif existe.
 *
 * POURQUOI LA SONDE NE L'A PAS ATTRAPÉ, ET C'EST LA LEÇON LA PLUS CHÈRE DE L'ÉPISODE. La sonde S1 a bel
 * et bien été JOUÉE, dans un vrai navigateur, et elle a rendu « ENREGISTRE ok ». Elle a mesuré que
 * l'éditeur S'OUVRE ET ENREGISTRE — c'est vrai — et que « getUsers({who:'authors'}) » rend « null », ce
 * qui a été consigné comme « il perd une liste, il ne tombe pas ». ELLE N'A PAS LU CE QUE L'ÉCRAN
 * AFFICHE. Entre « la liste des auteurs est vide » et « le panneau ANNONCE qu'il n'y a pas d'auteur », il
 * y a toute la différence entre une fonction perdue et une AFFIRMATION FAUSSE lue par l'éleveuse. Une
 * sonde qui interroge l'API du navigateur sans jamais lire le texte rendu peut rapporter « acceptable »
 * sur un écran qui ment.
 *
 * LA PROPRIÉTÉ ARCHITECTURALE DE LA FORME CORRIGÉE, ET ELLE VAUT PLUS QUE LE CORRECTIF LUI-MÊME.
 * TOUTE DÉFAILLANCE DE CE MÉCANISME — appariement raté, capacité non résoluble, filtre non chargé,
 * « get_routes() » appelée hors dispatch — LAISSE LE DRAPEAU DÉSARMÉ, DONC LA TABLE INTACTE. CETTE FORME
 * PEUT DONC ÉCHOUER EN FUYANT ; ELLE NE PEUT PAS ÉCHOUER EN MENTANT. La forme livrée le 2026-09-08 avait
 * L'INVERSION EXACTE — toute défaillance y laissait la table amputée — et c'est ce qui a fait afficher
 * « (Aucun auteur/autrice) » sur un contenu qui avait un auteur.
 *
 * CE QUE CE CORRECTIF ABANDONNE, DIT SANS L'ADOUCIR — DEUX CHOSES, ET AUCUNE N'EST ARRONDIE.
 *   1. L'INDEX « /wp-json/ » REDEVIENT ENTIER, POUR TOUT LE MONDE. La route de l'index est « / » : elle
 *      ne commence pas par « /wp/v2/users », le drapeau n'est donc jamais armé pour elle et sa table est
 *      intacte. Conséquence à dire plutôt qu'à taire : L'INDEX ANNONCE LES DEUX ROUTES QUE LE DISPATCH
 *      REFUSE À UN ANONYME. Le §4.4 du contrat gelé appelait cela une incohérence — « annoncée mais
 *      absente » ne se lit que d'une façon — et LA CIBLE P4 DU §7.1, qui exige que l'index ne porte que
 *      les quatre clés préservées, ÉCHOUE DÉSORMAIS SANS QU'AUCUNE RÉGRESSION N'AIT EU LIEU. L'échange
 *      est nommé : on troque un signal de second ordre — « quelqu'un a retiré quelque chose » — contre
 *      l'empoisonnement d'index en cache que le §4.1 refusait, ET contre un écran qui ment. AUCUNE
 *      IDENTITÉ DE COMPTE NE SORT PAR CET INDEX : il ne publie que des clés de route.
 *   2. UN DEMANDEUR PORTANT « edit_posts » REÇOIT DE NOUVEAU LES DEUX ROUTES. La propriété mesurée de
 *      l'issue — « le nom civil de l'éleveuse n'est plus publié en une requête SANS COOKIE » — tient
 *      entière ; celle qu'on pourrait croire livrée — « plus personne ne lit jamais ces routes » — n'a
 *      jamais été la propriété défendue, et c'est justement l'éleveuse que la forme précédente privait de
 *      son propre écran.
 *
 * BORNE 2 — RELUE, ET LA PHRASE CHANGE D'UN MOT. Elle se lit plus haut « INTACTE. Aucun des trois ne
 * dépend d'un état en base ». RIEN N'EST ÉCRIT EN BASE, et cela reste vrai : ni option, ni méta, ni
 * transient, aucun réglage, aucune visite de « wp-admin », aucune règle de réécriture. Mais ce fichier
 * porte désormais UN ÉTAT EN MÉMOIRE, pour le seul processus en cours — voir « drapeau_de_retrait() ».
 * LA PHRASE JUSTE EST DONC « SANS ÉTAT PERSISTANT », ET CE N'EST PAS LA MÊME PHRASE QUE « SANS ÉTAT ».
 *
 * BORNE 1 — LE COMPTAGE SEUL CHANGE, ET LA BORNE PAS DU TOUT. Elle se lit plus haut « Les trois rappels
 * de ce fichier LISENT ET RÉPONDENT », et la borne 2 « Aucun des trois » : ILS SONT QUATRE depuis ce
 * correctif — trois qui retirent ou substituent, un qui ARME. LA BORNE N'EST PAS ÉTENDUE POUR AUTANT :
 * le rappel neuf lit une route et une capacité, rend son argument INCHANGÉ sur tous ses chemins,
 * n'amende aucune requête et n'écrit rien. Le motif complet est au huitième acte daté de
 * « bootstrap.php ».
 * ─────────────────────────────────────────────────────────────────────────────────────────────
 */

/**
 * Loquet à sens unique : dit si la table des routes doit être amputée dans le processus en cours.
 *
 * NON ACCROCHÉE. Ce n'est pas un rappel, c'est LE SEUL STOCKAGE DE CE MODULE. Elle est armée par
 * « armer_le_retrait_des_routes_d_identite() » et lue par « retirer_les_routes_d_identite() ».
 *
 * POURQUOI UN « static » DE FONCTION, ET NON AUTRE CHOSE. Trois formes ont été envisagées, trois sont
 * refusées, chacune avec son motif :
 *   UN « add_filter » POSÉ DYNAMIQUEMENT — refusé. Le §10 du contrat gèle « Hooks offerts au thème :
 *   AUCUN », et UN DRAPEAU QUI EST UN HOOK EST UN HOOK : n'importe quel fichier du site pourrait
 *   l'armer, le désarmer ou en changer la priorité, et le mécanisme le plus grave du module deviendrait
 *   un point d'extension que personne n'a voulu.
 *   UNE VARIABLE GLOBALE — refusée : inscriptible de partout, par n'importe quel code, sans une trace et
 *   sans un grep qui le montre.
 *   UNE PROPRIÉTÉ STATIQUE DE CLASSE — refusée : ce module n'a AUCUNE classe, et en introduire une pour
 *   porter un booléen ferait entrer une famille de formes entière là où il n'y en avait pas.
 *
 * LE PRIX PAYÉ, ÉCRIT PLUTÔT QUE TU : cette fonction A DEUX RÔLES — poser et lire — ce qu'un nom seul ne
 * dit pas, et ce qu'un successeur pressé lira mal une fois. Elle l'assume parce que c'est le seul
 * stockage à la fois PRIVÉ AU MODULE (portée de fonction), NON INSCRIPTIBLE DE L'EXTÉRIEUR, HORS BASE ET
 * HORS GLOBAL.
 *
 * BORNE 2, ET LA NUANCE EST TOUT LE SUJET : L'ÉTAT EST EN MÉMOIRE, POUR LE SEUL PROCESSUS EN COURS.
 * Aucune option, aucune méta, aucun transient, RIEN EN BASE — donc rien à nettoyer, rien à migrer, rien
 * qui survive à la requête. Le module devient « SANS ÉTAT PERSISTANT », ce qui n'est pas la même phrase
 * que « sans état », et il vaut mieux l'écrire que de laisser un successeur citer l'ancienne.
 *
 * POURQUOI AUCUN DÉSARMEMENT, JAMAIS. Un désarmement posé TROP TARD serait décoratif : la table est déjà
 * calculée, le mal — ou le bien — est fait. Posé TROP TÔT, il laisserait passer la route pour un
 * anonyme : UNE FUITE SILENCIEUSE, c'est-à-dire exactement le défaut que ce fichier existe pour fermer.
 * Et il n'y a rien à désarmer : L'ARMEMENT EXIGE UN DEMANDEUR SANS « edit_posts », OR LE DEMANDEUR NE
 * CHANGE PAS AU SEIN D'UN PROCESSUS. Le loquet est donc à sens unique par construction, et idempotent —
 * ce que le fait 18 réclame, « rest_pre_dispatch » pouvant s'appliquer deux fois dans le même processus.
 *
 * @param bool $armer Vrai pour ARMER le retrait. Ne désarme jamais, quelle que soit la valeur.
 *
 * @return bool Vrai si le retrait a été armé dans ce processus.
 */
function drapeau_de_retrait( bool $armer = false ): bool {
	static $arme = false;

	if ( true === $armer ) {
		$arme = true;
	}

	return $arme;
}

/**
 * Arme le retrait des routes d'identité quand le demandeur de cette requête REST n'a pas « edit_posts ».
 *
 * NE RETIRE RIEN, NE RÉPOND RIEN, NE COURT-CIRCUITE JAMAIS. Il décide seulement, AVANT que la table des
 * routes ne soit calculée (fait 16), s'il faudra l'amputer ; c'est
 * « retirer_les_routes_d_identite() » qui ampute, et lui seul.
 *
 * L'INVERSION DE SÛRETÉ — LE CŒUR DU CORRECTIF, ET LA PHRASE À LIRE AVANT DE TOUCHER À CE FICHIER.
 * Sous « rest_endpoints », l'appariement décide CE QU'ON RETIRE : sur-apparier DÉTRUIT DES ROUTES — un
 * préfixe aurait tué quatre routes sur six, dont « /users/me » — la panne y est catastrophique et
 * INVISIBLE EN ANONYME. D'où l'interdit gelé du §15, reconduit ENTIER dans l'autre fonction.
 * Sous « rest_pre_dispatch », l'appariement décide seulement SI ON ARME : le retrait, lui, reste deux
 * « unset() » littéraux. SUR-APPARIER EST DONC INOFFENSIF — armer sur « /users/me » ne retire pas
 * « /me », sa clé n'étant dans aucun « unset() » — ET SEUL LE SOUS-APPARIEMENT FUIT. ON APPARIE DONC
 * GÉNÉREUSEMENT, exactement à l'envers de la fonction voisine.
 * MÊME MOT, MOTIF OPPOSÉ. L'ÉCRIRE, SINON UN SUCCESSEUR APPLIQUERA LE MAUVAIS INTERDIT — c'est
 * exactement ce que le §4.3 a dû faire pour « REST_REQUEST », dont le verdict se recopiait et le motif
 * non.
 *
 * CE QUI EST VOLONTAIREMENT SUR-APPARIÉ, ET POURQUOI C'EST SANS CONSÉQUENCE :
 *   « /wp/v2/users/me » — armée, JAMAIS RETIRÉE : sa clé n'entre dans aucun « unset() ». La route
 *   résout, et c'est son « permission_callback » qui répond, par le code du cœur (fait 20). C'est elle
 *   qui résout l'identité de l'éleveuse dans l'éditeur de blocs, et elle n'est pas touchée.
 *   « /wp/v2/users/<id>/application-passwords[/…] » — armées, JAMAIS RETIRÉES, mêmes clés absentes des
 *   « unset() ».
 *
 * « 0 === strpos() » ET NON « str_starts_with() » : c'est l'idiome de ce dépôt — « strpos » y est
 * partout, « str_starts_with » n'apparaît nulle part en code dans « wp-content/ », relevé le
 * 2026-09-08 — il est Yoda par construction, et une seule forme donne UN SEUL grep le jour d'un audit.
 *
 * L'ORDRE DES DEUX GESTES EST IMPOSÉ, ET IL N'EST PAS COSMÉTIQUE. Inversé, « current_user_can() » — et
 * les filtres « user_has_cap » et « map_meta_cap » qu'il déclenche — courrait sur CHAQUE requête REST du
 * site pour une décision qui ne retirera rien dans la quasi-totalité des cas. L'appariement de route est
 * SANS EFFET DE BORD ; demander une capacité NE L'EST PAS.
 *
 * POURQUOI LE PREMIER GESTE TIENT LA CONTRAINTE D'INDEX DU §4.1. La route de l'index REST est « / » :
 * elle ne commence pas par « /wp/v2/users », DONC LE DRAPEAU N'EST JAMAIS ARMÉ POUR ELLE, donc
 * « /wp-json/ » est bâti d'une TABLE INTACTE POUR TOUT LE MONDE, à URL identique et sans « Vary ».
 * L'objection d'empoisonnement de cache du §4.1 est ainsi RESPECTÉE, et non contredite ; ce qu'elle
 * coûte est nommé au deuxième acte daté en tête de ce fichier, point 1.
 *
 * JAMAIS DE COURT-CIRCUIT, SUR AUCUN CHEMIN : « $resultat » est rendu tel quel, y compris quand on arme.
 * Rendre « null » inconditionnellement AVALERAIT EN SILENCE le court-circuit d'un tiers. Rendre un corps
 * de notre main rouvrirait l'oracle de second ordre pour lequel l'option B du §4.7 a été écartée, ET
 * nous forcerait à recopier une chaîne du cœur (« rest_user_cannot_view ») dans un fichier où l'i18n est
 * interdite — une ancre T114 de plus, pour rien.
 *
 * LA CAPACITÉ EST « edit_posts », ET CE QU'ELLE LAISSE PASSER DIT POURQUOI. Laisse passer :
 * Administrateur, ÉDITRICE, Auteur, Contributeur. Arrête : anonyme, Abonné.
 * L'ARGUMENT QUI FERME LA DISCUSSION : LE CŒUR ACCORDE DÉJÀ LA COLLECTION « users » À UN ANONYME PAR
 * DÉFAUT — mesuré, « 200 · 672 » sans cookie. NOTRE GARDE NE DONNE DONC JAMAIS PLUS QUE LE CŒUR : ELLE
 * NE FAIT QUE RETIRER. Son pire échec est « nous n'avons rien retiré », jamais « nous avons exposé
 * davantage ». LA GARDE EST SOUSTRACTIVE.
 *   PAS « list_users » — L'ÉLEVEUSE EST ÉDITRICE ET NE L'A PAS : la garde ne mordrait pas pour elle, et
 *   le défaut survivrait à l'identique. Écrit parce qu'un successeur la proposera, la capacité portant
 *   le nom du sujet.
 *   PAS « edit_others_posts » — elle armerait pour un Auteur, dont l'écran d'édition afficherait alors la
 *   valeur fausse : le défaut ne serait pas corrigé, il serait DÉPLACÉ SUR UN RÔLE que le site peut créer
 *   demain.
 *   PAS « is_user_logged_in() » — fonction remplaçable, interdite à l'extension
 *   (« query/page-protegee/bootstrap.php:156-161 »). « current_user_can( 'edit_posts' ) » est la
 *   frontière déjà employée par la maison (« blocks/grille-chiens/donnees.php:190 »).
 *
 * TYPAGE, ET CHAQUE CHOIX A SON MOTIF — L'ATTRIBUTION COMPRISE. « $resultat » N'EST PAS TYPÉ. LE CŒUR,
 * LUI, Y PASSE « null » : c'est tout ce que le fait 16 établit, et lui faire dire davantage serait une
 * ancre qui ment. Ce qui nous parvient est la valeur DE LA CHAÎNE de rappels — un tiers posé avant nous
 * peut y avoir mis son court-circuit, « WP_HTTP_Response » ou « WP_Error ». Aucun n'existe aujourd'hui
 * dans « wp-content/ » (fait 21) ; typer le paramètre poserait donc un « TypeError » sous
 * « strict_types » le jour où il en existerait un, sur un chemin que nous ne contrôlons pas. AUCUN TYPE
 * DE RETOUR N'EST DÉCLARÉ NON PLUS : la fonction rend « $resultat » tel quel, et « mixed » — le seul qui
 * conviendrait — n'ajouterait rien à ce que le « @return » ci-dessous dit déjà. « $serveur » N'EST PAS
 * TYPÉ non plus : il n'est JAMAIS LU, et typer un paramètre qu'on ne lit pas crée un chemin de
 * « TypeError » gratuit. « $requete » EST typé, parce qu'on appelle une méthode dessus.
 *
 * @param mixed            $resultat Court-circuit éventuel posé par un tiers. RENDU TEL QUEL, toujours.
 * @param mixed            $serveur  Serveur REST en cours. JAMAIS LU.
 * @param \WP_REST_Request $requete  Requête en cours de dispatch.
 *
 * @return mixed Le « $resultat » reçu, inchangé — sur tous les chemins, sans exception.
 */
function armer_le_retrait_des_routes_d_identite( $resultat, $serveur, \WP_REST_Request $requete ) {
	if ( 0 !== strpos( $requete->get_route(), '/wp/v2/users' ) ) {
		return $resultat;
	}

	if ( ! current_user_can( 'edit_posts' ) ) {
		drapeau_de_retrait( true );
	}

	return $resultat;
}

/**
 * Retire de la table des routes REST les deux routes qui publient une identité de compte.
 *
 * DEUX « unset() » SUR DEUX CLÉS LITTÉRALES, ET RIEN D'AUTRE. La carte complète, écrite ici pour qu'un
 * successeur voie d'un coup d'œil ce qu'il ne doit pas casser (relevé du 2026-09-08 sur l'index rendu,
 * confirmé par les faits 5 et 6) :
 *
 *   RETIRÉES   : /wp/v2/users
 *                /wp/v2/users/(?P<id>[\d]+)
 *   PRÉSERVÉES : /wp/v2/users/me
 *                /wp/v2/users/(?P<user_id>(?:[\d]+|me))/application-passwords
 *                /wp/v2/users/(?P<user_id>(?:[\d]+|me))/application-passwords/introspect
 *                /wp/v2/users/(?P<user_id>(?:[\d]+|me))/application-passwords/(?P<uuid>[\w\-]+)
 *
 * INTERDIT GELÉ — AUCUNE CORRESPONDANCE PAR PRÉFIXE. Ni « strpos », ni « str_starts_with », ni
 * « str_contains », ni « preg_* », ni « array_filter » sur motif, ni aucune boucle sur
 * « array_keys() ». UNE CORRESPONDANCE PAR PRÉFIXE DÉTRUIRAIT QUATRE ROUTES SUR SIX — « /users/me », que
 * le cœur laisse ouverte à « __return_true » (fait 6), et les trois routes de mots de passe
 * d'application. ET LA PANNE SERAIT INVISIBLE EN ANONYME, puisque « /users/me » rend déjà 401 sans
 * session : elle ne se verrait qu'EN session, c'est-à-dire seulement chez l'éleveuse, sur l'écran de son
 * profil, sans un mot d'explication.
 *
 * LE TABLEAU REÇU EST RENDU, AMPUTÉ — JAMAIS REMPLACÉ. Un « return array( … ) » DÉTRUIRAIT TOUTES LES
 * ROUTES DU SITE : l'éditeur de blocs par terre, et invisible en recette si l'on ne teste que le front.
 * C'est l'interdit de #49 à un cran plus grave, parce qu'ici le tableau n'est pas une requête, c'est la
 * table des routes entière.
 *
 * AUCUN « isset » PRÉALABLE, ET C'EST VOULU : « unset() » sur une clé absente est un non-opérant SANS
 * notice. Une garde de forme n'achèterait rien et ferait croire à un cas qu'elle protège.
 *
 * AUCUNE GARDE DE CONTEXTE — C'EST UNE PROPRIÉTÉ, PAS UN OUBLI. Deux ont été envisagées, toutes deux
 * refusées, chacune avec SON motif :
 *
 *   « is_admin() » — REFUSÉE. Elle vaut FAUX sur « /wp-json/ » (doctrine du dépôt,
 *   « query/page-protegee/bootstrap.php:147-148 »), mais le vrai danger est ailleurs : le préchargement
 *   de l'éditeur de blocs court EN ADMINISTRATION, où elle vaut VRAI, tandis que les requêtes
 *   ultérieures du même écran courent sur « /wp-json/ », où elle vaut FAUX. Elle livrerait donc UNE
 *   ADMINISTRATION INCOHÉRENTE : routes présentes au préchargement, absentes ensuite. Une garde qui
 *   rassure sans couvrir — et qui, en plus, ment à moitié.
 *
 *   « defined( 'REST_REQUEST' ) » — REFUSÉE AUSSI, MAIS LE MOTIF DE #49 NE S'Y RECOPIE PAS : IL SE
 *   REMPLACE, et il faut écrire le motif neuf sinon un successeur recopiera un raisonnement qui ne vaut
 *   plus. Chez #49, le filtre « request » court AVANT que « rest_api_loaded() » ne définisse la
 *   constante : la garde y serait TOUJOURS FAUSSE, donc trompeuse. ICI C'EST L'INVERSE — « rest_endpoints »
 *   court dans « get_routes() », donc bien après : la constante serait déjà définie et LE TEST TOUJOURS
 *   VRAI. Ce serait une garde contre un cas impossible, qui rassure sans rien couvrir. Même verdict,
 *   motif opposé. ET CE MOTIF-CI EST UNE DÉDUCTION, JAMAIS UNE MESURE — contrat #56 §14, point 1 : la
 *   définition de la constante à l'instant de « rest_endpoints » n'a PAS été relevée dans le conteneur.
 *   Elle ne décide de rien, le crochet étant écarté de toute façon ; mais l'écrire au présent de
 *   l'indicatif serait exactement la faute qui a bloqué le lot 20.
 *
 *   ET AUCUNE CONDITION DE CAPACITÉ NON PLUS. « /wp-json/ » EST UN INDEX, bâti par le MÊME
 *   « get_routes() » que le dispatch (fait 2) : une table conditionnée à la capacité rendrait un index
 *   dont le contenu varie selon le demandeur, à URL identique, sans « Vary ». Le premier cache venu
 *   servirait à un anonyme l'index d'une session, ou l'inverse — panne muette et intermittente, la pire
 *   famille du dépôt. L'objection est déjà écrite dans ce dépôt, à « query/page-protegee/bootstrap.php:156-161 » :
 *   « un index rendu conditionnel serait empoisonné en cache dès la première mise en cache. »
 *
 * CE QUE CE RETRAIT ABANDONNE, DIT SANS L'ADOUCIR. Le panneau « Auteur » des écrans d'édition de Pages
 * et d'Articles perd sa liste de comptes, et les verbes d'écriture de ces deux routes disparaissent avec
 * elles. Les trois types métier ne sont pas concernés — Portée et Chien ne déclarent pas « author » dans
 * leurs « supports », Résultat déclare « 'supports' => false » — l'inscription est fermée, et
 * « users.php », « profile.php », « user-edit.php » sont des écrans PHP classiques. NUANCE À NE PAS
 * LISSER : seul « profile.php » est MESURÉ intact — relevé en session le 2026-09-08, et rejoué après
 * correctif ; pour « users.php » et « user-edit.php », « écran classique et non REST » est une
 * DÉDUCTION et non une mesure (contrat #56 §14, point 3).
 *
 * LES DEUX CLÉS DISPARAISSENT AUSSI DE L'INDEX « /wp-json/ », ET C'EST LA COHÉRENCE, PAS UN EFFET DE
 * BORD (fait 2). Les laisser annoncées dans l'index pendant que le dispatch rend 404 fabriquerait
 * exactement l'oracle que le fait 4 nous fait fuir : « annoncée mais absente » ne se lit que d'une façon.
 *
 * ACTE DATÉ DU 2026-09-08 (#56) — UNE LIGNE DE GARDE EN TÊTE, ET DEUX PARAGRAPHES CI-DESSUS QUI CHANGENT
 * DE PORTÉE. Le retrait n'est plus inconditionnel : il ne mord QUE si le drapeau a été armé, plus tôt
 * dans le même dispatch, par « armer_le_retrait_des_routes_d_identite() » — lequel n'arme que pour un
 * demandeur SANS « edit_posts ». LE MOTIF EST UNE RÉGRESSION MESURÉE : la forme inconditionnelle
 * amputait la table pour l'éleveuse aussi, et son écran d'édition annonçait « (Aucun auteur/autrice) »
 * sur un contenu qui A un auteur. Le récit complet est au deuxième acte daté en tête de ce fichier.
 * CE QUI NE CHANGE PAS, ET QUI SE LIT AVANT DE TOUCHER À CETTE FONCTION :
 *   L'INTERDIT DE PRÉFIXE DU §15 RESTE ENTIER ICI. Deux « unset() » sur deux clés littérales, dans cet
 *   ordre, sans « isset », sans boucle, sans motif. La garde ajoutée NE LIT AUCUNE ROUTE : elle lit un
 *   booléen. L'appariement de route vit dans l'autre fonction, où sur-apparier est INOFFENSIF ;
 *   l'inversion de sûreté est écrite au-dessus d'elle, ET ELLE NE SE RECOPIE PAS ICI.
 *   LES DEUX GARDES REFUSÉES CI-DESSUS LE RESTENT, motifs inchangés : « is_admin() » et
 *   « defined( 'REST_REQUEST' ) ». LE DRAPEAU N'EST NI L'UNE NI L'AUTRE — ce n'est pas une garde de
 *   CONTEXTE, c'est le report d'une DÉCISION au seul instant où le demandeur est connu et où la table
 *   n'est pas encore calculée (faits 16 et 17).
 *   LA CONDITION DE CAPACITÉ QUE LE §4.1 REFUSAIT N'EST PAS RÉINTRODUITE ICI. Rien dans cette fonction
 *   ne demande une capacité, et l'index « /wp-json/ » est bâti d'une TABLE INTACTE POUR TOUT LE MONDE,
 *   la route de l'index — « / » — n'armant jamais le drapeau : l'index ne varie donc pas selon le
 *   demandeur, à URL identique, et l'empoisonnement de cache reste fermé. LE PARAGRAPHE CI-DESSUS « LES
 *   DEUX CLÉS DISPARAISSENT AUSSI DE L'INDEX » CESSE D'ÊTRE VRAI PAR CE FAIT MÊME, et ce qu'on y perd
 *   est nommé au deuxième acte daté en tête de ce fichier, point 1 : la cible P4 du §7.1 échoue
 *   désormais sans qu'aucune régression n'ait eu lieu.
 *
 * @param array $routes Table des routes REST, telle que « get_routes() » vient de l'assembler.
 *
 * @return array La table reçue INCHANGÉE si le retrait n'a pas été armé ; sinon, la table reçue,
 *               amputée des deux routes d'identité — et de rien d'autre.
 */
function retirer_les_routes_d_identite( array $routes ): array {
	if ( ! drapeau_de_retrait() ) {
		return $routes;
	}

	unset( $routes['/wp/v2/users'] );
	unset( $routes['/wp/v2/users/(?P<id>[\d]+)'] );

	return $routes;
}

/**
 * Substitue le titre du site au nom d'auteur publié par les trois gabarits de flux.
 *
 * UN SEUL FILTRE POUR RSS2, RDF ET ATOM (faits 7, 8, 9, 11). Aucun gabarit du cœur n'est recopié, et le
 * recopier serait rejeté d'emblée : ce serait « on modifiera le fichier » déguisé en architecture, et un
 * fichier qui SE PÉRIME EN SILENCE à chaque montée de version — la dette T114 créée volontairement, dans
 * sa forme la plus coûteuse.
 *
 * GARDE 1, ET C'EST LA GARDE LA PLUS GRAVE DU FICHIER : SORTIR EN ADMINISTRATION, EN RENDANT LE NOM
 * INCHANGÉ. Le motif est MESURÉ, pas déduit (fait 15) :
 * « wp-admin/includes/class-wp-posts-list-table.php:1284 » appelle « get_the_author() », et la colonne
 * « Auteur » de l'écran Pages rend bien « admin » — relevé en session le 2026-09-08. SANS CETTE GARDE,
 * LES ÉCRANS PAGES ET ARTICLES AFFICHERAIENT LE TITRE DU SITE COMME AUTEUR DE TOUT, EN SILENCE : chaque
 * ligne attribuée à « Berger Hollandais du Mont Brabant », l'onglet et le filtre par auteur devenus
 * illisibles, et aucune erreur nulle part. UN ÉCRAN QUI A L'AIR DE MARCHER ET QUI MENT SUR CE QU'IL
 * MONTRE EST PIRE QU'UN ÉCRAN CASSÉ : l'éleveuse n'a aucune raison de le signaler. C'est mot pour mot le
 * mode de panne de l'amendement HIGH de #49 — CETTE FOIS VU AVANT D'ÊTRE LIVRÉ, et c'est la seule raison
 * pour laquelle cette ligne existe.
 *
 * GARDE 2 : RENDRE « get_bloginfo( 'name' ) » SANS JAMAIS LIRE « $nom ». L'argument reçu est jeté sans
 * être regardé, et c'est la propriété de #49 conservée : LA JUSTESSE NE DÉPEND D'AUCUN ÉTAT DE LA BASE
 * DES COMPTES. Elle vaut pour les comptes présents ET FUTURS, sans une liste d'identifiants, sans une
 * table « identifiant → pseudonyme » — le mécanisme qui ment le jour où un compte est créé.
 *
 * POURQUOI CETTE VALEUR-LÀ. C'EST LE DÉFAUT DU CŒUR POUR LE CHAMP JUMEAU DE L'oEMBED (fait 12,
 * « embed.php:604 ») : ON N'INVENTE PAS UNE VALEUR, ON EMPRUNTE LA SIENNE — exactement comme #49 a
 * emprunté la chaîne « 404 » au cœur plutôt que d'en composer une. Elle est déjà publique, elle n'est
 * une chaîne en dur de personne, et elle est pilotée par un réglage que l'éleveuse ouvre elle-même
 * (Réglages › Général › Titre du site). NUANCE DUE À LA MESURE, ÉCRITE PLUTÔT QUE LISSÉE : l'argument
 * « c'est la valeur du canal » a été ÉCARTÉ, le titre du canal passant par « wp_title_rss() » et non par
 * « get_bloginfo( 'name' ) » — c'est l'argument du défaut du cœur POUR CE CHAMP-LÀ qui porte la
 * décision, et lui seul.
 *
 * « is_feed() » EST REFUSÉ, TROIS MOTIFS — ET IL Y EN AVAIT QUATRE, LE QUATRIÈME A ÉTÉ RAYÉ PAR LA
 * MESURE :
 *   1. La propriété livrée serait AMPUTÉE. Avec la garde on livre « ce site ne publie pas un nom de
 *      compte DANS UN FLUX » ; sans elle, « ce site ne publie JAMAIS un nom de compte à un visiteur ».
 *      La première est une demi-fermeture par construction.
 *   2. Elle n'achète rien aujourd'hui : le thème appelle « the_author » ZÉRO fois — revérifié sur le
 *      disque le 2026-09-08.
 *   3. Elle coûte demain : le jour où un gabarit écrirait « the_author() », la version gardée fuirait EN
 *      SILENCE, la version non gardée échoue du bon côté.
 *   4. RAYÉ — « le gabarit d'embarquement est un candidat vivant pour l'autre moitié » : mesuré le
 *      2026-09-08, « /…/embed/ » NE PUBLIE AUCUNE IDENTITÉ DE COMPTE, ses deux occurrences de « admin »
 *      étant des noms de classe CSS « dashicons-admin-comments » dans un SVG en « data: ». Un motif qui
 *      tombe se raye ; il ne se garde pas parce que la conclusion tient toujours par ailleurs.
 *
 * AUCUN ÉCHAPPEMENT DE NOTRE MAIN, ET C'EST DÉLIBÉRÉ. Ce filtre alimente TROIS GABARITS AUX RÈGLES
 * DIFFÉRENTES : CDATA en RSS2 (fait 7) et en RDF (fait 8), TEXTE BRUT en Atom (fait 9). AUCUN
 * ÉCHAPPEMENT N'EST JUSTE POUR LES TROIS À LA FOIS, et en poser un casserait au moins l'un d'eux —
 * entités doublées dans les deux CDATA, ou balise malformée dans l'Atom. CONSÉQUENCE ASSUMÉE ET NOMMÉE
 * (résidu R9) : un titre de site contenant « & » ou « < » rendrait l'Atom malformé. C'EST HÉRITÉ DU
 * CŒUR, qui imprime déjà le « display_name » brut au même endroit, et non un défaut que ce rappel
 * introduit.
 *
 * AUCUNE INTERACTION AVEC « archives-d-auteur.php » — regardée, pas supposée : son rappel ne mord que si
 * une clé de « CLES_D_AUTEUR » est présente, celui-ci NE LIT AUCUNE VARIABLE DE REQUÊTE, et les deux
 * crochets courent à des instants disjoints. Aucun ordre à imposer entre les deux fichiers.
 *
 * @param string $nom Nom d'affichage que le cœur s'apprête à publier. JAMAIS LU hors administration.
 *
 * @return string Le nom reçu tel quel en administration ; le titre du site partout ailleurs.
 */
function substituer_le_nom_d_auteur( string $nom ): string {
	if ( is_admin() ) {
		return $nom;
	}

	return get_bloginfo( 'name' );
}

/**
 * Rétablit dans le document oEmbed les valeurs d'auteur que le cœur donne lui-même par défaut.
 *
 * ÉCRASER, ET NON RETIRER. Le fait 12 établit que le cœur pose « get_bloginfo( 'name' ) » et
 * « get_home_url() » comme valeurs PAR DÉFAUT de ces deux champs, puis les écrase dès qu'un auteur est
 * résolu. Substituer, c'est donc RÉTABLIR LE DÉFAUT DU CŒUR — et la propriété livrée en devient
 * remarquablement mesurable : après ce rappel, l'oEmbed d'un contenu singulier publie EXACTEMENT les
 * deux mêmes valeurs que celui de l'accueil, qui tombe sur la branche par défaut et reste inchangé à
 * l'octet.
 *
 * RETIRER LES CLÉS PRODUIRAIT AU CONTRAIRE UNE FORME QU'AUCUN DÉFAUT DU CŒUR NE PRODUIT : un document
 * oEmbed sans champ d'auteur, valide au sens de la spécification, mais SIGNANT QU'ON A RETIRÉ QUELQUE
 * CHOSE. On ne remplace pas un oracle par un oracle plus discret — c'est le raisonnement du fait 4, à un
 * autre étage.
 *
 * CES EXPRESSIONS-LÀ, PAS LEURS ÉQUIVALENTES. « get_bloginfo( 'name' ) » et « get_home_url() » sont
 * relevées à « embed.php:604-607 » comme étant le défaut du cœur lui-même. NE JAMAIS LEUR SUBSTITUER
 * « get_bloginfo( 'url' ) » NI « site_url() » : elles peuvent rendre une autre valeur, et le contrôle
 * qui prouve ce rappel est l'ÉGALITÉ CHAMP À CHAMP avec l'oEmbed de l'accueil — on ne prouve pas qu'on a
 * écrit la bonne fonction, on prouve qu'on rend la bonne valeur, et une expression équivalente-mais-autre
 * ferait échouer ce contrôle sans rien signaler d'autre.
 *
 * AUCUNE CLÉ N'EST RETIRÉE, AUCUNE N'EST LUE. Le tableau reçu est rendu, deux champs réécrits — ni
 * inspection, ni condition sur ce qu'il contenait.
 *
 * AUCUNE GARDE, ET C'EST DÉLIBÉRÉ : CE RAPPEL FILTRE LA DONNÉE, PAS LE CONTEXTE. Il est juste partout où
 * le cœur assemble une réponse oEmbed. Et le fait 14 dispense d'en écrire une seconde pour le XML : la
 * forme XML est assemblée APRÈS ce filtre (« embed.php:782-810 », « _oembed_create_xml() »), donc UN
 * SEUL RAPPEL COUVRE « format=json » ET « format=xml ». Mesuré sur les deux formes, pas déduit de l'une.
 *
 * @param array $donnees Document oEmbed assemblé par le cœur, avant mise en forme JSON ou XML.
 *
 * @return array Le document reçu, ses deux champs d'auteur ramenés au défaut du cœur.
 */
function substituer_l_auteur_oembed( array $donnees ): array {
	$donnees['author_name'] = get_bloginfo( 'name' );
	$donnees['author_url']  = get_home_url();

	return $donnees;
}
