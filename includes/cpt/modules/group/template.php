<?php
/**
 * Block template: Parish Group
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
			'className' => 'group-header',
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
					'height'      => '300px',
				),
			),

			// Group Name (uses post title)
			array(
				'core/post-title',
				array(
					'level'     => 1,
					'textAlign' => 'center',
				),
			),

			// Group Type
			array(
				'core/paragraph',
				array(
					'align'       => 'center',
					'placeholder' => __( 'Group type (e.g., Youth Ministry, Prayer Group, Service)', 'parish-core' ),
					'style'       => array(
						'typography' => array(
							'fontSize' => '1.25rem',
						),
					),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_group_type' ),
							),
						),
					),
				),
			),

			// Age Range & Membership Status
			array(
				'core/paragraph',
				array(
					'align'       => 'center',
					'placeholder' => __( 'Age range (e.g., All Ages, 18-35)', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_age_range' ),
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
			'className' => 'group-description',
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
					'content' => __( 'About This Group', 'parish-core' ),
				),
			),

			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Describe this parish group...', 'parish-core' ),
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

	// Meeting Information
	array(
		'core/group',
		array(
			'className' => 'group-meeting',
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
					'content' => __( 'Meeting Information', 'parish-core' ),
				),
			),

			array(
				'core/columns',
				array(),
				array(
					// Column 1: Day & Time
					array(
						'core/column',
						array(),
						array(
							// Meeting Day
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Meeting day (e.g., Every Tuesday)', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_meeting_day' ),
											),
										),
									),
								),
							),

							// Meeting Time
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Meeting time (e.g., 7:00 PM)', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_meeting_time' ),
											),
										),
									),
								),
							),
						),
					),

					// Column 2: Location
					array(
						'core/column',
						array(),
						array(
							// Meeting Location
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Meeting location', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_meeting_location' ),
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
			'className' => 'group-contact',
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
					'content' => __( 'Contact', 'parish-core' ),
				),
			),

			// Coordinator
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Coordinator name', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_coordinator_name' ),
							),
						),
					),
				),
			),

			// Email
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Contact email', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_contact_email' ),
							),
						),
					),
				),
			),

			// Phone
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Contact phone', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_contact_phone' ),
							),
						),
					),
				),
			),

			// Registration button
			array(
				'core/buttons',
				array(),
				array(
					array(
						'core/button',
						array(
							'text'     => __( 'Join This Group', 'parish-core' ),
							'metadata' => array(
								'bindings' => array(
									'url' => array(
										'source' => 'parish/post-meta',
										'args'   => array( 'key' => 'parish_registration_link' ),
									),
								),
							),
						),
					),
				),
			),
		),
	),

	// Activities & Requirements (Collapsible)
	array(
		'core/details',
		array(
			'summary'     => __( 'Activities & Requirements', 'parish-core' ),
			'showContent' => false,
		),
		array(
			array(
				'core/heading',
				array(
					'level'   => 3,
					'content' => __( 'Activities', 'parish-core' ),
				),
			),
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Group activities...', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_activities' ),
							),
						),
					),
				),
			),

			array(
				'core/heading',
				array(
					'level'   => 3,
					'content' => __( 'Requirements', 'parish-core' ),
				),
			),
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Membership requirements...', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_requirements' ),
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
			'content'     => __( 'Photos & Updates', 'parish-core' ),
			'placeholder' => __( 'Add any additional content here...', 'parish-core' ),
		),
	),

	array(
		'core/paragraph',
		array(
			'placeholder' => __( 'This section is fully editable and not bound to structured data. Add photos, news, or other content.', 'parish-core' ),
		),
	),
);
