<?php
/**
 * L'interrupteur « en sommeil » : sa déclaration, ses deux écrans, son enregistrement.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Fields\Sommeil;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * CE MODULE NE PORTE AUCUNE GARDE « if ( ! is_admin() ) { return; } », ET C'EST LE PIÈGE LE PLUS
 * COÛTEUX DE CETTE ISSUE.
 *
 * « fields/chien/bootstrap.php:17 » en porte une, à juste titre : rien de ce module-là n'a de sens
 * hors de wp-admin. ICI, ELLE TUERAIT LE MODULE SUR LA SEULE FAÇADE OÙ IL DOIT COURIR. Le panneau
 * « Résumé » de l'éditeur de blocs écrit l'état par la façade REST, « /wp-json/ », OÙ is_admin() VAUT
 * FAUX : sous cette garde, register_post_meta() ne courrait pas, la clé ne serait pas déclarée, la
 * case se cocherait, la page s'enregistrerait — ET LA CASE REVIENDRAIT DÉCOCHÉE. Sans erreur, sans
 * une ligne au journal, sur un écran qui répond 200. L'éleveuse conclurait que l'interrupteur ne
 * marche pas, et elle aurait raison.
 *
 * LE CONTEXTE SE TESTE DANS LES RAPPELS, JAMAIS AU CHARGEMENT. C'est ce que font « rendre_la_case() »
 * — qui vérifie le type du contenu et la capacité —, « mettre_le_script_en_file() » — qui vérifie
 * l'écran — et « enregistrer() » — qui vérifie le nonce et la capacité.
 *
 * C'EST AUSSI POURQUOI LA MENTION DANS LES LISTES VIT DANS UN MODULE SÉPARÉ, « admin/sommeil » : ce
 * module-là a besoin de la garde, celui-ci serait tué par elle. Les réunir fabriquerait exactement
 * le piège que décrit le §2.2 du contrat #23.
 *
 * LA CLÉ ET SA LECTURE NE SONT JAMAIS RECOPIÉES. Elles vivent dans « query/mise-en-sommeil/etat.php »,
 * que ce module require_once plutôt que de dépendre de l'ordre de parcours du chargeur — un module ne
 * dépend jamais de cet ordre, et une seconde inclusion est sans effet. On require le FICHIER, jamais
 * le bootstrap.php d'un autre module.
 */

require_once MTB_CORE_DIR . 'includes/query/mise-en-sommeil/etat.php';

require_once __DIR__ . '/libelles.php';
require_once __DIR__ . '/declaration.php';
require_once __DIR__ . '/ecran-classique.php';
require_once __DIR__ . '/sauvegarde.php';
require_once __DIR__ . '/editeur-de-blocs.php';

add_action( 'init', __NAMESPACE__ . '\\declarer_le_champ', 20 );
add_action( 'init', __NAMESPACE__ . '\\declarer_le_script', 20 );

add_action( 'post_submitbox_misc_actions', __NAMESPACE__ . '\\rendre_la_case', 10, 1 );

/*
 * UN CROCHET D'ENREGISTREMENT PAR TYPE DÉCLARÉ, TIRÉ DE LA MÊME LISTE QUE « register_post_meta » ET
 * QUE L'ÉCRAN. Les trois « add_action » ne sont pas écrits à la main : un type ajouté à « TYPES » et
 * oublié ici donnerait une case qui se coche et ne s'enregistre jamais — une panne muette de plus.
 * C'est la liste qui commande, à un seul endroit.
 *
 * « save_post_page » EST INERTE SOUS LA FAÇADE REST, ET CE N'EST PAS UN DOUBLON — écrit ici pour
 * qu'une chaîne future ne le lise pas comme tel et ne le retire pas de la liste.
 *
 * Une page enregistrée depuis l'éditeur de blocs passe par « /wp-json/ », qui ne poste AUCUN
 * formulaire : le champ « mtb_sommeil_nonce » n'y est pas, la deuxième garde de « enregistrer() »
 * sort immédiatement, et c'est le cœur qui écrit l'état à partir de la métadonnée déclarée. Ce rappel
 * ne sert donc QUE le repli où l'éditeur de blocs serait désactivé sur les pages — auquel cas
 * l'éditeur classique rend l'encadré « Publier », donc notre case, donc notre nonce. Sans lui, ce
 * repli enregistrerait la page sans jamais écrire l'état, en silence.
 */
foreach ( TYPES as $type_en_sommeil ) {
	add_action( 'save_post_' . $type_en_sommeil, __NAMESPACE__ . '\\enregistrer', 10, 3 );
}

// La variable de boucle ne survit pas au bootstrap : l'espace global n'accueille rien de ce module.
unset( $type_en_sommeil );

add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\\mettre_le_script_en_file', 10 );
