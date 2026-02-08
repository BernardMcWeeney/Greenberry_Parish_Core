/**
 * Mass Schedule Block
 *
 * Dynamic Gutenberg block that displays liturgical schedules for a church.
 * Works inside Query Loops by automatically resolving the current post ID
 * from block context. Renders with Font Awesome icons and configurable colors.
 *
 * @package ParishCore
 */

import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	ToggleControl,
	SelectControl,
	ColorPicker,
	BaseControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';

/**
 * Block icon - calendar with clock.
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
		<circle cx="12" cy="15" r="3" />
		<path d="M12 13v2l1 1" />
	</svg>
);

/**
 * Event type options.
 */
const EVENT_TYPE_OPTIONS = [
	{ value: '', label: __( 'All Types', 'parish-core' ) },
	{ value: 'mass', label: __( 'Mass Only', 'parish-core' ) },
	{ value: 'confession', label: __( 'Confession Only', 'parish-core' ) },
	{ value: 'adoration', label: __( 'Adoration Only', 'parish-core' ) },
	{ value: 'rosary', label: __( 'Rosary Only', 'parish-core' ) },
];

/**
 * Register the Mass Schedule block.
 */
registerBlockType( 'parish/mass-schedule', {
	title: __( 'Mass Schedule', 'parish-core' ),
	description: __(
		'Display liturgical schedules for a church with Font Awesome icons. Works in Query Loops.',
		'parish-core'
	),
	icon: blockIcon,
	category: 'widgets',
	keywords: [
		__( 'mass', 'parish-core' ),
		__( 'schedule', 'parish-core' ),
		__( 'times', 'parish-core' ),
		__( 'church', 'parish-core' ),
		__( 'liturgy', 'parish-core' ),
	],
	usesContext: [ 'postType', 'postId' ],

	edit: function MassScheduleEdit( { attributes, setAttributes, context } ) {
		const blockProps = useBlockProps();
		const {
			showIcon,
			showLivestream,
			showSpecial,
			showAllDays,
			eventType,
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
						<ToggleControl
							label={ __( 'Show Clock Icon', 'parish-core' ) }
							help={ __(
								'Display clock icon at the start of each row.',
								'parish-core'
							) }
							checked={ showIcon !== false }
							onChange={ ( value ) =>
								setAttributes( { showIcon: value } )
							}
							__nextHasNoMarginBottom
						/>

						<ToggleControl
							label={ __( 'Show All Days', 'parish-core' ) }
							help={ __(
								'Show all days of the week, with "No Mass" for days without scheduled masses.',
								'parish-core'
							) }
							checked={ showAllDays }
							onChange={ ( value ) =>
								setAttributes( { showAllDays: value } )
							}
							__nextHasNoMarginBottom
						/>

						<ToggleControl
							label={ __( 'Show Livestream Indicators', 'parish-core' ) }
							help={ __(
								'Display video icon for livestreamed services.',
								'parish-core'
							) }
							checked={ showLivestream }
							onChange={ ( value ) =>
								setAttributes( { showLivestream: value } )
							}
							__nextHasNoMarginBottom
						/>

						<ToggleControl
							label={ __( 'Show Special Events', 'parish-core' ) }
							help={ __(
								'Include Holy Days and special liturgical events.',
								'parish-core'
							) }
							checked={ showSpecial }
							onChange={ ( value ) =>
								setAttributes( { showSpecial: value } )
							}
							__nextHasNoMarginBottom
						/>

						<SelectControl
							label={ __( 'Event Type Filter', 'parish-core' ) }
							value={ eventType }
							options={ EVENT_TYPE_OPTIONS }
							onChange={ ( value ) =>
								setAttributes( { eventType: value } )
							}
							__nextHasNoMarginBottom
							__next40pxDefaultSize
						/>
					</PanelBody>

					<PanelBody
						title={ __( 'Colors', 'parish-core' ) }
						initialOpen={ false }
					>
						<BaseControl
							label={ __( 'Icon Color', 'parish-core' ) }
							help={ __( 'Color for clock and video icons. Leave empty to use theme accent color.', 'parish-core' ) }
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
							label={ __( 'Time Color', 'parish-core' ) }
							help={ __( 'Color for time text. Leave empty to use theme accent color.', 'parish-core' ) }
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
					block="parish/mass-schedule"
					attributes={ attributes }
					urlQueryArgs={ { post_id: context.postId } }
					EmptyResponsePlaceholder={ () => (
						<div className="parish-mass-schedule-placeholder">
							<p>
								{ __(
									'Mass Schedule — Add this block inside a Church Query Loop to display the schedule.',
									'parish-core'
								) }
							</p>
						</div>
					) }
					ErrorResponsePlaceholder={ () => (
						<div className="parish-mass-schedule-error">
							<p>
								{ __(
									'Error loading schedule. Make sure this block is inside a Church Query Loop.',
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
