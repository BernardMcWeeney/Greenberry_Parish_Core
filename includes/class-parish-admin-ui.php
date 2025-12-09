<?php
/**
 * Admin UI - Menus, pages, and meta boxes.
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Parish_Admin_UI class.
 */
class Parish_Admin_UI {

	/**
	 * Singleton instance.
	 *
	 * @var Parish_Admin_UI|null
	 */
	private static ?Parish_Admin_UI $instance = null;

	/**
	 * Get singleton instance.
	 */
	public static function instance(): Parish_Admin_UI {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ), 5 );
		add_action( 'admin_menu', array( $this, 'reorder_submenu' ), 999 );
		add_filter( 'admin_footer_text', array( $this, 'custom_admin_footer' ) );
	}

	/**
	 * Custom admin footer text.
	 *
	 * @param string $text Footer text.
	 */
	public function custom_admin_footer( string $text ): string {
		$screen = get_current_screen();
		
		if ( $screen && ( strpos( $screen->id, 'parish' ) !== false || strpos( $screen->base, 'parish' ) !== false ) ) {
			return '<span id="footer-thankyou">Thank you for being a valued partner of <a href="https://greenberry.ie" target="_blank">Greenberry</a>.</span>';
		}
		
		return $text;
	}

	/**
	 * Register admin menu.
	 */
	public function register_admin_menu(): void {
		// Main menu - Dashboard (Editor access).
		add_menu_page(
			__( 'Parish Dashboard', 'parish-core' ),
			__( 'Parish', 'parish-core' ),
			'edit_posts',
			'parish-core',
			array( $this, 'render_dashboard_page' ),
			'dashicons-location-alt',
			3
		);

		// Dashboard submenu.
		add_submenu_page(
			'parish-core',
			__( 'Dashboard', 'parish-core' ),
			__( 'Dashboard', 'parish-core' ),
			'edit_posts',
			'parish-core',
			array( $this, 'render_dashboard_page' )
		);

		// About Parish (Editor level).
		add_submenu_page(
			'parish-core',
			__( 'About Parish', 'parish-core' ),
			__( 'About Parish', 'parish-core' ),
			'edit_posts',
			'parish-about',
			array( $this, 'render_about_page' )
		);

		// Mass Times (Editor level).
		if ( Parish_Core::is_feature_enabled( 'mass_times' ) ) {
			add_submenu_page(
				'parish-core',
				__( 'Mass Times', 'parish-core' ),
				__( 'Mass Times', 'parish-core' ),
				'edit_posts',
				'parish-mass-times',
				array( $this, 'render_mass_times_page' )
			);
		}

		// Events Calendar (Editor level).
		if ( Parish_Core::is_feature_enabled( 'events' ) ) {
			add_submenu_page(
				'parish-core',
				__( 'Events Calendar', 'parish-core' ),
				__( 'Events', 'parish-core' ),
				'edit_posts',
				'parish-events',
				array( $this, 'render_events_page' )
			);
		}

		// Readings API (Admin only).
		if ( Parish_Core::is_feature_enabled( 'liturgical' ) ) {
			add_submenu_page(
				'parish-core',
				__( 'Readings API', 'parish-core' ),
				__( 'Readings API', 'parish-core' ),
				'manage_options',
				'parish-readings',
				array( $this, 'render_readings_page' )
			);
		}

		// Settings (Admin only).
		add_submenu_page(
			'parish-core',
			__( 'Settings', 'parish-core' ),
			__( 'Settings', 'parish-core' ),
			'manage_options',
			'parish-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Reorder submenu.
	 */
	public function reorder_submenu(): void {
		global $submenu;

		if ( ! isset( $submenu['parish-core'] ) ) {
			return;
		}

		$order = array(
			'parish-core',
			'parish-about',
			'parish-mass-times',
			'parish-events',
			'parish-readings',
		);

		$ordered   = array();
		$cpts      = array();
		$settings  = null;
		$remaining = array();

		foreach ( $submenu['parish-core'] as $item ) {
			$slug = $item[2];

			if ( in_array( $slug, $order, true ) ) {
				$ordered[ array_search( $slug, $order, true ) ] = $item;
			} elseif ( $slug === 'parish-settings' ) {
				$settings = $item;
			} elseif ( strpos( $slug, 'edit.php?post_type=parish_' ) !== false ) {
				$cpts[] = $item;
			} else {
				$remaining[] = $item;
			}
		}

		ksort( $ordered );
		$new_menu = array_values( $ordered );

		foreach ( $cpts as $cpt ) {
			$new_menu[] = $cpt;
		}

		foreach ( $remaining as $item ) {
			$new_menu[] = $item;
		}

		if ( $settings ) {
			$new_menu[] = $settings;
		}

		$submenu['parish-core'] = $new_menu;
	}

	/**
	 * Render Dashboard page.
	 */
	public function render_dashboard_page(): void {
		?>
		<div class="wrap parish-dashboard-wrap">
			<div id="parish-dashboard-app">
				<div class="parish-loading">
					<span class="spinner is-active"></span>
					<p><?php esc_html_e( 'Loading Dashboard...', 'parish-core' ); ?></p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render About Parish page.
	 */
	public function render_about_page(): void {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'About Parish', 'parish-core' ); ?></h1>
			<div id="parish-about-app">
				<div class="parish-loading">
					<span class="spinner is-active"></span>
					<p><?php esc_html_e( 'Loading...', 'parish-core' ); ?></p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Mass Times page.
	 */
	public function render_mass_times_page(): void {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Mass Times Schedule', 'parish-core' ); ?></h1>
			<div id="parish-mass-times-app">
				<div class="parish-loading">
					<span class="spinner is-active"></span>
					<p><?php esc_html_e( 'Loading...', 'parish-core' ); ?></p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Events page.
	 */
	public function render_events_page(): void {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Parish Events Calendar', 'parish-core' ); ?></h1>
			<div id="parish-events-app">
				<div class="parish-loading">
					<span class="spinner is-active"></span>
					<p><?php esc_html_e( 'Loading...', 'parish-core' ); ?></p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Readings API page.
	 */
	public function render_readings_page(): void {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Catholic Readings API', 'parish-core' ); ?></h1>
			<div id="parish-readings-app">
				<div class="parish-loading">
					<span class="spinner is-active"></span>
					<p><?php esc_html_e( 'Loading...', 'parish-core' ); ?></p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Settings page.
	 */
	public function render_settings_page(): void {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Parish Core Settings', 'parish-core' ); ?></h1>
			<div id="parish-settings-app">
				<div class="parish-loading">
					<span class="spinner is-active"></span>
					<p><?php esc_html_e( 'Loading...', 'parish-core' ); ?></p>
				</div>
			</div>
		</div>
		<?php
	}
}
