<?php
/**
 * Block template: Church
 *
 * Uses WordPress 6.6+ Block Bindings API to bind core blocks to post meta.
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
			'className' => 'church-header',
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

			// Church Name (uses post title)
			array(
				'core/post-title',
				array(
					'level'     => 1,
					'textAlign' => 'center',
				),
			),

			// Parish Priest
			array(
				'core/paragraph',
				array(
					'align'       => 'center',
					'placeholder' => __( 'Parish Priest name', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_parish_priest' ),
							),
						),
					),
				),
			),

			// Description
			array(
				'core/paragraph',
				array(
					'align'       => 'center',
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
		),
	),

	// Contact Information Panel
	array(
		'core/group',
		array(
			'className' => 'church-contact',
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
					'content' => __( 'Contact Information', 'parish-core' ),
				),
			),

			// Address
			array(
				'core/paragraph',
				array(
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

			// Website button
			array(
				'core/buttons',
				array(),
				array(
					array(
						'core/button',
						array(
							'text'     => __( 'Visit Website', 'parish-core' ),
							'metadata' => array(
								'bindings' => array(
									'url' => array(
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

	// Schedule Section (Dynamic - uses schedule templates)
	array(
		'core/group',
		array(
			'className' => 'church-schedule-section',
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
					'content' => __( 'Mass & Confession Schedule', 'parish-core' ),
				),
			),

			// Dynamic schedule block - renders from schedule templates.
			array(
				'parish/church-schedule',
				array(
					'format'       => 'list',
					'eventTypes'   => array( 'mass', 'confession' ),
					'showFeastDay' => true,
					'days'         => 7,
				),
			),
		),
	),

	// Legacy Mass Times (hidden by default, for backward compatibility)
	array(
		'core/group',
		array(
			'className' => 'church-mass-times church-legacy-schedule',
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
					'level'   => 3,
					'content' => __( 'Mass Times (Legacy)', 'parish-core' ),
				),
			),

			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Mass schedule (for backward compatibility - use Schedule Templates instead)...', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_mass_times' ),
							),
						),
					),
				),
			),
		),
	),

	// Legacy Confession Times (hidden by default, for backward compatibility)
	array(
		'core/group',
		array(
			'className' => 'church-confession-times church-legacy-schedule',
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
					'level'   => 3,
					'content' => __( 'Confession Times (Legacy)', 'parish-core' ),
				),
			),

			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Confession schedule (for backward compatibility)...', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_confession_times' ),
							),
						),
					),
				),
			),
		),
	),

	// Accessibility & Parking (Collapsible)
	array(
		'core/details',
		array(
			'summary'     => __( 'Accessibility & Parking', 'parish-core' ),
			'showContent' => false,
		),
		array(
			array(
				'core/heading',
				array(
					'level'   => 3,
					'content' => __( 'Accessibility', 'parish-core' ),
				),
			),

			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Accessibility information...', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_accessibility_info' ),
							),
						),
					),
				),
			),

			array(
				'core/heading',
				array(
					'level'   => 3,
					'content' => __( 'Parking', 'parish-core' ),
				),
			),

			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Parking information...', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_parking_info' ),
							),
						),
					),
				),
			),
		),
	),

	// Map Embed
	array(
		'core/group',
		array(
			'className' => 'church-map',
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
			array(
				'core/heading',
				array(
					'level'   => 2,
					'content' => __( 'Location', 'parish-core' ),
				),
			),

			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Add Google Maps embed URL here (e.g., https://www.google.com/maps/embed?pb=...)', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_map_embed' ),
							),
						),
					),
				),
			),
		),
	),

	// Social Media Links
	array(
		'core/group',
		array(
			'className' => 'church-social',
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
					'level'   => 3,
					'content' => __( 'Follow Us', 'parish-core' ),
				),
			),

			array(
				'core/buttons',
				array(),
				array(
					array(
						'core/button',
						array(
							'text'     => __( 'Facebook', 'parish-core' ),
							'metadata' => array(
								'bindings' => array(
									'url' => array(
										'source' => 'parish/post-meta',
										'args'   => array( 'key' => 'parish_facebook_url' ),
									),
								),
							),
						),
					),
					array(
						'core/button',
						array(
							'text'     => __( 'Twitter', 'parish-core' ),
							'metadata' => array(
								'bindings' => array(
									'url' => array(
										'source' => 'parish/post-meta',
										'args'   => array( 'key' => 'parish_twitter_url' ),
									),
								),
							),
						),
					),
					array(
						'core/button',
						array(
							'text'     => __( 'Instagram', 'parish-core' ),
							'metadata' => array(
								'bindings' => array(
									'url' => array(
										'source' => 'parish/post-meta',
										'args'   => array( 'key' => 'parish_instagram_url' ),
									),
								),
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
			'placeholder' => __( 'Add any additional content, galleries, or information here...', 'parish-core' ),
		),
	),

	array(
		'core/paragraph',
		array(
			'placeholder' => __( 'This section is fully editable and not bound to structured data. Add photos, history, ministries, or other content.', 'parish-core' ),
		),
	),
);
