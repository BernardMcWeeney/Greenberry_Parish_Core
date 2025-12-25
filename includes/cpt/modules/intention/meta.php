<?php
/**
 * Meta schema: Mass Intention (parish_intention)
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'post_type' => 'parish_intention',
	'fields'    => array(
		// Date the intention is for (YYYY-MM-DD).
		'intention_date'  => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Intention Date', 'parish-core' ),
		),

		// Schedule template ID this intention is for.
		'schedule_id'     => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Schedule', 'parish-core' ),
		),

		// Church ID (redundant but useful for queries).
		'church_id'       => array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'show_in_rest'      => true,
			'default'           => 0,
			'label'             => __( 'Church', 'parish-core' ),
		),

		// Type of intention: living, deceased, special, thanksgiving.
		'intention_type'  => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => 'deceased',
			'label'             => __( 'Intention Type', 'parish-core' ),
		),

		// Name of person requesting the intention.
		'requestor_name'  => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Requested By', 'parish-core' ),
		),

		// Whether the intention should be displayed publicly.
		'is_public'       => array(
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'show_in_rest'      => true,
			'default'           => true,
			'label'             => __( 'Display Publicly', 'parish-core' ),
		),

		// Whether the stipend has been received.
		'stipend_received' => array(
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'show_in_rest'      => true,
			'default'           => false,
			'label'             => __( 'Stipend Received', 'parish-core' ),
		),

		// Stipend amount (optional tracking).
		'stipend_amount'  => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Stipend Amount', 'parish-core' ),
		),

		// Additional notes (private, not displayed).
		'notes'           => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Notes', 'parish-core' ),
		),
	),
);
