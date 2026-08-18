/**
 * Composant « Bandeau d'alerte » — écran d'édition.
 *
 * ES5, aucune syntaxe JSX, aucune étape de construction : le fichier est servi tel quel, lisible et
 * modifiable sans outillage. Il n'est atteignable que par la poignée « mtb-bandeau-alerte-editeur »,
 * référencée uniquement par « editorScript » : le cœur ne la met en file que dans l'éditeur, et le
 * visiteur ne reçoit pas un octet de ce fichier.
 */
( function () {
	'use strict';

	/*
	 * Les trois dépendances déclarées à l'enregistrement du script, et rien de plus. Une dépendance
	 * manquante laisse le composant non enregistré : le contenu déjà saisi reste intact et le site
	 * continue de le rendre, puisque le rendu est celui du serveur.
	 */
	if ( ! window.wp || ! wp.blocks || ! wp.element || ! wp.blockEditor ) {
		return;
	}

	var el = wp.element.createElement;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var RichText = wp.blockEditor.RichText;

	var NOM = 'mtb/bandeau-alerte';

	var NOM_AFFICHE = 'Bandeau d\'alerte';
	var PHRASE_VIDE = 'Ce bloc n\'affiche rien tant qu\'aucun texte n\'est renseigné.';
	var INVITE = 'Le message à afficher…';

	/**
	 * Dit si le message ne porte aucun texte affichable.
	 *
	 * Miroir volontairement plus simple que celui du serveur : il ne décode pas toutes les entités
	 * HTML. Conséquence maximale, un encadré d'éditeur légèrement en retard sur la vérité pendant une
	 * frappe. LE SERVEUR EST L'AUTORITÉ, L'ÉDITEUR EST UN CONSEIL : déporter la décision côté serveur
	 * coûterait un aller-retour réseau à chaque caractère tapé.
	 *
	 * Les trois points de code sont écrits en séquence d'échappement plutôt qu'en caractères
	 * littéraux, invisibles à la relecture : U+00A0 espace insécable, U+202F espace fine insécable
	 * posée par le clavier français devant un « ! », U+200B espace sans chasse venue d'un collage.
	 *
	 * @param {*} valeur Valeur enregistrée du message.
	 * @return {boolean} Vrai s'il n'y a rien à afficher.
	 */
	function estVide( valeur ) {
		if ( 'string' !== typeof valeur ) {
			return true;
		}

		return '' === valeur
			.replace( /<[^>]*>/g, '' )
			.replace( /&nbsp;/g, ' ' )
			.replace( /[\s\u00A0\u202F\u200B]+/g, '' );
	}

	/**
	 * Encadré d'état vide, jamais rendu par le serveur : il n'existe que dans l'éditeur.
	 *
	 * Balisage écrit à la main plutôt qu'un composant du cœur : l'apparence commune aux composants du
	 * catalogue vit dans la feuille « editor.css » du thème, qui habille les trois crochets ci-dessous.
	 * Aucune classe modificatrice, aucune classe locale — une classe qu'aucune règle ne cible est un
	 * nom qu'un composant sœur orthographiera mal un jour.
	 *
	 * Le nom du composant est porté par un « span » et non par un « p » : « editor.css » lui pose
	 * « display: block » et ne remet pas à zéro la marge basse qu'un « p » hérite de la feuille de
	 * base. Il est écrit en CASSE NATURELLE : les capitales sont posées par « text-transform », et un
	 * lecteur d'écran épellerait lettre à lettre des capitales littérales.
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
			/*
			 * Appelé inconditionnellement, avant toute branche : c'est un crochet d'état de React.
			 * Aucune classe du thème n'est posée ici — l'habillage appartient à l'encart intérieur,
			 * exactement comme côté public.
			 */
			var racine = useBlockProps();

			var message = 'string' === typeof proprietes.attributes.message
				? proprietes.attributes.message
				: '';

			/*
			 * LE CHAMP RESTE MONTÉ EN PERMANENCE, À LA MÊME PLACE DANS L'ARBRE, et l'encart reste
			 * rendu même vide : le champ y affiche son texte d'invite, donc l'éleveuse voit la forme
			 * qu'elle remplit et peut cliquer dedans. Toute conception qui remplacerait le champ par
			 * l'encadré d'état vide — branche if/else, bascule sur la sélection — ferait remonter le
			 * champ par React et LE CURSEUR SAUTERAIT HORS DU CHAMP au premier caractère tapé.
			 *
			 * L'encadré d'état vide est un FRÈRE POSTÉRIEUR de l'encart, jamais un enfant : il porte
			 * lui-même un fond creusé et un contour tireté, qui imbriqués se liraient comme la bordure
			 * de l'encart. Ajouter ou retirer un nœud APRÈS un nœud stable ne remonte pas ce dernier.
			 *
			 * Ni « onSplit », ni « onReplace », ni « identifier » : ils demanderaient au cœur de
			 * scinder le bloc à la touche Entrée, c'est-à-dire d'en créer un second. Sans eux, Entrée
			 * insère un saut de ligne — comportement voulu, et c'est pourquoi « br » est admise en
			 * sortie.
			 */
			return el(
				'div',
				racine,
				el(
					'div',
					{ className: 'mtb-bandeau-alerte' },
					el( RichText, {
						tagName: 'p',
						className: 'mtb-bandeau-alerte__message',
						value: message,
						/*
						 * Le lien seul, et rien d'autre : pointer vers une page du site sans recopier
						 * une mise en forme. Ni gras, ni italique, ni couleur, ni taille — le
						 * composant ne laisse prendre aucune décision visuelle. Ce réglage est une
						 * commodité d'interface, jamais une barrière : la barrière est en PHP, en
						 * sortie.
						 */
						allowedFormats: [ 'core/link' ],
						placeholder: INVITE,
						onChange: function ( valeur ) {
							proprietes.setAttributes( { message: valeur } );
						}
					} )
				),
				estVide( message ) ? el( EtatVide ) : null
			);
		},

		/*
		 * Une FONCTION qui rend null, jamais « save: null » : le cœur exige une fonction et refuserait
		 * l'enregistrement du bloc. Rien n'est écrit dans la page — le balisage a une seule vérité,
		 * celle du serveur, et aucune dérive de validation de bloc ne peut se produire.
		 */
		save: function () {
			return null;
		}
	} );
}() );
