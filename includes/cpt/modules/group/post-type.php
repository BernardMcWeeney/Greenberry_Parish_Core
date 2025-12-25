<?php
/**
 * CPT: Parish Groups
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'post_type' => 'parish_group',
	'feature'   => 'groups',
	'args'      => array(
		'labels' => array(
			'name'               => __( 'Parish Groups', 'parish-core' ),
			'singular_name'      => __( 'Parish Group', 'parish-core' ),
			'add_new'            => __( 'Add New', 'parish-core' ),
			'add_new_item'       => __( 'Add New Group', 'parish-core' ),
			'edit_item'          => __( 'Edit Group', 'parish-core' ),
			'new_item'           => __( 'New Group', 'parish-core' ),
			'view_item'          => __( 'View Group', 'parish-core' ),
			'search_items'       => __( 'Search Groups', 'parish-core' ),
			'not_found'          => __( 'No groups found', 'parish-core' ),
			'not_found_in_trash' => __( 'No groups in trash', 'parish-core' ),
			'all_items'          => __( 'All Groups', 'parish-core' ),
			'menu_name'          => __( 'Groups', 'parish-core' ),
		),
		'menu_icon'     => 'dashicons-groups',
		'rewrite'       => array( 'slug' => 'groups', 'with_front' => false ),
		'show_in_menu'  => 'parish-core',
		'template'      => Parish_CPT_Templates::get_group_template(),
		'template_lock' => 'all',
	),
);
