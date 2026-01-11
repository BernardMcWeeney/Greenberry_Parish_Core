<?php
/**
 * Block template: Prayer
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

	// Prayer Type (above title)
	array(
		'core/paragraph',
		array(
			'placeholder' => __( 'Prayer type (e.g., Traditional, Novena, Litany)', 'parish-core' ),
			'metadata'    => array(
				'bindings' => array(
					'content' => array(
						'source' => 'parish/post-meta',
						'args'   => array( 'key' => 'parish_prayer_type' ),
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
					'placeholder' => __( 'When to pray this prayer (e.g., Morning, Evening, Before Meals)...', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_when_to_pray' ),
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

	// Prayer Text Section
	array(
		'core/group',
		array(
			'metadata' => array( 'name' => 'Prayer Text' ),
			'style'    => array(
				'spacing' => array(
					'margin'  => array(
						'top'    => 'var:preset|spacing|30',
						'bottom' => 'var:preset|spacing|30',
					),
					'padding' => array(
						'top'    => 'var:preset|spacing|20',
						'right'  => 'var:preset|spacing|20',
						'bottom' => 'var:preset|spacing|20',
						'left'   => 'var:preset|spacing|20',
					),
				),
				'color'   => array( 'background' => '#f9f9f9' ),
			),
			'layout'   => array( 'type' => 'constrained' ),
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
				),
			),
		),
	),

	// Attribution Section
	array(
		'core/group',
		array(
			'metadata' => array( 'name' => 'Attribution' ),
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
					'placeholder' => __( 'Author (e.g., St. Francis, Traditional)', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_author_name' ),
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
