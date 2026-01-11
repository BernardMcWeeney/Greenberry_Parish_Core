<?php
/**
 * Taxonomies: Church
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Church Type (hierarchical - like categories).
register_taxonomy(
	'parish_church_type',
	'parish_church',
	array(
		'labels'            => array(
			'name'              => __( 'Church Types', 'parish-core' ),
			'singular_name'     => __( 'Church Type', 'parish-core' ),
			'search_items'      => __( 'Search Church Types', 'parish-core' ),
			'all_items'         => __( 'All Church Types', 'parish-core' ),
			'parent_item'       => __( 'Parent Church Type', 'parish-core' ),
			'parent_item_colon' => __( 'Parent Church Type:', 'parish-core' ),
			'edit_item'         => __( 'Edit Church Type', 'parish-core' ),
			'update_item'       => __( 'Update Church Type', 'parish-core' ),
			'add_new_item'      => __( 'Add New Church Type', 'parish-core' ),
			'new_item_name'     => __( 'New Church Type Name', 'parish-core' ),
			'menu_name'         => __( 'Church Types', 'parish-core' ),
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'church-type', 'with_front' => false ),
	)
);
