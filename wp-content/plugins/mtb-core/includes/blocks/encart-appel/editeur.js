/**
 * Composant « Encart d’appel » dans l’éditeur : un aperçu fidèle et trois réglages facultatifs.
 *
 * Aucune étape de construction : ce fichier est du JavaScript ordinaire, servi tel quel, sans JSX.
 * Il n’est chargé qu’à l’administration ; le visiteur ne reçoit pas un octet de script.
 *
 * L’aperçu passe par ServerSideRender, le composant du cœur. C’est l’argument entier en faveur de ce
 * choix : un aperçu dessiné ici devrait, pour montrer le numéro, en recopier la valeur dans ce
 * fichier — une deuxième écriture du même fait de domaine, ce qui est interdit. Le serveur reste la
 * seule source du numéro, et l’aperçu montre exactement ce que verra le visiteur.
 *
 * Titre, description, mots-clés, catégorie, icône et attributs viennent tous de « block.json » : rien
 * n’est répété ici.
 */
( function ( wp ) {
	'use strict';

	// Une dépendance du cœur absente : on n’enregistre rien plutôt que de lever une erreur en console.
	if ( ! wp || ! wp.blocks || ! wp.element || ! wp.blockEditor || ! wp.components || ! wp.data || ! wp.htmlEntities || ! wp.serverSideRender ) {
		return;
	}

	var creer = wp.element.createElement;
	var useSelect = wp.data.useSelect;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var PanelBody = wp.components.PanelBody;
	var TextControl = wp.components.TextControl;
	var SelectControl = wp.components.SelectControl;
	var decoder = wp.htmlEntities.decodeEntities;
	var RenduServeur = wp.serverSideRender;

	/*
	 * Nom du bloc, écrit UNE SEULE FOIS : l’enregistrement et l’aperçu serveur doivent viser le même.
	 * Écrit deux fois, une divergence enregistrerait bien le bloc et ne casserait que l’aperçu, sans
	 * une erreur — exactement le genre de panne muette que ce projet paie cher.
	 */
	var NOM = 'mtb/encart-appel';

	/*
	 * Requête des pages proposées au bouton. Seuls l’identifiant et le titre sont demandés : le
	 * sélecteur n’a besoin de rien d’autre, et rien d’autre ne transite.
	 *
	 * « per_page: 100 » et non « -1 » : MESURÉ sur le cœur installé (WordPress 6.9), « per_page=-1 »
	 * est refusé par l’API REST avec un code 400 (« per_page doit être compris entre 1 et 100 »),
	 * parce que la validation du paramètre s’exécute avant son assainissement. La liste ne se
	 * résoudrait jamais et le sélecteur resterait bloqué sur « Chargement… », donc désactivé : le
	 * réglage principal du composant serait inutilisable. 100 est le maximum accepté ; le site source
	 * compte 52 adresses en tout.
	 */
	var REQUETE_PAGES = {
		per_page: 100,
		status: 'publish',
		orderby: 'title',
		order: 'asc',
		_fields: 'id,title'
	};

	var AIDE_PAGE = 'Sans page choisie, l’encart s’affiche sans bouton. Le bouton porte le nom de la page.';
	var AIDE_PAGE_PERDUE = 'La page choisie n’est plus disponible. Choisissez-en une autre.';

	/**
	 * Ce que l’éditrice voit quand l’encart n’a ni numéro ni page à montrer. Le visiteur, lui, ne voit
	 * rien du tout.
	 *
	 * Le nom du composant est écrit en casse naturelle : les capitales sont posées par la feuille de
	 * l’éditeur, jamais tapées ici. Le cadre est émis seul, sans classe modificatrice et sans être
	 * enveloppé dans le composant : son apparence est unique à tous les composants du site.
	 *
	 * @return {Object} Élément de l’état vide.
	 */
	function etatVide() {
		return creer(
			'div',
			{ className: 'mtb-etat-vide' },
			creer( 'span', { className: 'mtb-etat-vide__nom' }, 'Encart d’appel' ),
			creer(
				'p',
				{ className: 'mtb-etat-vide__phrase' },
				'Ce bloc n’affiche rien tant qu’aucun numéro de téléphone ni aucune page de contact ne sont indiqués.'
			)
		);
	}

	/**
	 * Construit les options du sélecteur de page, dans l’ordre où le serveur les livre.
	 *
	 * Les titres passent par le décodeur d’entités du cœur : sans lui, une page nommée
	 * « Élevage &#038; famille » afficherait son entité en clair dans la liste.
	 *
	 * @param {Array} pages Pages publiées, telles que le magasin du cœur les rend.
	 *
	 * @return {Array} Options du sélecteur, « Aucune » en tête.
	 */
	function optionsPages( pages ) {
		var options = [ { value: '0', label: 'Aucune (pas de bouton)' } ];

		pages.forEach( function ( page ) {
			options.push( {
				value: String( page.id ),
				label: decoder( page.title && page.title.rendered ? page.title.rendered : '' )
			} );
		} );

		return options;
	}

	/**
	 * Le volet de réglages : trois champs facultatifs, dans l’ordre où ils se lisent à l’écran.
	 *
	 * Le champ du téléphone ne porte AUCUN texte fantôme. Y écrire le numéro par défaut le recopierait
	 * dans ce fichier — deuxième écriture d’un fait de domaine. L’aperçu, à deux centimètres, montre
	 * déjà le numéro réellement retenu.
	 *
	 * @param {Object} proprietes Propriétés fournies par l’éditeur.
	 * @param {Array}  pages      Pages publiées, ou null tant que la liste n’est pas résolue.
	 *
	 * @return {Object} Volet latéral.
	 */
	function reglages( proprietes, pages ) {
		var attributs = proprietes.attributes;
		var chargee = Array.isArray( pages );
		var choisie = String( attributs.page_id || 0 );
		var presente = false;
		var options;

		if ( chargee ) {
			options = optionsPages( pages );

			options.forEach( function ( option ) {
				if ( option.value === choisie ) {
					presente = true;
				}
			} );
		} else {
			/*
			 * Tant que la liste n’est pas revenue, le champ affiche « Chargement… » plutôt que de
			 * paraître vide alors qu’une page est bel et bien choisie.
			 */
			options = [ { value: choisie, label: 'Chargement…' } ];
		}

		return creer(
			InspectorControls,
			null,
			creer(
				PanelBody,
				{ title: 'Réglages de l’encart', initialOpen: true },
				creer( TextControl, {
					label: 'Phrase d’accroche',
					help: 'Facultatif. Une phrase sous le titre « Nous contacter ». Laissée vide, aucune phrase n’apparaît.',
					value: attributs.accroche,
					onChange: function ( valeur ) {
						proprietes.setAttributes( { accroche: valeur } );
					},
					// Les deux drapeaux du cœur évitent un avis de dépréciation en console.
					__next40pxDefaultSize: true,
					__nextHasNoMarginBottom: true
				} ),
				creer( TextControl, {
					label: 'Téléphone affiché',
					help: 'Laissez ce champ vide pour afficher le numéro de l’élevage. Ce que vous tapez ici s’affiche exactement tel quel, sur cette page seulement.',
					value: attributs.telephone,
					onChange: function ( valeur ) {
						proprietes.setAttributes( { telephone: valeur } );
					},
					__next40pxDefaultSize: true,
					__nextHasNoMarginBottom: true
				} ),
				/*
				 * Page choisie devenue introuvable — supprimée, dépubliée ou protégée par un mot de
				 * passe. Le champ retombe sur « Aucune », qui est exactement ce que voit le visiteur,
				 * et l’aide dit quoi faire. Le choix enregistré n’est pas effacé pour autant : si la
				 * page est republiée, le bouton revient tout seul.
				 */
				creer( SelectControl, {
					label: 'Page vers laquelle mène le bouton',
					help: chargee && ! presente ? AIDE_PAGE_PERDUE : AIDE_PAGE,
					value: chargee && ! presente ? '0' : choisie,
					options: options,
					disabled: ! chargee,
					onChange: function ( valeur ) {
						proprietes.setAttributes( { page_id: parseInt( valeur, 10 ) || 0 } );
					},
					__next40pxDefaultSize: true,
					__nextHasNoMarginBottom: true
				} )
			)
		);
	}

	wp.blocks.registerBlockType( NOM, {
		edit: function ( proprietes ) {
			var pages = useSelect( function ( select ) {
				return select( 'core' ).getEntityRecords( 'postType', 'page', REQUETE_PAGES );
			}, [] );

			return creer(
				wp.element.Fragment,
				null,
				reglages( proprietes, pages ),
				creer(
					'div',
					useBlockProps(),
					creer( RenduServeur, {
						block: NOM,
						attributes: proprietes.attributes,
						EmptyResponsePlaceholder: etatVide
					} )
				)
			);
		},
		// Bloc à rendu serveur : rien n’est enregistré dans le contenu de la page.
		save: function () {
			return null;
		}
	} );
} )( window.wp );
