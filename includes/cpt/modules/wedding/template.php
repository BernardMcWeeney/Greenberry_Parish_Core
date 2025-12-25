<?php
/**
 * Block template: Wedding Notice
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
			'className' => 'wedding-notice-header',
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
					'aspectRatio' => '4/3',
					'width'       => '100%',
					'height'      => '400px',
				),
			),

			// Couple Names Header
			array(
				'core/columns',
				array(
					'style' => array(
						'spacing' => array(
							'padding' => array(
								'top' => '1.5rem',
							),
						),
					),
				),
				array(
					// Bride Name
					array(
						'core/column',
						array(),
						array(
							array(
								'core/heading',
								array(
									'level'       => 1,
									'textAlign'   => 'right',
									'placeholder' => __( 'Bride Name', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_bride_name' ),
											),
										),
									),
								),
							),
						),
					),

					// Ampersand
					array(
						'core/column',
						array( 'width' => '80px' ),
						array(
							array(
								'core/heading',
								array(
									'level'     => 1,
									'textAlign' => 'center',
									'content'   => '&',
								),
							),
						),
					),

					// Groom Name
					array(
						'core/column',
						array(),
						array(
							array(
								'core/heading',
								array(
									'level'       => 1,
									'textAlign'   => 'left',
									'placeholder' => __( 'Groom Name', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_groom_name' ),
											),
										),
									),
								),
							),
						),
					),
				),
			),

			// Wedding Date & Time
			array(
				'core/paragraph',
				array(
					'align'       => 'center',
					'placeholder' => __( 'Wedding date (e.g., 24 March 2025)', 'parish-core' ),
					'style'       => array(
						'typography' => array(
							'fontSize' => '1.25rem',
						),
					),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_wedding_date' ),
							),
						),
					),
				),
			),

			array(
				'core/paragraph',
				array(
					'align'       => 'center',
					'placeholder' => __( 'Wedding time (e.g., 2:00 PM)', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_wedding_time' ),
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
			'className' => 'wedding-notice-ceremony',
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
					// Column 1: Location & Celebrant
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

							// Church Name
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
												'args'   => array( 'key' => 'parish_celebrant_name' ),
											),
										),
									),
								),
							),
						),
					),

					// Column 2: Wedding Party
					array(
						'core/column',
						array(),
						array(
							array(
								'core/heading',
								array(
									'level'   => 3,
									'content' => __( 'Wedding Party', 'parish-core' ),
								),
							),

							// Best Man
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Best man', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_best_man' ),
											),
										),
									),
								),
							),

							// Bridesmaid
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Bridesmaid', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_bridesmaid' ),
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

	// Reception Details (Collapsible)
	array(
		'core/details',
		array(
			'summary'     => __( 'Reception Details', 'parish-core' ),
			'showContent' => false,
		),
		array(
			// Reception Venue
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Reception venue', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_reception_venue' ),
							),
						),
					),
				),
			),

			// Reception Address
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Reception address', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_reception_address' ),
							),
						),
					),
				),
			),

			// Reception Time
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Reception time', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_reception_time' ),
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
			'className' => 'wedding-notice-notes',
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
					'placeholder' => __( 'Any additional notes about the wedding...', 'parish-core' ),
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

	// Photos & Memories Section (Unlocked)
	array(
		'core/separator',
		array(),
	),

	array(
		'core/heading',
		array(
			'level'       => 2,
			'content'     => __( 'Photos & Memories', 'parish-core' ),
			'placeholder' => __( 'Add photos and memories from the wedding...', 'parish-core' ),
		),
	),

	array(
		'core/paragraph',
		array(
			'placeholder' => __( 'This section is fully editable and not bound to structured data. Add photos, quotes, or memories here.', 'parish-core' ),
		),
	),
);
