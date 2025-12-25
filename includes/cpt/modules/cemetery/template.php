<?php
/**
 * Block template: Cemetery
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
			'className' => 'cemetery-header',
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

			// Cemetery Name (uses post title)
			array(
				'core/post-title',
				array(
					'level'     => 1,
					'textAlign' => 'center',
				),
			),
		),
	),

	// Location & Contact
	array(
		'core/group',
		array(
			'className' => 'cemetery-contact',
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
					'content' => __( 'Location & Contact', 'parish-core' ),
				),
			),

			array(
				'core/columns',
				array(),
				array(
					// Column 1: Address & Contact
					array(
						'core/column',
						array(),
						array(
							// Address
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Cemetery address...', 'parish-core' ),
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
						),
					),

					// Column 2: Manager & Map
					array(
						'core/column',
						array(),
						array(
							array(
								'core/heading',
								array(
									'level'   => 3,
									'content' => __( 'Cemetery Manager', 'parish-core' ),
								),
							),

							// Manager Name
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Manager name', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_manager_name' ),
											),
										),
									),
								),
							),

							// Manager Phone
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Manager phone', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_manager_phone' ),
											),
										),
									),
								),
							),

							// Map button
							array(
								'core/buttons',
								array(),
								array(
									array(
										'core/button',
										array(
											'text'     => __( 'Open in Google Maps', 'parish-core' ),
											'metadata' => array(
												'bindings' => array(
													'url' => array(
														'source' => 'parish/post-meta',
														'args'   => array( 'key' => 'parish_map_embed' ),
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
		),
	),

	// Visiting Hours
	array(
		'core/group',
		array(
			'className' => 'cemetery-visiting',
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
					'content' => __( 'Visiting Hours', 'parish-core' ),
				),
			),

			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Visiting hours and information...', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_visiting_hours' ),
							),
						),
					),
				),
			),
		),
	),

	// Policies (Collapsible)
	array(
		'core/details',
		array(
			'summary'     => __( 'Cemetery Policies', 'parish-core' ),
			'showContent' => false,
		),
		array(
			array(
				'core/heading',
				array(
					'level'   => 3,
					'content' => __( 'Burial Policy', 'parish-core' ),
				),
			),
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Burial policy details...', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_burial_policy' ),
							),
						),
					),
				),
			),

			array(
				'core/heading',
				array(
					'level'   => 3,
					'content' => __( 'Monument Policy', 'parish-core' ),
				),
			),
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Monument policy details...', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_monument_policy' ),
							),
						),
					),
				),
			),

			array(
				'core/heading',
				array(
					'level'   => 3,
					'content' => __( 'Maintenance Policy', 'parish-core' ),
				),
			),
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Maintenance policy details...', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_maintenance_policy' ),
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

	// Special Notices
	array(
		'core/group',
		array(
			'className' => 'cemetery-notices',
			'style'     => array(
				'spacing' => array(
					'padding' => array(
						'top'    => '1.5rem',
						'bottom' => '1.5rem',
					),
				),
				'color'   => array(
					'background' => '#fff8e6',
				),
			),
		),
		array(
			array(
				'core/heading',
				array(
					'level'   => 2,
					'content' => __( 'Special Notices', 'parish-core' ),
				),
			),

			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Special notices (e.g., Blessing of the Graves)...', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_special_notices' ),
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
			'placeholder' => __( 'This section is fully editable and not bound to structured data. Add photos, history, or other content.', 'parish-core' ),
		),
	),
);
