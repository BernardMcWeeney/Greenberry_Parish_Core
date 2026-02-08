<?php
/**
 * Events Calendar Block
 *
 * Full month calendar view with iCal subscription support.
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Events Calendar Block class.
 */
class Parish_Events_Calendar_Block {

	/**
	 * Register the block with WordPress.
	 *
	 * @return void
	 */
	public static function register(): void {
		register_block_type(
			'parish/events-calendar',
			array(
				'api_version'     => 3,
				'editor_script'   => 'parish-core-editor-blocks',
				'render_callback' => array( __CLASS__, 'render' ),
				'attributes'      => array(
					'sacrament'        => array(
						'type'    => 'string',
						'default' => '',
					),
					'churchId'         => array(
						'type'    => 'integer',
						'default' => 0,
					),
					'cemeteryId'       => array(
						'type'    => 'integer',
						'default' => 0,
					),
					'autoDetect'       => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'showSubscribe'    => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'showDownload'     => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'iconColor'        => array(
						'type'    => 'string',
						'default' => '',
					),
					'timeColor'        => array(
						'type'    => 'string',
						'default' => '',
					),
				),
				'supports'        => array(
					'html'     => false,
					'align'    => array( 'wide', 'full' ),
					'spacing'  => array(
						'margin'  => true,
						'padding' => true,
					),
				),
			)
		);
	}

	/**
	 * Initialize iCal feed handling.
	 * Called early on init hook via parish-core.php.
	 *
	 * @return void
	 */
	public static function init_ical_feed(): void {
		// Handle iCal requests via query parameter.
		add_action( 'template_redirect', array( __CLASS__, 'handle_ical_request' ) );

		// Register REST API endpoint for iCal.
		add_action( 'rest_api_init', array( __CLASS__, 'register_rest_endpoint' ) );
	}

	/**
	 * Register REST API endpoint for iCal feed.
	 *
	 * @return void
	 */
	public static function register_rest_endpoint(): void {
		register_rest_route(
			'parish-core/v1',
			'/events/calendar.ics',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'rest_ical_callback' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'sacrament' => array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'church'    => array(
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					),
					'cemetery'  => array(
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					),
					'download'  => array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'default'           => '',
					),
				),
			)
		);
	}

	/**
	 * REST API callback for iCal feed.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return void|WP_Error Outputs iCal directly or returns error.
	 */
	public static function rest_ical_callback( $request ) {
		$sacrament   = $request->get_param( 'sacrament' ) ?? '';
		$church_id   = $request->get_param( 'church' ) ?? 0;
		$cemetery_id = $request->get_param( 'cemetery' ) ?? 0;
		$download    = $request->get_param( 'download' ) ?? '';

		$ical_content = self::generate_ical( $sacrament, $church_id, $cemetery_id );

		// Generate a descriptive filename.
		$filename = self::generate_ical_filename( $sacrament, $church_id, $cemetery_id );

		// Set proper headers for iCal content.
		header( 'Content-Type: text/calendar; charset=utf-8' );
		header( 'Cache-Control: no-cache, no-store, must-revalidate' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );
		header( 'X-Content-Type-Options: nosniff' );

		// Only add download disposition if explicitly downloading.
		if ( $download === '1' ) {
			header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		} else {
			// For subscription, use inline so calendar apps can read it.
			header( 'Content-Disposition: inline; filename="' . $filename . '"' );
		}

		// Output iCal content directly and exit.
		echo $ical_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	/**
	 * Generate a descriptive filename for the iCal file.
	 *
	 * @param string $sacrament   Sacrament slug filter.
	 * @param int    $church_id   Church ID filter.
	 * @param int    $cemetery_id Cemetery ID filter.
	 * @return string Filename.
	 */
	private static function generate_ical_filename( string $sacrament = '', int $church_id = 0, int $cemetery_id = 0 ): string {
		$parts = array( 'parish-events' );

		if ( ! empty( $sacrament ) ) {
			$parts[] = sanitize_file_name( $sacrament );
		}

		if ( $church_id > 0 ) {
			$church_name = get_the_title( $church_id );
			if ( $church_name ) {
				$parts[] = sanitize_file_name( $church_name );
			}
		}

		if ( $cemetery_id > 0 ) {
			$cemetery_name = get_the_title( $cemetery_id );
			if ( $cemetery_name ) {
				$parts[] = sanitize_file_name( $cemetery_name );
			}
		}

		return implode( '-', $parts ) . '.ics';
	}

	/**
	 * Handle iCal feed requests via query parameter (legacy support).
	 *
	 * @return void
	 */
	public static function handle_ical_request(): void {
		if ( ! isset( $_GET['parish_ical'] ) || $_GET['parish_ical'] !== '1' ) {
			return;
		}

		// Verify nonce for download requests to prevent CSRF.
		$download = isset( $_GET['download'] ) && $_GET['download'] === '1';

		// Get filter parameters.
		$sacrament   = isset( $_GET['sacrament'] ) ? sanitize_text_field( wp_unslash( $_GET['sacrament'] ) ) : '';
		$church_id   = isset( $_GET['church'] ) ? absint( $_GET['church'] ) : 0;
		$cemetery_id = isset( $_GET['cemetery'] ) ? absint( $_GET['cemetery'] ) : 0;

		$ical_content = self::generate_ical( $sacrament, $church_id, $cemetery_id );
		$filename     = self::generate_ical_filename( $sacrament, $church_id, $cemetery_id );

		// Output iCal with proper headers.
		header( 'Content-Type: text/calendar; charset=utf-8' );
		header( 'Cache-Control: no-cache, no-store, must-revalidate' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );
		header( 'X-Content-Type-Options: nosniff' );

		if ( $download ) {
			header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		} else {
			header( 'Content-Disposition: inline; filename="' . $filename . '"' );
		}

		echo $ical_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	/**
	 * Generate iCal content.
	 *
	 * @param string $sacrament   Sacrament slug filter.
	 * @param int    $church_id   Church ID filter.
	 * @param int    $cemetery_id Cemetery ID filter.
	 * @return string iCal content.
	 */
	private static function generate_ical( string $sacrament = '', int $church_id = 0, int $cemetery_id = 0 ): string {
		// Query events - get events from 30 days ago to 1 year ahead.
		$args = array(
			'post_type'      => 'parish_event',
			'posts_per_page' => 500,
			'post_status'    => 'publish',
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => 'parish_event_date',
					'value'   => gmdate( 'Y-m-d', strtotime( '-30 days' ) ),
					'compare' => '>=',
					'type'    => 'DATE',
				),
				array(
					'key'     => 'parish_event_date',
					'value'   => gmdate( 'Y-m-d', strtotime( '+1 year' ) ),
					'compare' => '<=',
					'type'    => 'DATE',
				),
			),
			'orderby'        => 'meta_value',
			'meta_key'       => 'parish_event_date',
			'order'          => 'ASC',
		);

		if ( ! empty( $sacrament ) ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'parish_sacrament',
					'field'    => 'slug',
					'terms'    => $sacrament,
				),
			);
		}

		if ( $church_id > 0 ) {
			$args['meta_query'][] = array(
				'key'     => 'parish_event_church_id',
				'value'   => $church_id,
				'compare' => '=',
				'type'    => 'NUMERIC',
			);
		}

		if ( $cemetery_id > 0 ) {
			$args['meta_query'][] = array(
				'key'     => 'parish_event_cemetery_id',
				'value'   => $cemetery_id,
				'compare' => '=',
				'type'    => 'NUMERIC',
			);
		}

		$query = new WP_Query( $args );

		// Generate iCal content.
		$site_name = get_bloginfo( 'name' );
		$site_url  = home_url();
		$site_host = wp_parse_url( $site_url, PHP_URL_HOST ) ?: 'parish.local';
		$timezone  = wp_timezone_string();

		// Build calendar name based on filters.
		$calendar_name = $site_name . ' - Parish Events';
		if ( ! empty( $sacrament ) ) {
			$term = get_term_by( 'slug', $sacrament, 'parish_sacrament' );
			if ( $term ) {
				$calendar_name = $site_name . ' - ' . $term->name;
			}
		}
		if ( $church_id > 0 ) {
			$church_name = get_the_title( $church_id );
			if ( $church_name ) {
				$calendar_name = $church_name . ' - Events';
			}
		}
		if ( $cemetery_id > 0 ) {
			$cemetery_name = get_the_title( $cemetery_id );
			if ( $cemetery_name ) {
				$calendar_name = $cemetery_name . ' - Events';
			}
		}

		$ical  = "BEGIN:VCALENDAR\r\n";
		$ical .= "VERSION:2.0\r\n";
		$ical .= "PRODID:-//Parish Core//Events Calendar//EN\r\n";
		$ical .= "CALSCALE:GREGORIAN\r\n";
		$ical .= "METHOD:PUBLISH\r\n";
		$ical .= 'X-WR-CALNAME:' . self::ical_escape( $calendar_name ) . "\r\n";
		$ical .= 'X-WR-TIMEZONE:' . self::ical_escape( $timezone ) . "\r\n";
		$ical .= "REFRESH-INTERVAL;VALUE=DURATION:PT1H\r\n";
		$ical .= "X-PUBLISHED-TTL:PT1H\r\n";

		if ( $query->have_posts() ) {
			$wp_timezone = wp_timezone();

			while ( $query->have_posts() ) {
				$query->the_post();
				$post_id = get_the_ID();

				$event_date     = get_post_meta( $post_id, 'parish_event_date', true );
				$event_time     = get_post_meta( $post_id, 'parish_event_time', true );
				$event_end_time = get_post_meta( $post_id, 'parish_event_end_time', true );
				$event_location = get_post_meta( $post_id, 'parish_event_location', true );

				if ( empty( $event_date ) ) {
					continue;
				}

				// Validate date format.
				if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $event_date ) ) {
					continue;
				}

				// Build start/end times.
				$all_day = empty( $event_time );

				$ical .= "BEGIN:VEVENT\r\n";
				$ical .= 'UID:parish-event-' . $post_id . '@' . $site_host . "\r\n";
				$ical .= 'DTSTAMP:' . gmdate( 'Ymd\THis\Z' ) . "\r\n";

				if ( ! $all_day ) {
					// Create DateTime in WordPress timezone for proper conversion.
					try {
						$start_dt = new DateTime( $event_date . ' ' . $event_time, $wp_timezone );
						$start_utc = clone $start_dt;
						$start_utc->setTimezone( new DateTimeZone( 'UTC' ) );

						if ( ! empty( $event_end_time ) ) {
							$end_dt = new DateTime( $event_date . ' ' . $event_end_time, $wp_timezone );
						} else {
							// Default to 1 hour duration.
							$end_dt = clone $start_dt;
							$end_dt->modify( '+1 hour' );
						}
						$end_utc = clone $end_dt;
						$end_utc->setTimezone( new DateTimeZone( 'UTC' ) );

						$ical .= 'DTSTART:' . $start_utc->format( 'Ymd\THis\Z' ) . "\r\n";
						$ical .= 'DTEND:' . $end_utc->format( 'Ymd\THis\Z' ) . "\r\n";
					} catch ( Exception $e ) {
						// Fallback to all-day if datetime parsing fails.
						$all_day = true;
					}
				}

				if ( $all_day ) {
					$start_datetime = gmdate( 'Ymd', strtotime( $event_date ) );
					$end_datetime   = gmdate( 'Ymd', strtotime( $event_date . ' +1 day' ) );
					$ical .= 'DTSTART;VALUE=DATE:' . $start_datetime . "\r\n";
					$ical .= 'DTEND;VALUE=DATE:' . $end_datetime . "\r\n";
				}

				$ical .= 'SUMMARY:' . self::ical_escape( get_the_title() ) . "\r\n";

				if ( ! empty( $event_location ) ) {
					$ical .= 'LOCATION:' . self::ical_escape( $event_location ) . "\r\n";
				}

				$description = wp_strip_all_tags( get_the_excerpt() );
				if ( ! empty( $description ) ) {
					$ical .= 'DESCRIPTION:' . self::ical_escape( $description ) . "\r\n";
				}

				$ical .= 'URL:' . esc_url_raw( get_permalink() ) . "\r\n";
				$ical .= 'STATUS:CONFIRMED' . "\r\n";
				$ical .= 'TRANSP:OPAQUE' . "\r\n";
				$ical .= "END:VEVENT\r\n";
			}
			wp_reset_postdata();
		}

		$ical .= "END:VCALENDAR\r\n";

		return $ical;
	}

	/**
	 * Escape text for iCal format.
	 *
	 * @param string $text Text to escape.
	 * @return string Escaped text.
	 */
	private static function ical_escape( string $text ): string {
		// First, normalize line endings and strip any HTML.
		$text = wp_strip_all_tags( $text );
		$text = str_replace( array( "\r\n", "\r" ), "\n", $text );

		// Escape special iCal characters in order: backslash first, then others.
		$text = str_replace( '\\', '\\\\', $text );
		$text = str_replace( ';', '\\;', $text );
		$text = str_replace( ',', '\\,', $text );
		$text = str_replace( "\n", '\\n', $text );

		return $text;
	}

	/**
	 * Fold long lines for iCal format (RFC 5545 requires max 75 octets per line).
	 *
	 * @param string $line Line to fold.
	 * @return string Folded line.
	 */
	private static function ical_fold_line( string $line ): string {
		$max_length = 75;

		if ( strlen( $line ) <= $max_length ) {
			return $line;
		}

		$folded = '';
		while ( strlen( $line ) > $max_length ) {
			if ( $folded === '' ) {
				$folded = substr( $line, 0, $max_length );
				$line   = substr( $line, $max_length );
			} else {
				// Continuation lines start with a space/tab.
				$folded .= "\r\n " . substr( $line, 0, $max_length - 1 );
				$line    = substr( $line, $max_length - 1 );
			}
		}

		if ( $line !== '' ) {
			$folded .= "\r\n " . $line;
		}

		return $folded;
	}

	/**
	 * Auto-detect church or cemetery ID from current page context.
	 *
	 * @return array Array with 'church_id' and 'cemetery_id' keys.
	 */
	private static function detect_context(): array {
		$context = array(
			'church_id'   => 0,
			'cemetery_id' => 0,
		);

		$post_id = get_the_ID();
		if ( ! $post_id ) {
			global $post;
			$post_id = $post ? $post->ID : 0;
		}

		if ( ! $post_id ) {
			return $context;
		}

		$post_type = get_post_type( $post_id );

		if ( $post_type === 'parish_church' ) {
			$context['church_id'] = $post_id;
		} elseif ( $post_type === 'parish_cemetery' ) {
			$context['cemetery_id'] = $post_id;
		}

		return $context;
	}

	/**
	 * Get the iCal feed URL with filters.
	 *
	 * @param string $sacrament   Sacrament slug filter.
	 * @param int    $church_id   Church ID filter.
	 * @param int    $cemetery_id Cemetery ID filter.
	 * @param bool   $download    Whether this is a download URL.
	 * @return string Feed URL.
	 */
	private static function get_ical_url( string $sacrament = '', int $church_id = 0, int $cemetery_id = 0, bool $download = false ): string {
		// Use REST API endpoint for cleaner URLs.
		$url = rest_url( 'parish-core/v1/events/calendar.ics' );

		$params = array();
		if ( ! empty( $sacrament ) ) {
			$params['sacrament'] = $sacrament;
		}
		if ( $church_id > 0 ) {
			$params['church'] = $church_id;
		}
		if ( $cemetery_id > 0 ) {
			$params['cemetery'] = $cemetery_id;
		}
		if ( $download ) {
			$params['download'] = '1';
		}

		if ( ! empty( $params ) ) {
			$url = add_query_arg( $params, $url );
		}

		return $url;
	}

	/**
	 * Get the webcal subscription URL.
	 *
	 * @param string $sacrament   Sacrament slug filter.
	 * @param int    $church_id   Church ID filter.
	 * @param int    $cemetery_id Cemetery ID filter.
	 * @return string Webcal URL.
	 */
	private static function get_webcal_url( string $sacrament = '', int $church_id = 0, int $cemetery_id = 0 ): string {
		$https_url = self::get_ical_url( $sacrament, $church_id, $cemetery_id, false );
		// Convert https:// to webcal:// for native calendar app subscription.
		return preg_replace( '/^https?:/', 'webcal:', $https_url );
	}

	/**
	 * Get the Google Calendar subscription URL.
	 *
	 * @param string $sacrament   Sacrament slug filter.
	 * @param int    $church_id   Church ID filter.
	 * @param int    $cemetery_id Cemetery ID filter.
	 * @return string Google Calendar URL.
	 */
	private static function get_google_calendar_url( string $sacrament = '', int $church_id = 0, int $cemetery_id = 0 ): string {
		$ical_url = self::get_ical_url( $sacrament, $church_id, $cemetery_id, false );

		// Google Calendar requires the URL to use webcal:// or https:// protocol.
		// Using cid parameter with the webcal URL for subscription.
		$webcal_url = preg_replace( '/^https?:/', 'webcal:', $ical_url );

		return 'https://calendar.google.com/calendar/r?cid=' . rawurlencode( $webcal_url );
	}

	/**
	 * Render the block on the frontend.
	 *
	 * @param array $attributes Block attributes.
	 * @return string Rendered HTML.
	 */
	public static function render( array $attributes ): string {
		// Feature check.
		if ( ! Parish_Core::is_feature_enabled( 'events' ) ) {
			return '';
		}

		$sacrament      = sanitize_text_field( $attributes['sacrament'] ?? '' );
		$church_id      = absint( $attributes['churchId'] ?? 0 );
		$cemetery_id    = absint( $attributes['cemeteryId'] ?? 0 );
		$auto_detect    = (bool) ( $attributes['autoDetect'] ?? true );
		$show_subscribe = (bool) ( $attributes['showSubscribe'] ?? true );
		$show_download  = (bool) ( $attributes['showDownload'] ?? true );
		$icon_color     = sanitize_hex_color( $attributes['iconColor'] ?? '' ) ?: 'var(--wp--preset--color--accent,#609fae)';
		$time_color     = sanitize_hex_color( $attributes['timeColor'] ?? '' ) ?: 'var(--wp--preset--color--accent,#609fae)';

		// Auto-detect context.
		if ( $auto_detect && $church_id === 0 && $cemetery_id === 0 ) {
			$context = self::detect_context();
			if ( $context['church_id'] > 0 ) {
				$church_id = $context['church_id'];
			}
			if ( $context['cemetery_id'] > 0 ) {
				$cemetery_id = $context['cemetery_id'];
			}
		}

		// Get month/year from URL params or use current.
		$current_month = isset( $_GET['cal_month'] ) ? absint( $_GET['cal_month'] ) : (int) current_time( 'n' );
		$current_year  = isset( $_GET['cal_year'] ) ? absint( $_GET['cal_year'] ) : (int) current_time( 'Y' );

		// Validate.
		if ( $current_month < 1 || $current_month > 12 ) {
			$current_month = (int) current_time( 'n' );
		}
		if ( $current_year < 2020 || $current_year > 2100 ) {
			$current_year = (int) current_time( 'Y' );
		}

		// Calculate first and last day of month.
		$first_day_of_month = gmdate( 'Y-m-d', mktime( 0, 0, 0, $current_month, 1, $current_year ) );
		$last_day_of_month  = gmdate( 'Y-m-d', mktime( 0, 0, 0, $current_month + 1, 0, $current_year ) );
		$days_in_month      = (int) gmdate( 't', mktime( 0, 0, 0, $current_month, 1, $current_year ) );
		$first_day_weekday  = (int) gmdate( 'w', mktime( 0, 0, 0, $current_month, 1, $current_year ) );

		// Query events for this month.
		$args = array(
			'post_type'      => 'parish_event',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'meta_query'     => array(
				array(
					'key'     => 'parish_event_date',
					'value'   => array( $first_day_of_month, $last_day_of_month ),
					'compare' => 'BETWEEN',
					'type'    => 'DATE',
				),
			),
			'orderby'        => array(
				'parish_event_date' => 'ASC',
				'parish_event_time' => 'ASC',
			),
			'meta_key'       => 'parish_event_date',
		);

		if ( ! empty( $sacrament ) ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'parish_sacrament',
					'field'    => 'slug',
					'terms'    => $sacrament,
				),
			);
		}

		if ( $church_id > 0 ) {
			$args['meta_query'][] = array(
				'key'     => 'parish_event_church_id',
				'value'   => $church_id,
				'compare' => '=',
				'type'    => 'NUMERIC',
			);
		}

		if ( $cemetery_id > 0 ) {
			$args['meta_query'][] = array(
				'key'     => 'parish_event_cemetery_id',
				'value'   => $cemetery_id,
				'compare' => '=',
				'type'    => 'NUMERIC',
			);
		}

		$query = new WP_Query( $args );

		// Group events by date.
		$events_by_date = array();
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post_id    = get_the_ID();
				$event_date = get_post_meta( $post_id, 'parish_event_date', true );
				$event_time = get_post_meta( $post_id, 'parish_event_time', true );

				if ( ! isset( $events_by_date[ $event_date ] ) ) {
					$events_by_date[ $event_date ] = array();
				}

				$events_by_date[ $event_date ][] = array(
					'title' => get_the_title(),
					'time'  => $event_time,
					'url'   => get_permalink(),
				);
			}
			wp_reset_postdata();
		}

		// Calculate previous/next month.
		$prev_month = $current_month - 1;
		$prev_year  = $current_year;
		if ( $prev_month < 1 ) {
			$prev_month = 12;
			$prev_year--;
		}

		$next_month = $current_month + 1;
		$next_year  = $current_year;
		if ( $next_month > 12 ) {
			$next_month = 1;
			$next_year++;
		}

		$today = current_time( 'Y-m-d' );

		// Build calendar HTML.
		$html = '<div class="parish-events-calendar">';

		// Calendar header with navigation.
		$html .= '<div class="parish-calendar-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1em;padding:0.5em 0;flex-wrap:wrap;gap:0.5em;">';

		$base_url = remove_query_arg( array( 'cal_month', 'cal_year' ) );
		$prev_url = add_query_arg( array( 'cal_month' => $prev_month, 'cal_year' => $prev_year ), $base_url );
		$next_url = add_query_arg( array( 'cal_month' => $next_month, 'cal_year' => $next_year ), $base_url );

		$html .= '<a href="' . esc_url( $prev_url ) . '" class="parish-calendar-nav" style="padding:0.5em 1em;background:#f0f0f0;border-radius:4px;text-decoration:none;color:inherit;display:flex;align-items:center;gap:0.25em;">';
		$html .= '<i class="fa-solid fa-chevron-left"></i> ' . esc_html__( 'Prev', 'parish-core' );
		$html .= '</a>';

		$html .= '<h3 class="parish-calendar-title" style="margin:0;font-size:1.25em;">';
		$html .= esc_html( date_i18n( 'F Y', mktime( 0, 0, 0, $current_month, 1, $current_year ) ) );
		$html .= '</h3>';

		$html .= '<a href="' . esc_url( $next_url ) . '" class="parish-calendar-nav" style="padding:0.5em 1em;background:#f0f0f0;border-radius:4px;text-decoration:none;color:inherit;display:flex;align-items:center;gap:0.25em;">';
		$html .= esc_html__( 'Next', 'parish-core' ) . ' <i class="fa-solid fa-chevron-right"></i>';
		$html .= '</a>';

		$html .= '</div>';

		// Calendar grid.
		$html .= '<div class="parish-calendar-grid" style="display:grid;grid-template-columns:repeat(7,1fr);gap:1px;background:#ddd;border:1px solid #ddd;border-radius:8px;overflow:hidden;">';

		// Day headers.
		$day_names = array(
			__( 'Sun', 'parish-core' ),
			__( 'Mon', 'parish-core' ),
			__( 'Tue', 'parish-core' ),
			__( 'Wed', 'parish-core' ),
			__( 'Thu', 'parish-core' ),
			__( 'Fri', 'parish-core' ),
			__( 'Sat', 'parish-core' ),
		);

		foreach ( $day_names as $day_name ) {
			$html .= '<div class="parish-calendar-day-header" style="background:#f6f7f7;padding:0.75em 0.5em;text-align:center;font-weight:600;font-size:0.85em;">';
			$html .= esc_html( $day_name );
			$html .= '</div>';
		}

		// Empty cells before first day.
		for ( $i = 0; $i < $first_day_weekday; $i++ ) {
			$html .= '<div class="parish-calendar-day parish-calendar-day-empty" style="background:#fafafa;min-height:100px;"></div>';
		}

		// Days of the month.
		for ( $day = 1; $day <= $days_in_month; $day++ ) {
			$date_str   = sprintf( '%04d-%02d-%02d', $current_year, $current_month, $day );
			$is_today   = ( $date_str === $today );
			$has_events = isset( $events_by_date[ $date_str ] );

			$day_style = 'background:#fff;min-height:100px;padding:0.5em;position:relative;';
			if ( $is_today ) {
				$day_style .= 'background:#f0f7ff;';
			}

			$html .= '<div class="parish-calendar-day' . ( $is_today ? ' parish-calendar-today' : '' ) . ( $has_events ? ' parish-calendar-has-events' : '' ) . '" style="' . esc_attr( $day_style ) . '">';

			// Day number.
			$day_number_style = 'display:inline-block;font-weight:600;font-size:0.9em;margin-bottom:0.25em;';
			if ( $is_today ) {
				$day_number_style .= 'background:' . esc_attr( $icon_color ) . ';color:#fff;width:1.75em;height:1.75em;line-height:1.75em;text-align:center;border-radius:50%;';
			}
			$html .= '<span class="parish-calendar-day-number" style="' . esc_attr( $day_number_style ) . '">' . esc_html( $day ) . '</span>';

			// Events for this day.
			if ( $has_events ) {
				$html .= '<div class="parish-calendar-day-events" style="font-size:0.75em;line-height:1.3;">';
				$event_count = 0;
				foreach ( $events_by_date[ $date_str ] as $event ) {
					if ( $event_count >= 3 ) {
						$remaining = count( $events_by_date[ $date_str ] ) - 3;
						$html     .= '<div class="parish-calendar-event-more" style="color:#666;font-style:italic;">+' . esc_html( $remaining ) . ' ' . esc_html__( 'more', 'parish-core' ) . '</div>';
						break;
					}
					$time_str = '';
					if ( ! empty( $event['time'] ) ) {
						$timestamp = strtotime( '2000-01-01 ' . $event['time'] );
						if ( $timestamp !== false ) {
							$time_str = gmdate( 'g:ia', $timestamp ) . ' ';
						}
					}
					$html .= '<a href="' . esc_url( $event['url'] ) . '" class="parish-calendar-event" style="display:block;padding:2px 4px;margin:2px 0;background:' . esc_attr( $icon_color ) . '22;border-left:2px solid ' . esc_attr( $icon_color ) . ';border-radius:2px;text-decoration:none;color:inherit;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="' . esc_attr( $event['title'] ) . '">';
					$html .= '<span style="color:' . esc_attr( $time_color ) . ';font-weight:500;">' . esc_html( $time_str ) . '</span>';
					$html .= esc_html( $event['title'] );
					$html .= '</a>';
					$event_count++;
				}
				$html .= '</div>';
			}

			$html .= '</div>';
		}

		// Empty cells after last day.
		$total_cells = $first_day_weekday + $days_in_month;
		$remaining   = $total_cells % 7;
		if ( $remaining > 0 ) {
			for ( $i = $remaining; $i < 7; $i++ ) {
				$html .= '<div class="parish-calendar-day parish-calendar-day-empty" style="background:#fafafa;min-height:100px;"></div>';
			}
		}

		$html .= '</div>'; // .parish-calendar-grid

		// Subscribe/Download buttons - positioned below the calendar.
		if ( $show_subscribe || $show_download ) {
			$download_url = self::get_ical_url( $sacrament, $church_id, $cemetery_id, true );
			$webcal_url   = self::get_webcal_url( $sacrament, $church_id, $cemetery_id );
			$gcal_url     = self::get_google_calendar_url( $sacrament, $church_id, $cemetery_id );

			$html .= '<div class="parish-calendar-actions" style="display:flex;flex-wrap:wrap;gap:0.5em;margin-top:1em;justify-content:center;">';

			if ( $show_subscribe ) {
				$html .= '<a href="' . esc_url( $webcal_url ) . '" class="parish-calendar-subscribe" style="display:inline-flex;align-items:center;gap:0.5em;padding:0.5em 1em;background:' . esc_attr( $icon_color ) . ';color:#fff;border-radius:4px;text-decoration:none;font-size:0.9em;">';
				$html .= '<i class="fa-solid fa-calendar-plus"></i> ' . esc_html__( 'Subscribe (iCal)', 'parish-core' );
				$html .= '</a>';

				$html .= '<a href="' . esc_url( $gcal_url ) . '" target="_blank" rel="noopener noreferrer" class="parish-calendar-google" style="display:inline-flex;align-items:center;gap:0.5em;padding:0.5em 1em;background:#4285f4;color:#fff;border-radius:4px;text-decoration:none;font-size:0.9em;">';
				$html .= '<i class="fa-brands fa-google"></i> ' . esc_html__( 'Add to Google', 'parish-core' );
				$html .= '</a>';
			}

			if ( $show_download ) {
				$html .= '<a href="' . esc_url( $download_url ) . '" class="parish-calendar-download" style="display:inline-flex;align-items:center;gap:0.5em;padding:0.5em 1em;background:#f0f0f0;color:inherit;border-radius:4px;text-decoration:none;font-size:0.9em;">';
				$html .= '<i class="fa-solid fa-download"></i> ' . esc_html__( 'Download .ics', 'parish-core' );
				$html .= '</a>';
			}

			$html .= '</div>';
		}

		$html .= '</div>'; // .parish-events-calendar

		// Get block wrapper attributes.
		$wrapper_attributes = get_block_wrapper_attributes(
			array(
				'class' => 'parish-events-calendar-block',
			)
		);

		return sprintf(
			'<div %s>%s</div>',
			$wrapper_attributes,
			$html
		);
	}
}

// Initialize iCal feed handling early.
add_action( 'init', array( 'Parish_Events_Calendar_Block', 'init_ical_feed' ), 5 );
