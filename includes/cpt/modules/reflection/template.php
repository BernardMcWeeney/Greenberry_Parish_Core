<?php
/**
 * Block template: Reflection
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
			'className' => 'reflection-header',
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
			// Reflection Title (uses post title)
			array(
				'core/post-title',
				array(
					'level'     => 1,
					'textAlign' => 'center',
				),
			),

			// Reflection Type & Season
			array(
				'core/columns',
				array(),
				array(
					array(
						'core/column',
						array(),
						array(
							// Reflection Type
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Reflection type (e.g., Homily, Meditation)', 'parish-core' ),
									'style'       => array(
										'typography' => array(
											'fontWeight' => '600',
										),
									),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_reflection_type' ),
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
							// Liturgical Season
							array(
								'core/paragraph',
								array(
									'align'       => 'right',
									'placeholder' => __( 'Liturgical season', 'parish-core' ),
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
				),
			),

			// Author Info
			array(
				'core/paragraph',
				array(
					'align'       => 'center',
					'placeholder' => __( 'Author name', 'parish-core' ),
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

			array(
				'core/paragraph',
				array(
					'align'       => 'center',
					'placeholder' => __( 'Author title (e.g., Parish Priest)', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_author_title' ),
							),
						),
					),
				),
			),
		),
	),

	// Key Verse (Quote)
	array(
		'core/group',
		array(
			'className' => 'reflection-verse',
			'style'     => array(
				'spacing' => array(
					'padding' => array(
						'top'    => '1.5rem',
						'bottom' => '1.5rem',
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
					'align'       => 'center',
					'placeholder' => __( 'Key verse or quote...', 'parish-core' ),
					'style'       => array(
						'typography' => array(
							'fontSize'  => '1.25rem',
							'fontStyle' => 'italic',
						),
					),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_key_verse' ),
							),
						),
					),
				),
			),
		),
	),

	// Scripture Readings (Collapsible)
	array(
		'core/details',
		array(
			'summary'     => __( 'Scripture Readings', 'parish-core' ),
			'showContent' => false,
		),
		array(
			array(
				'core/columns',
				array(),
				array(
					array(
						'core/column',
						array(),
						array(
							array(
								'core/heading',
								array(
									'level'   => 4,
									'content' => __( 'First Reading', 'parish-core' ),
								),
							),
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'First reading reference', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_first_reading' ),
											),
										),
									),
								),
							),

							array(
								'core/heading',
								array(
									'level'   => 4,
									'content' => __( 'Psalm', 'parish-core' ),
								),
							),
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Responsorial psalm', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_psalm' ),
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
								'core/heading',
								array(
									'level'   => 4,
									'content' => __( 'Second Reading', 'parish-core' ),
								),
							),
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Second reading reference', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_second_reading' ),
											),
										),
									),
								),
							),

							array(
								'core/heading',
								array(
									'level'   => 4,
									'content' => __( 'Gospel', 'parish-core' ),
								),
							),
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Gospel reading reference', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_gospel_reading' ),
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

	// Reflection Text
	array(
		'core/group',
		array(
			'className' => 'reflection-text',
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
					'content' => __( 'Reflection', 'parish-core' ),
				),
			),

			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'The reflection text...', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_reflection_text' ),
							),
						),
					),
				),
			),
		),
	),

	// Media Links
	array(
		'core/buttons',
		array(
			'layout' => array(
				'type'           => 'flex',
				'justifyContent' => 'center',
			),
		),
		array(
			array(
				'core/button',
				array(
					'text'     => __( 'Listen to Audio', 'parish-core' ),
					'metadata' => array(
						'bindings' => array(
							'url' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_audio_url' ),
							),
						),
					),
				),
			),
			array(
				'core/button',
				array(
					'text'     => __( 'Watch Video', 'parish-core' ),
					'metadata' => array(
						'bindings' => array(
							'url' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_video_url' ),
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
		'core/paragraph',
		array(
			'placeholder' => __( 'Add any additional commentary or notes here...', 'parish-core' ),
		),
	),
);
