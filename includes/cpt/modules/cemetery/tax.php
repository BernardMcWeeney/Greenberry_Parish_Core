<?php
/**
 * Taxonomies: Cemetery
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Cemetery Section (hierarchical - like categories).
register_taxonomy(
	'parish_cemetery_section',
	'parish_cemetery',
	array(
		'labels'            => array(
			'name'              => __( 'Sections', 'parish-core' ),
			'singular_name'     => __( 'Section', 'parish-core' ),
			'search_items'      => __( 'Search Sections', 'parish-core' ),
			'all_items'         => __( 'All Sections', 'parish-core' ),
			'parent_item'       => __( 'Parent Section', 'parish-core' ),
			'parent_item_colon' => __( 'Parent Section:', 'parish-core' ),
			'edit_item'         => __( 'Edit Section', 'parish-core' ),
			'update_item'       => __( 'Update Section', 'parish-core' ),
			'add_new_item'      => __( 'Add New Section', 'parish-core' ),
			'new_item_name'     => __( 'New Section Name', 'parish-core' ),
			'menu_name'         => __( 'Sections', 'parish-core' ),
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'cemetery-section', 'with_front' => false ),
	)
);
