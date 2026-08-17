/**
 * Composant « Galerie photos » côté éditeur : choix des photos, ordre, aperçu rendu par le serveur.
 * Aucune dépendance, aucune étape de préparation, aucun appel vers l'extérieur.
 */
( function ( wp ) {
	'use strict';

	if ( ! wp || ! wp.blocks || ! wp.element || ! wp.blockEditor || ! wp.components ) {
		return;
	}

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var useRef = wp.element.useRef;
	var useEffect = wp.element.useEffect;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var MediaUpload = wp.blockEditor.MediaUpload;
	var MediaUploadCheck = wp.blockEditor.MediaUploadCheck;
	var Bouton = wp.components.Button;
	var PanelBody = wp.components.PanelBody;

	/*
	 * Le paquet du cœur s'expose tantôt directement, tantôt sous « default » selon la version : les
	 * deux formes sont lues, aucune n'est supposée.
	 */
	var Apercu = wp.serverSideRender ? ( wp.serverSideRender.default || wp.serverSideRender ) : null;

	var PHRASE_AUCUN_CHOIX = "Ce bloc n'affiche rien tant qu'aucune photo n'est choisie.";
	var PHRASE_INDISPONIBLE = "Ce bloc n'affiche rien tant qu'aucune photo choisie n'est disponible.";

	/*
	 * Le titre du bloc, recopié de block.json et en CASSE NATURELLE. Les capitales sont posées par
	 * « text-transform » sur .mtb-etat-vide__nom (themes/mtb/assets/css/editor.css), jamais tapées
	 * ici : un lecteur d'écran épellerait lettre à lettre des capitales littérales, alors qu'il lit
	 * normalement un texte que le rendu met en capitales. L'apparence est la même dans les deux cas,
	 * et c'est ce qui rend la faute invisible à l'œil — seul le DOM la montre.
	 */
	var NOM_AFFICHE = 'Galerie photos';

	/**
	 * Cadre d'état vide, jamais rendu par le serveur : il n'existe que dans l'éditeur.
	 *
	 * Deux crochets sur la racine, volontairement : le partagé, pour qu'une règle unique absorbe un
	 * jour les cadres des dix composants, et le local, pour que cette centralisation n'ait pas à
	 * toucher ce fichier.
	 */
	function cadreVide( phrase, bouton ) {
		return el(
			'div',
			{ className: 'mtb-etat-vide mtb-galerie-photos__vide' },
			el( 'p', { className: 'mtb-etat-vide__nom' }, NOM_AFFICHE ),
			el( 'p', { className: 'mtb-etat-vide__phrase' }, phrase ),
			bouton
		);
	}

	/*
	 * Déclaré une seule fois, hors du composant : passé en composant à l'aperçu, il doit garder la
	 * même identité d'un rendu à l'autre, sinon le cadre serait démonté et remonté sans cesse.
	 */
	function CadreIndisponible() {
		return cadreVide( PHRASE_INDISPONIBLE, null );
	}

	/**
	 * Redonne le focus après un ajout, un retrait ou un déplacement.
	 *
	 * Le focus va au bouton homologue de la photo déplacée. Si ce bouton vient d'être désactivé par
	 * le déplacement même — une photo montée en première place ne peut plus monter — il retombe sur
	 * « Retirer », toujours actif. Si la photo a disparu de la liste, il retombe sur « Ajouter des
	 * photos ». Sans cela, le focus retomberait dans le vide et l'éleveuse perdrait sa place.
	 */
	function rendreLeFocus( zone, ajouter, position, role ) {
		var lignes = zone ? zone.querySelectorAll( '[data-mtb-rang]' ) : null;
		var cible;
		var repli;

		if ( lignes && position >= 0 && position < lignes.length ) {
			cible = lignes[ position ].querySelector( '[data-mtb-bouton="' + role + '"]' );

			if ( cible && ! cible.disabled ) {
				cible.focus();

				return;
			}

			repli = lignes[ position ].querySelector( '[data-mtb-bouton="retirer"]' );

			if ( repli ) {
				repli.focus();

				return;
			}
		}

		if ( ajouter && 'function' === typeof ajouter.focus ) {
			ajouter.focus();
		}
	}

	function Edition( proprietes ) {
		var photos = Array.isArray( proprietes.attributes.photos ) ? proprietes.attributes.photos : [];
		var attributsDeBloc = useBlockProps();
		var zone = useRef( null );
		var ajouter = useRef( null );
		var voeu = useRef( null );
		var contenu;

		useEffect( function () {
			var demande = voeu.current;

			voeu.current = null;

			if ( ! demande ) {
				return;
			}

			rendreLeFocus( zone.current, ajouter.current, demande.position, demande.role );
		} );

		function enregistrer( liste, position, role ) {
			voeu.current = { position: position, role: role };
			proprietes.setAttributes( { photos: liste } );
		}

		/*
		 * « Ajouter des photos » ajoute, il ne remplace pas : la fenêtre s'ouvre sans sélection, ne
		 * renvoie que les photos qui viennent d'être choisies, et celles déjà présentes sont sautées
		 * plutôt que remises à la fin.
		 */
		function ajouterDesPhotos( choisies ) {
			var nouvelles = Array.isArray( choisies ) ? choisies : [ choisies ];
			var liste = photos.slice();
			var index;
			var identifiant;

			for ( index = 0; index < nouvelles.length; index++ ) {
				identifiant = nouvelles[ index ] ? parseInt( nouvelles[ index ].id, 10 ) : 0;

				if ( ! identifiant || -1 !== liste.indexOf( identifiant ) ) {
					continue;
				}

				liste.push( identifiant );
			}

			enregistrer( liste, liste.length - 1, 'retirer' );
		}

		function retirer( position ) {
			var liste = photos.slice();

			liste.splice( position, 1 );
			enregistrer( liste, position, 'retirer' );
		}

		function deplacee( depart, arrivee ) {
			var liste = photos.slice();
			var photo = liste[ depart ];

			liste.splice( depart, 1 );
			liste.splice( arrivee, 0, photo );

			return liste;
		}

		function monter( position ) {
			if ( position <= 0 ) {
				return;
			}

			enregistrer( deplacee( position, position - 1 ), position - 1, 'monter' );
		}

		function descendre( position ) {
			if ( position >= photos.length - 1 ) {
				return;
			}

			enregistrer( deplacee( position, position + 1 ), position + 1, 'descendre' );
		}

		function fenetreDesPhotos( rendreLeBouton ) {
			return el(
				MediaUploadCheck,
				null,
				el( MediaUpload, {
					allowedTypes: [ 'image' ],
					multiple: true,
					title: 'Photos de la galerie',
					onSelect: ajouterDesPhotos,
					render: rendreLeBouton
				} )
			);
		}

		/*
		 * Chaque bouton porte le rang de sa photo : trois boutons par photo tous nommés « Retirer »
		 * seraient indistinguables à la tabulation comme au lecteur d'écran.
		 *
		 * Aux bornes de la liste, le bouton sans effet est désactivé plutôt que retiré : le nombre de
		 * boutons ne varie pas d'une ligne à l'autre, le parcours au clavier reste régulier, et le
		 * lecteur d'écran annonce le contrôle comme indisponible.
		 */
		function ligne( identifiant, index ) {
			var rang = index + 1;

			return el(
				'li',
				{ key: String( identifiant ) + '-' + String( index ), 'data-mtb-rang': String( rang ) },
				el(
					Bouton,
					{
						variant: 'link',
						'data-mtb-bouton': 'retirer',
						onClick: function () {
							retirer( index );
						}
					},
					'Retirer la photo ' + rang
				),
				el(
					Bouton,
					{
						variant: 'link',
						'data-mtb-bouton': 'monter',
						disabled: 0 === index,
						onClick: function () {
							monter( index );
						}
					},
					'Monter la photo ' + rang
				),
				el(
					Bouton,
					{
						variant: 'link',
						'data-mtb-bouton': 'descendre',
						disabled: index === photos.length - 1,
						onClick: function () {
							descendre( index );
						}
					},
					'Descendre la photo ' + rang
				)
			);
		}

		if ( 0 === photos.length ) {
			contenu = cadreVide(
				PHRASE_AUCUN_CHOIX,
				fenetreDesPhotos( function ( parametres ) {
					return el( 'button', { type: 'button', onClick: parametres.open }, 'Ajouter des photos' );
				} )
			);
		} else if ( Apercu ) {
			/*
			 * L'aperçu est le rendu du serveur, pas une grille redessinée en JavaScript : filtre de
			 * validité, sous-taille, srcset, ordre et libellé de rang n'existent qu'une fois, en PHP.
			 * Ce qu'on achète : l'aperçu de l'éleveuse est la page.
			 */
			contenu = el( Apercu, {
				block: 'mtb/galerie-photos',
				attributes: { photos: photos },
				EmptyResponsePlaceholder: CadreIndisponible
			} );
		} else {
			// Sans le paquet d'aperçu du cœur, on ne montre rien plutôt qu'un état vide qui serait faux.
			contenu = null;
		}

		return el(
			Fragment,
			null,
			el(
				InspectorControls,
				null,
				el(
					PanelBody,
					{ title: 'Photos de la galerie', initialOpen: true },
					el( 'ul', { ref: zone }, photos.map( ligne ) ),
					fenetreDesPhotos( function ( parametres ) {
						return el(
							Bouton,
							{ variant: 'secondary', ref: ajouter, onClick: parametres.open },
							'Ajouter des photos'
						);
					} )
				)
			),
			el( 'div', attributsDeBloc, contenu )
		);
	}

	/*
	 * Titre, catégorie, icône, description, attributs et « supports » viennent de block.json, que le
	 * serveur transmet à l'éditeur : ils ne sont pas recopiés ici. Le rendu public appartient au
	 * serveur, donc rien n'est enregistré dans le contenu de la page.
	 */
	wp.blocks.registerBlockType( 'mtb/galerie-photos', {
		edit: Edition,
		save: function () {
			return null;
		}
	} );
} )( window.wp );
