<?php
/**
 * CPT: Cemeteries
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'post_type' => 'parish_cemetery',
	'feature'   => 'cemeteries',
	'args'      => array(
		'labels' => array(
			'name'               => __( 'Cemeteries', 'parish-core' ),
			'singular_name'      => __( 'Cemetery', 'parish-core' ),
			'add_new'            => __( 'Add New', 'parish-core' ),
			'add_new_item'       => __( 'Add New Cemetery', 'parish-core' ),
			'edit_item'          => __( 'Edit Cemetery', 'parish-core' ),
			'new_item'           => __( 'New Cemetery', 'parish-core' ),
			'view_item'          => __( 'View Cemetery', 'parish-core' ),
			'search_items'       => __( 'Search Cemeteries', 'parish-core' ),
			'not_found'          => __( 'No cemeteries found', 'parish-core' ),
			'not_found_in_trash' => __( 'No cemeteries in trash', 'parish-core' ),
			'all_items'          => __( 'All Cemeteries', 'parish-core' ),
			'menu_name'          => __( 'Cemeteries', 'parish-core' ),
		),
		'menu_icon'     => 'dashicons-location',
		'rewrite'       => array( 'slug' => 'cemeteries', 'with_front' => false ),
		'show_in_menu'  => 'parish-core',
		'template'      => Parish_CPT_Templates::get_cemetery_template(),
		'template_lock' => 'all',
	),
);
