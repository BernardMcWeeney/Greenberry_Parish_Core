<?php
/**
 * Events Meta Fields Configuration
 *
 * @package ParishCore
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'post_type' => 'parish_event',
	'fields'    => array(
		'event_date'            => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'single'            => true,
			'default'           => '',
			'label'             => __( 'Event Date', 'parish-core' ),
			'description'       => __( 'Date in YYYY-MM-DD format', 'parish-core' ),
		),
		'event_time'            => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'single'            => true,
			'default'           => '',
			'label'             => __( 'Event Time', 'parish-core' ),
			'description'       => __( 'Time in HH:MM format', 'parish-core' ),
		),
		'event_end_time'        => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'single'            => true,
			'default'           => '',
			'label'             => __( 'End Time', 'parish-core' ),
			'description'       => __( 'Optional end time in HH:MM format', 'parish-core' ),
		),
		'event_location'        => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'single'            => true,
			'default'           => '',
			'label'             => __( 'Location', 'parish-core' ),
			'description'       => __( 'Free-form location text', 'parish-core' ),
		),
		'event_church_id'       => array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'show_in_rest'      => true,
			'single'            => true,
			'default'           => 0,
			'label'             => __( 'Church', 'parish-core' ),
			'description'       => __( 'Select a church or 0 for all churches', 'parish-core' ),
		),
		'event_is_cemetery'     => array(
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'show_in_rest'      => true,
			'single'            => true,
			'default'           => false,
			'label'             => __( 'Cemetery Event', 'parish-core' ),
			'description'       => __( 'Is this a cemetery event?', 'parish-core' ),
		),
		'event_organizer'       => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'single'            => true,
			'default'           => '',
			'label'             => __( 'Organizer', 'parish-core' ),
			'description'       => __( 'Name of event organizer', 'parish-core' ),
		),
		'event_contact_email'   => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_email',
			'show_in_rest'      => true,
			'single'            => true,
			'default'           => '',
			'label'             => __( 'Contact Email', 'parish-core' ),
			'description'       => __( 'Contact email for inquiries', 'parish-core' ),
		),
		'event_contact_phone'   => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'single'            => true,
			'default'           => '',
			'label'             => __( 'Contact Phone', 'parish-core' ),
			'description'       => __( 'Contact phone number', 'parish-core' ),
		),
		'event_registration_url' => array(
			'type'              => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'show_in_rest'      => true,
			'single'            => true,
			'default'           => '',
			'label'             => __( 'Registration URL', 'parish-core' ),
			'description'       => __( 'URL for event registration or tickets', 'parish-core' ),
		),
		'event_featured'        => array(
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'show_in_rest'      => true,
			'single'            => true,
			'default'           => false,
			'label'             => __( 'Featured Event', 'parish-core' ),
			'description'       => __( 'Mark as featured to highlight on homepage', 'parish-core' ),
		),
		'event_color'           => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_hex_color',
			'show_in_rest'      => true,
			'single'            => true,
			'default'           => '#609fae',
			'label'             => __( 'Event Color', 'parish-core' ),
			'description'       => __( 'Hex color for calendar display', 'parish-core' ),
		),
	),
);
