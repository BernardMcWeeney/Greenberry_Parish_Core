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

	private static ?Parish_Core $instance = null;

	public static function instance(): Parish_Core {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->init_components();
	}

	private function init_components(): void {
		// CPT registration (registry-based system).
		if ( class_exists( 'Parish_CPT_Registry' ) ) {
			Parish_CPT_Registry::instance();
		}

		// Meta registration (registry-based system) — replaces Parish_Meta.
		if ( class_exists( 'Parish_Meta_Registry' ) ) {
			Parish_Meta_Registry::instance();
		}

		// Blocks (register dynamic blocks, enqueue editor JS, etc.)
		if ( class_exists( 'Parish_Blocks' ) ) {
			Parish_Blocks::instance();
		}

		// Block Bindings (post-meta editable bindings)
		if ( class_exists( 'Parish_Block_Bindings' ) ) {
			Parish_Block_Bindings::instance();
		}

		// Auto-title generation from meta.
		if ( class_exists( 'Parish_Auto_Title' ) ) {
			Parish_Auto_Title::instance();
		}

		if ( class_exists( 'Parish_Slider' ) ) {
			Parish_Slider::instance();
		}

		if ( class_exists( 'Parish_REST_API' ) ) {
			Parish_REST_API::instance();
		}

		if ( class_exists( 'Parish_Admin_UI' ) ) {
			Parish_Admin_UI::instance();
		}

		if ( class_exists( 'Parish_Shortcodes' ) ) {
			Parish_Shortcodes::instance();
		}

		if ( class_exists( 'Parish_Assets' ) ) {
			Parish_Assets::instance();
		}

		if ( class_exists( 'Parish_Admin_Colors' ) ) {
			Parish_Admin_Colors::instance();
		}

		// Readings API module (liturgical features).
		if ( class_exists( 'Parish_Readings' ) && self::is_feature_enabled( 'liturgical' ) ) {
			Parish_Readings::instance();
		}

		// Rosary shortcodes.
		if ( class_exists( 'Parish_Rosary_Shortcodes' ) && self::is_feature_enabled( 'rosary' ) ) {
			Parish_Rosary_Shortcodes::register();
		}

		// Rosary blocks.
		if ( class_exists( 'Parish_Rosary_Blocks' ) && self::is_feature_enabled( 'rosary' ) ) {
			Parish_Rosary_Blocks::register();
		}

	}

	public static function get_settings(): array {
		$defaults = parish_core_get_default_settings();
		$settings = get_option( 'parish_core_settings', array() );
		return wp_parse_args( $settings, $defaults );
	}

	public static function get_setting( string $key, $default = null ) {
		$settings = self::get_settings();
		return $settings[ $key ] ?? $default;
	}

	public static function update_settings( array $new_settings ): bool {
		$current = self::get_settings();
		$merged  = array_merge( $current, $new_settings );
		return update_option( 'parish_core_settings', $merged );
	}

	public static function is_feature_enabled( string $feature ): bool {
		$key = 'enable_' . $feature;
		return (bool) self::get_setting( $key, true );
	}

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
			'parish_travels',
		);
	}

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
			'parish_travels'      => 'travels',
		);

		return $map[ $post_type ] ?? '';
	}

	public static function get_modules(): array {
		return array(
			'mass_times' => array(
				'name'        => __( 'Mass Times', 'parish-core' ),
				'description' => __( 'Weekly Mass schedule and special celebrations', 'parish-core' ),
				'icon'        => 'clock',
				'has_page'    => true,
			),
			'rosary' => array(
				'name'        => __( 'Rosary', 'parish-core' ),
				'description' => __( 'Daily rosary mysteries and meditations', 'parish-core' ),
				'icon'        => 'heart',
				'has_page'    => false,
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
