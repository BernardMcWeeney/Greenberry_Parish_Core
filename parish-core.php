<?php
/**
 * Plugin Name: Parish Core
 * Plugin URI: https://github.com/greenberry/parish-core
 * Description: A comprehensive parish management system for Catholic parishes.
 * Version: 9.1.0
 * Author: Greenberry
 * Author URI: https://greenberry.ie
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: parish-core
 * Domain Path: /languages
 * Requires at least: 6.6
 * Requires PHP: 8.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PARISH_CORE_VERSION', '9.1.0' );
define( 'PARISH_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'PARISH_CORE_URL', plugin_dir_url( __FILE__ ) );
define( 'PARISH_CORE_BASENAME', plugin_basename( __FILE__ ) );

function parish_core_check_requirements(): bool {
	$wp_version  = get_bloginfo( 'version' );
	$php_version = PHP_VERSION;
	$min_wp      = '6.6';
	$min_php     = '8.2';

	if ( version_compare( $wp_version, $min_wp, '<' ) || version_compare( $php_version, $min_php, '<' ) ) {
		add_action( 'admin_notices', function() use ( $min_wp, $min_php ) {
			echo '<div class="notice notice-error"><p>';
			printf(
				esc_html__( 'Parish Core requires WordPress %1$s+ and PHP %2$s+. Please upgrade.', 'parish-core' ),
				esc_html( $min_wp ),
				esc_html( $min_php )
			);
			echo '</p></div>';
		} );
		return false;
	}

	return true;
}

if ( ! parish_core_check_requirements() ) {
	return;
}

function parish_core_includes(): void {
	$includes = array(
		'class-parish-core.php',
		'class-parish-assets.php',
		'class-parish-blocks.php',
		'class-parish-block-bindings.php',
		'class-parish-auto-title.php',

		// CPT registries + templates.
		'cpt/class-parish-cpt-registry.php',
		'cpt/class-parish-meta-registry.php',
		'cpt/class-parish-cpt-templates.php',

		// Schedule system.
		'schedule/class-parish-schedule-generator.php',

		// Rosary classes.
		'class-parish-rosary-data.php',
		'class-parish-rosary-schedule.php',
		'class-parish-rosary-shortcodes.php',
		'class-parish-rosary-blocks.php',

		// Other modules you already have.
		'class-parish-rest-api.php',
		'class-parish-admin-ui.php',
		'class-parish-shortcodes.php',
		'class-parish-admin-colors.php',
		'class-parish-slider.php',
		'class-parish-readings.php',
		'class-parish-feast-days.php',
	);

	foreach ( $includes as $file ) {
		$filepath = PARISH_CORE_PATH . 'includes/' . $file;
		if ( file_exists( $filepath ) ) {
			require_once $filepath;
		}
	}
}

add_action( 'plugins_loaded', 'parish_core_includes', 5 );

function parish_core_init(): void {
	if ( class_exists( 'Parish_Core' ) ) {
		Parish_Core::instance();
	}
}

add_action( 'plugins_loaded', 'parish_core_init', 10 );

function parish_core_activate(): void {
	// Register CPTs + taxonomies + meta before flushing rewrites.
	if ( class_exists( 'Parish_CPT_Registry' ) ) {
		$registry = Parish_CPT_Registry::instance();
		$registry->register_post_types();
		$registry->register_taxonomies();
	}

	if ( class_exists( 'Parish_Meta_Registry' ) ) {
		Parish_Meta_Registry::instance()->register_all();
	}

	flush_rewrite_rules();

	$defaults = parish_core_get_default_settings();
	$existing = get_option( 'parish_core_settings', array() );

	if ( empty( $existing ) ) {
		update_option( 'parish_core_settings', $defaults );
	}

	set_transient( 'parish_core_activated', true, 60 );
}

register_activation_hook( __FILE__, 'parish_core_activate' );

function parish_core_deactivate(): void {
	flush_rewrite_rules();
	wp_clear_scheduled_hook( 'parish_fetch_readings_cron' );
	wp_clear_scheduled_hook( 'parish_cleanup_intentions' );
	wp_clear_scheduled_hook( 'parish_cleanup_overrides' );
}

register_deactivation_hook( __FILE__, 'parish_core_deactivate' );

/**
 * Sanitize embed HTML for livestream embeds.
 * Allows iframes from trusted video platforms only.
 *
 * @param string $html The embed HTML to sanitize.
 * @return string Sanitized HTML.
 */
function parish_sanitize_embed_html( string $html ): string {
	if ( empty( $html ) ) {
		return '';
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
		),
	);

	$sanitized = wp_kses( $html, $allowed_html );

	// Validate that iframe src is from trusted domains.
	if ( preg_match( '/src=["\']([^"\']+)["\']/', $sanitized, $matches ) ) {
		$src  = $matches[1];
		$host = wp_parse_url( $src, PHP_URL_HOST );

		$trusted_hosts = apply_filters(
			'parish_trusted_embed_hosts',
			array(
				'youtube.com',
				'www.youtube.com',
				'youtube-nocookie.com',
				'www.youtube-nocookie.com',
				'vimeo.com',
				'player.vimeo.com',
				'facebook.com',
				'www.facebook.com',
				'churchservices.tv',
				'www.churchservices.tv',
				'mcnmedia.tv',
				'www.mcnmedia.tv',
			)
		);

		$is_trusted = false;
		foreach ( $trusted_hosts as $trusted ) {
			if ( $host === $trusted || str_ends_with( $host, '.' . $trusted ) ) {
				$is_trusted = true;
				break;
			}
		}

		if ( ! $is_trusted ) {
			return '';
		}
	}

	return $sanitized;
}

/**
 * Valid weekday names for recurrence.
 */
define( 'PARISH_VALID_WEEKDAYS', array( 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' ) );

/**
 * Sanitize recurrence object for mass times.
 *
 * @param mixed $value The recurrence value to sanitize.
 * @return array Sanitized recurrence array.
 */
function parish_sanitize_recurrence( $value ): array {
	// Default structure
	$default = array(
		'type'         => 'weekly',
		'days'         => array(),
		'day_of_month' => null,
		'ordinal'      => 'first',
		'ordinal_day'  => 'Friday',
		'month'        => 1,
		'end_date'     => '',
	);

	// If not an array, return default
	if ( ! is_array( $value ) ) {
		return $default;
	}

	$sanitized = array();

	// Sanitize type
	$valid_types = array( 'daily', 'weekly', 'biweekly', 'monthly_day', 'monthly_ordinal', 'yearly' );
	$sanitized['type'] = isset( $value['type'] ) && in_array( $value['type'], $valid_types, true )
		? $value['type']
		: 'weekly';

	// Sanitize days array - CRITICAL: validate against actual weekday names
	$sanitized['days'] = array();
	if ( isset( $value['days'] ) && is_array( $value['days'] ) ) {
		foreach ( $value['days'] as $day ) {
			if ( is_string( $day ) ) {
				// Normalize: trim and capitalize first letter
				$normalized = ucfirst( strtolower( trim( $day ) ) );
				// Only include if it's a valid weekday name
				if ( in_array( $normalized, PARISH_VALID_WEEKDAYS, true ) ) {
					$sanitized['days'][] = $normalized;
				}
			}
		}
		// Remove duplicates and re-index
		$sanitized['days'] = array_values( array_unique( $sanitized['days'] ) );
	}

	// Sanitize day_of_month
	$sanitized['day_of_month'] = isset( $value['day_of_month'] ) ? absint( $value['day_of_month'] ) : null;
	if ( $sanitized['day_of_month'] < 1 || $sanitized['day_of_month'] > 31 ) {
		$sanitized['day_of_month'] = null;
	}

	// Sanitize ordinal
	$valid_ordinals = array( 'first', 'second', 'third', 'fourth', 'last' );
	$sanitized['ordinal'] = isset( $value['ordinal'] ) && in_array( $value['ordinal'], $valid_ordinals, true )
		? $value['ordinal']
		: 'first';

	// Sanitize ordinal_day
	$sanitized['ordinal_day'] = isset( $value['ordinal_day'] ) ? ucfirst( strtolower( trim( $value['ordinal_day'] ) ) ) : 'Friday';
	if ( ! in_array( $sanitized['ordinal_day'], PARISH_VALID_WEEKDAYS, true ) ) {
		$sanitized['ordinal_day'] = 'Friday';
	}

	// Sanitize month
	$sanitized['month'] = isset( $value['month'] ) ? absint( $value['month'] ) : 1;
	if ( $sanitized['month'] < 1 || $sanitized['month'] > 12 ) {
		$sanitized['month'] = 1;
	}

	// Sanitize end_date
	$sanitized['end_date'] = '';
	if ( isset( $value['end_date'] ) && is_string( $value['end_date'] ) && ! empty( $value['end_date'] ) ) {
		// Validate date format Y-m-d
		if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value['end_date'] ) ) {
			$sanitized['end_date'] = sanitize_text_field( $value['end_date'] );
		}
	}

	return $sanitized;
}

/**
 * Sanitize exception dates array for mass times.
 *
 * @param mixed $value The exception dates value to sanitize.
 * @return array Sanitized array of date strings.
 */
function parish_sanitize_exception_dates( $value ): array {
	if ( ! is_array( $value ) ) {
		return array();
	}

	$sanitized = array();
	foreach ( $value as $date ) {
		if ( is_string( $date ) && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			$sanitized[] = sanitize_text_field( $date );
		}
	}

	// Remove duplicates and re-index
	return array_values( array_unique( $sanitized ) );
}

/**
 * Schedule cleanup cron jobs for intentions and overrides.
 */
function parish_core_schedule_cleanup_crons(): void {
	// Schedule intention cleanup (daily at 3am).
	if ( ! wp_next_scheduled( 'parish_cleanup_intentions' ) ) {
		wp_schedule_event( strtotime( 'tomorrow 03:00:00' ), 'daily', 'parish_cleanup_intentions' );
	}

	// Schedule override cleanup (weekly on Sunday at 4am).
	if ( ! wp_next_scheduled( 'parish_cleanup_overrides' ) ) {
		wp_schedule_event( strtotime( 'next sunday 04:00:00' ), 'weekly', 'parish_cleanup_overrides' );
	}
}

add_action( 'init', 'parish_core_schedule_cleanup_crons' );

/**
 * Cleanup expired mass intentions.
 */
function parish_core_cleanup_intentions(): void {
	$expiry_days = apply_filters( 'parish_intention_expiry_days', 7 );
	$cutoff      = date( 'Y-m-d', strtotime( "-{$expiry_days} days" ) );

	$intentions = get_posts(
		array(
			'post_type'      => 'parish_intention',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'meta_query'     => array(
				array(
					'key'     => 'parish_intention_date',
					'value'   => $cutoff,
					'compare' => '<',
					'type'    => 'DATE',
				),
			),
			'fields'         => 'ids',
		)
	);

	foreach ( $intentions as $intention_id ) {
		wp_trash_post( $intention_id );
	}
}

add_action( 'parish_cleanup_intentions', 'parish_core_cleanup_intentions' );

/**
 * Cleanup expired schedule overrides.
 */
function parish_core_cleanup_overrides(): void {
	if ( class_exists( 'Parish_Schedule_Generator' ) ) {
		Parish_Schedule_Generator::instance()->cleanup_expired_overrides( 30 );
	}
}

add_action( 'parish_cleanup_overrides', 'parish_core_cleanup_overrides' );

/**
 * Run database migrations on version upgrade.
 */
function parish_core_maybe_migrate(): void {
	$db_version      = get_option( 'parish_core_db_version', '0' );
	$current_version = PARISH_CORE_VERSION;

	// Run migration 6.3.0: Update existing mass times with default livestream URL.
	if ( version_compare( $db_version, '6.3.0', '<' ) ) {
		parish_core_migrate_livestream_urls();
	}

	// One-time migration: convert legacy Parish News CPT posts to WordPress posts.
	if ( ! get_option( 'parish_core_news_posts_migrated', false ) ) {
		parish_core_migrate_news_posts_to_wp_posts();
	}

	// Update version if changed.
	if ( $db_version !== $current_version ) {
		update_option( 'parish_core_db_version', $current_version );
	}
}

add_action( 'admin_init', 'parish_core_maybe_migrate' );

/**
 * Migration: Update existing mass times with empty livestream URLs to use the default.
 */
function parish_core_migrate_livestream_urls(): void {
	$default_url = Parish_Core::get_setting( 'default_livestream_url', 'https://bohermeenparish.ie/online-live-mass/' );

	if ( empty( $default_url ) ) {
		return;
	}

	$args = array(
		'post_type'      => 'parish_mass_time',
		'posts_per_page' => -1,
		'post_status'    => 'any',
		'meta_query'     => array(
			'relation' => 'OR',
			array(
				'key'     => 'parish_mass_time_livestream_url',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => 'parish_mass_time_livestream_url',
				'value'   => '',
				'compare' => '=',
			),
		),
		'fields'         => 'ids',
	);

	$mass_times = get_posts( $args );

	foreach ( $mass_times as $mass_time_id ) {
		update_post_meta( $mass_time_id, 'parish_mass_time_livestream_url', $default_url );
	}

	// Log migration.
	if ( ! empty( $mass_times ) ) {
		error_log( sprintf( 'Parish Core: Migrated %d mass times with default livestream URL.', count( $mass_times ) ) );
	}
}

/**
 * One-time migration: convert legacy parish_news CPT to default WordPress posts.
 */
function parish_core_migrate_news_posts_to_wp_posts(): void {
	$news_posts = get_posts(
		array(
			'post_type'      => 'parish_news',
			'posts_per_page' => -1,
			'post_status'    => 'any',
			'fields'         => 'ids',
		)
	);

	if ( empty( $news_posts ) ) {
		update_option( 'parish_core_news_posts_migrated', true );
		return;
	}

	// Register temporary taxonomies for migration if they are missing.
	if ( ! taxonomy_exists( 'parish_news_category' ) ) {
		register_taxonomy( 'parish_news_category', 'parish_news', array( 'public' => false ) );
	}
	if ( ! taxonomy_exists( 'parish_news_tag' ) ) {
		register_taxonomy( 'parish_news_tag', 'parish_news', array( 'public' => false ) );
	}

	$migrated = 0;
	$errors   = 0;

	foreach ( $news_posts as $post_id ) {
		$category_ids = array();
		$tag_names    = array();

		$legacy_categories = wp_get_post_terms( $post_id, 'parish_news_category' );
		if ( is_array( $legacy_categories ) ) {
			foreach ( $legacy_categories as $term ) {
				$existing = term_exists( $term->name, 'category' );
				if ( ! $existing ) {
					$created = wp_insert_term( $term->name, 'category' );
					if ( ! is_wp_error( $created ) ) {
						$category_ids[] = (int) $created['term_id'];
					}
					continue;
				}

				$category_ids[] = (int) ( is_array( $existing ) ? ( $existing['term_id'] ?? 0 ) : $existing );
			}
		}

		$legacy_tags = wp_get_post_terms( $post_id, 'parish_news_tag' );
		if ( is_array( $legacy_tags ) ) {
			foreach ( $legacy_tags as $term ) {
				$tag_names[] = $term->name;
			}
		}

		$updated = wp_update_post(
			array(
				'ID'        => $post_id,
				'post_type' => 'post',
			),
			true
		);

		if ( is_wp_error( $updated ) ) {
			++$errors;
			continue;
		}

		if ( ! empty( $category_ids ) ) {
			wp_set_post_categories( $post_id, array_unique( array_filter( $category_ids ) ), false );
		}

		if ( ! empty( $tag_names ) ) {
			wp_set_post_tags( $post_id, array_unique( $tag_names ), false );
		}

		++$migrated;
	}

	update_option( 'parish_core_news_posts_migrated', true );

	if ( $migrated > 0 || $errors > 0 ) {
		error_log(
			sprintf(
				'Parish Core: Migrated %d parish_news posts to default posts (%d errors).',
				$migrated,
				$errors
			)
		);
	}
}

/**
 * Keep your existing default settings function unchanged
 * (you already have it below in your current file).
 */
function parish_core_get_default_settings(): array {
	return array(
		// Feature toggles (admin only).
		'enable_death_notices'    => true,
		'enable_baptism_notices'  => true,
		'enable_wedding_notices'  => true,
		'enable_churches'         => true,
		'enable_schools'          => true,
		'enable_cemeteries'       => true,
		'enable_groups'           => true,
		'enable_newsletters'      => true,
		'enable_news'             => true,
		'enable_gallery'          => true,
		'enable_reflections'      => true,
		'enable_events'           => true,
		'enable_liturgical'       => true,
		'enable_prayers'          => true,
		'enable_slider'           => true,
		'enable_travels'          => true,
		'enable_mass_times'       => true,

		// Readings API settings (admin only).
		'readings_api_key'        => '',
		'readings_schedules'      => '{}',

		// Feast Days settings.
		'feast_days_sync_enabled' => false,
		'feast_days_months_ahead' => 3,

		// Mass Times settings.
		'default_livestream_url'  => 'https://bohermeenparish.ie/online-live-mass/',

		// Admin Color settings.
		'admin_colors_enabled'     => false,
		'admin_color_menu_text'    => '#ffffff',
		'admin_color_base_menu'    => '#1d2327',
		'admin_color_highlight'    => '#2271b1',
		'admin_color_notification' => '#d63638',
		'admin_color_background'   => '#f0f0f1',
		'admin_color_links'        => '#2271b1',
		'admin_color_buttons'      => '#2271b1',
		'admin_color_form_inputs'  => '#2271b1',
		'menu_options'             => array(
			'menu_order' => array(
				'parish-core',
				'parish-about',
				'parish-events',
				'parish-mass-times',
				'parish-slider',
				'cpts',
				'parish-readings',
				'parish-settings',
				'remaining',
			),
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
		),

		// Parish Identity (About Parish - editor level).
		'parish_name'          => '',
		'parish_description'   => '',
		'parish_logo_id'       => 0,
		'parish_banner_id'     => 0,
		'parish_diocese_name'  => '',
		'parish_diocese_url'   => '',

		// Contact (About Parish).
		'parish_address'         => '',
		'parish_phone'           => '',
		'parish_email'           => '',
		'parish_office_hours'    => '',
		'parish_emergency_phone' => '',

		// Social Links (About Parish).
		'parish_website'    => '',
		'parish_facebook'   => '',
		'parish_twitter'    => '',
		'parish_instagram'  => '',
		'parish_youtube'    => '',
		'parish_livestream' => '',
		'parish_donate'     => '',

		// Clergy & Staff (About Parish) - JSON array.
		'parish_clergy' => '[]',

		// Resources (About Parish) - JSON array.
		'parish_resources' => '[]',

		// Quick Actions (About Parish) - JSON array.
		'parish_quick_actions' => '[]',

		// Events (stored separately as JSON).
		'parish_events' => '[]',
	);
}

/**
 * Get all available shortcodes and blocks with their documentation.
 * This is used by the Shortcode Reference tab.
 */
function parish_core_get_shortcode_reference(): array {
	return array(
		// =========================================================================
		// PARISH CONTENT SHORTCODES
		// =========================================================================
		array(
			'shortcode'   => '[parish_events]',
			'name'        => __( 'Events List', 'parish-core' ),
			'description' => __( 'Display upcoming parish events with filtering options', 'parish-core' ),
			'type'        => 'shortcode',
			'attributes'  => array(
				'limit' => __( 'Number of events to show (default: 10)', 'parish-core' ),
				'type'  => __( 'Filter by type: "parish", "sacrament", "feast"', 'parish-core' ),
				'month' => __( 'Filter by month number (1-12)', 'parish-core' ),
				'year'  => __( 'Filter by year (e.g., "2026")', 'parish-core' ),
				'past'  => __( '"yes" to show past events instead of upcoming', 'parish-core' ),
			),
			'example'     => '[parish_events limit="5" type="sacrament"]',
			'feature'     => 'events',
		),
		array(
			'shortcode'   => '[parish_reflection]',
			'name'        => __( 'Latest Reflection', 'parish-core' ),
			'description' => __( 'Display the most recent published reflection', 'parish-core' ),
			'type'        => 'shortcode',
			'attributes'  => array(),
			'example'     => '[parish_reflection]',
			'feature'     => 'reflections',
		),
		array(
			'shortcode'   => '[parish_churches]',
			'name'        => __( 'Churches List', 'parish-core' ),
			'description' => __( 'Display all parish churches with their addresses', 'parish-core' ),
			'type'        => 'shortcode',
			'attributes'  => array(),
			'example'     => '[parish_churches]',
			'feature'     => 'churches',
		),
		array(
			'shortcode'   => '[parish_clergy]',
			'name'        => __( 'Clergy & Staff', 'parish-core' ),
			'description' => __( 'Display clergy and staff list from settings', 'parish-core' ),
			'type'        => 'shortcode',
			'attributes'  => array(),
			'example'     => '[parish_clergy]',
			'feature'     => null,
		),
		array(
			'shortcode'   => '[parish_contact]',
			'name'        => __( 'Contact Information', 'parish-core' ),
			'description' => __( 'Display parish contact details (address, phone, email, office hours)', 'parish-core' ),
			'type'        => 'shortcode',
			'attributes'  => array(),
			'example'     => '[parish_contact]',
			'feature'     => null,
		),
		array(
			'shortcode'   => '[parish_prayers]',
			'name'        => __( 'Prayer Directory', 'parish-core' ),
			'description' => __( 'Display published prayers with titles and content', 'parish-core' ),
			'type'        => 'shortcode',
			'attributes'  => array(
				'limit'   => __( 'Number of prayers to show (-1 for all)', 'parish-core' ),
				'orderby' => __( 'Order by: "title", "date", or "rand"', 'parish-core' ),
			),
			'example'     => '[parish_prayers limit="5" orderby="title"]',
			'feature'     => 'prayers',
		),
		array(
			'shortcode'   => '[parish_slider]',
			'name'        => __( 'Hero Slider', 'parish-core' ),
			'description' => __( 'Display the homepage hero slider with manual and dynamic slides', 'parish-core' ),
			'type'        => 'shortcode',
			'attributes'  => array(
				'class' => __( 'Additional CSS class for custom styling', 'parish-core' ),
			),
			'example'     => '[parish_slider class="my-custom-slider"]',
			'feature'     => 'slider',
		),

		// =========================================================================
		// MASS TIMES & SCHEDULE SHORTCODES
		// =========================================================================
		array(
			'shortcode'   => '[parish_today_widget]',
			'name'        => __( 'Today\'s Schedule Widget', 'parish-core' ),
			'description' => __( 'Compact widget showing Mass Times grouped by church for a single day', 'parish-core' ),
			'type'        => 'shortcode',
			'attributes'  => array(
				'date'       => __( 'Date in YYYY-MM-DD format (default: today)', 'parish-core' ),
				'church_id'  => __( 'Filter by specific church ID', 'parish-core' ),
				'type'       => __( 'Filter by type: "mass", "confession", "adoration", "rosary"', 'parish-core' ),
				'show_notes' => __( '"yes" to show notes (default: no)', 'parish-core' ),
			),
			'example'     => '[parish_today_widget type="mass" show_notes="yes"]',
			'feature'     => 'mass_times',
		),
		array(
			'shortcode'   => '[parish_church_schedule]',
			'name'        => __( 'Church Weekly Schedule', 'parish-core' ),
			'description' => __( 'Weekly schedule for a specific church grouped by day with special events section', 'parish-core' ),
			'type'        => 'shortcode',
			'attributes'  => array(
				'church_id'       => __( 'Church ID (auto-detects on church single pages and query loops)', 'parish-core' ),
				'type'            => __( 'Filter by type: "mass", "confession", "adoration"', 'parish-core' ),
				'show_special'    => __( '"yes" or "no" to show special events (default: yes)', 'parish-core' ),
				'show_livestream' => __( '"yes" or "no" to show livestream icons (default: yes)', 'parish-core' ),
			),
			'example'     => '[parish_church_schedule]',
			'feature'     => 'mass_times',
		),
		array(
			'shortcode'   => '[parish_schedule]',
			'name'        => __( 'Schedule View', 'parish-core' ),
			'description' => __( 'Display Mass Times schedule for multiple days in various formats', 'parish-core' ),
			'type'        => 'shortcode',
			'attributes'  => array(
				'days'            => __( 'Number of days to show (default: 7)', 'parish-core' ),
				'church_id'       => __( 'Filter by specific church ID', 'parish-core' ),
				'event_type'      => __( 'Filter by event type', 'parish-core' ),
				'format'          => __( 'Display format: "list", "table", "cards", or "simple"', 'parish-core' ),
				'show_feast_day'  => __( '"yes" or "no" to show feast day info (default: yes)', 'parish-core' ),
				'show_livestream' => __( '"yes" or "no" to show livestream badges (default: yes)', 'parish-core' ),
			),
			'example'     => '[parish_schedule days="7" format="cards"]',
			'feature'     => 'mass_times',
		),
		array(
			'shortcode'   => '[parish_weekly_schedule]',
			'name'        => __( 'Weekly Schedule', 'parish-core' ),
			'description' => __( 'Full week schedule grouped by day with today highlighted', 'parish-core' ),
			'type'        => 'shortcode',
			'attributes'  => array(
				'church_id'       => __( 'Filter by specific church ID', 'parish-core' ),
				'event_type'      => __( 'Filter by event type', 'parish-core' ),
				'show_feast_day'  => __( '"yes" or "no" (default: yes)', 'parish-core' ),
				'show_livestream' => __( '"yes" or "no" (default: yes)', 'parish-core' ),
			),
			'example'     => '[parish_weekly_schedule church_id="123"]',
			'feature'     => 'mass_times',
		),
		array(
			'shortcode'   => '[parish_today_schedule]',
			'name'        => __( 'Today\'s Schedule', 'parish-core' ),
			'description' => __( 'Today\'s schedule with feast day header and all events', 'parish-core' ),
			'type'        => 'shortcode',
			'attributes'  => array(
				'church_id'       => __( 'Filter by specific church ID', 'parish-core' ),
				'event_type'      => __( 'Filter by event type', 'parish-core' ),
				'show_feast_day'  => __( '"yes" or "no" (default: yes)', 'parish-core' ),
				'show_livestream' => __( '"yes" or "no" (default: yes)', 'parish-core' ),
			),
			'example'     => '[parish_today_schedule show_feast_day="yes"]',
			'feature'     => 'mass_times',
		),

		// =========================================================================
		// LITURGICAL & READINGS SHORTCODES
		// =========================================================================
		array(
			'shortcode'   => '[daily_readings]',
			'name'        => __( 'Daily Readings', 'parish-core' ),
			'description' => __( 'Display today\'s Mass readings from the Readings API', 'parish-core' ),
			'type'        => 'shortcode',
			'attributes'  => array(),
			'example'     => '[daily_readings]',
			'feature'     => 'liturgical',
		),
		array(
			'shortcode'   => '[mass_reading_details]',
			'name'        => __( 'Mass Reading Details', 'parish-core' ),
			'description' => __( 'Display structured Mass readings (First Reading, Psalm, Second Reading, Gospel)', 'parish-core' ),
			'type'        => 'shortcode',
			'attributes'  => array(),
			'example'     => '[mass_reading_details]',
			'feature'     => 'liturgical',
		),
		array(
			'shortcode'   => '[sunday_homily]',
			'name'        => __( 'Sunday Homily', 'parish-core' ),
			'description' => __( 'Display Sunday homily notes from the Readings API', 'parish-core' ),
			'type'        => 'shortcode',
			'attributes'  => array(),
			'example'     => '[sunday_homily]',
			'feature'     => 'liturgical',
		),
		array(
			'shortcode'   => '[saint_of_the_day]',
			'name'        => __( 'Saint of the Day', 'parish-core' ),
			'description' => __( 'Display information about today\'s saint', 'parish-core' ),
			'type'        => 'shortcode',
			'attributes'  => array(),
			'example'     => '[saint_of_the_day]',
			'feature'     => 'liturgical',
		),
		array(
			'shortcode'   => '[next_sunday_reading]',
			'name'        => __( 'Next Sunday Reading', 'parish-core' ),
			'description' => __( 'Display next Sunday\'s readings in English', 'parish-core' ),
			'type'        => 'shortcode',
			'attributes'  => array(),
			'example'     => '[next_sunday_reading]',
			'feature'     => 'liturgical',
		),
		array(
			'shortcode'   => '[next_sunday_reading_irish]',
			'name'        => __( 'Next Sunday Reading (Irish)', 'parish-core' ),
			'description' => __( 'Display next Sunday\'s readings in Irish (Gaeilge)', 'parish-core' ),
			'type'        => 'shortcode',
			'attributes'  => array(),
			'example'     => '[next_sunday_reading_irish]',
			'feature'     => 'liturgical',
		),
		array(
			'shortcode'   => '[daily_readings_irish]',
			'name'        => __( 'Daily Readings (Irish)', 'parish-core' ),
			'description' => __( 'Display today\'s readings in Irish (Gaeilge)', 'parish-core' ),
			'type'        => 'shortcode',
			'attributes'  => array(),
			'example'     => '[daily_readings_irish]',
			'feature'     => 'liturgical',
		),
		array(
			'shortcode'   => '[feast_day_details]',
			'name'        => __( 'Feast Day Details', 'parish-core' ),
			'description' => __( 'Display today\'s liturgical feast with colored indicator and rank', 'parish-core' ),
			'type'        => 'shortcode',
			'attributes'  => array(),
			'example'     => '[feast_day_details]',
			'feature'     => 'liturgical',
		),
		array(
			'shortcode'   => '[liturgical_day]',
			'name'        => __( 'Liturgical Day', 'parish-core' ),
			'description' => __( 'Display liturgical information for today (season, cycles, rosary)', 'parish-core' ),
			'type'        => 'shortcode',
			'attributes'  => array(
				'link_rosary' => __( 'URL to rosary page for linking (e.g., "/rosary")', 'parish-core' ),
			),
			'example'     => '[liturgical_day link_rosary="/rosary"]',
			'feature'     => 'liturgical',
		),
		array(
			'shortcode'   => '[liturgical_week]',
			'name'        => __( 'Liturgical Week', 'parish-core' ),
			'description' => __( 'Display liturgical information for the week with rosary schedule', 'parish-core' ),
			'type'        => 'shortcode',
			'attributes'  => array(
				'link_rosary' => __( 'URL to rosary page for linking', 'parish-core' ),
			),
			'example'     => '[liturgical_week link_rosary="/rosary"]',
			'feature'     => 'liturgical',
		),

		// =========================================================================
		// ROSARY SHORTCODES
		// =========================================================================
		array(
			'shortcode'   => '[rosary_today]',
			'name'        => __( 'Today\'s Rosary', 'parish-core' ),
			'description' => __( 'Display today\'s rosary mystery set with date and list of mysteries', 'parish-core' ),
			'type'        => 'shortcode',
			'attributes'  => array(
				'show_date'   => __( '"yes" or "no" to show current date (default: yes)', 'parish-core' ),
				'show_season' => __( '"yes" or "no" to show liturgical season note (default: no)', 'parish-core' ),
				'force_day'   => __( 'Force a specific day: "mon", "tue", "wed", "thu", "fri", "sat", "sun"', 'parish-core' ),
				'force_set'   => __( 'Force a specific set: "joyful", "sorrowful", "glorious", "luminous"', 'parish-core' ),
			),
			'example'     => '[rosary_today show_date="yes" show_season="no"]',
			'feature'     => 'rosary',
		),
		array(
			'shortcode'   => '[rosary_full]',
			'name'        => __( 'Full Rosary', 'parish-core' ),
			'description' => __( 'Display full rosary mysteries with detailed meditations, scriptures, and fruits', 'parish-core' ),
			'type'        => 'shortcode',
			'attributes'  => array(
				'set'             => __( 'Specific set: "joyful", "sorrowful", "glorious", "luminous" (default: today\'s)', 'parish-core' ),
				'show_fruit'      => __( '"yes" or "no" to show spiritual fruit (default: yes)', 'parish-core' ),
				'show_scripture'  => __( '"yes" or "no" to show scripture references (default: yes)', 'parish-core' ),
				'show_meditation' => __( '"yes" or "no" to show meditation text (default: yes)', 'parish-core' ),
				'show_quote'      => __( '"yes" or "no" to show scripture quote (default: yes)', 'parish-core' ),
				'force_day'       => __( 'Force a specific day for testing', 'parish-core' ),
				'force_set'       => __( 'Force a specific set (overrides day)', 'parish-core' ),
			),
			'example'     => '[rosary_full set="joyful" show_fruit="yes"]',
			'feature'     => 'rosary',
		),
		array(
			'shortcode'   => '[rosary_days]',
			'name'        => __( 'Rosary Days', 'parish-core' ),
			'description' => __( 'Display which days of the week each rosary series is prayed', 'parish-core' ),
			'type'        => 'shortcode',
			'attributes'  => array(
				'link_rosary' => __( 'URL to rosary page for linking', 'parish-core' ),
			),
			'example'     => '[rosary_days link_rosary="/rosary"]',
			'feature'     => 'liturgical',
		),
		array(
			'shortcode'   => '[rosary_week]',
			'name'        => __( 'Weekly Rosary Schedule', 'parish-core' ),
			'description' => __( 'Display rosary mysteries for each day of the week in table or list format', 'parish-core' ),
			'type'        => 'shortcode',
			'attributes'  => array(
				'link'       => __( 'URL to rosary page for linking', 'parish-core' ),
				'show_today' => __( '"yes" or "no" to highlight today (default: yes)', 'parish-core' ),
				'format'     => __( 'Display format: "table" or "list" (default: table)', 'parish-core' ),
			),
			'example'     => '[rosary_week link="/rosary" format="table"]',
			'feature'     => 'liturgical',
		),
		array(
			'shortcode'   => '[rosary_series]',
			'name'        => __( 'Rosary Series Overview', 'parish-core' ),
			'description' => __( 'Display all four rosary series with days prayed during current liturgical season', 'parish-core' ),
			'type'        => 'shortcode',
			'attributes'  => array(
				'link'   => __( 'URL to rosary page for linking', 'parish-core' ),
				'series' => __( 'Show specific series: "joyful", "sorrowful", "glorious", "luminous"', 'parish-core' ),
			),
			'example'     => '[rosary_series link="/rosary"]',
			'feature'     => 'liturgical',
		),
		array(
			'shortcode'   => '[rosary_mysteries]',
			'name'        => __( 'Rosary Mysteries List', 'parish-core' ),
			'description' => __( 'Display the five mysteries of each rosary series (ideal for dedicated rosary pages)', 'parish-core' ),
			'type'        => 'shortcode',
			'attributes'  => array(
				'series' => __( 'Specific series or leave empty for all four', 'parish-core' ),
			),
			'example'     => '[rosary_mysteries series="joyful"]',
			'feature'     => 'liturgical',
		),

		// =========================================================================
		// GUTENBERG BLOCKS
		// =========================================================================
		array(
			'shortcode'   => 'parish/rosary-today',
			'name'        => __( 'Rosary Today Block', 'parish-core' ),
			'description' => __( 'Gutenberg block to display today\'s rosary mysteries with configurable options', 'parish-core' ),
			'type'        => 'block',
			'attributes'  => array(
				'showDate'   => __( 'Show current date (default: true)', 'parish-core' ),
				'showSeason' => __( 'Show liturgical season note (default: false)', 'parish-core' ),
			),
			'example'     => __( 'Add via Block Editor: Parish > Rosary Today', 'parish-core' ),
			'feature'     => 'rosary',
		),
		array(
			'shortcode'   => 'parish/rosary-full',
			'name'        => __( 'Full Rosary Block', 'parish-core' ),
			'description' => __( 'Gutenberg block for full rosary with meditations, scriptures, and configurable content', 'parish-core' ),
			'type'        => 'block',
			'attributes'  => array(
				'mysterySet'     => __( 'Specific mystery set or auto-detect from today', 'parish-core' ),
				'showFruit'      => __( 'Show spiritual fruit (default: true)', 'parish-core' ),
				'showScripture'  => __( 'Show scripture references (default: true)', 'parish-core' ),
				'showMeditation' => __( 'Show meditation text (default: true)', 'parish-core' ),
				'showQuote'      => __( 'Show scripture quote (default: true)', 'parish-core' ),
			),
			'example'     => __( 'Add via Block Editor: Parish > Full Rosary', 'parish-core' ),
			'feature'     => 'rosary',
		),
		array(
			'shortcode'   => 'parish/events',
			'name'        => __( 'Events Block', 'parish-core' ),
			'description' => __( 'Display parish events with auto-detection of church/cemetery context', 'parish-core' ),
			'type'        => 'block',
			'attributes'  => array(
				'view'       => __( 'View mode: "upcoming", "today", or "week" (default: upcoming)', 'parish-core' ),
				'limit'      => __( 'Number of events to show (default: 5)', 'parish-core' ),
				'sacrament'  => __( 'Filter by sacrament slug (e.g., "baptism", "marriage")', 'parish-core' ),
				'churchId'   => __( 'Filter by church ID (0 = all or auto-detect)', 'parish-core' ),
				'cemeteryId' => __( 'Filter by cemetery ID (0 = all or auto-detect)', 'parish-core' ),
				'autoDetect' => __( 'Auto-detect church/cemetery from page context (default: true)', 'parish-core' ),
				'showIcon'   => __( 'Show calendar icon (default: true)', 'parish-core' ),
				'iconColor'  => __( 'Custom icon color (hex)', 'parish-core' ),
				'timeColor'  => __( 'Custom time/date color (hex)', 'parish-core' ),
			),
			'example'     => __( 'Add via Block Editor: Parish > Events', 'parish-core' ),
			'feature'     => 'events',
		),
		array(
			'shortcode'   => 'parish/events-calendar',
			'name'        => __( 'Events Calendar Block', 'parish-core' ),
			'description' => __( 'Full month calendar with iCal subscription and download support', 'parish-core' ),
			'type'        => 'block',
			'attributes'  => array(
				'sacrament'     => __( 'Filter by sacrament slug', 'parish-core' ),
				'churchId'      => __( 'Filter by church ID (0 = all or auto-detect)', 'parish-core' ),
				'cemeteryId'    => __( 'Filter by cemetery ID (0 = all or auto-detect)', 'parish-core' ),
				'autoDetect'    => __( 'Auto-detect church/cemetery from page context (default: true)', 'parish-core' ),
				'showSubscribe' => __( 'Show iCal/Google Calendar subscribe buttons (default: true)', 'parish-core' ),
				'showDownload'  => __( 'Show .ics download button (default: true)', 'parish-core' ),
				'iconColor'     => __( 'Custom accent color (hex)', 'parish-core' ),
				'timeColor'     => __( 'Custom time color (hex)', 'parish-core' ),
			),
			'example'     => __( 'Add via Block Editor: Parish > Events Calendar', 'parish-core' ),
			'feature'     => 'events',
		),
		array(
			'shortcode'   => 'parish/events-list',
			'name'        => __( 'Events List Block', 'parish-core' ),
			'description' => __( 'Full events listing with search, filters, list/grid toggle, and pagination', 'parish-core' ),
			'type'        => 'block',
			'attributes'  => array(
				'showSearch'        => __( 'Show search bar (default: true)', 'parish-core' ),
				'showFilters'       => __( 'Show filter dropdowns (default: true)', 'parish-core' ),
				'showLayoutToggle'  => __( 'Show list/grid toggle (default: true)', 'parish-core' ),
				'filterBySacrament' => __( 'Pre-filter by sacrament slug', 'parish-core' ),
				'filterByChurch'    => __( 'Pre-filter by church ID', 'parish-core' ),
				'limit'             => __( 'Events per page (default: 10)', 'parish-core' ),
				'layout'            => __( 'Default layout: "list" or "grid"', 'parish-core' ),
				'showPagination'    => __( 'Show pagination (default: true)', 'parish-core' ),
			),
			'example'     => __( 'Add via Block Editor: Parish > Events List', 'parish-core' ),
			'feature'     => 'events',
		),
		array(
			'shortcode'   => 'parish/sacrament-events',
			'name'        => __( 'Sacrament Events Block', 'parish-core' ),
			'description' => __( 'Display upcoming events filtered by a specific sacrament (e.g., Baptisms, Confirmations)', 'parish-core' ),
			'type'        => 'block',
			'attributes'  => array(
				'sacrament'       => __( 'Sacrament slug to filter by (required)', 'parish-core' ),
				'churchId'        => __( 'Filter by church ID', 'parish-core' ),
				'limit'           => __( 'Number of events (default: 5)', 'parish-core' ),
				'showIcon'        => __( 'Show date icon (default: true)', 'parish-core' ),
				'showChurch'      => __( 'Show church name (default: true)', 'parish-core' ),
				'showDescription' => __( 'Show event excerpt (default: false)', 'parish-core' ),
				'emptyMessage'    => __( 'Custom message when no events', 'parish-core' ),
			),
			'example'     => __( 'Add via Block Editor: Parish > Sacrament Events', 'parish-core' ),
			'feature'     => 'events',
		),
		array(
			'shortcode'   => 'parish/mass-schedule',
			'name'        => __( 'Mass Schedule Block', 'parish-core' ),
			'description' => __( 'Display weekly Mass schedule for a church. Works in Query Loops and single church pages.', 'parish-core' ),
			'type'        => 'block',
			'attributes'  => array(
				'showIcon'       => __( 'Show clock icon (default: true)', 'parish-core' ),
				'showLivestream' => __( 'Show livestream indicators (default: true)', 'parish-core' ),
				'showSpecial'    => __( 'Show special events section (default: true)', 'parish-core' ),
				'eventType'      => __( 'Filter by event type (e.g., "mass")', 'parish-core' ),
				'showAllDays'    => __( 'Show all days even if empty (default: true)', 'parish-core' ),
				'iconColor'      => __( 'Custom icon color (hex)', 'parish-core' ),
				'timeColor'      => __( 'Custom time color (hex)', 'parish-core' ),
			),
			'example'     => __( 'Add via Block Editor: Parish > Mass Schedule', 'parish-core' ),
			'feature'     => 'mass_times',
		),
		array(
			'shortcode'   => 'parish/church-schedule',
			'name'        => __( 'Church Schedule Block', 'parish-core' ),
			'description' => __( 'Flexible church schedule block with multiple event types and format options', 'parish-core' ),
			'type'        => 'block',
			'attributes'  => array(
				'format'         => __( 'Display format (default: list)', 'parish-core' ),
				'eventTypes'     => __( 'Array of event types to show (default: mass, confession)', 'parish-core' ),
				'showFeastDay'   => __( 'Show feast day info (default: true)', 'parish-core' ),
				'days'           => __( 'Number of days to show (default: 7)', 'parish-core' ),
				'showIcon'       => __( 'Show clock icon (default: true)', 'parish-core' ),
				'showLivestream' => __( 'Show livestream indicators (default: true)', 'parish-core' ),
				'groupByDay'     => __( 'Group events by day (default: true)', 'parish-core' ),
				'showAllDays'    => __( 'Show all days even if empty (default: true)', 'parish-core' ),
			),
			'example'     => __( 'Add via Block Editor: Parish > Church Schedule', 'parish-core' ),
			'feature'     => 'mass_times',
		),
		array(
			'shortcode'   => 'parish/today-mass',
			'name'        => __( 'Today\'s Mass Block', 'parish-core' ),
			'description' => __( 'Display today\'s Mass times grouped by church with livestream indicators', 'parish-core' ),
			'type'        => 'block',
			'attributes'  => array(
				'churchId'  => __( 'Filter by church ID (0 for all)', 'parish-core' ),
				'eventType' => __( 'Filter by type (default: mass)', 'parish-core' ),
				'showIcon'  => __( 'Show video icon for livestreams (default: true)', 'parish-core' ),
				'showDate'  => __( 'Show date header (default: true)', 'parish-core' ),
				'showNotes' => __( 'Show event notes (default: false)', 'parish-core' ),
				'iconColor' => __( 'Custom icon color (hex)', 'parish-core' ),
				'timeColor' => __( 'Custom time color (hex)', 'parish-core' ),
			),
			'example'     => __( 'Add via Block Editor: Parish > Today\'s Mass', 'parish-core' ),
			'feature'     => 'mass_times',
		),
		array(
			'shortcode'   => 'parish/church-selector',
			'name'        => __( 'Church Selector Block', 'parish-core' ),
			'description' => __( 'Editor block to select and display related church for a post', 'parish-core' ),
			'type'        => 'block',
			'attributes'  => array(),
			'example'     => __( 'Add via Block Editor: Parish > Church Selector', 'parish-core' ),
			'feature'     => 'churches',
		),
		array(
			'shortcode'   => 'parish/slider',
			'name'        => __( 'Hero Slider Block', 'parish-core' ),
			'description' => __( 'Gutenberg block for the hero slider with manual and dynamic slides', 'parish-core' ),
			'type'        => 'block',
			'attributes'  => array(),
			'example'     => __( 'Add via Block Editor: Parish > Hero Slider', 'parish-core' ),
			'feature'     => 'slider',
		),
	);
}
