<?php
/**
 * Events Post Type Configuration
 *
 * @package ParishCore
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'post_type' => 'parish_event',
	'feature'   => 'events',
	'args'      => array(
		'label'               => __( 'Events', 'parish-core' ),
		'labels'              => array(
			'name'               => __( 'Events', 'parish-core' ),
			'singular_name'      => __( 'Event', 'parish-core' ),
			'add_new'            => __( 'Add New Event', 'parish-core' ),
			'add_new_item'       => __( 'Add New Event', 'parish-core' ),
			'edit_item'          => __( 'Edit Event', 'parish-core' ),
			'new_item'           => __( 'New Event', 'parish-core' ),
			'view_item'          => __( 'View Event', 'parish-core' ),
			'search_items'       => __( 'Search Events', 'parish-core' ),
			'not_found'          => __( 'No events found', 'parish-core' ),
			'not_found_in_trash' => __( 'No events found in trash', 'parish-core' ),
			'all_items'          => __( 'All Events', 'parish-core' ),
			'archives'           => __( 'Event Archives', 'parish-core' ),
			'insert_into_item'   => __( 'Insert into event', 'parish-core' ),
			'uploaded_to_this_item' => __( 'Uploaded to this event', 'parish-core' ),
			'filter_items_list'  => __( 'Filter events list', 'parish-core' ),
			'items_list_navigation' => __( 'Events list navigation', 'parish-core' ),
			'items_list'         => __( 'Events list', 'parish-core' ),
		),
		'public'              => true,
		'publicly_queryable'  => true,
		'show_ui'             => true,
		'show_in_menu'        => false,  // Will add custom admin page via React UI.
		'show_in_rest'        => true,
		'rest_base'           => 'events',
		'rest_controller_class' => 'WP_REST_Posts_Controller',
		'menu_icon'           => 'dashicons-calendar-alt',
		'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
		'has_archive'         => true,
		'rewrite'             => array(
			'slug'       => 'events',
			'with_front' => false,
		),
		'capability_type'     => 'post',
		'hierarchical'        => false,
		'delete_with_user'    => false,
		'can_export'          => true,
		'show_in_nav_menus'   => true,
		'exclude_from_search' => false,
		'map_meta_cap'        => true,
	),
);
