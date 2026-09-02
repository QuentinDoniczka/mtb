/**
 * Galerie photos de la fiche Chien : ajout, retrait et ordre, avec la fenêtre des photos du cœur
 * de WordPress. Aucune dépendance, aucune étape de préparation, aucun appel vers l'extérieur.
 */
( function () {
	'use strict';

	var zone = document.getElementById( 'mtb-chien-galerie' );

	if ( ! zone || ! window.wp || ! window.wp.media ) {
		return;
	}

	var liste = zone.querySelector( '.mtb-galerie__liste' );
	var champ = document.getElementById( 'mtb-champ-mtb-galerie' );
	var ajouter = zone.querySelector( '.mtb-galerie__ajouter' );

	if ( ! liste || ! champ || ! ajouter ) {
		return;
	}

	var photos = [];
	var fenetre = null;

	function lireEtatInitial() {
		var elements = liste.querySelectorAll( '.mtb-galerie__photo' );
		var index;
		var image;

		for ( index = 0; index < elements.length; index++ ) {
			image = elements[ index ].querySelector( 'img' );

			photos.push( {
				id: parseInt( elements[ index ].getAttribute( 'data-mtb-photo' ), 10 ),
				url: image ? image.getAttribute( 'src' ) : '',
				alt: image ? image.getAttribute( 'alt' ) : ''
			} );
		}
	}

	function bouton( classe, texte, position ) {
		var element = document.createElement( 'button' );

		element.type = 'button';
		element.className = 'button ' + classe;
		element.textContent = texte;
		element.setAttribute( 'data-mtb-position', String( position ) );

		return element;
	}

	/*
	 * La liste entière est redessinée après chaque ajout, retrait ou déplacement : c'est ce qui
	 * garantit que le rang annoncé par chaque bouton reste juste, sans avoir à renuméroter quoi
	 * que ce soit à la main.
	 */
	function dessiner( positionFocus, classeFocus ) {
		var index;
		var rang;
		var element;
		var image;
		var monter;
		var descendre;

		while ( liste.firstChild ) {
			liste.removeChild( liste.firstChild );
		}

		for ( index = 0; index < photos.length; index++ ) {
			rang = index + 1;
			element = document.createElement( 'li' );
			element.className = 'mtb-galerie__photo';
			element.setAttribute( 'data-mtb-photo', String( photos[ index ].id ) );

			if ( photos[ index ].url ) {
				image = document.createElement( 'img' );
				image.src = photos[ index ].url;
				image.alt = photos[ index ].alt || '';
				element.appendChild( image );
			}

			monter = bouton( 'mtb-galerie__avant', 'Monter la photo ' + rang, index );
			descendre = bouton( 'mtb-galerie__apres', 'Descendre la photo ' + rang, index );

			/*
			 * Recalculé ici, à chaque redessin, et non posé une fois : sans cela l'état serait faux
			 * dès le premier ajout ou retrait. C'est la même règle que pour le rang — une seule
			 * source, « photos », et tout se déduit d'elle.
			 */
			monter.disabled = ( 0 === index );
			descendre.disabled = ( index === photos.length - 1 );

			element.appendChild( bouton( 'mtb-galerie__retirer', 'Retirer la photo ' + rang, index ) );
			element.appendChild( monter );
			element.appendChild( descendre );

			liste.appendChild( element );
		}

		var identifiants = [];

		for ( index = 0; index < photos.length; index++ ) {
			identifiants.push( photos[ index ].id );
		}

		champ.value = identifiants.join( ',' );

		rendreLeFocus( positionFocus, classeFocus );
	}

	function rendreLeFocus( position, classe ) {
		if ( 'number' !== typeof position || ! classe ) {
			return;
		}

		var elements = liste.querySelectorAll( '.mtb-galerie__photo' );

		if ( position < 0 || position >= elements.length ) {
			ajouter.focus();

			return;
		}

		var cible = elements[ position ].querySelector( '.' + classe );

		if ( cible && ! cible.disabled ) {
			cible.focus();

			return;
		}

		/*
		 * Le bouton visé vient d'être désactivé par le déplacement même : une photo montée en
		 * première place ne peut plus monter. Le focus reste alors sur la photo déplacée, au bouton
		 * « Retirer » qui, lui, est toujours actif — sans ce repli, le focus retomberait dans le
		 * vide et l'éleveuse perdrait sa place dans la page.
		 */
		var repli = elements[ position ].querySelector( '.mtb-galerie__retirer' );

		if ( repli ) {
			repli.focus();

			return;
		}

		ajouter.focus();
	}

	function deja( identifiant ) {
		var index;

		for ( index = 0; index < photos.length; index++ ) {
			if ( photos[ index ].id === identifiant ) {
				return true;
			}
		}

		return false;
	}

	function ouvrirLaFenetre() {
		if ( ! fenetre ) {
			fenetre = window.wp.media( {
				title: 'Photos de la galerie',
				button: { text: 'Ajouter à la galerie' },
				library: { type: 'image' },
				multiple: 'add'
			} );

			fenetre.on( 'select', function () {
				var choisies = fenetre.state().get( 'selection' ).toJSON();
				var index;
				var photo;

				for ( index = 0; index < choisies.length; index++ ) {
					photo = choisies[ index ];

					if ( deja( photo.id ) ) {
						continue;
					}

					photos.push( {
						id: photo.id,
						url: photo.sizes && photo.sizes.thumbnail ? photo.sizes.thumbnail.url : photo.url,
						alt: photo.alt || ''
					} );
				}

				dessiner( photos.length - 1, 'mtb-galerie__retirer' );
			} );
		}

		fenetre.open();
	}

	function deplacer( depart, arrivee ) {
		var photo = photos[ depart ];

		photos.splice( depart, 1 );
		photos.splice( arrivee, 0, photo );
	}

	liste.addEventListener( 'click', function ( evenement ) {
		var cible = evenement.target;

		if ( ! cible || ! cible.getAttribute || ! cible.getAttribute( 'data-mtb-position' ) ) {
			return;
		}

		var position = parseInt( cible.getAttribute( 'data-mtb-position' ), 10 );

		evenement.preventDefault();

		if ( -1 !== cible.className.indexOf( 'mtb-galerie__retirer' ) ) {
			photos.splice( position, 1 );
			dessiner( position, 'mtb-galerie__retirer' );

			return;
		}

		/*
		 * Les bornes restent vérifiées ici bien que les boutons soient désactivés : l'écoute est
		 * posée sur la liste, et un clic simulé sur un bouton désactivé y remonterait quand même.
		 * Sans ces gardes, un déplacement vers -1 insérerait la photo en fin de galerie — un
		 * réordonnancement silencieux, jamais demandé. La ceinture ne coûte rien.
		 */
		if ( -1 !== cible.className.indexOf( 'mtb-galerie__avant' ) && position > 0 ) {
			deplacer( position, position - 1 );
			dessiner( position - 1, 'mtb-galerie__avant' );

			return;
		}

		if ( -1 !== cible.className.indexOf( 'mtb-galerie__apres' ) && position < photos.length - 1 ) {
			deplacer( position, position + 1 );
			dessiner( position + 1, 'mtb-galerie__apres' );
		}
	} );

	ajouter.addEventListener( 'click', function ( evenement ) {
		evenement.preventDefault();
		ouvrirLaFenetre();
	} );

	lireEtatInitial();
	dessiner();
} )();
