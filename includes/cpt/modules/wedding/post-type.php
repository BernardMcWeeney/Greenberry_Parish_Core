<?php
/**
 * CPT: Wedding Notices
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'post_type' => 'parish_wedding',
	'feature'   => 'wedding_notices',
	'args'      => array(
		'labels' => array(
			'name'               => __( 'Wedding Notices', 'parish-core' ),
			'singular_name'      => __( 'Wedding Notice', 'parish-core' ),
			'add_new'            => __( 'Add New', 'parish-core' ),
			'add_new_item'       => __( 'Add New Wedding Notice', 'parish-core' ),
			'edit_item'          => __( 'Edit Wedding Notice', 'parish-core' ),
			'new_item'           => __( 'New Wedding Notice', 'parish-core' ),
			'view_item'          => __( 'View Wedding Notice', 'parish-core' ),
			'search_items'       => __( 'Search Wedding Notices', 'parish-core' ),
			'not_found'          => __( 'No wedding notices found', 'parish-core' ),
			'not_found_in_trash' => __( 'No wedding notices in trash', 'parish-core' ),
			'all_items'          => __( 'All Weddings', 'parish-core' ),
			'menu_name'          => __( 'Weddings', 'parish-core' ),
		),
		'menu_icon'     => 'dashicons-heart',
		'rewrite'       => array( 'slug' => 'weddings', 'with_front' => false ),
		'show_in_menu'  => 'parish-core',
		'template'      => Parish_CPT_Templates::get_wedding_template(),
		'template_lock' => 'all',
	),
);
