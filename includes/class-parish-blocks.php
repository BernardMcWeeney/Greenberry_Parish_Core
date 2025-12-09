<?php
/**
 * Block registration.
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Parish_Blocks {

	private static ?Parish_Blocks $instance = null;

	public static function instance(): Parish_Blocks {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'register_blocks' ) );
	}

	/**
	 * Register custom blocks.
	 */
	public function register_blocks(): void {
		// Single bundled script for all parish editor blocks.
		wp_register_script(
			'parish-core-editor-blocks',
			PARISH_CORE_URL . 'assets/js/parish-core-editor-blocks.js',
			array(
				'wp-blocks',
				'wp-element',
				'wp-components',
				'wp-i18n',
				'wp-data',
				'wp-block-editor', // fallback for older WP in JS.
			),
			PARISH_CORE_VERSION,
			true
		);

		// Related Church selector block (meta-backed).
		register_block_type(
			'parish/related-church',
			array(
				'editor_script'   => 'parish-core-editor-blocks',
				'render_callback' => '__return_empty_string', // no front-end HTML from the block itself
				'supports'        => array(
					'html' => false,
				),
			)
		);
	}
}
