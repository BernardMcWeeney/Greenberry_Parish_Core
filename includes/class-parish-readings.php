<?php
/**
 * Catholic Readings API Integration Module
 *
 * Fetches and caches readings from the Catholic Readings API.
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
	 * API base URL.
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
	 * Get singleton instance.
	 */
	public static function instance(): Parish_Readings {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->setup_endpoints();

		// Register shortcodes.
		add_action( 'init', array( $this, 'register_shortcodes' ) );

		// Schedule daily fetch.
		add_action( 'init', array( $this, 'schedule_cron' ) );
		add_action( 'parish_fetch_readings_cron', array( $this, 'fetch_all_readings' ) );
	}

	/**
	 * Setup API endpoints configuration.
	 */
	private function setup_endpoints(): void {
		$this->endpoints = array(
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
	 */
	public function register_shortcodes(): void {
		foreach ( $this->endpoints as $key => $config ) {
			add_shortcode( $config['shortcode'], array( $this, 'render_shortcode' ) );
		}
	}

	/**
	 * Schedule daily cron job.
	 */
	public function schedule_cron(): void {
		if ( ! wp_next_scheduled( 'parish_fetch_readings_cron' ) ) {
			// Schedule for 5am daily.
			$timestamp = strtotime( 'tomorrow 05:00:00' );
			wp_schedule_event( $timestamp, 'daily', 'parish_fetch_readings_cron' );
		}
	}

	/**
	 * Fetch all readings from API.
	 */
	public function fetch_all_readings(): array {
		$api_key = Parish_Core::get_setting( 'readings_api_key', '' );
		$results = array();

		if ( empty( $api_key ) ) {
			return array(
				'success' => false,
				'message' => __( 'API key not configured.', 'parish-core' ),
			);
		}

		foreach ( array_keys( $this->endpoints ) as $endpoint ) {
			$result = $this->fetch_endpoint( $endpoint, $api_key );
			$results[ $endpoint ] = $result;
		}

		return array(
			'success' => true,
			'results' => $results,
		);
	}

	/**
	 * Fetch a single endpoint.
	 *
	 * @param string $endpoint Endpoint key.
	 * @param string $api_key  API key (optional, will use settings if not provided).
	 */
	public function fetch_endpoint( string $endpoint, ?string $api_key = null ): array {
		if ( ! isset( $this->endpoints[ $endpoint ] ) ) {
			return array(
				'success' => false,
				'message' => __( 'Invalid endpoint.', 'parish-core' ),
			);
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

		$config   = $this->endpoints[ $endpoint ];
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
			return '<div class="parish-readings-content parish-readings-empty">' .
			       '<p>' . esc_html__( 'Content not available. Please check back later.', 'parish-core' ) . '</p>' .
			       '</div>';
		}

		return $this->format_reading( $endpoint, $data );
	}

	/**
	 * Format reading data for display.
	 *
	 * @param string $endpoint Endpoint key.
	 * @param array  $data     Reading data.
	 */
	private function format_reading( string $endpoint, array $data ): string {
		switch ( $endpoint ) {
			case 'mass_reading_details':
				return $this->format_mass_readings( $data );

			case 'feast_day_details':
				return $this->format_feast_days( $data );

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

		$html = '<div class="parish-readings-content parish-mass-readings">';
		$html .= '<dl class="readings-list">';

		foreach ( $sections as $key => $label ) {
			if ( ! empty( $content[ $key ] ) ) {
				$html .= '<dt class="reading-label">' . esc_html( $label ) . '</dt>';
				$html .= '<dd class="reading-text">' . wp_kses_post( $content[ $key ] ) . '</dd>';
			}
		}

		$html .= '</dl>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Format feast day details.
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
		$html .= '<ul class="feast-list">';

		foreach ( $celebrations as $celebration ) {
			$title  = $celebration['title'] ?? __( 'Unknown', 'parish-core' );
			$colour = strtolower( $celebration['colour'] ?? '' );
			$rank   = $celebration['rank'] ?? '';

			$html .= '<li class="feast-item">';
			$html .= '<strong class="feast-title">' . esc_html( $title ) . '</strong>';

			if ( $colour ) {
				$html .= ' <span class="liturgical-colour colour-' . esc_attr( $colour ) . '">';
				$html .= esc_html( ucfirst( $colour ) );
				$html .= '</span>';
			}

			if ( $rank ) {
				$html .= ' <span class="feast-rank">';
				$html .= esc_html( ucwords( str_replace( '_', ' ', $rank ) ) );
				$html .= '</span>';
			}

			$html .= '</li>';
		}

		$html .= '</ul>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Get status of all endpoints for admin display.
	 */
	public function get_endpoints_status(): array {
		$status = array();

		foreach ( $this->endpoints as $key => $config ) {
			$last_fetch = $this->get_last_fetch( $key );
			$has_data   = null !== $this->get_reading( $key );

			$status[ $key ] = array(
				'name'       => $config['name'],
				'shortcode'  => '[' . $config['shortcode'] . ']',
				'schedule'   => $config['schedule'],
				'last_fetch' => $last_fetch,
				'has_data'   => $has_data,
			);
		}

		return $status;
	}
}
