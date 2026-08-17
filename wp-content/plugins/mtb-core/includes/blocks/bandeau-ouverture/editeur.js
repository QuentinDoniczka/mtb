/**
 * Écran d'édition du composant « Bandeau d'ouverture ».
 *
 * ES5, aucune syntaxe JSX, aucune étape de construction : le fichier est servi tel quel, lisible et
 * modifiable sans outillage. Le rendu affiché dans la toile est celui du serveur, exactement celui
 * que verra le visiteur : le balisage n'a qu'une seule vérité, et aucune dérive de validation de bloc
 * ne peut se produire puisque rien n'est enregistré dans la page.
 */
( function () {
	'use strict';

	/*
	 * La liste couvre les six dépendances déclarées à l'enregistrement du script, « wp-server-side-render »
	 * comprise : l'aperçu de la toile EST le rendu du serveur, et sans elle « edit » lèverait une erreur
	 * de rendu qui vide l'écran d'édition. Une dépendance manquante laisse plutôt le composant
	 * non enregistré : le contenu déjà saisi reste intact et le site continue de le rendre.
	 */
	if ( ! window.wp || ! wp.blocks || ! wp.element || ! wp.blockEditor || ! wp.components || ! wp.data || ! wp.serverSideRender ) {
		return;
	}

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var MediaUpload = wp.blockEditor.MediaUpload;
	var MediaUploadCheck = wp.blockEditor.MediaUploadCheck;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var Button = wp.components.Button;
	var Notice = wp.components.Notice;
	var PanelBody = wp.components.PanelBody;
	var TextControl = wp.components.TextControl;
	var TextareaControl = wp.components.TextareaControl;
	var RenduServeur = wp.serverSideRender;

	var NOM = 'mtb/bandeau-ouverture';

	var NOM_AFFICHE = 'Bandeau d\'ouverture';
	var PHRASE_VIDE = 'Ce bloc n\'affiche rien tant qu\'aucune photo n\'est choisie et que la page n\'a pas de titre.';

	/**
	 * Rend un texte en chaîne sûre, quelle que soit la valeur reçue.
	 *
	 * @param {*} valeur Valeur à normaliser.
	 * @return {string} Chaîne sans espaces aux extrémités.
	 */
	function texte( valeur ) {
		return 'string' === typeof valeur ? valeur.replace( /^\s+|\s+$/g, '' ) : '';
	}

	/**
	 * État vide, servi partout où le composant n'affiche rien : avant l'appel au serveur comme lorsque
	 * le serveur répond une chaîne vide. Les deux chemins rendent le même balisage.
	 *
	 * Balisage écrit à la main plutôt qu'un composant du cœur : l'apparence commune aux dix composants
	 * du catalogue vit dans la feuille « editor.css » du thème, qui habille les trois crochets
	 * ci-dessous. Exactement deux éléments enfants, jamais un troisième — un élément de plus chez un
	 * seul composant ferait de cette feuille commune une négociation bloc par bloc. Le bouton de choix
	 * de la photo n'y figure donc pas : le panneau latéral reste le seul chemin vers la photo.
	 *
	 * Le nom du composant est écrit en CASSE NATURELLE et jamais en capitales : les capitales sont
	 * posées par « editor.css » en « text-transform: uppercase ». C'est un point d'interface pour les
	 * cinq composants sœurs — les accents des capitales françaises sont ainsi produits par le moteur et
	 * non par la frappe, et un lecteur d'écran ne se met pas à épeler des capitales littérales.
	 *
	 * Composant déclaré une seule fois, hors du rendu : le redéfinir à chaque passage le remonterait
	 * entièrement à chaque frappe au clavier.
	 *
	 * @return {Object} Élément à afficher.
	 */
	function EtatVide() {
		return el(
			'div',
			{ className: 'mtb-etat-vide' },
			el( 'span', { className: 'mtb-etat-vide__nom' }, NOM_AFFICHE ),
			el( 'p', { className: 'mtb-etat-vide__phrase' }, PHRASE_VIDE )
		);
	}

	wp.blocks.registerBlockType( NOM, {
		edit: function ( proprietes ) {
			var attributs = proprietes.attributes;
			var definir = proprietes.setAttributes;
			var identifiantDuBloc = proprietes.clientId;

			var photo = attributs.photo ? parseInt( attributs.photo, 10 ) : 0;

			if ( isNaN( photo ) || photo < 0 ) {
				photo = 0;
			}

			var titre = 'string' === typeof attributs.titre ? attributs.titre : '';
			var accroche = 'string' === typeof attributs.accroche ? attributs.accroche : '';

			var lu = wp.data.useSelect(
				function ( selectionner ) {
					var ecran = selectionner( 'core/editor' );
					var toile = selectionner( 'core/block-editor' );
					var contenus = selectionner( 'core' );
					var parent = toile ? toile.getBlockRootClientId( identifiantDuBloc ) : null;

					return {
						postId: ecran ? ecran.getCurrentPostId() : 0,
						titreDeLaPage: ecran ? ecran.getEditedPostAttribute( 'title' ) : '',
						/*
						 * La position est lue sur l'arbre VIVANT de l'éditeur, donc plus fraîche que la
						 * décision du serveur, qui juge sur le contenu enregistré. Deux vérités, mais
						 * l'une est un conseil et l'autre un rendu, et l'écart se referme au premier
						 * enregistrement.
						 */
						estPremier: ! parent && 0 === ( toile ? toile.getBlockIndex( identifiantDuBloc ) : -1 ),
						/*
						 * getEntityRecord et non getMedia : ce dernier est déprécié depuis la version
						 * 6.9 du cœur et imprime un avertissement dans la console à chaque rendu.
						 */
						fichier: photo && contenus ? contenus.getEntityRecord( 'postType', 'attachment', photo ) : null
					};
				},
				[ identifiantDuBloc, photo ]
			);

			var titreEffectif = '' !== texte( titre ) ? texte( titre ) : texte( lu.titreDeLaPage );
			var estVide = 0 === photo && '' === titreEffectif && '' === texte( accroche );
			var fichierNonPhoto = !! ( lu.fichier && lu.fichier.media_type && 'image' !== lu.fichier.media_type );

			/**
			 * Bouton qui ouvre la bibliothèque de photos.
			 *
			 * MediaUploadCheck entoure MediaUpload : une utilisatrice sans le droit de téléverser voit
			 * le réglage désactivé, au lieu d'un bouton qui échoue sans rien dire.
			 *
			 * @param {string} libelle Texte du bouton.
			 * @return {Object} Élément à afficher.
			 */
			function boutonPhoto( libelle ) {
				return el(
					MediaUploadCheck,
					null,
					el( MediaUpload, {
						allowedTypes: [ 'image' ],
						multiple: false,
						value: photo,
						onSelect: function ( choisie ) {
							definir( { photo: choisie && choisie.id ? parseInt( choisie.id, 10 ) : 0 } );
						},
						render: function ( ouverture ) {
							return el(
								Button,
								{ variant: 'secondary', onClick: ouverture.open },
								libelle
							);
						}
					} )
				);
			}

			var reglagesPhoto = [
				el( 'div', { key: 'choisir' }, boutonPhoto( photo ? 'Remplacer la photo' : 'Choisir une photo' ) )
			];

			if ( photo ) {
				reglagesPhoto.push(
					el(
						Button,
						{
							key: 'retirer',
							variant: 'link',
							isDestructive: true,
							onClick: function () {
								definir( { photo: 0 } );
							}
						},
						'Retirer la photo'
					)
				);
			}

			/*
			 * La classe d'aide est celle du cœur : l'aide sous un réglage doit ressembler à toutes les
			 * autres aides de l'éditeur. Aucune apparence n'est inventée ici.
			 */
			reglagesPhoto.push(
				el(
					'p',
					{ key: 'aide', className: 'components-base-control__help' },
					'La description de la photo — celle que lisent les personnes aveugles — se saisit sur la photo elle-même, une seule fois, et sert partout où la photo apparaît.'
				)
			);

			var panneaux = el(
				InspectorControls,
				null,
				el( PanelBody, { title: 'Photo', initialOpen: true }, reglagesPhoto ),
				el(
					PanelBody,
					{ title: 'Titre et accroche', initialOpen: true },
					/*
					 * Champs de texte simple, jamais un RichText : ni gras, ni italique, et surtout
					 * AUCUN LIEN. Ce n'est pas de la frilosité, c'est une mesure — un lien posé sur le
					 * voile du bandeau donne 4,09:1, échec AA pour du texte normal, et le système de
					 * design ne définit aucune encre de lien pour un fond « voile sur photo ». Passer
					 * ces champs en RichText demande d'abord que cette paire de contraste soit définie.
					 *
					 * « __nextHasNoMarginBottom » évite l'avertissement de dépréciation que le cœur
					 * imprime dans la console depuis la version 6.7 ; le réglage devient le défaut en
					 * version 7.0, où la clé sera simplement ignorée.
					 */
					el( TextControl, {
						__nextHasNoMarginBottom: true,
						label: 'Titre du bandeau',
						help: 'Laissez vide : le bandeau reprend alors le titre de la page.',
						value: titre,
						onChange: function ( valeur ) {
							definir( { titre: valeur } );
						}
					} ),
					el( TextareaControl, {
						__nextHasNoMarginBottom: true,
						label: 'Accroche',
						help: 'Une phrase de présentation, sous le titre. Facultative.',
						rows: 3,
						value: accroche,
						onChange: function ( valeur ) {
							definir( { accroche: valeur } );
						}
					} )
				)
			);

			var avis = [];

			/*
			 * Le titre affiché deux fois est accepté, puis signalé : le supprimer en douce ferait
			 * disparaître son titre à cause d'un paragraphe vide placé au-dessus, sans le moindre
			 * indice. Un défaut visible vaut mieux qu'un défaut muet. L'avertissement ne s'affiche que
			 * si un titre est réellement rendu, sinon il annoncerait un doublon qui n'existe pas.
			 */
			if ( ! lu.estPremier && '' !== titreEffectif ) {
				avis.push(
					el(
						Notice,
						{ key: 'position', status: 'warning', isDismissible: false },
						'Ce bandeau n\'est pas le premier bloc de la page : le titre s\'affichera deux fois. Déplacez-le tout en haut.'
					)
				);
			}

			if ( fichierNonPhoto ) {
				avis.push(
					el(
						Notice,
						{ key: 'fichier', status: 'warning', isDismissible: false },
						'Le fichier choisi n\'est pas une photo. Choisissez une photo.'
					)
				);
			}

			var corps;

			if ( estVide ) {
				corps = el( EtatVide );
			} else {
				corps = el( RenduServeur, {
					block: NOM,
					attributes: { photo: photo, titre: titre, accroche: accroche },
					/*
					 * Sans « post_id », l'aperçu n'a aucun contexte de contenu : le repli sur le titre
					 * de la page serait vide et elle verrait un bandeau muet là où le site en montrera
					 * un titré.
					 */
					urlQueryArgs: { post_id: lu.postId },
					EmptyResponsePlaceholder: EtatVide
				} );
			}

			return el(
				Fragment,
				null,
				panneaux,
				el( 'div', useBlockProps(), avis, corps )
			);
		},

		/*
		 * Rendu serveur : rien n'est enregistré dans la page. Le cœur exige une FONCTION ici — un
		 * « save: null » fait échouer la validation d'enregistrement du bloc, avec une erreur en
		 * console et un composant absent de l'insérteur.
		 */
		save: function () {
			return null;
		}
	} );
} )();
