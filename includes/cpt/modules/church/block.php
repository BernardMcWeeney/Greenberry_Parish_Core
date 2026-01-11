<?php
/**
 * Church Schedule Block
 *
 * Dynamic block that renders the liturgical schedule for a church.
 * Uses the parish_church_schedule shortcode internally.
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Church Schedule Block class.
 */
class Parish_Church_Schedule_Block {

	/**
	 * Register the block with WordPress.
	 *
	 * @return void
	 */
	public static function register(): void {
		register_block_type(
			'parish/church-schedule',
			array(
				'editor_script'   => 'parish-core-editor-blocks',
				'render_callback' => array( __CLASS__, 'render' ),
				'uses_context'    => array( 'postType', 'postId' ),
				'attributes'      => array(
					'format'          => array(
						'type'    => 'string',
						'default' => 'list',
					),
					'eventTypes'      => array(
						'type'    => 'array',
						'default' => array( 'mass', 'confession' ),
						'items'   => array( 'type' => 'string' ),
					),
					'showFeastDay'    => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'days'            => array(
						'type'    => 'integer',
						'default' => 7,
					),
					'showLivestream'  => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'groupByDay'      => array(
						'type'    => 'boolean',
						'default' => true,
					),
				),
				'supports'        => array(
					'html'     => false,
					'align'    => array( 'wide', 'full' ),
					'spacing'  => array(
						'margin'  => true,
						'padding' => true,
					),
				),
			)
		);
	}

	/**
	 * Render the block on the frontend.
	 *
	 * @param array    $attributes Block attributes.
	 * @param string   $content    Block content.
	 * @param WP_Block $block      Block instance.
	 * @return string Rendered HTML.
	 */
	public static function render( array $attributes, string $content, WP_Block $block ): string {
		$post_id = $block->context['postId'] ?? get_the_ID();

		if ( ! $post_id ) {
			return '';
		}

		// Only render on church posts.
		$post_type = get_post_type( $post_id );
		if ( 'parish_church' !== $post_type ) {
			return '';
		}

		// Check if church has schedule_display set to 'static' (legacy mode).
		$display_mode = get_post_meta( $post_id, 'parish_schedule_display', true );
		if ( 'static' === $display_mode ) {
			return self::render_static_schedule( $post_id );
		}

		// Get feast day settings from church meta.
		$feast_display     = get_post_meta( $post_id, 'parish_feast_day_display', true ) ?: 'inline';
		$include_memorials = (bool) get_post_meta( $post_id, 'parish_include_memorials', true );

		// Build shortcode attributes.
		$shortcode_atts = array(
			'church_id'     => $post_id,
			'format'        => $attributes['format'] ?? 'list',
			'days'          => $attributes['days'] ?? 7,
			'show_feast_day' => ( $attributes['showFeastDay'] ?? true ) ? 'true' : 'false',
		);

		// Add event types if specified.
		if ( ! empty( $attributes['eventTypes'] ) ) {
			$shortcode_atts['event_type'] = implode( ',', $attributes['eventTypes'] );
		}

		// Build shortcode string.
		$shortcode_parts = array();
		foreach ( $shortcode_atts as $key => $value ) {
			$shortcode_parts[] = sprintf( '%s="%s"', $key, esc_attr( $value ) );
		}

		$shortcode = '[parish_church_schedule ' . implode( ' ', $shortcode_parts ) . ']';

		// Execute shortcode and wrap in block wrapper.
		$output = do_shortcode( $shortcode );

		if ( empty( trim( $output ) ) ) {
			// Show placeholder in editor context.
			if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
				return sprintf(
					'<div class="parish-church-schedule-placeholder">%s</div>',
					esc_html__( 'No schedule found for this church.', 'parish-core' )
				);
			}
			return '';
		}

		// Get block wrapper attributes.
		$wrapper_attributes = get_block_wrapper_attributes(
			array(
				'class' => 'parish-church-schedule-block',
			)
		);

		return sprintf(
			'<div %s>%s</div>',
			$wrapper_attributes,
			$output
		);
	}

	/**
	 * Render static/legacy schedule from meta fields.
	 *
	 * Falls back to the old mass_times/confession_times meta fields.
	 *
	 * @param int $post_id Church post ID.
	 * @return string Rendered HTML.
	 */
	private static function render_static_schedule( int $post_id ): string {
		$mass_times       = get_post_meta( $post_id, 'parish_mass_times', true );
		$confession_times = get_post_meta( $post_id, 'parish_confession_times', true );

		if ( empty( $mass_times ) && empty( $confession_times ) ) {
			return '';
		}

		$output = '<div class="parish-church-schedule-static">';

		if ( ! empty( $mass_times ) ) {
			$output .= '<div class="schedule-section schedule-mass">';
			$output .= '<h3>' . esc_html__( 'Mass Times', 'parish-core' ) . '</h3>';
			$output .= '<div class="schedule-content">' . wp_kses_post( nl2br( $mass_times ) ) . '</div>';
			$output .= '</div>';
		}

		if ( ! empty( $confession_times ) ) {
			$output .= '<div class="schedule-section schedule-confession">';
			$output .= '<h3>' . esc_html__( 'Confession Times', 'parish-core' ) . '</h3>';
			$output .= '<div class="schedule-content">' . wp_kses_post( nl2br( $confession_times ) ) . '</div>';
			$output .= '</div>';
		}

		$output .= '</div>';

		return $output;
	}
}
