<?php
/**
 * Events Calendar Block
 *
 * Full month calendar view.
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
					'includeFeastDays' => array(
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

		$sacrament         = sanitize_text_field( $attributes['sacrament'] ?? '' );
		$church_id         = absint( $attributes['churchId'] ?? 0 );
		$cemetery_id       = absint( $attributes['cemeteryId'] ?? 0 );
		$auto_detect       = (bool) ( $attributes['autoDetect'] ?? true );
		$include_feast_days = (bool) ( $attributes['includeFeastDays'] ?? true );
		$icon_color        = sanitize_hex_color( $attributes['iconColor'] ?? '' ) ?: 'var(--wp--preset--color--accent,#609fae)';
		$time_color        = sanitize_hex_color( $attributes['timeColor'] ?? '' ) ?: 'var(--wp--preset--color--accent,#609fae)';

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
		// Priority sorting ensures manual events (priority 0) appear before feast days (priority 10).
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

		// Exclude feast day events if option is disabled.
		if ( ! $include_feast_days ) {
			$args['meta_query'][] = array(
				'relation' => 'OR',
				array(
					'key'     => 'parish_event_is_feast_day',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'   => 'parish_event_is_feast_day',
					'value' => '0',
				),
				array(
					'key'   => 'parish_event_is_feast_day',
					'value' => '',
				),
			);
		}

		$query = new WP_Query( $args );

		// Group events by date.
		$events_by_date = array();
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post_id        = get_the_ID();
				$event_date     = get_post_meta( $post_id, 'parish_event_date', true );
				$event_time     = get_post_meta( $post_id, 'parish_event_time', true );
				$event_priority = (int) get_post_meta( $post_id, 'parish_event_priority', true );

				if ( ! isset( $events_by_date[ $event_date ] ) ) {
					$events_by_date[ $event_date ] = array();
				}

				$events_by_date[ $event_date ][] = array(
					'title'    => get_the_title(),
					'time'     => $event_time,
					'url'      => get_permalink(),
					'priority' => $event_priority,
				);
			}
			wp_reset_postdata();
		}

		// Sort events within each date by priority (lower = higher), then by time.
		foreach ( $events_by_date as $date => &$events ) {
			usort( $events, function ( $a, $b ) {
				// Sort by priority first (manual events = 0, feast days = 10).
				if ( $a['priority'] !== $b['priority'] ) {
					return $a['priority'] <=> $b['priority'];
				}
				// Then by time.
				return strcmp( $a['time'], $b['time'] );
			} );
		}
		unset( $events ); // Break reference.

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

		// Build calendar HTML - wrapper allows horizontal scroll on small screens.
		$html = '<div class="parish-events-calendar" style="width:100%;overflow-x:auto;">';

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

		// Calendar grid - ensure minimum column width so all 7 days are always visible.
		$html .= '<div class="parish-calendar-grid" style="display:grid;grid-template-columns:repeat(7,minmax(90px,1fr));gap:1px;background:#ddd;border:1px solid #ddd;border-radius:8px;">';

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
