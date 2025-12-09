/**
 * Parish Core Admin - About Parish
 */
(function (window) {
	'use strict';

	const {
		el,
		useState,
		useEffect,
		Panel,
		PanelBody,
		TextControl,
		TextareaControl,
		Button,
		Notice,
		Card,
		CardBody,
		Flex,
		FlexBlock,
		apiFetch,
		LoadingSpinner,
		MediaUploader,
	} = window.ParishCoreAdmin;

	function AboutParish() {
		const [data, setData] = useState({});
		const [clergy, setClergy] = useState([]);
		const [resources, setResources] = useState([]);
		const [loading, setLoading] = useState(true);
		const [saving, setSaving] = useState(false);
		const [notice, setNotice] = useState(null);

		useEffect(function () {
			apiFetch({ path: '/parish/v1/about' })
				.then(function (res) {
					setData(res);
					setClergy(res.parish_clergy || []);
					setResources(res.parish_resources || []);
					setLoading(false);
				})
				.catch(function () {
					setLoading(false);
				});
		}, []);

		const updateField = function (k, v) {
			setData(function (p) {
				return Object.assign({}, p, { [k]: v });
			});
		};

		const saveData = function () {
			setSaving(true);
			apiFetch({
				path: '/parish/v1/about',
				method: 'POST',
				data: Object.assign({}, data, {
					parish_clergy: clergy,
					parish_resources: resources,
				}),
			})
				.then(function () {
					setSaving(false);
					setNotice({ type: 'success', message: 'Saved!' });
				})
				.catch(function (err) {
					setSaving(false);
					setNotice({ type: 'error', message: err.message });
				});
		};

		if (loading) return el(LoadingSpinner, { text: 'Loading...' });

		return el(
			'div',
			{ className: 'parish-about-page' },
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
				Panel,
				null,
				el(
					PanelBody,
					{ title: 'Parish Identity', initialOpen: true },
					el(TextControl, {
						label: 'Parish Name',
						value: data.parish_name || '',
						onChange: function (v) {
							updateField('parish_name', v);
						},
					}),
					el(TextareaControl, {
						label: 'Description',
						value: data.parish_description || '',
						onChange: function (v) {
							updateField('parish_description', v);
						},
						rows: 3,
					}),
					el(
						'div',
						{ className: 'media-fields' },
						el(MediaUploader, {
							label: 'Logo',
							value: {
								id: data.parish_logo_id,
								url: data.parish_logo_url,
							},
							onChange: function (id, url) {
								updateField('parish_logo_id', id);
								updateField('parish_logo_url', url);
							},
						}),
						el(MediaUploader, {
							label: 'Banner',
							value: {
								id: data.parish_banner_id,
								url: data.parish_banner_url,
							},
							onChange: function (id, url) {
								updateField('parish_banner_id', id);
								updateField('parish_banner_url', url);
							},
						})
					),
					el(TextControl, {
						label: 'Diocese Name',
						value: data.parish_diocese_name || '',
						onChange: function (v) {
							updateField('parish_diocese_name', v);
						},
					}),
					el(TextControl, {
						label: 'Diocese URL',
						type: 'url',
						value: data.parish_diocese_url || '',
						onChange: function (v) {
							updateField('parish_diocese_url', v);
						},
					})
				),
				el(
					PanelBody,
					{ title: 'Contact Information', initialOpen: false },
					el(TextareaControl, {
						label: 'Address',
						value: data.parish_address || '',
						onChange: function (v) {
							updateField('parish_address', v);
						},
						rows: 3,
					}),
					el(TextControl, {
						label: 'Phone',
						value: data.parish_phone || '',
						onChange: function (v) {
							updateField('parish_phone', v);
						},
					}),
					el(TextControl, {
						label: 'Email',
						type: 'email',
						value: data.parish_email || '',
						onChange: function (v) {
							updateField('parish_email', v);
						},
					}),
					el(TextControl, {
						label: 'Office Hours',
						value: data.parish_office_hours || '',
						onChange: function (v) {
							updateField('parish_office_hours', v);
						},
					})
				),
				el(
					PanelBody,
					{ title: 'Social & Online', initialOpen: false },
					el(TextControl, {
						label: 'Website',
						type: 'url',
						value: data.parish_website || '',
						onChange: function (v) {
							updateField('parish_website', v);
						},
					}),
					el(TextControl, {
						label: 'Facebook',
						type: 'url',
						value: data.parish_facebook || '',
						onChange: function (v) {
							updateField('parish_facebook', v);
						},
					}),
					el(TextControl, {
						label: 'YouTube',
						type: 'url',
						value: data.parish_youtube || '',
						onChange: function (v) {
							updateField('parish_youtube', v);
						},
					}),
					el(TextControl, {
						label: 'Livestream URL',
						type: 'url',
						value: data.parish_livestream || '',
						onChange: function (v) {
							updateField('parish_livestream', v);
						},
					})
				),
				el(
					PanelBody,
					{ title: 'Clergy & Staff', initialOpen: false },
					clergy.map(function (p, i) {
						return el(
							Card,
							{ key: i, className: 'list-item-card', size: 'small' },
							el(
								CardBody,
								null,
								el(
									Flex,
									{ gap: 2 },
									el(
										FlexBlock,
										null,
										el(TextControl, {
											label: 'Name',
											value: p.name || '',
											onChange: function (v) {
												var u = clergy.slice();
												u[i] = Object.assign({}, u[i], {
													name: v,
												});
												setClergy(u);
											},
										})
									),
									el(
										FlexBlock,
										null,
										el(TextControl, {
											label: 'Role',
											value: p.role || '',
											onChange: function (v) {
												var u = clergy.slice();
												u[i] = Object.assign({}, u[i], {
													role: v,
												});
												setClergy(u);
											},
										})
									)
								),
								el(
									Button,
									{
										isDestructive: true,
										isSmall: true,
										onClick: function () {
											setClergy(
												clergy.filter(function (_, j) {
													return j !== i;
												})
											);
										},
									},
									'Remove'
								)
							)
						);
					}),
					el(
						Button,
						{
							isSecondary: true,
							onClick: function () {
								setClergy(
									clergy.concat([{ name: '', role: '' }])
								);
							},
						},
						'+ Add'
					)
				)
			),
			el(
				'div',
				{ className: 'parish-save-bar' },
				el(
					Button,
					{ isPrimary: true, isBusy: saving, onClick: saveData },
					saving ? 'Saving...' : 'Save Changes'
				)
			)
		);
	}

	window.ParishCoreAdmin = window.ParishCoreAdmin || {};
	Object.assign(window.ParishCoreAdmin, {
		AboutParish,
	});
})(window);
