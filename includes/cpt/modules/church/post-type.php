<?php
/**
 * CPT: Churches
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'post_type' => 'parish_church',
	'feature'   => 'churches',
	'args'      => array(
		'labels' => array(
			'name'               => __( 'Churches', 'parish-core' ),
			'singular_name'      => __( 'Church', 'parish-core' ),
			'add_new'            => __( 'Add New', 'parish-core' ),
			'add_new_item'       => __( 'Add New Church', 'parish-core' ),
			'edit_item'          => __( 'Edit Church', 'parish-core' ),
			'new_item'           => __( 'New Church', 'parish-core' ),
			'view_item'          => __( 'View Church', 'parish-core' ),
			'search_items'       => __( 'Search Churches', 'parish-core' ),
			'not_found'          => __( 'No churches found', 'parish-core' ),
			'not_found_in_trash' => __( 'No churches in trash', 'parish-core' ),
			'all_items'          => __( 'All Churches', 'parish-core' ),
			'menu_name'          => __( 'Churches', 'parish-core' ),
		),
		'menu_icon'     => 'dashicons-building',
		'rewrite'       => array( 'slug' => 'churches', 'with_front' => false ),
		'show_in_menu'  => 'parish-core',
		'show_in_rest'  => true, // Required for Gutenberg and Block Bindings
		'template'      => Parish_CPT_Templates::get_church_template(),
		'template_lock' => 'insert', // Prevent adding/removing blocks, but allow editing
	),
);
