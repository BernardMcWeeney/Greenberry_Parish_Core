<?php
/**
 * Auto-generate post titles for Parish CPTs.
 *
 * Generates meaningful titles from meta fields when posts are saved,
 * making it easier to identify posts in admin lists.
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles automatic title generation for Parish CPTs.
 */
class Parish_Auto_Title {

	/**
	 * Singleton instance.
	 *
	 * @var Parish_Auto_Title|null
	 */
	private static ?Parish_Auto_Title $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return Parish_Auto_Title
	 */
	public static function instance(): Parish_Auto_Title {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor - hook into WordPress.
	 */
	private function __construct() {
		// Generate title on save.
		add_filter( 'wp_insert_post_data', array( $this, 'generate_title' ), 10, 2 );
	}

	/**
	 * Generate auto-title from meta fields.
	 *
	 * @param array $data    Post data being saved.
	 * @param array $postarr Raw post array including meta.
	 * @return array Modified post data.
	 */
	public function generate_title( array $data, array $postarr ): array {
		$post_type = $data['post_type'] ?? '';

		// Only process our CPTs.
		if ( ! str_starts_with( $post_type, 'parish_' ) ) {
			return $data;
		}

		// Skip auto-drafts being saved as revisions (autosave performance).
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $data;
		}

		// Skip if this is a revision.
		if ( wp_is_post_revision( $postarr['ID'] ?? 0 ) ) {
			return $data;
		}

		// Get the post ID (0 for new posts).
		$post_id = $postarr['ID'] ?? 0;

		// Get meta from postarr or from database if updating.
		$meta = $this->get_meta_for_title( $post_id, $postarr );

		// Generate title based on post type.
		$generated_title = $this->generate_title_for_type( $post_type, $meta );

		// Only update if we generated a title.
		if ( ! empty( $generated_title ) ) {
			$data['post_title'] = $generated_title;

			// Also update the slug if it's auto-draft or new.
			if ( empty( $data['post_name'] ) || $data['post_status'] === 'auto-draft' ) {
				$data['post_name'] = sanitize_title( $generated_title );
			}
		}

		return $data;
	}

	/**
	 * Get meta values for title generation.
	 *
	 * Combines incoming meta with existing database meta.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $postarr Raw post array.
	 * @return array Meta values.
	 */
	private function get_meta_for_title( int $post_id, array $postarr ): array {
		$meta = array();

		// Get existing meta from database.
		if ( $post_id > 0 ) {
			$existing = get_post_meta( $post_id );
			foreach ( $existing as $key => $value ) {
				$meta[ $key ] = is_array( $value ) ? ( $value[0] ?? '' ) : $value;
			}
		}

		// Merge in any meta from the current request.
		if ( ! empty( $postarr['meta_input'] ) && is_array( $postarr['meta_input'] ) ) {
			$meta = array_merge( $meta, $postarr['meta_input'] );
		}

		return $meta;
	}

	/**
	 * Generate title for a specific post type.
	 *
	 * @param string $post_type Post type.
	 * @param array  $meta      Meta values.
	 * @return string Generated title or empty string.
	 */
	private function generate_title_for_type( string $post_type, array $meta ): string {
		switch ( $post_type ) {
			case 'parish_death_notice':
				return $this->generate_death_notice_title( $meta );

			case 'parish_wedding':
				return $this->generate_wedding_title( $meta );

			case 'parish_baptism':
				return $this->generate_baptism_title( $meta );

			default:
				return '';
		}
	}

	/**
	 * Generate death notice title: "Name, Address - Date".
	 *
	 * @param array $meta Meta values.
	 * @return string Generated title.
	 */
	private function generate_death_notice_title( array $meta ): string {
		$name      = trim( $meta['parish_full_name'] ?? '' );
		$residence = trim( $meta['parish_residence'] ?? '' );
		$date      = $meta['parish_date_of_death'] ?? '';

		// Need at least a name.
		if ( empty( $name ) ) {
			return '';
		}

		$parts = array( $name );

		if ( ! empty( $residence ) ) {
			$parts[] = $residence;
		}

		if ( ! empty( $date ) ) {
			// Format the date nicely.
			$formatted_date = $this->format_date( $date );
			if ( $formatted_date ) {
				$parts[] = $formatted_date;
			}
		}

		return implode( ' - ', $parts );
	}

	/**
	 * Generate wedding title: "Bride & Groom - Date".
	 *
	 * @param array $meta Meta values.
	 * @return string Generated title.
	 */
	private function generate_wedding_title( array $meta ): string {
		$bride = trim( $meta['parish_bride_name'] ?? '' );
		$groom = trim( $meta['parish_groom_name'] ?? '' );
		$date  = $meta['parish_wedding_date'] ?? '';

		// Need at least one name.
		if ( empty( $bride ) && empty( $groom ) ) {
			return '';
		}

		// Build couple name.
		$couple = '';
		if ( ! empty( $bride ) && ! empty( $groom ) ) {
			$couple = $bride . ' & ' . $groom;
		} elseif ( ! empty( $bride ) ) {
			$couple = $bride;
		} else {
			$couple = $groom;
		}

		// Add date if available.
		if ( ! empty( $date ) ) {
			$formatted_date = $this->format_date( $date );
			if ( $formatted_date ) {
				return $couple . ' - ' . $formatted_date;
			}
		}

		return $couple;
	}

	/**
	 * Generate baptism title: "Child Name - Date".
	 *
	 * @param array $meta Meta values.
	 * @return string Generated title.
	 */
	private function generate_baptism_title( array $meta ): string {
		$child_name = trim( $meta['parish_child_name'] ?? '' );
		$date       = $meta['parish_baptism_date'] ?? '';

		// Need at least a name.
		if ( empty( $child_name ) ) {
			return '';
		}

		// Add date if available.
		if ( ! empty( $date ) ) {
			$formatted_date = $this->format_date( $date );
			if ( $formatted_date ) {
				return $child_name . ' - ' . $formatted_date;
			}
		}

		return $child_name;
	}

	/**
	 * Format a date string for display.
	 *
	 * @param string $date Date string (YYYY-MM-DD or readable).
	 * @return string Formatted date or original string.
	 */
	private function format_date( string $date ): string {
		$date = trim( $date );

		if ( empty( $date ) ) {
			return '';
		}

		// Try to parse as a date.
		$timestamp = strtotime( $date );

		if ( $timestamp === false ) {
			// Not a parseable date, return as-is.
			return $date;
		}

		// Format as "15 January 2025".
		return wp_date( 'j F Y', $timestamp );
	}
}
