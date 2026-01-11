<?php
/**
 * Block template: News
 *
 * Simple, sparse template for news items.
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

	// Post Title
	array(
		'core/post-title',
		array(
			'level'    => 1,
			'fontSize' => 'x-large',
		),
	),

	// Post Date
	array(
		'core/post-date',
		array(
			'style'     => array(
				'spacing' => array(
					'margin' => array(
						'top'    => '0',
						'bottom' => '0',
					),
				),
			),
			'textColor' => 'contrast-2',
			'fontSize'  => 'small',
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

	// Content Area (freeform)
	array(
		'core/paragraph',
		array(
			'placeholder' => __( 'Write the news article content here...', 'parish-core' ),
		),
	),
);
