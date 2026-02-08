<?php
/**
 * Block registration for Parish Core.
 *
 * Handles registration of custom blocks and loading of editor assets.
 * Uses webpack-built assets when available, falls back to legacy scripts.
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles block registration and script loading.
 */
class Parish_Blocks {

	/**
	 * Singleton instance.
	 *
	 * @var Parish_Blocks|null
	 */
	private static ?Parish_Blocks $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return Parish_Blocks
	 */
	public static function instance(): Parish_Blocks {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor - hook into WordPress.
	 */
	private function __construct() {
		add_action( 'init', array( $this, 'register_blocks' ) );
	}

	/**
	 * Register custom blocks and editor scripts.
	 *
	 * @return void
	 */
	public function register_blocks(): void {
		$this->register_editor_script();
		$this->register_core_blocks();
		$this->register_module_blocks();
	}

	/**
	 * Register the main editor blocks script.
	 *
	 * Uses webpack-built assets if available, falls back to legacy.
	 *
	 * @return void
	 */
	private function register_editor_script(): void {
		$asset_file = PARISH_CORE_PATH . 'build/editor-blocks.asset.php';

		if ( file_exists( $asset_file ) ) {
			// Use webpack-built assets.
			$asset = require $asset_file;

			wp_register_script(
				'parish-core-editor-blocks',
				PARISH_CORE_URL . 'build/editor-blocks.js',
				$asset['dependencies'],
				$asset['version'],
				true
			);
		} else {
			// Fallback to legacy script.
			wp_register_script(
				'parish-core-editor-blocks',
				PARISH_CORE_URL . 'assets/js/parish-core-editor-blocks.js',
				array(
					'wp-blocks',
					'wp-element',
					'wp-components',
					'wp-i18n',
					'wp-data',
					'wp-api-fetch',
					'wp-block-editor',
				),
				PARISH_CORE_VERSION,
				true
			);
		}

		// Set script translations.
		wp_set_script_translations( 'parish-core-editor-blocks', 'parish-core' );
	}

	/**
	 * Register core shared blocks.
	 *
	 * @return void
	 */
	private function register_core_blocks(): void {
		// Related Church selector block (meta-backed).
		register_block_type(
			'parish/related-church',
			array(
				'editor_script'   => 'parish-core-editor-blocks',
				'render_callback' => '__return_empty_string',
				'supports'        => array(
					'html' => false,
				),
			)
		);

		// Church Selector block with parish church relationship + manual fallback.
		register_block_type(
			'parish/church-selector',
			array(
				'editor_script'   => 'parish-core-editor-blocks',
				'render_callback' => array( $this, 'render_church_selector' ),
				'uses_context'    => array( 'postType', 'postId' ),
				'supports'        => array(
					'html'     => false,
					'multiple' => false,
				),
			)
		);
	}

	/**
	 * Render callback for church-selector block.
	 *
	 * Displays the selected church name on the frontend.
	 *
	 * @param array    $attributes Block attributes.
	 * @param string   $content    Block content.
	 * @param WP_Block $block      Block instance.
	 * @return string Rendered HTML.
	 */
	public function render_church_selector( array $attributes, string $content, WP_Block $block ): string {
		$post_id = $block->context['postId'] ?? get_the_ID();

		if ( ! $post_id ) {
			return '';
		}

		$related_church = absint( get_post_meta( $post_id, 'parish_related_church', true ) );
		$church_name    = get_post_meta( $post_id, 'parish_church_name', true );

		// If there's a related church, get its title.
		if ( $related_church > 0 ) {
			$church_post = get_post( $related_church );
			if ( $church_post && 'parish_church' === $church_post->post_type ) {
				$church_name = get_the_title( $church_post );
			}
		}

		if ( empty( $church_name ) ) {
			return '';
		}

		return sprintf(
			'<p class="parish-church-name">%s</p>',
			esc_html( $church_name )
		);
	}

	/**
	 * Discover and register all blocks from module directories.
	 *
	 * Each module can define a block.php file that contains a class
	 * with a public static register() method.
	 *
	 * @return void
	 */
	private function register_module_blocks(): void {
		$base  = PARISH_CORE_PATH . 'includes/cpt/modules/';
		$files = glob( $base . '*/*block.php' );

		if ( empty( $files ) ) {
			return;
		}

		foreach ( $files as $file ) {
			if ( ! file_exists( $file ) ) {
				continue;
			}

			// Detect which classes are introduced by this module file.
			$before = get_declared_classes();

			require_once $file;

			$after      = get_declared_classes();
			$new_classes = array_values( array_diff( $after, $before ) );

			if ( empty( $new_classes ) ) {
				continue;
			}

			foreach ( $new_classes as $class ) {
				// Only register classes that implement a public static register() method.
				if ( is_string( $class ) && method_exists( $class, 'register' ) && is_callable( array( $class, 'register' ) ) ) {
					call_user_func( array( $class, 'register' ) );
				}
			}
		}
	}
}
