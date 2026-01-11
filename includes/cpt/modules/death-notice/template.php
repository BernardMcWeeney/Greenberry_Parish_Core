<?php
/**
 * Block template: Death Notice
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

	// Post Title (Full Name)
	array(
		'core/post-title',
		array(
			'level'    => 1,
			'fontSize' => 'x-large',
		),
	),

	// Address/Location Line
	array(
		'core/paragraph',
		array(
			'style'       => array(
				'spacing' => array(
					'margin' => array(
						'top'    => '0',
						'bottom' => 'var:preset|spacing|20',
					),
				),
			),
			'textColor'   => 'contrast-2',
			'placeholder' => __( 'Main Street, Duleek, Co. Meath', 'parish-core' ),
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

			// Right Column - Details
			array(
				'core/column',
				array( 'width' => '66.66%' ),
				array(
					// Died peacefully row
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
														'iconName' => 'cross',
														'prefix'   => 'fas',
														'icon'     => array( 384, 512, null, null, 'M176 0c-26.5 0-48 21.5-48 48l0 80-80 0c-26.5 0-48 21.5-48 48l0 32c0 26.5 21.5 48 48 48l80 0 0 208c0 26.5 21.5 48 48 48l32 0c26.5 0 48-21.5 48-48l0-208 80 0c26.5 0 48-21.5 48-48l0-32c0-26.5-21.5-48-48-48l-80 0 0-80c0-26.5-21.5-48-48-48L176 0z' ),
													),
													'spin'           => false,
													'transform'      => null,
													'style'          => array( 'fontSize' => '1em' ),
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
										'core/paragraph',
										array(
											'style'       => array(
												'spacing' => array(
													'margin' => array( 'top' => '0', 'bottom' => '0' ),
												),
											),
											'placeholder' => __( 'Died peacefully on January 5, 2026', 'parish-core' ),
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
									array(
										'core/paragraph',
										array(
											'style'       => array(
												'spacing' => array(
													'margin' => array( 'top' => '5px', 'bottom' => '0' ),
												),
											),
											'textColor'   => 'contrast-2',
											'placeholder' => __( 'Aged 78 years', 'parish-core' ),
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
								),
							),
						),
					),

					// Funeral Mass row
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
													'style'          => array( 'fontSize' => '1em' ),
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
											'content'   => __( 'Funeral Mass', 'parish-core' ),
											'style'     => array(
												'spacing'    => array(
													'margin' => array( 'top' => '0', 'bottom' => '5px' ),
												),
												'typography' => array(
													'fontWeight' => '600',
												),
											),
											'fontSize'  => 'medium',
										),
									),
									array(
										'core/paragraph',
										array(
											'style'       => array(
												'spacing' => array(
													'margin' => array( 'top' => '0', 'bottom' => '0' ),
												),
											),
											'placeholder' => __( 'January 8, 2026 at 11:00 AM', 'parish-core' ),
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
								),
							),
						),
					),

					// Church/Location row
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
														'iconName' => 'location-dot',
														'prefix'   => 'fas',
														'icon'     => array( 384, 512, null, null, 'M0 188.6C0 84.4 86 0 192 0S384 84.4 384 188.6c0 119.3-120.2 262.3-170.4 316.8-11.8 12.8-31.5 12.8-43.3 0-50.2-54.5-170.4-197.5-170.4-316.8zM192 256a64 64 0 1 0 0-128 64 64 0 1 0 0 128z' ),
													),
													'spin'           => false,
													'transform'      => null,
													'style'          => array( 'fontSize' => '1em' ),
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
										'core/paragraph',
										array(
											'style'       => array(
												'spacing'    => array(
													'margin' => array( 'top' => '0', 'bottom' => '0' ),
												),
												'typography' => array(
													'fontWeight' => '600',
												),
											),
											'placeholder' => __( "St. Patrick's Church, Duleek", 'parish-core' ),
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
									array(
										'core/paragraph',
										array(
											'style'       => array(
												'spacing' => array(
													'margin' => array( 'top' => '5px', 'bottom' => '0' ),
												),
											),
											'textColor'   => 'contrast-2',
											'placeholder' => __( 'Burial afterwards in Duleek Cemetery', 'parish-core' ),
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

					// View on RIP.ie Button
					array(
						'core/buttons',
						array(
							'style' => array(
								'spacing' => array(
									'margin' => array( 'top' => 'var:preset|spacing|20' ),
								),
							),
						),
						array(
							array(
								'core/button',
								array(
									'text'      => __( 'View on RIP.ie', 'parish-core' ),
									'className' => 'is-style-outline',
									'metadata'  => array(
										'bindings' => array(
											'url' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_rip_ie_url' ),
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

	// Reposing Section
	array(
		'core/group',
		array(
			'metadata' => array( 'name' => 'Reposing' ),
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
						array( 'width' => '30px' ),
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
					array(
						'core/column',
						array(),
						array(
							array(
								'core/heading',
								array(
									'level'     => 3,
									'textAlign' => 'left',
									'content'   => '<strong>' . __( 'Reposing', 'parish-core' ) . '</strong>',
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
					'placeholder' => __( 'Reposing at his residence on Tuesday, January 7th, from 4:00 PM to 8:00 PM with prayers at 7:00 PM. Removal on Wednesday morning to St. Patrick\'s Church for 11:00 AM Funeral Mass.', 'parish-core' ),
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
		),
	),

	// Family Section
	array(
		'core/group',
		array(
			'metadata' => array( 'name' => 'Family' ),
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
						array( 'width' => '30px' ),
						array(
							array(
								'font-awesome/icon',
								array(
									'iconLayers' => array(
										array(
											'iconDefinition' => array(
												'iconName' => 'heart',
												'prefix'   => 'fas',
												'icon'     => array( 512, 512, null, null, 'M47.6 300.4L228.3 469.1c7.5 7 17.4 10.9 27.7 10.9s20.2-3.9 27.7-10.9L464.4 300.4c30.4-28.3 47.6-68 47.6-109.5v-5.8c0-69.9-50.5-129.5-119.4-141C347 36.5 300.6 51.4 268 84L256 96 244 84c-32.6-32.6-79-47.5-124.6-39.9C50.5 55.6 0 115.2 0 185.1v5.8c0 41.5 17.2 81.2 47.6 109.5z' ),
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
									'level'     => 3,
									'textAlign' => 'left',
									'content'   => '<strong>' . __( 'Family', 'parish-core' ) . '</strong>',
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
					'placeholder' => __( 'Deeply regretted by his loving wife Kathleen, sons Michael and Seán, daughters Áine and Siobhán, grandchildren, great-grandchildren, sons-in-law, daughters-in-law, extended family, neighbours and friends.', 'parish-core' ),
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
		),
	),

	// Tribute Section
	array(
		'core/group',
		array(
			'metadata' => array( 'name' => 'Tribute' ),
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
					'content'   => '<strong>' . __( 'Tribute', 'parish-core' ) . '</strong>',
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
					'placeholder' => __( 'Patrick was a devoted family man, a lifelong farmer, and a pillar of the Duleek community. He served as a Eucharistic minister at St. Patrick\'s Church for over 30 years and was a founding member of the local GAA club. His gentle nature, warm smile, and willingness to help anyone in need will be fondly remembered by all who knew him.', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_tribute' ),
							),
						),
					),
				),
			),
			array(
				'core/quote',
				array(
					'style' => array(
						'spacing' => array(
							'margin' => array( 'top' => 'var:preset|spacing|20' ),
						),
					),
				),
				array(
					array(
						'core/paragraph',
						array(
							'style'       => array(
								'typography' => array(
									'fontStyle' => 'italic',
								),
							),
							'placeholder' => __( '"Eternal rest grant unto him, O Lord, and let perpetual light shine upon him. May his soul and the souls of all the faithful departed, through the mercy of God, rest in peace. Amen."', 'parish-core' ),
							'metadata'    => array(
								'bindings' => array(
									'content' => array(
										'source' => 'parish/post-meta',
										'args'   => array( 'key' => 'parish_prayer' ),
									),
								),
							),
						),
					),
				),
			),
		),
	),

	// Watch & Condolences Buttons
	array(
		'core/buttons',
		array(
			'layout' => array(
				'type'           => 'flex',
				'justifyContent' => 'center',
			),
			'style'  => array(
				'spacing' => array(
					'margin' => array(
						'top'    => 'var:preset|spacing|30',
						'bottom' => 'var:preset|spacing|30',
					),
				),
			),
		),
		array(
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
			array(
				'core/button',
				array(
					'text'      => __( 'Leave Condolences', 'parish-core' ),
					'className' => 'is-style-outline',
					'metadata'  => array(
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
);
