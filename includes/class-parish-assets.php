<?php
/**
 * Assets - Scripts and Styles.
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Parish_Assets class.
 */
class Parish_Assets {

	/**
	 * Singleton instance.
	 *
	 * @var Parish_Assets|null
	 */
	private static ?Parish_Assets $instance = null;

	/**
	 * Get singleton instance.
	 */
	public static function instance(): Parish_Assets {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		// Admin assets.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		// Front-end assets.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_front_assets' ) );
	}

	/**
	 * Enqueue admin assets for Parish Core pages.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_assets( string $hook ): void {
		// Only load on Parish Core pages.
		if ( ! $this->is_parish_page( $hook ) ) {
			return;
		}

		// WordPress script dependencies for the shared utils file.
		$deps = array(
			'wp-element',
			'wp-components',
			'wp-api-fetch',
			'wp-i18n',
			'wp-date',
		);

		/**
		 * 1) Shared utilities (creates window.ParishCoreAdmin and uses window.parishCore)
		 */
		wp_enqueue_script(
			'parish-admin-utils',
			PARISH_CORE_URL . 'assets/js/parish-core-admin-utils.js',
			$deps,
			PARISH_CORE_VERSION,
			true
		);

		// Localized data for the app (attached to the utils handle so it's available before modules run).
		wp_localize_script(
			'parish-admin-utils',
			'parishCore',
			array(
				'apiUrl'   => rest_url( 'parish/v1/' ),
				'nonce'    => wp_create_nonce( 'wp_rest' ),
				'adminUrl' => admin_url(),
				'siteUrl'  => home_url(),
				'page'     => $this->get_current_page( $hook ),
				'isAdmin'  => current_user_can( 'manage_options' ),
				'settings' => Parish_Core::get_settings(),
				'version'  => PARISH_CORE_VERSION,
			)
		);

		/**
		 * 2) Feature modules (all depend on utils)
		 */
		wp_enqueue_script(
			'parish-admin-dashboard',
			PARISH_CORE_URL . 'assets/js/parish-core-admin-dashboard.js',
			array( 'parish-admin-utils' ),
			PARISH_CORE_VERSION,
			true
		);

		wp_enqueue_script(
			'parish-admin-about',
			PARISH_CORE_URL . 'assets/js/parish-core-admin-about.js',
			array( 'parish-admin-utils' ),
			PARISH_CORE_VERSION,
			true
		);

		wp_enqueue_script(
			'parish-admin-mass-times',
			PARISH_CORE_URL . 'assets/js/parish-core-admin-mass-times.js',
			array( 'parish-admin-utils' ),
			PARISH_CORE_VERSION,
			true
		);

		// New Event Times CPT-based management (enhanced Mass Times).
		wp_enqueue_script(
			'parish-admin-event-times',
			PARISH_CORE_URL . 'assets/js/parish-core-admin-event-times.js',
			array( 'parish-admin-utils' ),
			PARISH_CORE_VERSION,
			true
		);

		// Mass Times Weekly Grid Editor.
		wp_enqueue_script(
			'parish-admin-mass-times-grid',
			PARISH_CORE_URL . 'assets/js/parish-core-admin-mass-times-grid.js',
			array( 'parish-admin-utils', 'parish-admin-event-times' ),
			PARISH_CORE_VERSION,
			true
		);

		wp_enqueue_script(
			'parish-admin-events',
			PARISH_CORE_URL . 'assets/js/parish-core-admin-events.js',
			array( 'parish-admin-utils' ),
			PARISH_CORE_VERSION,
			true
		);

		// Slider module - depends on utils for shared components
		wp_enqueue_script(
			'parish-admin-slider',
			PARISH_CORE_URL . 'assets/js/parish-core-admin-slider.js',
			array( 'parish-admin-utils' ),
			PARISH_CORE_VERSION,
			true
		);

		wp_enqueue_script(
			'parish-admin-readings',
			PARISH_CORE_URL . 'assets/js/parish-core-admin-readings.js',
			array( 'parish-admin-utils' ),
			PARISH_CORE_VERSION,
			true
		);

		wp_enqueue_script(
			'parish-admin-settings',
			PARISH_CORE_URL . 'assets/js/parish-core-admin-settings.js',
			array( 'parish-admin-utils' ),
			PARISH_CORE_VERSION,
			true
		);

		wp_enqueue_script(
			'parish-post-meta-bindings',
			PARISH_CORE_URL . 'assets/js/parish-post-meta-bindings.js',
			array( 'wp-blocks', 'wp-data', 'wp-core-data', 'wp-dom-ready' ),
			PARISH_CORE_VERSION,
			true
		);


		/**
		 * 3) Router & bootstrap (loads last, depends on all modules)
		 */
		wp_enqueue_script(
			'parish-admin-app',
			PARISH_CORE_URL . 'assets/js/parish-core-admin-app.js',
			array(
				'parish-admin-utils',
				'parish-admin-dashboard',
				'parish-admin-about',
				'parish-admin-mass-times',
				'parish-admin-event-times',
				'parish-admin-mass-times-grid',
				'parish-admin-events',
				'parish-admin-slider',
				'parish-admin-readings',
				'parish-admin-settings',
			),
			PARISH_CORE_VERSION,
			true
		);

		// WordPress components styles.
		wp_enqueue_style( 'wp-components' );

		// Admin CSS.
		wp_enqueue_style(
			'parish-admin',
			PARISH_CORE_URL . 'assets/css/admin.css',
			array( 'wp-components' ),
			PARISH_CORE_VERSION
		);

		// Event times admin styles.
		wp_enqueue_style(
			'parish-event-times-admin',
			PARISH_CORE_URL . 'assets/css/event-times.css',
			array( 'wp-components' ),
			PARISH_CORE_VERSION
		);

		// Media uploader for About Parish and Slider pages.
		if ( $hook === 'parish_page_parish-about' || $hook === 'parish_page_parish-slider' ) {
			wp_enqueue_media();
		}
	}

	/**
	 * Check if current admin page is a Parish Core page.
	 *
	 * @param string $hook Current admin page hook.
	 */
	private function is_parish_page( string $hook ): bool {
		$parish_pages = array(
			'toplevel_page_parish-core',
			'parish_page_parish-about',
			'parish_page_parish-mass-times',
			'parish_page_parish-events',
			'parish_page_parish-slider',
			'parish_page_parish-readings',
			'parish_page_parish-settings',
		);

		return in_array( $hook, $parish_pages, true );
	}

	/**
	 * Get current page identifier for the React app.
	 *
	 * @param string $hook Current admin page hook.
	 */
	private function get_current_page( string $hook ): string {
		$map = array(
			'toplevel_page_parish-core'     => 'dashboard',
			'parish_page_parish-about'      => 'about',
			'parish_page_parish-mass-times' => 'mass-times',
			'parish_page_parish-events'     => 'events',
			'parish_page_parish-slider'     => 'slider',
			'parish_page_parish-readings'   => 'readings',
			'parish_page_parish-settings'   => 'settings',
		);

		return $map[ $hook ] ?? 'unknown';
	}

	/**
	 * Enqueue front-end assets.
	 */
	public function enqueue_front_assets(): void {
		// Main front-end styles.
		wp_enqueue_style(
			'parish-front',
			PARISH_CORE_URL . 'assets/css/front.css',
			array(),
			PARISH_CORE_VERSION
		);

		// Event times styles - load when shortcodes are detected.
		global $post;
		$should_load_event_times = false;

		if ( is_a( $post, 'WP_Post' ) ) {
			$event_time_shortcodes = array(
				'parish_times',
				'parish_times_today',
				'parish_mass_times',
				'parish_confessions',
				'parish_adoration',
			);
			foreach ( $event_time_shortcodes as $shortcode ) {
				if ( has_shortcode( $post->post_content, $shortcode ) ) {
					$should_load_event_times = true;
					break;
				}
			}
		}

		if ( $should_load_event_times ) {
			wp_enqueue_style(
				'parish-event-times',
				PARISH_CORE_URL . 'assets/css/event-times.css',
				array(),
				PARISH_CORE_VERSION
			);
		}

		// Slider styles - always load on front page or if shortcode detected.
		$should_load_slider = false;

		if ( is_front_page() || is_home() ) {
			$should_load_slider = true;
		}

		if ( is_a( $post, 'WP_Post' ) ) {
			if ( has_shortcode( $post->post_content, 'parish_slider' ) ) {
				$should_load_slider = true;
			}
			if ( function_exists( 'has_block' ) && has_block( 'parish-core/slider', $post ) ) {
				$should_load_slider = true;
			}
		}

		if ( $should_load_slider ) {
			wp_enqueue_style(
				'parish-slider',
				PARISH_CORE_URL . 'assets/css/slider.css',
				array(),
				PARISH_CORE_VERSION
			);

			wp_enqueue_script(
				'parish-slider',
				PARISH_CORE_URL . 'assets/js/parish-slider.js',
				array(),
				PARISH_CORE_VERSION,
				true
			);
		}
	}
}
