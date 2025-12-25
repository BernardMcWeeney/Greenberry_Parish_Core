<?php
/**
 * Block template: Death Notice
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
			'className' => 'death-notice-header',
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

			// Full Name (H1) - bound to parish_full_name
			array(
				'core/heading',
				array(
					'level'      => 1,
					'textAlign'  => 'center',
					'placeholder' => __( 'Full Name of Deceased', 'parish-core' ),
					'metadata'   => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_full_name' ),
							),
						),
					),
				),
			),

			// Summary line - bound to parish_notice_summary
			array(
				'core/paragraph',
				array(
					'align'       => 'center',
					'placeholder' => __( 'e.g., Late of Main Street, Greenberry', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_notice_summary' ),
							),
						),
					),
				),
			),
		),
	),

	// Details Grid
	array(
		'core/group',
		array(
			'className' => 'death-notice-details',
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
				'core/columns',
				array(),
				array(
					// Column 1: Core Details
					array(
						'core/column',
						array(),
						array(
							array(
								'core/heading',
								array(
									'level'   => 3,
									'content' => __( 'Details', 'parish-core' ),
								),
							),

							// Date of death
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Date of death (e.g., 15 January 2025)', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_date_of_death' ),
											),
										),
									),
								),
							),

							// Age
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Age (e.g., 82 years)', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_age' ),
											),
										),
									),
								),
							),

							// Residence
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Residence', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_residence' ),
											),
										),
									),
								),
							),

							// Parish
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Parish', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_parish' ),
											),
										),
									),
								),
							),
						),
					),

					// Column 2: Arrangements
					array(
						'core/column',
						array(),
						array(
							array(
								'core/heading',
								array(
									'level'   => 3,
									'content' => __( 'Funeral Arrangements', 'parish-core' ),
								),
							),

							// Reposing location
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Reposing at...', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_reposing_location' ),
											),
										),
									),
								),
							),

							// Reposing times
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'From (time)', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_reposing_start' ),
											),
										),
									),
								),
							),
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'To (time)', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_reposing_end' ),
											),
										),
									),
								),
							),

							// Removal details
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Removal details', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_removal_details' ),
											),
										),
									),
								),
							),

							// Funeral Mass location
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Funeral Mass at...', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_funeral_mass_location' ),
											),
										),
									),
								),
							),

							// Funeral Mass datetime
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Date and time of Funeral Mass', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_funeral_mass_datetime' ),
											),
										),
									),
								),
							),

							// Burial/Cremation location
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Burial/Cremation location', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_burial_cremation_location' ),
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

	// Actions (Buttons)
	array(
		'core/buttons',
		array(
			'align' => 'center',
		),
		array(
			// Watch online button - bound to stream_url
			array(
				'core/button',
				array(
					'text'     => __( 'Watch Funeral Online', 'parish-core' ),
					'metadata' => array(
						'bindings' => array(
							'url' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_stream_url' ),
							),
						),
					),
				),
			),

			// Leave condolences button - bound to condolences_url
			array(
				'core/button',
				array(
					'text'     => __( 'Leave Condolences', 'parish-core' ),
					'metadata' => array(
						'bindings' => array(
							'url' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_condolences_url' ),
							),
						),
					),
				),
			),
		),
	),

	// Family Notice Section
	array(
		'core/group',
		array(
			'className' => 'death-notice-family',
			'style'     => array(
				'spacing' => array(
					'padding' => array(
						'top'    => '2rem',
						'bottom' => '1rem',
					),
				),
			),
		),
		array(
			array(
				'core/heading',
				array(
					'level'   => 3,
					'content' => __( 'Family Notice', 'parish-core' ),
				),
			),

			// Family message - bound to family_notice
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Family message...', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_family_notice' ),
							),
						),
					),
				),
			),

			// Donations in lieu - bound to donation_in_lieu
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Donations in lieu of flowers...', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_donation_in_lieu' ),
							),
						),
					),
				),
			),
		),
	),

	// Tribute Section (Unlocked - free content)
	array(
		'core/separator',
		array(),
	),

	array(
		'core/heading',
		array(
			'level'   => 3,
			'content' => __( 'Tribute & Memories', 'parish-core' ),
		),
	),

	array(
		'core/paragraph',
		array(
			'placeholder' => __( 'Add photos, quotes, memories, or other tribute content here. This section is fully editable and not bound to structured data.', 'parish-core' ),
		),
	),
);
