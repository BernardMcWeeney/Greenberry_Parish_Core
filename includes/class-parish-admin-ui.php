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
		add_action( 'admin_menu', array( $this, 'replace_wp_dashboard_menu_for_non_admin' ), 1000 );
		add_action( 'admin_menu', array( $this, 'promote_parish_menus_for_non_admin' ), 1001 );
		add_action( 'admin_menu', array( $this, 'reorder_top_level_menu_for_non_admin' ), 1100 );
		add_action( 'load-index.php', array( $this, 'redirect_wp_dashboard_for_non_admin' ) );
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
		$dashboard_replaced = $this->should_replace_wp_dashboard_for_current_user();

		// Main menu - Dashboard (Editor access).
		add_menu_page(
			__( 'Parish Dashboard', 'parish-core' ),
			$dashboard_replaced ? __( 'Dashboard', 'parish-core' ) : __( 'Parish', 'parish-core' ),
			'edit_posts',
			'parish-core',
			array( $this, 'render_dashboard_page' ),
			'dashicons-location-alt',
			$dashboard_replaced ? 2 : 3
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

		// Hero Slider (Editor level).
		if ( Parish_Core::is_feature_enabled( 'slider' ) ) {
			add_submenu_page(
				'parish-core',
				__( 'Hero Slider', 'parish-core' ),
				__( 'Slider', 'parish-core' ),
				'edit_posts',
				'parish-slider',
				array( $this, 'render_slider_page' )
			);
		}

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

		$options = $this->get_menu_options();
		$order   = $options['menu_order'];

		$known_pages = array(
			'parish-core',
			'parish-about',
			'parish-events',
			'parish-slider',
			'parish-mass-times',
			'parish-readings',
			'parish-settings',
		);

		$items_by_slug = array();
		$cpts          = array();
		$remaining     = array();

		foreach ( $submenu['parish-core'] as $item ) {
			$slug = $item[2] ?? '';

			if ( empty( $slug ) ) {
				continue;
			}

			if ( in_array( $slug, $known_pages, true ) ) {
				$items_by_slug[ $slug ] = $item;
			} elseif ( strpos( $slug, 'edit.php?post_type=parish_' ) !== false ) {
				$cpts[] = $item;
			} else {
				$remaining[] = $item;
			}
		}

		$new_menu = array();
		$added    = array();

		foreach ( $order as $token ) {
			if ( 'cpts' === $token ) {
				foreach ( $cpts as $cpt ) {
					$new_menu[] = $cpt;
				}
				continue;
			}

			if ( 'remaining' === $token ) {
				foreach ( $remaining as $item ) {
					$new_menu[] = $item;
				}
				continue;
			}

			if ( isset( $items_by_slug[ $token ] ) ) {
				$new_menu[]      = $items_by_slug[ $token ];
				$added[ $token ] = true;
			}
		}

		foreach ( $items_by_slug as $slug => $item ) {
			if ( isset( $added[ $slug ] ) ) {
				continue;
			}
			$new_menu[] = $item;
		}

		$submenu['parish-core'] = $new_menu;
	}

	/**
	 * Remove the default WordPress dashboard menu for configured non-admin roles.
	 */
	public function replace_wp_dashboard_menu_for_non_admin(): void {
		if ( ! $this->should_replace_wp_dashboard_for_current_user() ) {
			return;
		}

		remove_menu_page( 'index.php' );
		remove_submenu_page( 'index.php', 'index.php' );
	}

	/**
	 * Redirect wp-admin dashboard to Parish dashboard for configured non-admin roles.
	 */
	public function redirect_wp_dashboard_for_non_admin(): void {
		if ( ! $this->should_replace_wp_dashboard_for_current_user() ) {
			return;
		}

		wp_safe_redirect( admin_url( 'admin.php?page=parish-core' ) );
		exit;
	}

	/**
	 * Promote Parish submenu entries to top-level entries for configured non-admin roles.
	 */
	public function promote_parish_menus_for_non_admin(): void {
		global $submenu;

		if ( ! $this->should_flatten_parish_menu_for_current_user() ) {
			return;
		}

		if ( ! isset( $submenu['parish-core'] ) || ! is_array( $submenu['parish-core'] ) ) {
			return;
		}

		$protected_slugs = array(
			'parish-core',
			'parish-readings',
			'parish-settings',
		);

		$position = 4;
		$seen     = array();

		foreach ( $submenu['parish-core'] as $item ) {
			$slug = $item[2] ?? '';

			if ( empty( $slug ) || in_array( $slug, $protected_slugs, true ) || isset( $seen[ $slug ] ) ) {
				continue;
			}

			$seen[ $slug ] = true;

			if ( ! $this->is_promotable_menu_slug( $slug ) ) {
				continue;
			}

			$capability = $item[1] ?? 'edit_posts';
			if ( ! current_user_can( $capability ) ) {
				continue;
			}

			$menu_title = wp_strip_all_tags( (string) ( $item[0] ?? '' ) );
			$page_title = wp_strip_all_tags( (string) ( $item[3] ?? $menu_title ) );
			$callback   = $this->get_page_callback_for_slug( $slug );

			add_menu_page(
				! empty( $page_title ) ? $page_title : $menu_title,
				$menu_title,
				$capability,
				$slug,
				$callback ?? '',
				$this->get_promoted_menu_icon( $slug ),
				$position
			);

			remove_submenu_page( 'parish-core', $slug );
			++$position;
		}
	}

	/**
	 * Reorder top-level admin menu for non-admin users.
	 */
	public function reorder_top_level_menu_for_non_admin(): void {
		global $menu;

		if ( current_user_can( 'manage_options' ) || ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		if ( ! is_array( $menu ) || empty( $menu ) ) {
			return;
		}

		$desired_order = $this->get_secretary_top_level_menu_order();
		$desired_map   = array_flip( $desired_order );

		$matched = array();
		$others  = array();

		foreach ( $menu as $item ) {
			$slug = $item[2] ?? '';

			if ( '' !== $slug && isset( $desired_map[ $slug ] ) && ! isset( $matched[ $slug ] ) ) {
				$matched[ $slug ] = $item;
				continue;
			}

			$others[] = $item;
		}

		$new_menu = array();

		foreach ( $desired_order as $slug ) {
			if ( isset( $matched[ $slug ] ) ) {
				$new_menu[] = $matched[ $slug ];
			}
		}

		foreach ( $others as $item ) {
			$new_menu[] = $item;
		}

		$reindexed = array();
		$position  = 2;
		foreach ( $new_menu as $item ) {
			$reindexed[ (string) $position ] = $item;
			$position += 2;
		}

		$menu = $reindexed;
	}

	/**
	 * Determine if Parish submenu should be flattened for the current user.
	 */
	private function should_flatten_parish_menu_for_current_user(): bool {
		if ( current_user_can( 'manage_options' ) || ! current_user_can( 'edit_posts' ) ) {
			return false;
		}

		$role = $this->get_current_default_non_admin_role();
		if ( '' === $role ) {
			return false;
		}

		$options = $this->get_menu_options();
		return (bool) ( $options['flatten_roles'][ $role ] ?? false );
	}

	/**
	 * Determine if WordPress dashboard should be replaced for the current user.
	 */
	private function should_replace_wp_dashboard_for_current_user(): bool {
		if ( current_user_can( 'manage_options' ) || ! current_user_can( 'edit_posts' ) ) {
			return false;
		}

		$role = $this->get_current_default_non_admin_role();
		if ( '' === $role ) {
			return false;
		}

		$options = $this->get_menu_options();
		return (bool) ( $options['replace_dashboard_roles'][ $role ] ?? false );
	}

	/**
	 * Get default non-admin role for current user.
	 */
	private function get_current_default_non_admin_role(): string {
		$user = wp_get_current_user();
		if ( empty( $user->roles ) || ! is_array( $user->roles ) ) {
			return '';
		}

		foreach ( $this->get_supported_default_non_admin_roles() as $role ) {
			if ( in_array( $role, $user->roles, true ) ) {
				return $role;
			}
		}

		return '';
	}

	/**
	 * Supported default non-admin WordPress roles.
	 *
	 * @return string[]
	 */
	private function get_supported_default_non_admin_roles(): array {
		return array(
			'editor',
			'author',
			'contributor',
			'subscriber',
		);
	}

	/**
	 * Get normalized menu options.
	 */
	private function get_menu_options(): array {
		$defaults = $this->get_default_menu_options();
		$raw      = Parish_Core::get_setting( 'menu_options', array() );

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

		foreach ( $this->get_supported_default_non_admin_roles() as $role ) {
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
	 * Default menu options.
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
	 * Default menu order tokens.
	 *
	 * @return string[]
	 */
	private function get_default_menu_order(): array {
		return array(
			'parish-core',
			'parish-about',
			'parish-events',
			'parish-mass-times',
			'parish-slider',
			'cpts',
			'parish-readings',
			'parish-settings',
			'remaining',
		);
	}

	/**
	 * Fixed top-level menu order for parish secretary roles.
	 *
	 * @return string[]
	 */
	private function get_secretary_top_level_menu_order(): array {
		return array(
			'parish-core',                         // Dashboard.
			'edit.php',                            // Posts.
			'edit.php?post_type=parish_newsletter',
			'parish-mass-times',
			'parish-events',
			'edit.php?post_type=parish_reflection',
			'edit.php?post_type=parish_death_notice',
			'edit.php?post_type=parish_wedding',
			'edit.php?post_type=parish_baptism',
			'edit.php?post_type=parish_church',
			'edit.php?post_type=parish_cemetery',
			'edit.php?post_type=parish_school',
			'edit.php?post_type=parish_group',
			'edit.php?post_type=parish_service',
			'edit.php?post_type=parish_history',
			'edit.php?post_type=parish_prayer',
			'edit.php?post_type=parish_gallery',
			'upload.php',                          // Media.
			'edit.php?post_type=page',             // Pages.
			'parish-slider',
			'parish-about',
		);
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

		$allowed = $defaults;
		$order   = array();

		foreach ( $value as $token ) {
			$token = is_string( $token ) ? trim( $token ) : '';
			if ( '' === $token || ! in_array( $token, $allowed, true ) || in_array( $token, $order, true ) ) {
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
	 * Callback map for promoted custom menu pages.
	 */
	private function get_page_callback_for_slug( string $slug ): ?callable {
		$map = array(
			'parish-about'      => array( $this, 'render_about_page' ),
			'parish-events'     => array( $this, 'render_events_page' ),
			'parish-slider'     => array( $this, 'render_slider_page' ),
			'parish-mass-times' => array( $this, 'render_mass_times_page' ),
		);

		return $map[ $slug ] ?? null;
	}

	/**
	 * Get icon for promoted top-level menu items.
	 */
	private function get_promoted_menu_icon( string $slug ): string {
		$page_icons = array(
			'parish-about'      => 'dashicons-admin-home',
			'parish-events'     => 'dashicons-calendar-alt',
			'parish-slider'     => 'dashicons-images-alt2',
			'parish-mass-times' => 'dashicons-clock',
		);

		if ( isset( $page_icons[ $slug ] ) ) {
			return $page_icons[ $slug ];
		}

		if ( preg_match( '/post_type=([a-z0-9_]+)/', $slug, $matches ) ) {
			$post_type = $matches[1];
			$cpt_icons = array(
				'parish_church'       => 'dashicons-building',
				'parish_clergy'       => 'dashicons-businessman',
				'parish_school'       => 'dashicons-welcome-learn-more',
				'parish_cemetery'     => 'dashicons-location',
				'parish_group'        => 'dashicons-groups',
				'parish_newsletter'   => 'dashicons-media-document',
				'parish_gallery'      => 'dashicons-format-gallery',
				'parish_reflection'   => 'dashicons-format-quote',
				'parish_prayer'       => 'dashicons-book-alt',
				'parish_history'      => 'dashicons-book-alt',
				'parish_death_notice' => 'dashicons-heart',
				'parish_baptism'      => 'dashicons-groups',
				'parish_wedding'      => 'dashicons-heart',
				'parish_service'      => 'dashicons-heart',
				'parish_mass_time'    => 'dashicons-clock',
			);

			if ( isset( $cpt_icons[ $post_type ] ) ) {
				return $cpt_icons[ $post_type ];
			}
		}

		return 'dashicons-admin-page';
	}

	/**
	 * Check if a submenu slug should be promoted to top-level.
	 */
	private function is_promotable_menu_slug( string $slug ): bool {
		$custom_pages = array(
			'parish-about',
			'parish-events',
			'parish-slider',
			'parish-mass-times',
		);

		if ( in_array( $slug, $custom_pages, true ) ) {
			return true;
		}

		return (bool) preg_match( '/^edit\.php\?post_type=parish_[a-z0-9_]+$/', $slug );
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
	 * Render Slider Settings page.
	 */
	public function render_slider_page(): void {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Hero Slider', 'parish-core' ); ?></h1>
			<div id="parish-slider-app">
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
			<h1><?php esc_html_e( 'Mass Times', 'parish-core' ); ?></h1>
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
