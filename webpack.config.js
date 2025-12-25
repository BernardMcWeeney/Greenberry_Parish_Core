/**
 * WordPress webpack configuration.
 *
 * This config extends the default @wordpress/scripts config to add custom
 * entry points for our block bindings and editor scripts.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-scripts/
 */

const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );

module.exports = {
	...defaultConfig,
	entry: {
		// Block bindings editor integration.
		'block-bindings': path.resolve( process.cwd(), 'src', 'block-bindings', 'index.js' ),

		// Editor blocks (related-church, etc.).
		'editor-blocks': path.resolve( process.cwd(), 'src', 'editor-blocks', 'index.js' ),
	},
	output: {
		...defaultConfig.output,
		path: path.resolve( process.cwd(), 'build' ),
	},
};
