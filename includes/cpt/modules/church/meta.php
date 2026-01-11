<?php
/**
 * Meta schema: Church (parish_church)
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
	'post_type' => 'parish_church',
	'fields'    => array(
		// Opening Description (tagline)
		'description'          => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Description', 'parish-core' ),
		),

		// About This Church (history/background)
		'about'                => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'About this church', 'parish-core' ),
		),

		// Contact & Location
		'address'              => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Address', 'parish-core' ),
		),
		'phone'                => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Phone', 'parish-core' ),
		),
		'opening_hours'        => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Opening hours', 'parish-core' ),
		),
		'established_year'     => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Established year', 'parish-core' ),
		),

		// Related Links - Link 1 (Live Mass)
		'link1_title'          => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Link 1 title', 'parish-core' ),
		),
		'link1_url'            => array(
			'type'              => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Link 1 URL', 'parish-core' ),
		),
		'link1_description'    => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Link 1 description', 'parish-core' ),
		),

		// Related Links - Link 2 (Cemetery)
		'link2_title'          => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Link 2 title', 'parish-core' ),
		),
		'link2_url'            => array(
			'type'              => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Link 2 URL', 'parish-core' ),
		),
		'link2_description'    => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Link 2 description', 'parish-core' ),
		),

		// Related Links - Link 3 (Mass Readings)
		'link3_title'          => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Link 3 title', 'parish-core' ),
		),
		'link3_url'            => array(
			'type'              => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Link 3 URL', 'parish-core' ),
		),
		'link3_description'    => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Link 3 description', 'parish-core' ),
		),

		// Mass Intentions Callout
		'mass_intentions_text' => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Mass intentions text', 'parish-core' ),
		),
		'mass_intentions_url'  => array(
			'type'              => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Mass intentions URL', 'parish-core' ),
		),

		// Parish Office/Centre Info
		'office_address'       => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Parish office address', 'parish-core' ),
		),
		'office_hours'         => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Parish office hours', 'parish-core' ),
		),
	),
);
