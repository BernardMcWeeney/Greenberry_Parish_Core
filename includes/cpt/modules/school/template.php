<?php
/**
 * Block template: School
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
			'className' => 'school-header',
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
					'aspectRatio' => '16/9',
					'width'       => '100%',
					'height'      => '400px',
				),
			),

			// School Name (uses post title)
			array(
				'core/post-title',
				array(
					'level'     => 1,
					'textAlign' => 'center',
				),
			),

			// School Type
			array(
				'core/paragraph',
				array(
					'align'       => 'center',
					'placeholder' => __( 'School type (e.g., Primary School, Secondary School)', 'parish-core' ),
					'style'       => array(
						'typography' => array(
							'fontSize' => '1.25rem',
						),
					),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_school_type' ),
							),
						),
					),
				),
			),

			// Grade Levels
			array(
				'core/paragraph',
				array(
					'align'       => 'center',
					'placeholder' => __( 'Grade levels (e.g., Junior Infants - 6th Class)', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_grade_levels' ),
							),
						),
					),
				),
			),
		),
	),

	// Staff Information
	array(
		'core/group',
		array(
			'className' => 'school-staff',
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
					'level'   => 2,
					'content' => __( 'Staff', 'parish-core' ),
				),
			),

			array(
				'core/columns',
				array(),
				array(
					// Column 1: Principal & Vice Principal
					array(
						'core/column',
						array(),
						array(
							// Principal
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Principal name', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_principal_name' ),
											),
										),
									),
								),
							),

							// Vice Principal
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Vice Principal name', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_vice_principal' ),
											),
										),
									),
								),
							),
						),
					),

					// Column 2: Chaplain
					array(
						'core/column',
						array(),
						array(
							// Chaplain
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Chaplain name', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_chaplain' ),
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

	// Contact Information
	array(
		'core/group',
		array(
			'className' => 'school-contact',
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
					'level'   => 2,
					'content' => __( 'Contact Information', 'parish-core' ),
				),
			),

			// Address
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'School address...', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_address' ),
							),
						),
					),
				),
			),

			// Phone
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Phone number', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_phone' ),
							),
						),
					),
				),
			),

			// Email
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Email address', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_email' ),
							),
						),
					),
				),
			),

			// Website & Enrollment buttons
			array(
				'core/buttons',
				array(),
				array(
					array(
						'core/button',
						array(
							'text'     => __( 'Visit Website', 'parish-core' ),
							'metadata' => array(
								'bindings' => array(
									'url' => array(
										'source' => 'parish/post-meta',
										'args'   => array( 'key' => 'parish_website' ),
									),
								),
							),
						),
					),
					array(
						'core/button',
						array(
							'text'     => __( 'Enrollment Info', 'parish-core' ),
							'metadata' => array(
								'bindings' => array(
									'url' => array(
										'source' => 'parish/post-meta',
										'args'   => array( 'key' => 'parish_enrollment_link' ),
									),
								),
							),
						),
					),
				),
			),
		),
	),

	// School Hours
	array(
		'core/group',
		array(
			'className' => 'school-hours',
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
					'content' => __( 'School Hours', 'parish-core' ),
				),
			),

			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'School hours (e.g., 9:00 AM - 2:30 PM)', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_school_hours' ),
							),
						),
					),
				),
			),
		),
	),

	// Additional Information (Collapsible)
	array(
		'core/details',
		array(
			'summary'     => __( 'Enrollment & Curriculum', 'parish-core' ),
			'showContent' => false,
		),
		array(
			array(
				'core/heading',
				array(
					'level'   => 3,
					'content' => __( 'Enrollment Information', 'parish-core' ),
				),
			),

			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Enrollment information...', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_enrollment_info' ),
							),
						),
					),
				),
			),

			array(
				'core/heading',
				array(
					'level'   => 3,
					'content' => __( 'Curriculum', 'parish-core' ),
				),
			),

			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Curriculum information...', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_curriculum_info' ),
							),
						),
					),
				),
			),
		),
	),

	// Facilities (Collapsible)
	array(
		'core/details',
		array(
			'summary'     => __( 'Facilities', 'parish-core' ),
			'showContent' => false,
		),
		array(
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Facilities information...', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_facilities' ),
							),
						),
					),
				),
			),
		),
	),

	// Social Media Links
	array(
		'core/group',
		array(
			'className' => 'school-social',
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
					'content' => __( 'Follow Us', 'parish-core' ),
				),
			),

			array(
				'core/buttons',
				array(),
				array(
					array(
						'core/button',
						array(
							'text'     => __( 'Facebook', 'parish-core' ),
							'metadata' => array(
								'bindings' => array(
									'url' => array(
										'source' => 'parish/post-meta',
										'args'   => array( 'key' => 'parish_facebook_url' ),
									),
								),
							),
						),
					),
					array(
						'core/button',
						array(
							'text'     => __( 'Twitter', 'parish-core' ),
							'metadata' => array(
								'bindings' => array(
									'url' => array(
										'source' => 'parish/post-meta',
										'args'   => array( 'key' => 'parish_twitter_url' ),
									),
								),
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
		'core/heading',
		array(
			'level'       => 2,
			'content'     => __( 'Additional Information', 'parish-core' ),
			'placeholder' => __( 'Add any additional content here...', 'parish-core' ),
		),
	),

	array(
		'core/paragraph',
		array(
			'placeholder' => __( 'This section is fully editable and not bound to structured data. Add photos, events, news, or other content.', 'parish-core' ),
		),
	),
);
