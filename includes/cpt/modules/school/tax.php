<?php
/**
 * Taxonomies: School
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// School Type (hierarchical - like categories).
register_taxonomy(
	'parish_school_type',
	'parish_school',
	array(
		'labels'            => array(
			'name'              => __( 'School Types', 'parish-core' ),
			'singular_name'     => __( 'School Type', 'parish-core' ),
			'search_items'      => __( 'Search School Types', 'parish-core' ),
			'all_items'         => __( 'All School Types', 'parish-core' ),
			'parent_item'       => __( 'Parent School Type', 'parish-core' ),
			'parent_item_colon' => __( 'Parent School Type:', 'parish-core' ),
			'edit_item'         => __( 'Edit School Type', 'parish-core' ),
			'update_item'       => __( 'Update School Type', 'parish-core' ),
			'add_new_item'      => __( 'Add New School Type', 'parish-core' ),
			'new_item_name'     => __( 'New School Type Name', 'parish-core' ),
			'menu_name'         => __( 'School Types', 'parish-core' ),
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'school-type', 'with_front' => false ),
	)
);
