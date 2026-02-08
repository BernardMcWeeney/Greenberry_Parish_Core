<?php
/**
 * Today Mass Block
 *
 * Dynamic Gutenberg block that displays today's Mass times across all churches.
 * Uses Font Awesome icons and consistent styling.
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Today Mass Block class.
 */
class Parish_Today_Mass_Block {

	/**
	 * Register the block with WordPress.
	 *
	 * @return void
	 */
	public static function register(): void {
		register_block_type(
			'parish/today-mass',
			array(
				'api_version'     => 3,
				'editor_script'   => 'parish-core-editor-blocks',
				'render_callback' => array( __CLASS__, 'render' ),
				'attributes'      => array(
					'churchId'       => array(
						'type'    => 'integer',
						'default' => 0,
					),
					'eventType'      => array(
						'type'    => 'string',
						'default' => 'mass',
					),
					'showIcon'       => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'showDate'       => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'showNotes'      => array(
						'type'    => 'boolean',
						'default' => false,
					),
					'iconColor'      => array(
						'type'    => 'string',
						'default' => '',
					),
					'timeColor'      => array(
						'type'    => 'string',
						'default' => '',
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
	 * @param array $attributes Block attributes.
	 * @return string Rendered HTML.
	 */
	public static function render( array $attributes ): string {
		// Feature check.
		if ( ! Parish_Core::is_feature_enabled( 'mass_times' ) ) {
			return '';
		}

		if ( ! class_exists( 'Parish_Schedule_Generator' ) ) {
			return '';
		}

		$church_id   = (int) ( $attributes['churchId'] ?? 0 );
		$event_type  = sanitize_text_field( $attributes['eventType'] ?? 'mass' );
		$show_icon   = (bool) ( $attributes['showIcon'] ?? true );
		$show_date   = (bool) ( $attributes['showDate'] ?? true );
		$show_notes  = (bool) ( $attributes['showNotes'] ?? false );
		$icon_color  = sanitize_hex_color( $attributes['iconColor'] ?? '' ) ?: 'var(--wp--preset--color--accent,#609fae)';
		$time_color  = sanitize_hex_color( $attributes['timeColor'] ?? '' ) ?: 'var(--wp--preset--color--accent,#609fae)';

		// Get today's date.
		$target_date = wp_date( 'Y-m-d' );

		// Build filters.
		$filters = array();
		if ( $church_id > 0 ) {
			$filters['church_id'] = $church_id;
		}
		if ( ! empty( $event_type ) ) {
			$filters['event_type'] = $event_type;
		}

		// Generate schedule for today.
		$generator = Parish_Schedule_Generator::instance();
		$schedule  = $generator->generate( $target_date, $target_date, $filters );

		// Build HTML output.
		$html = '';

		// Date header.
		if ( $show_date ) {
			$display_date = wp_date( 'l, j F Y', strtotime( $target_date ) );
			$html .= '<p class="parish-today-mass-date">' . esc_html( $display_date ) . '</p>';
		}

		$html .= '<div class="parish-today-mass-rows">';

		if ( empty( $schedule ) ) {
			$html .= '<div class="parish-today-mass-church-block" style="padding:0.75em 0;border-bottom:1px solid rgba(0,0,0,0.08);">';
			$html .= '<p class="parish-today-mass-no-mass" style="font-style:italic;opacity:0.5;margin:0;">' . esc_html__( 'No events scheduled for today', 'parish-core' ) . '</p>';
			$html .= '</div>';
		} else {
			// Group events by church.
			$by_church = array();
			foreach ( $schedule as $event ) {
				$c_id   = $event['church_id'] ?? 0;
				$c_name = $event['church_name'] ?? __( 'All Churches', 'parish-core' );

				if ( ! isset( $by_church[ $c_id ] ) ) {
					$by_church[ $c_id ] = array(
						'name'     => $c_name,
						'has_live' => false,
						'events'   => array(),
					);
				}
				$by_church[ $c_id ]['events'][] = $event;
				if ( ! empty( $event['is_livestreamed'] ) ) {
					$by_church[ $c_id ]['has_live'] = true;
				}
			}

			foreach ( $by_church as $church_data ) {
				$html .= '<div class="parish-today-mass-church-block" style="padding:0.75em 0;border-bottom:1px solid rgba(0,0,0,0.08);">';

				// Row 1: Church name.
				$html .= '<div class="parish-today-mass-header" style="margin-bottom:0.25em;">';
				$html .= '<span class="parish-today-mass-church" style="font-weight:500;">' . esc_html( $church_data['name'] ) . '</span>';
				$html .= '</div>';

				// Row 2: Times with individual livestream icons.
				$times_parts = array();
				foreach ( $church_data['events'] as $event ) {
					$time_str  = self::format_time( $event['time'] ?? '' );
					$time_html = esc_html( $time_str );

					if ( $show_notes && ! empty( $event['notes'] ) ) {
						$time_html .= ' <span style="font-size:0.9em;opacity:0.7;">(' . esc_html( wp_strip_all_tags( $event['notes'] ) ) . ')</span>';
					}

					// Add livestream icon next to this specific time if applicable.
					if ( $show_icon && ! empty( $event['is_livestreamed'] ) ) {
						$time_html .= sprintf(
							' <i class="fa-solid fa-video" style="color:%s;font-size:0.9em;" aria-label="%s"></i>',
							esc_attr( $icon_color ),
							esc_attr__( 'Livestreamed', 'parish-core' )
						);
					}

					$times_parts[] = $time_html;
				}

				$html .= sprintf(
					'<div class="parish-today-mass-times" style="color:%s;font-weight:600;">%s</div>',
					esc_attr( $time_color ),
					implode( ', ', $times_parts )
				);

				$html .= '</div>'; // .parish-today-mass-church-block
			}
		}

		$html .= '</div>'; // .parish-today-mass-rows

		// Get block wrapper attributes.
		$wrapper_attributes = get_block_wrapper_attributes(
			array(
				'class' => 'parish-today-mass-block',
			)
		);

		return sprintf(
			'<div %s>%s</div>',
			$wrapper_attributes,
			$html
		);
	}

	/**
	 * Format time from various input formats.
	 *
	 * @param string $datetime DateTime or time string.
	 * @return string Formatted time.
	 */
	private static function format_time( string $datetime ): string {
		if ( empty( $datetime ) ) {
			return '';
		}

		// Handle ISO 8601 format (2026-01-11T10:00).
		if ( strpos( $datetime, 'T' ) !== false ) {
			$parts = explode( 'T', $datetime );
			$time  = $parts[1] ?? '';
			if ( strlen( $time ) > 5 ) {
				$time = substr( $time, 0, 5 );
			}
		} elseif ( strpos( $datetime, ' ' ) !== false ) {
			$parts = explode( ' ', $datetime );
			$time  = $parts[1] ?? '';
			if ( strlen( $time ) > 5 ) {
				$time = substr( $time, 0, 5 );
			}
		} else {
			$time = $datetime;
		}

		$timestamp = strtotime( "2000-01-01 $time" );
		if ( $timestamp === false ) {
			return $time;
		}

		return gmdate( 'g:i A', $timestamp );
	}
}
