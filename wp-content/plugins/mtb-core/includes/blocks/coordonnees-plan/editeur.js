/**
 * Composant « Coordonnées et plan d'accès » — écran d'édition.
 *
 * ES5, aucune syntaxe JSX, aucune étape de construction : le fichier est servi tel quel, lisible et
 * modifiable sans outillage. Le rendu affiché dans la toile est celui du serveur, exactement celui
 * que verra le visiteur : le balisage n'a qu'une seule vérité, et aucune dérive de validation de
 * bloc ne peut se produire puisque rien n'est enregistré dans la page.
 */
( function () {
	'use strict';

	/*
	 * La liste couvre les six dépendances déclarées à l'enregistrement du script,
	 * « wp-server-side-render » comprise : l'aperçu de la toile EST le rendu du serveur, et sans elle
	 * « edit » lèverait une erreur qui vide l'écran d'édition. Une dépendance manquante laisse plutôt
	 * le composant non enregistré : le contenu déjà saisi reste intact et le site continue de le
	 * rendre.
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
	var useSelect = wp.data.useSelect;
	var RenduServeur = wp.serverSideRender;

	var NOM = 'mtb/coordonnees-plan';

	var NOM_AFFICHE = 'Coordonnées et plan d\'accès';
	var PHRASE_VIDE = 'Ce bloc n\'affiche rien tant qu\'aucune coordonnée n\'est renseignée.';

	/**
	 * Rend un texte en chaîne sûre, quelle que soit la valeur reçue.
	 *
	 * @param {*} valeur Valeur à normaliser.
	 * @return {string} Chaîne sans espaces aux extrémités, jamais réutilisée en sortie.
	 */
	function texte( valeur ) {
		return 'string' === typeof valeur ? valeur.replace( /^\s+|\s+$/g, '' ) : '';
	}

	/**
	 * État vide, servi partout où le composant n'affiche rien : avant l'appel au serveur comme
	 * lorsque le serveur répond une chaîne vide. Les deux chemins rendent le même balisage.
	 *
	 * Balisage écrit à la main plutôt qu'un composant du cœur : l'apparence commune aux composants du
	 * catalogue vit dans la feuille « editor.css » du thème, qui habille les trois crochets
	 * ci-dessous. Exactement deux éléments enfants, jamais un troisième, et AUCUNE classe
	 * modificatrice — un crochet propre à un composant ferait de cette feuille commune une
	 * négociation bloc par bloc.
	 *
	 * Le nom est porté par un « span » et non par un « p » : « editor.css » lui pose
	 * « display: block » sans remettre les marges à zéro, et un « p » hériterait de la marge basse de
	 * « base.css », qui déséquilibrerait le cadre.
	 *
	 * Le nom du composant est écrit en CASSE NATURELLE : les capitales sont posées par « editor.css »
	 * en « text-transform ». Tapées en dur, un lecteur d'écran les épellerait lettre à lettre.
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

			var adresse = 'string' === typeof attributs.adresse ? attributs.adresse : '';
			var telephone = 'string' === typeof attributs.telephone ? attributs.telephone : '';
			var courriel = 'string' === typeof attributs.courriel ? attributs.courriel : '';
			var planDescription = 'string' === typeof attributs.plan_description ? attributs.plan_description : '';

			var plan = attributs.plan_id ? parseInt( attributs.plan_id, 10 ) : 0;

			if ( isNaN( plan ) || plan < 0 ) {
				plan = 0;
			}

			var lu = useSelect(
				function ( selectionner ) {
					var ecran = selectionner( 'core/editor' );
					var contenus = selectionner( 'core' );

					/*
					 * getEntityRecord et non getMedia : ce dernier est déprécié depuis la version 6.9
					 * du cœur et imprime un avertissement dans la console à chaque rendu. La
					 * résolution se surveille sur getEntityRecord, qui seule sait dire « la pièce
					 * jointe a été cherchée et n'existe plus » sans le confondre avec « pas encore
					 * chargée ».
					 */
					var fichier = plan && contenus
						? contenus.getEntityRecord( 'postType', 'attachment', plan )
						: null;

					return {
						postId: ecran ? ecran.getCurrentPostId() : 0,
						fichier: fichier,
						introuvable: Boolean(
							plan
								&& contenus
								&& contenus.hasFinishedResolution( 'getEntityRecord', [ 'postType', 'attachment', plan ] )
								&& ! fichier
						)
					};
				},
				[ plan ]
			);

			var planExiste = 0 < plan && false === lu.introuvable;
			var fichierNonImage = Boolean( lu.fichier && lu.fichier.media_type && 'image' !== lu.fichier.media_type );

			var estVide = '' === texte( adresse ) && '' === texte( telephone ) && '' === texte( courriel );

			/**
			 * Bouton qui ouvre la médiathèque.
			 *
			 * MediaUploadCheck entoure MediaUpload : une utilisatrice sans le droit de téléverser voit
			 * le réglage désactivé, au lieu d'un bouton qui échoue sans rien dire.
			 *
			 * @param {string} libelle Texte du bouton.
			 * @return {Object} Élément à afficher.
			 */
			function boutonPlan( libelle ) {
				return el(
					MediaUploadCheck,
					null,
					el( MediaUpload, {
						allowedTypes: [ 'image' ],
						multiple: false,
						value: plan,
						onSelect: function ( choisie ) {
							var maj = { plan_id: choisie && choisie.id ? parseInt( choisie.id, 10 ) : 0 };

							/*
							 * Saisi une fois : la description écrite sur l'image dans la médiathèque
							 * est reprise si le champ est encore vide, et jamais si elle a déjà été
							 * ajustée ici. Rien n'est inventé — c'est le texte qu'elle a elle-même
							 * tapé sur l'image.
							 */
							if ( '' === planDescription && choisie && choisie.alt ) {
								maj.plan_description = choisie.alt;
							}

							definir( maj );
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

			var reglagesPlan = [
				el( 'div', { key: 'choisir' }, boutonPlan( planExiste ? 'Remplacer le plan' : 'Choisir un plan' ) )
			];

			if ( 0 < plan ) {
				reglagesPlan.push(
					el(
						Button,
						{
							key: 'retirer',
							variant: 'link',
							isDestructive: true,
							onClick: function () {
								definir( { plan_id: 0, plan_description: '' } );
							}
						},
						'Retirer le plan'
					)
				);
			}

			reglagesPlan.push(
				el( TextControl, {
					key: 'description',
					/*
					 * « __nextHasNoMarginBottom » évite l'avertissement de dépréciation que le cœur
					 * imprime dans la console depuis la version 6.7 ; le réglage devient le défaut en
					 * version 7.0, où la clé sera simplement ignorée.
					 */
					__nextHasNoMarginBottom: true,
					__next40pxDefaultSize: true,
					/*
					 * Libellé figé par MASTER.md:918 (§10.2), verbatim, et déjà employé à
					 * l'identique par fiche-information/editeur.js:234. Le mot « photo » vaut
					 * ici pour un plan : l'objet n'est pas une photographie, mais le §10 est
					 * l'arbitre du vocabulaire et le catalogue parle d'une seule voix. La
					 * question d'un libellé propre aux images qui ne sont pas des photos est
					 * ouverte pour la prochaine révision de lead-design-mtb ; ne pas la
					 * rouvrir ici. Voir contrat #11 §3.1.
					 */
					label: 'Description de la photo (pour les personnes aveugles)',
					help: 'Laissez vide si le plan ne dit rien de plus que l\'adresse écrite au-dessus. S\'il porte une indication qu\'on ne lit nulle part ailleurs — « entrée par le chemin après le pont » —, écrivez-la ici.',
					value: planDescription,
					onChange: function ( valeur ) {
						definir( { plan_description: valeur } );
					}
				} )
			);

			/*
			 * La classe d'aide est celle du cœur : une aide sous un réglage doit ressembler à toutes
			 * les autres aides de l'éditeur. Aucune apparence n'est inventée ici.
			 */
			reglagesPlan.push(
				el(
					'p',
					{ key: 'aide-legende', className: 'components-base-control__help' },
					'La mention affichée sous le plan — la source de la carte, par exemple — se saisit dans le champ « Légende » de la médiathèque, sur l\'image elle-même. Elle suit l\'image partout où elle est utilisée.'
				)
			);

			var panneaux = el(
				InspectorControls,
				null,
				el(
					PanelBody,
					{ title: 'Coordonnées', initialOpen: true },
					/*
					 * Champs de texte simple, jamais un RichText : ni gras, ni italique, et surtout
					 * aucun lien. Une adresse, un numéro et un courriel se recopient ; ils ne se
					 * mettent pas en forme.
					 */
					el( TextareaControl, {
						__nextHasNoMarginBottom: true,
						label: 'Adresse',
						help: 'Appuyez sur Entrée pour passer à la ligne suivante. L\'adresse s\'affiche exactement comme vous l\'écrivez.',
						rows: 3,
						value: adresse,
						onChange: function ( valeur ) {
							definir( { adresse: valeur } );
						}
					} ),
					el( TextControl, {
						__nextHasNoMarginBottom: true,
						__next40pxDefaultSize: true,
						label: 'Téléphone',
						help: 'Le numéro s\'affiche tel que vous l\'écrivez. Le lien d\'appel retire les espaces tout seul.',
						value: telephone,
						onChange: function ( valeur ) {
							definir( { telephone: valeur } );
						}
					} ),
					el( TextControl, {
						__nextHasNoMarginBottom: true,
						__next40pxDefaultSize: true,
						label: 'Courriel',
						help: 'Si l\'adresse n\'est pas reconnue, elle s\'affiche telle quelle, sans lien cliquable.',
						value: courriel,
						onChange: function ( valeur ) {
							definir( { courriel: valeur } );
						}
					} )
				),
				el( PanelBody, { title: 'Plan d\'accès', initialOpen: false }, reglagesPlan )
			);

			var avis = [];

			/*
			 * Un défaut visible vaut mieux qu'un défaut muet : le plan disparaît de la page publique
			 * dans ces deux cas, et rien à l'écran ne le dirait sans cet avis.
			 */
			if ( lu.introuvable ) {
				avis.push(
					el(
						Notice,
						{ key: 'introuvable', status: 'warning', isDismissible: false },
						'Le plan choisi n\'est plus dans la médiathèque : il ne s\'affiche pas sur le site. Choisissez-en un autre, ou retirez-le.'
					)
				);
			}

			if ( fichierNonImage ) {
				avis.push(
					el(
						Notice,
						{ key: 'fichier', status: 'warning', isDismissible: false },
						'Le fichier choisi n\'est pas une image. Choisissez une image de plan.'
					)
				);
			}

			var corps;

			if ( estVide ) {
				corps = el( EtatVide );
			} else {
				corps = el( RenduServeur, {
					block: NOM,
					attributes: {
						adresse: adresse,
						telephone: telephone,
						courriel: courriel,
						plan_id: plan,
						plan_description: planDescription
					},
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
