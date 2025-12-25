/**
 * Church Selector Block
 *
 * Allows users to either:
 * 1. Select a parish church from the parish_church CPT
 * 2. Enter a custom church name for non-parish churches
 *
 * @package ParishCore
 */

import { registerBlockType } from '@wordpress/blocks';
import { useSelect } from '@wordpress/data';
import { useEntityProp } from '@wordpress/core-data';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	TextControl,
	Spinner,
	ToggleControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useEffect, useState } from '@wordpress/element';

/**
 * Church Selector Block
 *
 * Displays a church selector in the editor with option for custom entry.
 */
registerBlockType( 'parish/church-selector', {
	title: __( 'Church Selector', 'parish-core' ),
	description: __(
		'Select a parish church or enter a custom church name.',
		'parish-core'
	),
	icon: 'building',
	category: 'widgets',
	supports: {
		html: false,
		multiple: false, // Only one per post.
	},
	usesContext: [ 'postType', 'postId' ],

	edit: function ChurchSelectorEdit( { context } ) {
		const blockProps = useBlockProps();
		const { postType, postId } = context;

		// State for whether to use custom church name.
		const [ useCustom, setUseCustom ] = useState( false );

		// Get/set meta using useEntityProp.
		const [ meta, setMeta ] = useEntityProp(
			'postType',
			postType,
			'meta',
			postId
		);

		const relatedChurch = meta?.parish_related_church || 0;
		const churchName = meta?.parish_church_name || '';

		// Fetch all parish_church posts.
		const churches = useSelect( ( select ) => {
			return select( 'core' ).getEntityRecords( 'postType', 'parish_church', {
				per_page: -1,
				_fields: [ 'id', 'title' ],
				orderby: 'title',
				order: 'asc',
			} );
		}, [] );

		// Determine if custom is being used based on existing data.
		useEffect( () => {
			if ( churchName && ! relatedChurch ) {
				setUseCustom( true );
			}
		}, [ churchName, relatedChurch ] );

		// Loading state.
		if ( churches === undefined ) {
			return (
				<div { ...blockProps }>
					<Spinner />
					{ ' ' }
					{ __( 'Loading churches...', 'parish-core' ) }
				</div>
			);
		}

		// Build select options.
		const options = [
			{
				label: __( '-- Select a parish church --', 'parish-core' ),
				value: '0',
			},
			...( churches || [] ).map( ( post ) => ( {
				label: post.title.rendered || __( '(no title)', 'parish-core' ),
				value: String( post.id ),
			} ) ),
		];

		// Find currently selected church name.
		const selectedChurch = churches?.find(
			( post ) => post.id === relatedChurch
		);
		const displayName = useCustom
			? churchName
			: selectedChurch?.title?.rendered || '';

		// Handle church selection change.
		const handleChurchChange = ( value ) => {
			const churchId = parseInt( value, 10 ) || 0;
			setMeta( {
				...meta,
				parish_related_church: churchId,
				// Clear custom name when selecting a parish church.
				parish_church_name: churchId > 0 ? '' : churchName,
			} );
		};

		// Handle custom name change.
		const handleCustomNameChange = ( value ) => {
			setMeta( {
				...meta,
				parish_church_name: value,
				// Clear related church when using custom.
				parish_related_church: 0,
			} );
		};

		// Handle toggle change.
		const handleToggle = ( checked ) => {
			setUseCustom( checked );
			if ( checked ) {
				// Switching to custom - clear related church.
				setMeta( {
					...meta,
					parish_related_church: 0,
				} );
			} else {
				// Switching to select - clear custom name.
				setMeta( {
					...meta,
					parish_church_name: '',
				} );
			}
		};

		return (
			<div { ...blockProps }>
				<InspectorControls>
					<PanelBody
						title={ __( 'Church Selection', 'parish-core' ) }
						initialOpen={ true }
					>
						<ToggleControl
							label={ __( 'Use custom church name', 'parish-core' ) }
							help={
								useCustom
									? __(
											'Enter a church name that is not in the parish.',
											'parish-core'
									  )
									: __(
											'Select from parish churches.',
											'parish-core'
									  )
							}
							checked={ useCustom }
							onChange={ handleToggle }
						/>

						{ ! useCustom && (
							<SelectControl
								label={ __( 'Parish Church', 'parish-core' ) }
								value={ String( relatedChurch ) }
								options={ options }
								onChange={ handleChurchChange }
							/>
						) }

						{ useCustom && (
							<TextControl
								label={ __( 'Church Name', 'parish-core' ) }
								value={ churchName }
								onChange={ handleCustomNameChange }
								placeholder={ __(
									'Enter church name...',
									'parish-core'
								) }
							/>
						) }
					</PanelBody>
				</InspectorControls>

				<div className="parish-church-selector">
					<p>
						<strong>{ __( 'Church:', 'parish-core' ) }</strong>{ ' ' }
						{ displayName || (
							<em>{ __( 'Not selected', 'parish-core' ) }</em>
						) }
					</p>
					{ useCustom && (
						<small>
							{ __( '(Custom entry)', 'parish-core' ) }
						</small>
					) }
				</div>
			</div>
		);
	},

	// No save - meta is handled automatically.
	save: () => null,
} );
