( function( wp ) {
	const { registerBlockType } = wp.blocks;
	const { __ } = wp.i18n;
	const { SelectControl, Spinner, PanelBody } = wp.components;
	const { useSelect } = wp.data;
	const { InspectorControls } = wp.blockEditor || wp.editor;

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
				meta: 'parish_related_church', // 👈 matches your register_post_meta key
			},
		},

		edit: ( { attributes, setAttributes } ) => {
			const { relatedChurch } = attributes;

			// Fetch all parish_church posts from REST.
			const churches = useSelect(
				( select ) => {
					return select( 'core' ).getEntityRecords(
						'postType',
						'parish_church',
						{ per_page: -1, _fields: [ 'id', 'title', 'link' ] }
					);
				},
				[]
			);

			if ( churches === undefined ) {
				return (
					<div className="parish-related-church-block">
						<Spinner />
						{ __( 'Loading churches…', 'parish-core' ) }
					</div>
				);
			}

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
						<strong>{ __( 'Related Church:', 'parish-core' ) }</strong>{ ' ' }
						{ selected
							? selected.title.rendered
							: __( 'None selected', 'parish-core' ) }
					</p>
				</div>
			);
		},

		// We don’t save any HTML. Meta is saved automatically by WP.
		save: () => null,
	} );
} )( window.wp );
