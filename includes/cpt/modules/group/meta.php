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
		// Opening Description (tagline)
		'description'        => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Description', 'parish-core' ),
		),

		// About This Group (detailed info)
		'about'              => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'About this group', 'parish-core' ),
		),

		// Meeting Information
		'meeting_schedule'   => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Meeting schedule', 'parish-core' ),
		),
		'meeting_location'   => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Meeting location', 'parish-core' ),
		),

		// Contact Information
		'coordinator_name'   => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => 'Parish Office',
			'label'             => __( 'Coordinator', 'parish-core' ),
		),
		'email'              => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_email',
			'show_in_rest'      => true,
			'default'           => 'bohermeenparish1@gmail.com',
			'label'             => __( 'Email', 'parish-core' ),
		),
		'phone'              => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '+353 (0)46 907 3805',
			'label'             => __( 'Phone', 'parish-core' ),
		),
		'website'            => array(
			'type'              => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Website', 'parish-core' ),
		),

		// Age Range / Who
		'age_range'          => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Age range', 'parish-core' ),
		),

		// Join This Group Callout
		'join_info'          => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
			'show_in_rest'      => true,
			'default'           => 'Contact the Parish Office for more information.',
			'label'             => __( 'Join information', 'parish-core' ),
		),
		'contact_url'        => array(
			'type'              => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'show_in_rest'      => true,
			'default'           => 'https://bohermeenparish.ie/parish-office/#contact-us',
			'label'             => __( 'Contact page URL', 'parish-core' ),
		),
	),
);
