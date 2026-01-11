<?php
/**
 * CPT: Mass Times
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'post_type' => 'parish_mass_time',
	'feature'   => 'mass_times',
	'args'      => array(
		'labels'              => array(
			'name'               => __( 'Mass Times', 'parish-core' ),
			'singular_name'      => __( 'Mass Time', 'parish-core' ),
			'add_new'            => __( 'Add New', 'parish-core' ),
			'add_new_item'       => __( 'Add New Mass Time', 'parish-core' ),
			'edit_item'          => __( 'Edit Mass Time', 'parish-core' ),
			'new_item'           => __( 'New Mass Time', 'parish-core' ),
			'view_item'          => __( 'View Mass Time', 'parish-core' ),
			'search_items'       => __( 'Search Mass Times', 'parish-core' ),
			'not_found'          => __( 'No mass times found', 'parish-core' ),
			'not_found_in_trash' => __( 'No mass times in trash', 'parish-core' ),
			'all_items'          => __( 'All Mass Times', 'parish-core' ),
			'menu_name'          => __( 'Mass Times', 'parish-core' ),
		),
		'public'              => false,
		'publicly_queryable'  => false,
		'show_ui'             => false,
		'show_in_menu'        => false,
		'menu_icon'           => 'dashicons-clock',
		'rewrite'             => false,
		'show_in_rest'        => true,
		'rest_base'           => 'mass-times',
		'supports'            => array( 'title', 'custom-fields' ),
		'capability_type'     => 'post',
		'map_meta_cap'        => true,
		'exclude_from_search' => true,
	),
);
