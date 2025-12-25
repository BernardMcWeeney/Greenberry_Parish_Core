<?php
/**
 * Block template: Gallery
 *
 * Uses WordPress 6.5+ Block Bindings API to bind core blocks to post meta.
 * Admins edit a designed document; blocks automatically sync to/from meta.
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	// Header Section
	array(
		'core/group',
		array(
			'className' => 'gallery-header',
			'style'     => array(
				'spacing' => array(
					'padding' => array(
						'top'    => '2rem',
						'bottom' => '2rem',
					),
				),
			),
		),
		array(
			// Gallery Title (uses post title)
			array(
				'core/post-title',
				array(
					'level'     => 1,
					'textAlign' => 'center',
				),
			),

			// Event Type & Date
			array(
				'core/columns',
				array(),
				array(
					array(
						'core/column',
						array(),
						array(
							// Event Type
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Event type (e.g., Mass, Festival, Sacrament)', 'parish-core' ),
									'style'       => array(
										'typography' => array(
											'fontWeight' => '600',
										),
									),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_event_type' ),
											),
										),
									),
								),
							),
						),
					),
					array(
						'core/column',
						array(),
						array(
							// Event Date
							array(
								'core/paragraph',
								array(
									'align'       => 'right',
									'placeholder' => __( 'Event date', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_event_date' ),
											),
										),
									),
								),
							),
						),
					),
				),
			),

			// Event Location
			array(
				'core/paragraph',
				array(
					'align'       => 'center',
					'placeholder' => __( 'Event location', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_event_location' ),
							),
						),
					),
				),
			),
		),
	),

	// Description
	array(
		'core/group',
		array(
			'className' => 'gallery-description',
			'style'     => array(
				'spacing' => array(
					'padding' => array(
						'top'    => '1rem',
						'bottom' => '1rem',
					),
				),
			),
		),
		array(
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Gallery description...', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_description' ),
							),
						),
					),
				),
			),
		),
	),

	// Gallery Block (unlocked - user adds their photos here)
	array(
		'core/gallery',
		array(
			'linkTo' => 'media',
		),
	),

	// Photographer Credit
	array(
		'core/group',
		array(
			'className' => 'gallery-credit',
			'style'     => array(
				'spacing' => array(
					'padding' => array(
						'top'    => '1.5rem',
						'bottom' => '1.5rem',
					),
				),
				'color'   => array(
					'background' => '#f5f5f5',
				),
			),
		),
		array(
			array(
				'core/heading',
				array(
					'level'   => 3,
					'content' => __( 'Photo Credits', 'parish-core' ),
				),
			),

			// Photographer
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Photographer name', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_photographer_name' ),
							),
						),
					),
				),
			),

			// Credit
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Photo credit / attribution', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_photographer_credit' ),
							),
						),
					),
				),
			),
		),
	),

	// Additional Content Section (Unlocked)
	array(
		'core/separator',
		array(),
	),

	array(
		'core/paragraph',
		array(
			'placeholder' => __( 'Add any additional notes or captions here...', 'parish-core' ),
		),
	),
);
