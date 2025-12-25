/**
 * Parish Core - Editor Blocks
 *
 * Custom blocks for the block editor.
 *
 * @package ParishCore
 */

import { registerBlockType } from '@wordpress/blocks';
import { useSelect } from '@wordpress/data';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, Spinner } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

// Import additional blocks.
import './church-selector';

/**
 * Related Church Selector Block
 *
 * Allows selecting a church post and stores the relationship in post meta.
 * Used in wedding, baptism, death notice CPTs to link to a church.
 */
registerBlockType( 'parish/related-church', {
	title: __( 'Related Church', 'parish-core' ),
	description: __( 'Select the church this notice relates to.', 'parish-core' ),
	icon: 'building',
	category: 'widgets',
	supports: {
		html: false,
	},
	attributes: {
		relatedChurch: {
			type: 'string',
			source: 'meta',
			meta: 'parish_related_church',
		},
	},

	edit: function RelatedChurchEdit( { attributes, setAttributes } ) {
		const { relatedChurch } = attributes;

		// Fetch all parish_church posts from REST API.
		const churches = useSelect( ( select ) => {
			return select( 'core' ).getEntityRecords(
				'postType',
				'parish_church',
				{
					per_page: -1,
					_fields: [ 'id', 'title', 'link' ],
				}
			);
		}, [] );

		// Loading state.
		if ( churches === undefined ) {
			return (
				<div className="parish-related-church-block">
					<Spinner />
					{ ' ' }
					{ __( 'Loading churches...', 'parish-core' ) }
				</div>
			);
		}

		// Build select options.
		const options = [
			{
				label: __( 'Select a church', 'parish-core' ),
				value: '',
			},
			...churches.map( ( post ) => ( {
				label: post.title.rendered || __( '(no title)', 'parish-core' ),
				value: String( post.id ),
			} ) ),
		];

		// Find currently selected church.
		const selected = churches.find(
			( post ) => String( post.id ) === String( relatedChurch )
		);

		return (
			<div className="parish-related-church-block">
				<InspectorControls>
					<PanelBody title={ __( 'Related Church', 'parish-core' ) }>
						<SelectControl
							label={ __( 'Church', 'parish-core' ) }
							value={ relatedChurch || '' }
							options={ options }
							onChange={ ( value ) =>
								setAttributes( { relatedChurch: value } )
							}
						/>
					</PanelBody>
				</InspectorControls>

				<p>
					<strong>{ __( 'Related Church:', 'parish-core' ) }</strong>
					{ ' ' }
					{ selected
						? selected.title.rendered
						: __( 'None selected', 'parish-core' ) }
				</p>
			</div>
		);
	},

	// No save function - meta is saved automatically by WordPress.
	save: () => null,
} );
