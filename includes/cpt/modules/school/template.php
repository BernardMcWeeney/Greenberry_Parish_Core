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
	// Breadcrumbs
	array(
		'gb/breadcrumbs',
		array(),
	),

	// School Type Label
	array(
		'core/paragraph',
		array(
			'style'       => array(
				'spacing' => array(
					'margin' => array( 'top' => '0', 'bottom' => '0' ),
				),
			),
			'textColor'   => 'contrast-2',
			'fontSize'    => 'small',
			'placeholder' => __( 'School type (e.g., Primary School)', 'parish-core' ),
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

	// Post Title (School Name)
	array(
		'core/post-title',
		array(
			'level'    => 1,
			'fontSize' => 'x-large',
		),
	),

	// Opening Section
	array(
		'core/group',
		array(
			'metadata' => array( 'name' => 'Opening Section' ),
			'layout'   => array( 'type' => 'constrained' ),
		),
		array(
			// Description paragraph bound to meta
			array(
				'core/paragraph',
				array(
					'style'       => array(
						'elements' => array(
							'link' => array(
								'color' => array( 'text' => 'var:preset|color|contrast-2' ),
							),
						),
						'spacing'  => array(
							'margin' => array(
								'top'    => '0',
								'bottom' => '0',
							),
						),
					),
					'textColor'   => 'contrast-2',
					'fontFamily'  => 'system-serif',
					'placeholder' => __( 'Brief description of the school...', 'parish-core' ),
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

			// Separator
			array(
				'core/separator',
				array(
					'className'       => 'is-style-wide',
					'style'           => array(
						'spacing' => array(
							'margin' => array(
								'top'    => 'var:preset|spacing|30',
								'bottom' => 'var:preset|spacing|10',
							),
						),
					),
					'backgroundColor' => 'contrast-3',
				),
			),
		),
	),

	// Spacer
	array(
		'core/spacer',
		array(
			'height' => 'var:preset|spacing|20',
		),
	),

	// Featured Image
	array(
		'core/post-featured-image',
		array(
			'aspectRatio' => '16/9',
			'width'       => '100%',
			'height'      => '25vh',
			'style'       => array(
				'spacing' => array(
					'margin' => array( 'bottom' => 'var:preset|spacing|30' ),
				),
				'border'  => array( 'radius' => '0px' ),
			),
		),
	),

	// Info Grid Section - Row 1 (Address, Phone, Email)
	array(
		'core/group',
		array(
			'layout' => array( 'type' => 'constrained' ),
		),
		array(
			array(
				'core/columns',
				array(),
				array(
					// Address Column
					array(
						'core/column',
						array(),
						array(
							array(
								'core/columns',
								array(),
								array(
									// Icon Column
									array(
										'core/column',
										array(
											'width'  => '5%',
											'layout' => array( 'type' => 'default' ),
										),
										array(
											array(
												'font-awesome/icon',
												array(
													'iconLayers' => array(
														array(
															'iconDefinition' => array(
																'iconName' => 'location-dot',
																'prefix'   => 'fas',
																'icon'     => array( 384, 512, null, null, 'M0 188.6C0 84.4 86 0 192 0S384 84.4 384 188.6c0 119.3-120.2 262.3-170.4 316.8-11.8 12.8-31.5 12.8-43.3 0-50.2-54.5-170.4-197.5-170.4-316.8zM192 256a64 64 0 1 0 0-128 64 64 0 1 0 0 128z' ),
															),
															'spin'           => false,
															'transform'      => null,
															'style'          => array( 'fontSize' => '1.2em' ),
															'color'          => '#609fae',
														),
													),
												),
											),
										),
									),
									// Content Column
									array(
										'core/column',
										array( 'width' => '66.66%' ),
										array(
											array(
												'core/heading',
												array(
													'level'     => 4,
													'textAlign' => 'left',
													'content'   => __( 'Address', 'parish-core' ),
													'style'     => array(
														'spacing'    => array(
															'margin' => array( 'top' => '0', 'bottom' => '0' ),
														),
														'typography' => array(
															'fontStyle'  => 'normal',
															'fontWeight' => '500',
														),
													),
													'textColor' => 'contrast-2',
													'fontSize'  => 'small',
												),
											),
											array(
												'core/paragraph',
												array(
													'style'       => array(
														'spacing' => array(
															'margin' => array( 'top' => '5px', 'bottom' => '5px' ),
														),
													),
													'fontSize'    => 'medium',
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
										),
									),
								),
							),
						),
					),

					// Phone Column
					array(
						'core/column',
						array(),
						array(
							array(
								'core/columns',
								array(),
								array(
									// Icon Column
									array(
										'core/column',
										array(
											'width'  => '5%',
											'layout' => array( 'type' => 'default' ),
										),
										array(
											array(
												'font-awesome/icon',
												array(
													'iconLayers' => array(
														array(
															'iconDefinition' => array(
																'iconName' => 'phone',
																'prefix'   => 'fas',
																'icon'     => array( 512, 512, null, null, 'M164.9 24.6c-7.7-18.6-28-28.5-47.4-23.2l-88 24C12.1 30.2 0 46 0 64C0 311.4 200.6 512 448 512c18 0 33.8-12.1 38.6-29.5l24-88c5.3-19.4-4.6-39.7-23.2-47.4l-96-40c-16.3-6.8-35.2-2.1-46.3 11.6L304.7 368C234.3 334.7 177.3 277.7 144 207.3L193.3 167c13.7-11.2 18.4-30 11.6-46.3l-40-96z' ),
															),
															'spin'           => false,
															'transform'      => null,
															'style'          => array( 'fontSize' => '1.2em' ),
															'color'          => '#609fae',
														),
													),
												),
											),
										),
									),
									// Content Column
									array(
										'core/column',
										array( 'width' => '66.66%' ),
										array(
											array(
												'core/heading',
												array(
													'level'     => 4,
													'textAlign' => 'left',
													'content'   => __( 'Phone', 'parish-core' ),
													'style'     => array(
														'spacing'    => array(
															'margin' => array( 'top' => '0', 'bottom' => '0' ),
														),
														'typography' => array(
															'fontStyle'  => 'normal',
															'fontWeight' => '500',
														),
													),
													'textColor' => 'contrast-2',
													'fontSize'  => 'small',
												),
											),
											array(
												'core/paragraph',
												array(
													'style'       => array(
														'spacing' => array(
															'margin' => array( 'top' => '5px', 'bottom' => '5px' ),
														),
													),
													'fontSize'    => 'medium',
													'placeholder' => __( 'Phone number...', 'parish-core' ),
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
										),
									),
								),
							),
						),
					),

					// Email Column
					array(
						'core/column',
						array(),
						array(
							array(
								'core/columns',
								array(),
								array(
									// Icon Column
									array(
										'core/column',
										array(
											'width'  => '5%',
											'layout' => array( 'type' => 'default' ),
										),
										array(
											array(
												'font-awesome/icon',
												array(
													'iconLayers' => array(
														array(
															'iconDefinition' => array(
																'iconName' => 'envelope',
																'prefix'   => 'fas',
																'icon'     => array( 512, 512, null, null, 'M48 64C21.5 64 0 85.5 0 112c0 15.1 7.1 29.3 19.2 38.4L236.8 313.6c11.4 8.5 27 8.5 38.4 0L492.8 150.4c12.1-9.1 19.2-23.3 19.2-38.4c0-26.5-21.5-48-48-48L48 64zM0 176L0 384c0 35.3 28.7 64 64 64l384 0c35.3 0 64-28.7 64-64l0-208L294.4 339.2c-22.8 17.1-54 17.1-76.8 0L0 176z' ),
															),
															'spin'           => false,
															'transform'      => null,
															'style'          => array( 'fontSize' => '1.2em' ),
															'color'          => '#609fae',
														),
													),
												),
											),
										),
									),
									// Content Column
									array(
										'core/column',
										array( 'width' => '66.66%' ),
										array(
											array(
												'core/heading',
												array(
													'level'     => 4,
													'textAlign' => 'left',
													'content'   => __( 'Email', 'parish-core' ),
													'style'     => array(
														'spacing'    => array(
															'margin' => array( 'top' => '0', 'bottom' => '0' ),
														),
														'typography' => array(
															'fontStyle'  => 'normal',
															'fontWeight' => '500',
														),
													),
													'textColor' => 'contrast-2',
													'fontSize'  => 'small',
												),
											),
											array(
												'core/paragraph',
												array(
													'style'       => array(
														'spacing' => array(
															'margin' => array( 'top' => '5px', 'bottom' => '5px' ),
														),
													),
													'fontSize'    => 'medium',
													'placeholder' => __( 'Email address...', 'parish-core' ),
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
								),
							),
						),
					),
				),
			),
		),
	),

	// Info Grid Section - Row 2 (Website, Principal, Founded)
	array(
		'core/group',
		array(
			'layout' => array( 'type' => 'constrained' ),
		),
		array(
			array(
				'core/columns',
				array(),
				array(
					// Website Column
					array(
						'core/column',
						array(),
						array(
							array(
								'core/columns',
								array(),
								array(
									// Icon Column
									array(
										'core/column',
										array(
											'width'  => '5%',
											'layout' => array( 'type' => 'default' ),
										),
										array(
											array(
												'font-awesome/icon',
												array(
													'iconLayers' => array(
														array(
															'iconDefinition' => array(
																'iconName' => 'globe',
																'prefix'   => 'fas',
																'icon'     => array( 512, 512, null, null, 'M352 256c0 22.2-1.2 43.6-3.3 64l-185.3 0c-2.2-20.4-3.3-41.8-3.3-64s1.2-43.6 3.3-64l185.3 0c2.2 20.4 3.3 41.8 3.3 64zm28.8-64l123.1 0c5.3 20.5 8.1 41.9 8.1 64s-2.8 43.5-8.1 64l-123.1 0c2.1-20.6 3.2-42 3.2-64s-1.1-43.4-3.2-64zm112.6-32l-116.7 0c-10-63.9-29.8-117.4-55.3-151.6c78.3 20.7 142 77.5 171.9 151.6zm-149.1 0l-176.6 0c6.1-36.4 15.5-68.6 27-94.7c10.5-23.6 22.2-40.7 33.5-51.5C239.4 3.2 248.6 0 256 0s16.6 3.2 27.8 13.8c11.3 10.8 23 27.9 33.5 51.5c11.6 26 20.9 58.2 27 94.7zm-209 0L18.6 160C48.6 85.9 112.2 29.1 190.6 8.4C165.1 42.6 145.3 96.1 135.3 160zM8.1 192l123.1 0c-2.1 20.6-3.2 42-3.2 64s1.1 43.4 3.2 64L8.1 320C2.8 299.5 0 278.1 0 256s2.8-43.5 8.1-64zM194.7 446.6c-11.6-26-20.9-58.2-27-94.6l176.6 0c-6.1 36.4-15.5 68.6-27 94.6c-10.5 23.6-22.2 40.7-33.5 51.5C272.6 508.8 263.4 512 256 512s-16.6-3.2-27.8-13.8c-11.3-10.8-23-27.9-33.5-51.5zM135.3 352c10 63.9 29.8 117.4 55.3 151.6C112.2 482.9 48.6 426.1 18.6 352l116.7 0zm358.1 0c-30 74.1-93.6 130.9-171.9 151.6c25.5-34.2 45.2-87.7 55.3-151.6l116.7 0z' ),
															),
															'spin'           => false,
															'transform'      => null,
															'style'          => array( 'fontSize' => '1.2em' ),
															'color'          => '#609fae',
														),
													),
												),
											),
										),
									),
									// Content Column
									array(
										'core/column',
										array( 'width' => '66.66%' ),
										array(
											array(
												'core/heading',
												array(
													'level'     => 4,
													'textAlign' => 'left',
													'content'   => __( 'Website', 'parish-core' ),
													'style'     => array(
														'spacing'    => array(
															'margin' => array( 'top' => '0', 'bottom' => '0' ),
														),
														'typography' => array(
															'fontStyle'  => 'normal',
															'fontWeight' => '500',
														),
													),
													'textColor' => 'contrast-2',
													'fontSize'  => 'small',
												),
											),
											array(
												'core/paragraph',
												array(
													'style'       => array(
														'spacing' => array(
															'margin' => array( 'top' => '5px', 'bottom' => '5px' ),
														),
													),
													'fontSize'    => 'medium',
													'placeholder' => __( 'Website URL...', 'parish-core' ),
													'metadata'    => array(
														'bindings' => array(
															'content' => array(
																'source' => 'parish/post-meta',
																'args'   => array( 'key' => 'parish_website' ),
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

					// Principal Column
					array(
						'core/column',
						array(),
						array(
							array(
								'core/columns',
								array(),
								array(
									// Icon Column
									array(
										'core/column',
										array(
											'width'  => '5%',
											'layout' => array( 'type' => 'default' ),
										),
										array(
											array(
												'font-awesome/icon',
												array(
													'iconLayers' => array(
														array(
															'iconDefinition' => array(
																'iconName' => 'user-tie',
																'prefix'   => 'fas',
																'icon'     => array( 448, 512, null, null, 'M224 0c70.7 0 128 57.3 128 128s-57.3 128-128 128S96 198.7 96 128 153.3 0 224 0zM209.1 359.2l-18.6-31c-6.4-10.7 1.3-24.2 13.7-24.2l24 0 24.9 0c12.4 0 20.1 13.6 13.7 24.2l-18.6 31 33.4 123.9 39.5-161.2c6.5-26.5 30.1-44.9 57.4-44.9l18.4 0c35.3 0 64 28.7 64 64l0 176c0 17.7-14.3 32-32 32s-32-14.3-32-32l0-176-18.4 0-50.7 206.9c-8.3 33.8-55.2 33.8-63.5 0L260.6 360.5l-36.6 60.4c-12.8 21.2-43.3 21.2-56.1 0l-36.6-60.4L80.8 567.3c-8.3 33.8-55.2 33.8-63.5 0L-33.4 360.4-51.9 360.4 0 536l0 8c0 17.7-14.3 32-32 32s-32-14.3-32-32l0-176c0-35.3 28.7-64 64-64l18.4 0c27.3 0 50.9 18.4 57.4 44.9l39.5 161.2 33.4-123.9z' ),
															),
															'spin'           => false,
															'transform'      => null,
															'style'          => array( 'fontSize' => '1.2em' ),
															'color'          => '#609fae',
														),
													),
												),
											),
										),
									),
									// Content Column
									array(
										'core/column',
										array( 'width' => '66.66%' ),
										array(
											array(
												'core/heading',
												array(
													'level'     => 4,
													'textAlign' => 'left',
													'content'   => __( 'Principal', 'parish-core' ),
													'style'     => array(
														'spacing'    => array(
															'margin' => array( 'top' => '0', 'bottom' => '0' ),
														),
														'typography' => array(
															'fontStyle'  => 'normal',
															'fontWeight' => '500',
														),
													),
													'textColor' => 'contrast-2',
													'fontSize'  => 'small',
												),
											),
											array(
												'core/paragraph',
												array(
													'style'       => array(
														'spacing' => array(
															'margin' => array( 'top' => '5px', 'bottom' => '5px' ),
														),
													),
													'fontSize'    => 'medium',
													'placeholder' => __( 'Principal name...', 'parish-core' ),
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
										),
									),
								),
							),
						),
					),

					// Founded Column
					array(
						'core/column',
						array(),
						array(
							array(
								'core/columns',
								array(),
								array(
									// Icon Column
									array(
										'core/column',
										array(
											'width'  => '5%',
											'layout' => array( 'type' => 'default' ),
										),
										array(
											array(
												'font-awesome/icon',
												array(
													'iconLayers' => array(
														array(
															'iconDefinition' => array(
																'iconName' => 'calendar',
																'prefix'   => 'fas',
																'icon'     => array( 448, 512, null, null, 'M96 32l0 32L48 64C21.5 64 0 85.5 0 112l0 48 448 0 0-48c0-26.5-21.5-48-48-48l-48 0 0-32c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 32L160 64l0-32c0-17.7-14.3-32-32-32S96 14.3 96 32zM448 192L0 192 0 464c0 26.5 21.5 48 48 48l352 0c26.5 0 48-21.5 48-48l0-272z' ),
															),
															'spin'           => false,
															'transform'      => null,
															'style'          => array( 'fontSize' => '1.2em' ),
															'color'          => '#609fae',
														),
													),
												),
											),
										),
									),
									// Content Column
									array(
										'core/column',
										array( 'width' => '66.66%' ),
										array(
											array(
												'core/heading',
												array(
													'level'     => 4,
													'textAlign' => 'left',
													'content'   => __( 'Founded', 'parish-core' ),
													'style'     => array(
														'spacing'    => array(
															'margin' => array( 'top' => '0', 'bottom' => '0' ),
														),
														'typography' => array(
															'fontStyle'  => 'normal',
															'fontWeight' => '500',
														),
													),
													'textColor' => 'contrast-2',
													'fontSize'  => 'small',
												),
											),
											array(
												'core/paragraph',
												array(
													'style'       => array(
														'spacing' => array(
															'margin' => array( 'top' => '5px', 'bottom' => '5px' ),
														),
													),
													'fontSize'    => 'medium',
													'placeholder' => __( 'Year founded...', 'parish-core' ),
													'metadata'    => array(
														'bindings' => array(
															'content' => array(
																'source' => 'parish/post-meta',
																'args'   => array( 'key' => 'parish_founded_year' ),
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
		),
	),

	// About the School Section
	array(
		'core/group',
		array(
			'metadata' => array( 'name' => 'About the School' ),
			'style'    => array(
				'spacing' => array(
					'margin' => array(
						'top'    => 'var:preset|spacing|30',
						'bottom' => 'var:preset|spacing|30',
					),
				),
			),
			'layout'   => array( 'type' => 'constrained' ),
		),
		array(
			array(
				'core/heading',
				array(
					'level'     => 3,
					'textAlign' => 'left',
					'content'   => '<strong>' . __( 'About the School', 'parish-core' ) . '</strong>',
					'style'     => array(
						'spacing'    => array(
							'margin' => array( 'top' => '0', 'bottom' => '0' ),
						),
						'typography' => array(
							'fontStyle'  => 'normal',
							'fontWeight' => '500',
						),
						'elements'   => array(
							'link' => array(
								'color'  => array( 'text' => '#323232' ),
								':hover' => array(
									'color' => array( 'text' => 'var:preset|color|accent' ),
								),
							),
						),
						'color'      => array( 'text' => '#323232' ),
					),
					'fontSize'  => 'large',
				),
			),
			array(
				'core/separator',
				array(
					'className'       => 'is-style-wide',
					'backgroundColor' => 'contrast-3',
				),
			),
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'About the school, history, ethos, and mission...', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_about' ),
							),
						),
					),
				),
			),
		),
	),

	// School Times Section
	array(
		'core/group',
		array(
			'metadata' => array( 'name' => 'School Times' ),
			'style'    => array(
				'spacing' => array(
					'margin' => array(
						'top'    => 'var:preset|spacing|30',
						'bottom' => 'var:preset|spacing|30',
					),
				),
			),
			'layout'   => array( 'type' => 'constrained' ),
		),
		array(
			array(
				'core/heading',
				array(
					'level'     => 3,
					'textAlign' => 'left',
					'content'   => '<strong>' . __( 'School Times', 'parish-core' ) . '</strong>',
					'style'     => array(
						'spacing'    => array(
							'margin' => array( 'top' => '0', 'bottom' => '0' ),
						),
						'typography' => array(
							'fontStyle'  => 'normal',
							'fontWeight' => '500',
						),
						'elements'   => array(
							'link' => array(
								'color'  => array( 'text' => '#323232' ),
								':hover' => array(
									'color' => array( 'text' => 'var:preset|color|accent' ),
								),
							),
						),
						'color'      => array( 'text' => '#323232' ),
					),
					'fontSize'  => 'large',
				),
			),
			array(
				'core/separator',
				array(
					'className'       => 'is-style-wide',
					'backgroundColor' => 'contrast-3',
				),
			),
			array(
				'core/columns',
				array(),
				array(
					// Infants Column
					array(
						'core/column',
						array(
							'style' => array(
								'color'   => array( 'background' => '#fafafa' ),
								'spacing' => array(
									'padding' => array(
										'top'    => 'var:preset|spacing|10',
										'bottom' => 'var:preset|spacing|10',
										'left'   => 'var:preset|spacing|10',
										'right'  => 'var:preset|spacing|10',
									),
								),
							),
						),
						array(
							array(
								'core/heading',
								array(
									'level'     => 4,
									'content'   => __( 'Infants', 'parish-core' ),
									'textAlign' => 'center',
									'style'     => array(
										'spacing' => array(
											'margin' => array( 'top' => '0', 'bottom' => '0' ),
										),
									),
									'fontSize'  => 'medium',
								),
							),
							array(
								'core/paragraph',
								array(
									'align'       => 'center',
									'placeholder' => __( 'Infants hours...', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_infants_hours' ),
											),
										),
									),
								),
							),
						),
					),

					// 1st - 6th Class Column
					array(
						'core/column',
						array(
							'style' => array(
								'color'   => array( 'background' => '#fafafa' ),
								'spacing' => array(
									'padding' => array(
										'top'    => 'var:preset|spacing|10',
										'bottom' => 'var:preset|spacing|10',
										'left'   => 'var:preset|spacing|10',
										'right'  => 'var:preset|spacing|10',
									),
								),
							),
						),
						array(
							array(
								'core/heading',
								array(
									'level'     => 4,
									'content'   => __( '1st - 6th Class', 'parish-core' ),
									'textAlign' => 'center',
									'style'     => array(
										'spacing' => array(
											'margin' => array( 'top' => '0', 'bottom' => '0' ),
										),
									),
									'fontSize'  => 'medium',
								),
							),
							array(
								'core/paragraph',
								array(
									'align'       => 'center',
									'placeholder' => __( 'Primary hours...', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_primary_hours' ),
											),
										),
									),
								),
							),
						),
					),

					// Office Hours Column
					array(
						'core/column',
						array(
							'style' => array(
								'color'   => array( 'background' => '#fafafa' ),
								'spacing' => array(
									'padding' => array(
										'top'    => 'var:preset|spacing|10',
										'bottom' => 'var:preset|spacing|10',
										'left'   => 'var:preset|spacing|10',
										'right'  => 'var:preset|spacing|10',
									),
								),
							),
						),
						array(
							array(
								'core/heading',
								array(
									'level'     => 4,
									'content'   => __( 'Office Hours', 'parish-core' ),
									'textAlign' => 'center',
									'style'     => array(
										'spacing' => array(
											'margin' => array( 'top' => '0', 'bottom' => '0' ),
										),
									),
									'fontSize'  => 'medium',
								),
							),
							array(
								'core/paragraph',
								array(
									'align'       => 'center',
									'placeholder' => __( 'Office hours...', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_office_hours' ),
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

	// Facilities Section (manually editable)
	array(
		'core/group',
		array(
			'metadata' => array( 'name' => 'Facilities' ),
			'style'    => array(
				'spacing' => array(
					'margin' => array(
						'top'    => 'var:preset|spacing|30',
						'bottom' => 'var:preset|spacing|30',
					),
				),
			),
			'layout'   => array( 'type' => 'constrained' ),
		),
		array(
			array(
				'core/heading',
				array(
					'level'     => 3,
					'textAlign' => 'left',
					'content'   => '<strong>' . __( 'Facilities', 'parish-core' ) . '</strong>',
					'style'     => array(
						'spacing'    => array(
							'margin' => array( 'top' => '0', 'bottom' => '0' ),
						),
						'typography' => array(
							'fontStyle'  => 'normal',
							'fontWeight' => '500',
						),
						'elements'   => array(
							'link' => array(
								'color'  => array( 'text' => '#323232' ),
								':hover' => array(
									'color' => array( 'text' => 'var:preset|color|accent' ),
								),
							),
						),
						'color'      => array( 'text' => '#323232' ),
					),
					'fontSize'  => 'large',
				),
			),
			array(
				'core/separator',
				array(
					'className'       => 'is-style-wide',
					'backgroundColor' => 'contrast-3',
				),
			),
			array(
				'core/list',
				array(),
				array(
					array( 'core/list-item', array( 'content' => __( 'Modern classrooms', 'parish-core' ) ) ),
					array( 'core/list-item', array( 'content' => __( 'General purpose hall', 'parish-core' ) ) ),
					array( 'core/list-item', array( 'content' => __( 'Learning support rooms', 'parish-core' ) ) ),
					array( 'core/list-item', array( 'content' => __( 'Computer room', 'parish-core' ) ) ),
					array( 'core/list-item', array( 'content' => __( 'Library', 'parish-core' ) ) ),
					array( 'core/list-item', array( 'content' => __( 'Playground', 'parish-core' ) ) ),
				),
			),
		),
	),

	// Sacramental Preparation Section
	array(
		'core/group',
		array(
			'metadata' => array( 'name' => 'Sacramental Preparation' ),
			'style'    => array(
				'spacing' => array(
					'margin'  => array(
						'top'    => 'var:preset|spacing|30',
						'bottom' => 'var:preset|spacing|30',
					),
					'padding' => array(
						'top'    => 'var:preset|spacing|10',
						'right'  => 'var:preset|spacing|20',
						'bottom' => 'var:preset|spacing|10',
						'left'   => 'var:preset|spacing|20',
					),
				),
				'color'   => array( 'background' => '#f5f5f5' ),
				'border'  => array(
					'top'    => array( 'width' => '0px', 'style' => 'none' ),
					'right'  => array( 'width' => '0px', 'style' => 'none' ),
					'bottom' => array( 'width' => '0px', 'style' => 'none' ),
					'left'   => array( 'color' => 'var:preset|color|accent', 'width' => '5px' ),
				),
			),
			'layout'   => array( 'type' => 'constrained' ),
		),
		array(
			array(
				'core/heading',
				array(
					'level'     => 3,
					'textAlign' => 'left',
					'content'   => '<strong>' . __( 'Sacramental Preparation', 'parish-core' ) . '</strong>',
					'style'     => array(
						'spacing'    => array(
							'margin' => array( 'top' => '0', 'bottom' => '0' ),
						),
						'typography' => array(
							'fontStyle'  => 'normal',
							'fontWeight' => '500',
						),
						'elements'   => array(
							'link' => array(
								'color'  => array( 'text' => '#323232' ),
								':hover' => array(
									'color' => array( 'text' => 'var:preset|color|accent' ),
								),
							),
						),
						'color'      => array( 'text' => '#323232' ),
					),
					'fontSize'  => 'large',
				),
			),
			array(
				'core/paragraph',
				array(
					'style'       => array(
						'spacing' => array(
							'margin' => array(
								'right' => 'var:preset|spacing|20',
								'left'  => 'var:preset|spacing|20',
							),
						),
					),
					'textColor'   => 'contrast',
					'placeholder' => __( 'Information about sacramental preparation (First Communion, Confirmation)...', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_sacramental_prep' ),
							),
						),
					),
				),
			),
		),
	),

	// Find Us / Map Section
	array(
		'core/group',
		array(
			'metadata' => array( 'name' => 'Find Us' ),
			'style'    => array(
				'spacing' => array(
					'margin' => array(
						'top'    => 'var:preset|spacing|30',
						'bottom' => 'var:preset|spacing|30',
					),
				),
			),
			'layout'   => array( 'type' => 'constrained' ),
		),
		array(
			array(
				'core/heading',
				array(
					'level'     => 3,
					'textAlign' => 'left',
					'content'   => '<strong>' . __( 'Find Us', 'parish-core' ) . '</strong>',
					'style'     => array(
						'spacing'    => array(
							'margin' => array( 'top' => '0', 'bottom' => '0' ),
						),
						'typography' => array(
							'fontStyle'  => 'normal',
							'fontWeight' => '500',
						),
						'elements'   => array(
							'link' => array(
								'color'  => array( 'text' => '#323232' ),
								':hover' => array(
									'color' => array( 'text' => 'var:preset|color|accent' ),
								),
							),
						),
						'color'      => array( 'text' => '#323232' ),
					),
					'fontSize'  => 'large',
				),
			),
			array(
				'core/separator',
				array(
					'className'       => 'is-style-wide',
					'backgroundColor' => 'contrast-3',
				),
			),
			array(
				'core/group',
				array(
					'layout' => array( 'type' => 'constrained' ),
				),
				array(
					// Map embed placeholder - users will replace with actual HTML embed
					array(
						'core/html',
						array(
							'content' => '<iframe src="" width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>',
						),
					),
				),
			),
		),
	),
);
