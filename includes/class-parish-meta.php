<?php
/**
 * Meta fields registration.
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Parish_Meta class.
 */
class Parish_Meta {

	private static ?Parish_Meta $instance = null;

	public static function instance(): Parish_Meta {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'register_meta' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_meta' ), 10, 2 );
	}

	/**
	 * Register meta fields.
	 */
	public function register_meta(): void {
		// Death notice fields.
		$this->register_post_meta( 'parish_death_notice', array(
			'date_of_death',
			'age',
			'townland',
			'funeral_date',
			'funeral_time',
			'funeral_church',
			'burial_cemetery',
			'family_flowers',
			'donations',
			'funeral_home',
			'rosary_details',
			'removal_details',
			// Shared "related church" meta (used by custom blocks).
			'related_church',
		) );

		// Baptism fields.
		$this->register_post_meta( 'parish_baptism', array(
			'child_name',
			'baptism_date',
			'baptism_church',
			'parents_names',
			'godparents',
			'celebrant',
			// Shared "related church" meta.
			'related_church',
		) );

		// Wedding fields.
		$this->register_post_meta( 'parish_wedding', array(
			'couple_names',
			'wedding_date',
			'wedding_church',
			'celebrant',
			'best_man',
			'bridesmaid',
			// Shared "related church" meta.
			'related_church',
		) );

		// Church fields.
		$this->register_post_meta( 'parish_church', array(
			'address',
			'eircode',
			'phone',
			'email',
			'lat',
			'lng',
			'mass_times',
			'confession_times',
			'accessibility',
		) );

		// School fields.
		$this->register_post_meta( 'parish_school', array(
			'address',
			'phone',
			'email',
			'website',
			'principal',
			'roll_number',
		) );

		// Newsletter fields (still registered for REST / programmatic use).
		// Editor UI is handled via blocks, not meta boxes.
		$this->register_post_meta( 'parish_newsletter', array(
			'issue_date',
			'pdf_url',
		) );
	}

	/**
	 * Register meta for a post type.
	 *
	 * @param string $post_type Post type.
	 * @param array  $fields    List of field keys (without 'parish_' prefix).
	 */
	private function register_post_meta( string $post_type, array $fields ): void {
		foreach ( $fields as $field ) {
			register_post_meta( $post_type, "parish_{$field}", array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_text_field',
			) );
		}
	}

	/**
	 * Add meta boxes.
	 *
	 * Newsletter removed here so it becomes block-only in the editor.
	 */
	public function add_meta_boxes(): void {
		$post_types = array(
			'parish_death_notice' => __( 'Death Notice Details', 'parish-core' ),
			'parish_baptism'      => __( 'Baptism Details', 'parish-core' ),
			'parish_wedding'      => __( 'Wedding Details', 'parish-core' ),
			'parish_church'       => __( 'Church Details', 'parish-core' ),
			'parish_school'       => __( 'School Details', 'parish-core' ),
			// 'parish_newsletter' => __( 'Newsletter Details', 'parish-core' ), // now block-based.
		);

		foreach ( $post_types as $type => $title ) {
			if ( post_type_exists( $type ) ) {
				add_meta_box(
					"parish_{$type}_meta",
					$title,
					array( $this, 'render_meta_box' ),
					$type,
					'normal',
					'high'
				);
			}
		}
	}

	/**
	 * Render meta box.
	 *
	 * @param \WP_Post $post Current post object.
	 */
	public function render_meta_box( \WP_Post $post ): void {
		wp_nonce_field( 'parish_save_meta', 'parish_meta_nonce' );

		$post_type = $post->post_type;
		$fields    = $this->get_fields_for_type( $post_type );

		echo '<table class="form-table parish-meta-table">';

		foreach ( $fields as $key => $label ) {
			$meta_key = "parish_{$key}";
			$value    = get_post_meta( $post->ID, $meta_key, true );
			$type     = $this->get_field_type( $key );

			echo '<tr>';
			echo '<th><label for="' . esc_attr( $meta_key ) . '">' . esc_html( $label ) . '</label></th>';
			echo '<td>';

			if ( 'textarea' === $type ) {
				echo '<textarea id="' . esc_attr( $meta_key ) . '" name="' . esc_attr( $meta_key ) . '" rows="3" class="large-text">' . esc_textarea( $value ) . '</textarea>';
			} elseif ( 'date' === $type ) {
				echo '<input type="date" id="' . esc_attr( $meta_key ) . '" name="' . esc_attr( $meta_key ) . '" value="' . esc_attr( $value ) . '" class="regular-text">';
			} elseif ( 'time' === $type ) {
				echo '<input type="time" id="' . esc_attr( $meta_key ) . '" name="' . esc_attr( $meta_key ) . '" value="' . esc_attr( $value ) . '" class="regular-text">';
			} elseif ( 'url' === $type ) {
				echo '<input type="url" id="' . esc_attr( $meta_key ) . '" name="' . esc_attr( $meta_key ) . '" value="' . esc_attr( $value ) . '" class="large-text">';
			} elseif ( 'email' === $type ) {
				echo '<input type="email" id="' . esc_attr( $meta_key ) . '" name="' . esc_attr( $meta_key ) . '" value="' . esc_attr( $value ) . '" class="regular-text">';
			} else {
				echo '<input type="text" id="' . esc_attr( $meta_key ) . '" name="' . esc_attr( $meta_key ) . '" value="' . esc_attr( $value ) . '" class="regular-text">';
			}

			echo '</td>';
			echo '</tr>';
		}

		echo '</table>';
	}

	/**
	 * Get fields for post type (for classic meta boxes).
	 *
	 * Newsletter + 'related_church' are intentionally not listed here:
	 * - Newsletter is block-only in the editor.
	 * - related_church is managed via a custom block, not this meta box UI.
	 *
	 * @param string $post_type Post type.
	 *
	 * @return array<string,string> Field key => Label.
	 */
	private function get_fields_for_type( string $post_type ): array {
		$fields = array(
			'parish_death_notice' => array(
				'date_of_death'   => __( 'Date of Death', 'parish-core' ),
				'age'             => __( 'Age', 'parish-core' ),
				'townland'        => __( 'Townland/Address', 'parish-core' ),
				'funeral_date'    => __( 'Funeral Date', 'parish-core' ),
				'funeral_time'    => __( 'Funeral Time', 'parish-core' ),
				'funeral_church'  => __( 'Funeral Church', 'parish-core' ),
				'burial_cemetery' => __( 'Burial Cemetery', 'parish-core' ),
				'family_flowers'  => __( 'Family Flowers', 'parish-core' ),
				'donations'       => __( 'Donations', 'parish-core' ),
				'funeral_home'    => __( 'Funeral Home', 'parish-core' ),
				'rosary_details'  => __( 'Rosary Details', 'parish-core' ),
				'removal_details' => __( 'Removal Details', 'parish-core' ),
				// Note: related_church is *not* shown here (handled by block UI).
			),
			'parish_baptism'      => array(
				'child_name'     => __( 'Child Name', 'parish-core' ),
				'baptism_date'   => __( 'Baptism Date', 'parish-core' ),
				'baptism_church' => __( 'Church', 'parish-core' ),
				'parents_names'  => __( 'Parents', 'parish-core' ),
				'godparents'     => __( 'Godparents', 'parish-core' ),
				'celebrant'      => __( 'Celebrant', 'parish-core' ),
				// related_church not shown here.
			),
			'parish_wedding'      => array(
				'couple_names'   => __( 'Couple Names', 'parish-core' ),
				'wedding_date'   => __( 'Wedding Date', 'parish-core' ),
				'wedding_church' => __( 'Church', 'parish-core' ),
				'celebrant'      => __( 'Celebrant', 'parish-core' ),
				'best_man'       => __( 'Best Man', 'parish-core' ),
				'bridesmaid'     => __( 'Bridesmaid', 'parish-core' ),
				// related_church not shown here.
			),
			'parish_church'       => array(
				'address'          => __( 'Address', 'parish-core' ),
				'eircode'          => __( 'Eircode', 'parish-core' ),
				'phone'            => __( 'Phone', 'parish-core' ),
				'email'            => __( 'Email', 'parish-core' ),
				'lat'              => __( 'Latitude', 'parish-core' ),
				'lng'              => __( 'Longitude', 'parish-core' ),
				'mass_times'       => __( 'Mass Times', 'parish-core' ),
				'confession_times' => __( 'Confession Times', 'parish-core' ),
				'accessibility'    => __( 'Accessibility', 'parish-core' ),
			),
			'parish_school'       => array(
				'address'     => __( 'Address', 'parish-core' ),
				'phone'       => __( 'Phone', 'parish-core' ),
				'email'       => __( 'Email', 'parish-core' ),
				'website'     => __( 'Website', 'parish-core' ),
				'principal'   => __( 'Principal', 'parish-core' ),
				'roll_number' => __( 'Roll Number', 'parish-core' ),
			),
			// 'parish_newsletter' => array(
			// 	'issue_date' => __( 'Issue Date', 'parish-core' ),
			// 	'pdf_url'    => __( 'PDF URL', 'parish-core' ),
			// ),
		);

		return $fields[ $post_type ] ?? array();
	}

	/**
	 * Get field type.
	 *
	 * @param string $key Field key (without 'parish_' prefix).
	 *
	 * @return string text|textarea|date|time|url|email
	 */
	private function get_field_type( string $key ): string {
		$types = array(
			'date_of_death'    => 'date',
			'funeral_date'     => 'date',
			'baptism_date'     => 'date',
			'wedding_date'     => 'date',
			'issue_date'       => 'date',
			'funeral_time'     => 'time',
			'email'            => 'email',
			'website'          => 'url',
			'pdf_url'          => 'url',
			'address'          => 'textarea',
			'mass_times'       => 'textarea',
			'confession_times' => 'textarea',
			'rosary_details'   => 'textarea',
			'removal_details'  => 'textarea',
		);

		return $types[ $key ] ?? 'text';
	}

	/**
	 * Save meta.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 */
	public function save_meta( int $post_id, \WP_Post $post ): void {
		if ( ! isset( $_POST['parish_meta_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['parish_meta_nonce'], 'parish_save_meta' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$fields = $this->get_fields_for_type( $post->post_type );

		foreach ( array_keys( $fields ) as $key ) {
			$meta_key = "parish_{$key}";

			if ( isset( $_POST[ $meta_key ] ) ) {
				$value = sanitize_text_field( wp_unslash( $_POST[ $meta_key ] ) );
				update_post_meta( $post_id, $meta_key, $value );
			}
		}
	}
}
