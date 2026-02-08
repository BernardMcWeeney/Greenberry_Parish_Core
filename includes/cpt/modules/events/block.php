<?php
/**
 * Events Block
 *
 * Dynamic Gutenberg block that displays parish events with filtering options.
 * Supports auto-detection of church/cemetery context for filtering.
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
					'view'        => array(
						'type'    => 'string',
						'default' => 'upcoming',
					),
					'limit'       => array(
						'type'    => 'integer',
						'default' => 5,
					),
					'sacrament'   => array(
						'type'    => 'string',
						'default' => '',
					),
					'churchId'    => array(
						'type'    => 'integer',
						'default' => 0,
					),
					'cemeteryId'  => array(
						'type'    => 'integer',
						'default' => 0,
					),
					'autoDetect'  => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'showIcon'    => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'iconColor'   => array(
						'type'    => 'string',
						'default' => '',
					),
					'timeColor'   => array(
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
			'post_type'   => '',
		);

		// Check if we're in a Query Loop or single post context.
		$post_id = get_the_ID();
		if ( ! $post_id ) {
			global $post;
			$post_id = $post ? $post->ID : 0;
		}

		if ( ! $post_id ) {
			return $context;
		}

		$post_type = get_post_type( $post_id );
		$context['post_type'] = $post_type;

		// Direct match - we're on a church or cemetery page.
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

		$view        = sanitize_text_field( $attributes['view'] ?? 'upcoming' );
		$limit       = (int) ( $attributes['limit'] ?? 5 );
		$sacrament   = sanitize_text_field( $attributes['sacrament'] ?? '' );
		$church_id   = absint( $attributes['churchId'] ?? 0 );
		$cemetery_id = absint( $attributes['cemeteryId'] ?? 0 );
		$auto_detect = (bool) ( $attributes['autoDetect'] ?? true );
		$show_icon   = (bool) ( $attributes['showIcon'] ?? true );
		$icon_color  = sanitize_hex_color( $attributes['iconColor'] ?? '' ) ?: 'var(--wp--preset--color--accent,#609fae)';
		$time_color  = sanitize_hex_color( $attributes['timeColor'] ?? '' ) ?: 'var(--wp--preset--color--accent,#609fae)';

		// Auto-detect context if enabled and no explicit filter set.
		if ( $auto_detect && $church_id === 0 && $cemetery_id === 0 ) {
			$context = self::detect_context();
			if ( $context['church_id'] > 0 ) {
				$church_id = $context['church_id'];
			}
			if ( $context['cemetery_id'] > 0 ) {
				$cemetery_id = $context['cemetery_id'];
			}
		}

		$today = current_time( 'Y-m-d' );

		// Build query for parish_event CPT.
		$args = array(
			'post_type'      => 'parish_event',
			'posts_per_page' => $limit,
			'post_status'    => 'publish',
			'meta_query'     => array(
				'relation' => 'AND',
			),
			'orderby'        => array(
				'parish_event_date' => 'ASC',
				'parish_event_time' => 'ASC',
			),
			'meta_key'       => 'parish_event_date',
		);

		// Filter by view.
		switch ( $view ) {
			case 'today':
				$args['meta_query'][] = array(
					'key'     => 'parish_event_date',
					'value'   => $today,
					'compare' => '=',
					'type'    => 'DATE',
				);
				break;

			case 'week':
				$week_end = gmdate( 'Y-m-d', strtotime( '+7 days' ) );
				$args['meta_query'][] = array(
					'key'     => 'parish_event_date',
					'value'   => array( $today, $week_end ),
					'compare' => 'BETWEEN',
					'type'    => 'DATE',
				);
				break;

			case 'upcoming':
			default:
				// All upcoming events from today onwards.
				$args['meta_query'][] = array(
					'key'     => 'parish_event_date',
					'value'   => $today,
					'compare' => '>=',
					'type'    => 'DATE',
				);
				break;
		}

		// Filter by sacrament taxonomy if specified.
		if ( ! empty( $sacrament ) ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'parish_sacrament',
					'field'    => 'slug',
					'terms'    => $sacrament,
				),
			);
		}

		// Filter by church.
		if ( $church_id > 0 ) {
			$args['meta_query'][] = array(
				'key'     => 'parish_event_church_id',
				'value'   => $church_id,
				'compare' => '=',
				'type'    => 'NUMERIC',
			);
		}

		// Filter by cemetery.
		if ( $cemetery_id > 0 ) {
			$args['meta_query'][] = array(
				'key'     => 'parish_event_cemetery_id',
				'value'   => $cemetery_id,
				'compare' => '=',
				'type'    => 'NUMERIC',
			);
		}

		$query  = new WP_Query( $args );
		$events = array();

		// Convert posts to event array format for rendering.
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post_id        = get_the_ID();
				$event_date     = get_post_meta( $post_id, 'parish_event_date', true );
				$event_time     = get_post_meta( $post_id, 'parish_event_time', true );
				$event_location = get_post_meta( $post_id, 'parish_event_location', true );
				$event_church   = absint( get_post_meta( $post_id, 'parish_event_church_id', true ) );

				// Get church name as fallback location.
				$location = $event_location;
				if ( empty( $location ) && $event_church > 0 ) {
					$church = get_post( $event_church );
					if ( $church ) {
						$location = $church->post_title;
					}
				}

				$events[] = array(
					'title'    => get_the_title(),
					'date'     => $event_date,
					'time'     => $event_time,
					'location' => $location,
					'url'      => get_permalink(),
				);
			}
			wp_reset_postdata();
		}

		// Build HTML output.
		$html = '<div class="parish-events-rows">';

		if ( empty( $events ) ) {
			$html .= '<div class="parish-events-empty" style="padding:0.75em 0;font-style:italic;opacity:0.6;">';
			switch ( $view ) {
				case 'today':
					$html .= esc_html__( 'No events scheduled for today.', 'parish-core' );
					break;
				case 'week':
					$html .= esc_html__( 'No events scheduled this week.', 'parish-core' );
					break;
				default:
					$html .= esc_html__( 'No upcoming events.', 'parish-core' );
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
	 * @param string $view       View type.
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
		$url      = $event['url'] ?? '';

		$today = current_time( 'Y-m-d' );

		// Format date.
		$date_display = '';
		if ( $date ) {
			if ( $view === 'today' && $date === $today ) {
				$date_display = __( 'Today', 'parish-core' );
			} else {
				$date_display = date_i18n( 'l, M j', strtotime( $date ) );
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

		// Make title clickable if URL exists.
		if ( ! empty( $url ) ) {
			$html .= '<a href="' . esc_url( $url ) . '" class="parish-events-title" style="font-weight:500;text-decoration:none;color:inherit;">' . esc_html( $title ) . '</a>';
		} else {
			$html .= '<span class="parish-events-title" style="font-weight:500;">' . esc_html( $title ) . '</span>';
		}
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
