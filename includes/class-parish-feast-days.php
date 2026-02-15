<?php
/**
 * Feast Days API Integration.
 *
 * Integrates with calapi.inadiutorium.cz to fetch Catholic liturgical calendar data
 * and populate parish events with feast days.
 *
 * @link http://calapi.inadiutorium.cz/api-doc
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Parish_Feast_Days class.
 */
class Parish_Feast_Days {

	/**
	 * Singleton instance.
	 *
	 * @var Parish_Feast_Days|null
	 */
	private static ?Parish_Feast_Days $instance = null;

	/**
	 * API roots.
	 */
	private const API_ROOTS = array(
		'http://calapi.inadiutorium.cz/api/v0/en/calendars',
		'https://calapi.inadiutorium.cz/api/v0/en/calendars',
	);

	/**
	 * Calendar IDs to try in order.
	 */
	private const API_CALENDARS = array( 'default', 'general-en' );

	/**
	 * Cache duration in seconds (24 hours).
	 */
	private const CACHE_DURATION = 86400;

	/**
	 * Get singleton instance.
	 */
	public static function instance(): Parish_Feast_Days {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		// Register REST endpoints.
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );

		// Schedule daily sync.
		add_action( 'init', array( $this, 'schedule_sync' ) );
		add_action( 'parish_sync_feast_days', array( $this, 'run_scheduled_sync' ) );
	}

	/**
	 * Register REST API routes.
	 */
	public function register_rest_routes(): void {
		register_rest_route(
			'parish/v1',
			'/feast-days',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'rest_get_feast_days' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'year'  => array(
						'type'              => 'integer',
						'default'           => (int) wp_date( 'Y' ),
						'sanitize_callback' => 'absint',
					),
					'month' => array(
						'type'              => 'integer',
						'default'           => (int) wp_date( 'n' ),
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		register_rest_route(
			'parish/v1',
			'/feast-days/sync',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_sync_feast_days' ),
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
				'args'                => array(
					'months_ahead' => array(
						'type'              => 'integer',
						'default'           => 3,
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		register_rest_route(
			'parish/v1',
			'/feast-days/(?P<date>[0-9]{4}-[0-9]{2}-[0-9]{2})',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'rest_get_feast_day' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Schedule daily sync if not already scheduled and setting is enabled.
	 */
	public function schedule_sync(): void {
		$sync_enabled = Parish_Core::get_setting( 'feast_days_sync_enabled', false );

		if ( $sync_enabled ) {
			if ( ! wp_next_scheduled( 'parish_sync_feast_days' ) ) {
				wp_schedule_event( strtotime( 'tomorrow 02:00:00' ), 'daily', 'parish_sync_feast_days' );
			}
		} else {
			// Unschedule if sync is disabled.
			$timestamp = wp_next_scheduled( 'parish_sync_feast_days' );
			if ( $timestamp ) {
				wp_unschedule_event( $timestamp, 'parish_sync_feast_days' );
			}
		}
	}

	/**
	 * Run scheduled sync using the configured months-ahead setting.
	 */
	public function run_scheduled_sync(): void {
		$months_ahead = absint( Parish_Core::get_setting( 'feast_days_months_ahead', 3 ) );
		if ( $months_ahead < 1 ) {
			$months_ahead = 3;
		}
		$this->sync_feast_days_to_events( $months_ahead );
	}

	/**
	 * Check if feast day sync is enabled.
	 *
	 * @return bool True if enabled.
	 */
	public static function is_sync_enabled(): bool {
		return (bool) Parish_Core::get_setting( 'feast_days_sync_enabled', false );
	}

	/**
	 * Fetch feast day data for a specific date from the API.
	 *
	 * @param string $date Date in Y-m-d format.
	 * @return array|null Feast day data or null on error.
	 */
	public function fetch_feast_day( string $date ): ?array {
		$cache_key = 'parish_feast_' . $date;
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		// Parse date components.
		$parts = explode( '-', $date );
		if ( count( $parts ) !== 3 ) {
			return null;
		}

		list( $year, $month, $day ) = $parts;

		$data   = null;
		$errors = array();
		foreach ( $this->build_day_urls( $year, $month, $day ) as $url ) {
			$response = $this->request_json( $url, 10 );
			if ( $response['success'] ) {
				$data = $response['data'];
				break;
			}
			$errors[] = $response['error'];
		}

		if ( ! is_array( $data ) ) {
			if ( ! empty( $errors ) ) {
				error_log( 'Parish Feast Days: Day API failed - ' . implode( ' | ', $errors ) );
			}
			return null;
		}

		// Normalize the data structure.
		$feast_data = $this->normalize_feast_data( $data, $date );

		// Cache for 24 hours.
		set_transient( $cache_key, $feast_data, self::CACHE_DURATION );

		return $feast_data;
	}

	/**
	 * Fetch feast days for a month.
	 *
	 * @param int $year  Year.
	 * @param int $month Month (1-12).
	 * @return array Array of feast days.
	 */
	public function fetch_month( int $year, int $month ): array {
		$cache_key = sprintf( 'parish_feasts_%d_%02d', $year, $month );
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$data   = null;
		$errors = array();
		foreach ( $this->build_month_urls( $year, $month ) as $url ) {
			$response = $this->request_json( $url, 15 );
			if ( $response['success'] ) {
				$data = $response['data'];
				break;
			}
			$errors[] = $response['error'];
		}

		if ( ! is_array( $data ) ) {
			if ( ! empty( $errors ) ) {
				error_log( 'Parish Feast Days: Monthly API failed - ' . implode( ' | ', $errors ) );
			}
			return array();
		}

		$feast_days = array();

		// The API usually returns an array of days, but some variants wrap it.
		$days = $this->extract_month_days( $data );
		foreach ( $days as $day_data ) {
			if ( ! is_array( $day_data ) ) {
				continue;
			}

			$date = $this->resolve_api_day_date( $day_data, $year, $month );
			if ( empty( $date ) ) {
				continue;
			}

			$feast_data = $this->normalize_feast_data( $day_data, $date );

			if ( ! empty( $feast_data['celebrations'] ) ) {
				$feast_days[ $date ] = $feast_data;
			}
		}

		// Cache for 24 hours.
		set_transient( $cache_key, $feast_days, self::CACHE_DURATION );

		return $feast_days;
	}

	/**
	 * Build candidate URLs for a specific day.
	 *
	 * @param string $year  Year.
	 * @param string $month Month.
	 * @param string $day   Day.
	 * @return array
	 */
	private function build_day_urls( string $year, string $month, string $day ): array {
		$urls = array();
		foreach ( self::API_ROOTS as $root ) {
			foreach ( self::API_CALENDARS as $calendar ) {
				$urls[] = sprintf( '%s/%s/%d/%d/%d', $root, $calendar, (int) $year, (int) $month, (int) $day );
			}
		}
		return $urls;
	}

	/**
	 * Build candidate URLs for a month query.
	 *
	 * @param int $year  Year.
	 * @param int $month Month.
	 * @return array
	 */
	private function build_month_urls( int $year, int $month ): array {
		$urls = array();
		foreach ( self::API_ROOTS as $root ) {
			foreach ( self::API_CALENDARS as $calendar ) {
				$urls[] = sprintf( '%s/%s/%d/%d', $root, $calendar, $year, $month );
			}
		}
		return $urls;
	}

	/**
	 * Request and decode JSON from API endpoint.
	 *
	 * @param string $url     URL to request.
	 * @param int    $timeout Request timeout in seconds.
	 * @return array{success:bool,data:mixed,error:string}
	 */
	private function request_json( string $url, int $timeout ): array {
		$response = wp_remote_get(
			$url,
			array(
				'timeout'     => $timeout,
				'redirection' => 5,
				'headers'     => array( 'Accept' => 'application/json' ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'data'    => null,
				'error'   => sprintf( 'Request error for %s: %s', $url, $response->get_error_message() ),
			);
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== (int) $code ) {
			return array(
				'success' => false,
				'data'    => null,
				'error'   => sprintf( 'HTTP %d for %s', (int) $code, $url ),
			);
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );
		if ( ! is_array( $data ) ) {
			return array(
				'success' => false,
				'data'    => null,
				'error'   => sprintf( 'Invalid JSON for %s', $url ),
			);
		}

		return array(
			'success' => true,
			'data'    => $data,
			'error'   => '',
		);
	}

	/**
	 * Normalize month response shape into a plain list of day objects.
	 *
	 * @param array $data Raw API response.
	 * @return array
	 */
	private function extract_month_days( array $data ): array {
		if ( isset( $data[0] ) && is_array( $data[0] ) ) {
			return $data;
		}

		if ( isset( $data['days'] ) && is_array( $data['days'] ) ) {
			return $data['days'];
		}

		return array();
	}

	/**
	 * Resolve a normalized Y-m-d date from a day object.
	 *
	 * @param array $day_data Day payload from API.
	 * @param int   $year     Requested year.
	 * @param int   $month    Requested month.
	 * @return string
	 */
	private function resolve_api_day_date( array $day_data, int $year, int $month ): string {
		$raw_date = isset( $day_data['date'] ) ? sanitize_text_field( (string) $day_data['date'] ) : '';
		if ( preg_match( '/^\d{4}-\d{1,2}-\d{1,2}$/', $raw_date ) ) {
			$parts = explode( '-', $raw_date );
			return sprintf( '%04d-%02d-%02d', (int) $parts[0], (int) $parts[1], (int) $parts[2] );
		}

		$day = isset( $day_data['day'] ) ? absint( $day_data['day'] ) : 0;
		if ( $day >= 1 && $day <= 31 ) {
			return sprintf( '%d-%02d-%02d', $year, $month, $day );
		}

		return '';
	}

	/**
	 * Normalize feast day data from API response.
	 *
	 * @param array  $data API response data.
	 * @param string $date Date string.
	 * @return array Normalized feast data.
	 */
	private function normalize_feast_data( array $data, string $date ): array {
		$celebrations = array();

		// Handle different API response structures.
		$raw_celebrations = $data['celebrations'] ?? array();

		foreach ( $raw_celebrations as $celebration ) {
			if ( ! is_array( $celebration ) ) {
				continue;
			}

			$rank     = $this->normalize_rank( $celebration['rank'] ?? '' );
			$color    = $this->normalize_color( $celebration['colour'] ?? $celebration['color'] ?? '' );
			$rank_num = isset( $celebration['rank_num'] ) && is_numeric( $celebration['rank_num'] )
				? (float) $celebration['rank_num']
				: $this->rank_to_number( $rank );

			$celebrations[] = array(
				'title'       => sanitize_text_field( $celebration['title'] ?? '' ),
				'rank'        => $rank,
				'rank_num'    => $rank_num,
				'color'       => $color,
				'season'      => sanitize_text_field( $data['season'] ?? '' ),
				'season_week' => absint( $data['season_week'] ?? 0 ),
			);
		}

		// Sort by rank (lower number = higher priority in CalAPI).
		usort( $celebrations, function ( $a, $b ) {
			return ( $a['rank_num'] ?? 99 ) <=> ( $b['rank_num'] ?? 99 );
		} );

		return array(
			'date'         => $date,
			'season'       => sanitize_text_field( $data['season'] ?? '' ),
			'season_week'  => absint( $data['season_week'] ?? 0 ),
			'weekday'      => sanitize_text_field( $data['weekday'] ?? '' ),
			'celebrations' => $celebrations,
		);
	}

	/**
	 * Normalize rank string.
	 *
	 * @param string $rank Raw rank string.
	 * @return string Normalized rank.
	 */
	private function normalize_rank( string $rank ): string {
		$rank = strtolower( trim( $rank ) );

		$rank_map = array(
			'solemnity' => 'solemnity',
			'feast'     => 'feast',
			'memorial'  => 'memorial',
			'optional'  => 'optional_memorial',
			'optional memorial' => 'optional_memorial',
			'weekday'   => 'weekday',
			'ferial'    => 'weekday',
		);

		return $rank_map[ $rank ] ?? $rank;
	}

	/**
	 * Convert rank to numeric value for sorting.
	 *
	 * @param string $rank Rank string.
	 * @return float Numeric rank value (lower = higher priority).
	 */
	private function rank_to_number( string $rank ): float {
		$ranks = array(
			'solemnity'         => 1.3,
			'feast'             => 2.0,
			'memorial'          => 3.11,
			'optional_memorial' => 3.12,
			'weekday'           => 3.13,
		);

		return $ranks[ $rank ] ?? 99.0;
	}

	/**
	 * Normalize liturgical color.
	 *
	 * @param string $color Raw color string.
	 * @return string Normalized color.
	 */
	private function normalize_color( string $color ): string {
		$color = strtolower( trim( $color ) );

		$color_map = array(
			'green'  => 'green',
			'white'  => 'white',
			'red'    => 'red',
			'violet' => 'violet',
			'purple' => 'violet',
			'rose'   => 'rose',
			'pink'   => 'rose',
			'black'  => 'black',
			'gold'   => 'gold',
		);

		return $color_map[ $color ] ?? 'green';
	}

	/**
	 * Get liturgical color hex value.
	 *
	 * @param string $color Color name.
	 * @return string Hex color code.
	 */
	public function get_color_hex( string $color ): string {
		$colors = array(
			'green'  => '#008000',
			'white'  => '#f5f5f5',
			'red'    => '#c0392b',
			'violet' => '#8e44ad',
			'rose'   => '#e8a0bf',
			'black'  => '#2c3e50',
			'gold'   => '#f1c40f',
		);

		return $colors[ $color ] ?? '#008000';
	}

	/**
	 * Sync feast days to parish events for upcoming months.
	 *
	 * @param int $months_ahead Number of months to sync (default 3).
	 * @return array Sync results.
	 */
	public function sync_feast_days_to_events( int $months_ahead = 3 ): array {
		if ( ! post_type_exists( 'parish_event' ) ) {
			return array( 'error' => 'Events post type not available.' );
		}

		$months_ahead = max( 1, $months_ahead );

		$results = array(
			'created'  => 0,
			'updated'  => 0,
			'skipped'  => 0,
			'errors'   => array(),
			'months'   => array(),
		);

		$current_year  = (int) wp_date( 'Y' );
		$current_month = (int) wp_date( 'n' );

		for ( $i = 0; $i < $months_ahead; $i++ ) {
			$month = $current_month + $i;
			$year  = $current_year;

			if ( $month > 12 ) {
				$month -= 12;
				$year++;
			}

			// Force fresh month data during sync to avoid stale empty cache blocking event creation.
			delete_transient( sprintf( 'parish_feasts_%d_%02d', $year, $month ) );
			$feast_days = $this->fetch_month( $year, $month );

			$month_key = sprintf( '%04d-%02d', $year, $month );
			$results['months'][ $month_key ] = array(
				'days'         => count( $feast_days ),
				'celebrations' => 0,
				'processed'    => 0,
			);

			if ( empty( $feast_days ) ) {
				$results['errors'][] = sprintf( 'No feast-day data returned for %s.', $month_key );
				continue;
			}

			foreach ( $feast_days as $date => $day_data ) {
				foreach ( $day_data['celebrations'] as $celebration ) {
					$results['months'][ $month_key ]['celebrations']++;

					$rank_num = isset( $celebration['rank_num'] ) && is_numeric( $celebration['rank_num'] )
						? (float) $celebration['rank_num']
						: $this->rank_to_number( $celebration['rank'] ?? '' );
					$rank = strtolower( (string) ( $celebration['rank'] ?? '' ) );
					$title = trim( (string) ( $celebration['title'] ?? '' ) );

					// Skip plain weekdays/ferial entries; keep optional memorials and above.
					if ( '' === $title || 'weekday' === $rank || 'ferial' === $rank || $rank_num >= 3.13 ) {
						continue;
					}

					$results['months'][ $month_key ]['processed']++;
					$result = $this->create_or_update_feast_event( $date, $celebration, $day_data );

					if ( 'created' === $result ) {
						$results['created']++;
					} elseif ( 'updated' === $result ) {
						$results['updated']++;
					} elseif ( 'skipped' === $result ) {
						$results['skipped']++;
					} else {
						$results['errors'][] = $result;
					}
				}
			}
		}

		$total_processed = 0;
		foreach ( $results['months'] as $month_stats ) {
			$total_processed += (int) ( $month_stats['processed'] ?? 0 );
		}
		if ( 0 === $total_processed && empty( $results['errors'] ) ) {
			$results['errors'][] = 'No processable celebrations were returned by the API for the selected period.';
		}

		return $results;
	}

	/**
	 * Create or update a feast day event.
	 *
	 * @param string $date        Date in Y-m-d format.
	 * @param array  $celebration Celebration data.
	 * @param array  $day_data    Full day data.
	 * @return string Result: 'created', 'updated', 'skipped', or error message.
	 */
	private function create_or_update_feast_event( string $date, array $celebration, array $day_data ): string {
		$title = $celebration['title'];

		if ( empty( $title ) ) {
			return 'skipped';
		}

		// Check if event already exists for this date and title.
		$existing = get_posts(
			array(
				'post_type'      => 'parish_event',
				'posts_per_page' => 1,
				'post_status'    => 'any',
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'key'   => 'parish_event_date',
						'value' => $date,
					),
					array(
						'key'   => 'parish_event_is_feast_day',
						'value' => '1',
					),
				),
				's' => $title,
			)
		);

		// Check for manual events on same date - don't override them.
		$manual_events = get_posts(
			array(
				'post_type'      => 'parish_event',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'key'   => 'parish_event_date',
						'value' => $date,
					),
					array(
						'relation' => 'OR',
						array(
							'key'     => 'parish_event_is_feast_day',
							'compare' => 'NOT EXISTS',
						),
						array(
							'key'   => 'parish_event_is_feast_day',
							'value' => '0',
						),
					),
				),
			)
		);

		// If there's a manual event on this date, we still create the feast day
		// but mark it as lower priority.
		$is_priority = empty( $manual_events );

		$post_data = array(
			'post_type'   => 'parish_event',
			'post_status' => 'publish',
			'post_title'  => sanitize_text_field( $title ),
			'post_content' => sprintf(
				/* translators: %1$s: liturgical season, %2$s: celebration rank */
				__( 'Liturgical celebration during %1$s. Rank: %2$s.', 'parish-core' ),
				ucfirst( $day_data['season'] ?? 'ordinary time' ),
				ucfirst( str_replace( '_', ' ', $celebration['rank'] ) )
			),
		);

		$meta_input = array(
			'parish_event_date'          => $date,
			'parish_event_is_feast_day'  => true,
			'parish_event_feast_rank'    => $celebration['rank'],
			'parish_event_feast_rank_num' => $celebration['rank_num'],
			'parish_event_color'         => $this->get_color_hex( $celebration['color'] ),
			'parish_event_liturgical_color' => $celebration['color'],
			'parish_event_liturgical_season' => $day_data['season'] ?? '',
			'parish_event_priority'      => $is_priority ? 0 : 10, // Lower number = higher priority.
		);

		if ( ! empty( $existing ) ) {
			// Update existing.
			$post_id = $existing[0]->ID;
			$post_data['ID'] = $post_id;

			$result = wp_update_post( $post_data, true );

			if ( is_wp_error( $result ) ) {
				return $result->get_error_message();
			}

			foreach ( $meta_input as $key => $value ) {
				update_post_meta( $post_id, $key, $value );
			}

			return 'updated';
		} else {
			// Create new.
			$post_data['meta_input'] = $meta_input;

			$post_id = wp_insert_post( $post_data, true );

			if ( is_wp_error( $post_id ) ) {
				return $post_id->get_error_message();
			}

			// Add to feast_day taxonomy if it exists.
			if ( taxonomy_exists( 'parish_feast_day' ) ) {
				wp_set_object_terms( $post_id, $title, 'parish_feast_day', true );
			}

			return 'created';
		}
	}

	/**
	 * REST callback: Get feast days for a month.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function rest_get_feast_days( \WP_REST_Request $request ): \WP_REST_Response {
		$year  = $request->get_param( 'year' );
		$month = $request->get_param( 'month' );

		$feast_days = $this->fetch_month( $year, $month );

		return rest_ensure_response(
			array(
				'year'       => $year,
				'month'      => $month,
				'feast_days' => $feast_days,
			)
		);
	}

	/**
	 * REST callback: Get feast day for a specific date.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function rest_get_feast_day( \WP_REST_Request $request ): \WP_REST_Response {
		$date = $request->get_param( 'date' );

		$feast_data = $this->fetch_feast_day( $date );

		if ( null === $feast_data ) {
			return new \WP_REST_Response(
				array( 'error' => 'Could not fetch feast day data.' ),
				500
			);
		}

		return rest_ensure_response( $feast_data );
	}

	/**
	 * REST callback: Sync feast days to events.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function rest_sync_feast_days( \WP_REST_Request $request ): \WP_REST_Response {
		$months_ahead = $request->has_param( 'months_ahead' )
			? absint( $request->get_param( 'months_ahead' ) )
			: absint( Parish_Core::get_setting( 'feast_days_months_ahead', 3 ) );
		if ( $months_ahead < 1 ) {
			$months_ahead = 3;
		}

		$results = $this->sync_feast_days_to_events( $months_ahead );
		if ( isset( $results['error'] ) ) {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => sanitize_text_field( (string) $results['error'] ),
					'results' => $results,
				),
				500
			);
		}

		$error_count = count( $results['errors'] ?? array() );
		$message     = sprintf(
			/* translators: %1$d: created count, %2$d: updated count, %3$d: skipped count */
			__( 'Sync complete. Created: %1$d, Updated: %2$d, Skipped: %3$d', 'parish-core' ),
			(int) ( $results['created'] ?? 0 ),
			(int) ( $results['updated'] ?? 0 ),
			(int) ( $results['skipped'] ?? 0 )
		);
		if ( $error_count > 0 ) {
			$message .= ' ' . sprintf(
				/* translators: %d: error count */
				_n( 'Error: %d issue detected.', 'Errors: %d issues detected.', $error_count, 'parish-core' ),
				$error_count
			);
		}

		return rest_ensure_response(
			array(
				'success' => empty( $results['errors'] ?? array() ),
				'message' => $message,
				'results' => $results,
			)
		);
	}

	/**
	 * Get events for a date range, with manual events prioritized over feast days.
	 *
	 * @param string $start Start date (Y-m-d).
	 * @param string $end   End date (Y-m-d).
	 * @return array Events sorted by priority.
	 */
	public function get_events_with_priority( string $start, string $end ): array {
		if ( ! post_type_exists( 'parish_event' ) ) {
			return array();
		}

		$args = array(
			'post_type'      => 'parish_event',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'meta_query'     => array(
				array(
					'key'     => 'parish_event_date',
					'value'   => array( $start, $end ),
					'compare' => 'BETWEEN',
					'type'    => 'DATE',
				),
			),
			'orderby'        => array(
				'meta_value' => 'ASC',
			),
			'meta_key'       => 'parish_event_date',
		);

		$query  = new \WP_Query( $args );
		$events = array();

		foreach ( $query->posts as $post ) {
			$is_feast_day = (bool) get_post_meta( $post->ID, 'parish_event_is_feast_day', true );
			$priority     = (int) get_post_meta( $post->ID, 'parish_event_priority', true );

			// Manual events always get priority 0 (highest).
			if ( ! $is_feast_day ) {
				$priority = 0;
			}

			$events[] = array(
				'id'           => $post->ID,
				'title'        => $post->post_title,
				'date'         => get_post_meta( $post->ID, 'parish_event_date', true ),
				'time'         => get_post_meta( $post->ID, 'parish_event_time', true ),
				'is_feast_day' => $is_feast_day,
				'priority'     => $priority,
				'color'        => get_post_meta( $post->ID, 'parish_event_color', true ),
				'feast_rank'   => get_post_meta( $post->ID, 'parish_event_feast_rank', true ),
			);
		}

		// Sort by date first, then by priority (lower = higher priority).
		usort( $events, function ( $a, $b ) {
			$date_cmp = strcmp( $a['date'], $b['date'] );
			if ( 0 !== $date_cmp ) {
				return $date_cmp;
			}
			return $a['priority'] <=> $b['priority'];
		} );

		return $events;
	}
}
