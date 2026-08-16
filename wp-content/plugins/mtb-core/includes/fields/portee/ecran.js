/**
 * Amélioration progressive de l'écran de saisie d'une portée.
 *
 * Aucune étape de construction : ce fichier est servi tel quel. Aucune variable globale et aucune
 * chaîne injectée depuis PHP : tout le texte affiché vient du document.
 *
 * Sans ce script, l'écran reste utilisable : les deux chemins de saisie d'un parent sont visibles,
 * trois rangées de chiots vierges sont déjà présentes, et les cases « Retirer » fonctionnent.
 */
( function () {
	'use strict';

	function chacun( collection, action ) {
		Array.prototype.forEach.call( collection, action );
	}

	function devoiler( element ) {
		if ( element ) {
			element.hidden = false;
		}
	}

	/**
	 * Masque la branche de saisie que l'éleveuse n'a pas choisie, pour le père et pour la mère.
	 */
	function brancherParents() {
		chacun( document.querySelectorAll( '.mtb-portee-parent[data-parent]' ), function ( groupe ) {
			var radios = groupe.querySelectorAll( 'input[type="radio"][data-branche]' );
			var branches = groupe.querySelectorAll( '.mtb-portee-branche[data-branche]' );

			function appliquer() {
				var choisie = null;

				chacun( radios, function ( radio ) {
					if ( radio.checked ) {
						choisie = radio.getAttribute( 'data-branche' );
					}
				} );

				// Tant que rien n'est choisi, les deux chemins restent visibles et à égalité.
				chacun( branches, function ( branche ) {
					branche.hidden = ( null !== choisie && branche.getAttribute( 'data-branche' ) !== choisie );
				} );
			}

			chacun( radios, function ( radio ) {
				radio.addEventListener( 'change', appliquer );
			} );

			appliquer();
		} );
	}

	/**
	 * Ajoute une rangée de chiot en clonant le gabarit du document.
	 */
	function brancherChiots() {
		var corps = document.getElementById( 'mtb-portee-chiots-rangees' );
		var modele = document.getElementById( 'mtb-portee-chiot-modele' );
		var bouton = document.getElementById( 'mtb-portee-chiot-ajouter' );
		var annonce = document.getElementById( 'mtb-portee-chiots-annonce' );

		if ( ! corps || ! modele || ! bouton ) {
			return;
		}

		devoiler( bouton.parentNode );

		bouton.addEventListener( 'click', function () {
			var index = corps.children.length;
			var balisage = modele.innerHTML
				.split( '__INDEX__' ).join( String( index ) )
				.split( '__NUMERO__' ).join( String( index + 1 ) );

			var tampon = document.createElement( 'tbody' );
			tampon.innerHTML = balisage;

			var rangee = tampon.firstElementChild;

			if ( ! rangee ) {
				return;
			}

			corps.appendChild( rangee );

			var premier = rangee.querySelector( 'input, select' );

			if ( premier ) {
				premier.focus();
			}

			if ( annonce ) {
				annonce.textContent = annonce.getAttribute( 'data-annonce' ) || '';
			}
		} );
	}

	/**
	 * Renumérote la galerie après un ajout, un déplacement ou un retrait.
	 *
	 * La mention « Photo n » et les libellés des trois actions sont recalculés depuis le rang réel
	 * de chaque élément, jamais renumérotés à la main : après un déplacement, aucun libellé ne peut
	 * désigner une autre photo que la sienne. Les phrases viennent du document, par le gabarit posé
	 * en « data-libelle-rang » ; le script n'y met que le nombre.
	 *
	 * La première photo ne peut pas monter, la dernière ne peut pas descendre : leurs boutons sont
	 * désactivés ici, et nulle part ailleurs. Le serveur n'en désactive aucun — il ignore ce que
	 * deviendra l'ordre après manipulation. Les boutons sont désactivés plutôt que retirés : leur
	 * nombre reste le même d'une ligne à l'autre, la mise en page ne saute pas, le parcours clavier
	 * reste régulier, et un lecteur d'écran annonce le bouton comme indisponible — ce qui est exact.
	 *
	 * Le total est relu à chaque appel : la fonction tourne aussi après un ajout, et une valeur
	 * mémorisée désignerait la mauvaise dernière photo.
	 */
	function renumeroterPhotos( liste ) {
		var rang = 0;
		var total = liste.children.length;

		chacun( liste.children, function ( element ) {
			rang = rang + 1;

			chacun( element.querySelectorAll( '[data-libelle-rang]' ), function ( action ) {
				var gabarit = action.getAttribute( 'data-libelle-rang' );

				if ( gabarit ) {
					action.textContent = gabarit.split( '__NUMERO__' ).join( String( rang ) );
				}
			} );

			var monter = element.querySelector( '[data-deplacer="monter"]' );
			var descendre = element.querySelector( '[data-deplacer="descendre"]' );

			// Photo unique : rang et total valent 1, les deux se désactivent sans traitement à part.
			if ( monter ) {
				monter.disabled = ( 1 === rang );
			}

			if ( descendre ) {
				descendre.disabled = ( rang === total );
			}
		} );
	}

	function ajouterPhoto( liste, modele, donnees ) {
		var identifiant = parseInt( donnees.id, 10 );

		if ( ! identifiant ) {
			return;
		}

		if ( liste.querySelector( 'input[type="hidden"][value="' + identifiant + '"]' ) ) {
			return;
		}

		var tampon = document.createElement( 'ul' );
		tampon.innerHTML = modele.innerHTML.split( '__ID__' ).join( String( identifiant ) );

		var element = tampon.firstElementChild;

		if ( ! element ) {
			return;
		}

		var image = element.querySelector( 'img' );

		if ( image ) {
			var source = donnees.url;

			if ( donnees.sizes && donnees.sizes.thumbnail ) {
				source = donnees.sizes.thumbnail.url;
			}

			image.setAttribute( 'src', source || '' );
			image.setAttribute( 'alt', donnees.alt || '' );
		}

		liste.appendChild( element );

		chacun( element.querySelectorAll( '.mtb-portee-outil' ), devoiler );
		renumeroterPhotos( liste );
	}

	/**
	 * Ordonne la galerie et ouvre la fenêtre de choix des photos du cœur.
	 */
	function brancherGalerie() {
		var liste = document.getElementById( 'mtb-portee-galerie-liste' );
		var modele = document.getElementById( 'mtb-portee-galerie-modele' );
		var bouton = document.getElementById( 'mtb-portee-galerie-ajouter' );

		if ( ! liste ) {
			return;
		}

		chacun( liste.querySelectorAll( '.mtb-portee-outil' ), devoiler );

		// Les extrémités sont désactivées dès l'affichage, avant toute manipulation.
		renumeroterPhotos( liste );

		liste.addEventListener( 'click', function ( evenement ) {
			var cible = evenement.target;

			if ( ! cible || 'function' !== typeof cible.closest ) {
				return;
			}

			var declencheur = cible.closest( '[data-deplacer]' );

			if ( ! declencheur ) {
				return;
			}

			/*
			 * L'écoute est déléguée à la liste : un clic simulé sur un bouton désactivé y remonte
			 * quand même. On s'arrête donc ici plutôt que de renuméroter et de déplacer le focus
			 * pour un déplacement qui n'a pas eu lieu.
			 */
			if ( declencheur.disabled ) {
				return;
			}

			var element = declencheur.closest( 'li' );

			if ( ! element ) {
				return;
			}

			if ( 'monter' === declencheur.getAttribute( 'data-deplacer' ) ) {
				if ( element.previousElementSibling ) {
					liste.insertBefore( element, element.previousElementSibling );
				}
			} else if ( element.nextElementSibling ) {
				liste.insertBefore( element.nextElementSibling, element );
			}

			renumeroterPhotos( liste );

			/*
			 * La photo arrivée en tête ou en fin voit son bouton se désactiver : lui rendre le focus
			 * le ferait tomber dans le vide et Fabienne perdrait sa place. Repli sur la case
			 * « Retirer » de la même ligne, seul contrôle toujours actif.
			 */
			var aFocaliser = declencheur.disabled ? element.querySelector( 'input[type="checkbox"]' ) : declencheur;

			if ( aFocaliser ) {
				aFocaliser.focus();
			}
		} );

		if ( ! bouton || ! modele || ! window.wp || ! window.wp.media ) {
			return;
		}

		devoiler( bouton.parentNode );

		bouton.addEventListener( 'click', function () {
			var intitule = bouton.textContent;

			// Une fenêtre neuve à chaque ouverture : une fenêtre réutilisée conserve sa sélection.
			var cadre = window.wp.media( {
				title: intitule,
				button: { text: intitule },
				library: { type: 'image' },
				multiple: 'add'
			} );

			cadre.on( 'select', function () {
				cadre.state().get( 'selection' ).each( function ( piece ) {
					ajouterPhoto( liste, modele, piece.toJSON() );
				} );
			} );

			cadre.open();
		} );
	}

	function initialiser() {
		brancherParents();
		brancherChiots();
		brancherGalerie();
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', initialiser );
	} else {
		initialiser();
	}
}() );
