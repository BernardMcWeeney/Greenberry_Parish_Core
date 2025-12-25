<?php
/**
 * Recurrence utilities for Parish Core schedule system.
 *
 * Handles all recurrence pattern calculations including:
 * - Daily, weekly, bi-weekly, monthly patterns
 * - Positional patterns (First Friday, Last Sunday)
 * - Annual fixed dates
 * - Moveable feasts (Easter-relative dates)
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Recurrence calculation utilities.
 */
class Parish_Recurrence {

	/**
	 * Day name to number mapping (ISO-8601: Monday = 1, Sunday = 7).
	 */
	private const DAY_MAP = array(
		'Monday'    => 1,
		'Tuesday'   => 2,
		'Wednesday' => 3,
		'Thursday'  => 4,
		'Friday'    => 5,
		'Saturday'  => 6,
		'Sunday'    => 7,
	);

	/**
	 * Position name to week number mapping.
	 */
	private const POSITION_MAP = array(
		'first'  => 1,
		'second' => 2,
		'third'  => 3,
		'fourth' => 4,
		'fifth'  => 5,
		'last'   => -1,
	);

	/**
	 * Get Easter date for a given year.
	 *
	 * Uses PHP's easter_days() function for accurate calculation.
	 *
	 * @param int $year The year.
	 * @return DateTime Easter Sunday date.
	 */
	public static function get_easter( int $year ): DateTime {
		$base = new DateTime( "{$year}-03-21" );
		$base->modify( '+' . easter_days( $year ) . ' days' );
		return $base;
	}

	/**
	 * Get all moveable feast dates for a year.
	 *
	 * Moveable feasts are calculated relative to Easter.
	 *
	 * @param int $year The year.
	 * @return array<string, DateTime> Feast name to date mapping.
	 */
	public static function get_moveable_feasts( int $year ): array {
		$easter = self::get_easter( $year );

		return array(
			// Lent
			'ash_wednesday'      => ( clone $easter )->modify( '-46 days' ),
			'first_sunday_lent'  => ( clone $easter )->modify( '-42 days' ),

			// Holy Week
			'palm_sunday'        => ( clone $easter )->modify( '-7 days' ),
			'holy_thursday'      => ( clone $easter )->modify( '-3 days' ),
			'good_friday'        => ( clone $easter )->modify( '-2 days' ),
			'holy_saturday'      => ( clone $easter )->modify( '-1 day' ),

			// Easter Season
			'easter_sunday'      => $easter,
			'easter_monday'      => ( clone $easter )->modify( '+1 day' ),
			'divine_mercy'       => ( clone $easter )->modify( '+7 days' ),
			'ascension'          => ( clone $easter )->modify( '+39 days' ),
			'pentecost'          => ( clone $easter )->modify( '+49 days' ),

			// Post-Pentecost
			'trinity_sunday'     => ( clone $easter )->modify( '+56 days' ),
			'corpus_christi'     => ( clone $easter )->modify( '+60 days' ),
			'sacred_heart'       => ( clone $easter )->modify( '+68 days' ),
			'immaculate_heart'   => ( clone $easter )->modify( '+69 days' ),

			// Christ the King (Last Sunday before Advent)
			'christ_the_king'    => self::get_advent_start( $year )->modify( '-7 days' ),
		);
	}

	/**
	 * Get Advent start date for a year.
	 *
	 * Advent begins on the fourth Sunday before Christmas.
	 *
	 * @param int $year The year.
	 * @return DateTime First Sunday of Advent.
	 */
	public static function get_advent_start( int $year ): DateTime {
		$christmas = new DateTime( "{$year}-12-25" );
		$dow       = (int) $christmas->format( 'N' ); // 1=Mon, 7=Sun

		// Calculate days to go back to the fourth Sunday before Christmas.
		// If Christmas is Sunday (7), we go back 21 days.
		// Otherwise, we go back (21 + days_until_sunday).
		$days_back = 21 + ( $dow % 7 );

		return ( clone $christmas )->modify( "-{$days_back} days" );
	}

	/**
	 * Check if a date matches a recurrence pattern.
	 *
	 * @param DateTime $date       The date to check.
	 * @param array    $recurrence Recurrence configuration.
	 * @param string   $created_at Template creation date for bi-weekly offset.
	 * @return bool True if the date matches the pattern.
	 */
	public static function matches_pattern( DateTime $date, array $recurrence, string $created_at = '' ): bool {
		$type = $recurrence['type'] ?? '';

		switch ( $type ) {
			case 'daily':
				return self::matches_daily( $date, $recurrence );

			case 'weekly':
				return self::matches_weekly( $date, $recurrence );

			case 'biweekly':
				return self::matches_biweekly( $date, $recurrence, $created_at );

			case 'monthly':
				return self::matches_monthly( $date, $recurrence );

			case 'positional':
				return self::matches_positional( $date, $recurrence );

			case 'last_of_month':
				return self::matches_last_of_month( $date, $recurrence );

			case 'annual':
				return self::matches_annual( $date, $recurrence );

			case 'moveable':
				return self::matches_moveable( $date, $recurrence );

			default:
				return false;
		}
	}

	/**
	 * Check if date matches daily pattern.
	 *
	 * @param DateTime $date       The date to check.
	 * @param array    $recurrence Recurrence configuration.
	 * @return bool True if matches.
	 */
	private static function matches_daily( DateTime $date, array $recurrence ): bool {
		$interval = (int) ( $recurrence['every_n_days'] ?? 1 );

		if ( 1 === $interval ) {
			return true;
		}

		// For intervals > 1, we need a reference date.
		$reference = ! empty( $recurrence['start_date'] )
			? new DateTime( $recurrence['start_date'] )
			: new DateTime( 'today' );

		$diff = $reference->diff( $date )->days;
		return 0 === ( $diff % $interval );
	}

	/**
	 * Check if date matches weekly pattern.
	 *
	 * @param DateTime $date       The date to check.
	 * @param array    $recurrence Recurrence configuration with 'days' array.
	 * @return bool True if matches.
	 */
	private static function matches_weekly( DateTime $date, array $recurrence ): bool {
		$days     = $recurrence['days'] ?? array();
		$day_name = $date->format( 'l' );

		return in_array( $day_name, $days, true );
	}

	/**
	 * Check if date matches bi-weekly pattern.
	 *
	 * @param DateTime $date       The date to check.
	 * @param array    $recurrence Recurrence configuration.
	 * @param string   $created_at Reference date for week calculation.
	 * @return bool True if matches.
	 */
	private static function matches_biweekly( DateTime $date, array $recurrence, string $created_at ): bool {
		// First check if it's the right day of week.
		if ( ! self::matches_weekly( $date, $recurrence ) ) {
			return false;
		}

		// Calculate week offset.
		$reference   = ! empty( $created_at ) ? new DateTime( $created_at ) : new DateTime( 'today' );
		$week_diff   = (int) floor( $reference->diff( $date )->days / 7 );
		$week_offset = (int) ( $recurrence['week_offset'] ?? 0 );

		return ( $week_diff % 2 ) === $week_offset;
	}

	/**
	 * Check if date matches monthly pattern (specific day of month).
	 *
	 * @param DateTime $date       The date to check.
	 * @param array    $recurrence Recurrence configuration with 'day_of_month'.
	 * @return bool True if matches.
	 */
	private static function matches_monthly( DateTime $date, array $recurrence ): bool {
		$target_day = (int) ( $recurrence['day_of_month'] ?? 1 );
		$actual_day = (int) $date->format( 'j' );

		// Handle end of month.
		if ( $target_day > 28 ) {
			$last_day = (int) $date->format( 't' );
			if ( $target_day > $last_day ) {
				return $actual_day === $last_day;
			}
		}

		return $actual_day === $target_day;
	}

	/**
	 * Check if date matches positional pattern (e.g., "First Friday").
	 *
	 * @param DateTime $date       The date to check.
	 * @param array    $recurrence Recurrence with 'position' and 'day'.
	 * @return bool True if matches.
	 */
	private static function matches_positional( DateTime $date, array $recurrence ): bool {
		$position   = $recurrence['position'] ?? 'first';
		$target_day = $recurrence['day'] ?? 'Friday';

		// Check if it's the right day of week.
		if ( $date->format( 'l' ) !== $target_day ) {
			return false;
		}

		$day_of_month = (int) $date->format( 'j' );

		// Determine which occurrence of this day it is.
		$occurrence = (int) ceil( $day_of_month / 7 );

		$target_occurrence = self::POSITION_MAP[ $position ] ?? 1;

		if ( -1 === $target_occurrence ) {
			// "Last" occurrence - check if next week is a different month.
			return self::is_last_occurrence_of_day( $date );
		}

		return $occurrence === $target_occurrence;
	}

	/**
	 * Check if date is the last occurrence of its day in the month.
	 *
	 * @param DateTime $date The date to check.
	 * @return bool True if it's the last occurrence.
	 */
	private static function is_last_occurrence_of_day( DateTime $date ): bool {
		$next_week = ( clone $date )->modify( '+7 days' );
		return $next_week->format( 'm' ) !== $date->format( 'm' );
	}

	/**
	 * Check if date matches last-of-month pattern.
	 *
	 * @param DateTime $date       The date to check.
	 * @param array    $recurrence Recurrence with 'day'.
	 * @return bool True if matches.
	 */
	private static function matches_last_of_month( DateTime $date, array $recurrence ): bool {
		$target_day = $recurrence['day'] ?? 'Sunday';

		if ( $date->format( 'l' ) !== $target_day ) {
			return false;
		}

		return self::is_last_occurrence_of_day( $date );
	}

	/**
	 * Check if date matches annual pattern.
	 *
	 * @param DateTime $date       The date to check.
	 * @param array    $recurrence Recurrence with 'month' and 'day'.
	 * @return bool True if matches.
	 */
	private static function matches_annual( DateTime $date, array $recurrence ): bool {
		$target_month = (int) ( $recurrence['month'] ?? 1 );
		$target_day   = (int) ( $recurrence['day'] ?? 1 );

		return (int) $date->format( 'n' ) === $target_month
			&& (int) $date->format( 'j' ) === $target_day;
	}

	/**
	 * Check if date matches a moveable feast pattern.
	 *
	 * @param DateTime $date       The date to check.
	 * @param array    $recurrence Recurrence with 'feast' name or 'offset' from Easter.
	 * @return bool True if matches.
	 */
	private static function matches_moveable( DateTime $date, array $recurrence ): bool {
		$year   = (int) $date->format( 'Y' );
		$feasts = self::get_moveable_feasts( $year );

		// Check named feast.
		$feast_name = $recurrence['feast'] ?? '';
		if ( ! empty( $feast_name ) && isset( $feasts[ $feast_name ] ) ) {
			return $date->format( 'Y-m-d' ) === $feasts[ $feast_name ]->format( 'Y-m-d' );
		}

		// Check Easter offset.
		if ( isset( $recurrence['easter_offset'] ) ) {
			$offset  = (int) $recurrence['easter_offset'];
			$easter  = self::get_easter( $year );
			$target  = ( clone $easter )->modify( "{$offset} days" );

			return $date->format( 'Y-m-d' ) === $target->format( 'Y-m-d' );
		}

		return false;
	}

	/**
	 * Get all dates matching a recurrence pattern within a range.
	 *
	 * @param array  $recurrence Recurrence configuration.
	 * @param string $start_date Start date (Y-m-d).
	 * @param string $end_date   End date (Y-m-d).
	 * @param string $created_at Template creation date.
	 * @return array<DateTime> Matching dates.
	 */
	public static function get_dates_in_range(
		array $recurrence,
		string $start_date,
		string $end_date,
		string $created_at = ''
	): array {
		$matches = array();
		$current = new DateTime( $start_date );
		$end     = new DateTime( $end_date );

		while ( $current <= $end ) {
			if ( self::matches_pattern( $current, $recurrence, $created_at ) ) {
				$matches[] = clone $current;
			}
			$current->modify( '+1 day' );
		}

		return $matches;
	}

	/**
	 * Get a human-readable description of a recurrence pattern.
	 *
	 * @param array $recurrence Recurrence configuration.
	 * @return string Human-readable description.
	 */
	public static function get_description( array $recurrence ): string {
		$type = $recurrence['type'] ?? '';
		$time = $recurrence['time'] ?? '';

		$time_str = ! empty( $time ) ? sprintf( ' at %s', $time ) : '';

		switch ( $type ) {
			case 'daily':
				$n = (int) ( $recurrence['every_n_days'] ?? 1 );
				if ( 1 === $n ) {
					return __( 'Daily', 'parish-core' ) . $time_str;
				}
				/* translators: %d is number of days */
				return sprintf( __( 'Every %d days', 'parish-core' ), $n ) . $time_str;

			case 'weekly':
				$days = $recurrence['days'] ?? array();
				if ( count( $days ) === 7 ) {
					return __( 'Every day', 'parish-core' ) . $time_str;
				}
				return implode( ', ', $days ) . $time_str;

			case 'biweekly':
				$days = $recurrence['days'] ?? array();
				/* translators: %s is day name(s) */
				return sprintf( __( 'Every other %s', 'parish-core' ), implode( ', ', $days ) ) . $time_str;

			case 'monthly':
				$day = (int) ( $recurrence['day_of_month'] ?? 1 );
				/* translators: %s is ordinal day (1st, 2nd, etc.) */
				return sprintf( __( '%s of each month', 'parish-core' ), self::ordinal( $day ) ) . $time_str;

			case 'positional':
				$position = ucfirst( $recurrence['position'] ?? 'first' );
				$day      = $recurrence['day'] ?? 'Friday';
				/* translators: %1$s is position (First, Second), %2$s is day name */
				return sprintf( __( '%1$s %2$s of each month', 'parish-core' ), $position, $day ) . $time_str;

			case 'last_of_month':
				$day = $recurrence['day'] ?? 'Sunday';
				/* translators: %s is day name */
				return sprintf( __( 'Last %s of each month', 'parish-core' ), $day ) . $time_str;

			case 'annual':
				$month = (int) ( $recurrence['month'] ?? 1 );
				$day   = (int) ( $recurrence['day'] ?? 1 );
				$date  = DateTime::createFromFormat( 'n-j', "{$month}-{$day}" );
				/* translators: %s is date (e.g., "December 25") */
				return sprintf( __( 'Annually on %s', 'parish-core' ), $date->format( 'F j' ) ) . $time_str;

			case 'moveable':
				$feast = $recurrence['feast'] ?? '';
				if ( ! empty( $feast ) ) {
					return ucwords( str_replace( '_', ' ', $feast ) ) . $time_str;
				}
				$offset = (int) ( $recurrence['easter_offset'] ?? 0 );
				if ( $offset > 0 ) {
					/* translators: %d is number of days */
					return sprintf( __( '%d days after Easter', 'parish-core' ), $offset ) . $time_str;
				} elseif ( $offset < 0 ) {
					/* translators: %d is number of days */
					return sprintf( __( '%d days before Easter', 'parish-core' ), abs( $offset ) ) . $time_str;
				}
				return __( 'Easter Sunday', 'parish-core' ) . $time_str;

			default:
				return __( 'Unknown pattern', 'parish-core' );
		}
	}

	/**
	 * Convert number to ordinal string.
	 *
	 * @param int $number The number.
	 * @return string Ordinal string (1st, 2nd, 3rd, etc.).
	 */
	private static function ordinal( int $number ): string {
		$suffix = array( 'th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th' );

		if ( ( $number % 100 >= 11 ) && ( $number % 100 <= 13 ) ) {
			return $number . 'th';
		}

		return $number . $suffix[ $number % 10 ];
	}
}
