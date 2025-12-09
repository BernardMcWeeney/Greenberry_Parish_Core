<?php
/**
 * Custom Post Types registration.
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Parish_CPT class.
 */
class Parish_CPT {

	private static ?Parish_CPT $instance = null;

	public static function instance(): Parish_CPT {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'register_post_types' ), 5 );
		add_action( 'init', array( $this, 'register_taxonomies' ), 5 );
	}

	/**
	 * Get common CPT arguments.
	 */
	private function get_common_args(): array {
		return array(
			'public'              => true,
			'show_ui'             => true,
			'show_in_rest'        => true, // Enables block editor + REST API.
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
		);
	}

	/**
	 * Register all CPTs.
	 */
	public function register_post_types(): void {
		// Death Notices.
		if ( Parish_Core::is_feature_enabled( 'death_notices' ) ) {
			register_post_type( 'parish_death_notice', array_merge( $this->get_common_args(), array(
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
				'menu_icon'    => 'dashicons-heart',
				'rewrite'      => array( 'slug' => 'death-notices', 'with_front' => false ),
				'show_in_menu' => 'parish-core',
				'template'     => Parish_CPT_Templates::get_death_notice_template(),
				'template_lock'=> 'all',
			)));
		}

		// Baptism Notices.
		if ( Parish_Core::is_feature_enabled( 'baptism_notices' ) ) {
			register_post_type( 'parish_baptism', array_merge( $this->get_common_args(), array(
				'labels' => array(
					'name'               => __( 'Baptism Notices', 'parish-core' ),
					'singular_name'      => __( 'Baptism Notice', 'parish-core' ),
					'add_new'            => __( 'Add New', 'parish-core' ),
					'add_new_item'       => __( 'Add New Baptism Notice', 'parish-core' ),
					'edit_item'          => __( 'Edit Baptism Notice', 'parish-core' ),
					'new_item'           => __( 'New Baptism Notice', 'parish-core' ),
					'view_item'          => __( 'View Baptism Notice', 'parish-core' ),
					'search_items'       => __( 'Search Baptism Notices', 'parish-core' ),
					'not_found'          => __( 'No baptism notices found', 'parish-core' ),
					'not_found_in_trash' => __( 'No baptism notices in trash', 'parish-core' ),
					'all_items'          => __( 'All Baptisms', 'parish-core' ),
					'menu_name'          => __( 'Baptisms', 'parish-core' ),
				),
				'menu_icon'    => 'dashicons-groups',
				'rewrite'      => array( 'slug' => 'baptisms', 'with_front' => false ),
				'show_in_menu' => 'parish-core',
				'template'     => Parish_CPT_Templates::get_baptism_template(),
				'template_lock'=> 'all',
			)));
		}

		// Wedding Notices.
		if ( Parish_Core::is_feature_enabled( 'wedding_notices' ) ) {
			register_post_type( 'parish_wedding', array_merge( $this->get_common_args(), array(
				'labels' => array(
					'name'               => __( 'Wedding Notices', 'parish-core' ),
					'singular_name'      => __( 'Wedding Notice', 'parish-core' ),
					'add_new'            => __( 'Add New', 'parish-core' ),
					'add_new_item'       => __( 'Add New Wedding Notice', 'parish-core' ),
					'edit_item'          => __( 'Edit Wedding Notice', 'parish-core' ),
					'new_item'           => __( 'New Wedding Notice', 'parish-core' ),
					'view_item'          => __( 'View Wedding Notice', 'parish-core' ),
					'search_items'       => __( 'Search Wedding Notices', 'parish-core' ),
					'not_found'          => __( 'No wedding notices found', 'parish-core' ),
					'not_found_in_trash' => __( 'No wedding notices in trash', 'parish-core' ),
					'all_items'          => __( 'All Weddings', 'parish-core' ),
					'menu_name'          => __( 'Weddings', 'parish-core' ),
				),
				'menu_icon'    => 'dashicons-heart',
				'rewrite'      => array( 'slug' => 'weddings', 'with_front' => false ),
				'show_in_menu' => 'parish-core',
				'template'     => Parish_CPT_Templates::get_wedding_template(),
				'template_lock'=> 'all',
			)));
		}

		// Churches.
		if ( Parish_Core::is_feature_enabled( 'churches' ) ) {
			register_post_type( 'parish_church', array_merge( $this->get_common_args(), array(
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
				'menu_icon'    => 'dashicons-building',
				'rewrite'      => array( 'slug' => 'churches', 'with_front' => false ),
				'show_in_menu' => 'parish-core',
				'template'     => Parish_CPT_Templates::get_church_template(),
				'template_lock'=> 'all',
			)));
		}

		// Schools.
		if ( Parish_Core::is_feature_enabled( 'schools' ) ) {
			register_post_type( 'parish_school', array_merge( $this->get_common_args(), array(
				'labels' => array(
					'name'               => __( 'Schools', 'parish-core' ),
					'singular_name'      => __( 'School', 'parish-core' ),
					'add_new'            => __( 'Add New', 'parish-core' ),
					'add_new_item'       => __( 'Add New School', 'parish-core' ),
					'edit_item'          => __( 'Edit School', 'parish-core' ),
					'new_item'           => __( 'New School', 'parish-core' ),
					'view_item'          => __( 'View School', 'parish-core' ),
					'search_items'       => __( 'Search Schools', 'parish-core' ),
					'not_found'          => __( 'No schools found', 'parish-core' ),
					'not_found_in_trash' => __( 'No schools in trash', 'parish-core' ),
					'all_items'          => __( 'All Schools', 'parish-core' ),
					'menu_name'          => __( 'Schools', 'parish-core' ),
				),
				'menu_icon'    => 'dashicons-welcome-learn-more',
				'rewrite'      => array( 'slug' => 'schools', 'with_front' => false ),
				'show_in_menu' => 'parish-core',
				'template'     => Parish_CPT_Templates::get_school_template(),
				'template_lock'=> 'all',
			)));
		}

		// Cemeteries.
		if ( Parish_Core::is_feature_enabled( 'cemeteries' ) ) {
			register_post_type( 'parish_cemetery', array_merge( $this->get_common_args(), array(
				'labels' => array(
					'name'               => __( 'Cemeteries', 'parish-core' ),
					'singular_name'      => __( 'Cemetery', 'parish-core' ),
					'add_new'            => __( 'Add New', 'parish-core' ),
					'add_new_item'       => __( 'Add New Cemetery', 'parish-core' ),
					'edit_item'          => __( 'Edit Cemetery', 'parish-core' ),
					'new_item'           => __( 'New Cemetery', 'parish-core' ),
					'view_item'          => __( 'View Cemetery', 'parish-core' ),
					'search_items'       => __( 'Search Cemeteries', 'parish-core' ),
					'not_found'          => __( 'No cemeteries found', 'parish-core' ),
					'not_found_in_trash' => __( 'No cemeteries in trash', 'parish-core' ),
					'all_items'          => __( 'All Cemeteries', 'parish-core' ),
					'menu_name'          => __( 'Cemeteries', 'parish-core' ),
				),
				'menu_icon'    => 'dashicons-location',
				'rewrite'      => array( 'slug' => 'cemeteries', 'with_front' => false ),
				'show_in_menu' => 'parish-core',
				'template'     => Parish_CPT_Templates::get_cemetery_template(),
				'template_lock'=> 'all',
			)));
		}

		// Parish Groups.
		if ( Parish_Core::is_feature_enabled( 'groups' ) ) {
			register_post_type( 'parish_group', array_merge( $this->get_common_args(), array(
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
				'menu_icon'    => 'dashicons-groups',
				'rewrite'      => array( 'slug' => 'groups', 'with_front' => false ),
				'show_in_menu' => 'parish-core',
				'template'     => Parish_CPT_Templates::get_group_template(),
				'template_lock'=> 'all',
			)));
		}

		// Newsletters.
		if ( Parish_Core::is_feature_enabled( 'newsletters' ) ) {
			register_post_type( 'parish_newsletter', array_merge( $this->get_common_args(), array(
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
				'menu_icon'    => 'dashicons-media-document',
				'rewrite'      => array( 'slug' => 'newsletters', 'with_front' => false ),
				'show_in_menu' => 'parish-core',
				// You had a custom supports here; we keep that.
				'supports'     => array( 'title', 'editor', 'thumbnail', 'revisions' ),
				'template'     => Parish_CPT_Templates::get_newsletter_template(),
				'template_lock'=> 'all',
			)));
		}

		// Parish News.
		if ( Parish_Core::is_feature_enabled( 'news' ) ) {
			register_post_type( 'parish_news', array_merge( $this->get_common_args(), array(
				'labels' => array(
					'name'               => __( 'Parish News', 'parish-core' ),
					'singular_name'      => __( 'News Item', 'parish-core' ),
					'add_new'            => __( 'Add New', 'parish-core' ),
					'add_new_item'       => __( 'Add New News', 'parish-core' ),
					'edit_item'          => __( 'Edit News', 'parish-core' ),
					'new_item'           => __( 'New News', 'parish-core' ),
					'view_item'          => __( 'View News', 'parish-core' ),
					'search_items'       => __( 'Search News', 'parish-core' ),
					'not_found'          => __( 'No news found', 'parish-core' ),
					'not_found_in_trash' => __( 'No news in trash', 'parish-core' ),
					'all_items'          => __( 'All News', 'parish-core' ),
					'menu_name'          => __( 'News', 'parish-core' ),
				),
				'menu_icon'    => 'dashicons-megaphone',
				'rewrite'      => array( 'slug' => 'parish-news', 'with_front' => false ),
				'show_in_menu' => 'parish-core',
				'template'     => Parish_CPT_Templates::get_news_template(),
				'template_lock'=> 'insert',
			)));
		}

		// Gallery.
		if ( Parish_Core::is_feature_enabled( 'gallery' ) ) {
			register_post_type( 'parish_gallery', array_merge( $this->get_common_args(), array(
				'labels' => array(
					'name'               => __( 'Gallery', 'parish-core' ),
					'singular_name'      => __( 'Gallery', 'parish-core' ),
					'add_new'            => __( 'Add New', 'parish-core' ),
					'add_new_item'       => __( 'Add New Gallery', 'parish-core' ),
					'edit_item'          => __( 'Edit Gallery', 'parish-core' ),
					'new_item'           => __( 'New Gallery', 'parish-core' ),
					'view_item'          => __( 'View Gallery', 'parish-core' ),
					'search_items'       => __( 'Search Galleries', 'parish-core' ),
					'not_found'          => __( 'No galleries found', 'parish-core' ),
					'not_found_in_trash' => __( 'No galleries in trash', 'parish-core' ),
					'all_items'          => __( 'All Galleries', 'parish-core' ),
					'menu_name'          => __( 'Gallery', 'parish-core' ),
				),
				'menu_icon'    => 'dashicons-format-gallery',
				'rewrite'      => array( 'slug' => 'gallery', 'with_front' => false ),
				'show_in_menu' => 'parish-core',
				'template'     => Parish_CPT_Templates::get_gallery_template(),
				'template_lock'=> 'all',
			)));
		}

		// Reflections - simple CPT.
		if ( Parish_Core::is_feature_enabled( 'reflections' ) ) {
			register_post_type( 'parish_reflection', array(
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
				'public'              => true,
				'show_ui'             => true,
				'show_in_rest'        => true,
				'has_archive'         => true,
				'publicly_queryable'  => true,
				'capability_type'     => 'post',
				'supports'            => array( 'title', 'editor' ),
				'menu_icon'           => 'dashicons-format-quote',
				'rewrite'             => array( 'slug' => 'reflections', 'with_front' => false ),
				'show_in_menu'        => 'parish-core',
				'template'            => Parish_CPT_Templates::get_reflection_template(),
				'template_lock'=> 'all',
			));
		}

		// Prayers Directory - CPT for parish prayers.
		if ( Parish_Core::is_feature_enabled( 'prayers' ) ) {
			register_post_type( 'parish_prayer', array(
				'labels' => array(
					'name'               => __( 'Prayers', 'parish-core' ),
					'singular_name'      => __( 'Prayer', 'parish-core' ),
					'add_new'            => __( 'Add New', 'parish-core' ),
					'add_new_item'       => __( 'Add New Prayer', 'parish-core' ),
					'edit_item'          => __( 'Edit Prayer', 'parish-core' ),
					'new_item'           => __( 'New Prayer', 'parish-core' ),
					'view_item'          => __( 'View Prayer', 'parish-core' ),
					'search_items'       => __( 'Search Prayers', 'parish-core' ),
					'not_found'          => __( 'No prayers found', 'parish-core' ),
					'not_found_in_trash' => __( 'No prayers in trash', 'parish-core' ),
					'all_items'          => __( 'All Prayers', 'parish-core' ),
					'menu_name'          => __( 'Prayers', 'parish-core' ),
				),
				'public'              => true,
				'show_ui'             => true,
				'show_in_rest'        => true,
				'has_archive'         => true,
				'publicly_queryable'  => true,
				'capability_type'     => 'post',
				'supports'            => array( 'title', 'editor', 'thumbnail' ),
				'menu_icon'           => 'dashicons-book-alt',
				'rewrite'             => array( 'slug' => 'prayers', 'with_front' => false ),
				'show_in_menu'        => 'parish-core',
				'template'            => Parish_CPT_Templates::get_prayer_template(),
				'template_lock'=> 'all',
			));
		}
	}

	/**
	 * Register taxonomies.
	 */
	public function register_taxonomies(): void {
		// Group Type.
		if ( Parish_Core::is_feature_enabled( 'groups' ) ) {
			register_taxonomy( 'parish_group_type', array( 'parish_group' ), array(
				'labels' => array(
					'name'          => __( 'Group Types', 'parish-core' ),
					'singular_name' => __( 'Group Type', 'parish-core' ),
					'add_new_item'  => __( 'Add New Group Type', 'parish-core' ),
					'edit_item'     => __( 'Edit Group Type', 'parish-core' ),
					'menu_name'     => __( 'Group Types', 'parish-core' ),
				),
				'hierarchical'      => true,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rewrite'           => array( 'slug' => 'group-type' ),
			));
		}
	}
}
