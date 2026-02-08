/**
 * Parish Core Admin - Events Calendar (CPT-based)
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
		CheckboxControl,
		Flex,
		apiFetch,
		LoadingSpinner,
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
		const churches = props.churches;
		const taxonomies = props.taxonomies;
		const onSave = props.onSave;
		const onDelete = props.onDelete;
		const onClose = props.onClose;

		const [form, setForm] = useState(event);
		const [saving, setSaving] = useState(false);

		const upd = function (k, v) {
			setForm(function (p) {
				return Object.assign({}, p, { [k]: v });
			});
		};

		const handleSave = function () {
			setSaving(true);
			onSave(form).finally(function () {
				setSaving(false);
			});
		};

		const handleDelete = function () {
			if (confirm('Are you sure you want to delete this event?')) {
				setSaving(true);
				onDelete(form.id).finally(function () {
					setSaving(false);
				});
			}
		};

		const colors = [
			{ label: 'Parish Blue', value: '#609fae' },
			{ label: 'Blue', value: '#2271b1' },
			{ label: 'Green', value: '#00a32a' },
			{ label: 'Red', value: '#d63638' },
			{ label: 'Purple', value: '#8c5cb5' },
			{ label: 'Orange', value: '#dba617' },
			{ label: 'Gold', value: '#FFD700' },
		];

		const churchOptions = [{ label: 'All Churches', value: 0 }].concat(
			(churches || []).map(function (c) {
				return { label: c.title, value: c.id };
			})
		);

		const sacramentOptions = [{ label: 'None', value: '' }].concat(
			(taxonomies.sacraments || []).map(function (t) {
				return { label: t.name, value: t.slug };
			})
		);

		const typeOptions = [{ label: 'None', value: '' }].concat(
			(taxonomies.types || []).map(function (t) {
				return { label: t.name, value: t.slug };
			})
		);

		return el(
			Modal,
			{ title: form.id ? 'Edit Event' : 'New Event', onRequestClose: onClose, className: 'parish-modal parish-event-modal' },
			el(
				'div',
				{ className: 'modal-form', style: { maxHeight: '70vh', overflowY: 'auto' } },
				el(TextControl, {
					label: 'Event Title *',
					value: form.title,
					onChange: function (v) {
						upd('title', v);
					},
					required: true,
				}),
				el(
					'div',
					{ style: { display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: '12px' } },
					el(TextControl, {
						label: 'Date *',
						type: 'date',
						value: form.date,
						onChange: function (v) {
							upd('date', v);
						},
						required: true,
					}),
					el(TextControl, {
						label: 'Start Time',
						type: 'time',
						value: form.time,
						onChange: function (v) {
							upd('time', v);
						},
					}),
					el(TextControl, {
						label: 'End Time',
						type: 'time',
						value: form.end_time || '',
						onChange: function (v) {
							upd('end_time', v);
						},
					})
				),
				el(
					'div',
					{ style: { display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px' } },
					el(SelectControl, {
						label: 'Church',
						value: form.church_id || 0,
						options: churchOptions,
						onChange: function (v) {
							upd('church_id', parseInt(v, 10));
						},
						__nextHasNoMarginBottom: true,
						__next40pxDefaultSize: true,
					}),
					el(SelectControl, {
						label: 'Color',
						value: form.color,
						options: colors,
						onChange: function (v) {
							upd('color', v);
						},
						__nextHasNoMarginBottom: true,
						__next40pxDefaultSize: true,
					})
				),
				el(TextControl, {
					label: 'Location',
					value: form.location || '',
					onChange: function (v) {
						upd('location', v);
					},
				}),
				el(
					'div',
					{ style: { display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px' } },
					el(SelectControl, {
						label: 'Sacrament',
						value: form.sacrament || '',
						options: sacramentOptions,
						onChange: function (v) {
							upd('sacrament', v);
						},
						__nextHasNoMarginBottom: true,
						__next40pxDefaultSize: true,
					}),
					el(SelectControl, {
						label: 'Event Type',
						value: form.type || '',
						options: typeOptions,
						onChange: function (v) {
							upd('type', v);
						},
						__nextHasNoMarginBottom: true,
						__next40pxDefaultSize: true,
					})
				),
				el(TextareaControl, {
					label: 'Description',
					value: form.description || '',
					rows: 3,
					onChange: function (v) {
						upd('description', v);
					},
				}),
				el(
					'div',
					{ style: { borderTop: '1px solid #ddd', paddingTop: '16px', marginTop: '16px' } },
					el('h4', { style: { marginTop: 0, marginBottom: '12px' } }, 'Organizer & Contact Info')
				),
				el(TextControl, {
					label: 'Organizer Name',
					value: form.organizer || '',
					onChange: function (v) {
						upd('organizer', v);
					},
				}),
				el(
					'div',
					{ style: { display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px' } },
					el(TextControl, {
						label: 'Contact Email',
						type: 'email',
						value: form.contact_email || '',
						onChange: function (v) {
							upd('contact_email', v);
						},
					}),
					el(TextControl, {
						label: 'Contact Phone',
						type: 'tel',
						value: form.contact_phone || '',
						onChange: function (v) {
							upd('contact_phone', v);
						},
					})
				),
				el(TextControl, {
					label: 'Registration URL',
					type: 'url',
					value: form.registration_url || '',
					onChange: function (v) {
						upd('registration_url', v);
					},
					help: 'URL for event registration or ticket purchase',
				}),
				el(
					'div',
					{ style: { display: 'flex', gap: '24px', marginTop: '16px' } },
					el(CheckboxControl, {
						label: 'Featured Event',
						checked: form.featured || false,
						onChange: function (v) {
							upd('featured', v);
						},
						help: 'Highlight on homepage',
					}),
					el(CheckboxControl, {
						label: 'Cemetery Event',
						checked: form.is_cemetery || false,
						onChange: function (v) {
							upd('is_cemetery', v);
						},
					})
				)
			),
			el(
				'div',
				{ className: 'modal-actions' },
				el(
					Flex,
					{ justify: 'space-between' },
					form.id
						? el(
								Button,
								{ isDestructive: true, onClick: handleDelete, disabled: saving },
								'Delete'
						  )
						: el('span'),
					el(
						Flex,
						{ gap: 2 },
						el(
							Button,
							{ isSecondary: true, onClick: onClose, disabled: saving },
							'Cancel'
						),
						el(
							Button,
							{
								isPrimary: true,
								onClick: handleSave,
								isBusy: saving,
								disabled: saving || !form.title || !form.date,
							},
							saving ? 'Saving...' : 'Save'
						)
					)
				)
			)
		);
	}

	function EventsCalendar() {
		const [events, setEvents] = useState([]);
		const [churches, setChurches] = useState([]);
		const [taxonomies, setTaxonomies] = useState({ sacraments: [], types: [], locations: [], feast_days: [] });
		const [loading, setLoading] = useState(true);
		const [notice, setNotice] = useState(null);
		const [viewDate, setViewDate] = useState(new Date());
		const [editing, setEditing] = useState(null);
		const [needsMigration, setNeedsMigration] = useState(false);
		const [migrating, setMigrating] = useState(false);

		const loadMonth = function (date) {
			const year = date.getFullYear();
			const month = date.getMonth();
			const start = year + '-' + String(month + 1).padStart(2, '0') + '-01';
			const lastDay = new Date(year, month + 1, 0).getDate();
			const end = year + '-' + String(month + 1).padStart(2, '0') + '-' + String(lastDay).padStart(2, '0');

			setLoading(true);
			apiFetch({
				path: '/parish/v1/events/calendar?start=' + start + '&end=' + end,
			})
				.then(function (res) {
					setEvents(res || []);
					setLoading(false);
				})
				.catch(function (err) {
					console.error('Failed to load events:', err);
					setLoading(false);
					setNotice({ type: 'error', message: 'Failed to load events.' });
				});
		};

		useEffect(function () {
			// Load churches
			apiFetch({ path: '/parish/v1/churches/list' })
				.then(function (res) {
					setChurches(res || []);
				})
				.catch(function (err) {
					console.error('Failed to load churches:', err);
				});

			// Load taxonomies
			apiFetch({ path: '/parish/v1/events/taxonomies' })
				.then(function (res) {
					setTaxonomies(res || { sacraments: [], types: [], locations: [], feast_days: [] });
				})
				.catch(function (err) {
					console.error('Failed to load taxonomies:', err);
				});

			// Check if migration is needed
			apiFetch({ path: '/parish/v1/events' })
				.then(function (res) {
					if (res.events && res.events.length > 0) {
						setNeedsMigration(true);
					}
				})
				.catch(function () {
					// Ignore error
				});

			loadMonth(viewDate);
		}, []);

		useEffect(
			function () {
				loadMonth(viewDate);
			},
			[viewDate]
		);

		const runMigration = function () {
			if (!confirm('This will migrate all events from JSON storage to the database. Continue?')) {
				return;
			}

			setMigrating(true);
			apiFetch({
				path: '/parish/v1/events/migrate',
				method: 'POST',
			})
				.then(function (res) {
					setMigrating(false);
					setNeedsMigration(false);
					setNotice({
						type: res.success ? 'success' : 'error',
						message: res.message || 'Migration completed.',
					});
					loadMonth(viewDate);
				})
				.catch(function (err) {
					setMigrating(false);
					setNotice({ type: 'error', message: err.message || 'Migration failed.' });
				});
		};

		const add = function (date) {
			setEditing({
				id: null,
				title: '',
				date: date || new Date().toISOString().split('T')[0],
				time: '',
				end_time: '',
				location: '',
				description: '',
				church_id: 0,
				sacrament: '',
				type: '',
				organizer: '',
				contact_email: '',
				contact_phone: '',
				registration_url: '',
				featured: false,
				is_cemetery: false,
				color: '#609fae',
			});
		};

		const saveEvent = function (evt) {
			return new Promise(function (resolve, reject) {
				const isNew = !evt.id;
				const endpoint = isNew ? '/wp/v2/parish_event' : '/wp/v2/parish_event/' + evt.id;
				const method = isNew ? 'POST' : 'PUT';

				const data = {
					title: evt.title,
					content: evt.description || '',
					status: 'publish',
					meta: {
						parish_event_date: evt.date,
						parish_event_time: evt.time || '',
						parish_event_end_time: evt.end_time || '',
						parish_event_location: evt.location || '',
						parish_event_church_id: evt.church_id || 0,
						parish_event_is_cemetery: evt.is_cemetery || false,
						parish_event_organizer: evt.organizer || '',
						parish_event_contact_email: evt.contact_email || '',
						parish_event_contact_phone: evt.contact_phone || '',
						parish_event_registration_url: evt.registration_url || '',
						parish_event_featured: evt.featured || false,
						parish_event_color: evt.color || '#609fae',
					},
				};

				// Add taxonomies
				if (evt.sacrament) {
					apiFetch({ path: '/wp/v2/parish_sacrament?slug=' + evt.sacrament })
						.then(function (terms) {
							if (terms && terms.length > 0) {
								data.parish_sacrament = [terms[0].id];
							}
						})
						.catch(function () {});
				}

				if (evt.type) {
					apiFetch({ path: '/wp/v2/parish_event_type?slug=' + evt.type })
						.then(function (terms) {
							if (terms && terms.length > 0) {
								data.parish_event_type = [terms[0].id];
							}
						})
						.catch(function () {});
				}

				apiFetch({
					path: endpoint,
					method: method,
					data: data,
				})
					.then(function (res) {
						setNotice({ type: 'success', message: isNew ? 'Event created!' : 'Event updated!' });
						setEditing(null);
						loadMonth(viewDate);
						resolve(res);
					})
					.catch(function (err) {
						setNotice({ type: 'error', message: err.message || 'Failed to save event.' });
						reject(err);
					});
			});
		};

		const deleteEvent = function (id) {
			return new Promise(function (resolve, reject) {
				apiFetch({
					path: '/wp/v2/parish_event/' + id,
					method: 'DELETE',
				})
					.then(function () {
						setNotice({ type: 'success', message: 'Event deleted.' });
						setEditing(null);
						loadMonth(viewDate);
						resolve();
					})
					.catch(function (err) {
						setNotice({ type: 'error', message: err.message || 'Failed to delete event.' });
						reject(err);
					});
			});
		};

		const editEvent = function (evt) {
			setEditing({
				id: evt.id,
				title: evt.title,
				date: evt.date,
				time: evt.time || '',
				end_time: evt.end_time || '',
				location: evt.location || '',
				description: evt.description || '',
				church_id: evt.church_id || 0,
				sacrament: evt.sacraments && evt.sacraments.length > 0 ? evt.sacraments[0] : '',
				type: evt.types && evt.types.length > 0 ? evt.types[0] : '',
				organizer: evt.organizer || '',
				contact_email: evt.contact_email || '',
				contact_phone: evt.contact_phone || '',
				registration_url: evt.registration_url || '',
				featured: evt.featured || false,
				is_cemetery: evt.is_cemetery || false,
				color: evt.color || '#609fae',
			});
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
			needsMigration &&
				el(
					Notice,
					{ status: 'warning', isDismissible: false },
					el('p', null, 'Events need to be migrated from JSON to the new database structure.'),
					el(
						Button,
						{ isPrimary: true, isBusy: migrating, onClick: runMigration },
						migrating ? 'Migrating...' : 'Migrate Events Now'
					)
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
					{
						isPrimary: true,
						onClick: function () {
							add();
						},
					},
					'+ Add Event'
				)
			),
			el(MonthView, {
				year: year,
				month: month,
				events: events,
				onDayClick: add,
				onEventClick: editEvent,
			}),
			editing &&
				el(EventModal, {
					event: editing,
					churches: churches,
					taxonomies: taxonomies,
					onSave: saveEvent,
					onDelete: deleteEvent,
					onClose: function () {
						setEditing(null);
					},
				})
		);
	}

	window.ParishCoreAdmin = window.ParishCoreAdmin || {};
	Object.assign(window.ParishCoreAdmin, {
		EventsCalendar,
	});
})(window);
