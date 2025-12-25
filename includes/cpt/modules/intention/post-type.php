<?php
/**
 * CPT: Mass Intentions
 *
 * Lightweight CPT for managing mass intentions.
 * Intentions are linked to schedule templates and auto-expire after a week.
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'post_type' => 'parish_intention',
	'feature'   => 'mass_times',
	'args'      => array(
		'labels' => array(
			'name'               => __( 'Mass Intentions', 'parish-core' ),
			'singular_name'      => __( 'Mass Intention', 'parish-core' ),
			'add_new'            => __( 'Add New', 'parish-core' ),
			'add_new_item'       => __( 'Add New Intention', 'parish-core' ),
			'edit_item'          => __( 'Edit Intention', 'parish-core' ),
			'new_item'           => __( 'New Intention', 'parish-core' ),
			'view_item'          => __( 'View Intention', 'parish-core' ),
			'search_items'       => __( 'Search Intentions', 'parish-core' ),
			'not_found'          => __( 'No intentions found', 'parish-core' ),
			'not_found_in_trash' => __( 'No intentions in trash', 'parish-core' ),
			'all_items'          => __( 'All Intentions', 'parish-core' ),
			'menu_name'          => __( 'Mass Intentions', 'parish-core' ),
		),
		'menu_icon'           => 'dashicons-heart',
		'public'              => false,
		'publicly_queryable'  => false,
		'show_ui'             => true,
		'show_in_menu'        => 'parish-core',
		'show_in_rest'        => true,
		'exclude_from_search' => true,
		'capability_type'     => 'post',
		'supports'            => array( 'title', 'custom-fields' ),
		'rewrite'             => false,
		'has_archive'         => false,
	),
);
