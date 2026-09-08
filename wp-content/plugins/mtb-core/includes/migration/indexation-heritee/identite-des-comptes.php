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
 * @param array $routes Table des routes REST, telle que « get_routes() » vient de l'assembler.
 *
 * @return array La table reçue, amputée des deux routes d'identité — et de rien d'autre.
 */
function retirer_les_routes_d_identite( array $routes ): array {
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
