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

		// Events (legacy JSON endpoint).
		register_rest_route( $this->namespace, '/events', array(
			array( 'methods' => 'GET', 'callback' => array( $this, 'get_events' ), 'permission_callback' => array( $this, 'can_edit' ) ),
			array( 'methods' => 'POST', 'callback' => array( $this, 'update_events' ), 'permission_callback' => array( $this, 'can_edit' ) ),
		));

		// Events CPT - Calendar view for React admin.
		register_rest_route( $this->namespace, '/events/calendar', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_events_calendar' ),
			'permission_callback' => array( $this, 'can_edit' ),
			'args'                => array(
				'start'      => array( 'type' => 'string', 'format' => 'date', 'required' => true ),
				'end'        => array( 'type' => 'string', 'format' => 'date', 'required' => true ),
				'church_id'  => array( 'type' => 'integer' ),
				'sacrament'  => array( 'type' => 'string' ),
				'type'       => array( 'type' => 'string' ),
				'featured'   => array( 'type' => 'boolean' ),
			),
		));

		// Events - Taxonomies for filters.
		register_rest_route( $this->namespace, '/events/taxonomies', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_event_taxonomies' ),
			'permission_callback' => '__return_true',
		));

		// Events - List view for admin.
		register_rest_route( $this->namespace, '/events/list', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_events_list' ),
			'permission_callback' => array( $this, 'can_edit' ),
			'args'                => array(
				'filter'    => array( 'type' => 'string', 'default' => 'upcoming' ),
				'church'    => array( 'type' => 'integer', 'default' => 0 ),
				'sacrament' => array( 'type' => 'integer', 'default' => 0 ),
				'cemetery'  => array( 'type' => 'boolean', 'default' => false ),
			),
		));

		// Events - Delete past events.
		register_rest_route( $this->namespace, '/events/delete-past', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'delete_past_events' ),
			'permission_callback' => array( $this, 'can_manage' ),
		));

		// Churches.
		register_rest_route( $this->namespace, '/churches', array(
			'methods' => 'GET', 'callback' => array( $this, 'get_churches' ), 'permission_callback' => array( $this, 'can_edit' ),
		));

		// Churches list for event creation.
		register_rest_route( $this->namespace, '/churches/list', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_churches_list' ),
			'permission_callback' => array( $this, 'can_edit' ),
		));

		// Readings API.
		register_rest_route( $this->namespace, '/readings/status', array(
			'methods' => 'GET', 'callback' => array( $this, 'get_readings_status' ), 'permission_callback' => array( $this, 'can_manage' ),
		));

		register_rest_route( $this->namespace, '/readings/fetch', array(
			'methods' => 'POST', 'callback' => array( $this, 'fetch_readings' ), 'permission_callback' => array( $this, 'can_manage' ),
		));

		// Readings schedule management.
		register_rest_route( $this->namespace, '/readings/schedules', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_readings_schedules' ),
			'permission_callback' => array( $this, 'can_manage' ),
		));

		register_rest_route( $this->namespace, '/readings/schedules/(?P<endpoint>[a-z_]+)', array(
			'methods'             => 'PUT',
			'callback'            => array( $this, 'update_endpoint_schedule' ),
			'permission_callback' => array( $this, 'can_manage' ),
			'args'                => array(
				'schedule'   => array( 'type' => 'string', 'required' => true ),
				'time'       => array( 'type' => 'string' ),
				'times'      => array( 'type' => 'array' ),
				'start_time' => array( 'type' => 'string' ),
			),
		));

		register_rest_route( $this->namespace, '/readings/fetch/(?P<endpoint>[a-z_]+)', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'fetch_single_reading' ),
			'permission_callback' => array( $this, 'can_manage' ),
		));

		register_rest_route( $this->namespace, '/readings/schedule-options', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_schedule_options' ),
			'permission_callback' => array( $this, 'can_manage' ),
		));

		// Cron status and management.
		register_rest_route( $this->namespace, '/readings/cron-status', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_readings_cron_status' ),
			'permission_callback' => array( $this, 'can_manage' ),
		));

		register_rest_route( $this->namespace, '/readings/reschedule-cron', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'reschedule_readings_cron' ),
			'permission_callback' => array( $this, 'can_manage' ),
		));

		// Keep this dynamic endpoint route after fixed /readings/* routes.
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

		// =====================================================================
		// SCHEDULE SYSTEM (Enhanced Mass Times)
		// =====================================================================

		// Schedule templates (CRUD).
		register_rest_route( $this->namespace, '/schedule/templates', array(
			array( 'methods' => 'GET', 'callback' => array( $this, 'get_schedule_templates' ), 'permission_callback' => array( $this, 'can_edit' ) ),
			array( 'methods' => 'POST', 'callback' => array( $this, 'save_schedule_template' ), 'permission_callback' => array( $this, 'can_edit' ) ),
		));

		register_rest_route( $this->namespace, '/schedule/templates/(?P<id>[a-z0-9_]+)', array(
			array( 'methods' => 'GET', 'callback' => array( $this, 'get_schedule_template' ), 'permission_callback' => array( $this, 'can_edit' ) ),
			array( 'methods' => 'PUT', 'callback' => array( $this, 'update_schedule_template' ), 'permission_callback' => array( $this, 'can_edit' ) ),
			array( 'methods' => 'DELETE', 'callback' => array( $this, 'delete_schedule_template' ), 'permission_callback' => array( $this, 'can_edit' ) ),
		));

		// Schedule overrides.
		register_rest_route( $this->namespace, '/schedule/overrides', array(
			array( 'methods' => 'GET', 'callback' => array( $this, 'get_schedule_overrides' ), 'permission_callback' => array( $this, 'can_edit' ) ),
			array( 'methods' => 'POST', 'callback' => array( $this, 'add_schedule_override' ), 'permission_callback' => array( $this, 'can_edit' ) ),
		));

		register_rest_route( $this->namespace, '/schedule/overrides/(?P<id>[a-z0-9_]+)', array(
			array( 'methods' => 'DELETE', 'callback' => array( $this, 'delete_schedule_override' ), 'permission_callback' => array( $this, 'can_edit' ) ),
		));

		// Generated schedule (public endpoints).
		register_rest_route( $this->namespace, '/schedule', array(
			'methods' => 'GET',
			'callback' => array( $this, 'get_generated_schedule' ),
			'permission_callback' => '__return_true',
			'args' => array(
				'start' => array( 'type' => 'string', 'format' => 'date' ),
				'end' => array( 'type' => 'string', 'format' => 'date' ),
				'church_id' => array( 'type' => 'integer' ),
				'event_type' => array( 'type' => 'string' ),
			),
		));

		register_rest_route( $this->namespace, '/schedule/today', array(
			'methods' => 'GET',
			'callback' => array( $this, 'get_today_schedule' ),
			'permission_callback' => '__return_true',
		));

		register_rest_route( $this->namespace, '/schedule/week', array(
			'methods' => 'GET',
			'callback' => array( $this, 'get_week_schedule' ),
			'permission_callback' => '__return_true',
		));

		// Event types reference.
		register_rest_route( $this->namespace, '/schedule/event-types', array(
			'methods' => 'GET',
			'callback' => array( $this, 'get_event_types' ),
			'permission_callback' => '__return_true',
		));

		// =====================================================================
		// MASS TIMES CPT ENDPOINTS (New CPT-based system)
		// =====================================================================

		// Mass Times CRUD.
		register_rest_route( $this->namespace, '/mass-times', array(
			array( 'methods' => 'GET', 'callback' => array( $this, 'get_mass_times' ), 'permission_callback' => array( $this, 'can_edit' ) ),
			array( 'methods' => 'POST', 'callback' => array( $this, 'create_mass_time' ), 'permission_callback' => array( $this, 'can_edit' ) ),
		));

		register_rest_route( $this->namespace, '/mass-times/(?P<id>\d+)', array(
			array( 'methods' => 'GET', 'callback' => array( $this, 'get_mass_time' ), 'permission_callback' => array( $this, 'can_edit' ) ),
			array( 'methods' => 'PUT', 'callback' => array( $this, 'update_mass_time' ), 'permission_callback' => array( $this, 'can_edit' ) ),
			array( 'methods' => 'DELETE', 'callback' => array( $this, 'delete_mass_time' ), 'permission_callback' => array( $this, 'can_edit' ) ),
		));

		// Cancel single occurrence.
		register_rest_route( $this->namespace, '/mass-times/(?P<id>\d+)/cancel/(?P<date>[0-9-]+)', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'cancel_mass_occurrence' ),
			'permission_callback' => array( $this, 'can_edit' ),
			'args'                => array(
				'id'   => array( 'type' => 'integer', 'required' => true ),
				'date' => array( 'type' => 'string', 'format' => 'date', 'required' => true ),
			),
		));

		// Cancel entire series.
		register_rest_route( $this->namespace, '/mass-times/(?P<id>\d+)/cancel-series', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'cancel_mass_series' ),
			'permission_callback' => array( $this, 'can_edit' ),
			'args'                => array(
				'id' => array( 'type' => 'integer', 'required' => true ),
			),
		));

		// Restore cancelled occurrence.
		register_rest_route( $this->namespace, '/mass-times/(?P<id>\d+)/restore/(?P<date>[0-9-]+)', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'restore_mass_occurrence' ),
			'permission_callback' => array( $this, 'can_edit' ),
			'args'                => array(
				'id'   => array( 'type' => 'integer', 'required' => true ),
				'date' => array( 'type' => 'string', 'format' => 'date', 'required' => true ),
			),
		));

		// Mass Times occurrences query.
		register_rest_route( $this->namespace, '/mass-times/occurrences', array(
			'methods' => 'GET',
			'callback' => array( $this, 'get_mass_time_occurrences' ),
			'permission_callback' => '__return_true',
			'args' => array(
				'from' => array( 'type' => 'string', 'format' => 'date', 'required' => true ),
				'to' => array( 'type' => 'string', 'format' => 'date', 'required' => true ),
				'church_id' => array( 'type' => 'integer' ),
				'type' => array( 'type' => 'string' ),
				'active_only' => array( 'type' => 'boolean', 'default' => true ),
			),
		));
	}

	public function can_edit(): bool { return current_user_can( 'edit_posts' ); }
	public function can_manage(): bool { return current_user_can( 'manage_options' ); }

	/**
	 * Supported default non-admin WordPress roles.
	 *
	 * @return string[]
	 */
	private function get_default_non_admin_roles(): array {
		return array(
			'editor',
			'author',
			'contributor',
			'subscriber',
		);
	}

	/**
	 * Labels for default non-admin roles.
	 */
	private function get_default_non_admin_role_labels(): array {
		return array(
			'editor'      => __( 'Editor', 'parish-core' ),
			'author'      => __( 'Author', 'parish-core' ),
			'contributor' => __( 'Contributor', 'parish-core' ),
			'subscriber'  => __( 'Subscriber', 'parish-core' ),
		);
	}

	/**
	 * Default menu options for non-admin role behavior.
	 */
	private function get_default_menu_options(): array {
		return array(
			'menu_order' => $this->get_default_menu_order(),
			'flatten_roles' => array(
				'editor'      => true,
				'author'      => true,
				'contributor' => true,
				'subscriber'  => true,
			),
			'replace_dashboard_roles' => array(
				'editor'      => true,
				'author'      => true,
				'contributor' => true,
				'subscriber'  => true,
			),
		);
	}

	/**
	 * Menu order tokens with labels.
	 */
	private function get_menu_order_labels(): array {
		return array(
			'parish-core'       => __( 'Dashboard', 'parish-core' ),
			'parish-about'      => __( 'About Parish', 'parish-core' ),
			'parish-events'     => __( 'Events', 'parish-core' ),
			'parish-mass-times' => __( 'Mass Times', 'parish-core' ),
			'parish-slider'     => __( 'Slider', 'parish-core' ),
			'cpts'              => __( 'Content Types (CPTs)', 'parish-core' ),
			'parish-readings'   => __( 'Readings API', 'parish-core' ),
			'parish-settings'   => __( 'Settings', 'parish-core' ),
			'remaining'         => __( 'Other Items', 'parish-core' ),
		);
	}

	/**
	 * Default menu order tokens.
	 *
	 * @return string[]
	 */
	private function get_default_menu_order(): array {
		return array_keys( $this->get_menu_order_labels() );
	}

	/**
	 * Normalize menu order tokens.
	 *
	 * @param mixed $value Raw value.
	 * @return string[]
	 */
	private function normalize_menu_order( $value ): array {
		$defaults = $this->get_default_menu_order();

		if ( ! is_array( $value ) ) {
			return $defaults;
		}

		$order = array();
		foreach ( $value as $token ) {
			$token = is_string( $token ) ? trim( $token ) : '';
			if ( '' === $token || ! in_array( $token, $defaults, true ) || in_array( $token, $order, true ) ) {
				continue;
			}
			$order[] = $token;
		}

		foreach ( $defaults as $token ) {
			if ( in_array( $token, $order, true ) ) {
				continue;
			}
			$order[] = $token;
		}

		return $order;
	}

	/**
	 * Normalize menu options from settings storage.
	 *
	 * @param mixed $raw Raw menu options value.
	 */
	private function normalize_menu_options( $raw ): array {
		$defaults = $this->get_default_menu_options();

		if ( is_string( $raw ) ) {
			$decoded = json_decode( $raw, true );
			$raw     = is_array( $decoded ) ? $decoded : array();
		}

		if ( ! is_array( $raw ) ) {
			$raw = array();
		}

		$flatten_roles           = is_array( $raw['flatten_roles'] ?? null ) ? $raw['flatten_roles'] : array();
		$replace_dashboard_roles = is_array( $raw['replace_dashboard_roles'] ?? null ) ? $raw['replace_dashboard_roles'] : array();
		$menu_order              = $this->normalize_menu_order( $raw['menu_order'] ?? $defaults['menu_order'] );

		foreach ( $this->get_default_non_admin_roles() as $role ) {
			$flatten_roles[ $role ] = isset( $flatten_roles[ $role ] )
				? (bool) $flatten_roles[ $role ]
				: (bool) $defaults['flatten_roles'][ $role ];

			$replace_dashboard_roles[ $role ] = isset( $replace_dashboard_roles[ $role ] )
				? (bool) $replace_dashboard_roles[ $role ]
				: (bool) $defaults['replace_dashboard_roles'][ $role ];
		}

		return array(
			'flatten_roles'           => $flatten_roles,
			'replace_dashboard_roles' => $replace_dashboard_roles,
			'menu_order'              => $menu_order,
		);
	}

	/**
	 * Sanitize menu options payload from API.
	 *
	 * @param mixed $value Request value.
	 */
	private function sanitize_menu_options( $value ): array {
		return $this->normalize_menu_options( $value );
	}

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

		$data['recent_death_notices'] = $this->get_recent_posts( 'parish_death_notice', 5, 'death_notices' );
		$data['recent_newsletters']   = $this->get_recent_posts( 'parish_newsletter', 5, 'newsletters' );
		$data['upcoming_events']      = $this->get_upcoming_events();
		$data['next_mass_times']      = $this->get_next_mass_times();
		$data['recent_posts']         = $this->get_all_recent_posts( 7 );

		return rest_ensure_response( $data );
	}

	/**
	 * Get recently published posts across all parish post types.
	 *
	 * @param int $limit Number of posts to return.
	 * @return array Recent posts with type labels.
	 */
	private function get_all_recent_posts( int $limit = 7 ): array {
		$post_types = array(
			'parish_newsletter'   => array( 'label' => __( 'Newsletter', 'parish-core' ), 'feature' => 'newsletters' ),
			'parish_death_notice' => array( 'label' => __( 'Death Notice', 'parish-core' ), 'feature' => 'death_notices' ),
			'parish_reflection'   => array( 'label' => __( 'Reflection', 'parish-core' ), 'feature' => 'reflections' ),
			'parish_event'        => array( 'label' => __( 'Event', 'parish-core' ), 'feature' => 'events' ),
			'post'                => array( 'label' => __( 'Post', 'parish-core' ), 'feature' => 'news' ),
			'parish_baptism'      => array( 'label' => __( 'Baptism', 'parish-core' ), 'feature' => 'baptism_notices' ),
			'parish_wedding'      => array( 'label' => __( 'Wedding', 'parish-core' ), 'feature' => 'wedding_notices' ),
		);

		$enabled_types = array();
		foreach ( $post_types as $type => $cfg ) {
			if ( Parish_Core::is_feature_enabled( $cfg['feature'] ) && post_type_exists( $type ) ) {
				$enabled_types[] = $type;
			}
		}

		if ( empty( $enabled_types ) ) {
			return array();
		}

		$posts = get_posts( array(
			'post_type'      => $enabled_types,
			'posts_per_page' => $limit,
			'post_status'    => 'publish',
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );

		$result = array();
		foreach ( $posts as $p ) {
			$type_config = $post_types[ $p->post_type ] ?? null;
			$result[] = array(
				'id'         => $p->ID,
				'title'      => $p->post_title,
				'date'       => wp_date( 'M j', strtotime( $p->post_date ) ),
				'type_label' => $type_config ? $type_config['label'] : ucfirst( str_replace( 'parish_', '', $p->post_type ) ),
				'edit_url'   => get_edit_post_link( $p->ID, 'raw' ),
			);
		}

		return $result;
	}

	private function get_enabled_features(): array {
		$features = array( 'death_notices', 'baptism_notices', 'wedding_notices', 'churches', 'schools', 'cemeteries', 'groups', 'newsletters', 'news', 'gallery', 'reflections', 'events', 'liturgical', 'prayers', 'slider', 'mass_times' );
		$enabled = array();
		foreach ( $features as $f ) { $enabled[ $f ] = Parish_Core::is_feature_enabled( $f ); }
		return $enabled;
	}

	private function get_quick_actions( array $settings ): array {
		$custom = json_decode( $settings['parish_quick_actions'] ?? '[]', true ) ?: array();
		if ( ! empty( $custom ) ) return $custom;
		$defaults = array();
		$defaults[] = array( 'label' => __( 'Parish Details', 'parish-core' ), 'url' => admin_url( 'admin.php?page=parish-about' ), 'icon' => 'admin-home' );
		if ( Parish_Core::is_feature_enabled( 'mass_times' ) ) $defaults[] = array( 'label' => __( 'Mass Times', 'parish-core' ), 'url' => admin_url( 'admin.php?page=parish-mass-times' ), 'icon' => 'clock' );
		if ( Parish_Core::is_feature_enabled( 'events' ) ) $defaults[] = array( 'label' => __( 'Events', 'parish-core' ), 'url' => admin_url( 'admin.php?page=parish-events' ), 'icon' => 'calendar-alt' );
		if ( Parish_Core::is_feature_enabled( 'newsletters' ) ) $defaults[] = array( 'label' => __( 'Add Newsletter', 'parish-core' ), 'url' => admin_url( 'post-new.php?post_type=parish_newsletter' ), 'icon' => 'media-document' );
		if ( Parish_Core::is_feature_enabled( 'death_notices' ) ) $defaults[] = array( 'label' => __( 'Add Death Notice', 'parish-core' ), 'url' => admin_url( 'post-new.php?post_type=parish_death_notice' ), 'icon' => 'plus-alt' );
		if ( Parish_Core::is_feature_enabled( 'reflections' ) ) $defaults[] = array( 'label' => __( 'Add Reflection', 'parish-core' ), 'url' => admin_url( 'post-new.php?post_type=parish_reflection' ), 'icon' => 'format-quote' );
		if ( Parish_Core::is_feature_enabled( 'baptism_notices' ) ) $defaults[] = array( 'label' => __( 'Add Baptism Notice', 'parish-core' ), 'url' => admin_url( 'post-new.php?post_type=parish_baptism' ), 'icon' => 'groups' );
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
			'post'                => array( 'label' => __( 'News Posts', 'parish-core' ), 'feature' => 'news' ),
			'parish_mass_time'    => array( 'label' => __( 'Mass Times', 'parish-core' ), 'feature' => 'mass_times' ),
			'parish_event'        => array( 'label' => __( 'Events', 'parish-core' ), 'feature' => 'events' ),
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

		$today = current_time( 'Y-m-d' );

		// Prefer Events CPT data when available.
		if ( post_type_exists( 'parish_event' ) ) {
			$query = new \WP_Query(
				array(
					'post_type'      => 'parish_event',
					'posts_per_page' => 5,
					'post_status'    => 'publish',
					'orderby'        => 'meta_value',
					'meta_key'       => 'parish_event_date',
					'order'          => 'ASC',
					'meta_query'     => array(
						array(
							'key'     => 'parish_event_date',
							'value'   => $today,
							'compare' => '>=',
							'type'    => 'DATE',
						),
					),
				)
			);

			$events = array();
			foreach ( $query->posts as $post ) {
				$date          = get_post_meta( $post->ID, 'parish_event_date', true );
				$event_church  = (int) get_post_meta( $post->ID, 'parish_event_church_id', true );
				$church_name   = '';
				$date_display  = $date ? wp_date( 'D j M', strtotime( $date ) ) : '';

				if ( $event_church > 0 ) {
					$church = get_post( $event_church );
					$church_name = $church ? html_entity_decode( $church->post_title, ENT_QUOTES, 'UTF-8' ) : '';
				}

				$events[] = array(
					'id'          => $post->ID,
					'title'       => $post->post_title,
					'date'        => $date,
					'date_display'=> $date_display,
					'time'        => get_post_meta( $post->ID, 'parish_event_time', true ),
					'location'    => get_post_meta( $post->ID, 'parish_event_location', true ),
					'church_name' => $church_name,
					'edit_url'    => get_edit_post_link( $post->ID, 'raw' ),
				);
			}

			if ( ! empty( $events ) ) {
				return $events;
			}
		}

		// Fallback to legacy JSON settings events.
		$events = json_decode( Parish_Core::get_setting( 'parish_events', '[]' ), true ) ?: array();
		$upcoming = array_filter( $events, fn( $e ) => ( $e['date'] ?? '' ) >= $today );
		usort( $upcoming, fn( $a, $b ) => strcmp( $a['date'] ?? '', $b['date'] ?? '' ) );

		$output = array();
		foreach ( array_slice( $upcoming, 0, 5 ) as $event ) {
			$date = $event['date'] ?? '';
			$output[] = array(
				'id'           => $event['id'] ?? '',
				'title'        => sanitize_text_field( $event['title'] ?? '' ),
				'date'         => $date,
				'date_display' => $date ? wp_date( 'D j M', strtotime( $date ) ) : '',
				'time'         => sanitize_text_field( $event['time'] ?? '' ),
				'location'     => sanitize_text_field( $event['location'] ?? '' ),
				'church_name'  => '',
				'edit_url'     => admin_url( 'admin.php?page=parish-events' ),
			);
		}

		return $output;
	}

	private function get_next_mass_times( int $days = 7, int $limit = 8 ): array {
		if ( ! Parish_Core::is_feature_enabled( 'mass_times' ) || ! class_exists( 'Parish_Schedule_Generator' ) ) {
			return array();
		}

		$days = max( 1, $days );
		$to_offset = $days - 1;
		$from = wp_date( 'Y-m-d' );
		$to   = wp_date( 'Y-m-d', strtotime( '+' . $to_offset . ' days' ) );

		$occurrences = Parish_Schedule_Generator::instance()->generate(
			$from,
			$to,
			array(
				'active_only' => true,
			)
		);

		if ( empty( $occurrences ) ) {
			return array();
		}

		$now = wp_date( 'Y-m-d H:i' );
		$upcoming = array_filter(
			$occurrences,
			static function ( array $occ ) use ( $now ): bool {
				return ! empty( $occ['datetime'] ) && $occ['datetime'] >= $now;
			}
		);

		// If all remaining occurrences are earlier today, still show nearest schedule items.
		if ( empty( $upcoming ) ) {
			$upcoming = $occurrences;
		}

		$output = array();
		foreach ( array_slice( array_values( $upcoming ), 0, $limit ) as $occ ) {
			$datetime = $occ['datetime'] ?? '';
			$timestamp = $datetime ? strtotime( $datetime ) : 0;
			$date = $occ['date'] ?? '';
			$time = $occ['time'] ?? '';

			$output[] = array(
				'post_id'          => (int) ( $occ['post_id'] ?? 0 ),
				'title'            => sanitize_text_field( $occ['title'] ?? '' ),
				'type_label'       => sanitize_text_field( $occ['type_label'] ?? __( 'Mass', 'parish-core' ) ),
				'date'             => $date,
				'time'             => $time,
				'datetime'         => $datetime,
				'date_display'     => $date ? wp_date( 'D j M', strtotime( $date ) ) : '',
				'time_display'     => $timestamp ? wp_date( 'g:i A', $timestamp ) : $time,
				'church_name'      => sanitize_text_field( $occ['church_name'] ?? __( 'All Churches', 'parish-core' ) ),
				'is_livestreamed'  => ! empty( $occ['is_livestreamed'] ),
			);
		}

		return $output;
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

		// Enhance rosary images with URLs.
		if ( ! empty( $settings['rosary_images'] ) ) {
			foreach ( $settings['rosary_images'] as $key => &$img ) {
				if ( ! empty( $img['id'] ) && empty( $img['url'] ) ) {
					$img['url'] = wp_get_attachment_url( $img['id'] );
				}
			}
		}

		// Enhance season images with URLs.
		if ( ! empty( $settings['season_images'] ) ) {
			foreach ( $settings['season_images'] as $key => &$img ) {
				if ( ! empty( $img['id'] ) && empty( $img['url'] ) ) {
					$img['url'] = wp_get_attachment_url( $img['id'] );
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
			'enabled'              => isset( $params['enabled'] ) ? (bool) $params['enabled'] : true,
			'autoplay'             => isset( $params['autoplay'] ) ? (bool) $params['autoplay'] : true,
			'autoplay_speed'       => isset( $params['autoplay_speed'] ) ? absint( $params['autoplay_speed'] ) : 5000,
			'transition_speed'     => isset( $params['transition_speed'] ) ? absint( $params['transition_speed'] ) : 1000,
			'show_arrows'          => isset( $params['show_arrows'] ) ? (bool) $params['show_arrows'] : true,
			'show_dots'            => isset( $params['show_dots'] ) ? (bool) $params['show_dots'] : true,
			'pause_on_hover'       => isset( $params['pause_on_hover'] ) ? (bool) $params['pause_on_hover'] : true,
			'height_desktop'       => isset( $params['height_desktop'] ) ? absint( $params['height_desktop'] ) : 700,
			'height_tablet'        => isset( $params['height_tablet'] ) ? absint( $params['height_tablet'] ) : 500,
			'height_mobile'        => isset( $params['height_mobile'] ) ? absint( $params['height_mobile'] ) : 400,
			'overlay_color'        => isset( $params['overlay_color'] ) ? sanitize_hex_color( $params['overlay_color'] ) : '#4A8391',
			'overlay_opacity'      => isset( $params['overlay_opacity'] ) ? floatval( $params['overlay_opacity'] ) : 0.7,
			'overlay_gradient'     => isset( $params['overlay_gradient'] ) ? (bool) $params['overlay_gradient'] : true,
			'use_liturgical_color' => isset( $params['use_liturgical_color'] ) ? (bool) $params['use_liturgical_color'] : false,
			'cta_color'            => isset( $params['cta_color'] ) ? sanitize_hex_color( $params['cta_color'] ) : '#d97706',
			'cta_hover_color'      => isset( $params['cta_hover_color'] ) ? sanitize_hex_color( $params['cta_hover_color'] ) : '#b45309',
			'rosary_images'        => array(),
			'season_images'        => array(),
			'slides'               => array(),
		);

		// Sanitize rosary images.
		if ( ! empty( $params['rosary_images'] ) && is_array( $params['rosary_images'] ) ) {
			$mysteries = array( 'Joyful', 'Sorrowful', 'Glorious', 'Luminous' );
			foreach ( $mysteries as $mystery ) {
				if ( isset( $params['rosary_images'][ $mystery ] ) ) {
					$sanitized['rosary_images'][ $mystery ] = array(
						'id'  => absint( $params['rosary_images'][ $mystery ]['id'] ?? 0 ),
						'url' => esc_url_raw( $params['rosary_images'][ $mystery ]['url'] ?? '' ),
					);
				} else {
					$sanitized['rosary_images'][ $mystery ] = array( 'id' => 0, 'url' => '' );
				}
			}
		}

		// Sanitize season images.
		if ( ! empty( $params['season_images'] ) && is_array( $params['season_images'] ) ) {
			$seasons = array( 'Advent', 'Christmas', 'Lent', 'Easter', 'Ordinary Time' );
			foreach ( $seasons as $season ) {
				if ( isset( $params['season_images'][ $season ] ) ) {
					$sanitized['season_images'][ $season ] = array(
						'id'  => absint( $params['season_images'][ $season ]['id'] ?? 0 ),
						'url' => esc_url_raw( $params['season_images'][ $season ]['url'] ?? '' ),
					);
				} else {
					$sanitized['season_images'][ $season ] = array( 'id' => 0, 'url' => '' );
				}
			}
		}

		// Sanitize slides.
		if ( ! empty( $params['slides'] ) && is_array( $params['slides'] ) ) {
			foreach ( $params['slides'] as $slide ) {
				$sanitized_slide = array(
					'id'             => sanitize_text_field( $slide['id'] ?? '' ),
					'type'           => in_array( $slide['type'] ?? '', array( 'manual', 'dynamic' ), true ) ? $slide['type'] : 'manual',
					'enabled'        => isset( $slide['enabled'] ) ? (bool) $slide['enabled'] : true,
					'image_id'       => absint( $slide['image_id'] ?? 0 ),
					'image_url'      => esc_url_raw( $slide['image_url'] ?? '' ),
					'image_fit'      => in_array( $slide['image_fit'] ?? '', array( 'cover', 'contain', 'fill' ), true ) ? $slide['image_fit'] : 'cover',
					'image_position' => in_array( $slide['image_position'] ?? '', array( 'center', 'top', 'bottom', 'left', 'right' ), true ) ? $slide['image_position'] : 'center',
					'display_mode'   => in_array( $slide['display_mode'] ?? '', array( 'full', 'title', 'image' ), true ) ? $slide['display_mode'] : 'full',
					'text_align'     => in_array( $slide['text_align'] ?? '', array( 'left', 'center', 'right' ), true ) ? $slide['text_align'] : 'left',
					'cta_text'       => sanitize_text_field( $slide['cta_text'] ?? '' ),
					'cta_link'       => esc_url_raw( $slide['cta_link'] ?? '' ),
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
				'category'    => $source['category'] ?? 'content',
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
		$s            = Parish_Core::get_settings();
		$menu_options = $this->normalize_menu_options( $s['menu_options'] ?? array() );

		return rest_ensure_response( array(
			'enable_death_notices' => (bool) ( $s['enable_death_notices'] ?? true ),
			'enable_baptism_notices' => (bool) ( $s['enable_baptism_notices'] ?? true ),
			'enable_wedding_notices' => (bool) ( $s['enable_wedding_notices'] ?? true ),
			'enable_mass_times' => (bool) ( $s['enable_mass_times'] ?? true ),
			'enable_rosary' => (bool) ( $s['enable_rosary'] ?? true ),
			'enable_churches' => (bool) ( $s['enable_churches'] ?? true ),
			'enable_schools' => (bool) ( $s['enable_schools'] ?? true ),
			'enable_cemeteries' => (bool) ( $s['enable_cemeteries'] ?? true ),
			'enable_groups' => (bool) ( $s['enable_groups'] ?? true ),
			'enable_newsletters' => (bool) ( $s['enable_newsletters'] ?? true ),
			'enable_news' => (bool) ( $s['enable_news'] ?? true ),
			'enable_gallery' => (bool) ( $s['enable_gallery'] ?? true ),
			'enable_reflections' => (bool) ( $s['enable_reflections'] ?? true ),
			'enable_prayers' => (bool) ( $s['enable_prayers'] ?? true ),
			'enable_events' => (bool) ( $s['enable_events'] ?? true ),
			'enable_liturgical' => (bool) ( $s['enable_liturgical'] ?? true ),
			'enable_slider' => (bool) ( $s['enable_slider'] ?? true ),
			'readings_api_key' => $this->mask_api_key( $s['readings_api_key'] ?? '' ),
			'admin_colors_enabled' => (bool) ( $s['admin_colors_enabled'] ?? false ),
			'admin_color_menu_text' => $s['admin_color_menu_text'] ?? '#ffffff',
			'admin_color_base_menu' => $s['admin_color_base_menu'] ?? '#1d2327',
			'admin_color_highlight' => $s['admin_color_highlight'] ?? '#2271b1',
			'admin_color_notification' => $s['admin_color_notification'] ?? '#d63638',
			'admin_color_background' => $s['admin_color_background'] ?? '#f0f0f1',
			'admin_color_links' => $s['admin_color_links'] ?? '#2271b1',
			'admin_color_buttons' => $s['admin_color_buttons'] ?? '#2271b1',
			'admin_color_form_inputs' => $s['admin_color_form_inputs'] ?? '#2271b1',
			'menu_options' => $menu_options,
			'menu_roles' => $this->get_default_non_admin_role_labels(),
			'menu_order_labels' => $this->get_menu_order_labels(),
			'feast_days_sync_enabled' => (bool) ( $s['feast_days_sync_enabled'] ?? false ),
			'feast_days_months_ahead' => (int) ( $s['feast_days_months_ahead'] ?? 3 ),
		));
	}

	public function update_settings( \WP_REST_Request $request ): \WP_REST_Response {
		$params = $request->get_json_params();
		$toggles = array( 'enable_death_notices', 'enable_baptism_notices', 'enable_wedding_notices', 'enable_mass_times', 'enable_rosary', 'enable_churches', 'enable_schools', 'enable_cemeteries', 'enable_groups', 'enable_newsletters', 'enable_news', 'enable_gallery', 'enable_reflections', 'enable_prayers', 'enable_events', 'enable_liturgical', 'enable_slider', 'admin_colors_enabled', 'feast_days_sync_enabled' );
		$colors = array( 'admin_color_menu_text', 'admin_color_base_menu', 'admin_color_highlight', 'admin_color_notification', 'admin_color_background', 'admin_color_links', 'admin_color_buttons', 'admin_color_form_inputs' );
		$sanitized = array();
		foreach ( $toggles as $key ) { if ( isset( $params[ $key ] ) ) $sanitized[ $key ] = (bool) $params[ $key ]; }
		foreach ( $colors as $key ) { if ( isset( $params[ $key ] ) ) $sanitized[ $key ] = sanitize_hex_color( $params[ $key ] ) ?: '#1d2327'; }
		if ( isset( $params['readings_api_key'] ) ) $sanitized['readings_api_key'] = sanitize_text_field( $params['readings_api_key'] );
		if ( isset( $params['menu_options'] ) ) $sanitized['menu_options'] = $this->sanitize_menu_options( $params['menu_options'] );
		if ( isset( $params['feast_days_months_ahead'] ) ) $sanitized['feast_days_months_ahead'] = absint( $params['feast_days_months_ahead'] ) ?: 3;

		Parish_Core::update_settings( $sanitized );

		// Apply feast-day cron changes immediately when settings are saved.
		if ( class_exists( 'Parish_Feast_Days' ) ) {
			Parish_Feast_Days::instance()->schedule_sync();
		}

		return rest_ensure_response( array( 'success' => true, 'message' => __( 'Settings saved.', 'parish-core' ) ) );
	}

	// =========================================================================
	// EVENTS
	// =========================================================================
	public function get_events(): \WP_REST_Response {
		return rest_ensure_response( array(
			'events' => json_decode( Parish_Core::get_setting( 'parish_events', '[]' ), true ) ?: array(),
			'churches' => $this->get_churches_array(),
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

	/**
	 * Get events for calendar view (CPT-based).
	 */
	public function get_events_calendar( \WP_REST_Request $request ): \WP_REST_Response {
		$start      = sanitize_text_field( $request['start'] );
		$end        = sanitize_text_field( $request['end'] );
		$church_id  = isset( $request['church_id'] ) ? (int) $request['church_id'] : 0;
		$sacrament  = isset( $request['sacrament'] ) ? sanitize_text_field( $request['sacrament'] ) : '';
		$type       = isset( $request['type'] ) ? sanitize_text_field( $request['type'] ) : '';
		$featured   = isset( $request['featured'] ) ? rest_sanitize_boolean( $request['featured'] ) : null;

		$args = array(
			'post_type'      => 'parish_event',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'meta_query'     => array(
				array(
					'key'     => 'parish_event_date',
					'value'   => array( $start, $end ),
					'compare' => 'BETWEEN',
					'type'    => 'DATE',
				),
			),
			'orderby'        => 'meta_value',
			'meta_key'       => 'parish_event_date',
			'order'          => 'ASC',
		);

		// Filter by church.
		if ( $church_id > 0 ) {
			$args['meta_query'][] = array(
				'key'   => 'parish_event_church_id',
				'value' => $church_id,
			);
		}

		// Filter by featured.
		if ( null !== $featured ) {
			$args['meta_query'][] = array(
				'key'   => 'parish_event_featured',
				'value' => $featured ? '1' : '0',
			);
		}

		// Filter by taxonomy.
		if ( ! empty( $sacrament ) || ! empty( $type ) ) {
			$args['tax_query'] = array();

			if ( ! empty( $sacrament ) ) {
				$args['tax_query'][] = array(
					'taxonomy' => 'parish_sacrament',
					'field'    => 'slug',
					'terms'    => $sacrament,
				);
			}

			if ( ! empty( $type ) ) {
				$args['tax_query'][] = array(
					'taxonomy' => 'parish_event_type',
					'field'    => 'slug',
					'terms'    => $type,
				);
			}
		}

		$query  = new \WP_Query( $args );
		$events = array();

		foreach ( $query->posts as $post ) {
			$events[] = array(
				'id'                 => $post->ID,
				'title'              => $post->post_title,
				'description'        => $post->post_content,
				'excerpt'            => $post->post_excerpt,
				'date'               => get_post_meta( $post->ID, 'parish_event_date', true ),
				'time'               => get_post_meta( $post->ID, 'parish_event_time', true ),
				'end_time'           => get_post_meta( $post->ID, 'parish_event_end_time', true ),
				'location'           => get_post_meta( $post->ID, 'parish_event_location', true ),
				'church_id'          => (int) get_post_meta( $post->ID, 'parish_event_church_id', true ),
				'is_cemetery'        => (bool) get_post_meta( $post->ID, 'parish_event_is_cemetery', true ),
				'organizer'          => get_post_meta( $post->ID, 'parish_event_organizer', true ),
				'contact_email'      => get_post_meta( $post->ID, 'parish_event_contact_email', true ),
				'contact_phone'      => get_post_meta( $post->ID, 'parish_event_contact_phone', true ),
				'registration_url'   => get_post_meta( $post->ID, 'parish_event_registration_url', true ),
				'featured'           => (bool) get_post_meta( $post->ID, 'parish_event_featured', true ),
				'color'              => get_post_meta( $post->ID, 'parish_event_color', true ),
				'thumbnail'          => get_the_post_thumbnail_url( $post->ID, 'medium' ),
				'sacraments'         => wp_get_post_terms( $post->ID, 'parish_sacrament', array( 'fields' => 'names' ) ),
				'types'              => wp_get_post_terms( $post->ID, 'parish_event_type', array( 'fields' => 'names' ) ),
				'feast_days'         => wp_get_post_terms( $post->ID, 'parish_feast_day', array( 'fields' => 'names' ) ),
				'event_locations'    => wp_get_post_terms( $post->ID, 'parish_event_location', array( 'fields' => 'names' ) ),
			);
		}

		return rest_ensure_response( $events );
	}

	/**
	 * Get event taxonomies for filters.
	 */
	public function get_event_taxonomies( \WP_REST_Request $request ): \WP_REST_Response {
		return rest_ensure_response(
			array(
				'sacraments' => get_terms( array( 'taxonomy' => 'parish_sacrament', 'hide_empty' => false ) ),
				'types'      => get_terms( array( 'taxonomy' => 'parish_event_type', 'hide_empty' => false ) ),
				'locations'  => get_terms( array( 'taxonomy' => 'parish_event_location', 'hide_empty' => false ) ),
				'feast_days' => get_terms( array( 'taxonomy' => 'parish_feast_day', 'hide_empty' => false ) ),
			)
		);
	}

	/**
	 * Get events list for admin panel.
	 */
	public function get_events_list( \WP_REST_Request $request ): \WP_REST_Response {
		$filter       = sanitize_text_field( $request['filter'] ?? 'upcoming' );
		$church_id    = (int) ( $request['church'] ?? 0 );
		$sacrament_id = (int) ( $request['sacrament'] ?? 0 );
		$cemetery     = (bool) ( $request['cemetery'] ?? false );
		$today        = current_time( 'Y-m-d' );

		$args = array(
			'post_type'      => 'parish_event',
			'posts_per_page' => 100,
			'post_status'    => 'publish',
			'orderby'        => 'meta_value',
			'meta_key'       => 'parish_event_date',
			'order'          => 'ASC',
		);

		// Filter by date.
		if ( 'upcoming' === $filter ) {
			$args['meta_query'] = array(
				array(
					'key'     => 'parish_event_date',
					'value'   => $today,
					'compare' => '>=',
					'type'    => 'DATE',
				),
			);
		} elseif ( 'past' === $filter ) {
			$args['meta_query'] = array(
				array(
					'key'     => 'parish_event_date',
					'value'   => $today,
					'compare' => '<',
					'type'    => 'DATE',
				),
			);
			$args['order'] = 'DESC';
		}

		// Filter by church.
		if ( $church_id > 0 ) {
			if ( ! isset( $args['meta_query'] ) ) {
				$args['meta_query'] = array();
			}
			$args['meta_query'][] = array(
				'key'   => 'parish_event_church_id',
				'value' => $church_id,
			);
		}

		// Filter by cemetery.
		if ( $cemetery ) {
			if ( ! isset( $args['meta_query'] ) ) {
				$args['meta_query'] = array();
			}
			$args['meta_query'][] = array(
				'key'   => 'parish_event_is_cemetery',
				'value' => '1',
			);
		}

		// Filter by sacrament taxonomy.
		if ( $sacrament_id > 0 ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'parish_sacrament',
					'field'    => 'term_id',
					'terms'    => $sacrament_id,
				),
			);
		}

		$query  = new \WP_Query( $args );
		$events = array();

		foreach ( $query->posts as $post ) {
			$event_church_id = (int) get_post_meta( $post->ID, 'parish_event_church_id', true );
			$church_name     = '';
			if ( $event_church_id > 0 ) {
				$church_post = get_post( $event_church_id );
				$church_name = $church_post ? $church_post->post_title : '';
			}

			// Get sacrament terms.
			$sacrament_terms = wp_get_post_terms( $post->ID, 'parish_sacrament', array( 'fields' => 'all' ) );
			$sacrament_names = '';
			$sacrament_id    = 0;
			if ( is_array( $sacrament_terms ) && ! empty( $sacrament_terms ) ) {
				$sacrament_names = implode( ', ', wp_list_pluck( $sacrament_terms, 'name' ) );
				$sacrament_id    = $sacrament_terms[0]->term_id;
			}

			$events[] = array(
				'id'               => $post->ID,
				'title'            => $post->post_title,
				'description'      => $post->post_content,
				'date'             => get_post_meta( $post->ID, 'parish_event_date', true ),
				'time'             => get_post_meta( $post->ID, 'parish_event_time', true ),
				'location'         => get_post_meta( $post->ID, 'parish_event_location', true ),
				'registration_url' => get_post_meta( $post->ID, 'parish_event_registration_url', true ),
				'church_id'        => $event_church_id,
				'church_name'      => $church_name,
				'cemetery_id'      => (int) get_post_meta( $post->ID, 'parish_event_cemetery_id', true ),
				'featured'         => (bool) get_post_meta( $post->ID, 'parish_event_featured', true ),
				'is_cemetery'      => (bool) get_post_meta( $post->ID, 'parish_event_is_cemetery', true ),
				'sacrament'        => $sacrament_names,
				'sacrament_id'     => $sacrament_id,
			);
		}

		return rest_ensure_response( $events );
	}

	/**
	 * Delete all past events.
	 */
	public function delete_past_events( \WP_REST_Request $request ): \WP_REST_Response {
		$today = current_time( 'Y-m-d' );

		$args = array(
			'post_type'      => 'parish_event',
			'posts_per_page' => -1,
			'post_status'    => 'any',
			'meta_query'     => array(
				array(
					'key'     => 'parish_event_date',
					'value'   => $today,
					'compare' => '<',
					'type'    => 'DATE',
				),
			),
			'fields'         => 'ids',
		);

		$query   = new \WP_Query( $args );
		$deleted = 0;

		foreach ( $query->posts as $post_id ) {
			if ( wp_delete_post( $post_id, true ) ) {
				$deleted++;
			}
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => sprintf( __( 'Deleted %d past event(s).', 'parish-core' ), $deleted ),
				'count'   => $deleted,
			)
		);
	}

	// =========================================================================
	// CHURCHES
	// =========================================================================
	public function get_churches(): \WP_REST_Response {
		return rest_ensure_response( $this->get_churches_array() );
	}

	public function get_churches_list(): \WP_REST_Response {
		return rest_ensure_response( $this->get_churches_array() );
	}

	private function get_churches_array(): array {
		if ( ! post_type_exists( 'parish_church' ) ) {
			return array();
		}
		$churches = get_posts( array( 'post_type' => 'parish_church', 'posts_per_page' => -1, 'post_status' => 'publish', 'orderby' => 'title', 'order' => 'ASC' ) );
		return array_map( fn( $c ) => array( 'id' => $c->ID, 'title' => html_entity_decode( $c->post_title, ENT_QUOTES, 'UTF-8' ) ), $churches );
	}

	// =========================================================================
	// READINGS API
	// =========================================================================
	public function get_readings_status(): \WP_REST_Response {
		if ( ! class_exists( 'Parish_Readings' ) ) {
			return rest_ensure_response( array(
				'error'       => 'Readings module not available.',
				'endpoints'   => array(),
				'api_key_set' => false,
			) );
		}
		$readings = Parish_Readings::instance();
		return rest_ensure_response( array(
			'endpoints'   => $readings->get_endpoints_status(),
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

	/**
	 * Get all endpoint schedules.
	 *
	 * @return \WP_REST_Response
	 */
	public function get_readings_schedules(): \WP_REST_Response {
		if ( ! class_exists( 'Parish_Readings' ) ) {
			return rest_ensure_response( array( 'error' => 'Readings module not available.' ) );
		}

		$schedules = json_decode( Parish_Core::get_setting( 'readings_schedules', '{}' ), true ) ?: array();
		$readings  = Parish_Readings::instance();
		$endpoints = $readings->get_endpoints_status();

		return rest_ensure_response( array(
			'schedules' => $schedules,
			'endpoints' => $endpoints,
		) );
	}

	/**
	 * Update a single endpoint's schedule.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function update_endpoint_schedule( \WP_REST_Request $request ): \WP_REST_Response {
		$endpoint = sanitize_text_field( $request->get_param( 'endpoint' ) );
		$params   = $request->get_json_params() ?: $request->get_params();

		if ( empty( $endpoint ) ) {
			return rest_ensure_response( array(
				'success' => false,
				'message' => __( 'Invalid endpoint.', 'parish-core' ),
			) );
		}

		if ( class_exists( 'Parish_Readings' ) ) {
			$readings  = Parish_Readings::instance();
			$endpoints = $readings->get_endpoints();
			if ( ! isset( $endpoints[ $endpoint ] ) ) {
				return rest_ensure_response( array(
					'success' => false,
					'message' => __( 'Unknown readings endpoint.', 'parish-core' ),
				) );
			}
		}

		// Get current schedules.
		$schedules = json_decode( Parish_Core::get_setting( 'readings_schedules', '{}' ), true ) ?: array();

		// Build new schedule config.
		$config = array(
			'schedule' => sanitize_text_field( $params['schedule'] ?? 'daily_once' ),
		);

		if ( isset( $params['time'] ) ) {
			$config['time'] = sanitize_text_field( $params['time'] );
		}
		if ( isset( $params['times'] ) && is_array( $params['times'] ) ) {
			$config['times'] = array_map( 'sanitize_text_field', $params['times'] );
		}
		if ( isset( $params['start_time'] ) ) {
			$config['start_time'] = sanitize_text_field( $params['start_time'] );
		}

		// Update schedules.
		$schedules[ $endpoint ] = $config;

		// Save to settings.
		$settings                       = Parish_Core::get_settings();
		$settings['readings_schedules'] = wp_json_encode( $schedules );
		Parish_Core::update_settings( $settings );

		// Reschedule this endpoint.
		if ( class_exists( 'Parish_Readings' ) ) {
			$readings = Parish_Readings::instance();
			$readings->schedule_endpoint( $endpoint, $config );
		}

		return rest_ensure_response( array(
			'success' => true,
			'message' => __( 'Schedule updated successfully.', 'parish-core' ),
			'config'  => $config,
		) );
	}

	/**
	 * Manually fetch a single reading endpoint.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function fetch_single_reading( \WP_REST_Request $request ): \WP_REST_Response {
		if ( ! class_exists( 'Parish_Readings' ) ) {
			return rest_ensure_response( array(
				'success' => false,
				'message' => 'Readings module not available.',
			) );
		}

		$endpoint = sanitize_text_field( $request->get_param( 'endpoint' ) );
		$readings = Parish_Readings::instance();
		$result   = $readings->fetch_endpoint( $endpoint );

		return rest_ensure_response( array(
			'success' => ! empty( $result ),
			'message' => ! empty( $result ) ? __( 'Fetch successful.', 'parish-core' ) : __( 'Fetch failed.', 'parish-core' ),
			'data'    => $result,
		) );
	}

	/**
	 * Get available schedule options.
	 *
	 * @return \WP_REST_Response
	 */
	public function get_schedule_options(): \WP_REST_Response {
		$options = array(
			array(
				'value' => 'daily_once',
				'label' => __( 'Once Daily', 'parish-core' ),
			),
			array(
				'value' => 'daily_twice',
				'label' => __( 'Twice Daily', 'parish-core' ),
			),
			array(
				'value' => 'every_6_hours',
				'label' => __( 'Every 6 Hours', 'parish-core' ),
			),
			array(
				'value' => 'every_4_hours',
				'label' => __( 'Every 4 Hours', 'parish-core' ),
			),
		);

		return rest_ensure_response( $options );
	}

	/**
	 * Get cron status for readings API.
	 *
	 * @return \WP_REST_Response
	 */
	public function get_readings_cron_status(): \WP_REST_Response {
		if ( ! class_exists( 'Parish_Readings' ) ) {
			return rest_ensure_response( array(
				'error' => 'Readings module not available.',
			) );
		}

		$readings = Parish_Readings::instance();
		return rest_ensure_response( $readings->get_cron_status() );
	}

	/**
	 * Force reschedule all readings cron events.
	 *
	 * @return \WP_REST_Response
	 */
	public function reschedule_readings_cron(): \WP_REST_Response {
		if ( ! class_exists( 'Parish_Readings' ) ) {
			return rest_ensure_response( array(
				'success' => false,
				'message' => 'Readings module not available.',
			) );
		}

		$readings = Parish_Readings::instance();
		$results  = $readings->force_reschedule_cron();

		return rest_ensure_response( array(
			'success' => true,
			'message' => __( 'Cron events rescheduled successfully.', 'parish-core' ),
			'results' => $results,
		) );
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
	// SCHEDULE SYSTEM CALLBACKS
	// =========================================================================

	/**
	 * Get all schedule templates.
	 *
	 * @return \WP_REST_Response
	 */
	public function get_schedule_templates(): \WP_REST_Response {
		$templates = Parish_Core::get_setting( 'liturgical_schedules', array() );

		// Ensure it's an array.
		if ( ! is_array( $templates ) ) {
			$templates = array();
		}

		// Add church names to each template.
		foreach ( $templates as &$template ) {
			if ( ! empty( $template['church_id'] ) ) {
				$template['church_name'] = get_the_title( $template['church_id'] );
			}
		}

		return rest_ensure_response( array(
			'templates' => array_values( $templates ),
			'churches'  => $this->get_churches_array(),
		) );
	}

	/**
	 * Save a new schedule template.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function save_schedule_template( \WP_REST_Request $request ): \WP_REST_Response {
		$params = $request->get_json_params();

		if ( ! class_exists( 'Parish_Schedule_Generator' ) ) {
			return rest_ensure_response( array(
				'success' => false,
				'message' => __( 'Schedule system not available.', 'parish-core' ),
			) );
		}

		$generator = Parish_Schedule_Generator::instance();
		$template  = $this->sanitize_schedule_template( $params );

		// Generate ID if not provided.
		if ( empty( $template['id'] ) ) {
			$template['id'] = 'sched_' . wp_generate_password( 8, false, false );
		}

		$result = $generator->save_template( $template );

		if ( $result ) {
			return rest_ensure_response( array(
				'success'  => true,
				'message'  => __( 'Schedule template saved.', 'parish-core' ),
				'template' => $template,
			) );
		}

		return rest_ensure_response( array(
			'success' => false,
			'message' => __( 'Failed to save template.', 'parish-core' ),
		) );
	}

	/**
	 * Get a single schedule template.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_schedule_template( \WP_REST_Request $request ): \WP_REST_Response {
		$id        = $request->get_param( 'id' );
		$templates = Parish_Core::get_setting( 'liturgical_schedules', array() );

		foreach ( $templates as $template ) {
			if ( ( $template['id'] ?? '' ) === $id ) {
				if ( ! empty( $template['church_id'] ) ) {
					$template['church_name'] = get_the_title( $template['church_id'] );
				}
				return rest_ensure_response( $template );
			}
		}

		return new \WP_REST_Response( array( 'error' => 'Template not found.' ), 404 );
	}

	/**
	 * Update an existing schedule template.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function update_schedule_template( \WP_REST_Request $request ): \WP_REST_Response {
		$id        = $request->get_param( 'id' );
		$params    = $request->get_json_params();
		$templates = Parish_Core::get_setting( 'liturgical_schedules', array() );

		$found = false;
		foreach ( $templates as $index => $template ) {
			if ( ( $template['id'] ?? '' ) === $id ) {
				$updated              = $this->sanitize_schedule_template( $params );
				$updated['id']        = $id; // Preserve ID.
				$templates[ $index ]  = $updated;
				$found                = true;
				break;
			}
		}

		if ( ! $found ) {
			return new \WP_REST_Response( array( 'error' => 'Template not found.' ), 404 );
		}

		Parish_Core::update_settings( array(
			'liturgical_schedules' => $templates,
		) );

		return rest_ensure_response( array(
			'success' => true,
			'message' => __( 'Template updated.', 'parish-core' ),
		) );
	}

	/**
	 * Delete a schedule template.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function delete_schedule_template( \WP_REST_Request $request ): \WP_REST_Response {
		$id        = $request->get_param( 'id' );
		$templates = Parish_Core::get_setting( 'liturgical_schedules', array() );

		$filtered = array_filter( $templates, fn( $t ) => ( $t['id'] ?? '' ) !== $id );

		if ( count( $filtered ) === count( $templates ) ) {
			return new \WP_REST_Response( array( 'error' => 'Template not found.' ), 404 );
		}

		Parish_Core::update_settings( array(
			'liturgical_schedules' => array_values( $filtered ),
		) );

		return rest_ensure_response( array(
			'success' => true,
			'message' => __( 'Template deleted.', 'parish-core' ),
		) );
	}

	/**
	 * Get all schedule overrides.
	 *
	 * @return \WP_REST_Response
	 */
	public function get_schedule_overrides(): \WP_REST_Response {
		$overrides = Parish_Core::get_setting( 'schedule_overrides', array() );

		if ( ! is_array( $overrides ) ) {
			$overrides = array();
		}

		// Filter out expired overrides (older than 30 days).
		$cutoff    = gmdate( 'Y-m-d', strtotime( '-30 days' ) );
		$overrides = array_filter( $overrides, fn( $o ) => ( $o['date'] ?? '' ) >= $cutoff );

		return rest_ensure_response( array(
			'overrides' => array_values( $overrides ),
		) );
	}

	/**
	 * Add a schedule override.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function add_schedule_override( \WP_REST_Request $request ): \WP_REST_Response {
		$params = $request->get_json_params();

		if ( ! class_exists( 'Parish_Schedule_Generator' ) ) {
			return rest_ensure_response( array(
				'success' => false,
				'message' => __( 'Schedule system not available.', 'parish-core' ),
			) );
		}

		$generator = Parish_Schedule_Generator::instance();
		$override  = $this->sanitize_schedule_override( $params );

		// Generate ID if not provided.
		if ( empty( $override['id'] ) ) {
			$override['id'] = 'over_' . wp_generate_password( 8, false, false );
		}

		$result = $generator->add_override( $override );

		if ( $result ) {
			return rest_ensure_response( array(
				'success'  => true,
				'message'  => __( 'Override added.', 'parish-core' ),
				'override' => $override,
			) );
		}

		return rest_ensure_response( array(
			'success' => false,
			'message' => __( 'Failed to add override.', 'parish-core' ),
		) );
	}

	/**
	 * Delete a schedule override.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function delete_schedule_override( \WP_REST_Request $request ): \WP_REST_Response {
		$id        = $request->get_param( 'id' );
		$overrides = Parish_Core::get_setting( 'schedule_overrides', array() );

		$filtered = array_filter( $overrides, fn( $o ) => ( $o['id'] ?? '' ) !== $id );

		if ( count( $filtered ) === count( $overrides ) ) {
			return new \WP_REST_Response( array( 'error' => 'Override not found.' ), 404 );
		}

		Parish_Core::update_settings( array(
			'schedule_overrides' => array_values( $filtered ),
		) );

		return rest_ensure_response( array(
			'success' => true,
			'message' => __( 'Override deleted.', 'parish-core' ),
		) );
	}

	/**
	 * Get generated schedule for a date range.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_generated_schedule( \WP_REST_Request $request ): \WP_REST_Response {
		if ( ! class_exists( 'Parish_Schedule_Generator' ) ) {
			return rest_ensure_response( array( 'error' => 'Schedule system not available.' ) );
		}

		$start      = $request->get_param( 'start' ) ?: gmdate( 'Y-m-d' );
		$end        = $request->get_param( 'end' ) ?: gmdate( 'Y-m-d', strtotime( '+7 days' ) );
		$church_id  = $request->get_param( 'church_id' );
		$event_type = $request->get_param( 'event_type' );

		$filters = array();
		if ( $church_id ) {
			$filters['church_id'] = absint( $church_id );
		}
		if ( $event_type ) {
			$filters['event_type'] = sanitize_text_field( $event_type );
		}

		$generator = Parish_Schedule_Generator::instance();
		$schedule  = $generator->generate( $start, $end, $filters );

		return rest_ensure_response( array(
			'start'    => $start,
			'end'      => $end,
			'schedule' => $schedule,
		) );
	}

	/**
	 * Get today's schedule.
	 *
	 * @return \WP_REST_Response
	 */
	public function get_today_schedule(): \WP_REST_Response {
		if ( ! class_exists( 'Parish_Schedule_Generator' ) ) {
			return rest_ensure_response( array( 'error' => 'Schedule system not available.' ) );
		}

		$generator = Parish_Schedule_Generator::instance();
		$schedule  = $generator->generate_today();

		// Get feast day info.
		$feast_info = null;
		if ( class_exists( 'Parish_Feast_Day_Service' ) ) {
			$feast_service = new Parish_Feast_Day_Service();
			$feast_info    = $feast_service->get_feast_day( gmdate( 'Y-m-d' ) );
		}

		return rest_ensure_response( array(
			'date'      => gmdate( 'Y-m-d' ),
			'day_name'  => gmdate( 'l' ),
			'feast'     => $feast_info,
			'schedule'  => $schedule,
		) );
	}

	/**
	 * Get this week's schedule.
	 *
	 * @return \WP_REST_Response
	 */
	public function get_week_schedule(): \WP_REST_Response {
		if ( ! class_exists( 'Parish_Schedule_Generator' ) ) {
			return rest_ensure_response( array( 'error' => 'Schedule system not available.' ) );
		}

		$generator = Parish_Schedule_Generator::instance();
		$schedule  = $generator->generate_week();

		// Group by day.
		$by_day = array();
		foreach ( $schedule as $event ) {
			$date = $event['date'] ?? '';
			if ( ! isset( $by_day[ $date ] ) ) {
				$by_day[ $date ] = array(
					'date'     => $date,
					'day_name' => gmdate( 'l', strtotime( $date ) ),
					'events'   => array(),
				);
			}
			$by_day[ $date ]['events'][] = $event;
		}

		return rest_ensure_response( array(
			'start'    => gmdate( 'Y-m-d' ),
			'end'      => gmdate( 'Y-m-d', strtotime( '+6 days' ) ),
			'days'     => array_values( $by_day ),
		) );
	}

	/**
	 * Get available event types.
	 *
	 * @return \WP_REST_Response
	 */
	public function get_event_types(): \WP_REST_Response {
		if ( ! class_exists( 'Parish_Schedule_Generator' ) ) {
			return rest_ensure_response( array() );
		}

		return rest_ensure_response( Parish_Schedule_Generator::EVENT_TYPES );
	}

	/**
	 * Sanitize a schedule template.
	 *
	 * @param array $params Raw parameters.
	 * @return array Sanitized template.
	 */
	private function sanitize_schedule_template( array $params ): array {
		$template = array(
			'id'         => sanitize_text_field( $params['id'] ?? '' ),
			'church_id'  => absint( $params['church_id'] ?? 0 ),
			'event_type' => sanitize_text_field( $params['event_type'] ?? 'mass' ),
			'title'      => sanitize_text_field( $params['title'] ?? '' ),
			'active'     => (bool) ( $params['active'] ?? true ),
			'created_at' => sanitize_text_field( $params['created_at'] ?? gmdate( 'Y-m-d' ) ),
		);

		// Recurrence settings.
		if ( ! empty( $params['recurrence'] ) && is_array( $params['recurrence'] ) ) {
			$rec = $params['recurrence'];
			$template['recurrence'] = array(
				'type'      => sanitize_text_field( $rec['type'] ?? 'weekly' ),
				'time'      => sanitize_text_field( $rec['time'] ?? '' ),
				'end_time'  => sanitize_text_field( $rec['end_time'] ?? '' ),
			);

			// Type-specific fields.
			if ( ! empty( $rec['days'] ) && is_array( $rec['days'] ) ) {
				$template['recurrence']['days'] = array_map( 'sanitize_text_field', $rec['days'] );
			}
			if ( ! empty( $rec['day_of_month'] ) ) {
				$template['recurrence']['day_of_month'] = absint( $rec['day_of_month'] );
			}
			if ( ! empty( $rec['position'] ) ) {
				$template['recurrence']['position'] = sanitize_text_field( $rec['position'] );
			}
			if ( ! empty( $rec['day_of_week'] ) ) {
				$template['recurrence']['day_of_week'] = sanitize_text_field( $rec['day_of_week'] );
			}
			if ( ! empty( $rec['month'] ) ) {
				$template['recurrence']['month'] = absint( $rec['month'] );
			}
			if ( ! empty( $rec['day'] ) ) {
				$template['recurrence']['day'] = absint( $rec['day'] );
			}
			if ( ! empty( $rec['feast'] ) ) {
				$template['recurrence']['feast'] = sanitize_text_field( $rec['feast'] );
			}
		}

		// Liturgical settings.
		if ( ! empty( $params['liturgical'] ) && is_array( $params['liturgical'] ) ) {
			$lit = $params['liturgical'];
			$template['liturgical'] = array(
				'rite'     => sanitize_text_field( $lit['rite'] ?? 'roman' ),
				'language' => sanitize_text_field( $lit['language'] ?? 'english' ),
				'form'     => sanitize_text_field( $lit['form'] ?? 'ordinary' ),
			);
		}

		// Livestream settings.
		if ( ! empty( $params['livestream'] ) && is_array( $params['livestream'] ) ) {
			$ls = $params['livestream'];
			$template['livestream'] = array(
				'enabled' => (bool) ( $ls['enabled'] ?? false ),
				'url'     => esc_url_raw( $ls['url'] ?? '' ),
			);
		}

		// Notes.
		if ( ! empty( $params['notes'] ) ) {
			$template['notes'] = sanitize_textarea_field( $params['notes'] );
		}

		return $template;
	}

	/**
	 * Sanitize a schedule override.
	 *
	 * @param array $params Raw parameters.
	 * @return array Sanitized override.
	 */
	private function sanitize_schedule_override( array $params ): array {
		$override = array(
			'id'          => sanitize_text_field( $params['id'] ?? '' ),
			'schedule_id' => sanitize_text_field( $params['schedule_id'] ?? '' ),
			'date'        => sanitize_text_field( $params['date'] ?? '' ),
			'type'        => sanitize_text_field( $params['type'] ?? 'cancellation' ),
		);

		// Validate type.
		if ( ! in_array( $override['type'], array( 'cancellation', 'time_change', 'addition' ), true ) ) {
			$override['type'] = 'cancellation';
		}

		// Type-specific fields.
		if ( $override['type'] === 'time_change' ) {
			$override['new_time']     = sanitize_text_field( $params['new_time'] ?? '' );
			$override['new_end_time'] = sanitize_text_field( $params['new_end_time'] ?? '' );
		}

		if ( $override['type'] === 'addition' ) {
			$override['church_id']  = absint( $params['church_id'] ?? 0 );
			$override['event_type'] = sanitize_text_field( $params['event_type'] ?? 'mass' );
			$override['title']      = sanitize_text_field( $params['title'] ?? '' );
			$override['time']       = sanitize_text_field( $params['time'] ?? '' );
			$override['end_time']   = sanitize_text_field( $params['end_time'] ?? '' );
		}

		// Reason for any override.
		if ( ! empty( $params['reason'] ) ) {
			$override['reason'] = sanitize_textarea_field( $params['reason'] );
		}

		return $override;
	}

	// =========================================================================
	// MASS TIMES CPT CALLBACKS
	// =========================================================================

	/**
	 * Get all Mass Time posts.
	 *
	 * @return \WP_REST_Response
	 */
	public function get_mass_times(): \WP_REST_Response {
		if ( ! Parish_Core::is_feature_enabled( 'mass_times' ) ) {
			return rest_ensure_response( array( 'error' => 'Mass Times feature is disabled.' ) );
		}

		$posts = get_posts( array(
			'post_type'      => 'parish_mass_time',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'orderby'        => 'meta_value',
			'meta_key'       => 'parish_mass_time_start_datetime',
			'order'          => 'ASC',
		) );

		$mass_times = array();
		foreach ( $posts as $post ) {
			$mass_times[] = $this->format_mass_time_post( $post );
		}

		return rest_ensure_response( array(
			'mass_times'  => $mass_times,
			'churches'    => $this->get_churches_array(),
			'event_types' => class_exists( 'Parish_Schedule_Generator' )
				? Parish_Schedule_Generator::instance()->get_event_types()
				: array(),
		) );
	}

	/**
	 * Get a single Mass Time post.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_mass_time( \WP_REST_Request $request ): \WP_REST_Response {
		$id   = absint( $request->get_param( 'id' ) );
		$post = get_post( $id );

		if ( ! $post || 'parish_mass_time' !== $post->post_type ) {
			return new \WP_REST_Response( array( 'error' => 'Mass Time not found.' ), 404 );
		}

		return rest_ensure_response( $this->format_mass_time_post( $post ) );
	}

	/**
	 * Create a new Mass Time post.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function create_mass_time( \WP_REST_Request $request ): \WP_REST_Response {
		if ( ! Parish_Core::is_feature_enabled( 'mass_times' ) ) {
			return rest_ensure_response( array( 'success' => false, 'message' => 'Mass Times feature is disabled.' ) );
		}

		$params = $request->get_json_params();
		$meta   = $this->sanitize_mass_time_meta( $params );

		$post_data = array(
			'post_type'   => 'parish_mass_time',
			'post_status' => 'publish',
			'post_title'  => sanitize_text_field( $params['title'] ?? '' ),
			'meta_input'  => $meta,
		);

		$post_id = wp_insert_post( $post_data, true );

		if ( is_wp_error( $post_id ) ) {
			return rest_ensure_response( array(
				'success' => false,
				'message' => $post_id->get_error_message(),
			) );
		}

		// Clear schedule cache.
		if ( class_exists( 'Parish_Schedule_Generator' ) ) {
			Parish_Schedule_Generator::instance()->clear_cache( $post_id );
		}

		return rest_ensure_response( array(
			'success'   => true,
			'message'   => __( 'Mass Time created.', 'parish-core' ),
			'mass_time' => $this->format_mass_time_post( get_post( $post_id ) ),
		) );
	}

	/**
	 * Update an existing Mass Time post.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function update_mass_time( \WP_REST_Request $request ): \WP_REST_Response {
		$id   = absint( $request->get_param( 'id' ) );
		$post = get_post( $id );

		if ( ! $post || 'parish_mass_time' !== $post->post_type ) {
			return new \WP_REST_Response( array( 'error' => 'Mass Time not found.' ), 404 );
		}

		$params = $request->get_json_params();
		$meta   = $this->sanitize_mass_time_meta( $params );

		// Update title if provided.
		if ( isset( $params['title'] ) ) {
			wp_update_post( array(
				'ID'         => $id,
				'post_title' => sanitize_text_field( $params['title'] ),
			) );
		}

		// Update meta fields.
		foreach ( $meta as $key => $value ) {
			update_post_meta( $id, $key, $value );
		}

		// Clear schedule cache.
		if ( class_exists( 'Parish_Schedule_Generator' ) ) {
			Parish_Schedule_Generator::instance()->clear_cache( $id );
		}

		return rest_ensure_response( array(
			'success'   => true,
			'message'   => __( 'Mass Time updated.', 'parish-core' ),
			'mass_time' => $this->format_mass_time_post( get_post( $id ) ),
		) );
	}

	/**
	 * Delete a Mass Time post.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function delete_mass_time( \WP_REST_Request $request ): \WP_REST_Response {
		$id   = absint( $request->get_param( 'id' ) );
		$post = get_post( $id );

		if ( ! $post || 'parish_mass_time' !== $post->post_type ) {
			return new \WP_REST_Response( array( 'error' => 'Mass Time not found.' ), 404 );
		}

		// Clear schedule cache BEFORE delete (since clear_cache checks post type).
		if ( class_exists( 'Parish_Schedule_Generator' ) ) {
			Parish_Schedule_Generator::instance()->clear_cache( $id );
		}

		$result = wp_delete_post( $id, true );

		if ( ! $result ) {
			return rest_ensure_response( array(
				'success' => false,
				'message' => __( 'Failed to delete Mass Time.', 'parish-core' ),
			) );
		}

		return rest_ensure_response( array(
			'success' => true,
			'message' => __( 'Mass Time deleted.', 'parish-core' ),
		) );
	}

	/**
	 * Cancel a single occurrence of a recurring Mass Time.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function cancel_mass_occurrence( \WP_REST_Request $request ): \WP_REST_Response {
		$id   = absint( $request->get_param( 'id' ) );
		$date = sanitize_text_field( $request->get_param( 'date' ) );

		// Validate post exists and is a mass time.
		$post = get_post( $id );
		if ( ! $post || 'parish_mass_time' !== $post->post_type ) {
			return new \WP_REST_Response( array( 'error' => 'Mass Time not found.' ), 404 );
		}

		// Validate date format.
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			return new \WP_REST_Response( array( 'error' => 'Invalid date format. Use Y-m-d.' ), 400 );
		}

		// Cancel the occurrence.
		if ( ! class_exists( 'Parish_Schedule_Generator' ) ) {
			return new \WP_REST_Response( array( 'error' => 'Schedule generator not available.' ), 500 );
		}

		$success = Parish_Schedule_Generator::cancel_occurrence( $id, $date );

		if ( ! $success ) {
			return rest_ensure_response( array(
				'success' => false,
				'message' => __( 'Failed to cancel occurrence. It may already be cancelled.', 'parish-core' ),
			) );
		}

		return rest_ensure_response( array(
			'success' => true,
			'message' => __( 'Occurrence cancelled successfully.', 'parish-core' ),
		) );
	}

	/**
	 * Cancel an entire recurring series.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function cancel_mass_series( \WP_REST_Request $request ): \WP_REST_Response {
		$id = absint( $request->get_param( 'id' ) );

		// Validate post exists and is a mass time.
		$post = get_post( $id );
		if ( ! $post || 'parish_mass_time' !== $post->post_type ) {
			return new \WP_REST_Response( array( 'error' => 'Mass Time not found.' ), 404 );
		}

		// Cancel the series.
		if ( ! class_exists( 'Parish_Schedule_Generator' ) ) {
			return new \WP_REST_Response( array( 'error' => 'Schedule generator not available.' ), 500 );
		}

		$success = Parish_Schedule_Generator::cancel_series( $id );

		if ( ! $success ) {
			return rest_ensure_response( array(
				'success' => false,
				'message' => __( 'Failed to cancel series.', 'parish-core' ),
			) );
		}

		return rest_ensure_response( array(
			'success' => true,
			'message' => __( 'Series cancelled successfully.', 'parish-core' ),
		) );
	}

	/**
	 * Restore a previously cancelled occurrence.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function restore_mass_occurrence( \WP_REST_Request $request ): \WP_REST_Response {
		$id   = absint( $request->get_param( 'id' ) );
		$date = sanitize_text_field( $request->get_param( 'date' ) );

		// Validate post exists and is a mass time.
		$post = get_post( $id );
		if ( ! $post || 'parish_mass_time' !== $post->post_type ) {
			return new \WP_REST_Response( array( 'error' => 'Mass Time not found.' ), 404 );
		}

		// Validate date format.
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			return new \WP_REST_Response( array( 'error' => 'Invalid date format. Use Y-m-d.' ), 400 );
		}

		// Restore the occurrence.
		if ( ! class_exists( 'Parish_Schedule_Generator' ) ) {
			return new \WP_REST_Response( array( 'error' => 'Schedule generator not available.' ), 500 );
		}

		$success = Parish_Schedule_Generator::restore_occurrence( $id, $date );

		if ( ! $success ) {
			return rest_ensure_response( array(
				'success' => false,
				'message' => __( 'Failed to restore occurrence. It may not be cancelled.', 'parish-core' ),
			) );
		}

		return rest_ensure_response( array(
			'success' => true,
			'message' => __( 'Occurrence restored successfully.', 'parish-core' ),
		) );
	}

	/**
	 * Get Mass Time occurrences for a date range.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_mass_time_occurrences( \WP_REST_Request $request ): \WP_REST_Response {
		if ( ! Parish_Core::is_feature_enabled( 'mass_times' ) ) {
			return rest_ensure_response( array( 'error' => 'Mass Times feature is disabled.' ) );
		}

		if ( ! class_exists( 'Parish_Schedule_Generator' ) ) {
			return rest_ensure_response( array( 'error' => 'Schedule generator not available.' ) );
		}

		$from        = $request->get_param( 'from' ) ?: wp_date( 'Y-m-d' );
		$to          = $request->get_param( 'to' ) ?: wp_date( 'Y-m-d', strtotime( '+6 days' ) );
		$church_id   = $request->get_param( 'church_id' );
		$type        = $request->get_param( 'type' );
		$active_only = $request->get_param( 'active_only' ) !== false;

		$filters = array( 'active_only' => $active_only );
		if ( null !== $church_id ) {
			$filters['church_id'] = absint( $church_id );
		}
		if ( $type ) {
			$filters['type'] = sanitize_text_field( $type );
		}

		$generator   = Parish_Schedule_Generator::instance();
		$occurrences = $generator->generate( $from, $to, $filters );

		// Group by date for easier frontend rendering.
		$by_date = array();
		foreach ( $occurrences as $occ ) {
			$date = $occ['date'];
			if ( ! isset( $by_date[ $date ] ) ) {
				$by_date[ $date ] = array(
					'date'        => $date,
					'day_name'    => wp_date( 'l', strtotime( $date ) ),
					'formatted'   => wp_date( 'l, j F Y', strtotime( $date ) ),
					'is_today'    => $date === wp_date( 'Y-m-d' ),
					'occurrences' => array(),
				);
			}
			$by_date[ $date ]['occurrences'][] = $occ;
		}

		return rest_ensure_response( array(
			'from'  => $from,
			'to'    => $to,
			'days'  => array_values( $by_date ),
			'total' => count( $occurrences ),
		) );
	}

	/**
	 * Format a Mass Time post for API response.
	 *
	 * @param \WP_Post $post The post object.
	 * @return array Formatted mass time data.
	 */
	private function format_mass_time_post( \WP_Post $post ): array {
		$church_id = absint( get_post_meta( $post->ID, 'parish_mass_time_church_id', true ) );
		$church_name = __( 'All Churches', 'parish-core' );
		$church_color = '';
		if ( $church_id > 0 ) {
			$church = get_post( $church_id );
			if ( $church ) {
				$church_name = html_entity_decode( $church->post_title, ENT_QUOTES, 'UTF-8' );
				$church_color = get_post_meta( $church_id, 'parish_color', true );
			}
		}

		$recurrence_raw = get_post_meta( $post->ID, 'parish_mass_time_recurrence', true );
		$exception_dates_raw = get_post_meta( $post->ID, 'parish_mass_time_exception_dates', true );

		// Use the global sanitize functions to ensure consistent data structure
		// This also validates that days contains only valid weekday names
		$recurrence = parish_sanitize_recurrence( $recurrence_raw );
		$exception_dates = parish_sanitize_exception_dates( $exception_dates_raw );

		return array(
			'id'               => $post->ID,
			'title'            => $post->post_title,
			'church_id'        => $church_id,
			'church_name'      => $church_name,
			'church_color'     => $church_color,
			'liturgical_type'  => get_post_meta( $post->ID, 'parish_mass_time_liturgical_type', true ) ?: 'mass',
			'start_datetime'   => get_post_meta( $post->ID, 'parish_mass_time_start_datetime', true ),
			'duration_minutes' => absint( get_post_meta( $post->ID, 'parish_mass_time_duration_minutes', true ) ) ?: 60,
			'is_active'        => (bool) get_post_meta( $post->ID, 'parish_mass_time_is_active', true ),
			'is_special_event' => (bool) get_post_meta( $post->ID, 'parish_mass_time_is_special_event', true ),
			'is_recurring'     => (bool) get_post_meta( $post->ID, 'parish_mass_time_is_recurring', true ),
			'recurrence'       => $recurrence,
			'exception_dates'  => $exception_dates,
			'is_livestreamed'  => (bool) get_post_meta( $post->ID, 'parish_mass_time_is_livestreamed', true ),
			'livestream_url'   => get_post_meta( $post->ID, 'parish_mass_time_livestream_url', true ),
			'livestream_embed' => get_post_meta( $post->ID, 'parish_mass_time_livestream_embed', true ),
			'notes'            => get_post_meta( $post->ID, 'parish_mass_time_notes', true ),
		);
	}

	/**
	 * Sanitize Mass Time meta data from request.
	 *
	 * @param array $params Request parameters.
	 * @return array Sanitized meta array with prefixed keys.
	 */
	private function sanitize_mass_time_meta( array $params ): array {
		$meta = array();
		$prefix = 'parish_mass_time_';

		if ( isset( $params['church_id'] ) ) {
			$meta[ $prefix . 'church_id' ] = absint( $params['church_id'] );
		}

		if ( isset( $params['liturgical_type'] ) ) {
			$meta[ $prefix . 'liturgical_type' ] = sanitize_text_field( $params['liturgical_type'] );
		}

		if ( isset( $params['start_datetime'] ) ) {
			$meta[ $prefix . 'start_datetime' ] = sanitize_text_field( $params['start_datetime'] );
		}

		if ( isset( $params['duration_minutes'] ) ) {
			$meta[ $prefix . 'duration_minutes' ] = absint( $params['duration_minutes'] );
		}

		if ( isset( $params['is_active'] ) ) {
			$meta[ $prefix . 'is_active' ] = (bool) $params['is_active'];
		}

		if ( isset( $params['is_special_event'] ) ) {
			$meta[ $prefix . 'is_special_event' ] = (bool) $params['is_special_event'];
		}

		if ( isset( $params['is_recurring'] ) ) {
			$meta[ $prefix . 'is_recurring' ] = (bool) $params['is_recurring'];
		}

		// Use the global sanitize function for recurrence - ensures proper validation
		if ( isset( $params['recurrence'] ) ) {
			$meta[ $prefix . 'recurrence' ] = parish_sanitize_recurrence( $params['recurrence'] );
		}

		// Use the global sanitize function for exception dates
		if ( isset( $params['exception_dates'] ) ) {
			$meta[ $prefix . 'exception_dates' ] = parish_sanitize_exception_dates( $params['exception_dates'] );
		}

		if ( isset( $params['is_livestreamed'] ) ) {
			$meta[ $prefix . 'is_livestreamed' ] = (bool) $params['is_livestreamed'];
		}

		if ( isset( $params['livestream_url'] ) ) {
			$meta[ $prefix . 'livestream_url' ] = esc_url_raw( $params['livestream_url'] );
		} elseif ( isset( $params['is_livestreamed'] ) && $params['is_livestreamed'] ) {
			// If livestream is enabled but no URL provided, use default.
			$default_url = Parish_Core::get_setting( 'default_livestream_url', '' );
			if ( ! empty( $default_url ) ) {
				$meta[ $prefix . 'livestream_url' ] = esc_url_raw( $default_url );
			}
		}

		if ( isset( $params['livestream_embed'] ) ) {
			$meta[ $prefix . 'livestream_embed' ] = parish_sanitize_embed_html( $params['livestream_embed'] );
		}

		if ( isset( $params['notes'] ) ) {
			$meta[ $prefix . 'notes' ] = wp_kses_post( $params['notes'] );
		}

		return $meta;
	}

	// =========================================================================
	// HELPERS
	// =========================================================================
	private function get_image_url( int $id ): string {
		if ( ! $id ) return '';
		return wp_get_attachment_url( $id ) ?: '';
	}

	/**
	 * Mask an API key for display, showing only first 4 and last 4 characters.
	 * Returns empty string if no key is set.
	 *
	 * @param string $key The API key to mask.
	 * @return string Masked key or empty string.
	 */
	private function mask_api_key( string $key ): string {
		if ( empty( $key ) ) {
			return '';
		}
		$length = strlen( $key );
		if ( $length <= 8 ) {
			return str_repeat( '*', $length );
		}
		return substr( $key, 0, 4 ) . str_repeat( '*', $length - 8 ) . substr( $key, -4 );
	}
}
