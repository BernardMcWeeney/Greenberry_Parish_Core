<?php
/**
 * Event Time Generator for Parish Core.
 *
 * Generates concrete schedule instances from parish_event_time CPT posts.
 * Handles complex recurrence patterns, exceptions, and readings integration.
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Event Time Generator class.
 */
class Parish_Event_Time_Generator {

	/**
	 * Singleton instance.
	 *
	 * @var Parish_Event_Time_Generator|null
	 */
	private static ?Parish_Event_Time_Generator $instance = null;

	/**
	 * Cache for generated instances.
	 *
	 * @var array
	 */
	private array $cache = array();

	/**
	 * Readings cache.
	 *
	 * @var array
	 */
	private array $readings_cache = array();

	/**
	 * Event type definitions.
	 */
	public const EVENT_TYPES = array(
		'mass' => array(
			'label'       => 'Mass',
			'plural'      => 'Masses',
			'icon'        => 'dashicons-buddicons-community',
			'color'       => '#4A8391',
			'has_readings' => true,
		),
		'confession' => array(
			'label'       => 'Confession',
			'plural'      => 'Confessions',
			'icon'        => 'dashicons-heart',
			'color'       => '#8B5CF6',
			'has_readings' => false,
		),
		'adoration' => array(
			'label'       => 'Adoration',
			'plural'      => 'Adoration',
			'icon'        => 'dashicons-visibility',
			'color'       => '#FFD700',
			'has_readings' => false,
		),
		'baptism' => array(
			'label'       => 'Baptism',
			'plural'      => 'Baptisms',
			'icon'        => 'dashicons-admin-site-alt3',
			'color'       => '#06B6D4',
			'has_readings' => true,
		),
		'wedding' => array(
			'label'       => 'Wedding',
			'plural'      => 'Weddings',
			'icon'        => 'dashicons-heart',
			'color'       => '#F59E0B',
			'has_readings' => true,
		),
		'funeral' => array(
			'label'       => 'Funeral',
			'plural'      => 'Funerals',
			'icon'        => 'dashicons-minus',
			'color'       => '#1F2937',
			'has_readings' => true,
		),
		'stations' => array(
			'label'       => 'Stations of the Cross',
			'plural'      => 'Stations of the Cross',
			'icon'        => 'dashicons-plus',
			'color'       => '#7C3AED',
			'has_readings' => false,
		),
		'rosary' => array(
			'label'       => 'Rosary',
			'plural'      => 'Rosary',
			'icon'        => 'dashicons-marker',
			'color'       => '#3B82F6',
			'has_readings' => false,
		),
		'benediction' => array(
			'label'       => 'Benediction',
			'plural'      => 'Benediction',
			'icon'        => 'dashicons-awards',
			'color'       => '#F97316',
			'has_readings' => false,
		),
		'novena' => array(
			'label'       => 'Novena',
			'plural'      => 'Novenas',
			'icon'        => 'dashicons-star-filled',
			'color'       => '#EC4899',
			'has_readings' => false,
		),
		'other' => array(
			'label'       => 'Other',
			'plural'      => 'Other Services',
			'icon'        => 'dashicons-calendar',
			'color'       => '#94A3B8',
			'has_readings' => false,
		),
	);

	/**
	 * Recurrence frequency options.
	 */
	public const FREQUENCIES = array(
		'none'       => 'Does not repeat',
		'daily'      => 'Daily',
		'weekly'     => 'Weekly',
		'biweekly'   => 'Every 2 weeks',
		'monthly'    => 'Monthly',
		'bimonthly'  => 'Every 2 months',
		'yearly'     => 'Yearly',
	);

	/**
	 * Get singleton instance.
	 *
	 * @return Parish_Event_Time_Generator
	 */
	public static function instance(): Parish_Event_Time_Generator {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private constructor.
	 */
	private function __construct() {}

	/**
	 * Generate schedule for a date range.
	 *
	 * @param string $start_date Start date (Y-m-d).
	 * @param string $end_date   End date (Y-m-d).
	 * @param array  $filters    Optional filters.
	 * @return array Array of schedule instances.
	 */
	public function generate( string $start_date, string $end_date, array $filters = array() ): array {
		$cache_key = md5( $start_date . $end_date . wp_json_encode( $filters ) );

		if ( isset( $this->cache[ $cache_key ] ) ) {
			return $this->cache[ $cache_key ];
		}

		// Get all active event times
		$event_times = $this->get_event_times( $filters );
		$instances   = array();

		// Pre-fetch readings for the date range
		$readings = $this->get_readings_for_range( $start_date, $end_date );

		// Pre-fetch feast days
		$feast_days = $this->get_feast_days_for_range( $start_date, $end_date );

		foreach ( $event_times as $event_time ) {
			$event_instances = $this->generate_instances_for_event(
				$event_time,
				$start_date,
				$end_date,
				$readings,
				$feast_days
			);

			$instances = array_merge( $instances, $event_instances );
		}

		// Sort by date and time
		usort( $instances, function ( $a, $b ) {
			$date_cmp = strcmp( $a['date'], $b['date'] );
			if ( 0 !== $date_cmp ) {
				return $date_cmp;
			}
			return strcmp( $a['time'], $b['time'] );
		} );

		$this->cache[ $cache_key ] = $instances;

		return $instances;
	}

	/**
	 * Get event times from database.
	 *
	 * @param array $filters Filters to apply.
	 * @return array Event time posts with meta.
	 */
	private function get_event_times( array $filters = array() ): array {
		$args = array(
			'post_type'      => 'parish_event_time',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'meta_query'     => array(
				array(
					'key'     => 'parish_is_active',
					'value'   => '1',
					'compare' => '=',
				),
			),
		);

		// Filter by church
		if ( ! empty( $filters['church_id'] ) ) {
			$args['meta_query'][] = array(
				'key'     => 'parish_church_id',
				'value'   => absint( $filters['church_id'] ),
				'compare' => '=',
				'type'    => 'NUMERIC',
			);
		}

		// Filter by event type
		if ( ! empty( $filters['type'] ) ) {
			$types = is_array( $filters['type'] ) ? $filters['type'] : array( $filters['type'] );
			if ( ! in_array( 'all', $types, true ) ) {
				$args['meta_query'][] = array(
					'key'     => 'parish_event_type',
					'value'   => $types,
					'compare' => 'IN',
				);
			}
		}

		// Filter by livestream
		if ( isset( $filters['livestream'] ) && $filters['livestream'] ) {
			$args['meta_query'][] = array(
				'key'     => 'parish_livestream_enabled',
				'value'   => '1',
				'compare' => '=',
			);
		}

		$posts = get_posts( $args );

		return array_map( function ( $post ) {
			return $this->hydrate_event_time( $post );
		}, $posts );
	}

	/**
	 * Hydrate event time post with all meta.
	 *
	 * @param WP_Post $post Post object.
	 * @return array Hydrated event time data.
	 */
	private function hydrate_event_time( WP_Post $post ): array {
		$meta = get_post_meta( $post->ID );

		return array(
			'id'                   => $post->ID,
			'title'                => $post->post_title,
			'church_id'            => (int) ( $meta['parish_church_id'][0] ?? 0 ),
			'event_type'           => $meta['parish_event_type'][0] ?? 'mass',
			'start_datetime'       => $meta['parish_start_datetime'][0] ?? '',
			'duration_minutes'     => (int) ( $meta['parish_duration_minutes'][0] ?? 60 ),
			'timezone'             => $meta['parish_timezone'][0] ?? wp_timezone_string(),
			'is_recurring'         => (bool) ( $meta['parish_is_recurring'][0] ?? false ),
			'recurrence_rule'      => $meta['parish_recurrence_rule'][0] ?? '',
			'recurrence_end_type'  => $meta['parish_recurrence_end_type'][0] ?? 'never',
			'recurrence_end_date'  => $meta['parish_recurrence_end_date'][0] ?? '',
			'recurrence_count'     => (int) ( $meta['parish_recurrence_count'][0] ?? 0 ),
			'exception_dates'      => json_decode( $meta['parish_exception_dates'][0] ?? '[]', true ),
			'livestream_enabled'   => (bool) ( $meta['parish_livestream_enabled'][0] ?? false ),
			'livestream_mode'      => $meta['parish_livestream_mode'][0] ?? 'link',
			'livestream_url'       => $meta['parish_livestream_url'][0] ?? '',
			'livestream_embed'     => $meta['parish_livestream_embed'][0] ?? '',
			'livestream_provider'  => $meta['parish_livestream_provider'][0] ?? '',
			'intentions'           => $meta['parish_intentions'][0] ?? '',
			'notes'                => $meta['parish_notes'][0] ?? '',
			'readings_mode'        => $meta['parish_readings_mode'][0] ?? 'auto',
			'readings_override'    => $meta['parish_readings_override'][0] ?? '',
			'liturgical_rite'      => $meta['parish_liturgical_rite'][0] ?? 'roman',
			'liturgical_form'      => $meta['parish_liturgical_form'][0] ?? 'ordinary',
			'language'             => $meta['parish_language'][0] ?? '',
			'linked_mass_id'       => (int) ( $meta['parish_linked_mass_id'][0] ?? 0 ),
			'is_special'           => (bool) ( $meta['parish_is_special'][0] ?? false ),
			'display_priority'     => (int) ( $meta['parish_display_priority'][0] ?? 0 ),
		);
	}

	/**
	 * Generate instances for a single event time.
	 *
	 * @param array  $event_time Event time data.
	 * @param string $start_date Range start date.
	 * @param string $end_date   Range end date.
	 * @param array  $readings   Pre-fetched readings.
	 * @param array  $feast_days Pre-fetched feast days.
	 * @return array Generated instances.
	 */
	private function generate_instances_for_event(
		array $event_time,
		string $start_date,
		string $end_date,
		array $readings,
		array $feast_days
	): array {
		$instances = array();

		// Parse start datetime
		try {
			$start_dt = new DateTime( $event_time['start_datetime'], new DateTimeZone( $event_time['timezone'] ?: 'UTC' ) );
		} catch ( Exception $e ) {
			return array();
		}

		$time_str = $start_dt->format( 'H:i' );

		if ( ! $event_time['is_recurring'] ) {
			// Single event - check if in range
			$event_date = $start_dt->format( 'Y-m-d' );
			if ( $event_date >= $start_date && $event_date <= $end_date ) {
				$instances[] = $this->build_instance(
					$event_time,
					$event_date,
					$time_str,
					$readings[ $event_date ] ?? null,
					$feast_days[ $event_date ] ?? null
				);
			}
		} else {
			// Recurring event - generate dates
			$recurrence_rule = json_decode( $event_time['recurrence_rule'], true ) ?: array();
			$exception_dates = $event_time['exception_dates'] ?: array();

			$dates = $this->get_recurrence_dates(
				$recurrence_rule,
				$start_dt,
				$start_date,
				$end_date,
				$event_time['recurrence_end_type'],
				$event_time['recurrence_end_date'],
				$event_time['recurrence_count'],
				$exception_dates
			);

			foreach ( $dates as $date ) {
				$date_str = $date->format( 'Y-m-d' );
				$instances[] = $this->build_instance(
					$event_time,
					$date_str,
					$time_str,
					$readings[ $date_str ] ?? null,
					$feast_days[ $date_str ] ?? null
				);
			}
		}

		return $instances;
	}

	/**
	 * Get recurrence dates within a range.
	 *
	 * @param array    $rule           Recurrence rule.
	 * @param DateTime $event_start    Event start datetime.
	 * @param string   $range_start    Range start date.
	 * @param string   $range_end      Range end date.
	 * @param string   $end_type       End type (never, until, count).
	 * @param string   $end_date       End date for recurrence.
	 * @param int      $count          Number of occurrences.
	 * @param array    $exceptions     Exception dates.
	 * @return array Array of DateTime objects.
	 */
	private function get_recurrence_dates(
		array $rule,
		DateTime $event_start,
		string $range_start,
		string $range_end,
		string $end_type,
		string $end_date,
		int $count,
		array $exceptions
	): array {
		$dates = array();
		$frequency = $rule['frequency'] ?? 'weekly';
		$days = $rule['days'] ?? array(); // For weekly: ['Monday', 'Wednesday']
		$day_of_month = $rule['day_of_month'] ?? null;
		$position = $rule['position'] ?? null; // first, second, third, fourth, last
		$interval = (int) ( $rule['interval'] ?? 1 );

		$current = new DateTime( $range_start );
		$end = new DateTime( $range_end );

		// Apply recurrence end constraints
		if ( 'until' === $end_type && ! empty( $end_date ) ) {
			$recurrence_end = new DateTime( $end_date );
			if ( $recurrence_end < $end ) {
				$end = $recurrence_end;
			}
		}

		$occurrence_count = 0;
		$max_count = ( 'count' === $end_type && $count > 0 ) ? $count : PHP_INT_MAX;

		// For counting, we need to start from event start
		$counting_start = clone $event_start;

		while ( $current <= $end && $occurrence_count < $max_count ) {
			$date_str = $current->format( 'Y-m-d' );

			// Check if this date matches the pattern
			$matches = $this->date_matches_recurrence(
				$current,
				$frequency,
				$event_start,
				$days,
				$day_of_month,
				$position,
				$interval
			);

			if ( $matches ) {
				// Check if date is on or after event start
				if ( $current >= $event_start ) {
					// Count this occurrence
					$occurrence_count++;

					// Check if not an exception
					if ( ! in_array( $date_str, $exceptions, true ) ) {
						// Check if within range
						if ( $date_str >= $range_start ) {
							$dates[] = clone $current;
						}
					}
				}
			}

			$current->modify( '+1 day' );
		}

		return $dates;
	}

	/**
	 * Check if a date matches recurrence pattern.
	 *
	 * @param DateTime $date        Date to check.
	 * @param string   $frequency   Recurrence frequency.
	 * @param DateTime $event_start Event start for interval calculation.
	 * @param array    $days        Days of week for weekly.
	 * @param int|null $day_of_month Day of month for monthly.
	 * @param string|null $position Position for monthly (first, last, etc).
	 * @param int      $interval    Interval (every N weeks/months).
	 * @return bool True if matches.
	 */
	private function date_matches_recurrence(
		DateTime $date,
		string $frequency,
		DateTime $event_start,
		array $days,
		?int $day_of_month,
		?string $position,
		int $interval
	): bool {
		switch ( $frequency ) {
			case 'daily':
				$diff_days = (int) $event_start->diff( $date )->days;
				return ( $diff_days % $interval ) === 0;

			case 'weekly':
				// Check if correct day of week
				$day_name = $date->format( 'l' );
				// If no days specified, use the event start day
				$check_days = ! empty( $days ) ? $days : array( $event_start->format( 'l' ) );
				if ( ! in_array( $day_name, $check_days, true ) ) {
					return false;
				}
				// Check interval (every N weeks)
				if ( $interval > 1 ) {
					$weeks_diff = (int) floor( $event_start->diff( $date )->days / 7 );
					if ( ( $weeks_diff % $interval ) !== 0 ) {
						return false;
					}
				}
				return true;

			case 'biweekly':
				$day_name = $date->format( 'l' );
				// If no days specified, use the event start day
				$check_days = ! empty( $days ) ? $days : array( $event_start->format( 'l' ) );
				if ( ! in_array( $day_name, $check_days, true ) ) {
					return false;
				}
				$weeks_diff = (int) floor( $event_start->diff( $date )->days / 7 );
				return ( $weeks_diff % 2 ) === 0;

			case 'monthly':
				// Check month interval
				$months_diff = ( (int) $date->format( 'Y' ) - (int) $event_start->format( 'Y' ) ) * 12
					+ (int) $date->format( 'n' ) - (int) $event_start->format( 'n' );
				if ( ( $months_diff % $interval ) !== 0 ) {
					return false;
				}

				// Positional monthly (First Friday, Last Sunday, etc)
				if ( $position && ! empty( $days ) ) {
					return $this->matches_positional_monthly( $date, $position, $days[0] ?? '' );
				}

				// Day of month
				if ( $day_of_month ) {
					return (int) $date->format( 'j' ) === $day_of_month;
				}

				// Default: same day of month as event start
				return (int) $date->format( 'j' ) === (int) $event_start->format( 'j' );

			case 'bimonthly':
				$months_diff = ( (int) $date->format( 'Y' ) - (int) $event_start->format( 'Y' ) ) * 12
					+ (int) $date->format( 'n' ) - (int) $event_start->format( 'n' );
				if ( ( $months_diff % 2 ) !== 0 ) {
					return false;
				}
				return (int) $date->format( 'j' ) === (int) $event_start->format( 'j' );

			case 'yearly':
				// Check year interval
				$years_diff = (int) $date->format( 'Y' ) - (int) $event_start->format( 'Y' );
				if ( ( $years_diff % $interval ) !== 0 ) {
					return false;
				}
				// Same month and day
				return $date->format( 'm-d' ) === $event_start->format( 'm-d' );

			default:
				return false;
		}
	}

	/**
	 * Check if date matches positional monthly pattern (First Friday, etc).
	 *
	 * @param DateTime $date     Date to check.
	 * @param string   $position Position (first, second, third, fourth, last).
	 * @param string   $day      Day name.
	 * @return bool True if matches.
	 */
	private function matches_positional_monthly( DateTime $date, string $position, string $day ): bool {
		if ( $date->format( 'l' ) !== $day ) {
			return false;
		}

		$day_of_month = (int) $date->format( 'j' );
		$occurrence = (int) ceil( $day_of_month / 7 );

		$position_map = array(
			'first'  => 1,
			'second' => 2,
			'third'  => 3,
			'fourth' => 4,
			'fifth'  => 5,
		);

		if ( 'last' === $position ) {
			// Check if next week is same month
			$next_week = ( clone $date )->modify( '+7 days' );
			return $next_week->format( 'm' ) !== $date->format( 'm' );
		}

		return $occurrence === ( $position_map[ $position ] ?? 1 );
	}

	/**
	 * Build a single instance.
	 *
	 * @param array       $event_time Event time data.
	 * @param string      $date       Date string (Y-m-d).
	 * @param string      $time       Time string (H:i).
	 * @param array|null  $readings   Readings for this date.
	 * @param array|null  $feast_day  Feast day info.
	 * @return array Instance data.
	 */
	private function build_instance(
		array $event_time,
		string $date,
		string $time,
		?array $readings,
		?array $feast_day
	): array {
		$event_type = $event_time['event_type'];
		$type_info = self::EVENT_TYPES[ $event_type ] ?? self::EVENT_TYPES['other'];

		// Calculate end time
		$end_time = '';
		if ( $event_time['duration_minutes'] > 0 ) {
			try {
				$start = new DateTime( $date . ' ' . $time );
				$start->modify( '+' . $event_time['duration_minutes'] . ' minutes' );
				$end_time = $start->format( 'H:i' );
			} catch ( Exception $e ) {
				// Ignore
			}
		}

		// Handle readings based on mode
		$instance_readings = null;
		if ( $type_info['has_readings'] ) {
			switch ( $event_time['readings_mode'] ) {
				case 'auto':
					$instance_readings = $readings;
					break;
				case 'override':
					$instance_readings = json_decode( $event_time['readings_override'], true );
					break;
				case 'none':
				default:
					$instance_readings = null;
			}
		}

		// Get church info
		$church_name = '';
		$church_slug = '';
		if ( $event_time['church_id'] > 0 ) {
			$church = get_post( $event_time['church_id'] );
			if ( $church ) {
				$church_name = $church->post_title;
				$church_slug = $church->post_name;
			}
		}

		// Parse intentions if JSON
		$intentions = $event_time['intentions'];
		if ( ! empty( $intentions ) && is_string( $intentions ) ) {
			$decoded = json_decode( $intentions, true );
			if ( json_last_error() === JSON_ERROR_NONE ) {
				$intentions = $decoded;
			}
		}

		return array(
			'id'                  => $event_time['id'] . '_' . str_replace( '-', '', $date ),
			'event_time_id'       => $event_time['id'],
			'title'               => $event_time['title'],
			'date'                => $date,
			'date_formatted'      => date_i18n( get_option( 'date_format' ), strtotime( $date ) ),
			'day_name'            => date_i18n( 'l', strtotime( $date ) ),
			'time'                => $time,
			'time_formatted'      => date_i18n( get_option( 'time_format' ), strtotime( $date . ' ' . $time ) ),
			'end_time'            => $end_time,
			'end_time_formatted'  => $end_time ? date_i18n( get_option( 'time_format' ), strtotime( $date . ' ' . $end_time ) ) : '',
			'duration_minutes'    => $event_time['duration_minutes'],
			'event_type'          => $event_type,
			'event_type_label'    => $type_info['label'],
			'event_type_color'    => $type_info['color'],
			'event_type_icon'     => $type_info['icon'],
			'church_id'           => $event_time['church_id'],
			'church_name'         => $church_name,
			'church_slug'         => $church_slug,
			'livestream'          => array(
				'enabled'  => $event_time['livestream_enabled'],
				'mode'     => $event_time['livestream_mode'],
				'url'      => $event_time['livestream_url'],
				'embed'    => $event_time['livestream_embed'],
				'provider' => $event_time['livestream_provider'],
			),
			'intentions'          => $intentions,
			'notes'               => $event_time['notes'],
			'readings'            => $instance_readings,
			'feast_day'           => $feast_day,
			'liturgical'          => array(
				'rite'     => $event_time['liturgical_rite'],
				'form'     => $event_time['liturgical_form'],
				'language' => $event_time['language'],
			),
			'linked_mass_id'      => $event_time['linked_mass_id'],
			'is_special'          => $event_time['is_special'],
			'is_recurring'        => $event_time['is_recurring'],
			'display_priority'    => $event_time['display_priority'],
		);
	}

	/**
	 * Get readings for a date range (with caching).
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date   End date.
	 * @return array Readings keyed by date.
	 */
	private function get_readings_for_range( string $start_date, string $end_date ): array {
		$readings = array();

		if ( ! class_exists( 'Parish_Readings' ) ) {
			return $readings;
		}

		$current = new DateTime( $start_date );
		$end = new DateTime( $end_date );

		while ( $current <= $end ) {
			$date_str = $current->format( 'Y-m-d' );
			$cache_key = 'parish_readings_' . $date_str;

			// Check transient cache
			$cached = get_transient( $cache_key );
			if ( false !== $cached ) {
				$readings[ $date_str ] = $cached;
			} else {
				// Fetch readings for this date
				$reading_data = $this->fetch_readings_for_date( $date_str );
				if ( $reading_data ) {
					$readings[ $date_str ] = $reading_data;
					// Cache for 24 hours
					set_transient( $cache_key, $reading_data, DAY_IN_SECONDS );
				}
			}

			$current->modify( '+1 day' );
		}

		return $readings;
	}

	/**
	 * Fetch readings for a specific date.
	 *
	 * @param string $date Date (Y-m-d).
	 * @return array|null Readings data or null.
	 */
	private function fetch_readings_for_date( string $date ): ?array {
		if ( ! class_exists( 'Parish_Readings' ) ) {
			return null;
		}

		$readings = Parish_Readings::instance();

		// The readings API caches today's readings - only return data for today
		$today = current_time( 'Y-m-d' );
		if ( $date !== $today ) {
			return null; // Readings API only has today's readings cached
		}

		// Get mass reading details
		$data = $readings->get_reading( 'mass_reading_details' );

		if ( empty( $data ) ) {
			return null;
		}

		return array(
			'first_reading'  => $data['first_reading'] ?? null,
			'psalm'          => $data['responsorial_psalm'] ?? null,
			'second_reading' => $data['second_reading'] ?? null,
			'gospel'         => $data['gospel'] ?? null,
			'source'         => $data['source'] ?? 'api',
		);
	}

	/**
	 * Get feast days for a date range.
	 *
	 * Uses Parish_Feast_Day_Service if available, otherwise falls back to Parish_Readings
	 * which only has today's feast day cached.
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date   End date.
	 * @return array Feast days keyed by date.
	 */
	private function get_feast_days_for_range( string $start_date, string $end_date ): array {
		// Use dedicated feast day service if available
		if ( class_exists( 'Parish_Feast_Day_Service' ) ) {
			return Parish_Feast_Day_Service::instance()->get_feast_days_for_range( $start_date, $end_date );
		}

		// Fall back to Parish_Readings - only provides today's data
		if ( class_exists( 'Parish_Readings' ) ) {
			$feast_days = array();
			$today = current_time( 'Y-m-d' );

			// Only fetch for today since the Readings API only caches today's data
			if ( $today >= $start_date && $today <= $end_date ) {
				$data = Parish_Readings::instance()->get_reading( 'feast_day_details' );
				if ( ! empty( $data ) && isset( $data['celebrations'] ) ) {
					// Extract first celebration for display
					$celebration = $data['celebrations'][0] ?? array();
					$feast_days[ $today ] = array(
						'title'  => $celebration['title'] ?? '',
						'rank'   => $celebration['rank'] ?? '',
						'colour' => $celebration['colour'] ?? '',
					);
				}
			}

			return $feast_days;
		}

		return array();
	}

	/**
	 * Generate today's schedule.
	 *
	 * @param array $filters Optional filters.
	 * @return array Today's schedule instances.
	 */
	public function generate_today( array $filters = array() ): array {
		$today = current_time( 'Y-m-d' );
		return $this->generate( $today, $today, $filters );
	}

	/**
	 * Generate this week's schedule.
	 *
	 * @param array $filters Optional filters.
	 * @return array This week's schedule instances.
	 */
	public function generate_week( array $filters = array() ): array {
		$start = current_time( 'Y-m-d' );
		$end = date( 'Y-m-d', strtotime( '+6 days', strtotime( $start ) ) );
		return $this->generate( $start, $end, $filters );
	}

	/**
	 * Generate schedule for N days.
	 *
	 * @param int   $days    Number of days.
	 * @param array $filters Optional filters.
	 * @return array Schedule instances.
	 */
	public function generate_days( int $days, array $filters = array() ): array {
		$start = current_time( 'Y-m-d' );
		$end = date( 'Y-m-d', strtotime( '+' . ( $days - 1 ) . ' days', strtotime( $start ) ) );
		return $this->generate( $start, $end, $filters );
	}

	/**
	 * Generate schedule for a church.
	 *
	 * @param int    $church_id Church post ID.
	 * @param int    $days      Number of days to generate.
	 * @param array  $filters   Additional filters.
	 * @return array Schedule instances.
	 */
	public function generate_for_church( int $church_id, int $days = 14, array $filters = array() ): array {
		$filters['church_id'] = $church_id;
		return $this->generate_days( $days, $filters );
	}

	/**
	 * Clear the instance cache.
	 *
	 * @return void
	 */
	public function clear_cache(): void {
		$this->cache = array();
	}

	/**
	 * Get event type info.
	 *
	 * @param string $type Event type slug.
	 * @return array|null Type info or null.
	 */
	public static function get_event_type( string $type ): ?array {
		return self::EVENT_TYPES[ $type ] ?? null;
	}

	/**
	 * Get all event types.
	 *
	 * @return array All event types.
	 */
	public static function get_event_types(): array {
		return self::EVENT_TYPES;
	}

	/**
	 * Get all frequencies.
	 *
	 * @return array All frequency options.
	 */
	public static function get_frequencies(): array {
		return self::FREQUENCIES;
	}

	/**
	 * Group instances by date.
	 *
	 * @param array $instances Schedule instances.
	 * @return array Instances grouped by date.
	 */
	public static function group_by_date( array $instances ): array {
		$grouped = array();

		foreach ( $instances as $instance ) {
			$date = $instance['date'];
			if ( ! isset( $grouped[ $date ] ) ) {
				$grouped[ $date ] = array(
					'date'           => $date,
					'date_formatted' => $instance['date_formatted'],
					'day_name'       => $instance['day_name'],
					'events'         => array(),
				);
			}
			$grouped[ $date ]['events'][] = $instance;
		}

		return array_values( $grouped );
	}

	/**
	 * Group instances by church.
	 *
	 * @param array $instances Schedule instances.
	 * @return array Instances grouped by church.
	 */
	public static function group_by_church( array $instances ): array {
		$grouped = array();

		foreach ( $instances as $instance ) {
			$church_id = $instance['church_id'];
			if ( ! isset( $grouped[ $church_id ] ) ) {
				$grouped[ $church_id ] = array(
					'church_id'   => $church_id,
					'church_name' => $instance['church_name'],
					'church_slug' => $instance['church_slug'],
					'events'      => array(),
				);
			}
			$grouped[ $church_id ]['events'][] = $instance;
		}

		return array_values( $grouped );
	}
}
