<?php
/**
 * Block template: Reflection
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

	// Reflection Type (above title)
	array(
		'core/paragraph',
		array(
			'placeholder' => __( 'Reflection type (e.g., Homily, Meditation)', 'parish-core' ),
			'metadata'    => array(
				'bindings' => array(
					'content' => array(
						'source' => 'parish/post-meta',
						'args'   => array( 'key' => 'parish_reflection_type' ),
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

	// Opening Section - Author
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
					'placeholder' => __( 'Author name (e.g., Fr. John Smith)', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_author_name' ),
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

	// Key Verse / Quote
	array(
		'core/group',
		array(
			'metadata' => array( 'name' => 'Key Verse' ),
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
					'align'       => 'center',
					'placeholder' => __( 'Key verse or quote...', 'parish-core' ),
					'metadata'    => array(
						'bindings' => array(
							'content' => array(
								'source' => 'parish/post-meta',
								'args'   => array( 'key' => 'parish_key_verse' ),
							),
						),
					),
					'style'       => array(
						'typography' => array(
							'fontSize'  => '1.25rem',
							'fontStyle' => 'italic',
						),
					),
				),
			),
		),
	),

	// Reflection Text Section
	array(
		'core/group',
		array(
			'metadata' => array( 'name' => 'Reflection' ),
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
					'content'   => '<strong>' . __( 'Reflection', 'parish-core' ) . '</strong>',
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
);
