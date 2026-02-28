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
	// Breadcrumbs
	array(
		'gb/breadcrumbs',
		array(),
	),

	// Post Title
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
					'placeholder' => __( 'Brief description of the cemetery...', 'parish-core' ),
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

	// Info Grid Section (Address, Contact, Opening Hours, Established)
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
										),
									),
								),
							),
						),
					),

					// Contact Column
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
													'content'   => __( 'Contact', 'parish-core' ),
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

					// Opening Hours Column
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
																'iconName' => 'clock',
																'prefix'   => 'fas',
																'icon'     => array( 512, 512, null, null, 'M256 0a256 256 0 1 1 0 512A256 256 0 1 1 256 0zM232 120l0 136c0 8 4 15.5 10.7 20l96 64c11 7.4 25.9 4.4 33.3-6.7s4.4-25.9-6.7-33.3L280 243.2 280 120c0-13.3-10.7-24-24-24s-24 10.7-24 24z' ),
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
													'content'   => __( 'Opening Hours', 'parish-core' ),
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
													'placeholder' => __( 'Opening hours...', 'parish-core' ),
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
								),
							),
						),
					),

					// Established Column
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
													'content'   => __( 'Established', 'parish-core' ),
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
													'placeholder' => __( 'Year established...', 'parish-core' ),
													'metadata'    => array(
														'bindings' => array(
															'content' => array(
																'source' => 'parish/post-meta',
																'args'   => array( 'key' => 'parish_established_year' ),
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

	// Cemetery Events Section
	array(
		'core/group',
		array(
			'metadata' => array( 'name' => 'Cemetery Events' ),
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
				'core/group',
				array(
					'style'  => array(
						'spacing' => array( 'blockGap' => '0.5rem' ),
					),
					'layout' => array( 'type' => 'flex', 'flexWrap' => 'nowrap' ),
				),
				array(
					array(
						'font-awesome/icon',
						array(
							'iconLayers' => array(
								array(
									'iconDefinition' => array(
										'iconName' => 'clock',
										'prefix'   => 'fas',
										'icon'     => array( 512, 512, null, null, 'M256 0a256 256 0 1 1 0 512A256 256 0 1 1 256 0zM232 120l0 136c0 8 4 15.5 10.7 20l96 64c11 7.4 25.9 4.4 33.3-6.7s4.4-25.9-6.7-33.3L280 243.2 280 120c0-13.3-10.7-24-24-24s-24 10.7-24 24z' ),
									),
									'spin'           => false,
									'transform'      => null,
									'color'          => '#4a8391',
								),
							),
						),
					),
					array(
						'core/heading',
						array(
							'level'     => 3,
							'textAlign' => 'left',
							'content'   => '<strong>' . __( 'Cemetery Events', 'parish-core' ) . '</strong>',
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
					'style'  => array(
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
					'layout' => array( 'type' => 'constrained' ),
				),
				array(
					array(
						'parish/events',
						array(),
					),
				),
			),
		),
	),

	// History Section
	array(
		'core/group',
		array(
			'metadata' => array( 'name' => 'History' ),
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
					'content'   => '<strong>' . __( 'History', 'parish-core' ) . '</strong>',
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
					'placeholder' => __( 'Cemetery history and background information...', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_history' ),
							),
						),
					),
				),
			),
		),
	),

	// Facilities Section
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
					array( 'core/list-item', array( 'content' => __( 'Parking facilities', 'parish-core' ) ) ),
					array( 'core/list-item', array( 'content' => __( 'Water points', 'parish-core' ) ) ),
					array( 'core/list-item', array( 'content' => __( 'Waste disposal', 'parish-core' ) ) ),
					array( 'core/list-item', array( 'content' => __( 'Seating areas', 'parish-core' ) ) ),
				),
			),
		),
	),

	// Cemetery Regulations Section
	array(
		'core/group',
		array(
			'metadata' => array( 'name' => 'Cemetery Regulations' ),
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
					'content'   => '<strong>' . __( 'Cemetery Regulations', 'parish-core' ) . '</strong>',
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
					'placeholder' => __( 'Cemetery regulations and rules...', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_regulations' ),
							),
						),
					),
				),
			),
		),
	),

	// Cemetery Enquiries Callout
	array(
		'core/group',
		array(
			'metadata' => array( 'name' => 'Cemetery Enquiries' ),
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
					'content'   => '<strong>' . __( 'Cemetery Enquiries', 'parish-core' ) . '</strong>',
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
					'placeholder' => __( 'Information about cemetery enquiries...', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_enquiries_text' ),
							),
						),
					),
				),
			),
			array(
				'core/buttons',
				array(),
				array(
					array(
						'core/button',
						array(
							'text'     => __( 'Contact Us', 'parish-core' ),
							'metadata' => array(
								'bindings' => array(
									'url' => array(
										'source' => 'parish/post-meta',
										'args'   => array( 'key' => 'parish_contact_url' ),
									),
								),
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
