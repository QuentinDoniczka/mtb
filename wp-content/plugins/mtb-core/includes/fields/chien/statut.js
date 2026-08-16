/**
 * Accorde au sexe les libellés de statut affichés sur l'écran de saisie d'une fiche Chien.
 *
 * La valeur enregistrée ne change jamais : seul le texte affiché est échangé, exactement comme le
 * site le fera. Sans ce script, les formes masculines canoniques restent affichées et la ligne
 * d'aide sous le champ explique l'accord : rien ne casse et rien ne ment.
 */
( function () {
	'use strict';

	var statuts = document.getElementById( 'mtb-champ-mtb-statut' );

	if ( ! statuts ) {
		return;
	}

	var libelles = statuts.querySelectorAll( '[data-mtb-masculin][data-mtb-feminin]' );
	var sexes = document.querySelectorAll( 'input[name="_mtb_sexe"]' );

	if ( 0 === libelles.length || 0 === sexes.length ) {
		return;
	}

	function accorder() {
		var choisi = '';
		var index;

		for ( index = 0; index < sexes.length; index++ ) {
			if ( sexes[ index ].checked ) {
				choisi = sexes[ index ].value;
			}
		}

		for ( index = 0; index < libelles.length; index++ ) {
			libelles[ index ].textContent = 'femelle' === choisi
				? libelles[ index ].getAttribute( 'data-mtb-feminin' )
				: libelles[ index ].getAttribute( 'data-mtb-masculin' );
		}
	}

	for ( var depart = 0; depart < sexes.length; depart++ ) {
		sexes[ depart ].addEventListener( 'change', accorder );
	}

	accorder();
} )();
