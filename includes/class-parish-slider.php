<?php
/**
 * Parish Hero Slider Module
 *
 * Provides a hybrid slider with manual and dynamic slides.
 * Dynamic slides auto-populate from Reflections, Feast Days, Readings,
 * Death Notices, Baptisms, Weddings, Churches, Schools, Groups, etc.
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
	 * Liturgical color hex values.
	 *
	 * @var array
	 */
	private array $liturgical_colors = array(
		'green'  => '#008000',
		'white'  => '#FFFFFF',
		'red'    => '#C41E3A',
		'violet' => '#8B008B',
		'purple' => '#663399',
		'rose'   => '#FF007F',
		'pink'   => '#FF69B4',
		'gold'   => '#FFD700',
		'black'  => '#000000',
	);

	/**
	 * Rosary mystery data with default images.
	 *
	 * @var array
	 */
	private array $rosary_mysteries = array(
		'Joyful' => array(
			'name'        => 'Joyful Mysteries',
			'description' => 'Meditate on the joy of Christ\'s coming',
			'color'       => '#FFD700',
		),
		'Sorrowful' => array(
			'name'        => 'Sorrowful Mysteries',
			'description' => 'Contemplate the Passion of Our Lord',
			'color'       => '#8B0000',
		),
		'Glorious' => array(
			'name'        => 'Glorious Mysteries',
			'description' => 'Celebrate the Resurrection and glory',
			'color'       => '#FFD700',
		),
		'Luminous' => array(
			'name'        => 'Luminous Mysteries',
			'description' => 'Reflect on Christ\'s public ministry',
			'color'       => '#4169E1',
		),
	);

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
	 * Includes all CPT sources plus liturgical sources.
	 */
	private function setup_dynamic_sources(): void {
		$this->dynamic_sources = array(
			// === LITURGICAL SOURCES ===
			'feast_day' => array(
				'name'        => __( 'Feast of the Day', 'parish-core' ),
				'description' => __( 'Shows today\'s liturgical feast with color overlay', 'parish-core' ),
				'icon'        => 'calendar-alt',
				'category'    => 'liturgical',
				'callback'    => array( $this, 'get_feast_day_slide_data' ),
			),
			'daily_readings' => array(
				'name'        => __( 'Daily Readings', 'parish-core' ),
				'description' => __( 'Highlights today\'s Mass readings', 'parish-core' ),
				'icon'        => 'book',
				'category'    => 'liturgical',
				'callback'    => array( $this, 'get_daily_readings_slide_data' ),
			),
			'liturgical_season' => array(
				'name'        => __( 'Liturgical Season', 'parish-core' ),
				'description' => __( 'Shows current season with cycle information', 'parish-core' ),
				'icon'        => 'admin-site',
				'category'    => 'liturgical',
				'callback'    => array( $this, 'get_liturgical_season_slide_data' ),
			),
			'rosary_today' => array(
				'name'        => __( 'Today\'s Rosary', 'parish-core' ),
				'description' => __( 'Shows which rosary mysteries to pray today', 'parish-core' ),
				'icon'        => 'heart',
				'category'    => 'liturgical',
				'callback'    => array( $this, 'get_rosary_slide_data' ),
				'supports_custom_images' => true,
			),
			'saint_of_day' => array(
				'name'        => __( 'Saint of the Day', 'parish-core' ),
				'description' => __( 'Features today\'s saint', 'parish-core' ),
				'icon'        => 'star-filled',
				'category'    => 'liturgical',
				'callback'    => array( $this, 'get_saint_slide_data' ),
			),

			// === CPT SOURCES ===
			'reflection' => array(
				'name'        => __( 'Latest Reflection', 'parish-core' ),
				'description' => __( 'Shows the most recent reflection post', 'parish-core' ),
				'icon'        => 'format-quote',
				'category'    => 'content',
				'callback'    => array( $this, 'get_reflection_slide_data' ),
				'feature'     => 'reflections',
			),
			'latest_newsletter' => array(
				'name'        => __( 'Latest Newsletter', 'parish-core' ),
				'description' => __( 'Promotes the most recent parish newsletter', 'parish-core' ),
				'icon'        => 'media-document',
				'category'    => 'content',
				'callback'    => array( $this, 'get_newsletter_slide_data' ),
				'feature'     => 'newsletters',
			),
			'latest_news' => array(
				'name'        => __( 'Latest News Post', 'parish-core' ),
				'description' => __( 'Shows the most recent WordPress post', 'parish-core' ),
				'icon'        => 'megaphone',
				'category'    => 'content',
				'callback'    => array( $this, 'get_news_slide_data' ),
				'feature'     => 'news',
			),
			'upcoming_event' => array(
				'name'        => __( 'Next Event', 'parish-core' ),
				'description' => __( 'Shows the next upcoming parish event', 'parish-core' ),
				'icon'        => 'calendar',
				'category'    => 'content',
				'callback'    => array( $this, 'get_event_slide_data' ),
				'feature'     => 'events',
			),
			'recent_death_notice' => array(
				'name'        => __( 'Recent Death Notice', 'parish-core' ),
				'description' => __( 'Shows the most recent death notice', 'parish-core' ),
				'icon'        => 'heart',
				'category'    => 'sacraments',
				'callback'    => array( $this, 'get_death_notice_slide_data' ),
				'feature'     => 'death_notices',
			),
			'recent_baptism' => array(
				'name'        => __( 'Recent Baptism', 'parish-core' ),
				'description' => __( 'Celebrates the most recent baptism', 'parish-core' ),
				'icon'        => 'groups',
				'category'    => 'sacraments',
				'callback'    => array( $this, 'get_baptism_slide_data' ),
				'feature'     => 'baptism_notices',
			),
			'recent_wedding' => array(
				'name'        => __( 'Recent Wedding', 'parish-core' ),
				'description' => __( 'Celebrates the most recent wedding', 'parish-core' ),
				'icon'        => 'heart',
				'category'    => 'sacraments',
				'callback'    => array( $this, 'get_wedding_slide_data' ),
				'feature'     => 'wedding_notices',
			),
			'featured_church' => array(
				'name'        => __( 'Featured Church', 'parish-core' ),
				'description' => __( 'Highlights a parish church', 'parish-core' ),
				'icon'        => 'building',
				'category'    => 'places',
				'callback'    => array( $this, 'get_church_slide_data' ),
				'feature'     => 'churches',
			),
			'featured_school' => array(
				'name'        => __( 'Featured School', 'parish-core' ),
				'description' => __( 'Highlights a parish school', 'parish-core' ),
				'icon'        => 'welcome-learn-more',
				'category'    => 'places',
				'callback'    => array( $this, 'get_school_slide_data' ),
				'feature'     => 'schools',
			),
			'featured_group' => array(
				'name'        => __( 'Featured Group', 'parish-core' ),
				'description' => __( 'Highlights a parish group or ministry', 'parish-core' ),
				'icon'        => 'groups',
				'category'    => 'community',
				'callback'    => array( $this, 'get_group_slide_data' ),
				'feature'     => 'groups',
			),
			'featured_gallery' => array(
				'name'        => __( 'Latest Gallery', 'parish-core' ),
				'description' => __( 'Shows the most recent photo gallery', 'parish-core' ),
				'icon'        => 'format-gallery',
				'category'    => 'content',
				'callback'    => array( $this, 'get_gallery_slide_data' ),
				'feature'     => 'gallery',
			),
			'featured_prayer' => array(
				'name'        => __( 'Prayer of the Day', 'parish-core' ),
				'description' => __( 'Features a prayer from the prayer directory', 'parish-core' ),
				'icon'        => 'book-alt',
				'category'    => 'liturgical',
				'callback'    => array( $this, 'get_prayer_slide_data' ),
				'feature'     => 'prayers',
			),
		);
	}

	/**
	 * Get available dynamic sources (filtered by enabled features).
	 */
	public function get_dynamic_sources(): array {
		$sources = array();

		foreach ( $this->dynamic_sources as $key => $source ) {
			// Check if feature is enabled (if required).
			if ( isset( $source['feature'] ) && ! Parish_Core::is_feature_enabled( $source['feature'] ) ) {
				continue;
			}
			$sources[ $key ] = $source;
		}

		return $sources;
	}

	/**
	 * Get slider settings with defaults.
	 */
	public function get_slider_settings(): array {
		$defaults = array(
			'enabled'            => true,
			'autoplay'           => true,
			'autoplay_speed'     => 5000,
			'transition_speed'   => 1000,
			'show_arrows'        => true,
			'show_dots'          => true,
			'pause_on_hover'     => true,
			'height_desktop'     => 700,
			'height_tablet'      => 500,
			'height_mobile'      => 400,
			'overlay_color'      => '#4A8391',
			'overlay_opacity'    => 0.7,
			'overlay_gradient'   => true,
			'use_liturgical_color' => false,
			'cta_color'          => '#d97706',
			'cta_hover_color'    => '#b45309',
			'rosary_images'      => array(
				'Joyful'    => array( 'id' => 0, 'url' => '' ),
				'Sorrowful' => array( 'id' => 0, 'url' => '' ),
				'Glorious'  => array( 'id' => 0, 'url' => '' ),
				'Luminous'  => array( 'id' => 0, 'url' => '' ),
			),
			'season_images'      => array(
				'Advent'        => array( 'id' => 0, 'url' => '' ),
				'Christmas'     => array( 'id' => 0, 'url' => '' ),
				'Lent'          => array( 'id' => 0, 'url' => '' ),
				'Easter'        => array( 'id' => 0, 'url' => '' ),
				'Ordinary Time' => array( 'id' => 0, 'url' => '' ),
			),
			'slides'             => array(),
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
				$dynamic_slide = $this->prepare_dynamic_slide( $slide, $settings );
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
			'id'             => $slide['id'] ?? uniqid(),
			'type'           => 'manual',
			'image'          => $image_url,
			'image_fit'      => $slide['image_fit'] ?? 'cover',
			'image_position' => $slide['image_position'] ?? 'center',
			'display_mode'   => $slide['display_mode'] ?? 'full',
			'title'          => $slide['title'] ?? '',
			'subtitle'       => $slide['subtitle'] ?? '',
			'description'    => $slide['description'] ?? '',
			'cta_text'       => $slide['cta_text'] ?? '',
			'cta_link'       => $slide['cta_link'] ?? '',
			'text_align'     => $slide['text_align'] ?? 'left',
		);
	}

	/**
	 * Prepare a dynamic slide for output.
	 *
	 * @param array $slide    Slide configuration.
	 * @param array $settings Global slider settings.
	 */
	private function prepare_dynamic_slide( array $slide, array $settings ): ?array {
		$source = $slide['source'] ?? '';

		if ( ! isset( $this->dynamic_sources[ $source ] ) ) {
			return null;
		}

		$source_config = $this->dynamic_sources[ $source ];

		// Check feature availability.
		if ( isset( $source_config['feature'] ) && ! Parish_Core::is_feature_enabled( $source_config['feature'] ) ) {
			return null;
		}

		$callback = $source_config['callback'];

		if ( ! is_callable( $callback ) ) {
			return null;
		}

		// Pass settings for rosary/season images.
		$dynamic_data = call_user_func( $callback, $settings );

		if ( empty( $dynamic_data ) ) {
			return null;
		}

		// Determine image: custom override > rosary image > dynamic data.
		$image_url = '';
		if ( ! empty( $slide['image_id'] ) ) {
			$image_url = wp_get_attachment_image_url( $slide['image_id'], 'full' );
		} elseif ( ! empty( $slide['image_url'] ) ) {
			$image_url = $slide['image_url'];
		} elseif ( ! empty( $dynamic_data['image'] ) ) {
			$image_url = $dynamic_data['image'];
		}

		// Determine overlay color (liturgical color support).
		$overlay_color = null;
		if ( ! empty( $dynamic_data['liturgical_color'] ) ) {
			$overlay_color = $dynamic_data['liturgical_color'];
		}

		return array(
			'id'              => $slide['id'] ?? uniqid(),
			'type'            => 'dynamic',
			'source'          => $source,
			'image'           => $image_url,
			'image_fit'       => $slide['image_fit'] ?? 'cover',
			'image_position'  => $slide['image_position'] ?? 'center',
			'display_mode'    => $slide['display_mode'] ?? 'full',
			'title'           => ! empty( $slide['title_override'] ) ? $slide['title_override'] : ( $dynamic_data['title'] ?? '' ),
			'subtitle'        => ! empty( $slide['subtitle_override'] ) ? $slide['subtitle_override'] : ( $dynamic_data['subtitle'] ?? '' ),
			'description'     => ! empty( $slide['description_override'] ) ? $slide['description_override'] : ( $dynamic_data['description'] ?? '' ),
			'cta_text'        => ! empty( $slide['cta_text'] ) ? $slide['cta_text'] : ( $dynamic_data['cta_text'] ?? '' ),
			'cta_link'        => ! empty( $slide['cta_link'] ) ? $slide['cta_link'] : ( $dynamic_data['cta_link'] ?? '' ),
			'text_align'      => $slide['text_align'] ?? 'left',
			'overlay_color'   => $overlay_color,
			'meta'            => $dynamic_data['meta'] ?? array(),
		);
	}

	// ==========================================================================
	// LITURGICAL DYNAMIC SLIDE DATA CALLBACKS
	// ==========================================================================

	/**
	 * Get feast day slide data with liturgical color.
	 */
	public function get_feast_day_slide_data( array $settings = array() ): ?array {
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
		$colour      = strtolower( $celebration['colour'] ?? 'green' );
		$rank        = $celebration['rank'] ?? '';

		// Get hex color for overlay.
		$color_hex = $this->liturgical_colors[ $colour ] ?? '#4A8391';

		// Format rank nicely.
		$rank_display = '';
		if ( ! empty( $rank ) ) {
			$rank_display = ucwords( str_replace( '_', ' ', $rank ) );
		}

		return array(
			'title'           => __( 'Today\'s Feast', 'parish-core' ),
			'subtitle'        => $title,
			'description'     => sprintf(
				__( 'Liturgical Colour: %s | Rank: %s', 'parish-core' ),
				ucfirst( $colour ),
				$rank_display
			),
			'cta_text'        => __( 'View Readings', 'parish-core' ),
			'cta_link'        => '',
			'liturgical_color' => $color_hex,
			'meta'            => array(
				'colour_name' => $colour,
				'colour_hex'  => $color_hex,
				'rank'        => $rank,
			),
		);
	}

	/**
	 * Get daily readings slide data.
	 */
	public function get_daily_readings_slide_data( array $settings = array() ): ?array {
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
	 * Get liturgical season slide data with cycle information.
	 */
	public function get_liturgical_season_slide_data( array $settings = array() ): ?array {
		$season        = '';
		$sunday_cycle  = '';
		$weekday_cycle = '';
		$loth_volume   = '';

		if ( class_exists( 'Parish_Readings' ) ) {
			$readings = Parish_Readings::instance();
			$data     = $readings->get_reading( 'liturgy_day' );

			if ( ! empty( $data ) ) {
				$season        = $data['season'] ?? '';
				$sunday_cycle  = $data['sunday-cycle'] ?? '';
				$weekday_cycle = $data['weekday-cycle'] ?? '';
				$loth_volume   = $data['loth-volume'] ?? '';
			}
		}

		if ( empty( $season ) ) {
			$season = $this->calculate_liturgical_season();
		}

		// Calculate cycles if not provided.
		if ( empty( $sunday_cycle ) ) {
			$sunday_cycle = $this->calculate_sunday_cycle();
		}
		if ( empty( $weekday_cycle ) ) {
			$weekday_cycle = $this->calculate_weekday_cycle();
		}

		$season_descriptions = array(
			'Advent'        => __( 'Prepare the way of the Lord', 'parish-core' ),
			'Christmas'     => __( 'The Word became flesh and dwelt among us', 'parish-core' ),
			'Lent'          => __( 'A time for prayer, fasting, and almsgiving', 'parish-core' ),
			'Easter'        => __( 'He is Risen! Alleluia!', 'parish-core' ),
			'Ordinary Time' => __( 'Growing in faith day by day', 'parish-core' ),
		);

		$season_colors = array(
			'Advent'        => $this->liturgical_colors['violet'],
			'Christmas'     => $this->liturgical_colors['white'],
			'Lent'          => $this->liturgical_colors['violet'],
			'Easter'        => $this->liturgical_colors['white'],
			'Ordinary Time' => $this->liturgical_colors['green'],
		);

		// Build cycle info string.
		$cycle_info = '';
		if ( $sunday_cycle || $weekday_cycle ) {
			$parts = array();
			if ( $sunday_cycle ) {
				$parts[] = sprintf( __( 'Year %s', 'parish-core' ), $sunday_cycle );
			}
			if ( $weekday_cycle ) {
				$parts[] = sprintf( __( 'Cycle %s', 'parish-core' ), $weekday_cycle );
			}
			$cycle_info = implode( ' • ', $parts );
		}

		// Check for custom season image.
		$season_images = $settings['season_images'] ?? array();
		$custom_image  = '';

		if ( ! empty( $season_images[ $season ]['url'] ) ) {
			$custom_image = $season_images[ $season ]['url'];
		} elseif ( ! empty( $season_images[ $season ]['id'] ) ) {
			$custom_image = wp_get_attachment_image_url( $season_images[ $season ]['id'], 'full' );
		}

		return array(
			'title'           => $season,
			'subtitle'        => $season_descriptions[ $season ] ?? '',
			'description'     => $cycle_info,
			'cta_text'        => __( 'Learn More', 'parish-core' ),
			'cta_link'        => '',
			'image'           => $custom_image,
			'liturgical_color' => $season_colors[ $season ] ?? null,
			'meta'            => array(
				'season'        => $season,
				'sunday_cycle'  => $sunday_cycle,
				'weekday_cycle' => $weekday_cycle,
				'loth_volume'   => $loth_volume,
			),
		);
	}

	/**
	 * Get rosary slide data with custom image support.
	 *
	 * Uses Parish_Rosary_Schedule for reliable mystery set determination
	 * instead of external API which can be inconsistent.
	 */
	public function get_rosary_slide_data( array $settings = array() ): ?array {
		$series = '';

		// Use Parish_Rosary_Schedule for reliable rosary series determination.
		if ( class_exists( 'Parish_Rosary_Schedule' ) ) {
			$series = ucfirst( Parish_Rosary_Schedule::get_todays_mystery_set() );
		}

		if ( empty( $series ) ) {
			// Fallback calculation if class not available.
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

		$mystery_info = $this->rosary_mysteries[ $series ] ?? null;

		if ( ! $mystery_info ) {
			return null;
		}

		// Check for custom rosary image.
		$rosary_images = $settings['rosary_images'] ?? array();
		$custom_image  = '';

		if ( ! empty( $rosary_images[ $series ]['url'] ) ) {
			$custom_image = $rosary_images[ $series ]['url'];
		} elseif ( ! empty( $rosary_images[ $series ]['id'] ) ) {
			$custom_image = wp_get_attachment_image_url( $rosary_images[ $series ]['id'], 'full' );
		}

		return array(
			'title'       => __( 'Today\'s Rosary', 'parish-core' ),
			'subtitle'    => $mystery_info['name'],
			'description' => $mystery_info['description'],
			'cta_text'    => __( 'Pray the Rosary', 'parish-core' ),
			'cta_link'    => '',
			'image'       => $custom_image,
			'meta'        => array(
				'series' => $series,
				'color'  => $mystery_info['color'],
			),
		);
	}

	/**
	 * Get saint of the day slide data.
	 */
	public function get_saint_slide_data( array $settings = array() ): ?array {
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

	// ==========================================================================
	// CPT DYNAMIC SLIDE DATA CALLBACKS
	// ==========================================================================

	/**
	 * Get reflection slide data.
	 */
	public function get_reflection_slide_data( array $settings = array() ): ?array {
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
	 * Get newsletter slide data.
	 */
	public function get_newsletter_slide_data( array $settings = array() ): ?array {
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
			'meta'        => array( 'post_id' => $post->ID ),
		);
	}

	/**
	 * Get news slide data.
	 */
	public function get_news_slide_data( array $settings = array() ): ?array {
		if ( ! post_type_exists( 'post' ) ) {
			return null;
		}

		$posts = get_posts( array(
			'post_type'      => 'post',
			'posts_per_page' => 1,
			'post_status'    => 'publish',
		) );

		if ( empty( $posts ) ) {
			return null;
		}

		$post    = $posts[0];
		$excerpt = wp_trim_words( $post->post_content, 20 );

		return array(
			'title'       => __( 'Parish News', 'parish-core' ),
			'subtitle'    => $post->post_title,
			'description' => $excerpt,
			'cta_text'    => __( 'Read More', 'parish-core' ),
			'cta_link'    => get_permalink( $post->ID ),
			'image'       => get_the_post_thumbnail_url( $post->ID, 'full' ),
			'meta'        => array( 'post_id' => $post->ID ),
		);
	}

	/**
	 * Get event slide data.
	 */
	public function get_event_slide_data( array $settings = array() ): ?array {
		$parish_settings = Parish_Core::get_settings();
		$events          = json_decode( $parish_settings['parish_events'] ?? '[]', true );

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

		$event      = $upcoming[0];
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
	 * Get death notice slide data.
	 */
	public function get_death_notice_slide_data( array $settings = array() ): ?array {
		if ( ! post_type_exists( 'parish_death_notice' ) ) {
			return null;
		}

		$posts = get_posts( array(
			'post_type'      => 'parish_death_notice',
			'posts_per_page' => 1,
			'post_status'    => 'publish',
		) );

		if ( empty( $posts ) ) {
			return null;
		}

		$post = $posts[0];

		return array(
			'title'       => __( 'Rest in Peace', 'parish-core' ),
			'subtitle'    => $post->post_title,
			'description' => __( 'Please keep in your prayers', 'parish-core' ),
			'cta_text'    => __( 'View Notice', 'parish-core' ),
			'cta_link'    => get_permalink( $post->ID ),
			'image'       => get_the_post_thumbnail_url( $post->ID, 'full' ),
			'meta'        => array( 'post_id' => $post->ID ),
		);
	}

	/**
	 * Get baptism slide data.
	 */
	public function get_baptism_slide_data( array $settings = array() ): ?array {
		if ( ! post_type_exists( 'parish_baptism' ) ) {
			return null;
		}

		$posts = get_posts( array(
			'post_type'      => 'parish_baptism',
			'posts_per_page' => 1,
			'post_status'    => 'publish',
		) );

		if ( empty( $posts ) ) {
			return null;
		}

		$post = $posts[0];

		return array(
			'title'       => __( 'Welcome to the Church', 'parish-core' ),
			'subtitle'    => $post->post_title,
			'description' => __( 'Newly baptised into our parish family', 'parish-core' ),
			'cta_text'    => __( 'View Notice', 'parish-core' ),
			'cta_link'    => get_permalink( $post->ID ),
			'image'       => get_the_post_thumbnail_url( $post->ID, 'full' ),
			'meta'        => array( 'post_id' => $post->ID ),
		);
	}

	/**
	 * Get wedding slide data.
	 */
	public function get_wedding_slide_data( array $settings = array() ): ?array {
		if ( ! post_type_exists( 'parish_wedding' ) ) {
			return null;
		}

		$posts = get_posts( array(
			'post_type'      => 'parish_wedding',
			'posts_per_page' => 1,
			'post_status'    => 'publish',
		) );

		if ( empty( $posts ) ) {
			return null;
		}

		$post = $posts[0];

		return array(
			'title'       => __( 'Congratulations', 'parish-core' ),
			'subtitle'    => $post->post_title,
			'description' => __( 'Joined in Holy Matrimony', 'parish-core' ),
			'cta_text'    => __( 'View Notice', 'parish-core' ),
			'cta_link'    => get_permalink( $post->ID ),
			'image'       => get_the_post_thumbnail_url( $post->ID, 'full' ),
			'meta'        => array( 'post_id' => $post->ID ),
		);
	}

	/**
	 * Get church slide data.
	 */
	public function get_church_slide_data( array $settings = array() ): ?array {
		if ( ! post_type_exists( 'parish_church' ) ) {
			return null;
		}

		$posts = get_posts( array(
			'post_type'      => 'parish_church',
			'posts_per_page' => 1,
			'post_status'    => 'publish',
			'orderby'        => 'rand',
		) );

		if ( empty( $posts ) ) {
			return null;
		}

		$post    = $posts[0];
		$excerpt = wp_trim_words( $post->post_content, 15 );

		return array(
			'title'       => __( 'Our Churches', 'parish-core' ),
			'subtitle'    => $post->post_title,
			'description' => $excerpt,
			'cta_text'    => __( 'Learn More', 'parish-core' ),
			'cta_link'    => get_permalink( $post->ID ),
			'image'       => get_the_post_thumbnail_url( $post->ID, 'full' ),
			'meta'        => array( 'post_id' => $post->ID ),
		);
	}

	/**
	 * Get school slide data.
	 */
	public function get_school_slide_data( array $settings = array() ): ?array {
		if ( ! post_type_exists( 'parish_school' ) ) {
			return null;
		}

		$posts = get_posts( array(
			'post_type'      => 'parish_school',
			'posts_per_page' => 1,
			'post_status'    => 'publish',
			'orderby'        => 'rand',
		) );

		if ( empty( $posts ) ) {
			return null;
		}

		$post    = $posts[0];
		$excerpt = wp_trim_words( $post->post_content, 15 );

		return array(
			'title'       => __( 'Parish Schools', 'parish-core' ),
			'subtitle'    => $post->post_title,
			'description' => $excerpt,
			'cta_text'    => __( 'Learn More', 'parish-core' ),
			'cta_link'    => get_permalink( $post->ID ),
			'image'       => get_the_post_thumbnail_url( $post->ID, 'full' ),
			'meta'        => array( 'post_id' => $post->ID ),
		);
	}

	/**
	 * Get group slide data.
	 */
	public function get_group_slide_data( array $settings = array() ): ?array {
		if ( ! post_type_exists( 'parish_group' ) ) {
			return null;
		}

		$posts = get_posts( array(
			'post_type'      => 'parish_group',
			'posts_per_page' => 1,
			'post_status'    => 'publish',
			'orderby'        => 'rand',
		) );

		if ( empty( $posts ) ) {
			return null;
		}

		$post    = $posts[0];
		$excerpt = wp_trim_words( $post->post_content, 15 );

		return array(
			'title'       => __( 'Get Involved', 'parish-core' ),
			'subtitle'    => $post->post_title,
			'description' => $excerpt,
			'cta_text'    => __( 'Join Us', 'parish-core' ),
			'cta_link'    => get_permalink( $post->ID ),
			'image'       => get_the_post_thumbnail_url( $post->ID, 'full' ),
			'meta'        => array( 'post_id' => $post->ID ),
		);
	}

	/**
	 * Get gallery slide data.
	 */
	public function get_gallery_slide_data( array $settings = array() ): ?array {
		if ( ! post_type_exists( 'parish_gallery' ) ) {
			return null;
		}

		$posts = get_posts( array(
			'post_type'      => 'parish_gallery',
			'posts_per_page' => 1,
			'post_status'    => 'publish',
		) );

		if ( empty( $posts ) ) {
			return null;
		}

		$post = $posts[0];

		return array(
			'title'       => __( 'Photo Gallery', 'parish-core' ),
			'subtitle'    => $post->post_title,
			'description' => get_the_date( 'F j, Y', $post ),
			'cta_text'    => __( 'View Gallery', 'parish-core' ),
			'cta_link'    => get_permalink( $post->ID ),
			'image'       => get_the_post_thumbnail_url( $post->ID, 'full' ),
			'meta'        => array( 'post_id' => $post->ID ),
		);
	}

	/**
	 * Get prayer slide data.
	 */
	public function get_prayer_slide_data( array $settings = array() ): ?array {
		if ( ! post_type_exists( 'parish_prayer' ) ) {
			return null;
		}

		$posts = get_posts( array(
			'post_type'      => 'parish_prayer',
			'posts_per_page' => 1,
			'post_status'    => 'publish',
			'orderby'        => 'rand',
		) );

		if ( empty( $posts ) ) {
			return null;
		}

		$post    = $posts[0];
		$excerpt = wp_trim_words( $post->post_content, 20 );

		return array(
			'title'       => __( 'Prayer of the Day', 'parish-core' ),
			'subtitle'    => $post->post_title,
			'description' => $excerpt,
			'cta_text'    => __( 'Read Prayer', 'parish-core' ),
			'cta_link'    => get_permalink( $post->ID ),
			'image'       => get_the_post_thumbnail_url( $post->ID, 'full' ),
			'meta'        => array( 'post_id' => $post->ID ),
		);
	}

	// ==========================================================================
	// CALCULATION HELPERS
	// ==========================================================================

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

	/**
	 * Calculate Sunday cycle (A, B, or C).
	 */
	private function calculate_sunday_cycle(): string {
		$year      = (int) current_time( 'Y' );
		$remainder = $year % 3;

		switch ( $remainder ) {
			case 0:
				return 'C';
			case 1:
				return 'A';
			case 2:
				return 'B';
			default:
				return 'A';
		}
	}

	/**
	 * Calculate weekday cycle (I or II).
	 */
	private function calculate_weekday_cycle(): string {
		$year = (int) current_time( 'Y' );
		return ( $year % 2 === 0 ) ? 'II' : 'I';
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
			'--slider-height-desktop: %dpx; --slider-height-tablet: %dpx; --slider-height-mobile: %dpx; --slider-overlay-color: %s; --slider-overlay-opacity: %s; --slider-cta-bg: %s; --slider-cta-hover: %s;',
			$settings['height_desktop'],
			$settings['height_tablet'],
			$settings['height_mobile'],
			esc_attr( $settings['overlay_color'] ),
			esc_attr( $settings['overlay_opacity'] ),
			esc_attr( $settings['cta_color'] ?? '#d97706' ),
			esc_attr( $settings['cta_hover_color'] ?? '#b45309' )
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
				<?php foreach ( $slides as $index => $slide ) :
					// Determine overlay color for this slide.
					$slide_overlay = '';
					if ( ! empty( $slide['overlay_color'] ) && ( $settings['use_liturgical_color'] ?? false ) ) {
						$slide_overlay = '--slide-overlay-color: ' . esc_attr( $slide['overlay_color'] ) . ';';
					}
					
					// Get display options.
					$display_mode   = $slide['display_mode'] ?? 'full';
					$image_fit      = $slide['image_fit'] ?? 'cover';
					$image_position = $slide['image_position'] ?? 'center';
				?>
					<div class="parish-slider__slide <?php echo $index === 0 ? 'is-active' : ''; ?>" 
						 data-index="<?php echo esc_attr( $index ); ?>"
						 data-align="<?php echo esc_attr( $slide['text_align'] ?? 'left' ); ?>"
						 data-display="<?php echo esc_attr( $display_mode ); ?>"
						 data-image-fit="<?php echo esc_attr( $image_fit ); ?>"
						 data-image-position="<?php echo esc_attr( $image_position ); ?>"
						 <?php if ( $slide_overlay ) : ?>style="<?php echo esc_attr( $slide_overlay ); ?>"<?php endif; ?>>
						
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
