<?php
/**
 * Composant « Formulaire de contact » — composition et échappement du balisage public.
 *
 * Fichier inclus UNE SEULE FOIS, par « bootstrap.php ». Il est le seul du module, avec les quatre
 * autres fichiers d'aide, à déclarer des fonctions : « render.php » est inclus par le cœur avec un
 * « require » NU, donc une fois par instance du bloc présente sur la page.
 *
 * CE FICHIER N'ÉMET AUCUNE RÈGLE VISUELLE. Il pose des crochets de classes — vingt exactement, la
 * liste du contrat #22 §7 est CLOSE dans les deux sens : le serveur n'en émet aucun qui n'y figure,
 * et chacun de ceux qui y figurent doit être stylé ou nommé comme délibérément non stylé dans
 * « mtb-formulaire-contact.css ». L'habillage appartient au thème, sans exception.
 *
 * L'ÉCHAPPEMENT EST SYSTÉMATIQUE ET SE FAIT ICI, au plus près de la sortie : « esc_html() » pour le
 * texte, « esc_attr() » pour un attribut, « esc_url() » pour une adresse, « esc_textarea() » pour la
 * zone de saisie — jamais « esc_attr() » sur elle, qui laisserait passer une fin de balise —, et
 * « wp_kses() » pour la seule valeur qui a le droit de porter du balisage, la mention.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Blocks\FormulaireContact;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Racine des crochets de classes, et des identifiants de champ. */
const CROCHET = 'mtb-formulaire-contact';

/**
 * Ancre du composant — CONTRACTUELLE, fixée par le rendu et jamais réglable.
 *
 * Elle est à la fois la cible du « action » du formulaire, celle de l'en-tête « Location: » du 303
 * et celle du lien de reprise. C'est pourquoi « supports.anchor » vaut false dans « block.json » :
 * une ancre saisie par l'éleveuse la remplacerait et casserait les trois d'un coup.
 */
const ANCRE = 'mtb-formulaire-contact';

/**
 * Mention d'information retenue, telle qu'elle est enregistrée.
 *
 * Recastée sans faire confiance au schéma de « block.json » : « do_blocks() » peut aussi tourner
 * sur du contenu importé par la reprise de l'ancien site, ou forgé à la main.
 *
 * @param array<string, mixed> $attributs Attributs du bloc.
 *
 * @return string Mention brute, jamais normalisée.
 */
function mention_retenue( array $attributs ): string {
	if ( ! isset( $attributs['mention'] ) || ! is_string( $attributs['mention'] ) ) {
		return '';
	}

	return $attributs['mention'];
}

/**
 * Résultat de la soumission, s'il concerne bien la page en cours de rendu.
 *
 * LE COMPARATEUR DE PAGE EST INDISPENSABLE : le bloc peut être rendu hors de la page postée — dans
 * un extrait, une boucle secondaire, un rendu différé. En cas d'écart, le résultat est IGNORÉ et le
 * formulaire se rend vierge. Jamais l'erreur d'une page sur une autre.
 *
 * @param int $post_id Contenu en cours de rendu.
 *
 * @return array<string, mixed>|null Résultat concordant, ou null.
 */
function resultat_pour( int $post_id ): ?array {
	$resultat = Etat::resultat();

	if ( null === $resultat ) {
		return null;
	}

	$origine = isset( $resultat['post_id'] ) ? (int) $resultat['post_id'] : 0;

	if ( 0 === $post_id || $origine !== $post_id ) {
		return null;
	}

	return $resultat;
}

/**
 * Compose le rendu public complet, selon la table de décision du contrat §6.8.
 *
 * Le rang d'instance a déjà été traité par « render.php » : seule la première instance arrive ici.
 *
 * @param array<string, mixed> $attributs Attributs du bloc.
 * @param int                  $post_id   Contenu en cours de rendu.
 *
 * @return string Balisage complet et échappé, ou chaîne vide pour ne rien rendre du tout.
 */
function composer( array $attributs, int $post_id ): string {
	$mention   = mention_retenue( $attributs );
	$permalien = $post_id > 0 ? (string) get_permalink( $post_id ) : '';

	// 2. Marqueur de confirmation en URL. Le formulaire n'est PAS re-rendu : ses champs seraient
	// vides — rien n'est stocké — et un formulaire vierge sous « Message envoyé. » inviterait à un
	// second envoi qu'aucune déduplication ne pourrait absorber (Q-back-3, ratifiée).
	if ( marqueur_de_confirmation_present() ) {
		return enveloppe( confirmation( $permalien ) );
	}

	$resultat = resultat_pour( $post_id );

	// 3. Une soumission a eu lieu sur CETTE page : on répond, quoi qu'il arrive.
	if ( null !== $resultat ) {
		$action  = adresse_de_soumission( $permalien );
		$valeurs = valeurs_du_resultat( $resultat );
		$champs  = isset( $resultat['champs'] ) && is_array( $resultat['champs'] ) ? $resultat['champs'] : array();
		$issue   = isset( $resultat['issue'] ) && is_string( $resultat['issue'] ) ? $resultat['issue'] : 'erreurs';

		if ( 'erreurs' === $issue ) {
			$globales = isset( $resultat['globales'] ) && is_array( $resultat['globales'] ) ? $resultat['globales'] : array();

			return enveloppe( resume( $globales, $champs ) . formulaire( $action, $valeurs, $champs, $mention ) );
		}

		return enveloppe( information( $issue ) . formulaire( $action, $valeurs, $champs, $mention ) );
	}

	// 4. Hors soumission, un formulaire sans destination est une promesse fausse, et une mention
	// vide est une information promise qui manque : le composant n'existe pas du tout côté public.
	// L'éleveuse, elle, lit dans l'éditeur la phrase qui dit lequel des deux manque.
	if ( ! destination_utilisable() || mention_est_vide( $mention ) ) {
		return '';
	}

	// 5. Sans adresse de page, le formulaire ne saurait pas où poster.
	if ( '' === $permalien ) {
		return '';
	}

	// 6. Formulaire vierge.
	return enveloppe( formulaire( adresse_de_soumission( $permalien ), valeurs_vides(), array(), $mention ) );
}

/**
 * Valeurs à rappeler dans le formulaire, toujours les trois clés, toujours des chaînes.
 *
 * @param array<string, mixed> $resultat Résultat posé par le traitement.
 *
 * @return array{nom: string, courriel: string, message: string}
 */
function valeurs_du_resultat( array $resultat ): array {
	$valeurs = isset( $resultat['valeurs'] ) && is_array( $resultat['valeurs'] ) ? $resultat['valeurs'] : array();
	$sures   = valeurs_vides();

	foreach ( array_keys( $sures ) as $cle ) {
		if ( isset( $valeurs[ $cle ] ) && is_string( $valeurs[ $cle ] ) ) {
			$sures[ $cle ] = $valeurs[ $cle ];
		}
	}

	return $sures;
}

/**
 * Adresse de soumission du formulaire, ancre comprise.
 *
 * L'ancre dans le « action » n'est pas décorative : après un rechargement en erreur, elle ramène la
 * visiteuse au formulaire plutôt qu'en haut de page.
 *
 * @param string $permalien Adresse de la page, éventuellement vide.
 *
 * @return string Adresse non échappée.
 */
function adresse_de_soumission( string $permalien ): string {
	// Un « action » réduit à l'ancre poste sur l'adresse courante : repli correct, jamais vide.
	if ( '' === $permalien ) {
		return '#' . ANCRE;
	}

	return $permalien . '#' . ANCRE;
}

/**
 * Enveloppe commune aux quatre états visibles.
 *
 * « tabindex="-1" » N'EST PAS DÉCORATIF. Après un rechargement, l'ancre « #mtb-formulaire-contact »
 * fait de cette enveloppe le point de départ de la navigation séquentielle : la touche Tab suivante
 * entre donc dans le formulaire. C'est LE SEUL SUBSTITUT SANS JAVASCRIPT à une mise au focus, et
 * l'issue n'a pas le droit d'un octet de JavaScript public.
 *
 * @param string $interieur Balisage déjà composé et échappé.
 *
 * @return string Enveloppe complète, ou chaîne vide si l'intérieur est vide.
 */
function enveloppe( string $interieur ): string {
	if ( '' === $interieur ) {
		return '';
	}

	/*
	 * « wp-block-mtb-formulaire-contact » est écrit ICI, à la main, et ce n'est pas une redondance :
	 * « supports.className » vaut false dans « block.json » — le contrat §4.2 l'exige, pour que
	 * l'éleveuse ne puisse pas coller de classe sur le composant — et c'est ce réglage, et lui seul,
	 * qui empêche le cœur d'ajouter la classe « wp-block-<nom> ». Mesuré sur la pile : sans cette
	 * ligne, l'enveloppe sortait avec le seul crochet « mtb-formulaire-contact », alors que le
	 * contrat §6.1 fige les deux classes, dans cet ordre.
	 */
	$attributs = get_block_wrapper_attributes(
		array(
			'class'    => 'wp-block-mtb-formulaire-contact ' . CROCHET,
			'id'       => ANCRE,
			'tabindex' => '-1',
		)
	);

	// get_block_wrapper_attributes() échappe lui-même chacune de ses valeurs ; l'entourer
	// d'esc_attr() doublerait l'échappement et sortirait des guillemets en entités.
	return '<div ' . $attributs . '>' . $interieur . '</div>';
}

/**
 * Résumé d'erreurs, en tête du formulaire — État 2.
 *
 * PAS DE « role="alert" », et c'est délibéré : ce résumé est du contenu de document initial, non
 * une mise à jour dynamique. Son annonce au chargement est incohérente d'un lecteur d'écran à
 * l'autre. Le dispositif retenu est le couple ancre + « tabindex="-1" » + titre, qui fonctionne
 * partout et sans JavaScript.
 *
 * Une erreur GLOBALE — jeton, délai — est une ligne SANS LIEN : elle ne désigne aucun champ, et un
 * lien qui ne mène nulle part est pire que pas de lien.
 *
 * @param string[]              $globales Erreurs sans champ désigné.
 * @param array<string, string> $champs   Erreurs par champ.
 *
 * @return string Balisage échappé, ou chaîne vide s'il n'y a rien à résumer.
 */
function resume( array $globales, array $champs ): string {
	$lignes = '';

	// Ordre de saisie, pour que la liste se lise comme le formulaire se remplit.
	foreach ( array( 'nom', 'courriel', 'message' ) as $cle ) {
		if ( ! isset( $champs[ $cle ] ) || ! is_string( $champs[ $cle ] ) ) {
			continue;
		}

		$lignes .= '<li><a href="' . esc_url( '#' . identifiant( $cle ) ) . '">'
			. esc_html( $champs[ $cle ] )
			. '</a></li>';
	}

	foreach ( $globales as $globale ) {
		if ( ! is_string( $globale ) || '' === $globale ) {
			continue;
		}

		$lignes .= '<li>' . esc_html( $globale ) . '</li>';
	}

	if ( '' === $lignes ) {
		return '';
	}

	return '<div class="' . esc_attr( CROCHET . '__resume' ) . '">'
		. '<h2 class="' . esc_attr( CROCHET . '__resume-titre' ) . '">' . esc_html( TITRE_RESUME ) . '</h2>'
		. '<ul>' . $lignes . '</ul>'
		. '</div>';
}

/**
 * Encart d'envoi impossible — État 4.
 *
 * ON NE MENT PAS. Le rejet silencieux — afficher « Message envoyé. » pour priver un robot de tout
 * signal — a été refusé : c'est une AFFIRMATION FAUSSE faite à un humain dont le message n'existera
 * plus nulle part, et la décision 45 dit sans détour qu'un courriel perdu est perdu. Le texte
 * affiché NE NOMME PAS LE PIÈGE : un robot n'apprend pas quel champ l'a trahi.
 *
 * Exposition déclarée : qui déclenche le piège reçoit l'adresse de l'élevage. Elle est DÉJÀ
 * publique au pied de page de toutes les pages depuis l'issue #18, en « mailto: » non obfusqué.
 * Aucune exposition nouvelle.
 *
 * @param string $issue Nature de l'issue.
 *
 * @return string Balisage échappé.
 */
function information( string $issue ): string {
	$causes = array(
		'piege'               => CAUSE_PIEGE,
		'envoi_echoue'        => CAUSE_ENVOI_ECHOUE,
		'destination_absente' => CAUSE_DESTINATION_ABSENTE,
		'corps_perdu'         => CAUSE_CORPS_PERDU,
	);

	$cause = $causes[ $issue ] ?? CAUSE_ENVOI_ECHOUE;

	/*
	 * Corps de requête perdu : le fragment « en recopiant votre message ci-dessous » est retiré.
	 * Il n'y a rien ci-dessous — le texte n'est jamais arrivé jusqu'au site — et l'écrire serait
	 * envoyer la visiteuse chercher un message qui n'existe pas.
	 */
	$avec_recopie = 'corps_perdu' !== $issue;

	return '<div class="' . esc_attr( CROCHET . '__information' ) . '">'
		. '<h2 class="' . esc_attr( CROCHET . '__information-titre' ) . '">' . esc_html( TITRE_INFORMATION ) . '</h2>'
		. '<p class="' . esc_attr( CROCHET . '__information-texte' ) . '">' . esc_html( $cause ) . '</p>'
		. recours_courriel( $avec_recopie )
		. recours_telephone()
		. '</div>';
}

/**
 * Ligne de recours par courriel de l'encart d'envoi impossible.
 *
 * La ligne DISPARAÎT sans adresse utilisable — on n'écrit pas « Non renseigné » à côté de « vous
 * pouvez écrire directement à ». L'adresse affichée est celle qui marche : proposer une adresse que
 * le cœur juge invalide serait proposer un recours qui n'en est pas un.
 *
 * @param bool $avec_recopie Vrai quand le message saisi est réaffiché sous l'encart.
 *
 * @return string Balisage échappé, ou chaîne vide.
 */
function recours_courriel( bool $avec_recopie ): string {
	$adresse = destination();

	if ( '' === $adresse ) {
		return '';
	}

	$lisible = esc_html( $adresse );
	$lien    = lien_de_recours_courriel( $adresse );

	if ( '' !== $lien ) {
		$lisible = '<a href="' . esc_url( $lien ) . '">' . $lisible . '</a>';
	}

	// Le gabarit est échappé AVANT la substitution ; le fragment inséré l'a été séparément.
	return '<p class="' . esc_attr( CROCHET . '__information-recours' ) . '">'
		. sprintf( esc_html( gabarit_recours_courriel( $avec_recopie ) ), $lisible )
		. '</p>';
}

/**
 * Ligne de recours par téléphone de l'encart d'envoi impossible.
 *
 * Le numéro s'affiche TEL QU'IL EST STOCKÉ : aucun groupage, aucun indicatif ajouté (décision 38).
 * Sans numéro composable, il reste en texte nu plutôt que dans un lien qui composerait faux.
 *
 * @return string Balisage échappé, ou chaîne vide.
 */
function recours_telephone(): string {
	$numero = telephone_de_recours();

	if ( null === $numero ) {
		return '';
	}

	$lisible = esc_html( $numero );
	$lien    = lien_de_recours_telephone( $numero );

	if ( '' !== $lien ) {
		$lisible = '<a href="' . esc_url( $lien ) . '">' . $lisible . '</a>';
	}

	return '<p class="' . esc_attr( CROCHET . '__information-recours' ) . '">'
		. sprintf( esc_html( gabarit_recours_telephone() ), $lisible )
		. '</p>';
}

/**
 * Encart de confirmation — État 3.
 *
 * DEUX PHRASES ET PAS UNE DE PLUS : aucune promesse de réponse. « L'élevage vous répondra » serait
 * un engagement pris au nom de l'éleveuse, sur un délai que personne ici ne connaît.
 *
 * Le lien de reprise pointe vers la page NUE, sans le marqueur : cliquer dessus rend un formulaire
 * vierge, et non une seconde confirmation.
 *
 * @param string $permalien Adresse de la page, éventuellement vide.
 *
 * @return string Balisage échappé.
 */
function confirmation( string $permalien ): string {
	$encart = '<div class="' . esc_attr( CROCHET . '__confirmation' ) . '">'
		. '<p><strong>' . esc_html( CONFIRMATION_PREFIXE ) . '</strong> ' . esc_html( CONFIRMATION_TEXTE ) . '</p>'
		. '</div>';

	// Sans adresse de page, le lien de reprise n'existe pas : mieux vaut pas de lien qu'un lien mort.
	if ( '' === $permalien ) {
		return $encart;
	}

	return $encart
		. '<p class="' . esc_attr( CROCHET . '__reprise' ) . '">'
		. '<a href="' . esc_url( $permalien . '#' . ANCRE ) . '">' . esc_html( CONFIRMATION_REPRISE ) . '</a>'
		. '</p>';
}

/**
 * Identifiant d'une saisie — cible du « for » de l'étiquette ET des liens du résumé.
 *
 * @param string $cle Clé du champ.
 *
 * @return string Identifiant.
 */
function identifiant( string $cle ): string {
	return 'mtb-contact-' . $cle;
}

/**
 * Le formulaire lui-même — États 1, 2 et 4.
 *
 * TROIS CHOIX CONTRACTUELS, tous portés ici :
 *
 * - « novalidate » AVEC « required ». « required » garde la sémantique « obligatoire » pour les
 *   technologies d'assistance ; « novalidate » empêche la bulle native du navigateur, pour que LE
 *   SERVEUR RESTE LA SEULE SOURCE DES MESSAGES. Conséquence pour la feuille de style : ne jamais
 *   styler « :invalid », « :valid » ni « :user-invalid » — un champ obligatoire vide est
 *   « :invalid » dès le premier affichage, et le peindre en erreur accuserait la visiteuse avant
 *   qu'elle ait tapé un caractère.
 * - « type="email" » et « inputmode="email" » sont là pour le clavier des téléphones, PAS pour
 *   valider quoi que ce soit.
 * - AUCUN « maxlength ». Il tronquerait un collage en silence. Les plafonds sont contrôlés côté
 *   serveur et refusés avec une phrase, jamais rognés.
 *
 * UN JETON NEUF EST ÉMIS À CHAQUE RÉAFFICHAGE, et les trois valeurs sont toujours rappelées : un
 * jeton invalide ou expiré ne vide JAMAIS les champs.
 *
 * @param string                                                $action  Adresse de soumission.
 * @param array{nom: string, courriel: string, message: string} $valeurs Valeurs à rappeler.
 * @param array<string, string>                                 $champs  Erreurs par champ.
 * @param string                                                $mention Mention d'information, brute.
 *
 * @return string Balisage échappé.
 */
function formulaire( string $action, array $valeurs, array $champs, string $mention ): string {
	$sortie = '<form class="' . esc_attr( CROCHET . '__formulaire' ) . '" method="post"'
		. ' action="' . esc_url( $action ) . '"'
		. ' accept-charset="UTF-8" novalidate aria-label="' . esc_attr( NOM_FORMULAIRE ) . '">';

	$sortie .= saisie_texte( 'nom', ETIQUETTE_NOM, $valeurs['nom'], $champs['nom'] ?? '', 'text', 'name', '' );
	$sortie .= saisie_texte( 'courriel', ETIQUETTE_COURRIEL, $valeurs['courriel'], $champs['courriel'] ?? '', 'email', 'email', 'email' );
	$sortie .= saisie_zone( 'message', ETIQUETTE_MESSAGE, $valeurs['message'], $champs['message'] ?? '' );

	$sortie .= piege();

	$sortie .= '<input type="hidden" name="mtb_contact_action" value="envoi">';
	$sortie .= '<input type="hidden" name="mtb_contact_jeton" value="' . esc_attr( jeton_creer() ) . '">';

	/*
	 * La mention est passée à wp_kses() TELLE QU'ELLE EST ENREGISTRÉE : la détection du vide
	 * travaille sur une copie, jamais sur la valeur émise. esc_html() afficherait « <a href="…"> »
	 * en clair au milieu de la phrase de l'éleveuse.
	 *
	 * Sur un POST, une mention vide ne fait PAS disparaître le composant — il faut répondre à la
	 * visiteuse ; le paragraphe est simplement absent.
	 */
	if ( ! mention_est_vide( $mention ) ) {
		$sortie .= '<p class="' . esc_attr( CROCHET . '__mention' ) . '">'
			. wp_kses( $mention, balises_admises(), protocoles_admis() )
			. '</p>';
	}

	// Le bouton ne porte AUCUNE classe : « base.css » habille déjà l'élément « button » en entier,
	// et le conteneur « __actions » est là pour l'empêcher de s'étirer sur toute la grille.
	$sortie .= '<div class="' . esc_attr( CROCHET . '__actions' ) . '">'
		. '<button type="submit">' . esc_html( LIBELLE_ENVOI ) . '</button>'
		. '</div>';

	return $sortie . '</form>';
}

/**
 * Compose l'attribut « aria-describedby » d'une saisie.
 *
 * L'ERREUR EN PREMIER, l'aide ensuite : c'est l'ordre dans lequel les technologies d'assistance
 * lisent la liste, et une visiteuse doit entendre ce qui ne va pas avant d'entendre à quoi sert le
 * champ.
 *
 * @param string $id      Identifiant de la saisie.
 * @param bool   $erreur  Vrai quand le champ porte une erreur.
 * @param bool   $aide    Vrai quand le champ porte une note d'aide.
 *
 * @return string Attribut complet précédé d'une espace, ou chaîne vide.
 */
function decrit_par( string $id, bool $erreur, bool $aide ): string {
	$cibles = array();

	if ( $erreur ) {
		$cibles[] = $id . '-erreur';
	}

	if ( $aide ) {
		$cibles[] = $id . '-aide';
	}

	if ( array() === $cibles ) {
		return '';
	}

	return ' aria-describedby="' . esc_attr( implode( ' ', $cibles ) ) . '"';
}

/**
 * Ouverture d'un groupe : le conteneur, l'étiquette, et la note d'aide s'il y en a une.
 *
 * « __champ » DÉSIGNE LE GROUPE — étiquette, aide, saisie et message d'erreur — et non l'élément de
 * saisie, qui est « __saisie ». Le vocabulaire suit celui de l'éleveuse : « le champ Nom » nomme
 * l'étiquette et sa case, pas la case seule.
 *
 * L'étiquette est un vrai « label » lié par « for », visible AU-DESSUS du champ : jamais un
 * « placeholder » en guise d'étiquette, qui disparaît dès la première frappe.
 *
 * La mention « (obligatoire) » est ÉCRITE DANS L'ÉTIQUETTE, jamais un astérisque coloré.
 *
 * @param string $id        Identifiant de la saisie.
 * @param string $etiquette Libellé visible.
 * @param string $aide      Note d'aide, ou chaîne vide.
 *
 * @return string Balisage échappé.
 */
function ouverture_de_groupe( string $id, string $etiquette, string $aide ): string {
	$sortie = '<div class="' . esc_attr( CROCHET . '__champ' ) . '">'
		. '<label class="' . esc_attr( CROCHET . '__etiquette' ) . '" for="' . esc_attr( $id ) . '">'
		. esc_html( $etiquette ) . ' '
		. '<span class="' . esc_attr( CROCHET . '__obligatoire' ) . '">' . esc_html( MENTION_OBLIGATOIRE ) . '</span>'
		. '</label>';

	if ( '' !== $aide ) {
		$sortie .= '<span class="' . esc_attr( CROCHET . '__aide' ) . '" id="' . esc_attr( $id . '-aide' ) . '">'
			. esc_html( $aide )
			. '</span>';
	}

	return $sortie;
}

/**
 * Fermeture d'un groupe : le message d'erreur s'il y en a un, puis le conteneur.
 *
 * Le second signal exigé par le contrat §12.9 est LE PRÉFIXE « Erreur : » ÉCRIT DANS LE TEXTE,
 * jamais un « ::before » : un « content: » disparaît d'une copie de texte et de certains modes de
 * restitution, et la couleur seule ne dit rien.
 *
 * @param string $id     Identifiant de la saisie.
 * @param string $erreur Message d'erreur, ou chaîne vide.
 *
 * @return string Balisage échappé.
 */
function fermeture_de_groupe( string $id, string $erreur ): string {
	$sortie = '';

	if ( '' !== $erreur ) {
		$sortie .= '<strong class="' . esc_attr( CROCHET . '__erreur' ) . '" id="' . esc_attr( $id . '-erreur' ) . '">'
			. esc_html( $erreur )
			. '</strong>';
	}

	return $sortie . '</div>';
}

/**
 * Groupe complet d'une saisie sur une ligne.
 *
 * « aria-invalid="true" » N'EST POSÉ QUE SUR UN CHAMP FAUTIF, jamais « ="false" » : l'absence de
 * l'attribut vaut « valide ». C'est lui qui déclenche le bord d'erreur déjà livré par « base.css ».
 *
 * @param string $cle          Clé du champ.
 * @param string $etiquette    Libellé visible.
 * @param string $valeur       Valeur à rappeler.
 * @param string $erreur       Message d'erreur, ou chaîne vide.
 * @param string $type         Type de la saisie.
 * @param string $autocomplete Jeton de remplissage automatique.
 * @param string $inputmode    Mode de saisie, ou chaîne vide.
 *
 * @return string Balisage échappé.
 */
function saisie_texte( string $cle, string $etiquette, string $valeur, string $erreur, string $type, string $autocomplete, string $inputmode ): string {
	$id = identifiant( $cle );

	/*
	 * Le courriel est le SEUL des trois champs à porter une note d'aide, et le contrat §9 n'en
	 * prévoit pas d'autre : la règle est écrite ici plutôt que passée en paramètre, pour qu'un
	 * appelant ne puisse pas inventer une note que personne n'a rédigée.
	 */
	$aide = 'courriel' === $cle ? AIDE_COURRIEL : '';

	$saisie = '<input class="' . esc_attr( CROCHET . '__saisie' ) . '"'
		. ' type="' . esc_attr( $type ) . '"'
		. ' id="' . esc_attr( $id ) . '"'
		. ' name="' . esc_attr( 'mtb_contact_' . $cle ) . '"'
		. ' value="' . esc_attr( $valeur ) . '"'
		. ' autocomplete="' . esc_attr( $autocomplete ) . '"';

	if ( '' !== $inputmode ) {
		$saisie .= ' inputmode="' . esc_attr( $inputmode ) . '"';
	}

	$saisie .= ' required';

	if ( '' !== $erreur ) {
		$saisie .= ' aria-invalid="true"';
	}

	$saisie .= decrit_par( $id, '' !== $erreur, '' !== $aide ) . '>';

	return ouverture_de_groupe( $id, $etiquette, $aide ) . $saisie . fermeture_de_groupe( $id, $erreur );
}

/**
 * Groupe complet de la zone de message.
 *
 * « esc_textarea() » ET NON « esc_html() » : seule la première échappe ce qu'il faut pour un contenu
 * de « textarea », où une fin de balise échappée à moitié fermerait l'élément et laisserait le
 * reste du message se lire comme du balisage.
 *
 * @param string $cle       Clé du champ.
 * @param string $etiquette Libellé visible.
 * @param string $valeur    Valeur à rappeler.
 * @param string $erreur    Message d'erreur, ou chaîne vide.
 *
 * @return string Balisage échappé.
 */
function saisie_zone( string $cle, string $etiquette, string $valeur, string $erreur ): string {
	$id = identifiant( $cle );

	$saisie = '<textarea class="' . esc_attr( CROCHET . '__saisie ' . CROCHET . '__zone' ) . '"'
		. ' id="' . esc_attr( $id ) . '"'
		. ' name="' . esc_attr( 'mtb_contact_' . $cle ) . '"'
		. ' rows="8" required';

	if ( '' !== $erreur ) {
		$saisie .= ' aria-invalid="true"';
	}

	$saisie .= decrit_par( $id, '' !== $erreur, false ) . '>' . esc_textarea( $valeur ) . '</textarea>';

	return ouverture_de_groupe( $id, $etiquette, '' ) . $saisie . fermeture_de_groupe( $id, $erreur );
}

/**
 * Le piège à robots.
 *
 * NOM DÉLIBÉRÉMENT HORS DES JETONS QUE LES NAVIGATEURS CIBLENT — ni « telephone », ni « site-web »,
 * ni « url », ni « adresse », ni « société », ni « code postal » : un champ qui porte un de ces mots
 * se fait remplir automatiquement, et un remplissage automatique coûterait ici un message humain
 * détruit.
 *
 * « aria-hidden="true" » sur le conteneur ET « tabindex="-1" » sur la saisie, ENSEMBLE : sans le
 * second, la règle « aria-hidden-focus » d'axe est violée — un élément focalisable caché aux
 * technologies d'assistance piège la navigation au clavier.
 *
 * L'étiquette existe pour le cas où la feuille de style ne serait pas chargée : le champ devient
 * alors visible, et une visiteuse doit lire ce qu'il attend d'elle. C'est le crochet « __piege »,
 * et lui seul, qui le masque — un crochet FONCTIONNEL, pas décoratif.
 *
 * La valeur n'est JAMAIS rappelée : un réaffichage repart d'un champ vide, pour que la visiteuse
 * dont le navigateur l'avait rempli ait une chance de passer au second essai.
 *
 * @return string Balisage échappé.
 */
function piege(): string {
	$id = identifiant( 'reference' );

	return '<div class="' . esc_attr( CROCHET . '__piege' ) . '" aria-hidden="true">'
		. '<label for="' . esc_attr( $id ) . '">' . esc_html( ETIQUETTE_PIEGE ) . '</label>'
		. '<input type="text" id="' . esc_attr( $id ) . '" name="mtb_contact_reference" value=""'
		. ' autocomplete="off" tabindex="-1">'
		. '</div>';
}
