/**
 * Le renommage des chaînes servies par « wp.i18n » sur l'écran d'édition d'une page.
 *
 * CE QUE CE FICHIER FAIT, ET RIEN D'AUTRE : il pose un unique rappel sur le point d'extension
 * « i18n.gettext » et rend, pour les seules chaînes sources de la table, le libellé français que le
 * serveur lui a passé. Partout ailleurs il rend la traduction reçue, sans y toucher.
 *
 * POURQUOI DU JAVASCRIPT ALORS QUE LE RESTE DU MODULE EST DU PHP. Relevé sur WORDPRESS 6.9, le
 * 2026-09-08 : la rangée « Slug » de la zone latérale n'est émise par AUCUN PHP sur cet écran. Elle
 * vient du paquet « wp-editor », par « wp.i18n », et son catalogue français la traduit par « Slug ».
 * Ce n'est pas une traduction manquante : aucun filtre PHP ne l'atteint, et un filtre « gettext » PHP
 * y serait inerte ici tout en débordant ailleurs.
 *
 * ES5, AUCUNE SYNTAXE JSX, AUCUNE ÉTAPE DE CONSTRUCTION : le fichier est servi tel quel, lisible et
 * modifiable sans outillage. Il n'est mis en file que sur l'écran d'édition d'une page ; le visiteur
 * du site n'en reçoit pas un octet.
 *
 * TOUTES LES CHAÎNES VIENNENT DU SERVEUR, dans « window.mtbVocabulairePage.table ». Ce fichier n'en
 * compose aucune, n'en concatène aucune et n'en corrige aucune.
 *
 * CINQ INTERDITS DANS LE RAPPEL, CHACUN POUR UNE PANNE PRÉCISE :
 *
 *   1. AUCUN APPEL À « wp.i18n.__() », « _x() » NI « sprintf() ». Nous SOMMES dans « i18n.gettext » :
 *      le moindre appel se rappellerait lui-même, en récursion infinie, dès la première chaîne.
 *   2. ON N'ÉCHAPPE PAS. La valeur est rendue par React, qui échappe. Échapper ici doublerait
 *      l'échappement et une apostrophe ressortirait en « &#039; » au milieu d'une étiquette.
 *   3. AUCUNE VALEUR NE PORTE « % », « < », « > » NI « & ». « @wordpress/i18n » expose sprintf(), et
 *      de nombreuses chaînes du cœur y passent : un « % » de trop y lève la même panne que du côté
 *      PHP. La garde est posée dans « libelles.php », qui écrit les valeurs ; ce fichier n'en fabrique
 *      aucune.
 *   4. COMPARAISON STRICTE DE LA CHAÎNE ENTIÈRE. Aucune expression régulière, aucun « indexOf »,
 *      aucun « replace », aucun « toLowerCase ». C'est ce qui borne le module aux émissions relevées
 *      et lui interdit de déborder sur une phrase qui contiendrait le mot par hasard.
 *   5. AUCUN TEST D'ÉCRAN, AUCUN TEST DE PANNEAU, AUCUNE LECTURE DU DOM. La borne d'écran est posée
 *      CÔTÉ SERVEUR, par la garde de « ecran.php » qui décide de mettre ce fichier en file ou non. La
 *      reposer ici serait deux vérités à tenir, qui divergeraient.
 *
 * CE QUE LA GARDE D'ÉCRAN NE BORNE PAS, ET IL FAUT LE SAVOIR : une fois ce fichier chargé, le filtre
 * s'applique à TOUT le JavaScript de cet écran — dialogues et inserteur compris. On ne peut pas le
 * borner à un panneau. Ce qui est borné, c'est CE QUI ENTRE DANS LA TABLE : uniquement des chaînes
 * sources mesurées sur cet écran.
 *
 * MODE DE PANNE. Cette moitié tombe en silence dans cinq cas : le cœur reformule une chaîne source ;
 * une dépendance manque ; la chaîne est capturée avant que le filtre soit posé ; le point d'extension
 * change de nom ; la charge du serveur n'est pas imprimée. Dans les cinq, l'écran redit « Slug », la
 * page s'enregistre normalement et le journal reste vide. AUCUN TÉMOIN AUTOMATIQUE N'EST LIVRÉ, et
 * c'est une décision : chercher « Slug » dans un paquet du cœur est un canari faux vert, la présence
 * de la source ne disant rien de l'interception. Le seul témoin honnête est un navigateur réel, et la
 * vérification se rejoue EN ENTIER à chaque montée de version de WordPress.
 */
( function () {
	'use strict';

	if ( ! window.wp || ! wp.hooks || ! wp.hooks.addFilter ) {
		return;
	}

	var reglages = window.mtbVocabulairePage;

	/*
	 * Repli explicite plutôt que confiance : si la charge du serveur manque, le module devient inerte,
	 * jamais fautif. Un renommage absent se voit à l'écran ; une erreur de script emporterait l'éditeur
	 * entier pour deux libellés.
	 */
	if ( ! reglages || ! reglages.table ) {
		return;
	}

	/*
	 * Table lue UNE FOIS, capturée en portée de fichier : c'est l'équivalent JavaScript de la
	 * mémoïsation par « static » de la table PHP. Le rappel qui suit est appelé pour chaque chaîne
	 * traduite de l'écran, soit des milliers de fois.
	 */
	var table = reglages.table;

	/**
	 * Rend le libellé français pour les chaînes de la table, la traduction reçue partout ailleurs.
	 *
	 * Les gardes vont de la moins chère à la plus chère. Le domaine d'abord : la forme accepte une
	 * valeur absente, le cœur pouvant appeler « __() » sans domaine — auquel cas le domaine EST le
	 * domaine par défaut, et la chaîne nous concerne.
	 *
	 * « hasOwnProperty » et jamais « table[ texte ] » nu : « constructor », « toString » et quelques
	 * autres sont des clés héritées de tout objet, et une chaîne du cœur qui porterait ce nom
	 * ressortirait transformée en fonction.
	 *
	 * @param {string} traduction Traduction que le cœur s'apprête à rendre.
	 * @param {string} texte      Chaîne source anglaise, telle qu'écrite dans le cœur.
	 * @param {string} domaine    Domaine de traduction de l'appel.
	 *
	 * @return {string} Le libellé français pour nos chaînes, la traduction reçue partout ailleurs.
	 */
	function remplacer( traduction, texte, domaine ) {
		if ( domaine && 'default' !== domaine ) {
			return traduction;
		}

		if ( 'string' !== typeof texte ) {
			return traduction;
		}

		if ( ! Object.prototype.hasOwnProperty.call( table, texte ) ) {
			return traduction;
		}

		return table[ texte ];
	}

	wp.hooks.addFilter( 'i18n.gettext', 'mtb/vocabulaire-page', remplacer );
}() );
