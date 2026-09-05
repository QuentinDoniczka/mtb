<?php
/**
 * Service des 46 redirections permanentes, sur « template_redirect » priorité 1.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Migration\Redirections301;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * POURQUOI LA PRIORITÉ 1 SUFFIT, ET POURQUOI CE HOOK.
 *
 * « template-loader.php:23 » déclenche « do_action( 'template_redirect' ) » INCONDITIONNELLEMENT
 * dès que « wp_using_themes() », y compris sur un 404 : les 46 anciennes adresses, qui ne
 * correspondent à aucun contenu, y passent bien. « redirect_canonical » et
 * « wp_old_slug_redirect » sont en priorité 10 (« default-filters.php:666 » et « :471 ») : la
 * priorité 1 les pré-empte, « redirect_guess_404_permalink() » compris, donc aucune devinette du
 * cœur ne s'interpose entre l'ancienne adresse et la nôtre.
 *
 * Une règle de réécriture traduit une URL en REQUÊTE ; une 301 est une RÉPONSE. Ce module n'en pose
 * donc aucune, ne régénère rien, ne lit aucune option et ne dépend d'aucun état en base pour se
 * déclencher : il fonctionne à la seconde où le dossier arrive par FTP.
 *
 * INTERACTION CONNUE : « blocks/formulaire-contact/bootstrap.php:69 » accroche déjà
 * « template_redirect » en priorité 1. À priorité égale l'ordre est celui de l'enregistrement, donc
 * du parcours du chargeur — « blocks » (4ᵉ) avant « migration » (6ᵉ). Sans conséquence : ce
 * rappel-là ne traite que des POST, que la garde 2 ci-dessous écarte, et aucune des 52 adresses
 * n'est « /contact/ » en POST. Aucun des deux modules ne dépend de cet ordre.
 */

/**
 * Sert la redirection permanente d'une des 46 anciennes adresses, ou ne fait rien.
 *
 * Les dix gardes ci-dessous sont dans l'ordre imposé par le contrat #24 §5. Trois ne se négocient
 * pas : l'anti-boucle, le refus de rediriger vers une cible non résolue, et le fait que la
 * comparaison porte sur le chemin seul.
 */
function rediriger(): void {
	// 1. Contextes où une redirection de front n'a rien à faire.
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}

	// 2. Une 301 sur un POST perd le corps de la requête : on laisse passer.
	$methode = isset( $_SERVER['REQUEST_METHOD'] )
		? strtoupper( sanitize_key( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) )
		: '';

	if ( 'GET' !== $methode && 'HEAD' !== $methode ) {
		return;
	}

	/*
	 * 3. Normalisation. « REQUEST_URI » n'est PAS passée à « sanitize_text_field() » : elle
	 * détruirait le chemin, et notamment les 29 adresses accentuées. Le décodage, le rejet de
	 * l'octet nul et le retrait du préfixe du site vivent dans « normaliser_chemin() », dont le
	 * résultat ne sert QU'À une lecture de clé dans un tableau constant.
	 */
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Un chemin d'URL ne se nettoie pas comme du texte : voir « chemin.php ».
	$brut = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '';

	$demande = normaliser_chemin( $brut );

	if ( '' === $demande ) {
		return;
	}

	$carte = carte();

	// 4. Hors carte : la requête suit son cours normal. Un « isset() » sur 52 entrées, c'est le coût
	// que ce module fait payer à toutes les autres requêtes du site.
	if ( ! isset( $carte[ $demande ] ) ) {
		return;
	}

	$entree = $carte[ $demande ];

	// 5. C'est ici que les six adresses identiques sortent — par conception, écrite dans la donnée.
	if ( '301' !== $entree['verdict'] ) {
		return;
	}

	// 6. Résolution de l'identité en permalien servi aujourd'hui.
	$resolution = resoudre_cible( is_array( $entree['cible'] ) ? $entree['cible'] : array() );
	$cible      = is_string( $resolution['url'] ) ? $resolution['url'] : '';

	/*
	 * 7. CIBLE NON RÉSOLUE ⇒ AUCUNE REDIRECTION. Une 301 vers un 404 déplace la rupture au lieu de
	 * la supprimer. On laisse le gabarit 404 du thème rendre ses liens de recours (D12), et la
	 * commande de vérification signale l'identité en échec.
	 */
	if ( '' === $cible ) {
		return;
	}

	/*
	 * 8. Garde d'hôte. Sans elle, « wp_safe_redirect() » passerait par « wp_validate_redirect() »,
	 * qui remplace une destination externe par « wp-admin » — le visiteur d'une ancienne adresse
	 * publique atterrirait sur un écran de connexion, SILENCIEUSEMENT.
	 */
	$hote_cible = hote_de( $cible );
	$hote_site  = hote_de( home_url( '/' ) );

	if ( '' === $hote_cible || '' === $hote_site || $hote_cible !== $hote_site ) {
		return;
	}

	// 9. Anti-boucle : même fonction de normalisation des deux côtés, jamais une comparaison de
	// chaînes brutes qui laisserait passer « /a/ » contre « /a » ou une forme percent-encodée.
	if ( normaliser_chemin( $cible ) === $demande ) {
		return;
	}

	/*
	 * 10. La chaîne de requête n'est pas reportée sur la cible. Écart mineur, assumé : aucun
	 * paramètre n'a de sens sur ce site (zéro traceur, D6), et reporter une chaîne arbitraire venue
	 * d'un domaine tiers sur une adresse interne serait une surface d'attaque gratuite.
	 */
	wp_safe_redirect( $cible, 301 );

	exit;
}
