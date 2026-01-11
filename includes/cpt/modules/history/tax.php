<?php
/**
 * Taxonomies: Parish History
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// History Era/Period (hierarchical - like categories).
register_taxonomy(
	'parish_history_era',
	'parish_history',
	array(
		'labels'            => array(
			'name'              => __( 'Eras', 'parish-core' ),
			'singular_name'     => __( 'Era', 'parish-core' ),
			'search_items'      => __( 'Search Eras', 'parish-core' ),
			'all_items'         => __( 'All Eras', 'parish-core' ),
			'parent_item'       => __( 'Parent Era', 'parish-core' ),
			'parent_item_colon' => __( 'Parent Era:', 'parish-core' ),
			'edit_item'         => __( 'Edit Era', 'parish-core' ),
			'update_item'       => __( 'Update Era', 'parish-core' ),
			'add_new_item'      => __( 'Add New Era', 'parish-core' ),
			'new_item_name'     => __( 'New Era Name', 'parish-core' ),
			'menu_name'         => __( 'Eras', 'parish-core' ),
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'history-era', 'with_front' => false ),
	)
);

// History Topic (hierarchical - like categories).
register_taxonomy(
	'parish_history_topic',
	'parish_history',
	array(
		'labels'            => array(
			'name'              => __( 'Topics', 'parish-core' ),
			'singular_name'     => __( 'Topic', 'parish-core' ),
			'search_items'      => __( 'Search Topics', 'parish-core' ),
			'all_items'         => __( 'All Topics', 'parish-core' ),
			'parent_item'       => __( 'Parent Topic', 'parish-core' ),
			'parent_item_colon' => __( 'Parent Topic:', 'parish-core' ),
			'edit_item'         => __( 'Edit Topic', 'parish-core' ),
			'update_item'       => __( 'Update Topic', 'parish-core' ),
			'add_new_item'      => __( 'Add New Topic', 'parish-core' ),
			'new_item_name'     => __( 'New Topic Name', 'parish-core' ),
			'menu_name'         => __( 'Topics', 'parish-core' ),
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'history-topic', 'with_front' => false ),
	)
);

// History Tags (non-hierarchical - like tags).
register_taxonomy(
	'parish_history_tag',
	'parish_history',
	array(
		'labels'            => array(
			'name'                       => __( 'Tags', 'parish-core' ),
			'singular_name'              => __( 'Tag', 'parish-core' ),
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
		'rewrite'           => array( 'slug' => 'history-tag', 'with_front' => false ),
	)
);

/**
 * Create default era terms for history if they don't exist.
 */
add_action(
	'init',
	function () {
		static $did_seed = false;
		if ( $did_seed ) {
			return;
		}
		$did_seed = true;

		if ( ! taxonomy_exists( 'parish_history_era' ) ) {
			return;
		}

		$default_terms = array(
			'founding'   => __( 'Founding Years', 'parish-core' ),
			'early'      => __( 'Early Years', 'parish-core' ),
			'mid-century' => __( 'Mid-Century', 'parish-core' ),
			'modern'     => __( 'Modern Era', 'parish-core' ),
		);

		foreach ( $default_terms as $slug => $name ) {
			if ( ! term_exists( $slug, 'parish_history_era' ) ) {
				wp_insert_term( $name, 'parish_history_era', array( 'slug' => $slug ) );
			}
		}
	},
	20
);

/**
 * Create default topic terms for history if they don't exist.
 */
add_action(
	'init',
	function () {
		static $did_seed = false;
		if ( $did_seed ) {
			return;
		}
		$did_seed = true;

		if ( ! taxonomy_exists( 'parish_history_topic' ) ) {
			return;
		}

		$default_terms = array(
			'buildings'     => __( 'Buildings & Architecture', 'parish-core' ),
			'clergy'        => __( 'Clergy & Religious', 'parish-core' ),
			'events'        => __( 'Events & Celebrations', 'parish-core' ),
			'organisations' => __( 'Organisations & Groups', 'parish-core' ),
			'people'        => __( 'People & Parishioners', 'parish-core' ),
			'sacraments'    => __( 'Sacraments & Ceremonies', 'parish-core' ),
		);

		foreach ( $default_terms as $slug => $name ) {
			if ( ! term_exists( $slug, 'parish_history_topic' ) ) {
				wp_insert_term( $name, 'parish_history_topic', array( 'slug' => $slug ) );
			}
		}
	},
	20
);
