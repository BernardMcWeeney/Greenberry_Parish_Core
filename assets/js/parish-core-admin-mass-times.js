/**
 * Parish Core Admin - Mass Times
 * Enhanced scheduler with 7-day grid and monthly calendar views
 */
(function (window) {
	'use strict';

	if (!window.ParishCoreAdmin) {
		return;
	}

	const {
		el,
		useState,
		useEffect,
		useCallback,
		useMemo,
		Fragment,
		Button,
		Notice,
		Modal,
		SelectControl,
		TextControl,
		TextareaControl,
		ToggleControl,
		Flex,
		FlexBlock,
		FlexItem,
		apiFetch,
		LoadingSpinner,
	} = window.ParishCoreAdmin;

	// =========================================================================
	// CONSTANTS & UTILITIES
	// =========================================================================
	const TYPE_COLORS = {
		mass: { bg: '#e8f4fc', border: '#2271b1', text: '#1a5a8e' },
		confession: { bg: '#f3e8f5', border: '#8c5cb5', text: '#6a4390' },
		adoration: { bg: '#fff8e1', border: '#dba617', text: '#9c7a0d' },
		rosary: { bg: '#e8f5e9', border: '#00a32a', text: '#007a1f' },
	};

	const WEEKDAYS = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

	/**
	 * Generate an array of 7 consecutive days starting from a given date.
	 * Each day object includes date, day_name, dayNum, is_today, isPast, and an empty occurrences array.
	 */
	function generateWeekDays(startDate) {
		const days = [];
		const today = new Date();
		today.setHours(0, 0, 0, 0);

		for (let i = 0; i < 7; i++) {
			const d = new Date(startDate + 'T12:00:00');
			d.setDate(d.getDate() + i);
			const dateStr = d.toISOString().split('T')[0];
			const dayOfWeek = d.getDay();

			days.push({
				date: dateStr,
				day_name: WEEKDAYS[dayOfWeek],
				dayNum: d.getDate(),
				is_today: d.toDateString() === today.toDateString(),
				isPast: d < today,
				occurrences: [],
			});
		}
		return days;
	}

	/**
	 * Merge API response days with a full 7-day structure.
	 * Ensures all 7 days are present even if some have no occurrences.
	 */
	function mergeWithFullWeek(startDate, apiDays) {
		const fullWeek = generateWeekDays(startDate);
		const apiByDate = {};

		// Index API days by date
		(apiDays || []).forEach(day => {
			apiByDate[day.date] = day;
		});

		// Merge: use API data if available, otherwise use empty day structure
		return fullWeek.map(day => {
			if (apiByDate[day.date]) {
				return {
					...day,
					...apiByDate[day.date],
					occurrences: apiByDate[day.date].occurrences || [],
				};
			}
			return day;
		});
	}
	const ORDINALS = [
		{ label: 'First', value: 'first' },
		{ label: 'Second', value: 'second' },
		{ label: 'Third', value: 'third' },
		{ label: 'Fourth', value: 'fourth' },
		{ label: 'Last', value: 'last' },
	];
	const MONTHS = [
		{ label: 'January', value: 1 }, { label: 'February', value: 2 }, { label: 'March', value: 3 },
		{ label: 'April', value: 4 }, { label: 'May', value: 5 }, { label: 'June', value: 6 },
		{ label: 'July', value: 7 }, { label: 'August', value: 8 }, { label: 'September', value: 9 },
		{ label: 'October', value: 10 }, { label: 'November', value: 11 }, { label: 'December', value: 12 },
	];

	function formatTime(time) {
		if (!time) return '';
		const [hours, mins] = time.split(':');
		const h = parseInt(hours, 10);
		const ampm = h >= 12 ? 'PM' : 'AM';
		const h12 = h % 12 || 12;
		return `${h12}:${mins} ${ampm}`;
	}

	function formatDateShort(dateStr) {
		const d = new Date(dateStr + 'T12:00:00');
		return d.toLocaleDateString('en-IE', { day: 'numeric', month: 'short' });
	}

	function getMonthDates(year, month) {
		const firstDay = new Date(year, month, 1);
		const lastDay = new Date(year, month + 1, 0);
		const startPad = firstDay.getDay();
		const dates = [];

		// Pad start with previous month's days
		for (let i = startPad - 1; i >= 0; i--) {
			const d = new Date(year, month, -i);
			dates.push({
				date: d.toISOString().split('T')[0],
				dayNum: d.getDate(),
				isCurrentMonth: false,
				isToday: d.toDateString() === new Date().toDateString(),
			});
		}

		// Current month days
		for (let day = 1; day <= lastDay.getDate(); day++) {
			const d = new Date(year, month, day);
			dates.push({
				date: d.toISOString().split('T')[0],
				dayNum: day,
				isCurrentMonth: true,
				isToday: d.toDateString() === new Date().toDateString(),
			});
		}

		// Pad end to complete grid
		const remaining = 42 - dates.length; // 6 rows * 7 days
		for (let i = 1; i <= remaining; i++) {
			const d = new Date(year, month + 1, i);
			dates.push({
				date: d.toISOString().split('T')[0],
				dayNum: i,
				isCurrentMonth: false,
				isToday: d.toDateString() === new Date().toDateString(),
			});
		}

		return dates;
	}

	// =========================================================================
	// COMPONENTS
	// =========================================================================

	/**
	 * Live Badge Component
	 */
	function LiveBadge() {
		return el('span', {
			className: 'live-badge',
			style: {
				background: '#d63638',
				color: '#fff',
				padding: '2px 6px',
				borderRadius: '3px',
				fontSize: '10px',
				fontWeight: '600',
				marginLeft: '4px',
			}
		}, 'LIVE');
	}

	/**
	 * Recurring Icon
	 */
	function RecurringIcon() {
		return el('span', {
			title: 'Recurring event',
			style: {
				color: '#2271b1',
				fontSize: '12px',
				marginLeft: '4px',
			}
		}, '↻');
	}

	/**
	 * Occurrence Card Component (for grid view)
	 */
	function OccurrenceCard({ occurrence, onClick }) {
		const colors = TYPE_COLORS[occurrence.type] || TYPE_COLORS.mass;

		return el('div', {
			className: 'occurrence-card',
			onClick: () => onClick(occurrence),
			style: {
				background: colors.bg,
				borderLeft: `4px solid ${colors.border}`,
				borderRadius: '4px',
				padding: '8px 10px',
				marginBottom: '6px',
				cursor: 'pointer',
				transition: 'transform 0.1s, box-shadow 0.1s',
			},
			onMouseEnter: (e) => {
				e.currentTarget.style.transform = 'translateX(2px)';
				e.currentTarget.style.boxShadow = '0 2px 8px rgba(0,0,0,0.1)';
			},
			onMouseLeave: (e) => {
				e.currentTarget.style.transform = 'none';
				e.currentTarget.style.boxShadow = 'none';
			}
		},
			el('div', { style: { display: 'flex', alignItems: 'center', gap: '4px', marginBottom: '2px' } },
				el('strong', { style: { fontSize: '14px', color: colors.text } }, formatTime(occurrence.time)),
				occurrence.is_livestreamed && el(LiveBadge),
				occurrence.is_recurring !== false && el(RecurringIcon)
			),
			el('div', { style: { fontSize: '12px', color: '#444', fontWeight: '500' } }, occurrence.type_label || occurrence.type),
			el('div', { style: { fontSize: '11px', color: '#666', marginTop: '2px' } }, occurrence.church_name)
		);
	}

	/**
	 * 7-Day Grid View Component
	 */
	function SevenDayGrid({ days, onAddClick, onOccurrenceClick }) {
		return el('div', { className: 'mass-times-7day-grid' },
			days.map(day => el('div', {
				key: day.date,
				className: `grid-day-column ${day.is_today ? 'is-today' : ''} ${day.isPast ? 'is-past' : ''}`,
			},
				el('div', { className: 'day-header' },
					el('span', { className: 'day-name' }, day.day_name),
					el('span', { className: 'day-number' }, day.dayNum),
					day.is_today && el('span', { className: 'today-badge' }, 'Today')
				),
				el('div', { className: 'day-events' },
					(day.occurrences || []).length > 0
						? day.occurrences.map((occ, idx) =>
							el(OccurrenceCard, {
								key: `${occ.post_id}-${idx}`,
								occurrence: occ,
								onClick: onOccurrenceClick,
							})
						)
						: el('div', { className: 'no-events' }, 'No scheduled times')
				),
				el('div', { className: 'day-footer' },
					el(Button, {
						isSmall: true,
						variant: 'secondary',
						className: 'add-time-btn',
						onClick: () => onAddClick(day.date),
					}, '+ Add Time')
				)
			))
		);
	}

	/**
	 * Monthly Calendar View Component
	 */
	function MonthlyCalendarView({ year, month, occurrencesByDate, onDateClick, onOccurrenceClick }) {
		const dates = useMemo(() => getMonthDates(year, month), [year, month]);
		const weeks = [];
		for (let i = 0; i < dates.length; i += 7) {
			weeks.push(dates.slice(i, i + 7));
		}

		return el('div', { className: 'month-calendar' },
			el('div', { className: 'calendar-header' },
				['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].map(day =>
					el('div', { key: day, className: 'calendar-day-header' }, day)
				)
			),
			el('div', { className: 'calendar-body' },
				weeks.map((week, weekIdx) =>
					el('div', { key: weekIdx, className: 'calendar-week' },
						week.map(day => {
							const dayOccurrences = occurrencesByDate[day.date] || [];
							return el('div', {
								key: day.date,
								className: `calendar-day ${!day.isCurrentMonth ? 'other-month' : ''} ${day.isToday ? 'is-today' : ''}`,
								onClick: () => onDateClick(day.date),
							},
								el('div', { className: 'day-number' }, day.dayNum),
								el('div', { className: 'day-occurrences' },
									dayOccurrences.slice(0, 3).map((occ, idx) => {
										const colors = TYPE_COLORS[occ.type] || TYPE_COLORS.mass;
										return el('div', {
											key: idx,
											className: 'calendar-occurrence',
											style: { background: colors.border, color: '#fff' },
											onClick: (e) => { e.stopPropagation(); onOccurrenceClick(occ); },
										}, formatTime(occ.time));
									}),
									dayOccurrences.length > 3 && el('div', { className: 'more-count' }, `+${dayOccurrences.length - 3} more`)
								)
							);
						})
					)
				)
			)
		);
	}

	/**
	 * Recurrence Builder Component
	 */
	function RecurrenceBuilder({ recurrence, onChange }) {
		const rec = recurrence || { type: 'weekly', days: [] };
		const updateField = (field, value) => onChange({ ...rec, [field]: value });

		const recurrenceTypes = [
			{ label: 'Daily', value: 'daily' },
			{ label: 'Weekly', value: 'weekly' },
			{ label: 'Every 2 Weeks', value: 'biweekly' },
			{ label: 'Monthly (by day)', value: 'monthly_day' },
			{ label: 'Monthly (e.g., First Friday)', value: 'monthly_ordinal' },
			{ label: 'Yearly', value: 'yearly' },
		];

		return el('div', { className: 'recurrence-builder' },
			el(SelectControl, {
				label: 'Repeat',
				value: rec.type || 'weekly',
				options: recurrenceTypes,
				onChange: v => updateField('type', v),
				__nextHasNoMarginBottom: true,
				__next40pxDefaultSize: true,
			}),

			// Weekly/Biweekly: Day selector
			(rec.type === 'weekly' || rec.type === 'biweekly') && el('div', { className: 'days-picker' },
				el('label', { style: { display: 'block', marginBottom: '8px', fontWeight: '500' } }, 'On days:'),
				el('div', { className: 'days-buttons' },
					WEEKDAYS.map(day => {
						const selected = (rec.days || []).includes(day);
						return el(Button, {
							key: day,
							isSmall: true,
							variant: selected ? 'primary' : 'secondary',
							style: selected ? {} : { background: '#f0f0f1' },
							onClick: () => {
								const current = rec.days || [];
								const updated = selected
									? current.filter(d => d !== day)
									: [...current, day];
								updateField('days', updated);
							}
						}, day.slice(0, 3));
					})
				)
			),

			// Monthly by day
			rec.type === 'monthly_day' && el(TextControl, {
				label: 'Day of month',
				type: 'number',
				min: 1,
				max: 31,
				value: rec.day_of_month || '',
				onChange: v => updateField('day_of_month', parseInt(v, 10)),
			}),

			// Monthly ordinal
			rec.type === 'monthly_ordinal' && el(Fragment, null,
				el(Flex, { gap: 3 },
					el(FlexBlock, null,
						el(SelectControl, {
							label: 'Week',
							value: rec.ordinal || 'first',
							options: ORDINALS,
							onChange: v => updateField('ordinal', v),
							__nextHasNoMarginBottom: true,
							__next40pxDefaultSize: true,
						})
					),
					el(FlexBlock, null,
						el(SelectControl, {
							label: 'Day',
							value: rec.ordinal_day || 'Friday',
							options: WEEKDAYS.map(d => ({ label: d, value: d })),
							onChange: v => updateField('ordinal_day', v),
							__nextHasNoMarginBottom: true,
							__next40pxDefaultSize: true,
						})
					)
				)
			),

			// Yearly
			rec.type === 'yearly' && el(Flex, { gap: 3 },
				el(FlexBlock, null,
					el(SelectControl, {
						label: 'Month',
						value: rec.month || 1,
						options: MONTHS,
						onChange: v => updateField('month', parseInt(v, 10)),
						__nextHasNoMarginBottom: true,
						__next40pxDefaultSize: true,
					})
				),
				el(FlexBlock, null,
					el(TextControl, {
						label: 'Day',
						type: 'number',
						min: 1,
						max: 31,
						value: rec.day_of_month || '',
						onChange: v => updateField('day_of_month', parseInt(v, 10)),
					})
				)
			),

			// End date
			el('div', { style: { marginTop: '16px' } },
				el(TextControl, {
					label: 'End date (optional)',
					help: 'Leave empty for no end date',
					type: 'date',
					value: rec.end_date || '',
					onChange: v => updateField('end_date', v),
				})
			)
		);
	}

	/**
	 * Exception Dates Component
	 */
	function ExceptionDates({ dates, onChange }) {
		const [newDate, setNewDate] = useState('');
		const sortedDates = [...(dates || [])].sort();

		const addDate = () => {
			if (newDate && !dates.includes(newDate)) {
				onChange([...dates, newDate]);
				setNewDate('');
			}
		};

		const removeDate = (dateToRemove) => {
			onChange(dates.filter(d => d !== dateToRemove));
		};

		return el('div', { className: 'exception-dates' },
			el('label', { style: { display: 'block', marginBottom: '8px', fontWeight: '500' } }, 'Skip dates (exceptions):'),
			el('p', { style: { fontSize: '12px', color: '#666', margin: '0 0 12px' } },
				'Add specific dates when this recurring event should NOT occur.'
			),
			sortedDates.length > 0 && el('div', { className: 'exception-list', style: { marginBottom: '12px' } },
				sortedDates.map(date => el('div', {
					key: date,
					style: {
						display: 'inline-flex',
						alignItems: 'center',
						gap: '6px',
						background: '#f0f0f1',
						padding: '4px 8px',
						borderRadius: '4px',
						marginRight: '8px',
						marginBottom: '8px',
						fontSize: '13px',
					}
				},
					formatDateShort(date),
					el(Button, {
						isSmall: true,
						isDestructive: true,
						onClick: () => removeDate(date),
						style: { minWidth: '20px', height: '20px', padding: 0 },
					}, '×')
				))
			),
			el(Flex, { gap: 2, align: 'flex-end' },
				el(FlexBlock, null,
					el(TextControl, {
						type: 'date',
						value: newDate,
						onChange: setNewDate,
						__next40pxDefaultSize: true,
					})
				),
				el(FlexItem, null,
					el(Button, {
						variant: 'secondary',
						onClick: addDate,
						disabled: !newDate,
					}, 'Add Exception')
				)
			)
		);
	}

	/**
	 * Delete Confirmation Dialog for Recurring Events
	 */
	function DeleteConfirmDialog({ massTime, onDeleteSingle, onDeleteSeries, onCancel }) {
		const isRecurring = massTime && massTime.is_recurring;

		return el(Modal, {
			title: 'Delete Mass Time',
			onRequestClose: onCancel,
			className: 'parish-modal delete-confirm-modal',
			style: { maxWidth: '400px' },
		},
			el('p', { style: { marginBottom: '16px' } },
				isRecurring
					? 'This is a recurring event. What would you like to delete?'
					: 'Are you sure you want to delete this Mass Time?'
			),
			isRecurring && el('div', { style: { display: 'flex', flexDirection: 'column', gap: '12px', marginBottom: '16px' } },
				el(Button, {
					variant: 'secondary',
					onClick: onDeleteSingle,
					style: { justifyContent: 'flex-start' },
				}, 'Only this occurrence (add exception date)'),
				el(Button, {
					isDestructive: true,
					onClick: onDeleteSeries,
					style: { justifyContent: 'flex-start' },
				}, 'Delete entire recurring series')
			),
			!isRecurring && el('div', { style: { display: 'flex', gap: '8px', justifyContent: 'flex-end' } },
				el(Button, { variant: 'secondary', onClick: onCancel }, 'Cancel'),
				el(Button, { isDestructive: true, onClick: onDeleteSeries }, 'Delete')
			),
			isRecurring && el('div', { style: { display: 'flex', justifyContent: 'flex-end', paddingTop: '12px', borderTop: '1px solid #ddd' } },
				el(Button, { variant: 'secondary', onClick: onCancel }, 'Cancel')
			)
		);
	}

	/**
	 * Mass Time Form Modal
	 */
	function MassTimeModal({ massTime, churches, eventTypes, isNew, onSave, onDelete, onDeleteSingle, onClose }) {
		const [form, setForm] = useState(massTime);
		const [saving, setSaving] = useState(false);
		const [activeTab, setActiveTab] = useState('basic');
		const [showDeleteConfirm, setShowDeleteConfirm] = useState(false);

		const upd = (k, v) => setForm(prev => ({ ...prev, [k]: v }));

		const churchOptions = [{ label: 'All Churches', value: 0 }].concat(
			churches.map(c => ({ label: c.title, value: c.id }))
		);

		const typeOptions = Object.keys(eventTypes).map(k => ({
			label: eventTypes[k],
			value: k,
		}));

		const handleSave = () => {
			setSaving(true);
			onSave(form);
		};

		const tabs = [
			{ id: 'basic', label: 'Basic Info' },
			{ id: 'schedule', label: 'Schedule' },
			{ id: 'livestream', label: 'Livestream' },
		];

		return el(Modal, {
			title: isNew ? 'Add Mass Time' : 'Edit Mass Time',
			onRequestClose: onClose,
			className: 'parish-modal mass-time-modal',
			style: { maxWidth: '600px' },
		},
			// Tabs
			el('div', { className: 'modal-tabs', style: { display: 'flex', gap: '4px', marginBottom: '20px', borderBottom: '1px solid #ddd', paddingBottom: '12px' } },
				tabs.map(tab => el(Button, {
					key: tab.id,
					variant: activeTab === tab.id ? 'primary' : 'secondary',
					isSmall: true,
					onClick: () => setActiveTab(tab.id),
				}, tab.label))
			),

			el('div', { className: 'modal-form' },
				// Basic Info Tab
				activeTab === 'basic' && el(Fragment, null,
					el(SelectControl, {
						label: 'Church',
						value: form.church_id || 0,
						options: churchOptions,
						onChange: v => upd('church_id', parseInt(v, 10)),
						__nextHasNoMarginBottom: true,
						__next40pxDefaultSize: true,
					}),
					el(SelectControl, {
						label: 'Type',
						value: form.liturgical_type || 'mass',
						options: typeOptions,
						onChange: v => upd('liturgical_type', v),
						__nextHasNoMarginBottom: true,
						__next40pxDefaultSize: true,
					}),
					el(TextControl, {
						label: 'Title (optional)',
						help: 'Auto-generated if left blank',
						value: form.title || '',
						onChange: v => upd('title', v),
					}),
					el(Flex, { gap: 3 },
						el(FlexBlock, null,
							el(TextControl, {
								label: 'Date',
								type: 'date',
								value: (form.start_datetime || '').split('T')[0],
								onChange: v => {
									const time = (form.start_datetime || '').split('T')[1] || '10:00';
									upd('start_datetime', v + 'T' + time);
								},
							})
						),
						el(FlexBlock, null,
							el(TextControl, {
								label: 'Time',
								type: 'time',
								value: (form.start_datetime || '').split('T')[1] || '',
								onChange: v => {
									const date = (form.start_datetime || '').split('T')[0] || new Date().toISOString().split('T')[0];
									upd('start_datetime', date + 'T' + v);
								},
							})
						)
					),
					el(TextControl, {
						label: 'Duration (minutes)',
						type: 'number',
						min: 15,
						max: 240,
						value: form.duration_minutes || 60,
						onChange: v => upd('duration_minutes', parseInt(v, 10)),
					}),
					el(ToggleControl, {
						label: 'Active',
						help: 'Only active times appear on the frontend',
						checked: form.is_active !== false,
						onChange: v => upd('is_active', v),
					}),
					el(TextareaControl, {
						label: 'Notes',
						help: 'Optional notes (e.g., Latin Mass, Vigil)',
						value: form.notes || '',
						rows: 2,
						onChange: v => upd('notes', v),
					})
				),

				// Schedule Tab
				activeTab === 'schedule' && el(Fragment, null,
					el(ToggleControl, {
						label: 'Special Event (one-off)',
						help: 'Mark as a special one-time event',
						checked: form.is_special_event || false,
						onChange: v => upd('is_special_event', v),
					}),
					!form.is_special_event && el(Fragment, null,
						el(ToggleControl, {
							label: 'Recurring',
							checked: form.is_recurring || false,
							onChange: v => upd('is_recurring', v),
						}),
						form.is_recurring && el(Fragment, null,
							el(RecurrenceBuilder, {
								recurrence: form.recurrence || {},
								onChange: rec => upd('recurrence', rec),
							}),
							el('div', { style: { marginTop: '16px' } },
								el(ExceptionDates, {
									dates: form.exception_dates || [],
									onChange: dates => upd('exception_dates', dates),
								})
							)
						)
					),
					!form.is_recurring && !form.is_special_event && el('div', {
						style: {
							background: '#f6f7f7',
							padding: '16px',
							borderRadius: '6px',
							marginTop: '12px',
						}
					},
						el('p', { style: { margin: 0, color: '#666' } },
							'This is a single occurrence event. Enable "Recurring" to make it repeat, or "Special Event" to mark it as a one-off occasion.'
						)
					)
				),

				// Livestream Tab
				activeTab === 'livestream' && el(Fragment, null,
					el(ToggleControl, {
						label: 'Livestreamed',
						help: 'This event is available via livestream',
						checked: form.is_livestreamed || false,
						onChange: v => upd('is_livestreamed', v),
					}),
					form.is_livestreamed && el(Fragment, null,
						el(TextControl, {
							label: 'Livestream URL',
							type: 'url',
							placeholder: 'https://youtube.com/watch?v=...',
							value: form.livestream_url || '',
							onChange: v => upd('livestream_url', v),
						}),
						el(TextareaControl, {
							label: 'Embed Code (optional)',
							help: 'Alternative to URL - paste embed code from YouTube/Vimeo',
							value: form.livestream_embed || '',
							rows: 3,
							onChange: v => upd('livestream_embed', v),
						})
					)
				)
			),

			// Actions
			el('div', { className: 'modal-actions', style: { display: 'flex', justifyContent: 'space-between', paddingTop: '16px', borderTop: '1px solid #ddd', marginTop: '16px' } },
				!isNew && form && form.id ? el(Button, {
					isDestructive: true,
					onClick: () => setShowDeleteConfirm(true),
				}, 'Delete') : el('div'),
				el(Flex, { gap: 2 },
					el(Button, { variant: 'secondary', onClick: onClose }, 'Cancel'),
					el(Button, { variant: 'primary', isBusy: saving, onClick: handleSave },
						saving ? 'Saving...' : 'Save'
					)
				)
			),
			// Delete confirmation dialog
			showDeleteConfirm && el(DeleteConfirmDialog, {
				massTime: form,
				onDeleteSingle: () => {
					setShowDeleteConfirm(false);
					if (form && form.id) {
						onDeleteSingle(form.id, form.start_datetime ? form.start_datetime.split('T')[0] : null);
					}
				},
				onDeleteSeries: () => {
					setShowDeleteConfirm(false);
					if (form && form.id) {
						onDelete(form.id);
					}
				},
				onCancel: () => setShowDeleteConfirm(false),
			})
		);
	}

	/**
	 * Main Mass Times Component
	 */
	function MassTimes() {
		// State
		const [view, setView] = useState('week'); // 'week' or 'month'
		const [occurrences, setOccurrences] = useState([]);
		const [massTimePosts, setMassTimePosts] = useState([]);
		const [churches, setChurches] = useState([]);
		const [eventTypes, setEventTypes] = useState({});
		const [loading, setLoading] = useState(true);
		const [notice, setNotice] = useState(null);
		const [weekStart, setWeekStart] = useState(new Date().toISOString().split('T')[0]);
		const [monthYear, setMonthYear] = useState({ year: new Date().getFullYear(), month: new Date().getMonth() });
		const [editing, setEditing] = useState(null);
		const [editingOccurrenceDate, setEditingOccurrenceDate] = useState(null); // Track which occurrence date we're editing
		const [isNew, setIsNew] = useState(false);
		const [filterChurch, setFilterChurch] = useState('');
		const [filterType, setFilterType] = useState('');
		const [showInactive, setShowInactive] = useState(false);

		// Computed values
		const weekEndDate = useMemo(() => {
			const d = new Date(weekStart);
			d.setDate(d.getDate() + 6);
			return d.toISOString().split('T')[0];
		}, [weekStart]);

		const monthName = useMemo(() => {
			const d = new Date(monthYear.year, monthYear.month, 1);
			return d.toLocaleDateString('en-IE', { month: 'long', year: 'numeric' });
		}, [monthYear]);

		const occurrencesByDate = useMemo(() => {
			const byDate = {};
			occurrences.forEach(day => {
				byDate[day.date] = day.occurrences || [];
			});
			return byDate;
		}, [occurrences]);

		// Data loading
		const loadData = useCallback(() => {
			setLoading(true);
			let from, to;

			if (view === 'week') {
				from = weekStart;
				to = weekEndDate;
			} else {
				const firstDay = new Date(monthYear.year, monthYear.month, 1);
				const lastDay = new Date(monthYear.year, monthYear.month + 1, 0);
				// Extend range to include padding days
				firstDay.setDate(firstDay.getDate() - firstDay.getDay());
				lastDay.setDate(lastDay.getDate() + (6 - lastDay.getDay()));
				from = firstDay.toISOString().split('T')[0];
				to = lastDay.toISOString().split('T')[0];
			}

			let url = `/parish/v1/mass-times/occurrences?from=${from}&to=${to}&active_only=${!showInactive}`;
			if (filterChurch) url += `&church_id=${filterChurch}`;
			if (filterType) url += `&type=${filterType}`;

			Promise.all([
				apiFetch({ path: url }),
				apiFetch({ path: '/parish/v1/mass-times' }),
			])
				.then(([occData, mtData]) => {
					// For week view, merge with full 7-day structure to ensure all days are shown
					const mergedDays = view === 'week'
						? mergeWithFullWeek(weekStart, occData.days || [])
						: occData.days || [];
					setOccurrences(mergedDays);
					setMassTimePosts(mtData.mass_times || []);
					setChurches(mtData.churches || []);
					setEventTypes(mtData.event_types || {});
					setLoading(false);
				})
				.catch(err => {
					setNotice({ type: 'error', message: err.message });
					setLoading(false);
				});
		}, [weekStart, weekEndDate, monthYear, view, filterChurch, filterType, showInactive]);

		useEffect(() => {
			loadData();
		}, [loadData]);

		// Navigation
		const prevWeek = () => {
			const d = new Date(weekStart);
			d.setDate(d.getDate() - 7);
			setWeekStart(d.toISOString().split('T')[0]);
		};

		const nextWeek = () => {
			const d = new Date(weekStart);
			d.setDate(d.getDate() + 7);
			setWeekStart(d.toISOString().split('T')[0]);
		};

		const prevMonth = () => {
			setMonthYear(prev => {
				const newMonth = prev.month === 0 ? 11 : prev.month - 1;
				const newYear = prev.month === 0 ? prev.year - 1 : prev.year;
				return { year: newYear, month: newMonth };
			});
		};

		const nextMonth = () => {
			setMonthYear(prev => {
				const newMonth = prev.month === 11 ? 0 : prev.month + 1;
				const newYear = prev.month === 11 ? prev.year + 1 : prev.year;
				return { year: newYear, month: newMonth };
			});
		};

		const goToday = () => {
			const today = new Date();
			setWeekStart(today.toISOString().split('T')[0]);
			setMonthYear({ year: today.getFullYear(), month: today.getMonth() });
		};

		// Handlers
		const handleAdd = (date) => {
			setIsNew(true);
			setEditing({
				church_id: filterChurch ? parseInt(filterChurch, 10) : 0,
				liturgical_type: filterType || 'mass',
				start_datetime: (date || weekStart) + 'T10:00',
				duration_minutes: 60,
				is_active: true,
				is_special_event: false,
				is_recurring: false,
				recurrence: { type: 'weekly', days: [] },
				exception_dates: [],
				is_livestreamed: false,
			});
		};

		const handleEdit = (occ) => {
			const post = massTimePosts.find(p => p.id === occ.post_id);
			if (post) {
				setIsNew(false);
				setEditing(post);
				setEditingOccurrenceDate(occ.date); // Track which occurrence date we clicked on
			} else {
				setNotice({ type: 'error', message: 'Could not find the Mass Time post. Try refreshing the page.' });
			}
		};

		const handleSave = (data) => {
			const method = isNew ? 'POST' : 'PUT';
			const path = isNew ? '/parish/v1/mass-times' : `/parish/v1/mass-times/${data.id}`;

			apiFetch({ path, method, data })
				.then(() => {
					setEditing(null);
					setEditingOccurrenceDate(null);
					setNotice({
						type: 'success',
						message: isNew ? 'Mass Time created!' : 'Mass Time updated!',
					});
					// Small delay to ensure server cache is cleared before refetching
					setTimeout(() => loadData(), 200);
				})
				.catch(err => {
					setNotice({ type: 'error', message: err.message });
				});
		};

		const handleDelete = (id) => {
			if (!id) {
				setNotice({ type: 'error', message: 'Cannot delete: Invalid ID.' });
				return;
			}
			apiFetch({ path: `/parish/v1/mass-times/${id}`, method: 'DELETE' })
				.then(() => {
					setEditing(null);
					setEditingOccurrenceDate(null);
					setNotice({ type: 'success', message: 'Mass Time deleted.' });
					// Small delay to ensure server cache is cleared before refetching
					setTimeout(() => loadData(), 200);
				})
				.catch(err => {
					setNotice({ type: 'error', message: err.message });
				});
		};

		// Delete single occurrence by adding an exception date
		const handleDeleteSingle = (id, occurrenceDate) => {
			if (!id) {
				setNotice({ type: 'error', message: 'Cannot delete occurrence: Invalid ID.' });
				return;
			}
			// Find the post and add the occurrence date to exception_dates
			const post = massTimePosts.find(p => p.id === id);
			if (!post) {
				setNotice({ type: 'error', message: 'Could not find the Mass Time post.' });
				return;
			}

			// Use the tracked occurrence date or fall back to the passed date
			const dateToExclude = editingOccurrenceDate || occurrenceDate;
			if (!dateToExclude) {
				setNotice({ type: 'error', message: 'Could not determine which occurrence to delete.' });
				return;
			}

			const currentExceptions = post.exception_dates || [];
			if (currentExceptions.includes(dateToExclude)) {
				setNotice({ type: 'warning', message: 'This date is already excluded.' });
				return;
			}

			const updatedExceptions = [...currentExceptions, dateToExclude];

			apiFetch({
				path: `/parish/v1/mass-times/${id}`,
				method: 'PUT',
				data: { ...post, exception_dates: updatedExceptions },
			})
				.then(() => {
					setEditing(null);
					setEditingOccurrenceDate(null);
					setNotice({ type: 'success', message: `Occurrence on ${dateToExclude} has been removed from the series.` });
					// Small delay to ensure server cache is cleared before refetching
					setTimeout(() => loadData(), 200);
				})
				.catch(err => {
					setNotice({ type: 'error', message: err.message });
				});
		};

		// Options
		const churchOptions = [{ label: 'All Churches', value: '' }].concat(
			churches.map(c => ({ label: c.title, value: String(c.id) }))
		);

		const typeOptions = [{ label: 'All Types', value: '' }].concat(
			Object.keys(eventTypes).map(k => ({ label: eventTypes[k], value: k }))
		);

		// Render loading state
		if (loading && occurrences.length === 0) {
			return el(LoadingSpinner, { text: 'Loading Mass Times...' });
		}

		return el('div', { className: 'parish-mass-times-page' },
			// Notice
			notice && el(Notice, {
				status: notice.type,
				isDismissible: true,
				onRemove: () => setNotice(null),
			}, notice.message),

			// Header
			el('div', { className: 'page-header' },
				el('div', null,
					el('h2', { style: { margin: 0 } }, 'Mass Times'),
					el('p', { style: { margin: '4px 0 0', color: '#666' } },
						`Configure the weekly mass and service schedule. Use [parish_today_widget] for today's times or [parish_church_schedule] for weekly schedules.`
					)
				),
				el('div', { style: { fontSize: '13px', color: '#2271b1' } },
					`${churches.length} church${churches.length !== 1 ? 'es' : ''} available`
				)
			),

			// Toolbar
			el('div', { className: 'mass-times-toolbar', style: {
				display: 'flex',
				flexWrap: 'wrap',
				gap: '12px',
				alignItems: 'center',
				padding: '12px 16px',
				background: '#f6f7f7',
				borderRadius: '6px',
				marginBottom: '20px',
			}},
				// View switcher
				el('div', { className: 'view-switcher', style: { display: 'flex', gap: '4px' } },
					el(Button, {
						isSmall: true,
						variant: view === 'week' ? 'primary' : 'secondary',
						onClick: () => setView('week'),
					}, '7-Day'),
					el(Button, {
						isSmall: true,
						variant: view === 'month' ? 'primary' : 'secondary',
						onClick: () => setView('month'),
					}, 'Month')
				),

				// Navigation
				el('div', { className: 'nav-controls', style: { display: 'flex', alignItems: 'center', gap: '8px' } },
					el(Button, { isSmall: true, onClick: view === 'week' ? prevWeek : prevMonth }, '←'),
					el('span', { style: { fontWeight: '500', minWidth: '200px', textAlign: 'center' } },
						view === 'week'
							? `${formatDateShort(weekStart)} — ${formatDateShort(weekEndDate)}`
							: monthName
					),
					el(Button, { isSmall: true, onClick: view === 'week' ? nextWeek : nextMonth }, '→'),
					el(Button, { isSmall: true, variant: 'tertiary', onClick: goToday }, 'Today')
				),

				// Spacer
				el('div', { style: { flex: 1 } }),

				// Filters
				el(SelectControl, {
					value: filterChurch,
					options: churchOptions,
					onChange: setFilterChurch,
					__nextHasNoMarginBottom: true,
					__next40pxDefaultSize: true,
					style: { minWidth: '160px' },
				}),
				el(SelectControl, {
					value: filterType,
					options: typeOptions,
					onChange: setFilterType,
					__nextHasNoMarginBottom: true,
					__next40pxDefaultSize: true,
					style: { minWidth: '120px' },
				}),
				el(ToggleControl, {
					label: 'Show inactive',
					checked: showInactive,
					onChange: setShowInactive,
					__nextHasNoMarginBottom: true,
				}),
				el(Button, {
					variant: 'primary',
					onClick: () => handleAdd(),
				}, '+ Add Mass Time')
			),

			// View
			view === 'week'
				? el(SevenDayGrid, {
					days: occurrences,
					onAddClick: handleAdd,
					onOccurrenceClick: handleEdit,
				})
				: el(MonthlyCalendarView, {
					year: monthYear.year,
					month: monthYear.month,
					occurrencesByDate,
					onDateClick: handleAdd,
					onOccurrenceClick: handleEdit,
				}),

			// Modal
			editing && el(MassTimeModal, {
				massTime: editing,
				churches,
				eventTypes,
				isNew,
				onSave: handleSave,
				onDelete: handleDelete,
				onDeleteSingle: handleDeleteSingle,
				onClose: () => {
					setEditing(null);
					setEditingOccurrenceDate(null);
				},
			})
		);
	}

	// Register component
	window.ParishCoreAdmin = window.ParishCoreAdmin || {};
	Object.assign(window.ParishCoreAdmin, {
		MassTimes: MassTimes,
	});
})(window);
