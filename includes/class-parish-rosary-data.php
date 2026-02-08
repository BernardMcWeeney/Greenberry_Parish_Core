<?php
/**
 * Parish Rosary Data Loader
 *
 * Loads and provides access to rosary mysteries data from JSON file.
 *
 * @package ParishCore
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Parish Rosary Data class.
 */
class Parish_Rosary_Data {

	/**
	 * Cached mysteries data.
	 *
	 * @var array|null
	 */
	private static ?array $mysteries = null;

	/**
	 * Get all rosary mysteries data.
	 *
	 * @return array Array of all mystery sets with their data.
	 */
	public static function get_mysteries(): array {
		if ( null === self::$mysteries ) {
			$file = PARISH_CORE_PATH . 'includes/data/rosary-mysteries.json';

			if ( file_exists( $file ) ) {
				$json = file_get_contents( $file );
				self::$mysteries = json_decode( $json, true ) ?: array();
			} else {
				self::$mysteries = array();
			}
		}

		return self::$mysteries;
	}

	/**
	 * Get a specific mystery set (joyful, sorrowful, glorious, or luminous).
	 *
	 * @param string $set The mystery set to retrieve.
	 * @return array|null The mystery set data or null if not found.
	 */
	public static function get_mystery_set( string $set ): ?array {
		$mysteries = self::get_mysteries();
		return $mysteries[ $set ] ?? null;
	}

	/**
	 * Get a single mystery from a set.
	 *
	 * @param string $set    The mystery set (joyful, sorrowful, glorious, luminous).
	 * @param int    $number The mystery number (1-5).
	 * @return array|null The mystery data or null if not found.
	 */
	public static function get_single_mystery( string $set, int $number ): ?array {
		$mystery_set = self::get_mystery_set( $set );

		if ( ! $mystery_set ) {
			return null;
		}

		$mysteries = $mystery_set['mysteries'] ?? array();

		foreach ( $mysteries as $mystery ) {
			if ( $mystery['number'] === $number ) {
				return $mystery;
			}
		}

		return null;
	}

	/**
	 * Get all available mystery set keys.
	 *
	 * @return array Array of mystery set keys.
	 */
	public static function get_mystery_set_keys(): array {
		$mysteries = self::get_mysteries();
		return array_keys( $mysteries );
	}
}
