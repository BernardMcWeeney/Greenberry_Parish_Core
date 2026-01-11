<?php
/**
 * CPT: Parish History
 *
 * Flexible CPT for documenting parish history, events, and milestones.
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'post_type' => 'parish_history',
	'feature'   => 'history',
	'args'      => array(
		'labels' => array(
			'name'               => __( 'Parish History', 'parish-core' ),
			'singular_name'      => __( 'History Entry', 'parish-core' ),
			'add_new'            => __( 'Add New', 'parish-core' ),
			'add_new_item'       => __( 'Add New History Entry', 'parish-core' ),
			'edit_item'          => __( 'Edit History Entry', 'parish-core' ),
			'new_item'           => __( 'New History Entry', 'parish-core' ),
			'view_item'          => __( 'View History Entry', 'parish-core' ),
			'search_items'       => __( 'Search History', 'parish-core' ),
			'not_found'          => __( 'No history entries found', 'parish-core' ),
			'not_found_in_trash' => __( 'No history entries in trash', 'parish-core' ),
			'all_items'          => __( 'All History', 'parish-core' ),
			'menu_name'          => __( 'Parish History', 'parish-core' ),
		),
		'menu_icon'     => 'dashicons-book-alt',
		'rewrite'       => array( 'slug' => 'parish-history', 'with_front' => false ),
		'show_in_menu'  => 'parish-core',
		'template'      => Parish_CPT_Templates::get_history_template(),
		'template_lock' => false,
	),
);
