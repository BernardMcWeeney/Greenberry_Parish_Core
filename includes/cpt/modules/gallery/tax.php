<?php
/**
 * Taxonomies: Gallery
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Gallery Event Type (hierarchical - like categories).
register_taxonomy(
	'parish_gallery_event',
	'parish_gallery',
	array(
		'labels'            => array(
			'name'              => __( 'Event Types', 'parish-core' ),
			'singular_name'     => __( 'Event Type', 'parish-core' ),
			'search_items'      => __( 'Search Event Types', 'parish-core' ),
			'all_items'         => __( 'All Event Types', 'parish-core' ),
			'parent_item'       => __( 'Parent Event Type', 'parish-core' ),
			'parent_item_colon' => __( 'Parent Event Type:', 'parish-core' ),
			'edit_item'         => __( 'Edit Event Type', 'parish-core' ),
			'update_item'       => __( 'Update Event Type', 'parish-core' ),
			'add_new_item'      => __( 'Add New Event Type', 'parish-core' ),
			'new_item_name'     => __( 'New Event Type Name', 'parish-core' ),
			'menu_name'         => __( 'Event Types', 'parish-core' ),
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'gallery-event', 'with_front' => false ),
	)
);

// Gallery Tags (non-hierarchical).
register_taxonomy(
	'parish_gallery_tag',
	'parish_gallery',
	array(
		'labels'            => array(
			'name'                       => __( 'Gallery Tags', 'parish-core' ),
			'singular_name'              => __( 'Gallery Tag', 'parish-core' ),
			'search_items'               => __( 'Search Tags', 'parish-core' ),
			'popular_items'              => __( 'Popular Tags', 'parish-core' ),
			'all_items'                  => __( 'All Tags', 'parish-core' ),
			'edit_item'                  => __( 'Edit Tag', 'parish-core' ),
			'update_item'                => __( 'Update Tag', 'parish-core' ),
			'add_new_item'               => __( 'Add New Tag', 'parish-core' ),
			'new_item_name'              => __( 'New Tag Name', 'parish-core' ),
			'separate_items_with_commas' => __( 'Separate tags with commas', 'parish-core' ),
			'add_or_remove_items'        => __( 'Add or remove tags', 'parish-core' ),
			'choose_from_most_used'      => __( 'Choose from most used tags', 'parish-core' ),
			'menu_name'                  => __( 'Tags', 'parish-core' ),
		),
		'hierarchical'      => false,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'gallery-tag', 'with_front' => false ),
	)
);
