<?php
/**
 * CPT: Gallery
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'post_type' => 'parish_gallery',
	'feature'   => 'gallery',
	'args'      => array(
		'labels' => array(
			'name'               => __( 'Gallery', 'parish-core' ),
			'singular_name'      => __( 'Gallery', 'parish-core' ),
			'add_new'            => __( 'Add New', 'parish-core' ),
			'add_new_item'       => __( 'Add New Gallery', 'parish-core' ),
			'edit_item'          => __( 'Edit Gallery', 'parish-core' ),
			'new_item'           => __( 'New Gallery', 'parish-core' ),
			'view_item'          => __( 'View Gallery', 'parish-core' ),
			'search_items'       => __( 'Search Galleries', 'parish-core' ),
			'not_found'          => __( 'No galleries found', 'parish-core' ),
			'not_found_in_trash' => __( 'No galleries in trash', 'parish-core' ),
			'all_items'          => __( 'All Galleries', 'parish-core' ),
			'menu_name'          => __( 'Gallery', 'parish-core' ),
		),
		'menu_icon'     => 'dashicons-format-gallery',
		'rewrite'       => array( 'slug' => 'gallery', 'with_front' => false ),
		'show_in_menu'  => 'parish-core',
		'template'      => Parish_CPT_Templates::get_gallery_template(),
		'template_lock' => 'all',
	),
);
