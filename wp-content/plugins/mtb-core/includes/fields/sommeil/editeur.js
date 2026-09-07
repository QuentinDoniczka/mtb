/**
 * Interrupteur « en sommeil » — zone latérale de l'éditeur de blocs.
 *
 * OÙ LA CASE PARAÎT, RELEVÉ AU NAVIGATEUR, WORDPRESS 6.9, LE 2026-09-07 : zone latérale de droite,
 * onglet « Page », sous les rangées « État », « Publier », « Slug », « Auteur/autrice », « Modèle »,
 * « Commentaires » et « Parent », SANS AUCUN TITRE DE PANNEAU AU-DESSUS D'ELLE. Il n'existe PAS de
 * panneau « Résumé » sur cette version : le mot ne figure nulle part à l'écran, vérifié. Ne pas le
 * réintroduire, ici ni dans une fiche d'aide — l'éleveuse chercherait une chose qui n'existe pas.
 *
 * L'emplacement n'est pas notre choix : il est décidé par « PluginPostStatusInfo », le point
 * d'extension du cœur. Ce fichier fournit le contenu, pas la place.
 *
 * ES5, aucune syntaxe JSX, aucune étape de construction : le fichier est servi tel quel, lisible et
 * modifiable sans outillage. Il n'est mis en file que sur l'écran d'édition d'une page ; le visiteur
 * n'en reçoit pas un octet.
 *
 * TOUTES LES PHRASES VIENNENT DU SERVEUR, dans « window.mtbSommeil ». Ce fichier n'en compose aucune,
 * n'en concatène aucune et n'en corrige aucune : c'est ce qui garantit que les trois écrans de
 * l'interrupteur disent mot pour mot la même chose.
 */
( function () {
	'use strict';

	/*
	 * Les dépendances déclarées à l'enregistrement du script, et rien de plus. Une dépendance
	 * manquante laisse le panneau absent : la page s'enregistre quand même, l'état déjà écrit reste
	 * intact, et rien n'est journalisé — d'où le contrôle au navigateur réel du protocole.
	 */
	if ( ! window.wp || ! wp.plugins || ! wp.element || ! wp.components || ! wp.data ) {
		return;
	}

	var reglages = window.mtbSommeil;

	if ( ! reglages ) {
		return;
	}

	/*
	 * REPLI, ET DANS CE SENS-LÀ SEULEMENT — JAMAIS L'INVERSE.
	 *
	 * « wp.editPost.PluginPostStatusInfo » est DÉPRÉCIÉ DEPUIS WORDPRESS 6.6 : le lire en premier
	 * imprimerait un avertissement de dépréciation dans la console à chaque ouverture de l'éditeur.
	 * « wp.editor.PluginPostStatusInfo » est la forme courante ; le second n'est là que pour une
	 * installation plus ancienne. Ces deux phrases sont écrites parce que, sans elles, une chaîne
	 * future « simplifierait » l'expression en gardant la branche dépréciée.
	 */
	var Panneau = ( wp.editor && wp.editor.PluginPostStatusInfo )
		|| ( wp.editPost && wp.editPost.PluginPostStatusInfo );

	if ( ! Panneau ) {
		return;
	}

	var el = wp.element.createElement;

	/*
	 * LA CLÉ VIENT DU SERVEUR, COMME LES PHRASES, ET ELLE N'EST ÉCRITE NULLE PART ICI.
	 *
	 * Elle n'existe qu'à un seul endroit dans tout le dépôt — « MTB\Core\Query\MiseEnSommeil\CLE » —
	 * et « wp_add_inline_script() » l'apporte jusqu'ici dans « window.mtbSommeil.cle », exactement
	 * comme il apporte le libellé et l'aide. La recopier en dur ferait courir la panne la plus grave
	 * de ce module : la constante PHP changée, ce fichier continuerait d'écrire sous l'ancien nom, la
	 * case de la zone latérale cesserait de persister, la page répondrait 200 et le journal
	 * resterait vide.
	 *
	 * Repli explicite plutôt que confiance : si la charge du serveur n'apportait pas de clé
	 * exploitable, le panneau ne se monte pas du tout. Un panneau absent se voit ; un panneau qui
	 * écrit sous une clé vide ne se voit pas.
	 */
	var CLE = reglages.cle;

	if ( 'string' !== typeof CLE || '' === CLE ) {
		return;
	}

	/**
	 * La case à cocher de la zone latérale, et l'avertissement de page d'accueil le cas échéant.
	 *
	 * Lecture et écriture passent par « core/editor » et jamais par la façade REST en direct : l'état
	 * suit ainsi le cycle normal de l'éditeur — il devient une modification non enregistrée, le bouton
	 * « Mettre à jour » s'active, et c'est l'enregistrement de la page qui l'écrit. Écrire en direct
	 * poserait l'état AVANT que l'éleveuse n'ait confirmé, et un abandon de la page laisserait un
	 * contenu endormi sans qu'elle l'ait validé.
	 *
	 * @return {Object} Élément à afficher.
	 */
	function Interrupteur() {
		var meta = wp.data.useSelect( function ( select ) {
			return select( 'core/editor' ).getEditedPostAttribute( 'meta' );
		}, [] );

		var editPost = wp.data.useDispatch( 'core/editor' ).editPost;

		// L'état de départ est l'ABSENCE de la clé : « meta » peut ne rien porter, la case est alors décochée.
		var endormi = !! meta && '1' === meta[ CLE ];

		var avertissement = reglages.accueil
			? el(
				wp.components.Notice,
				{ status: 'warning', isDismissible: false },
				reglages.avertissement
			)
			: null;

		/*
		 * UN SEUL ENFANT PASSÉ AU PANNEAU, ET C'EST « FlexBlock > Flex ». MESURÉ, PAS CHOISI PAR GOÛT.
		 *
		 * LE DÉFAUT QUE CECI RÉPARE. « PluginPostStatusInfo » enveloppe ce qu'on lui donne dans UN
		 * « components-panel__row », qui est « display:flex; flex-direction:row;
		 * justify-content:space-between ». Lui passer DEUX enfants — la case et l'encart — les met
		 * CÔTE À CÔTE. Relevé au navigateur, WordPress 6.9, avant correction : case « largeur 121 px »,
		 * encart « largeur 112 px », sur la même ligne, l'avertissement rendu à un ou deux mots par
		 * ligne sur une quinzaine de lignes. L'arbitrage A5 du contrat #52 veut un interrupteur
		 * « jamais silencieux » sur la page d'accueil ; un avertissement illisible n'est pas silencieux,
		 * mais il ne prévient personne non plus — et c'est le SEUL garde-fou avant qu'elle ne retire
		 * l'adresse principale du site des moteurs de recherche.
		 *
		 * TROIS PIÈGES, ET IL FAUT LES TROIS REMÈDES À LA FOIS :
		 *
		 *   1. UN FRAGMENT NE SUFFIT PAS. Ses enfants restent frères dans le même flex, donc toujours
		 *      côte à côte. Il faut un vrai nœud conteneur.
		 *   2. UN CONTENEUR NU NE SUFFIT PAS NON PLUS. Enfant unique d'un flex, il vaut « flex:0 1 auto »
		 *      et se dimensionne à son contenu : on aurait l'empilement sans la largeur. D'où
		 *      « FlexBlock », la primitive DU CŒUR faite pour OCCUPER L'ESPACE DISPONIBLE d'un conteneur
		 *      flex — elle porte « flex:1 » et « min-width:0 ». Préférée à
		 *      « __experimentalVStack », qui empile mais reste « flex:0 1 auto » : lu dans le cœur
		 *      (« wp-includes/js/dist/components.js », « useFlex »), sa propriété « expanded » ne pose
		 *      « 100 % » que sur la HAUTEUR quand la direction est la colonne, jamais sur la largeur.
		 *      Son préfixe annonce en outre qu'il peut disparaître d'une version à l'autre.
		 *   3. SANS ÉCART, LES DEUX BOÎTES SE TOUCHENT EXACTEMENT. Mesuré : le bas de la case et le haut
		 *      de l'encart tombaient au même pixel, la case ne portant plus de marge basse depuis
		 *      « __nextHasNoMarginBottom ». C'est correct au sens du flux, illisible à l'œil, et
		 *      indistinguable d'un chevauchement pour une sonde automatique. D'où le « Flex » intérieur,
		 *      en colonne, avec l'écart de l'échelle du cœur : 8 px mesurés entre les deux boîtes.
		 *
		 * « align: stretch » est ÉCRIT PLUTÔT QUE LAISSÉ AU DÉFAUT. Lu dans « useFlex » : en colonne,
		 * « Flex » laisse « align-items » à « normal », qui se comporte déjà comme « stretch » — mais en
		 * RANGÉE il pose « center », qui ramènerait les deux boîtes à la largeur de leur contenu. L'écrire
		 * rend la pleine largeur indépendante du défaut du cœur comme de la direction.
		 *
		 * AUCUN STYLE EN LIGNE, AUCUNE FEUILLE DE STYLE, AUCUNE CLASSE À NOUS, DEUX COMPOSANTS DU CŒUR
		 * NON EXPÉRIMENTAUX : zéro octet de CSS produit par l'extension, comme l'exigent le §7 du
		 * contrat #52 et la décision 67.
		 */
		return el(
			Panneau,
			null,
			el(
				wp.components.FlexBlock,
				null,
				el(
					wp.components.Flex,
					{ direction: 'column', align: 'stretch', gap: 2 },
					el( wp.components.CheckboxControl, {
						/*
						 * MESURÉ, PAS SUPPOSÉ : sans cette propriété, WordPress 6.9 imprime dans la console,
						 * à chaque ouverture de l'éditeur, « Bottom margin styles for wp.components.
						 * CheckboxControl is deprecated since version 6.7 and will be removed in version
						 * 7.0 ». Même motif que le repli de « Panneau » ci-dessus : on ne laisse pas un
						 * avertissement de dépréciation s'installer dans la console de l'éleveuse. Elle n'a
						 * aucun effet hors de la marge basse du contrôle : l'écart de 8 px sous la case
						 * vient du « Flex » ci-dessus, et non de cette marge.
						 */
						__nextHasNoMarginBottom: true,
						label: reglages.libelle,
						help: reglages.aide,
						checked: endormi,
						onChange: function ( coche ) {
							var modification = {};

							modification[ CLE ] = coche ? '1' : '0';

							editPost( { meta: modification } );
						}
					} ),
					/*
					 * « null » hors de la page d'accueil : React ne rend alors AUCUN nœud. Le « Flex »
					 * n'a plus qu'un seul enfant, son écart ne s'applique donc à rien, et il ne reste ni
					 * conteneur vide ni espacement parasite sous la case. Vérifié à l'écran sur une page
					 * ordinaire, pas seulement supposé.
					 */
					avertissement
				)
			)
		);
	}

	wp.plugins.registerPlugin( 'mtb-sommeil', { render: Interrupteur } );
}() );
