<?php
/**
 * Schedule Generator - generates Mass Time occurrences from CPT posts.
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Parish_Schedule_Generator class.
 */
class Parish_Schedule_Generator {

	/**
	 * Singleton instance.
	 *
	 * @var Parish_Schedule_Generator|null
	 */
	private static ?Parish_Schedule_Generator $instance = null;

	/**
	 * Event types with labels.
	 */
	public const EVENT_TYPES = array(
		'mass'       => 'Mass',
		'confession' => 'Confession',
		'adoration'  => 'Adoration',
		'rosary'     => 'Rosary',
	);

	/**
	 * Cache group for transients.
	 */
	private const CACHE_GROUP = 'parish_schedule';

	/**
	 * Cache expiry in seconds (5 minutes).
	 */
	private const CACHE_EXPIRY = 300;

	/**
	 * Get singleton instance.
	 */
	public static function instance(): Parish_Schedule_Generator {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'save_post_parish_mass_time', array( $this, 'clear_cache' ), 10, 1 );
		add_action( 'delete_post', array( $this, 'clear_cache' ), 10, 1 );
		add_filter( 'wp_insert_post_data', array( $this, 'auto_generate_title' ), 10, 2 );
	}

	/**
	 * Get event types with labels (filterable).
	 *
	 * @return array<string, string> Event types.
	 */
	public function get_event_types(): array {
		return apply_filters( 'parish_mass_time_types', self::EVENT_TYPES );
	}

	/**
	 * Auto-generate title for Mass Time posts if empty.
	 *
	 * @param array $data    Post data.
	 * @param array $postarr Raw post data.
	 * @return array Modified post data.
	 */
	public function auto_generate_title( array $data, array $postarr ): array {
		if ( 'parish_mass_time' !== $data['post_type'] ) {
			return $data;
		}

		if ( ! empty( $data['post_title'] ) ) {
			return $data;
		}

		$church_id = isset( $postarr['meta_input']['parish_mass_time_church_id'] )
			? absint( $postarr['meta_input']['parish_mass_time_church_id'] )
			: 0;

		$type = isset( $postarr['meta_input']['parish_mass_time_liturgical_type'] )
			? sanitize_text_field( $postarr['meta_input']['parish_mass_time_liturgical_type'] )
			: 'mass';

		$datetime = isset( $postarr['meta_input']['parish_mass_time_start_datetime'] )
			? sanitize_text_field( $postarr['meta_input']['parish_mass_time_start_datetime'] )
			: '';

		$church_name = __( 'All Churches', 'parish-core' );
		if ( $church_id > 0 ) {
			$church = get_post( $church_id );
			if ( $church ) {
				$church_name = html_entity_decode( $church->post_title, ENT_QUOTES, 'UTF-8' );
			}
		}

		$event_types = $this->get_event_types();
		$type_label  = $event_types[ $type ] ?? ucfirst( $type );

		$time_str = '';
		if ( $datetime ) {
			$timestamp = strtotime( $datetime );
			if ( $timestamp ) {
				$time_str = wp_date( 'g:i A', $timestamp );
			}
		}

		$title_parts = array( $type_label, $church_name );
		if ( $time_str ) {
			$title_parts[] = $time_str;
		}

		$data['post_title'] = implode( ' — ', $title_parts );

		return $data;
	}

	/**
	 * Clear schedule cache when a Mass Time post is saved or deleted.
	 *
	 * @param int $post_id Post ID.
	 */
	public function clear_cache( int $post_id ): void {
		$post_type = get_post_type( $post_id );
		if ( 'parish_mass_time' !== $post_type ) {
			return;
		}

		// Clear all schedule transients.
		global $wpdb;
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
				'_transient_parish_sched_%'
			)
		);
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
				'_transient_timeout_parish_sched_%'
			)
		);
	}

	/**
	 * Generate occurrences for a date range.
	 *
	 * @param string $start   Start date (Y-m-d).
	 * @param string $end     End date (Y-m-d).
	 * @param array  $filters Optional filters: church_id, type, active_only.
	 * @return array Array of occurrence objects.
	 */
	public function generate( string $start, string $end, array $filters = array() ): array {
		$cache_key = 'parish_sched_' . md5( $start . $end . wp_json_encode( $filters ) );
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$defaults = array(
			'church_id'   => null,
			'type'        => null,
			'active_only' => true,
		);
		$filters  = wp_parse_args( $filters, $defaults );

		// Build meta query.
		$meta_query = array();

		if ( $filters['active_only'] ) {
			// Include posts where is_active is explicitly true OR where it's not set (defaults to true).
			$meta_query[] = array(
				'relation' => 'OR',
				array(
					'key'     => 'parish_mass_time_is_active',
					'value'   => '1',
					'compare' => '=',
				),
				array(
					'key'     => 'parish_mass_time_is_active',
					'compare' => 'NOT EXISTS',
				),
			);
		}

		if ( null !== $filters['type'] && '' !== $filters['type'] ) {
			$meta_query[] = array(
				'key'     => 'parish_mass_time_liturgical_type',
				'value'   => sanitize_text_field( $filters['type'] ),
				'compare' => '=',
			);
		}

		// Get all Mass Time posts.
		$args = array(
			'post_type'      => 'parish_mass_time',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'meta_query'     => $meta_query,
		);

		$posts       = get_posts( $args );
		$occurrences = array();

		// Use WordPress timezone for consistent date handling.
		$tz       = wp_timezone();
		$start_dt = new DateTime( $start . ' 00:00:00', $tz );
		$end_dt   = new DateTime( $end . ' 23:59:59', $tz );
		$start_ts = $start_dt->getTimestamp();
		$end_ts   = $end_dt->getTimestamp();

		foreach ( $posts as $post ) {
			$church_id = absint( get_post_meta( $post->ID, 'parish_mass_time_church_id', true ) );

			// Filter by church if specified.
			if ( null !== $filters['church_id'] && '' !== $filters['church_id'] ) {
				$filter_church = absint( $filters['church_id'] );
				// Show items for specific church OR "all churches" (0).
				if ( $church_id !== $filter_church && 0 !== $church_id ) {
					continue;
				}
			}

			$post_occurrences = $this->get_occurrences_for_post( $post, $start_ts, $end_ts );
			$occurrences      = array_merge( $occurrences, $post_occurrences );
		}

		// Sort by datetime.
		usort(
			$occurrences,
			function ( $a, $b ) {
				return strcmp( $a['datetime'], $b['datetime'] );
			}
		);

		set_transient( $cache_key, $occurrences, self::CACHE_EXPIRY );

		return $occurrences;
	}

	/**
	 * Generate occurrences for today.
	 *
	 * @param array $filters Optional filters.
	 * @return array Array of occurrence objects.
	 */
	public function generate_today( array $filters = array() ): array {
		$today = wp_date( 'Y-m-d' );
		return $this->generate( $today, $today, $filters );
	}

	/**
	 * Generate occurrences for this week (today + 6 days).
	 *
	 * @param array $filters Optional filters.
	 * @return array Array of occurrence objects grouped by date.
	 */
	public function generate_week( array $filters = array() ): array {
		$today = wp_date( 'Y-m-d' );
		$end   = wp_date( 'Y-m-d', strtotime( '+6 days' ) );

		$occurrences = $this->generate( $today, $end, $filters );

		// Group by date.
		$grouped = array();
		foreach ( $occurrences as $occ ) {
			$date = $occ['date'];
			if ( ! isset( $grouped[ $date ] ) ) {
				$grouped[ $date ] = array();
			}
			$grouped[ $date ][] = $occ;
		}

		return $grouped;
	}

	/**
	 * Get occurrences for a single Mass Time post.
	 *
	 * @param WP_Post $post     The Mass Time post.
	 * @param int     $start_ts Start timestamp.
	 * @param int     $end_ts   End timestamp.
	 * @return array Array of occurrence objects.
	 */
	public function get_occurrences_for_post( WP_Post $post, int $start_ts, int $end_ts ): array {
		$occurrences = array();

		$church_id        = absint( get_post_meta( $post->ID, 'parish_mass_time_church_id', true ) );
		$type             = get_post_meta( $post->ID, 'parish_mass_time_liturgical_type', true ) ?: 'mass';
		$start_datetime   = get_post_meta( $post->ID, 'parish_mass_time_start_datetime', true );
		$duration         = absint( get_post_meta( $post->ID, 'parish_mass_time_duration_minutes', true ) ) ?: 60;
		$is_special       = (bool) get_post_meta( $post->ID, 'parish_mass_time_is_special_event', true );
		$is_recurring     = (bool) get_post_meta( $post->ID, 'parish_mass_time_is_recurring', true );
		$recurrence       = get_post_meta( $post->ID, 'parish_mass_time_recurrence', true ) ?: array();
		$exception_dates  = get_post_meta( $post->ID, 'parish_mass_time_exception_dates', true ) ?: array();
		$is_livestreamed  = (bool) get_post_meta( $post->ID, 'parish_mass_time_is_livestreamed', true );
		$livestream_url   = get_post_meta( $post->ID, 'parish_mass_time_livestream_url', true );
		$livestream_embed = get_post_meta( $post->ID, 'parish_mass_time_livestream_embed', true );
		$notes            = get_post_meta( $post->ID, 'parish_mass_time_notes', true );

		if ( empty( $start_datetime ) ) {
			return $occurrences;
		}

		$base_timestamp = strtotime( $start_datetime );
		if ( ! $base_timestamp ) {
			return $occurrences;
		}

		// Get church info.
		$church_name = __( 'All Churches', 'parish-core' );
		if ( $church_id > 0 ) {
			$church = get_post( $church_id );
			if ( $church ) {
				$church_name = html_entity_decode( $church->post_title, ENT_QUOTES, 'UTF-8' );
			}
		}

		// Get event type label.
		$event_types = $this->get_event_types();
		$type_label  = $event_types[ $type ] ?? ucfirst( $type );

		// Build base occurrence data.
		$base_data = array(
			'post_id'          => $post->ID,
			'title'            => $post->post_title,
			'church_id'        => $church_id,
			'church_name'      => $church_name,
			'type'             => $type,
			'type_label'       => $type_label,
			'duration_minutes' => $duration,
			'is_special_event' => $is_special,
			'is_livestreamed'  => $is_livestreamed,
			'livestream_url'   => $livestream_url,
			'livestream_embed' => $livestream_embed,
			'notes'            => $notes,
		);

		// Convert exception dates to a lookup array.
		$exceptions = array_flip( (array) $exception_dates );

		if ( $is_recurring && ! empty( $recurrence ) ) {
			// Generate recurring occurrences.
			$dates = $this->expand_recurrence( $recurrence, $base_timestamp, $start_ts, $end_ts );

			foreach ( $dates as $date_ts ) {
				// Use wp_date for timezone-aware formatting.
				$date = wp_date( 'Y-m-d', $date_ts );

				// Skip exception dates.
				if ( isset( $exceptions[ $date ] ) ) {
					continue;
				}

				$time     = wp_date( 'H:i', $base_timestamp );
				$datetime = $date . ' ' . $time;

				$occurrences[] = array_merge(
					$base_data,
					array(
						'date'     => $date,
						'time'     => $time,
						'datetime' => $datetime,
					)
				);
			}
		} else {
			// Single occurrence (or special event).
			$event_date = wp_date( 'Y-m-d', $base_timestamp );

			// Use WordPress timezone for comparison.
			$tz       = wp_timezone();
			$event_dt = new DateTime( $event_date . ' 00:00:00', $tz );
			$event_ts = $event_dt->getTimestamp();

			// Compare date only (not time) to ensure same-day events are included.
			$start_date_ts = ( new DateTime( '@' . $start_ts ) )->setTimezone( $tz )->setTime( 0, 0, 0 )->getTimestamp();
			$end_date_ts   = ( new DateTime( '@' . $end_ts ) )->setTimezone( $tz )->setTime( 23, 59, 59 )->getTimestamp();

			if ( $event_ts >= $start_date_ts && $event_ts <= $end_date_ts ) {
				// Check if not in exceptions.
				if ( ! isset( $exceptions[ $event_date ] ) ) {
					$occurrences[] = array_merge(
						$base_data,
						array(
							'date'     => $event_date,
							'time'     => wp_date( 'H:i', $base_timestamp ),
							'datetime' => wp_date( 'Y-m-d H:i', $base_timestamp ),
						)
					);
				}
			}
		}

		return $occurrences;
	}

	/**
	 * Expand a recurrence rule into dates within the given range.
	 *
	 * @param array $recurrence    Recurrence rule.
	 * @param int   $base_ts       Base timestamp (for time).
	 * @param int   $range_start   Range start timestamp.
	 * @param int   $range_end     Range end timestamp.
	 * @return array Array of date timestamps.
	 */
	private function expand_recurrence( array $recurrence, int $base_ts, int $range_start, int $range_end ): array {
		$dates = array();
		$type  = $recurrence['type'] ?? 'weekly';

		// Check recurrence end date.
		$rec_end = null;
		if ( ! empty( $recurrence['end_date'] ) ) {
			$rec_end = strtotime( $recurrence['end_date'] );
			if ( $rec_end && $rec_end < $range_end ) {
				$range_end = $rec_end;
			}
		}

		// Limit to 90 days max for safety.
		$max_end   = strtotime( '+90 days', $range_start );
		$range_end = min( $range_end, $max_end );

		switch ( $type ) {
			case 'daily':
				$dates = $this->expand_daily( $range_start, $range_end );
				break;

			case 'weekly':
				$days = $recurrence['days'] ?? array();
				// If no days specified, default to the day of the week from base timestamp
				if ( empty( $days ) ) {
					$days = array( wp_date( 'l', $base_ts ) );
				}
				$dates = $this->expand_weekly( $days, $range_start, $range_end );
				break;

			case 'biweekly':
				$days = $recurrence['days'] ?? array();
				// If no days specified, default to the day of the week from base timestamp
				if ( empty( $days ) ) {
					$days = array( wp_date( 'l', $base_ts ) );
				}
				$dates = $this->expand_biweekly( $days, $base_ts, $range_start, $range_end );
				break;

			case 'monthly_day':
				$day_of_month = absint( $recurrence['day_of_month'] ?? 1 );
				$dates        = $this->expand_monthly_day( $day_of_month, $range_start, $range_end );
				break;

			case 'monthly_ordinal':
				$ordinal = $recurrence['ordinal'] ?? 'first';
				$day     = $recurrence['ordinal_day'] ?? 'Friday';
				$dates   = $this->expand_monthly_ordinal( $ordinal, $day, $range_start, $range_end );
				break;

			case 'yearly':
				$month = absint( $recurrence['month'] ?? 1 );
				$day   = absint( $recurrence['day_of_month'] ?? 1 );
				$dates = $this->expand_yearly( $month, $day, $range_start, $range_end );
				break;
		}

		return $dates;
	}

	/**
	 * Expand daily recurrence.
	 *
	 * @param int $start Range start.
	 * @param int $end   Range end.
	 * @return array Dates.
	 */
	private function expand_daily( int $start, int $end ): array {
		$dates = array();

		// Normalize to start of day using WordPress timezone.
		$tz      = wp_timezone();
		$current = ( new DateTime( '@' . $start ) )->setTimezone( $tz )->setTime( 0, 0, 0 )->getTimestamp();
		$end_day = ( new DateTime( '@' . $end ) )->setTimezone( $tz )->setTime( 23, 59, 59 )->getTimestamp();

		while ( $current <= $end_day ) {
			$dates[] = $current;
			$current = strtotime( '+1 day', $current );
		}

		return $dates;
	}

	/**
	 * Expand weekly recurrence.
	 *
	 * @param array $days  Array of day names (e.g., ['Monday', 'Friday']).
	 * @param int   $start Range start.
	 * @param int   $end   Range end.
	 * @return array Dates.
	 */
	private function expand_weekly( array $days, int $start, int $end ): array {
		$dates = array();

		// Trim whitespace and filter out empty/invalid day values
		$days = array_filter( array_map( 'trim', $days ), function( $day ) {
			return is_string( $day ) && ! empty( $day );
		} );

		if ( empty( $days ) ) {
			return $dates;
		}

		// Normalize to start of day using WordPress timezone.
		$tz      = wp_timezone();
		$current = ( new DateTime( '@' . $start ) )->setTimezone( $tz )->setTime( 0, 0, 0 )->getTimestamp();
		$end_day = ( new DateTime( '@' . $end ) )->setTimezone( $tz )->setTime( 23, 59, 59 )->getTimestamp();

		// Normalize day names to handle case inconsistencies.
		$normalized_days = array_map( 'ucfirst', array_map( 'strtolower', array_values( $days ) ) );

		while ( $current <= $end_day ) {
			// Use wp_date for timezone-aware day name.
			$day_name = wp_date( 'l', $current );
			if ( in_array( $day_name, $normalized_days, true ) ) {
				$dates[] = $current;
			}
			$current = strtotime( '+1 day', $current );
		}

		return $dates;
	}

	/**
	 * Expand biweekly recurrence.
	 *
	 * @param array $days    Array of day names.
	 * @param int   $base_ts Base timestamp to determine week parity.
	 * @param int   $start   Range start.
	 * @param int   $end     Range end.
	 * @return array Dates.
	 */
	private function expand_biweekly( array $days, int $base_ts, int $start, int $end ): array {
		$dates = array();

		// Trim whitespace and filter out empty/invalid day values
		$days = array_filter( array_map( 'trim', $days ), function( $day ) {
			return is_string( $day ) && ! empty( $day );
		} );

		if ( empty( $days ) ) {
			return $dates;
		}

		// Normalize to start of day using WordPress timezone.
		$tz      = wp_timezone();
		$current = ( new DateTime( '@' . $start ) )->setTimezone( $tz )->setTime( 0, 0, 0 )->getTimestamp();
		$end_day = ( new DateTime( '@' . $end ) )->setTimezone( $tz )->setTime( 23, 59, 59 )->getTimestamp();

		// Normalize day names to handle case inconsistencies.
		$normalized_days = array_map( 'ucfirst', array_map( 'strtolower', array_values( $days ) ) );

		// Determine the week number of the base date using timezone-aware function.
		$base_week = (int) wp_date( 'W', $base_ts );

		while ( $current <= $end_day ) {
			$current_week = (int) wp_date( 'W', $current );
			$week_diff    = abs( $current_week - $base_week );

			// Check if this is an "on" week (every other week).
			if ( 0 === $week_diff % 2 ) {
				// Use wp_date for timezone-aware day name.
				$day_name = wp_date( 'l', $current );
				if ( in_array( $day_name, $normalized_days, true ) ) {
					$dates[] = $current;
				}
			}

			$current = strtotime( '+1 day', $current );
		}

		return $dates;
	}

	/**
	 * Expand monthly by day of month.
	 *
	 * @param int $day_of_month Day of month (1-31).
	 * @param int $start        Range start.
	 * @param int $end          Range end.
	 * @return array Dates.
	 */
	private function expand_monthly_day( int $day_of_month, int $start, int $end ): array {
		$dates = array();

		// Start from the beginning of the month of $start.
		$current_month = wp_date( 'Y-m', $start );

		for ( $i = 0; $i < 4; $i++ ) { // Max 4 months.
			$date_str  = $current_month . '-' . str_pad( (string) $day_of_month, 2, '0', STR_PAD_LEFT );
			$timestamp = strtotime( $date_str );

			// Handle months with fewer days.
			if ( $timestamp && (int) wp_date( 'd', $timestamp ) === $day_of_month ) {
				if ( $timestamp >= $start && $timestamp <= $end ) {
					$dates[] = $timestamp;
				}
			}

			$current_month = wp_date( 'Y-m', strtotime( $current_month . '-01 +1 month' ) );
		}

		return $dates;
	}

	/**
	 * Expand monthly by ordinal weekday (e.g., First Friday).
	 *
	 * @param string $ordinal Ordinal: first, second, third, fourth, last.
	 * @param string $day     Day name: Monday, Tuesday, etc.
	 * @param int    $start   Range start.
	 * @param int    $end     Range end.
	 * @return array Dates.
	 */
	private function expand_monthly_ordinal( string $ordinal, string $day, int $start, int $end ): array {
		$dates = array();

		$ordinal_map = array(
			'first'  => 'first',
			'second' => 'second',
			'third'  => 'third',
			'fourth' => 'fourth',
			'last'   => 'last',
		);

		$ord = $ordinal_map[ strtolower( $ordinal ) ] ?? 'first';

		// Start from the beginning of the month of $start.
		$current_month = wp_date( 'Y-m', $start );

		for ( $i = 0; $i < 4; $i++ ) { // Max 4 months.
			$date_str  = "{$ord} {$day} of {$current_month}";
			$timestamp = strtotime( $date_str );

			if ( $timestamp && $timestamp >= $start && $timestamp <= $end ) {
				$dates[] = $timestamp;
			}

			$current_month = wp_date( 'Y-m', strtotime( $current_month . '-01 +1 month' ) );
		}

		return $dates;
	}

	/**
	 * Expand yearly recurrence.
	 *
	 * @param int $month Month (1-12).
	 * @param int $day   Day of month.
	 * @param int $start Range start.
	 * @param int $end   Range end.
	 * @return array Dates.
	 */
	private function expand_yearly( int $month, int $day, int $start, int $end ): array {
		$dates = array();

		// Check current year and next year.
		$start_year = (int) wp_date( 'Y', $start );

		for ( $year = $start_year; $year <= $start_year + 1; $year++ ) {
			$date_str  = sprintf( '%04d-%02d-%02d', $year, $month, $day );
			$timestamp = strtotime( $date_str );

			if ( $timestamp && $timestamp >= $start && $timestamp <= $end ) {
				$dates[] = $timestamp;
			}
		}

		return $dates;
	}

	/**
	 * Get a weekly schedule for a church (recurring items grouped by day).
	 *
	 * @param int $church_id Church ID (0 for all).
	 * @return array Schedule grouped by day of week.
	 */
	public function get_weekly_schedule( int $church_id = 0 ): array {
		$days = array(
			'Sunday'    => array(),
			'Monday'    => array(),
			'Tuesday'   => array(),
			'Wednesday' => array(),
			'Thursday'  => array(),
			'Friday'    => array(),
			'Saturday'  => array(),
		);

		$meta_query = array(
			'relation' => 'AND',
			// Active check: is_active = 1 OR is_active not set (defaults to true).
			array(
				'relation' => 'OR',
				array(
					'key'     => 'parish_mass_time_is_active',
					'value'   => '1',
					'compare' => '=',
				),
				array(
					'key'     => 'parish_mass_time_is_active',
					'compare' => 'NOT EXISTS',
				),
			),
			// Recurring check.
			array(
				'key'     => 'parish_mass_time_is_recurring',
				'value'   => '1',
				'compare' => '=',
			),
		);

		if ( $church_id > 0 ) {
			$meta_query[] = array(
				'relation' => 'OR',
				array(
					'key'     => 'parish_mass_time_church_id',
					'value'   => $church_id,
					'compare' => '=',
					'type'    => 'NUMERIC',
				),
				array(
					'key'     => 'parish_mass_time_church_id',
					'value'   => '0',
					'compare' => '=',
					'type'    => 'NUMERIC',
				),
			);
		}

		$posts = get_posts(
			array(
				'post_type'      => 'parish_mass_time',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'meta_query'     => $meta_query,
			)
		);

		$event_types = $this->get_event_types();

		foreach ( $posts as $post ) {
			$recurrence = get_post_meta( $post->ID, 'parish_mass_time_recurrence', true ) ?: array();
			$type       = $recurrence['type'] ?? 'weekly';

			if ( 'weekly' !== $type && 'daily' !== $type ) {
				continue;
			}

			$start_datetime  = get_post_meta( $post->ID, 'parish_mass_time_start_datetime', true );

			$rec_days = array();
			if ( 'daily' === $type ) {
				$rec_days = array_keys( $days );
			} else {
				$rec_days = $recurrence['days'] ?? array();
				// If no days specified, default to the day of the week from start datetime
				if ( empty( $rec_days ) && $start_datetime ) {
					$ts = strtotime( $start_datetime );
					if ( $ts ) {
						$rec_days = array( wp_date( 'l', $ts ) );
					}
				}
			}
			$lit_type        = get_post_meta( $post->ID, 'parish_mass_time_liturgical_type', true ) ?: 'mass';
			$is_livestreamed = (bool) get_post_meta( $post->ID, 'parish_mass_time_is_livestreamed', true );
			$livestream_url  = get_post_meta( $post->ID, 'parish_mass_time_livestream_url', true );
			$notes           = get_post_meta( $post->ID, 'parish_mass_time_notes', true );

			$time = '';
			if ( $start_datetime ) {
				$ts = strtotime( $start_datetime );
				if ( $ts ) {
					$time = wp_date( 'H:i', $ts );
				}
			}

			$item = array(
				'post_id'         => $post->ID,
				'title'           => $post->post_title,
				'time'            => $time,
				'type'            => $lit_type,
				'type_label'      => $event_types[ $lit_type ] ?? ucfirst( $lit_type ),
				'is_livestreamed' => $is_livestreamed,
				'livestream_url'  => $livestream_url,
				'notes'           => $notes,
			);

			// Normalize day names for comparison.
			foreach ( $rec_days as $day ) {
				$normalized_day = ucfirst( strtolower( $day ) );
				if ( isset( $days[ $normalized_day ] ) ) {
					$days[ $normalized_day ][] = $item;
				}
			}
		}

		// Sort each day by time.
		foreach ( $days as $day => $items ) {
			usort(
				$items,
				function ( $a, $b ) {
					return strcmp( $a['time'], $b['time'] );
				}
			);
			$days[ $day ] = $items;
		}

		return $days;
	}

	/**
	 * Get special events for a church.
	 *
	 * @param int $church_id Church ID (0 for all).
	 * @return array Array of special events.
	 */
	public function get_special_events( int $church_id = 0 ): array {
		$events = array();

		$meta_query = array(
			'relation' => 'AND',
			// Active check: is_active = 1 OR is_active not set (defaults to true).
			array(
				'relation' => 'OR',
				array(
					'key'     => 'parish_mass_time_is_active',
					'value'   => '1',
					'compare' => '=',
				),
				array(
					'key'     => 'parish_mass_time_is_active',
					'compare' => 'NOT EXISTS',
				),
			),
			// Recurring check.
			array(
				'key'     => 'parish_mass_time_is_recurring',
				'value'   => '1',
				'compare' => '=',
			),
		);

		if ( $church_id > 0 ) {
			$meta_query[] = array(
				'relation' => 'OR',
				array(
					'key'     => 'parish_mass_time_church_id',
					'value'   => $church_id,
					'compare' => '=',
					'type'    => 'NUMERIC',
				),
				array(
					'key'     => 'parish_mass_time_church_id',
					'value'   => '0',
					'compare' => '=',
					'type'    => 'NUMERIC',
				),
			);
		}

		$posts = get_posts(
			array(
				'post_type'      => 'parish_mass_time',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'meta_query'     => $meta_query,
			)
		);

		$event_types = $this->get_event_types();

		foreach ( $posts as $post ) {
			$recurrence = get_post_meta( $post->ID, 'parish_mass_time_recurrence', true ) ?: array();
			$type       = $recurrence['type'] ?? 'weekly';

			// Include monthly ordinal and yearly as "special".
			if ( 'monthly_ordinal' !== $type && 'yearly' !== $type ) {
				continue;
			}

			$start_datetime  = get_post_meta( $post->ID, 'parish_mass_time_start_datetime', true );
			$lit_type        = get_post_meta( $post->ID, 'parish_mass_time_liturgical_type', true ) ?: 'mass';
			$is_livestreamed = (bool) get_post_meta( $post->ID, 'parish_mass_time_is_livestreamed', true );
			$livestream_url  = get_post_meta( $post->ID, 'parish_mass_time_livestream_url', true );
			$notes           = get_post_meta( $post->ID, 'parish_mass_time_notes', true );

			$time = '';
			if ( $start_datetime ) {
				$ts = strtotime( $start_datetime );
				if ( $ts ) {
					$time = wp_date( 'H:i', $ts );
				}
			}

			$description = '';
			if ( 'monthly_ordinal' === $type ) {
				$ordinal = ucfirst( $recurrence['ordinal'] ?? 'first' );
				$day     = $recurrence['ordinal_day'] ?? 'Friday';
				$description = sprintf( '%s %s of the Month', $ordinal, $day );
			} elseif ( 'yearly' === $type ) {
				$month = absint( $recurrence['month'] ?? 1 );
				$day   = absint( $recurrence['day_of_month'] ?? 1 );
				$description = wp_date( 'F j', strtotime( "2024-{$month}-{$day}" ) );
			}

			$events[] = array(
				'post_id'         => $post->ID,
				'title'           => $post->post_title,
				'time'            => $time,
				'type'            => $lit_type,
				'type_label'      => $event_types[ $lit_type ] ?? ucfirst( $lit_type ),
				'description'     => $description,
				'recurrence_type' => $type,
				'is_livestreamed' => $is_livestreamed,
				'livestream_url'  => $livestream_url,
				'notes'           => $notes,
			);
		}

		return $events;
	}

	/**
	 * Cleanup expired overrides (legacy compatibility).
	 *
	 * @param int $days_old Overrides older than this many days will be deleted.
	 */
	public function cleanup_expired_overrides( int $days_old = 30 ): void {
		// This is a placeholder for legacy compatibility.
		// The new CPT-based system doesn't use overrides in the same way.
	}

	/**
	 * Cancel a single occurrence of a recurring event.
	 * Adds the date to the exception_dates meta field.
	 *
	 * @param int    $post_id Mass Time post ID.
	 * @param string $date    Date to cancel in Y-m-d format.
	 * @return bool True on success, false if already cancelled or error.
	 */
	public static function cancel_occurrence( int $post_id, string $date ): bool {
		$exception_dates = get_post_meta( $post_id, 'parish_mass_time_exception_dates', true ) ?: array();

		// Ensure it's an array.
		if ( ! is_array( $exception_dates ) ) {
			$exception_dates = array();
		}

		// Check if already cancelled.
		if ( in_array( $date, $exception_dates, true ) ) {
			return false;
		}

		// Add to exceptions.
		$exception_dates[] = $date;

		$result = update_post_meta( $post_id, 'parish_mass_time_exception_dates', $exception_dates );

		// Clear cache on success.
		if ( $result ) {
			self::instance()->clear_cache( $post_id );
		}

		return (bool) $result;
	}

	/**
	 * Cancel an entire recurring series by marking it as inactive.
	 *
	 * @param int $post_id Mass Time post ID.
	 * @return bool True on success, false on error.
	 */
	public static function cancel_series( int $post_id ): bool {
		$result = update_post_meta( $post_id, 'parish_mass_time_is_active', false );

		// Clear cache on success.
		if ( $result ) {
			self::instance()->clear_cache( $post_id );
		}

		return (bool) $result;
	}

	/**
	 * Restore a previously cancelled occurrence.
	 * Removes the date from the exception_dates meta field.
	 *
	 * @param int    $post_id Mass Time post ID.
	 * @param string $date    Date to restore in Y-m-d format.
	 * @return bool True on success, false if not found or error.
	 */
	public static function restore_occurrence( int $post_id, string $date ): bool {
		$exception_dates = get_post_meta( $post_id, 'parish_mass_time_exception_dates', true ) ?: array();

		// Ensure it's an array.
		if ( ! is_array( $exception_dates ) ) {
			return false;
		}

		// Find and remove the date.
		$key = array_search( $date, $exception_dates, true );

		if ( false === $key ) {
			return false;
		}

		unset( $exception_dates[ $key ] );

		// Re-index array.
		$exception_dates = array_values( $exception_dates );

		$result = update_post_meta( $post_id, 'parish_mass_time_exception_dates', $exception_dates );

		// Clear cache on success.
		if ( $result ) {
			self::instance()->clear_cache( $post_id );
		}

		return (bool) $result;
	}
}
