<?php
/**
 * CPT: Parish News
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'post_type' => 'parish_news',
	'feature'   => 'news',
	'args'      => array(
		'labels' => array(
			'name'               => __( 'Parish News', 'parish-core' ),
			'singular_name'      => __( 'News Item', 'parish-core' ),
			'add_new'            => __( 'Add New', 'parish-core' ),
			'add_new_item'       => __( 'Add New News', 'parish-core' ),
			'edit_item'          => __( 'Edit News', 'parish-core' ),
			'new_item'           => __( 'New News', 'parish-core' ),
			'view_item'          => __( 'View News', 'parish-core' ),
			'search_items'       => __( 'Search News', 'parish-core' ),
			'not_found'          => __( 'No news found', 'parish-core' ),
			'not_found_in_trash' => __( 'No news in trash', 'parish-core' ),
			'all_items'          => __( 'All News', 'parish-core' ),
			'menu_name'          => __( 'News', 'parish-core' ),
		),
		'menu_icon'     => 'dashicons-megaphone',
		'rewrite'       => array( 'slug' => 'parish-news', 'with_front' => false ),
		'show_in_menu'  => 'parish-core',
		'template'      => Parish_CPT_Templates::get_news_template(),
		'template_lock' => 'insert',
	),
);
