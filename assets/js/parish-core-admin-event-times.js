/**
 * Parish Core Admin - Event Times (Advanced Mass Times)
 *
 * Comprehensive event time management with recurrence, livestream, and readings support.
 */
(function (window) {
	'use strict';

	const {
		el,
		useState,
		useEffect,
		useCallback,
		useMemo,
		Fragment,
		Button,
		Notice,
		TextControl,
		TextareaControl,
		SelectControl,
		ToggleControl,
		Flex,
		FlexItem,
		FlexBlock,
		Modal,
		Spinner,
		CheckboxControl,
		DateTimePicker,
		apiFetch,
		LoadingSpinner,
	} = window.ParishCoreAdmin;

	// Days of the week for recurrence
	const WEEKDAYS = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

	// Positions for monthly recurrence
	const POSITIONS = [
		{ label: 'First', value: 'first' },
		{ label: 'Second', value: 'second' },
		{ label: 'Third', value: 'third' },
		{ label: 'Fourth', value: 'fourth' },
		{ label: 'Last', value: 'last' },
	];

	// Livestream providers
	const LIVESTREAM_PROVIDERS = [
		{ label: 'YouTube', value: 'youtube' },
		{ label: 'Facebook', value: 'facebook' },
		{ label: 'Vimeo', value: 'vimeo' },
		{ label: 'MCN Media', value: 'mcnmedia' },
		{ label: 'Church Streaming', value: 'churchstreaming' },
		{ label: 'Other', value: 'custom' },
	];

	/**
	 * Format datetime for display
	 */
	function formatDateTime(dateStr) {
		if (!dateStr) return '';
		var d = new Date(dateStr);
		return d.toLocaleDateString() + ' ' + d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
	}

	/**
	 * Format time for display
	 */
	function formatTime(dateStr) {
		if (!dateStr) return '';
		var d = new Date(dateStr);
		return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
	}

	/**
	 * Get recurrence description
	 */
	function getRecurrenceDescription(rule, isRecurring) {
		if (!isRecurring || !rule || !rule.frequency) return 'One-time';

		var desc = '';
		switch (rule.frequency) {
			case 'daily':
				desc = 'Daily';
				break;
			case 'weekly':
				if (rule.days && rule.days.length > 0) {
					desc = 'Every ' + rule.days.join(', ');
				} else {
					desc = 'Weekly';
				}
				break;
			case 'biweekly':
				if (rule.days && rule.days.length > 0) {
					desc = 'Every other ' + rule.days.join(', ');
				} else {
					desc = 'Every 2 weeks';
				}
				break;
			case 'monthly':
				if (rule.position && rule.days && rule.days.length > 0) {
					desc = rule.position.charAt(0).toUpperCase() + rule.position.slice(1) + ' ' + rule.days[0] + ' monthly';
				} else {
					desc = 'Monthly';
				}
				break;
			case 'bimonthly':
				desc = 'Every 2 months';
				break;
			case 'yearly':
				desc = 'Yearly';
				break;
			default:
				desc = 'Custom';
		}
		return desc;
	}

	/**
	 * Recurrence Builder Component
	 */
	function RecurrenceBuilder(props) {
		var rule = props.rule || {};
		var endType = props.endType || 'never';
		var endDate = props.endDate || '';
		var endCount = props.endCount || 10;
		var exceptions = Array.isArray(props.exceptions) ? props.exceptions : [];
		var onChange = props.onChange;

		var frequency = rule.frequency || 'weekly';
		// Ensure days is always an array
		var days = Array.isArray(rule.days) ? rule.days : [];
		var position = rule.position || 'first';
		var dayOfMonth = rule.day_of_month || 1;

		function updateRule(updates) {
			onChange({
				rule: Object.assign({}, rule, updates),
				endType: endType,
				endDate: endDate,
				endCount: endCount,
				exceptions: exceptions,
			});
		}

		function updateEnd(field, value) {
			var updates = { rule: rule, endType: endType, endDate: endDate, endCount: endCount, exceptions: exceptions };
			updates[field] = value;
			onChange(updates);
		}

		function toggleDay(day) {
			var newDays = days.includes(day)
				? days.filter(function (d) { return d !== day; })
				: days.concat([day]);
			updateRule({ days: newDays });
		}

		function addException() {
			var newDate = new Date().toISOString().split('T')[0];
			updateEnd('exceptions', exceptions.concat([newDate]));
		}

		function removeException(index) {
			updateEnd('exceptions', exceptions.filter(function (_, i) { return i !== index; }));
		}

		function updateException(index, value) {
			var newExceptions = exceptions.map(function (e, i) { return i === index ? value : e; });
			updateEnd('exceptions', newExceptions);
		}

		return el('div', { className: 'recurrence-builder' },
			// Frequency selector
			el('div', { className: 'recurrence-field' },
				el(SelectControl, {
					label: 'Repeats',
					value: frequency,
					options: [
						{ label: 'Daily', value: 'daily' },
						{ label: 'Weekly', value: 'weekly' },
						{ label: 'Every 2 weeks', value: 'biweekly' },
						{ label: 'Monthly', value: 'monthly' },
						{ label: 'Every 2 months', value: 'bimonthly' },
						{ label: 'Yearly', value: 'yearly' },
					],
					onChange: function (val) { updateRule({ frequency: val }); },
					__nextHasNoMarginBottom: true,
					__next40pxDefaultSize: true,
				})
			),

			// Days selection for weekly/biweekly
			(frequency === 'weekly' || frequency === 'biweekly') && el('div', { className: 'recurrence-field' },
				el('label', { className: 'components-base-control__label' }, 'On these days'),
				el('div', { className: 'day-checkboxes' },
					WEEKDAYS.map(function (day) {
						return el(CheckboxControl, {
							key: day,
							label: day.substring(0, 3),
							checked: days.includes(day),
							onChange: function () { toggleDay(day); },
							__nextHasNoMarginBottom: true,
						});
					})
				)
			),

			// Position for monthly
			frequency === 'monthly' && el('div', { className: 'recurrence-field' },
				el(Flex, { gap: 2 },
					el(FlexItem, null,
						el(SelectControl, {
							label: 'Position',
							value: position || 'first',
							options: POSITIONS,
							onChange: function (val) { updateRule({ position: val }); },
							__nextHasNoMarginBottom: true,
							__next40pxDefaultSize: true,
						})
					),
					el(FlexItem, null,
						el(SelectControl, {
							label: 'Day',
							value: days[0] || 'Sunday',
							options: WEEKDAYS.map(function (d) { return { label: d, value: d }; }),
							onChange: function (val) { updateRule({ days: [val] }); },
							__nextHasNoMarginBottom: true,
							__next40pxDefaultSize: true,
						})
					)
				)
			),

			// End rule
			el('div', { className: 'recurrence-field' },
				el(SelectControl, {
					label: 'Ends',
					value: endType,
					options: [
						{ label: 'Never', value: 'never' },
						{ label: 'On date', value: 'until' },
						{ label: 'After occurrences', value: 'count' },
					],
					onChange: function (val) { updateEnd('endType', val); },
					__nextHasNoMarginBottom: true,
					__next40pxDefaultSize: true,
				})
			),

			endType === 'until' && el('div', { className: 'recurrence-field' },
				el(TextControl, {
					type: 'date',
					label: 'End date',
					value: endDate,
					onChange: function (val) { updateEnd('endDate', val); },
					__nextHasNoMarginBottom: true,
					__next40pxDefaultSize: true,
				})
			),

			endType === 'count' && el('div', { className: 'recurrence-field' },
				el(TextControl, {
					type: 'number',
					label: 'Number of occurrences',
					value: String(endCount),
					onChange: function (val) { updateEnd('endCount', parseInt(val, 10) || 10); },
					min: 1,
					max: 999,
					__nextHasNoMarginBottom: true,
					__next40pxDefaultSize: true,
				})
			),

			// Exceptions
			el('div', { className: 'recurrence-field' },
				el('label', { className: 'components-base-control__label' }, 'Exception dates (skip these)'),
				exceptions.length > 0 && el('div', { className: 'exception-list' },
					exceptions.map(function (exc, index) {
						return el(Flex, { key: index, gap: 2, align: 'center' },
							el(FlexBlock, null,
								el(TextControl, {
									type: 'date',
									value: exc,
									onChange: function (val) { updateException(index, val); },
									__nextHasNoMarginBottom: true,
								})
							),
							el(FlexItem, null,
								el(Button, {
									isDestructive: true,
									isSmall: true,
									icon: 'no-alt',
									onClick: function () { removeException(index); },
								})
							)
						);
					})
				),
				el(Button, {
					isSecondary: true,
					isSmall: true,
					onClick: addException,
				}, '+ Add exception')
			)
		);
	}

	/**
	 * Livestream Configuration Component
	 */
	function LivestreamConfig(props) {
		var enabled = props.enabled || false;
		var mode = props.mode || 'link';
		var url = props.url || '';
		var embed = props.embed || '';
		var provider = props.provider || '';
		var onChange = props.onChange;

		function update(field, value) {
			var updates = { enabled: enabled, mode: mode, url: url, embed: embed, provider: provider };
			updates[field] = value;
			onChange(updates);
		}

		return el('div', { className: 'livestream-config' },
			el(ToggleControl, {
				label: 'Enable livestream',
				checked: enabled,
				onChange: function (val) { update('enabled', val); },
				__nextHasNoMarginBottom: true,
			}),

			enabled && el(Fragment, null,
				el(SelectControl, {
					label: 'Display mode',
					value: mode,
					options: [
						{ label: 'Link only', value: 'link' },
						{ label: 'Embedded player', value: 'embed' },
					],
					onChange: function (val) { update('mode', val); },
					__nextHasNoMarginBottom: true,
					__next40pxDefaultSize: true,
				}),

				el(SelectControl, {
					label: 'Provider',
					value: provider,
					options: [{ label: 'Select provider...', value: '' }].concat(LIVESTREAM_PROVIDERS),
					onChange: function (val) { update('provider', val); },
					__nextHasNoMarginBottom: true,
					__next40pxDefaultSize: true,
				}),

				mode === 'link' && el(TextControl, {
					label: 'Livestream URL',
					value: url,
					onChange: function (val) { update('url', val); },
					placeholder: 'https://youtube.com/watch?v=...',
					__nextHasNoMarginBottom: true,
					__next40pxDefaultSize: true,
				}),

				mode === 'embed' && el(TextareaControl, {
					label: 'Embed code',
					value: embed,
					onChange: function (val) { update('embed', val); },
					placeholder: '<iframe src="..." ...></iframe>',
					rows: 4,
					help: 'Allowed providers: YouTube, Facebook, Vimeo, MCN Media, Church Streaming',
					__nextHasNoMarginBottom: true,
				}),

				mode === 'embed' && embed && el('div', { className: 'embed-preview' },
					el('label', { className: 'components-base-control__label' }, 'Preview'),
					el('div', {
						className: 'embed-preview-frame',
						dangerouslySetInnerHTML: { __html: embed },
					})
				)
			)
		);
	}

	/**
	 * Readings Preview Component
	 */
	function ReadingsPreview(props) {
		var date = props.date;
		var mode = props.mode || 'auto';

		var stateReadings = useState(null);
		var readings = stateReadings[0];
		var setReadings = stateReadings[1];

		var stateLoading = useState(false);
		var loading = stateLoading[0];
		var setLoading = stateLoading[1];

		useEffect(function () {
			if (mode !== 'auto' || !date) {
				setReadings(null);
				return;
			}

			var dateStr = date.split('T')[0];
			setLoading(true);

			apiFetch({ path: '/parish/v1/event-times/readings/' + dateStr })
				.then(function (response) {
					setReadings(response.readings);
					setLoading(false);
				})
				.catch(function () {
					setReadings(null);
					setLoading(false);
				});
		}, [date, mode]);

		if (mode === 'none') {
			return el('div', { className: 'readings-preview' },
				el('p', { className: 'readings-disabled' }, 'Readings display is disabled for this event.')
			);
		}

		if (mode === 'override') {
			return el('div', { className: 'readings-preview' },
				el('p', null, 'Custom readings override is enabled.')
			);
		}

		if (loading) {
			return el('div', { className: 'readings-preview' },
				el(Spinner),
				el('span', null, ' Loading readings...')
			);
		}

		if (!readings) {
			return el('div', { className: 'readings-preview' },
				el('p', null, 'No readings available for this date.')
			);
		}

		return el('div', { className: 'readings-preview' },
			el('h4', null, 'Readings for this date'),
			readings.first_reading && el('div', { className: 'reading-item' },
				el('strong', null, 'First Reading: '),
				el('span', null, readings.first_reading.reference || 'Available')
			),
			readings.psalm && el('div', { className: 'reading-item' },
				el('strong', null, 'Psalm: '),
				el('span', null, readings.psalm.reference || 'Available')
			),
			readings.second_reading && el('div', { className: 'reading-item' },
				el('strong', null, 'Second Reading: '),
				el('span', null, readings.second_reading.reference || 'Available')
			),
			readings.gospel && el('div', { className: 'reading-item' },
				el('strong', null, 'Gospel: '),
				el('span', null, readings.gospel.reference || 'Available')
			)
		);
	}

	/**
	 * Event Time Editor Modal
	 */
	function EventTimeEditor(props) {
		var eventTime = props.eventTime;
		var churches = props.churches || [];
		var eventTypes = props.eventTypes || [];
		var onSave = props.onSave;
		var onClose = props.onClose;
		var isNew = !eventTime || !eventTime.id;

		// Form state - merge defaults with existing eventTime to prevent undefined values
		var stateForm = useState(function () {
			var defaults = {
				title: '',
				church_id: 0,
				event_type: 'mass',
				start_datetime: new Date().toISOString(),
				duration_minutes: 60,
				timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
				is_recurring: false,
				recurrence_rule: { frequency: 'weekly', days: [] },
				recurrence_end_type: 'never',
				recurrence_end_date: '',
				recurrence_count: 10,
				exception_dates: [],
				livestream_enabled: false,
				livestream_mode: 'link',
				livestream_url: '',
				livestream_embed: '',
				livestream_provider: '',
				intentions: '',
				notes: '',
				readings_mode: 'auto',
				readings_override: null,
				liturgical_rite: 'roman',
				liturgical_form: 'ordinary',
				language: '',
				linked_mass_id: 0,
				is_active: true,
				is_special: false,
				display_priority: 0,
			};
			if (!eventTime) {
				return defaults;
			}
			// Merge defaults with eventTime, ensuring no undefined values
			var merged = Object.assign({}, defaults, eventTime);
			// Ensure recurrence_rule is always an object (not null/undefined)
			if (!merged.recurrence_rule || typeof merged.recurrence_rule !== 'object') {
				merged.recurrence_rule = Object.assign({}, defaults.recurrence_rule);
			} else if (eventTime.recurrence_rule) {
				merged.recurrence_rule = Object.assign({}, defaults.recurrence_rule, eventTime.recurrence_rule);
			}
			// Ensure arrays are arrays
			if (!Array.isArray(merged.exception_dates)) {
				merged.exception_dates = [];
			}
			if (!Array.isArray(merged.recurrence_rule.days)) {
				merged.recurrence_rule.days = [];
			}
			return merged;
		});
		var form = stateForm[0];
		var setForm = stateForm[1];

		var stateSaving = useState(false);
		var saving = stateSaving[0];
		var setSaving = stateSaving[1];

		var stateError = useState(null);
		var error = stateError[0];
		var setError = stateError[1];

		var stateActiveTab = useState('general');
		var activeTab = stateActiveTab[0];
		var setActiveTab = stateActiveTab[1];

		function updateForm(field, value) {
			setForm(function (prev) {
				var next = Object.assign({}, prev);
				next[field] = value;
				return next;
			});
		}

		// Helper to format datetime for datetime-local input
		function formatDatetimeForInput(datetime) {
			if (!datetime || typeof datetime !== 'string') {
				return '';
			}
			// Handle ISO format (2024-01-15T10:30:00.000Z or 2024-01-15T10:30:00)
			// Extract just YYYY-MM-DDTHH:MM for the input
			var match = datetime.match(/^(\d{4}-\d{2}-\d{2})[T ](\d{2}:\d{2})/);
			if (match) {
				return match[1] + 'T' + match[2];
			}
			return '';
		}

		function handleSave() {
			// Validate required fields before submitting
			if (!form.start_datetime || typeof form.start_datetime !== 'string' || form.start_datetime.trim() === '') {
				setError('Start date and time is required.');
				return;
			}

			// Ensure data types are correct before sending
			var dataToSend = Object.assign({}, form, {
				church_id: parseInt(form.church_id, 10) || 0,
				duration_minutes: parseInt(form.duration_minutes, 10) || 60,
				recurrence_count: parseInt(form.recurrence_count, 10) || 0,
				linked_mass_id: parseInt(form.linked_mass_id, 10) || 0,
				display_priority: parseInt(form.display_priority, 10) || 0,
				is_recurring: Boolean(form.is_recurring),
				livestream_enabled: Boolean(form.livestream_enabled),
				is_active: form.is_active !== false,
				is_special: Boolean(form.is_special),
				recurrence_rule: form.recurrence_rule || { frequency: 'weekly', days: [] },
				exception_dates: Array.isArray(form.exception_dates) ? form.exception_dates : [],
				// Ensure readings_override is either a valid object or null
				readings_override: (form.readings_override && typeof form.readings_override === 'object') ? form.readings_override : null,
			});

			setSaving(true);
			setError(null);

			var path = isNew ? '/parish/v1/event-times' : '/parish/v1/event-times/' + form.id;
			var method = isNew ? 'POST' : 'PUT';

			apiFetch({ path: path, method: method, data: dataToSend })
				.then(function (response) {
					setSaving(false);
					onSave(response);
				})
				.catch(function (err) {
					setSaving(false);
					setError(err.message || 'Failed to save');
				});
		}

		// Build church options
		var churchOptions = [{ label: 'Parish-wide (All Churches)', value: 0 }].concat(
			churches.map(function (c) {
				return { label: c.title, value: c.id };
			})
		);

		// Build event type options
		var typeOptions = eventTypes.map(function (t) {
			return { label: t.label, value: t.value };
		});

		return el(Modal, {
			title: isNew ? 'Add Event Time' : 'Edit Event Time',
			onRequestClose: onClose,
			className: 'event-time-editor-modal',
			shouldCloseOnClickOutside: false,
		},
			error && el(Notice, { status: 'error', isDismissible: false }, error),

			// Tabs
			el('div', { className: 'editor-tabs' },
				el(Button, {
					className: activeTab === 'general' ? 'is-active' : '',
					onClick: function () { setActiveTab('general'); },
				}, 'General'),
				el(Button, {
					className: activeTab === 'recurrence' ? 'is-active' : '',
					onClick: function () { setActiveTab('recurrence'); },
				}, 'Schedule'),
				el(Button, {
					className: activeTab === 'livestream' ? 'is-active' : '',
					onClick: function () { setActiveTab('livestream'); },
				}, 'Livestream'),
				el(Button, {
					className: activeTab === 'content' ? 'is-active' : '',
					onClick: function () { setActiveTab('content'); },
				}, 'Content'),
				el(Button, {
					className: activeTab === 'readings' ? 'is-active' : '',
					onClick: function () { setActiveTab('readings'); },
				}, 'Readings')
			),

			// General Tab
			activeTab === 'general' && el('div', { className: 'editor-tab-content' },
				el(SelectControl, {
					label: 'Church',
					value: form.church_id,
					options: churchOptions,
					onChange: function (val) { updateForm('church_id', parseInt(val, 10)); },
					__nextHasNoMarginBottom: true,
					__next40pxDefaultSize: true,
				}),

				el(SelectControl, {
					label: 'Event Type',
					value: form.event_type,
					options: typeOptions,
					onChange: function (val) { updateForm('event_type', val); },
					__nextHasNoMarginBottom: true,
					__next40pxDefaultSize: true,
				}),

				el(TextControl, {
					label: 'Title (optional)',
					value: form.title,
					onChange: function (val) { updateForm('title', val); },
					placeholder: 'Auto-generated if empty',
					__nextHasNoMarginBottom: true,
					__next40pxDefaultSize: true,
				}),

				el('div', { className: 'datetime-field' },
					el('label', { className: 'components-base-control__label' }, 'Start Date & Time'),
					el(TextControl, {
						type: 'datetime-local',
						value: formatDatetimeForInput(form.start_datetime),
						onChange: function (val) {
							if (val && typeof val === 'string') {
								updateForm('start_datetime', val + ':00');
							}
						},
						__nextHasNoMarginBottom: true,
						__next40pxDefaultSize: true,
					})
				),

				el(TextControl, {
					type: 'number',
					label: 'Duration (minutes)',
					value: String(form.duration_minutes),
					onChange: function (val) { updateForm('duration_minutes', parseInt(val, 10) || 60); },
					min: 15,
					max: 480,
					__nextHasNoMarginBottom: true,
					__next40pxDefaultSize: true,
				}),

				el(Flex, { gap: 4 },
					el(FlexItem, null,
						el(ToggleControl, {
							label: 'Active',
							checked: form.is_active,
							onChange: function (val) { updateForm('is_active', val); },
							__nextHasNoMarginBottom: true,
						})
					),
					el(FlexItem, null,
						el(ToggleControl, {
							label: 'Special event',
							checked: form.is_special,
							onChange: function (val) { updateForm('is_special', val); },
							help: 'Highlight as special/one-time event',
							__nextHasNoMarginBottom: true,
						})
					)
				)
			),

			// Recurrence Tab
			activeTab === 'recurrence' && el('div', { className: 'editor-tab-content' },
				el(ToggleControl, {
					label: 'Recurring event',
					checked: form.is_recurring,
					onChange: function (val) { updateForm('is_recurring', val); },
					__nextHasNoMarginBottom: true,
				}),

				form.is_recurring && el(RecurrenceBuilder, {
					rule: form.recurrence_rule,
					endType: form.recurrence_end_type,
					endDate: form.recurrence_end_date,
					endCount: form.recurrence_count,
					exceptions: form.exception_dates,
					onChange: function (data) {
						setForm(function (prev) {
							return Object.assign({}, prev, {
								recurrence_rule: data.rule,
								recurrence_end_type: data.endType,
								recurrence_end_date: data.endDate,
								recurrence_count: data.endCount,
								exception_dates: data.exceptions,
							});
						});
					},
				})
			),

			// Livestream Tab
			activeTab === 'livestream' && el('div', { className: 'editor-tab-content' },
				el(LivestreamConfig, {
					enabled: form.livestream_enabled,
					mode: form.livestream_mode,
					url: form.livestream_url,
					embed: form.livestream_embed,
					provider: form.livestream_provider,
					onChange: function (data) {
						setForm(function (prev) {
							return Object.assign({}, prev, {
								livestream_enabled: data.enabled,
								livestream_mode: data.mode,
								livestream_url: data.url,
								livestream_embed: data.embed,
								livestream_provider: data.provider,
							});
						});
					},
				})
			),

			// Content Tab
			activeTab === 'content' && el('div', { className: 'editor-tab-content' },
				el(TextareaControl, {
					label: 'Intentions',
					value: form.intentions,
					onChange: function (val) { updateForm('intentions', val); },
					rows: 4,
					help: 'Mass intentions for this event',
					__nextHasNoMarginBottom: true,
				}),

				el(TextareaControl, {
					label: 'Notes',
					value: form.notes,
					onChange: function (val) { updateForm('notes', val); },
					rows: 4,
					help: 'Additional notes to display',
					__nextHasNoMarginBottom: true,
				}),

				el(Flex, { gap: 2 },
					el(FlexItem, null,
						el(SelectControl, {
							label: 'Liturgical Rite',
							value: form.liturgical_rite,
							options: [
								{ label: 'Roman Rite', value: 'roman' },
								{ label: 'Byzantine Rite', value: 'byzantine' },
								{ label: 'Maronite Rite', value: 'maronite' },
							],
							onChange: function (val) { updateForm('liturgical_rite', val); },
							__nextHasNoMarginBottom: true,
							__next40pxDefaultSize: true,
						})
					),
					el(FlexItem, null,
						el(SelectControl, {
							label: 'Form',
							value: form.liturgical_form,
							options: [
								{ label: 'Ordinary Form', value: 'ordinary' },
								{ label: 'Extraordinary Form (TLM)', value: 'extraordinary' },
							],
							onChange: function (val) { updateForm('liturgical_form', val); },
							__nextHasNoMarginBottom: true,
							__next40pxDefaultSize: true,
						})
					)
				),

				el(TextControl, {
					label: 'Language',
					value: form.language,
					onChange: function (val) { updateForm('language', val); },
					placeholder: 'e.g., English, Latin, Irish',
					__nextHasNoMarginBottom: true,
					__next40pxDefaultSize: true,
				})
			),

			// Readings Tab
			activeTab === 'readings' && el('div', { className: 'editor-tab-content' },
				el(SelectControl, {
					label: 'Readings Display',
					value: form.readings_mode,
					options: [
						{ label: 'Auto (by date)', value: 'auto' },
						{ label: 'Custom override', value: 'override' },
						{ label: 'None', value: 'none' },
					],
					onChange: function (val) { updateForm('readings_mode', val); },
					__nextHasNoMarginBottom: true,
					__next40pxDefaultSize: true,
				}),

				el(ReadingsPreview, {
					date: form.start_datetime,
					mode: form.readings_mode,
				})
			),

			// Actions
			el('div', { className: 'editor-actions' },
				el(Button, {
					isSecondary: true,
					onClick: onClose,
					disabled: saving,
				}, 'Cancel'),
				el(Button, {
					isPrimary: true,
					onClick: handleSave,
					isBusy: saving,
					disabled: saving,
				}, saving ? 'Saving...' : 'Save')
			)
		);
	}

	/**
	 * Event Time List Item
	 */
	function EventTimeListItem(props) {
		var item = props.item;
		var onEdit = props.onEdit;
		var onDuplicate = props.onDuplicate;
		var onDelete = props.onDelete;

		var typeColor = item.event_type_color || '#4A8391';

		return el('tr', { className: 'event-time-row' },
			el('td', { className: 'column-type' },
				el('span', {
					className: 'type-badge',
					style: { backgroundColor: typeColor },
				}, item.event_type_label || item.event_type)
			),
			el('td', { className: 'column-church' }, item.church_name || '—'),
			el('td', { className: 'column-time' },
				formatTime(item.start_datetime),
				item.duration_minutes > 0 && el('span', { className: 'duration' },
					' (' + item.duration_minutes + ' min)'
				)
			),
			el('td', { className: 'column-schedule' },
				getRecurrenceDescription(item.recurrence_rule, item.is_recurring)
			),
			el('td', { className: 'column-features' },
				item.livestream_enabled && el('span', { className: 'feature-badge livestream' }, 'Live'),
				item.is_special && el('span', { className: 'feature-badge special' }, 'Special'),
				!item.is_active && el('span', { className: 'feature-badge inactive' }, 'Inactive')
			),
			el('td', { className: 'column-actions' },
				el(Button, {
					isSmall: true,
					onClick: function () { onEdit(item); },
				}, 'Edit'),
				el(Button, {
					isSmall: true,
					onClick: function () { onDuplicate(item); },
				}, 'Copy'),
				el(Button, {
					isSmall: true,
					isDestructive: true,
					onClick: function () { onDelete(item); },
				}, 'Delete')
			)
		);
	}

	/**
	 * Main Event Times Component
	 */
	function EventTimes() {
		// State
		var stateEventTimes = useState([]);
		var eventTimes = stateEventTimes[0];
		var setEventTimes = stateEventTimes[1];

		var stateChurches = useState([]);
		var churches = stateChurches[0];
		var setChurches = stateChurches[1];

		var stateEventTypes = useState([]);
		var eventTypes = stateEventTypes[0];
		var setEventTypes = stateEventTypes[1];

		var stateLoading = useState(true);
		var loading = stateLoading[0];
		var setLoading = stateLoading[1];

		var stateNotice = useState(null);
		var notice = stateNotice[0];
		var setNotice = stateNotice[1];

		var stateEditing = useState(null);
		var editing = stateEditing[0];
		var setEditing = stateEditing[1];

		var stateFilters = useState({ church_id: 0, type: 'all' });
		var filters = stateFilters[0];
		var setFilters = stateFilters[1];

		// Load data
		useEffect(function () {
			Promise.all([
				apiFetch({ path: '/parish/v1/event-times' }),
				apiFetch({ path: '/parish/v1/event-times/churches' }),
				apiFetch({ path: '/parish/v1/event-times/types' }),
			])
				.then(function (results) {
					setEventTimes(results[0] || []);
					setChurches(results[1] || []);
					setEventTypes(results[2] || []);
					setLoading(false);
				})
				.catch(function (err) {
					console.error('Failed to load:', err);
					setLoading(false);
					setNotice({ type: 'error', message: 'Failed to load data.' });
				});
		}, []);

		// Refresh list
		function refreshList() {
			var params = new URLSearchParams();
			if (filters.church_id > 0) params.append('church_id', filters.church_id);
			if (filters.type !== 'all') params.append('type', filters.type);

			apiFetch({ path: '/parish/v1/event-times?' + params.toString() })
				.then(function (data) {
					setEventTimes(data || []);
				})
				.catch(function (err) {
					console.error('Failed to refresh:', err);
				});
		}

		useEffect(function () {
			if (!loading) {
				refreshList();
			}
		}, [filters]);

		// Handlers
		function handleEdit(item) {
			setEditing(item);
		}

		function handleCreate() {
			setEditing({});
		}

		function handleSave(savedItem) {
			setEditing(null);
			refreshList();
			setNotice({ type: 'success', message: 'Event time saved successfully!' });
		}

		function handleDuplicate(item) {
			apiFetch({
				path: '/parish/v1/event-times/' + item.id + '/duplicate',
				method: 'POST',
			})
				.then(function () {
					refreshList();
					setNotice({ type: 'success', message: 'Event time duplicated!' });
				})
				.catch(function (err) {
					setNotice({ type: 'error', message: 'Failed to duplicate: ' + err.message });
				});
		}

		function handleDelete(item) {
			if (!confirm('Delete this event time? This cannot be undone.')) return;

			apiFetch({
				path: '/parish/v1/event-times/' + item.id,
				method: 'DELETE',
			})
				.then(function () {
					refreshList();
					setNotice({ type: 'success', message: 'Event time deleted.' });
				})
				.catch(function (err) {
					setNotice({ type: 'error', message: 'Failed to delete: ' + err.message });
				});
		}

		// Filter options
		var churchOptions = [{ label: 'All Churches', value: 0 }].concat(
			churches.map(function (c) { return { label: c.title, value: c.id }; })
		);

		var typeOptions = [{ label: 'All Types', value: 'all' }].concat(
			eventTypes.map(function (t) { return { label: t.label, value: t.value }; })
		);

		if (loading) {
			return el(LoadingSpinner, { text: 'Loading event times...' });
		}

		return el('div', { className: 'parish-event-times-page' },
			el('div', { className: 'page-header' },
				el('h2', null, 'Event Times'),
				el('p', null, 'Manage Mass times, confessions, adoration, and other parish services.')
			),

			notice && el(Notice, {
				status: notice.type,
				isDismissible: true,
				onRemove: function () { setNotice(null); },
			}, notice.message),

			// Toolbar
			el('div', { className: 'toolbar' },
				el(Flex, { gap: 2, wrap: true, justify: 'space-between' },
					el(Flex, { gap: 2 },
						el(SelectControl, {
							value: filters.church_id,
							options: churchOptions,
							onChange: function (val) { setFilters(function (f) { return Object.assign({}, f, { church_id: parseInt(val, 10) }); }); },
							__nextHasNoMarginBottom: true,
							__next40pxDefaultSize: true,
						}),
						el(SelectControl, {
							value: filters.type,
							options: typeOptions,
							onChange: function (val) { setFilters(function (f) { return Object.assign({}, f, { type: val }); }); },
							__nextHasNoMarginBottom: true,
							__next40pxDefaultSize: true,
						})
					),
					el(Button, {
						isPrimary: true,
						onClick: handleCreate,
					}, '+ Add Event Time')
				)
			),

			// Table
			eventTimes.length === 0
				? el('div', { className: 'no-items' },
					el('p', null, 'No event times found. Create your first event time to get started.')
				)
				: el('table', { className: 'event-times-table wp-list-table widefat striped' },
					el('thead', null,
						el('tr', null,
							el('th', null, 'Type'),
							el('th', null, 'Church'),
							el('th', null, 'Time'),
							el('th', null, 'Schedule'),
							el('th', null, 'Features'),
							el('th', null, 'Actions')
						)
					),
					el('tbody', null,
						eventTimes.map(function (item) {
							return el(EventTimeListItem, {
								key: item.id,
								item: item,
								onEdit: handleEdit,
								onDuplicate: handleDuplicate,
								onDelete: handleDelete,
							});
						})
					)
				),

			// Editor Modal
			editing !== null && el(EventTimeEditor, {
				eventTime: editing,
				churches: churches,
				eventTypes: eventTypes,
				onSave: handleSave,
				onClose: function () { setEditing(null); },
			})
		);
	}

	// Export
	window.ParishCoreAdmin = window.ParishCoreAdmin || {};
	window.ParishCoreAdmin.EventTimes = EventTimes;
	window.ParishCoreAdmin.EventTimeEditor = EventTimeEditor;
	window.ParishCoreAdmin.RecurrenceBuilder = RecurrenceBuilder;
	window.ParishCoreAdmin.LivestreamConfig = LivestreamConfig;
})(window);
