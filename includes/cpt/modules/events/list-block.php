<?php
/**
 * Events List Block
 *
 * Dynamic Gutenberg block that displays events with search, filters, and list/grid views.
 * Uses parish_event CPT with taxonomy and meta filtering.
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Events List Block class.
 */
class Parish_Events_List_Block {

	/**
	 * Register the block with WordPress.
	 *
	 * @return void
	 */
	public static function register(): void {
		register_block_type(
			'parish/events-list',
			array(
				'api_version'     => 3,
				'editor_script'   => 'parish-core-editor-blocks',
				'render_callback' => array( __CLASS__, 'render' ),
				'attributes'      => array(
					'showSearch'        => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'showFilters'       => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'showLayoutToggle'  => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'filterBySacrament' => array(
						'type'    => 'string',
						'default' => '',
					),
					'filterByChurch'    => array(
						'type'    => 'integer',
						'default' => 0,
					),
					'filterByCemetery'  => array(
						'type'    => 'boolean',
						'default' => false,
					),
					'limit'             => array(
						'type'    => 'integer',
						'default' => 10,
					),
					'layout'            => array(
						'type'    => 'string',
						'default' => 'list',
					),
					'showPagination'    => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'includeFeastDays'  => array(
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
	 * @param array $attributes Block attributes.
	 * @return string Rendered HTML.
	 */
	public static function render( array $attributes ): string {
		// Feature check.
		if ( ! Parish_Core::is_feature_enabled( 'events' ) ) {
			return '';
		}

		$show_search         = (bool) ( $attributes['showSearch'] ?? true );
		$show_filters        = (bool) ( $attributes['showFilters'] ?? true );
		$show_layout_toggle  = (bool) ( $attributes['showLayoutToggle'] ?? true );
		$filter_sacrament    = sanitize_text_field( $attributes['filterBySacrament'] ?? '' );
		$filter_church       = absint( $attributes['filterByChurch'] ?? 0 );
		$filter_cemetery     = (bool) ( $attributes['filterByCemetery'] ?? false );
		$limit               = absint( $attributes['limit'] ?? 10 );
		$layout              = sanitize_text_field( $attributes['layout'] ?? 'list' );
		$show_pagination     = (bool) ( $attributes['showPagination'] ?? true );
		$include_feast_days  = (bool) ( $attributes['includeFeastDays'] ?? true );

		// Get URL parameters for filtering.
		$search_query       = isset( $_GET['event_search'] ) ? sanitize_text_field( wp_unslash( $_GET['event_search'] ) ) : '';
		$filter_sacrament   = isset( $_GET['event_sacrament'] ) ? sanitize_text_field( wp_unslash( $_GET['event_sacrament'] ) ) : $filter_sacrament;
		$filter_church      = isset( $_GET['event_church'] ) ? absint( $_GET['event_church'] ) : $filter_church;
		$filter_cemetery    = isset( $_GET['event_cemetery'] ) ? (bool) $_GET['event_cemetery'] : $filter_cemetery;
		$current_layout     = isset( $_GET['event_layout'] ) ? sanitize_text_field( wp_unslash( $_GET['event_layout'] ) ) : $layout;
		$paged              = max( 1, absint( $_GET['event_page'] ?? 1 ) );

		// Build query.
		$args = array(
			'post_type'      => 'parish_event',
			'posts_per_page' => $limit,
			'paged'          => $paged,
			'post_status'    => 'publish',
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => 'parish_event_date',
					'value'   => current_time( 'Y-m-d' ),
					'compare' => '>=',
					'type'    => 'DATE',
				),
			),
			'orderby'        => array(
				'parish_event_date' => 'ASC',
				'parish_event_time' => 'ASC',
			),
			'meta_key'       => 'parish_event_date',
		);

		// Search filter.
		if ( ! empty( $search_query ) ) {
			$args['s'] = $search_query;
		}

		// Taxonomy filters.
		$tax_query = array();

		if ( ! empty( $filter_sacrament ) ) {
			$tax_query[] = array(
				'taxonomy' => 'parish_sacrament',
				'field'    => 'slug',
				'terms'    => $filter_sacrament,
			);
		}

		if ( ! empty( $tax_query ) ) {
			$args['tax_query'] = $tax_query;
		}

		// Meta filters.
		if ( $filter_church > 0 ) {
			$args['meta_query'][] = array(
				'key'     => 'parish_event_church_id',
				'value'   => $filter_church,
				'compare' => '=',
				'type'    => 'NUMERIC',
			);
		}

		if ( $filter_cemetery ) {
			$args['meta_query'][] = array(
				'relation' => 'OR',
				array(
					'key'     => 'parish_event_is_cemetery',
					'value'   => '1',
					'compare' => '=',
				),
				array(
					'key'     => 'parish_event_is_cemetery',
					'value'   => true,
					'compare' => '=',
				),
			);
		}

		// Exclude feast day events if option is disabled.
		if ( ! $include_feast_days ) {
			$args['meta_query'][] = array(
				'relation' => 'OR',
				array(
					'key'     => 'parish_event_is_feast_day',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'   => 'parish_event_is_feast_day',
					'value' => '0',
				),
				array(
					'key'   => 'parish_event_is_feast_day',
					'value' => '',
				),
			);
		}

		$query  = new WP_Query( $args );
		$events = $query->posts;

		// Sort events by date, priority (manual events first), then time.
		usort( $events, function ( $a, $b ) {
			$date_a     = get_post_meta( $a->ID, 'parish_event_date', true );
			$date_b     = get_post_meta( $b->ID, 'parish_event_date', true );
			$priority_a = (int) get_post_meta( $a->ID, 'parish_event_priority', true );
			$priority_b = (int) get_post_meta( $b->ID, 'parish_event_priority', true );
			$time_a     = get_post_meta( $a->ID, 'parish_event_time', true );
			$time_b     = get_post_meta( $b->ID, 'parish_event_time', true );

			// Sort by date first.
			$date_cmp = strcmp( $date_a, $date_b );
			if ( 0 !== $date_cmp ) {
				return $date_cmp;
			}

			// Then by priority (lower = higher priority, manual events = 0).
			if ( $priority_a !== $priority_b ) {
				return $priority_a <=> $priority_b;
			}

			// Then by time.
			return strcmp( $time_a, $time_b );
		} );

		// Build HTML.
		$html = '';

		// Search and Filters.
		if ( $show_search || $show_filters || $show_layout_toggle ) {
			$html .= self::render_controls( $show_search, $show_filters, $show_layout_toggle, $search_query, $filter_sacrament, $filter_church, $filter_cemetery, $current_layout );
		}

		// Events list/grid.
		if ( empty( $events ) ) {
			$html .= '<div class="parish-events-list-empty" style="padding:2em;text-align:center;background:#f9f9f9;border-radius:8px;">';
			$html .= '<p style="margin:0;opacity:0.7;">' . esc_html__( 'No upcoming events found.', 'parish-core' ) . '</p>';
			$html .= '</div>';
		} else {
			$html .= self::render_events( $events, $current_layout );
		}

		// Pagination.
		if ( $show_pagination && $query->max_num_pages > 1 ) {
			$html .= self::render_pagination( $paged, $query->max_num_pages );
		}

		wp_reset_postdata();

		// Get block wrapper attributes.
		$wrapper_attributes = get_block_wrapper_attributes(
			array(
				'class' => 'parish-events-list-block',
			)
		);

		return sprintf(
			'<div %s>%s</div>',
			$wrapper_attributes,
			$html
		);
	}

	/**
	 * Render search and filter controls.
	 *
	 * @param bool   $show_search        Show search bar.
	 * @param bool   $show_filters       Show filter dropdowns.
	 * @param bool   $show_layout_toggle Show layout toggle.
	 * @param string $search_query       Current search query.
	 * @param string $filter_sacrament   Current sacrament filter.
	 * @param int    $filter_church      Current church filter.
	 * @param bool   $filter_cemetery    Current cemetery filter.
	 * @param string $current_layout     Current layout (list/grid).
	 * @return string HTML output.
	 */
	private static function render_controls( bool $show_search, bool $show_filters, bool $show_layout_toggle, string $search_query, string $filter_sacrament, int $filter_church, bool $filter_cemetery, string $current_layout ): string {
		$html = '<div class="parish-events-list-controls" style="display:flex;flex-wrap:wrap;gap:12px;margin-bottom:24px;padding:16px;background:#f6f7f7;border-radius:8px;">';

		// Search bar.
		if ( $show_search ) {
			$html .= '<div class="parish-events-search" style="flex:1;min-width:200px;">';
			$html .= '<input type="text" name="event_search" placeholder="' . esc_attr__( 'Search events...', 'parish-core' ) . '" value="' . esc_attr( $search_query ) . '" style="width:100%;padding:8px 12px;border:1px solid #ddd;border-radius:4px;font-size:14px;" />';
			$html .= '</div>';
		}

		// Filters.
		if ( $show_filters ) {
			// Sacrament filter.
			$sacraments = get_terms(
				array(
					'taxonomy'   => 'parish_sacrament',
					'hide_empty' => false,
				)
			);

			if ( ! is_wp_error( $sacraments ) && ! empty( $sacraments ) ) {
				$html .= '<div class="parish-events-filter-sacrament">';
				$html .= '<select name="event_sacrament" style="padding:8px 12px;border:1px solid #ddd;border-radius:4px;font-size:14px;">';
				$html .= '<option value="">' . esc_html__( 'All Sacraments', 'parish-core' ) . '</option>';
				foreach ( $sacraments as $sacrament ) {
					$html .= sprintf(
						'<option value="%s" %s>%s</option>',
						esc_attr( $sacrament->slug ),
						selected( $filter_sacrament, $sacrament->slug, false ),
						esc_html( $sacrament->name )
					);
				}
				$html .= '</select>';
				$html .= '</div>';
			}

			// Church filter.
			$churches = get_posts(
				array(
					'post_type'      => 'parish_church',
					'posts_per_page' => -1,
					'post_status'    => 'publish',
					'orderby'        => 'title',
					'order'          => 'ASC',
				)
			);

			if ( ! empty( $churches ) ) {
				$html .= '<div class="parish-events-filter-church">';
				$html .= '<select name="event_church" style="padding:8px 12px;border:1px solid #ddd;border-radius:4px;font-size:14px;">';
				$html .= '<option value="0">' . esc_html__( 'All Churches', 'parish-core' ) . '</option>';
				foreach ( $churches as $church ) {
					$html .= sprintf(
						'<option value="%d" %s>%s</option>',
						$church->ID,
						selected( $filter_church, $church->ID, false ),
						esc_html( $church->post_title )
					);
				}
				$html .= '</select>';
				$html .= '</div>';
			}

			// Cemetery filter.
			$html .= '<div class="parish-events-filter-cemetery">';
			$html .= '<label style="display:flex;align-items:center;gap:6px;cursor:pointer;">';
			$html .= '<input type="checkbox" name="event_cemetery" value="1" ' . checked( $filter_cemetery, true, false ) . ' />';
			$html .= esc_html__( 'Cemetery Events', 'parish-core' );
			$html .= '</label>';
			$html .= '</div>';
		}

		// Layout toggle.
		if ( $show_layout_toggle ) {
			$html .= '<div class="parish-events-layout-toggle" style="display:flex;gap:4px;">';
			$html .= sprintf(
				'<button type="button" class="layout-btn %s" data-layout="list" style="padding:8px 12px;border:1px solid #ddd;border-radius:4px;background:%s;cursor:pointer;" title="%s">',
				$current_layout === 'list' ? 'active' : '',
				$current_layout === 'list' ? '#2271b1' : '#fff',
				esc_attr__( 'List View', 'parish-core' )
			);
			$html .= '<svg width="16" height="16" viewBox="0 0 24 24" fill="' . ( $current_layout === 'list' ? '#fff' : '#333' ) . '"><path d="M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 4h14v-2H7v2zm0 4h14v-2H7v2zM7 7v2h14V7H7z"/></svg>';
			$html .= '</button>';
			$html .= sprintf(
				'<button type="button" class="layout-btn %s" data-layout="grid" style="padding:8px 12px;border:1px solid #ddd;border-radius:4px;background:%s;cursor:pointer;" title="%s">',
				$current_layout === 'grid' ? 'active' : '',
				$current_layout === 'grid' ? '#2271b1' : '#fff',
				esc_attr__( 'Grid View', 'parish-core' )
			);
			$html .= '<svg width="16" height="16" viewBox="0 0 24 24" fill="' . ( $current_layout === 'grid' ? '#fff' : '#333' ) . '"><path d="M4 8h4V4H4v4zm6 12h4v-4h-4v4zm-6 0h4v-4H4v4zm0-6h4v-4H4v4zm6 0h4v-4h-4v4zm6-10v4h4V4h-4zm-6 4h4V4h-4v4zm6 6h4v-4h-4v4zm0 6h4v-4h-4v4z"/></svg>';
			$html .= '</button>';
			$html .= '</div>';
		}

		$html .= '</div>';

		// JavaScript for interactivity.
		$html .= '<script>
		(function() {
			const controls = document.querySelector(".parish-events-list-controls");
			if (!controls) return;

			// Auto-submit on filter change.
			controls.querySelectorAll("select, input[type=checkbox]").forEach(function(el) {
				el.addEventListener("change", function() {
					updateFilters();
				});
			});

			// Search on Enter key.
			const searchInput = controls.querySelector("input[name=event_search]");
			if (searchInput) {
				searchInput.addEventListener("keypress", function(e) {
					if (e.key === "Enter") {
						e.preventDefault();
						updateFilters();
					}
				});
			}

			// Layout toggle.
			controls.querySelectorAll(".layout-btn").forEach(function(btn) {
				btn.addEventListener("click", function() {
					const layout = this.dataset.layout;
					const url = new URL(window.location.href);
					url.searchParams.set("event_layout", layout);
					window.location.href = url.toString();
				});
			});

			function updateFilters() {
				const url = new URL(window.location.href);
				const search = controls.querySelector("input[name=event_search]");
				const sacrament = controls.querySelector("select[name=event_sacrament]");
				const church = controls.querySelector("select[name=event_church]");
				const cemetery = controls.querySelector("input[name=event_cemetery]");

				if (search && search.value) url.searchParams.set("event_search", search.value);
				else url.searchParams.delete("event_search");

				if (sacrament && sacrament.value) url.searchParams.set("event_sacrament", sacrament.value);
				else url.searchParams.delete("event_sacrament");

				if (church && church.value !== "0") url.searchParams.set("event_church", church.value);
				else url.searchParams.delete("event_church");

				if (cemetery && cemetery.checked) url.searchParams.set("event_cemetery", "1");
				else url.searchParams.delete("event_cemetery");

				url.searchParams.delete("event_page"); // Reset to page 1.
				window.location.href = url.toString();
			}
		})();
		</script>';

		return $html;
	}

	/**
	 * Render events in list or grid layout.
	 *
	 * @param array  $events Array of WP_Post objects.
	 * @param string $layout Layout type (list/grid).
	 * @return string HTML output.
	 */
	private static function render_events( array $events, string $layout ): string {
		$is_grid = $layout === 'grid';

		$html = '<div class="parish-events-list-items" style="' . ( $is_grid ? 'display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px;' : '' ) . '">';

		foreach ( $events as $event ) {
			$html .= self::render_event_item( $event, $is_grid );
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Render a single event item.
	 *
	 * @param WP_Post $event   Event post object.
	 * @param bool    $is_grid Whether rendering as grid card.
	 * @return string HTML output.
	 */
	private static function render_event_item( WP_Post $event, bool $is_grid ): string {
		$event_date     = get_post_meta( $event->ID, 'parish_event_date', true );
		$event_time     = get_post_meta( $event->ID, 'parish_event_time', true );
		$event_end_time = get_post_meta( $event->ID, 'parish_event_end_time', true );
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

		// Get sacraments.
		$sacraments      = wp_get_post_terms( $event->ID, 'parish_sacrament', array( 'fields' => 'names' ) );
		$sacrament_names = is_wp_error( $sacraments ) ? '' : implode( ', ', $sacraments );

		// Format date/time.
		$date_display = '';
		if ( $event_date ) {
			$date_display = date_i18n( 'l, F j, Y', strtotime( $event_date ) );
		}

		$time_display = '';
		if ( $event_time ) {
			$time_display = date_i18n( 'g:i A', strtotime( "2000-01-01 $event_time" ) );
			if ( $event_end_time ) {
				$time_display .= ' - ' . date_i18n( 'g:i A', strtotime( "2000-01-01 $event_end_time" ) );
			}
		}

		// Get thumbnail.
		$thumbnail = '';
		if ( $is_grid && has_post_thumbnail( $event->ID ) ) {
			$thumbnail = get_the_post_thumbnail( $event->ID, 'medium', array( 'style' => 'width:100%;height:160px;object-fit:cover;border-radius:8px 8px 0 0;' ) );
		}

		// Build HTML.
		if ( $is_grid ) {
			// Grid/card layout.
			$html = '<div class="parish-event-card" style="background:#fff;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.08);overflow:hidden;transition:transform 0.2s,box-shadow 0.2s;">';

			if ( $thumbnail ) {
				$html .= '<div class="event-card-thumbnail">' . $thumbnail . '</div>';
			}

			$html .= '<div class="event-card-content" style="padding:16px;">';
			$html .= '<h3 class="event-card-title" style="margin:0 0 8px;font-size:18px;"><a href="' . esc_url( get_permalink( $event ) ) . '" style="text-decoration:none;color:inherit;">' . esc_html( $event->post_title ) . '</a></h3>';

			if ( $date_display ) {
				$html .= '<div class="event-card-date" style="font-size:14px;color:#2271b1;margin-bottom:4px;"><i class="fa-regular fa-calendar" style="margin-right:6px;"></i>' . esc_html( $date_display ) . '</div>';
			}

			if ( $time_display ) {
				$html .= '<div class="event-card-time" style="font-size:14px;color:#666;margin-bottom:4px;"><i class="fa-regular fa-clock" style="margin-right:6px;"></i>' . esc_html( $time_display ) . '</div>';
			}

			if ( $event_location || $church_name ) {
				$html .= '<div class="event-card-location" style="font-size:14px;color:#666;"><i class="fa-solid fa-location-dot" style="margin-right:6px;"></i>' . esc_html( $event_location ?: $church_name ) . '</div>';
			}

			if ( $sacrament_names ) {
				$html .= '<div class="event-card-sacrament" style="margin-top:8px;"><span style="display:inline-block;padding:4px 8px;background:#e8f4fc;color:#2271b1;border-radius:4px;font-size:12px;">' . esc_html( $sacrament_names ) . '</span></div>';
			}

			$html .= '</div>'; // .event-card-content
			$html .= '</div>'; // .parish-event-card
		} else {
			// List layout.
			$html = '<div class="parish-event-row" style="padding:16px 0;border-bottom:1px solid #eee;display:flex;gap:16px;align-items:flex-start;">';

			// Date box.
			if ( $event_date ) {
				$html .= '<div class="event-date-box" style="flex-shrink:0;width:60px;text-align:center;background:#2271b1;color:#fff;border-radius:8px;padding:8px;">';
				$html .= '<div style="font-size:24px;font-weight:bold;line-height:1;">' . esc_html( date_i18n( 'j', strtotime( $event_date ) ) ) . '</div>';
				$html .= '<div style="font-size:12px;text-transform:uppercase;">' . esc_html( date_i18n( 'M', strtotime( $event_date ) ) ) . '</div>';
				$html .= '</div>';
			}

			// Content.
			$html .= '<div class="event-content" style="flex:1;">';
			$html .= '<h3 class="event-title" style="margin:0 0 4px;font-size:18px;"><a href="' . esc_url( get_permalink( $event ) ) . '" style="text-decoration:none;color:inherit;">' . esc_html( $event->post_title ) . '</a></h3>';

			$details = array();
			if ( $time_display ) {
				$details[] = '<i class="fa-regular fa-clock"></i> ' . esc_html( $time_display );
			}
			if ( $event_location || $church_name ) {
				$details[] = '<i class="fa-solid fa-location-dot"></i> ' . esc_html( $event_location ?: $church_name );
			}

			if ( ! empty( $details ) ) {
				$html .= '<div class="event-details" style="font-size:14px;color:#666;display:flex;flex-wrap:wrap;gap:16px;">' . implode( '', array_map( function( $d ) { return '<span>' . $d . '</span>'; }, $details ) ) . '</div>';
			}

			if ( $sacrament_names ) {
				$html .= '<div class="event-sacrament" style="margin-top:6px;"><span style="display:inline-block;padding:2px 8px;background:#e8f4fc;color:#2271b1;border-radius:4px;font-size:12px;">' . esc_html( $sacrament_names ) . '</span></div>';
			}

			$html .= '</div>'; // .event-content
			$html .= '</div>'; // .parish-event-row
		}

		return $html;
	}

	/**
	 * Render pagination.
	 *
	 * @param int $current_page Current page number.
	 * @param int $total_pages  Total number of pages.
	 * @return string HTML output.
	 */
	private static function render_pagination( int $current_page, int $total_pages ): string {
		$html = '<div class="parish-events-pagination" style="display:flex;justify-content:center;gap:8px;margin-top:24px;">';

		// Previous.
		if ( $current_page > 1 ) {
			$html .= '<a href="' . esc_url( add_query_arg( 'event_page', $current_page - 1 ) ) . '" style="padding:8px 16px;background:#f0f0f0;border-radius:4px;text-decoration:none;color:#333;">' . esc_html__( 'Previous', 'parish-core' ) . '</a>';
		}

		// Page numbers.
		for ( $i = 1; $i <= $total_pages; $i++ ) {
			if ( $i === $current_page ) {
				$html .= '<span style="padding:8px 16px;background:#2271b1;color:#fff;border-radius:4px;">' . $i . '</span>';
			} else {
				$html .= '<a href="' . esc_url( add_query_arg( 'event_page', $i ) ) . '" style="padding:8px 16px;background:#f0f0f0;border-radius:4px;text-decoration:none;color:#333;">' . $i . '</a>';
			}
		}

		// Next.
		if ( $current_page < $total_pages ) {
			$html .= '<a href="' . esc_url( add_query_arg( 'event_page', $current_page + 1 ) ) . '" style="padding:8px 16px;background:#f0f0f0;border-radius:4px;text-decoration:none;color:#333;">' . esc_html__( 'Next', 'parish-core' ) . '</a>';
		}

		$html .= '</div>';

		return $html;
	}
}
