<?php
/**
 * Event Time Shortcodes.
 *
 * Provides shortcodes for displaying Mass times, confessions, and other services.
 * Supports multiple layouts and extensive customization options.
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Event Time Shortcodes class.
 */
class Parish_Event_Time_Shortcodes {

	/**
	 * Singleton instance.
	 *
	 * @var Parish_Event_Time_Shortcodes|null
	 */
	private static ?Parish_Event_Time_Shortcodes $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return Parish_Event_Time_Shortcodes
	 */
	public static function instance(): Parish_Event_Time_Shortcodes {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		// Main times shortcodes
		add_shortcode( 'parish_times', array( $this, 'render_times' ) );
		add_shortcode( 'parish_times_today', array( $this, 'render_times_today' ) );
		add_shortcode( 'parish_mass_times', array( $this, 'render_mass_times' ) );
		add_shortcode( 'parish_mass_times_today', array( $this, 'render_mass_times_today' ) );

		// Service-specific shortcodes
		add_shortcode( 'parish_confessions', array( $this, 'render_confessions' ) );
		add_shortcode( 'parish_adoration', array( $this, 'render_adoration' ) );
		add_shortcode( 'parish_services', array( $this, 'render_services' ) );

		// Church schedule shortcode (weekly view grouped by church)
		add_shortcode( 'parish_church_schedule', array( $this, 'render_church_schedule' ) );

		// Enqueue styles
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
	}

	/**
	 * Enqueue frontend styles.
	 */
	public function enqueue_styles(): void {
		wp_register_style(
			'parish-event-times',
			PARISH_CORE_URL . 'assets/css/event-times.css',
			array(),
			PARISH_CORE_VERSION
		);
	}

	/**
	 * Main times shortcode.
	 *
	 * [parish_times church="ID|slug" type="mass|confession|adoration|all" days="14" limit="50"
	 *               layout="list|table|cards|compact" show_readings="1" show_intentions="0"
	 *               show_notes="0" livestream="auto|link|embed" group_by="day|church|none"]
	 *
	 * @param array  $atts    Shortcode attributes.
	 * @param string $content Shortcode content.
	 * @return string Rendered HTML.
	 */
	public function render_times( $atts = array(), $content = '' ): string {
		$atts = shortcode_atts( array(
			'church'           => '',
			'type'             => 'all',
			'days'             => 14,
			'limit'            => 50,
			'layout'           => 'list',
			'show_readings'    => '1',
			'show_intentions'  => '0',
			'show_notes'       => '0',
			'show_livestream'  => '1',
			'show_church'      => '1',
			'livestream'       => 'auto', // auto, link, embed, hide
			'group_by'         => 'day',  // day, church, none
			'class'            => '',
			'heading'          => '',
			'empty_message'    => __( 'No upcoming services scheduled.', 'parish-core' ),
		), $atts, 'parish_times' );

		wp_enqueue_style( 'parish-event-times' );

		// Resolve church ID
		$church_id = $this->resolve_church_id( $atts['church'] );

		// Build filters
		$filters = array();
		if ( $church_id > 0 ) {
			$filters['church_id'] = $church_id;
		}
		if ( 'all' !== $atts['type'] ) {
			$types = array_map( 'trim', explode( ',', $atts['type'] ) );
			$filters['type'] = count( $types ) === 1 ? $types[0] : $types;
		}

		// Generate schedule
		$generator = Parish_Event_Time_Generator::instance();
		$instances = $generator->generate_days( (int) $atts['days'], $filters );

		// Apply limit
		$limit = (int) $atts['limit'];
		if ( $limit > 0 && count( $instances ) > $limit ) {
			$instances = array_slice( $instances, 0, $limit );
		}

		if ( empty( $instances ) ) {
			return $this->render_empty( $atts['empty_message'], $atts['class'] );
		}

		// Group if requested
		$grouped = null;
		switch ( $atts['group_by'] ) {
			case 'day':
				$grouped = Parish_Event_Time_Generator::group_by_date( $instances );
				break;
			case 'church':
				$grouped = Parish_Event_Time_Generator::group_by_church( $instances );
				break;
		}

		// Render based on layout
		switch ( $atts['layout'] ) {
			case 'table':
				return $this->render_table_layout( $instances, $grouped, $atts );
			case 'cards':
				return $this->render_cards_layout( $instances, $grouped, $atts );
			case 'compact':
				return $this->render_compact_layout( $instances, $grouped, $atts );
			case 'list':
			default:
				return $this->render_list_layout( $instances, $grouped, $atts );
		}
	}

	/**
	 * Today's times shortcode.
	 *
	 * [parish_times_today church="ID|slug" type="mass|all" layout="list|table" show_readings="1"]
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string Rendered HTML.
	 */
	public function render_times_today( $atts = array() ): string {
		$atts = shortcode_atts( array(
			'church'          => '',
			'type'            => 'all',
			'layout'          => 'list',
			'show_readings'   => '1',
			'show_intentions' => '0',
			'show_notes'      => '0',
			'show_livestream' => '1',
			'show_church'     => '1',
			'class'           => '',
			'heading'         => sprintf( __( 'Today\'s Schedule - %s', 'parish-core' ), date_i18n( get_option( 'date_format' ) ) ),
			'empty_message'   => __( 'No services scheduled for today.', 'parish-core' ),
		), $atts, 'parish_times_today' );

		$atts['days']     = 1;
		$atts['limit']    = 100;
		$atts['group_by'] = 'none';

		return $this->render_times( $atts );
	}

	/**
	 * Mass times only shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string Rendered HTML.
	 */
	public function render_mass_times( $atts = array() ): string {
		$atts['type'] = 'mass';
		$atts = shortcode_atts( array(
			'church'          => '',
			'type'            => 'mass',
			'days'            => 14,
			'limit'           => 50,
			'layout'          => 'list',
			'show_readings'   => '1',
			'show_intentions' => '0',
			'show_notes'      => '0',
			'show_livestream' => '1',
			'show_church'     => '1',
			'group_by'        => 'day',
			'class'           => '',
			'heading'         => __( 'Mass Times', 'parish-core' ),
			'empty_message'   => __( 'No Masses scheduled.', 'parish-core' ),
		), $atts, 'parish_mass_times' );

		return $this->render_times( $atts );
	}

	/**
	 * Confessions only shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string Rendered HTML.
	 */
	public function render_confessions( $atts = array() ): string {
		$atts['type'] = 'confession';
		$atts = shortcode_atts( array(
			'church'          => '',
			'type'            => 'confession',
			'days'            => 14,
			'limit'           => 50,
			'layout'          => 'list',
			'show_readings'   => '0',
			'show_intentions' => '0',
			'show_notes'      => '1',
			'show_livestream' => '0',
			'show_church'     => '1',
			'group_by'        => 'day',
			'class'           => '',
			'heading'         => __( 'Confession Times', 'parish-core' ),
			'empty_message'   => __( 'No Confessions scheduled.', 'parish-core' ),
		), $atts, 'parish_confessions' );

		return $this->render_times( $atts );
	}

	/**
	 * Adoration only shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string Rendered HTML.
	 */
	public function render_adoration( $atts = array() ): string {
		$atts['type'] = 'adoration';
		$atts = shortcode_atts( array(
			'church'          => '',
			'type'            => 'adoration',
			'days'            => 14,
			'limit'           => 50,
			'layout'          => 'list',
			'show_readings'   => '0',
			'show_intentions' => '0',
			'show_notes'      => '1',
			'show_livestream' => '0',
			'show_church'     => '1',
			'group_by'        => 'day',
			'class'           => '',
			'heading'         => __( 'Adoration', 'parish-core' ),
			'empty_message'   => __( 'No Adoration scheduled.', 'parish-core' ),
		), $atts, 'parish_adoration' );

		return $this->render_times( $atts );
	}

	/**
	 * Mass times today only shortcode.
	 *
	 * [parish_mass_times_today church="ID|slug" layout="list|table" show_readings="1"]
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string Rendered HTML.
	 */
	public function render_mass_times_today( $atts = array() ): string {
		$atts = shortcode_atts( array(
			'church'          => '',
			'type'            => 'mass',
			'layout'          => 'list',
			'show_readings'   => '1',
			'show_intentions' => '0',
			'show_notes'      => '0',
			'show_livestream' => '1',
			'show_church'     => '1',
			'class'           => '',
			'heading'         => sprintf( __( 'Today\'s Mass Times - %s', 'parish-core' ), date_i18n( get_option( 'date_format' ) ) ),
			'empty_message'   => __( 'No Masses scheduled for today.', 'parish-core' ),
		), $atts, 'parish_mass_times_today' );

		$atts['days']     = 1;
		$atts['limit']    = 100;
		$atts['group_by'] = 'none';

		return $this->render_times( $atts );
	}

	/**
	 * Services shortcode for confession, adoration, and other services.
	 *
	 * [parish_services church="ID|slug" type="confession|adoration|all" days="30" layout="list|table"]
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string Rendered HTML.
	 */
	public function render_services( $atts = array() ): string {
		$atts = shortcode_atts( array(
			'church'          => '',
			'type'            => 'all', // confession, adoration, stations, rosary, benediction, novena, all
			'days'            => 30,
			'limit'           => 100,
			'layout'          => 'list',
			'show_readings'   => '0',
			'show_intentions' => '0',
			'show_notes'      => '1',
			'show_livestream' => '0',
			'show_church'     => '1',
			'group_by'        => 'day',
			'class'           => '',
			'heading'         => '',
			'empty_message'   => __( 'No services scheduled.', 'parish-core' ),
		), $atts, 'parish_services' );

		// If 'all' is specified, use all non-mass service types
		if ( 'all' === $atts['type'] ) {
			$atts['type'] = 'confession,adoration,stations,rosary,benediction,novena';
		}

		// Set heading based on type if not provided
		if ( empty( $atts['heading'] ) ) {
			$type = $atts['type'];
			if ( strpos( $type, ',' ) !== false ) {
				$atts['heading'] = __( 'Parish Services', 'parish-core' );
			} else {
				$type_labels = array(
					'confession'  => __( 'Confession Times', 'parish-core' ),
					'adoration'   => __( 'Adoration', 'parish-core' ),
					'stations'    => __( 'Stations of the Cross', 'parish-core' ),
					'rosary'      => __( 'Rosary', 'parish-core' ),
					'benediction' => __( 'Benediction', 'parish-core' ),
					'novena'      => __( 'Novena', 'parish-core' ),
				);
				$atts['heading'] = $type_labels[ $type ] ?? __( 'Services', 'parish-core' );
			}
		}

		return $this->render_times( $atts );
	}

	/**
	 * Resolve church ID from ID or slug.
	 *
	 * @param string $church Church ID or slug.
	 * @return int Church ID or 0.
	 */
	private function resolve_church_id( string $church ): int {
		if ( empty( $church ) ) {
			return 0;
		}

		if ( is_numeric( $church ) ) {
			return (int) $church;
		}

		// Try by slug
		$post = get_page_by_path( $church, OBJECT, 'parish_church' );
		return $post ? $post->ID : 0;
	}

	/**
	 * Render empty state.
	 *
	 * @param string $message Empty message.
	 * @param string $class   Additional CSS class.
	 * @return string HTML.
	 */
	private function render_empty( string $message, string $class = '' ): string {
		$classes = 'parish-times parish-times--empty';
		if ( ! empty( $class ) ) {
			$classes .= ' ' . esc_attr( $class );
		}

		return sprintf(
			'<div class="%s"><p class="parish-times__empty-message">%s</p></div>',
			esc_attr( $classes ),
			esc_html( $message )
		);
	}

	/**
	 * Render list layout.
	 *
	 * @param array $instances All instances.
	 * @param array $grouped   Grouped instances (or null).
	 * @param array $atts      Shortcode attributes.
	 * @return string HTML.
	 */
	private function render_list_layout( array $instances, ?array $grouped, array $atts ): string {
		$classes = 'parish-times parish-times--list';
		if ( ! empty( $atts['class'] ) ) {
			$classes .= ' ' . esc_attr( $atts['class'] );
		}

		ob_start();
		?>
		<div class="<?php echo esc_attr( $classes ); ?>">
			<?php if ( ! empty( $atts['heading'] ) ) : ?>
				<h3 class="parish-times__heading"><?php echo esc_html( $atts['heading'] ); ?></h3>
			<?php endif; ?>

			<?php if ( $grouped && 'day' === $atts['group_by'] ) : ?>
				<?php foreach ( $grouped as $group ) : ?>
					<div class="parish-times__group">
						<h4 class="parish-times__group-heading">
							<span class="parish-times__day-name"><?php echo esc_html( $group['day_name'] ); ?></span>
							<span class="parish-times__date"><?php echo esc_html( $group['date_formatted'] ); ?></span>
						</h4>
						<ul class="parish-times__list">
							<?php foreach ( $group['events'] as $event ) : ?>
								<?php echo $this->render_list_item( $event, $atts ); ?>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endforeach; ?>

			<?php elseif ( $grouped && 'church' === $atts['group_by'] ) : ?>
				<?php foreach ( $grouped as $group ) : ?>
					<div class="parish-times__group">
						<h4 class="parish-times__group-heading"><?php echo esc_html( $group['church_name'] ); ?></h4>
						<ul class="parish-times__list">
							<?php foreach ( $group['events'] as $event ) : ?>
								<?php echo $this->render_list_item( $event, $atts ); ?>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endforeach; ?>

			<?php else : ?>
				<ul class="parish-times__list">
					<?php foreach ( $instances as $event ) : ?>
						<?php echo $this->render_list_item( $event, $atts ); ?>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render a single list item.
	 *
	 * @param array $event Event instance.
	 * @param array $atts  Shortcode attributes.
	 * @return string HTML.
	 */
	private function render_list_item( array $event, array $atts ): string {
		$show_church     = '1' === $atts['show_church'] && ! empty( $event['church_name'] );
		$show_readings   = '1' === $atts['show_readings'] && ! empty( $event['readings'] );
		$show_intentions = '1' === $atts['show_intentions'] && ! empty( $event['intentions'] );
		$show_notes      = '1' === $atts['show_notes'] && ! empty( $event['notes'] );
		$show_livestream = '1' === $atts['show_livestream'] && $event['livestream']['enabled'];

		ob_start();
		?>
		<li class="parish-times__item" data-event-type="<?php echo esc_attr( $event['event_type'] ); ?>">
			<div class="parish-times__item-main">
				<span class="parish-times__time"><?php echo esc_html( $event['time_formatted'] ); ?></span>
				<span class="parish-times__type" style="--type-color: <?php echo esc_attr( $event['event_type_color'] ); ?>">
					<?php echo esc_html( $event['event_type_label'] ); ?>
				</span>
				<?php if ( $show_church ) : ?>
					<span class="parish-times__church"><?php echo esc_html( $event['church_name'] ); ?></span>
				<?php endif; ?>
				<?php if ( $show_livestream ) : ?>
					<?php echo $this->render_livestream_badge( $event, $atts['livestream'] ); ?>
				<?php endif; ?>
				<?php if ( $event['is_special'] ) : ?>
					<span class="parish-times__badge parish-times__badge--special"><?php esc_html_e( 'Special', 'parish-core' ); ?></span>
				<?php endif; ?>
			</div>

			<?php if ( ! empty( $event['title'] ) && $event['title'] !== $event['event_type_label'] ) : ?>
				<div class="parish-times__title"><?php echo esc_html( $event['title'] ); ?></div>
			<?php endif; ?>

			<?php if ( $event['feast_day'] ) : ?>
				<div class="parish-times__feast-day">
					<?php echo esc_html( $event['feast_day']['title'] ?? $event['feast_day']['name'] ?? '' ); ?>
				</div>
			<?php endif; ?>

			<?php if ( $show_readings ) : ?>
				<?php echo $this->render_readings( $event['readings'] ); ?>
			<?php endif; ?>

			<?php if ( $show_intentions ) : ?>
				<div class="parish-times__intentions">
					<strong><?php esc_html_e( 'Intentions:', 'parish-core' ); ?></strong>
					<?php echo wp_kses_post( is_string( $event['intentions'] ) ? $event['intentions'] : '' ); ?>
				</div>
			<?php endif; ?>

			<?php if ( $show_notes ) : ?>
				<div class="parish-times__notes"><?php echo wp_kses_post( $event['notes'] ); ?></div>
			<?php endif; ?>

			<?php if ( $show_livestream && 'embed' === $atts['livestream'] && ! empty( $event['livestream']['embed'] ) ) : ?>
				<div class="parish-times__embed">
					<?php echo wp_kses( $event['livestream']['embed'], $this->get_allowed_iframe_html() ); ?>
				</div>
			<?php endif; ?>
		</li>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render table layout.
	 *
	 * @param array $instances All instances.
	 * @param array $grouped   Grouped instances (or null).
	 * @param array $atts      Shortcode attributes.
	 * @return string HTML.
	 */
	private function render_table_layout( array $instances, ?array $grouped, array $atts ): string {
		$classes = 'parish-times parish-times--table';
		if ( ! empty( $atts['class'] ) ) {
			$classes .= ' ' . esc_attr( $atts['class'] );
		}

		$show_church   = '1' === $atts['show_church'];
		$show_readings = '1' === $atts['show_readings'];

		ob_start();
		?>
		<div class="<?php echo esc_attr( $classes ); ?>">
			<?php if ( ! empty( $atts['heading'] ) ) : ?>
				<h3 class="parish-times__heading"><?php echo esc_html( $atts['heading'] ); ?></h3>
			<?php endif; ?>

			<table class="parish-times__table">
				<thead>
					<tr>
						<th class="parish-times__th-date"><?php esc_html_e( 'Date', 'parish-core' ); ?></th>
						<th class="parish-times__th-time"><?php esc_html_e( 'Time', 'parish-core' ); ?></th>
						<th class="parish-times__th-type"><?php esc_html_e( 'Service', 'parish-core' ); ?></th>
						<?php if ( $show_church ) : ?>
							<th class="parish-times__th-church"><?php esc_html_e( 'Church', 'parish-core' ); ?></th>
						<?php endif; ?>
						<?php if ( $show_readings ) : ?>
							<th class="parish-times__th-readings"><?php esc_html_e( 'Readings', 'parish-core' ); ?></th>
						<?php endif; ?>
						<th class="parish-times__th-info"></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $instances as $event ) : ?>
						<tr class="parish-times__row" data-event-type="<?php echo esc_attr( $event['event_type'] ); ?>">
							<td class="parish-times__td-date">
								<span class="parish-times__day-name"><?php echo esc_html( $event['day_name'] ); ?></span>
								<span class="parish-times__date"><?php echo esc_html( $event['date_formatted'] ); ?></span>
							</td>
							<td class="parish-times__td-time"><?php echo esc_html( $event['time_formatted'] ); ?></td>
							<td class="parish-times__td-type">
								<span class="parish-times__type-badge" style="--type-color: <?php echo esc_attr( $event['event_type_color'] ); ?>">
									<?php echo esc_html( $event['event_type_label'] ); ?>
								</span>
							</td>
							<?php if ( $show_church ) : ?>
								<td class="parish-times__td-church"><?php echo esc_html( $event['church_name'] ); ?></td>
							<?php endif; ?>
							<?php if ( $show_readings ) : ?>
								<td class="parish-times__td-readings">
									<?php if ( ! empty( $event['readings']['gospel'] ) ) : ?>
										<?php echo esc_html( $event['readings']['gospel']['reference'] ?? '' ); ?>
									<?php endif; ?>
								</td>
							<?php endif; ?>
							<td class="parish-times__td-info">
								<?php if ( $event['livestream']['enabled'] ) : ?>
									<?php echo $this->render_livestream_badge( $event, $atts['livestream'] ); ?>
								<?php endif; ?>
								<?php if ( $event['is_special'] ) : ?>
									<span class="parish-times__badge parish-times__badge--special"><?php esc_html_e( 'Special', 'parish-core' ); ?></span>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render cards layout.
	 *
	 * @param array $instances All instances.
	 * @param array $grouped   Grouped instances (or null).
	 * @param array $atts      Shortcode attributes.
	 * @return string HTML.
	 */
	private function render_cards_layout( array $instances, ?array $grouped, array $atts ): string {
		$classes = 'parish-times parish-times--cards';
		if ( ! empty( $atts['class'] ) ) {
			$classes .= ' ' . esc_attr( $atts['class'] );
		}

		ob_start();
		?>
		<div class="<?php echo esc_attr( $classes ); ?>">
			<?php if ( ! empty( $atts['heading'] ) ) : ?>
				<h3 class="parish-times__heading"><?php echo esc_html( $atts['heading'] ); ?></h3>
			<?php endif; ?>

			<div class="parish-times__cards-grid">
				<?php foreach ( $instances as $event ) : ?>
					<?php echo $this->render_card_item( $event, $atts ); ?>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render a single card item.
	 *
	 * @param array $event Event instance.
	 * @param array $atts  Shortcode attributes.
	 * @return string HTML.
	 */
	private function render_card_item( array $event, array $atts ): string {
		$show_church     = '1' === $atts['show_church'] && ! empty( $event['church_name'] );
		$show_readings   = '1' === $atts['show_readings'] && ! empty( $event['readings'] );
		$show_livestream = '1' === $atts['show_livestream'] && $event['livestream']['enabled'];

		ob_start();
		?>
		<div class="parish-times__card" data-event-type="<?php echo esc_attr( $event['event_type'] ); ?>" style="--type-color: <?php echo esc_attr( $event['event_type_color'] ); ?>">
			<div class="parish-times__card-header">
				<span class="parish-times__card-type"><?php echo esc_html( $event['event_type_label'] ); ?></span>
				<?php if ( $show_livestream ) : ?>
					<?php echo $this->render_livestream_badge( $event, $atts['livestream'] ); ?>
				<?php endif; ?>
			</div>

			<div class="parish-times__card-body">
				<div class="parish-times__card-datetime">
					<span class="parish-times__card-date"><?php echo esc_html( $event['day_name'] . ', ' . $event['date_formatted'] ); ?></span>
					<span class="parish-times__card-time"><?php echo esc_html( $event['time_formatted'] ); ?></span>
				</div>

				<?php if ( $show_church ) : ?>
					<div class="parish-times__card-church"><?php echo esc_html( $event['church_name'] ); ?></div>
				<?php endif; ?>

				<?php if ( $event['feast_day'] ) : ?>
					<div class="parish-times__card-feast">
						<?php echo esc_html( $event['feast_day']['title'] ?? $event['feast_day']['name'] ?? '' ); ?>
					</div>
				<?php endif; ?>

				<?php if ( $show_readings && ! empty( $event['readings']['gospel'] ) ) : ?>
					<div class="parish-times__card-readings">
						<span class="parish-times__readings-label"><?php esc_html_e( 'Gospel:', 'parish-core' ); ?></span>
						<?php echo esc_html( $event['readings']['gospel']['reference'] ?? '' ); ?>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( $event['is_special'] ) : ?>
				<div class="parish-times__card-special"><?php esc_html_e( 'Special Event', 'parish-core' ); ?></div>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render compact layout.
	 *
	 * @param array $instances All instances.
	 * @param array $grouped   Grouped instances (or null).
	 * @param array $atts      Shortcode attributes.
	 * @return string HTML.
	 */
	private function render_compact_layout( array $instances, ?array $grouped, array $atts ): string {
		$classes = 'parish-times parish-times--compact';
		if ( ! empty( $atts['class'] ) ) {
			$classes .= ' ' . esc_attr( $atts['class'] );
		}

		$show_church = '1' === $atts['show_church'];

		ob_start();
		?>
		<div class="<?php echo esc_attr( $classes ); ?>">
			<?php if ( ! empty( $atts['heading'] ) ) : ?>
				<h3 class="parish-times__heading"><?php echo esc_html( $atts['heading'] ); ?></h3>
			<?php endif; ?>

			<?php if ( $grouped && 'day' === $atts['group_by'] ) : ?>
				<?php foreach ( $grouped as $group ) : ?>
					<div class="parish-times__compact-group">
						<span class="parish-times__compact-date"><?php echo esc_html( $group['day_name'] ); ?>:</span>
						<?php foreach ( $group['events'] as $i => $event ) : ?>
							<?php if ( $i > 0 ) echo ', '; ?>
							<span class="parish-times__compact-item">
								<?php echo esc_html( $event['time_formatted'] ); ?>
								<?php if ( $show_church && ! empty( $event['church_name'] ) ) : ?>
									(<?php echo esc_html( $event['church_name'] ); ?>)
								<?php endif; ?>
								<?php if ( $event['livestream']['enabled'] ) : ?>
									<span class="parish-times__badge parish-times__badge--live"><?php esc_html_e( 'Live', 'parish-core' ); ?></span>
								<?php endif; ?>
							</span>
						<?php endforeach; ?>
					</div>
				<?php endforeach; ?>
			<?php else : ?>
				<div class="parish-times__compact-list">
					<?php foreach ( $instances as $event ) : ?>
						<span class="parish-times__compact-item">
							<?php echo esc_html( $event['day_name'] . ' ' . $event['time_formatted'] ); ?>
							<?php if ( $show_church && ! empty( $event['church_name'] ) ) : ?>
								- <?php echo esc_html( $event['church_name'] ); ?>
							<?php endif; ?>
						</span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render livestream badge.
	 *
	 * @param array  $event Event instance.
	 * @param string $mode  Livestream display mode.
	 * @return string HTML.
	 */
	private function render_livestream_badge( array $event, string $mode = 'auto' ): string {
		if ( 'hide' === $mode || ! $event['livestream']['enabled'] ) {
			return '';
		}

		$url = $event['livestream']['url'] ?? '';
		$has_link = ! empty( $url );

		if ( $has_link && in_array( $mode, array( 'auto', 'link' ), true ) ) {
			return sprintf(
				'<a href="%s" class="parish-times__badge parish-times__badge--live" target="_blank" rel="noopener">%s</a>',
				esc_url( $url ),
				esc_html__( 'Watch Live', 'parish-core' )
			);
		}

		return sprintf(
			'<span class="parish-times__badge parish-times__badge--live">%s</span>',
			esc_html__( 'Livestream', 'parish-core' )
		);
	}

	/**
	 * Render readings summary.
	 *
	 * @param array $readings Readings data.
	 * @return string HTML.
	 */
	private function render_readings( ?array $readings ): string {
		if ( empty( $readings ) ) {
			return '';
		}

		ob_start();
		?>
		<div class="parish-times__readings">
			<?php if ( ! empty( $readings['first_reading']['reference'] ) ) : ?>
				<span class="parish-times__reading">
					<strong><?php esc_html_e( '1st:', 'parish-core' ); ?></strong>
					<?php echo esc_html( $readings['first_reading']['reference'] ); ?>
				</span>
			<?php endif; ?>
			<?php if ( ! empty( $readings['psalm']['reference'] ) ) : ?>
				<span class="parish-times__reading">
					<strong><?php esc_html_e( 'Ps:', 'parish-core' ); ?></strong>
					<?php echo esc_html( $readings['psalm']['reference'] ); ?>
				</span>
			<?php endif; ?>
			<?php if ( ! empty( $readings['gospel']['reference'] ) ) : ?>
				<span class="parish-times__reading">
					<strong><?php esc_html_e( 'Gospel:', 'parish-core' ); ?></strong>
					<?php echo esc_html( $readings['gospel']['reference'] ); ?>
				</span>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get allowed HTML for iframe embeds.
	 *
	 * @return array Allowed HTML.
	 */
	private function get_allowed_iframe_html(): array {
		return array(
			'iframe' => array(
				'src'             => true,
				'width'           => true,
				'height'          => true,
				'frameborder'     => true,
				'allow'           => true,
				'allowfullscreen' => true,
				'title'           => true,
				'loading'         => true,
				'style'           => true,
				'class'           => true,
				'id'              => true,
			),
		);
	}

	/**
	 * Church schedule shortcode - shows weekly schedule grouped by church.
	 *
	 * Displays a clean, simple view similar to traditional parish bulletins.
	 * Shows recurring schedule patterns rather than specific dates.
	 *
	 * [parish_church_schedule church="ID|slug|all" type="mass|all" show_address="1"
	 *                         show_map="1" show_notes="1" accent_color="#5a7a8a"]
	 *
	 * @param array  $atts    Shortcode attributes.
	 * @param string $content Optional notes content to display.
	 * @return string Rendered HTML.
	 */
	public function render_church_schedule( $atts = array(), $content = '' ): string {
		$atts = shortcode_atts( array(
			'church'        => 'all',
			'type'          => 'mass',
			'show_address'  => '1',
			'show_map'      => '1',
			'show_notes'    => '1',
			'show_icons'    => '1',
			'accent_color'  => '#5a7a8a',
			'bg_color'      => '#5a7a8a',
			'text_color'    => '#ffffff',
			'class'         => '',
			'notes'         => '',
		), $atts, 'parish_church_schedule' );

		wp_enqueue_style( 'parish-event-times' );

		// Get churches to display
		$churches = $this->get_churches_for_schedule( $atts['church'] );

		if ( empty( $churches ) ) {
			return '<div class="parish-schedule parish-schedule--empty"><p>' .
				esc_html__( 'No churches found.', 'parish-core' ) . '</p></div>';
		}

		// Get recurring event times grouped by church
		$schedules = $this->get_recurring_schedules( $churches, $atts['type'] );

		// Use shortcode content as notes if provided
		$notes = ! empty( $content ) ? $content : $atts['notes'];

		ob_start();
		$this->render_schedule_output( $churches, $schedules, $atts, $notes );
		return ob_get_clean();
	}

	/**
	 * Get churches for schedule display.
	 *
	 * @param string $church Church ID, slug, or 'all'.
	 * @return array Array of church data.
	 */
	private function get_churches_for_schedule( string $church ): array {
		$churches = array();

		if ( 'all' === $church ) {
			$posts = get_posts( array(
				'post_type'      => 'parish_church',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
			) );
		} else {
			// Multiple churches specified
			$church_ids = array_map( 'trim', explode( ',', $church ) );
			$posts = array();

			foreach ( $church_ids as $id ) {
				if ( is_numeric( $id ) ) {
					$post = get_post( (int) $id );
				} else {
					$post = get_page_by_path( $id, OBJECT, 'parish_church' );
				}
				if ( $post && 'parish_church' === $post->post_type ) {
					$posts[] = $post;
				}
			}
		}

		foreach ( $posts as $post ) {
			$churches[] = array(
				'id'           => $post->ID,
				'name'         => $post->post_title,
				'slug'         => $post->post_name,
				'address'      => $this->get_church_address( $post->ID ),
				'map_url'      => get_post_meta( $post->ID, 'parish_map_url', true ),
				'lat'          => get_post_meta( $post->ID, 'parish_latitude', true ),
				'lng'          => get_post_meta( $post->ID, 'parish_longitude', true ),
				'livestream'   => get_post_meta( $post->ID, 'parish_livestream_url', true ),
			);
		}

		return $churches;
	}

	/**
	 * Get church address formatted.
	 *
	 * @param int $church_id Church post ID.
	 * @return array Address components.
	 */
	private function get_church_address( int $church_id ): array {
		return array(
			'street'   => get_post_meta( $church_id, 'parish_street_address', true ),
			'city'     => get_post_meta( $church_id, 'parish_city', true ),
			'county'   => get_post_meta( $church_id, 'parish_county', true ),
			'postcode' => get_post_meta( $church_id, 'parish_postcode', true ),
			'country'  => get_post_meta( $church_id, 'parish_country', true ),
		);
	}

	/**
	 * Get recurring schedules grouped by church and day.
	 *
	 * @param array  $churches Array of church data.
	 * @param string $type     Event type filter.
	 * @return array Schedules indexed by church ID.
	 */
	private function get_recurring_schedules( array $churches, string $type ): array {
		$schedules = array();
		$church_ids = array_column( $churches, 'id' );

		// Query recurring event times
		$args = array(
			'post_type'      => 'parish_event_time',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => 'parish_is_recurring',
					'value'   => '1',
					'compare' => '=',
				),
				array(
					'key'     => 'parish_is_active',
					'value'   => '1',
					'compare' => '=',
				),
			),
		);

		// Filter by church if specific churches selected
		if ( count( $church_ids ) > 0 && ! in_array( 0, $church_ids, true ) ) {
			$args['meta_query'][] = array(
				'key'     => 'parish_church_id',
				'value'   => $church_ids,
				'compare' => 'IN',
			);
		}

		// Filter by event type
		if ( 'all' !== $type ) {
			$types = array_map( 'trim', explode( ',', $type ) );
			$args['meta_query'][] = array(
				'key'     => 'parish_event_type',
				'value'   => $types,
				'compare' => count( $types ) > 1 ? 'IN' : '=',
			);
		}

		$posts = get_posts( $args );

		// Group by church and day
		foreach ( $posts as $post ) {
			$church_id = (int) get_post_meta( $post->ID, 'parish_church_id', true );
			$event_type = get_post_meta( $post->ID, 'parish_event_type', true ) ?: 'mass';
			$start_datetime = get_post_meta( $post->ID, 'parish_start_datetime', true );
			$recurrence_rule = get_post_meta( $post->ID, 'parish_recurrence_rule', true );
			$livestream_enabled = get_post_meta( $post->ID, 'parish_livestream_enabled', true );
			$is_special = get_post_meta( $post->ID, 'parish_is_special', true );
			$notes = get_post_meta( $post->ID, 'parish_notes', true );

			// Get time from datetime
			$time = '';
			if ( $start_datetime ) {
				try {
					$dt = new DateTime( $start_datetime );
					$time = $dt->format( 'H:i' );
				} catch ( \Exception $e ) {
					continue;
				}
			}

			// Get days from recurrence rule
			$days = array();
			if ( is_array( $recurrence_rule ) && isset( $recurrence_rule['days'] ) ) {
				$days = $recurrence_rule['days'];
			} elseif ( is_string( $recurrence_rule ) ) {
				$rule = maybe_unserialize( $recurrence_rule );
				if ( is_array( $rule ) && isset( $rule['days'] ) ) {
					$days = $rule['days'];
				}
			}

			// If no specific days, use the start datetime's day
			if ( empty( $days ) && $start_datetime ) {
				try {
					$dt = new DateTime( $start_datetime );
					$days = array( $dt->format( 'l' ) );
				} catch ( \Exception $e ) {
					// Skip if can't parse
				}
			}

			// Build schedule entry
			$event_data = array(
				'id'          => $post->ID,
				'time'        => $time,
				'time_formatted' => $this->format_time_12h( $time ),
				'event_type'  => $event_type,
				'event_label' => $this->get_event_type_label( $event_type ),
				'livestream'  => (bool) $livestream_enabled,
				'is_special'  => (bool) $is_special,
				'notes'       => $notes,
			);

			// Add to each day
			foreach ( $days as $day ) {
				if ( ! isset( $schedules[ $church_id ] ) ) {
					$schedules[ $church_id ] = array();
				}
				if ( ! isset( $schedules[ $church_id ][ $day ] ) ) {
					$schedules[ $church_id ][ $day ] = array();
				}
				$schedules[ $church_id ][ $day ][] = $event_data;
			}
		}

		// Sort times within each day
		foreach ( $schedules as $church_id => &$church_schedule ) {
			foreach ( $church_schedule as $day => &$events ) {
				usort( $events, function( $a, $b ) {
					return strcmp( $a['time'], $b['time'] );
				} );
			}
		}

		return $schedules;
	}

	/**
	 * Format time in 12-hour format.
	 *
	 * @param string $time Time in H:i format.
	 * @return string Formatted time.
	 */
	private function format_time_12h( string $time ): string {
		if ( empty( $time ) ) {
			return '';
		}

		try {
			$dt = DateTime::createFromFormat( 'H:i', $time );
			if ( $dt ) {
				return $dt->format( 'g:i a' );
			}
		} catch ( \Exception $e ) {
			// Fall through
		}

		return $time;
	}

	/**
	 * Get event type label.
	 *
	 * @param string $type Event type slug.
	 * @return string Label.
	 */
	private function get_event_type_label( string $type ): string {
		$labels = array(
			'mass'        => __( 'Mass', 'parish-core' ),
			'confession'  => __( 'Confession', 'parish-core' ),
			'adoration'   => __( 'Adoration', 'parish-core' ),
			'stations'    => __( 'Stations of the Cross', 'parish-core' ),
			'rosary'      => __( 'Rosary', 'parish-core' ),
			'benediction' => __( 'Benediction', 'parish-core' ),
			'novena'      => __( 'Novena', 'parish-core' ),
			'vigil'       => __( 'Vigil', 'parish-core' ),
			'funeral'     => __( 'Funeral', 'parish-core' ),
			'wedding'     => __( 'Wedding', 'parish-core' ),
			'baptism'     => __( 'Baptism', 'parish-core' ),
		);

		return $labels[ $type ] ?? ucfirst( $type );
	}

	/**
	 * Render the schedule output.
	 *
	 * @param array  $churches  Churches data.
	 * @param array  $schedules Schedules by church.
	 * @param array  $atts      Shortcode attributes.
	 * @param string $notes     Additional notes.
	 */
	private function render_schedule_output( array $churches, array $schedules, array $atts, string $notes ): void {
		$class = 'parish-schedule';
		if ( ! empty( $atts['class'] ) ) {
			$class .= ' ' . esc_attr( $atts['class'] );
		}

		$day_order = array( 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' );

		echo '<div class="' . esc_attr( $class ) . '">';

		foreach ( $churches as $church ) :
			$church_schedule = $schedules[ $church['id'] ] ?? array();
			$has_address = '1' === $atts['show_address'] && $this->has_address( $church['address'] );
			$has_map = '1' === $atts['show_map'] && ( ! empty( $church['map_url'] ) || ( ! empty( $church['lat'] ) && ! empty( $church['lng'] ) ) );
			$show_icons = '1' === $atts['show_icons'];
			?>
			<div class="parish-schedule__church"
				 style="--schedule-accent: <?php echo esc_attr( $atts['accent_color'] ); ?>;
				        --schedule-bg: <?php echo esc_attr( $atts['bg_color'] ); ?>;
				        --schedule-text: <?php echo esc_attr( $atts['text_color'] ); ?>;">

				<div class="parish-schedule__header">
					<h3 class="parish-schedule__church-name"><?php echo esc_html( $church['name'] ); ?></h3>
				</div>

				<div class="parish-schedule__content">
					<div class="parish-schedule__times">
						<?php if ( empty( $church_schedule ) ) : ?>
							<p class="parish-schedule__no-times"><?php esc_html_e( 'No regular schedule set.', 'parish-core' ); ?></p>
						<?php else : ?>
							<ul class="parish-schedule__day-list">
								<?php foreach ( $day_order as $day ) :
									if ( ! isset( $church_schedule[ $day ] ) ) continue;
									$events = $church_schedule[ $day ];
									?>
									<li class="parish-schedule__day-item">
										<strong class="parish-schedule__day-name"><?php echo esc_html( strtoupper( $day ) ); ?>:</strong>
										<span class="parish-schedule__day-times">
											<?php
											$time_parts = array();
											foreach ( $events as $event ) {
												$time_str = esc_html( $event['time_formatted'] );
												$icons = '';

												if ( $show_icons ) {
													if ( $event['livestream'] ) {
														$icons .= '<span class="parish-schedule__icon parish-schedule__icon--live" title="' . esc_attr__( 'Livestream available', 'parish-core' ) . '">📺</span>';
													}
													if ( $event['is_special'] ) {
														$icons .= '<span class="parish-schedule__icon parish-schedule__icon--special" title="' . esc_attr__( 'Special event', 'parish-core' ) . '">⭐</span>';
													}
												}

												$time_parts[] = $time_str . $icons;
											}
											echo implode( ', ', $time_parts );
											?>
										</span>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</div>

					<?php if ( $has_address || $has_map ) : ?>
						<div class="parish-schedule__info">
							<?php if ( $has_address ) : ?>
								<div class="parish-schedule__address">
									<strong><?php echo esc_html( $church['name'] ); ?>,</strong><br>
									<?php if ( ! empty( $church['address']['street'] ) ) : ?>
										<?php echo esc_html( $church['address']['street'] ); ?>,<br>
									<?php endif; ?>
									<?php if ( ! empty( $church['address']['city'] ) ) : ?>
										<?php echo esc_html( $church['address']['city'] ); ?>,
									<?php endif; ?>
									<?php if ( ! empty( $church['address']['postcode'] ) ) : ?>
										<?php echo esc_html( $church['address']['postcode'] ); ?>
									<?php endif; ?>
									<?php if ( ! empty( $church['address']['county'] ) ) : ?>
										<br><?php echo esc_html( $church['address']['county'] ); ?>
									<?php endif; ?>
								</div>
							<?php endif; ?>

							<?php if ( $has_map ) : ?>
								<?php
								$map_url = $church['map_url'];
								if ( empty( $map_url ) && ! empty( $church['lat'] ) && ! empty( $church['lng'] ) ) {
									$map_url = 'https://www.google.com/maps?q=' . $church['lat'] . ',' . $church['lng'];
								}
								?>
								<a href="<?php echo esc_url( $map_url ); ?>"
								   class="parish-schedule__map-btn"
								   target="_blank"
								   rel="noopener">
									<?php esc_html_e( 'Google Map', 'parish-core' ); ?>
								</a>
							<?php endif; ?>

							<?php if ( ! empty( $church['livestream'] ) ) : ?>
								<a href="<?php echo esc_url( $church['livestream'] ); ?>"
								   class="parish-schedule__livestream-btn"
								   target="_blank"
								   rel="noopener">
									📺 <?php esc_html_e( 'Watch Live', 'parish-core' ); ?>
								</a>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach;

		// Render notes if provided
		if ( '1' === $atts['show_notes'] && ! empty( $notes ) ) :
			?>
			<div class="parish-schedule__notes"
				 style="--schedule-accent: <?php echo esc_attr( $atts['accent_color'] ); ?>;">
				<?php echo wp_kses_post( wpautop( $notes ) ); ?>
			</div>
		<?php endif;

		echo '</div>';
	}

	/**
	 * Check if address has any data.
	 *
	 * @param array $address Address array.
	 * @return bool
	 */
	private function has_address( array $address ): bool {
		return ! empty( $address['street'] ) ||
			   ! empty( $address['city'] ) ||
			   ! empty( $address['postcode'] );
	}
}

// Initialize
Parish_Event_Time_Shortcodes::instance();
