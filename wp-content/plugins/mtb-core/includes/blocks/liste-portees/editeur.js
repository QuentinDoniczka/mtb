/**
 * Côté éditeur du composant « Liste de portées ».
 *
 * Aucune étape de construction : ce fichier est servi tel quel, sans JSX. Le titre, la catégorie, la
 * description et les attributs viennent de block.json, que le cœur transmet déjà à l'éditeur.
 *
 * C'est ici, et nulle part côté serveur, que se décide « suis-je dans l'éditeur ». Le rendu public ne
 * connaît que le public.
 */
( function ( wp ) {
	'use strict';

	var el = wp.element.createElement;
	var useSelect = wp.data.useSelect;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelBody = wp.components.PanelBody;
	var TextControl = wp.components.TextControl;
	var SelectControl = wp.components.SelectControl;
	var ServerSideRender = wp.serverSideRender;

	// Années transmises par le serveur, uniquement dans l'éditeur.
	var annees = Array.isArray( window.mtbListePorteesAnnees ) ? window.mtbListePorteesAnnees : [];

	/**
	 * Construit les options du sélecteur d'année, dans l'ordre où le serveur les livre.
	 *
	 * @param {string} choisie Année enregistrée dans le bloc.
	 * @return {Array} Options du sélecteur.
	 */
	function optionsAnnees( choisie ) {
		var options = [ { value: '', label: 'Toutes les années' } ];
		var presente = false;

		annees.forEach( function ( annee ) {
			options.push( { value: annee, label: annee } );

			if ( annee === choisie ) {
				presente = true;
			}
		} );

		/*
		 * L'année enregistrée est conservée même si plus aucune portée ne la porte : la dernière portée
		 * de cette année a pu être dépubliée, et effacer le réglage en silence perdrait son choix.
		 */
		if ( '' !== choisie && ! presente ) {
			options.push( { value: choisie, label: choisie } );
		}

		return options;
	}

	/**
	 * Encadré affiché tant qu'aucune portée n'est publiée. Deux paragraphes, pour rester lisible même
	 * sans feuille de style.
	 *
	 * Les crochets « mtb-etat-vide* » sont ceux du lot : l'apparence de cet encadré est unique aux dix
	 * composants et vit dans la feuille de l'éditeur. Les crochets locaux « __vide-editeur* » sont
	 * conservés à côté pour que ce fichier n'ait pas à être rouvert le jour où elle bouge.
	 *
	 * @return {Object} Élément à afficher.
	 */
	function encadreVide() {
		return el(
			'div',
			{ className: 'mtb-etat-vide mtb-liste-portees__vide-editeur' },
			el(
				'p',
				{ className: 'mtb-etat-vide__nom mtb-liste-portees__vide-editeur-nom' },
				'Liste de portées'
			),
			el(
				'p',
				{ className: 'mtb-etat-vide__phrase mtb-liste-portees__vide-editeur-phrase' },
				"Ce bloc n'affiche rien tant qu'aucune portée n'est publiée."
			)
		);
	}

	wp.blocks.registerBlockType( 'mtb/liste-portees', {
		edit: function ( proprietes ) {
			var attributs = proprietes.attributes;
			var blockProps = useBlockProps();

			// Seule l'existence d'une portée publiée est demandée, jamais un champ de portée.
			var publiees = useSelect( function ( select ) {
				return select( 'core' ).getEntityRecords( 'postType', 'mtb_portee', {
					per_page: 1,
					status: 'publish',
					_fields: 'id'
				} );
			}, [] );

			var reglages = el(
				InspectorControls,
				{},
				el(
					PanelBody,
					{ title: 'Réglages', initialOpen: true },
					el( TextControl, {
						label: 'Nombre de portées à afficher',
						help: 'Laissez vide pour afficher toutes les portées.',
						/*
						 * Sans ce drapeau, le cœur inscrit un avis de dépréciation dans la console à
						 * chaque affichage du panneau. Il n'emporte aucune décision visuelle : il
						 * demande au composant du cœur son espacement courant plutôt que l'ancien.
						 */
						__nextHasNoMarginBottom: true,
						type: 'number',
						min: '1',
						step: '1',
						value: attributs.nombre || '',
						onChange: function ( valeur ) {
							proprietes.setAttributes( { nombre: valeur } );
						}
					} ),
					el( SelectControl, {
						label: 'Année',
						help: "La liste n'affiche que les portées nées cette année-là.",
						__nextHasNoMarginBottom: true,
						value: attributs.annee || '',
						options: optionsAnnees( attributs.annee || '' ),
						onChange: function ( valeur ) {
							proprietes.setAttributes( { annee: valeur } );
						}
					} )
				)
			);

			var corps = null;

			if ( Array.isArray( publiees ) ) {
				corps = 0 === publiees.length
					? encadreVide()
					: el( ServerSideRender, {
						block: 'mtb/liste-portees',
						attributes: attributs
					} );
			}

			// Tant que la lecture n'a pas abouti, le bloc reste vide : aucune phrase n'est inventée.
			return el( 'div', blockProps, reglages, corps );
		},
		save: function () {
			return null;
		}
	} );
} )( window.wp );
