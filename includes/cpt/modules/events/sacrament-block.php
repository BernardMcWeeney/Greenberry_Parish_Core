<?php
/**
 * Sacrament Events Block
 *
 * Dynamic Gutenberg block that displays events filtered by sacrament.
 * Ideal for embedding on sacrament-specific pages (e.g., Eucharist, Confirmation).
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sacrament Events Block class.
 */
class Parish_Sacrament_Events_Block {

	/**
	 * Register the block with WordPress.
	 *
	 * @return void
	 */
	public static function register(): void {
		register_block_type(
			'parish/sacrament-events',
			array(
				'api_version'     => 3,
				'editor_script'   => 'parish-core-editor-blocks',
				'render_callback' => array( __CLASS__, 'render' ),
				'attributes'      => array(
					'sacrament'        => array(
						'type'    => 'string',
						'default' => '',
					),
					'churchId'         => array(
						'type'    => 'integer',
						'default' => 0,
					),
					'limit'            => array(
						'type'    => 'integer',
						'default' => 5,
					),
					'showIcon'         => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'showChurch'       => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'showDescription'  => array(
						'type'    => 'boolean',
						'default' => false,
					),
					'emptyMessage'     => array(
						'type'    => 'string',
						'default' => '',
					),
					'iconColor'        => array(
						'type'    => 'string',
						'default' => '',
					),
					'dateColor'        => array(
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
		if ( ! Parish_Core::is_feature_enabled( 'events' ) ) {
			return '';
		}

		$sacrament        = sanitize_text_field( $attributes['sacrament'] ?? '' );
		$church_id        = absint( $attributes['churchId'] ?? 0 );
		$limit            = absint( $attributes['limit'] ?? 5 );
		$show_icon        = (bool) ( $attributes['showIcon'] ?? true );
		$show_church      = (bool) ( $attributes['showChurch'] ?? true );
		$show_description = (bool) ( $attributes['showDescription'] ?? false );
		$empty_message    = sanitize_text_field( $attributes['emptyMessage'] ?? '' );
		$icon_color       = sanitize_hex_color( $attributes['iconColor'] ?? '' ) ?: 'var(--wp--preset--color--accent,#2271b1)';
		$date_color       = sanitize_hex_color( $attributes['dateColor'] ?? '' ) ?: 'var(--wp--preset--color--accent,#2271b1)';

		// If no sacrament specified, show nothing.
		if ( empty( $sacrament ) ) {
			return '';
		}

		// Build query.
		$args = array(
			'post_type'      => 'parish_event',
			'posts_per_page' => $limit,
			'post_status'    => 'publish',
			'meta_query'     => array(
				array(
					'key'     => 'parish_event_date',
					'value'   => current_time( 'Y-m-d' ),
					'compare' => '>=',
					'type'    => 'DATE',
				),
			),
			'tax_query'      => array(
				array(
					'taxonomy' => 'parish_sacrament',
					'field'    => 'slug',
					'terms'    => $sacrament,
				),
			),
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
			'meta_key'       => 'parish_event_date',
		);

		// Filter by church if specified.
		if ( $church_id > 0 ) {
			$args['meta_query'][] = array(
				'key'     => 'parish_event_church_id',
				'value'   => $church_id,
				'compare' => '=',
				'type'    => 'NUMERIC',
			);
		}

		$query  = new WP_Query( $args );
		$events = $query->posts;

		// Get sacrament term for display.
		$sacrament_term = get_term_by( 'slug', $sacrament, 'parish_sacrament' );
		$sacrament_name = $sacrament_term ? $sacrament_term->name : ucwords( str_replace( '-', ' ', $sacrament ) );

		// Build HTML.
		$html = '<div class="parish-sacrament-events">';

		if ( empty( $events ) ) {
			$message = ! empty( $empty_message ) ? $empty_message : sprintf(
				/* translators: %s: sacrament name */
				__( 'No upcoming %s events scheduled.', 'parish-core' ),
				strtolower( $sacrament_name )
			);
			$html .= '<div class="parish-sacrament-events-empty" style="padding:16px;background:#f9f9f9;border-radius:8px;text-align:center;">';
			$html .= '<p style="margin:0;opacity:0.7;">' . esc_html( $message ) . '</p>';
			$html .= '</div>';
		} else {
			$html .= '<ul class="parish-sacrament-events-list" style="list-style:none;margin:0;padding:0;">';

			foreach ( $events as $event ) {
				$html .= self::render_event_item( $event, $show_icon, $show_church, $show_description, $icon_color, $date_color );
			}

			$html .= '</ul>';
		}

		$html .= '</div>';

		wp_reset_postdata();

		// Get block wrapper attributes.
		$wrapper_attributes = get_block_wrapper_attributes(
			array(
				'class' => 'parish-sacrament-events-block',
			)
		);

		return sprintf(
			'<div %s>%s</div>',
			$wrapper_attributes,
			$html
		);
	}

	/**
	 * Render a single event item.
	 *
	 * @param WP_Post $event            Event post object.
	 * @param bool    $show_icon        Whether to show calendar icon.
	 * @param bool    $show_church      Whether to show church name.
	 * @param bool    $show_description Whether to show event excerpt.
	 * @param string  $icon_color       Color for icons.
	 * @param string  $date_color       Color for date.
	 * @return string HTML output.
	 */
	private static function render_event_item( WP_Post $event, bool $show_icon, bool $show_church, bool $show_description, string $icon_color, string $date_color ): string {
		$event_date     = get_post_meta( $event->ID, 'parish_event_date', true );
		$event_time     = get_post_meta( $event->ID, 'parish_event_time', true );
		$event_location = get_post_meta( $event->ID, 'parish_event_location', true );
		$church_id      = absint( get_post_meta( $event->ID, 'parish_event_church_id', true ) );

		// Get church name.
		$church_name = '';
		if ( $church_id > 0 ) {
			$church = get_post( $church_id );
			if ( $church ) {
				$church_name = $church->post_title;
			}
		}

		// Format date.
		$date_display = '';
		if ( $event_date ) {
			$date_display = date_i18n( 'l, F j', strtotime( $event_date ) );
		}

		// Format time.
		$time_display = '';
		if ( $event_time ) {
			$time_display = date_i18n( 'g:i A', strtotime( "2000-01-01 $event_time" ) );
		}

		// Build HTML.
		$html = '<li class="parish-sacrament-event-item" style="padding:12px 0;border-bottom:1px solid #eee;display:flex;align-items:flex-start;gap:12px;">';

		// Icon/date box.
		if ( $show_icon && $event_date ) {
			$html .= sprintf(
				'<div class="event-date-icon" style="flex-shrink:0;width:50px;text-align:center;background:%s;color:#fff;border-radius:6px;padding:6px;">',
				esc_attr( $date_color )
			);
			$html .= '<div style="font-size:20px;font-weight:bold;line-height:1;">' . esc_html( date_i18n( 'j', strtotime( $event_date ) ) ) . '</div>';
			$html .= '<div style="font-size:10px;text-transform:uppercase;">' . esc_html( date_i18n( 'M', strtotime( $event_date ) ) ) . '</div>';
			$html .= '</div>';
		}

		// Content.
		$html .= '<div class="event-content" style="flex:1;min-width:0;">';

		// Title.
		$html .= '<a href="' . esc_url( get_permalink( $event ) ) . '" class="event-title" style="display:block;font-weight:600;text-decoration:none;color:inherit;margin-bottom:4px;">';
		$html .= esc_html( $event->post_title );
		$html .= '</a>';

		// Details.
		$details = array();
		if ( $date_display ) {
			$details[] = $date_display;
		}
		if ( $time_display ) {
			$details[] = $time_display;
		}

		if ( ! empty( $details ) ) {
			$html .= '<div class="event-datetime" style="font-size:13px;color:#666;margin-bottom:2px;">';
			$html .= '<i class="fa-regular fa-calendar" style="margin-right:4px;color:' . esc_attr( $icon_color ) . ';"></i>';
			$html .= esc_html( implode( ' at ', $details ) );
			$html .= '</div>';
		}

		// Location/Church.
		if ( $show_church && ( $event_location || $church_name ) ) {
			$html .= '<div class="event-location" style="font-size:13px;color:#666;">';
			$html .= '<i class="fa-solid fa-location-dot" style="margin-right:4px;color:' . esc_attr( $icon_color ) . ';"></i>';
			$html .= esc_html( $event_location ?: $church_name );
			$html .= '</div>';
		}

		// Description/Excerpt.
		if ( $show_description && ! empty( $event->post_excerpt ) ) {
			$html .= '<div class="event-description" style="font-size:13px;color:#666;margin-top:6px;">';
			$html .= esc_html( wp_trim_words( $event->post_excerpt, 20 ) );
			$html .= '</div>';
		}

		$html .= '</div>'; // .event-content
		$html .= '</li>';

		return $html;
	}
}
