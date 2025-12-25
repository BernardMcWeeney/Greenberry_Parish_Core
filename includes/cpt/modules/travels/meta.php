<?php
/**
 * Meta schema: Parish Travels
 *
 * Defines all custom meta fields for the parish_travels post type.
 * Each field is registered with REST API support for Block Bindings.
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'post_type' => 'parish_travels',
	'fields'    => array(
		// === Core Trip Information ===
		'travel_title' => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Trip title', 'parish-core' ),
		),
		'destination' => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Destination', 'parish-core' ),
		),
		'travel_summary' => array(
			'type'              => 'string',
			'sanitize_callback' => 'wp_kses_post',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Trip summary', 'parish-core' ),
		),
		'travel_description' => array(
			'type'              => 'string',
			'sanitize_callback' => 'wp_kses_post',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Full description', 'parish-core' ),
		),

		// === Dates ===
		'departure_date' => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Departure date', 'parish-core' ),
		),
		'return_date' => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Return date', 'parish-core' ),
		),
		'duration' => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Duration (e.g., 7 days)', 'parish-core' ),
		),

		// === Pricing ===
		'price' => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Price', 'parish-core' ),
		),
		'price_includes' => array(
			'type'              => 'string',
			'sanitize_callback' => 'wp_kses_post',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Price includes', 'parish-core' ),
		),
		'price_excludes' => array(
			'type'              => 'string',
			'sanitize_callback' => 'wp_kses_post',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Price excludes', 'parish-core' ),
		),
		'deposit_amount' => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Deposit amount', 'parish-core' ),
		),
		'payment_deadline' => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Payment deadline', 'parish-core' ),
		),

		// === Logistics ===
		'departure_location' => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Departure location', 'parish-core' ),
		),
		'departure_time' => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Departure time', 'parish-core' ),
		),
		'transport_type' => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Transport type', 'parish-core' ),
		),
		'accommodation' => array(
			'type'              => 'string',
			'sanitize_callback' => 'wp_kses_post',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Accommodation details', 'parish-core' ),
		),

		// === Spiritual Content (for pilgrimages) ===
		'spiritual_director' => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Spiritual director', 'parish-core' ),
		),
		'spiritual_focus' => array(
			'type'              => 'string',
			'sanitize_callback' => 'wp_kses_post',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Spiritual focus', 'parish-core' ),
		),
		'itinerary_highlights' => array(
			'type'              => 'string',
			'sanitize_callback' => 'wp_kses_post',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Itinerary highlights', 'parish-core' ),
		),

		// === Booking & Contact ===
		'spaces_available' => array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'show_in_rest'      => true,
			'default'           => 0,
			'label'             => __( 'Spaces available', 'parish-core' ),
		),
		'booking_url' => array(
			'type'              => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Booking URL', 'parish-core' ),
		),
		'contact_name' => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Contact person', 'parish-core' ),
		),
		'contact_phone' => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Contact phone', 'parish-core' ),
		),
		'contact_email' => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_email',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Contact email', 'parish-core' ),
		),

		// === Status ===
		'is_featured' => array(
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'show_in_rest'      => true,
			'default'           => false,
			'label'             => __( 'Featured trip', 'parish-core' ),
		),
		'registration_status' => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => 'open',
			'label'             => __( 'Registration status', 'parish-core' ),
		),

		// === Related Church ===
		'related_church' => array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'show_in_rest'      => true,
			'default'           => 0,
			'label'             => __( 'Related church', 'parish-core' ),
		),

		// === Additional Information ===
		'requirements' => array(
			'type'              => 'string',
			'sanitize_callback' => 'wp_kses_post',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Requirements (passport, visa, etc.)', 'parish-core' ),
		),
		'additional_notes' => array(
			'type'              => 'string',
			'sanitize_callback' => 'wp_kses_post',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Additional notes', 'parish-core' ),
		),
	),
);
