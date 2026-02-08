<?php
/**
 * Events Block
 *
 * Dynamic Gutenberg block that displays today's or this week's events.
 * Uses consistent styling with other parish blocks.
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Events Block class.
 */
class Parish_Events_Block {

	/**
	 * Register the block with WordPress.
	 *
	 * @return void
	 */
	public static function register(): void {
		register_block_type(
			'parish/events',
			array(
				'api_version'     => 3,
				'editor_script'   => 'parish-core-editor-blocks',
				'render_callback' => array( __CLASS__, 'render' ),
				'attributes'      => array(
					'view'       => array(
						'type'    => 'string',
						'default' => 'today',
					),
					'limit'      => array(
						'type'    => 'integer',
						'default' => 5,
					),
					'eventType'  => array(
						'type'    => 'string',
						'default' => '',
					),
					'showIcon'   => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'iconColor'  => array(
						'type'    => 'string',
						'default' => '',
					),
					'timeColor'  => array(
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

		$view       = sanitize_text_field( $attributes['view'] ?? 'today' );
		$limit      = (int) ( $attributes['limit'] ?? 5 );
		$event_type = sanitize_text_field( $attributes['eventType'] ?? '' );
		$show_icon  = (bool) ( $attributes['showIcon'] ?? true );
		$icon_color = sanitize_hex_color( $attributes['iconColor'] ?? '' ) ?: 'var(--wp--preset--color--accent,#609fae)';
		$time_color = sanitize_hex_color( $attributes['timeColor'] ?? '' ) ?: 'var(--wp--preset--color--accent,#609fae)';

		// Get events from settings.
		$events = json_decode( Parish_Core::get_setting( 'parish_events', '[]' ), true ) ?: array();
		$today  = current_time( 'Y-m-d' );

		// Filter by type if specified.
		if ( ! empty( $event_type ) ) {
			$events = array_filter( $events, function( $event ) use ( $event_type ) {
				return ( $event['event_type'] ?? '' ) === $event_type;
			});
		}

		// Filter by view (today or week).
		if ( $view === 'today' ) {
			$events = array_filter( $events, function( $event ) use ( $today ) {
				return ( $event['date'] ?? '' ) === $today;
			});
		} else {
			// This week - get events from today to 7 days from now.
			$week_end = gmdate( 'Y-m-d', strtotime( '+7 days' ) );
			$events = array_filter( $events, function( $event ) use ( $today, $week_end ) {
				$date = $event['date'] ?? '';
				return $date >= $today && $date <= $week_end;
			});
		}

		// Sort by date.
		usort( $events, function( $a, $b ) {
			$cmp = strcmp( $a['date'] ?? '', $b['date'] ?? '' );
			if ( $cmp === 0 ) {
				return strcmp( $a['time'] ?? '', $b['time'] ?? '' );
			}
			return $cmp;
		});

		// Limit results.
		$events = array_slice( $events, 0, $limit );

		// Build HTML output.
		$html = '<div class="parish-events-rows">';

		if ( empty( $events ) ) {
			$html .= '<div class="parish-events-empty" style="padding:0.75em 0;font-style:italic;opacity:0.6;">';
			if ( $view === 'today' ) {
				$html .= esc_html__( 'No events scheduled for today.', 'parish-core' );
			} else {
				$html .= esc_html__( 'No events scheduled this week.', 'parish-core' );
			}
			$html .= '</div>';
		} else {
			foreach ( $events as $event ) {
				$html .= self::render_event_row( $event, $view, $show_icon, $icon_color, $time_color );
			}
		}

		$html .= '</div>';

		// Get block wrapper attributes.
		$wrapper_attributes = get_block_wrapper_attributes(
			array(
				'class' => 'parish-events-block',
			)
		);

		return sprintf(
			'<div %s>%s</div>',
			$wrapper_attributes,
			$html
		);
	}

	/**
	 * Render a single event row.
	 *
	 * @param array  $event      Event data.
	 * @param string $view       View type (today or week).
	 * @param bool   $show_icon  Whether to show the calendar icon.
	 * @param string $icon_color Color for icons.
	 * @param string $time_color Color for time/date.
	 * @return string HTML output.
	 */
	private static function render_event_row( array $event, string $view, bool $show_icon, string $icon_color, string $time_color ): string {
		$title    = $event['title'] ?? '';
		$date     = $event['date'] ?? '';
		$time     = $event['time'] ?? '';
		$location = $event['location'] ?? '';

		// Format date.
		$date_display = '';
		if ( $date ) {
			if ( $view === 'week' ) {
				// For week view, show day name and date.
				$date_display = date_i18n( 'l, M j', strtotime( $date ) );
			} else {
				// For today view, just show "Today".
				$date_display = __( 'Today', 'parish-core' );
			}
		}

		// Format time.
		$time_display = '';
		if ( $time ) {
			$timestamp = strtotime( "2000-01-01 $time" );
			if ( $timestamp !== false ) {
				$time_display = gmdate( 'g:i A', $timestamp );
			} else {
				$time_display = $time;
			}
		}

		$html = '<div class="parish-events-row" style="padding:0.75em 0;border-bottom:1px solid rgba(0,0,0,0.08);">';

		// Row 1: Title + optional calendar icon.
		$html .= '<div class="parish-events-header" style="display:flex;flex-direction:row;align-items:center;gap:0.5em;margin-bottom:0.25em;">';

		if ( $show_icon ) {
			$html .= sprintf(
				'<i class="fa-regular fa-calendar" style="color:%s;font-size:0.9em;"></i>',
				esc_attr( $icon_color )
			);
		}

		$html .= '<span class="parish-events-title" style="font-weight:500;">' . esc_html( $title ) . '</span>';
		$html .= '</div>';

		// Row 2: Date/time and location.
		$html .= '<div class="parish-events-details" style="display:flex;flex-direction:row;flex-wrap:wrap;gap:0.5em 1.5em;font-size:0.9em;">';

		// Date and time.
		$datetime_parts = array();
		if ( $date_display ) {
			$datetime_parts[] = $date_display;
		}
		if ( $time_display ) {
			$datetime_parts[] = $time_display;
		}
		if ( ! empty( $datetime_parts ) ) {
			$html .= sprintf(
				'<span class="parish-events-datetime" style="color:%s;font-weight:600;">%s</span>',
				esc_attr( $time_color ),
				esc_html( implode( ' • ', $datetime_parts ) )
			);
		}

		// Location.
		if ( $location ) {
			$html .= '<span class="parish-events-location" style="opacity:0.7;"><i class="fa-solid fa-location-dot" style="margin-right:0.25em;"></i>' . esc_html( $location ) . '</span>';
		}

		$html .= '</div>';

		$html .= '</div>'; // .parish-events-row

		return $html;
	}
}
