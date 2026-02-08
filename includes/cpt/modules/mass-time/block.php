<?php
/**
 * Mass Schedule Block
 *
 * Dynamic Gutenberg block that renders liturgical schedules for a church.
 * Outputs native Gutenberg blocks (columns, paragraphs) with Font Awesome icons.
 * Works inside Query Loops by automatically resolving the current post ID
 * from block context.
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mass Schedule Block class.
 */
class Parish_Mass_Schedule_Block {

	/**
	 * Register the block with WordPress.
	 *
	 * @return void
	 */
	public static function register(): void {
		register_block_type(
			'parish/mass-schedule',
			array(
				'api_version'     => 3,
				'editor_script'   => 'parish-core-editor-blocks',
				'render_callback' => array( __CLASS__, 'render' ),
				'uses_context'    => array( 'postType', 'postId' ),
				'attributes'      => array(
					'showIcon'       => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'showLivestream' => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'showSpecial'    => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'eventType'      => array(
						'type'    => 'string',
						'default' => '',
					),
					'iconColor'      => array(
						'type'    => 'string',
						'default' => '',
					),
					'timeColor'      => array(
						'type'    => 'string',
						'default' => '',
					),
					'showAllDays'    => array(
						'type'    => 'boolean',
						'default' => true,
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
	 * @param array    $attributes Block attributes.
	 * @param string   $content    Block content.
	 * @param WP_Block $block      Block instance with context.
	 * @return string Rendered HTML.
	 */
	public static function render( array $attributes, string $content, WP_Block $block ): string {
		// Feature check.
		if ( ! Parish_Core::is_feature_enabled( 'mass_times' ) ) {
			return '';
		}

		// Get post ID from block context (Query Loop) or current post.
		$post_id = $block->context['postId'] ?? get_the_ID();

		if ( ! $post_id ) {
			return self::render_placeholder( __( 'No post context available.', 'parish-core' ) );
		}

		// Validate post type - only render on church posts.
		$post_type = get_post_type( $post_id );
		if ( 'parish_church' !== $post_type ) {
			return self::render_placeholder( __( 'This block only works on Church posts.', 'parish-core' ) );
		}

		$church_id       = (int) $post_id;
		$filter_type     = sanitize_text_field( $attributes['eventType'] ?? '' );
		$show_icon       = (bool) ( $attributes['showIcon'] ?? true );
		$show_livestream = (bool) ( $attributes['showLivestream'] ?? true );
		$show_special    = (bool) ( $attributes['showSpecial'] ?? true );
		$show_all_days   = (bool) ( $attributes['showAllDays'] ?? true );
		$icon_color      = sanitize_hex_color( $attributes['iconColor'] ?? '' ) ?: 'var(--wp--preset--color--accent,#609fae)';
		$time_color      = sanitize_hex_color( $attributes['timeColor'] ?? '' ) ?: 'var(--wp--preset--color--accent,#609fae)';

		// Get all active Mass Time posts for this church.
		$meta_query = array(
			'relation' => 'AND',
			array(
				'key'     => 'parish_mass_time_is_active',
				'value'   => '1',
				'compare' => '=',
			),
			array(
				'relation' => 'OR',
				array(
					'key'     => 'parish_mass_time_church_id',
					'value'   => $church_id,
					'compare' => '=',
				),
				array(
					'key'     => 'parish_mass_time_church_id',
					'value'   => '0',
					'compare' => '=',
				),
			),
		);

		// Filter by type if specified.
		if ( ! empty( $filter_type ) ) {
			$meta_query[] = array(
				'key'     => 'parish_mass_time_liturgical_type',
				'value'   => $filter_type,
				'compare' => '=',
			);
		}

		$mass_time_posts = get_posts( array(
			'post_type'      => 'parish_mass_time',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'meta_query'     => $meta_query,
		) );

		// Day order for weekly schedule (Saturday first for weekend visibility).
		$day_order = array( 'Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday' );

		// Categorize posts into regular weekly and special events.
		$weekly_data    = array();
		$special_events = array();

		foreach ( $mass_time_posts as $mt_post ) {
			$is_recurring     = get_post_meta( $mt_post->ID, 'parish_mass_time_is_recurring', true );
			$is_special       = get_post_meta( $mt_post->ID, 'parish_mass_time_is_special_event', true );
			$recurrence       = get_post_meta( $mt_post->ID, 'parish_mass_time_recurrence', true );
			$start_datetime   = get_post_meta( $mt_post->ID, 'parish_mass_time_start_datetime', true );
			$notes            = get_post_meta( $mt_post->ID, 'parish_mass_time_notes', true );
			$side_note        = get_post_meta( $mt_post->ID, 'parish_mass_time_side_note', true );
			$is_livestreamed  = get_post_meta( $mt_post->ID, 'parish_mass_time_is_livestreamed', true );

			// Parse time from datetime (handles ISO 8601 format).
			$time = self::format_time( $start_datetime );

			$event_data = array(
				'id'              => $mt_post->ID,
				'title'           => $mt_post->post_title,
				'time'            => $time,
				'notes'           => $notes,
				'side_note'       => $side_note,
				'is_livestreamed' => $is_livestreamed,
			);

			// Determine if this is a special event or regular weekly schedule.
			if ( $is_special ) {
				$special_events[] = $event_data;
			} elseif ( $is_recurring && is_array( $recurrence ) ) {
				$rec_type = $recurrence['type'] ?? 'weekly';
				$rec_days = $recurrence['days'] ?? array();

				if ( ( $rec_type === 'weekly' || $rec_type === 'biweekly' ) && ! empty( $rec_days ) ) {
					foreach ( $rec_days as $day ) {
						if ( ! isset( $weekly_data[ $day ] ) ) {
							$weekly_data[ $day ] = array();
						}
						$weekly_data[ $day ][] = $event_data;
					}
				} elseif ( $rec_type === 'daily' ) {
					foreach ( $day_order as $day ) {
						if ( ! isset( $weekly_data[ $day ] ) ) {
							$weekly_data[ $day ] = array();
						}
						$weekly_data[ $day ][] = $event_data;
					}
				} elseif ( $rec_type === 'monthly_ordinal' ) {
					$ordinal                = $recurrence['ordinal'] ?? 'first';
					$ordinal_day            = $recurrence['ordinal_day'] ?? '';
					$event_data['title']    = ucfirst( $ordinal ) . ' ' . $ordinal_day;
					$special_events[]       = $event_data;
				} else {
					$special_events[] = $event_data;
				}
			} else {
				// Fallback: try to extract day of week from start_datetime.
				// This handles cases where recurrence data is missing but we have a datetime.
				$day_from_datetime = self::get_day_from_datetime( $start_datetime );
				if ( $day_from_datetime && in_array( $day_from_datetime, $day_order, true ) ) {
					if ( ! isset( $weekly_data[ $day_from_datetime ] ) ) {
						$weekly_data[ $day_from_datetime ] = array();
					}
					$weekly_data[ $day_from_datetime ][] = $event_data;
				} elseif ( ! empty( $event_data['title'] ) ) {
					// Only add to special events if it has a title (otherwise skip it).
					$special_events[] = $event_data;
				}
			}
		}

		// Sort events within each day by time.
		foreach ( $weekly_data as $day => $events ) {
			usort( $weekly_data[ $day ], function ( $a, $b ) {
				return strcmp( $a['time'], $b['time'] );
			} );
		}

		// Build HTML output directly (more reliable than do_blocks for dynamic content).
		$html = '<div class="parish-mass-schedule-rows">';

		// Weekly schedule section - show all days if enabled.
		foreach ( $day_order as $day ) {
			$events = $weekly_data[ $day ] ?? array();

			if ( empty( $events ) && ! $show_all_days ) {
				continue;
			}

			$html .= self::render_schedule_row_html( $day, $events, $show_icon, $show_livestream, $icon_color, $time_color );
		}

		// Special events section.
		if ( $show_special && ! empty( $special_events ) ) {
			$html .= '<div class="parish-mass-schedule-divider"></div>';
			foreach ( $special_events as $event ) {
				$html .= self::render_schedule_row_html(
					$event['title'],
					array( $event ),
					$show_icon,
					$show_livestream,
					$icon_color,
					$time_color
				);
			}
		}

		$html .= '</div>';

		// Get block wrapper attributes.
		$wrapper_attributes = get_block_wrapper_attributes(
			array(
				'class' => 'parish-mass-schedule-block',
			)
		);

		return sprintf(
			'<div %s>%s</div>',
			$wrapper_attributes,
			$html
		);
	}

	/**
	 * Render a single schedule row as HTML.
	 *
	 * @param string $label          Day name or event title.
	 * @param array  $events         Events for this row.
	 * @param bool   $show_icon      Whether to show the clock icon.
	 * @param bool   $show_livestream Whether to show livestream indicators.
	 * @param string $icon_color     Color for icons.
	 * @param string $time_color     Color for times.
	 * @return string HTML output.
	 */
	private static function render_schedule_row_html( string $label, array $events, bool $show_icon, bool $show_livestream, string $icon_color, string $time_color ): string {
		// Use inline styles to force the layout.
		$row_style   = 'display:flex;flex-direction:row;align-items:center;gap:0.75em;padding:0.35em 0;border-bottom:1px solid rgba(0,0,0,0.05);';
		$icon_style  = 'flex:0 0 auto;width:1.2em;display:inline-flex;align-items:center;justify-content:center;';
		$day_style   = 'flex:0 0 120px;font-weight:500;';
		$times_style = 'flex:1 1 auto;display:inline-flex;flex-wrap:wrap;align-items:center;gap:0.25em;';

		$html = sprintf( '<div class="parish-mass-schedule-row" style="%s">', $row_style );

		// Clock icon (optional).
		if ( $show_icon ) {
			$html .= sprintf(
				'<div class="parish-mass-schedule-icon" style="%s"><i class="fa-regular fa-clock" style="color:%s;font-size:1em;"></i></div>',
				$icon_style,
				esc_attr( $icon_color )
			);
		}

		// Day name.
		$html .= sprintf(
			'<div class="parish-mass-schedule-day" style="%s">%s</div>',
			$day_style,
			esc_html( $label )
		);

		// Times.
		$html .= sprintf( '<div class="parish-mass-schedule-times" style="%s">', $times_style );

		if ( empty( $events ) ) {
			$html .= '<span class="parish-mass-schedule-no-mass" style="font-style:italic;opacity:0.5;">' . esc_html__( 'No Mass', 'parish-core' ) . '</span>';
		} else {
			$times_parts = array();

			foreach ( $events as $event ) {
				$time_html = sprintf(
					'<span class="parish-mass-schedule-time" style="color:%s;font-weight:600;">%s</span>',
					esc_attr( $time_color ),
					esc_html( $event['time'] )
				);

				if ( ! empty( $event['notes'] ) ) {
					$time_html .= ' <span class="parish-mass-schedule-note" style="font-size:0.9em;opacity:0.7;">(' . esc_html( wp_strip_all_tags( $event['notes'] ) ) . ')</span>';
				}

				// Add livestream icon right after this time if applicable.
				if ( $show_livestream && ! empty( $event['is_livestreamed'] ) ) {
					$time_html .= sprintf(
						' <i class="fa-solid fa-video" style="color:%s;font-size:0.9em;" aria-label="%s"></i>',
						esc_attr( $icon_color ),
						esc_attr__( 'Livestreamed', 'parish-core' )
					);
				}

				$times_parts[] = $time_html;
			}

			$html .= implode( '<span class="parish-mass-schedule-sep" style="opacity:0.5;">, </span>', $times_parts );
		}

		$html .= '</div>';

		// Side note (from first event that has one).
		$side_note = '';
		foreach ( $events as $event ) {
			if ( ! empty( $event['side_note'] ) ) {
				$side_note = $event['side_note'];
				break;
			}
		}
		if ( $side_note ) {
			$html .= '<div class="parish-mass-schedule-sidenote" style="flex:0 0 auto;font-size:0.85em;font-style:italic;opacity:0.6;margin-left:auto;text-align:right;">' . esc_html( $side_note ) . '</div>';
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Format time from various input formats.
	 *
	 * @param string $datetime DateTime or time string.
	 * @return string Formatted time (e.g., "10:00 AM").
	 */
	private static function format_time( string $datetime ): string {
		if ( empty( $datetime ) ) {
			return '';
		}

		// Handle ISO 8601 format (2026-01-11T10:00).
		if ( strpos( $datetime, 'T' ) !== false ) {
			$parts = explode( 'T', $datetime );
			$time  = $parts[1] ?? '';
			if ( strlen( $time ) > 5 ) {
				$time = substr( $time, 0, 5 );
			}
		} elseif ( strpos( $datetime, ' ' ) !== false ) {
			$parts = explode( ' ', $datetime );
			$time  = $parts[1] ?? '';
			if ( strlen( $time ) > 5 ) {
				$time = substr( $time, 0, 5 );
			}
		} else {
			$time = $datetime;
		}

		$timestamp = strtotime( "2000-01-01 $time" );
		if ( $timestamp === false ) {
			return $time;
		}

		return gmdate( 'g:i A', $timestamp );
	}

	/**
	 * Extract day of week from a datetime string.
	 *
	 * @param string $datetime DateTime string (ISO 8601 or other format).
	 * @return string|null Day name (e.g., "Monday") or null if parsing fails.
	 */
	private static function get_day_from_datetime( string $datetime ): ?string {
		if ( empty( $datetime ) ) {
			return null;
		}

		// Try to parse the datetime.
		$timestamp = strtotime( $datetime );
		if ( $timestamp === false ) {
			return null;
		}

		// Return the full day name (e.g., "Monday", "Tuesday").
		return gmdate( 'l', $timestamp );
	}

	/**
	 * Render placeholder message for editor preview.
	 *
	 * @param string $message Placeholder message.
	 * @return string HTML output.
	 */
	private static function render_placeholder( string $message ): string {
		if ( ! ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return '';
		}

		$wrapper_attributes = get_block_wrapper_attributes(
			array( 'class' => 'parish-mass-schedule-placeholder' )
		);

		return sprintf(
			'<div %s><p>%s</p></div>',
			$wrapper_attributes,
			esc_html( $message )
		);
	}
}
