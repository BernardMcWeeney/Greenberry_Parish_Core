<?php
/**
 * Block template: Clergy & Staff
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

	// Post Title (Name)
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
					'placeholder' => __( 'Brief description or title...', 'parish-core' ),
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

	// Main Content - Two Column Layout
	array(
		'core/columns',
		array(
			'style' => array(
				'spacing' => array(
					'margin'   => array(
						'top'    => 'var:preset|spacing|30',
						'bottom' => 'var:preset|spacing|30',
					),
					'blockGap' => array( 'left' => 'var:preset|spacing|40' ),
				),
			),
		),
		array(
			// Left Column - Photo
			array(
				'core/column',
				array( 'width' => '33.33%' ),
				array(
					array(
						'core/post-featured-image',
						array(
							'aspectRatio' => '3/4',
							'width'       => '100%',
							'height'      => 'auto',
							'style'       => array(
								'border' => array( 'radius' => '0px' ),
							),
						),
					),
				),
			),

			// Right Column - Info Grid
			array(
				'core/column',
				array( 'width' => '66.66%' ),
				array(
					// Role row
					array(
						'core/columns',
						array(
							'style' => array(
								'spacing' => array(
									'margin'   => array( 'bottom' => 'var:preset|spacing|20' ),
									'blockGap' => array( 'left' => 'var:preset|spacing|10' ),
								),
							),
						),
						array(
							array(
								'core/column',
								array( 'width' => '30px' ),
								array(
									array(
										'font-awesome/icon',
										array(
											'iconLayers' => array(
												array(
													'iconDefinition' => array(
														'iconName' => 'user-tie',
														'prefix'   => 'fas',
														'icon'     => array( 448, 512, null, null, 'M96 128a128 128 0 1 0 256 0A128 128 0 1 0 96 128zm94.5 200.2l18.6 31L175.8 483.1l-36-146.9c-2-8.1-9.8-13.4-17.9-11.3C51.9 342.4 0 405.8 0 481.3c0 17 13.8 30.7 30.7 30.7l131.7 0c0 0 0 0 .1 0l9.9 0 119.3 0 9.9 0c0 0 0 0 .1 0l131.7 0c17 0 30.7-13.8 30.7-30.7c0-75.5-51.9-138.9-121.9-156.4c-8.1-2-15.9 3.3-17.9 11.3l-36 146.9L254.9 359.2l18.6-31c6.4-10.7-1.3-24.2-13.7-24.2L224 304l-19.7 0c-12.4 0-20.1 13.6-13.7 24.2z' ),
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
								array(),
								array(
									array(
										'core/heading',
										array(
											'level'     => 4,
											'textAlign' => 'left',
											'content'   => __( 'Role', 'parish-core' ),
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
											'placeholder' => __( 'Parish Priest', 'parish-core' ),
											'metadata'    => array(
												'bindings' => array(
													'content' => array(
														'source' => 'parish/post-meta',
														'args'   => array( 'key' => 'parish_role' ),
													),
												),
											),
										),
									),
								),
							),
						),
					),

					// Years Served row
					array(
						'core/columns',
						array(
							'style' => array(
								'spacing' => array(
									'margin'   => array( 'bottom' => 'var:preset|spacing|20' ),
									'blockGap' => array( 'left' => 'var:preset|spacing|10' ),
								),
							),
						),
						array(
							array(
								'core/column',
								array( 'width' => '30px' ),
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
								array(),
								array(
									array(
										'core/heading',
										array(
											'level'     => 4,
											'textAlign' => 'left',
											'content'   => __( 'Years Served', 'parish-core' ),
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
											'placeholder' => __( '2015 - Present', 'parish-core' ),
											'metadata'    => array(
												'bindings' => array(
													'content' => array(
														'source' => 'parish/post-meta',
														'args'   => array( 'key' => 'parish_year_from' ),
													),
												),
											),
										),
									),
								),
							),
						),
					),

					// Email row
					array(
						'core/columns',
						array(
							'style' => array(
								'spacing' => array(
									'margin'   => array( 'bottom' => 'var:preset|spacing|20' ),
									'blockGap' => array( 'left' => 'var:preset|spacing|10' ),
								),
							),
						),
						array(
							array(
								'core/column',
								array( 'width' => '30px' ),
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
								array(),
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
											'placeholder' => __( 'email@parish.ie', 'parish-core' ),
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

					// Phone row
					array(
						'core/columns',
						array(
							'style' => array(
								'spacing' => array(
									'margin'   => array( 'bottom' => 'var:preset|spacing|20' ),
									'blockGap' => array( 'left' => 'var:preset|spacing|10' ),
								),
							),
						),
						array(
							array(
								'core/column',
								array( 'width' => '30px' ),
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
								array(),
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
											'placeholder' => __( '(01) 234 5678', 'parish-core' ),
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
		),
	),

	// Biography Section
	array(
		'core/group',
		array(
			'metadata' => array( 'name' => 'Biography' ),
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
					'content'   => '<strong>' . __( 'Biography', 'parish-core' ) . '</strong>',
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
					'placeholder' => __( 'Biography and background information about this member...', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_biography' ),
							),
						),
					),
				),
			),
		),
	),

	// Additional Information (manually editable)
	array(
		'core/group',
		array(
			'metadata' => array( 'name' => 'Additional Information' ),
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
					'content'   => '<strong>' . __( 'Additional Information', 'parish-core' ) . '</strong>',
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
					'placeholder' => __( 'Add any additional information, notable achievements, or memories here. This section is fully editable.', 'parish-core' ),
				),
			),
		),
	),
);
