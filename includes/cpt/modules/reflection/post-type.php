<?php
/**
 * CPT: Reflections
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'post_type' => 'parish_reflection',
	'feature'   => 'reflections',
	'args'      => array(
		'labels' => array(
			'name'               => __( 'Reflections', 'parish-core' ),
			'singular_name'      => __( 'Reflection', 'parish-core' ),
			'add_new'            => __( 'Add New', 'parish-core' ),
			'add_new_item'       => __( 'Add New Reflection', 'parish-core' ),
			'edit_item'          => __( 'Edit Reflection', 'parish-core' ),
			'new_item'           => __( 'New Reflection', 'parish-core' ),
			'view_item'          => __( 'View Reflection', 'parish-core' ),
			'search_items'       => __( 'Search Reflections', 'parish-core' ),
			'not_found'          => __( 'No reflections found', 'parish-core' ),
			'not_found_in_trash' => __( 'No reflections in trash', 'parish-core' ),
			'all_items'          => __( 'All Reflections', 'parish-core' ),
			'menu_name'          => __( 'Reflections', 'parish-core' ),
		),

		// Override common supports: reflections are intentionally simple.
		// Must include 'custom-fields' for Block Bindings meta to save.
		'supports'      => array( 'title', 'editor', 'custom-fields' ),

		'menu_icon'     => 'dashicons-format-quote',
		'rewrite'       => array( 'slug' => 'reflections', 'with_front' => false ),
		'show_in_menu'  => 'parish-core',
		'template'      => Parish_CPT_Templates::get_reflection_template(),
		'template_lock' => 'all',
	),
);
