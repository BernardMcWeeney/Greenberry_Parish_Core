/**
 * Events Block
 *
 * Dynamic Gutenberg block that displays today's or this week's events.
 *
 * @package ParishCore
 */

import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	ToggleControl,
	SelectControl,
	RangeControl,
	ColorPicker,
	BaseControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';

/**
 * Block icon - calendar.
 */
const blockIcon = (
	<svg
		xmlns="http://www.w3.org/2000/svg"
		width="24"
		height="24"
		viewBox="0 0 24 24"
		fill="none"
		stroke="currentColor"
		strokeWidth="2"
		strokeLinecap="round"
		strokeLinejoin="round"
	>
		<rect width="18" height="18" x="3" y="4" rx="2" ry="2" />
		<line x1="16" x2="16" y1="2" y2="6" />
		<line x1="8" x2="8" y1="2" y2="6" />
		<line x1="3" x2="21" y1="10" y2="10" />
		<line x1="9" x2="9" y1="14" y2="14" />
		<line x1="15" x2="15" y1="14" y2="14" />
		<line x1="9" x2="9" y1="18" y2="18" />
		<line x1="15" x2="15" y1="18" y2="18" />
	</svg>
);

/**
 * View options.
 */
const VIEW_OPTIONS = [
	{ value: 'today', label: __( "Today's Events", 'parish-core' ) },
	{ value: 'week', label: __( "This Week's Events", 'parish-core' ) },
];

/**
 * Event type options.
 */
const EVENT_TYPE_OPTIONS = [
	{ value: '', label: __( 'All Types', 'parish-core' ) },
	{ value: 'parish', label: __( 'Parish Events', 'parish-core' ) },
	{ value: 'sacrament', label: __( 'Sacraments', 'parish-core' ) },
	{ value: 'feast', label: __( 'Feast Days', 'parish-core' ) },
];

/**
 * Register the Events block.
 */
registerBlockType( 'parish/events', {
	title: __( 'Events', 'parish-core' ),
	description: __(
		"Display today's or this week's parish events.",
		'parish-core'
	),
	icon: blockIcon,
	category: 'widgets',
	keywords: [
		__( 'events', 'parish-core' ),
		__( 'calendar', 'parish-core' ),
		__( 'today', 'parish-core' ),
		__( 'week', 'parish-core' ),
		__( 'schedule', 'parish-core' ),
	],

	edit: function EventsEdit( { attributes, setAttributes } ) {
		const blockProps = useBlockProps();
		const {
			view,
			limit,
			eventType,
			showIcon,
			iconColor,
			timeColor,
		} = attributes;

		return (
			<div { ...blockProps }>
				<InspectorControls>
					<PanelBody
						title={ __( 'Display Settings', 'parish-core' ) }
						initialOpen={ true }
					>
						<SelectControl
							label={ __( 'View', 'parish-core' ) }
							value={ view }
							options={ VIEW_OPTIONS }
							onChange={ ( value ) =>
								setAttributes( { view: value } )
							}
							__nextHasNoMarginBottom
							__next40pxDefaultSize
						/>

						<RangeControl
							label={ __( 'Maximum Events', 'parish-core' ) }
							value={ limit }
							onChange={ ( value ) =>
								setAttributes( { limit: value } )
							}
							min={ 1 }
							max={ 20 }
							__nextHasNoMarginBottom
						/>

						<SelectControl
							label={ __( 'Event Type', 'parish-core' ) }
							value={ eventType }
							options={ EVENT_TYPE_OPTIONS }
							onChange={ ( value ) =>
								setAttributes( { eventType: value } )
							}
							__nextHasNoMarginBottom
							__next40pxDefaultSize
						/>

						<ToggleControl
							label={ __( 'Show Calendar Icon', 'parish-core' ) }
							help={ __(
								'Display calendar icon next to event titles.',
								'parish-core'
							) }
							checked={ showIcon !== false }
							onChange={ ( value ) =>
								setAttributes( { showIcon: value } )
							}
							__nextHasNoMarginBottom
						/>
					</PanelBody>

					<PanelBody
						title={ __( 'Colors', 'parish-core' ) }
						initialOpen={ false }
					>
						<BaseControl
							label={ __( 'Icon Color', 'parish-core' ) }
							help={ __( 'Color for calendar and location icons.', 'parish-core' ) }
							__nextHasNoMarginBottom
						>
							<ColorPicker
								color={ iconColor || '#609fae' }
								onChange={ ( value ) =>
									setAttributes( { iconColor: value } )
								}
								enableAlpha={ false }
							/>
						</BaseControl>

						<BaseControl
							label={ __( 'Date/Time Color', 'parish-core' ) }
							help={ __( 'Color for date and time text.', 'parish-core' ) }
							__nextHasNoMarginBottom
						>
							<ColorPicker
								color={ timeColor || '#609fae' }
								onChange={ ( value ) =>
									setAttributes( { timeColor: value } )
								}
								enableAlpha={ false }
							/>
						</BaseControl>
					</PanelBody>
				</InspectorControls>

				<ServerSideRender
					block="parish/events"
					attributes={ attributes }
					EmptyResponsePlaceholder={ () => (
						<div className="parish-events-placeholder">
							<p>
								{ __(
									"Events — Displays today's or this week's parish events.",
									'parish-core'
								) }
							</p>
						</div>
					) }
					ErrorResponsePlaceholder={ () => (
						<div className="parish-events-placeholder">
							<p>
								{ __(
									'Error loading events.',
									'parish-core'
								) }
							</p>
						</div>
					) }
				/>
			</div>
		);
	},

	// Server-side rendered - no save function needed.
	save: () => null,
} );
