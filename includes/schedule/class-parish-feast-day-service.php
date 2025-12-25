<?php
/**
 * Feast Day Service for Parish Core.
 *
 * Integrates with external liturgical APIs to provide feast day information.
 * Works with the existing Parish_Readings class for caching.
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Feast day service class.
 */
class Parish_Feast_Day_Service {

	/**
	 * Singleton instance.
	 *
	 * @var Parish_Feast_Day_Service|null
	 */
	private static ?Parish_Feast_Day_Service $instance = null;

	/**
	 * CalAPI base URL.
	 */
	private const CALAPI_BASE = 'http://calapi.inadiutorium.cz/api/v0/en/calendars/general-en/';

	/**
	 * Cache transient prefix.
	 */
	private const CACHE_PREFIX = 'parish_feast_';

	/**
	 * Cache duration in seconds (24 hours).
	 */
	private const CACHE_DURATION = DAY_IN_SECONDS;

	/**
	 * Liturgical color mappings.
	 */
	public const LITURGICAL_COLORS = array(
		'green'  => array(
			'name' => 'Green',
			'hex'  => '#228B22',
		),
		'white'  => array(
			'name' => 'White',
			'hex'  => '#FFFFFF',
		),
		'red'    => array(
			'name' => 'Red',
			'hex'  => '#DC143C',
		),
		'violet' => array(
			'name' => 'Violet',
			'hex'  => '#8B008B',
		),
		'rose'   => array(
			'name' => 'Rose',
			'hex'  => '#FF007F',
		),
		'black'  => array(
			'name' => 'Black',
			'hex'  => '#000000',
		),
		'gold'   => array(
			'name' => 'Gold',
			'hex'  => '#FFD700',
		),
	);

	/**
	 * Feast rank hierarchy.
	 */
	public const FEAST_RANKS = array(
		'solemnity'   => 1,
		'feast'       => 2,
		'memorial'    => 3,
		'opt_memorial' => 4,
		'feria'       => 5,
	);

	/**
	 * Get singleton instance.
	 *
	 * @return Parish_Feast_Day_Service
	 */
	public static function instance(): Parish_Feast_Day_Service {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Get feast day information for a date.
	 *
	 * @param string $date Date in Y-m-d format.
	 * @return array|null Feast day data or null.
	 */
	public function get_feast_day( string $date ): ?array {
		// Validate date format.
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			return null;
		}

		// Check cache first.
		$cached = $this->get_cached( $date );
		if ( null !== $cached ) {
			return $cached;
		}

		// Try to get from Parish_Readings if available.
		if ( class_exists( 'Parish_Readings' ) ) {
			$readings_data = $this->get_from_readings_class( $date );
			if ( null !== $readings_data ) {
				$this->set_cached( $date, $readings_data );
				return $readings_data;
			}
		}

		// Fetch from API.
		$api_data = $this->fetch_from_api( $date );
		if ( null !== $api_data ) {
			$this->set_cached( $date, $api_data );
			return $api_data;
		}

		// Return computed basic info if API fails.
		return $this->get_computed_info( $date );
	}

	/**
	 * Get feast days for a date range.
	 *
	 * @param string $start_date Start date (Y-m-d).
	 * @param string $end_date   End date (Y-m-d).
	 * @return array Feast days keyed by date.
	 */
	public function get_feast_days_for_range( string $start_date, string $end_date ): array {
		$feast_days = array();
		$current    = new DateTime( $start_date );
		$end        = new DateTime( $end_date );

		while ( $current <= $end ) {
			$date_str = $current->format( 'Y-m-d' );
			$feast    = $this->get_feast_day( $date_str );

			if ( null !== $feast ) {
				$feast_days[ $date_str ] = $feast;
			}

			$current->modify( '+1 day' );
		}

		return $feast_days;
	}

	/**
	 * Get cached feast day data.
	 *
	 * @param string $date Date (Y-m-d).
	 * @return array|null Cached data or null.
	 */
	private function get_cached( string $date ): ?array {
		$key  = self::CACHE_PREFIX . str_replace( '-', '', $date );
		$data = get_transient( $key );

		if ( false === $data ) {
			return null;
		}

		return $data;
	}

	/**
	 * Set cached feast day data.
	 *
	 * @param string $date Date (Y-m-d).
	 * @param array  $data Data to cache.
	 * @return void
	 */
	private function set_cached( string $date, array $data ): void {
		$key = self::CACHE_PREFIX . str_replace( '-', '', $date );
		set_transient( $key, $data, self::CACHE_DURATION );
	}

	/**
	 * Try to get feast data from Parish_Readings class.
	 *
	 * @param string $date Date (Y-m-d).
	 * @return array|null Feast data or null.
	 */
	private function get_from_readings_class( string $date ): ?array {
		if ( ! class_exists( 'Parish_Readings' ) ) {
			return null;
		}

		$readings = Parish_Readings::instance();

		// Check if we have feast_day_details endpoint data.
		$feast_data = $readings->get_reading( 'feast_day_details' );

		if ( empty( $feast_data ) ) {
			return null;
		}

		// Check if the cached data is for our date.
		$cached_date = $feast_data['date'] ?? '';
		if ( $cached_date !== $date ) {
			return null;
		}

		return $this->format_api_response( $feast_data );
	}

	/**
	 * Fetch feast day from CalAPI.
	 *
	 * @param string $date Date (Y-m-d).
	 * @return array|null Formatted feast data or null.
	 */
	private function fetch_from_api( string $date ): ?array {
		$url = self::CALAPI_BASE . $date;

		$response = wp_remote_get(
			$url,
			array(
				'timeout'    => 10,
				'user-agent' => 'ParishCore/' . PARISH_CORE_VERSION,
			)
		);

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $code ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( empty( $data ) || ! is_array( $data ) ) {
			return null;
		}

		return $this->format_api_response( $data );
	}

	/**
	 * Format API response to standard structure.
	 *
	 * @param array $data Raw API data.
	 * @return array Formatted feast data.
	 */
	private function format_api_response( array $data ): array {
		$celebrations = $data['celebrations'] ?? array();

		if ( empty( $celebrations ) ) {
			return array(
				'title'            => '',
				'rank'             => 'feria',
				'color'            => 'green',
				'color_hex'        => self::LITURGICAL_COLORS['green']['hex'],
				'season'           => $data['season'] ?? 'ordinary',
				'season_week'      => $data['season_week'] ?? null,
				'all_celebrations' => array(),
			);
		}

		$primary = $celebrations[0];
		$color   = strtolower( $primary['colour'] ?? 'green' );

		return array(
			'title'            => $primary['title'] ?? '',
			'rank'             => $this->normalize_rank( $primary['rank'] ?? '' ),
			'color'            => $color,
			'color_hex'        => self::LITURGICAL_COLORS[ $color ]['hex'] ?? self::LITURGICAL_COLORS['green']['hex'],
			'season'           => $data['season'] ?? 'ordinary',
			'season_week'      => $data['season_week'] ?? null,
			'all_celebrations' => array_map(
				function ( $c ) {
					return array(
						'title' => $c['title'] ?? '',
						'rank'  => $this->normalize_rank( $c['rank'] ?? '' ),
						'color' => strtolower( $c['colour'] ?? 'green' ),
					);
				},
				$celebrations
			),
		);
	}

	/**
	 * Normalize feast rank to standard format.
	 *
	 * @param string $rank Raw rank string.
	 * @return string Normalized rank.
	 */
	private function normalize_rank( string $rank ): string {
		$rank = strtolower( $rank );

		if ( str_contains( $rank, 'solemnity' ) ) {
			return 'solemnity';
		}
		if ( str_contains( $rank, 'feast' ) ) {
			return 'feast';
		}
		if ( str_contains( $rank, 'optional' ) ) {
			return 'opt_memorial';
		}
		if ( str_contains( $rank, 'memorial' ) ) {
			return 'memorial';
		}

		return 'feria';
	}

	/**
	 * Get computed liturgical info when API is unavailable.
	 *
	 * @param string $date Date (Y-m-d).
	 * @return array Basic liturgical info.
	 */
	private function get_computed_info( string $date ): array {
		$date_obj = new DateTime( $date );
		$year     = (int) $date_obj->format( 'Y' );
		$season   = $this->compute_liturgical_season( $date_obj, $year );
		$color    = $this->get_season_color( $season );

		return array(
			'title'            => '',
			'rank'             => 'feria',
			'color'            => $color,
			'color_hex'        => self::LITURGICAL_COLORS[ $color ]['hex'],
			'season'           => $season,
			'season_week'      => null,
			'all_celebrations' => array(),
		);
	}

	/**
	 * Compute liturgical season for a date.
	 *
	 * @param DateTime $date Date object.
	 * @param int      $year Year.
	 * @return string Season name.
	 */
	private function compute_liturgical_season( DateTime $date, int $year ): string {
		$easter       = Parish_Recurrence::get_easter( $year );
		$ash_wed      = ( clone $easter )->modify( '-46 days' );
		$pentecost    = ( clone $easter )->modify( '+49 days' );
		$advent_start = Parish_Recurrence::get_advent_start( $year );
		$christmas    = new DateTime( "{$year}-12-25" );
		$epiphany     = new DateTime( "{$year}-01-06" );

		// Previous year's Christmas season might extend into January.
		$prev_epiphany = new DateTime( ( $year - 1 ) . '-01-06' );

		$date_str = $date->format( 'Y-m-d' );

		// Advent.
		if ( $date >= $advent_start && $date < $christmas ) {
			return 'advent';
		}

		// Christmas season (Dec 25 - Epiphany).
		if ( $date >= $christmas || $date <= $epiphany ) {
			return 'christmas';
		}

		// Lent.
		if ( $date >= $ash_wed && $date < $easter ) {
			return 'lent';
		}

		// Easter season.
		if ( $date >= $easter && $date <= $pentecost ) {
			return 'easter';
		}

		// Ordinary Time.
		return 'ordinary';
	}

	/**
	 * Get default liturgical color for a season.
	 *
	 * @param string $season Season name.
	 * @return string Color name.
	 */
	private function get_season_color( string $season ): string {
		switch ( $season ) {
			case 'advent':
			case 'lent':
				return 'violet';
			case 'christmas':
			case 'easter':
				return 'white';
			default:
				return 'green';
		}
	}

	/**
	 * Get display name for a season.
	 *
	 * @param string $season Season identifier.
	 * @return string Display name.
	 */
	public static function get_season_name( string $season ): string {
		$names = array(
			'advent'    => __( 'Advent', 'parish-core' ),
			'christmas' => __( 'Christmas', 'parish-core' ),
			'lent'      => __( 'Lent', 'parish-core' ),
			'easter'    => __( 'Easter', 'parish-core' ),
			'ordinary'  => __( 'Ordinary Time', 'parish-core' ),
		);

		return $names[ $season ] ?? $season;
	}

	/**
	 * Check if a feast is a major celebration.
	 *
	 * @param array $feast Feast data.
	 * @return bool True if solemnity or feast.
	 */
	public static function is_major_feast( array $feast ): bool {
		$rank = $feast['rank'] ?? 'feria';
		return in_array( $rank, array( 'solemnity', 'feast' ), true );
	}

	/**
	 * Format feast day for display.
	 *
	 * @param array  $feast   Feast data.
	 * @param string $format  Format: 'full', 'badge', 'inline'.
	 * @return string HTML output.
	 */
	public function format_for_display( array $feast, string $format = 'inline' ): string {
		if ( empty( $feast['title'] ) ) {
			return '';
		}

		$title     = esc_html( $feast['title'] );
		$color_hex = $feast['color_hex'] ?? '#228B22';
		$rank      = $feast['rank'] ?? 'feria';

		switch ( $format ) {
			case 'badge':
				return sprintf(
					'<span class="parish-feast-badge parish-feast-rank-%s" style="background-color: %s;">%s</span>',
					esc_attr( $rank ),
					esc_attr( $color_hex ),
					$title
				);

			case 'full':
				$season_name = self::get_season_name( $feast['season'] ?? 'ordinary' );
				$rank_name   = ucfirst( $rank );
				return sprintf(
					'<div class="parish-feast-full">
						<span class="parish-feast-color" style="background-color: %s;"></span>
						<span class="parish-feast-title">%s</span>
						<span class="parish-feast-meta">%s &middot; %s</span>
					</div>',
					esc_attr( $color_hex ),
					$title,
					esc_html( $rank_name ),
					esc_html( $season_name )
				);

			case 'inline':
			default:
				return sprintf(
					'<span class="parish-feast-inline" style="border-left-color: %s;">%s</span>',
					esc_attr( $color_hex ),
					$title
				);
		}
	}
}
