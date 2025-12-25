<?php
/**
 * Block template: Baptism Notice
 *
 * Uses WordPress 6.6+ Block Bindings API to bind core blocks to post meta.
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
			'className' => 'baptism-notice-header',
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
			// Featured Image
			array(
				'core/post-featured-image',
				array(
					'aspectRatio' => '3/4',
					'width'       => '300px',
					'height'      => '400px',
					'align'       => 'center',
				),
			),

			// Child Name (H1) - bound to parish_child_name
			array(
				'core/heading',
				array(
					'level'       => 1,
					'textAlign'   => 'center',
					'placeholder' => __( 'Child Name', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_child_name' ),
							),
						),
					),
				),
			),

			// Baptism Date
			array(
				'core/paragraph',
				array(
					'align'       => 'center',
					'placeholder' => __( 'Baptism date (e.g., 15 January 2025)', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_baptism_date' ),
							),
						),
					),
				),
			),
		),
	),

	// Ceremony Details
	array(
		'core/group',
		array(
			'className' => 'baptism-notice-ceremony',
			'style'     => array(
				'spacing' => array(
					'padding' => array(
						'top'    => '1.5rem',
						'bottom' => '1.5rem',
					),
				),
				'color'   => array(
					'background' => '#f9f9f9',
				),
			),
		),
		array(
			array(
				'core/heading',
				array(
					'level'   => 2,
					'content' => __( 'Ceremony Details', 'parish-core' ),
				),
			),

			array(
				'core/columns',
				array(),
				array(
					// Column 1: Family Info
					array(
						'core/column',
						array(),
						array(
							array(
								'core/heading',
								array(
									'level'   => 3,
									'content' => __( 'Family', 'parish-core' ),
								),
							),

							// Parents
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Parents names', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_parents_names' ),
											),
										),
									),
								),
							),

							// Godparents
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Godparents names', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_godparents' ),
											),
										),
									),
								),
							),
						),
					),

					// Column 2: Church & Celebrant
					array(
						'core/column',
						array(),
						array(
							array(
								'core/heading',
								array(
									'level'   => 3,
									'content' => __( 'Location', 'parish-core' ),
								),
							),

							// Church name
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Church name', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_church_name' ),
											),
										),
									),
								),
							),

							// Celebrant
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Celebrant name', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_celebrant' ),
											),
										),
									),
								),
							),
						),
					),
				),
			),
		),
	),

	// Additional Notes
	array(
		'core/group',
		array(
			'className' => 'baptism-notice-notes',
			'style'     => array(
				'spacing' => array(
					'padding' => array(
						'top'    => '1.5rem',
						'bottom' => '1.5rem',
					),
				),
			),
		),
		array(
			array(
				'core/heading',
				array(
					'level'   => 3,
					'content' => __( 'Additional Information', 'parish-core' ),
				),
			),

			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Any additional notes about the baptism...', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_notes' ),
							),
						),
					),
				),
			),
		),
	),

	// Tribute/Memories Section (Unlocked)
	array(
		'core/separator',
		array(),
	),

	array(
		'core/heading',
		array(
			'level'       => 2,
			'content'     => __( 'Photos & Memories', 'parish-core' ),
			'placeholder' => __( 'Add photos and memories from the baptism day...', 'parish-core' ),
		),
	),

	array(
		'core/paragraph',
		array(
			'placeholder' => __( 'This section is fully editable and not bound to structured data. Add photos, quotes, or memories here.', 'parish-core' ),
		),
	),
);
