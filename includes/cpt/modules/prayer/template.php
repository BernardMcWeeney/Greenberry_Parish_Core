<?php
/**
 * Block template: Prayer
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
			'className' => 'prayer-header',
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
			// Prayer Title (uses post title)
			array(
				'core/post-title',
				array(
					'level'     => 1,
					'textAlign' => 'center',
				),
			),

			// Prayer Type & Category
			array(
				'core/columns',
				array(),
				array(
					array(
						'core/column',
						array(),
						array(
							// Prayer Type
							array(
								'core/paragraph',
								array(
									'align'       => 'center',
									'placeholder' => __( 'Prayer type (e.g., Traditional, Novena, Litany)', 'parish-core' ),
									'style'       => array(
										'typography' => array(
											'fontWeight' => '600',
										),
									),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_prayer_type' ),
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
							// Prayer Category
							array(
								'core/paragraph',
								array(
									'align'       => 'center',
									'placeholder' => __( 'Category (e.g., Adoration, Petition)', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_prayer_category' ),
											),
										),
									),
								),
							),
						),
					),
				),
			),

			// Liturgical Season & Feast Day
			array(
				'core/paragraph',
				array(
					'align'       => 'center',
					'placeholder' => __( 'Liturgical season (e.g., Lent, Advent)', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_liturgical_season' ),
							),
						),
					),
				),
			),
		),
	),

	// Prayer Text Section
	array(
		'core/group',
		array(
			'className' => 'prayer-content',
			'style'     => array(
				'spacing' => array(
					'padding' => array(
						'top'    => '2rem',
						'bottom' => '2rem',
					),
				),
				'color'   => array(
					'background' => '#f9f9f9',
				),
			),
		),
		array(
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Enter the prayer text here...', 'parish-core' ),
					'style'       => array(
						'typography' => array(
							'fontSize'   => '1.125rem',
							'lineHeight' => '1.8',
						),
					),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_prayer_text' ),
							),
						),
					),
				),
			),
		),
	),

	// Usage Information
	array(
		'core/group',
		array(
			'className' => 'prayer-usage',
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
					'content' => __( 'When to Pray', 'parish-core' ),
				),
			),

			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'When to pray this prayer (e.g., Morning, Evening, Before Meals)...', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_when_to_pray' ),
							),
						),
					),
				),
			),
		),
	),

	// Scripture References (Collapsible)
	array(
		'core/details',
		array(
			'summary'     => __( 'Scripture References', 'parish-core' ),
			'showContent' => false,
		),
		array(
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Related scripture passages (e.g., Psalm 23, John 3:16)...', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_scripture_refs' ),
							),
						),
					),
				),
			),
		),
	),

	// Attribution Section
	array(
		'core/group',
		array(
			'className' => 'prayer-attribution',
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
					'content' => __( 'Attribution', 'parish-core' ),
				),
			),

			array(
				'core/columns',
				array(),
				array(
					// Column 1: Author & Source
					array(
						'core/column',
						array(),
						array(
							// Author
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Author (e.g., St. Francis, Traditional)', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_author_name' ),
											),
										),
									),
								),
							),

							// Source
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Source (book, tradition, etc.)', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_source' ),
											),
										),
									),
								),
							),
						),
					),

					// Column 2: Language & Feast Day
					array(
						'core/column',
						array(),
						array(
							// Language
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Language (e.g., English, Latin)', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_language' ),
											),
										),
									),
								),
							),

							// Feast Day
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Associated feast day', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_feast_day' ),
											),
										),
									),
								),
							),
						),
					),
				),
			),

			// Copyright
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Copyright information...', 'parish-core' ),
					'style'       => array(
						'typography' => array(
							'fontSize' => '0.875rem',
						),
					),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_copyright_info' ),
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
			'content'     => __( 'Reflection & Notes', 'parish-core' ),
			'placeholder' => __( 'Add any additional content here...', 'parish-core' ),
		),
	),

	array(
		'core/paragraph',
		array(
			'placeholder' => __( 'This section is fully editable and not bound to structured data. Add reflections, personal notes, or other content.', 'parish-core' ),
		),
	),
);
