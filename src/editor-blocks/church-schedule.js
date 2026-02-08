/**
 * Church Schedule Block
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
	CheckboxControl,
	ColorPicker,
	BaseControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import ServerSideRender from '@wordpress/server-side-render';

/**
 * Block icon - church.
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
		<path d="M10 2v4" />
		<path d="M8 4h4" />
		<path d="M10 6v4" />
		<path d="M3 10l7-6 7 6" />
		<path d="M3 22V10" />
		<path d="M17 22V10" />
		<path d="M3 22h14" />
		<rect x="7" y="14" width="6" height="8" />
	</svg>
);

/**
 * Event type options.
 */
const EVENT_TYPE_OPTIONS = [
	{ value: 'mass', label: __( 'Mass', 'parish-core' ) },
	{ value: 'confession', label: __( 'Confession', 'parish-core' ) },
	{ value: 'adoration', label: __( 'Adoration', 'parish-core' ) },
	{ value: 'rosary', label: __( 'Rosary', 'parish-core' ) },
	{ value: 'other', label: __( 'Other', 'parish-core' ) },
];

/**
 * Register the Church Schedule block.
 */
registerBlockType( 'parish/church-schedule', {
	title: __( 'Church Schedule', 'parish-core' ),
	description: __(
		'Display liturgical schedules for a church with Font Awesome icons. Works in Query Loops.',
		'parish-core'
	),
	icon: blockIcon,
	category: 'widgets',
	keywords: [
		__( 'church', 'parish-core' ),
		__( 'schedule', 'parish-core' ),
		__( 'times', 'parish-core' ),
		__( 'mass', 'parish-core' ),
		__( 'confession', 'parish-core' ),
	],
	usesContext: [ 'postType', 'postId' ],

	edit: function ChurchScheduleEdit( { attributes, setAttributes, context } ) {
		const blockProps = useBlockProps();
		const {
			showIcon,
			showLivestream,
			showAllDays,
			eventTypes,
			iconColor,
			timeColor,
		} = attributes;

		const toggleEventType = ( type ) => {
			const current = eventTypes || [ 'mass', 'confession' ];
			if ( current.includes( type ) ) {
				setAttributes( {
					eventTypes: current.filter( ( t ) => t !== type ),
				} );
			} else {
				setAttributes( {
					eventTypes: [ ...current, type ],
				} );
			}
		};

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
								'Show all days of the week, with "No events" for days without scheduled events.',
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

						<BaseControl
							label={ __( 'Event Types to Show', 'parish-core' ) }
							__nextHasNoMarginBottom
						>
							{ EVENT_TYPE_OPTIONS.map( ( option ) => (
								<CheckboxControl
									key={ option.value }
									label={ option.label }
									checked={ ( eventTypes || [ 'mass', 'confession' ] ).includes( option.value ) }
									onChange={ () => toggleEventType( option.value ) }
									__nextHasNoMarginBottom
								/>
							) ) }
						</BaseControl>
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
					block="parish/church-schedule"
					attributes={ attributes }
					urlQueryArgs={ { post_id: context.postId } }
					EmptyResponsePlaceholder={ () => (
						<div className="parish-church-schedule-placeholder">
							<p>
								{ __(
									'Church Schedule — Add this block inside a Church Query Loop to display the schedule.',
									'parish-core'
								) }
							</p>
						</div>
					) }
					ErrorResponsePlaceholder={ () => (
						<div className="parish-church-schedule-placeholder">
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
