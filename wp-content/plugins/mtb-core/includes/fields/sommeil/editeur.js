/**
 * Interrupteur « en sommeil » — panneau « Résumé » de l'éditeur de blocs.
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
	 * case du panneau « Résumé » cesserait de persister, la page répondrait 200 et le journal
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
	 * La case à cocher du panneau « Résumé », et l'avertissement de page d'accueil le cas échéant.
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

		return el(
			Panneau,
			null,
			el( wp.components.CheckboxControl, {
				/*
				 * MESURÉ, PAS SUPPOSÉ : sans cette propriété, WordPress 6.9 imprime dans la console, à
				 * chaque ouverture de l'éditeur, « Bottom margin styles for wp.components.
				 * CheckboxControl is deprecated since version 6.7 and will be removed in version 7.0 ».
				 * Même motif que le repli de « Panneau » ci-dessus : on ne laisse pas un avertissement
				 * de dépréciation s'installer dans la console de l'éleveuse. Elle n'a aucun effet hors
				 * de la marge basse du contrôle, que le panneau « Résumé » compose lui-même.
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
			avertissement
		);
	}

	wp.plugins.registerPlugin( 'mtb-sommeil', { render: Interrupteur } );
}() );
