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
	 * - show_livestream: "yes" to only show livestreamed masses
	 * - format: "daily" (today only), "weekly" (full week), "simple" (compact list)
	 */
	public function mass_times_shortcode( $atts ): string {
		if ( ! Parish_Core::is_feature_enabled( 'mass_times' ) ) {
			return '';
		}

		$atts = shortcode_atts( array(
			'day'            => '',
			'church_id'      => '',
			'show_livestream' => '',
			'format'         => 'weekly',
		), $atts );

		$mass_times = json_decode( Parish_Core::get_setting( 'mass_times', '[]' ), true ) ?: array();

		if ( empty( $mass_times ) ) {
			return '<p>' . esc_html__( 'No mass times scheduled.', 'parish-core' ) . '</p>';
		}

		// Filter by day.
		if ( $atts['format'] === 'daily' ) {
			$atts['day'] = current_time( 'l' ); // Today's day name.
		}

		if ( ! empty( $atts['day'] ) ) {
			$mass_times = array_filter( $mass_times, function( $mt ) use ( $atts ) {
				return strcasecmp( $mt['day'] ?? '', $atts['day'] ) === 0;
			});
		}

		// Filter by church.
		if ( ! empty( $atts['church_id'] ) ) {
			$church_id = (int) $atts['church_id'];
			$mass_times = array_filter( $mass_times, function( $mt ) use ( $church_id ) {
				return ( $mt['church_id'] ?? 0 ) === $church_id;
			});
		}

		// Filter by livestream.
		if ( $atts['show_livestream'] === 'yes' ) {
			$mass_times = array_filter( $mass_times, function( $mt ) {
				return ! empty( $mt['is_livestreamed'] );
			});
		}

		// Filter active only.
		$mass_times = array_filter( $mass_times, function( $mt ) {
			return $mt['active'] ?? true;
		});

		if ( empty( $mass_times ) ) {
			return '<p>' . esc_html__( 'No mass times match your criteria.', 'parish-core' ) . '</p>';
		}

		// Simple format - just a list.
		if ( $atts['format'] === 'simple' ) {
			$html = '<ul class="parish-mass-times-simple">';
			foreach ( $mass_times as $mt ) {
				$html .= '<li><strong>' . esc_html( $mt['time'] ?? '' ) . '</strong>';
				if ( ! empty( $mt['day'] ) ) {
					$html .= ' (' . esc_html( $mt['day'] ) . ')';
				}
				if ( ! empty( $mt['church_id'] ) ) {
					$html .= ' - ' . esc_html( get_the_title( $mt['church_id'] ) );
				}
				$html .= '</li>';
			}
			$html .= '</ul>';
			return $html;
		}

		// Group by day for weekly format.
		$by_day = array();
		foreach ( $mass_times as $mt ) {
			$day = $mt['day'] ?? 'Unknown';
			$by_day[ $day ][] = $mt;
		}

		$days_order = array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' );

		$html = '<div class="parish-mass-times">';

		foreach ( $days_order as $day ) {
			if ( empty( $by_day[ $day ] ) ) {
				continue;
			}

			$html .= '<div class="mass-day"><h4>' . esc_html( $day ) . '</h4><ul>';

			foreach ( $by_day[ $day ] as $mt ) {
				$html .= '<li>';
				$html .= '<strong>' . esc_html( $mt['time'] ?? '' ) . '</strong>';

				if ( ! empty( $mt['church_id'] ) ) {
					$church_name = get_the_title( $mt['church_id'] );
					if ( $church_name ) {
						$html .= ' - ' . esc_html( $church_name );
					}
				}

				if ( ! empty( $mt['is_livestreamed'] ) ) {
					$html .= ' <span class="livestream-badge">' . esc_html__( 'Livestream', 'parish-core' ) . '</span>';
				}

				if ( ! empty( $mt['notes'] ) ) {
					$html .= ' <em>(' . esc_html( $mt['notes'] ) . ')</em>';
				}

				$html .= '</li>';
			}

			$html .= '</ul></div>';
		}

		$html .= '</div>';

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
}
