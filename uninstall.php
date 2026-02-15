<?php
/**
 * Uninstall Parish Core
 *
 * @package ParishCore
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete options.
delete_option( 'parish_core_settings' );
delete_option( 'parish_slider_settings' );
delete_option( 'parish_core_db_version' );

// Delete readings-related options.
$readings_options = array(
	'daily_readings_content',
	'daily_readings_last_fetch',
	'sunday_homily_content',
	'sunday_homily_last_fetch',
	'saint_of_the_day_content',
	'saint_of_the_day_last_fetch',
	'next_sunday_reading_content',
	'next_sunday_reading_last_fetch',
	'next_sunday_reading_irish_content',
	'next_sunday_reading_irish_last_fetch',
	'daily_readings_irish_content',
	'daily_readings_irish_last_fetch',
	'mass_reading_details_content',
	'mass_reading_details_last_fetch',
	'feast_day_details_content',
	'feast_day_details_last_fetch',
);

foreach ( $readings_options as $option ) {
	delete_option( $option );
}

// Delete transients.
$transients = array(
	'daily_readings_cache',
	'sunday_homily_cache',
	'saint_of_the_day_cache',
	'next_sunday_reading_cache',
	'next_sunday_reading_irish_cache',
	'daily_readings_irish_cache',
	'mass_reading_details_cache',
	'feast_day_details_cache',
);

foreach ( $transients as $transient ) {
	delete_transient( $transient );
}

// Clear scheduled events.
wp_clear_scheduled_hook( 'parish_fetch_readings_cron' );

// Optionally delete all parish posts (uncomment if desired).
/*
$post_types = array(
	'parish_death_notice',
	'parish_baptism',
	'parish_wedding',
	'parish_church',
	'parish_school',
	'parish_cemetery',
	'parish_group',
	'parish_newsletter',
	'parish_news',
	'parish_gallery',
	'parish_reflection',
);

foreach ( $post_types as $post_type ) {
	$posts = get_posts( array(
		'post_type'      => $post_type,
		'posts_per_page' => -1,
		'post_status'    => 'any',
		'fields'         => 'ids',
	) );

	foreach ( $posts as $post_id ) {
		wp_delete_post( $post_id, true );
	}
}
*/
