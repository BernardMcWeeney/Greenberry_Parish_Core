<?php
/**
 * Plugin Name: Parish Core
 * Plugin URI: https://github.com/greenberry/parish-core
 * Description: A comprehensive parish management system for Catholic parishes.
 * Version: 3.0.0
 * Author: Greenberry
 * Author URI: https://greenberry.ie
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: parish-core
 * Domain Path: /languages
 * Requires at least: 6.5
 * Requires PHP: 8.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
define( 'PARISH_CORE_VERSION', '3.0.0' );
define( 'PARISH_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'PARISH_CORE_URL', plugin_dir_url( __FILE__ ) );
define( 'PARISH_CORE_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Check minimum requirements.
 */
function parish_core_check_requirements(): bool {
	$wp_version  = get_bloginfo( 'version' );
	$php_version = PHP_VERSION;
	$min_wp      = '6.5';
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
		});
		return false;
	}
	return true;
}

if ( ! parish_core_check_requirements() ) {
	return;
}

/**
 * Include required files.
 */
function parish_core_includes(): void {
	$includes = array(
		'class-parish-core.php',
		'class-parish-blocks.php',
		'class-parish-cpt-templates.php',
		'class-parish-cpt.php',
		'class-parish-meta.php',
		'class-parish-rest-api.php',
		'class-parish-admin-ui.php',
		'class-parish-shortcodes.php',
		'class-parish-assets.php',
		'class-parish-readings.php',
		'class-parish-admin-colors.php',
	);

	foreach ( $includes as $file ) {
		$filepath = PARISH_CORE_PATH . 'includes/' . $file;
		if ( file_exists( $filepath ) ) {
			require_once $filepath;
		}
	}
}

add_action( 'plugins_loaded', 'parish_core_includes', 5 );

/**
 * Initialize the plugin.
 */
function parish_core_init(): void {
	if ( class_exists( 'Parish_Core' ) ) {
		Parish_Core::instance();
	}
}

add_action( 'plugins_loaded', 'parish_core_init', 10 );

/**
 * Activation hook.
 */
function parish_core_activate(): void {
	// Ensure CPTs are registered.
	if ( class_exists( 'Parish_CPT' ) ) {
		$cpt = Parish_CPT::instance();
		$cpt->register_post_types();
		$cpt->register_taxonomies();
	}

	// Flush rewrite rules.
	flush_rewrite_rules();

	// Set default settings.
	$defaults = parish_core_get_default_settings();
	$existing = get_option( 'parish_core_settings', array() );
	
	if ( empty( $existing ) ) {
		update_option( 'parish_core_settings', $defaults );
	}

	// Set activation transient.
	set_transient( 'parish_core_activated', true, 60 );
}

register_activation_hook( __FILE__, 'parish_core_activate' );

/**
 * Deactivation hook.
 */
function parish_core_deactivate(): void {
	flush_rewrite_rules();
	
	// Clear scheduled events.
	wp_clear_scheduled_hook( 'parish_fetch_readings_cron' );
}

register_deactivation_hook( __FILE__, 'parish_core_deactivate' );

/**
 * Get default plugin settings.
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
		'enable_mass_times'       => true,
		'enable_events'           => true,
		'enable_liturgical'       => true,
		'enable_prayers'          => true,

		// Readings API settings (admin only).
		'readings_api_key'        => '',
		'readings_schedules'      => '{}',

		// Admin Color settings.
		'admin_colors_enabled'    => false,
		'admin_color_menu_text'   => '#ffffff',
		'admin_color_base_menu'   => '#1d2327',
		'admin_color_highlight'   => '#2271b1',
		'admin_color_notification' => '#d63638',
		'admin_color_background'  => '#f0f0f1',
		'admin_color_links'       => '#2271b1',
		'admin_color_buttons'     => '#2271b1',
		'admin_color_form_inputs' => '#2271b1',

		// Parish Identity (About Parish - editor level).
		'parish_name'             => '',
		'parish_description'      => '',
		'parish_logo_id'          => 0,
		'parish_banner_id'        => 0,
		'parish_diocese_name'     => '',
		'parish_diocese_url'      => '',

		// Contact (About Parish).
		'parish_address'          => '',
		'parish_phone'            => '',
		'parish_email'            => '',
		'parish_office_hours'     => '',
		'parish_emergency_phone'  => '',

		// Social Links (About Parish).
		'parish_website'          => '',
		'parish_facebook'         => '',
		'parish_twitter'          => '',
		'parish_instagram'        => '',
		'parish_youtube'          => '',
		'parish_livestream'       => '',
		'parish_donate'           => '',

		// Clergy & Staff (About Parish) - JSON array.
		'parish_clergy'           => '[]',

		// Resources (About Parish) - JSON array.
		'parish_resources'        => '[]',

		// Quick Actions (About Parish) - JSON array.
		'parish_quick_actions'    => '[]',

		// Mass Times (stored separately as JSON).
		'mass_times'              => '[]',

		// Events (stored separately as JSON).
		'parish_events'           => '[]',
	);
}

/**
 * Get all available shortcodes with their documentation.
 * This is used by the Shortcode Reference tab.
 */
function parish_core_get_shortcode_reference(): array {
	return array(
		// Mass Times
		array(
			'shortcode'   => '[parish_mass_times]',
			'name'        => __( 'Mass Times', 'parish-core' ),
			'description' => __( 'Display weekly mass times schedule', 'parish-core' ),
			'attributes'  => array(
				'day'             => __( 'Filter by day (e.g., "Sunday", "Monday")', 'parish-core' ),
				'church_id'       => __( 'Filter by church ID', 'parish-core' ),
				'show_livestream' => __( '"yes" to show only livestreamed masses', 'parish-core' ),
				'format'          => __( '"daily", "weekly", or "simple"', 'parish-core' ),
			),
			'example'     => '[parish_mass_times day="Sunday" format="simple"]',
			'feature'     => 'mass_times',
		),
		// Events
		array(
			'shortcode'   => '[parish_events]',
			'name'        => __( 'Events', 'parish-core' ),
			'description' => __( 'Display upcoming parish events', 'parish-core' ),
			'attributes'  => array(
				'limit' => __( 'Number of events to show (default: 10)', 'parish-core' ),
				'type'  => __( 'Filter by type: "parish", "sacrament", "feast"', 'parish-core' ),
				'month' => __( 'Filter by month (1-12)', 'parish-core' ),
				'year'  => __( 'Filter by year (e.g., "2024")', 'parish-core' ),
				'past'  => __( '"yes" to show past events', 'parish-core' ),
			),
			'example'     => '[parish_events limit="5" type="sacrament"]',
			'feature'     => 'events',
		),
		// Reflection
		array(
			'shortcode'   => '[parish_reflection]',
			'name'        => __( 'Latest Reflection', 'parish-core' ),
			'description' => __( 'Display the most recent reflection', 'parish-core' ),
			'attributes'  => array(),
			'example'     => '[parish_reflection]',
			'feature'     => 'reflections',
		),
		// Churches
		array(
			'shortcode'   => '[parish_churches]',
			'name'        => __( 'Churches List', 'parish-core' ),
			'description' => __( 'Display all parish churches', 'parish-core' ),
			'attributes'  => array(),
			'example'     => '[parish_churches]',
			'feature'     => 'churches',
		),
		// Clergy
		array(
			'shortcode'   => '[parish_clergy]',
			'name'        => __( 'Clergy & Staff', 'parish-core' ),
			'description' => __( 'Display clergy and staff list', 'parish-core' ),
			'attributes'  => array(),
			'example'     => '[parish_clergy]',
			'feature'     => null,
		),
		// Contact
		array(
			'shortcode'   => '[parish_contact]',
			'name'        => __( 'Contact Information', 'parish-core' ),
			'description' => __( 'Display parish contact details', 'parish-core' ),
			'attributes'  => array(),
			'example'     => '[parish_contact]',
			'feature'     => null,
		),
		// Prayers
		array(
			'shortcode'   => '[parish_prayers]',
			'name'        => __( 'Prayer Directory', 'parish-core' ),
			'description' => __( 'Display parish prayers', 'parish-core' ),
			'attributes'  => array(
				'limit'   => __( 'Number of prayers (-1 for all)', 'parish-core' ),
				'orderby' => __( 'Order by: "title", "date", "rand"', 'parish-core' ),
			),
			'example'     => '[parish_prayers limit="5"]',
			'feature'     => 'prayers',
		),
		// Daily Readings
		array(
			'shortcode'   => '[daily_readings]',
			'name'        => __( 'Daily Readings', 'parish-core' ),
			'description' => __( 'Display today\'s mass readings', 'parish-core' ),
			'attributes'  => array(),
			'example'     => '[daily_readings]',
			'feature'     => 'liturgical',
		),
		// Mass Reading Details
		array(
			'shortcode'   => '[mass_reading_details]',
			'name'        => __( 'Mass Reading Details', 'parish-core' ),
			'description' => __( 'Display detailed mass readings with structure', 'parish-core' ),
			'attributes'  => array(),
			'example'     => '[mass_reading_details]',
			'feature'     => 'liturgical',
		),
		// Sunday Homily
		array(
			'shortcode'   => '[sunday_homily]',
			'name'        => __( 'Sunday Homily', 'parish-core' ),
			'description' => __( 'Display Sunday homily notes', 'parish-core' ),
			'attributes'  => array(),
			'example'     => '[sunday_homily]',
			'feature'     => 'liturgical',
		),
		// Saint of the Day
		array(
			'shortcode'   => '[saint_of_the_day]',
			'name'        => __( 'Saint of the Day', 'parish-core' ),
			'description' => __( 'Display today\'s saint information', 'parish-core' ),
			'attributes'  => array(),
			'example'     => '[saint_of_the_day]',
			'feature'     => 'liturgical',
		),
		// Next Sunday Reading
		array(
			'shortcode'   => '[next_sunday_reading]',
			'name'        => __( 'Next Sunday Reading', 'parish-core' ),
			'description' => __( 'Display next Sunday\'s readings', 'parish-core' ),
			'attributes'  => array(),
			'example'     => '[next_sunday_reading]',
			'feature'     => 'liturgical',
		),
		// Next Sunday Reading Irish
		array(
			'shortcode'   => '[next_sunday_reading_irish]',
			'name'        => __( 'Next Sunday Reading (Irish)', 'parish-core' ),
			'description' => __( 'Display next Sunday\'s readings in Irish', 'parish-core' ),
			'attributes'  => array(),
			'example'     => '[next_sunday_reading_irish]',
			'feature'     => 'liturgical',
		),
		// Daily Readings Irish
		array(
			'shortcode'   => '[daily_readings_irish]',
			'name'        => __( 'Daily Readings (Irish)', 'parish-core' ),
			'description' => __( 'Display today\'s readings in Irish', 'parish-core' ),
			'attributes'  => array(),
			'example'     => '[daily_readings_irish]',
			'feature'     => 'liturgical',
		),
		// Feast Day Details
		array(
			'shortcode'   => '[feast_day_details]',
			'name'        => __( 'Feast Day Details', 'parish-core' ),
			'description' => __( 'Display today\'s liturgical feast information', 'parish-core' ),
			'attributes'  => array(),
			'example'     => '[feast_day_details]',
			'feature'     => 'liturgical',
		),
	);
}
