<?php
/**
 * Block template: Parish Travels
 *
 * Uses WordPress 6.5+ Block Bindings API to bind core blocks to post meta.
 * Editors can type directly into blocks; data syncs to/from meta fields.
 *
 * @link https://developer.wordpress.org/block-editor/reference-guides/block-api/block-bindings/
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	// ========================================
	// HEADER SECTION
	// ========================================
	array(
		'core/group',
		array(
			'className' => 'parish-travels-header',
			'layout'    => array( 'type' => 'constrained' ),
			'style'     => array(
				'spacing' => array(
					'padding' => array(
						'top'    => 'var(--wp--preset--spacing--50)',
						'bottom' => 'var(--wp--preset--spacing--50)',
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
					'align'       => 'wide',
					'style'       => array(
						'border' => array(
							'radius' => '8px',
						),
					),
				),
			),

			// Trip Title - bound to parish_travel_title
			array(
				'core/heading',
				array(
					'level'       => 1,
					'placeholder' => __( 'Enter trip title...', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_travel_title' ),
							),
						),
					),
				),
			),

			// Destination & Duration Row
			array(
				'core/columns',
				array( 'isStackedOnMobile' => true ),
				array(
					array(
						'core/column',
						array( 'width' => '50%' ),
						array(
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Destination (e.g., Lourdes, France)', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_destination' ),
											),
										),
									),
									'style'       => array(
										'typography' => array(
											'fontSize' => '1.25rem',
										),
									),
								),
							),
						),
					),
					array(
						'core/column',
						array( 'width' => '50%' ),
						array(
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Duration (e.g., 7 days / 6 nights)', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_duration' ),
											),
										),
									),
								),
							),
						),
					),
				),
			),

			// Trip Summary
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Brief summary of the trip...', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_travel_summary' ),
							),
						),
					),
					'style'       => array(
						'typography' => array(
							'fontSize' => '1.1rem',
						),
					),
				),
			),
		),
	),

	// ========================================
	// DATES & PRICING SECTION
	// ========================================
	array(
		'core/group',
		array(
			'className'       => 'parish-travels-details',
			'backgroundColor' => 'tertiary',
			'style'           => array(
				'spacing' => array(
					'padding' => array(
						'top'    => 'var(--wp--preset--spacing--40)',
						'bottom' => 'var(--wp--preset--spacing--40)',
						'left'   => 'var(--wp--preset--spacing--40)',
						'right'  => 'var(--wp--preset--spacing--40)',
					),
				),
				'border'  => array(
					'radius' => '8px',
				),
			),
		),
		array(
			array(
				'core/heading',
				array(
					'level'   => 2,
					'content' => __( 'Trip Details', 'parish-core' ),
				),
			),

			array(
				'core/columns',
				array(),
				array(
					// Left Column: Dates
					array(
						'core/column',
						array(),
						array(
							array(
								'core/heading',
								array(
									'level'   => 3,
									'content' => __( 'Dates', 'parish-core' ),
								),
							),
							array(
								'core/paragraph',
								array(
									'content' => '<strong>' . __( 'Departure:', 'parish-core' ) . '</strong>',
								),
							),
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Departure date', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_departure_date' ),
											),
										),
									),
								),
							),
							array(
								'core/paragraph',
								array(
									'content' => '<strong>' . __( 'Return:', 'parish-core' ) . '</strong>',
								),
							),
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Return date', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_return_date' ),
											),
										),
									),
								),
							),
						),
					),

					// Right Column: Pricing
					array(
						'core/column',
						array(),
						array(
							array(
								'core/heading',
								array(
									'level'   => 3,
									'content' => __( 'Pricing', 'parish-core' ),
								),
							),
							array(
								'core/paragraph',
								array(
									'content' => '<strong>' . __( 'Price:', 'parish-core' ) . '</strong>',
								),
							),
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Price (e.g., €1,250 per person)', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_price' ),
											),
										),
									),
									'style'       => array(
										'typography' => array(
											'fontSize'   => '1.5rem',
											'fontWeight' => '600',
										),
									),
								),
							),
							array(
								'core/paragraph',
								array(
									'content' => '<strong>' . __( 'Deposit:', 'parish-core' ) . '</strong>',
								),
							),
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Deposit amount', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_deposit_amount' ),
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

	// ========================================
	// WHAT'S INCLUDED SECTION
	// ========================================
	array(
		'core/group',
		array(
			'className' => 'parish-travels-inclusions',
			'style'     => array(
				'spacing' => array(
					'padding' => array(
						'top'    => 'var(--wp--preset--spacing--40)',
						'bottom' => 'var(--wp--preset--spacing--40)',
					),
				),
			),
		),
		array(
			array(
				'core/heading',
				array(
					'level'   => 2,
					'content' => __( 'What\'s Included', 'parish-core' ),
				),
			),
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'List what\'s included in the price (flights, accommodation, meals, guided tours, etc.)...', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_price_includes' ),
							),
						),
					),
				),
			),
			array(
				'core/heading',
				array(
					'level'   => 3,
					'content' => __( 'Not Included', 'parish-core' ),
				),
			),
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'List what\'s not included (travel insurance, personal expenses, tips, etc.)...', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_price_excludes' ),
							),
						),
					),
				),
			),
		),
	),

	// ========================================
	// SPIRITUAL FOCUS (for pilgrimages)
	// ========================================
	array(
		'core/group',
		array(
			'className'       => 'parish-travels-spiritual',
			'backgroundColor' => 'secondary',
			'style'           => array(
				'spacing' => array(
					'padding' => array(
						'top'    => 'var(--wp--preset--spacing--40)',
						'bottom' => 'var(--wp--preset--spacing--40)',
						'left'   => 'var(--wp--preset--spacing--40)',
						'right'  => 'var(--wp--preset--spacing--40)',
					),
				),
				'border'  => array(
					'radius' => '8px',
				),
			),
		),
		array(
			array(
				'core/heading',
				array(
					'level'   => 2,
					'content' => __( 'Spiritual Focus', 'parish-core' ),
				),
			),
			array(
				'core/paragraph',
				array(
					'content' => '<strong>' . __( 'Spiritual Director:', 'parish-core' ) . '</strong>',
				),
			),
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Name of spiritual director...', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_spiritual_director' ),
							),
						),
					),
				),
			),
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Describe the spiritual focus of this pilgrimage...', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_spiritual_focus' ),
							),
						),
					),
				),
			),
		),
	),

	// ========================================
	// ITINERARY HIGHLIGHTS
	// ========================================
	array(
		'core/group',
		array(
			'className' => 'parish-travels-itinerary',
			'style'     => array(
				'spacing' => array(
					'padding' => array(
						'top'    => 'var(--wp--preset--spacing--40)',
						'bottom' => 'var(--wp--preset--spacing--40)',
					),
				),
			),
		),
		array(
			array(
				'core/heading',
				array(
					'level'   => 2,
					'content' => __( 'Itinerary Highlights', 'parish-core' ),
				),
			),
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'List key itinerary highlights and places to visit...', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_itinerary_highlights' ),
							),
						),
					),
				),
			),
		),
	),

	// ========================================
	// LOGISTICS
	// ========================================
	array(
		'core/group',
		array(
			'className' => 'parish-travels-logistics',
			'style'     => array(
				'spacing' => array(
					'padding' => array(
						'top'    => 'var(--wp--preset--spacing--40)',
						'bottom' => 'var(--wp--preset--spacing--40)',
					),
				),
			),
		),
		array(
			array(
				'core/heading',
				array(
					'level'   => 2,
					'content' => __( 'Travel Details', 'parish-core' ),
				),
			),
			array(
				'core/columns',
				array(),
				array(
					array(
						'core/column',
						array(),
						array(
							array(
								'core/paragraph',
								array(
									'content' => '<strong>' . __( 'Departure Location:', 'parish-core' ) . '</strong>',
								),
							),
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Where the group meets for departure...', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_departure_location' ),
											),
										),
									),
								),
							),
							array(
								'core/paragraph',
								array(
									'content' => '<strong>' . __( 'Departure Time:', 'parish-core' ) . '</strong>',
								),
							),
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Departure time...', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_departure_time' ),
											),
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
									'content' => '<strong>' . __( 'Transport:', 'parish-core' ) . '</strong>',
								),
							),
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Mode of transport (e.g., Coach, Flight)...', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_transport_type' ),
											),
										),
									),
								),
							),
							array(
								'core/paragraph',
								array(
									'content' => '<strong>' . __( 'Accommodation:', 'parish-core' ) . '</strong>',
								),
							),
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Accommodation details...', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_accommodation' ),
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

	// ========================================
	// BOOKING & CONTACT
	// ========================================
	array(
		'core/group',
		array(
			'className'       => 'parish-travels-booking',
			'backgroundColor' => 'primary',
			'textColor'       => 'background',
			'style'           => array(
				'spacing' => array(
					'padding' => array(
						'top'    => 'var(--wp--preset--spacing--50)',
						'bottom' => 'var(--wp--preset--spacing--50)',
						'left'   => 'var(--wp--preset--spacing--40)',
						'right'  => 'var(--wp--preset--spacing--40)',
					),
				),
				'border'  => array(
					'radius' => '8px',
				),
			),
		),
		array(
			array(
				'core/heading',
				array(
					'level'   => 2,
					'content' => __( 'Book Your Place', 'parish-core' ),
				),
			),
			array(
				'core/columns',
				array(),
				array(
					array(
						'core/column',
						array(),
						array(
							array(
								'core/paragraph',
								array(
									'content' => '<strong>' . __( 'Contact:', 'parish-core' ) . '</strong>',
								),
							),
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Contact person name...', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_contact_name' ),
											),
										),
									),
								),
							),
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Phone number...', 'parish-core' ),
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
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Email address...', 'parish-core' ),
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
						),
					),
					array(
						'core/column',
						array(),
						array(
							array(
								'core/paragraph',
								array(
									'content' => '<strong>' . __( 'Payment Deadline:', 'parish-core' ) . '</strong>',
								),
							),
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Final payment deadline...', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_payment_deadline' ),
											),
										),
									),
								),
							),
							// Book Now Button
							array(
								'core/buttons',
								array( 'layout' => array( 'type' => 'flex' ) ),
								array(
									array(
										'core/button',
										array(
											'text'            => __( 'Book Now', 'parish-core' ),
											'backgroundColor' => 'background',
											'textColor'       => 'primary',
											'metadata'        => array(
												'bindings' => array(
													'url' => array(
														'source' => 'parish/post-meta',
														'args'   => array( 'key' => 'parish_booking_url' ),
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

	// ========================================
	// REQUIREMENTS & NOTES
	// ========================================
	array(
		'core/group',
		array(
			'className' => 'parish-travels-requirements',
			'style'     => array(
				'spacing' => array(
					'padding' => array(
						'top'    => 'var(--wp--preset--spacing--40)',
						'bottom' => 'var(--wp--preset--spacing--40)',
					),
				),
			),
		),
		array(
			array(
				'core/heading',
				array(
					'level'   => 2,
					'content' => __( 'Requirements', 'parish-core' ),
				),
			),
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'List any requirements (valid passport, visa, travel insurance, fitness level, etc.)...', 'parish-core' ),
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
			array(
				'core/heading',
				array(
					'level'   => 3,
					'content' => __( 'Additional Information', 'parish-core' ),
				),
			),
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Any additional notes or information...', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_additional_notes' ),
							),
						),
					),
				),
			),
		),
	),

	// ========================================
	// FULL DESCRIPTION (unlocked for rich editing)
	// ========================================
	array(
		'core/separator',
		array( 'className' => 'is-style-wide' ),
	),
	array(
		'core/heading',
		array(
			'level'   => 2,
			'content' => __( 'Full Trip Description', 'parish-core' ),
		),
	),
	array(
		'core/paragraph',
		array(
			'placeholder' => __( 'This section is fully editable. Add detailed descriptions, images, galleries, or any other content...', 'parish-core' ),
		),
	),
);
