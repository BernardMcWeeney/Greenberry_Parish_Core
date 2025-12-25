<?php
/**
 * Block template: Newsletter
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
			'className' => 'newsletter-header',
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
					'height'      => '300px',
				),
			),

			// Newsletter Title (uses post title)
			array(
				'core/post-title',
				array(
					'level'     => 1,
					'textAlign' => 'center',
				),
			),

			// Issue Number
			array(
				'core/paragraph',
				array(
					'align'       => 'center',
					'placeholder' => __( 'Issue number (e.g., Vol. 5 Issue 12)', 'parish-core' ),
					'style'       => array(
						'typography' => array(
							'fontSize' => '1.25rem',
						),
					),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_issue_number' ),
							),
						),
					),
				),
			),

			// Publication Date
			array(
				'core/paragraph',
				array(
					'align'       => 'center',
					'placeholder' => __( 'Publication date', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_publication_date' ),
							),
						),
					),
				),
			),
		),
	),

	// Download & View Actions
	array(
		'core/group',
		array(
			'className' => 'newsletter-actions',
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
				'core/buttons',
				array(
					'layout' => array(
						'type'            => 'flex',
						'justifyContent'  => 'center',
					),
				),
				array(
					array(
						'core/button',
						array(
							'text'     => __( 'Download PDF', 'parish-core' ),
							'metadata' => array(
								'bindings' => array(
									'url' => array(
										'source' => 'parish/post-meta',
										'args'   => array( 'key' => 'parish_pdf_file_url' ),
									),
								),
							),
						),
					),
					array(
						'core/button',
						array(
							'text'     => __( 'View Online', 'parish-core' ),
							'metadata' => array(
								'bindings' => array(
									'url' => array(
										'source' => 'parish/post-meta',
										'args'   => array( 'key' => 'parish_online_link' ),
									),
								),
							),
						),
					),
				),
			),
		),
	),

	// Summary
	array(
		'core/group',
		array(
			'className' => 'newsletter-summary',
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
					'content' => __( 'In This Issue', 'parish-core' ),
				),
			),

			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Summary of this newsletter...', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_summary' ),
							),
						),
					),
				),
			),
		),
	),

	// Highlights
	array(
		'core/group',
		array(
			'className' => 'newsletter-highlights',
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
				'core/heading',
				array(
					'level'   => 2,
					'content' => __( 'Highlights', 'parish-core' ),
				),
			),

			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Key highlights from this newsletter...', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_highlights' ),
							),
						),
					),
				),
			),
		),
	),

	// Editor Information (Collapsible)
	array(
		'core/details',
		array(
			'summary'     => __( 'About This Newsletter', 'parish-core' ),
			'showContent' => false,
		),
		array(
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Editor name', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_editor_name' ),
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
			'content'     => __( 'Additional Notes', 'parish-core' ),
			'placeholder' => __( 'Add any additional content here...', 'parish-core' ),
		),
	),

	array(
		'core/paragraph',
		array(
			'placeholder' => __( 'This section is fully editable and not bound to structured data.', 'parish-core' ),
		),
	),
);
