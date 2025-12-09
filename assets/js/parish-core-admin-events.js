/**
 * Parish Core Admin - Events Calendar
 */
(function (window) {
	'use strict';

	const {
		el,
		useState,
		useEffect,
		Button,
		Notice,
		Modal,
		SelectControl,
		TextControl,
		TextareaControl,
		Flex,
		apiFetch,
		LoadingSpinner,
		generateId,
	} = window.ParishCoreAdmin;

	function MonthView(props) {
		const year = props.year;
		const month = props.month;
		const events = props.events || [];
		const onDayClick = props.onDayClick;
		const onEventClick = props.onEventClick;

		const firstDay = new Date(year, month, 1);
		const lastDay = new Date(year, month + 1, 0);
		const startDay = firstDay.getDay() || 7;
		const daysInMonth = lastDay.getDate();

		var weeks = [];
		var week = [];

		for (var i = 1; i < startDay; i++) week.push(null);
		for (var day = 1; day <= daysInMonth; day++) {
			week.push(day);
			if (week.length === 7) {
				weeks.push(week);
				week = [];
			}
		}
		if (week.length > 0) {
			while (week.length < 7) week.push(null);
			weeks.push(week);
		}

		const getEventsForDay = function (d) {
			if (!d) return [];
			var ds =
				year +
				'-' +
				String(month + 1).padStart(2, '0') +
				'-' +
				String(d).padStart(2, '0');
			return events.filter(function (e) {
				return e.date === ds;
			});
		};

		const today = new Date();

		return el(
			'div',
			{ className: 'month-view' },
			el(
				'div',
				{ className: 'calendar-header' },
				['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'].map(function (d) {
					return el('div', { key: d, className: 'calendar-day-header' }, d);
				})
			),
			el(
				'div',
				{ className: 'calendar-body' },
				weeks.map(function (wk, wi) {
					return el(
						'div',
						{ key: wi, className: 'calendar-week' },
						wk.map(function (d, di) {
							var dayEvents = getEventsForDay(d);
							var isToday =
								d &&
								today.getFullYear() === year &&
								today.getMonth() === month &&
								today.getDate() === d;

							return el(
								'div',
								{
									key: di,
									className:
										'calendar-day' +
										(d ? '' : ' empty') +
										(isToday ? ' today' : ''),
									onClick: d
										? function () {
												onDayClick(
													year +
														'-' +
														String(month + 1).padStart(2, '0') +
														'-' +
														String(d).padStart(2, '0')
												);
										  }
										: null,
								},
								d && el('span', { className: 'day-number' }, d),
								dayEvents.slice(0, 3).map(function (evt) {
									return el(
										'div',
										{
											key: evt.id,
											className: 'calendar-event',
											style: { background: evt.color },
											onClick: function (e) {
												e.stopPropagation();
												onEventClick(evt);
											},
										},
										evt.title || 'Event'
									);
								}),
								dayEvents.length > 3 &&
									el(
										'div',
										{ className: 'more-events' },
										'+' + (dayEvents.length - 3)
									)
							);
						})
					);
				})
			)
		);
	}

	function EventModal(props) {
		const event = props.event;
		const onSave = props.onSave;
		const onDelete = props.onDelete;
		const onClose = props.onClose;

		const [form, setForm] = useState(event);
		const upd = function (k, v) {
			setForm(function (p) {
				return Object.assign({}, p, { [k]: v });
			});
		};

		const eventTypes = [
			{ label: 'Parish Event', value: 'parish' },
			{ label: 'Sacrament', value: 'sacrament' },
			{ label: 'Feast Day', value: 'feast' },
			{ label: 'Meeting', value: 'meeting' },
		];
		const colors = [
			{ label: 'Blue', value: '#2271b1' },
			{ label: 'Green', value: '#00a32a' },
			{ label: 'Red', value: '#d63638' },
			{ label: 'Purple', value: '#8c5cb5' },
			{ label: 'Orange', value: '#dba617' },
		];

		return el(
			Modal,
			{ title: 'Event', onRequestClose: onClose, className: 'parish-modal' },
			el(
				'div',
				{ className: 'modal-form' },
				el(TextControl, {
					label: 'Title',
					value: form.title,
					onChange: function (v) {
						upd('title', v);
					},
				}),
				el(TextControl, {
					label: 'Date',
					type: 'date',
					value: form.date,
					onChange: function (v) {
						upd('date', v);
					},
				}),
				el(TextControl, {
					label: 'Time',
					type: 'time',
					value: form.time,
					onChange: function (v) {
						upd('time', v);
					},
				}),
				el(SelectControl, {
					label: 'Type',
					value: form.event_type,
					options: eventTypes,
					onChange: function (v) {
						upd('event_type', v);
					},
				}),
				el(SelectControl, {
					label: 'Color',
					value: form.color,
					options: colors,
					onChange: function (v) {
						upd('color', v);
					},
				}),
				el(TextControl, {
					label: 'Location',
					value: form.location,
					onChange: function (v) {
						upd('location', v);
					},
				}),
				el(TextareaControl, {
					label: 'Description',
					value: form.description,
					rows: 2,
					onChange: function (v) {
						upd('description', v);
					},
				})
			),
			el(
				'div',
				{ className: 'modal-actions' },
				el(
					Flex,
					{ justify: 'space-between' },
					el(
						Button,
						{ isDestructive: true, onClick: onDelete },
						'Delete'
					),
					el(
						Flex,
						{ gap: 2 },
						el(
							Button,
							{ isSecondary: true, onClick: onClose },
							'Cancel'
						),
						el(
							Button,
							{
								isPrimary: true,
								onClick: function () {
									onSave(form);
								},
							},
							'Save'
						)
					)
				)
			)
		);
	}

	function EventsCalendar() {
		const [events, setEvents] = useState([]);
		const [loading, setLoading] = useState(true);
		const [saving, setSaving] = useState(false);
		const [notice, setNotice] = useState(null);
		const [viewDate, setViewDate] = useState(new Date());
		const [editing, setEditing] = useState(null);

		useEffect(function () {
			apiFetch({ path: '/parish/v1/events' })
				.then(function (res) {
					setEvents(res.events || []);
					setLoading(false);
				})
				.catch(function () {
					setLoading(false);
				});
		}, []);

		const save = function () {
			setSaving(true);
			apiFetch({
				path: '/parish/v1/events',
				method: 'POST',
				data: { events: events },
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

		const add = function (date) {
			setEditing({
				id: generateId(),
				title: '',
				date: date || new Date().toISOString().split('T')[0],
				time: '',
				location: '',
				description: '',
				event_type: 'parish',
				color: '#2271b1',
			});
		};

		const saveEvent = function (evt) {
			const idx = events.findIndex(function (e) {
				return e.id === evt.id;
			});
			if (idx >= 0) {
				const u = events.slice();
				u[idx] = evt;
				setEvents(u);
			} else {
				setEvents(events.concat([evt]));
			}
			setEditing(null);
		};

		const deleteEvent = function (id) {
			setEvents(
				events.filter(function (e) {
					return e.id !== id;
				})
			);
		};

		if (loading) return el(LoadingSpinner, { text: 'Loading...' });

		const year = viewDate.getFullYear();
		const month = viewDate.getMonth();

		return el(
			'div',
			{ className: 'parish-events-page' },
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
				'div',
				{ className: 'page-header' },
				el(
					'div',
					{ className: 'calendar-nav' },
					el(
						Button,
						{
							isSmall: true,
							onClick: function () {
								var d = new Date(viewDate);
								d.setMonth(d.getMonth() - 1);
								setViewDate(d);
							},
						},
						'←'
					),
					el(
						'span',
						{ className: 'current-month' },
						viewDate.toLocaleDateString('en-IE', {
							month: 'long',
							year: 'numeric',
						})
					),
					el(
						Button,
						{
							isSmall: true,
							onClick: function () {
								var d = new Date(viewDate);
								d.setMonth(d.getMonth() + 1);
								setViewDate(d);
							},
						},
						'→'
					),
					el(
						Button,
						{
							isSmall: true,
							isSecondary: true,
							onClick: function () {
								setViewDate(new Date());
							},
						},
						'Today'
					)
				),
				el(
					Button,
					{ isPrimary: true, onClick: function () { add(); } },
					'+ Add Event'
				)
			),
			el(MonthView, {
				year: year,
				month: month,
				events: events,
				onDayClick: add,
				onEventClick: setEditing,
			}),
			editing &&
				el(EventModal, {
					event: editing,
					onSave: saveEvent,
					onDelete: function () {
						deleteEvent(editing.id);
						setEditing(null);
					},
					onClose: function () {
						setEditing(null);
					},
				}),
			el(
				'div',
				{ className: 'parish-save-bar' },
				el(
					Button,
					{ isPrimary: true, isBusy: saving, onClick: save },
					saving ? 'Saving...' : 'Save All'
				)
			)
		);
	}

	window.ParishCoreAdmin = window.ParishCoreAdmin || {};
	Object.assign(window.ParishCoreAdmin, {
		EventsCalendar,
	});
})(window);
