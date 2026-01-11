<?php
/**
 * Taxonomies: Wedding
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Wedding Status (hierarchical - for tracking).
register_taxonomy(
	'parish_wedding_status',
	'parish_wedding',
	array(
		'labels'            => array(
			'name'              => __( 'Status', 'parish-core' ),
			'singular_name'     => __( 'Status', 'parish-core' ),
			'search_items'      => __( 'Search Status', 'parish-core' ),
			'all_items'         => __( 'All Status', 'parish-core' ),
			'parent_item'       => __( 'Parent Status', 'parish-core' ),
			'parent_item_colon' => __( 'Parent Status:', 'parish-core' ),
			'edit_item'         => __( 'Edit Status', 'parish-core' ),
			'update_item'       => __( 'Update Status', 'parish-core' ),
			'add_new_item'      => __( 'Add New Status', 'parish-core' ),
			'new_item_name'     => __( 'New Status Name', 'parish-core' ),
			'menu_name'         => __( 'Status', 'parish-core' ),
		),
		'hierarchical'      => true,
		'public'            => false,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => false,
	)
);

// Wedding Year (hierarchical - for archiving).
register_taxonomy(
	'parish_wedding_year',
	'parish_wedding',
	array(
		'labels'            => array(
			'name'              => __( 'Years', 'parish-core' ),
			'singular_name'     => __( 'Year', 'parish-core' ),
			'search_items'      => __( 'Search Years', 'parish-core' ),
			'all_items'         => __( 'All Years', 'parish-core' ),
			'parent_item'       => __( 'Parent Year', 'parish-core' ),
			'parent_item_colon' => __( 'Parent Year:', 'parish-core' ),
			'edit_item'         => __( 'Edit Year', 'parish-core' ),
			'update_item'       => __( 'Update Year', 'parish-core' ),
			'add_new_item'      => __( 'Add New Year', 'parish-core' ),
			'new_item_name'     => __( 'New Year Name', 'parish-core' ),
			'menu_name'         => __( 'Years', 'parish-core' ),
		),
		'hierarchical'      => true,
		'public'            => false,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => false,
	)
);
