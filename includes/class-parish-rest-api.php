<?php
/**
 * REST API endpoints.
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Parish_REST_API class.
 */
class Parish_REST_API {

	private static ?Parish_REST_API $instance = null;
	private string $namespace = 'parish/v1';

	public static function instance(): Parish_REST_API {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes(): void {
		// Dashboard.
		register_rest_route( $this->namespace, '/dashboard', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_dashboard_data' ),
			'permission_callback' => array( $this, 'can_edit' ),
		));

		// About Parish.
		register_rest_route( $this->namespace, '/about', array(
			array( 'methods' => 'GET', 'callback' => array( $this, 'get_about_data' ), 'permission_callback' => array( $this, 'can_edit' ) ),
			array( 'methods' => 'POST', 'callback' => array( $this, 'update_about_data' ), 'permission_callback' => array( $this, 'can_edit' ) ),
		));

		// Settings.
		register_rest_route( $this->namespace, '/settings', array(
			array( 'methods' => 'GET', 'callback' => array( $this, 'get_settings' ), 'permission_callback' => array( $this, 'can_manage' ) ),
			array( 'methods' => 'POST', 'callback' => array( $this, 'update_settings' ), 'permission_callback' => array( $this, 'can_manage' ) ),
		));

		// Mass Times.
		register_rest_route( $this->namespace, '/mass-times', array(
			array( 'methods' => 'GET', 'callback' => array( $this, 'get_mass_times' ), 'permission_callback' => array( $this, 'can_edit' ) ),
			array( 'methods' => 'POST', 'callback' => array( $this, 'update_mass_times' ), 'permission_callback' => array( $this, 'can_edit' ) ),
		));

		// Events.
		register_rest_route( $this->namespace, '/events', array(
			array( 'methods' => 'GET', 'callback' => array( $this, 'get_events' ), 'permission_callback' => array( $this, 'can_edit' ) ),
			array( 'methods' => 'POST', 'callback' => array( $this, 'update_events' ), 'permission_callback' => array( $this, 'can_edit' ) ),
		));

		// Churches.
		register_rest_route( $this->namespace, '/churches', array(
			'methods' => 'GET', 'callback' => array( $this, 'get_churches' ), 'permission_callback' => array( $this, 'can_edit' ),
		));

		// Readings API.
		register_rest_route( $this->namespace, '/readings/status', array(
			'methods' => 'GET', 'callback' => array( $this, 'get_readings_status' ), 'permission_callback' => array( $this, 'can_manage' ),
		));

		register_rest_route( $this->namespace, '/readings/fetch', array(
			'methods' => 'POST', 'callback' => array( $this, 'fetch_readings' ), 'permission_callback' => array( $this, 'can_manage' ),
		));

		register_rest_route( $this->namespace, '/readings/(?P<endpoint>[a-z_]+)', array(
			'methods' => 'GET', 'callback' => array( $this, 'get_reading' ), 'permission_callback' => '__return_true',
		));

		// Shortcode reference.
		register_rest_route( $this->namespace, '/shortcodes', array(
			'methods' => 'GET', 'callback' => array( $this, 'get_shortcode_reference' ), 'permission_callback' => array( $this, 'can_edit' ),
		));

		// Liturgical.
		register_rest_route( $this->namespace, '/liturgical', array(
			'methods' => 'GET', 'callback' => array( $this, 'get_liturgical_data' ), 'permission_callback' => array( $this, 'can_edit' ),
		));

		// Slider Settings.
		register_rest_route( $this->namespace, '/slider/settings', array(
			array( 'methods' => 'GET', 'callback' => array( $this, 'get_slider_settings' ), 'permission_callback' => array( $this, 'can_edit' ) ),
			array( 'methods' => 'POST', 'callback' => array( $this, 'update_slider_settings' ), 'permission_callback' => array( $this, 'can_edit' ) ),
		));

		// Slider Dynamic Sources.
		register_rest_route( $this->namespace, '/slider/sources', array(
			'methods' => 'GET', 
			'callback' => array( $this, 'get_slider_sources' ), 
			'permission_callback' => array( $this, 'can_edit' ),
		));

		// Slider Preview (public for frontend).
		register_rest_route( $this->namespace, '/slider/preview', array(
			'methods' => 'GET', 
			'callback' => array( $this, 'get_slider_preview' ), 
			'permission_callback' => '__return_true',
		));
	}

	public function can_edit(): bool { return current_user_can( 'edit_posts' ); }
	public function can_manage(): bool { return current_user_can( 'manage_options' ); }

	// =========================================================================
	// DASHBOARD
	// =========================================================================
	public function get_dashboard_data(): \WP_REST_Response {
		$settings = Parish_Core::get_settings();
		$data = array(
			'parish_name'      => $settings['parish_name'] ?? '',
			'parish_logo'      => $this->get_image_url( (int) ( $settings['parish_logo_id'] ?? 0 ) ),
			'parish_banner'    => $this->get_image_url( (int) ( $settings['parish_banner_id'] ?? 0 ) ),
			'diocese_name'     => $settings['parish_diocese_name'] ?? '',
			'diocese_url'      => $settings['parish_diocese_url'] ?? '',
			'quick_actions'    => $this->get_quick_actions( $settings ),
			'resources'        => json_decode( $settings['parish_resources'] ?? '[]', true ) ?: array(),
			'stats'            => $this->get_content_stats(),
			'enabled_features' => $this->get_enabled_features(),
		);

		if ( Parish_Core::is_feature_enabled( 'liturgical' ) ) {
			$data['liturgical'] = $this->get_liturgical_info();
		}

		if ( class_exists( 'Parish_Readings' ) ) {
			$readings = Parish_Readings::instance();
			$mr = $readings->get_reading( 'mass_reading_details' );
			$data['mass_reading_details'] = $mr['content'] ?? $mr ?? null;
		}

		if ( Parish_Core::is_feature_enabled( 'reflections' ) ) {
			$data['reflection'] = $this->get_latest_reflection_data();
		}

		if ( Parish_Core::is_feature_enabled( 'mass_times' ) ) {
			$data['todays_masses'] = $this->get_todays_masses();
			$data['weeks_masses']  = $this->get_weeks_masses();
		}

		$data['recent_death_notices'] = $this->get_recent_posts( 'parish_death_notice', 5, 'death_notices' );
		$data['recent_newsletters']   = $this->get_recent_posts( 'parish_newsletter', 5, 'newsletters' );
		$data['upcoming_events']      = $this->get_upcoming_events();

		return rest_ensure_response( $data );
	}

	private function get_todays_masses(): array {
		$mass_times = json_decode( Parish_Core::get_setting( 'mass_times', '[]' ), true ) ?: array();
		$today = current_time( 'l' );
		$todays = array_filter( $mass_times, fn( $mt ) => ( $mt['day'] ?? '' ) === $today && ( $mt['active'] ?? true ) );
		usort( $todays, fn( $a, $b ) => strcmp( $a['time'] ?? '', $b['time'] ?? '' ) );
		return array_map( fn( $mt ) => array_merge( $mt, array( 'church_name' => $mt['church_id'] ? get_the_title( $mt['church_id'] ) : '' ) ), array_values( $todays ) );
	}

	private function get_weeks_masses(): array {
		$mass_times = json_decode( Parish_Core::get_setting( 'mass_times', '[]' ), true ) ?: array();
		$days = array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' );
		$by_day = array();
		foreach ( $days as $day ) {
			$dm = array_filter( $mass_times, fn( $mt ) => ( $mt['day'] ?? '' ) === $day && ( $mt['active'] ?? true ) );
			usort( $dm, fn( $a, $b ) => strcmp( $a['time'] ?? '', $b['time'] ?? '' ) );
			$by_day[ $day ] = array_map( fn( $mt ) => array_merge( $mt, array( 'church_name' => $mt['church_id'] ? get_the_title( $mt['church_id'] ) : '' ) ), array_values( $dm ) );
		}
		return $by_day;
	}

	private function get_enabled_features(): array {
		$features = array( 'death_notices', 'baptism_notices', 'wedding_notices', 'churches', 'schools', 'cemeteries', 'groups', 'newsletters', 'news', 'gallery', 'reflections', 'mass_times', 'events', 'liturgical', 'prayers', 'slider' );
		$enabled = array();
		foreach ( $features as $f ) { $enabled[ $f ] = Parish_Core::is_feature_enabled( $f ); }
		return $enabled;
	}

	private function get_quick_actions( array $settings ): array {
		$custom = json_decode( $settings['parish_quick_actions'] ?? '[]', true ) ?: array();
		if ( ! empty( $custom ) ) return $custom;
		$defaults = array();
		if ( Parish_Core::is_feature_enabled( 'newsletters' ) ) $defaults[] = array( 'label' => __( 'Add Newsletter', 'parish-core' ), 'url' => admin_url( 'post-new.php?post_type=parish_newsletter' ), 'icon' => 'media-document' );
		if ( Parish_Core::is_feature_enabled( 'reflections' ) ) $defaults[] = array( 'label' => __( 'Add Reflection', 'parish-core' ), 'url' => admin_url( 'post-new.php?post_type=parish_reflection' ), 'icon' => 'format-quote' );
		if ( Parish_Core::is_feature_enabled( 'death_notices' ) ) $defaults[] = array( 'label' => __( 'Add Death Notice', 'parish-core' ), 'url' => admin_url( 'post-new.php?post_type=parish_death_notice' ), 'icon' => 'plus-alt' );
		if ( Parish_Core::is_feature_enabled( 'baptism_notices' ) ) $defaults[] = array( 'label' => __( 'Add Baptism Notice', 'parish-core' ), 'url' => admin_url( 'post-new.php?post_type=parish_baptism' ), 'icon' => 'baby' );
		if ( Parish_Core::is_feature_enabled( 'wedding_notices' ) ) $defaults[] = array( 'label' => __( 'Add Wedding Notice', 'parish-core' ), 'url' => admin_url( 'post-new.php?post_type=parish_wedding' ), 'icon' => 'heart' );
		return $defaults;
	}

	private function get_content_stats(): array {
		$types = array(
			'parish_death_notice' => array( 'label' => __( 'Death Notices', 'parish-core' ), 'feature' => 'death_notices' ),
			'parish_baptism'      => array( 'label' => __( 'Baptisms', 'parish-core' ), 'feature' => 'baptism_notices' ),
			'parish_wedding'      => array( 'label' => __( 'Weddings', 'parish-core' ), 'feature' => 'wedding_notices' ),
			'parish_church'       => array( 'label' => __( 'Churches', 'parish-core' ), 'feature' => 'churches' ),
			'parish_school'       => array( 'label' => __( 'Schools', 'parish-core' ), 'feature' => 'schools' ),
			'parish_cemetery'     => array( 'label' => __( 'Cemeteries', 'parish-core' ), 'feature' => 'cemeteries' ),
			'parish_group'        => array( 'label' => __( 'Parish Groups', 'parish-core' ), 'feature' => 'groups' ),
			'parish_newsletter'   => array( 'label' => __( 'Newsletters', 'parish-core' ), 'feature' => 'newsletters' ),
			'parish_news'         => array( 'label' => __( 'Parish News', 'parish-core' ), 'feature' => 'news' ),
			'parish_gallery'      => array( 'label' => __( 'Gallery', 'parish-core' ), 'feature' => 'gallery' ),
			'parish_reflection'   => array( 'label' => __( 'Reflections', 'parish-core' ), 'feature' => 'reflections' ),
			'parish_prayer'       => array( 'label' => __( 'Prayers', 'parish-core' ), 'feature' => 'prayers' ),
		);

		$stats = array();
		foreach ( $types as $post_type => $cfg ) {
			if ( ! Parish_Core::is_feature_enabled( $cfg['feature'] ) || ! post_type_exists( $post_type ) ) {
				continue;
			}
			$count_obj = wp_count_posts( $post_type );
			$published = isset( $count_obj->publish ) ? (int) $count_obj->publish : 0;
			$draft     = isset( $count_obj->draft )   ? (int) $count_obj->draft   : 0;
			$stats[ $post_type ] = array( 'label' => $cfg['label'], 'published' => $published, 'draft' => $draft );
		}
		return $stats;
	}

	private function get_liturgical_info(): array {
		$info = array( 'date' => current_time( 'Y-m-d' ), 'formatted_date' => current_time( 'l, F j, Y' ), 'season' => $this->calculate_liturgical_season(), 'week' => $this->get_liturgical_week(), 'color' => '', 'feast_day' => '', 'celebrations' => array() );
		if ( class_exists( 'Parish_Readings' ) ) {
			$fd = Parish_Readings::instance()->get_reading( 'feast_day_details' );
			if ( ! empty( $fd['celebrations'] ) ) {
				$info['celebrations'] = $fd['celebrations'];
				if ( ! empty( $fd['celebrations'][0] ) ) {
					$info['feast_day'] = $fd['celebrations'][0]['title'] ?? '';
					$info['color'] = $fd['celebrations'][0]['colour'] ?? '';
				}
			}
		}
		return $info;
	}

	private function calculate_liturgical_season(): string {
		$now = new \DateTime( 'now', new \DateTimeZone( wp_timezone_string() ) );
		$year = (int) $now->format( 'Y' );
		$month = (int) $now->format( 'n' );
		$day = (int) $now->format( 'j' );
		$easter = new \DateTime( "{$year}-03-21" );
		$easter->modify( '+' . easter_days( $year ) . ' days' );
		$ash_wed = clone $easter; $ash_wed->modify( '-46 days' );
		$pentecost = clone $easter; $pentecost->modify( '+49 days' );
		$christmas = new \DateTime( "{$year}-12-25" );
		$advent = clone $christmas;
		$dow = (int) $christmas->format( 'N' );
		$advent->modify( '-' . ( 21 + ( $dow % 7 ) ) . ' days' );
		if ( $now >= $advent && $month === 12 && $day < 25 ) return 'Advent';
		if ( ( $month === 12 && $day >= 25 ) || ( $month === 1 && $day <= 13 ) ) return 'Christmas';
		if ( $now >= $ash_wed && $now < $easter ) return 'Lent';
		if ( $now >= $easter && $now <= $pentecost ) return 'Easter';
		return 'Ordinary Time';
	}

	private function get_liturgical_week(): ?int {
		if ( $this->calculate_liturgical_season() !== 'Ordinary Time' ) return null;
		$now = new \DateTime( 'now', new \DateTimeZone( wp_timezone_string() ) );
		$year = (int) $now->format( 'Y' );
		$easter = new \DateTime( "{$year}-03-21" );
		$easter->modify( '+' . easter_days( $year ) . ' days' );
		$pentecost = clone $easter; $pentecost->modify( '+49 days' );
		if ( $now > $pentecost ) return min( 34, (int) ceil( $now->diff( $pentecost )->days / 7 ) + 9 );
		$epiphany = new \DateTime( "{$year}-01-06" );
		return min( 8, (int) ceil( $now->diff( $epiphany )->days / 7 ) + 1 );
	}

	private function get_recent_posts( string $pt, int $count, string $feature ): array {
		if ( ! Parish_Core::is_feature_enabled( $feature ) || ! post_type_exists( $pt ) ) return array();
		$posts = get_posts( array( 'post_type' => $pt, 'posts_per_page' => $count, 'post_status' => 'publish' ) );
		return array_map( fn( $p ) => array( 'id' => $p->ID, 'title' => $p->post_title, 'date' => get_the_date( 'M j, Y', $p ), 'edit_url' => get_edit_post_link( $p->ID, 'raw' ) ), $posts );
	}

	private function get_upcoming_events(): array {
		if ( ! Parish_Core::is_feature_enabled( 'events' ) ) return array();
		$events = json_decode( Parish_Core::get_setting( 'parish_events', '[]' ), true ) ?: array();
		$today = current_time( 'Y-m-d' );
		$upcoming = array_filter( $events, fn( $e ) => ( $e['date'] ?? '' ) >= $today );
		usort( $upcoming, fn( $a, $b ) => strcmp( $a['date'] ?? '', $b['date'] ?? '' ) );
		return array_slice( $upcoming, 0, 5 );
	}

	private function get_latest_reflection_data(): ?array {
		if ( ! post_type_exists( 'parish_reflection' ) ) return null;
		$r = get_posts( array( 'post_type' => 'parish_reflection', 'posts_per_page' => 1, 'post_status' => 'publish' ) );
		if ( empty( $r ) ) return null;
		return array( 'id' => $r[0]->ID, 'title' => $r[0]->post_title, 'content' => wp_strip_all_tags( $r[0]->post_content ), 'date' => get_the_date( 'F j, Y', $r[0] ) );
	}

	// =========================================================================
	// ABOUT PARISH
	// =========================================================================
	public function get_about_data(): \WP_REST_Response {
		$s = Parish_Core::get_settings();
		return rest_ensure_response( array(
			'parish_name' => $s['parish_name'] ?? '', 'parish_description' => $s['parish_description'] ?? '',
			'parish_logo_id' => (int) ( $s['parish_logo_id'] ?? 0 ), 'parish_logo_url' => $this->get_image_url( (int) ( $s['parish_logo_id'] ?? 0 ) ),
			'parish_banner_id' => (int) ( $s['parish_banner_id'] ?? 0 ), 'parish_banner_url' => $this->get_image_url( (int) ( $s['parish_banner_id'] ?? 0 ) ),
			'parish_diocese_name' => $s['parish_diocese_name'] ?? '', 'parish_diocese_url' => $s['parish_diocese_url'] ?? '',
			'parish_address' => $s['parish_address'] ?? '', 'parish_phone' => $s['parish_phone'] ?? '', 'parish_email' => $s['parish_email'] ?? '',
			'parish_office_hours' => $s['parish_office_hours'] ?? '', 'parish_emergency_phone' => $s['parish_emergency_phone'] ?? '',
			'parish_website' => $s['parish_website'] ?? '', 'parish_facebook' => $s['parish_facebook'] ?? '', 'parish_twitter' => $s['parish_twitter'] ?? '',
			'parish_instagram' => $s['parish_instagram'] ?? '', 'parish_youtube' => $s['parish_youtube'] ?? '', 'parish_livestream' => $s['parish_livestream'] ?? '', 'parish_donate' => $s['parish_donate'] ?? '',
			'parish_clergy' => json_decode( $s['parish_clergy'] ?? '[]', true ) ?: array(),
			'parish_resources' => json_decode( $s['parish_resources'] ?? '[]', true ) ?: array(),
			'parish_quick_actions' => json_decode( $s['parish_quick_actions'] ?? '[]', true ) ?: array(),
		));
	}

	public function update_about_data( \WP_REST_Request $request ): \WP_REST_Response {
		$params = $request->get_json_params();
		$allowed = array( 'parish_name', 'parish_description', 'parish_logo_id', 'parish_banner_id', 'parish_diocese_name', 'parish_diocese_url', 'parish_address', 'parish_phone', 'parish_email', 'parish_office_hours', 'parish_emergency_phone', 'parish_website', 'parish_facebook', 'parish_twitter', 'parish_instagram', 'parish_youtube', 'parish_livestream', 'parish_donate', 'parish_clergy', 'parish_resources', 'parish_quick_actions' );
		$sanitized = array();
		foreach ( $allowed as $key ) {
			if ( ! isset( $params[ $key ] ) ) continue;
			$v = $params[ $key ];
			if ( in_array( $key, array( 'parish_logo_id', 'parish_banner_id' ), true ) ) $sanitized[ $key ] = absint( $v );
			elseif ( in_array( $key, array( 'parish_clergy', 'parish_resources', 'parish_quick_actions' ), true ) ) $sanitized[ $key ] = wp_json_encode( is_array( $v ) ? $v : array() );
			elseif ( strpos( $key, '_url' ) !== false || in_array( $key, array( 'parish_website', 'parish_facebook', 'parish_twitter', 'parish_instagram', 'parish_youtube', 'parish_livestream', 'parish_donate' ), true ) ) $sanitized[ $key ] = esc_url_raw( $v );
			elseif ( $key === 'parish_email' ) $sanitized[ $key ] = sanitize_email( $v );
			elseif ( in_array( $key, array( 'parish_description', 'parish_address' ), true ) ) $sanitized[ $key ] = sanitize_textarea_field( $v );
			else $sanitized[ $key ] = sanitize_text_field( $v );
		}
		Parish_Core::update_settings( $sanitized );
		return rest_ensure_response( array( 'success' => true, 'message' => __( 'Saved.', 'parish-core' ) ) );
	}

	// =========================================================================
	// SLIDER
	// =========================================================================
	public function get_slider_settings(): \WP_REST_Response {
		if ( ! class_exists( 'Parish_Slider' ) ) {
			return rest_ensure_response( array( 'error' => 'Slider module not available.' ) );
		}

		$slider   = Parish_Slider::instance();
		$settings = $slider->get_slider_settings();

		// Enhance slides with image data.
		if ( ! empty( $settings['slides'] ) ) {
			foreach ( $settings['slides'] as &$slide ) {
				if ( ! empty( $slide['image_id'] ) ) {
					$slide['image_url']  = wp_get_attachment_url( $slide['image_id'] );
					$slide['image_data'] = array(
						'id'  => $slide['image_id'],
						'url' => $slide['image_url'],
					);
				}
			}
		}

		return rest_ensure_response( $settings );
	}

	public function update_slider_settings( \WP_REST_Request $request ): \WP_REST_Response {
		if ( ! class_exists( 'Parish_Slider' ) ) {
			return rest_ensure_response( array( 'success' => false, 'message' => 'Slider module not available.' ) );
		}

		$slider = Parish_Slider::instance();
		$params = $request->get_json_params();

		// Sanitize settings.
		$sanitized = array(
			'enabled'          => isset( $params['enabled'] ) ? (bool) $params['enabled'] : true,
			'autoplay'         => isset( $params['autoplay'] ) ? (bool) $params['autoplay'] : true,
			'autoplay_speed'   => isset( $params['autoplay_speed'] ) ? absint( $params['autoplay_speed'] ) : 5000,
			'transition_speed' => isset( $params['transition_speed'] ) ? absint( $params['transition_speed'] ) : 1000,
			'show_arrows'      => isset( $params['show_arrows'] ) ? (bool) $params['show_arrows'] : true,
			'show_dots'        => isset( $params['show_dots'] ) ? (bool) $params['show_dots'] : true,
			'pause_on_hover'   => isset( $params['pause_on_hover'] ) ? (bool) $params['pause_on_hover'] : true,
			'height_desktop'   => isset( $params['height_desktop'] ) ? absint( $params['height_desktop'] ) : 700,
			'height_tablet'    => isset( $params['height_tablet'] ) ? absint( $params['height_tablet'] ) : 500,
			'height_mobile'    => isset( $params['height_mobile'] ) ? absint( $params['height_mobile'] ) : 400,
			'overlay_color'    => isset( $params['overlay_color'] ) ? sanitize_hex_color( $params['overlay_color'] ) : '#4A8391',
			'overlay_opacity'  => isset( $params['overlay_opacity'] ) ? floatval( $params['overlay_opacity'] ) : 0.7,
			'overlay_gradient' => isset( $params['overlay_gradient'] ) ? (bool) $params['overlay_gradient'] : true,
			'slides'           => array(),
		);

		// Sanitize slides.
		if ( ! empty( $params['slides'] ) && is_array( $params['slides'] ) ) {
			foreach ( $params['slides'] as $slide ) {
				$sanitized_slide = array(
					'id'         => sanitize_text_field( $slide['id'] ?? '' ),
					'type'       => in_array( $slide['type'] ?? '', array( 'manual', 'dynamic' ), true ) ? $slide['type'] : 'manual',
					'enabled'    => isset( $slide['enabled'] ) ? (bool) $slide['enabled'] : true,
					'image_id'   => absint( $slide['image_id'] ?? 0 ),
					'image_url'  => esc_url_raw( $slide['image_url'] ?? '' ),
					'text_align' => in_array( $slide['text_align'] ?? '', array( 'left', 'center', 'right' ), true ) ? $slide['text_align'] : 'left',
					'cta_text'   => sanitize_text_field( $slide['cta_text'] ?? '' ),
					'cta_link'   => esc_url_raw( $slide['cta_link'] ?? '' ),
				);

				if ( $sanitized_slide['type'] === 'manual' ) {
					$sanitized_slide['title']       = sanitize_text_field( $slide['title'] ?? '' );
					$sanitized_slide['subtitle']    = sanitize_text_field( $slide['subtitle'] ?? '' );
					$sanitized_slide['description'] = sanitize_textarea_field( $slide['description'] ?? '' );
				} else {
					// Dynamic slide.
					$sanitized_slide['source']               = sanitize_text_field( $slide['source'] ?? '' );
					$sanitized_slide['title_override']       = sanitize_text_field( $slide['title_override'] ?? '' );
					$sanitized_slide['subtitle_override']    = sanitize_text_field( $slide['subtitle_override'] ?? '' );
					$sanitized_slide['description_override'] = sanitize_textarea_field( $slide['description_override'] ?? '' );
				}

				$sanitized['slides'][] = $sanitized_slide;
			}
		}

		$slider->update_slider_settings( $sanitized );

		return rest_ensure_response( array(
			'success' => true,
			'message' => __( 'Slider settings saved.', 'parish-core' ),
		) );
	}

	public function get_slider_sources(): \WP_REST_Response {
		if ( ! class_exists( 'Parish_Slider' ) ) {
			return rest_ensure_response( array() );
		}

		$slider  = Parish_Slider::instance();
		$sources = $slider->get_dynamic_sources();

		$output = array();
		foreach ( $sources as $key => $source ) {
			$output[ $key ] = array(
				'name'        => $source['name'],
				'description' => $source['description'],
				'icon'        => $source['icon'],
			);
		}

		return rest_ensure_response( $output );
	}

	public function get_slider_preview(): \WP_REST_Response {
		if ( ! class_exists( 'Parish_Slider' ) ) {
			return rest_ensure_response( array() );
		}

		$slider = Parish_Slider::instance();
		$slides = $slider->get_slides();

		return rest_ensure_response( array(
			'settings' => $slider->get_slider_settings(),
			'slides'   => $slides,
		) );
	}

	// =========================================================================
	// SETTINGS
	// =========================================================================
	public function get_settings(): \WP_REST_Response {
		$s = Parish_Core::get_settings();
		return rest_ensure_response( array(
			'enable_death_notices' => (bool) ( $s['enable_death_notices'] ?? true ),
			'enable_baptism_notices' => (bool) ( $s['enable_baptism_notices'] ?? true ),
			'enable_wedding_notices' => (bool) ( $s['enable_wedding_notices'] ?? true ),
			'enable_churches' => (bool) ( $s['enable_churches'] ?? true ),
			'enable_schools' => (bool) ( $s['enable_schools'] ?? true ),
			'enable_cemeteries' => (bool) ( $s['enable_cemeteries'] ?? true ),
			'enable_groups' => (bool) ( $s['enable_groups'] ?? true ),
			'enable_newsletters' => (bool) ( $s['enable_newsletters'] ?? true ),
			'enable_news' => (bool) ( $s['enable_news'] ?? true ),
			'enable_gallery' => (bool) ( $s['enable_gallery'] ?? true ),
			'enable_reflections' => (bool) ( $s['enable_reflections'] ?? true ),
			'enable_prayers' => (bool) ( $s['enable_prayers'] ?? true ),
			'enable_mass_times' => (bool) ( $s['enable_mass_times'] ?? true ),
			'enable_events' => (bool) ( $s['enable_events'] ?? true ),
			'enable_liturgical' => (bool) ( $s['enable_liturgical'] ?? true ),
			'enable_slider' => (bool) ( $s['enable_slider'] ?? true ),
			'readings_api_key' => $s['readings_api_key'] ?? '',
			'admin_colors_enabled' => (bool) ( $s['admin_colors_enabled'] ?? false ),
			'admin_color_menu_text' => $s['admin_color_menu_text'] ?? '#ffffff',
			'admin_color_base_menu' => $s['admin_color_base_menu'] ?? '#1d2327',
			'admin_color_highlight' => $s['admin_color_highlight'] ?? '#2271b1',
			'admin_color_notification' => $s['admin_color_notification'] ?? '#d63638',
			'admin_color_background' => $s['admin_color_background'] ?? '#f0f0f1',
			'admin_color_links' => $s['admin_color_links'] ?? '#2271b1',
			'admin_color_buttons' => $s['admin_color_buttons'] ?? '#2271b1',
			'admin_color_form_inputs' => $s['admin_color_form_inputs'] ?? '#2271b1',
		));
	}

	public function update_settings( \WP_REST_Request $request ): \WP_REST_Response {
		$params = $request->get_json_params();
		$toggles = array( 'enable_death_notices', 'enable_baptism_notices', 'enable_wedding_notices', 'enable_churches', 'enable_schools', 'enable_cemeteries', 'enable_groups', 'enable_newsletters', 'enable_news', 'enable_gallery', 'enable_reflections', 'enable_prayers', 'enable_mass_times', 'enable_events', 'enable_liturgical', 'enable_slider', 'admin_colors_enabled' );
		$colors = array( 'admin_color_menu_text', 'admin_color_base_menu', 'admin_color_highlight', 'admin_color_notification', 'admin_color_background', 'admin_color_links', 'admin_color_buttons', 'admin_color_form_inputs' );
		$sanitized = array();
		foreach ( $toggles as $key ) { if ( isset( $params[ $key ] ) ) $sanitized[ $key ] = (bool) $params[ $key ]; }
		foreach ( $colors as $key ) { if ( isset( $params[ $key ] ) ) $sanitized[ $key ] = sanitize_hex_color( $params[ $key ] ) ?: '#1d2327'; }
		if ( isset( $params['readings_api_key'] ) ) $sanitized['readings_api_key'] = sanitize_text_field( $params['readings_api_key'] );
		Parish_Core::update_settings( $sanitized );
		return rest_ensure_response( array( 'success' => true, 'message' => __( 'Settings saved.', 'parish-core' ) ) );
	}

	// =========================================================================
	// MASS TIMES
	// =========================================================================
	public function get_mass_times(): \WP_REST_Response {
		return rest_ensure_response( array(
			'mass_times' => json_decode( Parish_Core::get_setting( 'mass_times', '[]' ), true ) ?: array(),
			'churches' => $this->get_churches_list(),
		));
	}

	public function update_mass_times( \WP_REST_Request $request ): \WP_REST_Response {
		$mass_times = $request->get_json_params()['mass_times'] ?? array();
		$sanitized = array();
		foreach ( $mass_times as $mt ) {
			$sanitized[] = array(
				'id' => sanitize_text_field( $mt['id'] ?? wp_generate_uuid4() ),
				'church_id' => absint( $mt['church_id'] ?? 0 ),
				'day' => sanitize_text_field( $mt['day'] ?? '' ),
				'time' => sanitize_text_field( $mt['time'] ?? '' ),
				'is_recurring' => (bool) ( $mt['is_recurring'] ?? true ),
				'recurrence_type' => sanitize_text_field( $mt['recurrence_type'] ?? 'weekly' ),
				'is_livestreamed' => (bool) ( $mt['is_livestreamed'] ?? false ),
				'livestream_url' => esc_url_raw( $mt['livestream_url'] ?? '' ),
				'notes' => sanitize_textarea_field( $mt['notes'] ?? '' ),
				'active' => (bool) ( $mt['active'] ?? true ),
			);
		}
		Parish_Core::update_settings( array( 'mass_times' => wp_json_encode( $sanitized ) ) );
		return rest_ensure_response( array( 'success' => true, 'message' => __( 'Saved.', 'parish-core' ) ) );
	}

	// =========================================================================
	// EVENTS
	// =========================================================================
	public function get_events(): \WP_REST_Response {
		return rest_ensure_response( array(
			'events' => json_decode( Parish_Core::get_setting( 'parish_events', '[]' ), true ) ?: array(),
			'churches' => $this->get_churches_list(),
		));
	}

	public function update_events( \WP_REST_Request $request ): \WP_REST_Response {
		$events = $request->get_json_params()['events'] ?? array();
		$sanitized = array();
		foreach ( $events as $e ) {
			$sanitized[] = array(
				'id' => sanitize_text_field( $e['id'] ?? wp_generate_uuid4() ),
				'title' => sanitize_text_field( $e['title'] ?? '' ),
				'date' => sanitize_text_field( $e['date'] ?? '' ),
				'time' => sanitize_text_field( $e['time'] ?? '' ),
				'location' => sanitize_text_field( $e['location'] ?? '' ),
				'description' => sanitize_textarea_field( $e['description'] ?? '' ),
				'event_type' => sanitize_text_field( $e['event_type'] ?? 'parish' ),
				'color' => sanitize_hex_color( $e['color'] ?? '#2271b1' ) ?: '#2271b1',
			);
		}
		Parish_Core::update_settings( array( 'parish_events' => wp_json_encode( $sanitized ) ) );
		return rest_ensure_response( array( 'success' => true, 'message' => __( 'Saved.', 'parish-core' ) ) );
	}

	// =========================================================================
	// CHURCHES
	// =========================================================================
	public function get_churches(): \WP_REST_Response {
		return rest_ensure_response( $this->get_churches_list() );
	}

	private function get_churches_list(): array {
		if ( ! post_type_exists( 'parish_church' ) ) return array();
		$churches = get_posts( array( 'post_type' => 'parish_church', 'posts_per_page' => -1, 'post_status' => 'publish', 'orderby' => 'title', 'order' => 'ASC' ) );
		return array_map( fn( $c ) => array( 'id' => $c->ID, 'title' => $c->post_title ), $churches );
	}

	// =========================================================================
	// READINGS API
	// =========================================================================
	public function get_readings_status(): \WP_REST_Response {
		if ( ! class_exists( 'Parish_Readings' ) ) {
			return rest_ensure_response( array( 'error' => 'Readings module not available.' ) );
		}
		$readings = Parish_Readings::instance();
		return rest_ensure_response( array(
			'endpoints' => $readings->get_endpoints_status(),
			'api_key_set' => ! empty( Parish_Core::get_setting( 'readings_api_key', '' ) ),
		));
	}

	public function fetch_readings( \WP_REST_Request $request ): \WP_REST_Response {
		if ( ! class_exists( 'Parish_Readings' ) ) {
			return rest_ensure_response( array( 'success' => false, 'message' => 'Readings module not available.' ) );
		}
		$readings = Parish_Readings::instance();
		$params = $request->get_json_params();
		$endpoint = $params['endpoint'] ?? '';
		if ( $endpoint ) {
			$result = $readings->fetch_endpoint( $endpoint );
			return rest_ensure_response( $result );
		}
		$result = $readings->fetch_all_readings();
		return rest_ensure_response( $result );
	}

	public function get_reading( \WP_REST_Request $request ): \WP_REST_Response {
		if ( ! class_exists( 'Parish_Readings' ) ) {
			return rest_ensure_response( null );
		}
		$endpoint = $request->get_param( 'endpoint' );
		$readings = Parish_Readings::instance();
		return rest_ensure_response( $readings->get_reading( $endpoint ) );
	}

	// =========================================================================
	// SHORTCODES
	// =========================================================================
	public function get_shortcode_reference(): \WP_REST_Response {
		return rest_ensure_response( parish_core_get_shortcode_reference() );
	}

	// =========================================================================
	// LITURGICAL
	// =========================================================================
	public function get_liturgical_data(): \WP_REST_Response {
		return rest_ensure_response( $this->get_liturgical_info() );
	}

	// =========================================================================
	// HELPERS
	// =========================================================================
	private function get_image_url( int $id ): string {
		if ( ! $id ) return '';
		return wp_get_attachment_url( $id ) ?: '';
	}
}