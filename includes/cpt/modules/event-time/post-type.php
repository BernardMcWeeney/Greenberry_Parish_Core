<?php
/**
 * Parish Event Time Custom Post Type.
 *
 * Stores Mass times, Confession times, Adoration, and other parish services.
 * Supports recurrence, livestreaming, intentions, and readings integration.
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'post_type' => 'parish_event_time',
	'feature'   => 'mass_times',
	'args'      => array(
		'labels'              => array(
			'name'                  => _x( 'Event Times', 'Post Type General Name', 'parish-core' ),
			'singular_name'         => _x( 'Event Time', 'Post Type Singular Name', 'parish-core' ),
			'menu_name'             => __( 'Event Times', 'parish-core' ),
			'name_admin_bar'        => __( 'Event Time', 'parish-core' ),
			'archives'              => __( 'Event Time Archives', 'parish-core' ),
			'attributes'            => __( 'Event Time Attributes', 'parish-core' ),
			'parent_item_colon'     => __( 'Parent Event Time:', 'parish-core' ),
			'all_items'             => __( 'All Event Times', 'parish-core' ),
			'add_new_item'          => __( 'Add New Event Time', 'parish-core' ),
			'add_new'               => __( 'Add New', 'parish-core' ),
			'new_item'              => __( 'New Event Time', 'parish-core' ),
			'edit_item'             => __( 'Edit Event Time', 'parish-core' ),
			'update_item'           => __( 'Update Event Time', 'parish-core' ),
			'view_item'             => __( 'View Event Time', 'parish-core' ),
			'view_items'            => __( 'View Event Times', 'parish-core' ),
			'search_items'          => __( 'Search Event Times', 'parish-core' ),
			'not_found'             => __( 'Not found', 'parish-core' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'parish-core' ),
			'featured_image'        => __( 'Featured Image', 'parish-core' ),
			'set_featured_image'    => __( 'Set featured image', 'parish-core' ),
			'remove_featured_image' => __( 'Remove featured image', 'parish-core' ),
			'use_featured_image'    => __( 'Use as featured image', 'parish-core' ),
			'insert_into_item'      => __( 'Insert into event time', 'parish-core' ),
			'uploaded_to_this_item' => __( 'Uploaded to this event time', 'parish-core' ),
			'items_list'            => __( 'Event Times list', 'parish-core' ),
			'items_list_navigation' => __( 'Event Times list navigation', 'parish-core' ),
			'filter_items_list'     => __( 'Filter event times list', 'parish-core' ),
		),
		'label'               => __( 'Event Time', 'parish-core' ),
		'description'         => __( 'Mass times, confessions, adoration, and other parish services', 'parish-core' ),
		'supports'            => array( 'title', 'custom-fields', 'revisions' ),
		'hierarchical'        => false,
		'public'              => false,
		'show_ui'             => false, // Managed via React admin
		'show_in_menu'        => false,
		'menu_position'       => 25,
		'menu_icon'           => 'dashicons-clock',
		'show_in_admin_bar'   => false,
		'show_in_nav_menus'   => false,
		'can_export'          => true,
		'has_archive'         => false,
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
		'capability_type'     => 'post',
		'show_in_rest'        => true,
		'rest_base'           => 'event-times',
		'rest_controller_class' => 'WP_REST_Posts_Controller',
	),
);
