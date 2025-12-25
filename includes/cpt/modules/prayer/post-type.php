<?php
/**
 * CPT: Prayers
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'post_type' => 'parish_prayer',
	'feature'   => 'prayers',
	'args'      => array(
		'labels' => array(
			'name'               => __( 'Prayers', 'parish-core' ),
			'singular_name'      => __( 'Prayer', 'parish-core' ),
			'add_new'            => __( 'Add New', 'parish-core' ),
			'add_new_item'       => __( 'Add New Prayer', 'parish-core' ),
			'edit_item'          => __( 'Edit Prayer', 'parish-core' ),
			'new_item'           => __( 'New Prayer', 'parish-core' ),
			'view_item'          => __( 'View Prayer', 'parish-core' ),
			'search_items'       => __( 'Search Prayers', 'parish-core' ),
			'not_found'          => __( 'No prayers found', 'parish-core' ),
			'not_found_in_trash' => __( 'No prayers in trash', 'parish-core' ),
			'all_items'          => __( 'All Prayers', 'parish-core' ),
			'menu_name'          => __( 'Prayers', 'parish-core' ),
		),

		// Override common supports: prayers need thumbnails.
		'supports'      => array( 'title', 'editor', 'thumbnail' ),

		'menu_icon'     => 'dashicons-book-alt',
		'rewrite'       => array( 'slug' => 'prayers', 'with_front' => false ),
		'show_in_menu'  => 'parish-core',
		'template'      => Parish_CPT_Templates::get_prayer_template(),
		'template_lock' => 'all',
	),
);
