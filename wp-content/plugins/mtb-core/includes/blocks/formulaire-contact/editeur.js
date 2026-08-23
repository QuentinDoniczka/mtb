/**
 * Composant « Formulaire de contact » — écran d'édition.
 *
 * ES5, aucune syntaxe JSX, aucune étape de construction : le fichier est servi tel quel, lisible et
 * modifiable sans outillage. Il n'est atteignable que par la poignée
 * « mtb-formulaire-contact-editeur », référencée uniquement par « editorScript » : le cœur ne la met
 * en file que dans l'éditeur, et LE VISITEUR N'EN REÇOIT PAS UN OCTET.
 *
 * L'APERÇU EST UNE REPRÉSENTATION STATIQUE, PAS UN « ServerSideRender ». Quatre motifs, dans l'ordre
 * de poids :
 *
 * 1. LA MENTION SE TAPE EN PLACE. Un rendu serveur donne une image morte : la mention y serait en
 *    lecture seule et l'unique réglage du composant deviendrait inatteignable. Ce point tranche à
 *    lui seul.
 * 2. Un aperçu serveur injecterait un « form method="post" » VIVANT DANS wp-admin, où l'écran
 *    d'édition est lui-même un formulaire. Formulaires imbriqués : HTML invalide.
 * 3. Un jeton horodaté n'a aucun sens dans un aperçu : re-frappé à chaque rendu, périmé avant
 *    d'avoir servi.
 * 4. « ServerSideRender » passe par REST, où « get_queried_object() » est nul et « $_POST »
 *    appartient à la requête REST. Des chemins de code à garder pour zéro bénéfice.
 *
 * La représentation N'ÉMET AUCUN « form » et ses champs sont de vrais champs en lecture seule, SANS
 * ATTRIBUT « name » : rien ne peut partir d'ici. Elle porte les mêmes crochets et les mêmes éléments
 * que le rendu public — à la seule exception du « form », remplacé par un « div ».
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

	var NOM = 'mtb/formulaire-contact';
	var CROCHET = 'mtb-formulaire-contact';

	/*
	 * AUCUNE CHAÎNE FRANÇAISE DANS CE FICHIER — amendement §19.2 du contrat #22.
	 *
	 * Ce script en portait douze en dur, dont huit MOT POUR MOT identiques à des constantes de
	 * « messages.php ». Le §9 confie au serveur la totalité des mots affichés ; une retouche d'un
	 * libellé côté serveur aurait laissé cet aperçu afficher l'ancien, SANS ERREUR NI JOURNAL —
	 * l'éleveuse aurait lu dans son écran un mot que le site n'emploie plus (décisions 43 et 46).
	 *
	 * Ils arrivent maintenant par le script en ligne posé sur cette poignée, avec l'état de la
	 * destination. Le seul exemplaire qui subsiste ailleurs est le « title » de « block.json », que
	 * le cœur lit avant tout PHP du module : frontière JSON/PHP/JS imposée, dette T-#22-h.
	 */

	/**
	 * Charge posée par le serveur en script en ligne, sur cette même poignée.
	 *
	 * ELLE NE PORTE AUCUNE COORDONNÉE. Le §4.4, précisé par le §19.2, n'y admet que deux choses :
	 * l'ÉTAT de la destination en un mot, et des LIBELLÉS D'INTERFACE. Ni adresse, ni numéro — et
	 * aucun appel REST n'est ajouté pour les obtenir.
	 *
	 * @return {Object} La charge, ou un objet vide.
	 */
	function donnees() {
		var charge = window.mtbFormulaireContact;

		return charge && 'object' === typeof charge ? charge : {};
	}

	/**
	 * Un libellé d'interface, tel que « messages.php » l'écrit.
	 *
	 * AUCUN REPLI EN FRANÇAIS ICI, ET C'EST LE POINT DE L'AMENDEMENT : un texte de secours écrit
	 * dans ce fichier recréerait exactement la duplication que le §19.2 supprime, et divergerait au
	 * premier ajustement. Le cas ne peut d'ailleurs pas se produire — le cœur imprime la charge
	 * « before » sur la poignée qui sert ce fichier : si ces lignes s'exécutent, la charge est là.
	 *
	 * @param {string} cle Clé de « libelles_editeur() », côté serveur.
	 * @return {string} Le libellé, ou une chaîne vide si la charge manquait.
	 */
	function libelle( cle ) {
		var libelles = donnees().libelles;

		return libelles && 'string' === typeof libelles[ cle ] ? libelles[ cle ] : '';
	}

	/**
	 * État de la destination, posé par le serveur en script en ligne.
	 *
	 * SEUL L'ÉTAT TRANSITE — « presente », « invalide » ou « absente » : l'adresse elle-même ne sort
	 * jamais vers l'éditeur.
	 *
	 * VALEUR PÉRIMABLE, ET C'EST ASSUMÉ : elle est calculée au chargement de l'écran. Si l'éleveuse
	 * change le réglage dans un autre onglet, elle recharge l'écran d'édition pour voir la phrase
	 * changer. Le serveur reste l'autorité — l'éditeur n'est qu'un conseil.
	 *
	 * @return {string} L'un des trois états ; « presente » quand rien n'a été posé, pour ne pas
	 *                  afficher une alerte fausse si le script en ligne venait à manquer.
	 */
	function etatDestination() {
		var destination = donnees().destination;

		return 'string' === typeof destination ? destination : 'presente';
	}

	/**
	 * Dit si la mention ne porte aucun texte affichable.
	 *
	 * Miroir volontairement plus simple que celui du serveur : il ne décode pas toutes les entités
	 * HTML. Conséquence maximale, un encadré d'éditeur légèrement en retard sur la vérité pendant
	 * une frappe. LE SERVEUR EST L'AUTORITÉ, L'ÉDITEUR EST UN CONSEIL : déporter la décision côté
	 * serveur coûterait un aller-retour réseau à chaque caractère tapé.
	 *
	 * Les trois points de code sont écrits en séquence d'échappement plutôt qu'en caractères
	 * littéraux, invisibles à la relecture : U+00A0 espace insécable, U+202F espace fine insécable
	 * posée par le clavier français devant un « ! », U+200B espace sans chasse venue d'un collage.
	 *
	 * @param {*} valeur Valeur enregistrée de la mention.
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
	 * Choisit la phrase d'état vide, par ordre de priorité.
	 *
	 * La destination passe avant la mention : sans adresse d'arrivée, écrire la mention ne ferait
	 * pas apparaître le formulaire, et lui demander de la taper d'abord serait l'envoyer travailler
	 * pour rien.
	 *
	 * Elle rend une CLÉ et non une phrase : c'est l'état qui décide, jamais le texte. Un libellé
	 * absent donnerait alors un encadré sans phrase — moins faux qu'un encadré escamoté, qui
	 * laisserait croire que tout va bien.
	 *
	 * @param {string} destination État de la destination.
	 * @param {*} mention Valeur enregistrée de la mention.
	 * @return {string} Clé du libellé à afficher, ou chaîne vide quand tout va bien.
	 */
	function cleManquante( destination, mention ) {
		if ( 'absente' === destination ) {
			return 'etat_destination_absente';
		}

		if ( 'invalide' === destination ) {
			return 'etat_destination_invalide';
		}

		if ( estVide( mention ) ) {
			return 'etat_mention_vide';
		}

		return '';
	}

	/**
	 * Encadré d'état vide, jamais rendu par le serveur : il n'existe que dans l'éditeur.
	 *
	 * Balisage écrit à la main plutôt qu'un composant du cœur : l'apparence commune aux composants
	 * du catalogue vit dans la feuille « editor.css » du thème, qui habille les trois crochets
	 * ci-dessous. ZÉRO CSS D'ÉDITEUR NOUVEAU pour cette issue.
	 *
	 * Le nom du composant est porté par un « span » et non par un « p » : « editor.css » lui pose
	 * « display: block » et ne remet pas à zéro la marge basse qu'un « p » hérite de la feuille de
	 * base. Il est écrit en CASSE NATURELLE : les capitales sont posées par « text-transform », et
	 * un lecteur d'écran épellerait lettre à lettre des capitales littérales.
	 *
	 * @param {Object} proprietes Propriétés du composant, dont la phrase à afficher.
	 * @return {Object} Élément à afficher.
	 */
	function EtatVide( proprietes ) {
		return el(
			'div',
			{ className: 'mtb-etat-vide' },
			el( 'span', { className: 'mtb-etat-vide__nom' }, libelle( 'nom_formulaire' ) ),
			el( 'p', { className: 'mtb-etat-vide__phrase' }, proprietes.phrase )
		);
	}

	/**
	 * Étiquette d'un groupe, avec sa mention d'obligation.
	 *
	 * @param {string} pour Identifiant de la saisie décrite.
	 * @param {string} texte Libellé visible.
	 * @return {Object} Élément à afficher.
	 */
	function etiquette( pour, texte ) {
		return el(
			'label',
			{ className: CROCHET + '__etiquette', htmlFor: pour },
			texte + ' ',
			el( 'span', { className: CROCHET + '__obligatoire' }, libelle( 'mention_obligatoire' ) )
		);
	}

	wp.blocks.registerBlockType( NOM, {
		edit: function ( proprietes ) {
			/*
			 * Appelé inconditionnellement, avant toute branche : c'est un crochet d'état de React.
			 * Aucune classe du thème n'est posée ici — l'habillage appartient au conteneur
			 * intérieur, exactement comme côté public.
			 */
			var racine = useBlockProps();

			var mention = 'string' === typeof proprietes.attributes.mention
				? proprietes.attributes.mention
				: '';

			var manque = cleManquante( etatDestination(), mention );

			/*
			 * L'ENCADRÉ D'ÉTAT VIDE EST POSÉ AU-DESSUS DE LA REPRÉSENTATION, JAMAIS À SA PLACE.
			 * Écart déclaré à MASTER.md §9.1, et il a une raison précise : dans le cas « mention
			 * vide », un encadré qui remplacerait la représentation cacherait LE SEUL CHAMP QUI
			 * PERMET DE LE FAIRE DISPARAÎTRE — un état vide dont on ne peut pas sortir.
			 *
			 * Les deux positions d'enfants sont TOUJOURS occupées, l'une éventuellement par « null » :
			 * React réconcilie ses enfants par index, et un index qui reste stable ne remonte pas le
			 * champ de saisie. Toute conception qui ferait apparaître ou disparaître un nœud AVANT
			 * la mention la remonterait, et LE CURSEUR SAUTERAIT HORS DU CHAMP au premier caractère
			 * tapé.
			 */
			return el(
				'div',
				racine,
				'' !== manque ? el( EtatVide, { phrase: libelle( manque ) } ) : null,
				el(
					'div',
					{ className: CROCHET },
					el(
						'div',
						{ className: CROCHET + '__formulaire' },

						el(
							'div',
							{ className: CROCHET + '__champ' },
							etiquette( 'mtb-contact-nom', libelle( 'etiquette_nom' ) ),
							el( 'input', {
								className: CROCHET + '__saisie',
								type: 'text',
								id: 'mtb-contact-nom',
								defaultValue: '',
								readOnly: true
							} )
						),

						el(
							'div',
							{ className: CROCHET + '__champ' },
							etiquette( 'mtb-contact-courriel', libelle( 'etiquette_courriel' ) ),
							el(
								'span',
								{ className: CROCHET + '__aide', id: 'mtb-contact-courriel-aide' },
								libelle( 'aide_courriel' )
							),
							el( 'input', {
								className: CROCHET + '__saisie',
								type: 'email',
								id: 'mtb-contact-courriel',
								defaultValue: '',
								readOnly: true,
								'aria-describedby': 'mtb-contact-courriel-aide'
							} )
						),

						el(
							'div',
							{ className: CROCHET + '__champ' },
							etiquette( 'mtb-contact-message', libelle( 'etiquette_message' ) ),
							el( 'textarea', {
								className: CROCHET + '__saisie ' + CROCHET + '__zone',
								id: 'mtb-contact-message',
								rows: 8,
								defaultValue: '',
								readOnly: true
							} )
						),

						/*
						 * Le piège figure dans l'aperçu, masqué par la même règle que côté public :
						 * l'éleveuse doit pouvoir constater qu'il ne se voit pas, et le jour où la
						 * feuille manquerait, elle le verrait ici avant que ses visiteuses ne le
						 * voient.
						 */
						el(
							'div',
							{ className: CROCHET + '__piege', 'aria-hidden': 'true' },
							el( 'label', { htmlFor: 'mtb-contact-reference' }, libelle( 'etiquette_piege' ) ),
							el( 'input', {
								type: 'text',
								id: 'mtb-contact-reference',
								defaultValue: '',
								readOnly: true,
								tabIndex: -1
							} )
						),

						/*
						 * LE SEUL CHAMP ÉDITABLE DU COMPOSANT, et il reste monté en permanence, à la
						 * même place dans l'arbre.
						 *
						 * Ni « onSplit », ni « onReplace », ni « identifier » : ils demanderaient au
						 * cœur de scinder le bloc à la touche Entrée, c'est-à-dire d'en créer un
						 * second — que « supports.multiple: false » refuse. Sans eux, Entrée insère
						 * un saut de ligne, et c'est pourquoi « br » est admise en sortie.
						 *
						 * Le lien seul est autorisé, et rien d'autre : ni gras, ni italique, ni
						 * couleur, ni taille. Le composant ne laisse prendre aucune décision
						 * visuelle. Ce réglage est une commodité d'interface, jamais une barrière :
						 * la barrière est en PHP, en sortie, par « wp_kses() ».
						 */
						el( RichText, {
							tagName: 'p',
							className: CROCHET + '__mention',
							value: mention,
							allowedFormats: [ 'core/link' ],
							placeholder: libelle( 'invite_mention' ),
							onChange: function ( valeur ) {
								proprietes.setAttributes( { mention: valeur } );
							}
						} ),

						/*
						 * « type="button" » ET NON « submit » : l'écran d'édition est lui-même un
						 * formulaire, et un bouton d'envoi y soumettrait l'article en cours. C'est
						 * la seule divergence d'attribut de l'aperçu, après le « div » qui remplace
						 * le « form ».
						 */
						el(
							'div',
							{ className: CROCHET + '__actions' },
							el( 'button', { type: 'button' }, libelle( 'libelle_envoi' ) )
						)
					)
				)
			);
		},

		/*
		 * Une FONCTION qui rend null, jamais « save: null » : le cœur exige une fonction et
		 * refuserait l'enregistrement du bloc. Rien n'est écrit dans la page — le balisage a une
		 * seule vérité, celle du serveur, et aucune dérive de validation de bloc ne peut se
		 * produire.
		 */
		save: function () {
			return null;
		}
	} );
}() );
