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
					'placeholder' => __( 'Brief description of the group...', 'parish-core' ),
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

	// Info Grid (When, Where, Contact, Who)
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
					// When Column
					array(
						'core/column',
						array(),
						array(
							array(
								'core/columns',
								array(),
								array(
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
																'iconName' => 'calendar-days',
																'prefix'   => 'fas',
																'icon'     => array( 448, 512, null, null, 'M128 0c17.7 0 32 14.3 32 32l0 32 128 0 0-32c0-17.7 14.3-32 32-32s32 14.3 32 32l0 32 48 0c26.5 0 48 21.5 48 48l0 48L0 160l0-48C0 85.5 21.5 64 48 64l48 0 0-32c0-17.7 14.3-32 32-32zM0 192l448 0 0 272c0 26.5-21.5 48-48 48L48 512c-26.5 0-48-21.5-48-48L0 192zm64 80l0 32c0 8.8 7.2 16 16 16l32 0c8.8 0 16-7.2 16-16l0-32c0-8.8-7.2-16-16-16l-32 0c-8.8 0-16 7.2-16 16zm128 0l0 32c0 8.8 7.2 16 16 16l32 0c8.8 0 16-7.2 16-16l0-32c0-8.8-7.2-16-16-16l-32 0c-8.8 0-16 7.2-16 16zm144-16c-8.8 0-16 7.2-16 16l0 32c0 8.8 7.2 16 16 16l32 0c8.8 0 16-7.2 16-16l0-32c0-8.8-7.2-16-16-16l-32 0zM64 400l0 32c0 8.8 7.2 16 16 16l32 0c8.8 0 16-7.2 16-16l0-32c0-8.8-7.2-16-16-16l-32 0c-8.8 0-16 7.2-16 16zm128 0l0 32c0 8.8 7.2 16 16 16l32 0c8.8 0 16-7.2 16-16l0-32c0-8.8-7.2-16-16-16l-32 0c-8.8 0-16 7.2-16 16zm144-16c-8.8 0-16 7.2-16 16l0 32c0 8.8 7.2 16 16 16l32 0c8.8 0 16-7.2 16-16l0-32c0-8.8-7.2-16-16-16l-32 0z' ),
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
									array(
										'core/column',
										array( 'width' => '66.66%' ),
										array(
											array(
												'core/heading',
												array(
													'level'     => 4,
													'textAlign' => 'left',
													'content'   => __( 'When', 'parish-core' ),
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
													'placeholder' => __( 'Meeting schedule...', 'parish-core' ),
													'metadata'    => array(
														'bindings' => array(
															'content' => array(
																'source' => 'parish/post-meta',
																'args'   => array( 'key' => 'parish_meeting_schedule' ),
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

					// Where Column
					array(
						'core/column',
						array(),
						array(
							array(
								'core/columns',
								array(),
								array(
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
									array(
										'core/column',
										array( 'width' => '66.66%' ),
										array(
											array(
												'core/heading',
												array(
													'level'     => 4,
													'textAlign' => 'left',
													'content'   => __( 'Where', 'parish-core' ),
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
													'placeholder' => __( 'Meeting location...', 'parish-core' ),
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

					// Contact Column
					array(
						'core/column',
						array(),
						array(
							array(
								'core/columns',
								array(),
								array(
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
																'iconName' => 'user',
																'prefix'   => 'fas',
																'icon'     => array( 448, 512, null, null, 'M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512l388.6 0c16.4 0 29.7-13.3 29.7-29.7C448 383.8 368.2 304 269.7 304l-91.4 0z' ),
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
													'placeholder' => __( 'Contact person...', 'parish-core' ),
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
										),
									),
								),
							),
						),
					),

					// Who Column
					array(
						'core/column',
						array(),
						array(
							array(
								'core/columns',
								array(),
								array(
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
																'iconName' => 'users',
																'prefix'   => 'fas',
																'icon'     => array( 640, 512, null, null, 'M144 0a80 80 0 1 1 0 160A80 80 0 1 1 144 0zM512 0a80 80 0 1 1 0 160A80 80 0 1 1 512 0zM0 298.7C0 239.8 47.8 192 106.7 192l42.7 0c15.9 0 31 3.5 44.6 9.7c-1.3 7.2-1.9 14.7-1.9 22.3c0 38.2 16.8 72.5 43.3 96c-.2 0-.4 0-.7 0L21.3 320C9.6 320 0 310.4 0 298.7zM405.3 320c-.2 0-.4 0-.7 0c26.6-23.5 43.3-57.8 43.3-96c0-7.6-.7-15-1.9-22.3c13.6-6.3 28.7-9.7 44.6-9.7l42.7 0C592.2 192 640 239.8 640 298.7c0 11.8-9.6 21.3-21.3 21.3l-213.4 0zM224 224a96 96 0 1 1 192 0 96 96 0 1 1 -192 0zM128 485.3C128 411.7 187.7 352 261.3 352l117.4 0C452.3 352 512 411.7 512 485.3c0 14.7-11.9 26.7-26.7 26.7l-330.7 0c-14.7 0-26.7-11.9-26.7-26.7z' ),
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
									array(
										'core/column',
										array( 'width' => '66.66%' ),
										array(
											array(
												'core/heading',
												array(
													'level'     => 4,
													'textAlign' => 'left',
													'content'   => __( 'Who', 'parish-core' ),
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
													'placeholder' => __( 'Who can join...', 'parish-core' ),
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
								),
							),
						),
					),
				),
			),
		),
	),

	// About This Group Section
	array(
		'core/group',
		array(
			'metadata' => array( 'name' => 'About This Group' ),
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
					'content'   => '<strong>' . __( 'About This Group', 'parish-core' ) . '</strong>',
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
					'placeholder' => __( 'About this group, its purpose, history, and activities...', 'parish-core' ),
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

	// Activities Section (manually editable)
	array(
		'core/group',
		array(
			'metadata' => array( 'name' => 'Activities' ),
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
					'content'   => '<strong>' . __( 'Activities', 'parish-core' ) . '</strong>',
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
					array( 'core/list-item', array( 'content' => __( 'Weekly meetings', 'parish-core' ) ) ),
					array( 'core/list-item', array( 'content' => __( 'Special events', 'parish-core' ) ) ),
					array( 'core/list-item', array( 'content' => __( 'Community outreach', 'parish-core' ) ) ),
					array( 'core/list-item', array( 'content' => __( 'Spiritual formation', 'parish-core' ) ) ),
				),
			),
		),
	),

	// Join This Group Callout
	array(
		'core/group',
		array(
			'metadata' => array( 'name' => 'Join This Group' ),
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
					'content'   => '<strong>' . __( 'Join This Group', 'parish-core' ) . '</strong>',
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
					'placeholder' => __( 'Information about how to join this group...', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_join_info' ),
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
							'text'     => __( 'Get in Touch', 'parish-core' ),
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

	// Contact Details Section
	array(
		'core/group',
		array(
			'metadata' => array( 'name' => 'Contact Details' ),
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
					'content'   => '<strong>' . __( 'Contact Details', 'parish-core' ) . '</strong>',
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
					// Email Column
					array(
						'core/column',
						array(),
						array(
							array(
								'core/columns',
								array(),
								array(
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

					// Phone Column
					array(
						'core/column',
						array(),
						array(
							array(
								'core/columns',
								array(),
								array(
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

					// Website Column
					array(
						'core/column',
						array(),
						array(
							array(
								'core/columns',
								array(),
								array(
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
				),
			),
		),
	),
);
