<?php
/**
 * Meta schema: Prayer (parish_prayer)
 *
 * Uses WordPress 6.5+ Block Bindings API
 * All fields exposed to REST with show_in_rest
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'post_type' => 'parish_prayer',
	'fields'    => array(
		// Prayer Content
		'prayer_text'       => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Prayer text', 'parish-core' ),
		),

		// Prayer Classification
		'prayer_type'       => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field', // Traditional, Novena, Litany, Devotional, etc.
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Prayer type', 'parish-core' ),
		),
		'prayer_category'   => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field', // Adoration, Petition, Thanksgiving, Intercession
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Category', 'parish-core' ),
		),

		// Liturgical Context
		'liturgical_season' => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field', // Advent, Christmas, Lent, Easter, Ordinary Time, Any
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Liturgical season', 'parish-core' ),
		),
		'feast_day'         => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field', // e.g., "Feast of St. Patrick"
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Feast day', 'parish-core' ),
		),
		'liturgical_date'   => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field', // YYYY-MM-DD or recurring pattern
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Liturgical date', 'parish-core' ),
		),

		// Attribution
		'author_name'       => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field', // e.g., "St. Francis", "Traditional"
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Author', 'parish-core' ),
		),
		'source'            => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field', // Book, tradition, saint, etc.
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Source', 'parish-core' ),
		),
		'copyright_info'    => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Copyright', 'parish-core' ),
		),

		// Usage Information
		'when_to_pray'      => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_textarea_field', // Morning, Evening, Before Meals, etc.
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'When to pray', 'parish-core' ),
		),
		'duration_minutes'  => array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'show_in_rest'      => true,
			'default'           => 0,
			'label'             => __( 'Duration (minutes)', 'parish-core' ),
		),

		// Scripture References
		'scripture_refs'    => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field', // e.g., "Psalm 23, John 3:16"
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Scripture references', 'parish-core' ),
		),

		// Language
		'language'          => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field', // English, Latin, Spanish, etc.
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Language', 'parish-core' ),
		),
		'has_translation'   => array(
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'show_in_rest'      => true,
			'default'           => false,
			'label'             => __( 'Has translation', 'parish-core' ),
		),

		// Audio
		'audio_url'         => array(
			'type'              => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Audio URL', 'parish-core' ),
		),

		// Popular/Featured
		'is_featured'       => array(
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'show_in_rest'      => true,
			'default'           => false,
			'label'             => __( 'Featured', 'parish-core' ),
		),
		'prayer_count'      => array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint', // Track how many times it's been prayed
			'show_in_rest'      => true,
			'default'           => 0,
			'label'             => __( 'Prayer count', 'parish-core' ),
		),
	),
);
