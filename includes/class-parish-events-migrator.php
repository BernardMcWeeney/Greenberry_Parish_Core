<?php
/**
 * Parish Events Migrator
 *
 * Handles migration of events from JSON storage to Custom Post Type.
 *
 * @package ParishCore
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Parish Events Migrator class.
 */
class Parish_Events_Migrator {

	/**
	 * Migrate events from JSON to CPT.
	 *
	 * @return array Migration results with counts and errors.
	 */
	public static function migrate_json_to_cpt(): array {
		$events   = json_decode( Parish_Core::get_setting( 'parish_events', '[]' ), true ) ?: array();
		$migrated = 0;
		$errors   = array();

		if ( empty( $events ) ) {
			return array(
				'migrated' => 0,
				'errors'   => array( __( 'No events found to migrate.', 'parish-core' ) ),
			);
		}

		foreach ( $events as $event ) {
			$post_id = wp_insert_post(
				array(
					'post_type'    => 'parish_event',
					'post_title'   => sanitize_text_field( $event['title'] ?? __( 'Untitled Event', 'parish-core' ) ),
					'post_content' => wp_kses_post( $event['description'] ?? '' ),
					'post_status'  => 'publish',
					'meta_input'   => array(
						'parish_event_date'     => $event['date'] ?? '',
						'parish_event_time'     => $event['time'] ?? '',
						'parish_event_location' => $event['location'] ?? '',
						'parish_event_color'    => $event['color'] ?? '#609fae',
					),
				),
				true
			);

			if ( is_wp_error( $post_id ) ) {
				$errors[] = sprintf(
					/* translators: %s: Event title */
					__( 'Failed to migrate event "%s": %s', 'parish-core' ),
					$event['title'] ?? __( 'Unknown', 'parish-core' ),
					$post_id->get_error_message()
				);
			} else {
				// Map old event_type to new taxonomies.
				self::assign_legacy_type( $post_id, $event['event_type'] ?? '' );
				++$migrated;
			}
		}

		// Backup original JSON data.
		if ( $migrated > 0 ) {
			$backup_data = Parish_Core::get_setting( 'parish_events', '[]' );
			update_option( 'parish_events_backup', $backup_data, false );
			add_option( 'parish_events_migrated_at', current_time( 'mysql' ), '', false );
		}

		return array(
			'migrated' => $migrated,
			'errors'   => $errors,
		);
	}

	/**
	 * Assign taxonomy terms based on legacy event type.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $type    Legacy event type.
	 * @return void
	 */
	private static function assign_legacy_type( int $post_id, string $type ): void {
		$mapping = array(
			'parish'    => array(
				'taxonomy' => 'parish_event_type',
				'term'     => 'Parish Event',
			),
			'sacrament' => array(
				'taxonomy' => 'parish_sacrament',
				'term'     => 'Baptisms',
			),
			'feast'     => array(
				'taxonomy' => 'parish_feast_day',
				'term'     => '',
			),
			'meeting'   => array(
				'taxonomy' => 'parish_event_type',
				'term'     => 'Community Gathering',
			),
		);

		if ( isset( $mapping[ $type ] ) ) {
			$tax  = $mapping[ $type ]['taxonomy'];
			$term = $mapping[ $type ]['term'];

			if ( ! empty( $term ) ) {
				wp_set_object_terms( $post_id, $term, $tax );
			}
		} else {
			// Default to generic parish event.
			wp_set_object_terms( $post_id, 'Parish Event', 'parish_event_type' );
		}
	}

	/**
	 * Check if migration is needed.
	 *
	 * @return bool True if migration is needed.
	 */
	public static function needs_migration(): bool {
		// Check if already migrated.
		if ( get_option( 'parish_events_migrated_at' ) ) {
			return false;
		}

		// Check if JSON data exists.
		$json_events = json_decode( Parish_Core::get_setting( 'parish_events', '[]' ), true ) ?: array();

		if ( empty( $json_events ) ) {
			return false;
		}

		// Check if no CPT posts exist yet.
		$existing_posts = get_posts(
			array(
				'post_type'      => 'parish_event',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'post_status'    => 'any',
			)
		);

		return empty( $existing_posts );
	}

	/**
	 * Mark migration as completed manually (for users who don't need it).
	 *
	 * @return void
	 */
	public static function skip_migration(): void {
		add_option( 'parish_events_migrated_at', current_time( 'mysql' ), '', false );
		add_option( 'parish_events_migration_skipped', true, '', false );
	}

	/**
	 * Reset migration state (for testing).
	 *
	 * @return void
	 */
	public static function reset_migration_state(): void {
		delete_option( 'parish_events_migrated_at' );
		delete_option( 'parish_events_migration_skipped' );
		delete_option( 'parish_events_backup' );
	}
}
