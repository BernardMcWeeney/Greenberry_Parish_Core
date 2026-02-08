<?php
/**
 * Parish Rosary Schedule
 *
 * Determines which rosary mystery set should be prayed based on the day of the week
 * and liturgical season, with special handling for Lent Sundays.
 *
 * @package ParishCore
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Parish Rosary Schedule class.
 */
class Parish_Rosary_Schedule {

	/**
	 * Standard weekly rosary schedule.
	 *
	 * @var array
	 */
	private const STANDARD_SCHEDULE = array(
		'monday'    => 'joyful',
		'tuesday'   => 'sorrowful',
		'wednesday' => 'glorious',
		'thursday'  => 'luminous',
		'friday'    => 'sorrowful',
		'saturday'  => 'joyful',
		'sunday'    => 'glorious',
	);

	/**
	 * Get today's recommended mystery set.
	 *
	 * @return string The mystery set key (joyful, sorrowful, glorious, luminous).
	 */
	public static function get_todays_mystery_set(): string {
		$day_of_week = strtolower( wp_date( 'l' ) );

		// Check if Lent and Sunday - Sorrowful mysteries are prayed on Sundays during Lent.
		if ( 'sunday' === $day_of_week && self::is_lent() ) {
			return 'sorrowful';
		}

		return self::STANDARD_SCHEDULE[ $day_of_week ] ?? 'joyful';
	}

	/**
	 * Check if currently in the Lenten season.
	 *
	 * @return bool True if in Lent, false otherwise.
	 */
	public static function is_lent(): bool {
		// Try Liturgy.day API first.
		$liturgical_data = self::fetch_liturgical_season();

		if ( $liturgical_data && isset( $liturgical_data['season'] ) ) {
			return 'lent' === strtolower( $liturgical_data['season'] );
		}

		// Fallback: calculate manually.
		return self::calculate_lent_manually();
	}

	/**
	 * Fetch liturgical season from Liturgy.day API.
	 *
	 * @return array|null Liturgical data array or null on failure.
	 */
	private static function fetch_liturgical_season(): ?array {
		$today     = wp_date( 'Y-m-d' );
		$cache_key = 'parish_liturgical_' . $today;

		$cached = get_transient( $cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

		$url      = "https://liturgy.day/api/day/{$today}";
		$response = wp_remote_get( $url, array( 'timeout' => 10 ) );

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( $data ) {
			set_transient( $cache_key, $data, DAY_IN_SECONDS );
			return $data;
		}

		return null;
	}

	/**
	 * Calculate if currently in Lent using manual calculation.
	 *
	 * @return bool True if in Lent, false otherwise.
	 */
	private static function calculate_lent_manually(): bool {
		$year           = (int) wp_date( 'Y' );
		$easter         = easter_date( $year );
		$ash_wednesday  = strtotime( '-46 days', $easter );
		$now            = time();

		return $now >= $ash_wednesday && $now < $easter;
	}

	/**
	 * Get the full week's rosary schedule.
	 *
	 * @return array Array of days with their mystery sets and data.
	 */
	public static function get_week_schedule(): array {
		$schedule = array();
		$is_lent  = self::is_lent();

		foreach ( self::STANDARD_SCHEDULE as $day => $mystery_set ) {
			// Override Sunday mystery set if in Lent.
			if ( 'sunday' === $day && $is_lent ) {
				$mystery_set = 'sorrowful';
			}

			$schedule[ $day ] = array(
				'mystery_set' => $mystery_set,
				'data'        => Parish_Rosary_Data::get_mystery_set( $mystery_set ),
			);
		}

		return $schedule;
	}

	/**
	 * Get mystery set for a specific date.
	 *
	 * @param string $date Date in Y-m-d format.
	 * @return string The mystery set key.
	 */
	public static function get_mystery_set_for_date( string $date ): string {
		$timestamp   = strtotime( $date );
		$day_of_week = strtolower( gmdate( 'l', $timestamp ) );

		// Check if Lent and Sunday.
		if ( 'sunday' === $day_of_week ) {
			$year           = (int) gmdate( 'Y', $timestamp );
			$easter         = easter_date( $year );
			$ash_wednesday  = strtotime( '-46 days', $easter );

			if ( $timestamp >= $ash_wednesday && $timestamp < $easter ) {
				return 'sorrowful';
			}
		}

		return self::STANDARD_SCHEDULE[ $day_of_week ] ?? 'joyful';
	}
}
