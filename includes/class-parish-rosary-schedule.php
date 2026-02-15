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
		// Use local calculation (reliable, no external API dependency).
		return self::calculate_lent_manually();
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

	/**
	 * Get mystery set for a specific day of week (for testing/preview).
	 *
	 * @param string $day Day name (mon, tue, wed, thu, fri, sat, sun or full names).
	 * @return string The mystery set key.
	 */
	public static function get_mystery_set_for_day( string $day ): string {
		// Normalize day name.
		$day_map = array(
			'mon' => 'monday',
			'tue' => 'tuesday',
			'wed' => 'wednesday',
			'thu' => 'thursday',
			'fri' => 'friday',
			'sat' => 'saturday',
			'sun' => 'sunday',
		);

		$day = strtolower( $day );
		$day = $day_map[ $day ] ?? $day;

		// Check if Sunday and currently in Lent.
		if ( 'sunday' === $day && self::is_lent() ) {
			return 'sorrowful';
		}

		return self::STANDARD_SCHEDULE[ $day ] ?? 'joyful';
	}
}
