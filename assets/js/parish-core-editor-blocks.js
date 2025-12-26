/**
 * Parish Core - Editor Blocks
 *
 * Custom blocks for the block editor.
 * Uses wp.element.createElement (no JSX) for WordPress compatibility.
 */

(function (wp) {
	if (!wp || !wp.blocks) return;

	const { createElement: el } = wp.element;
	const { registerBlockType } = wp.blocks;
	const { __ } = wp.i18n;
	const { SelectControl, Spinner, PanelBody, ToggleControl, RangeControl, CheckboxControl } = wp.components;
	const { useSelect } = wp.data;
	const { InspectorControls, useBlockProps } = wp.blockEditor || wp.editor;

	/**
	 * Related Church Selector Block
	 *
	 * Allows selecting a church post and stores the relationship in post meta.
	 */
	registerBlockType('parish/related-church', {
		title: __('Related Church', 'parish-core'),
		description: __('Select the church this notice relates to.', 'parish-core'),
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

		edit: function (props) {
			const { attributes, setAttributes } = props;
			const { relatedChurch } = attributes;

			// Fetch all parish_church posts from REST.
			const churches = useSelect(
				function (select) {
					return select('core').getEntityRecords(
						'postType',
						'parish_church',
						{ per_page: -1, _fields: ['id', 'title', 'link'] }
					);
				},
				[]
			);

			if (churches === undefined) {
				return el('div', { className: 'parish-related-church-block' },
					el(Spinner),
					' ',
					__('Loading churches…', 'parish-core')
				);
			}

			const options = [
				{
					label: __('Select a church', 'parish-core'),
					value: '',
				}
			].concat(
				churches.map(function (post) {
					return {
						label: post.title.rendered || __('(no title)', 'parish-core'),
						value: String(post.id),
					};
				})
			);

			const selected = churches.find(function (post) {
				return String(post.id) === String(relatedChurch);
			});

			return el('div', { className: 'parish-related-church-block' },
				el(InspectorControls, {},
					el(PanelBody, { title: __('Related Church', 'parish-core') },
						el(SelectControl, {
							label: __('Church', 'parish-core'),
							value: relatedChurch || '',
							options: options,
							onChange: function (value) {
								setAttributes({ relatedChurch: value });
							},
							__nextHasNoMarginBottom: true,
							__next40pxDefaultSize: true
						})
					)
				),

				el('p', {},
					el('strong', {}, __('Related Church:', 'parish-core')),
					' ',
					selected
						? selected.title.rendered
						: __('None selected', 'parish-core')
				)
			);
		},

		// No save function - meta is saved automatically by WordPress
		save: function () {
			return null;
		},
	});

	/**
	 * Church Schedule Block
	 *
	 * Dynamic block that renders the liturgical schedule for a church.
	 * Server-rendered using the parish_church_schedule shortcode.
	 */
	const EVENT_TYPE_OPTIONS = [
		{ label: __('Mass', 'parish-core'), value: 'mass' },
		{ label: __('Confession', 'parish-core'), value: 'confession' },
		{ label: __('Adoration', 'parish-core'), value: 'adoration' },
		{ label: __('Baptism', 'parish-core'), value: 'baptism' },
		{ label: __('Wedding', 'parish-core'), value: 'wedding' },
		{ label: __('Funeral', 'parish-core'), value: 'funeral' },
		{ label: __('Stations of the Cross', 'parish-core'), value: 'stations' },
		{ label: __('Rosary', 'parish-core'), value: 'rosary' },
		{ label: __('Novena', 'parish-core'), value: 'novena' },
		{ label: __('Benediction', 'parish-core'), value: 'benediction' },
		{ label: __('Vespers', 'parish-core'), value: 'vespers' },
	];

	const FORMAT_OPTIONS = [
		{ label: __('List', 'parish-core'), value: 'list' },
		{ label: __('Table', 'parish-core'), value: 'table' },
		{ label: __('Cards', 'parish-core'), value: 'cards' },
		{ label: __('Simple', 'parish-core'), value: 'simple' },
	];

	registerBlockType('parish/church-schedule', {
		title: __('Church Schedule', 'parish-core'),
		description: __('Display the liturgical schedule for this church.', 'parish-core'),
		icon: 'clock',
		category: 'widgets',
		supports: {
			html: false,
			align: ['wide', 'full'],
		},
		attributes: {
			format: {
				type: 'string',
				default: 'list',
			},
			eventTypes: {
				type: 'array',
				default: ['mass', 'confession'],
			},
			showFeastDay: {
				type: 'boolean',
				default: true,
			},
			days: {
				type: 'number',
				default: 7,
			},
			showLivestream: {
				type: 'boolean',
				default: true,
			},
			groupByDay: {
				type: 'boolean',
				default: true,
			},
		},

		edit: function (props) {
			const { attributes, setAttributes } = props;
			const { format, eventTypes, showFeastDay, days, showLivestream } = attributes;
			const blockProps = useBlockProps ? useBlockProps({ className: 'parish-church-schedule-editor' }) : { className: 'parish-church-schedule-editor' };

			// Toggle event type selection
			const toggleEventType = function (type) {
				const current = eventTypes || [];
				if (current.includes(type)) {
					setAttributes({ eventTypes: current.filter(function (t) { return t !== type; }) });
				} else {
					setAttributes({ eventTypes: [...current, type] });
				}
			};

			// Get selected event type labels
			const selectedLabels = (eventTypes || []).map(function (type) {
				const opt = EVENT_TYPE_OPTIONS.find(function (o) { return o.value === type; });
				return opt ? opt.label : type;
			}).join(', ');

			return el('div', blockProps,
				el(InspectorControls, {},
					el(PanelBody, { title: __('Schedule Settings', 'parish-core'), initialOpen: true },
						el(SelectControl, {
							label: __('Display Format', 'parish-core'),
							value: format,
							options: FORMAT_OPTIONS,
							onChange: function (value) {
								setAttributes({ format: value });
							},
							__nextHasNoMarginBottom: true,
							__next40pxDefaultSize: true
						}),

						el(RangeControl, {
							label: __('Days to Show', 'parish-core'),
							value: days,
							onChange: function (value) {
								setAttributes({ days: value });
							},
							min: 1,
							max: 14,
						}),

						el(ToggleControl, {
							label: __('Show Feast Days', 'parish-core'),
							checked: showFeastDay,
							onChange: function (value) {
								setAttributes({ showFeastDay: value });
							}
						}),

						el(ToggleControl, {
							label: __('Show Livestream Links', 'parish-core'),
							checked: showLivestream,
							onChange: function (value) {
								setAttributes({ showLivestream: value });
							}
						})
					),

					el(PanelBody, { title: __('Event Types', 'parish-core'), initialOpen: false },
						EVENT_TYPE_OPTIONS.map(function (option) {
							return el(CheckboxControl, {
								key: option.value,
								label: option.label,
								checked: (eventTypes || []).includes(option.value),
								onChange: function () {
									toggleEventType(option.value);
								}
							});
						})
					)
				),

				// Editor preview
				el('div', { className: 'parish-church-schedule-preview' },
					el('div', { className: 'parish-schedule-block-header' },
						el('span', { className: 'dashicons dashicons-clock' }),
						el('strong', {}, __('Church Schedule', 'parish-core'))
					),
					el('p', { className: 'parish-schedule-block-info' },
						__('Displaying:', 'parish-core'),
						' ',
						selectedLabels || __('All events', 'parish-core')
					),
					el('p', { className: 'parish-schedule-block-meta' },
						__('Format:', 'parish-core'), ' ', format,
						' | ',
						__('Days:', 'parish-core'), ' ', days
					),
					el('p', { className: 'parish-schedule-block-note' },
						el('em', {},
							__('Schedule will be rendered dynamically on the frontend from schedule templates.', 'parish-core')
						)
					)
				)
			);
		},

		// Server-side rendering - no save needed
		save: function () {
			return null;
		},
	});
})(window.wp);
