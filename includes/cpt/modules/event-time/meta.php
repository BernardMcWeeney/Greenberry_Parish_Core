<?php
/**
 * Parish Event Time Meta Fields.
 *
 * Defines all metadata for Mass times, confessions, and other services.
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper class for meta field sanitization.
 *
 * IMPORTANT: This class must be defined BEFORE the return statement
 * so it exists when referenced in sanitize_callback.
 */
class Parish_Event_Time_Meta {

	/**
	 * Sanitize JSON string.
	 *
	 * @param string $value JSON string.
	 * @return string Sanitized JSON string.
	 */
	public static function sanitize_json( $value ): string {
		if ( empty( $value ) ) {
			return '';
		}

		// Decode to validate
		$decoded = json_decode( $value, true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return '';
		}

		// Re-encode to ensure clean JSON
		return wp_json_encode( $decoded );
	}

	/**
	 * Sanitize embed code - whitelist safe providers.
	 *
	 * @param string $value Embed code.
	 * @return string Sanitized embed code.
	 */
	public static function sanitize_embed_code( $value ): string {
		if ( empty( $value ) ) {
			return '';
		}

		// Allowed iframe domains
		$allowed_domains = array(
			'youtube.com',
			'www.youtube.com',
			'youtube-nocookie.com',
			'www.youtube-nocookie.com',
			'youtu.be',
			'facebook.com',
			'www.facebook.com',
			'fb.watch',
			'vimeo.com',
			'player.vimeo.com',
			'twitch.tv',
			'player.twitch.tv',
			'churchstreaming.tv',
			'livestream.com',
			'mcnmedia.tv',
			'boxcast.tv',
		);

		// Extract iframe src
		if ( preg_match( '/<iframe[^>]+src=["\']([^"\']+)["\']/', $value, $matches ) ) {
			$src = $matches[1];
			$parsed = wp_parse_url( $src );
			$host = $parsed['host'] ?? '';

			// Check if domain is allowed
			$domain_allowed = false;
			foreach ( $allowed_domains as $domain ) {
				if ( $host === $domain || str_ends_with( $host, '.' . $domain ) ) {
					$domain_allowed = true;
					break;
				}
			}

			if ( ! $domain_allowed ) {
				return '';
			}
		}

		// Use WordPress KSES with iframe allowed
		$allowed_html = array(
			'iframe' => array(
				'src'             => true,
				'width'           => true,
				'height'          => true,
				'frameborder'     => true,
				'allow'           => true,
				'allowfullscreen' => true,
				'title'           => true,
				'loading'         => true,
				'style'           => true,
				'class'           => true,
				'id'              => true,
			),
		);

		return wp_kses( $value, $allowed_html );
	}
}

// Return the meta field definitions
return array(
	'post_type' => 'parish_event_time',
	'fields'    => array(
		// ========================================
		// Core Fields
		// ========================================
		'church_id' => array(
			'type'              => 'integer',
			'description'       => __( 'Associated church ID', 'parish-core' ),
			'single'            => true,
			'default'           => 0,
			'sanitize_callback' => 'absint',
			'show_in_rest'      => true,
		),
		'event_type' => array(
			'type'              => 'string',
			'description'       => __( 'Type of event (mass, confession, adoration, other)', 'parish-core' ),
			'single'            => true,
			'default'           => 'mass',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
		),
		'start_datetime' => array(
			'type'              => 'string',
			'description'       => __( 'Start date and time (ISO 8601)', 'parish-core' ),
			'single'            => true,
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
		),
		'duration_minutes' => array(
			'type'              => 'integer',
			'description'       => __( 'Duration in minutes', 'parish-core' ),
			'single'            => true,
			'default'           => 60,
			'sanitize_callback' => 'absint',
			'show_in_rest'      => true,
		),
		'timezone' => array(
			'type'              => 'string',
			'description'       => __( 'Timezone identifier', 'parish-core' ),
			'single'            => true,
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
		),

		// ========================================
		// Recurrence Fields
		// ========================================
		'is_recurring' => array(
			'type'              => 'boolean',
			'description'       => __( 'Whether this is a recurring event', 'parish-core' ),
			'single'            => true,
			'default'           => false,
			'sanitize_callback' => 'rest_sanitize_boolean',
			'show_in_rest'      => true,
		),
		'recurrence_rule' => array(
			'type'              => 'string',
			'description'       => __( 'Recurrence rule as JSON', 'parish-core' ),
			'single'            => true,
			'default'           => '',
			'sanitize_callback' => array( 'Parish_Event_Time_Meta', 'sanitize_json' ),
			'show_in_rest'      => array(
				'schema' => array(
					'type' => 'string',
				),
			),
		),
		'recurrence_end_type' => array(
			'type'              => 'string',
			'description'       => __( 'End rule: never, until, count', 'parish-core' ),
			'single'            => true,
			'default'           => 'never',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
		),
		'recurrence_end_date' => array(
			'type'              => 'string',
			'description'       => __( 'End date for recurrence (Y-m-d)', 'parish-core' ),
			'single'            => true,
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
		),
		'recurrence_count' => array(
			'type'              => 'integer',
			'description'       => __( 'Number of occurrences', 'parish-core' ),
			'single'            => true,
			'default'           => 0,
			'sanitize_callback' => 'absint',
			'show_in_rest'      => true,
		),
		'exception_dates' => array(
			'type'              => 'string',
			'description'       => __( 'Exception dates as JSON array', 'parish-core' ),
			'single'            => true,
			'default'           => '[]',
			'sanitize_callback' => array( 'Parish_Event_Time_Meta', 'sanitize_json' ),
			'show_in_rest'      => array(
				'schema' => array(
					'type' => 'string',
				),
			),
		),

		// ========================================
		// Livestream Fields
		// ========================================
		'livestream_enabled' => array(
			'type'              => 'boolean',
			'description'       => __( 'Whether livestream is enabled', 'parish-core' ),
			'single'            => true,
			'default'           => false,
			'sanitize_callback' => 'rest_sanitize_boolean',
			'show_in_rest'      => true,
		),
		'livestream_mode' => array(
			'type'              => 'string',
			'description'       => __( 'Livestream mode: link or embed', 'parish-core' ),
			'single'            => true,
			'default'           => 'link',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
		),
		'livestream_url' => array(
			'type'              => 'string',
			'description'       => __( 'Livestream URL', 'parish-core' ),
			'single'            => true,
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
			'show_in_rest'      => true,
		),
		'livestream_embed' => array(
			'type'              => 'string',
			'description'       => __( 'Livestream embed code (sanitized)', 'parish-core' ),
			'single'            => true,
			'default'           => '',
			'sanitize_callback' => array( 'Parish_Event_Time_Meta', 'sanitize_embed_code' ),
			'show_in_rest'      => true,
		),
		'livestream_provider' => array(
			'type'              => 'string',
			'description'       => __( 'Livestream provider (youtube, facebook, vimeo, custom)', 'parish-core' ),
			'single'            => true,
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
		),

		// ========================================
		// Content Fields
		// ========================================
		'intentions' => array(
			'type'              => 'string',
			'description'       => __( 'Mass intentions as JSON array or rich text', 'parish-core' ),
			'single'            => true,
			'default'           => '',
			'sanitize_callback' => 'wp_kses_post',
			'show_in_rest'      => true,
		),
		'notes' => array(
			'type'              => 'string',
			'description'       => __( 'Additional notes (rich text)', 'parish-core' ),
			'single'            => true,
			'default'           => '',
			'sanitize_callback' => 'wp_kses_post',
			'show_in_rest'      => true,
		),

		// ========================================
		// Readings Integration
		// ========================================
		'readings_mode' => array(
			'type'              => 'string',
			'description'       => __( 'Readings mode: auto, override, none', 'parish-core' ),
			'single'            => true,
			'default'           => 'auto',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
		),
		'readings_override' => array(
			'type'              => 'string',
			'description'       => __( 'Custom readings override as JSON', 'parish-core' ),
			'single'            => true,
			'default'           => '',
			'sanitize_callback' => array( 'Parish_Event_Time_Meta', 'sanitize_json' ),
			'show_in_rest'      => array(
				'schema' => array(
					'type' => 'string',
				),
			),
		),

		// ========================================
		// Liturgical Settings
		// ========================================
		'liturgical_rite' => array(
			'type'              => 'string',
			'description'       => __( 'Liturgical rite (roman, byzantine, etc)', 'parish-core' ),
			'single'            => true,
			'default'           => 'roman',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
		),
		'liturgical_form' => array(
			'type'              => 'string',
			'description'       => __( 'Liturgical form (ordinary, extraordinary)', 'parish-core' ),
			'single'            => true,
			'default'           => 'ordinary',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
		),
		'language' => array(
			'type'              => 'string',
			'description'       => __( 'Primary language of the service', 'parish-core' ),
			'single'            => true,
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
		),

		// ========================================
		// Linkage Fields
		// ========================================
		'linked_mass_id' => array(
			'type'              => 'integer',
			'description'       => __( 'Linked Mass ID (for confessions/adoration attached to a Mass)', 'parish-core' ),
			'single'            => true,
			'default'           => 0,
			'sanitize_callback' => 'absint',
			'show_in_rest'      => true,
		),

		// ========================================
		// Status/Visibility
		// ========================================
		'is_active' => array(
			'type'              => 'boolean',
			'description'       => __( 'Whether this event time is active', 'parish-core' ),
			'single'            => true,
			'default'           => true,
			'sanitize_callback' => 'rest_sanitize_boolean',
			'show_in_rest'      => true,
		),
		'is_special' => array(
			'type'              => 'boolean',
			'description'       => __( 'Whether this is a special/one-time event', 'parish-core' ),
			'single'            => true,
			'default'           => false,
			'sanitize_callback' => 'rest_sanitize_boolean',
			'show_in_rest'      => true,
		),
		'display_priority' => array(
			'type'              => 'integer',
			'description'       => __( 'Display priority (higher = more prominent)', 'parish-core' ),
			'single'            => true,
			'default'           => 0,
			'sanitize_callback' => function( $value ) { return intval( $value ); },
			'show_in_rest'      => true,
		),
	),
);
