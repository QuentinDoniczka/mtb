<?php
/**
 * Avis affichés à l'éleveuse après l'enregistrement d'une fiche Chien.
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Fields\Chien;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Collecte les avis pendant la sauvegarde, pour qu'ils survivent à la redirection.
 *
 * La sauvegarde et l'affichage ont lieu dans deux requêtes différentes : WordPress redirige après
 * l'enregistrement. Les codes collectés ici sont donc passés en paramètre d'adresse, puis relus et
 * traduits par la liste fermée ci-dessous — aucun texte ne transite par l'adresse, et un code
 * inconnu n'affiche rien.
 */
final class Avis {

	/**
	 * Codes collectés pendant la requête d'enregistrement.
	 *
	 * @var string[]
	 */
	private static array $codes = array();

	/**
	 * Saisies refusées à citer dans un avis, par nom de paramètre d'adresse.
	 *
	 * @var array<string, string>
	 */
	private static array $precisions = array();

	/**
	 * Classe purement statique : aucune instance n'a de sens.
	 */
	private function __construct() {}

	/**
	 * Retient un avis, sans doublon.
	 *
	 * @param string $code Code de la liste fermée de messages().
	 */
	public static function ajouter( string $code ): void {
		if ( ! in_array( $code, self::$codes, true ) ) {
			self::$codes[] = $code;
		}
	}

	/**
	 * Codes collectés pendant cette requête.
	 *
	 * @return string[]
	 */
	public static function codes(): array {
		return self::$codes;
	}

	/**
	 * Retient une saisie refusée, pour la citer telle quelle dans l'avis.
	 *
	 * Lui montrer ce qu'elle a tapé lui évite de chercher laquelle des deux dates a été refusée.
	 *
	 * @param string $parametre Nom du paramètre d'adresse qui la transporte.
	 * @param string $saisie    Valeur refusée, telle qu'elle a été tapée.
	 */
	public static function preciser( string $parametre, string $saisie ): void {
		self::$precisions[ $parametre ] = $saisie;
	}

	/**
	 * Saisies refusées pendant cette requête.
	 *
	 * @return array<string, string>
	 */
	public static function precisions(): array {
		return self::$precisions;
	}

	/**
	 * Liste fermée des avis : code => type d'encart et phrase française.
	 *
	 * @return array<string, array<string, string>>
	 */
	public static function messages(): array {
		return array(
			'statut_vide'           => array(
				'type'  => 'warning',
				'texte' => 'Ce chien est enregistré sans statut : il n\'apparaîtra pas sur la page « La meute ». Vous pouvez renseigner le champ Statut quand vous le souhaitez.',
			),
			'sexe_refuse'           => array(
				'type'  => 'error',
				'texte' => 'Erreur : le sexe reçu ne fait pas partie des choix proposés. Le champ Sexe a été laissé vide.',
			),
			'variete_refusee'       => array(
				'type'  => 'error',
				'texte' => 'Erreur : la variété reçue ne fait pas partie des choix proposés. Le champ Variété a été laissé vide.',
			),
			'statut_refuse'         => array(
				'type'  => 'error',
				'texte' => 'Erreur : le statut reçu ne fait pas partie des choix proposés. Le champ Statut a été laissé vide.',
			),
			'adn_refuse'            => array(
				'type'  => 'error',
				'texte' => 'Erreur : la réponse reçue pour « ADN identifié » ne fait pas partie des choix proposés. Le champ a été laissé vide.',
			),
			'cadrage_refuse'        => array(
				'type'  => 'error',
				'texte' => 'Erreur : le cadrage reçu ne fait pas partie des choix proposés. Le cadrage centré a été conservé.',
			),
			/*
			 * Une saisie refusée n'efface jamais la date déjà enregistrée, et l'avis cite ce qui a
			 * été tapé : l'ancien message annonçait le contraire et indiquait un format que
			 * l'assainissement refusait, ce qui enfermait l'éleveuse dans une boucle.
			 */
			'naissance_refusee'     => array(
				'type'      => 'error',
				'texte'     => "Erreur : la date de naissance n'a pas été comprise. La date déjà enregistrée a été conservée. Écrivez la date en entier, sous la forme jj/mm/aaaa.",
				'modele'    => "Erreur : la date de naissance « %s » n'a pas été comprise. La date déjà enregistrée a été conservée. Écrivez la date en entier, sous la forme jj/mm/aaaa.",
				'parametre' => 'mtb_chien_naissance',
			),
			'deces_refuse'          => array(
				'type'      => 'error',
				'texte'     => "Erreur : la date de décès n'a pas été comprise. La date déjà enregistrée a été conservée. Écrivez la date en entier, sous la forme jj/mm/aaaa.",
				'modele'    => "Erreur : la date de décès « %s » n'a pas été comprise. La date déjà enregistrée a été conservée. Écrivez la date en entier, sous la forme jj/mm/aaaa.",
				'parametre' => 'mtb_chien_deces',
			),
			'deces_avant_naissance' => array(
				'type'  => 'error',
				'texte' => 'Erreur : la date de décès est antérieure à la date de naissance. Les deux dates ont été enregistrées telles que vous les avez saisies ; corrigez celle qui ne convient pas.',
			),
			'pere_soi_meme'         => array(
				'type'  => 'error',
				'texte' => 'Erreur : un chien ne peut pas être son propre père. Le champ Père a été laissé vide.',
			),
			'mere_soi_meme'         => array(
				'type'  => 'error',
				'texte' => 'Erreur : un chien ne peut pas être sa propre mère. Le champ Mère a été laissé vide.',
			),
			'pere_introuvable'      => array(
				'type'  => 'error',
				'texte' => 'Erreur : la fiche choisie comme père n\'existe plus. Le champ Père a été laissé vide ; vous pouvez saisir son nom et son élevage à la place.',
			),
			'mere_introuvable'      => array(
				'type'  => 'error',
				'texte' => 'Erreur : la fiche choisie comme mère n\'existe plus. Le champ Mère a été laissé vide ; vous pouvez saisir son nom et son élevage à la place.',
			),
			'pedigree_refuse'       => array(
				'type'  => 'error',
				'texte' => 'Erreur : le lien pedigree n\'a pas été reconnu comme une adresse Internet. Le champ a été laissé vide ; copiez l\'adresse complète depuis la barre du navigateur.',
			),
		);
	}
}
