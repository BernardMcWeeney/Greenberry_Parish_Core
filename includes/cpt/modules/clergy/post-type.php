<?php
/**
 * CPT: Parish Clergy & Staff
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'post_type' => 'parish_clergy',
	'feature'   => 'clergy',
	'args'      => array(
		'labels' => array(
			'name'               => __( 'Clergy & Staff', 'parish-core' ),
			'singular_name'      => __( 'Clergy/Staff Member', 'parish-core' ),
			'add_new'            => __( 'Add New', 'parish-core' ),
			'add_new_item'       => __( 'Add New Member', 'parish-core' ),
			'edit_item'          => __( 'Edit Member', 'parish-core' ),
			'new_item'           => __( 'New Member', 'parish-core' ),
			'view_item'          => __( 'View Member', 'parish-core' ),
			'search_items'       => __( 'Search Clergy & Staff', 'parish-core' ),
			'not_found'          => __( 'No members found', 'parish-core' ),
			'not_found_in_trash' => __( 'No members in trash', 'parish-core' ),
			'all_items'          => __( 'All Clergy & Staff', 'parish-core' ),
			'menu_name'          => __( 'Clergy & Staff', 'parish-core' ),
		),
		'menu_icon'     => 'dashicons-businessman',
		'rewrite'       => array( 'slug' => 'clergy', 'with_front' => false ),
		'show_in_menu'  => 'parish-core',
		'template'      => Parish_CPT_Templates::get_clergy_template(),
		'template_lock' => 'all',
	),
);
