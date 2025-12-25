<?php
/**
 * Block template: News
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
			'className' => 'news-header',
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

			// News Title (uses post title)
			array(
				'core/post-title',
				array(
					'level'     => 1,
					'textAlign' => 'left',
				),
			),

			// Category & Date
			array(
				'core/columns',
				array(),
				array(
					array(
						'core/column',
						array(),
						array(
							// Category
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Category (e.g., Announcement, Event)', 'parish-core' ),
									'style'       => array(
										'typography' => array(
											'fontWeight' => '600',
										),
									),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_news_category' ),
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
							// Publish Date
							array(
								'core/paragraph',
								array(
									'align'       => 'right',
									'placeholder' => __( 'Publish date', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_publish_date' ),
											),
										),
									),
								),
							),
						),
					),
				),
			),

			// Author
			array(
				'core/paragraph',
				array(
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
		),
	),

	// Summary
	array(
		'core/group',
		array(
			'className' => 'news-summary',
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
				'core/paragraph',
				array(
					'placeholder' => __( 'News summary...', 'parish-core' ),
					'style'       => array(
						'typography' => array(
							'fontSize' => '1.1rem',
						),
					),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_news_summary' ),
							),
						),
					),
				),
			),
		),
	),

	// Event Details (if applicable)
	array(
		'core/details',
		array(
			'summary'     => __( 'Event Details', 'parish-core' ),
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
							// Event Date
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Event date', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_event_date' ),
											),
										),
									),
								),
							),

							// Event Time
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Event time', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_event_time' ),
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
							// Event Location
							array(
								'core/paragraph',
								array(
									'placeholder' => __( 'Event location', 'parish-core' ),
									'metadata'    => array(
										'bindings' => array(
											'content' => array(
												'source' => 'parish/post-meta',
												'args'   => array( 'key' => 'parish_event_location' ),
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

	// External Link
	array(
		'core/buttons',
		array(),
		array(
			array(
				'core/button',
				array(
					'placeholder' => __( 'Learn More', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'url'  => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_external_link' ),
							),
							'text' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_external_link_text' ),
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
			'content'     => __( 'Full Article', 'parish-core' ),
			'placeholder' => __( 'Add the full news content here...', 'parish-core' ),
		),
	),

	array(
		'core/paragraph',
		array(
			'placeholder' => __( 'This section is fully editable and not bound to structured data. Add the full news article content.', 'parish-core' ),
		),
	),
);
