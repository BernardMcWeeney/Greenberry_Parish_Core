<?php
/**
 * Meta schema: Mass Time (parish_mass_time)
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'post_type' => 'parish_mass_time',
	'fields'    => array(
		// Church relationship (0 = All Churches)
		// Keys prefixed with 'mass_time_' so meta registry creates 'parish_mass_time_*' keys
		'mass_time_church_id'        => array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'show_in_rest'      => true,
			'default'           => 0,
			'label'             => __( 'Church', 'parish-core' ),
		),

		// Liturgical type (mass, confession, adoration, rosary, etc.)
		'mass_time_liturgical_type'  => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => 'mass',
			'label'             => __( 'Type', 'parish-core' ),
		),

		// Start date and time (ISO 8601 format in site timezone)
		'mass_time_start_datetime'   => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Start Date & Time', 'parish-core' ),
		),

		// Duration in minutes
		'mass_time_duration_minutes' => array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'show_in_rest'      => true,
			'default'           => 60,
			'label'             => __( 'Duration (minutes)', 'parish-core' ),
		),

		// Active status
		'mass_time_is_active'        => array(
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'show_in_rest'      => true,
			'default'           => true,
			'label'             => __( 'Active', 'parish-core' ),
		),

		// Special event (one-off)
		'mass_time_is_special_event' => array(
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'show_in_rest'      => true,
			'default'           => false,
			'label'             => __( 'Special Event', 'parish-core' ),
		),

		// Recurring flag
		'mass_time_is_recurring'     => array(
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'show_in_rest'      => true,
			'default'           => false,
			'label'             => __( 'Recurring', 'parish-core' ),
		),

		// Recurrence rules (JSON object)
		'mass_time_recurrence'       => array(
			'type'              => 'object',
			'sanitize_callback' => 'parish_sanitize_recurrence',
			'show_in_rest'      => array(
				'schema' => array(
					'type'       => 'object',
					'properties' => array(
						'type'         => array(
							'type' => 'string',
							'enum' => array( 'daily', 'weekly', 'biweekly', 'monthly_day', 'monthly_ordinal', 'yearly' ),
						),
						'days'         => array(
							'type'  => 'array',
							'items' => array( 'type' => 'string' ),
						),
						'day_of_month' => array(
							'type' => 'integer',
						),
						'ordinal'      => array(
							'type' => 'string',
							'enum' => array( 'first', 'second', 'third', 'fourth', 'last' ),
						),
						'ordinal_day'  => array(
							'type' => 'string',
						),
						'month'        => array(
							'type' => 'integer',
						),
						'end_date'     => array(
							'type' => 'string',
						),
					),
				),
			),
			'default'           => array( 'type' => 'weekly', 'days' => array() ),
			'label'             => __( 'Recurrence Rules', 'parish-core' ),
		),

		// Exception dates (dates to skip)
		'mass_time_exception_dates'  => array(
			'type'              => 'array',
			'sanitize_callback' => 'parish_sanitize_exception_dates',
			'show_in_rest'      => array(
				'schema' => array(
					'type'  => 'array',
					'items' => array( 'type' => 'string' ),
				),
			),
			'default'           => array(),
			'label'             => __( 'Exception Dates', 'parish-core' ),
		),

		// Livestream enabled
		'mass_time_is_livestreamed'  => array(
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'show_in_rest'      => true,
			'default'           => false,
			'label'             => __( 'Livestreamed', 'parish-core' ),
		),

		// Livestream URL
		'mass_time_livestream_url'   => array(
			'type'              => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'show_in_rest'      => true,
			'default'           => 'https://bohermeenparish.ie/online-live-mass/',
			'label'             => __( 'Livestream URL', 'parish-core' ),
		),

		// Livestream embed code (sanitized HTML)
		'mass_time_livestream_embed' => array(
			'type'              => 'string',
			'sanitize_callback' => 'parish_sanitize_embed_html',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Livestream Embed Code', 'parish-core' ),
		),

		// Notes (rich text)
		'mass_time_notes'            => array(
			'type'              => 'string',
			'sanitize_callback' => 'wp_kses_post',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Notes', 'parish-core' ),
		),
	),
);
