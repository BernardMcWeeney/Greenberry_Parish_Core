/**
 * Today Mass Block
 *
 * Dynamic Gutenberg block that displays today's Mass times.
 * Shows times across all churches or filtered by a specific church.
 *
 * @package ParishCore
 */

import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import {
	PanelBody,
	ToggleControl,
	SelectControl,
	ColorPicker,
	BaseControl,
	Spinner,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';

/**
 * Block icon - calendar today.
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
		<path d="M8 14h.01" />
		<path d="M12 14h.01" />
		<path d="M16 14h.01" />
		<path d="M8 18h.01" />
		<path d="M12 18h.01" />
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
 * Register the Today Mass block.
 */
registerBlockType( 'parish/today-mass', {
	title: __( 'Today Mass', 'parish-core' ),
	description: __(
		"Display today's Mass times across all churches or for a specific church.",
		'parish-core'
	),
	icon: blockIcon,
	category: 'widgets',
	keywords: [
		__( 'today', 'parish-core' ),
		__( 'mass', 'parish-core' ),
		__( 'schedule', 'parish-core' ),
		__( 'times', 'parish-core' ),
		__( 'daily', 'parish-core' ),
	],

	edit: function TodayMassEdit( { attributes, setAttributes } ) {
		const blockProps = useBlockProps();
		const {
			churchId,
			eventType,
			showIcon,
			showDate,
			showNotes,
			iconColor,
			timeColor,
		} = attributes;

		// Fetch all parish_church posts for the dropdown.
		const churches = useSelect( ( select ) => {
			return select( 'core' ).getEntityRecords(
				'postType',
				'parish_church',
				{
					per_page: -1,
					_fields: [ 'id', 'title' ],
				}
			);
		}, [] );

		// Build church options.
		const churchOptions = [
			{ value: 0, label: __( 'All Churches', 'parish-core' ) },
		];

		if ( churches ) {
			churches.forEach( ( church ) => {
				churchOptions.push( {
					value: church.id,
					label: church.title.rendered || __( '(no title)', 'parish-core' ),
				} );
			} );
		}

		return (
			<div { ...blockProps }>
				<InspectorControls>
					<PanelBody
						title={ __( 'Display Settings', 'parish-core' ) }
						initialOpen={ true }
					>
						{ churches === undefined ? (
							<>
								<Spinner />
								{ ' ' }
								{ __( 'Loading churches...', 'parish-core' ) }
							</>
						) : (
							<SelectControl
								label={ __( 'Church', 'parish-core' ) }
								value={ churchId || 0 }
								options={ churchOptions }
								onChange={ ( value ) =>
									setAttributes( { churchId: parseInt( value, 10 ) } )
								}
								__nextHasNoMarginBottom
								__next40pxDefaultSize
							/>
						) }

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
							label={ __( 'Show Icon', 'parish-core' ) }
							help={ __(
								'Display video/live icon.',
								'parish-core'
							) }
							checked={ showIcon !== false }
							onChange={ ( value ) =>
								setAttributes( { showIcon: value } )
							}
							__nextHasNoMarginBottom
						/>

						<ToggleControl
							label={ __( 'Show Date', 'parish-core' ) }
							help={ __(
								"Display today's date above the schedule.",
								'parish-core'
							) }
							checked={ showDate }
							onChange={ ( value ) =>
								setAttributes( { showDate: value } )
							}
							__nextHasNoMarginBottom
						/>

						<ToggleControl
							label={ __( 'Show Notes', 'parish-core' ) }
							help={ __(
								'Display notes for each Mass time.',
								'parish-core'
							) }
							checked={ showNotes }
							onChange={ ( value ) =>
								setAttributes( { showNotes: value } )
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
							help={ __( 'Color for clock and video icons.', 'parish-core' ) }
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
							help={ __( 'Color for time text.', 'parish-core' ) }
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
					block="parish/today-mass"
					attributes={ attributes }
					EmptyResponsePlaceholder={ () => (
						<div className="parish-today-mass-placeholder">
							<p>
								{ __(
									"Today Mass — Displays today's Mass times.",
									'parish-core'
								) }
							</p>
						</div>
					) }
					ErrorResponsePlaceholder={ () => (
						<div className="parish-today-mass-placeholder">
							<p>
								{ __(
									'Error loading schedule.',
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
