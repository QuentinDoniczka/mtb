<?php
/**
 * Le vocabulaire de l'écran d'une page : la rangée « Slug » et la famille « image mise en avant ».
 *
 * CE QUE L'ÉLEVEUSE VOIT CHANGER. Sur l'écran d'une page, et nulle part ailleurs : la rangée de la
 * zone latérale qui s'appelait « Slug » s'appelle désormais « Adresse de la page », et la fenêtre
 * volante qu'elle ouvre porte le même titre ; le bouton de la photo dit « Choisir la photo
 * principale », et la fenêtre des photos qu'il ouvre s'intitule « Photo principale ». Quand une photo
 * est déjà posée, le NOM ACCESSIBLE de ce bouton, invisible à l'œil et prononcé par un lecteur
 * d'écran, dit « Modifier ou remplacer la photo principale ». Rien d'autre ne bouge : aucune donnée
 * n'est touchée, aucune adresse ne change, aucune page n'est à ré-enregistrer, le site public est
 * identique. Les Portées, les Chiens et les Résultats disent DÉJÀ ces mots, par leurs propres
 * libellés : ils ne changent pas d'un caractère. Les libellés viennent de
 * « design-system/MASTER.md » §10.2, qui fige « Photo principale » et « Adresse de la page » ; c'est
 * son §10.4 qui range « slug », « permalien » et « image mise en avant » parmi les mots interdits à
 * l'écran.
 *
 * CE MODULE NE PORTE AUCUNE GARDE « if ( ! is_admin() ) { return; } », ET C'EST LE PIÈGE LE PLUS
 * COÛTEUX DE CETTE ISSUE. Le réflexe est de recopier la garde de « admin/description-photo », qui la
 * revendique par écrit et a raison de le faire. ICI ELLE SERAIT NUISIBLE, ET CE N'EST PAS UNE
 * SUPPOSITION : la variante gardée a été jouée sur WordPress 6.9, le 2026-09-08, et voici ce qu'elle
 * a donné. Le bouton de la photo affichait bien le libellé français au premier rendu — le
 * préchargement des libellés naît dans la requête « wp-admin », où is_admin() vaut vrai — mais la
 * façade REST « /wp/v2/types/page », WP-CLI et tout appel « apiFetch » ultérieur rendaient encore
 * « Définir l’image mise en avant ». Le libellé aurait donc été INTERMITTENT : réparé au chargement de
 * l'écran, retombé au mot interdit dès le premier rafraîchissement du magasin de l'éditeur. Un écran
 * qui se répare tout seul puis se casse à l'usage est PIRE qu'un écran qui ne change jamais, et il ne
 * laisse ni erreur, ni ligne au journal.
 *
 * LE CONTEXTE SE TESTE DANS LE RAPPEL QUI MET LE SCRIPT EN FILE, JAMAIS AU CHARGEMENT DU MODULE.
 * C'est le partage que « includes/fields/sommeil/bootstrap.php » documente déjà : l'un des deux
 * crochets a besoin de la garde d'ouverture, l'autre serait tué par elle. Ici la garde d'écran vit
 * dans « ecran.php », et elle ne concerne que la moitié JavaScript.
 *
 * LE COÛT DE L'ABSENCE DE GARDE, CHIFFRÉ, PARCE QUE C'EST LUI QUI REND L'ÉCART DÉFENDABLE. Ce filtre
 * coûte quatre affectations de propriété par calcul des libellés du type « page », et rien du tout
 * sur les requêtes où le cœur ne les calcule pas. C'est sans commune mesure avec un rappel
 * « gettext », appelé pour CHAQUE chaîne traduite de CHAQUE requête — d'où la garde de
 * « admin/description-photo », juste chez lui et fausse ici.
 *
 * POURQUOI LE FILTRE EST POSÉ À L'INCLUSION ET JAMAIS SUR « init ». Le cœur enregistre le type
 * « page » pendant l'amorçage, avant « plugins_loaded » et donc bien avant « init ». Un « add_filter »
 * posé depuis un rappel de « init » arriverait APRÈS le calcul des libellés et ne mordrait RIEN, en
 * silence. Posé à l'inclusion du bootstrap, comme « admin/description-photo » pose le sien, il est en
 * place à temps. Le chargeur autorise expressément « add_filter » à l'inclusion.
 *
 * ÉCART AU CROCHET DE GROUPE, DÉCLARÉ. Le chargeur associe au groupe « admin » les crochets
 * « admin_menu » et « admin_init ». Ce module pose « post_type_labels_page » (à l'inclusion) et
 * « enqueue_block_editor_assets ». L'écart est réel et volontaire : cette liste est descriptive et le
 * chargeur n'en vérifie rien — « admin/sommeil » pose déjà « display_post_states » et
 * « admin/corbeille » « bulk_post_updated_messages ». Écrit ici pour qu'une chaîne future ne le lise
 * pas comme une faute à corriger.
 *
 * MODULE DISTINCT DE « admin/description-photo », À NE PAS Y RANGER. Quatre raisons, les trois
 * premières transposées du critère de séparation que ce module-là a lui-même fixé. Sa garde
 * « is_admin() », en tête de fichier avec « return », TUERAIT notre filtre sur la façade REST ; la
 * neutraliser ferait payer une comparaison « gettext » à chaque visiteur anonyme, ce que son en-tête
 * déclare inacceptable. Préfixer « _description-photo » pour désactiver le renommage de l'écran d'une
 * page emporterait le renommage de « Texte alternatif » sur six parcours médias, et réciproquement :
 * deux pannes sans rapport, couplées. Son sujet est UN CHAMP à travers toutes les surfaces médias, le
 * nôtre est UN TYPE DE CONTENU. Enfin ce module introduit le premier fichier JavaScript du groupe
 * « admin », avec un crochet de mise en file, dans un module dont l'en-tête déclare ne poser qu'un
 * seul « add_filter ».
 *
 * AUCUN NUMÉRO DE LIGNE DU CŒUR N'EST CITÉ NULLE PART DANS CE MODULE, ET C'EST DÉLIBÉRÉ. Un numéro de
 * ligne se périme en silence à la première montée de version. La moitié PHP s'appuie sur un NOM DE
 * FILTRE — « post_type_labels_page », API publique — et la moitié JavaScript sur des CHAÎNES SOURCES,
 * citées avec la version et la date de leur relevé. Écart assumé à la forme de
 * « admin/description-photo », dont la table de huit lignes se périmera sans prévenir.
 *
 * CE QUE CE MODULE N'ATTEINT PAS, NOMMÉMENT, AVEC SA SUITE. Le mot « Slug » reste écrit dans la
 * MODIFICATION RAPIDE des listes — Pages, Portées, Chiens et Articles —, où le cœur l'émet en PHP :
 * deux de ces écrans sont quotidiens pour l'éleveuse, le remède mord sur trois types et appartient à
 * un autre module ; c'est une dette ouverte, pas un oubli. Il reste aussi sur les écrans de taxonomie
 * et sur Réglages → Permaliens, deux surfaces où l'éleveuse ne va pas et que son rôle ne lui ouvre
 * même pas. « Image mise en avant » reste sur l'écran d'un Article, à une ligne près, et cette ligne
 * n'est PAS écrite « en passant » : l'issue ne ferme qu'un écran. La phrase d'aide de la fenêtre
 * volante emploie « permalien », mot interdit lui aussi, mais c'est un texte d'aide et non une
 * étiquette — précédent gelé de « admin/description-photo », qui a refusé de remplacer un texte d'aide
 * du cœur. Enfin la rangée s'appelle « Link », et non « Slug », quand la page est la page d'accueil :
 * ce cas-là n'est pas couvert.
 *
 * RELEVÉ DE L'ÉCRAN, DATÉ ET VERSIONNÉ — WORDPRESS 6.9, LE 2026-09-08, page témoin « Placement ».
 * Zone latérale, onglet « Page », dans cet ordre : État · Publier · Slug · Auteur/autrice · Modèle ·
 * Commentaires · Parent. « Extrait » N'EST PAS SUR CET ÉCRAN — le type « page » ne déclare pas ce
 * support sur cette installation, mesuré —, ce qui clôt la question du mot interdit « extrait » ici :
 * il n'y a rien à remplacer. Le mot français « Modèle » reste : §10.4 interdit « template », l'anglais,
 * et §10.2 ne fige aucun autre mot pour cette rangée ; le renommer serait inventer du vocabulaire.
 *
 * MODE DE PANNE, MOITIÉ PAR MOITIÉ. La moitié PHP ne compare AUCUNE chaîne : elle ne peut pas tomber
 * parce que le cœur aurait reformulé un texte. Elle ne tomberait que si l'éditeur cessait de lire les
 * libellés du type, ou si le cœur renommait une clé de « register_post_type » — quasi impossible,
 * c'est son API publique. La moitié JavaScript, elle, compare des chaînes sources : elle tombe le jour
 * où le cœur en reformule une, et elle tombe EN SILENCE. Dans les deux cas la panne est bénigne — rien
 * de cassé, rien de perdu, retour exact à l'état d'avant — et rigoureusement muette.
 *
 * IL N'Y A AUCUN TÉMOIN AUTOMATIQUE, ET C'EST UNE DÉCISION, PAS UN OUBLI. Toutes les commandes WP-CLI
 * du dépôt vivent dans « includes/migration/ » ; en inventer une ici casserait deux conventions pour
 * un témoin d'une ligne, qui serait de surcroît FAUX VERT sur la moitié qui en aurait le plus besoin.
 * La vérification est MANUELLE, REJOUABLE, ET SE REJOUE EN ENTIER À CHAQUE MONTÉE DE WORDPRESS :
 *
 *   1. La moitié PHP mord :
 *      wp eval 'echo get_post_type_object("page")->labels->set_featured_image;'
 *      doit rendre « Choisir la photo principale ». Cette commande prouve que le filtre mord ; elle ne
 *      prouve PAS que l'écran lit ce libellé — cela se lit au navigateur, et seulement là.
 *   2. Les deux moitiés à l'écran : ouvrir l'écran d'une page dans un vrai navigateur, SANS aucune
 *      interaction, et lire la zone latérale. « Adresse de la page » présent, « Slug » nulle part,
 *      « Choisir la photo principale » présent, « Image mise en avant » nulle part.
 *   3. Le non-débordement : sur l'écran d'une portée, d'un chien, d'un article, sur les listes et sur
 *      la Médiathèque, le mot « Slug » DOIT RESTER là où il est aujourd'hui. Sa présence y est la
 *      preuve que la garde tient, jamais un échec.
 *
 * La seule parade retenue contre la panne muette est humaine : une ligne dans la rubrique « Ce n'est
 * pas normal, signalez-le » de la fiche du guide. L'éleveuse est le seul détecteur qui regarde
 * vraiment l'écran.
 *
 * POUR DÉSACTIVER CE MODULE : renommer son dossier en « _vocabulaire-page ». Les deux moitiés
 * disparaissent ensemble, l'écran revient exactement à son état d'avant, et aucun autre module n'est
 * emporté.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Admin\VocabulairePage;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/libelles.php';
require_once __DIR__ . '/filtre-des-libelles.php';
require_once __DIR__ . '/ecran.php';

/*
 * À L'INCLUSION, ET SURTOUT PAS SUR « init » : le type « page » est enregistré par le cœur pendant
 * l'amorçage, et un filtre posé plus tard ne mordrait rien, en silence. Sans garde de contexte, pour
 * que la façade REST et l'administration ne divergent jamais.
 */
add_filter( 'post_type_labels_page', __NAMESPACE__ . '\\remplacer_les_libelles', 10, 1 );

add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\\mettre_le_script_en_file', 10 );
