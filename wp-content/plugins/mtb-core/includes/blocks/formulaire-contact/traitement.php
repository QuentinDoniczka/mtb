<?php
/**
 * Composant « Formulaire de contact » — traitement de la soumission, sur « template_redirect ».
 *
 * Fichier inclus UNE SEULE FOIS, par « bootstrap.php ».
 *
 * AMENDEMENT DÉCLARÉ AU CONTRAT #1 §2, qui impose « init 20 » aux modules du groupe « blocks » :
 * l'enregistrement du bloc reste sur « init 20 » ; ce traitement est un hook SUPPLÉMENTAIRE, le
 * seul du groupe. Écart assumé et écrit, non fait en douce (décision 46).
 *
 * PRIORITÉ 1 SUR « template_redirect », et c'est le seul point qui compte dans le choix du hook :
 * aucun autre écouteur ne doit pouvoir transformer le POST en redirection avant que le message ne
 * parte. « template_redirect » est aussi le premier moment où « is_singular() »,
 * « get_queried_object() » et « post_password_required() » disent la vérité, et où il reste
 * possible d'émettre un en-tête « Location: ».
 *
 * RIEN N'EST ÉCRIT EN BASE, nulle part dans ce fichier : ni option, ni méta, ni transient, ni
 * cookie, ni session, ni fichier journal (décision 45). Le message existe en mémoire PHP le temps
 * d'une requête, puis nulle part. Corollaire assumé : un courriel perdu est perdu.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Blocks\FormulaireContact;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Plafonds serveur. Ils REFUSENT avec une phrase, ils ne rognent jamais une saisie en silence. */
const PLAFOND_NOM      = 200;
const PLAFOND_COURRIEL = 254;
const PLAFOND_MESSAGE  = 20000;

/** Nom du paramètre d'URL qui marque une confirmation. Préfixé : aucune collision avec le cœur. */
const MARQUEUR = 'mtb_contact';

/**
 * Retrouve la page rendue quand elle porte le composant, ou rien.
 *
 * Gardes 2 à 6 du contrat #22 §5.1, dans cet ordre. Elles valent aussi bien pour la soumission que
 * pour la requête de confirmation, d'où leur mise en commun.
 *
 * « has_block() » reçoit l'objet WP_Post EXPLICITEMENT, jamais null : le repli sur
 * « $GLOBALS['post'] » fonctionne aujourd'hui mais serait une dépendance implicite à une variable
 * globale, que le contrat #1 §5 interdit.
 *
 * @return \WP_Post|null Page portant le composant, ou null.
 */
function page_du_bloc(): ?\WP_Post {
	// Ceinture : « template_redirect » ne court pas dans l'administration, mais un chargement
	// d'administration qui simulerait le front ne doit pas envoyer de courriel.
	if ( is_admin() ) {
		return null;
	}

	if ( ! is_singular() ) {
		return null;
	}

	$page = get_queried_object();

	if ( ! $page instanceof \WP_Post ) {
		return null;
	}

	/*
	 * Page protégée par mot de passe et mot de passe non saisi : le cœur ne rend pas le contenu,
	 * donc le formulaire n'a jamais été affiché. Accepter le POST ferait fuir, par le courriel,
	 * l'existence d'un contenu réservé — et c'est exactement ce que la protection interdit.
	 */
	if ( post_password_required( $page ) ) {
		return null;
	}

	if ( ! has_block( 'mtb/formulaire-contact', $page ) ) {
		return null;
	}

	return $page;
}

/**
 * Dit si la requête courante est une soumission à traiter, et sur quelle page.
 *
 * Garde 1 du contrat §5.1 — la méthode — puis les gardes 2 à 6.
 *
 * @return \WP_Post|null Page portant le composant sur un POST, ou null.
 */
function doit_traiter(): ?\WP_Post {
	if ( 'POST' !== strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
		return null;
	}

	return page_du_bloc();
}

/**
 * Rappel unique de « template_redirect », priorité 1.
 */
function traiter(): void {
	$page = doit_traiter();

	if ( null === $page ) {
		/*
		 * Ce n'est pas une soumission. Reste le cas de la requête GET de confirmation, qui n'a rien
		 * à traiter mais dont les en-têtes doivent interdire la mise en cache et l'indexation.
		 */
		preparer_confirmation();

		return;
	}

	/*
	 * CAS 7 BIS — LE CORPS DE REQUÊTE PERDU. Méthode POST, « $_POST » vide et un « Content-Length »
	 * non nul : PHP a jeté le corps, faute de place (« post_max_size »). Sans ce cas, la visiteuse
	 * recevrait un formulaire vierge et SON TEXTE AURAIT DISPARU SANS UN MOT. Six lignes, et c'est
	 * un refus muet de moins.
	 */
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Aucune valeur n'est lue ici : on ne teste que la vacuité du tableau.
	if ( ! isset( $_POST['mtb_contact_action'] ) ) {
		$longueur = isset( $_SERVER['CONTENT_LENGTH'] ) ? (int) $_SERVER['CONTENT_LENGTH'] : 0;

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Idem : test de vacuité, aucune valeur lue.
		if ( array() === $_POST && $longueur > 0 ) {
			entetes_sans_cache();
			Etat::poser( resultat( $page->ID, 'corps_perdu', valeurs_vides() ) );
		}

		return;
	}

	$valeurs = valeurs_postees();

	/*
	 * LE PIÈGE D'ABORD, avant le jeton et avant la validation. Une visiteuse dont le navigateur a
	 * rempli le champ automatiquement doit voir tout de suite l'encart de recours, plutôt qu'une
	 * liste de fautes à corriger qui la ramènerait au même refus. Le texte affiché NE NOMME PAS LE
	 * PIÈGE : un robot n'apprend pas quel champ l'a trahi.
	 */
	if ( '' !== lire_champ_poste( 'mtb_contact_reference', false ) ) {
		entetes_sans_cache();
		Etat::poser( resultat( $page->ID, 'piege', $valeurs ) );

		return;
	}

	/*
	 * LA DESTINATION AVANT LA VALIDATION. Sans adresse d'arrivée, aucune correction de champ ne
	 * ferait partir le message : demander à la visiteuse de corriger son courriel serait lui faire
	 * perdre son temps. C'est la seule situation où le composant s'affiche alors que la destination
	 * est inutilisable — exception déclarée à la décision 26, motivée par la dette T26 : une
	 * visiteuse qui vient de cliquer ne doit jamais recevoir le silence.
	 */
	$adresse = destination();

	if ( '' === $adresse ) {
		entetes_sans_cache();
		Etat::poser( resultat( $page->ID, 'destination_absente', $valeurs ) );

		return;
	}

	$globales = array();
	$echec    = jeton_verifier( lire_champ_poste( 'mtb_contact_jeton', false ) );

	if ( '' !== $echec ) {
		$globales[] = message_de_jeton( $echec );
	}

	$champs = valider( $valeurs, $adresse );

	if ( array() !== $globales || array() !== $champs ) {
		entetes_sans_cache();
		Etat::poser( resultat( $page->ID, 'erreurs', $valeurs, $globales, $champs ) );

		return;
	}

	if ( ! envoyer( $valeurs, $adresse, $page ) ) {
		entetes_sans_cache();
		Etat::poser( resultat( $page->ID, 'envoi_echoue', $valeurs ) );

		return;
	}

	rediriger_vers_la_confirmation( $page );
}

/**
 * Pose les en-têtes de la requête GET de confirmation.
 *
 * ÉCART DÉCLARÉ À L'ORDRE DES GARDES DU §5.1 : la garde 1 y « rend la main » sur une requête qui
 * n'est pas un POST. Elle rend bien la main au traitement — aucun courriel ne peut partir d'ici —
 * mais la réponse de confirmation a ses propres en-têtes à poser, et « template_redirect » est le
 * dernier moment où c'est possible. Toutes les autres gardes sont re-jouées telles quelles.
 */
function preparer_confirmation(): void {
	$methode = strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) );

	if ( 'GET' !== $methode && 'HEAD' !== $methode ) {
		return;
	}

	if ( ! marqueur_de_confirmation_present() ) {
		return;
	}

	if ( null === page_du_bloc() ) {
		return;
	}

	entetes_sans_cache();
}

/**
 * Dit si l'URL courante porte le marqueur de confirmation.
 *
 * AVEU ÉCRIT : « ?mtb_contact=envoye » se tape à la main. LA CONFIRMATION EST UN ÉCHO D'URL, PAS
 * UNE PREUVE D'ENVOI. C'est sans conséquence — rien n'est révélé, rien n'est déclenché — et c'est
 * inévitable dès lors que rien n'est stocké.
 *
 * @return bool Vrai quand le marqueur est présent et vaut « envoye ».
 */
function marqueur_de_confirmation_present(): bool {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Lecture d'un marqueur d'affichage sur une requête GET publique : rien n'est écrit, rien n'est révélé.
	if ( ! isset( $_GET[ MARQUEUR ] ) ) {
		return false;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- assainir_ligne() est l'assainisseur du module ; la valeur n'est que comparée à une constante.
	return 'envoye' === assainir_ligne( wp_unslash( $_GET[ MARQUEUR ] ) );
}

/**
 * Interdit la mise en cache et l'indexation de la réponse.
 *
 * Posé sur la réponse de confirmation ET sur toute réponse POST en erreur : une page qui rappelle
 * le nom, l'adresse et le message d'une visiteuse ne doit être conservée par aucun intermédiaire,
 * ni figurer dans un index.
 */
function entetes_sans_cache(): void {
	// « noindex, nofollow » sur la page : la balise est écrite dans le « head » par le cœur.
	add_filter( 'wp_robots', 'wp_robots_no_robots' );

	// Une sortie déjà commencée rendrait l'appel bruyant sans rien poser d'utile.
	if ( headers_sent() ) {
		return;
	}

	nocache_headers();
}

/**
 * Valeurs de départ, toujours les trois clés, toujours des chaînes.
 *
 * @return array{nom: string, courriel: string, message: string}
 */
function valeurs_vides(): array {
	return array(
		'nom'      => '',
		'courriel' => '',
		'message'  => '',
	);
}

/**
 * Relit les trois valeurs postées et les recopie proprement.
 *
 * @return array{nom: string, courriel: string, message: string}
 */
function valeurs_postees(): array {
	return array(
		'nom'      => lire_champ_poste( 'mtb_contact_nom', false ),
		'courriel' => lire_champ_poste( 'mtb_contact_courriel', false ),
		'message'  => lire_champ_poste( 'mtb_contact_message', true ),
	);
}

/**
 * Traduit un code d'échec de jeton en phrase française.
 *
 * @param string $echec Code rendu par « jeton_verifier() ».
 *
 * @return string Phrase à afficher, toujours récupérable en un clic.
 */
function message_de_jeton( string $echec ): string {
	if ( 'vieux' === $echec ) {
		return ERREUR_JETON_VIEUX;
	}

	if ( 'rapide' === $echec ) {
		return ERREUR_JETON_RAPIDE;
	}

	return ERREUR_JETON_INVALIDE;
}

/**
 * Construit un résultat complet, toutes clés présentes.
 *
 * Toutes les clés sont toujours là : le rendu n'a donc jamais à tester l'existence de l'une
 * d'elles, et une clé oubliée ne peut pas se traduire en avertissement PHP sur une page publique.
 *
 * @param int                                                   $post_id  Page sur laquelle le POST a été reçu.
 * @param string                                                $issue    Nature de l'issue.
 * @param array{nom: string, courriel: string, message: string} $valeurs  Valeurs à rappeler.
 * @param string[]                                              $globales Erreurs sans champ désigné.
 * @param array<string, string>                                 $champs   Erreurs par champ.
 *
 * @return array<string, mixed>
 */
function resultat( int $post_id, string $issue, array $valeurs, array $globales = array(), array $champs = array() ): array {
	return array(
		'post_id'  => $post_id,
		'issue'    => $issue,
		'globales' => $globales,
		'champs'   => $champs,
		'valeurs'  => $valeurs,
	);
}

/**
 * Valide les trois valeurs et rend les erreurs, champ par champ.
 *
 * Les plafonds REFUSENT, ils ne rognent pas : tronquer un collage en silence ferait partir un
 * message amputé sans que personne ne le sache. C'est aussi pourquoi le balisage ne porte aucun
 * « maxlength ».
 *
 * Sur le courriel, la longueur est jugée AVANT la validité : une adresse de 300 caractères est
 * refusée par « is_email() » de toute façon, et « votre adresse dépasse 254 caractères » dit
 * quelque chose d'actionnable là où « n'est pas valide » laisserait chercher.
 *
 * @param array{nom: string, courriel: string, message: string} $valeurs Valeurs assainies.
 * @param string                                                $adresse Destination, pour la phrase du message trop long.
 *
 * @return array<string, string> Erreurs par champ ; une clé absente signale un champ non fautif.
 */
function valider( array $valeurs, string $adresse ): array {
	$champs = array();

	if ( '' === $valeurs['nom'] ) {
		$champs['nom'] = ERREUR_NOM_VIDE;
	} elseif ( mb_strlen( $valeurs['nom'] ) > PLAFOND_NOM ) {
		$champs['nom'] = ERREUR_NOM_TROP_LONG;
	}

	if ( '' === $valeurs['courriel'] ) {
		$champs['courriel'] = ERREUR_COURRIEL_VIDE;
	} elseif ( mb_strlen( $valeurs['courriel'] ) > PLAFOND_COURRIEL ) {
		$champs['courriel'] = ERREUR_COURRIEL_LONG;
	} elseif ( ! is_email( $valeurs['courriel'] ) ) {
		$champs['courriel'] = ERREUR_COURRIEL_INVALIDE;
	}

	if ( '' === $valeurs['message'] ) {
		$champs['message'] = ERREUR_MESSAGE_VIDE;
	} elseif ( mb_strlen( $valeurs['message'] ) > PLAFOND_MESSAGE ) {
		$champs['message'] = erreur_message_trop_long( $adresse );
	}

	return $champs;
}

/**
 * Envoie le message à l'élevage.
 *
 * L'INJECTION D'EN-TÊTE EST LA PRIORITÉ DE SÉCURITÉ DE CE MODULE, et elle est fermée en amont :
 *
 * - « Reply-To » : l'adresse a traversé « assainir_ligne() », qui convertit CR et LF en espace
 *   AVANT tout autre traitement, puis « is_email() ». L'en-tête est NU — « Reply-To: adresse » —
 *   et jamais « Nom <adresse> » : un nom entre chevrons rouvrirait une surface de composition.
 * - « From » : ON N'Y TOUCHE PAS. WordPress pose « wordpress@<domaine> », seule adresse
 *   susceptible de passer SPF. Aucun filtre « wp_mail_from » ni « wp_mail_from_name » n'est posé :
 *   ils affecteraient TOUT le courrier du site, réinitialisations de mot de passe comprises.
 * - « Subject » : contient le nom, après « assainir_ligne() » — CR et LF impossibles par
 *   construction — et plafonné.
 * - « $to » : « is_email() » une seconde fois, juste avant l'appel.
 * - Corps : « text/plain; charset=UTF-8 » posé explicitement. Aucun HTML, donc aucun « < » ne
 *   disparaît et rien n'est interprété.
 *
 * @param array{nom: string, courriel: string, message: string} $valeurs Valeurs assainies et validées.
 * @param string                                                $adresse Destination.
 * @param \WP_Post                                              $page    Page d'où part le message.
 *
 * @return bool Vrai si le courriel a été remis au transport.
 */
function envoyer( array $valeurs, string $adresse, \WP_Post $page ): bool {
	// Ceinture : une adresse non valide bascule en « destination inutilisable », jamais en
	// formulaire qui échoue toujours sans que personne ne sache pourquoi.
	if ( ! is_email( $adresse ) ) {
		return false;
	}

	$entetes = array( 'Content-Type: text/plain; charset=UTF-8' );

	if ( is_email( $valeurs['courriel'] ) ) {
		$entetes[] = 'Reply-To: ' . $valeurs['courriel'];
	}

	$origine = (string) get_permalink( $page );

	return (bool) wp_mail(
		$adresse,
		sujet_courriel( $valeurs['nom'] ),
		corps_courriel( $valeurs, $origine, horodatage_lisible() ),
		$entetes
	);
}

/**
 * Redirige vers la confirmation, en 303.
 *
 * 303 et non 302 : c'est le code qui dit explicitement « refaites la requête en GET ». Il ferme le
 * réenvoi du formulaire à la touche F5, seule protection sans JavaScript contre un double envoi.
 *
 * L'URL ne porte AUCUNE donnée personnelle : un marqueur préfixé, et l'ancre du composant.
 *
 * @param \WP_Post $page Page à laquelle revenir.
 */
function rediriger_vers_la_confirmation( \WP_Post $page ): void {
	$permalien = (string) get_permalink( $page );

	// Repli théorique : une page publiée et interrogée a toujours un permalien.
	if ( '' === $permalien ) {
		$permalien = home_url( '/' );
	}

	/*
	 * L'ancre vient de « ANCRE » (rendu.php) et n'est pas recopiée : c'est le rendu qui la pose sur
	 * l'enveloppe, et deux littéraux dans deux fichiers divergeraient un jour EN SILENCE — le 303
	 * atterrirait sur une ancre qui n'existe plus, sans erreur ni journal.
	 */
	wp_safe_redirect(
		add_query_arg( MARQUEUR, 'envoye', $permalien ) . '#' . ANCRE,
		303
	);

	exit;
}
