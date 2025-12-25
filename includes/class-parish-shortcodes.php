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
		add_shortcode( 'parish_mass_times', array( $this, 'mass_times_shortcode' ) );
		add_shortcode( 'parish_events', array( $this, 'events_shortcode' ) );
		add_shortcode( 'parish_churches', array( $this, 'churches_shortcode' ) );
		add_shortcode( 'parish_clergy', array( $this, 'clergy_shortcode' ) );
		add_shortcode( 'parish_contact', array( $this, 'contact_shortcode' ) );
		add_shortcode( 'parish_prayers', array( $this, 'prayers_shortcode' ) );

		// Enhanced schedule shortcodes.
		add_shortcode( 'parish_schedule', array( $this, 'schedule_shortcode' ) );
		add_shortcode( 'parish_church_schedule', array( $this, 'church_schedule_shortcode' ) );
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
	 * Mass times shortcode.
	 *
	 * Attributes:
	 * - day: Filter by specific day (e.g., "Sunday", "Monday")
	 * - church_id: Filter by specific church ID
	 * - show_livestream: "yes" to only show livestreamed, "no" to hide badge
	 * - format: "daily" (today only), "weekly" (full week), "simple" (compact list)
	 */
	public function mass_times_shortcode( $atts ): string {
		if ( ! Parish_Core::is_feature_enabled( 'mass_times' ) ) {
			return '';
		}

		$atts = shortcode_atts( array(
			'day'            => '',
			'church_id'      => '',
			'show_livestream' => 'yes',
			'format'         => 'weekly',
		), $atts );

		// Get schedule from settings (new simple format: keyed by day name).
		$schedule = Parish_Core::get_setting( 'mass_times_schedule', array() );

		if ( empty( $schedule ) || ! is_array( $schedule ) ) {
			return '<p>' . esc_html__( 'No mass times scheduled.', 'parish-core' ) . '</p>';
		}

		// Event type labels.
		$type_labels = array(
			'mass'       => __( 'Mass', 'parish-core' ),
			'confession' => __( 'Confession', 'parish-core' ),
			'adoration'  => __( 'Adoration', 'parish-core' ),
			'rosary'     => __( 'Rosary', 'parish-core' ),
			'stations'   => __( 'Stations of the Cross', 'parish-core' ),
			'benediction' => __( 'Benediction', 'parish-core' ),
			'vespers'    => __( 'Vespers', 'parish-core' ),
			'novena'     => __( 'Novena', 'parish-core' ),
			'other'      => __( 'Other', 'parish-core' ),
		);

		// Build church name cache.
		$church_names = array();
		if ( post_type_exists( 'parish_church' ) ) {
			$churches = get_posts( array(
				'post_type'      => 'parish_church',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			) );
			foreach ( $churches as $church ) {
				$church_names[ $church->ID ] = $church->post_title;
			}
		}

		// Filter by day if specified.
		if ( $atts['format'] === 'daily' ) {
			$atts['day'] = current_time( 'l' ); // Today's day name.
		}

		$days_order = array( 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' );
		$filter_church_id = $atts['church_id'] ? (string) $atts['church_id'] : '';

		// Single day format.
		if ( ! empty( $atts['day'] ) ) {
			$day_slots = $schedule[ $atts['day'] ] ?? array();

			// Filter by church if specified.
			if ( $filter_church_id ) {
				$day_slots = array_filter( $day_slots, function( $slot ) use ( $filter_church_id ) {
					return empty( $slot['church_id'] ) || $slot['church_id'] === $filter_church_id;
				});
			}

			if ( empty( $day_slots ) ) {
				return '<p>' . esc_html__( 'No times scheduled for this day.', 'parish-core' ) . '</p>';
			}

			// Sort by time.
			usort( $day_slots, function( $a, $b ) {
				return strcmp( $a['time'] ?? '', $b['time'] ?? '' );
			});

			$html = '<div class="parish-mass-times parish-mass-times-daily">';
			$html .= '<h4>' . esc_html( $atts['day'] ) . '</h4>';
			$html .= '<ul>';

			foreach ( $day_slots as $slot ) {
				$html .= $this->render_mass_time_slot( $slot, $type_labels, $church_names, $atts );
			}

			$html .= '</ul></div>';
			return $html;
		}

		// Weekly format - show all days.
		$html = '<div class="parish-mass-times parish-mass-times-weekly">';

		foreach ( $days_order as $day ) {
			$day_slots = $schedule[ $day ] ?? array();

			// Filter by church if specified.
			if ( $filter_church_id ) {
				$day_slots = array_filter( $day_slots, function( $slot ) use ( $filter_church_id ) {
					return empty( $slot['church_id'] ) || $slot['church_id'] === $filter_church_id;
				});
			}

			if ( empty( $day_slots ) ) {
				continue;
			}

			// Sort by time.
			usort( $day_slots, function( $a, $b ) {
				return strcmp( $a['time'] ?? '', $b['time'] ?? '' );
			});

			$html .= '<div class="mass-day">';
			$html .= '<h4>' . esc_html( $day ) . '</h4>';
			$html .= '<ul>';

			foreach ( $day_slots as $slot ) {
				$html .= $this->render_mass_time_slot( $slot, $type_labels, $church_names, $atts );
			}

			$html .= '</ul></div>';
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Render a single mass time slot.
	 *
	 * @param array $slot        Slot data.
	 * @param array $type_labels Event type labels.
	 * @param array $church_names Church ID to name mapping.
	 * @param array $atts        Shortcode attributes.
	 * @return string HTML output.
	 */
	private function render_mass_time_slot( array $slot, array $type_labels, array $church_names, array $atts ): string {
		$time = $slot['time'] ?? '';
		$type = $slot['type'] ?? 'mass';
		$notes = $slot['notes'] ?? '';
		$church_id = $slot['church_id'] ?? '';
		$livestream = ! empty( $slot['livestream'] );
		$type_label = $type_labels[ $type ] ?? ucfirst( $type );

		// Format time for display.
		$display_time = $this->format_time( $time );

		$html = '<li>';
		$html .= '<strong>' . esc_html( $display_time ) . '</strong>';
		$html .= ' <span class="event-type">' . esc_html( $type_label ) . '</span>';

		// Show church name if assigned.
		if ( ! empty( $church_id ) && isset( $church_names[ (int) $church_id ] ) ) {
			$html .= ' <span class="event-church">@ ' . esc_html( $church_names[ (int) $church_id ] ) . '</span>';
		}

		// Show livestream badge.
		if ( $livestream && $atts['show_livestream'] === 'yes' ) {
			$html .= ' <span class="livestream-badge" title="' . esc_attr__( 'Livestreamed', 'parish-core' ) . '">📺</span>';
		}

		if ( ! empty( $notes ) ) {
			$html .= ' <em class="event-notes">(' . esc_html( $notes ) . ')</em>';
		}

		$html .= '</li>';

		return $html;
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
			return $this->mass_times_shortcode( $atts ); // Fallback to legacy.
		}

		$atts = shortcode_atts( array(
			'days'           => 7,
			'church_id'      => '',
			'event_type'     => '',
			'format'         => 'list',
			'show_feast_day' => 'yes',
			'show_livestream' => 'yes',
		), $atts );

		$generator = new Parish_Schedule_Generator();
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
	 * Auto-detects church ID on church single pages.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function church_schedule_shortcode( $atts ): string {
		if ( ! Parish_Core::is_feature_enabled( 'mass_times' ) ) {
			return '';
		}

		$atts = shortcode_atts( array(
			'church_id'      => '',
			'days'           => 7,
			'format'         => 'list',
			'show_feast_day' => 'no',
		), $atts );

		// Auto-detect church ID on single church pages.
		if ( empty( $atts['church_id'] ) && is_singular( 'parish_church' ) ) {
			$atts['church_id'] = get_the_ID();
		}

		if ( empty( $atts['church_id'] ) ) {
			return '<p class="parish-schedule-error">' . esc_html__( 'No church specified.', 'parish-core' ) . '</p>';
		}

		return $this->schedule_shortcode( $atts );
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
			return $this->mass_times_shortcode( array( 'format' => 'weekly' ) );
		}

		$atts = shortcode_atts( array(
			'church_id'       => '',
			'event_type'      => '',
			'show_feast_day'  => 'yes',
			'show_livestream' => 'yes',
		), $atts );

		$generator = new Parish_Schedule_Generator();
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
			return $this->mass_times_shortcode( array( 'format' => 'daily' ) );
		}

		$atts = shortcode_atts( array(
			'church_id'       => '',
			'event_type'      => '',
			'show_feast_day'  => 'yes',
			'show_livestream' => 'yes',
		), $atts );

		$generator = new Parish_Schedule_Generator();
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
			if ( $atts['show_livestream'] === 'yes' && ! empty( $event['livestream']['enabled'] ) ) {
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
			if ( $atts['show_livestream'] === 'yes' && ! empty( $event['livestream']['enabled'] ) ) {
				$url = $event['livestream']['url'] ?? '#';
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

		if ( $atts['show_livestream'] === 'yes' && ! empty( $event['livestream']['enabled'] ) ) {
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
}
