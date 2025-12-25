/**
 * Parish Core - Block Bindings Editor Integration
 *
 * Implements the WordPress 6.7+ Block Bindings API for custom post meta.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-bindings/
 * @see https://make.wordpress.org/core/2024/10/21/block-bindings-improvements-to-the-editor-experience-in-6-7/
 *
 * @package ParishCore
 */

import { registerBlockBindingsSource } from '@wordpress/blocks';
import { store as coreDataStore } from '@wordpress/core-data';
import { store as editorStore } from '@wordpress/editor';
import { toHTMLString, isRichTextValue } from '@wordpress/rich-text';

/**
 * Debounce delay in milliseconds.
 * Waits this long after the last keystroke before updating meta.
 */
const DEBOUNCE_DELAY = 150;

/**
 * Pending meta updates, keyed by postType:postId.
 */
const pendingUpdates = new Map();

/**
 * Debounce timers, keyed by postType:postId.
 */
const debounceTimers = new Map();

/**
 * Meta cache for getValues, keyed by postType:postId.
 * Uses a simple object cache that's invalidated on editEntityRecord.
 */
let metaCache = new Map();
let lastCacheTime = 0;
const CACHE_TTL = 50; // Cache valid for 50ms

/**
 * Validate that a meta key belongs to Parish Core.
 *
 * All parish post meta keys are prefixed with 'parish_' for namespacing.
 *
 * @param {string} key The meta key to validate.
 * @return {boolean} True if key is valid.
 */
const isValidParishMetaKey = ( key ) => {
	return typeof key === 'string' && key.startsWith( 'parish_' );
};

/**
 * Extract meta key from binding configuration.
 *
 * The binding can be a simple string or an object with args.key.
 *
 * @param {Object|string} binding The binding configuration.
 * @return {string|null} The meta key or null if invalid.
 */
const getMetaKeyFromBinding = ( binding ) => {
	if ( typeof binding === 'string' ) {
		return binding;
	}
	if ( binding && typeof binding === 'object' ) {
		return binding?.args?.key ?? null;
	}
	return null;
};

/**
 * Get editor context with fallbacks.
 *
 * Retrieves postType and postId from context or falls back to editor store.
 *
 * @param {Object} context   Block context object.
 * @param {Function} select  Data store selector.
 * @return {Object} Object with postType and postId.
 */
const getEditorContext = ( context, select ) => {
	const editor = select( editorStore );
	return {
		postType: context?.postType ?? editor?.getCurrentPostType?.(),
		postId: context?.postId ?? editor?.getCurrentPostId?.(),
	};
};

/**
 * Normalize a value for meta storage.
 *
 * Block attributes can be RichText objects, strings, numbers, or other types.
 * Meta fields expect strings, so we convert appropriately.
 *
 * @param {*} value The value to normalize.
 * @return {string|undefined} Normalized string value, or undefined for no change.
 */
const normalizeValueForMeta = ( value ) => {
	// Undefined means no change - don't update meta.
	if ( value === undefined ) {
		return undefined;
	}

	// Null or empty becomes empty string.
	if ( value === null || value === '' ) {
		return '';
	}

	// Already a string - return as-is.
	if ( typeof value === 'string' ) {
		return value;
	}

	// Numbers and booleans - convert to string.
	if ( typeof value === 'number' || typeof value === 'boolean' ) {
		return String( value );
	}

	// RichText value object - convert to HTML string.
	// This preserves formatting like bold, italic, links.
	if ( isRichTextValue( value ) ) {
		return toHTMLString( { value } );
	}

	// Some RichText implementations expose text or value properties.
	if ( value && typeof value.text === 'string' ) {
		return value.text;
	}
	if ( value && typeof value.value === 'string' ) {
		return value.value;
	}

	// Unknown type - return empty to avoid storing garbage.
	return '';
};

/**
 * Register the Parish Post Meta block bindings source.
 *
 * This allows core blocks (Heading, Paragraph, Button, Image) to bind
 * their attributes to custom post meta fields.
 *
 * The source is registered on both server (PHP) and client (JS):
 * - Server handles front-end rendering
 * - Client handles editor experience (reading/writing values)
 */
registerBlockBindingsSource( {
	/**
	 * Unique identifier for the binding source.
	 * Must match the name registered in PHP.
	 */
	name: 'parish/post-meta',

	/**
	 * Block context values this source needs.
	 * These are merged with server-side usesContext.
	 */
	usesContext: [ 'postType', 'postId' ],

	/**
	 * Get values for bound attributes.
	 *
	 * Called by the editor when rendering blocks with bindings.
	 * Returns current meta values for each bound attribute.
	 *
	 * Uses short-lived caching to avoid repeated entity lookups
	 * during rapid re-renders.
	 *
	 * @param {Object} params             Parameters object.
	 * @param {Function} params.select    Data store selector function.
	 * @param {Object} params.context     Block context (postType, postId).
	 * @param {Object} params.bindings    Map of attribute names to binding configs.
	 * @return {Object} Map of attribute names to their current values.
	 */
	getValues( { select, context, bindings } ) {
		const values = {};

		if ( ! bindings || typeof bindings !== 'object' ) {
			return values;
		}

		const { postType, postId } = getEditorContext( context, select );

		if ( ! postType || ! postId ) {
			return values;
		}

		const cacheKey = `${ postType }:${ postId }`;
		const now = Date.now();

		// Invalidate cache if TTL expired.
		if ( now - lastCacheTime > CACHE_TTL ) {
			metaCache.clear();
			lastCacheTime = now;
		}

		// Check cache for meta object.
		let meta = metaCache.get( cacheKey );

		if ( ! meta ) {
			// Get the edited entity record which includes meta.
			const record = select( coreDataStore ).getEditedEntityRecord(
				'postType',
				postType,
				postId
			);

			meta = record?.meta || {};
			metaCache.set( cacheKey, meta );
		}

		// Map each binding to its current meta value.
		for ( const [ attributeName, binding ] of Object.entries( bindings ) ) {
			const metaKey = getMetaKeyFromBinding( binding );

			if ( ! isValidParishMetaKey( metaKey ) ) {
				continue;
			}

			const value = meta[ metaKey ];
			// Return empty string for undefined/null to avoid placeholders.
			values[ attributeName ] = value ?? '';
		}

		return values;
	},

	/**
	 * Set values for bound attributes.
	 *
	 * Called by the editor when user edits a bound block.
	 * Updates the post meta with new values.
	 *
	 * Uses debouncing to batch rapid updates (e.g., during typing)
	 * which significantly improves editor performance.
	 *
	 * @param {Object} params              Parameters object.
	 * @param {Function} params.select     Data store selector function.
	 * @param {Function} params.dispatch   Data store dispatch function.
	 * @param {Object} params.context      Block context (postType, postId).
	 * @param {Object} params.bindings     Map of attribute names to binding configs with newValue.
	 */
	setValues( { select, dispatch, context, bindings } ) {
		if ( ! bindings || typeof bindings !== 'object' ) {
			return;
		}

		const { postType, postId } = getEditorContext( context, select );

		if ( ! postType || ! postId ) {
			return;
		}

		const cacheKey = `${ postType }:${ postId }`;

		// Collect all meta updates from bindings.
		for ( const [ , binding ] of Object.entries( bindings ) ) {
			const metaKey = getMetaKeyFromBinding( binding );

			if ( ! isValidParishMetaKey( metaKey ) ) {
				continue;
			}

			// newValue is provided by the Block Bindings API.
			const rawValue = binding?.newValue;
			const normalizedValue = normalizeValueForMeta( rawValue );

			// undefined means no change - skip this key.
			if ( normalizedValue === undefined ) {
				continue;
			}

			// Merge into pending updates for this post.
			const existing = pendingUpdates.get( cacheKey ) || {};
			existing[ metaKey ] = normalizedValue;
			pendingUpdates.set( cacheKey, existing );
		}

		// Clear any existing debounce timer.
		const existingTimer = debounceTimers.get( cacheKey );
		if ( existingTimer ) {
			clearTimeout( existingTimer );
		}

		// Set new debounced dispatch.
		const timer = setTimeout( () => {
			const metaUpdates = pendingUpdates.get( cacheKey );
			pendingUpdates.delete( cacheKey );
			debounceTimers.delete( cacheKey );

			// Only dispatch if we have updates.
			if ( ! metaUpdates || Object.keys( metaUpdates ).length === 0 ) {
				return;
			}

			// Update the entity record meta - this triggers autosave.
			dispatch( coreDataStore ).editEntityRecord(
				'postType',
				postType,
				postId,
				{ meta: metaUpdates }
			);
		}, DEBOUNCE_DELAY );

		debounceTimers.set( cacheKey, timer );
	},

	/**
	 * Determine if user can edit a bound attribute.
	 *
	 * Controls whether the block is editable in the editor.
	 * Returns true for parish_ prefixed post types with valid meta keys.
	 *
	 * @param {Object} params          Parameters object.
	 * @param {Function} params.select Data store selector function.
	 * @param {Object} params.context  Block context (postType, postId).
	 * @param {Object} params.args     Binding arguments (key, etc.).
	 * @return {boolean} True if user can edit this binding.
	 */
	canUserEditValue( { select, context, args } ) {
		const { postType, postId } = getEditorContext( context, select );

		// Must have valid context.
		if ( ! postType || ! postId ) {
			return false;
		}

		// Only allow editing for parish_ prefixed post types.
		if ( ! String( postType ).startsWith( 'parish_' ) ) {
			return false;
		}

		// Must be a valid parish meta key.
		const metaKey = args?.key;
		return isValidParishMetaKey( metaKey );
	},
} );
