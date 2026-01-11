<?php
/**
 * CPT: Newsletters
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'post_type' => 'parish_newsletter',
	'feature'   => 'newsletters',
	'args'      => array(
		'labels' => array(
			'name'               => __( 'Newsletters', 'parish-core' ),
			'singular_name'      => __( 'Newsletter', 'parish-core' ),
			'add_new'            => __( 'Add New', 'parish-core' ),
			'add_new_item'       => __( 'Add New Newsletter', 'parish-core' ),
			'edit_item'          => __( 'Edit Newsletter', 'parish-core' ),
			'new_item'           => __( 'New Newsletter', 'parish-core' ),
			'view_item'          => __( 'View Newsletter', 'parish-core' ),
			'search_items'       => __( 'Search Newsletters', 'parish-core' ),
			'not_found'          => __( 'No newsletters found', 'parish-core' ),
			'not_found_in_trash' => __( 'No newsletters in trash', 'parish-core' ),
			'all_items'          => __( 'All Newsletters', 'parish-core' ),
			'menu_name'          => __( 'Newsletters', 'parish-core' ),
		),
		'menu_icon'     => 'dashicons-media-document',
		'rewrite'       => array( 'slug' => 'newsletters', 'with_front' => false ),
		'show_in_menu'  => 'parish-core',

		// Override common supports: you said you want this custom set.
		// Must include 'custom-fields' for Block Bindings meta to save.
		'supports'      => array( 'title', 'editor', 'thumbnail', 'revisions', 'custom-fields' ),

		'template'      => Parish_CPT_Templates::get_newsletter_template(),
		'template_lock' => 'all',
	),
);
