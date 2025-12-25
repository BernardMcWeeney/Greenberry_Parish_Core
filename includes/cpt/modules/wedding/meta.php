<?php
/**
 * Meta schema: Wedding (parish_wedding)
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
	'post_type' => 'parish_wedding',
	'fields'    => array(
		// Couple Information
		'bride_name'        => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Bride name', 'parish-core' ),
		),
		'groom_name'        => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Groom name', 'parish-core' ),
		),

		// Ceremony Details
		'wedding_date'      => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field', // YYYY-MM-DD
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Wedding date', 'parish-core' ),
		),
		'wedding_time'      => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field', // HH:MM
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Wedding time', 'parish-core' ),
		),

		// Location (relationship to parish_church)
		'related_church'    => array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'show_in_rest'      => true,
			'default'           => 0,
			'label'             => __( 'Related church', 'parish-core' ),
		),

		// Church name (for display when not using relationship)
		'church_name'       => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Church name', 'parish-core' ),
		),

		// Celebrant
		'celebrant_name'    => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Celebrant', 'parish-core' ),
		),

		// Wedding Party
		'best_man'          => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Best man', 'parish-core' ),
		),
		'bridesmaid'        => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Bridesmaid', 'parish-core' ),
		),

		// Reception Information
		'reception_venue'   => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Reception venue', 'parish-core' ),
		),
		'reception_address' => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Reception address', 'parish-core' ),
		),
		'reception_time'    => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field', // HH:MM
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Reception time', 'parish-core' ),
		),

		// Additional Information
		'notes'             => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Additional notes', 'parish-core' ),
		),
	),
);
