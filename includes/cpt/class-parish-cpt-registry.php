<?php
/**
 * CPT registry loader for Parish Core.
 *
 * Handles auto-discovery and registration of all custom post types and
 * taxonomies from module directories. Includes Block Bindings support.
 *
 * @link https://developer.wordpress.org/block-editor/reference-guides/block-api/block-bindings/
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles CPT and taxonomy registration.
 */
class Parish_CPT_Registry {

	/**
	 * Singleton instance.
	 *
	 * @var Parish_CPT_Registry|null
	 */
	private static ?Parish_CPT_Registry $instance = null;

	/**
	 * Flag to prevent double registration.
	 *
	 * @var bool
	 */
	private bool $did_register_post_types = false;

	/**
	 * Flag to prevent double registration.
	 *
	 * @var bool
	 */
	private bool $did_register_taxonomies = false;

	/**
	 * Get singleton instance.
	 *
	 * @return Parish_CPT_Registry
	 */
	public static function instance(): Parish_CPT_Registry {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor - hook into WordPress.
	 */
	private function __construct() {
		add_action( 'init', array( $this, 'register_post_types' ), 5 );
		add_action( 'init', array( $this, 'register_taxonomies' ), 5 );

		// Enable Block Bindings editing for users who can edit the post.
		add_filter( 'block_editor_settings_all', array( $this, 'filter_block_editor_settings_all' ), 10, 2 );

		// Add taxonomy filters to admin list tables.
		add_action( 'restrict_manage_posts', array( $this, 'add_taxonomy_filters' ) );
	}

	/**
	 * Filter block editor settings to enable Block Bindings.
	 *
	 * Sets canUpdateBlockBindings to true for parish CPTs when the user
	 * has permission to edit the post. This is required for the editor
	 * to allow editing of bound block attributes.
	 *
	 * @param array                   $settings       Editor settings.
	 * @param WP_Block_Editor_Context $editor_context Editor context.
	 * @return array Modified settings.
	 */
	public function filter_block_editor_settings_all( array $settings, $editor_context ): array {
		if ( empty( $editor_context ) || empty( $editor_context->post ) || ! ( $editor_context->post instanceof WP_Post ) ) {
			return $settings;
		}

		$post = $editor_context->post;

		// Only affect our CPTs.
		if ( ! str_starts_with( (string) $post->post_type, 'parish_' ) ) {
			return $settings;
		}

		// If the user can edit this post, allow updating binding values in-editor.
		if ( current_user_can( 'edit_post', $post->ID ) ) {
			$settings['canUpdateBlockBindings'] = true;
		}

		return $settings;
	}

	/**
	 * Get common arguments shared by all parish CPTs.
	 *
	 * These defaults are merged with module-specific args.
	 * 'custom-fields' support is required for Block Bindings.
	 *
	 * @return array Common CPT arguments.
	 */
	private function get_common_args(): array {
		return array(
			'public'              => true,
			'show_ui'             => true,
			'show_in_rest'        => true, // Required for Block Editor and Block Bindings.
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			// 'custom-fields' is required for Block Bindings to read/write meta.
			'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'custom-fields' ),
		);
	}


	public function register_post_types(): void {
		if ( $this->did_register_post_types ) {
			return;
		}
		$this->did_register_post_types = true;

		$common = $this->get_common_args();
		$base   = PARISH_CORE_PATH . 'includes/cpt/modules/';
		$files  = glob( $base . '*/post-type.php' );

		if ( empty( $files ) ) {
			return;
		}

		foreach ( $files as $path ) {
			$normalized_path = str_replace( '\\', '/', $path );

			// Remove legacy Parish News CPT in favor of default WordPress posts.
			if ( str_ends_with( $normalized_path, '/news/post-type.php' ) ) {
				continue;
			}

			if ( ! file_exists( $path ) ) {
				continue;
			}

			$definition = require $path;

			if ( ! is_array( $definition ) ) {
				continue;
			}

			$post_type = $definition['post_type'] ?? '';
			$args      = $definition['args'] ?? array();
			$feature   = $definition['feature'] ?? '';

			if ( empty( $post_type ) || empty( $args ) ) {
				continue;
			}

			if ( ! empty( $feature ) && class_exists( 'Parish_Core' ) && ! Parish_Core::is_feature_enabled( $feature ) ) {
				continue;
			}

			register_post_type( $post_type, array_merge( $common, $args ) );
		}
	}


	public function register_taxonomies(): void {
		if ( $this->did_register_taxonomies ) {
			return;
		}
		$this->did_register_taxonomies = true;

		$base  = PARISH_CORE_PATH . 'includes/cpt/modules/';
		$files = glob( $base . '*/tax.php' );

		if ( empty( $files ) ) {
			return;
		}

		foreach ( $files as $path ) {
			$normalized_path = str_replace( '\\', '/', $path );

			// Remove legacy Parish News taxonomies in favor of default WordPress taxonomies.
			if ( str_ends_with( $normalized_path, '/news/tax.php' ) ) {
				continue;
			}

			if ( ! file_exists( $path ) ) {
				continue;
			}

			$definition = require $path;

			if ( ! is_array( $definition ) ) {
				continue;
			}

			$post_type  = $definition['post_type'] ?? '';
			$taxonomies = $definition['taxonomies'] ?? array();

			if ( empty( $post_type ) || empty( $taxonomies ) ) {
				continue;
			}

			// Register each taxonomy.
			foreach ( $taxonomies as $taxonomy => $config ) {
				$args          = $config['args'] ?? array();
				$default_terms = $config['default_terms'] ?? array();

				if ( empty( $args ) ) {
					continue;
				}

				// Register the taxonomy.
				register_taxonomy( $taxonomy, $post_type, $args );

				// Insert default terms if they don't exist.
				if ( ! empty( $default_terms ) ) {
					foreach ( $default_terms as $term_name ) {
						if ( ! term_exists( $term_name, $taxonomy ) ) {
							wp_insert_term( $term_name, $taxonomy );
						}
					}
				}
			}
		}
	}

	/**
	 * Add taxonomy dropdown filters to admin list tables.
	 *
	 * Displays dropdown filters for all taxonomies associated with
	 * parish CPTs in the admin posts list.
	 *
	 * @param string $post_type The current post type.
	 * @return void
	 */
	public function add_taxonomy_filters( string $post_type ): void {
		// Only add filters for parish CPTs.
		if ( ! str_starts_with( $post_type, 'parish_' ) ) {
			return;
		}

		// Get all taxonomies for this post type.
		$taxonomies = get_object_taxonomies( $post_type, 'objects' );

		if ( empty( $taxonomies ) ) {
			return;
		}

		foreach ( $taxonomies as $taxonomy ) {
			// Skip if taxonomy shouldn't show UI.
			if ( ! $taxonomy->show_ui ) {
				continue;
			}

			// Get the currently selected term.
			$selected = isset( $_GET[ $taxonomy->name ] ) ? sanitize_text_field( wp_unslash( $_GET[ $taxonomy->name ] ) ) : '';

			// Render the dropdown.
			wp_dropdown_categories(
				array(
					'show_option_all' => $taxonomy->labels->all_items,
					'taxonomy'        => $taxonomy->name,
					'name'            => $taxonomy->name,
					'orderby'         => 'name',
					'selected'        => $selected,
					'hierarchical'    => $taxonomy->hierarchical,
					'show_count'      => true,
					'hide_empty'      => true,
					'value_field'     => 'slug',
				)
			);
		}
	}
}
