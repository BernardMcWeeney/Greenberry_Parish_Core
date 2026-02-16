<?php
/**
 * Catholic Readings API Integration Module
 *
 * Fetches and caches readings from the Catholic Readings API and Liturgy.day API.
 * Provides shortcodes for displaying readings on the frontend.
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Parish_Readings class.
 */
class Parish_Readings {

	/**
	 * Singleton instance.
	 *
	 * @var Parish_Readings|null
	 */
	private static ?Parish_Readings $instance = null;

	/**
	 * API base URL for Catholic Readings API.
	 *
	 * @var string
	 */
	private string $api_base_url = 'https://catholic-readings-api.app.greenberry.ie/api/v1/';

	/**
	 * Cache duration in seconds (24 hours).
	 *
	 * @var int
	 */
	private int $cache_duration = 86400;

	/**
	 * Available API endpoints.
	 *
	 * @var array
	 */
	private array $endpoints = array();

	/**
	 * Rosary mystery descriptions.
	 *
	 * @var array
	 */
	private array $rosary_mysteries = array(
		'Joyful' => array(
			'name'        => 'Joyful Mysteries',
			'description' => 'The Joyful Mysteries focus on the joy surrounding Christ\'s birth and early life.',
			'mysteries'   => array(
				'The Annunciation',
				'The Visitation',
				'The Nativity',
				'The Presentation',
				'The Finding in the Temple',
			),
			'anchor'      => 'joyful',
		),
		'Sorrowful' => array(
			'name'        => 'Sorrowful Mysteries',
			'description' => 'The Sorrowful Mysteries contemplate the Passion and Death of Jesus.',
			'mysteries'   => array(
				'The Agony in the Garden',
				'The Scourging at the Pillar',
				'The Crowning with Thorns',
				'The Carrying of the Cross',
				'The Crucifixion',
			),
			'anchor'      => 'sorrowful',
		),
		'Glorious' => array(
			'name'        => 'Glorious Mysteries',
			'description' => 'The Glorious Mysteries celebrate the Resurrection and glory of Christ and Mary.',
			'mysteries'   => array(
				'The Resurrection',
				'The Ascension',
				'The Descent of the Holy Spirit',
				'The Assumption of Mary',
				'The Coronation of Mary',
			),
			'anchor'      => 'glorious',
		),
		'Luminous' => array(
			'name'        => 'Luminous Mysteries',
			'description' => 'The Luminous Mysteries (Mysteries of Light) reflect on Christ\'s public ministry.',
			'mysteries'   => array(
				'The Baptism in the Jordan',
				'The Wedding at Cana',
				'The Proclamation of the Kingdom',
				'The Transfiguration',
				'The Institution of the Eucharist',
			),
			'anchor'      => 'luminous',
		),
	);

	/**
	 * Liturgical color hex values.
	 *
	 * @var array
	 */
	private array $liturgical_colors = array(
		'green'  => '#008000',
		'white'  => '#FFFFFF',
		'red'    => '#C41E3A',
		'violet' => '#8B00FF',
		'purple' => '#800080',
		'rose'   => '#FF007F',
		'pink'   => '#FFC0CB',
		'gold'   => '#FFD700',
		'black'  => '#000000',
	);

	/**
	 * Get singleton instance.
	 */
	public static function instance(): Parish_Readings {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Schedule options configuration.
	 *
	 * @var array
	 */
	private array $schedule_options = array(
		'daily_once'     => array(
			'label'        => 'Once Daily',
			'frequency'    => 'daily',
			'times'        => 1,
			'default_time' => '05:00',
		),
		'daily_twice'    => array(
			'label'         => 'Twice Daily',
			'frequency'     => 'twicedaily',
			'times'         => 2,
			'default_times' => array( '05:00', '17:00' ),
		),
		'every_6_hours'  => array(
			'label'     => 'Every 6 Hours',
			'frequency' => 'every_6_hours',
			'times'     => 4,
			'interval'  => 21600,
		),
		'every_4_hours'  => array(
			'label'     => 'Every 4 Hours',
			'frequency' => 'every_4_hours',
			'times'     => 6,
			'interval'  => 14400,
		),
	);

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->setup_endpoints();

		// Register shortcodes.
		add_action( 'init', array( $this, 'register_shortcodes' ) );

		// Register custom cron schedules.
		add_filter( 'cron_schedules', array( $this, 'register_custom_schedules' ) );

		// Schedule daily fetch.
		add_action( 'init', array( $this, 'schedule_cron' ) );
		add_action( 'parish_fetch_readings_cron', array( $this, 'fetch_all_readings' ) );
	}

	/**
	 * Setup API endpoints configuration.
	 */
	private function setup_endpoints(): void {
		$this->endpoints = array(
			// Catholic Readings API endpoints.
			'daily_readings' => array(
				'name'      => __( 'Daily Readings', 'parish-core' ),
				'path'      => 'content/daily_readings',
				'shortcode' => 'daily_readings',
				'schedule'  => 'daily',
			),
			'sunday_homily' => array(
				'name'      => __( 'Sunday Homily', 'parish-core' ),
				'path'      => 'content/sunday_homily',
				'shortcode' => 'sunday_homily',
				'schedule'  => 'weekly',
			),
			'saint_of_the_day' => array(
				'name'      => __( 'Saint of the Day', 'parish-core' ),
				'path'      => 'content/saint_of_the_day',
				'shortcode' => 'saint_of_the_day',
				'schedule'  => 'daily',
			),
			'next_sunday_reading' => array(
				'name'      => __( 'Next Sunday Reading', 'parish-core' ),
				'path'      => 'content/next_sunday_reading',
				'shortcode' => 'next_sunday_reading',
				'schedule'  => 'weekly',
			),
			'next_sunday_reading_irish' => array(
				'name'      => __( 'Next Sunday Reading (Irish)', 'parish-core' ),
				'path'      => 'content/next_sunday_reading_irish',
				'shortcode' => 'next_sunday_reading_irish',
				'schedule'  => 'weekly',
			),
			'daily_readings_irish' => array(
				'name'      => __( 'Daily Readings (Irish)', 'parish-core' ),
				'path'      => 'content/daily_readings_irish',
				'shortcode' => 'daily_readings_irish',
				'schedule'  => 'daily',
			),
			'mass_reading_details' => array(
				'name'      => __( 'Mass Reading Details', 'parish-core' ),
				'path'      => 'content/mass_reading_details',
				'shortcode' => 'mass_reading_details',
				'schedule'  => 'daily',
			),
			'feast_day_details' => array(
				'name'      => __( 'Feast Day Details', 'parish-core' ),
				'path'      => 'calendars/general-en/today',
				'shortcode' => 'feast_day_details',
				'schedule'  => 'daily',
				'base_url'  => 'http://calapi.inadiutorium.cz/api/v0/en/',
			),

			// Liturgical endpoints - uses local calculation (no external API).
			'liturgy_day' => array(
				'name'      => __( 'Liturgical Day', 'parish-core' ),
				'shortcode' => 'liturgical_day',
				'schedule'  => 'daily',
				'local'     => true, // Uses local calculation instead of external API.
			),
			'liturgy_week' => array(
				'name'      => __( 'Liturgical Week', 'parish-core' ),
				'shortcode' => 'liturgical_week',
				'schedule'  => 'weekly',
				'local'     => true, // Uses local calculation instead of external API.
			),
			'rosary_days' => array(
				'name'      => __( 'Rosary Days', 'parish-core' ),
				'shortcode' => 'rosary_days',
				'schedule'  => 'daily',
				'local'     => true, // Uses local calculation instead of external API.
			),
		);
	}

	/**
	 * Get endpoints configuration.
	 */
	public function get_endpoints(): array {
		return $this->endpoints;
	}

	/**
	 * Register shortcodes for all endpoints.
	 * Note: [rosary_today] and [rosary_full] are registered by Parish_Rosary_Shortcodes class.
	 */
	public function register_shortcodes(): void {
		// Register API-based shortcodes.
		foreach ( $this->endpoints as $key => $config ) {
			add_shortcode( $config['shortcode'], array( $this, 'render_shortcode' ) );
		}

		// Register additional rosary shortcodes.
		// Note: [rosary_today] is handled by Parish_Rosary_Shortcodes (uses dedicated data/schedule classes).
		// Only register these supplementary rosary shortcodes if not already registered.
		if ( ! shortcode_exists( 'rosary_week' ) ) {
			add_shortcode( 'rosary_week', array( $this, 'render_rosary_week' ) );
		}
		if ( ! shortcode_exists( 'rosary_series' ) ) {
			add_shortcode( 'rosary_series', array( $this, 'render_rosary_series' ) );
		}
		if ( ! shortcode_exists( 'rosary_mysteries' ) ) {
			add_shortcode( 'rosary_mysteries', array( $this, 'render_rosary_mysteries' ) );
		}
	}

	/**
	 * Schedule cron jobs for all endpoints.
	 * Each endpoint can have its own schedule configuration.
	 */
	public function schedule_cron(): void {
		// Get per-endpoint schedules from settings.
		$schedules = json_decode( Parish_Core::get_setting( 'readings_schedules', '{}' ), true ) ?: array();

		// Always register action callbacks (they don't persist between requests).
		$this->register_endpoint_actions( $schedules );

		// Create a hash of current schedule configuration to detect changes.
		$current_config_hash = md5( wp_json_encode( $schedules ) );
		$stored_config_hash  = get_option( 'parish_readings_schedule_hash', '' );

		// Check if the global cron hook is scheduled.
		$hook                  = 'parish_fetch_readings_cron';
		$global_hook_scheduled = wp_next_scheduled( $hook );

		// Force reschedule if: config changed OR global hook is missing.
		$needs_reschedule = ( $current_config_hash !== $stored_config_hash ) || ! $global_hook_scheduled;

		if ( $needs_reschedule ) {
			// Schedule each endpoint with its specific configuration.
			foreach ( array_keys( $this->endpoints ) as $endpoint ) {
				$schedule_config = $schedules[ $endpoint ] ?? array(
					'schedule' => 'daily_once',
					'time'     => '05:00',
				);

				$this->schedule_endpoint( $endpoint, $schedule_config );
			}

			// Update stored hash.
			update_option( 'parish_readings_schedule_hash', $current_config_hash );
		}

		// Schedule the global cron hook for fetch_all_readings() - ensure it's always scheduled.
		if ( ! $global_hook_scheduled ) {
			$timezone  = wp_timezone();
			$now       = new DateTime( 'now', $timezone );
			$next_run  = new DateTime( 'tomorrow 05:00:00', $timezone );
			$today_5am = new DateTime( 'today 05:00:00', $timezone );
			if ( $now < $today_5am ) {
				$next_run = $today_5am;
			}

			$scheduled = wp_schedule_event( $next_run->getTimestamp(), 'daily', $hook );

			// Log scheduling for debugging if WP_DEBUG is enabled.
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG && false === $scheduled ) {
				error_log( 'Parish Core: Failed to schedule parish_fetch_readings_cron event.' );
			}
		}
	}

	/**
	 * Register action callbacks for all endpoint cron hooks.
	 * This must run on every request as callbacks don't persist.
	 *
	 * @param array $schedules Per-endpoint schedule configuration.
	 */
	private function register_endpoint_actions( array $schedules ): void {
		foreach ( array_keys( $this->endpoints ) as $endpoint ) {
			$schedule_config = $schedules[ $endpoint ] ?? array( 'schedule' => 'daily_once' );
			$schedule_type   = $schedule_config['schedule'] ?? 'daily_once';
			$hook            = "parish_fetch_{$endpoint}";

			if ( 'daily_twice' === $schedule_type ) {
				// Register actions for each time slot.
				$times = $schedule_config['times'] ?? array( '05:00', '17:00' );
				foreach ( array_keys( $times ) as $idx ) {
					$hook_with_idx = $hook . '_' . $idx;
					add_action(
						$hook_with_idx,
						function () use ( $endpoint ) {
							$this->fetch_endpoint( $endpoint );
						}
					);
				}
			} else {
				// Single action for other schedule types.
				add_action(
					$hook,
					function () use ( $endpoint ) {
						$this->fetch_endpoint( $endpoint );
					}
				);
			}
		}
	}

	/**
	 * Register custom cron schedules.
	 *
	 * @param array $schedules Existing schedules.
	 * @return array Modified schedules.
	 */
	public function register_custom_schedules( array $schedules ): array {
		$schedules['every_6_hours'] = array(
			'interval' => 21600, // 6 * 3600
			'display'  => __( 'Every 6 Hours', 'parish-core' ),
		);

		$schedules['every_4_hours'] = array(
			'interval' => 14400, // 4 * 3600
			'display'  => __( 'Every 4 Hours', 'parish-core' ),
		);

		return $schedules;
	}

	/**
	 * Schedule a single endpoint with its specific schedule configuration.
	 * Note: Action callbacks are registered separately in register_endpoint_actions().
	 *
	 * @param string $endpoint Endpoint key.
	 * @param array  $config   Schedule configuration.
	 */
	public function schedule_endpoint( string $endpoint, array $config ): void {
		$hook = "parish_fetch_{$endpoint}";

		// Clear existing schedules for this endpoint.
		wp_clear_scheduled_hook( $hook );

		$schedule_type = $config['schedule'] ?? 'daily_once';
		$timezone      = wp_timezone();

		switch ( $schedule_type ) {
			case 'daily_once':
				$time     = $config['time'] ?? '05:00';
				$next_run = $this->calculate_next_daily_run( $time, $timezone );
				wp_schedule_event( $next_run->getTimestamp(), 'daily', $hook );
				break;

			case 'daily_twice':
				$times = $config['times'] ?? array( '05:00', '17:00' );
				foreach ( $times as $idx => $time ) {
					$hook_with_idx = $hook . '_' . $idx;
					wp_clear_scheduled_hook( $hook_with_idx );
					$next_run = $this->calculate_next_daily_run( $time, $timezone );
					wp_schedule_event( $next_run->getTimestamp(), 'twicedaily', $hook_with_idx );
				}
				break;

			case 'every_6_hours':
			case 'every_4_hours':
				$start    = $config['start_time'] ?? '00:00';
				$next_run = $this->calculate_next_daily_run( $start, $timezone );
				wp_schedule_event( $next_run->getTimestamp(), $schedule_type, $hook );
				break;
		}
	}

	/**
	 * Calculate next daily run time.
	 *
	 * @param string       $time     Time in HH:MM format.
	 * @param DateTimeZone $timezone Timezone.
	 * @return DateTime Next run datetime.
	 */
	private function calculate_next_daily_run( string $time, DateTimeZone $timezone ): DateTime {
		$now    = new DateTime( 'now', $timezone );
		$target = new DateTime( "today {$time}", $timezone );

		if ( $now >= $target ) {
			$target = new DateTime( "tomorrow {$time}", $timezone );
		}

		return $target;
	}

	/**
	 * Fetch all readings from API.
	 */
	public function fetch_all_readings(): array {
		$api_key = Parish_Core::get_setting( 'readings_api_key', '' );
		$results = array();

		foreach ( array_keys( $this->endpoints ) as $endpoint ) {
			$config = $this->endpoints[ $endpoint ];

			// Skip endpoints that require API key if not set.
			if ( empty( $api_key ) && ! isset( $config['base_url'] ) ) {
				continue;
			}

			// Local endpoints use local calculation (no API key required).
			if ( ! empty( $config['local'] ) ) {
				$result = $this->fetch_liturgy_day_endpoint( $endpoint );
			} else {
				$result = $this->fetch_endpoint( $endpoint, $api_key );
			}

			$results[ $endpoint ] = $result;
		}

		return array(
			'success' => true,
			'results' => $results,
		);
	}

	/**
	 * Calculate liturgical data locally (no external API).
	 *
	 * @param string $endpoint Endpoint key.
	 */
	public function fetch_liturgy_day_endpoint( string $endpoint ): array {
		if ( ! isset( $this->endpoints[ $endpoint ] ) ) {
			return array(
				'success' => false,
				'message' => __( 'Invalid endpoint.', 'parish-core' ),
			);
		}

		// Calculate data locally instead of calling external API.
		$data = array();

		switch ( $endpoint ) {
			case 'liturgy_day':
				$season = $this->calculate_liturgical_season();
				$rosary = $this->calculate_todays_rosary();
				$data   = array(
					'date'          => current_time( 'Y-m-d' ),
					'season'        => $season,
					'rosary-series' => $rosary['rosary-series'],
					'sunday-cycle'  => $this->calculate_sunday_cycle(),
					'weekday-cycle' => $this->calculate_weekday_cycle(),
					'loth-volume'   => $this->calculate_loth_volume( $season ),
				);
				break;

			case 'liturgy_week':
				$season = $this->calculate_liturgical_season();
				$data   = array(
					'season'      => $season,
					'rosary-week' => $this->calculate_week_rosary( $season ),
				);
				break;

			case 'rosary_days':
				$season = $this->calculate_liturgical_season();
				$data   = array(
					'rosary-days' => array(
						'Joyful'    => array( 'Monday', 'Saturday' ),
						'Sorrowful' => array( 'Tuesday', 'Friday' ),
						'Glorious'  => array( 'Sunday', 'Wednesday' ),
						'Luminous'  => array( 'Thursday' ),
					),
				);
				break;

			default:
				return array(
					'success' => false,
					'message' => __( 'Unknown liturgical endpoint.', 'parish-core' ),
				);
		}

		// Store in options and transient cache.
		update_option( "{$endpoint}_content", $data );
		update_option( "{$endpoint}_last_fetch", current_time( 'mysql' ) );
		set_transient( "{$endpoint}_cache", $data, $this->cache_duration );

		return array(
			'success'    => true,
			'message'    => __( 'Calculated successfully.', 'parish-core' ),
			'last_fetch' => current_time( 'mysql' ),
		);
	}

	/**
	 * Calculate the Sunday lectionary cycle (A, B, or C).
	 *
	 * @return string Cycle letter.
	 */
	private function calculate_sunday_cycle(): string {
		$year = (int) current_time( 'Y' );
		// Cycle follows a 3-year pattern: A (year % 3 == 1), B (year % 3 == 2), C (year % 3 == 0).
		$cycles = array( 'C', 'A', 'B' );
		return $cycles[ $year % 3 ];
	}

	/**
	 * Calculate the weekday lectionary cycle (I or II).
	 *
	 * @return string Cycle number.
	 */
	private function calculate_weekday_cycle(): string {
		$year = (int) current_time( 'Y' );
		// Odd years = Year I, Even years = Year II.
		return ( $year % 2 === 1 ) ? 'I' : 'II';
	}

	/**
	 * Calculate Liturgy of the Hours volume.
	 *
	 * @param string $season Liturgical season.
	 * @return string Volume number.
	 */
	private function calculate_loth_volume( string $season ): string {
		$volumes = array(
			'Advent'        => 'I',
			'Christmas'     => 'I',
			'Lent'          => 'II',
			'Easter'        => 'II',
			'Ordinary Time' => 'III/IV',
		);
		return $volumes[ $season ] ?? 'III';
	}

	/**
	 * Fetch a single endpoint.
	 *
	 * @param string $endpoint Endpoint key.
	 * @param string $api_key  API key (optional, will use settings if not provided).
	 */
	public function fetch_endpoint( string $endpoint, ?string $api_key = null ): array {
		// Handle computed/static shortcodes that don't have actual API endpoints.
		$computed_shortcodes = array( 'rosary_today', 'rosary_week', 'rosary_series', 'rosary_mysteries' );
		if ( in_array( $endpoint, $computed_shortcodes, true ) ) {
			return array(
				'success' => true,
				'message' => __( 'This shortcode uses computed data from liturgy.day API and does not require separate fetching.', 'parish-core' ),
			);
		}

		if ( ! isset( $this->endpoints[ $endpoint ] ) ) {
			return array(
				'success' => false,
				'message' => __( 'Invalid endpoint.', 'parish-core' ),
			);
		}

		$config = $this->endpoints[ $endpoint ];

		// Handle local endpoints (liturgical calculations).
		if ( ! empty( $config['local'] ) ) {
			return $this->fetch_liturgy_day_endpoint( $endpoint );
		}

		if ( null === $api_key ) {
			$api_key = Parish_Core::get_setting( 'readings_api_key', '' );
		}

		if ( empty( $api_key ) ) {
			return array(
				'success' => false,
				'message' => __( 'API key not configured.', 'parish-core' ),
			);
		}

		$base_url = $config['base_url'] ?? $this->api_base_url;
		$url      = $base_url . $config['path'];

		// Build request.
		$args = array(
			'headers' => array(
				'Accept' => 'application/json',
			),
			'timeout' => 30,
		);

		// Add API key.
		if ( strpos( $base_url, 'inadiutorium' ) !== false ) {
			$url = add_query_arg( 'apikey', $api_key, $url );
		} else {
			$args['headers']['Authorization'] = 'Bearer ' . $api_key;
			$url = add_query_arg( 'api_key', $api_key, $url );
		}

		$response = wp_remote_get( $url, $args );

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'message' => $response->get_error_message(),
			);
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( $code !== 200 ) {
			return array(
				'success' => false,
				'message' => sprintf( __( 'API returned status %d', 'parish-core' ), $code ),
			);
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return array(
				'success' => false,
				'message' => __( 'Invalid JSON response.', 'parish-core' ),
			);
		}

		// Store in options and transient cache.
		update_option( "{$endpoint}_content", $data );
		update_option( "{$endpoint}_last_fetch", current_time( 'mysql' ) );
		set_transient( "{$endpoint}_cache", $data, $this->cache_duration );

		return array(
			'success'    => true,
			'message'    => __( 'Fetched successfully.', 'parish-core' ),
			'last_fetch' => current_time( 'mysql' ),
		);
	}

	/**
	 * Get reading data.
	 *
	 * @param string $endpoint Endpoint key.
	 */
	public function get_reading( string $endpoint ): ?array {
		// Try transient first.
		$data = get_transient( "{$endpoint}_cache" );

		if ( false === $data ) {
			// Fall back to stored option.
			$data = get_option( "{$endpoint}_content", null );
		}

		return is_array( $data ) ? $data : null;
	}

	/**
	 * Get last fetch time for an endpoint.
	 *
	 * @param string $endpoint Endpoint key.
	 */
	public function get_last_fetch( string $endpoint ): ?string {
		return get_option( "{$endpoint}_last_fetch", null );
	}

	/**
	 * Render shortcode.
	 *
	 * @param array  $atts    Shortcode attributes.
	 * @param string $content Shortcode content.
	 * @param string $tag     Shortcode tag.
	 */
	public function render_shortcode( $atts, $content, $tag ): string {
		// Find endpoint by shortcode tag.
		$endpoint = null;
		foreach ( $this->endpoints as $key => $config ) {
			if ( $config['shortcode'] === $tag ) {
				$endpoint = $key;
				break;
			}
		}

		if ( ! $endpoint ) {
			return '';
		}

		$data = $this->get_reading( $endpoint );

		if ( empty( $data ) ) {
			// Try to fetch if data is not available.
			$this->fetch_endpoint( $endpoint );
			$data = $this->get_reading( $endpoint );
		}

		if ( empty( $data ) ) {
			return '<div class="parish-readings-content parish-readings-empty">' .
			       '<p>' . esc_html__( 'Content not available. Please check back later.', 'parish-core' ) . '</p>' .
			       '</div>';
		}

		$atts = shortcode_atts( array(
			'link_rosary' => '',
			'show_link'   => 'yes',
			'format'      => 'full',
		), $atts, $tag );

		return $this->format_reading( $endpoint, $data, $atts );
	}

	/**
	 * Format reading data for display.
	 *
	 * @param string $endpoint Endpoint key.
	 * @param array  $data     Reading data.
	 * @param array  $atts     Shortcode attributes.
	 */
	private function format_reading( string $endpoint, array $data, array $atts = array() ): string {
		switch ( $endpoint ) {
			case 'mass_reading_details':
				return $this->format_mass_readings( $data );

			case 'feast_day_details':
				return $this->format_feast_days( $data );

			case 'liturgy_day':
				return $this->format_liturgical_day( $data, $atts );

			case 'liturgy_week':
				return $this->format_liturgical_week( $data, $atts );

			case 'rosary_days':
				return $this->format_rosary_days( $data, $atts );

			default:
				return $this->format_generic_reading( $data );
		}
	}

	/**
	 * Format generic reading content.
	 *
	 * @param array $data Reading data.
	 */
	private function format_generic_reading( array $data ): string {
		$content = '';

		if ( isset( $data['content'] ) ) {
			$content = is_string( $data['content'] ) ? $data['content'] : '';
		} elseif ( isset( $data['text'] ) ) {
			$content = is_string( $data['text'] ) ? $data['text'] : '';
		}

		if ( empty( $content ) ) {
			return '<div class="parish-readings-content parish-readings-empty">' .
			       '<p>' . esc_html__( 'Content not available.', 'parish-core' ) . '</p>' .
			       '</div>';
		}

		return '<div class="parish-readings-content">' . wp_kses_post( $content ) . '</div>';
	}

	/**
	 * Format mass readings with structure.
	 *
	 * @param array $data Reading data.
	 */

	private function format_mass_readings( array $data ): string {
		$content = $data['content'] ?? $data;

		if ( ! is_array( $content ) ) {
			return $this->format_generic_reading( $data );
		}

		$sections = array(
			'first_reading'      => __( 'First Reading', 'parish-core' ),
			'psalm'              => __( 'Responsorial Psalm', 'parish-core' ),
			'second_reading'     => __( 'Second Reading', 'parish-core' ),
			'gospel_acclamation' => __( 'Gospel Acclamation', 'parish-core' ),
			'gospel'             => __( 'Gospel', 'parish-core' ),
		);

		$html  = '<div class="parish-readings-content parish-mass-readings">';
		$html .= '<div class="readings-rows">';

		foreach ( $sections as $key => $label ) {
			if ( ! empty( $content[ $key ] ) ) {
				$html .= '<p class="reading-row">';
				$html .= '<strong class="reading-label">' . esc_html( $label ) . '</strong>';
				$html .= '<span class="reading-sep">: </span>';
				$html .= '<span class="reading-text">' . wp_kses_post( $content[ $key ] ) . '</span>';
				$html .= '</p>';
			}
		}

		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}




	/**
	 * Format feast day details with colored "badge" indicators.
	 *
	 * @param array $data Feast data.
	 */
	private function format_feast_days( array $data ): string {
		$celebrations = $data['celebrations'] ?? array();

		if ( empty( $celebrations ) || ! is_array( $celebrations ) ) {
			return '<div class="parish-readings-content parish-readings-empty">' .
				'<p>' . esc_html__( 'Feast day information not available.', 'parish-core' ) . '</p>' .
				'</div>';
		}

		$html = '<div class="parish-readings-content parish-feast-days">';

		foreach ( $celebrations as $celebration ) {
			$title_raw = isset( $celebration['title'] ) ? trim( (string) $celebration['title'] ) : '';
			$title     = $title_raw !== '' ? $title_raw : __( 'Feast day', 'parish-core' );

			$colour   = strtolower( trim( (string) ( $celebration['colour'] ?? '' ) ) );
			$rank_raw = isset( $celebration['rank'] ) ? trim( (string) $celebration['rank'] ) : '';
			$rank     = $rank_raw !== '' ? ucwords( str_replace( '_', ' ', $rank_raw ) ) : '';

			$color_hex = $this->liturgical_colors[ $colour ] ?? '#808080';

			$is_light_colour = in_array( $colour, array( 'white', 'rose', 'yellow' ), true );
			$badge_text      = $is_light_colour ? '#111' : '#fff';
			$border_color    = ( $colour === 'white' ) ? '#ccc' : 'rgba(0,0,0,0.08)';

			$html .= '<div class="feast-entry">';

			$html .= '<p class="feast-row feast-row--title">';
			$html .= '<strong class="feast-label">' . esc_html__( 'Feast:', 'parish-core' ) . '</strong> ';
			$html .= '<span class="feast-title">' . esc_html( $title ) . '</span>';
			$html .= '</p>';

			$html .= '<p class="feast-row feast-row--colour">';
			$html .= '<strong class="feast-label">' . esc_html__( 'Liturgical colour:', 'parish-core' ) . '</strong> ';

			if ( $colour !== '' ) {
				$html .= '<span class="liturgical-colour-badge colour-' . esc_attr( $colour ) . '" ';
				$html .= 'style="--liturgical-badge-bg:' . esc_attr( $color_hex ) . ';';
				$html .= '--liturgical-badge-fg:' . esc_attr( $badge_text ) . ';';
				$html .= '--liturgical-badge-border:' . esc_attr( $border_color ) . ';" ';
				$html .= 'title="' . esc_attr( ucfirst( $colour ) ) . '">';
				$html .= esc_html( ucfirst( $colour ) );
				$html .= '</span>';
			} else {
				$html .= '<span class="liturgical-colour-badge" style="--liturgical-badge-bg:#808080;--liturgical-badge-fg:#fff;">';
				$html .= esc_html__( 'Unknown', 'parish-core' );
				$html .= '</span>';
			}

			$html .= '</p>';

			$html .= '<p class="feast-row feast-row--rank">';
			$html .= '<strong class="feast-label">' . esc_html__( 'Rank:', 'parish-core' ) . '</strong> ';
			if ( $rank !== '' ) {
				$html .= '<span class="feast-rank">' . esc_html( $rank ) . '</span>';
			}
			$html .= '</p>';

			$html .= '</div>';
		}

		$html .= '</div>';

		return $html;
	}




	/**
	 * Format liturgical day data.
	 *
	 * @param array $data Liturgical day data.
	 * @param array $atts Shortcode attributes.
	 */
	private function format_liturgical_day( array $data, array $atts = array() ): string {
		$season        = $data['season'] ?? '';
		$sunday_cycle  = $data['sunday-cycle'] ?? '';
		$weekday_cycle = $data['weekday-cycle'] ?? '';
		$loth_volume   = $data['loth-volume'] ?? '';
		$rosary_series = $data['rosary-series'] ?? '';
		$date          = $data['date'] ?? current_time( 'Y-m-d' );

		$html  = '<div class="parish-readings-content parish-liturgical-day">';
		$html .= '<div class="liturgical-info">';

		$html .= '<p class="liturgical-date">';
		$html .= '<strong>' . esc_html__( 'Date:', 'parish-core' ) . '</strong> ';
		$html .= esc_html( date_i18n( get_option( 'date_format' ), strtotime( $date ) ) );
		$html .= '</p>';

		if ( $season ) {
			$html .= '<p class="liturgical-season">';
			$html .= '<strong>' . esc_html__( 'Liturgical Season:', 'parish-core' ) . '</strong> ';
			$html .= esc_html( $season );
			$html .= '</p>';
		}

		if ( $sunday_cycle ) {
			$html .= '<p class="liturgical-sunday-cycle">';
			$html .= '<strong>' . esc_html__( 'Sunday Cycle:', 'parish-core' ) . '</strong> ';
			$html .= esc_html( $sunday_cycle );
			$html .= '</p>';
		}

		if ( $weekday_cycle ) {
			$html .= '<p class="liturgical-weekday-cycle">';
			$html .= '<strong>' . esc_html__( 'Weekday Cycle:', 'parish-core' ) . '</strong> ';
			$html .= esc_html( $weekday_cycle );
			$html .= '</p>';
		}

		if ( $loth_volume ) {
			$html .= '<p class="liturgical-loth">';
			$html .= '<strong>' . esc_html__( 'Liturgy of the Hours:', 'parish-core' ) . '</strong> ';
			$html .= sprintf( esc_html__( 'Volume %s', 'parish-core' ), esc_html( $loth_volume ) );
			$html .= '</p>';
		}

		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Format liturgical week data.
	 *
	 * @param array $data Liturgical week data.
	 * @param array $atts Shortcode attributes.
	 */
	private function format_liturgical_week( array $data, array $atts = array() ): string {
		$rosary_week = $data['rosary-week'] ?? array();
		$rosary_link = $atts['link_rosary'] ?? '';

		$html = '<div class="parish-readings-content parish-liturgical-week">';

		// Basic week info.
		$html .= '<div class="week-info">';
		if ( isset( $data['season'] ) ) {
			$html .= '<p><strong>' . esc_html__( 'Season:', 'parish-core' ) . '</strong> ' . esc_html( $data['season'] ) . '</p>';
		}
		$html .= '</div>';

		// Rosary schedule for the week.
		if ( ! empty( $rosary_week ) ) {
			$html .= '<div class="rosary-week-schedule">';
			$html .= '<h4>' . esc_html__( 'Rosary Mysteries This Week', 'parish-core' ) . '</h4>';
			$html .= '<table class="rosary-schedule-table">';
			$html .= '<thead><tr>';
			$html .= '<th>' . esc_html__( 'Day', 'parish-core' ) . '</th>';
			$html .= '<th>' . esc_html__( 'Mysteries', 'parish-core' ) . '</th>';
			$html .= '</tr></thead>';
			$html .= '<tbody>';

			$days_order = array( 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' );

			foreach ( $days_order as $day ) {
				$series = $rosary_week[ $day ] ?? '';
				if ( ! $series ) {
					continue;
				}

				$mystery_info = $this->rosary_mysteries[ $series ] ?? null;

				$html .= '<tr>';
				$html .= '<td><strong>' . esc_html( $day ) . '</strong></td>';
				$html .= '<td>';

				if ( $rosary_link && $mystery_info ) {
					$link_url = trailingslashit( $rosary_link ) . '#' . $mystery_info['anchor'];
					$html .= '<a href="' . esc_url( $link_url ) . '">' . esc_html( $mystery_info['name'] ) . '</a>';
				} else {
					$html .= esc_html( $mystery_info['name'] ?? $series . ' Mysteries' );
				}

				$html .= '</td>';
				$html .= '</tr>';
			}

			$html .= '</tbody>';
			$html .= '</table>';
			$html .= '</div>';
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Format rosary days data.
	 *
	 * @param array $data Rosary days data.
	 * @param array $atts Shortcode attributes.
	 */
	private function format_rosary_days( array $data, array $atts = array() ): string {
		$rosary_days = $data['rosary-days'] ?? array();
		$season      = $data['season'] ?? '';
		$rosary_link = $atts['link_rosary'] ?? '';

		$html = '<div class="parish-readings-content parish-rosary-days">';

		if ( $season ) {
			$html .= '<p class="rosary-season"><strong>' . esc_html__( 'During', 'parish-core' ) . ' ' . esc_html( $season ) . ':</strong></p>';
		}

		$html .= '<div class="rosary-series-list">';

		foreach ( $rosary_days as $series => $days ) {
			$mystery_info = $this->rosary_mysteries[ $series ] ?? null;

			if ( ! $mystery_info || empty( $days ) ) {
				continue;
			}

			$html .= '<div class="rosary-series-item">';
			$html .= '<h4>';

			if ( $rosary_link ) {
				$link_url = trailingslashit( $rosary_link ) . '#' . $mystery_info['anchor'];
				$html .= '<a href="' . esc_url( $link_url ) . '">' . esc_html( $mystery_info['name'] ) . '</a>';
			} else {
				$html .= esc_html( $mystery_info['name'] );
			}

			$html .= '</h4>';
			$html .= '<p class="series-days">' . esc_html( implode( ', ', $days ) ) . '</p>';
			$html .= '<p class="series-description">' . esc_html( $mystery_info['description'] ) . '</p>';
			$html .= '</div>';
		}

		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Render rosary today shortcode.
	 *
	 * @param array  $atts    Shortcode attributes.
	 * @param string $content Shortcode content.
	 */
	public function render_rosary_today( $atts, $content = '' ): string {
		$atts = shortcode_atts( array(
			'link'         => '',
			'show_link'    => 'yes',
			'format'       => 'full', // full, simple, link-only.
			'show_mysteries' => 'no',
		), $atts, 'rosary_today' );

		// Try to get from liturgy_day endpoint.
		$data = $this->get_reading( 'liturgy_day' );

		// If not available, try to fetch.
		if ( empty( $data ) ) {
			$this->fetch_liturgy_day_endpoint( 'liturgy_day' );
			$data = $this->get_reading( 'liturgy_day' );
		}

		// Fallback: calculate based on day and season.
		if ( empty( $data ) || ! isset( $data['rosary-series'] ) ) {
			$data = $this->calculate_todays_rosary();
		}

		$series = $data['rosary-series'] ?? '';
		$season = $data['season'] ?? '';

		if ( empty( $series ) ) {
			return '<div class="parish-readings-content parish-readings-empty">' .
			       '<p>' . esc_html__( 'Rosary information not available.', 'parish-core' ) . '</p>' .
			       '</div>';
		}

		$mystery_info = $this->rosary_mysteries[ $series ] ?? null;

		if ( ! $mystery_info ) {
			return '<div class="parish-readings-content parish-readings-empty">' .
			       '<p>' . esc_html__( 'Unknown rosary series.', 'parish-core' ) . '</p>' .
			       '</div>';
		}

		$rosary_link = $atts['link'];

		$html = '<div class="parish-readings-content parish-rosary-today">';

		if ( $atts['format'] === 'link-only' && $rosary_link ) {
			$link_url = trailingslashit( $rosary_link ) . '#' . $mystery_info['anchor'];
			$html .= '<a href="' . esc_url( $link_url ) . '" class="rosary-today-link">';
			$html .= esc_html( $mystery_info['name'] );
			$html .= '</a>';
		} elseif ( $atts['format'] === 'simple' ) {
			$html .= '<p class="rosary-today-simple">';
			$html .= '<strong>' . esc_html__( 'Today\'s Rosary:', 'parish-core' ) . '</strong> ';

			if ( $rosary_link && $atts['show_link'] === 'yes' ) {
				$link_url = trailingslashit( $rosary_link ) . '#' . $mystery_info['anchor'];
				$html .= '<a href="' . esc_url( $link_url ) . '">' . esc_html( $mystery_info['name'] ) . '</a>';
			} else {
				$html .= esc_html( $mystery_info['name'] );
			}

			$html .= '</p>';
		} else {
			// Full format.
			$html .= '<div class="rosary-today-full">';

			$html .= '<h3 class="rosary-title">';
			if ( $rosary_link && $atts['show_link'] === 'yes' ) {
				$link_url = trailingslashit( $rosary_link ) . '#' . $mystery_info['anchor'];
				$html .= '<a href="' . esc_url( $link_url ) . '">' . esc_html( $mystery_info['name'] ) . '</a>';
			} else {
				$html .= esc_html( $mystery_info['name'] );
			}
			$html .= '</h3>';

			$html .= '<p class="rosary-description">' . esc_html( $mystery_info['description'] ) . '</p>';

			if ( $atts['show_mysteries'] === 'yes' ) {
				$html .= '<ol class="rosary-mysteries-list">';
				foreach ( $mystery_info['mysteries'] as $mystery ) {
					$html .= '<li>' . esc_html( $mystery ) . '</li>';
				}
				$html .= '</ol>';
			}

			if ( $season ) {
				$html .= '<p class="rosary-season-note">';
				$html .= '<small>' . sprintf( esc_html__( 'Based on the %s liturgical season.', 'parish-core' ), esc_html( $season ) ) . '</small>';
				$html .= '</p>';
			}

			$html .= '</div>';
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Render rosary week shortcode.
	 *
	 * @param array  $atts    Shortcode attributes.
	 * @param string $content Shortcode content.
	 */
	public function render_rosary_week( $atts, $content = '' ): string {
		$atts = shortcode_atts( array(
			'link'       => '',
			'show_today' => 'yes',
			'format'     => 'table', // table, list.
		), $atts, 'rosary_week' );

		// Try to get from liturgy_week endpoint.
		$data = $this->get_reading( 'liturgy_week' );

		// If not available, try to fetch.
		if ( empty( $data ) ) {
			$this->fetch_liturgy_day_endpoint( 'liturgy_week' );
			$data = $this->get_reading( 'liturgy_week' );
		}

		$rosary_week = $data['rosary-week'] ?? array();
		$season      = $data['season'] ?? '';
		$rosary_link = $atts['link'];
		$today       = current_time( 'l' ); // Day name.

		if ( empty( $rosary_week ) ) {
			// Fallback: calculate based on current season.
			$rosary_week = $this->calculate_week_rosary( $season ?: $this->calculate_liturgical_season() );
		}

		$html = '<div class="parish-readings-content parish-rosary-week">';

		if ( $season ) {
			$html .= '<p class="rosary-season"><strong>' . esc_html__( 'Season:', 'parish-core' ) . '</strong> ' . esc_html( $season ) . '</p>';
		}

		if ( $atts['format'] === 'list' ) {
			$html .= '<ul class="rosary-week-list">';

			$days_order = array( 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' );

			foreach ( $days_order as $day ) {
				$series = $rosary_week[ $day ] ?? '';
				if ( ! $series ) {
					continue;
				}

				$mystery_info = $this->rosary_mysteries[ $series ] ?? null;
				$is_today     = ( $day === $today && $atts['show_today'] === 'yes' );

				$html .= '<li class="' . ( $is_today ? 'is-today' : '' ) . '">';
				$html .= '<strong>' . esc_html( $day ) . ( $is_today ? ' (' . esc_html__( 'Today', 'parish-core' ) . ')' : '' ) . ':</strong> ';

				if ( $rosary_link && $mystery_info ) {
					$link_url = trailingslashit( $rosary_link ) . '#' . $mystery_info['anchor'];
					$html .= '<a href="' . esc_url( $link_url ) . '">' . esc_html( $mystery_info['name'] ) . '</a>';
				} else {
					$html .= esc_html( $mystery_info['name'] ?? $series . ' Mysteries' );
				}

				$html .= '</li>';
			}

			$html .= '</ul>';
		} else {
			// Table format.
			$html .= '<table class="rosary-week-table">';
			$html .= '<thead><tr>';
			$html .= '<th>' . esc_html__( 'Day', 'parish-core' ) . '</th>';
			$html .= '<th>' . esc_html__( 'Mysteries', 'parish-core' ) . '</th>';
			$html .= '</tr></thead>';
			$html .= '<tbody>';

			$days_order = array( 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' );

			foreach ( $days_order as $day ) {
				$series = $rosary_week[ $day ] ?? '';
				if ( ! $series ) {
					continue;
				}

				$mystery_info = $this->rosary_mysteries[ $series ] ?? null;
				$is_today     = ( $day === $today && $atts['show_today'] === 'yes' );

				$html .= '<tr class="' . ( $is_today ? 'is-today' : '' ) . '">';
				$html .= '<td><strong>' . esc_html( $day ) . '</strong>';
				if ( $is_today ) {
					$html .= ' <span class="today-badge">' . esc_html__( 'Today', 'parish-core' ) . '</span>';
				}
				$html .= '</td>';
				$html .= '<td>';

				if ( $rosary_link && $mystery_info ) {
					$link_url = trailingslashit( $rosary_link ) . '#' . $mystery_info['anchor'];
					$html .= '<a href="' . esc_url( $link_url ) . '">' . esc_html( $mystery_info['name'] ) . '</a>';
				} else {
					$html .= esc_html( $mystery_info['name'] ?? $series . ' Mysteries' );
				}

				$html .= '</td>';
				$html .= '</tr>';
			}

			$html .= '</tbody>';
			$html .= '</table>';
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Render rosary series shortcode.
	 *
	 * @param array  $atts    Shortcode attributes.
	 * @param string $content Shortcode content.
	 */
	public function render_rosary_series( $atts, $content = '' ): string {
		$atts = shortcode_atts( array(
			'link'   => '',
			'series' => '', // Specific series: joyful, sorrowful, glorious, luminous.
		), $atts, 'rosary_series' );

		// Try to get from rosary_days endpoint.
		$data = $this->get_reading( 'rosary_days' );

		// If not available, try to fetch.
		if ( empty( $data ) ) {
			$this->fetch_liturgy_day_endpoint( 'rosary_days' );
			$data = $this->get_reading( 'rosary_days' );
		}

		$rosary_days = $data['rosary-days'] ?? array();
		$season      = $data['season'] ?? '';
		$rosary_link = $atts['link'];

		// If specific series requested.
		$specific_series = ucfirst( strtolower( $atts['series'] ) );

		$html = '<div class="parish-readings-content parish-rosary-series">';

		if ( $season ) {
			$html .= '<p class="rosary-season">';
			$html .= '<strong>' . sprintf( esc_html__( 'During %s:', 'parish-core' ), esc_html( $season ) ) . '</strong>';
			$html .= '</p>';
		}

		$series_to_show = $specific_series ? array( $specific_series ) : array( 'Joyful', 'Sorrowful', 'Glorious', 'Luminous' );

		$html .= '<div class="rosary-series-grid">';

		foreach ( $series_to_show as $series ) {
			$mystery_info = $this->rosary_mysteries[ $series ] ?? null;

			if ( ! $mystery_info ) {
				continue;
			}

			$days = $rosary_days[ $series ] ?? array();

			$html .= '<div class="rosary-series-card">';
			$html .= '<h4>';

			if ( $rosary_link ) {
				$link_url = trailingslashit( $rosary_link ) . '#' . $mystery_info['anchor'];
				$html .= '<a href="' . esc_url( $link_url ) . '">' . esc_html( $mystery_info['name'] ) . '</a>';
			} else {
				$html .= esc_html( $mystery_info['name'] );
			}

			$html .= '</h4>';

			$html .= '<p class="series-description">' . esc_html( $mystery_info['description'] ) . '</p>';

			if ( ! empty( $days ) ) {
				$html .= '<p class="series-days">';
				$html .= '<strong>' . esc_html__( 'Prayed on:', 'parish-core' ) . '</strong> ';
				$html .= esc_html( implode( ', ', $days ) );
				$html .= '</p>';
			}

			$html .= '</div>';
		}

		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Render rosary mysteries shortcode - displays the actual mysteries.
	 *
	 * @param array  $atts    Shortcode attributes.
	 * @param string $content Shortcode content.
	 */
	public function render_rosary_mysteries( $atts, $content = '' ): string {
		$atts = shortcode_atts( array(
			'series' => '', // joyful, sorrowful, glorious, luminous, or empty for all.
		), $atts, 'rosary_mysteries' );

		$series = ucfirst( strtolower( $atts['series'] ) );

		$html = '<div class="parish-readings-content parish-rosary-mysteries">';

		$series_to_show = $series ? array( $series ) : array( 'Joyful', 'Sorrowful', 'Glorious', 'Luminous' );

		foreach ( $series_to_show as $s ) {
			$mystery_info = $this->rosary_mysteries[ $s ] ?? null;

			if ( ! $mystery_info ) {
				continue;
			}

			$html .= '<div class="mysteries-section" id="' . esc_attr( $mystery_info['anchor'] ) . '">';
			$html .= '<h3>' . esc_html( $mystery_info['name'] ) . '</h3>';
			$html .= '<p class="mysteries-description">' . esc_html( $mystery_info['description'] ) . '</p>';

			$html .= '<ol class="mysteries-list">';
			foreach ( $mystery_info['mysteries'] as $mystery ) {
				$html .= '<li>' . esc_html( $mystery ) . '</li>';
			}
			$html .= '</ol>';
			$html .= '</div>';
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Calculate today's rosary based on day and season.
	 *
	 * @return array
	 */
	private function calculate_todays_rosary(): array {
		$day_of_week = current_time( 'l' );
		$season      = $this->calculate_liturgical_season();

		// Rosary schedule based on season.
		$schedule = $this->get_rosary_schedule( $season );
		$series   = $schedule[ $day_of_week ] ?? 'Joyful';

		return array(
			'rosary-series' => $series,
			'season'        => $season,
		);
	}

	/**
	 * Calculate week rosary schedule.
	 *
	 * @param string $season Liturgical season.
	 * @return array
	 */
	private function calculate_week_rosary( string $season ): array {
		return $this->get_rosary_schedule( $season );
	}

	/**
	 * Get rosary schedule for a season.
	 *
	 * @param string $season Liturgical season.
	 * @return array
	 */
	private function get_rosary_schedule( string $season ): array {
		// Traditional rosary schedule.
		$schedule = array(
			'Ordinary Time' => array(
				'Sunday'    => 'Glorious',
				'Monday'    => 'Joyful',
				'Tuesday'   => 'Sorrowful',
				'Wednesday' => 'Glorious',
				'Thursday'  => 'Luminous',
				'Friday'    => 'Sorrowful',
				'Saturday'  => 'Joyful',
			),
			'Easter' => array(
				'Sunday'    => 'Glorious',
				'Monday'    => 'Joyful',
				'Tuesday'   => 'Sorrowful',
				'Wednesday' => 'Glorious',
				'Thursday'  => 'Luminous',
				'Friday'    => 'Sorrowful',
				'Saturday'  => 'Joyful',
			),
			'Advent' => array(
				'Sunday'    => 'Joyful',
				'Monday'    => 'Joyful',
				'Tuesday'   => 'Sorrowful',
				'Wednesday' => 'Glorious',
				'Thursday'  => 'Luminous',
				'Friday'    => 'Sorrowful',
				'Saturday'  => 'Joyful',
			),
			'Christmas' => array(
				'Sunday'    => 'Joyful',
				'Monday'    => 'Joyful',
				'Tuesday'   => 'Sorrowful',
				'Wednesday' => 'Glorious',
				'Thursday'  => 'Luminous',
				'Friday'    => 'Sorrowful',
				'Saturday'  => 'Joyful',
			),
			'Lent' => array(
				'Sunday'    => 'Sorrowful',
				'Monday'    => 'Joyful',
				'Tuesday'   => 'Sorrowful',
				'Wednesday' => 'Glorious',
				'Thursday'  => 'Luminous',
				'Friday'    => 'Sorrowful',
				'Saturday'  => 'Joyful',
			),
		);

		return $schedule[ $season ] ?? $schedule['Ordinary Time'];
	}

	/**
	 * Calculate liturgical season.
	 *
	 * @return string
	 */
	private function calculate_liturgical_season(): string {
		$now   = new \DateTime( 'now', new \DateTimeZone( wp_timezone_string() ) );
		$year  = (int) $now->format( 'Y' );
		$month = (int) $now->format( 'n' );
		$day   = (int) $now->format( 'j' );

		// Easter calculation.
		$easter = new \DateTime( "{$year}-03-21" );
		$easter->modify( '+' . easter_days( $year ) . ' days' );

		// Key dates.
		$ash_wed   = clone $easter;
		$ash_wed->modify( '-46 days' );

		$pentecost = clone $easter;
		$pentecost->modify( '+49 days' );

		$christmas = new \DateTime( "{$year}-12-25" );

		// Advent start (4th Sunday before Christmas).
		$advent = clone $christmas;
		$dow    = (int) $christmas->format( 'N' );
		$advent->modify( '-' . ( 21 + ( $dow % 7 ) ) . ' days' );

		// Determine season.
		if ( $now >= $advent && $month === 12 && $day < 25 ) {
			return 'Advent';
		}
		if ( ( $month === 12 && $day >= 25 ) || ( $month === 1 && $day <= 13 ) ) {
			return 'Christmas';
		}
		if ( $now >= $ash_wed && $now < $easter ) {
			return 'Lent';
		}
		if ( $now >= $easter && $now <= $pentecost ) {
			return 'Easter';
		}

		return 'Ordinary Time';
	}

	/**
	 * Get status of all endpoints for admin display.
	 */
	public function get_endpoints_status(): array {
		$status = array();

		foreach ( $this->endpoints as $key => $config ) {
			$last_fetch = $this->get_last_fetch( $key );
			$has_data   = null !== $this->get_reading( $key );

			// Determine if this is a local endpoint (no API key required).
			$is_local = ! empty( $config['local'] );

			$status[ $key ] = array(
				'name'         => $config['name'],
				'shortcode'    => '[' . $config['shortcode'] . ']',
				'schedule'     => $config['schedule'],
				'last_fetch'   => $last_fetch,
				'has_data'     => $has_data,
				'fetchable'    => true,
				'requires_key' => ! $is_local,
				'local'        => $is_local,
			);
		}

		// Add additional computed shortcodes (not fetchable - they use data from other endpoints).
		$additional = array(
			'rosary_today' => array(
				'name'        => __( 'Rosary Today', 'parish-core' ),
				'shortcode'   => '[rosary_today]',
				'schedule'    => 'computed',
				'last_fetch'  => null,
				'has_data'    => true,
				'fetchable'   => false,
				'requires_key' => false,
				'note'        => __( 'Uses data from Liturgical Day endpoint', 'parish-core' ),
			),
			'rosary_week' => array(
				'name'        => __( 'Rosary Week', 'parish-core' ),
				'shortcode'   => '[rosary_week]',
				'schedule'    => 'computed',
				'last_fetch'  => null,
				'has_data'    => true,
				'fetchable'   => false,
				'requires_key' => false,
				'note'        => __( 'Uses data from Liturgical Week endpoint', 'parish-core' ),
			),
			'rosary_series' => array(
				'name'        => __( 'Rosary Series', 'parish-core' ),
				'shortcode'   => '[rosary_series]',
				'schedule'    => 'computed',
				'last_fetch'  => null,
				'has_data'    => true,
				'fetchable'   => false,
				'requires_key' => false,
				'note'        => __( 'Uses data from Rosary Days endpoint', 'parish-core' ),
			),
			'rosary_mysteries' => array(
				'name'        => __( 'Rosary Mysteries', 'parish-core' ),
				'shortcode'   => '[rosary_mysteries]',
				'schedule'    => 'static',
				'last_fetch'  => null,
				'has_data'    => true,
				'fetchable'   => false,
				'requires_key' => false,
				'note'        => __( 'Static content - no API required', 'parish-core' ),
			),
		);

		return array_merge( $status, $additional );
	}

	/**
	 * Get status of all scheduled cron events for debugging.
	 *
	 * @return array Cron status information.
	 */
	public function get_cron_status(): array {
		$status = array();

		// Check global hook.
		$global_hook      = 'parish_fetch_readings_cron';
		$global_next_run  = wp_next_scheduled( $global_hook );
		$status['global'] = array(
			'hook'       => $global_hook,
			'scheduled'  => (bool) $global_next_run,
			'next_run'   => $global_next_run ? gmdate( 'Y-m-d H:i:s', $global_next_run ) : null,
			'recurrence' => 'daily',
		);

		// Check per-endpoint hooks.
		$schedules = json_decode( Parish_Core::get_setting( 'readings_schedules', '{}' ), true ) ?: array();

		foreach ( array_keys( $this->endpoints ) as $endpoint ) {
			$hook     = "parish_fetch_{$endpoint}";
			$next_run = wp_next_scheduled( $hook );

			$schedule_config = $schedules[ $endpoint ] ?? array( 'schedule' => 'daily_once' );
			$schedule_type   = $schedule_config['schedule'] ?? 'daily_once';

			$endpoint_status = array(
				'hook'          => $hook,
				'scheduled'     => (bool) $next_run,
				'next_run'      => $next_run ? gmdate( 'Y-m-d H:i:s', $next_run ) : null,
				'schedule_type' => $schedule_type,
			);

			// Check indexed hooks for daily_twice.
			if ( 'daily_twice' === $schedule_type ) {
				$endpoint_status['indexed_hooks'] = array();
				foreach ( array( 0, 1 ) as $idx ) {
					$indexed_hook = "{$hook}_{$idx}";
					$indexed_next = wp_next_scheduled( $indexed_hook );
					$endpoint_status['indexed_hooks'][ $idx ] = array(
						'hook'      => $indexed_hook,
						'scheduled' => (bool) $indexed_next,
						'next_run'  => $indexed_next ? gmdate( 'Y-m-d H:i:s', $indexed_next ) : null,
					);
				}
			}

			$status['endpoints'][ $endpoint ] = $endpoint_status;
		}

		// Add hash info for debugging.
		$status['config_hash'] = array(
			'current' => md5( wp_json_encode( $schedules ) ),
			'stored'  => get_option( 'parish_readings_schedule_hash', '' ),
		);

		return $status;
	}

	/**
	 * Force reschedule all cron events.
	 * Useful for recovery after cron events are lost.
	 *
	 * @return array Results of rescheduling.
	 */
	public function force_reschedule_cron(): array {
		$results = array();

		// Clear the stored hash to force rescheduling.
		delete_option( 'parish_readings_schedule_hash' );

		// Clear and reschedule global hook.
		$global_hook = 'parish_fetch_readings_cron';
		wp_clear_scheduled_hook( $global_hook );

		$timezone  = wp_timezone();
		$now       = new DateTime( 'now', $timezone );
		$next_run  = new DateTime( 'tomorrow 05:00:00', $timezone );
		$today_5am = new DateTime( 'today 05:00:00', $timezone );
		if ( $now < $today_5am ) {
			$next_run = $today_5am;
		}

		$scheduled = wp_schedule_event( $next_run->getTimestamp(), 'daily', $global_hook );
		$results['global'] = array(
			'hook'      => $global_hook,
			'scheduled' => false !== $scheduled,
			'next_run'  => $next_run->format( 'Y-m-d H:i:s' ),
		);

		// Reschedule per-endpoint hooks.
		$schedules = json_decode( Parish_Core::get_setting( 'readings_schedules', '{}' ), true ) ?: array();

		foreach ( array_keys( $this->endpoints ) as $endpoint ) {
			$schedule_config = $schedules[ $endpoint ] ?? array(
				'schedule' => 'daily_once',
				'time'     => '05:00',
			);

			$this->schedule_endpoint( $endpoint, $schedule_config );

			$hook     = "parish_fetch_{$endpoint}";
			$next_run = wp_next_scheduled( $hook );

			$results['endpoints'][ $endpoint ] = array(
				'hook'      => $hook,
				'scheduled' => (bool) $next_run,
				'next_run'  => $next_run ? gmdate( 'Y-m-d H:i:s', $next_run ) : null,
			);
		}

		// Update stored hash.
		update_option( 'parish_readings_schedule_hash', md5( wp_json_encode( $schedules ) ) );

		return $results;
	}
}