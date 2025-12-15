<?php
/**
 * Parish Hero Slider Module
 *
 * Provides a hybrid slider with manual and dynamic slides.
 * Dynamic slides auto-populate from Reflections, Feast Days, Readings, etc.
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Parish_Slider class.
 */
class Parish_Slider {

	/**
	 * Singleton instance.
	 *
	 * @var Parish_Slider|null
	 */
	private static ?Parish_Slider $instance = null;

	/**
	 * Available dynamic slide sources.
	 *
	 * @var array
	 */
	private array $dynamic_sources = array();

	/**
	 * Get singleton instance.
	 */
	public static function instance(): Parish_Slider {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->setup_dynamic_sources();

		// Register shortcode.
		add_shortcode( 'parish_slider', array( $this, 'render_slider' ) );

		// Register block.
		add_action( 'init', array( $this, 'register_block' ) );
	}

	/**
	 * Setup dynamic slide source configurations.
	 */
	private function setup_dynamic_sources(): void {
		$this->dynamic_sources = array(
			'reflection' => array(
				'name'        => __( 'Latest Reflection', 'parish-core' ),
				'description' => __( 'Automatically shows the most recent reflection post', 'parish-core' ),
				'icon'        => 'format-quote',
				'callback'    => array( $this, 'get_reflection_slide_data' ),
			),
			'feast_day' => array(
				'name'        => __( 'Feast of the Day', 'parish-core' ),
				'description' => __( 'Shows today\'s liturgical feast or celebration', 'parish-core' ),
				'icon'        => 'calendar-alt',
				'callback'    => array( $this, 'get_feast_day_slide_data' ),
			),
			'daily_readings' => array(
				'name'        => __( 'Daily Readings', 'parish-core' ),
				'description' => __( 'Highlights today\'s Mass readings', 'parish-core' ),
				'icon'        => 'book',
				'callback'    => array( $this, 'get_daily_readings_slide_data' ),
			),
			'liturgical_season' => array(
				'name'        => __( 'Liturgical Season', 'parish-core' ),
				'description' => __( 'Shows current liturgical season information', 'parish-core' ),
				'icon'        => 'admin-site',
				'callback'    => array( $this, 'get_liturgical_season_slide_data' ),
			),
			'rosary_today' => array(
				'name'        => __( 'Today\'s Rosary', 'parish-core' ),
				'description' => __( 'Shows which rosary mysteries to pray today', 'parish-core' ),
				'icon'        => 'heart',
				'callback'    => array( $this, 'get_rosary_slide_data' ),
			),
			'saint_of_day' => array(
				'name'        => __( 'Saint of the Day', 'parish-core' ),
				'description' => __( 'Features today\'s saint', 'parish-core' ),
				'icon'        => 'star-filled',
				'callback'    => array( $this, 'get_saint_slide_data' ),
			),
			'latest_newsletter' => array(
				'name'        => __( 'Latest Newsletter', 'parish-core' ),
				'description' => __( 'Promotes the most recent parish newsletter', 'parish-core' ),
				'icon'        => 'media-document',
				'callback'    => array( $this, 'get_newsletter_slide_data' ),
			),
			'upcoming_event' => array(
				'name'        => __( 'Next Event', 'parish-core' ),
				'description' => __( 'Shows the next upcoming parish event', 'parish-core' ),
				'icon'        => 'calendar',
				'callback'    => array( $this, 'get_event_slide_data' ),
			),
		);
	}

	/**
	 * Get available dynamic sources.
	 */
	public function get_dynamic_sources(): array {
		return $this->dynamic_sources;
	}

	/**
	 * Get slider settings.
	 */
	public function get_slider_settings(): array {
		$defaults = array(
			'enabled'          => true,
			'autoplay'         => true,
			'autoplay_speed'   => 5000,
			'transition_speed' => 1000,
			'show_arrows'      => true,
			'show_dots'        => true,
			'pause_on_hover'   => true,
			'height_desktop'   => 700,
			'height_tablet'    => 500,
			'height_mobile'    => 400,
			'overlay_color'    => '#4A8391',
			'overlay_opacity'  => 0.7,
			'overlay_gradient' => true,
			'slides'           => array(),
		);

		$settings = get_option( 'parish_slider_settings', array() );
		return wp_parse_args( $settings, $defaults );
	}

	/**
	 * Update slider settings.
	 *
	 * @param array $settings Settings to update.
	 */
	public function update_slider_settings( array $settings ): bool {
		return update_option( 'parish_slider_settings', $settings );
	}

	/**
	 * Get all slides (merged manual + dynamic).
	 */
	public function get_slides(): array {
		$settings = $this->get_slider_settings();
		$slides   = $settings['slides'] ?? array();
		$output   = array();

		foreach ( $slides as $slide ) {
			if ( ! ( $slide['enabled'] ?? true ) ) {
				continue;
			}

			if ( ( $slide['type'] ?? 'manual' ) === 'manual' ) {
				$output[] = $this->prepare_manual_slide( $slide );
			} elseif ( ( $slide['type'] ?? '' ) === 'dynamic' ) {
				$dynamic_slide = $this->prepare_dynamic_slide( $slide );
				if ( $dynamic_slide ) {
					$output[] = $dynamic_slide;
				}
			}
		}

		return $output;
	}

	/**
	 * Prepare a manual slide for output.
	 *
	 * @param array $slide Slide data.
	 */
	private function prepare_manual_slide( array $slide ): array {
		$image_url = '';
		if ( ! empty( $slide['image_id'] ) ) {
			$image_url = wp_get_attachment_image_url( $slide['image_id'], 'full' );
		} elseif ( ! empty( $slide['image_url'] ) ) {
			$image_url = $slide['image_url'];
		}

		return array(
			'id'          => $slide['id'] ?? uniqid(),
			'type'        => 'manual',
			'image'       => $image_url,
			'title'       => $slide['title'] ?? '',
			'subtitle'    => $slide['subtitle'] ?? '',
			'description' => $slide['description'] ?? '',
			'cta_text'    => $slide['cta_text'] ?? '',
			'cta_link'    => $slide['cta_link'] ?? '',
			'text_align'  => $slide['text_align'] ?? 'left',
		);
	}

	/**
	 * Prepare a dynamic slide for output.
	 *
	 * @param array $slide Slide configuration.
	 */
	private function prepare_dynamic_slide( array $slide ): ?array {
		$source = $slide['source'] ?? '';

		if ( ! isset( $this->dynamic_sources[ $source ] ) ) {
			return null;
		}

		$source_config = $this->dynamic_sources[ $source ];
		$callback      = $source_config['callback'];

		if ( ! is_callable( $callback ) ) {
			return null;
		}

		$dynamic_data = call_user_func( $callback );

		if ( empty( $dynamic_data ) ) {
			return null;
		}

		// Merge slide config with dynamic data.
		// Slide config can override dynamic data for customization.
		$image_url = '';
		if ( ! empty( $slide['image_id'] ) ) {
			$image_url = wp_get_attachment_image_url( $slide['image_id'], 'full' );
		} elseif ( ! empty( $slide['image_url'] ) ) {
			$image_url = $slide['image_url'];
		} elseif ( ! empty( $dynamic_data['image'] ) ) {
			$image_url = $dynamic_data['image'];
		}

		return array(
			'id'          => $slide['id'] ?? uniqid(),
			'type'        => 'dynamic',
			'source'      => $source,
			'image'       => $image_url,
			'title'       => ! empty( $slide['title_override'] ) ? $slide['title_override'] : ( $dynamic_data['title'] ?? '' ),
			'subtitle'    => ! empty( $slide['subtitle_override'] ) ? $slide['subtitle_override'] : ( $dynamic_data['subtitle'] ?? '' ),
			'description' => ! empty( $slide['description_override'] ) ? $slide['description_override'] : ( $dynamic_data['description'] ?? '' ),
			'cta_text'    => ! empty( $slide['cta_text'] ) ? $slide['cta_text'] : ( $dynamic_data['cta_text'] ?? '' ),
			'cta_link'    => ! empty( $slide['cta_link'] ) ? $slide['cta_link'] : ( $dynamic_data['cta_link'] ?? '' ),
			'text_align'  => $slide['text_align'] ?? 'left',
			'meta'        => $dynamic_data['meta'] ?? array(),
		);
	}

	// ==========================================================================
	// DYNAMIC SLIDE DATA CALLBACKS
	// ==========================================================================

	/**
	 * Get reflection slide data.
	 */
	public function get_reflection_slide_data(): ?array {
		if ( ! post_type_exists( 'parish_reflection' ) ) {
			return null;
		}

		$posts = get_posts( array(
			'post_type'      => 'parish_reflection',
			'posts_per_page' => 1,
			'post_status'    => 'publish',
		) );

		if ( empty( $posts ) ) {
			return null;
		}

		$post    = $posts[0];
		$excerpt = wp_trim_words( $post->post_content, 25 );

		return array(
			'title'       => __( 'Weekly Reflection', 'parish-core' ),
			'subtitle'    => $post->post_title,
			'description' => $excerpt,
			'cta_text'    => __( 'Read Reflection', 'parish-core' ),
			'cta_link'    => get_permalink( $post->ID ),
			'image'       => get_the_post_thumbnail_url( $post->ID, 'full' ),
			'meta'        => array(
				'post_id'   => $post->ID,
				'post_date' => get_the_date( '', $post ),
			),
		);
	}

	/**
	 * Get feast day slide data.
	 */
	public function get_feast_day_slide_data(): ?array {
		if ( ! class_exists( 'Parish_Readings' ) ) {
			return null;
		}

		$readings = Parish_Readings::instance();
		$data     = $readings->get_reading( 'feast_day_details' );

		if ( empty( $data['celebrations'] ) ) {
			return null;
		}

		$celebration = $data['celebrations'][0];
		$title       = $celebration['title'] ?? '';
		$colour      = $celebration['colour'] ?? 'green';
		$rank        = $celebration['rank'] ?? '';

		return array(
			'title'       => __( 'Today\'s Feast', 'parish-core' ),
			'subtitle'    => $title,
			'description' => ! empty( $rank ) ? ucwords( str_replace( '_', ' ', $rank ) ) : '',
			'cta_text'    => __( 'View Readings', 'parish-core' ),
			'cta_link'    => '',
			'meta'        => array(
				'liturgical_color' => $colour,
				'rank'             => $rank,
			),
		);
	}

	/**
	 * Get daily readings slide data.
	 */
	public function get_daily_readings_slide_data(): ?array {
		if ( ! class_exists( 'Parish_Readings' ) ) {
			return null;
		}

		$readings = Parish_Readings::instance();
		$data     = $readings->get_reading( 'mass_reading_details' );

		if ( empty( $data ) ) {
			return null;
		}

		$content = $data['content'] ?? $data;
		$gospel  = '';

		if ( ! empty( $content['gospel'] ) ) {
			$gospel = wp_trim_words( wp_strip_all_tags( $content['gospel'] ), 20 );
		}

		return array(
			'title'       => __( 'Today\'s Readings', 'parish-core' ),
			'subtitle'    => current_time( 'l, F j' ),
			'description' => $gospel,
			'cta_text'    => __( 'Read Full Readings', 'parish-core' ),
			'cta_link'    => '',
			'meta'        => array(
				'date' => current_time( 'Y-m-d' ),
			),
		);
	}

	/**
	 * Get liturgical season slide data.
	 */
	public function get_liturgical_season_slide_data(): ?array {
		if ( class_exists( 'Parish_Readings' ) ) {
			$readings = Parish_Readings::instance();
			$data     = $readings->get_reading( 'liturgy_day' );
		} else {
			$data = array();
		}

		if ( empty( $data ) ) {
			// Fallback to calculating.
			$season = $this->calculate_liturgical_season();
			$data   = array( 'season' => $season );
		}

		$season = $data['season'] ?? 'Ordinary Time';

		$season_descriptions = array(
			'Advent'        => __( 'Prepare the way of the Lord', 'parish-core' ),
			'Christmas'     => __( 'The Word became flesh and dwelt among us', 'parish-core' ),
			'Lent'          => __( 'A time for prayer, fasting, and almsgiving', 'parish-core' ),
			'Easter'        => __( 'He is Risen! Alleluia!', 'parish-core' ),
			'Ordinary Time' => __( 'Growing in faith day by day', 'parish-core' ),
		);

		return array(
			'title'       => $season,
			'subtitle'    => $season_descriptions[ $season ] ?? '',
			'description' => '',
			'cta_text'    => __( 'Learn More', 'parish-core' ),
			'cta_link'    => '',
			'meta'        => array(
				'season'        => $season,
				'sunday_cycle'  => $data['sunday-cycle'] ?? '',
				'weekday_cycle' => $data['weekday-cycle'] ?? '',
			),
		);
	}

	/**
	 * Get rosary slide data.
	 */
	public function get_rosary_slide_data(): ?array {
		$series = '';

		if ( class_exists( 'Parish_Readings' ) ) {
			$readings = Parish_Readings::instance();
			$data     = $readings->get_reading( 'liturgy_day' );
			$series   = $data['rosary-series'] ?? '';
		}

		if ( empty( $series ) ) {
			// Fallback calculation.
			$day_of_week = current_time( 'l' );
			$schedule    = array(
				'Sunday'    => 'Glorious',
				'Monday'    => 'Joyful',
				'Tuesday'   => 'Sorrowful',
				'Wednesday' => 'Glorious',
				'Thursday'  => 'Luminous',
				'Friday'    => 'Sorrowful',
				'Saturday'  => 'Joyful',
			);
			$series = $schedule[ $day_of_week ] ?? 'Joyful';
		}

		$mystery_descriptions = array(
			'Joyful'    => __( 'Meditate on the joy of Christ\'s coming', 'parish-core' ),
			'Sorrowful' => __( 'Contemplate the Passion of Our Lord', 'parish-core' ),
			'Glorious'  => __( 'Celebrate the Resurrection and glory', 'parish-core' ),
			'Luminous'  => __( 'Reflect on Christ\'s public ministry', 'parish-core' ),
		);

		return array(
			'title'       => __( 'Today\'s Rosary', 'parish-core' ),
			'subtitle'    => $series . ' ' . __( 'Mysteries', 'parish-core' ),
			'description' => $mystery_descriptions[ $series ] ?? '',
			'cta_text'    => __( 'Pray the Rosary', 'parish-core' ),
			'cta_link'    => '',
			'meta'        => array(
				'series' => $series,
			),
		);
	}

	/**
	 * Get saint of the day slide data.
	 */
	public function get_saint_slide_data(): ?array {
		if ( ! class_exists( 'Parish_Readings' ) ) {
			return null;
		}

		$readings = Parish_Readings::instance();
		$data     = $readings->get_reading( 'saint_of_the_day' );

		if ( empty( $data ) ) {
			return null;
		}

		$content = $data['content'] ?? ( $data['text'] ?? '' );
		$excerpt = is_string( $content ) ? wp_trim_words( wp_strip_all_tags( $content ), 25 ) : '';

		return array(
			'title'       => __( 'Saint of the Day', 'parish-core' ),
			'subtitle'    => current_time( 'F j' ),
			'description' => $excerpt,
			'cta_text'    => __( 'Learn More', 'parish-core' ),
			'cta_link'    => '',
		);
	}

	/**
	 * Get newsletter slide data.
	 */
	public function get_newsletter_slide_data(): ?array {
		if ( ! post_type_exists( 'parish_newsletter' ) ) {
			return null;
		}

		$posts = get_posts( array(
			'post_type'      => 'parish_newsletter',
			'posts_per_page' => 1,
			'post_status'    => 'publish',
		) );

		if ( empty( $posts ) ) {
			return null;
		}

		$post = $posts[0];

		return array(
			'title'       => __( 'Parish Newsletter', 'parish-core' ),
			'subtitle'    => $post->post_title,
			'description' => get_the_date( 'F j, Y', $post ),
			'cta_text'    => __( 'Read Newsletter', 'parish-core' ),
			'cta_link'    => get_permalink( $post->ID ),
			'image'       => get_the_post_thumbnail_url( $post->ID, 'full' ),
			'meta'        => array(
				'post_id' => $post->ID,
			),
		);
	}

	/**
	 * Get event slide data.
	 */
	public function get_event_slide_data(): ?array {
		$settings = Parish_Core::get_settings();
		$events   = json_decode( $settings['parish_events'] ?? '[]', true );

		if ( empty( $events ) ) {
			return null;
		}

		$today    = current_time( 'Y-m-d' );
		$upcoming = array_filter( $events, function ( $e ) use ( $today ) {
			return ( $e['date'] ?? '' ) >= $today;
		} );

		if ( empty( $upcoming ) ) {
			return null;
		}

		usort( $upcoming, function ( $a, $b ) {
			return strcmp( $a['date'] ?? '', $b['date'] ?? '' );
		} );

		$event = $upcoming[0];

		$event_date = ! empty( $event['date'] ) ? date_i18n( 'l, F j', strtotime( $event['date'] ) ) : '';

		return array(
			'title'       => __( 'Upcoming Event', 'parish-core' ),
			'subtitle'    => $event['title'] ?? '',
			'description' => $event_date . ( ! empty( $event['time'] ) ? ' at ' . $event['time'] : '' ),
			'cta_text'    => __( 'View Events', 'parish-core' ),
			'cta_link'    => '',
			'meta'        => array(
				'event_id'   => $event['id'] ?? '',
				'event_date' => $event['date'] ?? '',
			),
		);
	}

	/**
	 * Calculate liturgical season (fallback).
	 */
	private function calculate_liturgical_season(): string {
		$now   = new \DateTime( 'now', new \DateTimeZone( wp_timezone_string() ) );
		$year  = (int) $now->format( 'Y' );
		$month = (int) $now->format( 'n' );
		$day   = (int) $now->format( 'j' );

		$easter = new \DateTime( "{$year}-03-21" );
		$easter->modify( '+' . easter_days( $year ) . ' days' );

		$ash_wed = clone $easter;
		$ash_wed->modify( '-46 days' );

		$pentecost = clone $easter;
		$pentecost->modify( '+49 days' );

		$christmas = new \DateTime( "{$year}-12-25" );
		$advent    = clone $christmas;
		$dow       = (int) $christmas->format( 'N' );
		$advent->modify( '-' . ( 21 + ( $dow % 7 ) ) . ' days' );

		if ( $now >= $advent && $month === 12 && $day < 25 ) {
			return 'Advent';
		}
		if ( ( $month === 12 && $day >= 25 ) || ( $month === 1 && $day <= 13 ) ) {
			return 'Christmas';
		}
		if ( $now >= $ash_wed && $now < $easter ) {
			return 'Lent';
		}
		if ( $now >= $easter && $now <= $pentecost ) {
			return 'Easter';
		}

		return 'Ordinary Time';
	}

	// ==========================================================================
	// FRONTEND RENDERING
	// ==========================================================================

	/**
	 * Register Gutenberg block.
	 */
	public function register_block(): void {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		register_block_type( 'parish-core/slider', array(
			'render_callback' => array( $this, 'render_slider' ),
			'attributes'      => array(
				'className' => array( 'type' => 'string', 'default' => '' ),
			),
		) );
	}

	/**
	 * Render slider shortcode.
	 *
	 * @param array  $atts    Shortcode attributes.
	 * @param string $content Shortcode content.
	 */
	public function render_slider( $atts = array(), $content = '' ): string {
		$atts = shortcode_atts( array(
			'class' => '',
		), $atts, 'parish_slider' );

		$settings = $this->get_slider_settings();

		if ( ! $settings['enabled'] ) {
			return '';
		}

		$slides = $this->get_slides();

		if ( empty( $slides ) ) {
			return '';
		}

		// Build CSS variables for customization.
		$css_vars = sprintf(
			'--slider-height-desktop: %dpx; --slider-height-tablet: %dpx; --slider-height-mobile: %dpx; --slider-overlay-color: %s; --slider-overlay-opacity: %s;',
			$settings['height_desktop'],
			$settings['height_tablet'],
			$settings['height_mobile'],
			esc_attr( $settings['overlay_color'] ),
			esc_attr( $settings['overlay_opacity'] )
		);

		$slider_class = 'parish-slider';
		if ( ! empty( $atts['class'] ) ) {
			$slider_class .= ' ' . sanitize_html_class( $atts['class'] );
		}
		if ( $settings['overlay_gradient'] ) {
			$slider_class .= ' has-gradient';
		}

		// Data attributes for JS.
		$data_attrs = sprintf(
			'data-autoplay="%s" data-speed="%d" data-transition="%d" data-pause-hover="%s"',
			$settings['autoplay'] ? 'true' : 'false',
			$settings['autoplay_speed'],
			$settings['transition_speed'],
			$settings['pause_on_hover'] ? 'true' : 'false'
		);

		ob_start();
		?>
		<div class="<?php echo esc_attr( $slider_class ); ?>" style="<?php echo esc_attr( $css_vars ); ?>" <?php echo $data_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> tabindex="0">
			<div class="parish-slider__track">
				<?php foreach ( $slides as $index => $slide ) : ?>
					<div class="parish-slider__slide <?php echo $index === 0 ? 'is-active' : ''; ?>" 
						 data-index="<?php echo esc_attr( $index ); ?>"
						 data-align="<?php echo esc_attr( $slide['text_align'] ?? 'left' ); ?>">
						
						<?php if ( ! empty( $slide['image'] ) ) : ?>
							<div class="parish-slider__image" style="background-image: url('<?php echo esc_url( $slide['image'] ); ?>')"></div>
						<?php else : ?>
							<div class="parish-slider__image parish-slider__image--placeholder"></div>
						<?php endif; ?>

						<div class="parish-slider__overlay"></div>

						<div class="parish-slider__content">
							<div class="parish-slider__text">
								<?php if ( ! empty( $slide['title'] ) ) : ?>
									<h2 class="parish-slider__title"><?php echo esc_html( $slide['title'] ); ?></h2>
								<?php endif; ?>

								<?php if ( ! empty( $slide['subtitle'] ) ) : ?>
									<p class="parish-slider__subtitle"><?php echo esc_html( $slide['subtitle'] ); ?></p>
								<?php endif; ?>

								<?php if ( ! empty( $slide['description'] ) ) : ?>
									<p class="parish-slider__description"><?php echo esc_html( $slide['description'] ); ?></p>
								<?php endif; ?>

								<?php if ( ! empty( $slide['cta_text'] ) ) : ?>
									<a href="<?php echo esc_url( $slide['cta_link'] ?: '#' ); ?>" class="parish-slider__cta">
										<?php echo esc_html( $slide['cta_text'] ); ?>
										<svg class="parish-slider__cta-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
											<path d="M9 18l6-6-6-6"/>
										</svg>
									</a>
								<?php endif; ?>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<?php if ( $settings['show_arrows'] && count( $slides ) > 1 ) : ?>
				<button class="parish-slider__arrow parish-slider__arrow--prev" aria-label="<?php esc_attr_e( 'Previous slide', 'parish-core' ); ?>">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="M15 18l-6-6 6-6"/>
					</svg>
				</button>
				<button class="parish-slider__arrow parish-slider__arrow--next" aria-label="<?php esc_attr_e( 'Next slide', 'parish-core' ); ?>">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="M9 18l6-6-6-6"/>
					</svg>
				</button>
			<?php endif; ?>

			<?php if ( $settings['show_dots'] && count( $slides ) > 1 ) : ?>
				<div class="parish-slider__dots">
					<?php foreach ( $slides as $index => $slide ) : ?>
						<button class="parish-slider__dot <?php echo $index === 0 ? 'is-active' : ''; ?>" 
								data-index="<?php echo esc_attr( $index ); ?>"
								aria-label="<?php printf( esc_attr__( 'Go to slide %d', 'parish-core' ), $index + 1 ); ?>">
						</button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}
}
