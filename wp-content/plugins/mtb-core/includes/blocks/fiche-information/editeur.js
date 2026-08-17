/**
 * Composant « Fiche d'information » — écran d'édition.
 *
 * JavaScript ordinaire, servi tel quel : aucune étape de construction, aucun JSX.
 */
( function ( wp ) {
	'use strict';

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;

	var useBlockProps = wp.blockEditor.useBlockProps;
	var useInnerBlocksProps = wp.blockEditor.useInnerBlocksProps;
	var InnerBlocks = wp.blockEditor.InnerBlocks;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var RichText = wp.blockEditor.RichText;
	var MediaUpload = wp.blockEditor.MediaUpload;
	var MediaUploadCheck = wp.blockEditor.MediaUploadCheck;

	var PanelBody = wp.components.PanelBody;
	var SelectControl = wp.components.SelectControl;
	var TextControl = wp.components.TextControl;
	var Button = wp.components.Button;

	var useSelect = wp.data.useSelect;

	/*
	 * La prose n'accepte que le paragraphe et la liste. « allowedBlocks » ne contraint qu'un niveau :
	 * les éléments d'une liste restent insérables dans la liste elle-même.
	 */
	var BLOCS_AUTORISES = [ 'core/paragraph', 'core/list' ];
	var GABARIT = [ [ 'core/paragraph', {} ] ];

	/**
	 * Retire le balisage et les espaces d'une valeur, pour la seule décision « est-ce vide ».
	 *
	 * @param {*} valeur Valeur enregistrée, chaîne ou objet de texte enrichi.
	 * @return {string} Texte nu, jamais réutilisé en sortie.
	 */
	function texteNu( valeur ) {
		if ( null === valeur || undefined === valeur ) {
			return '';
		}

		return String( valeur ).replace( /<[^>]*>/g, '' ).replace( /&nbsp;/g, ' ' ).trim();
	}

	/**
	 * Dit si la prose ne porte aucun texte, listes imbriquées comprises.
	 *
	 * @param {Array} blocs Blocs enfants.
	 * @return {boolean} Vrai si rien n'a été tapé.
	 */
	function proseEstVide( blocs ) {
		if ( ! blocs || 0 === blocs.length ) {
			return true;
		}

		for ( var i = 0; i < blocs.length; i++ ) {
			var bloc = blocs[ i ];

			if ( bloc.attributes && '' !== texteNu( bloc.attributes.content ) ) {
				return false;
			}

			if ( bloc.innerBlocks && ! proseEstVide( bloc.innerBlocks ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Adresse de la photo à montrer dans le canevas.
	 *
	 * @param {Object} photo Enregistrement de la médiathèque.
	 * @return {string} Adresse, ou chaîne vide si elle n'est pas encore connue.
	 */
	function adressePhoto( photo ) {
		if ( ! photo ) {
			return '';
		}

		var tailles = photo.media_details && photo.media_details.sizes ? photo.media_details.sizes : null;

		if ( tailles && tailles.large && tailles.large.source_url ) {
			return tailles.large.source_url;
		}

		return photo.source_url ? photo.source_url : '';
	}

	/**
	 * Cadre d'état vide, jamais rendu par le serveur : il n'existe que dans l'éditeur.
	 *
	 * Deux crochets sur la racine, volontairement : « mtb-etat-vide » porte l'apparence commune aux
	 * dix composants du catalogue (MASTER §9.1), écrite une seule fois dans « editor.css » du thème ;
	 * « mtb-fiche-information__vide » est réservé à un ajustement propre à ce composant, et jamais à
	 * l'apparence commune. Ce module n'émet que les crochets, aucune règle visuelle.
	 *
	 * @return {Object} Élément du cadre.
	 */
	function cadreVide() {
		return el(
			'div',
			{ className: 'mtb-etat-vide mtb-fiche-information__vide' },
			el( 'p', { className: 'mtb-etat-vide__nom' }, 'FICHE D\'INFORMATION' ),
			el(
				'p',
				{ className: 'mtb-etat-vide__phrase' },
				'Ce bloc n\'affiche rien tant qu\'aucun texte ni aucune photo n\'est renseigné.'
			)
		);
	}

	wp.blocks.registerBlockType( 'mtb/fiche-information', {
		edit: function ( props ) {
			var attributs = props.attributes;
			var majAttributs = props.setAttributes;
			var identifiantPhoto = attributs.photo_id ? attributs.photo_id : 0;

			// Les trois crochets d'état sont appelés inconditionnellement, avant toute branche.
			var proprietesRacine = useBlockProps( {
				className: 'mtb-fiche-information mtb-fiche-information--editeur'
			} );

			var proprietesProse = useInnerBlocksProps(
				{ className: 'mtb-fiche-information__prose' },
				{
					allowedBlocks: BLOCS_AUTORISES,
					template: GABARIT,
					templateLock: false
				}
			);

			var lecture = useSelect(
				function ( select ) {
					/*
					 * getEntityRecord et non getMedia : ce dernier est déprécié depuis la
					 * version 6.9 et son emploi émet un avertissement de console à chaque
					 * chargement de l'éditeur. Forme gelée par l'amendement 4 du contrat.
					 * La résolution se surveille sur getEntityRecord, jamais sur getMedia,
					 * qui n'en dépend plus.
					 */
					var photo = identifiantPhoto
						? select( 'core' ).getEntityRecord( 'postType', 'attachment', identifiantPhoto )
						: null;

					return {
						photo: photo,
						photoIntrouvable: Boolean(
							identifiantPhoto
								&& select( 'core' ).hasFinishedResolution(
									'getEntityRecord',
									[ 'postType', 'attachment', identifiantPhoto ]
								)
								&& ! photo
						),
						enfants: select( 'core/block-editor' ).getBlocks( props.clientId )
					};
				},
				[ identifiantPhoto, props.clientId ]
			);

			var photoUtilisable = 0 < identifiantPhoto && false === lecture.photoIntrouvable;
			var adresse = adressePhoto( lecture.photo );

			var figure = null;

			if ( photoUtilisable ) {
				figure = el(
					'figure',
					{ className: 'mtb-fiche-information__figure' },
					el(
						'div',
						{
							className: 'mtb-fiche-information__photo',
							'data-cadrage': attributs.cadrage
						},
						'' === adresse
							? null
							: el( 'img', {
								className: 'mtb-fiche-information__image',
								src: adresse,
								alt: attributs.photo_description
							} )
					),
					'' === texteNu( attributs.photo_legende )
						? null
						: el(
							'figcaption',
							{ className: 'mtb-fiche-information__legende' },
							attributs.photo_legende
						)
				);
			}

			var titre = el( RichText, {
				tagName: attributs.niveau_titre,
				className: 'mtb-fiche-information__titre',
				value: attributs.titre,
				allowedFormats: [ 'core/bold', 'core/italic', 'core/link' ],
				placeholder: 'Titre de la section',
				onChange: function ( valeur ) {
					majAttributs( { titre: valeur } );
				}
			} );

			var prose = el( 'div', proprietesProse );

			var etatVide = null;

			if (
				'' === texteNu( attributs.titre )
				&& false === photoUtilisable
				&& proseEstVide( lecture.enfants )
			) {
				etatVide = cadreVide();
			}

			var reglagesPhoto = null;

			if ( photoUtilisable ) {
				reglagesPhoto = el(
					Fragment,
					null,
					el( TextControl, {
						__next40pxDefaultSize: true,
						__nextHasNoMarginBottom: true,
						label: 'Description de la photo (pour les personnes aveugles)',
						help: 'Décrivez ce que montre la photo. Laissez vide si la photo n\'apporte aucune information.',
						value: attributs.photo_description,
						onChange: function ( valeur ) {
							majAttributs( { photo_description: valeur } );
						}
					} ),
					el( TextControl, {
						__next40pxDefaultSize: true,
						__nextHasNoMarginBottom: true,
						label: 'Légende de la photo',
						help: 'Texte affiché sous la photo. Facultatif.',
						value: attributs.photo_legende,
						onChange: function ( valeur ) {
							majAttributs( { photo_legende: valeur } );
						}
					} ),
					el( SelectControl, {
						__next40pxDefaultSize: true,
						__nextHasNoMarginBottom: true,
						label: 'Position de la photo',
						help: 'La photo se place avant ou après les paragraphes. Le titre reste toujours en premier.',
						value: attributs.position_photo,
						options: [
							{ label: 'Photo au-dessus du texte', value: 'dessus' },
							{ label: 'Photo sous le texte', value: 'dessous' }
						],
						onChange: function ( valeur ) {
							majAttributs( { position_photo: valeur } );
						}
					} ),
					el( SelectControl, {
						__next40pxDefaultSize: true,
						__nextHasNoMarginBottom: true,
						label: 'Cadrage de la photo',
						help: 'Choisissez la partie de la photo qui doit rester visible si elle est recadrée.',
						value: attributs.cadrage,
						options: [
							{ label: 'Haut gauche', value: 'haut_gauche' },
							{ label: 'Haut', value: 'haut' },
							{ label: 'Centre', value: 'centre' },
							{ label: 'Haut droite', value: 'haut_droite' },
							{ label: 'Bas', value: 'bas' }
						],
						onChange: function ( valeur ) {
							majAttributs( { cadrage: valeur } );
						}
					} )
				);
			}

			var reglages = el(
				InspectorControls,
				null,
				el(
					PanelBody,
					null,
					el( SelectControl, {
						__next40pxDefaultSize: true,
						__nextHasNoMarginBottom: true,
						label: 'Niveau du titre',
						help: '« Titre de section » pour un titre principal de la page, « Sous-titre » pour une subdivision.',
						value: attributs.niveau_titre,
						options: [
							{ label: 'Titre de section', value: 'h2' },
							{ label: 'Sous-titre', value: 'h3' }
						],
						onChange: function ( valeur ) {
							majAttributs( { niveau_titre: valeur } );
						}
					} )
				),
				el(
					PanelBody,
					{ title: 'Photo' },
					el(
						MediaUploadCheck,
						null,
						el( MediaUpload, {
							allowedTypes: [ 'image' ],
							value: identifiantPhoto,
							onSelect: function ( photo ) {
								var maj = { photo_id: photo && photo.id ? photo.id : 0 };

								// Type once : la description de la médiathèque est reprise si le
								// champ est encore vide, et jamais si elle a déjà été ajustée.
								if ( '' === attributs.photo_description && photo && photo.alt ) {
									maj.photo_description = photo.alt;
								}

								majAttributs( maj );
							},
							render: function ( ouverture ) {
								return el(
									Button,
									{ variant: 'secondary', onClick: ouverture.open },
									photoUtilisable ? 'Remplacer la photo' : 'Choisir une photo'
								);
							}
						} )
					),
					false === photoUtilisable
						? null
						: el(
							Button,
							{
								variant: 'secondary',
								onClick: function () {
									majAttributs( { photo_id: 0 } );
								}
							},
							'Retirer la photo'
						),
					reglagesPhoto
				)
			);

			var corps = 'dessous' === attributs.position_photo
				? [ titre, prose, figure ]
				: [ titre, figure, prose ];

			return el(
				Fragment,
				null,
				reglages,
				el( 'div', proprietesRacine, corps[ 0 ], corps[ 1 ], corps[ 2 ], etatVide )
			);
		},

		/*
		 * Rien d'autre que l'InnerBlocks : « $content » côté serveur doit contenir exactement la
		 * prose, sans enveloppe. Une enveloppe ici produirait deux conteneurs de prose imbriqués.
		 */
		save: function () {
			return el( InnerBlocks.Content );
		}
	} );
}( window.wp ) );
