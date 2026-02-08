<?php
/**
 * Plugin Name: Parish Core
 * Plugin URI: https://github.com/greenberry/parish-core
 * Description: A comprehensive parish management system for Catholic parishes.
 * Version: 5.2.0
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

define( 'PARISH_CORE_VERSION', '4.5.0' );
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

		// Other modules you already have.
		'class-parish-rest-api.php',
		'class-parish-admin-ui.php',
		'class-parish-shortcodes.php',
		'class-parish-admin-colors.php',
		'class-parish-slider.php',
		'class-parish-readings.php',
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
 * Get all available shortcodes with their documentation.
 * This is used by the Shortcode Reference tab.
 */
function parish_core_get_shortcode_reference(): array {
	return array(
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
			'description' => __( 'Display today\'s liturgical feast with colored indicator', 'parish-core' ),
			'attributes'  => array(),
			'example'     => '[feast_day_details]',
			'feature'     => 'liturgical',
		),
		// Liturgical Day
		array(
			'shortcode'   => '[liturgical_day]',
			'name'        => __( 'Liturgical Day', 'parish-core' ),
			'description' => __( 'Display liturgical information for today (season, cycles, rosary)', 'parish-core' ),
			'attributes'  => array(
				'link_rosary' => __( 'URL to rosary page for linking (e.g., "/rosary")', 'parish-core' ),
			),
			'example'     => '[liturgical_day link_rosary="/rosary"]',
			'feature'     => 'liturgical',
		),
		// Liturgical Week
		array(
			'shortcode'   => '[liturgical_week]',
			'name'        => __( 'Liturgical Week', 'parish-core' ),
			'description' => __( 'Display liturgical information for the week with rosary schedule', 'parish-core' ),
			'attributes'  => array(
				'link_rosary' => __( 'URL to rosary page for linking', 'parish-core' ),
			),
			'example'     => '[liturgical_week link_rosary="/rosary"]',
			'feature'     => 'liturgical',
		),
		// Rosary Days
		array(
			'shortcode'   => '[rosary_days]',
			'name'        => __( 'Rosary Days', 'parish-core' ),
			'description' => __( 'Display which days of the week each rosary series is prayed', 'parish-core' ),
			'attributes'  => array(
				'link_rosary' => __( 'URL to rosary page for linking', 'parish-core' ),
			),
			'example'     => '[rosary_days link_rosary="/rosary"]',
			'feature'     => 'liturgical',
		),
		// Rosary Today
		array(
			'shortcode'   => '[rosary_today]',
			'name'        => __( 'Today\'s Rosary', 'parish-core' ),
			'description' => __( 'Display which rosary mysteries to pray today with optional link', 'parish-core' ),
			'attributes'  => array(
				'link'           => __( 'URL to rosary page (adds #joyful, #sorrowful, etc.)', 'parish-core' ),
				'show_link'      => __( '"yes" or "no" to show/hide link (default: yes)', 'parish-core' ),
				'format'         => __( '"full", "simple", or "link-only"', 'parish-core' ),
				'show_mysteries' => __( '"yes" to list all 5 mysteries (default: no)', 'parish-core' ),
			),
			'example'     => '[rosary_today link="/rosary" format="full" show_mysteries="yes"]',
			'feature'     => 'liturgical',
		),
		// Rosary Week
		array(
			'shortcode'   => '[rosary_week]',
			'name'        => __( 'Weekly Rosary Schedule', 'parish-core' ),
			'description' => __( 'Display rosary mysteries for each day of the week', 'parish-core' ),
			'attributes'  => array(
				'link'       => __( 'URL to rosary page for linking', 'parish-core' ),
				'show_today' => __( '"yes" or "no" to highlight today (default: yes)', 'parish-core' ),
				'format'     => __( '"table" or "list" (default: table)', 'parish-core' ),
			),
			'example'     => '[rosary_week link="/rosary" format="table"]',
			'feature'     => 'liturgical',
		),
		// Rosary Series
		array(
			'shortcode'   => '[rosary_series]',
			'name'        => __( 'Rosary Series Overview', 'parish-core' ),
			'description' => __( 'Display all four rosary series with days prayed during current season', 'parish-core' ),
			'attributes'  => array(
				'link'   => __( 'URL to rosary page for linking', 'parish-core' ),
				'series' => __( 'Specific series: "joyful", "sorrowful", "glorious", "luminous"', 'parish-core' ),
			),
			'example'     => '[rosary_series link="/rosary"]',
			'feature'     => 'liturgical',
		),
		// Rosary Mysteries
		array(
			'shortcode'   => '[rosary_mysteries]',
			'name'        => __( 'Rosary Mysteries List', 'parish-core' ),
			'description' => __( 'Display the five mysteries of each rosary series (great for rosary pages)', 'parish-core' ),
			'attributes'  => array(
				'series' => __( 'Specific series or leave empty for all four', 'parish-core' ),
			),
			'example'     => '[rosary_mysteries series="joyful"]',
			'feature'     => 'liturgical',
		),
		// Hero Slider
		array(
			'shortcode'   => '[parish_slider]',
			'name'        => __( 'Hero Slider', 'parish-core' ),
			'description' => __( 'Display the homepage hero slider with manual and dynamic slides', 'parish-core' ),
			'attributes'  => array(
				'class' => __( 'Additional CSS class for styling', 'parish-core' ),
			),
			'example'     => '[parish_slider]',
			'feature'     => 'slider',
		),
		// Mass Times - Today Widget
		array(
			'shortcode'   => '[parish_today_widget]',
			'name'        => __( 'Today\'s Schedule Widget', 'parish-core' ),
			'description' => __( 'Compact widget showing Mass Times grouped by church for a single day', 'parish-core' ),
			'attributes'  => array(
				'date'       => __( 'Date in YYYY-MM-DD format (default: today)', 'parish-core' ),
				'church_id'  => __( 'Filter by specific church ID', 'parish-core' ),
				'type'       => __( 'Filter by type: "mass", "confession", "adoration", "rosary"', 'parish-core' ),
				'show_notes' => __( '"yes" to show notes (default: no)', 'parish-core' ),
			),
			'example'     => '[parish_today_widget church_id="123" show_notes="yes"]',
			'feature'     => 'mass_times',
		),
		// Mass Times - Church Schedule
		array(
			'shortcode'   => '[parish_church_schedule]',
			'name'        => __( 'Church Weekly Schedule', 'parish-core' ),
			'description' => __( 'Weekly schedule view for a specific church with special events section', 'parish-core' ),
			'attributes'  => array(
				'church_id'       => __( 'Church ID (auto-detects on church pages)', 'parish-core' ),
				'show_special'    => __( '"yes" or "no" to show special events (default: yes)', 'parish-core' ),
				'show_livestream' => __( '"yes" or "no" to show livestream icons (default: yes)', 'parish-core' ),
			),
			'example'     => '[parish_church_schedule church_id="123"]',
			'feature'     => 'mass_times',
		),
		// Mass Times - General Schedule
		array(
			'shortcode'   => '[parish_schedule]',
			'name'        => __( 'Schedule View', 'parish-core' ),
			'description' => __( 'Display Mass Times schedule for multiple days', 'parish-core' ),
			'attributes'  => array(
				'days'            => __( 'Number of days to show (default: 7)', 'parish-core' ),
				'church_id'       => __( 'Filter by specific church', 'parish-core' ),
				'event_type'      => __( 'Filter by type', 'parish-core' ),
				'format'          => __( '"list", "table", "cards", "simple"', 'parish-core' ),
				'show_feast_day'  => __( '"yes" or "no" (default: yes)', 'parish-core' ),
				'show_livestream' => __( '"yes" or "no" (default: yes)', 'parish-core' ),
			),
			'example'     => '[parish_schedule days="7" format="cards"]',
			'feature'     => 'mass_times',
		),
		// Mass Times - Weekly Schedule
		array(
			'shortcode'   => '[parish_weekly_schedule]',
			'name'        => __( 'Weekly Schedule', 'parish-core' ),
			'description' => __( 'Full week schedule grouped by day', 'parish-core' ),
			'attributes'  => array(
				'church_id'       => __( 'Filter by specific church', 'parish-core' ),
				'event_type'      => __( 'Filter by type', 'parish-core' ),
				'show_feast_day'  => __( '"yes" or "no" (default: yes)', 'parish-core' ),
				'show_livestream' => __( '"yes" or "no" (default: yes)', 'parish-core' ),
			),
			'example'     => '[parish_weekly_schedule church_id="123"]',
			'feature'     => 'mass_times',
		),
		// Mass Times - Today Schedule
		array(
			'shortcode'   => '[parish_today_schedule]',
			'name'        => __( 'Today\'s Schedule', 'parish-core' ),
			'description' => __( 'Today\'s schedule with feast day header', 'parish-core' ),
			'attributes'  => array(
				'church_id'       => __( 'Filter by specific church', 'parish-core' ),
				'event_type'      => __( 'Filter by type', 'parish-core' ),
				'show_feast_day'  => __( '"yes" or "no" (default: yes)', 'parish-core' ),
				'show_livestream' => __( '"yes" or "no" (default: yes)', 'parish-core' ),
			),
			'example'     => '[parish_today_schedule]',
			'feature'     => 'mass_times',
		),
	);
}