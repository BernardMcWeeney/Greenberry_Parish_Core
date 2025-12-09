/**
 * Parish Core Admin - Readings API
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
		Button,
		Notice,
		Flex,
		apiFetch,
		LoadingSpinner,
	} = window.ParishCoreAdmin;

	function ReadingsAPI() {
		const [status, setStatus] = useState(null);
		const [settings, setSettings] = useState({});
		const [loading, setLoading] = useState(true);
		const [saving, setSaving] = useState(false);
		const [fetching, setFetching] = useState({});
		const [notice, setNotice] = useState(null);

		useEffect(function () {
			Promise.all([
				apiFetch({ path: '/parish/v1/readings/status' }),
				apiFetch({ path: '/parish/v1/settings' }),
			])
				.then(function (res) {
					setStatus(res[0]);
					setSettings(res[1]);
					setLoading(false);
				})
				.catch(function () {
					setLoading(false);
				});
		}, []);

		const saveApiKey = function () {
			setSaving(true);
			apiFetch({
				path: '/parish/v1/settings',
				method: 'POST',
				data: { readings_api_key: settings.readings_api_key },
			})
				.then(function () {
					setSaving(false);
					setNotice({
						type: 'success',
						message: 'API Key saved!',
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

		const fetchEndpoint = function (ep) {
			setFetching(
				Object.assign({}, fetching, {
					[ep]: true,
				})
			);
			apiFetch({
				path: '/parish/v1/readings/fetch',
				method: 'POST',
				data: { endpoint: ep },
			})
				.then(function (res) {
					setFetching(
						Object.assign({}, fetching, {
							[ep]: false,
						})
					);
					setNotice({
						type: res.success ? 'success' : 'error',
						message: res.message,
					});
					if (res.success) {
						apiFetch({
							path: '/parish/v1/readings/status',
						}).then(function (s) {
							setStatus(s);
						});
					}
				})
				.catch(function (err) {
					setFetching(
						Object.assign({}, fetching, {
							[ep]: false,
						})
					);
					setNotice({
						type: 'error',
						message: err.message,
					});
				});
		};

		const fetchAll = function () {
			setFetching({ all: true });
			apiFetch({
				path: '/parish/v1/readings/fetch',
				method: 'POST',
			})
				.then(function (res) {
					setFetching({});
					setNotice({
						type: res.success ? 'success' : 'error',
						message: res.success ? 'All readings fetched!' : 'Failed',
					});
					apiFetch({
						path: '/parish/v1/readings/status',
					}).then(function (s) {
						setStatus(s);
					});
				})
				.catch(function () {
					setFetching({});
					setNotice({
						type: 'error',
						message: 'Failed to fetch readings',
					});
				});
		};

		if (loading) return el(LoadingSpinner, { text: 'Loading...' });

		const endpoints =
			status && status.endpoints
				? Object.keys(status.endpoints).map(function (k) {
						return Object.assign({ key: k }, status.endpoints[k]);
				  })
				: [];

		return el(
			'div',
			{ className: 'parish-readings-page' },
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
					{ title: 'API Configuration', initialOpen: true },
					el(TextControl, {
						label: 'API Key',
						type: 'password',
						value: settings.readings_api_key || '',
						onChange: function (v) {
							setSettings(
								Object.assign({}, settings, {
									readings_api_key: v,
								})
							);
						},
						help: 'Enter your Catholic Readings API key',
					}),
					el(
						Flex,
						{ gap: 2 },
						el(
							Button,
							{ isPrimary: true, isBusy: saving, onClick: saveApiKey },
							'Save API Key'
						),
						status &&
							status.api_key_set &&
							el(
								'span',
								{ className: 'api-status ok' },
								'✓ API Key configured'
							)
					)
				),
				el(
					PanelBody,
					{ title: 'Endpoints', initialOpen: true },
					el(
						'p',
						{ className: 'description' },
						'Fetch readings from the API. Data is cached for 24 hours.'
					),
					el(
						'div',
						{ className: 'endpoints-list' },
						el(
							'div',
							{ className: 'endpoints-header' },
							el('span', null, 'Endpoint'),
							el('span', null, 'Shortcode'),
							el('span', null, 'Last Fetched'),
							el('span', null, 'Action')
						),
						endpoints.map(function (ep) {
							return el(
								'div',
								{ key: ep.key, className: 'endpoint-row' },
								el(
									'span',
									{ className: 'ep-name' },
									ep.name
								),
								el(
									'code',
									{ className: 'ep-shortcode' },
									ep.shortcode
								),
								el(
									'span',
									{ className: 'ep-date' },
									ep.last_fetch || 'Never'
								),
								el(
									Button,
									{
										isSecondary: true,
										isSmall: true,
										isBusy: fetching[ep.key],
										disabled: !status.api_key_set,
										onClick: function () {
											fetchEndpoint(ep.key);
										},
									},
									'Fetch'
								)
							);
						})
					),
					el(
						'div',
						{ className: 'endpoints-actions' },
						el(
							Button,
							{
								isPrimary: true,
								isBusy: fetching.all,
								disabled: !status.api_key_set,
								onClick: fetchAll,
							},
							'Fetch All Readings'
						)
					)
				)
			)
		);
	}

	window.ParishCoreAdmin = window.ParishCoreAdmin || {};
	Object.assign(window.ParishCoreAdmin, {
		ReadingsAPI,
	});
})(window);
