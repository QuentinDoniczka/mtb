<?php
/**
 * Composant « Formulaire de contact » — le passage du résultat de la requête au rendu.
 *
 * Fichier inclus UNE SEULE FOIS, par « bootstrap.php ».
 *
 * POURQUOI UNE CLASSE STATIQUE ET NON UNE VARIABLE GLOBALE. Le traitement s'exécute sur
 * « template_redirect », bien avant que le cœur ne rende les blocs de la page : il faut donc porter
 * un résultat d'un moment à l'autre de la même requête. Le contrat #1 §5 interdit toute variable
 * globale nouvelle ; la forme retenue est celle de « MTB\Core\Loader » — classe finale, entièrement
 * statique, constructeur privé, donc non instanciable et sans état à sérialiser.
 *
 * RIEN N'EST ÉCRIT NULLE PART. Le résultat vit en mémoire PHP le temps d'une requête, puis nulle
 * part : ni transient — un transient est une écriture en base —, ni cookie, ni session, ni fichier
 * journal (décision 45).
 *
 * @package MTB\Core
 */

declare(strict_types=1);

namespace MTB\Core\Blocks\FormulaireContact;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Résultat de la soumission en cours, et rang de l'instance rendue.
 */
final class Etat {

	/**
	 * Résultat posé par « traiter() », ou null quand aucune soumission n'a eu lieu.
	 *
	 * Forme, clés TOUJOURS toutes présentes :
	 *   'post_id'  int
	 *   'issue'    'erreurs'|'piege'|'envoi_echoue'|'destination_absente'|'corps_perdu'
	 *   'globales' string[]
	 *   'champs'   array<'nom'|'courriel'|'message', string>  — clé absente = champ non fautif
	 *   'valeurs'  array{nom: string, courriel: string, message: string} — jamais absentes
	 *
	 * @var array<string, mixed>|null
	 */
	private static ?array $resultat = null;

	/**
	 * Nombre d'instances du bloc déjà rendues dans cette requête.
	 *
	 * @var int
	 */
	private static int $instances = 0;

	/**
	 * Classe purement statique : aucune instance n'a de sens.
	 */
	private function __construct() {}

	/**
	 * Pose le résultat de la soumission. Appelé une seule fois, par « traiter() ».
	 *
	 * @param array<string, mixed> $resultat Résultat complet, toutes clés présentes.
	 */
	public static function poser( array $resultat ): void {
		self::$resultat = $resultat;
	}

	/**
	 * Rend le résultat SANS LE CONSOMMER.
	 *
	 * Ne pas le consommer est délibéré : le rang d'instance, et lui seul, décide quelle instance
	 * l'affiche. Un « lire une fois puis oublier » ferait dépendre le rendu de l'ordre d'appel, donc
	 * de l'ordre des blocs dans la page — une dépendance invisible et impossible à déboguer.
	 *
	 * @return array<string, mixed>|null Résultat, ou null si aucune soumission n'a eu lieu.
	 */
	public static function resultat(): ?array {
		return self::$resultat;
	}

	/**
	 * Incrémente le compteur et rend le rang de l'instance qui se rend.
	 *
	 * Le contrat #22 §5.7 fixe que tout rang différent de 1 ne rend RIEN. Deux formulaires postant
	 * sur la même adresse sont indiscernables du serveur — il ne saurait pas lequel a été soumis —
	 * et les neuf identifiants du balisage seraient dupliqués dans le document, ce qui casse à la
	 * fois les « for » des étiquettes et les liens du résumé d'erreurs.
	 *
	 * @return int Rang de l'instance, à partir de 1.
	 */
	public static function prochaine_instance(): int {
		++self::$instances;

		return self::$instances;
	}
}
