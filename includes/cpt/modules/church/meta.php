<?php
/**
 * Meta schema: Church (parish_church)
 *
 * Uses WordPress 6.6+ Block Bindings API
 * All fields exposed to REST with show_in_rest
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'post_type' => 'parish_church',
	'fields'    => array(
		// Core Information
		'parish_priest'      => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
		),
		'description'        => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
			'show_in_rest'      => true,
		),

		// Contact Information
		'address'            => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
			'show_in_rest'      => true,
		),
		'phone'              => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
		),
		'email'              => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_email',
			'show_in_rest'      => true,
		),
		'website'            => array(
			'type'              => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'show_in_rest'      => true,
		),

		// Mass & Confession Times
		'mass_times'         => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
			'show_in_rest'      => true,
		),
		'confession_times'   => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
			'show_in_rest'      => true,
		),

		// Location
		'map_embed'          => array(
			'type'              => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'show_in_rest'      => true,
		),

		// Additional Information
		'accessibility_info' => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
			'show_in_rest'      => true,
		),
		'parking_info'       => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
			'show_in_rest'      => true,
		),

		// Social Media
		'facebook_url'       => array(
			'type'              => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'show_in_rest'      => true,
		),
		'twitter_url'        => array(
			'type'              => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'show_in_rest'      => true,
		),
		'instagram_url'      => array(
			'type'              => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'show_in_rest'      => true,
		),

		// Schedule Display Settings
		'schedule_display'   => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => 'dynamic',
		),
		'feast_day_display'  => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => 'inline',
		),
		'include_memorials'  => array(
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'show_in_rest'      => true,
			'default'           => false,
		),
	),
);
