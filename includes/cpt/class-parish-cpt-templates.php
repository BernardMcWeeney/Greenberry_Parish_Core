<?php
/**
 * Block templates for Parish CPTs.
 *
 * Loads Gutenberg block templates from module directories.
 * Templates use the Block Bindings API to connect blocks to post meta.
 *
 * @link https://developer.wordpress.org/block-editor/reference-guides/block-api/block-bindings/
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles loading block templates for Parish CPTs.
 */
class Parish_CPT_Templates {

	/**
	 * Load a template from a module directory.
	 *
	 * @param string $module The module directory name.
	 * @return array The template array, or empty array if not found.
	 */
	private static function load_module_template( string $module ): array {
		$path = PARISH_CORE_PATH . 'includes/cpt/modules/' . $module . '/template.php';

		if ( file_exists( $path ) ) {
			$template = require $path;
			return is_array( $template ) ? $template : array();
		}

		return array();
	}

	/**
	 * Magic method to handle get_*_template calls dynamically.
	 *
	 * Converts method names like get_death_notice_template to module names like death-notice.
	 *
	 * @param string $name      The method name.
	 * @param array  $arguments The method arguments.
	 * @return array The template array.
	 */
	public static function __callStatic( string $name, array $arguments ): array {
		// Check if this is a get_*_template call.
		if ( str_starts_with( $name, 'get_' ) && str_ends_with( $name, '_template' ) ) {
			// Extract the module name: get_death_notice_template -> death_notice.
			$module = substr( $name, 4, -9 );
			// Convert underscores to hyphens: death_notice -> death-notice.
			$module = str_replace( '_', '-', $module );

			return self::load_module_template( $module );
		}

		return array();
	}

	// ========================================
	// Explicit methods for IDE autocompletion
	// ========================================

	/**
	 * Get death notice template.
	 *
	 * @return array
	 */
	public static function get_death_notice_template(): array {
		return self::load_module_template( 'death-notice' );
	}

	/**
	 * Get baptism template.
	 *
	 * @return array
	 */
	public static function get_baptism_template(): array {
		return self::load_module_template( 'baptism' );
	}

	/**
	 * Get wedding template.
	 *
	 * @return array
	 */
	public static function get_wedding_template(): array {
		return self::load_module_template( 'wedding' );
	}

	/**
	 * Get church template.
	 *
	 * @return array
	 */
	public static function get_church_template(): array {
		return self::load_module_template( 'church' );
	}

	/**
	 * Get school template.
	 *
	 * @return array
	 */
	public static function get_school_template(): array {
		return self::load_module_template( 'school' );
	}

	/**
	 * Get cemetery template.
	 *
	 * @return array
	 */
	public static function get_cemetery_template(): array {
		return self::load_module_template( 'cemetery' );
	}

	/**
	 * Get group template.
	 *
	 * @return array
	 */
	public static function get_group_template(): array {
		return self::load_module_template( 'group' );
	}

	/**
	 * Get newsletter template.
	 *
	 * @return array
	 */
	public static function get_newsletter_template(): array {
		return self::load_module_template( 'newsletter' );
	}

	/**
	 * Get news template.
	 *
	 * @return array
	 */
	public static function get_news_template(): array {
		return self::load_module_template( 'news' );
	}

	/**
	 * Get gallery template.
	 *
	 * @return array
	 */
	public static function get_gallery_template(): array {
		return self::load_module_template( 'gallery' );
	}

	/**
	 * Get reflection template.
	 *
	 * @return array
	 */
	public static function get_reflection_template(): array {
		return self::load_module_template( 'reflection' );
	}

	/**
	 * Get prayer template.
	 *
	 * @return array
	 */
	public static function get_prayer_template(): array {
		return self::load_module_template( 'prayer' );
	}

	/**
	 * Get travels template.
	 *
	 * @return array
	 */
	public static function get_travels_template(): array {
		return self::load_module_template( 'travels' );
	}

	/**
	 * Get intention template.
	 *
	 * @return array
	 */
	public static function get_intention_template(): array {
		return self::load_module_template( 'intention' );
	}
}
