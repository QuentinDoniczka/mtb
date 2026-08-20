/**
 * Composant « Tableau de résultats » — écran d'édition.
 *
 * ES5, aucune syntaxe JSX, aucune étape de construction : le fichier est servi tel quel, lisible et
 * modifiable sans outillage.
 *
 * Aucun texte affiché n'est écrit ici : l'étiquette du réglage, son aide, les dix libellés de choix
 * et les onze phrases d'état vide arrivent de PHP dans « mtbTableauResultats », déjà composées. Ce
 * script en CHOISIT une, il n'en compose aucune — c'est la règle que le serveur impose au thème,
 * appliquée à notre propre JavaScript.
 *
 * L'aperçu affiché dans la toile est le rendu du serveur, exactement celui que verra le visiteur :
 * le balisage n'a qu'une seule vérité, et rien n'est enregistré dans la page.
 */
( function ( wp, donnees ) {
	'use strict';

	/*
	 * Une dépendance manquante laisse le composant non enregistré : le contenu déjà saisi reste
	 * intact et le site continue de le rendre. « wp-server-side-render » est indispensable —
	 * l'aperçu de la toile EST le rendu du serveur.
	 */
	if ( ! wp || ! wp.blocks || ! wp.element || ! wp.blockEditor || ! wp.components || ! wp.serverSideRender ) {
		return;
	}

	if ( ! donnees || ! donnees.nom || ! donnees.reglage || ! donnees.etatVide ) {
		return;
	}

	var el = wp.element.createElement;
	var RenduServeur = wp.serverSideRender;

	/**
	 * La phrase d'état vide qui correspond au réglage de l'instance.
	 *
	 * @param {string} source     Mode de l'instance.
	 * @param {string} discipline Clé de discipline, chaîne vide pour « toutes ».
	 * @return {string} Phrase composée par le serveur.
	 */
	function phraseEtatVide( source, discipline ) {
		var phrases = donnees.etatVide.phrases || {};

		if ( 'chien-courant' === source ) {
			return donnees.etatVide.palmares || '';
		}

		if ( Object.prototype.hasOwnProperty.call( phrases, discipline ) ) {
			return phrases[ discipline ];
		}

		return phrases[ '' ] || '';
	}

	wp.blocks.registerBlockType( donnees.nom, {
		edit: function ( proprietes ) {
			var attributs = proprietes.attributes || {};
			var discipline = 'string' === typeof attributs.discipline ? attributs.discipline : donnees.reglage.defaut;
			var source = 'chien-courant' === attributs.source ? 'chien-courant' : 'discipline';

			/*
			 * Balisage écrit à la main plutôt qu'un composant du cœur : l'apparence commune aux
			 * composants du catalogue vit dans la feuille « editor.css » du thème, qui habille les
			 * trois crochets ci-dessous. Exactement deux éléments enfants, jamais un troisième, et
			 * AUCUNE classe modificatrice — un crochet propre à un composant ferait de cette feuille
			 * commune une négociation bloc par bloc.
			 *
			 * Le nom est porté par un « span » et non par un « p » : « editor.css » lui pose
			 * « display: block » sans remettre les marges à zéro, et un « p » hériterait de la marge
			 * basse de « base.css ». Il est écrit en CASSE NATURELLE : les capitales sont posées en
			 * « text-transform », faute de quoi un lecteur d'écran épellerait le mot.
			 */
			var EtatVide = wp.element.useCallback(
				function () {
					return el(
						'div',
						{ className: 'mtb-etat-vide' },
						el( 'span', { className: 'mtb-etat-vide__nom' }, donnees.nomAffiche ),
						el( 'p', { className: 'mtb-etat-vide__phrase' }, phraseEtatVide( source, discipline ) )
					);
				},
				[ source, discipline ]
			);

			/*
			 * Le mode « palmarès d'une fiche chien » n'est jamais réglable ici : il n'est écrit que
			 * par un gabarit, et l'éleveuse n'a rien à choisir sur une fiche de chien.
			 */
			var reglage = el(
				wp.components.PanelBody,
				{
					title: donnees.reglage.etiquette,
					initialOpen: true
				},
				el( wp.components.RadioControl, {
					label: donnees.reglage.etiquette,
					hideLabelFromVision: true,
					help: donnees.reglage.aide,
					selected: discipline,
					options: donnees.reglage.choix,
					onChange: function ( valeur ) {
						proprietes.setAttributes( { discipline: valeur } );
					}
				} )
			);

			return el(
				wp.element.Fragment,
				null,
				el( wp.blockEditor.InspectorControls, null, reglage ),
				el(
					'div',
					wp.blockEditor.useBlockProps(),
					el( RenduServeur, {
						block: donnees.nom,
						attributes: proprietes.attributes,
						EmptyResponsePlaceholder: EtatVide
					} )
				)
			);
		},

		/*
		 * Rendu serveur : rien n'est enregistré dans la page. Le cœur exige une FONCTION ici — un
		 * « save: null » fait échouer la validation d'enregistrement du bloc.
		 */
		save: function () {
			return null;
		}
	} );
} )( window.wp, window.mtbTableauResultats );
