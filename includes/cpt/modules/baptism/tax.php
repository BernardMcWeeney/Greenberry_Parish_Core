<?php
/**
 * Taxonomies: Baptism
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Baptism Year (hierarchical - for archiving).
register_taxonomy(
	'parish_baptism_year',
	'parish_baptism',
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
