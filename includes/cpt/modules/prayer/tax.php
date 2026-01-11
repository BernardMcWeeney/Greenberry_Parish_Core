<?php
/**
 * Taxonomies: Prayer
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Prayer Type (hierarchical - like categories).
register_taxonomy(
	'parish_prayer_type',
	'parish_prayer',
	array(
		'labels'            => array(
			'name'              => __( 'Prayer Types', 'parish-core' ),
			'singular_name'     => __( 'Prayer Type', 'parish-core' ),
			'search_items'      => __( 'Search Prayer Types', 'parish-core' ),
			'all_items'         => __( 'All Prayer Types', 'parish-core' ),
			'parent_item'       => __( 'Parent Prayer Type', 'parish-core' ),
			'parent_item_colon' => __( 'Parent Prayer Type:', 'parish-core' ),
			'edit_item'         => __( 'Edit Prayer Type', 'parish-core' ),
			'update_item'       => __( 'Update Prayer Type', 'parish-core' ),
			'add_new_item'      => __( 'Add New Prayer Type', 'parish-core' ),
			'new_item_name'     => __( 'New Prayer Type Name', 'parish-core' ),
			'menu_name'         => __( 'Prayer Types', 'parish-core' ),
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'prayer-type', 'with_front' => false ),
	)
);

// Prayer Occasion (non-hierarchical - like tags).
register_taxonomy(
	'parish_prayer_occasion',
	'parish_prayer',
	array(
		'labels'            => array(
			'name'                       => __( 'Occasions', 'parish-core' ),
			'singular_name'              => __( 'Occasion', 'parish-core' ),
			'search_items'               => __( 'Search Occasions', 'parish-core' ),
			'popular_items'              => __( 'Popular Occasions', 'parish-core' ),
			'all_items'                  => __( 'All Occasions', 'parish-core' ),
			'edit_item'                  => __( 'Edit Occasion', 'parish-core' ),
			'update_item'                => __( 'Update Occasion', 'parish-core' ),
			'add_new_item'               => __( 'Add New Occasion', 'parish-core' ),
			'new_item_name'              => __( 'New Occasion Name', 'parish-core' ),
			'separate_items_with_commas' => __( 'Separate occasions with commas', 'parish-core' ),
			'add_or_remove_items'        => __( 'Add or remove occasions', 'parish-core' ),
			'choose_from_most_used'      => __( 'Choose from most used occasions', 'parish-core' ),
			'menu_name'                  => __( 'Occasions', 'parish-core' ),
		),
		'hierarchical'      => false,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'prayer-occasion', 'with_front' => false ),
	)
);
