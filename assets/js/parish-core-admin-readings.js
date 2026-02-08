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
		SelectControl,
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
		const [schedules, setSchedules] = useState({});
		const [scheduleOptions, setScheduleOptions] = useState([]);
		const [apiKeyModified, setApiKeyModified] = useState(false);
		const [newApiKey, setNewApiKey] = useState('');

		useEffect(function () {
			Promise.allSettled([
				apiFetch({ path: '/parish/v1/readings/status' }),
				apiFetch({ path: '/parish/v1/settings' }),
				apiFetch({ path: '/parish/v1/readings/schedules' }),
				apiFetch({ path: '/parish/v1/readings/schedule-options' }),
			])
				.then(function (results) {
					// Handle readings status
					if (results[0].status === 'fulfilled') {
						setStatus(results[0].value || { endpoints: {}, api_key_set: false });
					} else {
						console.error('Failed to load readings status:', results[0].reason);
						setStatus({ endpoints: {}, api_key_set: false });
					}

					// Handle settings
					if (results[1].status === 'fulfilled') {
						setSettings(results[1].value || {});
					} else {
						console.error('Failed to load settings:', results[1].reason);
						setSettings({});
					}

					// Handle schedules - extract the schedules property from the response
					if (results[2].status === 'fulfilled') {
						var scheduleData = results[2].value || {};
						// The API returns { schedules: {...}, endpoints: {...} }
						setSchedules(scheduleData.schedules || scheduleData || {});
					} else {
						console.error('Failed to load schedules:', results[2].reason);
						setSchedules({});
					}

					// Handle schedule options
					if (results[3].status === 'fulfilled') {
						setScheduleOptions(results[3].value || []);
					} else {
						console.error('Failed to load schedule options:', results[3].reason);
						setScheduleOptions([]);
					}

					setLoading(false);
				});
		}, []);

		const saveApiKey = function () {
			if (!apiKeyModified || !newApiKey) {
				setNotice({
					type: 'warning',
					message: 'Please enter a new API key to save.',
				});
				return;
			}
			setSaving(true);
			apiFetch({
				path: '/parish/v1/settings',
				method: 'POST',
				data: { readings_api_key: newApiKey },
			})
				.then(function () {
					setSaving(false);
					setApiKeyModified(false);
					setNewApiKey('');
					setNotice({
						type: 'success',
						message: 'API Key saved!',
					});
					apiFetch({ path: '/parish/v1/readings/status' })
						.then(function (s) {
							setStatus(s);
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

		const updateSchedule = function (endpoint, newSchedule) {
			var currentSchedule = schedules[endpoint] || {};
			var updatedSchedule = Object.assign({}, currentSchedule, { schedule: newSchedule });

			setSchedules(Object.assign({}, schedules, { [endpoint]: updatedSchedule }));

			apiFetch({
				path: '/parish/v1/readings/schedules/' + endpoint,
				method: 'PUT',
				data: { schedule: newSchedule, time: currentSchedule.time || '05:00' },
			})
				.then(function () {
					setNotice({ type: 'success', message: 'Schedule saved.' });
				})
				.catch(function (err) {
					setSchedules(Object.assign({}, schedules));
					setNotice({ type: 'error', message: err.message || 'Failed to save schedule.' });
				});
		};

		if (loading) return el(LoadingSpinner, { text: 'Loading...' });

		const endpoints =
			status && status.endpoints
				? Object.keys(status.endpoints).map(function (k) {
						return Object.assign({ key: k }, status.endpoints[k]);
				  })
				: [];

		const fetchableEndpoints = endpoints.filter(function (ep) {
			return ep.fetchable !== false;
		});
		const computedEndpoints = endpoints.filter(function (ep) {
			return ep.fetchable === false;
		});

		const hasApiKey = status && status.api_key_set;

		var defaultScheduleOptions = [
			{ label: 'Daily', value: 'daily_once' },
			{ label: 'Twice Daily', value: 'daily_twice' },
			{ label: 'Every 6 Hours', value: 'every_6_hours' },
			{ label: 'Every 4 Hours', value: 'every_4_hours' },
		];

		var selectOptions = scheduleOptions.length > 0
			? scheduleOptions.map(function (opt) {
				return { label: opt.label, value: opt.value };
			})
			: defaultScheduleOptions;

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
			!hasApiKey &&
				el(
					Notice,
					{
						status: 'warning',
						isDismissible: false,
					},
					'No API key configured. Some endpoints require an API key to fetch data. Free endpoints (Liturgy.day) will still work.'
				),
			el(
				Panel,
				null,
				el(
					PanelBody,
					{ title: 'API Configuration', initialOpen: true },
					el(TextControl, {
						label: 'Catholic Readings API Key',
						type: 'password',
						value: newApiKey,
						placeholder: settings.readings_api_key ? '••••••••••••' : 'Enter API key...',
						onChange: function (v) {
							setNewApiKey(v);
							setApiKeyModified(true);
						},
						help: status && status.api_key_set
							? 'API key is configured. Enter a new key to replace it.'
							: 'Enter your Catholic Readings API key. Note: Liturgy.day endpoints do not require an API key.',
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
								'API Key configured'
							)
					)
				),
				el(
					PanelBody,
					{ title: 'API Endpoints', initialOpen: true },
					el(
						'p',
						{ className: 'description' },
						'Readings are automatically fetched based on the schedule. Click Fetch to manually refresh.'
					),
					fetchableEndpoints.length === 0 && el(
						'p',
						{ style: { padding: '20px', textAlign: 'center', color: '#666' } },
						status && status.error ? status.error : 'No endpoints available.'
					),
					fetchableEndpoints.length > 0 && el(
						'div',
						{ className: 'readings-endpoint-cards' },
						fetchableEndpoints.map(function (ep) {
							var requiresKey = ep.requires_key !== false;
							var canFetch = !requiresKey || status.api_key_set;
							var currentSchedule = schedules[ep.key] || {};
							var scheduleValue = currentSchedule.schedule || 'daily_once';

							return el(
								'div',
								{
									key: ep.key,
									className: 'readings-endpoint-card' + (!canFetch ? ' disabled' : ''),
								},
								el(
									'div',
									{ className: 'endpoint-card-header' },
									el(
										'div',
										{ className: 'endpoint-card-status' },
										el('span', {
											className: 'status-dot' + (canFetch ? ' active' : ' inactive'),
											title: canFetch
												? (requiresKey ? 'API key configured' : 'No API key required')
												: 'API key required',
										}),
										el('strong', null, ep.name),
										!requiresKey && el('span', { className: 'free-badge' }, 'Free')
									),
									el(
										Button,
										{
											isSecondary: true,
											isSmall: true,
											isBusy: fetching[ep.key],
											disabled: !canFetch,
											onClick: function () {
												fetchEndpoint(ep.key);
											},
										},
										'Fetch Now'
									)
								),
								el(
									'div',
									{ className: 'endpoint-card-body' },
									el(
										'div',
										{ className: 'endpoint-card-row' },
										el('span', { className: 'endpoint-label' }, 'Shortcode'),
										el('code', { className: 'endpoint-shortcode' }, ep.shortcode)
									),
									el(
										'div',
										{ className: 'endpoint-card-row' },
										el('span', { className: 'endpoint-label' }, 'Schedule'),
										el(
											'div',
											{ className: 'endpoint-schedule-select' },
											el(SelectControl, {
												value: scheduleValue,
												options: selectOptions,
												onChange: function (val) {
													updateSchedule(ep.key, val);
												},
												__nextHasNoMarginBottom: true,
											})
										)
									),
									el(
										'div',
										{ className: 'endpoint-card-row' },
										el('span', { className: 'endpoint-label' }, 'Last Fetched'),
										el('span', { className: 'endpoint-date' }, ep.last_fetch || 'Never')
									)
								)
							);
						})
					),
					el(
						'div',
						{ className: 'readings-actions', style: { marginTop: '20px', paddingTop: '16px', borderTop: '1px solid #ddd' } },
						el(
							Button,
							{
								isPrimary: true,
								isBusy: fetching.all,
								onClick: fetchAll,
							},
							'Fetch All Readings'
						)
					)
				),
				computedEndpoints.length > 0 && el(
					PanelBody,
					{ title: 'Computed Shortcodes', initialOpen: false },
					el(
						'p',
						{ className: 'description' },
						'These shortcodes use data from the API endpoints above.'
					),
					el(
						'div',
						{ className: 'computed-shortcodes-list' },
						computedEndpoints.map(function (ep) {
							return el(
								'div',
								{ key: ep.key, className: 'computed-shortcode-item' },
								el('code', null, ep.shortcode),
								el('span', null, ep.name)
							);
						})
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
