<?php
/**
 * Les tables gelées du module, et la source unique du libellé « Adresse de la page ».
 *
 * SOURCE UNIQUE DE TOUTES LES CHAÎNES AFFICHÉES PAR CE MODULE : les libellés photo, « Adresse de la
 * page », et le nom accessible du bouton. Le fichier ne les dénombre pas, à dessein : le nom accessible
 * n'était au gel du contrat #54 qu'une entrée CONDITIONNELLE, entrée après mesure, et un compte écrit
 * ici s'est déjà périmé une fois. LES FONCTIONS FONT FOI. Aucun autre fichier de ce module, PHP ou
 * JavaScript, n'écrit l'une de ces chaînes : le rappel des libellés du type appelle
 * « libelles_du_type() », le JavaScript reçoit « table_javascript() » par « wp_add_inline_script() »,
 * et le rappel de la Modification rapide consulte « table_modification_rapide() ». Recopier l'une de
 * ces chaînes ailleurs fabriquerait une seconde vérité, qui divergerait en silence le jour où l'une
 * des deux changerait.
 *
 * « ADRESSE DE LA PAGE » N'EST ÉCRITE QU'UNE FOIS, dans « libelle_adresse_de_la_page() », parce que
 * deux tables la rendent (contrat #59, interdit n° 16 réécrit). Les CLÉS des tables ne sont pas des
 * chaînes affichées mais des sources anglaises du cœur : les deux tables portent la clé « Slug » parce
 * qu'elles visent deux émissions distinctes, l'une en JavaScript sur l'écran d'une page, l'autre en
 * PHP dans la Modification rapide des listes. ELLES NE SE FUSIONNENT PAS : la table JavaScript porte
 * une chaîne qui n'a jamais été mesurée sur les listes.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Admin\VocabulairePage;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Les quatre libellés de la famille « photo » du type de contenu « page ».
 *
 * LES QUATRE VALEURS SONT RECOPIÉES DU DÉPÔT, ELLES NE SONT PAS RÉDIGÉES ICI. Elles viennent de
 * « includes/content/portee/bootstrap.php », qui les pose déjà dans le tableau « labels » de son
 * register_post_type(), et « includes/content/chien/bootstrap.php » les répète à l'identique.
 * « design-system/MASTER.md » §10.2 ne fige que le NOM — « Photo principale », jamais « image mise
 * en avant » — et ne dit rien des trois verbes. La règle qui s'applique aux trois autres n'est donc
 * pas « recopier §10.2 » mais RECOPIER LE DÉPÔT, pour que les types Portée, Chien et Page disent le
 * même mot. Un dev qui les retaperait de mémoire produirait une quatrième formulation.
 *
 * LES QUATRE, OU AUCUNE — JAMAIS UN SOUS-ENSEMBLE. Deux seulement de ces quatre clés sont mesurées à
 * l'écran d'une page sur WordPress 6.9 (le bouton, par « set_featured_image » ; le titre de la
 * fenêtre des photos, par « featured_image »). L'écart entre ce qui est mesuré et ce qui est écrit
 * est DÉCLARÉ, pas dissimulé, et il tient à quatre motifs. Renommer le bouton sans le titre laisserait
 * le mot interdit à l'écran, juste au-dessus. La fiche d'aide deviendrait inécrivable — elle emploierait
 * le mot interdit pour désigner l'endroit où il ne faut pas l'employer. Portée et Chien disent déjà les
 * quatre : une Page qui n'en dirait que deux créerait un écart qu'une chaîne future « corrigerait » au
 * hasard, dans un sens ou dans l'autre. Et le coût des deux clés non mesurées est de deux affectations.
 *
 * Mémoïsée par un « static » local, comme la table de « admin/description-photo » : construite une
 * fois par requête.
 *
 * @return array<string, string> Clé de libellé du cœur => libellé affiché.
 */
function libelles_du_type(): array {
	static $libelles = null;

	if ( null === $libelles ) {
		$libelles = array(
			'featured_image'        => 'Photo principale',
			'set_featured_image'    => 'Choisir la photo principale',
			'remove_featured_image' => 'Retirer la photo principale',
			'use_featured_image'    => 'Utiliser comme photo principale',
		);
	}

	return $libelles;
}

/**
 * Le libellé qui remplace « Slug » partout où ce module le renomme.
 *
 * RECOPIÉ, PAS RÉDIGÉ : « design-system/MASTER.md » §10.2 le fige, et les écrans de saisie d'une
 * portée et d'un chien titrent déjà leur boîte ainsi. Le même mot vaut pour les trois types de la
 * Modification rapide et pour l'écran d'une page. On ne le raccourcit jamais, même si une rangée
 * passe à la ligne : ni « Adresse », ni « Adresse page ».
 *
 * AUCUN « % », « < », « > » NI « & » : cette valeur passe par les deux points d'extension de
 * traduction, PHP et JavaScript, où ces caractères déclenchent la panne décrite plus bas.
 *
 * @return string Le libellé affiché.
 */
function libelle_adresse_de_la_page(): string {
	return 'Adresse de la page';
}

/**
 * Les chaînes sources anglaises du navigateur, et le libellé français qui les remplace.
 *
 * ON COMPARE SUR L'ANGLAIS, JAMAIS SUR LE FRANÇAIS, exactement comme « admin/description-photo ». Le
 * point d'extension « i18n.gettext » reçoit la traduction en premier paramètre et la CHAÎNE SOURCE en
 * deuxième : comparer la source rend le module indifférent à l'état de la langue du site, il mord
 * aussi bien sur une installation restée en anglais — cas que le provisionnement documente comme
 * possible. Comparer le français serait de surcroît un piège de caractère : l'apostrophe du cœur est
 * U+2019 (’) et non U+0027 ('), et une comparaison écrite avec la mauvaise échouerait sans le dire.
 *
 * LES DEUX ENTRÉES, ET POURQUOI CHACUNE. Relevé au navigateur, WORDPRESS 6.9, LE 2026-09-08, sur
 * l'écran d'édition d'une page :
 *
 *   « Slug » — msgid NU, sans contexte, émis par « __() » depuis le paquet « wp-editor ». TROIS
 *   émissions sur cet écran : l'étiquette de la rangée de la zone latérale, le titre de la fenêtre
 *   volante qu'elle ouvre, et l'étiquette du champ de saisie de cette fenêtre — cette dernière est
 *   masquée à l'œil et lue par les lecteurs d'écran. Le catalogue français traduit « Slug » par
 *   « Slug » : ce n'est PAS une traduction manquante, c'est la traduction officielle, et aucun filtre
 *   PHP ne l'atteint sur l'écran d'édition d'une page (sur les listes, depuis #59, un filtre PHP
 *   l'atteint : voir « table_modification_rapide() »).
 *
 *   « Edit or replace the featured image » — msgid NU également, nom accessible du bouton de la photo
 *   quand une photo est déjà posée. Le mot interdit y survit INVISIBLE À L'ŒIL ET PRONONCÉ À VOIX
 *   HAUTE. Le remplacement n'est pas une phrase rédigée : c'est la phrase française du cœur — « Modifier
 *   ou remplacer l’image mise en avant » — avec le SEUL groupe nominal substitué. Rien d'autre n'a
 *   bougé, ni le verbe, ni la conjonction, ni l'ordre des mots.
 *
 * CE QUI N'EST PAS DANS CETTE TABLE, ET NE DOIT PAS Y ENTRER SANS MESURE. Une entrée écrite « au cas
 * où » est un renommage non mesuré, qui déborde sur des écrans que personne n'a regardés. En
 * particulier : la phrase d'aide de la fenêtre volante emploie le mot « permalien », interdit lui
 * aussi, mais c'est un TEXTE D'AIDE et non une étiquette — « admin/description-photo » a déjà refusé
 * de remplacer un texte d'aide du cœur, au motif qu'on ne remplace jamais un texte par un autre qui en
 * dit moins, et §10.2 fige des libellés, jamais des phrases. Quant à la page d'accueil, la ligne n'y
 * devient « Link » QUE POUR UN ADMINISTRATEUR : le cœur fait « isFrontPage ? __("Link") : __("Slug") »
 * et calcule « isFrontPage » depuis les réglages du site, que le rôle ÉDITEUR ne peut pas lire
 * (« /wp-json/wp/v2/settings » rend 403, « page_on_front » vaut null). Pour l'éleveuse,
 * « isFrontPage » est donc FAUX, le cœur émet « Slug », et LA TABLE CI-DESSOUS LE RENOMME DÉJÀ.
 * Mesuré sur les deux comptes le 2026-09-08. N'ajoutez donc pas « Link » ici pour « compléter » :
 * ce serait renommer une rangée que seule l'administration voit, sur un écran hors du parcours de
 * l'éleveuse, et sans qu'aucun mot du §10.4 soit en cause — « Lien » n'est pas un mot interdit.
 *
 * AUCUNE VALEUR NE PORTE « % », « < », « > » NI « & », et c'est une contrainte opposable à toute ligne
 * future. « @wordpress/i18n » expose sprintf() et de nombreuses chaînes du cœur y passent : un « % » de
 * trop y lève la même panne que du côté PHP, celle que « admin/corbeille/bootstrap.php » documente.
 *
 * @return array<string, string> Chaîne source anglaise du cœur => libellé affiché.
 */
function table_javascript(): array {
	static $table = null;

	if ( null === $table ) {
		$table = array(
			'Slug'                               => libelle_adresse_de_la_page(),
			'Edit or replace the featured image' => 'Modifier ou remplacer la photo principale',
		);
	}

	return $table;
}

/**
 * Les chaînes sources anglaises de la Modification rapide des listes, et leur libellé français.
 *
 * UNE SEULE ENTRÉE, MESURÉE. Relevé au navigateur, WORDPRESS 6.9, LE 2026-09-17, session Éditrice, dans
 * le panneau « Modification rapide » des listes Pages, Portées et Chiens : « Slug » est le seul mot du
 * §10.4 de « design-system/MASTER.md » qui s'y trouve. Il est émis en PHP par
 * « WP_Posts_List_Table::inline_edit() », msgid NU, domaine « default ». « Modèle » reste : c'est le mot
 * français du cœur, et §10.4 n'interdit que l'anglais « template ».
 *
 * AUCUNE ENTRÉE N'ENTRE ICI SANS AVOIR ÉTÉ MESURÉE DANS CE PANNEAU. Le rappel qui lit cette table
 * reçoit TOUTES les chaînes du domaine « default » traduites après « load-edit.php » sur trois listes
 * entières — en-têtes de colonnes, actions groupées, messages, pied de page. Une entrée ajoutée « au
 * cas où » renommerait ce mot partout sur ces écrans, et non dans le seul panneau.
 *
 * Mémoïsée par un « static » local : le rappel qui la consulte est appelé pour chaque chaîne traduite
 * de la liste.
 *
 * @return array<string, string> Chaîne source anglaise du cœur => libellé affiché.
 */
function table_modification_rapide(): array {
	static $table = null;

	if ( null === $table ) {
		$table = array(
			'Slug' => libelle_adresse_de_la_page(),
		);
	}

	return $table;
}
