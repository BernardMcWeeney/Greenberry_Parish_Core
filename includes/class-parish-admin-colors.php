<?php
/**
 * Admin Colors Module - Custom WordPress Admin Color Scheme
 *
 * This module provides a customizable admin color scheme with accessibility in mind.
 * Colors can be configured via the Parish Core Settings page.
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Parish_Admin_Colors class.
 */
class Parish_Admin_Colors {

	/**
	 * Singleton instance.
	 *
	 * @var Parish_Admin_Colors|null
	 */
	private static ?Parish_Admin_Colors $instance = null;

	/**
	 * Default color scheme.
	 *
	 * @var array
	 */
	private array $defaults = array(
		'menu_text'    => '#ffffff',
		'base_menu'    => '#1d2327',
		'highlight'    => '#2271b1',
		'notification' => '#d63638',
		'background'   => '#f0f0f1',
		'links'        => '#2271b1',
		'buttons'      => '#2271b1',
		'form_inputs'  => '#2271b1',
	);

	/**
	 * Get singleton instance.
	 */
	public static function instance(): Parish_Admin_Colors {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		// Only run in admin.
		if ( ! is_admin() ) {
			return;
		}

		// Output custom color CSS.
		add_action( 'admin_head', array( $this, 'output_color_scheme' ), 999 );
	}

	/**
	 * Check if custom colors are enabled.
	 */
	private function is_enabled(): bool {
		return (bool) Parish_Core::get_setting( 'admin_colors_enabled', false );
	}

	/**
	 * Get color values from settings.
	 */
	public function get_colors(): array {
		$settings = Parish_Core::get_settings();

		return array(
			'menu_text'    => $settings['admin_color_menu_text'] ?? $this->defaults['menu_text'],
			'base_menu'    => $settings['admin_color_base_menu'] ?? $this->defaults['base_menu'],
			'highlight'    => $settings['admin_color_highlight'] ?? $this->defaults['highlight'],
			'notification' => $settings['admin_color_notification'] ?? $this->defaults['notification'],
			'background'   => $settings['admin_color_background'] ?? $this->defaults['background'],
			'links'        => $settings['admin_color_links'] ?? $this->defaults['links'],
			'buttons'      => $settings['admin_color_buttons'] ?? $this->defaults['buttons'],
			'form_inputs'  => $settings['admin_color_form_inputs'] ?? $this->defaults['form_inputs'],
		);
	}

	/**
	 * Get default colors.
	 */
	public function get_defaults(): array {
		return $this->defaults;
	}

	/**
	 * Output custom color scheme CSS.
	 */
	public function output_color_scheme(): void {
		// Check if enabled.
		if ( ! $this->is_enabled() ) {
			return;
		}

		$colors = $this->get_colors();
		$css    = $this->build_color_css( $colors );

		if ( ! empty( $css ) ) {
			printf(
				'<style id="parish-admin-colors">%s</style>',
				$css // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS is sanitized in build method.
			);
		}
	}

	/**
	 * Check if current page is a block editor.
	 */
	private function is_block_editor_page(): bool {
		global $pagenow;

		$block_pages = array(
			'post.php',
			'post-new.php',
			'site-editor.php',
			'widgets.php',
		);

		return in_array( $pagenow, $block_pages, true );
	}

	/**
	 * Build the color scheme CSS.
	 *
	 * @param array $colors Color values.
	 */
	private function build_color_css( array $colors ): string {
		// Sanitize all colors.
		foreach ( $colors as $key => $value ) {
			$colors[ $key ] = $this->sanitize_hex_color( $value );
		}

		// Generate color variants.
		$highlight_dark = $this->adjust_brightness( $colors['highlight'], -20 );
		$highlight_darker = $this->adjust_brightness( $colors['highlight'], -30 );
		$highlight_light = $this->adjust_brightness( $colors['highlight'], 15 );
		$menu_dark = $this->adjust_brightness( $colors['base_menu'], -15 );
		$menu_submenu = $this->adjust_brightness( $colors['base_menu'], -25 );
		$links_dark = $this->adjust_brightness( $colors['links'], -15 );
		$buttons_dark = $this->adjust_brightness( $colors['buttons'], -10 );
		$buttons_light = $this->adjust_brightness( $colors['buttons'], 10 );
		$highlight_rgb = $this->hex_to_rgb( $colors['highlight'] );

		// Calculate accessible text color for buttons.
		$button_text = $this->get_contrast_color( $colors['buttons'] );

		return "
/* Parish Core Admin Color Scheme - Accessibility Focused */

/* WordPress admin design tokens */
:root,
body.wp-admin,
body.wp-admin.admin-color-fresh,
body.wp-admin.admin-color-modern {
	--wp-admin-theme-color: {$colors['highlight']};
	--wp-admin-theme-color-darker-10: {$highlight_dark};
	--wp-admin-theme-color-darker-20: {$highlight_darker};
	--wp-admin-theme-color-rgb: {$highlight_rgb};
	--wp-components-color-accent: {$colors['highlight']};
	--wp-components-color-accent-darker-10: {$highlight_dark};
	--wp-components-color-accent-darker-20: {$highlight_darker};
}

/* Page Background */
body.wp-admin {
	background: {$colors['background']};
}

/* Links */
a {
	color: {$colors['links']};
}
a:hover,
a:active,
a:focus {
	color: {$links_dark};
}

/* Admin Menu */
#adminmenuback,
#adminmenuwrap,
#adminmenu {
	background: {$colors['base_menu']};
}

#adminmenu a {
	color: {$colors['menu_text']};
}

#adminmenu div.wp-menu-image:before {
	color: {$colors['menu_text']};
}

#adminmenu a:hover,
#adminmenu li.menu-top:hover,
#adminmenu li.opensub > a.menu-top,
#adminmenu li > a.menu-top:focus {
	color: {$colors['menu_text']};
	background-color: {$colors['highlight']};
}

#adminmenu li.menu-top:hover div.wp-menu-image:before,
#adminmenu li.opensub > a.menu-top div.wp-menu-image:before {
	color: {$colors['menu_text']};
}

#adminmenu li.current a.menu-top,
#adminmenu li.wp-has-current-submenu a.wp-has-current-submenu,
#adminmenu li.wp-has-current-submenu .wp-submenu .wp-submenu-head,
.folded #adminmenu li.current.menu-top {
	color: {$colors['menu_text']};
	background: {$colors['highlight']};
}

#adminmenu li.current a.menu-top div.wp-menu-image:before,
#adminmenu li.wp-has-current-submenu a.wp-has-current-submenu div.wp-menu-image:before {
	color: {$colors['menu_text']};
}

/* Submenus */
#adminmenu .wp-submenu,
#adminmenu .wp-has-current-submenu .wp-submenu,
#adminmenu .wp-has-current-submenu.opensub .wp-submenu,
.folded #adminmenu .wp-has-current-submenu .wp-submenu {
	background: {$menu_submenu};
}

#adminmenu .wp-submenu a,
#adminmenu .wp-has-current-submenu .wp-submenu a {
	color: rgba(255, 255, 255, 0.7);
}

#adminmenu .wp-submenu a:hover,
#adminmenu .wp-submenu a:focus,
#adminmenu .wp-has-current-submenu .wp-submenu a:hover,
#adminmenu .wp-has-current-submenu .wp-submenu a:focus {
	color: {$colors['highlight']};
}

#adminmenu .wp-submenu li.current a,
#adminmenu .wp-has-current-submenu .wp-submenu li.current a {
	color: {$colors['menu_text']};
}

/* Collapse button */
#collapse-button,
#collapse-button:hover,
#collapse-button:focus {
	color: {$colors['menu_text']};
}

/* Admin Bar */
#wpadminbar {
	background: {$colors['base_menu']};
}

#wpadminbar .ab-item,
#wpadminbar a.ab-item,
#wpadminbar > #wp-toolbar span.ab-label,
#wpadminbar > #wp-toolbar span.noticon {
	color: {$colors['menu_text']};
}

#wpadminbar:not(.mobile) .ab-top-menu > li:hover > .ab-item,
#wpadminbar:not(.mobile) .ab-top-menu > li > .ab-item:focus,
#wpadminbar.nojq .quicklinks .ab-top-menu > li > .ab-item:focus {
	color: {$colors['highlight']};
	background: {$menu_dark};
}

#wpadminbar:not(.mobile) > #wp-toolbar li:hover span.ab-label,
#wpadminbar:not(.mobile) > #wp-toolbar li.hover span.ab-label,
#wpadminbar:not(.mobile) > #wp-toolbar a:focus span.ab-label {
	color: {$colors['highlight']};
}

#wpadminbar .quicklinks .menupop ul li a,
#wpadminbar .quicklinks .menupop ul li a strong,
#wpadminbar .quicklinks .menupop.hover ul li a {
	color: rgba(255, 255, 255, 0.7);
}

#wpadminbar .quicklinks .menupop ul li a:hover,
#wpadminbar .quicklinks .menupop ul li a:focus,
#wpadminbar .quicklinks .menupop.hover ul li a:hover,
#wpadminbar .quicklinks .menupop.hover ul li a:focus {
	color: {$colors['highlight']};
}

/* Buttons */
.wp-core-ui .button,
.wp-core-ui .button-secondary {
	color: {$colors['buttons']};
	border-color: {$colors['buttons']};
	background: transparent;
}

.wp-core-ui .button:hover,
.wp-core-ui .button-secondary:hover,
.wp-core-ui .button:focus,
.wp-core-ui .button-secondary:focus {
	color: {$buttons_dark};
	border-color: {$buttons_dark};
	background: transparent;
}

.wp-core-ui .button-primary {
	background: {$colors['buttons']};
	border-color: {$colors['buttons']};
	color: {$button_text};
}

.wp-core-ui .button-primary:hover,
.wp-core-ui .button-primary:focus {
	background: {$buttons_light};
	border-color: {$buttons_dark};
	color: {$button_text};
}

.wp-core-ui .button-link,
.wp-core-ui .button-link:active,
.wp-core-ui .button-link:focus,
.wp-core-ui .button-link:hover {
	color: {$colors['links']};
}

.wp-core-ui .button-primary:active {
	background: {$buttons_dark};
	border-color: {$buttons_dark};
	color: {$button_text};
}

.wp-core-ui .button-primary.button-hero {
	box-shadow: 0 2px 0 {$buttons_dark};
}

.wp-core-ui .button-primary:disabled,
.wp-core-ui .button-primary:disabled:hover,
.wp-core-ui .button-primary.button-primary-disabled,
.wp-core-ui .button-primary.disabled {
	color: rgba(255, 255, 255, 0.6) !important;
	background: {$colors['buttons']} !important;
	border-color: {$colors['buttons']} !important;
}

/* Form Elements */
input[type='checkbox']:checked::before {
	content: url(\"data:image/svg+xml;utf8,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2020%2020%27%3E%3Cpath%20d%3D%27M14.83%204.89l1.34.94-5.81%208.38H9.02L5.78%209.67l1.34-1.25%202.57%202.4z%27%20fill%3D%27{$this->url_encode_color($colors['form_inputs'])}%27%2F%3E%3C%2Fsvg%3E\");
}

input[type='radio']:checked::before {
	background: {$colors['form_inputs']};
}

input[type='text']:focus,
input[type='password']:focus,
input[type='color']:focus,
input[type='date']:focus,
input[type='datetime']:focus,
input[type='datetime-local']:focus,
input[type='email']:focus,
input[type='month']:focus,
input[type='number']:focus,
input[type='search']:focus,
input[type='tel']:focus,
input[type='time']:focus,
input[type='url']:focus,
input[type='week']:focus,
input[type='checkbox']:focus,
input[type='radio']:focus,
select:focus,
textarea:focus {
	border-color: {$colors['form_inputs']};
	box-shadow: 0 0 0 1px {$colors['form_inputs']};
}

/* Notifications/Badges */
#adminmenu .awaiting-mod,
#adminmenu .update-plugins,
#adminmenu li.current a .awaiting-mod,
#adminmenu li a.wp-has-current-submenu .update-plugins,
.wp-core-ui .wp-ui-notification {
	color: #fff;
	background: {$colors['notification']};
}

/* Highlight colors */
.wp-core-ui .wp-ui-highlight {
	color: #fff;
	background-color: {$colors['highlight']};
}

.wp-core-ui .wp-ui-text-highlight {
	color: {$colors['highlight']};
}

/* Dashboard welcome panel */
.welcome-panel {
	border-color: {$colors['highlight']};
}

/* Plugin updates */
.plugins .active th.check-column {
	border-left-color: {$colors['highlight']};
}

/* Posts list - current item */
.wp-list-table .is-expanded td,
.wp-list-table .is-expanded th {
	background: rgba(0, 0, 0, 0.03);
}

/* Media */
.media-item .bar,
.media-progress-bar div {
	background-color: {$colors['highlight']};
}

.details.attachment {
	box-shadow: inset 0 0 0 3px #fff, inset 0 0 0 7px {$colors['highlight']};
}

.attachment.details .check {
	background-color: {$colors['highlight']};
	box-shadow: 0 0 0 1px #fff, 0 0 0 2px {$colors['highlight']};
}

/* Themes */
.theme-browser .theme.active .theme-name,
.theme-browser .theme.add-new-theme a:hover::after,
.theme-browser .theme.add-new-theme a:focus::after {
	background: {$colors['highlight']};
}

/* Accessibility: Focus styles */
.wp-core-ui .button:focus,
.wp-core-ui .button-primary:focus,
.wp-core-ui .button-secondary:focus {
	box-shadow: 0 0 0 1px #fff, 0 0 0 3px {$colors['buttons']};
}

a:focus {
	box-shadow: 0 0 0 2px {$colors['links']};
	outline: 2px solid transparent;
}

/* Tabs */
.nav-tab-active,
.nav-tab-active:focus,
.nav-tab-active:focus:active,
.nav-tab-active:hover {
	border-bottom-color: {$colors['background']};
	background: {$colors['background']};
	color: {$colors['links']};
}

/* Notices and chips */
.notice.notice-info,
.notice.notice-success,
.notice.notice-warning,
.notice.notice-error {
	border-left-color: {$colors['highlight']};
}

.components-button.is-primary {
	background: {$colors['buttons']};
	border-color: {$colors['buttons']};
	color: {$button_text};
}

.components-button.is-primary:hover,
.components-button.is-primary:focus {
	background: {$buttons_light};
	border-color: {$buttons_dark};
	color: {$button_text};
}

/* Screen meta links */
#screen-meta-links .show-settings {
	border-color: {$colors['highlight']};
}

#screen-meta-links .show-settings:hover,
#screen-meta-links .show-settings:focus {
	color: {$colors['highlight']};
}
";
	}

	/**
	 * Sanitize hex color.
	 *
	 * @param string $color Hex color value.
	 */
	private function sanitize_hex_color( string $color ): string {
		$color = ltrim( $color, '#' );

		if ( preg_match( '/^[a-f0-9]{6}$/i', $color ) ) {
			return '#' . $color;
		}

		if ( preg_match( '/^[a-f0-9]{3}$/i', $color ) ) {
			return '#' . $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
		}

		return '#1d2327'; // Fallback.
	}

	/**
	 * Adjust color brightness.
	 *
	 * @param string $hex    Hex color.
	 * @param int    $amount Amount to adjust (-100 to 100).
	 */
	private function adjust_brightness( string $hex, int $amount ): string {
		$hex = ltrim( $hex, '#' );

		$r = max( 0, min( 255, hexdec( substr( $hex, 0, 2 ) ) + $amount ) );
		$g = max( 0, min( 255, hexdec( substr( $hex, 2, 2 ) ) + $amount ) );
		$b = max( 0, min( 255, hexdec( substr( $hex, 4, 2 ) ) + $amount ) );

		return sprintf( '#%02x%02x%02x', $r, $g, $b );
	}

	/**
	 * Get contrasting text color for accessibility.
	 *
	 * @param string $hex Background hex color.
	 */
	private function get_contrast_color( string $hex ): string {
		$hex = ltrim( $hex, '#' );

		$r = hexdec( substr( $hex, 0, 2 ) );
		$g = hexdec( substr( $hex, 2, 2 ) );
		$b = hexdec( substr( $hex, 4, 2 ) );

		// Calculate relative luminance (WCAG formula).
		$luminance = ( 0.299 * $r + 0.587 * $g + 0.114 * $b ) / 255;

		// Return white or black based on contrast.
		return $luminance > 0.5 ? '#1d2327' : '#ffffff';
	}

	/**
	 * URL encode color for SVG data URI.
	 *
	 * @param string $color Hex color.
	 */
	private function url_encode_color( string $color ): string {
		return str_replace( '#', '%23', $color );
	}

	/**
	 * Convert hex color to "r, g, b" string.
	 *
	 * @param string $hex Hex color.
	 */
	private function hex_to_rgb( string $hex ): string {
		$hex = ltrim( $hex, '#' );

		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}

		$r = hexdec( substr( $hex, 0, 2 ) );
		$g = hexdec( substr( $hex, 2, 2 ) );
		$b = hexdec( substr( $hex, 4, 2 ) );

		return sprintf( '%d, %d, %d', $r, $g, $b );
	}
}
