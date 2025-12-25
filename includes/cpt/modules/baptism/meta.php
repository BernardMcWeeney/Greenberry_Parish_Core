<?php
/**
 * Meta schema: Baptism (parish_baptism)
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'post_type' => 'parish_baptism',
	'fields'    => array(
		'child_name' => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Child name', 'parish-core' ),
		),
		'baptism_date' => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field', // YYYY-MM-DD
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Baptism date', 'parish-core' ),
		),
		// Church relationship - links to parish_church CPT.
		'related_church' => array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'show_in_rest'      => true,
			'default'           => 0,
			'label'             => __( 'Parish church', 'parish-core' ),
		),
		// Church name - manual entry for non-parish churches.
		'church_name' => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Other church name', 'parish-core' ),
		),
		'celebrant' => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Celebrant', 'parish-core' ),
		),
		'parents_names' => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Parents’ names', 'parish-core' ),
		),
		'godparents' => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Godparents', 'parish-core' ),
		),
		'notes' => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Additional notes', 'parish-core' ),
		),
	),
);
