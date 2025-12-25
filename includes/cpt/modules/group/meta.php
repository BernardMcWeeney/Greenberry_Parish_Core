<?php
/**
 * Meta schema: Group (parish_group)
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
	'post_type' => 'parish_group',
	'fields'    => array(
		// Group Type
		'group_type'        => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field', // e.g., Youth, Adults, Prayer, Service, etc.
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Group type', 'parish-core' ),
		),

		// Description
		'description'       => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Description', 'parish-core' ),
		),

		// Meeting Information
		'meeting_day'       => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field', // e.g., "Every Tuesday"
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Meeting day', 'parish-core' ),
		),
		'meeting_time'      => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field', // e.g., "7:00 PM"
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Meeting time', 'parish-core' ),
		),
		'meeting_location'  => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Meeting location', 'parish-core' ),
		),

		// Related Church
		'related_church'    => array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'show_in_rest'      => true,
			'default'           => 0,
			'label'             => __( 'Related church', 'parish-core' ),
		),

		// Contact Information
		'coordinator_name'  => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Coordinator', 'parish-core' ),
		),
		'contact_email'     => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_email',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Email', 'parish-core' ),
		),
		'contact_phone'     => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Phone', 'parish-core' ),
		),

		// Age Range
		'age_range'         => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field', // e.g., "18-35", "All Ages", "Youth"
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Age range', 'parish-core' ),
		),

		// Membership
		'membership_status' => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field', // Open, Closed, By Invitation
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Membership status', 'parish-core' ),
		),
		'max_members'       => array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'show_in_rest'      => true,
			'default'           => 0,
			'label'             => __( 'Max members', 'parish-core' ),
		),
		'current_members'   => array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'show_in_rest'      => true,
			'default'           => 0,
			'label'             => __( 'Current members', 'parish-core' ),
		),

		// Additional Information
		'requirements'      => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Requirements', 'parish-core' ),
		),
		'activities'        => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Activities', 'parish-core' ),
		),

		// Registration
		'registration_link' => array(
			'type'              => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Registration link', 'parish-core' ),
		),
	),
);
