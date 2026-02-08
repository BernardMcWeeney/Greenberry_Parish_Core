<?php
/**
 * Parish Rosary Blocks
 *
 * Gutenberg blocks for displaying rosary mysteries.
 *
 * @package ParishCore
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Parish Rosary Blocks class.
 */
class Parish_Rosary_Blocks {

	/**
	 * Register Gutenberg blocks with WordPress.
	 *
	 * @return void
	 */
	public static function register(): void {
		register_block_type(
			'parish/rosary-today',
			array(
				'api_version'     => 3,
				'render_callback' => array( __CLASS__, 'render_today' ),
				'attributes'      => array(
					'showDate'   => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'showSeason' => array(
						'type'    => 'boolean',
						'default' => false,
					),
				),
				'supports'        => array(
					'html'    => false,
					'align'   => array( 'wide', 'full' ),
					'spacing' => array(
						'margin'  => true,
						'padding' => true,
					),
				),
			)
		);

		register_block_type(
			'parish/rosary-full',
			array(
				'api_version'     => 3,
				'render_callback' => array( __CLASS__, 'render_full' ),
				'attributes'      => array(
					'mysterySet'     => array(
						'type'    => 'string',
						'default' => '',
					),
					'showFruit'      => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'showScripture'  => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'showMeditation' => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'showQuote'      => array(
						'type'    => 'boolean',
						'default' => true,
					),
				),
				'supports'        => array(
					'html'    => false,
					'align'   => array( 'wide', 'full' ),
					'spacing' => array(
						'margin'  => true,
						'padding' => true,
					),
				),
			)
		);
	}

	/**
	 * Render today's rosary block.
	 *
	 * @param array $attributes Block attributes.
	 * @return string Rendered HTML.
	 */
	public static function render_today( array $attributes ): string {
		$atts = array(
			'show_date'   => $attributes['showDate'] ? 'yes' : 'no',
			'show_season' => $attributes['showSeason'] ? 'yes' : 'no',
		);

		$content = Parish_Rosary_Shortcodes::rosary_today( $atts );

		// Get block wrapper attributes.
		$wrapper_attributes = get_block_wrapper_attributes(
			array(
				'class' => 'parish-rosary-today-block',
			)
		);

		return sprintf(
			'<div %s>%s</div>',
			$wrapper_attributes,
			$content
		);
	}

	/**
	 * Render full rosary block.
	 *
	 * @param array $attributes Block attributes.
	 * @return string Rendered HTML.
	 */
	public static function render_full( array $attributes ): string {
		$atts = array(
			'set'             => $attributes['mysterySet'] ?? '',
			'show_fruit'      => $attributes['showFruit'] ? 'yes' : 'no',
			'show_scripture'  => $attributes['showScripture'] ? 'yes' : 'no',
			'show_meditation' => $attributes['showMeditation'] ? 'yes' : 'no',
			'show_quote'      => $attributes['showQuote'] ? 'yes' : 'no',
		);

		$content = Parish_Rosary_Shortcodes::rosary_full( $atts );

		// Get block wrapper attributes.
		$wrapper_attributes = get_block_wrapper_attributes(
			array(
				'class' => 'parish-rosary-full-block',
			)
		);

		return sprintf(
			'<div %s>%s</div>',
			$wrapper_attributes,
			$content
		);
	}
}
