<?php
/**
 * Shortcodes.
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Parish_Shortcodes class.
 */
class Parish_Shortcodes {

	private static ?Parish_Shortcodes $instance = null;

	public static function instance(): Parish_Shortcodes {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'register_shortcodes' ) );
	}

	/**
	 * Register shortcodes.
	 */
	public function register_shortcodes(): void {
		add_shortcode( 'parish_reflection', array( $this, 'reflection_shortcode' ) );
		add_shortcode( 'parish_events', array( $this, 'events_shortcode' ) );
		add_shortcode( 'parish_churches', array( $this, 'churches_shortcode' ) );
		add_shortcode( 'parish_clergy', array( $this, 'clergy_shortcode' ) );
		add_shortcode( 'parish_contact', array( $this, 'contact_shortcode' ) );
		add_shortcode( 'parish_prayers', array( $this, 'prayers_shortcode' ) );

		// Mass Times shortcodes.
		add_shortcode( 'parish_today_widget', array( $this, 'today_widget_shortcode' ) );
		add_shortcode( 'parish_church_schedule', array( $this, 'church_schedule_shortcode' ) );
		add_shortcode( 'parish_schedule', array( $this, 'schedule_shortcode' ) );
		add_shortcode( 'parish_weekly_schedule', array( $this, 'weekly_schedule_shortcode' ) );
		add_shortcode( 'parish_today_schedule', array( $this, 'today_schedule_shortcode' ) );
	}

	/**
	 * Latest reflection shortcode.
	 */
	public function reflection_shortcode( $atts ): string {
		if ( ! Parish_Core::is_feature_enabled( 'reflections' ) ) {
			return '';
		}

		$reflections = get_posts( array(
			'post_type'      => 'parish_reflection',
			'posts_per_page' => 1,
			'post_status'    => 'publish',
		));

		if ( empty( $reflections ) ) {
			return '';
		}

		$reflection = $reflections[0];

		return sprintf(
			'<div class="parish-reflection">
				<blockquote>%s</blockquote>
				<cite>— %s</cite>
			</div>',
			wp_kses_post( $reflection->post_content ),
			esc_html( $reflection->post_title )
		);
	}

	/**
	 * Events shortcode.
	 * 
	 * Attributes:
	 * - limit: Number of events to show (default 10)
	 * - type: Filter by event type (parish, sacrament, feast)
	 * - month: Filter by month number (1-12)
	 * - year: Filter by year
	 * - past: "yes" to show past events instead of upcoming
	 */
	public function events_shortcode( $atts ): string {
		if ( ! Parish_Core::is_feature_enabled( 'events' ) ) {
			return '';
		}

		$atts = shortcode_atts( array(
			'limit' => 10,
			'type'  => '',
			'month' => '',
			'year'  => '',
			'past'  => 'no',
		), $atts );

		$events = json_decode( Parish_Core::get_setting( 'parish_events', '[]' ), true ) ?: array();
		$today  = current_time( 'Y-m-d' );

		// Filter by type.
		if ( ! empty( $atts['type'] ) ) {
			$events = array_filter( $events, function( $event ) use ( $atts ) {
				return ( $event['event_type'] ?? '' ) === $atts['type'];
			});
		}

		// Filter by month/year.
		if ( ! empty( $atts['month'] ) || ! empty( $atts['year'] ) ) {
			$filter_month = $atts['month'] ? str_pad( $atts['month'], 2, '0', STR_PAD_LEFT ) : '';
			$filter_year = $atts['year'] ?: '';
			
			$events = array_filter( $events, function( $event ) use ( $filter_month, $filter_year ) {
				$date = $event['date'] ?? '';
				if ( empty( $date ) ) return false;
				
				$parts = explode( '-', $date );
				if ( count( $parts ) < 3 ) return false;
				
				if ( $filter_year && $parts[0] !== $filter_year ) return false;
				if ( $filter_month && $parts[1] !== $filter_month ) return false;
				
				return true;
			});
		} else {
			// Default: filter by past/upcoming.
			if ( $atts['past'] === 'yes' ) {
				$events = array_filter( $events, function( $event ) use ( $today ) {
					return ( $event['date'] ?? '' ) < $today;
				});
			} else {
				$events = array_filter( $events, function( $event ) use ( $today ) {
					return ( $event['date'] ?? '' ) >= $today;
				});
			}
		}

		// Sort by date.
		usort( $events, function( $a, $b ) use ( $atts ) {
			$cmp = strcmp( $a['date'] ?? '', $b['date'] ?? '' );
			return $atts['past'] === 'yes' ? -$cmp : $cmp; // Reverse for past events.
		});

		$events = array_slice( $events, 0, (int) $atts['limit'] );

		if ( empty( $events ) ) {
			return '<p>' . esc_html__( 'No events found.', 'parish-core' ) . '</p>';
		}

		$html = '<div class="parish-events"><ul>';

		foreach ( $events as $event ) {
			$date = date_i18n( 'l, F j, Y', strtotime( $event['date'] ) );

			$html .= '<li>';
			$html .= '<strong>' . esc_html( $event['title'] ?? '' ) . '</strong><br>';
			$html .= '<span class="event-date">' . esc_html( $date ) . '</span>';

			if ( ! empty( $event['time'] ) ) {
				$html .= ' at ' . esc_html( $event['time'] );
			}

			if ( ! empty( $event['location'] ) ) {
				$html .= '<br><span class="event-location">' . esc_html( $event['location'] ) . '</span>';
			}

			$html .= '</li>';
		}

		$html .= '</ul></div>';

		return $html;
	}

	/**
	 * Prayers shortcode.
	 * 
	 * Attributes:
	 * - limit: Number of prayers to show (default -1 for all)
	 * - orderby: Order by field (title, date, rand)
	 */
	public function prayers_shortcode( $atts ): string {
		if ( ! Parish_Core::is_feature_enabled( 'prayers' ) ) {
			return '';
		}

		$atts = shortcode_atts( array(
			'limit'   => -1,
			'orderby' => 'title',
		), $atts );

		$prayers = get_posts( array(
			'post_type'      => 'parish_prayer',
			'posts_per_page' => (int) $atts['limit'],
			'post_status'    => 'publish',
			'orderby'        => $atts['orderby'],
			'order'          => 'ASC',
		));

		if ( empty( $prayers ) ) {
			return '<p>' . esc_html__( 'No prayers available.', 'parish-core' ) . '</p>';
		}

		$html = '<div class="parish-prayers">';

		foreach ( $prayers as $prayer ) {
			$html .= '<div class="prayer-item">';
			$html .= '<h4>' . esc_html( $prayer->post_title ) . '</h4>';
			$html .= '<div class="prayer-text">' . wp_kses_post( $prayer->post_content ) . '</div>';
			$html .= '</div>';
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Churches shortcode.
	 */
	public function churches_shortcode( $atts ): string {
		if ( ! Parish_Core::is_feature_enabled( 'churches' ) ) {
			return '';
		}

		$churches = get_posts( array(
			'post_type'      => 'parish_church',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'orderby'        => 'title',
			'order'          => 'ASC',
		));

		if ( empty( $churches ) ) {
			return '';
		}

		$html = '<div class="parish-churches">';

		foreach ( $churches as $church ) {
			$address = get_post_meta( $church->ID, 'parish_address', true );

			$html .= '<div class="church-item">';
			$html .= '<h4>' . esc_html( $church->post_title ) . '</h4>';

			if ( $address ) {
				$html .= '<p>' . esc_html( $address ) . '</p>';
			}

			$html .= '</div>';
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Clergy shortcode.
	 */
	public function clergy_shortcode( $atts ): string {
		$clergy = json_decode( Parish_Core::get_setting( 'parish_clergy', '[]' ), true ) ?: array();

		if ( empty( $clergy ) ) {
			return '';
		}

		$html = '<div class="parish-clergy"><ul>';

		foreach ( $clergy as $person ) {
			$html .= '<li>';
			$html .= '<strong>' . esc_html( $person['name'] ?? '' ) . '</strong>';

			if ( ! empty( $person['role'] ) ) {
				$html .= ' - ' . esc_html( $person['role'] );
			}

			$html .= '</li>';
		}

		$html .= '</ul></div>';

		return $html;
	}

	/**
	 * Contact shortcode.
	 */
	public function contact_shortcode( $atts ): string {
		$settings = Parish_Core::get_settings();

		$html = '<div class="parish-contact">';

		if ( ! empty( $settings['parish_address'] ) ) {
			$html .= '<p><strong>' . esc_html__( 'Address:', 'parish-core' ) . '</strong><br>' . nl2br( esc_html( $settings['parish_address'] ) ) . '</p>';
		}

		if ( ! empty( $settings['parish_phone'] ) ) {
			$html .= '<p><strong>' . esc_html__( 'Phone:', 'parish-core' ) . '</strong> ' . esc_html( $settings['parish_phone'] ) . '</p>';
		}

		if ( ! empty( $settings['parish_email'] ) ) {
			$html .= '<p><strong>' . esc_html__( 'Email:', 'parish-core' ) . '</strong> <a href="mailto:' . esc_attr( $settings['parish_email'] ) . '">' . esc_html( $settings['parish_email'] ) . '</a></p>';
		}

		if ( ! empty( $settings['parish_office_hours'] ) ) {
			$html .= '<p><strong>' . esc_html__( 'Office Hours:', 'parish-core' ) . '</strong> ' . esc_html( $settings['parish_office_hours'] ) . '</p>';
		}

		$html .= '</div>';

		return $html;
	}

	// =========================================================================
	// ENHANCED SCHEDULE SHORTCODES
	// =========================================================================

	/**
	 * General schedule shortcode using the new schedule generator.
	 *
	 * Attributes:
	 * - days: Number of days to show (default 7)
	 * - church_id: Filter by church ID
	 * - event_type: Filter by event type (mass, confession, adoration, etc.)
	 * - format: Display format (list, cards, table, simple)
	 * - show_feast_day: yes/no to show feast day info (default yes)
	 * - show_livestream: yes/no to show livestream badge (default yes)
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function schedule_shortcode( $atts ): string {
		if ( ! Parish_Core::is_feature_enabled( 'mass_times' ) ) {
			return '';
		}

		if ( ! class_exists( 'Parish_Schedule_Generator' ) ) {
			return '<p class="parish-schedule-error">' . esc_html__( 'Schedule system not available.', 'parish-core' ) . '</p>';
		}

		$atts = shortcode_atts( array(
			'days'           => 7,
			'church_id'      => '',
			'event_type'     => '',
			'format'         => 'list',
			'show_feast_day' => 'yes',
			'show_livestream' => 'yes',
		), $atts );

		$generator = Parish_Schedule_Generator::instance();
		$start     = gmdate( 'Y-m-d' );
		$end       = gmdate( 'Y-m-d', strtotime( '+' . ( (int) $atts['days'] - 1 ) . ' days' ) );

		$filters = array();
		if ( ! empty( $atts['church_id'] ) ) {
			$filters['church_id'] = (int) $atts['church_id'];
		}
		if ( ! empty( $atts['event_type'] ) ) {
			$filters['event_type'] = sanitize_text_field( $atts['event_type'] );
		}

		$schedule = $generator->generate( $start, $end, $filters );

		if ( empty( $schedule ) ) {
			return '<p class="parish-schedule-empty">' . esc_html__( 'No scheduled events.', 'parish-core' ) . '</p>';
		}

		return $this->render_schedule( $schedule, $atts );
	}

	/**
	 * Church-specific schedule shortcode.
	 * Auto-detects church ID on church single pages or within query loops.
	 * Displays weekly schedule grouped by day with special events section.
	 *
	 * Attributes:
	 * - church_id: Church ID (auto-detects on church pages and in query loops)
	 * - type: Filter by event type (mass, confession, adoration) - default shows all
	 * - show_special: yes/no to show special events section (default yes)
	 * - show_livestream: yes/no to show livestream indicators (default yes)
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function church_schedule_shortcode( $atts ): string {
		if ( ! Parish_Core::is_feature_enabled( 'mass_times' ) ) {
			return '';
		}

		if ( ! class_exists( 'Parish_Schedule_Generator' ) ) {
			return '';
		}

		$atts = shortcode_atts( array(
			'church_id'       => '',
			'type'            => '',
			'show_special'    => 'yes',
			'show_livestream' => 'yes',
		), $atts );

		// Auto-detect church ID - works in query loops and on single pages.
		if ( empty( $atts['church_id'] ) ) {
			// Method 1: Try get_the_ID() - works in block query loops and classic loops.
			$current_id = get_the_ID();
			if ( $current_id && get_post_type( $current_id ) === 'parish_church' ) {
				$atts['church_id'] = $current_id;
			}

			// Method 2: Fallback to global $post.
			if ( empty( $atts['church_id'] ) ) {
				global $post;
				if ( $post && $post->post_type === 'parish_church' ) {
					$atts['church_id'] = $post->ID;
				}
			}

			// Method 3: Fallback for single church pages using queried object.
			if ( empty( $atts['church_id'] ) && is_singular( 'parish_church' ) ) {
				$atts['church_id'] = get_queried_object_id();
			}
		}

		if ( empty( $atts['church_id'] ) ) {
			return '';
		}

		$church_id      = (int) $atts['church_id'];
		$filter_type    = sanitize_text_field( $atts['type'] );
		$show_livestream = $atts['show_livestream'] === 'yes';

		// Clock icon SVG.
		$clock_icon = '<svg class="schedule-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>';

		// Livestream icon SVG.
		$live_icon = '<svg class="livestream-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 7 3 5"/><path d="M9 6V3"/><path d="m13 7 2-2"/><path d="M17 6v3"/><path d="M19 10h3"/><path d="M21 14 3 14"/><rect width="18" height="8" x="3" y="14" rx="2"/><path d="M7 18h10"/></svg>';

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

		if ( empty( $mass_time_posts ) ) {
			return '<p class="parish-schedule-empty">' . esc_html__( 'No scheduled times.', 'parish-core' ) . '</p>';
		}

		// Day order for weekly schedule.
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

			// Parse time from datetime.
			$time = '';
			if ( $start_datetime ) {
				$time = $this->format_time( $start_datetime );
			}

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
				// Weekly recurring - get the days.
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
					// Daily recurring - add to all days.
					foreach ( $day_order as $day ) {
						if ( ! isset( $weekly_data[ $day ] ) ) {
							$weekly_data[ $day ] = array();
						}
						$weekly_data[ $day ][] = $event_data;
					}
				} elseif ( $rec_type === 'monthly_ordinal' ) {
					// Monthly ordinal (e.g., First Friday) - treat as special.
					$ordinal     = $recurrence['ordinal'] ?? 'first';
					$ordinal_day = $recurrence['ordinal_day'] ?? '';
					$event_data['title'] = ucfirst( $ordinal ) . ' ' . $ordinal_day;
					$special_events[] = $event_data;
				} else {
					// Other recurrence types - treat as special.
					$special_events[] = $event_data;
				}
			} else {
				// One-off event without special flag - add to special.
				$special_events[] = $event_data;
			}
		}

		// Sort events within each day by time.
		foreach ( $weekly_data as $day => $events ) {
			usort( $weekly_data[ $day ], function ( $a, $b ) {
				return strcmp( $a['time'], $b['time'] );
			});
		}

		// Build HTML output.
		$html = '<div class="parish-church-schedule">';

		// Weekly schedule section.
		if ( ! empty( $weekly_data ) ) {
			$html .= '<div class="schedule-table">';

			foreach ( $day_order as $day ) {
				if ( ! isset( $weekly_data[ $day ] ) ) {
					continue;
				}

				$events = $weekly_data[ $day ];
				$html .= '<div class="schedule-row">';

				// Day label with clock icon.
				$html .= '<div class="schedule-day">';
				$html .= $clock_icon;
				$html .= '<span class="day-name">' . esc_html( $day ) . '</span>';
				$html .= '</div>';

				// Times column.
				$html .= '<div class="schedule-times">';
				$time_parts = array();
				foreach ( $events as $event ) {
					$time_html = '<span class="time">' . esc_html( $event['time'] ) . '</span>';

					if ( $show_livestream && $event['is_livestreamed'] ) {
						$time_html .= $live_icon;
					}

					if ( ! empty( $event['notes'] ) ) {
						$time_html .= '<span class="time-note">(' . esc_html( wp_strip_all_tags( $event['notes'] ) ) . ')</span>';
					}

					$time_parts[] = $time_html;
				}
				$html .= implode( '<span class="time-sep">,</span> ', $time_parts );
				$html .= '</div>';

				// Side note column (use from first event that has one).
				$side_note = '';
				foreach ( $events as $event ) {
					if ( ! empty( $event['side_note'] ) ) {
						$side_note = $event['side_note'];
						break;
					}
				}
				if ( $side_note ) {
					$html .= '<div class="schedule-side-note">' . esc_html( $side_note ) . '</div>';
				}

				$html .= '</div>'; // .schedule-row
			}

			$html .= '</div>'; // .schedule-table
		}

		// Special events section.
		if ( $atts['show_special'] === 'yes' && ! empty( $special_events ) ) {
			$html .= '<div class="schedule-table schedule-special">';

			foreach ( $special_events as $event ) {
				$html .= '<div class="schedule-row">';

				// Event title with clock icon.
				$html .= '<div class="schedule-day">';
				$html .= $clock_icon;
				$html .= '<span class="day-name">' . esc_html( $event['title'] ) . '</span>';
				$html .= '</div>';

				// Time.
				$html .= '<div class="schedule-times">';
				$html .= '<span class="time">' . esc_html( $event['time'] ) . '</span>';

				if ( $show_livestream && $event['is_livestreamed'] ) {
					$html .= $live_icon;
				}

				if ( ! empty( $event['notes'] ) ) {
					$html .= '<span class="time-note">(' . esc_html( wp_strip_all_tags( $event['notes'] ) ) . ')</span>';
				}
				$html .= '</div>';

				// Side note.
				if ( ! empty( $event['side_note'] ) ) {
					$html .= '<div class="schedule-side-note">' . esc_html( $event['side_note'] ) . '</div>';
				}

				$html .= '</div>'; // .schedule-row
			}

			$html .= '</div>'; // .schedule-special
		}

		$html .= '</div>'; // .parish-church-schedule

		return $html;
	}

	/**
	 * Weekly schedule shortcode - shows full week grouped by day.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function weekly_schedule_shortcode( $atts ): string {
		if ( ! Parish_Core::is_feature_enabled( 'mass_times' ) ) {
			return '';
		}

		if ( ! class_exists( 'Parish_Schedule_Generator' ) ) {
			return '<p class="parish-schedule-error">' . esc_html__( 'Schedule system not available.', 'parish-core' ) . '</p>';
		}

		$atts = shortcode_atts( array(
			'church_id'       => '',
			'event_type'      => '',
			'show_feast_day'  => 'yes',
			'show_livestream' => 'yes',
		), $atts );

		$generator = Parish_Schedule_Generator::instance();
		$schedule  = $generator->generate_week( array(
			'church_id'  => $atts['church_id'] ? (int) $atts['church_id'] : null,
			'event_type' => $atts['event_type'] ?: null,
		) );

		if ( empty( $schedule ) ) {
			return '<p class="parish-schedule-empty">' . esc_html__( 'No scheduled events this week.', 'parish-core' ) . '</p>';
		}

		// Group by day.
		$by_day = array();
		foreach ( $schedule as $event ) {
			$date = $event['date'] ?? '';
			if ( ! isset( $by_day[ $date ] ) ) {
				$day_name = gmdate( 'l', strtotime( $date ) );
				$by_day[ $date ] = array(
					'date'     => $date,
					'day_name' => $day_name,
					'events'   => array(),
				);
			}
			$by_day[ $date ]['events'][] = $event;
		}

		$html = '<div class="parish-weekly-schedule">';

		foreach ( $by_day as $day_data ) {
			$is_today    = $day_data['date'] === gmdate( 'Y-m-d' );
			$today_class = $is_today ? ' is-today' : '';
			$date_label  = gmdate( 'F j', strtotime( $day_data['date'] ) );

			$html .= '<div class="schedule-day' . $today_class . '">';
			$html .= '<h4 class="day-header">';
			$html .= '<span class="day-name">' . esc_html( $day_data['day_name'] ) . '</span>';
			$html .= '<span class="day-date">' . esc_html( $date_label ) . '</span>';
			if ( $is_today ) {
				$html .= '<span class="today-badge">' . esc_html__( 'Today', 'parish-core' ) . '</span>';
			}
			$html .= '</h4>';

			$html .= '<ul class="schedule-events">';
			foreach ( $day_data['events'] as $event ) {
				$html .= $this->render_event_item( $event, $atts );
			}
			$html .= '</ul>';
			$html .= '</div>';
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Today's schedule shortcode with feast day info.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function today_schedule_shortcode( $atts ): string {
		if ( ! Parish_Core::is_feature_enabled( 'mass_times' ) ) {
			return '';
		}

		if ( ! class_exists( 'Parish_Schedule_Generator' ) ) {
			return '<p class="parish-schedule-error">' . esc_html__( 'Schedule system not available.', 'parish-core' ) . '</p>';
		}

		$atts = shortcode_atts( array(
			'church_id'       => '',
			'event_type'      => '',
			'show_feast_day'  => 'yes',
			'show_livestream' => 'yes',
		), $atts );

		$generator = Parish_Schedule_Generator::instance();
		$schedule  = $generator->generate_today( array(
			'church_id'  => $atts['church_id'] ? (int) $atts['church_id'] : null,
			'event_type' => $atts['event_type'] ?: null,
		) );

		$html = '<div class="parish-today-schedule">';

		// Feast day header.
		if ( $atts['show_feast_day'] === 'yes' ) {
			$feast_html = $this->render_feast_day_header();
			if ( $feast_html ) {
				$html .= $feast_html;
			}
		}

		// Today's date.
		$html .= '<h4 class="today-header">';
		$html .= '<span class="today-date">' . esc_html( gmdate( 'l, F j, Y' ) ) . '</span>';
		$html .= '</h4>';

		if ( empty( $schedule ) ) {
			$html .= '<p class="no-events">' . esc_html__( 'No events scheduled for today.', 'parish-core' ) . '</p>';
		} else {
			$html .= '<ul class="schedule-events">';
			foreach ( $schedule as $event ) {
				$html .= $this->render_event_item( $event, $atts );
			}
			$html .= '</ul>';
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Render schedule HTML based on format.
	 *
	 * @param array $schedule Schedule data.
	 * @param array $atts     Shortcode attributes.
	 * @return string HTML output.
	 */
	private function render_schedule( array $schedule, array $atts ): string {
		$format = $atts['format'] ?? 'list';

		switch ( $format ) {
			case 'table':
				return $this->render_schedule_table( $schedule, $atts );
			case 'cards':
				return $this->render_schedule_cards( $schedule, $atts );
			case 'simple':
				return $this->render_schedule_simple( $schedule, $atts );
			case 'list':
			default:
				return $this->render_schedule_list( $schedule, $atts );
		}
	}

	/**
	 * Render schedule as a grouped list.
	 *
	 * @param array $schedule Schedule data.
	 * @param array $atts     Shortcode attributes.
	 * @return string HTML output.
	 */
	private function render_schedule_list( array $schedule, array $atts ): string {
		// Group by date.
		$by_date = array();
		foreach ( $schedule as $event ) {
			$date = $event['date'] ?? '';
			$by_date[ $date ][] = $event;
		}

		$html = '<div class="parish-schedule parish-schedule-list">';

		foreach ( $by_date as $date => $events ) {
			$day_name   = gmdate( 'l', strtotime( $date ) );
			$date_label = gmdate( 'F j', strtotime( $date ) );
			$is_today   = $date === gmdate( 'Y-m-d' );

			$html .= '<div class="schedule-day' . ( $is_today ? ' is-today' : '' ) . '">';
			$html .= '<h4 class="day-header">' . esc_html( $day_name ) . ' <span class="day-date">' . esc_html( $date_label ) . '</span></h4>';

			// Show feast day if enabled.
			if ( $atts['show_feast_day'] === 'yes' && isset( $events[0]['feast'] ) ) {
				$feast = $events[0]['feast'];
				if ( ! empty( $feast['title'] ) ) {
					$html .= '<div class="feast-day-info">';
					if ( ! empty( $feast['colour'] ) ) {
						$html .= '<span class="feast-color" style="background-color: ' . esc_attr( $this->get_liturgical_color_hex( $feast['colour'] ) ) . '"></span>';
					}
					$html .= '<span class="feast-title">' . esc_html( $feast['title'] ) . '</span>';
					$html .= '</div>';
				}
			}

			$html .= '<ul class="schedule-events">';
			foreach ( $events as $event ) {
				$html .= $this->render_event_item( $event, $atts );
			}
			$html .= '</ul>';
			$html .= '</div>';
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Render schedule as a table.
	 *
	 * @param array $schedule Schedule data.
	 * @param array $atts     Shortcode attributes.
	 * @return string HTML output.
	 */
	private function render_schedule_table( array $schedule, array $atts ): string {
		$html = '<table class="parish-schedule parish-schedule-table">';
		$html .= '<thead><tr>';
		$html .= '<th>' . esc_html__( 'Day', 'parish-core' ) . '</th>';
		$html .= '<th>' . esc_html__( 'Time', 'parish-core' ) . '</th>';
		$html .= '<th>' . esc_html__( 'Event', 'parish-core' ) . '</th>';
		$html .= '<th>' . esc_html__( 'Location', 'parish-core' ) . '</th>';
		$html .= '</tr></thead>';
		$html .= '<tbody>';

		foreach ( $schedule as $event ) {
			$is_cancelled = ! empty( $event['cancelled'] );
			$row_class    = $is_cancelled ? ' class="cancelled"' : '';

			$html .= '<tr' . $row_class . '>';
			$html .= '<td>' . esc_html( gmdate( 'D, M j', strtotime( $event['date'] ?? '' ) ) ) . '</td>';
			$html .= '<td>' . esc_html( $this->format_time( $event['time'] ?? '' ) ) . '</td>';
			$html .= '<td>';
			$html .= esc_html( $event['event_type_label'] ?? $event['event_type'] ?? '' );
			if ( ! empty( $event['title'] ) ) {
				$html .= ' - ' . esc_html( $event['title'] );
			}
			if ( $atts['show_livestream'] === 'yes' && ! empty( $event['is_livestreamed'] ) ) {
				$html .= ' <span class="livestream-badge">📺</span>';
			}
			$html .= '</td>';
			$html .= '<td>' . esc_html( $event['church_name'] ?? '' ) . '</td>';
			$html .= '</tr>';
		}

		$html .= '</tbody></table>';

		return $html;
	}

	/**
	 * Render schedule as cards.
	 *
	 * @param array $schedule Schedule data.
	 * @param array $atts     Shortcode attributes.
	 * @return string HTML output.
	 */
	private function render_schedule_cards( array $schedule, array $atts ): string {
		$html = '<div class="parish-schedule parish-schedule-cards">';

		foreach ( $schedule as $event ) {
			$is_cancelled = ! empty( $event['cancelled'] );
			$card_class   = $is_cancelled ? ' cancelled' : '';

			$html .= '<div class="schedule-card' . $card_class . '">';
			$html .= '<div class="card-time">' . esc_html( $this->format_time( $event['time'] ?? '' ) ) . '</div>';
			$html .= '<div class="card-content">';
			$html .= '<div class="card-type">' . esc_html( $event['event_type_label'] ?? $event['event_type'] ?? '' ) . '</div>';
			if ( ! empty( $event['title'] ) ) {
				$html .= '<div class="card-title">' . esc_html( $event['title'] ) . '</div>';
			}
			if ( ! empty( $event['church_name'] ) ) {
				$html .= '<div class="card-location">' . esc_html( $event['church_name'] ) . '</div>';
			}
			if ( ! empty( $event['intention'] ) ) {
				$html .= '<div class="card-intention">🕯️ ' . esc_html( $event['intention'] ) . '</div>';
			}
			if ( $atts['show_livestream'] === 'yes' && ! empty( $event['is_livestreamed'] ) ) {
				$url = $event['livestream_url'] ?: '#';
				$html .= '<a href="' . esc_url( $url ) . '" class="livestream-link">📺 ' . esc_html__( 'Watch Live', 'parish-core' ) . '</a>';
			}
			$html .= '</div>';
			$html .= '</div>';
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Render schedule as simple list.
	 *
	 * @param array $schedule Schedule data.
	 * @param array $atts     Shortcode attributes.
	 * @return string HTML output.
	 */
	private function render_schedule_simple( array $schedule, array $atts ): string {
		$html = '<ul class="parish-schedule parish-schedule-simple">';

		foreach ( $schedule as $event ) {
			$html .= '<li>';
			$html .= '<strong>' . esc_html( $this->format_time( $event['time'] ?? '' ) ) . '</strong>';
			$html .= ' - ' . esc_html( $event['event_type_label'] ?? $event['event_type'] ?? '' );
			if ( ! empty( $event['church_name'] ) ) {
				$html .= ' (' . esc_html( $event['church_name'] ) . ')';
			}
			$html .= '</li>';
		}

		$html .= '</ul>';

		return $html;
	}

	/**
	 * Render a single event item.
	 *
	 * @param array $event Event data.
	 * @param array $atts  Shortcode attributes.
	 * @return string HTML output.
	 */
	private function render_event_item( array $event, array $atts ): string {
		$is_cancelled = ! empty( $event['cancelled'] );
		$li_class     = $is_cancelled ? ' class="cancelled"' : '';

		$html = '<li' . $li_class . '>';
		$html .= '<span class="event-time">' . esc_html( $this->format_time( $event['time'] ?? '' ) ) . '</span>';
		$html .= '<span class="event-type">' . esc_html( $event['event_type_label'] ?? $event['event_type'] ?? '' ) . '</span>';

		if ( ! empty( $event['title'] ) ) {
			$html .= '<span class="event-title">' . esc_html( $event['title'] ) . '</span>';
		}

		if ( ! empty( $event['church_name'] ) ) {
			$html .= '<span class="event-location">' . esc_html( $event['church_name'] ) . '</span>';
		}

		if ( ! empty( $event['intention'] ) ) {
			$html .= '<span class="event-intention">🕯️ ' . esc_html( $event['intention'] ) . '</span>';
		}

		if ( $atts['show_livestream'] === 'yes' && ! empty( $event['is_livestreamed'] ) ) {
			$html .= '<span class="livestream-badge">📺</span>';
		}

		if ( $is_cancelled ) {
			$html .= '<span class="cancelled-badge">' . esc_html__( 'Cancelled', 'parish-core' ) . '</span>';
		}

		$html .= '</li>';

		return $html;
	}

	/**
	 * Render feast day header.
	 *
	 * @return string HTML output.
	 */
	private function render_feast_day_header(): string {
		if ( ! class_exists( 'Parish_Feast_Day_Service' ) ) {
			return '';
		}

		$service = new Parish_Feast_Day_Service();
		$feast   = $service->get_feast_day( gmdate( 'Y-m-d' ) );

		if ( empty( $feast ) || empty( $feast['title'] ) ) {
			return '';
		}

		$html = '<div class="feast-day-header">';

		if ( ! empty( $feast['colour'] ) ) {
			$html .= '<span class="feast-color" style="background-color: ' . esc_attr( $this->get_liturgical_color_hex( $feast['colour'] ) ) . '"></span>';
		}

		$html .= '<span class="feast-title">' . esc_html( $feast['title'] ) . '</span>';

		if ( ! empty( $feast['rank'] ) ) {
			$html .= '<span class="feast-rank">' . esc_html( $feast['rank'] ) . '</span>';
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Format time for display.
	 *
	 * @param string $time Time in HH:MM format.
	 * @return string Formatted time.
	 */
	private function format_time( string $time ): string {
		if ( empty( $time ) ) {
			return '';
		}

		$timestamp = strtotime( "2000-01-01 $time" );
		if ( $timestamp === false ) {
			return $time;
		}

		return gmdate( 'g:i A', $timestamp );
	}

	/**
	 * Get hex color for liturgical color name.
	 *
	 * @param string $color Liturgical color name.
	 * @return string Hex color code.
	 */
	private function get_liturgical_color_hex( string $color ): string {
		$colors = array(
			'green'  => '#228B22',
			'white'  => '#FFFFFF',
			'red'    => '#DC143C',
			'violet' => '#8B008B',
			'purple' => '#8B008B',
			'rose'   => '#FF69B4',
			'pink'   => '#FF69B4',
			'black'  => '#000000',
			'gold'   => '#FFD700',
		);

		return $colors[ strtolower( $color ) ] ?? '#666666';
	}

	// =========================================================================
	// TODAY WIDGET SHORTCODE
	// =========================================================================

	/**
	 * Today widget shortcode - compact widget showing Mass Times for a single day.
	 *
	 * Attributes:
	 * - date: Date in YYYY-MM-DD format (default today)
	 * - church_id: Filter by specific church (optional, 0 or empty for all)
	 * - type: Filter by liturgical type (optional)
	 * - show_notes: yes/no to show notes field (default no)
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function today_widget_shortcode( $atts ): string {
		if ( ! Parish_Core::is_feature_enabled( 'mass_times' ) ) {
			return '';
		}

		if ( ! class_exists( 'Parish_Schedule_Generator' ) ) {
			return '';
		}

		$atts = shortcode_atts( array(
			'date'       => '',
			'church_id'  => '',
			'type'       => '',
			'show_notes' => 'no',
		), $atts );

		// Determine the target date.
		$target_date = ! empty( $atts['date'] ) ? sanitize_text_field( $atts['date'] ) : wp_date( 'Y-m-d' );

		// Build filters.
		$filters = array();
		if ( ! empty( $atts['church_id'] ) ) {
			$filters['church_id'] = (int) $atts['church_id'];
		}
		if ( ! empty( $atts['type'] ) ) {
			$filters['event_type'] = sanitize_text_field( $atts['type'] );
		}

		// Generate schedule for the target date.
		$generator = Parish_Schedule_Generator::instance();
		$schedule  = $generator->generate( $target_date, $target_date, $filters );

		// Format the date for display.
		$display_date = wp_date( 'l, j F Y', strtotime( $target_date ) );

		$html = '<div class="parish-today-widget">';
		$html .= '<p class="widget-date">' . esc_html( $display_date ) . '</p>';

		if ( empty( $schedule ) ) {
			$html .= '<p class="no-events">' . esc_html__( 'No events scheduled.', 'parish-core' ) . '</p>';
		} else {
			// Group by church.
			$by_church = array();
			foreach ( $schedule as $event ) {
				$church_id   = $event['church_id'] ?? 0;
				$church_name = $event['church_name'] ?? __( 'All Churches', 'parish-core' );

				if ( ! isset( $by_church[ $church_id ] ) ) {
					$by_church[ $church_id ] = array(
						'name'         => $church_name,
						'has_live'     => false,
						'events'       => array(),
					);
				}
				$by_church[ $church_id ]['events'][] = $event;
				if ( ! empty( $event['is_livestreamed'] ) ) {
					$by_church[ $church_id ]['has_live'] = true;
				}
			}

			foreach ( $by_church as $church_data ) {
				$html .= '<div class="widget-church">';

				// Church name row with live indicator.
				$html .= '<p class="church-name">';
				$html .= esc_html( $church_data['name'] );
				if ( $church_data['has_live'] ) {
					$html .= '<span class="livestream-badge"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 7 3 5"/><path d="M9 6V3"/><path d="m13 7 2-2"/><path d="M17 6v3"/><path d="M19 10h3"/><path d="M21 14 3 14"/><rect width="18" height="8" x="3" y="14" rx="2"/><path d="M7 18h10"/></svg>' . esc_html__( 'Live', 'parish-core' ) . '</span>';
				}
				$html .= '</p>';

				// Times.
				$times = array();
				foreach ( $church_data['events'] as $event ) {
					$time_str = $this->format_time( $event['time'] ?? '' );
					if ( $atts['show_notes'] === 'yes' && ! empty( $event['notes'] ) ) {
						$time_str .= ' <span class="time-note">(' . esc_html( wp_strip_all_tags( $event['notes'] ) ) . ')</span>';
					}
					$times[] = '<span class="time">' . $time_str . '</span>';
				}
				$html .= '<p class="church-times">' . implode( ', ', $times ) . '</p>';

				$html .= '</div>';
			}
		}

		$html .= '</div>'; // .parish-today-widget

		return $html;
	}
}
