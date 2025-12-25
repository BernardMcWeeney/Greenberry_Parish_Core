/**
 * Parish Core Admin - Mass Times
 *
 * Simple 7-day schedule management for mass times with church and livestream support.
 */
(function (window) {
	'use strict';

	const {
		el,
		useState,
		useEffect,
		Button,
		Notice,
		TextControl,
		SelectControl,
		ToggleControl,
		Flex,
		apiFetch,
		LoadingSpinner,
	} = window.ParishCoreAdmin;

	// Days of the week
	const DAYS = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

	// Event types
	const EVENT_TYPES = [
		{ label: 'Mass', value: 'mass' },
		{ label: 'Confession', value: 'confession' },
		{ label: 'Adoration', value: 'adoration' },
		{ label: 'Rosary', value: 'rosary' },
		{ label: 'Stations of the Cross', value: 'stations' },
		{ label: 'Benediction', value: 'benediction' },
		{ label: 'Vespers', value: 'vespers' },
		{ label: 'Novena', value: 'novena' },
		{ label: 'Other', value: 'other' },
	];

	/**
	 * Time slot component with church selector and livestream toggle
	 */
	function TimeSlot(props) {
		var slot = props.slot;
		var churches = props.churches || [];
		var onUpdate = props.onUpdate;
		var onRemove = props.onRemove;

		// Build church options
		var churchOptions = [{ label: 'All Churches', value: '' }].concat(
			churches.map(function (church) {
				return {
					label: church.title.rendered || '(Untitled)',
					value: String(church.id),
				};
			})
		);

		return el('div', { className: 'time-slot' },
			el('div', { className: 'time-slot-row' },
				el(Flex, { gap: 2, align: 'center', wrap: true },
					el('div', { className: 'time-input' },
						el(TextControl, {
							type: 'time',
							value: slot.time || '',
							onChange: function (val) { onUpdate({ ...slot, time: val }); },
							__nextHasNoMarginBottom: true,
							__next40pxDefaultSize: true,
						})
					),
					el('div', { className: 'type-select' },
						el(SelectControl, {
							value: slot.type || 'mass',
							options: EVENT_TYPES,
							onChange: function (val) { onUpdate({ ...slot, type: val }); },
							__nextHasNoMarginBottom: true,
							__next40pxDefaultSize: true,
						})
					),
					churches.length > 0 && el('div', { className: 'church-select' },
						el(SelectControl, {
							value: slot.church_id || '',
							options: churchOptions,
							onChange: function (val) { onUpdate({ ...slot, church_id: val }); },
							__nextHasNoMarginBottom: true,
							__next40pxDefaultSize: true,
						})
					),
					el(Button, {
						isDestructive: true,
						isSmall: true,
						onClick: onRemove,
						icon: 'trash',
						label: 'Remove',
					})
				)
			),
			el('div', { className: 'time-slot-row time-slot-options' },
				el(Flex, { gap: 4, align: 'center', wrap: true },
					el('div', { className: 'notes-input' },
						el(TextControl, {
							placeholder: 'Notes (e.g., Latin Mass, First Friday)',
							value: slot.notes || '',
							onChange: function (val) { onUpdate({ ...slot, notes: val }); },
							__nextHasNoMarginBottom: true,
							__next40pxDefaultSize: true,
						})
					),
					el('div', { className: 'livestream-toggle' },
						el(ToggleControl, {
							label: 'Livestream',
							checked: slot.livestream || false,
							onChange: function (val) { onUpdate({ ...slot, livestream: val }); },
							__nextHasNoMarginBottom: true,
						})
					)
				)
			)
		);
	}

	/**
	 * Day schedule component
	 */
	function DaySchedule(props) {
		var day = props.day;
		var slots = props.slots || [];
		var churches = props.churches || [];
		var onUpdate = props.onUpdate;

		function addSlot() {
			var newSlots = slots.concat([{
				time: '09:00',
				type: 'mass',
				church_id: '',
				notes: '',
				livestream: false,
			}]);
			onUpdate(newSlots);
		}

		function updateSlot(index, newSlot) {
			var newSlots = slots.map(function (s, i) {
				return i === index ? newSlot : s;
			});
			onUpdate(newSlots);
		}

		function removeSlot(index) {
			var newSlots = slots.filter(function (_, i) {
				return i !== index;
			});
			onUpdate(newSlots);
		}

		return el('div', { className: 'day-schedule' },
			el('div', { className: 'day-header' },
				el('h4', null, day),
				el(Button, {
					isSecondary: true,
					isSmall: true,
					onClick: addSlot,
				}, '+ Add Time')
			),
			el('div', { className: 'day-slots' },
				slots.length === 0
					? el('p', { className: 'no-times' }, 'No times scheduled')
					: slots.map(function (slot, index) {
						return el(TimeSlot, {
							key: day + '-' + index,
							slot: slot,
							churches: churches,
							onUpdate: function (s) { updateSlot(index, s); },
							onRemove: function () { removeSlot(index); },
						});
					})
			)
		);
	}

	/**
	 * Main Mass Times component
	 */
	function MassTimes() {
		var stateSchedule = useState({});
		var schedule = stateSchedule[0];
		var setSchedule = stateSchedule[1];

		var stateChurches = useState([]);
		var churches = stateChurches[0];
		var setChurches = stateChurches[1];

		var stateLoading = useState(true);
		var loading = stateLoading[0];
		var setLoading = stateLoading[1];

		var stateSaving = useState(false);
		var saving = stateSaving[0];
		var setSaving = stateSaving[1];

		var stateNotice = useState(null);
		var notice = stateNotice[0];
		var setNotice = stateNotice[1];

		// Load schedule and churches on mount
		useEffect(function () {
			Promise.all([
				apiFetch({ path: '/parish/v1/settings' }),
				apiFetch({ path: '/wp/v2/parish_church?per_page=100&_fields=id,title' }).catch(function () { return []; }),
			])
				.then(function (results) {
					var settings = results[0];
					var churchList = results[1];
					setSchedule(settings.mass_times_schedule || {});
					setChurches(churchList || []);
					setLoading(false);
				})
				.catch(function (err) {
					console.error('Failed to load data:', err);
					setSchedule({});
					setChurches([]);
					setLoading(false);
				});
		}, []);

		function updateDay(day, slots) {
			var newSchedule = Object.assign({}, schedule);
			newSchedule[day] = slots;
			setSchedule(newSchedule);
		}

		function saveSchedule() {
			setSaving(true);
			setNotice(null);

			apiFetch({
				path: '/parish/v1/settings',
				method: 'POST',
				data: { mass_times_schedule: schedule },
			})
				.then(function () {
					setNotice({ type: 'success', message: 'Schedule saved successfully!' });
					setSaving(false);
				})
				.catch(function (err) {
					setNotice({ type: 'error', message: 'Failed to save: ' + (err.message || 'Unknown error') });
					setSaving(false);
				});
		}

		if (loading) {
			return el(LoadingSpinner, { text: 'Loading schedule...' });
		}

		return el('div', { className: 'parish-mass-times-page' },
			el('div', { className: 'page-header' },
				el('h2', null, 'Mass Times'),
				el('p', null, 'Configure the weekly mass and service schedule. Use the shortcode [parish_mass_times] to display on your site.'),
				churches.length > 0 && el('p', { className: 'church-count' },
					churches.length + ' church' + (churches.length !== 1 ? 'es' : '') + ' available for assignment.'
				)
			),

			notice && el(Notice, {
				status: notice.type,
				isDismissible: true,
				onRemove: function () { setNotice(null); },
			}, notice.message),

			el('div', { className: 'schedule-grid' },
				DAYS.map(function (day) {
					return el(DaySchedule, {
						key: day,
						day: day,
						slots: schedule[day] || [],
						churches: churches,
						onUpdate: function (slots) { updateDay(day, slots); },
					});
				})
			),

			el('div', { className: 'parish-save-bar' },
				el(Button, {
					isPrimary: true,
					isBusy: saving,
					disabled: saving,
					onClick: saveSchedule,
				}, saving ? 'Saving...' : 'Save Schedule')
			)
		);
	}

	// Export
	window.ParishCoreAdmin = window.ParishCoreAdmin || {};
	window.ParishCoreAdmin.MassTimes = MassTimes;
})(window);
