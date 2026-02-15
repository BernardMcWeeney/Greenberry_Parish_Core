<?php
/**
 * Meta registry for Parish Core CPTs.
 *
 * Automatically discovers and registers post meta fields from module definitions.
 * Each module's meta.php file defines the schema for its fields.
 *
 * Meta fields are registered with:
 * - REST API support (required for Block Bindings)
 * - Proper authorization callbacks
 * - Type-appropriate sanitization
 * - Labels for the Attributes panel (WP 6.7+)
 *
 * @link https://developer.wordpress.org/block-editor/reference-guides/block-api/block-bindings/
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles meta field registration for all Parish CPTs.
 */
class Parish_Meta_Registry {

	/**
	 * Singleton instance.
	 *
	 * @var Parish_Meta_Registry|null
	 */
	private static ?Parish_Meta_Registry $instance = null;

	/**
	 * Cached meta definitions from module files.
	 *
	 * @var array<int, array<string,mixed>>
	 */
	private array $definitions = array();

	/**
	 * Get singleton instance.
	 *
	 * @return Parish_Meta_Registry
	 */
	public static function instance(): Parish_Meta_Registry {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor - hook into WordPress init.
	 */
	private function __construct() {
		// Register meta early so it's available for Block Bindings.
		add_action( 'init', array( $this, 'register_all' ), 6 );
	}

	/**
	 * Load meta definitions from all module meta.php files.
	 *
	 * @return void
	 */
	private function load_definitions(): void {
		if ( ! empty( $this->definitions ) ) {
			return;
		}

		$base  = PARISH_CORE_PATH . 'includes/cpt/modules/';
		$files = glob( $base . '*/meta.php' );

		if ( empty( $files ) ) {
			$this->definitions = array();
			return;
		}

		foreach ( $files as $file ) {
			$normalized_path = str_replace( '\\', '/', $file );

			// Remove legacy Parish News meta schema in favor of default WordPress posts.
			if ( str_ends_with( $normalized_path, '/news/meta.php' ) ) {
				continue;
			}

			$def = require $file;

			if ( is_array( $def ) && ! empty( $def['post_type'] ) && ! empty( $def['fields'] ) ) {
				$this->definitions[] = $def;
			}
		}
	}

	/**
	 * Register all meta fields from loaded definitions.
	 *
	 * @return void
	 */
	public function register_all(): void {
		$this->load_definitions();

		foreach ( $this->definitions as $def ) {
			$post_type = (string) $def['post_type'];
			$fields    = (array) $def['fields'];

			foreach ( $fields as $field_key => $schema ) {
				$this->register_field( $post_type, (string) $field_key, (array) $schema );
			}
		}
	}

	/**
	 * Register a single meta field.
	 *
	 * @param string $post_type The post type to register for.
	 * @param string $field_key The field key (without parish_ prefix).
	 * @param array  $schema    The field schema definition.
	 * @return void
	 */
	private function register_field( string $post_type, string $field_key, array $schema ): void {
		// All parish meta keys are prefixed.
		$meta_key = 'parish_' . $field_key;

		$type  = $schema['type'] ?? 'string';
		$label = $schema['label'] ?? null;

		// Determine sanitization callback based on type or explicit setting.
		$sanitize = $this->get_sanitize_callback( $type, $schema );

		// Always provide a default to avoid placeholder behavior.
		$default = $this->get_default_value( $type, $schema );

		// Build REST schema for proper API exposure.
		$rest_schema = $this->build_rest_schema( $type, $schema );

		$args = array(
			'type'              => $type,
			'single'            => true,
			'show_in_rest'      => $rest_schema,
			'sanitize_callback' => $sanitize,
			'default'           => $default,

			// Authorization callback - required for meta updates to work.
			// Block Bindings setValues() will fail without proper auth.
			'auth_callback'     => array( $this, 'authorize_meta_update' ),
		);

		// Add label for WP 6.7+ Attributes panel.
		if ( is_string( $label ) && '' !== $label ) {
			$args['label'] = $label;
		}

		register_post_meta( $post_type, $meta_key, $args );
	}

	/**
	 * Get the appropriate sanitization callback for a field type.
	 *
	 * @param string $type   The field type.
	 * @param array  $schema The field schema.
	 * @return callable The sanitization callback.
	 */
	private function get_sanitize_callback( string $type, array $schema ): callable {
		// Allow explicit override.
		if ( ! empty( $schema['sanitize_callback'] ) && is_callable( $schema['sanitize_callback'] ) ) {
			return $schema['sanitize_callback'];
		}

		// Type-based defaults.
		switch ( $type ) {
			case 'integer':
			case 'number':
				return 'absint';

			case 'boolean':
				return 'rest_sanitize_boolean';

			case 'string':
			default:
				// Check if this field might contain HTML.
				$might_have_html = in_array(
					$schema['sanitize_callback'] ?? '',
					array( 'wp_kses_post', 'sanitize_textarea_field' ),
					true
				);

				if ( $might_have_html ) {
					return 'wp_kses_post';
				}

				return 'sanitize_text_field';
		}
	}

	/**
	 * Get the default value for a field type.
	 *
	 * @param string $type   The field type.
	 * @param array  $schema The field schema.
	 * @return mixed The default value.
	 */
	private function get_default_value( string $type, array $schema ) {
		// Allow explicit override.
		if ( array_key_exists( 'default', $schema ) ) {
			return $schema['default'];
		}

		// Type-based defaults.
		switch ( $type ) {
			case 'integer':
			case 'number':
				return 0;

			case 'boolean':
				return false;

			case 'array':
				return array();

			case 'object':
				return new stdClass();

			case 'string':
			default:
				return '';
		}
	}

	/**
	 * Build REST API schema for proper exposure.
	 *
	 * @param string $type   The field type.
	 * @param array  $schema The field schema.
	 * @return array|bool REST schema configuration.
	 */
	private function build_rest_schema( string $type, array $schema ) {
		// Respect explicit false.
		if ( isset( $schema['show_in_rest'] ) && false === $schema['show_in_rest'] ) {
			return false;
		}

		// Allow full custom schema.
		if ( is_array( $schema['show_in_rest'] ?? null ) ) {
			return $schema['show_in_rest'];
		}

		// Build schema based on type.
		$rest_type = $type;

		// Map PHP types to JSON Schema types.
		if ( 'integer' === $type ) {
			$rest_type = 'integer';
		} elseif ( 'number' === $type ) {
			$rest_type = 'number';
		} elseif ( 'boolean' === $type ) {
			$rest_type = 'boolean';
		} else {
			$rest_type = 'string';
		}

		$rest_schema = array(
			'schema' => array(
				'type'    => $rest_type,
				'default' => $this->get_default_value( $type, $schema ),
			),
		);

		// Add context for full API access.
		$rest_schema['schema']['context'] = array( 'view', 'edit' );

		return $rest_schema;
	}

	/**
	 * Authorization callback for meta updates.
	 *
	 * Checks if the current user can edit the post.
	 * This is required for Block Bindings setValues() to work.
	 *
	 * @param bool   $allowed  Whether the user is allowed (unused, we recalculate).
	 * @param string $meta_key The meta key being updated.
	 * @param int    $post_id  The post ID.
	 * @return bool True if user can update, false otherwise.
	 */
	public function authorize_meta_update( bool $allowed, string $meta_key, int $post_id ): bool {
		// Validate post ID.
		$post_id = absint( $post_id );
		if ( $post_id <= 0 ) {
			return false;
		}

		// Validate meta key belongs to parish.
		if ( ! str_starts_with( $meta_key, 'parish_' ) ) {
			return false;
		}

		// Check user capability.
		return current_user_can( 'edit_post', $post_id );
	}

	/**
	 * Get all registered meta keys for a post type.
	 *
	 * @param string $post_type The post type.
	 * @return array Array of meta keys.
	 */
	public function get_meta_keys_for_post_type( string $post_type ): array {
		$this->load_definitions();

		$keys = array();

		foreach ( $this->definitions as $def ) {
			if ( $def['post_type'] !== $post_type ) {
				continue;
			}

			foreach ( array_keys( $def['fields'] ) as $field_key ) {
				$keys[] = 'parish_' . $field_key;
			}
		}

		return $keys;
	}
}
