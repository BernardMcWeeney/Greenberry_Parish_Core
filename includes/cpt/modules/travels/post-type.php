<?php
/**
 * CPT definition: Parish Travels
 *
 * Custom post type for parish pilgrimages, trips, and travel events.
 * Uses WordPress 6.5+ Block Bindings API for editable meta fields.
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'post_type' => 'parish_travels',
	'feature'   => 'travels',
	'args'      => array(
		'labels'       => array(
			'name'                  => __( 'Travels', 'parish-core' ),
			'singular_name'         => __( 'Travel', 'parish-core' ),
			'add_new'               => __( 'Add New', 'parish-core' ),
			'add_new_item'          => __( 'Add New Travel', 'parish-core' ),
			'edit_item'             => __( 'Edit Travel', 'parish-core' ),
			'new_item'              => __( 'New Travel', 'parish-core' ),
			'view_item'             => __( 'View Travel', 'parish-core' ),
			'view_items'            => __( 'View Travels', 'parish-core' ),
			'search_items'          => __( 'Search Travels', 'parish-core' ),
			'not_found'             => __( 'No travels found', 'parish-core' ),
			'not_found_in_trash'    => __( 'No travels found in trash', 'parish-core' ),
			'all_items'             => __( 'All Travels', 'parish-core' ),
			'menu_name'             => __( 'Travels', 'parish-core' ),
			'archives'              => __( 'Travel Archives', 'parish-core' ),
			'attributes'            => __( 'Travel Attributes', 'parish-core' ),
			'insert_into_item'      => __( 'Insert into travel', 'parish-core' ),
			'uploaded_to_this_item' => __( 'Uploaded to this travel', 'parish-core' ),
			'featured_image'        => __( 'Travel Image', 'parish-core' ),
			'set_featured_image'    => __( 'Set travel image', 'parish-core' ),
			'remove_featured_image' => __( 'Remove travel image', 'parish-core' ),
			'use_featured_image'    => __( 'Use as travel image', 'parish-core' ),
		),
		'description'  => __( 'Parish pilgrimages, trips, and travel events.', 'parish-core' ),
		'menu_icon'    => 'dashicons-airplane',
		'menu_position' => 25,
		'show_in_menu' => 'parish-core',
		'show_in_rest' => true, // Required for Block Editor and Block Bindings.
		'rewrite'      => array(
			'slug'       => 'travels',
			'with_front' => false,
		),

		// Block template with bindings - allows editing bound meta in-place.
		'template'      => Parish_CPT_Templates::get_travels_template(),
		'template_lock' => 'insert', // Allow editing content but not adding/removing template blocks.

		// Supports - title is used for internal reference, rest comes from meta.
		'supports'      => array(
			'title',
			'editor',
			'thumbnail',
			'excerpt',
			'revisions',
			'custom-fields', // Required for Block Bindings to work with meta.
		),
	),
);
