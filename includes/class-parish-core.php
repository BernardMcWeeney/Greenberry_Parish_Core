<?php
/**
 * Main Parish Core class.
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Parish_Core class - Main plugin controller.
 */
class Parish_Core {

	/**
	 * Singleton instance.
	 *
	 * @var Parish_Core|null
	 */
	private static ?Parish_Core $instance = null;

	/**
	 * Get singleton instance.
	 */
	public static function instance(): Parish_Core {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->init_components();
	}

	/**
	 * Initialize all components.
	 */
	private function init_components(): void {
		// CPT registration.
		if ( class_exists( 'Parish_CPT' ) ) {
			Parish_CPT::instance();
		}

		if ( class_exists( 'Parish_Slider' ) ) {
			Parish_Slider::instance();
		}

		// Meta fields.
		if ( class_exists( 'Parish_Meta' ) ) {
			Parish_Meta::instance();
		}

		// REST API.
		if ( class_exists( 'Parish_REST_API' ) ) {
			Parish_REST_API::instance();
		}

		// Admin UI.
		if ( class_exists( 'Parish_Admin_UI' ) ) {
			Parish_Admin_UI::instance();
		}

		// Shortcodes.
		if ( class_exists( 'Parish_Shortcodes' ) ) {
			Parish_Shortcodes::instance();
		}

		// Assets.
		if ( class_exists( 'Parish_Assets' ) ) {
			Parish_Assets::instance();
		}

		// Readings integration.
		if ( class_exists( 'Parish_Readings' ) ) {
			Parish_Readings::instance();
		}

		// Admin Colors (separate module).
		if ( class_exists( 'Parish_Admin_Colors' ) ) {
			Parish_Admin_Colors::instance();
		}
	}

	/**
	 * Get all settings.
	 */
	public static function get_settings(): array {
		$defaults = parish_core_get_default_settings();
		$settings = get_option( 'parish_core_settings', array() );
		return wp_parse_args( $settings, $defaults );
	}

	/**
	 * Get a specific setting.
	 *
	 * @param string $key     Setting key.
	 * @param mixed  $default Default value.
	 */
	public static function get_setting( string $key, $default = null ) {
		$settings = self::get_settings();
		return $settings[ $key ] ?? $default;
	}

	/**
	 * Update settings.
	 *
	 * @param array $new_settings Settings to update.
	 */
	public static function update_settings( array $new_settings ): bool {
		$current = self::get_settings();
		$merged  = array_merge( $current, $new_settings );
		return update_option( 'parish_core_settings', $merged );
	}

	/**
	 * Check if a feature is enabled.
	 *
	 * @param string $feature Feature key.
	 */
	public static function is_feature_enabled( string $feature ): bool {
		$key = 'enable_' . $feature;
		return (bool) self::get_setting( $key, true );
	}

	/**
	 * Get all CPT slugs.
	 */
	public static function get_post_types(): array {
		return array(
			'parish_death_notice',
			'parish_baptism',
			'parish_wedding',
			'parish_church',
			'parish_school',
			'parish_cemetery',
			'parish_group',
			'parish_newsletter',
			'parish_news',
			'parish_gallery',
			'parish_reflection',
			'parish_prayer',
		);
	}

	/**
	 * Get feature key for a post type.
	 *
	 * @param string $post_type Post type slug.
	 */
	public static function get_feature_key( string $post_type ): string {
		$map = array(
			'parish_death_notice' => 'death_notices',
			'parish_baptism'      => 'baptism_notices',
			'parish_wedding'      => 'wedding_notices',
			'parish_church'       => 'churches',
			'parish_school'       => 'schools',
			'parish_cemetery'     => 'cemeteries',
			'parish_group'        => 'groups',
			'parish_newsletter'   => 'newsletters',
			'parish_news'         => 'news',
			'parish_gallery'      => 'gallery',
			'parish_reflection'   => 'reflections',
			'parish_prayer'       => 'prayers',
		);
		return $map[ $post_type ] ?? '';
	}

	/**
	 * Get registered modules/features for the dashboard.
	 */
	public static function get_modules(): array {
		return array(
			'mass_times' => array(
				'name'        => __( 'Mass Times', 'parish-core' ),
				'description' => __( 'Manage weekly mass schedules', 'parish-core' ),
				'icon'        => 'clock',
				'has_page'    => true,
			),
			'events' => array(
				'name'        => __( 'Events Calendar', 'parish-core' ),
				'description' => __( 'Parish events and sacraments', 'parish-core' ),
				'icon'        => 'calendar',
				'has_page'    => true,
			),
			'liturgical' => array(
				'name'        => __( 'Liturgical Calendar', 'parish-core' ),
				'description' => __( 'Catholic readings and feast days', 'parish-core' ),
				'icon'        => 'book',
				'has_page'    => true,
			),
			'admin_colors' => array(
				'name'        => __( 'Admin Colors', 'parish-core' ),
				'description' => __( 'Customize WordPress admin appearance', 'parish-core' ),
				'icon'        => 'admin-appearance',
				'has_page'    => false,
			),
		);
	}
}
