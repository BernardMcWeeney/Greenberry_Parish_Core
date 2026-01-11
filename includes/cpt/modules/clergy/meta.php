<?php
/**
 * Meta schema: Clergy & Staff (parish_clergy)
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
	'post_type' => 'parish_clergy',
	'fields'    => array(
		// Role/Position
		'role'               => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Role/Position', 'parish-core' ),
		),

		// Status (current/past)
		'status'             => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => 'current',
			'label'             => __( 'Status', 'parish-core' ),
		),

		// Years Served
		'year_from'          => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Year from', 'parish-core' ),
		),
		'year_to'            => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Year to', 'parish-core' ),
		),

		// Biography
		'description'        => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Brief description', 'parish-core' ),
		),
		'biography'          => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Biography', 'parish-core' ),
		),

		// Ordination/Appointment
		'ordination_date'    => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Ordination/Appointment date', 'parish-core' ),
		),

		// Contact Information (for current members)
		'email'              => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_email',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Email', 'parish-core' ),
		),
		'phone'              => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Phone', 'parish-core' ),
		),

		// Church relationship
		'related_church'     => array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'show_in_rest'      => true,
			'default'           => 0,
			'label'             => __( 'Related church', 'parish-core' ),
		),

		// Order/display priority
		'display_order'      => array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'show_in_rest'      => true,
			'default'           => 0,
			'label'             => __( 'Display order', 'parish-core' ),
		),
	),
);
