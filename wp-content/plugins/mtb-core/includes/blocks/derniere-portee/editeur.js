/**
 * Bloc « Encart dernière portée » dans l'éditeur : un aperçu fidèle et un seul réglage.
 *
 * Aucune étape de construction : ce fichier est du JavaScript ordinaire, servi tel quel. Pas de JSX,
 * les éléments sont créés par wp.element.createElement.
 *
 * L'aperçu passe par ServerSideRender, le composant du cœur : le même rendu que sur le site, sans
 * qu'aucune détection de contexte ne soit faite côté serveur. Une représentation dessinée ici
 * devrait, pour savoir quelle portée est la dernière, refaire ce tri elle-même — ce qu'aucune moitié
 * du site n'a le droit de faire.
 *
 * Titre, description, mots-clés, catégorie, icône et attributs viennent tous de « block.json » :
 * rien n'est répété ici.
 */
( function ( blocs, elements, editeurDeBlocs, composants, RenduServeur ) {
	'use strict';

	// Une dépendance du cœur absente : on n'enregistre rien plutôt que de lever une erreur en console.
	if ( ! blocs || ! elements || ! editeurDeBlocs || ! composants || ! RenduServeur ) {
		return;
	}

	var creer = elements.createElement;

	/**
	 * Ce que l'éditrice voit quand aucune portée n'est publiée. Le visiteur, lui, ne voit rien.
	 *
	 * @return {Object} Élément de l'état vide.
	 */
	function etatVide() {
		return creer(
			'div',
			{ className: 'mtb-etat-vide mtb-etat-vide--derniere-portee' },
			creer( 'p', { className: 'mtb-etat-vide__nom' }, 'Encart dernière portée' ),
			creer(
				'p',
				{ className: 'mtb-etat-vide__phrase' },
				'Ce bloc n’affiche rien tant qu’aucune portée n’est publiée.'
			)
		);
	}

	/**
	 * Le seul réglage de l'encart : un titre d'accroche facultatif.
	 *
	 * Aucun texte fantôme dans le champ : il ne servirait qu'à suggérer une accroche, donc à en
	 * inventer une.
	 *
	 * @param {Object} proprietes Propriétés fournies par l'éditeur.
	 *
	 * @return {Object} Panneau latéral.
	 */
	function reglages( proprietes ) {
		return creer(
			editeurDeBlocs.InspectorControls,
			null,
			creer(
				composants.PanelBody,
				{ title: 'Réglages de l’encart' },
				creer( composants.TextControl, {
					label: 'Titre d’accroche',
					help: 'Facultatif. Laissé vide, aucun titre n’apparaît au-dessus de l’encart. L’encart montre toujours la portée née le plus récemment : il n’y a rien à choisir.',
					value: proprietes.attributes.accroche,
					onChange: function ( valeur ) {
						proprietes.setAttributes( { accroche: valeur } );
					},
					// Les deux drapeaux du cœur évitent une dépréciation en console sur WordPress 6.7+.
					__next40pxDefaultSize: true,
					__nextHasNoMarginBottom: true
				} )
			)
		);
	}

	blocs.registerBlockType( 'mtb/derniere-portee', {
		edit: function ( proprietes ) {
			return creer(
				elements.Fragment,
				null,
				reglages( proprietes ),
				creer(
					'div',
					editeurDeBlocs.useBlockProps(),
					creer( RenduServeur, {
						block: 'mtb/derniere-portee',
						attributes: proprietes.attributes,
						EmptyResponsePlaceholder: etatVide
					} )
				)
			);
		},
		// Bloc à rendu serveur : rien n'est enregistré dans le contenu de la page.
		save: function () {
			return null;
		}
	} );
/*
 * MESURÉ dans le cœur installé, pas déduit — WordPress 6.9 (wp-includes/version.php:19).
 *
 * wp-includes/js/dist/server-side-render.js, DERNIÈRE LIGNE (l. 265) :
 *     (window.wp = window.wp || {}).serverSideRender = __webpack_exports__["default"];
 * et la version réellement chargée dans l'éditeur, server-side-render.min.js :
 *     (window.wp=window.wp||{}).serverSideRender=t.default
 *
 * « __webpack_exports__["default"] » est lié (l. 40-42) à « index_default », qui vaut
 * (l. 257-260) « ServerSideRenderCompat = ServerSideRenderWithPostId » : le COMPOSANT
 * lui-même, jamais un objet de module. Il porte en plus « .ServerSideRender » et
 * « .useServerSideRender » en propriétés de compatibilité, mais AUCUNE « .default ».
 *
 * Les deux pannes redoutées sont donc écartées, chacune pour une raison mesurée : la
 * garde ci-dessus passe parce que la valeur est le composant (le bloc s'enregistre et
 * reste dans l'inséreur), et createElement reçoit un type d'élément valide (l'aperçu
 * ne casse pas). La forme défensive « .default || » n'est PAS écrite : « .default »
 * n'existant pas ici, elle serait du code mort laissant croire que le cas existe.
 *
 * Ordre de chargement vérifié sur la page de l'éditeur : server-side-render.min.js
 * arrive avant ce fichier (dépendance « wp-server-side-render » déclarée dans
 * bootstrap.php), donc la globale est déjà posée quand cette fonction s'exécute.
 */
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components, window.wp.serverSideRender );
