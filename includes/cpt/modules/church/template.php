<?php
/**
 * Block template: Church
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
					'placeholder' => __( 'Brief description of the church...', 'parish-core' ),
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

	// Spacer between Opening Section and About This Church
	array(
		'core/spacer',
		array(
			'height' => 'var:preset|spacing|20',
		),
	),

	// About This Church Section
	array(
		'core/group',
		array(
			'metadata' => array( 'name' => 'About This Church' ),
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
					'content'   => '<strong>' . __( 'About This Church', 'parish-core' ) . '</strong>',
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
					'placeholder' => __( 'History and background information about this church...', 'parish-core' ),
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
													'placeholder' => __( 'Church address...', 'parish-core' ),
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
																'args'   => array( 'key' => 'parish_opening_hours' ),
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

	// Mass Schedule Section
	array(
		'core/group',
		array(
			'metadata' => array( 'name' => 'Mass Schedule' ),
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
							'content'   => '<strong>' . __( 'Mass Schedule', 'parish-core' ) . '</strong>',
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
						'parish/church-schedule',
						array(
							'eventTypes' => array( 'mass', 'confession', 'adoration', 'rosary', 'other' ),
						),
					),
				),
			),
		),
	),

	// Church Events Section
	array(
		'core/group',
		array(
			'metadata' => array( 'name' => 'Church Events' ),
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
							'content'   => '<strong>' . __( 'Church Events', 'parish-core' ) . '</strong>',
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

	// Features & Facilities Section (manually editable)
	array(
		'core/group',
		array(
			'metadata' => array( 'name' => 'Features & Facilities' ),
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
					'content'   => '<strong>' . __( 'Features & Facilities', 'parish-core' ) . '</strong>',
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
					// Features Column
					array(
						'core/column',
						array(),
						array(
							array(
								'core/heading',
								array(
									'level'   => 4,
									'content' => __( 'Features', 'parish-core' ),
									'style'   => array(
										'typography' => array( 'fontSize' => '1.2rem' ),
									),
								),
							),
								array(
									'core/list',
									array(),
									array(
										array( 'core/list-item', array( 'content' => __( 'Wheelchair Accessible', 'parish-core' ) ) ),
										array( 'core/list-item', array( 'content' => __( 'Hearing Loop', 'parish-core' ) ) ),
										array( 'core/list-item', array( 'content' => __( 'Parking Available', 'parish-core' ) ) ),
									),
								),
						),
					),
					// Facilities Column
					array(
						'core/column',
						array(),
						array(
							array(
								'core/heading',
								array(
									'level'   => 4,
									'content' => __( 'Facilities', 'parish-core' ),
									'style'   => array(
										'typography' => array( 'fontSize' => '1.2rem' ),
									),
								),
							),
								array(
									'core/list',
									array(),
									array(
										array( 'core/list-item', array( 'content' => __( 'Sacristy', 'parish-core' ) ) ),
										array( 'core/list-item', array( 'content' => __( 'Toilet Facilities', 'parish-core' ) ) ),
										array( 'core/list-item', array( 'content' => __( 'Confession Room', 'parish-core' ) ) ),
									),
								),
						),
					),
				),
			),
		),
	),

	// Mass Intentions Callout
	array(
		'core/group',
		array(
			'metadata' => array( 'name' => 'Mass Intentions' ),
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
					'content'   => '<strong>' . __( 'Mass Intentions', 'parish-core' ) . '</strong>',
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
					'placeholder' => __( 'Information about booking Mass intentions...', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_mass_intentions_text' ),
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
							'text'     => __( 'Book Mass Intention', 'parish-core' ),
							'metadata' => array(
								'bindings' => array(
									'url' => array(
										'source' => 'parish/post-meta',
										'args'   => array( 'key' => 'parish_mass_intentions_url' ),
									),
								),
							),
						),
					),
				),
			),
		),
	),

	// Location & Directions Section
	array(
		'core/group',
		array(
			'metadata' => array( 'name' => 'Location & Directions' ),
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
					'content'   => '<strong>' . __( 'Location & Directions', 'parish-core' ) . '</strong>',
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
					array(
						'core/html',
						array(
							'content' => '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2365.667225271815!2d-6.754289222974414!3d53.63507415308049!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x48675b884854e84d%3A0xbd545982f70c7c2!2sSt%20Cuthbert&#39;s%20Church!5e0!3m2!1sen!2sie!4v1769025893111!5m2!1sen!2sie" width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>',
						),
					),
				),
			),
		),
	),
);
