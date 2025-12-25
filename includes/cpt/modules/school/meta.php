<?php
/**
 * Meta schema: School (parish_school)
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
	'post_type' => 'parish_school',
	'fields'    => array(
		// Basic Information
		'school_type'       => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field', // Primary, Secondary, etc.
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'School type', 'parish-core' ),
		),

		// Contact Information
		'address'           => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Address', 'parish-core' ),
		),
		'phone'             => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Phone', 'parish-core' ),
		),
		'email'             => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_email',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Email', 'parish-core' ),
		),
		'website'           => array(
			'type'              => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Website', 'parish-core' ),
		),

		// Staff
		'principal_name'    => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Principal', 'parish-core' ),
		),
		'vice_principal'    => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Vice Principal', 'parish-core' ),
		),
		'chaplain'          => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Chaplain', 'parish-core' ),
		),

		// Enrollment
		'enrollment_info'   => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Enrollment information', 'parish-core' ),
		),
		'enrollment_link'   => array(
			'type'              => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Enrollment link', 'parish-core' ),
		),

		// Academic Information
		'grade_levels'      => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field', // e.g., "K-8" or "9-12"
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Grade levels', 'parish-core' ),
		),
		'curriculum_info'   => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Curriculum', 'parish-core' ),
		),

		// Facilities
		'facilities'        => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Facilities', 'parish-core' ),
		),

		// Hours
		'school_hours'      => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'School hours', 'parish-core' ),
		),

		// Related Church
		'related_church'    => array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'show_in_rest'      => true,
			'default'           => 0,
			'label'             => __( 'Related church', 'parish-core' ),
		),

		// Social Media
		'facebook_url'      => array(
			'type'              => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Facebook', 'parish-core' ),
		),
		'twitter_url'       => array(
			'type'              => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Twitter', 'parish-core' ),
		),
	),
);
