/**
 * Parish Core — Block Bindings (Editor)
 * Reads/writes post meta for bound block attributes.
 */
(function (wp) {
	if (!wp?.blocks?.registerBlockBindingsSource) return;
	if (!wp?.domReady || !wp?.data?.select || !wp?.data?.dispatch) return;

	const { registerBlockBindingsSource } = wp.blocks;
	const { select, dispatch } = wp.data;

	const CORE_STORE = "core";
	const EDITOR_STORE = "core/editor";

	const isAllowedKey = (key) => typeof key === "string" && key.startsWith("parish_");

	const getEditorContext = (context) => {
		const editor = select(EDITOR_STORE);
		return {
			postType: context?.postType ?? editor?.getCurrentPostType?.(),
			postId: context?.postId ?? editor?.getCurrentPostId?.(),
		};
	};

	const getMetaKeyFromBinding = (binding) => {
		if (typeof binding === "string") return binding;
		if (!binding || typeof binding !== "object") return null;
		return binding?.args?.key ?? binding?.key ?? null;
	};

	const getEditedRecord = (postType, postId) => {
		return (
			select(CORE_STORE).getEditedEntityRecord("postType", postType, postId) ||
			select(CORE_STORE).getEntityRecord("postType", postType, postId) ||
			null
		);
	};

	/**
	 * IMPORTANT:
	 * Block bindings can pass RichText values as objects.
	 * Meta sanitizers (sanitize_text_field/textarea) will wipe arrays/objects to ''.
	 * Convert to a string before writing meta.
	 */
	const normalizeToMetaString = (value) => {
		if (value === undefined) return undefined; // means "no change"
		if (value === null) return "";

		// Most cases: already a string
		if (typeof value === "string") return value;

		// Numbers/bools: store as string
		if (typeof value === "number" || typeof value === "boolean") return String(value);

		// RichTextValue -> HTML string (keeps bold/italic, etc.)
		// wp.richText exists if you add dependency wp-rich-text
		if (wp?.richText?.isRichTextValue && wp?.richText?.toHTMLString) {
			try {
				if (wp.richText.isRichTextValue(value)) {
					return wp.richText.toHTMLString({ value });
				}
			} catch (e) {
				// fall through
			}
		}

		// Some shapes expose text directly
		if (value && typeof value.text === "string") return value.text;
		if (value && typeof value.value === "string") return value.value;

		// Last resort: don't save junk
		return "";
	};

	wp.domReady(function () {
		registerBlockBindingsSource({
			name: "parish/post-meta",
			label: "Parish Post Meta",
			usesContext: ["postType", "postId"],

			getValues({ context, bindings }) {
				const values = {};
				if (!bindings) return values;

				const { postType, postId } = getEditorContext(context);
				if (!postType || !postId) return values;

				const record = getEditedRecord(postType, postId);
				const meta = record?.meta || {};

				for (const attrName of Object.keys(bindings)) {
					const metaKey = getMetaKeyFromBinding(bindings[attrName]);
					if (!isAllowedKey(metaKey)) continue;

					const v = meta[metaKey];
					values[attrName] = v === undefined || v === null ? "" : v;
				}

				return values;
			},

			setValues({ context, bindings }) {
				if (!bindings) return;

				const { postType, postId } = getEditorContext(context);
				if (!postType || !postId) return;

				const metaUpdates = {};

				for (const attrName of Object.keys(bindings)) {
					const binding = bindings[attrName];
					const metaKey = getMetaKeyFromBinding(binding);
					if (!isAllowedKey(metaKey)) continue;

					const raw = binding && typeof binding === "object" ? binding.newValue : undefined;
					const newValue = normalizeToMetaString(raw);

					// undefined means "no change" — skip
					if (newValue === undefined) continue;

					metaUpdates[metaKey] = newValue;
				}

				if (!Object.keys(metaUpdates).length) return;

				dispatch(CORE_STORE).editEntityRecord("postType", postType, postId, {
					meta: metaUpdates,
				});
			},

			// Correct signature: args is the per-binding args (your { key: 'parish_...' }).
			canUserEditValue({ context, args }) {
				const { postType, postId } = getEditorContext(context);
				if (!postType || !postId) return false;
				if (!String(postType).startsWith("parish_")) return false;

				const metaKey = args?.key;
				return isAllowedKey(metaKey);
			},
		});
	});
})(window.wp);
