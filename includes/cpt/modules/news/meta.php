<?php
/**
 * Meta schema: News (parish_news)
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
	'post_type' => 'parish_news',
	'fields'    => array(
		// Publication Information
		'publish_date'      => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field', // YYYY-MM-DD
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Publish date', 'parish-core' ),
		),
		'author_name'       => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Author', 'parish-core' ),
		),

		// Content
		'news_summary'      => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Summary', 'parish-core' ),
		),

		// Content Classification
		'news_category'     => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field', // e.g., Announcement, Event, Update
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Category', 'parish-core' ),
		),

		// Related Church
		'related_church'    => array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'show_in_rest'      => true,
			'default'           => 0,
			'label'             => __( 'Related church', 'parish-core' ),
		),

		// Event Information (if applicable)
		'event_date'        => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field', // YYYY-MM-DD
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Event date', 'parish-core' ),
		),
		'event_time'        => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field', // HH:MM
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Event time', 'parish-core' ),
		),
		'event_location'    => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Event location', 'parish-core' ),
		),

		// External Link
		'external_link'     => array(
			'type'              => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'External link', 'parish-core' ),
		),
		'external_link_text' => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Link text', 'parish-core' ),
		),

		// Priority/Featured
		'is_featured'       => array(
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'show_in_rest'      => true,
			'default'           => false,
			'label'             => __( 'Featured', 'parish-core' ),
		),
		'is_urgent'         => array(
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'show_in_rest'      => true,
			'default'           => false,
			'label'             => __( 'Urgent', 'parish-core' ),
		),

		// Expiration
		'expiration_date'   => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field', // YYYY-MM-DD
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Expiration date', 'parish-core' ),
		),
	),
);
