<?php
/**
 * CPT: Death Notices
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'post_type' => 'parish_death_notice',
	'feature'   => 'death_notices',
	'args'      => array(
		'labels' => array(
			'name'               => __( 'Death Notices', 'parish-core' ),
			'singular_name'      => __( 'Death Notice', 'parish-core' ),
			'add_new'            => __( 'Add New', 'parish-core' ),
			'add_new_item'       => __( 'Add New Death Notice', 'parish-core' ),
			'edit_item'          => __( 'Edit Death Notice', 'parish-core' ),
			'new_item'           => __( 'New Death Notice', 'parish-core' ),
			'view_item'          => __( 'View Death Notice', 'parish-core' ),
			'search_items'       => __( 'Search Death Notices', 'parish-core' ),
			'not_found'          => __( 'No death notices found', 'parish-core' ),
			'not_found_in_trash' => __( 'No death notices in trash', 'parish-core' ),
			'all_items'          => __( 'All Death Notices', 'parish-core' ),
			'menu_name'          => __( 'Death Notices', 'parish-core' ),
		),
		'menu_icon'     => 'dashicons-heart',
		'rewrite'       => array( 'slug' => 'death-notices', 'with_front' => false ),
		'show_in_menu'  => 'parish-core',
		'show_in_rest'  => true, // Required for Gutenberg and Block Bindings
		'template'      => Parish_CPT_Templates::get_death_notice_template(),
		'template_lock' => 'insert', // Prevent adding/removing blocks, but allow editing
	),
);
