/**
 * Church Sidebar Panel
 *
 * Adds a color picker to the church post type editor sidebar
 * for setting the church's display color.
 *
 * @package ParishCore
 */

import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { ColorPicker, BaseControl } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { useEntityProp } from '@wordpress/core-data';

/**
 * Church Color Panel Component
 */
function ChurchColorPanel() {
	const postType = useSelect( ( select ) => {
		return select( 'core/editor' ).getCurrentPostType();
	}, [] );

	// Only show for parish_church post type.
	if ( postType !== 'parish_church' ) {
		return null;
	}

	const postId = useSelect( ( select ) => {
		return select( 'core/editor' ).getCurrentPostId();
	}, [] );

	const [ meta, setMeta ] = useEntityProp( 'postType', 'parish_church', 'meta', postId );

	const churchColor = meta?.parish_color || '#609fae';

	const handleColorChange = ( color ) => {
		setMeta( { ...meta, parish_color: color } );
	};

	return (
		<PluginDocumentSettingPanel
			name="parish-church-color"
			title={ __( 'Church Display Color', 'parish-core' ) }
			className="parish-church-color-panel"
		>
			<BaseControl
				id="church-color-picker"
				label={ __( 'Select a color for this church. This color will be used in the Mass Times admin to identify this church.', 'parish-core' ) }
			>
				<div style={ { marginTop: '12px' } }>
					<ColorPicker
						color={ churchColor }
						onChange={ handleColorChange }
						enableAlpha={ false }
					/>
				</div>
				<div
					style={ {
						marginTop: '12px',
						padding: '12px',
						borderLeft: `4px solid ${ churchColor }`,
						backgroundColor: `${ churchColor }22`,
						borderRadius: '4px',
					} }
				>
					<strong>{ __( 'Preview', 'parish-core' ) }</strong>
					<p style={ { margin: '4px 0 0 0', fontSize: '12px' } }>
						{ __( 'This is how the church will appear in Mass Times.', 'parish-core' ) }
					</p>
				</div>
			</BaseControl>
		</PluginDocumentSettingPanel>
	);
}

// Register the plugin.
registerPlugin( 'parish-church-color-panel', {
	render: ChurchColorPanel,
	icon: 'admin-appearance',
} );
