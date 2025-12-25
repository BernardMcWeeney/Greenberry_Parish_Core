<?php
/**
 * Meta schema: Newsletter (parish_newsletter)
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
	'post_type' => 'parish_newsletter',
	'fields'    => array(
		// Publication Information
		'publication_date'  => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field', // YYYY-MM-DD
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Publication date', 'parish-core' ),
		),
		'issue_number'      => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field', // e.g., "Vol. 5 Issue 12"
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Issue number', 'parish-core' ),
		),

		// File Attachment
		'pdf_file_id'       => array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'show_in_rest'      => true,
			'default'           => 0,
			'label'             => __( 'PDF file ID', 'parish-core' ),
		),
		'pdf_file_url'      => array(
			'type'              => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'PDF file URL', 'parish-core' ),
		),

		// Alternative Formats
		'online_link'       => array(
			'type'              => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Online link', 'parish-core' ),
		),

		// Summary/Highlights
		'summary'           => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Summary', 'parish-core' ),
		),
		'highlights'        => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Highlights', 'parish-core' ),
		),

		// Editor Information
		'editor_name'       => array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '',
			'label'             => __( 'Editor', 'parish-core' ),
		),

		// Archive
		'is_featured'       => array(
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'show_in_rest'      => true,
			'default'           => false,
			'label'             => __( 'Featured', 'parish-core' ),
		),
	),
);
