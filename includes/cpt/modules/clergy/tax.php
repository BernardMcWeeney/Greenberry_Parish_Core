<?php
/**
 * Taxonomies: Clergy
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Clergy Role (hierarchical - like categories).
register_taxonomy(
	'parish_clergy_role',
	'parish_clergy',
	array(
		'labels'            => array(
			'name'              => __( 'Roles', 'parish-core' ),
			'singular_name'     => __( 'Role', 'parish-core' ),
			'search_items'      => __( 'Search Roles', 'parish-core' ),
			'all_items'         => __( 'All Roles', 'parish-core' ),
			'parent_item'       => __( 'Parent Role', 'parish-core' ),
			'parent_item_colon' => __( 'Parent Role:', 'parish-core' ),
			'edit_item'         => __( 'Edit Role', 'parish-core' ),
			'update_item'       => __( 'Update Role', 'parish-core' ),
			'add_new_item'      => __( 'Add New Role', 'parish-core' ),
			'new_item_name'     => __( 'New Role Name', 'parish-core' ),
			'menu_name'         => __( 'Roles', 'parish-core' ),
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'clergy-role', 'with_front' => false ),
	)
);

// Clergy Status (hierarchical - for Active/Past filtering).
register_taxonomy(
	'parish_clergy_status',
	'parish_clergy',
	array(
		'labels'            => array(
			'name'              => __( 'Status', 'parish-core' ),
			'singular_name'     => __( 'Status', 'parish-core' ),
			'search_items'      => __( 'Search Status', 'parish-core' ),
			'all_items'         => __( 'All Status', 'parish-core' ),
			'parent_item'       => __( 'Parent Status', 'parish-core' ),
			'parent_item_colon' => __( 'Parent Status:', 'parish-core' ),
			'edit_item'         => __( 'Edit Status', 'parish-core' ),
			'update_item'       => __( 'Update Status', 'parish-core' ),
			'add_new_item'      => __( 'Add New Status', 'parish-core' ),
			'new_item_name'     => __( 'New Status Name', 'parish-core' ),
			'menu_name'         => __( 'Status', 'parish-core' ),
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'clergy-status', 'with_front' => false ),
	)
);

/**
 * Create default status terms for clergy if they don't exist.
 */
add_action(
	'init',
	function () {
		// Only run once per request and after taxonomy is registered.
		static $did_seed = false;
		if ( $did_seed ) {
			return;
		}
		$did_seed = true;

		// Check if taxonomy exists.
		if ( ! taxonomy_exists( 'parish_clergy_status' ) ) {
			return;
		}

		// Default status terms.
		$default_terms = array(
			'active' => __( 'Active', 'parish-core' ),
			'past'   => __( 'Past', 'parish-core' ),
		);

		foreach ( $default_terms as $slug => $name ) {
			if ( ! term_exists( $slug, 'parish_clergy_status' ) ) {
				wp_insert_term(
					$name,
					'parish_clergy_status',
					array( 'slug' => $slug )
				);
			}
		}
	},
	20
);

/**
 * Create default role terms for clergy if they don't exist.
 */
add_action(
	'init',
	function () {
		// Only run once per request and after taxonomy is registered.
		static $did_seed = false;
		if ( $did_seed ) {
			return;
		}
		$did_seed = true;

		// Check if taxonomy exists.
		if ( ! taxonomy_exists( 'parish_clergy_role' ) ) {
			return;
		}

		// Default role terms.
		$default_terms = array(
			'parish-priest'    => __( 'Parish Priest', 'parish-core' ),
			'curate'           => __( 'Curate', 'parish-core' ),
			'deacon'           => __( 'Deacon', 'parish-core' ),
			'pastoral-worker'  => __( 'Pastoral Worker', 'parish-core' ),
			'secretary'        => __( 'Secretary', 'parish-core' ),
			'sacristan'        => __( 'Sacristan', 'parish-core' ),
		);

		foreach ( $default_terms as $slug => $name ) {
			if ( ! term_exists( $slug, 'parish_clergy_role' ) ) {
				wp_insert_term(
					$name,
					'parish_clergy_role',
					array( 'slug' => $slug )
				);
			}
		}
	},
	20
);
