<?php
/**
 * Meta schema: Reflection (parish_reflection)
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
	'post_type' => 'parish_reflection',
	'fields'    => array(
		// Liturgical Context
		'liturgical_season' => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field', // Advent, Christmas, Lent, Easter, Ordinary Time
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Liturgical season', 'parish-core' ),
		),
		'liturgical_date'   => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field', // YYYY-MM-DD
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Liturgical date', 'parish-core' ),
		),
		'sunday_cycle'      => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field', // A, B, C
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Sunday cycle', 'parish-core' ),
		),
		'weekday_cycle'     => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field', // I, II
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Weekday cycle', 'parish-core' ),
		),

		// Scripture References
		'gospel_reading'    => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field', // e.g., "John 3:16-21"
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Gospel reading', 'parish-core' ),
		),
		'first_reading'     => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'First reading', 'parish-core' ),
		),
		'second_reading'    => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Second reading', 'parish-core' ),
		),
		'psalm'             => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Psalm', 'parish-core' ),
		),

		// Reflection Content
		'reflection_text'   => array(
			'type'              => 'string',
			'sanitize_callback' => 'wp_kses_post', // Allow some HTML for formatting
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Reflection', 'parish-core' ),
		),
		'key_verse'         => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Key verse', 'parish-core' ),
		),

		// Author
		'author_name'       => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Author', 'parish-core' ),
		),
		'author_title'      => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field', // e.g., "Pastor", "Deacon", "Priest"
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Author title', 'parish-core' ),
		),

		// Reflection Type
		'reflection_type'   => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field', // Homily, Meditation, Teaching, etc.
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Reflection type', 'parish-core' ),
		),

		// Audio/Video
		'audio_url'         => array(
			'type'              => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Audio URL', 'parish-core' ),
		),
		'video_url'         => array(
			'type'              => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Video URL', 'parish-core' ),
		),

		// Related
		'related_church'    => array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'show_in_rest'      => true,
			'default'           => 0,
			'label'             => __( 'Related church', 'parish-core' ),
		),
	),
);
