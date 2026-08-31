/**
 * Composant « Lien de recours » — aperçu dans l'éditeur.
 *
 * ES5, aucune syntaxe JSX, aucune étape de construction : le fichier est servi tel quel. Il n'est
 * atteignable que par la poignée « mtb-lien-de-recours-editeur », référencée par « editorScript »
 * seul : le visiteur ne le reçoit jamais.
 *
 * Le bloc n'est PAS insérable et n'offre AUCUN réglage : sa cible est écrite dans le gabarit, une
 * fois pour toutes. Ce fichier n'existe donc que pour montrer, dans l'éditeur de site, le libellé du
 * lien au lieu du cadre « ce bloc n'est pas pris en charge ». L'adresse réelle, elle, n'est calculée
 * qu'au rendu, par le serveur — l'éditeur n'en sait rien et ne doit pas prétendre le contraire.
 */
( function () {
	'use strict';

	if ( ! window.wp || ! wp.blocks || ! wp.element || ! wp.blockEditor ) {
		return;
	}

	var el = wp.element.createElement;
	var useBlockProps = wp.blockEditor.useBlockProps;

	// Recopie des libellés de « rendu.php ». L'autorité n'est pourtant ni ce fichier ni le PHP :
	// ce sont MASTER.md §10.3 (« Toutes les portées », « La meute ») et §9.5 (« Accueil »). Une passe
	// qui les resynchronise part de là — jamais du titre de l'archive des portées, jamais des
	// libellés du menu d'administration, qui leur ressemblent sans être régis par §10.3.
	var LIBELLES = {
		accueil: 'Accueil',
		portees: 'Toutes les portées',
		meute: 'La meute'
	};

	wp.blocks.registerBlockType( 'mtb/lien-de-recours', {
		edit: function ( proprietes ) {
			var racine = useBlockProps( { className: 'mtb-lien-de-recours' } );
			var cible = proprietes.attributes.cible;

			return el(
				'li',
				racine,
				LIBELLES[ cible ] || LIBELLES.accueil
			);
		},

		/*
		 * Une FONCTION qui rend null, jamais « save: null » : le cœur exige une fonction et
		 * refuserait l'enregistrement du bloc. Rien n'est écrit dans le gabarit — le balisage a une
		 * seule vérité, celle du serveur.
		 */
		save: function () {
			return null;
		}
	} );
}() );
