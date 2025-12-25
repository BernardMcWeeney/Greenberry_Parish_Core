<?php
/**
 * Schedule Generator for Parish Core.
 *
 * Generates concrete schedule instances from templates, overrides, and intentions.
 * This is the core engine that computes "what masses/events happen on which dates"
 * from the stored schedule templates.
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Schedule generator class.
 */
class Parish_Schedule_Generator {

	/**
	 * Singleton instance.
	 *
	 * @var Parish_Schedule_Generator|null
	 */
	private static ?Parish_Schedule_Generator $instance = null;

	/**
	 * Loaded schedule templates.
	 *
	 * @var array
	 */
	private array $templates = array();

	/**
	 * Loaded schedule overrides.
	 *
	 * @var array
	 */
	private array $overrides = array();

	/**
	 * Flag to track if data is loaded.
	 *
	 * @var bool
	 */
	private bool $data_loaded = false;

	/**
	 * Event type definitions with labels and icons.
	 */
	public const EVENT_TYPES = array(
		// Sacraments
		'mass'         => array(
			'label' => 'Mass',
			'icon'  => 'dashicons-buddicons-community',
			'color' => '#4A8391',
		),
		'confession'   => array(
			'label' => 'Confession',
			'icon'  => 'dashicons-heart',
			'color' => '#8B5CF6',
		),
		'baptism'      => array(
			'label' => 'Baptism',
			'icon'  => 'dashicons-admin-site-alt3',
			'color' => '#06B6D4',
		),
		'confirmation' => array(
			'label' => 'Confirmation',
			'icon'  => 'dashicons-shield',
			'color' => '#DC2626',
		),
		'wedding'      => array(
			'label' => 'Wedding',
			'icon'  => 'dashicons-heart',
			'color' => '#F59E0B',
		),
		'funeral'      => array(
			'label' => 'Funeral',
			'icon'  => 'dashicons-minus',
			'color' => '#1F2937',
		),
		'anointing'    => array(
			'label' => 'Anointing of the Sick',
			'icon'  => 'dashicons-plus-alt',
			'color' => '#10B981',
		),

		// Devotions
		'adoration'    => array(
			'label' => 'Adoration',
			'icon'  => 'dashicons-visibility',
			'color' => '#FFD700',
		),
		'stations'     => array(
			'label' => 'Stations of the Cross',
			'icon'  => 'dashicons-plus',
			'color' => '#7C3AED',
		),
		'rosary'       => array(
			'label' => 'Rosary',
			'icon'  => 'dashicons-marker',
			'color' => '#3B82F6',
		),
		'novena'       => array(
			'label' => 'Novena',
			'icon'  => 'dashicons-star-filled',
			'color' => '#EC4899',
		),
		'benediction'  => array(
			'label' => 'Benediction',
			'icon'  => 'dashicons-awards',
			'color' => '#F97316',
		),
		'vespers'      => array(
			'label' => 'Vespers',
			'icon'  => 'dashicons-calendar-alt',
			'color' => '#6366F1',
		),

		// Other
		'meeting'      => array(
			'label' => 'Parish Meeting',
			'icon'  => 'dashicons-groups',
			'color' => '#64748B',
		),
		'other'        => array(
			'label' => 'Other',
			'icon'  => 'dashicons-calendar',
			'color' => '#94A3B8',
		),
	);

	/**
	 * Liturgical rite options.
	 */
	public const RITES = array(
		'roman'        => 'Roman Rite',
		'byzantine'    => 'Byzantine Rite',
		'maronite'     => 'Maronite Rite',
		'chaldean'     => 'Chaldean Rite',
		'syro-malabar' => 'Syro-Malabar Rite',
	);

	/**
	 * Liturgical form options.
	 */
	public const FORMS = array(
		'ordinary'      => 'Ordinary Form (Novus Ordo)',
		'extraordinary' => 'Extraordinary Form (TLM)',
	);

	/**
	 * Get singleton instance.
	 *
	 * @return Parish_Schedule_Generator
	 */
	public static function instance(): Parish_Schedule_Generator {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private constructor.
	 */
	private function __construct() {}

	/**
	 * Load schedule data from settings.
	 *
	 * @param bool $force Force reload even if already loaded.
	 * @return void
	 */
	private function load_data( bool $force = false ): void {
		if ( $this->data_loaded && ! $force ) {
			return;
		}

		$settings = get_option( 'parish_core_settings', array() );

		// Load templates.
		$this->templates = $settings['liturgical_schedules'] ?? array();
		if ( ! is_array( $this->templates ) ) {
			$this->templates = array();
		}

		// Load overrides.
		$this->overrides = $settings['schedule_overrides'] ?? array();
		if ( ! is_array( $this->overrides ) ) {
			$this->overrides = array();
		}

		$this->data_loaded = true;
	}

	/**
	 * Generate schedule for a date range.
	 *
	 * @param string $start_date Start date (Y-m-d).
	 * @param string $end_date   End date (Y-m-d).
	 * @param array  $filters    Optional filters.
	 * @return array Array of schedule instances.
	 */
	public function generate( string $start_date, string $end_date, array $filters = array() ): array {
		$this->load_data();

		$instances = array();
		$current   = new DateTime( $start_date );
		$end       = new DateTime( $end_date );

		// Pre-load intentions for the date range.
		$intentions = $this->get_intentions_for_range( $start_date, $end_date );

		// Pre-load feast days if available.
		$feast_days = $this->get_feast_days_for_range( $start_date, $end_date );

		while ( $current <= $end ) {
			$date_str = $current->format( 'Y-m-d' );
			$day_name = $current->format( 'l' );

			// Get feast day for this date.
			$feast_info = $feast_days[ $date_str ] ?? null;

			// Process each template.
			foreach ( $this->templates as $template ) {
				// Skip inactive templates.
				if ( empty( $template['active'] ) ) {
					continue;
				}

				// Apply filters.
				if ( ! $this->matches_filters( $template, $filters ) ) {
					continue;
				}

				// Check if this template applies to this date.
				$recurrence = $template['recurrence'] ?? array();
				$created_at = $template['created_at'] ?? '';

				if ( ! Parish_Recurrence::matches_pattern( $current, $recurrence, $created_at ) ) {
					continue;
				}

				// Check for overrides.
				$override = $this->get_override_for( $template['id'] ?? '', $date_str );

				// If cancelled, skip.
				if ( $override && 'cancellation' === ( $override['type'] ?? '' ) ) {
					continue;
				}

				// Build the instance.
				$instance = $this->build_instance( $template, $current, $override, $feast_info );

				// Add intentions.
				$template_id             = $template['id'] ?? '';
				$instance['intentions']  = $intentions[ $date_str ][ $template_id ] ?? array();

				$instances[] = $instance;
			}

			// Add one-time additions for this date.
			foreach ( $this->get_additions_for_date( $date_str ) as $addition ) {
				if ( $this->matches_filters( $addition, $filters ) ) {
					$instances[] = $this->build_addition_instance( $addition, $current, $feast_info );
				}
			}

			$current->modify( '+1 day' );
		}

		// Sort by date, then time.
		usort(
			$instances,
			function ( $a, $b ) {
				$date_cmp = strcmp( $a['date'], $b['date'] );
				if ( 0 !== $date_cmp ) {
					return $date_cmp;
				}
				return strcmp( $a['time'], $b['time'] );
			}
		);

		return $instances;
	}

	/**
	 * Generate today's schedule.
	 *
	 * @param array $filters Optional filters.
	 * @return array Today's schedule instances.
	 */
	public function generate_today( array $filters = array() ): array {
		$today = current_time( 'Y-m-d' );
		return $this->generate( $today, $today, $filters );
	}

	/**
	 * Generate this week's schedule.
	 *
	 * @param array $filters Optional filters.
	 * @return array This week's schedule instances.
	 */
	public function generate_week( array $filters = array() ): array {
		$start = current_time( 'Y-m-d' );
		$end   = date( 'Y-m-d', strtotime( '+6 days', strtotime( $start ) ) );
		return $this->generate( $start, $end, $filters );
	}

	/**
	 * Generate schedule for a specific church.
	 *
	 * @param int    $church_id Church post ID.
	 * @param string $start     Start date.
	 * @param string $end       End date.
	 * @return array Schedule instances for the church.
	 */
	public function generate_for_church( int $church_id, string $start, string $end ): array {
		return $this->generate( $start, $end, array( 'church_id' => $church_id ) );
	}

	/**
	 * Check if a template matches the given filters.
	 *
	 * @param array $template Template or addition data.
	 * @param array $filters  Filters to apply.
	 * @return bool True if matches all filters.
	 */
	private function matches_filters( array $template, array $filters ): bool {
		// Filter by church.
		if ( ! empty( $filters['church_id'] ) ) {
			$church_id = (int) ( $template['church_id'] ?? 0 );
			if ( $church_id !== (int) $filters['church_id'] ) {
				return false;
			}
		}

		// Filter by event type.
		if ( ! empty( $filters['event_type'] ) ) {
			$event_type = $template['event_type'] ?? '';
			if ( $event_type !== $filters['event_type'] ) {
				return false;
			}
		}

		// Filter by event types (multiple).
		if ( ! empty( $filters['event_types'] ) && is_array( $filters['event_types'] ) ) {
			$event_type = $template['event_type'] ?? '';
			if ( ! in_array( $event_type, $filters['event_types'], true ) ) {
				return false;
			}
		}

		// Filter by livestream.
		if ( isset( $filters['livestreamed'] ) ) {
			$is_livestreamed = ! empty( $template['livestream']['enabled'] );
			if ( $filters['livestreamed'] !== $is_livestreamed ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get override for a template on a specific date.
	 *
	 * @param string $template_id Template ID.
	 * @param string $date        Date (Y-m-d).
	 * @return array|null Override data or null.
	 */
	private function get_override_for( string $template_id, string $date ): ?array {
		foreach ( $this->overrides as $override ) {
			// Skip additions (handled separately).
			if ( 'addition' === ( $override['type'] ?? '' ) ) {
				continue;
			}

			$override_schedule_id = $override['schedule_id'] ?? '';
			$override_date        = $override['date'] ?? '';

			if ( $override_schedule_id === $template_id && $override_date === $date ) {
				return $override;
			}
		}

		return null;
	}

	/**
	 * Get one-time additions for a date.
	 *
	 * @param string $date Date (Y-m-d).
	 * @return array Additions for the date.
	 */
	private function get_additions_for_date( string $date ): array {
		$additions = array();

		foreach ( $this->overrides as $override ) {
			if ( 'addition' !== ( $override['type'] ?? '' ) ) {
				continue;
			}

			if ( ( $override['date'] ?? '' ) === $date ) {
				$additions[] = $override;
			}
		}

		return $additions;
	}

	/**
	 * Build a schedule instance from a template.
	 *
	 * @param array     $template   Template data.
	 * @param DateTime  $date       Date for this instance.
	 * @param array|null $override  Override data if any.
	 * @param array|null $feast     Feast day info if any.
	 * @return array Schedule instance.
	 */
	private function build_instance( array $template, DateTime $date, ?array $override, ?array $feast ): array {
		$recurrence = $template['recurrence'] ?? array();

		// Apply override modifications.
		$time      = $override['new_time'] ?? ( $recurrence['time'] ?? '' );
		$church_id = $override['new_church_id'] ?? ( $template['church_id'] ?? 0 );

		$event_type      = $template['event_type'] ?? 'mass';
		$event_type_info = self::EVENT_TYPES[ $event_type ] ?? self::EVENT_TYPES['other'];

		return array(
			'id'              => ( $template['id'] ?? '' ) . '_' . $date->format( 'Ymd' ),
			'template_id'     => $template['id'] ?? '',
			'override_id'     => $override['id'] ?? null,
			'date'            => $date->format( 'Y-m-d' ),
			'day_name'        => $date->format( 'l' ),
			'time'            => $time,
			'end_time'        => $recurrence['end_time'] ?? null,
			'church_id'       => (int) $church_id,
			'church_name'     => $this->get_church_name( (int) $church_id ),
			'event_type'      => $event_type,
			'event_type_label' => $event_type_info['label'],
			'event_type_color' => $event_type_info['color'],
			'title'           => $template['title'] ?? '',
			'description'     => $override['reason'] ?? ( $template['description'] ?? '' ),
			'liturgical'      => $template['liturgical'] ?? array(),
			'livestream'      => $template['livestream'] ?? array(),
			'feast_day'       => $feast,
			'is_modified'     => ! empty( $override ),
			'modification_reason' => $override['reason'] ?? '',
			'intentions'      => array(),
		);
	}

	/**
	 * Build a schedule instance from an addition override.
	 *
	 * @param array     $addition Addition data.
	 * @param DateTime  $date     Date for this instance.
	 * @param array|null $feast   Feast day info if any.
	 * @return array Schedule instance.
	 */
	private function build_addition_instance( array $addition, DateTime $date, ?array $feast ): array {
		$event_type      = $addition['event_type'] ?? 'mass';
		$event_type_info = self::EVENT_TYPES[ $event_type ] ?? self::EVENT_TYPES['other'];

		return array(
			'id'              => $addition['id'] ?? uniqid( 'add_' ),
			'template_id'     => null,
			'override_id'     => $addition['id'] ?? null,
			'date'            => $date->format( 'Y-m-d' ),
			'day_name'        => $date->format( 'l' ),
			'time'            => $addition['time'] ?? '',
			'end_time'        => $addition['end_time'] ?? null,
			'church_id'       => (int) ( $addition['church_id'] ?? 0 ),
			'church_name'     => $this->get_church_name( (int) ( $addition['church_id'] ?? 0 ) ),
			'event_type'      => $event_type,
			'event_type_label' => $event_type_info['label'],
			'event_type_color' => $event_type_info['color'],
			'title'           => $addition['title'] ?? '',
			'description'     => $addition['description'] ?? '',
			'liturgical'      => $addition['liturgical'] ?? array(),
			'livestream'      => $addition['livestream'] ?? array(),
			'feast_day'       => $feast,
			'is_modified'     => false,
			'is_special'      => $addition['is_special'] ?? true,
			'intentions'      => array(),
		);
	}

	/**
	 * Get church name by ID.
	 *
	 * @param int $church_id Church post ID.
	 * @return string Church name.
	 */
	private function get_church_name( int $church_id ): string {
		if ( $church_id <= 0 ) {
			return '';
		}

		static $cache = array();

		if ( ! isset( $cache[ $church_id ] ) ) {
			$cache[ $church_id ] = get_the_title( $church_id ) ?: '';
		}

		return $cache[ $church_id ];
	}

	/**
	 * Get intentions for a date range.
	 *
	 * @param string $start_date Start date (Y-m-d).
	 * @param string $end_date   End date (Y-m-d).
	 * @return array Intentions grouped by date and template ID.
	 */
	private function get_intentions_for_range( string $start_date, string $end_date ): array {
		if ( ! post_type_exists( 'parish_intention' ) ) {
			return array();
		}

		$intentions = get_posts(
			array(
				'post_type'      => 'parish_intention',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'meta_query'     => array(
					array(
						'key'     => 'parish_intention_date',
						'value'   => array( $start_date, $end_date ),
						'compare' => 'BETWEEN',
						'type'    => 'DATE',
					),
				),
			)
		);

		$grouped = array();

		foreach ( $intentions as $intention ) {
			$date        = get_post_meta( $intention->ID, 'parish_intention_date', true );
			$template_id = get_post_meta( $intention->ID, 'parish_schedule_id', true );
			$is_public   = get_post_meta( $intention->ID, 'parish_is_public', true );

			if ( empty( $date ) ) {
				continue;
			}

			if ( ! isset( $grouped[ $date ] ) ) {
				$grouped[ $date ] = array();
			}

			if ( ! isset( $grouped[ $date ][ $template_id ] ) ) {
				$grouped[ $date ][ $template_id ] = array();
			}

			$grouped[ $date ][ $template_id ][] = array(
				'id'        => $intention->ID,
				'title'     => $intention->post_title,
				'type'      => get_post_meta( $intention->ID, 'parish_intention_type', true ),
				'requestor' => get_post_meta( $intention->ID, 'parish_requestor_name', true ),
				'is_public' => (bool) $is_public,
			);
		}

		return $grouped;
	}

	/**
	 * Get feast days for a date range.
	 *
	 * @param string $start_date Start date (Y-m-d).
	 * @param string $end_date   End date (Y-m-d).
	 * @return array Feast days keyed by date.
	 */
	private function get_feast_days_for_range( string $start_date, string $end_date ): array {
		// Use Parish_Feast_Day_Service if available.
		if ( class_exists( 'Parish_Feast_Day_Service' ) ) {
			return Parish_Feast_Day_Service::instance()->get_feast_days_for_range( $start_date, $end_date );
		}

		return array();
	}

	/**
	 * Get all templates.
	 *
	 * @return array All schedule templates.
	 */
	public function get_templates(): array {
		$this->load_data();
		return $this->templates;
	}

	/**
	 * Get a single template by ID.
	 *
	 * @param string $id Template ID.
	 * @return array|null Template data or null.
	 */
	public function get_template( string $id ): ?array {
		$this->load_data();

		foreach ( $this->templates as $template ) {
			if ( ( $template['id'] ?? '' ) === $id ) {
				return $template;
			}
		}

		return null;
	}

	/**
	 * Save templates.
	 *
	 * @param array $templates Templates to save.
	 * @return bool Success.
	 */
	public function save_templates( array $templates ): bool {
		$settings                         = get_option( 'parish_core_settings', array() );
		$settings['liturgical_schedules'] = $templates;

		$result = update_option( 'parish_core_settings', $settings );

		if ( $result ) {
			$this->templates   = $templates;
			$this->data_loaded = false;
		}

		return $result;
	}

	/**
	 * Add or update a template.
	 *
	 * @param array $template Template data.
	 * @return bool Success.
	 */
	public function save_template( array $template ): bool {
		$this->load_data();

		$id    = $template['id'] ?? '';
		$found = false;

		foreach ( $this->templates as $index => $existing ) {
			if ( ( $existing['id'] ?? '' ) === $id ) {
				$this->templates[ $index ] = $template;
				$found                     = true;
				break;
			}
		}

		if ( ! $found ) {
			if ( empty( $id ) ) {
				$template['id'] = 'sched_' . uniqid();
			}
			$template['created_at']  = current_time( 'c' );
			$this->templates[]       = $template;
		}

		$template['updated_at'] = current_time( 'c' );

		return $this->save_templates( $this->templates );
	}

	/**
	 * Delete a template.
	 *
	 * @param string $id Template ID to delete.
	 * @return bool Success.
	 */
	public function delete_template( string $id ): bool {
		$this->load_data();

		$this->templates = array_filter(
			$this->templates,
			function ( $template ) use ( $id ) {
				return ( $template['id'] ?? '' ) !== $id;
			}
		);

		return $this->save_templates( array_values( $this->templates ) );
	}

	/**
	 * Get all overrides.
	 *
	 * @return array All schedule overrides.
	 */
	public function get_overrides(): array {
		$this->load_data();
		return $this->overrides;
	}

	/**
	 * Save overrides.
	 *
	 * @param array $overrides Overrides to save.
	 * @return bool Success.
	 */
	public function save_overrides( array $overrides ): bool {
		$settings                       = get_option( 'parish_core_settings', array() );
		$settings['schedule_overrides'] = $overrides;

		$result = update_option( 'parish_core_settings', $settings );

		if ( $result ) {
			$this->overrides   = $overrides;
			$this->data_loaded = false;
		}

		return $result;
	}

	/**
	 * Add an override.
	 *
	 * @param array $override Override data.
	 * @return bool Success.
	 */
	public function add_override( array $override ): bool {
		$this->load_data();

		if ( empty( $override['id'] ) ) {
			$override['id'] = 'ovr_' . uniqid();
		}

		$override['created_at'] = current_time( 'c' );
		$override['created_by'] = get_current_user_id();

		$this->overrides[] = $override;

		return $this->save_overrides( $this->overrides );
	}

	/**
	 * Delete an override.
	 *
	 * @param string $id Override ID.
	 * @return bool Success.
	 */
	public function delete_override( string $id ): bool {
		$this->load_data();

		$this->overrides = array_filter(
			$this->overrides,
			function ( $override ) use ( $id ) {
				return ( $override['id'] ?? '' ) !== $id;
			}
		);

		return $this->save_overrides( array_values( $this->overrides ) );
	}

	/**
	 * Clean up expired overrides.
	 *
	 * Removes past overrides to keep the data clean.
	 *
	 * @param int $days_to_keep Days to keep past overrides.
	 * @return int Number of overrides removed.
	 */
	public function cleanup_expired_overrides( int $days_to_keep = 30 ): int {
		$this->load_data();

		$cutoff        = date( 'Y-m-d', strtotime( "-{$days_to_keep} days" ) );
		$original_count = count( $this->overrides );

		$this->overrides = array_filter(
			$this->overrides,
			function ( $override ) use ( $cutoff ) {
				$date = $override['date'] ?? '';
				// Keep if no date or date is in the future.
				return empty( $date ) || $date >= $cutoff;
			}
		);

		$removed = $original_count - count( $this->overrides );

		if ( $removed > 0 ) {
			$this->save_overrides( array_values( $this->overrides ) );
		}

		return $removed;
	}
}
