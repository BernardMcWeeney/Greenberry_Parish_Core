<?php
/**
 * Block template: Parish History
 *
 * Flexible, sparse template for parish history entries.
 * Similar to news - allows freeform content.
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

	// Era/Period (above title)
	array(
		'core/paragraph',
		array(
			'placeholder' => __( 'Time period (e.g., 1920s, Victorian Era, Founding Years)', 'parish-core' ),
			'metadata'    => array(
				'bindings' => array(
					'content' => array(
						'source' => 'parish/post-meta',
						'args'   => array( 'key' => 'parish_history_era' ),
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

	// Year/Date
	array(
		'core/paragraph',
		array(
			'placeholder' => __( 'Year or date (e.g., 1923, March 1945)', 'parish-core' ),
			'metadata'    => array(
				'bindings' => array(
					'content' => array(
						'source' => 'parish/post-meta',
						'args'   => array( 'key' => 'parish_history_year' ),
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
			'fontFamily'  => 'system-serif',
		),
	),

	// Separator
	array(
		'core/separator',
		array(
			'className'       => 'is-style-wide',
			'style'           => array(
				'spacing' => array(
					'margin' => array(
						'top'    => 'var:preset|spacing|30',
						'bottom' => 'var:preset|spacing|30',
					),
				),
			),
			'backgroundColor' => 'contrast-3',
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

	// Content Area (freeform - flexible for any content)
	array(
		'core/paragraph',
		array(
			'placeholder' => __( 'Write the history content here. You can add more blocks below for images, quotes, documents, etc...', 'parish-core' ),
		),
	),
);
