<?php
/**
 * Block template for Mass Intention (parish_intention)
 *
 * Simple template for admin-only intention management.
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	// Intention details group.
	array(
		'core/group',
		array(
			'className' => 'intention-details',
			'layout'    => array(
				'type' => 'constrained',
			),
		),
		array(
			// Info paragraph.
			array(
				'core/paragraph',
				array(
					'content'   => '<strong>' . __( 'Mass Intention', 'parish-core' ) . '</strong>',
					'className' => 'intention-header',
				),
			),

			// Intention name (post title).
			array(
				'core/post-title',
				array(
					'level'       => 2,
					'placeholder' => __( 'Enter intention (e.g., "For the repose of John Smith")', 'parish-core' ),
				),
			),

			// Type indicator.
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Use the sidebar to set intention details: date, type, requestor, etc.', 'parish-core' ),
					'className'   => 'intention-instructions',
				),
			),
		),
	),
);
