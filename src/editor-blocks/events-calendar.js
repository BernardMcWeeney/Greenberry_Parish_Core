/**
 * Events Calendar Block
 *
 * Full month calendar view with iCal subscription support.
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
	Spinner,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import ServerSideRender from '@wordpress/server-side-render';

/**
 * Block icon - calendar grid.
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
		<rect x="6" y="13" width="3" height="3" fill="currentColor" />
		<rect x="10.5" y="13" width="3" height="3" fill="currentColor" />
		<rect x="15" y="13" width="3" height="3" fill="currentColor" />
	</svg>
);

/**
 * Register the Events Calendar block.
 */
registerBlockType( 'parish/events-calendar', {
	title: __( 'Events Calendar', 'parish-core' ),
	description: __(
		'Display a full month calendar of parish events with iCal subscription and download options.',
		'parish-core'
	),
	icon: blockIcon,
	category: 'widgets',
	keywords: [
		__( 'calendar', 'parish-core' ),
		__( 'events', 'parish-core' ),
		__( 'month', 'parish-core' ),
		__( 'ical', 'parish-core' ),
		__( 'subscribe', 'parish-core' ),
		__( 'google calendar', 'parish-core' ),
	],

	edit: function EventsCalendarEdit( { attributes, setAttributes } ) {
		const blockProps = useBlockProps();
		const {
			sacrament,
			churchId,
			cemeteryId,
			autoDetect,
			showSubscribe,
			showDownload,
			iconColor,
			timeColor,
		} = attributes;

		// Fetch sacraments from REST API.
		const { sacraments, isLoadingSacraments } = useSelect( ( select ) => {
			const { getEntityRecords, isResolving } = select( 'core' );
			return {
				sacraments: getEntityRecords( 'taxonomy', 'parish_sacrament', {
					per_page: -1,
					orderby: 'name',
					order: 'asc',
				} ),
				isLoadingSacraments: isResolving( 'getEntityRecords', [
					'taxonomy',
					'parish_sacrament',
					{ per_page: -1, orderby: 'name', order: 'asc' },
				] ),
			};
		}, [] );

		// Fetch churches from REST API.
		const { churches, isLoadingChurches } = useSelect( ( select ) => {
			const { getEntityRecords, isResolving } = select( 'core' );
			return {
				churches: getEntityRecords( 'postType', 'parish_church', {
					per_page: -1,
					orderby: 'title',
					order: 'asc',
					status: 'publish',
				} ),
				isLoadingChurches: isResolving( 'getEntityRecords', [
					'postType',
					'parish_church',
					{ per_page: -1, orderby: 'title', order: 'asc', status: 'publish' },
				] ),
			};
		}, [] );

		// Fetch cemeteries from REST API.
		const { cemeteries, isLoadingCemeteries } = useSelect( ( select ) => {
			const { getEntityRecords, isResolving } = select( 'core' );
			return {
				cemeteries: getEntityRecords( 'postType', 'parish_cemetery', {
					per_page: -1,
					orderby: 'title',
					order: 'asc',
					status: 'publish',
				} ),
				isLoadingCemeteries: isResolving( 'getEntityRecords', [
					'postType',
					'parish_cemetery',
					{ per_page: -1, orderby: 'title', order: 'asc', status: 'publish' },
				] ),
			};
		}, [] );

		// Build sacrament options.
		const sacramentOptions = [
			{ value: '', label: __( 'All Sacraments', 'parish-core' ) },
		];
		if ( sacraments ) {
			sacraments.forEach( ( term ) => {
				sacramentOptions.push( {
					value: term.slug,
					label: term.name,
				} );
			} );
		}

		// Build church options.
		const churchOptions = [
			{ value: 0, label: autoDetect ? __( 'Auto-detect / All', 'parish-core' ) : __( 'All Churches', 'parish-core' ) },
		];
		if ( churches ) {
			churches.forEach( ( church ) => {
				churchOptions.push( {
					value: church.id,
					label: church.title.rendered,
				} );
			} );
		}

		// Build cemetery options.
		const cemeteryOptions = [
			{ value: 0, label: autoDetect ? __( 'Auto-detect / All', 'parish-core' ) : __( 'All Cemeteries', 'parish-core' ) },
		];
		if ( cemeteries ) {
			cemeteries.forEach( ( cemetery ) => {
				cemeteryOptions.push( {
					value: cemetery.id,
					label: cemetery.title.rendered,
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
						<ToggleControl
							label={ __( 'Show Subscribe Buttons', 'parish-core' ) }
							help={ __(
								'Show iCal and Google Calendar subscribe buttons.',
								'parish-core'
							) }
							checked={ showSubscribe !== false }
							onChange={ ( value ) =>
								setAttributes( { showSubscribe: value } )
							}
							__nextHasNoMarginBottom
						/>

						<ToggleControl
							label={ __( 'Show Download Button', 'parish-core' ) }
							help={ __(
								'Show button to download .ics file.',
								'parish-core'
							) }
							checked={ showDownload !== false }
							onChange={ ( value ) =>
								setAttributes( { showDownload: value } )
							}
							__nextHasNoMarginBottom
						/>
					</PanelBody>

					<PanelBody
						title={ __( 'Filters', 'parish-core' ) }
						initialOpen={ true }
					>
						<ToggleControl
							label={ __( 'Auto-detect Context', 'parish-core' ) }
							help={ __(
								'Automatically filter by church/cemetery when on those pages.',
								'parish-core'
							) }
							checked={ autoDetect !== false }
							onChange={ ( value ) =>
								setAttributes( { autoDetect: value } )
							}
							__nextHasNoMarginBottom
						/>

						{ isLoadingSacraments ? (
							<div style={ { display: 'flex', alignItems: 'center', gap: '8px', padding: '8px 0' } }>
								<Spinner />
								<span>{ __( 'Loading sacraments...', 'parish-core' ) }</span>
							</div>
						) : (
							<SelectControl
								label={ __( 'Sacrament', 'parish-core' ) }
								value={ sacrament }
								options={ sacramentOptions }
								onChange={ ( value ) =>
									setAttributes( { sacrament: value } )
								}
								__nextHasNoMarginBottom
								__next40pxDefaultSize
							/>
						) }

						{ isLoadingChurches ? (
							<div style={ { display: 'flex', alignItems: 'center', gap: '8px', padding: '8px 0' } }>
								<Spinner />
								<span>{ __( 'Loading churches...', 'parish-core' ) }</span>
							</div>
						) : (
							<SelectControl
								label={ __( 'Church', 'parish-core' ) }
								value={ churchId }
								options={ churchOptions }
								onChange={ ( value ) =>
									setAttributes( { churchId: parseInt( value, 10 ) } )
								}
								help={ autoDetect && churchId === 0 ? __( 'Will auto-detect on church pages.', 'parish-core' ) : '' }
								__nextHasNoMarginBottom
								__next40pxDefaultSize
							/>
						) }

						{ isLoadingCemeteries ? (
							<div style={ { display: 'flex', alignItems: 'center', gap: '8px', padding: '8px 0' } }>
								<Spinner />
								<span>{ __( 'Loading cemeteries...', 'parish-core' ) }</span>
							</div>
						) : (
							<SelectControl
								label={ __( 'Cemetery', 'parish-core' ) }
								value={ cemeteryId }
								options={ cemeteryOptions }
								onChange={ ( value ) =>
									setAttributes( { cemeteryId: parseInt( value, 10 ) } )
								}
								help={ autoDetect && cemeteryId === 0 ? __( 'Will auto-detect on cemetery pages.', 'parish-core' ) : '' }
								__nextHasNoMarginBottom
								__next40pxDefaultSize
							/>
						) }
					</PanelBody>

					<PanelBody
						title={ __( 'Colors', 'parish-core' ) }
						initialOpen={ false }
					>
						<BaseControl
							label={ __( 'Accent Color', 'parish-core' ) }
							help={ __( 'Color for today highlight and event indicators.', 'parish-core' ) }
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
							help={ __( 'Color for event times.', 'parish-core' ) }
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
					block="parish/events-calendar"
					attributes={ attributes }
					EmptyResponsePlaceholder={ () => (
						<div className="parish-events-calendar-placeholder">
							<p>
								{ __(
									'Events Calendar — Displays a full month calendar with subscription options.',
									'parish-core'
								) }
							</p>
						</div>
					) }
					ErrorResponsePlaceholder={ () => (
						<div className="parish-events-calendar-placeholder">
							<p>
								{ __(
									'Error loading calendar.',
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
