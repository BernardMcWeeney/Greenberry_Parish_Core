/**
 * Parish Core Admin - Mass Times Weekly Grid Editor
 *
 * A 7-day weekly grid view for quick Mass Times management.
 * Allows adding, editing, deleting Mass Times with visual week layout.
 */
(function (window) {
	'use strict';

	const {
		el,
		useState,
		useEffect,
		useCallback,
		useMemo,
		useRef,
		Fragment,
		Button,
		Notice,
		TextControl,
		SelectControl,
		ToggleControl,
		Flex,
		FlexItem,
		FlexBlock,
		Modal,
		Spinner,
		apiFetch,
		LoadingSpinner,
	} = window.ParishCoreAdmin;

	// Days of the week
	const WEEKDAYS = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

	/**
	 * Format time for display
	 */
	function formatTime(timeStr) {
		if (!timeStr) return '';
		var parts = timeStr.split(':');
		var hours = parseInt(parts[0], 10);
		var mins = parts[1] || '00';
		var ampm = hours >= 12 ? 'PM' : 'AM';
		hours = hours % 12 || 12;
		return hours + ':' + mins + ' ' + ampm;
	}

	/**
	 * Event Card Component
	 */
	function EventCard(props) {
		var event = props.event;
		var onEdit = props.onEdit;
		var onDelete = props.onDelete;
		var onDuplicate = props.onDuplicate;

		var typeColor = event.event_type_color || '#4A8391';

		return el('div', {
			className: 'grid-event-card' + (event.is_recurring ? ' is-recurring' : '') + (event.is_special ? ' is-special' : ''),
			style: { borderLeftColor: typeColor },
		},
			el('div', { className: 'event-card-main' },
				el('span', { className: 'event-card-time' }, event.time_formatted || formatTime(event.time)),
				el('span', { className: 'event-card-type' }, event.event_type_label || event.event_type),
				event.church_name && el('span', { className: 'event-card-church' }, event.church_name)
			),
			el('div', { className: 'event-card-badges' },
				event.is_recurring && el('span', { className: 'badge badge-recurring', title: 'Recurring' }, 'R'),
				event.livestream && event.livestream.enabled && el('span', { className: 'badge badge-live', title: 'Livestream' }, 'L'),
				event.is_special && el('span', { className: 'badge badge-special', title: 'Special' }, 'S')
			),
			el('div', { className: 'event-card-actions' },
				el(Button, {
					isSmall: true,
					icon: 'edit',
					label: 'Edit',
					onClick: function () { onEdit(event); },
				}),
				el(Button, {
					isSmall: true,
					icon: 'admin-page',
					label: 'Duplicate',
					onClick: function () { onDuplicate(event); },
				}),
				el(Button, {
					isSmall: true,
					icon: 'trash',
					label: 'Delete',
					isDestructive: true,
					onClick: function () { onDelete(event); },
				})
			)
		);
	}

	/**
	 * Day Column Component
	 */
	function DayColumn(props) {
		var day = props.day;
		var events = day.events || [];
		var onAddEvent = props.onAddEvent;
		var onEditEvent = props.onEditEvent;
		var onDeleteEvent = props.onDeleteEvent;
		var onDuplicateEvent = props.onDuplicateEvent;
		var onClearDay = props.onClearDay;

		var dayClass = 'grid-day-column';
		if (day.is_today) dayClass += ' is-today';
		if (day.is_past) dayClass += ' is-past';

		// Feast day data
		var feast = day.feast;
		var hasFeast = feast && feast.title;

		return el('div', { className: dayClass },
			el('div', { className: 'day-header' },
				el('span', { className: 'day-name' }, day.day_short),
				el('span', { className: 'day-number' }, day.day_number),
				day.is_today && el('span', { className: 'today-badge' }, 'Today')
			),

			// Feast day display
			feast && el('div', {
				className: 'day-feast' + (hasFeast ? ' day-feast--' + feast.rank : ''),
				style: { borderLeftColor: feast.color_hex || '#228B22' },
				title: hasFeast ? feast.title + ' (' + feast.rank + ')' : feast.season,
			},
				hasFeast
					? el('span', { className: 'feast-title' }, feast.title)
					: el('span', { className: 'feast-season' }, feast.season)
			),

			el('div', { className: 'day-events' },
				events.length === 0
					? el('div', { className: 'no-events' }, 'No events')
					: events.map(function (event) {
						return el(EventCard, {
							key: event.id,
							event: event,
							onEdit: onEditEvent,
							onDelete: onDeleteEvent,
							onDuplicate: onDuplicateEvent,
						});
					})
			),

			el('div', { className: 'day-footer' },
				el(Button, {
					isSmall: true,
					isSecondary: true,
					className: 'add-event-btn',
					onClick: function () { onAddEvent(day.date); },
				}, '+ Add'),

				events.length > 0 && el(Button, {
					isSmall: true,
					isDestructive: true,
					className: 'clear-day-btn',
					onClick: function () { onClearDay(day.date); },
				}, 'Clear')
			)
		);
	}

	/**
	 * Week Navigation Component
	 */
	function WeekNavigation(props) {
		var weekLabel = props.weekLabel;
		var weekStart = props.weekStart;
		var onPrevWeek = props.onPrevWeek;
		var onNextWeek = props.onNextWeek;
		var onToday = props.onToday;
		var onCopyLastWeek = props.onCopyLastWeek;
		var loading = props.loading;

		return el(Flex, { className: 'week-navigation', justify: 'space-between', align: 'center' },
			el(FlexItem, null,
				el(Flex, { gap: 2 },
					el(Button, {
						isSecondary: true,
						onClick: onPrevWeek,
						disabled: loading,
						icon: 'arrow-left-alt2',
					}, 'Prev'),
					el(Button, {
						isSecondary: true,
						onClick: onToday,
						disabled: loading,
					}, 'Today'),
					el(Button, {
						isSecondary: true,
						onClick: onNextWeek,
						disabled: loading,
						icon: 'arrow-right-alt2',
						iconPosition: 'right',
					}, 'Next')
				)
			),

			el(FlexItem, null,
				el('h3', { className: 'week-label' }, weekLabel)
			),

			el(FlexItem, null,
				el(Button, {
					isSecondary: true,
					onClick: onCopyLastWeek,
					disabled: loading,
					icon: 'admin-page',
				}, 'Copy Last Week')
			)
		);
	}

	/**
	 * Main Mass Times Grid Component
	 */
	function MassTimesGrid(props) {
		var onSwitchView = props.onSwitchView;

		// State
		var stateGrid = useState([]);
		var grid = stateGrid[0];
		var setGrid = stateGrid[1];

		var stateWeekStart = useState('');
		var weekStart = stateWeekStart[0];
		var setWeekStart = stateWeekStart[1];

		var stateWeekLabel = useState('');
		var weekLabel = stateWeekLabel[0];
		var setWeekLabel = stateWeekLabel[1];

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

		var stateFilters = useState({ church_id: 0, type: 'all' });
		var filters = stateFilters[0];
		var setFilters = stateFilters[1];

		var stateEditing = useState(null);
		var editing = stateEditing[0];
		var setEditing = stateEditing[1];

		// Load initial data
		useEffect(function () {
			Promise.all([
				apiFetch({ path: '/parish/v1/event-times/churches' }),
				apiFetch({ path: '/parish/v1/event-times/types' }),
			])
				.then(function (results) {
					setChurches(results[0] || []);
					setEventTypes(results[1] || []);
					loadGrid();
				})
				.catch(function (err) {
					console.error('Failed to load metadata:', err);
					setLoading(false);
					setNotice({ type: 'error', message: 'Failed to load initial data.' });
				});
		}, []);

		// Load grid data
		var loadGrid = useCallback(function (targetWeekStart) {
			setLoading(true);

			var params = new URLSearchParams();
			if (targetWeekStart) {
				params.append('week_start', targetWeekStart);
			}
			if (filters.church_id > 0) {
				params.append('church_id', filters.church_id);
			}
			if (filters.type !== 'all') {
				params.append('type', filters.type);
			}

			apiFetch({ path: '/parish/v1/mass-times/grid?' + params.toString() })
				.then(function (response) {
					setGrid(response.grid || []);
					setWeekStart(response.week_start);
					setWeekLabel(response.week_label);
					setLoading(false);
				})
				.catch(function (err) {
					console.error('Failed to load grid:', err);
					setLoading(false);
					setNotice({ type: 'error', message: 'Failed to load schedule.' });
				});
		}, [filters]);

		// Reload when filters change
		useEffect(function () {
			if (weekStart) {
				loadGrid(weekStart);
			}
		}, [filters]);

		// Navigation handlers
		function goToPrevWeek() {
			var prev = new Date(weekStart);
			prev.setDate(prev.getDate() - 7);
			loadGrid(prev.toISOString().split('T')[0]);
		}

		function goToNextWeek() {
			var next = new Date(weekStart);
			next.setDate(next.getDate() + 7);
			loadGrid(next.toISOString().split('T')[0]);
		}

		function goToToday() {
			loadGrid('');
		}

		// Add new event handler - opens modal with pre-filled date
		function handleAddNew(date) {
			// Create a new event object with the selected date
			var startDateTime = date + 'T09:00:00';
			setEditing({
				id: 0,
				title: '',
				event_type: 'mass',
				start_datetime: startDateTime,
				duration_minutes: 60,
				church_id: filters.church_id || 0,
				is_recurring: false,
				recurrence_rule: { frequency: 'weekly', days: [] },
				description: '',
				livestream_enabled: false,
				readings_override: null,
			});
		}

		// Edit handler
		function handleEdit(event) {
			// Fetch full event data
			apiFetch({ path: '/parish/v1/event-times/' + event.event_time_id })
				.then(function (fullEvent) {
					setEditing(fullEvent);
				})
				.catch(function (err) {
					setNotice({ type: 'error', message: 'Failed to load event details.' });
				});
		}

		// Save handler
		function handleSave(savedEvent) {
			setEditing(null);
			loadGrid(weekStart);
			setNotice({ type: 'success', message: 'Mass time saved successfully!' });
		}

		// Delete handler
		function handleDelete(event) {
			if (!confirm('Delete this mass time? This cannot be undone.')) return;

			apiFetch({
				path: '/parish/v1/event-times/' + event.event_time_id,
				method: 'DELETE',
			})
				.then(function () {
					loadGrid(weekStart);
					setNotice({ type: 'success', message: 'Mass time deleted.' });
				})
				.catch(function (err) {
					setNotice({ type: 'error', message: 'Failed to delete: ' + (err.message || 'Unknown error') });
				});
		}

		// Duplicate handler
		function handleDuplicate(event) {
			// Show a day picker or duplicate to same day
			var targetDate = prompt('Enter target date (YYYY-MM-DD):', event.date);
			if (!targetDate) return;

			apiFetch({
				path: '/parish/v1/mass-times/' + event.event_time_id + '/duplicate-to-day',
				method: 'POST',
				data: { target_date: targetDate, copy_time: true },
			})
				.then(function (response) {
					loadGrid(weekStart);
					setNotice({ type: 'success', message: response.message || 'Duplicated!' });
				})
				.catch(function (err) {
					setNotice({ type: 'error', message: 'Failed to duplicate: ' + (err.message || 'Unknown error') });
				});
		}

		// Clear day handler
		function handleClearDay(date) {
			if (!confirm('Delete all non-recurring events for this day?')) return;

			var data = { date: date };
			if (filters.church_id > 0) {
				data.church_id = filters.church_id;
			}

			apiFetch({
				path: '/parish/v1/mass-times/clear-day',
				method: 'POST',
				data: data,
			})
				.then(function (response) {
					loadGrid(weekStart);
					setNotice({ type: 'success', message: response.message || 'Day cleared.' });
				})
				.catch(function (err) {
					setNotice({ type: 'error', message: 'Failed to clear day: ' + (err.message || 'Unknown error') });
				});
		}

		// Copy last week handler
		function handleCopyLastWeek() {
			var lastWeek = new Date(weekStart);
			lastWeek.setDate(lastWeek.getDate() - 7);

			if (!confirm('Copy all events from last week to this week?')) return;

			apiFetch({
				path: '/parish/v1/mass-times/copy-week',
				method: 'POST',
				data: {
					source_week_start: lastWeek.toISOString().split('T')[0],
					target_week_start: weekStart,
					church_id: filters.church_id,
				},
			})
				.then(function (response) {
					loadGrid(weekStart);
					setNotice({ type: 'success', message: response.message || 'Week copied!' });
				})
				.catch(function (err) {
					setNotice({ type: 'error', message: 'Failed to copy week: ' + (err.message || 'Unknown error') });
				});
		}

		// Filter options
		var churchOptions = [{ label: 'All Churches', value: 0 }].concat(
			churches.map(function (c) { return { label: c.title, value: c.id }; })
		);

		var typeOptions = [{ label: 'All Types', value: 'all' }].concat(
			eventTypes.map(function (t) { return { label: t.label, value: t.value }; })
		);

		// Render
		return el('div', { className: 'mass-times-grid-page' },
			// Header
			el('div', { className: 'page-header' },
				el(Flex, { justify: 'space-between', align: 'center' },
					el(FlexItem, null,
						el('h2', null, 'Mass Times'),
						el('p', null, 'Manage your parish schedule with the weekly grid view.')
					),
					el(FlexItem, null,
						el(Button, {
							isSecondary: true,
							onClick: onSwitchView,
						}, 'Switch to List View')
					)
				)
			),

			// Notices
			notice && el(Notice, {
				status: notice.type,
				isDismissible: true,
				onRemove: function () { setNotice(null); },
			}, notice.message),

			// Filters
			el('div', { className: 'grid-filters' },
				el(Flex, { gap: 2 },
					el(SelectControl, {
						value: filters.church_id,
						options: churchOptions,
						onChange: function (v) { setFilters(function (f) { return Object.assign({}, f, { church_id: parseInt(v, 10) }); }); },
						__nextHasNoMarginBottom: true,
						__next40pxDefaultSize: true,
					}),
					el(SelectControl, {
						value: filters.type,
						options: typeOptions,
						onChange: function (v) { setFilters(function (f) { return Object.assign({}, f, { type: v }); }); },
						__nextHasNoMarginBottom: true,
						__next40pxDefaultSize: true,
					})
				)
			),

			// Week Navigation
			el(WeekNavigation, {
				weekLabel: weekLabel,
				weekStart: weekStart,
				onPrevWeek: goToPrevWeek,
				onNextWeek: goToNextWeek,
				onToday: goToToday,
				onCopyLastWeek: handleCopyLastWeek,
				loading: loading,
			}),

			// Grid
			loading
				? el(LoadingSpinner, { text: 'Loading schedule...' })
				: el('div', { className: 'week-grid' },
					grid.map(function (day) {
						return el(DayColumn, {
							key: day.date,
							day: day,
							onAddEvent: handleAddNew,
							onEditEvent: handleEdit,
							onDeleteEvent: handleDelete,
							onDuplicateEvent: handleDuplicate,
							onClearDay: handleClearDay,
						});
					})
				),

			// Editor Modal
			editing !== null && el(window.ParishCoreAdmin.EventTimeEditor || EventTimeEditorFallback, {
				eventTime: editing,
				churches: churches,
				eventTypes: eventTypes,
				onSave: handleSave,
				onClose: function () { setEditing(null); },
			})
		);
	}

	/**
	 * Fallback editor if the full editor isn't loaded
	 */
	function EventTimeEditorFallback(props) {
		return el(Modal, {
			title: 'Edit Event Time',
			onRequestClose: props.onClose,
		},
			el('p', null, 'Full editor not available. Please use the list view for editing.'),
			el(Button, { isPrimary: true, onClick: props.onClose }, 'Close')
		);
	}

	/**
	 * Combined Mass Times Component (Grid + List toggle)
	 */
	function MassTimesManager() {
		var stateView = useState('grid');
		var view = stateView[0];
		var setView = stateView[1];

		if (view === 'grid') {
			return el(MassTimesGrid, {
				onSwitchView: function () { setView('list'); },
			});
		}

		// Fall back to the existing EventTimes component
		if (window.ParishCoreAdmin.EventTimes) {
			return el('div', null,
				el('div', { className: 'view-switcher' },
					el(Button, {
						isSecondary: true,
						onClick: function () { setView('grid'); },
					}, 'Switch to Grid View')
				),
				el(window.ParishCoreAdmin.EventTimes)
			);
		}

		return el('div', null, 'List view not available.');
	}

	// Export components
	window.ParishCoreAdmin = window.ParishCoreAdmin || {};
	window.ParishCoreAdmin.MassTimesGrid = MassTimesGrid;
	window.ParishCoreAdmin.MassTimesManager = MassTimesManager;

})(window);
