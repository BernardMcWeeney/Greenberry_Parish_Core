<?php
/**
 * Taxonomies: Group
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Group Category (hierarchical - like categories).
register_taxonomy(
	'parish_group_category',
	'parish_group',
	array(
		'labels'            => array(
			'name'              => __( 'Group Categories', 'parish-core' ),
			'singular_name'     => __( 'Group Category', 'parish-core' ),
			'search_items'      => __( 'Search Categories', 'parish-core' ),
			'all_items'         => __( 'All Categories', 'parish-core' ),
			'parent_item'       => __( 'Parent Category', 'parish-core' ),
			'parent_item_colon' => __( 'Parent Category:', 'parish-core' ),
			'edit_item'         => __( 'Edit Category', 'parish-core' ),
			'update_item'       => __( 'Update Category', 'parish-core' ),
			'add_new_item'      => __( 'Add New Category', 'parish-core' ),
			'new_item_name'     => __( 'New Category Name', 'parish-core' ),
			'menu_name'         => __( 'Categories', 'parish-core' ),
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'group-category', 'with_front' => false ),
	)
);

// Group Type (non-hierarchical - like tags).
register_taxonomy(
	'parish_group_type',
	'parish_group',
	array(
		'labels'            => array(
			'name'                       => __( 'Group Types', 'parish-core' ),
			'singular_name'              => __( 'Group Type', 'parish-core' ),
			'search_items'               => __( 'Search Types', 'parish-core' ),
			'popular_items'              => __( 'Popular Types', 'parish-core' ),
			'all_items'                  => __( 'All Types', 'parish-core' ),
			'edit_item'                  => __( 'Edit Type', 'parish-core' ),
			'update_item'                => __( 'Update Type', 'parish-core' ),
			'add_new_item'               => __( 'Add New Type', 'parish-core' ),
			'new_item_name'              => __( 'New Type Name', 'parish-core' ),
			'separate_items_with_commas' => __( 'Separate types with commas', 'parish-core' ),
			'add_or_remove_items'        => __( 'Add or remove types', 'parish-core' ),
			'choose_from_most_used'      => __( 'Choose from most used types', 'parish-core' ),
			'menu_name'                  => __( 'Types', 'parish-core' ),
		),
		'hierarchical'      => false,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'group-type', 'with_front' => false ),
	)
);
