<?php
/**
 * CPT definition: Baptism Notices
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'post_type' => 'parish_baptism',
	'feature'   => 'baptism_notices',
	'args'      => array(
		'labels' => array(
			'name'               => __( 'Baptism Notices', 'parish-core' ),
			'singular_name'      => __( 'Baptism Notice', 'parish-core' ),
			'add_new'            => __( 'Add New', 'parish-core' ),
			'add_new_item'       => __( 'Add New Baptism Notice', 'parish-core' ),
			'edit_item'          => __( 'Edit Baptism Notice', 'parish-core' ),
			'new_item'           => __( 'New Baptism Notice', 'parish-core' ),
			'view_item'          => __( 'View Baptism Notice', 'parish-core' ),
			'search_items'       => __( 'Search Baptism Notices', 'parish-core' ),
			'not_found'          => __( 'No baptism notices found', 'parish-core' ),
			'not_found_in_trash' => __( 'No baptism notices in trash', 'parish-core' ),
			'all_items'          => __( 'All Baptisms', 'parish-core' ),
			'menu_name'          => __( 'Baptisms', 'parish-core' ),
		),
		'menu_icon'     => 'dashicons-groups',
		'rewrite'       => array( 'slug' => 'baptisms', 'with_front' => false ),
		'show_in_menu'  => 'parish-core',
		'show_in_rest'  => true, // Required for Gutenberg and Block Bindings

		// Gutenberg template with Block Bindings
		'template'      => Parish_CPT_Templates::get_baptism_template(),
		'template_lock' => 'insert', // Prevent adding/removing blocks, but allow editing

		// Optional: if you want the editor focused on your block only
		// 'supports' => array( 'title', 'thumbnail', 'revisions' ),
	),
);
