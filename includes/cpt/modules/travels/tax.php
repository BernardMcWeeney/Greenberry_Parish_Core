<?php
/**
 * Taxonomies: Parish Travels
 *
 * Registers custom taxonomies for the parish_travels post type.
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Travel Type taxonomy (Pilgrimage, Day Trip, Retreat, etc.)
 */
register_taxonomy(
	'parish_travel_type',
	'parish_travels',
	array(
		'labels'            => array(
			'name'                       => __( 'Travel Types', 'parish-core' ),
			'singular_name'              => __( 'Travel Type', 'parish-core' ),
			'search_items'               => __( 'Search Travel Types', 'parish-core' ),
			'popular_items'              => __( 'Popular Travel Types', 'parish-core' ),
			'all_items'                  => __( 'All Travel Types', 'parish-core' ),
			'edit_item'                  => __( 'Edit Travel Type', 'parish-core' ),
			'update_item'                => __( 'Update Travel Type', 'parish-core' ),
			'add_new_item'               => __( 'Add New Travel Type', 'parish-core' ),
			'new_item_name'              => __( 'New Travel Type Name', 'parish-core' ),
			'separate_items_with_commas' => __( 'Separate types with commas', 'parish-core' ),
			'add_or_remove_items'        => __( 'Add or remove travel types', 'parish-core' ),
			'choose_from_most_used'      => __( 'Choose from most used types', 'parish-core' ),
			'not_found'                  => __( 'No travel types found', 'parish-core' ),
			'menu_name'                  => __( 'Travel Types', 'parish-core' ),
			'back_to_items'              => __( 'Back to Travel Types', 'parish-core' ),
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'show_in_nav_menus' => true,
		'rewrite'           => array(
			'slug'         => 'travel-type',
			'with_front'   => false,
			'hierarchical' => true,
		),
	)
);

/**
 * Travel Destination taxonomy (Lourdes, Rome, Holy Land, etc.)
 */
register_taxonomy(
	'parish_travel_destination',
	'parish_travels',
	array(
		'labels'            => array(
			'name'                       => __( 'Destinations', 'parish-core' ),
			'singular_name'              => __( 'Destination', 'parish-core' ),
			'search_items'               => __( 'Search Destinations', 'parish-core' ),
			'popular_items'              => __( 'Popular Destinations', 'parish-core' ),
			'all_items'                  => __( 'All Destinations', 'parish-core' ),
			'edit_item'                  => __( 'Edit Destination', 'parish-core' ),
			'update_item'                => __( 'Update Destination', 'parish-core' ),
			'add_new_item'               => __( 'Add New Destination', 'parish-core' ),
			'new_item_name'              => __( 'New Destination Name', 'parish-core' ),
			'separate_items_with_commas' => __( 'Separate destinations with commas', 'parish-core' ),
			'add_or_remove_items'        => __( 'Add or remove destinations', 'parish-core' ),
			'choose_from_most_used'      => __( 'Choose from most used destinations', 'parish-core' ),
			'not_found'                  => __( 'No destinations found', 'parish-core' ),
			'menu_name'                  => __( 'Destinations', 'parish-core' ),
			'back_to_items'              => __( 'Back to Destinations', 'parish-core' ),
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'show_in_nav_menus' => true,
		'rewrite'           => array(
			'slug'         => 'destination',
			'with_front'   => false,
			'hierarchical' => true,
		),
	)
);
