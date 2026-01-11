<?php
/**
 * Taxonomies: Service
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Service Type (hierarchical - like categories).
register_taxonomy(
	'parish_service_type',
	'parish_service',
	array(
		'labels'            => array(
			'name'              => __( 'Service Types', 'parish-core' ),
			'singular_name'     => __( 'Service Type', 'parish-core' ),
			'search_items'      => __( 'Search Service Types', 'parish-core' ),
			'all_items'         => __( 'All Service Types', 'parish-core' ),
			'parent_item'       => __( 'Parent Service Type', 'parish-core' ),
			'parent_item_colon' => __( 'Parent Service Type:', 'parish-core' ),
			'edit_item'         => __( 'Edit Service Type', 'parish-core' ),
			'update_item'       => __( 'Update Service Type', 'parish-core' ),
			'add_new_item'      => __( 'Add New Service Type', 'parish-core' ),
			'new_item_name'     => __( 'New Service Type Name', 'parish-core' ),
			'menu_name'         => __( 'Service Types', 'parish-core' ),
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'service-type', 'with_front' => false ),
	)
);

// Service Category (non-hierarchical - like tags).
register_taxonomy(
	'parish_service_category',
	'parish_service',
	array(
		'labels'            => array(
			'name'                       => __( 'Categories', 'parish-core' ),
			'singular_name'              => __( 'Category', 'parish-core' ),
			'search_items'               => __( 'Search Categories', 'parish-core' ),
			'popular_items'              => __( 'Popular Categories', 'parish-core' ),
			'all_items'                  => __( 'All Categories', 'parish-core' ),
			'edit_item'                  => __( 'Edit Category', 'parish-core' ),
			'update_item'                => __( 'Update Category', 'parish-core' ),
			'add_new_item'               => __( 'Add New Category', 'parish-core' ),
			'new_item_name'              => __( 'New Category Name', 'parish-core' ),
			'separate_items_with_commas' => __( 'Separate categories with commas', 'parish-core' ),
			'add_or_remove_items'        => __( 'Add or remove categories', 'parish-core' ),
			'choose_from_most_used'      => __( 'Choose from most used categories', 'parish-core' ),
			'menu_name'                  => __( 'Categories', 'parish-core' ),
		),
		'hierarchical'      => false,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'service-category', 'with_front' => false ),
	)
);
