<?php
/**
 * Meta schema: Gallery (parish_gallery)
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
	'post_type' => 'parish_gallery',
	'fields'    => array(
		// Description
		'description'       => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Description', 'parish-core' ),
		),

		// Event Information
		'event_date'        => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field', // YYYY-MM-DD
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Event date', 'parish-core' ),
		),
		'event_type'        => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field', // e.g., Mass, Festival, Sacrament, Community
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Event type', 'parish-core' ),
		),

		// Location
		'event_location'    => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Location', 'parish-core' ),
		),
		'related_church'    => array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'show_in_rest'      => true,
			'default'           => 0,
			'label'             => __( 'Related church', 'parish-core' ),
		),

		// Photographer
		'photographer_name' => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Photographer', 'parish-core' ),
		),
		'photographer_credit' => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Photo credit', 'parish-core' ),
		),

		// Gallery Settings
		'gallery_layout'    => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field', // grid, masonry, slideshow
			'show_in_rest'      => true,
			'default'           => 'grid',
			'label'             => __( 'Layout', 'parish-core' ),
		),
		'image_count'       => array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'show_in_rest'      => true,
			'default'           => 0,
			'label'             => __( 'Image count', 'parish-core' ),
		),

		// Privacy
		'is_public'         => array(
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'show_in_rest'      => true,
			'default'           => true,
			'label'             => __( 'Public', 'parish-core' ),
		),
		'password_protected' => array(
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'show_in_rest'      => true,
			'default'           => false,
			'label'             => __( 'Password protected', 'parish-core' ),
		),

		// Featured
		'is_featured'       => array(
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'show_in_rest'      => true,
			'default'           => false,
			'label'             => __( 'Featured', 'parish-core' ),
		),
	),
);
