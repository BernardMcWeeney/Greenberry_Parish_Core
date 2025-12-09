<?php
/**
 * Block templates for Parish CPTs.
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Parish_CPT_Templates class.
 *
 * Provides block editor templates for custom post types.
 */
class Parish_CPT_Templates {

	/**
	 * Death Notice template.
	 */
	public static function get_death_notice_template(): array {
		return array(
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Name of deceased', 'parish-core' ),
				),
			),
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Townland / address', 'parish-core' ),
				),
			),
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Date of death (e.g. 24 March 2025)', 'parish-core' ),
				),
			),
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Funeral details (date, time, church)', 'parish-core' ),
				),
			),
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Burial cemetery', 'parish-core' ),
				),
			),
			array(
				'core/details',
				array(
					'summary'     => __( 'Additional details (flowers, donations, rosary, removal…)', 'parish-core' ),
					'showContent' => false,
				),
			),
		);
	}

	/**
	 * Baptism Notice template.
	 */
	public static function get_baptism_template(): array {
		return array(
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Child name', 'parish-core' ),
				),
			),
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Baptism date (e.g. 24 March 2025)', 'parish-core' ),
				),
			),
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Church', 'parish-core' ),
				),
			),
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Parents', 'parish-core' ),
				),
			),
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Godparents', 'parish-core' ),
				),
			),
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Celebrant', 'parish-core' ),
				),
			),
		);
	}

	/**
	 * Wedding Notice template.
	 */
	public static function get_wedding_template(): array {
		return array(
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Couple names', 'parish-core' ),
				),
			),
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Wedding date (e.g. 24 March 2025)', 'parish-core' ),
				),
			),
			array(
                'parish/related-church',
                array(),
            ),
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Celebrant', 'parish-core' ),
				),
			),
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Best man', 'parish-core' ),
				),
			),
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Bridesmaid', 'parish-core' ),
				),
			),
		);
	}

	/**
	 * Church template.
	 */
	public static function get_church_template(): array {
		return array(
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Address', 'parish-core' ),
				),
			),
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Eircode', 'parish-core' ),
				),
			),
			array(
				'core/list',
				array(
					'placeholder' => __( "Contact info:\n• Phone\n• Email\n• Website", 'parish-core' ),
				),
			),
			array(
				'core/heading',
				array(
					'level'   => 3,
					'content' => __( 'Mass times', 'parish-core' ),
				),
			),
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'List Mass times…', 'parish-core' ),
				),
			),
			array(
				'core/heading',
				array(
					'level'   => 3,
					'content' => __( 'Confession times', 'parish-core' ),
				),
			),
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'List confession times…', 'parish-core' ),
				),
			),
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Accessibility (parking, ramp, hearing loop…)', 'parish-core' ),
				),
			),
		);
	}

	/**
	 * School template.
	 */
	public static function get_school_template(): array {
		return array(
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Address', 'parish-core' ),
				),
			),
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Principal', 'parish-core' ),
				),
			),
			array(
				'core/list',
				array(
					'placeholder' => __( "Contact info:\n• Phone\n• Email\n• Website", 'parish-core' ),
				),
			),
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Roll number', 'parish-core' ),
				),
			),
		);
	}

	/**
	 * Cemetery template.
	 */
	public static function get_cemetery_template(): array {
		return array(
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Location / address', 'parish-core' ),
				),
			),
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Opening times or visiting information…', 'parish-core' ),
				),
			),
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Any additional notes…', 'parish-core' ),
				),
			),
		);
	}

	/**
	 * Group template.
	 */
	public static function get_group_template(): array {
		return array(
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Describe this parish group…', 'parish-core' ),
				),
			),
			array(
				'core/list',
				array(
					'placeholder' => __( "Meeting times / activities:\n• …", 'parish-core' ),
				),
			),
		);
	}

	/**
	 * Newsletter template.
	 */
	public static function get_newsletter_template(): array {
		return array(
			array(
				'core/post-date',
				array(
					'field' => "date"
				),
			),
			array(
				'core/file',
				array(
					'showDownloadButton' => true,
				),
			),
			array(
            'core/paragraph',
                array(
                    'placeholder' => __( 'Optional introduction or notes for this newsletter…', 'parish-core' ),
                    // maybe this one is allowed to be removed, so no lock here:
                    // 'lock' => array( 'move' => true, 'remove' => true ),
                ),
            ),
		);
	}

	/**
	 * Parish News template.
	 */
	public static function get_news_template(): array {
		return array(
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Introductory paragraph for this news item…', 'parish-core' ),
				),
			),
		);
	}

	/**
	 * Gallery template.
	 */
	public static function get_gallery_template(): array {
		return array(
			array(
				'core/gallery',
				array(),
			),
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Gallery description or notes…', 'parish-core' ),
				),
			),
		);
	}

	/**
	 * Reflection template.
	 */
	public static function get_reflection_template(): array {
		return array(
			array(
				'core/quote',
				array(),
			),
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Further reflection, context or commentary…', 'parish-core' ),
				),
			),
		);
	}

	/**
	 * Prayer template.
	 */
	public static function get_prayer_template(): array {
		return array(
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Intro text (optional)…', 'parish-core' ),
				),
			),
			array(
				'core/separator',
				array(),
			),
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Prayer text…', 'parish-core' ),
				),
			),
			array(
				'core/paragraph',
				array(
					'placeholder' => __( 'Source or attribution (optional)…', 'parish-core' ),
				),
			),
		);
	}
}
