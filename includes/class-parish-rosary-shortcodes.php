<?php
/**
 * Parish Rosary Shortcodes
 *
 * Provides shortcodes for displaying rosary mysteries on the frontend.
 *
 * @package ParishCore
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Parish Rosary Shortcodes class.
 */
class Parish_Rosary_Shortcodes {

	/**
	 * Register shortcodes with WordPress.
	 *
	 * @return void
	 */
	public static function register(): void {
		add_shortcode( 'rosary_today', array( __CLASS__, 'rosary_today' ) );
		add_shortcode( 'rosary_full', array( __CLASS__, 'rosary_full' ) );
	}

	/**
	 * Render today's rosary summary.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string Rendered HTML.
	 */
	public static function rosary_today( $atts ): string {
		$atts = shortcode_atts(
			array(
				'show_date'   => 'yes',
				'show_season' => 'yes',
			),
			$atts
		);

		$mystery_set = Parish_Rosary_Schedule::get_todays_mystery_set();
		$data        = Parish_Rosary_Data::get_mystery_set( $mystery_set );

		if ( ! $data ) {
			return '<p>' . esc_html__( 'Rosary data not available.', 'parish-core' ) . '</p>';
		}

		$html = '<div class="parish-rosary-today">';

		// Header.
		$html .= '<div class="rosary-header">';

		if ( 'yes' === $atts['show_date'] ) {
			$html .= '<div class="rosary-date">' . wp_date( 'l, F j, Y' ) . '</div>';
		}

		$html .= '<h3 class="rosary-title">' . esc_html( $data['name'] ) . '</h3>';

		if ( 'yes' === $atts['show_season'] && ! empty( $data['season_note'] ) ) {
			$html .= '<div class="rosary-season-note">' . esc_html( $data['season_note'] ) . '</div>';
		}

		$html .= '</div>'; // .rosary-header

		// Mysteries list.
		$html .= '<ol class="rosary-mysteries-list">';
		foreach ( $data['mysteries'] as $mystery ) {
			$html .= '<li>' . esc_html( $mystery['title'] ) . '</li>';
		}
		$html .= '</ol>';

		$html .= '</div>'; // .parish-rosary-today

		return $html;
	}

	/**
	 * Render full rosary with detailed meditations.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string Rendered HTML.
	 */
	public static function rosary_full( $atts ): string {
		$atts = shortcode_atts(
			array(
				'set'             => '',
				'show_fruit'      => 'yes',
				'show_scripture'  => 'yes',
				'show_meditation' => 'yes',
				'show_quote'      => 'yes',
			),
			$atts
		);

		$mystery_set = ! empty( $atts['set'] )
			? sanitize_text_field( $atts['set'] )
			: Parish_Rosary_Schedule::get_todays_mystery_set();

		$data = Parish_Rosary_Data::get_mystery_set( $mystery_set );

		if ( ! $data ) {
			return '<p>' . esc_html__( 'Rosary data not available.', 'parish-core' ) . '</p>';
		}

		$html = '<div class="parish-rosary-full">';

		// Header.
		$html .= '<div class="rosary-header">';
		$html .= '<h2 class="rosary-title">' . esc_html( $data['name'] ) . '</h2>';

		if ( ! empty( $data['description'] ) ) {
			$html .= '<p class="rosary-description">' . esc_html( $data['description'] ) . '</p>';
		}

		$html .= '</div>'; // .rosary-header

		// Each mystery.
		foreach ( $data['mysteries'] as $mystery ) {
			$html .= '<div class="rosary-mystery">';
			$html .= '<h3 class="mystery-title">' . $mystery['number'] . '. ' . esc_html( $mystery['title'] ) . '</h3>';

			if ( ! empty( $mystery['summary'] ) ) {
				$html .= '<p class="mystery-summary">' . esc_html( $mystery['summary'] ) . '</p>';
			}

			if ( 'yes' === $atts['show_fruit'] && ! empty( $mystery['fruit'] ) ) {
				$html .= '<div class="mystery-fruit"><strong>' . esc_html__( 'Fruit:', 'parish-core' ) . '</strong> ' . esc_html( $mystery['fruit'] ) . '</div>';
			}

			if ( 'yes' === $atts['show_scripture'] && ! empty( $mystery['scripture_ref'] ) ) {
				$html .= '<div class="mystery-scripture"><strong>' . esc_html__( 'Scripture:', 'parish-core' ) . '</strong> ' . esc_html( $mystery['scripture_ref'] ) . '</div>';
			}

			if ( 'yes' === $atts['show_quote'] && ! empty( $mystery['scripture_quote'] ) ) {
				$html .= '<blockquote class="mystery-quote">' . esc_html( $mystery['scripture_quote'] ) . '</blockquote>';
			}

			if ( 'yes' === $atts['show_meditation'] && ! empty( $mystery['meditation'] ) ) {
				$html .= '<div class="mystery-meditation">' . wp_kses_post( $mystery['meditation'] ) . '</div>';
			}

			$html .= '</div>'; // .rosary-mystery
		}

		$html .= '</div>'; // .parish-rosary-full

		return $html;
	}
}
