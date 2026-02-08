/**
 * Parish Core Admin - Settings & Shortcodes
 */
(function (window) {
	'use strict';

	const {
		el,
		useState,
		useEffect,
		Fragment,
		Panel,
		PanelBody,
		ToggleControl,
		Button,
		Notice,
		TabPanel,
		apiFetch,
		LoadingSpinner,
	} = window.ParishCoreAdmin;

	function ShortcodeReference() {
		const [shortcodes, setShortcodes] = useState([]);
		const [loading, setLoading] = useState(true);

		useEffect(function () {
			apiFetch({ path: '/parish/v1/shortcodes' })
				.then(function (res) {
					setShortcodes(res || []);
					setLoading(false);
				})
				.catch(function () {
					setLoading(false);
				});
		}, []);

		if (loading) return el(LoadingSpinner, { text: 'Loading...' });

		// Separate shortcodes and blocks
		var traditionalShortcodes = shortcodes.filter(function (s) {
			return s.type !== 'block';
		});
		var blocks = shortcodes.filter(function (s) {
			return s.type === 'block';
		});

		return el(
			'div',
			{ className: 'shortcode-reference' },
			// Traditional Shortcodes Section
			el('h3', { style: { marginTop: 0 } }, 'Shortcodes'),
			el(
				'p',
				null,
				'Copy and paste these shortcodes into your pages, posts, or widgets:'
			),
			el(
				'table',
				{ className: 'shortcode-table widefat' },
				el(
					'thead',
					null,
					el(
						'tr',
						null,
						el('th', { style: { width: '200px' } }, 'Shortcode'),
						el('th', { style: { width: '180px' } }, 'Description'),
						el('th', null, 'Attributes')
					)
				),
				el(
					'tbody',
					null,
					traditionalShortcodes.map(function (s, i) {
						var hasAttrs = s.attributes && Object.keys(s.attributes).length > 0;
						var attrList = hasAttrs
							? Object.keys(s.attributes).map(function (k) {
									return el(
										'div',
										{ key: k, style: { marginBottom: '4px' } },
										el('code', { style: { background: '#e8e8e8', padding: '2px 4px', borderRadius: '3px', fontSize: '11px' } }, k),
										el('span', { style: { marginLeft: '6px', fontSize: '12px', color: '#555' } }, s.attributes[k])
									);
								})
							: el('span', { style: { color: '#999', fontStyle: 'italic' } }, 'No attributes');
						return el(
							'tr',
							{ key: 'sc-' + i },
							el(
								'td',
								null,
								el('code', { className: 'shortcode-code' }, s.shortcode),
								s.example && el(
									'div',
									{ style: { marginTop: '8px' } },
									el('span', { style: { fontSize: '11px', color: '#666', display: 'block', marginBottom: '2px' } }, 'Example:'),
									el('code', { style: { fontSize: '11px', background: '#f0f6fc', padding: '4px 6px', borderRadius: '3px', display: 'block', wordBreak: 'break-all' } }, s.example)
								)
							),
							el('td', null, el('strong', null, s.name), el('br'), s.description),
							el(
								'td',
								null,
								el('div', { className: 'attrs-list' }, attrList)
							)
						);
					})
				)
			),
			// Gutenberg Blocks Section
			blocks.length > 0 &&
				el(
					Fragment,
					null,
					el('h3', { style: { marginTop: '2em' } }, 'Gutenberg Blocks'),
					el(
						'p',
						null,
						'These blocks can be added via the Block Editor. Search for "Parish" in the block inserter:'
					),
					el(
						'table',
						{ className: 'shortcode-table widefat' },
						el(
							'thead',
							null,
							el(
								'tr',
								null,
								el('th', { style: { width: '200px' } }, 'Block Name'),
								el('th', { style: { width: '180px' } }, 'Description'),
								el('th', null, 'Settings')
							)
						),
						el(
							'tbody',
							null,
							blocks.map(function (s, i) {
								var hasAttrs = s.attributes && Object.keys(s.attributes).length > 0;
								var attrList = hasAttrs
									? Object.keys(s.attributes).map(function (k) {
											return el(
												'div',
												{ key: k, style: { marginBottom: '4px' } },
												el('code', { style: { background: '#e8e8e8', padding: '2px 4px', borderRadius: '3px', fontSize: '11px' } }, k),
												el('span', { style: { marginLeft: '6px', fontSize: '12px', color: '#555' } }, s.attributes[k])
											);
										})
									: el('span', { style: { color: '#999', fontStyle: 'italic' } }, 'No settings');
								return el(
									'tr',
									{ key: 'block-' + i },
									el(
										'td',
										null,
										el('code', { className: 'shortcode-code' }, s.shortcode),
										s.example && el(
											'div',
											{ style: { marginTop: '6px', fontSize: '11px', color: '#666' } },
											s.example
										)
									),
									el('td', null, el('strong', null, s.name), el('br'), s.description),
									el(
										'td',
										null,
										el('div', { className: 'attrs-list' }, attrList)
									)
								);
							})
						)
					)
				),
			// Tip Section
			el(
				'div',
				{ style: { marginTop: '2em', padding: '1em', background: '#f0f6fc', borderRadius: '6px', borderLeft: '3px solid #2271b1' } },
				el('strong', null, 'Tip: '),
				'Copy shortcodes directly from the table above. Each shortcode includes an example showing how to use it with attributes.'
			)
		);
	}

	function Settings() {
		const [settings, setSettings] = useState({});
		const [loading, setLoading] = useState(true);
		const [saving, setSaving] = useState(false);
		const [notice, setNotice] = useState(null);

		useEffect(function () {
			apiFetch({ path: '/parish/v1/settings' })
				.then(function (res) {
					setSettings(res);
					setLoading(false);
				})
				.catch(function () {
					setLoading(false);
				});
		}, []);

		const upd = function (k, v) {
			setSettings(function (p) {
				return Object.assign({}, p, { [k]: v });
			});
		};

		const save = function () {
			setSaving(true);
			apiFetch({
				path: '/parish/v1/settings',
				method: 'POST',
				data: settings,
			})
				.then(function () {
					setSaving(false);
					setNotice({
						type: 'success',
						message: 'Settings saved! Refresh to see changes.',
					});
				})
				.catch(function (err) {
					setSaving(false);
					setNotice({
						type: 'error',
						message: err.message,
					});
				});
		};

		if (loading) return el(LoadingSpinner, { text: 'Loading...' });

		const featureToggles = [
			{ key: 'enable_mass_times', label: 'Mass Times' },
			{ key: 'enable_rosary', label: 'Rosary' },
			{ key: 'enable_death_notices', label: 'Death Notices' },
			{ key: 'enable_baptism_notices', label: 'Baptism Notices' },
			{ key: 'enable_wedding_notices', label: 'Wedding Notices' },
			{ key: 'enable_churches', label: 'Churches' },
			{ key: 'enable_schools', label: 'Schools' },
			{ key: 'enable_cemeteries', label: 'Cemeteries' },
			{ key: 'enable_groups', label: 'Parish Groups' },
			{ key: 'enable_newsletters', label: 'Newsletters' },
			{ key: 'enable_news', label: 'Parish News' },
			{ key: 'enable_gallery', label: 'Gallery' },
			{ key: 'enable_reflections', label: 'Reflections' },
			{ key: 'enable_prayers', label: 'Prayers' },
			{ key: 'enable_events', label: 'Events Calendar' },
			{ key: 'enable_liturgical', label: 'Liturgical Calendar' },
			{ key: 'enable_slider', label: 'Hero Slider' },
		];

		const colorFields = [
			{ key: 'admin_color_base_menu', label: 'Menu Background', def: '#1d2327' },
			{ key: 'admin_color_menu_text', label: 'Menu Text', def: '#ffffff' },
			{ key: 'admin_color_highlight', label: 'Highlight/Active', def: '#2271b1' },
			{ key: 'admin_color_notification', label: 'Notifications', def: '#d63638' },
			{ key: 'admin_color_background', label: 'Page Background', def: '#f0f0f1' },
			{ key: 'admin_color_links', label: 'Links', def: '#2271b1' },
			{ key: 'admin_color_buttons', label: 'Buttons', def: '#2271b1' },
			{ key: 'admin_color_form_inputs', label: 'Form Focus', def: '#2271b1' },
		];

		const resetColors = function () {
			colorFields.forEach(function (f) {
				upd(f.key, f.def);
			});
		};

		const tabs = [
			{ name: 'features', title: 'Features', className: 'tab-features' },
			{ name: 'colors', title: 'Admin Colors', className: 'tab-colors' },
			{ name: 'shortcodes', title: 'Shortcodes & Blocks', className: 'tab-shortcodes' },
		];

		return el(
			'div',
			{ className: 'parish-settings-page' },
			notice &&
				el(
					Notice,
					{
						status: notice.type,
						isDismissible: true,
						onRemove: function () {
							setNotice(null);
						},
					},
					notice.message
				),
			el(
				TabPanel,
				{ className: 'parish-settings-tabs', tabs: tabs },
				function (tab) {
					if (tab.name === 'features') {
						return el(
							Panel,
							null,
							el(
								PanelBody,
								{ title: 'Feature Toggles', initialOpen: true },
								el(
									'p',
									{ className: 'description' },
									'Enable or disable Parish Core features. Disabled features will hide their menu items and shortcodes.'
								),
								el(
									'div',
									{ className: 'feature-toggles-grid' },
									featureToggles.map(function (t) {
										return el(ToggleControl, {
											key: t.key,
											label: t.label,
											checked: settings[t.key] !== false,
											onChange: function (v) {
												upd(t.key, v);
											},
										});
									})
								)
							)
						);
					}

					if (tab.name === 'colors') {
						return el(
							Panel,
							null,
							el(
								PanelBody,
								{ title: 'Admin Color Scheme', initialOpen: true },
								el(ToggleControl, {
									label: 'Enable Custom Colors',
									checked: settings.admin_colors_enabled === true,
									onChange: function (v) {
										upd('admin_colors_enabled', v);
									},
									help: 'Apply custom colors to the WordPress admin. Does not affect block editors.',
								}),
								settings.admin_colors_enabled &&
									el(
										Fragment,
										null,
										el(
											'p',
											{ className: 'description' },
											'Customize the WordPress admin appearance. Colors are chosen with accessibility in mind.'
										),
										el(
											'div',
											{ className: 'color-fields-grid' },
											colorFields.map(function (f) {
												return el(
													'div',
													{
														key: f.key,
														className: 'color-field',
													},
													el('label', null, f.label),
													el(
														'div',
														{ className: 'color-input-wrap' },
														el('input', {
															type: 'color',
															value:
																settings[f.key] ||
																f.def,
															onChange: function (e) {
																upd(
																	f.key,
																	e.target.value
																);
															},
														}),
														el('input', {
															type: 'text',
															className: 'color-text',
															value:
																settings[f.key] ||
																f.def,
															onChange: function (e) {
																upd(
																	f.key,
																	e.target.value
																);
															},
														})
													)
												);
											})
										),
										el(
											Button,
											{ isSecondary: true, onClick: resetColors },
											'Reset to Defaults'
										)
									)
							)
						);
					}

					if (tab.name === 'shortcodes') {
						return el(
							Panel,
							null,
							el(
								PanelBody,
								{ title: 'Shortcode & Block Reference', initialOpen: true },
								el(ShortcodeReference)
							)
						);
					}

					return null;
				}
			),
			el(
				'div',
				{ className: 'parish-save-bar' },
				el(
					Button,
					{ isPrimary: true, isBusy: saving, onClick: save },
					saving ? 'Saving...' : 'Save Settings'
				)
			)
		);
	}

	window.ParishCoreAdmin = window.ParishCoreAdmin || {};
	Object.assign(window.ParishCoreAdmin, {
		Settings,
		ShortcodeReference,
	});
})(window);