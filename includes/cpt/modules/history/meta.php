<?php
/**
 * Post meta: Parish History
 *
 * @package ParishCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'parish_history_era' => array(
		'type'         => 'string',
		'description'  => __( 'Historical era or time period', 'parish-core' ),
		'single'       => true,
		'show_in_rest' => true,
	),
	'parish_history_year' => array(
		'type'         => 'string',
		'description'  => __( 'Year or date of the historical event', 'parish-core' ),
		'single'       => true,
		'show_in_rest' => true,
	),
);
