<?php
/**
 * Taxonomies: Reflection
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Reflection Type (hierarchical - like categories).
register_taxonomy(
	'parish_reflection_type',
	'parish_reflection',
	array(
		'labels'            => array(
			'name'              => __( 'Reflection Types', 'parish-core' ),
			'singular_name'     => __( 'Reflection Type', 'parish-core' ),
			'search_items'      => __( 'Search Reflection Types', 'parish-core' ),
			'all_items'         => __( 'All Reflection Types', 'parish-core' ),
			'parent_item'       => __( 'Parent Reflection Type', 'parish-core' ),
			'parent_item_colon' => __( 'Parent Reflection Type:', 'parish-core' ),
			'edit_item'         => __( 'Edit Reflection Type', 'parish-core' ),
			'update_item'       => __( 'Update Reflection Type', 'parish-core' ),
			'add_new_item'      => __( 'Add New Reflection Type', 'parish-core' ),
			'new_item_name'     => __( 'New Reflection Type Name', 'parish-core' ),
			'menu_name'         => __( 'Reflection Types', 'parish-core' ),
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'reflection-type', 'with_front' => false ),
	)
);

// Liturgical Season (non-hierarchical - like tags).
register_taxonomy(
	'parish_liturgical_season',
	'parish_reflection',
	array(
		'labels'            => array(
			'name'                       => __( 'Liturgical Seasons', 'parish-core' ),
			'singular_name'              => __( 'Liturgical Season', 'parish-core' ),
			'search_items'               => __( 'Search Seasons', 'parish-core' ),
			'popular_items'              => __( 'Popular Seasons', 'parish-core' ),
			'all_items'                  => __( 'All Seasons', 'parish-core' ),
			'edit_item'                  => __( 'Edit Season', 'parish-core' ),
			'update_item'                => __( 'Update Season', 'parish-core' ),
			'add_new_item'               => __( 'Add New Season', 'parish-core' ),
			'new_item_name'              => __( 'New Season Name', 'parish-core' ),
			'separate_items_with_commas' => __( 'Separate seasons with commas', 'parish-core' ),
			'add_or_remove_items'        => __( 'Add or remove seasons', 'parish-core' ),
			'choose_from_most_used'      => __( 'Choose from most used seasons', 'parish-core' ),
			'menu_name'                  => __( 'Seasons', 'parish-core' ),
		),
		'hierarchical'      => false,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'liturgical-season', 'with_front' => false ),
	)
);
