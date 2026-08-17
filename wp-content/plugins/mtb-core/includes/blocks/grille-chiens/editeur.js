/**
 * Déclare le composant « Grille de chiens » côté éditeur.
 *
 * Aucun texte affiché n'est écrit ici : l'étiquette du réglage, son aide et les libellés des cinq
 * choix arrivent de PHP dans « mtbGrilleChiens », construits depuis le vocabulaire du chien. Un
 * libellé recopié ici finirait par ne plus dire la même chose que le site.
 *
 * L'aperçu rendu par le serveur n'est pas décoratif : c'est lui qui produit la requête pendant
 * laquelle le serveur reconnaît le contexte d'édition, donc lui qui fait apparaître l'état vide.
 * Le retirer le ferait disparaître sans erreur.
 */
( function ( wp, donnees ) {
	'use strict';

	if ( ! wp || ! wp.blocks || ! wp.element || ! wp.blockEditor || ! wp.components || ! wp.serverSideRender ) {
		return;
	}

	if ( ! donnees || ! donnees.nom || ! donnees.reglage ) {
		return;
	}

	var creer = wp.element.createElement;
	var apercu = wp.serverSideRender;

	wp.blocks.registerBlockType( donnees.nom, {
		edit: function ( proprietes ) {
			var reglage = creer(
				wp.components.PanelBody,
				{
					title: donnees.reglage.etiquette,
					initialOpen: true
				},
				creer( wp.components.RadioControl, {
					label: donnees.reglage.etiquette,
					hideLabelFromVision: true,
					help: donnees.reglage.aide,
					selected: proprietes.attributes.statut || donnees.reglage.defaut,
					options: donnees.reglage.choix,
					onChange: function ( valeur ) {
						proprietes.setAttributes( { statut: valeur } );
					}
				} )
			);

			return creer(
				wp.element.Fragment,
				null,
				creer( wp.blockEditor.InspectorControls, null, reglage ),
				creer(
					'div',
					wp.blockEditor.useBlockProps(),
					creer( apercu, {
						block: donnees.nom,
						attributes: proprietes.attributes
					} )
				)
			);
		},
		save: function () {
			return null;
		}
	} );
} )( window.wp, window.mtbGrilleChiens );
