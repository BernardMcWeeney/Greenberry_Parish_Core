<?php
/**
 * REST API for Parish Event Times.
 *
 * Provides CRUD endpoints for managing Mass times, confessions, and other services.
 * Also provides schedule generation endpoints for frontend display.
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Event Time REST API class.
 */
class Parish_Event_Time_REST {

	/**
	 * Singleton instance.
	 *
	 * @var Parish_Event_Time_REST|null
	 */
	private static ?Parish_Event_Time_REST $instance = null;

	/**
	 * REST namespace.
	 *
	 * @var string
	 */
	private string $namespace = 'parish/v1';

	/**
	 * Get singleton instance.
	 *
	 * @return Parish_Event_Time_REST
	 */
	public static function instance(): Parish_Event_Time_REST {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register REST routes.
	 */
	public function register_routes(): void {
		// ========================================
		// CRUD Routes for Event Times
		// ========================================

		// List all event times
		register_rest_route( $this->namespace, '/event-times', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_event_times' ),
				'permission_callback' => array( $this, 'can_edit' ),
				'args'                => array(
					'church_id' => array(
						'type'    => 'integer',
						'default' => 0,
					),
					'type' => array(
						'type'    => 'string',
						'default' => 'all',
					),
					'status' => array(
						'type'    => 'string',
						'default' => 'publish',
					),
				),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_event_time' ),
				'permission_callback' => array( $this, 'can_edit' ),
				'args'                => $this->get_event_time_args(),
			),
		) );

		// Single event time
		register_rest_route( $this->namespace, '/event-times/(?P<id>\d+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_event_time' ),
				'permission_callback' => array( $this, 'can_edit' ),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_event_time' ),
				'permission_callback' => array( $this, 'can_edit' ),
				'args'                => $this->get_event_time_args(),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_event_time' ),
				'permission_callback' => array( $this, 'can_edit' ),
			),
		) );

		// Duplicate an event time
		register_rest_route( $this->namespace, '/event-times/(?P<id>\d+)/duplicate', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'duplicate_event_time' ),
			'permission_callback' => array( $this, 'can_edit' ),
		) );

		// Bulk operations
		register_rest_route( $this->namespace, '/event-times/bulk', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'bulk_action' ),
			'permission_callback' => array( $this, 'can_edit' ),
			'args'                => array(
				'action' => array(
					'required' => true,
					'type'     => 'string',
					'enum'     => array( 'delete', 'activate', 'deactivate' ),
				),
				'ids' => array(
					'required' => true,
					'type'     => 'array',
					'items'    => array( 'type' => 'integer' ),
				),
			),
		) );

		// ========================================
		// Schedule Generation Routes
		// ========================================

		// Get generated schedule
		register_rest_route( $this->namespace, '/event-times/schedule', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'get_schedule' ),
			'permission_callback' => '__return_true', // Public
			'args'                => array(
				'start_date' => array(
					'type'    => 'string',
					'default' => '',
				),
				'end_date' => array(
					'type'    => 'string',
					'default' => '',
				),
				'days' => array(
					'type'    => 'integer',
					'default' => 14,
				),
				'church_id' => array(
					'type'    => 'integer',
					'default' => 0,
				),
				'type' => array(
					'type'    => 'string',
					'default' => 'all',
				),
				'group_by' => array(
					'type'    => 'string',
					'enum'    => array( 'none', 'day', 'church' ),
					'default' => 'none',
				),
				'limit' => array(
					'type'    => 'integer',
					'default' => 100,
				),
			),
		) );

		// Get today's schedule
		register_rest_route( $this->namespace, '/event-times/schedule/today', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'get_schedule_today' ),
			'permission_callback' => '__return_true',
			'args'                => array(
				'church_id' => array(
					'type'    => 'integer',
					'default' => 0,
				),
				'type' => array(
					'type'    => 'string',
					'default' => 'all',
				),
			),
		) );

		// Get this week's schedule
		register_rest_route( $this->namespace, '/event-times/schedule/week', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'get_schedule_week' ),
			'permission_callback' => '__return_true',
			'args'                => array(
				'church_id' => array(
					'type'    => 'integer',
					'default' => 0,
				),
				'type' => array(
					'type'    => 'string',
					'default' => 'all',
				),
				'group_by' => array(
					'type'    => 'string',
					'enum'    => array( 'none', 'day', 'church' ),
					'default' => 'day',
				),
			),
		) );

		// ========================================
		// Admin Grid View Routes (Mass Times)
		// ========================================

		// Get 7-day grid for admin UI
		register_rest_route( $this->namespace, '/mass-times/grid', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'get_mass_times_grid' ),
			'permission_callback' => array( $this, 'can_edit' ),
			'args'                => array(
				'week_start' => array(
					'type'        => 'string',
					'default'     => '',
					'description' => 'Start date of the week (Y-m-d). Defaults to current week.',
				),
				'church_id' => array(
					'type'    => 'integer',
					'default' => 0,
				),
				'type' => array(
					'type'    => 'string',
					'default' => 'all',
				),
			),
		) );

		// Quick create mass time (for grid click)
		register_rest_route( $this->namespace, '/mass-times/quick-create', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'quick_create_mass_time' ),
			'permission_callback' => array( $this, 'can_edit' ),
			'args'                => array(
				'date' => array(
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'time' => array(
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'church_id' => array(
					'type'              => 'integer',
					'default'           => 0,
					'sanitize_callback' => function( $value ) { return absint( $value ); },
				),
				'event_type' => array(
					'type'              => 'string',
					'default'           => 'mass',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'duration_minutes' => array(
					'type'              => 'integer',
					'default'           => 60,
					'sanitize_callback' => function( $value ) { return absint( $value ); },
				),
			),
		) );

		// Duplicate event to another day
		register_rest_route( $this->namespace, '/mass-times/(?P<id>\d+)/duplicate-to-day', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'duplicate_to_day' ),
			'permission_callback' => array( $this, 'can_edit' ),
			'args'                => array(
				'target_date' => array(
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'copy_time' => array(
					'type'    => 'boolean',
					'default' => true,
				),
			),
		) );

		// Copy week schedule
		register_rest_route( $this->namespace, '/mass-times/copy-week', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'copy_week_schedule' ),
			'permission_callback' => array( $this, 'can_edit' ),
			'args'                => array(
				'source_week_start' => array(
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'target_week_start' => array(
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'church_id' => array(
					'type'    => 'integer',
					'default' => 0,
				),
			),
		) );

		// Clear day
		register_rest_route( $this->namespace, '/mass-times/clear-day', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'clear_day' ),
			'permission_callback' => array( $this, 'can_edit' ),
			'args'                => array(
				'date' => array(
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'church_id' => array(
					'type'    => 'integer',
					'default' => 0,
				),
				'type' => array(
					'type'    => 'string',
					'default' => 'all',
				),
			),
		) );

		// ========================================
		// Metadata Routes
		// ========================================

		// Get event types
		register_rest_route( $this->namespace, '/event-times/types', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'get_event_types' ),
			'permission_callback' => '__return_true',
		) );

		// Get frequencies
		register_rest_route( $this->namespace, '/event-times/frequencies', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'get_frequencies' ),
			'permission_callback' => '__return_true',
		) );

		// Get churches
		register_rest_route( $this->namespace, '/event-times/churches', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'get_churches' ),
			'permission_callback' => array( $this, 'can_edit' ),
		) );

		// Get readings for a date
		register_rest_route( $this->namespace, '/event-times/readings/(?P<date>\d{4}-\d{2}-\d{2})', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'get_readings' ),
			'permission_callback' => '__return_true',
		) );
	}

	/**
	 * Get argument schema for event time endpoints.
	 *
	 * @return array Argument schema.
	 */
	private function get_event_time_args(): array {
		return array(
			'title' => array(
				'type'              => 'string',
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
			),
			'church_id' => array(
				'type'              => 'integer',
				'required'          => false,
				'default'           => 0,
				'sanitize_callback' => function( $value ) { return absint( $value ); },
			),
			'event_type' => array(
				'type'              => 'string',
				'default'           => 'mass',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'start_datetime' => array(
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			),
			'duration_minutes' => array(
				'type'              => 'integer',
				'default'           => 60,
				'sanitize_callback' => function( $value ) { return absint( $value ); },
			),
			'timezone' => array(
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'is_recurring' => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'recurrence_rule' => array(
				'type'    => array( 'object', 'array' ),
				'default' => array(),
			),
			'recurrence_end_type' => array(
				'type'              => 'string',
				'default'           => 'never',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'recurrence_end_date' => array(
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'recurrence_count' => array(
				'type'              => 'integer',
				'default'           => 0,
				'sanitize_callback' => function( $value ) { return absint( $value ); },
			),
			'exception_dates' => array(
				'type'    => 'array',
				'default' => array(),
			),
			'livestream_enabled' => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'livestream_mode' => array(
				'type'              => 'string',
				'default'           => 'link',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'livestream_url' => array(
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
			),
			'livestream_embed' => array(
				'type'    => 'string',
				'default' => '',
			),
			'livestream_provider' => array(
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'intentions' => array(
				'type'    => 'string',
				'default' => '',
			),
			'notes' => array(
				'type'    => 'string',
				'default' => '',
			),
			'readings_mode' => array(
				'type'              => 'string',
				'default'           => 'auto',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'readings_override' => array(
				'type'    => array( 'object', 'null' ),
				'default' => null,
			),
			'liturgical_rite' => array(
				'type'              => 'string',
				'default'           => 'roman',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'liturgical_form' => array(
				'type'              => 'string',
				'default'           => 'ordinary',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'language' => array(
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'linked_mass_id' => array(
				'type'              => 'integer',
				'default'           => 0,
				'sanitize_callback' => function( $value ) { return absint( $value ); },
			),
			'is_active' => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'is_special' => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'display_priority' => array(
				'type'              => 'integer',
				'default'           => 0,
				'sanitize_callback' => function( $value ) { return intval( $value ); },
			),
		);
	}

	/**
	 * Permission callback - check if user can edit.
	 *
	 * @return bool|WP_Error True if permitted.
	 */
	public function can_edit() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'You do not have permission to manage event times.', 'parish-core' ),
				array( 'status' => 403 )
			);
		}
		return true;
	}

	/**
	 * Clear all event time caches.
	 *
	 * Clears both the generator cache and grid transient caches.
	 *
	 * @return void
	 */
	private function clear_all_caches(): void {
		// Clear generator cache.
		if ( class_exists( 'Parish_Event_Time_Generator' ) ) {
			Parish_Event_Time_Generator::instance()->clear_cache();
		}

		// Clear grid transients (delete all with parish_grid_ prefix).
		global $wpdb;
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
				'_transient_parish_grid_%',
				'_transient_timeout_parish_grid_%'
			)
		);
	}

	/**
	 * Get all event times.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public function get_event_times( WP_REST_Request $request ): WP_REST_Response {
		$args = array(
			'post_type'      => 'parish_event_time',
			'post_status'    => $request->get_param( 'status' ),
			'posts_per_page' => -1,
			'orderby'        => 'meta_value',
			'meta_key'       => 'parish_start_datetime',
			'order'          => 'ASC',
		);

		$meta_query = array();

		$church_id = $request->get_param( 'church_id' );
		if ( $church_id > 0 ) {
			$meta_query[] = array(
				'key'     => 'parish_church_id',
				'value'   => $church_id,
				'compare' => '=',
				'type'    => 'NUMERIC',
			);
		}

		$type = $request->get_param( 'type' );
		if ( 'all' !== $type && ! empty( $type ) ) {
			$meta_query[] = array(
				'key'     => 'parish_event_type',
				'value'   => $type,
				'compare' => '=',
			);
		}

		if ( ! empty( $meta_query ) ) {
			$args['meta_query'] = $meta_query;
		}

		$posts = get_posts( $args );
		$items = array_map( array( $this, 'prepare_event_time_response' ), $posts );

		return rest_ensure_response( $items );
	}

	/**
	 * Get a single event time.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response or error.
	 */
	public function get_event_time( WP_REST_Request $request ) {
		try {
			$id = absint( $request->get_param( 'id' ) );

			if ( $id <= 0 ) {
				return new WP_Error(
					'invalid_id',
					__( 'Invalid event time ID.', 'parish-core' ),
					array( 'status' => 400 )
				);
			}

			$post = get_post( $id );

			if ( ! $post || 'parish_event_time' !== $post->post_type ) {
				return new WP_Error(
					'not_found',
					__( 'Event time not found.', 'parish-core' ),
					array( 'status' => 404 )
				);
			}

			return rest_ensure_response( $this->prepare_event_time_response( $post ) );

		} catch ( Throwable $e ) {
			return new WP_Error(
				'get_error',
				sprintf( __( 'Error retrieving event time: %s', 'parish-core' ), $e->getMessage() ),
				array( 'status' => 500 )
			);
		}
	}

	/**
	 * Create a new event time.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response or error.
	 */
	public function create_event_time( WP_REST_Request $request ) {
		try {
			$params = $request->get_params();

			// Validate required field
			if ( empty( $params['start_datetime'] ) ) {
				return new WP_Error(
					'missing_start_datetime',
					__( 'Start date/time is required.', 'parish-core' ),
					array( 'status' => 400 )
				);
			}

			// Sanitize and validate params
			$params = $this->sanitize_event_time_params( $params );

			// Generate title if not provided
			$title = isset( $params['title'] ) && is_string( $params['title'] ) ? trim( $params['title'] ) : '';
			if ( empty( $title ) ) {
				$event_type = $params['event_type'] ?? 'mass';
				$title = $this->get_event_type_label( $event_type );

				// Add church name
				$church_id = absint( $params['church_id'] ?? 0 );
				if ( $church_id > 0 ) {
					$church = get_post( $church_id );
					if ( $church ) {
						$title .= ' - ' . $church->post_title;
					}
				}

				// Add time
				if ( ! empty( $params['start_datetime'] ) ) {
					try {
						$dt = new DateTime( $params['start_datetime'] );
						$title .= ' (' . $dt->format( 'l H:i' ) . ')';
					} catch ( Throwable $e ) {
						// Use raw datetime if parsing fails
					}
				}
			}

			$post_id = wp_insert_post( array(
				'post_type'   => 'parish_event_time',
				'post_status' => 'publish',
				'post_title'  => sanitize_text_field( $title ),
			), true );

			if ( is_wp_error( $post_id ) ) {
				return new WP_Error(
					'create_failed',
					__( 'Failed to create event time.', 'parish-core' ),
					array( 'status' => 500 )
				);
			}

			// Save all meta
			$this->save_event_time_meta( $post_id, $params );

			// Clear all caches
			$this->clear_all_caches();

			$post = get_post( $post_id );

			if ( ! $post ) {
				return new WP_Error(
					'post_not_found',
					__( 'Event time could not be retrieved after creation.', 'parish-core' ),
					array( 'status' => 500 )
				);
			}

			return rest_ensure_response( $this->prepare_event_time_response( $post ) );

		} catch ( Throwable $e ) {
			return new WP_Error(
				'create_error',
				sprintf( __( 'Error creating event time: %s', 'parish-core' ), $e->getMessage() ),
				array( 'status' => 500 )
			);
		}
	}

	/**
	 * Update an event time.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response or error.
	 */
	public function update_event_time( WP_REST_Request $request ) {
		try {
			$id = absint( $request->get_param( 'id' ) );

			if ( $id <= 0 ) {
				return new WP_Error(
					'invalid_id',
					__( 'Invalid event time ID.', 'parish-core' ),
					array( 'status' => 400 )
				);
			}

			$post = get_post( $id );

			if ( ! $post || 'parish_event_time' !== $post->post_type ) {
				return new WP_Error(
					'not_found',
					__( 'Event time not found.', 'parish-core' ),
					array( 'status' => 404 )
				);
			}

			$params = $request->get_params();

			// Update title if provided
			if ( isset( $params['title'] ) && is_string( $params['title'] ) ) {
				$result = wp_update_post( array(
					'ID'         => $id,
					'post_title' => sanitize_text_field( $params['title'] ),
				), true );

				if ( is_wp_error( $result ) ) {
					return new WP_Error(
						'update_failed',
						__( 'Failed to update event time title.', 'parish-core' ),
						array( 'status' => 500 )
					);
				}
			}

			// Sanitize and validate params before saving
			$params = $this->sanitize_event_time_params( $params );

			// Save all meta
			$this->save_event_time_meta( $id, $params );

			// Clear all caches
			$this->clear_all_caches();

			$post = get_post( $id );

			if ( ! $post ) {
				return new WP_Error(
					'post_not_found',
					__( 'Event time could not be retrieved after update.', 'parish-core' ),
					array( 'status' => 500 )
				);
			}

			return rest_ensure_response( $this->prepare_event_time_response( $post ) );

		} catch ( Throwable $e ) {
			return new WP_Error(
				'update_error',
				sprintf( __( 'Error updating event time: %s', 'parish-core' ), $e->getMessage() ),
				array( 'status' => 500 )
			);
		}
	}

	/**
	 * Delete an event time.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response or error.
	 */
	public function delete_event_time( WP_REST_Request $request ) {
		$id = $request->get_param( 'id' );
		$post = get_post( $id );

		if ( ! $post || 'parish_event_time' !== $post->post_type ) {
			return new WP_Error(
				'not_found',
				__( 'Event time not found.', 'parish-core' ),
				array( 'status' => 404 )
			);
		}

		wp_delete_post( $id, true );

		// Clear all caches
		$this->clear_all_caches();

		return rest_ensure_response( array(
			'deleted' => true,
			'id'      => $id,
		) );
	}

	/**
	 * Duplicate an event time.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response or error.
	 */
	public function duplicate_event_time( WP_REST_Request $request ) {
		$id = $request->get_param( 'id' );
		$post = get_post( $id );

		if ( ! $post || 'parish_event_time' !== $post->post_type ) {
			return new WP_Error(
				'not_found',
				__( 'Event time not found.', 'parish-core' ),
				array( 'status' => 404 )
			);
		}

		// Create new post
		$new_id = wp_insert_post( array(
			'post_type'   => 'parish_event_time',
			'post_status' => 'publish',
			'post_title'  => $post->post_title . ' (Copy)',
		) );

		if ( is_wp_error( $new_id ) ) {
			return $new_id;
		}

		// Copy all meta
		$meta = get_post_meta( $id );
		foreach ( $meta as $key => $values ) {
			if ( strpos( $key, 'parish_' ) === 0 ) {
				update_post_meta( $new_id, $key, maybe_unserialize( $values[0] ) );
			}
		}

		$new_post = get_post( $new_id );
		return rest_ensure_response( $this->prepare_event_time_response( $new_post ) );
	}

	/**
	 * Bulk action on event times.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function bulk_action( WP_REST_Request $request ): WP_REST_Response {
		$action = $request->get_param( 'action' );
		$ids = $request->get_param( 'ids' );

		$processed = 0;

		foreach ( $ids as $id ) {
			$post = get_post( $id );
			if ( ! $post || 'parish_event_time' !== $post->post_type ) {
				continue;
			}

			switch ( $action ) {
				case 'delete':
					wp_delete_post( $id, true );
					$processed++;
					break;

				case 'activate':
					update_post_meta( $id, 'parish_is_active', true );
					$processed++;
					break;

				case 'deactivate':
					update_post_meta( $id, 'parish_is_active', false );
					$processed++;
					break;
			}
		}

		// Clear all caches
		$this->clear_all_caches();

		return rest_ensure_response( array(
			'action'    => $action,
			'processed' => $processed,
			'total'     => count( $ids ),
		) );
	}

	/**
	 * Get generated schedule.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function get_schedule( WP_REST_Request $request ): WP_REST_Response {
		$start_date = $request->get_param( 'start_date' );
		$end_date = $request->get_param( 'end_date' );
		$days = $request->get_param( 'days' );
		$group_by = $request->get_param( 'group_by' );
		$limit = $request->get_param( 'limit' );

		// Calculate dates if not provided
		if ( empty( $start_date ) ) {
			$start_date = current_time( 'Y-m-d' );
		}
		if ( empty( $end_date ) ) {
			$end_date = date( 'Y-m-d', strtotime( '+' . ( $days - 1 ) . ' days', strtotime( $start_date ) ) );
		}

		$filters = array();

		$church_id = $request->get_param( 'church_id' );
		if ( $church_id > 0 ) {
			$filters['church_id'] = $church_id;
		}

		$type = $request->get_param( 'type' );
		if ( 'all' !== $type && ! empty( $type ) ) {
			$types = explode( ',', $type );
			$filters['type'] = count( $types ) === 1 ? $types[0] : $types;
		}

		$generator = Parish_Event_Time_Generator::instance();
		$instances = $generator->generate( $start_date, $end_date, $filters );

		// Apply limit
		if ( $limit > 0 && count( $instances ) > $limit ) {
			$instances = array_slice( $instances, 0, $limit );
		}

		// Group if requested
		switch ( $group_by ) {
			case 'day':
				$instances = Parish_Event_Time_Generator::group_by_date( $instances );
				break;
			case 'church':
				$instances = Parish_Event_Time_Generator::group_by_church( $instances );
				break;
		}

		return rest_ensure_response( array(
			'start_date' => $start_date,
			'end_date'   => $end_date,
			'group_by'   => $group_by,
			'count'      => is_array( $instances ) ? count( $instances ) : 0,
			'schedule'   => $instances,
		) );
	}

	/**
	 * Get today's schedule.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function get_schedule_today( WP_REST_Request $request ): WP_REST_Response {
		$filters = array();

		$church_id = $request->get_param( 'church_id' );
		if ( $church_id > 0 ) {
			$filters['church_id'] = $church_id;
		}

		$type = $request->get_param( 'type' );
		if ( 'all' !== $type && ! empty( $type ) ) {
			$filters['type'] = $type;
		}

		$generator = Parish_Event_Time_Generator::instance();
		$instances = $generator->generate_today( $filters );

		return rest_ensure_response( array(
			'date'     => current_time( 'Y-m-d' ),
			'day_name' => current_time( 'l' ),
			'count'    => count( $instances ),
			'schedule' => $instances,
		) );
	}

	/**
	 * Get this week's schedule.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function get_schedule_week( WP_REST_Request $request ): WP_REST_Response {
		$filters = array();
		$group_by = $request->get_param( 'group_by' );

		$church_id = $request->get_param( 'church_id' );
		if ( $church_id > 0 ) {
			$filters['church_id'] = $church_id;
		}

		$type = $request->get_param( 'type' );
		if ( 'all' !== $type && ! empty( $type ) ) {
			$filters['type'] = $type;
		}

		$generator = Parish_Event_Time_Generator::instance();
		$instances = $generator->generate_week( $filters );

		// Group if requested
		if ( 'day' === $group_by ) {
			$instances = Parish_Event_Time_Generator::group_by_date( $instances );
		} elseif ( 'church' === $group_by ) {
			$instances = Parish_Event_Time_Generator::group_by_church( $instances );
		}

		return rest_ensure_response( array(
			'start_date' => current_time( 'Y-m-d' ),
			'end_date'   => date( 'Y-m-d', strtotime( '+6 days' ) ),
			'group_by'   => $group_by,
			'count'      => is_array( $instances ) ? count( $instances ) : 0,
			'schedule'   => $instances,
		) );
	}

	/**
	 * Get event types.
	 *
	 * @return WP_REST_Response Response.
	 */
	public function get_event_types(): WP_REST_Response {
		$types = Parish_Event_Time_Generator::get_event_types();

		$response = array();
		foreach ( $types as $slug => $info ) {
			$response[] = array(
				'value' => $slug,
				'label' => $info['label'],
				'plural' => $info['plural'],
				'icon'  => $info['icon'],
				'color' => $info['color'],
				'has_readings' => $info['has_readings'],
			);
		}

		return rest_ensure_response( $response );
	}

	/**
	 * Get frequencies.
	 *
	 * @return WP_REST_Response Response.
	 */
	public function get_frequencies(): WP_REST_Response {
		$frequencies = Parish_Event_Time_Generator::get_frequencies();

		$response = array();
		foreach ( $frequencies as $value => $label ) {
			$response[] = array(
				'value' => $value,
				'label' => $label,
			);
		}

		return rest_ensure_response( $response );
	}

	/**
	 * Get churches.
	 *
	 * @return WP_REST_Response Response.
	 */
	public function get_churches(): WP_REST_Response {
		$posts = get_posts( array(
			'post_type'      => 'parish_church',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		) );

		$churches = array_map( function ( $post ) {
			return array(
				'id'    => $post->ID,
				'title' => $post->post_title,
				'slug'  => $post->post_name,
			);
		}, $posts );

		return rest_ensure_response( $churches );
	}

	/**
	 * Get readings for a date.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function get_readings( WP_REST_Request $request ): WP_REST_Response {
		$date = $request->get_param( 'date' );

		// Check cache
		$cache_key = 'parish_readings_' . $date;
		$cached = get_transient( $cache_key );

		if ( false !== $cached ) {
			return rest_ensure_response( array(
				'date'     => $date,
				'cached'   => true,
				'readings' => $cached,
			) );
		}

		// Fetch from API
		if ( ! class_exists( 'Parish_Readings' ) ) {
			return rest_ensure_response( array(
				'date'     => $date,
				'error'    => 'Readings API not available',
				'readings' => null,
			) );
		}

		$readings = Parish_Readings::instance();
		$data = $readings->get_reading( 'mass_reading_details', array( 'date' => $date ) );

		if ( ! empty( $data ) ) {
			set_transient( $cache_key, $data, DAY_IN_SECONDS );
		}

		return rest_ensure_response( array(
			'date'     => $date,
			'cached'   => false,
			'readings' => $data,
		) );
	}

	/**
	 * Safely decode JSON from meta value.
	 *
	 * @param string|null $value   JSON string to decode.
	 * @param mixed       $default Default value if decoding fails.
	 * @return mixed Decoded value or default.
	 */
	private function safe_json_decode( ?string $value, $default = array() ) {
		if ( empty( $value ) || ! is_string( $value ) ) {
			return $default;
		}

		$decoded = json_decode( $value, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return $default;
		}

		return $decoded ?? $default;
	}

	/**
	 * Prepare event time for response.
	 *
	 * @param WP_Post $post Post object.
	 * @return array Prepared data.
	 */
	private function prepare_event_time_response( WP_Post $post ): array {
		$meta = get_post_meta( $post->ID );

		// Helper to safely get meta value
		$get_meta = function ( string $key, $default = '' ) use ( $meta ) {
			$value = $meta[ $key ][0] ?? $default;
			return is_string( $value ) ? $value : $default;
		};

		// Helper to safely get integer meta
		$get_int_meta = function ( string $key, int $default = 0 ) use ( $meta ): int {
			return isset( $meta[ $key ][0] ) ? absint( $meta[ $key ][0] ) : $default;
		};

		// Helper to safely get boolean meta
		$get_bool_meta = function ( string $key, bool $default = false ) use ( $meta ): bool {
			if ( ! isset( $meta[ $key ][0] ) ) {
				return $default;
			}
			return filter_var( $meta[ $key ][0], FILTER_VALIDATE_BOOLEAN );
		};

		// Get church info
		$church_id = $get_int_meta( 'parish_church_id', 0 );
		$church_name = '';
		if ( $church_id > 0 ) {
			$church = get_post( $church_id );
			if ( $church instanceof WP_Post ) {
				$church_name = $church->post_title;
			}
		}

		// Parse JSON fields safely
		$recurrence_rule = $this->safe_json_decode( $get_meta( 'parish_recurrence_rule' ), array() );
		$exception_dates = $this->safe_json_decode( $get_meta( 'parish_exception_dates' ), array() );
		$readings_override = $this->safe_json_decode( $get_meta( 'parish_readings_override' ), null );

		return array(
			'id'                   => $post->ID,
			'title'                => $post->post_title,
			'status'               => $post->post_status,
			'church_id'            => $church_id,
			'church_name'          => $church_name,
			'event_type'           => $get_meta( 'parish_event_type', 'mass' ),
			'start_datetime'       => $get_meta( 'parish_start_datetime' ),
			'duration_minutes'     => $get_int_meta( 'parish_duration_minutes', 60 ),
			'timezone'             => $get_meta( 'parish_timezone' ) ?: wp_timezone_string(),
			'is_recurring'         => $get_bool_meta( 'parish_is_recurring' ),
			'recurrence_rule'      => $recurrence_rule,
			'recurrence_end_type'  => $get_meta( 'parish_recurrence_end_type', 'never' ),
			'recurrence_end_date'  => $get_meta( 'parish_recurrence_end_date' ),
			'recurrence_count'     => $get_int_meta( 'parish_recurrence_count' ),
			'exception_dates'      => $exception_dates,
			'livestream_enabled'   => $get_bool_meta( 'parish_livestream_enabled' ),
			'livestream_mode'      => $get_meta( 'parish_livestream_mode', 'link' ),
			'livestream_url'       => $get_meta( 'parish_livestream_url' ),
			'livestream_embed'     => $get_meta( 'parish_livestream_embed' ),
			'livestream_provider'  => $get_meta( 'parish_livestream_provider' ),
			'intentions'           => $get_meta( 'parish_intentions' ),
			'notes'                => $get_meta( 'parish_notes' ),
			'readings_mode'        => $get_meta( 'parish_readings_mode', 'auto' ),
			'readings_override'    => $readings_override,
			'liturgical_rite'      => $get_meta( 'parish_liturgical_rite', 'roman' ),
			'liturgical_form'      => $get_meta( 'parish_liturgical_form', 'ordinary' ),
			'language'             => $get_meta( 'parish_language' ),
			'linked_mass_id'       => $get_int_meta( 'parish_linked_mass_id' ),
			'is_active'            => $get_bool_meta( 'parish_is_active', true ),
			'is_special'           => $get_bool_meta( 'parish_is_special' ),
			'display_priority'     => $get_int_meta( 'parish_display_priority' ),
			'created'              => $post->post_date,
			'modified'             => $post->post_modified,
		);
	}

	/**
	 * Sanitize and validate event time parameters.
	 *
	 * @param array $params Raw parameters from request.
	 * @return array Sanitized parameters.
	 */
	private function sanitize_event_time_params( array $params ): array {
		$sanitized = array();

		// Integer fields
		$int_fields = array( 'church_id', 'duration_minutes', 'recurrence_count', 'linked_mass_id', 'display_priority' );
		foreach ( $int_fields as $field ) {
			if ( isset( $params[ $field ] ) ) {
				$sanitized[ $field ] = absint( $params[ $field ] );
			}
		}

		// String fields (sanitize as text)
		$text_fields = array(
			'title', 'event_type', 'timezone', 'recurrence_end_type', 'recurrence_end_date',
			'livestream_mode', 'livestream_provider', 'readings_mode',
			'liturgical_rite', 'liturgical_form', 'language',
		);
		foreach ( $text_fields as $field ) {
			if ( isset( $params[ $field ] ) ) {
				$sanitized[ $field ] = is_string( $params[ $field ] ) ? sanitize_text_field( $params[ $field ] ) : '';
			}
		}

		// URL field
		if ( isset( $params['livestream_url'] ) ) {
			$sanitized['livestream_url'] = is_string( $params['livestream_url'] ) ? esc_url_raw( $params['livestream_url'] ) : '';
		}

		// Boolean fields
		$bool_fields = array( 'is_recurring', 'livestream_enabled', 'is_active', 'is_special' );
		foreach ( $bool_fields as $field ) {
			if ( isset( $params[ $field ] ) ) {
				$sanitized[ $field ] = filter_var( $params[ $field ], FILTER_VALIDATE_BOOLEAN );
			}
		}

		// Datetime field - validate format
		if ( isset( $params['start_datetime'] ) ) {
			$datetime = $params['start_datetime'];
			if ( is_string( $datetime ) && ! empty( $datetime ) ) {
				// Try to parse and normalize the datetime
				try {
					$dt = new DateTime( $datetime );
					$sanitized['start_datetime'] = $dt->format( 'Y-m-d\TH:i:s' );
				} catch ( Exception $e ) {
					// Keep original if parsing fails, let the generator handle it
					$sanitized['start_datetime'] = sanitize_text_field( $datetime );
				}
			}
		}

		// Array/Object fields - ensure proper structure
		if ( isset( $params['recurrence_rule'] ) ) {
			$rule = $params['recurrence_rule'];
			if ( is_array( $rule ) ) {
				$sanitized['recurrence_rule'] = array(
					'frequency'    => isset( $rule['frequency'] ) ? sanitize_text_field( $rule['frequency'] ) : 'weekly',
					'days'         => isset( $rule['days'] ) && is_array( $rule['days'] ) ? array_map( 'sanitize_text_field', $rule['days'] ) : array(),
					'day_of_month' => isset( $rule['day_of_month'] ) ? absint( $rule['day_of_month'] ) : null,
					'position'     => isset( $rule['position'] ) ? sanitize_text_field( $rule['position'] ) : null,
					'interval'     => isset( $rule['interval'] ) ? max( 1, absint( $rule['interval'] ) ) : 1,
				);
			} elseif ( is_string( $rule ) ) {
				// Try to decode if it's a JSON string
				$decoded = json_decode( $rule, true );
				$sanitized['recurrence_rule'] = is_array( $decoded ) ? $decoded : array();
			}
		}

		if ( isset( $params['exception_dates'] ) ) {
			$dates = $params['exception_dates'];
			if ( is_array( $dates ) ) {
				$sanitized['exception_dates'] = array_filter( array_map( 'sanitize_text_field', $dates ) );
			} elseif ( is_string( $dates ) ) {
				$decoded = json_decode( $dates, true );
				$sanitized['exception_dates'] = is_array( $decoded ) ? array_filter( array_map( 'sanitize_text_field', $decoded ) ) : array();
			}
		}

		// Content fields (allow some HTML)
		if ( isset( $params['intentions'] ) ) {
			$sanitized['intentions'] = is_string( $params['intentions'] ) ? wp_kses_post( $params['intentions'] ) : '';
		}

		if ( isset( $params['notes'] ) ) {
			$sanitized['notes'] = is_string( $params['notes'] ) ? wp_kses_post( $params['notes'] ) : '';
		}

		// Embed code (handled by separate sanitizer)
		if ( isset( $params['livestream_embed'] ) ) {
			$sanitized['livestream_embed'] = is_string( $params['livestream_embed'] ) ? $params['livestream_embed'] : '';
		}

		// Readings override (complex object)
		if ( isset( $params['readings_override'] ) ) {
			if ( is_array( $params['readings_override'] ) ) {
				$sanitized['readings_override'] = $params['readings_override'];
			} elseif ( is_string( $params['readings_override'] ) && ! empty( $params['readings_override'] ) ) {
				$decoded = json_decode( $params['readings_override'], true );
				$sanitized['readings_override'] = is_array( $decoded ) ? $decoded : null;
			} else {
				$sanitized['readings_override'] = null;
			}
		}

		return $sanitized;
	}

	/**
	 * Save event time meta fields.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $params  Parameters to save.
	 */
	private function save_event_time_meta( int $post_id, array $params ): void {
		$meta_fields = array(
			'church_id'            => 'parish_church_id',
			'event_type'           => 'parish_event_type',
			'start_datetime'       => 'parish_start_datetime',
			'duration_minutes'     => 'parish_duration_minutes',
			'timezone'             => 'parish_timezone',
			'is_recurring'         => 'parish_is_recurring',
			'recurrence_end_type'  => 'parish_recurrence_end_type',
			'recurrence_end_date'  => 'parish_recurrence_end_date',
			'recurrence_count'     => 'parish_recurrence_count',
			'livestream_enabled'   => 'parish_livestream_enabled',
			'livestream_mode'      => 'parish_livestream_mode',
			'livestream_url'       => 'parish_livestream_url',
			'livestream_provider'  => 'parish_livestream_provider',
			'intentions'           => 'parish_intentions',
			'notes'                => 'parish_notes',
			'readings_mode'        => 'parish_readings_mode',
			'liturgical_rite'      => 'parish_liturgical_rite',
			'liturgical_form'      => 'parish_liturgical_form',
			'language'             => 'parish_language',
			'linked_mass_id'       => 'parish_linked_mass_id',
			'is_active'            => 'parish_is_active',
			'is_special'           => 'parish_is_special',
			'display_priority'     => 'parish_display_priority',
		);

		foreach ( $meta_fields as $param_key => $meta_key ) {
			if ( ! isset( $params[ $param_key ] ) ) {
				continue;
			}

			$value = $params[ $param_key ];

			// Handle booleans - convert to string for meta storage
			if ( is_bool( $value ) ) {
				$value = $value ? '1' : '0';
			} elseif ( is_int( $value ) || is_float( $value ) ) {
				// Numbers are fine as-is
				$value = (string) $value;
			} elseif ( ! is_string( $value ) ) {
				// Skip non-scalar values in simple fields
				continue;
			}

			update_post_meta( $post_id, $meta_key, $value );
		}

		// Handle JSON fields with proper encoding and error checking
		$this->save_json_meta( $post_id, 'parish_recurrence_rule', $params['recurrence_rule'] ?? null );
		$this->save_json_meta( $post_id, 'parish_exception_dates', $params['exception_dates'] ?? null );
		$this->save_json_meta( $post_id, 'parish_readings_override', $params['readings_override'] ?? null );

		// Sanitize embed code
		if ( isset( $params['livestream_embed'] ) ) {
			$embed = is_string( $params['livestream_embed'] ) ? $params['livestream_embed'] : '';
			$sanitized = ! empty( $embed ) ? $this->sanitize_embed_code( $embed ) : '';
			update_post_meta( $post_id, 'parish_livestream_embed', $sanitized );
		}
	}

	/**
	 * Save a JSON meta field with proper encoding and error handling.
	 *
	 * @param int         $post_id  Post ID.
	 * @param string      $meta_key Meta key.
	 * @param mixed       $value    Value to save (array, string, or null).
	 */
	private function save_json_meta( int $post_id, string $meta_key, $value ): void {
		if ( null === $value ) {
			return;
		}

		// If already a string (pre-encoded JSON), validate and save
		if ( is_string( $value ) ) {
			if ( empty( $value ) ) {
				update_post_meta( $post_id, $meta_key, '[]' );
				return;
			}

			// Validate it's valid JSON
			$decoded = json_decode( $value, true );
			if ( json_last_error() === JSON_ERROR_NONE ) {
				update_post_meta( $post_id, $meta_key, $value );
			} else {
				// Invalid JSON string - save empty array
				update_post_meta( $post_id, $meta_key, '[]' );
			}
			return;
		}

		// If array/object, encode to JSON
		if ( is_array( $value ) ) {
			$encoded = wp_json_encode( $value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
			if ( false === $encoded ) {
				// Encoding failed - save empty array
				$encoded = '[]';
			}
			update_post_meta( $post_id, $meta_key, $encoded );
			return;
		}

		// For any other type, save empty
		update_post_meta( $post_id, $meta_key, '[]' );
	}

	/**
	 * Sanitize embed code - whitelist safe providers.
	 *
	 * Local copy to avoid dependency on Parish_Event_Time_Meta class
	 * which may not be loaded when REST API runs.
	 *
	 * @param string $value Embed code.
	 * @return string Sanitized embed code.
	 */
	private function sanitize_embed_code( string $value ): string {
		if ( empty( $value ) ) {
			return '';
		}

		$allowed_domains = array(
			'youtube.com',
			'www.youtube.com',
			'youtube-nocookie.com',
			'www.youtube-nocookie.com',
			'youtu.be',
			'facebook.com',
			'www.facebook.com',
			'fb.watch',
			'vimeo.com',
			'player.vimeo.com',
			'twitch.tv',
			'player.twitch.tv',
			'churchstreaming.tv',
			'livestream.com',
			'mcnmedia.tv',
			'boxcast.tv',
		);

		if ( preg_match( '/<iframe[^>]+src=["\']([^"\']+)["\']/', $value, $matches ) ) {
			$src = $matches[1];
			$parsed = wp_parse_url( $src );
			$host = $parsed['host'] ?? '';

			$domain_allowed = false;
			foreach ( $allowed_domains as $domain ) {
				if ( $host === $domain || str_ends_with( $host, '.' . $domain ) ) {
					$domain_allowed = true;
					break;
				}
			}

			if ( ! $domain_allowed ) {
				return '';
			}
		}

		$allowed_html = array(
			'iframe' => array(
				'src'             => true,
				'width'           => true,
				'height'          => true,
				'frameborder'     => true,
				'allow'           => true,
				'allowfullscreen' => true,
				'title'           => true,
				'loading'         => true,
				'style'           => true,
				'class'           => true,
				'id'              => true,
			),
		);

		return wp_kses( $value, $allowed_html );
	}

	/**
	 * Get event type label safely.
	 *
	 * Provides fallback if Parish_Event_Time_Generator is not available.
	 *
	 * @param string $event_type Event type slug.
	 * @return string Human-readable label.
	 */
	private function get_event_type_label( string $event_type ): string {
		// Try to use generator if available
		if ( class_exists( 'Parish_Event_Time_Generator' ) ) {
			$type_info = Parish_Event_Time_Generator::get_event_type( $event_type );
			if ( ! empty( $type_info['label'] ) ) {
				return $type_info['label'];
			}
		}

		// Fallback labels
		$fallback_labels = array(
			'mass'       => 'Mass',
			'confession' => 'Confession',
			'adoration'  => 'Adoration',
			'baptism'    => 'Baptism',
			'wedding'    => 'Wedding',
			'funeral'    => 'Funeral',
			'stations'   => 'Stations of the Cross',
			'rosary'     => 'Rosary',
			'other'      => 'Service',
		);

		return $fallback_labels[ $event_type ] ?? ucfirst( $event_type );
	}

	// ========================================
	// Mass Times Grid Methods
	// ========================================

	/**
	 * Get mass times grid for admin UI.
	 *
	 * Returns a 7-day grid structure optimized for the weekly editor.
	 * Includes feast day and liturgical data for each day.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response.
	 */
	public function get_mass_times_grid( WP_REST_Request $request ): WP_REST_Response {
		$week_start_param = $request->get_param( 'week_start' );
		$church_id = $request->get_param( 'church_id' );
		$type = $request->get_param( 'type' );

		// Calculate week start (respect WP locale setting)
		$start_of_week = (int) get_option( 'start_of_week', 0 ); // 0 = Sunday, 1 = Monday

		if ( ! empty( $week_start_param ) ) {
			$week_start = new DateTime( $week_start_param );
		} else {
			$week_start = new DateTime( 'now', wp_timezone() );
			// Adjust to start of week
			$current_dow = (int) $week_start->format( 'w' );
			$days_to_subtract = ( $current_dow - $start_of_week + 7 ) % 7;
			if ( $days_to_subtract > 0 ) {
				$week_start->modify( "-{$days_to_subtract} days" );
			}
		}

		$week_end = ( clone $week_start )->modify( '+6 days' );

		// Build filters
		$filters = array();
		if ( $church_id > 0 ) {
			$filters['church_id'] = $church_id;
		}
		if ( 'all' !== $type && ! empty( $type ) ) {
			$filters['type'] = $type;
		}

		// Check transient cache for grid data (cache for 5 minutes)
		$cache_key = 'parish_grid_' . md5( $week_start->format( 'Y-m-d' ) . wp_json_encode( $filters ) );
		$cached_response = get_transient( $cache_key );
		if ( false !== $cached_response && ! isset( $_GET['nocache'] ) ) {
			// Update dynamic fields (is_today, is_past) and return
			$cached_response = $this->update_grid_dynamic_fields( $cached_response );
			return rest_ensure_response( $cached_response );
		}

		// Generate schedule for the week
		$generator = Parish_Event_Time_Generator::instance();
		$instances = $generator->generate(
			$week_start->format( 'Y-m-d' ),
			$week_end->format( 'Y-m-d' ),
			$filters
		);

		// Also get the raw event times (templates) to show in grid
		$raw_event_times = $this->get_event_times_for_week( $week_start, $week_end, $filters );

		// Pre-fetch feast days for the week (batch fetch for performance)
		$feast_days = array();
		if ( class_exists( 'Parish_Feast_Day_Service' ) ) {
			$feast_service = Parish_Feast_Day_Service::instance();
			$feast_days = $feast_service->get_feast_days_for_range(
				$week_start->format( 'Y-m-d' ),
				$week_end->format( 'Y-m-d' )
			);
		}

		// Build grid structure
		$grid = array();
		$current = clone $week_start;
		for ( $i = 0; $i < 7; $i++ ) {
			$date_str = $current->format( 'Y-m-d' );
			$feast = $feast_days[ $date_str ] ?? null;

			$grid[ $date_str ] = array(
				'date'           => $date_str,
				'date_formatted' => date_i18n( get_option( 'date_format' ), $current->getTimestamp() ),
				'day_name'       => date_i18n( 'l', $current->getTimestamp() ),
				'day_short'      => date_i18n( 'D', $current->getTimestamp() ),
				'day_number'     => $current->format( 'j' ),
				'is_today'       => $date_str === current_time( 'Y-m-d' ),
				'is_past'        => $date_str < current_time( 'Y-m-d' ),
				'events'         => array(),
				'templates'      => array(), // Raw event time posts for editing
				// Liturgical data
				'feast'          => $feast ? array(
					'title'     => $feast['title'] ?? '',
					'rank'      => $feast['rank'] ?? 'feria',
					'color'     => $feast['color'] ?? 'green',
					'color_hex' => $feast['color_hex'] ?? '#228B22',
					'season'    => $feast['season'] ?? 'ordinary',
				) : null,
			);
			$current->modify( '+1 day' );
		}

		// Populate generated instances
		foreach ( $instances as $instance ) {
			$date = $instance['date'];
			if ( isset( $grid[ $date ] ) ) {
				$grid[ $date ]['events'][] = $instance;
			}
		}

		// Populate templates
		foreach ( $raw_event_times as $event_time ) {
			$start_date = $event_time['start_date'] ?? '';
			if ( ! empty( $start_date ) && isset( $grid[ $start_date ] ) ) {
				$grid[ $start_date ]['templates'][] = $event_time;
			}
		}

		// Sort events by time within each day
		foreach ( $grid as $date => &$day ) {
			usort( $day['events'], function ( $a, $b ) {
				return strcmp( $a['time'], $b['time'] );
			} );
		}

		$response = array(
			'week_start'       => $week_start->format( 'Y-m-d' ),
			'week_end'         => $week_end->format( 'Y-m-d' ),
			'week_label'       => sprintf(
				/* translators: %1$s: start date, %2$s: end date */
				__( '%1$s - %2$s', 'parish-core' ),
				date_i18n( 'M j', $week_start->getTimestamp() ),
				date_i18n( 'M j, Y', $week_end->getTimestamp() )
			),
			'start_of_week'    => $start_of_week,
			'church_id'        => $church_id,
			'type_filter'      => $type,
			'total_events'     => count( $instances ),
			'grid'             => array_values( $grid ),
		);

		// Cache for 5 minutes
		set_transient( $cache_key, $response, 5 * MINUTE_IN_SECONDS );

		return rest_ensure_response( $response );
	}

	/**
	 * Update dynamic fields in cached grid response.
	 *
	 * @param array $response Cached response data.
	 * @return array Updated response.
	 */
	private function update_grid_dynamic_fields( array $response ): array {
		$today = current_time( 'Y-m-d' );

		if ( isset( $response['grid'] ) && is_array( $response['grid'] ) ) {
			foreach ( $response['grid'] as &$day ) {
				if ( isset( $day['date'] ) ) {
					$day['is_today'] = $day['date'] === $today;
					$day['is_past'] = $day['date'] < $today;
				}
			}
		}

		return $response;
	}

	/**
	 * Get event times that occur during a week (raw posts, not generated instances).
	 *
	 * @param DateTime $week_start Week start date.
	 * @param DateTime $week_end   Week end date.
	 * @param array    $filters    Filters.
	 * @return array Event time data.
	 */
	private function get_event_times_for_week( DateTime $week_start, DateTime $week_end, array $filters = array() ): array {
		$args = array(
			'post_type'      => 'parish_event_time',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'meta_query'     => array(
				array(
					'key'     => 'parish_is_active',
					'value'   => '1',
					'compare' => '=',
				),
			),
		);

		if ( ! empty( $filters['church_id'] ) ) {
			$args['meta_query'][] = array(
				'key'     => 'parish_church_id',
				'value'   => absint( $filters['church_id'] ),
				'compare' => '=',
				'type'    => 'NUMERIC',
			);
		}

		if ( ! empty( $filters['type'] ) && 'all' !== $filters['type'] ) {
			$types = is_array( $filters['type'] ) ? $filters['type'] : array( $filters['type'] );
			$args['meta_query'][] = array(
				'key'     => 'parish_event_type',
				'value'   => $types,
				'compare' => 'IN',
			);
		}

		$posts = get_posts( $args );
		$results = array();

		foreach ( $posts as $post ) {
			$data = $this->prepare_event_time_response( $post );
			// Add parsed start date for grid placement
			if ( ! empty( $data['start_datetime'] ) ) {
				try {
					$dt = new DateTime( $data['start_datetime'] );
					$data['start_date'] = $dt->format( 'Y-m-d' );
					$data['start_time'] = $dt->format( 'H:i' );
				} catch ( Exception $e ) {
					$data['start_date'] = '';
					$data['start_time'] = '';
				}
			}
			$results[] = $data;
		}

		return $results;
	}

	/**
	 * Quick create a mass time from grid click.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function quick_create_mass_time( WP_REST_Request $request ) {
		$date = $request->get_param( 'date' );
		$time = $request->get_param( 'time' );
		$church_id = $request->get_param( 'church_id' );
		$event_type = $request->get_param( 'event_type' );
		$duration = $request->get_param( 'duration_minutes' );

		// Validate date format
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			return new WP_Error( 'invalid_date', __( 'Invalid date format.', 'parish-core' ), array( 'status' => 400 ) );
		}

		// Validate time format
		if ( ! preg_match( '/^\d{2}:\d{2}$/', $time ) ) {
			return new WP_Error( 'invalid_time', __( 'Invalid time format.', 'parish-core' ), array( 'status' => 400 ) );
		}

		$datetime = $date . 'T' . $time . ':00';

		// Generate title
		$title = $this->get_event_type_label( $event_type );

		if ( $church_id > 0 ) {
			$church = get_post( $church_id );
			if ( $church ) {
				$title .= ' - ' . $church->post_title;
			}
		}

		try {
			$dt = new DateTime( $datetime );
			$title .= ' (' . date_i18n( 'l H:i', $dt->getTimestamp() ) . ')';
		} catch ( Exception $e ) {
			// Ignore
		}

		// Create the post
		$post_id = wp_insert_post( array(
			'post_type'   => 'parish_event_time',
			'post_status' => 'publish',
			'post_title'  => $title,
		) );

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		// Save meta
		$this->save_event_time_meta( $post_id, array(
			'church_id'        => $church_id,
			'event_type'       => $event_type,
			'start_datetime'   => $datetime,
			'duration_minutes' => $duration,
			'timezone'         => wp_timezone_string(),
			'is_recurring'     => false,
			'is_active'        => true,
			'is_special'       => false,
		) );

		// Clear all caches
		$this->clear_all_caches();

		$post = get_post( $post_id );
		return rest_ensure_response( $this->prepare_event_time_response( $post ) );
	}

	/**
	 * Duplicate an event time to another day.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function duplicate_to_day( WP_REST_Request $request ) {
		$id = $request->get_param( 'id' );
		$target_date = $request->get_param( 'target_date' );
		$copy_time = $request->get_param( 'copy_time' );

		$post = get_post( $id );
		if ( ! $post || 'parish_event_time' !== $post->post_type ) {
			return new WP_Error( 'not_found', __( 'Event time not found.', 'parish-core' ), array( 'status' => 404 ) );
		}

		// Validate target date
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $target_date ) ) {
			return new WP_Error( 'invalid_date', __( 'Invalid target date format.', 'parish-core' ), array( 'status' => 400 ) );
		}

		// Get original meta
		$meta = get_post_meta( $id );
		$original_datetime = $meta['parish_start_datetime'][0] ?? '';

		// Calculate new datetime
		$new_datetime = $target_date . 'T09:00:00'; // Default time
		if ( $copy_time && ! empty( $original_datetime ) ) {
			try {
				$original_dt = new DateTime( $original_datetime );
				$new_datetime = $target_date . 'T' . $original_dt->format( 'H:i:s' );
			} catch ( Exception $e ) {
				// Use default
			}
		}

		// Create new post
		$new_id = wp_insert_post( array(
			'post_type'   => 'parish_event_time',
			'post_status' => 'publish',
			'post_title'  => $post->post_title . ' (Copy)',
		) );

		if ( is_wp_error( $new_id ) ) {
			return $new_id;
		}

		// Copy all parish_ meta fields
		foreach ( $meta as $key => $values ) {
			if ( strpos( $key, 'parish_' ) === 0 ) {
				update_post_meta( $new_id, $key, maybe_unserialize( $values[0] ) );
			}
		}

		// Update the datetime
		update_post_meta( $new_id, 'parish_start_datetime', $new_datetime );

		// Make it non-recurring (one-off copy)
		update_post_meta( $new_id, 'parish_is_recurring', '0' );

		// Clear all caches
		$this->clear_all_caches();

		$new_post = get_post( $new_id );
		return rest_ensure_response( array(
			'success' => true,
			'message' => __( 'Event duplicated successfully.', 'parish-core' ),
			'event'   => $this->prepare_event_time_response( $new_post ),
		) );
	}

	/**
	 * Copy a week's schedule to another week.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function copy_week_schedule( WP_REST_Request $request ) {
		$source_start = $request->get_param( 'source_week_start' );
		$target_start = $request->get_param( 'target_week_start' );
		$church_id = $request->get_param( 'church_id' );

		// Validate dates
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $source_start ) ||
			 ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $target_start ) ) {
			return new WP_Error( 'invalid_date', __( 'Invalid date format.', 'parish-core' ), array( 'status' => 400 ) );
		}

		$source_date = new DateTime( $source_start );
		$target_date = new DateTime( $target_start );
		$days_diff = (int) $source_date->diff( $target_date )->days;
		if ( $source_date > $target_date ) {
			$days_diff = -$days_diff;
		}

		$source_end = ( clone $source_date )->modify( '+6 days' );

		// Get events from source week
		$filters = array();
		if ( $church_id > 0 ) {
			$filters['church_id'] = $church_id;
		}

		$generator = Parish_Event_Time_Generator::instance();
		$instances = $generator->generate(
			$source_date->format( 'Y-m-d' ),
			$source_end->format( 'Y-m-d' ),
			$filters
		);

		// Group by event_time_id to avoid duplicating recurring events multiple times
		$unique_event_ids = array();
		$created = 0;

		foreach ( $instances as $instance ) {
			$event_time_id = $instance['event_time_id'];

			// Skip if we've already processed this recurring event
			if ( isset( $unique_event_ids[ $event_time_id ] ) ) {
				continue;
			}
			$unique_event_ids[ $event_time_id ] = true;

			$original_post = get_post( $event_time_id );
			if ( ! $original_post ) {
				continue;
			}

			$meta = get_post_meta( $event_time_id );
			$original_datetime = $meta['parish_start_datetime'][0] ?? '';

			// Calculate new datetime
			$new_datetime = '';
			if ( ! empty( $original_datetime ) ) {
				try {
					$original_dt = new DateTime( $original_datetime );
					$original_dt->modify( "+{$days_diff} days" );
					$new_datetime = $original_dt->format( 'Y-m-d\TH:i:s' );
				} catch ( Exception $e ) {
					continue;
				}
			}

			// Create new post
			$new_id = wp_insert_post( array(
				'post_type'   => 'parish_event_time',
				'post_status' => 'publish',
				'post_title'  => $original_post->post_title,
			) );

			if ( is_wp_error( $new_id ) ) {
				continue;
			}

			// Copy all parish_ meta fields
			foreach ( $meta as $key => $values ) {
				if ( strpos( $key, 'parish_' ) === 0 ) {
					update_post_meta( $new_id, $key, maybe_unserialize( $values[0] ) );
				}
			}

			// Update datetime and make non-recurring
			update_post_meta( $new_id, 'parish_start_datetime', $new_datetime );
			update_post_meta( $new_id, 'parish_is_recurring', '0' );

			$created++;
		}

		// Clear all caches
		$this->clear_all_caches();

		return rest_ensure_response( array(
			'success' => true,
			'message' => sprintf(
				/* translators: %d: number of events */
				__( 'Copied %d event(s) to the new week.', 'parish-core' ),
				$created
			),
			'created' => $created,
		) );
	}

	/**
	 * Clear all events for a specific day.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response.
	 */
	public function clear_day( WP_REST_Request $request ) {
		$date = $request->get_param( 'date' );
		$church_id = $request->get_param( 'church_id' );
		$type = $request->get_param( 'type' );

		// Validate date
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			return new WP_Error( 'invalid_date', __( 'Invalid date format.', 'parish-core' ), array( 'status' => 400 ) );
		}

		$args = array(
			'post_type'      => 'parish_event_time',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => 'parish_start_datetime',
					'value'   => $date,
					'compare' => 'LIKE',
				),
				array(
					'key'     => 'parish_is_recurring',
					'value'   => '0',
					'compare' => '=',
				),
			),
		);

		if ( $church_id > 0 ) {
			$args['meta_query'][] = array(
				'key'     => 'parish_church_id',
				'value'   => $church_id,
				'compare' => '=',
				'type'    => 'NUMERIC',
			);
		}

		if ( 'all' !== $type && ! empty( $type ) ) {
			$args['meta_query'][] = array(
				'key'     => 'parish_event_type',
				'value'   => $type,
				'compare' => '=',
			);
		}

		$posts = get_posts( $args );
		$deleted = 0;

		foreach ( $posts as $post ) {
			wp_delete_post( $post->ID, true );
			$deleted++;
		}

		// Clear all caches
		$this->clear_all_caches();

		return rest_ensure_response( array(
			'success' => true,
			'message' => sprintf(
				/* translators: %d: number of events */
				__( 'Deleted %d non-recurring event(s).', 'parish-core' ),
				$deleted
			),
			'deleted' => $deleted,
			'note'    => __( 'Recurring events were not deleted. Edit or delete them individually.', 'parish-core' ),
		) );
	}
}

// Initialize
Parish_Event_Time_REST::instance();
