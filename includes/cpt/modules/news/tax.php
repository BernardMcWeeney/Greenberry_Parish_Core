<?php
/**
 * Taxonomies: Parish News
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// News Category (hierarchical - like categories).
register_taxonomy(
	'parish_news_category',
	'parish_news',
	array(
		'labels'            => array(
			'name'              => __( 'News Categories', 'parish-core' ),
			'singular_name'     => __( 'News Category', 'parish-core' ),
			'search_items'      => __( 'Search Categories', 'parish-core' ),
			'all_items'         => __( 'All Categories', 'parish-core' ),
			'parent_item'       => __( 'Parent Category', 'parish-core' ),
			'parent_item_colon' => __( 'Parent Category:', 'parish-core' ),
			'edit_item'         => __( 'Edit Category', 'parish-core' ),
			'update_item'       => __( 'Update Category', 'parish-core' ),
			'add_new_item'      => __( 'Add New Category', 'parish-core' ),
			'new_item_name'     => __( 'New Category Name', 'parish-core' ),
			'menu_name'         => __( 'Categories', 'parish-core' ),
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'news-category', 'with_front' => false ),
	)
);

// News Tags (non-hierarchical - like tags).
register_taxonomy(
	'parish_news_tag',
	'parish_news',
	array(
		'labels'            => array(
			'name'                       => __( 'News Tags', 'parish-core' ),
			'singular_name'              => __( 'News Tag', 'parish-core' ),
			'search_items'               => __( 'Search Tags', 'parish-core' ),
			'popular_items'              => __( 'Popular Tags', 'parish-core' ),
			'all_items'                  => __( 'All Tags', 'parish-core' ),
			'edit_item'                  => __( 'Edit Tag', 'parish-core' ),
			'update_item'                => __( 'Update Tag', 'parish-core' ),
			'add_new_item'               => __( 'Add New Tag', 'parish-core' ),
			'new_item_name'              => __( 'New Tag Name', 'parish-core' ),
			'separate_items_with_commas' => __( 'Separate tags with commas', 'parish-core' ),
			'add_or_remove_items'        => __( 'Add or remove tags', 'parish-core' ),
			'choose_from_most_used'      => __( 'Choose from most used tags', 'parish-core' ),
			'menu_name'                  => __( 'Tags', 'parish-core' ),
		),
		'hierarchical'      => false,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'news-tag', 'with_front' => false ),
	)
);
