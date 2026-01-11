<?php
/**
 * CPT: Parish Services
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'post_type' => 'parish_service',
	'feature'   => 'services',
	'args'      => array(
		'labels' => array(
			'name'               => __( 'Parish Services', 'parish-core' ),
			'singular_name'      => __( 'Parish Service', 'parish-core' ),
			'add_new'            => __( 'Add New', 'parish-core' ),
			'add_new_item'       => __( 'Add New Service', 'parish-core' ),
			'edit_item'          => __( 'Edit Service', 'parish-core' ),
			'new_item'           => __( 'New Service', 'parish-core' ),
			'view_item'          => __( 'View Service', 'parish-core' ),
			'search_items'       => __( 'Search Services', 'parish-core' ),
			'not_found'          => __( 'No services found', 'parish-core' ),
			'not_found_in_trash' => __( 'No services in trash', 'parish-core' ),
			'all_items'          => __( 'All Services', 'parish-core' ),
			'menu_name'          => __( 'Services', 'parish-core' ),
		),
		'menu_icon'     => 'dashicons-heart',
		'rewrite'       => array( 'slug' => 'services', 'with_front' => false ),
		'show_in_menu'  => 'parish-core',
		'template'      => Parish_CPT_Templates::get_service_template(),
		'template_lock' => 'all',
	),
);
