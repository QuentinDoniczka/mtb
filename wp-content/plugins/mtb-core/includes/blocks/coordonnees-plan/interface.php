<?php
/**
 * Composant « Coordonnées et plan d'accès » — balisage réutilisable hors d'un rendu de bloc.
 *
 * Espace de noms global, volontairement : le thème n'écrit jamais « MTB\ », c'est ce qui rend la
 * frontière thème / extension vérifiable d'un simple grep. Aucun hook n'est posé ici.
 *
 * ÉCART DÉCLARÉ AU CONTRAT §2, qui domicilie mtb_coordonnees_plan_rendu() dans « coordonnees.php ».
 * Elle vit ici parce que le §14 du même contrat (dette T19) exige que « coordonnees.php » reste
 * DÉPLAÇABLE TEL QUEL vers includes/query/ : une fonction de rendu y dépendrait de « rendu.php » et
 * l'en empêcherait. C'est aussi la convention du dépôt — voir fiche-information/interface.php.
 *
 * Le pied de page de l'epic Gabarits, et tout gabarit qui a besoin du même motif, passent par ici
 * plutôt que de réécrire un second balisage côté thème, qui dériverait de celui-ci au premier
 * ajustement. C'est aussi ce qui garantit qu'aucun numéro, aucune adresse et aucun courriel n'est
 * jamais écrit dans un fichier du thème.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'mtb_coordonnees_plan_rendu' ) ) {
	/**
	 * Rend les coordonnées de l'élevage, et le plan d'accès si un plan est fourni.
	 *
	 * Rend du HTML, donc pas de préfixe « mtb_get_ » : les fonctions « mtb_get_* » lisent une donnée,
	 * celle-ci compose un balisage. N'imprime rien, ne rend jamais l'état vide de l'éditeur.
	 *
	 * Appelée sans argument, elle rend les coordonnées de référence de l'élevage et aucun plan :
	 * c'est exactement ce dont un pied de page a besoin.
	 *
	 * Toute clé inconnue de $arguments est écartée, jamais rendue.
	 *
	 * @param array<string,mixed> $arguments {
	 *     Valeurs à rendre, toutes facultatives.
	 *
	 *     @type string   $adresse          Adresse postale, retours à la ligne admis.
	 *     @type string   $telephone        Numéro de téléphone, tel qu'il doit s'afficher.
	 *     @type string   $courriel         Adresse de courriel, telle qu'elle doit s'afficher.
	 *     @type int      $plan_id          Identifiant de la pièce jointe du plan, 0 pour aucun plan.
	 *     @type string   $plan_description Description de l'image du plan, vide si décorative.
	 *     @type string[] $classes          Classes ajoutées après « mtb-coordonnees-plan ».
	 * }
	 *
	 * @return string Balisage complet, racine comprise, ou chaîne vide si les trois coordonnées
	 *                sont vides.
	 */
	function mtb_coordonnees_plan_rendu( array $arguments = array() ): string {
		$reference = mtb_coordonnees_elevage();

		$defauts = array(
			'adresse'          => $reference['adresse'],
			'telephone'        => $reference['telephone'],
			'courriel'         => $reference['courriel'],
			'plan_id'          => 0,
			'plan_description' => '',
			'classes'          => array(),
		);

		/*
		 * array_intersect_key et non wp_parse_args : une clé inconnue est écartée ici même, donc
		 * elle ne peut atteindre aucune sortie. Chaque valeur reçue est ensuite ramenée au type
		 * attendu sans avertissement, un gabarit ne devant jamais faire tomber une page.
		 */
		$valeurs = array_merge( $defauts, array_intersect_key( $arguments, $defauts ) );

		$corps = \MTB\Core\Blocks\CoordonneesPlan\contenu(
			\MTB\Core\Blocks\CoordonneesPlan\texte_multiligne( $valeurs['adresse'] ),
			\MTB\Core\Blocks\CoordonneesPlan\texte_ligne( $valeurs['telephone'] ),
			\MTB\Core\Blocks\CoordonneesPlan\texte_ligne( $valeurs['courriel'] ),
			absint( is_scalar( $valeurs['plan_id'] ) ? $valeurs['plan_id'] : 0 ),
			\MTB\Core\Blocks\CoordonneesPlan\texte_ligne( $valeurs['plan_description'] ),
			(int) get_the_ID()
		);

		// Rien à afficher : aucun élément racine, pas même un conteneur vide.
		if ( '' === $corps ) {
			return '';
		}

		$classes = array( 'mtb-coordonnees-plan' );

		if ( is_array( $valeurs['classes'] ) ) {
			foreach ( $valeurs['classes'] as $classe ) {
				if ( is_string( $classe ) && '' !== $classe ) {
					$classes[] = $classe;
				}
			}
		}

		/*
		 * La racine est construite littéralement : get_block_wrapper_attributes() ne rendrait pas
		 * ici ce qu'on croit, un gabarit de thème n'étant pas dans un contexte de rendu de bloc.
		 */
		return sprintf(
			'<div class="%1$s">%2$s</div>',
			esc_attr( implode( ' ', $classes ) ),
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Étiquettes, valeurs, URI et légende échappées par rendu.php ; l'image est échappée par wp_get_attachment_image().
			$corps
		);
	}
}
