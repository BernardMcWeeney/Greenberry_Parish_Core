<?php
/**
 * CPT: Schools
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'post_type' => 'parish_school',
	'feature'   => 'schools',
	'args'      => array(
		'labels' => array(
			'name'               => __( 'Schools', 'parish-core' ),
			'singular_name'      => __( 'School', 'parish-core' ),
			'add_new'            => __( 'Add New', 'parish-core' ),
			'add_new_item'       => __( 'Add New School', 'parish-core' ),
			'edit_item'          => __( 'Edit School', 'parish-core' ),
			'new_item'           => __( 'New School', 'parish-core' ),
			'view_item'          => __( 'View School', 'parish-core' ),
			'search_items'       => __( 'Search Schools', 'parish-core' ),
			'not_found'          => __( 'No schools found', 'parish-core' ),
			'not_found_in_trash' => __( 'No schools in trash', 'parish-core' ),
			'all_items'          => __( 'All Schools', 'parish-core' ),
			'menu_name'          => __( 'Schools', 'parish-core' ),
		),
		'menu_icon'     => 'dashicons-welcome-learn-more',
		'rewrite'       => array( 'slug' => 'schools', 'with_front' => false ),
		'show_in_menu'  => 'parish-core',
		'template'      => Parish_CPT_Templates::get_school_template(),
		'template_lock' => 'all',
	),
);
