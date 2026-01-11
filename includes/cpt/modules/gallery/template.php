<?php
/**
 * Block template: Gallery
 *
 * Uses WordPress 6.5+ Block Bindings API to bind core blocks to post meta.
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

	// Event Type (above title)
	array(
		'core/paragraph',
		array(
			'placeholder' => __( 'Event type (e.g., First Communion, Parish Festival)', 'parish-core' ),
			'metadata'    => array(
				'bindings' => array(
					'content' => array(
						'source' => 'parish/post-meta',
						'args'   => array( 'key' => 'parish_event_type' ),
					),
				),
			),
			'style'       => array(
				'spacing' => array(
					'margin' => array(
						'top'    => '0',
						'bottom' => '0',
					),
				),
			),
			'textColor'   => 'contrast-2',
			'fontSize'    => 'small',
		),
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
					'placeholder' => __( 'Brief description of the gallery...', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_description' ),
							),
						),
					),
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

	// Info Row - Date and Location
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
					// Date Column
					array(
						'core/column',
						array(),
						array(
							array(
								'core/columns',
								array(
									'style' => array(
										'spacing' => array(
											'blockGap' => array( 'left' => 'var:preset|spacing|10' ),
										),
									),
								),
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
									array(
										'core/column',
										array( 'width' => '66.66%' ),
										array(
											array(
												'core/heading',
												array(
													'level'     => 4,
													'textAlign' => 'left',
													'content'   => __( 'Date', 'parish-core' ),
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
													'placeholder' => __( 'Event date...', 'parish-core' ),
													'metadata'    => array(
														'bindings' => array(
															'content' => array(
																'source' => 'parish/post-meta',
																'args'   => array( 'key' => 'parish_event_date' ),
															),
														),
													),
													'style'       => array(
														'spacing' => array(
															'margin' => array( 'top' => '5px', 'bottom' => '5px' ),
														),
													),
													'fontSize'    => 'medium',
												),
											),
										),
									),
								),
							),
						),
					),
					// Location Column
					array(
						'core/column',
						array(),
						array(
							array(
								'core/columns',
								array(
									'style' => array(
										'spacing' => array(
											'blockGap' => array( 'left' => 'var:preset|spacing|10' ),
										),
									),
								),
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
													'content'   => __( 'Location', 'parish-core' ),
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
													'placeholder' => __( 'Event location...', 'parish-core' ),
													'metadata'    => array(
														'bindings' => array(
															'content' => array(
																'source' => 'parish/post-meta',
																'args'   => array( 'key' => 'parish_event_location' ),
															),
														),
													),
													'style'       => array(
														'spacing' => array(
															'margin' => array( 'top' => '5px', 'bottom' => '5px' ),
														),
													),
													'fontSize'    => 'medium',
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

	// Gallery Section
	array(
		'core/group',
		array(
			'metadata' => array( 'name' => 'Gallery' ),
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
					'content'   => '<strong>' . __( 'Photos', 'parish-core' ) . '</strong>',
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
			// Gallery block - users add their photos here
			array(
				'core/gallery',
				array(
					'linkTo'    => 'media',
					'columns'   => 3,
					'imageCrop' => true,
				),
			),
		),
	),

	// Photo Credits (optional)
	array(
		'core/group',
		array(
			'metadata' => array( 'name' => 'Photo Credits' ),
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
				'core/paragraph',
				array(
					'placeholder' => __( 'Photo credit: Photographer name', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_photographer_name' ),
							),
						),
					),
					'fontSize'    => 'small',
					'textColor'   => 'contrast-2',
				),
			),
		),
	),
);
