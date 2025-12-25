<?php
/**
 * Block Bindings integration for Parish Core.
 *
 * Implements the WordPress 6.5+ Block Bindings API to enable core blocks
 * (Heading, Paragraph, Button, Image) to bind their attributes to custom
 * post meta fields.
 *
 * @link https://developer.wordpress.org/block-editor/reference-guides/block-api/block-bindings/
 * @link https://make.wordpress.org/core/2024/10/21/block-bindings-improvements-to-the-editor-experience-in-6-7/
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles block bindings registration and editor asset loading.
 */
class Parish_Block_Bindings {

	/**
	 * Singleton instance.
	 *
	 * @var Parish_Block_Bindings|null
	 */
	private static ?Parish_Block_Bindings $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return Parish_Block_Bindings
	 */
	public static function instance(): Parish_Block_Bindings {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor - hook into WordPress.
	 */
	private function __construct() {
		// Register binding source before CPTs (priority 5) but after core.
		add_action( 'init', array( $this, 'register_binding_source' ), 4 );

		// Enqueue editor assets for block bindings.
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
	}

	/**
	 * Register the parish/post-meta block bindings source.
	 *
	 * This registers the server-side component that handles front-end rendering.
	 * The client-side component (JavaScript) handles editor interactions.
	 *
	 * @return void
	 */
	public function register_binding_source(): void {
		// Block Bindings API was introduced in WP 6.5.
		if ( ! function_exists( 'register_block_bindings_source' ) ) {
			return;
		}

		register_block_bindings_source(
			'parish/post-meta',
			array(
				'label'              => __( 'Parish Post Meta', 'parish-core' ),
				'uses_context'       => array( 'postId', 'postType' ),
				'get_value_callback' => array( $this, 'get_binding_value' ),
			)
		);
	}

	/**
	 * Get the value for a bound attribute.
	 *
	 * Called during front-end rendering when a block attribute is bound
	 * to our source. Retrieves the post meta value.
	 *
	 * @param array    $source_args    Binding arguments (contains 'key').
	 * @param WP_Block $block_instance The block being rendered.
	 * @param string   $attribute_name The name of the attribute being bound.
	 * @return mixed|null The meta value or null if invalid.
	 */
	public function get_binding_value( array $source_args, $block_instance, string $attribute_name ) {
		// Get post ID from block context.
		$post_id = $block_instance->context['postId'] ?? 0;
		$post_id = (int) $post_id;

		// Fallback to current post if no context.
		if ( $post_id <= 0 ) {
			$post_id = get_the_ID();
		}

		// Validate we have a post.
		if ( $post_id <= 0 ) {
			return null;
		}

		// Get the meta key from binding args.
		$meta_key = $source_args['key'] ?? '';

		// Validate meta key is a parish key.
		if ( ! $this->is_valid_meta_key( $meta_key ) ) {
			return null;
		}

		// Get and return the meta value.
		$value = get_post_meta( $post_id, $meta_key, true );

		// Return null for empty values to allow default/placeholder behavior.
		if ( $value === '' || $value === null ) {
			return null;
		}

		return $value;
	}

	/**
	 * Validate that a meta key is a valid parish meta key.
	 *
	 * All parish post meta keys must be prefixed with 'parish_' for security
	 * and namespacing purposes.
	 *
	 * @param string $meta_key The meta key to validate.
	 * @return bool True if valid, false otherwise.
	 */
	private function is_valid_meta_key( string $meta_key ): bool {
		// Must be a non-empty string.
		if ( empty( $meta_key ) ) {
			return false;
		}

		// Must start with 'parish_' prefix.
		if ( ! str_starts_with( $meta_key, 'parish_' ) ) {
			return false;
		}

		// Prevent directory traversal or other shenanigans.
		if ( preg_match( '/[^a-z0-9_]/', $meta_key ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Enqueue block bindings editor script.
	 *
	 * Loads the JavaScript that handles editor-side reading and writing
	 * of bound meta values.
	 *
	 * @return void
	 */
	public function enqueue_editor_assets(): void {
		// Only load in admin context.
		if ( ! is_admin() ) {
			return;
		}

		$screen = get_current_screen();

		// Only load on post editor screens.
		if ( ! $screen || 'post' !== $screen->base ) {
			return;
		}

		// Only load for parish_ prefixed post types.
		$post_type = $screen->post_type ?? '';
		if ( empty( $post_type ) || ! str_starts_with( $post_type, 'parish_' ) ) {
			return;
		}

		// Use built assets if available (npm run build).
		$asset_file = PARISH_CORE_PATH . 'build/block-bindings.asset.php';

		if ( file_exists( $asset_file ) ) {
			$asset = require $asset_file;

			wp_enqueue_script(
				'parish-block-bindings',
				PARISH_CORE_URL . 'build/block-bindings.js',
				$asset['dependencies'],
				$asset['version'],
				true
			);
		} else {
			// Fallback to legacy script location.
			wp_enqueue_script(
				'parish-block-bindings',
				PARISH_CORE_URL . 'assets/js/parish-post-meta-bindings.js',
				array(
					'wp-blocks',
					'wp-data',
					'wp-core-data',
					'wp-rich-text',
				),
				PARISH_CORE_VERSION,
				true
			);
		}

		// Set script translations for i18n.
		wp_set_script_translations( 'parish-block-bindings', 'parish-core' );
	}
}
